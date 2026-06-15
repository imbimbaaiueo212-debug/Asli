<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;        // TAMBAHKAN BARIS INI
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RekapAbsensi;
use App\Models\BukuIndukHistory;
use App\Models\JadwalDetail;
use App\Models\PengajuanGaransi;
use Carbon\Carbon;
use App\Models\Unit;
use App\Models\LevelHistory;
use Illuminate\Support\Facades\Auth;

class BukuInduk extends Model
{
    use HasFactory;

    protected $table = 'buku_induk';

    protected $fillable = [
        'nim', 'nama', 'tmpt_lahir', 'tgl_lahir', 'tgl_masuk', 'usia',
        'lama_bljr', 'tahap', 'tgl_keluar', 'kategori_keluar', 'alasan',
        'kelas', 'gol', 'kd', 'spp', 'status', 'petugas_trial', 'guru',
        'orangtua', 'no_telp_hp', 'note', 'no_cab_merge', 'no_pembayaran_murid',
        'note_garansi', 'periode', 'tgl_mulai', 'tgl_akhir', 'alert', 'tgl_bayar',
        'tgl_selesai', 'alert2', 'asal_modul', 'keterangan_optional', 'level',
        'jenis_kbm', 'kode_jadwal', 'hari_jam', 'alamat_murid', 'status_pindah',
        'tanggal_pindah', 'ke_bimba_intervio', 'keterangan', 'bimba_unit', 'no_cabang', 'info', 'tgl_daftar',
        'tgl_surat_garansi', 'keterangan_level', 'tgl_level', 'tgl_aktif', 'tgl_tahapan', 'keterangan_info',
        'tgl_pengajuan_garansi', 'tgl_selesai_garansi', 'masa_aktif_garansi', 'perpanjang_garansi', 'alasan_garansi', 'jumlah_beasiswa', 'modul_terakhir','nama_humas',
    ];

    // =================================================================
    // CASTS — SUPAYA SPP SELALU INTEGER & TANGGAL BENAR
    // =================================================================
    protected $casts = [
        'spp'              => 'integer',
        'usia'             => 'integer',
        'tgl_lahir'        => 'date',
        'tgl_masuk'        => 'date',
        'tgl_keluar'       => 'date',
        'tgl_mulai'        => 'date',
        'tgl_akhir'        => 'date',
        'tgl_bayar'        => 'date',
        'tgl_selesai'      => 'date',
        'tanggal_pindah'   => 'date',
        'tgl_daftar'       => 'date',
        'tgl_surat_garansi' => 'date',
        'tgl_level'        => 'date',
        'tgl_aktif'        => 'date',
        'tgl_tahapan'      => 'date',
        'tgl_pengajuan_garansi' => 'date',
        'tgl_selesai_garansi' => 'date',
        ];

    // =================================================================
    // AUTO TRIM NIM — INI YANG MEMBUAT EDIT SELALU BERHASIL!
    // =================================================================
    public function getNimAttribute($value)
    {
        return trim($value);
    }

    public function setNimAttribute($value)
    {
        $this->attributes['nim'] = trim($value);
    }

    // =================================================================
    // RELATIONSHIPS
    // =================================================================
    public function histories()
    {
        return $this->hasMany(BukuIndukHistory::class, 'buku_induk_id');
    }

