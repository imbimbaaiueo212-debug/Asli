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
        Schema::table('data_produk', function (Blueprint $table) {

    $table->string('adjustment_status')->nullable();

    $table->string('adjustment_type')->nullable();

    $table->text('adjustment_note')->nullable();

    $table->integer('adjustment_qty')->default(0);

    $table->timestamp('adjustment_at')->nullable();

    $table->unsignedBigInteger('adjustment_by')->nullable();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {
            //
        });
    }
};
