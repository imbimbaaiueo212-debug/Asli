<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('registrations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->string('tahun_ajaran');                    // ex: 2025/2026
            $t->string('gelombang')->nullable();
            $t->string('program')->nullable();
            $t->enum('status', ['pending','verified','accepted','rejected'])->default('pending');
            $t->date('tanggal_daftar')->nullable();
            $t->timestamps();

            $t->unique(['student_id','tahun_ajaran']);     // 1 murid 1x per TA
            $t->index(['tahun_ajaran','status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('registrations');
    }
};
