<?php

namespace App\Exports;

use App\Models\AbsensiVolunteer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiVolunteerExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = AbsensiVolunteer::query()->orderBy('tanggal', 'desc');

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
            'ID Fingerprint',         // ← Kolom baru, wajib untuk match updateOrCreate di import
            'NIK',
            'Nama Relawan',
            'Posisi',
            'Unit',
            'No Cabang',
            'Tanggal',
            'On Duty',
            'Off Duty',
            'Jam Masuk',
            'Jam Keluar',
            'Status',
            'Keterangan',
            'Jam Lembur (menit)',
            'Absen Pulang',
        ];
    }

    public function map($row): array
    {
        $lemburMenit = (int) ($row->jam_lembur ?? 0);
        $jamLembur   = floor($lemburMenit / 60);
        $menitLembur = $lemburMenit % 60;
        $lemburText  = $jamLembur . 'j ' . $menitLembur . 'm';

        $absenPulang = $row->jam_keluar ? 'Sudah' : 'Belum';

        return [
            $row->id_fingerprint ?? '',   // ← Nilai asli dari database
            $row->nik ?? '',
            $row->nama_relawan ?? '',
            $row->posisi ?? 'Relawan',
            $row->bimba_unit ?? '',
            $row->no_cabang ?? '',
            $row->tanggal ? $row->tanggal->format('d/m/Y') : '',
            $row->onduty ?? '',
            $row->offduty ?? '',
            $row->jam_masuk ?? '',
            $row->jam_keluar ?? '',
            $row->status ?? 'Hadir',
            $row->keterangan ?? '',
            $lemburText,
            $absenPulang,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD4E9CE'],
                ],
            ],
            'A' => ['alignment' => ['horizontal' => 'center']], // ID Fingerprint
            'G' => ['alignment' => ['horizontal' => 'center']], // Tanggal
            'H' => ['alignment' => ['horizontal' => 'center']], // On Duty
            'I' => ['alignment' => ['horizontal' => 'center']], // Off Duty
            'J' => ['alignment' => ['horizontal' => 'center']], // Jam Masuk
            'K' => ['alignment' => ['horizontal' => 'center']], // Jam Keluar
            'N' => ['alignment' => ['horizontal' => 'center']], // Lembur
        ];
    }
}