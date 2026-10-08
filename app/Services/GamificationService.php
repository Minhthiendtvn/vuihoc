<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\LearnerProfile;
use App\Models\PlaySession;
use App\Models\Skill;
use App\Models\XpEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dịch vụ gamification của VuiHoc (agent Progress, phase 2).
 *
 * QUY ƯỚC DỮ LIỆU VỚI AGENT GAMEPLAY (trường answers_json của play_sessions):
 * - answers_json nên là mảng các phần tử, mỗi phần tử mô tả một câu hỏi:
 *   [{"question_id":1,"correct":true,"correct_units":2,"total_units":3,"seconds":4}, ...]
 *   + "correct_units": số đơn vị đúng trong câu hỏi (ví dụ: số cặp matching đúng,
 *     số blank fill đúng; mặc định = correct ? 1 : 0).
 *   + "total_units": tổng số đơn vị trong câu hỏi (mặc định 1).
 *   + "seconds": thời gian làm câu hỏi đó (giây) — dùng cho thưởng tốc độ quiz.
 * - Nếu answers_json trống/không đọc được, service suy ra số đơn vị đúng từ
 *   score / (XP mỗi đơn vị) và số câu hỏi từ question_ids_json (fallback).
 *
 * Quy tắc thưởng tốc độ quiz (theo câu): trả lời trung bình <= 5 giây/câu
 * được tối đa xp.quiz_time_bonus_max (5) mỗi câu; >= 30 giây/câu được 0;
 * ở giữa nội suy tuyến tính rồi làm tròn tổng.
 *
 * Streak tính theo ngày Asia/Ho_Chi_Minh: hôm nay != last_play_date thì
 * streak tăng (hôm qua) hoặc reset về 1 (đứt chuỗi); cộng xp.streak_day (2)
 * mỗi ngày mới có lượt chơi; chỉ cộng 1 lần/ngày dù chơi nhiều lượt.
 */
class GamificationService
{
    /** 3 method public theo CONTRACT.md mục (e) — agent Gameplay gọi các method này. */

    /**
     * Cấp độ tương ứng với tổng XP (theo config 'vuihoc.levels').
     */
    public static function levelForXp(int $xp): int
    {
        $levels = config('vuihoc.levels', [1 => 0]);

        $level = 1;
        foreach ($levels as $lvl => $threshold) {
            if ($xp >= (int) $threshold && (int) $lvl > $level) {
                $level = (int) $lvl;
            }
        }

        return $level;
    }

