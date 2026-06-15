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
    Schema::create('penerimaan', function (Blueprint $table) {
        $table->id();
        $table->string('kwitansi')->unique();
        $table->string('via');
        $table->date('tanggal');
        $table->string('nim');
        $table->string('nama_murid');
        $table->string('kelas');
        $table->string('gol')->nullable();
        $table->string('kd')->nullable();
        $table->string('status');
        $table->string('guru');

        // Komponen biaya
        $table->integer('daftar')->default(0);
        $table->integer('voucher')->default(0);
        $table->integer('spp')->default(0);
        $table->integer('kaos')->default(0);
        $table->integer('kpk')->default(0);
        $table->integer('sertifikat')->default(0);
        $table->integer('stpb')->default(0);
        $table->integer('tas')->default(0);
        $table->integer('event')->default(0);
        $table->integer('lain_lain')->default(0);
        $table->integer('total')->default(0);

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan');
    }
};
