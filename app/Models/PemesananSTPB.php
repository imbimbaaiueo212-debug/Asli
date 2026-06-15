<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PemesananSTPB extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_stpb';

    protected $fillable = [
        'nim','nama_murid','tmpt_lahir','tgl_lahir','tgl_masuk',
        'nama_orang_tua','level','tgl_level','minggu','keterangan',
        'unit_id',     // ← TAMBAHKAN INI
        'tgl_lulus',   // ← TAMBAHKAN INI
        'tgl_pemesanan', //baru
    ];

    protected $casts = [
        'tgl_lahir'  => 'date',
        'tgl_masuk'  => 'date',
        'tgl_level'  => 'date',
        'tgl_lulus'  => 'date', // ← cast tanggal lulus
        'tgl_pemesanan' => 'date', //baru
    ];
    /**
 * Hitung minggu ke-berapa dalam bulan berdasarkan tgl_pemesanan (selalu 1-5).
 *
 * @return int|null
 */
public function getMingguDalamBulanAttribute(): ?int
{
    if (!$this->tgl_pemesanan) {
        return null;
    }

    $hari = Carbon::parse($this->tgl_pemesanan)->day;

    return (int) ceil($hari / 7);
}
public function bukuInduk()
{
    return $this->belongsTo(BukuInduk::class, 'nim', 'nim');
}
public function unit()
{
    return $this->belongsTo(Unit::class);
}
}