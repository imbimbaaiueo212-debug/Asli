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
    Schema::table('voucher_lama', function (Blueprint $table) {
        $table->integer('jumlah_voucher')->default(1); // default 1
    });
}

public function down()
{
    Schema::table('voucher_lama', function (Blueprint $table) {
        $table->dropColumn('jumlah_voucher');
    });
}
};
