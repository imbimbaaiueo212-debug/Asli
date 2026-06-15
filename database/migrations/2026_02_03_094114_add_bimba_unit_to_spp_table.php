<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp', function (Blueprint $table) {
            $table->string('bimba_unit', 50)->nullable()->after('nim');
            // atau jika ingin index: $table->index('bimba_unit');
        });
    }

    public function down(): void
    {
        Schema::table('spp', function (Blueprint $table) {
            $table->dropColumn('bimba_unit');
        });
    }
};