<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pindah_golongan', function (Blueprint $table) {
            $table->enum('source', ['manual', 'buku_induk'])
                  ->default('manual')
                  ->after('alasan_pindah');   // letakkan setelah kolom alasan_pindah
        });
    }

    public function down()
    {
        Schema::table('pindah_golongan', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};