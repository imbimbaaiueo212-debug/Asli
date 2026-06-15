<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\PembayaranTunjangan;
use App\Models\PendapatanTunjangan;
use App\Models\PotonganTunjangan;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\Skim;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembayaranTunjanganController extends Controller
{
    /**
     * Tampilkan semua data pembayaran tunjangan
     */
    public function index(Request $request)
{
    $nama       = $request->input('nama');
    $monthFrom  = $request->input('month_from');
    $monthTo    = $request->input('month_to');

    $user = auth()->user();
    $tarif_harian = 24000;

    // ====================== PARSING BULAN ======================
    if ($monthFrom) {
        try {
            $monthFrom = Carbon::parse($monthFrom)->format('Y-m');
        } catch (\Throwable $e) {
            $monthFrom = null;
        }
    }

    if ($monthTo) {
        try {
            $monthTo = Carbon::parse($monthTo)->format('Y-m');
        } catch (\Throwable $e) {
            $monthTo = null;
        }
    }

    // Default ke bulan ini jika tidak ada filter range
    $singleBulan = $request->input('bulan', now()->format('Y-m'));
    
    if (!$monthFrom && !$monthTo) {
        $monthFrom = $singleBulan;
        $monthTo   = $singleBulan;
    }

    // ====================== AMBIL PROFILE ======================
    // Hanya Aktif dan Magang yang boleh muncul
    $profilesQuery = Profile::query()
        ->whereIn('status_karyawan', ['Aktif', 'Magang'])
        ->orWhereNull('status_karyawan')
        ->where(function ($q) {
            $q->whereNull('tgl_masuk')
              ->orWhere('tgl_masuk', '>=', '2010-01-01');
        });

    if ($nama) {
        $profilesQuery->where('nama', $nama);
    }

    $profiles = $profilesQuery->orderBy('nama')->get();

    // ====================== AMBIL PENDAPATAN & POTONGAN ======================
    $pendapatanQuery = PendapatanTunjangan::query();
    $potonganQuery   = PotonganTunjangan::query();

    if ($monthFrom && $monthTo) {
        $pendapatanQuery->whereBetween('bulan', [$monthFrom, $monthTo]);
        $potonganQuery->whereBetween('bulan', [$monthFrom, $monthTo]);
    } else {
        $pendapatanQuery->where('bulan', $singleBulan);
        $potonganQuery->where('bulan', $singleBulan);
    }

    $pendapatanList = $pendapatanQuery->get()->groupBy('nama');
    $potonganList   = $potonganQuery->get()->groupBy('nama');

    // ====================== MAPPING DATA DENGAN AKUMULASI ======================
    $pembayaranTunjangans = $profiles->map(function ($profile) use ($pendapatanList, $potonganList, $monthFrom, $monthTo, $singleBulan) {

        $pendapatanRecords = $pendapatanList->get($profile->nama) ?? collect();
        $potonganRecords   = $potonganList->get($profile->nama) ?? collect();

        // Total Pendapatan
        $totalPendapatan = $pendapatanRecords->sum('total');
        if ($totalPendapatan == 0) {
            $totalPendapatan = $this->hitungSkim($profile); // fallback jika belum ada data pendapatan
        }

        // Total Potongan (diakumulasi jika range bulan)
        $sakit_rp       = (int) $potonganRecords->sum('sakit');
        $izin_rp        = (int) $potonganRecords->sum('izin');
        $alpa_rp        = (int) $potonganRecords->sum('alpa');
        $tidak_aktif_rp = (int) $potonganRecords->sum('tidak_aktif');
        $kelebihan_rp   = (int) ($potonganRecords->sum('kelebihan_nominal') + $potonganRecords->sum('kelebihan'));
        $lain_lain_rp   = (int) $potonganRecords->sum('lain_lain');

        $totalPotongan = $sakit_rp + $izin_rp + $alpa_rp + $tidak_aktif_rp + $kelebihan_rp + $lain_lain_rp;

        // Format Masa Kerja
        $masaKerjaBulan = $profile->masa_kerja ?? 0;
        $years  = intdiv($masaKerjaBulan, 12);
        $months = $masaKerjaBulan % 12;

        $masaKerjaFormatted = trim(
            ($years > 0 ? $years . ' th ' : '') .
            ($months > 0 ? $months . ' bln' : '0 bln')
        );

        // Label Bulan untuk tampilan
        $bulanLabel = ($monthFrom && $monthTo && $monthFrom !== $monthTo)
            ? "$monthFrom s/d $monthTo"
            : ($monthFrom ?? $singleBulan);

        return (object) [
            'nik'           => $profile->nik ?? '-',
            'nama'          => $profile->nama,
            'bulan'         => $bulanLabel,
            'jabatan'       => $profile->jabatan ?? '-',
            'status'        => $profile->status_karyawan ?? 'Aktif',
            'departemen'    => $profile->departemen ?? '-',
            'masa_kerja'    => $masaKerjaFormatted,

            'no_rekening'   => $profile->no_rekening ?? '-',
            'bank'          => $profile->bank ?? '-',
            'atas_nama'     => $profile->atas_nama ?? '-',

            'pendapatan'    => $totalPendapatan,

            'sakit'         => $sakit_rp,
            'izin'          => $izin_rp,
            'alpa'          => $alpa_rp,
            'tidak_aktif'   => $tidak_aktif_rp,
            'kelebihan'     => $kelebihan_rp,
            'lain_lain'     => $lain_lain_rp,

            'potongan'      => $totalPotongan,
            'dibayarkan'    => $totalPendapatan - $totalPotongan,

            'bimba_unit'    => $profile->bimba_unit ?? $profile->nama_unit ?? '-',
            'no_cabang'     => $profile->no_cabang ?? $profile->kode_cabang ?? '-',

            'jumlah_bulan'  => $potonganRecords->count(),
        ];
    })
    ->sortBy('nik')
    ->values();

    // ====================== DROPDOWN FILTER ======================
    $allNames = Profile::orderBy('nama')->pluck('nama');

    $allMonths = PendapatanTunjangan::distinct()
        ->orderByDesc('bulan')
        ->pluck('bulan');

    return view('pembayaran.index', compact(
        'pembayaranTunjangans',
        'allNames',
        'allMonths',
        'nama',
        'monthFrom',
        'monthTo',
        'singleBulan'
    ));
}
private function hitungSkim($profile)
{
    if (!$profile) {
        return 0;
    }

    // ambil skim berdasarkan jabatan atau masa kerja
    $skim = Skim::where('jabatan', $profile->jabatan)->first();

    if (!$skim) {
        return 0;
    }

    $masaKerja = $profile->masa_kerja ?? 0;

    $thp        = $skim->thp ?? 0;
    $kerajinan  = $skim->kerajinan ?? 0;
    $english    = $skim->english ?? 0;
    $mentor     = $skim->mentor ?? 0;
    $keluarga   = $skim->tj_keluarga ?? 0;

    return $thp + $kerajinan + $english + $mentor + $keluarga;
}

    public function create(Request $request)
    {
        // (tetap sama seperti punyamu)
        $tglMasuk = $request->old('tgl_masuk') ?? $request->get('tgl_masuk');

        $bulan = $request->old('bulan') ?? $request->get('bulan');
        $tahun = $request->old('tahun') ?? $request->get('tahun');

        $asOf = now();
        if ($bulan && $tahun) {
            $map = [
                'Januari'=>'01','Februari'=>'02','Maret'=>'03','April'=>'04','Mei'=>'05','Juni'=>'06',
                'Juli'=>'07','Agustus'=>'08','September'=>'09','Oktober'=>'10','November'=>'11','Desember'=>'12'
            ];
            $mm = $map[ucfirst(strtolower($bulan))] ?? str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $asOf = Carbon::createFromFormat('Y-m-d', $tahun.'-'.$mm.'-01')->endOfMonth();
        }

        $defaultMasaKerja = 0;
        if (!empty($tglMasuk)) {
            try {
                $defaultMasaKerja = Carbon::parse($tglMasuk)->diffInMonths($asOf);
            } catch (\Exception $e) {
                $defaultMasaKerja = 0;
            }
        }

        return view('pendapatan.create', compact('defaultMasaKerja'));
    }

    public function store(Request $request)
    {
        // (bagian validasi & helper toNumber tetap PERSIS seperti punyamu)
        $validated = $request->validate([
            'nama'        => ['required','string','max:100'],
            'bulan'       => ['required','string','max:20'],
            'tahun'       => ['nullable','integer','min:1900','max:3000'],
            'tgl_masuk'   => ['nullable','date'],
            'masa_kerja'  => ['nullable','integer','min:0'],

            'nik'         => ['nullable','string','max:50'],
            'jabatan'     => ['nullable','string','max:100'],
            'status'      => ['nullable','string','max:50'],
            'departemen'  => ['nullable','string','max:100'],
            'no_rekening' => ['nullable','string','max:50'],
            'bank'        => ['nullable','string','max:50'],
            'atas_nama'   => ['nullable','string','max:100'],

            'pendapatan'  => ['nullable'],
            'potongan'    => ['nullable'],
        ], [], [
            'nama'  => 'Nama karyawan',
            'bulan' => 'Bulan',
        ]);

        $toNumber = function ($value) {
            if ($value === null || $value === '') return null;
            $s = str_replace([' ', "\u{00A0}"], '', (string)$value);
            if (preg_match('/[\.,]\d{1,2}$/', $s)) {
                $decimal = substr($s, -3);
                $int = substr($s, 0, -3);
                $int = preg_replace('/[^\d-]/', '', $int);
                $dec = preg_replace('/[^\d]/', '', substr($decimal, 1));
                $s = $int.'.'.$dec;
            } else {
                $s = preg_replace('/[^\d-]/', '', $s);
            }
            if ($s === '' || $s === '-') return null;
            return (float)$s;
        };

        $map = [
            'januari'=>'01','februari'=>'02','maret'=>'03','april'=>'04','mei'=>'05','juni'=>'06',
            'juli'=>'07','agustus'=>'08','september'=>'09','oktober'=>'10','november'=>'11','desember'=>'12'
        ];

        $inputBulan  = trim((string)$validated['bulan']);
        $inputTahun  = $validated['tahun'] ?? null;
        $bulanNumber = null; $tahunFinal = $inputTahun;

        if (preg_match('/^(\d{4})-(\d{2})$/', $inputBulan, $m)) {
            $tahunFinal  = (int)$m[1];
            $bulanNumber = $m[2];
        } elseif (preg_match('/^\d{1,2}$/', $inputBulan)) {
            $bulanNumber = str_pad($inputBulan, 2, '0', STR_PAD_LEFT);
        } else {
            $key = strtolower($inputBulan);
            $bulanNumber = $map[$key] ?? null;
        }

        if (!$bulanNumber) {
            return back()->withErrors(['bulan' => 'Format bulan tidak dikenali.'])->withInput();
        }
        if (!$tahunFinal) {
            $tahunFinal = (int) date('Y');
        }

        $bulanKey = $tahunFinal.'-'.$bulanNumber;

        try {
            $asOf = Carbon::createFromFormat('Y-m-d', $tahunFinal.'-'.$bulanNumber.'-01')->endOfMonth();
        } catch (\Exception $e) {
            return back()->withErrors(['bulan' => 'Periode tidak valid.'])->withInput();
        }

        $profile = Profile::where('nama', $validated['nama'])->first();
        $nik         = $validated['nik']         ?? ($profile->nik ?? null);
        $jabatan     = $validated['jabatan']     ?? ($profile->jabatan ?? null);
        $status      = $validated['status']      ?? ($profile->status ?? null);
        $departemen  = $validated['departemen']  ?? ($profile->departemen ?? null);
        $no_rekening = $validated['no_rekening'] ?? ($profile->no_rekening ?? null);
        $bank        = $validated['bank']        ?? ($profile->bank ?? null);
        $atas_nama   = $validated['atas_nama']   ?? ($profile->atas_nama ?? null);

        // ✅ ambil unit & cabang dari profile
        $bimbaUnit = $profile->bimba_unit ?? $profile->nama_unit ?? null;
        $noCabang  = $profile->no_cabang ?? $profile->kode_cabang ?? null;

        $masaKerja = $validated['masa_kerja'] ?? null;
        if ($masaKerja === null) {
            $tglMasuk = $validated['tgl_masuk'] ?? ($profile->tgl_masuk ?? null);
            if ($tglMasuk) {
                try {
                    $masaKerja = Carbon::parse($tglMasuk)->diffInMonths($asOf);
                } catch (\Exception $e) {
                    $masaKerja = 0;
                }
            } else {
                $masaKerja = 0;
            }
        }
        $masaKerja = max(0, (int)$masaKerja);

        $pendapatanInput = $toNumber($request->input('pendapatan'));
        $potonganInput   = $toNumber($request->input('potongan'));

        if ($pendapatanInput === null) {
            $aggPendapatan = PendapatanTunjangan::where('nama', $validated['nama'])
                ->where('bulan', $bulanKey)
                ->selectRaw('SUM(total) as total_pendapatan')
                ->first();
            $pendapatanInput = (float) ($aggPendapatan->total_pendapatan ?? 0);
        }

        if ($potonganInput === null) {
            $pot = PotonganTunjangan::where('nama', $validated['nama'])
                ->where('bulan', $bulanKey)
                ->first();
            $sakit = $pot->sakit ?? 0;
            $izin  = $pot->izin ?? 0;
            $alpa  = $pot->alpa ?? 0;
            $tdk   = $pot->tidak_aktif ?? 0;
            $keleb = $pot->kelebihan ?? 0;
            $lain  = $pot->lain_lain ?? 0;
            $potonganInput = (float)($sakit + $izin + $alpa + $tdk + $keleb + $lain);
        }

        $pendapatan = max(0, (float)$pendapatanInput);
        $potongan   = max(0, (float)$potonganInput);
        $dibayarkan = max(0, $pendapatan - $potongan);

        try {
            DB::transaction(function () use (
                $validated, $bulanKey, $masaKerja, $pendapatan, $potongan, $dibayarkan,
                $nik, $jabatan, $status, $departemen, $no_rekening, $bank, $atas_nama,
                $bimbaUnit, $noCabang        // ✅ ikut dibawa
            ) {
                $row = PembayaranTunjangan::firstOrNew([
                    'nama'  => $validated['nama'],
                    'bulan' => $bulanKey,
                ]);

                $row->nik         = $nik;
                $row->jabatan     = $jabatan;
                $row->status      = $status;
                $row->departemen  = $departemen;
                $row->masa_kerja  = $masaKerja;
                $row->no_rekening = $no_rekening;
                $row->bank        = $bank;
                $row->atas_nama   = $atas_nama;

                // ✅ simpan unit & cabang
                $row->bimba_unit  = $bimbaUnit;
                $row->no_cabang   = $noCabang;

                $row->pendapatan  = $pendapatan;
                $row->potongan    = $potongan;
                $row->dibayarkan  = $dibayarkan;

                $row->save();
            });
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan PembayaranTunjangan: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors([
                'general' => 'Maaf, terjadi kesalahan saat menyimpan. Silakan coba lagi.'
            ])->withInput();
        }

        return redirect()
            ->route('pembayaran.index', ['nama' => $validated['nama'], 'bulan' => $bulanKey])
            ->with('success', 'Data pembayaran tunjangan berhasil disimpan.');
    }

    /**
     * Detail data
     */
    public function show(PembayaranTunjangan $pembayaran)
    {
        return view('pembayaran.show', compact('pembayaran'));
    }

    /**
     * Edit data
     */
    public function edit(PembayaranTunjangan $pembayaran)
    {
        return view('pembayaran.edit', compact('pembayaran'));
    }

    /**
     * Update data
     */
    public function update(Request $request, PembayaranTunjangan $pembayaran)
    {
        $validated = $request->validate([
            'nik'         => 'nullable|string|max:50',
            'nama'        => 'required|string|max:100',
            'jabatan'     => 'nullable|string|max:100',
            'status'      => 'nullable|string|max:50',
            'departemen'  => 'nullable|string|max:100',
            'masa_kerja'  => 'nullable|integer',
            'no_rekening' => 'nullable|string|max:50',
            'bank'        => 'nullable|string|max:50',
            'atas_nama'   => 'nullable|string|max:100',
            'pendapatan'  => 'required|numeric',
            'potongan'    => 'nullable|numeric',

            // ✅ boleh update manual kalau perlu
            'bimba_unit'  => 'nullable|string|max:100',
            'no_cabang'   => 'nullable|string|max:50',
        ]);

        $validated['dibayarkan'] = $validated['pendapatan'] - ($validated['potongan'] ?? 0);

        $pembayaran->update($validated);

        return redirect()->route('pembayaran.index')->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Hapus data
     */
    public function destroy(PembayaranTunjangan $pembayaran)
    {
        $pembayaran->delete();
        return redirect()->route('pembayaran.index')->with('success', 'Data berhasil dihapus');
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $nama = $request->input('nama');
        $units = Unit::all(); // masih bisa dipakai sebagai default kalau mau

        // ✅ tambahkan MAX(bimba_unit), MAX(no_cabang)
        $pendapatanGrouped = PendapatanTunjangan::selectRaw('
            nama,
            bulan,
            MAX(status)      as status,
            MAX(jabatan)     as jabatan,
            MAX(departemen)  as departemen,
            MAX(masa_kerja)  as masa_kerja,
            MAX(bimba_unit)  as bimba_unit,
            MAX(no_cabang)   as no_cabang,
            SUM(thp)         as total_thp,
            SUM(kerajinan)   as total_kerajinan,
            SUM(english)     as total_english,
            SUM(mentor)      as total_mentor,
            SUM(kekurangan)  as total_kekurangan,
            SUM(tj_keluarga) as total_tj_keluarga,
            SUM(lain_lain)   as total_lain_lain,
            SUM(total)       as total_pendapatan
        ')
        ->when($request->nama, fn($q, $nama) => $q->where('nama', $nama))
        ->when($request->bulan, fn($q, $bulan) => $q->where('bulan', $bulan))
        ->groupBy('nama', 'bulan')
        ->orderBy('bulan', 'desc')
        ->orderBy('nama', 'asc')
        ->get();

        $pembayaranTunjangans = $pendapatanGrouped->map(function ($pendapatan) use ($units) {
            $profile  = Profile::where('nama', $pendapatan->nama)->first() ?? new Profile();
            $potongan = PotonganTunjangan::where('nama', $pendapatan->nama)
                        ->where('bulan', $pendapatan->bulan)
                        ->first() ?? new PotonganTunjangan();

            $totalBulan = (int) ($pendapatan->masa_kerja ?? 0);
            $years  = floor($totalBulan / 12);
            $months = $totalBulan % 12;
            $masaKerjaFormatted = trim(
                ($years > 0 ? $years . ' th ' : '') .
                ($months > 0 ? $months . ' bln' : '0 bln')
            );

            $sakit       = $potongan->sakit ?? 0;
            $izin        = $potongan->izin ?? 0;
            $alpa        = $potongan->alpa ?? 0;
            $tidakAktif  = $potongan->tidak_aktif ?? 0;
            $kelebihan   = $potongan->kelebihan ?? 0;
            $lainLain    = $potongan->lain_lain ?? 0;
            $totalPotongan = $sakit + $izin + $alpa + $tidakAktif + $kelebihan + $lainLain;

            $totalPendapatan = $pendapatan->total_pendapatan;
            $dibayarkan = $totalPendapatan - $totalPotongan;

            // ✅ tentukan unit & cabang
            $defaultUnit = optional($units->first())->biMBA_unit ?? '-';

            $bimbaUnit = $pendapatan->bimba_unit
                ?? ($profile->bimba_unit ?? $profile->nama_unit ?? $defaultUnit);

            $noCabang  = $pendapatan->no_cabang
                ?? ($profile->no_cabang ?? $profile->kode_cabang ?? '-');

            return (object)[
                'nik'             => $profile->nik ?? '-',
                'nama'            => $pendapatan->nama,
                'unit'            => $bimbaUnit,   // dipakai di view pdf
                'no_cabang'       => $noCabang,    // bisa ditampilkan di pdf
                'bulan'           => $pendapatan->bulan,
                'jabatan'         => $pendapatan->jabatan ?? '-',
                'status'          => $pendapatan->status ?? '-',
                'departemen'      => $pendapatan->departemen ?? '-',
                'masa_kerja'      => $masaKerjaFormatted,
                'no_rekening'     => $profile->no_rekening ?? '-',
                'bank'            => $profile->bank ?? '-',
                'atas_nama'       => $profile->atas_nama ?? '-',
                'total_pendapatan'=> $totalPendapatan,
                'total_potongan'  => $totalPotongan,
                'dibayarkan'      => $dibayarkan,
            ];
        });

        $pdf = PDF::loadView('pembayaran.pdf', compact('pembayaranTunjangans'))
            ->setPaper('a5', 'landscape');

        return $pdf->stream('slip_pembayaran.pdf');
    }
}
