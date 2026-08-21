<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Check if NIM and Nama combination exists in database
     */
    public function checkStudent(Request $request)
    {
        $request->validate([
            'nim' => 'required|string|digits:10',
            'nama' => 'required|string|max:255',
        ], [
            'nim.digits' => 'NIM harus terdiri dari 10 digit.',
            'nama.max' => 'Nama harus terdiri dari maksimal 255 karakter.',
        ]);

        $student_exist = User::where('nim', $request->nim)
            ->where('nama', $request->nama)
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->exists();
        
        if ($student_exist) {
            return response()->json([
                'exists' => false,
                'message' => 'Akun telah terdaftar. Silakan masuk ke akun Anda.'
            ]);
        }

        $student = User::where('nim', $request->nim)
            ->where('nama', $request->nama)
            ->whereNull('email')
            ->whereNull('password')
            ->first();

        return response()->json([
            'exists' => (bool) $student,
            'message' => $student
                ? 'Data ditemukan. Silakan lengkapi pendaftaran Anda.'
                : 'Data tidak ditemukan. Silakan periksa NIM dan Nama Anda.'
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nim' => 'required|string|digits:10|exists:mahasiswa,nim',
            'nama' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:mahasiswa,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nim.exists' => 'NIM tidak ditemukan dalam database.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Kata sandi minimal harus terdiri dari :min karakter.',
            'password.mixed' => 'Kata sandi harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        ]);

        // Find the student and update with email and password
        $user = User::where('nim', $request->nim)
            ->where('nama', $request->nama)
            ->whereNull('email')
            ->whereNull('password')
            ->first();

        if (!$user) {
            return back()->withErrors(['nim' => 'Kredensial mahasiswa tidak valid.']);
        }

        // Check domain email @student.unud.ac.id
        $emailDomain = substr(strrchr($request->email, "@"), 1);
        if ($emailDomain !== 'student.unud.ac.id') {
            return back()->withErrors(['email' => 'Email harus menggunakan domain @student.unud.ac.id.']);
        }

        $user->update([
            'email' => $request->email,
            'email_verified_at' => now(),
            'password' => Hash::make($request->password),
        ]);

        // Generate surat suara
        $kegiatan = Kegiatan::where('tahun', now()->year)
            ->where('waktu_selesai', '>', now())
            ->get();
        if ($kegiatan->isNotEmpty()) {
            foreach ($kegiatan as $k) {
                $k->mahasiswa()->attach([$user->nim => ['has_vote' => null]]);
            }
        }

        return to_route('login')
            ->with('alert', ['type' => 'success', 'title' => 'Pendaftaran berhasil. Silakan masuk ke akun Anda.'])
            ->with('status', 'Pendaftaran berhasil. Silakan masuk ke akun Anda.');
    }
}
