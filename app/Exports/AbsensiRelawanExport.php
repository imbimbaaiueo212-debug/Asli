<?php

namespace App\Exports;

use App\Models\AbsensiRelawan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiRelawanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = AbsensiRelawan::query()->orderBy('tanggal', 'desc');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('tanggal', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('tanggal', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['nik'])) {
            $query->where('nik', $this->filters['nik']);
        }
        if (!empty($this->filters['bimba_unit'])) {
            $query->where('bimba_unit', $this->filters['bimba_unit']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama Relawaan',
            'Posisi',
            'Status Relawaan',
            'Departemen',
            'Bimba Unit',
            'No Cabang',
            'Tanggal',
            'Absensi',
            'Keterangan',
            'Status',
        ];
    }

    public function map($row): array
    {
        // Fallback aman untuk tanggal (jika casting belum aktif)
        $tanggalFormatted = $row->tanggal 
            ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') 
            : '';

        return [
            $row->nik ?? '',
            $row->nama_relawaan ?? '',
            $row->posisi ?? '',
            $row->status_relawaan ?? '',
            $row->departemen ?? '',
            $row->bimba_unit ?? '',
            $row->no_cabang ?? '',
            $tanggalFormatted,
            $row->absensi ?? '',
            $row->keterangan ?? '',
            $row->status ?? 'Hadir',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD4E9CE'],
                ],
            ],
            'H' => ['alignment' => ['horizontal' => 'center']], // Tanggal
            'K' => ['alignment' => ['horizontal' => 'center']], // Status
            'G' => ['alignment' => ['horizontal' => 'center']], // No Cabang
        ];
    }
}