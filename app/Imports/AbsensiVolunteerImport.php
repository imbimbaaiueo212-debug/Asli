<?php

namespace App\Imports;

use App\Models\AbsensiVolunteer;
use App\Models\AbsensiRelawan;
use App\Models\Profile;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Illuminate\Support\Collection;
use Throwable;

class AbsensiVolunteerImport implements ToCollection, WithHeadingRow, WithCustomCsvSettings, SkipsOnError
{
    use \Maatwebsite\Excel\Concerns\Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // normalize header & values
            $row = $row->map('trim')->map(fn($v) => $v === '' ? null : $v);
            $row = $row->keyBy(fn($v, $k) => strtolower($k));

            $idFingerprint = $row['id_fingerprint'] ?? $row['id fingerprint'] ?? null;
            $tanggalRaw    = $row['tanggal'] ?? null;

            if (!$idFingerprint || !$tanggalRaw) {
                continue;
            }

            $tanggal = $this->parseTanggal($tanggalRaw);
            if (!$tanggal) {
                continue;
            }

            $nik  = $row['nik'] ?? null;
            $nama = $row['nama_relawan'] ?? $row['nama relawan'] ?? $row['nama'] ?? null;
            if (!$nik && !$nama) {
                continue;
            }

            // enrich profile if available
            $profile = null;
            if ($nik) {
                $profile = Profile::where('nik', $nik)->first();
            }
            if (!$profile && $nama) {
                $profile = Profile::where('nama', $nama)->first();
                if ($profile) {
                    $nik = $profile->nik;
                }
            }

            // POSISI/JABATAN: ambil dari profile (kolom 'jabatan' atau 'posisi') jika ada
            $posisi = $profile->jabatan ?? $profile->posisi ?? $row['posisi'] ?? 'Relawan';

            $bimba_unit = $row['bimba_unit'] ?? $row['unit'] ?? $profile->bimba_unit ?? 'Tidak Diketahui';

            // --- NO CABANG: ambil dari file / kolom alternatif / profile, lalu normalisasi format ---
            $rawNoCabang = $row['no_cabang'] ?? $row['cabang'] ?? $profile->no_cabang ?? null;
            $no_cabang = $this->formatNoCabang($rawNoCabang);

            $keterangan = $row['keterangan'] ?? null;
            $jamMasuk   = $this->parseTime($row['jam_masuk'] ?? null);
            $jamKeluar  = $this->parseTime($row['jam_keluar'] ?? null);

            // --- ONDUTY & OFFDUTY: prioritas Excel -> profile -> null
            $onduty = $this->parseTime($row['onduty'] ?? $row['on_duty'] ?? $profile->onduty ?? $profile->on_duty ?? null);
            $offduty = $this->parseTime($row['offduty'] ?? $row['off_duty'] ?? $profile->offduty ?? $profile->off_duty ?? null);

            // Ambil raw absensi dari kolom 'absensi' (prioritas), fallback ke 'status'
            $rawAbsensi = $row['absensi'] ?? $row['status'] ?? $row['keterangan_absensi'] ?? null;

            // ambil departemen: dari file, atau profile, atau fallback
            $departemen = $row['departemen'] ?? $row['department'] ?? $profile->departemen ?? 'biMBA-AIUEO';

            // Mapping: raw absensi -> teks baku + status sistem
            [$canonicalAbsensi, $mappedStatus] = $this->mapPotonganSwitch($rawAbsensi);

            // Simpan / update AbsensiVolunteer (semua baris tetap disimpan)
            AbsensiVolunteer::updateOrCreate(
                [
                    'id_fingerprint' => $idFingerprint,
                    'tanggal'        => $tanggal,
                ],
                [
                    'nik'          => $nik,
                    'nama_relawan' => $nama,
                    'posisi'       => $posisi,
                    'bimba_unit'   => $bimba_unit,
                    'no_cabang'    => $no_cabang,
                    // Simpan status sistem hasil mapping (Sakit/Izin/Alpa/DT/PC/Hadir)
                    'status'       => $mappedStatus,
                    'jam_masuk'    => $jamMasuk,
                    'jam_keluar'   => $jamKeluar,
                    'keterangan'   => $keterangan,
                    'jam_lembur'   => (int) ($row['jam_lembur'] ?? 0),
                    // simpan onduty/offduty agar ditampilkan persis seperti Excel/profile
                    'onduty'       => $onduty,
                    'offduty'      => $offduty,
                ]
            );

