<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\HargaSaptataruna;
use App\Models\Student;
use App\Models\Unit;
use App\Models\Huma;
use App\Models\BukuInduk;
use App\Models\Profile;
use App\Models\Penerimaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    // ===== List + filter =====
    public function index(Request $request)
{
    $q           = trim((string) $request->get('q', ''));
    $status      = trim((string) $request->get('status', ''));
    $unitId      = $request->get('unit_id');   // ← Tambahkan ini
    $user        = Auth::user();
    $isAdmin     = $user && in_array($user->role ?? '', ['admin', 'superadmin']);

    Log::info('DEBUG REGISTRATION INDEX', [
        'user'            => $user?->name ?? '-',
        'role'            => $user?->role ?? '-',
        'user_bimba_unit' => $user?->bimba_unit ?? '-',
        'is_admin'        => $isAdmin,
        'search'          => $q,
        'status'          => $status,
        'unit_id'         => $unitId
    ]);

    $query = Registration::query()
        ->with(['student.bukuInduk'])
        ->latest('created_at');

    // Search
    if ($q !== '') {
        $query->whereHas('student', function ($sq) use ($q) {
            $sq->where('nim', 'like', "%{$q}%")
               ->orWhere('nama', 'like', "%{$q}%");
        });
    }

    // Filter Status
    if ($status !== '') {
        $query->where('status', $status);
    }

    // ========================
    // FILTER UNIT
    // ========================
    if (!$isAdmin) {
        $userUnit     = trim($user->bimba_unit ?? '');
        $userNoCabang = trim($user->no_cabang ?? '');

        $query->where(function ($qry) use ($userUnit, $userNoCabang) {
            if ($userUnit) {
                $qry->where('bimba_unit', 'LIKE', "%{$userUnit}%")
                    ->orWhereHas('student', fn($sq) => 
                        $sq->where('bimba_unit', 'LIKE', "%{$userUnit}%")
                    );
            }

            if ($userNoCabang) {
                $qry->orWhere('no_cabang', $userNoCabang)
                    ->orWhereHas('student', fn($sq) => 
                        $sq->where('no_cabang', $userNoCabang)
                    );
            }

            // Unit khusus yang diizinkan
            $qry->orWhere('bimba_unit', 'LIKE', '%VILLA BEKASI INDAH 2%')
                ->orWhere('no_cabang', '00340')
                ->orWhere('bimba_unit', 'LIKE', '%GRIYA PESONA MADANI%')
                ->orWhere('no_cabang', '05141')
                ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA IV%')
                ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA 4%')
                ->orWhere('no_cabang', '01045')

                ->orWhereHas('student', function ($sq) {
                    $sq->whereIn('no_cabang', ['00340', '05141', '01045'])
                       ->orWhere('bimba_unit', 'LIKE', '%VILLA BEKASI INDAH 2%')
                       ->orWhere('bimba_unit', 'LIKE', '%GRIYA PESONA MADANI%')
                       ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA%');
                });
        });
    } 
    // Admin bisa filter manual
    elseif ($unitId) {
        $unit = Unit::find($unitId);
        if ($unit) {
            $query->whereHas('student', function ($sq) use ($unit) {
                $sq->where('no_cabang', $unit->no_cabang)
                   ->where('bimba_unit', 'LIKE', "%{$unit->biMBA_unit}%");
            });
        }
    }

    $regs = $query->paginate(25)->withQueryString();

    // Unit Options untuk dropdown
    $unitOptions = Unit::orderBy('no_cabang')
        ->get()
        ->map(fn ($u) => [
            'value' => $u->id,
            'label' => trim(($u->no_cabang ?? '') . ' - ' . ($u->biMBA_unit ?? '')),
        ])
        ->toArray();

    return view('registrations.index', compact(
        'regs',
        'unitOptions',
        'unitId',      // ← Ini yang diperbaiki
        'q',
        'status'
    ));
}

private function extractUkuran($nama)
{
    if (preg_match('/\((S|M|L|XL|XXL|XXXL)\)/i', $nama, $matches)) {
        return strtoupper($matches[1]);
    }
    return 'Standar';
}

    // ===== Create form =====
    public function create(Request $request)
{
    $students = Student::with('muridTrial')
        ->orderBy('nama')
        ->get([
            'id',
            'nim',
            'nama',
            'bimba_unit',
            'no_cabang',
            'tgl_lahir',
            'tempat_lahir',
            'orangtua',
            'alamat'
        ]);

    $selectedStudentId = (int) $request->query('student_id');

    $prefilledNim = '';
    $prefilledNama = '';
    $prefilledUnit = '';
    $prefilledCabang = '';
    $prefilledTglLahir = '';
    $prefilledTmptLahir = '';
    $prefilledOrangtua = '';
    $prefilledInfo = '';

    $selectedStudent = null;

    if ($selectedStudentId) {

        $selectedStudent = Student::with('muridTrial','registrations')
            ->find($selectedStudentId);

        if ($selectedStudent) {

            $trial = $selectedStudent->muridTrial;

            // cek apakah user batal dari form pendaftaran
            $hasActiveReg = $selectedStudent->registrations()
                ->whereIn('status',['pending','verified','accepted'])
                ->exists();

           if ($selectedStudent) {
                $trial = $selectedStudent->muridTrial;

                // Hanya kembalikan ke 'aktif' jika statusnya BATAL
                if ($trial && $trial->status_trial === 'batal') {
                    $trial->update(['status_trial' => 'aktif']);
                }

                // JANGAN ubah 'lanjut_daftar' menjadi aktif
                // Jika sudah lanjut_daftar, biarkan saja
            }

            $prefilledNim = $selectedStudent->nim
                ?? 'Akan digenerate otomatis setelah disimpan';

            $prefilledNama =
                $selectedStudent->nama
                ?? $trial?->nama
                ?? '';

            $prefilledUnit =
                $selectedStudent->bimba_unit
                ?? $trial?->bimba_unit
                ?? '';

            $prefilledCabang =
                $selectedStudent->no_cabang
                ?? $trial?->no_cabang
                ?? '';

            $prefilledTglLahir =
                $selectedStudent->tgl_lahir
                ?? $trial?->tgl_lahir
                ?? '';

            $prefilledTmptLahir =
                $selectedStudent->tempat_lahir
                ?? $trial?->tempat_lahir
                ?? '';

            $prefilledOrangtua =
                $selectedStudent->orangtua
                ?? $trial?->orangtua
                ?? '';

            $prefilledInfo =
                $selectedStudent->informasi_bimba
                ?? $trial?->info
                ?? '';
            $prefilledTmptLahir = $selectedStudent->tempat_lahir 
                ?? $trial?->tempat_lahir 
                ?? '';
            $prefilledHpAyah = $selectedStudent->hp_ayah 
                ?? $trial?->hp_ayah 
                ?? '';

            $prefilledHpIbu  = $selectedStudent->hp_ibu 
                ?? $trial?->hp_ibu 
                ?? '';
            $prefilledAlamat = $selectedStudent->alamat 
                ?? $trial?->alamat 
                ?? '';
            // Detail Alamat
            $prefilledNoRumah   = $selectedStudent->no_rumah ?? $trial?->no_rumah ?? '';
            $prefilledRt        = $selectedStudent->rt ?? $trial?->rt ?? '';
            $prefilledRw        = $selectedStudent->rw ?? $trial?->rw ?? '';
            $prefilledKelurahan = $selectedStudent->kelurahan ?? $trial?->kelurahan ?? '';
            $prefilledKecamatan = $selectedStudent->kecamatan ?? $trial?->kecamatan ?? '';
            $prefilledKodyaKab  = $selectedStudent->kodya_kab ?? $trial?->kodya_kab ?? '';
            $prefilledProvinsi  = $selectedStudent->provinsi ?? $trial?->provinsi ?? '';
            $prefilledHari = $selectedStudent->hari ?? $trial?->hari ?? '';
            $prefilledJam  = $selectedStudent->jam  ?? $trial?->jam  ?? '';
        }
    }

    $hargaSaptataruna = HargaSaptataruna::all();

    $kdOptions = ['A','B','C','D','E','F'];

    $sppMapping = [];

    foreach ($hargaSaptataruna as $row) {
        foreach ($kdOptions as $KD) {
            $col = strtolower($KD);
            $sppMapping[$row->kode][$KD] = (int) ($row->$col ?? 0);
        }
    }

    $tahapanOptions = ['Persiapan','Lanjutan'];
    $kelasOptions = ['biMBA-AIUEO','English biMBA'];

    $guruOptions = Profile::where('jabatan','!=','Kepala Unit')
        ->orderBy('nama')
        ->pluck('nama')
        ->toArray();

    $kodeJadwalOptions = [
        '108','109','110','111','112','113','114','115','116',
        '208','209','210','211',
        '308','309','310','311'
    ];

        $levelOptions = ['Level 1', 'Level 2', 'Level 3', 'Level 4'];
        $jenisKbmOptions = ['Full TM', 'Full DLC', 'Kombinasi TM & DLC'];
        $infoOptions = ['Brosur', 'Event', 'Humas', 'Internet', 'Spanduk', 'Lainnya'];
        $asalModulOptions = ['biMBA IM', 'biMBA Unit'];

    $penerimaanPrefill = array_fill_keys([
        'kwitansi','via','bulan','tahun','tanggal',
        'daftar','voucher','spp_rp','spp','kaos',
        'kpk','sertifikat','stpb','tas','event','lain_lain'
    ],null);

    if($selectedStudent?->bukuInduk){

        $bi = $selectedStudent->bukuInduk;

        $penerimaanPrefill['spp_rp'] =
            $bi->spp ? (int)$bi->spp : null;

        $penerimaanPrefill['spp'] =
            trim(($bi->gol ? $bi->gol.'/' : '').($bi->kd ?? ''))
            ?: ($bi->kd ?? null);
    }

    $daftarList = HargaSaptataruna::whereIn('kode', ['bA', 'Eb'])
    ->orWhere('nama', 'LIKE', '%biMBA-AIUEO%')
    ->orWhere('nama', 'LIKE', '%English biMBA%')
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'          => $item->kode,
            'nama'          => $item->nama,
            'harga_duafa'   => (float)($item->duafa ?? 0),
            'harga_promo'   => (float)($item->promo_2019 ?? 0),
            'harga_daftar'  => (float)($item->daftar_ulang ?? 0),
            'harga_spesial' => (float)($item->spesial ?? 0),
            'harga_umum1'   => (float)($item->umum1 ?? 0),
            'harga_umum2'   => (float)($item->umum2 ?? 0),
        ];
    });
    // =====================
