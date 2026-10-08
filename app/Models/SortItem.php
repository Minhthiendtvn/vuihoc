<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SortItem extends Model
{
    protected $fillable = [
        'question_id', 'item_text', 'category', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
