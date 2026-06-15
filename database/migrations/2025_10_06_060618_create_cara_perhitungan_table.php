<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cara_perhitungan', function (Blueprint $table) {
            $table->id();
            $table->string('kategori'); // Guru / Kepala Unit
            $table->string('range_fm'); // Contoh: "6 - 10"
            $table->integer('tarif');   // Contoh: 100000
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cara_perhitungan');
    }
};
