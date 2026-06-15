<?php

namespace App\Http\Controllers;

use App\Models\Komisi;
use Illuminate\Http\Request;

class PembayaranKomisiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        // Eager load profile dan unit supaya view bisa memanggil:
        // $row->profile->nik, $row->bimba_unit, $row->no_cabang atau $row->unit->no_cabang
        $data = Komisi::where('bulan', $bulan)
                      ->where('tahun', $tahun)
                      ->with(['profile:id,nama,nik,no_karyawan', 'unit']) // sertakan kolom nik supaya tidak terpotong
                      ->orderBy('nomor_urut')
                      ->get();

        $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        return view('pembayaran-komisi.index', compact('data', 'bulan', 'tahun', 'namaBulan'));
    }

    public function savePembayaran(Request $request)
    {
        $komisi = Komisi::findOrFail($request->komisi_id);

        $insentif = (int) preg_replace('/\D/', '', $request->insentif ?? 0);
        $kurang   = (int) preg_replace('/\D/', '', $request->kurang ?? 0);
        $lebih    = (int) preg_replace('/\D/', '', $request->lebih ?? 0);
        $bulan    = $request->bulan;

        // HITUNG ULANG TRANSFER YANG BENAR
        $transfer = ($komisi->komisi_mb_bimba ?? 0)
                  + ($komisi->komisi_mt_bimba ?? 0)
                  + ($komisi->komisi_mb_english ?? 0)
                  + ($komisi->komisi_mt_english ?? 0)
                  + ($komisi->mb_insentif_ku ?? 0)
                  + $insentif + $lebih - $kurang;

        $komisi->update([
            'insentif_bimba'     => $insentif,
            'kurang_bimba'       => $kurang,
            'lebih_bimba'        => $lebih,
            'bulan_kurang_lebih' => $bulan,
            'transfer_bimba'     => $transfer,
        ]);

        // muat ulang relasi kecil-kecilan agar response berisi data unit/profile terbaru
        $komisi->load(['profile:id,nama,nik,no_karyawan', 'unit']);

        return response()->json([
            'success'     => true,
            'transfer'    => 'Rp ' . number_format($transfer, 0, ',', '.'),
            'transfer_val'=> $transfer,
            // tambahkan bimba_unit & no_cabang agar front-end bisa update tanpa reload
            'bimba_unit'  => $komisi->bimba_unit ?? ($komisi->unit->biMBA_unit ?? null),
            'no_cabang'   => $komisi->no_cabang ?? ($komisi->unit->no_cabang ?? null),
            'komisi'      => $komisi, // opsional: kembalikan objek komisi (berguna untuk debug/front-end)
        ]);
    }

    public function updateAdjustment(Request $request)
    {
        $komisi = Komisi::findOrFail($request->komisi_id);

        // PERBAIKAN KRUSIAL: JANGAN MASUKKAN mb_insentif_ku KE DALAM THP!
        // THP hanya komisi MB + MT saja (tanpa insentif KU)
        $thp = ($komisi->komisi_mb_bimba ?? 0) + ($komisi->komisi_mt_bimba ?? 0);
        // Jika ingin tambahkan English ke THP, uncomment:
        // $thp += ($komisi->komisi_mb_english ?? 0) + ($komisi->komisi_mt_english ?? 0);

        $insentif = (int) preg_replace('/\D/', '', $request->insentif ?? 0);
        $kurang   = (int) preg_replace('/\D/', '', $request->kurang ?? 0);
        $lebih    = (int) preg_replace('/\D/', '', $request->lebih ?? 0);
        $bulan    = $request->bulan ?: null;

        // Transfer = THP (tanpa KU) + semua insentif + adjustment
        $transfer = $thp 
                  + ($komisi->mb_insentif_ku ?? 0)        // ← Insentif KU masuk di sini (hanya sekali!)
                  + ($komisi->insentif_bimba ?? 0)
                  + $insentif
                  + $lebih
                  - $kurang;

        $komisi->update([
            'insentif_tambahan' => $insentif,
            'kurang_bayar'       => $kurang,
            'lebih_bayar'        => $lebih,
            'bulan_adjustment'   => $bulan,
            'transfer_bimba'     => $transfer,
        ]);

        // reload relasi untuk response
        $komisi->load(['profile:id,nama,nik,no_karyawan', 'unit']);

        return response()->json([
            'success'     => true,
            'transfer'    => 'Rp ' . number_format($transfer, 0, ',', '.'),
            'transfer_val'=> $transfer,
            'bimba_unit'  => $komisi->bimba_unit ?? ($komisi->unit->biMBA_unit ?? null),
            'no_cabang'   => $komisi->no_cabang ?? ($komisi->unit->no_cabang ?? null),
            'komisi'      => $komisi,
        ]);
    }
}
