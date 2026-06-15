<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\MuridTrial;
use App\Models\Registration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\BukuInduk;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentHistory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Unit;

class StudentController extends Controller
{
           /**
     * Pemetaan header Google Form -> kolom tabel students
     * Support header yang menempel / tidak rapi
     */
    protected array $FORM_HEADER_MAP = [
        'Timestamp' => 'form_timestamp',
        'Email Address' => 'email',
        'Sumber Pendaftaran' => 'sumber_pendaftaran',

        'Nama Lengkap Peserta Anak biMBA' => 'nama',
        'Nama Lengkap Peserta Anak biMB' => 'nama',
        'Nama' => 'nama',

        'Tanggal lahir' => 'tgl_lahir',
        'Tanggal Lahir' => 'tgl_lahir',
        'Alamat Lengkap' => 'alamat',
        'Tempat Lahir' => 'tempat_lahir',
        'Column 7' => 'tempat_lahir',

        'Jenis Kelamin' => 'jenis_kelamin',
        'Agama' => 'agama_murid',

        'Kode Pos' => 'kode_pos',
        'Nomor Rumah' => 'no_rumah',
        'RT.' => 'rt',
        'RW.' => 'rw',
        'Kelurahan' => 'kelurahan',
        'Kecamatan' => 'kecamatan',
        'Kodya / Kab' => 'kodya_kab',
        'Provinsi' => 'provinsi',

        'Nama Ayah' => 'nama_ayah',
        'Agama Ayah' => 'agama_ayah',
        'Pekerjaan Ayah' => 'pekerjaan_ayah',
        'Alamat Kantor Ayah' => 'alamat_kantor_ayah',
        'No Telp. Kantor Ayah' => 'telepon_kantor_ayah',
        'No. HP/WA Ayah' => 'hp_ayah',

        'Nama Ibu' => 'nama_ibu',
        'Agama Ibu' => 'agama_ibu',
        'Pekerjaan Ibu' => 'pekerjaan_ibu',
        'Alamat Kantor Ibu' => 'alamat_kantor_ibu',
        'No. Telepon Kantor Ibu' => 'telepon_kantor_ibu',
        'No. HP/WA Ibu' => 'hp_ibu',

        'Tanggal Daftar' => 'tanggal_masuk',
        'Tanggal Masuk Sekolah' => 'tanggal_masuk',
        'Informasi biMBA-AIUEO didapat dari' => 'informasi_bimba',
        'Hari' => 'hari',
        'Jam' => 'jam',

        'biMBA Unit' => 'bimba_unit',
        'Unit' => 'bimba_unit',

        'Upload KK (Kartu Keluarga)' => 'foto_kk',
        'Upload KK' => 'foto_kk',
        'Kartu Keluarga' => 'foto_kk',

        'Upload Surat Mutasi' => 'foto_mutasi',
        'Surat Mutasi' => 'foto_mutasi',

        'Nama' => 'nama_pemberi_tahu',
        

        // Placeholder
        'Column 39' => null,
        'Column 38' => null,
        'Masukan Column' => null,
    ];

