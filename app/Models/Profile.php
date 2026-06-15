<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\BukuInduk;
use App\Models\Scopes\UnitScope;
use App\Models\Scopes\AktifScope;
use App\Models\RekapAbsensi;
use App\Models\PendapatanTunjangan;
use App\Models\Skim;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Events\ProfileUpdated; // Pastikan ini ada

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';

    protected $fillable = [
    'no_urut', 'nik', 'nama', 'jabatan', 'status_karyawan', 'departemen',
    'biMBA_unit', 'no_cabang', 'tgl_masuk', 'masa_kerja',
    'jumlah_murid_mba', 'jumlah_murid_eng', 'total_murid', 'jumlah_murid_jadwal',
    'jumlah_rombim', 'rb', 'rb_tambahan', 'rb_calc', 'ktr', 'ktr_tambahan',
    'rp', 'jenis_mutasi', 'tgl_mutasi_jabatan', 'masa_kerja_jabatan',
    'tgl_lahir', 'usia', 'no_telp', 'email', 'no_rekening', 'bank', 'atas_nama',
    'mentor_magang', 'periode', 'tgl_selesai_magang', 'ukuran', 'status_lain',
    'keterangan', 'seragam', 'tgl_ambil_seragam', 'kaos_kuning_hitam',
    'kaos_merah_kuning_biru', 'kemeja_kuning_hitam', 'blazer_merah', 'blazer_biru', 'tgl_keluar', 'keterangan_keluar',
    
    // TAMBAHKAN INI
    'total_murid_bawahan',
    // tambahkan ketiga kolom baru ini
    'tgl_magang',
    'tgl_non_aktif',
    'tgl_resign',
    'tempat_lahir',
    
];

    protected $casts = [
        'tgl_masuk' => 'date',
        'tgl_mutasi_jabatan' => 'date',
        'tgl_lahir' => 'date',
        'tgl_selesai_magang' => 'date',
        'tgl_ambil_seragam' => 'date',
        'seragam' => 'array',
        'tgl_magang'     => 'date',
        'tgl_non_aktif'  => 'date',
        'tgl_resign'     => 'date',
        'tgl_keluar'    => 'date',
    ];

    // Dispatch event saat created atau updated → agar rekap imbalan otomatis update
    protected $dispatchesEvents = [
        'created' => ProfileUpdated::class,
        'updated' => ProfileUpdated::class,
    ];

    

    // ===================================================================
    // ACCESSORS
    // ===================================================================
    /**
 * Usia dalam format "X tahun Y bulan"
 */
public function getUsiaFormatAttribute()
{
    if (!$this->tgl_lahir) {
        return '0 tahun 0 bulan';
    }

    $lahir = Carbon::parse($this->tgl_lahir);
    $diff  = $lahir->diff(Carbon::now());

    $tahun = $diff->y;
    $bulan = $diff->m;

    return "{$tahun} tahun {$bulan} bulan";
}

/**
 * Bonus: Usia hanya tahun (untuk keperluan lain)
 */
