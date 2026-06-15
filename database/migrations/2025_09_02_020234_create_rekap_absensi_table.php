<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rekap_absensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_relawan');
            $table->string('jabatan')->nullable();
            $table->string('departemen')->nullable();

            // SRJ
            $table->string('srj_106')->nullable();
            $table->string('srj_107')->nullable();
            $table->string('srj_108')->nullable();
            $table->string('srj_109')->nullable();
            $table->string('srj_110')->nullable();
            $table->string('srj_111')->nullable();
            $table->string('srj_112')->nullable();
            $table->string('srj_113')->nullable();
            $table->string('srj_114')->nullable();
            $table->string('srj_115')->nullable();
            $table->string('srj_116')->nullable();

            // SKS
            $table->string('sks_206')->nullable();
            $table->string('sks_207')->nullable();
            $table->string('sks_208')->nullable();
            $table->string('sks_209')->nullable();
            $table->string('sks_210')->nullable();
            $table->string('sks_211')->nullable();

            // S6
            $table->string('s6_306')->nullable();
            $table->string('s6_307')->nullable();
            $table->string('s6_308')->nullable();
            $table->string('s6_309')->nullable();
            $table->string('s6_310')->nullable();
            $table->string('s6_311')->nullable();

            // Tambahan
            $table->string('s6_306_')->nullable();
            $table->string('s6_307_')->nullable();
            $table->string('s6_306_2')->nullable();
            $table->string('s6_307_3')->nullable();
            $table->string('s6_306_4')->nullable();
            $table->string('s6_307_5')->nullable();

            $table->integer('jumlah_murid')->default(0);
            $table->integer('jumlah_rombim')->default(0);
            $table->string('penyesuaian_rb')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_absensi');
    }
};
