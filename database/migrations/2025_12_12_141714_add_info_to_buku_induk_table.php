<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->text('info')->nullable()->after('no_cabang'); // atau string(500) jika ingin batas karakter
        });
    }

    public function down()
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->dropColumn('info');
        });
    }
};