// KAOS (Perbaikan Query)
$hargaKaos = HargaSaptataruna::where('kategori', 'PENJUALAN')
    ->where(function($q) {
        $q->where('nama', 'LIKE', '%kaos%')
          ->orWhere('nama', 'LIKE', '%KAS%')
          ->orWhere('kode', 'LIKE', '%KAS%');
    })
    ->get();

$kaosPendekList = $hargaKaos->filter(function ($item) {
    $nama = strtolower($item->nama ?? '');
    return strpos($nama, 'pendek') !== false || 
           strpos($nama, 'lengan pendek') !== false ||
           strpos($nama, 'pendek') !== false;
})->map(function ($item) {
    return [
        'kode'   => $item->kode,
        'nama'   => $item->nama,
        'harga'  => (float)($item->harga ?? 0),
    ];
})->values();

$kaosPanjangList = $hargaKaos->filter(function ($item) {
    $nama = strtolower($item->nama ?? '');
    return strpos($nama, 'panjang') !== false || 
           strpos($nama, 'lengan panjang') !== false;
})->map(function ($item) {
    return [
        'kode'   => $item->kode,
        'nama'   => $item->nama,
        'harga'  => (float)($item->harga ?? 0),
    ];
})->values();

// =====================
// KPK
// =====================

$kpkList = HargaSaptataruna::where('nama', 'LIKE', '%KPK%')
    ->orWhere('kode', 'LIKE', '%KPK%')
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'  => $item->kode,
            'nama'  => $item->nama,
            'harga' => (float)$item->harga,
        ];
    })
    ->values();

// =====================
// TAS
// =====================

$tasList = HargaSaptataruna::where('nama', 'LIKE', '%TAS%')
    ->orWhere('kode', 'LIKE', '%TAS%')
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'  => $item->kode,
            'nama'  => $item->nama,
            'harga' => (float)$item->harga,
        ];
    })
    ->values();

// =====================
// SERTIFIKAT
// =====================

$sertifikatList = HargaSaptataruna::where('nama', 'LIKE', '%SERTIFIKAT%')
    ->orWhere('nama', 'LIKE', '%STF%')
    ->orWhere('kode', 'LIKE', '%STF%')
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'  => $item->kode,
            'nama'  => $item->nama,
            'harga' => (float)$item->harga,
        ];
    })
    ->values();

// =====================
// STPB
// =====================

$stpbList = HargaSaptataruna::where('nama', 'LIKE', '%STPB%')
    ->orWhere('kode', 'LIKE', '%STPB%')
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'  => $item->kode,
            'nama'  => $item->nama,
            'harga' => (float)$item->harga,
        ];
    })
    ->values();

// =====================
// RBAS
// =====================

$rbasList = HargaSaptataruna::where('nama', 'LIKE', '%RBAS%')
    ->orWhere('kode', 'LIKE', '%RBAS%')
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'  => $item->kode,
            'nama'  => $item->nama,
            'harga' => (float)$item->harga,
        ];
    })
    ->values();

// =====================
// BCABS01
// =====================

$bcabs01List = HargaSaptataruna::where('kode', 'BCABS.01')
    ->orWhere('kode', 'LIKE', '%BCABS01%')
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'  => $item->kode,
            'nama'  => $item->nama,
            'harga' => (float)$item->harga,
        ];
    })
    ->values();

// =====================
// BCABS02
// =====================

$bcabs02List = HargaSaptataruna::where('kode', 'BCABS.02')
    ->orWhere('kode', 'LIKE', '%BCABS02%')
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'  => $item->kode,
            'nama'  => $item->nama,
            'harga' => (float)$item->harga,
        ];
    })
    ->values();

     $noteGaransiOptions = ['Berkebutuhan Khusus', 'Tidak Memenuhi Syarat'];

    return view('registrations.create',compact(

    'students',
    'selectedStudentId',

    'prefilledNim',
    'prefilledNama',
    'prefilledUnit',
    'prefilledCabang',

    'prefilledTglLahir',
    'prefilledTmptLahir',
    'prefilledOrangtua',
    'prefilledInfo',
    'prefilledTmptLahir',
    'prefilledHpAyah',
    'prefilledHpIbu',
    'prefilledAlamat',
    'prefilledNoRumah',
    'prefilledRt',
    'prefilledRw',
    'prefilledKelurahan',
    'prefilledKecamatan',
    'prefilledKodyaKab',
    'prefilledProvinsi',
    'prefilledHari',
    'prefilledJam',

    'hargaSaptataruna',
    'kdOptions',
    'sppMapping',
    'levelOptions',
    'jenisKbmOptions',
    'infoOptions',
    'asalModulOptions',
    'tahapanOptions',
    'kelasOptions',
    'guruOptions',
    'kodeJadwalOptions',
    'penerimaanPrefill',
    'selectedStudent',

    // =====================
    // BIAYA
    // =====================
    'daftarList',
    'kaosPendekList',
    'kaosPanjangList',
    'kpkList',
    'tasList',
    'sertifikatList',
    'stpbList',
    'rbasList',
    'bcabs01List',
    'bcabs02List',
    'noteGaransiOptions',

));
}

