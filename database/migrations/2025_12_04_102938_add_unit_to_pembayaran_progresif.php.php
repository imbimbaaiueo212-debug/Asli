<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembayaran_progresif', function (Blueprint $table) {
            $table->string('bimba_unit')->nullable()->index()->after('atas_nama');
            $table->string('no_cabang')->nullable()->index()->after('bimba_unit');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_progresif', function (Blueprint $table) {
            $table->dropColumn(['bimba_unit', 'no_cabang']);
        });
    }
};

