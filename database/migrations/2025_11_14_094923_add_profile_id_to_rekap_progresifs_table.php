<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekap_progresif', function (Blueprint $table) {
            // Tambah kolom profile_id (nullable dulu agar tidak error kalau sudah ada data)
            $table->foreignId('profile_id')->nullable()->after('id')->constrained('profiles')->onDelete('set null');

            // Tambah index unik agar satu karyawan hanya punya 1 rekap per bulan-tahun
            $table->unique(['profile_id', 'bulan', 'tahun'], 'unique_rekap_per_karyawan_per_bulan');
        });
    }

    public function down(): void
    {
        Schema::table('rekap_progresifs', function (Blueprint $table) {
            $table->dropUnique('unique_rekap_per_karyawan_per_bulan');
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });
    }
};