<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pindah_golongan', function (Blueprint $table) {
            $table->string('nim', 30)->nullable()->after('id')->comment('Nomor Induk');
            $table->string('nama', 100)->nullable()->after('nim')->comment('Nama guru / murid');
        });
    }

    public function down(): void
    {
        Schema::table('pindah_golongan', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nama']);
        });
    }
};
