<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $t) {
            // Meta dari Form
            $t->timestamp('form_timestamp')->nullable()->after('id');  // "Timestamp"
            $t->string('email')->nullable()->after('form_timestamp');  // "Email Address"
            $t->string('sumber_pendaftaran')->nullable()->after('email'); // "Sumber Pendaftaran" (mentah)

            // Identitas murid
            $t->string('tempat_lahir')->nullable()->after('tgl_lahir'); // "Tempat Lahir"
            $t->string('jenis_kelamin', 20)->nullable()->after('usia'); // "Jenis Kelamin"
            $t->string('agama_murid', 50)->nullable()->after('jenis_kelamin'); // "Agama"

            // Alamat domisili
            $t->string('kode_pos', 10)->nullable()->after('alamat');
            $t->string('no_rumah')->nullable()->after('kode_pos');
            $t->string('rt', 10)->nullable()->after('no_rumah');
            $t->string('rw', 10)->nullable()->after('rt');
            $t->string('kelurahan')->nullable()->after('rw');
            $t->string('kecamatan')->nullable()->after('kelurahan');
            $t->string('kodya_kab')->nullable()->after('kecamatan');
            $t->string('provinsi')->nullable()->after('kodya_kab');

            // Data ayah
            $t->string('nama_ayah')->nullable()->after('orangtua');
            $t->string('agama_ayah', 50)->nullable();
            $t->string('pekerjaan_ayah')->nullable();
            $t->text('alamat_kantor_ayah')->nullable();
            $t->string('telepon_kantor_ayah')->nullable();
            $t->string('hp_ayah')->nullable();

            // Data ibu
            $t->string('nama_ibu')->nullable();
            $t->string('agama_ibu', 50)->nullable();
            $t->string('pekerjaan_ibu')->nullable();
            $t->text('alamat_kantor_ibu')->nullable();
            $t->string('telepon_kantor_ibu')->nullable();
            $t->string('hp_ibu')->nullable();

            // Info pendaftaran & jadwal
            $t->date('tanggal_masuk')->nullable();
            $t->decimal('biaya_pendaftaran', 15, 2)->nullable();
            $t->decimal('spp_bulanan', 15, 2)->nullable();
            $t->string('informasi_bimba')->nullable(); // "Informasi biMBA-AIUEO didapat dari"
            $t->string('hari')->nullable();
            $t->string('jam')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $t) {
            $t->dropColumn([
                'form_timestamp','email','sumber_pendaftaran',
                'tempat_lahir','jenis_kelamin','agama_murid',
                'kode_pos','no_rumah','rt','rw','kelurahan','kecamatan','kodya_kab','provinsi',
                'nama_ayah','agama_ayah','pekerjaan_ayah','alamat_kantor_ayah','telepon_kantor_ayah','hp_ayah',
                'nama_ibu','agama_ibu','pekerjaan_ibu','alamat_kantor_ibu','telepon_kantor_ibu','hp_ibu',
                'tanggal_masuk','biaya_pendaftaran','spp_bulanan','informasi_bimba','hari','jam',
            ]);
        });
    }
};
