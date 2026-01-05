<?php

namespace App\Http\Controllers\Parents;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Controller;
use App\Models\PembayaranSiswa;
use App\Models\Post;
use App\Models\User;
use App\Services\ParentFinanceSummaryService;
use App\Notifications\ParentVerificationApprovedNotification;

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
        $jumlahSiswa = $nonActiveStudents->count();
        $totalPendaftaran = $jumlahSiswa * $biayaPerSiswa;

        // Cek pembayaran online
        $pembayaran = PembayaranSiswa::where('user_id', $parent->id)
            ->whereIn('status', ['pending', 'approve', 'reject'])
            ->whereIn('jenis', ['Daftar_Ulang', 'Pendaftaran_Baru', 'Pembayaran_RAB', 'Pembayaran_Jersey'])
            ->latest()
            ->first();

        $jadwalLatihan = Post::with('category')
            ->whereHas('category', function ($q) {
                $q->where('slug', 'jadwal-latihan');
            })
            ->whereMonth('published_at', now()->month)
            ->whereYear('published_at', now()->year)
            ->orderBy('published_at', 'desc')
            ->first();
        
        // Notifikasi verifikasi pembayaran (jenis pendaftaran / iuran)
        $approvedNotification = $parent->notifications()
            ->where('type', 'App\Notifications\ParentVerificationApprovedNotification')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('parent.dashboard', [
            'parent'            => $parent,
            'students'          => $children,
            'nonActiveStudents' => $nonActiveStudents,
            'totalPendaftaran'  => $totalPendaftaran,
            'pembayaran'        => $pembayaran,
            'jadwalLatihan'     => $jadwalLatihan,
            'approvedNotification' => $approvedNotification,
            'notificationCount' => $approvedNotification->count(),
        ]);
    }

    public function uploadPembayaran(Request $request)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|max:4096', // 4MB limit
            'jumlah' => 'nullable|numeric|min:0',
            'regType' => 'required|in:Daftar_Ulang,Pendaftaran_Baru',
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
            'jenis' => $request->regType,
            'jumlah_total' => $totalPendaftaran,
            'status' => 'pending',
            'bukti_pembayaran' => $path,
        ]);

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new PendingReRegistrationNotification($pembayaran));

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
        $request->validate([
            'siswa_ids' => 'required|string',
            'regType' => 'required|in:Daftar_Ulang,Pendaftaran_Baru',
        ]);

        $parent = Auth::user();
        $siswaIds = explode(',', $request->siswa_ids);

        // Hitung total (0 untuk re-registration)
        $total = $request->regType === 'Daftar_Ulang' ? 0 : 650000 * count($siswaIds);

        // Buat header pembayaran
        $pembayaran = PembayaranSiswa::create([
            'user_id' => $parent->id,
            'jenis' => $request->regType,
            'jumlah_total' => $total,
            'status' => 'pending',
        ]);

        // Insert per siswa
        foreach ($siswaIds as $siswaId) {
            DB::table('pembayaran_siswa_detail')->insert([
                'pembayaran_id' => $pembayaran->id,
                'siswa_id' => $siswaId,
                'jumlah' =>$request->regType === 'Daftar_Ulang' ? 0 : 650000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new PendingReRegistrationNotification($pembayaran));

        return back()->with('success', 'Registrasi ulang berhasil dikirim ke admin untuk verifikasi.');
    }

    
}
