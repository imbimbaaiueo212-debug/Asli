<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Penerimaan extends Model
{
    protected $table = 'penerimaan';

    protected $fillable = [
        'kwitansi', 'via', 'tanggal',
        'nim', 'nama_murid', 'kelas', 'gol', 'kd', 'status', 'guru',
        'daftar', 'voucher', 'spp', 
        'kaos', 'kaos_lengan_panjang',
        'kpk', 'sertifikat', 'stpb', 'tas', 'event', 'lain_lain',
        'total', 'bulan', 'tahun', 'nilai_spp', 'bukti_transfer_path',
        'bimba_unit', 'no_cabang',
        'RBAS', 'BCABS01', 'BCABS02',

        // TAMBAHAN BARU: tanggal penyerahan per produk
        'tanggal_penyerahan_kaos_pendek',
        'tanggal_penyerahan_kaos_panjang',
        'tanggal_penyerahan_kpk',
        'tanggal_penyerahan_tas',
        'tanggal_penyerahan_rbas',
        'tanggal_penyerahan_bcabs01',
        'tanggal_penyerahan_bcabs02',
        'tanggal_penyerahan_sertifikat',
        'tanggal_penyerahan_stpb',
        'tanggal_penyerahan_event',
        'tanggal_penyerahan_lainlain',

        // TAMBAHAN BARU: Ukuran kaos (S, M, L, XL, dll)
        'ukuran_kaos_pendek',
        'ukuran_kaos_panjang',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'kaos_pendek_details' => 'array',
    'kaos_panjang_details' => 'array',

        // Cast semua tanggal penyerahan per produk
        'tanggal_penyerahan_kaos_pendek' => 'date:Y-m-d',
        'tanggal_penyerahan_kaos_panjang' => 'date:Y-m-d',
        'tanggal_penyerahan_kpk' => 'date:Y-m-d',
        'tanggal_penyerahan_tas' => 'date:Y-m-d',
        'tanggal_penyerahan_rbas' => 'date:Y-m-d',
        'tanggal_penyerahan_bcabs01' => 'date:Y-m-d',
        'tanggal_penyerahan_bcabs02' => 'date:Y-m-d',
        'tanggal_penyerahan_sertifikat' => 'date:Y-m-d',
        'tanggal_penyerahan_stpb' => 'date:Y-m-d',
        'tanggal_penyerahan_event' => 'date:Y-m-d',
        'tanggal_penyerahan_lainlain' => 'date:Y-m-d',

        'total' => 'integer',
        'spp'   => 'integer',
    ];

    public function bukuInduk()
    {
        return $this->belongsTo(BukuInduk::class, 'nim', 'nim');
    }

    public function getBuktiTransferUrlAttribute(): ?string
    {
        if (!$this->bukti_transfer_path) {
            return null;
        }

        $path = Str::replaceFirst('public/', '', $this->bukti_transfer_path);
        $path = ltrim($path, '/');

        return asset('storage/' . $path);
    }

    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);

        static::deleted(function (self $penerimaan) {
            if ($penerimaan->bukti_transfer_path) {
                try {
                    Storage::disk('public')->delete($penerimaan->bukti_transfer_path);
                } catch (\Throwable $e) {
                    Log::warning('Gagal hapus file bukti penerimaan', [
                        'id'      => $penerimaan->id,
                        'path'    => $penerimaan->bukti_transfer_path,
                        'error'   => $e->getMessage()
                    ]);
                }
            }
        });
    }

    public function getTanggalAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    // HAPUS ACCESSOR INI KARENA SUDAH TIDAK DIPAKAI (field tanggal_penyerahan sudah dihapus)
    // public function getTanggalPenyerahanFormattedAttribute()
    // {
    //     return $this->tanggal_penyerahan ? Carbon::parse($this->tanggal_penyerahan)->format('d-m-Y') : '-';
    // }
}