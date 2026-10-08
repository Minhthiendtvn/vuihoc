<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'created_by', 'learner_profile_id', 'classroom_id', 'lesson_id',
        'game_type', 'note', 'deadline', 'share_token', 'status', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline'     => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function profile()
    {
        return $this->belongsTo(LearnerProfile::class, 'learner_profile_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /** true nếu đang pending và đã qua hạn. */
    public function isOverdue(): bool
    {
        return $this->status === 'pending'
            && $this->deadline !== null
            && $this->deadline->isPast();
    }

    /** Nhãn trạng thái hiển thị: Chưa làm / Đã làm / Quá hạn. */
    public function statusLabel(): string
    {
        if ($this->status === 'done') {
            return 'Đã làm';
        }

        return $this->isOverdue() ? 'Quá hạn' : 'Chưa làm';
    }

    /** true nếu bài giao cho cả lớp. */
    public function isForClass(): bool
    {
        return $this->classroom_id !== null;
    }

    /** Đối tượng được giao: tên con hoặc tên lớp. */
    public function targetName(): string
    {
        if ($this->isForClass()) {
            return 'Cả lớp ' . ($this->classroom?->name ?? '');
        }

        return $this->profile?->display_name ?? '';
    }
}
