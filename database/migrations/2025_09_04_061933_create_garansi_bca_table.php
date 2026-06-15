<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garansi_bca', function (Blueprint $table) {
            $table->id();

            $table->string('virtual_account')->nullable();
            $table->string('nama_murid')->nullable();
            $table->string('tempat_tanggal_lahir')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('nama_orang_tua_wali')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garansi_bca');
    }
};