    // -------------------------------------------------------------------------
    // Helper: normalisasi nama unit (dipakai konsisten di banyak tempat)
    // -------------------------------------------------------------------------
    protected function normalizeUnitName(?string $s): ?string
    {
        if (!$s) return null;
        $s = trim(mb_strtolower((string) $s));
        // Hapus kata noise umum agar perbandingan lebih konsisten
        $s = preg_replace('/\b(unit|bi?mba|aiueo|cabang)\b/u', ' ', $s);
        // Hapus tanda baca / karakter non alnum/spasi
        $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s);
        // Collapse spaces
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }

    // -------------------------------------------------------------------------
    // Helper: resolve no_cabang dari bimba_unit
    // - 1) exact normalized match
    // - 2) try code extraction (numbers)
    // - 3) aliases column (if exists)
    // - 4) limited fuzzy (levenshtein threshold)
    // - 5) otherwise null
    // -------------------------------------------------------------------------
    protected function resolveNoCabangFromBimbaUnit(?string $bimbaUnit): ?string
{
    if (empty($bimbaUnit)) return null;

    $raw   = trim($bimbaUnit);
    $lower = strtolower($raw);

    // === ATURAN KHUSUS – DIPAKSA BENAR OTOMATIS SELAMANYA ===
    if (str_contains($lower, 'griya') && str_contains($lower, 'pesona') && str_contains($lower, 'madani')) {
        return '05141'; // GRIYA PESONA MADANI = 05141 SELAMANYA
    }
    if (str_contains($lower, 'sapta taruna iv')) {
    return '01045';
}
    if (str_contains($lower, 'villa bekasi indah 2')) {
        return '00340';
    }
    
    // tambah unit lain di sini kalau perlu...

    // === Coba ambil kode angka langsung dari teks (misal: "05141 Griya Pesona Madani") ===
    if (preg_match('/\b(05[0-9]{3,5})\b/', $raw, $m)) {
        return $m[1];
    }

    // === Coba cari di tabel units (fallback) ===
    $norm = $this->normalizeUnitName($raw);

    $unit = Unit::whereRaw('LOWER(bimba_unit) LIKE ?', ["%{$norm}%"])
                ->orWhereRaw('LOWER(bimba_unit) LIKE ?', ["%{$raw}%"])
                ->first(['no_cabang']);

    if ($unit?->no_cabang) {
        return $unit->no_cabang;
    }

    return null; // jangan asal tebak
}

    // -------------------------------------------------------------------------
    // INDEX
    // -------------------------------------------------------------------------
  public function index(Request $request)
{
    $q           = trim((string) $request->input('q', ''));
    $statusTrial = trim((string) $request->input('status_trial', ''));
    $user        = Auth::user();

    $isAdmin = $user && in_array($user->role ?? '', ['admin', 'superadmin']);
    $unitId  = $isAdmin ? $request->input('unit_id') : null;

    Log::info('DEBUG INDEX MULAI', [
        'user' => $user?->name,
        'role' => $user?->role,
        'user_bimba_unit' => $user?->bimba_unit ?? '-'
    ]);

    // =========================
    // QUERY UTAMA
    // =========================
    $query = Student::query()
        ->with('muridTrial')
        ->latest('id');

    // Search
    if ($q !== '') {
        $query->where(function ($w) use ($q) {
            $w->where('nim', 'like', "%{$q}%")
              ->orWhere('nama', 'like', "%{$q}%");
        });
    }

    // Status Trial
    if ($statusTrial !== '') {
        $key = strtolower($statusTrial);
        if ($key === 'bukan trial') $key = 'tanpa trial';

        $map = [
            'tanpa trial'   => 'tanpa_trial',
            'aktif'         => 'aktif',
            'lanjut_daftar' => 'lanjut_daftar',
            'batal'         => 'batal',
        ];

        if ($key === 'tanpa trial') {
            $query->whereNull('murid_trial_id');
        } else {
            $query->whereNotNull('murid_trial_id')
                  ->whereHas('muridTrial', function ($mt) use ($map, $key) {
                      $mt->where('status_trial', $map[$key] ?? $key);
                  });
        }
    }

    // =========================
// FILTER UNIT
// =========================
if (!$isAdmin) {
    $userUnit     = trim($user->bimba_unit ?? '');
    $userNoCabang = trim($user->no_cabang ?? '');

    $query->where(function ($qry) use ($userUnit, $userNoCabang) {
        if ($userUnit) {
            $qry->where('bimba_unit', 'LIKE', "%{$userUnit}%");
        }
        if ($userNoCabang) {
            $qry->orWhere('no_cabang', $userNoCabang);
        }

        // Semua unit yang diizinkan
        $qry->orWhere('bimba_unit', 'LIKE', '%VILLA BEKASI INDAH 2%')
            ->orWhere('no_cabang', '00340')
            ->orWhere('bimba_unit', 'LIKE', '%GRIYA PESONA MADANI%')
            ->orWhere('no_cabang', '05141')
            ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA IV%')
            ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA 4%')
            ->orWhere('no_cabang', '01045');
    });
}

    $students = $query->paginate(20)->withQueryString();

    Log::info('DEBUG INDEX SELESAI', [
        'total_data_ditemukan' => $students->total(),
        'is_admin' => $isAdmin
    ]);

    // Student Options
    $studentOptionsQuery = Student::query();

    if (!$isAdmin) {
        $studentOptionsQuery->where(function ($qry) {
            $qry->where('no_cabang', '05141')
                ->orWhere('bimba_unit', 'LIKE', '%05141%')
                ->orWhere('bimba_unit', 'LIKE', '%GRIYA PESONA MADANI%');
        });
    } elseif ($unitId) {
        $unit = Unit::find($unitId);
        if ($unit) {
            $studentOptionsQuery->where('no_cabang', $unit->no_cabang);
        }
    }

    $studentOptions = $studentOptionsQuery
        ->select('nim', 'nama')
        ->orderBy('nama')
        ->limit(1000)
        ->get()
        ->map(fn($s) => [
            'value' => $s->nim,
            'label' => "{$s->nim} | {$s->nama}",
        ])
        ->toArray();

    $unitOptions = Unit::orderBy('no_cabang')
        ->get()
        ->map(fn($u) => [
            'value' => $u->id,
            'label' => trim(($u->no_cabang ?? '') . ' - ' . ($u->biMBA_unit ?? ''))
        ]);

    return view('students.index', compact(
        'students', 'q', 'statusTrial', 'studentOptions', 'unitOptions', 'unitId'
    ));
}
    // -------------------------------------------------------------------------
    // CREATE FORM
    // -------------------------------------------------------------------------
    public function create()
    {
        $kelasList = \App\Models\BukuInduk::select('kelas')
            ->whereNotNull('kelas')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        return view('students.create', compact('kelasList'));
    }

    // -------------------------------------------------------------------------
    // STORE (pendaftaran langsung)
    // -------------------------------------------------------------------------
    public function store(Request $request)
{
    $request->merge([
        'source' => $this->normalizeSource($request->input('source')),
    ]);

    $formRules = $this->formValidationRules();

    $data = $request->validate(array_merge([
        'nama'       => ['required', 'string', 'max:255'],
        'kelas'      => ['nullable', 'string', 'max:100'],
        'tgl_lahir'  => ['nullable', 'date'],
        'usia'       => ['nullable', 'integer', 'min:1', 'max:120'],
        'orangtua'   => ['nullable', 'string', 'max:255'],
        'no_telp'    => ['nullable', 'string', 'max:20'],
        'alamat'     => ['nullable', 'string'],
        'guru_wali'  => ['nullable', 'string', 'max:255'],
        'source'     => ['nullable', 'in:trial,direct'],
        'no_cabang'  => ['nullable', 'string', 'max:20'],
        'bimba_unit' => ['nullable', 'string', 'max:100'],
    ], $formRules));

    // --- hitung usia & source seperti punyamu ---
    if (empty($data['usia']) && !empty($data['tgl_lahir'])) {
        $data['usia'] = Carbon::parse($data['tgl_lahir'])->age;
    }
    if (empty($data['source']) && !empty($data['sumber_pendaftaran'])) {
        $data['source'] = $this->normalizeSourceFromForm($data['sumber_pendaftaran']) ?? 'direct';
    }
    if (!array_key_exists('source', $data) || $data['source'] === null) {
        $data['source'] = 'direct';
    }

    $this->castFormLikeColumns($data);

    // 1️⃣ DI SINI: PASTIKAN no_cabang ikut dari bimba_unit (kalau kosong)
    if (!empty($data['bimba_unit']) && empty($data['no_cabang'])) {
        $resolved = $this->resolveNoCabangFromBimbaUnit($data['bimba_unit']);
        if ($resolved) {
            $data['no_cabang'] = $resolved;
        }
    }

    // 2️⃣ BARU MASUK TRANSAKSI & BUAT NIM BERDASARKAN bimba_unit
    $student = DB::transaction(function () use ($data) {
        $nim = $this->generateNimFromBukuInduk($data['bimba_unit'] ?? null);

        // jaga-jaga kalau ada tabrakan NIM
        while (Student::where('nim', $nim)->lockForUpdate()->exists()) {
            $nim = $this->incrementNim($nim);
        }

        $data['nim'] = $nim;

        $student = Student::create($data);

        if ($student->source === 'trial') {
            $this->ensureTrialRelation($student, 'aktif');
        }

        if ($student->source === 'direct') {
            $hasActive = Registration::where('student_id', $student->id)
                ->whereIn('status', ['pending', 'verified', 'accepted'])
                ->exists();

            if (!$hasActive) {
                $payload = [
                    'student_id'     => $student->id,
                    'status'         => 'pending',
                    'tanggal_daftar' => now(),
                    'source'         => $student->source,
                    'created_by'     => Auth::id(),
                ];
                if (Schema::hasColumn('registrations', 'tahun_ajaran')) {
                    $payload['tahun_ajaran'] = method_exists(Registration::class, 'currentAcademicYear')
                        ? Registration::currentAcademicYear() : null;
                }
                Registration::create(array_filter($payload, fn($v) => $v !== null));
            }
        }

        return $student;
    });

    return redirect()->route('students.index')
        ->with('success', "Student baru ({$student->nama}) berhasil didaftarkan. NIM: {$student->nim}.");
}


    // -------------------------------------------------------------------------
    // Promote dari MuridTrial (simpan bimba_unit & no_cabang)
    // -------------------------------------------------------------------------
    public function promoteFromTrial(MuridTrial $murid)
    {
        abort_unless($murid->exists, 404);

        if ($murid->student) {
            return redirect()
                ->route('students.edit', $murid->student->id)
                ->with('info', 'Murid ini sudah menjadi student.');
        }

        // ambil nama unit dari murid trial
        $possibleUnit = $murid->bimba_unit ?? $murid->unit ?? null;
        $possibleUnit = is_string($possibleUnit) ? trim($possibleUnit) : null;

        // resolve no_cabang (akan memakai normalizeUnitName)
        $resolvedNoCabang = null;
        try {
            $resolvedNoCabang = $this->resolveNoCabangFromBimbaUnit($possibleUnit);
        } catch (\Throwable $e) {
            Log::warning('promoteFromTrial: resolveNoCabangFromBimbaUnit error', ['id' => $murid->id, 'err' => $e->getMessage()]);
        }

        $student = DB::transaction(function () use ($murid, $possibleUnit, $resolvedNoCabang) {
            $nim = $this->generateNimFromBukuInduk($possibleUnit);
            while (Student::where('nim', $nim)->lockForUpdate()->exists()) {
                $nim = $this->incrementNim($nim);
            }

            return Student::create([
                'nim' => $nim,
                'nama' => $murid->nama,
                'kelas' => $murid->kelas,
                'tgl_lahir' => $murid->tgl_lahir,
                'usia' => $murid->usia,
                'orangtua' => $murid->orangtua,
                'no_telp' => $murid->no_telp,
                'alamat' => $murid->alamat,
                'guru_wali' => $murid->guru_trial,
                'source' => 'trial',
                'murid_trial_id' => $murid->id,
                'promoted_at' => now(),
                // SIMPAN UNIT & CABANG
                'bimba_unit' => $possibleUnit,
                'no_cabang'  => $resolvedNoCabang,
            ]);
        });

        return redirect()
            ->route('students.edit', $student->id)
            ->with('success', 'Berhasil promote ke Students. Buku Induk akan dibuat saat registrasi di-accept.');
    }

   public function acceptRegistration(Student $student)
{
    $bi = BukuInduk::where('nim', $student->nim)->first();

    if ($bi) {
        $this->mergeStudentToBukuInduk($student, $bi);
    } else {
        $biData = $this->buildBukuIndukPayload($student, true);
        $biData['tanggal_masuk'] = $student->tanggal_masuk ?? Carbon::now()->toDateString();

        $bi = DB::transaction(function () use ($student, $biData) {
            return BukuInduk::create($biData);
        });
    }

    // FORCE UPDATE PALING KUAT
    $this->forceUpdateTrialToLanjutDaftar($student);

    StudentHistory::create([
        'student_id' => $student->id,
        'user_id' => Auth::id(),
        'diff' => ['registration_status' => ['old' => 'Pending/Promoted', 'new' => 'Accepted (Buku Induk Created)']],
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);

    return redirect()
        ->route('students.edit', $student->id)
        ->with('success', 'Pendaftaran berhasil diterima. Status trial diubah menjadi Lanjut Daftar.');
}

/**
 * FORCE UPDATE PALING AGRESIF
 */
protected function forceUpdateTrialToLanjutDaftar(Student $student): void
{
    DB::transaction(function () use ($student) {
        // 1. Via relasi
        if ($student->muridTrial) {
            $student->muridTrial->update([
                'status_trial'  => 'lanjut_daftar',
                'tanggal_aktif' => null,
            ]);
            Log::info('[FORCE] MuridTrial updated to lanjut_daftar via relation', ['nama' => $student->nama]);
            return;
        }

        // 2. Cari via nama + student_id
        $trial = MuridTrial::where('nama', 'LIKE', "%{$student->nama}%")
            ->orWhere('id', function ($q) use ($student) {
                $q->select('murid_trial_id')->from('students')->where('id', $student->id);
            })
            ->first();

        if ($trial) {
            $trial->update([
                'status_trial'  => 'lanjut_daftar',
                'tanggal_aktif' => null,
            ]);
            Log::info('[FORCE] MuridTrial updated to lanjut_daftar via search', ['nama' => $student->nama]);
        }
    });
}

    // -------------------------------------------------------------------------
    // EDIT FORM
    // -------------------------------------------------------------------------
    public function edit(\App\Models\Student $student)
    {
        $bi = \App\Models\BukuInduk::where('nim', $student->nim)->first();

        $kelasList = \App\Models\BukuInduk::select('kelas')
            ->whereNotNull('kelas')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        $student->load('muridTrial');

        $trialStatuses = [
            'aktif' => 'Trial Aktif',
            'lanjut_daftar' => 'Lanjut Daftar',
            'batal' => 'Batal',
            'tanpa_trial' => 'Tanpa Trial',
            'mutasi' => 'Mutasi',
        ];

        $sumberInformasi = [
        'HUMAS'          => 'HUMAS',
        'Iklan'          => 'Iklan',
        'Sosial Media'   => 'Sosial Media',
        'Referral'       => 'Referral / Rekomendasi',
        'Sekolah'        => 'Kerjasama Sekolah',
        'Event'          => 'Event / Open House',
        'Lainnya'        => 'Lainnya',
    ];

        return view('students.edit', compact('student', 'bi', 'kelasList', 'trialStatuses', 'sumberInformasi'));
    }

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------
    public function update(Request $request, Student $student)
{
    $normalized = $this->normalizeSource($request->input('source'));
    $formRules = $this->formValidationRules();

    $data = $request->validate(array_merge([
        'nama' => ['required', 'string', 'max:255'],
        'kelas' => ['nullable', 'string', 'max:100'],
        'tgl_lahir' => ['nullable', 'date'],
        'usia' => ['nullable', 'integer', 'between:1,120'],
        'orangtua' => ['nullable', 'string', 'max:255'],
        'no_telp' => ['nullable', 'string', 'max:20'],
        'alamat' => ['nullable', 'string'],
        'guru_wali' => ['nullable', 'string', 'max:255'],
        'source' => ['nullable', 'in:trial,direct'],
        'status_trial' => ['nullable', 'string'],
        'no_cabang' => ['nullable', 'string', 'max:20'],
        'bimba_unit' => ['nullable', 'string', 'max:100'],

        // === FIELD BARU ===
        'informasi_bimba'       => ['nullable', 'string', 'max:100'],
        'informasi_humas_nama'  => ['nullable', 'string', 'max:255'],
        'informasi_lainnya'     => ['nullable', 'string', 'max:1000'],
    ], $formRules));

    // === LOGIKA KHUSUS INFORMASI_BIMBA ===
    if ($request->informasi_bimba === 'Lainnya' && $request->filled('informasi_lainnya')) {
        $data['informasi_bimba'] = $request->informasi_lainnya;
    }

    if ($request->has('source')) {
        $data['source'] = $normalized ?? ($student->source ?? 'direct');
    } else {
        unset($data['source']);
    }

    if (empty($data['source']) && !empty($data['sumber_pendaftaran'])) {
        $data['source'] = $this->normalizeSourceFromForm($data['sumber_pendaftaran']) ?? ($student->source ?? 'direct');
    }

    $this->castFormLikeColumns($data);

    // Resolve no_cabang
    if (!empty($data['bimba_unit'])) {
        $shouldResolve = empty($data['no_cabang']) || (isset($data['bimba_unit']) && $data['bimba_unit'] !== $student->bimba_unit);
        if ($shouldResolve) {
            $resolved = $this->resolveNoCabangFromBimbaUnit($data['bimba_unit']);
            if ($resolved) {
                $data['no_cabang'] = $resolved;
            }
        }
    }

    DB::transaction(function () use ($request, $student, $data) {
        $fields = [
            'nama','kelas','tgl_lahir','usia','orangtua','no_telp','telp_hp','hp_ayah','hp_ibu',
            'alamat','guru_wali','source','no_cabang','bimba_unit',
            // Tambahkan field baru untuk history
            'informasi_bimba', 'informasi_humas_nama'
        ];

        $before = $student->only($fields);

        $student->update($data);

        if (($student->source ?? null) === 'trial') {
            $this->ensureTrialRelation($student, 'aktif');
        }

        $bi = BukuInduk::where('nim', $student->nim)->first();
        if ($bi) {
            $this->mergeStudentToBukuInduk($student, $bi);
        }

        $after = $student->only($fields);
        $diff = [];
        foreach ($fields as $k) {
            $old = $before[$k] ?? null;
            $nw = $after[$k] ?? null;
            if ($k === 'tgl_lahir') {
                $old = $old ? substr((string) $old, 0, 10) : $old;
                $nw = $nw ? substr((string) $nw, 0, 10) : $nw;
            }
            if ((string) $old !== (string) $nw) {
                $diff[$k] = ['old' => $old, 'new' => $nw];
            }
        }

        if ($diff) {
            StudentHistory::create([
                'student_id' => $student->id,
                'user_id' => Auth::id(),
                'diff' => $diff,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    });

    return redirect()->route('students.index')
        ->with('success', 'Data student berhasil diperbarui.');
}

    // -------------------------------------------------------------------------
    // Helper NIM dari Buku Induk
    // -------------------------------------------------------------------------
   protected function generateNimFromBukuInduk(?string $bimbaUnit = null): string
{
    return DB::transaction(function () use ($bimbaUnit) {
        // 1. Ambil kode unit / no_cabang 5 digit
        $kodeUnit = $this->resolveKodeUnit($bimbaUnit); // contoh: "05141"

        // 2. Cari NIM terakhir di buku_induk dengan prefix kodeUnit
        $lastNim = BukuInduk::whereRaw('LEFT(nim, 5) = ?', [$kodeUnit])
            ->whereRaw('LENGTH(nim) = 9')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(nim, 6, 4) AS UNSIGNED) DESC')
            ->value('nim');

        if (!$lastNim) {
            $lastNim = Student::whereRaw('LEFT(nim, 5) = ?', [$kodeUnit])
                ->whereRaw('LENGTH(nim) = 9')
                ->orderByRaw('CAST(SUBSTRING(nim, 6, 4) AS UNSIGNED) DESC')
                ->value('nim');
        }

        // 3. Hitung nomor urut berikutnya (4 digit terakhir)
        if ($lastNim) {
            $urutanSekarang   = (int) substr($lastNim, 5);
            $urutanBerikutnya = $urutanSekarang + 1;
        } else {
            $urutanBerikutnya = 1;
        }

        // 4. Bentuk NIM: [no_cabang 5 digit][urut 4 digit]
        return $kodeUnit . str_pad($urutanBerikutnya, 4, '0', STR_PAD_LEFT);
        // contoh: 05141 + 0579 = 051410579
    });
}



protected function resolveKodeUnit(?string $bimbaUnit): string
{
    if (empty($bimbaUnit)) {
        return '01045'; // default Sapta Taruna IV kalau unit tidak diisi
    }

    $lower = mb_strtolower(trim($bimbaUnit));

    // ATURAN KHUSUS — DIPAKSA SELAMANYA BENAR
    if (str_contains($lower, 'griya') && str_contains($lower, 'pesona') && str_contains($lower, 'madani')) {
        return '05141';
    }
    if (str_contains($lower, 'sapta taruna iv') || str_contains($lower, 'sapta taruna 4')) {
        return '01045';
    }
    if (str_contains($lower, 'villa bekasi indah 2') || str_contains($lower, 'vbi 2')) {
        return '00340';
    }

    // Ambil angka 5 digit langsung dari teks (misal: "05141 Griya Pesona Madani")
    if (preg_match('/\b05[0-9]{3}\b/', $bimbaUnit, $m)) {
        return $m[0];
    }

    // Fallback: cek dari tabel units
    $unit = \App\Models\Unit::whereRaw('LOWER(bimba_unit) LIKE ?', ["%{$lower}%"])
        ->orWhereRaw('LOWER(bimba_unit) LIKE ?', ["%{$bimbaUnit}%"])
        ->first(['no_cabang']);

    // hasil akhir: selalu 5 digit kode cabang
    return $unit?->no_cabang ?? '01045';
}

    protected function incrementNim(string $nim): string
{
    $prefix = substr($nim, 0, 5);      // no_cabang
    $number = (int) substr($nim, 5);   // 4 digit urut

    $number++;

    return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
}


    protected function normalizeSource($value): ?string
    {
        if ($value === '' || $value === null)
            return null;

        $map = [
            'langsung' => 'direct',
            'promote' => 'trial',
            'trial' => 'trial',
            'direct' => 'direct',
        ];

        return $map[$value] ?? $value;
    }

    protected function normalizeSourceFromForm(?string $sumber): ?string
    {
        if ($sumber === null) return null;
        $x = strtolower(trim($sumber));
        $map = [
            'pendaftaran baru' => 'direct',
            'pendaftaran mutasi' => 'direct',
            'pendaftaran langsung' => 'direct',
            'daftar langsung' => 'direct',
            'langsung' => 'direct',
            'direct' => 'direct',
            'murid pendaftaran trial' => 'trial',
            'trial' => 'trial',
            'dari trial' => 'trial',
            'promote' => 'trial',
            'bukan trial' => 'direct',
            'tanpa trial' => 'direct',
        ];
        if (!isset($map[$x]) && str_contains($x, 'trial')) return 'trial';
        return $map[$x] ?? 'direct';
    }

    protected function formValidationRules(): array
    {
        return [
            'form_timestamp' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'sumber_pendaftaran' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'string', 'max:20'],
            'agama_murid' => ['nullable', 'string', 'max:50'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'no_rumah' => ['nullable', 'string', 'max:50'],
            'rt' => ['nullable', 'string', 'max:10'],
            'rw' => ['nullable', 'string', 'max:10'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kodya_kab' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'agama_ayah' => ['nullable', 'string', 'max:50'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:255'],
            'alamat_kantor_ayah' => ['nullable', 'string'],
            'telepon_kantor_ayah' => ['nullable', 'string', 'max:30'],
            'hp_ayah' => ['nullable', 'string', 'max:30'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'agama_ibu' => ['nullable', 'string', 'max:50'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'alamat_kantor_ibu' => ['nullable', 'string'],
            'telepon_kantor_ibu' => ['nullable', 'string', 'max:30'],
            'hp_ibu' => ['nullable', 'string', 'max:30'],
            'tanggal_masuk' => ['nullable', 'string'],
            'biaya_pendaftaran' => ['nullable', 'string'],
            'spp_bulanan' => ['nullable', 'string'],
            'informasi_bimba' => ['nullable', 'string', 'max:255'],
            'hari' => ['nullable', 'string', 'max:50'],
            'jam' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function castFormLikeColumns(array &$data): void
    {
        foreach (['tgl_lahir', 'tanggal_masuk'] as $d) {
            if (!empty($data[$d])) {
                $val = (string) $data[$d];
                $val = str_replace('/', '-', $val);
                try {
                    $data[$d] = Carbon::parse($val)->format('Y-m-d');
                } catch (\Throwable $e) {
                }
            }
        }

        if (!empty($data['form_timestamp'])) {
            $ts = (string) $data['form_timestamp'];
            $ts = str_replace('/', '-', $ts);
            $ts = preg_replace('/(\s\d{1,2})\.(\d{2})(?:\.(\d{2}))?$/', '$1:$2:$3', $ts);
            try {
                $data['form_timestamp'] = Carbon::parse($ts);
            } catch (\Throwable $e) {
                unset($data['form_timestamp']);
            }
        }

        foreach (['biaya_pendaftaran', 'spp_bulanan'] as $m) {
            if (isset($data[$m]) && $data[$m] !== null && $data[$m] !== '') {
                $raw = preg_replace('/[^0-9,\.]/', '', (string) $data[$m]);
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
                $data[$m] = is_numeric($raw) ? (float) $raw : null;
            }
        }

        if (!empty($data['hari'])) {
            $h = strtolower(trim((string) $data['hari']));
            $map = [
                'seini' => 'Senin','senin' => 'Senin','selasa' => 'Selasa','rabu' => 'Rabu',
                'kamis' => 'Kamis','jumat' => 'Jumat',"jum'at" => 'Jumat','sabtu' => 'Sabtu','minggu' => 'Minggu',
            ];
            if (str_contains($h, ',')) $h = trim(strtok($h, ','));
            $data['hari'] = $map[$h] ?? ucwords($h);
        }

        if (!empty($data['jam'])) {
            $jam = trim((string) $data['jam']);
            if (str_contains($jam, ',')) {
                $parts = explode(',', $jam);
                $jam = trim($parts[1] ?? $jam);
            }
            $jam = preg_replace('/^jam\s*/i', '', $jam);
            $jam = str_replace('.', ':', $jam);
            $data['jam'] = $jam;
        }

        if (empty($data['no_telp'])) {
            $data['no_telp'] = $data['hp_ayah'] ?? $data['hp_ibu'] ?? null;
        }

        if (empty($data['orangtua'])) {
            $ayah = isset($data['nama_ayah']) ? trim((string) $data['nama_ayah']) : '';
            $ibu = isset($data['nama_ibu']) ? trim((string) $data['nama_ibu']) : '';
            $join = trim(implode(' & ', array_filter([$ayah ?: null, $ibu ?: null])));
            if ($join !== '') $data['orangtua'] = $join;
        }
    }

    public function destroy(Student $student)
    {
        DB::transaction(function () use ($student) {
            BukuInduk::where('nim', $student->nim)->delete();
            $student->delete();
        });

        return redirect()
            ->route('students.index')
            ->with('success', "Data student {$student->nama} (NIM: {$student->nim}) telah dihapus.");
    }

    /**
 * Perbaiki semua no_cabang yang kosong atau salah berdasarkan bimba_unit
 */
protected function fixAllNoCabang(): void
{
    $studentsToFix = Student::whereNotNull('bimba_unit')
        ->where('bimba_unit', '!=', '')
        ->select('id', 'bimba_unit', 'no_cabang')
        ->get();

    $filled  = 0;
    $updated = 0;

    foreach ($studentsToFix as $student) {
        $correctCode = $this->resolveNoCabangFromBimbaUnit($student->bimba_unit);

        if ($correctCode && $student->no_cabang !== $correctCode) {
            $oldCode = $student->no_cabang;

            $student->no_cabang = $correctCode;
            $student->saveQuietly();

            if (is_null($oldCode)) {
                $filled++;
            } else {
                $updated++;
            }

            Log::info('no_cabang diperbaiki otomatis', [
                'student_id'     => $student->id,
                'bimba_unit'     => $student->bimba_unit,
                'old_no_cabang'  => $oldCode,
                'new_no_cabang'  => $correctCode,
            ]);
        }
    }

    Log::info("fixAllNoCabang selesai - Baru diisi: {$filled}, Diperbaiki: {$updated}");
}

    // -------------------------------------------------------------------------
    // importFromSheet: panggil command dan lalu buat registrasi otomatis
    // serta pasca-import isi no_cabang secara deterministik
    // -------------------------------------------------------------------------
       // -------------------------------------------------------------------------
// importFromSheet: panggil command dan lalu buat registrasi otomatis
// serta pasca-import isi no_cabang secara deterministik
// -------------------------------------------------------------------------
public function importFromSheet(Request $request)
{
    $sheet = $request->input('sheet', 'Registrasi');
    $user  = Auth::user();

    if (!$user) {
        return back()->with('error', 'Anda harus login terlebih dahulu.');
    }

    $isAdmin = in_array($user->role ?? '', ['admin', 'superadmin']);

    Log::info("Mulai import sheet via web", [
        'sheet'       => $sheet,
        'user'        => $user->name ?? 'unknown',
        'role'        => $user->role ?? 'unknown',
        'is_admin'    => $isAdmin,
        'bimba_unit'  => $user->bimba_unit ?? null,
    ]);

    $unitFilter = null;

    if (!$isAdmin) {
        $unitFilter = trim((string) $user->bimba_unit);

        if (empty($unitFilter)) {
            Log::warning("User biasa tidak memiliki bimba_unit", ['user_id' => $user->id]);
            return back()->with('error', 'Akun Anda tidak memiliki Unit biMBA. Hubungi Admin.');
        }
    }

    try {
        $exitCode = Artisan::call('forms:import-students', [
            'sheet'  => $sheet,
            '--unit' => $unitFilter,   // null = admin
        ]);

        if ($exitCode !== 0) {
            $output = Artisan::output();
            Log::error("Artisan call gagal", ['output' => $output]);
            return back()->with('error', 'Import gagal dijalankan.');
        }

        // Proses tambahan
        $this->setDefaultDaftarBaruStatus();
        $this->createPendingRegistrations();
        $this->fixAllNoCabang();

        $message = "✅ Import sheet '{$sheet}' selesai!";
        if (!$isAdmin && $unitFilter) {
            $message .= " (Hanya unit: {$unitFilter})";
        }

        return back()->with('success', $message);

    } catch (\Exception $e) {
        Log::error("Exception saat import", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    /**
 * Set default status 'daftar_baru' untuk MuridTrial yang baru diimport
 * dan belum punya status
 */
protected function setDefaultDaftarBaruStatus(): void
{
    $updated = MuridTrial::whereNull('status_trial')
        ->orWhere('status_trial', '')
        ->orWhere('status_trial', 'baru')           // ubah yang lama 'baru' jadi 'daftar_baru'
        ->update([
            'status_trial'       => 'daftar_baru',
            'tanggal_trial_baru' => DB::raw('COALESCE(tanggal_trial_baru, waktu_submit, created_at)'),
        ]);

    if ($updated > 0) {
        Log::info("setDefaultDaftarBaruStatus: {$updated} record MuridTrial diubah menjadi 'daftar_baru'");
    }
}

/**
 * Buat Registration otomatis untuk murid Direct (non-trial)
 * FIXED: Respect unique constraint student_id + tahun_ajaran
 */
protected function createPendingRegistrations(): void
{
    $currentYear = Registration::currentAcademicYear() ?? date('Y') . '/' . (date('Y') + 1);

    $directStudents = Student::where('source', 'direct')
        ->whereDoesntHave('registrations', function ($q) use ($currentYear) {
            $q->where('tahun_ajaran', $currentYear)
              ->whereIn('status', ['pending', 'verified', 'accepted']);
        })
        ->get();

    $created = 0;

    foreach ($directStudents as $student) {
        // Double check inside loop (safer)
        $hasActiveThisYear = Registration::where('student_id', $student->id)
            ->where('tahun_ajaran', $currentYear)
            ->whereIn('status', ['pending', 'verified', 'accepted'])
            ->exists();

        if (!$hasActiveThisYear) {
            $payload = [
                'student_id'     => $student->id,
                'status'         => 'pending',
                'tanggal_daftar' => now(),
                'source'         => $student->source,
                'created_by'     => Auth::id() ?? null,
                'tahun_ajaran'   => $currentYear,
            ];

            try {
                Registration::create($payload);
                $created++;
            } catch (\Exception $e) {
                // Ignore duplicate errors (in case of race condition)
                if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                    Log::error("Failed creating pending registration", [
                        'student_id' => $student->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    if ($created > 0) {
        Log::info("createPendingRegistrations: {$created} registrasi baru dibuat untuk tahun {$currentYear}.");
    }
}


protected function preventDuplicateStudents(): void
{
    // Ambil semua student yang baru diimport (misal: yang belum punya NIM atau timestamp import baru)
    $newImports = Student::whereNull('nim')
        ->orWhere('created_at', '>=', now()->subMinutes(10))
        ->get();

    foreach ($newImports as $student) {
        // Cek duplikat berdasarkan Nama + Tgl Lahir
        $duplicate = Student::where('id', '!=', $student->id)
            ->where('nama', 'LIKE', "%{$student->nama}%")
            ->where('tgl_lahir', $student->tgl_lahir)
            ->first();

        if ($duplicate) {
            Log::warning("Duplikat ditemukan dan dihapus", [
                'new_id' => $student->id,
                'existing_id' => $duplicate->id,
                'nama' => $student->nama
            ]);

            $student->delete(); // atau merge data-nya
            continue;
        }

        // Kalau belum punya NIM, generate
        if (empty($student->nim) && !empty($student->bimba_unit)) {
            $nim = $this->generateNimFromBukuInduk($student->bimba_unit);
            while (Student::where('nim', $nim)->exists()) {
                $nim = $this->incrementNim($nim);
            }
            $student->nim = $nim;
            $student->save();
        }
    }
}

    public function historyJson(Student $student)
    {
        $histories = $student->histories()->with('user')->latest()->take(50)->get();

        $data = $histories->map(function ($h) {
            return [
                'date' => $h->created_at->format('Y-m-d H:i'),
                'user' => optional($h->user)->name ?? 'System',
                'ip' => $h->ip,
                'diff' => $h->diff,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function ensureTrialRelation(Student $student, string $status = 'baru'): void
{
    if ($student->source !== 'trial') {
        return;
    }

    // === LOGIKA BARU: Jangan langsung buat MuridTrial kalau status masih 'baru' ===
    if ($status === 'baru' || $student->trial_status === 'baru') {
        // Hanya update timestamp mulai trial
        if (empty($student->trial_started_at)) {
            $student->trial_started_at = now();
            $student->trial_status = 'baru';
            $student->saveQuietly();
        }
        return; // JANGAN buat MuridTrial dulu
    }

    // === Kalau sudah 'aktif' baru buat MuridTrial ===
    if ($student->murid_trial_id) {
        $trial = $student->muridTrial;
        $updates = [];

        if (empty($trial->bimba_unit) && !empty($student->bimba_unit)) {
            $updates['bimba_unit'] = $student->bimba_unit;
        }
        if (empty($trial->no_cabang) && !empty($student->no_cabang)) {
            $updates['no_cabang'] = $student->no_cabang;
        }
        if (!empty($updates)) {
            $trial->update($updates);
        }
        return;
    }

    try {
        $trial = MuridTrial::create([
            'nama'               => $student->nama,
            'tgl_mulai'          => $student->tanggal_masuk ?? now()->format('Y-m-d'),
            'status_trial'       => 'aktif',           // langsung aktif
            'kelas'              => $student->kelas ?? 'Reguler',
            'tgl_lahir'          => $student->tgl_lahir,
            'usia'               => $student->usia,
            'orangtua'           => $student->orangtua,
            'no_telp'            => $student->no_telp,
            'alamat'             => $student->alamat,
            'guru_trial'         => $student->guru_wali,
            'bimba_unit'         => $student->bimba_unit,
            'no_cabang'          => $student->no_cabang,
            'tanggal_aktif'     => now()->format('Y-m-d'),
            'tanggal_trial_baru' => $student->trial_started_at
                ? Carbon::parse($student->trial_started_at)->format('Y-m-d')
                : now()->format('Y-m-d'),
        ]);

        $student->murid_trial_id = $trial->id;
        $student->trial_status   = 'aktif';
        $student->saveQuietly();

        $this->createHumasSafely($student);

        Log::info('MuridTrial berhasil dibuat otomatis', [
            'student_id' => $student->id,
            'nama'       => $student->nama,
            'status'     => 'aktif'
        ]);

    } catch (\Throwable $e) {
        Log::error('Gagal create MuridTrial', [
            'nama'  => $student->nama,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Buat Humas dengan NIH unik & otomatis increment
 * Jika 05141020001 sudah ada → jadi 05141020002, dst.
 */
protected function createHumasSafely(Student $student): void
{
    if (empty($student->informasi_humas_nama)) {
        return;
    }

    try {
        $noCabang = trim($student->no_cabang ?? '05141');

        DB::transaction(function () use ($student, $noCabang) {

            // Cek apakah humas dengan nama yang sama sudah ada
            $existing = DB::table('humas')
                ->where('nama', 'LIKE', "%{$student->informasi_humas_nama}%")
                ->where('no_cabang', $noCabang)
                ->first();

            if ($existing) {
                Log::info("Humas sudah ada, dilewati", [
                    'nih'  => $existing->nih,
                    'nama' => $existing->nama
                ]);
                return;
            }

            // Ambil NIH tertinggi di cabang ini
            $lastNih = DB::table('humas')
                        ->where('no_cabang', $noCabang)
                        ->whereRaw('LENGTH(nih) = 9')
                        ->lockForUpdate()
                        ->orderByRaw('CAST(SUBSTRING(nih, 6, 4) AS UNSIGNED) DESC')
                        ->value('nih');

            $lastSeq = 20000; // mulai dari 20000 jika belum ada

            if ($lastNih) {
                $lastSeq = (int) substr($lastNih, 5); // ambil 4 digit setelah no_cabang
            }

            $newSeq = $lastSeq + 1;
            $nih = $noCabang . str_pad($newSeq, 4, '0', STR_PAD_LEFT);

            // Anti-collision (jika ada yang cepat insert)
            while (DB::table('humas')->where('nih', $nih)->exists()) {
                $newSeq++;
                $nih = $noCabang . str_pad($newSeq, 4, '0', STR_PAD_LEFT);
            }

            // Insert Humas
            DB::table('humas')->insert([
                'tgl_reg'     => now()->format('Y-m-d'),
                'nih'         => $nih,
                'nama'        => $student->informasi_humas_nama,
                'no_telp'     => $student->hp_ibu ?? $student->hp_ayah ?? $student->no_telp,
                'status'      => 'baru',
                'pekerjaan'   => null,
                'alamat'      => $student->alamat,
                'bimba_unit'  => $student->bimba_unit,
                'no_cabang'   => $noCabang,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            Log::info("✅ Humas berhasil dibuat dengan NIH baru", [
                'nih'  => $nih,
                'nama' => $student->informasi_humas_nama
            ]);

        });

    } catch (\Throwable $e) {
        Log::warning("Gagal create humas", [
            'nama'  => $student->nama ?? 'unknown',
            'error' => $e->getMessage()
        ]);
    }
}

    protected function buildBukuIndukPayload(Student $student, bool $forCreate = false): array
    {
        $payload = [
            'nim' => $student->nim,
            'nama' => $student->nama ?? null,
            'tmpt_lahir' => $student->tempat_lahir ?? null,
            'tgl_lahir' => $student->tgl_lahir ?? null,
            'alamat_murid' => $student->alamat ?? null,
            'alamat' => $student->alamat ?? null,
            'usia' => $student->usia ?? null,
            'kelas' => $student->kelas ?? null,
            'orangtua' => $student->orangtua ?? null,
            'nama_ayah' => $student->nama_ayah ?? null,
            'nama_ibu' => $student->nama_ibu ?? null,
            'no_telp_hp' => $student->no_telp ?? $student->telp_hp ?? $student->hp_ayah ?? $student->hp_ibu ?? null,
            'no_telp' => $student->no_telp ?? $student->telp_hp ?? null,
            'petugas_trial' => $student->petugas_trial,
            'kode_jadwal' => $student->kode_jadwal ?? null,
            'hari_jam' => $student->hari ?? null,
            'no_cabang' => $student->no_cabang,
            'bimba_unit' => $student->bimba_unit,
            'note' => null,
        ];

        if ($forCreate) {
            $payload['tanggal_masuk'] = $student->tanggal_masuk ?? null;
            $payload['periode'] = $student->periode ?? null;
            $payload['level'] = $student->level ?? null;
        }

        return $payload;
    }

    protected function mergeStudentToBukuInduk(Student $student, BukuInduk $bi): void
{
    // Daftar kolom yang TIDAK BOLEH di-overwrite (kecuali manual)
    $protectedFields = [
        'kelas', 'tahap', 'gol', 'kd', 'spp', 'kode_jadwal',
        'lama_bljr', 'tgl_keluar', 'kategori_keluar', 'alasan', 'no_pembayaran_murid'
        // 'petugas_trial' diHAPUS dari sini → biar boleh di-update otomatis!
    ];

    // Kolom yang SELALU di-overwrite kalau ada nilai baru
    $alwaysOverwrite = [
        'nama', 'alamat_murid', 'alamat', 'orangtua',
        'no_telp_hp', 'no_telp', 'nama_ayah', 'nama_ibu',
        'no_cabang', 'bimba_unit',
        'petugas_trial', // <<< INI YANG BARU DITAMBAH! SELALU UPDATE
    ];

    $biPayload = $this->buildBukuIndukPayload($student, false);
    $updates   = [];

    foreach ($biPayload as $col => $val) {
        if (!Schema::hasColumn('buku_induk', $col)) {
            continue;
        }

        // Jangan sentuh kolom yang dilindungi
        if (in_array($col, $protectedFields, true)) {
            continue;
        }

        // SELALU overwrite kolom penting (termasuk petugas_trial)
        if (in_array($col, $alwaysOverwrite, true)) {
            if ($val !== null && $val !== '') {
                $updates[$col] = $val;
            }
            continue;
        }

        // Untuk kolom lain: isi hanya kalau masih kosong
        $current = $bi->$col ?? null;
        if (($current === null || $current === '') && $val !== null && $val !== '') {
            $updates[$col] = $val;
        }
    }

    if (!empty($updates)) {
        BukuInduk::where('nim', $student->nim)->update($updates);
        Log::info('mergeStudentToBukuInduk applied', [
            'nim'      => $student->nim,
            'updates'  => array_keys($updates),
            'petugas_trial' => $updates['petugas_trial'] ?? '(tidak berubah)',
        ]);
    }
}

    // -------------------------------------------------------------------------
    // MUTASI MASUK → LANGSUNG MASUK BUKU INDUK (Versi FINAL – NO TYPO!)
    // -------------------------------------------------------------------------
    public function mutasiMasuk(Request $request, Student $student)
    {
        $data = $request->validate([
            'tanggal_mutasi' => 'required|date',
            'asal_unit'      => 'nullable|string|max:255',
            'asal_kode'      => 'nullable|string|max:50',
            'tahap'          => 'required|string|max:50',
            'gol'            => 'required|string|max:50',
            'kd'             => 'required|in:a,b,c,d,e,f,A,B,C,D,E,F',
            'spp'            => 'nullable|numeric|min:0',
            'status_pindah'  => 'nullable|string|max:100',
            'keterangan'     => 'nullable|string|max:1000',
        ]);

        // Auto hitung SPP kalau kosong (dari tabel harga_sapta_taruna)
        if (empty($data['spp']) && class_exists(\App\Models\HargaSaptaTaruna::class)) {
            $harga = \App\Models\HargaSaptaTaruna::where('kode', $data['gol'])->first();
            if ($harga) {
                $col = strtolower($data['kd']);
                $data['spp'] = (int) ($harga->{$col} ?? 0);
            }
        }

        return DB::transaction(function () use ($student, $data) {
            // 1. Update data student (unit & tanggal masuk)
            $student->update([
                'tanggal_masuk' => $data['tanggal_mutasi'],
                'bimba_unit'    => $data['asal_unit'] ?? $student->bimba_unit,
                'no_cabang'     => $this->resolveNoCabangFromBimbaUnit($data['asal_unit'] ?? $student->bimba_unit),
            ]);

            // 2. BUAT / UPDATE BUKU INDUK → INI INTINYA!
            BukuInduk::updateOrCreate(
                ['nim' => $student->nim],
                [
                    'nama'           => $student->nama,
                    'kelas'          => $student->kelas ?? '-',
                    'status'         => 'Baru', // atau 'Mutasi Masuk' kalau mau beda
                    'tanggal_masuk'  => $data['tanggal_mutasi'],
                    'tanggal_pindah' => $data['tanggal_mutasi'],
                    'status_pindah'  => $data['status_pindah'] ?? 'Pindah Masuk',
                    'tahap'          => $data['tahap'],
                    'gol'            => $data['gol'],
                    'kd'             => strtoupper($data['kd']),
                    'spp'            => (int) ($data['spp'] ?? 0),
                    'tempat_lahir'   => $student->tempat_lahir,
                    'tgl_lahir'      => $student->tgl_lahir,
                    'usia'           => $student->usia ?? Carbon::parse($data['tanggal_mutasi'])->age,
                    'alamat_murid'   => $student->alamat,
                    'alamat'         => $student->alamat,
                    'orangtua'       => $student->orangtua,
                    'no_telp_hp'     => $student->no_telp ?? $student->hp_ayah ?? $student->hp_ibu,
                    'no_telp'        => $student->no_telp,
                    'guru'           => $student->guru_wali,
                    'no_cabang'      => $student->no_cabang,
                    'bimba_unit'     => $student->bimba_unit,
                    'keterangan'     => $data['keterangan'],
                ]
            );

            // 3. Kalau masih nyantol ke trial → ubah status jadi mutasi
            if ($student->muridTrial()->exists()) {
                $student->muridTrial()->update(['status_trial' => 'mutasi']);
            }

            // 4. Log history
            StudentHistory::create([
                'student_id' => $student->id,
                'user_id'    => Auth::id(),
                'diff'       => ['mutasi_masuk' => $data],
            ]);

            return redirect()->route('students.edit', $student->id)
                ->with('success', "Mutasi masuk {$student->nama} berhasil! Data sudah masuk Buku Induk dengan lengkap.");
        });
    }

    /**
 * Aktifkan Kembali Murid yang Statusnya Keluar
 */
public function reactivate(Student $student)
{
    $bi = BukuInduk::where('nim', $student->nim)->first();

    if (!$bi) {
        return back()->with('error', 'Buku Induk tidak ditemukan.');
    }

    return DB::transaction(function () use ($student, $bi) {
        
        // Reset semua field keluar
        $bi->update([
            'status'          => 'Aktif',
            'tgl_keluar'      => null,
            'kategori_keluar' => null,
            'alasan'          => null,
            'tanggal_pindah'  => null,
            'tanggal_masuk'   => $student->tanggal_masuk ?? now()->toDateString(),
            'updated_at'      => now(),
        ]);

        // Update juga di tabel Student (opsional tapi direkomendasikan)
        $student->update([
            'tanggal_masuk' => $bi->tanggal_masuk ?? now()->toDateString(),
        ]);

        // Log history
        StudentHistory::create([
            'student_id' => $student->id,
            'user_id'    => Auth::id(),
            'diff'       => [
                'status' => [
                    'old' => 'Keluar',
                    'new' => 'Aktif Kembali'
                ],
                'kategori_keluar' => ['old' => $bi->getOriginal('kategori_keluar'), 'new' => null],
                'alasan'          => ['old' => $bi->getOriginal('alasan'), 'new' => null],
            ],
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('students.edit', $student->id)
            ->with('success', "Murid {$student->nama} ({$student->nim}) berhasil diaktifkan kembali.");
    });
}
    /**
     * Normalisasi header Google Form yang sering menempel
     */
    protected function normalizeFormHeader(string $header): string
    {
        $header = trim($header);

        // Hapus emoji, karakter aneh, dan multiple spaces
        $header = preg_replace('/[^\p{L}\p{N}\s\/\.\-\(\)\:]/u', ' ', $header);
        $header = preg_replace('/\s+/', ' ', $header);
        $header = trim($header);

        // Beberapa perbaikan khusus untuk header yang sering menempel
        $replacements = [
            'Email AddressSumber'          => 'Email Address Sumber',
            'biMBATanggal'                 => 'biMBA Tanggal',
            'Kab Provinsi'                 => 'Kab Provinsi',
            'Ayah Agama'                   => 'Ayah Agama',
            'IbuAgama'                     => 'Ibu Agama',
            'IbuTanggal'                   => 'Ibu Tanggal',
            'Daftar Informasi'             => 'Daftar Informasi',
            'dari:Hari'                    => 'dari: Hari',
            'biMBA: biMBA Unit'            => 'biMBA Unit',
            'Upload Surat MutasiMOHON'     => 'Upload Surat Mutasi',
        ];

        foreach ($replacements as $bad => $good) {
            if (str_contains($header, $bad)) {
                $header = str_replace($bad, $good, $header);
            }
        }

        return $header;
    }
    public function show(Student $student)
{
    // Load relasi jika diperlukan
    $student->load(['muridTrial']);

    return view('students.show', compact('student'));
}


/**
 * Buat HUMAS untuk semua student baru setelah import
 */
protected function createHumasForImportedStudents(): void
{
    $students = Student::where(function ($q) {
            $q->whereNotNull('orangtua')
              ->orWhereNotNull('nama_ayah')
              ->orWhereNotNull('nama_ibu');
        })
        ->whereDoesntHave('humas') // jika ada relasi humas di model Student
        ->limit(300)
        ->get();

    foreach ($students as $student) {
        $this->createOrUpdateHumaFromStudent($student);
    }

    Log::info("createHumasForImportedStudents selesai");
}


}
