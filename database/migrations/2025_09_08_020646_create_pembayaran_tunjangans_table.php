<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_tunjangans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');                   // Nama karyawan
            $table->string('jabatan')->nullable();    // Jabatan
            $table->string('status')->nullable();     // Status (Tetap/Kontrak dll)
            $table->string('departemen')->nullable(); // Departemen
            $table->integer('masa_kerja')->nullable();// Dalam bulan/tahun
            $table->string('no_rekening')->nullable();// Nomor rekening bank
            $table->string('bank')->nullable();       // Nama bank
            $table->string('atas_nama')->nullable();  // Atas nama rekening
            $table->decimal('pendapatan', 15, 2)->default(0);
            $table->decimal('potongan', 15, 2)->default(0);
            $table->decimal('dibayarkan', 15, 2)->default(0); // pendapatan - potongan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tunjangans');
    }
};
