<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('potongan_tunjangans', function (Blueprint $table) {

            // Tambah kolom nominal
            if (!Schema::hasColumn('potongan_tunjangans', 'izin_nominal')) {
                $table->decimal('izin_nominal', 12, 2)->nullable()->after('izin');
            }

            if (!Schema::hasColumn('potongan_tunjangans', 'alpa_nominal')) {
                $table->decimal('alpa_nominal', 12, 2)->nullable()->after('alpa');
            }

            if (!Schema::hasColumn('potongan_tunjangans', 'tidak_aktif_nominal')) {
                $table->decimal('tidak_aktif_nominal', 12, 2)->nullable()->after('tidak_aktif');
            }

            // Ubah bulan supaya formatnya konsisten: YYYY-MM
            if (Schema::hasColumn('potongan_tunjangans', 'bulan')) {
                $table->char('bulan', 7)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('potongan_tunjangans', function (Blueprint $table) {
            if (Schema::hasColumn('potongan_tunjangans', 'izin_nominal')) {
                $table->dropColumn('izin_nominal');
            }
            if (Schema::hasColumn('potongan_tunjangans', 'alpa_nominal')) {
                $table->dropColumn('alpa_nominal');
            }
            if (Schema::hasColumn('potongan_tunjangans', 'tidak_aktif_nominal')) {
                $table->dropColumn('tidak_aktif_nominal');
            }

            // Kembalikan 'bulan' menjadi string
            if (Schema::hasColumn('potongan_tunjangans', 'bulan')) {
                $table->string('bulan')->nullable()->change();
            }
        });
    }
};
