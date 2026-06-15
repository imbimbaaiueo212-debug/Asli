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
    Schema::table('profiles', function (Blueprint $table) {
        $table->string('kaos_kuning_hitam')->nullable()->after('tgl_ambil_seragam');
        $table->string('kaos_merah_kuning_biru')->nullable()->after('kaos_kuning_hitam');
        $table->string('kemeja_kuning_hitam')->nullable()->after('kaos_merah_kuning_biru');
        $table->string('blazer_merah')->nullable()->after('kemeja_kuning_hitam');
        $table->string('blazer_biru')->nullable()->after('blazer_merah');
    });
}

public function down()
{
    Schema::table('profiles', function (Blueprint $table) {
        $table->dropColumn([
            'kaos_kuning_hitam',
            'kaos_merah_kuning_biru',
            'kemeja_kuning_hitam',
            'blazer_merah',
            'blazer_biru'
        ]);
    });
}
};
