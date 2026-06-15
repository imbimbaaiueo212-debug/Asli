<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Profile;
use App\Models\BukuInduk;
use App\Models\PenyesuaianRbGuru;
use App\Models\Ktr;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Models\PenyesuaianKtr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProfileExport;
use App\Imports\ProfileImport;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    private $jabatanOptions = [
        'Kepala Unit', 'Asisten KU', 'Guru', 'Asisten Guru',
        'Staff Mobile', 'Admin', 'Bendahara', 'Satpam',
        'Office Boy', 'Office Girl'
    ];

    private $statusOptions = ['Aktif', 'Magang', 'Non-Aktif', 'Resign', 'Non-Aktif Sementara'];
    private $departemenOptions = ['biMBA-AIUEO', 'English biMBA', 'biMBA + English', 'Part Time English'];

    // ===================================================================
    // INDEX – Tampilkan daftar + hitung otomatis
    // ===================================================================

    public function index(Request $request)
{
    $search     = $request->filled('search') ? $request->input('search') : null;
    $unitFilter = $request->filled('unit') ? $request->input('unit') : null;

    $baseQuery = Profile::whereIn('jabatan', $this->jabatanOptions);

    // Dropdown options
    $unitOptions = (clone $baseQuery)
        ->whereNotNull('biMBA_unit')
        ->distinct()
        ->orderBy('biMBA_unit')
        ->pluck('biMBA_unit')
        ->values();

    $profileOptionsQuery = (clone $baseQuery)
        ->select('id', 'nik', 'nama', 'biMBA_unit')
        ->orderBy('nik');

    if ($unitFilter) {
        $profileOptionsQuery->where('biMBA_unit', $unitFilter);
    }

    $profileOptions = $profileOptionsQuery->get();

    // QUERY UTAMA
    $query = (clone $baseQuery)
        ->with(['bukuIndukMba']); // optional, untuk optimasi

    if ($unitFilter) $query->where('biMBA_unit', $unitFilter);
    if ($search) $query->where('id', $search);

    $profiles = $query->orderBy('nik')->get();

    // 🔥 REKALKULASI OTOMATIS
foreach ($profiles as $profile) {
    if (in_array($profile->jabatan, ['Guru', 'Kepala Unit'])) {
        $this->recalculateMuridDanKtr($profile);
    }
}

// Refresh ulang dari database
$profiles = Profile::whereIn('id', $profiles->pluck('id'))
    ->with(['bukuIndukMba'])
    ->orderBy('nik')
    ->get();

    // Refresh collection setelah recalculate
    $profiles = $profiles->fresh();   // atau reload dari DB

    // Opsi RB & KTR
    $rbOptions = Ktr::where('waktu', 'like', 'RB%')
        ->orderByRaw("CAST(SUBSTRING(waktu, 3) AS UNSIGNED)")
        ->pluck('waktu')
        ->unique()
        ->values();

    $ktrOptions = Ktr::where('kategori', 'like', 'KTR%')
        ->orderByRaw("CAST(SUBSTRING(kategori, 5, INSTR(SUBSTRING(kategori, 5), ' ') - 1) AS UNSIGNED), SUBSTRING(kategori, INSTR(kategori, ' ') + 1)")
        ->pluck('kategori')
        ->unique()
        ->values();

    $mentors = Profile::whereIn('jabatan', ['Guru', 'Kepala Unit'])
        ->orderBy('nama')
        ->get(['nama']);

    return view('profiles.index', compact(
        'profiles',
        'profileOptions',
        'unitOptions',
        'mentors',
        'rbOptions',
        'ktrOptions',
        'search',
        'unitFilter'
    ));
}
    // ===================================================================
    // CREATE & STORE
    // ===================================================================
