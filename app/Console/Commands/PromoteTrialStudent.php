<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PromoteTrialStudent extends Command
{
    protected $signature = 'trial:promote {id : ID atau NIM murid trial yang akan dipromote}';

    protected $description = 'Promote murid trial menjadi murid resmi → dapat NIM + masuk buku induk + promoted_at diisi';

    public function handle()
    {
        $identifier = $this->argument('id');

        // Cari student berdasarkan ID atau NIM
        $student = Student::where('id', $identifier)
            ->orWhere('nim', $identifier)
            ->first();

        if (!$student) {
            $this->error("Murid dengan ID/NIM '{$identifier}' tidak ditemukan.");
            return 1;
        }

        // Pastikan memang murid trial
        if ($student->source !== 'trial') {
            $this->warn("Murid '{$student->nama}' (NIM: {$student->nim}) bukan murid trial. Sudah resmi.");
            return 1;
        }

        // Sudah punya NIM? (seharusnya tidak)
        if ($student->nim) {
            $this->warn("Murid '{$student->nama}' sudah punya NIM: {$student->nim}. Tidak perlu dipromote lagi.");
            return 1;
        }

        DB::transaction(function () use ($student) {
            // 1. Generate NIM permanen
            $student->nim = $this->nextNim($student->bimba_unit, $student->no_cabang);

            // 2. Ubah status jadi murid resmi
            $student->source = 'direct';        // atau 'regular' kalau kamu pakai
            $student->promoted_at = now();      // penting untuk tracking

            $student->save();

            // 3. Masukkan ke buku induk sebagai murid resmi
            $this->createBukuIndukResmi($student);

            // 4. Update status di tabel MuridTrial (jika ada)
            if ($student->murid_trial_id) {
                $student->muridTrial()->update([
                    'status_trial' => 'lanjut_daftar'
                ]);
            }
        });

        $this->info("SUKSES! Murid berhasil dipromote menjadi murid resmi.");
        $this->table(['Field', 'Value'], [
            ['Nama', $student->nama],
            ['NIM Baru', $student->nim],
            ['Unit', $student->bimba_unit ?? '-'],
            ['Promoted At', $student->promoted_at->format('d-m-Y H:i')],
        ]);

        return 0;
    }

    /**
     * Generate NIM berikutnya (sama persis seperti di ImportStudentsFromForms)
     */
    protected function nextNim(?string $bimbaUnit, ?string $noCabang): string
    {
        $kodeUnit = $noCabang ?? '01045';
        $kodeUnit = preg_replace('/\D/', '', $kodeUnit);
        $kodeUnit = str_pad($kodeUnit, 5, '0', STR_PAD_LEFT);

        $last = \App\Models\Student::whereRaw('LEFT(nim, 5) = ?', [$kodeUnit])
            ->whereNotNull('nim')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(nim, 6) AS UNSIGNED) DESC')
            ->value('nim');

        $seq = $last ? (int) substr($last, 5) + 1 : 1;

        $nim = $kodeUnit . str_pad($seq, 4, '0', STR_PAD_LEFT);

        // Safety loop kalau entah kenapa bentrok
        while (\App\Models\Student::where('nim', $nim)->exists()) {
            $seq++;
            $nim = $kodeUnit . str_pad($seq, 4, '0', STR_PAD_LEFT);
        }

        return $nim;
    }

    /**
     * Buat entri buku induk (sama seperti di command import)
     */
    protected function createBukuIndukResmi(Student $student): void
    {
        \App\Models\BukuInduk::updateOrCreate(
            ['nim' => $student->nim],
            [
                'nama'           => $student->nama,
                'kelas'          => $student->kelas ?? 'Reguler',
                'status'         => 'Aktif',
                'tanggal_masuk'  => $student->tanggal_masuk ?? now(),
                'bimba_unit'     => $student->bimba_unit,
                'no_cabang'      => $student->no_cabang,
                'petugas_trial'  => $student->petugas_trial,
                'tgl_lahir'      => $student->tgl_lahir,
                'tempat_lahir'   => $student->tempat_lahir,
                'alamat_murid'   => $student->alamat,
                'alamat'         => $student->alamat,
                'no_telp_hp'     => $student->no_telp,
                'no_telp'        => $student->no_telp,
                'orangtua'       => $student->orangtua,
                'nama_ayah'      => $student->nama_ayah,
                'nama_ibu'       => $student->nama_ibu,
                'hp_ayah'        => $student->hp_ayah,
                'hp_ibu'         => $student->hp_ibu,
            ]
        );
    }
}