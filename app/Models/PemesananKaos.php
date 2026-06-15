<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananKaos extends Model
{
    use HasFactory;

    protected $fillable = [
    'no_bukti',
    'tanggal',
    'unit_id',
    'nim',
    'nama_murid',
    'gol',
    'tgl_masuk',
    'lama_bljr',
    'guru',

    'kaos',
    'kaos_panjang',
    'size',
    'size_pendek',
    'size_panjang',
    'kpk',
    'kode_tas',      // ← BARU
    'jumlah_tas',    // ← BARU (opsional, default 1)
    'rbas',
    'bcabs01',
    'bcabs02',
    'sertifikat',
    'stpb',
    'keterangan'
];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}