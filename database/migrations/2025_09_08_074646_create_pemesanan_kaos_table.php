<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan_kaos', function (Blueprint $table) {
            $table->id();
            $table->string('no_bukti')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('nim')->nullable();
            $table->string('nama_murid')->nullable();
            $table->integer('kaos')->nullable();
            $table->string('size')->nullable();
            $table->integer('kpk')->nullable();
            $table->integer('tas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_kaos');
    }
};
