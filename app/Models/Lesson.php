<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'skill_id', 'title', 'slug', 'objective', 'difficulty',
        'duration_minutes', 'instructions', 'summary', 'sort_order', 'status', 'grade', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'sort_order'       => 'integer',
            'grade'            => 'integer',
            'is_demo'          => 'boolean',
        ];
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'lesson_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Đếm số câu hỏi theo từng kiểu chơi (quiz/matching/sort/fill).
     * Kết quả: thuộc tính quiz_count, matching_count, sort_count, fill_count.
     */
    public function scopeWithQuestionTypeCounts(Builder $query): Builder
    {
        foreach (['quiz', 'matching', 'sort', 'fill'] as $type) {
            $query->withCount([
                "questions as {$type}_count" => fn ($q) => $q->where('game_type', $type),
            ]);
        }

        return $query;
    }
}
