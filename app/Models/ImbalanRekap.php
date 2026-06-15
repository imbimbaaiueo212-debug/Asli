<?php

namespace App\Models;

use App\Models\Scopes\AktifScope;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Scopes\UnitScope;

class ImbalanRekap extends Model
{
    protected $table = 'imbalan_rekaps';

    protected $fillable = [
        'profile_id',
        'nama',
        'posisi',
        'status',
        'departemen',
        'masa_kerja',
        'waktu_mgg',
        'waktu_bln',
        'durasi_kerja',
        'persen',
        'ktr',
        'imbalan_pokok',
        'imbalan_lainnya',
        'total_imbalan',
        'insentif_mentor',
        'keterangan_insentif',
        'tambahan_transport',
        'at_hari',
        'kekurangan',
        'bulan_kekurangan',
        'keterangan_kekurangan',
        'kelebihan',
        'bulan_kelebihan',
        'cicilan',
        'keterangan_cicilan',
        'installment_id',
        'total_kelebihan',
        'jumlah_murid',
        'jumlah_spp',
        'kekurangan_spp',
        'kelebihan_spp',
        'jumlah_bagi_hasil',
        'keterangan_bagi_hasil',
        'yang_dibayarkan',
        'catatan',
        'bulan',
        'bimba_unit',
        'no_cabang',
        'status_pembayaran',
        'tanggal_dibayar',
        'dibayar_oleh',
        'potongan_tunjangan_nominal', // pastikan ada di migration
        // TAMBAHKAN DUA BARIS INI
    'lembur_jam',
    'lembur_nominal',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);
        static::addGlobalScope(new AktifScope);
    }

    public function profile()
    {
        return $this->belongsTo(\App\Models\Profile::class, 'nama', 'nama');
    }

    /**
     * Mutator untuk at_hari → otomatis hitung tambahan_transport
     */
    public function setAtHariAttribute($value)
    {
        $value = trim($value ?? '');

        if ($value === '' || $value === '-' || $value === '0' || !is_numeric($value)) {
            $this->attributes['at_hari'] = 0;
            $this->attributes['tambahan_transport'] = 0;
            return;
        }

        $hari = (int) $value;
        if ($hari < 0) $hari = 0;

        $this->attributes['at_hari'] = $hari;
        $this->attributes['tambahan_transport'] = $hari * 24000;
    }

    /**
     * Accessor: tanggal_dibayar sebagai Carbon
     */
    public function getTanggalDibayarAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function setTanggalDibayarAttribute($value)
    {
        $this->attributes['tanggal_dibayar'] = $value ? Carbon::parse($value) : null;
    }

    // ────────────────────────────────────────────────
    // ACCESSOR POTONGAN TUNJANGAN (PERBAIKAN UTAMA)
    // ────────────────────────────────────────────────

    /**
     * Ambil record potongan tunjangan terkait berdasarkan nama + bulan
     */
    public function getPotonganTunjanganAttribute()
    {
        if (!$this->nama || !$this->bulan) {
            return null;
        }

        try {
            $carbonBulan = Carbon::createFromFormat('F Y', $this->bulan, 'id');
            $bulanYmd = $carbonBulan->format('Y-m');
        } catch (\Exception $e) {
            // Fallback ke bulan saat ini jika format bulan gagal
            $bulanYmd = now()->format('Y-m');
        }

        return \App\Models\PotonganTunjangan::where('nama', $this->nama)
            ->where('bulan', $bulanYmd)
            ->first();
    }

    /**
     * Nominal total potongan (langsung ambil dari kolom 'total' di potongan_tunjangans)
     */
    public function getPotonganTunjanganNominalAttribute()
    {
        $pot = $this->potongan_tunjangan;
        return $pot ? (float) ($pot->total ?? 0) : 0;
    }

    /**
     * Status potongan yang detail dan informatif
     * Menampilkan jenis potongan apa saja yang ada (jika total > 0)
     */
    public function getPotonganTunjanganStatusAttribute()
    {
        $pot = $this->potongan_tunjangan;

        // Tidak ada record atau total nol
        if (!$pot || $pot->total <= 0) {
            return 'Tidak Ada Potongan';
        }

        // Kumpulkan jenis potongan yang bernilai > 0
        $jenisPotongan = [];

        if ($pot->sakit > 0) {
            $jenisPotongan[] = "Sakit ({$pot->sakit} hari)";
        }
        if ($pot->izin > 0) {
            $jenisPotongan[] = "Izin ({$pot->izin} hari)";
        }
        if ($pot->alpa > 0) {
            $jenisPotongan[] = "Alpa ({$pot->alpa} hari)";
        }
        if ($pot->tidak_aktif > 0) {
            $jenisPotongan[] = "Tidak Aktif ({$pot->tidak_aktif} hari)";
        }
        if ($pot->kelebihan_nominal > 0 || $pot->kelebihan > 0) {
            $nilai = $pot->kelebihan_nominal > 0 ? $pot->kelebihan_nominal : $pot->kelebihan;
            $jenisPotongan[] = "Kelebihan Rp " . number_format($nilai, 0, ',', '.');
        }
        if ($pot->cash_advance_nominal > 0) {
            $jenisPotongan[] = "Cash Advance Rp " . number_format($pot->cash_advance_nominal, 0, ',', '.');
        }
        if ($pot->lain_lain > 0) {
            $jenisPotongan[] = "Lain-lain Rp " . number_format($pot->lain_lain, 0, ',', '.');
        }

        // Jika ada potongan tapi tidak terdeteksi jenisnya (jarang terjadi)
        if (empty($jenisPotongan)) {
            return 'Ada Potongan (detail tidak terdeteksi)';
        }

        // Gabungkan jadi string yang rapi
        return 'Ada Potongan: ' . implode(', ', $jenisPotongan);
    }


public function adjustments()
{
    return $this->hasMany(Adjustment::class, 'nik', 'nik');
}
public function getAdjustmentSummaryAttribute()
{
    $parts = [];
    if ($this->keterangan_kekurangan) {
        $parts[] = "Kekurangan: " . $this->keterangan_kekurangan;
    }
    if ($this->keterangan_kelebihan) {
        $parts[] = "Kelebihan: " . $this->keterangan_kelebihan;
    }
    return $parts ? implode("\n", $parts) : '-';
}
/**
 * Accessor: Nama bulan kekurangan + tahun (contoh: "Maret 2026")
 */
public function getBulanKekuranganFullAttribute()
{
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $bulan = $this->bulan_kekurangan;
    $tahun = $this->tahun ?? now()->year; // Jika tidak ada kolom 'tahun' di rekap, pakai tahun sekarang sebagai fallback

    if (!$bulan || !isset($months[$bulan])) {
        return '-';
    }

    return $months[$bulan] . ' ' . $tahun;
}

/**
 * Accessor: Nama bulan kelebihan + tahun
 */
public function getBulanKelebihanFullAttribute()
{
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $bulan = $this->bulan_kelebihan;
    $tahun = $this->tahun ?? now()->year;

    if (!$bulan || !isset($months[$bulan])) {
        return '-';
    }

    return $months[$bulan] . ' ' . $tahun;
}

    public function getKtrAttribute($value)
    {
        if ($value) return $value;
        
        // Fallback ambil dari profile
        if ($this->profile) {
            return $this->profile->ktr ?? $this->profile->ktr_tambahan ?? null;
        }
        
        return null;
    }
}