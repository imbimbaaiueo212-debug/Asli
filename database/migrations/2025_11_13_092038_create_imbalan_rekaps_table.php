<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImbalanRekapsTable extends Migration
{
    public function up()
    {
        Schema::create('imbalan_rekaps', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->index();
            $table->string('posisi')->nullable()->index();
            $table->string('status')->nullable();
            $table->string('departemen')->nullable();
            $table->integer('masa_kerja')->nullable();
            $table->string('waktu_mgg')->nullable()->index();
            $table->string('waktu_bln')->nullable();
            $table->decimal('durasi_kerja', 8, 2)->nullable();
            $table->decimal('persen', 5, 2)->nullable();
            $table->string('ktr')->nullable();

            $table->decimal('imbalan_pokok', 14, 2)->default(0);
            $table->decimal('imbalan_lainnya', 14, 2)->default(0);
            $table->decimal('total_imbalan', 14, 2)->default(0);

            $table->decimal('insentif_mentor', 14, 2)->default(0);
            $table->text('keterangan_insentif')->nullable();
            $table->decimal('tambahan_transport', 14, 2)->default(0);
            $table->integer('at_hari')->nullable();

            $table->decimal('kekurangan', 14, 2)->default(0);
            $table->integer('bulan_kekurangan')->nullable();
            $table->text('keterangan_kekurangan')->nullable();

            $table->decimal('kelebihan', 14, 2)->default(0);
            $table->integer('bulan_kelebihan')->nullable();
            $table->decimal('cicilan', 14, 2)->default(0);
            $table->text('keterangan_cicilan')->nullable();
            $table->decimal('total_kelebihan', 14, 2)->default(0);

            $table->integer('jumlah_murid')->default(0);
            $table->decimal('jumlah_spp', 14, 2)->default(0);
            $table->decimal('kekurangan_spp', 14, 2)->default(0);
            $table->decimal('kelebihan_spp', 14, 2)->default(0);

            $table->decimal('jumlah_bagi_hasil', 14, 2)->default(0);
            $table->text('keterangan_bagi_hasil')->nullable();

            $table->decimal('yang_dibayarkan', 14, 2)->default(0);
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('imbalan_rekaps');
    }
}
