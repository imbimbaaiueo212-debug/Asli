<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slip_pembayaran_progresif', function (Blueprint $table) {
            $table->id();

            // Identitas staff
            $table->string('no_induk');
            $table->string('nama_staff');
            $table->string('jabatan')->nullable();
            $table->string('unit_bimba')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->string('bulan_bayar')->nullable();

            // Rincian murid
            $table->integer('am1')->default(0);
            $table->integer('am2')->default(0);
            $table->integer('mgrs')->default(0);
            $table->integer('mdf')->default(0);
            $table->integer('bnf1')->default(0);
            $table->integer('bnf2')->default(0);
            $table->integer('mb')->default(0);
            $table->integer('mt')->default(0);
            $table->integer('mbe')->default(0);
            $table->integer('mte')->default(0);

            // Rincian pendapatan
            $table->bigInteger('spp_bimba')->default(0);
            $table->bigInteger('spp_english')->default(0);

            // Rincian pembayaran
            $table->decimal('total_fm', 10, 2)->default(0);
            $table->bigInteger('nilai_progresif')->default(0);
            $table->bigInteger('total_komisi')->default(0);
            $table->bigInteger('komisi_mb_bimba')->default(0);
            $table->bigInteger('komisi_mt_bimba')->default(0);
            $table->bigInteger('komisi_mb_english')->default(0);
            $table->bigInteger('komisi_mt_english')->default(0);
            $table->bigInteger('komisi_asku')->default(0);
            $table->bigInteger('total_pendapatan')->default(0);

            // Adjustment
            $table->bigInteger('kekurangan_progresif')->default(0);
            $table->bigInteger('kelebihan_progresif')->default(0);

            // Rekening
            $table->string('bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('atas_nama')->nullable();
            $table->bigInteger('jumlah_dibayarkan')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slip_pembayaran_progresif');
    }
};
