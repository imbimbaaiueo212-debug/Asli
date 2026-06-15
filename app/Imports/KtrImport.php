<?php

namespace App\Imports;

use App\Models\Ktr;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class KtrImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
{
    return new Ktr([
        'waktu'    => $row['waktu_mgg'],            // simpan langsung sebagai string
        'kategori' => $row['kategori'],
        'jumlah'   => (int) str_replace('.', '', $row['rp']), // hilangkan titik ribuan
    ]);
}

}
