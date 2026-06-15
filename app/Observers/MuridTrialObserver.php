<?php

namespace App\Observers;

use App\Models\MuridTrial;
use App\Models\Komisi;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MuridTrialObserver
{
    /**
     * Handle "updated" event (termasuk saat guru_trial diubah atau status_trial berubah)
     */
    public function updated(MuridTrial $muridTrial)
    {
        // Hanya proses jika kolom yang relevan berubah
        if (!$muridTrial->isDirty(['guru_trial', 'status_trial', 'tanggal_aktif'])) {
            return;
        }

        // Ambil guru lama & baru (jika updated)
        $guruLama = $muridTrial->getOriginal('guru_trial');
        $guruBaru = $muridTrial->guru_trial;

        $gurus = array_filter(array_unique([$guruLama, $guruBaru]));

        if (empty($gurus)) {
            return;
        }

        // Tentukan periode yang terpengaruh (gunakan tanggal_aktif jika ada, fallback created_at)
        $tahun = $muridTrial->tanggal_aktif 
            ? $muridTrial->tanggal_aktif->year 
            : $muridTrial->created_at->year;

        $bulan = $muridTrial->tanggal_aktif 
            ? $muridTrial->tanggal_aktif->month 
            : $muridTrial->created_at->month;

        foreach ($gurus as $namaGuru) {
            if (empty(trim($namaGuru))) {
                continue;
            }

            $profile = Profile::where('nama', trim($namaGuru))
                ->where('jabatan', 'Guru')
                ->where('status_karyawan', 'Aktif')
                ->first();

            if (!$profile) {
                Log::warning("Profile guru tidak ditemukan untuk update MT otomatis: {$namaGuru}");
                continue;
            }

            // Hitung ulang jumlah murid trial aktif untuk guru ini di periode tersebut
            $mtCount = MuridTrial::where('guru_trial', $namaGuru)
                ->whereIn(DB::raw('LOWER(TRIM(status_trial))'), [
                    'aktif', 'trial aktif', 'active', '1', 'true', 'berjalan', 'ongoing'
                ])
                ->where(function ($q) use ($tahun, $bulan) {
                    $q->whereYear('tanggal_aktif', $tahun)
                      ->whereMonth('tanggal_aktif', $bulan)
                      ->orWhere(function ($sub) use ($tahun, $bulan) {
                          $sub->whereNull('tanggal_aktif')
                              ->whereYear('created_at', $tahun)
                              ->whereMonth('created_at', $bulan);
                      });
                })
                ->count();

            // Update atau buat record komisi hanya untuk MT
            Komisi::updateOrCreate(
                [
                    'nama'  => trim($namaGuru),
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'profile_id'       => $profile->id,
                    'nomor_urut'       => $profile->no_urut ?? 999,
                    'jabatan'          => $profile->jabatan,
                    'status'           => $profile->status_karyawan,
                    'departemen'       => $profile->departemen ?? null,
                    'masa_kerja'       => $profile->masa_kerja ?? '-',
                    'bimba_unit'       => $profile->bimba_unit ?? null,
                    'no_cabang'        => $profile->no_cabang ?? null,
                    'nik'              => $profile->nik ?? null,

                    // Update hanya MT (field lain biarkan apa adanya)
                    'murid_mt_bimba'   => $mtCount,
                    'komisi_mt_bimba'  => $mtCount * 50000,

                    // Adjust kolom akumulasi (jika MT termasuk di dalamnya)
                    'sudah_dibayar'    => DB::raw("GREATEST(0, sudah_dibayar - komisi_mt_bimba + " . ($mtCount * 50000) . ")"),
                    'mb_umum_ku'       => DB::raw("GREATEST(0, mb_umum_ku - komisi_mt_bimba + " . ($mtCount * 50000) . ")"),

                    'keterangan'       => DB::raw("CONCAT(COALESCE(keterangan, ''), ' | MT otomatis updated dari murid trial - " . now()->format('d/m/Y H:i') . "')"),
                ]
            );

            Log::info("MT otomatis diupdate untuk guru {$namaGuru} di periode {$bulan}/{$tahun}: {$mtCount} murid trial");
        }
    }

    /**
     * Handle "deleted" event → hapus pengaruh MT jika record trial dihapus
     */
    public function deleted(MuridTrial $muridTrial)
    {
        if (empty($muridTrial->guru_trial)) {
            return;
        }

        $this->updated($muridTrial); // reuse logika yang sama
    }
}