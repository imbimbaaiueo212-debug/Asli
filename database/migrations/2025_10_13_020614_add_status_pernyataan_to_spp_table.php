<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('spp', function (Blueprint $table) {
            $table->string('status_pernyataan')->default('Belum Membuat Pernyataan')->after('file_pernyataan');
        });
    }

    public function down()
    {
        Schema::table('spp', function (Blueprint $table) {
            $table->dropColumn('status_pernyataan');
        });
    }
};
    
