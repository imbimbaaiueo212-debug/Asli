<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_murid', function (Blueprint $table) {

            $table->id();

            // Relasi ke buku induk
            $table->foreignId('buku_induk_id')
                  ->constrained('buku_induk')
                  ->onDelete('cascade');

            // Data cuti
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            $table->string('jenis_cuti')->nullable();
            $table->text('alasan')->nullable();

            // Upload surat
            $table->string('surat_dokter')->nullable();

            // Status cuti
            $table->enum('status', [
                'aktif',
                'selesai'
            ])->default('aktif');

            // User input
            $table->string('dibuat_oleh')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_murid');
    }
};