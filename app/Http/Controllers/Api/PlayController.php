<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearnerProfile;
use App\Models\Lesson;
use App\Models\PlaySession;
use App\Models\Question;
use App\Services\GamificationService;
use App\Services\QuestionGrader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * API v1 — luồng chơi game bằng play token.
 * Tái dùng QuestionGrader (tách từ PlayController web) và GamificationService,
 * không viết lại logic chấm điểm / tính XP.
 *
 * POST /api/v1/play/start {lesson_id, game_type} → {token, questions}
 * POST /api/v1/play/{token}/submit {answers} → {score, accuracy, xp, level_up, badges}
 */
class PlayController extends Controller
{
    /**
     * Hồ sơ học viên của user qua API:
     * - learner: profile của chính user;
     * - parent: truyền profile_id trong request, phải thuộc về parent này;
     * - role khác: không có hồ sơ → 403.
     */
    private function resolveProfile(Request $request): LearnerProfile
    {
        $user = $request->user();

        if ($user->role === 'learner') {
            $profile = $user->learnerProfile;
            if (! $profile) {
                abort(422, 'Tài khoản chưa có hồ sơ học viên.');
            }

            return $profile;
        }

        if ($user->role === 'parent') {
            $profileId = $request->input('profile_id');
            $profile = $profileId
                ? LearnerProfile::where('id', $profileId)->where('parent_id', $user->id)->first()
                : null;
            if (! $profile) {
                abort(422, 'Vui lòng chọn hồ sơ của con (profile_id).');
            }

            return $profile;
        }

        abort(403, 'Tài khoản này không có hồ sơ học viên để chơi game.');
    }

    public function start(Request $request)
    {
        $profile = $this->resolveProfile($request);

        $data = $request->validate([
            'lesson_id' => ['required'],
            'game_type' => ['required', 'string'],
        ]);

        $lesson = Lesson::where('slug', $data['lesson_id'])
            ->orWhere('id', $data['lesson_id'])
            ->first();
        if (! $lesson || $lesson->status !== 'published') {
            return response()->json(['message' => 'Bài học không tồn tại hoặc chưa được xuất bản.'], 404);
        }

        $gameTypes = config('vuihoc.game_types', []);
        if (! array_key_exists($data['game_type'], $gameTypes)) {
            return response()->json(['message' => 'Kiểu chơi không hợp lệ.'], 422);
        }

        // Chọn ngẫu nhiên tối đa 5 câu hỏi, chỉ lấy câu có đủ dữ liệu để chơi được.
        $questions = $lesson->questions()
            ->where('game_type', $data['game_type'])
            ->with(['options', 'pairs', 'sortItems', 'fillAnswers'])
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->filter(fn (Question $q) => QuestionGrader::questionIsPlayable($q))
            ->values();

        if ($questions->isEmpty()) {
            return response()->json([
                'message' => 'Bài học chưa có câu hỏi cho kiểu chơi "' . ($gameTypes[$data['game_type']] ?? $data['game_type']) . '".',
            ], 422);
        }

        do {
            $token = Str::random(40);
        } while (PlaySession::where('token', $token)->exists());

        $session = PlaySession::create([
            'profile_id'        => $profile->id,
            'lesson_id'         => $lesson->id,
            'game_type'         => $data['game_type'],
            'token'             => $token,
            'question_ids_json' => $questions->pluck('id')->toJson(),
            'started_at'        => now(),
            'expires_at'        => now()->addMinutes(30),
            'status'            => 'started',
            'max_score'         => $questions->sum('points'),
        ]);

        return response()->json([
            'token'          => $session->token,
            'expires_at'     => $session->expires_at->toIso8601String(),
            'lesson'         => ['id' => $lesson->id, 'title' => $lesson->title, 'slug' => $lesson->slug],
            'game_type'      => $session->game_type,
            'game_type_name' => $gameTypes[$session->game_type] ?? $session->game_type,
            'question_count' => $questions->count(),
            // Câu hỏi đã lọc đáp án — tái dùng logic của web.
            'questions'      => QuestionGrader::buildShowPayload($session),
        ], 201);
    }

