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
        $table->integer('nominal')->default(0)->after('jumlah_voucher');
        // atau: $table->decimal('nominal', 12, 2)->default(0);
    });
}

public function down()
{
    Schema::table('voucher_lama', function (Blueprint $table) {
        $table->dropColumn('nominal');
    });
}
};
