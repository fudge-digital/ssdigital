<?php

namespace App\Http\Controllers\Parents;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\PembayaranSiswa;
use App\Models\Post;
use App\Services\ParentFinanceSummaryService;

class ParentDashboardController extends Controller
{
    public function index(ParentFinanceSummaryService $summaryService)
    {
        $parent = Auth::user();
        $now = Carbon::now();

        // Ambil semua anak milik orang tua
        $children = $parent->children()->with('siswaProfile')->get();

        // Ambil anak yang baru didaftarkan (misalnya status != aktif)
        $nonActiveStudents = $children->filter(function ($child) {
            return $child->siswaProfile->status !== 'aktif';
        });

        // Hitung total pembayaran pendaftaran
        $biayaPerSiswa = 650000;
        $jumlahSiswa = count($nonActiveStudents);
        $totalPendaftaran = $jumlahSiswa * $biayaPerSiswa;

        // Cek apakah pernah register offline
        $hasOfflineRegistration = PembayaranSiswa::where('user_id', $parent->id)
            ->where('jenis', 'offline')
            ->exists();

        $isReRegistration = $hasOfflineRegistration;

        // Cek pembayaran online
        $pembayaran = PembayaranSiswa::where('user_id', $parent->id)
            ->where('jenis', 'pendaftaran')
            ->whereIn('status', ['pending', 'approve'])
            ->first();

        $jadwalLatihan = Post::with('category')
            ->whereHas('category', function ($q) {
                $q->where('slug', 'jadwal-latihan');
            })
            ->whereMonth('published_at', now()->month)
            ->whereYear('published_at', now()->year)
            ->orderBy('published_at', 'desc')
            ->first();

        return view('parent.dashboard', [
            'parent'            => $parent,
            'students'          => $children,
            'nonActiveStudents' => $nonActiveStudents,
            'totalPendaftaran'  => $totalPendaftaran,
            'pembayaran'        => $pembayaran,
            'isReRegistration'  => $isReRegistration,
            'jadwalLatihan'     => $jadwalLatihan,
        ]);
    }

    public function uploadPembayaran(Request $request)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|max:4096', // 4MB limit
            'jumlah' => 'nullable|numeric|min:0',
            'siswa_ids' => 'nullable|string',
        ]);

        $orangTua = Auth::user();
        $siswaIds = explode(',', $request->siswa_ids);

        // Pastikan semua siswa memang anak dari user ini
        $validSiswa = $orangTua->children()->whereIn('users.id', $siswaIds)->get();
        if ($validSiswa->count() !== count($siswaIds)) {
            return back()->withErrors(['msg' => 'Beberapa data siswa tidak valid.']);
        }

        $biayaPerSiswa = 650000;
        $totalPendaftaran = $biayaPerSiswa * $validSiswa->count();

        $file = $request->file('bukti_pembayaran');
        $filename = 'pembayaran_' . $orangTua->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Cek environment
        $useDirectPublicStorage = env('USE_DIRECT_PUBLIC_STORAGE', false);

        if (!$useDirectPublicStorage) {
            // LOCAL development
            $path = $file->storeAs('bukti_pembayaran', $filename, 'public');
        } else {
            // PRODUCTION: public_html/storage/bukti_pembayaran
            $destinationPath = base_path('../public_html/storage/bukti_pembayaran');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);

            // Simpan path ke database
            $path = 'storage/bukti_pembayaran/' . $filename;
        }

        // Simpan Pembayaran
        $pembayaran = PembayaranSiswa::create([
            'user_id' => $orangTua->id,
            'jenis' => 'Pendaftaran_Baru',
            'jumlah_total' => $totalPendaftaran,
            'status' => 'pending',
            'bukti_pembayaran' => $path,
        ]);

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new PendingRegistrationNotification($pembayaran));

        // Simpan relasi ke masing-masing siswa (jika nanti perlu tracking per anak)
        foreach ($validSiswa as $siswa) {
            DB::table('pembayaran_siswa_detail')->insert([
                'pembayaran_id' => $pembayaran->id,
                'siswa_id' => $siswa->id,
                'jumlah' => $biayaPerSiswa,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Silakan tunggu verifikasi admin.');
    }

    /**
     * Kirim registrasi ulang (skip pembayaran)
     */
    public function reRegistration(Request $request)
    {
        $parent = Auth::user();
        $siswaIds = explode(',', $request->siswa_ids);

        // Hitung total (0 untuk re-registration)
        $total = 0;

        // Buat header pembayaran
        $pembayaran = PembayaranSiswa::create([
            'user_id' => $parent->id,
            'jenis' => 'Daftar_Ulang',
            'jumlah_total' => $total,
            'status' => 'pending',
        ]);

        // Insert per siswa
        foreach ($siswaIds as $siswaId) {
            DB::table('pembayaran_siswa_detail')->insert([
                'pembayaran_id' => $pembayaran->id,
                'siswa_id' => $siswaId,
                'jumlah' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new PendingRegistrationNotification($pembayaran));

        return back()->with('success', 'Registrasi ulang berhasil dikirim ke admin untuk verifikasi.');
    }
}
