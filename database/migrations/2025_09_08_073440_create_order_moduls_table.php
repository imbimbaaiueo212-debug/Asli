<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_moduls', function (Blueprint $table) {
            $table->id();

            // Minggu ke-1
            $table->string('kode1')->nullable();
            $table->integer('jml1')->nullable();
            $table->decimal('hrg1',12,2)->nullable();
            $table->string('sts1')->nullable();

            // Minggu ke-2
            $table->string('kode2')->nullable();
            $table->integer('jml2')->nullable();
            $table->decimal('hrg2',12,2)->nullable();
            $table->string('sts2')->nullable();

            // Minggu ke-3
            $table->string('kode3')->nullable();
            $table->integer('jml3')->nullable();
            $table->decimal('hrg3',12,2)->nullable();
            $table->string('sts3')->nullable();

            // Minggu ke-4
            $table->string('kode4')->nullable();
            $table->integer('jml4')->nullable();
            $table->decimal('hrg4',12,2)->nullable();
            $table->string('sts4')->nullable();

            // Minggu ke-5
            $table->string('kode5')->nullable();
            $table->integer('jml5')->nullable();
            $table->decimal('hrg5',12,2)->nullable();
            $table->string('sts5')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_moduls');
    }
};
