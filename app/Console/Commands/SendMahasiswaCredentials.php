<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CredentialMailer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendMahasiswaCredentials extends Command
{
    protected $signature = 'mahasiswa:send-credentials
                            {--limit=100 : Maximum emails to send in this run (0 = tanpa batas)}
                            {--prodi= : Filter by program studi ID}
                            {--angkatan= : Filter by angkatan year}
                            {--resend : Resend even if already sent before}
                            {--nim= : Send to a specific NIM only}';

    protected $description = 'Send login credentials to students who have not received them yet';

    public function handle(CredentialMailer $mailer): int
    {
        $limit    = (int) $this->option('limit');
        $prodi    = $this->option('prodi');
        $angkatan = $this->option('angkatan');
        $resend   = $this->option('resend');
        $nimOnly  = $this->option('nim');

        $query = User::where('is_admin', false)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        // Filter: specific NIM
        if ($nimOnly) {
            $query->where('nim', $nimOnly);
        } else {
            // Filter: only unsent (unless --resend)
            if (!$resend) {
                $query->whereNull('credentials_sent_at');
            }

            if ($prodi) {
                $query->where('id_program_studi', $prodi);
            }

            if ($angkatan) {
                $query->where('angkatan', $angkatan);
            }
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('Tidak ada mahasiswa yang perlu dikirimi credentials.');
            return self::SUCCESS;
        }

        $lock = Cache::lock(CredentialMailer::BULK_LOCK_KEY, CredentialMailer::BULK_LOCK_SECONDS);
        if (! $lock->get()) {
            $this->warn('Pengiriman email massal lain sedang berlangsung (lock aktif). Silakan tunggu hingga selesai.');
            return self::FAILURE;
        }

        try {
            $throttleDelayMs = $mailer->throttleDelayMs();
            $maxRetries      = $mailer->maxRetries();

            $this->info("Total yang akan diproses: {$total}");
            if ($limit > 0) {
                $this->info("Limit pengiriman saat ini: {$limit} email");
                $query->limit($limit);
            } else {
                $this->info('Mode pengiriman: tanpa limit');
            }

            $mahasiswaList = $query->get();
            $sent   = 0;
            $failed = 0;

            $bar = $this->output->createProgressBar($mahasiswaList->count());
            $bar->start();

            foreach ($mahasiswaList->values() as $index => $mahasiswa) {
                if ($index > 0 && $throttleDelayMs > 0) {
                    usleep($throttleDelayMs * 1000);
                }

                $ok = $mailer->send($mahasiswa, $maxRetries, function (string $message) {
                    $this->newLine();
                    $this->warn($message);
                });

                if ($ok) {
                    $sent++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->error("Gagal kirim ke {$mahasiswa->nim} ({$mahasiswa->email}): {$mailer->lastError}");
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $remaining = max(0, $total - $sent);
            $this->info("✅ Berhasil terkirim : {$sent}");
            if ($failed > 0) {
                $this->warn("❌ Gagal             : {$failed}");
            }
            $this->info("📬 Sisa belum terkirim: {$remaining}");

            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
