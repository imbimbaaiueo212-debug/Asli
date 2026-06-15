<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->date('tgl_magang')->nullable()->after('periode');          // atau after kolom yang kamu anggap paling dekat
            $table->date('tgl_non_aktif')->nullable()->after('tgl_magang');
            $table->date('tgl_resign')->nullable()->after('tgl_non_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['tgl_magang', 'tgl_non_aktif', 'tgl_resign']);
        });
    }
};