<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            // Hapus unique constraint
            $table->dropUnique(['profile_id']);
        });
    }

    public function down()
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            // Kembalikan unique jika di-rollback
            $table->unique('profile_id');
        });
    }
};