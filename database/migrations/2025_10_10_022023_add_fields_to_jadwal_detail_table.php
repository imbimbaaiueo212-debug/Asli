<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom baru ke tabel jadwal_detail
     */
    public function up()
    {
        Schema::table('jadwal_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_detail', 'guru')) {
                $table->string('guru')->nullable();
            }

            if (!Schema::hasColumn('jadwal_detail', 'kelas')) {
                $table->string('kelas')->nullable();
            }

            if (!Schema::hasColumn('jadwal_detail', 'kode_jadwal')) {
                $table->integer('kode_jadwal')->nullable();
            }

            if (!Schema::hasColumn('jadwal_detail', 'jenis_kbm')) {
                $table->string('jenis_kbm')->nullable();
            }
        });
    }

    /**
     * Hapus kolom yang ditambahkan (rollback)
     */
    public function down(): void
    {
        Schema::table('jadwal_detail', function (Blueprint $table) {
            $table->dropColumn(['guru', 'kelas', 'kode_jadwal', 'jenis_kbm']);
        });
    }
};
