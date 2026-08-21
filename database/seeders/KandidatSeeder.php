<?php

namespace Database\Seeders;

use App\Models\Kandidat;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KandidatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kandidat::factory()->createMany([
            [
                'id_kegiatan' => 1,
                'no_urut' => '01',
                'foto' => 'foto-kandidat/calon-1.png',
                'visi' => 'Mewujudkan DPM FMIPA 2026 sebagai lembaga legislatif yang aspiratif, responsif, transparan, dan kolaboratif dalam menjalankan tugas dan wewenang, serta tanggung jawabnya demi terciptanya tata kelola organisasi yang demokratis dan berorientasi pada kemajuan mahasiswa FMIPA.',
                'misi' => '1. Menegakan fungsi legislasi, aspirasi, dan pengawasan untuk memastikan terselenggaranya tata kelola organisasi yang efektif. 2. Menjalin komunikasi dan kerja sama yang harmonis dengan seluruh elemen mahasiswa, lembaga mahasiswa, serta pihak fakultas untuk mewujudkan sinergi yang produktif. 3. Mendorong partisipasi aktif mahasiswa dalam proses demokrasi serta penyaluran aspirasi secara terbuka dan berkelanjutan.',
            ],
            [
                'id_kegiatan' => 1,
                'no_urut' => '02',
                'foto' => 'foto-kandidat/calon-2.png',
                'visi' => 'Mewujudkan DPM sebagai pilar legislasi yang adaptif dan kolaboraif, demi terciptanya tatanan emahasiswaan yang harmonis.',
                'misi' => '1. Menguatkan pelaksanaan tugas legislatif DPM secara bertanggung jawab dan selaras dengan aspirasi mahasiswa, berpedoman pada AD-ART dan GBHK. 2. Meningkatkan mutu pengawasan yang objektif dan solutif terhadap kinerja seluruh lembaga kemahasiswaan di lingkungan Fakultas Matematika dan Ilmu Pengetahuan Alam dan Ilmu Pengetahuan Alam. 3. Membangun sinergi dan kemitraan yang harmonis antar lembaga kemahasiswaan internal dan eksternal fakultas MIPA. 4. Menciptakan budaya kerja internal DPM yang solid, adaptif, dan saling mendukung guna mengoptimalkan kinerja kelembagaan',
            ],
            [
                'id_kegiatan' => 2,
                'no_urut' => '01',
                'foto' => 'foto-kandidat/calon-1.png',
                'visi' => 'Mewujudkan DPM FMIPA 2026 sebagai lembaga legislatif yang aspiratif, responsif, transparan, dan kolaboratif dalam menjalankan tugas dan wewenang, serta tanggung jawabnya demi terciptanya tata kelola organisasi yang demokratis dan berorientasi pada kemajuan mahasiswa FMIPA.',
                'misi' => '1. Menegakan fungsi legislasi, aspirasi, dan pengawasan untuk memastikan terselenggaranya tata kelola organisasi yang efektif. 2. Menjalin komunikasi dan kerja sama yang harmonis dengan seluruh elemen mahasiswa, lembaga mahasiswa, serta pihak fakultas untuk mewujudkan sinergi yang produktif. 3. Mendorong partisipasi aktif mahasiswa dalam proses demokrasi serta penyaluran aspirasi secara terbuka dan berkelanjutan.',
            ],
            [
                'id_kegiatan' => 2,
                'no_urut' => '02',
                'foto' => 'foto-kandidat/calon-2.png',
                'visi' => 'Mewujudkan DPM sebagai pilar legislasi yang adaptif dan kolaboraif, demi terciptanya tatanan emahasiswaan yang harmonis.',
                'misi' => '1. Menguatkan pelaksanaan tugas legislatif DPM secara bertanggung jawab dan selaras dengan aspirasi mahasiswa, berpedoman pada AD-ART dan GBHK. 2. Meningkatkan mutu pengawasan yang objektif dan solutif terhadap kinerja seluruh lembaga kemahasiswaan di lingkungan Fakultas Matematika dan Ilmu Pengetahuan Alam dan Ilmu Pengetahuan Alam. 3. Membangun sinergi dan kemitraan yang harmonis antar lembaga kemahasiswaan internal dan eksternal fakultas MIPA. 4. Menciptakan budaya kerja internal DPM yang solid, adaptif, dan saling mendukung guna mengoptimalkan kinerja kelembagaan',
            ],
        ]);

        // Proses setiap kandidat yang telah dibuat
        $nimCalon = ['2408551063', '2408561090'];
        Kandidat::all()->each(function ($kandidat, $i) use ($nimCalon) {
            // Ambil kegiatan dari kandidat yang dibuat
            // $id_kegiatan = $kandidat->id_kegiatan;
            // $kegiatan = Kegiatan::find($id_kegiatan);

            // Ambil 1 mahasiswa secara acak dengan program studi yang sama sebagai calon ketua
            // $mahasiswa = User::whereNotIn('nim', function ($query) {
            //         $query->select('nim')
            //             ->from('mahasiswa_kandidat');
            //     })
            //     ->get()
            //     ->random(1)
            //     ->first();
            $mahasiswa = User::find($nimCalon[$i % 2]);
            if ($mahasiswa) {
                $kandidat->mahasiswa()->attach($mahasiswa->nim, [
                    'jabatan' => 'ketua',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
