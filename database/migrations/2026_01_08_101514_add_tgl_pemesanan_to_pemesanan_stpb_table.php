<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('pemesanan_stpb', function (Blueprint $table) {
        $table->date('tgl_pemesanan')->after('tgl_lulus')->nullable();
    });
}

public function down()
{
    Schema::table('pemesanan_stpb', function (Blueprint $table) {
        $table->dropColumn('tgl_pemesanan');
    });
}

};
