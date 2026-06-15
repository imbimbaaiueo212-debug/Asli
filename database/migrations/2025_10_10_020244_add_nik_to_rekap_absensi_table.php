<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rekap_absensi', function (Blueprint $table) {
            // ✅ Tambah kolom NIK
            $table->string('nik')->nullable()->after('nama_relawan');
        });
    }

    public function down(): void
    {
        Schema::table('rekap_absensi', function (Blueprint $table) {
            $table->dropColumn('nik');
        });
    }
};
