<?php

namespace App\Http\Controllers;

use App\Models\IuranBulanan;
use App\Models\IuranRequest;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\IuranPendingNotification;
use App\Notifications\IuranRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IuranBulananController extends Controller
{
    //
    public function index()
    {
        $parent = Auth::user();
        $students = $parent->children;
        $siswaIds = $students->pluck('id'); // untuk parent view siswa langsung

        $iuran = IuranBulanan::with('siswa')
            ->whereIn('siswa_id', $siswaIds)
            ->orderByDesc('bulan')
            ->paginate(12);

        // Ambil bulan terbaru
        $latestMonth = IuranBulanan::whereIn('siswa_id', $siswaIds)->max('bulan');

        // Ambil semua iuran bulan terbaru
        $currentIuran = IuranBulanan::whereIn('siswa_id', $siswaIds)
            ->where('bulan', $latestMonth)
            ->get();

        // Semua unpaid bulan terbaru (menggunakan status 'unpaid' sesuai diskusi)
        $unpaid = $currentIuran->where('status', 'unpaid');
        $paid = $currentIuran->where('status', 'paid');
        $pending = $currentIuran->where('status', 'pending');

        // Hitung total dari unpaid saja
        $total = $unpaid->sum('jumlah');

        // child names sebagai collection untuk foreach di blade
        // Pastikan nama kolom di model siswa: 'name' atau 'nama' — sesuaikan jika beda
        $childNames = $students->pluck('name'); // <-- collection

        // juga sediakan versi string untuk WhatsApp
        $childNamesString = $childNames->implode(', ');

        return view('parent.iuran.index', compact(
            'iuran',
            'childNames',        // collection untuk foreach
            'childNamesString',  // string untuk WA atau teks
            'latestMonth',
            'total',
            'unpaid',
            'paid',
            'pending',
            'currentIuran'
        ));
    }

    public function uploadBukti(Request $request, IuranBulanan $iuran)
    {
        $request->validate([
            'bukti' => 'required|image|max:2048',
            'tanggal_bayar' => 'required|date',
            'catatan'       => 'string|nullable'
        ]);

        // filename
        $file = $request->file('bukti');
        $filename = 'iuran_' . $iuran->bulan . '.' . $file->getClientOriginalExtension();

        // storage logic multi environment
        $useDirectPublicStorage = env('USE_DIRECT_PUBLIC_STORAGE', false);

        if (!$useDirectPublicStorage && file_exists(public_path('storage'))) {
            // Local
            $path = $file->storeAs('bukti_iuran', $filename, 'public');
        } else {
            // Server
            $destination = public_path('storage/bukti_iuran');
            if (!file_exists($destination)) mkdir($destination, 0755, true);

            $file->move($destination, $filename);
            $path = 'storage/bukti_iuran/' . $filename;
        }

        // semua anak dari parent pada bulan yang sama
        $students = Auth::user()->children->pluck('id');

        $iuranList = IuranBulanan::whereIn('siswa_id', $students)
        ->where('bulan', $iuran->bulan)
        ->get();

        // Update record
        $iuran->update([
            'bukti' => $path,
            'status' => 'pending',
            'tanggal_bayar' => $request->tanggal_bayar,
            'catatan'   => $request->catatan
        ]);

        // === NOTIFIKASI UNTUK ADMIN ===
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new IuranPendingNotification([
                'parent_name' => Auth::user()->name,
                'bulan'       => $iuran->bulan,
                'amount'      => $iuran->jumlah,
            ]));
        }

        return back()->with('success', 'Bukti pembayaran berhasil diupload, menunggu verifikasi');
    }

    public function uploadBuktiTotal(Request $request)
    {
        $request->validate([
            'bukti' => 'required|image|max:2048',
            'tanggal_bayar' => 'required|date',
            'catatan' => 'nullable|string'
        ]);

        $parent = Auth::user();
        $siswaIds = $parent->children->pluck('id');
        $latestMonth = IuranBulanan::whereIn('siswa_id', $siswaIds)->max('bulan');

        // Ambil semua iuran bulan terkait
        $iurans = IuranBulanan::whereIn('siswa_id', $siswaIds)
            ->where('bulan', $latestMonth)
            ->where('status', 'unpaid')
            ->get();
        
        if ($iurans->isEmpty()) {
            return back()->with('error', 'Tidak ada tagihan unpaid pada bulan ini.');
        }

        $bulan = $iurans->first()->bulan;

        $file = $request->file('bukti');
        $filename = 'iuran_' . $bulan . '.' . $file->getClientOriginalExtension();

        $useDirectPublicStorage = env('USE_DIRECT_PUBLIC_STORAGE', false);

        // ===== LOCAL MODE (XAMPP, Windows) =====
        if (!$useDirectPublicStorage) {
            // Simpan ke storage/app/public/bukti_iuran
            $path = $file->storeAs('bukti_iuran', $filename, 'public');
        } 

        // ===== SERVER MODE (Shared Hosting) =====
        else {
            $basePath = public_path('storage');
            $destination = public_path('storage/bukti_iuran');

            // Buat folder public/storage jika belum ada
            if (!is_dir($basePath)) {
                mkdir($basePath, 0755, true);
            }

            // Buat folder public/storage/bukti_iuran
            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            // Move file
            $file->move($destination, $filename);

            // Save path relative untuk DB
            $path = 'storage/bukti_iuran/' . $filename;
        }

        // Update massal
        foreach ($iurans as $iuran) {
            $iuran->update([
                'bukti' => $path,
                'status' => 'pending',
                'tanggal_bayar' => $request->tanggal_bayar,
                'catatan' => $request->catatan
            ]);
        }

        // === NOTIFIKASI UNTUK ADMIN ===
        $admins = User::where('role', 'admin')->get();
        $totalNotifikasi = $iurans->sum('jumlah');

        foreach ($admins as $admin) {
            $admin->notify(new IuranPendingNotification([
                'parent_name' => Auth::user()->name,
                'bulan'       => $iuran->bulan,
                'amount'      => $iuran->jumlah,
            ]));
        }

        return back()->with('success', 'Bukti pembayaran berhasil diupload, menunggu verifikasi admin');
    }

    public function verify(IuranBulanan $iuran)
    {
        $admin = auth()->user(); // user yang sedang login
        $students = $iuran->siswa->parent->children->pluck('id');

        IuranBulanan::whereIn('siswa_id', $students)
        ->where('bulan', $iuran->bulan)
        ->update([
            'status' => 'paid',
            'tanggal_bayar' => now(),
            'diverifikasi_oleh' => $admin->userProfile->nama_staff ?? $admin->name
        ]);

        // === NOTIFIKASI UNTUK ORANG TUA ===
        $parent->notify(new IuranVerifiedNotification([
            'bulan' => $iuran->bulan,
            'amount' => $iuran->jumlah
        ]));

        return back()->with('success', 'Pembayaran berhasil diverifikasi');
    }

    // FORM Request Tagihan
    public function requestForm()
    {
        $parent = Auth::user();
        $child = $parent->children->first(); // asumsi 1 anak, nanti bisa adjust multi

        if (!$child) {
            return back()->with('error', 'Anda belum memiliki siswa.');
        }

        $currentMonth = now()->format('Y-m');

        // Cek apakah bulan berjalan sudah paid
        $hasPaid = IuranBulanan::where('siswa_id', $child->id)
            ->where('bulan', $currentMonth)
            ->where('status', 'paid')
            ->exists();

        return view('parent.iuran.request', compact('hasPaid'));
    }

    // STORE REQUEST TAGIHAN
    public function submitRequest(Request $request)
    {
        $request->validate([
            'months' => 'required|in:3,6'
        ]);

        $parent = auth()->user();
        $students = $parent->children;

        if ($students->isEmpty()) {
            return back()->with('error', 'Anda tidak memiliki siswa.');
        }

        $months = $request->months;

        // Tentukan bulan berjalan
        $currentMonth = now()->format('Y-m');

        // Cek apakah bulan berjalan masih unpaid untuk salah satu anak
        $hasUnpaidCurrentMonth = IuranBulanan::whereIn('siswa_id', $students->pluck('id'))
            ->where('bulan', $currentMonth)
            ->where('status', 'unpaid')
            ->exists();

        // Jika masih unpaid → mulai dari bulan ini, jika sudah paid → mulai bulan depan
        $startMonth = $hasUnpaidCurrentMonth
            ? $currentMonth
            : now()->addMonth()->format('Y-m');

        // Harga sesuai aturan baru
        $promoPrices = config('promo.prices');
        $pricePerStudent = ($months == 3) ? 325000 : 300000;

        $studentCount = $students->count();
        $totalTagihan = $studentCount * $pricePerStudent * $months;

        // SIMPAN REQUEST
        $req = IuranRequest::create([
            'parent_id'     => $parent->id,
            'months'        => $months,
            'student_count' => $studentCount,
            'total_tagihan' => $totalTagihan,
            'month'         => $startMonth, // ← PENTING
            'status'        => 'pending',
        ]);

        // NOTIFIKASI KE ADMIN
        foreach (User::where('role', 'admin')->get() as $admin) {
            $admin->notify(new IuranRequestNotification('request_created', $req));
        }

        return back()->with('success', 'Request berhasil dikirim dan menunggu persetujuan admin.');
    }

}