public function create()
{
    $unitCollection = Unit::orderBy('no_cabang')->get();

    $units = $unitCollection->mapWithKeys(function ($unit) {
        $label = $unit->no_cabang
            ? $unit->no_cabang . ' - ' . $unit->biMBA_unit
            : $unit->biMBA_unit;
        return [$unit->biMBA_unit => $label];
    })->sort();

    $unitNoCabang = $unitCollection->pluck('no_cabang', 'biMBA_unit')->toArray();

    return view('profiles.create', [
        'jabatanOptions'    => $this->jabatanOptions,
        'statusOptions'     => $this->statusOptions,
        'departemenOptions' => $this->departemenOptions,
        'units'             => $units,
        'unitNoCabang'      => $unitNoCabang,
    ]);
}

   public function store(Request $request)
{
    $validated = $this->validateProfileRequest($request);
    //dd($validated);  // ← hapus atau comment ini setelah debug selesai
    $validated = array_map(fn($v) => $v === '' ? null : $v, $validated);

    // 🔥 Mapping otomatis
    $status = $validated['status_karyawan'] ?? null;

    if ($status === 'Magang' && !empty($validated['tgl_masuk'])) {
        $validated['tgl_magang'] = $validated['tgl_masuk'];
        $validated['tgl_masuk'] = null;
    }

    $this->autoActivateMagangInArray($validated);

    if (($validated['jabatan'] ?? null) === 'Guru') {
        $this->calculateGuruCounts($validated);
    }

    $this->assignKtrAndRp($validated);

    $validated['rp'] = $validated['rp'] ? floatval($validated['rp']) : null;

    $profile = Profile::create($validated);

    $this->calculateAndSaveMasaKerja($profile);

    return redirect()->route('profiles.index')
        ->with('success', 'Profile berhasil ditambahkan');
}
    // ===================================================================
    // SHOW & EDIT
    // ===================================================================
    public function show(Profile $profile)
    {
        return view('profiles.show', compact('profile'));
    }

    // Di dalam method edit()
    public function edit(Profile $profile)
{
    // Hanya hitung masa kerja, JANGAN recalculate RB & KTR di halaman edit
    $this->calculateAndSaveMasaKerja($profile);
    $this->calculateAndSaveMasaKerjaJabatan($profile);

    // Ambil jabatan dari tabel Skim
    $jabatanOptions = \App\Models\Skim::query()
        ->distinct()
        ->orderBy('jabatan')
        ->pluck('jabatan', 'jabatan')
        ->toArray();

    $statusOptions     = $this->statusOptions;
    $departemenOptions = $this->departemenOptions;

    // Unit
    $unitCollection = Unit::orderBy('no_cabang')->get();

    $units = $unitCollection->mapWithKeys(function ($unit) {
        $label = $unit->no_cabang
            ? $unit->no_cabang . ' - ' . $unit->biMBA_unit
            : $unit->biMBA_unit;
        return [$unit->biMBA_unit => $label];
    })->sort();

    $unitNoCabang = $unitCollection->pluck('no_cabang', 'biMBA_unit')->toArray();

    // RB & KTR Options
    $rbOptions = \App\Models\Ktr::where('waktu', 'like', 'RB%')
        ->orderByRaw("CAST(SUBSTRING(waktu, 3) AS UNSIGNED)")
        ->pluck('waktu')
        ->unique()
        ->values();

    $ktrOptions = \App\Models\Ktr::where('kategori', 'like', 'KTR%')
        ->orderByRaw("
            CAST(SUBSTRING(kategori, 5, INSTR(SUBSTRING(kategori, 5), ' ') - 1) AS UNSIGNED),
            SUBSTRING(kategori, INSTR(kategori, ' ') + 1)
        ")
        ->pluck('kategori')
        ->unique()
        ->values();

    return view('profiles.edit', compact(
        'profile',
        'jabatanOptions',
        'statusOptions',
        'departemenOptions',
        'units',
        'unitNoCabang',
        'rbOptions',
        'ktrOptions'
    ));
}

    public function update(Request $request, Profile $profile)
{
    $validated = $this->validateProfileRequest($request, $profile);
    $validated = array_map(fn($v) => $v === '' ? null : $v, $validated);

    $this->autoActivateMagangInArray($validated);

    // ==================== LOGIC STATUS KARYAWAN (DI MANUALKAN) ====================
    if (!empty($validated['tgl_keluar'])) {
        // Hanya isi status 'Resign' jika user tidak memilih status lain (kosong)
        if (empty($validated['status_karyawan'])) {
            $validated['status_karyawan'] = 'Resign';
        }
        // Jika user sudah memilih (misalnya Non-Aktif / Non-Aktif Sementara), 
        // maka status tetap mengikuti pilihan user
    } else {
        // Jika tgl_keluar dikosongkan, kembalikan ke Aktif
        if (empty($validated['status_karyawan'])) {
            $validated['status_karyawan'] = 'Aktif';
        }
    }

    // Hanya hitung jika jabatan Guru...
    if ($profile->jabatan === 'Guru' || ($validated['jabatan'] ?? null) === 'Guru') {
        $this->calculateGuruCounts($validated);
    }

    if (isset($validated['rb']) || isset($validated['ktr'])) {
        $this->assignKtrAndRp($validated);
    }

    $validated['rp'] = $validated['rp'] ?? $profile->rp;

    // Update ke database
    $profile->update($validated);

    // Histori
    $profile->histories()->updateOrCreate(
        ['periode' => now()->format('Y-m')],
        [
            'status_karyawan'      => $profile->status_karyawan,
            'jumlah_murid_jadwal'  => $profile->jumlah_murid_jadwal,
            'rb'                   => $profile->rb,
            'ktr'                  => $profile->ktr,
            'ktr_tambahan'         => $profile->ktr_tambahan,
            'rp'                   => $profile->rp,
            'tgl_keluar'           => $profile->tgl_keluar,
            'keterangan_keluar'    => $profile->keterangan_keluar,
            'changed_by'           => Auth::user()->name,
        ]
    );

    $this->calculateAndSaveMasaKerja($profile);
    $this->calculateAndSaveMasaKerjaJabatan($profile);

    $labelBulan = Carbon::now()->locale('id')->translatedFormat('F Y');
    app(ImbalanRekapController::class)->createRekapsForPeriode($labelBulan);

    return redirect()->route('profiles.index')
        ->with('success', 'Profile berhasil diperbarui');
}

/**
 * Hitung masa kerja jabatan dalam bulan dari tgl_mutasi_jabatan sampai hari ini
 * dan simpan ke kolom masa_kerja_jabatan
 */
protected function calculateAndSaveMasaKerjaJabatan(Profile $profile)
{
    if (!$profile->tgl_mutasi_jabatan) {
        $profile->masa_kerja_jabatan = 0;
        $profile->saveQuietly();
        return;
    }

    // Pastikan jadi Carbon instance
    $start = Carbon::parse($profile->tgl_mutasi_jabatan);
    $today = Carbon::today();  // tanpa jam, lebih aman & konsisten

    if ($start->isFuture()) {
        $profile->masa_kerja_jabatan = 0;
        $profile->saveQuietly();
        return;
    }

    // Hitung bulan penuh
    $months = $start->diffInMonths($today);

    // Cek sisa hari di bulan terakhir (pembulatan umum: >=15 hari → +1 bulan)
    $startNextMonth = $start->copy()->addMonths($months);
    $remainingDays = $today->diffInDays($startNextMonth);

    if ($remainingDays >= 15) {
        $months++;
    }

    $profile->masa_kerja_jabatan = max(0, $months);
    $profile->saveQuietly();  // gunakan ini kalau ada observer
    // atau $profile->save() kalau tidak masalah
}

    // ===================================================================
    // DESTROY
    // ===================================================================
    public function destroy(Profile $profile)
    {
        $profile->delete();
        return redirect()->route('profiles.index')->with('success', 'Profile berhasil dihapus');
    }

    // ===================================================================
    // AJAX: INLINE UPDATE RB
    // ===================================================================
    public function inlineUpdate(Request $request, Profile $profile)
{
    if (!Auth::check()) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    $raw = trim($request->rb ?? '');

    // Jika user pilih "Auto"
    if ($raw === '' || $raw === 'auto' || strtolower($raw) === 'auto') {
        // Reset ke otomatis
        $profile->rb = null;
        $profile->rb_tambahan = null;

        // Hitung ulang otomatis
        if ($profile->jabatan === 'Guru') {
            $this->calculateGuruData($profile);
        } elseif ($profile->jabatan === 'Kepala Unit') {
            $this->calculateKepalaUnitData($profile);
        }

        $profile->save();

        return response()->json([
            'status' => 'ok',
            'profile' => [
                'rb'               => $profile->rb,
                'rb_tambahan'      => $profile->rb_tambahan,
                'ktr'              => $profile->ktr,
                'ktr_tambahan'     => $profile->ktr_tambahan,
                'rp'               => $profile->rp,
                'jumlah_rombim'    => $profile->jumlah_rombim,
                'jumlah_murid_jadwal' => $profile->jumlah_murid_jadwal,
            ]
        ]);
    }

    // ================== MANUAL RB ==================
    $rbLabel = $this->normalizeRbLabel($raw);
    $isValidRb = $rbLabel ? Ktr::where('waktu', $rbLabel)->exists() : false;

    if ($profile->jabatan === 'Guru') {
        $this->calculateGuruData($profile);
    } elseif ($profile->jabatan === 'Kepala Unit') {
        $this->calculateKepalaUnitData($profile);
    }

    if ($isValidRb) {
        $profile->rb = $rbLabel;
        $profile->rb_tambahan = $rbLabel;

        $jumlahMurid = $this->getJumlahMuridForProfile($profile);
        $priorityKtr = $profile->jabatan === 'Guru' ? $profile->ktr : $profile->ktr_tambahan;

        $profile->rp = $this->resolveRpFromJumlahMuridWithKtr(
            $jumlahMurid,
            $priorityKtr,
            $rbLabel
        );
    } else {
        $profile->rb = $rbLabel;
        $profile->rb_tambahan = $rbLabel;
        $profile->rp = null;
    }

    $profile->save();

    // Refresh rekap
    $labelBulan = Carbon::now()->locale('id')->translatedFormat('F Y');
    app(ImbalanRekapController::class)->createRekapsForPeriode($labelBulan);

    return response()->json([
        'status' => 'ok',
        'profile' => [
            'rb'               => $profile->rb,
            'rb_tambahan'      => $profile->rb_tambahan,
            'ktr'              => $profile->ktr,
            'ktr_tambahan'     => $profile->ktr_tambahan,
            'rp'               => $profile->rp,
            'jumlah_rombim'    => $profile->jumlah_rombim,
            'jumlah_murid_jadwal' => $profile->jumlah_murid_jadwal,
        ]
    ]);
}

    // ===================================================================
// AJAX: INLINE UPDATE KTR
// ===================================================================
public function inlineUpdateKtr(Request $request, Profile $profile)
{
    $request->validate(['ktr' => 'nullable|string|max:10']);

    $inputKtr = $request->filled('ktr') ? trim($request->ktr) : null;

    // ================== PILIHAN "Otomatis" / Kosong ==================
    if ($inputKtr === '' || $inputKtr === 'Otomatis' || $inputKtr === null) {

        $profile->ktr_tambahan = null;   // reset manual override

        // Hitung ulang otomatis
        if ($profile->jabatan === 'Guru') {
            $this->calculateGuruData($profile);
        } elseif ($profile->jabatan === 'Kepala Unit') {
            $this->calculateKepalaUnitData($profile);
        }

    } 
    // ================== PILIHAN MANUAL KTR ==================
    else {

        $isValidKtr = Ktr::where('kategori', $inputKtr)->exists();

        if ($isValidKtr) {
            $profile->ktr = $inputKtr;
            $profile->ktr_tambahan = $inputKtr;   // simpan sebagai manual

            $jumlahMurid = $this->getJumlahMuridForProfile($profile);

            $profile->rp = $this->resolveRpFromJumlahMuridWithKtr(
                $jumlahMurid,
                $inputKtr,
                $profile->rb
            );
        } else {
            // KTR tidak valid → reset RP
            $profile->rp = null;
        }
    }

    $profile->save();

    // Refresh rekap imbalan
    $labelBulan = Carbon::now()->locale('id')->translatedFormat('F Y');
    app(ImbalanRekapController::class)->createRekapsForPeriode($labelBulan);

    return response()->json([
        'status' => 'ok',
        'profile' => [
            'rb'               => $profile->rb,
            'rb_tambahan'      => $profile->rb_tambahan,
            'ktr'              => $profile->ktr,
            'ktr_tambahan'     => $profile->ktr_tambahan,   // ini yang penting untuk tampilan
            'rp'               => $profile->rp,
            'jumlah_rombim'    => $profile->jumlah_rombim,
            'jumlah_murid_jadwal' => $profile->jumlah_murid_jadwal,
        ]
    ]);
}

    // ===================================================================
    // HELPER: VALIDASI REQUEST
    // ===================================================================
    private function validateProfileRequest(Request $request, Profile $profile = null): array
{
    $ignoreId = $profile?->id ?? 'NULL';

    return $request->validate([
        'nik'               => "required|string|max:20|unique:profiles,nik,{$ignoreId}",
        'nama'              => 'required|string|max:100',
        'jabatan'           => 'nullable|string',
        'status_karyawan'   => 'nullable|string',
        'departemen'        => 'nullable|string',

        // INI YANG WAJIB DITAMBAHKAN — BIAR MASUK KE DATABASE
        'biMBA_unit'        => 'required|string|max:100',        // WAJIB & disimpan
        'no_cabang'         => 'nullable|string|max:20',         // Opsional, otomatis dari JS

        'tgl_masuk'         => 'nullable|date',
        'tempat_lahir'      => 'nullable|string',
        'tgl_lahir'         => 'nullable|date',
        'tgl_magang'        => 'nullable|date',
        'tgl_non_aktif'     => 'nullable|date',
        'tgl_resign'        => 'nullable|date',
        'tgl_keluar'        => 'nullable|date',
        'keterangan_keluar' => 'nullable|string',

        // Field guru (hanya divalidasi jika jabatan Guru)
        'jumlah_murid_mba'  => 'nullable|integer|min:0',
        'jumlah_murid_eng'  => 'nullable|integer|min:0',
        'rb'                => 'nullable|string',
        'ktr'               => 'nullable|string',
        'rp'                => 'nullable|numeric',

        // Kontak & bank
        'no_telp'           => 'nullable|string',
        'email'             => 'nullable|email',
        'no_rekening'       => 'nullable|string',
        'bank'              => 'nullable|string',
        'atas_nama'         => 'nullable|string',

        // Mutasi & lainnya
        'jenis_mutasi'      => 'nullable|string',
        'tgl_mutasi_jabatan'=> 'nullable|date',
        'masa_kerja_jabatan'=> 'nullable|integer',

        'ukuran'            => 'nullable|string',
        'status_lain' => 'nullable|string|in:Belum Terima,Sudah Terima',
        'keterangan'        => 'nullable|string',

        // TAMBAHKAN 5 KOLOM SERAGAM BARU INI:
        'tgl_ambil_seragam'      => 'nullable|date',
        'kaos_kuning_hitam'        => 'nullable|in:S,M,L,XL,XXL',
        'kaos_merah_kuning_biru'   => 'nullable|in:S,M,L,XL,XXL',
        'kemeja_kuning_hitam'      => 'nullable|in:S,M,L,XL,XXL',
        'blazer_merah'             => 'nullable|in:S,M,L,XL,XXL',
        'blazer_biru'              => 'nullable|in:S,M,L,XL,XXL',
    ]);
}

    // ===================================================================
    // AUTO ACTIVATE MAGANG – DINONAKTIFKAN
    // ===================================================================
    private function autoActivateMagangIfNeeded(Profile $profile): void
    {
        // Logika 30 hari dihapus.
        // Status Magang hanya akan diubah jika tgl_selesai_magang diisi via inlineUpdateField.
        return;
    }

    private function autoActivateMagangInArray(array &$data): void
    {
        // Logika otomatis Magang -> Aktif berdasarkan tgl_masuk (30 hari)
        // sudah dihapus/dinonaktifkan. Tidak ada perubahan status di sini.
        return;
    }

    // ===================================================================
    // HITUNG JUMLAH MURID
    // ===================================================================
    private function calculateGuruCounts(array &$data): void
{
    $nama = $data['nama'];
    $data['jumlah_murid_mba'] = $this->getJumlahMuridMBA($nama);
    $data['total_murid'] = BukuInduk::where('guru', $nama)->count();
}

    private function getJumlahMuridForProfile(Profile $profile): int
    {
        return $profile->jabatan === 'Guru'
            ? (int) ($profile->jumlah_murid_jadwal ?? $profile->jumlah_murid_mba ?? $this->getJumlahMuridMBA($profile->nama))
            : BukuInduk::whereIn('guru', function ($q) use ($profile) {
                $q->select('nama')->from('profiles')
                    ->where('departemen', $profile->departemen)
                    ->where('jabatan', 'Guru');
            })->count();
    }

    private function getJumlahMuridMBA(string $namaGuru): int
    {
        $possibleCols = ['program', 'departemen', 'kelas', 'jenis_program', 'paket'];
        $column = collect($possibleCols)->first(fn($col) => Schema::hasColumn('buku_induk', $col));

        $query = BukuInduk::where('guru', $namaGuru);
        if ($column) {
            $query->where($column, 'like', '%MBA%');
        }
        return $query->count();
    }

    private function calculateGuruData(Profile $profile): void
{
    $jumlahMurid = BukuInduk::where('guru', $profile->nama)
                    ->whereIn('status', ['Aktif', 'Baru'])
                    ->count();

    $profile->jumlah_murid_mba     = $jumlahMurid;
    $profile->jumlah_murid_jadwal  = $jumlahMurid;
    $profile->total_murid          = $jumlahMurid;
    $profile->jumlah_rombim        = $this->resolveSlotRombimFromCount($jumlahMurid);

    // Gunakan logic resolveRbFromCount yang sudah diperbaiki
    $rbNumber = $this->resolveRbFromCount($jumlahMurid);
    $profile->rb = $rbNumber ? 'RB' . $rbNumber : 'RB30';
    $profile->rb_tambahan = $profile->rb;

    $profile->ktr = $this->resolveKtrFromRb($profile->rb) ?? 'KTR 1A';
    $profile->ktr_tambahan = $profile->ktr;

    $profile->rp = $this->resolveRpFromJumlahMuridWithKtr(
        $jumlahMurid,
        $profile->ktr,
        $profile->rb
    );

    $profile->saveQuietly();
}

   private function calculateKepalaUnitData(Profile $profile): void
{
    $totalMurid = BukuInduk::where('bimba_unit', $profile->biMBA_unit)
        ->whereIn('status', ['Aktif', 'Baru'])
        ->count();

    $profile->total_murid_bawahan = $totalMurid;
    $profile->jumlah_murid_jadwal = $totalMurid;

    if ($totalMurid <= 0) {
        $profile->rb  = 'RB40';
        $profile->ktr = 'KTR 1B';
        $profile->ktr_tambahan = null;
    } else {
        if (empty(trim($profile->rb ?? ''))) {
            $profile->rb = 'RB40';
        }

        $ktrOtomatis = $this->formatKtrFromCountForKepala($totalMurid);

        if (empty(trim($profile->ktr_tambahan ?? ''))) {
            // AUTO
            $profile->ktr = $ktrOtomatis;
            $profile->ktr_tambahan = null;
        } else {
            // MANUAL
            $profile->ktr = $profile->ktr_tambahan;
        }
    }

    $profile->rb_tambahan = $profile->rb;

    $profile->rp = $this->resolveRpFromJumlahMuridWithKtr(
        $totalMurid,
        $profile->ktr,
        $profile->rb
    );

    $profile->saveQuietly();
}

    private function recalculateProfileData(Profile $profile): void
    {
        $profile->jumlah_murid_mba = $this->getJumlahMuridMBA($profile->nama);
        $profile->total_murid = BukuInduk::where('guru', $profile->nama)->count();

        if ($profile->jabatan === 'Guru') {
            $jadwal = (int) ($profile->jumlah_murid_mba ?? 0);
            $profile->jumlah_murid_jadwal = $jadwal > 0 ? $jadwal : null;
            $profile->jumlah_rombim = $this->resolveSlotRombimFromCount($jadwal);
            $ktr = $profile->rb ? $this->resolveKtrFromRb($profile->rb) : null;
            $profile->rp = $this->resolveRpFromJumlahMuridWithKtr($jadwal, $ktr, $profile->rb);
        }

        if ($profile->jabatan === 'Kepala Unit') {
            $total = BukuInduk::whereIn('guru', function ($q) use ($profile) {
                $q->select('nama')->from('profiles')
                    ->where('departemen', $profile->departemen)
                    ->where('jabatan', 'Guru');
            })->count();

            $profile->total_murid_bawahan = $total;

            if (empty($profile->ktr_tambahan) && $total > 0) {
                $profile->ktr_tambahan = $this->formatKtrFromCountForKepala($total);
            }
            $profile->rp = $this->resolveRpFromJumlahMuridWithKtr($total, $profile->ktr_tambahan);
        }

        $profile->rb_tambahan = $profile->rb;
        $profile->ktr_tambahan = $profile->ktr;
    }

    // ===================================================================
    // ASSIGN KTR & RP (UNTUK STORE/UPDATE)
    // ===================================================================
    private function assignKtrAndRp(array &$data): void
{
    $jabatan        = $data['jabatan'] ?? null;
    $murid          = (int) ($data['jumlah_murid_jadwal'] ?? $data['jumlah_murid_mba'] ?? 0);
    $total          = (int) ($data['total_murid'] ?? $data['total_murid_bawahan'] ?? 0);
    $masaKerjaBulan = (int) ($data['masa_kerja'] ?? 0);
    $status         = $data['status_karyawan'] ?? 'Aktif';

    $hasManualRb           = !empty(trim($data['rb'] ?? ''));
    $hasManualKtr          = !empty(trim($data['ktr'] ?? ''));
    $hasManualKtrTambahan  = !empty(trim($data['ktr_tambahan'] ?? ''));
    $hasManualRp           = isset($data['rp']) && $data['rp'] !== null;

    // Reset default
    $data['ktr']           = null;
    $data['ktr_tambahan']  = null;
    $data['rp']            = null;
    $data['rb']            = null;
    $data['jumlah_rombim'] = null;

    if ($jabatan === 'Guru') {
        
        // ==================== KASUS 0 MURID ====================
        if ($murid <= 0) {
            $data['rb']            = 'RB30';
            $data['ktr']           = 'KTR 1A';
            $data['ktr_tambahan']  = 'KTR 1A';
            $data['jumlah_rombim'] = 0;

            // Tetap ambil RP dari tabel KTR
            $data['rp'] = $this->resolveRpFromJumlahMuridWithKtr(
                0,
                'KTR 1A',
                'RB30'
            );

            // Jika ada manual RP, prioritaskan
            if ($hasManualRp) {
                $data['rp'] = floatval($data['rp']);
            }
        } 
        // ==================== KASUS ADA MURID ====================
        else {
            if (!$hasManualRb) {
                $rb = $this->resolveRbFromCount($murid);
                $data['rb'] = $rb ? 'RB' . $rb : 'RB30';
            }

            $rbNormalized = $this->normalizeRbValue($data['rb']);

            if (!$hasManualKtr) {
                $data['ktr'] = $rbNormalized
                    ? $this->resolveKtrFromRb("RB{$rbNormalized}")
                    : $this->formatKtrFromCountForGuru($murid);
            }

            if (!$hasManualKtrTambahan) {
                $data['ktr_tambahan'] = $data['ktr'];
            }

            if (!$hasManualRp) {
                $data['rp'] = $this->resolveRpFromJumlahMuridWithKtr(
                    $murid,
                    $data['ktr_tambahan'] ?? $data['ktr'],
                    $data['rb']
                );
            }

            $data['jumlah_rombim'] = $this->resolveSlotRombimFromCount($murid);
        }
    } 
    elseif ($jabatan === 'Kepala Unit') {
        $jumlahEfektif = $total > 0 ? $total : $murid;

        if (!$hasManualRb) {
            $data['rb'] = 'RB40';
        }

        if (!$hasManualKtrTambahan) {
            $data['ktr_tambahan'] = $this->formatKtrFromCountForKepala($jumlahEfektif) ?? 'KTR 1B';
        }

        if (!$hasManualRp) {
            $data['rp'] = $this->resolveRpFromJumlahMuridWithKtr(
                $jumlahEfektif,
                $data['ktr_tambahan'],
                $data['rb']
            );
        }
    } 
    else {
        // Jabatan lain (Staff, dll)
        $range = $masaKerjaBulan >= 24 ? '>= 24 Bulan' : '< 24 Bulan';

        $skim = DB::table('skim')
            ->where('jabatan', $jabatan)
            ->where('masa_kerja', $range)
            ->where('status', $status)
            ->first();

        if (!$skim) {
            $skim = DB::table('skim')
                ->where('jabatan', $jabatan)
                ->where('masa_kerja', $range)
                ->where('status', 'Aktif')
                ->first();
        }

        $data['rp'] = $skim ? (float) $skim->thp : 0;

        if ($hasManualRp) {
            $data['rp'] = floatval($data['rp']);
        }
    }
}

    // ===================================================================
    // RB, KTR, RP HELPERS
    // ===================================================================
    private function normalizeRbLabel(string $raw): ?string
    {
        return preg_match('/(\d{1,3})/', $raw, $m) ? 'RB' . $m[1] : null;
    }

    private function resolveRbFromCount(int $count): ?int
{
    if (!class_exists(PenyesuaianRbGuru::class)) {
        // Fallback logic lama (kalau tabel PenyesuaianRbGuru belum ada)
        if ($count >= 36) return 40;
        if ($count >= 31) return 35;
        if ($count >= 26) return 35;   // ← PERBAIKAN UTAMA
        if ($count >= 21) return 30;   // atau 25 sesuai aturan lama
        return 30; // default
    }

    // Gunakan data dari tabel PenyesuaianRbGuru (prioritas utama)
    $mappings = PenyesuaianRbGuru::orderBy('id')->get();

    if ($mappings->isEmpty()) {
        // Fallback jika tabel kosong
        if ($count >= 36) return 40;
        if ($count >= 26) return 35;   // ← PERBAIKAN
        if ($count >= 21) return 30;
        return 30;
    }

    foreach ($mappings as $map) {
        [$min, $max] = $this->parseRangeString($map->jumlah_murid) ?: [0, 0];
        
        if ($count >= $min && $count <= $max) {
            return $this->normalizeRbValue($map->penyesuaian_rb);
        }
    }

    // Jika melebihi range tertinggi → ambil yang paling tinggi
    $highest = $mappings->sortByDesc(function ($map) {
        [$min, $max] = $this->parseRangeString($map->jumlah_murid) ?: [0, 999999];
        return $max;
    })->first();

    return $highest ? $this->normalizeRbValue($highest->penyesuaian_rb) : 30;
}

    private function resolveSlotRombimFromCount(int $count): ?int
{
    if (!class_exists(PenyesuaianRbGuru::class)) {
        return $count > 0 ? (int) ceil($count / 8) : null;
    }

    $mappings = PenyesuaianRbGuru::orderBy('id')->get();
    if ($mappings->isEmpty()) {
        return $count > 0 ? (int) ceil($count / 8) : null;
    }

    foreach ($mappings as $map) {
        [$min, $max] = $this->parseRangeString($map->jumlah_murid) ?: [0, 0];
        if ($count >= $min && $count <= $max) {
            return $this->normalizeSlotRombimValue($map->slot_rombim); // 0–25 → 8
        }
    }

    // Kalau di atas range tertinggi → pakai yang tertinggi
    $highest = $mappings->sortByDesc(function ($map) {
        [$min, $max] = $this->parseRangeString($map->jumlah_murid) ?: [0, 0];
        return $max;
    })->first();

    return $highest ? $this->normalizeSlotRombimValue($highest->slot_rombim) : null;
}

    private function resolveKtrFromRb(?string $rbLabel): ?string
    {
        if (!$rbLabel) return null;
        $rb = (int) preg_replace('/\D/', '', $rbLabel);
        if ($rb >= 1 && $rb <= 30) return 'KTR 1A';
        if ($rb >= 31 && $rb <= 40) return 'KTR 2A';
        if ($rb >= 41 && $rb <= 60) return 'KTR 2B';
        if ($rb > 60) return 'KTR 3A';
        return null;
    }

    private function formatKtrFromCountForGuru(int $count): ?string
    {
        if ($count >= 1 && $count <= 30) return 'KTR 1A';
        if ($count >= 31 && $count <= 40) return 'KTR 2A';
        if ($count >= 41 && $count <= 60) return 'KTR 2B';
        if ($count > 60) return 'KTR 3A';
        return null;
    }

   private function formatKtrFromCountForKepala(int $count): string
{
    if ($count <= 0) {
        return 'KTR 1B';
    }

    // Ambil data dari tabel PenyesuaianKtr
    $penyesuaian = PenyesuaianKtr::orderBy('id')->get();

    if ($penyesuaian->isEmpty()) {
        // Fallback ke hardcoded jika tabel kosong
        return $this->getHardcodedKtrForKepala($count);
    }

    foreach ($penyesuaian as $p) {
        [$min, $max] = $this->parseRangeString($p->jumlah_murid) ?: [0, 0];
        
        if ($count >= $min && $count <= $max) {
            return trim($p->penyesuaian_ktr);
        }
    }

    // Jika jumlah murid melebihi range tertinggi → ambil yang paling tinggi
    $highest = $penyesuaian->sortByDesc(function ($p) {
        [$min, $max] = $this->parseRangeString($p->jumlah_murid) ?: [0, 999999];
        return $max;
    })->first();

    return $highest ? trim($highest->penyesuaian_ktr) : 'KTR 10B';
}

/**
 * Fallback hardcoded (jika tabel PenyesuaianKtr kosong)
 */
private function getHardcodedKtrForKepala(int $count): string
{
    if ($count <= 25)   return 'KTR 1B';
    if ($count <= 75)   return 'KTR 2A';
    if ($count <= 100)  return 'KTR 2B';
    if ($count <= 125)  return 'KTR 3A';
    if ($count <= 150)  return 'KTR 3B';
    if ($count <= 175)  return 'KTR 4A';
    if ($count <= 200)  return 'KTR 4B';
    if ($count <= 225)  return 'KTR 5A';
    if ($count <= 250)  return 'KTR 5B';
    if ($count <= 275)  return 'KTR 6A';
    if ($count <= 300)  return 'KTR 6B';
    if ($count <= 325)  return 'KTR 7A';
    if ($count <= 350)  return 'KTR 7B';
    if ($count <= 375)  return 'KTR 8A';
    if ($count <= 400)  return 'KTR 8B';
    if ($count <= 425)  return 'KTR 9A';
    if ($count <= 450)  return 'KTR 9B';
    if ($count <= 475)  return 'KTR 10A';

    return 'KTR 10B';
}

    private function parseRangeString(?string $s): ?array
{
    if (!$s) return null;

    // Bersihkan karakter dash yang berbeda
    $s = preg_replace('/[-\x{2013}\x{2014}\x{2212}]/u', '-', trim($s));

    if (preg_match('/^(\d+)-(\d+)$/', $s, $m)) {
        $min = (int) $m[1];
        $max = (int) $m[2];
        return [$min > $max ? $max : $min, $min > $max ? $min : $max];
    }

    return null;
}

    private function parseRangeMappings($mappings, $column)
    {
        $parsed = [];
        foreach ($mappings as $map) {
            if (!preg_match('/RB\s*0*?(\d{1,3})/i', strtoupper(trim($map->{$column} ?? '')), $m)) continue;
            $rb = (int) $m[1];
            $parsed[] = ['min' => max(1, $rb - 4), 'max' => $rb, 'map' => $map];
        }
        return $parsed;
    }

    private function extractRpFromRow($row): ?float
    {
        if (!$row) return null;
        $cols = ['jumlah', 'rp', 'nominal', 'harga', 'jumlah_rp', 'biaya', 'nilai'];
        foreach ($cols as $col) {
            if (isset($row->{$col})) {
                $v = $this->normalizeCurrencyToInt($row->{$col});
                if ($v !== null) return $v;
            }
        }
        foreach ($row->getAttributes() as $v) {
            $clean = $this->normalizeCurrencyToInt($v);
            if ($clean !== null) return (float) $clean;
        }
        return null;
    }

    private function normalizeCurrencyToInt($value): ?int
    {
        if ($value === null || !is_scalar($value)) return null;
        $s = preg_replace('/[Rp\s]/i', '', trim((string) $value));
        $s = preg_replace('/[^0-9,.]/', '', $s);
        if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (strpos($s, ',') !== false) {
            $s = str_replace(',', '.', $s);
        }
        return is_numeric($s) ? (int) round((float) $s) : null;
    }

    private function normalizeRbValue($rbRaw): ?int
    {
        return $rbRaw && preg_match('/(\d+)/', (string) $rbRaw, $m) ? (int) $m[1] : null;
    }

    private function normalizeSlotRombimValue($raw): ?int
    {
        return $raw && preg_match('/(\d+)/', (string) $raw, $m) ? (int) $m[1] : null;
    }


protected function calculateAndSaveMasaKerja($profile)
{
    // ✅ PRIORITAS: tgl_selesai_magang > tgl_masuk
    $startDate = null;
    
    if ($profile->tgl_selesai_magang) {
        $startDate = Carbon::parse($profile->tgl_selesai_magang);
    } elseif ($profile->tgl_masuk) {
        $startDate = Carbon::parse($profile->tgl_masuk);
    }
    
    if (!$startDate) {
        $profile->masa_kerja = 0;
        $profile->saveQuietly();
        return;
    }

    // ✅ SELALU sampai HARI INI (tidak peduli resign/non-aktif)
    $today = Carbon::today();
    
    // Reset jam untuk perbandingan akurat (sama seperti JS)
    $startDate->startOfDay();
    $today->startOfDay();

    // ✅ LOGIKA BULAN YANG SAMA PERSIS DENGAN JAVASCRIPT
    $years = $today->year - $startDate->year;
    $months = $today->month - $startDate->month;
    
    // Jika bulan sudah tepat TAPI tanggal hari ini < tanggal mulai → kurangi 1 bulan
    if ($months < 0 || ($months === 0 && $today->day < $startDate->day)) {
        $months += 12;
        $years--;
    }
    
    $totalMonths = $years * 12 + $months;
    $totalMonths = max(0, $totalMonths);

    $profile->masa_kerja = $totalMonths;
    $profile->saveQuietly();
}

    public function inlineUpdateField(Request $request, Profile $profile)
{
    if (!Auth::check()) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    $field = $request->input('field');
    $value = $request->input('value');

    $allowedFields = [
        'mentor_magang', 'periode', 'tgl_selesai_magang',
        'ukuran', 'tgl_ambil_seragam', 'status_lain', 'keterangan'
    ];

    if (!in_array($field, $allowedFields)) {
        return response()->json(['status' => 'error', 'message' => 'Field tidak diizinkan'], 422);
    }

    if ($field === 'tgl_selesai_magang') {
        $newValue = $value === '' ? null : $value;

        $profile->tgl_selesai_magang = $newValue;

        // Ubah status jika perlu
        if ($newValue && $profile->status_karyawan === 'Magang') {
            $profile->status_karyawan = 'Aktif';
        }

        // TIDAK MENGUBAH tgl_masuk sama sekali
        Log::info("tgl_selesai_magang diupdate untuk {$profile->nama}: " . ($newValue ?? 'NULL'));
    } 
    else {
        $profile->{$field} = $value === '' ? null : $value;
    }

    $profile->save();

    // Hitung ulang masa kerja
    if (in_array($field, ['tgl_selesai_magang', 'tgl_masuk', 'tgl_resign', 'tgl_non_aktif', 'status_karyawan'])) {
        $this->calculateAndSaveMasaKerja($profile);
    }

    // Response
    $formattedValue = $value;
    if (in_array($field, ['tgl_selesai_magang', 'tgl_ambil_seragam']) && $value) {
        $formattedValue = Carbon::parse($value)->format('d-m-Y');
    }

    return response()->json([
        'status'          => 'ok',
        'field'           => $field,
        'value'           => $formattedValue,
        'status_karyawan' => $profile->status_karyawan ?? null,
        'masa_kerja_text' => $this->formatMasaKerjaText($profile->masa_kerja ?? 0)
    ]);
}

private function formatMasaKerjaText(?int $months): string
{
    if ($months === null || $months < 0) {
        return '-';
    }

    $years  = floor($months / 12);
    $remain = $months % 12;

    $parts = [];
    if ($years > 0) {
        $parts[] = "$years tahun";
    }
    if ($remain > 0) {
        $parts[] = "$remain bulan";
    }

    return $parts ? implode(' ', $parts) : 'Kurang dari 1 bulan';
}
    public function updateSeragamKolom(Request $request, Profile $profile)
{
    $field = $request->field;
    $ukuran = $request->ukuran ? strtoupper(trim($request->ukuran)) : null;

    // Validasi hanya boleh S, M, L, XL, XXL atau kosong
    if ($ukuran && !in_array($ukuran, ['S', 'M', 'L', 'XL', 'XXL'])) {
        return response()->json(['status' => 'Ukuran tidak valid'], 422);
    }

    // Simpan langsung ke kolom
    $profile->{$field} = $ukuran;
    $profile->save();

    // Auto isi tanggal ambil seragam kalau baru pertama kali ada seragam
    $adaSeragam = $profile->kaos_kuning_hitam || $profile->kaos_merah_kuning_biru ||
                  $profile->kemeja_kuning_hitam || $profile->blazer_merah || $profile->blazer_biru;

    if ($adaSeragam && !$profile->tgl_ambil_seragam) {
        $profile->tgl_ambil_seragam = now();
        $profile->save();
    }

    return response()->json(['status' => 'ok', 'ukuran' => $ukuran]);
}
public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:10240', // max 10MB
    ]);

    try {
        Excel::import(new ProfileImport, $request->file('file'));

        return redirect()->route('profiles.index')
            ->with('success', 'Data profile berhasil diimport!');
    } catch (\Throwable $e) {
        Log::error('Gagal import Profile', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()->with('error', 'Gagal import: ' . $e->getMessage());
    }
}
public function export(Request $request)
{
    $filters = $request->only(['unit', 'search']);

    return Excel::download(
        new ProfileExport($filters),
        'Daftar_Profile_' . now()->format('Y-m-d_His') . '.xlsx'
    );
}

