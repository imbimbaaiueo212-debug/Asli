<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MuridTrial;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoActivateTrial extends Command
{
    protected $signature = 'trial:auto-activate';
    protected $description = 'Mengaktifkan trial murid secara otomatis setelah 24 jam';

    public function handle()
    {
        $batas = now()->subMinutes(1);

$trials = MuridTrial::where('status_trial', 'baru')
    ->whereNull('tanggal_aktif')
    ->where('created_at', '<=', $batas)
    ->get();

        $count = 0;

        foreach ($trials as $trial) {
            $tanggalAktif = Carbon::parse($trial->waktu_submit)->addHours(24);

            $trial->update([
                'status_trial'  => 'aktif',
                'tanggal_aktif' => $tanggalAktif,
            ]);

            $this->info("Trial {$trial->nama} (ID: {$trial->id}) diaktifkan pada {$tanggalAktif}");
            Log::info("Trial diaktifkan otomatis", [
                'id'            => $trial->id,
                'nama'          => $trial->nama,
                'waktu_submit'  => $trial->waktu_submit,
                'tanggal_aktif' => $tanggalAktif,
            ]);

            $count++;
        }

        $this->info("Selesai. Total trial diaktifkan: {$count}");
        Log::info("Selesai auto-activate trial", ['total' => $count]);

        return Command::SUCCESS;
    }
}