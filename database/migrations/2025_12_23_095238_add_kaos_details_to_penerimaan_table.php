<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('penerimaan', function (Blueprint $table) {
        // Kolom untuk kaos pendek: array ukuran + jumlah
        $table->json('kaos_pendek_details')->nullable(); // contoh: [{"ukuran": "KAL", "jumlah": 2}, {"ukuran": "KAM", "jumlah": 1}]

        // Kolom untuk kaos panjang
        $table->json('kaos_panjang_details')->nullable();

        // Opsional: total pcs otomatis (cache)
        $table->integer('kaos_pendek_pcs')->default(0);
        $table->integer('kaos_panjang_pcs')->default(0);
    });
}

public function down()
{
    Schema::table('penerimaan', function (Blueprint $table) {
        $table->dropColumn(['kaos_pendek_details', 'kaos_panjang_details', 'kaos_pendek_pcs', 'kaos_panjang_pcs']);
    });
}
};
