<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('level_histories', function (Blueprint $table) {
    $table->id();

    $table->foreignId('buku_induk_id')
          ->constrained('buku_induk') // 🔥 FIX DI SINI
          ->cascadeOnDelete();

    $table->string('level');
    $table->date('tgl_level');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_histories');
    }
};
