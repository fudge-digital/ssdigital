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

        // Ambil pembayaran terakhir untuk orang tua ini
        $pembayaran = PembayaranSiswa::where('user_id', $parent->id)
            ->where('jenis', 'pendaftaran')
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

        return view('parent.dashboard', [
            'parent'            => $parent,
            'students'          => $children,
            'nonActiveStudents' => $nonActiveStudents,
            'totalPendaftaran'  => $totalPendaftaran,
            'pembayaran'        => $pembayaran,
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
        $jumlahTotal = $biayaPerSiswa * $validSiswa->count();

        $file = $request->file('bukti_pembayaran');
        $filename = 'pembayaran_' . $orangTua->id . '_' . time() . '.' . $file->getClientOriginalExtension();


        // Deteksi apakah harus langsung ke public_html/storage/
        $useDirectPublicStorage = env('USE_DIRECT_PUBLIC_STORAGE', false);

        if (!$useDirectPublicStorage && file_exists(public_path('storage'))) {
            // 🖥️ LOCAL: gunakan disk 'public' (storage/app/public)
            $path = $file->storeAs('bukti_pembayaran', $filename, 'public');
        } else {
            // 🌐 SERVER: simpan langsung ke public_html/storage/bukti_pembayaran
            $destinationPath = public_path('storage/bukti_pembayaran');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $path = 'storage/bukti_pembayaran/' . $filename;
        }

        $pembayaran = PembayaranSiswa::create([
            'user_id' => $orangTua->id,
            'jenis' => 'pendaftaran',
            'jumlah_total' => $jumlahTotal,
            'status' => 'pending',
            'bukti_pembayaran' => $path,
        ]);

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

}
