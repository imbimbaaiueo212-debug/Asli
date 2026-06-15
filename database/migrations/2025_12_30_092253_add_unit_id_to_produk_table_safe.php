<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('produk', function (Blueprint $table) {
            // 1. Tambah kolom unit_id dulu sebagai nullable
            $table->foreignId('unit_id')->nullable()->after('pendataan');
        });

        // 2. Isi nilai default untuk data yang sudah ada
        // Misalnya: isi dengan unit pertama yang ada, atau unit dengan ID 1
        // Ganti sesuai kebutuhan kamu

        $defaultUnitId = DB::table('units')->first()?->id;

        if ($defaultUnitId) {
            DB::table('produk')->update(['unit_id' => $defaultUnitId]);
        } else {
            // Jika tabel units masih kosong, buat satu dulu atau set null sementara
            DB::table('produk')->update(['unit_id' => null]);
        }

        // 3. Ubah kolom menjadi NOT NULL (opsional, tergantung kebutuhan)
        Schema::table('produk', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
        });

        // 4. Baru sekarang tambahkan foreign key constraint
        Schema::table('produk', function (Blueprint $table) {
            $table->foreign('unit_id')
                  ->references('id')
                  ->on('units')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};