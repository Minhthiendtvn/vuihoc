<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'lesson_id', 'game_type', 'prompt', 'explanation',
        'difficulty', 'points', 'sort_order', 'grade', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'points'     => 'integer',
            'sort_order' => 'integer',
            'grade'      => 'integer',
            'is_demo'    => 'boolean',
        ];
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }

    public function pairs()
    {
        return $this->hasMany(MatchingPair::class, 'question_id');
    }

    public function sortItems()
    {
        return $this->hasMany(SortItem::class, 'question_id');
    }

    public function fillAnswers()
    {
        return $this->hasMany(FillAnswer::class, 'question_id');
    }
}
