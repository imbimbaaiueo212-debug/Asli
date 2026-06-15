<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('murid_trials', function (Blueprint $table) {
            $table->date('tanggal_trial_baru')->nullable()->after('tanggal_aktif');
            // Opsional: tambahkan comment agar jelas di database
            $table->comment = 'Tanggal mulai trial baru (untuk status "baru"), diisi sekali saja';
        });
    }

    public function down(): void
    {
        Schema::table('murid_trials', function (Blueprint $table) {
            $table->dropColumn('tanggal_trial_baru');
        });
    }
};