<?php

namespace App\Exports;

use App\Models\VoucherLama;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VoucherLamaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = VoucherLama::query()
            ->with(['histori' => function ($q) {
                $q->latest('tanggal_pemakaian'); // ambil pemakaian terbaru
            }])
            ->orderBy('tanggal', 'desc');

        // Filter sama seperti di index
        if (!empty($this->filters['nama_murid'])) {
            $nama = trim($this->filters['nama_murid']);
            $query->where(function ($q) use ($nama) {
                $q->whereRaw('LOWER(TRIM(nama_murid)) LIKE ?', ["%{$nama}%"])
                  ->orWhereRaw('LOWER(TRIM(nama_murid_baru)) LIKE ?', ["%{$nama}%"]);
            });
        }

        if (!empty($this->filters['tanggal_dari'])) {
            $query->whereDate('tanggal', '>=', $this->filters['tanggal_dari']);
        }

        if (!empty($this->filters['tanggal_sampai'])) {
            $query->whereDate('tanggal', '<=', $this->filters['tanggal_sampai']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'voucher',
            'tanggal',
            'status',
            'nim',
            'nama_murid',
            'orangtua',
            'telp_hp',
            'nim_murid_baru',
            'nama_murid_baru',
            'orangtua_murid_baru',
            'telp_hp_murid_baru',
            'tanggal_pemakaian',
            'jumlah_voucher',
            'bimba_unit',
            'no_cabang',
            'no_voucher',           // ← ditambahkan
            'tanggal_penyerahan',   // ← ditambahkan
        ];
    }

    public function map($voucher): array
{
    // Helper function untuk format tanggal aman
    $formatTanggal = function ($tanggal) {
        if (empty($tanggal)) return '-';
        try {
            return Carbon::parse($tanggal)->format('d-m-Y');
        } catch (\Exception $e) {
            return (string) $tanggal; // fallback jika gagal parse
        }
    };

    // Tanggal spin / tanggal umum
    $tanggal = $formatTanggal($voucher->tanggal);

    // Status
    $status = $voucher->status ?? '';
    if ($status === 'Digunakan' || $voucher->jumlah_voucher <= 0) {
        $statusLabel = 'Digunakan';
    } elseif ($status === 'pemakaian') {
        $statusLabel = 'Dalam Pemakaian';
    } elseif ($status === 'penyerahan' || $voucher->tanggal_penyerahan) {
        $statusLabel = 'Penyerahan';
    } else {
        $statusLabel = 'Belum Diserahkan';
    }

    // Tanggal pemakaian terakhir dari histori
    $tanggalPemakaian = '-';
    if ($voucher->histori->isNotEmpty()) {
        $historiTerbaru = $voucher->histori->first();
        $tanggalPemakaian = $formatTanggal($historiTerbaru->tanggal_pemakaian);
    }

    // Kolom tambahan
    $noVoucher         = $voucher->no_voucher ?? '-';
    $tanggalPenyerahan = $formatTanggal($voucher->tanggal_penyerahan);

    return [
        $voucher->voucher ?? '-',                    // voucher
        $tanggal,                                    // tanggal
        $statusLabel,                                // status
        $voucher->nim ?? '-',                        // nim
        $voucher->nama_murid ?? '-',                 // nama_murid
        $voucher->orangtua ?? '-',                   // orangtua
        $voucher->telp_hp ?? '-',                    // telp_hp
        $voucher->nim_murid_baru ?? '-',             // nim_murid_baru
        $voucher->nama_murid_baru ?? '-',            // nama_murid_baru
        $voucher->orangtua_murid_baru ?? '-',        // orangtua_murid_baru
        $voucher->telp_hp_murid_baru ?? '-',         // telp_hp_murid_baru
        $tanggalPemakaian,                           // tanggal_pemakaian
        $voucher->jumlah_voucher ?? 0,               // jumlah_voucher
        $voucher->bimba_unit ?? '-',                 // bimba_unit
        $voucher->no_cabang  ?? '-',                 // no_cabang
        $noVoucher,                                  // no_voucher
        $tanggalPenyerahan,                          // tanggal_penyerahan
    ];
}

    public function styles(Worksheet $sheet)
    {
        return [
            // Header (baris pertama)
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0D6EFD'], // warna biru primary
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],

            // Kolom jumlah_voucher → rata kanan
            'M:M' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],

            // Kolom bimba_unit & no_cabang → rata tengah (opsional, sesuai selera)
            'N:N' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'O:O' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],

            // Freeze header row
            'A2' => ['freezePane' => 'A2'],
        ];
    }
}