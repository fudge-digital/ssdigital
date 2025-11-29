<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SiswaProfile;

class SiswaProfile extends Model
{
    protected $fillable = [
        'user_id', 'niss', 'nama_lengkap', 'nama_panggilan', 'foto', 'jenis_kelamin',
        'tempat_lahir', 'tanggal_lahir', 'usia', 'kelompok_umur', 'asal_sekolah',
        'size_jersey', 'nomor_jersey', 'tinggi_badan', 'berat_badan', 'status'
    ];

    public function getStatusLabelAttribute()
    {
        return match (strtolower($this->status)) {
            'tidak_aktif' => 'Belum Aktif',
            'aktif'       => 'Aktif',
            'suspended'   => 'Ditangguhkan',
            default       => '-',
        };
    }

    public function getJenisKelaminLabelAttribute()
    {
        return match ($this->jenis_kelamin) {
            'Laki-laki' => 'Putra',
            'Perempuan' => 'Putri',
            default => '-',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(StudentDocument::class, 'siswa_profile_id');
    }
}
