<?php

namespace App\Imports;

use App\Models\VoucherLama;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class VoucherLamaImport implements
    ToModel,
    WithHeadingRow,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnError
{
    use SkipsFailures;

    public function model(array $row)
    {
        $voucherCode = trim($row['voucher'] ?? '');
        if ($voucherCode === '') {
            Log::warning('IMPORT SKIP: voucher kosong', [
                'row' => $row['__row__'] ?? null
            ]);
            return null;
        }

        $jumlahVoucher = (int) ($row['jumlah_voucher'] ?? 1);
        if ($jumlahVoucher <= 0) {
            $jumlahVoucher = 1;
        }

        $nominal = $this->normalizeNumeric($row['nominal'] ?? null);
        if ($nominal === null) {
            $nominal = 50000 * $jumlahVoucher;
        }

        $tanggal            = $this->convertExcelDate($row['tanggal'] ?? null);
        $tanggalPenyerahan  = $this->convertExcelDate($row['tanggal_penyerahan'] ?? null);
        $tanggalPemakaian   = $this->convertExcelDate($row['tanggal_pemakaian'] ?? null);

        // Logging tambahan jika ada nilai tapi gagal parse (debug)
        if (isset($row['tanggal_penyerahan']) && $row['tanggal_penyerahan'] !== '' && $tanggalPenyerahan === null) {
            Log::warning('tanggal_penyerahan gagal diparse', [
                'voucher' => $voucherCode,
                'nilai_excel' => $row['tanggal_penyerahan'],
                'row' => $row['__row__'] ?? 'unknown'
            ]);
        }

        $voucher = VoucherLama::withoutGlobalScopes()
            ->where('voucher', $voucherCode)
            ->first();

        if (! $voucher) {
            $voucher = VoucherLama::withoutGlobalScopes()->newModelInstance();
        }

        $voucher->fill([
            'voucher'               => $voucherCode,
            'no_voucher'            => $this->cleanString($row['no_voucher'] ?? null),
            'jumlah_voucher'        => $jumlahVoucher,
            'nominal'               => $nominal,

            'tanggal'               => $tanggal,
            'tanggal_penyerahan'    => $tanggalPenyerahan,
            'tanggal_pemakaian'     => $tanggalPemakaian,

            'status'                => trim($row['status'] ?? 'Belum Diserahkan'),
            'source'                => 'import',

            'nim'                   => $this->normalizeNim($row['nim'] ?? null),
            'nama_murid'            => $this->cleanString($row['nama_murid'] ?? null),
            'orangtua'              => $this->cleanString($row['orangtua'] ?? null),
            'telp_hp'               => $this->normalizePhone($row['telp_hp'] ?? null),

            'nim_murid_baru'        => $this->normalizeNim($row['nim_murid_baru'] ?? null),
            'nama_murid_baru'       => $this->cleanString($row['nama_murid_baru'] ?? null),
            'orangtua_murid_baru'   => $this->cleanString($row['orangtua_murid_baru'] ?? null),
            'telp_hp_murid_baru'    => $this->normalizePhone($row['telp_hp_murid_baru'] ?? null),

            'bimba_unit'            => $this->cleanString($row['bimba_unit'] ?? null),
            'no_cabang'             => $this->normalizeCabang($row['no_cabang'] ?? null),
        ]);

        $voucher->save();

        Log::info('IMPORT OK', [
            'voucher'            => $voucherCode,
            'nominal'            => $nominal,
            'jumlah_voucher'     => $jumlahVoucher,
            'no_cabang_final'    => $voucher->no_cabang,
            'bimba_unit'         => $voucher->bimba_unit,
            'no_voucher'         => $voucher->no_voucher,
            'tanggal_penyerahan' => $voucher->tanggal_penyerahan ? $voucher->tanggal_penyerahan->format('Y-m-d') : null,
        ]);

        return null;
    }

    public function onError(\Throwable $e): void
    {
        Log::error('VoucherLamaImport ERROR', [
            'message' => $e->getMessage(),
        ]);
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function convertExcelDate($value)
    {
        if ($value === null || $value === '') return null;

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }

            $parsed = strtotime($value);
            return $parsed ? date('Y-m-d', $parsed) : null;
        } catch (\Throwable $e) {
            Log::warning('DATE PARSE FAIL', ['value' => $value]);
            return null;
        }
    }

    private function normalizeCabang($value)
    {
        if ($value === null) return null;
        $digits = preg_replace('/\D/', '', (string) $value);
        if ($digits === '') return null;
        return str_pad($digits, 5, '0', STR_PAD_LEFT);
    }

    private function normalizeNim($value)
    {
        if ($value === null || $value === '') return null;
        $nim = preg_replace('/\D/', '', (string) $value);
        if ($nim === '') return null;
        return str_pad($nim, 5, '0', STR_PAD_LEFT);
    }

    private function normalizePhone($value)
    {
        if ($value === null || $value === '') return null;
        $phone = preg_replace('/\D/', '', (string) $value);
        if ($phone === '') return null;
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    private function normalizeNumeric($value)
    {
        if ($value === null || $value === '') return null;
        $num = filter_var($value, FILTER_VALIDATE_FLOAT);
        return $num !== false ? (float) $num : null;
    }

    private function cleanString($value)
    {
        if ($value === null) return null;
        $v = trim((string) $value);
        return $v === '' ? null : $v;
    }
}