// ===== STORE (CREATE) =====
public function store(Request $request)
{
    $data = $request->validate([
        'student_id'         => 'required|exists:students,id',
        'status'             => ['required', Rule::in(['pending','verified','accepted','rejected'])],
        'tanggal_daftar'     => 'nullable|date',
        'tanggal_penerimaan' => 'nullable|date',
        'gelombang'          => 'nullable|string|max:100',
        'program'            => 'nullable|string|max:100',

        // BI Data
        'bi.nim'             => 'nullable|string',
        'bi.nama'            => 'nullable|string',
        'bi.tahap'           => 'nullable|string',
        'bi.tgl_tahapan'     => 'nullable|string',
        'bi.jenis_kbm'       => 'nullable|string',
        
        'bi.level'           => 'nullable|string',
        'bi.tgl_level'       => 'nullable|string',
        'bi.no_telp_hp'      => 'nullable|string',
        'bi.alamat_murid'    => 'nullable|string',
        'bi.asal_modul'      => 'nullable|string',

        'bi.kelas'           => 'nullable|string',
        'bi.gol'             => 'nullable|string',
        'bi.kd'              => 'nullable|string',
        'bi.guru'            => 'nullable|string',
        'bi.kode_jadwal'     => 'nullable|string',
        'bi.hari_jam'        => 'nullable|string',
        'bi.spp'             => 'nullable|string',

        // Garansi
        'bi.tgl_surat_garansi' => 'nullable|string',
        'bi.note_garansi'      => 'nullable|string',

        // Biaya (Flat)
        'daftar'             => 'nullable|string',
        'voucher'            => 'nullable|string',
        'spp_rp'             => 'nullable|string',
        'kaos_pendek'        => 'nullable|string',
        'kaos_panjang'       => 'nullable|string',
        'kpk'                => 'nullable|string',
        'tas'                => 'nullable|string',
        'sertifikat'         => 'nullable|string',
        'stpb'               => 'nullable|string',
        'event'              => 'nullable|string',
        'lain_lain'          => 'nullable|string',
        'rbas'               => 'nullable|string',
        'BCABS01'            => 'nullable|string',
        'BCABS02'            => 'nullable|string',

        'attachment'         => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:3072',
    ]);

    $student = Student::with('muridTrial', 'bukuInduk')->findOrFail($data['student_id']);

    // ====================== CEK DUPLIKAT REGISTRASI ======================
    $currentTahunAjaran = Registration::currentAcademicYear() ?? date('Y') . '/' . (date('Y') + 1);

    $existingReg = Registration::where('student_id', $student->id)
        ->where('tahun_ajaran', $currentTahunAjaran)
        ->whereIn('status', ['pending', 'verified', 'accepted'])
        ->first();

    if ($existingReg) {
        return redirect()->route('registrations.edit', $existingReg->id)
            ->with('warning', 'Registrasi untuk siswa ini di tahun ajaran ' . $currentTahunAjaran . ' sudah ada.');
    }

    // ====================== HELPER NORMALISASI TANGGAL ======================
    $normalizeDate = function ($value) {
        if (empty($value)) return null;
        $value = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $value, $m)) {
            $day   = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year  = $m[3];
            return "{$year}-{$month}-{$day}";
        }
        

        return null;
    };

    // ... (BI DATA, PAY, FINAL DATA tetap sama seperti sebelumnya) ...

    // ====================== BI DATA ======================
    $biInput = $request->input('bi', []);
    
    $bi = [
        'nim'                => $biInput['nim'] ?? $student->nim,
        'nama'               => $biInput['nama'] ?? $student->nama,
        'tahap'              => $biInput['tahap'] ?? null,
        'tgl_tahapan'        => $normalizeDate($biInput['tgl_tahapan'] ?? null),
        'jenis_kbm'          => $biInput['jenis_kbm'] ?? null,
        
        'level'              => $biInput['level'] ?? null,
        'tgl_level'          => $normalizeDate($biInput['tgl_level'] ?? null),
        'no_telp_hp'         => $biInput['no_telp_hp'] ?? null,
        'alamat_murid'       => $biInput['alamat_murid'] ?? null,
        'asal_modul'         => $biInput['asal_modul'] ?? null,

        'kelas'              => $biInput['kelas'] ?? 'biMBA-AIUEO',
        'gol'                => strtoupper($biInput['gol'] ?? '-'),
        'kd'                 => strtoupper($biInput['kd'] ?? '-'),
        'guru'               => $biInput['guru'] ?? '-',
        'kode_jadwal'        => $this->convertJadwalToKode(
                                $biInput['hari'] ?? $student->hari ?? null, 
                                $biInput['jam'] ?? $student->jam ?? null
                            ),
        'hari_jam'           => trim(($biInput['hari'] ?? '') . ' ' . ($biInput['jam'] ?? '')),
        'spp'                => null,

        'tgl_surat_garansi'  => $normalizeDate($biInput['tgl_surat_garansi'] ?? null),
        'note_garansi'       => $biInput['note_garansi'] ?? null,
    ];

    // Hitung SPP
    if (!empty($biInput['spp'])) {
        $bi['spp'] = (int) preg_replace('/\D/', '', $biInput['spp']);
    } elseif (!empty($bi['gol']) && !empty($bi['kd'])) {
        $row = HargaSaptataruna::where('kode', $bi['gol'])->first();
        $col = strtolower($bi['kd']);
        $bi['spp'] = $row ? (int)($row->$col ?? 0) : null;
    }

    // ====================== DATA BIAYA ======================
    $pay = [
        'kwitansi'   => $request->kwitansi ?? null,
        'via'        => $request->via ?? null,
        'bulan'      => $request->bulan ?? null,
        'tahun'      => $request->tahun ?? null,
        'tanggal_penerimaan' => $this->tryParseDateToYmd($request->tanggal_penerimaan ?? now()),

        'daftar'     => $this->parseMoney($request->daftar ?? 0),
        'voucher'    => $this->parseMoney($request->voucher ?? 0),
        'spp_rp'     => $this->parseMoney($request->spp_rp ?? 0),
        'kaos'       => $this->parseMoney(($request->kaos_pendek ?? 0) + ($request->kaos_panjang ?? 0)),
        'kpk'        => $this->parseMoney($request->kpk ?? 0),
        'tas'        => $this->parseMoney($request->tas ?? 0),
        'sertifikat' => $this->parseMoney($request->sertifikat ?? 0),
        'stpb'       => $this->parseMoney($request->stpb ?? 0),
        'event'      => $this->parseMoney($request->event ?? 0),
        'lain_lain'  => $this->parseMoney($request->lain_lain ?? 0),
    ];

    // ====================== FINAL DATA ======================
    $finalData = [
        'student_id'         => $data['student_id'],
        'gelombang'          => $data['gelombang'] ?? null,
        'program'            => $data['program'] ?? null,
        'status'             => $data['status'],
        'tanggal_daftar'     => $data['tanggal_daftar'] ?? now()->format('Y-m-d'),
        'tahun_ajaran'       => $currentTahunAjaran,   // ← Pakai variabel yang sudah diambil
        
        'bimba_unit'         => $student->bimba_unit,
        'no_cabang'          => $student->no_cabang,

        'tahap'              => $bi['tahap'],
        'tgl_tahapan'        => $bi['tgl_tahapan'],
        'level'              => $bi['level'],
        'tgl_level'          => $bi['tgl_level'],
        'jenis_kbm'          => $bi['jenis_kbm'],
        'kelas'              => $bi['kelas'],
        'gol'                => $bi['gol'],
        'kd'                 => $bi['kd'],
        'spp'                => $bi['spp'],
        'guru'               => $bi['guru'],
        'kode_jadwal'        => $bi['kode_jadwal'],
        'hari_jam'           => $bi['hari_jam'],

        'no_telp_hp'         => $bi['no_telp_hp'],
        'alamat_murid'       => $bi['alamat_murid'],
        'asal_modul'         => $bi['asal_modul'],

        'periode'            => $biInput['periode'] ?? null,
        'tgl_mulai'          => $normalizeDate($biInput['tgl_mulai'] ?? null),
        'tgl_akhir'          => $normalizeDate($biInput['tgl_akhir'] ?? null),
        'alert'              => $biInput['alert'] ?? null,

        'tgl_bayar'          => $normalizeDate($biInput['tgl_bayar'] ?? null),
        'tgl_selesai'        => $normalizeDate($biInput['tgl_selesai'] ?? null),
        'alert2'             => $biInput['alert2'] ?? null,

        'tgl_surat_garansi'  => $bi['tgl_surat_garansi'],
        'note_garansi'       => $bi['note_garansi'],
        'tgl_pengajuan_garansi' => $normalizeDate($biInput['tgl_pengajuan_garansi'] ?? null),
        'tgl_selesai_garansi'   => $normalizeDate($biInput['tgl_selesai_garansi'] ?? null),
        'masa_aktif_garansi'    => $biInput['masa_aktif_garansi'] ?? null,
        'perpanjang_garansi'    => $request->boolean('perpanjang_garansi'),
        'alasan_garansi'        => $biInput['alasan_garansi'] ?? null,

        'kwitansi'           => $pay['kwitansi'],
        'via'                => $pay['via'],
        'bulan'              => $pay['bulan'],
        'tahun'              => $pay['tahun'],
        'tanggal_penerimaan' => $pay['tanggal_penerimaan'],
        'daftar'             => $pay['daftar'],
        'voucher'            => $pay['voucher'],
        'spp_rp'             => $pay['spp_rp'],
        'spp_keterangan'     => $request->spp ?? null,
        'kaos'               => $pay['kaos'],
        'kpk'                => $pay['kpk'],
        'sertifikat'         => $pay['sertifikat'],
        'stpb'               => $pay['stpb'],
        'tas'                => $pay['tas'],
        'event'              => $pay['event'],
        'lain_lain'          => $pay['lain_lain'],
    ];

    if ($request->hasFile('attachment')) {
        $finalData['attachment_path'] = $request->file('attachment')->store('registrations', 'public');
    }

    // ====================== SIMPAN ======================
    $reg = null;
    DB::transaction(function () use ($finalData, $student, $bi, $pay, &$reg) {
        $reg = Registration::create($finalData);

        if ($reg->status === 'accepted') {
            $this->commitBukuIndukWithPayload(
                $student,
                $reg->status,
                array_merge($bi, ['penerimaan' => $pay]),
                $student->bimba_unit,
                $student->no_cabang,
                $reg->tanggal_daftar
            );
        }
    });

    // Setelah simpan registrasi
    if ($reg) {
        $this->syncRegistrationToTrial($reg);
    }

    $this->createOrUpdateHumaFromStudent($student);

    // Update Trial Status
    if (($request->has('from_trial') || $student->muridTrial) && $reg) {
        $muridTrial = $student->muridTrial;
        if ($muridTrial) {
            $newTrialStatus = ($reg->status === 'accepted') ? 'terdaftar' : 'lanjut_daftar';
            $muridTrial->update([
                'status_trial'  => $newTrialStatus,
                'tanggal_aktif' => now()->format('Y-m-d'),
            ]);
        }
    }

    $message = 'Registrasi berhasil disimpan dengan status ' . strtoupper($reg->status ?? 'pending');

    if ($reg && $reg->status === 'accepted') {
        return redirect()->route('penerimaan.create', [
            'nim'        => $student->nim ?? $bi['nim'],
            'student_id' => $student->id,
        ])->with('success', $message . '. Silakan lengkapi data penerimaan.');
    } else {
        return redirect()->route('registrations.index')
                         ->with('success', $message . '. Data tersimpan.');
    }
}

    // ===== EDIT =====
   public function edit(Registration $registration)
{
    // Eager Loading Relasi
    $registration->load(['student.bukuInduk', 'muridTrial']);

    // Data Students lengkap
    $students = \App\Models\Student::orderBy('nama')
        ->get([
            'id', 'nim', 'nama',
            'tempat_lahir', 'tgl_lahir',
            'orangtua', 'alamat',
            'hp_ayah', 'hp_ibu',
            'bimba_unit', 'no_cabang',
            'hari', 'jam'
        ]);

    $biMaster = optional($registration->student)->bukuInduk;
    $trial    = $registration->muridTrial;
    $student  = $registration->student;

    // Data pendukung form
    $hargaSaptataruna = \App\Models\HargaSaptataruna::all();
    $kdOptions = ['A','B','C','D','E','F'];

    $sppMapping = [];
    foreach ($hargaSaptataruna as $row) {
        foreach ($kdOptions as $KD) {
            $col = strtolower($KD);
            $sppMapping[$row->kode][$KD] = (int) ($row->$col ?? 0);
        }
    }

    $tahapanOptions = ['Persiapan', 'Lanjutan'];
    $kelasOptions   = ['biMBA-AIUEO', 'English biMBA'];
    $levelOptions = ['Level 1', 'Level 2', 'Level 3', 'Level 4'];
    $jenisKbmOptions = ['Full TM', 'Full DLC', 'Kombinasi TM & DLC'];
    $infoOptions = ['Brosur', 'Event', 'Humas', 'Internet', 'Spanduk', 'Lainnya'];
    $asalModulOptions = ['biMBA IM', 'biMBA Unit'];

    // Guru Options
    $guruOptions = [];
    $allGuru = Profile::whereIn('jabatan', ['Guru', 'Pengajar'])
                ->orderBy('nama')
                ->pluck('nama')
                ->toArray();

    if ($student && !empty($student->bimba_unit)) {
        $guruUnit = Profile::where('bimba_unit', $student->bimba_unit)
                    ->whereIn('jabatan', ['Guru', 'Pengajar'])
                    ->orderBy('nama')
                    ->pluck('nama')
                    ->toArray();
        $guruOptions = $guruUnit ?: $allGuru;
    } else {
        $guruOptions = $allGuru;
    }

    $savedGuru = $registration->guru ?? optional($biMaster)->guru;
    if ($savedGuru && !in_array($savedGuru, $guruOptions)) {
        $guruOptions[] = $savedGuru;
    }

    $kodeJadwalOptions = [
        '108','109','110','111','112','113','114','115','116',
        '208','209','210','211','308','309','310','311'
    ];

    // Prefill Data
    $biPrefill = [
        'tahap'        => $registration->tahap ?? optional($biMaster)->tahap,
        'kelas'        => $registration->kelas ?? optional($biMaster)->kelas,
        'gol'          => $registration->gol   ?? optional($biMaster)->gol,
        'kd'           => $registration->kd    ?? optional($biMaster)->kd,
        'spp'          => $registration->spp   ?? optional($biMaster)->spp,
        'guru'         => $registration->guru  ?? optional($biMaster)->guru,
        'kode_jadwal'  => $registration->kode_jadwal ?? optional($biMaster)->kode_jadwal,
        'hari'         => $registration->hari  ?? optional($biMaster)->hari ?? $student?->hari ?? $trial?->hari,
        'jam'          => $registration->jam   ?? optional($biMaster)->jam  ?? $student?->jam  ?? $trial?->jam,
       
        // === TAMBAHKAN INI ===
        'jenis_kbm'         => $registration->jenis_kbm ?? optional($biMaster)->jenis_kbm,
        'level'             => $registration->level ?? optional($biMaster)->level,
        'tgl_level'         => $registration->tgl_level ?? optional($biMaster)->tgl_level,
        'asal_modul'        => $registration->asal_modul ?? optional($biMaster)->asal_modul,
        'keterangan_optional' => $registration->keterangan_optional ?? optional($biMaster)->keterangan_optional,
        // =====================

        'tempat_lahir' => optional($biMaster)->tempat_lahir ?? $student?->tempat_lahir ?? $trial?->tempat_lahir,
        'tanggal_lahir'=> optional($biMaster)->tgl_lahir   ?? $student?->tgl_lahir   ?? $trial?->tgl_lahir,
        'orangtua'     => optional($biMaster)->orangtua    ?? $student?->orangtua    ?? $trial?->orangtua,
        'alamat'       => optional($biMaster)->alamat      ?? $student?->alamat      ?? $trial?->alamat,

        'no_telp'      => optional($biMaster)->no_telp ?? 
                          trim(implode(' / ', array_filter([
                              $student?->hp_ayah, 
                              $student?->hp_ibu
                          ]))),
    ];

    $isAdmin = auth()->check() && (
        auth()->user()->role === 'admin' || 
        (auth()->user()->is_admin ?? false)
    );

    return view('registrations.edit', compact(
        'registration',
        'students',
        'hargaSaptataruna',
        'kdOptions',
        'sppMapping',
        'tahapanOptions',
        'kelasOptions',
        'levelOptions',
        'jenisKbmOptions',
        'infoOptions',
        'asalModulOptions',
        'biPrefill',
        'guruOptions',
        'kodeJadwalOptions',
        'isAdmin',
        'trial',
        'student'
    ));
}

    public function update(Request $request, Registration $registration)
{
    $data = $request->validate([
        'student_id'         => ['required', 'exists:students,id'],
        'gelombang'          => ['nullable', 'string', 'max:100'],
        'program'            => ['nullable', 'string', 'max:100'],
        'status'             => ['required', Rule::in(['pending', 'verified', 'accepted', 'rejected'])],
        'tanggal_daftar'     => ['nullable', 'date'],
        'tanggal_penerimaan' => ['nullable', 'date'],           // ← Pastikan ini ada
        'bi'                 => ['array'],
        'penerimaan'         => ['nullable', 'array'],          // ← tambahkan ini
        'attachment'         => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:3072'],
    ]);

   $user = Auth::user();
$isAdmin = $user && ($user->role === 'admin' || ($user->is_admin ?? false));

// Izinkan semua user mengubah ke Accepted
if ($data['status'] === 'accepted') {
    Log::info('Status diubah ke Accepted', [
        'user_id' => $user?->id,
        'user_role' => $user?->role,
        'is_admin' => $isAdmin,
        'registration_id' => $registration->id
    ]);
}

    $student = Student::with('bukuInduk')->findOrFail($data['student_id']);
    $bimbaUnit = $student->bimba_unit;
    $noCabang = $student->no_cabang;

    $oldStatus = $registration->status;

    // ====================== PROSES BI ======================
    $bi = $request->input('bi', []);
    $bi['tahap']      = $bi['tahap'] ?? null;
    $bi['kelas']      = $bi['kelas'] ?? null;
    $bi['gol']        = strtoupper($bi['gol'] ?? '');
    $bi['kd']         = strtoupper($bi['kd'] ?? '');
    $bi['guru']       = $bi['guru'] ?? null;
    $bi['kode_jadwal'] = $bi['kode_jadwal'] ?? null;
    $bi['hari_jam']   = $bi['jam'] ?? null;   // sesuaikan dengan nama field form

    // Hitung SPP
    $rawSpp = $bi['spp'] ?? null;
    if ($rawSpp !== null && trim($rawSpp) !== '') {
        $bi['spp'] = (int) preg_replace('/\D/', '', $rawSpp);
    } else {
        if (!empty($bi['gol']) && !empty($bi['kd'])) {
            $row = HargaSaptataruna::where('kode', $bi['gol'])->first();
            $col = strtolower($bi['kd']);
            $bi['spp'] = $row && isset($row->$col) ? (int) $row->$col : null;
        } else {
            $bi['spp'] = null;
        }
    }

    // ====================== PROSES PENERIMAAN ======================
    $p = $request->input('penerimaan', []);
    $pay = [
        'tanggal_penerimaan' => $this->tryParseDateToYmd(
            $request->tanggal_penerimaan ?? $p['tanggal'] ?? $p['tanggal_penerimaan'] ?? null
        ),
        'kwitansi'  => $p['kwitansi'] ?? null,
        'via'       => $p['via'] ?? null,
        'bulan'     => $p['bulan'] ?? null,
        'tahun'     => $p['tahun'] ?? null,
        'daftar'    => $this->parseMoney($p['daftar'] ?? null),
        'voucher'   => $this->parseMoney($p['voucher'] ?? null),
        'spp_rp'    => $this->parseMoney($p['spp_rp'] ?? null),
        // ... sisanya sesuai kebutuhan
        'kaos'      => $this->parseMoney($p['kaos'] ?? null),
        'kpk'       => $this->parseMoney($p['kpk'] ?? null),
        // dst...
    ];

    // ====================== UPDATE REGISTRATION ======================
    $updateData = array_merge($data, [
        'bimba_unit'         => $bimbaUnit,
        'no_cabang'          => $noCabang,
        'tahap'              => $bi['tahap'],
        'kelas'              => $bi['kelas'],
        'gol'                => $bi['gol'],
        'kd'                 => $bi['kd'],
        'spp'                => $bi['spp'],
        'guru'               => $bi['guru'],
        'kode_jadwal'        => $bi['kode_jadwal'],
        'hari_jam'           => $bi['hari_jam'],

        'tanggal_penerimaan' => $pay['tanggal_penerimaan'],
        'kwitansi'           => $pay['kwitansi'],
        'via'                => $pay['via'],
        'bulan'              => $pay['bulan'],
        'tahun'              => $pay['tahun'],
        'daftar'             => $pay['daftar'],
        'voucher'            => $pay['voucher'],
        'spp_rp'             => $pay['spp_rp'],
        'spp_keterangan'     => $p['spp'] ?? null,
        'kaos'               => $pay['kaos'],
        'kpk'                => $pay['kpk'],
        // tambahkan field lain yang diperlukan
    ]);

    $registration->update($updateData);

    // Attachment
    if ($request->hasFile('attachment')) {
        if ($registration->attachment_path) {
            Storage::disk('public')->delete($registration->attachment_path);
        }
        $registration->attachment_path = $request->file('attachment')->store('registrations', 'public');
        $registration->save();
    }

    // Commit Buku Induk hanya jika status berubah ke Accepted
    if ($oldStatus !== 'accepted' && $registration->status === 'accepted') {
        $bi['penerimaan'] = $pay;
        $this->commitBukuIndukWithPayload(
            $student,
            $registration->status,
            $bi,
            $bimbaUnit,
            $noCabang,
            $registration->tanggal_daftar
        );

        // === TAMBAHKAN INI: Buat/Update HUMAS Otomatis ===
        $this->createOrUpdateHumaFromStudent($student);
        $this->syncRegistrationToTrial($registration);
    }

    // Redirect
    if ($registration->status === 'accepted') {
        return redirect()->route('penerimaan.create', [
            'nim' => $student->nim,
            'student_id' => $student->id,
        ])->with('success', 'Registrasi berhasil diupdate menjadi ACCEPTED.');
    } 

    return redirect()->route('registrations.index')
                     ->with('success', 'Registrasi berhasil diperbarui.');
}

    public function destroy(Registration $registration)
    {
        if ($registration->attachment_path && Storage::disk('public')->exists($registration->attachment_path)) {
            Storage::disk('public')->delete($registration->attachment_path);
        }
        $registration->delete();
        return redirect()->route('registrations.index')->with('success', 'Registrasi dihapus.');
    }

