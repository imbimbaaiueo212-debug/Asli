<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->date('tgl_keluar')->nullable()->after('tgl_resign');
            $table->text('keterangan_keluar')->nullable()->after('tgl_keluar');
            
            // Optional: tambahkan index jika sering dicari
            // $table->index('tgl_keluar');
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['tgl_keluar', 'keterangan_keluar']);
        });
    }
};