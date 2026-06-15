<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('registrations', function (Blueprint $table) {
            
            // Hanya tambah 1 kolom yang diminta
            if (!Schema::hasColumn('registrations', 'tgl_surat_garansi')) {
                $table->date('tgl_surat_garansi')->nullable()
                      ->comment('Tanggal Diberikan Surat Garansi BCA');
            }

        });
    }

    public function down()
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'tgl_surat_garansi')) {
                $table->dropColumn('tgl_surat_garansi');
            }
        });
    }
};