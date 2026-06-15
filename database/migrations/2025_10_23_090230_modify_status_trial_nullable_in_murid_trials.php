<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('murid_trials', function (Blueprint $table) {
            // Ubah kolom status_trial jadi nullable dan tanpa default
            $table->string('status_trial', 50)->nullable()->default(null)->change();
        });
    }

    public function down(): void {
        Schema::table('murid_trials', function (Blueprint $table) {
            // Kembalikan seperti semula jika rollback
            $table->enum('status_trial', ['baru','lulus','batal'])->default('baru')->change();
        });
    }
};
