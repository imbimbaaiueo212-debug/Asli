<?php

namespace App\Imports;

use App\Models\HargaSaptataruna;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class HargaSaptatarunaImport implements ToCollection
{
    /**
     * Fungsi helper untuk parsing harga dari Excel
     */
    private function parsePrice($value)
    {
        if ($value === null) return null;

        // Hilangkan Rp, titik, koma
        $clean = str_replace(['Rp', '.', ','], '', $value);

        // Kalau tetap '-' atau kosong, kembalikan null
        return ($clean === '-' || $clean === '') ? null : (int)$clean;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Convert inner Collection ke array supaya lebih aman
            $row = $row->toArray();

            // Skip baris header/kategori
            $firstCell = $row[0] ?? null;
            if ($firstCell === null || in_array(strtoupper($firstCell), [
                'BIAYA PENDAFTARAN',
                'PENJUALAN',
                'BIAYA SPP PER BULAN',
                'KODE'
            ])) {
                continue;
            }

            // Simpan ke database
            HargaSaptataruna::create([
                'kategori'    => $row[0] ?? null,
                'kode'        => $row[1] ?? null,
                'nama'        => $row[2] ?? null,
                'duafa'       => $this->parsePrice($row[3] ?? null),
                'promo_2019'  => $this->parsePrice($row[4] ?? null),
                'daftar_ulang'=> $this->parsePrice($row[5] ?? null),
                'spesial'     => $this->parsePrice($row[6] ?? null),
                'umum1'       => $this->parsePrice($row[7] ?? null),
                'umum2'       => $this->parsePrice($row[8] ?? null),
                'a'           => $this->parsePrice($row[9] ?? null),
                'b'           => $this->parsePrice($row[10] ?? null),
                'c'           => $this->parsePrice($row[11] ?? null),
                'd'           => $this->parsePrice($row[12] ?? null),
                'e'           => $this->parsePrice($row[13] ?? null),
                'f'           => $this->parsePrice($row[14] ?? null),
            ]);
        }
    }
}
