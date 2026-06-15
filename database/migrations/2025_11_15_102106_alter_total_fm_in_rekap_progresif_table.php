<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTotalFmInRekapProgresifTable extends Migration
{
    public function up()
    {
        Schema::table('rekap_progresif', function (Blueprint $table) {
            // ganti jadi decimal(8,2) atau double sesuai kebutuhan
            $table->decimal('total_fm', 8, 2)->nullable()->change();
            // jika kolom tidak nullable dan kamu ingin default 0:
            // $table->decimal('total_fm', 8, 2)->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('rekap_progresif', function (Blueprint $table) {
            // ubah kembali sesuai tipe lama, mis. integer
            $table->integer('total_fm')->nullable()->change();
        });
    }
}
