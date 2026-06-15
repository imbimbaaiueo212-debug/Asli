<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocers', function (Blueprint $table) {
            $table->id(); // NO
            $table->integer('numerator')->nullable(); // NUMERATOR
            $table->string('kategori_v')->nullable(); // KATEGORI V
            $table->integer('nilai_v')->nullable(); // NILAI V
            $table->date('tgl_peny')->nullable(); // TGL PENY
            $table->string('st_v')->nullable(); // ST V

            // VA MURID HUMAS #
            $table->string('va_murid_humas')->nullable(); // VA MURID HUMAS
            $table->string('va_murid_humas_1')->nullable(); // 1
            $table->string('va_murid_humas_2')->nullable(); // 2
            $table->string('nama_murid_humas')->nullable(); // NAMA MURID HUMAS

            // VA MURID BARU *
            $table->string('va_murid_baru')->nullable(); // VA MURID BARU *
            $table->string('va_murid_baru_1')->nullable(); // 1'
            $table->string('va_murid_baru_2')->nullable(); // 2''
            $table->string('nama_murid_baru')->nullable(); // NAMA MURID BARU

            $table->text('keterangan')->nullable(); // KETERANGAN #

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocers');
    }
};
