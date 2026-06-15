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
    Schema::create('jadwal_detail', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('murid_id');
        $table->enum('hari', ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']);
        $table->enum('shift', ['SRJ','SKS','S6']);
        $table->integer('jam_ke')->default(1); // slot 40 menit
        $table->timestamps();

        $table->foreign('murid_id')->references('id')->on('buku_induk')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_detail');
    }
};
