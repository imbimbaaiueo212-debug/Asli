<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBulanStringToImbalanRekapsTable extends Migration
{
    public function up()
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            // Kolom bulan format bebas: "September 2025"
            $table->string('bulan')
                  ->nullable()
                  ->after('catatan')
                  ->comment('Contoh: \"September 2025\"');
        });
    }

    public function down()
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            $table->dropColumn('bulan');
        });
    }
}
