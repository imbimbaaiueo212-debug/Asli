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
    Schema::table('data_produk', function (Blueprint $table) {
        $table->string('periode', 7)->nullable()->after('id'); // format YYYY-MM
    });
}

public function down()
{
    Schema::table('data_produk', function (Blueprint $table) {
        $table->dropColumn('periode');
    });
}
};
