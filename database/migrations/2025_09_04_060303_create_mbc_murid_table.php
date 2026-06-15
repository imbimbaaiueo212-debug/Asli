<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbc_murid', function (Blueprint $table) {
            $table->id();
            
            // Profil Unit
            $table->string('no_cabang')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();

            // Profil Murid
            $table->string('no_pembayaran')->nullable();
            $table->string('nama_murid')->nullable();
            $table->string('kelas')->nullable();
            $table->string('golongan_kode')->nullable();
            $table->decimal('spp', 15, 2)->nullable();
            $table->string('wali_murid')->nullable();

            // Bill Payment / Virtual Account placeholder
            $table->text('bill_payment')->nullable();
            $table->text('virtual_account')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbc_murid');
    }
};
