<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlipTunjangan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_induk', 'nama_staff', 'jabatan', 'unit', 'tanggal_masuk', 'bulan',
        'tunjangan_pokok','tunjangan_harian','tunjangan_fungsional','tunjangan_kesehatan',
        'tunjangan_kerajinan','komisi_english','komisi_mentor','kekurangan_tunjangan',
        'tunjangan_keluarga','lain_lain_pendapatan','total_pendapatan',
        'sakit','izin','alpa','tidak_aktif','kelebihan_tunjangan','lain_lain_potongan','total_potongan',
        'dibayarkan','bank','no_rekening','atas_nama','email'
    ];

    public function pembayaran()
{
    return $this->belongsTo(PembayaranTunjangan::class, 'nama', 'nama');
}
}
