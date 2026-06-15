<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedInteger('total_murid_bawahan')->nullable()->default(0)->after('total_murid');
            $table->unsignedInteger('total_rombim_bawahan')->nullable()->default(0)->after('total_murid_bawahan');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['total_murid_bawahan', 'total_rombim_bawahan']);
        });
    }
};