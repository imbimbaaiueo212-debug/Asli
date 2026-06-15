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
    Schema::create('pengajuan_garansi', function (Blueprint $table) {
        $table->id();
        $table->string('nim');
        $table->string('nama_murid');
        $table->string('bimba_unit')->nullable();

        $table->date('tgl_pengajuan')->nullable();
        $table->text('alasan')->nullable();

        $table->enum('status', ['pending','disetujui','ditolak'])
              ->default('pending');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_garansi');
    }
};
