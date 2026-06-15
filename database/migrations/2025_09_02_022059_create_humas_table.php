<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('humas', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_reg'); // Tanggal Registrasi
            $table->string('nih')->unique(); // Nomor Induk Humas
            $table->string('nama');
            $table->string('status')->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->string('pekerjaan')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('humas');
    }
};

