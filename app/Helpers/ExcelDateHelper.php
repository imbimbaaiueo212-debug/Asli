<?php

namespace App\Helpers;

use Carbon\Carbon;

class ExcelDateHelper
{
    public static function convertExcelDate($value)
    {
        // Kalau value berupa angka (serial number Excel)
        if (is_numeric($value)) {
            // Excel mulai dari 1900-01-01
            return Carbon::createFromDate(1899, 12, 30)->addDays($value)->format('Y-m-d');
        }

        // Kalau value string dengan format dd/mm/yyyy
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        }

        // Kalau sudah dalam format yyyy-mm-dd
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
