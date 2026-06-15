<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('potongan_tunjangans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');                  // Nama karyawan
            $table->string('jabatan')->nullable();   // Jabatan
            $table->string('status')->nullable();    // Status (Tetap/Kontrak dll)
            $table->string('departemen')->nullable();// Departemen
            $table->integer('masa_kerja')->nullable();// Dalam bulan/tahun
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpa')->default(0);
            $table->integer('tidak_aktif')->default(0);
            $table->decimal('kelebihan', 12, 2)->default(0);
            $table->string('bulan');                 // Contoh: "September 2025"
            $table->decimal('lain_lain', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('potongan_tunjangans');
    }
};
