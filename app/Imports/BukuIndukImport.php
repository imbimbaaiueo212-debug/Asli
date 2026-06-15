<?php

namespace App\Imports;

use App\Models\BukuInduk;
use App\Models\BukuIndukHistory;
use App\Models\HargaSaptataruna;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BukuIndukImport implements ToCollection, WithHeadingRow, WithStartRow
{
    /**
     * Skip 2 baris header (Group + Detail)
     */
    public function startRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
         
        Log::info('=== IMPORT DIMULAI ===', ['total_data_rows' => $rows->count()]);

        
        foreach ($rows as $index => $row) {

            /* =====================================================
             * VALIDASI WAJIB - NIM di kolom C (index 2)
             * ===================================================== */
            $nim = trim((string) ($row[2] ?? $row['2'] ?? ''));
            if ($nim === '') {
                continue;
            }

            Log::info("Memproses NIM: {$nim}");

            /* =====================================================
             * NORMALISASI (menggunakan numeric key)
             * ===================================================== */
            $bimbaUnit = trim($row[0] ?? '');
            $kelas     = trim($row[15] ?? '');
            $guru      = trim($row[20] ?? '');

            /* =====================================================
             * KONVERSI TANGGAL
             * ===================================================== */
            $tglLahir    = $this->convertExcelDate($row[5] ?? null);
            $tglDaftar   = $this->convertExcelDate($row[10] ?? null);
            $tglMasuk    = $this->convertExcelDate($row[11] ?? null);
            $tglKeluar   = $this->convertExcelDate($row[22] ?? null);
            $tglMulai    = $this->convertExcelDate($row[31] ?? null);   // sesuaikan jika perlu
            $tglAkhir    = $this->convertExcelDate($row[32] ?? null);
            $tglPindah   = $this->convertExcelDate($row[42] ?? null);

            $usia = $tglLahir ? $tglLahir->age : null;
            $lama_bljr = $tglMasuk
                ? $tglMasuk->diff(Carbon::today())->format('%y tahun %m bulan')
                : null;

            /* =====================================================
             * OTOMATISASI STATUS
             * ===================================================== */
            $statusInput = trim((string) ($row[21] ?? ''));
            $status = $statusInput;

            if (empty($statusInput)) {
                $today = Carbon::today();
                if ($tglKeluar) {
                    $status = $tglKeluar->lessThanOrEqualTo($today) ? 'Keluar' : 'Aktif';
                } elseif ($tglMasuk) {
                    $status = $tglMasuk->lessThanOrEqualTo($today) ? 'Aktif' : 'Baru';
                } else {
                    $status = 'Aktif';
                }
            }

            /* =====================================================
             * LOGIKA SPP OTOMATIS
             * ===================================================== */
            $spp = null;
            $spp_source = 'manual';

            if (isset($row[18]) && is_numeric($row[18])) {
                $spp = (float) $row[18];
                if ($spp > 0 && $spp < 1000) $spp *= 1000;
                $spp_source = 'excel';
            }

            if ($spp === null) {
                $gol = strtoupper(trim($row[16] ?? ''));
                $kd  = strtoupper(trim($row[17] ?? ''));
                if ($gol && in_array($kd, ['A','B','C','D','E','F'])) {
                    $harga = HargaSaptataruna::whereRaw('UPPER(TRIM(kode)) = ?', [$gol])->first();
                    if ($harga && !is_null($harga->{strtolower($kd)})) {
                        $spp = (float) $harga->{strtolower($kd)};
                        $spp_source = 'auto_harga_saptataruna';
                    }
                }
            }

            /* =====================================================
             * SINKRON UNIT ↔ CABANG
             * ===================================================== */
            [$noCabang, $bimbaUnit] = $this->syncCabangUnitFromDB($row, $bimbaUnit, $nim);

            /* =====================================================
             * DATA FINAL
             * ===================================================== */
            $data = [
                'nim'                  => $nim,
                'nama'                 => trim($row[3] ?? ''),
                'bimba_unit'           => $bimbaUnit,
                'no_cabang'            => $noCabang,
                'kelas'                => $kelas,
                'guru'                 => $guru,

                'tgl_daftar'           => $tglDaftar?->format('Y-m-d'),
                'tgl_masuk'            => $tglMasuk?->format('Y-m-d'),
                'tgl_keluar'           => $tglKeluar?->format('Y-m-d'),
                'tanggal_pindah'       => $tglPindah?->format('Y-m-d'),

                'gol'                  => strtoupper(trim($row[16] ?? '')),
                'kd'                   => strtoupper(trim($row[17] ?? '')),
                'spp'                  => $spp,

                'status'               => $status,

                'tmpt_lahir'           => trim($row[4] ?? ''),
                'tgl_lahir'            => $tglLahir?->format('Y-m-d'),
                'usia'                 => $usia,
                'lama_bljr'            => $lama_bljr,

                'orangtua'             => trim($row[7] ?? ''),
                'no_telp_hp'           => trim($row[8] ?? ''),
                'alamat_murid'         => trim($row[9] ?? ''),

                'tahap'              => trim($row[13] ?? ''),
                'tgl_tahapan'       => $this->convertExcelDate($row[14] ?? null)?->format('Y-m-d'),
                'level'                => trim($row[26] ?? ''),
                'tgl_level'            => $this->convertExcelDate($row[27] ?? null)?->format('Y-m-d'),
                'jenis_kbm'            => trim($row[28] ?? ''),
                'kode_jadwal' => trim($row['jadwal'] ?? ''),
                'hari_jam'             => trim($row[40] ?? ''),
                'periode' => $this->normalizePeriode(
                        $row['masa_aktif_dhuafa_bnf'] ?? ''
                    ),
                'tgl_mulai'  => $tglMulai?->format('Y-m-d'),
                'tgl_akhir'  => $tglAkhir?->format('Y-m-d'),
                'alert' => trim($row[33] ?? ''),

                'petugas_trial'        => trim($row[19] ?? ''),
                'no_cab_merge'         => trim($row[25] ?? ''),
                'asal_modul' => trim($row['supply_modul'] ?? ''),
                'ke_bimba_intervio'    => trim($row[43] ?? ''),

                'kategori_keluar'      => trim($row[23] ?? ''),
                'alasan_keluar'        => trim($row[24] ?? ''),
                'status_pindah'        => trim($row[41] ?? ''),

                'note_garansi'         => trim($row[45] ?? ''),
                'info'                 => trim($row[29] ?? ''),
            ];

            /* =====================================================
             * SIMPAN / UPDATE
             * ===================================================== */
            $bukuInduk = BukuInduk::where('nim', $nim)->first();

            if ($bukuInduk) {
                $bukuInduk->update($data);
                $action = 'update_import';
            } else {
                $bukuInduk = BukuInduk::create($data);
                $action = 'import_create';
            }

            BukuIndukHistory::create([
                'buku_induk_id' => $bukuInduk->id,
                'action' => $action,
                'user'   => Auth::user()?->name ?? 'import_system',
                'note'   => "Updated via import | SPP: {$spp_source}",
            ]);
        }
    }

    // ==================== HELPER METHODS (Tetap Sama) ====================

    private function syncCabangUnitFromDB($row, ?string $bimbaUnit, string $nim): array
    {
        $noCabang = trim($row[1] ?? '');

        $unit = $bimbaUnit;

        if ($noCabang) {
            $found = Unit::withoutGlobalScopes()->where('no_cabang', $noCabang)->first();
            if ($found) {
                $unit = $found->biMBA_unit;
            }
        } elseif ($unit) {
            $found = Unit::withoutGlobalScopes()->whereRaw('UPPER(bimba_unit) = ?', [strtoupper($unit)])->first();
            if ($found) $noCabang = $found->no_cabang;
        }

        return [$noCabang, $unit];
    }

    private function getHeaderValue(Collection $row, array $possibleKeys): ?string
    {
        // Backup jika butuh (meski kita pakai numeric key)
        foreach ($possibleKeys as $key) {
            $keyNorm = strtolower(str_replace([' ','_','-'], '', $key));
            foreach ($row as $header => $value) {
                $headerNorm = strtolower(str_replace([' ','_','-'], '', $header));
                if ($headerNorm === $keyNorm || str_contains($headerNorm, $keyNorm)) {
                    $val = trim((string) $value);
                    return $val !== '' ? $val : null;
                }
            }
        }
        return null;
    }

    private function convertExcelDate($value): ?Carbon
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject($value));
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
    private function normalizePeriode($value): ?string
{
    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    $value = strtolower(str_replace(' ', '', $value));

    $mapping = [
        'ke-1' => 'Ke-1',
        'ke1'  => 'Ke-1',

        'ke-2' => 'Ke-2',
        'ke2'  => 'Ke-2',

        'ke-3' => 'Ke-3',
        'ke3'  => 'Ke-3',

        'ke-4' => 'Ke-4',
        'ke4'  => 'Ke-4',
    ];

    return $mapping[$value] ?? null;
}
}