<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoucherLama;
use App\Models\BukuInduk;
use App\Models\VoucherHistori;
use App\Imports\VoucherLamaImport;
use App\Exports\VoucherLamaExport;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class VoucherLamaController extends Controller
{
    // ---------------------------
    // Helper file functions
    // ---------------------------

    /**
     * Simpan file bukti penyerahan (jika ada) dan kembalikan path yang disimpan (tanpa prefix 'public/').
     *
     * @param \Illuminate\Http\UploadedFile|null $file
     * @return string|null
     */
    protected function storeBuktiPenyerahanFile(?\Illuminate\Http\UploadedFile $file): ?string
{
    if (!$file || !$file->isValid()) {
        return null;
    }

    try {
        // Pastikan folder ada
        if (!Storage::disk('public')->exists('bukti_penyerahan')) {
            Storage::disk('public')->makeDirectory('bukti_penyerahan');
        }

        $path = $file->store('bukti_penyerahan', 'public');

        if (!$path) {
            Log::error('Gagal menyimpan file bukti', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize()
            ]);
            return null;
        }

        return $path;
    } catch (\Exception $e) {
        Log::error('Exception saat upload bukti: ' . $e->getMessage());
        return null;
    }
}

    /**
     * Hapus file bukti penyerahan jika path ada.
     * Path yang diterima diasumsikan tanpa prefix 'public/' (mis. 'bukti_penyerahan/xxx.jpg')
     *
     * @param string|null $storedPath
     * @return void
     */
    protected function deleteBuktiPenyerahanFile(?string $storedPath): void
    {
        if (! $storedPath) return;

        // normalisasi: jika path disimpan dg prefix 'public/' -> hilangkan
        $storedPath = ltrim(preg_replace('#^public/#', '', $storedPath), '/');

        try {
            // gunakan disk 'public' secara eksplisit
            if (Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->delete($storedPath);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to delete bukti_penyerahan file', [
                'path' => $storedPath,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ---------------------------
    // Tampilkan semua data
    // ---------------------------
    public function index(Request $request)
{
    $user = auth()->user();

    // =========================
    // BASE QUERY
    // =========================
    $query = VoucherLama::with('histori');

    // =========================
    // FILTER USER (NON ADMIN)
    // =========================
    if (!$user->isAdminUser()) {
        $query->where('bimba_unit', $user->bimba_unit);
    }

    // =========================
    // FILTER NAMA MURID
    // =========================
    if ($request->filled('nama_murid')) {
        $query->where(function ($q) use ($request) {
            $q->where('nama_murid', $request->nama_murid)
              ->orWhere('nama_murid_baru', $request->nama_murid);
        });
    }

    // =========================
    // FILTER TANGGAL
    // =========================
    if ($request->filled('tanggal_dari')) {
        $query->whereDate('tanggal', '>=', $request->tanggal_dari);
    }

    if ($request->filled('tanggal_sampai')) {
        $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
    }

    // =========================
    // FILTER UNIT (ADMIN)
    // =========================
    if ($user->isAdminUser() && $request->filled('bimba_unit')) {
        $query->where('bimba_unit', $request->bimba_unit);
    }

    // =========================
    // GET DATA
    // =========================
    $vouchers = $query->latest()->get();

    // =========================
    // DATA FILTER DROPDOWN
    // =========================
    $namaMurid = VoucherLama::selectRaw("
            CONCAT(
                COALESCE(nim, '-'), ' | ', COALESCE(nama_murid, '-')
            ) as display
        ")
        ->distinct()
        ->pluck('display');

    $listBimbaUnit = VoucherLama::select('bimba_unit')
        ->distinct()
        ->pluck('bimba_unit');

    // =========================
    // SPIN RESULT (JANGAN ERROR)
    // =========================
    $spinResult = session('spinResult', null);

    // =========================
    // RETURN WAJIB ADA $vouchers
    // =========================
    return view('voucher.index', compact(
        'vouchers',
        'namaMurid',
        'listBimbaUnit',
        'spinResult'
    ));
}


    // ----------------------------------------------------------------------------- 
    // Form create
public function create()
{
    $user = Auth::user();

    $units = $user->isAdminUser() 
        ? \App\Models\Unit::orderBy('biMBA_unit')->get() 
        : collect();

    // 🔥 tambahan penting
    $tipeVoucher = [
        'regular' => 'Voucher Lama',
        'event' => 'Voucher Event',
        'lainnya' => 'Voucher Lainnya',
    ];

    return view('voucher.create', compact('units', 'tipeVoucher'));
}

    // ----------------------------------------------------------------------------- 
    // Simpan data manual (multiple no_voucher)
    // ----------------------------------------------------------------------------- 
// Simpan data manual (multiple no_voucher)
public function store(Request $request)
{
    $user = auth()->user();

    // Tentukan apakah voucher ini independent (event/lainnya) atau terikat ke murid (regular)
    $isIndependent = $request->tipe_voucher !== 'regular';

    $request->merge([
        'is_independent' => $isIndependent
    ]);

    $request->validate([
        'no_voucher' => 'required|array|min:1',
        'no_voucher.*' => 'required|string|distinct|unique:voucher_lama,no_voucher',

        'tanggal_penyerahan' => 'nullable|date',

        // Hanya wajib untuk REGULAR (Humas)
        'nim' => Rule::requiredIf(!$isIndependent),
        'nama_murid' => Rule::requiredIf(!$isIndependent),

        'orangtua' => 'nullable|string',
        'telp_hp' => 'nullable|string',

        // Untuk murid baru (regular)
        'nim_murid_baru' => 'nullable|string|unique:voucher_lama,nim_murid_baru',
        'nama_murid_baru' => 'nullable|string',

        'bukti_penyerahan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

        'bimba_unit' => $user->isAdminUser() 
            ? ['required', 'string', 'exists:units,bimba_unit'] 
            : 'nullable',

        'tipe_voucher' => 'required|in:regular,event,lainnya',
    ]);

    $voucherNumbers = $request->input('no_voucher', []);

    $tanggalPenyerahan = $request->filled('tanggal_penyerahan')
        ? Carbon::parse($request->tanggal_penyerahan)->toDateString()
        : null;

    $buktiPath = $this->storeBuktiPenyerahanFile($request->file('bukti_penyerahan'));

    $statusAuto = $this->statusFromTanggalPenyerahan($tanggalPenyerahan);

    // ================= UNIT & CABANG =================
    if ($user->isAdminUser()) {
        $bimbaUnit = $request->bimba_unit;
        $noCabang = \App\Models\Unit::where('biMBA_unit', $bimbaUnit)
                        ->value('no_cabang');
    } else {
        $bimbaUnit = $user->bimba_unit;
        $noCabang = $user->no_cabang ?? \App\Models\Unit::where('biMBA_unit', $user->bimba_unit)
                        ->value('no_cabang');

        if (!$bimbaUnit) {
            return back()->withErrors(['bimba_unit' => 'Unit Anda belum diatur.'])->withInput();
        }
    }

        // ================= LOOP CREATE VOUCHER =================
    foreach ($voucherNumbers as $noVoucher) {

        VoucherLama::create([
    'voucher'           => $noVoucher,
    'no_voucher'        => $noVoucher,
    'tanggal'           => now()->toDateString(),
    'tanggal_penyerahan'=> $tanggalPenyerahan,
    'status'            => $statusAuto,
    'jumlah_voucher'    => 1,

    // Tipe & Status
    'tipe_voucher'      => $request->tipe_voucher,
    'is_independent'    => $isIndependent,

    // Data Humas
    'nim' => $isIndependent ? null : $request->nim,
    'nama_murid' => $isIndependent ? null : $request->nama_murid,
    'orangtua' => $isIndependent ? null : $request->orangtua,
    'telp_hp' => $isIndependent ? null : $request->telp_hp,

    // Data Murid Baru
    'nim_murid_baru'      => $request->nim_murid_baru,
    'nama_murid_baru'     => $request->nama_murid_baru,
    'orangtua_murid_baru' => $request->orangtua_murid_baru,
    'telp_hp_murid_baru'  => $request->telp_hp_murid_baru,

    // Unit
    'bimba_unit' => $bimbaUnit,
    'no_cabang'  => $noCabang,

    'source' => 'manual',
    'bukti_penyerahan_path' => $buktiPath,
]);
    }

    return redirect()->route('voucher.index')
        ->with('success', count($voucherNumbers) . ' voucher berhasil ditambahkan.');
}

    // ----------------------------------------------------------------------------- 
    // Edit
    public function edit($id)
{
    $voucher = VoucherLama::findOrFail($id);
    $bukuInduk = BukuInduk::all();

    $units = auth()->user()->isAdminUser() 
        ? \App\Models\Unit::orderBy('biMBA_unit')->get() 
        : collect();

    return view('voucher.edit', compact('voucher', 'bukuInduk', 'units'));
}

    // ----------------------------------------------------------------------------- 
    // Update biasa / pemakaian
   public function update(Request $request, $id)
{
    $voucher = VoucherLama::findOrFail($id);

    $request->validate([
        'no_voucher' => [
    'required',
    'string',
    'max:255',
    Rule::unique('voucher_lama', 'no_voucher')->ignore($voucher->id)
],

        'tipe_voucher' => 'required|in:regular,event,lainnya',

        'tanggal' => 'nullable|date',
        'tanggal_penyerahan' => 'nullable|date',

        'status' => 'nullable|in:penyerahan,pemakaian,Digunakan,belum_diserahkan',
        'tanggal_pemakaian' => 'nullable|date|required_if:status,pemakaian',

        'nim' => 'nullable|string',
        'nama_murid' => 'nullable|string',

        'orangtua' => 'nullable|string|max:255',
        'telp_hp' => 'nullable|string|max:20',

        // ✅ PERBAIKAN UTAMA
        'nim_murid_baru' => 'nullable|string|max:255',
        'nama_murid_baru' => 'nullable|string|max:255',

        'jumlah_voucher' => 'required|integer|min:0',

        'bukti_penyerahan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

        'is_independent' => 'nullable|boolean',
    ]);

    // ================= FLAG =================
    $isIndependent = $request->boolean('is_independent');

    // ================= PEMAKAIAN =================
    $is_usage = (
        $request->input('status') === 'pemakaian' &&
        $voucher->status !== 'Digunakan' &&
        $voucher->jumlah_voucher > 0
    );

    if ($is_usage) {
        $voucher->decrement('jumlah_voucher', 1);
        $voucher->refresh();

        VoucherHistori::create([
            'voucher_lama_id' => $voucher->id,
            'voucher' => $voucher->voucher,
            'tanggal' => $voucher->tanggal,
            'tanggal_pemakaian' => $request->tanggal_pemakaian,
            'nim' => $voucher->nim,
            'nama_murid' => $voucher->nama_murid,
            'jumlah_voucher' => 1,
            'bukti_penggunaan_path' => $voucher->bukti_penyerahan_path ?? null,
        ]);

        if ($voucher->jumlah_voucher <= 0) {
            $voucher->update([
                'status' => 'Digunakan',
                'tanggal_pemakaian' => $request->tanggal_pemakaian,
            ]);
        } else {
            $voucher->update([
                'status' => $this->statusFromTanggalPenyerahan($voucher->tanggal_penyerahan),
                'tanggal_pemakaian' => null,
            ]);
        }

        return redirect()->route('voucher.index')
            ->with('success', 'Voucher digunakan 1x.');
    }

    // ================= UPDATE BIASA =================
    $tanggalPenyerahan = $request->filled('tanggal_penyerahan')
        ? $request->input('tanggal_penyerahan')
        : $voucher->tanggal_penyerahan;

    $tanggalPenyerahan = in_array($tanggalPenyerahan, ['', 'null', null]) ? null : $tanggalPenyerahan;

    if ((int)$request->jumlah_voucher <= 0) {
        $statusAuto = 'Digunakan';
        $tanggalPemakaian = $request->tanggal_pemakaian ?? now()->toDateString();
    } elseif ($request->has('tanggal_penyerahan')) {
        $statusAuto = $this->statusFromTanggalPenyerahan($tanggalPenyerahan);
        $tanggalPemakaian = $request->tanggal_pemakaian ?? $voucher->tanggal_pemakaian;
    } else {
        $statusAuto = $voucher->status;
        $tanggalPemakaian = $voucher->tanggal_pemakaian;
    }

    // File Bukti
    if ($request->hasFile('bukti_penyerahan')) {
        $this->deleteBuktiPenyerahanFile($voucher->bukti_penyerahan_path);
        $newPath = $this->storeBuktiPenyerahanFile($request->file('bukti_penyerahan'));
    } else {
        $newPath = $voucher->bukti_penyerahan_path;
    }

    // Update Data
    $voucher->update([
        'no_voucher' => $request->no_voucher,
        'tanggal'                => $request->input('tanggal', $voucher->tanggal),

        'status'                 => $statusAuto,
        'tanggal_pemakaian'      => $tanggalPemakaian,
        'tanggal_penyerahan'     => $tanggalPenyerahan,

        'tipe_voucher'           => $request->tipe_voucher,
        'is_independent'         => $isIndependent,

        'nim'                    => $isIndependent ? null : $request->nim,
        'nama_murid'             => $isIndependent ? null : $request->nama_murid,

        'orangtua'               => $request->orangtua,
        'telp_hp'                => $request->telp_hp,

        'nim_murid_baru'         => $request->nim_murid_baru,
        'nama_murid_baru'        => $request->nama_murid_baru,
        'orangtua_murid_baru'    => $request->orangtua_murid_baru,
        'telp_hp_murid_baru'     => $request->telp_hp_murid_baru,

        'jumlah_voucher'         => $request->jumlah_voucher,
        'bukti_penyerahan_path'  => $newPath,
    ]);

    return redirect()->route('voucher.index')
        ->with('success', 'Voucher berhasil diperbarui!');
}

    // ----------------------------------------------------------------------------- 
    // Hapus
    public function destroy($id)
    {
        $voucher = VoucherLama::findOrFail($id);

        // hapus file bukti jika ada
        $this->deleteBuktiPenyerahanFile($voucher->bukti_penyerahan_path);

        $voucher->delete();

        return redirect()->route('voucher.index')->with('success', 'Data voucher berhasil dihapus.');
    }

    // ----------------------------------------------------------------------------- 
    // getBukuInduk (tetap seperti kamu punya)
    public function getBukuInduk($nim)
    {
        Log::info('getBukuInd called', ['nim' => $nim]);

        $biCols = Schema::getColumnListing('buku_induk');
        $bi_has_no_telp_hp = in_array('no_telp_hp', $biCols, true);
        $bi_has_no_telp = in_array('no_telp', $biCols, true);

        $biSelect = ['nim', DB::raw('TRIM(nama) as nama_murid'), 'orangtua'];
        if ($bi_has_no_telp_hp) $biSelect[] = 'no_telp_hp';
        if ($bi_has_no_telp) $biSelect[] = 'no_telp';

        $data = BukuInduk::where('nim', $nim)->select($biSelect)->first();
        if (!$data) {
            $nimStripped = ltrim($nim, '0');
            if ($nimStripped !== $nim) {
                $data = BukuInduk::where('nim', $nimStripped)->select($biSelect)->first();
            }
        }
        if (!$data) {
            $data = BukuInduk::where('nim', 'like', "%{$nim}%")->select($biSelect)->first();
        }
        if (!$data) {
            Log::warning('buku_induk not found', ['nim' => $nim]);
            return response()->json(null, 404);
        }

        // normalisasi nama humas
        $humasName = (string) $data->nama_murid;
        if (strpos($humasName, ' - ') !== false) {
            $parts = explode(' - ', $humasName, 2);
            $humasName = trim($parts[1] ?? $parts[0]);
        }
        $humasLower = mb_strtolower(trim($humasName));

        // students select build
        $cols = Schema::getColumnListing('students');
        $has_no_telp = in_array('no_telp', $cols, true);
        $has_hp_ayah = in_array('hp_ayah', $cols, true);
        $has_hp_ibu = in_array('hp_ibu', $cols, true);
        $has_telp_hp = in_array('telp_hp', $cols, true);
        $has_no_telp_hp = in_array('no_telp_hp', $cols, true);
        $has_orangtua = in_array('orangtua', $cols, true);
        $has_parent_name = in_array('parent_name', $cols, true);
        $has_informasi_humas = in_array('informasi_humas_nama', $cols, true);

        $selects = ['id', 'nim', DB::raw('TRIM(nama) as nama')];

        if ($has_no_telp) {
            $selects[] = DB::raw("COALESCE(no_telp, " .
                                 ($has_hp_ayah ? "hp_ayah, " : "") .
                                 ($has_hp_ibu ? "hp_ibu, " : "") .
                                 ($has_no_telp_hp ? "no_telp_hp, " : "") .
                                 ($has_telp_hp ? "telp_hp, " : "") .
                                 "'' ) as telp_hp");
        } else {
            $fragments = [];
            if ($has_hp_ayah) $fragments[] = 'hp_ayah';
            if ($has_hp_ibu)  $fragments[] = 'hp_ibu';
            if ($has_no_telp_hp) $fragments[] = 'no_telp_hp';
            if ($has_telp_hp) $fragments[] = 'telp_hp';
            $coalesce = $fragments ? implode(', ', $fragments) . ", ''" : "''";
            $selects[] = DB::raw("COALESCE({$coalesce}) as telp_hp");
        }

        if ($has_orangtua) $selects[] = DB::raw("COALESCE(orangtua,'') as orangtua");
        elseif ($has_parent_name) $selects[] = DB::raw("COALESCE(parent_name,'') as orangtua");
        else $selects[] = DB::raw("'' as orangtua");

        if (! $has_informasi_humas) {
            $bi_phone = $data->no_telp_hp ?? ($data->no_telp ?? '');
            return response()->json([
                'nim' => $data->nim,
                'nama_murid' => $data->nama_murid,
                'orangtua' => $data->orangtua ?? '',
                'no_telp_hp' => $bi_phone,
                'telp_hp' => $bi_phone,
                'suggested_new_student' => null,
                'candidate_list' => [],
                'last_voucher_amount' => null,
                'voucher_count' => null,
                'suggested_voucher_numbers' => null,
            ]);
        }

        // kandidat search
        $candidate = Student::query()
            ->whereRaw('LOWER(TRIM(informasi_humas_nama)) = ?', [$humasLower])
            ->select($selects)
            ->orderByRaw("CASE WHEN COALESCE(TRIM(telp_hp),'') = '' THEN 1 ELSE 0 END ASC")
            ->orderBy('id','asc')
            ->first();

        $is_candidate_used = false;
        if ($candidate) {
            $is_candidate_used = VoucherLama::where('nim_murid_baru', $candidate->nim)
                                            ->orWhereRaw('LOWER(TRIM(nama_murid_baru)) = ?', [mb_strtolower(trim($candidate->nama))])
                                            ->exists();
            if ($is_candidate_used) $candidate = null;
        }

        $candidate_list = [];
        if (!$candidate) {
            $matches = Student::query()
                ->whereRaw('LOWER(informasi_humas_nama) LIKE ?', ['%'.$humasLower.'%'])
                ->select($selects)
                ->orderByRaw("CASE WHEN COALESCE(TRIM(telp_hp),'') = '' THEN 1 ELSE 0 END ASC")
                ->orderBy('id','asc')
                ->limit(10)
                ->get();

            if ($matches->count()) {
                $candidate_list_raw = $matches->map(fn($r) => [
                    'student_id' => $r->id,
                    'nim' => $r->nim,
                    'nama' => $r->nama,
                    'orangtua' => $r->orangtua,
                    'telp_hp' => $r->telp_hp,
                ])->toArray();

                foreach ($candidate_list_raw as $c) {
                    $usedInVoucher = VoucherLama::where(function($q) use ($c) {
                        $q->where('nim_murid_baru', $c['nim'])
                          ->orWhereRaw('LOWER(TRIM(nama_murid_baru)) = ?', [mb_strtolower(trim($c['nama']))]);
                    })->exists();

                    $usedInWheel = \App\Models\WheelWinner::where('student_id', $c['student_id'])->exists();

                    if (! $usedInVoucher && ! $usedInWheel) {
                        $candidate_list[] = $c;
                    } else {
                        Log::info('candidate filtered out (already used)', ['student' => $c, 'usedInVoucher' => $usedInVoucher, 'usedInWheel' => $usedInWheel]);
                    }
                }

                if (count($candidate_list) > 0) {
                    $candidate = Student::find($candidate_list[0]['student_id']);
                } else {
                    $candidate = null;
                }
            }
        }

        $rowHash = md5(($candidate->id ?? '') . '|' . mb_strtolower($humasName));
        $lastWinner = null;
        if (!empty($rowHash)) {
            $lastWinner = \App\Models\WheelWinner::where('row_hash', $rowHash)->latest('won_at')->first();
        }
        if (!$lastWinner && !empty($humasName)) {
            $lastWinner = \App\Models\WheelWinner::whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($humasName).'%'])
                ->latest('won_at')->first();
        }

        $last_voucher_amount = null;
        $voucher_count = null;
        $suggested_voucher_numbers = null;

        if ($lastWinner && !empty($lastWinner->voucher_amount)) {
            $last_voucher_amount = (int) $lastWinner->voucher_amount;
            $voucher_count = max(1, (int) round($last_voucher_amount / 50000));
            $suggested_voucher_numbers = $this->generateVoucherNumbers($voucher_count, 'WHEEL');
        }

        $bi_phone = $data->no_telp_hp ?? ($data->no_telp ?? '');

        $suggestedStudent = null;
        if ($candidate) {
            $suggestedStudent = [
                'student_id' => $candidate->id,
                'nim' => $candidate->nim,
                'nama' => $candidate->nama,
                'orangtua' => $candidate->orangtua,
                'telp_hp' => $candidate->telp_hp ?? null,
                'no_telp_hp' => $candidate->telp_hp ?? null,
            ];
        }

        $response = [
            'nim' => $data->nim,
            'nama_murid' => $data->nama_murid,
            'orangtua' => $data->orangtua ?? '',
            'no_telp_hp' => $bi_phone,
            'telp_hp' => $bi_phone,
            'suggested_new_student' => $suggestedStudent,
            'candidate_list' => $candidate_list,
            'last_voucher_amount' => $last_voucher_amount,
            'voucher_count' => $voucher_count,
            'suggested_voucher_numbers' => $suggested_voucher_numbers,
        ];

        Log::info('getBukuInd response', ['nim' => $nim, 'resp_sample' => [
            'suggested_new_student' => $suggestedStudent ? $suggestedStudent['nim'] : null,
            'candidate_count' => count($candidate_list),
            'last_voucher_amount' => $last_voucher_amount
        ]]);

        return response()->json($response);
    }

    // ----------------------------------------------------------------------------- 
    // generate voucher numbers
    protected function generateVoucherNumbers(int $count, string $prefix = null): array
    {
        $out = [];
        $prefix = $prefix ?? ('VCHR-' . date('Ymd'));
        for ($i = 0; $i < $count; $i++) {
            $rand = strtoupper(\Illuminate\Support\Str::random(6));
            $out[] = $prefix . '-' . $rand . '-' . str_pad($i+1, 3, '0', STR_PAD_LEFT);
        }
        return $out;
    }

    // ----------------------------------------------------------------------------- 
    // histori
    public function histori($id)
    {
        $voucher = VoucherLama::findOrFail($id);
        $histori = VoucherHistori::where('voucher_lama_id', $id)->get();

        return view('voucher.histori', compact('voucher', 'histori'));
    }

    // ----------------------------------------------------------------------------- 
    // import
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new VoucherLamaImport, $request->file('file'));
        return back()->with('success', 'Data voucher lama berhasil diimpor!');
    }

    // ----------------------------------------------------------------------------- 
    // storeFromSpin (pastikan tanggal_penyerahan = NULL dan status = 'belum_diserahkan')
    public function storeFromSpin(Request $request)
{
    $validated = $request->validate([
        'nominal_spin'          => 'required|integer|min:50000',
        'nim_humas'             => 'required|string|exists:buku_induk,nim',
        'tanggal_spin'          => 'nullable|date',
        'nim_murid_baru'        => 'nullable|string',
        'nama_murid_baru'       => 'nullable|string',
        'orangtua_murid_baru'   => 'nullable|string',
        'telp_hp_murid_baru'    => 'nullable|string',
        'bimba_unit'            => 'required|string|max:100',      // BARU
        'no_cabang'             => 'required|string|max:50',       // BARU
        'bukti_penyerahan'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ]);

    $nominal            = (int) $validated['nominal_spin'];
    $nilaiPerVoucher    = 50000;
    $tanggalSpinRaw     = $validated['tanggal_spin'] ?? null;
    $tanggalSpin        = ($tanggalSpinRaw === '' || $tanggalSpinRaw === 'null' || $tanggalSpinRaw === 'undefined')
                            ? null : ($tanggalSpinRaw ? Carbon::parse($tanggalSpinRaw)->toDateString() : null);

    $nimHumas           = $validated['nim_humas'];
    $bimbaUnit          = $validated['bimba_unit'];      // BARU
    $noCabang           = $validated['no_cabang'];       // BARU

    $jumlah_voucher     = max(1, intdiv($nominal, $nilaiPerVoucher));
    $voucherCodes       = $this->generateVoucherNumbers($jumlah_voucher, 'SPIN-' . date('Ymd'));

    // simpan file bukti (jika ada)
    $uploaded   = $request->file('bukti_penyerahan');
    $buktiPath  = $this->storeBuktiPenyerahanFile($uploaded);

    DB::beginTransaction();
    try {
        $created = [];
        foreach ($voucherCodes as $code) {
            $bi = BukuInduk::where('nim', $nimHumas)->first();

            $v = VoucherLama::create([
                'voucher'               => $code,
                'no_voucher'            => null,
                'jumlah_voucher'        => 1,
                'nominal'               => $nilaiPerVoucher,
                'tanggal'               => $tanggalSpin ?: null,
                'tanggal_penyerahan'    => null,
                'status'                => 'belum_diserahkan',
                'nim'                   => $nimHumas,
                'nama_murid'            => $bi?->nama ? trim($bi->nama) : null,
                'orangtua'              => $bi?->orangtua ?? null,
                'telp_hp'               => $bi?->no_telp_hp ?? $bi?->no_telp ?? null,
                'nim_murid_baru'        => $validated['nim_murid_baru'] ?? null,
                'nama_murid_baru'       => $validated['nama_murid_baru'] ?? null,
                'orangtua_murid_baru'   => $validated['orangtua_murid_baru'] ?? null,
                'telp_hp_murid_baru'    => $validated['telp_hp_murid_baru'] ?? null,
                'source'                => 'spin',
                'bukti_penyerahan_path' => $buktiPath,

                // <<< BARIS BARU >>>
                'bimba_unit'            => $bimbaUnit,
                'no_cabang'             => $noCabang,
            ]);

            $created[] = $v;
        }

        DB::commit();

        \Session::flash('spinResult', [
            'count'     => count($created),
            'nominal'   => $nominal,
            'nominal_formatted' => number_format($nominal, 0, ',', '.'),
            'rows'      => collect($created)->map(function ($r) {
                return [
                    'voucher'            => $r->voucher,
                    'nominal'            => $r->nominal,
                    'tanggal_spin'       => $r->tanggal ? Carbon::parse($r->tanggal)->format('d-m-Y') : null,
                    'tanggal_penyerahan' => null,
                    'status'             => $r->status,
                    'nim'                => $r->nim,
                    'nama_murid'         => $r->nama_murid,
                    'nim_murid_baru'     => $r->nim_murid_baru,
                    'nama_murid_baru'    => $r->nama_murid_baru,
                    'orangtua_murid_baru'=> $r->orangtua_murid_baru,
                    'telp_hp_murid_baru' => $r->telp_hp_murid_baru,
                    'bukti_penyerahan_path' => $r->bukti_penyerahan_path,
                    'bimba_unit'         => $r->bimba_unit,     // agar muncul di flash message (opsional)
                    'no_cabang'          => $r->no_cabang,      // agar muncul di flash message (opsional)
                ];
            })->toArray(),
        ]);

        return redirect()->route('voucher.index')
            ->with('success', "Berhasil membuat " . count($created) . " voucher spin.");
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('storeFromSpin error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return back()->withErrors(['error' => 'Gagal menyimpan hasil spin.']);
    }
}

  public function updateInline(Request $request, $id)
{
    try {
        $field = $request->input('field');
        $rawValue = $request->input('value');

        // DEBUG (sementara)
        Log::info('updateInline called', [
            'id' => $id,
            'field_received' => $field,
            'value_received' => $rawValue,
            'all_input' => $request->all()
        ]);

        $allowed = ['no_voucher', 'tanggal_penyerahan'];

        if (!in_array($field, $allowed, true)) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Field tidak diizinkan: ' . $field
            ], 422);
        }

        $voucher = VoucherLama::findOrFail($id);

        $normalized = ($rawValue === '' || $rawValue === 'null' || $rawValue === null) 
                        ? null : trim($rawValue);

        // Validasi
        $validatorData = ['value' => $normalized];
        $rules = [];

        if ($field === 'no_voucher') {
            $rules['value'] = ['nullable', 'string', 'max:255', 
                Rule::unique('voucher_lama', 'no_voucher')->ignore($voucher->id)];
        } else {
            $rules['value'] = ['nullable', 'date'];
        }

        $validator = Validator::make($validatorData, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'message' => $validator->errors()->first('value')
            ], 422);
        }

        // Update Logic
        if ($field === 'no_voucher') {
            $voucher->no_voucher = $normalized;
        } else {
            $tanggal = $normalized ? Carbon::parse($normalized)->toDateString() : null;
            $voucher->tanggal_penyerahan = $tanggal;
            $voucher->status = $this->statusFromTanggalPenyerahan($tanggal);
        }

        if ($voucher->jumlah_voucher <= 0) {
            $voucher->status = 'Digunakan';
        }

        $voucher->save();
        $voucher->refresh();

        $formatted = $voucher->tanggal_penyerahan 
            ? Carbon::parse($voucher->tanggal_penyerahan)->format('d-m-Y') 
            : 'Belum diisi';

        return response()->json([
            'status'  => 'ok',
            'message' => 'Perubahan berhasil disimpan',
            'data'    => [
                'id'                          => $voucher->id,
                'tanggal_penyerahan'          => $voucher->tanggal_penyerahan,
                'tanggal_penyerahan_formatted'=> $formatted,
                'status'                      => $voucher->status,
            ],
        ]);

    } catch (\Throwable $e) {
        Log::error('updateInline Error', [
            'id' => $id,
            'field' => $request->input('field'),
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => 'Terjadi kesalahan sistem.'
        ], 500);
    }
}

    // ----------------------------------------------------------------------------- 
    // helper status
    protected function statusFromTanggalPenyerahan($tanggalPenyerahan)
    {
        return $tanggalPenyerahan ? 'penyerahan' : 'belum_diserahkan';
    }

    // ----------------------------------------------------------------------------- 
    public static function syncForStudent(\App\Models\Student $student): array
    {
        try {
            $nimKey = (string) $student->nim;
            $nimNumeric = ltrim($nimKey, '0');

            $telpRaw = null;
            if (!empty($student->hp_ayah)) $telpRaw = $student->hp_ayah;
            elseif (!empty($student->hp_ibu)) $telpRaw = $student->hp_ibu;
            elseif (!empty($student->no_telp)) $telpRaw = $student->no_telp;
            elseif (!empty($student->telp_hp)) $telpRaw = $student->telp_hp;
            else {
                $bi = \App\Models\BukuInduk::where('nim', $student->nim)->first();
                $telpRaw = $bi?->no_telp_hp ?? $bi?->no_telp ?? null;
            }

            $telpVal = $telpRaw ? preg_replace('/\D+/', '', (string)$telpRaw) : null;
            if ($telpVal === '') $telpVal = null;

            $updateForNewStudent = [];
            if (!empty($student->nama)) $updateForNewStudent['nama_murid_baru'] = $student->nama;
            if (!empty($student->orangtua)) $updateForNewStudent['orangtua_murid_baru'] = $student->orangtua;
            if (!empty($telpVal)) $updateForNewStudent['telp_hp_murid_baru'] = $telpVal;

            $updateForHumas = [];
            if (!empty($student->nama)) $updateForHumas['nama_murid'] = $student->nama;
            if (!empty($student->orangtua)) $updateForHumas['orangtua'] = $student->orangtua;
            if (!empty($telpVal)) $updateForHumas['telp_hp'] = $telpVal;

            $counts = ['murid_baru' => 0, 'humas' => 0, 'fallback' => 0];

            if (!empty($updateForNewStudent)) {
                $q = self::where(function($q) use ($nimKey, $nimNumeric) {
                    $q->where('nim_murid_baru', $nimKey)
                      ->orWhereRaw("TRIM(LEADING '0' FROM COALESCE(nim_murid_baru,'')) = ?", [$nimNumeric]);
                });
                $counts['murid_baru'] = $q->update($updateForNewStudent);
            }

            if (!empty($updateForHumas)) {
                $q2 = self::where(function($q) use ($nimKey, $nimNumeric) {
                    $q->where('nim', $nimKey)
                      ->orWhereRaw("TRIM(LEADING '0' FROM COALESCE(nim,'')) = ?", [$nimNumeric]);
                });
                $counts['humas'] = $q2->update($updateForHumas);
            }

            if (!empty($updateForHumas) && !empty($student->nama)) {
                $nameNormalized = mb_strtolower(trim($student->nama));
                $q3 = self::where(function($q) {
                        $q->whereNull('nim')->orWhere('nim', '');
                    })
                    ->whereRaw('LOWER(TRIM(COALESCE(nama_murid, \'\'))) = ?', [$nameNormalized]);

                $counts['fallback'] = $q3->update($updateForHumas);
            }

            \Log::info('VoucherLama::syncForStudent completed', [
                'nim' => $nimKey,
                'telp_used' => $telpVal,
                'counts' => $counts,
            ]);

            return $counts;
        } catch (\Throwable $e) {
            \Log::error('VoucherLama::syncForStudent error: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'exception' => $e,
            ]);
            return ['murid_baru' => 0, 'humas' => 0, 'fallback' => 0];
        }
    }

    // ----------------------------------------------------------------------------- 
    public function uploadBuktiByNim(Request $request)
{
    try {
        $validated = $request->validate([
            'no_voucher'         => 'nullable|string',
            'nim_murid_baru'     => 'nullable|string',
            'tanggal_penyerahan' => 'nullable|date',
            'bukti_penyerahan'   => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if (empty($request->no_voucher) && empty($request->nim_murid_baru)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No Voucher atau NIM Murid Baru harus diisi.'
            ], 422);
        }

        $newPath = $this->storeBuktiPenyerahanFile($request->file('bukti_penyerahan'));
        if (!$newPath) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan file.'], 500);
        }

        $tanggalPenyerahan = $request->filled('tanggal_penyerahan') 
            ? Carbon::parse($request->tanggal_penyerahan)->toDateString() 
            : null;

        $query = VoucherLama::query();

        if ($request->filled('no_voucher')) {
            $query->where('no_voucher', $request->no_voucher);   // ← Hanya 1 voucher
        } elseif ($request->filled('nim_murid_baru')) {
            $nim = trim($request->nim_murid_baru);
            $query->where('nim_murid_baru', $nim);
        }

        $voucher = $query->first();   // ← Ambil hanya 1 (first), bukan get()

        if (!$voucher) {
            $this->deleteBuktiPenyerahanFile($newPath);
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher tidak ditemukan.'
            ], 404);
        }

        // Hapus bukti lama jika ada
        $this->deleteBuktiPenyerahanFile($voucher->bukti_penyerahan_path);

        $voucher->update([
            'bukti_penyerahan_path' => $newPath,
            'tanggal_penyerahan'    => $tanggalPenyerahan,
            'status'                => $this->statusFromTanggalPenyerahan($tanggalPenyerahan),
        ]);

        return response()->json([
            'status'  => 'ok',
            'message' => 'Bukti berhasil diunggah.',
            'bukti_path' => $newPath,
        ]);

    } catch (\Throwable $e) {
        Log::error('uploadBuktiByNim Error: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat mengunggah.'
        ], 500);
    }
}

    public function storeHistori(Request $request, $id)
    {
        $voucher = VoucherLama::findOrFail($id);

        $data = $request->validate([
            'tanggal' => 'nullable|date',
            'tanggal_pemakaian' => 'nullable|date',
            'nim' => 'nullable|string',
            'nama_murid' => 'nullable|string',
            'jumlah_voucher' => 'nullable|integer|min:1',
            'bukti_penyerahan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'confirm_use' => 'nullable|boolean',
        ]);

        $buktiPath = $this->storeBuktiPenyerahanFile($request->file('bukti_penyerahan'));

        DB::beginTransaction();
        try {
            $hist = VoucherHistori::create([
                'voucher_lama_id' => $voucher->id,
                'voucher' => $voucher->voucher,
                'tanggal' => $data['tanggal'] ?? $voucher->tanggal,
                'tanggal_pemakaian' => $data['tanggal_pemakaian'] ?? null,
                'nim' => $data['nim'] ?? $voucher->nim,
                'nama_murid' => $data['nama_murid'] ?? $voucher->nama_murid,
                'jumlah_voucher' => $data['jumlah_voucher'] ?? 1,
                'bukti_penggunaan_path' => $buktiPath,
            ]);

            if (!empty($hist->tanggal_pemakaian) && $request->filled('confirm_use')) {
                $dec = (int)($hist->jumlah_voucher ?? 1);
                $voucher->decrement('jumlah_voucher', $dec);
                if ($voucher->jumlah_voucher <= 0) {
                    $voucher->update(['status' => 'Digunakan', 'tanggal_pemakaian' => $hist->tanggal_pemakaian]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Histori disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($buktiPath) $this->deleteBuktiPenyerahanFile($buktiPath);
            Log::error('storeHistori error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Gagal menyimpan histori.']);
        }
    }

    public function uploadHistoriBukti(Request $request, $id)
    {
        $hist = \App\Models\VoucherHistori::findOrFail($id);

        $validated = $request->validate([
            'bukti_penyerahan' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $file = $request->file('bukti_penyerahan');
        $newPath = $this->storeBuktiPenyerahanFile($file);
        if (! $newPath) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan file.'], 500);
        }

        DB::beginTransaction();
        try {
            // hapus file lama histori jika ada
            $this->deleteBuktiPenyerahanFile($hist->bukti_penggunaan_path);

            $hist->update([
                'bukti_penggunaan_path' => $newPath,
            ]);

            DB::commit();

            $clean = \Illuminate\Support\Str::startsWith($newPath, 'public/') ? preg_replace('#^public/#','',$newPath) : $newPath;
            $url = asset('storage/' . ltrim($clean, '/'));
            $ext = strtolower(pathinfo($clean, PATHINFO_EXTENSION));

            return response()->json([
                'status' => 'ok',
                'message' => 'Bukti berhasil diunggah.',
                'bukti_penggunaan_path' => $newPath,
                'url' => $url,
                'ext' => $ext,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->deleteBuktiPenyerahanFile($newPath);
            Log::error('uploadHistoriBukti error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan bukti.'], 500);
        }
    }
    public function export(Request $request)
{
    $filters = $request->only(['nama_murid', 'tanggal_dari', 'tanggal_sampai']);

    return Excel::download(
        new VoucherLamaExport($filters),
        'Daftar_Voucher_' . now()->format('Y-m-d_His') . '.xlsx'
    );
}
public function exportPdf(Request $request)
{
    $query = VoucherLama::query();

    if ($request->filled('nama_murid')) {
        $search = trim($request->nama_murid);
        $parts = explode(' | ', $search, 2);

        $nimSearch  = trim($parts[0] ?? '');
        $namaSearch = trim($parts[1] ?? $search);

        $query->where(function ($q) use ($nimSearch, $namaSearch) {
            if ($nimSearch !== '') {
                $q->where('nim', $nimSearch);
            }
            if ($namaSearch !== '') {
                $q->orWhere('nama_murid', 'like', "%{$namaSearch}%");
            }
        });
    }

    if ($request->filled('tanggal_dari')) {
        $query->whereDate('tanggal', '>=', $request->tanggal_dari);
    }

    if ($request->filled('tanggal_sampai')) {
        $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
    }

    if ($request->filled('bimba_unit')) {
        $query->where('bimba_unit', $request->bimba_unit);
    }

    $vouchers = $query->orderBy('id','desc')->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('voucher.pdf', compact('vouchers'))
            ->setPaper('a4', 'landscape');

    return $pdf->stream('Daftar_Voucher_' . now()->format('Ymd_His') . '.pdf');
}
public function printVoucher($id)
{
    $voucher = VoucherLama::findOrFail($id);  // ← WAJIB ini! Ambil 1 voucher berdasarkan ID

   $pdf = Pdf::loadView('voucher.print-voucher', compact('voucher'))
          ->setPaper([0, 0, 878.74, 283.46], 'landscape')
          ->setWarnings(false);

    return $pdf->stream('voucher-' . ($voucher->no_voucher ?? $voucher->voucher ?? $id) . '.pdf');
}

public function getMuridByUnit(Request $request)
{
    try {
        $unit = $request->query('bimba_unit');
        Log::info('AJAX getMuridByUnit dipanggil', [
            'unit' => $unit,
            'user_id' => auth()->id() ?? 'guest',
            'ip' => $request->ip()
        ]);

        if (!$unit) {
            Log::info('Unit kosong, return []');
            return response()->json([]);
        }

        // Query minimal dulu untuk cek apakah tabel bisa diakses
        $murid = BukuInduk::where('bimba_unit', $unit)
            ->select('nim', 'nama', 'orangtua', 'no_telp_hp')
            ->orderBy('nama')
            ->get();

        Log::info('Query berhasil', [
            'count' => $murid->count(),
            'first_row' => $murid->first() ? $murid->first()->toArray() : 'kosong'
        ]);

        $formatted = $murid->map(function ($item) {
            $nim = (string) ($item->nim ?? ''); // paksa string
            $nimPadded = str_pad($nim, 4, '0', STR_PAD_LEFT);

            $nama = trim((string) ($item->nama ?? 'Tidak ada nama'));
            $orangtua = trim((string) ($item->orangtua ?? ''));
            $telp = trim((string) ($item->no_telp_hp ?? ''));

            return [
                'id' => $nimPadded,
                'text' => $nimPadded . ' - ' . $nama,
                'nama' => $nama,
                'orangtua' => $orangtua,
                'telp_hp' => $telp,
            ];
        })->toArray();

        Log::info('Formatted data siap dikirim', ['count_formatted' => count($formatted)]);

        return response()->json($formatted);
    } catch (\Throwable $e) {
        Log::error('ERROR KRITIS di getMuridByUnit', [
            'unit' => $request->query('bimba_unit'),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
        ]);

        return response()->json([
            'error' => $e->getMessage(),
            'details' => 'Cek log server (laravel.log) untuk detail lengkap'
        ], 500);
    }
}
}
