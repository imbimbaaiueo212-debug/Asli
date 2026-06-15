<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Log;
use App\Models\BukuInduk;
use App\Models\Penerimaan;
use App\Models\PindahGolongan;
use Carbon\Carbon;
use App\Traits\HasBulanIndo;

class SyncPindahGolonganSheet extends Command
{
    use HasBulanIndo;

    protected $signature = 'pindah-golongan:sheet';
    protected $description = 'Sinkronisasi data pindah golongan dari Google Sheets ke Laravel.';

    public function handle()
{
    $spreadsheetId = config('services.google.sheets.spreadsheet_id');

    // 🔥 PAKSA LANGSUNG
    $sheetNames = collect([
        'Pindah Golongan Responses'
    ]);

    $total = 0;
    foreach ($sheetNames as $name) {
        $this->info("Processing sheet: {$name}");
        $total += $this->processSheet($spreadsheetId, $name);
    }

    $this->info("✅ Total baris diproses: {$total}");
    return self::SUCCESS;
}

   private function processSheet(string $spreadsheetId, string $sheetName): int
{
    $rows = Sheets::spreadsheet($spreadsheetId)->sheet($sheetName)->get()->toArray();
    if (empty($rows)) return 0;

    // --- Normalisasi header ---
    $rawHeader = $rows[0];
    $norm = function ($s) {
        $s = (string)$s;
        $s = str_replace("\xC2\xA0", ' ', $s);
        $s = trim($s);
        $s = preg_replace('/\s+/u', ' ', $s);
        $s = preg_replace('/[()]/u', '', $s);
        return mb_strtolower($s);
    };

    $header = array_map($norm, $rawHeader);
    $data   = array_slice($rows, 1);

    // helper cari index
    $find = function(array $aliases) use ($header, $norm) {
        foreach ($aliases as $a) {
            $i = array_search($norm($a), $header, true);
            if ($i !== false) return $i;
        }
        return false;
    };

    // =========================
    // INDEX KOLOM
    // =========================
    $iNim = $find([
        'NIM',
        'No Induk',
        'Nomor Induk',
        'NIM - Nama Lengkap'
    ]);

    $iNama = $find([
        'Nama Lengkap',
        'Nama',
        'Nama Murid'
    ]);

    $iGolBaru    = $find(['Golongan Baru','Gol Baru','Golongan']);
    $iKdBaru     = $find(['Kode Baru (KD Baru)','Kode Baru','KD Baru','Kode']);
    $iSppBaru    = $find(['SPP Baru (Nominal Angka)','SPP Baru','Nominal SPP','Tarif SPP']);
    $iTglEf      = $find(['Tanggal Efektif Pindah Golongan','Tanggal Efektif','Tgl Efektif']);
    $iAlasan     = $find(['Alasan Pindah','Alasan']);
    $iKeterangan = $find(['Keterangan','Keterangan Pindah','Catatan']);
    $iStatus     = $find(['Status']);

    if ($iGolBaru === false || $iKdBaru === false || $iSppBaru === false) {
        Log::warning('Kolom wajib tidak ditemukan untuk sheet '.$sheetName, [
            'header' => $header
        ]);
        return 0;
    }

    $count = 0;

    foreach ($data as $rIdx => $row) {
        $get = fn($i) => ($i !== false && array_key_exists($i, $row)) ? $row[$i] : null;

        $nimRaw  = (string) $get($iNim);
        $namaRaw = (string) $get($iNama);

        $nim  = null;
        $nama = null;

        // =========================
        // PARSING NIM + NAMA
        // =========================
        if ($nimRaw) {
            // ambil angka saja
            $nim = preg_replace('/\D/', '', $nimRaw);

            if ($nim !== '') {
                $nim = str_pad($nim, 9, '0', STR_PAD_LEFT);
            }

            // ambil nama dari gabungan
            $nama = trim(preg_replace('/^[0-9\s\-]+/', '', $nimRaw));
        }

        // fallback ke nama jika nim kosong
        if (!$nim && $namaRaw) {
            $nama = trim($namaRaw);
        }

        if (!$nim && !$nama) {
            $this->warn("Baris ".($rIdx+2)." dilewati: NIM & Nama kosong.");
            continue;
        }

        // =========================
        // SKIP STATUS
        // =========================
        $statusVal = strtoupper(trim((string) $get($iStatus)));
        if ($iStatus !== false && in_array($statusVal, ['PROCESSED','DONE','SELESAI'], true)) continue;

        // =========================
        // DATA BARU
        // =========================
        $golBaru = trim((string) $get($iGolBaru));
        $kdBaru  = trim((string) $get($iKdBaru));

        $sppRaw  = (string) $get($iSppBaru);
        $sppRaw  = str_replace(["\xC2\xA0", ' '], '', $sppRaw);
        $sppBaru = (float) preg_replace('/[^\d]/', '', $sppRaw);

        $tglEf      = $get($iTglEf);
        $alasan     = $iAlasan     !== false ? trim((string) $get($iAlasan))     : null;
        $keterangan = $iKeterangan !== false ? trim((string) $get($iKeterangan)) : null;

        if ($golBaru === '' || $kdBaru === '' || $sppBaru <= 0) {
            $this->warn("Baris ".($rIdx+2)." dilewati (data wajib tidak valid).");
            continue;
        }

        // =========================
        // CARI DATA DB
        // =========================
        $buku = null;

        if ($nim) {
            $buku = BukuInduk::where('nim', $nim)->first();
        }

        if (!$buku && $nama) {
            $buku = BukuInduk::where('nama', 'like', "%{$nama}%")->first();
        }

        if (!$buku) {
            $this->warn("Data tidak ditemukan (NIM: {$nim}, Nama: {$nama})");
            continue;
        }

        // =========================
        // INSERT
        // =========================
        PindahGolongan::create([
            'nim'  => $buku->nim,
            'nama' => $buku->nama,
            'guru' => $buku->guru,
            'gol'  => $buku->gol,
            'kd'   => $buku->kd,
            'spp'  => $buku->spp,

            'bimba_unit' => $buku->bimba_unit ?? null,
            'no_cabang'  => $buku->no_cabang ?? null,

            'gol_baru' => $golBaru,
            'kd_baru'  => $kdBaru,
            'spp_baru' => $sppBaru,
            'tanggal_pindah_golongan' => $this->parseDateAny($tglEf) ?? now(),

            'keterangan'    => $keterangan,
            'alasan_pindah' => $alasan,
        ]);

        // =========================
        // UPDATE BUKU INDUK
        // =========================
        $buku->update([
            'gol' => $golBaru,
            'kd'  => $kdBaru,
            'spp' => $sppBaru,
        ]);

        // =========================
        // UPDATE PENERIMAAN
        // =========================
        $efDate = $this->parseDateAny($tglEf) ?? now();
        $ef     = Carbon::parse($efDate)->startOfMonth();

        $ids = Penerimaan::where('nim', $buku->nim)
            ->get()
            ->filter(function($rec) use ($ef) {
                $tahun = (int) $rec->tahun;
                $bulan = $this->bulanAngka($rec->bulan ?? '');

                return ($tahun > $ef->year) ||
                       ($tahun == $ef->year && $bulan >= $ef->month);
            })
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            Penerimaan::whereIn('id', $ids)->update([
                'gol' => $golBaru,
                'kd'  => $kdBaru,
            ]);
        }

        // =========================
        // MARK PROCESSED
        // =========================
        if ($iStatus !== false) {
            $rowNumber = $rIdx + 2;
            $colLetter = $this->columnLetter($iStatus);

            Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheetName)
                ->range("{$colLetter}{$rowNumber}")
                ->update([['Processed']]);
        }

        $count++;
    }

    return $count;
}





    private function columnLetter($index): string
    {
        $index++;
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = (int)(($index - $mod) / 26);
            $index = $index > 0 ? $index - 1 : 0;
        }
        return $letters;
    }

    private function parseDateAny($value): ?string
    {
        if (!$value) return null;
        try {
            if (is_numeric($value)) {
                return Carbon::create(1899,12,30)->addDays((int)$value)->toDateString();
            }
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
