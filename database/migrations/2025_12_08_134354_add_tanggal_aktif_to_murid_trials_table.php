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
    Schema::table('murid_trials', function ($table) {
        $table->date('tanggal_aktif')->nullable()->after('status_trial');
    });
}

public function down()
{
    Schema::table('murid_trials', function ($table) {
        $table->dropColumn('tanggal_aktif');
    });
}
};
