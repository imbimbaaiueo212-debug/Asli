<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('potongan_tunjangans', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('id'); // ✅ tambahkan kolom nik setelah id
        });
    }

    public function down(): void
    {
        Schema::table('potongan_tunjangans', function (Blueprint $table) {
            $table->dropColumn('nik');
        });
    }
};
