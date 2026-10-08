<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'criteria', 'threshold', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'integer',
            'is_demo'   => 'boolean',
        ];
    }

    public function profiles()
    {
        return $this->belongsToMany(LearnerProfile::class, 'profile_badges', 'badge_id', 'profile_id')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
