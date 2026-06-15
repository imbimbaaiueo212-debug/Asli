<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ktrs', function (Blueprint $table) {
            $table->id(); // Kolom NO
            $table->date('waktu'); // Waktu / Minggu
            $table->string('kategori'); // Kategori transaksi
            $table->decimal('jumlah', 15, 2); // Rp. (pakai decimal agar bisa menyimpan angka besar)
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ktrs');
    }
};
