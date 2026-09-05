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

    public function vote(string $id, Request $request)
    {
        $user = Auth::user();

        // SECURITY: Validate kandidat exists AND belongs to this specific kegiatan
        $data = $request->validate([
            'kandidat' => [
                'required',
                'integer',
                Rule::exists('kandidat', 'id')->where('id_kegiatan', $id),
            ],
        ], [
            'kandidat.required' => 'Pilihan kandidat wajib ada.',
            'kandidat.integer'  => 'Data kandidat tidak valid.',
            'kandidat.exists'   => 'Kandidat tidak ditemukan atau bukan bagian dari kegiatan ini.',
        ]);

        $kegiatan = Kegiatan::findOrFail($id);

        // SECURITY: Ensure the kegiatan is still active
        if (now()->isAfter($kegiatan->waktu_selesai)) {
            Log::warning('[VOTE] Attempt to vote on ended kegiatan', [
                'nim'         => $user->nim,
                'id_kegiatan' => $id,
                'ip'          => $request->ip(),
            ]);
            return to_route('home')->with('alert', ['type' => 'error', 'title' => 'Pemilihan telah berakhir', 'message' => 'Waktu pemilihan sudah habis.']);
        }
        
        return DB::transaction(function () use ($user, $data, $kegiatan, $id, $request) {
            // SECURITY: Double-vote check inside transaction with row lock
            $alreadyVoted = DB::table('surat_suara')
                ->where('nim', $user->nim)
                ->where('id_kegiatan', $id)
                ->whereNotNull('has_vote')
                ->lockForUpdate()
                ->exists();

            if ($alreadyVoted) {
                Log::warning('[SECURITY] Double-vote attempt detected', [
                    'nim'         => $user->nim,
                    'id_kegiatan' => $id,
                    'kandidat_id' => $data['kandidat'],
                    'ip'          => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);
                return to_route('home')->with('alert', ['type' => 'error', 'title' => 'Anda sudah vote', 'message' => 'Anda sudah memberikan suara! Suara ganda ditolak.']);
            }

            // Mark as voted
            $kegiatan->mahasiswa()->syncWithoutDetaching([$user->nim => ['has_vote' => now()]]);
            
            // Increment candidate vote with lock
            $kandidat = $kegiatan->kandidat()->where('id', $data['kandidat'])->lockForUpdate()->first();
            if ($kandidat) {
                $kandidat->increment('jumlah_suara');
            }

            Log::info('[VOTE] Vote cast successfully', [
                'nim'         => $user->nim,
                'id_kegiatan' => $id,
                'kandidat_id' => $data['kandidat'],
            ]);

            return to_route('home')->with('alert', ['type' => 'success', 'title' => 'Vote berhasil dilakukan', 'message' => 'Terima kasih sudah memilih calon ketua DPM FMIPA']);
        });
    }
}
