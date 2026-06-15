<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_relawan', function (Blueprint $table) {
            // === Kolom yang mungkin belum ada ===

            // Ganti string nama_relawaan jadi foreign key ke users/profiles (REKOMENDASI UTAMA)
            // Tapi kalau masih mau pakai string, kita biarkan dulu, tambah foreign nanti kalau mau
            // $table->unsignedBigInteger('relawan_id')->nullable()->after('id');
            // $table->foreign('relawan_id')->references('id')->on('users')->onDelete('cascade');

            // Jam masuk & keluar
            $table->time('jam_masuk')->nullable()->after('tanggal');
            $table->time('jam_keluar')->nullable()->after('jam_masuk');

            // Foto bukti absen (upload)
            $table->string('photo')->nullable()->after('keterangan');

            // On duty / off duty (apakah terjadwal hari itu)
            $table->boolean('onduty')->default(true)->after('photo');
            $table->boolean('offduty')->default(false)->after('onduty');

            // Jam lembur (dalam menit)
            $table->integer('jam_lembur')->default(0)->after('offduty');

            // Lokasi / koordinat (opsional, kalau pakai GPS)
            $table->string('latitude')->nullable()->after('jam_lembur');
            $table->string('longitude')->nullable()->after('latitude');

            // IP address atau device info (opsional)
            $table->string('ip_address')->nullable()->after('longitude');

            // Status lama bisa dihapus atau digabung, tapi kita biarkan dulu kalau masih dipakai
            // $table->dropColumn('status'); // kalau mau hapus yang lama
        });
    }

    public function down(): void
    {
        Schema::table('absensi_relawan', function (Blueprint $table) {
            $table->dropColumn([
                'jam_masuk', 'jam_keluar', 'photo', 'onduty', 'offduty',
                'jam_lembur', 'latitude', 'longitude', 'ip_address'
            ]);
            // $table->dropColumn('relawan_id'); // jika ditambahkan
        });
    }
};