<?php

namespace App\Imports;

use App\Models\OrderModul;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrderModulImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new OrderModul([
            'kode1' => $row['kode1'] ?? null,
            'jml1'  => $this->toInt($row['jml1']),
            'hrg1'  => $this->toInt($row['hrg1']),
            'sts1'  => $row['sts1'] ?? null,

            'kode2' => $row['kode2'] ?? null,
            'jml2'  => $this->toInt($row['jml2']),
            'hrg2'  => $this->toInt($row['hrg2']),
            'sts2'  => $row['sts2'] ?? null,

            'kode3' => $row['kode3'] ?? null,
            'jml3'  => $this->toInt($row['jml3']),
            'hrg3'  => $this->toInt($row['hrg3']),
            'sts3'  => $row['sts3'] ?? null,

            'kode4' => $row['kode4'] ?? null,
            'jml4'  => $this->toInt($row['jml4']),
            'hrg4'  => $this->toInt($row['hrg4']),
            'sts4'  => $row['sts4'] ?? null,

            'kode5' => $row['kode5'] ?? null,
            'jml5'  => $this->toInt($row['jml5']),
            'hrg5'  => $this->toInt($row['hrg5']),
            'sts5'  => $row['sts5'] ?? null,
        ]);
    }

    /**
     * Helper: ubah nilai kosong menjadi 0 dan pastikan integer.
     */
    private function toInt($value)
    {
        return is_numeric($value) ? (int)$value : 0;
    }
}
