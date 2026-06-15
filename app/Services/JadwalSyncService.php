<?php

namespace App\Services;

use App\Models\BukuInduk;
use App\Models\JadwalDetail;

class JadwalSyncService
{
    /**
     * Sinkronisasi jadwal untuk 1 murid (otomatis)
     */
    public static function syncMurid(BukuInduk $murid): void
    {
        // Hanya murid aktif / baru
        if (!in_array($murid->status, ['Aktif', 'Baru'])) {
            return;
        }

        if (empty($murid->kode_jadwal)) {
            return;
        }

        // =============================
        // 1. Ambil angka kode jadwal
        // =============================
        $kode = (int) preg_replace('/\D+/', '', (string) $murid->kode_jadwal);
        if ($kode === 0) {
            return;
        }

        // =============================
        // 2. Tentukan shift & hari
        // =============================
        $shift = null;
        $hariList = [];

        if ($kode >= 108 && $kode <= 116) {
            $shift = 'SRJ';
            $hariList = ['Senin', 'Rabu', 'Jumat'];
        } elseif ($kode >= 208 && $kode <= 211) {
            $shift = 'SKS';
            $hariList = ['Selasa', 'Kamis', 'Sabtu'];
        } elseif ($kode >= 308 && $kode <= 311) {
            $shift = 'S6';
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        } else {
            return;
        }

        // =============================
        // 3. Tentukan jam ke-
        // =============================
        $jamKe = match ($kode) {
            108, 208, 308 => 1,
            109, 209, 309 => 2,
            110, 210, 310 => 3,
            111, 211, 311 => 4,
            112 => 5,
            113 => 6,
            114 => 7,
            115 => 8,
            116 => 9,
            default => 1
        };

        // =============================
        // 4. Normalisasi guru
        // =============================
        $guruNama = trim((string) ($murid->guru ?? ''));
        if ($guruNama === '' || $guruNama === '-') {
            $guruNama = 'TANPA GURU';
        }

        // =============================
        // 5. Buat / Update JadwalDetail
        // =============================
        foreach ($hariList as $hari) {
            JadwalDetail::updateOrCreate(
                [
                    'murid_id' => $murid->id,
                    'hari'     => $hari,
                    'shift'    => $shift,
                    'jam_ke'   => $jamKe,
                ],
                [
                    'guru'        => $guruNama,
                    'kelas'       => $murid->kelas ?? '-',
                    'kode_jadwal' => $murid->kode_jadwal,
                    'jenis_kbm'   => $murid->jenis_kbm ?? '-',
                ]
            );
        }
    }
}
