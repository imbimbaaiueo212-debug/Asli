<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PemesananKaos;
use App\Models\Unit;
use App\Models\Scopes\UnitScope;
use App\Models\BukuInduk;
use App\Models\Produk;
use Carbon\Carbon;

class PemesananKaosController extends Controller
{
    public function index(Request $request)
    {
        $query = PemesananKaos::with('unit');

        // Filter-filter
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->filled('nama_murid')) {
            $query->where('nama_murid', 'like', '%' . $request->nama_murid . '%');
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        $orders = $query->latest()->get();

        // Label Tas
        $tasProduk = Produk::where('jenis', 'like', '%Tas%')->first();
        $tasLabel = $tasProduk?->label ?? 'TASMBA';

        // Rekap Keseluruhan – Pendek & Panjang terpisah dengan kode ...01 untuk panjang
        $rekapKeseluruhan = [
            // === Kaos Lengan Pendek ===
            ['kode' => 'KAS',      'nama_barang' => 'Kaos Anak S (Pendek)',      'jumlah' => $orders->where('size', 'KAS')->sum('kaos')],
            ['kode' => 'KAM',      'nama_barang' => 'Kaos Anak M (Pendek)',      'jumlah' => $orders->where('size', 'KAM')->sum('kaos')],
            ['kode' => 'KAL',      'nama_barang' => 'Kaos Anak L (Pendek)',      'jumlah' => $orders->where('size', 'KAL')->sum('kaos')],
            ['kode' => 'KAXL',     'nama_barang' => 'Kaos Anak XL (Pendek)',     'jumlah' => $orders->where('size', 'KAXL')->sum('kaos')],
            ['kode' => 'KAXXL',    'nama_barang' => 'Kaos Anak XXL (Pendek)',    'jumlah' => $orders->where('size', 'KAXXL')->sum('kaos')],
            ['kode' => 'KAXXXL',   'nama_barang' => 'Kaos Anak XXXL (Pendek)',   'jumlah' => $orders->where('size', 'KAXXXL')->sum('kaos')],
            ['kode' => 'KAXXXLS',  'nama_barang' => 'Kaos Anak XXXLS (Pendek)',  'jumlah' => $orders->where('size', 'KAXXXLS')->sum('kaos')],

            // === Kaos Lengan Panjang (suffix 01) ===
            ['kode' => 'KAS01',    'nama_barang' => 'Kaos Anak S (Panjang)',     'jumlah' => $orders->where('size', 'KAS')->sum('kaos_panjang')],
            ['kode' => 'KAM01',    'nama_barang' => 'Kaos Anak M (Panjang)',     'jumlah' => $orders->where('size', 'KAM')->sum('kaos_panjang')],
            ['kode' => 'KAL01',    'nama_barang' => 'Kaos Anak L (Panjang)',     'jumlah' => $orders->where('size', 'KAL')->sum('kaos_panjang')],
            ['kode' => 'KAXL01',   'nama_barang' => 'Kaos Anak XL (Panjang)',    'jumlah' => $orders->where('size', 'KAXL')->sum('kaos_panjang')],
            ['kode' => 'KAXXL01',  'nama_barang' => 'Kaos Anak XXL (Panjang)',   'jumlah' => $orders->where('size', 'KAXXL')->sum('kaos_panjang')],
            ['kode' => 'KAXXXL01', 'nama_barang' => 'Kaos Anak XXXL (Panjang)',  'jumlah' => $orders->where('size', 'KAXXXL')->sum('kaos_panjang')],
            ['kode' => 'KAXXXLS01','nama_barang' => 'Kaos Anak XXXLS (Panjang)','jumlah' => $orders->where('size', 'KAXXXLS')->sum('kaos_panjang')],

            // === Item Lainnya ===
            ['kode' => 'RBAS',     'nama_barang' => 'RBAS',                      'jumlah' => $orders->sum('rbas')],
            ['kode' => 'BCABS01',  'nama_barang' => 'BCABS01',                   'jumlah' => $orders->sum('bcabs01')],
            ['kode' => 'BCABS02',  'nama_barang' => 'BCABS02',                   'jumlah' => $orders->sum('bcabs02')],
            ['kode' => 'KPK',      'nama_barang' => 'KPK',                        'jumlah' => $orders->sum('kpk')],
            ['kode' => $tasLabel,  'nama_barang' => 'Tas biMBA',                 'jumlah' => $orders->sum('tas')],
        ];

        // Rekap Per Unit
        $rekapPerUnit = [];
        $isAdmin = auth()->check() && auth()->user()->is_admin;

        $unitsQuery = $isAdmin ? Unit::withoutGlobalScope(UnitScope::class) : Unit::query();

        $units = $unitsQuery
            ->whereHas('pemesananKaos', fn($q) => $q->whereIn('id', $orders->pluck('id')))
            ->with(['pemesananKaos' => fn($q) => $q->whereIn('id', $orders->pluck('id'))])
            ->orderBy('biMBA_unit')
            ->get();

        foreach ($units as $unit) {
            $pesanan = $unit->pemesananKaos;
            if ($pesanan->isEmpty()) continue;

            $rekapPerUnit[] = [
                'unit_id'    => $unit->id,
                'unit_label' => $unit->label,

                'kaos_pendek' => [
                    'KAS'     => $pesanan->where('size', 'KAS')->sum('kaos'),
                    'KAM'     => $pesanan->where('size', 'KAM')->sum('kaos'),
                    'KAL'     => $pesanan->where('size', 'KAL')->sum('kaos'),
                    'KAXL'    => $pesanan->where('size', 'KAXL')->sum('kaos'),
                    'KAXXL'   => $pesanan->where('size', 'KAXXL')->sum('kaos'),
                    'KAXXXL'  => $pesanan->where('size', 'KAXXXL')->sum('kaos'),
                    'KAXXXLS' => $pesanan->where('size', 'KAXXXLS')->sum('kaos'),
                ],

                'kaos_panjang' => [
                    'KAS01'    => $pesanan->where('size', 'KAS')->sum('kaos_panjang'),
                    'KAM01'    => $pesanan->where('size', 'KAM')->sum('kaos_panjang'),
                    'KAL01'    => $pesanan->where('size', 'KAL')->sum('kaos_panjang'),
                    'KAXL01'   => $pesanan->where('size', 'KAXL')->sum('kaos_panjang'),
                    'KAXXL01'  => $pesanan->where('size', 'KAXXL')->sum('kaos_panjang'),
                    'KAXXXL01' => $pesanan->where('size', 'KAXXXL')->sum('kaos_panjang'),
                    'KAXXXLS01'=> $pesanan->where('size', 'KAXXXLS')->sum('kaos_panjang'),
                ],

                'lainnya' => [
                    'rbas'    => $pesanan->sum('rbas'),
                    'bcabs01' => $pesanan->sum('bcabs01'),
                    'bcabs02' => $pesanan->sum('bcabs02'),
                    'kpk'     => $pesanan->sum('kpk'),
                    'tas'     => $pesanan->sum('tas'),
                ]
            ];
        }

        // Filter options
        $filterUnits = $isAdmin
            ? Unit::withoutGlobalScope(UnitScope::class)->orderBy('biMBA_unit')->get()
            : Unit::orderBy('biMBA_unit')->get();

        $distinctMurid = PemesananKaos::distinct()
            ->when($request->filled('unit_id'), fn($q) => $q->where('unit_id', $request->unit_id))
            ->orderBy('nama_murid')
            ->pluck('nama_murid');

        return view('pemesanan_kaos.index', compact(
            'orders', 'rekapKeseluruhan', 'rekapPerUnit', 'filterUnits', 'distinctMurid', 'tasLabel'
        ));
    }

