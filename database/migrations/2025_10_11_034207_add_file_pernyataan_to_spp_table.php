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
    Schema::table('spp', function (Blueprint $table) {
        $table->string('file_pernyataan')->nullable()->after('kelas');
    });
}

public function down()
{
    Schema::table('spp', function (Blueprint $table) {
        $table->dropColumn('file_pernyataan');
    });
}
};
