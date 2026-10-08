<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'avatar_path',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /** Hồ sơ học viên của chính user (role learner). */
    public function learnerProfile()
    {
        return $this->hasOne(LearnerProfile::class, 'user_id');
    }

    /** Các hồ sơ học viên mà user này là phụ huynh (role parent). */
    public function childProfiles()
    {
        return $this->hasMany(LearnerProfile::class, 'parent_id');
    }

    /** Các lớp học mà user này sở hữu (role teacher/parent). */
    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'owner_id');
    }
}
