<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekap_progresif', function (Blueprint $table) {
            // 👇 sesuaikan panjang kalau perlu
            $table->string('bimba_unit')->nullable()->after('departemen');
            $table->string('no_cabang', 20)->nullable()->after('bimba_unit');

            // optional: index untuk filter cepat
            $table->index('bimba_unit');
            $table->index('no_cabang');
        });
    }

    public function down(): void
    {
        Schema::table('rekap_progresif', function (Blueprint $table) {
            $table->dropIndex(['bimba_unit']);
            $table->dropIndex(['no_cabang']);
            $table->dropColumn(['bimba_unit', 'no_cabang']);
        });
    }
};
