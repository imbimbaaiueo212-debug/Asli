<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_volunteer', function (Blueprint $table) {
            $table->text('alasan')
                  ->nullable()
                  ->after('keterangan');   // taruh setelah keterangan biar rapi
        });
    }

    public function down(): void
    {
        Schema::table('absensi_volunteer', function (Blueprint $table) {
            $table->dropColumn('alasan');
        });
    }
};