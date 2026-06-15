<?php

namespace App\Exports;

use App\Models\Produk;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProdukExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    ShouldAutoSize
{
    protected Unit $unit;

    public function __construct(Unit $unit)
    {
        $this->unit = $unit;
    }

    public function query()
    {
        return Produk::withoutGlobalScopes()
            ->where('unit_id', $this->unit->id)
            ->orderBy('kode');
    }

    public function headings(): array
    {
        return [
            'kode',
            'kategori',
            'jenis',
            'label',
            'nama_produk',
            'satuan',
            'berat',
            'harga',
            'status',
            'isi',
            'pendataan',
        ];
    }

    public function map($produk): array
    {
        return [
            $produk->kode,
            $produk->kategori,
            $produk->jenis,
            $produk->label,
            $produk->nama_produk,
            $produk->satuan,

            // 🔥 ANGKA MURNI (kg) → Excel format yang urus tampilan
            ((int) $produk->berat) / 1000,   // 23 → 0.023

            // 🔥 ANGKA MURNI (rupiah)
            (int) $produk->harga,            // 6966

            $produk->status,
            $produk->isi,
            $produk->pendataan,
        ];
    }

    /**
     * INI KUNCI UTAMA
     * Format kolom agar tampil SAMA PERSIS seperti Excel Anda
     */
    public function columnFormats(): array
    {
        return [
            // Kolom G = berat → 0,052 Kg
            'G' => '0.000" Kg"',

            // Kolom H = harga → 6.966
            'H' => '#,##0',
        ];
    }
}
