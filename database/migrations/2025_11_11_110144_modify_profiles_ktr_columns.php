<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyProfilesKtrColumns extends Migration
{
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            // ubah tipe menjadi string supaya bisa menyimpan 'KTR 1A' dsb.
            if (Schema::hasColumn('profiles', 'ktr')) {
                $table->string('ktr', 50)->nullable()->change();
            } else {
                $table->string('ktr', 50)->nullable();
            }

            if (Schema::hasColumn('profiles', 'ktr_tambahan')) {
                $table->string('ktr_tambahan', 50)->nullable()->change();
            } else {
                $table->string('ktr_tambahan', 50)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            // kembalikan ke integer kalau memang sebelumnya integer — hati-hati data
            // jika Anda ingin rollback, sesuaikan sesuai kebutuhan
            // contoh: $table->integer('ktr')->nullable()->change();
        });
    }
}
