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
        $tahunLahir = Carbon::parse($tanggal_lahir)->year;
        $tahunSekarang = now()->year;

        $usia = $tahunSekarang - $tahunLahir;

        $ku = (int) (ceil($usia / 2) * 2);

        if ($ku < 8) return 'KU Below 8';
        if ($ku <= 10) return 'KU 10';
        if ($ku <= 12) return 'KU 12';
        if ($ku <= 14) return 'KU 14';
        if ($ku <= 16) return 'KU 16';
        if ($ku <= 18) return 'KU 18';
        if ($ku <= 35) return 'KU Divisi';
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
