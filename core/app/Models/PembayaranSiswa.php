<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSiswa extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_siswa';

    protected $fillable = [
        'user_id',
        'jenis',
        'jumlah_total',
        'status',
        'bukti_pembayaran',
        'catatan',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
    ];

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'reject'  => 'Gagal Verifikasi',
            'approve' => 'Verifikasi Berhasil',
            default   => 'Tidak diketahui'
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-500 text-white',
            'reject'  => 'bg-red-600 text-white',
            'approve' => 'bg-green-600 text-white',
            default   => 'bg-gray-400 text-white'
        };
    }

    public function getBuktiUrlAttribute()
    {
        // Jalankan jika path kosong
        if (!$this->bukti_pembayaran) {
            return null;
        }

        // Jika LOCAL, gunakan public/storage
        if (app()->environment('local')) {
            return asset('storage/' . str_replace('storage/', '', $this->bukti_pembayaran));
        }

        // Jika PRODUCTION, gunakan path langsung
        return asset($this->bukti_pembayaran);
    }

    // 🔹 Relasi ke orang tua
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔹 Relasi ke siswa tunggal (format lama)
    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    // 🔹 Relasi ke banyak siswa (format baru)
    public function details()
    {
        return $this->hasMany(PembayaranSiswaDetail::class, 'pembayaran_id');
    }

    // 🔹 Relasi ke admin yang memverifikasi
    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
