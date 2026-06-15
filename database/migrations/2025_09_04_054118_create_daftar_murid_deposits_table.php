<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_murid_deposits', function (Blueprint $table) {
            $table->id(); // NO
            $table->date('tanggal_transaksi'); // TANGGAL TRANSAKSI
            $table->string('alert')->nullable(); // ALERT
            $table->string('nim'); // NIM
            $table->string('nama_murid'); // NAMA MURID
            $table->string('kelas'); // KELAS
            $table->string('status'); // STATUS
            $table->string('nama_guru'); // NAMA GURU
            $table->decimal('jumlah_deposit', 15, 2); // JUMLAH DEPOSIT
            $table->string('kategori_deposit'); // KATEGORI DEPOSIT
            $table->string('status_deposit'); // STATUS DEPOSIT
            $table->text('keterangan_deposit')->nullable(); // KETERANGAN DEPOSIT
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daftar_murid_deposits');
    }
};
