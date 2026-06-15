<?php

namespace App\Imports;

use App\Models\DurasiKegiatan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DurasiKegiatanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new DurasiKegiatan([
            'waktu_mgg' => $row['waktu_mgg'],
            'waktu_bln' => $row['waktu_bln'],
        ]);
    }
}
