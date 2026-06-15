<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBulanKekuranganToPendapatanTunjanganTable extends Migration
{
    public function up()
    {
        Schema::table('pendapatan_tunjangan', function (Blueprint $table) {
            $table->string('bulan_kekurangan')->nullable()->after('kekurangan');
        });
    }

    public function down()
    {
        Schema::table('pendapatan_tunjangan', function (Blueprint $table) {
            $table->dropColumn('bulan_kekurangan');
        });
    }
}
