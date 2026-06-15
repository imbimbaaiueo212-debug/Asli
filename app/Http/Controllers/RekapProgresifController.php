<?php

namespace App\Http\Controllers;

use App\Models\RekapProgresif;
use App\Models\Profile;
use App\Models\Penerimaan;
use App\Models\HargaSaptataruna;
use App\Models\BukuInduk;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RekapProgresifController extends Controller
{

    private function isCurrentUserAdmin(): bool
    {
        if (!Auth::check()) return false;

        $role = strtoupper(Auth::user()->role ?? '');

        $allowedRoles = [
            'ADMIN',
            'ADMINISTRATOR',
            'OWNER',
            'DIREKTUR',
            'KEPALA UA'
        ];

        return in_array($role, $allowedRoles);
    }

    private function getIndonesianMonthName(string $monthNumber): ?string
    {
        $months = [
            '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
            '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
            '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember',
        ];

        return $months[$monthNumber] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | TARIF PROGRESIF
    |--------------------------------------------------------------------------
    */

    private $progresifTariffs = [

        'GURU'=>[
            [6,10,100000],[11,15,200000],[16,20,300000],
            [21,25,450000],[26,30,600000],[31,35,800000],
            [36,40,1000000],[41,45,1400000],[46,50,1500000],
            [51,55,1600000],[56,60,1800000],[61,65,2000000],
            [66,70,2100000],[71,75,2400000],[76,80,2600000]
        ],

        'KEPALA UNIT'=>[
            [10,29,210000],[30,49,420000],[50,69,630000],
            [70,89,840000],[90,109,1050000],[110,129,1260000],
            [130,149,1470000],[150,169,1680000],[170,189,1890000],
            [190,209,2100000],[210,229,2310000],[230,249,2520000]
        ],

        'KEPALA UA'=>[
            [250,269,2730000],[270,289,2940000],[290,309,3150000],
            [310,329,3360000],[330,349,3570000],[350,369,3780000],
            [370,389,3990000],[390,409,4200000],[410,429,4410000],
            [430,449,4620000],[450,469,4830000],[470,489,5040000]
        ]
    ];

    private function calculateProgresifTariff($totalFM,$jabatan)
    {
        $jabatan = strtoupper(trim($jabatan));

        if(!isset($this->progresifTariffs[$jabatan])){
            return 0;
        }

        $fm = floor($totalFM);

        foreach($this->progresifTariffs[$jabatan] as $range){

            [$min,$max,$tarif] = $range;

            if($fm >= $min && $fm <= $max){
                return $tarif;
            }
        }

        $last = end($this->progresifTariffs[$jabatan]);

        return $fm > $last[1] ? $last[2] : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

public function index(Request $request)
{
    $periode     = $request->get('periode');
    $nama        = $request->get('nama');           // tambahan filter nama
    $monthFrom   = $request->get('month_from');
    $monthTo     = $request->get('month_to');

    // ====================== PARSING PERIODE ======================
    $selectedTahun = null;
    $selectedBulan = null;
    $selectedBulanNama = null;

    if ($monthFrom && $monthTo) {
        try {
            $from = Carbon::parse($monthFrom);
            $to   = Carbon::parse($monthTo);
            $selectedTahun = $from->year;   // Untuk sementara pakai tahun dari month_from
        } catch (\Exception $e) {}
    } 
    elseif ($periode && preg_match('/^\d{4}-\d{2}$/', $periode)) {
        try {
            $date = Carbon::createFromFormat('Y-m', $periode);
            $selectedTahun = $date->year;
            $selectedBulan = $date->format('m');
            $selectedBulanNama = $this->getIndonesianMonthName($selectedBulan);
        } catch (\Exception $e) {}
    } else {
        // Default: bulan sebelumnya
        $defaultDate = Carbon::now()->subMonth()->startOfMonth();
        $selectedTahun = $defaultDate->year;
        $selectedBulan = $defaultDate->format('m');
        $selectedBulanNama = $this->getIndonesianMonthName($selectedBulan);
        $periode = $defaultDate->format('Y-m');
    }

    // ====================== AMBIL PROFILE (Aktif + Magang) ======================
    $profilesQuery = Profile::query()
        ->whereIn('status_karyawan', ['Aktif', 'Magang'])
        ->orWhereNull('status_karyawan')
        ->where(function ($q) {
            $q->whereNull('tgl_masuk')
              ->orWhere('tgl_masuk', '>=', '2010-01-01');
        });

    if ($nama) {
        $profilesQuery->where('nama', 'LIKE', "%{$nama}%");
    }

    $profiles = $profilesQuery->orderBy('bimba_unit')
                              ->orderBy('nama')
                              ->get();

    // ====================== AMBIL REKAP PROGRESIF ======================
$rekapQuery = RekapProgresif::query();

if ($selectedTahun) {
    $rekapQuery->where('tahun', $selectedTahun);
}
if ($selectedBulanNama) {
    $rekapQuery->where('bulan', $selectedBulanNama);
} elseif ($monthFrom && $monthTo) {
    $rekapQuery->whereBetween('tahun', [$from->year, $to->year]);
}

$rekapList = $rekapQuery->get()->keyBy('nama');

// ====================== AUTO CREATE MISSING REKAP ======================
$createdCount = 0;

if ($selectedTahun && $selectedBulanNama) {
    foreach ($profiles as $profile) {
        if (!$rekapList->has($profile->nama)) {

            $calc = $this->calculateForProfile($profile, strtolower($selectedBulanNama), $selectedTahun);

            $data = [
                'nama'          => $profile->nama,
                'jabatan'       => $profile->jabatan ?? '-',
                'status'        => $profile->status_karyawan ?? 'Aktif',
                'departemen'    => $profile->departemen ?? '-',
                'masa_kerja'    => $this->formatMasaKerja($profile->masa_kerja),

                'bulan'         => $selectedBulanNama,
                'tahun'         => $selectedTahun,

                'spp_bimba'     => $calc['spp_bimba'],
                'am1'           => $calc['am1'],
                'am2'           => $calc['am2'],
                'total_fm'      => $calc['total_fm'],
                'progresif'     => $calc['progresif'],

                'spp_english'   => 0,
                'komisi'        => 0,
                'dibayarkan'    => $calc['progresif'],

                'bimba_unit'    => $calc['bimba_unit'],
                'no_cabang'     => $calc['no_cabang'],
            ];

            $newRekap = RekapProgresif::create($data);

            // Simpan dengan ID yang benar
            $rekapList->put($profile->nama, $newRekap);

            $createdCount++;
        }
    }
}

if ($createdCount > 0) {
    session()->flash('success', "$createdCount data rekap baru berhasil dibuat otomatis.");
}

   // ====================== MAPPING DATA ======================
$rekapProgresifs = $profiles->map(function ($profile) use ($rekapList, $selectedBulanNama, $selectedTahun) {

    $rekap = $rekapList->get($profile->nama);

    if ($rekap) {
        // Sudah ada rekap → ambil dari database
        $data = [
            'spp_bimba'  => $rekap->spp_bimba,
            'am1'        => $rekap->am1,
            'am2'        => $rekap->am2,
            'total_fm'   => $rekap->total_fm,
            'progresif'  => $rekap->progresif,
            'bimba_unit' => $rekap->bimba_unit,
            'no_cabang'  => $rekap->no_cabang,
        ];
    } else {
        // Belum ada rekap → hitung otomatis
        $calc = $this->calculateForProfile($profile, strtolower($selectedBulanNama), $selectedTahun);

        $data = $calc;
    }

    return (object) [
        'id'            => $rekap?->id,
        'nama'          => $profile->nama,
        'jabatan'       => $profile->jabatan ?? $rekap?->jabatan ?? '-',
        'status'        => $profile->status_karyawan ?? $rekap?->status ?? 'Aktif',
        'departemen'    => $profile->departemen ?? $rekap?->departemen ?? '-',
        'masa_kerja'    => $this->formatMasaKerja($profile->masa_kerja ?? $rekap?->masa_kerja),
        'bimba_unit'    => $data['bimba_unit'],
        'no_cabang'     => $data['no_cabang'],

        'bulan'         => $rekap?->bulan ?? $selectedBulanNama,
        'tahun'         => $rekap?->tahun ?? $selectedTahun,

        'spp_bimba'     => $data['spp_bimba'],
        'am1'           => $data['am1'],
        'am2'           => $data['am2'],
        'total_fm'      => $data['total_fm'],
        'progresif'     => $data['progresif'],
        'spp_english'   => $rekap?->spp_english ?? 0,
        'komisi'        => $rekap?->komisi ?? 0,
        'dibayarkan'    => $rekap?->dibayarkan ?? ($data['progresif'] ?? 0),

        'has_rekap'     => !is_null($rekap),
    ];
})->sortBy('bimba_unit')->sortBy('nama')->values();

    $isAdmin = $this->isCurrentUserAdmin();

    // Data untuk dropdown
    $allProfiles = Profile::whereIn('status_karyawan', ['Aktif', 'Magang'])
                          ->orWhereNull('status_karyawan')
                          ->orderBy('nama')
                          ->pluck('nama');

    $allPeriods = RekapProgresif::selectRaw("CONCAT(tahun, '-', LPAD(MONTH(STR_TO_DATE(bulan, '%M')), 2, '0')) as periode")
                                ->distinct()
                                ->orderByDesc('tahun')
                                ->pluck('periode');

    return view('rekap-progresif.index', compact(
        'rekapProgresifs',
        'isAdmin',
        'periode',
        'nama',
        'monthFrom',
        'monthTo',
        'selectedTahun',
        'selectedBulanNama',
        'allProfiles',
        'allPeriods'
    ));
}

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $profiles = Profile::orderBy('nama')->get();
        return view('rekap-progresif.create',compact('profiles'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {

        $profile = Profile::findOrFail($request->profile_id);

        $bulan = strtolower($request->bulan);
        $tahun = $request->tahun;

        $bimbaUnit = $profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit;

        $isKepalaUnit = strtolower($profile->jabatan) === 'kepala unit';

        $paidQuery = Penerimaan::whereRaw('LOWER(bulan)=?',[$bulan])
            ->where('tahun',$tahun)
            ->where('spp','>',0);

        if($isKepalaUnit){
    $paidQuery->where('bimba_unit',$bimbaUnit);
}else{
    $paidQuery->where('guru',$profile->nama);
}

        $paidNims = $paidQuery->distinct()->pluck('nim');

        $totalMuridBayar = $paidNims->count();

        $totalSPP = Penerimaan::whereIn('nim',$paidNims)
            ->whereRaw('LOWER(bulan)=?',[$bulan])
            ->where('tahun',$tahun)
            ->sum('spp');

        $totalMurid = $isKepalaUnit
    ? BukuInduk::where('bimba_unit', $bimbaUnit)->where('status','aktif')->count()
    : BukuInduk::where('guru',$profile->nama)->where('status','aktif')->count();

        $hargaS3 = HargaSaptataruna::whereRaw("LOWER(nama)='s3'")
            ->value('harga') ?? 300000;

        $totalFM = round(($totalSPP / $hargaS3) * 1.17,2);

        $progresif = $this->calculateProgresifTariff($totalFM,$profile->jabatan);

        $unit = Unit::whereRaw('LOWER(TRIM(biMBA_unit))=?',[strtolower(trim($bimbaUnit))])->first();

        $data = [

            'nama'=>$profile->nama,
            'jabatan'=>$profile->jabatan,
            'status'=>$profile->status_karyawan,
            'departemen'=>$profile->departemen,
            'masa_kerja'=>$this->formatMasaKerja($profile->masa_kerja),

            'bulan'=>$request->bulan,
            'tahun'=>$tahun,

            'spp_bimba'=>$totalSPP,
            'am1'=>$totalMurid,
            'am2'=>$totalMuridBayar,

            'total_fm'=>$totalFM,
            'progresif'=>$progresif,

            'spp_english'=>$request->spp_english ?? 0,
            'komisi'=>$request->komisi ?? 0,

            'dibayarkan'=>$progresif
                + ($request->spp_english ?? 0)
                + ($request->komisi ?? 0),

            'bimba_unit'=>$bimbaUnit,
            'no_cabang'=>$unit->no_cabang ?? null
        ];

        RekapProgresif::create($data);

        return redirect()->route('rekap-progresif.index')
        ->with('success','Data berhasil disimpan');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
public function edit($id)  // atau RekapProgresif $rekap_progresif
{
    $rekap_progresif = RekapProgresif::findOrFail($id);
    return view('rekap-progresif.edit', compact('rekap_progresif'));
}

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request,$id)
    {

        $rekap = RekapProgresif::findOrFail($id);

        $rekap->update([

            'spp_english'=>$request->spp_english,
            'komisi'=>$request->komisi,

            'dibayarkan'=>
                $rekap->progresif
                + ($request->spp_english ?? 0)
                + ($request->komisi ?? 0)

        ]);

        return redirect()->route('rekap-progresif.index')
        ->with('success','Data berhasil diupdate');
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT MASA KERJA
    |--------------------------------------------------------------------------
    */

    /*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

public function destroy($id)
{
    $rekap = RekapProgresif::findOrFail($id);

    $rekap->delete();

    return redirect()
        ->route('rekap-progresif.index')
        ->with('success','Data berhasil dihapus');
}

    private function formatMasaKerja($masaKerjaRaw)
    {

        $masaKerjaRaw = (int)$masaKerjaRaw;

        $tahun = floor($masaKerjaRaw / 12);
        $bulan = $masaKerjaRaw % 12;

        return ($tahun ? "$tahun tahun " : '')
            .($bulan ? "$bulan bulan" : ($tahun ? '' : '-'));
    }

    public function calculate(Request $request)
{
    $profile = Profile::findOrFail($request->profile_id);

    $bulan = strtolower(trim($request->bulan));
    $tahun = $request->tahun;

    $bimbaUnit = $profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit;

    $unit = Unit::whereRaw(
        'LOWER(TRIM(biMBA_unit)) = ?',
        [strtolower(trim($bimbaUnit))]
    )->first();

    $noCabang = $unit->no_cabang ?? null;

    $jabatan = strtolower(trim($profile->jabatan));

    $isKepalaUnit = $jabatan === 'kepala unit';
    $isRelawan = $jabatan === 'relawan';

    $paidQuery = Penerimaan::whereRaw('LOWER(bulan)=?', [$bulan])
        ->where('tahun', $tahun)
        ->where('spp', '>', 0);

    /*
    |--------------------------------------------------------------------------
    | FILTER DATA MURID
    |--------------------------------------------------------------------------
    */

    if ($isKepalaUnit || $isRelawan) {

        // Kepala unit ambil semua murid di unit
        $paidQuery->where('bimba_unit', 'LIKE', '%' . $bimbaUnit . '%');

    } else {

        // Guru hanya murid dia
        $paidQuery->where('guru', $profile->nama);

    }

    $paidNims = $paidQuery->distinct()->pluck('nim');

    $totalMuridBayar = $paidNims->count();

    $totalSPP = Penerimaan::whereIn('nim', $paidNims)
        ->whereRaw('LOWER(bulan)=?', [$bulan])
        ->where('tahun', $tahun)
        ->sum('spp');

    /*
    |--------------------------------------------------------------------------
    | TOTAL MURID AKTIF
    |--------------------------------------------------------------------------
    */

    if ($isKepalaUnit || $isRelawan) {

        $totalMurid = BukuInduk::where('bimba_unit', 'LIKE', '%' . $bimbaUnit . '%')
            ->where('status', 'aktif')
            ->count();

    } else {

        $totalMurid = BukuInduk::where('guru', $profile->nama)
            ->where('status', 'aktif')
            ->count();

    }

    $hargaS3 = HargaSaptataruna::whereRaw("LOWER(nama)='s3'")
        ->value('harga') ?? 300000;

    $totalFM = round(($totalSPP / $hargaS3) * 1.17, 2);

    $progresif = $this->calculateProgresifTariff($totalFM, $profile->jabatan);

    return response()->json([

        'jabatan' => $profile->jabatan,
        'status' => $profile->status_karyawan,
        'departemen' => $profile->departemen,
        'masa_kerja' => $this->formatMasaKerja($profile->masa_kerja),

        'bimba_unit' => $bimbaUnit,
        'no_cabang' => $noCabang,

        'spp_bimba' => $totalSPP,
        'total_fm' => $totalFM,
        'progresif' => $progresif,

        'am1' => $totalMurid,
        'am2' => $totalMuridBayar
    ]);
}

    /**
     * Generate Rekap Progresif Otomatis (dipanggil dari Command)
     */
    public function autoGenerateForPreviousMonth($profile, $bulan, $tahun)
    {
        try {
            $bimbaUnit = $profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit ?? null;
            
            $unit = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', 
                [strtolower(trim($bimbaUnit))]
            )->first();

            $isKepalaUnit = strtolower(trim($profile->jabatan)) === 'kepala unit';

            // Query penerimaan SPP
            $paidQuery = Penerimaan::whereRaw('LOWER(bulan) = ?', [$bulan])
                ->where('tahun', $tahun)
                ->where('spp', '>', 0);

            if ($isKepalaUnit) {
                $paidQuery->where('bimba_unit', 'LIKE', '%' . $bimbaUnit . '%');
            } else {
                $paidQuery->where('guru', $profile->nama);
            }

            $paidNims = $paidQuery->distinct()->pluck('nim');

            $totalMuridBayar = $paidNims->count();

            $totalSPP = Penerimaan::whereIn('nim', $paidNims)
                ->whereRaw('LOWER(bulan) = ?', [$bulan])
                ->where('tahun', $tahun)
                ->sum('spp');

            // Total murid aktif
            if ($isKepalaUnit) {
                $totalMurid = BukuInduk::where('bimba_unit', 'LIKE', '%' . $bimbaUnit . '%')
                    ->where('status', 'aktif')
                    ->count();
            } else {
                $totalMurid = BukuInduk::where('guru', $profile->nama)
                    ->where('status', 'aktif')
                    ->count();
            }

            $hargaS3 = HargaSaptataruna::whereRaw("LOWER(nama)='s3'")
                ->value('harga') ?? 300000;

            $totalFM = round(($totalSPP / $hargaS3) * 1.17, 2);

            $progresif = $this->calculateProgresifTariff($totalFM, $profile->jabatan);

            $data = [
                'nama'          => $profile->nama,
                'jabatan'       => $profile->jabatan,
                'status'        => $profile->status_karyawan ?? 'Aktif',
                'departemen'    => $profile->departemen,
                'masa_kerja'    => $this->formatMasaKerja($profile->masa_kerja),

                'bulan'         => $bulan,
                'tahun'         => $tahun,

                'spp_bimba'     => $totalSPP,
                'am1'           => $totalMurid,
                'am2'           => $totalMuridBayar,

                'total_fm'      => $totalFM,
                'progresif'     => $progresif,

                'spp_english'   => 0,
                'komisi'        => 0,
                'dibayarkan'    => $progresif,

                'bimba_unit'    => $bimbaUnit,
                'no_cabang'     => $unit->no_cabang ?? null,
            ];

            RekapProgresif::updateOrCreate(
                [
                    'nama'  => $profile->nama,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                $data
            );

        } catch (\Throwable $e) {
            throw $e; // biar ditangkap di command
        }
    }

    /**
 * Hitung progresif untuk satu profile (digunakan di index & calculate)
 */
private function calculateForProfile($profile, string $bulan, int $tahun)
{
    $bimbaUnit = $profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit;

    $unit = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', 
        [strtolower(trim($bimbaUnit))]
    )->first();

    $jabatan = strtolower(trim($profile->jabatan ?? ''));
    $isKepalaUnit = $jabatan === 'kepala unit' || $jabatan === 'kepala ua';

    // Query Penerimaan
    $paidQuery = Penerimaan::whereRaw('LOWER(bulan) = ?', [$bulan])
        ->where('tahun', $tahun)
        ->where('spp', '>', 0);

    if ($isKepalaUnit) {
        $paidQuery->where('bimba_unit', 'LIKE', '%' . $bimbaUnit . '%');
    } else {
        $paidQuery->where('guru', $profile->nama);
    }

    $paidNims = $paidQuery->distinct()->pluck('nim');

    $totalMuridBayar = $paidNims->count();

    $totalSPP = Penerimaan::whereIn('nim', $paidNims)
        ->whereRaw('LOWER(bulan) = ?', [$bulan])
        ->where('tahun', $tahun)
        ->sum('spp');

    // Total Murid Aktif
    if ($isKepalaUnit) {
        $totalMurid = BukuInduk::where('bimba_unit', 'LIKE', '%' . $bimbaUnit . '%')
            ->where('status', 'aktif')
            ->count();
    } else {
        $totalMurid = BukuInduk::where('guru', $profile->nama)
            ->where('status', 'aktif')
            ->count();
    }

    $hargaS3 = HargaSaptataruna::whereRaw("LOWER(nama)='s3'")
        ->value('harga') ?? 300000;

    $totalFM = round(($totalSPP / $hargaS3) * 1.17, 2);

    $progresif = $this->calculateProgresifTariff($totalFM, $profile->jabatan);

    return [
        'spp_bimba'   => $totalSPP,
        'am1'         => $totalMurid,
        'am2'         => $totalMuridBayar,
        'total_fm'    => $totalFM,
        'progresif'   => $progresif,
        'bimba_unit'  => $bimbaUnit,
        'no_cabang'   => $unit->no_cabang ?? null,
    ];
}

/**
 * Generate / Simpan Semua Rekap yang Belum Ada
 */
public function generateAllMissing(Request $request)
{
    $periode = $request->get('periode');
    $selectedTahun = null;
    $selectedBulanNama = null;

    if ($periode && preg_match('/^\d{4}-\d{2}$/', $periode)) {
        $date = Carbon::createFromFormat('Y-m', $periode);
        $selectedTahun = $date->year;
        $selectedBulan = $date->format('m');
        $selectedBulanNama = $this->getIndonesianMonthName($selectedBulan);
    } else {
        $defaultDate = Carbon::now()->subMonth()->startOfMonth();
        $selectedTahun = $defaultDate->year;
        $selectedBulanNama = $this->getIndonesianMonthName($defaultDate->format('m'));
    }

    $profiles = Profile::whereIn('status_karyawan', ['Aktif', 'Magang'])
                        ->orWhereNull('status_karyawan')
                        ->get();

    $created = 0;

    foreach ($profiles as $profile) {
        // Cek apakah sudah ada rekap
        $exists = RekapProgresif::where('nama', $profile->nama)
                    ->where('bulan', $selectedBulanNama)
                    ->where('tahun', $selectedTahun)
                    ->exists();

        if (!$exists) {
            // Hitung dulu
            $calc = $this->calculateForProfile($profile, strtolower($selectedBulanNama), $selectedTahun);

            $data = [
                'nama'          => $profile->nama,
                'jabatan'       => $profile->jabatan,
                'status'        => $profile->status_karyawan ?? 'Aktif',
                'departemen'    => $profile->departemen,
                'masa_kerja'    => $this->formatMasaKerja($profile->masa_kerja),

                'bulan'         => $selectedBulanNama,
                'tahun'         => $selectedTahun,

                'spp_bimba'     => $calc['spp_bimba'],
                'am1'           => $calc['am1'],
                'am2'           => $calc['am2'],
                'total_fm'      => $calc['total_fm'],
                'progresif'     => $calc['progresif'],

                'spp_english'   => 0,
                'komisi'        => 0,
                'dibayarkan'    => $calc['progresif'],

                'bimba_unit'    => $calc['bimba_unit'],
                'no_cabang'     => $calc['no_cabang'],
            ];

            RekapProgresif::create($data);
            $created++;
        }
    }

    return redirect()->route('rekap-progresif.index', ['periode' => $periode ?? ''])
                     ->with('success', "$created data rekap berhasil dibuat otomatis.");
}

}