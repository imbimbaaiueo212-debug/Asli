<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {

            $table->integer('saldo_sistem')
                  ->default(0)
                  ->after('sld_akhir');

        });
    }

    public function down(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {

            $table->dropColumn('saldo_sistem');

        });
    }
};
