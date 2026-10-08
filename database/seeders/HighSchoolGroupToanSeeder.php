<?php

namespace Database\Seeders;

use App\Models\FillAnswer;
use App\Models\Lesson;
use App\Models\MatchingPair;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Skill;
use App\Models\SortItem;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;

/**
 * Dữ liệu Toán THPT (lớp 10, 11, 12).
 *
 * Tạo 3 topics mới cho môn Toán (slug `toan`), mỗi topic đúng 1 khối:
 *  - toan-thpt-lop-10 (📐): "Mệnh đề, tập hợp và hàm số"      — grade 10
 *  - toan-thpt-lop-11 (📏): "Lượng giác và dãy số"            — grade 11
 *  - toan-thpt-lop-12 (🧮): "Đạo hàm và ứng dụng"             — grade 12
 *
 * Mỗi topic 2 skills, mỗi skill 2 bài học (slug {topic-slug}-lop-{grade}-{n},
 * n = 1..4 trong phạm vi topic), status 'published', grade = khối,
 * is_demo = true. Mỗi bài đúng 16 câu: 4 quiz + 4 matching + 4 sort + 4 fill.
 * Tổng: 12 bài, 192 câu. Nội dung TIẾNG VIỆT TỰ VIẾT 100%, đúng chương trình
 * THPT Việt Nam, mỗi câu có explanation.
 *
 * Lưu ý: cột difficulty của lessons/questions là enum ('de','trung_binh','kho')
 * nên 'medium' → 'trung_binh', 'hard' → 'kho'.
 *
 * Idempotent: topic/skill dùng firstOrCreate theo slug; lesson bỏ qua khi
 * slug đã tồn tại; câu hỏi bỏ qua khi (lesson_id, game_type) đã được seed.
 */
class HighSchoolGroupToanSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $gradeBySlug = [];

    /** Cấu trúc: topic_slug => [grade, name, icon, description, skills...] */
    private array $plan = [
        'toan-thpt-lop-10' => [
            'grade' => 10,
            'name' => 'Mệnh đề, tập hợp và hàm số',
            'icon' => '📐',
            'description' => 'Mệnh đề, tập hợp, hàm số bậc hai và dấu của tam thức bậc hai.',
            'skills' => [
                ['slug' => 'kn-thpt10-menh-de-tap-hop', 'name' => 'Mệnh đề và tập hợp'],
                ['slug' => 'kn-thpt10-ham-so-bac-hai', 'name' => 'Hàm số bậc hai'],
            ],
        ],
        'toan-thpt-lop-11' => [
            'grade' => 11,
            'name' => 'Lượng giác và dãy số',
            'icon' => '📏',
            'description' => 'Hàm số lượng giác, phương trình lượng giác cơ bản, dãy số và cấp số.',
            'skills' => [
                ['slug' => 'kn-thpt11-luong-giac', 'name' => 'Hàm số lượng giác & phương trình'],
                ['slug' => 'kn-thpt11-day-so-cap-so', 'name' => 'Dãy số và cấp số'],
            ],
        ],
        'toan-thpt-lop-12' => [
            'grade' => 12,
            'name' => 'Đạo hàm và ứng dụng',
            'icon' => '🧮',
            'description' => 'Đạo hàm, quy tắc tính đạo hàm, khảo sát sự biến thiên và cực trị hàm số.',
            'skills' => [
                ['slug' => 'kn-thpt12-dao-ham', 'name' => 'Đạo hàm'],
                ['slug' => 'kn-thpt12-khao-sat-ham-so', 'name' => 'Khảo sát hàm số'],
            ],
        ],
    ];

    public function run(): void
    {
        $subject = Subject::where('slug', 'toan')->firstOrFail();

        foreach ($this->plan as $topicSlug => $cfg) {
            $maxOrder = (int) Topic::where('subject_id', $subject->id)->max('sort_order');
            $topic = Topic::firstOrCreate(
                ['slug' => $topicSlug],
                [
                    'subject_id' => $subject->id,
                    'name' => $cfg['name'],
                    'description' => $cfg['description'],
                    'icon' => $cfg['icon'],
                    'sort_order' => $maxOrder + 1,
                    'grade_min' => $cfg['grade'],
                    'grade_max' => $cfg['grade'],
                    'is_published' => true,
                    'is_demo' => true,
                ]
            );

            $num = 0;
            foreach ($cfg['skills'] as $sIdx => $s) {
                $maxSkillOrder = (int) Skill::where('topic_id', $topic->id)->max('sort_order');
                $skill = Skill::firstOrCreate(
                    ['slug' => $s['slug']],
                    [
                        'topic_id' => $topic->id,
                        'name' => $s['name'],
                        'description' => $s['name'],
                        'sort_order' => $maxSkillOrder + 1,
                        'is_demo' => true,
                    ]
                );
                for ($k = 1; $k <= 2; $k++) {
                    $num++;
                    $meta = $this->lessonMeta($topicSlug, $num);
                    $this->createLesson($skill, $topicSlug, $cfg['grade'], $num, $meta);
                }
            }
        }

        // ---- Lớp 10 ----
        $this->seedL10MenhDe1(); $this->seedL10TapHop2();
        $this->seedL10HamSoBacHai3(); $this->seedL10TamThuc4();
        // ---- Lớp 11 ----
        $this->seedL11LuongGiac1(); $this->seedL11PtLuongGiac2();
        $this->seedL11CapSoCong3(); $this->seedL11CapSoNhan4();
        // ---- Lớp 12 ----
        $this->seedL12DaoHam1(); $this->seedL12QuyTacDaoHam2();
        $this->seedL12DonDieuCucTri3(); $this->seedL12GtlnGtnn4();
    }

    // ---------------- helpers (tái dùng y hệt file mẫu) ----------------

    private function lesson(string $slug): Lesson
    {
        if (! isset($this->lessonBySlug[$slug])) {
            $this->lessonBySlug[$slug] = Lesson::where('slug', $slug)->firstOrFail();
        }
        return $this->lessonBySlug[$slug];
    }

    private function createLesson(Skill $skill, string $topicSlug, int $grade, int $num, array $meta): void
    {
        $slug = "{$topicSlug}-lop-{$grade}-{$num}";
        $existing = Lesson::where('slug', $slug)->first();
        if ($existing) {
            $this->lessonBySlug[$slug] = $existing;
            return; // đã tồn tại: bỏ qua (idempotent)
        }
        $maxOrder = (int) Lesson::where('skill_id', $skill->id)->max('sort_order');
        $lesson = Lesson::create([
            'skill_id' => $skill->id,
            'slug' => $slug,
            'title' => $meta['title'],
            'objective' => $meta['objective'],
            'difficulty' => $meta['difficulty'],
            'duration_minutes' => $meta['duration'],
            'instructions' => $meta['instructions'],
            'sort_order' => $maxOrder + 1,
            'status' => 'published',
            'grade' => $grade,
            'is_demo' => true,
        ]);
        $this->lessonBySlug[$slug] = $lesson;
        $this->gradeBySlug[$slug] = $grade;
    }

    private function seeded(string $lessonSlug, string $type): bool
    {
        return Question::where('lesson_id', $this->lesson($lessonSlug)->id)
            ->where('game_type', $type)->exists();
    }

    private function newQuestion(string $lessonSlug, string $gameType, string $prompt, string $explanation, string $difficulty = 'de', int $points = 10): Question
    {
        $lesson = $this->lesson($lessonSlug);
        $this->orderByLesson[$lessonSlug] = ($this->orderByLesson[$lessonSlug] ?? 0) + 1;

        return Question::create([
            'lesson_id'   => $lesson->id,
            'game_type'   => $gameType,
            'prompt'      => $prompt,
            'explanation' => $explanation,
            'difficulty'  => $difficulty,
            'points'      => $points,
            'sort_order'  => $this->orderByLesson[$lessonSlug],
            'grade'       => $this->gradeBySlug[$lessonSlug] ?? $lesson->grade,
            'is_demo'     => true,
        ]);
    }

    private function quiz(string $lesson, string $prompt, array $options, int $correct, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'quiz', $prompt, $explanation, $difficulty);
        foreach ($options as $i => $text) {
            QuestionOption::create([
                'question_id' => $q->id, 'option_text' => $text,
                'is_correct' => $i === $correct, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function matching(string $lesson, string $prompt, array $pairs, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'matching', $prompt, $explanation, $difficulty);
        foreach ($pairs as $i => [$left, $right]) {
            MatchingPair::create([
                'question_id' => $q->id, 'left_text' => $left, 'right_text' => $right, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function sortQ(string $lesson, string $prompt, array $items, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'sort', $prompt, $explanation, $difficulty);
        foreach ($items as $i => [$text, $category]) {
            SortItem::create([
                'question_id' => $q->id, 'item_text' => $text, 'category' => $category, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function fill(string $lesson, string $prompt, array $answers, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'fill', $prompt, $explanation, $difficulty);
        foreach ($answers as $i => [$blankIndex, $text]) {
            FillAnswer::create([
                'question_id' => $q->id, 'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1,
            ]);
        }
    }

    /**
     * Siêu dữ liệu 12 bài học: [$topicSlug][$num] =>
     * [title, objective, difficulty, duration, instructions].
     */
    private function lessonMeta(string $topicSlug, int $num): array
    {
        $plan = [
            'toan-thpt-lop-10' => [
                1 => ['title' => 'Mệnh đề và tính đúng – sai của mệnh đề',
                    'objective' => 'Nhận biết mệnh đề; xác định tính đúng – sai của mệnh đề; lập mệnh đề phủ định; dùng ký hiệu ∀, ∃.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Mệnh đề là câu khẳng định có tính đúng hoặc sai. Câu hỏi, câu cảm thán, câu cầu khiến không phải là mệnh đề. Phủ định của "∀x, P(x)" là "∃x, không P(x)" và ngược lại.'],
                2 => ['title' => 'Tập hợp và các phép toán trên tập hợp',
                    'objective' => 'Dùng ký hiệu ∈, ∉, ⊂; thực hiện giao, hợp, hiệu hai tập hợp; biểu diễn tập số bằng khoảng, đoạn.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'A ∩ B gồm phần tử chung của A và B; A ∪ B gồm mọi phần tử của A hoặc B; A \\ B gồm phần tử thuộc A nhưng không thuộc B. Tập rỗng ∅ là tập con của mọi tập hợp.'],
                3 => ['title' => 'Hàm số bậc hai và đồ thị parabol',
                    'objective' => 'Xác định đỉnh, trục đối xứng, hướng bề lõm của parabol y = ax² + bx + c; tìm giá trị lớn nhất – nhỏ nhất.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Đỉnh I(−b/2a; −Δ/4a), trục đối xứng x = −b/2a. a > 0: bề lõm hướng lên, hàm số có giá trị nhỏ nhất; a < 0: bề lõm hướng xuống, hàm số có giá trị lớn nhất.'],
                4 => ['title' => 'Dấu của tam thức bậc hai và bất phương trình bậc hai',
                    'objective' => 'Xét dấu tam thức bậc hai theo a và Δ; giải bất phương trình bậc hai một ẩn.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Δ < 0: f(x) cùng dấu với a với mọi x. Δ = 0: f(x) cùng dấu với a, trừ nghiệm kép. Δ > 0: f(x) trái dấu với a giữa hai nghiệm, cùng dấu với a ngoài khoảng hai nghiệm ("trong trái, ngoài cùng").'],
            ],
            'toan-thpt-lop-11' => [
                1 => ['title' => 'Góc lượng giác và giá trị lượng giác của góc đặc biệt',
                    'objective' => 'Nhớ giá trị sin, cos, tan của các góc đặc biệt; vận dụng công thức sin²α + cos²α = 1.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Nhớ bảng giá trị: sin 30° = cos 60° = 1/2; sin 60° = cos 30° = √3/2; tan 45° = 1. Công thức cơ bản: sin²α + cos²α = 1; tan α = sin α / cos α (cos α ≠ 0).'],
                2 => ['title' => 'Phương trình lượng giác cơ bản',
                    'objective' => 'Giải các phương trình sin x = a, cos x = a, tan x = a, cot x = a; nắm điều kiện xác định của tan, cot.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'sin x = 0 ⇔ x = kπ; cos x = 0 ⇔ x = π/2 + kπ; sin x = 1 ⇔ x = π/2 + k2π; cos x = −1 ⇔ x = π + k2π. tan x xác định khi x ≠ π/2 + kπ; cot x xác định khi x ≠ kπ. |a| > 1 thì sin x = a và cos x = a vô nghiệm.'],
                3 => ['title' => 'Cấp số cộng: số hạng tổng quát và tổng n số hạng đầu',
                    'objective' => 'Nhận biết cấp số cộng; tính số hạng tổng quát uₙ = u₁ + (n − 1)d và tổng Sₙ.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Cấp số cộng: uₙ₊₁ = uₙ + d (d là công sai). Số hạng tổng quát: uₙ = u₁ + (n − 1)d. Tổng n số hạng đầu: Sₙ = n(u₁ + uₙ)/2 = n[2u₁ + (n − 1)d]/2.'],
                4 => ['title' => 'Cấp số nhân: số hạng tổng quát và tổng n số hạng đầu',
                    'objective' => 'Nhận biết cấp số nhân; tính số hạng tổng quát uₙ = u₁·qⁿ⁻¹ và tổng Sₙ.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Cấp số nhân: uₙ₊₁ = uₙ · q (q là công bội). Số hạng tổng quát: uₙ = u₁ · qⁿ⁻¹. Tổng n số hạng đầu: Sₙ = u₁(qⁿ − 1)/(q − 1) với q ≠ 1.'],
            ],
            'toan-thpt-lop-12' => [
                1 => ['title' => 'Đạo hàm: định nghĩa và các công thức cơ bản',
                    'objective' => 'Tính đạo hàm của hàm đa thức, hàm phân thức đơn giản, sin, cos, eˣ, ln x bằng công thức.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Nhớ công thức: (xⁿ)' . "'" . ' = n·xⁿ⁻¹; (√x)' . "'" . ' = 1/(2√x); (1/x)' . "'" . ' = −1/x²; (sin x)' . "'" . ' = cos x; (cos x)' . "'" . ' = −sin x; (eˣ)' . "'" . ' = eˣ; (ln x)' . "'" . ' = 1/x. Đạo hàm của hằng số bằng 0.'],
                2 => ['title' => 'Quy tắc tính đạo hàm: tổng, tích, thương và hàm hợp',
                    'objective' => 'Vận dụng quy tắc đạo hàm của tổng, hiệu, tích, thương và hàm hợp để tính đạo hàm.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => "(u + v)' = u' + v'; (u·v)' = u'v + uv'; (u/v)' = (u'v − uv')/v². Đạo hàm hàm hợp: nếu y = f(u), u = g(x) thì y'_x = y'_u · u'_x, ví dụ [(3x+1)²]' = 6(3x+1)."],
                3 => ['title' => 'Tính đơn điệu và cực trị của hàm số',
                    'objective' => 'Xét tính đơn điệu bằng dấu của đạo hàm; tìm điểm cực đại, cực tiểu và giá trị cực trị.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => "y' > 0: hàm số đồng biến; y' < 0: hàm số nghịch biến. y' đổi dấu từ + sang − tại x₀: x₀ là điểm cực đại; đổi dấu từ − sang +: x₀ là điểm cực tiểu."],
                4 => ['title' => 'Giá trị lớn nhất – giá trị nhỏ nhất của hàm số',
                    'objective' => 'Tìm GTLN, GTNN của hàm số trên một đoạn bằng cách so sánh giá trị tại điểm tới hạn và hai đầu mút.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Trên đoạn [a; b], hàm số liên tục luôn có GTLN và GTNN. Cách tìm: tính y' . "'" . ', tìm các điểm tới hạn trong (a; b), rồi so sánh giá trị hàm số tại các điểm đó với f(a), f(b).'],
            ],
        ];

        return $plan[$topicSlug][$num];
    }

    // ================= LỚP 10 =================

    private function seedL10MenhDe1(): void
    {
        $L = 'toan-thpt-lop-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trong các câu sau, câu nào là mệnh đề đúng?',
                ['Phương trình x² − 1 = 0 có nghiệm duy nhất x = 1', 'Tam giác đều có ba góc bằng 60°', 'Số 12 chia hết cho 5', 'Số π là số hữu tỉ'], 1,
                'Tam giác đều có ba góc đều bằng 60° là khẳng định đúng. x² − 1 = 0 có hai nghiệm x = ±1; 12 không chia hết cho 5; π là số vô tỉ.');
            $this->quiz($L, 'Mệnh đề phủ định của mệnh đề "∀x ∈ ℝ, x² ≥ 0" là mệnh đề nào?',
                ['∃x ∈ ℝ, x² < 0', '∀x ∈ ℝ, x² < 0', '∃x ∈ ℝ, x² > 0', '∀x ∈ ℝ, x² ≤ 0'], 0,
                'Phủ định của "với mọi" là "tồn tại", đồng thời phủ định bất đẳng thức: x² ≥ 0 thành x² < 0.', 'trung_binh');
            $this->quiz($L, 'Trong các câu sau, câu nào KHÔNG phải là mệnh đề?',
                ['Hà Nội là thủ đô của Việt Nam', 'Bạn có thích học Toán không?', '15 chia hết cho 3', 'Số 7 là số lẻ'], 1,
                'Câu hỏi không khẳng định điều gì nên không có tính đúng hoặc sai, do đó không phải là mệnh đề.');
            $this->quiz($L, 'Mệnh đề "Nếu a chia hết cho 6 thì a chia hết cho 3" là mệnh đề đúng hay sai?',
                ['Đúng', 'Sai', 'Không phải là mệnh đề', 'Không xác định được'], 0,
                'Mọi số chia hết cho 6 đều viết được dưới dạng 6k = 3·(2k) nên chia hết cho 3. Mệnh đề điều kiện này luôn đúng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi mệnh đề với tính đúng – sai của nó.',
                [['∀n ∈ ℕ, n² ≥ n', 'Đúng'], ['∃x ∈ ℝ, x² = −1', 'Sai'],
                 ['∀x ∈ ℝ, x² + 1 > 0', 'Đúng'], ['∃n ∈ ℕ, n + 1 = 0', 'Sai']],
                'Với mọi số tự nhiên n đều có n² ≥ n. Không có số thực nào bình phương bằng −1. x² + 1 luôn lớn hơn 0. Không có số tự nhiên n nào thỏa n + 1 = 0.', 'trung_binh');
            $this->matching($L, 'Nối mỗi ký hiệu logic với cách đọc của nó.',
                [['∀', 'Với mọi'], ['∃', 'Tồn tại'], ['⇒', 'Suy ra'], ['⇔', 'Tương đương']],
                '∀ đọc là "với mọi", ∃ đọc là "tồn tại", ⇒ là ký hiệu suy ra, ⇔ là ký hiệu tương đương.');
            $this->matching($L, 'Nối mỗi câu với phân loại của nó.',
                [['"7 là số nguyên tố"', 'Là mệnh đề'], ['"Bạn tên gì?"', 'Không phải mệnh đề'],
                 ['"Sông Hồng chảy qua Hà Nội"', 'Là mệnh đề'], ['"Hãy mở cửa ra!"', 'Không phải mệnh đề']],
                'Câu khẳng định có tính đúng/sai là mệnh đề; câu hỏi và câu cầu khiến không phải là mệnh đề.');
            $this->matching($L, 'Nối mỗi mệnh đề với mệnh đề phủ định của nó.',
                [['"Mọi học sinh đều đi học"', '"Có ít nhất một học sinh không đi học"'],
                 ['"Tồn tại số chẵn là số nguyên tố"', '"Mọi số chẵn đều không là số nguyên tố"'],
                 ['"x ≥ 5"', '"x < 5"'], ['"a = b"', '"a ≠ b"']],
                'Phủ định của "mọi" là "tồn tại ... không", phủ định của "tồn tại" là "mọi ... đều không".', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm MỆNH ĐỀ ĐÚNG hoặc MỆNH ĐỀ SAI.',
                [['Số 13 là số nguyên tố', 'Mệnh đề đúng'], ['2 + 2 = 5', 'Mệnh đề sai'],
                 ['Tam giác vuông có một góc 90°', 'Mệnh đề đúng'], ['Số 15 là số chẵn', 'Mệnh đề sai'],
                 ['Mọi số tự nhiên đều lớn hơn 0', 'Mệnh đề sai'], ['9 là số chính phương', 'Mệnh đề đúng']],
                '13 là số nguyên tố; 2 + 2 = 4; 15 là số lẻ; số 0 là số tự nhiên nhưng không lớn hơn 0; 9 = 3².');
            $this->sortQ($L, 'Kéo mỗi mệnh đề vào nhóm chứa ký hiệu ∀ hoặc chứa ký hiệu ∃.',
                [['∀x ∈ ℝ, x² ≥ 0', 'Chứa ký hiệu ∀'], ['∀n ∈ ℕ, 2n là số chẵn', 'Chứa ký hiệu ∀'],
                 ['∃x ∈ ℝ, x³ = 8', 'Chứa ký hiệu ∃'], ['∃n ∈ ℕ, n là số nguyên tố chẵn', 'Chứa ký hiệu ∃'],
                 ['∀a ∈ ℝ, a + 0 = a', 'Chứa ký hiệu ∀'], ['∃k ∈ ℤ, 3k = 7', 'Chứa ký hiệu ∃']],
                '∀ là ký hiệu "với mọi", ∃ là ký hiệu "tồn tại".');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm MỆNH ĐỀ hoặc KHÔNG PHẢI MỆNH ĐỀ.',
                [['"Số π lớn hơn 3"', 'Mệnh đề'], ['"Hãy mở cửa ra!"', 'Không phải mệnh đề'],
                 ['"Bạn ăn cơm chưa?"', 'Không phải mệnh đề'], ['"Sông Hồng chảy qua Hà Nội"', 'Mệnh đề'],
                 ['"Ôi, đẹp quá!"', 'Không phải mệnh đề'], ['"12 chia hết cho 4"', 'Mệnh đề']],
                'Chỉ câu khẳng định có tính đúng hoặc sai mới là mệnh đề.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm MỆNH ĐỀ ĐIỀU KIỆN (dạng "Nếu... thì...") hoặc KHÔNG PHẢI.',
                [['"Nếu tam giác ABC đều thì AB = BC"', 'Mệnh đề điều kiện'],
                 ['"Nếu x > 2 thì x² > 4"', 'Mệnh đề điều kiện'],
                 ['"5 là số lẻ"', 'Không phải mệnh đề điều kiện'],
                 ['"Số 8 chia hết cho 2"', 'Không phải mệnh đề điều kiện'],
                 ['"Nếu n chia hết cho 4 thì n chia hết cho 2"', 'Mệnh đề điều kiện'],
                 ['"Hà Nội là thủ đô của Việt Nam"', 'Không phải mệnh đề điều kiện']],
                'Mệnh đề điều kiện có dạng "Nếu P thì Q", ký hiệu P ⇒ Q.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mệnh đề phủ định của "x > 10" là "x ___ 10" (điền ký hiệu).', [[0, '<=']],
                'Phủ định của "lớn hơn" là "nhỏ hơn hoặc bằng".');
            $this->fill($L, 'Mệnh đề "∀n ∈ ℕ, n + 1 > n" là mệnh đề ___.', [[0, 'đúng']],
                'Với mọi số tự nhiên n, n + 1 luôn lớn hơn n nên mệnh đề đúng.');
            $this->fill($L, 'Câu "x + 5 = 10" với x là biến chưa xác định ___ phải là mệnh đề (điền "có" hoặc "không").', [[0, 'không']],
                'Khi x chưa xác định, ta không biết câu đúng hay sai nên chưa phải là mệnh đề.');
            $this->fill($L, 'Ký hiệu ∃ đọc là "___ tại" (điền từ còn thiếu).', [[0, 'tồn']],
                '∃ đọc là "tồn tại".');
        }
    }

    private function seedL10TapHop2(): void
    {
        $L = 'toan-thpt-lop-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cho A = {1; 2; 3}. Khẳng định nào sau đây đúng?',
                ['2 ∈ A', '4 ∈ A', '2 ⊂ A', '{1; 2} ∈ A'], 0,
                '2 là phần tử của A nên 2 ∈ A. {1; 2} là tập con của A, phải viết {1; 2} ⊂ A chứ không dùng ∈.');
            $this->quiz($L, 'Cho A = {1; 2}, B = {2; 3}. Tập hợp A ∩ B bằng tập hợp nào?',
                ['{2}', '{1; 2; 3}', '{1; 3}', '∅'], 0,
                'Giao của A và B gồm các phần tử chung của cả hai tập hợp, ở đây chỉ có số 2.');
            $this->quiz($L, 'Cho A = {1; 2}, B = {2; 3}. Tập hợp A ∪ B bằng tập hợp nào?',
                ['{1; 2; 3}', '{2}', '{1; 3}', '{1; 2; 2; 3}'], 0,
                'Hợp của A và B gồm mọi phần tử thuộc A hoặc thuộc B, mỗi phần tử chỉ liệt kê một lần.');
            $this->quiz($L, 'Số phần tử của tập hợp {x ∈ ℕ | x < 5} là bao nhiêu?',
                ['4', '5', '6', '3'], 1,
                'Các số tự nhiên nhỏ hơn 5 là 0, 1, 2, 3, 4 — gồm 5 phần tử.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tập hợp với số phần tử của nó.',
                [['{a; b; c}', '3 phần tử'], ['{1; 2; 3; 4; 5}', '5 phần tử'],
                 ['∅', '0 phần tử'], ['{x ∈ ℕ | 2 < x < 3}', '0 phần tử']],
                'Không có số tự nhiên nào lớn hơn 2 và nhỏ hơn 3 nên tập hợp cuối là tập rỗng.');
            $this->matching($L, 'Cho A = {1; 2; 3}, B = {3; 4}. Nối mỗi phép toán với kết quả của nó.',
                [['A ∩ B', '{3}'], ['A ∪ B', '{1; 2; 3; 4}'],
                 ['A \\ B', '{1; 2}'], ['B \\ A', '{4}']],
                'A ∩ B = {3}; A ∪ B = {1; 2; 3; 4}; A \\ B gồm phần tử thuộc A mà không thuộc B là {1; 2}; B \\ A = {4}.', 'trung_binh');
            $this->matching($L, 'Nối mỗi ký hiệu tập hợp với ý nghĩa của nó.',
                [['⊂', 'Là tập con của'], ['∪', 'Hợp hai tập hợp'],
                 ['∩', 'Giao hai tập hợp'], ['∈', 'Thuộc tập hợp']],
                '∈: thuộc; ⊂: là tập con của; ∪: phép hợp; ∩: phép giao.');
            $this->matching($L, 'Nối mỗi cách viết khoảng với tập hợp số tương ứng.',
                [['[1; 3]', 'Các số thực từ 1 đến 3, kể cả 1 và 3'],
                 ['(1; 3)', 'Các số thực lớn hơn 1 và nhỏ hơn 3'],
                 ['[1; 3)', 'Các số thực từ 1 đến dưới 3, kể cả 1'],
                 ['(−∞; 2]', 'Các số thực nhỏ hơn hoặc bằng 2']],
                'Ngoặc vuông [ ] nghĩa là lấy cả đầu mút, ngoặc tròn ( ) nghĩa là không lấy đầu mút.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Xét tập hợp {2; 4; 6}. Kéo mỗi số vào nhóm THUỘC hoặc KHÔNG THUỘC tập hợp.',
                [['2', 'Thuộc tập hợp'], ['5', 'Không thuộc tập hợp'],
                 ['6', 'Thuộc tập hợp'], ['3', 'Không thuộc tập hợp'],
                 ['4', 'Thuộc tập hợp'], ['7', 'Không thuộc tập hợp']],
                'Chỉ các số 2, 4, 6 thuộc tập hợp {2; 4; 6}.');
            $this->sortQ($L, 'Xét tập hợp {1; 2; 3}. Kéo mỗi tập hợp vào nhóm LÀ TẬP CON hoặc KHÔNG PHẢI TẬP CON.',
                [['{1}', 'Là tập con'], ['{1; 3}', 'Là tập con'],
                 ['∅', 'Là tập con'], ['{2; 4}', 'Không phải tập con'],
                 ['{1; 2; 3}', 'Là tập con'], ['{5}', 'Không phải tập con']],
                'Tập rỗng ∅ là tập con của mọi tập hợp. {2; 4} không phải tập con vì 4 không thuộc {1; 2; 3}.');
            $this->sortQ($L, 'Xét A = {x ∈ ℕ | x < 6}. Kéo mỗi số vào nhóm THUỘC A hoặc KHÔNG THUỘC A.',
                [['5', 'Thuộc A'], ['0', 'Thuộc A'],
                 ['6', 'Không thuộc A'], ['3', 'Thuộc A'],
                 ['−1', 'Không thuộc A'], ['10', 'Không thuộc A']],
                'A = {0; 1; 2; 3; 4; 5}. Số 6 không nhỏ hơn 6; −1 không phải số tự nhiên.');
            $this->sortQ($L, 'Kéo mỗi số vào nhóm SỐ TỰ NHIÊN (ℕ) hoặc SỐ NGUYÊN ÂM.',
                [['5', 'Số tự nhiên'], ['0', 'Số tự nhiên'],
                 ['−3', 'Số nguyên âm'], ['−7', 'Số nguyên âm'],
                 ['2', 'Số tự nhiên'], ['−1', 'Số nguyên âm']],
                'Số tự nhiên gồm 0, 1, 2, 3, ... Số nguyên âm là các số nguyên nhỏ hơn 0.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cho A = {1; 2; 3}, B = {3; 4; 5}. Khi đó A ∩ B = {___} (điền phần tử chung).', [[0, '3']],
                'Phần tử chung duy nhất của A và B là số 3.');
            $this->fill($L, 'Tập hợp không có phần tử nào gọi là tập hợp ___ (điền từ).', [[0, 'rỗng']],
                'Tập hợp rỗng ký hiệu là ∅.');
            $this->fill($L, 'Tập hợp các số thực x thỏa mãn 1 < x ≤ 4 viết dưới dạng khoảng là (1; ___].', [[0, '4']],
                'x ≤ 4 nên đầu mút 4 được lấy, viết dưới dạng ngoặc vuông: (1; 4].');
            $this->fill($L, 'Tập hợp rỗng là tập con của ___ tập hợp (điền "mọi" hoặc "một số").', [[0, 'mọi']],
                'Theo quy ước, tập rỗng là tập con của mọi tập hợp.', 'trung_binh');
        }
    }

    private function seedL10HamSoBacHai3(): void
    {
        $L = 'toan-thpt-lop-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đồ thị của hàm số y = x² có dạng nào?',
                ['Parabol mở lên trên', 'Parabol mở xuống dưới', 'Đường thẳng', 'Đường tròn'], 0,
                'Với a = 1 > 0, đồ thị y = x² là parabol có bề lõm hướng lên trên, đỉnh tại gốc tọa độ O.');
            $this->quiz($L, 'Tọa độ đỉnh của parabol y = x² − 4x + 3 là điểm nào?',
                ['(2; −1)', '(−2; 11)', '(2; 1)', '(−2; −1)'], 0,
                'Hoành độ đỉnh x = −b/2a = 4/2 = 2; tung độ y = 2² − 4·2 + 3 = −1. Vậy đỉnh I(2; −1).', 'trung_binh');
            $this->quiz($L, 'Hàm số y = −2x² + 4x − 1 có giá trị lớn nhất bằng bao nhiêu?',
                ['1', '−1', '2', '−2'], 0,
                'a = −2 < 0 nên hàm số có giá trị lớn nhất tại đỉnh: x = −4/(2·(−2)) = 1, y = −2 + 4 − 1 = 1.', 'trung_binh');
            $this->quiz($L, 'Trục đối xứng của parabol y = 2x² + 8x + 5 là đường thẳng nào?',
                ['x = −2', 'x = 2', 'x = −8', 'x = 4'], 0,
                'Trục đối xứng có phương trình x = −b/2a = −8/4 = −2.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hàm số bậc hai với tọa độ đỉnh của parabol.',
                [['y = x²', 'I(0; 0)'], ['y = (x − 1)²', 'I(1; 0)'],
                 ['y = x² + 2', 'I(0; 2)'], ['y = (x + 2)² − 1', 'I(−2; −1)']],
                'Dạng y = (x − h)² + k có đỉnh I(h; k).', 'trung_binh');
            $this->matching($L, 'Nối mỗi hàm số với hướng bề lõm của parabol.',
                [['y = 3x²', 'Bề lõm hướng lên'], ['y = −x² + 1', 'Bề lõm hướng xuống'],
                 ['y = 2x² − 5', 'Bề lõm hướng lên'], ['y = −4x²', 'Bề lõm hướng xuống']],
                'a > 0: bề lõm hướng lên; a < 0: bề lõm hướng xuống.');
            $this->matching($L, 'Nối mỗi hàm số với trục đối xứng của parabol.',
                [['y = x² − 2x', 'x = 1'], ['y = x² + 4x', 'x = −2'],
                 ['y = 2x² − 6x', 'x = 1,5'], ['y = −x² + 2x + 1', 'x = 1']],
                'Trục đối xứng x = −b/2a: với y = 2x² − 6x thì x = 6/4 = 1,5.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hàm số với giá trị lớn nhất hoặc nhỏ nhất của nó.',
                [['y = x² − 2x + 5', 'Giá trị nhỏ nhất là 4'],
                 ['y = −x² + 4x', 'Giá trị lớn nhất là 4'],
                 ['y = x² + 1', 'Giá trị nhỏ nhất là 1'],
                 ['y = −2x² − 3', 'Giá trị lớn nhất là −3']],
                'Giá trị tại đỉnh: y = x² − 2x + 5 có đỉnh (1; 4); y = −x² + 4x có đỉnh (2; 4).', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm PARABOL MỞ LÊN TRÊN hoặc MỞ XUỐNG DƯỚI.',
                [['y = x²', 'Mở lên trên'], ['y = −3x²', 'Mở xuống dưới'],
                 ['y = 2x² − 1', 'Mở lên trên'], ['y = −x² + 5', 'Mở xuống dưới'],
                 ['y = 5x² + 2x', 'Mở lên trên'], ['y = −2x² + x', 'Mở xuống dưới']],
                'Dấu của hệ số a quyết định hướng bề lõm: a > 0 mở lên, a < 0 mở xuống.');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm ĐỒ THỊ ĐI QUA GỐC TỌA ĐỘ O hoặc KHÔNG ĐI QUA O.',
                [['y = x²', 'Đi qua O'], ['y = x² − 2x', 'Đi qua O'],
                 ['y = x² + 1', 'Không đi qua O'], ['y = 3x² + x', 'Đi qua O'],
                 ['y = −x² + 4', 'Không đi qua O'], ['y = 2x(x − 1)', 'Đi qua O']],
                'Đồ thị đi qua O khi f(0) = 0, tức là hệ số tự do bằng 0.');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm CÓ GIÁ TRỊ NHỎ NHẤT hoặc CÓ GIÁ TRỊ LỚN NHẤT.',
                [['y = x²', 'Có giá trị nhỏ nhất'], ['y = −x²', 'Có giá trị lớn nhất'],
                 ['y = 3x² + 2', 'Có giá trị nhỏ nhất'], ['y = −2x² + 1', 'Có giá trị lớn nhất'],
                 ['y = (x − 1)² − 5', 'Có giá trị nhỏ nhất'], ['y = −(x + 2)²', 'Có giá trị lớn nhất']],
                'a > 0: hàm số có giá trị nhỏ nhất tại đỉnh; a < 0: có giá trị lớn nhất tại đỉnh.');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm TRỤC ĐỐI XỨNG LÀ TRỤC TUNG (x = 0) hoặc TRỤC ĐỐI XỨNG KHÁC.',
                [['y = x²', 'Trục đối xứng x = 0'], ['y = 2x² + 3', 'Trục đối xứng x = 0'],
                 ['y = x² − 2x', 'Trục đối xứng khác'], ['y = −x² + 5', 'Trục đối xứng x = 0'],
                 ['y = x² + 4x + 1', 'Trục đối xứng khác'], ['y = −3x² − 2', 'Trục đối xứng x = 0']],
                'Khi b = 0, trục đối xứng x = −b/2a = 0 chính là trục tung.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đỉnh của parabol y = x² − 6x + 8 có hoành độ bằng ___ (điền số).', [[0, '3']],
                'Hoành độ đỉnh x = −b/2a = 6/2 = 3.', 'trung_binh');
            $this->fill($L, 'Hàm số y = −x² + 2x + 3 đạt giá trị lớn nhất bằng ___ (điền số).', [[0, '4']],
                'Đỉnh tại x = 1, giá trị lớn nhất y = −1 + 2 + 3 = 4.', 'trung_binh');
            $this->fill($L, 'Parabol y = ax² + bx + c (a ≠ 0) có bề lõm hướng lên khi a ___ 0 (điền dấu).', [[0, '>']],
                'a > 0 thì bề lõm hướng lên trên, a < 0 thì bề lõm hướng xuống dưới.');
            $this->fill($L, 'Giao điểm của parabol y = x² với trục tung là điểm có tọa độ (0; ___) (điền số).', [[0, '0']],
                'Thay x = 0 vào y = x² được y = 0, vậy giao điểm là O(0; 0).');
        }
    }

    private function seedL10TamThuc4(): void
    {
        $L = 'toan-thpt-lop-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tam thức f(x) = x² − 3x + 2 có các nghiệm là?',
                ['x = 1 và x = 2', 'x = −1 và x = −2', 'x = 1 và x = −2', 'Vô nghiệm'], 0,
                'Giải x² − 3x + 2 = 0: Δ = 9 − 8 = 1, x = (3 ± 1)/2 nên x = 1 hoặc x = 2.');
            $this->quiz($L, 'Với a > 0 và Δ < 0, tam thức f(x) = ax² + bx + c như thế nào với mọi x?',
                ['Luôn dương với mọi x', 'Luôn âm với mọi x', 'Bằng 0 với mọi x', 'Đổi dấu tùy theo x'], 0,
                'Khi Δ < 0, tam thức không có nghiệm nên luôn cùng dấu với hệ số a; a > 0 thì luôn dương.', 'trung_binh');
            $this->quiz($L, 'Bất phương trình x² − 4 < 0 có tập nghiệm là?',
                ['(−2; 2)', '(−∞; −2) ∪ (2; +∞)', '[−2; 2]', '(−∞; 2)'], 0,
                'x² − 4 = 0 có hai nghiệm x = ±2; a = 1 > 0 nên f(x) < 0 giữa hai nghiệm, tức là −2 < x < 2.', 'trung_binh');
            $this->quiz($L, 'Tam thức f(x) = −x² + 5x − 6 mang dấu dương khi nào?',
                ['2 < x < 3', 'x < 2 hoặc x > 3', 'x ≤ 2', 'x ≥ 3'], 0,
                '−x² + 5x − 6 = 0 ⇔ x² − 5x + 6 = 0 có nghiệm x = 2, x = 3. a = −1 < 0 nên f(x) > 0 giữa hai nghiệm.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tam thức bậc hai với nghiệm của nó.',
                [['x² − 5x + 6', 'x = 2 và x = 3'], ['x² − 4x + 4', 'x = 2 (nghiệm kép)'],
                 ['x² + x − 2', 'x = −2 và x = 1'], ['x² − 9', 'x = −3 và x = 3']],
                'x² − 4x + 4 = (x − 2)² nên có nghiệm kép x = 2; x² − 9 = (x − 3)(x + 3).', 'trung_binh');
            $this->matching($L, 'Nối mỗi trường hợp của biệt thức Δ với số nghiệm của tam thức.',
                [['Δ > 0', 'Hai nghiệm phân biệt'], ['Δ = 0', 'Nghiệm kép'],
                 ['Δ < 0', 'Vô nghiệm'], ['Δ ≥ 0', 'Có nghiệm']],
                'Δ > 0: hai nghiệm phân biệt; Δ = 0: nghiệm kép x = −b/2a; Δ < 0: vô nghiệm.');
            $this->matching($L, 'Nối mỗi bất phương trình với tập nghiệm của nó.',
                [['x² < 9', '(−3; 3)'], ['x² ≥ 4', '(−∞; −2] ∪ [2; +∞)'],
                 ['(x − 1)² ≤ 0', '{1}'], ['x² + 1 > 0', 'ℝ (mọi số thực)']],
                '(x − 1)² ≤ 0 chỉ đúng khi x = 1; x² + 1 luôn dương nên nghiệm là mọi số thực.', 'trung_binh');
            $this->matching($L, 'Nối mỗi trường hợp của a và Δ với dấu của tam thức.',
                [['a > 0, Δ < 0', 'Luôn dương với mọi x'], ['a < 0, Δ < 0', 'Luôn âm với mọi x'],
                 ['a > 0, Δ > 0', 'Đổi dấu qua hai nghiệm'], ['a < 0, Δ > 0', 'Đổi dấu qua hai nghiệm']],
                'Δ < 0: cùng dấu với a với mọi x. Δ > 0: "trong trái, ngoài cùng" so với dấu của a.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tam thức vào nhóm theo số nghiệm của nó.',
                [['x² − 3x + 2', 'Hai nghiệm phân biệt'], ['x² − 2x + 1', 'Nghiệm kép'],
                 ['x² + 1', 'Vô nghiệm'], ['x² − 5x + 6', 'Hai nghiệm phân biệt'],
                 ['4x² − 4x + 1', 'Nghiệm kép'], ['x² + x + 1', 'Vô nghiệm']],
                'Tính Δ: x² − 2x + 1 = (x − 1)² có Δ = 0; x² + x + 1 có Δ = 1 − 4 < 0.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tam thức vào nhóm LUÔN DƯƠNG hoặc LUÔN ÂM với mọi x.',
                [['x² + 1', 'Luôn dương'], ['−x² − 2', 'Luôn âm'],
                 ['x² + 2x + 5', 'Luôn dương'], ['−x² + 2x − 5', 'Luôn âm'],
                 ['2x² + 3', 'Luôn dương'], ['−3x² − 1', 'Luôn âm']],
                'Các tam thức này đều có Δ < 0 nên luôn cùng dấu với a: x² + 2x + 5 có Δ = −16 < 0, a > 0.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tam thức vào nhóm CÓ NGHIỆM NGUYÊN hoặc NGHIỆM KHÔNG NGUYÊN.',
                [['x² − 4', 'Nghiệm nguyên'], ['x² − 2', 'Nghiệm không nguyên'],
                 ['x² − 5x + 6', 'Nghiệm nguyên'], ['x² − 7x + 12', 'Nghiệm nguyên'],
                 ['x² − 3', 'Nghiệm không nguyên'], ['x² − x − 6', 'Nghiệm nguyên']],
                'x² − 2 = 0 có nghiệm x = ±√2; x² − 3 = 0 có nghiệm x = ±√3 — đều không nguyên.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tam thức vào nhóm có hệ số a > 0 hoặc a < 0.',
                [['x² − 1', 'a > 0'], ['−2x² + 3x', 'a < 0'],
                 ['5x² − 2', 'a > 0'], ['−x² − x − 1', 'a < 0'],
                 ['3x² + x + 1', 'a > 0'], ['−4x² + 5', 'a < 0']],
                'a là hệ số của x² trong tam thức ax² + bx + c.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nghiệm của phương trình x² − 7x + 10 = 0 là x = 2 và x = ___ (điền số).', [[0, '5']],
                'Theo định lý Vi-ét: tổng hai nghiệm bằng 7, tích bằng 10, nên nghiệm còn lại là 5.', 'trung_binh');
            $this->fill($L, 'Biệt thức Δ của tam thức 2x² − 4x + 1 bằng ___ (điền số).', [[0, '8']],
                'Δ = b² − 4ac = 16 − 8 = 8.', 'trung_binh');
            $this->fill($L, 'Tập nghiệm của bất phương trình (x − 1)(x − 5) < 0 là khoảng (1; ___) (điền số).', [[0, '5']],
                'Tích hai thừa số âm khi x nằm giữa hai nghiệm 1 và 5.', 'trung_binh');
            $this->fill($L, 'Khi Δ = 0, tam thức bậc hai có nghiệm ___ (điền từ: kép/phân biệt).', [[0, 'kép']],
                'Δ = 0 cho nghiệm kép x = −b/2a.');
        }
    }

    // ================= LỚP 11 =================

    private function seedL11LuongGiac1(): void
    {
        $L = 'toan-thpt-lop-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Giá trị của sin 30° bằng bao nhiêu?',
                ['1/2', '√3/2', '√2/2', '1'], 0,
                'Góc đặc biệt: sin 30° = 1/2, cos 30° = √3/2.');
            $this->quiz($L, 'Giá trị của cos 60° bằng bao nhiêu?',
                ['1/2', '√3/2', '0', '1'], 0,
                'cos 60° = sin 30° = 1/2 (hai góc phụ nhau).');
            $this->quiz($L, 'Giá trị của tan 45° bằng bao nhiêu?',
                ['1', '√3', '1/√3', '0'], 0,
                'tan 45° = sin 45° / cos 45° = 1.');
            $this->quiz($L, 'Đẳng thức nào sau đây đúng với mọi góc α?',
                ['sin²α + cos²α = 1', 'sin²α − cos²α = 1', 'tan α · cot α = 0', 'sin 2α = sin α'], 0,
                'Công thức lượng giác cơ bản: sin²α + cos²α = 1 với mọi α.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi góc với giá trị sin của nó.',
                [['sin 0°', '0'], ['sin 90°', '1'], ['sin 180°', '0'], ['sin 30°', '1/2']],
                'sin 0° = sin 180° = 0; sin 90° = 1; sin 30° = 1/2.');
            $this->matching($L, 'Nối mỗi góc với giá trị cos của nó.',
                [['cos 0°', '1'], ['cos 90°', '0'], ['cos 180°', '−1'], ['cos 60°', '1/2']],
                'cos 0° = 1; cos 90° = 0; cos 180° = −1; cos 60° = 1/2.');
            $this->matching($L, 'Nối mỗi công thức với tên gọi của nó.',
                [['sin²α + cos²α = 1', 'Công thức cơ bản'],
                 ['tan α = sin α / cos α', 'Định nghĩa tan'],
                 ['1 + tan²α = 1/cos²α', 'Hệ quả'],
                 ['sin(π − α) = sin α', 'Công thức hai góc bù nhau']],
                '1 + tan²α = 1/cos²α suy ra từ công thức cơ bản khi chia hai vế cho cos²α.', 'trung_binh');
            $this->matching($L, 'Nối mỗi góc đặc biệt với giá trị tan của nó.',
                [['tan 0°', '0'], ['tan 45°', '1'], ['tan 60°', '√3'], ['tan 30°', '1/√3']],
                'tan 0° = 0; tan 30° = 1/√3; tan 45° = 1; tan 60° = √3.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi giá trị sin vào nhóm DƯƠNG hoặc ÂM.',
                [['sin 30°', 'sin dương'], ['sin 150°', 'sin dương'],
                 ['sin 210°', 'sin âm'], ['sin 330°', 'sin âm'],
                 ['sin 45°', 'sin dương'], ['sin 300°', 'sin âm']],
                'sin dương ở góc phần tư I và II, sin âm ở góc phần tư III và IV.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi giá trị lượng giác vào nhóm BẰNG 0, BẰNG 1 hoặc BẰNG −1.',
                [['sin 0°', 'Bằng 0'], ['cos 0°', 'Bằng 1'],
                 ['cos 180°', 'Bằng −1'], ['sin 180°', 'Bằng 0'],
                 ['sin 90°', 'Bằng 1'], ['cos 90°', 'Bằng 0']],
                'Nhớ các giá trị tại 0°, 90°, 180° của sin và cos.');
            $this->sortQ($L, 'Kéo mỗi góc (tính bằng độ) vào nhóm GÓC PHẦN TƯ I hoặc GÓC PHẦN TƯ II.',
                [['30°', 'Phần tư I'], ['120°', 'Phần tư II'],
                 ['45°', 'Phần tư I'], ['150°', 'Phần tư II'],
                 ['60°', 'Phần tư I'], ['135°', 'Phần tư II']],
                'Phần tư I: 0° đến 90°; phần tư II: 90° đến 180°.');
            $this->sortQ($L, 'Kéo mỗi đẳng thức lượng giác vào nhóm ĐÚNG hoặc SAI.',
                [['sin²α + cos²α = 1', 'Đúng'], ['tan α · cot α = 1', 'Đúng'],
                 ['sin 2α = 2 sin α', 'Sai'], ['cos(−α) = cos α', 'Đúng'],
                 ['sin(π + α) = sin α', 'Sai'], ['tan α = sin α / cos α', 'Đúng']],
                'sin 2α = 2 sin α cos α (không phải 2 sin α); sin(π + α) = −sin α.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'sin 90° + cos 0° = ___ (điền số).', [[0, '2']],
                'sin 90° = 1 và cos 0° = 1, tổng bằng 2.');
            $this->fill($L, 'Theo công thức lượng giác cơ bản, sin²α + cos²α = ___ (điền số).', [[0, '1']],
                'Đây là đẳng thức đúng với mọi góc α.');
            $this->fill($L, 'tan α có nghĩa khi cos α ___ 0 (điền từ: "bằng" hoặc "khác").', [[0, 'khác']],
                'Vì tan α = sin α / cos α nên mẫu số cos α phải khác 0.');
            $this->fill($L, 'Góc 180° tương ứng với ___ π radian (điền số).', [[0, '1']],
                '180° = π radian.', 'trung_binh');
        }
    }

    private function seedL11PtLuongGiac2(): void
    {
        $L = 'toan-thpt-lop-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nghiệm của phương trình sin x = 0 là họ nghiệm nào?',
                ['x = kπ (k ∈ ℤ)', 'x = π/2 + kπ (k ∈ ℤ)', 'x = k2π (k ∈ ℤ)', 'x = π + k2π (k ∈ ℤ)'], 0,
                'sin x = 0 tại các điểm x = 0, ±π, ±2π, ... tức là x = kπ với k nguyên.');
            $this->quiz($L, 'Nghiệm của phương trình cos x = 1 là họ nghiệm nào?',
                ['x = k2π (k ∈ ℤ)', 'x = kπ (k ∈ ℤ)', 'x = π/2 + k2π (k ∈ ℤ)', 'x = π + kπ (k ∈ ℤ)'], 0,
                'cos x = 1 tại x = 0, ±2π, ... tức là x = k2π với k nguyên.');
            $this->quiz($L, 'Phương trình tan x = 1 có họ nghiệm nào?',
                ['x = π/4 + kπ (k ∈ ℤ)', 'x = π/3 + kπ (k ∈ ℤ)', 'x = π/6 + k2π (k ∈ ℤ)', 'x = kπ (k ∈ ℤ)'], 0,
                'tan x = 1 khi x = π/4 + kπ, vì tan π/4 = 1 và tan có chu kỳ π.', 'trung_binh');
            $this->quiz($L, 'Phương trình sin x = 2 có nghiệm không?',
                ['Vô nghiệm', 'x = kπ (k ∈ ℤ)', 'x = π/2 + k2π (k ∈ ℤ)', 'Có vô số nghiệm'], 0,
                '|sin x| ≤ 1 với mọi x nên sin x = 2 không thể xảy ra — phương trình vô nghiệm.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phương trình lượng giác với họ nghiệm của nó.',
                [['sin x = 1', 'x = π/2 + k2π'], ['cos x = 0', 'x = π/2 + kπ'],
                 ['sin x = −1', 'x = −π/2 + k2π'], ['cos x = −1', 'x = π + k2π']],
                'sin x = 1 tại đỉnh trên của đường tròn lượng giác; cos x = 0 tại hai điểm trên trục tung.', 'trung_binh');
            $this->matching($L, 'Nối mỗi biểu thức với điều kiện xác định của nó.',
                [['tan x', 'x ≠ π/2 + kπ'], ['cot x', 'x ≠ kπ'],
                 ['1/sin x', 'x ≠ kπ'], ['1/cos x', 'x ≠ π/2 + kπ']],
                'tan x và 1/cos x không xác định khi cos x = 0; cot x và 1/sin x không xác định khi sin x = 0.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phương trình với số nghiệm của nó trên đoạn [0; 2π].',
                [['sin x = 0', '3 nghiệm'], ['cos x = 0', '2 nghiệm'],
                 ['tan x = 0', '3 nghiệm'], ['sin x = 1', '1 nghiệm']],
                'sin x = 0 tại x = 0, π, 2π; cos x = 0 tại x = π/2, 3π/2; sin x = 1 chỉ tại x = π/2.', 'trung_binh');
            $this->matching($L, 'Nối mỗi giá trị lượng giác đặc biệt với kết quả của nó.',
                [['sin π/6', '1/2'], ['cos π/3', '1/2'], ['tan π/4', '1'], ['sin π/3', '√3/2']],
                'π/6 = 30°, π/3 = 60°, π/4 = 45°: sin 30° = 1/2, sin 60° = √3/2.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phương trình vào nhóm CÓ NGHIỆM hoặc VÔ NGHIỆM.',
                [['sin x = 1/2', 'Có nghiệm'], ['cos x = 2', 'Vô nghiệm'],
                 ['tan x = 5', 'Có nghiệm'], ['sin x = −3', 'Vô nghiệm'],
                 ['cos x = −1/2', 'Có nghiệm'], ['cot x = 0', 'Có nghiệm']],
                '|sin x| ≤ 1 và |cos x| ≤ 1 nên sin x = −3 và cos x = 2 vô nghiệm; tan và cot nhận mọi giá trị thực.');
            $this->sortQ($L, 'Kéo mỗi họ nghiệm vào nhóm CHỨA kπ hoặc CHỨA k2π.',
                [['x = kπ', 'Chứa kπ'], ['x = π/2 + kπ', 'Chứa kπ'],
                 ['x = k2π', 'Chứa k2π'], ['x = π/3 + k2π', 'Chứa k2π'],
                 ['x = π/4 + kπ', 'Chứa kπ'], ['x = −π/2 + k2π', 'Chứa k2π']],
                'Nhìn vào phần chu kỳ trong họ nghiệm: kπ hay k2π.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi giá trị lượng giác vào nhóm DƯƠNG hoặc ÂM.',
                [['sin π/4', 'Dương'], ['cos 2π/3', 'Âm'],
                 ['tan π/6', 'Dương'], ['sin 5π/6', 'Dương'],
                 ['cos 3π/4', 'Âm'], ['tan 5π/4', 'Dương']],
                'cos 2π/3 = −1/2; cos 3π/4 = −√2/2; tan 5π/4 = tan 225° = 1.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi khẳng định về nghiệm vào nhóm ĐÚNG hoặc SAI.',
                [['sin x = 0 ⇔ x = kπ', 'Đúng'], ['cos x = 0 ⇔ x = k2π', 'Sai'],
                 ['tan x = 0 ⇔ x = kπ', 'Đúng'], ['sin x = 1 ⇔ x = k2π', 'Sai'],
                 ['cos x = 1 ⇔ x = k2π', 'Đúng'], ['cot x = 1 ⇔ x = π/4 + kπ', 'Đúng']],
                'cos x = 0 ⇔ x = π/2 + kπ; sin x = 1 ⇔ x = π/2 + k2π.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phương trình sin x = 0 có họ nghiệm x = k___ (điền ký hiệu góc).', [[0, 'π']],
                'sin x = 0 ⇔ x = kπ với k ∈ ℤ.');
            $this->fill($L, 'Phương trình cos x = −1 có họ nghiệm x = π + k___ (điền ký hiệu).', [[0, '2π']],
                'cos x = −1 ⇔ x = π + k2π với k ∈ ℤ.');
            $this->fill($L, 'Điều kiện xác định của tan x là x ≠ π/2 + k___ (điền ký hiệu).', [[0, 'π']],
                'tan x không xác định khi cos x = 0, tức là x = π/2 + kπ.');
            $this->fill($L, 'Phương trình sin x = 1/2 có một nghiệm là x = π/___ (điền số).', [[0, '6']],
                'sin π/6 = 1/2.', 'trung_binh');
        }
    }

    private function seedL11CapSoCong3(): void
    {
        $L = 'toan-thpt-lop-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cho cấp số cộng có u₁ = 3, công sai d = 2. Số hạng u₃ bằng bao nhiêu?',
                ['7', '5', '9', '6'], 0,
                'u₃ = u₁ + 2d = 3 + 4 = 7.');
            $this->quiz($L, 'Công thức số hạng tổng quát của cấp số cộng (uₙ) là công thức nào?',
                ['uₙ = u₁ + (n − 1)d', 'uₙ = u₁ + nd', 'uₙ = u₁ · dⁿ⁻¹', 'uₙ = u₁ + (n + 1)d'], 0,
                'Số hạng tổng quát: uₙ = u₁ + (n − 1)d với d là công sai.');
            $this->quiz($L, 'Dãy số nào sau đây là một cấp số cộng?',
                ['1; 3; 5; 7', '1; 2; 4; 8', '1; 4; 9; 16', '2; 5; 9; 14'], 0,
                'Dãy 1; 3; 5; 7 có hiệu hai số hạng liên tiếp luôn bằng 2 — là cấp số cộng với d = 2.');
            $this->quiz($L, 'Tổng 10 số hạng đầu của cấp số cộng có u₁ = 1, d = 1 bằng bao nhiêu?',
                ['55', '50', '45', '100'], 0,
                'Đây là tổng 1 + 2 + ... + 10 = 10·11/2 = 55.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cấp số cộng với công sai của nó.',
                [['1; 4; 7; 10', 'd = 3'], ['10; 7; 4; 1', 'd = −3'],
                 ['5; 5; 5; 5', 'd = 0'], ['2; 6; 10; 14', 'd = 4']],
                'Công sai d = uₙ₊₁ − uₙ: dãy không đổi có d = 0.');
            $this->matching($L, 'Nối mỗi bộ (u₁, d, n) với số hạng uₙ tương ứng.',
                [['u₁ = 2, d = 3, n = 4', 'u₄ = 11'], ['u₁ = 5, d = −2, n = 3', 'u₃ = 1'],
                 ['u₁ = 1, d = 5, n = 5', 'u₅ = 21'], ['u₁ = 0, d = 4, n = 6', 'u₆ = 20']],
                'Dùng uₙ = u₁ + (n − 1)d: 2 + 3·3 = 11; 5 + 2·(−2) = 1.', 'trung_binh');
            $this->matching($L, 'Nối mỗi công thức với ý nghĩa của nó.',
                [['uₙ = u₁ + (n − 1)d', 'Số hạng tổng quát'],
                 ['Sₙ = n(u₁ + uₙ)/2', 'Tổng n số hạng đầu'],
                 ['d = uₙ₊₁ − uₙ', 'Công sai'],
                 ['uₖ = (uₖ₋₁ + uₖ₊₁)/2', 'Tính chất ba số hạng liên tiếp']],
                'Mỗi số hạng (trừ hai đầu) bằng trung bình cộng của hai số hạng kề nó.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tổng với giá trị của nó.',
                [['1 + 2 + ... + 10', '55'], ['2 + 4 + ... + 20', '110'],
                 ['5 + 5 + ... + 5 (10 số hạng)', '50'], ['1 + 3 + ... + 19', '100']],
                '2 + 4 + ... + 20 = 2(1 + ... + 10) = 110; 1 + 3 + ... + 19 có 10 số hạng, tổng = 10·(1+19)/2 = 100.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm LÀ CẤP SỐ CỘNG hoặc KHÔNG PHẢI CẤP SỐ CỘNG.',
                [['2; 5; 8; 11', 'Là cấp số cộng'], ['1; 2; 4; 7', 'Không phải cấp số cộng'],
                 ['3; 3; 3; 3', 'Là cấp số cộng'], ['1; 4; 9; 16', 'Không phải cấp số cộng'],
                 ['−2; 0; 2; 4', 'Là cấp số cộng'], ['5; 10; 20; 40', 'Không phải cấp số cộng']],
                'Cấp số cộng có hiệu hai số hạng liên tiếp không đổi. Dãy 3; 3; 3; 3 là cấp số cộng với d = 0.');
            $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm theo dấu của công sai.',
                [['1; 3; 5', 'Công sai dương'], ['9; 6; 3', 'Công sai âm'],
                 ['4; 4; 4', 'Công sai bằng 0'], ['−5; −2; 1', 'Công sai dương'],
                 ['10; 8; 6', 'Công sai âm'], ['0; 0; 0', 'Công sai bằng 0']],
                'd > 0: dãy tăng; d < 0: dãy giảm; d = 0: dãy không đổi.');
            $this->sortQ($L, 'Kéo mỗi dãy số (cho bởi số hạng tổng quát) vào nhóm DÃY TĂNG, DÃY GIẢM hoặc DÃY KHÔNG ĐỔI.',
                [['uₙ = 2n + 1', 'Dãy tăng'], ['uₙ = 10 − 3n', 'Dãy giảm'],
                 ['uₙ = 7', 'Dãy không đổi'], ['uₙ = n + 5', 'Dãy tăng'],
                 ['uₙ = 20 − n', 'Dãy giảm'], ['uₙ = −3', 'Dãy không đổi']],
                'uₙ = an + b: a > 0 thì tăng, a < 0 thì giảm, a = 0 thì không đổi.', 'trung_binh');
            $this->sortQ($L, 'Xét cấp số cộng 2; 5; 8; 11; ... Kéo mỗi số vào nhóm THUỘC hoặc KHÔNG THUỘC dãy.',
                [['2', 'Thuộc dãy'], ['5', 'Thuộc dãy'],
                 ['9', 'Không thuộc dãy'], ['14', 'Thuộc dãy'],
                 ['7', 'Không thuộc dãy'], ['20', 'Thuộc dãy']],
                'Các số hạng có dạng 2 + 3k: 14 = 2 + 3·4, 20 = 2 + 3·6; 9 và 7 không viết được dưới dạng này.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cấp số cộng có u₁ = 4, d = 3. Số hạng u₅ = ___ (điền số).', [[0, '16']],
                'u₅ = u₁ + 4d = 4 + 12 = 16.', 'trung_binh');
            $this->fill($L, 'Công thức tổng n số hạng đầu của cấp số cộng: Sₙ = n(u₁ + uₙ)/___ (điền số).', [[0, '2']],
                'Sₙ = n(u₁ + uₙ)/2.');
            $this->fill($L, 'Dãy 7; 4; 1; −2; ... có công sai d = ___ (điền số).', [[0, '−3']],
                'd = 4 − 7 = −3.');
            $this->fill($L, 'Nếu ba số a, b, c lập thành cấp số cộng thì b = (a + c)/___ (điền số).', [[0, '2']],
                'Số hạng ở giữa bằng trung bình cộng của hai số hạng kề nó.', 'trung_binh');
        }
    }

    private function seedL11CapSoNhan4(): void
    {
        $L = 'toan-thpt-lop-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cho cấp số nhân có u₁ = 2, công bội q = 3. Số hạng u₃ bằng bao nhiêu?',
                ['18', '12', '6', '54'], 0,
                'u₃ = u₁ · q² = 2 · 9 = 18.');
            $this->quiz($L, 'Công thức số hạng tổng quát của cấp số nhân (uₙ) là công thức nào?',
                ['uₙ = u₁ · qⁿ⁻¹', 'uₙ = u₁ + (n − 1)q', 'uₙ = u₁ · qⁿ', 'uₙ = u₁ · nq'], 0,
                'Số hạng tổng quát: uₙ = u₁ · qⁿ⁻¹ với q là công bội.');
            $this->quiz($L, 'Dãy số nào sau đây là một cấp số nhân?',
                ['3; 6; 12; 24', '2; 4; 6; 8', '1; 3; 5; 7', '5; 10; 15; 20'], 0,
                'Dãy 3; 6; 12; 24 có tỉ số hai số hạng liên tiếp luôn bằng 2 — là cấp số nhân với q = 2.');
            $this->quiz($L, 'Tổng 4 số hạng đầu của cấp số nhân có u₁ = 1, q = 2 bằng bao nhiêu?',
                ['15', '7', '31', '8'], 0,
                '1 + 2 + 4 + 8 = 15. Dùng công thức: S₄ = 1·(2⁴ − 1)/(2 − 1) = 15.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cấp số nhân với công bội của nó.',
                [['2; 6; 18', 'q = 3'], ['8; 4; 2', 'q = 1/2'],
                 ['5; −10; 20', 'q = −2'], ['3; 3; 3', 'q = 1']],
                'Công bội q = uₙ₊₁ / uₙ: dãy 8; 4; 2 có q = 1/2.', 'trung_binh');
            $this->matching($L, 'Nối mỗi bộ (u₁, q, n) với số hạng uₙ tương ứng.',
                [['u₁ = 3, q = 2, n = 4', 'u₄ = 24'], ['u₁ = 5, q = −1, n = 3', 'u₃ = 5'],
                 ['u₁ = 1, q = 3, n = 3', 'u₃ = 9'], ['u₁ = 4, q = 1/2, n = 3', 'u₃ = 1']],
                'Dùng uₙ = u₁·qⁿ⁻¹: 3·2³ = 24; 5·(−1)² = 5; 4·(1/2)² = 1.', 'trung_binh');
            $this->matching($L, 'Nối mỗi công thức với ý nghĩa của nó.',
                [['uₙ = u₁·qⁿ⁻¹', 'Số hạng tổng quát'],
                 ['Sₙ = u₁(qⁿ − 1)/(q − 1)', 'Tổng n số hạng đầu'],
                 ['q = uₙ₊₁/uₙ', 'Công bội'],
                 ['uₖ² = uₖ₋₁·uₖ₊₁', 'Tính chất ba số hạng liên tiếp']],
                'Mỗi số hạng bằng trung bình nhân của hai số hạng kề nó (với các số dương).', 'trung_binh');
            $this->matching($L, 'Nối mỗi tổng với giá trị của nó.',
                [['1 + 2 + 4 + 8', '15'], ['3 + 6 + 12', '21'],
                 ['10 + 10 + 10', '30'], ['1 + 3 + 9 + 27', '40']],
                '1 + 3 + 9 + 27 = (3⁴ − 1)/(3 − 1) = 80/2 = 40.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm LÀ CẤP SỐ NHÂN hoặc KHÔNG PHẢI CẤP SỐ NHÂN.',
                [['2; 4; 8; 16', 'Là cấp số nhân'], ['1; 3; 5; 7', 'Không phải cấp số nhân'],
                 ['5; 5; 5; 5', 'Là cấp số nhân'], ['2; 6; 12; 24', 'Không phải cấp số nhân'],
                 ['1; −2; 4; −8', 'Là cấp số nhân'], ['3; 9; 18; 36', 'Không phải cấp số nhân']],
                'Cấp số nhân có tỉ số hai số hạng liên tiếp không đổi. Dãy 5; 5; 5; 5 là cấp số nhân với q = 1.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm CÔNG BỘI DƯƠNG hoặc CÔNG BỘI ÂM.',
                [['2; 6; 18', 'Công bội dương'], ['4; −8; 16', 'Công bội âm'],
                 ['1; 2; 4', 'Công bội dương'], ['−3; 6; −12', 'Công bội âm'],
                 ['5; 10; 20', 'Công bội dương'], ['2; −4; 8', 'Công bội âm']],
                'q < 0: các số hạng đổi dấu liên tiếp; q > 0: các số hạng cùng dấu.');
            $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm CÓ CÔNG BỘI q = 2 hoặc q ≠ 2.',
                [['3; 6; 12', 'q = 2'], ['1; 2; 4; 8', 'q = 2'],
                 ['5; 15; 45', 'q ≠ 2'], ['2; 4; 8', 'q = 2'],
                 ['4; 12; 36', 'q ≠ 2'], ['7; 14; 28', 'q = 2']],
                'Tính q = u₂/u₁: dãy 5; 15; 45 có q = 3.', 'trung_binh');
            $this->sortQ($L, 'Xét cấp số nhân 3; 6; 12; 24; ... Kéo mỗi số vào nhóm THUỘC hoặc KHÔNG THUỘC dãy.',
                [['3', 'Thuộc dãy'], ['12', 'Thuộc dãy'],
                 ['48', 'Thuộc dãy'], ['18', 'Không thuộc dãy'],
                 ['96', 'Thuộc dãy'], ['36', 'Không thuộc dãy']],
                'Các số hạng có dạng 3·2ᵏ: 48 = 3·2⁴, 96 = 3·2⁵; 18 và 36 không viết được dưới dạng này.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cấp số nhân có u₁ = 5, q = 2. Số hạng u₄ = ___ (điền số).', [[0, '40']],
                'u₄ = u₁·q³ = 5·8 = 40.', 'trung_binh');
            $this->fill($L, 'Dãy 2; −6; 18; −54; ... có công bội q = ___ (điền số).', [[0, '−3']],
                'q = −6/2 = −3.', 'trung_binh');
            $this->fill($L, 'Nếu ba số dương a, b, c lập thành cấp số nhân thì b² = a · ___ (điền chữ).', [[0, 'c']],
                'Tính chất: bình phương số hạng ở giữa bằng tích hai số hạng kề nó.', 'trung_binh');
            $this->fill($L, 'Tổng S₃ của cấp số nhân có u₁ = 2, q = 3 là ___ (điền số).', [[0, '26']],
                '2 + 6 + 18 = 26.', 'trung_binh');
        }
    }

    // ================= LỚP 12 =================

    private function seedL12DaoHam1(): void
    {
        $L = 'toan-thpt-lop-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đạo hàm của hàm số y = x³ bằng bao nhiêu?',
                ['3x²', 'x²', '3x', 'x³/3'], 0,
                "Dùng công thức (xⁿ)' = n·xⁿ⁻¹ với n = 3: (x³)' = 3x².");
            $this->quiz($L, 'Đạo hàm của hàm số y = √x (với x > 0) bằng bao nhiêu?',
                ['1/(2√x)', '2√x', '1/√x', '√x/2'], 0,
                "Công thức: (√x)' = 1/(2√x) với x > 0.", 'trung_binh');
            $this->quiz($L, 'Đạo hàm của hàm hằng y = 5 bằng bao nhiêu?',
                ['0', '5', '1', '5x'], 0,
                'Đạo hàm của mọi hàm hằng đều bằng 0.');
            $this->quiz($L, 'Đạo hàm của hàm số y = 1/x (với x ≠ 0) bằng bao nhiêu?',
                ['−1/x²', '1/x²', '−1/x', 'ln|x|'], 0,
                "Viết y = x⁻¹, dùng (xⁿ)' = n·xⁿ⁻¹ với n = −1: y' = −x⁻² = −1/x².", 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hàm số với đạo hàm của nó.',
                [['y = x²', "y' = 2x"], ['y = x⁴', "y' = 4x³"],
                 ['y = 5x', "y' = 5"], ['y = x', "y' = 1"]],
                "Dùng (xⁿ)' = n·xⁿ⁻¹: (x⁴)' = 4x³; (5x)' = 5.");
            $this->matching($L, 'Nối mỗi hàm số lượng giác – mũ – logarit với đạo hàm của nó.',
                [['y = sin x', "y' = cos x"], ['y = cos x', "y' = −sin x"],
                 ['y = e^x', "y' = e^x"], ['y = ln x', "y' = 1/x"]],
                '(sin x)' . "'" . ' = cos x; (cos x)' . "'" . ' = −sin x; (eˣ)' . "'" . ' = eˣ; (ln x)' . "'" . ' = 1/x.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hàm số với đạo hàm của nó.',
                [['y = x⁵', "y' = 5x⁴"], ['y = 1/x²', "y' = −2/x³"],
                 ['y = √x', "y' = 1/(2√x)"], ['y = 2x³', "y' = 6x²"]],
                'Viết 1/x² = x⁻² nên đạo hàm là −2x⁻³ = −2/x³.', 'trung_binh');
            $this->matching($L, 'Nối mỗi ký hiệu đạo hàm với ý nghĩa của nó.',
                [["y'", 'Đạo hàm của y theo x'], ["f'(x)", 'Đạo hàm của f tại điểm x'],
                 ["y''", 'Đạo hàm cấp hai của y'], ['dy/dx', 'Đạo hàm của y theo x']],
                "y', f'(x), dy/dx đều chỉ đạo hàm cấp một; y'' là đạo hàm cấp hai.");
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm ĐẠO HÀM BẰNG 0 hoặc ĐẠO HÀM KHÁC 0.',
                [['y = 7', 'Đạo hàm bằng 0'], ['y = π', 'Đạo hàm bằng 0'],
                 ['y = x', 'Đạo hàm khác 0'], ['y = −3', 'Đạo hàm bằng 0'],
                 ['y = 2x', 'Đạo hàm khác 0'], ['y = 100', 'Đạo hàm bằng 0']],
                'Hàm hằng (kể cả hằng số π) có đạo hàm bằng 0.');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm ĐẠO HÀM LÀ ĐA THỨC hoặc ĐẠO HÀM KHÔNG PHẢI ĐA THỨC.',
                [['y = x³', 'Đa thức'], ['y = 2x² + x', 'Đa thức'],
                 ['y = 1/x', 'Không phải đa thức'], ['y = sin x', 'Không phải đa thức'],
                 ['y = x⁵ − 3x', 'Đa thức'], ['y = √x', 'Không phải đa thức']],
                'Đạo hàm của đa thức vẫn là đa thức; đạo hàm của 1/x là −1/x², của sin x là cos x.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm CÓ ĐẠO HÀM TẠI x = 0 hoặc KHÔNG CÓ ĐẠO HÀM TẠI x = 0.',
                [['y = x²', 'Có đạo hàm tại x = 0'], ['y = x³', 'Có đạo hàm tại x = 0'],
                 ['y = |x|', 'Không có đạo hàm tại x = 0'], ['y = 1/x', 'Không có đạo hàm tại x = 0'],
                 ['y = sin x', 'Có đạo hàm tại x = 0'], ['y = √x', 'Không có đạo hàm tại x = 0']],
                'y = |x| có đồ thị "gãy" tại 0; y = 1/x và y = √x không xác định hoặc không trơn tại 0.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đạo hàm vào nhóm CHỨA BIẾN x hoặc LÀ HẰNG SỐ.',
                [["y' = 2x", 'Chứa biến x'], ["y' = 0", 'Là hằng số'],
                 ["y' = cos x", 'Chứa biến x'], ["y' = 5", 'Là hằng số'],
                 ["y' = 3x²", 'Chứa biến x'], ["y' = −sin x", 'Chứa biến x']],
                'Đạo hàm của hàm bậc nhất ax + b là hằng số a; đạo hàm của hàm hằng là 0.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đạo hàm của y = 4x³ là y' . "'" . ' = 12x^___ (điền số mũ).', [[0, '2']],
                "(4x³)' = 12x².");
            $this->fill($L, 'Đạo hàm của mọi hàm hằng luôn bằng ___ (điền số).', [[0, '0']],
                'Vì hàm hằng không thay đổi nên tốc độ biến thiên bằng 0.');
            $this->fill($L, 'Nếu y = x⁷ thì y' . "'" . ' = 7x^___ (điền số mũ).', [[0, '6']],
                "Dùng (xⁿ)' = n·xⁿ⁻¹ với n = 7.");
            $this->fill($L, 'Đạo hàm của y = sin x là y' . "'" . ' = ___ x (điền "sin" hoặc "cos").', [[0, 'cos']],
                "(sin x)' = cos x.", 'trung_binh');
        }
    }

    private function seedL12QuyTacDaoHam2(): void
    {
        $L = 'toan-thpt-lop-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đạo hàm của hàm số y = x² + 3x bằng bao nhiêu?',
                ['2x + 3', '2x', 'x² + 3', '2x + 3x'], 0,
                "Dùng quy tắc đạo hàm của tổng: (x²)' + (3x)' = 2x + 3.");
            $this->quiz($L, 'Đạo hàm của hàm số y = x · sin x bằng bao nhiêu?',
                ['sin x + x·cos x', 'cos x', 'sin x − x·cos x', 'x·cos x'], 0,
                "Dùng quy tắc đạo hàm của tích: (x·sin x)' = 1·sin x + x·cos x.", 'trung_binh');
            $this->quiz($L, 'Đạo hàm của hàm số y = (2x + 1)² bằng bao nhiêu?',
                ['4(2x + 1)', '2(2x + 1)', '4x + 1', '2x + 1'], 0,
                "Dùng quy tắc hàm hợp: [(2x+1)²]' = 2(2x+1)·2 = 4(2x+1).", 'trung_binh');
            $this->quiz($L, 'Đạo hàm của hàm số y = (x² + 1)/(x − 1) tại x = 0 bằng bao nhiêu?',
                ['−1', '1', '0', '2'], 0,
                "y' = [2x(x−1) − (x²+1)]/(x−1)². Tại x = 0: y' = (0 − 1)/1 = −1.", 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hàm số với đạo hàm của nó (quy tắc tổng – hiệu).',
                [['y = x³ + x²', "y' = 3x² + 2x"], ['y = 5x² − 2x', "y' = 10x − 2"],
                 ['y = x⁴ − 3x', "y' = 4x³ − 3"], ['y = 2x + 7', "y' = 2"]],
                "(u ± v)' = u' ± v': đạo hàm từng số hạng rồi cộng/trừ lại.");
            $this->matching($L, 'Nối mỗi hàm số với đạo hàm của nó (quy tắc tích).',
                [['y = x·e^x', "y' = e^x + x·e^x"],
                 ['y = x²·sin x', "y' = 2x·sin x + x²·cos x"],
                 ['y = x·ln x', "y' = ln x + 1"],
                 ['y = 2x·cos x', "y' = 2cos x − 2x·sin x"]],
                "(u·v)' = u'v + uv': với y = x·ln x thì y' = 1·ln x + x·(1/x) = ln x + 1.", 'trung_binh');
            $this->matching($L, 'Nối mỗi hàm hợp với đạo hàm của nó.',
                [['y = (3x + 1)²', "y' = 6(3x + 1)"], ['y = sin 2x', "y' = 2cos 2x"],
                 ['y = e^(3x)', "y' = 3e^(3x)"], ['y = (x² + 1)³', "y' = 6x(x² + 1)²"]],
                "Đạo hàm hàm hợp: nhân thêm đạo hàm của hàm trong, ví dụ (sin 2x)' = 2cos 2x.", 'trung_binh');
            $this->matching($L, 'Nối mỗi quy tắc đạo hàm với công thức của nó.',
                [["(u + v)'", "u' + v'"], ["(u·v)'", "u'v + uv'"],
                 ["(u/v)'", "(u'v − uv')/v²"], ['Đạo hàm hàm hợp', "y'_x = y'_u · u'_x"]],
                'Thuộc lòng bốn công thức này để tính nhanh đạo hàm.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm theo QUY TẮC ĐẠO HÀM cần dùng.',
                [['y = x² + sin x', 'Quy tắc tổng'], ['y = x·e^x', 'Quy tắc tích'],
                 ['y = (x + 1)/(x − 1)', 'Quy tắc thương'], ['y = 3x² − 5x + 1', 'Quy tắc tổng'],
                 ['y = x²·ln x', 'Quy tắc tích'], ['y = sin x / x', 'Quy tắc thương']],
                'Tổng/hiệu: đạo hàm từng phần; tích: u' . "'" . 'v + uv' . "'" . '; thương: (u' . "'" . 'v − uv' . "'" . ')/v².', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đẳng thức đạo hàm vào nhóm ĐÚNG hoặc SAI.',
                [["(x²)' = 2x", 'Đúng'], ["(sin x)' = −cos x", 'Sai'],
                 ["(e^x)' = e^x", 'Đúng'], ["(ln x)' = 1/x²", 'Sai'],
                 ["(x³)' = 3x²", 'Đúng'], ["(cos x)' = sin x", 'Sai']],
                "(sin x)' = cos x; (ln x)' = 1/x; (cos x)' = −sin x.");
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm HÀM HỢP hoặc KHÔNG PHẢI HÀM HỢP.',
                [['y = sin 2x', 'Hàm hợp'], ['y = (x + 1)³', 'Hàm hợp'],
                 ['y = x² + 1', 'Không phải hàm hợp'], ['y = e^(x²)', 'Hàm hợp'],
                 ['y = 2x + 3', 'Không phải hàm hợp'], ['y = √(x + 1)', 'Hàm hợp']],
                'Hàm hợp là hàm số của một hàm số khác, ví dụ sin(2x) là hàm sin của hàm 2x.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm theo ĐẶC ĐIỂM CỦA ĐẠO HÀM của nó.',
                [['y = x³', 'Bậc giảm đi 1'], ['y = sin x', 'Không phải đa thức'],
                 ['y = x² + 1', 'Bậc giảm đi 1'], ['y = e^x', 'Dạng giữ nguyên'],
                 ['y = x⁵', 'Bậc giảm đi 1'], ['y = ln x', 'Không phải đa thức']],
                'Đạo hàm đa thức bậc n là đa thức bậc n − 1; (eˣ)' . "'" . ' = eˣ giữ nguyên dạng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, "Quy tắc đạo hàm của tích: (u·v)' = u'v + u___ (điền ký hiệu).", [[0, "v'"]],
                "(u·v)' = u'v + uv'.");
            $this->fill($L, 'Đạo hàm của y = (x + 2)² là y' . "'" . ' = 2(x + ___) (điền số).', [[0, '2']],
                "Dùng quy tắc hàm hợp: [(x+2)²]' = 2(x+2)·1 = 2(x+2).", 'trung_binh');
            $this->fill($L, 'Nếu y = x²·e^x thì y' . "'" . ' = e^x(2x + x^___) (điền số mũ).', [[0, '2']],
                "y' = 2x·e^x + x²·e^x = e^x(2x + x²).", 'trung_binh');
            $this->fill($L, "Quy tắc đạo hàm của thương: (u/v)' = (u'v − uv')/___ (điền mẫu số).", [[0, 'v²']],
                "(u/v)' = (u'v − uv')/v² với v ≠ 0.", 'trung_binh');
        }
    }

    private function seedL12DonDieuCucTri3(): void
    {
        $L = 'toan-thpt-lop-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, "Cho hàm số có đạo hàm y' = 2x − 4. Hàm số đồng biến trên khoảng nào?",
                ['(2; +∞)', '(−∞; 2)', '(−∞; +∞)', '(0; 2)'], 0,
                "y' > 0 ⇔ 2x − 4 > 0 ⇔ x > 2, nên hàm số đồng biến trên (2; +∞).", 'trung_binh');
            $this->quiz($L, 'Hàm số y = x³ − 3x đạt cực đại tại điểm nào?',
                ['x = −1', 'x = 1', 'x = 0', 'x = 3'], 0,
                "y' = 3x² − 3 = 0 ⇔ x = ±1. y'' = 6x; tại x = −1 thì y'' = −6 < 0 nên x = −1 là điểm cực đại.", 'trung_binh');
            $this->quiz($L, 'Hàm số y = −x² + 4x − 3 nghịch biến trên khoảng nào?',
                ['(2; +∞)', '(−∞; 2)', '(−∞; +∞)', '(0; 2)'], 0,
                "y' = −2x + 4 < 0 ⇔ x > 2, nên hàm số nghịch biến trên (2; +∞).", 'trung_binh');
            $this->quiz($L, 'Giá trị cực tiểu của hàm số y = x² − 2x + 5 bằng bao nhiêu?',
                ['4', '5', '1', '0'], 0,
                'Parabol mở lên trên, đạt cực tiểu tại đỉnh x = 1: y = 1 − 2 + 5 = 4.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hàm số với khoảng đồng biến của nó.',
                [['y = 2x + 1', '(−∞; +∞)'], ['y = x²', '(0; +∞)'],
                 ['y = −3x + 2', 'Không có khoảng đồng biến'], ['y = x³', '(−∞; +∞)']],
                "y = x³ có y' = 3x² ≥ 0 nên đồng biến trên ℝ; y = −3x + 2 luôn nghịch biến.", 'trung_binh');
            $this->matching($L, 'Nối mỗi hàm số với số điểm cực trị của nó.',
                [['y = x³ − 3x', '2 điểm cực trị'], ['y = x² + 1', '1 điểm cực trị'],
                 ['y = x³', '0 điểm cực trị'], ['y = −x⁴ + 2x²', '3 điểm cực trị']],
                "y = −x⁴ + 2x² có y' = −4x³ + 4x = 0 tại x = 0, ±1 — ba điểm cực trị.", 'trung_binh');
            $this->matching($L, 'Nối mỗi trường hợp của dấu đạo hàm với kết luận về hàm số.',
                [["y' > 0", 'Hàm số đồng biến'], ["y' < 0", 'Hàm số nghịch biến'],
                 ["y' = 0", 'Điểm dừng (nghi ngờ cực trị)'], ["y' đổi dấu từ + sang −", 'Điểm cực đại']],
                'Dấu của đạo hàm quyết định tính đơn điệu; sự đổi dấu của y' . "'" . ' quyết định cực trị.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hàm số với giá trị cực đại của nó.',
                [['y = −x² + 2x + 3', 'Cực đại y = 4 tại x = 1'],
                 ['y = −2x² + 8x', 'Cực đại y = 8 tại x = 2'],
                 ['y = −x² − 1', 'Cực đại y = −1 tại x = 0'],
                 ['y = −(x − 3)² + 5', 'Cực đại y = 5 tại x = 3']],
                'Parabol mở xuống dưới đạt cực đại tại đỉnh: y = −2x² + 8x có đỉnh (2; 8).', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm ĐỒNG BIẾN hoặc NGHỊCH BIẾN trên ℝ.',
                [['y = 3x + 1', 'Đồng biến trên ℝ'], ['y = −2x + 5', 'Nghịch biến trên ℝ'],
                 ['y = x³', 'Đồng biến trên ℝ'], ['y = −x³', 'Nghịch biến trên ℝ'],
                 ['y = 5x − 2', 'Đồng biến trên ℝ'], ['y = −x + 7', 'Nghịch biến trên ℝ']],
                'Hàm bậc nhất y = ax + b: a > 0 đồng biến, a < 0 nghịch biến trên ℝ.');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm theo loại cực trị của nó.',
                [['y = −x²', 'Có cực đại'], ['y = x²', 'Có cực tiểu'],
                 ['y = x³', 'Không có cực trị'], ['y = −x³ + 3x', 'Có cực đại'],
                 ['y = 2x + 1', 'Không có cực trị'], ['y = x² − 4x', 'Có cực tiểu']],
                "y = −x³ + 3x có y' = −3x² + 3 đổi dấu từ + sang − tại x = 1 nên có cực đại.", 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi điểm tới hạn vào nhóm ĐIỂM CỰC ĐẠI hoặc ĐIỂM CỰC TIỂU.',
                [['x = 0 của y = x³ − 3x²', 'Điểm cực đại'], ['x = 2 của y = x³ − 3x²', 'Điểm cực tiểu'],
                 ['x = 0 của y = −x³ + 3x²', 'Điểm cực tiểu'], ['x = 2 của y = −x³ + 3x²', 'Điểm cực đại'],
                 ['x = −1 của y = x³ − 3x', 'Điểm cực đại'], ['x = 1 của y = x³ − 3x', 'Điểm cực tiểu']],
                "Xét dấu y': với y = x³ − 3x², y' = 3x(x − 2) đổi dấu + sang − tại x = 0 (cực đại), − sang + tại x = 2 (cực tiểu).", 'trung_binh');
            $this->sortQ($L, "Kéo mỗi hàm số vào nhóm có y'(0) DƯƠNG hoặc y'(0) ÂM.",
                [['y = x² + x', "y'(0) dương"], ['y = x² − x', "y'(0) âm"],
                 ['y = 3x + x²', "y'(0) dương"], ['y = −2x + x³', "y'(0) âm"],
                 ['y = x + 5', "y'(0) dương"], ['y = −x − 1', "y'(0) âm"]],
                "Tính y' rồi thay x = 0: với y = x² + x thì y' = 2x + 1, y'(0) = 1 > 0.", 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, "Hàm số đồng biến trên khoảng (a; b) khi y' ___ 0 trên khoảng đó (điền dấu).", [[0, '>']],
                "y' > 0 thì hàm số đồng biến; y' < 0 thì nghịch biến.");
            $this->fill($L, "Điểm mà tại đó y' đổi dấu từ âm sang dương là điểm cực ___ (điền từ).", [[0, 'tiểu']],
                'Đổi dấu − sang +: cực tiểu; đổi dấu + sang −: cực đại.', 'trung_binh');
            $this->fill($L, 'Hàm số y = x³ − 3x² đạt cực đại tại x = ___ (điền số).', [[0, '0']],
                "y' = 3x² − 6x = 3x(x − 2); y' đổi dấu từ + sang − tại x = 0 nên cực đại tại x = 0.", 'trung_binh');
            $this->fill($L, 'Giá trị cực tiểu của hàm số y = x² − 4x + 7 là ___ (điền số).', [[0, '3']],
                'Đỉnh parabol tại x = 2, giá trị cực tiểu y = 4 − 8 + 7 = 3.', 'trung_binh');
        }
    }

    private function seedL12GtlnGtnn4(): void
    {
        $L = 'toan-thpt-lop-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Giá trị lớn nhất của hàm số y = −x² + 4x trên đoạn [0; 3] bằng bao nhiêu?',
                ['4', '3', '0', '5'], 0,
                'Đỉnh tại x = 2 ∈ [0; 3], y(2) = 4; so với y(0) = 0, y(3) = 3 nên GTLN là 4.', 'trung_binh');
            $this->quiz($L, 'Giá trị nhỏ nhất của hàm số y = x² − 2x + 3 trên đoạn [0; 2] bằng bao nhiêu?',
                ['2', '3', '1', '4'], 0,
                'Đỉnh tại x = 1 ∈ [0; 2], y(1) = 2; so với y(0) = y(2) = 3 nên GTNN là 2.', 'trung_binh');
            $this->quiz($L, 'Giá trị lớn nhất của hàm số y = sin x trên ℝ bằng bao nhiêu?',
                ['1', '0', 'π', '−1'], 0,
                '−1 ≤ sin x ≤ 1 với mọi x nên GTLN của sin x là 1 (đạt tại x = π/2 + k2π).');
            $this->quiz($L, 'Giá trị nhỏ nhất của hàm số y = x + 1/x với x > 0 bằng bao nhiêu?',
                ['2', '1', '0', '4'], 0,
                'Theo bất đẳng thức Cô-si: x + 1/x ≥ 2√(x·1/x) = 2, dấu bằng khi x = 1.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hàm số (trên đoạn cho trước) với giá trị lớn nhất của nó.',
                [['y = x² trên [0; 2]', 'GTLN = 4'], ['y = −x² + 1 trên [−1; 1]', 'GTLN = 1'],
                 ['y = 2x + 1 trên [0; 3]', 'GTLN = 7'], ['y = x³ trên [−1; 1]', 'GTLN = 1']],
                'Hàm đồng biến trên đoạn thì GTLN tại đầu phải: y = 2x + 1 trên [0; 3] đạt 7 tại x = 3.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hàm số (trên đoạn cho trước) với giá trị nhỏ nhất của nó.',
                [['y = x² trên [−2; 1]', 'GTNN = 0'], ['y = x² − 4 trên [0; 3]', 'GTNN = −4'],
                 ['y = (x − 1)² trên [0; 2]', 'GTNN = 0'], ['y = 2x − 5 trên [1; 4]', 'GTNN = −3']],
                'y = x² − 4 trên [0; 3] nhỏ nhất tại x = 0 (bằng −4); y = 2x − 5 đồng biến nên nhỏ nhất tại x = 1.', 'trung_binh');
            $this->matching($L, 'Nối mỗi ký hiệu với ý nghĩa của nó.',
                [['max', 'Giá trị lớn nhất'], ['min', 'Giá trị nhỏ nhất'],
                 ['GTLN', 'Giá trị lớn nhất'], ['GTNN', 'Giá trị nhỏ nhất']],
                'GTLN (max) và GTNN (min) là hai khái niệm cơ bản khi khảo sát hàm số.');
            $this->matching($L, 'Nối mỗi hàm số (trên đoạn cho trước) với điểm đạt giá trị lớn nhất.',
                [['y = −x² + 4x trên [0; 3]', 'x = 2'],
                 ['y = x² − 2x trên [0; 2]', 'x = 0 hoặc x = 2'],
                 ['y = 3x + 1 trên [1; 2]', 'x = 2'],
                 ['y = −2x + 5 trên [0; 4]', 'x = 0']],
                'y = x² − 2x trên [0; 2] có giá trị 0 tại cả hai đầu mút x = 0 và x = 2 — cùng là GTLN.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm GTLN ĐẠT TẠI ĐIỂM TRONG hoặc TẠI ĐẦU MÚT của đoạn.',
                [['y = −x² + 4x trên [0; 3]', 'Đạt tại điểm trong'],
                 ['y = 2x + 1 trên [0; 3]', 'Đạt tại đầu mút'],
                 ['y = −x² + 2x + 1 trên [0; 2]', 'Đạt tại điểm trong'],
                 ['y = −3x + 2 trên [1; 4]', 'Đạt tại đầu mút'],
                 ['y = sin x trên [0; π]', 'Đạt tại điểm trong'],
                 ['y = x³ trên [0; 1]', 'Đạt tại đầu mút']],
                'Hàm bậc nhất đơn điệu nên GTLN tại đầu mút; y = −x² + 2x + 1 đạt cực đại tại x = 1 (điểm trong).', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm có GTLN DƯƠNG hoặc GTLN ÂM.',
                [['y = −x² + 1', 'GTLN dương'], ['y = −x² − 2', 'GTLN âm'],
                 ['y = sin x', 'GTLN dương'], ['y = −e^x', 'GTLN âm'],
                 ['y = −(x − 1)² + 4', 'GTLN dương'], ['y = −x² − x − 5', 'GTLN âm']],
                'y = −e^x < 0 với mọi x nên GTLN cũng âm (tiến tới 0 nhưng không đạt).', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm CÓ CẢ GTLN VÀ GTNN hoặc KHÔNG CÓ GTLN.',
                [['y = x² trên [0; 1]', 'Có cả GTLN và GTNN'],
                 ['y = 1/x trên (0; 1)', 'Không có GTLN'],
                 ['y = sin x trên [0; 2π]', 'Có cả GTLN và GTNN'],
                 ['y = x trên (0; +∞)', 'Không có GTLN'],
                 ['y = −x² trên [−1; 1]', 'Có cả GTLN và GTNN'],
                 ['y = e^x trên (0; 1)', 'Không có GTLN']],
                'Hàm liên tục trên đoạn đóng [a; b] luôn có GTLN và GTNN; trên khoảng mở có thể không đạt được.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm có GTNN BẰNG 0 hoặc GTNN KHÁC 0.',
                [['y = x²', 'GTNN bằng 0'], ['y = (x − 2)²', 'GTNN bằng 0'],
                 ['y = x² + 1', 'GTNN khác 0'], ['y = |x|', 'GTNN bằng 0'],
                 ['y = 2x² + 3', 'GTNN khác 0'], ['y = (x + 1)² − 2', 'GTNN khác 0']],
                'y = (x + 1)² − 2 có GTNN bằng −2 tại x = −1.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'GTLN của hàm số y = −x² + 6x − 5 trên ℝ bằng ___ (điền số).', [[0, '4']],
                'Đỉnh tại x = 3, GTLN y = −9 + 18 − 5 = 4.', 'trung_binh');
            $this->fill($L, 'Trên đoạn [a; b], để tìm GTLN của hàm số liên tục ta so sánh giá trị tại các điểm tới hạn và tại hai ___ (điền từ).', [[0, 'đầu mút']],
                'So sánh f tại các điểm tới hạn trong (a; b) với f(a) và f(b).', 'trung_binh');
            $this->fill($L, 'GTNN của hàm số y = x² − 6x + 10 trên ℝ bằng ___ (điền số).', [[0, '1']],
                'Đỉnh tại x = 3, GTNN y = 9 − 18 + 10 = 1.', 'trung_binh');
            $this->fill($L, 'Với x > 0, theo bất đẳng thức Cô-si, x + 1/x ≥ ___ (điền số).', [[0, '2']],
                'x + 1/x ≥ 2√(x · 1/x) = 2, dấu bằng khi x = 1.', 'trung_binh');
        }
    }
}
