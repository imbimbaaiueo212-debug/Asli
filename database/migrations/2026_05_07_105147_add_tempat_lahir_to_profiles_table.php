<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('tgl_lahir');
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('tempat_lahir');
        });
    }
};