<?php

namespace App\Services;

use App\Models\PlaySession;
use App\Models\Question;

/**
 * Logic chấm điểm câu hỏi server-side — TÁCH từ PlayController (phase 2, agent Gameplay)
 * để dùng chung cho cả web (PlayController) và API v1 (agent API, phase 3).
 * Behavior giữ nguyên 100% so với bản gốc trong PlayController.
 *
 * Cấu trúc `answers` (giống quy ước của PlayController):
 *   - quiz:     answers[<question_id>] = <option_id>
 *   - matching: answers[<question_id>][<left_pair_id>] = <right_pair_id>
 *   - sort:     answers[<question_id>][<item_id>] = "<category>"
 *   - fill:     answers[<question_id>][<blank_index>] = "<text>"
 */
class QuestionGrader
{
    /** Câu hỏi chỉ "chơi được" khi có đủ dữ liệu con tương ứng. */
    public static function questionIsPlayable(Question $q): bool
    {
        return match ($q->game_type) {
            'quiz'     => $q->options->count() >= 2,
            'matching' => $q->pairs->count() >= 2,
            'sort'     => $q->sortItems->count() >= 2,
            'fill'     => $q->fillAnswers->count() >= 1 && str_contains((string) $q->prompt, '___'),
            default    => false,
        };
    }

    /**
     * Dữ liệu câu hỏi đã "làm sạch" để trả về client — TUYỆT ĐỐI không chứa đáp án:
     * quiz: chỉ id + text của options (đã xáo trộn), không có is_correct;
     * matching: 2 cột xáo trộn độc lập, không có quan hệ left→right;
     * sort: items xáo trộn KHÔNG kèm category; category chỉ hiện tên nhóm;
     * fill: chỉ prompt chứa ___, không có fill_answers.
     */
    public static function buildShowPayload(PlaySession $session): array
    {
        $ids = json_decode((string) $session->question_ids_json, true) ?: [];

        $questions = Question::whereIn('id', $ids)
            ->with(['options', 'pairs', 'sortItems', 'fillAnswers'])
            ->get()
            ->sortBy(fn (Question $q) => array_search($q->id, $ids))
            ->values();

        return $questions->map(function (Question $q) {
            $base = ['id' => $q->id, 'prompt' => $q->prompt, 'points' => (int) $q->points];

            switch ($q->game_type) {
                case 'quiz':
                    $base['options'] = $q->options->shuffle()->map(fn ($o) => [
                        'id' => $o->id, 'text' => $o->option_text,
                    ])->values()->all();
                    break;

                case 'matching':
                    $base['left'] = $q->pairs->sortBy('sort_order')->map(fn ($p) => [
                        'id' => $p->id, 'text' => $p->left_text,
                    ])->values()->all();
                    $base['right'] = $q->pairs->shuffle()->map(fn ($p) => [
                        'id' => $p->id, 'text' => $p->right_text,
                    ])->values()->all();
                    break;

                case 'sort':
                    $base['items'] = $q->sortItems->shuffle()->map(fn ($i) => [
                        'id' => $i->id, 'text' => $i->item_text,
                    ])->values()->all();
                    // Chỉ tên các nhóm (để gán), KHÔNG gắn nhóm đúng vào từng item.
                    $base['categories'] = $q->sortItems->pluck('category')->unique()->values()->all();
                    break;

                case 'fill':
                    $base['segments'] = self::splitPrompt((string) $q->prompt);
                    break;
            }

            return $base;
        })->all();
    }

    /** Tách prompt thành các đoạn text / ô trống theo dấu ___. */
    public static function splitPrompt(string $prompt): array
    {
        $parts = explode('___', $prompt);
        $segments = [];
        $last = count($parts) - 1;

        foreach ($parts as $i => $part) {
            if ($part !== '') {
                $segments[] = ['t' => 'text', 'v' => $part];
            }
            if ($i < $last) {
                $segments[] = ['t' => 'blank', 'i' => $i];
            }
        }

        return $segments;
    }

    /** Kết quả "trống" cho trường hợp gian lận: mọi câu 0 điểm. */
    public static function blankGrade(Question $q): array
    {
        $graded = self::gradeQuestion($q, null);
        $graded['score'] = 0;
        $graded['review']['correct'] = false;
        $graded['review']['correct_count'] = 0;
        $graded['review']['correct_units'] = 0;
        $graded['review']['score'] = 0;
        foreach ($graded['review']['items'] as &$item) {
            $item['is_correct'] = false;
        }

        return $graded;
    }

    /**
     * Chấm 1 câu hỏi server-side.
     * @return ['score'=>int, 'review'=>array]
     */
    public static function gradeQuestion(Question $q, mixed $userAns): array
    {
        $review = [
            'question_id' => $q->id,
            'id' => $q->id,
            'type' => $q->game_type,
            'prompt' => $q->prompt,
            'points' => (int) $q->points,
            'explanation' => $q->explanation,
            'user_answer' => null,
            'correct_answer' => null,
            'items' => [],
            // Các key phục vụ GamificationService::recordPlay (quy ước của agent Progress):
            // 'correct_units' = số đơn vị đúng, 'total_units' = tổng đơn vị, 'seconds' điền ở submit().
            'correct_units' => 0,
            'total_units' => 0,
            'seconds' => 0,
        ];

        $result = match ($q->game_type) {
            'quiz' => self::gradeQuiz($q, $userAns, $review),
            'matching' => self::gradeMatching($q, $userAns, $review),
            'sort' => self::gradeSort($q, $userAns, $review),
            'fill' => self::gradeFill($q, $userAns, $review),
            default => ['correct_count' => 0, 'total_count' => 0],
        };

        $total = max(1, $result['total_count']);
        $correct = $result['correct_count'];
        // Điểm từng phần theo tỉ lệ đúng (quiz: đúng hết hoặc 0).
        $score = (int) round($q->points * $correct / $total);

        $review['correct'] = $correct === $result['total_count'] && $result['total_count'] > 0;
        $review['correct_units'] = $correct;
        $review['total_units'] = $result['total_count'];
        $review['correct_count'] = $correct;
        $review['total_count'] = $result['total_count'];
        $review['score'] = $score;
        $review['max_score'] = (int) $q->points;

        return ['score' => $score, 'review' => $review];
    }

