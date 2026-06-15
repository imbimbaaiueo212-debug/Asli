<?php

namespace App\Http\Controllers;

use App\Models\SertifikatBeasiswa;
use App\Models\BukuInduk;
use App\Models\Unit;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SertifikatBeasiswaController extends Controller
{
    /* =====================================================
     * INDEX - Daftar Sertifikat
     * ===================================================== */
    public function index()
    {
        $beasiswa = SertifikatBeasiswa::orderBy('id', 'desc')
            ->forCurrentUser()  // Scope ini sudah handle filter unit non-admin
            ->get();

        return view('sertifikat_beasiswa.index', compact('beasiswa'));
    }

    /* =====================================================
     * CREATE - Form Tambah
     * ===================================================== */
    public function create(Request $request)
    {
        $user = Auth::user();

        // List unit untuk dropdown
        if ($user->isAdminUser()) {
            $listUnit = Unit::orderBy('biMBA_unit')
                ->get()
                ->mapWithKeys(fn ($u) => [$u->biMBA_unit => $u->label]);
        } else {
            // Non-admin hanya boleh pilih unit sendiri
            $listUnit = collect([
                $user->bimba_unit => $user->bimba_unit  // label bisa diganti jika ada accessor
            ])->filter(); // hilangkan jika kosong
        }

        // List murid
        $muridQuery = BukuInduk::query()
            ->selectRaw("TRIM(nim) AS nim, UPPER(TRIM(nama)) AS nama")
            ->whereNotNull('nim')
            ->where('nim', '!=', '');

        if ($user->isAdminUser()) {
            if ($request->filled('bimba_unit')) {
                $muridQuery->where('bimba_unit', $request->bimba_unit);
            } else {
                $muridQuery->whereRaw('1 = 0'); // kosong jika belum pilih unit
            }
        } else {
            // Non-admin hanya murid dari unit sendiri
            if ($user->bimba_unit) {
                $muridQuery->where('bimba_unit', $user->bimba_unit);
            } else {
                $muridQuery->whereRaw('1 = 0');
            }
        }

        $listMurid = $muridQuery
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn ($m) => [
                "{$m->nim} | {$m->nama}" => "{$m->nim} | {$m->nama}"
            ]);

        return view('sertifikat_beasiswa.create', compact('listUnit', 'listMurid'));
    }

    /* =====================================================
     * STORE - Simpan Data Baru
     * ===================================================== */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'murid'           => 'required|string',
            'bimba_unit'      => 'required|string',  // ← diganti dari nama_unit
            'virtual_account' => 'nullable|string',
            'tanggal_lahir'   => 'nullable|date',
            'alamat'          => 'nullable|string',
            'nama_orang_tua'  => 'nullable|string',
            'golongan'        => 'nullable|string',
            'jumlah_beasiswa' => 'required|numeric|min:0',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date',
            'periode_bea_ke'  => 'required|string',
        ]);

        // Parse murid
        [$nim, $nama] = array_map('trim', explode('|', $validated['murid'], 2));
        unset($validated['murid']);

        // Non-admin: kunci bimba_unit ke unit user
        if (!$user->isAdminUser()) {
            if (!$user->bimba_unit) {
                return back()->withErrors(['bimba_unit' => 'Unit Anda belum diatur. Hubungi admin.']);
            }
            $validated['bimba_unit'] = $user->bimba_unit;
        }

        $beasiswa = SertifikatBeasiswa::create(array_merge($validated, [
            'nim'  => $nim,
            'nama' => $nama,
        ]));

        $this->syncBeasiswaToBukuInduk(
            $nim,
            $validated['periode_bea_ke'] ?? null,
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_selesai'] ?? null
        );

        return redirect()
            ->route('sertifikat-beasiswa.index')
            ->with('success', 'Data Sertifikat Beasiswa berhasil ditambahkan!');
    }

    /* =====================================================
     * EDIT - Form Edit
     * ===================================================== */
    public function edit($id)
    {
        $user = Auth::user();

        $beasiswa = SertifikatBeasiswa::forCurrentUser()->findOrFail($id);

        // List unit
        if ($user->isAdminUser()) {
            $listUnit = Unit::orderBy('biMBA_unit')
                ->get()
                ->mapWithKeys(fn ($u) => [$u->biMBA_unit => $u->label]);
        } else {
            $listUnit = collect([
                $beasiswa->bimba_unit => $beasiswa->bimba_unit
            ]);
        }

        // List murid sesuai unit sertifikat
        $listMurid = BukuInduk::query()
            ->selectRaw("TRIM(nim) AS nim, UPPER(TRIM(nama)) AS nama")
            ->where('bimba_unit', $beasiswa->bimba_unit)
            ->whereNotNull('nim')
            ->where('nim', '!=', '')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn ($m) => [
                "{$m->nim} | {$m->nama}" => "{$m->nim} | {$m->nama}"
            ]);

        return view('sertifikat_beasiswa.edit', compact('beasiswa', 'listUnit', 'listMurid'));
    }

    /* =====================================================
     * UPDATE - Simpan Perubahan
     * ===================================================== */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $beasiswa = SertifikatBeasiswa::forCurrentUser()->findOrFail($id);

        $validated = $request->validate([
            'murid'           => 'required|string',
            'bimba_unit'      => 'required|string',
            'virtual_account' => 'nullable|string',
            'tanggal_lahir'   => 'nullable|date',
            'alamat'          => 'nullable|string',
            'nama_orang_tua'  => 'nullable|string',
            'golongan'        => 'nullable|string',
            'jumlah_beasiswa' => 'required|numeric|min:0',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date',
            'periode_bea_ke'  => 'required|string',
        ]);

        [$nim, $nama] = array_map('trim', explode('|', $validated['murid'], 2));
        unset($validated['murid']);

        // Non-admin tidak boleh ubah unit
        if (!$user->isAdminUser()) {
            $validated['bimba_unit'] = $user->bimba_unit;
        }

        $beasiswa->update(array_merge($validated, [
            'nim'  => $nim,
            'nama' => $nama,
        ]));

        $this->syncBeasiswaToBukuInduk(
            $nim,
            $validated['periode_bea_ke'] ?? null,
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_selesai'] ?? null
        );

        return redirect()
            ->route('sertifikat-beasiswa.index')
            ->with('success', 'Data Sertifikat Beasiswa berhasil diupdate!');
    }

    /* =====================================================
     * PDF - Generate Sertifikat
     * ===================================================== */
    public function pdf($id)
{
    $beasiswa = SertifikatBeasiswa::forCurrentUser()->findOrFail($id);

    return view('sertifikat_beasiswa.pdf', compact('beasiswa'));
}

    /* =====================================================
     * SYNC KE BUKU INDUK
     * ===================================================== */
    private function syncBeasiswaToBukuInduk(
    string $nim,
    ?string $periode,
    ?string $tglMulai,
    ?string $tglAkhir
): void {

    $bukuInduk = BukuInduk::where('nim', $nim)->first();

    if (!$bukuInduk) return;

    // kalau semua kosong → JANGAN UPDATE APA PUN
    if (empty($periode) && empty($tglMulai) && empty($tglAkhir)) {
        return;
    }

    $alert = null;

    if ($tglMulai && $tglAkhir) {
        $today = Carbon::today();

        if ($today->between(
            Carbon::parse($tglMulai),
            Carbon::parse($tglAkhir)
        )) {
            $alert = 'aktif';
        }
    }

    $bukuInduk->update([
        'periode'   => $periode,
        'tgl_mulai' => $tglMulai,
        'tgl_akhir' => $tglAkhir,
        'alert'     => $alert,
    ]);
}


    public function destroy($id)
{
    $beasiswa = SertifikatBeasiswa::forCurrentUser()->findOrFail($id);
    
    $beasiswa->delete(); // soft delete

    // Optional: rollback sync ke buku induk jika diperlukan
    $this->syncBeasiswaToBukuInduk($beasiswa->nim, null, null, null);

    return redirect()
        ->route('sertifikat-beasiswa.index')
        ->with('success', 'Sertifikat beasiswa berhasil dihapus');
}
}