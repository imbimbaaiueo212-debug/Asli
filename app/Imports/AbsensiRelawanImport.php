<?php

namespace App\Imports;

use App\Models\AbsensiRelawan;
use App\Models\Profile;
use App\Models\Unit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AbsensiRelawanImport implements ToModel, WithHeadingRow, SkipsOnFailure, SkipsOnError, WithChunkReading
{
    use SkipsFailures, SkipsErrors;

    private int $success = 0;
    private array $unitCache = [];

    public function __construct()
    {
        $this->unitCache = Unit::pluck('no_cabang', 'biMBA_unit')->toArray();
    }

    public function getSuccessCount(): int { return $this->success; }
    public function headingRow(): int { return 1; }
    public function chunkSize(): int { return 500; }

    // NIK: SELALU 10 DIGIT
    private function normalizeNik($value): ?string
    {
        if ($value === null || $value === '' || $value === 0) return null;
        $clean = preg_replace('/\D/', '', (string)$value);
        if ($clean === '' || $clean === '0') return null;
        return str_pad($clean, 11, '0', STR_PAD_LEFT); // 0010201045
    }

    // NO CABANG: SELALU 5 DIGIT → 01045
    private function normalizeNoCabang($value): ?string
    {
        if ($value === null || $value === '' || $value === 0) return null;
        $clean = preg_replace('/\D/', '', (string)$value);
        if ($clean === '' || $clean === '0') return null;
        return str_pad($clean, 5, '0', STR_PAD_LEFT); // 01045
    }

    private function normalizeRowKeys(array $row): array
    {
        $r = [];
        foreach ($row as $k => $v) {
            $key = strtolower(trim($k));
            $r[$key] = $v;
        }

        $aliases = [
            'nama_relawan'   => 'nama_relawaan',
            'nama'           => 'nama_relawaan',
            'status_relawan' => 'status_relawaan',
            'tgl'            => 'tanggal',
            'date'           => 'tanggal',
            'ket'            => 'keterangan',
            'unit'           => 'bimba_unit',
            'cabang'         => 'no_cabang',
            'unit_bimba'     => 'bimba_unit',
        ];

        foreach ($aliases as $from => $to) {
            if (isset($r[$from]) && !isset($r[$to])) {
                $r[$to] = $r[$from];
            }
        }
        return $r;
    }

    private function parseTanggal($value): ?Carbon
    {
        if ($value === null || $value === '') return null;

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->startOfDay();
            } catch (\Throwable $e) {}
        }

        $str = trim((string)$value);
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $str)) {
            return Carbon::createFromFormat('d/m/Y', $str)->startOfDay();
        }

        foreach (['Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/Y', 'm/d/Y'] as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $str);
                if ($dt) return $dt->startOfDay();
            } catch (\Throwable $e) {}
        }

        try { return Carbon::parse($str)->startOfDay(); }
        catch (\Throwable $e) { return null; }
    }

    private function normalizeAbsensi(string $val): string
    {
        $s = mb_strtolower(trim($val));
        $map = [
            'sakit dengan keterangan dokter' => 'Sakit Dengan Keterangan Dokter',
            'sakit keterangan dokter'        => 'Sakit Dengan Keterangan Dokter',
            'sakit dokter'                   => 'Sakit Dengan Keterangan Dokter',
            'sakit tanpa keterangan dokter'  => 'Sakit Tanpa Keterangan Dokter',
            'sakit tanpa dokter'             => 'Sakit Tanpa Keterangan Dokter',
            'izin dengan form di acc'        => 'Izin Dengan Form di ACC',
            'izin form acc'                  => 'Izin Dengan Form di ACC',
            'izin tanpa form di acc'         => 'Izin Tanpa Form di ACC',
            'izin tanpa form'                => 'Izin Tanpa Form di ACC',
            'tidak mengisi absensi mingguan' => 'Tidak Mengisi Absensi Mingguan',
            'tidak aktif'                    => 'Tidak Aktif',
            'cuti melahirkan'                => 'Cuti Melahirkan',
            'cuti'                           => 'Cuti',
            'datang terlambat'               => 'Datang Terlambat',
            'pulang cepat'                   => 'Pulang Cepat',
        ];
        foreach ($map as $k => $v) {
            if (strpos($s, $k) !== false) return $v;
        }
        return ucwords($val);
    }

    private function mapAbsensiToStatus(string $absensi): string
    {
        $s = trim(mb_strtolower($absensi));
        $map = [
            'sakit dengan keterangan dokter' => 'Sakit',
            'sakit tanpa keterangan dokter'  => 'Izin',
            'izin dengan form di acc'        => 'Izin',
            'izin tanpa form di acc'         => 'Alpa',
            'tidak masuk tanpa form'         => 'Alpa',
            'tidak mengisi absensi mingguan' => 'Alpa',
            'tidak aktif'                    => 'Tidak Aktif',
            'cuti'                           => 'Cuti',
            'cuti melahirkan'                => 'Cuti',
            'datang terlambat'               => 'DT',
            'pulang cepat'                   => 'PC',
        ];
        foreach ($map as $k => $v) {
            if (strpos($s, $k) !== false) return $v;
        }
        return 'Hadir';
    }

    public function model(array $raw)
    {
        $row = $this->normalizeRowKeys($raw);

        $nama        = trim($row['nama_relawaan'] ?? '');
        $posisi      = trim($row['posisi'] ?? '');
        $statusRel   = trim($row['status_relawaan'] ?? '');
        $departemen  = trim($row['departemen'] ?? '');
        $unitExcel   = trim($row['bimba_unit'] ?? '');

        // PENTING: GUNAKAN normalizeNoCabang!
        $cabangExcel = $this->normalizeNoCabang($row['no_cabang'] ?? '');

        $tanggalRaw  = $row['tanggal'] ?? null;
        $absensiRaw  = trim($row['absensi'] ?? '');
        $ket         = trim($row['keterangan'] ?? '');

        // NIK: PAKAI normalizeNik
        $nikExcel = $this->normalizeNik($row['nik'] ?? '');

        // Validasi
        if ($nama === '' || $tanggalRaw === null || $absensiRaw === '') {
            $this->failures()->push(new \Maatwebsite\Excel\Validators\Failure(0, 'row', ['Wajib isi: Nama, Tanggal, Absensi'], $raw));
            return null;
        }

        $tanggal = $this->parseTanggal($tanggalRaw);
        if (!$tanggal) {
            $this->failures()->push(new \Maatwebsite\Excel\Validators\Failure(0, 'tanggal', ['Tanggal tidak valid'], $raw));
            return null;
        }

        $absensi   = $this->normalizeAbsensi($absensiRaw);
        $statusMap = $this->mapAbsensiToStatus($absensi);

        // === UNIT & NO CABANG ===
        $bimbaUnit = $unitExcel;
        $noCabang  = $cabangExcel;

        // Jika kosong, ambil dari Profile → dan format ulang!
        if (!$bimbaUnit || !$noCabang) {
            $profile = Profile::whereRaw('LOWER(nama) = ?', [strtolower($nama)])->first();
            if ($profile) {
                $bimbaUnit = $bimbaUnit ?: $profile->bimba_unit;
                $noCabang  = $noCabang ?: $this->normalizeNoCabang($profile->no_cabang);
                $nikExcel  = $nikExcel ?: $this->normalizeNik($profile->nik);
            }
        }

        // Reverse lookup kalau hanya ada no_cabang
        if (!$bimbaUnit && $noCabang && isset(array_flip($this->unitCache)[$noCabang])) {
            $bimbaUnit = array_flip($this->unitCache)[$noCabang];
        }

        // Kalau hanya ada unit, ambil cabang dari cache
        if ($bimbaUnit && !$noCabang) {
            $cached = $this->unitCache[$bimbaUnit] ?? null;
            $noCabang = $cached ? $this->normalizeNoCabang($cached) : null;
        }

        // Upsert
        $existing = AbsensiRelawan::where('nama_relawaan', $nama)
            ->whereDate('tanggal', $tanggal->format('Y-m-d'))
            ->first();

        $data = [
            'nama_relawaan'   => $nama,
            'nik'             => $nikExcel,
            'posisi'          => $posisi,
            'status_relawaan' => $statusRel,
            'departemen'      => $departemen,
            'bimba_unit'      => $bimbaUnit,
            'no_cabang'       => $noCabang,
            'tanggal'         => $tanggal->format('Y-m-d'),
            'absensi'         => $absensi,
            'keterangan'      => $ket ?: null,
            'status'          => $statusMap,
        ];

        if ($existing) {
            $existing->update($data);
            $record = $existing;
        } else {
            $record = AbsensiRelawan::create($data);
        }

        // Sync potongan
        if (method_exists($record, 'hitungPotonganTunjangan')) {
            $record->hitungPotonganTunjangan();
        }

        $this->success++;
        return null;
    }
}