<?php

namespace App\Observers;

use App\Models\Profile;
use App\Models\PendapatanTunjangan;
use App\Models\PotonganTunjangan;
use App\Models\PembayaranTunjangan;
use App\Models\ImbalanRekap;
use App\Models\DurasiKegiatan;
use App\Models\AbsensiRelawan;
use App\Models\Penerimaan;
use App\Models\BukuInduk;
use App\Models\Unit;
use App\Models\HargaSaptataruna;
use App\Models\RekapProgresif;
use App\Services\SkimTHPService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProfileObserver
{
    public function created(Profile $profile)
{
    // Reload model supaya data terbaru (termasuk masa_kerja yang dihitung di controller)
    $profile->refresh();

    // 1. Cek apakah profile aktif
    $statusLower = strtolower(trim($profile->status_karyawan ?? ''));
    $isActive = !in_array($statusLower, ['resign', 'keluar', 'pensiun', 'non aktif', 'nonaktif']);

    if (!$isActive) {
        $this->syncImbalanRekap($profile);
        return;
    }

    // 2. Cek jabatan: guru atau kepala unit
    $jabatanLower = strtolower(trim($profile->jabatan ?? ''));

    $isGuru = str_contains($jabatanLower, 'guru') ||
              str_contains($jabatanLower, 'pengajar') ||
              str_contains($jabatanLower, 'tutor');

    $isKepalaUnit = str_contains($jabatanLower, 'kepala unit') ||
                    str_contains($jabatanLower, 'kepala bimba') ||
                    str_contains($jabatanLower, 'kepala sekolah') ||
                    $jabatanLower === 'ku';

    if (!$isGuru && !$isKepalaUnit) {
        $this->syncImbalanRekap($profile);
        return;
    }

    // 3. Ambil bulan berjalan
    $now       = Carbon::now();
    $bulanNama = $this->getIndonesianMonthName($now->format('m'));
    $tahun     = $now->year;

    // Cek duplikat
    if (RekapProgresif::where('nama', $profile->nama)
        ->where('bulan', $bulanNama)
        ->where('tahun', $tahun)
        ->exists()) {
        $this->syncImbalanRekap($profile);
        return;
    }

    // 4. Normalisasi bimba_unit (penting untuk exact match)
    $bimbaUnit = trim($profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit ?? '');
    $bimbaUnit = preg_replace('/\s+/', ' ', $bimbaUnit); // hilangkan spasi ganda

    // 5. Hitung masa kerja (prioritas kolom, fallback manual)
    $masaKerjaBulan = (int) ($profile->masa_kerja ?? 0);

    if ($masaKerjaBulan <= 0 && $profile->tgl_masuk) {
        $start = Carbon::parse($profile->tgl_masuk);
        $end   = $profile->tgl_resign 
            ? Carbon::parse($profile->tgl_resign) 
            : ($profile->tgl_non_aktif 
                ? Carbon::parse($profile->tgl_non_aktif) 
                : Carbon::today());

        if (!$start->isFuture()) {
            $masaKerjaBulan = $start->diffInMonths($end);
        }
    }

    $masaKerjaText = $this->formatMasaKerja($masaKerjaBulan);

    Log::debug("Masa kerja dihitung untuk {$profile->nama}: {$masaKerjaText} (raw: {$masaKerjaBulan} bulan)");

    // 6. Hitung data progresif
    $paidQuery = Penerimaan::whereRaw('LOWER(bulan) = ?', [strtolower($bulanNama)])
        ->where('tahun', $tahun)
        ->where('spp', '>', 0);

    if ($isKepalaUnit) {
        $paidQuery->where('bimba_unit', $bimbaUnit);  // ← EXACT MATCH, sama seperti store()
    } else {
        $paidQuery->where('guru', $profile->nama);
    }

    $paidNims = $paidQuery->distinct()->pluck('nim');

    $totalMuridBayar = $paidNims->count();

    $totalSPP = Penerimaan::whereIn('nim', $paidNims)
        ->whereRaw('LOWER(bulan) = ?', [strtolower($bulanNama)])
        ->where('tahun', $tahun)
        ->sum('spp');

    // Total murid aktif
    $totalMuridQuery = BukuInduk::where('status', 'aktif');

    if ($isKepalaUnit) {
        $totalMuridQuery->where('bimba_unit', $bimbaUnit);  // ← EXACT MATCH
    } else {
        $totalMuridQuery->where('guru', $profile->nama);
    }

    $totalMurid = $totalMuridQuery->count();

    $hargaS3 = HargaSaptataruna::whereRaw("LOWER(nama)='s3'")
        ->value('harga') ?? 300000;

    $totalFM = round(($totalSPP / $hargaS3) * 1.17, 2);

    $progresif = $this->calculateProgresifTariff($totalFM, $profile->jabatan);

    // Log detail untuk Kepala Unit (debug perbedaan)
    if ($isKepalaUnit) {
        Log::debug("Rekap Progresif Auto (Observer) - Kepala Unit", [
            'nama'         => $profile->nama,
            'bimba_unit'   => $bimbaUnit,
            'total_murid'  => $totalMurid,
            'total_spp'    => $totalSPP,
            'total_fm'     => $totalFM,
            'progresif'    => $progresif,
            'range_tarif'  => floor($totalFM) . ' → ' . $progresif,
        ]);
    }

    // 7. Ambil no_cabang
    $unit = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($bimbaUnit))])->first();

    // 8. Siapkan data lengkap
    $data = [
        'nama'          => $profile->nama,
        'jabatan'       => $profile->jabatan,
        'status'        => $profile->status_karyawan,
        'departemen'    => $profile->departemen,
        'masa_kerja'    => $masaKerjaText,

        'bulan'         => $bulanNama,
        'tahun'         => $tahun,

        'spp_bimba'     => (float) $totalSPP,
        'am1'           => (int) $totalMurid,
        'am2'           => (int) $totalMuridBayar,

        'total_fm'      => $totalFM,
        'progresif'     => (float) $progresif,

        'spp_english'   => 0.0,
        'komisi'        => 0.0,

        'dibayarkan'    => (float) $progresif,

        'bimba_unit'    => $bimbaUnit,
        'no_cabang'     => $unit?->no_cabang ?? null,

        'created_at'    => now(),
        'updated_at'    => now(),
    ];

    try {
        RekapProgresif::create($data);

        Log::info("Auto-created RekapProgresif untuk profile baru", [
            'profile_id'   => $profile->id,
            'nama'         => $profile->nama,
            'jabatan'      => $profile->jabatan,
            'masa_kerja'   => $masaKerjaText,
            'bulan'        => $bulanNama,
            'tahun'        => $tahun,
            'total_fm'     => $totalFM,
            'progresif'    => $progresif,
            'total_murid'  => $totalMurid,
            'spp'          => $totalSPP,
        ]);
    } catch (\Exception $e) {
        Log::error("Gagal auto-create RekapProgresif", [
            'profile_id' => $profile->id,
            'nama'       => $profile->nama,
            'error'      => $e->getMessage(),
            'trace'      => $e->getTraceAsString(),
        ]);
    }

    // 9. Sync imbalan
    $this->syncImbalanRekap($profile);
}

    public function updated(Profile $profile)
    {
        // Kode asli kamu – tidak diubah
        $thp = SkimTHPService::getTHP(
            $profile->jabatan ?? '',
            $profile->status_karyawan ?? '',
            $profile->masa_kerja ?? 0
        );

        $pendapatans = PendapatanTunjangan::where('nama', $profile->nama)->get();
        foreach ($pendapatans as $pendapatan) {
            $total = $thp
                + ($pendapatan->kerajinan ?? 0)
                + ($pendapatan->english ?? 0)
                + ($pendapatan->mentor ?? 0)
                - ($pendapatan->kekurangan ?? 0)
                + ($pendapatan->tj_keluarga ?? 0)
                + ($pendapatan->lain_lain ?? 0);

            $pendapatan->update([
                'thp'        => $thp,
                'jabatan'    => $profile->jabatan,
                'status'     => $profile->status_karyawan,
                'departemen' => $profile->departemen,
                'masa_kerja' => $profile->masa_kerja,
                'total'      => $total,
            ]);
        }

        $potongan = PotonganTunjangan::where('nama', $profile->nama)->first();
        if ($potongan) {
            $potongan->status = $profile->status_karyawan;
            $potongan->alpa   = $profile->status_karyawan === 'Magang' ? 0 : $potongan->alpa;
            $potongan->save();
        }

        PembayaranTunjangan::where('nama', $profile->nama)
            ->update(['pendapatan' => $thp]);

        $this->syncImbalanRekap($profile);

        // Sync NIK & NAMA ke tabel lain (kode asli kamu)
        if ($profile->isDirty('nik') || $profile->isDirty('nama')) {
            $oldNik  = $profile->getOriginal('nik');
            $oldNama = $profile->getOriginal('nama');
            $newNik  = $profile->nik;
            $newNama = $profile->nama;

            AbsensiRelawan::where('nik', $oldNik)
                ->orWhere('nama_relawaan', $oldNama)
                ->update([
                    'nik'           => $newNik,
                    'nama_relawaan' => $newNama,
                ]);

            PendapatanTunjangan::where('nama', $oldNama)
                ->update(['nama' => $newNama]);

            PotonganTunjangan::where('nama', $oldNama)
                ->update(['nama' => $newNama]);

            PembayaranTunjangan::where('nama', $oldNama)
                ->update(['nama' => $newNama]);

            ImbalanRekap::whereNull('profile_id')
                ->where('nama', $oldNama)
                ->update(['nama' => $newNama]);
        }
    }

    public function deleted(Profile $profile)
    {
        ImbalanRekap::where('profile_id', $profile->id)->delete();
        ImbalanRekap::where('nama', $profile->nama)->whereNull('profile_id')->delete();
    }

    // Method syncImbalanRekap (kode asli kamu – tidak diubah)
    private function syncImbalanRekap(Profile $p)
    {
        try {
            $bulan = is_numeric($p->masa_kerja) ? (int) $p->masa_kerja : 0;
            $masaKerja = $this->formatMasaKerja($bulan);

            $waktuMgg = null;
            $waktuBln = null;
            $durasiKerja = null;

            if ($p->rb) {
                preg_match('/\d+/', trim($p->rb), $matches);
                $angka = $matches[0] ?? null;

                if ($angka) {
                    $durasi = DurasiKegiatan::where('waktu_mgg', 'like', "RB{$angka}")->first();
                    if ($durasi) {
                        $waktuMgg = $durasi->waktu_mgg;
                        $waktuBln = $durasi->waktu_bln;
                        $durasiKerja = $durasi->waktu_bln 
                            ? (int) preg_replace('/\D+/', '', $durasi->waktu_bln)
                            : null;
                    } else {
                        $waktuMgg = "RB{$angka}";
                    }
                } else {
                    $waktuMgg = $p->rb;
                }
            }

            $posisi = $p->jabatan ?? $p->posisi ?? null;
            $departemen = $p->unit ?? $p->departemen ?? 'biMBA-AIUEO';

            $imbalanPokok = (float) ($p->rp ?? 0);
            $imbalanLainnya = (float) ($p->tunjangan ?? 0);
            $totalImbalan = $imbalanPokok + $imbalanLainnya;
            $insentifMentor = (float) ($p->insentif ?? 0);
            $tambahanTransport = (float) ($p->transport ?? 0);

            $yangDibayarkan = 
                $totalImbalan + $insentifMentor + $tambahanTransport +
                (float) ($p->total_kelebihan ?? 0) - 
                (float) ($p->kekurangan ?? 0) - 
                (float) ($p->cicilan ?? 0);

            $data = [
                'profile_id' => $p->id,
                'nama' => $p->nama,
                'posisi' => $posisi,
                'status' => $p->status_karyawan ?? '-',
                'departemen' => $departemen,
                'masa_kerja' => $masaKerja,
                'waktu_mgg' => $waktuMgg,
                'waktu_bln' => $waktuBln,
                'durasi_kerja' => $durasiKerja,
                'persen' => 100,
                'ktr' => $p->ktr ?? null,
                'imbalan_pokok' => $imbalanPokok,
                'imbalan_lainnya' => $imbalanLainnya,
                'total_imbalan' => $totalImbalan,
                'insentif_mentor' => $insentifMentor,
                'keterangan_insentif' => $p->keterangan_insentif ?? null,
                'tambahan_transport' => $tambahanTransport,
                'at_hari' => $p->at_hari ?? null,
                'kekurangan' => (float) ($p->kekurangan ?? 0),
                'bulan_kekurangan' => $p->bulan_kekurangan ?? null,
                'keterangan_kekurangan' => $p->keterangan_kekurangan ?? null,
                'kelebihan' => (float) ($p->kelebihan ?? 0),
                'bulan_kelebihan' => $p->bulan_kelebihan ?? null,
                'cicilan' => (float) ($p->cicilan ?? 0),
                'keterangan_cicilan' => $p->keterangan_cicilan ?? null,
                'total_kelebihan' => (float) ($p->total_kelebihan ?? 0),
                'jumlah_murid' => (int) ($p->jumlah_murid ?? 0),
                'jumlah_spp' => (float) ($p->jumlah_spp ?? 0),
                'kekurangan_spp' => (float) ($p->kekurangan_spp ?? 0),
                'kelebihan_spp' => (float) ($p->kelebihan_spp ?? 0),
                'jumlah_bagi_hasil' => (float) ($p->jumlah_bagi_hasil ?? 0),
                'keterangan_bagi_hasil' => $p->keterangan_bagi_hasil ?? null,
                'yang_dibayarkan' => $yangDibayarkan,
                'catatan' => $p->catatan ?? null,
                'm1' => $p->m1 ?? null,
                'm2' => $p->m2 ?? null,
            ];

            ImbalanRekap::updateOrCreate(
                ['profile_id' => $p->id],
                $data
            );

            $this->hapusDuplikat($p->nama);

        } catch (\Exception $e) {
            Log::error('Sync ImbalanRekap gagal', [
                'profile_id' => $p->id,
                'nama' => $p->nama,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function hapusDuplikat($nama)
    {
        $rekaps = ImbalanRekap::where('nama', $nama)
            ->orderBy('profile_id', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        if ($rekaps->count() > 1) {
            $rekaps->skip(1)->each->delete();
        }
    }

    private function formatMasaKerja($bulan)
    {
        if ($bulan === null || $bulan < 0) return '-';
        $tahun = intdiv($bulan, 12);
        $sisaBulan = $bulan % 12;
        $parts = [];
        if ($tahun > 0) $parts[] = "$tahun tahun";
        if ($sisaBulan > 0) $parts[] = "$sisaBulan bulan";
        return $parts ? implode(' ', $parts) : '0 bulan';
    }

    private function getIndonesianMonthName(string $monthNumber): ?string
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        return $months[$monthNumber] ?? null;
    }

    private function calculateProgresifTariff($totalFM, $jabatan)
    {
        $progresifTariffs = [
            'GURU' => [
                [6,10,100000], [11,15,200000], [16,20,300000],
                [21,25,450000], [26,30,600000], [31,35,800000],
                [36,40,1000000], [41,45,1400000], [46,50,1500000],
                [51,55,1600000], [56,60,1800000], [61,65,2000000],
                [66,70,2100000], [71,75,2400000], [76,80,2600000]
            ],
            'KEPALA UNIT' => [
                [10,29,210000], [30,49,420000], [50,69,630000],
                [70,89,840000], [90,109,1050000], [110,129,1260000],
                [130,149,1470000], [150,169,1680000], [170,189,1890000],
                [190,209,2100000], [210,229,2310000], [230,249,2520000]
            ],
            'KEPALA UA' => [
                [250,269,2730000], [270,289,2940000], [290,309,3150000],
                [310,329,3360000], [330,349,3570000], [350,369,3780000],
                [370,389,3990000], [390,409,4200000], [410,429,4410000],
                [430,449,4620000], [450,469,4830000], [470,489,5040000]
            ]
        ];

        $jabatanUpper = strtoupper(trim($jabatan));

        if (!isset($progresifTariffs[$jabatanUpper])) {
            return 0;
        }

        $fm = floor($totalFM);

        foreach ($progresifTariffs[$jabatanUpper] as $range) {
            [$min, $max, $tarif] = $range;
            if ($fm >= $min && $fm <= $max) {
                return $tarif;
            }
        }

        $last = end($progresifTariffs[$jabatanUpper]);
        return $fm > $last[1] ? $last[2] : 0;
    }
}