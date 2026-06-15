<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('absensi_relawan', function (Blueprint $table) {
            // Ubah kolom status menjadi string supaya bisa menampung semua nilai
            $table->string('status', 20)->default('Izin')->change();
        });
    }

    public function down(): void
    {
        Schema::table('absensi_relawan', function (Blueprint $table) {
            // Kembalikan ke enum lama jika rollback
            $table->enum('status', ['Izin','Datang Terlambat','Alpa','Sakit','Lainnya','Cuti','Pulang Cepat'])->default('Izin')->change();
        });
    }
};
