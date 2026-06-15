<?php

namespace App\Events;

use App\Models\Profile;
use App\Models\ProfileHistory;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
        $this->saveHistory();
    }

   protected function saveHistory(): void
{
    $periode = Carbon::now()->format('Y-m');

    $changedBy = $this->getChangedBy();

    try {
        ProfileHistory::create([
            'profile_id'          => $this->profile->id,
            'periode'             => $periode,
            'changed_at'          => Carbon::now(),
            'status_karyawan'     => $this->profile->status_karyawan,
            'tgl_magang'          => $this->profile->tgl_magang,
            'tgl_non_aktif'       => $this->profile->tgl_non_aktif,
            'tgl_resign'          => $this->profile->tgl_resign,
            'tgl_selesai_magang'  => $this->profile->tgl_selesai_magang,
            'tgl_masuk'           => $this->profile->tgl_masuk,
            'jumlah_murid_mba'    => $this->profile->jumlah_murid_mba,
            'jumlah_murid_jadwal' => $this->profile->jumlah_murid_jadwal,
            'jumlah_rombim'       => $this->profile->jumlah_rombim,
            'rb'                  => $this->profile->rb,
            'ktr'                 => $this->profile->ktr,
            'ktr_tambahan'        => $this->profile->ktr_tambahan,
            'rp'                  => $this->profile->rp,
            'masa_kerja'          => $this->profile->masa_kerja,
            'masa_kerja_jabatan'  => $this->profile->masa_kerja_jabatan,
            'data_lengkap'        => $this->profile->toArray(),
            'changed_by'          => $changedBy,
        ]);

        Log::info("✅ ProfileHistory berhasil disimpan", [
            'profile_id' => $this->profile->id,
            'nama'       => $this->profile->nama,
            'periode'    => $periode,
            'changed_by' => $changedBy,
            'changed_at' => Carbon::now()->toDateTimeString()
        ]);

    } catch (\Exception $e) {
        Log::error('❌ Gagal menyimpan ProfileHistory: ' . $e->getMessage(), [
            'profile_id' => $this->profile->id,
            'error'      => $e->getTraceAsString()
        ]);
    }
}

    /**
     * Ambil nama user yang melakukan perubahan
     */
    protected function getChangedBy(): string
    {
        if (Auth::check()) {
            $user = Auth::user();
            return $user->name ?? $user->email ?? $user->username ?? 'Admin';
        }

        return 'Sistem';
    }
}