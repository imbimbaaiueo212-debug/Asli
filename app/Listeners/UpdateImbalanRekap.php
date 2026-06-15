<?php

namespace App\Listeners;

use App\Events\ProfileUpdated;
use App\Http\Controllers\ImbalanRekapController;
use Carbon\Carbon;

class UpdateImbalanRekap
{
    public function handle(ProfileUpdated $event)
    {
        $profile = $event->profile;
        $controller = new ImbalanRekapController();
        $bulanSekarang = Carbon::now()->locale('id')->translatedFormat('F Y');
        
        // Regenerate rekap untuk bulan saat ini (atau semua bulan terkait jika perlu)
        $controller->createRekapsForPeriode($bulanSekarang);
    }
}