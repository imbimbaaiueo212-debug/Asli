<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom no_voucher ke tabel voucher_lama
     */
    public function up(): void
    {
        Schema::table('voucher_lama', function (Blueprint $table) {
            if (!Schema::hasColumn('voucher_lama', 'no_voucher')) {
                $table->string('no_voucher', 100)
                    ->nullable()
                    ->after('voucher')
                    ->comment('Nomor unik voucher manual');
            }
        });
    }

    /**
     * Hapus kolom jika rollback
     */
    public function down(): void
    {
        Schema::table('voucher_lama', function (Blueprint $table) {
            if (Schema::hasColumn('voucher_lama', 'no_voucher')) {
                $table->dropColumn('no_voucher');
            }
        });
    }
};
