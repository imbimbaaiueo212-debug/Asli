<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('absensi_relawan', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('id'); 
            // bisa ubah posisi 'after' sesuai kebutuhan, misalnya setelah 'nama_relawaan'
        });
    }

    public function down(): void
    {
        Schema::table('absensi_relawan', function (Blueprint $table) {
            $table->dropColumn('nik');
        });
    }
};
