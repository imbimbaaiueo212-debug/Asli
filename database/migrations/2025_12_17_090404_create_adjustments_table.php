<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('bimba_unit'); // Nama unit BIMBA
            $table->string('no_cabang');   // Nomor cabang
            $table->decimal('nominal', 15, 2); // Nominal uang
            $table->unsignedTinyInteger('month'); // Bulan 1-12 (Januari-Desember)
            $table->enum('type', ['potongan', 'tambahan']); // Status: potongan atau tambahan
            $table->text('keterangan')->nullable(); // Keterangan opsional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
