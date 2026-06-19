<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WheelWinner;
use App\Models\Student;
use App\Models\VoucherLama;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class WheelController extends Controller
{
    protected array $vouchers = [
        'Rp 50.000',
        'Rp 100.000',
        'Rp 150.000',
        'Rp 200.000',
        'Rp 250.000',
        'Rp 300.000',
        'Rp 350.000',
        'Rp 400.000',
        'Rp 450.000',
        'Rp 500.000',
        'Rp 550.000',
        'Rp 600.000',
        'Rp 650.000',
        'Rp 700.000',
        'Rp 750.000',
        'Rp 800.000',
        'Rp 850.000',
        'Rp 900.000',
        'Rp 950.000',
        'Rp 1.000.000',
        'Rp 1.050.000',
        'Rp 1.100.000',
        'Rp 1.150.000',
        'Rp 1.200.000'
    ];

    public function publicIndex(Request $request, $row_hash)
    {
        // Periksa signature
        if (! $request->hasValidSignature()) {
            Log::warning('Invalid signature in publicIndex', [
                'url' => $request->fullUrl(),
                'row_hash' => $row_hash
            ]);
            abort(403, 'Tanda tangan URL tidak valid atau kadaluarsa.');
        }

        // Cek row_hash valid
        $all = $this->fetchAvailableFromStudents();
        $found = collect($all)->first(fn($x) => ($x['row_hash'] ?? '') === $row_hash);
        if (! $found) {
            Log::warning('Humas not found in publicIndex', ['row_hash' => $row_hash]);
            abort(404, 'Humas tidak ditemukan atau sudah pernah menang.');
        }

        // Gunakan temporarySignedRoute untuk spin_post_url
        $spin_post_url = URL::temporarySignedRoute(
            'wheels.public.spin',
            now()->addHours(1),
            ['row_hash' => $row_hash]
        );

        return view('wheels.public', [
            'row_hash' => $row_hash,
            'referrer_name' => $found['referrer_name'] ?? $found['name'] ?? null,
            'brought_name' => $found['brought_name'] ?? null,
            'spin_post_url' => $spin_post_url,
        ]);
    }

    /**
     * Public spin — menerima POST dari halaman publik.
     * Periksa signature manual (hasValidSignature).
     */
    public function publicSpin(Request $request)
{
    if (! $request->hasValidSignature()) {
        return response()->json(['error' => 'Invalid signature.'], 403);
    }

    $row_hash = $request->query('row_hash');
    if (empty($row_hash)) {
        return response()->json(['error' => 'row_hash is required'], 422);
    }

    $available = $this->fetchAvailableFromStudents();
    $chosen = collect($available)->first(fn($x) => ($x['row_hash'] ?? '') === $row_hash);

    if (!$chosen) {
        return response()->json(['error' => 'Data tidak tersedia atau sudah digunakan.'], 422);
    }

    $voucherIndex = random_int(0, count($this->vouchers) - 1);
    $voucher = $this->vouchers[$voucherIndex];

    try {
        $winner = DB::transaction(function () use ($chosen, $voucher, $voucherIndex) {

            $studentId = $chosen['student_id'] ?? null;
            $rowHash   = $chosen['row_hash'] ?? null;
            $referrer  = $chosen['referrer_name'] ?? '';
            $brought   = $chosen['brought_name'] ?? '';
            $bimbaUnit = $chosen['bimba_unit'] ?? null;
            $noCabang  = $chosen['no_cabang'] ?? null;

            $displayName = $referrer;
            if ($brought) {
                $displayName = strtoupper($referrer) . ' (' . $brought . ')';
            }

            // Pengecekan hanya row_hash dan nama (tidak ada batas cabang lagi)
            if (!empty($rowHash) && WheelWinner::where('row_hash', $rowHash)->exists()) {
                throw new \RuntimeException('row_hash_duplicate');
            }

            if (WheelWinner::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($displayName))])->exists()) {
                throw new \RuntimeException('name_duplicate');
            }

            $w = new WheelWinner();
            $w->name           = $displayName;
            $w->voucher        = $voucher;
            $w->voucher_index  = $voucherIndex;
            $w->row_hash       = $rowHash;
            $w->voucher_amount = $this->parseRpToInt($voucher);
            if ($studentId) $w->student_id = $studentId;
            $w->bimba_unit     = $bimbaUnit;
            $w->no_cabang      = $noCabang;
            $w->won_at         = now();
            $w->save();

            return $w;
        });
    } catch (\Throwable $e) {
        $msg = $e->getMessage();

        if (str_contains($msg, 'row_hash_duplicate')) {
            return response()->json(['error' => 'Data ini sudah pernah digunakan untuk spin.'], 409);
        }

        if (str_contains($msg, 'name_duplicate')) {
            return response()->json(['error' => 'Nama ini sudah pernah menang sebelumnya.'], 409);
        }

        Log::error('Public spin failure: ' . $msg);
        return response()->json(['error' => 'Gagal menyimpan pemenang. Silakan coba lagi.'], 409);
    }

    // Buat voucher
    $createdOk = $this->createVouchersAndFlash($winner);

    return response()->json([
        'name' => $winner->name,
        'voucher' => $winner->voucher,
        'voucher_index' => (int) $winner->voucher_index,
        'won_at' => optional($winner->won_at)->toDateTimeString(),
        'voucher_amount' => $winner->voucher_amount,
        'row_hash' => $winner->row_hash,
        'voucher_created' => (bool) $createdOk,
    ]);
}

    public function index()
    {
        $available = $this->fetchAvailableFromStudents();
        return view('wheels.index', ['availableNames' => array_values($available)]);
    }

    public function names(Request $request)
    {
        $available = $this->fetchAvailableFromStudents();

        $rowHash = $request->query('row_hash');
        $studentId = $request->query('student_id');

        if ($rowHash) {
            $available = array_values(array_filter($available, function ($it) use ($rowHash) {
                return (isset($it['row_hash']) && $it['row_hash'] === $rowHash)
                    || (isset($it['row_hash']) && (string)$it['row_hash'] === (string)$rowHash);
            }));
        } elseif ($studentId) {
            $available = array_values(array_filter($available, function ($it) use ($studentId) {
                return isset($it['student_id']) && ((string)$it['student_id'] === (string)$studentId);
            }));
        }

        return response()->json(['data' => array_values($available)]);
    }

    /**
     * Lookup data referrer + info voucher terakhir
     */
    public function lookup(Request $request)
    {
        $row = $request->query('row_hash');
        $ref  = $request->query('referrer');

        Log::info('wheels.lookup called', ['row_hash' => $row, 'referrer' => $ref, 'ip' => $request->ip()]);

        $all = $this->fetchAvailableFromStudents() ?? [];
        $normalize = fn($s) => mb_strtolower(trim((string)($s ?? '')));

        $found = null;
        if ($row) {
            $found = collect($all)->first(fn($x) => (($x['row_hash'] ?? '') === $row));
        } elseif ($ref) {
            $needle = $normalize($ref);
            $found = collect($all)->first(fn($x) => $normalize($x['referrer_name'] ?? '') === $needle);
            if (! $found) {
                $found = collect($all)->first(fn($x) => mb_stripos($normalize($x['referrer_name'] ?? ''), $needle) !== false);
            }
        }

        if (! $found) {
            Log::warning('wheels.lookup not found', ['row_hash' => $row, 'referrer' => $ref]);
            return response()->json(['error' => 'Tidak ditemukan'], 404);
        }

        $studentData = null;
        if (!empty($found['student_id'])) {
            $student = Student::find($found['student_id']);
            if ($student) {
                $studentData = [
                    'student_id' => $student->id,
                    'nim'        => $student->nim ?? null,
                    'nama'       => $student->nama ?? ($student->name ?? null),
                    'orangtua'   => $student->orangtua ?? ($student->parent_name ?? null),
                    'telp_hp'    => $student->telp_hp ?? ($student->no_telp ?? $student->no_telp_hp ?? null),
                ];
            }
        }

        $lastVoucherAmount = null;
        if (!empty($found['row_hash'])) {
            $last = WheelWinner::where('row_hash', $found['row_hash'])->latest('won_at')->first();
            if ($last) {
                $lastVoucherAmount = $last->voucher_amount ?? $this->parseRpToInt($last->voucher ?? null);
            }
        }

        if ($lastVoucherAmount === null && !empty($found['referrer_name'])) {
            $needle = '%' . $normalize($found['referrer_name']) . '%';
            $last = WheelWinner::whereRaw('LOWER(name) LIKE ?', [$needle])->latest('won_at')->first();
            if ($last) {
                $lastVoucherAmount = $last->voucher_amount ?? $this->parseRpToInt($last->voucher ?? null);
            }
        }

        $voucher_count = null;
        $suggested_voucher_numbers = null;
        if (!empty($lastVoucherAmount) && is_numeric($lastVoucherAmount) && $lastVoucherAmount > 0) {
            $voucher_count = max(1, (int) ceil((float) $lastVoucherAmount / 50000));
            $suggested_voucher_numbers = $this->generateVoucherNumbers($voucher_count, 'WHEEL');
        }

        $response = array_merge($found, [
            'student' => $studentData,
            'last_voucher_amount' => $lastVoucherAmount,
            'voucher_count' => $voucher_count,
            'suggested_voucher_numbers' => $suggested_voucher_numbers,
        ]);

        return response()->json($response);
    }

    /**
     * Parse string mata uang seperti "Rp 100.000" atau "100000" menjadi integer (defensif).
     */
    protected function parseRpToInt($value): ?int
    {
        if ($value === null) return null;
        $digits = preg_replace('/[^\d]/u', '', (string) $value);
        if ($digits === '') return null;
        return (int) $digits;
    }

    /**
     * Generate nomor voucher sejumlah $count, prefix opsional.
     * Mengecek DB VoucherLama agar tidak menghasilkan duplikat.
     */
    protected function generateVoucherNumbers(int $count, string $prefix = null): array
    {
        $out = [];
        $prefix = $prefix ? strtoupper($prefix) . '-' . date('Ymd') : 'VCHR-' . date('Ymd');

        $attempts = 0;
        $maxAttempts = max(100, $count * 30);

        while (count($out) < $count && $attempts < $maxAttempts) {
            $attempts++;
            $rand = strtoupper(Str::random(6));
            $candidate = $prefix . '-' . $rand . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            if (in_array($candidate, $out, true)) continue;
            $exists = VoucherLama::where('voucher', $candidate)->exists();
            if ($exists) continue;

            $out[] = $candidate;
        }

        while (count($out) < $count) {
            $out[] = $prefix . '-' . uniqid();
        }

        return $out;
    }

    /**
     * Ambil data available dari tabel students
     */
    protected function fetchAvailableFromStudents(): array
{
    $humasCol = 'informasi_humas_nama';
    $studentNameCol = 'nama';

    $user = Auth::user();
    $isAdmin = $user && (
        ($user->is_admin ?? false) || 
        in_array($user->role ?? '', ['admin', 'superadmin', 'keuangan'])
    );

    $userUnit     = trim($user->bimba_unit ?? '');
    $userNoCabang = trim($user->no_cabang ?? '');

    // ==================== STUDENTS ====================
    $queryStudents = DB::table('students')
        ->join('registrations', function ($join) {
            $join->on('registrations.student_id', '=', 'students.id')
                ->where('registrations.status', '=', 'accepted');
        })
        ->whereNotNull($humasCol)
        ->whereRaw("TRIM({$humasCol}) <> ''");

    // === FILTER UNIT (Hanya untuk non-admin) ===
    if (!$isAdmin && ($userUnit || $userNoCabang)) {
        $queryStudents->where(function ($q) use ($userUnit, $userNoCabang) {
            // Filter berdasarkan nama unit
            if ($userUnit) {
                $q->where('students.bimba_unit', 'LIKE', "%{$userUnit}%")
                  ->orWhere('registrations.bimba_unit', 'LIKE', "%{$userUnit}%");
            }

            // Filter berdasarkan no_cabang
            if ($userNoCabang) {
                $q->orWhere('students.no_cabang', $userNoCabang)
                  ->orWhere('registrations.no_cabang', $userNoCabang);
            }

            // Mapping unit khusus
            $allowedNoCabang = [];
            $lowerUnit = strtolower($userUnit);

            if (str_contains($lowerUnit, 'sapta taruna')) {
                $allowedNoCabang[] = '01045';
            } elseif (str_contains($lowerUnit, 'griya pesona madani')) {
                $allowedNoCabang[] = '05141';
            } elseif (str_contains($lowerUnit, 'villa bekasi indah')) {
                $allowedNoCabang[] = '00340';
            }

            if (!empty($allowedNoCabang)) {
                $q->orWhereIn('students.no_cabang', $allowedNoCabang)
                  ->orWhereIn('registrations.no_cabang', $allowedNoCabang);
            }
        });
    }

    $fromStudents = $queryStudents->select([
        'students.id as student_id',
        'students.nim as nim_murid',
        DB::raw("TRIM({$humasCol}) as humas_name_raw"),
        DB::raw("TRIM({$studentNameCol}) as student_name"),
        'registrations.bimba_unit',
        'registrations.no_cabang',
        DB::raw("'student' as source")
    ])->get();

    // ==================== BUKU INDUK ====================
    $queryBuku = DB::table('buku_induk')
        ->where('info', 'humas')
        ->whereNotNull('nama_humas')
        ->whereRaw("TRIM(nama_humas) <> ''");

    // === FILTER UNIT (Hanya untuk non-admin) ===
    if (!$isAdmin && ($userUnit || $userNoCabang)) {
        $queryBuku->where(function ($q) use ($userUnit, $userNoCabang) {
            if ($userUnit) {
                $q->where('bimba_unit', 'LIKE', "%{$userUnit}%");
            }

            if ($userNoCabang) {
                $q->orWhere('no_cabang', $userNoCabang);
            }

            $allowedNoCabang = [];
            $lowerUnit = strtolower($userUnit);

            if (str_contains($lowerUnit, 'sapta taruna')) {
                $allowedNoCabang[] = '01045';
            } elseif (str_contains($lowerUnit, 'griya pesona madani')) {
                $allowedNoCabang[] = '05141';
            } elseif (str_contains($lowerUnit, 'villa bekasi indah')) {
                $allowedNoCabang[] = '00340';
            }

            if (!empty($allowedNoCabang)) {
                $q->orWhereIn('no_cabang', $allowedNoCabang);
            }
        });
    }

    $fromBukuInduk = $queryBuku->select([
        DB::raw('NULL as student_id'),
        'nim as nim_murid',
        DB::raw('TRIM(nama_humas) as humas_name_raw'),
        DB::raw('TRIM(nama) as student_name'),
        'bimba_unit',
        'no_cabang',
        DB::raw("'buku_induk' as source")
    ])->get();

    $rows = $fromStudents->merge($fromBukuInduk);

    // ==================== DATA PEMENANG ====================
    $winnerHashes = WheelWinner::whereNotNull('row_hash')
        ->pluck('row_hash')
        ->map(fn($v) => trim((string) $v))
        ->toArray();

    $winnerNames = WheelWinner::pluck('name')
        ->filter()
        ->map(function ($n) {
            return preg_replace('/\s+/', ' ', mb_strtolower(trim((string) $n)));
        })
        ->toArray();

    $seen = [];
    $out = [];

    foreach ($rows as $r) {
        $referrerRaw = trim((string) ($r->humas_name_raw ?? ''));
        $broughtRaw  = trim((string) ($r->student_name ?? ''));

        if ($referrerRaw === '') {
            continue;
        }

        // Dedup
        $uniqueKey = mb_strtolower($referrerRaw) . '|' . mb_strtolower($broughtRaw);
        if (isset($seen[$uniqueKey])) {
            continue;
        }
        $seen[$uniqueKey] = true;

        // Generate row_hash
        if ($r->source === 'student' && !empty($r->student_id)) {
            $rowHash = md5('stu:' . $r->student_id);
        } else {
            $rowHash = md5(json_encode([
                $r->nim_murid,
                $r->student_name,
                $r->humas_name_raw,
                $r->bimba_unit ?? '',
            ], JSON_UNESCAPED_UNICODE));
        }

        // Display name
        $displayName = strtoupper(trim($referrerRaw));
        if ($broughtRaw !== '') {
            $displayName .= ' (' . trim($broughtRaw) . ')';
        }

        $normalizedName = preg_replace('/\s+/', ' ', mb_strtolower(trim($displayName)));

        // Skip jika sudah menang
        if (in_array($rowHash, $winnerHashes, true)) continue;
        if (in_array($normalizedName, $winnerNames, true)) continue;
        if (WheelWinner::where('row_hash', $rowHash)->exists()) continue;
        if (WheelWinner::whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])->exists()) continue;

        $out[] = [
            'student_id'    => $r->student_id,
            'nim_murid'     => $r->nim_murid,
            'referrer_name' => $referrerRaw,
            'brought_name'  => $broughtRaw,
            'name'          => $referrerRaw,
            'row_hash'      => $rowHash,
            'bimba_unit'    => $r->bimba_unit ?? null,
            'no_cabang'     => $r->no_cabang ?? null,
            'source'        => $r->source,
        ];
    }

    usort($out, function ($a, $b) {
        return strcasecmp($a['referrer_name'], $b['referrer_name']);
    });

    return $out;
}
  public function spin(Request $request)
{
    $request->validate([
        'row_hash' => ['nullable', 'string'],
        'name'     => ['nullable', 'string'],
    ]);

    $available = $this->fetchAvailableFromStudents();
    if (empty($available)) {
        return response()->json(['error' => 'Tidak ada nama tersedia untuk undian.'], 422);
    }

    $rowHashReq = $request->input('row_hash');
    $nameReq    = trim((string) $request->input('name', ''));

    $chosen = null;
    if ($rowHashReq) {
        $chosen = collect($available)->first(fn($x) => ($x['row_hash'] ?? '') === $rowHashReq);
    }
    if (!$chosen && $nameReq !== '') {
        $chosen = collect($available)->first(function ($x) use ($nameReq) {
            $ref = trim($x['referrer_name'] ?? '');
            return strcasecmp($ref, $nameReq) === 0;
        });
    }
    if (!$chosen) {
        $chosen = collect($available)->random();
    }

    $voucherIndex = random_int(0, count($this->vouchers) - 1);
    $voucher = $this->vouchers[$voucherIndex];

    try {
        $winner = DB::transaction(function () use ($chosen, $voucher, $voucherIndex) {

            $displayName = $chosen['referrer_name'] ?? '';
            if (!empty($chosen['brought_name'])) {
                $displayName = strtoupper($displayName) . ' (' . $chosen['brought_name'] . ')';
            }

            // Hanya cek row_hash (tidak cek no_cabang lagi)
            if (!empty($chosen['row_hash']) && WheelWinner::where('row_hash', $chosen['row_hash'])->exists()) {
                throw new \RuntimeException('row_hash_duplicate');
            }

            $w = WheelWinner::create([
                'name'           => $displayName,
                'voucher'        => $voucher,
                'voucher_index'  => $voucherIndex,
                'voucher_amount' => $this->parseRpToInt($voucher),
                'row_hash'       => $chosen['row_hash'] ?? null,
                'student_id'     => $chosen['student_id'] ?? null,
                'bimba_unit'     => $chosen['bimba_unit'] ?? null,
                'no_cabang'      => $chosen['no_cabang'] ?? null,
                'won_at'         => now(),
            ]);

            return $w;
        });
    } catch (\Throwable $e) {
        $msg = $e->getMessage();
        Log::error('Spin Error', ['message' => $msg, 'chosen' => $chosen]);

        if (str_contains($msg, 'row_hash_duplicate')) {
            return response()->json(['error' => 'Data ini sudah pernah digunakan untuk spin.'], 409);
        }

        // Tangani error unique no_cabang dari database
        if (str_contains($msg, 'no_cabang_unique') || str_contains($msg, 'Duplicate entry')) {
            return response()->json([
                'error' => 'Cabang ini sudah pernah menang. Sistem mengizinkan multiple win, tapi database masih ada batasan. Hubungi developer untuk hapus unique constraint.'
            ], 409);
        }

        return response()->json(['error' => 'Gagal menyimpan pemenang. Silakan coba lagi.'], 409);
    }

    $createdOk = $this->createVouchersAndFlash($winner);

    return response()->json([
        'name' => $winner->name,
        'voucher' => $winner->voucher,
        'voucher_index' => (int) $winner->voucher_index,
        'id' => $winner->id,
        'row_hash' => $winner->row_hash,
        'no_cabang' => $winner->no_cabang,
        'won_at' => optional($winner->won_at)->toDateTimeString(),
        'voucher_amount' => $winner->voucher_amount,
        'voucher_created' => (bool) $createdOk,
    ]);
}


    public function history(Request $request)
    {
        $perPage = (int) $request->input('per_page', 25);
        if ($perPage <= 0) $perPage = 25;

        $winners = WheelWinner::latest('won_at')->paginate($perPage);
        return response()->json($winners);
    }

    /**
     * Helper: buat voucher rows di voucher_lama dan flash hasil ke session.
     * Menggunakan DB::transaction + logging yang lebih lengkap.
     *
     * @param \App\Models\WheelWinner $winner
     * @return bool true jika berhasil membuat voucher rows, false jika gagal
     */
    /**
 * Helper: buat voucher rows di voucher_lama dan flash hasil ke session.
 * Mengembalikan true jika berhasil membuat voucher rows, false jika gagal.
 *
 * Perubahan penting:
 * - tanggal_penyerahan diset NULL setelah spin
 * - status diset 'belum_diserahkan'
 * - flash menggunakan key 'spinResult' dan field 'tanggal_spin'
 */
