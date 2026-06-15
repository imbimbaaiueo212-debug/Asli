<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_harga', function (Blueprint $table) {
            $table->id();
            $table->string('kategori')->nullable();   // Biaya Pendaftaran, Penjualan, Biaya SPP Perbulan
            $table->string('sub_kategori')->nullable(); // Duafa, Promo 2019, KA, S2, S3B1, dll
            $table->string('unit')->nullable();       // bA biMBA AIUEO, Eb English biMBA, dll
            $table->string('deskripsi')->nullable();  // Keterangan tambahan, misal "Seminggu 3x"
            $table->decimal('harga_a',15,2)->nullable();
            $table->decimal('harga_b',15,2)->nullable();
            $table->decimal('harga_c',15,2)->nullable();
            $table->decimal('harga_d',15,2)->nullable();
            $table->decimal('harga_e',15,2)->nullable();
            $table->decimal('harga_f',15,2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daftar_harga');
    }
};
