<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // murid_trials
        Schema::table('murid_trials', function (Blueprint $table) {
            if (!Schema::hasColumn('murid_trials','nim')) {
                $table->string('nim', 20)->nullable()->after('nama');
                $table->unique('nim','murid_trials_nim_unique');
            }
        });
        // buku_induk
        Schema::table('buku_induk', function (Blueprint $table) {
            if (!Schema::hasColumn('buku_induk','nim')) {
                $table->string('nim', 20)->after('id'); // atau setelah kolom yang kamu mau
                $table->unique('nim','buku_induk_nim_unique');
            }
        });
    }
    public function down(): void {
        Schema::table('murid_trials', function (Blueprint $table) {
            $table->dropUnique('murid_trials_nim_unique');
            $table->dropColumn('nim');
        });
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->dropUnique('buku_induk_nim_unique');
            $table->dropColumn('nim');
        });
    }
};

