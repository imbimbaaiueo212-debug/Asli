<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id(); // NO, auto increment
            $table->string('kode')->nullable();
            $table->string('kategori')->nullable();
            $table->string('jenis')->nullable();
            $table->string('label')->nullable();
            $table->string('nama_produk')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('berat', 10, 2)->nullable(); // misal kg atau gram
            $table->decimal('harga', 15, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('isi')->nullable();
            $table->string('pendataan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
