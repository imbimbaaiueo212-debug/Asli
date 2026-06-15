<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendapatanTunjanganTable extends Migration
{
    public function up()
    {
        Schema::create('pendapatan_tunjangan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('status')->nullable();
            $table->string('departemen')->nullable();
            $table->string('masa_kerja')->nullable();
            $table->decimal('thp', 15, 2)->default(0);
            $table->decimal('kerajinan', 15, 2)->default(0);
            $table->decimal('english', 15, 2)->default(0);
            $table->decimal('mentor', 15, 2)->default(0);
            $table->decimal('kekurangan', 15, 2)->default(0);
            $table->string('bulan')->nullable();
            $table->decimal('tj_keluarga', 15, 2)->default(0);
            $table->decimal('lain_lain', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pendapatan_tunjangan');
    }
}
