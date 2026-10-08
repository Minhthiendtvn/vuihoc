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
 * Môn Ngữ văn — nội dung TỰ VIẾT 100% tiếng Việt, bám chương trình lớp 6-9.
 * Idempotent: Subject/Topic/Skill/Lesson dùng updateOrCreate theo slug;
 * câu hỏi bỏ qua khi đã tồn tại theo (lesson_id, game_type).
 */
class SubjectNguVanSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(
            ['slug' => 'ngu-van'],
            [
                'name' => 'Ngữ văn',
                'icon' => '📝',
                'color' => '#ec4899',
                'description' => 'Học ngữ văn qua game: từ loại, biện pháp tu từ và văn miêu tả.',
                'sort_order' => 6,
                'is_published' => true,
                'is_demo' => true,
            ]
        );

        $topics = [
            ['name' => 'Từ loại', 'slug' => 'nv-tu-loai', 'icon' => '🔤',
             'description' => 'Danh từ, động từ, tính từ và cách dùng trong câu.',
             'grade_min' => 6, 'grade_max' => 7],
            ['name' => 'Biện pháp tu từ', 'slug' => 'nv-bien-phap-tu-tu', 'icon' => '✨',
             'description' => 'So sánh, nhân hoá, ẩn dụ, hoán dụ.',
             'grade_min' => 7, 'grade_max' => 8],
            ['name' => 'Văn miêu tả và dấu câu', 'slug' => 'nv-van-mieu-ta', 'icon' => '📝',
             'description' => 'Bố cục bài văn miêu tả và cách dùng dấu câu.',
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
            'nv-tu-loai' => [
                ['name' => 'Nhận diện từ loại', 'slug' => 'kn-nv-tu-loai-1'],
                ['name' => 'Luyện tập từ loại trong câu', 'slug' => 'kn-nv-tu-loai-2'],
            ],
            'nv-bien-phap-tu-tu' => [
                ['name' => 'Nhận biết biện pháp tu từ', 'slug' => 'kn-nv-tu-tu-1'],
            ],
            'nv-van-mieu-ta' => [
                ['name' => 'Viết văn miêu tả', 'slug' => 'kn-nv-mieu-ta-1'],
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
            'kn-nv-tu-loai-1' => [
                [
                    'title' => 'Danh từ và động từ', 'slug' => 'nv-danh-tu-dong-tu',
                    'objective' => 'Nhận biết được danh từ và động từ, xác định đúng từ loại của từ trong câu.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Danh từ chỉ người, vật, hiện tượng. Động từ chỉ hành động, trạng thái của người và sự vật.',
                ],
                [
                    'title' => 'Tính từ và câu văn', 'slug' => 'nv-tinh-tu',
                    'objective' => 'Nhận biết được tính từ và xác định chủ ngữ, vị ngữ trong câu đơn.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Tính từ chỉ đặc điểm, tính chất. Câu đơn gồm chủ ngữ (ai? cái gì?) và vị ngữ (làm gì? thế nào?).',
                ],
            ],
            'kn-nv-tu-loai-2' => [
                [
                    'title' => 'Phân biệt từ loại trong câu', 'slug' => 'nv-phan-biet-tu-loai',
                    'objective' => 'Phân biệt được danh từ, động từ, tính từ khi đặt trong câu cụ thể.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Đọc cả câu rồi mới xác định từ loại: cùng một hình thức từ có thể thuộc từ loại khác nhau tùy vị trí.',
                ],
            ],
            'kn-nv-tu-tu-1' => [
                [
                    'title' => 'So sánh và nhân hoá', 'slug' => 'nv-so-sanh-nhan-hoa',
                    'objective' => 'Nhận biết được biện pháp so sánh và nhân hoá qua dấu hiệu từ ngữ.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'So sánh thường có từ "như, tựa, giống". Nhân hoá gán hành động, đặc điểm của con người cho sự vật.',
                ],
                [
                    'title' => 'Ẩn dụ và hoán dụ', 'slug' => 'nv-an-du-hoan-du',
                    'objective' => 'Phân biệt được ẩn dụ và hoán dụ, hiểu cách gọi tên sự vật theo lối tu từ.',
                    'difficulty' => 'kho', 'duration_minutes' => 12,
                    'instructions' => 'Ẩn dụ: gọi sự vật này bằng tên sự vật khác có nét tương đồng (không dùng từ so sánh). Hoán dụ: lấy cái gần gũi để chỉ cái cần nói (bộ phận chỉ toàn thể, vật chứa chỉ vật bị chứa...).',
                ],
            ],
            'kn-nv-mieu-ta-1' => [
                [
                    'title' => 'Bố cục bài văn miêu tả', 'slug' => 'nv-bo-cuc-mieu-ta',
                    'objective' => 'Nắm được bố cục ba phần và cách dùng từ ngữ gợi hình, gợi cảm trong văn miêu tả.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Bài văn miêu tả gồm mở bài, thân bài, kết bài. Chú ý từ ngữ gợi hình, gợi cảm và trình tự miêu tả hợp lí.',
                ],
                [
                    'title' => 'Dấu câu trong văn bản', 'slug' => 'nv-dau-cau',
                    'objective' => 'Dùng đúng dấu chấm, dấu hỏi, dấu than, dấu phẩy, dấu hai chấm trong câu.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Dấu chấm kết thúc câu kể, dấu hỏi kết thúc câu hỏi, dấu than kết thúc câu cảm thán, dấu phẩy ngăn cách các bộ phận trong câu.',
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

        $this->seedDanhTuDongTu();
        $this->seedTinhTu();
        $this->seedPhanBietTuLoai();
        $this->seedSoSanhNhanHoa();
        $this->seedAnDuHoanDu();
        $this->seedBoCucMieuTa();
        $this->seedDauCau();
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

    private function seedDanhTuDongTu(): void
    {
        $L = 'nv-danh-tu-dong-tu';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trong câu "Mẹ nấu cơm.", từ nào là động từ?',
                ['Mẹ', 'nấu', 'cơm', 'cả câu'], 1,
                '"Mẹ" và "cơm" là danh từ (chỉ người, chỉ vật). "Nấu" chỉ hành động nên là động từ.');
            $this->quiz($L, 'Từ nào sau đây là danh từ chỉ người?',
                ['chạy', 'bàn', 'học sinh', 'vui'], 2,
                '"Học sinh" chỉ người nên là danh từ. "Chạy" là động từ, "vui" là tính từ.');
            $this->quiz($L, 'Trong các từ "đỏ, bay, cánh diều, hạnh phúc", từ nào là động từ?',
                ['đỏ', 'bay', 'cánh diều', 'hạnh phúc'], 1,
                '"Bay" chỉ hoạt động nên là động từ. "Đỏ" là tính từ, "cánh diều" và "hạnh phúc" là danh từ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với từ loại của nó.',
                [['trường học', 'Danh từ'], ['chạy nhảy', 'Động từ'], ['xanh mát', 'Tính từ'], ['bơi lội', 'Động từ']],
                'Trường học chỉ nơi chốn (danh từ); chạy nhảy, bơi lội chỉ hành động (động từ); xanh mát chỉ đặc điểm (tính từ).');
            $this->matching($L, 'Nối mỗi từ gạch chân với từ loại của nó.',
                [['Từ "cây bàng" trong câu "Cây bàng rất cao."', 'Danh từ'],
                 ['Từ "hót" trong câu "Chim hót líu lo."', 'Động từ'],
                 ['Từ "rực rỡ" trong câu "Hoa nở rực rỡ."', 'Tính từ']],
                'Cây bàng chỉ vật (danh từ); hót chỉ hoạt động (động từ); rực rỡ chỉ đặc điểm (tính từ).');
            $this->matching($L, 'Nối mỗi từ với nhóm từ loại đúng.',
                [['sách vở', 'Danh từ'], ['mưa rơi', 'Động từ'], ['tròn xoe', 'Tính từ']],
                'Sách vở chỉ vật (danh từ); mưa rơi chỉ hiện tượng chuyển động (động từ); tròn xoe chỉ hình dáng (tính từ).');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc ĐỘNG TỪ.',
                [['ngôi nhà', 'Danh từ'], ['con mèo', 'Danh từ'], ['ăn cơm', 'Động từ'],
                 ['đọc sách', 'Động từ'], ['xe đạp', 'Danh từ'], ['bơi lội', 'Động từ']],
                'Danh từ chỉ người, vật: ngôi nhà, con mèo, xe đạp. Động từ chỉ hành động: ăn cơm, đọc sách, bơi lội.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TÍNH TỪ hoặc CỤM ĐỘNG TỪ.',
                [['cao lớn', 'Tính từ'], ['hiền lành', 'Tính từ'],
                 ['đọc sách', 'Cụm động từ'], ['ăn cơm', 'Cụm động từ']],
                'Cao lớn, hiền lành chỉ đặc điểm (tính từ). Đọc sách, ăn cơm chỉ hành động (cụm động từ).');
            $this->sortQ($L, 'Kéo mỗi danh từ vào nhóm CHỈ NGƯỜI hoặc CHỈ VẬT.',
                [['thầy giáo', 'Danh từ chỉ người'], ['bác sĩ', 'Danh từ chỉ người'],
                 ['quyển vở', 'Danh từ chỉ vật'], ['cái bàn', 'Danh từ chỉ vật']],
                'Thầy giáo, bác sĩ chỉ người. Quyển vở, cái bàn chỉ vật.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ chỉ người, vật, hiện tượng gọi là ___.', [[0, 'danh từ']],
                'Danh từ dùng để chỉ người, vật, hiện tượng, khái niệm: mẹ, trường học, niềm vui...');
            $this->fill($L, 'Trong câu "Lan đọc sách.", từ "đọc" là ___.', [[0, 'động từ']],
                '"Đọc" chỉ hành động nên là động từ.');
            $this->fill($L, '"Cánh đồng lúa chín ___." Điền từ còn thiếu chỉ màu sắc của lúa chín.', [[0, 'vàng']],
                '"Vàng" chỉ màu sắc, đặc điểm của cánh đồng lúa chín nên là tính từ.');
        }
    }

    private function seedTinhTu(): void
    {
        $L = 'nv-tinh-tu';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Từ nào sau đây là tính từ?',
                ['cao lớn', 'chạy nhảy', 'đọc sách', 'ăn cơm'], 0,
                '"Cao lớn" chỉ đặc điểm nên là tính từ. Các từ còn lại chỉ hành động nên là động từ.');
            $this->quiz($L, 'Trong câu "Trời trong xanh.", vị ngữ là gì?',
                ['Trời', 'trong xanh', 'Trời trong', 'cả câu'], 1,
                'Chủ ngữ trả lời câu hỏi "cái gì?": Trời. Vị ngữ trả lời "thế nào?": trong xanh.');
            $this->quiz($L, 'Câu nào có tính từ chỉ tính cách con người?',
                ['Bé Lan rất hiền lành.', 'Con mèo chạy nhanh.', 'Chim hót líu lo.', 'Cá bơi tung tăng.'], 0,
                '"Hiền lành" chỉ tính cách con người. "Nhanh", "líu lo", "tung tăng" miêu tả hành động.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với từ loại của nó.',
                [['cao lớn', 'Tính từ'], ['chăm chỉ', 'Tính từ'], ['chạy nhanh', 'Cụm động từ'], ['đọc truyện', 'Cụm động từ']],
                'Cao lớn, chăm chỉ chỉ đặc điểm (tính từ). Chạy nhanh, đọc truyện chỉ hành động (cụm động từ).');
            $this->matching($L, 'Nối mỗi câu với thành phần được hỏi.',
                [['Câu "Mẹ nấu cơm." – chủ ngữ là', 'Mẹ'],
                 ['Câu "Em bé cười khúc khích." – vị ngữ là', 'cười khúc khích'],
                 ['Câu "Sông Thu Bồn trong xanh." – chủ ngữ là', 'Sông Thu Bồn']],
                'Chủ ngữ thường đứng đầu câu, chỉ ai/cái gì. Vị ngữ chỉ làm gì, thế nào.');
            $this->matching($L, 'Nối mỗi tính từ với sự vật nó thường miêu tả.',
                [['rực rỡ', 'Hoa'], ['rì rào', 'Gió'], ['mênh mông', 'Biển']],
                'Hoa rực rỡ, gió rì rào, biển mênh mông là những kết hợp quen thuộc.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TÍNH TỪ hoặc DANH TỪ.',
                [['đẹp đẽ', 'Tính từ'], ['ngôi trường', 'Danh từ'],
                 ['vui vẻ', 'Tính từ'], ['bạn bè', 'Danh từ']],
                'Đẹp đẽ, vui vẻ chỉ đặc điểm (tính từ). Ngôi trường, bạn bè chỉ vật, chỉ người (danh từ).');
            $this->sortQ($L, 'Kéo mỗi tính từ vào nhóm CHỈ MÀU SẮC hoặc CHỈ TÍNH CÁCH.',
                [['đỏ rực', 'Màu sắc'], ['xanh biếc', 'Màu sắc'],
                 ['chăm chỉ', 'Tính cách'], ['hiền lành', 'Tính cách']],
                'Đỏ rực, xanh biếc chỉ màu sắc. Chăm chỉ, hiền lành chỉ tính cách con người.');
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm CHỦ NGỮ hoặc VỊ NGỮ.',
                [['Mẹ', 'Chủ ngữ'], ['nấu cơm', 'Vị ngữ'],
                 ['Em bé', 'Chủ ngữ'], ['chạy nhảy', 'Vị ngữ']],
                'Chủ ngữ chỉ ai/cái gì; vị ngữ chỉ làm gì, thế nào.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ chỉ đặc điểm, tính chất của sự vật gọi là ___.', [[0, 'tính từ']],
                'Tính từ miêu tả đặc điểm, tính chất: cao, thấp, đẹp, hiền lành...');
            $this->fill($L, 'Trong câu "Hoa nở rực rỡ.", tính từ là ___.', [[0, 'rực rỡ']],
                '"Rực rỡ" chỉ đặc điểm của hoa đang nở nên là tính từ.');
            $this->fill($L, 'Câu đơn gồm hai thành phần chính là chủ ngữ và ___.', [[0, 'vị ngữ']],
                'Chủ ngữ nêu đối tượng, vị ngữ nêu hoạt động, đặc điểm của đối tượng.');
        }
    }

    private function seedPhanBietTuLoai(): void
    {
        $L = 'nv-phan-biet-tu-loai';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trong câu "Hai chú chim đang hót.", từ "hót" thuộc từ loại nào?',
                ['Danh từ', 'Động từ', 'Tính từ', 'Số từ'], 1,
                '"Hót" chỉ hoạt động của chim nên là động từ.', 'trung_binh');
            $this->quiz($L, 'Cặp từ nào gồm một động từ và một tính từ?',
                ['chạy – nhanh', 'bàn – ghế', 'xanh – đỏ', 'đọc – vở'], 0,
                '"Chạy" chỉ hành động (động từ), "nhanh" chỉ đặc điểm (tính từ).', 'trung_binh');
            $this->quiz($L, 'Trong câu "Cô giáo giảng bài rất hay.", tính từ là từ nào?',
                ['cô giáo', 'giảng', 'bài', 'hay'], 3,
                '"Hay" chỉ đặc điểm của bài giảng nên là tính từ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ loại với định nghĩa đúng.',
                [['Danh từ', 'Chỉ người, vật, hiện tượng'],
                 ['Động từ', 'Chỉ hành động, trạng thái'],
                 ['Tính từ', 'Chỉ đặc điểm, tính chất']],
                'Đây là ba từ loại cơ bản nhất các em cần nắm vững.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ với từ loại của nó trong câu đã cho.',
                [['"quyển sách" trong "Em mua quyển sách."', 'Danh từ'],
                 ['"chạy nhảy" trong "Trẻ em chạy nhảy."', 'Động từ'],
                 ['"vui vẻ" trong "Lớp học vui vẻ."', 'Tính từ'],
                 ['"con đường" trong "Con đường rất dài."', 'Danh từ']],
                'Đọc cả câu rồi xác định: quyển sách chỉ vật, chạy nhảy chỉ hành động, vui vẻ chỉ đặc điểm.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ láy với từ loại của nó.',
                [['lấp lánh', 'Tính từ'], ['rì rào', 'Tính từ'],
                 ['chập chờn', 'Tính từ'], ['thì thầm', 'Động từ']],
                'Lấp lánh, rì rào, chập chờn gợi đặc điểm (tính từ); thì thầm chỉ hành động nói khẽ (động từ).', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc KHÔNG PHẢI DANH TỪ.',
                [['ngôi nhà', 'Danh từ'], ['con sông', 'Danh từ'],
                 ['chạy nhảy', 'Không phải danh từ'], ['vui vẻ', 'Không phải danh từ']],
                'Ngôi nhà, con sông chỉ vật (danh từ). Chạy nhảy là động từ, vui vẻ là tính từ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm ĐỘNG TỪ hoặc TÍNH TỪ.',
                [['ăn cơm', 'Động từ'], ['đọc sách', 'Động từ'],
                 ['xinh đẹp', 'Tính từ'], ['chăm chỉ', 'Tính từ']],
                'Ăn cơm, đọc sách chỉ hành động (động từ). Xinh đẹp, chăm chỉ chỉ đặc điểm (tính từ).', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi danh từ vào nhóm CHỈ NGƯỜI hoặc CHỈ VẬT.',
                [['thầy giáo', 'Chỉ người'], ['bác sĩ', 'Chỉ người'],
                 ['quyển vở', 'Chỉ vật'], ['cái bàn', 'Chỉ vật']],
                'Thầy giáo, bác sĩ chỉ người. Quyển vở, cái bàn chỉ vật.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ "học sinh" chỉ người nên thuộc từ loại ___.', [[0, 'danh từ']],
                '"Học sinh" chỉ người → danh từ chỉ người.', 'trung_binh');
            $this->fill($L, 'Từ chỉ hành động như "chạy", "nhảy" gọi là ___.', [[0, 'động từ']],
                'Động từ chỉ hành động, hoạt động của người và sự vật.', 'trung_binh');
            $this->fill($L, '"Bầu trời trong ___." Điền tính từ còn thiếu chỉ màu sắc của bầu trời.', [[0, 'xanh']],
                '"Xanh" chỉ màu sắc của bầu trời → tính từ.', 'trung_binh');
        }
    }

    private function seedSoSanhNhanHoa(): void
    {
        $L = 'nv-so-sanh-nhan-hoa';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Câu "Mặt trời như một quả cầu lửa khổng lồ." dùng biện pháp tu từ nào?',
                ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Hoán dụ'], 0,
                'Câu có từ "như", đối chiếu mặt trời với quả cầu lửa → biện pháp so sánh.', 'trung_binh');
            $this->quiz($L, 'Câu "Chị gió thì thầm ngoài cửa sổ." dùng biện pháp tu từ nào?',
                ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Hoán dụ'], 1,
                'Gió được gán hành động của con người (thì thầm) → biện pháp nhân hoá.', 'trung_binh');
            $this->quiz($L, 'Dấu hiệu thường gặp của biện pháp so sánh là gì?',
                ['Có các từ "như, tựa, giống"', 'Gán hành động người cho vật', 'Gọi vật này bằng tên vật khác', 'Lấy bộ phận chỉ toàn thể'], 0,
                'So sánh thường xuất hiện cùng các từ ngữ so sánh: như, tựa, giống như...', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu với biện pháp tu từ được dùng.',
                [['"Mây trắng như những cánh buồm."', 'So sánh'],
                 ['"Ông mặt trời mỉm cười."', 'Nhân hoá'],
                 ['"Lá vàng rơi như những cánh bướm."', 'So sánh'],
                 ['"Bác trống trường gọi chúng em."', 'Nhân hoá']],
                'Có từ "như" → so sánh. Gán hành động người (mỉm cười, gọi) cho vật → nhân hoá.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ ngữ với vai trò của nó.',
                [['như', 'Từ so sánh'], ['tựa', 'Từ so sánh'], ['giống như', 'Từ so sánh']],
                'Như, tựa, giống như là những từ ngữ báo hiệu biện pháp so sánh.', 'trung_binh');
            $this->matching($L, 'Nối mỗi sự vật với hành động nhân hoá phù hợp.',
                [['Gió', 'thì thầm'], ['Ông mặt trời', 'mỉm cười'], ['Cô mây', 'dạo chơi']],
                'Gió thì thầm, mặt trời mỉm cười, mây dạo chơi: vật được gán hành động người.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm SO SÁNH hoặc NHÂN HOÁ.',
                [['"Đôi mắt long lanh như hai vì sao."', 'So sánh'],
                 ['"Tóc bà bạc như mây."', 'So sánh'],
                 ['"Ông mặt trời thức dậy."', 'Nhân hoá'],
                 ['"Chị gió gõ cửa sổ."', 'Nhân hoá']],
                'Có từ "như" → so sánh. Gán hành động người (thức dậy, gõ cửa) cho vật → nhân hoá.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ TU TỪ hoặc KHÔNG TU TỪ.',
                [['"Lá vàng như bướm bay."', 'Có tu từ'],
                 ['"Biển hát ru êm đềm."', 'Có tu từ'],
                 ['"Trời nắng chang chang."', 'Không tu từ'],
                 ['"Mây trắng bồng bềnh."', 'Không tu từ']],
                '"Lá vàng như bướm bay" (so sánh), "Biển hát ru" (nhân hoá) có tu từ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ SO SÁNH hoặc KHÔNG PHẢI TỪ SO SÁNH.',
                [['như', 'Từ so sánh'], ['giống', 'Từ so sánh'],
                 ['và', 'Không phải từ so sánh'], ['rồi', 'Không phải từ so sánh']],
                'Như, giống là từ ngữ so sánh. Và, rồi không phải.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Biện pháp ___ dùng các từ "như, tựa, giống" để đối chiếu hai sự vật.', [[0, 'so sánh']],
                'So sánh đối chiếu hai sự vật có nét tương đồng để làm nổi bật đặc điểm.', 'trung_binh');
            $this->fill($L, 'Gán hành động, đặc điểm của con người cho sự vật gọi là ___.', [[0, 'nhân hoá']],
                'Nhân hoá khiến sự vật trở nên gần gũi, sinh động như con người.', 'trung_binh');
            $this->fill($L, '"Đôi má em ___ như hai quả táo chín." Điền tính từ còn thiếu chỉ màu sắc.', [[0, 'đỏ']],
                '"Đỏ" chỉ màu sắc của má; câu có từ "như" → biện pháp so sánh.', 'trung_binh');
        }
    }

    private function seedAnDuHoanDu(): void
    {
        $L = 'nv-an-du-hoan-du';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Thuyền về có nhớ bến chăng / Bến thì một dạ khăng khăng đợi thuyền." – "thuyền", "bến" là biện pháp gì?',
                ['Ẩn dụ', 'Hoán dụ', 'So sánh', 'Nhân hoá'], 0,
                '"Thuyền" chỉ người ra đi, "bến" chỉ người ở lại: gọi sự vật này bằng tên sự vật khác → ẩn dụ.', 'kho');
            $this->quiz($L, '"Bàn tay ta làm nên tất cả." – "bàn tay" là biện pháp gì?',
                ['Ẩn dụ', 'Hoán dụ', 'So sánh', 'Nhân hoá'], 1,
                'Lấy bộ phận (bàn tay) để chỉ toàn thể (con người lao động) → hoán dụ.', 'kho');
            $this->quiz($L, 'Điểm khác nhau cơ bản giữa ẩn dụ và so sánh là gì?',
                ['Ẩn dụ không dùng từ so sánh', 'Ẩn dụ luôn dài hơn', 'So sánh không có hình ảnh', 'Hai biện pháp giống hệt nhau'], 0,
                'So sánh có từ "như, tựa, giống"; ẩn dụ gọi thẳng tên sự vật khác mà không dùng từ so sánh.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu với biện pháp tu từ được dùng.',
                [['"Ăn quả nhớ kẻ trồng cây." – "quả" chỉ thành quả', 'Ẩn dụ'],
                 ['"Cả lớp im phăng phắc." – "cả lớp" chỉ học sinh', 'Hoán dụ'],
                 ['"Mặt trời xuống biển như hòn lửa." – có từ "như"', 'So sánh']],
                '"Quả" chỉ thành quả (ẩn dụ); "cả lớp" là vật chứa chỉ học sinh (hoán dụ).', 'kho');
            $this->matching($L, 'Nối mỗi biện pháp với cách hiểu đúng.',
                [['Ẩn dụ', 'Gọi tên sự vật này bằng tên sự vật khác'],
                 ['Hoán dụ', 'Lấy cái gần gũi để chỉ cái cần nói'],
                 ['Nhân hoá', 'Gán đặc điểm con người cho sự vật']],
                'Ẩn dụ dựa trên nét tương đồng; hoán dụ dựa trên quan hệ gần gũi.', 'kho');
            $this->matching($L, 'Nối mỗi hình ảnh thơ với ý nghĩa tu từ.',
                [['"Ngày ngày mặt trời đi qua trên lăng" – "mặt trời" chỉ Bác Hồ', 'Ẩn dụ'],
                 ['"Áo chàm đưa buổi phân li" – "áo chàm" chỉ người dân Việt Bắc', 'Hoán dụ'],
                 ['"Một cây làm chẳng nên non" – "cây" chỉ một người', 'Hoán dụ']],
                '"Mặt trời" chỉ Bác Hồ (ẩn dụ); "áo chàm", "một cây" lấy dấu hiệu/bộ phận chỉ người (hoán dụ).', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ẨN DỤ hoặc HOÁN DỤ.',
                [['"Người Cha mái tóc bạc" – "Người Cha" chỉ Bác Hồ', 'Ẩn dụ'],
                 ['"Thuyền về có nhớ bến chăng" – "thuyền" chỉ người ra đi', 'Ẩn dụ'],
                 ['"Bàn tay ta làm nên tất cả" – "bàn tay" chỉ người lao động', 'Hoán dụ'],
                 ['"Một cây làm chẳng nên non" – "cây" chỉ một người', 'Hoán dụ']],
                'Gọi bằng tên sự vật khác có nét tương đồng → ẩn dụ. Lấy bộ phận chỉ toàn thể → hoán dụ.', 'kho');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ ẨN DỤ hoặc KHÔNG CÓ ẨN DỤ.',
                [['"Thuyền về có nhớ bến chăng"', 'Có ẩn dụ'],
                 ['"Ngày ngày mặt trời đi qua trên lăng"', 'Có ẩn dụ'],
                 ['"Trời hôm nay rất đẹp"', 'Không có ẩn dụ'],
                 ['"Em đi học đúng giờ"', 'Không có ẩn dụ']],
                '"Thuyền", "bến", "mặt trời" (chỉ Bác Hồ) là ẩn dụ.', 'kho');
            $this->sortQ($L, 'Kéo mỗi câu hoán dụ vào nhóm đúng.',
                [['"Bàn tay ta làm nên tất cả."', 'Bộ phận – toàn thể'],
                 ['"Một cây làm chẳng nên non."', 'Bộ phận – toàn thể'],
                 ['"Cả trường reo hò."', 'Vật chứa – vật bị chứa'],
                 ['"Làng tôi ai cũng vui."', 'Vật chứa – vật bị chứa']],
                'Bàn tay/cây là bộ phận chỉ người. Trường/làng là vật chứa chỉ người bên trong.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Gọi tên sự vật này bằng tên sự vật khác có nét tương đồng gọi là ___.', [[0, 'ẩn dụ']],
                'Ẩn dụ không dùng từ so sánh mà gọi thẳng tên sự vật khác.', 'kho');
            $this->fill($L, 'Lấy bộ phận để chỉ toàn thể, lấy vật chứa để chỉ vật bị chứa gọi là ___.', [[0, 'hoán dụ']],
                'Hoán dụ dựa trên mối quan hệ gần gũi giữa các sự vật.', 'kho');
            $this->fill($L, '"___ tay ta làm nên tất cả." Điền từ còn thiếu trong câu thơ có biện pháp hoán dụ.', [[0, 'Bàn']],
                '"Bàn tay" là bộ phận chỉ người lao động → hoán dụ.', 'kho');
        }
    }

    private function seedBoCucMieuTa(): void
    {
        $L = 'nv-bo-cuc-mieu-ta';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bài văn miêu tả gồm mấy phần?',
                ['2 phần', '3 phần', '4 phần', '5 phần'], 1,
                'Bài văn miêu tả gồm ba phần: mở bài, thân bài, kết bài.', 'trung_binh');
            $this->quiz($L, 'Phần nào giới thiệu đối tượng miêu tả?',
                ['Mở bài', 'Thân bài', 'Kết bài', 'Cả ba phần'], 0,
                'Mở bài giới thiệu đối tượng được miêu tả.', 'trung_binh');
            $this->quiz($L, 'Nhóm từ nào gợi hình ảnh, nên dùng trong văn miêu tả?',
                ['xanh mướt, rì rào', 'cộng, trừ', 'vì, nên', 'rất, lắm'], 0,
                'Từ ngữ gợi hình, gợi cảm (xanh mướt, rì rào) giúp bài văn sinh động.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phần của bài văn với nhiệm vụ của nó.',
                [['Mở bài', 'Giới thiệu đối tượng miêu tả'],
                 ['Thân bài', 'Tả chi tiết từng bộ phận'],
                 ['Kết bài', 'Nêu cảm nghĩ về đối tượng']],
                'Mở bài giới thiệu, thân bài tả chi tiết, kết bài nêu cảm nghĩ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đối tượng với từ ngữ miêu tả phù hợp.',
                [['Dòng sông', 'trong xanh, uốn lượn'],
                 ['Ngọn núi', 'sừng sững, chót vót'],
                 ['Cánh đồng', 'xanh mướt, bát ngát']],
                'Chọn từ ngữ gợi hình phù hợp với từng đối tượng miêu tả.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cách tả với trình tự miêu tả.',
                [['Từ xa đến gần', 'Trình tự không gian'],
                 ['Từ sáng đến tối', 'Trình tự thời gian'],
                 ['Từ bao quát đến chi tiết', 'Trình tự hợp lí']],
                'Miêu tả theo trình tự giúp người đọc hình dung rõ ràng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ GỢI HÌNH hoặc KHÔNG GỢI HÌNH.',
                [['xanh mướt', 'Từ gợi hình'], ['rì rào', 'Từ gợi hình'],
                 ['bàn ghế', 'Không gợi hình'], ['cộng trừ', 'Không gợi hình']],
                'Xanh mướt, rì rào gợi hình ảnh, âm thanh. Bàn ghế, cộng trừ là từ trung tính.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm MỞ BÀI hoặc THÂN BÀI.',
                [['"Quê hương em có một dòng sông rất đẹp."', 'Mở bài'],
                 ['"Mùa thu, vườn nhà em rực rỡ sắc hoa."', 'Mở bài'],
                 ['"Mặt nước trong veo soi bóng mây trời."', 'Thân bài'],
                 ['"Từng đàn cá tung tăng bơi lội."', 'Thân bài']],
                'Mở bài giới thiệu chung; thân bài tả chi tiết từng bộ phận.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm MIÊU TẢ NGƯỜI hoặc MIÊU TẢ CẢNH.',
                [['"Đôi mắt bà hiền từ."', 'Miêu tả người'],
                 ['"Mái tóc mẹ dài óng ả."', 'Miêu tả người'],
                 ['"Nắng vàng rải khắp sân."', 'Miêu tả cảnh'],
                 ['"Chim hót líu lo trên cành."', 'Miêu tả cảnh']],
                'Miêu tả người tập trung vào ngoại hình, tính cách; miêu tả cảnh vào thiên nhiên.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bài văn miêu tả gồm ba phần: mở bài, thân bài và ___.', [[0, 'kết bài']],
                'Kết bài nêu cảm nghĩ, tình cảm của người viết về đối tượng.', 'trung_binh');
            $this->fill($L, 'Phần ___ tả chi tiết từng bộ phận của đối tượng miêu tả.', [[0, 'thân bài']],
                'Thân bài là phần quan trọng nhất, tả chi tiết theo trình tự hợp lí.', 'trung_binh');
            $this->fill($L, 'Để bài văn sinh động cần dùng từ ngữ gợi hình, gợi ___.', [[0, 'cảm']],
                'Từ ngữ gợi hình vẽ ra hình ảnh, từ ngữ gợi cảm khơi gợi cảm xúc.', 'trung_binh');
        }
    }

    private function seedDauCau(): void
    {
        $L = 'nv-dau-cau';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dấu câu nào dùng để kết thúc câu kể?',
                ['Dấu chấm', 'Dấu hỏi', 'Dấu than', 'Dấu phẩy'], 0,
                'Câu kể (câu trần thuật) kết thúc bằng dấu chấm.');
            $this->quiz($L, 'Câu "Bạn có khỏe không?" cần dùng dấu câu nào ở cuối?',
                ['Dấu chấm', 'Dấu hỏi', 'Dấu than', 'Dấu phẩy'], 1,
                'Câu hỏi kết thúc bằng dấu hỏi (?).');
            $this->quiz($L, 'Dấu hai chấm thường dùng để làm gì?',
                ['Báo hiệu lời giải thích, liệt kê', 'Kết thúc câu', 'Ngăn cách các vế câu', 'Thay cho dấu chấm'], 0,
                'Dấu hai chấm (:) báo hiệu phần giải thích, liệt kê hoặc lời nói trực tiếp.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dấu câu với công dụng của nó.',
                [['Dấu chấm', 'Kết thúc câu kể'],
                 ['Dấu hỏi', 'Kết thúc câu hỏi'],
                 ['Dấu than', 'Kết thúc câu cảm thán'],
                 ['Dấu phẩy', 'Ngăn cách các bộ phận trong câu']],
                'Mỗi loại câu có dấu kết thúc riêng; dấu phẩy dùng bên trong câu.');
            $this->matching($L, 'Nối mỗi câu với dấu câu cần dùng ở cuối.',
                [['Câu cảm thán "Trời đẹp quá!"', 'Dấu than'],
                 ['Câu hỏi "Bạn tên gì?"', 'Dấu hỏi'],
                 ['Câu kể "Em đi học."', 'Dấu chấm']],
                'Cảm thán → dấu than (!), hỏi → dấu hỏi (?), kể → dấu chấm (.).');
            $this->matching($L, 'Nối mỗi kí hiệu với tên dấu câu.',
                [[':', 'Dấu hai chấm'], [';', 'Dấu chấm phẩy'], ['…', 'Dấu ba chấm']],
                'Dấu hai chấm (:), dấu chấm phẩy (;), dấu ba chấm (…) hay dùng trong văn bản.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Dựa vào dấu cuối câu, kéo mỗi câu vào nhóm CÂU KỂ hoặc CÂU HỎI.',
                [['"Em đi học."', 'Câu kể'], ['"Trời mưa to."', 'Câu kể'],
                 ['"Bạn có đi không?"', 'Câu hỏi'], ['"Ai đã đến?"', 'Câu hỏi']],
                'Kết thúc bằng dấu chấm → câu kể; bằng dấu hỏi → câu hỏi.');
            $this->sortQ($L, 'Kéo mỗi dấu vào nhóm DẤU KẾT THÚC CÂU hoặc DẤU NGĂN CÁCH.',
                [['Dấu chấm', 'Kết thúc câu'], ['Dấu hỏi', 'Kết thúc câu'],
                 ['Dấu phẩy', 'Ngăn cách'], ['Dấu hai chấm', 'Ngăn cách']],
                'Dấu chấm, hỏi, than đứng cuối câu. Dấu phẩy, hai chấm dùng bên trong câu.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm DÙNG DẤU PHẨY ĐÚNG hoặc SAI.',
                [['"Mẹ mua rau, thịt, cá."', 'Đúng'],
                 ['"Sáng nay, trời trong xanh."', 'Đúng'],
                 ['"Em, đi học."', 'Sai'],
                 ['"Lan, và Mai đi chơi."', 'Sai']],
                'Dấu phẩy ngăn cách các từ liệt kê hoặc trạng ngữ đầu câu; không đặt tùy tiện.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Câu hỏi kết thúc bằng dấu ___.', [[0, 'hỏi']],
                'Dấu hỏi (?) đặt cuối câu hỏi.');
            $this->fill($L, 'Dấu ___ dùng để ngăn cách các bộ phận liệt kê trong câu.', [[0, 'phẩy']],
                'Ví dụ: "Mẹ mua rau, thịt, cá."');
            $this->fill($L, '"Ôi, đẹp quá___" Điền dấu câu còn thiếu vào cuối câu cảm thán.', [[0, '!']],
                'Câu cảm thán kết thúc bằng dấu chấm than (!).');
        }
    }
}
