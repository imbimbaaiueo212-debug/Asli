<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buku_induk_beasiswa_history', function (Blueprint $table) {
            $table->id();
            $table->string('nim');
            $table->string('nama');
            $table->string('alamat_murid');
            $table->string('orangtua');

            $table->string('periode'); // Ke-1, Ke-2, dst
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_akhir')->nullable();

            $table->decimal('jumlah_beasiswa', 15, 2)->nullable();

            $table->string('status')->default('aktif'); // aktif / selesai
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_induk_beasiswa_history');
    }
};
