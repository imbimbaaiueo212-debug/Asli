<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleFormService;
use App\Models\MuridTrial;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SyncMuridTrialCommand extends Command
{
    /**
     * php artisan muridtrial:sync --dry-run --since=2025-10-01 --limit=200
     */
    protected $signature = 'muridtrial:sync 
                            {--dry-run : Jalankan tanpa menulis ke DB}
                            {--since= : Hanya ambil yang Timestamp >= tanggal/waktu ini (contoh: "2025-10-01" atau "2025-10-01 00:00:00")}
                            {--limit= : Batasi jumlah baris yang diproses}';

    protected $description = 'Mengambil dan mengimpor data murid trial dari Google Form Sheet.';

    // ==== KONFIGURASI HEADER (alias agar toleran rename di Sheet) ====
    protected array $H = [
        'timestamp' => ['Timestamp', 'Waktu', 'Waktu Pengisian'],
        'nama' => ['Nama Murid', 'Nama Lengkap', 'Nama'],
        'no_telp' => [
            'no_telp',
            'No. Telp/HP Kontak',
            'No HP',
            'Nomor HP',
            'Nomor Telepon',
            'No. Telepon',
            'No. Telepon Kantor Ayah',
            'No. Telepon Kantor Ibu'
        ],
        'email' => ['Email Address', 'Email', 'E-mail'],
        'orangtua' => ['Nama Orang Tua / Wali', 'Nama Orang Tua', 'Wali', 'Nama Ayah', 'Nama Ibu'],
        'tgl_mulai' => ['Tanggal Masuk Sekolah', 'Tanggal Mulai Trial', 'Tanggal Masuk Trial', 'Mulai Trial'],
        // HAPUS alias "Hari/Jam" di sini agar tidak nyasar ke kolom kelas
        'kelas' => ['Kelas yang Diminati', 'Kelas Diminati', 'Kelas'],
        'tgl_lahir' => ['Tanggal Lahir Murid', 'Tanggal Lahir', 'Tgl Lahir'],
        'usia' => ['Usia', 'Umur'], // akan dihitung jika tidak ada
        'guru_trial' => ['Guru Trial (Jika Sudah Tahu)', 'Guru Trial', 'Guru'],
        'info' => ['Informasi Tambahan (Riwayat Belajar, dsb.)', 'Informasi Tambahan', 'Keterangan', 'Informasi biMBA-AIUEO didapat dari :'],
        // alamat akan dirakit dari beberapa kolom di bawah ini bila 'Alamat' tidak ada
        'alamat' => ['Alamat Lengkap', 'Alamat'],
        // bagian-bagian alamat (untuk perakitan)
        'alamat_parts' => ['No . Rumah', 'No Rumah', 'Nomor Rumah', 'Rt', 'RT', 'Rw', 'RW', 'Kelurahan', 'Desa', 'Kecamatan', 'Kodya / Kab', 'Kodya', 'Kabupaten', 'Kota', 'Provinsi', 'Kode pos', 'Kodepos'],
        // (opsional) jadwal preferensi
        'hari' => ['Hari', 'Hari:'],
        'jam' => ['Jam', 'Jam :', 'Jam:'],
        'bimba_unit' => [
    'biMBA Unit',              // <— INI YANG KAMU PAKAI (paling atas = prioritas 1)
    'biMBA Unit ',             // kalau ada spasi di belakang
    ' biMBA Unit',             // kalau ada spasi di depan
    'Unit biMBA',
    'Unit biMBA*',
    'Unit biMBA (Pilih salah satu)',
    'Unit biMBA AIUEO',
    'Pilih Unit biMBA',
    'Unit',
    'Nama Unit',
    'Unit tempat belajar',
    'Unit BIMBA',
    'Pilih Unit',
    'Unit / Cabang',
    'Unit/Cabang',
    'bimba_unit',
    'unit',
],
    ];

    public function handle(GoogleFormService $googleFormService)
    {
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        $dry = (bool) $this->option('dry-run');
        $sinceOpt = $this->option('since');
        $limitOpt = $this->option('limit');
        $since = null;

        if ($sinceOpt) {
            try {
                $since = Carbon::parse($sinceOpt);
            } catch (\Throwable $e) {
                $this->warn("Opsi --since tidak valid. Abaikan. Detail: " . $e->getMessage());
            }
        }

        $this->info('Memulai sinkronisasi data murid trial dari Google Sheet...');
        if ($dry)
            $this->line('Mode: DRY RUN (tidak menulis ke database).');

        try {
            // Pastikan nama sheet sesuai kebutuhanmu
            $responses = $googleFormService->getResponses('Daftar Trial Baru');
        } catch (\Throwable $e) {
            $this->error('Gagal mengambil data dari Google Sheet: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($limitOpt && is_numeric($limitOpt)) {
            $responses = array_slice($responses, 0, (int) $limitOpt);
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($responses as $idx => $response) {
            // 1) Timestamp
            $timestampString = $this->pick($response, $this->H['timestamp']);
            if (!$timestampString) {
                $this->warn("SKIP #" . ($idx + 1) . ": kolom Timestamp tidak ditemukan.");
                $skipped++;
                continue;
            }

            $waktuSubmit = $this->parseDateTimeFlexible($timestampString);
            if (!$waktuSubmit) {
                $this->warn("SKIP #" . ($idx + 1) . ": gagal parse Timestamp: `{$timestampString}`");
                $skipped++;
                continue;
            }

            if ($since && $waktuSubmit->lt($since)) {
                // Lebih lama dari batas since → lewati tanpa hitung skip
                continue;
            }

            // 2) Normalisasi data
            $namaSheet = (string) $this->pick($response, $this->H['nama'], 'N/A');
            $noTelpRaw = (string) $this->pick($response, $this->H['no_telp'], '');
            $noTelpSheet = $this->normalizePhoneID($noTelpRaw); // ke +62xxxx

            if ($namaSheet === 'N/A' || $namaSheet === '') {
    $this->warn("SKIP #" . ($idx + 1) . ": nama kosong");
    $skipped++;
    continue;
}

if ($noTelpSheet === null) {
    $this->line("<fg=yellow>Info:</> Nomor telepon kosong/tidak ada untuk <fg=cyan>{$namaSheet}</fg=cyan> → tetap diimport");
}

            // Alamat: ambil langsung; jika kosong, rakit dari bagian-bagian
            $alamatLangsung = (string) $this->pick($response, $this->H['alamat'], '');
            if ($alamatLangsung === '' || $alamatLangsung === null) {
                $alamatFinal = $this->buildAddressFromParts($response);
            } else {
                $alamatFinal = $alamatLangsung;
            }

            // (opsional) preferensi
            $preferensiHari = $this->pick($response, $this->H['hari']);
            $preferensiJam = $this->pick($response, $this->H['jam']);
            $bimbaUnit = $this->pick($response, $this->H['bimba_unit']);
            // 3) Values untuk upsert
            $values = [
                'nama' => $namaSheet,
                'no_telp' => $noTelpSheet,
                'email' => $this->pick($response, $this->H['email']),
                // NOTE: kalau kolom di DB bertipe datetime, ubah ke ->toDateTimeString()
                'waktu_submit' => $waktuSubmit?->toDateString(),
                'orangtua' => $this->pick($response, $this->H['orangtua']),
                'tgl_mulai' => $this->parseDateFlexible($this->pick($response, $this->H['tgl_mulai'])),
                'kelas' => $this->pick($response, $this->H['kelas']),
                'tgl_lahir' => $this->parseDateFlexible($this->pick($response, $this->H['tgl_lahir'])),
                'usia' => $this->toIntSafe($this->pick($response, $this->H['usia'])),
                'guru_trial' => $this->pick($response, $this->H['guru_trial']),
                'info' => $this->pick($response, $this->H['info']),
                'alamat' => $alamatFinal,
                'bimba_unit' => $this->pick($response, $this->H['bimba_unit']),
                // Jika tabel murid_trials punya kolom tambahan ini, boleh ikut isi:
                // 'preferensi_hari' => $preferensiHari,
                // 'preferensi_jam'  => $preferensiJam,
            ];

            // 4) Kriteria unik
            $attributes = [
                'nama' => $values['nama'],
                'no_telp' => $values['no_telp'],
            ];
            // fallback hitung usia jika tidak ada
            if (empty($values['usia']) && !empty($values['tgl_lahir'])) {
    // pilih tanggal referensi yang paling masuk akal
    $refDate = $values['tgl_mulai']
        ?? ($waktuSubmit?->toDateString())
        ?? now('Asia/Jakarta')->toDateString();

    try {
        $lahir = \Illuminate\Support\Carbon::parse($values['tgl_lahir']);
        $ref   = \Illuminate\Support\Carbon::parse($refDate);
        $diff  = $lahir->diff($ref);

        // isi usia tahun
        $values['usia'] = max(0, $diff->y);

        // kalau mau simpan detail bulan juga (kalau kolomnya ada)
        if (\Illuminate\Support\Facades\Schema::hasColumn('murid_trials', 'usia_bulan_total')) {
            $values['usia_bulan_total'] = max(0, $lahir->diffInMonths($ref));
        }

        // kalau mau simpan string langsung (misal di kolom terpisah)
        if (\Illuminate\Support\Facades\Schema::hasColumn('murid_trials', 'usia_label')) {
            $values['usia_label'] = sprintf('%d Tahun, %d Bulan', $diff->y, $diff->m);
        }

    } catch (\Throwable $e) {
        $values['usia'] = null;
        if (isset($values['usia_bulan_total'])) $values['usia_bulan_total'] = null;
        if (isset($values['usia_label'])) $values['usia_label'] = null;
    }
}


            try {
                if ($dry) {
                    $exists = MuridTrial::where($attributes)->exists();
                    $exists ? $updated++ : $imported++;
                } else {
                    $model = MuridTrial::updateOrCreate($attributes, $values);
                    $model->wasRecentlyCreated ? $imported++ : $updated++;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $this->error('Database Error pada baris #' . ($idx + 1) . ': ' . $e->getMessage());
                $skipped++;
            } catch (\Throwable $e) {
                $this->error('Error tak terduga baris #' . ($idx + 1) . ': ' . $e->getMessage());
                $skipped++;
            }
        }

        $this->info('Sinkronisasi selesai.');
        $this->info("Baru: $imported, Update: $updated, Lewat: $skipped");
        $this->line("Ringkasan: Insert=$imported, Update=$updated, Skip=$skipped");

        return self::SUCCESS;
    }

    // ===== Helpers =====

    /** Normalisasi string header: huruf kecil, hapus zero-width, samakan tanda baca & spasi */
    private function normHeader(string $s): string
    {
        $s = Str::of($s)
            ->lower()
            ->replaceMatches('/[\x{200B}\x{200C}\x{200D}\x{FEFF}\x{2060}]/u', '') // zero-width
            ->replace("\xC2\xA0", ' ') // NBSP
            ->replaceMatches('/[.,\/\\\\\-\_\:\;\|\(\)\[\]\{\}]+/u', ' ') // tanda baca -> spasi
            ->replaceMatches('/\s+/u', ' ')
            ->trim();
        return (string) $s;
    }

    /** Ambil nilai kolom dengan alias (mencocokkan pakai normHeader + contains) */
    private function pick(array $row, array $aliases, $default = null)
    {
        static $cacheKeyed = null;
        static $lastRef = null;

        // cache normalisasi key agar hemat
        if ($lastRef !== $row) {
            $norm = [];
            foreach ($row as $k => $v)
                $norm[$this->normHeader((string) $k)] = $v;
            $cacheKeyed = $norm;
            $lastRef = $row;
        }
        $normed = $cacheKeyed;

        foreach ($aliases as $alias) {
            $a = $this->normHeader((string) $alias);
            foreach ($normed as $k => $v) {
                if (Str::contains($k, $a)) {
                    return is_string($v) ? trim($v) : $v;
                }
            }
        }
        return $default;
    }

    /** Rakit alamat dari bagian-bagian jika kolom Alamat kosong */
    private function buildAddressFromParts(array $row): ?string
    {
        $parts = [
            $this->pick($row, ['No . Rumah', 'No Rumah', 'Nomor Rumah']),
            $this->pick($row, ['Rt', 'RT']),
            $this->pick($row, ['Rw', 'RW']),
            $this->pick($row, ['Kelurahan', 'Desa']),
            $this->pick($row, ['Kecamatan']),
            $this->pick($row, ['Kodya / Kab', 'Kodya', 'Kabupaten', 'Kota']),
            $this->pick($row, ['Provinsi']),
            $this->pick($row, ['Kode pos', 'Kodepos']),
        ];
        $parts = array_values(array_filter(array_map(fn($v) => is_string($v) ? trim($v) : $v, $parts)));
        return empty($parts) ? null : implode(', ', $parts);
    }

    /** Terima dd/mm/YYYY, dd-mm-YYYY, YYYY-mm-dd, m/d/Y, hingga serial date (angka). */
    private function parseDateFlexible($value): ?string
    {
        if ($value === null || $value === '')
            return null;

        // Serial date sheet (angka)
        if (is_numeric($value)) {
            // Google/Excel serial -> origin 1899-12-30
            $base = Carbon::create(1899, 12, 30, 0, 0, 0, 'Asia/Jakarta');
            return $base->copy()->addDays((int) $value)->toDateString();
        }

        $value = trim((string) $value);

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'];
        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value, 'Asia/Jakarta')->toDateString();
            } catch (\Throwable $e) {
            }
        }

        // fallback parser bebas (mencakup "26 April 2025" dsb.)
        try {
            return Carbon::parse($value, 'Asia/Jakarta')->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Versi DateTime (punya jam-menit-detik). Menangani H:i dan H.i */
    private function parseDateTimeFlexible($value): ?Carbon
    {
        if ($value === null || $value === '')
            return null;

        // Serial dengan pecahan hari (jam) dari Excel bisa muncul
        if (is_numeric($value)) {
            $base = Carbon::create(1899, 12, 30, 0, 0, 0, 'Asia/Jakarta');
            $days = floor((float) $value);
            $fraction = (float) $value - $days;
            $seconds = (int) round($fraction * 86400);
            return $base->copy()->addDays((int) $days)->addSeconds($seconds);
        }

        $value = trim((string) $value);

        $formats = [
            // titik
            'd/m/Y H.i.s',
            'd-m-Y H.i.s',
            'Y-m-d H.i.s',
            'm/d/Y H.i.s',
            'd/m/Y H.i',
            'd-m-Y H.i',
            'Y-m-d H.i',
            'm/d/Y H.i',
            // titik + milidetik (opsional)
            'd/m/Y H.i.s.u',
            'd-m-Y H.i.s.u',
            'Y-m-d H.i.s.u',
            'm/d/Y H.i.s.u',
            // titik tanpa tahun 4 digit (jarang)
            // titik selesai
            'd/m/Y H:i:s',
            'd-m-Y H:i:s',
            'Y-m-d H:i:s',
            'm/d/Y H:i:s',
            'd/m/Y H:i',
            'd-m-Y H:i',
            'Y-m-d H:i',
            'm/d/Y H:i',
        ];
        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value, 'Asia/Jakarta');
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value, 'Asia/Jakarta');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Normalisasi nomor HP Indonesia -> +62XXXXXXXX */
    private function normalizePhoneID(string $raw): ?string
{
    $raw = trim($raw);

    // Kasus-kasus yang berarti "belum ada nomor"
    if ($raw === '' || 
        $raw === '-' || 
        $raw === '—' || 
        $raw === '–' || 
        strcasecmp($raw, 'tidak ada') === 0 || 
        strcasecmp($raw, 'n/a') === 0) {
        return null;
    }

    $s = preg_replace('/[^0-9+]/', '', $raw);

    if ($s === '') return null;

    if (Str::startsWith($s, '08')) {
        $s = '+62' . substr($s, 1);
    } elseif (Str::startsWith($s, '8')) {
        $s = '+62' . $s;
    } elseif (Str::startsWith($s, '62')) {
        $s = '+' . $s;
    } elseif (!Str::startsWith($s, '+62')) {
        return null;
    }

    $s = preg_replace('/^(\+62)0+/', '+62', $s);

    return strlen($s) >= 12 ? $s : null;
}

    private function toIntSafe($v): ?int
    {
        if ($v === null || $v === '')
            return null;
        if (is_numeric($v))
            return (int) $v;
        $v = preg_replace('/[^\d]/', '', (string) $v);
        return $v !== '' ? (int) $v : null;
    }
}
