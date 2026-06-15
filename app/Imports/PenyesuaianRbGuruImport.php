<?php

namespace App\Imports;

use App\Models\PenyesuaianRbGuru;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenyesuaianRbGuruImport implements ToModel, WithHeadingRow
{
    protected $lastJumlahMurid = null;
    protected $lastSlotRombim = null;
    protected $lastPenyesuaianRb = null;

    public function model(array $row)
    {
        // Fill down untuk kolom yang kosong
        $jumlahMurid   = $row['jumlah_murid'] ?? $this->lastJumlahMurid;
        $slotRombim    = $row['slot_rombim'] ?? $this->lastSlotRombim;
        $penyesuaianRb = $row['penyesuaian_rb'] ?? $this->lastPenyesuaianRb;

        // Skip row jika jumlah_murid tetap null
        if (!$jumlahMurid) {
            return null;
        }

        // Update nilai terakhir
        $this->lastJumlahMurid = $jumlahMurid;
        $this->lastSlotRombim  = $slotRombim;
        $this->lastPenyesuaianRb = $penyesuaianRb;

        return new PenyesuaianRbGuru([
            'jumlah_murid'   => $jumlahMurid,
            'slot_rombim'    => $slotRombim,
            'jam_kegiatan'   => $row['jam_kegiatan'] ?? null,
            'penyesuaian_rb' => $penyesuaianRb,
        ]);
    }
}
