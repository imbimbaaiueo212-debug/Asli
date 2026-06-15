<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan_stpb', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->nullable();
            $table->string('nama_murid')->nullable();
            $table->string('tmpt_lahir')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->string('nama_orang_tua')->nullable();
            $table->string('level')->nullable();
            $table->date('tgl_level')->nullable();
            $table->integer('minggu')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_stpb');
    }
};
