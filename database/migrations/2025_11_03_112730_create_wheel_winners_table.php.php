<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWheelWinnersTable extends Migration
{
    public function up()
    {
        Schema::create('wheel_winners', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('voucher')->nullable();
            $table->unsignedSmallInteger('voucher_index')->nullable();
            $table->string('row_hash')->nullable()->index(); // md5 row JSON atau timestamp
            $table->timestamp('won_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wheel_winners');
    }
}

