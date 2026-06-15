<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->id();
            $table->string('no_bukti')->unique();   // Nomor bukti transaksi
            $table->date('tanggal');               // Tanggal transaksi
            $table->string('kategori');            // Misal: ATK, Transport, dll
            $table->string('keterangan');          // Uraian transaksi
            $table->integer('debit')->default(0);  // Uang masuk
            $table->integer('kredit')->default(0); // Uang keluar
            $table->integer('saldo')->default(0);  // Saldo berjalan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash');
    }
};
