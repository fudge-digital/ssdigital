<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IuranBulanan;
use App\Models\IuranRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Notifications\IuranPendingNotification;

class AdminIuranController extends Controller
{
    // INDEX: Menampilkan semua iuran untuk admin
    public function index(Request $request)
    {
        $search = $request->search;

        $iuran = IuranBulanan::with(['siswa.siswaProfile', 'siswa.parents.userProfile'])
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('siswa', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhereHas('parents.userProfile', function ($p) use ($search) {
                              $p->where('nama_ayah', 'like', "%{$search}%");
                          });
                    });
                })
                ->orderByDesc('bulan')
                ->orderBy('status')
                ->paginate(25);
        
        // Parent pending untuk modal Generate Bulk
        $pendingParents = User::where('role', 'orang_tua')
        ->whereHas('iuranRequests', fn($q) => $q->where('status', 'pending'))
        ->get();
        
        $parents = User::where('role', 'orang_tua')->get();

        return view('admin.iuran.index', compact('iuran', 'parents', 'pendingParents', 'search'));
    }

    public function generate()
    {
        $bulan = Carbon::now()->format('Y-m');

        if (IuranBulanan::where('bulan', $bulan)->exists()) {
            return back()->with('error','Tagihan bulan ini sudah ada.');
        }

        $students = User::where('role','siswa')
            ->whereHas('siswaProfile', fn($q) => $q->where('status','aktif'))
            ->with('parents') // untuk mengambil parent cepat
            ->get();

        $prices = config('promo.prices');

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $parent = $student->parents->first(); // jika ada lebih dr 1 parent, gunakan logic bisnis Anda

                $promoType = $parent->promo_type ?? 'none';
                $price = $prices[$promoType] ?? $prices['none'];

                IuranBulanan::create([
                    'siswa_id' => $student->id,
                    'bulan'    => $bulan,
                    'jumlah'   => $price,
                    'status'   => 'unpaid',
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate: ' . $e->getMessage());
        }

        return back()->with('success', 'Tagihan bulan ini berhasil digenerate.');
    }

    public function verifikasi()
    {
        $pending = IuranBulanan::where('status', 'pending')->with('siswa.siswaProfile')->paginate(20);

        return view('admin.iuran.verifikasi', compact('pending'));
    }

    public function approve($id)
    {
        $iuran = IuranBulanan::findOrFail($id);
        $iuran->update([
            'status' => 'paid',
        ]);

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function bulkVerify(Request $request)
    {
        $ids = $request->iuran_ids;

        if (!$ids) {
            return back()->with('error','Tidak ada item yang dipilih.');
        }

        $admin = auth()->user(); // user yang sedang login

        IuranBulanan::whereIn('id', $ids)->update([
            'status' => 'paid',
            'diverifikasi_oleh' => $admin->id,
        ]);

        return back()->with('success', 'Berhasil memverifikasi ' . count($ids) . ' pembayaran.');
    }

    // Tampilkan list request pending
    public function requests()
    {
        // Ambil semua request billing dari orang tua
        $requests = IuranRequest::with(['parent.userProfile', 'parent.children.userProfile'])
            ->latest()
            ->paginate(15);

        return view('admin.iuran.requests.index', compact('requests'));
    }

    // 1) menampilkan detail request (dipanggil oleh AJAX untuk modal)
    public function requestDetail($id)
    {
        $req = IuranRequest::with('parent.children.userProfile')->findOrFail($id);

        $parentName = $req->parent->userProfile->nama_lengkap ?? $req->parent->name;

        // List nama siswa
        $students = $req->parent->children->map(function($child){
            return $child->userProfile->nama_lengkap ?? $child->name;
        })->implode(', ');

        return response()->json([
            'parent' => $parentName,
            'students' => $students,
            'student_count' => $req->student_count,
            'months' => $req->months . ' bulan',
            'month_list' => $req->month_list,   // ← kirim array
            'total_tagihan' => number_format($req->total_tagihan, 0, ',', '.'),
            'created_at' => $req->created_at->format('d M Y H:i')
        ]);
    }

    // 2) approve request: buat IuranBulanan sesuai jumlah bulan dan aturan harga
    public function approveRequest(Request $request, $id)
    {
        \Log::info("APPROVE REQUEST HIT ID: $id");
        $req = IuranRequest::with('parent.children.userProfile')->findOrFail($id);

        if ($req->status === 'approved') {
            return back()->with('info', 'Request sudah disetujui sebelumnya.');
        }

        $months = (int) $req->months;
        $prices = config('iuran.prices'); // ambil harga dari config

        DB::beginTransaction();
        try {
            $batchId = (string) Str::uuid();
            $created = 0;

            // mulai bulan depan
            $start = now()->startOfMonth()->addMonth();

            foreach ($req->parent->children as $child) {

                // ambil promo type siswa (default none)
                $promoType = optional($child->userProfile)->promo_type ?? 'none';

                // harga sesuai promo
                $pricePerMonth = $prices[$promoType] ?? $prices['none'];

                for ($i = 0; $i < $months; $i++) {
                    $bulan = $start->copy()->addMonths($i)->format('Y-m');

                    if (IuranBulanan::where('siswa_id', $child->id)->where('bulan', $bulan)->exists()) {
                        continue;
                    }

                    IuranBulanan::create([
                        'siswa_id' => $child->id,
                        'bulan' => $bulan,
                        'jumlah' => $pricePerMonth,
                        'status' => 'unpaid',
                        'request_type' => 'bulk',
                        'request_batch_id' => $batchId,
                    ]);

                    $created++;
                }
            }

            $req->update(['status' => 'approved']);
            DB::commit();

            return back()->with('success', "Berhasil generate $created tagihan untuk {$months} bulan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('approveRequest error: '.$e->getMessage());
            return back()->with('error', 'Gagal generate tagihan saat approve.');
        }
    }

    // 3) generateBulk (jika admin memanggil generateBulk dengan request_id, update pricing logic di sini juga)
    public function generateBulk(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:users,id',
            'start_month' => 'nullable|date_format:Y-m',
            'request_id' => 'required|exists:iuran_requests,id'
        ]);

        $req = IuranRequest::findOrFail($request->request_id);
        $parent = User::findOrFail($request->parent_id);
        $months = (int) $req->months;

        $studentIds = $parent->children->pluck('id')->toArray();
        if (empty($studentIds)) {
            return back()->with('error', 'Parent tidak memiliki siswa.');
        }

        $start = $request->start_month
            ? Carbon::createFromFormat('Y-m', $request->start_month)->startOfMonth()
            : now()->startOfMonth()->addMonth();

        $batchId = (string) Str::uuid();
        $created = 0;

        DB::beginTransaction();
        try {
            for ($i = 0; $i < $months; $i++) {
                $bulan = $start->copy()->addMonths($i)->format('Y-m');

                foreach ($studentIds as $siswaId) {
                    if (IuranBulanan::where('siswa_id', $siswaId)->where('bulan', $bulan)->exists()) {
                        continue;
                    }

                    // harga per bulan (sesuai koreksi)
                    if ($months <= 3) {
                        $nominal = 325000;
                    } elseif ($months <= 6) {
                        $nominal = 300000;
                    } else {
                        $siswa = User::find($siswaId);
                        $nominal = $siswa->userProfile->nominal_iuran ?? 0;
                    }

                    IuranBulanan::create([
                        'siswa_id' => $siswaId,
                        'bulan' => $bulan,
                        'jumlah' => $nominal,
                        'status' => 'unpaid',
                        'request_type' => 'bulk',
                        'request_batch_id' => $batchId
                    ]);

                    $created++;
                }
            }

            $req->update(['status' => 'approved']);

            DB::commit();
            return back()->with('success', "Berhasil generate $created tagihan untuk $months bulan mulai {$start->format('Y-m')} (batch: $batchId).");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('generateBulk error: '.$e->getMessage());
            return back()->with('error', 'Gagal generate tagihan.');
        }
    }

}

