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
        $table->date('tanggal_pemakaian')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('voucher_lama', function (Blueprint $table) {
        $table->dropColumn('tanggal_pemakaian');
    });
}

};
