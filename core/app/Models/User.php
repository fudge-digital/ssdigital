<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'promo_type',
        'role',
    ];

    public function hasRole($roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isManajerTim(): bool { return $this->role === 'manajer_tim'; }
    public function isPelatih(): bool { return $this->role === 'pelatih'; }
    public function isAsistenPelatih(): bool { return $this->role === 'asisten_pelatih'; }
    public function isOrangTua(): bool { return $this->role === 'orang_tua'; }
    public function isSiswa(): bool { return $this->role === 'siswa'; }

    public function getPromoTypeLabelAttribute()
    {
        $type = $this->promo_type ?? 'none';
        $labels = config('promo.labels');
        return $labels[$type]['label'] ?? strtoupper($type);
    }

    public function getPromoPriceAttribute()
    {
        $type = $this->promo_type ?? 'none';
        $prices = config('promo.prices');
        return $prices[$type] ?? $prices['none'];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::created(function ($user) {

            if ($user->userProfile()->doesntExist()) {
                if ($user->role === 'orang_tua') {
                    $user->userProfile()->create(['nama_ayah' => $user->name]);
                } else {
                    $user->userProfile()->create(['nama_staff' => $user->name]);
                }
            }

            if ($user->role === 'siswa' && request()->is('admin/*')) {
                $user->siswaProfile()->create([
                    'nama_lengkap' => $user->name,
                    'status' => 'tidak_aktif',
                ]);
            }
        });
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function siswaProfile()
    {
        return $this->hasOne(SiswaProfile::class, 'user_id');
    }

    public function getProfileAttribute()
    {
        return $this->siswaProfile ?? $this->userProfile;
    }

    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id');
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function handledStudents()
    {
        return $this->belongsToMany(User::class, 'coach_student_category', 'coach_id', 'student_id');
    }

    // pembayaran yang dibuat oleh user (sebagai parent)
    public function pembayaranYangDibuat()
    {
        return $this->hasMany(\App\Models\PembayaranSiswa::class, 'user_id');
    }

    // pembayaran yang terkait siswa (jika user adalah siswa)
    public function pembayaranSebagaiSiswa()
    {
        return $this->hasMany(\App\Models\PembayaranSiswa::class, 'siswa_id');
    }

    public function iuranBulanan()
    {
        return $this->hasMany(\App\Models\IuranBulanan::class, 'siswa_id');
    }

    public function iuranRequests()
    {
        return $this->hasMany(\App\Models\IuranRequest::class, 'parent_id');
    }
}