/**
 * Helper: buat voucher rows di voucher_lama setelah spin
 */
protected function createVouchersAndFlash(\App\Models\WheelWinner $winner): bool
{
    try {
        $nilaiPerVoucher = 50000;
        $voucherAmount = (int) ($winner->voucher_amount ?? $this->parseRpToInt($winner->voucher ?? null) ?? 50000);
        $voucherCount = max(1, intdiv($voucherAmount, $nilaiPerVoucher));

        $prefix = 'SPIN-' . date('Ymd');
        $voucherNumbers = $this->generateVoucherNumbers($voucherCount, $prefix);

        $rawName = (string) ($winner->name ?? '');
        $referrerName = trim(preg_replace('/\s*\(.*?\)$/', '', $rawName)); // Nama Humas → Chayra
        $broughtName  = trim(preg_match('/\((.*?)\)/', $rawName, $m) ? $m[1] : ''); // Nama Murid Baru → Zahira

        // === CARI DATA HUMAS (Chayra) ===
        $humasRecord = \App\Models\BukuInduk::whereRaw('LOWER(TRIM(nama)) = ?', 
            [mb_strtolower($referrerName)])
            ->orWhereRaw('LOWER(TRIM(nama_humas)) = ?', [mb_strtolower($referrerName)])
            ->first();

        // === CARI DATA MURID BARU (Zahira) ===
        $muridRecord = \App\Models\BukuInduk::whereRaw('LOWER(TRIM(nama)) = ?', 
            [mb_strtolower($broughtName)])
            ->first();

        $nimHumas     = $humasRecord?->nim ?? null;
        $nimMuridBaru = $muridRecord?->nim ?? $winner->student?->nim ?? null;

        $createdRows = [];

        DB::transaction(function () use (&$createdRows, $voucherNumbers, $nilaiPerVoucher, 
            $humasRecord, $muridRecord, $referrerName, $broughtName, $nimHumas, $nimMuridBaru, $winner) {

            foreach ($voucherNumbers as $vnum) {

                $row = \App\Models\VoucherLama::create([
                    'voucher'                => $vnum,
                    'no_voucher'             => $vnum,
                    'jumlah_voucher'         => 1,
                    'nominal'                => $nilaiPerVoucher,
                    'tanggal'                => now()->toDateString(),
                    'tanggal_penyerahan'     => null,
                    'status'                 => 'belum_diserahkan',
                    'source'                 => 'spin',
                    'tipe_voucher'           => 'regular',

                    // === DATA HUMAS ===
                    'nim'                    => $nimHumas,                    // NIM Chayra (jika ada)
                    'nama_murid'             => $referrerName,               // Chayra Nadhifa
                    'orangtua'               => $humasRecord?->orangtua,
                    'telp_hp'                => $humasRecord?->no_telp_hp ?? $humasRecord?->no_telp,

                    // === DATA MURID BARU ===
                    'nim_murid_baru'         => $nimMuridBaru,               // NIM Zahira
                    'nama_murid_baru'        => $broughtName,                // Zahira Ceisya Habibah
                    'orangtua_murid_baru'    => $muridRecord?->orangtua ?? $humasRecord?->orangtua,
                    'telp_hp_murid_baru'     => $muridRecord?->no_telp_hp ?? $muridRecord?->no_telp ?? 
                                                $humasRecord?->no_telp_hp,

                    'bimba_unit'             => $winner->bimba_unit,
                    'no_cabang'              => $winner->no_cabang,
                ]);

                $createdRows[] = $row;
            }
        });

        // Flash Result
        Session::flash('spinResult', [
            'count' => count($createdRows),
            'nominal' => $voucherAmount,
            'nominal_formatted' => number_format($voucherAmount, 0, ',', '.'),
            'rows' => collect($createdRows)->map(fn($r) => [
                'voucher'         => $r->voucher,
                'humas'           => $r->nama_murid,
                'nim_humas'       => $r->nim,
                'nim_murid_baru'  => $r->nim_murid_baru,
                'nama_murid_baru' => $r->nama_murid_baru,
            ])->toArray(),
        ]);

        return true;

    } catch (\Throwable $e) {
        Log::error('createVouchersAndFlash FAILED', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return false;
    }
}

/**
 * Generate Signed URL untuk Orang Tua
 */
/**
 * Generate Signed URL untuk Orang Tua
 */
public function getParentSpinLink(Request $request)
{
    $row_hash = $request->get('row_hash');
    $child_name = $request->get('child_name');

    if (empty($row_hash) && empty($child_name)) {
        return response()->json([
            'success' => false,
            'error' => 'Parameter row_hash atau child_name diperlukan'
        ], 422);
    }

    try {
        $available = $this->fetchAvailableFromStudents();
        $found = null;

        if ($row_hash) {
            $found = collect($available)->first(fn($x) => 
                ($x['row_hash'] ?? '') === $row_hash
            );
        } elseif ($child_name) {
            $child_name = trim($child_name);
            $found = collect($available)->first(function ($x) use ($child_name) {
                return strcasecmp(trim($x['brought_name'] ?? ''), $child_name) === 0 ||
                       strcasecmp(trim($x['referrer_name'] ?? ''), $child_name) === 0;
            });
        }

        if (!$found || empty($found['row_hash'])) {
            return response()->json([
                'success' => false,
                'error' => 'Data tidak ditemukan atau sudah tidak tersedia'
            ], 404);
        }

        $signedUrl = URL::temporarySignedRoute(
            'wheels.public.index',
            now()->addDays(7),
            ['row_hash' => $found['row_hash']]
        );

        return response()->json([
            'success' => true,
            'url'     => $signedUrl,
            'name'    => $found['referrer_name'] ?? $found['name'] ?? ''
        ]);

    } catch (\Exception $e) {
        Log::error('getParentSpinLink Error', [
            'row_hash' => $row_hash,
            'child_name' => $child_name,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
        ], 500);
    }
}

public function delete(Request $request)
{
    $user = Auth::user();
    if (!in_array($user->role ?? '', ['admin', 'superadmin'])) {
        return response()->json(['success' => false, 'message' => 'Hanya admin yang boleh menghapus'], 403);
    }

    $request->validate(['row_hash' => 'required|string']);

    // Sesuaikan dengan model Anda
    $deleted = DB::table('wheel_participants') // atau nama tabel Anda
                ->where('row_hash', $request->row_hash)
                ->delete();

    if ($deleted) {
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
}
}
