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
    Schema::table('order_moduls', function (Blueprint $table) {
        $table->unsignedTinyInteger('minggu_ke')->after('tanggal_order'); // 1-5
        $table->unique(['tanggal_order', 'minggu_ke']); // cegah double order minggu sama di bulan sama
    });
}

public function down(): void
{
    Schema::table('order_moduls', function (Blueprint $table) {
        $table->dropUnique(['tanggal_order', 'minggu_ke']);
        $table->dropColumn('minggu_ke');
    });
}
};
