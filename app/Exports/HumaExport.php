<?php

namespace App\Exports;

use App\Models\Huma; // Ganti dengan nama model kamu (asumsi model Humas)
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HumaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Huma::query()->orderBy('tgl_reg', 'desc');

        // Filter nama orang tua
        if (!empty($this->filters['nama'])) {
            $query->where('nama', $this->filters['nama']);
        }

        // Filter unit
        if (!empty($this->filters['unit'])) {
            $query->where('bimba_unit', $this->filters['unit']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Tgl Reg',
            'NIH',
            'Nama Humas',
            'Pekerjaan',
            'No. Telp',
            'Unit',
            'Cabang',
        ];
    }

    public function map($humas): array
    {
        return [
            $humas->tgl_reg ? $humas->tgl_reg->format('d-m-Y') : '-',
            $humas->nih ?? '-',
            $humas->nama ?? '-',
            $humas->pekerjaan ?? '-',
            $humas->no_telp ?? '-',
            $humas->bimba_unit ?? '-',
            $humas->no_cabang ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header (baris 1)
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0D6EFD'], // biru primary
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],

            // Freeze header
            'A2' => ['freezePane' => 'A2'],

            // Kolom tanggal rata tengah
            'A:A' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }
}