    /**
     * Ghi nhận một lượt chơi: cộng XP, cập nhật streak/cấp độ, xét huy hiệu.
     * Dùng DB transaction. Không gửi mail/thông báo ngoài.
     *
     * @return ['xp'=>int,'level_up'=>bool,'new_level'=>int,'new_badges'=>Collection,'streak'=>int]
     */
    public static function recordPlay(LearnerProfile $profile, PlaySession $session): array
    {
        return DB::transaction(function () use ($profile, $session) {
            $profile->refresh();
            $session->refresh();

            // Chống cộng XP 2 lần cho cùng một session (nếu Gameplay gọi lặp).
            $already = XpEvent::where('profile_id', $profile->id)
                ->where('source', 'play')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta_json, '\$.session_id')) = ?", [(string) $session->id])
                ->exists();

            if ($already) {
                return [
                    'xp'         => (int) $session->xp_earned,
                    'level_up'   => false,
                    'new_level'  => (int) $profile->level,
                    'new_badges' => collect(),
                    'streak'     => (int) $profile->current_streak,
                ];
            }

            $xpConf = config('vuihoc.xp');

            // --- 1. Tính XP của lượt chơi -----------------------------------
            $calc = self::calculatePlayXp($session, $xpConf);
            $playXp = $calc['total'];

            XpEvent::create([
                'profile_id' => $profile->id,
                'source'     => 'play',
                'amount'     => $playXp,
                'meta_json'  => json_encode([
                    'session_id'       => $session->id,
                    'lesson_id'        => $session->lesson_id,
                    'game_type'        => $session->game_type,
                    'score'            => $session->score,
                    'max_score'        => $session->max_score,
                    'accuracy'         => (float) $session->accuracy,
                    'duration_seconds' => $session->duration_seconds,
                    'correct_units'    => $calc['correct_units'],
                    'total_units'      => $calc['total_units'],
                    'time_bonus'       => $calc['time_bonus'],
                    'perfect_bonus'    => $calc['perfect_bonus'],
                ]),
            ]);

            // --- 2. Cập nhật streak ----------------------------------------
            $today = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();
            $last  = $profile->last_play_date?->toDateString();

            $streak = (int) $profile->current_streak;
            $totalXp = $playXp;

            if ($last === null) {
                $streak = 1;
            } elseif ($last === $today) {
                // đã chơi hôm nay: giữ nguyên streak, không cộng streak_bonus nữa
            } elseif ($last === Carbon::now('Asia/Ho_Chi_Minh')->subDay()->toDateString()) {
                $streak = $streak + 1;
            } else {
                $streak = 1; // đứt chuỗi
            }

            if ($last !== $today) {
                $streakBonus = (int) ($xpConf['streak_day'] ?? 0);
                $totalXp += $streakBonus;

                XpEvent::create([
                    'profile_id' => $profile->id,
                    'source'     => 'streak_bonus',
                    'amount'     => $streakBonus,
                    'meta_json'  => json_encode(['date' => $today, 'streak' => $streak]),
                ]);
            }

            $profile->total_xp = (int) $profile->total_xp + $totalXp;
            $profile->current_streak = $streak;
            $profile->longest_streak = max((int) $profile->longest_streak, $streak);
            $profile->last_play_date = $today;

            $oldLevel = (int) $profile->level;
            $newLevel = self::levelForXp($profile->total_xp);
            $profile->level = $newLevel;
            $profile->save();

            $session->xp_earned = $totalXp;
            $session->save();

            // --- 3. Xét và trao huy hiệu -----------------------------------
            $newBadges = self::awardBadges($profile);

            return [
                'xp'         => $totalXp,
                'level_up'   => $newLevel > $oldLevel,
                'new_level'  => $newLevel,
                'new_badges' => $newBadges,
                'streak'     => $streak,
            ];
        });
    }

    /**
     * Gợi ý độ khó tiếp theo cho skill.
     * Dựa vào accuracy trung bình 3 lượt chơi gần nhất của profile ở các
     * lesson thuộc skill: >= 80% → tăng 1 bậc; < 40% → giảm 1 bậc;
     * còn lại giữ nguyên. Mặc định 'trung_binh'.
     * Bậc: de < trung_binh < kho.
     */
    public static function suggestNextDifficulty(LearnerProfile $profile, Skill $skill): string
    {
        $ladder = ['de' => 0, 'trung_binh' => 1, 'kho' => 2];
        $names  = array_flip($ladder);

        $sessions = $profile->playSessions()
            ->where('status', 'finished')
            ->whereHas('lesson', fn ($q) => $q->where('skill_id', $skill->id))
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->limit(3)
            ->with('lesson')
            ->get();

        if ($sessions->isEmpty()) {
            return 'trung_binh';
        }

        $avg = $sessions->avg(fn ($s) => (float) $s->accuracy);

        $base = $sessions->first()->lesson?->difficulty ?? 'trung_binh';
        $idx  = $ladder[$base] ?? 1;

        if ($avg >= 80) {
            $idx = min(2, $idx + 1);
        } elseif ($avg < 40) {
            $idx = max(0, $idx - 1);
        }

        return $names[$idx];
    }

    // ================= helpers private =================

    /**
     * Tính XP cho một lượt chơi từ PlaySession.
     * @return ['total'=>int,'correct_units'=>int,'total_units'=>int,'time_bonus'=>int,'perfect_bonus'=>int]
     */
    private static function calculatePlayXp(PlaySession $session, array $xpConf): array
    {
        $perUnit = match ($session->game_type) {
            'quiz'     => (int) ($xpConf['quiz_correct'] ?? 10),
            'matching' => (int) ($xpConf['matching_pair'] ?? 5),
            'sort'     => (int) ($xpConf['sort_item'] ?? 5),
            'fill'     => (int) ($xpConf['fill_blank'] ?? 10),
            default    => 0,
        };

        $details = self::parseAnswerDetails($session->answers_json);
        $questionIds = self::parseJsonArray($session->question_ids_json);
        $numQuestions = max(count($questionIds), count($details), 1);

        if ($details !== []) {
            $correctUnits = 0;
            $totalUnits = 0;
            $secondsSum = 0;
            $secondsCount = 0;
            foreach ($details as $d) {
                $correct = (bool) ($d['correct'] ?? $d['is_correct'] ?? false);
                $cu = isset($d['correct_units']) ? (int) $d['correct_units'] : ($correct ? 1 : 0);
                $tu = isset($d['total_units']) ? (int) $d['total_units'] : 1;
                $correctUnits += max(0, $cu);
                $totalUnits += max(1, $tu);
                if (isset($d['seconds']) || isset($d['duration_seconds'])) {
                    $secondsSum += (int) ($d['seconds'] ?? $d['duration_seconds']);
                    $secondsCount++;
                }
            }
            $avgSecondsPerQuestion = $secondsCount > 0
                ? $secondsSum / max($secondsCount, 1)
                : $session->duration_seconds / max($totalUnits, 1);
        } else {
            // Fallback: suy số đơn vị đúng từ score và số câu từ question_ids_json.
            $totalUnits = $numQuestions;
            $correctUnits = (int) round($session->score / max($perUnit, 1));
            $correctUnits = min(max($correctUnits, 0), $totalUnits);
            $avgSecondsPerQuestion = $session->duration_seconds / max($totalUnits, 1);
        }

        $baseXp = $correctUnits * $perUnit;

        // Thưởng tốc độ chỉ áp dụng cho quiz.
        $timeBonus = 0;
        if ($session->game_type === 'quiz') {
            $maxBonus = (int) ($xpConf['quiz_time_bonus_max'] ?? 5);
            // <= 5 giây/câu: tối đa; >= 30 giây/câu: 0; ở giữa nội suy tuyến tính.
            $ratio = (30 - $avgSecondsPerQuestion) / 25;
            $ratio = min(1, max(0, $ratio));
            $timeBonus = (int) round($maxBonus * $ratio * $totalUnits);
        }

        $total = $baseXp + $timeBonus;

        // Thưởng perfect: đạt 100% lượt chơi → +perfect_bonus_percent (làm tròn).
        $perfectBonus = 0;
        if ((float) $session->accuracy >= 100) {
            $perfectBonus = (int) round($total * ((int) ($xpConf['perfect_bonus_percent'] ?? 20)) / 100);
            $total += $perfectBonus;
        }

        return [
            'total'         => $total,
            'correct_units' => $correctUnits,
            'total_units'   => $totalUnits,
            'time_bonus'    => $timeBonus,
            'perfect_bonus' => $perfectBonus,
        ];
    }

    /** Giải mã answers_json thành mảng chi tiết từng câu hỏi (hoặc []). */
    private static function parseAnswerDetails(?string $json): array
    {
        if (! $json) {
            return [];
        }
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return [];
        }
        // Hỗ trợ cả dạng {"details":[...]} và dạng mảng trực tiếp.
        $list = isset($data['details']) && is_array($data['details']) ? $data['details'] : $data;
        $out = [];
        foreach ($list as $item) {
            if (is_array($item)) {
                $out[] = $item;
            }
        }

        return $out;
    }

    private static function parseJsonArray(?string $json): array
    {
        if (! $json) {
            return [];
        }
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Xét toàn bộ huy hiệu trong bảng badges, trao những cái đạt điều kiện
     * mà profile chưa có. Trả về Collection các Badge vừa trao.
     */
    private static function awardBadges(LearnerProfile $profile): Collection
    {
        $badges = Badge::all();
        if ($badges->isEmpty()) {
            return collect();
        }

        $ownedIds = $profile->badges()->pluck('badges.id')->all();

        $finishedCount = $profile->playSessions()->where('status', 'finished')->count();
        $perfectCount  = $profile->playSessions()
            ->where('status', 'finished')
            ->where('accuracy', '>=', 100)
            ->count();
        $subjectsCount = $profile->playSessions()
            ->where('play_sessions.status', 'finished')
            ->join('lessons', 'lessons.id', '=', 'play_sessions.lesson_id')
            ->join('skills', 'skills.id', '=', 'lessons.skill_id')
            ->join('topics', 'topics.id', '=', 'skills.topic_id')
            ->distinct()
            ->count('topics.subject_id');

        $stats = [
            'finished'       => $finishedCount,
            'perfect'        => $perfectCount,
            'current_streak' => (int) $profile->current_streak,
            'total_xp'       => (int) $profile->total_xp,
            'subjects'       => $subjectsCount,
        ];

        $newBadges = collect();
        $now = now();

        foreach ($badges as $badge) {
            if (in_array($badge->id, $ownedIds, true)) {
                continue;
            }
            if (! self::badgeConditionMet($badge, $stats)) {
                continue;
            }

            try {
                $profile->badges()->attach($badge->id, ['earned_at' => $now]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Tranh chấp đồng thời (unique uq_pb_profile_badge) → bỏ qua.
                continue;
            }

            XpEvent::create([
                'profile_id' => $profile->id,
                'source'     => 'badge',
                'amount'     => 0,
                'meta_json'  => json_encode(['badge_id' => $badge->id, 'slug' => $badge->slug]),
            ]);

            $newBadges->push($badge);
        }

        return $newBadges;
    }

    /**
     * Kiểm tra điều kiện huy hiệu theo mã criteria:
     * first_play | streak_3/7/30 | xp_1000/5000 | perfect_5 | explorer_3 | scholar_10.
     * Ngưỡng lấy từ badges.threshold (fallback: số trong mã).
     */
    private static function badgeConditionMet(Badge $badge, array $stats): bool
    {
        $criteria = $badge->criteria;
        $threshold = (int) ($badge->threshold ?: 0);

        return match (true) {
            $criteria === 'first_play'            => $stats['finished'] >= 1,
            str_starts_with($criteria, 'streak_') => $stats['current_streak'] >= ($threshold ?: (int) substr($criteria, 7)),
            str_starts_with($criteria, 'xp_')     => $stats['total_xp'] >= ($threshold ?: (int) substr($criteria, 3)),
            $criteria === 'perfect_5'            => $stats['perfect'] >= ($threshold ?: 5),
            $criteria === 'explorer_3'           => $stats['subjects'] >= ($threshold ?: 3),
            $criteria === 'scholar_10'           => $stats['finished'] >= ($threshold ?: 10),
            default                              => false,
        };
    }
}
