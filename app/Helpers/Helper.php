<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

if (! function_exists('formatPhone')) {
function formatPhone($phone)
    {
        // Hapus semua karakter kecuali angka
        $phone = preg_replace('/\D+/', '', $phone);

        // Jika sudah diawali 62, biarkan
        if (substr($phone, 0, 2) === '62') {
            return $phone;
        }

        // Jika diawali 0, ubah menjadi 62
        if (substr($phone, 0, 1) === '0') {
            return '62' . substr($phone, 1);
        }

        return $phone;
    }
}

if (!function_exists('foto_url')) {
    function foto_url($path, $type = 'siswa', $placeholder = null)
    {
        if (!$path) {
            return $placeholder ?? "https://placehold.co/150x200?text=No+Photo";
        }

        // Tentukan folder
        $folder = $type === 'user' ? 'foto_user' : 'foto_siswa';

        // Normalize: hapus prefix jika sudah ada
        $path = str_replace(['storage/', 'foto_user/', 'foto_siswa/'], '', $path);

        // Local + Production (sama karena storage:link)
        return asset("storage/{$folder}/{$path}");
    }
}

if (!function_exists('avatar_url')) {
    function avatar_url($user)
    {
        // Login sebagai siswa
        if ($user->role === 'siswa') {
            $foto = $user->siswaProfile?->foto;
            return foto_url($foto, 'siswa', "https://placehold.co/100x130?text=FS");
        }

        // Login sebagai parent / admin / coach / default user
        $foto = $user->userProfile?->foto;
        return foto_url($foto, 'user', "https://placehold.co/100x130?text=PU");
    }
}

if (! function_exists('formatDate')) {
    function formatDate($date, $format = 'd-m-Y')
    {
        if (!$date) {
            return '-';
        }

        return Carbon::parse($date)->translatedFormat($format);
    }
}

if (! function_exists('backRoute')) {
    function backRoute() {
        return match(auth()->user()->role ?? '') {
            'orang_tua' => route('parent.dashboard'),
            'pelatih'   => route('pelatih.dashboard'),
            'admin'     => route('siswa.index'),
            default     => url()->previous(),
        };
    }
}

if (! function_exists('backPost')) {
    function backPost() {
        return match(auth()->user()->role ?? '') {
            'orang_tua' => route('posts.public'),
            'pelatih'   => route('posts.public'),
            'admin'     => route('posts.index'),
            default     => url()->previous(),
        };
    }
}