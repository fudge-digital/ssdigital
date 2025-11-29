<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('userRole')) {
    /**
     * Mengambil role user yang sedang login.
     */
    function userRole(): ?string
    {
        return Auth::check() ? Auth::user()->role : null;
    }
}

if (!function_exists('isRole')) {
    /**
     * Mengecek apakah user memiliki role tertentu.
     */
    function isRole(string $role): bool
    {
        return Auth::check() && Auth::user()->role === $role;
    }
}

if (!function_exists('isAnyRole')) {
    /**
     * Mengecek apakah user memiliki salah satu dari beberapa role.
     */
    function isAnyRole(array $roles): bool
    {
        return Auth::check() && in_array(Auth::user()->role, $roles);
    }
}

if (!function_exists('roleName')) {
    /**
     * Menampilkan nama role yang lebih ramah untuk ditampilkan di UI.
     */
    function roleName(?string $role = null): string
    {
        $role = $role ?? userRole();

        return match ($role) {
            'super_admin'       => 'Super Admin',
            'admin'             => 'Admin',
            'manajer_tim'       => 'Manajer Tim',
            'pelatih'           => 'Pelatih',
            'asisten_pelatih'   => 'Asisten Pelatih',
            'orang_tua'         => 'Orang Tua',
            'siswa'             => 'Siswa',
            default             => 'Tidak Diketahui',
        };
    }
}
