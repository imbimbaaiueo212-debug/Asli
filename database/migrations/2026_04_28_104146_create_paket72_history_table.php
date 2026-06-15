<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paket72_history', function (Blueprint $table) {
            $table->id();

            $table->string('nim')->index();
            $table->string('nama');

            $table->date('tgl_bayar');
            $table->date('tgl_selesai');

            $table->string('status')->default('aktif');
            // aktif / expired

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket72_history');
    }
};
