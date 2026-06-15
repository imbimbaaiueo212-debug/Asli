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
    Schema::create('pembayaran_progresif', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->string('jabatan')->nullable();
        $table->string('status')->nullable();
        $table->string('departemen')->nullable();
        $table->string('masa_kerja')->nullable();
        
        // Data rekening
        $table->string('no_rekening')->nullable();
        $table->string('bank')->nullable();
        $table->string('atas_nama')->nullable();

        // Data pembayaran
        $table->integer('thp')->default(0);       // Take Home Pay
        $table->integer('kurang')->default(0);
        $table->integer('lebih')->default(0);
        $table->string('bulan')->nullable();
        $table->integer('transfer')->default(0);

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_progresif');
    }
};