    public function rekapAbsensi()
    {
        return $this->hasMany(RekapAbsensi::class, 'nama_relawan', 'guru');
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalDetail::class, 'murid_id');
    }

    public function unit()
{
    return $this->belongsTo(Unit::class, 'no_cabang', 'no_cabang')
                ->withoutGlobalScopes();
}

    // =================================================================
    // ACCESSOR: Usia Lengkap (contoh: 5 Tahun 3 Bulan)
    // =================================================================
    public function getUsiaLengkapAttribute()
    {
        if (!$this->tgl_lahir) {
            return '-';
        }

        $diff = Carbon::parse($this->tgl_lahir)->diff(Carbon::now());

        $usia = $diff->y . ' Tahun';
        if ($diff->m > 0) {
            $usia .= ' ' . $diff->m . ' Bulan';
        }

        return $usia;
    }

    // =================================================================
    // MODEL EVENTS — DIJALANKAN OTOMATIS
    // =================================================================
    // TAMBAHKAN INI DI BAGIAN booted() — GABUNGKAN DENGAN YANG SUDAH ADA
    protected static function booted()
{
    static::addGlobalScope(new UnitScope);

    // DELETE
    static::deleted(function ($bukuInduk) {
        \App\Models\BukuIndukHistory::create([
            'buku_induk_id' => $bukuInduk->id,
            'action'        => 'delete',
            'user'          => Auth::user()?->name ?? 'system',
            'data'          => $bukuInduk->toArray(),
        ]);

        RekapAbsensi::where('nama_relawan', $bukuInduk->guru)
            ->get()
            ->each->syncFromBukuInduk();
    });

    // SAVE (INI YANG PALING PENTING)
    static::saving(function ($bukuInduk) {

    // =====================================
    // PRIORITAS STATUS MANUAL
    // =====================================

    // Jika CUTI → jangan override
    if ($bukuInduk->status === 'Cuti') {
        return;
    }

    // Jika KELUAR → jangan override
    if (!empty($bukuInduk->tgl_keluar)) {
        $bukuInduk->status = 'Keluar';
        return;
    }

    // =====================================
    // AUTO STATUS BARU / AKTIF
    // =====================================

    if ($bukuInduk->tgl_masuk) {

        $tglMasuk = Carbon::parse($bukuInduk->tgl_masuk);

        if ($tglMasuk->lte(now()->subDays(30))) {
            $bukuInduk->status = 'Aktif';
        } else {
            $bukuInduk->status = 'Baru';
        }
    }
        });

    // AFTER SAVE
    static::saved(function ($bukuInduk) {
        RekapAbsensi::where('nama_relawan', $bukuInduk->guru)
            ->get()
            ->each->syncFromBukuInduk();
    });
}
    public function getTglMasukFormattedAttribute()
{
    return $this->tgl_masuk
        ? $this->tgl_masuk->translatedFormat('d F Y')
        : '-';
        
}
public function getTglLahirFormattedAttribute()
{
    return $this->tgl_lahir
        ? $this->tgl_lahir->translatedFormat('d F Y')
        : '-';
}
public function getPertemuanTerlewatAttribute()
{
    if (!$this->tgl_masuk) {
        return 0;
    }

    // Ambil tanggal mulai semester dari config / setting / periode
    $semesterMulai = Carbon::parse('2025-12-01'); // ← ganti dengan nilai real (bisa dari tabel)

    if ($this->tgl_masuk->lte($semesterMulai)) {
        return 0; // masuk sebelum atau pas awal semester
    }

    $jadwalHari = $this->jadwal->pluck('hari')->unique()->values();

    // Hitung jumlah hari pertemuan per minggu
    $hariPerMinggu = $jadwalHari->count();

    // Hitung jumlah minggu penuh yang terlewat
    $hariLewat = $semesterMulai->diffInDays($this->tgl_masuk, false);
    $mingguLewat = floor($hariLewat / 7);

    // Hitung sisa hari di minggu terakhir yang terlewat
    $sisaHari = $hariLewat % 7;
    $hariTambahan = 0;

    for ($i = 0; $i < $sisaHari; $i++) {
        $tanggal = $semesterMulai->copy()->addDays($i);
        if ($jadwalHari->contains($tanggal->locale('id')->isoFormat('dddd'))) { // 'Monday', 'Tuesday', dll
            $hariTambahan++;
        }
    }

    return ($mingguLewat * $hariPerMinggu) + $hariTambahan;
}

public function getHariJamExportAttribute()
{
    // Ambil data dari relation jadwal
    $details = $this->relationLoaded('jadwal') 
        ? $this->jadwal 
        : $this->jadwal()
            ->select('hari')
            ->distinct()
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->get();

    if ($details->isEmpty()) {
        return '';
    }

    // Hanya ambil daftar hari unik, sudah diurutkan
    $hariList = $details->pluck('hari')->unique()->all();

    // Gabungkan dengan koma dan spasi
    return implode(', ', $hariList);
}

public function getTglSuratGaransiFormattedAttribute()
{
    return $this->tgl_surat_garansi
        ? $this->tgl_surat_garansi->translatedFormat('d F Y')
        : '-';
}

public function getTglLevelFormattedAttribute()
{
    return $this->tgl_level
        ? $this->tgl_level->translatedFormat('d F Y')
        : '-';
}

public function levelHistories()
{
    return $this->hasMany(LevelHistory::class)
                ->orderByDesc('tgl_level');
}

public function pengajuanGaransi()
{
    return $this->hasMany(PengajuanGaransi::class, 'nim', 'nim');
}
public function scopeActive($q)
{
    return $q->where('status', 'Aktif');
}
public function handlePindahGolongan()
{
    $original = $this->getOriginal();

    $oldGol = $original['gol'] ?? null;
    $oldKd  = $original['kd']  ?? null;
    $oldSpp = $original['spp'] ?? null;

    $newGol = $this->gol;
    $newKd  = $this->kd;
    $newSpp = $this->spp;

    if ($oldGol === $newGol && $oldKd === $newKd && (int)$oldSpp === (int)$newSpp) {
        return;
    }

    $payload = [
        'nim'                     => $this->nim,
        'nama'                    => $this->nama,
        'guru'                    => $this->guru,
        'bimba_unit'              => $this->bimba_unit,
        'no_cabang'               => $this->no_cabang,

        'gol'                     => $oldGol,
        'kd'                      => $oldKd,
        'spp'                     => $oldSpp,

        'gol_baru'                => $newGol,
        'kd_baru'                 => $newKd,
        'spp_baru'                => $newSpp,

        'tanggal_pindah_golongan' => now(),
        'keterangan'              => 'Diubah langsung dari Buku Induk',
        'alasan_pindah'           => 'Perubahan golongan/kd/spp manual',
        'source'                  => 'buku_induk',     // ← PENTING
    ];

    PindahGolongan::create($payload);
}
public function cutiMurid()
{
    return $this->hasMany(CutiMurid::class);
}

public function student()
{
    return $this->hasOne(Student::class, 'nim', 'nim');
}

public function getTanggalAktifAttribute()
{
    $tanggalPenerimaan = optional(
        $this->student?->registrations
            ?->sortByDesc('tanggal_penerimaan')
            ?->first()
    )->tanggal_penerimaan;

    return $tanggalPenerimaan ?? $this->tgl_masuk;
}



}