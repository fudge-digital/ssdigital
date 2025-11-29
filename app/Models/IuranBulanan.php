<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IuranBulanan extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'bulan',
        'jumlah',
        'status',
        'bukti',
        'catatan',
        'diverifikasi_oleh',
        'tanggal_bayar',
        'request_batch_id',
        'request_type',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function diverifikasiOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('bulan', now()->format('Y-m'));
    }
}