    public function submit(Request $request, string $token)
    {
        $profile = $this->resolveProfile($request);

        $session = PlaySession::where('token', $token)->first();
        if (! $session) {
            return response()->json(['message' => 'Không tìm thấy phiên chơi.'], 404);
        }
        if ((int) $session->profile_id !== (int) $profile->id) {
            return response()->json(['message' => 'Bạn không có quyền nộp bài cho phiên chơi này.'], 403);
        }
        if ($session->status === 'finished') {
            return response()->json(['message' => 'Phiên chơi đã được nộp bài trước đó.'], 409);
        }
        if ($session->status !== 'started' || ! $session->isPlayable()) {
            return response()->json(['message' => 'Phiên chơi không còn hợp lệ (đã hết hạn).'], 410);
        }

        $answers = $request->input('answers', []);
        if (! is_array($answers)) {
            $answers = [];
        }

        $finishedAt = now();
        $duration = (int) $session->started_at->diffInSeconds($finishedAt, true);

        // Chống gian lận: làm bài dưới 5 giây → 0 điểm, không cộng XP.
        $cheated = $duration < 5;

        $ids = json_decode((string) $session->question_ids_json, true) ?: [];
        $questions = Question::whereIn('id', $ids)
            ->with(['options', 'pairs', 'sortItems', 'fillAnswers'])
            ->get()
            ->keyBy('id');

        $review = [];
        $score = 0;
        foreach ($ids as $qid) {
            $q = $questions->get($qid);
            if (! $q) {
                continue;
            }
            $graded = $cheated
                ? QuestionGrader::blankGrade($q)
                : QuestionGrader::gradeQuestion($q, $answers[$qid] ?? $answers[(string) $qid] ?? null);
            $score += $graded['score'];
            $review[] = $graded['review'];
        }

        $maxScore = (int) $session->max_score;
        $accuracy = $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0.0;
        $xpTemp = $cheated ? 0 : QuestionGrader::calcXp($session->game_type, $review, $accuracy, $duration);

        $perQuestionSeconds = max(1, (int) round($duration / max(1, count($review))));
        foreach ($review as &$r) {
            $r['seconds'] = $perQuestionSeconds;
        }
        unset($r);

        $session->status = 'finished';
        $session->finished_at = $finishedAt;
        $session->score = $score;
        $session->accuracy = $accuracy;
        $session->duration_seconds = $duration;
        $session->xp_earned = $xpTemp;
        $session->answers_json = json_encode([
            'cheated' => $cheated,
            'gamification' => null,
            'details' => $review,
        ], JSON_UNESCAPED_UNICODE);
        $session->save();

        // XP/cấp độ/streak/huy hiệu cuối cùng do GamificationService chốt.
        $gamification = $cheated ? null : $this->callGamification($profile, $session);
        $meta = json_decode((string) $session->answers_json, true) ?: [];
        $meta['gamification'] = $gamification;
        $session->answers_json = json_encode($meta, JSON_UNESCAPED_UNICODE);
        $session->save();

        return response()->json([
            'score'      => $score,
            'max_score'  => $maxScore,
            'accuracy'   => $accuracy,
            'cheated'    => $cheated,
            'xp'         => (int) ($gamification['xp'] ?? $xpTemp),
            'level_up'   => (bool) ($gamification['level_up'] ?? false),
            'new_level'  => $gamification['new_level'] ?? null,
            'streak'     => $gamification['streak'] ?? null,
            'badges'     => $gamification['new_badges'] ?? [],
            'review'     => $review,
        ]);
    }

    private function callGamification(LearnerProfile $profile, PlaySession $session): ?array
    {
        try {
            $res = GamificationService::recordPlay($profile, $session->fresh());
        } catch (\Throwable $e) {
            Log::warning('GamificationService::recordPlay (API) thất bại: ' . $e->getMessage());

            return null;
        }

        $session->xp_earned = (int) ($res['xp'] ?? $session->xp_earned);
        $session->save();

        return [
            'xp'        => (int) ($res['xp'] ?? 0),
            'level_up'  => (bool) ($res['level_up'] ?? false),
            'new_level' => $res['new_level'] ?? null,
            'streak'    => $res['streak'] ?? null,
            'new_badges' => collect($res['new_badges'] ?? [])->map(fn ($b) => [
                'name' => $b->name ?? '',
                'icon' => $b->icon ?? '🏅',
                'description' => $b->description ?? '',
            ])->values()->all(),
        ];
    }
}
