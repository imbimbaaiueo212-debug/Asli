<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->string('ukuran_kaos_pendek')->nullable()->after('kaos_lengan_panjang');
            $table->string('ukuran_kaos_panjang')->nullable()->after('ukuran_kaos_pendek');
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->dropColumn(['ukuran_kaos_pendek', 'ukuran_kaos_panjang']);
        });
    }
};