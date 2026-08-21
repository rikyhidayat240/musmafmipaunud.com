<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kegiatan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'nim' => '2008511001',
            'id_program_studi' => 1,
            'nama' => 'Admin DPM FMIPA',
            'angkatan' => 2021,
            'email' => 'dpmfmipaunud2025@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'is_admin' => true
        ]);
        User::factory()->create([
            'nim' => '2308561000',
            'id_program_studi' => 1,
            'nama' => 'Mahasiswa FMIPA',
            'angkatan' => 2021,
            // 'email' => 'mahasiswafmipa@gmail.com',
            // 'email_verified_at' => now(),
            // 'password' => null,
        ]);
        User::factory()->createMany(User::getMahasiswaFromSheet(now()->year));
    }
}
