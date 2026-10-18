<?php

namespace Tests\Feature;

use App\Mail\MahasiswaCredentials;
use App\Models\User;
use App\Services\CredentialMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Pengujian pengiriman kredensial mahasiswa melalui CLI dan Web.
 */
class CredentialSendingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanpa jeda/backoff nyata agar suite cepat; logika jeda tetap dieksekusi.
        config([
            'services.ses.throttle_delay_ms' => 0,
            'services.ses.backoff_base_ms'   => 0,
            'services.ses.max_retries'       => 3,
        ]);
    }

    private function student(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'password'            => Hash::make('OldPassword123'),
            'credentials_sent_at' => null,
        ], $attrs));
    }

    public function test_cli_command_uses_csprng_12_char_password_and_no_rand_shuffle(): void
    {
        $source = file_get_contents(app_path('Console/Commands/SendMahasiswaCredentials.php'));
        $this->assertDoesNotMatchRegularExpression('/\brand\s*\(/', $source);
        $this->assertDoesNotMatchRegularExpression('/\bshuffle\s*\(/', $source);

        Mail::fake();
        $student = $this->student();

        $this->artisan('mahasiswa:send-credentials', ['--nim' => $student->nim])->assertExitCode(0);

        Mail::assertSent(MahasiswaCredentials::class, function (MahasiswaCredentials $mail) use ($student) {
            $student->refresh();

            return strlen($mail->plainPassword) === 12
                && ctype_alnum($mail->plainPassword)
                && Hash::check($mail->plainPassword, $student->password);
        });
        $this->assertNotNull($student->fresh()->credentials_sent_at);
    }

    public function test_failed_email_never_changes_password_web_and_cli(): void
    {
        $admin = User::factory()->admin()->create();
        $web = $this->student();
        $cli = $this->student();
        $webHash = $web->password;
        $cliHash = $cli->password;

        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('Connection could not be established'));

        $this->actingAs($admin)
            ->post(route('users.send-credentials', $web->nim))
            ->assertStatus(302)
            ->assertSessionHas('error');

        $this->artisan('mahasiswa:send-credentials', ['--nim' => $cli->nim])
            ->expectsOutputToContain("Gagal kirim ke {$cli->nim}")
            ->assertExitCode(0);

        $this->assertSame($webHash, $web->fresh()->password);
        $this->assertNull($web->fresh()->credentials_sent_at);
        $this->assertSame($cliHash, $cli->fresh()->password);
        $this->assertTrue(Hash::check('OldPassword123', $cli->fresh()->password));
        $this->assertNull($cli->fresh()->credentials_sent_at);
    }

    public function test_throttling_error_is_retried_then_succeeds(): void
    {
        $student = $this->student();
        $calls = 0;

        Mail::shouldReceive('to')->twice()->andReturnSelf();
        Mail::shouldReceive('send')->twice()->andReturnUsing(function () use (&$calls) {
            $calls++;
            if ($calls === 1) {
                throw new \RuntimeException('454 Throttling failure: Maximum sending rate exceeded.');
            }

            return null;
        });

        $this->assertTrue(app(CredentialMailer::class)->send($student));
        $this->assertSame(2, $calls);
        $this->assertNotNull($student->fresh()->credentials_sent_at);
    }

    public function test_daily_quota_exceeded_does_not_trigger_retry(): void
    {
        $student = $this->student();
        $hash = $student->password;

        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('454 Throttling failure: Daily message quota exceeded.'));

        $this->assertFalse(app(CredentialMailer::class)->send($student));
        $this->assertSame($hash, $student->fresh()->password);
        $this->assertNull($student->fresh()->credentials_sent_at);
    }

    public function test_is_throttling_exception_matches_only_rate_errors(): void
    {
        $mailer = app(CredentialMailer::class);
        $is = fn (string $m) => $mailer->isThrottlingException(new \RuntimeException($m));

        $this->assertTrue($is('454 Throttling failure: Maximum sending rate exceeded.'));
        $this->assertTrue($is('Expected response code 250 but got code "429", Too Many Requests'));
        $this->assertTrue($is('Throttling'));
        $this->assertFalse($is('454 Throttling failure: Daily message quota exceeded.'));
        $this->assertFalse($is('Sending quota exceeded'));
        $this->assertFalse($is('554 Message rejected: Email address is not verified'));
        $this->assertFalse($is('Connection timed out after 14290 ms'));
    }

    public function test_bulk_lock_blocks_concurrent_runs_and_is_released_on_sync_path(): void
    {
        $admin = User::factory()->admin()->create();
        $this->student();

        $external = Cache::lock(CredentialMailer::BULK_LOCK_KEY, 60);
        $this->assertTrue($external->get());

        $this->actingAs($admin)
            ->post(route('users.send-bulk-credentials'))
            ->assertStatus(302)
            ->assertSessionHas('error');

        $this->artisan('mahasiswa:send-credentials')
            ->expectsOutputToContain('lock aktif')
            ->assertExitCode(1);

        $external->release();

        Mail::fake();
        $this->actingAs($admin)
            ->post(route('users.send-bulk-credentials'), ['only_unsent' => true])
            ->assertStatus(302)
            ->assertSessionHas('success');

        $again = Cache::lock(CredentialMailer::BULK_LOCK_KEY, 60);
        $this->assertTrue($again->get(), 'Lock harus dilepas setelah jalur sinkron selesai');
        $again->release();
    }

    public function test_bulk_deferred_path_sends_all_and_releases_lock(): void
    {
        $admin = User::factory()->admin()->create();
        $students = collect(range(1, 25))->map(fn () => $this->student());

        Mail::fake();

        $this->actingAs($admin)
            ->post(route('users.send-bulk-credentials'), ['only_unsent' => true])
            ->assertStatus(302)
            ->assertSessionHas('success');

        // Deferred callbacks dijalankan saat terminate di test kernel.
        Mail::assertSent(MahasiswaCredentials::class, 25);
        foreach ($students as $s) {
            $this->assertNotNull($s->fresh()->credentials_sent_at);
        }

        $again = Cache::lock(CredentialMailer::BULK_LOCK_KEY, 60);
        $this->assertTrue($again->get(), 'Lock harus dilepas di dalam closure defer');
        $again->release();
    }

    public function test_error_before_defer_branch_releases_lock(): void
    {
        $admin = User::factory()->admin()->create();
        $this->student();

        // Paksa error di dalam blok try (setelah lock diambil, sebelum percabangan defer).
        $this->app->bind(CredentialMailer::class, function () {
            throw new \RuntimeException('boom');
        });

        $this->withoutExceptionHandling();
        try {
            $this->actingAs($admin)->post(route('users.send-bulk-credentials'));
            $this->fail('Exception expected');
        } catch (\RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $again = Cache::lock(CredentialMailer::BULK_LOCK_KEY, 60);
        $this->assertTrue($again->get(), 'Lock harus dilepas oleh finally saat error sebelum defer');
        $again->release();
    }

    public function test_plaintext_password_is_never_logged(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->student();
        $captured = null;

        Mail::fake();
        Log::spy();

        $this->actingAs($admin)->post(route('users.send-credentials', $student->nim))->assertSessionHas('success');

        Mail::assertSent(MahasiswaCredentials::class, function ($mail) use (&$captured) {
            $captured = $mail->plainPassword;

            return true;
        });

        $this->assertNotNull($captured);
        foreach (['info', 'warning', 'error', 'debug', 'notice'] as $level) {
            Log::shouldNotHaveReceived($level, [\Mockery::on(fn ($msg) => is_string($msg) && str_contains($msg, $captured))]);
        }
    }
}
