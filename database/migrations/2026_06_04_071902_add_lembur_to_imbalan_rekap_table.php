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
    Schema::table('imbalan_rekaps', function (Blueprint $table) {
        $table->decimal('lembur_jam', 8, 2)->default(0)->after('yang_dibayarkan');
        $table->bigInteger('lembur_nominal')->default(0)->after('lembur_jam');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imbalan_rekap', function (Blueprint $table) {
            //
        });
    }
};
