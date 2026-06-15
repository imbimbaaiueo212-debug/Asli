<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemakaian_produk', function (Blueprint $table) {
            $table->id(); // NO
            $table->date('tanggal')->nullable();
            $table->integer('minggu')->nullable();
            $table->string('label')->nullable();
            $table->integer('jumlah')->nullable();
            $table->string('nim')->nullable();
            $table->string('kategori')->nullable();
            $table->string('jenis')->nullable();
            $table->string('nama_produk')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->string('nama_murid')->nullable();
            $table->string('gol')->nullable();
            $table->string('guru')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemakaian_produk');
    }
};