    // Method lain tetap sama (create, store, edit, update, destroy, getMuridByUnit)
    // Hanya validasi store & update yang sudah benar seperti sekarang

    public function create()
    {
        $isAdmin = auth()->user()->is_admin ?? false;

        $units = $isAdmin
            ? Unit::withoutGlobalScope(UnitScope::class)->orderBy('biMBA_unit')->get()
            : Unit::orderBy('biMBA_unit')->get();

        $tasOptions = Produk::where(function($query) {
                $query->where('jenis', 'like', '%Tas%')
                      ->orWhere('kategori', 'like', '%Tas%')
                      ->orWhere('label', 'like', '%Tas%')
                      ->orWhere('nama_produk', 'like', '%Tas%');
            })
            ->orderBy('kode')
            ->get(['kode', 'label', 'nama_produk', 'harga']);

        return view('pemesanan_kaos.create', compact('units', 'tasOptions'));
    }

    public function store(Request $request)
{
    $rules = [
        'tanggal'       => 'required|date',
        'nama_murid'    => 'required|string',
        'unit_id'       => 'required|exists:units,id',
        'kaos'          => 'nullable|integer|min:0',
        'kaos_panjang'  => 'nullable|integer|min:0',
        'rbas'          => 'nullable|integer|min:0',
        'bcabs01'       => 'nullable|integer|min:0',
        'bcabs02'       => 'nullable|integer|min:0',
        'kpk'           => 'nullable|integer|min:0',
        'kode_tas'      => 'nullable|string|exists:produk,kode',
        'jumlah_tas'    => 'nullable|integer|min:0',
    ];

    // Wajib size jika ada pesanan kaos
    if ($request->filled('kaos') && $request->kaos > 0 || 
        $request->filled('kaos_panjang') && $request->kaos_panjang > 0) {
        $rules['size'] = 'required|string|in:KAS,KAM,KAL,KAXL,KAXXL,KAXXXL,KAXXXLS';
    }

    if ($request->filled('kode_tas')) {
        $rules['jumlah_tas'] = 'required|integer|min:1';
    }

    $validated = $request->validate($rules);

    // Konversi semua field jumlah menjadi integer 0 jika kosong
    $data = $request->only([
        'no_bukti', 'tanggal', 'unit_id', 'nim', 'nama_murid', 'gol', 'tgl_masuk',
        'lama_bljr', 'guru', 'size', 'kpk', 'kode_tas', 'rbas', 'bcabs01', 'bcabs02',
        'keterangan'
    ]);

    // Set default 0 untuk semua field jumlah
    $data['kaos']        = (int) ($request->input('kaos', 0));
    $data['kaos_panjang'] = (int) ($request->input('kaos_panjang', 0));
    $data['rbas']        = (int) ($request->input('rbas', 0));
    $data['bcabs01']     = (int) ($request->input('bcabs01', 0));
    $data['bcabs02']     = (int) ($request->input('bcabs02', 0));
    $data['kpk']         = (int) ($request->input('kpk', 0));
    $data['jumlah_tas']  = (int) ($request->input('jumlah_tas', 0));

    // Handle tas
    if ($request->filled('kode_tas') && !$request->filled('jumlah_tas')) {
        $data['jumlah_tas'] = 1;
    }
    if (!$request->filled('kode_tas')) {
        $data['jumlah_tas'] = 0;
    }

    PemesananKaos::create($data);

    return redirect()->route('pemesanan_kaos.index')
        ->with('success', 'Data pemesanan berhasil disimpan!');
}

