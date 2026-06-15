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
        Schema::table('imbalan_rekaps', function (Blueprint $table) {

            // Status pembayaran
            $table->enum('status_pembayaran', [
                'draft',
                'dibayar'
            ])->default('draft')->after('yang_dibayarkan');

            // tanggal dibayar
            $table->timestamp('tanggal_dibayar')->nullable();

            // siapa yang membayar
            $table->string('dibayar_oleh')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {

            $table->dropColumn([
                'status_pembayaran',
                'tanggal_dibayar',
                'dibayar_oleh'
            ]);

        });
    }
};