<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->date('tanggal_penyerahan_kaos_pendek')->nullable()->after('kaos_lengan_panjang');
            $table->date('tanggal_penyerahan_kaos_panjang')->nullable()->after('kaos_lengan_panjang');
            $table->date('tanggal_penyerahan_kpk')->nullable()->after('kpk');
            $table->date('tanggal_penyerahan_tas')->nullable()->after('tas');
            $table->date('tanggal_penyerahan_rbas')->nullable()->after('RBAS');
            $table->date('tanggal_penyerahan_bcabs01')->nullable()->after('BCABS01');
            $table->date('tanggal_penyerahan_bcabs02')->nullable()->after('BCABS02');
            $table->date('tanggal_penyerahan_sertifikat')->nullable()->after('sertifikat');
            $table->date('tanggal_penyerahan_stpb')->nullable()->after('stpb');
            $table->date('tanggal_penyerahan_event')->nullable()->after('event');
            $table->date('tanggal_penyerahan_lainlain')->nullable()->after('lain_lain');
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_penyerahan_kaos_pendek',
                'tanggal_penyerahan_kaos_panjang',
                'tanggal_penyerahan_kpk',
                'tanggal_penyerahan_tas',
                'tanggal_penyerahan_rbas',
                'tanggal_penyerahan_bcabs01',
                'tanggal_penyerahan_bcabs02',
                'tanggal_penyerahan_sertifikat',
                'tanggal_penyerahan_stpb',
                'tanggal_penyerahan_event',
                'tanggal_penyerahan_lainlain',
            ]);
        });
    }
};