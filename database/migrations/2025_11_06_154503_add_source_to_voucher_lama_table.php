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
        if (! Schema::hasColumn('voucher_lama', 'source')) {
            $table->string('source', 20)->default('manual')->after('status');
        }
    });
}

public function down()
{
    Schema::table('voucher_lama', function (Blueprint $table) {
        if (Schema::hasColumn('voucher_lama', 'source')) {
            $table->dropColumn('source');
        }
    });
}

};
