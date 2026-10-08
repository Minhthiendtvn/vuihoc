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
 * Môn GDCD — nội dung TỰ VIẾT 100% tiếng Việt, bám chương trình lớp 6-9.
 * Idempotent: Subject/Topic/Skill/Lesson dùng updateOrCreate theo slug;
 * câu hỏi bỏ qua khi đã tồn tại theo (lesson_id, game_type).
 */
class SubjectGdcdSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(
            ['slug' => 'gdcd'],
            [
                'name' => 'GDCD',
                'icon' => '🤝',
                'color' => '#f97316',
                'description' => 'Rèn đạo đức và kỹ năng sống: quyền trẻ em, kỷ luật, an toàn mạng.',
                'sort_order' => 8,
                'is_published' => true,
                'is_demo' => true,
            ]
        );

        $topics = [
            ['name' => 'Quyền và bổn phận của trẻ em', 'slug' => 'gd-quyen-tre-em', 'icon' => '🧒',
             'description' => 'Quyền của trẻ em, bổn phận với gia đình, nhà trường và tôn trọng kỷ luật.',
             'grade_min' => 6, 'grade_max' => 7],
            ['name' => 'Kỹ năng sống', 'slug' => 'gd-ky-nang-song', 'icon' => '🌱',
             'description' => 'Tiết kiệm, tình bạn và cách ứng xử đẹp hằng ngày.',
             'grade_min' => 7, 'grade_max' => 8],
            ['name' => 'An toàn mạng và môi trường', 'slug' => 'gd-an-toan-mang', 'icon' => '🛡️',
             'description' => 'An toàn khi dùng mạng và bảo vệ môi trường sống.',
             'grade_min' => 8, 'grade_max' => 9],
        ];
        $order = 1;
        foreach ($topics as $t) {
            Topic::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'subject_id' => $subject->id,
                    'name' => $t['name'],
                    'description' => $t['description'],
                    'icon' => $t['icon'],
                    'sort_order' => $order++,
                    'grade_min' => $t['grade_min'],
                    'grade_max' => $t['grade_max'],
                    'is_published' => true,
                    'is_demo' => true,
                ]
            );
        }

        $skills = [
            'gd-quyen-tre-em' => [
                ['name' => 'Quyền và bổn phận của trẻ em', 'slug' => 'kn-gd-quyen-1'],
                ['name' => 'Tôn trọng kỷ luật', 'slug' => 'kn-gd-ky-luat-1'],
            ],
            'gd-ky-nang-song' => [
                ['name' => 'Tiết kiệm và tình bạn', 'slug' => 'kn-gd-kns-1'],
            ],
            'gd-an-toan-mang' => [
                ['name' => 'An toàn trên mạng và bảo vệ môi trường', 'slug' => 'kn-gd-atm-1'],
            ],
        ];
        foreach ($skills as $topicSlug => $list) {
            $topic = Topic::where('slug', $topicSlug)->firstOrFail();
            $order = 1;
            foreach ($list as $s) {
                Skill::updateOrCreate(
                    ['slug' => $s['slug']],
                    [
                        'topic_id' => $topic->id,
                        'name' => $s['name'],
                        'description' => $s['name'],
                        'sort_order' => $order++,
                        'is_demo' => true,
                    ]
                );
            }
        }

        $lessons = [
            'kn-gd-quyen-1' => [
                [
                    'title' => 'Quyền của trẻ em', 'slug' => 'gd-quyen-cua-tre-em',
                    'objective' => 'Kể được các quyền cơ bản của trẻ em và biết cách tự bảo vệ mình khi gặp nguy hiểm.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Trẻ em có quyền được sống, được giáo dục, được chăm sóc sức khoẻ, được vui chơi và được bảo vệ. Khi gặp nguy hiểm, gọi tổng đài 111.',
                ],
                [
                    'title' => 'Bổn phận của trẻ em', 'slug' => 'gd-bon-phan-cua-tre-em',
                    'objective' => 'Nêu được bổn phận của trẻ em với gia đình, nhà trường và xã hội.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Đi đôi với quyền là bổn phận: hiếu thảo với ông bà cha mẹ, lễ phép với thầy cô, chăm chỉ học tập, tôn trọng pháp luật.',
                ],
            ],
            'kn-gd-ky-luat-1' => [
                [
                    'title' => 'Tôn trọng kỷ luật', 'slug' => 'gd-ton-trong-ky-luat',
                    'objective' => 'Hiểu kỷ luật là gì và thực hiện nếp sống kỷ luật ở trường, ở nhà và nơi công cộng.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Kỷ luật là tự giác chấp hành quy định chung: đi học đúng giờ, xếp hàng, giữ trật tự, chấp hành luật giao thông.',
                ],
            ],
            'kn-gd-kns-1' => [
                [
                    'title' => 'Tiết kiệm', 'slug' => 'gd-tiet-kiem',
                    'objective' => 'Biết tiết kiệm điện, nước, tiền bạc, thời gian và tránh lãng phí.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Tiết kiệm là dùng hợp lí, đúng mức, không lãng phí: tắt điện khi không dùng, khoá vòi nước, bỏ ống heo...',
                ],
                [
                    'title' => 'Tình bạn đẹp', 'slug' => 'gd-tinh-ban',
                    'objective' => 'Hiểu thế nào là tình bạn đẹp và cách giữ gìn tình bạn.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Tình bạn đẹp xây dựng trên sự chân thành, tôn trọng, giúp đỡ lẫn nhau; tránh nói xấu, ích kỷ, ghen tị.',
                ],
            ],
            'kn-gd-atm-1' => [
                [
                    'title' => 'An toàn trên mạng', 'slug' => 'gd-an-toan-mang',
                    'objective' => 'Biết bảo vệ thông tin cá nhân và ứng xử an toàn khi dùng mạng.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Không chia sẻ địa chỉ, số điện thoại công khai; giữ bí mật mật khẩu; không gặp người lạ quen qua mạng; báo người lớn khi bị bắt nạt mạng.',
                ],
                [
                    'title' => 'Bảo vệ môi trường', 'slug' => 'gd-bao-ve-moi-truong',
                    'objective' => 'Nêu được các hành động bảo vệ môi trường và phân loại rác đơn giản.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Bảo vệ môi trường từ việc nhỏ: bỏ rác đúng nơi, trồng cây xanh, tiết kiệm nước, hạn chế đồ nhựa dùng một lần.',
                ],
            ],
        ];
        foreach ($lessons as $skillSlug => $list) {
            $skill = Skill::where('slug', $skillSlug)->firstOrFail();
            $order = 1;
            foreach ($list as $l) {
                $lesson = Lesson::updateOrCreate(
                    ['slug' => $l['slug']],
                    [
                        'skill_id' => $skill->id,
                        'title' => $l['title'],
                        'objective' => $l['objective'],
                        'difficulty' => $l['difficulty'],
                        'duration_minutes' => $l['duration_minutes'],
                        'instructions' => $l['instructions'],
                        'sort_order' => $order++,
                        'status' => 'published',
                        'is_demo' => true,
                    ]
                );
                $this->lessonBySlug[$l['slug']] = $lesson;
            }
        }

        $this->seedQuyenCuaTreEm();
        $this->seedBonPhanCuaTreEm();
        $this->seedTonTrongKyLuat();
        $this->seedTietKiem();
        $this->seedTinhBan();
        $this->seedAnToanMang();
        $this->seedBaoVeMoiTruong();
    }

    // ---------------- helpers ----------------

    private function lesson(string $slug): Lesson
    {
        if (! isset($this->lessonBySlug[$slug])) {
            $this->lessonBySlug[$slug] = Lesson::where('slug', $slug)->firstOrFail();
        }
        return $this->lessonBySlug[$slug];
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

    // ================= BÀI HỌC =================

    private function seedQuyenCuaTreEm(): void
    {
        $L = 'gd-quyen-cua-tre-em';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Quyền nào sau đây là quyền của trẻ em?',
                ['Được đi học', 'Phải đi làm kiếm tiền', 'Được hút thuốc lá', 'Được lái xe máy'], 0,
                'Trẻ em có quyền được giáo dục, tức là được đi học.');
            $this->quiz($L, 'Trẻ em có quyền được chăm sóc sức khoẻ, nghĩa là?',
                ['Được khám chữa bệnh khi ốm đau', 'Phải tự mua thuốc', 'Không cần tiêm phòng', 'Tự chữa bệnh ở nhà'], 0,
                'Quyền được chăm sóc sức khoẻ: trẻ em được khám chữa bệnh, tiêm phòng đầy đủ.');
            $this->quiz($L, 'Khi bị xâm hại hoặc gặp nguy hiểm, trẻ em nên làm gì?',
                ['Báo cho cha mẹ, thầy cô hoặc gọi tổng đài 111', 'Giữ im lặng một mình', 'Tự giải quyết bằng bạo lực', 'Bỏ nhà đi nơi khác'], 0,
                'Hãy báo ngay cho người lớn tin cậy hoặc gọi tổng đài bảo vệ trẻ em 111.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi quyền với nội dung của nó.',
                [['Quyền được sống', 'Được bảo vệ tính mạng, sức khoẻ'],
                 ['Quyền được giáo dục', 'Được đi học, được học tập'],
                 ['Quyền vui chơi, giải trí', 'Được tham gia hoạt động lành mạnh']],
                'Các quyền cơ bản: được sống, được giáo dục, được vui chơi giải trí.');
            $this->matching($L, 'Nối mỗi quyền với biểu hiện cụ thể.',
                [['Được khai sinh', 'Có họ tên và quốc tịch'],
                 ['Được chăm sóc sức khoẻ', 'Được khám chữa bệnh, tiêm phòng'],
                 ['Được bảo vệ', 'Không bị bạo lực, xâm hại, bỏ rơi']],
                'Khai sinh cho trẻ họ tên, quốc tịch; bảo vệ trẻ khỏi mọi xâm hại.');
            $this->matching($L, 'Nối mỗi tình huống với người có thể giúp đỡ.',
                [['Bị bạn bắt nạt ở trường', 'Thầy cô giáo'],
                 ['Gặp nguy hiểm khẩn cấp', 'Tổng đài 111'],
                 ['Ốm đau ở nhà', 'Cha mẹ']],
                'Gặp khó khăn hãy tìm người lớn tin cậy; trường hợp khẩn cấp gọi 111.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm QUYỀN CỦA TRẺ EM hoặc KHÔNG PHẢI.',
                [['Được đi học', 'Quyền của trẻ em'],
                 ['Được vui chơi', 'Quyền của trẻ em'],
                 ['Phải lao động nặng nhọc', 'Không phải quyền'],
                 ['Bị đánh đập', 'Không phải quyền']],
                'Được đi học, được vui chơi là quyền. Lao động nặng nhọc, bị đánh đập là vi phạm quyền trẻ em.');
            $this->sortQ($L, 'Thấy bạn bị bắt nạt, kéo mỗi hành động vào nhóm NÊN LÀM hoặc KHÔNG NÊN LÀM.',
                [['Báo cho thầy cô', 'Nên làm'],
                 ['Gọi người lớn giúp đỡ', 'Nên làm'],
                 ['Đứng xem và cổ vũ', 'Không nên làm'],
                 ['Quay clip đăng lên mạng', 'Không nên làm']],
                'Nên báo thầy cô, gọi người lớn. Không đứng xem, không quay clip đăng mạng.');
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm QUYỀN hoặc BỔN PHẬN của trẻ em.',
                [['Được chăm sóc sức khoẻ', 'Quyền'],
                 ['Được vui chơi', 'Quyền'],
                 ['Chăm chỉ học tập', 'Bổn phận'],
                 ['Kính trọng ông bà cha mẹ', 'Bổn phận']],
                'Được chăm sóc, được vui chơi là quyền; chăm học, hiếu thảo là bổn phận.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trẻ em có quyền được đi học, đó là quyền được ___ dục.', [[0, 'giáo']],
                'Quyền được giáo dục: mọi trẻ em đều được đi học.');
            $this->fill($L, 'Khi gặp nguy hiểm, trẻ em có thể gọi tổng đài ___ để được giúp đỡ.', [[0, '111']],
                '111 là tổng đài quốc gia bảo vệ trẻ em, hoạt động 24/7 và miễn phí.');
            $this->fill($L, 'Mọi trẻ em đều có quyền được ___ vệ khỏi bạo lực và xâm hại.', [[0, 'bảo']],
                'Quyền được bảo vệ: không ai được xâm hại, bạo lực với trẻ em.');
        }
    }

    private function seedBonPhanCuaTreEm(): void
    {
        $L = 'gd-bon-phan-cua-tre-em';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bổn phận nào của trẻ em đối với gia đình?',
                ['Kính trọng ông bà, cha mẹ', 'Đòi mua đồ chơi đắt tiền', 'Bỏ nhà đi chơi', 'Không nghe lời cha mẹ'], 0,
                'Trẻ em có bổn phận kính trọng, hiếu thảo với ông bà, cha mẹ.');
            $this->quiz($L, 'Đối với thầy cô giáo, học sinh cần làm gì?',
                ['Lễ phép, vâng lời', 'Cãi lại thầy cô', 'Nói dối thầy cô', 'Trốn học'], 0,
                'Học sinh phải lễ phép, tôn trọng và vâng lời thầy cô giáo.');
            $this->quiz($L, 'Bổn phận của trẻ em đối với đất nước là gì?',
                ['Yêu quê hương, học tập tốt', 'Xả rác bừa bãi', 'Vi phạm pháp luật', 'Không quan tâm việc chung'], 0,
                'Trẻ em cần yêu quê hương đất nước, chăm học để sau này xây dựng đất nước.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đối tượng với bổn phận tương ứng.',
                [['Với ông bà cha mẹ', 'Kính trọng, hiếu thảo'],
                 ['Với thầy cô', 'Lễ phép, chăm học'],
                 ['Với bạn bè', 'Đoàn kết, giúp đỡ']],
                'Hiếu thảo với cha mẹ, lễ phép với thầy cô, đoàn kết với bạn bè.');
            $this->matching($L, 'Nối mỗi nơi với bổn phận của trẻ em ở đó.',
                [['Ở nhà', 'Giúp đỡ việc nhà vừa sức'],
                 ['Ở trường', 'Chấp hành nội quy'],
                 ['Ngoài xã hội', 'Tôn trọng pháp luật']],
                'Ở đâu cũng có bổn phận: nhà giúp việc vừa sức, trường chấp hành nội quy, xã hội tôn trọng pháp luật.');
            $this->matching($L, 'Nối mỗi phẩm chất với biểu hiện của nó.',
                [['Hiếu thảo', 'Chăm sóc, vâng lời cha mẹ'],
                 ['Chăm chỉ', 'Cố gắng trong học tập'],
                 ['Trung thực', 'Thật thà trong thi cử']],
                'Hiếu thảo, chăm chỉ, trung thực là những phẩm chất tốt của trẻ em.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BỔN PHẬN hoặc KHÔNG PHẢI BỔN PHẬN.',
                [['Chăm chỉ học tập', 'Bổn phận'],
                 ['Giúp đỡ cha mẹ việc nhà', 'Bổn phận'],
                 ['Trốn học đi chơi', 'Không phải bổn phận'],
                 ['Nói dối thầy cô', 'Không phải bổn phận']],
                'Chăm học, giúp cha mẹ là bổn phận. Trốn học, nói dối là việc sai trái.');
            $this->sortQ($L, 'Kéo mỗi lời nói vào nhóm LỄ PHÉP hoặc THIẾU LỄ PHÉP.',
                [['"Con chào cô ạ!"', 'Lễ phép'],
                 ['"Em cảm ơn anh!"', 'Lễ phép'],
                 ['"Kệ tao!"', 'Thiếu lễ phép'],
                 ['"Im đi!"', 'Thiếu lễ phép']],
                'Nói năng lễ phép thể hiện sự tôn trọng người khác.');
            $this->sortQ($L, 'Kéo mỗi việc làm ở trường vào nhóm NÊN LÀM hoặc KHÔNG NÊN.',
                [['Xếp hàng khi ra chơi', 'Nên làm'],
                 ['Giữ trật tự trong lớp', 'Nên làm'],
                 ['Chạy nhảy ở hành lang', 'Không nên'],
                 ['Vẽ bậy lên bàn ghế', 'Không nên']],
                'Học sinh nên xếp hàng, giữ trật tự; không chạy nhảy hành lang, không vẽ bậy.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trẻ em có bổn phận kính trọng, hiếu thảo với ông bà, ___ mẹ.', [[0, 'cha']],
                'Hiếu thảo với ông bà, cha mẹ là bổn phận đầu tiên của trẻ em.');
            $this->fill($L, 'Học sinh cần ___ phép với thầy cô giáo.', [[0, 'lễ']],
                'Lễ phép là thái độ tôn trọng thầy cô.');
            $this->fill($L, 'Bổn phận của trẻ em là chăm chỉ ___ tập, rèn luyện đạo đức.', [[0, 'học']],
                'Học tập tốt là bổn phận và cũng là quyền lợi của trẻ em.');
        }
    }

    private function seedTonTrongKyLuat(): void
    {
        $L = 'gd-ton-trong-ky-luat';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Biểu hiện nào thể hiện tôn trọng kỷ luật ở trường?',
                ['Đi học đúng giờ', 'Đi học muộn', 'Nói chuyện riêng trong giờ', 'Xả rác trong lớp'], 0,
                'Đi học đúng giờ là biểu hiện cơ bản của tôn trọng kỷ luật.');
            $this->quiz($L, 'Khi xếp hàng, em cần làm gì?',
                ['Đứng đúng vị trí, giữ trật tự', 'Chen lấn lên trước', 'Đùa giỡn ồn ào', 'Bỏ hàng đi chơi'], 0,
                'Xếp hàng ngay ngắn, không chen lấn là giữ kỷ luật nơi công cộng.');
            $this->quiz($L, 'Vì sao chúng ta cần kỷ luật?',
                ['Giúp mọi hoạt động nền nếp, hiệu quả', 'Để bị phạt', 'Để khoe với bạn bè', 'Kỷ luật không có tác dụng gì'], 0,
                'Kỷ luật giúp tập thể hoạt động nền nếp, đạt hiệu quả cao.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi việc làm với ý nghĩa kỷ luật của nó.',
                [['Đi học đúng giờ', 'Kỷ luật về thời gian'],
                 ['Xếp hàng ngay ngắn', 'Kỷ luật nơi công cộng'],
                 ['Giữ im lặng trong giờ học', 'Kỷ luật trong lớp học']],
                'Kỷ luật thể hiện ở thời gian, nơi công cộng và trong lớp học.');
            $this->matching($L, 'Nối mỗi khái niệm với cách hiểu đúng.',
                [['Kỷ luật', 'Tự giác chấp hành quy định chung'],
                 ['Nội quy', 'Quy định của trường, lớp'],
                 ['Tự giác', 'Thực hiện mà không cần nhắc nhở']],
                'Kỷ luật tốt nhất là tự giác, không cần ai nhắc nhở.');
            $this->matching($L, 'Nối mỗi tín hiệu đèn giao thông với hành động đúng.',
                [['Đèn đỏ', 'Dừng lại'],
                 ['Đèn xanh', 'Được đi'],
                 ['Đèn vàng', 'Đi chậm, chuẩn bị dừng']],
                'Chấp hành đèn tín hiệu là kỷ luật khi tham gia giao thông.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm CÓ KỶ LUẬT hoặc THIẾU KỶ LUẬT.',
                [['Đi học đúng giờ', 'Có kỷ luật'],
                 ['Xếp hàng ngay ngắn', 'Có kỷ luật'],
                 ['Chen lấn xô đẩy', 'Thiếu kỷ luật'],
                 ['Nói leo trong giờ học', 'Thiếu kỷ luật']],
                'Đúng giờ, xếp hàng là có kỷ luật; chen lấn, nói leo là thiếu kỷ luật.');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm Ở TRƯỜNG hoặc Ở NHÀ.',
                [['Mặc đồng phục', 'Ở trường'],
                 ['Chào cờ sáng thứ hai', 'Ở trường'],
                 ['Dậy đúng giờ', 'Ở nhà'],
                 ['Phụ giúp cha mẹ', 'Ở nhà']],
                'Kỷ luật cần thực hiện ở mọi nơi: trường học và gia đình.');
            $this->sortQ($L, 'Kéo mỗi hành động giao thông vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Đội mũ bảo hiểm', 'Nên'],
                 ['Đi đúng phần đường', 'Nên'],
                 ['Vượt đèn đỏ', 'Không nên'],
                 ['Đùa giỡn trên đường', 'Không nên']],
                'Đội mũ bảo hiểm, đi đúng phần đường; tuyệt đối không vượt đèn đỏ.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đi học đúng giờ là biểu hiện của tôn trọng ___ luật.', [[0, 'kỷ']],
                'Kỷ luật bắt đầu từ những việc nhỏ như đúng giờ.');
            $this->fill($L, 'Khi đèn đỏ, người tham gia giao thông phải ___ lại.', [[0, 'dừng']],
                'Dừng lại khi đèn đỏ là quy tắc giao thông cơ bản.');
            $this->fill($L, 'Kỷ luật giúp mọi hoạt động trở nên nền nếp và ___ quả.', [[0, 'hiệu']],
                'Tập thể có kỷ luật làm việc hiệu quả hơn.');
        }
    }

    private function seedTietKiem(): void
    {
        $L = 'gd-tiet-kiem';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hành động nào thể hiện tiết kiệm điện?',
                ['Tắt đèn khi ra khỏi phòng', 'Bật điều hoà cả ngày', 'Mở đèn sáng cả đêm', 'Dùng nhiều thiết bị cùng lúc'], 0,
                'Tắt thiết bị điện khi không dùng là tiết kiệm điện.');
            $this->quiz($L, 'Để tiết kiệm tiền, em nên làm gì?',
                ['Bỏ ống heo, để dành', 'Mua đồ chơi mỗi ngày', 'Ăn quà vặt hết tiền', 'Mua đồ không cần thiết'], 0,
                'Bỏ ống heo là cách tiết kiệm tiền đơn giản và hiệu quả.');
            $this->quiz($L, 'Vì sao chúng ta cần tiết kiệm?',
                ['Tài nguyên có hạn, cần dùng hợp lí', 'Để khoe với bạn bè', 'Vì bị ép buộc', 'Tiết kiệm là không cần thiết'], 0,
                'Tài nguyên thiên nhiên có hạn nên phải sử dụng hợp lí, tránh lãng phí.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi việc tiết kiệm với cách làm cụ thể.',
                [['Tiết kiệm điện', 'Tắt thiết bị khi không dùng'],
                 ['Tiết kiệm nước', 'Khoá vòi sau khi dùng'],
                 ['Tiết kiệm thời gian', 'Lập kế hoạch học tập']],
                'Tiết kiệm thể hiện ở điện, nước, thời gian hằng ngày.');
            $this->matching($L, 'Nối mỗi thứ cần tiết kiệm với cách làm đúng.',
                [['Tiết kiệm tiền', 'Bỏ ống heo'],
                 ['Tiết kiệm giấy', 'Dùng cả hai mặt giấy'],
                 ['Tiết kiệm thức ăn', 'Lấy vừa đủ ăn']],
                'Bỏ ống heo, dùng giấy hai mặt, lấy thức ăn vừa đủ.');
            $this->matching($L, 'Nối mỗi khái niệm với cách hiểu đúng.',
                [['Lãng phí', 'Dùng bừa bãi, không hợp lí'],
                 ['Tiết kiệm', 'Dùng hợp lí, đúng mức'],
                 ['Cần kiệm', 'Chăm chỉ và tiết kiệm']],
                'Tiết kiệm ngược với lãng phí; cần kiệm là vừa chăm chỉ vừa tiết kiệm.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TIẾT KIỆM hoặc LÃNG PHÍ.',
                [['Tắt quạt khi ra ngoài', 'Tiết kiệm'],
                 ['Khoá vòi nước sau khi dùng', 'Tiết kiệm'],
                 ['Xả nước ào ào khi đánh răng', 'Lãng phí'],
                 ['Bật đèn sáng cả ngày', 'Lãng phí']],
                'Tắt quạt, khoá vòi nước là tiết kiệm; xả nước ào ào, bật đèn cả ngày là lãng phí.');
            $this->sortQ($L, 'Có tiền mừng tuổi, kéo mỗi cách dùng vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Bỏ ống heo', 'Nên'],
                 ['Mua sách vở', 'Nên'],
                 ['Mua bánh kẹo ăn hết một lần', 'Không nên'],
                 ['Tiêu hết vào game', 'Không nên']],
                'Nên để dành hoặc mua đồ dùng học tập; không tiêu xài hoang phí.');
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm TIẾT KIỆM ĐƯỢC hoặc KHÔNG LẤY LẠI ĐƯỢC.',
                [['Điện', 'Tiết kiệm được'],
                 ['Nước', 'Tiết kiệm được'],
                 ['Tiền bạc', 'Tiết kiệm được'],
                 ['Thời gian đã qua', 'Không lấy lại được']],
                'Điện, nước, tiền tiết kiệm được; thời gian đã qua không lấy lại được nên càng phải quý trọng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tắt đèn, tắt quạt khi ra khỏi phòng là tiết kiệm ___.', [[0, 'điện']],
                'Tiết kiệm điện vừa giảm chi phí vừa bảo vệ môi trường.');
            $this->fill($L, 'Lấy thức ăn vừa đủ, không bỏ thừa là tiết kiệm lương ___.', [[0, 'thực']],
                'Lương thực làm ra rất vất vả nên không được lãng phí.');
            $this->fill($L, 'Tiền mừng tuổi nên bỏ ___ heo để dành.', [[0, 'ống']],
                'Bỏ ống heo là thói quen tiết kiệm tốt của học sinh.');
        }
    }

    private function seedTinhBan(): void
    {
        $L = 'gd-tinh-ban';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tình bạn đẹp cần những đức tính nào?',
                ['Chân thành, giúp đỡ nhau', 'Ích kỷ', 'Nói xấu sau lưng', 'Ghen tị'], 0,
                'Tình bạn đẹp xây dựng trên sự chân thành và giúp đỡ lẫn nhau.');
            $this->quiz($L, 'Khi bạn mắc lỗi, em nên làm gì?',
                ['Góp ý chân thành với bạn', 'Chê cười bạn trước lớp', 'Mách lẻo để bạn bị phạt', 'Bỏ mặc bạn'], 0,
                'Bạn tốt góp ý chân thành để bạn sửa lỗi, không chê cười hay bỏ mặc.');
            $this->quiz($L, 'Hành động nào phá hoại tình bạn?',
                ['Nói xấu bạn sau lưng', 'Giúp bạn học bài', 'Chia sẻ đồ dùng', 'Động viên bạn'], 0,
                'Nói xấu sau lưng làm mất lòng tin, phá hoại tình bạn.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đức tính với biểu hiện trong tình bạn.',
                [['Chân thành', 'Không dối trá với bạn'],
                 ['Giúp đỡ', 'Sẵn sàng khi bạn khó khăn'],
                 ['Bao dung', 'Tha thứ lỗi lầm của bạn']],
                'Chân thành, giúp đỡ, bao dung là nền tảng của tình bạn đẹp.');
            $this->matching($L, 'Nối mỗi kiểu bạn với cách nhận biết.',
                [['Bạn tốt', 'Động viên khi bạn buồn'],
                 ['Bạn xấu', 'Xúi giục làm việc sai trái'],
                 ['Người bạn chân chính', 'Không bỏ rơi lúc hoạn nạn']],
                'Bạn tốt động viên, giúp đỡ; tránh xa bạn xấu xúi giục làm điều sai.');
            $this->matching($L, 'Nối mỗi việc làm với ý nghĩa của nó.',
                [['Chia sẻ', 'Cùng nhau vui buồn'],
                 ['Hợp tác', 'Cùng làm việc nhóm'],
                 ['Tôn trọng', 'Lắng nghe ý kiến của bạn']],
                'Chia sẻ, hợp tác, tôn trọng giúp tình bạn bền chặt.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BẠN TỐT hoặc BẠN XẤU.',
                [['Giúp bạn học bài', 'Bạn tốt'],
                 ['Chia sẻ đồ dùng', 'Bạn tốt'],
                 ['Xúi bạn trốn học', 'Bạn xấu'],
                 ['Nói xấu bạn', 'Bạn xấu']],
                'Bạn tốt giúp đỡ, chia sẻ; bạn xấu xúi giục, nói xấu.');
            $this->sortQ($L, 'Khi bạn buồn, kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['An ủi, động viên', 'Nên'],
                 ['Lắng nghe bạn tâm sự', 'Nên'],
                 ['Cười nhạo bạn', 'Không nên'],
                 ['Bỏ mặc bạn', 'Không nên']],
                'Khi bạn buồn nên an ủi, lắng nghe; không cười nhạo hay bỏ mặc.');
            $this->sortQ($L, 'Kéo mỗi lời nói vào nhóm LỜI HAY hoặc LỜI LÀM TỔN THƯƠNG.',
                [['"Cố lên, mình tin bạn!"', 'Lời hay'],
                 ['"Bạn làm tốt lắm!"', 'Lời hay'],
                 ['"Đồ ngốc!"', 'Tổn thương'],
                 ['"Chơi với cậu chán lắm!"', 'Tổn thương']],
                'Lời động viên sưởi ấm lòng bạn; lời miệt thị gây tổn thương.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tình bạn đẹp được xây dựng trên sự chân thành và ___ trọng lẫn nhau.', [[0, 'tôn']],
                'Tôn trọng là nền tảng không thể thiếu của tình bạn.');
            $this->fill($L, 'Khi bạn gặp khó khăn, em nên sẵn sàng ___ đỡ.', [[0, 'giúp']],
                'Giúp đỡ bạn lúc khó khăn là biểu hiện của tình bạn đẹp.');
            $this->fill($L, 'Người bạn tốt không bao giờ ___ xấu bạn sau lưng.', [[0, 'nói']],
                'Nói xấu sau lưng phá vỡ lòng tin trong tình bạn.');
        }
    }

    private function seedAnToanMang(): void
    {
        $L = 'gd-an-toan-mang';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thông tin nào KHÔNG nên chia sẻ công khai trên mạng?',
                ['Địa chỉ nhà, số điện thoại', 'Sở thích đọc sách', 'Món ăn yêu thích', 'Đội bóng yêu thích'], 0,
                'Địa chỉ nhà, số điện thoại là thông tin cá nhân, kẻ xấu có thể lợi dụng.', 'trung_binh');
            $this->quiz($L, 'Khi người lạ quen qua mạng rủ gặp mặt, em nên làm gì?',
                ['Từ chối và báo cho cha mẹ', 'Đồng ý đi một mình', 'Cho họ địa chỉ nhà', 'Gửi ảnh cá nhân'], 0,
                'Tuyệt đối không gặp riêng người lạ quen qua mạng; báo ngay cho cha mẹ.', 'trung_binh');
            $this->quiz($L, 'Khi bị trêu chọc, bắt nạt trên mạng, em nên làm gì?',
                ['Chụp lại bằng chứng và báo người lớn', 'Chửi lại thậm tệ', 'Im lặng chịu đựng', 'Trả đũa tương tự'], 0,
                'Lưu bằng chứng, chặn kẻ bắt nạt và báo cho cha mẹ, thầy cô.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thứ với cách bảo vệ đúng trên mạng.',
                [['Mật khẩu', 'Giữ bí mật, không cho ai biết'],
                 ['Thông tin cá nhân', 'Không đăng công khai'],
                 ['Người lạ trên mạng', 'Không tin tưởng vội vàng']],
                'Mật khẩu giữ kín; thông tin cá nhân không đăng công khai; cảnh giác với người lạ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tình huống mạng với cách xử lí đúng.',
                [['Bị bắt nạt trên mạng', 'Báo cho cha mẹ, thầy cô'],
                 ['Gặp tin giả', 'Kiểm chứng trước khi chia sẻ'],
                 ['Quảng cáo "trúng thưởng" lạ', 'Không bấm vào']],
                'Bắt nạt mạng thì báo người lớn; tin giả thì kiểm chứng; link lạ thì không bấm.', 'trung_binh');
            $this->matching($L, 'Nối mỗi việc dùng mạng với cách dùng đúng.',
                [['Chơi game', 'Có giờ giấc, không sa đà'],
                 ['Học trực tuyến', 'Tận dụng mạng để học tập'],
                 ['Kết bạn trên mạng', 'Chỉ với người quen biết ngoài đời']],
                'Dùng mạng có giờ giấc, ưu tiên học tập, cẩn trọng khi kết bạn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm trên mạng vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Dùng mạng để học bài', 'An toàn'],
                 ['Hỏi ý kiến cha mẹ khi thấy nghi ngờ', 'An toàn'],
                 ['Chia sẻ địa chỉ nhà', 'Nguy hiểm'],
                 ['Kết bạn với người lạ', 'Nguy hiểm']],
                'Học bài, hỏi ý kiến cha mẹ là an toàn; chia sẻ địa chỉ, kết bạn người lạ là nguy hiểm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Đặt mật khẩu mạnh', 'Nên'],
                 ['Đăng xuất sau khi dùng máy công cộng', 'Nên'],
                 ['Dùng chung mật khẩu với bạn', 'Không nên'],
                 ['Bấm vào link lạ "trúng thưởng"', 'Không nên']],
                'Nên đặt mật khẩu mạnh và đăng xuất máy công cộng; không chia sẻ mật khẩu, không bấm link lạ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tin tức vào nhóm ĐÁNG TIN hoặc TIN GIẢ.',
                [['Tin từ báo chí chính thống', 'Đáng tin'],
                 ['Thông báo từ nhà trường', 'Đáng tin'],
                 ['Tin "trúng thưởng" yêu cầu chuyển tiền', 'Tin giả'],
                 ['Tin đồn không rõ nguồn gốc', 'Tin giả']],
                'Tin chính thống, từ nhà trường thì đáng tin; tin trúng thưởng, tin đồn vô căn cứ là tin giả.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Không chia sẻ địa chỉ nhà, số điện thoại ___ khai trên mạng.', [[0, 'công']],
                'Thông tin cá nhân đăng công khai rất dễ bị kẻ xấu lợi dụng.', 'trung_binh');
            $this->fill($L, 'Mật khẩu tài khoản phải giữ ___ mật, không cho người khác biết.', [[0, 'bí']],
                'Mật khẩu là chìa khoá tài khoản, phải giữ bí mật tuyệt đối.', 'trung_binh');
            $this->fill($L, 'Khi bị bắt nạt trên mạng, hãy báo ngay cho cha mẹ hoặc ___ cô.', [[0, 'thầy']],
                'Đừng im lặng chịu đựng; hãy nhờ người lớn giúp đỡ.', 'trung_binh');
        }
    }

    private function seedBaoVeMoiTruong(): void
    {
        $L = 'gd-bao-ve-moi-truong';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hành động nào bảo vệ môi trường?',
                ['Bỏ rác đúng nơi quy định', 'Xả rác xuống sông', 'Đốt rác bừa bãi', 'Chặt cây xanh'], 0,
                'Bỏ rác đúng nơi quy định giữ đường phố, trường học sạch đẹp.');
            $this->quiz($L, 'Để giảm rác thải nhựa, em nên làm gì?',
                ['Dùng bình nước cá nhân', 'Dùng túi ni-lông mỗi ngày', 'Vứt chai nhựa xuống cống', 'Đốt túi ni-lông'], 0,
                'Dùng bình nước cá nhân, làn đi chợ giúp giảm đồ nhựa dùng một lần.');
            $this->quiz($L, 'Trồng cây xanh có tác dụng gì?',
                ['Làm sạch không khí, cho bóng mát', 'Gây ô nhiễm môi trường', 'Làm đất khô cằn', 'Thu hút sâu bệnh'], 0,
                'Cây xanh làm sạch không khí, cho bóng mát, chống xói mòn đất.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hành động với lợi ích của nó.',
                [['Bỏ rác đúng nơi', 'Giữ đường phố sạch đẹp'],
                 ['Trồng cây xanh', 'Làm sạch không khí'],
                 ['Tiết kiệm nước', 'Bảo vệ nguồn nước']],
                'Việc nhỏ mỗi ngày góp phần bảo vệ môi trường lớn.');
            $this->matching($L, 'Nối mỗi loại rác với ví dụ của nó.',
                [['Rác hữu cơ', 'Lá cây, thức ăn thừa'],
                 ['Rác tái chế', 'Chai nhựa, giấy báo cũ'],
                 ['Rác nguy hại', 'Pin cũ, bóng đèn vỡ']],
                'Phân loại rác giúp tái chế và xử lí đúng cách.');
            $this->matching($L, 'Nối mỗi khái niệm với nội dung của nó.',
                [['3R', 'Giảm thiểu – Tái sử dụng – Tái chế'],
                 ['Ngày Môi trường thế giới', '5/6 hằng năm'],
                 ['Trái Đất', 'Ngôi nhà chung của chúng ta']],
                '3R: giảm thiểu, tái sử dụng, tái chế. Ngày Môi trường thế giới 5/6.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BẢO VỆ hoặc PHÁ HOẠI môi trường.',
                [['Trồng cây xanh', 'Bảo vệ'],
                 ['Bỏ rác đúng nơi', 'Bảo vệ'],
                 ['Xả rác xuống sông', 'Phá hoại'],
                 ['Chặt phá rừng', 'Phá hoại']],
                'Trồng cây, bỏ rác đúng nơi là bảo vệ; xả rác, chặt phá rừng là phá hoại.');
            $this->sortQ($L, 'Kéo mỗi loại rác vào nhóm TÁI CHẾ ĐƯỢC hoặc KHÔNG TÁI CHẾ.',
                [['Chai nhựa', 'Tái chế được'],
                 ['Giấy báo cũ', 'Tái chế được'],
                 ['Thức ăn thừa', 'Không tái chế'],
                 ['Pin cũ', 'Không tái chế'],
                 ['Vỏ lon', 'Tái chế được'],
                 ['Túi ni-lông bẩn', 'Không tái chế']],
                'Chai nhựa, giấy, vỏ lon tái chế được; thức ăn thừa, pin cũ thì không.');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Dùng làn đi chợ', 'Nên'],
                 ['Tắt vòi nước khi đánh răng', 'Nên'],
                 ['Dùng ống hút nhựa một lần', 'Không nên'],
                 ['Vứt pin vào thùng rác thường', 'Không nên']],
                'Nên dùng làn, tiết kiệm nước; hạn chế nhựa một lần, pin cũ bỏ đúng nơi thu gom.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bỏ rác đúng ___ quy định là hành động bảo vệ môi trường.', [[0, 'nơi']],
                'Bỏ rác đúng nơi giữ trường lớp, đường phố sạch đẹp.');
            $this->fill($L, 'Trồng nhiều cây xanh giúp làm ___ không khí.', [[0, 'sạch']],
                'Cây xanh hấp thụ khí độc, nhả ô-xy làm sạch không khí.');
            $this->fill($L, 'Ngày Môi trường thế giới là ngày 5 tháng ___ hằng năm.', [[0, '6']],
                'Ngày 5/6 hằng năm là Ngày Môi trường thế giới.');
        }
    }
}
