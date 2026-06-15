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
    Schema::create('cash_advance_installments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cash_advance_id')->constrained('cash_advances')->onDelete('cascade');
        $table->integer('cicilan_ke'); // 1 sampai 10
        $table->date('jatuh_tempo'); // bulan cicilan (misal: 2025-12-01)
        $table->decimal('nominal_angsuran', 15, 2);
        $table->decimal('sudah_dibayar', 15, 2)->default(0);
        $table->date('tanggal_bayar')->nullable();
        $table->enum('status', ['belum', 'lunas'])->default('belum');
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}
};
