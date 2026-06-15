<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PenerimaanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $penerimaan;

    public function __construct(Collection $penerimaan)
    {
        $this->penerimaan = $penerimaan;
    }

    public function collection()
    {
        return $this->penerimaan;
    }

    public function headings(): array
    {
        return [
            'KWITANSI',
            'VIA',
            'BULAN',
            'TAHUN',
            'tanggal',
            'NIM',
            'NAMA MURID',
            'KELAS',
            'GOL',
            'KD',
            'STATUS',
            'GURU',
            'biMBA Unit',
            'No Cabang',
            'DAFTAR',
            'VOUCHER',
            'nilai_spp',
            'KAOS',
            'ukuran_kaos_pendek',
            'kaos_lengan_panjang',
            'ukuran_kaos_panjang',
            'KPK',
            'TAS',
            'RBAS',
            'BCABS01',
            'BCABS02',
            'SERTIFIKAT',
            'STPB',
            'EVENT',
            'LAIN-LAIN',
            'TOTAL',
        ];
    }

    public function map($item): array
    {
        return [
            $item->kwitansi ?? '',
            $item->via ?? '',
            $item->bulan ?? '',
            $item->tahun ?? '',
            $item->tanggal ? Carbon::parse($item->tanggal)->format('d-m-Y') : '',
            $item->nim ?? '',
            $item->nama_murid ?? '',
            $item->kelas ?? '',
            $item->gol ?? '',
            $item->kd ?? '',
            $item->status ?? '',
            $item->guru ?? '',
            $item->bimba_unit ?? '',
            $item->no_cabang ?? '',
            $item->daftar ?? 0,
            $item->voucher ?? 0,
            $item->spp ?? $item->nilai_spp ?? 0, // pakai spp jika nilai_spp kosong
            $item->kaos ?? 0,
            $item->ukuran_kaos_pendek ?? '',
            $item->kaos_lengan_panjang ?? 0,
            $item->ukuran_kaos_panjang ?? '',
            $item->kpk ?? 0,
            $item->tas ?? 0,
            $item->RBAS ?? 0,
            $item->BCABS01 ?? 0,
            $item->BCABS02 ?? 0,
            $item->sertifikat ?? 0,
            $item->stpb ?? 0,
            $item->event ?? 0,
            $item->lain_lain ?? 0,
            $item->total ?? 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header style (rapi seperti tabel kamu)
            1 => [
                'font' => [
                    'bold'  => true,
                    'size'  => 12,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0D6EFD'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],

            // Format angka ribuan untuk kolom nominal (DAFTAR sampai TOTAL)
            'O'  => ['numberFormat' => ['formatCode' => '#,##0']], // DAFTAR
            'P'  => ['numberFormat' => ['formatCode' => '#,##0']], // VOUCHER
            'Q'  => ['numberFormat' => ['formatCode' => '#,##0']], // nilai_spp
            'R'  => ['numberFormat' => ['formatCode' => '#,##0']], // KAOS
            'T'  => ['numberFormat' => ['formatCode' => '#,##0']], // kaos_lengan_panjang
            'V'  => ['numberFormat' => ['formatCode' => '#,##0']], // KPK
            'W'  => ['numberFormat' => ['formatCode' => '#,##0']], // TAS
            'X'  => ['numberFormat' => ['formatCode' => '#,##0']], // RBAS
            'Y'  => ['numberFormat' => ['formatCode' => '#,##0']], // BCABS01
            'Z'  => ['numberFormat' => ['formatCode' => '#,##0']], // BCABS02
            'AA' => ['numberFormat' => ['formatCode' => '#,##0']], // SERTIFIKAT
            'AB' => ['numberFormat' => ['formatCode' => '#,##0']], // STPB
            'AC' => ['numberFormat' => ['formatCode' => '#,##0']], // EVENT
            'AD' => ['numberFormat' => ['formatCode' => '#,##0']], // LAIN-LAIN
            'AE' => ['numberFormat' => ['formatCode' => '#,##0']], // TOTAL

            // Format tanggal (kolom E = tanggal)
            'E' => ['numberFormat' => ['formatCode' => 'dd-mm-yyyy']],
        ];
    }

    public function title(): string
    {
        return 'Data Penerimaan';
    }
}