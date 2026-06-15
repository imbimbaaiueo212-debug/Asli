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
    Schema::table('wheel_winners', function (Blueprint $table) {
        $table->dropUnique(['no_cabang']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wheel_winners', function (Blueprint $table) {
            //
        });
    }
};
