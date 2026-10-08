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
 * Seeder môn Trải nghiệm & Hướng nghiệp (tự chạy độc lập được):
 * subject → 3 topics → 6 skills → 6 bài học → mỗi bài 12 câu
 * (3 quiz + 3 matching + 3 sort + 3 fill), tất cả is_demo = true.
 * Nội dung TIẾNG VIỆT TỰ VIẾT 100%, bám chương trình lớp 6–9.
 */
class SubjectTraiNghiemHuongNghiepSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(['slug' => 'trai-nghiem-huong-nghiep'], [
            'name' => 'Trải nghiệm & Hướng nghiệp',
            'icon' => '🧭',
            'color' => '#14b8a6',
            'description' => 'Kỹ năng sống và định hướng nghề nghiệp cho học sinh.',
            'sort_order' => 14,
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
                'name' => 'Kỹ năng sống cơ bản', 'slug' => 'tnhn-ky-nang-song', 'icon' => '🤝',
                'description' => 'Quản lý thời gian, giao tiếp và làm việc nhóm.',
                'grade_min' => 6, 'grade_max' => 7,
                'skills' => [
                    [
                        'name' => 'Quản lý thời gian', 'slug' => 'kn-tnhn-quan-ly-thoi-gian',
                        'description' => 'Lập thời gian biểu và sắp xếp công việc hợp lý.',
                        'lessons' => [
                            [
                                'title' => 'Lập thời gian biểu', 'slug' => 'tnhn-lap-thoi-gian-bieu',
                                'objective' => 'Biết lập thời gian biểu cá nhân và ưu tiên việc quan trọng.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Thời gian là tài sản quý giá. Hãy học cách sắp xếp một ngày của mình thật khoa học nhé.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Giao tiếp và làm việc nhóm', 'slug' => 'kn-tnhn-lam-viec-nhom',
                        'description' => 'Kỹ năng lắng nghe, chia sẻ và hợp tác trong nhóm.',
                        'lessons' => [
                            [
                                'title' => 'Giao tiếp và làm việc nhóm', 'slug' => 'tnhn-giao-tiep-lam-viec-nhom',
                                'objective' => 'Biết lắng nghe, tôn trọng ý kiến bạn và phân công nhiệm vụ khi làm việc nhóm.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Làm việc nhóm tốt giúp mọi việc nhanh hơn và vui hơn. Cùng học cách hợp tác nhé.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'An toàn và môi trường', 'slug' => 'tnhn-an-toan-moi-truong', 'icon' => '🌍',
                'description' => 'An toàn giao thông và bảo vệ môi trường.',
                'grade_min' => 7, 'grade_max' => 8,
                'skills' => [
                    [
                        'name' => 'An toàn giao thông', 'slug' => 'kn-tnhn-an-toan-giao-thong',
                        'description' => 'Đội mũ bảo hiểm, qua đường an toàn và đi đúng phần đường.',
                        'lessons' => [
                            [
                                'title' => 'An toàn giao thông', 'slug' => 'tnhn-an-toan-giao-thong',
                                'objective' => 'Biết đội mũ bảo hiểm, qua đường đúng cách và đi đúng phần đường quy định.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Mỗi ngày em đều tham gia giao thông. Hãy ghi nhớ những quy tắc giữ an toàn cho bản thân nhé.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Bảo vệ môi trường', 'slug' => 'kn-tnhn-bao-ve-moi-truong',
                        'description' => 'Phân loại rác và hành động xanh bảo vệ môi trường.',
                        'lessons' => [
                            [
                                'title' => 'Phân loại rác, bảo vệ môi trường', 'slug' => 'tnhn-phan-loai-rac',
                                'objective' => 'Biết phân loại rác thải và các hành động đơn giản bảo vệ môi trường.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Trái Đất là ngôi nhà chung. Những hành động nhỏ của em mỗi ngày sẽ giúp môi trường xanh hơn.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Định hướng nghề nghiệp', 'slug' => 'tnhn-dinh-huong-nghe-nghiep', 'icon' => '🧭',
                'description' => 'Tìm hiểu các nghề nghiệp và nuôi dưỡng ước mơ nghề nghiệp.',
                'grade_min' => 8, 'grade_max' => 9,
                'skills' => [
                    [
                        'name' => 'Tìm hiểu nghề nghiệp', 'slug' => 'kn-tnhn-tim-hieu-nghe',
                        'description' => 'Biết công việc chính và phẩm chất cần có của các nghề quen thuộc.',
                        'lessons' => [
                            [
                                'title' => 'Các nghề nghiệp quen thuộc', 'slug' => 'tnhn-cac-nghe-quen-thuoc',
                                'objective' => 'Kể được công việc chính và phẩm chất cần có của các nghề: bác sĩ, giáo viên, kỹ sư, nông dân.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Xung quanh em có rất nhiều nghề nghiệp thú vị. Cùng tìm hiểu xem mỗi nghề làm gì và cần phẩm chất nào nhé.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Ước mơ nghề nghiệp', 'slug' => 'kn-tnhn-uoc-mo-nghe-nghiep',
                        'description' => 'Xác định sở thích, năng lực và lập kế hoạch theo đuổi ước mơ.',
                        'lessons' => [
                            [
                                'title' => 'Ước mơ nghề nghiệp của em', 'slug' => 'tnhn-uoc-mo-nghe-nghiep-cua-em',
                                'objective' => 'Biết chọn nghề phù hợp sở thích, năng lực và lập kế hoạch học tập để theo đuổi ước mơ.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Mỗi người đều có ước mơ. Hãy tìm hiểu cách biến ước mơ nghề nghiệp thành hiện thực nhé.',
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
        // ---------- Bài 1: Lập thời gian biểu ----------
        $this->quiz('tnhn-lap-thoi-gian-bieu', [
            ['Lập thời gian biểu giúp ích gì cho học sinh?',
                ['Sắp xếp việc học và chơi hợp lý, không bỏ sót việc', 'Để khoe với bạn bè', 'Để không phải học bài', 'Để thức khuya thoải mái'], 0,
                'Thời gian biểu giúp phân bổ hợp lý giữa học tập, nghỉ ngơi và vui chơi.'],
            ['Khi có nhiều việc cùng lúc, em nên làm việc nào trước?',
                ['Việc quan trọng và gấp', 'Việc dễ nhất', 'Việc vui nhất', 'Việc nào cũng được'], 0,
                'Ưu tiên việc vừa quan trọng vừa gấp, như bài kiểm tra ngày mai.'],
            ['Mỗi tối trước khi ngủ, em nên làm gì để ngày mai hiệu quả?',
                ['Chuẩn bị sách vở, xem lại thời gian biểu', 'Chơi game đến khuya', 'Không cần chuẩn bị gì', 'Thức xem phim'], 0,
                'Chuẩn bị trước giúp buổi sáng không vội vàng, ngày mới bắt đầu suôn sẻ.'],
        ]);
        $this->matching('tnhn-lap-thoi-gian-bieu', [
            ['Nối mỗi khung giờ với việc nên làm.',
                [['Buổi sáng', 'Học tập trung'], ['Buổi chiều', 'Vận động, thể thao'], ['Buổi tối', 'Ôn bài, chuẩn bị cho ngày mai']],
                'Sáng học tập trung, chiều vận động thể thao, tối ôn bài và chuẩn bị cho ngày mai.'],
            ['Nối mỗi việc với mức độ ưu tiên.',
                [['Ôn bài kiểm tra ngày mai', 'Ưu tiên cao'], ['Dọn phòng cuối tuần', 'Ưu tiên trung bình'], ['Xem phim giải trí', 'Ưu tiên thấp']],
                'Bài kiểm tra ngày mai ưu tiên cao nhất, giải trí ưu tiên thấp nhất.'],
            ['Nối mỗi thói quen với kết quả.',
                [['Làm bài tập ngay sau giờ học', 'Nhớ bài lâu, rảnh buổi tối'], ['Để bài tập đến khuya', 'Mệt mỏi, dễ sai'], ['Vừa học vừa chơi game', 'Mất tập trung, học lâu']],
                'Làm bài sớm thì nhớ lâu và rảnh tối; để khuya hoặc vừa học vừa chơi đều không tốt.'],
        ]);
        $this->sortQ('tnhn-lap-thoi-gian-bieu', [
            ['Xếp các việc làm vào nhóm: Nên làm / Không nên làm để quản lý thời gian tốt.',
                [['Lập thời gian biểu mỗi tối', 'Nên làm'], ['Làm việc quan trọng trước', 'Nên làm'], ['Lướt điện thoại khi đang học', 'Không nên làm'], ['Để mọi việc đến phút cuối', 'Không nên làm']],
                'Lập thời gian biểu và làm việc quan trọng trước là nên; lướt điện thoại khi học và nước đến chân mới nhảy thì không.'],
            ['Xếp các việc vào nhóm: Ưu tiên cao / Ưu tiên thấp.',
                [['Làm bài tập về nhà', 'Ưu tiên cao'], ['Ôn bài kiểm tra', 'Ưu tiên cao'], ['Chơi game giải trí', 'Ưu tiên thấp'], ['Tám chuyện trên mạng', 'Ưu tiên thấp']],
                'Bài tập và ôn kiểm tra ưu tiên cao; game và tám chuyện ưu tiên thấp.'],
            ['Xếp các khung giờ vào nhóm: Nên học bài / Nên nghỉ ngơi.',
                [['7h–9h sáng', 'Nên học bài'], ['19h–21h tối', 'Nên học bài'], ['12h–13h trưa', 'Nên nghỉ ngơi'], ['22h30 trở đi', 'Nên nghỉ ngơi']],
                'Sáng sớm và tối là giờ học tốt; trưa và sau 22h30 nên nghỉ ngơi.'],
        ]);
        $this->fill('tnhn-lap-thoi-gian-bieu', [
            ['Khi có nhiều việc, em nên ưu tiên làm việc ___ và gấp trước.',
                [[0, 'quan trọng']], 'Ưu tiên việc vừa quan trọng vừa gấp.'],
            ['Lập ___ giúp em không bỏ sót việc cần làm trong ngày.',
                [[0, 'thời gian biểu']], 'Thời gian biểu giúp sắp xếp và không bỏ sót việc.'],
            ['Không nên dùng điện thoại khi đang ___ bài.',
                [[0, 'học']], 'Dùng điện thoại khi học làm mất tập trung.'],
        ]);

        // ---------- Bài 2: Giao tiếp và làm việc nhóm ----------
        $this->quiz('tnhn-giao-tiep-lam-viec-nhom', [
            ['Khi bạn đang trình bày ý kiến, em nên làm gì?',
                ['Lắng nghe chăm chú, không ngắt lời', 'Ngắt lời để nói ý mình', 'Cười đùa với bạn bên cạnh', 'Bỏ đi chỗ khác'], 0,
                'Lắng nghe và không ngắt lời là phép lịch sự cơ bản trong giao tiếp.'],
            ['Khi làm bài tập nhóm, cách phân công nào là tốt?',
                ['Chia việc theo khả năng của từng bạn', 'Một bạn làm hết', 'Ai thích thì làm', 'Bốc thăm ai trúng thì làm'], 0,
                'Phân công theo khả năng giúp mỗi bạn phát huy điểm mạnh, nhóm hoàn thành tốt.'],
            ['Khi có bất đồng ý kiến trong nhóm, em nên làm gì?',
                ['Bình tĩnh thảo luận, tìm cách tốt nhất', 'Cãi nhau cho thắng', 'Bỏ nhóm ra về', 'Im lặng không nói gì'], 0,
                'Bất đồng là bình thường; thảo luận bình tĩnh giúp nhóm chọn được giải pháp tốt nhất.'],
        ]);
        $this->matching('tnhn-giao-tiep-lam-viec-nhom', [
            ['Nối mỗi hành vi với đánh giá.',
                [['Lắng nghe bạn nói hết ý', 'Giao tiếp tốt'], ['Cảm ơn khi được giúp đỡ', 'Giao tiếp tốt'], ['Ngắt lời người khác', 'Giao tiếp chưa tốt']],
                'Lắng nghe hết ý và cảm ơn là giao tiếp tốt; ngắt lời thì chưa tốt.'],
            ['Nối mỗi vai trò với nhiệm vụ trong nhóm.',
                [['Nhóm trưởng', 'Phân công, đôn đốc tiến độ'], ['Thư ký', 'Ghi chép ý kiến, kết quả'], ['Thành viên', 'Hoàn thành phần việc được giao']],
                'Nhóm trưởng phân công, thư ký ghi chép, thành viên hoàn thành việc được giao.'],
            ['Nối mỗi câu nói với tác dụng.',
                [['"Ý kiến của bạn rất hay!"', 'Động viên bạn'], ['"Mình chưa hiểu, bạn giải thích thêm nhé"', 'Hỏi để hiểu rõ'], ['"Cảm ơn bạn đã giúp mình"', 'Thể hiện sự biết ơn']],
                'Khen ngợi để động viên, hỏi để hiểu rõ, cảm ơn để thể hiện biết ơn.'],
        ]);
        $this->sortQ('tnhn-giao-tiep-lam-viec-nhom', [
            ['Xếp các hành vi vào nhóm: Nên làm / Không nên làm khi làm việc nhóm.',
                [['Lắng nghe ý kiến bạn', 'Nên làm'], ['Hoàn thành đúng phần việc', 'Nên làm'], ['Đổ hết việc cho bạn', 'Không nên làm'], ['Chê bai ý kiến của bạn', 'Không nên làm']],
                'Nên lắng nghe và hoàn thành phần việc; không đổ việc hay chê bai bạn.'],
            ['Xếp các câu nói vào nhóm: Lịch sự / Chưa lịch sự.',
                [['"Bạn nói tiếp đi, mình đang nghe"', 'Lịch sự'], ['"Cảm ơn bạn nhiều nhé"', 'Lịch sự'], ['"Im đi, ý kiến dở quá"', 'Chưa lịch sự'], ['"Kệ tao, tao thích làm vậy"', 'Chưa lịch sự']],
                'Lời nói tôn trọng, cảm ơn là lịch sự; quát tháo, chê bai là chưa lịch sự.'],
            ['Xếp các việc làm vào nhóm: Giúp nhóm tốt hơn / Làm nhóm tệ đi.',
                [['Động viên bạn khi bạn nản', 'Giúp nhóm tốt hơn'], ['Chia sẻ tài liệu cho cả nhóm', 'Giúp nhóm tốt hơn'], ['Giấu tài liệu riêng', 'Làm nhóm tệ đi'], ['Trễ hẹn họp nhóm', 'Làm nhóm tệ đi']],
                'Động viên và chia sẻ giúp nhóm tốt hơn; giấu tài liệu và trễ hẹn làm nhóm tệ đi.'],
        ]);
        $this->fill('tnhn-giao-tiep-lam-viec-nhom', [
            ['Khi bạn trình bày, em nên ___ chăm chú và không ngắt lời.',
                [[0, 'lắng nghe']], 'Lắng nghe chăm chú, không ngắt lời là phép lịch sự.'],
            ['Trong nhóm, mỗi bạn nên hoàn thành đúng ___ được giao.',
                [[0, 'nhiệm vụ']], 'Mỗi thành viên hoàn thành đúng nhiệm vụ được giao.'],
            ['Nói lời ___ khi được bạn giúp đỡ là phép lịch sự.',
                [[0, 'cảm ơn']], 'Cảm ơn khi được giúp đỡ thể hiện sự biết ơn và lịch sự.'],
        ]);

        // ---------- Bài 3: An toàn giao thông ----------
        $this->quiz('tnhn-an-toan-giao-thong', [
            ['Khi ngồi trên xe máy, xe đạp điện, em phải làm gì?',
                ['Đội mũ bảo hiểm đạt chuẩn, cài quai chắc chắn', 'Không cần đội mũ cho mát', 'Đội mũ nhưng không cài quai', 'Đội nón lá'], 0,
                'Mũ bảo hiểm đạt chuẩn và cài quai chắc chắn mới bảo vệ đầu khi có va chạm.'],
            ['Khi qua đường ở nơi không có đèn tín hiệu, em nên làm gì?',
                ['Quan sát trái – phải, chắc chắn an toàn mới qua', 'Chạy thật nhanh qua đường', 'Vừa đi vừa nghe nhạc', 'Nhắm mắt chạy qua'], 0,
                'Phải quan sát kỹ hai hướng, đợi xe dừng hẳn hoặc đường vắng mới qua.'],
            ['Người đi bộ nên đi ở đâu?',
                ['Trên vỉa hè hoặc lề đường bên phải', 'Giữa lòng đường', 'Trên làn xe máy', 'Đi đâu cũng được'], 0,
                'Người đi bộ đi trên vỉa hè; không có vỉa hè thì đi sát lề đường bên phải.'],
        ]);
        $this->matching('tnhn-an-toan-giao-thong', [
            ['Nối mỗi biển báo với ý nghĩa.',
                [['Biển tròn viền đỏ', 'Biển cấm'], ['Biển tam giác viền đỏ', 'Biển cảnh báo nguy hiểm'], ['Biển tròn nền xanh', 'Biển hiệu lệnh phải theo']],
                'Biển tròn viền đỏ là biển cấm, tam giác viền đỏ là biển cảnh báo, tròn nền xanh là biển hiệu lệnh.'],
            ['Nối mỗi tình huống với cách xử lý.',
                [['Đèn đỏ', 'Dừng lại'], ['Đèn xanh', 'Được đi'], ['Người đi bộ qua đường', 'Nhường đường']],
                'Đèn đỏ dừng lại, đèn xanh được đi, gặp người đi bộ phải nhường đường.'],
            ['Nối mỗi hành vi với đánh giá.',
                [['Đội mũ bảo hiểm khi đi xe đạp điện', 'An toàn'], ['Đi đúng làn đường', 'An toàn'], ['Vừa đi xe vừa dùng điện thoại', 'Nguy hiểm']],
                'Đội mũ bảo hiểm và đi đúng làn là an toàn; vừa đi vừa dùng điện thoại rất nguy hiểm.'],
        ]);
        $this->sortQ('tnhn-an-toan-giao-thong', [
            ['Xếp các hành vi vào nhóm: An toàn / Nguy hiểm.',
                [['Đội mũ bảo hiểm', 'An toàn'], ['Qua đường đúng vạch', 'An toàn'], ['Lạng lách, đánh võng', 'Nguy hiểm'], ['Vượt đèn đỏ', 'Nguy hiểm']],
                'Đội mũ và qua đường đúng vạch là an toàn; lạng lách và vượt đèn đỏ rất nguy hiểm.'],
            ['Xếp các vị trí vào nhóm: Dành cho người đi bộ / Dành cho xe.',
                [['Vỉa hè', 'Dành cho người đi bộ'], ['Vạch qua đường', 'Dành cho người đi bộ'], ['Lòng đường', 'Dành cho xe'], ['Làn đường xe máy', 'Dành cho xe']],
                'Vỉa hè và vạch qua đường dành cho người đi bộ; lòng đường và làn xe dành cho xe.'],
            ['Xếp các việc làm vào nhóm: Nên làm / Không nên làm khi đi xe đạp.',
                [['Đi sát lề phải', 'Nên làm'], ['Bật đèn khi trời tối', 'Nên làm'], ['Buông hai tay', 'Không nên làm'], ['Đèo 3 người', 'Không nên làm']],
                'Đi xe đạp nên đi sát lề phải, bật đèn khi tối; không buông tay hay đèo quá số người.'],
        ]);
        $this->fill('tnhn-an-toan-giao-thong', [
            ['Khi đi xe máy, xe đạp điện phải đội ___ bảo hiểm.',
                [[0, 'mũ']], 'Phải đội mũ bảo hiểm khi đi xe máy, xe đạp điện.'],
            ['Gặp đèn ___, người tham gia giao thông phải dừng lại.',
                [[0, 'đỏ']], 'Đèn đỏ có nghĩa là dừng lại.'],
            ['Người đi bộ nên đi trên ___ hè.',
                [[0, 'vỉa']], 'Người đi bộ nên đi trên vỉa hè.'],
        ]);

        // ---------- Bài 4: Phân loại rác, bảo vệ môi trường ----------
        $this->quiz('tnhn-phan-loai-rac', [
            ['Vỏ rau, vỏ trái cây thuộc loại rác nào?',
                ['Rác hữu cơ', 'Rác tái chế', 'Rác nguy hại', 'Rác xây dựng'], 0,
                'Rác hữu cơ là rác từ thực phẩm, có thể ủ làm phân bón.'],
            ['Pin cũ, bóng đèn hỏng thuộc loại rác nào?',
                ['Rác nguy hại', 'Rác hữu cơ', 'Rác tái chế', 'Rác thông thường'], 0,
                'Pin, bóng đèn chứa chất độc hại nên phải thu gom riêng, không vứt bừa bãi.'],
            ['Hành động nào sau đây bảo vệ môi trường?',
                ['Mang túi vải đi chợ thay túi ni lông', 'Xả rác xuống sông', 'Đốt rác bừa bãi', 'Dùng ống hút nhựa mỗi ngày'], 0,
                'Hạn chế túi ni lông, đồ nhựa dùng một lần là cách đơn giản bảo vệ môi trường.'],
        ]);
        $this->matching('tnhn-phan-loai-rac', [
            ['Nối mỗi loại rác với ví dụ.',
                [['Rác hữu cơ', 'Vỏ rau, thức ăn thừa'], ['Rác tái chế', 'Chai nhựa, giấy báo'], ['Rác nguy hại', 'Pin cũ, bóng đèn hỏng']],
                'Rác hữu cơ từ thực phẩm, rác tái chế như chai nhựa giấy báo, rác nguy hại như pin cũ.'],
            ['Nối mỗi hành động với lợi ích.',
                [['Trồng cây xanh', 'Làm sạch không khí'], ['Tiết kiệm nước', 'Bảo vệ nguồn nước'], ['Tắt điện khi không dùng', 'Tiết kiệm năng lượng']],
                'Trồng cây làm sạch không khí, tiết kiệm nước bảo vệ nguồn nước, tắt điện tiết kiệm năng lượng.'],
            ['Nối mỗi vật dụng với cách dùng thân thiện môi trường.',
                [['Túi vải', 'Dùng nhiều lần khi đi chợ'], ['Bình nước cá nhân', 'Thay chai nhựa dùng một lần'], ['Khăn tay vải', 'Thay khăn giấy']],
                'Dùng đồ tái sử dụng nhiều lần giúp giảm rác thải nhựa và giấy.'],
        ]);
        $this->sortQ('tnhn-phan-loai-rac', [
            ['Xếp các loại rác vào nhóm: Tái chế được / Không tái chế được.',
                [['Chai nhựa', 'Tái chế được'], ['Giấy báo cũ', 'Tái chế được'], ['Vỏ rau củ', 'Không tái chế được'], ['Tã giấy đã dùng', 'Không tái chế được']],
                'Chai nhựa, giấy báo tái chế được; vỏ rau củ và tã giấy đã dùng thì không.'],
            ['Xếp các hành động vào nhóm: Bảo vệ môi trường / Gây hại môi trường.',
                [['Phân loại rác', 'Bảo vệ môi trường'], ['Trồng cây xanh', 'Bảo vệ môi trường'], ['Xả rác bừa bãi', 'Gây hại môi trường'], ['Đốt rác ngoài trời', 'Gây hại môi trường']],
                'Phân loại rác và trồng cây bảo vệ môi trường; xả rác bừa bãi và đốt rác gây hại.'],
            ['Xếp các vật dụng vào nhóm: Nên dùng / Nên hạn chế.',
                [['Túi vải', 'Nên dùng'], ['Bình nước cá nhân', 'Nên dùng'], ['Túi ni lông', 'Nên hạn chế'], ['Ống hút nhựa', 'Nên hạn chế']],
                'Nên dùng túi vải, bình nước cá nhân; hạn chế túi ni lông và ống hút nhựa.'],
        ]);
        $this->fill('tnhn-phan-loai-rac', [
            ['Vỏ rau, thức ăn thừa thuộc loại rác ___.',
                [[0, 'hữu cơ']], 'Vỏ rau, thức ăn thừa là rác hữu cơ, có thể ủ làm phân bón.'],
            ['Pin cũ thuộc nhóm rác ___, cần thu gom riêng không vứt bừa bãi.',
                [[0, 'nguy hại']], 'Pin cũ là rác nguy hại, phải thu gom riêng.'],
            ['Mang ___ vải đi chợ giúp giảm túi ni lông.',
                [[0, 'túi']], 'Mang túi vải đi chợ giúp giảm rác thải ni lông.'],
        ]);

        // ---------- Bài 5: Các nghề nghiệp quen thuộc ----------
        $this->quiz('tnhn-cac-nghe-quen-thuoc', [
            ['Công việc chính của bác sĩ là gì?',
                ['Khám và chữa bệnh cho mọi người', 'Xây nhà cửa', 'Dạy học sinh', 'Trồng lúa'], 0,
                'Bác sĩ khám bệnh, chữa trị và chăm sóc sức khoẻ cộng đồng.', 'trung_binh'],
            ['Phẩm chất nào quan trọng nhất với nghề giáo viên?',
                ['Yêu thương học trò, kiên nhẫn', 'Thích tiền thưởng', 'Nói nhiều', 'Nghiêm khắc quá mức'], 0,
                'Giáo viên cần yêu thương, kiên nhẫn để dạy dỗ học trò nên người.', 'trung_binh'],
            ['Kỹ sư xây dựng cần giỏi môn học nào nhất?',
                ['Toán và Vật lý', 'Âm nhạc', 'Mỹ thuật duy nhất', 'Không cần học môn nào'], 0,
                'Kỹ sư cần giỏi Toán, Vật lý để tính toán, thiết kế công trình an toàn.', 'kho'],
        ]);
        $this->matching('tnhn-cac-nghe-quen-thuoc', [
            ['Nối mỗi nghề với công việc chính.',
                [['Bác sĩ', 'Khám chữa bệnh'], ['Giáo viên', 'Dạy học'], ['Kỹ sư', 'Thiết kế, xây dựng công trình'], ['Nông dân', 'Trồng trọt, chăn nuôi']],
                'Bác sĩ chữa bệnh, giáo viên dạy học, kỹ sư xây dựng, nông dân trồng trọt chăn nuôi.', 'trung_binh'],
            ['Nối mỗi nghề với phẩm chất cần có.',
                [['Bác sĩ', 'Tận tâm, cẩn thận'], ['Giáo viên', 'Yêu thương, kiên nhẫn'], ['Kỹ sư', 'Chính xác, sáng tạo'], ['Nông dân', 'Cần cù, chịu khó']],
                'Mỗi nghề cần phẩm chất riêng: bác sĩ tận tâm, giáo viên kiên nhẫn, kỹ sư chính xác, nông dân cần cù.', 'kho'],
            ['Nối mỗi nghề với nơi làm việc thường thấy.',
                [['Bác sĩ', 'Bệnh viện'], ['Giáo viên', 'Trường học'], ['Nông dân', 'Đồng ruộng'], ['Kỹ sư xây dựng', 'Công trường']],
                'Bác sĩ ở bệnh viện, giáo viên ở trường học, nông dân ở đồng ruộng, kỹ sư ở công trường.', 'trung_binh'],
        ]);
        $this->sortQ('tnhn-cac-nghe-quen-thuoc', [
            ['Xếp các nghề vào nhóm: Nghề chăm sóc con người / Nghề sản xuất, xây dựng.',
                [['Bác sĩ', 'Nghề chăm sóc con người'], ['Giáo viên', 'Nghề chăm sóc con người'], ['Kỹ sư', 'Nghề sản xuất, xây dựng'], ['Nông dân', 'Nghề sản xuất, xây dựng']],
                'Bác sĩ, giáo viên chăm sóc con người; kỹ sư, nông dân thuộc nhóm sản xuất, xây dựng.', 'trung_binh'],
            ['Xếp các phẩm chất vào nhóm: Cần cho mọi nghề / Chưa phải phẩm chất nghề nghiệp.',
                [['Trung thực', 'Cần cho mọi nghề'], ['Chăm chỉ', 'Cần cho mọi nghề'], ['Lười biếng', 'Chưa phải phẩm chất nghề nghiệp'], ['Gian dối', 'Chưa phải phẩm chất nghề nghiệp']],
                'Trung thực, chăm chỉ cần cho mọi nghề; lười biếng, gian dối thì không.', 'trung_binh'],
            ['Xếp các việc làm vào nhóm: Giúp tìm hiểu nghề / Chưa giúp tìm hiểu nghề.',
                [['Trò chuyện với người làm nghề', 'Giúp tìm hiểu nghề'], ['Đọc sách về các nghề', 'Giúp tìm hiểu nghề'], ['Tham quan nơi làm việc', 'Chưa giúp tìm hiểu nghề'], ['Không quan tâm', 'Chưa giúp tìm hiểu nghề']],
                'Trò chuyện và đọc sách giúp tìm hiểu nghề; chỉ tham quan qua loa hay không quan tâm thì chưa đủ.'],
        ]);
        $this->fill('tnhn-cac-nghe-quen-thuoc', [
            ['Bác sĩ làm việc chủ yếu tại ___.',
                [[0, 'bệnh viện']], 'Bác sĩ làm việc chủ yếu tại bệnh viện.', 'trung_binh'],
            ['Giáo viên cần yêu thương học trò và ___ nhẫn.',
                [[0, 'kiên']], 'Giáo viên cần yêu thương và kiên nhẫn với học trò.', 'trung_binh'],
            ['Nông dân trồng trọt, chăn nuôi trên ___ ruộng.',
                [[0, 'đồng']], 'Nông dân làm việc trên đồng ruộng.', 'trung_binh'],
        ]);

        // ---------- Bài 6: Ước mơ nghề nghiệp của em ----------
        $this->quiz('tnhn-uoc-mo-nghe-nghiep-cua-em', [
            ['Để chọn nghề phù hợp, em nên dựa vào điều gì?',
                ['Sở thích và năng lực của bản thân', 'Nghề nào lương cao nhất', 'Bạn bè chọn gì thì chọn nấy', 'Bốc thăm ngẫu nhiên'], 0,
                'Nghề phù hợp là nghề vừa đúng sở thích vừa phát huy được năng lực của mình.', 'trung_binh'],
            ['Muốn trở thành bác sĩ trong tương lai, ngay từ bây giờ em cần làm gì?',
                ['Học giỏi các môn tự nhiên, rèn tính cẩn thận', 'Không cần học, cứ ước là được', 'Chỉ chơi thể thao', 'Bỏ học đi làm sớm'], 0,
                'Ước mơ cần hành động: học giỏi Sinh, Hóa và rèn tính cẩn thận, tận tâm.', 'trung_binh'],
            ['Khi ước mơ nghề nghiệp thay đổi theo thời gian, em nên làm gì?',
                ['Tìm hiểu kỹ rồi điều chỉnh kế hoạch', 'Giấu không nói với ai', 'Bỏ luôn không ước mơ nữa', 'Đổi nghề mỗi tuần'], 0,
                'Thay đổi ước mơ là bình thường; hãy tìm hiểu kỹ về nghề mới rồi điều chỉnh kế hoạch học tập.', 'kho'],
        ]);
        $this->matching('tnhn-uoc-mo-nghe-nghiep-cua-em', [
            ['Nối mỗi sở thích với nghề phù hợp.',
                [['Thích chăm sóc người khác', 'Bác sĩ, y tá'], ['Thích dạy em nhỏ học bài', 'Giáo viên'], ['Thích máy móc, lắp ráp', 'Kỹ sư'], ['Thích cây cối, đồng ruộng', 'Nông dân']],
                'Sở thích gợi ý nghề phù hợp: chăm sóc người khác hợp nghề y, thích dạy học hợp nghề giáo...', 'trung_binh'],
            ['Nối mỗi ước mơ với việc cần làm ngay.',
                [['Muốn làm giáo viên', 'Học giỏi, rèn cách diễn đạt'], ['Muốn làm kỹ sư', 'Học tốt Toán, Lý'], ['Muốn làm bác sĩ', 'Học tốt Sinh, Hóa']],
                'Mỗi ước mơ cần hành động ngay: giáo viên rèn diễn đạt, kỹ sư học Toán Lý, bác sĩ học Sinh Hóa.', 'kho'],
            ['Nối mỗi giai đoạn với mục tiêu.',
                [['Cấp THCS', 'Học đều các môn, tìm hiểu nghề'], ['Cấp THPT', 'Chọn khối thi phù hợp nghề mơ ước'], ['Đại học', 'Học chuyên sâu nghề đã chọn']],
                'THCS học đều và tìm hiểu nghề, THPT chọn khối thi, đại học học chuyên sâu.', 'kho'],
        ]);
        $this->sortQ('tnhn-uoc-mo-nghe-nghiep-cua-em', [
            ['Xếp các việc làm vào nhóm: Giúp đạt ước mơ / Cản trở ước mơ.',
                [['Học tập chăm chỉ', 'Giúp đạt ước mơ'], ['Tìm hiểu về nghề mơ ước', 'Giúp đạt ước mơ'], ['Lười học, ham chơi', 'Cản trở ước mơ'], ['Bỏ cuộc khi gặp khó', 'Cản trở ước mơ']],
                'Học chăm và tìm hiểu nghề giúp đạt ước mơ; lười học và bỏ cuộc cản trở ước mơ.', 'trung_binh'],
            ['Xếp các yếu tố vào nhóm: Nên dựa vào khi chọn nghề / Không nên dựa vào.',
                [['Sở thích', 'Nên dựa vào khi chọn nghề'], ['Năng lực bản thân', 'Nên dựa vào khi chọn nghề'], ['Phong trào nhất thời', 'Không nên dựa vào'], ['Ép buộc của người khác', 'Không nên dựa vào']],
                'Chọn nghề nên dựa vào sở thích và năng lực; không nên theo phong trào hay bị ép buộc.', 'kho'],
            ['Xếp các câu nói vào nhóm: Tích cực / Tiêu cực.',
                [['"Mình sẽ cố gắng mỗi ngày"', 'Tích cực'], ['"Khó mấy mình cũng không bỏ cuộc"', 'Tích cực'], ['"Mình dốt nên chẳng làm được gì"', 'Tiêu cực'], ['"Ước mơ là chuyện viển vông"', 'Tiêu cực']],
                'Suy nghĩ tích cực tiếp thêm động lực; suy nghĩ tiêu cực cản bước ước mơ.', 'trung_binh'],
        ]);
        $this->fill('tnhn-uoc-mo-nghe-nghiep-cua-em', [
            ['Chọn nghề nên dựa vào sở thích và ___ lực của bản thân.',
                [[0, 'năng']], 'Chọn nghề dựa vào sở thích và năng lực bản thân.', 'trung_binh'],
            ['Để đạt ước mơ, em cần lập ___ hoạch học tập cụ thể.',
                [[0, 'kế']], 'Lập kế hoạch học tập cụ thể để theo đuổi ước mơ.', 'trung_binh'],
            ['Ước mơ chỉ thành hiện thực khi có ___ động mỗi ngày.',
                [[0, 'hành']], 'Ước mơ thành hiện thực nhờ hành động mỗi ngày.', 'kho'],
        ]);
    }
}
