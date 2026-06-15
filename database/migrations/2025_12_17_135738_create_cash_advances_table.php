<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Snapshot data profile saat pengajuan
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('no_telepon')->nullable();
            $table->text('alamat')->nullable();

            // Data pengajuan
            $table->string('bulan_pengajuan'); // contoh: "Desember 2025"
            $table->decimal('nominal_pinjam', 15, 2);
            $table->integer('jangka_waktu'); // jumlah bulan
            $table->decimal('angsuran_per_bulan', 15, 2)->nullable();
            $table->text('keperluan');

            // Status approval
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
    }
};