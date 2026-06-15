<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ImbalanRekapController;
use Carbon\Carbon;

class GenerateImbalanRekapMonthly extends Command
{
    protected $signature = 'imbalan:generate-bulan-ini {periode? : Nama bulan dan tahun, contoh: "Mei 2026"}';
    protected $description = 'Generate ImbalanRekap untuk bulan tertentu (default: bulan lalu)';

    public function handle()
    {
        $periode = $this->argument('periode');

        if ($periode) {
            $labelBulan = trim($periode);
            $this->info("🚀 Generate manual untuk: {$labelBulan}");
        } else {
            $labelBulan = Carbon::now()->subMonth()->locale('id')->translatedFormat('F Y');
            $this->info("🚀 Generate otomatis untuk bulan lalu: {$labelBulan}");
        }

        $controller = app(ImbalanRekapController::class);
        $result = $controller->createRekapsForPeriode($labelBulan);

        $this->info("✅ Berhasil: {$result['created']} baru, {$result['updated']} update untuk {$labelBulan}");

        if (count($result['errors']) > 0) {
            $this->warn("⚠️ Ada " . count($result['errors']) . " error:");
            foreach ($result['errors'] as $error) {
                $this->error("   - " . $error);
            }
        } else {
            $this->info("🎉 Tidak ada error.");
        }

        return Command::SUCCESS;
    }
}