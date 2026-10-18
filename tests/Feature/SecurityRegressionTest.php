<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Kegiatan;
use App\Models\Kandidat;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifikasi routes/console.php bebas route HTTP,
     * route caching berjalan bersih, serta endpoint registrasi publik dan enumerasi tidak aktif.
     */
    public function test_disabled_auth_and_student_check_routes_are_not_accessible_and_console_routes_are_clean(): void
    {
        // 1. Verifikasi isi routes/console.php tidak mengandung deklarasi route HTTP
        $consoleRoutesPath = base_path('routes/console.php');
        $this->assertFileExists($consoleRoutesPath);
        $content = file_get_contents($consoleRoutesPath);
        $this->assertStringNotContainsString('Route::get', $content);
        $this->assertStringNotContainsString('Route::post', $content);
        $this->assertStringNotContainsString('check-student', $content);

        // 2. check-student tidak ada sama sekali di routing -> 404
        $this->get('/check-student')->assertNotFound();
        $this->post('/check-student', ['nim' => '2208561001'])->assertNotFound();

        // 3. Action submit pendaftaran mandiri (POST /register) tidak terdaftar -> 405 Method Not Allowed
        $this->post('/register', [
            'nim' => '2208561099',
            'nama' => 'Pendaftar Liar',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(405);
        $this->assertGuest();

        // 4. GET /register mengembalikan layar login (bukan form pendaftaran mandiri Breeze)
        $registerRes = $this->get('/register');
        $registerRes->assertOk();
        $registerRes->assertInertia(fn ($page) => $page->component('auth/Login'));
    }

    /**
     * Verifikasi bahwa tabel surat_suara tidak memiliki kolom kandidat,
     * dan pencatatan riwayat vote tidak mengaitkan identitas pemilih ke pilihan kandidat.
     */
    public function test_surat_suara_schema_and_vote_history_do_not_link_voter_to_candidate(): void
    {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('surat_suara');
        $this->assertNotContains('kandidat_id', $columns);
        $this->assertNotContains('id_kandidat', $columns);
        $this->assertNotContains('pilihan', $columns);
        $this->assertNotContains('kandidat', $columns);
    }

    /**
     * Verifikasi migrasi kolom two_factor pada tabel mahasiswa dan uji rollback (down).
     */
    public function test_two_factor_columns_migration_and_rollback(): void
    {
        // 1. Verifikasi kolom ada setelah up()
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumns('mahasiswa', [
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ]));

        // 2. Eksekusi down() pada migrasi
        $migration = require database_path('migrations/2026_10_16_230000_add_two_factor_columns_to_mahasiswa_table.php');
        $migration->down();

        // 3. Verifikasi kolom berhasil dihapus
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('mahasiswa', 'two_factor_secret'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('mahasiswa', 'two_factor_recovery_codes'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('mahasiswa', 'two_factor_confirmed_at'));

        // 4. Eksekusi up() kembali untuk integritas test suite
        $migration->up();
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumns('mahasiswa', [
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ]));
    }

    /**
     * Penghapusan mahasiswa tanpa catatan suara/kandidat berhasil.
     */
    public function test_delete_mahasiswa_without_protected_records_succeeds(): void
    {
        $admin = User::factory()->admin()->create(['nim' => 'admin_del_1']);
        $student = User::factory()->create(['nim' => '2408561001']);

        $response = $this->actingAs($admin)->delete(route('users.destroy', $student->nim));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Data mahasiswa berhasil dihapus.');
        $this->assertDatabaseMissing('mahasiswa', ['nim' => $student->nim]);
    }

    /**
     * Penghapusan mahasiswa dengan catatan suara (surat_suara) ditolak tanpa error 500.
     */
    public function test_delete_mahasiswa_with_vote_is_rejected_and_vote_remains_intact(): void
    {
        $admin = User::factory()->admin()->create(['nim' => 'admin_del_2']);
        $voter = User::factory()->create(['nim' => '2408561002']);

        $kegiatan = Kegiatan::create([
            'nama'             => 'Musma FMIPA Uji FK',
            'tahun'            => (int) date('Y'),
            'waktu_mulai'      => now()->subHour(),
            'waktu_selesai'    => now()->addHour(),
            'ruang_lingkup'    => 'fakultas',
            'id_program_studi' => null,
            'foto'             => 'kegiatan.jpg',
        ]);

        DB::table('surat_suara')->insert([
            'id_kegiatan' => $kegiatan->id,
            'nim'         => $voter->nim,
            'has_vote'    => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('users.destroy', $voter->nim));

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Data mahasiswa tidak dapat dihapus karena terkait dengan data voting atau kandidat.');

        // Mahasiswa dan catatan suara tetap utuh
        $this->assertDatabaseHas('mahasiswa', ['nim' => $voter->nim]);
        $this->assertDatabaseHas('surat_suara', [
            'id_kegiatan' => $kegiatan->id,
            'nim'         => $voter->nim,
        ]);
    }

    /**
     * Penghapusan mahasiswa yang terdaftar sebagai kandidat (mahasiswa_kandidat) ditolak tanpa error 500.
     */
    public function test_delete_mahasiswa_with_kandidat_record_is_rejected(): void
    {
        $admin = User::factory()->admin()->create(['nim' => 'admin_del_3']);
        $candidateStudent = User::factory()->create(['nim' => '2408561003']);

        $kegiatan = Kegiatan::create([
            'nama'             => 'Musma FMIPA Uji Kandidat',
            'tahun'            => (int) date('Y'),
            'waktu_mulai'      => now()->subHour(),
            'waktu_selesai'    => now()->addHour(),
            'ruang_lingkup'    => 'fakultas',
            'id_program_studi' => null,
            'foto'             => 'kegiatan.jpg',
        ]);

        $kandidat = Kandidat::create([
            'id_kegiatan'  => $kegiatan->id,
            'no_urut'      => '01',
            'visi'         => 'Visi',
            'misi'         => 'Misi',
            'jumlah_suara' => 0,
        ]);

        DB::table('mahasiswa_kandidat')->insert([
            'id_kandidat' => $kandidat->id,
            'nim'         => $candidateStudent->nim,
            'jabatan'     => 'ketua',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('users.destroy', $candidateStudent->nim));

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Data mahasiswa tidak dapat dihapus karena terkait dengan data voting atau kandidat.');

        $this->assertDatabaseHas('mahasiswa', ['nim' => $candidateStudent->nim]);
        $this->assertDatabaseHas('mahasiswa_kandidat', [
            'id_kandidat' => $kandidat->id,
            'nim'         => $candidateStudent->nim,
        ]);
    }

    /**
     * Jalur penghapusan massal (bulk delete) melewati mahasiswa yang punya catatan suara/kandidat
     * tanpa crash/500, menghapus yang tidak punya catatan, dan melaporkan ringkasannya.
     */
    public function test_bulk_delete_mahasiswa_skips_students_with_protected_records_without_crashing(): void
    {
        $admin = User::factory()->admin()->create(['nim' => 'admin_del_bulk']);

        $studentNoVote1 = User::factory()->create(['nim' => '2408561011']);
        $studentWithVote = User::factory()->create(['nim' => '2408561012']);
        $studentNoVote2 = User::factory()->create(['nim' => '2408561013']);

        $kegiatan = Kegiatan::create([
            'nama'             => 'Musma Bulk FK',
            'tahun'            => (int) date('Y'),
            'waktu_mulai'      => now()->subHour(),
            'waktu_selesai'    => now()->addHour(),
            'ruang_lingkup'    => 'fakultas',
            'id_program_studi' => null,
            'foto'             => 'kegiatan.jpg',
        ]);

        DB::table('surat_suara')->insert([
            'id_kegiatan' => $kegiatan->id,
            'nim'         => $studentWithVote->nim,
            'has_vote'    => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('users.bulk-destroy'), [
            'nims' => [
                $studentNoVote1->nim,
                $studentWithVote->nim,
                $studentNoVote2->nim,
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas(
            'success',
            '2 data mahasiswa berhasil dihapus. 1 mahasiswa dilewati karena terkait dengan data voting atau kandidat.'
        );

        $this->assertDatabaseMissing('mahasiswa', ['nim' => $studentNoVote1->nim]);
        $this->assertDatabaseMissing('mahasiswa', ['nim' => $studentNoVote2->nim]);
        $this->assertDatabaseHas('mahasiswa', ['nim' => $studentWithVote->nim]);
        $this->assertDatabaseHas('surat_suara', [
            'id_kegiatan' => $kegiatan->id,
            'nim'         => $studentWithVote->nim,
        ]);
    }

    /**
     * updated_at kandidat tidak berubah saat vote dilakukan,
     * hitungan suara tetap bertambah secara benar, dan double vote ditolak.
     */
    public function test_kandidat_updated_at_is_unchanged_when_vote_is_cast_and_tally_increments_correctly(): void
    {
        $voter = User::factory()->create([
            'nim'      => '2408561099',
            'status'   => 'aktif',
            'is_admin' => false,
        ]);

        $kegiatan = Kegiatan::create([
            'nama'             => 'Musma FMIPA UpdatedAt Test',
            'tahun'            => (int) date('Y'),
            'waktu_mulai'      => now()->subHour(),
            'waktu_selesai'    => now()->addHour(),
            'ruang_lingkup'    => 'fakultas',
            'id_program_studi' => null,
            'foto'             => 'kegiatan.jpg',
        ]);

        $kandidat = Kandidat::create([
            'id_kegiatan'  => $kegiatan->id,
            'no_urut'      => '01',
            'visi'         => 'Visi',
            'misi'         => 'Misi',
            'jumlah_suara' => 0,
        ]);

        $frozenTime = '2026-10-01 08:00:00';
        DB::table('kandidat')
            ->where('id', $kandidat->id)
            ->update([
                'created_at' => $frozenTime,
                'updated_at' => $frozenTime,
            ]);

        $beforeUpdatedAt = DB::table('kandidat')->where('id', $kandidat->id)->value('updated_at');

        $response = $this->actingAs($voter)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);

        $response->assertRedirect(route('home'));

        // Pastikan updated_at sebelum == sesudah vote
        $afterUpdatedAt = DB::table('kandidat')->where('id', $kandidat->id)->value('updated_at');
        $this->assertSame($beforeUpdatedAt, $afterUpdatedAt, 'updated_at kandidat tidak boleh berubah saat vote');

        // Pastikan jumlah_suara naik tepat 1
        $this->assertSame(1, (int) DB::table('kandidat')->where('id', $kandidat->id)->value('jumlah_suara'));

        // Percobaan vote kedua ditolak dan tidak mengubah jumlah_suara maupun updated_at
        $secondResponse = $this->actingAs($voter)->post('/vote/' . $kegiatan->id, [
            'kandidat' => $kandidat->id,
        ]);

        $secondResponse->assertRedirect(route('home'));
        $this->assertSame(1, (int) DB::table('kandidat')->where('id', $kandidat->id)->value('jumlah_suara'));
        $this->assertSame($beforeUpdatedAt, DB::table('kandidat')->where('id', $kandidat->id)->value('updated_at'));
    }

    /**
     * Baris suara baru pada surat_suara disimpan dengan waktu yang telah dibulatkan
     * ke awal jam (YYYY-MM-DD HH:00:00) untuk has_vote, created_at, dan updated_at.
     */
    public function test_new_vote_record_timestamps_are_coarsened_to_the_hour(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-16 13:42:57'));

        try {
            $voter = User::factory()->create([
                'nim'      => '2408561077',
                'status'   => 'aktif',
                'is_admin' => false,
            ]);

            $kegiatan = Kegiatan::create([
                'nama'             => 'Musma FMIPA Coarsen Timestamp Test',
                'tahun'            => 2026,
                'waktu_mulai'      => Carbon::parse('2026-10-16 08:00:00'),
                'waktu_selesai'    => Carbon::parse('2026-10-16 17:00:00'),
                'ruang_lingkup'    => 'fakultas',
                'id_program_studi' => null,
                'foto'             => 'kegiatan.jpg',
            ]);

            $kandidat = Kandidat::create([
                'id_kegiatan'  => $kegiatan->id,
                'no_urut'      => '01',
                'visi'         => 'Visi Coarsen',
                'misi'         => 'Misi Coarsen',
                'jumlah_suara' => 0,
            ]);

            $response = $this->actingAs($voter)->post('/vote/' . $kegiatan->id, [
                'kandidat' => $kandidat->id,
            ]);

            $response->assertRedirect(route('home'));

            $ballot = DB::table('surat_suara')
                ->where('nim', $voter->nim)
                ->where('id_kegiatan', $kegiatan->id)
                ->first();

            $this->assertNotNull($ballot);
            $this->assertSame('2026-10-16 13:00:00', $ballot->has_vote);
            $this->assertSame('2026-10-16 13:00:00', $ballot->created_at);
            $this->assertSame('2026-10-16 13:00:00', $ballot->updated_at);
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * Command `votes:coarsen-timestamps` berjalan sebagai dry-run secara default
     * (tidak mengubah data), menolak jalan di production tanpa konfirmasi, dan membulatkan timestamp data saat diberi `--force`.
     */
    public function test_votes_coarsen_timestamps_command_dry_run_and_force_and_production_guard(): void
    {
        $voter1 = User::factory()->create(['nim' => '2408561081']);
        $voter2 = User::factory()->create(['nim' => '2408561082']);

        $kegiatan = Kegiatan::create([
            'nama'             => 'Musma Coarsen Command Test',
            'tahun'            => (int) date('Y'),
            'waktu_mulai'      => now()->subHour(),
            'waktu_selesai'    => now()->addHour(),
            'ruang_lingkup'    => 'fakultas',
            'id_program_studi' => null,
            'foto'             => 'kegiatan.jpg',
        ]);

        DB::table('surat_suara')->insert([
            [
                'nim'         => $voter1->nim,
                'id_kegiatan' => $kegiatan->id,
                'has_vote'    => '2026-10-10 09:34:51',
                'created_at'  => '2026-10-10 09:34:51',
                'updated_at'  => '2026-10-10 09:35:12',
            ],
            [
                'nim'         => $voter2->nim,
                'id_kegiatan' => $kegiatan->id,
                'has_vote'    => '2026-10-10 14:18:09',
                'created_at'  => '2026-10-10 14:18:09',
                'updated_at'  => '2026-10-10 14:19:44',
            ],
        ]);

        // 1. Default DRY-RUN: tidak boleh mengubah data
        $this->artisan('votes:coarsen-timestamps')
            ->expectsOutputToContain('[DRY-RUN]')
            ->assertExitCode(0);

        $this->assertDatabaseHas('surat_suara', [
            'nim'        => $voter1->nim,
            'has_vote'   => '2026-10-10 09:34:51',
            'created_at' => '2026-10-10 09:34:51',
            'updated_at' => '2026-10-10 09:35:12',
        ]);
        $this->assertDatabaseHas('surat_suara', [
            'nim'        => $voter2->nim,
            'has_vote'   => '2026-10-10 14:18:09',
            'created_at' => '2026-10-10 14:18:09',
            'updated_at' => '2026-10-10 14:19:44',
        ]);

        // 2. Proteksi APP_ENV=production: menolak --force tanpa --confirm-production / konfirmasi
        $originalEnv = $this->app['env'];
        $this->app['env'] = 'production';
        try {
            $this->artisan('votes:coarsen-timestamps', [
                '--force'          => true,
                '--no-interaction' => true,
            ])->assertExitCode(1);
        } finally {
            $this->app['env'] = $originalEnv;
        }

        // Pastikan data masih belum berubah setelah ditolak di production
        $this->assertDatabaseHas('surat_suara', [
            'nim'        => $voter1->nim,
            'has_vote'   => '2026-10-10 09:34:51',
            'created_at' => '2026-10-10 09:34:51',
            'updated_at' => '2026-10-10 09:35:12',
        ]);

        // 3. Eksekusi dengan --force: membulatkan timestamp data uji ke awal jam
        $this->artisan('votes:coarsen-timestamps', ['--force' => true])
            ->expectsOutputToContain('[APPLIED]')
            ->assertExitCode(0);

        $this->assertDatabaseHas('surat_suara', [
            'nim'        => $voter1->nim,
            'has_vote'   => '2026-10-10 09:00:00',
            'created_at' => '2026-10-10 09:00:00',
            'updated_at' => '2026-10-10 09:00:00',
        ]);
        $this->assertDatabaseHas('surat_suara', [
            'nim'        => $voter2->nim,
            'has_vote'   => '2026-10-10 14:00:00',
            'created_at' => '2026-10-10 14:00:00',
            'updated_at' => '2026-10-10 14:00:00',
        ]);
    }

    /**
     * Migrasi tabel audit_logs dan uji rollback (down).
     */
    public function test_audit_logs_migration_and_rollback(): void
    {
        // 1. Verifikasi tabel audit_logs dan kolom-kolomnya ada setelah up()
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('audit_logs'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumns('audit_logs', [
            'id',
            'admin_nim',
            'aksi',
            'target_type',
            'target_id',
            'ip',
            'user_agent',
            'metadata',
            'created_at',
            'updated_at',
        ]));

        // 2. Eksekusi down() pada migrasi audit_logs
        $migration = require database_path('migrations/2026_10_16_233000_create_audit_logs_table.php');
        $migration->down();

        // 3. Verifikasi tabel berhasil dihapus
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('audit_logs'));

        // 4. Eksekusi up() kembali agar skema tetap utuh untuk test lain
        $migration->up();
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('audit_logs'));
    }

    /**
     * Proteksi route /audit-logs khusus admin terverifikasi.
     * Tamu dan mahasiswa non-admin dilarang mengakses.
     */
    public function test_audit_logs_endpoint_access_control(): void
    {
        $admin = User::factory()->admin()->create(['nim' => 'admin_audit_1']);
        $student = User::factory()->create(['nim' => '2408561088', 'is_admin' => false]);

        // 1. Tamu diarahkan ke login
        $this->get('/audit-logs')->assertRedirect(route('login'));

        // 2. Mahasiswa biasa (non-admin) ditolak via middleware IsAdmin (403 Forbidden)
        $this->actingAs($student)->get('/audit-logs')->assertForbidden();

        // 3. Admin berhak mengakses (JSON request mengembalikan 200 dengan struktur paginasi)
        $response = $this->actingAs($admin)->getJson('/audit-logs');
        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'current_page',
            'total',
        ]);
    }

    /**
     * Aksi admin (seperti update mahasiswa) otomatis tercatat di tabel audit_logs.
     */
    public function test_admin_actions_trigger_audit_log_entries(): void
    {
        $admin = User::factory()->admin()->create(['nim' => 'admin_audit_2']);
        $student = User::factory()->create(['nim' => '2408561089']);

        // Admin melakukan update data mahasiswa
        $response = $this->actingAs($admin)->put(route('users.update', $student->nim), [
            'nim'              => $student->nim,
            'nama'             => 'Nama Mahasiswa Diubah',
            'email'            => 'changed@student.unud.ac.id',
            'id_program_studi' => 1,
            'angkatan'         => 2024,
        ]);

        $response->assertRedirect();

        // Verifikasi audit log tersimpan
        $log = AuditLog::where('aksi', 'mahasiswa.update')
            ->where('target_id', $student->nim)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($admin->nim, $log->admin_nim);
        $this->assertSame('user', $log->target_type);
        $this->assertIsArray($log->metadata);
        $this->assertSame('Nama Mahasiswa Diubah', $log->metadata['nama']);
    }

    /**
     * Sanitasi metadata AuditLogService melarang password, token,
     * secret 2FA, dan pilihan suara/kandidat.
     */
    public function test_audit_log_service_strictly_sanitizes_metadata(): void
    {
        $dirtyMetadata = [
            'nama'                      => 'Budi Santoso',
            'password'                  => 'SecretPassword123!',
            'plain_password'            => 'SecretPassword123!',
            'token'                     => 'Bearer xyz123',
            'remember_token'            => 'rem123',
            'two_factor_secret'         => 'TOTP_SECRET_KEY',
            'two_factor_recovery_codes' => ['code1', 'code2'],
            'kandidat'                  => 1,
            'id_kandidat'               => 2,
            'pilihan'                   => 'Kandidat 01',
            'vote'                      => 'secret_ballot',
            'nested'                    => [
                'safe_info' => 'Valid information',
                'password'  => 'NestedPassword',
                'vote'      => 'NestedChoice',
            ],
        ];

        $clean = AuditLogService::sanitizeMetadata($dirtyMetadata);

        // Kunci aman tetap ada
        $this->assertArrayHasKey('nama', $clean);
        $this->assertSame('Budi Santoso', $clean['nama']);
        $this->assertArrayHasKey('nested', $clean);
        $this->assertSame('Valid information', $clean['nested']['safe_info']);

        // Kunci sensitif tereliminasi sepenuhnya
        $this->assertArrayNotHasKey('password', $clean);
        $this->assertArrayNotHasKey('plain_password', $clean);
        $this->assertArrayNotHasKey('token', $clean);
        $this->assertArrayNotHasKey('remember_token', $clean);
        $this->assertArrayNotHasKey('two_factor_secret', $clean);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $clean);
        $this->assertArrayNotHasKey('kandidat', $clean);
        $this->assertArrayNotHasKey('id_kandidat', $clean);
        $this->assertArrayNotHasKey('pilihan', $clean);
        $this->assertArrayNotHasKey('vote', $clean);
        $this->assertArrayNotHasKey('password', $clean['nested']);
        $this->assertArrayNotHasKey('vote', $clean['nested']);
    }

    /**
     * Tidak ada panggilan env() di luar folder config/.
     * Seluruh konfigurasi runtime harus melalui config() agar aman saat config:cache.
     */
    public function test_no_env_calls_outside_config_directory(): void
    {
        $directories = [
            app_path(),
            base_path('routes'),
            base_path('bootstrap'),
        ];

        $envCalls = [];

        foreach ($directories as $dir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    if (str_contains($file->getPathname(), 'bootstrap' . DIRECTORY_SEPARATOR . 'cache')) {
                        continue;
                    }

                    $content = file_get_contents($file->getPathname());
                    if (preg_match('/\benv\s*\(/', $content)) {
                        $envCalls[] = $file->getPathname();
                    }
                }
            }
        }

        $this->assertEmpty($envCalls, 'Ditemukan panggilan env() di luar config/: ' . implode(', ', $envCalls));
    }

    /**
     * Verifikasi resolusi client IP dan scheme (HTTPS/HTTP) dengan header forwarded
     * ketika reverse proxy berada dalam daftar trusted proxies vs ketika tidak dipercaya.
     */
    public function test_trusted_proxy_scheme_and_ip_detection_with_trusted_and_untrusted_headers(): void
    {
        // 1. Proxy terpercaya: client IP dan scheme mengikuti header forwarded meskipun backend menerima via HTTP
        \Illuminate\Http\Middleware\TrustProxies::flushState();
        config(['trustedproxy.proxies' => ['192.168.1.50']]);

        $this->withServerVariables([
            'REMOTE_ADDR'            => '192.168.1.50',
            'HTTP_X_FORWARDED_FOR'   => '203.0.113.195',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT'  => '443',
        ])->get('http://localhost/');

        $this->assertSame('203.0.113.195', request()->ip());
        $this->assertSame('https', request()->getScheme());
        $this->assertTrue(request()->isSecure());

        // 2. Proxy tidak terpercaya: client IP dan scheme tetap mengacu pada direct connection (mencegah spoofing)
        \Illuminate\Http\Middleware\TrustProxies::flushState();
        config(['trustedproxy.proxies' => ['192.168.1.50']]);

        $this->withServerVariables([
            'REMOTE_ADDR'            => '198.51.100.2',
            'HTTP_X_FORWARDED_FOR'   => '203.0.113.195',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT'  => '443',
        ])->get('http://localhost/');

        $this->assertSame('198.51.100.2', request()->ip());
        $this->assertSame('http', request()->getScheme());
        $this->assertFalse(request()->isSecure());
    }

    /**
     * Verifikasi route diagnostik staging /staging/proxy-diagnostic:
     * - Tamu dialihkan ke login (302)
     * - Mahasiswa non-admin ditolak via IsAdmin (403 Forbidden)
     * - Admin menerima respon JSON 200 berisi informasi IP, scheme, dan header forwarded tanpa token/cookie/rahasia
     */
    public function test_staging_proxy_diagnostic_endpoint_access_control(): void
    {
        // 1. Guest ditolak (redirect 302 ke login)
        $response = $this->get('/staging/proxy-diagnostic');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // 2. Mahasiswa non-admin ditolak (403 Forbidden via IsAdmin middleware)
        $student = User::factory()->create([
            'nim'               => '2408561080',
            'is_admin'          => false,
            'status'            => 'aktif',
            'email_verified_at' => now(),
        ]);
        $response = $this->actingAs($student)->get('/staging/proxy-diagnostic');
        $response->assertForbidden();

        // 3. Admin diizinkan (200 JSON dengan data diagnostik yang aman)
        $admin = User::factory()->admin()->create([
            'nim'               => 'admin_diag',
            'status'            => 'aktif',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->withServerVariables([
                'REMOTE_ADDR'            => '10.0.0.1',
                'HTTP_X_FORWARDED_FOR'   => '203.0.113.200',
                'HTTP_X_FORWARDED_PROTO' => 'https',
                'HTTP_X_FORWARDED_HOST'  => 'musma.test',
                'HTTP_X_FORWARDED_PORT'  => '443',
            ])
            ->get('/staging/proxy-diagnostic');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'client_ip',
            'scheme',
            'is_secure',
            'remote_addr',
            'x_forwarded_for',
            'x_forwarded_proto',
            'x_forwarded_host',
            'x_forwarded_port',
        ]);

        $json = $response->json();
        $this->assertArrayNotHasKey('password', $json);
        $this->assertArrayNotHasKey('cookie', $json);
        $this->assertArrayNotHasKey('token', $json);
        $this->assertArrayNotHasKey('secret', $json);
    }

    /**
     * Verifikasi route diagnostik staging dinonaktifkan sepenuhnya (404) ketika APP_ENV=production.
     */
    public function test_staging_proxy_diagnostic_endpoint_is_disabled_in_production(): void
    {
        $admin = User::factory()->admin()->create([
            'nim'               => 'admin_diag_prod',
            'status'            => 'aktif',
            'email_verified_at' => now(),
        ]);

        $originalEnv = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            $response = $this->actingAs($admin)->get('/staging/proxy-diagnostic');
            $response->assertStatus(404);
        } finally {
            $this->app['env'] = $originalEnv;
        }
    }
}
