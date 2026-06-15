<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan_perlengkapan_unit', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->nullable();
            $table->string('nama_barang')->nullable();
            $table->integer('jumlah')->nullable();
            $table->decimal('harga',12,2)->nullable();
            $table->integer('minggu')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_perlengkapan_unit');
    }
};
