<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearnerProfile;
use App\Services\GamificationService;
use Illuminate\Http\Request;

/**
 * API v1 — tiến độ học tập của hồ sơ học viên.
 * GET /api/v1/progress → {total_xp, level, streak, accuracy, recent_plays}
 */
class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $profile = $user->role === 'learner'
            ? $user->learnerProfile
            : ($request->input('profile_id')
                ? LearnerProfile::where('id', $request->input('profile_id'))->where('parent_id', $user->id)->first()
                : null);

        if (! $profile) {
            return response()->json(['message' => 'Không tìm thấy hồ sơ học viên.'], 404);
        }

        $sessions = $profile->playSessions()
            ->where('status', 'finished')
            ->with('lesson')
            ->orderByDesc('finished_at')
            ->limit(20)
            ->get();

        $accuracy = $sessions->isNotEmpty()
            ? round($sessions->avg('accuracy'), 2)
            : 0.0;

        return response()->json([
            'profile' => [
                'id'           => $profile->id,
                'display_name' => $profile->display_name,
                'grade'        => $profile->grade,
            ],
            'total_xp'      => (int) $profile->total_xp,
            'level'         => GamificationService::levelForXp((int) $profile->total_xp),
            'streak'        => (int) $profile->current_streak,
            'longest_streak'=> (int) $profile->longest_streak,
            'accuracy'      => $accuracy,
            'finished_plays'=> $profile->playSessions()->where('status', 'finished')->count(),
            'badges_count'  => $profile->badges()->count(),
            'recent_plays'  => $sessions->take(10)->map(fn ($s) => [
                'id'         => $s->id,
                'lesson'     => $s->lesson?->title,
                'game_type'  => $s->game_type,
                'score'      => (int) $s->score,
                'max_score'  => (int) $s->max_score,
                'accuracy'   => (float) $s->accuracy,
                'xp_earned'  => (int) $s->xp_earned,
                'finished_at'=> $s->finished_at?->toIso8601String(),
            ])->values(),
        ]);
    }
}
