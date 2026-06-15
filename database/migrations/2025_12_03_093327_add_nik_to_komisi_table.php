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
    Schema::table('komisi', function (Blueprint $table) {
        $table->string('nik')->nullable()->after('nomor_urut');
    });
}

public function down()
{
    Schema::table('komisi', function (Blueprint $table) {
        $table->dropColumn('nik');
    });
}

};
