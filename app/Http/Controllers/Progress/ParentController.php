<?php

namespace App\Http\Controllers\Progress;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\LearnerProfile;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentController extends Controller
{
    /**
     * Khu vực phụ huynh /phu-huynh (middleware role:parent):
     * liệt kê hồ sơ các con, chọn 1 con (?profile= hoặc active_profile)
     * để xem tóm tắt tiến độ: XP, cấp độ, streak, accuracy, lịch sử gần đây,
     * điểm mạnh = skill có accuracy cao nhất.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        // Admin xem được hồ sơ của TẤT CẢ học viên; phụ huynh chỉ xem con của mình.
        $children = is_admin()
            ? LearnerProfile::orderBy('display_name')->get()
            : $user->childProfiles()->orderBy('display_name')->get();

        $selected = null;
        $requestedId = $request->query('profile');

        if ($requestedId) {
            $selected = $children->firstWhere('id', (int) $requestedId);
        }

        if (! $selected) {
            $active = active_profile();
            if ($active && $children->contains('id', $active->id)) {
                $selected = $active;
            }
        }

        if (! $selected) {
            $selected = $children->first();
        }

        $summary = null;

        if ($selected instanceof LearnerProfile) {
            $finished = $selected->playSessions()->where('status', 'finished');
            $plays = (clone $finished)->count();
            $avgAccuracy = (clone $finished)->avg('accuracy');
            $avgAccuracy = $avgAccuracy !== null ? round((float) $avgAccuracy, 1) : 0;

            $history = (clone $finished)
                ->with('lesson')
                ->orderByDesc('finished_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get();

            // Điểm mạnh: skill có accuracy trung bình cao nhất (tối thiểu 1 lượt).
            $strongest = null;
            $skillStats = $selected->playSessions()
                ->where('play_sessions.status', 'finished')
                ->join('lessons', 'lessons.id', '=', 'play_sessions.lesson_id')
                ->join('skills', 'skills.id', '=', 'lessons.skill_id')
                ->selectRaw('skills.id as skill_id, skills.name as skill_name, AVG(play_sessions.accuracy) as avg_acc, COUNT(*) as plays')
                ->groupBy('skills.id', 'skills.name')
                ->orderByDesc('avg_acc')
                ->first();

            if ($skillStats) {
                $strongest = [
                    'name'        => $skillStats->skill_name,
                    'avgAccuracy' => round((float) $skillStats->avg_acc, 1),
                    'plays'       => (int) $skillStats->plays,
                ];
            }

            // Cần luyện thêm: 3 kỹ năng có độ chính xác thấp nhất (tối thiểu 1 lượt).
            $weakest = $selected->playSessions()
                ->where('play_sessions.status', 'finished')
                ->join('lessons', 'lessons.id', '=', 'play_sessions.lesson_id')
                ->join('skills', 'skills.id', '=', 'lessons.skill_id')
                ->selectRaw('skills.id as skill_id, skills.name as skill_name, AVG(play_sessions.accuracy) as avg_acc, COUNT(*) as plays')
                ->groupBy('skills.id', 'skills.name')
                ->orderBy('avg_acc')
                ->limit(3)
                ->get()
                ->map(fn ($r) => [
                    'skill_id'    => (int) $r->skill_id,
                    'name'        => $r->skill_name,
                    'avgAccuracy' => round((float) $r->avg_acc, 1),
                    'plays'       => (int) $r->plays,
                ])
                ->values();

            // Bài đã giao cho con (trực tiếp + theo lớp).
            $assignments = AssignmentService::assignmentsForProfile($selected->id);

            $summary = [
                'profile'      => $selected,
                'plays'        => $plays,
                'avgAccuracy'  => $avgAccuracy,
                'history'      => $history,
                'strongest'    => $strongest,
                'weakest'      => $weakest,
                'assignments'  => $assignments,
                'gameTypes'    => config('vuihoc.game_types', []),
            ];
        }

        return view('parent.index', [
            'children' => $children,
            'selected' => $selected,
            'summary'  => $summary,
        ]);
    }
}
