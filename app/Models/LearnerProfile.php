<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearnerProfile extends Model
{
    protected $fillable = [
        'user_id', 'parent_id', 'display_name', 'avatar_emoji', 'grade',
        'daily_goal', 'font_size', 'total_xp', 'level',
        'current_streak', 'longest_streak', 'last_play_date',
    ];

    protected function casts(): array
    {
        return [
            'grade'          => 'integer',
            'daily_goal'     => 'integer',
            'total_xp'       => 'integer',
            'level'          => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_play_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function playSessions()
    {
        return $this->hasMany(PlaySession::class, 'profile_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'profile_id');
    }

    public function xpEvents()
    {
        return $this->hasMany(XpEvent::class, 'profile_id');
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'profile_badges', 'profile_id', 'badge_id')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
