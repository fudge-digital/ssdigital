<?php

namespace App\Http\Controllers\Siswas;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SiswaProfile;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Suppert\Facades\Hash;
use Carbon\Carbon;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        $student = User::where('role', 'siswa')
            ->with('siswaProfile')
            ->when($q, fn($query) =>
                $query->where('name', 'like', "%$q%")
            )
            ->orderBy('name')
            ->paginate(15);

        return view('siswa.index', compact('student'));
    }

    public function dashboard()
    {
        $now = Carbon::now();
        
        $student = auth()->user()
        ->load(['userProfile', 'siswaProfile', 'parents', 'parents.userProfile']);

        $jadwalLatihan = Post::with('category')
            ->whereHas('category', function($q){
                $q->where('slug', 'jadwal-latihan');
            })
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->latest()
            ->first(); // ambil satu post terbaru bulan ini

        return view('siswa.dashboard', compact('student', 'jadwalLatihan'));
    }

    public function show($id)
    {
        // Ambil siswa beserta profile dan relasi penting lainnya
        $student = User::with(['userProfile', 'siswaProfile', 'parents', 'parents.userProfile'])
            ->where('id', $id)
            ->where('role', 'siswa')
            ->findOrFail($id);

        // Jika role orang_tua, pastikan hanya boleh lihat anaknya
        if (auth()->user()->isOrangTua() && !$student->parents->contains(auth()->id())) {
            abort(403, 'Anda tidak memiliki akses untuk melihat detail siswa ini.');
        }

        // Jika siswa sendiri, boleh lihat
        if (auth()->user()->isSiswa() && auth()->id() !== $student->id) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Jika admin, pelatih, asisten pelatih, manajer tim -> akses full
        // Tidak perlu pengecekan tambahan

        return view('siswa.show', compact('student'));
    }

    public function edit($id)
    {
        $user = User::with('siswaProfile', 'parents')->where('id', $id)->where('role', 'siswa')->firstOrFail();
        
        // Authorization rules:
        // - super_admin & admin can edit all
        // - parent can edit if related to the siswa
        // - siswa can edit only if it's himself
        // - others (pelatih etc) cannot edit -> return 403
        $me = auth()->user();

        if ($me->isSuperAdmin() || $me->isAdmin()) {
            // allowed
        } elseif ($me->isOrangTua()) {
            // check if parent of this siswa
            if (!$user->parents->contains($me->id)) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit siswa ini.');
            }
        } elseif ($me->isSiswa()) {
            if ($me->id !== $user->id) {
                abort(403, 'Anda hanya boleh mengedit profil Anda sendiri.');
            }
        } else {
            // manajer_tim, pelatih, asisten_pelatih -> tidak boleh
            abort(403, 'Anda tidak memiliki akses untuk mengedit data ini.');
        }

        $student = $user;
        $profile = $user->siswaProfile;

        // determine editable fields for blade
        $editableFields = $this->getEditableFieldsFor($me);

        return view('siswa.edit', compact('user', 'profile', 'editableFields'));
    }

    public function update(Request $request, $id)
    {
        $user = User::with('siswaProfile','parents')
            ->where('id', $id)
            ->where('role', 'siswa')
            ->firstOrFail();

        $me = auth()->user();

        // Authorization (same as edit)
        if ($me->isSuperAdmin() || $me->isAdmin()) {
            // allowed
        } elseif ($me->isOrangTua()) {
            if (!$user->parents->contains($me->id)) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit siswa ini.');
            }
        } elseif ($me->isSiswa()) {
            if ($me->id !== $user->id) {
                abort(403, 'Anda hanya boleh mengedit profil Anda sendiri.');
            }
        } else {
            abort(403, 'Anda tidak memiliki akses untuk mengedit data ini.');
        }

        $editableFields = $this->getEditableFieldsFor($me);

        // ============ VALIDATION ============ //
        $rules = [];

        // Email
        if (in_array('user.email', $editableFields) || in_array('all', $editableFields)) {
            $rules['email'] = ['required', 'email', Rule::unique('users','email')->ignore($user->id)];
        }

        // Password optional
        if (in_array('user.password', $editableFields) || in_array('all', $editableFields)) {
            $rules['password'] = ['nullable', 'string', 'min:6'];
        }

        // Foto Profile
        if (in_array('profile.foto', $editableFields) || in_array('all', $editableFields)) {
            $rules['foto'] = ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'];
        }

        // Profile fields
        $profileFields = [
            'niss','nama_panggilan','no_whatsapp','tempat_lahir','tanggal_lahir','foto',
            'asal_sekolah','size_jersey','nomor_jersey','tinggi_badan','berat_badan','status'
        ];

        foreach ($profileFields as $pf) {
            if (in_array("profile.$pf", $editableFields) || in_array('all', $editableFields)) {
                $rules[$pf] = ['nullable'];
            }
        }

        $validated = $request->validate($rules);

        // ============ UPDATE USER ============ //
        if ($request->filled('email') && (in_array('user.email',$editableFields) || in_array('all',$editableFields))) {
            $user->email = $request->email;
        }

        if ($request->filled('password') && (in_array('user.password',$editableFields) || in_array('all',$editableFields))) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // ============ UPDATE PROFILE ============ //
        $profile = $user->siswaProfile ?? $user->siswaProfile()->create([]);

        foreach ($profileFields as $pf) {
            if (($request->filled($pf) || $request->has($pf)) &&
                (in_array("profile.$pf",$editableFields) || in_array('all',$editableFields))) {
                $profile->$pf = $request->$pf;
            }
        }

        $profile->save();

        return redirect()->route('siswa.edit', $user->id)
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function updatePhoto(Request $request, $id)
    {
        $request->validate([
            'foto_base64' => 'required',
        ]);

        $profile = SiswaProfile::findOrFail($id);

        $base64 = $request->foto_base64;
        $decoded = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
        $imageData = base64_decode($decoded);

        $filename = 'foto_' . $profile->user_id . '_' . time() . '.webp';

        $useDirectPublicStorage = env('USE_DIRECT_PUBLIC_STORAGE', false);

        if (!$useDirectPublicStorage && file_exists(public_path('storage'))) {
            Storage::disk('public')->put('foto_siswa/' . $filename, $imageData);
            $filepath = 'storage/foto_siswa/' . $filename;
        } else {
            $destinationPath = public_path('storage/foto_siswa');
            if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
            file_put_contents($destinationPath . '/' . $filename, $imageData);
            $filepath = 'storage/foto_siswa/' . $filename;
        }

        // delete old
        if ($profile->foto) {
            $old = str_replace('storage/', '', $profile->foto);
            if (Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            } else {
                $oldPublic = public_path($profile->foto);
                if (file_exists($oldPublic)) unlink($oldPublic);
            }
        }

        $profile->update(['foto' => $filepath]);

        return response()->json([
            'success' => true,
            'filepath' => asset($filepath)
        ]);
    }


    // Private helper: return array of editable fields for current auth user
    private function getEditableFieldsFor(User $me): array
    {
        // base mapping
        if ($me->isSuperAdmin()) {
            return ['all'];
        }

        if ($me->isAdmin()) {
            return [
                'user.email','user.password',
                'profile.niss','profile.foto','profile.nama_panggilan','profile.no_whatsapp','profile.tempat_lahir','profile.tanggal_lahir',
                'profile.asal_sekolah','profile.size_jersey','profile.nomor_jersey','profile.tinggi_badan','profile.berat_badan','profile.status'
            ];
        }

        if ($me->isOrangTua()) {
            return [
                'user.email','user.password', 
                'profile.foto','profile.nama_panggilan','profile.no_whatsapp','profile.tempat_lahir','profile.tanggal_lahir',
                'profile.asal_sekolah','profile.size_jersey','profile.tinggi_badan','profile.berat_badan'
            ];
        }

        if ($me->isSiswa()) {
            return [
                'profile.foto','profile.nama_panggilan','profile.no_whatsapp','profile.tempat_lahir','profile.tanggal_lahir',
                'profile.asal_sekolah','profile.size_jersey','profile.tinggi_badan','profile.berat_badan'
            ];
        }

        // manajer_tim, pelatih, asisten_pelatih => no edit
        return [];
    }

}