protected function commitBukuIndukWithPayload(
    Student $student,
    string $regStatus,
    array $bi = [],
    ?string $bimbaUnit = null,
    ?string $noCabang = null,
    ?string $tanggalDaftar = null
): void {

    if ($regStatus !== 'accepted') {
        Log::info("Commit BukuInduk dibatalkan: {$regStatus}");
        return;
    }

    DB::transaction(function () use ($student, $bi, $bimbaUnit, $noCabang, $tanggalDaftar) {

        // ====================== NIM GENERATION ======================
$nimSekarang = trim($student->nim ?? '');

// Buat NIM baru jika kosong, "-" atau kurang dari 9 digit
if (empty($nimSekarang) || $nimSekarang === '-' || strlen($nimSekarang) < 9) {
    
    $noCabangClean = trim($noCabang ?? $student->no_cabang ?? '');
    $bimbaUnitClean = trim($bimbaUnit ?? $student->bimba_unit ?? '');

    if (empty($noCabangClean)) {
        $noCabangClean = '05141'; // fallback
    }

    $prefix = str_pad($noCabangClean, 5, '0', STR_PAD_LEFT);

    // Ambil NIM terakhir
    $lastNIM = BukuInduk::where('nim', 'LIKE', $prefix . '%')
        ->lockForUpdate()
        ->orderByRaw('CAST(SUBSTRING(nim, 6) AS UNSIGNED) DESC')
        ->value('nim');

    if (!$lastNIM && !empty($bimbaUnitClean)) {
        $lastNIM = BukuInduk::where('nim', 'LIKE', $prefix . '%')
            ->where('bimba_unit', 'LIKE', "%{$bimbaUnitClean}%")
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(nim, 6) AS UNSIGNED) DESC')
            ->value('nim');
    }

    $nextNumber = $lastNIM ? (int) substr($lastNIM, 5) + 1 : 1;
    $newNim = $prefix . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);

    // Anti collision
    $attempt = 0;
    while (BukuInduk::where('nim', $newNim)->exists() && $attempt < 15) {
        $nextNumber++;
        $newNim = $prefix . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
        $attempt++;
    }

    $student->nim = $newNim;
    $student->save();

    Log::info("✅ NIM BARU dibuat saat Accept (dari Edit)", [
        'student_id' => $student->id,
        'nim'        => $newNim,
        'prefix'     => $prefix
    ]);
}

        // ====================== PREPARE DATA ======================
        $pay = $bi['penerimaan'] ?? [];

        $tglDaftarFinal = $tanggalDaftar 
            ?? $bi['tanggal_daftar'] 
            ?? $student->tanggal_daftar 
            ?? now()->format('Y-m-d');

        // ==================== TANGGAL MASUK ====================
        $tglMasukFinal = null;
        if (!empty($pay['tanggal_penerimaan'])) {
            $tglMasukFinal = $pay['tanggal_penerimaan'];
            Log::info("✅ Menggunakan tanggal_penerimaan untuk tgl_masuk", [
                'tanggal_penerimaan' => $tglMasukFinal
            ]);
        } elseif (!empty($pay['tanggal'])) {
            $tglMasukFinal = $pay['tanggal'];
        } else {
            $tglMasukFinal = $tglDaftarFinal;
            Log::warning("⚠️ tanggal_penerimaan kosong → fallback ke tgl_daftar", [
                'tgl_daftar' => $tglDaftarFinal
            ]);
        }

        // ==================== HITUNG USIA ====================
        $usia = 0;
        if ($student->tgl_lahir) {
            try {
                $usia = \Carbon\Carbon::parse($student->tgl_lahir)
                        ->diffInYears(\Carbon\Carbon::now());
            } catch (\Exception $e) {
                Log::error("Gagal menghitung usia", ['tgl_lahir' => $student->tgl_lahir]);
            }
        }

        // ====================== NORMALISASI DATA BUKU INDUK ======================
        // ====================== NORMALISASI DATA BUKU INDUK ======================
$biNorm = [
    'nama'           => $bi['nama'] ?? $student->nama ?? '',
    'kelas'          => $bi['kelas'] ?? 'biMBA-AIUEO',
    'gol'            => $bi['gol'] ?? '-',
    'kd'             => $bi['kd'] ?? '-',
    'guru'           => $bi['guru'] ?? '-',
    'tahap'          => $bi['tahap'] ?? null,
    'tgl_tahapan'    => $bi['tgl_tahapan'] ?? null,
    'jenis_kbm'      => $bi['jenis_kbm'] ?? null,
    
    // Data Baru dari Registration
    'level'          => $bi['level'] ?? null,
    'tgl_level'      => $bi['tgl_level'] ?? null,
    'no_telp_hp'     => $bi['no_telp_hp'] ?? null,
    'alamat_murid'   => $bi['alamat_murid'] ?? null,
    'asal_modul'     => $bi['asal_modul'] ?? null,

    'spp'            => $bi['spp'] ?? null,
    'kode_jadwal'    => $bi['kode_jadwal'] ?? null,

    // === FIELD BARU YANG BELUM MASUK ===
    'periode'                => $bi['periode'] ?? null,
    'tgl_mulai'              => $bi['tgl_mulai'] ?? null,
    'tgl_akhir'              => $bi['tgl_akhir'] ?? null,
    'alert'                  => $bi['alert'] ?? null,

    'tgl_bayar'              => $bi['tgl_bayar'] ?? null,
    'tgl_selesai'            => $bi['tgl_selesai'] ?? null,
    'alert2'                 => $bi['alert2'] ?? null,

    'tgl_surat_garansi'      => $bi['tgl_surat_garansi'] ?? null,
    'note_garansi'           => $bi['note_garansi'] ?? null,
    'tgl_pengajuan_garansi'  => $bi['tgl_pengajuan_garansi'] ?? null,
    'tgl_selesai_garansi'    => $bi['tgl_selesai_garansi'] ?? null,
    'masa_aktif_garansi'     => $bi['masa_aktif_garansi'] ?? null,
    'perpanjang_garansi'     => !empty($bi['tgl_surat_garansi']) ? 'Diberikan' : null,
    'alasan_garansi'         => $bi['alasan_garansi'] ?? null,

    // Data dari Student / Trial
    'tmpt_lahir'     => $bi['tmpt_lahir'] ?? $student->tempat_lahir ?? null,
    'tanggal_lahir'  => $bi['tanggal_lahir'] ?? $student->tgl_lahir ?? null,
    'orangtua'       => $student->orangtua ?? $trial?->orangtua ?? null,
    'info'           => $student->informasi_bimba ?? $trial?->info ?? null,
];

        // ====================== KONVERSI KODE JADWAL ======================
        if (!empty($bi['kode_jadwal']) && is_numeric($bi['kode_jadwal'])) {
            $biNorm['kode_jadwal'] = (string) $bi['kode_jadwal'];
        } 
        elseif (!empty($student->hari) || !empty($bi['hari_jam'] ?? null)) {
            $hariInput = $bi['hari_jam'] ?? $student->hari ?? null;
            $jamInput  = $student->jam ?? $bi['jam'] ?? null;

            $kodeJadwal = $this->convertJadwalToKode($hariInput, $jamInput);
            
            if ($kodeJadwal) {
                $biNorm['kode_jadwal'] = $kodeJadwal;
                Log::info("✅ Kode Jadwal dikonversi otomatis", [
                    'hari_input' => $hariInput,
                    'jam_input'  => $jamInput,
                    'kode_jadwal'=> $kodeJadwal
                ]);
            }
        }

        // ====================== BUKU INDUK ======================
        $trial = $student->muridTrial;
        $cleanBimbaUnit = $this->cleanUnitName($bimbaUnit ?? $student->bimba_unit ?? '');

        BukuInduk::updateOrCreate(
        ['nim' => $student->nim],
        [
        'nama'           => $biNorm['nama'],
        'bimba_unit'     => $cleanBimbaUnit,
        'no_cabang'      => $noCabang ?? $student->no_cabang,
        'status'         => 'Baru',

        'tgl_daftar'     => $tglDaftarFinal,
        'tgl_masuk'      => $tglMasukFinal,
        'tanggal_masuk'  => $tglMasukFinal,
        'tgl_aktif'      => $tglMasukFinal,

        'usia'           => $usia,

        // Semua data dari $biNorm
        'tahap'          => $biNorm['tahap'],
        'tgl_tahapan'    => $biNorm['tgl_tahapan'],
        'level'          => $biNorm['level'],
        'tgl_level'      => $biNorm['tgl_level'],
        'jenis_kbm'      => $biNorm['jenis_kbm'],
        'kelas'          => $biNorm['kelas'],
        'gol'            => $biNorm['gol'],
        'kd'             => $biNorm['kd'],
        'spp'            => $biNorm['spp'],
        'guru'           => $biNorm['guru'],
        'kode_jadwal'    => $biNorm['kode_jadwal'],

        'no_telp_hp'     => $biNorm['no_telp_hp'],
        'alamat_murid'   => $biNorm['alamat_murid'],
        'asal_modul'     => $biNorm['asal_modul'],
        'orangtua'       => $biNorm['orangtua'],
        'tmpt_lahir'     => $biNorm['tmpt_lahir'],
        'tgl_lahir'      => $biNorm['tanggal_lahir'],
        'info'           => $biNorm['info'],

        // Field Baru
        'periode'                => $biNorm['periode'],
        'tgl_mulai'              => $biNorm['tgl_mulai'],
        'tgl_akhir'              => $biNorm['tgl_akhir'],
        'alert'                  => $biNorm['alert'],

        'tgl_bayar'              => $biNorm['tgl_bayar'],
        'tgl_selesai'            => $biNorm['tgl_selesai'],
        'alert2'                 => $biNorm['alert2'],

        'tgl_surat_garansi'      => $biNorm['tgl_surat_garansi'],
        'note_garansi'           => $biNorm['note_garansi'],
        'tgl_pengajuan_garansi'  => $biNorm['tgl_pengajuan_garansi'],
        'tgl_selesai_garansi'    => $biNorm['tgl_selesai_garansi'],
        'masa_aktif_garansi'     => $biNorm['masa_aktif_garansi'],
        'perpanjang_garansi'     => $biNorm['perpanjang_garansi'],
        'alasan_garansi'         => $biNorm['alasan_garansi'],
    ]
);

        // ====================== PENERIMAAN ======================
        if (!empty($pay)) {
            $totalPayment = 
                ($pay['daftar'] ?? 0) + 
                ($pay['voucher'] ?? 0) + 
                ($pay['spp_rp'] ?? 0) + 
                ($pay['kaos'] ?? 0) + 
                ($pay['kpk'] ?? 0) + 
                ($pay['sertifikat'] ?? 0) + 
                ($pay['stpb'] ?? 0) + 
                ($pay['tas'] ?? 0) + 
                ($pay['event'] ?? 0) + 
                ($pay['lain_lain'] ?? 0);

            if ($totalPayment > 0) {
                $dt = Carbon::parse($pay['tanggal_penerimaan'] ?? $pay['tanggal'] ?? now());

                $penerimaanPayload = [
                    'via'           => $pay['via'] ?? 'Tunai',
                    'bulan'         => $pay['bulan'] ?? $dt->translatedFormat('F'),
                    'tahun'         => $pay['tahun'] ?? (int)$dt->format('Y'),
                    'tanggal'       => $dt->toDateString(),
                    'nim'           => $student->nim,
                    'nama_murid'    => $student->nama,
                    'bimba_unit'    => $cleanBimbaUnit,
                    'no_cabang'     => $noCabang,
                    'status'        => 'Baru',
                    'guru'          => $biNorm['guru'],
                    'kelas'         => $biNorm['kelas'],
                    'gol'           => $biNorm['gol'],
                    'kd'            => $biNorm['kd'],
                    
                    'daftar'        => (int)($pay['daftar'] ?? 0),
                    'voucher'       => (int)($pay['voucher'] ?? 0),
                    'spp'           => (int)($pay['spp_rp'] ?? 0),
                    'nilai_spp'     => (int)($pay['spp_rp'] ?? 0),
                    'kaos'          => (int)($pay['kaos'] ?? 0),
                    'kpk'           => (int)($pay['kpk'] ?? 0),
                    'sertifikat'    => (int)($pay['sertifikat'] ?? 0),
                    'stpb'          => (int)($pay['stpb'] ?? 0),
                    'tas'           => (int)($pay['tas'] ?? 0),
                    'event'         => (int)($pay['event'] ?? 0),
                    'lain_lain'     => (int)($pay['lain_lain'] ?? 0),
                ];

                $penerimaanPayload['total'] = $totalPayment;

                $kwitansi = $pay['kwitansi'] ?? ('REG-' . $student->nim . '-' . time());

                Penerimaan::updateOrCreate(
                    [
                        'nim'   => $student->nim, 
                        'bulan' => strtolower(trim($penerimaanPayload['bulan'])), 
                        'tahun' => $penerimaanPayload['tahun']
                    ],
                    array_merge($penerimaanPayload, ['kwitansi' => $kwitansi])
                );
            }
        }

        Log::info("✅ Commit Buku Induk selesai", [
            'nim'            => $student->nim,
            'unit'           => $cleanBimbaUnit,
            'tgl_masuk'      => $tglMasukFinal,
            'usia'           => $usia,
            'tgl_daftar'     => $tglDaftarFinal,
        ]);
    });
}

