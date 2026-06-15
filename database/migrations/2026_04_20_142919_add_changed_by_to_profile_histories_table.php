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
    Schema::table('profile_histories', function (Blueprint $table) {
        $table->string('changed_by')->nullable()->after('data_lengkap');
    });
}

public function down()
{
    Schema::table('profile_histories', function (Blueprint $table) {
        $table->dropColumn('changed_by');
    });
}
};
