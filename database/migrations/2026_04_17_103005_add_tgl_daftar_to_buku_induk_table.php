<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->date('tgl_daftar')->nullable()->after('tgl_masuk');
        });
    }

    public function down()
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->dropColumn('tgl_daftar');
        });
    }
};