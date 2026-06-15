<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_relawan', function (Blueprint $table) {
            $table->string('bimba_unit', 100)->nullable()->after('departemen');
            $table->string('no_cabang', 20)->nullable()->after('bimba_unit');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_relawan', function (Blueprint $table) {
            $table->dropColumn(['bimba_unit', 'no_cabang']);
        });
    }
};