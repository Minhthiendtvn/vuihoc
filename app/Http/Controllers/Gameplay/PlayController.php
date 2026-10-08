<?php

namespace App\Http\Controllers\Gameplay;

use App\Http\Controllers\Controller;
use App\Models\LearnerProfile;
use App\Models\Lesson;
use App\Models\PlaySession;
use App\Models\Question;
use App\Services\QuestionGrader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Luồng chơi game bằng play token (xem CONTRACT.md mục (d)).
 *
 * Cấu trúc `answers` gửi lên ở gameplay.submit (form POST):
 *   - quiz:     answers[<question_id>] = <option_id>            (radio, 1 đáp án)
 *   - matching: answers[<question_id>][<left_pair_id>] = <right_pair_id>
 *   - sort:     answers[<question_id>][<item_id>] = "<category>"
 *   - fill:     answers[<question_id>][<blank_index>] = "<text>"
 *
 * Chấm điểm HOÀN TOÀN server-side từ DB, không tin bất kỳ dữ liệu nào client gửi
 * ngoài lựa chọn của người chơi.
 */
class PlayController extends Controller
{
    /**
     * Hồ sơ học viên đang hoạt động, hoặc RedirectResponse về nơi phù hợp
     * với từng vai trò (kèm thông báo giải thích, không đá về trang chủ
     * một cách khó hiểu).
     */
    private function activeProfileOrRedirect(): LearnerProfile|RedirectResponse
    {
        $profile = active_profile();

        if ($profile) {
            return $profile;
        }

        $user = auth()->user();

        // Chưa đăng nhập → về trang đăng nhập.
        if (! $user) {
            return redirect()->route('auth.login');
        }

        // Phụ huynh chưa chọn hồ sơ con đang chơi → về trang quản lý con để chọn.
        if ($user->role === 'parent') {
            return redirect()->route('identity.children.index')
                ->with('info', 'Vui lòng chọn hồ sơ của con đang chơi trước nhé.');
        }

        // Học viên chưa có hồ sơ → tạo hồ sơ trước.
        if ($user->role === 'learner') {
            return redirect()->route('profile.create')
                ->with('info', 'Hãy tạo hồ sơ học tập của bạn trước khi chơi nhé.');
        }

        // Giáo viên / quản trị không có hồ sơ chơi trực tiếp.
        return redirect()->route('home')
            ->with('info', 'Tài khoản giáo viên/quản trị không chơi trực tiếp. Hãy dùng tài khoản học viên để chơi.');
    }

    private function findSession(string $token): PlaySession
    {
        $session = PlaySession::where('token', $token)->first();

        if (! $session) {
            abort(404, 'Không tìm thấy phiên chơi.');
        }

        return $session;
    }

    private function checkOwner(LearnerProfile $profile, PlaySession $session): void
    {
        if ((int) $session->profile_id !== (int) $profile->id) {
            abort(403, 'Bạn không có quyền xem phiên chơi này.');
        }
    }

    // ------------------------------------------------------------------
    // 1. START — POST /bai-hoc/{lesson}/choi/{game_type}
    // ------------------------------------------------------------------
    public function start(Request $request, string $lesson, string $gameType)
    {
        $profile = $this->activeProfileOrRedirect();
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $lessonModel = Lesson::where('slug', $lesson)->orWhere('id', $lesson)->first();
        if (! $lessonModel || $lessonModel->status !== 'published') {
            return back()->with('error', 'Bài học không tồn tại hoặc chưa được xuất bản.');
        }

        $gameTypes = config('vuihoc.game_types', []);
        if (! array_key_exists($gameType, $gameTypes)) {
            return back()->with('error', 'Kiểu chơi không hợp lệ.');
        }

        // Chọn ngẫu nhiên tối đa 5 câu hỏi, chỉ lấy câu có đủ dữ liệu để chơi được.
        $questions = $lessonModel->questions()
            ->where('game_type', $gameType)
            ->with(['options', 'pairs', 'sortItems', 'fillAnswers'])
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->filter(fn (Question $q) => QuestionGrader::questionIsPlayable($q))
            ->values();

        if ($questions->isEmpty()) {
            return back()->with(
                'error',
                'Bài học chưa có câu hỏi cho kiểu chơi "' . ($gameTypes[$gameType] ?? $gameType) . '".'
            );
        }

        do {
            $token = Str::random(40);
        } while (PlaySession::where('token', $token)->exists());

        $session = PlaySession::create([
            'profile_id'        => $profile->id,
            'lesson_id'         => $lessonModel->id,
            'game_type'         => $gameType,
            'token'             => $token,
            'question_ids_json' => $questions->pluck('id')->toJson(),
            'started_at'        => now(),
            'expires_at'        => now()->addMinutes(30),
            'status'            => 'started',
            'max_score'         => $questions->sum('points'),
        ]);

        return redirect()->route('gameplay.show', ['token' => $session->token]);
    }

