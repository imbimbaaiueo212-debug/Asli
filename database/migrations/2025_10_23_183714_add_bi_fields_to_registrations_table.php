<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom kelas, gol, kd, dan spp ke tabel registrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // kolom baru setelah kolom 'program' (boleh kamu sesuaikan)
            $table->string('kelas')->nullable()->after('program');
            $table->string('gol', 10)->nullable()->after('kelas');
            $table->string('kd', 2)->nullable()->after('gol');
            $table->unsignedInteger('spp')->nullable()->after('kd');
        });
    }

    /**
     * Rollback perubahan.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['kelas', 'gol', 'kd', 'spp']);
        });
    }
};
