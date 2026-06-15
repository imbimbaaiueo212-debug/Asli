<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Profile;
use App\Models\RekapProgresif;
use Illuminate\Support\Facades\Log;

class GenerateRekapProgresifBulanan extends Command
{
    protected $signature = 'rekap:generate-bulanan {--force}';
    protected $description = 'Generate Rekap Progresif otomatis untuk bulan sebelumnya (dijalankan tanggal 26 tiap bulan)';

    public function handle()
    {
        $force = $this->option('force');

        // Set locale Indonesia agar nama bulan jadi "februari", "maret", dll
        Carbon::setLocale('id');

        $today = Carbon::now();

        // Ambil BULAN SEBELUMNYA
        $prevMonth = $today->copy()->subMonth()->startOfMonth();
        $bulanNama = strtolower($prevMonth->translatedFormat('F')); // februari, maret, april, dst
        $tahun     = $prevMonth->year;

        $this->info("🚀 Memulai generate Rekap Progresif untuk: {$bulanNama} {$tahun}");

        // Query profile yang lebih fleksibel (sama seperti di controller kamu)
        $profiles = Profile::where(function ($q) {
                $q->whereRaw('LOWER(jabatan) LIKE ?', ['%guru%'])
                  ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%pengajar%'])
                  ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%tutor%'])
                  ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepala unit%'])
                  ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepala bimba%'])
                  ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kepala sekolah%'])
                  ->orWhereRaw('LOWER(jabatan) = ?', ['ku']);
            })
            ->whereNotIn('status_karyawan', ['Resign', 'Keluar', 'Pensiun', 'Non Aktif', 'Non-aktif'])
            ->get();

        $totalGenerated = 0;
        $totalSkipped   = 0;
        $errors         = [];

        foreach ($profiles as $profile) {
            // Cek apakah sudah ada rekap untuk bulan ini
            if (!$force && RekapProgresif::where('nama', $profile->nama)
                ->where('bulan', $bulanNama)
                ->where('tahun', $tahun)
                ->exists()) {
                
                $totalSkipped++;
                continue;
            }

            try {
                // Panggil method di controller (sementara)
                $controller = app(\App\Http\Controllers\RekapProgresifController::class);
                $controller->autoGenerateForPreviousMonth($profile, $bulanNama, $tahun);

                $totalGenerated++;
                $this->info("✅ Berhasil generate: {$profile->nama}");
            } catch (\Throwable $e) {
                $errors[] = ['nama' => $profile->nama, 'error' => $e->getMessage()];
                Log::error("Gagal generate rekap progresif", [
                    'nama'  => $profile->nama,
                    'bulan' => $bulanNama,
                    'tahun' => $tahun,
                    'error' => $e->getMessage()
                ]);
                $this->error("❌ Gagal: {$profile->nama} - {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("✅ Proses selesai!");
        $this->info("Dibuat baru : {$totalGenerated} record");
        $this->info("Dilewati    : {$totalSkipped} record");

        if (count($errors) > 0) {
            $this->error("⚠️ Terdapat " . count($errors) . " error selama proses.");
        }

        return Command::SUCCESS;
    }
}