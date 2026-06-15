<?php

namespace App\Http\Controllers;

use App\Models\PindahGolongan;
use Illuminate\Http\Request;
use App\Models\BukuInduk;
use App\Models\HargaSaptataruna;
use Carbon\Carbon;
use App\Models\Penerimaan;
use App\Traits\HasBulanIndo;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class PindahGolonganController extends Controller
{
    use HasBulanIndo;

    public function index(Request $request)
{
    if ($request->boolean('sync')) {
        Artisan::call('pindah-golongan:sheet');
        return redirect()->route('pindah-golongan.index')
            ->with('success', 'Sync dari Google Sheet sudah dijalankan.');
    }

    // Ambil input filter
    $filterNama   = $request->input('nama');
    $filterNim    = $request->input('nim');
    $filterUnit   = $request->input('unit');
    $filterCabang = $request->input('cabang');
    $tanggalDari  = $request->input('tanggal_dari');
    $tanggalSampai = $request->input('tanggal_sampai');

    // === DROPDOWN UNIT ===
    $units = collect(
        PindahGolongan::whereNotNull('bimba_unit')->distinct()->pluck('bimba_unit')
            ->merge(\App\Models\BukuInduk::whereNotNull('bimba_unit')->distinct()->pluck('bimba_unit'))
    )->filter()->unique()->sort()->values();

    // === DROPDOWN CABANG ===
    $cabangs = collect(
        PindahGolongan::whereNotNull('no_cabang')->distinct()->pluck('no_cabang')
            ->merge(\App\Models\BukuInduk::whereNotNull('no_cabang')->distinct()->pluck('no_cabang'))
    )->filter()->unique()->sort()->values();

    // === DROPDOWN NIM | NAMA: HANYA MURID YANG ADA DI TABEL PINDAH GOLONGAN ===
    $muridQuery = PindahGolongan::select('nim', 'nama')
        ->distinct()
        ->orderBy('nama');

    if ($filterUnit) {
        $muridQuery->where(function ($q) use ($filterUnit) {
            $q->where('bimba_unit', $filterUnit)
              ->orWhereHas('bukuInduk', fn($bq) => $bq->where('bimba_unit', $filterUnit));
        });
    }

    $muridOptions = $muridQuery->get();

    // === DROPDOWN NAMA LAMA (opsional, jika masih dipakai di view) ===
    $namaMurid = PindahGolongan::distinct()->orderBy('nama')->pluck('nama');

    // === QUERY UTAMA ===
    $query = PindahGolongan::with('bukuInduk')->latest();

    if ($filterNim) {
        $query->where('nim', $filterNim);
    } elseif ($filterNama) {
        $query->where('nama', 'like', "%{$filterNama}%");
    }

    if ($filterUnit) {
        $query->where(function ($q) use ($filterUnit) {
            $q->where('bimba_unit', $filterUnit)
              ->orWhereHas('bukuInduk', fn($bq) => $bq->where('bimba_unit', $filterUnit));
        });
    }

    if ($filterCabang) {
        $query->where(function ($q) use ($filterCabang) {
            $q->where('no_cabang', $filterCabang)
              ->orWhereHas('bukuInduk', fn($bq) => $bq->where('no_cabang', $filterCabang));
        });
    }

    if ($tanggalDari) {
        $query->whereDate('tanggal_pindah_golongan', '>=', $tanggalDari);
    }
    if ($tanggalSampai) {
        $query->whereDate('tanggal_pindah_golongan', '<=', $tanggalSampai);
    }

    $data = $query->get();

    return view('pindah_golongan.index', compact(
        'data',
        'units',
        'cabangs',
        'namaMurid',
        'muridOptions',
        'filterNim',
        'filterUnit'
    ));
}

    public function create()
    {
        $bukuInduk = BukuInduk::all();
        $hargaSaptataruna = HargaSaptataruna::all();
        return view('pindah_golongan.create', compact('bukuInduk', 'hargaSaptataruna'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'buku_induk_nim' => 'required|exists:buku_induk,nim',
            'gol_baru' => 'required|string|max:10',
            'kd_baru' => 'required|string|max:20',
            'spp_baru' => 'required|numeric',
            'tanggal_pindah_golongan' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
            'alasan_pindah' => 'nullable|string',
        ]);

        // Ambil data Buku Induk berdasarkan NIM
        $bukuInduk = BukuInduk::where('nim', $request->buku_induk_nim)->firstOrFail();
        $oldData = $bukuInduk->toArray();

        // Prepare payload untuk tabel pindah_golongan
        $pindahPayload = [
            'nim' => $bukuInduk->nim,
            'nama' => $bukuInduk->nama,
            'guru' => $bukuInduk->guru,
            'gol' => $bukuInduk->gol,
            'kd' => $bukuInduk->kd,
            'spp' => $bukuInduk->spp,
            'gol_baru' => $request->gol_baru,
            'kd_baru' => $request->kd_baru,
            'spp_baru' => $request->spp_baru,
            'tanggal_pindah_golongan' => $request->tanggal_pindah_golongan ?? now(),
            'keterangan' => $request->keterangan,
            'alasan_pindah' => $request->alasan_pindah,
        ];

        // Jika kolom tersedia di DB, tambahkan bimba_unit & no_cabang
        if (Schema::hasColumn('pindah_golongan', 'bimba_unit')) {
            $pindahPayload['bimba_unit'] = $bukuInduk->bimba_unit ?? null;
        }
        if (Schema::hasColumn('pindah_golongan', 'no_cabang')) {
            $pindahPayload['no_cabang'] = $bukuInduk->no_cabang ?? null;
        }

        // Simpan Pindah Golongan
        PindahGolongan::create($pindahPayload);

        // Update Buku Induk
        $bukuInduk->gol = $request->gol_baru;
        $bukuInduk->kd  = $request->kd_baru;
        $bukuInduk->spp = $request->spp_baru;
        $bukuInduk->save();

        // Update penerimaan downstream
        $efektif = Carbon::parse($request->tanggal_pindah_golongan ?? now())->startOfMonth();
        $efThn   = (int) $efektif->year;
        $efBln   = (int) $efektif->month;

        $semua = Penerimaan::where('nim', $bukuInduk->nim)->get();

        $idsUbah = $semua->filter(function($row) use ($efThn, $efBln) {
            $tahun = (int) ($row->tahun ?? 0);
            $bulan = $this->bulanAngka((string) $row->bulan);
            if ($tahun > $efThn) return true;
            if ($tahun < $efThn) return false;
            return $bulan >= $efBln;
        })->pluck('id');

        if ($idsUbah->isNotEmpty()) {
            $updatePayload = [
                'gol' => $request->gol_baru,
                'kd'  => $request->kd_baru,
            ];

            // update penerimaan bimba_unit/no_cabang jika kolom ada
            if (Schema::hasColumn('penerimaan', 'bimba_unit')) {
                $updatePayload['bimba_unit'] = $bukuInduk->bimba_unit ?? null;
            }
            if (Schema::hasColumn('penerimaan', 'no_cabang')) {
                $updatePayload['no_cabang'] = $bukuInduk->no_cabang ?? null;
            }

            Penerimaan::whereIn('id', $idsUbah)->update($updatePayload);
        }

        return redirect()->route('pindah-golongan.index')
                         ->with('success', 'Pindah Golongan berhasil, Buku Induk ikut update!');
    }

    public function edit(PindahGolongan $pindahGolongan)
{
    // ambil semua kombinasi unit -> no_cabang dari buku_induk
    $rows = BukuInduk::query()
        ->whereNotNull('bimba_unit')
        ->whereRaw("TRIM(COALESCE(bimba_unit,'')) <> ''")
        ->select('bimba_unit','no_cabang')
        ->get();

    // mapping: unit => unique list of cabang
    $unitMap = [];
    foreach ($rows as $r) {
        $unit = trim($r->bimba_unit);
        $cab  = trim($r->no_cabang ?? '');
        if ($unit === '') continue;
        if (! isset($unitMap[$unit])) $unitMap[$unit] = [];
        if ($cab !== '' && ! in_array($cab, $unitMap[$unit])) $unitMap[$unit][] = $cab;
    }

    // units list for select options (sorted)
    $units = array_keys($unitMap);
    sort($units, SORT_NATURAL | SORT_FLAG_CASE);

    $hargaSaptataruna = \App\Models\HargaSaptataruna::all();

    return view('pindah_golongan.edit', compact('pindahGolongan', 'hargaSaptataruna', 'units', 'unitMap'));
}


    public function update(Request $request, PindahGolongan $pindahGolongan)
    {
        $validated = $request->validate([
            'gol_baru' => 'nullable|string|max:10',
            'kd_baru' => 'nullable|string|max:20',
            'spp_baru' => 'nullable|string|max:20',
            'tanggal_pindah_golongan' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
            'alasan_pindah' => 'nullable|string',
        ]);

        // Prepare payload
        $pindahPayload = $validated;

        // Jika model pindah_golongan punya kolom bimba_unit/no_cabang dan BukuInduk tersedia -> sync
        $bukuInduk = BukuInduk::where('nim', $pindahGolongan->nim)->first();
        if ($bukuInduk) {
            if (Schema::hasColumn('pindah_golongan', 'bimba_unit')) {
                $pindahPayload['bimba_unit'] = $bukuInduk->bimba_unit ?? $pindahGolongan->bimba_unit ?? null;
            }
            if (Schema::hasColumn('pindah_golongan', 'no_cabang')) {
                $pindahPayload['no_cabang'] = $bukuInduk->no_cabang ?? $pindahGolongan->no_cabang ?? null;
            }
        }

        $pindahGolongan->update($pindahPayload);

        // Update BukuInduk sesuai nim
        $bukuInduk = BukuInduk::where('nim', $pindahGolongan->nim)->first();
        if ($bukuInduk) {
            $oldData = $bukuInduk->toArray();

            $bukuInduk->gol = $validated['gol_baru'] ?? $bukuInduk->gol;
            $bukuInduk->kd  = $validated['kd_baru'] ?? $bukuInduk->kd;
            $bukuInduk->spp = $validated['spp_baru'] ?? $bukuInduk->spp;
            $bukuInduk->save();

            // Simpan history BukuInduk
            $changedOld = [];
            $changedNew = [];
            foreach ($oldData as $key => $value) {
                if (($bukuInduk->$key ?? null) != $value) {
                    $changedOld[$key] = $value;
                    $changedNew[$key] = $bukuInduk->$key;
                }
            }

            if (!empty($changedOld)) {
                \App\Models\BukuIndukHistory::create([
                    'buku_induk_id' => $bukuInduk->id,
                    'action'        => 'update_pindah_golongan',
                    'user'          => Auth::user()->name ?? 'system',
                    'old_data'      => $changedOld,
                    'new_data'      => $changedNew,
                ]);
            }
        }

        // Update penerimaan downstream
        $efektif = Carbon::parse($pindahGolongan->tanggal_pindah_golongan)->startOfMonth();
        $efThn   = (int) $efektif->year;
        $efBln   = (int) $efektif->month;

        $semua = Penerimaan::where('nim', $pindahGolongan->nim)->get();

        $idsUbah = $semua->filter(function($row) use ($efThn, $efBln) {
            $tahun = (int) ($row->tahun ?? 0);
            $bulan = $this->bulanAngka((string) $row->bulan);
            if ($tahun > $efThn) return true;
            if ($tahun < $efThn) return false;
            return $bulan >= $efBln;
        })->pluck('id');

        if ($idsUbah->isNotEmpty()) {
            $updatePayload = [
                'gol' => $validated['gol_baru'] ?? $pindahGolongan->gol_baru,
                'kd'  => $validated['kd_baru']  ?? $pindahGolongan->kd_baru,
            ];

            if (Schema::hasColumn('penerimaan', 'bimba_unit') && $bukuInduk) {
                $updatePayload['bimba_unit'] = $bukuInduk->bimba_unit ?? null;
            }
            if (Schema::hasColumn('penerimaan', 'no_cabang') && $bukuInduk) {
                $updatePayload['no_cabang'] = $bukuInduk->no_cabang ?? null;
            }

            Penerimaan::whereIn('id', $idsUbah)->update($updatePayload);
        }

        return redirect()->route('pindah-golongan.index')
                         ->with('success', 'Data pindah golongan dan Buku Induk berhasil diperbarui.');
    }

    public function destroy(PindahGolongan $pindahGolongan)
    {
        $pindahGolongan->delete();
        return redirect()->route('pindah-golongan.index')
                         ->with('success', 'Data pindah golongan berhasil dihapus.');
    }

    private function normalizeRupiah($value): int
    {
        $n = (int) str_replace(['.', ','], '', (string) $value);
        if ($n > 0 && $n < 1000) $n *= 1000; // 300 -> 300000
        return $n;
    }
}
