<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            $table->string('masa_kerja')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('imbalan_rekaps', function (Blueprint $table) {
            $table->integer('masa_kerja')->nullable()->change();
        });
    }
};