<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'no_cabang',
        'biMBA_unit',
        'staff_sos',
        'telp',
        'email',
        'bank_nama',
        'bank_nomor',
        'bank_atas_nama',
        'alamat_jalan',
        'alamat_rt_rw',
        'alamat_kode_pos',
        'alamat_kel_des',
        'alamat_kecamatan',
        'alamat_kota_kab',
        'alamat_provinsi',
    ];

    /**
     * Accessor: Nama unit yang akan dipakai di dropdown & tampilan
     */
    public function getNamaUnitAttribute()
    {
        return strtoupper($this->biMBA_unit);
    }

    /**
     * Accessor: Label lengkap untuk dropdown (contoh: GRIYA PESONA MADANI (05141))
     */
    public function getLabelAttribute()
    {
        return strtoupper($this->biMBA_unit) . ' (' . $this->no_cabang . ')';
    }

    /**
     * Accessor: Hanya kode unit (tanpa cabang) — berguna kalau butuh clean code
     */
    public function getKodeUnitAttribute()
    {
        return strtoupper($this->biMBA_unit);
    }

    /**
     * Scope global biar semua query otomatis ter-filter berdasarkan unit user (kalau bukan admin)
     */
    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);
    }

    /**
     * Relasi ke Profile (jika diperlukan di masa depan)
     */
    public function profiles()
    {
        return $this->hasMany(\App\Models\Profile::class, 'bimba_unit', 'biMBA_unit');
    }

    /**
     * Casting otomatis biar no_cabang selalu 5 digit (contoh: 05141)
     */
    protected $casts = [
        'no_cabang' => 'string',
    ];

    /**
     * Mutator: Pastikan no_cabang selalu 5 digit saat disimpan
     */
    public function setNoCabangAttribute($value)
    {
        $this->attributes['no_cabang'] = str_pad(ltrim($value, '0'), 5, '0', STR_PAD_LEFT);
    }
    public function unit()
{
    // Pastikan kolom 'unit_id' di tabel users sudah terisi ID yang benar dari tabel units
    return $this->belongsTo(Unit::class, 'unit_id');
}
/**
 * Relasi: Satu Unit memiliki banyak pemesanan kaos
 */
public function pemesananKaos()
{
    return $this->hasMany(PemesananKaos::class, 'unit_id');
}
}