public function recalculateMuridDanKtr(Profile $profile): void
{
    if ($profile->status_karyawan === 'Resign') {
        return;
    }

    $totalMurid = 0;
    $unitName = trim($profile->biMBA_unit ?? '');

    if (empty($unitName)) {
        $unitName = trim(Profile::where('id', $profile->id)->value('biMBA_unit') ?? '');
    }

    if ($profile->jabatan === 'Guru') {
        $totalMurid = BukuInduk::withoutGlobalScopes()
            ->where('guru', trim($profile->nama ?? ''))
            ->whereIn('status', ['Aktif', 'Baru'])
            ->count();
    } 
    elseif (str_contains(strtolower($profile->jabatan ?? ''), 'kepala')) {
        $totalMurid = BukuInduk::withoutGlobalScopes()
            ->where('bimba_unit', '=', $unitName)
            ->whereIn('status', ['Aktif', 'Baru'])
            ->count();

        if ($totalMurid === 0 && !empty($unitName)) {
            $totalMurid = BukuInduk::withoutGlobalScopes()
                ->whereRaw('TRIM(bimba_unit) = ?', [$unitName])
                ->whereIn('status', ['Aktif', 'Baru'])
                ->count();
        }
    }

    // Update data murid
    $profile->total_murid_bawahan = $totalMurid;
    $profile->jumlah_murid_jadwal = $totalMurid;
    $profile->jumlah_murid_mba    = $totalMurid;
    $profile->total_murid         = $totalMurid;
    $profile->jumlah_rombim       = $totalMurid > 0 ? $this->resolveSlotRombimFromCount($totalMurid) : 0;

    $profile->saveQuietly();

    // ====================== KEPALA UNIT ======================
    if (str_contains(strtolower($profile->jabatan ?? ''), 'kepala')) {

        if ($totalMurid <= 0) {
            $profile->rb = 'RB40';
            $profile->ktr = 'KTR 1B';
            $profile->ktr_tambahan = null;   // pastikan auto
        } else {
            if (empty(trim($profile->rb ?? ''))) {
                $profile->rb = 'RB40';
            }
            $profile->rb_tambahan = $profile->rb;

            $ktrOtomatis = $this->formatKtrFromCountForKepala($totalMurid);

            // === LOGIKA AUTO / MANUAL ===
            if (empty(trim($profile->ktr_tambahan ?? ''))) {
                // MODE AUTO
                $profile->ktr = $ktrOtomatis;
                $profile->ktr_tambahan = null;     // penting: null = auto
            } else {
                // MODE MANUAL (pertahankan pilihan user)
                $profile->ktr = $profile->ktr_tambahan;
            }
        }

        $profile->rp = $this->resolveRpFromJumlahMuridWithKtr(
            $totalMurid, 
            $profile->ktr, 
            $profile->rb
        );

        $profile->saveQuietly();
    }
}

