<?php

namespace App\Imports;

use App\Models\PenyesuaianKtr;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenyesuaianKtrImport implements ToModel, WithHeadingRow
{
   public function model(array $row)
{
    return new PenyesuaianKtr([
        'jumlah_murid' => $row['jumlah_murid'] ?? $row['Jumlah Murid'] ?? null,
        'penyesuaian_ktr' => $row['penyesuaian_ktr'] ?? $row['Penyesuaian KTR'] ?? null,
    ]);
}
}
