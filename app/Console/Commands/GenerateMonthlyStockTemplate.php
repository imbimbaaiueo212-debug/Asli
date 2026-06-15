<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Unit;
use App\Models\Produk;
use App\Models\DataProduk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyStockTemplate extends Command
{
    protected $signature = 'stock:generate-template {--periode=}';
    protected $description = 'Generate template rekap stok bulanan secara otomatis setiap tanggal 1';

    public function handle()
    {
        $periode = $this->option('periode') 
            ? $this->option('periode') 
            : Carbon::now()->format('Y-m');

        $this->info("🚀 Memulai Generate Template untuk periode: {$periode}");

        // Ambil semua unit yang aktif (sesuaikan jika ada kolom status)
        $units = Unit::whereNotNull('biMBA_unit')
                     ->where('biMBA_unit', '!=', '')
                     ->get();

        if ($units->isEmpty()) {
            $this->error("❌ Tidak ada unit yang ditemukan.");
            Log::error("Auto Generate Template gagal: Tidak ada unit");
            return 1;
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($units as $unit) {
            try {
                $this->info("📍 Processing Unit: {$unit->no_cabang} | {$unit->biMBA_unit} (ID: {$unit->id})");

                $result = $this->generateForUnit($unit->id, $periode);

                $this->info("   ✅ Berhasil → Baru: {$result['created']} | Diupdate: {$result['updated']}");
                
                Log::info("Auto Generate Template berhasil", [
                    'unit_id'   => $unit->id,
                    'unit_name' => $unit->biMBA_unit,
                    'periode'   => $periode,
                    'created'   => $result['created'],
                    'updated'   => $result['updated']
                ]);

                $successCount++;

            } catch (\Exception $e) {
                $failedCount++;
                $this->error("❌ Gagal unit {$unit->no_cabang}: " . $e->getMessage());
                Log::error("Auto Generate Template gagal", [
                    'unit_id' => $unit->id,
                    'unit_name' => $unit->biMBA_unit ?? 'unknown',
                    'periode' => $periode,
                    'error'   => $e->getMessage(),
                    'trace'   => $e->getTraceAsString()
                ]);
            }
        }

        $this->newLine();
        $this->info("🎉 Proses selesai.");
        $this->info("✅ Berhasil : {$successCount} unit");
        $this->info("❌ Gagal    : {$failedCount} unit");
    }

    /**
     * Generate Template untuk 1 Unit
     */
    private function generateForUnit(int $unitId, string $periode)
    {
        $produks = Produk::where('pendataan', 1)
                    ->orderBy('kode')
                    ->get();

        if ($produks->isEmpty()) {
            throw new \Exception("Tidak ada produk dengan pendataan = 1");
        }

        $prevPeriode = Carbon::createFromFormat('Y-m', $periode)
                            ->subMonth()
                            ->format('Y-m');

        // 1. Sync Saldo Awal dari bulan sebelumnya
        $this->syncSaldoAwalFromPrevious($periode, $unitId);

        $created = 0;
        $updated = 0;

        // Ambil data saldo awal dari periode sebelumnya
        $prevData = DataProduk::where('periode', $prevPeriode)
            ->where('unit_id', $unitId)
            ->get(['kode', 'opname', 'sld_akhir'])
            ->mapWithKeys(function ($item) {
                $nilai = ($item->opname !== null && $item->opname != 0)
                            ? (int) $item->opname 
                            : (int) $item->sld_akhir;
                return [$item->kode => $nilai];
            });

        foreach ($produks as $produk) {
            $record = DataProduk::firstOrNew([
                'kode'     => $produk->kode,
                'periode'  => $periode,
                'unit_id'  => $unitId,
            ]);

            $sldAwalBaru = $prevData->get($produk->kode, 0);

            if ($record->exists) {
                $record->jenis     = $produk->jenis;
                $record->label     = $produk->label;
                $record->satuan    = $produk->satuan;
                $record->harga     = $produk->harga;
                $record->min_stok  = $produk->min_stok ?? 10;
                $record->sld_awal  = $sldAwalBaru;
                $record->sld_akhir = $sldAwalBaru;
                $record->saveQuietly();
                $updated++;
            } else {
                $record->fill([
                    'jenis'     => $produk->jenis,
                    'label'     => $produk->label,
                    'satuan'    => $produk->satuan,
                    'harga'     => $produk->harga,
                    'min_stok'  => $produk->min_stok ?? 10,
                    'sld_awal'  => $sldAwalBaru,
                    'sld_akhir' => $sldAwalBaru,
                    'terima'    => 0,
                    'pakai'     => 0,
                    'opname'    => null,
                ]);
                $record->save();
                $created++;
            }
        }

        // 2. Sync Transaksi Bulan Ini
        $this->syncTerimaFromPenerimaan($periode, $unitId);
        $this->syncPakaiFromPemakaian($periode, $unitId);
        $this->syncSldAwalFromOpname($periode, $unitId);

        return compact('created', 'updated');
    }

    // ====================== SYNC METHODS (Copy dari Controller) ======================

    private function syncSaldoAwalFromPrevious(string $periode, ?int $unitId = null)
    {
        $prevPeriode = Carbon::createFromFormat('Y-m', $periode)
            ->subMonth()
            ->format('Y-m');

        $prevQuery = DataProduk::where('periode', $prevPeriode);
        if ($unitId) $prevQuery->where('unit_id', $unitId);

        $prevData = $prevQuery
            ->get(['kode', 'opname', 'sld_akhir'])
            ->mapWithKeys(function ($item) {
                $nilai = ($item->opname !== null && $item->opname != 0)
                    ? $item->opname
                    : $item->sld_akhir;
                return [$item->kode => (int) $nilai];
            });

        $currentQuery = DataProduk::where('periode', $periode);
        if ($unitId) $currentQuery->where('unit_id', $unitId);

        $currentQuery->chunkById(200, function ($records) use ($prevData) {
            foreach ($records as $record) {
                $nilaiBaru = $prevData->get($record->kode, 0);
                if ($record->sld_awal != $nilaiBaru) {
                    $record->sld_awal = $nilaiBaru;
                    $record->sld_akhir = $nilaiBaru;
                    $record->saveQuietly();
                }
            }
        });
    }

    private function syncTerimaFromPenerimaan(string $periode, ?int $unitId = null)
    {
        $query = \App\Models\PenerimaanProduk::select('label', DB::raw('COALESCE(SUM(jumlah),0) as total'))
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode]);

        if ($unitId) $query->where('unit_id', $unitId);

        $penerimaans = $query->groupBy('label')->pluck('total', 'label');

        $dataQuery = DataProduk::where('periode', $periode);
        if ($unitId) $dataQuery->where('unit_id', $unitId);

        $dataQuery->chunkById(200, function ($records) use ($penerimaans) {
            foreach ($records as $record) {
                $baru = $penerimaans->get($record->label, 0);

                if ($record->terima != $baru) {
                    $record->terima = $baru;
                }

                $stokTeoritis = (int)$record->sld_awal + (int)$record->terima - (int)$record->pakai;
                $sldAkhirBaru = max(0, $stokTeoritis);

                if ($record->sld_akhir != $sldAkhirBaru) {
                    $record->sld_akhir = $sldAkhirBaru;
                }

                $record->saveQuietly();
            }
        });
    }

    private function syncPakaiFromPemakaian(string $periode, ?int $unitId = null)
    {
        $query = \App\Models\PemakaianProduk::select('label', DB::raw('COALESCE(SUM(jumlah),0) as total'))
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode]);

        if ($unitId) $query->where('unit_id', $unitId);

        $pemakaian = $query->groupBy('label')->pluck('total', 'label');

        $dataQuery = DataProduk::where('periode', $periode);
        if ($unitId) $dataQuery->where('unit_id', $unitId);

        $dataQuery->chunkById(200, function ($records) use ($pemakaian) {
            foreach ($records as $record) {
                $baru = $pemakaian->get($record->label, 0);

                if ($record->pakai != $baru) {
                    $record->pakai = $baru;
                }

                $stokTeoritis = (int)$record->sld_awal + (int)$record->terima - (int)$record->pakai;
                $sldAkhirBaru = max(0, $stokTeoritis);

                if ($record->sld_akhir != $sldAkhirBaru) {
                    $record->sld_akhir = $sldAkhirBaru;
                }

                $record->saveQuietly();
            }
        });
    }

    private function syncSldAwalFromOpname(string $periode, ?int $unitId = null)
    {
        $query = DataProduk::where('periode', $periode);
        if ($unitId) $query->where('unit_id', $unitId);

        $query->chunkById(200, function ($records) {
            foreach ($records as $record) {
                $saldoSistem = (int)$record->sld_awal + (int)$record->terima - (int)$record->pakai;
                $saldoSistem = max(0, $saldoSistem);

                $hasOpname = $record->opname !== null && (int)$record->opname > 0;
                $fisik = $hasOpname ? (int)$record->opname : $saldoSistem;

                $selisih = $fisik - $saldoSistem;
                $nilai = abs($selisih) * (int)$record->harga;

                $status = !$hasOpname ? 'BELUM OPNAME' :
                         ($selisih == 0 ? 'COCOK' : ($selisih > 0 ? 'LEBIH' : 'KURANG'));

                $record->sld_akhir = $fisik;
                $record->saldo_sistem = $saldoSistem;
                $record->selisih = $selisih;
                $record->nilai = $nilai;
                $record->adjustment_status = $status;

                $record->saveQuietly();
            }
        });
    }
}