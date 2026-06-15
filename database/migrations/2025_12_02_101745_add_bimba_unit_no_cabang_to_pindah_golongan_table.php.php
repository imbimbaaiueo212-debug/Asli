<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pindah_golongan', function (Blueprint $table) {
            if (! Schema::hasColumn('pindah_golongan', 'bimba_unit')) {
                $table->string('bimba_unit')->nullable()->after('spp');
            }
            if (! Schema::hasColumn('pindah_golongan', 'no_cabang')) {
                $table->string('no_cabang')->nullable()->after('bimba_unit');
            }
        });
    }

    public function down()
    {
        Schema::table('pindah_golongan', function (Blueprint $table) {
            if (Schema::hasColumn('pindah_golongan', 'bimba_unit')) {
                $table->dropColumn('bimba_unit');
            }
            if (Schema::hasColumn('pindah_golongan', 'no_cabang')) {
                $table->dropColumn('no_cabang');
            }
        });
    }
};
