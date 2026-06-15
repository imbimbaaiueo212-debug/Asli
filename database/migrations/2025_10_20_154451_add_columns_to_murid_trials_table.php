<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('murid_trials', function (Blueprint $table) {
            if (!Schema::hasColumn('murid_trials', 'nim')) {
                $table->string('nim', 20)->nullable()->unique();
            }
            if (!Schema::hasColumn('murid_trials', 'status_trial')) {
                $table->enum('status_trial', ['baru','lulus','batal'])->default('baru');
            }
            if (!Schema::hasColumn('murid_trials', 'promoted_at')) {
                $table->timestamp('promoted_at')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('murid_trials', function (Blueprint $table) {
            $table->dropColumn(['nim','status_trial','promoted_at']);
        });
    }
};
