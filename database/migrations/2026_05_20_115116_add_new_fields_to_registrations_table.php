<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('level')->nullable()->after('tahap');
            $table->date('tgl_level')->nullable()->after('level');
            
            $table->string('no_telp_hp')->nullable()->after('tgl_level');
            $table->text('alamat_murid')->nullable()->after('no_telp_hp');
            $table->string('asal_modul')->nullable()->after('alamat_murid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'level',
                'tgl_level',
                'no_telp_hp',
                'alamat_murid',
                'asal_modul'
            ]);
        });
    }
};