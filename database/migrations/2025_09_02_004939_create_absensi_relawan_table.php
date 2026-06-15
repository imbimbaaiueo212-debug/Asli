<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absensi_relawan', function (Blueprint $table) {
            $table->id(); // NO
            $table->string('nama_relawaan'); // Nama relawan
            $table->string('posisi'); // Posisi / jabatan
            $table->string('status_relawaan'); // Status relawan
            $table->string('departemen'); // Departemen
            $table->date('tanggal'); // Tanggal absensi
            $table->string('absensi'); // Diisi manual
            $table->text('keterangan')->nullable(); // Diisi manual
            $table->enum('status', ['Izin','Datang Terlambat','Alpa','Sakit','Lainnya'])->default('Izin'); // Pilihan status
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_relawan');
    }
};

