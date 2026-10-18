<?php

namespace App\Http\Controllers;

use App\Mail\MahasiswaCredentials;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CredentialMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MahasiswaController extends Controller
{
    /**
     * Check if the authenticated user is an admin.
     */
    private function isAdmin()
    {
        $role = auth('web')->user()->is_admin;
        return match ($role) {
            0 => false,
            1 => true,
        };
    }

    /**
     * Generate a secure random password using CSPRNG.
     */
    public function generatePassword(): string
    {
        return app(CredentialMailer::class)->generatePassword();
    }

    /**
     * Resend credential email to a specific student.
     * Password di DB hanya diganti setelah email berhasil terkirim.
     */
    public function sendCredentials(string $nim)
    {
        $mahasiswa = User::where('nim', $nim)->where('is_admin', false)->firstOrFail();

        if (empty($mahasiswa->email)) {
            return back()->with('error', 'Mahasiswa ini belum memiliki email yang terdaftar.');
        }

        $mailer = app(CredentialMailer::class);

        if (! $mailer->send($mahasiswa)) {
            Log::error('Send credentials error nim=' . $nim . ': ' . $mailer->lastError);
            return back()->with('error', 'Gagal mengirim email. Password mahasiswa tidak diubah. Silakan coba lagi.');
        }

        AuditLogService::log('mahasiswa.send_credentials', 'user', $mahasiswa->nim, [
            'nim' => $mahasiswa->nim,
        ]);

        return back()->with('success', 'Password baru berhasil dikirim ke ' . $mahasiswa->email);
    }

    /**
     * Send credential email to all students (Bulk).
     * Dilindungi Cache::lock agar tidak berjalan ganda; batch besar dijalankan setelah respons (defer).
     */
    public function sendBulkCredentials(Request $request)
    {
        $lock = Cache::lock(CredentialMailer::BULK_LOCK_KEY, CredentialMailer::BULK_LOCK_SECONDS);
        if (! $lock->get()) {
            return back()->with('error', 'Pengiriman email massal sedang berjalan di latar belakang. Silakan refresh halaman secara berkala untuk memantau progres.');
        }

        $isDeferred = false;

        try {
            $onlyUnsent = $request->boolean('only_unsent', false);

            $query = User::where('is_admin', false)
                ->whereNotNull('email')
                ->where('email', '!=', '');

            if ($onlyUnsent) {
                $query->whereNull('credentials_sent_at');
            }

            $mahasiswaList = $query->get()->values();

            if ($mahasiswaList->isEmpty()) {
                return back()->with('error', 'Tidak ada mahasiswa yang memenuhi syarat pengiriman email.');
            }

            $mailer = app(CredentialMailer::class);
            $total = $mahasiswaList->count();

            // Batch besar: kirim setelah respons HTTP agar tidak kena 504 Gateway Timeout.
            if ($total > 20) {
                $isDeferred = true;

                defer(function () use ($mahasiswaList, $lock, $mailer, $onlyUnsent, $total) {
                    set_time_limit(0);
                    ignore_user_abort(true);

                    try {
                        [$sent, $failed] = $this->sendCredentialBatch($mailer, $mahasiswaList);

                        AuditLogService::log('mahasiswa.send_bulk_credentials', 'user', null, [
                            'total'       => $total,
                            'sent'        => $sent,
                            'failed'      => $failed,
                            'only_unsent' => $onlyUnsent,
                            'deferred'    => true,
                        ]);
                    } finally {
                        $lock->release();
                    }
                });

                return back()->with('success', "Pengiriman massal ke {$total} mahasiswa sedang berjalan di latar belakang. Silakan refresh halaman secara berkala untuk memantau status Terkirim.");
            }

            [$sent, $failed] = $this->sendCredentialBatch($mailer, $mahasiswaList);

            AuditLogService::log('mahasiswa.send_bulk_credentials', 'user', null, [
                'total'       => $total,
                'sent'        => $sent,
                'failed'      => $failed,
                'only_unsent' => $onlyUnsent,
                'deferred'    => false,
            ]);

            $msg = "Pengiriman bulk selesai: {$sent} email berhasil terkirim.";
            if ($failed > 0) {
                $msg .= " ({$failed} gagal).";
            }

            return back()->with($failed > 0 && $sent === 0 ? 'error' : 'success', $msg);
        } finally {
            // Jalur defer melepas lock di dalam closure; jangan dilepas dua kali.
            if (! $isDeferred) {
                $lock->release();
            }
        }
    }

    /**
     * Kirim kredensial ke daftar mahasiswa dengan jeda antar email.
     *
     * @return array{0:int,1:int} [sent, failed]
     */
    protected function sendCredentialBatch(CredentialMailer $mailer, $mahasiswaList): array
    {
        $throttleDelayMs = $mailer->throttleDelayMs();
        $sent = 0;
        $failed = 0;

        foreach ($mahasiswaList as $index => $mahasiswa) {
            if ($index > 0 && $throttleDelayMs > 0) {
                usleep($throttleDelayMs * 1000);
            }

            if ($mailer->send($mahasiswa)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [$sent, $failed];
    }

    /**
     * Get mahasiswa data from spreadsheet.
     */
    public function syncMahasiswa(int $year)
    {
        // Get current users and users from spreadsheet
        $currentUsers = User::where('is_admin', false)->get();
        $users = User::getMahasiswaFromSheet($year);

        // Sync users
        try {
            $spreadsheetNims = collect($users)->pluck('nim')->toArray();
            $newCount = 0;
            $updatedCount = 0;

            foreach ($users as $user) {
                $userArray = (array) $user;
                $fillableData = Arr::only($userArray, ['nim', 'nama', 'id_program_studi', 'angkatan']);
                $status = $userArray['status'] ?? 'aktif';

                $currentUser = $currentUsers->firstWhere('nim', $userArray['nim']);

                if ($currentUser) {
                    $currentUser->fill($fillableData);
                    $currentUser->status = $status;
                    $currentUser->save();
                    $updatedCount++;
                } else {
                    $plainPassword = $this->generatePassword();
                    $nim = $userArray['nim'];
                    $email = $nim . '@student.unud.ac.id';

                    $newUser = new User();
                    $newUser->fill(array_merge($fillableData, [
                        'email'               => $email,
                        'password'            => Hash::make($plainPassword),
                        'email_verified_at'   => now(),
                        'credentials_sent_at' => null,
                    ]));
                    $newUser->status = $status;
                    $newUser->is_admin = false;
                    $newUser->save();

                    $newCount++;
                }
            }

            // Set status to 'nonaktif' for users not in spreadsheet
            User::where('is_admin', false)
                ->whereNotIn('nim', $spreadsheetNims)
                ->update(['status' => 'nonaktif']);

            AuditLogService::log('mahasiswa.sync', 'user', (string) $year, [
                'year'          => $year,
                'new_count'     => $newCount,
                'updated_count' => $updatedCount,
            ]);

            return redirect()->back()->with('success',
                "Sinkronisasi berhasil: {$newCount} data baru ditambahkan, {$updatedCount} data diperbarui.");
        } catch (\Exception $e) {
            Log::error('Sync mahasiswa error year=' . $year . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mahasiswa = User::with('program_studi')
            ->where('is_admin', false)
            ->get();
        $programStudi = ProgramStudi::all();

        return Inertia::render('mahasiswa/Index', [
            'mahasiswa'    => $mahasiswa,
            'programStudi' => $programStudi,
            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nim'              => 'required|string|max:15|unique:mahasiswa,nim',
            'nama'             => 'required|string|max:255',
            'email'            => 'nullable|email|max:255|unique:mahasiswa,email',
            'id_program_studi' => 'required|exists:program_studi,id',
            'angkatan'         => 'required|integer|min:2000|max:2100',
            'avatar'           => 'nullable|image|max:4096|mimes:jpeg,png,jpg,webp',
        ], [
            'nim.max'                 => 'NIM maksimal 15 digit.',
            'nim.unique'              => 'NIM sudah terdaftar.',
            'nama.max'                => 'Nama maksimal 255 karakter.',
            'email.max'               => 'Email maksimal 255 karakter.',
            'email.unique'            => 'Email sudah terdaftar.',
            'id_program_studi.exists' => 'Program studi tidak valid.',
            'angkatan.min'            => 'Angkatan minimal tahun 2000.',
            'angkatan.max'            => 'Angkatan maksimal tahun 2100.',
            'avatar.image'            => 'Foto profil harus berupa gambar.',
            'avatar.max'              => 'Ukuran foto profil maksimal 4MB.',
            'avatar.mimes'            => 'Foto profil harus berformat jpeg, png, jpg, atau webp.',
        ]);

        try {
            $avatarPath = null;
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                $avatarFile = $request->file('avatar');
                $avatarName = $avatarFile->hashName();

                $avatarDestination = Storage::disk('public')->path('foto-mahasiswa');
                if (!file_exists($avatarDestination)) {
                    mkdir($avatarDestination, 0755, true);
                }

                Storage::disk('public')->putFileAs('foto-mahasiswa', $avatarFile, $avatarName);
                $avatarPath = 'foto-mahasiswa/' . $avatarName;
            }

            $plainPassword = $this->generatePassword();
            $studentEmail  = !empty($validatedData['email'])
                ? $validatedData['email']
                : $validatedData['nim'] . '@student.unud.ac.id';

            $newUser = new User();
            $newUser->fill([
                'nim'                 => $validatedData['nim'],
                'nama'                => $validatedData['nama'],
                'email'               => $studentEmail,
                'id_program_studi'    => $validatedData['id_program_studi'],
                'angkatan'            => $validatedData['angkatan'],
                'avatar'              => $avatarPath,
                'password'            => Hash::make($plainPassword),
                'email_verified_at'   => now(),
                'credentials_sent_at' => null,
            ]);
            $newUser->status = 'aktif';
            $newUser->is_admin = false;
            $newUser->save();

            AuditLogService::log('mahasiswa.create', 'user', $newUser->nim, [
                'nim'              => $newUser->nim,
                'nama'             => $newUser->nama,
                'id_program_studi' => $newUser->id_program_studi,
                'angkatan'         => $newUser->angkatan,
            ]);

            try {
                Mail::to($studentEmail)->send(new MahasiswaCredentials($newUser, $plainPassword));
                $newUser->update(['credentials_sent_at' => now()]);
                return back()->with('success', 'Data mahasiswa berhasil dibuat dan password telah dikirim ke ' . $studentEmail);
            } catch (\Throwable $mailEx) {
                Log::warning('Credential email failed for nim=' . $validatedData['nim'] . ': ' . $mailEx->getMessage());
                return back()->with('success', 'Akun berhasil dibuat. Email password gagal dikirim — silakan klik tombol Kirim di tabel.');
            }
        } catch (\Throwable $e) {
            Log::error('Create mahasiswa error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat membuat data mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $nim)
    {
        $validatedData = $request->validate([
            'nim'              => 'required|string|max:15',
            'nama'             => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'id_program_studi' => 'required|exists:program_studi,id',
            'angkatan'         => 'required|integer|min:2000|max:2100',
            'avatar'           => 'nullable|image|max:4096|mimes:jpeg,png,jpg,webp',
        ], [
            'nim.max'                 => 'NIM maksimal 15 digit.',
            'nama.max'                => 'Nama maksimal 255 karakter.',
            'email.max'               => 'Email maksimal 255 karakter.',
            'id_program_studi.exists' => 'Program studi tidak valid.',
            'angkatan.min'            => 'Angkatan minimal tahun 2000.',
            'angkatan.max'            => 'Angkatan maksimal tahun 2100.',
            'avatar.image'            => 'Foto profil harus berupa gambar.',
            'avatar.max'              => 'Ukuran foto profil maksimal 4MB.',
            'avatar.mimes'            => 'Foto profil harus berformat jpeg, png, jpg, atau webp.',
        ]);

        try {
            $mahasiswa = User::where('nim', $nim)->firstOrFail();

            if ($validatedData['nim'] !== $nim && $this->hasProtectedRecords($nim)) {
                return redirect()->back()->with('error', 'NIM mahasiswa tidak dapat diubah karena sudah terkait dengan data voting atau kandidat.');
            }

            $existingNim = User::where('nim', $validatedData['nim'])
                ->whereNot('nim', $nim)
                ->first();
            if ($existingNim) {
                return redirect()->back()->with('error', 'NIM sudah terdaftar oleh mahasiswa lain.');
            }

            if (!empty($validatedData['email'])) {
                $existingEmail = User::where('email', $validatedData['email'])
                    ->whereNot('nim', $nim)
                    ->whereNotNull('email')
                    ->first();
                if ($existingEmail) {
                    return redirect()->back()->with('error', 'Email sudah terdaftar oleh mahasiswa lain.');
                }
            }

            $avatarPath = $mahasiswa->avatar;
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }

                $avatarFile = $request->file('avatar');
                $avatarName = $avatarFile->hashName();

                $avatarDestination = Storage::disk('public')->path('foto-mahasiswa');
                if (!file_exists($avatarDestination)) {
                    mkdir($avatarDestination, 0755, true);
                }

                Storage::disk('public')->putFileAs('foto-mahasiswa', $avatarFile, $avatarName);
                $avatarPath = 'foto-mahasiswa/' . $avatarName;
            }

            $updateData = [
                'nim'              => $validatedData['nim'],
                'nama'             => $validatedData['nama'],
                'id_program_studi' => $validatedData['id_program_studi'],
                'angkatan'         => $validatedData['angkatan'],
                'avatar'           => $avatarPath,
            ];

            if (!empty($validatedData['email'])) {
                $updateData['email'] = $validatedData['email'];
            }

            $mahasiswa->update($updateData);

            AuditLogService::log('mahasiswa.update', 'user', $mahasiswa->nim, [
                'nim'              => $mahasiswa->nim,
                'nama'             => $mahasiswa->nama,
                'id_program_studi' => $mahasiswa->id_program_studi,
                'angkatan'         => $mahasiswa->angkatan,
            ]);

            return redirect()->back()->with('success', 'Data mahasiswa berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Update mahasiswa error nim=' . $nim . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Check whether the student has protected vote or candidate records.
     */
    protected function hasProtectedRecords(string $nim): bool
    {
        return DB::table('surat_suara')->where('nim', $nim)->exists()
            || DB::table('mahasiswa_kandidat')->where('nim', $nim)->exists();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $nim)
    {
        try {
            $mahasiswa = User::where('nim', $nim)->firstOrFail();

            // Tolak penghapusan jika mahasiswa memiliki catatan suara atau kandidat
            if ($this->hasProtectedRecords($mahasiswa->nim)) {
                return redirect()->back()->with('error', 'Data mahasiswa tidak dapat dihapus karena terkait dengan data voting atau kandidat.');
            }

            $avatarPath = $mahasiswa->avatar;

            // Hapus mahasiswa terlebih dahulu agar file avatar tidak hilang jika database menolak
            $mahasiswa->delete();

            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            AuditLogService::log('mahasiswa.delete', 'user', $nim, [
                'nim' => $nim,
            ]);

            return redirect()->back()->with('success', 'Data mahasiswa berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Delete mahasiswa error nim=' . $nim . ': ' . $e->getMessage());
            if (str_contains($e->getMessage(), 'constraint') || str_contains($e->getMessage(), 'Integrity constraint violation')) {
                return redirect()->back()->with('error', 'Data mahasiswa tidak dapat dihapus karena terkait dengan data voting atau kandidat.');
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Bulk remove students from storage while skipping those with vote/candidate records.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'nims'   => 'required|array|min:1',
            'nims.*' => 'required|string|max:15',
        ]);

        $targetUsers = User::whereIn('nim', array_unique($validated['nims']))
            ->where('is_admin', false)
            ->get();

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($targetUsers as $mahasiswa) {
            if ($this->hasProtectedRecords($mahasiswa->nim)) {
                $skippedCount++;
                continue;
            }

            try {
                $avatarPath = $mahasiswa->avatar;
                $mahasiswa->delete();

                if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }

                $deletedCount++;
            } catch (\Exception $e) {
                Log::warning('Bulk delete skipped nim=' . $mahasiswa->nim . ': ' . $e->getMessage());
                $skippedCount++;
            }
        }

        AuditLogService::log('mahasiswa.bulk_delete', 'user', null, [
            'deleted_count' => $deletedCount,
            'skipped_count' => $skippedCount,
        ]);

        if ($deletedCount === 0 && $skippedCount > 0) {
            return redirect()->back()->with(
                'error',
                "Tidak ada mahasiswa yang dihapus. {$skippedCount} mahasiswa dilewati karena terkait dengan data voting atau kandidat."
            );
        }

        $message = "{$deletedCount} data mahasiswa berhasil dihapus.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} mahasiswa dilewati karena terkait dengan data voting atau kandidat.";
        }

        return redirect()->back()->with('success', $message);
    }
}
