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
        $siswaIds = $parent->children->pluck('id'); // untuk parent view siswa langsung

        $iuran = IuranBulanan::with('siswa')
            ->whereIn('siswa_id', $siswaIds)
            ->orderByDesc('bulan')
            ->paginate(12);
        
        // total per bulan aktif (misal bulan terbaru)
        $latestMonth = IuranBulanan::whereIn('siswa_id', $siswaIds)->max('bulan');
        $currentIuran = IuranBulanan::whereIn('siswa_id', $siswaIds)
        ->where('bulan', $latestMonth)
        ->get();

        $total = $currentIuran->sum('jumlah');  
        $childNames = $students->pluck('nama')->implode(', ');

        return view('parent.iuran.index', compact('iuran', 'childNames', 'latestMonth'));
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
        $latestMonth = request('bulan');

        // Ambil semua iuran bulan terkait
        $iurans = IuranBulanan::whereIn('siswa_id', $siswaIds)
            ->where('bulan', $latestMonth)
            ->where('status', 'unpaid')
            ->get();

        $bulan = $iurans->first()->bulan;

        // filename
        $file = $request->file('bukti');
        $filename = 'iuran_' . $bulan . '.' . $file->getClientOriginalExtension();

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
        $studentCount = $parent->children->count(); // support banyak anak
        $months = $request->months;

        // Tentukan harga per siswa per bulan
        $pricePerStudent = ($months == 3) ? 325000 : 300000;

        // Hitung total tagihan
        $totalTagihan = $studentCount * $pricePerStudent * $months;

        $iuranRequest = IuranRequest::create([
            'parent_id' => $parent->id,
            'months' => $months,
            'student_count' => $studentCount,
            'total_tagihan' => $totalTagihan,
            'status' => 'pending'
        ]);

        // Notifikasi ke admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new IuranRequestNotification('request_created', $iuranRequest));
        }

        return back()->with('success', 'Request berhasil dikirim dan menunggu persetujuan admin.');
    }

}
