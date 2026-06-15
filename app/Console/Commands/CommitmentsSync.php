<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleFormService;
use App\Models\MuridTrial;
use App\Models\ParentCommitment;
use App\Models\Student;
use App\Models\BukuInduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommitmentsSync extends Command
{
    protected $signature = 'commitments:sync 
                            {sheet=LEMBAR KOMITMEN : Nama sheet di spreadsheet}
                            {--debug : Tampilkan log debug pemetaan kolom dan proses}';

    protected $description = 'Sync Parent Commitments from Google Sheet (ubah status trial ke lanjut_daftar dan buat Student)';

    public function handle(GoogleFormService $forms)
    {
        $sheetName = $this->argument('sheet');
        $debug     = (bool) $this->option('debug');

        // Ambil data dari Google Sheet
        try {
            $rows = $forms->getResponses($sheetName); // Collection<assoc array>
        } catch (\Exception $e) {
            $this->error("Failed to get responses from Google Sheet: ".$e->getMessage());
            return static::FAILURE;
        }

        if ($rows->isEmpty()) {
            $this->info("No data on sheet '{$sheetName}'.");
            return static::SUCCESS;
        }

        // Tampilkan header untuk verifikasi (mode debug)
        if ($debug) {
            $headers = array_keys($rows->first());
            $this->line('Headers found:');
            foreach ($headers as $h) $this->line("- ".$h);
            $this->newLine();
        }

        // Helper ambil nilai berdasar beberapa alias header (case-insensitive, partial match)
        $pick = function (array $row, array $aliases, string $debugLabel = null) use ($debug) {
            foreach ($aliases as $alias) {
                foreach ($row as $h => $v) {
                    if (Str::of($h)->lower()->contains(Str::lower($alias))) {
                        $val = is_string($v) ? trim($v) : $v;
                        if ($debug) $this->line("  ✓ {$debugLabel}: match header '{$h}' (alias '{$alias}') -> '{$val}'");
                        return $val;
                    }
                }
            }
            if ($debug) $this->line("  ✗ {$debugLabel}: not found in row");
            return null;
        };

        // Normalisasi jawaban persetujuan (lebih toleran)
        $normalizeAgree = function ($raw) {
            $val = Str::lower(trim((string) $raw));
            $val = preg_replace('/[^\p{L}\p{N}\s]/u', '', $val);   // buang tanda baca/emoji
            $val = preg_replace('/\s+/', ' ', $val);               // rapikan spasi
            if ($val === 'on') return true;                        // checkbox typical
            return Str::contains($val, [
                'setuju', 'agree', 'yes', 'ya', 'iya', ' i agree ', ' i have read', 'i agree',
                'ya saya setuju', 'saya setuju', 'saya menyetujui',
            ]);
        };

        // Tracker ringkasan
        $processed             = 0; // baris setuju & trial ketemu
        $statusChangedCount    = 0; // status diubah ke lanjut_daftar
        $skippedNonAgreedCount = 0; // dilewati karena "tidak setuju"
        $skippedUnmatchedCount = 0; // dilewati karena trial tidak ketemu

        foreach ($rows as $idx => $row) {
            if ($debug) $this->line("Row #".($idx+1).":");

            // 1) Ambil ID Trial (opsional, kalau tidak ada kita fallback)
            $trialIdRaw = $pick($row, [
                'id trial', 'trial id', 'id_trial', 'trial',
                'id murid trial', 'murid_trial_id',
                'id trial (jangan diubah)', // contoh keterangan
            ], 'ID Trial');

            // 2) Ambil jawaban persetujuan
            $agreeRaw = $pick($row, [
                'Saya sudah membaca Lembar Komitmen Orang Tua dan SETUJU.',
                'setuju','agree','saya sudah membaca','saya telah membaca',
                'persetujuan','saya menyetujui','i agree',
                'sudah membaca dan menyetujui komitmen',
            ], 'AGREE');

            $isAgreed = $normalizeAgree($agreeRaw);
            if ($debug) $this->line("  -> agreed? ".($isAgreed ? 'YES' : 'NO')." (Value: '".(string)$agreeRaw."')");
            if (!$isAgreed) {
                $skippedNonAgreedCount++;
                if ($debug) $this->newLine();
                continue;
            }

            // 3) Temukan MuridTrial (ID → fallback nama+telp+(ortu))
            $murid = null;
            $trialId = (int) ($trialIdRaw ?? 0);
            if ($trialId > 0) {
                $murid = MuridTrial::find($trialId);
                if ($debug) $this->line("  -> Murid by ID {$trialId}: ".($murid ? 'FOUND' : 'NOT FOUND'));
            }

            $childNameFromSheet  = null;
            $phoneFromSheet      = null;
            $parentNameFromSheet = null;

            if (!$murid) {
                // Fallback (nama anak + telp + (opsional) ortu)
                $childNameFromSheet  = $pick($row, ['Nama Anak','nama anak','child','nama peserta','nama'], 'Nama Anak (fallback)');
                $phoneFromSheet      = $pick($row, ['No. Telp/WA','telp','wa','telepon','phone','hp'], 'Phone (fallback)');
                $parentNameFromSheet = $pick($row, ['Nama Orang Tua/Wali','nama orang tua','nama wali','orang tua','parent'], 'Parent Name (fallback)');

                if ($childNameFromSheet && $phoneFromSheet) {
                    // Ambil kandidat berdasarkan nama mirip, urut terbaru
                    $candidates = MuridTrial::where('nama','like','%'.$childNameFromSheet.'%')
                        ->latest('id')
                        ->take(10)
                        ->get();

                    // Normalisasi nomor telp jadi digit saja, cocokkan minimal 6 digit terakhir
                    $digitsSheet = preg_replace('/\D+/', '', (string)$phoneFromSheet);
                    $last6 = Str::substr($digitsSheet, -6) ?: $digitsSheet;

                    $murid = $candidates->first(function ($mt) use ($last6, $parentNameFromSheet) {
                        $mtDigits = preg_replace('/\D+/', '', (string)$mt->no_telp);
                        $okPhone  = $last6 ? Str::endsWith($mtDigits, $last6) : false;
                        $okParent = $parentNameFromSheet ? Str::contains(Str::lower((string)$mt->orangtua), Str::lower($parentNameFromSheet)) : true;
                        return $okPhone && $okParent;
                    });

                    if ($debug) {
                        $this->line("  -> Fallback by name/phone/parent: ".($murid ? "FOUND (id={$murid->id})" : 'NOT FOUND'));
                    }
                }
            }

            if (!$murid) {
                $skippedUnmatchedCount++;
                if ($debug) $this->newLine();
                continue;
            }

            // 4) Data tambahan dari Sheet (opsional)
            $parentName = $pick($row, ['Nama Orang Tua/Wali','nama orang tua','nama wali','orang tua','parent'], 'Parent Name');
            $childName  = $pick($row, ['Nama Anak','nama anak','child','nama peserta'], 'Child Name');
            $phone      = $pick($row, ['No. Telp/WA','telp','wa','telepon','phone','hp'], 'Phone');
            $address    = $pick($row, ['Alamat Lengkap','alamat','address'], 'Address');

            // 5) Transaksi: upsert komitmen, ubah status, buat Student (+ BukuInduk)
            DB::transaction(function () use ($murid, $parentName, $childName, $phone, $address, $debug, &$statusChangedCount) {
                // a) ParentCommitment
                $pc = ParentCommitment::updateOrCreate(
                    ['murid_trial_id' => $murid->id],
                    [
                        'parent_name' => $parentName ?: $murid->orangtua,
                        'child_name'  => $childName  ?: $murid->nama,
                        'phone'       => $phone      ?: $murid->no_telp,
                        'address'     => $address    ?: $murid->alamat,
                        'agreed'      => true,
                        'signed_at'   => now(),
                    ]
                );
                if ($debug) {
                    $this->line("  ✓ ParentCommitment ".($pc->wasRecentlyCreated ? 'created' : 'updated')." for MuridTrial {$murid->id}");
                }

                // b) Paksa status lanjut_daftar jika belum
                if ($murid->status_trial !== 'lanjut_daftar') {
                    $murid->update(['status_trial' => 'lanjut_daftar']);
                    $statusChangedCount++;
                    if ($debug) $this->line("  ✓ Status MuridTrial {$murid->id} -> 'lanjut_daftar'");
                } else {
                    if ($debug) $this->line("  - Status already 'lanjut_daftar'");
                }

                // c) Pastikan Student ada
                if (!$murid->student) {
                    $nim = $this->generateNextNim();
                    while (Student::where('nim', $nim)->lockForUpdate()->exists()) {
                        $nim = $this->incrementNim($nim);
                    }

                    $student = Student::create([
                        'nim'            => $nim,
                        'nama'           => $murid->nama,
                        'kelas'          => $murid->kelas,
                        'tgl_lahir'      => $murid->tgl_lahir,
                        'usia'           => $murid->usia,
                        'orangtua'       => $murid->orangtua,
                        'no_telp'        => $murid->no_telp,
                        'alamat'         => $murid->alamat,
                        'guru_wali'      => $murid->guru_trial,
                        'source'         => 'trial',
                        'murid_trial_id' => $murid->id,
                        'promoted_at'    => now(),
                    ]);
                    if ($debug) $this->line("  ✓ Student created (NIM {$nim})");

                    // Opsional: Buku Induk
                    BukuInduk::updateOrCreate(
                        ['nim' => $student->nim],
                        [
                            'student_id' => $student->id,
                            'nama'       => $student->nama,
                            'status'     => 'Baru',
                            'kelas'      => $student->kelas,
                        ]
                    );
                    if ($debug) $this->line("  ✓ BukuInduk upserted for Student {$student->id}");
                } else {
                    if ($debug) $this->line("  - Student already exists for this MuridTrial");
                }
            });

            $processed++;
            if ($debug) $this->newLine();
        }

        // Ringkasan
        $summary  = "Sinkron komitmen selesai. Diproses: {$processed} baris setuju. ";
        $summary .= "({$statusChangedCount} status diubah ke LANJUT DAFTAR. ";
        $summary .= "Dilewati karena tidak setuju: {$skippedNonAgreedCount} baris. ";
        $summary .= "Dilewati karena tidak cocok: {$skippedUnmatchedCount} baris).";
        $this->info($summary);

        return static::SUCCESS;
    }

    /**
     * Generate NIM berikutnya (cek max dari BukuInduk & Student).
     */
    protected function generateNextNim(): string
    {
        return DB::transaction(function () {
            $lastBI = BukuInduk::whereRaw('nim REGEXP "^[0-9]+$"')
                ->lockForUpdate()
                ->orderByRaw('CAST(nim AS UNSIGNED) DESC')
                ->value('nim');

            $lastST = Student::whereRaw('nim REGEXP "^[0-9]+$"')
                ->lockForUpdate()
                ->orderByRaw('CAST(nim AS UNSIGNED) DESC')
                ->value('nim');

            $max = max((int) $lastBI, (int) $lastST);
            if (!$max) return '010450001';

            $next = $max + 1;
            return str_pad((string)$next, 9, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Increment NIM (9 digit).
     */
    protected function incrementNim(string $nim): string
    {
        $n = (int) $nim;
        $n++;
        return str_pad((string) $n, 9, '0', STR_PAD_LEFT);
    }
}
