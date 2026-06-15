<?php

namespace App\Imports;

use App\Models\Profile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProfileImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsOnError, SkipsOnFailure, WithValidation
{
    use Importable, SkipsErrors;

    public function model(array $row)
    {
        // Skip baris kosong atau header
        if (empty(array_filter($row))) {
            return null;
        }

        // Bersihkan data
        $nik = trim($row['nik'] ?? '');
        if (empty($nik)) {
            Log::warning('Import Profile: NIK kosong, skip baris', $row);
            return null;
        }

        // Cek apakah NIK sudah ada → update atau create
        $profile = Profile::where('nik', $nik)->first();

        $data = [
            'nik'                  => $nik,
            'nama'                 => trim($row['nama'] ?? ''),
            'jabatan'              => trim($row['jabatan'] ?? null),
            'status_karyawan'      => trim($row['status_karyawan'] ?? null),
            'departemen'           => trim($row['departemen'] ?? null),
            'bimba_unit'           => trim($row['bimba_unit'] ?? null),
            'no_cabang'            => trim($row['no_cabang'] ?? null),
            'tgl_masuk'            => $this->parseDate($row['tgl_masuk'] ?? null),
            'masa_kerja'           => $this->parseInteger($row['masa_kerja'] ?? null),
            'jumlah_murid_mba'     => $this->parseInteger($row['jumlah_murid_mba'] ?? 0),
            'jumlah_murid_eng'     => $this->parseInteger($row['jumlah_murid_eng'] ?? 0),
            'total_murid'          => $this->parseInteger($row['total_murid'] ?? 0),
            'jumlah_murid_jadwal'  => $this->parseInteger($row['jumlah_murid_jadwal'] ?? 0),
            'jumlah_rombim'        => $this->parseInteger($row['jumlah_rombim'] ?? 0),
            'rb'                   => trim($row['rb'] ?? null),
            'rb_tambahan'          => trim($row['rb_tambahan'] ?? null),
            'ktr'                  => trim($row['ktr'] ?? null),
            'ktr_tambahan'         => trim($row['ktr_tambahan'] ?? null),
            'rp'                   => $this->parseFloat($row['rp'] ?? null),
            'jenis_mutasi'         => trim($row['jenis_mutasi'] ?? null),
            'tgl_mutasi_jabatan'   => $this->parseDate($row['tgl_mutasi_jabatan'] ?? null),
            'masa_kerja_jabatan'   => $this->parseInteger($row['masa_kerja_jabatan'] ?? null),
            'tgl_lahir'            => $this->parseDate($row['tgl_lahir'] ?? null),
            'usia'                 => $this->parseInteger($row['usia'] ?? null),
            'no_telp'              => trim($row['no_telp'] ?? null),
            'email'                => trim($row['email'] ?? null),
            'no_rekening'          => trim($row['no_rekening'] ?? null),
            'bank'                 => trim($row['bank'] ?? null),
            'atas_nama'            => trim($row['atas_nama'] ?? null),
            'mentor_magang'        => trim($row['mentor_magang'] ?? null),
            'periode'              => trim($row['periode'] ?? null),
            'tgl_selesai_magang'   => $this->parseDate($row['tgl_selesai_magang'] ?? null),
            'ukuran'               => trim($row['ukuran'] ?? null),
            'status_lain'          => trim($row['status_lain'] ?? null),
            'keterangan'           => trim($row['keterangan'] ?? null),
        ];

        if ($profile) {
            // Update jika NIK sudah ada
            $profile->update($data);
            Log::info('Profile updated via import', ['nik' => $nik]);
            return null; // tidak perlu return model lagi
        }

        // Create baru
        Log::info('Profile created via import', ['nik' => $nik]);
        return new Profile($data);
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning('Invalid date format in import', ['value' => $value]);
            return null;
        }
    }

    private function parseInteger($value)
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function parseFloat($value)
    {
        $clean = preg_replace('/[^0-9.]/', '', $value);
        return is_numeric($clean) ? (float) $clean : null;
    }

    public function rules(): array
    {
        return [
            'nik' => 'required|string|max:20',
            'nama' => 'required|string|max:100',
            '*.nik' => 'required|string|max:20',
            '*.nama' => 'required|string|max:100',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Import failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
            ]);
        }
    }
}