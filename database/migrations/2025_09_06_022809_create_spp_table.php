<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp', function (Blueprint $table) {
            $table->id();
            $table->string('nim');
            $table->string('nama_murid');
            $table->string('kelas');
            $table->string('tahap')->nullable();
            $table->string('gol')->nullable();
            $table->string('kd')->nullable();
            $table->integer('spp')->default(0);
            $table->string('stts')->nullable();          // status spp (lunas, tunggakan, dsb.)
            $table->string('s')->nullable();             // saya buat opsional, bisa diubah sesuai kebutuhanmu
            $table->string('petugas_trial')->nullable();
            $table->string('guru')->nullable();
            $table->string('note')->nullable();
            $table->text('keterangan_spp')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp');
    }
};
