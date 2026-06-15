<?php

namespace App\Services;

use App\Models\Skim;

class SkimTHPService
{
    /**
     * Ambil THP berdasarkan jabatan, status, dan masa kerja
     */
    public static function getTHP(string $jabatan, string $status, int $masaKerja): float
    {
        // Konversi bulan ke kategori
        $kategoriMasaKerja = $masaKerja < 24 ? '< 24 bulan' : '>= 24 bulan';

        $skim = Skim::whereRaw('LOWER(jabatan) = ?', [strtolower($jabatan)])
    ->whereRaw('LOWER(status) = ?', [strtolower($status)])
    ->where('masa_kerja', $kategoriMasaKerja)
    ->first();

        if ($skim) {
            return (float) $skim->thp;
        }

        return 0;
    }
}
