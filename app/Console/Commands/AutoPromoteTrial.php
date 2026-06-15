<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\MuridTrial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPromoteTrial extends Command
{
    protected $signature = 'trial:auto-promote';
    protected $description = 'Promote trial status dari "baru" menjadi "aktif" setelah 24 jam + buat MuridTrial';

    public function handle()
    {
        $this->info("🔄 Memulai AUTO PROMOTE TRIAL - MODE 24 JAM");

        $students = Student::where('source', 'trial')
            ->where('trial_status', 'baru')
            ->where('created_at', '<=', now()->subDay())   // 24 JAM
            ->get();

        $this->info("📊 Ditemukan {$students->count()} murid trial baru yang sudah lewat 24 jam");

        if ($students->isEmpty()) {
            $this->warn("Tidak ada data yang memenuhi syarat untuk dipromote.");
            return self::SUCCESS;
        }

        foreach ($students as $student) {
            try {
                DB::transaction(function () use ($student) {
                    $student->update(['trial_status' => 'aktif']);

                    $this->createMuridTrial($student);
                });

                $this->info("✅ BERHASIL dipromote: {$student->nama}");
            } catch (\Throwable $e) {
                $this->error("❌ Gagal promote {$student->nama} - " . $e->getMessage());
                Log::error("AutoPromote failed", [
                    'student_id' => $student->id,
                    'nama'       => $student->nama,
                    'error'      => $e->getMessage()
                ]);
            }
        }

        $this->info("🎉 Auto promote selesai.");
        return self::SUCCESS;
    }

    /**
     * Buat record MuridTrial
     */
    private function createMuridTrial(Student $student): void
    {
        // Cek duplikat
        if ($student->murid_trial_id || 
            MuridTrial::where('nama', $student->nama)
                      ->where('no_telp', $student->no_telp)
                      ->exists()) {
            return;
        }

        $trial = MuridTrial::create([
            'nama'                => $student->nama,
            'status_trial'        => 'aktif',
            'kelas'               => $student->kelas ?? 'Reguler',
            'tgl_lahir'           => $student->tgl_lahir,
            'usia'                => $student->usia,
            'orangtua'            => $student->orangtua,
            'no_telp'             => $student->no_telp,
            'alamat'              => $student->alamat,
            'guru_trial'          => $student->guru_wali,
            'bimba_unit'          => $student->bimba_unit,
            'no_cabang'           => $student->no_cabang,
            'tgl_mulai'           => $student->tanggal_masuk ?? now()->format('Y-m-d'),
            'waktu_submit'        => $student->created_at ?? now(),
            'tanggal_trial_baru'  => now()->format('Y-m-d'),
            'tanggal_aktif'       => now()->format('Y-m-d'),
        ]);

        $student->update([
            'murid_trial_id' => $trial->id,
            'trial_status'   => 'aktif'
        ]);

        Log::info("✅ MuridTrial berhasil dibuat setelah 24 jam", [
            'nama'     => $student->nama,
            'trial_id' => $trial->id
        ]);
    }
}