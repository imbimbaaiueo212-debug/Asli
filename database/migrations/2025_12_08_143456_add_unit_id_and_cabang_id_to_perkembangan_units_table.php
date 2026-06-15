<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perkembangan_units', function (Blueprint $table) {
            // Kode unit BIMBA, contoh: A001, B028, C115, dll
            $table->string('bimba_unit', 10)
                  ->after('id')
                  ->index(); // biar cepat query per unit

            // Nomor cabang, biasanya 01, 02, 03, dst (bisa string atau integer)
            $table->string('no_cabang', 5)
                  ->after('bimba_unit')
                  ->index(); // biar cepat query per cabang

            // Kombinasi unik: satu unit di satu cabang hanya boleh punya 1 data per tanggal + bulan
            $table->unique(['bimba_unit', 'no_cabang', 'tgl', 'bl']);
        });
    }

    public function down(): void
    {
        Schema::table('perkembangan_units', function (Blueprint $table) {
            $table->dropUnique(['bimba_unit', 'no_cabang', 'tgl', 'bl']);
            $table->dropIndex(['bimba_unit']);
            $table->dropIndex(['no_cabang']);

            $table->dropColumn('bimba_unit');
            $table->dropColumn('no_cabang');
        });
    }
};