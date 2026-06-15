<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendapatan_tunjangan', function (Blueprint $table) {
            // 1. Jika kolom 'thp' belum ada → buat baru
            if (!Schema::hasColumn('pendapatan_tunjangan', 'thp')) {
                $table->unsignedBigInteger('thp')->default(0)->after('masa_kerja');
            }

            // 2. Jika kolom 'rp' masih ada → pindahkan isinya ke 'thp', lalu hapus 'rp'
            if (Schema::hasColumn('pendapatan_tunjangan', 'rp')) {
                // Pindahkan semua data dari rp → thp (kalau thp masih 0)
                \DB::statement('UPDATE pendapatan_tunjangan SET thp = rp WHERE thp = 0 OR thp IS NULL');

                // Hapus kolom rp
                $table->dropColumn('rp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pendapatan_tunjangan', function (Blueprint $table) {
            // Balikin kalau rollback (opsional)
            if (!Schema::hasColumn('pendapatan_tunjangan', 'rp')) {
                $table->unsignedBigInteger('rp')->default(0)->after('masa_kerja');
                \DB::statement('UPDATE pendapatan_tunjangan SET rp = thp');
            }

            if (Schema::hasColumn('pendapatan_tunjangan', 'thp')) {
                $table->dropColumn('thp');
            }
        });
    }
};