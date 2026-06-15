<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('komisi', function (Blueprint $table) {
            $table->unsignedBigInteger('thp_bimba')->default(0)->after('mb_insentif_ku');
            $table->unsignedBigInteger('insentif_bimba')->default(0)->after('thp_bimba');
            $table->unsignedBigInteger('kurang_bimba')->default(0)->after('insentif_bimba');
            $table->unsignedBigInteger('lebih_bimba')->default(0)->after('kurang_bimba');
            $table->string('bulan_kurang_lebih')->nullable()->after('lebih_bimba'); // misal "Januari"
            $table->unsignedBigInteger('transfer_bimba')->default(0)->after('bulan_kurang_lebih');
        });
    }

    public function down()
    {
        Schema::table('komisi', function (Blueprint $table) {
            $table->dropColumn([
                'thp_bimba', 'insentif_bimba', 'kurang_bimba',
                'lebih_bimba', 'bulan_kurang_lebih', 'transfer_bimba'
            ]);
        });
    }
};