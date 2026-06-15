<?php

namespace App\Observers;

use App\Models\Profile;
use App\Http\Controllers\ImbalanRekapController;
use Carbon\Carbon;

class ProfileImbalanObserver
{
    protected $imbalanController;

    public function __construct()
    {
        $this->imbalanController = new ImbalanRekapController();
    }

    /**
     * Handle the Profile "created" event.
     */
    public function created(Profile $profile): void
    {
        $this->syncRekapImbalan();
    }

    /**
     * Handle the Profile "updated" event.
     */
    public function updated(Profile $profile): void
    {
        // Hanya sync jika field yang memengaruhi imbalan berubah
        if ($profile->isDirty(['jabatan', 'rb', 'ktr', 'rp', 'imbalan_pokok_default', 'status_karyawan', 'bimba_unit', 'no_cabang'])) {
            $this->syncRekapImbalan();
        }
    }

    /**
     * Regenerate rekap untuk bulan berjalan
     */
    protected function syncRekapImbalan(): void
    {
        $labelBulan = Carbon::now()->locale('id')->translatedFormat('F Y');
        $this->imbalanController->createRekapsForPeriode($labelBulan);
    }
}