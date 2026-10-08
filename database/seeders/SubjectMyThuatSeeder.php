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
 * Seeder môn Mỹ thuật (tự chạy độc lập được):
 * subject → 3 topics → 5 skills → 6 bài học → mỗi bài 12 câu
 * (3 quiz + 3 matching + 3 sort + 3 fill), tất cả is_demo = true.
 * Nội dung TIẾNG VIỆT TỰ VIẾT 100%, bám chương trình Mỹ thuật lớp 6–9.
 */
class SubjectMyThuatSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(['slug' => 'my-thuat'], [
            'name' => 'Mỹ thuật',
            'icon' => '🎨',
            'color' => '#eab308',
            'description' => 'Học mỹ thuật qua game: màu sắc, bố cục và tranh dân gian.',
            'sort_order' => 12,
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
                'name' => 'Màu sắc và pha màu', 'slug' => 'mt-mau-sac', 'icon' => '🎨',
                'description' => 'Nhận biết màu nóng, màu lạnh và cách pha màu cơ bản.',
                'grade_min' => 6, 'grade_max' => 7,
                'skills' => [
                    [
                        'name' => 'Nhận biết màu nóng – màu lạnh', 'slug' => 'kn-mt-nhan-biet-mau',
                        'description' => 'Phân biệt nhóm màu nóng và nhóm màu lạnh qua tranh vẽ.',
                        'lessons' => [
                            [
                                'title' => 'Màu nóng và màu lạnh', 'slug' => 'mt-mau-nong-mau-lanh',
                                'objective' => 'Phân biệt được nhóm màu nóng và nhóm màu lạnh, nêu được cảm giác mỗi nhóm gợi ra.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Đọc kỹ câu hỏi, chọn đáp án đúng nhất. Hãy tưởng tượng màu sắc trong tranh khi trả lời.',
                            ],
                            [
                                'title' => 'Pha màu cơ bản', 'slug' => 'mt-pha-mau-co-ban',
                                'objective' => 'Biết công thức pha các màu thứ cấp từ màu cơ bản và cách làm màu sáng hoặc tối hơn.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                                'instructions' => 'Nhớ lại ba màu cơ bản: đỏ, vàng, xanh dương. Từ đó suy ra các màu pha được.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Đường nét và bố cục', 'slug' => 'mt-duong-net-bo-cuc', 'icon' => '✏️',
                'description' => 'Đường nét, hình khối và cách sắp xếp bố cục trong tranh.',
                'grade_min' => 7, 'grade_max' => 8,
                'skills' => [
                    [
                        'name' => 'Đường nét và hình khối', 'slug' => 'kn-mt-duong-net',
                        'description' => 'Nhận biết các loại đường nét, hình phẳng và hình khối.',
                        'lessons' => [
                            [
                                'title' => 'Đường nét và hình khối', 'slug' => 'mt-duong-net-hinh-khoi',
                                'objective' => 'Phân biệt các loại đường nét và cảm giác chúng gợi ra; phân biệt hình phẳng với hình khối.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                                'instructions' => 'Quan sát đường nét trong tranh: thẳng hay cong, nét đậm hay nhạt, rồi suy nghĩ cảm giác chúng mang lại.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Bố cục trong tranh', 'slug' => 'kn-mt-bo-cuc',
                        'description' => 'Hiểu bố cục chính – phụ, gần – xa trong tranh.',
                        'lessons' => [
                            [
                                'title' => 'Bố cục trong tranh', 'slug' => 'mt-bo-cuc-tranh',
                                'objective' => 'Hiểu cách sắp xếp nhân vật chính – phụ, vật gần – xa để bức tranh cân đối, sinh động.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Hãy tưởng tượng mình đang sắp xếp các nhân vật trong một bức tranh: ai là chính, ai là phụ, cái gì gần, cái gì xa.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Tranh dân gian Đông Hồ', 'slug' => 'mt-tranh-dan-gian', 'icon' => '🖼️',
                'description' => 'Tìm hiểu tranh dân gian Đông Hồ và vẽ tranh theo phong cách dân gian.',
                'grade_min' => 8, 'grade_max' => 9,
                'skills' => [
                    [
                        'name' => 'Tìm hiểu tranh Đông Hồ', 'slug' => 'kn-mt-tim-hieu-dong-ho',
                        'description' => 'Biết nguồn gốc, chất liệu và các bức tranh Đông Hồ nổi tiếng.',
                        'lessons' => [
                            [
                                'title' => 'Tranh Đông Hồ nổi tiếng', 'slug' => 'mt-tranh-dong-ho-noi-tieng',
                                'objective' => 'Biết nguồn gốc, chất liệu làm tranh Đông Hồ và nội dung một số bức tranh tiêu biểu.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Tranh Đông Hồ là dòng tranh dân gian nổi tiếng của Việt Nam. Hãy tìm hiểu thật kỹ từng bức tranh nhé.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Vẽ tranh phong cách dân gian', 'slug' => 'kn-mt-ve-phong-cach-dan-gian',
                        'description' => 'Vận dụng đường nét, màu sắc dân gian để lên ý tưởng bức tranh của mình.',
                        'lessons' => [
                            [
                                'title' => 'Lên ý tưởng tranh dân gian', 'slug' => 'mt-y-tuong-tranh-dan-gian',
                                'objective' => 'Biết chọn đề tài, bố cục và màu sắc theo phong cách tranh dân gian cho bức tranh của mình.',
                                'difficulty' => 'kho', 'duration_minutes' => 12,
                                'instructions' => 'Bây giờ đến lượt em làm họa sĩ dân gian! Hãy vận dụng những gì đã học để lên ý tưởng cho bức tranh của mình.',
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
        // ---------- Bài 1: Màu nóng và màu lạnh ----------
        $this->quiz('mt-mau-nong-mau-lanh', [
            ['Những màu nào sau đây thuộc nhóm màu nóng?',
                ['Đỏ, cam, vàng', 'Xanh dương, xanh lá, tím', 'Đen, trắng, xám', 'Nâu, be, ghi'], 0,
                'Màu nóng gồm các màu gợi cảm giác ấm áp, rực rỡ như đỏ, cam, vàng.'],
            ['Màu xanh dương thường gợi cho người xem cảm giác gì?',
                ['Mát mẻ, yên bình', 'Nóng bức, sôi động', 'Buồn ngủ, uể oải', 'Căng thẳng, lo lắng'], 0,
                'Xanh dương là màu lạnh, gợi cảm giác mát mẻ, yên bình như bầu trời và mặt biển.'],
            ['Khi vẽ cảnh hoàng hôn rực rỡ, họa sĩ thường dùng nhiều màu nào?',
                ['Màu nóng như đỏ, cam, vàng', 'Màu lạnh như xanh dương, tím', 'Chỉ dùng màu đen và trắng', 'Chỉ dùng màu xám'], 0,
                'Hoàng hôn rực rỡ có ánh mặt trời đỏ rực nên họa sĩ dùng nhiều màu nóng để diễn tả.'],
        ]);
        $this->matching('mt-mau-nong-mau-lanh', [
            ['Nối mỗi màu với nhóm màu đúng của nó.',
                [['Đỏ', 'Màu nóng'], ['Cam', 'Màu nóng'], ['Xanh dương', 'Màu lạnh'], ['Tím', 'Màu lạnh']],
                'Đỏ, cam thuộc nhóm màu nóng; xanh dương, tím thuộc nhóm màu lạnh.'],
            ['Nối mỗi màu với cảm giác nó thường gợi ra.',
                [['Màu đỏ', 'Sôi động, mạnh mẽ'], ['Màu vàng', 'Tươi vui, rực rỡ'], ['Màu xanh lá', 'Tươi mát, gần gũi thiên nhiên']],
                'Màu đỏ sôi động, màu vàng tươi vui, màu xanh lá gợi thiên nhiên tươi mát.'],
            ['Nối mỗi bức tranh với nhóm màu phù hợp.',
                [['Tranh sa mạc nắng gắt', 'Màu nóng'], ['Tranh biển đêm trăng', 'Màu lạnh'], ['Tranh lễ hội đèn lồng', 'Màu nóng']],
                'Sa mạc nắng gắt và lễ hội đèn lồng hợp màu nóng; biển đêm hợp màu lạnh.'],
        ]);
        $this->sortQ('mt-mau-nong-mau-lanh', [
            ['Xếp các màu vào đúng nhóm: Màu nóng / Màu lạnh.',
                [['Đỏ', 'Màu nóng'], ['Cam', 'Màu nóng'], ['Vàng', 'Màu nóng'], ['Xanh dương', 'Màu lạnh'], ['Xanh lá cây', 'Màu lạnh'], ['Tím', 'Màu lạnh']],
                'Màu nóng: đỏ, cam, vàng. Màu lạnh: xanh dương, xanh lá cây, tím.'],
            ['Xếp các từ chỉ cảm giác vào nhóm màu gợi ra chúng.',
                [['Ấm áp', 'Màu nóng'], ['Rực rỡ', 'Màu nóng'], ['Mát mẻ', 'Màu lạnh'], ['Yên bình', 'Màu lạnh']],
                'Màu nóng gợi ấm áp, rực rỡ; màu lạnh gợi mát mẻ, yên bình.'],
            ['Xếp các chủ đề tranh vào nhóm màu nên dùng nhiều.',
                [['Bình minh trên biển', 'Màu nóng'], ['Lễ hội mùa xuân', 'Màu nóng'], ['Đêm trăng trên sông', 'Màu lạnh'], ['Mưa rào mùa hạ', 'Màu lạnh']],
                'Bình minh và lễ hội hợp màu nóng; đêm trăng và mưa rào hợp màu lạnh.'],
        ]);
        $this->fill('mt-mau-nong-mau-lanh', [
            ['Màu đỏ, màu cam và màu vàng thuộc nhóm màu ___.',
                [[0, 'nóng']], 'Đỏ, cam, vàng là ba màu nóng cơ bản.'],
            ['Màu xanh dương, xanh lá cây và màu tím thuộc nhóm màu ___.',
                [[0, 'lạnh']], 'Xanh dương, xanh lá cây, tím là ba màu lạnh cơ bản.'],
            ['Tranh vẽ sa mạc nắng gắt nên dùng nhiều màu ___.',
                [[0, 'nóng']], 'Sa mạc nắng gắt gợi cảm giác nóng bức nên dùng nhiều màu nóng.'],
        ]);

        // ---------- Bài 2: Pha màu cơ bản ----------
        $this->quiz('mt-pha-mau-co-ban', [
            ['Màu đỏ pha với màu vàng sẽ được màu gì?',
                ['Màu cam', 'Màu tím', 'Màu xanh lá', 'Màu nâu'], 0,
                'Đỏ + vàng = cam.', 'trung_binh'],
            ['Màu xanh dương pha với màu vàng sẽ được màu gì?',
                ['Màu xanh lá cây', 'Màu cam', 'Màu tím', 'Màu hồng'], 0,
                'Xanh dương + vàng = xanh lá cây.', 'trung_binh'],
            ['Muốn màu đỏ nhạt hơn (thành màu hồng), ta pha thêm màu gì?',
                ['Màu trắng', 'Màu đen', 'Màu xanh', 'Màu vàng'], 0,
                'Pha thêm trắng làm màu sáng và nhạt hơn; pha thêm đen làm màu tối hơn.', 'trung_binh'],
        ]);
        $this->matching('mt-pha-mau-co-ban', [
            ['Nối mỗi cặp màu cơ bản với màu pha được.',
                [['Đỏ + vàng', 'Màu cam'], ['Vàng + xanh dương', 'Màu xanh lá cây'], ['Đỏ + xanh dương', 'Màu tím']],
                'Đỏ + vàng = cam; vàng + xanh dương = xanh lá cây; đỏ + xanh dương = tím.', 'trung_binh'],
            ['Nối mỗi màu với cách pha của nó.',
                [['Màu cam', 'Đỏ pha vàng'], ['Màu tím', 'Đỏ pha xanh dương'], ['Màu xanh lá cây', 'Vàng pha xanh dương']],
                'Cam pha từ đỏ và vàng; tím pha từ đỏ và xanh dương; xanh lá cây pha từ vàng và xanh dương.', 'trung_binh'],
            ['Nối mỗi thao tác với kết quả.',
                [['Thêm màu trắng', 'Màu nhạt và sáng hơn'], ['Thêm màu đen', 'Màu tối và đậm hơn'], ['Thêm nước (màu nước)', 'Màu loãng và trong hơn']],
                'Trắng làm màu sáng nhạt hơn, đen làm màu tối đậm hơn, thêm nước làm màu loãng trong hơn.', 'trung_binh'],
        ]);
        $this->sortQ('mt-pha-mau-co-ban', [
            ['Xếp các màu vào nhóm: Màu cơ bản / Màu pha được.',
                [['Đỏ', 'Màu cơ bản'], ['Vàng', 'Màu cơ bản'], ['Xanh dương', 'Màu cơ bản'], ['Cam', 'Màu pha được'], ['Tím', 'Màu pha được'], ['Xanh lá cây', 'Màu pha được']],
                'Ba màu cơ bản là đỏ, vàng, xanh dương; cam, tím, xanh lá cây pha từ chúng.', 'trung_binh'],
            ['Xếp các thao tác vào nhóm: Làm màu sáng hơn / Làm màu tối hơn.',
                [['Pha thêm màu trắng', 'Làm màu sáng hơn'], ['Pha thêm nước', 'Làm màu sáng hơn'], ['Pha thêm màu đen', 'Làm màu tối hơn'], ['Vẽ nhiều lớp chồng nhau', 'Làm màu tối hơn']],
                'Thêm trắng hoặc nước làm màu sáng hơn; thêm đen hoặc chồng nhiều lớp làm màu tối hơn.', 'trung_binh'],
            ['Xếp các màu pha vào đúng công thức tạo ra chúng.',
                [['Cam', 'Đỏ + vàng'], ['Tím', 'Đỏ + xanh dương'], ['Xanh lá cây', 'Vàng + xanh dương']],
                'Cam = đỏ + vàng; tím = đỏ + xanh dương; xanh lá cây = vàng + xanh dương.', 'trung_binh'],
        ]);
        $this->fill('mt-pha-mau-co-ban', [
            ['Đỏ pha với vàng được màu ___.',
                [[0, 'cam']], 'Đỏ + vàng = cam.', 'trung_binh'],
            ['Ba màu cơ bản là đỏ, vàng và màu ___.',
                [[0, 'xanh dương']], 'Ba màu cơ bản: đỏ, vàng, xanh dương.', 'trung_binh'],
            ['Muốn màu tối và đậm hơn, ta pha thêm màu ___.',
                [[0, 'đen']], 'Pha thêm đen làm màu tối và đậm hơn.', 'trung_binh'],
        ]);

        // ---------- Bài 3: Đường nét và hình khối ----------
        $this->quiz('mt-duong-net-hinh-khoi', [
            ['Đường cong thường gợi cảm giác gì?',
                ['Mềm mại, uyển chuyển', 'Cứng rắn, dứt khoát', 'Nguy hiểm, đáng sợ', 'Lạnh lùng, xa cách'], 0,
                'Đường cong gợi sự mềm mại, uyển chuyển như dòng sông, làn tóc.', 'trung_binh'],
            ['Đường thẳng đứng thường gợi cảm giác gì?',
                ['Vững chắc, hiên ngang', 'Buồn bã, yếu đuối', 'Hỗn loạn, rối rắm', 'Mờ ảo, xa xăm'], 0,
                'Đường thẳng đứng như thân cây, cột nhà gợi sự vững chắc, hiên ngang.', 'trung_binh'],
            ['Hình nào sau đây là hình khối (có chiều sâu)?',
                ['Hình lập phương', 'Hình vuông', 'Hình tròn', 'Hình tam giác'], 0,
                'Hình lập phương là hình khối có chiều dài, rộng, cao; hình vuông, tròn, tam giác là hình phẳng.', 'trung_binh'],
        ]);
        $this->matching('mt-duong-net-hinh-khoi', [
            ['Nối mỗi loại đường nét với cảm giác nó gợi ra.',
                [['Đường thẳng đứng', 'Vững chắc'], ['Đường cong', 'Mềm mại'], ['Đường zíc zắc', 'Sôi động, gấp gáp']],
                'Đường thẳng đứng gợi vững chắc, đường cong gợi mềm mại, đường zíc zắc gợi sôi động.', 'trung_binh'],
            ['Nối mỗi hình phẳng với hình khối tương ứng.',
                [['Hình vuông', 'Hình lập phương'], ['Hình tròn', 'Hình cầu'], ['Hình tam giác', 'Hình chóp']],
                'Hình vuông mở rộng thành hình lập phương, hình tròn thành hình cầu, hình tam giác thành hình chóp.', 'trung_binh'],
            ['Nối mỗi vật với đường nét đặc trưng khi vẽ.',
                [['Dòng sông', 'Đường cong'], ['Cột điện', 'Đường thẳng đứng'], ['Tia chớp', 'Đường zíc zắc']],
                'Dòng sông vẽ bằng đường cong, cột điện bằng đường thẳng đứng, tia chớp bằng đường zíc zắc.', 'trung_binh'],
        ]);
        $this->sortQ('mt-duong-net-hinh-khoi', [
            ['Xếp các đường nét vào nhóm: Đường thẳng / Đường cong.',
                [['Đường ngang', 'Đường thẳng'], ['Đường dọc', 'Đường thẳng'], ['Đường chéo', 'Đường thẳng'], ['Đường lượn sóng', 'Đường cong'], ['Đường xoắn ốc', 'Đường cong']],
                'Đường ngang, dọc, chéo là đường thẳng; đường lượn sóng, xoắn ốc là đường cong.', 'trung_binh'],
            ['Xếp các hình vào nhóm: Hình phẳng / Hình khối.',
                [['Hình vuông', 'Hình phẳng'], ['Hình tròn', 'Hình phẳng'], ['Hình lập phương', 'Hình khối'], ['Hình cầu', 'Hình khối']],
                'Hình vuông, hình tròn là hình phẳng; hình lập phương, hình cầu là hình khối.', 'trung_binh'],
            ['Xếp các vật vào nhóm theo đường nét đặc trưng.',
                [['Thân cây', 'Đường thẳng'], ['Mái nhà', 'Đường thẳng'], ['Con suối', 'Đường cong'], ['Làn khói', 'Đường cong']],
                'Thân cây, mái nhà dùng đường thẳng; con suối, làn khói dùng đường cong.', 'trung_binh'],
        ]);
        $this->fill('mt-duong-net-hinh-khoi', [
            ['Đường ___ gợi cảm giác mềm mại, uyển chuyển.',
                [[0, 'cong']], 'Đường cong gợi cảm giác mềm mại, uyển chuyển.', 'trung_binh'],
            ['Hình lập phương là hình ___, còn hình vuông là hình phẳng.',
                [[0, 'khối']], 'Hình lập phương có chiều sâu nên là hình khối.', 'trung_binh'],
            ['Nét vẽ ___ thường dùng để diễn tả tia chớp, sự chuyển động nhanh.',
                [[0, 'zíc zắc']], 'Đường zíc zắc gợi sự sôi động, chuyển động nhanh như tia chớp.', 'trung_binh'],
        ]);

        // ---------- Bài 4: Bố cục trong tranh ----------
        $this->quiz('mt-bo-cuc-tranh', [
            ['Trong tranh, nhân vật chính thường được đặt ở đâu?',
                ['Vị trí nổi bật, dễ nhìn thấy nhất', 'Góc khuất khó thấy', 'Ngoài lề bức tranh', 'Chỗ nào cũng được'], 0,
                'Nhân vật chính cần đặt ở vị trí nổi bật để thu hút ánh nhìn đầu tiên.', 'trung_binh'],
            ['Khi vẽ phong cảnh, vật ở gần thường được vẽ như thế nào so với vật ở xa?',
                ['To và rõ hơn', 'Nhỏ và mờ hơn', 'Bằng nhau', 'Không vẽ vật ở gần'], 0,
                'Vật gần vẽ to, rõ nét; vật xa vẽ nhỏ, mờ dần — tạo chiều sâu cho tranh.', 'trung_binh'],
            ['Bố cục tranh cân đối nghĩa là gì?',
                ['Các mảng hình, màu sắc sắp xếp hài hòa, không lệch hẳn một bên', 'Vẽ thật nhiều chi tiết', 'Chỉ dùng một màu duy nhất', 'Vẽ càng to càng tốt'], 0,
                'Bố cục cân đối là sự sắp xếp hài hòa các mảng hình và màu sắc trong khung tranh.', 'trung_binh'],
        ]);
        $this->matching('mt-bo-cuc-tranh', [
            ['Nối mỗi yếu tố với vai trò trong bố cục.',
                [['Nhân vật chính', 'Đặt ở vị trí nổi bật'], ['Nhân vật phụ', 'Làm nổi bật nhân vật chính'], ['Phông nền', 'Tạo không gian cho câu chuyện']],
                'Nhân vật chính ở vị trí nổi bật, nhân vật phụ làm nền, phông nền tạo không gian.', 'trung_binh'],
            ['Nối mỗi vị trí với cách vẽ.',
                [['Vật ở gần', 'Vẽ to, rõ nét'], ['Vật ở xa', 'Vẽ nhỏ, mờ dần'], ['Đường chân trời', 'Đặt khoảng 1/3 chiều cao tranh']],
                'Vật gần to rõ, vật xa nhỏ mờ, đường chân trời đặt khoảng 1/3 chiều cao tranh.', 'trung_binh'],
            ['Nối mỗi bức tranh với điểm cần chú ý về bố cục.',
                [['Tranh gia đình sum họp', 'Mọi người quây quần quanh trung tâm'], ['Tranh phong cảnh núi', 'Núi xa mờ, cây gần rõ'], ['Tranh lễ hội', 'Nhân vật chính nổi bật giữa đám đông']],
                'Mỗi chủ đề có cách sắp xếp riêng nhưng đều cần nhân vật chính nổi bật và không gian hài hòa.', 'trung_binh'],
        ]);
        $this->sortQ('mt-bo-cuc-tranh', [
            ['Xếp các yếu tố vào nhóm: Nên nhấn mạnh / Nên tiết chế.',
                [['Nhân vật chính', 'Nên nhấn mạnh'], ['Màu sắc chủ đạo', 'Nên nhấn mạnh'], ['Chi tiết rườm rà', 'Nên tiết chế'], ['Nhân vật phụ quá to', 'Nên tiết chế']],
                'Nhấn mạnh nhân vật chính và màu chủ đạo; tiết chế chi tiết rườm rà.', 'trung_binh'],
            ['Xếp các vật vào nhóm: Vật gần / Vật xa.',
                [['Cây cổ thụ trước sân', 'Vật gần'], ['Đàn trâu đang gặm cỏ', 'Vật gần'], ['Ngọn núi phía chân trời', 'Vật xa'], ['Mây trắng trên trời', 'Vật xa']],
                'Cây trước sân, đàn trâu là vật gần; núi xa, mây trời là vật xa.', 'trung_binh'],
            ['Xếp các cách sắp xếp vào nhóm: Bố cục tốt / Bố cục chưa tốt.',
                [['Nhân vật chính ở vị trí nổi bật', 'Bố cục tốt'], ['Màu sắc hài hòa', 'Bố cục tốt'], ['Mọi vật dồn hết một góc', 'Bố cục chưa tốt'], ['Tranh trống một nửa, đặc một nửa', 'Bố cục chưa tốt']],
                'Bố cục tốt: nhân vật chính nổi bật, màu sắc hài hòa, phân bố đều khắp tranh.', 'trung_binh'],
        ]);
        $this->fill('mt-bo-cuc-tranh', [
            ['Trong tranh, nhân vật ___ thường được đặt ở vị trí nổi bật nhất.',
                [[0, 'chính']], 'Nhân vật chính luôn được đặt ở vị trí nổi bật nhất.', 'trung_binh'],
            ['Vật ở gần vẽ ___, vật ở xa vẽ nhỏ và mờ dần.',
                [[0, 'to']], 'Vật gần vẽ to và rõ nét hơn vật xa.', 'trung_binh'],
            ['Bố cục ___ là sự sắp xếp hài hòa các mảng hình và màu sắc trong tranh.',
                [[0, 'cân đối']], 'Bố cục cân đối tạo sự hài hòa cho bức tranh.', 'trung_binh'],
        ]);

        // ---------- Bài 5: Tranh Đông Hồ nổi tiếng ----------
        $this->quiz('mt-tranh-dong-ho-noi-tieng', [
            ['Tranh Đông Hồ có nguồn gốc từ làng nghề nào?',
                ['Làng Đông Hồ, Bắc Ninh', 'Làng Bát Tràng, Hà Nội', 'Làng Vạn Phúc, Hà Đông', 'Làng Phước Kiều, Quảng Nam'], 0,
                'Tranh Đông Hồ ra đời tại làng Đông Hồ, huyện Thuận Thành, tỉnh Bắc Ninh.', 'trung_binh'],
            ['Giấy điệp dùng in tranh Đông Hồ được làm từ nguyên liệu gì?',
                ['Vỏ con điệp nghiền mịn', 'Vỏ cây dó', 'Rơm rạ', 'Lá chuối khô'], 0,
                'Giấy điệp quét lớp bột từ vỏ điệp nghiền mịn nên óng ánh, bền màu.', 'trung_binh'],
            ['Bức tranh "Đám cưới chuột" thể hiện điều gì?',
                ['Đám rước dâu vui nhộn của loài chuột, châm biếm thói hối lộ', 'Cảnh chuột phá hoại mùa màng', 'Cảnh mèo bắt chuột', 'Cảnh chuột đào hang'], 0,
                'Đám cưới chuột vẽ đoàn rước dâu của chuột, vừa vui nhộn vừa châm biếm thói đút lót, hối lộ.', 'kho'],
        ]);
        $this->matching('mt-tranh-dong-ho-noi-tieng', [
            ['Nối mỗi bức tranh Đông Hồ với nội dung của nó.',
                [['Gà trống', 'Chú gà trống oai vệ, tượng trưng cho sự sung túc'], ['Đám cưới chuột', 'Đoàn rước dâu vui nhộn, châm biếm thói hối lộ'], ['Hứng dừa', 'Chú bé trèo hái dừa, thể hiện ước mơ đỗ đạt']],
                'Gà trống tượng trưng sung túc, Đám cưới chuột châm biếm thói hối lộ, Hứng dừa thể hiện ước mơ đỗ đạt.', 'trung_binh'],
            ['Nối mỗi màu trong tranh Đông Hồ với nguyên liệu tạo màu.',
                [['Màu đen', 'Than xoan'], ['Màu đỏ', 'Gỗ vang'], ['Màu vàng', 'Hoa hòe']],
                'Màu tranh Đông Hồ lấy từ thiên nhiên: than xoan cho màu đen, gỗ vang cho màu đỏ, hoa hòe cho màu vàng.', 'kho'],
            ['Nối mỗi đặc điểm với giá trị của tranh Đông Hồ.',
                [['In bằng ván khắc gỗ', 'Giữ được nét mộc mạc, dân gian'], ['Màu từ thiên nhiên', 'Bền màu, an toàn'], ['Đề tài gần gũi', 'Phản ánh đời sống người nông dân']],
                'Tranh Đông Hồ in ván khắc gỗ, màu thiên nhiên, đề tài gần gũi đời sống nông dân.', 'trung_binh'],
        ]);
        $this->sortQ('mt-tranh-dong-ho-noi-tieng', [
            ['Xếp các bức tranh vào nhóm: Tranh Đông Hồ / Không phải tranh Đông Hồ.',
                [['Gà trống', 'Tranh Đông Hồ'], ['Đám cưới chuột', 'Tranh Đông Hồ'], ['Hứng dừa', 'Tranh Đông Hồ'], ['Mona Lisa', 'Không phải tranh Đông Hồ']],
                'Gà trống, Đám cưới chuột, Hứng dừa là tranh Đông Hồ; Mona Lisa là tranh phương Tây.', 'trung_binh'],
            ['Xếp các nguyên liệu vào nhóm: Dùng làm màu vẽ / Không dùng làm màu vẽ.',
                [['Than xoan', 'Dùng làm màu vẽ'], ['Hoa hòe', 'Dùng làm màu vẽ'], ['Gỗ vang', 'Dùng làm màu vẽ'], ['Nhựa đường', 'Không dùng làm màu vẽ']],
                'Tranh Đông Hồ dùng màu thiên nhiên: than xoan, hoa hòe, gỗ vang.', 'kho'],
            ['Xếp các ý nghĩa vào nhóm: Có trong tranh Đông Hồ / Không có.',
                [['Châm biếm thói hư', 'Có trong tranh Đông Hồ'], ['Ước mơ sung túc', 'Có trong tranh Đông Hồ'], ['Ca ngợi chiến tranh', 'Không có']],
                'Tranh Đông Hồ hướng tới đời sống bình dị, ước mơ sung túc và châm biếm thói hư.', 'trung_binh'],
        ]);
        $this->fill('mt-tranh-dong-ho-noi-tieng', [
            ['Tranh Đông Hồ ra đời tại làng Đông Hồ, tỉnh ___.',
                [[0, 'Bắc Ninh']], 'Làng Đông Hồ thuộc huyện Thuận Thành, tỉnh Bắc Ninh.', 'trung_binh'],
            ['Giấy in tranh Đông Hồ gọi là giấy ___, óng ánh nhờ bột vỏ điệp.',
                [[0, 'điệp']], 'Giấy điệp được quét bột vỏ điệp nghiền mịn nên óng ánh.', 'kho'],
            ['Màu đen trong tranh Đông Hồ được làm từ ___.',
                [[0, 'than xoan']], 'Than xoan đốt từ gỗ xoan cho màu đen trong tranh Đông Hồ.', 'kho'],
        ]);

        // ---------- Bài 6: Lên ý tưởng tranh dân gian ----------
        $this->quiz('mt-y-tuong-tranh-dan-gian', [
            ['Muốn vẽ tranh phong cách dân gian, em nên chọn đề tài nào?',
                ['Cảnh sinh hoạt làng quê quen thuộc', 'Tàu vũ trụ ngoài hành tinh', 'Người máy tương lai', 'Cảnh thành phố hiện đại'], 0,
                'Tranh dân gian lấy đề tài từ đời sống làng quê gần gũi: chợ quê, trâu bò, lễ hội.', 'trung_binh'],
            ['Đường nét trong tranh dân gian thường có đặc điểm gì?',
                ['Mộc mạc, rõ ràng, ít chi tiết rườm rà', 'Phức tạp, nhiều chi tiết nhỏ', 'Mờ ảo, khó nhìn', 'Chỉ dùng một nét duy nhất'], 0,
                'Tranh dân gian dùng nét mộc mạc, rõ ràng, dễ hiểu với mọi người.', 'trung_binh'],
            ['Khi tô màu tranh phong cách dân gian, em nên ưu tiên màu nào?',
                ['Màu tươi, rực rỡ từ thiên nhiên', 'Chỉ màu xám và đen', 'Màu neon chói lóa', 'Không tô màu'], 0,
                'Tranh dân gian dùng màu tươi rực rỡ lấy từ thiên nhiên như đỏ, vàng, xanh lá.', 'trung_binh'],
        ]);
        $this->matching('mt-y-tuong-tranh-dan-gian', [
            ['Nối mỗi bước với việc cần làm khi vẽ tranh dân gian.',
                [['Chọn đề tài', 'Cảnh làng quê, lễ hội quen thuộc'], ['Phác bố cục', 'Nhân vật chính ở vị trí nổi bật'], ['Tô màu', 'Dùng màu tươi, rực rỡ']],
                'Vẽ tranh dân gian: chọn đề tài quen thuộc → phác bố cục → tô màu tươi rực rỡ.', 'trung_binh'],
            ['Nối mỗi đề tài với cảm xúc muốn thể hiện.',
                [['Chợ quê ngày Tết', 'Vui tươi, nhộn nhịp'], ['Mẹ ru con', 'Ấm áp, yêu thương'], ['Trâu về chuồng chiều', 'Bình yên, thân thuộc']],
                'Mỗi đề tài gợi một cảm xúc: chợ Tết vui tươi, mẹ ru con ấm áp, trâu về bình yên.', 'kho'],
            ['Nối mỗi lỗi thường gặp với cách khắc phục.',
                [['Nhân vật chính bị che khuất', 'Đặt nhân vật chính ở vị trí nổi bật'], ['Màu sắc lộn xộn', 'Chọn 3–4 màu chủ đạo'], ['Tranh thiếu chiều sâu', 'Vật gần vẽ to, vật xa vẽ nhỏ']],
                'Khắc phục lỗi: nhân vật chính nổi bật, giới hạn màu chủ đạo, tạo chiều sâu gần – xa.', 'kho'],
        ]);
        $this->sortQ('mt-y-tuong-tranh-dan-gian', [
            ['Xếp các đề tài vào nhóm: Phù hợp tranh dân gian / Chưa phù hợp.',
                [['Lễ hội làng', 'Phù hợp tranh dân gian'], ['Chợ quê', 'Phù hợp tranh dân gian'], ['Phi thuyền không gian', 'Chưa phù hợp'], ['Robot chiến đấu', 'Chưa phù hợp']],
                'Tranh dân gian hợp với đề tài làng quê; đề tài viễn tưởng, hiện đại chưa phù hợp.', 'trung_binh'],
            ['Xếp các việc làm vào nhóm: Nên làm trước / Nên làm sau.',
                [['Chọn đề tài', 'Nên làm trước'], ['Phác thảo bố cục', 'Nên làm trước'], ['Tô màu chi tiết', 'Nên làm sau'], ['Viền nét hoàn thiện', 'Nên làm sau']],
                'Vẽ tranh theo trình tự: chọn đề tài → phác bố cục → tô màu → viền nét hoàn thiện.', 'trung_binh'],
            ['Xếp các màu vào nhóm: Nên dùng nhiều / Nên hạn chế.',
                [['Đỏ tươi', 'Nên dùng nhiều'], ['Vàng rực', 'Nên dùng nhiều'], ['Xám xịt', 'Nên hạn chế'], ['Đen kịt', 'Nên hạn chế']],
                'Tranh dân gian ưu tiên màu tươi rực rỡ, hạn chế màu xám xịt, tối tăm.', 'trung_binh'],
        ]);
        $this->fill('mt-y-tuong-tranh-dan-gian', [
            ['Tranh dân gian thường lấy đề tài từ đời sống ___ gần gũi.',
                [[0, 'làng quê']], 'Tranh dân gian lấy đề tài từ đời sống làng quê gần gũi.', 'trung_binh'],
            ['Khi vẽ, em nên phác ___ trước rồi mới tô màu chi tiết.',
                [[0, 'bố cục']], 'Phác bố cục trước giúp bức tranh cân đối rồi mới tô màu.', 'kho'],
            ['Tranh dân gian dùng đường nét ___, rõ ràng, dễ hiểu.',
                [[0, 'mộc mạc']], 'Đường nét mộc mạc, rõ ràng là đặc trưng của tranh dân gian.', 'trung_binh'],
        ]);
    }
}
