<?php

namespace App\Helpers;

use Carbon\Carbon;

class StudentHelper
{
    public static function hitungUsia($tanggal_lahir)
    {
        return Carbon::parse($tanggal_lahir)->age;
    }

    public static function kelompokUmur($tanggal_lahir)
    {
        $tahunSekarang = now()->year;
        $tahunLahir = Carbon::parse($tanggal_lahir)->year;
        $usia = $tahunSekarang - $tahunLahir;

        if ($usia < 8) return 'KU Below 8';
        if ($usia <= 9) return 'KU 10';
        if ($usia <= 12) return 'KU 12';
        if ($usia <= 14) return 'KU 14';
        if ($usia <= 16) return 'KU 16';
        if ($usia <= 18) return 'KU 18';
        if ($usia <= 24) return 'KU Pra Divisi';
        if ($usia <= 35) return 'KU Divisi';
        return 'KU Veteran';
    }

    //public static function generateNISS($lastNissNumber = null)
    public static function generateNISS($nissDefault = null)
    {
        // $year = now()->format('y'); // contoh: 25
        // $prefix = 'SS-' . $year . '-';

        // $nextNumber = $lastNissNumber ? $lastNissNumber + 1 : 1;
        // $formatted = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        // return $prefix . $formatted;

        $nissDefault = 'SS-00-00';

        return $nissDefault;
    }
}
