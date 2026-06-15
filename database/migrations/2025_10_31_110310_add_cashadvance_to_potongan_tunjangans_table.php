<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('potongan_tunjangans', function (Blueprint $table) {
            if (!Schema::hasColumn('potongan_tunjangans', 'cash_advance_id')) {
                $table->unsignedBigInteger('cash_advance_id')->nullable()->after('total');
            }
            if (!Schema::hasColumn('potongan_tunjangans', 'cash_advance_nominal')) {
                $table->decimal('cash_advance_nominal', 15, 2)->nullable()->after('cash_advance_id');
            }
            if (!Schema::hasColumn('potongan_tunjangans', 'cash_advance_note')) {
                $table->text('cash_advance_note')->nullable()->after('cash_advance_nominal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('potongan_tunjangans', function (Blueprint $table) {
            if (Schema::hasColumn('potongan_tunjangans', 'cash_advance_note')) {
                $table->dropColumn('cash_advance_note');
            }
            if (Schema::hasColumn('potongan_tunjangans', 'cash_advance_nominal')) {
                $table->dropColumn('cash_advance_nominal');
            }
            if (Schema::hasColumn('potongan_tunjangans', 'cash_advance_id')) {
                $table->dropColumn('cash_advance_id');
            }
        });
    }
};
