<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = [
        'subject_id', 'name', 'slug', 'description', 'icon', 'sort_order',
        'grade_min', 'grade_max', 'is_published', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'sort_order'   => 'integer',
            'grade_min'    => 'integer',
            'grade_max'    => 'integer',
            'is_published' => 'boolean',
            'is_demo'      => 'boolean',
        ];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function skills()
    {
        return $this->hasMany(Skill::class, 'topic_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForGrade(Builder $query, int $grade): Builder
    {
        return $query->where('grade_min', '<=', $grade)
                     ->where('grade_max', '>=', $grade);
    }
}
