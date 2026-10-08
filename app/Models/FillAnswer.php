<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FillAnswer extends Model
{
    protected $fillable = [
        'question_id', 'blank_index', 'answer_text', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'blank_index' => 'integer',
            'sort_order'  => 'integer',
        ];
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