public function getNextNikNoUrut(Request $request)
{
    $bimba_unit = $request->bimba_unit;

    if (!$bimba_unit) {
        return response()->json(['error' => 'Unit kosong'], 400);
    }

    $unit = Unit::where('biMBA_unit', $bimba_unit)->first();

    if (!$unit) {
        return response()->json(['error' => 'Unit tidak ditemukan'], 404);
    }

    $noCabang = $unit->no_cabang;

    // ===============================
    // AMBIL NO URUT DARI NIK TERAKHIR
    // ===============================
    $lastNik = Profile::where('biMBA_unit', $bimba_unit)
        ->whereNotNull('nik')
        ->orderByDesc('nik')
        ->value('nik');

    if ($lastNik) {
        $lastUrut = (int) substr($lastNik, -4); // ambil 4 digit terakhir
    } else {
        $lastUrut = 0;
    }

    $nextUrut = $lastUrut + 1;

    $nextUrutPadded = str_pad($nextUrut, 4, '0', STR_PAD_LEFT);

    $nik = $noCabang . '01' . $nextUrutPadded;

    return response()->json([
        'nik' => $nik,
        'no_urut' => $nextUrut,
        'no_cabang' => $noCabang
    ]);
}
public function showHistori(Profile $profile)
{
    if (!Auth::user()->is_admin) {
        abort(403);
    }

    $histori = $profile->histories()
        ->orderBy('created_at', 'desc')   // lebih baik pakai created_at
        ->get([
            'periode',
            'status_karyawan',
            'jumlah_murid_jadwal',
            'rb',
            'ktr',
            'ktr_tambahan',
            'rp',
            'tgl_keluar',
            'keterangan_keluar',
            'changed_by',
            'created_at'
        ]);

    return response()->json($histori);
}

