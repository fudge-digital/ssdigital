<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PembayaranSiswa;
use App\Models\SiswaProfile;
use App\Helpers\StudentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayarans = PembayaranSiswa::with([
            'user',
            'siswa',
            'details.siswa'
        ])
        ->whereIn('status', ['pending', 'approve', 'reject'])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    public function suspend(Request $request, $id)
    {
        $p = PembayaranSiswa::findOrFail($id);
        $p->update(['status' => 'suspended']);

        return back()->with('success', 'Status pembayaran telah diset ke suspended.');
    }

    public function verify(Request $request, $id)
    {
        $pembayaran = PembayaranSiswa::with(['siswa.siswaProfile', 'details.siswa.siswaProfile'])->findOrFail($id);

        // Update status pembayaran
        $pembayaran->update([
            'status' => 'approve',
            'diverifikasi_oleh' => Auth::id(),
            'tanggal_verifikasi' => now(),
        ]);

        // 🔹 Kasus 1: Format lama (1 siswa per pembayaran)
        if ($pembayaran->siswa) {
            $this->activateStudent($pembayaran->siswa);
        }

        // 🔹 Kasus 2: Format baru (1 pembayaran untuk banyak siswa)
        if ($pembayaran->details && $pembayaran->details->count() > 0) {
            foreach ($pembayaran->details as $detail) {
                if ($detail->siswa) {
                    $this->activateStudent($detail->siswa);
                }
            }
        }

        return back()->with('success', 'Pembayaran diverifikasi dan seluruh siswa berhasil diaktifkan.');
    }

    public function approveReRegistration($id)
    {
        $pembayaran = PembayaranSiswa::findOrFail($id);

        // Update pembayaran
        $pembayaran->update([
            'status' => 'approve',
            'diverifikasi_oleh' => Auth::id(),
            'tanggal_verifikasi' => now(),
        ]);

        // Ambil semua siswa di detail
        $siswaIds = DB::table('pembayaran_siswa_detail')
            ->where('pembayaran_id', $pembayaran->id)
            ->pluck('siswa_id');

        // Update status semua siswa
        User::whereIn('id', $siswaIds)->update([
            'status' => 'aktif',
            'updated_at' => now()
        ]);

        return back()->with('success', 'Registrasi ulang berhasil diverifikasi, semua siswa telah diaktifkan.');
    }

    /**
     * Mengaktifkan siswa + generate NISS jika belum ada.
     */
    private function activateStudent($siswa)
    {
        if (!$siswa->siswaProfile) {
            return;
        }

        $profile = $siswa->siswaProfile;

        // Generate NISS jika belum ada
        if (empty($profile->niss)) {
            $last = SiswaProfile::whereNotNull('niss')->count();
            $profile->niss = StudentHelper::generateNISS($last);
        }

        $profile->status = 'aktif';
        $profile->save();
    }
}
