<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SiswaProfile;
use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung total siswa berdasarkan status
        $totalSiswa = SiswaProfile::count();
        $aktif = SiswaProfile::where('status', 'aktif')->count();
        $tidakAktif = SiswaProfile::where('status', '!=', 'aktif')
            ->where('status', '!=', 'suspended')
            ->count();
        $suspended = SiswaProfile::where('status', 'suspended')->count();

        $recentStudents = \App\Models\SiswaProfile::with('user')
        ->latest()
        ->take(5)
        ->get();

        // Hitung pembayaran
        $totalPembayaran = PembayaranSiswa::count();
        $pendingPembayaran = PembayaranSiswa::where('status', 'pending')->count();
        $verifiedPembayaran = PembayaranSiswa::where('status', 'verified')->count();

        return view('admin.dashboard', compact(
            'totalSiswa',
            'aktif',
            'tidakAktif',
            'suspended',
            'totalPembayaran',
            'pendingPembayaran',
            'verifiedPembayaran',
            'recentStudents',
        ));
    }
}
