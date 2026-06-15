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
    Schema::table('pembayaran_tunjangans', function (Blueprint $table) {
        $table->string('bulan', 7)->after('dibayarkan'); // format: YYYY-MM
    });
}

public function down()
{
    Schema::table('pembayaran_tunjangans', function (Blueprint $table) {
        $table->dropColumn('bulan');
    });
}
};
