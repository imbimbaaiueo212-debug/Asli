<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Tambahkan kolom baru 'tgl_ambil_seragam' (date, nullable) setelah kolom 'ukuran'
            $table->date('tgl_ambil_seragam')->nullable()->after('ukuran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Hapus kembali kolom jika migration di-rollback
            $table->dropColumn('tgl_ambil_seragam');
        });
    }
};
