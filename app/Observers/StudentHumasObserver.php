<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\Huma;
use Illuminate\Support\Facades\DB;

class StudentHumasObserver
{
    public function created(Student $student)
    {
        $this->buatAtauUpdateHumasOrangTua($student);
    }

    public function updated(Student $student)
    {
        if ($student->wasChanged('informasi_humas_nama')) {
            $this->buatAtauUpdateHumasOrangTua($student);
        }
    }

    private function buatAtauUpdateHumasOrangTua(Student $student)
    {
        $namaMuridPengundang = trim($student->informasi_humas_nama ?? '');
        if ($namaMuridPengundang === '') return;

        $pengundang = Student::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($namaMuridPengundang)])
            ->orWhere('nim', $namaMuridPengundang)
            ->first();

        if (!$pengundang) return;

        // Ambil data ayah & ibu
        $ayah = !empty(trim($pengundang->nama_ayah ?? '')) ? "Ayah " . trim($pengundang->nama_ayah) : null;
        $ibu  = !empty(trim($pengundang->nama_ibu ?? ''))  ? "Ibu "  . trim($pengundang->nama_ibu)  : null;

        if (!$ayah && !$ibu) return;

        // Gabung jadi satu: "Ayah Budi / Ibu Siti"
        $namaHumas = trim(($ayah ?? '') . ($ayah && $ibu ? ' / ' : '') . ($ibu ?? ''));

        // Ambil HP & pekerjaan (prioritas ayah)
        $hpUtama    = $pengundang->hp_ayah ?: $pengundang->hp_ibu;
        $pekerjaan  = trim($pengundang->pekerjaan_ayah ?? $pengundang->pekerjaan_ibu ?? 'Orang Tua');

        // Ambil unit & cabang
        $noCabang = $pengundang->no_cabang;
        if (!$noCabang && $pengundang->bimba_unit) {
            $unit = DB::table('units')
                ->whereRaw('LOWER(bimba_unit) LIKE ?', ['%' . strtolower($pengundang->bimba_unit) . '%'])
                ->first(['no_cabang']);
            $noCabang = $unit?->no_cabang;
        }
        if (!$noCabang) return;

        // FORMAT NIH: 05141020001 (cabang 3 digit + 14 + 02 + urut 4 digit)
        $cabang3Digit = str_pad($noCabang, 5, '0', STR_PAD_LEFT); //  // 05141 → 05141
        $kodeTetap    = '02';

        $last = Huma::where('nih', 'LIKE', $cabang3Digit . $kodeTetap . '%')
            ->orderByRaw('CAST(RIGHT(nih, 4) AS UNSIGNED) DESC')
            ->first();

        $urut = $last ? (intval(substr($last->nih, -4)) + 1) : 1;
        $urut4Digit = str_pad($urut, 4, '0', STR_PAD_LEFT);

        $nih = $cabang3Digit . $kodeTetap . $urut4Digit; // 05141020001

        $hpBersih = $hpUtama ? preg_replace('/\D/', '', $hpUtama) : null;

        // Cek apakah Humas dengan nama ini sudah ada di unit ini
        $humas = Huma::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($namaHumas)])
                     ->where('bimba_unit', $pengundang->bimba_unit)
                     ->first();

        if ($humas) {
            // UPDATE kalau sudah ada
            $humas->update([
                'no_telp'   => $hpBersih,
                'pekerjaan' => $pekerjaan,
                'alamat'    => $pengundang->alamat ?? $humas->alamat,
                'nih'       => $nih,
            ]);
        } else {
            // BUAT BARU
            Huma::create([
                'tgl_reg'    => now()->toDateString(),
                'nih'        => $nih,
                'nama'       => $namaHumas,        // "Ayah tes / Ibu tes"
                'no_telp'    => $hpBersih,
                'status'     => 'baru',
                'pekerjaan'  => $pekerjaan,
                'alamat'     => $pengundang->alamat ?? null,
                'bimba_unit' => $pengundang->bimba_unit,
                'no_cabang'  => $noCabang,
            ]);
        }
    }
}