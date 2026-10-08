<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\ClassMember;
use App\Models\PlaySession;
use Illuminate\Support\Collection;

/**
 * Logic dùng chung cho tính năng Giao bài.
 */
class AssignmentService
{
    /**
     * Tất cả bài được giao cho một hồ sơ: giao trực tiếp + giao theo lớp
     * mà hồ sơ là thành viên. Sắp xếp: chưa làm trước (hạn gần nhất lên đầu,
     * không hạn xếp sau), đã làm xếp cuối.
     */
    public static function assignmentsForProfile(int $profileId): Collection
    {
        $classIds = ClassMember::where('profile_id', $profileId)->pluck('classroom_id');

        return Assignment::with(['lesson.skill.topic.subject', 'creator', 'classroom'])
            ->where(function ($q) use ($profileId, $classIds) {
                $q->where('learner_profile_id', $profileId);
                if ($classIds->isNotEmpty()) {
                    $q->orWhereIn('classroom_id', $classIds);
                }
            })
            ->get()
            ->sortBy([
                fn ($a, $b) => ($a->status === 'done') <=> ($b->status === 'done'),
                fn ($a, $b) => ($a->deadline?->timestamp ?? PHP_INT_MAX) <=> ($b->deadline?->timestamp ?? PHP_INT_MAX),
                fn ($a, $b) => $b->created_at->timestamp <=> $a->created_at->timestamp,
            ])
            ->values();
    }

    /**
     * Tự động đánh dấu hoàn thành khi một lượt chơi finished:
     * - bài giao trực tiếp cho đúng hồ sơ, cùng lesson_id, đang pending → done
     * - bài giao theo lớp mà hồ sơ là thành viên, cùng lesson_id, đang pending → done
     * Gọi trong PlayController::submit sau khi session finished.
     */
    public static function markDoneForSession(PlaySession $session): int
    {
        if ($session->status !== 'finished' || ! $session->lesson_id) {
            return 0;
        }

        $classIds = ClassMember::where('profile_id', $session->profile_id)->pluck('classroom_id');

        $query = Assignment::pending()->where('lesson_id', $session->lesson_id)
            ->where(function ($q) use ($session, $classIds) {
                $q->where('learner_profile_id', $session->profile_id);
                if ($classIds->isNotEmpty()) {
                    $q->orWhereIn('classroom_id', $classIds);
                }
            });

        return $query->update([
            'status'       => 'done',
            'completed_at' => now(),
        ]);
    }
}
