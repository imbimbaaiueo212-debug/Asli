<?php

namespace App\Observers;

use App\Models\BukuInduk;
use App\Models\Profile;
use App\Services\JadwalSyncService;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\DB;

class BukuIndukObserver
{
    public function created(BukuInduk $bukuInduk)
    {
        JadwalSyncService::syncMurid($bukuInduk);

        DB::afterCommit(function () use ($bukuInduk) {
            $this->syncProfile($bukuInduk);
        });
    }

    public function updated(BukuInduk $bukuInduk)
    {
        $oldGuru = $bukuInduk->getOriginal('guru');

        if ($bukuInduk->wasChanged([
            'kode_jadwal',
            'guru',
            'kelas',
            'jenis_kbm',
            'status'
        ])) {

            JadwalSyncService::syncMurid($bukuInduk);

            DB::afterCommit(function () use ($bukuInduk, $oldGuru) {

                // Guru baru
                $this->syncProfile($bukuInduk);

                // Guru lama (penting!)
                if ($oldGuru && $oldGuru !== $bukuInduk->guru) {
                    $oldProfile = Profile::where('nama', $oldGuru)->first();
                    if ($oldProfile) {
                        app(ProfileController::class)->recalculateMuridDanKtr($oldProfile);
                    }
                }

            });
        }
    }

    private function syncProfile(BukuInduk $bukuInduk)
    {
        if (!$bukuInduk->guru) return;

        $profile = Profile::where('nama', $bukuInduk->guru)->first();

        if ($profile) {
            app(ProfileController::class)->recalculateMuridDanKtr($profile);
        }
    }
}