<?php

namespace App\Imports;

use App\Models\Penerimaan;
use App\Models\BukuInduk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PenerimaanImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected array $bukuCache = [];

    public function __construct()
    {
        // Cache semua buku induk sekali saja
        $this->bukuCache = BukuInduk::select(
            'nim',
            'nama',
            'kelas',
            'gol',
            'kd',
            'status',
            'guru',
            'bimba_unit',
            'no_cabang'
        )
        ->get()
        ->keyBy('nim')
        ->toArray();
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function parseInt($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = trim((string)$value);

        $value = str_replace([
            'Rp',
            'Rp.',
            'rp',
            '.',
            ',',
            ' '
        ], '', $value);

        return is_numeric($value)
            ? (int)$value
            : 0;
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {

            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }

            return Carbon::parse($value)
                ->format('Y-m-d');

        } catch (\Throwable $e) {

            Log::warning('Tanggal gagal diparse', [
                'value' => $value
            ]);

            return null;
        }
    }

    private function getValue(array $row, array $headers): ?string
    {
        $lower = [];

        foreach ($row as $k => $v) {
            $lower[strtolower(trim($k))] = $v;
        }

        foreach ($headers as $h) {

            $h = strtolower(trim($h));

            if (isset($lower[$h])) {

                $val = trim((string)$lower[$h]);

                return $val !== ''
                    ? $val
                    : null;
            }
        }

        return null;
    }

    private function generateKwitansi(
        string $nim,
        Carbon $tanggal,
        int $index
    ): string {

        $nimLast3 = str_pad(substr($nim, -3), 3, '0', STR_PAD_LEFT);

        $tahun2 = str_pad($tanggal->year % 100, 2, '0', STR_PAD_LEFT);

        $bulan2 = str_pad($tanggal->month, 2, '0', STR_PAD_LEFT);

        $tanggal2 = str_pad($tanggal->day, 2, '0', STR_PAD_LEFT);

        return 'KW'
            . $nimLast3
            . $tahun2
            . $bulan2
            . $tanggal2
            . str_pad($index, 2, '0', STR_PAD_LEFT);
    }

    public function collection(Collection $rows)
    {
        $insertData = [];

        $generatedCounter = [];

        foreach ($rows as $row) {

            try {

                $row = $row->toArray();

                $nim = trim(
                    (string)(
                        $this->getValue($row, ['nim'])
                        ?? ''
                    )
                );

                if (!$nim) {
                    continue;
                }

                $tanggalRaw = $this->getValue($row, [
                    'tanggal'
                ]);

                $tanggal = $this->parseDate($tanggalRaw);

                if (!$tanggal) {
                    continue;
                }

                $tanggalCarbon = Carbon::parse($tanggal);

                $buku = $this->bukuCache[$nim] ?? null;

                // =========================
                // KWITANSI
                // =========================

                $kwitansi = trim(
                    (string)(
                        $this->getValue($row, [
                            'kwitansi'
                        ]) ?? ''
                    )
                );

                if (!$kwitansi) {

                    $key = $nim . '_' . $tanggal;

                    if (!isset($generatedCounter[$key])) {
                        $generatedCounter[$key] = 1;
                    } else {
                        $generatedCounter[$key]++;
                    }

                    $kwitansi = $this->generateKwitansi(
                        $nim,
                        $tanggalCarbon,
                        $generatedCounter[$key]
                    );
                }

                // =========================
                // FIELD
                // =========================

                $via = strtolower(trim(
                    $this->getValue($row, ['via'])
                    ?? 'cash'
                ));

                $bulan = trim(
                    $this->getValue($row, ['bulan'])
                    ?? $tanggalCarbon->translatedFormat('F')
                );

                $tahun = (int)(
                    $this->getValue($row, ['tahun'])
                    ?? $tanggalCarbon->year
                );

                $nama_murid = trim(
                    $this->getValue($row, ['nama_murid'])
                    ?? ($buku['nama'] ?? '')
                );

                $kelas = trim(
                    $this->getValue($row, ['kelas'])
                    ?? ($buku['kelas'] ?? '')
                );

                $gol = trim(
                    $this->getValue($row, ['gol'])
                    ?? ($buku['gol'] ?? '')
                );

                $kd = trim(
                    $this->getValue($row, ['kd'])
                    ?? ($buku['kd'] ?? '')
                );

                $guru = trim(
                    $this->getValue($row, ['guru'])
                    ?? ($buku['guru'] ?? '')
                );

                $status = strtolower(trim(
                    $this->getValue($row, ['status'])
                    ?? ($buku['status'] ?? 'aktif')
                ));

                if (!in_array($status, ['aktif', 'keluar'])) {
                    $status = 'aktif';
                }

                $bimba_unit = trim(
                    $this->getValue($row, [
                        'bimba_unit',
                        'unit'
                    ]) ?? ($buku['bimba_unit'] ?? '')
                );

                $no_cabang = trim(
                    $this->getValue($row, [
                        'no_cabang'
                    ]) ?? ($buku['no_cabang'] ?? '')
                );

                // =========================
                // NOMINAL
                // =========================

                $daftar      = $this->parseInt($this->getValue($row, ['daftar']));
                $voucher     = $this->parseInt($this->getValue($row, ['voucher']));
                $spp = $this->parseInt(
    $this->getValue($row, [
        'spp',
        'nilai_spp'
    ])
);
                $kaos        = $this->parseInt($this->getValue($row, ['kaos']));
                $kpk         = $this->parseInt($this->getValue($row, ['kpk']));
                $tas         = $this->parseInt($this->getValue($row, ['tas']));
                $sertifikat  = $this->parseInt($this->getValue($row, ['sertifikat']));
                $stpb        = $this->parseInt($this->getValue($row, ['stpb']));
                $event       = $this->parseInt($this->getValue($row, ['event']));
                $lain_lain   = $this->parseInt($this->getValue($row, ['lain_lain']));

                $totalExcel = $this->parseInt(
                    $this->getValue($row, ['total'])
                );

                $calcTotal =
                    $daftar +
                    $voucher +
                    $spp +
                    $kaos +
                    $kpk +
                    $tas +
                    $sertifikat +
                    $stpb +
                    $event +
                    $lain_lain;

                $total = $totalExcel > 0
                    ? $totalExcel
                    : $calcTotal;

                // =========================
                // INSERT ARRAY
                // =========================

                $insertData[] = [

                    'kwitansi' => $kwitansi,
                    'via' => $via,
                    'tanggal' => $tanggal,
                    'bulan' => $bulan,
                    'tahun' => $tahun,

                    'nim' => $nim,
                    'nama_murid' => $nama_murid,

                    'kelas' => $kelas,
                    'gol' => $gol,
                    'kd' => $kd,
                    'guru' => $guru,
                    'status' => $status,

                    'bimba_unit' => $bimba_unit,
                    'no_cabang' => $no_cabang,

                    'daftar' => $daftar,
                    'voucher' => $voucher,
                    'spp' => $spp,
                    'kaos' => $kaos,
                    'kpk' => $kpk,
                    'tas' => $tas,
                    'sertifikat' => $sertifikat,
                    'stpb' => $stpb,
                    'event' => $event,
                    'lain_lain' => $lain_lain,

                    'total' => $total,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];

            } catch (\Throwable $e) {

                Log::error('IMPORT ERROR', [
                    'error' => $e->getMessage(),
                    'row' => $row
                ]);
            }
        }

        // =========================
        // UPSERT SEKALI
        // =========================

        if (!empty($insertData)) {

           Penerimaan::insert($insertData);

            Log::info('IMPORT SUCCESS', [
                'total_insert' => count($insertData)
            ]);
        }
    }
}