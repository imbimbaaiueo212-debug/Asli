<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CutiMurid extends Model
{
    protected $table = 'cuti_murid';

    protected $fillable = [
        'buku_induk_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_cuti',
        'alasan',
        'surat_dokter',
        'status',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Relasi
    public function bukuInduk()
    {
        return $this->belongsTo(BukuInduk::class);
    }
}