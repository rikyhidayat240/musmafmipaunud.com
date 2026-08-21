<?php

namespace App\Http\Controllers;

use App\Events\VoteCandidate;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $data = $request->validate(['kandidat' => 'required']);
        $kegiatan = Kegiatan::findOrFail($id);
        
        return DB::transaction(function () use ($user, $data, $kegiatan, $id) {
            // SECURITY FIX: Check if already voted
            $alreadyVoted = DB::table('surat_suara')
                ->where('nim', $user->nim)
                ->where('id_kegiatan', $id)
                ->whereNotNull('has_vote')
                ->exists();

            if ($alreadyVoted) {
                return to_route('home')->with('alert', ['type' => 'error', 'title' => 'Anda sudah vote', 'message' => 'Anda sudah memberikan suara! Suara ganda ditolak.']);
            }

            // Mark as voted
            $kegiatan->mahasiswa()->syncWithoutDetaching([$user->nim => ['has_vote' => now()]]);
            
            // Increment candidate vote
            $kandidat = $kegiatan->kandidat()->where('id', $data['kandidat'])->lockForUpdate()->first();
            if ($kandidat) {
                $kandidat->increment('jumlah_suara');
            }

            return to_route('home')->with('alert', ['type' => 'success', 'title' => 'Vote berhasil dilakukan', 'message' => 'Terima kasih sudah memilih calon ketua DPM FMIPA']);
        });
    }
}
