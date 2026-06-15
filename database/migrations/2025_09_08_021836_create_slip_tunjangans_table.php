<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('slip_tunjangans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_induk')->nullable();
            $table->string('nama_staff');
            $table->string('jabatan')->nullable();
            $table->string('unit')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('bulan')->nullable();

            // pendapatan
            $table->integer('tunjangan_pokok')->default(0);
            $table->integer('tunjangan_harian')->default(0);
            $table->integer('tunjangan_fungsional')->default(0);
            $table->integer('tunjangan_kesehatan')->default(0);
            $table->integer('tunjangan_kerajinan')->default(0);
            $table->integer('komisi_english')->default(0);
            $table->integer('komisi_mentor')->default(0);
            $table->integer('kekurangan_tunjangan')->default(0);
            $table->integer('tunjangan_keluarga')->default(0);
            $table->integer('lain_lain_pendapatan')->default(0);
            $table->integer('total_pendapatan')->default(0);

            // potongan
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpa')->default(0);
            $table->integer('tidak_aktif')->default(0);
            $table->integer('kelebihan_tunjangan')->default(0);
            $table->integer('lain_lain_potongan')->default(0);
            $table->integer('total_potongan')->default(0);

            // pembayaran
            $table->integer('dibayarkan')->default(0);
            $table->string('bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('atas_nama')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('slip_tunjangans');
    }
};
