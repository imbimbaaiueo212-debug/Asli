<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buku_induk', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('nama');
            $table->string('tmpt_lahir')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->integer('usia')->nullable();
            $table->string('lama_bljr')->nullable();
            $table->string('tahap')->nullable();
            $table->date('tgl_keluar')->nullable();
            $table->string('kategori_keluar')->nullable();
            $table->string('alasan')->nullable();
            $table->string('kelas')->nullable();
            $table->string('gol')->nullable();
            $table->string('kd')->nullable();
            $table->string('spp')->nullable();
            $table->string('status')->nullable();
            $table->string('petugas_trial')->nullable();
            $table->string('guru')->nullable();
            $table->string('orangtua')->nullable();
            $table->string('no_telp_hp')->nullable();
            $table->text('note')->nullable();
            $table->string('no_cab_merge')->nullable();
            $table->string('no_pembayaran_murid')->nullable();
            $table->text('note_garansi')->nullable();
            $table->string('periode')->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_akhir')->nullable();
            $table->string('alert')->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->string('alert2')->nullable(); // ALERT'
            $table->string('asal_modul')->nullable();
            $table->text('keterangan_optional')->nullable();
            $table->string('level')->nullable();
            $table->string('jenis_kbm')->nullable();
            $table->string('kode_jadwal')->nullable();
            $table->string('hari_jam')->nullable();
            $table->text('alamat_murid')->nullable();
            $table->string('status_pindah')->nullable();
            $table->date('tanggal_pindah')->nullable();
            $table->string('ke_bimba_intervio')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_induk');
    }
};

