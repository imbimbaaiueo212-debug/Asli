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
    Schema::table('harga_saptataruna', function (Blueprint $table) {
        $table->decimal('promo_umum', 15, 2)->nullable()->after('harga'); // sesuaikan tipe & posisi
    });
}

public function down()
{
    Schema::table('harga_saptataruna', function (Blueprint $table) {
        $table->dropColumn('promo_umum');
    });
}
};
