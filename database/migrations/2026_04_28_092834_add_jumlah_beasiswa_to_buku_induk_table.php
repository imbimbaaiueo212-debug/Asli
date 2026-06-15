<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->decimal('jumlah_beasiswa', 15, 2)
                ->nullable()
                ->after('spp');
        });
    }

    public function down(): void
    {
        Schema::table('buku_induk', function (Blueprint $table) {
            $table->dropColumn('jumlah_beasiswa');
        });
    }
};
