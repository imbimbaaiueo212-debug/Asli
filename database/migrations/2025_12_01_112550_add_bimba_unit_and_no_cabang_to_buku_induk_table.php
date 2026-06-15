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
    Schema::table('buku_induk', function (Blueprint $table) {
        $table->string('bimba_unit')->nullable()->after('petugas_trial');
        $table->string('no_cabang', 20)->nullable()->after('bimba_unit');
    });
}

public function down()
{
    Schema::table('buku_induk', function (Blueprint $table) {
        $table->dropColumn(['bimba_unit', 'no_cabang']);
    });
}
};
