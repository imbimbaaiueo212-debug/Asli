<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan_raport', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('nama_murid');
            $table->string('gol')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->string('lama_bljr')->nullable();
            $table->string('guru')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_raport');
    }
};