public function getUsiaAttribute()
{
    return $this->tgl_lahir?->age ?? 0;
}

    public function getMasaKerjaFormatAttribute()
    {
        if (!$this->masa_kerja) return '-';
        $years = intdiv($this->masa_kerja, 12);
        $months = $this->masa_kerja % 12;
        $parts = [];
        if ($years > 0) $parts[] = "$years tahun";
        if ($months > 0) $parts[] = "$months bulan";
        return $parts ? implode(' ', $parts) : '0 bulan';
    }

    public function getUnitLengkapAttribute()
    {
        if ($this->bimba_unit) {
            $text = strtoupper($this->bimba_unit);
            if ($this->no_cabang && !in_array(trim($this->no_cabang), ['', '-'])) {
                $text .= " ({$this->no_cabang})";
            }
            return $text;
        }

        if (!$this->departemen) return '-';

        static $cache = [];
        if (isset($cache[$this->departemen])) {
            return $cache[$this->departemen];
        }

        $unit = \App\Models\Unit::where('biMBA_unit', 'LIKE', "%{$this->departemen}%")
            ->select('biMBA_unit', 'no_cabang')
            ->first();

        if (!$unit) {
            $result = strtoupper($this->departemen);
        } else {
            $text = strtoupper($unit->biMBA_unit);
            if ($unit->no_cabang && !in_array(trim($unit->no_cabang), ['', '-'])) {
                $text .= " ({$unit->no_cabang})";
            }
            $result = $text;
        }

        $cache[$this->departemen] = $result;
        return $result;
    }

    // ===================================================================
    // RELATIONSHIPS
    // ===================================================================
    public function bukuInduk()
    {
        return $this->hasMany(BukuInduk::class, 'guru', 'nama');
    }

    public function bukuIndukMba()
    {
        $relation = $this->hasMany(BukuInduk::class, 'guru', 'nama');
        foreach (['program', 'departemen', 'kelas', 'jenis_program', 'paket'] as $col) {
            if (Schema::hasColumn('buku_induk', $col)) {
                return $relation->where($col, 'like', '%MBA%');
            }
        }
        return $relation;
    }

    public function getJumlahMuridMbaAttribute()
    {
        if (array_key_exists('jumlah_murid_mba', $this->attributes) && $this->attributes['jumlah_murid_mba'] !== null) {
            return (int) $this->attributes['jumlah_murid_mba'];
        }

        if ($this->relationLoaded('bukuIndukMba')) {
            return $this->bukuIndukMba->count();
        }

        $query = BukuInduk::where('guru', $this->nama);
        $filtered = false;
        foreach (['program', 'departemen', 'kelas', 'jenis_program', 'paket'] as $col) {
            if (Schema::hasColumn('buku_induk', $col)) {
                $query->where($col, 'like', '%MBA%');
                $filtered = true;
                break;
            }
        }

        return $filtered ? $query->count() : 0;
    }

    public function rekapAbsensi()
    {
        return $this->hasMany(RekapAbsensi::class, 'nama_relawan', 'nama');
    }

    public function pendapatanTunjangan()
    {
        return $this->hasOne(PendapatanTunjangan::class);
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'bimba_unit', 'biMBA_unit');
    }

    // ===================================================================
    // SCOPES
    // ===================================================================
    public function scopeGuru($query)
    {
        return $query->whereIn('jabatan', [
            'Guru', 'Guru Trial', 'Guru_Trial', 'guru_trial',
            'Pengajar', 'Guru biMBA', 'Tutor',
        ]);
    }
    // app/Helpers/KtrHelper.php atau di Model Profile
function hitungKtrTambahanKepala(?int $count): ?string
{
    if ($count === null || $count <= 0) {
        return null;
    }

    if ($count <= 25)   return 'KTR 1B';
    if ($count <= 75)   return 'KTR 2A';
    if ($count <= 100)  return 'KTR 2B';
    if ($count <= 125)  return 'KTR 3A';
    if ($count <= 150)  return 'KTR 3B';
    if ($count <= 175)  return 'KTR 4A';
    if ($count <= 200)  return 'KTR 4B';
    if ($count <= 225)  return 'KTR 5A';
    if ($count <= 250)  return 'KTR 5B';
    if ($count <= 275)  return 'KTR 6A';
    if ($count <= 300)  return 'KTR 6B';
    if ($count <= 325)  return 'KTR 7A';
    if ($count <= 350)  return 'KTR 7B';
    if ($count <= 375)  return 'KTR 8A';
    if ($count <= 400)  return 'KTR 8B';
    if ($count <= 425)  return 'KTR 9A';
    if ($count <= 450)  return 'KTR 9B';
    if ($count <= 475)  return 'KTR 10A';

    return 'KTR 10B';
}
// app/Models/Profile.php

