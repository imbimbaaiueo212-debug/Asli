<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PendapatanTunjangan;
use App\Models\PembayaranTunjangan;
use App\Models\Profile;

class PotonganTunjangan extends Model
{
    use HasFactory;

    protected $table = 'potongan_tunjangans';

    protected $fillable = [
        'nik',
        'nim',
        'nama',
        'jabatan',
        'status',
        'departemen',

        // ✅ TAMBAHAN BARU
        'bimba_unit',
        'no_cabang',

        'masa_kerja',

        // potongan (nominal)
        'sakit',
        'izin',
        'alpa',
        'tidak_aktif',

        // legacy (jumlah hari)
        'kelebihan',

        // explicit nominal fields
        'kelebihan_nominal',
        'kelebihan_bulan',

        // cash advance
        'cash_advance_id',
        'cash_advance_nominal',
        'cash_advance_note',

        // lainnya
        'bulan',
        'lain_lain',
        'total',
    ];

    protected $casts = [
        'sakit' => 'float',
        'izin' => 'float',
        'alpa' => 'float',
        'tidak_aktif' => 'float',
        'kelebihan' => 'float',
        'kelebihan_nominal' => 'float',
        'cash_advance_nominal' => 'float',
        'lain_lain' => 'float',
        'total' => 'float',
    ];

    /**
     * Relasi ke pendapatan (optional)
     */
    public function pendapatan()
    {
        return $this->belongsTo(PendapatanTunjangan::class, 'pendapatan_id');
    }

    /**
     * Relasi ke profile berdasarkan NIK
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'nik', 'nik');
    }

    /**
     * Relasi ke pembayaran
     */
    public function pembayaran()
    {
        return $this->hasMany(PembayaranTunjangan::class);
    }

    protected static function booted()
{
    static::addGlobalScope(new UnitScope);
}
}
