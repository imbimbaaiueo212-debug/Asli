<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_produk_adjustments', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('data_produk_id');

            $table->string('kode')->nullable();

            $table->string('jenis_adjustment');

            $table->integer('qty_adjustment')->default(0);

            $table->integer('stok_sebelum')->default(0);

            $table->integer('fisik_sebelum')->default(0);

            $table->integer('selisih_sebelum')->default(0);

            $table->integer('stok_sesudah')->default(0);

            $table->integer('fisik_sesudah')->default(0);

            $table->integer('selisih_sesudah')->default(0);

            $table->text('keterangan')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_produk_adjustments');
    }
};
