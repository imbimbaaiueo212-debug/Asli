<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profile_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            
            $table->string('periode')->comment('Format: YYYY-MM'); // contoh: 2026-03
            
            $table->string('status_karyawan')->nullable();
            $table->date('tgl_magang')->nullable();
            $table->date('tgl_non_aktif')->nullable();
            $table->date('tgl_resign')->nullable();
            $table->date('tgl_selesai_magang')->nullable();
            $table->date('tgl_masuk')->nullable();

            // Data penting untuk perhitungan gaji/imbalan
            $table->integer('jumlah_murid_mba')->nullable();
            $table->integer('jumlah_murid_jadwal')->nullable();
            $table->integer('jumlah_rombim')->nullable();
            $table->string('rb')->nullable();
            $table->string('ktr')->nullable();
            $table->string('ktr_tambahan')->nullable();
            $table->decimal('rp', 15, 2)->nullable();
            $table->integer('masa_kerja')->nullable();
            $table->integer('masa_kerja_jabatan')->nullable();

            // Cadangan semua data (sangat berguna untuk audit)
            $table->json('data_lengkap')->nullable();

            $table->timestamps();

            // Satu profile hanya boleh 1 history per bulan
            $table->unique(['profile_id', 'periode']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('profile_histories');
    }
};