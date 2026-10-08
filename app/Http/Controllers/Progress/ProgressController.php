<?php

namespace App\Http\Controllers\Progress;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Services\AssignmentService;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProgressController extends Controller
{
    /**
     * Trang tiến độ /tien-do: thẻ số liệu, biểu đồ 7 ngày, lịch sử chơi,
     * huy hiệu đã/chưa đạt, gợi ý độ khó cho skill vừa chơi gần nhất.
     * Cần auth + active_profile.
     */
    public function index(): View|RedirectResponse
    {
        $profile = active_profile();

        if (! $profile) {
            return redirect()->route('home')->with('error', 'Vui lòng đăng nhập và chọn hồ sơ học viên trước khi xem tiến độ.');
        }

        // --- Thẻ số liệu -------------------------------------------------
        $finishedQuery = $profile->playSessions()->where('status', 'finished');
        $plays = (clone $finishedQuery)->count();
        $avgAccuracy = (clone $finishedQuery)->avg('accuracy');
        $avgAccuracy = $avgAccuracy !== null ? round((float) $avgAccuracy, 1) : 0;

        $totalXp = (int) $profile->total_xp;
        $level = (int) $profile->level;
        $levels = config('vuihoc.levels', [1 => 0]);

        $currentThreshold = $levels[$level] ?? 0;
        $nextLevel = $level + 1;
        $nextThreshold = $levels[$nextLevel] ?? null;
        $levelProgress = 100;
        if ($nextThreshold !== null && $nextThreshold > $currentThreshold) {
            $levelProgress = min(100, max(0,
                round(($totalXp - $currentThreshold) / ($nextThreshold - $currentThreshold) * 100, 1)
            ));
        }

        // --- Biểu đồ hoạt động 7 ngày (số lượt chơi mỗi ngày) -------------
        $days = [];
        $tz = 'Asia/Ho_Chi_Minh';
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now($tz)->subDays($i);
            $days[] = [
                'date'  => $date->toDateString(),
                'label' => $i === 0 ? 'Hôm nay' : $date->format('d/m'),
                'dow'   => $date->translatedFormat('D'),
                'count' => 0,
            ];
        }
        $from = $days[0]['date'];

        $counts = $profile->playSessions()
            ->where('status', 'finished')
            ->whereDate('finished_at', '>=', $from)
            ->selectRaw('DATE(finished_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        foreach ($days as &$day) {
            $day['count'] = (int) ($counts[$day['date']] ?? 0);
        }
        unset($day);
        $maxCount = max(1, max(array_column($days, 'count')));

        // --- Lịch sử chơi -------------------------------------------------
        $history = $profile->playSessions()
            ->where('status', 'finished')
            ->with('lesson')
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        // --- Huy hiệu ------------------------------------------------------
        $allBadges = Badge::orderBy('id')->get();
        $earnedIds = $profile->badges()->pluck('badges.id')->all();

        // --- Gợi ý độ khó cho skill vừa chơi gần nhất ----------------------
        $suggestion = null;
        $lastSession = $profile->playSessions()
            ->where('status', 'finished')
            ->with('lesson.skill')
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->first();

        if ($lastSession && $lastSession->lesson && $lastSession->lesson->skill) {
            $skill = $lastSession->lesson->skill;
            $next = GamificationService::suggestNextDifficulty($profile, $skill);
            $suggestion = [
                'skill'       => $skill,
                'lesson'      => $lastSession->lesson,
                'current'     => $lastSession->lesson->difficulty,
                'next'        => $next,
                'difficulties' => config('vuihoc.difficulties', []),
            ];
        }

        return view('progress.index', [
            'profile'        => $profile,
            'plays'          => $plays,
            'avgAccuracy'    => $avgAccuracy,
            'level'          => $level,
            'totalXp'        => $totalXp,
            'nextLevel'      => $nextLevel,
            'nextThreshold'  => $nextThreshold,
            'levelProgress'  => $levelProgress,
            'days'           => $days,
            'maxCount'       => $maxCount,
            'history'        => $history,
            'allBadges'      => $allBadges,
            'earnedIds'      => $earnedIds,
            'suggestion'     => $suggestion,
            'gameTypes'      => config('vuihoc.game_types', []),
            'assignments'    => AssignmentService::assignmentsForProfile($profile->id),
        ]);
    }
}
