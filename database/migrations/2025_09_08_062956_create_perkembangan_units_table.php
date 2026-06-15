<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perkembangan_units', function (Blueprint $table) {
            $table->id();
            
            // Bisa diisi angka 1-31 atau tanggal awal bulan
            $table->string('tgl')->nullable();
            
            // Bulan (1-12)
            $table->string('bl')->nullable();
            
            // Kolom per hari 01-31
            for ($i = 1; $i <= 31; $i++) {
                $table->integer(str_pad($i, 2, '0', STR_PAD_LEFT))->default(0);
            }

            // Total unit per bulan
            $table->integer('T')->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perkembangan_units');
    }
};
