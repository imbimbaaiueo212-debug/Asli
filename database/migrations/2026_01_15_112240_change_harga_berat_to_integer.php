<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->unsignedBigInteger('harga')->default(0)->change();
            $table->unsignedBigInteger('berat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->decimal('harga', 15, 2)->default(0)->change();
            $table->decimal('berat', 10, 2)->nullable()->change();
        });
    }
};