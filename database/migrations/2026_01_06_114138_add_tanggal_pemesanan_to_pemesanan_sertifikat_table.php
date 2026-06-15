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
        Schema::table('pemesanan_sertifikat', function (Blueprint $table) {
            // Tambahkan kolom tanggal_pemesanan setelah kolom level
            $table->date('tanggal_pemesanan')->nullable()->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemesanan_sertifikat', function (Blueprint $table) {
            $table->dropColumn('tanggal_pemesanan');
        });
    }
};