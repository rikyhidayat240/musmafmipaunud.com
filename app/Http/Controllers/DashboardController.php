<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $kegiatan = Kegiatan::with('kandidat.mahasiswa')->where('tahun', now()->year)->get()->first();
        return Inertia::render('Dashboard', ['kegiatan' => $kegiatan]);
    }
}
