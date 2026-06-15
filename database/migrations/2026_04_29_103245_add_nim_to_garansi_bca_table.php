<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('garansi_bca', function (Blueprint $table) {
            $table->string('nim', 20)->after('id')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('garansi_bca', function (Blueprint $table) {
            $table->dropColumn('nim');
        });
    }
};
