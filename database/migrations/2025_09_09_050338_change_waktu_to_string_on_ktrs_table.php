<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ktrs', function (Blueprint $table) {
            $table->string('waktu', 20)->change(); // ubah dari date ke string
        });
    }

    public function down(): void
    {
        Schema::table('ktrs', function (Blueprint $table) {
            $table->date('waktu')->change(); // rollback ke date
        });
    }
};

