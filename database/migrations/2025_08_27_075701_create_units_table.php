<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('no_cabang');
            $table->string('biMBA_unit');
            $table->string('staff_sos')->nullable();
            $table->string('telp')->nullable();
            $table->string('email')->nullable();
            // bank info
            $table->string('bank_nama')->nullable();
            $table->string('bank_nomor')->nullable();
            $table->string('bank_atas_nama')->nullable();
            // alamat info
            $table->string('alamat_jalan')->nullable();
            $table->string('alamat_rt_rw')->nullable();
            $table->string('alamat_kode_pos')->nullable();
            $table->string('alamat_kel_des')->nullable();
            $table->string('alamat_kecamatan')->nullable();
            $table->string('alamat_kota_kab')->nullable();
            $table->string('alamat_provinsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
