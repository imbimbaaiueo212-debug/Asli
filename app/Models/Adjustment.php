<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Adjustment extends Model
{
    protected $fillable = [
        'bimba_unit',
        'no_cabang',
        'nik',
        'nama',
        'jabatan',
        'tanggal_masuk',
        'nominal',
        'month',
        'year',
        'type',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    // Accessor untuk nama bulan
    protected $appends = ['month_name'];

public function getMonthNameAttribute()
{
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    return $months[$this->month] ?? '';
}

    // Accessor untuk masa kerja (otomatis hitung)
    public function getMasaKerjaAttribute(): string
{
    if (!$this->tanggal_masuk) {
        return '-';
    }

    try {
        $start = Carbon::parse($this->tanggal_masuk);
    } catch (\Exception $e) {
        return '-';
    }

    $now = Carbon::now();

    // Jika tanggal masuk di masa depan
    if ($start->greaterThan($now)) {
        return 'Belum mulai';
    }

    // Hitung total bulan penuh dari tanggal masuk sampai sekarang
    $totalMonths = $start->diffInMonths($now);

    // Hitung tahun dan sisa bulan
    $years  = intdiv($totalMonths, 12);  // pembagian bulat (integer division)
    $months = $totalMonths % 12;         // sisa bulan (0-11)

    // Bangun string hasil
    $parts = [];

    if ($years > 0) {
        $parts[] = $years . ' tahun';
    }

    $parts[] = $months . ' bulan';

    // Jika masih 0 tahun 0 bulan (kurang dari 1 bulan)
    if ($years == 0 && $months == 0) {
        return '0 bulan';
    }

    return implode(' ', $parts);
}
}