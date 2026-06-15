<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_bagi_hasil', function (Blueprint $table) {
            $table->id();

            // Header
            $table->string('no_cabang')->nullable();
            $table->string('unit')->nullable();
            $table->date('bulan')->nullable(); // gunakan date untuk query bulanan
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('atas_nama')->nullable();

            // Bagian biMBA-AIUEO
            $table->integer('bimba_murid_aktif_lalu')->default(0);
            $table->integer('bimba_murid_baru')->default(0);
            $table->integer('bimba_murid_kembali')->default(0);
            $table->integer('bimba_murid_keluar')->default(0);
            $table->integer('bimba_murid_aktif_ini')->default(0);
            $table->integer('bimba_murid_dhuafa')->default(0);
            $table->integer('bimba_murid_bnf')->default(0);
            $table->integer('bimba_murid_garansi')->default(0);
            $table->integer('bimba_murid_deposit')->default(0);
            $table->integer('bimba_murid_piutang')->default(0);
            $table->integer('bimba_murid_wajib_spp')->default(0);
            $table->integer('bimba_murid_bayar_spp')->default(0);
            $table->integer('bimba_murid_belum_bayar')->default(0);
            $table->bigInteger('bimba_total_penerimaan_spp')->default(0);
            $table->decimal('bimba_persentase_bagi_hasil', 5, 2)->nullable();
            $table->bigInteger('bimba_jumlah_bagi_hasil')->default(0);

            // Bagian English biMBA
            $table->integer('eng_murid_aktif_lalu')->default(0);
            $table->integer('eng_murid_baru')->default(0);
            $table->integer('eng_murid_kembali')->default(0);
            $table->integer('eng_murid_keluar')->default(0);
            $table->integer('eng_murid_aktif_ini')->default(0);
            $table->integer('eng_murid_dhuafa')->default(0);
            $table->integer('eng_murid_bnf')->default(0);
            $table->integer('eng_murid_garansi')->default(0);
            $table->integer('eng_murid_deposit')->default(0);
            $table->integer('eng_murid_piutang')->default(0);
            $table->integer('eng_murid_wajib_spp')->default(0);
            $table->integer('eng_murid_bayar_spp')->default(0);
            $table->integer('eng_murid_belum_bayar')->default(0);
            $table->bigInteger('eng_total_penerimaan_spp')->default(0);
            $table->decimal('eng_persentase_bagi_hasil', 5, 2)->nullable();
            $table->bigInteger('eng_jumlah_bagi_hasil')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_bagi_hasil');
    }
};
