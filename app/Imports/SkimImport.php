<?php

namespace App\Imports;

use App\Models\Skim;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SkimImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Bersihkan angka: hapus titik dan koma, konversi ke float
        $tunj_pokok   = isset($row['tunj_pokok']) ? (float) str_replace(['.', ','], '', $row['tunj_pokok']) : 0;
        $harian       = isset($row['harian']) ? (float) str_replace(['.', ','], '', $row['harian']) : 0;
        $fungsional   = isset($row['fungsional']) ? (float) str_replace(['.', ','], '', $row['fungsional']) : 0;
        $kesehatan    = isset($row['kesehatan']) ? (float) str_replace(['.', ','], '', $row['kesehatan']) : 0;
        $tunj_khusus  = isset($row['tunj_khusus']) ? (float) str_replace(['.', ','], '', $row['tunj_khusus']) : 0;

        // Hitung THP
        $thp = $tunj_pokok + $harian + $fungsional + $kesehatan;

        // Simpan masa kerja langsung dari Excel, tanpa konversi
        $masaKerja = $row['masa_kerja'] ?? '';

        return new Skim([
            'jabatan'       => $row['jabatan'] ?? '',
            'masa_kerja'    => $masaKerja,
            'status'        => $row['status'] ?? '',
            'tunj_pokok'    => $tunj_pokok,
            'harian'        => $harian,
            'fungsional'    => $fungsional,
            'kesehatan'     => $kesehatan,
            'tunj_khusus'   => $tunj_khusus,
            'thp'           => $thp,
        ]);
    }
}
