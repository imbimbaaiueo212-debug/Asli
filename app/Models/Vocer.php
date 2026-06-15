<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Vocer extends Model
{
    protected $table = 'vocers';

    protected $fillable = [
        'numerator',
        'kategori_v',
        'nilai_v',
        'tgl_peny',
        'st_v',
        'va_murid_humas',
        'va_murid_humas_1',
        'va_murid_humas_2',
        'nama_murid_humas',
        'va_murid_baru',
        'va_murid_baru_1',
        'va_murid_baru_2',
        'nama_murid_baru',
        'keterangan',
    ];
}
