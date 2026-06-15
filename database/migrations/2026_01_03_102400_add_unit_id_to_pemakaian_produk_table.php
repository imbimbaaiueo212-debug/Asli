<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemakaian_produk', function (Blueprint $table) {
            $table->foreignId('unit_id')
                  ->after('tanggal')
                  ->constrained('units')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('pemakaian_produk', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};