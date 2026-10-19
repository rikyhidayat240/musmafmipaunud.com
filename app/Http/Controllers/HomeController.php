<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $year = now()->year;

        $event = Kegiatan::query()
            ->select(['id', 'nama', 'tahun', 'waktu_mulai', 'waktu_selesai'])
            ->where('tahun', $year)
            ->where('waktu_selesai', '>', now())
            ->orderBy('waktu_mulai', 'asc')
            ->first();

        if (!$event) {
            $event = Kegiatan::query()
                ->select(['id', 'nama', 'tahun', 'waktu_mulai', 'waktu_selesai'])
                ->latest('waktu_selesai')
                ->first();
        }

        $kegiatan = $event ? [
            'id' => $event->id,
            'nama' => $event->nama,
            'tahun' => $event->tahun,
            'waktu_mulai' => $event->waktu_mulai?->locale('id_ID')->isoFormat('DD MMMM YYYY, HH:mm'),
            'waktu_mulai_raw' => $event->waktu_mulai?->format('Y-m-d H:i:s'),
            'waktu_selesai' => $event->waktu_selesai?->locale('id_ID')->isoFormat('DD MMMM YYYY, HH:mm'),
            'waktu_selesai_raw' => $event->waktu_selesai?->format('Y-m-d H:i:s'),
            'status' => ($event->waktu_mulai && $event->waktu_selesai && now()->betweenIncluded($event->waktu_mulai, $event->waktu_selesai))
                ? 'Sedang Berlangsung'
                : (($event->waktu_mulai && now()->isBefore($event->waktu_mulai)) ? 'Coming Soon' : 'Selesai'),
        ] : null;

        return Inertia::render('Index', ['kegiatan' => $kegiatan]);
    }
}
