<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaySession extends Model
{
    protected $fillable = [
        'profile_id', 'lesson_id', 'game_type', 'token', 'status',
        'question_ids_json', 'started_at', 'expires_at', 'finished_at',
        'score', 'max_score', 'accuracy', 'duration_seconds', 'xp_earned',
        'answers_json',
    ];

    protected function casts(): array
    {
        return [
            'started_at'       => 'datetime',
            'expires_at'       => 'datetime',
            'finished_at'      => 'datetime',
            'score'            => 'integer',
            'max_score'        => 'integer',
            'accuracy'         => 'decimal:2',
            'duration_seconds' => 'integer',
            'xp_earned'        => 'integer',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(LearnerProfile::class, 'profile_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /** true nếu session chưa hoàn thành và còn hạn. */
    public function isPlayable(): bool
    {
        return $this->status === 'started' && $this->expires_at->isFuture();
    }
}
