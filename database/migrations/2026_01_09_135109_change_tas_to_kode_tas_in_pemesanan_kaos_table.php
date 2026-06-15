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
    Schema::table('pemesanan_kaos', function (Blueprint $table) {
        // Hapus kolom lama
        $table->dropColumn('tas');

        // Tambah kolom baru: kode tas
        $table->string('kode_tas')->nullable()->after('kpk');

        // Optional: tambah kolom jumlah tas (default 1, karena biasanya 1 per murid)
        $table->integer('jumlah_tas')->default(1)->after('kode_tas');
    });
}

public function down(): void
{
    Schema::table('pemesanan_kaos', function (Blueprint $table) {
        $table->dropColumn(['kode_tas', 'jumlah_tas']);
        $table->integer('tas')->nullable();
    });
}
};
