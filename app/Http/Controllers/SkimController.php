<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skim;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SkimImport;

class SkimController extends Controller
{
    private $jabatanOptions = [
        'Kepala Unit', 'Asisten KU', 'Guru', 'Asisten Guru', 
        'Admin', 'Bendahara', 'Satpam', 'Office Boy', 'Office Girl'
    ];

    private $statusOptions = [
        'Aktif', 'Magang', 'Resign'
    ];

    public function index()
    {
        $skims = Skim::all();
        return view('skim.index', compact('skims'));
    }

    public function create()
    {
        $jabatanOptions = $this->jabatanOptions;
        $statusOptions = $this->statusOptions;
        return view('skim.create', compact('jabatanOptions', 'statusOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jabatan' => 'required|string|max:100',
            'masa_kerja' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'tunj_pokok' => 'nullable|numeric',
            'harian' => 'nullable|numeric',
            'fungsional' => 'nullable|numeric',
            'kesehatan' => 'nullable|numeric',
            'tunj_khusus' => 'nullable|numeric',
        ]);

        $masaKerja = $request->masa_kerja; // Simpan langsung string apa adanya

        $thp = ($request->tunj_pokok ?? 0) + ($request->harian ?? 0) + ($request->fungsional ?? 0) + ($request->kesehatan ?? 0);
        $jumlah = $thp + ($request->tunj_khusus ?? 0);

        Skim::create([
            'jabatan' => $request->jabatan,
            'masa_kerja' => $masaKerja,
            'status' => $request->status,
            'tunj_pokok' => $request->tunj_pokok ?? 0,
            'harian' => $request->harian ?? 0,
            'fungsional' => $request->fungsional ?? 0,
            'kesehatan' => $request->kesehatan ?? 0,
            'thp' => $thp,
            'tunj_khusus' => $request->tunj_khusus ?? 0,
            'jumlah' => $jumlah,
        ]);

        return redirect()->back()->with('success', 'Data skim berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $skim = Skim::findOrFail($id);
        $jabatanOptions = $this->jabatanOptions;
        $statusOptions = $this->statusOptions;
        return view('skim.edit', compact('skim', 'jabatanOptions', 'statusOptions'));
    }

    public function update(Request $request, $id)
{
    $skim = Skim::findOrFail($id);

    $request->validate([
        'jabatan' => 'required|string|max:100',
        'masa_kerja' => 'nullable|string|max:50',
        'status' => 'nullable|string|max:50',
        'tunj_pokok' => 'nullable|numeric',
        'harian' => 'nullable|numeric',
        'fungsional' => 'nullable|numeric',
        'kesehatan' => 'nullable|numeric',
        'tunj_khusus' => 'nullable|numeric',
    ]);

    $masaKerja = $request->masa_kerja; // contoh: '< 24 Bulan' atau '>= 24 Bulan'

    $thp = ($request->tunj_pokok ?? 0) + ($request->harian ?? 0) + ($request->fungsional ?? 0) + ($request->kesehatan ?? 0);
    $jumlah = $thp + ($request->tunj_khusus ?? 0);

    $skim->update([
        'jabatan' => $request->jabatan,
        'masa_kerja' => $masaKerja,
        'status' => $request->status,
        'tunj_pokok' => $request->tunj_pokok ?? 0,
        'harian' => $request->harian ?? 0,
        'fungsional' => $request->fungsional ?? 0,
        'kesehatan' => $request->kesehatan ?? 0,
        'thp' => $thp,
        'tunj_khusus' => $request->tunj_khusus ?? 0,
        'jumlah' => $jumlah,
    ]);

    // Propagate perubahan SKIM ke tabel pendapatan_tunjangan
    $this->propagateSkimToTunjangan($skim);

    return redirect()->route('skim.index')->with('success', 'Data skim berhasil diupdate!');
}

/**
 * Update semua PendapatanTunjangan yang relevan dengan SKIM yang diubah.
 */
private function propagateSkimToTunjangan(Skim $skim)
{
    // Cari semua pendapatan dengan jabatan dan status yang sama.
    // Kita akan seleksi lagi berdasarkan kategori masa_kerja per record.
    $rows = \App\Models\PendapatanTunjangan::where('jabatan', $skim->jabatan)
        ->where('status', $skim->status)
        ->get();

    foreach ($rows as $row) {
        // Ambil masa_kerja dari record pendapatan (kemungkinan disimpan sebagai angka bulan atau string)
        $mk = $row->masa_kerja;

        // Normalisasi: jika numeric-like, cast ke int; jika string dengan angka di dalam, coba ekstrak angka
        $masaKerjaBulan = null;
        if ($mk === null) {
            // jika null, Anda bisa memutuskan untuk skip; di sini kita skip update jika tidak tahu masa kerja
            continue;
        } elseif (is_numeric($mk)) {
            $masaKerjaBulan = (int) $mk;
        } elseif (preg_match('/\d+/', $m, $m)) {
            $masaKerjaBulan = (int) $m[0];
        } else {
            // jika tidak bisa ditafsirkan, skip (atau Anda bisa set default)
            continue;
        }

        // Tentukan kategori sesuai logika yang sama dengan hitungSkim:
        $kategori = ($masaKerjaBulan < 24) ? '< 24 Bulan' : '>= 24 Bulan';

        // Jika kategori ini cocok dengan masa_kerja pada SKIM yang diupdate -> update row
        if ($kategori === $skim->masa_kerja) {
            $newThp = $skim->thp;

            // Cast field numeric agar safe
            $kerajinan   = floatval($row->kerajinan ?? 0);
            $english     = floatval($row->english ?? 0);
            $mentor      = floatval($row->mentor ?? 0);
            $kekurangan  = floatval($row->kekurangan ?? 0);
            $tj_keluarga = floatval($row->tj_keluarga ?? 0);
            $lain_lain   = floatval($row->lain_lain ?? 0);

            $newTotal = floatval($newThp) + $kerajinan + $english + $mentor + $kekurangan + $tj_keluarga + $lain_lain;

            $row->update([
                'thp' => $newThp,
                'total' => $newTotal,
            ]);
        }
    }

    // Jika dataset besar: pertimbangkan menggunakan Job/Queue untuk mengerjakan secara background
    // contoh: dispatch(new UpdatePendapatanFromSkimJob($skim));
}


    public function destroy($id)
    {
        $skim = Skim::findOrFail($id);
        $skim->delete();
        return redirect()->route('skim.index')->with('success','Data skim berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        Excel::import(new SkimImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data berhasil diimport!');
    }
}
