<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPenerimaanToRegistrationsTable extends Migration
{
    public function up()
    {
        Schema::table('registrations', function (Blueprint $table) {
            // penerimaan / kwitansi
            if (!Schema::hasColumn('registrations', 'kwitansi')) {
                $table->string('kwitansi')->nullable()->after('spp');
            }
            if (!Schema::hasColumn('registrations', 'via')) {
                $table->string('via')->nullable()->after('kwitansi');
            }
            if (!Schema::hasColumn('registrations', 'bulan')) {
                $table->string('bulan')->nullable()->after('via');
            }
            if (!Schema::hasColumn('registrations', 'tahun')) {
                $table->integer('tahun')->nullable()->after('bulan');
            }
            if (!Schema::hasColumn('registrations', 'tanggal_penerimaan')) {
                $table->date('tanggal_penerimaan')->nullable()->after('tahun');
            }

            // nominal
            if (!Schema::hasColumn('registrations', 'daftar')) {
                $table->bigInteger('daftar')->nullable()->after('tanggal_penerimaan');
            }
            if (!Schema::hasColumn('registrations', 'voucher')) {
                $table->bigInteger('voucher')->nullable()->after('daftar');
            }
            if (!Schema::hasColumn('registrations', 'spp_rp')) {
                $table->bigInteger('spp_rp')->nullable()->after('voucher');
            }
            if (!Schema::hasColumn('registrations', 'spp_keterangan')) {
                $table->string('spp_keterangan')->nullable()->after('spp_rp');
            }
            if (!Schema::hasColumn('registrations', 'kaos')) {
                $table->bigInteger('kaos')->nullable()->after('spp_keterangan');
            }
            if (!Schema::hasColumn('registrations', 'kpk')) {
                $table->bigInteger('kpk')->nullable()->after('kaos');
            }
            if (!Schema::hasColumn('registrations', 'sertifikat')) {
                $table->bigInteger('sertifikat')->nullable()->after('kpk');
            }
            if (!Schema::hasColumn('registrations', 'stpb')) {
                $table->bigInteger('stpb')->nullable()->after('sertifikat');
            }
            if (!Schema::hasColumn('registrations', 'tas')) {
                $table->bigInteger('tas')->nullable()->after('stpb');
            }
            if (!Schema::hasColumn('registrations', 'event')) {
                $table->bigInteger('event')->nullable()->after('tas');
            }
            if (!Schema::hasColumn('registrations', 'lain_lain')) {
                $table->bigInteger('lain_lain')->nullable()->after('event');
            }
            if (!Schema::hasColumn('registrations', 'total')) {
                $table->bigInteger('total')->nullable()->after('lain_lain');
            }
        });
    }

    public function down()
    {
        Schema::table('registrations', function (Blueprint $table) {
            // jangan drop kalau ada risiko; tapi contoh:
            $cols = [
              'kwitansi','via','bulan','tahun','tanggal_penerimaan',
              'daftar','voucher','spp_rp','spp_keterangan',
              'kaos','kpk','sertifikat','stpb','tas','event','lain_lain','total'
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('registrations', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
}

