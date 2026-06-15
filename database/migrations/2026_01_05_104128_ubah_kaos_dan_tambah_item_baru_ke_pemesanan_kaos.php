<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_kaos', function (Blueprint $table) {
            // Ubah komentar/nama logis kolom kaos (hanya dokumentasi, tidak ubah struktur)
            // Kolom 'kaos' sekarang artinya: Kaos Lengan Pendek

            // Tambah kolom baru
            $table->integer('kaos_panjang')->default(0)->after('kaos');
            $table->integer('rbas')->default(0)->after('kaos_panjang');
            $table->integer('bcabs01')->default(0)->after('rbas');
            $table->integer('bcabs02')->default(0)->after('bcabs01');
            $table->integer('sertifikat')->default(0)->after('bcabs02');
            $table->integer('stpb')->default(0)->after('sertifikat');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_kaos', function (Blueprint $table) {
            $table->dropColumn([
                'kaos_panjang',
                'rbas',
                'bcabs01',
                'bcabs02',
                'sertifikat',
                'stpb'
            ]);
        });
    }
};