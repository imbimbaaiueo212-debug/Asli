<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {

            // 📌 Tanggal diberikan surat garansi
            $table->date('tgl_surat_garansi')
                  ->nullable()
                  ->after('note_garansi');

            // 📌 Keterangan level (misal: naik level, evaluasi, dll)
            $table->string('keterangan_level')
                  ->nullable()
                  ->after('level');

            // 📌 Tanggal perubahan level
            $table->date('tgl_level')
                  ->nullable()
                  ->after('keterangan_level');
        });
    }

    public function down(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->dropColumn([
                'tgl_surat_garansi',
                'keterangan_level',
                'tgl_level'
            ]);
        });
    }
};
