<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daftar_murid_deposits', function (Blueprint $table) {
            if (! Schema::hasColumn('daftar_murid_deposits', 'bimba_unit')) {
                $table->string('bimba_unit')->nullable()->after('keterangan_deposit');
            }
            if (! Schema::hasColumn('daftar_murid_deposits', 'no_cabang')) {
                $table->string('no_cabang')->nullable()->after('bimba_unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daftar_murid_deposits', function (Blueprint $table) {
            if (Schema::hasColumn('daftar_murid_deposits', 'no_cabang')) {
                $table->dropColumn('no_cabang');
            }
            if (Schema::hasColumn('daftar_murid_deposits', 'bimba_unit')) {
                $table->dropColumn('bimba_unit');
            }
        });
    }
};
