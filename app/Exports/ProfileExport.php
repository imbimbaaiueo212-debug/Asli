<?php

namespace App\Exports;

use App\Models\Profile;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfileExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Profile::query()
            ->orderBy('nama');

        // Filter dari halaman index (jika ada)
        if (!empty($this->filters['unit'])) {
            $query->where('bimba_unit', $this->filters['unit']);
        }

        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            
            'NIK',
            'Nama',
            'Jabatan',
            'Status Karyawan',
            'Departemen',
            'Bimba Unit',
            'No Cabang',
            'Tgl Masuk',
            'Masa Kerja',
            'Jumlah Murid MBA',
            'Jumlah Murid ENG',
            'Total Murid',
            'Jumlah Murid Jadwal',
            'Jumlah Rombim',
            'RB',
            'RB Tambahan',
            'KTR',
            'KTR Tambahan',
            'RP',
            'Jenis Mutasi',
            'Tgl Mutasi Jabatan',
            'Masa Kerja Jabatan',
            'Tgl Lahir',
            'Usia',
            'No Telp',
            'Email',
            'No Rekening',
            'Bank',
            'Atas Nama',
            'Mentor Magang',
            'Periode',
            'Tgl Selesai Magang',
            'Ukuran',
            'Status Lain',
            'Keterangan',
            'Created At',
            'Updated At',
        ];
    }

    public function map($profile): array
    {
        return [
    
            
            $profile->nik ?? '-',
            $profile->nama ?? '-',
            $profile->jabatan ?? '-',
            $profile->status_karyawan ?? '-',
            $profile->departemen ?? '-',
            $profile->bimba_unit ?? '-',
            $profile->no_cabang ?? '-',
            $profile->tgl_masuk ? $profile->tgl_masuk->format('d-m-Y') : '-',
            $profile->masa_kerja ?? '-',
            $profile->jumlah_murid_mba ?? 0,
            $profile->jumlah_murid_eng ?? 0,
            $profile->total_murid ?? 0,
            $profile->jumlah_murid_jadwal ?? 0,
            $profile->jumlah_rombim ?? 0,
            $profile->rb ?? '-',
            $profile->rb_tambahan ?? '-',
            $profile->ktr ?? '-',
            $profile->ktr_tambahan ?? '-',
            $profile->rp ? number_format($profile->rp, 0, ',', '.') : '-',
            $profile->jenis_mutasi ?? '-',
            $profile->tgl_mutasi_jabatan ? $profile->tgl_mutasi_jabatan->format('d-m-Y') : '-',
            $profile->masa_kerja_jabatan ?? '-',
            $profile->tgl_lahir ? $profile->tgl_lahir->format('d-m-Y') : '-',
            $profile->usia ?? '-',
            $profile->no_telp ?? '-',
            $profile->email ?? '-',
            $profile->no_rekening ?? '-',
            $profile->bank ?? '-',
            $profile->atas_nama ?? '-',
            $profile->mentor_magang ?? '-',
            $profile->periode ?? '-',
            $profile->tgl_selesai_magang ? $profile->tgl_selesai_magang->format('d-m-Y') : '-',
            $profile->ukuran ?? '-',
            $profile->status_lain ?? '-',
            $profile->keterangan ?? '-',
            $profile->created_at ? $profile->created_at->format('d-m-Y H:i') : '-',
            $profile->updated_at ? $profile->updated_at->format('d-m-Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header baris pertama
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0D6EFD'], // biru primary
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],

            // Kolom angka (jumlah murid, RP, rombim, masa kerja, usia) → rata kanan
            'K:AA' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],

            // Kolom tanggal → format tanggal
            'I:I'  => ['numberFormat' => ['formatCode' => 'dd-mm-yyyy']],
            'V:V'  => ['numberFormat' => ['formatCode' => 'dd-mm-yyyy']],
            'W:W'  => ['numberFormat' => ['formatCode' => 'dd-mm-yyyy']],
            'AH:AH' => ['numberFormat' => ['formatCode' => 'dd-mm-yyyy hh:mm']],

            // Freeze header
            'A2' => ['freezePane' => 'A2'],
        ];
    }
}