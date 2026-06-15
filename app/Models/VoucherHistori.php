<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherHistori extends Model
{
    use HasFactory;

    protected $table = 'voucher_histori';

    protected $fillable = [
        'voucher_lama_id',
        'voucher',
        'tanggal',
        'tanggal_pemakaian',
        'nim',
        'nama_murid',
        'orangtua',
        'telp_hp',
        'nim_murid_baru',
        'nama_murid_baru',
        'orangtua_murid_baru',
        'telp_hp_murid_baru',
        'jumlah_voucher',
        'bukti_penggunaan_path', // <-- Tambahkan ini
    ];

    public function voucherLama()
    {
        return $this->belongsTo(VoucherLama::class);
    }
}
