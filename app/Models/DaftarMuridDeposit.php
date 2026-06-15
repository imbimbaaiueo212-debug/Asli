<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarMuridDeposit extends Model
{
    use HasFactory;

    protected $table = 'daftar_murid_deposits';

    protected $fillable = [
        'tanggal_transaksi',
        'alert',
        'nim',
        'nama_murid',
        'kelas',
        'status',
        'nama_guru',
        'jumlah_deposit',
        'kategori_deposit',
        'status_deposit',
        'keterangan_deposit',
        'penerimaan_id',
        'kwitansi',
        'bimba_unit',
        'no_cabang',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
    ];

    // OTOMATIS FILTER PER UNIT — KECUALI KOLOM UNIK GLOBAL (penerimaan_id, kwitansi)
    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);
    }
}