<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spp extends Model
{
    use HasFactory;

    protected $table = 'spp';

    protected $fillable = [
        'nim',
        'nama_murid',
        'kelas',
        'tahap',
        'gol',
        'kd',
        'spp',
        'stts',
        's',
        'petugas_trial',
        'guru',
        'note',
        'keterangan_spp',
        'file_pernyataan',
        'status_pernyataan',
        'bimba_unit',          // ← tambahkan ini
    ];
}
