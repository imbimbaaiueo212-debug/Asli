<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyesuaian_rb_guru', function (Blueprint $table) {
            $table->id();
            $table->string('jumlah_murid');     // kolom jumlah murid
            $table->string('slot_rombim');      // kolom slot rombim
            $table->string('jam_kegiatan');     // kolom jam kegiatan
            $table->string('penyesuaian_rb');   // kolom penyesuaian RB
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_rb_guru');
    }
};
