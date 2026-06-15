<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skim', function (Blueprint $table) {
            $table->id(); // NO
            $table->string('jabatan', 100);
            $table->string('masa_kerja', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->decimal('tunj_pokok', 15, 2)->default(0);
            $table->decimal('harian', 15, 2)->default(0);
            $table->decimal('fungsional', 15, 2)->default(0);
            $table->decimal('kesehatan', 15, 2)->default(0);
            $table->decimal('thp', 15, 2)->default(0);
            $table->decimal('tunj_khusus', 15, 2)->default(0);
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skim');
    }
};
