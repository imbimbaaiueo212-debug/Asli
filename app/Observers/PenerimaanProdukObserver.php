<?php

namespace App\Observers;

use App\Models\PenerimaanProduk;
use App\Models\DataProduk;
use Carbon\Carbon;

class PenerimaanProdukObserver
{
    public function created(PenerimaanProduk $penerimaan)
    {
        $this->updateTerimaRekap($penerimaan);
    }

    public function updated(PenerimaanProduk $penerimaan)
    {
        // Hanya jika jumlah atau tanggal berubah
        if ($penerimaan->isDirty(['jumlah', 'tanggal', 'label'])) {
            $this->updateTerimaRekap($penerimaan);
            // Jika tanggal berubah, update juga periode lama
            if ($penerimaan->isDirty('tanggal')) {
                $oldPeriode = Carbon::parse($penerimaan->getOriginal('tanggal'))->format('Y-m');
                $this->syncPeriode($oldPeriode);
            }
        }
    }

    public function deleted(PenerimaanProduk $penerimaan)
    {
        $this->updateTerimaRekap($penerimaan);
    }

    private function updateTerimaRekap(PenerimaanProduk $penerimaan)
    {
        $periode = Carbon::parse($penerimaan->tanggal)->format('Y-m');

        $this->syncPeriode($periode);
    }

    private function syncPeriode(string $periode)
    {
        $totalPerLabel = PenerimaanProduk::select('label', \DB::raw('COALESCE(SUM(jumlah), 0) as total'))
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])
            ->groupBy('label')
            ->pluck('total', 'label');

        DataProduk::where('periode', $periode)
            ->chunkById(200, function ($records) use ($totalPerLabel) {
                foreach ($records as $record) {
                    $terimaBaru = $totalPerLabel->get($record->label, 0);
                    if ($record->terima != $terimaBaru) {
                        $record->terima = $terimaBaru;
                        $record->saveQuietly();
                    }
                }
            });
    }
}