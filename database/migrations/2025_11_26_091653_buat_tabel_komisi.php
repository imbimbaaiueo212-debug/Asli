<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komisi', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->unsignedTinyInteger('bulan'); // 1-12
            $table->integer('nomor_urut');

            $table->string('nama');
            $table->string('jabatan');        // Kepala Unit / Guru
            $table->string('status');         // Aktif / Nonaktif
            $table->string('departemen');           // biMBA-AIUEO / English
            $table->string('masa_kerja');

            // JUMLAH SPP
            $table->bigInteger('spp_bimba')->default(0);
            $table->bigInteger('spp_english')->default(0);

            // RINCIAN KOMISI
            $table->bigInteger('komisi_mb_bimba')->default(0);
            $table->bigInteger('komisi_mt_bimba')->default(0);
            $table->bigInteger('komisi_mb_english')->default(0);
            $table->bigInteger('komisi_mt_english')->default(0);
            $table->bigInteger('total_komisi')->default(0);
            $table->bigInteger('sudah_dibayar')->default(0);

            // DATA MURID biMBA-AIUEO
            $table->integer('am1_bimba')->default(0);
            $table->integer('am2_bimba')->default(0);
            $table->integer('mgrs')->default(0);
            $table->integer('mdf')->default(0);
            $table->integer('bnf')->default(0);
            $table->integer('bnf2')->default(0);
            $table->integer('murid_mb_bimba')->default(0);
            $table->integer('mk_bimba')->default(0);
            $table->integer('murid_mt_bimba')->default(0);

            // DATA MURID ENGLISH
            $table->integer('am1_english')->default(0);
            $table->integer('am2_english')->default(0);
            $table->integer('murid_mb_english')->default(0);
            $table->integer('mk_english')->default(0);
            $table->integer('murid_mt_english')->default(0);

            // KHUSUS KEPALA UNIT
            $table->bigInteger('mb_umum_ku')->default(0);
            $table->bigInteger('mb_insentif_ku')->default(0);

            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komisi');
    }
};
