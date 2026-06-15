<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Komisi extends Model
{
    protected $table = 'komisi';
    protected $guarded = ['id'];

    // DAFTARKAN GLOBAL SCOPE saat booting model (dipanggil sekali)
    protected static function booted()
    {
        // Tambahkan global scope UnitScope (UnitScope sendiri akan aman mengecek auth/console)
        static::addGlobalScope(new UnitScope());

        // Hook saving tetap boleh untuk mengisi no_cabang otomatis
        static::saving(function ($komisi) {
            // pastikan class Unit di-import atau pakai FQCN
            if (!empty($komisi->bimba_unit)) {
                $unit = \App\Models\Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($komisi->bimba_unit))])->first();
                if ($unit) {
                    $komisi->no_cabang = $unit->no_cabang;
                }
            }
        });
    }

    // Scope untuk filter periode
    public function scopePeriode($query, $tahun, $bulan)
    {
        return $query->where('tahun', $tahun)->where('bulan', $bulan);
    }

    // Aksesor: Tampilkan "September 2025"
    public function getNamaBulanAttribute()
    {
        $daftar = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return $daftar[$this->bulan] . ' ' . $this->tahun;
    }

    // Aksesor: Format masa kerja → "7 Thn 11 Bln"
    public function getMasaKerjaFormatAttribute()
    {
        if (!$this->masa_kerja) return '-';
        $tahun = intdiv($this->masa_kerja, 12);
        $bulan = $this->masa_kerja % 12;
        $str = [];
        if ($tahun > 0) $str[] = "$tahun Thn";
        if ($bulan > 0) $str[] = "$bulan Bln";
        return implode(' ', $str);
    }

    // Relasi ke Profile (sesuaikan: di controller Anda sebelumnya ada profile_id)
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
    }

    // Relasi ke Unit berdasarkan nama unit di kolom bimba_unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'bimba_unit', 'biMBA_unit');
    }
    
}
