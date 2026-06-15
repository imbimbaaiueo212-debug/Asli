<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\UnitScope;   // TAMBAHKAN INI
use App\Models\Scopes\AktifScope;
use App\Models\BukuInduk;
use App\Models\Profile;

class RekapAbsensi extends Model
{
    use HasFactory;

    protected $table = 'rekap_absensi';

    protected $fillable = [
        'nik', 'nama_relawan', 'jabatan', 'status_karyawan', 'departemen',
        'bimba_unit', 'no_cabang', 'jumlah_murid', 'jumlah_rombim', 'penyesuaian_rb',
        'srj_108', 'srj_109', 'srj_110',
        'srj_111', 'srj_112', 'srj_113', 'srj_114', 'srj_115', 'srj_116',
        'sks_208', 'sks_209', 'sks_210', 'sks_211',
        's6_308', 's6_309', 's6_310', 's6_311',
        's6_306_2', 's6_307_3', 's6_306_4', 's6_307_5'
    ];

    // GUNAKAN CLASS UnitScope YANG SUDAH BENAR (implements Scope)
    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);
        static::addGlobalScope(new AktifScope());
    }

    public function syncFromBukuInduk()
    {
        $guru = Profile::where('nama', $this->nama_relawan)
            ->where('status_karyawan', 'Aktif')
            ->first();

        if (!$guru) {
            return;
        }

        // Update NIK dari profile
        if ($guru->nik && $this->nik !== $guru->nik) {
            $this->nik = $guru->nik;
            $this->saveQuietly();
        }

        // PAKAI UnitScope::class → ini yang bikin withoutGlobalScope JALAN!
        $bukuList = BukuInduk::withoutGlobalScope(UnitScope::class)
            ->where('guru', $guru->nama)
            ->where('status', 'Aktif')
            ->get();

        $srjCols = ['108', '109', '110', '111', '112', '113', '114', '115', '116'];
        $sksCols = ['208', '209', '210', '211'];
        $s6Cols  = ['308', '309', '310', '311'];

        $updateData = array_fill_keys(
            array_merge(
                array_map(fn($v) => 'srj_' . $v, $srjCols),
                array_map(fn($v) => 'sks_' . $v, $sksCols),
                array_map(fn($v) => 's6_'  . $v, $s6Cols)
            ),
            0
        );

        $muridTerhitung = [];

        foreach ($bukuList as $buku) {
            $kode = (string) $buku->kode_jadwal;
            $nim  = $buku->nim;

            if (in_array($kode, $srjCols)) {
                $updateData['srj_' . $kode]++;
                $muridTerhitung[$nim] = true;
            } elseif (in_array($kode, $sksCols)) {
                $updateData['sks_' . $kode]++;
                $muridTerhitung[$nim] = true;
            } elseif (in_array($kode, $s6Cols)) {
                $updateData['s6_' . $kode]++;
                $muridTerhitung[$nim] = true;
            }
        }

        $updateData['jumlah_murid']   = count($muridTerhitung);
        $updateData['jumlah_rombim']  = count(array_filter($updateData, fn($v, $k) => str_starts_with($k, 'srj_') || str_starts_with($k, 'sks_') || str_starts_with($k, 's6_'), ARRAY_FILTER_USE_BOTH));

        $this->update($updateData);
    }
}