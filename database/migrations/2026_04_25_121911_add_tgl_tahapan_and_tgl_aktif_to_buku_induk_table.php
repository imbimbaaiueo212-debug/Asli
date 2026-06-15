<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->date('tgl_tahapan')->nullable()->after('tahap');
            $table->date('tgl_aktif')->nullable()->after('tgl_tahapan');
        });
    }

    public function down(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->dropColumn(['tgl_tahapan', 'tgl_aktif']);
        });
    }
};
