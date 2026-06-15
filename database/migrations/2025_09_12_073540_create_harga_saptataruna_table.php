<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_saptataruna', function (Blueprint $table) {
            $table->id();
            $table->string('kategori')->nullable(); // Biaya Pendaftaran / Penjualan / SPP
            $table->string('kode')->nullable(); // bA, Eb, KA, S2, dsb
            $table->string('nama')->nullable(); // Nama item
            $table->decimal('duafa', 15, 2)->nullable();
            $table->decimal('promo_2019', 15, 2)->nullable();
            $table->decimal('daftar_ulang', 15, 2)->nullable();
            $table->decimal('spesial', 15, 2)->nullable();
            $table->decimal('umum1', 15, 2)->nullable();
            $table->decimal('umum2', 15, 2)->nullable();
            $table->decimal('harga', 15, 2)->nullable(); // untuk PENJUALAN
            $table->decimal('a', 15, 2)->nullable(); // untuk SPP
            $table->decimal('b', 15, 2)->nullable();
            $table->decimal('c', 15, 2)->nullable();
            $table->decimal('d', 15, 2)->nullable();
            $table->decimal('e', 15, 2)->nullable();
            $table->decimal('f', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_saptataruna');
    }
};
