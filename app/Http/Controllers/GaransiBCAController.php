<?php

namespace App\Http\Controllers;

use App\Models\GaransiBCA;
use App\Models\BukuInduk;
use App\Models\PengajuanGaransi;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;



class GaransiBCAController extends Controller
{
    /* =====================================================
     * INDEX
     * ===================================================== */
    public function index()
{
    $user = Auth::user();

    /* ================= GARANSI (SUDAH DIBERIKAN) ================= */
    $query = GaransiBCA::query()->orderBy('id', 'desc');

    // kalau nanti mau filter admin vs non-admin bisa ditambah di sini

    $data = $query->get();

    /* ================= PENGAJUAN GARANSI ================= */
    $pengajuanQuery = PengajuanGaransi::query()->orderBy('id', 'desc');

    // OPTIONAL: kalau mau filter unit
    if (!$user->is_admin ?? false) {
        // contoh kalau mau batasi per unit
        // $pengajuanQuery->where('bimba_unit', $user->bimba_unit);
    }

    $pengajuan = $pengajuanQuery->get();

    return view('garansi_bca.index', compact('data', 'pengajuan'));
}

    /* =====================================================
     * CREATE
     * ===================================================== */
    public function create(Request $request)
    {
        $user = Auth::user();

        /* ================= UNIT ================= */
        if ($user->isAdminUser()) {
            $listUnit = Unit::orderBy('biMBA_unit')
                ->pluck('biMBA_unit', 'biMBA_unit');
        } else {
            $listUnit = collect([
                $user->unit?->biMBA_unit => $user->unit?->biMBA_unit
            ]);
        }

        /* ================= MURID (BUKU INDUK) ================= */
        $muridQuery = BukuInduk::query()
            ->select(
                'nim',
                'nama',
                'tmpt_lahir',
                'tgl_lahir',
                'tgl_masuk',
                'orangtua',
                'bimba_unit'
            )
            ->whereNotNull('nim')
            ->where('nim', '!=', '');

        // Admin wajib pilih unit dulu
        if ($user->isAdminUser()) {
            if ($request->filled('bimba_unit')) {
                $muridQuery->where('bimba_unit', $request->bimba_unit);
            } else {
                $muridQuery->whereRaw('1 = 0');
            }
        }

        $listMurid = $muridQuery
            ->orderBy('nama')
            ->get();

        return view('garansi_bca.create', compact(
            'listUnit',
            'listMurid'
        ));
    }

    /* =====================================================
     * STORE
     * ===================================================== */
    public function store(Request $request)
{
    $request->validate([
        'nim'             => 'required|exists:buku_induk,nim',
        'virtual_account' => 'nullable|string',
    ]);

    /* ================= AMBIL DATA BUKU INDUK ================= */
    $murid = BukuInduk::where('nim', trim($request->nim))->firstOrFail();

    $tanggalDiberikan = Carbon::today();

    /* ================= GARANSI (ANTI DOUBLE) ================= */
    GaransiBCA::updateOrCreate(
        ['nim' => $murid->nim], // 🔥 kunci utama
        [
            'virtual_account' => $request->virtual_account,

            'nama_murid' => $murid->nama,

            'tempat_tanggal_lahir' => trim(
                ($murid->tmpt_lahir ?: '-') . ', ' .
                ($murid->tgl_lahir
                    ? Carbon::parse($murid->tgl_lahir)->format('d-m-Y')
                    : '-'
                )
            ),

            'tanggal_masuk'       => $murid->tgl_masuk,
            'nama_orang_tua_wali' => $murid->orangtua,
            'bimba_unit'          => $murid->bimba_unit,

            'tanggal_diberikan'   => $tanggalDiberikan,
            'sumber'              => 'manual',
        ]
    );

    /* ================= UPDATE KE BUKU INDUK ================= */
    $murid->update([
        'tgl_surat_garansi' => $tanggalDiberikan
    ]);

    return redirect()
        ->route('garansi-bca.index')
        ->with('success', 'Garansi berhasil dibuat / diperbarui');
}

    /* =====================================================
     * EDIT
     * ===================================================== */
    public function edit($id)
    {
        $data = GaransiBCA::findOrFail($id);

        return view('garansi_bca.edit', compact('data'));
    }

    /* =====================================================
     * UPDATE
     * ===================================================== */
    public function update(Request $request, $id)
    {
        $data = GaransiBCA::findOrFail($id);

        $validated = $request->validate([
            'virtual_account'        => 'nullable|string',
            'tempat_tanggal_lahir'   => 'nullable|string',
            'tanggal_masuk'          => 'nullable|date',
            'nama_orang_tua_wali'    => 'nullable|string',
            'tanggal_diberikan'      => 'nullable|date', // ← jika mau bisa diedit admin
        ]);

        $data->update($validated);

        return redirect()
            ->route('garansi-bca.index')
            ->with('success', 'Data Garansi BCA berhasil diupdate');
    }

    /* =====================================================
     * DESTROY
     * ===================================================== */
    public function destroy($id)
    {
        GaransiBCA::findOrFail($id)->delete();

        return redirect()
            ->route('garansi-bca.index')
            ->with('success', 'Data Garansi BCA berhasil dihapus');
    }
    /* =====================================================
 * PDF GARANSI BCA
 * ===================================================== */
public function pdf($id)
{
    $user = auth()->user();

    $data = GaransiBCA::findOrFail($id);

    // 🔐 Security: user non-admin hanya boleh lihat unit sendiri
    if (
        !$user->isAdminUser() &&
        $data->bimba_unit !== $user->unit?->biMBA_unit
    ) {
        abort(403);
    }

    $pdf = Pdf::loadView(
    'garansi_bca.pdf',
    compact('data')
)->setPaper('A5', 'landscape');

    // tampil di browser
    return $pdf->stream(
        'garansi-bca-' . str_replace(' ', '-', strtolower($data->nama_murid)) . '.pdf'
    );
}

public function approve($id)
{
    $p = PengajuanGaransi::findOrFail($id);

    $murid = BukuInduk::where('nim', $p->nim)->first();

    $tanggal = Carbon::today();

    // simpan ke garansi_bca
    GaransiBCA::create([
        'nim'   => $murid->nim,
        'nama_murid' => $murid->nama,
        'tanggal_masuk' => $murid->tgl_masuk,
        'nama_orang_tua_wali' => $murid->orangtua,
        'bimba_unit' => $murid->bimba_unit,
        'tanggal_diberikan' => $tanggal,
        'sumber' => 'pemberian', // 🔥 tambah ini
    ]);

    // update buku induk
    $murid->update([
        'tgl_surat_garansi' => $tanggal
    ]);

    // update status
    $p->update([
        'status' => 'disetujui'
    ]);

    return back()->with('success', 'Garansi berhasil disetujui');
}

public function reject($id)
{
    $p = PengajuanGaransi::findOrFail($id);

    $p->update([
        'status' => 'ditolak'
    ]);

    return back()->with('success', 'Pengajuan ditolak');
}
}
