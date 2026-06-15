<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('murid_trials', function (Blueprint $table) {
            // Kolom ini akan menyimpan waktu submit dari Google Form
            // Dibuat unique untuk mencegah impor data yang sama berulang kali.
            $table->timestamp('waktu_submit')->nullable()->unique(); 
        });
    }

    public function down(): void
    {
        Schema::table('murid_trials', function (Blueprint $table) {
            // Urutan drop index dan drop column harus benar
            $table->dropUnique(['waktu_submit']);
            $table->dropColumn('waktu_submit');
        });
    }
};