/**
 * Bersihkan nama unit (hapus nomor cabang di depan)
 */
private function cleanUnitName(?string $unitName): string
{
    if (empty($unitName)) {
        return '';
    }

    // Hapus pola seperti "05141 | ", "05141|", "05141 - ", dll
    $clean = trim(preg_replace('/^\s*\d+\s*[\|\-]\s*/', '', $unitName));
    
    return $clean ?: trim($unitName);
}

        
    // ===== Helper functions =====
    protected function enforceTrialStatus(Student $student, array &$data): void
    {
        if ($student->murid_trial_id && optional($student->muridTrial)->status_trial === 'batal') {
            $data['status'] = 'rejected';
        }
    }

    protected function parseMoney($v): ?int
    {
        if ($v === null || $v === '')
            return null;
        $raw = preg_replace('/[^\d]/', '', (string) $v);
        return $raw === '' ? null : (int) $raw;
    }

    protected function tryParseDateToYmd($val): ?string
    {
        if (!$val)
            return null;
        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function firstOrCreateFor(Student $student): Registration
{
    $currentYear = Registration::currentAcademicYear() ?? date('Y') . '/' . (date('Y') + 1);

    return DB::transaction(function () use ($student, $currentYear) {
        $existing = Registration::where('student_id', $student->id)
            ->where('tahun_ajaran', $currentYear)
            ->whereIn('status', ['pending', 'verified', 'accepted'])
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $payload = [
            'student_id'     => $student->id,
            'status'         => 'pending',
            'tanggal_daftar' => now(),
            'tahun_ajaran'   => $currentYear,
            'source'         => $student->source ?? 'direct',
            'created_by'     => Auth::id(),
        ];

        return Registration::create($payload);
    });
}
    public function show(Registration $registration)
    {
        // Kalau mau langsung ke halaman edit:
        return redirect()->route('registrations.edit', $registration->id);

        // Atau kalau nanti mau bikin halaman detail sendiri,
        // kamu bisa ganti jadi:
        // return view('registrations.show', compact('registration'));
    }


    protected function convertJadwalToKode(?string $hariJam, ?string $jam = null): ?string
{
    if (empty($hariJam)) {
        return null;
    }

    $text = strtoupper(trim($hariJam));

    // Bersihkan teks panjang
    $text = preg_replace('/\s*\(.+\)/', '', $text);   // hapus (SENIN | RABU | JUMAT)
    $text = preg_replace('/(SRJ|SKS|S6).*\1/', '$1', $text);
    $text = trim($text);

    $baseMap = [
        'SRJ' => 100,
        'SKS' => 200,
        'S6'  => 300,
        'S3'  => 300,
    ];

    $base = null;
    foreach ($baseMap as $key => $value) {
        if (str_contains($text, $key)) {
            $base = $value;
            break;
        }
    }

    if ($base === null) {
        return null;
    }

    // === LOGIKA BARU SESUAI PERMINTAAN ANDA ===
    $jamText = strtoupper(trim($jam ?? ''));

    // Khusus SRJ jam 11:00 WIB → 111
    if ($base === 100 && (str_contains($jamText, '11:00') || str_contains($jamText, '11.'))) {
        return '111';
    }

    // Jam pagi (08:00 / 09:00) → +8
    if (str_contains($jamText, '08:') || str_contains($jamText, '09:') || 
        str_contains($jamText, 'PAGI')) {
        return (string)($base + 8);
    }

    // Default untuk SRJ jam lain atau tidak terdeteksi → 109
    return (string)($base + 9);
}

/**
 * Convert tanggal Indonesia (DD-MM-YYYY atau DD/MM/YYYY) ke YYYY-MM-DD
 */
private function normalizeDate($value)
{
    if (empty($value)) return null;

    // Hapus spasi
    $value = trim($value);

    // Jika sudah format YYYY-MM-DD, return langsung
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
    }

    // Convert DD/MM/YYYY atau DD-MM-YYYY ke YYYY-MM-DD
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $value, $matches)) {
        $day   = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year  = $matches[3];
        return "{$year}-{$month}-{$day}";
    }

    return null;
}

