<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            if (!Schema::hasColumn('imbalan_rekaps', 'keterangan_kelebihan')) {
                $table->text('keterangan_kelebihan')->nullable()->after('bulan_kelebihan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            if (Schema::hasColumn('imbalan_rekaps', 'keterangan_kelebihan')) {
                $table->dropColumn('keterangan_kelebihan');
            }
        });
    }
};