<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pemesanan_perlengkapan_unit', function (Blueprint $table) {
            // Tanggal pemesanan
            $table->date('tanggal_pemesanan')->after('id')->nullable(false);

            // Unit biMBA
            $table->foreignId('unit_id')
                  ->after('tanggal_pemesanan')
                  ->constrained('units')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('pemesanan_perlengkapan_unit', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['tanggal_pemesanan', 'unit_id']);
        });
    }
};