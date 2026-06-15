<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('durasi_kegiatan', function (Blueprint $table) {
        $table->id();
        $table->string('waktu_mgg'); // kolom untuk waktu per minggu
        $table->string('waktu_bln'); // kolom untuk waktu per bulan
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('durasi_kegiatan');
    }
};
