<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DurasiKegiatan extends Model
{
    use HasFactory;

    protected $table = 'durasi_kegiatan';

    protected $fillable = ['waktu_mgg', 'waktu_bln'];
}
