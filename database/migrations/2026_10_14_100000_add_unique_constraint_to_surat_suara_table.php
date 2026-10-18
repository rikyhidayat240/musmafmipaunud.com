<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Check for existing duplicates before adding unique constraint
        $duplicates = DB::table('surat_suara')
            ->select('nim', 'id_kegiatan', DB::raw('COUNT(*) as total'))
            ->groupBy('nim', 'id_kegiatan')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $details = $duplicates->map(fn ($d) => "NIM: {$d->nim}, id_kegiatan: {$d->id_kegiatan} ({$d->total} baris)")->implode('; ');
            throw new \RuntimeException(
                "Migration gagal: Ditemukan duplikasi data pada tabel surat_suara ({$details}). " .
                "Harap bersihkan data duplikat secara manual dengan query database sebelum menjalankan migration ini."
            );
        }

        // 2. Add unique constraint
        Schema::table('surat_suara', function (Blueprint $table) {
            $table->unique(['nim', 'id_kegiatan'], 'surat_suara_nim_kegiatan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_suara', function (Blueprint $table) {
            $table->dropUnique('surat_suara_nim_kegiatan_unique');
        });
    }
};
