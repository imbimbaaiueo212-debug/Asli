<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lembur', function (Blueprint $table) {
            if (!Schema::hasColumn('lembur', 'bimba_unit')) {
                $table->string('bimba_unit')->nullable()->after('profile_id');
            }
            
            if (!Schema::hasColumn('lembur', 'no_cabang')) {
                $table->string('no_cabang')->nullable()->after('bimba_unit');
            }
        });
    }

    public function down()
    {
        Schema::table('lembur', function (Blueprint $table) {
            $table->dropColumn(['bimba_unit', 'no_cabang']);
        });
    }
};