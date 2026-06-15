<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyesuaian_ktr', function (Blueprint $table) {
            $table->id();
            $table->string('jumlah_murid');     // kolom jumlah murid
            $table->string('penyesuaian_ktr');  // kolom penyesuaian KTR
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_ktr');
    }
};
