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
        Schema::table('penerimaan_produk', function (Blueprint $table) {
            $table->unsignedBigInteger('pemesanan_sertifikat_id')->nullable()->after('isi');
            $table->foreign('pemesanan_sertifikat_id')
                  ->references('id')
                  ->on('pemesanan_sertifikat')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penerimaan_produk', function (Blueprint $table) {
            $table->dropForeign(['pemesanan_sertifikat_id']);
            $table->dropColumn('pemesanan_sertifikat_id');
        });
    }
};