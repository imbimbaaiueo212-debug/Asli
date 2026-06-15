<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('registrations', function (Blueprint $table) {
            
            // Surat Garansi Utama
            if (!Schema::hasColumn('registrations', 'tgl_surat_garansi')) {
                $table->date('tgl_surat_garansi')->nullable();
            }
            
            if (!Schema::hasColumn('registrations', 'note_garansi')) {
                $table->string('note_garansi')->nullable();
            }

            // Kolom Garansi Lainnya
            if (!Schema::hasColumn('registrations', 'tgl_pengajuan_garansi')) {
                $table->date('tgl_pengajuan_garansi')->nullable();
            }
            
            if (!Schema::hasColumn('registrations', 'tgl_selesai_garansi')) {
                $table->date('tgl_selesai_garansi')->nullable();
            }
            
            if (!Schema::hasColumn('registrations', 'masa_aktif_garansi')) {
                $table->string('masa_aktif_garansi')->nullable();
            }
            
            if (!Schema::hasColumn('registrations', 'perpanjang_garansi')) {
                $table->boolean('perpanjang_garansi')->nullable();
            }
            
            if (!Schema::hasColumn('registrations', 'alasan_garansi')) {
                $table->text('alasan_garansi')->nullable();
            }

        });
    }

    public function down()
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'tgl_surat_garansi',
                'note_garansi',
                'tgl_pengajuan_garansi',
                'tgl_selesai_garansi',
                'masa_aktif_garansi',
                'perpanjang_garansi',
                'alasan_garansi',
            ]);
        });
    }
};