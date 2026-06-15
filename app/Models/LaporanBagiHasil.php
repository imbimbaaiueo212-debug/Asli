<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanBagiHasil extends Model
{
    use HasFactory;

    protected $table = 'laporan_bagi_hasil';

    protected $fillable = [
        'no_cabang',
        'bimba_unit', // ✅ sudah diganti dari 'unit'
        'bulan',
        'nama_bank',
        'no_rekening',
        'atas_nama',

        'bimba_murid_aktif_lalu','bimba_murid_baru','bimba_murid_kembali','bimba_murid_keluar',
        'bimba_murid_aktif_ini','bimba_murid_dhuafa','bimba_murid_bnf','bimba_murid_garansi',
        'bimba_murid_deposit','bimba_murid_piutang','bimba_murid_wajib_spp','bimba_murid_bayar_spp',
        'bimba_murid_belum_bayar','bimba_total_penerimaan_spp','bimba_persentase_bagi_hasil','bimba_jumlah_bagi_hasil',

        'eng_murid_aktif_lalu','eng_murid_baru','eng_murid_kembali','eng_murid_keluar',
        'eng_murid_aktif_ini','eng_murid_dhuafa','eng_murid_bnf','eng_murid_garansi',
        'eng_murid_deposit','eng_murid_piutang','eng_murid_wajib_spp','eng_murid_bayar_spp',
        'eng_murid_belum_bayar','eng_total_penerimaan_spp','eng_persentase_bagi_hasil','eng_jumlah_bagi_hasil'
    ];

    // Accessor otomatis hitung jumlah bagi hasil
    public function getBimbaJumlahBagiHasilAttribute($value)
    {
        return $this->bimba_total_penerimaan_spp * ($this->bimba_persentase_bagi_hasil / 100);
    }

    public function getEngJumlahBagiHasilAttribute($value)
    {
        return $this->eng_total_penerimaan_spp * ($this->eng_persentase_bagi_hasil / 100);
    }
    protected static function booted()
{
    static::addGlobalScope(new UnitScope);
}
}
