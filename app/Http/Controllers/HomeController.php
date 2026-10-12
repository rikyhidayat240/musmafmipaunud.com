<?php

namespace App\Http\Controllers;

use App\Http\Resources\KandidatResource;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $year = now()->year;
        $activeKegiatan = Kegiatan::with('kandidat.mahasiswa')
            ->where('tahun', $year)
            ->where('waktu_selesai', '>', now())
            ->first();
        
        if ($activeKegiatan) {
            $kegiatan = $activeKegiatan->toResource();
        } else {
            $latestKegiatan = Kegiatan::with('kandidat.mahasiswa')
                ->latest()
                ->first();
            $kegiatan = $latestKegiatan ? $latestKegiatan->toResource() : null;
        }

        return Inertia::render('Index', ['kegiatan' => $kegiatan]);
    }
}
