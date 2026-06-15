<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bimba_unit')->nullable()->after('email');
            // optional: tambah index biar query lebih cepat
            $table->index('bimba_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['bimba_unit']); // hapus index dulu kalau ada
            $table->dropColumn(['bimba_unit']);
        });
    }
};