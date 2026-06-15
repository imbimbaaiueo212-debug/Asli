<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('no_urut')->nullable();            // NO
            $table->string('nik', 50)->unique();               // NIK
            $table->string('nama', 100);                       // NAMA
            $table->string('jabatan', 100)->nullable();        // JABATAN
            $table->string('status_karyawan', 50)->nullable(); // STATUS
            $table->string('departemen', 100)->nullable();     // DEPARTEMEN
            $table->date('tgl_masuk')->nullable();             // TGL MASUK
            $table->integer('masa_kerja')->nullable();         // MASA KERJA (bulan/tahun)
            $table->integer('jumlah_murid_mba')->nullable();   // J MURID MBA
            $table->integer('jumlah_murid_eng')->nullable();   // J MURID ENG
            $table->integer('total_murid')->nullable();        // T MURID
            $table->integer('jumlah_murid_jadwal')->nullable();// J MURID (JDWL)
            $table->integer('jumlah_rombim')->nullable();      // JUMLAH ROMBIM
            $table->integer('rb')->nullable();                 // RB
            $table->integer('rb_tambahan')->nullable();        // RB !
            $table->integer('ktr')->nullable();                // KTR
            $table->integer('ktr_tambahan')->nullable();       // KTR !
            $table->decimal('rp', 15, 2)->nullable();          // RP'
            $table->string('jenis_mutasi', 100)->nullable();   // JENIS MUTASI
            $table->date('tgl_mutasi_jabatan')->nullable();    // TGL MUTASI JABATAN
            $table->integer('masa_kerja_jabatan')->nullable(); // MASA KERJA JABATAN TERBARU
            $table->date('tgl_lahir')->nullable();             // TGL LAHIR
            $table->integer('usia')->nullable();               // USIA
            $table->string('no_telp', 20)->nullable();         // NO TELP
            $table->string('email', 100)->nullable();          // EMAIL
            $table->string('no_rekening', 50)->nullable();     // NO REKENING
            $table->string('bank', 50)->nullable();            // BANK
            $table->string('atas_nama', 100)->nullable();      // ATAS NAMA
            $table->string('mentor_magang', 100)->nullable();  // MENTOR MAGANG
            $table->string('periode', 50)->nullable();         // PERIODE
            $table->date('tgl_selesai_magang')->nullable();    // TGL SELESAI MAGANG
            $table->string('ukuran', 10)->nullable();          // UKURAN
            $table->string('status_lain', 50)->nullable();     // STATUS'
            $table->text('keterangan')->nullable();            // KETERANGAN
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
