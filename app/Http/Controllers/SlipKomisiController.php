<?php

namespace App\Http\Controllers;

use App\Models\Komisi;
use App\Models\Unit;
use App\Models\Profile;             // ← TAMBAH INI
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;      // ← TAMBAH INI

class SlipKomisiController extends Controller
{
    public function index(Request $request)
    {
        $bulan    = (int) $request->get('bulan', date('n'));
        $tahun    = (int) $request->get('tahun', date('Y'));
        $staff_id = $request->get('staff_id');

        // AMBIL DATA KOMISI + PROFILE (PASTIKAN TGL_MASUK IKUT)
        $data = Komisi::where('bulan', $bulan)
                      ->where('tahun', $tahun)
                      ->with([
                          'profile:id,nama,nik,no_karyawan,tgl_masuk,bank,no_rekening,atas_nama_rekening',
                          'unit'
                      ])
                      ->orderBy('nomor_urut')
                      ->get();

        // FALLBACK: kalau relasi profile kosong, coba cari profile berdasarkan NAMA
        foreach ($data as $row) {
            if (!$row->profile && $row->nama) {
                $profile = Profile::whereRaw('LOWER(TRIM(nama)) = ?', [strtolower(trim($row->nama))])->first();
                if ($profile) {
                    $row->setRelation('profile', $profile);
                }
            }
        }

        // DATA STAFF YANG DIPILIH
        $selectedKomisi = null;
        if ($staff_id) {
            $selectedKomisi = Komisi::with(['profile', 'unit'])->find($staff_id);

            if ($selectedKomisi) {
                // fallback relasi profile jika belum keisi profile_id
                if (!$selectedKomisi->profile && $selectedKomisi->nama) {
                    $profile = Profile::whereRaw('LOWER(TRIM(nama)) = ?', [strtolower(trim($selectedKomisi->nama))])->first();
                    if ($profile) {
                        $selectedKomisi->setRelation('profile', $profile);
                    }
                }

                // pastikan tgl_masuk berupa Carbon atau null
                if ($selectedKomisi->profile && $selectedKomisi->profile->tgl_masuk) {
                    try {
                        $selectedKomisi->profile->tgl_masuk =
                            Carbon::parse($selectedKomisi->profile->tgl_masuk);
                    } catch (\Exception $e) {
                        $selectedKomisi->profile->tgl_masuk = null;
                    }
                }
            }
        }

        // === CARI UNIT OPTIONS SAMA SEPERTI SEBELUMNYA ===
        $unitOptions = collect();
        if ($selectedKomisi && $selectedKomisi->departemen) {
            $depart = trim($selectedKomisi->departemen);
            $unitOptions = Unit::where('biMBA_unit', 'LIKE', "%{$depart}%")
                               ->orderBy('biMBA_unit')
                               ->get();
        }

        if ($unitOptions->isEmpty()) {
            $unitOptions = Unit::orderBy('biMBA_unit')->get();
        }

        $selectedUnit = null;
        if ($selectedKomisi && !empty($selectedKomisi->bimba_unit)) {
            $selectedUnit = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($selectedKomisi->bimba_unit))])->first();
        }
        if (!$selectedUnit) {
            $selectedUnit = $unitOptions->first();
        }

        $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        return view('slip-komisi.index', compact(
            'data', 'bulan', 'tahun', 'namaBulan', 'selectedKomisi', 'unitOptions', 'selectedUnit'
        ));
    }

    /**
     * Preview PDF untuk satu komisi (digunakan oleh iframe di modal).
     * Mendukung optional ?unit_id=123 untuk override unit yang digunakan di slip.
     */
    public function previewPDF(Request $request)
    {
        $staff_id = $request->query('staff_id');

        if (!$staff_id) {
            return '<h2 style="text-align:center;padding:150px;color:red;">Pilih staff terlebih dahulu!</h2>';
        }

        // Muat komisi + profile + unit
        $komisi = Komisi::with(['profile', 'unit'])->find($staff_id);
        if (!$komisi) {
            return '<h2 style="text-align:center;padding:150px;color:orange;">Data tidak ditemukan!</h2>';
        }

        // FALLBACK PENTING:
        // Kalau relasi profile tidak ada (profile_id null / tidak match),
        // coba cari profile berdasarkan nama di tabel komisi.
        if (!$komisi->profile && !empty($komisi->nama)) {
            $profile = Profile::where('nama', $komisi->nama)->first();
            if ($profile) {
                $komisi->setRelation('profile', $profile);
            }
        }

        $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember'];

        // === pilih unit yang dipakai di slip ===
        $selectedUnit = null;
        if (!empty($komisi->bimba_unit)) {
            $selectedUnit = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($komisi->bimba_unit))])->first();
        }
        if (!$selectedUnit && $komisi->unit) {
            $selectedUnit = $komisi->unit;
        }
        if (!$selectedUnit) {
            $selectedUnit = Unit::first();
        }

        // pastikan angka aman (optional, tapi bagus)
        $komisi->komisi_mb_bimba   = (int) ($komisi->komisi_mb_bimba ?? 0);
        $komisi->komisi_mt_bimba   = (int) ($komisi->komisi_mt_bimba ?? 0);
        $komisi->komisi_mb_english = (int) ($komisi->komisi_mb_english ?? 0);
        $komisi->komisi_mt_english = (int) ($komisi->komisi_mt_english ?? 0);
        $komisi->mb_insentif_ku    = (int) ($komisi->mb_insentif_ku ?? 0);
        $komisi->insentif_bimba    = (int) ($komisi->insentif_bimba ?? 0);
        $komisi->lebih_bimba       = (int) ($komisi->lebih_bimba ?? 0);
        $komisi->kurang_bimba      = (int) ($komisi->kurang_bimba ?? 0);

        $transfer_final =
              $komisi->komisi_mb_bimba
            + $komisi->komisi_mt_bimba
            + $komisi->komisi_mb_english
            + $komisi->komisi_mt_english
            + $komisi->mb_insentif_ku
            + $komisi->insentif_bimba
            + ($komisi->insentif_tambahan ?? 0)
            + ($komisi->lebih_bayar ?? 0)
            + $komisi->lebih_bimba
            - ($komisi->kurang_bayar ?? 0)
            - $komisi->kurang_bimba;

        $total_insentif =
              $komisi->mb_insentif_ku
            + $komisi->insentif_bimba
            + ($komisi->insentif_tambahan ?? 0);

        $unit_snapshot       = $komisi->bimba_unit ?? null;
        $no_cabang_snapshot  = $komisi->no_cabang ?? null;
        $unit_relasi         = $selectedUnit;
        $units               = Unit::all();

        $logoPath = public_path('template/img/logoslip.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $pdf = Pdf::loadView('slip-komisi.pdf-slip-komisi', compact(
            'komisi',
            'namaBulan',
            'unit_relasi',
            'unit_snapshot',
            'no_cabang_snapshot',
            'units',
            'logoBase64',
            'total_insentif',
            'transfer_final'
        ))->setPaper('a5', 'landscape');

        $filename = "Slip_Komisi_" . preg_replace('/[^A-Za-z0-9\-]/', '_', $komisi->profile?->nama ?? 'Staff')
                  . "_{$namaBulan[$komisi->bulan-1]}_{$komisi->tahun}.pdf";

        return $pdf->stream($filename);
    }
}
