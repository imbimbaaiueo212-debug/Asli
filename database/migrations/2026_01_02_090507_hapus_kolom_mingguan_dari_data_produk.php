<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('data_produk', function (Blueprint $table) {
            // Hapus semua kolom mingguan (1 sampai 5)
            for ($i = 1; $i <= 5; $i++) {
                $table->dropColumn([
                    "sld_awal{$i}",
                    "terima{$i}",
                    "pakai{$i}",
                    "sld_akhir{$i}",
                    "status{$i}",
                ]);
            }
        });
    }

    public function down()
    {
        Schema::table('data_produk', function (Blueprint $table) {
            for ($i = 1; $i <= 5; $i++) {
                $table->integer("sld_awal{$i}")->default(0);
                $table->integer("terima{$i}")->default(0);
                $table->integer("pakai{$i}")->default(0);
                $table->integer("sld_akhir{$i}")->default(0);
                $table->string("status{$i}")->default('OK');
            }
        });
    }
};