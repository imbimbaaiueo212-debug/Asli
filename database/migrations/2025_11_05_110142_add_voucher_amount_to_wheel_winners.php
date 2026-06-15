<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi: tambahkan kolom voucher_amount.
     */
    public function up(): void
    {
        Schema::table('wheel_winners', function (Blueprint $table) {
            $table->integer('voucher_amount')
                  ->nullable()
                  ->after('voucher_index')
                  ->comment('Nilai nominal voucher dalam rupiah, misal 50000 atau 1200000');
        });
    }

    /**
     * Kembalikan perubahan (rollback).
     */
    public function down(): void
    {
        Schema::table('wheel_winners', function (Blueprint $table) {
            $table->dropColumn('voucher_amount');
        });
    }
};
