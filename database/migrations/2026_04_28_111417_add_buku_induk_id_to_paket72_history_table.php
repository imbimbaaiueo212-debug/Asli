<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('paket72_history', function (Blueprint $table) {
            $table->unsignedBigInteger('buku_induk_id')->after('id')->nullable();

            $table->index('buku_induk_id');
        });
    }

    public function down(): void
    {
        Schema::table('paket72_history', function (Blueprint $table) {
            $table->dropColumn('buku_induk_id');
        });
    }
};
