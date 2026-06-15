<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendapatan_tunjangan', function (Blueprint $table) {
            // sesuaikan posisi "after" kalau mau taruh di tempat lain
            $table->string('bimba_unit')->nullable()->after('departemen');
            $table->string('no_cabang', 50)->nullable()->after('bimba_unit');
        });
    }

    public function down(): void
    {
        Schema::table('pendapatan_tunjangan', function (Blueprint $table) {
            $table->dropColumn(['bimba_unit', 'no_cabang']);
        });
    }
};

