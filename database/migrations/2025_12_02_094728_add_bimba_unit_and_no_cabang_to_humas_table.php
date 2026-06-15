<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('humas', function (Blueprint $table) {
            if (! Schema::hasColumn('humas', 'bimba_unit')) {
                $table->string('bimba_unit')->nullable()->after('nama');
            }
            if (! Schema::hasColumn('humas', 'no_cabang')) {
                $table->string('no_cabang')->nullable()->after('bimba_unit');
            }
        });
    }

    public function down()
    {
        Schema::table('humas', function (Blueprint $table) {
            if (Schema::hasColumn('humas', 'bimba_unit')) {
                $table->dropColumn('bimba_unit');
            }
            if (Schema::hasColumn('humas', 'no_cabang')) {
                $table->dropColumn('no_cabang');
            }
        });
    }
};
