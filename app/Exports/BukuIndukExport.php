<?php

namespace App\Exports;

use App\Models\BukuInduk;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Contracts\Database\Query\Builder;

class BukuIndukExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnFormatting
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = BukuInduk::query();

        if (!empty($this->filters['murid'])) {
            $query->where('nim', $this->filters['murid']);
        }

        if (!empty($this->filters['unit'])) {
            $query->where('bimba_unit', $this->filters['unit']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('nim', 'asc');
    }

    public function headings(): array
    {
        return [
            // Group Header (Baris 1)
            [
                'BUKU INDUK MURID biMBA AIUEO','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',

                'MASA AKTIF (DHUAFA & BNF)','','','',

                'PAKET 72','','',

                'SUPPLY MODUL','',

                'JADWAL','',

                'PINDAH','','',

                'GARANSI 372','','',
            ],

            // Detail Header (Baris 2)
            [
                'BIMBA UNIT', 'NO CABANG', 'NIM', 'NAMA', 'TEMPAT LAHIR', 'TGL LAHIR', 'USIA', 
                'ORANGTUA', 'NO TELP/HP', 'ALAMAT MURID', 'TGL DAFTAR', 'TGL MASUK', 
                'LAMA BLJR', 'TAHAPAN', 'TGL TAHAPAN', 'KELAS', 'GOL', 'KD', 'SPP', 
                'PETUGAS TRIAL', 'GURU', 'STATUS', 'TGL KELUAR', 'KATEGORI KELUAR', 
                'ALASAN KELUAR', 'NO CAB MERGE', 'LEVEL', 'TGL LEVEL', 'JENIS KBM', 'INFO',

                'PERIODE','TGL MULAI','TGL AKHIR','ALERT',

                'TGL BAYAR','TGL SELESAI','ALERT 2',

                'ASAL MODUL','KETERANGAN',

                'KODE JADWAL','HARI/JAM',

                'STATUS PINDAH','TGL PINDAH','KE INTERVIO',

                'TGL DIBERIKAN SURAT GARANSI','NOTE GARANSI',
            ]
        ];
    }

    /**
     * 🔥 DATA - Format Tanggal Menjadi Y-m-d saja
     */
    public function map($item): array
    {
        $sppClean = $item->spp ? (int) str_replace(['.', 'Rp', ' '], '', $item->spp) : null;

        return [
            $item->bimba_unit,
            $item->no_cabang,
            $item->nim,
            $item->nama,
            $item->tmpt_lahir,
            $this->formatDate($item->tgl_lahir),
            $item->usia,
            $item->orangtua,
            $item->no_telp_hp,
            $item->alamat_murid,
            $this->formatDate($item->tgl_daftar),
            $this->formatDate($item->tgl_masuk),
            $item->lama_bljr,
            $item->tahap,
            $this->formatDate($item->tgl_tahapan),
            $item->kelas,
            $item->gol,
            $item->kd,
            $sppClean,
            $item->petugas_trial,
            $item->guru,
            $item->status,
            $this->formatDate($item->tgl_keluar),
            $item->kategori_keluar,
            $item->alasan_keluar,
            $item->no_cab_merge,
            $item->level,
            $this->formatDate($item->tgl_level),
            $item->jenis_kbm,
            $item->info,

            $item->periode,
            $this->formatDate($item->tgl_mulai),
            $this->formatDate($item->tgl_akhir),
            $item->alert,

            $this->formatDate($item->tgl_bayar),
            $this->formatDate($item->tgl_selesai),
            $item->alert2,

            $item->asal_modul,
            $item->keterangan_optional,

            $item->kode_jadwal,
            $item->hari_jam,

            $item->status_pindah,
            $this->formatDate($item->tanggal_pindah),
            $item->ke_bimba_intervio,

            $this->formatDate($item->tgl_pengajuan_garansi),
            $item->note_garansi,
        ];
    }

    /**
     * Format Tanggal menjadi Y-m-d (tanpa jam)
     */
    private function formatDate($date): ?string
    {
        if (!$date) return null;
        if ($date instanceof \Carbon\Carbon) {
            return $date->format('Y-m-d');
        }
        return \Carbon\Carbon::parse($date)->format('Y-m-d');
    }

    /**
     * Format Kolom Tanggal sebagai Date di Excel
     */
    public function columnFormats(): array
    {
        return [
            'F'  => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL LAHIR
            'K'  => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL DAFTAR
            'L'  => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL MASUK
            'O'  => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL TAHAPAN
            'W'  => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL KELUAR
            'AB' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL LEVEL
            'AG' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL MULAI
            'AH' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL AKHIR
            'AJ' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL BAYAR
            'AK' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL SELESAI
            'AR' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL PINDAH
            'AS' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // TGL GARANSI
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // ===== MERGE HEADER =====
                $sheet->mergeCells('A1:AD1');   // Buku Induk

                $sheet->mergeCells('AE1:AH1');   // Masa Aktif ✅ FIX

                $sheet->mergeCells('AI1:AK1');   // Paket 72

                $sheet->mergeCells('AL1:AM1');   // Supply

                $sheet->mergeCells('AN1:AO1');   // Jadwal

                $sheet->mergeCells('AP1:AR1');   // Pindah

                $sheet->mergeCells('AS1:AT1');   // Garansi

                // ===== STYLE HEADER =====
                $sheet->getStyle('A1:AT2')->getFont()->setBold(true)->setSize(11);

                $sheet->getStyle('A1:AT2')->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                // Background header
                $sheet->getStyle('A1:AD1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD9EAD3');
                    $sheet->getStyle('AE1:AH1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('ff7539');
                    $sheet->getStyle('AI1:AK1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('ff7539');
                    $sheet->getStyle('AP1:AR1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('ff7539');
                    $sheet->getStyle('AL1:AM1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD9EAD3');
                    $sheet->getStyle('AN1:AO1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD9EAD3');
                    $sheet->getStyle('AS1:AT1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('ff7539');

                // Border header
                $sheet->getStyle('A1:AT2')->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Freeze
                $sheet->freezePane('A3');

                // Auto width
                foreach (range('A', 'AR') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Tinggi row
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(22);
            }
        ];
    }
}