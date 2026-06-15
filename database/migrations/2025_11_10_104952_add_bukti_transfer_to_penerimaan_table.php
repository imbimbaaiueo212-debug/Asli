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
    Schema::table('penerimaan', function (Blueprint $table) {
        $table->string('bukti_transfer_path')->nullable()->after('nilai_spp');
    });
}

public function down()
{
    Schema::table('penerimaan', function (Blueprint $table) {
        $table->dropColumn('bukti_transfer_path');
    });
}

};
