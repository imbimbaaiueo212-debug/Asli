<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_progresif', function (Blueprint $table) {
            $table->id();

            // Data dasar
            $table->string('nama')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('status')->nullable();
            $table->string('departemen')->nullable();
            $table->string('masa_kerja')->nullable();

            // Data murid dan SPP
            $table->integer('spp_bimba')->default(0);
            $table->integer('spp_english')->default(0);

            // FM, progresif, komisi
            $table->integer('total_fm')->default(0);
            $table->integer('progresif')->default(0);
            $table->integer('komisi')->default(0);
            $table->integer('dibayarkan')->default(0);

            // Kolom AM, MGRS, MDF, BNF dll
            $table->integer('am1')->default(0);
            $table->integer('am2')->default(0);
            $table->integer('mgrs')->default(0);
            $table->integer('mdf')->default(0);
            $table->integer('bnf')->default(0);
            $table->integer('bnf2')->default(0);
            $table->integer('mb')->default(0);
            $table->integer('mt')->default(0);
            $table->integer('mb_eb')->default(0);
            $table->integer('mt_eb')->default(0);

            // A-F
            $table->integer('a')->default(0);
            $table->integer('b')->default(0);
            $table->integer('c')->default(0);
            $table->integer('d')->default(0);
            $table->integer('e')->default(0);
            $table->integer('f')->default(0);

            // BNF'
            $table->integer('bnf_1')->default(0);
            $table->integer('a_1')->default(0);
            $table->integer('b_1')->default(0);
            $table->integer('c_1')->default(0);
            $table->integer('d_1')->default(0);
            $table->integer('e_1')->default(0);

            // A'' - F'' 
            $table->integer('a_2')->default(0);
            $table->integer('b_2')->default(0);
            $table->integer('c_2')->default(0);
            $table->integer('d_2')->default(0);
            $table->integer('e_2')->default(0);
            $table->integer('f_2')->default(0);

            // MDF', BNF'', MGRS2
            $table->integer('mdf_1')->default(0);
            $table->integer('bnf_2_2')->default(0);
            $table->integer('mgrs2')->default(0);

            // A''' - E'''
            $table->integer('a_3')->default(0);
            $table->integer('b_3')->default(0);
            $table->integer('c_3')->default(0);
            $table->integer('d_3')->default(0);
            $table->integer('e_3')->default(0);

            // MB', MT', MB.Eb', MT.Eb', ASKU
            $table->integer('mb_1')->default(0);
            $table->integer('mt_1')->default(0);
            $table->integer('mb_eb_1')->default(0);
            $table->integer('mt_eb_1')->default(0);
            $table->integer('asku')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_progresif');
    }
};
