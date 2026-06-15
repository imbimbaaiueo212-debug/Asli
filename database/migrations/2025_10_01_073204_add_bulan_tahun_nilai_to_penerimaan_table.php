<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->string('bulan')->nullable()->after('guru');
            $table->year('tahun')->nullable()->after('bulan');
            $table->integer('nilai_spp')->default(0)->after('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->dropColumn(['bulan', 'tahun', 'nilai_spp']);
        });
    }
};
