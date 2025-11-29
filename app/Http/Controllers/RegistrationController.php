<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\SiswaProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\StudentHelper;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParentRegistrationMail;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // 1️⃣ Validasi data orang tua
        $request->validate([
            'nama_ayah' => 'required|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'alamat' => 'required|string',
            'siswa' => 'required|array|min:1',
            'siswa.*.nama_lengkap' => 'required|string|max:255',
            'siswa.*.nama_panggilan' => 'nullable|string|max:255',
            'siswa.*.jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'siswa.*.tempat_lahir' => 'nullable|string',
            'siswa.*.tanggal_lahir' => 'required|date',
            'siswa.*.asal_sekolah' => 'nullable|string',
            'siswa.*.size_jersey' => 'nullable|string|max:10',
        ]);

        // 2️⃣ Buat akun orang tua
        $passwordOrtu = Str::random(8);
        $parent = User::create([
            'name' => $request->nama_ayah,
            'email' => $request->email,
            'password' => Hash::make($passwordOrtu),
            'role' => 'orang_tua',
        ]);

        UserProfile::create([
            'user_id' => $parent->id,
            'phone' => $request->phone,
            'alamat' => $request->alamat,
            'nama_ayah' => $request->nama_ayah,
            'nama_ibu' => $request->nama_ibu,
        ]);

        // 3️⃣ Loop semua siswa yang didaftarkan
        $students = [];
        foreach ($request->siswa as $siswaData) {
            $namaDepan = strtolower(Str::slug(Str::words($siswaData['nama_lengkap'], 1, '')));
            $emailSiswa = "{$namaDepan}@siswa.login";
            $passwordSiswa = 'pass_siswa';

            $student = User::create([
                'name' => $siswaData['nama_lengkap'],
                'email' => $emailSiswa,
                'password' => Hash::make($passwordSiswa),
                'role' => 'siswa',
            ]);

            $usia = StudentHelper::hitungUsia($siswaData['tanggal_lahir']);
            $kelompokUmur = StudentHelper::kelompokUmur($siswaData['tanggal_lahir']);

            SiswaProfile::create([
                'user_id' => $student->id,
                'nama_lengkap' => $siswaData['nama_lengkap'],
                'nama_panggilan' => $siswaData['nama_panggilan'] ?? null,
                'jenis_kelamin' => $siswaData['jenis_kelamin'],
                'tempat_lahir' => $siswaData['tempat_lahir'] ?? null,
                'tanggal_lahir' => $siswaData['tanggal_lahir'],
                'usia' => $usia,
                'kelompok_umur' => $kelompokUmur,
                'asal_sekolah' => $siswaData['asal_sekolah'] ?? null,
                'size_jersey' => $siswaData['size_jersey'] ?? null,
                'status' => 'tidak_aktif',
            ]);

            // relasi orang tua ↔ siswa
            $parent->children()->attach($student->id);

            $students[] = [
                'name' => $student->name,
                'email' => $student->email,
                'usia' => $usia,
                'password' => $passwordSiswa, // password asli (plain)
            ];
        }

        // 4️⃣ Kirim email konfirmasi ke orang tua
        Mail::to($parent->email)->send(new ParentRegistrationMail($parent, $passwordOrtu, $students));

        return redirect()->route('register.success')->with('success', 'Pendaftaran berhasil! Silakan cek email untuk detail login.');
    }
}
