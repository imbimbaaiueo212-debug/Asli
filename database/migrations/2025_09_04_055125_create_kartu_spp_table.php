<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_spp', function (Blueprint $table) {
            $table->id();
            $table->string('no_pembayaran')->unique();
            $table->string('nama_murid');
            $table->string('golongan');
            $table->decimal('pembayaran_spp', 15, 2);
            $table->string('bimba_unit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_spp');
    }
};
