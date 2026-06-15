<?php

namespace App\Console\Commands;

use App\Models\BukuInduk;
use App\Models\Student;
use App\Models\MuridTrial;
use App\Models\Unit;
use App\Services\GoogleFormService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ImportStudentsFromForms extends Command
{
    protected $signature = 'forms:import-students
        {sheet? : Nama sheet (opsional, default: Registrasi)}
        {--unit= : Filter hanya unit tertentu (bimba_unit). Digunakan oleh user biasa}
        {--dd : Dump & die untuk debug (hanya 1 baris pertama)}
        {--stage=map : Tahap debug [map|create|update|stored]}';

    protected $description = 'Import Google Form → Student + Trial + Buku Induk OTOMATIS';

    public function handle(GoogleFormService $forms): int
{
    $sheetName  = $this->argument('sheet') ?: 'Registrasi';
    $unitFilter = $this->option('unit');

    $this->info("🚀 Memulai import dari sheet: {$sheetName}");

    if ($unitFilter) {
        $this->info("🔒 FILTER UNIT AKTIF: {$unitFilter}");
    }

    $rows = collect($forms->getResponses($sheetName));

    if ($rows->isEmpty()) {
        $this->info("Sheet '{$sheetName}' kosong.");
        return self::SUCCESS;
    }

    $imported = $updated = $skipped = 0;

    foreach ($rows as $index => $row) {
        if ($this->option('dd') && $index > 0) break;

        try {
            $payload = $this->mapRow((array) $row);

            if (empty($payload['nama'])) {
                $skipped++;
                continue;
            }

            if (!empty($payload['nama'])) {
                $payload['nama'] = $this->upperCaseName($payload['nama']);
            }

            // Filter Unit
            if ($unitFilter) {
                $rowUnit = trim($payload['bimba_unit'] ?? '');
                if (empty($rowUnit) || !str_contains(strtolower($rowUnit), strtolower($unitFilter))) {
                    $skipped++;
                    continue;
                }
            }

            // Deteksi jenis pendaftaran
            $sumber = strtolower(trim($payload['sumber_pendaftaran'] ?? ''));
            $isAktifKembali = preg_match('/\b(aktif kembali|aktif ulang|masuk kembali|reaktif|kembali masuk|daftar lagi|balik lagi)\b/i', $sumber);
            $isMutasi       = preg_match('/\b(mutasi|pindah|pindahan|transfer|pindah cabang)\b/i', $sumber);

            if ($isAktifKembali || $isMutasi) {
                $payload['source'] = 'direct';
            } else {
                $payload['source'] = 'trial';
            }

            // ====================== CEK SUDAH ADA ======================
            $existing = $this->findExistingStudent($payload);

            if ($existing) {
                // UPDATE DATA SAJA - JANGAN UBAH STATUS TRIAL
                $this->updateExistingStudent($existing, $payload);
                $updated++;
                $this->info("🔄 UPDATE (status trial DILINDUNGI): {$existing->nama}");
                continue;
            }

            // ====================== CREATE BARU ======================
            $this->createNewStudent($payload);
            $imported++;
            $this->info("✅ BARU DIBUAT: {$payload['nama']}");

        } catch (\Throwable $e) {
            $skipped++;
            Log::error('IMPORT ERROR', [
                'row'   => $index + 2,
                'nama'  => $payload['nama'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            $this->error("Error baris " . ($index + 2) . ": " . $e->getMessage());
        }
    }

    $this->newLine();
    $this->info("SELESAI IMPORT DARI GOOGLE FORM");
    $this->table(['Status', 'Jumlah'], [
        ['Baru'     => $imported],
        ['Updated'  => $updated],
        ['Skip'     => $skipped],
        ['Total'    => $rows->count()],
    ]);

    return self::SUCCESS;
}

protected function updateExistingStudent(Student $student, array $payload): void
{
    $updateData = [
        'bimba_unit' => $payload['bimba_unit'] ?? $student->bimba_unit,
        'no_cabang'  => $payload['no_cabang'] ?? $student->no_cabang,
        'alamat'     => $payload['alamat'] ?? $student->alamat,
        'orangtua'   => $payload['orangtua'] ?? $student->orangtua,
        'no_telp'    => $payload['no_telp'] ?? $student->no_telp,
        'hari'       => $payload['hari'] ?? $student->hari,
        'jam'        => $payload['jam'] ?? $student->jam,
        'source'     => $payload['source'] ?? $student->source,
    ];

    $student->update(array_filter($updateData));

    // === PERLINDUNGAN STATUS TRIAL ===
    if ($student->muridTrial) {
        $trial = $student->muridTrial;
        if (in_array($trial->status_trial, ['aktif', 'lanjut_daftar', 'terdaftar'])) {
            // Hanya update data pendukung, JANGAN ubah status
            $trial->update([
                'bimba_unit' => $student->bimba_unit,
                'no_cabang'  => $student->no_cabang,
                'alamat'     => $student->alamat,
                'orangtua'   => $student->orangtua,
                'no_telp'    => $student->no_telp,
            ]);
            Log::info("Status trial DILINDUNGI (tetap aktif/lanjut_daftar)", ['nama' => $student->nama]);
        }
    }
}

protected function createNewStudent(array $payload): void
{
    DB::transaction(function () use ($payload) {
        $student = Student::create($payload);

        if ($student->source === 'trial') {
            $this->ensureTrialRelation($student, 'baru');
        }
    });
}

    // Khusus untuk murid RESMI (bukan trial)
        

    // ====================================================================
    // MAPPING & NORMALISASI
    // ====================================================================
      protected function mapRow(array $row): array
{
    // Alias header umum
    $alias = [
        'timestamp' => 'form_timestamp',
        'email address' => 'email',
        'sumber pendaftaran' => 'sumber_pendaftaran',

        'nama lengkap peserta anak bimba' => 'nama',
        'nama lengkap peserta anak bimb' => 'nama',
        'nama' => 'informasi_humas_nama',

        'tanggal lahir' => 'tgl_lahir',
        'tempat lahir' => 'tempat_lahir',
        'alamat lengkap' => 'alamat',

        'jenis kelamin' => 'jenis_kelamin',
        'agama' => 'agama_murid',

        'kode pos' => 'kode_pos',
        'nomor rumah' => 'no_rumah',
        'rt' => 'rt',
        'rw' => 'rw',
        'kelurahan' => 'kelurahan',
        'kecamatan' => 'kecamatan',
        'kodya / kab' => 'kodya_kab',
        'kodya kab' => 'kodya_kab',
        'provinsi' => 'provinsi',

        'nama ayah' => 'nama_ayah',
        'agama ayah' => 'agama_ayah',
        'pekerjaan ayah' => 'pekerjaan_ayah',
        'alamat kantor ayah' => 'alamat_kantor_ayah',
        'no telp kantor ayah' => 'telepon_kantor_ayah',
        'no hp wa ayah' => 'hp_ayah',

        'nama ibu' => 'nama_ibu',
        'agama ibu' => 'agama_ibu',
        'pekerjaan ibu' => 'pekerjaan_ibu',
        'alamat kantor ibu' => 'alamat_kantor_ibu',
        'no telepon kantor ibu' => 'telepon_kantor_ibu',
        'no hp wa ibu' => 'hp_ibu',

        'tanggal daftar' => 'tanggal_masuk',
        'tanggal masuk sekolah' => 'tanggal_masuk',

        'informasi bimba aiueo didapat dari' => 'informasi_bimba',
        'hari' => 'hari',
        'jam' => 'jam',

        'bimba unit' => 'bimba_unit',
        'unit' => 'bimba_unit',

        // FOTO
        'upload kk (kartu keluarga)' => 'foto_kk',
        'upload kk' => 'foto_kk',
        'kartu keluarga' => 'foto_kk',
        'foto kk' => 'foto_kk',
        'kk' => 'foto_kk',

        'upload surat mutasi' => 'foto_mutasi',
        'surat mutasi' => 'foto_mutasi',
        'foto mutasi' => 'foto_mutasi',
        'mutasi' => 'foto_mutasi',
    ];

    // Helper Functions
    $parseDate = fn($v) => $this->tryParseDate((string) $v)?->format('Y-m-d');
    $parseDateTime = fn($v) => $this->tryParseDateTime((string) $v);

    $parseMoney = function ($v) {
        if ($v === null || $v === '') return null;
        $raw = preg_replace('/[^0-9,\.]/', '', (string) $v);
        $raw = str_replace('.', '', $raw);
        $raw = str_replace(',', '.', $raw);
        return is_numeric($raw) ? (float) $raw : null;
    };

    $normStr = function ($v) {
        if ($v === null) return null;
        $vv = trim((string) $v);
        return ($vv === '' || $vv === '-' || strtolower($vv) === 'null') ? null : $vv;
    };

    $normHari = function ($v) use ($normStr) {
    $v = $normStr($v);
    if (!$v) return null;

    // Ambil bagian sebelum koma (kalau ada)
    if (str_contains($v, ',')) {
        $v = trim(strtok($v, ','));
    }

    $v = trim($v);

    $map = [
        'senin'     => 'SENIN',
        'seini'     => 'SENIN',
        'selasa'    => 'SELASA',
        'rabu'      => 'RABU',
        'kamis'     => 'KAMIS',
        'jumat'     => 'JUMAT',
        "jum'at"    => 'JUMAT',
        'sabtu'     => 'SABTU',
        'minggu'    => 'MINGGU',
        'srj'       => 'SRJ',     // tambahkan ini
        's.r.j'     => 'SRJ',
        's r j'     => 'SRJ',
    ];

    $key = strtolower($v);
    return $map[$key] ?? strtoupper($v);   // Gunakan strtoupper() untuk kapital semua
};

    $normJam = function ($v) use ($normStr) {
        $v = $normStr($v);
        if (!$v) return null;
        if (str_contains($v, ',')) {
            $parts = explode(',', $v);
            $v = trim($parts[1] ?? $v);
        }
        $v = preg_replace('/^jam\s*/i', '', $v);
        $v = str_replace('.', ':', $v);
        return $v;
    };

    $result = [];
    $unmapped = [];

    foreach ($row as $rawHeader => $val) {
        $val = is_array($val) ? ($val[0] ?? null) : $val;
        if ($val === null || $val === '') continue;

        ['base' => $base, 'role' => $role] = $this->parseHeader((string) $rawHeader);
        $baseNorm = $this->normHeader($base);
        $headerLower = strtolower((string)$rawHeader);

        // ================== DETEKSI KHUSUS FOTO KK & MUTASI ==================
        if (str_contains($headerLower, 'upload kk') || 
            str_contains($headerLower, 'kartu keluarga') || 
            str_contains($headerLower, 'foto kk') || 
            $baseNorm === 'kk') {
            $result['foto_kk'] = trim((string)$val);
            continue;
        }

        if (str_contains($headerLower, 'upload surat mutasi') || 
            str_contains($headerLower, 'surat mutasi') || 
            str_contains($headerLower, 'foto mutasi') || 
            $baseNorm === 'mutasi') {
            $result['foto_mutasi'] = trim((string)$val);
            continue;
        }

        // AYAH
        if ($role === 'ayah') {
            if ($baseNorm === 'nama') { $result['nama_ayah'] = $normStr($val); continue; }
            if ($baseNorm === 'agama') { $result['agama_ayah'] = $normStr($val); continue; }
            if ($baseNorm === 'pekerjaan') { $result['pekerjaan_ayah'] = $normStr($val); continue; }
            if ($baseNorm === 'alamat kantor') { $result['alamat_kantor_ayah'] = $normStr($val); continue; }
            if ($baseNorm === 'telepon kantor') { $result['telepon_kantor_ayah'] = $normStr($val); continue; }
            if (in_array($baseNorm, ['hp wa', 'hp', 'hpwa'], true)) { $result['hp_ayah'] = $normStr($val); continue; }
        }

        // IBU
        if ($role === 'ibu') {
            if ($baseNorm === 'nama') { $result['nama_ibu'] = $normStr($val); continue; }
            if ($baseNorm === 'agama') { $result['agama_ibu'] = $normStr($val); continue; }
            if ($baseNorm === 'pekerjaan') { $result['pekerjaan_ibu'] = $normStr($val); continue; }
            if ($baseNorm === 'alamat kantor') { $result['alamat_kantor_ibu'] = $normStr($val); continue; }
            if ($baseNorm === 'telepon kantor') { $result['telepon_kantor_ibu'] = $normStr($val); continue; }
            if (in_array($baseNorm, ['hp wa', 'hp', 'hpwa'], true)) { $result['hp_ibu'] = $normStr($val); continue; }
        }

        // General alias
        if (array_key_exists($baseNorm, $alias) && $alias[$baseNorm]) {
            $result[$alias[$baseNorm]] = $normStr($val);
            continue;
        }

        // Fallback informasi_bimba
        if (str_contains($baseNorm, 'informasi') && (str_contains($baseNorm, 'bimba') || str_contains($baseNorm, 'aiueo'))) {
            $result['informasi_bimba'] = $normStr($val);
            continue;
        }

        // Jadwal & Biaya
        if (str_contains($baseNorm, 'jadwal') || str_starts_with($baseNorm, 'hari')) {
            $result['hari'] = $result['hari'] ?? $normHari($val);
            continue;
        }
        if (str_starts_with($baseNorm, 'jam')) {
            $result['jam'] = $result['jam'] ?? $normJam($val);
            continue;
        }
        if (str_contains($baseNorm, 'biaya') && str_contains($baseNorm, 'pendaftaran')) {
            $result['biaya_pendaftaran'] = $normStr($val);
            continue;
        }
        if (str_contains($baseNorm, 'spp') && str_contains($baseNorm, 'bulan')) {
            $result['spp_bulanan'] = $normStr($val);
            continue;
        }

        $unmapped[] = ['raw' => (string)$rawHeader, 'base' => $baseNorm, 'role' => $role];
    }

    // ====================== DOWNLOAD & SIMPAN FOTO LOKAL ======================
foreach (['foto_kk', 'foto_mutasi'] as $field) {
    if (empty($result[$field])) continue;

    $nama = $result['nama'] ?? 'unknown';
    $localPath = $this->downloadGoogleDriveFile($result[$field], $field, $nama);

    if ($localPath) {
        $result[$field] = $localPath;           // simpan path relatif
        // $result[$field . '_original'] = $result[$field]; // opsional: simpan url asli
    } else {
        $result[$field] = null;
    }
}

    // Validations & turunan
    $result['informasi_bimba'] = $result['informasi_bimba'] ?? null;
    $result['informasi_humas_nama'] = $result['informasi_humas_nama'] ?? null;

    if (isset($result['form_timestamp'])) {
        $result['form_timestamp'] = $parseDateTime($result['form_timestamp']);
    }

    if (!empty($result['tgl_lahir'])) {
        $result['tgl_lahir'] = $parseDate($result['tgl_lahir']);
        $result['usia'] = $result['tgl_lahir'] ? Carbon::parse($result['tgl_lahir'])->age : null;
    }

    foreach (['kode_pos','no_rumah','rt','rw','kelurahan','kecamatan','kodya_kab','provinsi'] as $f) {
        $result[$f] = $result[$f] ?? null;
    }

    if (!empty($result['tanggal_masuk'])) {
        $result['tanggal_masuk'] = $parseDate($result['tanggal_masuk']);
    }

    if (isset($result['biaya_pendaftaran'])) {
        $result['biaya_pendaftaran'] = $parseMoney($result['biaya_pendaftaran']);
    }
    if (isset($result['spp_bulanan'])) {
        $result['spp_bulanan'] = $parseMoney($result['spp_bulanan']);
    }

    if (!empty($result['hari'])) $result['hari'] = $normHari($result['hari']);
    if (!empty($result['jam'])) $result['jam'] = $normJam($result['jam']);

    if (empty($result['orangtua'] ?? null)) {
        $join = trim(implode(' & ', array_filter([
            $result['nama_ayah'] ?? null, 
            $result['nama_ibu'] ?? null
        ])));
        if ($join !== '') $result['orangtua'] = $join;
    }

    if (!empty($unmapped)) {
        Log::debug('IMPORT: Unmapped headers', $unmapped);
    }

    // Resolve no_cabang
    if (!empty($result['bimba_unit']) && empty($result['no_cabang'] ?? null)) {
        try {
            $resolved = $this->resolveNoCabangFromBimbaUnit($result['bimba_unit']);
            if ($resolved) $result['no_cabang'] = $resolved;
        } catch (\Throwable $e) {
            Log::warning('IMPORT: gagal resolve no_cabang', ['bimba_unit' => $result['bimba_unit']]);
        }
    }

    Log::debug('IMPORT: mapped essentials', [
        'nama'        => $result['nama'] ?? null,
        'bimba_unit'  => $result['bimba_unit'] ?? null,
        'no_cabang'   => $result['no_cabang'] ?? null,
        'foto_kk'     => $result['foto_kk'] ?? null,
        'foto_mutasi' => $result['foto_mutasi'] ?? null,
    ]);

    return $result;
}

private function getDirectGoogleDriveLink(string $url): string
{
    $url = trim((string) $url);
    if (empty($url)) return $url;

    if (preg_match('/[\/=]([a-zA-Z0-9_-]{25,})/', $url, $matches)) {
        $fileId = $matches[1];
        
        // Rekomendasi utama
        return "https://drive.google.com/thumbnail?id={$fileId}&sz=w1000";
    }

    return $url;
}

    /**
     * Try to normalize a unit name for matching.
     */
    protected function normalizeUnitName(?string $s): ?string
    {
        if (!$s) {
            return null;
        }
        $s = trim(mb_strtolower((string) $s));
        // hapus kata noise umum
        $s = preg_replace('/\b(unit|bi?mba|aiueo|cabang)\b/u', ' ', $s);
        // buang punctuation
        $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s);
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }

    /**
     * Resolve no_cabang dari bimba_unit (versi dengan aturan khusus).
     */
    protected function resolveNoCabangFromBimbaUnit(?string $bimbaUnit): ?string
    {
        if (empty($bimbaUnit)) {
            return null;
        }

        $raw = trim((string) $bimbaUnit);
        $lower = mb_strtolower($raw);

        // ATURAN KHUSUS – DIPAKSA BENAR SELAMANYA
        if (str_contains($lower, 'griya') && str_contains($lower, 'pesona') && str_contains($lower, 'madani')) {
            return '05141'; // Griya Pesona Madani
        }
        if (str_contains($lower, 'sapta taruna iv') || str_contains($lower, 'sapta taruna 4')) {
            return '01045';
        }
        if (str_contains($lower, 'pondok indah')) {
            return '05141';
        }
        if (str_contains($lower, 'kebayoran')) {
            return '05142';
        }

        // Coba ambil kode angka langsung (misal "05141 Griya Pesona Madani")
        if (preg_match('/\b(05[0-9]{3,5})\b/', $raw, $m)) {
            return $m[1];
        }

        // Fallback: cek di tabel units dengan normalize
        $norm = $this->normalizeUnitName($raw);
        $unit = Unit::whereRaw('LOWER(bimba_unit) LIKE ?', ["%{$norm}%"])
            ->orWhereRaw('LOWER(bimba_unit) LIKE ?', ["%{$raw}%"])
            ->first(['no_cabang']);

        if (!empty($unit?->no_cabang)) {
            return $unit->no_cabang;
        }

        return null;
    }

    protected function normalizeSourceFromForm(?string $sumber): string
{
    if (!$sumber) return 'trial';

    $x = strtolower(trim($sumber));

    // Hanya Google Form yang dipaksa trial
    if (str_contains($x, 'trial') || str_contains($x, 'daftar trial')) {
        return 'trial';
    }

    // Kalau di form tertulis "mutasi" atau "pindahan", boleh langsung
    if (str_contains($x, 'mutasi') || str_contains($x, 'pindahan') || str_contains($x, 'pindah cabang')) {
        return 'direct'; // atau 'mutasi' kalau mau bedain
    }

    // Selain itu → tetap trial dulu (kebanyakan registrasi biasa)
    return 'trial';
}

    protected function buildUpsertKey(array $p): array
    {
        if (!empty($p['nim'])) {
            return ['nim' => $p['nim']];
        }

        if (!empty($p['email']) && !empty($p['nama'])) {
            return ['email' => $p['email'], 'nama' => $p['nama']];
        }

        if (!empty($p['nama']) && !empty($p['tgl_lahir'])) {
            return ['nama' => $p['nama'], 'tgl_lahir' => $p['tgl_lahir']];
        }

        return ['nama' => $p['nama']];
    }

    // ====================================================================
    // NIM PER-CABANG
    // ====================================================================
    protected function nextNim(?string $bimbaUnit = null, ?string $noCabang = null): string
    {
        // 1. Tentukan prefix kodeUnit (5 digit)
        if (!empty($noCabang)) {
            $kodeUnit = preg_replace('/\D/', '', $noCabang);
            if (strlen($kodeUnit) < 5) {
                $kodeUnit = str_pad($kodeUnit, 5, '0', STR_PAD_LEFT);
            }
        } elseif (!empty($bimbaUnit)) {
            $kodeUnit = $this->resolveNoCabangFromBimbaUnit($bimbaUnit) ?? '01045';
        } else {
            $kodeUnit = '01045';
        }

        // 2. Cari NIM terakhir dengan prefix kodeUnit di buku_induk & students
        $lastNimBukuInduk = BukuInduk::whereRaw('LEFT(nim, 5) = ?', [$kodeUnit])
            ->whereRaw('LENGTH(nim) = 9')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(nim, 6, 4) AS UNSIGNED) DESC')
            ->value('nim');

        $lastNimStudent = Student::whereRaw('LEFT(nim, 5) = ?', [$kodeUnit])
            ->whereRaw('LENGTH(nim) = 9')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(nim, 6, 4) AS UNSIGNED) DESC')
            ->value('nim');

        $lastSeq = 0;

        if ($lastNimBukuInduk) {
            $lastSeq = max($lastSeq, (int) substr($lastNimBukuInduk, 5));
        }
        if ($lastNimStudent) {
            $lastSeq = max($lastSeq, (int) substr($lastNimStudent, 5));
        }

        $nextSeq = $lastSeq + 1;

        // 3. Bentuk NIM: [kodeUnit][urut 4 digit]
        $nim = $kodeUnit . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        // 4. Jaga-jaga kalau sudah terpakai
        while (Student::where('nim', $nim)->lockForUpdate()->exists()) {
            $nextSeq++;
            $nim = $kodeUnit . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $nim;
    }

    protected function normHeader(string $h): string
    {
        $h = mb_strtolower($h, 'UTF-8');
        $h = trim($h);
        $h = preg_replace('/[\x{200B}\x{200C}\x{200D}\x{FEFF}\x{2060}]/u', '', $h);
        $h = str_replace("\xC2\xA0", ' ', $h);
        $h = preg_replace('/[.,\/\\\\\-\_\:\;\|\(\)\[\]\{\}]+/u', ' ', $h);
        $h = preg_replace('/\s+/u', ' ', $h);
        $h = preg_replace('/\bkanto\b/u', 'kantor', $h);
        return trim($h);
    }

    protected function parseHeader(string $raw): array
    {
        $low = mb_strtolower($raw, 'UTF-8');
        $role = null;

        if (preg_match('/\bayah\b/u', $low)) {
            $role = 'ayah';
        }
        if (preg_match('/\bibu\b/u', $low)) {
            $role = 'ibu';
        }

        $base = preg_replace('/\s*\((ayah|ibu)\)\s*/iu', ' ', $raw);
        $base = preg_replace('/\b(ayah|ibu)\b/iu', ' ', $base);
        $base = $this->normHeader($base);
        $base = preg_replace('/\bno\s+/u', '', $base);
        $base = preg_replace('/\bhp\s*\/\s*wa\b/u', 'hp wa', $base);

        return ['base' => trim($base), 'role' => $role];
    }

    protected function tryParseDate(?string $val): ?Carbon
{
    if (empty($val)) {
        return null;
    }

    $val = trim((string) $val);

    // Prioritas tinggi untuk format Indonesia (DD/MM/YYYY)
    $formats = [
        'd/m/Y', 'd-m-Y', 'd.m.Y',     // Indonesia
        'd/m/y', 'd-m-y', 'd.m.y',
        'Y-m-d', 'Y/m/d',               // ISO
        'm/d/Y', 'm-d-Y',               // US (cadangan)
    ];

    foreach ($formats as $format) {
        try {
            $date = Carbon::createFromFormat($format, $val);
            if ($date) {
                return $date->startOfDay(); // pastikan jam 00:00
            }
        } catch (\Throwable $e) {
            continue;
        }
    }

    // Fallback terakhir
    try {
        return Carbon::parse($val)->startOfDay();
    } catch (\Throwable $e) {
        Log::warning("Gagal parse tanggal: {$val}");
        return null;
    }
}

    protected function tryParseDateTime(?string $val): ?Carbon
    {
        if (!$val) {
            return null;
        }
        try {
            return Carbon::parse($val);
        } catch (\Throwable $e) {
        }

        $formats = [
            'd/m/Y H:i:s',
            'd-m-Y H:i:s',
            'm/d/Y H:i:s',
            'm-d-Y H:i:s',
            'Y-m-d H:i:s',
            'd/m/Y H:i',
            'd-m-Y H:i',
            'm/d/Y H:i',
            'm-d-Y H:i',
            'Y-m-d H:i',
            'd/m/Y H.i:s',
            'd-m-Y H.i:s',
            'm/d/Y H.i:s',
            'm-d-Y H.i:s',
            'Y-m-d H.i:s',
            'd/m/Y H.i',
            'd-m-Y H.i',
            'm/d/Y H.i',
            'm-d/Y H.i',
            'Y-m-d H.i',
        ];

        foreach ($formats as $f) {
            try {
                return Carbon::createFromFormat($f, $val);
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    protected function assertStudentColumns(): void
    {
        $need = ['biaya_pendaftaran', 'spp_bulanan', 'informasi_bimba', 'hari', 'jam'];
        foreach ($need as $col) {
            if (!Schema::hasColumn('students', $col)) {
                Log::warning("IMPORT: Kolom '{$col}' TIDAK ADA di tabel students. Nilai akan diabaikan oleh Eloquent.");
            }
        }
    }

    protected function ensureTrialRelation(Student $student, string $status = 'baru'): void
{
    if ($student->source !== 'trial') {
        return;
    }

    // Jika sudah punya MuridTrial → update saja
    if ($student->murid_trial_id) {
        $student->muridTrial?->update([
            'bimba_unit' => $student->bimba_unit,
            'no_cabang'  => $student->no_cabang,
            'alamat'     => $student->alamat,
        ]);
        return;
    }

    // === CHECK DELAY 24 JAM ===
    $createdAt = $student->created_at ?? $student->trial_started_at ?? now();
    $sudah24Jam = $createdAt->lte(now()->subDay());

    if (!$sudah24Jam) {
        if (empty($student->trial_started_at)) {
            $student->trial_started_at = now();
            $student->trial_status = 'baru';
            $student->saveQuietly();
        }

        Log::info('⏳ IMPORT DITUNDA - Trial Baru (belum 24 jam)', [
            'student_id' => $student->id,
            'nama'       => $student->nama,
            'created_at' => $createdAt->format('Y-m-d H:i:s')
        ]);
        return;
    }

    // === SUDAH 24 JAM → BARU BUAT MURIDTRIAL ===
    try {
        $trial = MuridTrial::create([
            'nama'                => $student->nama,
            'status_trial'        => 'baru',           // JANGAN 'aktif'
            'kelas'               => $student->kelas ?? 'Reguler',
            'tgl_lahir'           => $student->tgl_lahir,
            'usia'                => $student->usia,
            'orangtua'            => $student->orangtua,
            'no_telp'             => $student->no_telp,
            'alamat'              => $student->alamat,
            'rt'                  => $student->rt,
            'guru_trial'          => $student->guru_wali,
            'bimba_unit'          => $student->bimba_unit,
            'no_cabang'           => $student->no_cabang,
            'tgl_mulai'           => $student->tanggal_masuk ?? now()->format('Y-m-d'),
            'waktu_submit'        => $student->created_at ?? now(),
            'tanggal_trial_baru'  => now()->format('Y-m-d'),
        ]);

        $student->update([
            'murid_trial_id' => $trial->id,
            'trial_status'   => 'baru'
        ]);

        Log::info('✅ MuridTrial DIBUAT setelah 24 jam (dari import)', [
            'trial_id'   => $trial->id,
            'student_id' => $student->id,
            'nama'       => $student->nama
        ]);

    } catch (\Throwable $e) {
        Log::error('Gagal create MuridTrial dari import', [
            'nama'  => $student->nama,
            'error' => $e->getMessage()
        ]);
    }
}

    protected function essentials(array $payload, ?Student $student = null): array
    {
        return [
            'nama' => $payload['nama'] ?? ($student->nama ?? null),
            'biaya_pendaftaran' => $payload['biaya_pendaftaran'] ?? ($student->biaya_pendaftaran ?? null),
            'spp_bulanan' => $payload['spp_bulanan'] ?? ($student->spp_bulanan ?? null),
            'informasi_bimba' => $payload['informasi_bimba'] ?? ($student->informasi_bimba ?? null),
            'hari' => $payload['hari'] ?? ($student->hari ?? null),
            'jam' => $payload['jam'] ?? ($student->jam ?? null),
            'source' => $payload['source'] ?? ($student->source ?? null),
            'murid_trial_id' => $student->murid_trial_id ?? null,
        ];
    }

    /**
     * Proses Aktif Kembali murid lama (pakai NIM yang sama)
     */
    protected function reactivateExistingStudent(Student $student, array $payload, BukuInduk $bukuInduk): void
    {
        $tanggalMasukBaru = $payload['tanggal_masuk'] ?? now()->format('Y-m-d');

        // Clear status keluar & update tanggal masuk
        $bukuInduk->update([
            'tgl_keluar'       => null,
            'kategori_keluar'  => null,
            'alasan'           => null,
            'tanggal_masuk'    => $tanggalMasukBaru,
            'status'           => 'Aktif Kembali',
            'keterangan'       => trim(($bukuInduk->keterangan ?? '') . "\n\n[OTOMATIS via Google Form] Aktif kembali pada " . now()->format('d/m/Y')),
        ]);

        // Update Student juga
        $student->update([
            'tanggal_masuk' => $tanggalMasukBaru,
            'source'        => 'direct',
        ]);

        // Catat riwayat
        if (class_exists(\App\Models\StudentHistory::class)) {
            \App\Models\StudentHistory::create([
                'student_id' => $student->id,
                'user_id'    => null, // dari import
                'diff'       => [
                    'aktif_kembali' => [
                        'via'                => 'google_form',
                        'nim'                => $student->nim,
                        'tanggal_masuk_baru' => $tanggalMasukBaru,
                        'old_tgl_keluar'     => $bukuInduk->getOriginal('tgl_keluar'),
                    ]
                ],
                'ip' => 'system-import',
            ]);
        }

        Log::info('Murid aktif kembali via Google Form (NIM lama)', [
            'nim'  => $student->nim,
            'nama' => $student->nama,
            'tanggal_masuk' => $tanggalMasukBaru,
        ]);
    }



/**
 * Download file Google Drive dan simpan ke storage lokal
 */
private function downloadGoogleDriveFile(string $originalUrl, string $fieldName, string $namaMurid): ?string
{
    $originalUrl = trim($originalUrl);
    if (empty($originalUrl)) {
        return null;
    }

    // Extract File ID
    if (!preg_match('/[\/=]([a-zA-Z0-9_-]{25,})/', $originalUrl, $matches)) {
        Log::warning("Gagal extract File ID", ['url' => $originalUrl]);
        return null;
    }

    $fileId = $matches[1];
    $namaMuridClean = Str::slug($namaMurid, '_');
    $ext = str_contains(strtolower($fieldName), 'mutasi') ? 'pdf' : 'jpg';
    $filename = "{$namaMuridClean}_{$fieldName}_" . time() . ".{$ext}";

    $folder = $fieldName === 'foto_kk' ? 'foto_kk' : 'foto_mutasi';
    $path = "{$folder}/{$filename}";

    try {
        $client = new \GuzzleHttp\Client([
            'timeout' => 60,
            'verify'  => false,        // Matikan kalau sering SSL error
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        // Link download terbaik (2025-2026)
        $downloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}";

        $response = $client->get($downloadUrl);

        $body = $response->getBody()->getContents();

        // === HANDLE VIRUS SCAN WARNING (File Besar) ===
        if (str_contains($body, 'virus') || str_contains($body, 'confirm=') || str_contains($body, 'Download anyway')) {
            preg_match('/confirm=([a-zA-Z0-9_-]+)/', $body, $confirmMatch);

            if (!empty($confirmMatch[1])) {
                $downloadUrl .= "&confirm=" . $confirmMatch[1];
                $response = $client->get($downloadUrl);
                $body = $response->getBody()->getContents();
            } else {
                // Coba metode alternatif
                $response = $client->get("https://drive.google.com/uc?id={$fileId}&export=download&confirm=t");
                $body = $response->getBody()->getContents();
            }
        }

        // Cek apakah yang didownload adalah HTML (bukan file)
        if (str_starts_with(trim($body), '<!DOCTYPE') || str_contains($body, '<html')) {
            Log::warning("Download mengembalikan HTML (kemungkinan file tidak public)", [
                'fileId' => $fileId,
                'nama'   => $namaMurid
            ]);
            return null;
        }

        Storage::disk('public')->put($path, $body);

        Log::info("✅ File berhasil didownload", [
            'nama'   => $namaMurid,
            'field'  => $fieldName,
            'path'   => $path,
            'size'   => strlen($body)
        ]);

        return $path;

    } catch (\Exception $e) {
        Log::error("❌ Gagal download Google Drive", [
            'nama'    => $namaMurid,
            'fileId'  => $fileId ?? 'unknown',
            'url'     => $originalUrl,
            'error'   => $e->getMessage()
        ]);
        return null;
    }
}

/**
 * Cari student existing (prioritas: NIM, lalu nama + tgl lahir)
 */
protected function findExistingStudent(array $payload): ?Student
{
    if (!empty($payload['nim'])) {
        return Student::where('nim', $payload['nim'])->first();
    }

    if (!empty($payload['nama']) && !empty($payload['tgl_lahir'])) {
        return Student::where('nama', $payload['nama'])
                      ->where('tgl_lahir', $payload['tgl_lahir'])
                      ->first();
    }

    return null;
}

/**
 * Proper Case untuk Nama (Setiap kata huruf besar di awal)
 */
protected function properCase(string $name): string
{
    $name = trim($name);
    if (empty($name)) return $name;

    // Ubah jadi title case
    $name = mb_convert_case($name, MB_CASE_TITLE, "UTF-8");

    // Koreksi kata khusus Indonesia
    $corrections = [
        ' Bin ' => ' bin ',
        ' Binti ' => ' binti ',
        ' Van ' => ' van ',
        ' De ' => ' de ',
    ];

    foreach ($corrections as $wrong => $correct) {
        $name = str_replace($wrong, $correct, $name);
    }

    return $name;
}

/**
 * Cari data lama di Buku Induk untuk aktif kembali
 */
protected function findOldBukuIndukForReactivate(string $nama, ?string $tglLahir = null, ?string $bimbaUnit = null)
{
    return BukuInduk::whereNotNull('nim')
        ->where(function ($q) use ($nama) {
            $clean = trim(strtoupper($nama));
            $q->whereRaw('TRIM(UPPER(nama)) = ?', [$clean])
              ->orWhereRaw('UPPER(nama) LIKE ?', ['%' . $clean . '%'])
              ->orWhereRaw('SOUNDEX(nama) = SOUNDEX(?)', [$nama]);
        })
        ->when($tglLahir, fn($q) => $q->where('tgl_lahir', $tglLahir))
        ->when($bimbaUnit, fn($q) => $q->where('bimba_unit', 'LIKE', "%{$bimbaUnit}%"))
        ->orderByRaw("CASE WHEN status IN ('keluar','Keluar','KELUAR') THEN 1 ELSE 2 END")
        ->orderBy('tgl_keluar', 'desc')
        ->first();
}

/**
 * Ubah nama menjadi KAPITAL SEMUA (ALL UPPERCASE)
 */
protected function upperCaseName(string $name): string
{
    $name = trim($name);
    if (empty($name)) return $name;

    // Ubah ke huruf besar semua
    return mb_strtoupper($name, 'UTF-8');
}
}
