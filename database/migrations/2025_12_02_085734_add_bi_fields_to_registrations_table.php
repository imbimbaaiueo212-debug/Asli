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
    Schema::table('registrations', function (Blueprint $table) {
        $table->string('guru')->nullable()->after('spp');
        $table->string('kode_jadwal')->nullable()->after('guru');
        $table->string('hari_jam')->nullable()->after('kode_jadwal');

        // pastikan juga ada kolom ini:
    });
}

public function down()
{
    Schema::table('registrations', function (Blueprint $table) {
        $table->dropColumn(['guru','kode_jadwal','hari_jam']);
    });
}

};
