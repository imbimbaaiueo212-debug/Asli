<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoPromoteTrial extends Command
{
    protected $signature = 'trial:auto-promote';
    protected $description = 'Promote trial status dari "baru" menjadi "aktif" setelah 24 jam + buat MuridTrial';

    public function handle()
{
    $this->info("🔄 Memulai auto promote trial...");

    $students = Student::where('source', 'trial')
        ->where('trial_status', 'baru')
        ->get();

    $this->info("📊 Ditemukan {$students->count()} murid dengan status 'Trial Baru'");

    if ($students->isEmpty()) {
        $this->warn("Tidak ada data yang memenuhi syarat untuk dipromote.");
        return self::SUCCESS;
    }

    $promoted = 0;

    foreach ($students as $student) {
        try {
            $this->info("🔄 Memproses: {$student->nama} (ID: {$student->id})");

            // Set timestamp kalau belum ada
            if (empty($student->trial_started_at)) {
                $student->trial_started_at = now();
                $this->info("   → trial_started_at di-set");
            }

            $student->trial_status = 'aktif';
            $student->save();

            // Panggil ensureTrialRelation
            $controller = app(\App\Http\Controllers\StudentController::class);
            $controller->ensureTrialRelation($student, 'aktif');

            $promoted++;

            $this->info("✅ BERHASIL dipromote: {$student->nama}");

        } catch (\Throwable $e) {
            $this->error("❌ Gagal promote {$student->nama}: " . $e->getMessage());
            Log::error('AutoPromoteTrial Error', [
                'student_id' => $student->id,
                'nama'       => $student->nama,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString()
            ]);
        }
    }

    $this->newLine();
    $this->info("✅ Auto promote selesai. {$promoted} murid berhasil dipromote.");
    
    return self::SUCCESS;
}
}