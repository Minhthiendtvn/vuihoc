<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'profile_id', 'target_type', 'target_id',
    ];

    protected function casts(): array
    {
        return [
            'target_id' => 'integer',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(LearnerProfile::class, 'profile_id');
    }
}
