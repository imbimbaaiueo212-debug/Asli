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
    Schema::table('potongan_tunjangans', function (Blueprint $table) {
        $table->string('kelebihan_bulan', 7)->nullable()->after('kelebihan'); // format YYYY-MM
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('potongan_tunjangans', function (Blueprint $table) {
            //
        });
    }
};