    // edit, update, destroy, getMuridByUnit tetap seperti kode kamu yang sekarang
    // (tidak perlu diubah lagi karena sudah benar)

    public function edit($id)
    {
        $order = PemesananKaos::findOrFail($id);

        $isAdmin = auth()->user()->is_admin ?? false;

        $units = $isAdmin
            ? Unit::withoutGlobalScope(UnitScope::class)->orderBy('biMBA_unit')->get()
            : Unit::orderBy('biMBA_unit')->get();

        $tasOptions = Produk::where(function($query) {
                $query->where('jenis', 'like', '%Tas%')
                      ->orWhere('kategori', 'like', '%Tas%')
                      ->orWhere('label', 'like', '%Tas%')
                      ->orWhere('nama_produk', 'like', '%Tas%');
            })
            ->orderBy('kode')
            ->get(['kode', 'label', 'nama_produk', 'harga']);

        return view('pemesanan_kaos.edit', compact('order', 'units', 'tasOptions'));
    }

    public function update(Request $request, $id)
    {
        $order = PemesananKaos::findOrFail($id);

        $rules = [
            'tanggal'       => 'required|date',
            'nama_murid'    => 'required|string',
            'unit_id'       => 'required|exists:units,id',
            'kaos'          => 'nullable|integer|min:0',
            'kaos_panjang'  => 'nullable|integer|min:0',
            'rbas'          => 'nullable|integer|min:0',
            'bcabs01'       => 'nullable|integer|min:0',
            'bcabs02'       => 'nullable|integer|min:0',
            'kpk'           => 'nullable|integer|min:0',
            'kode_tas'      => 'nullable|string|exists:produk,kode',
            'jumlah_tas'    => 'nullable|integer|min:0',
        ];

        if ($request->filled('kaos') && $request->kaos > 0 || 
            $request->filled('kaos_panjang') && $request->kaos_panjang > 0) {
            $rules['size'] = 'required|string|in:KAS,KAM,KAL,KAXL,KAXXL,KAXXXL,KAXXXLS';
        }

        if ($request->filled('kode_tas')) {
            $rules['jumlah_tas'] = 'required|integer|min:1';
        }

        $request->validate($rules);

        if ($request->filled('kode_tas') && !$request->filled('jumlah_tas')) {
            $request->merge(['jumlah_tas' => 1]);
        }
        if (!$request->filled('kode_tas')) {
            $request->merge(['jumlah_tas' => 0]);
        }

        $order->update($request->all());

        return redirect()->route('pemesanan_kaos.index')
            ->with('success', 'Data pemesanan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $order = PemesananKaos::findOrFail($id);
        $order->delete();

        return redirect()->route('pemesanan_kaos.index')
            ->with('success', 'Data pemesanan berhasil dihapus!');
    }

    public function getMuridByUnit($unit_id)
    {
        $unit = Unit::findOrFail($unit_id);

        $murids = BukuInduk::where('no_cabang', $unit->no_cabang)
            ->where('status', 'Aktif')
            ->orderBy('nama')
            ->get(['id', 'nim', 'nama', 'gol', 'tgl_masuk', 'guru']);

        return response()->json($murids->map(function ($murid) {
            $lamaBelajar = '-';
            if ($murid->tgl_masuk) {
                try {
                    $masuk = Carbon::parse($murid->tgl_masuk);
                    $sekarang = Carbon::now();

                    if ($masuk->greaterThan($sekarang)) {
                        $lamaBelajar = 'Belum mulai';
                    } else {
                        $diff = $masuk->diff($sekarang);
                        $tahun = $diff->y;
                        $bulan = $diff->m;

                        if ($tahun > 0) {
                            $lamaBelajar = $tahun . ' tahun';
                            if ($bulan > 0) $lamaBelajar .= ' ' . $bulan . ' bulan';
                        } elseif ($bulan > 0) {
                            $lamaBelajar = $bulan . ' bulan';
                        } else {
                            $lamaBelajar = '0 bulan';
                        }
                    }
                } catch (\Exception $e) {
                    $lamaBelajar = '-';
                }
            }

            $tglMasukDisplay = $murid->tgl_masuk ? Carbon::parse($murid->tgl_masuk)->format('d-m-Y') : '-';
            $tglMasukSave    = $murid->tgl_masuk ? Carbon::parse($murid->tgl_masuk)->format('Y-m-d') : null;

            return [
                'id'                => $murid->id,
                'nim'               => trim($murid->nim ?? ''),
                'nama'              => trim($murid->nama ?? ''),
                'display'           => trim($murid->nim ?? '') . ' | ' . trim($murid->nama ?? ''),
                'gol'               => trim($murid->gol ?? '-'),
                'tgl_masuk_display' => $tglMasukDisplay,
                'tgl_masuk'         => $tglMasukSave,
                'lama_bljr'         => $lamaBelajar,
                'guru'              => trim($murid->guru ?? '-'),
            ];
        }));
    }
}