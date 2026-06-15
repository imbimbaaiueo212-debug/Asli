<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('absensi_volunteer', function (Blueprint $table) {
            $table->id();
            $table->string('id_fingerprint')->nullable()->unique(); // untuk sync dari mesin/absen fingerprint
            $table->string('nik')->nullable();
            $table->string('nama_relawan');
            $table->string('posisi')->nullable();
            $table->string('bimba_unit');
            $table->string('no_cabang')->nullable();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->string('status')->default('Hadir');
            $table->text('keterangan')->nullable();
            $table->integer('jam_lembur')->default(0);
            $table->time('onduty')->nullable();     // jam masuk jadwal
            $table->time('offduty')->nullable();    // jam pulang jadwal
            $table->timestamps();

            // Biar tidak double absen di hari yang sama
            $table->unique(['id_fingerprint', 'tanggal']);
            $table->unique(['nik', 'tanggal']);

            // Untuk pencarian cepat
            $table->index('bimba_unit');
            $table->index('tanggal');
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensi_volunteer');
    }
};