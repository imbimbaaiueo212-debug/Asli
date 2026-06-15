<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('laporan_bagi_hasil', function (Blueprint $table) {
            $table->renameColumn('unit', 'bimba_unit');
        });
    }

    public function down()
    {
        Schema::table('laporan_bagi_hasil', function (Blueprint $table) {
            $table->renameColumn('bimba_unit', 'unit');
        });
    }
};

