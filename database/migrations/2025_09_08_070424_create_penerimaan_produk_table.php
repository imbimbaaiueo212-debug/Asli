<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_produk', function (Blueprint $table) {
            $table->id(); // NO
            $table->string('faktur')->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('minggu')->nullable();
            $table->string('label')->nullable();
            $table->integer('jumlah')->nullable();
            $table->string('kategori')->nullable();
            $table->string('jenis')->nullable();
            $table->string('nama_produk')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('isi')->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_produk');
    }
};
