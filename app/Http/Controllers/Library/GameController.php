<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Services\QuestionGrader;

/**
 * Trang "Trò chơi" VuiHoc — liệt kê 4 kiểu chơi (theo config vuihoc.game_types)
 * và các bài học chơi được của từng kiểu, lọc theo khối lớp của hồ sơ đang dùng.
 *
 * Quy ước "bài chơi được" của một kiểu chơi:
 *  - bài học status published;
 *  - chủ đề của bài đúng khối lớp hồ sơ (dùng scope forGrade của Topic;
 *    khách chưa đăng nhập tính mọi khối);
 *  - có ít nhất 1 câu hỏi questionIsPlayable của kiểu đó
 *    (QuestionGrader::questionIsPlayable — cần eager load các quan hệ con).
 */
class GameController extends Controller
{
    /** Icon + mô tả thêm cho từng kiểu chơi (tên kiểu lấy từ config, KHÔNG hardcode). */
    protected const GAME_META = [
        'quiz'     => ['icon' => '🎯', 'tagline' => 'Chọn đáp án đúng, tính giờ'],
        'matching' => ['icon' => '🔗', 'tagline' => 'Nối các cặp tương ứng'],
        'sort'     => ['icon' => '🗂️', 'tagline' => 'Kéo thả phân loại vào nhóm đúng'],
        'fill'     => ['icon' => '✏️', 'tagline' => 'Điền từ còn thiếu vào chỗ trống'],
    ];

    /**
     * GET /tro-choi — 4 thẻ kiểu chơi + số bài chơi được theo khối của hồ sơ.
     */
    public function index()
    {
        $gameTypes = config('vuihoc.game_types', []);
        $grade = active_profile()?->grade;

        $lessons = $this->playableLessonPool($grade);

        // Đếm số bài chơi được cho từng kiểu (mỗi bài chỉ tính 1 lần/kiểu).
        $counts = array_fill_keys(array_keys($gameTypes), 0);
        foreach ($lessons as $lesson) {
            foreach ($this->playableTypes($lesson) as $type) {
                $counts[$type]++;
            }
        }

        $games = [];
        foreach ($gameTypes as $type => $name) {
            $meta = self::GAME_META[$type] ?? ['icon' => '🎮', 'tagline' => ''];
            $games[] = [
                'type' => $type,
                'name' => $name,
                'icon' => $meta['icon'],
                'tagline' => $meta['tagline'],
                'playable_count' => $counts[$type] ?? 0,
            ];
        }

        return view('games.index', compact('games', 'grade'));
    }

    /**
     * GET /tro-choi/{game_type} — danh sách bài chơi được của 1 kiểu chơi.
     */
    public function show(string $gameType)
    {
        $gameTypes = config('vuihoc.game_types', []);
        if (! isset($gameTypes[$gameType])) {
            abort(404);
        }

        $grade = active_profile()?->grade;
        $difficulties = config('vuihoc.difficulties', []);

        $lessons = $this->playableLessonPool($grade)
            ->filter(function ($lesson) use ($gameType) {
                $lesson->playable_question_count = $this->countPlayableQuestions($lesson, $gameType);
                return $lesson->playable_question_count > 0;
            })
            ->values();

        return view('games.show', [
            'gameType' => $gameType,
            'gameName' => $gameTypes[$gameType],
            'gameIcon' => (self::GAME_META[$gameType] ?? ['icon' => '🎮'])['icon'],
            'gameTagline' => (self::GAME_META[$gameType] ?? ['tagline' => ''])['tagline'],
            'lessons' => $lessons,
            'grade' => $grade,
            'difficulties' => $difficulties,
        ]);
    }

    /**
     * Tập bài học published đúng khối lớp (khách: mọi khối), đã eager load
     * câu hỏi + dữ liệu con cần cho QuestionGrader::questionIsPlayable()
     * và quan hệ skill.topic.subject để hiển thị.
     */
    protected function playableLessonPool(?int $grade)
    {
        return Lesson::published()
            ->when($grade, fn ($q) => $q->whereHas('skill.topic', fn ($t) => $t->forGrade($grade)))
            ->when($grade, fn ($q) => $q->where(fn ($w) => $w->where('grade', $grade)->orWhereNull('grade')))
            ->with([
                'questions.options',
                'questions.pairs',
                'questions.sortItems',
                'questions.fillAnswers',
                'skill.topic.subject',
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    /** Các kiểu chơi có ≥1 câu chơi được trong bài (dùng để đếm số bài ở trang index). */
    protected function playableTypes(Lesson $lesson): array
    {
        $types = [];
        foreach ($lesson->questions as $question) {
            if (QuestionGrader::questionIsPlayable($question)) {
                $types[$question->game_type] = true;
            }
        }

        return array_keys($types);
    }

    /** Số câu chơi được của 1 kiểu cụ thể trong bài. */
    protected function countPlayableQuestions(Lesson $lesson, string $gameType): int
    {
        return $lesson->questions
            ->where('game_type', $gameType)
            ->filter(fn ($q) => QuestionGrader::questionIsPlayable($q))
            ->count();
    }
}
