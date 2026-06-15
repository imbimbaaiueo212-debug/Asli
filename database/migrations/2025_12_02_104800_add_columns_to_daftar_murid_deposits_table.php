<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('daftar_murid_deposits', function (Blueprint $table) {
            // hubungan ke tabel penerimaan
            $table->unsignedBigInteger('penerimaan_id')->nullable()->after('id');

            // kwitansi dari tabel penerimaan
            $table->string('kwitansi')->nullable()->after('penerimaan_id');

            // tambahan unit & cabang

            // index biar cepat
            $table->index('penerimaan_id');
            $table->index('kwitansi');
            $table->index('nim');
        });
    }

    public function down()
    {
        Schema::table('daftar_murid_deposits', function (Blueprint $table) {
            $table->dropColumn(['penerimaan_id', 'kwitansi']);
        });
    }
};