    // ------------------------------------------------------------------
    // 2. SHOW — GET /choi/{token}
    // ------------------------------------------------------------------
    public function show(Request $request, string $token)
    {
        $profile = $this->activeProfileOrRedirect();
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $session = $this->findSession($token);
        $this->checkOwner($profile, $session);

        if ($session->status === 'finished') {
            return redirect()->route('gameplay.result', ['token' => $token]);
        }

        if (! $session->isPlayable()) {
            if ($session->status === 'started') {
                $session->status = 'expired';
                $session->save();
            }
            abort(410, 'Phiên chơi đã hết hạn. Hãy bắt đầu một lượt chơi mới nhé!');
        }

        $session->loadMissing('lesson.skill');

        // Đồng hồ chơi: 10 phút, nhưng không vượt quá expires_at của session.
        // Carbon 3: diffInSeconds() mặc định trả về SỐ CÓ DẤU → truyền true để lấy trị tuyệt đối.
        $remaining = (int) min(600, $session->expires_at->diffInSeconds(now(), true));
        $remaining = max(1, $remaining);

        return view('gameplay.show-' . $session->game_type, [
            'session'           => $session,
            'lesson'            => $session->lesson,
            'skillName'         => $session->lesson->skill->name ?? '',
            'gameName'          => config('vuihoc.game_types.' . $session->game_type, $session->game_type),
            'questions'         => QuestionGrader::buildShowPayload($session),
            'remainingSeconds'  => $remaining,
        ]);
    }

    // ------------------------------------------------------------------
    // 3. SUBMIT — POST /choi/{token}/nop-bai
    // ------------------------------------------------------------------
    public function submit(Request $request, string $token)
    {
        $profile = $this->activeProfileOrRedirect();
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $session = $this->findSession($token);
        $this->checkOwner($profile, $session);

        // Nộp trùng (bấm 2 lần): về trang kết quả, không chấm lại.
        if ($session->status === 'finished') {
            return redirect()->route('gameplay.result', ['token' => $token]);
        }
        if ($session->status !== 'started' || ! $session->isPlayable()) {
            abort(403, 'Phiên chơi không còn hợp lệ.');
        }

        $answers = $request->input('answers', []);
        if (! is_array($answers)) {
            $answers = [];
        }

        $finishedAt = now();
        // Carbon 3: diffInSeconds() mặc định trả về số có dấu → dùng trị tuyệt đối.
        $duration = (int) $session->started_at->diffInSeconds($finishedAt, true);

        // Chống gian lận: làm bài dưới 5 giây → 0 điểm.
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
                : QuestionGrader::gradeQuestion($q, $answers[$qid] ?? null);
            $score += $graded['score'];
            $review[] = $graded['review'];
        }

        $maxScore = (int) $session->max_score;
        $accuracy = $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0.0;
        $xpTemp = $cheated ? 0 : QuestionGrader::calcXp($session->game_type, $review, $accuracy, $duration);

        // Thời gian ước tính cho mỗi câu — phục vụ thưởng tốc độ của GamificationService.
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
        // answers_json dùng key 'details' theo quy ước của agent Progress
        // (GamificationService::recordPlay đọc từ đây).
        $session->answers_json = json_encode([
            'cheated' => $cheated,
            'gamification' => null, // điền sau khi gọi GamificationService
            'details' => $review,
        ], JSON_UNESCAPED_UNICODE);
        $session->save();

