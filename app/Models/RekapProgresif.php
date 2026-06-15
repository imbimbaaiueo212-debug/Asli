<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapProgresif extends Model
{
    protected $table = 'rekap_progresif';

    protected $fillable = [
        'nama','jabatan','status','departemen','masa_kerja',
        'spp_bimba','spp_english','total_fm','progresif','komisi','dibayarkan',
        'am1','am2','mgrs','mdf','bnf','bnf2','mb','mt','mb_eb','mt_eb',
        'a','b','c','d','e','f',
        'bnf_1','a_1','b_1','c_1','d_1','e_1',
        'a_2','b_2','c_2','d_2','e_2','f_2',
        'mdf_1','bnf_2_2','mgrs2',
        'a_3','b_3','c_3','d_3','e_3',
        'mb_1','mt_1','mb_eb_1','mt_eb_1','asku', 'tahun', 'bulan', 'bimba_unit', 'no_cabang',

        
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    protected static function booted()
{
    static::addGlobalScope(new UnitScope);
}
    
}
