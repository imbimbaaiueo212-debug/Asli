<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Tambah kolom setelah informasi_bimba biar rapi
            if (!Schema::hasColumn('students', 'informasi_humas_nama')) {
                $table->string('informasi_humas_nama', 255)->nullable()->after('informasi_bimba');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'informasi_humas_nama')) {
                $table->dropColumn('informasi_humas_nama');
            }
        });
    }
};
