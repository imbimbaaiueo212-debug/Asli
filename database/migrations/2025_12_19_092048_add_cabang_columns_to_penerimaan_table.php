<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->string('RBAS')->nullable()->after('no_cabang');      // Contoh: kode cabang RBAS
            $table->string('BCABS01')->nullable()->after('no_cabang');  // Contoh: kode cabang BCABS01
            $table->string('BCABS02')->nullable()->after('no_cabang');  // Contoh: kode cabang BCABS02
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan', function (Blueprint $table) {
            $table->dropColumn(['RBAS', 'BCABS01', 'BCABS02']);
        });
    }
};