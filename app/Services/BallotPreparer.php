<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\User;

/**
 * Menyiapkan surat suara (baris pivot has_vote = null) untuk mahasiswa aktif saat login.
 * Dipakai oleh login biasa dan login setelah tantangan 2FA.
 */
class BallotPreparer
{
    public function prepareFor(?User $user): void
    {
        if (! $user || $user->is_admin || $user->status !== 'aktif') {
            return;
        }

        $kegiatan = Kegiatan::where('tahun', now()->year)
            ->where('waktu_selesai', '>', now())
            ->with('mahasiswa')
            ->get();

        foreach ($kegiatan as $k) {
            // Penting: jangan kirim ['has_vote' => null] sebagai nilai asosiatif karena
            // syncWithoutDetaching akan menimpa has_vote yang sudah terisi saat mahasiswa login ulang.
            $k->mahasiswa()->syncWithoutDetaching([$user->nim]);
        }
    }
}
