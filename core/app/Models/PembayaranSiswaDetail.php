<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSiswaDetail extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_siswa_detail';

    protected $fillable = [
        'pembayaran_id',
        'siswa_id',
        'jumlah',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(PembayaranSiswa::class, 'pembayaran_id');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
