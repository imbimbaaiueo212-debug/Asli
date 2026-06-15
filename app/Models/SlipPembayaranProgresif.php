<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Model;

class SlipPembayaranProgresif extends Model
{
    protected $table = 'slip_pembayaran_progresif';

    protected $fillable = [
        'no_induk','nama_staff','jabatan','unit_bimba','tgl_masuk','bulan_bayar',
        'am1','am2','mgrs','mdf','bnf1','bnf2','mb','mt','mbe','mte',
        'spp_bimba','spp_english',
        'total_fm','nilai_progresif','total_komisi',
        'komisi_mb_bimba','komisi_mt_bimba','komisi_mb_english','komisi_mt_english',
        'komisi_asku','total_pendapatan',
        'kekurangan_progresif','kelebihan_progresif',
        'bank','no_rekening','atas_nama','jumlah_dibayarkan'
    ];

    protected static function booted()
{
    static::addGlobalScope(new UnitScope);
}
}