private function getJumlahMuridAktif(string $namaGuru): int
{
    return BukuInduk::where('guru', $namaGuru)
        ->whereIn('status', ['Aktif', 'Baru'])
        ->count();
}

    private function resolveRpFromJumlahMuridWithKtr(
        int $jumlahMurid,
        ?string $forcedKtr = null,
        ?string $forcedRbLabel = null
    ): ?float {
        if (!class_exists(Ktr::class)) return null;

        // === LOGIKA BARU UNTUK 0 MURID ===
        if ($jumlahMurid <= 0) {
            $rbLabel = $forcedRbLabel ?? 'RB30';
            $ktrPriority = $forcedKtr ?? 'KTR 1A';
        } else {
            $rbLabel = $forcedRbLabel
                ?? ($this->resolveRbFromCount($jumlahMurid)
                    ? 'RB' . $this->resolveRbFromCount($jumlahMurid)
                    : 'RB30');

            $ktrPriority = $forcedKtr ?? ($rbLabel ? $this->resolveKtrFromRb($rbLabel) : 'KTR 1A');
        }

        // === CARI DI TABEL KTR ===
        if ($rbLabel) {
            $rbNumber = (int) preg_replace('/\D/', '', $rbLabel);
            $likeKey = "%RB" . ltrim((string) $rbNumber, '0') . "%";
            
            $rows = Ktr::where('waktu', 'like', $likeKey)->orderBy('id')->get();

            if ($rows->isNotEmpty()) {
                $norm = fn($s) => $s ? preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($s))) : null;
                $targetNorm = $norm($ktrPriority);
                $targetShort = $targetNorm && preg_match('/(\d+[A-Z]?)$/', $targetNorm, $m) ? $m[1] : null;

                foreach ($rows as $r) {
                    if ($norm($r->kategori ?? '') === $targetNorm) {
                        $v = $this->extractRpFromRow($r);
                        if ($v !== null) return $v;
                    }
                }

                if ($targetShort) {
                    foreach ($rows as $r) {
                        if (stripos($norm($r->kategori ?? ''), $targetShort) !== false) {
                            $v = $this->extractRpFromRow($r);
                            if ($v !== null) return $v;
                        }
                    }
                }

                // Fallback ambil baris pertama
                foreach ($rows as $r) {
                    $v = $this->extractRpFromRow($r);
                    if ($v !== null) return $v;
                }
            }
        }

        // Fallback range mapping
        $mappings = Ktr::orderBy('id')->get();
        if ($mappings->isNotEmpty()) {
            foreach ($this->parseRangeMappings($mappings, 'waktu') as $p) {
                if ($jumlahMurid >= $p['min'] && $jumlahMurid <= $p['max']) {
                    return $this->extractRpFromRow($p['map']);
                }
            }
        }

        return null;
    }

}
