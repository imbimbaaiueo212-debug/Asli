<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_perlengkapan_unit', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama_barang');
            // nullable() supaya data lama tidak error
            // after('nama_barang') agar posisi kolom rapi di database
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_perlengkapan_unit', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};