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
    Schema::table('pemesanan_sertifikat', function (Blueprint $table) {
        $table->string('bimba_unit')->nullable()->after('level');
        $table->string('no_cabang')->nullable()->after('bimba_unit');
    });
}

public function down()
{
    Schema::table('pemesanan_sertifikat', function (Blueprint $table) {
        $table->dropColumn(['bimba_unit', 'no_cabang']);
    });
}
};
