<?php

namespace App\Http\Controllers;

use App\Events\VoteCandidate;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VoteController extends Controller
{
    public function index(string $id, Request $request) 
    {
        if (Auth::user()->is_admin) {
            return back()->with('alert', ['type' => 'error', 'title' => 'Anda adalah admin', 'message' => 'Sebagai admin, anda tidak berhak untuk melakukan voting.']);
        }

        $user = User::find(Auth::user()->nim);
        if ($user->isActive() === false) {
            return back()->with('alert', ['type' => 'error', 'title' => 'Anda bukan mahasiswa aktif', 'message' => 'Anda harus terdaftar sebagai mahasiswa aktif untuk dapat melakukan voting.']);
        }
        $kegiatan = Kegiatan::with('kandidat.mahasiswa.program_studi')->findOrFail($id);
        if (now()->isBefore($kegiatan->waktu_mulai)) {
            return back()->with('alert', ['type' => 'error', 'title' => 'Pemilihan belum dimulai', 'message' => 'Tunggu hingga periode pemilihan dimulai.']);
        }
        if (!($kegiatan->tahun == now()->year && $kegiatan->is_user_allowed())) {
            return back()->with('alert', ['type' => 'error', 'title' => 'Anda sudah vote', 'message' => 'Terima kasih sudah memilih calon ketua DPM FMIPA']);
        }
        return Inertia::render('Vote', ['kegiatan' => $kegiatan]);
    }

    public function vote(string $id, \App\Http\Requests\VoteRequest $request)
    {
        $user = Auth::user();
        $kegiatan = Kegiatan::findOrFail($id);

        try {
            return DB::transaction(function () use ($user, $request, $kegiatan, $id) {
                // Kunci baris user untuk isolasi transaksi voting akun ini
                User::where('nim', $user->nim)->lockForUpdate()->first();

                // Cek apakah sudah memilih
                $alreadyVoted = DB::table('surat_suara')
                    ->where('nim', $user->nim)
                    ->where('id_kegiatan', $id)
                    ->whereNotNull('has_vote')
                    ->exists();

                if ($alreadyVoted) {
                    Log::warning('[SECURITY] Double-vote attempt detected', [
                        'nim'         => $user->nim,
                        'id_kegiatan' => $id,
                        'ip'          => $request->ip(),
                        'user_agent'  => $request->userAgent(),
                    ]);
                    return to_route('home')->with('alert', ['type' => 'error', 'title' => 'Anda sudah vote', 'message' => 'Anda sudah memberikan suara! Suara ganda ditolak.']);
                }

                // Tandai sudah vote dengan presisi jam (HH:00:00)
                $coarsenedTime = now()->startOfHour();
                $kegiatan->mahasiswa()->syncWithoutDetaching([$user->nim => ['has_vote' => $coarsenedTime]]);

                // Increment atomik via query builder tanpa mengubah updated_at kandidat
                DB::table('kandidat')->where('id', $request->kandidat)->where('id_kegiatan', $id)->increment('jumlah_suara');

                Log::info('[VOTE] Vote cast successfully', [
                    'nim'         => $user->nim,
                    'id_kegiatan' => $id,
                ]);

                return to_route('home')->with('alert', ['type' => 'success', 'title' => 'Vote berhasil dilakukan', 'message' => 'Terima kasih sudah memilih calon ketua DPM FMIPA']);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('[SECURITY] Double-vote detected via unique constraint', [
                'nim'         => $user->nim,
                'id_kegiatan' => $id,
                'ip'          => $request->ip(),
            ]);
            return to_route('home')->with('alert', ['type' => 'error', 'title' => 'Anda sudah vote', 'message' => 'Anda sudah memberikan suara! Suara ganda ditolak.']);
        }
    }
}
