<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penerimaan_produk', function (Blueprint $table) {
            $table->foreignId('unit_id')
                  ->nullable() // boleh kosong sementara kalau ada data lama
                  ->after('faktur')
                  ->constrained('units')
                  ->onDelete('set null'); // kalau unit dihapus, penerimaan tetap ada tapi unit jadi null
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_produk', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};