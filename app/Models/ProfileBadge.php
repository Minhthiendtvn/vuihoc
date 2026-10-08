<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileBadge extends Model
{
    protected $fillable = [
        'profile_id', 'badge_id', 'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(LearnerProfile::class, 'profile_id');
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }
}
