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
        Schema::table('pendapatan_tunjangan', function (Blueprint $table) {
            // Gunakan decimal agar lebih akurat untuk uang (rekomendasi)
            $table->decimal('rp', 15, 2)->nullable()->default(0)->after('total');
            
            // Atau kalau mau pakai unsignedBigInteger (untuk nilai dalam satuan rupiah tanpa desimal)
            // $table->unsignedBigInteger('rp')->nullable()->default(0)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendapatan_tunjangan', function (Blueprint $table) {
            $table->dropColumn('rp'); // ← ini yang tadi salah ketik
        });
    }
};