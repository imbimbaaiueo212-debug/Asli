<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voucher_lama', function (Blueprint $table) {
            $table->id(); // NO
            $table->string('voucher')->unique(); // VOUCHER
            $table->date('tanggal'); // TANGGAL
            $table->string('status'); // STATUS

            // DETAIL HUMAS
            $table->string('nim')->nullable();
            $table->string('nama_murid')->nullable();
            $table->string('orangtua')->nullable();
            $table->string('telp_hp')->nullable();

            // DETAIL MURID BARU
            $table->string('nim_murid_baru')->nullable();
            $table->string('nama_murid_baru')->nullable();
            $table->string('orangtua_murid_baru')->nullable();
            $table->string('telp_hp_murid_baru')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_lama');
    }
};

