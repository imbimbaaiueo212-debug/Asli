<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_moduls', function (Blueprint $table) {
            $table->date('tanggal_order')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('order_moduls', function (Blueprint $table) {
            $table->dropColumn('tanggal_order');
        });
    }
};