    private static function gradeQuiz(Question $q, mixed $userAns, array &$review): array
    {
        $options = $q->options->keyBy('id');
        $correctOption = $q->options->firstWhere('is_correct', true);

        $chosen = is_scalar($userAns) ? $options->get((int) $userAns) : null;
        $isCorrect = $chosen && $chosen->question_id == $q->id && (bool) $chosen->is_correct;

        $review['user_answer'] = $chosen ? $chosen->option_text : '— (bỏ trống)';
        $review['correct_answer'] = $correctOption ? $correctOption->option_text : '—';

        return ['correct_count' => $isCorrect ? 1 : 0, 'total_count' => 1];
    }

    private static function gradeMatching(Question $q, mixed $userAns, array &$review): array
    {
        $pairs = $q->pairs->keyBy('id');
        $userAns = is_array($userAns) ? $userAns : [];
        $correct = 0;

        foreach ($q->pairs as $pair) {
            $rightId = $userAns[$pair->id] ?? null;
            $rightPair = is_scalar($rightId) ? $pairs->get((int) $rightId) : null;

            // Đúng khi cặp phải được chọn thuộc cùng câu hỏi và có right_text trùng.
            $ok = $rightPair
                && $rightPair->question_id == $q->id
                && $rightPair->right_text === $pair->right_text;

            if ($ok) {
                $correct++;
            }
            $review['items'][] = [
                'label' => $pair->left_text,
                'user' => $rightPair ? $rightPair->right_text : '— (chưa ghép)',
                'correct' => $pair->right_text,
                'is_correct' => (bool) $ok,
            ];
        }

        return ['correct_count' => $correct, 'total_count' => $q->pairs->count()];
    }

    private static function gradeSort(Question $q, mixed $userAns, array &$review): array
    {
        $items = $q->sortItems->keyBy('id');
        $userAns = is_array($userAns) ? $userAns : [];
        $correct = 0;

        foreach ($q->sortItems as $item) {
            $userCat = $userAns[$item->id] ?? null;
            $ok = is_string($userCat) && $userCat === $item->category;

            if ($ok) {
                $correct++;
            }
            $review['items'][] = [
                'label' => $item->item_text,
                'user' => is_string($userCat) && $userCat !== '' ? $userCat : '— (chưa gán)',
                'correct' => $item->category,
                'is_correct' => $ok,
            ];
        }

        return ['correct_count' => $correct, 'total_count' => $q->sortItems->count()];
    }

    private static function gradeFill(Question $q, mixed $userAns, array &$review): array
    {
        $userAns = is_array($userAns) ? $userAns : [];
        $byBlank = $q->fillAnswers->groupBy('blank_index')->sortKeys();
        $correct = 0;

        foreach ($byBlank as $blankIndex => $accepted) {
            $userText = $userAns[$blankIndex] ?? '';
            $normUser = self::normalizeFill($userText);

            $ok = $normUser !== '' && $accepted->contains(
                fn ($a) => self::normalizeFill($a->answer_text) === $normUser
            );

            if ($ok) {
                $correct++;
            }
            $review['items'][] = [
                'label' => 'Chỗ trống ' . ($blankIndex + 1),
                'user' => trim((string) $userText) !== '' ? (string) $userText : '— (bỏ trống)',
                'correct' => $accepted->first()->answer_text,
                'is_correct' => $ok,
            ];
        }

        return ['correct_count' => $correct, 'total_count' => $byBlank->count()];
    }

    private static function normalizeFill(mixed $text): string
    {
        return mb_strtolower(trim((string) $text), 'UTF-8');
    }

    /**
     * Tính XP TẠM theo config vuihoc.xp (XP cuối cùng do GamificationService chốt).
     */
    public static function calcXp(string $gameType, array $review, float $accuracy, int $duration): int
    {
        $xpCfg = config('vuihoc.xp', []);
        $xp = 0;

        foreach ($review as $r) {
            $xp += match ($gameType) {
                'quiz' => $r['correct'] ? (int) ($xpCfg['quiz_correct'] ?? 10) : 0,
                'matching' => $r['correct_count'] * (int) ($xpCfg['matching_pair'] ?? 5),
                'sort' => $r['correct_count'] * (int) ($xpCfg['sort_item'] ?? 5),
                'fill' => $r['correct_count'] * (int) ($xpCfg['fill_blank'] ?? 10),
                default => 0,
            };
        }

        if ($gameType === 'quiz') {
            // Thưởng tốc độ: tối đa quiz_time_bonus_max, giảm dần về 0 khi chạm 10 phút.
            $bonusMax = (int) ($xpCfg['quiz_time_bonus_max'] ?? 5);
            $xp += (int) round($bonusMax * max(0, 1 - $duration / 600));
        }

        if ($accuracy >= 100) {
            $xp += (int) round($xp * (($xpCfg['perfect_bonus_percent'] ?? 20) / 100));
        }

        return $xp;
    }
}
