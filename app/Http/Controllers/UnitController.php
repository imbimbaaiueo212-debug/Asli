<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Revolution\Google\Sheets\Facades\Sheets;
use App\Models\Unit;

class UnitController extends Controller
{
    // Menampilkan semua unit
    public function index()
{
    $query = Unit::query();

    // contoh jika ada search
    if (request('search')) {
        $query->where('biMBA_unit', 'like', '%' . request('search') . '%');
    }

    $units = $query->paginate(12); // penting: paginate di akhir

    return view('unit.index', compact('units'));
}

    // Menampilkan profil unit tertentu
    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        return view('unit.profil', compact('unit'));
    }

    // Form tambah unit baru
    public function create()
    {
        return view('unit.create');
    }

    // Simpan data unit baru
    public function store(Request $request)
    {
        $request->validate([
            'no_cabang' => 'required|string',
            'biMBA_unit' => 'required|string',
            'telp' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        Unit::create($request->all());

        return redirect()->route('unit.index')->with('success', 'Unit berhasil ditambahkan.');
    }

    // Form edit unit
    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        return view('unit.edit', compact('unit'));
    }

    // Update data unit
    public function update(Request $request, $id)
    {
        $request->validate([
            'no_cabang' => 'required|string',
            'biMBA_unit' => 'required|string',
            'telp' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $unit = Unit::findOrFail($id);
        $unit->update($request->all());

        return redirect()->route('unit.index')->with('success', 'Unit berhasil diupdate.');
    }

    // Hapus unit
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('unit.index')->with('success', 'Unit berhasil dihapus.');
    }

    // === Export ke Google Sheet untuk AppSheet ===
public function exportToSheet()
{
    $units = Unit::all()->toArray();

    // Header kolom
    $header = [
        'ID', 'No Cabang', 'biMBA Unit', 'Staff SOS', 'Telp', 'Email',
        'Bank Nama', 'Bank Nomor', 'Bank Atas Nama',
        'Alamat Jalan', 'Alamat RT/RW', 'Kode Pos',
        'Kel/Des', 'Kecamatan', 'Kota/Kab', 'Provinsi'
    ];

    // Data isi
    $rows = [];
    foreach ($units as $u) {
        $rows[] = [
            $u['id'],
            $u['no_cabang'],
            $u['biMBA_unit'],
            $u['staff_sos'],
            $u['telp'],
            $u['email'],
            $u['bank_nama'],
            $u['bank_nomor'],
            $u['bank_atas_nama'],
            $u['alamat_jalan'],
            $u['alamat_rt_rw'],
            $u['alamat_kode_pos'],
            $u['alamat_kel_des'],
            $u['alamat_kecamatan'],
            $u['alamat_kota_kab'],
            $u['alamat_provinsi'],
        ];
    }

    // Tambahkan header di paling atas
    array_unshift($rows, $header);

    // ID spreadsheet dan sheet
    $spreadsheetId = "1U1ybpCWxvO5RI0qnswf6NGgTySwsppaCh3GR_8sGYS8"; 
    $range = "Units!A1";

    // Inisialisasi Google Client
    $client = new \Google\Client();
    $client->setAuthConfig(storage_path('app/google/laravelsheetsproject-4d56608b1c64.json'));
    $client->addScope(\Google\Service\Sheets::SPREADSHEETS);
    $service = new \Google\Service\Sheets($client);

    // Kosongkan isi lama
    $service->spreadsheets_values->clear(
        $spreadsheetId,
        $range,
        new \Google\Service\Sheets\ClearValuesRequest()
    );

    // Update dengan data baru (header + isi sekaligus)
    $service->spreadsheets_values->update(
        $spreadsheetId,
        $range,
        new \Google\Service\Sheets\ValueRange([
            'range' => $range,
            'majorDimension' => 'ROWS',
            'values' => $rows,
        ]),
        ['valueInputOption' => 'RAW']
    );

    return redirect()->back()->with('success', 'Data Unit berhasil dikirim ke Google Sheet untuk AppSheet.');
}

}


