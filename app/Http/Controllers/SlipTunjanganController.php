<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendapatanTunjangan;
use App\Models\PotonganTunjangan;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\Skim;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class SlipTunjanganController extends Controller
{
    private $bulanMappingAngka = [
        'Januari' => '01','Februari' => '02','Maret' => '03','April' => '04',
        'Mei' => '05','Juni' => '06','Juli' => '07','Agustus' => '08',
        'September' => '09','Oktober' => '10','November' => '11','Desember' => '12'
    ];

    public function index(Request $request)
    {
        $namaList = PendapatanTunjangan::whereIn(
        'nama',
        Profile::pluck('nama')
    )
    ->orderBy('nama')
    ->pluck('nama')
    ->unique()
    ->values();

        $selectedNama  = $request->get('nama');
        $selectedBulan = $request->get('bulan');
        $selectedTahun = $request->get('tahun', date('Y'));

        $bulanKey = null;

        if ($selectedBulan && $selectedTahun) {
            $bulanNumber = $this->bulanMappingAngka[$selectedBulan] ?? $selectedBulan;
            $bulanKey = "{$selectedTahun}-{$bulanNumber}";
        }

        $selectedSlip = null;

        if ($selectedNama) {
            $selectedSlip = PendapatanTunjangan::where('nama',$selectedNama)
                ->when($bulanKey,function($q) use ($bulanKey){
                    $q->where('bulan',$bulanKey);
                })
                ->first();
        }

        if ($selectedSlip) {

            $selectedSlip->tunjangan_kerajinan  = floatval($selectedSlip->kerajinan ?? 0);
            $selectedSlip->komisi_english       = floatval($selectedSlip->english ?? 0);
            $selectedSlip->komisi_mentor        = floatval($selectedSlip->mentor ?? 0);
            $selectedSlip->kekurangan_tunjangan = floatval($selectedSlip->kekurangan ?? 0);
            $selectedSlip->tunjangan_keluarga   = floatval($selectedSlip->tj_keluarga ?? 0);
            $selectedSlip->lain_lain_pendapatan = floatval($selectedSlip->lain_lain ?? 0);

        }

        $selectedPotongan = null;

        if ($selectedNama && $bulanKey) {

            $selectedPotongan = PotonganTunjangan::where('nama',$selectedNama)
                ->where('bulan',$bulanKey)
                ->first();
        }

        if ($selectedSlip && $selectedPotongan) {

            $selectedSlip->potongan_sakit       = floatval($selectedPotongan->sakit ?? 0);
            $selectedSlip->potongan_izin        = floatval($selectedPotongan->izin ?? 0);
            $selectedSlip->potongan_alpa        = floatval($selectedPotongan->alpa ?? 0);
            $selectedSlip->potongan_tidak_aktif = floatval($selectedPotongan->tidak_aktif ?? 0);

            $selectedSlip->potongan_kelebihan = floatval(
                $selectedPotongan->kelebihan_nominal ?? $selectedPotongan->kelebihan ?? 0
            );

            $selectedSlip->potongan_lain_lain = floatval($selectedPotongan->lain_lain ?? 0);

            $selectedSlip->potongan_cash_advance = floatval(
                $selectedPotongan->cash_advance_nominal ?? 0
            );

            $selectedSlip->potongan_cash_advance_note =
                $selectedPotongan->cash_advance_note ?? null;

            $selectedSlip->total_potongan =
                $selectedSlip->potongan_sakit +
                $selectedSlip->potongan_izin +
                $selectedSlip->potongan_alpa +
                $selectedSlip->potongan_tidak_aktif +
                $selectedSlip->potongan_kelebihan +
                $selectedSlip->potongan_lain_lain +
                $selectedSlip->potongan_cash_advance;
        }

        $profile = null;

        $tanggalMasuk = null;
        $masaKerja = null;

        $tunjanganPokok = 0;
        $tunjanganHarian = 0;
        $tunjanganFungsional = 0;
        $tunjanganKesehatan = 0;

        if ($selectedNama) {

            $profile = Profile::where('nama',$selectedNama)->first();

            $profile = null;
$tanggalMasuk = null;
$masaKerja = null;
$nik = '-';  // default fallback

if ($selectedNama) {
    $profile = Profile::where('nama', $selectedNama)->first();

    if ($profile) {
        $tanggalMasuk = $profile->tgl_masuk;
        $masaKerja    = $profile->masa_kerja;
        $nik          = $profile->nik ?? '-';  // ambil NIK dari Profile, fallback ke '-'

        $tunjangan = $this->getTunjanganPokokHarianFungsionalKesehatan($profile);

        $tunjanganPokok      = $tunjangan['pokok'];
        $tunjanganHarian     = $tunjangan['harian'];
        $tunjanganFungsional = $tunjangan['fungsional'];
        $tunjanganKesehatan  = $tunjangan['kesehatan'];

        // Push NIK ke objek slip (penting untuk view & PDF)
        if ($selectedSlip) {
            $selectedSlip->nik = $nik;
        }
    }
}
        }

        if ($selectedSlip) {

            $calculatedThp =
                $tunjanganPokok +
                $tunjanganHarian +
                $tunjanganFungsional +
                $tunjanganKesehatan;

            $dbThp = floatval($selectedSlip->thp ?? 0);

            $selectedSlip->thp = $dbThp > 0 ? $dbThp : $calculatedThp;
        }

        $unitData = Unit::first();

        return view('slip-tunjangan.index',compact(
            'namaList',
            'selectedNama',
            'selectedBulan',
            'selectedTahun',
            'selectedSlip',
            'selectedPotongan',
            'unitData',
            'tanggalMasuk',
            'masaKerja',
            'tunjanganPokok',
            'tunjanganHarian',
            'tunjanganFungsional',
            'tunjanganKesehatan',
            'profile'
        ));
    }

    public function previewPDF(Request $request)
    {

        $selectedNama  = $request->get('nama');
        $selectedBulan = $request->get('bulan');
        $selectedTahun = $request->get('tahun',date('Y'));

        if(!$selectedNama){
            abort(400,'Nama harus diisi');
        }

        $bulanKey = null;

        if($selectedBulan && $selectedTahun){

            $bulanNumber = $this->bulanMappingAngka[$selectedBulan] ?? $selectedBulan;
            $bulanKey = "{$selectedTahun}-{$bulanNumber}";
        }

        $selectedSlip = PendapatanTunjangan::where('nama',$selectedNama)
            ->when($bulanKey,function($q) use ($bulanKey){
                $q->where('bulan',$bulanKey);
            })
            ->first();

        if($selectedSlip){

            $selectedSlip->tunjangan_kerajinan  = floatval($selectedSlip->kerajinan ?? 0);
            $selectedSlip->komisi_english       = floatval($selectedSlip->english ?? 0);
            $selectedSlip->komisi_mentor        = floatval($selectedSlip->mentor ?? 0);
            $selectedSlip->kekurangan_tunjangan = floatval($selectedSlip->kekurangan ?? 0);
            $selectedSlip->tunjangan_keluarga   = floatval($selectedSlip->tj_keluarga ?? 0);
            $selectedSlip->lain_lain_pendapatan = floatval($selectedSlip->lain_lain ?? 0);
        }

        $selectedPotongan = PotonganTunjangan::where('nama',$selectedNama)
            ->when($bulanKey,function($q) use ($bulanKey){
                $q->where('bulan',$bulanKey);
            })
            ->first();

        if($selectedSlip && $selectedPotongan){

            $selectedSlip->potongan_sakit       = floatval($selectedPotongan->sakit ?? 0);
            $selectedSlip->potongan_izin        = floatval($selectedPotongan->izin ?? 0);
            $selectedSlip->potongan_alpa        = floatval($selectedPotongan->alpa ?? 0);
            $selectedSlip->potongan_tidak_aktif = floatval($selectedPotongan->tidak_aktif ?? 0);

            $selectedSlip->potongan_kelebihan =
                floatval($selectedPotongan->kelebihan_nominal ?? $selectedPotongan->kelebihan ?? 0);

            $selectedSlip->potongan_lain_lain =
                floatval($selectedPotongan->lain_lain ?? 0);

            $selectedSlip->potongan_cash_advance =
                floatval($selectedPotongan->cash_advance_nominal ?? 0);

            $selectedSlip->total_potongan =
                $selectedSlip->potongan_sakit +
                $selectedSlip->potongan_izin +
                $selectedSlip->potongan_alpa +
                $selectedSlip->potongan_tidak_aktif +
                $selectedSlip->potongan_kelebihan +
                $selectedSlip->potongan_lain_lain +
                $selectedSlip->potongan_cash_advance;
        }

        $profile = Profile::where('nama',$selectedNama)->first();

        $tanggalMasuk = $profile->tgl_masuk ?? null;
        $masaKerja    = $profile->masa_kerja ?? null;

        $tunjangan = $this->getTunjanganPokokHarianFungsionalKesehatan($profile);

        $tunjanganPokok      = $tunjangan['pokok'];
        $tunjanganHarian     = $tunjangan['harian'];
        $tunjanganFungsional = $tunjangan['fungsional'];
        $tunjanganKesehatan  = $tunjangan['kesehatan'];

        $unitData = Unit::first();

        $pdf = Pdf::loadView('slip-tunjangan.pdf',compact(
            'selectedSlip',
            'tanggalMasuk',
            'masaKerja',
            'tunjanganPokok',
            'tunjanganHarian',
            'tunjanganFungsional',
            'tunjanganKesehatan',
            'unitData',
            'selectedBulan',
            'selectedTahun',
            'profile'
        ))->setPaper('A5','landscape');

        $safeNama = preg_replace('/[^A-Za-z0-9_\-]/','_',$selectedNama);

        return $pdf->stream("Slip_Tunjangan_{$safeNama}.pdf");
    }

    private function getTunjanganPokokHarianFungsionalKesehatan(Profile $profile)
    {

        if(!$profile){
            return ['pokok'=>0,'harian'=>0,'fungsional'=>0,'kesehatan'=>0];
        }

        $masaKerjaBulan = is_numeric($profile->masa_kerja)
            ? intval($profile->masa_kerja)
            : $this->convertMasaKerjaKeBulan($profile->masa_kerja);

        $skim = Skim::whereRaw('LOWER(TRIM(jabatan))=?',[strtolower(trim($profile->jabatan))])
            ->whereRaw('LOWER(TRIM(status))=?',[strtolower(trim($profile->status_karyawan))])
            ->where(function($q) use ($masaKerjaBulan){

                if($masaKerjaBulan >= 24){
                    $q->where('masa_kerja','>= 24 Bulan');
                }else{
                    $q->where('masa_kerja','< 24 Bulan');
                }

            })->first();

        if(!$skim){

            Log::warning("Skim tidak ditemukan untuk {$profile->nama}");

            return ['pokok'=>0,'harian'=>0,'fungsional'=>0,'kesehatan'=>0];
        }

        return [
            'pokok'      => floatval($skim->tunj_pokok),
            'harian'     => floatval($skim->harian),
            'fungsional' => floatval($skim->fungsional),
            'kesehatan'  => floatval($skim->kesehatan)
        ];
    }

    private function convertMasaKerjaKeBulan($masaKerja)
    {

        if(!$masaKerja) return 0;

        preg_match('/(\d+)\s*tahun/i',$masaKerja,$tahun);
        preg_match('/(\d+)\s*bulan/i',$masaKerja,$bulan);

        $total = 0;

        if(isset($tahun[1])) $total += intval($tahun[1]) * 12;
        if(isset($bulan[1])) $total += intval($bulan[1]);

        return $total;
    }
}