<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_kaos', function (Blueprint $table) {
            // Hapus kolom ukuran terpisah
            $table->dropColumn(['size_pendek', 'size_panjang']);
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_kaos', function (Blueprint $table) {
            $table->string('size_pendek')->nullable()->after('kaos');
            $table->string('size_panjang')->nullable()->after('kaos_panjang');
        });
    }
};