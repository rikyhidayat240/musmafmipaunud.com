<?php

namespace Tests\Feature;

use App\Models\Kandidat;
use App\Models\Kegiatan;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecurityVoteTest extends TestCase
{
    use RefreshDatabase;

    private function createVoter(array $attributes = []): User
    {
        return User::forceCreate(array_merge([
            'nim'                 => '2108561001',
            'id_program_studi'    => 1,
            'nama'                => 'Mahasiswa Musma',
            'angkatan'            => 2021,
            'email'               => 'test@student.unud.ac.id',
            'email_verified_at'   => now(),
            'password'            => bcrypt('password'),
            'status'              => 'aktif',
            'is_admin'            => false,
        ], $attributes));
    }

    private function createEventAndCandidate(array $kegiatanAttributes = []): array
    {
        $kegiatan = Kegiatan::create(array_merge([
            'nama'             => 'Pemilu Ketua DPM FMIPA 2026',
            'tahun'            => (int) date('Y'),
            'waktu_mulai'      => now()->subHour(),
            'waktu_selesai'    => now()->addHour(),
            'ruang_lingkup'    => 'fakultas',
            'id_program_studi' => null,
            'foto'             => 'foto.jpg',
        ], $kegiatanAttributes));

        $kandidat = Kandidat::create([
            'id_kegiatan'  => $kegiatan->id,
            'no_urut'      => '01',
            'visi'         => 'Visi DPM',
            'misi'         => 'Misi DPM',
            'jumlah_suara' => 0,
        ]);

        return [$kegiatan, $kandidat];
    }

    public function test_vote_logs_do_not_contain_candidate_choices(): void
    {
        Log::spy();

        $user = $this->createVoter();
        [$kegiatan, $kandidat] = $this->createEventAndCandidate();

        $response = $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);

        $response->assertRedirect(route('home'));

        // Verifikasi log info terpanggil dan tidak memuat data kandidat
        Log::shouldHaveReceived('info')->withArgs(function ($message, $context = []) use ($kandidat) {
            $hasCandidateKey = isset($context['kandidat']) || isset($context['kandidat_id']) || isset($context['id_kandidat']);
            $hasCandidateInMessage = str_contains(strtolower($message), 'kandidat');
            return ! $hasCandidateKey && ! $hasCandidateInMessage;
        })->atLeast()->once();
    }

    public function test_double_vote_logs_and_security_warnings_do_not_contain_candidate_choices(): void
    {
        Log::spy();

        $user = $this->createVoter();
        [$kegiatan, $kandidat] = $this->createEventAndCandidate();

        // Vote pertama sukses
        $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ])->assertRedirect(route('home'));

        // Vote kedua memicu double-vote warning log
        $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ])->assertRedirect(route('home'));

        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context = []) {
            $hasCandidateKey = isset($context['kandidat']) || isset($context['kandidat_id']) || isset($context['id_kandidat']);
            return str_contains($message, 'Double-vote attempt detected') && ! $hasCandidateKey;
        })->atLeast()->once();
    }

    public function test_double_vote_is_rejected_and_increments_candidate_only_once(): void
    {
        $user = $this->createVoter();
        [$kegiatan, $kandidat] = $this->createEventAndCandidate();

        // Vote pertama
        $res1 = $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);
        $res1->assertRedirect(route('home'));

        // Vote kedua
        $res2 = $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);
        $res2->assertRedirect(route('home'));

        expect($kandidat->fresh()->jumlah_suara)->toBe(1);
    }

    public function test_server_validation_rejects_inactive_voter(): void
    {
        $user = $this->createVoter(['status' => 'nonaktif']);
        [$kegiatan, $kandidat] = $this->createEventAndCandidate();

        $response = $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);

        $response->assertSessionHasErrors('user');
        expect($kandidat->fresh()->jumlah_suara)->toBe(0);
    }

    public function test_server_validation_rejects_admin_voter(): void
    {
        $user = $this->createVoter(['is_admin' => true]);
        [$kegiatan, $kandidat] = $this->createEventAndCandidate();

        $response = $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);

        $response->assertSessionHasErrors('user');
        expect($kandidat->fresh()->jumlah_suara)->toBe(0);
    }

    public function test_server_validation_rejects_vote_before_schedule(): void
    {
        $user = $this->createVoter();
        [$kegiatan, $kandidat] = $this->createEventAndCandidate([
            'waktu_mulai'   => now()->addHour(),
            'waktu_selesai' => now()->addHours(2),
        ]);

        $response = $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);

        $response->assertSessionHasErrors('kegiatan');
        expect($kandidat->fresh()->jumlah_suara)->toBe(0);
    }

    public function test_server_validation_rejects_vote_after_schedule(): void
    {
        $user = $this->createVoter();
        [$kegiatan, $kandidat] = $this->createEventAndCandidate([
            'waktu_mulai'   => now()->subHours(3),
            'waktu_selesai' => now()->subHour(),
        ]);

        $response = $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);

        $response->assertSessionHasErrors('kegiatan');
        expect($kandidat->fresh()->jumlah_suara)->toBe(0);
    }

    public function test_server_validation_rejects_kegiatan_from_other_program_studi(): void
    {
        $user = $this->createVoter(['id_program_studi' => 1]);
        [$kegiatan, $kandidat] = $this->createEventAndCandidate([
            'ruang_lingkup'    => 'program studi',
            'id_program_studi' => 2,
        ]);

        $response = $this->actingAs($user)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);

        $response->assertSessionHasErrors('kegiatan');
        expect($kandidat->fresh()->jumlah_suara)->toBe(0);
    }

    public function test_candidate_vote_count_is_hidden_from_serialization(): void
    {
        [$kegiatan, $kandidat] = $this->createEventAndCandidate();
        $kandidat->update(['jumlah_suara' => 50]);

        $kandidatJson = $kandidat->fresh()->toArray();
        expect(array_key_exists('jumlah_suara', $kandidatJson))->toBeFalse();
    }

    public function test_relogin_after_voting_does_not_reset_has_vote_or_allow_second_vote(): void
    {
        $user = $this->createVoter();
        [$kegiatan, $kandidat] = $this->createEventAndCandidate();

        // 1. Login pertama (menyiapkan baris surat_suara dengan has_vote = null)
        $this->post(route('login.store'), [
            'email'    => $user->nim,
            'password' => 'password',
        ])->assertRedirect(route('home', absolute: false));

        // 2. Mahasiswa memberikan suara
        $this->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ])->assertRedirect(route('home'));

        $pivotAfterVote = DB::table('surat_suara')
            ->where('nim', $user->nim)
            ->where('id_kegiatan', $kegiatan->id)
            ->first();
        $this->assertNotNull($pivotAfterVote->has_vote, 'has_vote harus terisi setelah memilih');
        expect($kandidat->fresh()->jumlah_suara)->toBe(1);

        // 3. Logout lalu login kembali
        $this->post(route('logout'));
        $this->post(route('login.store'), [
            'email'    => $user->nim,
            'password' => 'password',
        ])->assertRedirect(route('home', absolute: false));

        // 4. Pastikan has_vote TIDAK ter-reset menjadi null saat login ulang
        $pivotAfterRelogin = DB::table('surat_suara')
            ->where('nim', $user->nim)
            ->where('id_kegiatan', $kegiatan->id)
            ->first();
        $this->assertNotNull($pivotAfterRelogin->has_vote, 'has_vote tidak boleh ter-reset menjadi null saat login ulang');

        // 5. Percobaan vote kedua setelah login ulang harus tetap ditolak
        $this->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ])->assertRedirect(route('home'));

        expect($kandidat->fresh()->jumlah_suara)->toBe(1);
    }

    public function test_election_supports_more_than_two_candidates_in_ballot_and_results(): void
    {
        $voter = $this->createVoter();
        $admin = $this->createVoter([
            'nim'      => '9999999999',
            'email'    => 'admin@unud.ac.id',
            'is_admin' => true,
        ]);

        $kegiatan = Kegiatan::create([
            'nama'             => 'Pemilu Ketua DPM FMIPA 3 Kandidat',
            'tahun'            => (int) date('Y'),
            'waktu_mulai'      => now()->subHour(),
            'waktu_selesai'    => now()->addHour(),
            'ruang_lingkup'    => 'fakultas',
            'id_program_studi' => null,
            'foto'             => 'foto.jpg',
        ]);

        // Buat 3 kandidat secara acak urutannya untuk menguji pengurutan no_urut
        $kandidat3 = Kandidat::create([
            'id_kegiatan'  => $kegiatan->id,
            'no_urut'      => '03',
            'visi'         => 'Visi 03',
            'misi'         => 'Misi 03',
            'jumlah_suara' => 0,
        ]);
        $kandidat1 = Kandidat::create([
            'id_kegiatan'  => $kegiatan->id,
            'no_urut'      => '01',
            'visi'         => 'Visi 01',
            'misi'         => 'Misi 01',
            'jumlah_suara' => 0,
        ]);
        $kandidat2 = Kandidat::create([
            'id_kegiatan'  => $kegiatan->id,
            'no_urut'      => '02',
            'visi'         => 'Visi 02',
            'misi'         => 'Misi 02',
            'jumlah_suara' => 0,
        ]);

        // 1. Login mahasiswa (menginisialisasi baris surat_suara) lalu cek halaman bilik suara (/vote/{id})
        $this->post(route('login.store'), [
            'email'    => $voter->nim,
            'password' => 'password',
        ])->assertRedirect(route('home', absolute: false));

        $ballotResponse = $this->get('/vote/' . $kegiatan->id);
        $ballotResponse->assertOk();
        $ballotKegiatan = $ballotResponse->viewData('page')['props']['kegiatan'];
        expect($ballotKegiatan['kandidat'])->toHaveCount(3);
        expect(array_column($ballotKegiatan['kandidat'], 'no_urut'))->toBe(['01', '02', '03']);

        // 2. Mahasiswa memilih kandidat nomor urut 03
        $this->actingAs($voter)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat3->id,
        ])->assertRedirect(route('home'));

        expect($kandidat3->fresh()->jumlah_suara)->toBe(1);

        // 3. Cek halaman hasil (/result) menampilkan semua 3 kandidat (bukan dibatasi 2)
        $resultResponse = $this->actingAs($admin)->get('/result?kegiatan=' . $kegiatan->id);
        $resultResponse->assertOk();
        $resultKegiatan = $resultResponse->viewData('page')['props']['kegiatan'];
        expect($resultKegiatan['kandidat'])->toHaveCount(3);
        expect(array_column($resultKegiatan['kandidat'], 'no_urut'))->toBe(['01', '02', '03']);
        expect(array_column($resultKegiatan['kandidat'], 'jumlah_suara'))->toBe([0, 0, 1]);
    }
}

