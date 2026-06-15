<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_histori', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_lama_id'); // relasi ke voucher lama
            $table->string('voucher');
            $table->date('tanggal'); // tanggal penyerahan awal
            $table->date('tanggal_pemakaian')->nullable();
            $table->string('nim')->nullable();
            $table->string('nama_murid')->nullable();
            $table->string('orangtua')->nullable();
            $table->string('telp_hp')->nullable();
            $table->string('nim_murid_baru')->nullable();
            $table->string('nama_murid_baru')->nullable();
            $table->string('orangtua_murid_baru')->nullable();
            $table->string('telp_hp_murid_baru')->nullable();
            $table->integer('jumlah_voucher')->default(1); // default 1 voucher per histori
            $table->timestamps();

            $table->foreign('voucher_lama_id')->references('id')->on('voucher_lama')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_histori');
    }
};

