<?php

namespace App\Helpers;

class TerbilangHelper
{
    public static function terbilang($angka)
    {
        $angka = abs((int) $angka);

        $baca = [
            "",
            "Satu",
            "Dua",
            "Tiga",
            "Empat",
            "Lima",
            "Enam",
            "Tujuh",
            "Delapan",
            "Sembilan",
            "Sepuluh",
            "Sebelas"
        ];

        if ($angka < 12) {
            return $baca[$angka];
        } elseif ($angka < 20) {
            return self::terbilang($angka - 10) . " Belas";
        } elseif ($angka < 100) {
            return self::terbilang($angka / 10) . " Puluh " . self::terbilang($angka % 10);
        } elseif ($angka < 200) {
            return "Seratus " . self::terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return self::terbilang($angka / 100) . " Ratus " . self::terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return "Seribu " . self::terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return self::terbilang($angka / 1000) . " Ribu " . self::terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return self::terbilang($angka / 1000000) . " Juta " . self::terbilang($angka % 1000000);
        }

        return "Terlalu Besar";
    }
}
