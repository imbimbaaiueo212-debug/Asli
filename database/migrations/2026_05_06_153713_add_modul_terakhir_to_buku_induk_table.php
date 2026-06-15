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
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->string('modul_terakhir', 100)
                  ->nullable()
                  ->after('asal_modul');   // letakkan setelah kolom asal_modul (opsional)
            
            // Index jika sering dicari
            // $table->index('modul_terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->dropColumn('modul_terakhir');
        });
    }
};