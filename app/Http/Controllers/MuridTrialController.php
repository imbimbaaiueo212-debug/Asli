<?php

namespace App\Http\Controllers;

use App\Models\MuridTrial;
use App\Models\ParentCommitment;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Unit;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MuridTrialController extends Controller
{
    public function index(Request $request)
{
    $this->autoActivateTrial();

    $status      = $request->get('status');
    $plainSearch = trim((string) $request->get('search', ''));
    $user        = Auth::user();

    $isAdmin = $user && in_array($user->role ?? '', ['admin', 'superadmin']);

    // Debug Log
    Log::info('DEBUG MURID TRIAL INDEX', [
        'user'            => $user?->name ?? '-',
        'role'            => $user?->role ?? '-',
        'user_bimba_unit' => $user?->bimba_unit ?? '-',
        'is_admin'        => $isAdmin,
        'search'          => $plainSearch,
        'status'          => $status
    ]);

    // =============================================
    // QUERY UTAMA
    // =============================================
    $query = MuridTrial::with('student')
                       ->latest('waktu_submit');

    // Search
    if ($plainSearch !== '') {
        $query->where(function ($q) use ($plainSearch) {
            $q->where('nama', 'like', "%{$plainSearch}%")
              ->orWhere('no_telp', 'like', "%{$plainSearch}%")
              ->orWhere('bimba_unit', 'like', "%{$plainSearch}%")
              ->orWhereHas('student', fn($s) => 
                  $s->where('nama', 'like', "%{$plainSearch}%")
              );
        });
    }

    // Status Filter
    if ($status !== '' && $status !== null) {
        if ($status === 'kosong') {
            $query->whereNull('status_trial');
        } else {
            $query->where('status_trial', $status);
        }
    }

    // ========================
    // FILTER UNIT (NON-ADMIN)
    // ========================
    if (!$isAdmin) {
        $userUnit     = trim($user->bimba_unit ?? '');
        $userNoCabang = trim($user->no_cabang ?? '');

        $query->where(function ($qry) use ($userUnit, $userNoCabang) {
            // Filter berdasarkan data user login
            if ($userUnit) {
                $qry->where('bimba_unit', 'LIKE', "%{$userUnit}%");
            }
            if ($userNoCabang) {
                $qry->orWhere('no_cabang', $userNoCabang);
            }

            // Unit-unit khusus yang diizinkan
            $qry->orWhere('bimba_unit', 'LIKE', '%VILLA BEKASI INDAH 2%')
                ->orWhere('no_cabang', '00340')
                ->orWhere('bimba_unit', 'LIKE', '%GRIYA PESONA MADANI%')
                ->orWhere('no_cabang', '05141')
                ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA IV%')
                ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA 4%')
                ->orWhere('no_cabang', '01045');
        });
    }

    $murid_trials = $query->paginate(25)->withQueryString();

// FORCE CHECK SEMUA DATA
foreach ($murid_trials as $murid) {
    $this->processStatusPromotion($murid);   // ← Tambahkan ini
    $murid->is_locked_guru = $this->isLockedForGuruTrial($murid, $isAdmin);
}
    // =============================================
    // UNIT OPTIONS
    // =============================================
    $unitOptions = Unit::orderBy('no_cabang')
        ->get()
        ->map(fn ($u) => [
            'value' => $u->id,
            'label' => trim(($u->no_cabang ?? '') . ' - ' . ($u->biMBA_unit ?? '')),
        ]);

   // =============================================
// DAFTAR GURU + KEPALA UNIT
// =============================================
$daftarGuru = [];   // ← JANGAN ISI DEFAULT DI SINI

// Ambil semua guru
$guruQuery = Profile::guru();

if (!$isAdmin) {
    $userUnit = trim($user->bimba_unit ?? '');
    if ($userUnit) {
        $guruQuery->where('bimba_unit', 'LIKE', "%{$userUnit}%");
    }
}

$gurus = $guruQuery->get();

foreach ($gurus as $g) {
    if (empty($g->nama)) continue;
    
    $label = trim($g->nama);
    if ($g->bimba_unit) {
        $label .= ' - ' . trim($g->bimba_unit);
    }
    
    $daftarGuru[$g->nama] = $label;
}

// Tambahkan Kepala Unit
$kepalaUnits = Profile::whereRaw('LOWER(jabatan) LIKE ?', ['%kepala unit%'])
                ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepala cabang%'])
                ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepsek%'])
                ->get();

foreach ($kepalaUnits as $ku) {
    if (empty($ku->nama)) continue;
    
    $label = trim($ku->nama);
    if ($ku->bimba_unit) {
        $label .= ' - ' . trim($ku->bimba_unit);
    }
    $label .= ' (Kepala Unit)';

    $key = $ku->nama;
    if (isset($daftarGuru[$key])) {
        $key .= ' (KU)';
    }
    $daftarGuru[$key] = $label;
}

asort($daftarGuru);   // Urutkan alfabetis

    return view('murid_trials.index', compact(
        'murid_trials',
        'daftarGuru',
        'unitOptions',
        'plainSearch',
        'status',
        'isAdmin'
    ));
}

/**
 * Cek apakah field guru_trial harus dikunci
 */
protected function isLockedForGuruTrial(MuridTrial $murid, bool $isAdmin): bool
{
    if ($isAdmin) {
        return false; // Admin selalu boleh edit
    }

    // Jika belum punya relasi student → boleh edit
    if (!$murid->student || empty($murid->student->nim)) {
        return false;
    }

    // Cek apakah sudah masuk Buku Induk
    $sudahBukuInduk = \App\Models\BukuInduk::where('nim', $murid->student->nim)->exists();

    // Cek apakah registrasi sudah accepted
    $sudahAccepted = $murid->student->registrations()
                        ->where('status', 'accepted')
                        ->exists();

    return $sudahBukuInduk || $sudahAccepted;
}

    public function store(Request $request)
{
    $rules = [
        'nama'       => 'required|string|max:255',
        'tgl_lahir'  => 'nullable|date',
        'usia'       => 'nullable|integer',
        'orangtua'   => 'nullable|string|max:255',
        'no_telp'    => 'nullable|string|max:20',
        'alamat'     => 'nullable|string',
        'bimba_unit' => 'nullable|string|max:100',
        'no_cabang'  => 'nullable|string|max:10',
        'tgl_mulai'  => 'nullable|date',
        'guru_trial' => 'nullable|string|max:255',
        'info'       => 'nullable|string',
        'tanggal_trial_baru' => 'nullable|date',
    ];

    $data = $request->validate($rules);

    // === DEFAULT STATUS BARU ===
    $data['status_trial'] = 'daftar_baru';

    if (empty($data['usia'] ?? null) && $data['tgl_lahir'] ?? null) {
        $data['usia'] = Carbon::parse($data['tgl_lahir'])->age;
    }

    if (empty($data['tanggal_trial_baru'])) {
        $data['tanggal_trial_baru'] = now()->format('Y-m-d');
    }

    $data['waktu_submit'] = now();

    MuridTrial::create($data);

    return redirect()->route('murid_trials.index')
        ->with('success', 'Data murid trial berhasil ditambahkan dengan status **Daftar Baru**.');
}

    public function update(Request $request, MuridTrial $murid_trial)
    {
        $data = $request->validate([
            'nama'           => 'required|string|max:255',
            'tgl_lahir'      => 'nullable|date',
            'usia'           => 'nullable|integer|min:1|max:120',
            'orangtua'       => 'nullable|string|max:255',
            'no_telp'        => 'nullable|string|max:20',
            'alamat'         => 'nullable|string',
            'bimba_unit'     => 'nullable|string|max:100',
            'no_cabang'      => 'nullable|string|max:10',
            'tgl_mulai'      => 'nullable|date',
            'guru_trial'     => 'nullable|string|max:255',
            'info'           => 'nullable|string',
            'tanggal_aktif'  => 'nullable|date', // hanya untuk status aktif
            'status_trial'   => 'required|in:aktif,batal,lanjut_daftar,baru',
            'tanggal_trial_baru' => 'nullable|date', // tambahkan ini
        ]);

        if (empty($data['usia']) && $data['tgl_lahir']) {
            $data['usia'] = Carbon::parse($data['tgl_lahir'])->age;
        }

        // Hanya status AKTIF yang boleh punya tanggal_aktif
        if ($data['status_trial'] === 'aktif') {
            $data['tanggal_aktif'] = $request->filled('tanggal_aktif')
                ? $request->tanggal_aktif
                : now()->format('Y-m-d');
        } else {
            $data['tanggal_aktif'] = null;
        }

        $murid_trial->update($data);

        $result = $this->processStatusPromotion($murid_trial);

        if (($result['action'] ?? null) === 'registration_create') {
            return redirect()->route('registrations.create', $result['params'])
                ->with($result['type'], $result['message']);
        }

        return redirect()->route('murid_trials.index')
            ->with($result['type'] ?? 'success', $result['message'] ?? 'Data berhasil diperbarui.');
    }

    public function updateStatus(Request $request, MuridTrial $murid_trial)
{
    $request->validate([
        'status_trial'       => 'required|in:daftar_baru,baru,aktif,batal,lanjut_daftar',
        'tanggal_aktif'      => 'nullable|date',
        'tanggal_trial_baru' => 'nullable|date',
    ]);

    $statusBaru = $request->status_trial;

    $updateData = [
        'status_trial' => $statusBaru,
    ];

    if ($statusBaru === 'aktif') {
        $updateData['tanggal_aktif'] = $request->filled('tanggal_aktif') 
            ? $request->tanggal_aktif 
            : now()->format('Y-m-d');
        
        // 🔥 PERBAIKAN: JANGAN NULL-KAN tanggal_trial_baru saat status aktif
        // $updateData['tanggal_trial_baru'] = null;   // ← BARIS INI DIHAPUS / DIKOMENTARI

    } elseif ($statusBaru === 'baru' || $statusBaru === 'daftar_baru') {
        
        $updateData['tanggal_trial_baru'] = $request->filled('tanggal_trial_baru') 
            ? $request->tanggal_trial_baru 
            : ($murid_trial->tanggal_trial_baru ?? now()->format('Y-m-d'));
        
        $updateData['tanggal_aktif'] = null;

    } else {
        // batal, lanjut_daftar, dll
        $updateData['tanggal_aktif'] = null;
        // Untuk status lain, kita pertahankan tanggal_trial_baru (jangan di-null)
        // $updateData['tanggal_trial_baru'] = null;   // ← JANGAN AKTIFKAN
    }

    $murid_trial->update($updateData);

    $result = $this->processStatusPromotion($murid_trial);

    if (($result['action'] ?? null) === 'registration_create') {
        return redirect()->route('registrations.create', $result['params'])
            ->with($result['type'], $result['message']);
    }

    $message = match ($statusBaru) {
        'batal'         => 'Status diubah menjadi BATAL dan nama guru telah dikosongkan otomatis.',
        'aktif'         => 'Status diubah menjadi AKTIF.',
        'baru', 'daftar_baru' => 'Status diubah menjadi TRIAL BARU.',
        'lanjut_daftar' => 'Status diubah menjadi LANJUT DAFTAR.',
        default         => 'Status berhasil diubah.',
    };

    return redirect()->route('murid_trials.index')
        ->with('success', $message);
}

            protected function processStatusPromotion(MuridTrial $murid_trial): array
    {
        // FORCE CHECK LANJUT DAFTAR
        if ($murid_trial->student) {
            $student = $murid_trial->student;

            $sudahBukuInduk = \App\Models\BukuInduk::where('nim', $student->nim)->exists();
            $sudahAccepted  = $student->registrations()
                                ->where('status', 'accepted')
                                ->exists();

            if (($sudahBukuInduk || $sudahAccepted) && $murid_trial->status_trial !== 'lanjut_daftar') {
                $murid_trial->update([
                    'status_trial'  => 'lanjut_daftar',
                    'tanggal_aktif' => null,
                ]);
                Log::info('🔄 AUTO UPGRADE ke LANJUT DAFTAR', [
                    'murid_trial_id' => $murid_trial->id,
                    'nama'           => $murid_trial->nama,
                ]);
            }
        }

        // === SINKRONISASI KE STUDENT TABLE ===
        $this->syncTrialStatusToStudent($murid_trial);

        // SINKRON BATAL KE STUDENT
        $this->syncBatalToStudent($murid_trial);

        // LANJUT DAFTAR
        if ($murid_trial->status_trial === 'lanjut_daftar') {
            $student = $murid_trial->student ?? $this->ensureStudentFor($murid_trial);

            ParentCommitment::updateOrCreate(
                ['murid_trial_id' => $murid_trial->id],
                [
                    'parent_name' => $murid_trial->orangtua ?: 'Orang Tua',
                    'child_name'  => $murid_trial->nama,
                    'phone'       => $murid_trial->no_telp,
                    'address'     => $murid_trial->alamat,
                    'agreed'      => true,
                    'signed_at'   => now(),
                    'student_id'  => $student->id,
                ]
            );

            return [
                'type'    => 'success',
                'message' => "Status Lanjut Daftar - Form pendaftaran siap",
                'action'  => 'registration_create',
                'params'  => [
                    'student_id'   => $student->id,
                    'tahun_ajaran' => Registration::currentAcademicYear(),
                    'from_trial'   => true,
                ],
            ];
        }

        // STATUS LAINNYA
        if ($murid_trial->status_trial === 'baru') {
            $this->ensureStudentFor($murid_trial);
            return ['type' => 'success', 'message' => 'Status: BARU'];
        }

        if ($murid_trial->status_trial === 'aktif') {
            $this->ensureStudentFor($murid_trial);
            $tgl = $murid_trial->tanggal_aktif?->format('d-m-Y') ?? 'hari ini';
            return ['type' => 'info', 'message' => "Trial AKTIF sejak: {$tgl}"];
        }

        if ($murid_trial->status_trial === 'batal') {
            return ['type' => 'warning', 'message' => 'Status: BATAL.'];
        }

        return ['type' => 'success', 'message' => 'Status diperbarui.'];
    }

    /**
     * Sinkronisasi status MuridTrial ke Student (khusus lanjut_daftar)
     */
    protected function syncTrialStatusToStudent(MuridTrial $murid_trial): void
    {
        if (!$murid_trial->student) {
            return;
        }

        $newStatus = match ($murid_trial->status_trial) {
            'lanjut_daftar' => 'lanjut_daftar',
            'batal'         => 'batal',
            'aktif'         => 'aktif',
            default         => $murid_trial->status_trial,
        };

        if ($murid_trial->student->trial_status !== $newStatus) {
            $murid_trial->student->update([
                'trial_status' => $newStatus,
            ]);

            Log::info('✅ Sinkronisasi status ke Student berhasil', [
                'nama'          => $murid_trial->nama,
                'trial_status'  => $murid_trial->status_trial,
                'student_status'=> $newStatus,
            ]);
        }
    }

    protected function ensureStudentFor(MuridTrial $murid_trial): Student
    {
        return DB::transaction(function () use ($murid_trial) {
            if ($student = $murid_trial->student) {
                return $student;
            }

            return Student::create([
                'murid_trial_id' => $murid_trial->id,
                'nama'           => $murid_trial->nama,
                'kelas'          => $murid_trial->kelas,
                'tgl_lahir'      => $murid_trial->tgl_lahir,
                'usia'           => $murid_trial->usia,
                'orangtua'       => $murid_trial->orangtua,
                'no_telp'        => $murid_trial->no_telp,
                'alamat'         => $murid_trial->alamat,
                'guru_wali'      => $murid_trial->guru_trial,
                'source'         => 'trial',
                'tanggal_masuk'  => $murid_trial->tgl_mulai,
                'bimba_unit'     => $murid_trial->bimba_unit,
                'no_cabang'      => $murid_trial->no_cabang,
                'nim'            => null, // SELALU NULL — tidak masuk buku induk
                'tanggal_trial_baru' => $murid_trial->tanggal_trial_baru,
            ]);
        });
    }

    public function destroy(MuridTrial $murid_trial)
{
    $murid_trial->update(['guru_trial' => null]); // aman
    $murid_trial->delete();

    return redirect()->route('murid_trials.index')
        ->with('success', 'Data murid trial berhasil dihapus.');
}

    public function updateGuru(Request $request, MuridTrial $muridTrial)
{
    $validated = $request->validate([
        'guru_trial' => 'nullable|string|max:255',
    ]);

    $guruBaru = $validated['guru_trial'] ?? null;

    // Jika user memilih "- Pilih Guru -" (kosong), simpan sebagai null
    if ($guruBaru === '' || $guruBaru === '- Pilih Guru -') {
        $guruBaru = null;
    }

    $muridTrial->update([
        'guru_trial' => $guruBaru,
    ]);

    $message = $guruBaru 
        ? "✅ Guru trial diperbarui menjadi: {$guruBaru}"
        : '✅ Guru trial berhasil dikosongkan';

    return back()->with('success', $message);
}

    public function searchAjax(Request $request)
    {
        $term = trim((string) $request->get('q', ''));
        $unit = $request->get('unit');

        $query = MuridTrial::with('student');

        if ($term !== '') {
            $query->where(fn($q) => $q
                ->where('nama', 'like', "%{$term}%")
                ->orWhere('no_telp', 'like', "%{$term}%")
                ->orWhereHas('student', fn($sq) => $sq->where('nama', 'like', "%{$term}%"))
            );
        }

        if ($unit) {
            $query->where('bimba_unit', $unit);
        }

        return response()->json(
            $query->take(30)->get(['id', 'nama', 'no_telp', 'bimba_unit'])
                  ->map(fn($r) => [
                      'id'         => $r->id,
                      'text'       => $r->nama,
                      'nama'       => $r->nama,
                      'no_telp'    => $r->no_telp,
                      'bimba_unit' => $r->bimba_unit,
                  ])
        );
    }

    public function show($id)
    {
        abort(404);
    }
    protected function autoActivateTrial()
{
    $batas = now()->subDay();

    $trials = MuridTrial::where('status_trial', 'baru')
        ->whereNull('tanggal_aktif')
        ->where('waktu_submit', '<=', $batas)
        ->get();

    foreach ($trials as $trial) {
        $trial->update([
            'status_trial'  => 'aktif',
            'tanggal_aktif' => \Carbon\Carbon::parse($trial->waktu_submit)->addDay(),
        ]);
    }
}


    /**
     * Sinkronisasi status batal ke Student
     */
    protected function syncBatalToStudent(MuridTrial $murid_trial): void
    {
        if ($murid_trial->status_trial === 'batal' && $murid_trial->student) {
            $murid_trial->student->update([
                'trial_status' => 'batal',
            ]);

            // Tolak registrasi yang masih pending
            $murid_trial->student->registrations()
                ->where('tahun_ajaran', Registration::currentAcademicYear())
                ->whereIn('status', ['pending', 'verified'])
                ->update(['status' => 'rejected']);

            Log::info('Status BATAL disinkronkan ke Student', [
                'murid_trial_id' => $murid_trial->id,
                'student_id'     => $murid_trial->student->id,
                'nama'           => $murid_trial->nama
            ]);
        }
    }

}