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
    Schema::table('garansi_bca', function (Blueprint $table) {
        $table->string('sumber')
            ->default('manual')
            ->after('tanggal_diberikan');
    });
}

public function down(): void
{
    Schema::table('garansi_bca', function (Blueprint $table) {
        $table->dropColumn('sumber');
    });
}
};
