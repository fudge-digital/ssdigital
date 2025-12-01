<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudentDocument extends Model
{
    //
    protected $fillable = [
        'siswa_profile_id',
        'type',
        'title',
        'file_path',
        'uploaded_by',
    ];

    protected $policies = [
        StudentDocument::class => StudentDocumentPolicy::class,
    ];

    // relasi ke siswa profile
    public function siswaProfile()
    {
        return $this->belongsTo(SiswaProfile::class, 'siswa_profile_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // helper untuk dapatkan url file
    public function getUrlAttribute()
    {
        // file_path disimpan relatif seperti "dokumen_siswa/{siswa_id}/filename.pdf"
        return Storage::disk('public')->url($this->file_path);
    }
}
