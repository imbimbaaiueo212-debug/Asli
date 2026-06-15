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
        $table->string('nama_humas')->nullable()->after('info');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            //
        });
    }
};
