<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('adjustments', function (Blueprint $table) {
            $table->string('nik', 20)->after('no_cabang'); // Sesuaikan panjang dengan NIK di profiles
            $table->string('nama')->after('nik');
            $table->string('jabatan')->nullable()->after('nama');
            $table->date('tanggal_masuk')->nullable()->after('jabatan');
            // masa_kerja bisa dihitung otomatis, jadi tidak perlu kolom di DB
            // atau kalau mau simpan manual:
            // $table->string('masa_kerja')->nullable()->after('tanggal_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('adjustments', function (Blueprint $table) {
            $table->dropColumn(['nik', 'nama', 'jabatan', 'tanggal_masuk']);
        });
    }
};