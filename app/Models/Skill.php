<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'topic_id', 'name', 'slug', 'description', 'sort_order', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_demo'    => 'boolean',
        ];
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'skill_id');
    }
}
