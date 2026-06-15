<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_moduls', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('tanggal_order')->constrained('units')->onDelete('set null');
            // Jika tabel units kamu pakai nama lain, sesuaikan di sini
        });
    }

    public function down(): void
    {
        Schema::table('order_moduls', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};