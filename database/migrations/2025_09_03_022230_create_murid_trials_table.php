<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('murid_trials', function (Blueprint $table) {
            $table->id(); 
            $table->date('tgl_mulai')->nullable(); 
            $table->string('kelas')->nullable();   
            $table->string('nama');                
            $table->date('tgl_lahir')->nullable(); 
            $table->integer('usia')->nullable();   
            $table->string('guru_trial')->nullable(); 
            $table->text('info')->nullable();      
            $table->string('orangtua')->nullable(); 
            $table->string('no_telp', 20)->nullable(); 
            $table->text('alamat')->nullable();    
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('murid_trials');
    }
};
