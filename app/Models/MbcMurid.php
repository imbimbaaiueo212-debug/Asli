<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MbcMurid extends Model
{
    use HasFactory;

    protected $table = 'mbc_murid';

    protected $fillable = [
        'no_cabang',
        'nama_unit',
        'no_telp',
        'email',
        'alamat',
        'no_pembayaran',
        'nama_murid',
        'kelas',
        'golongan_kode',
        'spp',
        'wali_murid',
        'bill_payment',
        'virtual_account',
    ];
}
