<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah ke tabel murid_trials
        Schema::table('murid_trials', function (Blueprint $table) {
            $table->string('bimba_unit', 100)->nullable()->after('no_telp');
            $table->string('no_cabang', 10)->nullable()->after('bimba_unit');
        });

        // Tambah juga ke tabel students (biar sinkron)
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'bimba_unit')) {
                $table->string('bimba_unit', 100)->nullable()->after('no_telp');
            }
            if (!Schema::hasColumn('students', 'no_cabang')) {
                $table->string('no_cabang', 10)->nullable()->after('bimba_unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('murid_trials', function (Blueprint $table) {
            $table->dropColumn(['bimba_unit', 'no_cabang']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['bimba_unit', 'no_cabang']);
        });
    }
};