<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('rekap_progresif', function (Blueprint $table) {
        $table->string('bulan')->nullable()->after('id');
        $table->integer('tahun')->nullable()->after('bulan');
    });
}

public function down(): void
{
    Schema::table('rekap_progresif', function (Blueprint $table) {
        $table->dropColumn(['bulan', 'tahun']);
    });
}
};