        // XP/cấp độ/streak/huy hiệu CUỐI CÙNG do GamificationService (agent Progress) chốt.
        // Lượt chơi gian lận (<5s) KHÔNG gọi recordPlay: không cộng XP, không tính streak,
        // không xét huy hiệu — đúng tinh thần "gian lận → 0 điểm".
        // Service chưa tồn tại → bọc try/catch, lượt chơi vẫn được ghi nhận bình thường.
        $gamification = $cheated ? null : $this->callGamification($profile, $session);
        $meta = json_decode((string) $session->answers_json, true) ?: [];
        $meta['gamification'] = $gamification;
        $session->answers_json = json_encode($meta, JSON_UNESCAPED_UNICODE);
        $session->save();

        // Tự động đánh dấu các bài được giao (cùng bài học) là đã làm.
        // Bọc try/catch để không ảnh hưởng luồng nộp bài nếu có lỗi.
        try {
            \App\Services\AssignmentService::markDoneForSession($session);
        } catch (\Throwable $e) {
            Log::warning('Assignment auto-done failed: ' . $e->getMessage());
        }

        return redirect()->route('gameplay.result', ['token' => $token]);
    }

    /**
     * Gọi GamificationService::recordPlay theo CONTRACT (e).
     * Service do agent Progress viết — nếu chưa tồn tại hoặc lỗi, trả về null
     * và lượt chơi vẫn được ghi nhận (xp_earned giữ giá trị tạm tính).
     */
    private function callGamification(LearnerProfile $profile, PlaySession $session): ?array
    {
        if (! class_exists(\App\Services\GamificationService::class)) {
            return null;
        }

        try {
            $res = \App\Services\GamificationService::recordPlay($profile, $session->fresh());
        } catch (\Throwable $e) {
            Log::warning('GamificationService::recordPlay thất bại: ' . $e->getMessage());
            return null;
        }

        // XP cuối cùng do service chốt → cập nhật lên session.
        $session->xp_earned = (int) ($res['xp'] ?? $session->xp_earned);
        $session->save();

        return [
            'xp' => (int) ($res['xp'] ?? 0),
            'level_up' => (bool) ($res['level_up'] ?? false),
            'new_level' => $res['new_level'] ?? null,
            'streak' => $res['streak'] ?? null,
            'new_badges' => collect($res['new_badges'] ?? [])->map(fn ($b) => [
                'name' => $b->name ?? '',
                'icon' => $b->icon ?? '🏅',
                'description' => $b->description ?? '',
            ])->values()->all(),
        ];
    }

    // ------------------------------------------------------------------
    // 4. RESULT — GET /choi/{token}/ket-qua
    // ------------------------------------------------------------------
    public function result(Request $request, string $token)
    {
        $profile = $this->activeProfileOrRedirect();
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $session = $this->findSession($token);
        $this->checkOwner($profile, $session);

        if ($session->status !== 'finished') {
            if ($session->isPlayable()) {
                return redirect()->route('gameplay.show', ['token' => $token]);
            }
            abort(404, 'Chưa có kết quả cho phiên chơi này.');
        }

        $session->loadMissing('lesson.skill');
        $lesson = $session->lesson;
        $data = json_decode((string) $session->answers_json, true) ?: [];

        // Gợi ý "bài tiếp theo": bài published tiếp theo cùng kỹ năng.
        $nextLesson = null;
        if ($lesson) {
            $nextLesson = Lesson::published()
                ->where('skill_id', $lesson->skill_id)
                ->where('id', '!=', $lesson->id)
                ->where('sort_order', '>', $lesson->sort_order)
                ->orderBy('sort_order')
                ->first()
                ?? Lesson::published()
                    ->where('skill_id', $lesson->skill_id)
                    ->where('id', '!=', $lesson->id)
                    ->orderBy('sort_order')
                    ->first();
        }

        return view('gameplay.result', [
            'session' => $session,
            'lesson' => $lesson,
            'skillName' => $lesson?->skill?->name ?? '',
            'gameName' => config('vuihoc.game_types.' . $session->game_type, $session->game_type),
            'review' => $data['details'] ?? $data['questions'] ?? [],
            'cheated' => (bool) ($data['cheated'] ?? false),
            'gamification' => $data['gamification'] ?? null,
            'nextLesson' => $nextLesson,
        ]);
    }
}
