<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lembur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            
            $table->date('tgl_lembur');
            $table->time('jam_awal');
            $table->time('jam_selesai');
            $table->decimal('total_jam', 5, 2)->nullable(); // dihitung otomatis
            
            $table->text('keterangan')->nullable();
            $table->string('status')->default('Diajukan'); // Diajukan, Disetujui, Ditolak
            
            $table->timestamps();
            
            $table->index(['tgl_lembur', 'profile_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lembur');
    }
};