public function getKtrDisplayAttribute(): string
{
    if (!empty($this->ktr_tambahan)) {
        return $this->ktr_tambahan;
    }

    // Pastikan ini kolom yang benar untuk jumlah murid bawahan Kepala Unit
    // Dari screenshot, sepertinya jumlah murid jadwal (JDWL) = 34
    $count = (int) ($this->jumlah_murid_jadwal ?? $this->total_murid_bawahan ?? 0);

    if ($count <= 0) {
        return $this->ktr ?? '-';
    }

    return match (true) {
        $count <= 25   => 'KTR 1B',
        $count <= 75   => 'KTR 2A',   // ← 34 harus masuk sini
        $count <= 100  => 'KTR 2B',
        $count <= 125  => 'KTR 3A',
        $count <= 150  => 'KTR 3B',
        $count <= 175  => 'KTR 4A',
        $count <= 200  => 'KTR 4B',
        $count <= 225  => 'KTR 5A',
        $count <= 250  => 'KTR 5B',
        $count <= 275  => 'KTR 6A',
        $count <= 300  => 'KTR 6B',
        $count <= 325  => 'KTR 7A',
        $count <= 350  => 'KTR 7B',
        $count <= 375  => 'KTR 8A',
        $count <= 400  => 'KTR 8B',
        $count <= 425  => 'KTR 9A',
        $count <= 450  => 'KTR 9B',
        $count <= 475  => 'KTR 10A',
        default        => 'KTR 10B',
    };
}
public function getStatusKaryawanLengkapAttribute(): string
{
    if ($this->tgl_resign) {
        return 'Resign per ' . $this->tgl_resign->format('d M Y');
    }

    if ($this->tgl_non_aktif) {
        return 'Non Aktif per ' . $this->tgl_non_aktif->format('d M Y');
    }

    if ($this->tgl_magang && $this->tgl_selesai_magang?->isFuture()) {
        return 'Magang (sampai ' . $this->tgl_selesai_magang->format('d M Y') . ')';
    }

    if ($this->tgl_magang) {
        return 'Magang selesai';
    }

    return $this->status_karyawan ?? 'Aktif';
}

public function getMasaKerjaJabatanBulanAttribute()
    {
        if (!$this->tgl_mutasi_jabatan) {
            return 0;
        }

        $start = Carbon::parse($this->tgl_mutasi_jabatan);
        $now   = Carbon::today(); // atau Carbon::now() kalau mau include jam

        // Cara paling akurat & umum dipakai di HR Indonesia
        $months = $start->diffInMonths($now);

        // Opsional: kalau ada sisa hari > 15 atau > 0 → dibulatkan naik
        // if ($start->diffInDays($now) % 30 > 15) {
        //     $months++;
        // }

        return $months;
    }

    // Kalau mau simpan juga ke kolom (untuk indexing & query cepat)
    public function updateMasaKerjaJabatan()
    {
        if ($this->tgl_mutasi_jabatan) {
            $this->masa_kerja_jabatan = $this->masa_kerja_jabatan_bulan;
            $this->saveQuietly(); // tanpa event/fillable issue
        }
    }

   
public function histories()
    {
        return $this->hasMany(ProfileHistory::class)
                    ->orderBy('periode', 'desc');
    }
    protected static function booted()
{
    static::addGlobalScope(new UnitScope);

}

// Di Model Profile.php
public function isKepalaUnit(): bool
{
    return str_contains(strtolower($this->jabatan ?? ''), 'kepala') ||
           str_contains(strtolower($this->jabatan ?? ''), 'head');
}

public function getTotalMuridBawahanAttribute()
{
    if (array_key_exists('total_murid_bawahan', $this->attributes) && 
        $this->attributes['total_murid_bawahan'] !== null) {
        return (int) $this->attributes['total_murid_bawahan'];
    }

    if ($this->isKepalaUnit()) {
        return BukuInduk::where('bimba_unit', $this->biMBA_unit)
            ->whereIn('status', ['Aktif', 'Baru'])
            ->count();
    }

    return BukuInduk::where('guru', $this->nama)
        ->whereIn('status', ['Aktif', 'Baru'])
        ->count();
}
}