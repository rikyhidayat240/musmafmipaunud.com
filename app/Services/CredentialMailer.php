<?php

namespace App\Services;

use App\Mail\MahasiswaCredentials;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Pengiriman kredensial login mahasiswa (dipakai CLI dan Web).
 *
 * Aturan keamanan:
 * - Password dibuat dengan CSPRNG (Str::password), 12 karakter.
 * - Password di DB hanya diubah SETELAH email berhasil dikirim, sehingga
 *   kegagalan SMTP/SES tidak mengunci mahasiswa dari password lamanya.
 * - Retry dengan backoff hanya untuk error laju (throttling), bukan kuota harian.
 * - Password plaintext tidak pernah ditulis ke log.
 */
class CredentialMailer
{
    public const BULK_LOCK_KEY = 'musma_bulk_email_lock';

    public const BULK_LOCK_SECONDS = 3600;

    /** Pesan error terakhir (tanpa password) untuk ditampilkan/log oleh pemanggil. */
    public ?string $lastError = null;

    public function generatePassword(): string
    {
        return Str::password(12, letters: true, numbers: true, symbols: false, spaces: false);
    }

    public function throttleDelayMs(): int
    {
        return max(0, (int) config('services.ses.throttle_delay_ms', 100));
    }

    public function maxRetries(): int
    {
        return max(1, (int) config('services.ses.max_retries', 3));
    }

    /**
     * Kirim kredensial baru ke satu mahasiswa dengan retry untuk error throttling.
     *
     * @param  callable|null  $onRetry  fn(string $message): void, dipanggil sebelum menunggu retry.
     */
    public function send(User $mahasiswa, ?int $maxRetries = null, ?callable $onRetry = null): bool
    {
        $maxRetries = $maxRetries ?? $this->maxRetries();
        $backoffBaseMs = max(0, (int) config('services.ses.backoff_base_ms', 1000));
        $this->lastError = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $plainPassword = $this->generatePassword();

                Mail::to($mahasiswa->email)->send(new MahasiswaCredentials($mahasiswa, $plainPassword));

                $mahasiswa->update([
                    'password'            => Hash::make($plainPassword),
                    'email_verified_at'   => $mahasiswa->email_verified_at ?? now(),
                    'credentials_sent_at' => now(),
                ]);

                return true;
            } catch (\Throwable $e) {
                $this->lastError = $e->getMessage();

                if ($attempt < $maxRetries && $this->isThrottlingException($e)) {
                    $sleepMs = $backoffBaseMs * (2 ** ($attempt - 1));
                    $message = "Throttling email untuk nim={$mahasiswa->nim} (percobaan {$attempt}/{$maxRetries}), retry dalam {$sleepMs}ms: {$e->getMessage()}";
                    Log::warning($message);
                    if ($onRetry) {
                        $onRetry($message);
                    }
                    if ($sleepMs > 0) {
                        usleep($sleepMs * 1000);
                    }

                    continue;
                }

                Log::warning("Credential email failed for nim={$mahasiswa->nim}: {$e->getMessage()}");

                return false;
            }
        }

        return false;
    }

    /**
     * Error laju sementara saja (mis. "454 Throttling failure: Maximum sending rate exceeded",
     * "429 Too Many Requests"). Kuota harian/kuota lain TIDAK di-retry.
     */
    public function isThrottlingException(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'daily') || str_contains($message, 'quota')) {
            return false;
        }

        return str_contains($message, 'maximum sending rate exceeded')
            || str_contains($message, 'rate exceeded')
            || str_contains($message, 'too many requests')
            || str_contains($message, 'throttling')
            || (bool) preg_match('/\b429\b/', $message)
            || ((bool) preg_match('/\b454\b/', $message) && (str_contains($message, 'throttl') || str_contains($message, 'rate')));
    }
}
