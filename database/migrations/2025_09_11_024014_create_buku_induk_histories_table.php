<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buku_induk_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buku_induk_id')->nullable();
            $table->string('action'); // create, update, import, delete
            $table->string('user')->nullable();
            $table->json('data'); // snapshot data
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_induk_histories');
    }
};

