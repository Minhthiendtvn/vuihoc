<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'code', 'description',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function profiles()
    {
        return $this->belongsToMany(LearnerProfile::class, 'class_members', 'classroom_id', 'profile_id')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function members()
    {
        return $this->hasMany(ClassMember::class, 'classroom_id');
    }
}
