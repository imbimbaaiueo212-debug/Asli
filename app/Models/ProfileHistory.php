<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileHistory extends Model
{
    protected $table = 'profile_histories';

    protected $fillable = [
        'profile_id', 
        'periode', 
        'status_karyawan',
        'tgl_magang', 
        'tgl_non_aktif', 
        'tgl_resign',
        'tgl_keluar',
        'keterangan_keluar', 
        'tgl_selesai_magang', 
        'tgl_masuk',
        'jumlah_murid_mba', 
        'jumlah_murid_jadwal', 
        'jumlah_rombim',
        'rb', 
        'ktr', 
        'ktr_tambahan', 
        'rp',
        'masa_kerja', 
        'masa_kerja_jabatan', 
        'data_lengkap', 
        'changed_by',
    ];

    protected $casts = [
        'tgl_magang'         => 'date',
        'tgl_non_aktif'      => 'date',
        'tgl_resign'         => 'date',
        'tgl_selesai_magang' => 'date',
        'tgl_masuk'          => 'date',
        'tgl_keluar'         => 'date',
        'data_lengkap'       => 'array',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}