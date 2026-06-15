<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->text('keterangan_info')->nullable()->after('info');
        });
    }

    public function down(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->dropColumn('keterangan_info');
        });
    }
};
