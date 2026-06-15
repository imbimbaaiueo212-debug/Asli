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
        $table->text('alasan_garansi')->nullable()->after('perpanjang_garansi');
    });
}

public function down()
{
    Schema::table('buku_induk', function (Blueprint $table) {
        $table->dropColumn('alasan_garansi');
    });
}
};
