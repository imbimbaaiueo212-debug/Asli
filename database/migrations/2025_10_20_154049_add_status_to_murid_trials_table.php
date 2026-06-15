<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('murid_trials', function (Blueprint $t) {
            if (!Schema::hasColumn('murid_trials','status_trial')) {
                $t->enum('status_trial', ['baru','lulus','batal'])->default('baru')->after('alamat');
            }
            if (!Schema::hasColumn('murid_trials','promoted_at')) {
                $t->timestamp('promoted_at')->nullable()->after('status_trial');
            }
            // pastikan kolom nim sudah ada sesuai kirimanmu; kalau belum, tambahkan:
            if (!Schema::hasColumn('murid_trials','nim')) {
                $t->string('nim',20)->nullable()->after('nama');
                $t->unique('nim','murid_trials_nim_unique');
            }
        });
    }
    public function down(): void {
        Schema::table('murid_trials', function (Blueprint $t) {
            if (Schema::hasColumn('murid_trials','promoted_at')) $t->dropColumn('promoted_at');
            if (Schema::hasColumn('murid_trials','status_trial')) $t->dropColumn('status_trial');
            // hati-hati: jangan drop nim kalau sudah dipakai fitur lain
            // $t->dropUnique('murid_trials_nim_unique'); $t->dropColumn('nim');
        });
    }
};
