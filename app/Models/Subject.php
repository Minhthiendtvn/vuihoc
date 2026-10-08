<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon', 'color', 'description',
        'sort_order', 'is_published', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'sort_order'   => 'integer',
            'is_published' => 'boolean',
            'is_demo'      => 'boolean',
        ];
    }

    public function topics()
    {
        return $this->hasMany(Topic::class, 'subject_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
