<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('periode')
                  ->constrained('units')
                  ->onDelete('cascade');

            // Unique per unit + periode + kode
            $table->unique(['unit_id', 'periode', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {
            $table->dropUnique(['unit_id', 'periode', 'kode']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};