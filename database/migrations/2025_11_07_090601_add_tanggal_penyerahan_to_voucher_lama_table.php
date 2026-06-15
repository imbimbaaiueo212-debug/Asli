<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTanggalPenyerahanToVoucherLamaTable extends Migration
{
    public function up()
    {
        Schema::table('voucher_lama', function (Blueprint $table) {
            // jika Anda ingin menyimpan hanya tanggal (tanpa waktu)
            $table->date('tanggal_penyerahan')->nullable()->after('tanggal');
            
            // jika ingin datetime gunakan:
            // $table->dateTime('tanggal_penyerahan')->nullable()->after('tanggal');
        });
    }

    public function down()
    {
        Schema::table('voucher_lama', function (Blueprint $table) {
            $table->dropColumn('tanggal_penyerahan');
        });
    }
}
