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
 * Seeder môn Giáo dục thể chất (tự chạy độc lập được):
 * subject → 3 topics → 6 skills → 6 bài học → mỗi bài 12 câu
 * (3 quiz + 3 matching + 3 sort + 3 fill), tất cả is_demo = true.
 * Nội dung TIẾNG VIỆT TỰ VIẾT 100%, bám chương trình GDTC lớp 6–9.
 */
class SubjectGdtcSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(['slug' => 'gdtc'], [
            'name' => 'Giáo dục thể chất',
            'icon' => '⚽',
            'color' => '#22c55e',
            'description' => 'Vận động an toàn: luật thể thao cơ bản và rèn luyện sức khoẻ.',
            'sort_order' => 13,
            'is_published' => true,
            'is_demo' => true,
        ]);

        $order = 1;
        foreach ($this->tree() as $t) {
            $topic = Topic::updateOrCreate(['slug' => $t['slug']], [
                'subject_id' => $subject->id,
                'name' => $t['name'],
                'description' => $t['description'],
                'icon' => $t['icon'],
                'sort_order' => $order++,
                'grade_min' => $t['grade_min'],
                'grade_max' => $t['grade_max'],
                'is_published' => true,
                'is_demo' => true,
            ]);
            foreach ($t['skills'] as $si => $s) {
                $skill = Skill::updateOrCreate(['slug' => $s['slug']], [
                    'topic_id' => $topic->id,
                    'name' => $s['name'],
                    'description' => $s['description'],
                    'sort_order' => $si + 1,
                    'is_demo' => true,
                ]);
                foreach ($s['lessons'] as $li => $l) {
                    Lesson::updateOrCreate(['slug' => $l['slug']], [
                        'skill_id' => $skill->id,
                        'title' => $l['title'],
                        'objective' => $l['objective'],
                        'difficulty' => $l['difficulty'],
                        'duration_minutes' => $l['duration_minutes'],
                        'instructions' => $l['instructions'],
                        'sort_order' => $li + 1,
                        'status' => 'published',
                        'is_demo' => true,
                    ]);
                }
            }
        }

        $this->seedQuestions();
    }

    private function tree(): array
    {
        return [
            [
                'name' => 'Khởi động và an toàn vận động', 'slug' => 'tt-van-dong-an-toan', 'icon' => '🏃',
                'description' => 'Khởi động đúng cách và giữ an toàn khi vận động.',
                'grade_min' => 6, 'grade_max' => 7,
                'skills' => [
                    [
                        'name' => 'Khởi động trước khi tập', 'slug' => 'kn-tt-khoi-dong',
                        'description' => 'Biết các động tác khởi động và tác dụng của chúng.',
                        'lessons' => [
                            [
                                'title' => 'Khởi động trước khi tập', 'slug' => 'tt-khoi-dong-truoc-khi-tap',
                                'objective' => 'Thực hiện đúng các động tác khởi động cơ bản và hiểu tác dụng của việc khởi động.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Khởi động là bước không thể bỏ qua trước mỗi buổi tập. Hãy học kỹ từng động tác nhé.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'An toàn khi vận động', 'slug' => 'kn-tt-an-toan',
                        'description' => 'Biết cách giữ an toàn: trang phục, giày, uống nước, xử lý khi mệt.',
                        'lessons' => [
                            [
                                'title' => 'An toàn khi vận động', 'slug' => 'tt-an-toan-khi-van-dong',
                                'objective' => 'Biết chọn trang phục, giày phù hợp; uống nước đúng cách và xử lý khi cơ thể mệt.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'An toàn là trên hết! Hãy ghi nhớ những quy tắc đơn giản để mỗi buổi tập đều vui và khỏe.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Luật thể thao cơ bản', 'slug' => 'tt-luat-the-thao', 'icon' => '⚽',
                'description' => 'Luật cơ bản của bóng đá và bóng rổ.',
                'grade_min' => 7, 'grade_max' => 8,
                'skills' => [
                    [
                        'name' => 'Luật bóng đá cơ bản', 'slug' => 'kn-tt-bong-da',
                        'description' => 'Số cầu thủ, thời gian thi đấu, việt vị và các quả đá phạt.',
                        'lessons' => [
                            [
                                'title' => 'Luật bóng đá cơ bản', 'slug' => 'tt-luat-bong-da-co-ban',
                                'objective' => 'Nắm được số cầu thủ, thời gian thi đấu, lỗi việt vị đơn giản và các quả đá phạt.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Bóng đá là môn thể thao vua. Nắm chắc luật cơ bản sẽ giúp em chơi hay và xem bóng đá thú vị hơn.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Luật bóng rổ cơ bản', 'slug' => 'kn-tt-bong-ro',
                        'description' => 'Số cầu thủ, cách tính điểm và các lỗi cơ bản trong bóng rổ.',
                        'lessons' => [
                            [
                                'title' => 'Luật bóng rổ cơ bản', 'slug' => 'tt-luat-bong-ro-co-ban',
                                'objective' => 'Nắm được số cầu thủ, cách tính điểm và lỗi chạy bước trong bóng rổ.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Bóng rổ là môn thể thao đồng đội hấp dẫn. Hãy tìm hiểu luật chơi cơ bản nhé.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Rèn luyện sức khoẻ', 'slug' => 'tt-suc-khoe-ve-sinh', 'icon' => '💪',
                'description' => 'Rèn luyện sức khoẻ hằng ngày và vệ sinh cá nhân khi tập luyện.',
                'grade_min' => 8, 'grade_max' => 9,
                'skills' => [
                    [
                        'name' => 'Rèn luyện sức khoẻ', 'slug' => 'kn-tt-ren-luyen',
                        'description' => 'Thói quen tập luyện, ăn uống, ngủ nghỉ để cơ thể khoẻ mạnh.',
                        'lessons' => [
                            [
                                'title' => 'Rèn luyện sức khoẻ hằng ngày', 'slug' => 'tt-ren-luyen-suc-khoe',
                                'objective' => 'Xây dựng thói quen tập luyện, ăn uống và ngủ nghỉ hợp lý để nâng cao sức khoẻ.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Sức khoẻ là vốn quý nhất. Hãy học cách chăm sóc cơ thể mình mỗi ngày nhé.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Vệ sinh cá nhân khi tập luyện', 'slug' => 'kn-tt-ve-sinh',
                        'description' => 'Giữ vệ sinh cơ thể, quần áo và dụng cụ tập luyện.',
                        'lessons' => [
                            [
                                'title' => 'Vệ sinh cá nhân khi tập luyện', 'slug' => 'tt-ve-sinh-ca-nhan-khi-tap',
                                'objective' => 'Biết giữ vệ sinh cơ thể, quần áo và dụng cụ sau khi tập luyện.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Tập luyện ra nhiều mồ hôi nên vệ sinh cá nhân rất quan trọng. Cùng tìm hiểu nhé.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ---------------- helpers ----------------

    private function lesson(string $slug): Lesson
    {
        return $this->lessonBySlug[$slug] ??= Lesson::where('slug', $slug)->firstOrFail();
    }

    /** Mỗi kiểu chơi của một bài chỉ seed một lần (chạy lại an toàn). */
    private function alreadySeeded(string $lessonSlug, string $gameType): bool
    {
        $lesson = $this->lesson($lessonSlug);
        return Question::where('lesson_id', $lesson->id)->where('game_type', $gameType)->exists();
    }

    private function newQuestion(string $lessonSlug, string $gameType, string $prompt, string $explanation, string $difficulty = 'de'): Question
    {
        $lesson = $this->lesson($lessonSlug);
        $this->orderByLesson[$lessonSlug] = ($this->orderByLesson[$lessonSlug] ?? 0) + 1;

        return Question::create([
            'lesson_id' => $lesson->id,
            'game_type' => $gameType,
            'prompt' => $prompt,
            'explanation' => $explanation,
            'difficulty' => $difficulty,
            'points' => 10,
            'sort_order' => $this->orderByLesson[$lessonSlug],
            'is_demo' => true,
        ]);
    }

    /** $items: [prompt, options[4], correctIndex, explanation, difficulty?] */
    private function quiz(string $lessonSlug, array $items): void
    {
        if ($this->alreadySeeded($lessonSlug, 'quiz')) {
            return;
        }
        foreach ($items as $item) {
            [$prompt, $options, $correct, $explanation] = $item;
            $difficulty = $item[4] ?? 'de';
            $q = $this->newQuestion($lessonSlug, 'quiz', $prompt, $explanation, $difficulty ?? 'de');
            foreach ($options as $i => $text) {
                QuestionOption::create([
                    'question_id' => $q->id, 'option_text' => $text,
                    'is_correct' => $i === $correct, 'sort_order' => $i + 1,
                ]);
            }
        }
    }

    /** $items: [prompt, [[left, right]...], explanation, difficulty?] */
    private function matching(string $lessonSlug, array $items): void
    {
        if ($this->alreadySeeded($lessonSlug, 'matching')) {
            return;
        }
        foreach ($items as $item) {
            [$prompt, $pairs, $explanation] = $item;
            $difficulty = $item[3] ?? 'de';
            $q = $this->newQuestion($lessonSlug, 'matching', $prompt, $explanation, $difficulty ?? 'de');
            foreach ($pairs as $i => [$left, $right]) {
                MatchingPair::create([
                    'question_id' => $q->id, 'left_text' => $left, 'right_text' => $right, 'sort_order' => $i + 1,
                ]);
            }
        }
    }

    /** $items: [prompt, [[text, category]...], explanation, difficulty?] */
    private function sortQ(string $lessonSlug, array $items): void
    {
        if ($this->alreadySeeded($lessonSlug, 'sort')) {
            return;
        }
        foreach ($items as $item) {
            [$prompt, $entries, $explanation] = $item;
            $difficulty = $item[3] ?? 'de';
            $q = $this->newQuestion($lessonSlug, 'sort', $prompt, $explanation, $difficulty ?? 'de');
            foreach ($entries as $i => [$text, $category]) {
                SortItem::create([
                    'question_id' => $q->id, 'item_text' => $text, 'category' => $category, 'sort_order' => $i + 1,
                ]);
            }
        }
    }

    /** $items: [prompt, [[blank_index, answer_text]...], explanation, difficulty?] — prompt phải chứa "___". */
    private function fill(string $lessonSlug, array $items): void
    {
        if ($this->alreadySeeded($lessonSlug, 'fill')) {
            return;
        }
        foreach ($items as $item) {
            [$prompt, $answers, $explanation] = $item;
            $difficulty = $item[3] ?? 'de';
            $q = $this->newQuestion($lessonSlug, 'fill', $prompt, $explanation, $difficulty ?? 'de');
            foreach ($answers as $i => [$blankIndex, $text]) {
                FillAnswer::create([
                    'question_id' => $q->id, 'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1,
                ]);
            }
        }
    }

    // ================= CÂU HỎI =================

    private function seedQuestions(): void
    {
        // ---------- Bài 1: Khởi động trước khi tập ----------
        $this->quiz('tt-khoi-dong-truoc-khi-tap', [
            ['Vì sao phải khởi động trước khi tập thể thao?',
                ['Để làm nóng cơ thể, tránh chấn thương', 'Để khoe với bạn bè', 'Để đỡ phải tập chính', 'Để tốn thời gian'], 0,
                'Khởi động làm nóng cơ, tăng linh hoạt khớp, giúp tránh chấn thương khi vận động mạnh.'],
            ['Thời gian khởi động phù hợp trước mỗi buổi tập là bao lâu?',
                ['5–10 phút', '1 phút', '30 phút', 'Không cần khởi động'], 0,
                'Khởi động khoảng 5–10 phút là đủ để cơ thể sẵn sàng.'],
            ['Động tác nào sau đây thuộc phần khởi động?',
                ['Xoay khớp cổ, vai, gối', 'Chạy nước rút 100m', 'Nâng tạ nặng', 'Nhảy xa hết sức'], 0,
                'Khởi động gồm các động tác nhẹ nhàng như xoay khớp, chạy nhẹ tại chỗ.'],
        ]);
        $this->matching('tt-khoi-dong-truoc-khi-tap', [
            ['Nối mỗi động tác khởi động với khớp được tác động.',
                [['Xoay cổ', 'Khớp cổ'], ['Xoay vai', 'Khớp vai'], ['Xoay gối', 'Khớp gối'], ['Xoay hông', 'Khớp hông']],
                'Mỗi động tác xoay tác động trực tiếp lên khớp cùng tên: cổ, vai, gối, hông.'],
            ['Nối mỗi động tác với tác dụng.',
                [['Chạy nhẹ tại chỗ', 'Làm nóng toàn thân'], ['Ép dọc', 'Giãn cơ chân'], ['Vươn vai', 'Giãn cơ thân trên']],
                'Chạy nhẹ làm nóng toàn thân, ép dọc giãn cơ chân, vươn vai giãn cơ thân trên.'],
            ['Nối mỗi thứ tự với động tác phù hợp.',
                [['Đầu tiên', 'Xoay các khớp từ trên xuống dưới'], ['Tiếp theo', 'Chạy nhẹ, ép dẻo'], ['Cuối cùng', 'Thả lỏng, hít thở sâu']],
                'Khởi động theo trình tự: xoay khớp → vận động nhẹ → thả lỏng.'],
        ]);
        $this->sortQ('tt-khoi-dong-truoc-khi-tap', [
            ['Xếp các động tác vào nhóm: Nên làm khi khởi động / Không nên làm.',
                [['Xoay khớp cổ tay', 'Nên làm khi khởi động'], ['Chạy nhẹ tại chỗ', 'Nên làm khi khởi động'], ['Nâng tạ nặng ngay', 'Không nên làm'], ['Chạy nước rút hết sức', 'Không nên làm']],
                'Khởi động chỉ gồm động tác nhẹ nhàng; tạ nặng và nước rút để dành cho phần tập chính.'],
            ['Xếp các động tác vào nhóm: Khởi động chung / Khởi động chuyên môn.',
                [['Chạy nhẹ quanh sân', 'Khởi động chung'], ['Xoay các khớp', 'Khởi động chung'], ['Tập phát bóng (trước giờ bóng chuyền)', 'Khởi động chuyên môn'], ['Tập sút bóng (trước giờ bóng đá)', 'Khởi động chuyên môn']],
                'Khởi động chung cho mọi môn; khởi động chuyên môn mô phỏng động tác của môn sắp tập.'],
            ['Xếp các việc làm vào nhóm: Đúng / Sai khi khởi động.',
                [['Khởi động 5–10 phút', 'Đúng'], ['Xoay khớp nhẹ nhàng', 'Đúng'], ['Bỏ qua khởi động để tập luôn', 'Sai'], ['Khởi động qua loa 30 giây', 'Sai']],
                'Phải khởi động đủ 5–10 phút, nhẹ nhàng; bỏ qua hoặc làm qua loa đều sai.'],
        ]);
        $this->fill('tt-khoi-dong-truoc-khi-tap', [
            ['Trước khi tập thể thao, cần ___ từ 5 đến 10 phút.',
                [[0, 'khởi động']], 'Khởi động 5–10 phút trước mỗi buổi tập.'],
            ['Khởi động giúp làm nóng cơ thể và tránh ___.',
                [[0, 'chấn thương']], 'Khởi động kỹ giúp tránh chấn thương khi vận động mạnh.'],
            ['Khi khởi động, nên xoay các ___ từ trên xuống dưới một cách nhẹ nhàng.',
                [[0, 'khớp']], 'Xoay các khớp từ trên xuống dưới: cổ, vai, khuỷu tay, hông, gối, cổ chân.'],
        ]);

        // ---------- Bài 2: An toàn khi vận động ----------
        $this->quiz('tt-an-toan-khi-van-dong', [
            ['Khi tập thể thao, nên đi giày như thế nào?',
                ['Giày thể thao vừa chân, đế mềm', 'Dép lê cho thoáng', 'Giày cao gót', 'Đi chân đất trên sân bê tông'], 0,
                'Giày thể thao vừa chân, đế mềm giúp bảo vệ bàn chân và khớp gối.'],
            ['Khi đang tập mà thấy chóng mặt, mệt lả, em nên làm gì?',
                ['Dừng tập, ngồi nghỉ và báo thầy cô', 'Cố tập tiếp cho hết giờ', 'Chạy nhanh hơn', 'Nín thở'], 0,
                'Chóng mặt là dấu hiệu cơ thể quá tải; phải dừng lại nghỉ ngơi và báo thầy cô ngay.'],
            ['Uống nước khi tập thể thao như thế nào là đúng?',
                ['Uống từng ngụm nhỏ, nhiều lần', 'Uống ừng ực thật nhiều một lúc', 'Không uống gì cả', 'Chỉ uống nước ngọt có ga'], 0,
                'Nên uống từng ngụm nhỏ nhiều lần; uống quá nhiều một lúc dễ đau bụng.'],
        ]);
        $this->matching('tt-an-toan-khi-van-dong', [
            ['Nối mỗi tình huống với cách xử lý đúng.',
                [['Bị chuột rút', 'Dừng lại, kéo giãn nhẹ vùng cơ bị rút'], ['Bị trầy xước nhẹ', 'Rửa sạch, sát trùng'], ['Thấy chóng mặt', 'Ngồi nghỉ, báo thầy cô']],
                'Chuột rút thì kéo giãn nhẹ; trầy xước thì rửa sạch; chóng mặt thì nghỉ và báo thầy cô.'],
            ['Nối mỗi môn thể thao với trang phục phù hợp.',
                [['Bơi lội', 'Đồ bơi, kính bơi'], ['Bóng đá', 'Giày đinh, tất dài'], ['Chạy bộ', 'Giày chạy nhẹ, quần áo thấm mồ hôi']],
                'Mỗi môn có trang phục riêng phù hợp để vận động an toàn, thoải mái.'],
            ['Nối mỗi thói quen với đánh giá.',
                [['Mang giày thể thao khi tập', 'Thói quen tốt'], ['Uống đủ nước khi tập', 'Thói quen tốt'], ['Tập ngay sau khi ăn no', 'Thói quen xấu']],
                'Mang giày thể thao và uống đủ nước là thói quen tốt; tập ngay sau khi ăn no có hại.'],
        ]);
        $this->sortQ('tt-an-toan-khi-van-dong', [
            ['Xếp các việc làm vào nhóm: An toàn / Nguy hiểm.',
                [['Đi giày thể thao vừa chân', 'An toàn'], ['Khởi động trước khi tập', 'An toàn'], ['Tập ngay sau khi ăn no', 'Nguy hiểm'], ['Chơi đùa xô đẩy khi tập', 'Nguy hiểm']],
                'Giày phù hợp và khởi động là an toàn; tập khi no và xô đẩy là nguy hiểm.'],
            ['Xếp các việc làm vào nhóm: Nên làm / Không nên làm khi bị thương nhẹ.',
                [['Báo thầy cô', 'Nên làm'], ['Rửa sạch vết thương', 'Nên làm'], ['Giấu không nói với ai', 'Không nên làm'], ['Tiếp tục tập như bình thường', 'Không nên làm']],
                'Bị thương phải báo thầy cô và xử lý vết thương, không được giấu hay tập tiếp.'],
            ['Xếp các loại đồ uống vào nhóm: Nên uống khi tập / Nên hạn chế.',
                [['Nước lọc', 'Nên uống khi tập'], ['Nước oresol khi mất nhiều mồ hôi', 'Nên uống khi tập'], ['Nước ngọt có ga', 'Nên hạn chế'], ['Trà sữa', 'Nên hạn chế']],
                'Khi tập nên uống nước lọc; nước ngọt có ga và trà sữa nên hạn chế.'],
        ]);
        $this->fill('tt-an-toan-khi-van-dong', [
            ['Khi tập thể thao, nên đi ___ vừa chân, đế mềm.',
                [[0, 'giày thể thao']], 'Giày thể thao vừa chân, đế mềm bảo vệ bàn chân và khớp gối.'],
            ['Không nên tập thể dục ngay sau khi ___.',
                [[0, 'ăn no']], 'Tập ngay sau khi ăn no dễ đau bụng, ảnh hưởng tiêu hóa.'],
            ['Khi bị thương, cần ___ cho thầy cô hoặc người lớn biết ngay.',
                [[0, 'báo']], 'Bị thương phải báo ngay cho thầy cô hoặc người lớn để được giúp đỡ.'],
        ]);

        // ---------- Bài 3: Luật bóng đá cơ bản ----------
        $this->quiz('tt-luat-bong-da-co-ban', [
            ['Mỗi đội bóng đá khi thi đấu chính thức có bao nhiêu cầu thủ trên sân?',
                ['11 cầu thủ', '7 cầu thủ', '5 cầu thủ', '9 cầu thủ'], 0,
                'Mỗi đội bóng đá có 11 cầu thủ trên sân, trong đó có 1 thủ môn.', 'trung_binh'],
            ['Một trận bóng đá chính thức gồm mấy hiệp, mỗi hiệp bao lâu?',
                ['2 hiệp, mỗi hiệp 45 phút', '4 hiệp, mỗi hiệp 15 phút', '1 hiệp 90 phút', '2 hiệp, mỗi hiệp 30 phút'], 0,
                'Trận đấu gồm 2 hiệp, mỗi hiệp 45 phút, nghỉ giữa hiệp 15 phút.', 'trung_binh'],
            ['Quả đá phạt đền (penalty) được thực hiện cách khung thành bao xa?',
                ['11 mét', '5 mét', '16 mét', '20 mét'], 0,
                'Chấm phạt đền cách khung thành 11m; cầu thủ đá đối mặt trực tiếp với thủ môn.', 'kho'],
        ]);
        $this->matching('tt-luat-bong-da-co-ban', [
            ['Nối mỗi thuật ngữ với ý nghĩa.',
                [['Việt vị', 'Cầu thủ tấn công đứng sau hậu vệ cuối khi nhận bóng'], ['Phạt góc', 'Đá từ góc sân khi bóng hết biên ngang do đội phòng ngự chạm cuối'], ['Ném biên', 'Ném bóng vào sân khi bóng hết biên dọc']],
                'Việt vị, phạt góc, ném biên là ba thuật ngữ cơ bản cần nhớ trong bóng đá.', 'trung_binh'],
            ['Nối mỗi lỗi với hình phạt.',
                [['Chơi bóng bằng tay (trừ thủ môn)', 'Đá phạt cho đối phương'], ['Phạm lỗi trong vòng cấm', 'Phạt đền 11m'], ['Lỗi nhẹ', 'Đá phạt trực tiếp hoặc gián tiếp']],
                'Chơi tay bị đá phạt, phạm lỗi trong vòng cấm bị phạt đền 11m.', 'kho'],
            ['Nối mỗi vị trí với nhiệm vụ chính.',
                [['Thủ môn', 'Bảo vệ khung thành'], ['Hậu vệ', 'Ngăn cản đối phương ghi bàn'], ['Tiền đạo', 'Ghi bàn vào lưới đối phương']],
                'Thủ môn giữ khung thành, hậu vệ phòng ngự, tiền đạo ghi bàn.', 'trung_binh'],
        ]);
        $this->sortQ('tt-luat-bong-da-co-ban', [
            ['Xếp các hành vi vào nhóm: Đúng luật / Phạm luật.',
                [['Sút bóng bằng chân', 'Đúng luật'], ['Tranh bóng fair-play', 'Đúng luật'], ['Dùng tay chơi bóng (không phải thủ môn)', 'Phạm luật'], ['Kéo áo đối phương', 'Phạm luật']],
                'Sút bóng bằng chân và tranh bóng fair-play là đúng luật; dùng tay và kéo áo là phạm luật.', 'trung_binh'],
            ['Xếp các tình huống vào nhóm: Đá phạt / Không phải đá phạt.',
                [['Phạt đền 11m', 'Đá phạt'], ['Đá phạt trực tiếp', 'Đá phạt'], ['Giao bóng giữa sân', 'Không phải đá phạt'], ['Ném biên', 'Không phải đá phạt']],
                'Phạt đền và đá phạt trực tiếp là các quả đá phạt; giao bóng và ném biên thì không.', 'kho'],
            ['Xếp các tình huống vào nhóm: Được công nhận bàn thắng / Không được công nhận.',
                [['Bóng qua vạch vôi khung thành', 'Được công nhận bàn thắng'], ['Sút phạt đền thành công', 'Được công nhận bàn thắng'], ['Bóng chạm tay rồi vào lưới', 'Không được công nhận'], ['Bóng chưa qua hết vạch vôi', 'Không được công nhận']],
                'Bàn thắng chỉ được công nhận khi bóng qua hết vạch vôi và không phạm luật.', 'kho'],
        ]);
        $this->fill('tt-luat-bong-da-co-ban', [
            ['Mỗi đội bóng đá có ___ cầu thủ trên sân khi thi đấu.',
                [[0, '11']], 'Mỗi đội bóng đá có 11 cầu thủ trên sân.', 'trung_binh'],
            ['Cầu thủ bị thổi phạt ___ khi đứng sau hậu vệ cuối cùng của đối phương lúc nhận bóng tấn công.',
                [[0, 'việt vị']], 'Đứng sau hậu vệ cuối khi nhận bóng tấn công là lỗi việt vị.', 'kho'],
            ['Quả phạt đền được đá từ chấm cách khung thành ___ mét.',
                [[0, '11']], 'Chấm phạt đền cách khung thành 11 mét.', 'kho'],
        ]);

        // ---------- Bài 4: Luật bóng rổ cơ bản ----------
        $this->quiz('tt-luat-bong-ro-co-ban', [
            ['Mỗi đội bóng rổ khi thi đấu có bao nhiêu cầu thủ trên sân?',
                ['5 cầu thủ', '11 cầu thủ', '7 cầu thủ', '3 cầu thủ'], 0,
                'Mỗi đội bóng rổ có 5 cầu thủ thi đấu trên sân.', 'trung_binh'],
            ['Cú ném rổ thành công từ ngoài vạch 3 điểm được tính mấy điểm?',
                ['3 điểm', '2 điểm', '1 điểm', '4 điểm'], 0,
                'Ném trong vạch được 2 điểm, ngoài vạch 3 điểm được 3 điểm, ném phạt được 1 điểm.', 'trung_binh'],
            ['Lỗi "chạy bước" trong bóng rổ là gì?',
                ['Ôm bóng chạy quá 2 bước mà không dẫn bóng', 'Chạy quá nhanh', 'Chạy sai hướng', 'Chạy ra ngoài sân'], 0,
                'Cầu thủ ôm bóng chỉ được đi tối đa 2 bước; muốn di chuyển tiếp phải dẫn bóng.', 'kho'],
        ]);
        $this->matching('tt-luat-bong-ro-co-ban', [
            ['Nối mỗi cách ghi điểm với số điểm.',
                [['Ném rổ trong vạch', '2 điểm'], ['Ném rổ ngoài vạch 3 điểm', '3 điểm'], ['Ném phạt thành công', '1 điểm']],
                'Ném trong vạch 2 điểm, ngoài vạch 3 điểm 3 điểm, ném phạt 1 điểm.', 'trung_binh'],
            ['Nối mỗi lỗi với mô tả.',
                [['Chạy bước', 'Ôm bóng đi quá 2 bước'], ['Hai lần dẫn bóng', 'Dừng dẫn bóng rồi lại dẫn tiếp'], ['Dẫn bóng hai tay', 'Dùng hai tay ôm bóng khi dẫn']],
                'Chạy bước, hai lần dẫn bóng và dẫn bóng hai tay đều là lỗi trong bóng rổ.', 'kho'],
            ['Nối mỗi vị trí với nhiệm vụ.',
                [['Hậu vệ dẫn bóng', 'Tổ chức tấn công'], ['Tiền phong', 'Ghi điểm từ nhiều vị trí'], ['Trung phong', 'Tranh bóng bật bảng, bảo vệ rổ']],
                'Hậu vệ tổ chức tấn công, tiền phong ghi điểm, trung phong tranh bóng bật bảng.', 'kho'],
        ]);
        $this->sortQ('tt-luat-bong-ro-co-ban', [
            ['Xếp các hành vi vào nhóm: Đúng luật / Phạm luật.',
                [['Dẫn bóng bằng một tay', 'Đúng luật'], ['Ném rổ', 'Đúng luật'], ['Ôm bóng chạy 5 bước', 'Phạm luật'], ['Đẩy đối phương', 'Phạm luật']],
                'Dẫn bóng một tay và ném rổ là đúng luật; ôm bóng chạy nhiều bước và đẩy người là phạm luật.', 'trung_binh'],
            ['Xếp các cú ném vào nhóm theo số điểm.',
                [['Ném phạt', '1 điểm'], ['Ném rổ trong vạch', '2 điểm'], ['Ném xa ngoài vạch 3 điểm', '3 điểm']],
                'Ném phạt 1 điểm, ném trong vạch 2 điểm, ném ngoài vạch 3 điểm 3 điểm.', 'trung_binh'],
            ['Xếp các tình huống vào nhóm: Được tính điểm / Không được tính điểm.',
                [['Bóng lọt qua rổ từ trên xuống', 'Được tính điểm'], ['Ném phạt thành công', 'Được tính điểm'], ['Bóng chạm vành rồi bật ra ngoài', 'Không được tính điểm'], ['Ném khi đã hết giờ', 'Không được tính điểm']],
                'Chỉ khi bóng lọt qua rổ trong thời gian thi đấu mới được tính điểm.', 'kho'],
        ]);
        $this->fill('tt-luat-bong-ro-co-ban', [
            ['Mỗi đội bóng rổ có ___ cầu thủ thi đấu trên sân.',
                [[0, '5']], 'Mỗi đội bóng rổ có 5 cầu thủ trên sân.', 'trung_binh'],
            ['Ôm bóng chạy quá 2 bước mà không dẫn bóng là lỗi ___.',
                [[0, 'chạy bước']], 'Ôm bóng đi quá 2 bước là lỗi chạy bước.', 'kho'],
            ['Cú ném phạt thành công được tính ___ điểm.',
                [[0, '1']], 'Mỗi cú ném phạt thành công được 1 điểm.', 'trung_binh'],
        ]);

        // ---------- Bài 5: Rèn luyện sức khoẻ hằng ngày ----------
        $this->quiz('tt-ren-luyen-suc-khoe', [
            ['Mỗi ngày học sinh nên vận động thể chất ít nhất bao lâu?',
                ['60 phút', '5 phút', '10 phút', 'Không cần vận động'], 0,
                'Học sinh nên vận động ít nhất 60 phút mỗi ngày với các hoạt động vừa sức.'],
            ['Giấc ngủ đủ cho học sinh mỗi đêm là bao lâu?',
                ['8–9 tiếng', '4–5 tiếng', '12 tiếng', 'Thức khuya không sao'], 0,
                'Học sinh cần ngủ 8–9 tiếng mỗi đêm để cơ thể phát triển và tỉnh táo học tập.'],
            ['Bữa ăn nào quan trọng nhất, không nên bỏ?',
                ['Bữa sáng', 'Bữa khuya', 'Bữa ăn vặt', 'Bữa nào cũng bỏ được'], 0,
                'Bữa sáng cung cấp năng lượng cho cả buổi học nên tuyệt đối không bỏ.'],
        ]);
        $this->matching('tt-ren-luyen-suc-khoe', [
            ['Nối mỗi thói quen với lợi ích.',
                [['Tập thể dục buổi sáng', 'Tỉnh táo, sảng khoái cả ngày'], ['Ngủ đủ giấc', 'Cơ thể phát triển tốt'], ['Ăn đủ rau xanh', 'Bổ sung vitamin, dễ tiêu hóa']],
                'Tập thể dục, ngủ đủ giấc và ăn rau xanh đều giúp cơ thể khoẻ mạnh.'],
            ['Nối mỗi nhóm chất với vai trò.',
                [['Chất đạm (thịt, trứng, đậu)', 'Xây dựng cơ bắp'], ['Chất bột đường (cơm, bánh mì)', 'Cung cấp năng lượng'], ['Vitamin (rau, quả)', 'Tăng sức đề kháng']],
                'Chất đạm xây cơ bắp, chất bột đường cho năng lượng, vitamin tăng đề kháng.'],
            ['Nối mỗi dấu hiệu với ý nghĩa.',
                [['Tim đập nhanh khi chạy', 'Cơ thể đang vận động tích cực'], ['Ra mồ hôi khi tập', 'Cơ thể đang tỏa nhiệt, làm mát'], ['Đau nhói khi tập sai tư thế', 'Cần dừng lại, kiểm tra tư thế']],
                'Tim đập nhanh và ra mồ hôi là bình thường; đau nhói thì phải dừng lại.'],
        ]);
        $this->sortQ('tt-ren-luyen-suc-khoe', [
            ['Xếp các thói quen vào nhóm: Tốt cho sức khoẻ / Có hại cho sức khoẻ.',
                [['Tập thể dục đều đặn', 'Tốt cho sức khoẻ'], ['Ngủ đủ 8 tiếng', 'Tốt cho sức khoẻ'], ['Thức khuya chơi game', 'Có hại cho sức khoẻ'], ['Ăn nhiều đồ ngọt', 'Có hại cho sức khoẻ']],
                'Tập thể dục và ngủ đủ giấc tốt cho sức khoẻ; thức khuya và ăn nhiều đồ ngọt có hại.'],
            ['Xếp các món ăn vào nhóm: Nên ăn nhiều / Nên hạn chế.',
                [['Rau xanh', 'Nên ăn nhiều'], ['Trái cây', 'Nên ăn nhiều'], ['Nước ngọt', 'Nên hạn chế'], ['Khoai tây chiên', 'Nên hạn chế']],
                'Nên ăn nhiều rau xanh, trái cây; hạn chế nước ngọt và đồ chiên rán.'],
            ['Xếp các hoạt động vào nhóm: Vận động tích cực / Ngồi một chỗ.',
                [['Đá bóng', 'Vận động tích cực'], ['Bơi lội', 'Vận động tích cực'], ['Ngồi xem tivi 3 tiếng', 'Ngồi một chỗ'], ['Chơi game trên điện thoại', 'Ngồi một chỗ']],
                'Đá bóng, bơi lội là vận động tích cực; ngồi lâu xem tivi, chơi game thì không.'],
        ]);
        $this->fill('tt-ren-luyen-suc-khoe', [
            ['Học sinh nên vận động thể chất ít nhất ___ phút mỗi ngày.',
                [[0, '60']], 'Vận động ít nhất 60 phút mỗi ngày giúp cơ thể khoẻ mạnh.'],
            ['Mỗi đêm học sinh nên ngủ đủ từ 8 đến 9 ___.',
                [[0, 'tiếng']], 'Ngủ đủ 8–9 tiếng mỗi đêm để cơ thể phát triển tốt.'],
            ['Bữa ăn ___ cung cấp năng lượng cho buổi học nên không được bỏ.',
                [[0, 'sáng']], 'Bữa sáng là bữa ăn quan trọng nhất, không nên bỏ.'],
        ]);

        // ---------- Bài 6: Vệ sinh cá nhân khi tập luyện ----------
        $this->quiz('tt-ve-sinh-ca-nhan-khi-tap', [
            ['Sau khi tập thể thao ra nhiều mồ hôi, em nên làm gì?',
                ['Tắm rửa sạch sẽ, thay quần áo khô', 'Để nguyên quần áo ướt đi chơi', 'Ngồi trước quạt thật lâu', 'Không cần làm gì'], 0,
                'Mồ hôi và bụi bẩn cần được tắm rửa sạch; quần áo ướt phải thay để tránh cảm lạnh.'],
            ['Khăn mặt dùng khi tập thể thao nên được giặt như thế nào?',
                ['Giặt sạch và phơi khô sau mỗi buổi tập', 'Dùng chung với bạn cho vui', 'Một tuần giặt một lần', 'Không cần giặt'], 0,
                'Khăn thấm mồ hôi dễ sinh vi khuẩn nên phải giặt sạch và phơi khô sau mỗi lần dùng.'],
            ['Trước khi ăn sau buổi tập, em cần làm gì?',
                ['Rửa tay sạch bằng xà phòng', 'Ăn luôn cho nhanh', 'Lau tay vào quần áo', 'Nhờ bạn đút'], 0,
                'Tay bẩn sau khi tập chứa nhiều vi khuẩn nên phải rửa sạch trước khi ăn.'],
        ]);
        $this->matching('tt-ve-sinh-ca-nhan-khi-tap', [
            ['Nối mỗi việc làm với lý do.',
                [['Tắm sau khi tập', 'Loại bỏ mồ hôi, bụi bẩn'], ['Thay quần áo khô', 'Tránh cảm lạnh'], ['Cắt móng tay gọn', 'Tránh làm xước bạn khi chơi']],
                'Tắm loại bỏ mồ hôi, thay đồ khô tránh cảm lạnh, móng tay gọn tránh làm xước bạn.'],
            ['Nối mỗi vật dụng với cách bảo quản.',
                [['Giày thể thao', 'Phơi nơi thoáng, tránh ẩm mốc'], ['Khăn mặt', 'Giặt sạch, phơi khô'], ['Bình nước cá nhân', 'Rửa sạch mỗi ngày']],
                'Giày phơi nơi thoáng, khăn giặt phơi khô, bình nước rửa sạch mỗi ngày.'],
            ['Nối mỗi thói quen với đánh giá.',
                [['Rửa tay trước khi ăn', 'Nên'], ['Dùng chung khăn với bạn', 'Không nên'], ['Để giày ướt trong túi kín', 'Không nên']],
                'Rửa tay trước khi ăn là nên; dùng chung khăn và để giày ướt trong túi kín thì không nên.'],
        ]);
        $this->sortQ('tt-ve-sinh-ca-nhan-khi-tap', [
            ['Xếp các việc làm vào nhóm: Nên làm / Không nên làm.',
                [['Tắm sau khi tập', 'Nên làm'], ['Rửa tay trước khi ăn', 'Nên làm'], ['Dùng chung khăn mặt', 'Không nên làm'], ['Mặc lại quần áo đẫm mồ hôi', 'Không nên làm']],
                'Nên tắm sau khi tập và rửa tay trước khi ăn; không dùng chung khăn hay mặc lại đồ ướt mồ hôi.'],
            ['Xếp các vật dụng vào nhóm: Dùng riêng / Có thể dùng chung.',
                [['Khăn mặt', 'Dùng riêng'], ['Bàn chải đánh răng', 'Dùng riêng'], ['Bóng đá của lớp', 'Có thể dùng chung'], ['Thảm tập chung', 'Có thể dùng chung']],
                'Đồ vệ sinh cá nhân phải dùng riêng; dụng cụ tập luyện có thể dùng chung.'],
            ['Xếp các thời điểm vào nhóm: Cần rửa tay / Chưa cần.',
                [['Trước khi ăn', 'Cần rửa tay'], ['Sau khi đi vệ sinh', 'Cần rửa tay'], ['Sau khi tập thể thao', 'Cần rửa tay'], ['Vừa rửa tay xong', 'Chưa cần']],
                'Cần rửa tay trước khi ăn, sau khi đi vệ sinh và sau khi tập thể thao.'],
        ]);
        $this->fill('tt-ve-sinh-ca-nhan-khi-tap', [
            ['Sau khi tập ra nhiều mồ hôi, cần ___ sạch sẽ và thay quần áo khô.',
                [[0, 'tắm rửa']], 'Tắm rửa sạch sẽ sau khi tập ra nhiều mồ hôi.'],
            ['Không nên dùng chung ___ mặt với người khác.',
                [[0, 'khăn']], 'Khăn mặt là đồ dùng cá nhân, không dùng chung.'],
            ['Trước khi ăn, cần rửa tay sạch bằng ___.',
                [[0, 'xà phòng']], 'Rửa tay bằng xà phòng trước khi ăn để diệt vi khuẩn.'],
        ]);
    }
}
