<?php

namespace App\Imports;

use App\Models\Produk;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProdukImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $user;
    private $unit;

    private int $inserted = 0;
    private int $updated  = 0;

    /**
     * Unit WAJIB dikirim dari controller
     */
    public function __construct($user, Unit $unit)
    {
        $this->user = $user;
        $this->unit = $unit;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            // ================= VALIDASI MINIMAL =================
            if (empty($row['kode']) || empty($row['nama_produk'])) {
                continue;
            }

            // ================= UPDATE / CREATE =================
            $produk = Produk::updateOrCreate(
                [
                    // 🔑 KUNCI UNIK
                    'kode'    => trim($row['kode']),
                    'unit_id' => $this->unit->id,
                ],
                [
                    'kategori'    => $row['kategori'] ?? null,
                    'jenis'       => $row['jenis'] ?? null,
                    'label'       => $row['label'] ?? null,
                    'nama_produk' => trim($row['nama_produk']),
                    'satuan'      => $row['satuan'] ?? 'Pcs',
                    'berat'       => $this->parseNumeric($row['berat'] ?? 0),
                    'harga'       => $this->parseNumeric($row['harga'] ?? 0),
                    'status'      => $row['status'] ?? 'Satuan',
                    'isi'         => $row['isi'] ?? null,
                    'pendataan'   => $row['pendataan'] ?? null,

                    // 🔐 KUNCI UNIT
                    'bimba_unit'  => $this->unit->biMBA_unit,
                    'no_cabang'   => $this->unit->no_cabang,
                ]
            );

            // ================= COUNTER =================
            if ($produk->wasRecentlyCreated) {
                $this->inserted++;
            } else {
                $this->updated++;
            }
        }
    }

    // ================= VALIDASI EXCEL =================
    public function rules(): array
    {
        return [
            '*.kode'        => 'required|string|max:50',
            '*.nama_produk' => 'required|string|max:255',
            '*.berat'       => 'nullable',
            '*.harga'       => 'nullable',
            '*.status'      => 'nullable|in:Satuan,Paket',
        ];
    }

    // ================= GETTER =================
    public function getInserted(): int
    {
        return $this->inserted;
    }

    public function getUpdated(): int
    {
        return $this->updated;
    }

    public function getImportedCount(): int
    {
        return $this->inserted + $this->updated;
    }

    // ================= HELPER =================
    private function parseNumeric($value): float
    {
        if ($value === null || $value === '') return 0;

        $value = (string) $value;
        $value = preg_replace('/[^0-9.,-]/', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : 0;
    }
}
