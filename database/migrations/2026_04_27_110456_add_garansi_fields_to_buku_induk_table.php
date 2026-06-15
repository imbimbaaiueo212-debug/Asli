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
        Schema::table('buku_induk', function (Blueprint $table) {
    $table->date('tgl_pengajuan_garansi')->nullable();
    $table->date('tgl_selesai_garansi')->nullable();
    $table->integer('masa_aktif_garansi')->nullable(); // dalam hari/bulan
    $table->string('perpanjang_garansi')->nullable(); // ya/tidak / keterangan
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            //
        });
    }
};
