<?php

namespace App\Imports;

use App\Models\Huma;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class HumasImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    public function model(array $row)
    {
        $nih  = trim($row['nih'] ?? '');
        $nama = trim($row['nama_humas'] ?? $row['nama'] ?? '');

        if (empty($nih) || empty($nama)) {
            return null;
        }

        // Cek duplikat tanpa global scope
        $huma = Huma::withoutGlobalScope(\App\Models\Scopes\UnitScope::class)
                    ->where('nih', $nih)
                    ->first();

        $data = [
            'tgl_reg'    => $this->parseExcelDate($row['tgl_reg'] ?? now()->toDateString()),
            'nih'        => $nih,
            'nama'       => $nama,
            'pekerjaan'  => $row['pekerjaan'] ?? null,
            'no_telp'    => $row['no_telp'] ?? null,
            'bimba_unit' => $row['unit'] ?? null,
            'no_cabang'  => $row['cabang'] ?? null,
            'status'     => $row['status'] ?? 'baru',
            'alamat'     => $row['alamat'] ?? null,
        ];

        if ($huma) {
            $huma->update($data);
            return null;
        }

        return new Huma($data);
    }

    private function parseExcelDate($value)
    {
        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value))->toDateString();
            }
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return now()->toDateString();
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
