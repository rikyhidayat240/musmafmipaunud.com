<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kegiatan::factory()->createMany([
            [
                'nama' => 'Demo Pemilihan Ketua DPM FMIPA 2026',
                'tahun' => 2025,
                'waktu_mulai' => '2025-10-30 06:00:00',
                'waktu_selesai' => '2025-10-31 22:00:00',
                'ruang_lingkup' => 'fakultas',
                'id_program_studi' => null,
                'foto' => 'foto-kegiatan/blank-photo-icon.jpg',
            ],
            [
                'nama' => 'Pemilihan Ketua DPM FMIPA 2026',
                'tahun' => 2025,
                'waktu_mulai' => '2025-11-01 10:00:00',
                'waktu_selesai' => '2025-11-02 08:00:00',
                'ruang_lingkup' => 'fakultas',
                'id_program_studi' => null,
                'foto' => 'foto-kegiatan/blank-photo-icon.jpg',
            ],
        ]);
    }
}
