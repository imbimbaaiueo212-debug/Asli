<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensi_volunteer', function (Blueprint $table) {
            // Hapus unique index
            $table->dropUnique(['id_fingerprint']); // atau nama indexnya kalau beda
            
            // Jadikan biasa saja (boleh duplikat)
            $table->integer('id_fingerprint')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('absensi_volunteer', function (Blueprint $table) {
            $table->integer('id_fingerprint')->unique()->change();
        });
    }
};