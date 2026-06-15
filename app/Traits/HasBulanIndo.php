<?php

namespace App\Traits;

trait HasBulanIndo
{
    private function bulanIndoMap(): array
    {
        return [
            'januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,'mei'=>5,'juni'=>6,
            'juli'=>7,'agustus'=>8,'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12,
        ];
    }
    private function bulanNama(int $bulan): string
    {
        $flip = array_flip($this->bulanIndoMap());
        return $flip[$bulan] ?? 'januari';
    }
    private function bulanAngka(string $nama): int
    {
        $map = $this->bulanIndoMap();
        return $map[strtolower($nama)] ?? 1;
    }
}
