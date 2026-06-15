<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            // safe-check agar migration idempotent
            if (! Schema::hasColumn('penerimaan', 'bimba_unit')) {
                $table->string('bimba_unit')->nullable()->after('nama_murid');
            }
            if (! Schema::hasColumn('penerimaan', 'no_cabang')) {
                $table->string('no_cabang')->nullable()->after('bimba_unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaan', 'bimba_unit')) {
                $table->dropColumn('bimba_unit');
            }
            if (Schema::hasColumn('penerimaan', 'no_cabang')) {
                $table->dropColumn('no_cabang');
            }
        });
    }
};
