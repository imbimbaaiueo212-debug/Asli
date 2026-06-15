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
        Schema::table('voucher_histori', function (Blueprint $table) {
            $table->string('bukti_penggunaan_path')->nullable()->after('tanggal_pemakaian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voucher_histori', function (Blueprint $table) {
            $table->dropColumn('bukti_penggunaan_path');
        });
    }
};