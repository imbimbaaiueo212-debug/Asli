<?php

namespace App\Http\Controllers;

use App\Models\DaftarMuridDeposit;
use App\Models\Penerimaan;
use App\Models\Scopes\UnitScope;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DaftarMuridDepositController extends Controller
{
    public function index(Request $request)
{
    Carbon::setLocale('id');
    setlocale(LC_TIME, 'id_ID.UTF-8');

    // Ambil input filter
    $bulan = $request->input('bulan', date('m'));
    $tahun = $request->input('tahun', date('Y'));
    $unit  = $request->input('unit'); // bisa null atau string

    // Siapkan opsi unit (hanya admin)
    $unitOptions = [];
    if (auth()->check() && auth()->user()->is_admin) {
    $unitOptions = Penerimaan::whereNotNull('bimba_unit')
        ->where('bimba_unit', '!=', '')
        ->pluck('bimba_unit')
        ->map(function ($unit) {
            $unit = trim($unit);
            return str_contains($unit, '|') 
                    ? trim(explode('|', $unit)[1]) 
                    : $unit;
        })
        ->unique()
        ->sort()
        ->values()
        ->toArray();
}

    // Query dengan filter
    $query = Penerimaan::whereMonth('tanggal', $bulan)
        ->whereYear('tanggal', $tahun)
        ->where('spp', '>', 0);

    // Filter unit hanya jika admin dan unit dipilih
    if (auth()->check() && auth()->user()->is_admin && $unit && $unit !== '') {
        $query->where('bimba_unit', $unit);
    }

    $penerimaan = $query->orderBy('tanggal', 'asc')->get();

    $muridDeposits = collect();

    $grouped = $penerimaan->groupBy(fn($item) => $item->nim ?: $item->nama_murid ?: 'unknown');

    foreach ($grouped as $transaksiList) {
        if ($transaksiList->count() <= 1) continue;

        $sorted = $transaksiList->sortBy('tanggal')->values();
        $indexDeposit = 0;

        foreach ($sorted->skip(1) as $p) {
            $tanggal = Carbon::parse($p->tanggal);

            $bulanDepositNama = $p->bulan ?? 'Tidak diketahui';
            $tahunDeposit     = $p->tahun ?? $tahun;

            $bulanAngka = $this->bulanKeAngka($bulanDepositNama);
            $namaBulan  = $this->namaBulanIndonesia($bulanAngka);

            $nimVal = !empty(trim($p->nim)) && $p->nim !== '-' ? $p->nim : null;

            $payload = [
                'tanggal_transaksi'  => $tanggal->toDateString(),
                'nim'                => $nimVal,
                'nama_murid'         => $p->nama_murid ?? '-',
                'kelas'              => $p->kelas ?? '-',
                'status'             => $p->status ?? '-',
                'nama_guru'          => $p->nama_guru ?? ($p->guru ?? '-'),
                'jumlah_deposit'     => (int)($p->spp ?? 0),
                'kategori_deposit'   => "Deposit {$namaBulan} {$tahunDeposit}",
                'status_deposit'     => 'Aktif',
                'keterangan_deposit' => "Deposit otomatis dari pembayaran ke-" . ($indexDeposit + 2),
                'penerimaan_id'      => $p->id ?? null,
                'kwitansi'           => $p->kwitansi ?? null,
                'bimba_unit'         => $p->bimba_unit ?? (auth()->user()->bimba_unit ?? null),
                'no_cabang'          => $p->no_cabang ?? (auth()->user()->no_cabang ?? null),
            ];

            $existing = DaftarMuridDeposit::withoutGlobalScope(UnitScope::class)
                ->where('penerimaan_id', $p->id)
                ->orWhere(function ($q) use ($payload) {
                    $q->where('nim', $payload['nim'])
                      ->where('jumlah_deposit', $payload['jumlah_deposit'])
                      ->where('kategori_deposit', $payload['kategori_deposit']);
                })
                ->first();

            if ($existing) {
                $existing->update($payload);
                $muridDeposits->push($existing);
            } else {
                $created = DaftarMuridDeposit::create($payload);
                $muridDeposits->push($created);
            }

            $indexDeposit++;
        }
    }

    return view('daftar_murid_deposit.index', compact(
        'muridDeposits',
        'bulan',
        'tahun',
        'unit',
        'unitOptions'
    ));
}

// Helper tetap sama
private function bulanKeAngka($namaBulan)
{
    $map = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12
    ];
    return $map[strtolower($namaBulan)] ?? 1;
}

private function namaBulanIndonesia($angka)
{
    $daftar = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    return $daftar[$angka] ?? 'Bulan Tidak Valid';
}

}