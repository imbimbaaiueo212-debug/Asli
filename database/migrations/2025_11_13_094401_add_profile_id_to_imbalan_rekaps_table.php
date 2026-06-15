<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('id')->index();
            $table->unique('profile_id');
        });
    }

    public function down()
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            $table->dropUnique(['profile_id']);
            $table->dropColumn('profile_id');
        });
    }
};