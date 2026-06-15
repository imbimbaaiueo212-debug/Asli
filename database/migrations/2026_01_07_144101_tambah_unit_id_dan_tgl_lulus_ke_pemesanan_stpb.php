<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pemesanan_stpb', function (Blueprint $table) {
            // Tambah kolom unit_id (foreign key ke tabel units)
            $table->foreignId('unit_id')
                  ->after('keterangan') // letakkan setelah kolom keterangan (opsional)
                  ->nullable()          // boleh null jika belum tahu unitnya
                  ->constrained('units') // otomatis buat foreign key ke tabel units.id
                  ->onDelete('set null'); // jika unit dihapus, set null

            // Tambah kolom tgl_lulus (tanggal lulus)
            $table->date('tgl_lulus')
                  ->after('unit_id')
                  ->nullable()
                  ->comment('Tanggal kelulusan siswa dari program STPB');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemesanan_stpb', function (Blueprint $table) {
            // Hapus foreign key dulu sebelum drop kolom
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
            $table->dropColumn('tgl_lulus');
        });
    }
};