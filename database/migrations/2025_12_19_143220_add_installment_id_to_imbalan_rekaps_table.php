<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            $table->unsignedBigInteger('installment_id')->nullable()->after('keterangan_cicilan');
            $table->foreign('installment_id')->references('id')->on('cash_advance_installments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            $table->dropForeign(['installment_id']);
            $table->dropColumn('installment_id');
        });
    }
};