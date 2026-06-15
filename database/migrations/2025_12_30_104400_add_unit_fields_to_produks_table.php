<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('produk', function (Blueprint $table) {
        $table->string('bimba_unit');           // nama unit, misal: GRIYA PESONA MADANI
        $table->string('no_cabang', 5);         // kode cabang 5 digit: 05141
        $table->index(['bimba_unit', 'no_cabang']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            //
        });
    }
};
