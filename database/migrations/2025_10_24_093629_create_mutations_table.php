<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mutations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('tipe', ['masuk','keluar'])->default('masuk'); // fokus: mutasi masuk
            $t->string('nama');               // jika belum ada student_id
            $t->string('asal_unit')->nullable();   // biMBA asal
            $t->string('asal_kode')->nullable();   // kode unit asal (opsional)
            $t->date('tanggal_mutasi')->nullable();
            $t->text('alasan')->nullable();
            $t->text('keterangan')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('mutations');
    }
};