/**
 * Buat atau Update data HUMAS otomatis dari Student
 * FIXED VERSION - Lebih aman & anti-duplikat
 */
/**
 * Buat atau Update HUMAS otomatis dari Student (Registration)
 * Disamakan dengan logic createHumasSafely
 */
protected function createOrUpdateHumaFromStudent(Student $student): void
{
    if (empty(trim($student->nama ?? ''))) {
        return;
    }

    $noCabang = trim($student->no_cabang ?? '05141'); // fallback aman
    $namaOrangTua = trim($student->orangtua 
        ?? $student->nama_ayah 
        ?? $student->nama_ibu 
        ?? ('Orang Tua ' . $student->nama));

    $noTelp = trim($student->no_telp 
        ?? $student->hp_ayah 
        ?? $student->hp_ibu);

    try {
        DB::transaction(function () use ($student, $noCabang, $namaOrangTua, $noTelp) {

            // 1. Cek duplikat berdasarkan nama + cabang
            $existing = DB::table('humas')
                ->where('no_cabang', $noCabang)
                ->where('nama', 'LIKE', "%{$namaOrangTua}%")
                ->first();

            if ($existing) {
                DB::table('humas')->where('id', $existing->id)->update([
                    'tgl_reg'    => $student->tanggal_masuk ?? now()->format('Y-m-d'),
                    'no_telp'    => $noTelp ?: $existing->no_telp,
                    'alamat'     => $student->alamat ?: $existing->alamat,
                    'bimba_unit' => $student->bimba_unit ?: $existing->bimba_unit,
                    'updated_at' => now(),
                ]);
                Log::info("Humas sudah ada → diupdate", ['nih' => $existing->nih]);
                return;
            }

            // 2. Generate NIH baru dengan logic lebih kuat
            $prefix = str_pad($noCabang, 5, '0', STR_PAD_LEFT) . '02';

            // Ambil NIH tertinggi dengan lock
            $lastNih = DB::table('humas')
                        ->where('no_cabang', $noCabang)
                        ->where('nih', 'LIKE', $prefix . '%')
                        ->whereRaw('LENGTH(nih) = 9')
                        ->lockForUpdate()
                        ->orderByRaw('CAST(SUBSTRING(nih, 7, 4) AS UNSIGNED) DESC')
                        ->value('nih');

            $lastSeq = 0;
            if ($lastNih) {
                $lastSeq = (int) substr($lastNih, 7); // posisi setelah prefix (05141 02)
            }

            $maxAttempts = 50;
            for ($i = 0; $i < $maxAttempts; $i++) {
                $newSeq = $lastSeq + $i + 1;
                $nih = $prefix . str_pad($newSeq, 4, '0', STR_PAD_LEFT);

                // Cek apakah NIH ini sudah ada
                if (!DB::table('humas')->where('nih', $nih)->exists()) {
                    DB::table('humas')->insert([
                        'tgl_reg'     => $student->tanggal_masuk ?? now()->format('Y-m-d'),
                        'nih'         => $nih,
                        'nama'        => $namaOrangTua,
                        'no_telp'     => $noTelp,
                        'status'      => 'baru',
                        'pekerjaan'   => 'Swasta',
                        'alamat'      => $student->alamat,
                        'bimba_unit'  => $student->bimba_unit,
                        'no_cabang'   => $noCabang,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    Log::info("✅ Humas baru dibuat", [
                        'nih' => $nih,
                        'nama' => $namaOrangTua,
                        'no_cabang' => $noCabang
                    ]);
                    return;
                }
            }

            Log::error("❌ Gagal membuat NIH unik setelah {$maxAttempts} percobaan", [
                'no_cabang' => $noCabang,
                'nama' => $namaOrangTua
            ]);

        });

    } catch (\Throwable $e) {
        Log::warning("Gagal create/update humas", [
            'nama'  => $namaOrangTua,
            'error' => $e->getMessage()
        ]);
    }
}

    /**
     * Sinkronisasi status Registration ke MuridTrial
     */
    protected function syncRegistrationToTrial(Registration $registration): void
    {
        if (!$registration->student || !$registration->student->muridTrial) {
            return;
        }

        $trial = $registration->student->muridTrial;
        $newTrialStatus = match ($registration->status) {
            'rejected', 'batal' => 'aktif',           // Kembali ke Trial Aktif
            'accepted'          => 'lanjut_daftar',   // atau 'terdaftar' kalau mau
            default             => $trial->status_trial,
        };

        if ($trial->status_trial !== $newTrialStatus) {
            $trial->update([
                'status_trial'  => $newTrialStatus,
                'tanggal_aktif' => $newTrialStatus === 'aktif' ? now()->format('Y-m-d') : null,
            ]);

            Log::info('🔄 Sinkronisasi Registration → MuridTrial', [
                'registration_status' => $registration->status,
                'trial_status_baru'   => $newTrialStatus,
                'nama'                => $trial->nama,
            ]);
        }
    }
}