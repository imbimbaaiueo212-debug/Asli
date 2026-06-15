<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE: membutuhkan doctrine/dbal untuk change()
        // composer require doctrine/dbal
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('rb')->nullable()->change();
            $table->string('rb_tambahan')->nullable()->change();
            $table->string('ktr')->nullable()->change();
            $table->string('ktr_tambahan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->integer('rb')->nullable()->change();
            $table->integer('rb_tambahan')->nullable()->change();
            $table->integer('ktr')->nullable()->change();
            $table->integer('ktr_tambahan')->nullable()->change();
        });
    }
};
