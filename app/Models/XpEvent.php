<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XpEvent extends Model
{
    protected $fillable = [
        'profile_id', 'source', 'amount', 'meta_json',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(LearnerProfile::class, 'profile_id');
    }
}
