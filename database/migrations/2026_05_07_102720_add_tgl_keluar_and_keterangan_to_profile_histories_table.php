<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profile_histories', function (Blueprint $table) {
            $table->date('tgl_keluar')->nullable()->after('tgl_resign');
            $table->text('keterangan_keluar')->nullable()->after('tgl_keluar');
        });
    }

    public function down()
    {
        Schema::table('profile_histories', function (Blueprint $table) {
            $table->dropColumn(['tgl_keluar', 'keterangan_keluar']);
        });
    }
};