<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            // Tambah kolom tanggal penyerahan
            $table->date('tanggal_penyerahan')->nullable()->after('tanggal');

            // Tambah kolom untuk Kaos Anak Lengan Panjang (nominal Rupiah)
            $table->integer('kaos_lengan_panjang')->default(0)->after('kaos');
        });
    }

    public function down()
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->dropColumn(['tanggal_penyerahan', 'kaos_lengan_panjang']);
        });
    }
};