<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pindah_golongan', function (Blueprint $table) {
            $table->id();
            $table->string('gol', 10)->nullable()->comment('Golongan lama');
            $table->string('gol_baru', 10)->nullable()->comment('Golongan baru');
            $table->string('kd', 20)->nullable()->comment('Kode lama');
            $table->string('kd_baru', 20)->nullable()->comment('Kode baru');
            $table->string('spp', 15, 2)->nullable()->comment('SPP lama');
            $table->string('spp_baru', 15, 2)->nullable()->comment('SPP baru');
            $table->string('guru', 50)->nullable()->comment('Nama guru');
            $table->date('tanggal_pindah_golongan')->nullable()->comment('Tanggal pindah golongan');
            $table->string('keterangan')->nullable()->comment('Keterangan tambahan');
            $table->text('alasan_pindah')->nullable()->comment('Alasan pindah golongan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pindah_golongan');
    }
};
