<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikat_beasiswa', function (Blueprint $table) {
            $table->id();

            $table->string('virtual_account')->nullable();
            $table->string('nim')->nullable();
            $table->string('nama')->nullable();
            $table->string('nama_unit')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_orang_tua')->nullable();
            $table->string('golongan')->nullable();
            $table->decimal('jumlah_beasiswa', 15, 2)->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('periode_bea_ke')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikat_beasiswa');
    }
};
