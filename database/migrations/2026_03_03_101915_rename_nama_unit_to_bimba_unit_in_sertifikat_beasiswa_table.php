<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikat_beasiswa', function (Blueprint $table) {
            // Rename kolom lama ke baru
            $table->renameColumn('nama_unit', 'bimba_unit');
        });
    }

    public function down(): void
    {
        Schema::table('sertifikat_beasiswa', function (Blueprint $table) {
            // Kembalikan jika rollback
            $table->renameColumn('bimba_unit', 'nama_unit');
        });
    }
};