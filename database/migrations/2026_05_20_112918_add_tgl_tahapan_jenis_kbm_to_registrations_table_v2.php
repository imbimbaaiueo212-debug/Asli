<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Tambahkan kolom hanya jika belum ada
            if (!Schema::hasColumn('registrations', 'tgl_tahapan')) {
                $table->date('tgl_tahapan')->nullable()->after('tahap');
            }

            if (!Schema::hasColumn('registrations', 'jenis_kbm')) {
                $table->string('jenis_kbm', 50)->nullable()->after('tgl_tahapan');
            }
        });
    }

    public function down()
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumnIfExists('tgl_tahapan');
            $table->dropColumnIfExists('jenis_kbm');
        });
    }
};