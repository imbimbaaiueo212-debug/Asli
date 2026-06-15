<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🧭 Ubah kolom status_trial menjadi string (lebih fleksibel)
        Schema::table('murid_trials', function (Blueprint $table) {
            $table->string('status_trial', 30)->nullable()->change();
        });

        // 🪄 (Opsional) Normalisasi data lama:
        DB::table('murid_trials')
            ->where('status_trial', 'baru')
            ->update(['status_trial' => 'aktif']);

        DB::table('murid_trials')
            ->where('status_trial', 'lulus')
            ->update(['status_trial' => 'lanjut_daftar']);
    }

    public function down(): void
    {
        // Jika ingin revert ke enum lama (opsional)
        Schema::table('murid_trials', function (Blueprint $table) {
            $table->enum('status_trial', ['baru', 'lulus', 'batal'])->nullable()->change();
        });
    }
};