            // Hanya simpan ke AbsensiRelawan jika mappedStatus bukan 'Hadir' AND nik ada
            $skipStatuses = ['hadir', 'minggu', 'libur nasional'];

if (!in_array(strtolower($mappedStatus), $skipStatuses) && $nik) {
                $key = ['nik' => $nik, 'tanggal' => $tanggal];

                // Ambil status relawan (status kepegawaian) dari profile bila ada (mis. Aktif/Magang)
                $statusRelawanFromProfile = $profile->status_karyawan ?? 'Tidak Diketahui';

                AbsensiRelawan::updateOrCreate(
                    $key,
                    [
                        'nik'             => $nik,
                        'nama_relawaan'   => $nama,
                        'posisi'          => $posisi,
                        'departemen'      => $departemen,
                        'bimba_unit'      => $bimba_unit,
                        'no_cabang'       => $no_cabang,
                        'tanggal'         => $tanggal,
                        // SIMPAN TEKS BAKU YANG DIGUNAKAN DI POTONGAN
                        'absensi'         => $canonicalAbsensi,
                        // status kepegawaian dan status sistem
                        'status_relawaan' => $statusRelawanFromProfile,
                        'status'          => $mappedStatus,
                        'keterangan'      => $keterangan,
                    ]
                );
            }
        }
    }

    /**
     * Map raw absensi text ke canonical absensi (dipakai oleh potongan) dan status sistem.
     * Mengikuti switch-case yang ada di hitungPotonganTunjangan().
     *
     * @param string|null $raw
     * @return array [canonicalAbsensi, mappedStatus]
     */
    private function mapPotonganSwitch($raw)
{
    $raw = trim((string) ($raw ?? ''));
    if ($raw === '') {
        return ['Hadir', 'Hadir'];
    }

    // Bersihkan teks: buang semua tanda kurung, titik, koma, dll
    $clean = strtolower($raw);
    $clean = preg_replace('/[^a-z0-9\s]/', ' ', $clean); // hapus ( ) . , dll
    $clean = preg_replace('/\s+/', ' ', trim($clean));

    // PRIORITAS 1: MINGGU (APAPUN FORMATNYA)
    if (str_contains($clean, 'minggu')) {
        return ['Hari Minggu', 'Minggu'];
    }

    // PRIORITAS 2: LIBUR NASIONAL / LIBUR
    if (str_contains($clean, 'libur') || str_contains($clean, 'cuti bersama')) {
        return ['Libur Nasional', 'Libur Nasional'];
    }

    // Sisanya...
    if (str_contains($clean, 'sakit') && str_contains($clean, 'dokter') && !str_contains($clean, 'tanpa')) {
        return ['Sakit Dengan Keterangan Dokter', 'Sakit'];
    }
    if (str_contains($clean, 'sakit') && str_contains($clean, 'tanpa')) {
        return ['Sakit Tanpa Keterangan Dokter', 'Izin'];
    }
    if (str_contains($clean, 'izin') && str_contains($clean, 'tanpa form')) {
        return ['Izin Tanpa Form di ACC', 'Alpa'];
    }
    if (str_contains($clean, 'izin')) {
        return ['Izin Dengan Form di ACC', 'Izin'];
    }
    if (str_contains($clean, 'tidak masuk') || str_contains($clean, 'tanpa keterangan') || str_contains($clean, 'alpa')) {
        return ['Tidak Masuk Tanpa Form', 'Alpa'];
    }
    if (str_contains($clean, 'datang terlambat') || str_contains($clean, 'terlambat')) {
        return ['Datang Terlambat', 'DT'];
    }
    if (str_contains($clean, 'pulang cepat') || str_contains($clean, 'pc')) {
        return ['Pulang Cepat', 'PC'];
    }
     if (str_contains($clean, 'tidak akif') || str_contains($clean, 'tidak aktif')) {
        return ['Tidak Aktif', 'Tidak Aktif'];
    }

    return ['Hadir', 'Hadir'];
}

    /**
     * Normalisasi value no_cabang:
     * - jika numeric dan length < 5 -> pad left dengan '0' sampai panjang 5 (mis. 5141 -> 05141)
     * - jika sudah string '05141' -> dikembalikan apa adanya
     * - jika null -> kembalikan null
     */
    private function formatNoCabang($value)
    {
        if ($value === null) return null;

        $v = trim((string) $value);

        if ($v === '') return null;

        if (preg_match('/^\d+$/', $v)) {
            $v = ltrim($v, '+');
            if (strlen($v) < 5) {
                return str_pad($v, 5, '0', STR_PAD_LEFT);
            }
            return $v;
        }

        return $v;
    }

    private function parseTanggal($value)
    {
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        $formats = ['d-M-y', 'd-M-Y', 'j-n-Y', 'd/m/Y', 'Y-m-d'];
        foreach ($formats as $format) {
            try {
                $date = \Carbon\Carbon::createFromFormat($format, trim($value));
                if ($date && $date->year > 2000) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseTime($value)
    {
        if (!$value || trim((string)$value) === '' || trim((string)$value) === '-') {
            return null;
        }

        $original = $value;
        $value = trim((string)$value);
        $value = preg_replace('/\s*(WIB|AM|PM).*$/i', '', $value);
        $value = str_replace(['.', ' '], ':', $value);
        $value = preg_replace('/[^0-9:]/', '', $value);

        if (str_contains($value, ':')) {
            $parts = explode(':', $value);
            if (count($parts) >= 3) {
                $value = $parts[0] . ':' . $parts[1];
            }
        }

        if (preg_match('/^(\d{3,4})$/', $value, $m)) {
            $str = str_pad($m[1], 4, '0', STR_PAD_LEFT);
            return substr($str, 0, 2) . ':' . substr($str, 2, 2);
        }

        if (is_numeric($original)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($original)->format('H:i');
            } catch (\Throwable $e) {
            }
        }

        if (preg_match('/^\d{1,2}:\d{2}$/', $value)) {
            return $value;
        }

        return null;
    }

    public function onError(Throwable $e)
    {
        // optional logging
        // \Log::error('Import Absensi Error: ' . $e->getMessage());
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'input_encoding' => 'UTF-8',
        ];
    }
}
