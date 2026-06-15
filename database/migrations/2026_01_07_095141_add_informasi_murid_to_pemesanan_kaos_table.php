<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_kaos', function (Blueprint $table) {
            $table->string('gol')->nullable()->after('nama_murid');
            $table->date('tgl_masuk')->nullable()->after('gol');
            $table->string('lama_bljr')->nullable()->after('tgl_masuk'); // string karena format seperti "2 tahun 3 bulan"
            $table->string('guru')->nullable()->after('lama_bljr');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_kaos', function (Blueprint $table) {
            $table->dropColumn(['gol', 'tgl_masuk', 'lama_bljr', 'guru']);
        });
    }
};