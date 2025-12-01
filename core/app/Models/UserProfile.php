<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class UserProfile extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 'phone', 'alamat', 'foto', 'nama_ayah', 'nama_ibu', 'nama_staff'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
