<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheel_winners', function (Blueprint $table) {
            // Tambah kolom bimba_unit (nullable (boleh kosong)
            $table->string('bimba_unit')->nullable()->after('student_id');

            // Tambah nomor voucher / kode voucher unik
            // Pilih salah satu sesuai kebutuhanmu:

            // Jika nomor voucher berurutan (001, 002, dst)
            $table->string('no_cabang')->unique()->nullable()->after('bimba_unit');

            // ATAU kalau mau auto-increment integer
            // $table->unsignedBigInteger('voucher_number')->unique()->nullable()->after('bimba_unit');
        });
    }

    public function down(): void
    {
        Schema::table('wheel_winners', function (Blueprint $table) {
            $table->dropColumn(['bimba_unit', 'no_cabang']);
        });
    }
};