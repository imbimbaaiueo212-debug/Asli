<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_produk', function (Blueprint $table) {
            $table->id(); // NO
            $table->string('jenis')->nullable();
            $table->string('label')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('harga',15,2)->nullable();
            $table->integer('min_stok')->nullable();

            // Rekap umum
            $table->integer('sld_awal')->nullable();
            $table->integer('terima')->nullable();
            $table->integer('pakai')->nullable();
            $table->integer('sld_akhir')->nullable();
            $table->string('status')->nullable();

            // Minggu 1
            $table->integer('sld_awal1')->nullable();
            $table->integer('terima1')->nullable();
            $table->integer('pakai1')->nullable();
            $table->integer('sld_akhir1')->nullable();
            $table->string('status1')->nullable();

            // Minggu 2
            $table->integer('sld_awal2')->nullable();
            $table->integer('terima2')->nullable();
            $table->integer('pakai2')->nullable();
            $table->integer('sld_akhir2')->nullable();
            $table->string('status2')->nullable();

            // Minggu 3
            $table->integer('sld_awal3')->nullable();
            $table->integer('terima3')->nullable();
            $table->integer('pakai3')->nullable();
            $table->integer('sld_akhir3')->nullable();
            $table->string('status3')->nullable();

            // Minggu 4
            $table->integer('sld_awal4')->nullable();
            $table->integer('terima4')->nullable();
            $table->integer('pakai4')->nullable();
            $table->integer('sld_akhir4')->nullable();
            $table->string('status4')->nullable();

            // Minggu 5
            $table->integer('sld_awal5')->nullable();
            $table->integer('terima5')->nullable();
            $table->integer('pakai5')->nullable();
            $table->integer('sld_akhir5')->nullable();
            $table->string('status5')->nullable();

            // Stok opname
            $table->integer('opname')->nullable();
            $table->decimal('nilai',15,2)->nullable();
            $table->decimal('selisih',15,2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_produk');
    }
};
