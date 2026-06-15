<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id(); // otomatis jadi nomor urut (NO)
            $table->date('tanggal');
            $table->string('keterangan_pengajuan');
            $table->integer('harga')->default(0);
            $table->integer('jumlah')->default(0);
            $table->integer('total')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};
