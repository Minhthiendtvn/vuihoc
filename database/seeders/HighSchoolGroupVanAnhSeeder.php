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
 * Dữ liệu THPT cho 3 môn: NGỮ VĂN (ngu-van), TIẾNG VIỆT (tieng-viet),
 * TIẾNG ANH (tieng-anh) — khối 10, 11, 12.
 *
 * 9 topics (mỗi môn 3 topics, mỗi topic đúng 1 khối: grade_min = grade_max),
 * 18 skills (mỗi skill 2 bài học, slug {skill-slug}-1/2), 36 bài học,
 * mỗi bài: đúng 4 quiz + 4 matching + 4 sort + 4 fill (16 câu).
 * Nội dung TỰ VIẾT 100%, bám chương trình THPT.
 *
 * Idempotent: topic/skill firstOrCreate theo slug; bài học bỏ qua khi slug
 * đã tồn tại; câu hỏi bỏ qua khi (lesson_id, game_type) đã được seed.
 */
class HighSchoolGroupVanAnhSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $gradeBySlug = [];

    /** topic_slug => meta + skills */
    private array $topicPlan = [
        'ngu-van-thpt-10' => [
            'subject' => 'ngu-van', 'name' => 'Đọc hiểu văn bản', 'icon' => '📖', 'grade' => 10,
            'description' => 'Rèn kĩ năng đọc hiểu văn bản truyện và thơ: nhận biết các yếu tố cơ bản, phân tích chi tiết nghệ thuật, xác định chủ đề và thông điệp.',
            'skills' => [
                ['slug' => 'ngu-van-thpt-10-doc-hieu-truyen', 'name' => 'Đọc hiểu truyện',
                 'description' => 'Đọc hiểu truyện ngắn: cốt truyện, tình huống truyện, nhân vật, người kể chuyện, chi tiết nghệ thuật, chủ đề.'],
                ['slug' => 'ngu-van-thpt-10-doc-hieu-tho', 'name' => 'Đọc hiểu thơ',
                 'description' => 'Đọc hiểu thơ: thể thơ, nhịp điệu, vần, hình ảnh thơ, cảm xúc trữ tình.'],
            ],
        ],
        'ngu-van-thpt-11' => [
            'subject' => 'ngu-van', 'name' => 'Nghị luận xã hội', 'icon' => '🗣️', 'grade' => 11,
            'description' => 'Rèn kĩ năng làm văn nghị luận xã hội: nghị luận về tư tưởng đạo lí và nghị luận về hiện tượng đời sống.',
            'skills' => [
                ['slug' => 'ngu-van-thpt-11-tu-tuong-dao-li', 'name' => 'Nghị luận về tư tưởng đạo lí',
                 'description' => 'Nghị luận về các quan niệm đạo đức, lối sống: thao tác lập luận, phân tích đề, sử dụng dẫn chứng.'],
                ['slug' => 'ngu-van-thpt-11-hien-tuong-doi-song', 'name' => 'Nghị luận về hiện tượng đời sống',
                 'description' => 'Nghị luận về các hiện tượng đời sống: nhận diện, đánh giá, tìm nguyên nhân và đề xuất giải pháp.'],
            ],
        ],
        'ngu-van-thpt-12' => [
            'subject' => 'ngu-van', 'name' => 'Nghị luận văn học', 'icon' => '✒️', 'grade' => 12,
            'description' => 'Rèn kĩ năng nghị luận văn học: phân tích tác phẩm văn xuôi và phân tích thơ, đánh giá giá trị nội dung và nghệ thuật.',
            'skills' => [
                ['slug' => 'ngu-van-thpt-12-phan-tich-van-xuoi', 'name' => 'Phân tích tác phẩm văn xuôi',
                 'description' => 'Phân tích nhân vật, tình huống truyện, đánh giá giá trị nội dung và nghệ thuật của tác phẩm văn xuôi.'],
                ['slug' => 'ngu-van-thpt-12-phan-tich-tho', 'name' => 'Phân tích thơ',
                 'description' => 'Phân tích tứ thơ, hình tượng, cảm xúc trữ tình; đánh giá giá trị tư tưởng và nghệ thuật của bài thơ.'],
            ],
        ],
        'tieng-viet-thpt-10' => [
            'subject' => 'tieng-viet', 'name' => 'Phong cách ngôn ngữ', 'icon' => '🔤', 'grade' => 10,
            'description' => 'Tìm hiểu các phong cách ngôn ngữ: phong cách ngôn ngữ văn chương và phong cách ngôn ngữ báo chí.',
            'skills' => [
                ['slug' => 'tieng-viet-thpt-10-pcnc-van-chuong', 'name' => 'Phong cách ngôn ngữ văn chương',
                 'description' => 'Đặc trưng của phong cách ngôn ngữ văn chương và cách nhận diện trong văn bản.'],
                ['slug' => 'tieng-viet-thpt-10-pcnc-bao-chi', 'name' => 'Phong cách ngôn ngữ báo chí',
                 'description' => 'Đặc trưng của phong cách ngôn ngữ báo chí; phân biệt bản tin và phóng sự.'],
            ],
        ],
        'tieng-viet-thpt-11' => [
            'subject' => 'tieng-viet', 'name' => 'Ngữ pháp nâng cao', 'icon' => '💬', 'grade' => 11,
            'description' => 'Ngữ pháp nâng cao: câu và các thành phần câu, các biện pháp tu từ thường gặp.',
            'skills' => [
                ['slug' => 'tieng-viet-thpt-11-cau-thanh-phan', 'name' => 'Câu và các thành phần câu',
                 'description' => 'Câu đơn, câu ghép, các thành phần chính – phụ của câu, câu đặc biệt.'],
                ['slug' => 'tieng-viet-thpt-11-bien-phap-tu-tu', 'name' => 'Biện pháp tu từ nâng cao',
                 'description' => 'Nhận diện và phân tích tác dụng của các biện pháp tu từ: so sánh, nhân hóa, ẩn dụ, hoán dụ, điệp ngữ, liệt kê...'],
            ],
        ],
        'tieng-viet-thpt-12' => [
            'subject' => 'tieng-viet', 'name' => 'Thực hành tiếng Việt tổng hợp', 'icon' => '📝', 'grade' => 12,
            'description' => 'Thực hành tiếng Việt tổng hợp: chữa lỗi dùng từ, đặt câu và liên kết văn bản.',
            'skills' => [
                ['slug' => 'tieng-viet-thpt-12-chua-loi', 'name' => 'Chữa lỗi dùng từ đặt câu',
                 'description' => 'Phát hiện và chữa các lỗi về dùng từ, kết hợp từ và cấu trúc câu.'],
                ['slug' => 'tieng-viet-thpt-12-lien-ket-van-ban', 'name' => 'Liên kết văn bản',
                 'description' => 'Các phép liên kết trong văn bản; tính mạch lạc của đoạn văn, bài văn.'],
            ],
        ],
        'tieng-anh-thpt-10' => [
            'subject' => 'tieng-anh', 'name' => 'Thì và câu điều kiện', 'icon' => '🇬🇧', 'grade' => 10,
            'description' => 'Ôn tập các thì cơ bản và câu điều kiện loại 1 (nội dung song ngữ Anh – Việt).',
            'skills' => [
                ['slug' => 'tieng-anh-thpt-10-cac-thi-co-ban', 'name' => 'Các thì cơ bản',
                 'description' => 'Ôn tập thì hiện tại đơn, hiện tại tiếp diễn, quá khứ đơn, tương lai đơn.'],
                ['slug' => 'tieng-anh-thpt-10-cau-dieu-kien-1', 'name' => 'Câu điều kiện loại 1',
                 'description' => 'Cấu trúc và cách dùng câu điều kiện loại 1 diễn tả điều có thể xảy ra ở hiện tại/tương lai.'],
            ],
        ],
        'tieng-anh-thpt-11' => [
            'subject' => 'tieng-anh', 'name' => 'Câu bị động và tường thuật', 'icon' => '🔁', 'grade' => 11,
            'description' => 'Câu bị động ở các thì và dạng đặc biệt; câu tường thuật các loại câu (nội dung song ngữ Anh – Việt).',
            'skills' => [
                ['slug' => 'tieng-anh-thpt-11-cau-bi-dong', 'name' => 'Câu bị động',
                 'description' => 'Cấu trúc câu bị động ở các thì cơ bản và các dạng đặc biệt.'],
                ['slug' => 'tieng-anh-thpt-11-cau-tuong-thuat', 'name' => 'Câu tường thuật',
                 'description' => 'Chuyển câu trực tiếp sang gián tiếp: câu trần thuật, câu hỏi, câu mệnh lệnh.'],
            ],
        ],
        'tieng-anh-thpt-12' => [
            'subject' => 'tieng-anh', 'name' => 'Ngữ pháp nâng cao lớp 12', 'icon' => '🎓', 'grade' => 12,
            'description' => 'Ngữ pháp nâng cao lớp 12: mệnh đề quan hệ rút gọn và đảo ngữ (nội dung song ngữ Anh – Việt).',
            'skills' => [
                ['slug' => 'tieng-anh-thpt-12-menh-de-quan-he', 'name' => 'Mệnh đề quan hệ rút gọn',
                 'description' => 'Rút gọn mệnh đề quan hệ bằng V-ing, V-ed và to-V.'],
                ['slug' => 'tieng-anh-thpt-12-dao-ngu', 'name' => 'Đảo ngữ',
                 'description' => 'Các cấu trúc đảo ngữ thường gặp với never, rarely, hardly, no sooner, only...'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->topicPlan as $topicSlug => $t) {
            $subject = Subject::where('slug', $t['subject'])->firstOrFail();
            $maxTOrder = (int) Topic::where('subject_id', $subject->id)->max('sort_order');
            $topic = Topic::firstOrCreate(
                ['slug' => $topicSlug],
                [
                    'subject_id' => $subject->id,
                    'name' => $t['name'],
                    'description' => $t['description'],
                    'icon' => $t['icon'],
                    'sort_order' => $maxTOrder + 1,
                    'grade_min' => $t['grade'],
                    'grade_max' => $t['grade'],
                    'is_published' => true,
                    'is_demo' => true,
                ]
            );
            foreach ($t['skills'] as $i => $s) {
                $skill = Skill::firstOrCreate(
                    ['slug' => $s['slug']],
                    [
                        'topic_id' => $topic->id,
                        'name' => $s['name'],
                        'description' => $s['description'],
                        'sort_order' => $i + 1,
                        'is_demo' => true,
                    ]
                );
                for ($num = 1; $num <= 2; $num++) {
                    $meta = $this->lessonMeta($s['slug'], $num);
                    $this->createLesson($skill, $s['slug'], $t['grade'], $num, $meta);
                }
            }
        }

        // ---- Ngữ văn 10 ----
        $this->seedNv10Truyen1(); $this->seedNv10Truyen2();
        $this->seedNv10Tho1(); $this->seedNv10Tho2();
        // ---- Ngữ văn 11 ----
        $this->seedNv11DaoLi1(); $this->seedNv11DaoLi2();
        $this->seedNv11HienTuong1(); $this->seedNv11HienTuong2();
        // ---- Ngữ văn 12 ----
        $this->seedNv12VanXuoi1(); $this->seedNv12VanXuoi2();
        $this->seedNv12Tho1(); $this->seedNv12Tho2();
        // ---- Tiếng Việt 10 ----
        $this->seedTv10VanChuong1(); $this->seedTv10VanChuong2();
        $this->seedTv10BaoChi1(); $this->seedTv10BaoChi2();
        // ---- Tiếng Việt 11 ----
        $this->seedTv11Cau1(); $this->seedTv11Cau2();
        $this->seedTv11TuTu1(); $this->seedTv11TuTu2();
        // ---- Tiếng Việt 12 ----
        $this->seedTv12ChuaLoi1(); $this->seedTv12ChuaLoi2();
        $this->seedTv12LienKet1(); $this->seedTv12LienKet2();
        // ---- Tiếng Anh 10 ----
        $this->seedEn10Thi1(); $this->seedEn10Thi2();
        $this->seedEn10Dk1a(); $this->seedEn10Dk1b();
        // ---- Tiếng Anh 11 ----
        $this->seedEn11BiDong1(); $this->seedEn11BiDong2();
        $this->seedEn11TuongThuat1(); $this->seedEn11TuongThuat2();
        // ---- Tiếng Anh 12 ----
        $this->seedEn12Qh1(); $this->seedEn12Qh2();
        $this->seedEn12DaoNgu1(); $this->seedEn12DaoNgu2();
    }

    // ---------------- helpers (tái dùng y hệt mẫu) ----------------

    private function lesson(string $slug): Lesson
    {
        if (! isset($this->lessonBySlug[$slug])) {
            $this->lessonBySlug[$slug] = Lesson::where('slug', $slug)->firstOrFail();
        }
        return $this->lessonBySlug[$slug];
    }

    private function createLesson(Skill $skill, string $skillSlug, int $grade, int $num, array $meta): void
    {
        $slug = "{$skillSlug}-{$num}";
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

    private function newQuestion(string $lessonSlug, string $gameType, string $prompt, string $explanation, string $difficulty = 'trung_binh', int $points = 10): Question
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

    private function quiz(string $lesson, string $prompt, array $options, int $correct, string $explanation, string $difficulty = 'trung_binh'): void
    {
        $q = $this->newQuestion($lesson, 'quiz', $prompt, $explanation, $difficulty);
        foreach ($options as $i => $text) {
            QuestionOption::create([
                'question_id' => $q->id, 'option_text' => $text,
                'is_correct' => $i === $correct, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function matching(string $lesson, string $prompt, array $pairs, string $explanation, string $difficulty = 'trung_binh'): void
    {
        $q = $this->newQuestion($lesson, 'matching', $prompt, $explanation, $difficulty);
        foreach ($pairs as $i => [$left, $right]) {
            MatchingPair::create([
                'question_id' => $q->id, 'left_text' => $left, 'right_text' => $right, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function sortQ(string $lesson, string $prompt, array $items, string $explanation, string $difficulty = 'trung_binh'): void
    {
        $q = $this->newQuestion($lesson, 'sort', $prompt, $explanation, $difficulty);
        foreach ($items as $i => [$text, $category]) {
            SortItem::create([
                'question_id' => $q->id, 'item_text' => $text, 'category' => $category, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function fill(string $lesson, string $prompt, array $answers, string $explanation, string $difficulty = 'trung_binh'): void
    {
        $q = $this->newQuestion($lesson, 'fill', $prompt, $explanation, $difficulty);
        foreach ($answers as $i => [$blankIndex, $text]) {
            FillAnswer::create([
                'question_id' => $q->id, 'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1,
            ]);
        }
    }

    /**
     * Siêu dữ liệu 36 bài học: [$skillSlug][$num] =>
     * [title, objective, difficulty, duration, instructions].
     * difficulty dùng chuẩn DB: 'trung_binh' (= medium), 'kho' (= hard).
     */
    private function lessonMeta(string $skillSlug, int $num): array
    {
        $plan = [
            'ngu-van-thpt-10-doc-hieu-truyen' => [
                1 => ['title' => 'Đọc hiểu truyện ngắn: cốt truyện và nhân vật (1)',
                    'objective' => 'Nhận biết các yếu tố cơ bản của truyện ngắn: cốt truyện, tình huống truyện, nhân vật và người kể chuyện.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Cốt truyện là chuỗi sự kiện liên kết bằng quan hệ nhân quả. Tình huống truyện là hoàn cảnh đặc biệt làm nảy sinh câu chuyện. Chú ý ngôi kể (thứ nhất/thứ ba) và vai trò của từng nhân vật.'],
                2 => ['title' => 'Đọc hiểu truyện ngắn: chi tiết, chủ đề và thông điệp (2)',
                    'objective' => 'Phân tích chi tiết nghệ thuật, xác định chủ đề và rút ra thông điệp của truyện ngắn.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Chi tiết nghệ thuật là chi tiết tiêu biểu, giàu sức gợi. Chủ đề là vấn đề trung tâm của tác phẩm; thông điệp là điều tác giả muốn gửi gắm tới người đọc.'],
            ],
            'ngu-van-thpt-10-doc-hieu-tho' => [
                1 => ['title' => 'Đọc hiểu thơ: thể thơ, nhịp điệu và vần (1)',
                    'objective' => 'Nhận biết các thể thơ quen thuộc; phân tích nhịp điệu, vần và tác dụng của chúng.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Xác định thể thơ qua số câu, số chữ mỗi câu. Chú ý cách gieo vần (vần chân, vần lưng) và cách ngắt nhịp tạo tiết tấu cho bài thơ.'],
                2 => ['title' => 'Đọc hiểu thơ: hình ảnh thơ và cảm xúc trữ tình (2)',
                    'objective' => 'Cảm nhận hình ảnh thơ, xác định chủ thể trữ tình và cảm xúc trữ tình chủ đạo.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Hình ảnh thơ được gợi lên từ ngôn ngữ giàu sức gợi. Chủ thể trữ tình là người bộc lộ cảm xúc trong bài thơ; hãy gọi tên cảm xúc chủ đạo và tìm những hình ảnh thể hiện nó.'],
            ],
            'ngu-van-thpt-11-tu-tuong-dao-li' => [
                1 => ['title' => 'Nghị luận về tư tưởng đạo lí: khái niệm và thao tác lập luận (1)',
                    'objective' => 'Nắm khái niệm nghị luận về tư tưởng đạo lí; sử dụng các thao tác lập luận giải thích, chứng minh, bình luận.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Nghị luận về tư tưởng đạo lí bàn về các quan niệm đạo đức, lối sống. Luận điểm là ý chính cần chứng minh; luận cứ là lí lẽ và dẫn chứng. Các thao tác chính: giải thích, chứng minh, bình luận, so sánh.'],
                2 => ['title' => 'Nghị luận về tư tưởng đạo lí: phân tích đề và sử dụng dẫn chứng (2)',
                    'objective' => 'Phân tích đề bài, lập dàn ý; lựa chọn và sử dụng dẫn chứng tiêu biểu, xác thực.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Khi phân tích đề: xác định vấn đề nghị luận, phạm vi và yêu cầu. Dẫn chứng phải tiêu biểu, xác thực, phù hợp với luận điểm; tránh dẫn chứng bịa đặt hoặc chung chung.'],
            ],
            'ngu-van-thpt-11-hien-tuong-doi-song' => [
                1 => ['title' => 'Nghị luận về hiện tượng đời sống: nhận diện và đánh giá (1)',
                    'objective' => 'Nhận diện hiện tượng đời sống trong đề bài; đánh giá mặt tích cực và tiêu cực của hiện tượng.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Nghị luận về hiện tượng đời sống bàn về các sự việc, hiện tượng nổi bật trong đời sống. Cần mô tả hiện tượng, đánh giá khách quan cả mặt tốt và mặt xấu, tránh phiến diện.'],
                2 => ['title' => 'Nghị luận về hiện tượng đời sống: nguyên nhân và giải pháp (2)',
                    'objective' => 'Phân tích nguyên nhân của hiện tượng; đề xuất giải pháp khắc phục hợp lí.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Với hiện tượng tiêu cực: tìm nguyên nhân khách quan và chủ quan, đề xuất giải pháp cụ thể, khả thi. Với hiện tượng tích cực: nêu ý nghĩa và cách nhân rộng.'],
            ],
            'ngu-van-thpt-12-phan-tich-van-xuoi' => [
                1 => ['title' => 'Phân tích tác phẩm văn xuôi: nhân vật và tình huống truyện (1)',
                    'objective' => 'Phân tích nhân vật qua ngoại hình, hành động, lời nói, tâm lí; phân tích vai trò của tình huống truyện.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Phân tích nhân vật cần bám vào các chi tiết: ngoại hình, hành động, lời nói, diễn biến tâm lí. Tình huống truyện là hoàn cảnh đặc biệt bộc lộ tính cách nhân vật và chủ đề tác phẩm.'],
                2 => ['title' => 'Phân tích tác phẩm văn xuôi: giá trị nội dung và nghệ thuật (2)',
                    'objective' => 'Đánh giá giá trị nội dung (hiện thực, nhân đạo) và giá trị nghệ thuật của tác phẩm văn xuôi.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Giá trị hiện thực: tác phẩm phản ánh hiện thực đời sống. Giá trị nhân đạo: sự đồng cảm, ngợi ca, phê phán. Giá trị nghệ thuật: tình huống, kết cấu, ngôn ngữ, giọng điệu.'],
            ],
            'ngu-van-thpt-12-phan-tich-tho' => [
                1 => ['title' => 'Phân tích thơ: tứ thơ, hình tượng và cảm xúc (1)',
                    'objective' => 'Xác định tứ thơ; phân tích hình tượng thơ và diễn biến cảm xúc trữ tình.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Tứ thơ là ý tưởng trung tâm của bài thơ. Phân tích hình tượng thơ qua từ ngữ, biện pháp tu từ; theo dõi mạch cảm xúc từ đầu đến cuối bài.'],
                2 => ['title' => 'Phân tích thơ: giá trị tư tưởng và nghệ thuật (2)',
                    'objective' => 'Đánh giá giá trị tư tưởng và những đặc sắc nghệ thuật của bài thơ.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Giá trị tư tưởng: tình cảm, quan niệm mà bài thơ gửi gắm. Nghệ thuật: thể thơ, vần nhịp, từ ngữ, hình ảnh, biện pháp tu từ, giọng điệu. Kết bài khái quát và nêu cảm nhận.'],
            ],
            'tieng-viet-thpt-10-pcnc-van-chuong' => [
                1 => ['title' => 'Phong cách ngôn ngữ văn chương: ba đặc trưng (1)',
                    'objective' => 'Nắm ba đặc trưng của phong cách ngôn ngữ văn chương: tính hình tượng, tính truyền cảm, tính cá thể hóa.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Phong cách ngôn ngữ văn chương có ba đặc trưng: tính hình tượng (gợi hình ảnh cụ thể), tính truyền cảm (gợi cảm xúc), tính cá thể hóa (dấu ấn riêng của tác giả).'],
                2 => ['title' => 'Phong cách ngôn ngữ văn chương: nhận diện trong văn bản (2)',
                    'objective' => 'Nhận diện phong cách ngôn ngữ văn chương trong văn bản cụ thể; phân biệt với các phong cách khác.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Dấu hiệu nhận biết: ngôn ngữ giàu hình ảnh, cảm xúc; nhiều biện pháp tu từ; dấu ấn cá nhân rõ nét. So sánh với phong cách báo chí, khoa học, hành chính để phân biệt.'],
            ],
            'tieng-viet-thpt-10-pcnc-bao-chi' => [
                1 => ['title' => 'Phong cách ngôn ngữ báo chí: đặc trưng và chức năng (1)',
                    'objective' => 'Nắm đặc trưng của phong cách ngôn ngữ báo chí: tính thông tin, tính thời sự, tính ngắn gọn.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Phong cách báo chí phục vụ chức năng thông tin thời sự. Đặc trưng: tính thông tin thời sự, tính ngắn gọn, tính sinh động hấp dẫn. Ngôn ngữ rõ ràng, chính xác, dễ hiểu.'],
                2 => ['title' => 'Phong cách ngôn ngữ báo chí: bản tin và phóng sự (2)',
                    'objective' => 'Phân biệt bản tin và phóng sự; nhận biết ngôn ngữ báo chí trong các thể loại.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Bản tin: đưa tin ngắn gọn, khách quan (ai, cái gì, ở đâu, khi nào). Phóng sự: phản ánh sâu, có miêu tả, bình luận, giàu hình ảnh hơn nhưng vẫn đảm bảo tính thời sự.'],
            ],
            'tieng-viet-thpt-11-cau-thanh-phan' => [
                1 => ['title' => 'Câu và các thành phần câu: câu đơn, câu ghép (1)',
                    'objective' => 'Phân biệt câu đơn và câu ghép; xác định chủ ngữ, vị ngữ trong câu.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Câu đơn có một cụm chủ ngữ – vị ngữ; câu ghép có từ hai cụm chủ ngữ – vị ngữ trở lên. Chủ ngữ trả lời Ai? Cái gì?; vị ngữ trả lời Làm gì? Thế nào? Là gì?'],
                2 => ['title' => 'Câu và các thành phần câu: thành phần phụ, câu đặc biệt (2)',
                    'objective' => 'Nhận biết trạng ngữ, các thành phần biệt lập; nhận biết câu đặc biệt và câu rút gọn.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Trạng ngữ chỉ thời gian, nơi chốn, nguyên nhân, mục đích... Câu đặc biệt không có cấu tạo chủ ngữ – vị ngữ (thường là cụm từ). Câu rút gọn lược bớt thành phần có thể khôi phục được.'],
            ],
            'tieng-viet-thpt-11-bien-phap-tu-tu' => [
                1 => ['title' => 'Biện pháp tu từ: so sánh, nhân hóa, ẩn dụ, hoán dụ (1)',
                    'objective' => 'Nhận diện và phân tích tác dụng của so sánh, nhân hóa, ẩn dụ, hoán dụ.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'So sánh: đối chiếu hai sự vật có nét tương đồng (có từ so sánh). Nhân hóa: gán đặc điểm người cho vật. Ẩn dụ: gọi tên sự vật này bằng tên sự vật khác có nét tương đồng (không có từ so sánh). Hoán dụ: gọi tên bằng nét liên tưởng gần gũi.'],
                2 => ['title' => 'Biện pháp tu từ: điệp ngữ, liệt kê, nói quá, chơi chữ (2)',
                    'objective' => 'Nhận diện và phân tích tác dụng của điệp ngữ, liệt kê, nói quá, nói giảm, chơi chữ.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Điệp ngữ: lặp lại từ ngữ để nhấn mạnh, tạo nhịp điệu. Liệt kê: kể ra hàng loạt sự vật cùng loại. Nói quá: phóng đại mức độ. Chơi chữ: dùng từ đồng âm, đa nghĩa tạo hiệu quả hài hước, sâu sắc.'],
            ],
            'tieng-viet-thpt-12-chua-loi' => [
                1 => ['title' => 'Chữa lỗi dùng từ: lỗi về nghĩa và kết hợp từ (1)',
                    'objective' => 'Phát hiện và chữa các lỗi dùng từ sai nghĩa, kết hợp từ thiếu chính xác, lặp từ, dùng từ Hán Việt sai.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Các lỗi thường gặp: dùng từ không đúng nghĩa, kết hợp từ gượng ép, lặp từ, dùng từ địa phương/khẩu ngữ trong văn viết trang trọng, nhầm từ Hán Việt. Khi chữa: thay từ đúng nghĩa, đúng phong cách.'],
                2 => ['title' => 'Chữa lỗi đặt câu: lỗi về cấu trúc và logic (2)',
                    'objective' => 'Phát hiện và chữa các lỗi câu thiếu thành phần, sai trật tự, lủng củng, mâu thuẫn logic.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Các lỗi thường gặp: câu thiếu chủ ngữ hoặc vị ngữ, sắp xếp trật tự từ sai, câu dài lủng củng, các vế câu mâu thuẫn logic. Khi chữa: bổ sung thành phần thiếu, sắp xếp lại trật tự hợp lí.'],
            ],
            'tieng-viet-thpt-12-lien-ket-van-ban' => [
                1 => ['title' => 'Liên kết văn bản: các phép liên kết (1)',
                    'objective' => 'Nhận biết các phép liên kết: lặp từ ngữ, đồng nghĩa – trái nghĩa, nối, thế.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Các phép liên kết chính: phép lặp (lặp từ ngữ), phép đồng nghĩa/trái nghĩa, phép nối (dùng quan hệ từ, từ nối), phép thế (dùng từ thay thế như ấy, đó, việc này).'],
                2 => ['title' => 'Liên kết văn bản: tính mạch lạc của đoạn văn (2)',
                    'objective' => 'Đánh giá tính mạch lạc của đoạn văn; sắp xếp câu theo trình tự hợp lí.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Đoạn văn mạch lạc khi các câu liên kết chặt chẽ, triển khai đúng chủ đề theo một trình tự (thời gian, không gian, logic...). Câu chủ đề thường đứng đầu hoặc cuối đoạn.'],
            ],
            'tieng-anh-thpt-10-cac-thi-co-ban' => [
                1 => ['title' => 'Ôn tập các thì: hiện tại đơn và hiện tại tiếp diễn (1)',
                    'objective' => 'Phân biệt và sử dụng đúng thì hiện tại đơn (thói quen, sự thật) và hiện tại tiếp diễn (đang diễn ra).',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Present simple: S + V(s/es) — habits, facts (every day, usually). Present continuous: S + am/is/are + V-ing — happening now (now, at the moment, Look!).'],
                2 => ['title' => 'Ôn tập các thì: quá khứ đơn và tương lai đơn (2)',
                    'objective' => 'Sử dụng đúng thì quá khứ đơn (hành động đã xong) và tương lai đơn (quyết định, dự đoán).',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Past simple: S + V-ed/V2 — finished actions (yesterday, last week, in 2020). Future simple: S + will + V — decisions, predictions (tomorrow, next week, I think...).'],
            ],
            'tieng-anh-thpt-10-cau-dieu-kien-1' => [
                1 => ['title' => 'Câu điều kiện loại 1: cấu trúc và cách dùng (1)',
                    'objective' => 'Nắm cấu trúc If + hiện tại đơn, will + V; dùng câu điều kiện loại 1 diễn tả điều có thể xảy ra.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Type 1: If + S + V (present simple), S + will + V. Diễn tả điều kiện có thể xảy ra ở hiện tại/tương lai. Mệnh đề If có thể đứng trước hoặc sau.'],
                2 => ['title' => 'Câu điều kiện loại 1: luyện tập vận dụng (2)',
                    'objective' => 'Vận dụng câu điều kiện loại 1 trong các tình huống; phân biệt với câu điều kiện loại 0.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Type 0 (sự thật hiển nhiên): If + present simple, present simple. Type 1 (có thể xảy ra): If + present simple, will + V. Chú ý dấu phẩy khi mệnh đề If đứng trước.'],
            ],
            'tieng-anh-thpt-11-cau-bi-dong' => [
                1 => ['title' => 'Câu bị động: các thì cơ bản (1)',
                    'objective' => 'Chuyển câu chủ động sang bị động ở các thì hiện tại đơn, quá khứ đơn, tương lai đơn, hiện tại tiếp diễn.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Passive: S + be + V-ed/V3 (+ by O). Chia "be" theo thì: is/are (hiện tại), was/were (quá khứ), will be (tương lai), is/are being (tiếp diễn). Tân ngữ của câu chủ động thành chủ ngữ câu bị động.'],
                2 => ['title' => 'Câu bị động: dạng đặc biệt và động từ khuyết thiếu (2)',
                    'objective' => 'Dùng câu bị động với động từ khuyết thiếu, thì hiện tại hoàn thành; câu bị động hai tân ngữ.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Modal passive: S + modal + be + V-ed (must be cleaned). Present perfect passive: S + have/has + been + V-ed. Với hai tân ngữ, có thể đưa tân ngữ gián tiếp hoặc trực tiếp lên làm chủ ngữ.'],
            ],
            'tieng-anh-thpt-11-cau-tuong-thuat' => [
                1 => ['title' => 'Câu tường thuật: câu trần thuật và câu hỏi (1)',
                    'objective' => 'Chuyển câu trần thuật và câu hỏi Yes/No, Wh- sang câu tường thuật; lùi thì đúng.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Reported speech: lùi thì (present → past, past → past perfect, will → would). Câu trần thuật: S + said/told + (that) + clause. Yes/No: asked + if/whether. Wh-: asked + từ để hỏi.'],
                2 => ['title' => 'Câu tường thuật: câu mệnh lệnh và câu điều kiện (2)',
                    'objective' => 'Tường thuật câu mệnh lệnh, yêu cầu (told/asked + to-V); tường thuật câu điều kiện.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Commands: S + told/asked + O + (not) to-V. Câu điều kiện loại 2, 3 khi tường thuật giữ nguyên thì. Chú ý đổi đại từ, trạng từ chỉ thời gian/nơi chốn (now → then, here → there, today → that day).'],
            ],
            'tieng-anh-thpt-12-menh-de-quan-he' => [
                1 => ['title' => 'Mệnh đề quan hệ rút gọn: V-ing và V-ed (1)',
                    'objective' => 'Rút gọn mệnh đề quan hệ chủ động bằng V-ing và bị động bằng V-ed.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Mệnh đề quan hệ chủ động (who/which + V) rút gọn thành V-ing: the boy who plays → the boy playing. Mệnh đề bị động (who/which + be + V-ed) rút gọn thành V-ed: the car which was made → the car made.'],
                2 => ['title' => 'Mệnh đề quan hệ rút gọn: to-V và luyện tập tổng hợp (2)',
                    'objective' => 'Rút gọn mệnh đề quan hệ bằng to-V (sau số thứ tự, so sánh nhất); luyện tập tổng hợp ba dạng.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Dùng to-V khi mệnh đề có số thứ tự (the first, the second), so sánh nhất (the best), hoặc chỉ mục đích: the first student to finish. Không rút gọn mệnh đề quan hệ không xác định có dấu phẩy bằng to-V.'],
            ],
            'tieng-anh-thpt-12-dao-ngu' => [
                1 => ['title' => 'Đảo ngữ: cấu trúc cơ bản (1)',
                    'objective' => 'Nắm cấu trúc đảo ngữ với các trạng từ phủ định: never, rarely, hardly, no sooner, only.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Khi trạng từ phủ định đứng đầu câu, đảo trợ động từ lên trước chủ ngữ: Never have I seen...; Rarely does she come...; Hardly had he arrived when...; No sooner had... than...; Only when... did...'],
                2 => ['title' => 'Đảo ngữ: luyện tập nâng cao (2)',
                    'objective' => 'Vận dụng đảo ngữ trong câu điều kiện (were/had/should) và các cấu trúc nâng cao.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Đảo ngữ câu điều kiện: Should you need help... (= If you should need); Were I rich... (= If I were); Had he studied... (= If he had studied). Chú ý: đảo ngữ không dùng với "not" thừa.'],
            ],
        ];

        return $plan[$skillSlug][$num];
    }

    // ================= NGỮ VĂN 10 =================

    private function seedNv10Truyen1(): void
    {
        $L = 'ngu-van-thpt-10-doc-hieu-truyen-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cốt truyện của một truyện ngắn là gì?',
                ['Chuỗi sự kiện được sắp xếp có mở đầu, diễn biến và kết thúc, liên kết bằng quan hệ nhân quả',
                 'Toàn bộ lời thoại của các nhân vật trong truyện',
                 'Phần mở đầu giới thiệu nhân vật và bối cảnh',
                 'Lời bình luận của người kể chuyện về sự kiện'],
                0, 'Cốt truyện là hệ thống sự kiện có quan hệ nhân quả, thường phát triển qua: mở đầu – thắt nút – cao trào – mở nút – kết thúc.');
            $this->quiz($L, 'Tình huống truyện có vai trò gì?',
                ['Làm nảy sinh câu chuyện, bộc lộ tính cách nhân vật và chủ đề tác phẩm',
                 'Chỉ dùng để giới thiệu bối cảnh xảy ra sự việc',
                 'Thay thế cho phần kết thúc của truyện',
                 'Là lời thoại của nhân vật chính'],
                0, 'Tình huống truyện là hoàn cảnh đặc biệt làm nảy sinh sự việc; từ cách nhân vật ứng xử trong tình huống, tính cách và chủ đề được bộc lộ.');
            $this->quiz($L, 'Người kể chuyện ngôi thứ nhất có đặc điểm nào?',
                ['Xưng "tôi", kể theo điểm nhìn của nhân vật trong truyện',
                 'Biết hết mọi suy nghĩ của tất cả nhân vật',
                 'Không bao giờ xuất hiện trong thế giới truyện',
                 'Chỉ kể lại lời thoại, không miêu tả'],
                0, 'Người kể ngôi thứ nhất xưng "tôi", vừa là người kể vừa có thể là nhân vật trong truyện, kể theo điểm nhìn hạn chế của mình.');
            $this->quiz($L, 'Đâu là dấu hiệu nhận biết nhân vật chính?',
                ['Giữ vai trò trung tâm, chi phối diễn biến của câu chuyện',
                 'Là nhân vật có nhiều lời thoại nhất',
                 'Luôn là người tốt, không có khuyết điểm',
                 'Là nhân vật được miêu tả ngoại hình đầu tiên'],
                0, 'Nhân vật chính giữ vai trò trung tâm, mọi sự kiện xoay quanh nhân vật này; không nhất thiết phải là người tốt hay nói nhiều nhất.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thuật ngữ với định nghĩa đúng của nó.',
                [['Cốt truyện', 'Chuỗi sự kiện liên kết bằng quan hệ nhân quả'],
                 ['Tình huống truyện', 'Hoàn cảnh đặc biệt làm nảy sinh câu chuyện'],
                 ['Nhân vật', 'Con người tham gia vào sự kiện của truyện'],
                 ['Người kể chuyện', 'Giọng kể dẫn dắt toàn bộ câu chuyện']],
                'Bốn yếu tố cơ bản của truyện: cốt truyện, tình huống truyện, nhân vật, người kể chuyện.');
            $this->matching($L, 'Nối mỗi cách kể với ví dụ minh họa.',
                [['Ngôi thứ nhất', 'Tôi bước vào lớp học mới với bao bỡ ngỡ.'],
                 ['Ngôi thứ ba', 'Cậu bé bước vào lớp học mới với bao bỡ ngỡ.'],
                 ['Lời nhân vật (đối thoại)', '— Cậu là học sinh mới phải không?'],
                 ['Lời người kể (miêu tả)', 'Sân trường rợp bóng phượng vĩ.']],
                'Ngôi thứ nhất xưng "tôi"; ngôi thứ ba gọi nhân vật bằng tên hoặc đại từ ngôi ba; cần phân biệt lời nhân vật và lời người kể.');
            $this->matching($L, 'Nối mỗi chặng của cốt truyện với nội dung tương ứng.',
                [['Mở đầu', 'Giới thiệu nhân vật và hoàn cảnh'],
                 ['Thắt nút', 'Xung đột bắt đầu nảy sinh'],
                 ['Cao trào', 'Xung đột lên đến đỉnh điểm'],
                 ['Mở nút', 'Xung đột được tháo gỡ']],
                'Cốt truyện thường phát triển qua: mở đầu → thắt nút → cao trào → mở nút → kết thúc.');
            $this->matching($L, 'Nối mỗi thể loại tự sự với đặc điểm của nó.',
                [['Truyện ngắn', 'Dung lượng ngắn, tập trung vào ít sự kiện'],
                 ['Tiểu thuyết', 'Dung lượng lớn, nhiều tuyến nhân vật'],
                 ['Truyện cổ tích', 'Có yếu tố kì ảo, kết thúc có hậu'],
                 ['Truyện đồng thoại', 'Nhân vật là loài vật biết nói, biết nghĩ']],
                'Mỗi thể loại tự sự có đặc điểm riêng về dung lượng, nhân vật và cách xây dựng sự kiện.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm CỐT TRUYỆN hoặc LỜI KỂ.',
                [['Mở đầu', 'Cốt truyện'], ['Thắt nút', 'Cốt truyện'], ['Cao trào', 'Cốt truyện'],
                 ['Lời thoại của nhân vật', 'Lời kể'], ['Lời miêu tả của người kể', 'Lời kể'], ['Lời độc thoại nội tâm', 'Lời kể']],
                'Cốt truyện là hệ thống sự kiện; lời kể là cách người kể diễn đạt sự kiện bằng miêu tả, đối thoại, độc thoại.');
            $this->sortQ($L, 'Kéo mỗi cách xưng hô vào nhóm NGÔI THỨ NHẤT hoặc NGÔI THỨ BA.',
                [['Tôi', 'Ngôi thứ nhất'], ['Mình (tự xưng)', 'Ngôi thứ nhất'], ['Chúng tôi', 'Ngôi thứ nhất'],
                 ['Cậu ấy', 'Ngôi thứ ba'], ['Họ', 'Ngôi thứ ba'], ['Nhân vật tên Lan', 'Ngôi thứ ba']],
                'Ngôi thứ nhất xưng tôi/mình/chúng tôi; ngôi thứ ba gọi nhân vật bằng tên hoặc cậu ấy/cô ấy/họ.');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm NHÂN VẬT CHÍNH hoặc NHÂN VẬT PHỤ.',
                [['Câu chuyện xoay quanh nhân vật này', 'Nhân vật chính'],
                 ['Xuất hiện trong mọi sự kiện lớn', 'Nhân vật chính'],
                 ['Chi phối diễn biến và kết thúc truyện', 'Nhân vật chính'],
                 ['Chỉ xuất hiện trong vài cảnh', 'Nhân vật phụ'],
                 ['Làm nền cho nhân vật chính', 'Nhân vật phụ'],
                 ['Không ảnh hưởng diễn biến chính', 'Nhân vật phụ']],
                'Nhân vật chính giữ vai trò trung tâm; nhân vật phụ hỗ trợ, làm nổi bật nhân vật chính.');
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm MIÊU TẢ NGOẠI HÌNH hoặc DIỄN BIẾN TÂM TRẠNG.',
                [['Mái tóc bạc trắng', 'Ngoại hình'], ['Đôi mắt đỏ hoe', 'Ngoại hình'], ['Dáng người gầy gò', 'Ngoại hình'],
                 ['Nỗi lo lắng dâng lên trong lòng', 'Tâm trạng'], ['Niềm vui vỡ òa', 'Tâm trạng'], ['Sự hối hận day dứt', 'Tâm trạng']],
                'Ngoại hình là những gì quan sát được bên ngoài; tâm trạng là diễn biến nội tâm của nhân vật.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cốt truyện là chuỗi các ___ được liên kết bằng quan hệ nhân quả.', [[0, 'sự kiện']],
                'Cốt truyện gồm nhiều sự kiện tạo thành chuỗi, không phải sự kiện đơn lẻ.');
            $this->fill($L, 'Người kể chuyện xưng "tôi" và tham gia vào câu chuyện thuộc ngôi kể thứ ___.', [[0, 'nhất']],
                'Ngôi thứ nhất: người kể xưng "tôi", có mặt trong thế giới truyện.');
            $this->fill($L, 'Đỉnh điểm của xung đột, nơi kịch tính được đẩy lên cao nhất gọi là ___ ___.', [[0, 'cao trào']],
                'Cao trào là đỉnh điểm của xung đột, sau đó diễn biến chuyển sang mở nút.');
            $this->fill($L, 'Hoàn cảnh đặc biệt làm nảy sinh câu chuyện, bộc lộ tính cách nhân vật gọi là ___ ___ ___.', [[0, 'tình huống truyện']],
                'Tình huống truyện là "cái hoàn cảnh" đặc biệt của câu chuyện.');
        }
    }

    private function seedNv10Truyen2(): void
    {
        $L = 'ngu-van-thpt-10-doc-hieu-truyen-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Chi tiết nghệ thuật trong truyện là gì?',
                ['Chi tiết tiêu biểu, giàu sức gợi, góp phần thể hiện chủ đề',
                 'Mọi chi tiết được nhắc đến trong truyện',
                 'Chi tiết chỉ xuất hiện ở phần mở đầu',
                 'Chi tiết miêu tả ngoại hình nhân vật'],
                0, 'Chi tiết nghệ thuật là chi tiết được chọn lọc kĩ, giàu sức gợi, góp phần khắc họa nhân vật và thể hiện chủ đề.');
            $this->quiz($L, 'Chủ đề của tác phẩm là gì?',
                ['Vấn đề trung tâm mà tác phẩm tập trung thể hiện',
                 'Tên gọi của tác phẩm',
                 'Nhân vật chính của truyện',
                 'Bối cảnh xảy ra câu chuyện'],
                0, 'Chủ đề là vấn đề trung tâm, là "cái" mà tác phẩm tập trung phản ánh và thể hiện.');
            $this->quiz($L, 'Thông điệp của truyện thường được rút ra bằng cách nào?',
                ['Tổng hợp ý nghĩa từ toàn bộ diễn biến và kết thúc câu chuyện',
                 'Chỉ đọc phần mở đầu của truyện',
                 'Đếm số lượng nhân vật xuất hiện',
                 'Xem độ dài ngắn của truyện'],
                0, 'Thông điệp là điều tác giả gửi gắm, chỉ hiện ra khi ta khái quát toàn bộ diễn biến, số phận nhân vật và kết thúc.');
            $this->quiz($L, 'Tư tưởng của tác phẩm thể hiện qua đâu?',
                ['Qua toàn bộ hệ thống hình tượng, sự kiện và cách đánh giá của tác giả',
                 'Chỉ qua lời nói của nhân vật chính',
                 'Qua tên của tác phẩm',
                 'Qua số chương, số phần của truyện'],
                0, 'Tư tưởng là cách nhìn, cách đánh giá của tác giả về cuộc sống, thể hiện qua toàn bộ tác phẩm chứ không chỉ một chi tiết.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi khái niệm với nội dung đúng của nó.',
                [['Chi tiết nghệ thuật', 'Chi tiết tiêu biểu, giàu sức gợi'],
                 ['Chủ đề', 'Vấn đề trung tâm của tác phẩm'],
                 ['Thông điệp', 'Điều tác giả muốn gửi gắm'],
                 ['Tư tưởng', 'Cách nhìn, cách đánh giá của tác giả về cuộc sống']],
                'Bốn khái niệm cần phân biệt khi đọc hiểu sâu một truyện ngắn.');
            $this->matching($L, 'Nối mỗi loại chi tiết với ví dụ minh họa.',
                [['Chi tiết miêu tả', 'Cánh đồng lúa chín vàng rực.'],
                 ['Chi tiết hành động', 'Bà cụ run run trao chiếc bánh cho cháu.'],
                 ['Chi tiết tâm lí', 'Lòng cậu se lại khi nghe tin dữ.'],
                 ['Chi tiết tượng trưng', 'Ngọn đèn dầu le lói trong đêm tối.']],
                'Chi tiết nghệ thuật có thể là miêu tả, hành động, tâm lí hoặc mang ý nghĩa tượng trưng.');
            $this->matching($L, 'Nối mỗi bước đọc hiểu với việc cần làm.',
                [['Tìm hiểu bối cảnh', 'Xác định thời gian, không gian câu chuyện'],
                 ['Tóm tắt cốt truyện', 'Nắm chuỗi sự kiện chính'],
                 ['Phân tích nhân vật', 'Tìm hiểu tính cách qua hành động, lời nói'],
                 ['Rút ra chủ đề', 'Khái quát vấn đề trung tâm']],
                'Quy trình đọc hiểu: bối cảnh → cốt truyện → nhân vật → chủ đề, thông điệp.');
            $this->matching($L, 'Nối mỗi mức độ với dạng câu hỏi đọc hiểu.',
                [['Nhận biết', 'Truyện có những nhân vật nào?'],
                 ['Thông hiểu', 'Vì sao nhân vật lại hành động như vậy?'],
                 ['Vận dụng', 'Em rút ra bài học gì cho bản thân?'],
                 ['Vận dụng cao', 'So sánh cách xây dựng nhân vật ở hai truyện.']],
                'Câu hỏi đọc hiểu đi từ nhận biết sự kiện đến lí giải, vận dụng và so sánh đánh giá.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm CHI TIẾT NGHỆ THUẬT hoặc CHI TIẾT THÔNG THƯỜNG.',
                [['Chiếc lá cuối cùng vẫn bám trên cành', 'Nghệ thuật'],
                 ['Giọt nước mắt lăn trên gò má nhăn nheo', 'Nghệ thuật'],
                 ['Tiếng cười giòn tan xua đi mệt mỏi', 'Nghệ thuật'],
                 ['Con số 5 trên bảng điểm', 'Thông thường'],
                 ['Giờ học bắt đầu lúc 7 giờ', 'Thông thường'],
                 ['Cửa lớp được sơn màu xanh', 'Thông thường']],
                'Chi tiết nghệ thuật giàu sức gợi và gắn với chủ đề; chi tiết thông thường chỉ cung cấp thông tin nền.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUỘC NỘI DUNG hoặc THUỘC NGHỆ THUẬT.',
                [['Chủ đề', 'Nội dung'], ['Thông điệp', 'Nội dung'], ['Tình cảm của tác giả', 'Nội dung'],
                 ['Ngôi kể', 'Nghệ thuật'], ['Kết cấu', 'Nghệ thuật'], ['Ngôn ngữ', 'Nghệ thuật']],
                'Nội dung là "viết về cái gì"; nghệ thuật là "viết như thế nào".');
            $this->sortQ($L, 'Kéo mỗi câu hỏi vào nhóm CÂU HỎI VỀ NHÂN VẬT hoặc CÂU HỎI VỀ CHỦ ĐỀ.',
                [['Nhân vật có tính cách như thế nào?', 'Nhân vật'],
                 ['Hành động nào bộc lộ rõ nhất tính cách?', 'Nhân vật'],
                 ['Diễn biến tâm lí nhân vật ra sao?', 'Nhân vật'],
                 ['Tác phẩm đề cập vấn đề gì của cuộc sống?', 'Chủ đề'],
                 ['Thông điệp tác giả gửi gắm là gì?', 'Chủ đề'],
                 ['Nhan đề gợi mở điều gì?', 'Chủ đề']],
                'Câu hỏi về nhân vật tập trung vào tính cách, hành động, tâm lí; câu hỏi về chủ đề hướng tới vấn đề và thông điệp.');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm TÍCH CỰC hoặc TIÊU CỰC (để đánh giá tính cách nhân vật).',
                [['Giúp đỡ bạn lúc khó khăn', 'Tích cực'], ['Nhường nhịn em nhỏ', 'Tích cực'], ['Dũng cảm nhận lỗi', 'Tích cực'],
                 ['Đổ lỗi cho người khác', 'Tiêu cực'], ['Ích kỉ chỉ nghĩ cho mình', 'Tiêu cực'], ['Nói dối để trốn tránh', 'Tiêu cực']],
                'Tính cách nhân vật bộc lộ qua hành động: việc làm tích cực gợi phẩm chất tốt, việc làm tiêu cực gợi thói xấu.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Chi tiết ___ ___ là chi tiết tiêu biểu, giàu sức gợi, góp phần thể hiện chủ đề.', [[0, 'nghệ thuật']],
                'Chi tiết nghệ thuật được tác giả chọn lọc kĩ lưỡng, "đắt" về ý nghĩa.');
            $this->fill($L, 'Vấn đề trung tâm mà tác phẩm tập trung thể hiện gọi là ___ ___.', [[0, 'chủ đề']],
                'Chủ đề là vấn đề trung tâm, là linh hồn nội dung của tác phẩm.');
            $this->fill($L, 'Điều tác giả muốn gửi gắm tới người đọc qua tác phẩm gọi là ___ ___.', [[0, 'thông điệp']],
                'Thông điệp thường là bài học, lời nhắn nhủ rút ra từ câu chuyện.');
            $this->fill($L, 'Khi đọc hiểu truyện, sau khi tóm tắt cốt truyện cần phân tích ___ ___ để hiểu tính cách.', [[0, 'nhân vật']],
                'Phân tích nhân vật qua ngoại hình, hành động, lời nói và diễn biến tâm lí.');
        }
    }

    private function seedNv10Tho1(): void
    {
        $L = 'ngu-van-thpt-10-doc-hieu-tho-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thơ lục bát có đặc điểm gì về số chữ?',
                ['Câu 6 chữ xen kẽ câu 8 chữ',
                 'Mỗi câu đều có 7 chữ',
                 'Mỗi câu đều có 5 chữ',
                 'Số chữ mỗi câu hoàn toàn tự do'],
                0, 'Lục bát: câu lục (6 chữ) xen câu bát (8 chữ), gieo vần ở tiếng thứ 6 của câu lục và câu bát.');
            $this->quiz($L, 'Nhịp trong thơ là gì?',
                ['Cách ngắt nhịp tạo tiết tấu cho câu thơ',
                 'Số lượng chữ trong mỗi câu thơ',
                 'Vần được gieo ở cuối câu thơ',
                 'Tên gọi của tác giả bài thơ'],
                0, 'Nhịp là cách ngắt câu thơ thành từng đoạn tạo tiết tấu, ví dụ nhịp 2/2, 4/4 trong câu 8 chữ.');
            $this->quiz($L, 'Vần trong thơ có tác dụng gì?',
                ['Tạo nhạc tính, liên kết các câu thơ với nhau',
                 'Làm cho bài thơ dài hơn',
                 'Thay thế hoàn toàn nhịp điệu',
                 'Không có tác dụng đặc biệt'],
                0, 'Vần là sự lặp lại âm thanh ở cuối (hoặc giữa) câu thơ, tạo nhạc tính và sự liên kết.');
            $this->quiz($L, 'Thơ tự do khác thơ cách luật ở điểm nào?',
                ['Không bị ràng buộc chặt chẽ về số chữ, số câu, vần, nhịp',
                 'Luôn phải gieo vần ở mọi câu',
                 'Mỗi câu bắt buộc đúng 7 chữ',
                 'Không được bộc lộ cảm xúc'],
                0, 'Thơ tự do phá bỏ khuôn mẫu gò bó của thơ cách luật, nhịp điệu theo cảm xúc tự nhiên.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thể thơ với đặc điểm của nó.',
                [['Lục bát', 'Câu 6 chữ – câu 8 chữ xen kẽ'],
                 ['Song thất lục bát', 'Hai câu 7 chữ – câu 6 chữ – câu 8 chữ'],
                 ['Thất ngôn bát cú', '8 câu, mỗi câu 7 chữ'],
                 ['Ngũ ngôn', 'Mỗi câu 5 chữ']],
                'Nhận diện thể thơ qua số câu và số chữ mỗi câu.');
            $this->matching($L, 'Nối mỗi khái niệm với nội dung đúng.',
                [['Vần', 'Âm thanh lặp lại ở cuối câu thơ'],
                 ['Nhịp', 'Cách ngắt câu tạo tiết tấu'],
                 ['Thanh điệu', 'Âm bằng – trắc tạo nhạc tính'],
                 ['Thể thơ', 'Khuôn mẫu về số câu, số chữ']],
                'Bốn yếu tố hình thức cơ bản cần nắm khi đọc hiểu thơ.');
            $this->matching($L, 'Nối mỗi cách gieo vần với mô tả.',
                [['Vần chân', 'Vần ở cuối câu thơ'],
                 ['Vần lưng', 'Vần ở giữa câu thơ'],
                 ['Vần liền', 'Hai câu liền nhau cùng vần'],
                 ['Vần cách', 'Câu cách nhau một câu cùng vần']],
                'Vần chân phổ biến nhất; vần lưng tạo nhạc tính đặc biệt; vần liền và vần cách là cách gieo vần.');
            $this->matching($L, 'Nối mỗi dấu hiệu với thể thơ tương ứng.',
                [['Câu 6/8 xen kẽ', 'Lục bát'],
                 ['8 câu, mỗi câu 7 chữ', 'Thất ngôn bát cú'],
                 ['Số chữ, số câu tự do', 'Thơ tự do'],
                 ['Mỗi câu 5 chữ', 'Thơ ngũ ngôn']],
                'Dấu hiệu số câu – số chữ giúp nhận diện nhanh thể thơ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thể thơ vào nhóm THƠ CÁCH LUẬT hoặc THƠ TỰ DO.',
                [['Lục bát', 'Cách luật'], ['Thất ngôn bát cú', 'Cách luật'], ['Song thất lục bát', 'Cách luật'], ['Ngũ ngôn', 'Cách luật'],
                 ['Không giới hạn số chữ, số câu', 'Tự do'], ['Nhịp điệu theo cảm xúc tự nhiên', 'Tự do']],
                'Thơ cách luật tuân thủ khuôn mẫu chặt chẽ; thơ tự do không bị ràng buộc về hình thức.');
            $this->sortQ($L, 'Kéo mỗi tiếng vào nhóm THANH BẰNG hoặc THANH TRẮC.',
                [['la (thanh ngang)', 'Bằng'], ['là (thanh huyền)', 'Bằng'], ['lờ (thanh huyền)', 'Bằng'],
                 ['lá (thanh sắc)', 'Trắc'], ['lả (thanh hỏi)', 'Trắc'], ['lạ (thanh nặng)', 'Trắc']],
                'Thanh bằng gồm thanh ngang và thanh huyền; thanh trắc gồm sắc, hỏi, ngã, nặng.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUỘC HÌNH THỨC hoặc THUỘC NỘI DUNG của bài thơ.',
                [['Thể thơ', 'Hình thức'], ['Vần, nhịp', 'Hình thức'], ['Từ ngữ, hình ảnh', 'Hình thức'],
                 ['Cảm xúc trữ tình', 'Nội dung'], ['Chủ đề', 'Nội dung'], ['Tư tưởng', 'Nội dung']],
                'Hình thức là "viết như thế nào"; nội dung là "viết về cái gì".');
            $this->sortQ($L, 'Kéo mỗi câu thơ vào nhóm CÂU 6 CHỮ hoặc CÂU 8 CHỮ.',
                [['Trăm năm trong cõi người ta', 'Câu 6 chữ'],
                 ['Trải qua một cuộc bể dâu', 'Câu 6 chữ'],
                 ['Đau đớn thay phận đàn bà', 'Câu 6 chữ'],
                 ['Chữ tài chữ mệnh khéo là ghét nhau', 'Câu 8 chữ'],
                 ['Những điều trông thấy mà đau đớn lòng', 'Câu 8 chữ'],
                 ['Lời rằng bạc mệnh cũng là lời chung', 'Câu 8 chữ']],
                'Đếm số tiếng trong câu: câu lục có 6 tiếng, câu bát có 8 tiếng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thơ lục bát gồm câu ___ chữ và câu ___ chữ xen kẽ nhau.', [[0, '6'], [1, '8']],
                'Câu lục 6 chữ xen câu bát 8 chữ là đặc trưng của thể lục bát.');
            $this->fill($L, 'Âm thanh được lặp lại ở cuối các câu thơ, tạo nhạc tính gọi là ___.', [[0, 'vần']],
                'Vần tạo sự liên kết và nhạc tính cho bài thơ.');
            $this->fill($L, 'Cách ngắt câu tạo tiết tấu cho bài thơ gọi là ___ thơ.', [[0, 'nhịp']],
                'Nhịp 2/2, 4/4, 3/3... tạo tiết tấu riêng cho từng câu thơ.');
            $this->fill($L, 'Thể thơ không bị ràng buộc về số chữ, số câu, vần, nhịp gọi là thơ ___ ___.', [[0, 'tự do']],
                'Thơ tự do đề cao cảm xúc tự nhiên, phá bỏ khuôn mẫu gò bó.');
        }
    }

    private function seedNv10Tho2(): void
    {
        $L = 'ngu-van-thpt-10-doc-hieu-tho-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hình ảnh thơ là gì?',
                ['Hình ảnh được gợi lên từ ngôn ngữ thơ, giàu sức gợi cảm',
                 'Ảnh minh họa in kèm theo bài thơ',
                 'Chân dung của tác giả bài thơ',
                 'Khổ thơ dài nhất trong bài'],
                0, 'Hình ảnh thơ không phải ảnh chụp mà là hình ảnh hiện lên trong tâm trí người đọc từ ngôn ngữ thơ.');
            $this->quiz($L, 'Chủ thể trữ tình trong thơ là ai?',
                ['Người bộc lộ cảm xúc, suy nghĩ trong bài thơ',
                 'Nhân vật được nhắc đến trong bài thơ',
                 'Người đọc bài thơ',
                 'Nhà phê bình văn học'],
                0, 'Chủ thể trữ tình (thường xưng "tôi", "ta", "anh", "em") là người trực tiếp bộc lộ cảm xúc.');
            $this->quiz($L, 'Cảm xúc trữ tình thường được bộc lộ qua đâu?',
                ['Qua hình ảnh thơ, ngôn ngữ và giọng điệu',
                 'Qua số lượng khổ thơ trong bài',
                 'Chỉ qua nhan đề của bài thơ',
                 'Qua năm sáng tác bài thơ'],
                0, 'Cảm xúc trữ tình thấm đẫm trong hình ảnh, từ ngữ và giọng điệu của toàn bài.');
            $this->quiz($L, 'Từ ngữ gợi hình, gợi cảm trong thơ có tác dụng gì?',
                ['Làm hình ảnh thơ sinh động, gợi cảm xúc cho người đọc',
                 'Làm cho bài thơ dài hơn',
                 'Thay thế hoàn toàn vần điệu',
                 'Không có tác dụng đặc biệt'],
                0, 'Từ láy, từ gợi hình, gợi thanh làm câu thơ sinh động như có hình, có tiếng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi khái niệm với nội dung đúng của nó.',
                [['Hình ảnh thơ', 'Hình ảnh gợi lên từ ngôn ngữ thơ'],
                 ['Cảm xúc trữ tình', 'Tình cảm của chủ thể trữ tình'],
                 ['Tứ thơ', 'Ý tưởng trung tâm của bài thơ'],
                 ['Giọng điệu', 'Sắc thái tình cảm bao trùm bài thơ']],
                'Bốn khái niệm cốt lõi khi đọc hiểu một bài thơ trữ tình.');
            $this->matching($L, 'Nối mỗi nhóm từ ngữ với tác dụng gợi tả.',
                [['Từ láy gợi hình', 'Gợi dáng vẻ sinh động'],
                 ['Từ láy gợi thanh', 'Gợi âm thanh'],
                 ['Từ chỉ màu sắc', 'Gợi bức tranh cụ thể'],
                 ['Động từ mạnh', 'Gợi hành động dứt khoát']],
                'Từ ngữ giàu sức gợi là chất liệu quan trọng tạo nên hình ảnh thơ.');
            $this->matching($L, 'Nối mỗi giọng điệu với sắc thái tình cảm.',
                [['Trầm lắng', 'Suy tư, sâu lắng'],
                 ['Hào hùng', 'Mạnh mẽ, sôi nổi'],
                 ['Thiết tha', 'Dịu dàng, đằm thắm'],
                 ['Bi tráng', 'Đau thương mà hùng tráng']],
                'Giọng điệu là sắc thái tình cảm bao trùm, chi phối cách cảm nhận bài thơ.');
            $this->matching($L, 'Nối mỗi bước cảm thụ thơ với việc cần làm.',
                [['Đọc diễn cảm', 'Cảm nhận nhịp điệu, nhạc tính'],
                 ['Tìm hình ảnh đẹp', 'Ghi lại hình ảnh gây ấn tượng'],
                 ['Xác định cảm xúc', 'Gọi tên tình cảm chủ đạo'],
                 ['Liên hệ bản thân', 'Rút ra cảm nhận riêng của mình']],
                'Quy trình cảm thụ: đọc → tìm hình ảnh → gọi tên cảm xúc → liên hệ bản thân.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ láy vào nhóm GỢI HÌNH hoặc GỢI THANH.',
                [['Lấp lánh', 'Gợi hình'], ['Xanh biếc', 'Gợi hình'], ['Mênh mông', 'Gợi hình'],
                 ['Rì rào', 'Gợi thanh'], ['Thánh thót', 'Gợi thanh'], ['Rì rầm', 'Gợi thanh']],
                'Từ láy gợi hình gợi dáng vẻ, màu sắc; từ láy gợi thanh gợi âm thanh.');
            $this->sortQ($L, 'Kéo mỗi hình ảnh vào nhóm HÌNH ẢNH THIÊN NHIÊN hoặc HÌNH ẢNH CON NGƯỜI.',
                [['Vầng trăng', 'Thiên nhiên'], ['Dòng sông', 'Thiên nhiên'], ['Cánh đồng', 'Thiên nhiên'],
                 ['Người mẹ', 'Con người'], ['Anh bộ đội', 'Con người'], ['Cô giáo', 'Con người']],
                'Hình ảnh thơ có thể lấy từ thiên nhiên hoặc từ đời sống con người.');
            $this->sortQ($L, 'Kéo mỗi từ ngữ cảm xúc vào nhóm CẢM XÚC VUI hoặc CẢM XÚC BUỒN.',
                [['Hân hoan', 'Vui'], ['Rộn ràng', 'Vui'], ['Phấn khởi', 'Vui'],
                 ['Ngậm ngùi', 'Buồn'], ['Xót xa', 'Buồn'], ['Lặng lẽ', 'Buồn']],
                'Gọi tên đúng cảm xúc là bước quan trọng khi xác định cảm xúc trữ tình.');
            $this->sortQ($L, 'Kéo mỗi vai trò vào nhóm CHỦ THỂ TRỮ TÌNH hoặc ĐỐI TƯỢNG TRỮ TÌNH.',
                [['Người bộc lộ cảm xúc trong thơ', 'Chủ thể'],
                 ['Nhân vật "tôi" trong bài thơ', 'Chủ thể'],
                 ['Tác giả bộc lộ tâm sự', 'Chủ thể'],
                 ['Người được nhà thơ gửi gắm tình cảm', 'Đối tượng'],
                 ['Quê hương trong nỗi nhớ', 'Đối tượng'],
                 ['Người mẹ được ngợi ca', 'Đối tượng']],
                'Chủ thể trữ tình bộc lộ cảm xúc; đối tượng trữ tình là nơi cảm xúc hướng tới.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hình ảnh được gợi lên từ ngôn ngữ thơ gọi là ___ ___ ___.', [[0, 'hình ảnh thơ']],
                'Hình ảnh thơ hiện lên trong tâm trí người đọc từ ngôn từ giàu sức gợi.');
            $this->fill($L, 'Người bộc lộ cảm xúc, suy nghĩ trong bài thơ gọi là ___ ___ ___ ___.', [[0, 'chủ thể trữ tình']],
                'Chủ thể trữ tình là "người nói" trong thơ trữ tình.');
            $this->fill($L, 'Ý tưởng trung tâm, làm nên "xương sống" của bài thơ gọi là ___ ___.', [[0, 'tứ thơ']],
                'Tứ thơ là mạch ý tưởng xuyên suốt, gắn kết các hình ảnh trong bài.');
            $this->fill($L, 'Sắc thái tình cảm bao trùm toàn bộ bài thơ gọi là ___ ___.', [[0, 'giọng điệu']],
                'Giọng điệu có thể trầm lắng, hào hùng, thiết tha, bi tráng...');
        }
    }

    // ================= NGỮ VĂN 11 =================

    private function seedNv11DaoLi1(): void
    {
        $L = 'ngu-van-thpt-11-tu-tuong-dao-li-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nghị luận về tư tưởng đạo lí bàn về vấn đề gì?',
                ['Các quan niệm về đạo đức, lối sống, lẽ sống của con người',
                 'Các hiện tượng thời sự nóng hổi trong xã hội',
                 'Một tác phẩm văn học cụ thể',
                 'Các sự kiện lịch sử đã qua'],
                0, 'Nghị luận về tư tưởng đạo lí bàn về các vấn đề thuộc lĩnh vực tư tưởng, đạo đức, lối sống.');
            $this->quiz($L, 'Thao tác lập luận giải thích là gì?',
                ['Làm rõ nghĩa của khái niệm, vấn đề được bàn luận',
                 'Kể lại một câu chuyện có thật',
                 'Miêu tả chi tiết sự vật, hiện tượng',
                 'Bộc lộ cảm xúc cá nhân'],
                0, 'Giải thích là làm rõ nghĩa của khái niệm, giúp người đọc hiểu đúng vấn đề.');
            $this->quiz($L, 'Thao tác lập luận chứng minh sử dụng gì?',
                ['Dẫn chứng thực tế và lí lẽ thuyết phục',
                 'Cảm xúc cá nhân duy nhất',
                 'Truyện cười gây vui',
                 'Lời thoại của nhân vật'],
                0, 'Chứng minh dùng dẫn chứng (số liệu, tấm gương, sự việc) và lí lẽ để làm sáng tỏ luận điểm.');
            $this->quiz($L, 'Luận điểm trong bài nghị luận là gì?',
                ['Ý kiến chính cần được chứng minh, làm sáng tỏ',
                 'Câu văn mở đầu của bài viết',
                 'Dẫn chứng được đưa ra',
                 'Phần kết bài của bài viết'],
                0, 'Luận điểm là ý chính của bài; mỗi luận điểm cần được triển khai bằng luận cứ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thao tác lập luận với nội dung của nó.',
                [['Giải thích', 'Làm rõ nghĩa của khái niệm'],
                 ['Chứng minh', 'Dùng dẫn chứng và lí lẽ'],
                 ['Bình luận', 'Đánh giá, bàn bạc mở rộng'],
                 ['So sánh', 'Đối chiếu để làm nổi bật vấn đề']],
                'Bốn thao tác lập luận cơ bản trong văn nghị luận xã hội.');
            $this->matching($L, 'Nối mỗi khái niệm với vai trò của nó.',
                [['Luận điểm', 'Ý chính cần chứng minh'],
                 ['Luận cứ', 'Lí lẽ và dẫn chứng'],
                 ['Lập luận', 'Cách sắp xếp lí lẽ chặt chẽ'],
                 ['Kết luận', 'Khẳng định lại vấn đề']],
                'Luận điểm – luận cứ – lập luận là ba yếu tố tạo nên sức thuyết phục.');
            $this->matching($L, 'Nối mỗi đề bài với dạng nghị luận tương ứng.',
                [['Bàn về lòng nhân ái', 'Tư tưởng đạo lí'],
                 ['Bàn về ý chí vươn lên', 'Tư tưởng đạo lí'],
                 ['Bàn về tai nạn giao thông', 'Hiện tượng đời sống'],
                 ['Bàn về bạo lực học đường', 'Hiện tượng đời sống']],
                'Đề về quan niệm sống thuộc tư tưởng đạo lí; đề về sự việc cụ thể thuộc hiện tượng đời sống.');
            $this->matching($L, 'Nối mỗi câu tục ngữ với chủ đề tư tưởng của nó.',
                [['Có công mài sắt, có ngày nên kim', 'Ý chí, kiên trì'],
                 ['Uống nước nhớ nguồn', 'Lòng biết ơn'],
                 ['Một cây làm chẳng nên non', 'Tinh thần đoàn kết'],
                 ['Học thầy không tày học bạn', 'Việc học hỏi']],
                'Tục ngữ là kho dẫn chứng quý cho dạng nghị luận về tư tưởng đạo lí.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đề bài vào nhóm TƯ TƯỞNG ĐẠO LÍ hoặc HIỆN TƯỢNG ĐỜI SỐNG.',
                [['Bàn về lòng hiếu thảo', 'Tư tưởng đạo lí'],
                 ['Bàn về đức tính trung thực', 'Tư tưởng đạo lí'],
                 ['Bàn về tình thầy trò', 'Tư tưởng đạo lí'],
                 ['Bàn về lối sống vô cảm', 'Hiện tượng đời sống'],
                 ['Bàn về ô nhiễm môi trường', 'Hiện tượng đời sống'],
                 ['Bàn về nghiện game ở học sinh', 'Hiện tượng đời sống']],
                'Tư tưởng đạo lí bàn quan niệm sống; hiện tượng đời sống bàn sự việc cụ thể.');
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm LUẬN ĐIỂM hoặc LUẬN CỨ.',
                [['Lòng nhân ái làm cuộc sống tốt đẹp hơn', 'Luận điểm'],
                 ['Cần rèn ý chí từ những việc nhỏ', 'Luận điểm'],
                 ['Trung thực là nền tảng của niềm tin', 'Luận điểm'],
                 ['Câu chuyện cậu bé nhường áo ấm cho bạn', 'Luận cứ'],
                 ['Số liệu người tham gia hiến máu tình nguyện', 'Luận cứ'],
                 ['Tấm gương bạn học vượt khó vươn lên', 'Luận cứ']],
                'Luận điểm là ý chính; luận cứ là lí lẽ, dẫn chứng làm sáng tỏ luận điểm.');
            $this->sortQ($L, 'Kéo mỗi chức năng vào nhóm MỞ BÀI hoặc KẾT BÀI.',
                [['Nêu vấn đề nghị luận', 'Mở bài'],
                 ['Dẫn dắt người đọc vào đề', 'Mở bài'],
                 ['Trích dẫn câu nói liên quan', 'Mở bài'],
                 ['Khẳng định lại vấn đề', 'Kết bài'],
                 ['Rút ra bài học nhận thức, hành động', 'Kết bài'],
                 ['Lời nhắn gửi tới người đọc', 'Kết bài']],
                'Mở bài nêu vấn đề; kết bài khẳng định lại và mở ra bài học.');
            $this->sortQ($L, 'Kéo mỗi dẫn chứng vào nhóm TIÊU BIỂU hoặc KHÔNG TIÊU BIỂU.',
                [['Tấm gương người thật, việc thật', 'Tiêu biểu'],
                 ['Câu chuyện gần gũi, xác thực', 'Tiêu biểu'],
                 ['Việc làm cụ thể, có sức thuyết phục', 'Tiêu biểu'],
                 ['Chuyện bịa đặt, thiếu căn cứ', 'Không tiêu biểu'],
                 ['Ví dụ chung chung, sáo rỗng', 'Không tiêu biểu'],
                 ['Dẫn chứng không liên quan đến đề', 'Không tiêu biểu']],
                'Dẫn chứng tốt phải tiêu biểu, xác thực và gắn chặt với luận điểm.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bài văn nghị luận về quan niệm đạo đức, lối sống thuộc dạng nghị luận về ___ ___ ___ ___.', [[0, 'tư tưởng đạo lí']],
                'Tư tưởng đạo lí bàn về lĩnh vực tư tưởng, đạo đức, lối sống.');
            $this->fill($L, 'Ý kiến chính cần chứng minh trong bài nghị luận gọi là ___ ___.', [[0, 'luận điểm']],
                'Bài văn thường có luận điểm chính và các luận điểm phụ.');
            $this->fill($L, 'Thao tác dùng dẫn chứng và lí lẽ để làm sáng tỏ luận điểm gọi là ___ ___.', [[0, 'chứng minh']],
                'Chứng minh là thao tác quan trọng nhất tạo sức thuyết phục.');
            $this->fill($L, 'Phần cuối bài khẳng định lại vấn đề và rút ra bài học gọi là ___ ___.', [[0, 'kết bài']],
                'Kết bài khép lại vấn đề, để lại dư âm cho người đọc.');
        }
    }

    private function seedNv11DaoLi2(): void
    {
        $L = 'ngu-van-thpt-11-tu-tuong-dao-li-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi phân tích đề nghị luận, việc đầu tiên cần làm là gì?',
                ['Xác định vấn đề nghị luận, phạm vi và yêu cầu của đề',
                 'Viết ngay phần kết bài',
                 'Tìm dẫn chứng trước khi hiểu đề',
                 'Đếm số chữ cần viết'],
                0, 'Phân tích đề giúp xác định đúng vấn đề, tránh lạc đề: vấn đề là gì, bàn ở góc độ nào.');
            $this->quiz($L, 'Dẫn chứng trong bài nghị luận cần đảm bảo yêu cầu gì?',
                ['Tiêu biểu, xác thực, phù hợp với luận điểm',
                 'Càng nhiều dẫn chứng càng tốt',
                 'Chỉ cần hay, không cần đúng',
                 'Dài và khó hiểu để gây ấn tượng'],
                0, 'Dẫn chứng phải chọn lọc: tiêu biểu, xác thực, gắn chặt với luận điểm đang chứng minh.');
            $this->quiz($L, 'Bố cục của bài văn nghị luận xã hội gồm mấy phần?',
                ['Ba phần: mở bài, thân bài, kết bài',
                 'Một phần duy nhất',
                 'Hai phần: mở bài và kết bài',
                 'Bốn phần bắt buộc'],
                0, 'Bố cục ba phần là chuẩn mực: mở bài nêu vấn đề, thân bài triển khai, kết bài khẳng định.');
            $this->quiz($L, 'Câu chuyển đoạn trong bài văn có tác dụng gì?',
                ['Liên kết các luận điểm, giúp bài viết mạch lạc',
                 'Trang trí cho bài viết thêm đẹp',
                 'Thay thế hoàn toàn phần kết bài',
                 'Kéo dài bài viết cho đủ số chữ'],
                0, 'Câu chuyển đoạn tạo sự liên kết giữa các đoạn, giúp mạch lập luận trôi chảy.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phần của thân bài với nội dung cần triển khai.',
                [['Giải thích', 'Làm rõ khái niệm, vấn đề của đề bài'],
                 ['Bàn luận', 'Phân tích đúng – sai, mặt tốt – xấu'],
                 ['Chứng minh', 'Đưa dẫn chứng làm sáng tỏ'],
                 ['Mở rộng', 'Bàn thêm, lật lại vấn đề, liên hệ bản thân']],
                'Thân bài nghị luận về tư tưởng đạo lí thường triển khai theo trình tự này.');
            $this->matching($L, 'Nối mỗi lỗi thường gặp với cách khắc phục.',
                [['Lạc đề', 'Phân tích kĩ đề trước khi viết'],
                 ['Thiếu dẫn chứng', 'Chuẩn bị kho dẫn chứng theo chủ đề'],
                 ['Lập luận lỏng lẻo', 'Sắp xếp luận điểm theo trình tự hợp lí'],
                 ['Diễn đạt lủng củng', 'Viết câu ngắn gọn, rõ ý']],
                'Nhận diện lỗi giúp tránh khi làm bài và tự chữa bài hiệu quả.');
            $this->matching($L, 'Nối mỗi dạng đề với hướng triển khai.',
                [['Bàn về một đức tính', 'Giải thích – biểu hiện – ý nghĩa – rèn luyện'],
                 ['Bàn về một thói xấu', 'Giải thích – biểu hiện – tác hại – khắc phục'],
                 ['Bàn về một quan niệm sống', 'Hiểu thế nào – đúng/sai – bài học'],
                 ['Bàn về ý nghĩa một câu nói', 'Giải thích câu nói – bàn luận – bài học']],
                'Mỗi dạng đề có hướng triển khai phù hợp; nắm được sẽ lập dàn ý nhanh.');
            $this->matching($L, 'Nối mỗi câu nói với bài học có thể rút ra.',
                [['Thất bại là mẹ thành công', 'Đừng nản lòng trước khó khăn'],
                 ['Đi một ngày đàng, học một sàng khôn', 'Chăm đi, chăm học hỏi'],
                 ['Ăn quả nhớ kẻ trồng cây', 'Biết ơn người đi trước'],
                 ['Có chí thì nên', 'Kiên trì sẽ thành công']],
                'Tục ngữ, danh ngôn vừa là dẫn chứng vừa gợi mở bài học.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cách mở bài vào nhóm MỞ BÀI HAY hoặc MỞ BÀI CHƯA HAY.',
                [['Nêu vấn đề một cách tự nhiên, gợi hứng thú', 'Hay'],
                 ['Dẫn dắt bằng câu chuyện, câu nói liên quan', 'Hay'],
                 ['Đi thẳng vào vấn đề rõ ràng', 'Hay'],
                 ['Chép lại nguyên văn đề bài', 'Chưa hay'],
                 ['Viết lan man, xa vấn đề', 'Chưa hay'],
                 ['Mở bài dài hơn cả thân bài', 'Chưa hay']],
                'Mở bài hay ngắn gọn, tự nhiên, nêu trúng vấn đề nghị luận.');
            $this->sortQ($L, 'Kéo mỗi dẫn chứng vào nhóm PHÙ HỢP hoặc KHÔNG PHÙ HỢP với đề "bàn về lòng kiên trì".',
                [['Câu chuyện người thợ rèn kiên nhẫn', 'Phù hợp'],
                 ['Tấm gương học sinh nghèo vượt khó', 'Phù hợp'],
                 ['Câu "Có công mài sắt, có ngày nên kim"', 'Phù hợp'],
                 ['Câu chuyện về lòng hiếu thảo', 'Không phù hợp'],
                 ['Số liệu ô nhiễm môi trường', 'Không phù hợp'],
                 ['Chuyện cổ tích không liên quan', 'Không phù hợp']],
                'Dẫn chứng phải gắn chặt với vấn đề nghị luận của đề bài.');
            $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm THUỘC MỞ BÀI hoặc THUỘC THÂN BÀI.',
                [['Nêu vấn đề nghị luận', 'Mở bài'],
                 ['Dẫn dắt vào đề', 'Mở bài'],
                 ['Giải thích khái niệm', 'Thân bài'],
                 ['Đưa dẫn chứng chứng minh', 'Thân bài'],
                 ['Bình luận, mở rộng', 'Thân bài'],
                 ['Liên hệ bản thân', 'Thân bài']],
                'Mở bài chỉ nêu vấn đề; mọi triển khai, chứng minh thuộc thân bài.');
            $this->sortQ($L, 'Kéo mỗi biểu hiện vào nhóm BIỂU HIỆN CỦA LÒNG NHÂN ÁI hoặc KHÔNG PHẢI.',
                [['Giúp đỡ người gặp hoạn nạn', 'Biểu hiện'],
                 ['Chia sẻ với người khó khăn', 'Biểu hiện'],
                 ['Cảm thông với nỗi đau người khác', 'Biểu hiện'],
                 ['Thờ ơ trước nỗi khổ của người khác', 'Không phải'],
                 ['Lợi dụng lòng tốt của người khác', 'Không phải'],
                 ['Cười cợt trên nỗi đau người khác', 'Không phải']],
                'Tìm biểu hiện cụ thể giúp bài văn chứng minh sinh động, thuyết phục.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trước khi viết, cần ___ ___ đề để xác định đúng vấn đề nghị luận.', [[0, 'phân tích']],
                'Phân tích đề là bước đầu tiên, quyết định bài viết có đúng hướng hay không.');
            $this->fill($L, 'Dẫn chứng phải ___ ___, xác thực và phù hợp với luận điểm.', [[0, 'tiêu biểu']],
                'Dẫn chứng tiêu biểu, xác thực tạo sức thuyết phục cho bài văn.');
            $this->fill($L, 'Câu ___ ___ giúp liên kết các đoạn văn mạch lạc với nhau.', [[0, 'chuyển đoạn']],
                'Câu chuyển đoạn tạo mạch liên kết giữa các luận điểm.');
            $this->fill($L, 'Bố cục bài văn nghị luận gồm ba phần: mở bài, ___ ___, kết bài.', [[0, 'thân bài']],
                'Thân bài là phần triển khai các luận điểm bằng luận cứ.');
        }
    }

    private function seedNv11HienTuong1(): void
    {
        $L = 'ngu-van-thpt-11-hien-tuong-doi-song-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nghị luận về hiện tượng đời sống bàn về vấn đề gì?',
                ['Các sự việc, hiện tượng nổi bật diễn ra trong đời sống xã hội',
                 'Các quan niệm trừu tượng về đạo đức',
                 'Một tác phẩm văn học cụ thể',
                 'Các sự kiện lịch sử trong quá khứ'],
                0, 'Hiện tượng đời sống là những sự việc, hiện tượng có thật, nổi bật trong đời sống hằng ngày.');
            $this->quiz($L, 'Bước đầu tiên khi làm bài nghị luận về hiện tượng đời sống là gì?',
                ['Mô tả, nhận diện hiện tượng được nêu trong đề',
                 'Đề xuất giải pháp ngay',
                 'Phê phán hiện tượng',
                 'Viết kết bài'],
                0, 'Cần nhận diện đúng hiện tượng: đó là hiện tượng gì, biểu hiện ra sao, rồi mới đánh giá.');
            $this->quiz($L, 'Khi đánh giá một hiện tượng đời sống cần đảm bảo gì?',
                ['Nhìn nhận khách quan, thấy cả mặt tích cực và tiêu cực',
                 'Chỉ nêu mặt tốt của hiện tượng',
                 'Chỉ phê phán mặt xấu',
                 'Đánh giá theo cảm tính cá nhân'],
                0, 'Đánh giá khách quan, toàn diện giúp bài văn thuyết phục và sâu sắc.');
            $this->quiz($L, 'Dẫn chứng cho dạng bài này thường lấy từ đâu?',
                ['Từ thực tế đời sống: báo chí, sự việc có thật',
                 'Từ truyện cổ tích',
                 'Từ giấc mơ cá nhân',
                 'Từ phim viễn tưởng'],
                0, 'Vì bàn về đời sống thực nên dẫn chứng phải lấy từ thực tế có thật, đáng tin cậy.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hiện tượng với tính chất của nó.',
                [['Hiến máu tình nguyện', 'Hiện tượng tích cực'],
                 ['Xếp hàng nơi công cộng', 'Hiện tượng tích cực'],
                 ['Bạo lực học đường', 'Hiện tượng tiêu cực'],
                 ['Xả rác bừa bãi', 'Hiện tượng tiêu cực']],
                'Xác định tính chất hiện tượng giúp chọn hướng đánh giá đúng.');
            $this->matching($L, 'Nối mỗi bước làm bài với việc cần làm.',
                [['Nhận diện', 'Mô tả hiện tượng là gì, biểu hiện ra sao'],
                 ['Đánh giá', 'Nêu mặt tốt – mặt xấu của hiện tượng'],
                 ['Nguyên nhân', 'Tìm hiểu vì sao có hiện tượng đó'],
                 ['Giải pháp', 'Đề xuất cách khắc phục, phát huy']],
                'Trình tự bốn bước khi nghị luận về một hiện tượng đời sống.');
            $this->matching($L, 'Nối mỗi hiện tượng với biểu hiện cụ thể.',
                [['Sống ảo', 'Suốt ngày đăng ảnh, "câu like" trên mạng'],
                 ['Vô cảm', 'Thờ ơ trước nỗi đau của người khác'],
                 ['Gian lận thi cử', 'Quay cóp, mang tài liệu vào phòng thi'],
                 ['Ô nhiễm tiếng ồn', 'Karaoke mở loa lớn suốt đêm']],
                'Nêu biểu hiện cụ thể giúp hiện tượng trở nên rõ ràng, sinh động.');
            $this->matching($L, 'Nối mỗi hiện tượng tích cực với ý nghĩa của nó.',
                [['Đọc sách', 'Mở mang tri thức, bồi đắp tâm hồn'],
                 ['Tập thể dục', 'Rèn luyện sức khỏe'],
                 ['Tình nguyện', 'Chia sẻ, gắn kết cộng đồng'],
                 ['Tiết kiệm', 'Trân trọng giá trị lao động']],
                'Với hiện tượng tích cực, cần nêu ý nghĩa và cách nhân rộng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm TÍCH CỰC hoặc TIÊU CỰC.',
                [['Hiến máu nhân đạo', 'Tích cực'], ['Giúp đỡ người già', 'Tích cực'], ['Đọc sách mỗi ngày', 'Tích cực'],
                 ['Bạo lực học đường', 'Tiêu cực'], ['Nghiện game', 'Tiêu cực'], ['Xả rác bừa bãi', 'Tiêu cực']],
                'Xác định tính chất hiện tượng là cơ sở để đánh giá đúng hướng.');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BIỂU HIỆN CỦA LỐI SỐNG ĐẸP hoặc KHÔNG PHẢI.',
                [['Nhặt rác nơi công cộng', 'Lối sống đẹp'],
                 ['Nhường ghế cho người già trên xe buýt', 'Lối sống đẹp'],
                 ['Tham gia dọn vệ sinh khu phố', 'Lối sống đẹp'],
                 ['Vứt rác qua cửa sổ xe', 'Không phải'],
                 ['Chen lấn khi xếp hàng', 'Không phải'],
                 ['Nói tục nơi công cộng', 'Không phải']],
                'Biểu hiện cụ thể giúp bài văn về hiện tượng sinh động, thuyết phục.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÁNH GIÁ KHÁCH QUAN hoặc PHIẾN DIỆN.',
                [['Nêu cả mặt tốt và mặt xấu của hiện tượng', 'Khách quan'],
                 ['Nhìn hiện tượng từ nhiều góc độ', 'Khách quan'],
                 ['Chỉ khen mà không thấy hạn chế', 'Phiến diện'],
                 ['Chỉ chê mà phủ nhận mặt tốt', 'Phiến diện'],
                 ['Đánh giá theo cảm tính', 'Phiến diện'],
                 ['Quy chụp mọi người đều xấu', 'Phiến diện']],
                'Đánh giá khách quan, toàn diện giúp bài văn sâu sắc và thuyết phục.');
            $this->sortQ($L, 'Kéo mỗi nguồn tin vào nhóm ĐÁNG TIN CẬY hoặc KHÔNG ĐÁNG TIN khi lấy dẫn chứng.',
                [['Báo chí chính thống', 'Đáng tin'],
                 ['Số liệu của cơ quan chức năng', 'Đáng tin'],
                 ['Sự việc có thật, kiểm chứng được', 'Đáng tin'],
                 ['Tin đồn trên mạng chưa kiểm chứng', 'Không đáng tin'],
                 ['Chuyện nghe kể lại mơ hồ', 'Không đáng tin'],
                 ['Ảnh chế, tin giả', 'Không đáng tin']],
                'Dẫn chứng về hiện tượng đời sống phải lấy từ nguồn đáng tin cậy.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dạng nghị luận bàn về các sự việc nổi bật trong đời sống gọi là nghị luận về ___ ___ ___ ___.', [[0, 'hiện tượng đời sống']],
                'Hiện tượng đời sống là những gì đang diễn ra quanh ta.');
            $this->fill($L, 'Bước đầu tiên khi làm bài là mô tả và ___ ___ hiện tượng.', [[0, 'nhận diện']],
                'Nhận diện đúng hiện tượng mới đánh giá đúng.');
            $this->fill($L, 'Khi đánh giá hiện tượng cần nhìn nhận ___ ___, thấy cả mặt tốt và mặt xấu.', [[0, 'khách quan']],
                'Đánh giá khách quan, toàn diện tránh phiến diện.');
            $this->fill($L, 'Dẫn chứng cho dạng bài này nên lấy từ ___ ___ có thật.', [[0, 'thực tế']],
                'Thực tế đời sống là nguồn dẫn chứng tốt nhất.');
        }
    }

    private function seedNv11HienTuong2(): void
    {
        $L = 'ngu-van-thpt-11-hien-tuong-doi-song-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nguyên nhân của hiện tượng tiêu cực thường gồm những nhóm nào?',
                ['Nguyên nhân chủ quan (con người) và nguyên nhân khách quan (hoàn cảnh)',
                 'Chỉ có nguyên nhân từ nhà trường',
                 'Chỉ có nguyên nhân từ gia đình',
                 'Không thể tìm được nguyên nhân'],
                0, 'Phân tích nguyên nhân cần thấy cả yếu tố chủ quan (ý thức con người) và khách quan (môi trường, quản lí).');
            $this->quiz($L, 'Giải pháp khắc phục hiện tượng tiêu cực cần đảm bảo gì?',
                ['Cụ thể, khả thi, phù hợp với từng đối tượng',
                 'Càng chung chung càng tốt',
                 'Chỉ hô khẩu hiệu',
                 'Đổ lỗi cho người khác'],
                0, 'Giải pháp phải cụ thể, làm được trong thực tế, hướng tới từng đối tượng liên quan.');
            $this->quiz($L, 'Với hiện tượng tích cực, bài văn nên tập trung vào điều gì?',
                ['Nêu ý nghĩa và cách nhân rộng hiện tượng',
                 'Tìm cách phê phán hiện tượng',
                 'Chứng minh hiện tượng không có thật',
                 'So sánh với hiện tượng tiêu cực'],
                0, 'Hiện tượng tích cực cần được ngợi ca ý nghĩa và lan tỏa trong cộng đồng.');
            $this->quiz($L, 'Liên hệ bản thân trong bài nghị luận về hiện tượng đời sống nhằm mục đích gì?',
                ['Rút ra bài học nhận thức và hành động cho chính mình',
                 'Khoe thành tích cá nhân',
                 'Kéo dài bài viết',
                 'Thay thế phần đánh giá'],
                0, 'Liên hệ bản thân thể hiện sự thấm thía vấn đề và trách nhiệm của người viết.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hiện tượng với nguyên nhân chủ yếu.',
                [['Nghiện game', 'Thiếu kiểm soát bản thân, cha mẹ bận rộn'],
                 ['Xả rác bừa bãi', 'Ý thức kém, thiếu thùng rác công cộng'],
                 ['Gian lận thi cử', 'Bệnh thành tích, lười học'],
                 ['Tai nạn giao thông', 'Phóng nhanh vượt ẩu, uống rượu bia']],
                'Tìm nguyên nhân giúp đề xuất giải pháp trúng đích.');
            $this->matching($L, 'Nối mỗi hiện tượng với giải pháp phù hợp.',
                [['Nghiện game', 'Quản lí thời gian, cha mẹ quan tâm hơn'],
                 ['Xả rác bừa bãi', 'Tuyên truyền, đặt thùng rác, xử phạt'],
                 ['Bạo lực học đường', 'Giáo dục kĩ năng sống, xử lí nghiêm'],
                 ['Ô nhiễm môi trường', 'Trồng cây, hạn chế rác thải nhựa']],
                'Giải pháp phải gắn với nguyên nhân và phù hợp từng đối tượng.');
            $this->matching($L, 'Nối mỗi đối tượng với trách nhiệm trong giải pháp.',
                [['Học sinh', 'Tự rèn ý thức, nói không với cái xấu'],
                 ['Gia đình', 'Quan tâm, giáo dục con em'],
                 ['Nhà trường', 'Giáo dục, tạo môi trường lành mạnh'],
                 ['Xã hội', 'Tuyên truyền, hoàn thiện pháp luật']],
                'Giải pháp toàn diện cần phân rõ trách nhiệm từng đối tượng.');
            $this->matching($L, 'Nối mỗi hiện tượng với bài học rút ra.',
                [['Dám thử thách', 'Dám vượt khó để trưởng thành'],
                 ['Lãng phí thức ăn', 'Trân trọng thành quả lao động'],
                 ['Vô lễ với thầy cô', 'Tôn sư trọng đạo'],
                 ['Lười đọc sách', 'Chăm đọc để mở mang tri thức']],
                'Mỗi hiện tượng đều gợi mở bài học cho bản thân người viết.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nguyên nhân vào nhóm CHỦ QUAN hoặc KHÁCH QUAN.',
                [['Ý thức kém của một bộ phận người dân', 'Chủ quan'],
                 ['Thiếu kĩ năng sống ở học sinh', 'Chủ quan'],
                 ['Chạy theo thành tích', 'Chủ quan'],
                 ['Thiếu thùng rác nơi công cộng', 'Khách quan'],
                 ['Chế tài xử phạt chưa nghiêm', 'Khách quan'],
                 ['Ảnh hưởng xấu từ mạng xã hội', 'Khách quan']],
                'Nguyên nhân chủ quan từ con người; khách quan từ hoàn cảnh, môi trường, quản lí.');
            $this->sortQ($L, 'Kéo mỗi giải pháp vào nhóm KHẢ THI hoặc CHUNG CHUNG.',
                [['Mỗi lớp đặt một thùng rác phân loại', 'Khả thi'],
                 ['Tổ chức buổi tuyên truyền mỗi tháng', 'Khả thi'],
                 ['Phụ huynh kiểm soát giờ chơi game của con', 'Khả thi'],
                 ['Kêu gọi mọi người hãy tốt lên', 'Chung chung'],
                 ['Mong xã hội hết cái xấu', 'Chung chung'],
                 ['Ai cũng phải có ý thức (không nói cách làm)', 'Chung chung']],
                'Giải pháp tốt phải cụ thể, chỉ rõ ai làm, làm gì, làm như thế nào.');
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm THUỘC PHẦN NGUYÊN NHÂN hoặc THUỘC PHẦN GIẢI PHÁP.',
                [['Vì sao hiện tượng này xuất hiện?', 'Nguyên nhân'],
                 ['Do ý thức con người còn hạn chế', 'Nguyên nhân'],
                 ['Do công tác quản lí còn lỏng lẻo', 'Nguyên nhân'],
                 ['Cần tuyên truyền nâng cao nhận thức', 'Giải pháp'],
                 ['Tăng cường kiểm tra, xử phạt', 'Giải pháp'],
                 ['Mỗi người tự rèn luyện bản thân', 'Giải pháp']],
                'Nguyên nhân lí giải "vì sao"; giải pháp trả lời "làm thế nào".');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm GÓP PHẦN KHẮC PHỤC hoặc LÀM TRẦM TRỌNG THÊM.',
                [['Tố giác hành vi gian lận', 'Khắc phục'],
                 ['Nhắc nhở bạn xả rác đúng nơi', 'Khắc phục'],
                 ['Tham gia đội tình nguyện', 'Khắc phục'],
                 ['Bao che cho bạn quay cóp', 'Trầm trọng thêm'],
                 ['Cổ vũ hành vi bạo lực', 'Trầm trọng thêm'],
                 ['Thờ ơ trước cái xấu', 'Trầm trọng thêm']],
                'Liên hệ bản thân: mỗi người đều có thể góp phần đẩy lùi hiện tượng tiêu cực.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nguyên nhân của hiện tượng gồm nguyên nhân ___ ___ và nguyên nhân khách quan.', [[0, 'chủ quan']],
                'Chủ quan từ con người, khách quan từ hoàn cảnh.');
            $this->fill($L, 'Giải pháp đề xuất phải cụ thể, ___ ___ và phù hợp từng đối tượng.', [[0, 'khả thi']],
                'Giải pháp khả thi là giải pháp làm được trong thực tế.');
            $this->fill($L, 'Với hiện tượng tích cực, cần nêu ý nghĩa và cách ___ ___ hiện tượng.', [[0, 'nhân rộng']],
                'Lan tỏa điều tốt đẹp là trách nhiệm của mỗi người.');
            $this->fill($L, 'Phần ___ ___ bản thân giúp bài văn thêm sâu sắc và chân thành.', [[0, 'liên hệ']],
                'Liên hệ bản thân: rút bài học cho chính mình.');
        }
    }

    // ================= NGỮ VĂN 12 =================

    private function seedNv12VanXuoi1(): void
    {
        $L = 'ngu-van-thpt-12-phan-tich-van-xuoi-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi phân tích nhân vật trong tác phẩm văn xuôi, cần dựa vào những yếu tố nào?',
                ['Ngoại hình, hành động, lời nói, diễn biến tâm lí của nhân vật',
                 'Chỉ dựa vào ngoại hình của nhân vật',
                 'Chỉ dựa vào lời kể của tác giả',
                 'Dựa vào số lần nhân vật xuất hiện'],
                0, 'Tính cách nhân vật bộc lộ qua bốn phương diện: ngoại hình, hành động, lời nói và tâm lí.', 'kho');
            $this->quiz($L, 'Tình huống truyện trong tác phẩm văn xuôi có ý nghĩa gì khi phân tích?',
                ['Là hoàn cảnh đặc biệt bộc lộ tính cách nhân vật và chủ đề tác phẩm',
                 'Chỉ là phần mở đầu của truyện',
                 'Không có ý nghĩa gì đặc biệt',
                 'Chỉ để kéo dài câu chuyện'],
                0, 'Phân tích tình huống truyện giúp thấy "cái thế" mà nhân vật bị đặt vào, từ đó tính cách và tư tưởng bộc lộ rõ nhất.', 'kho');
            $this->quiz($L, 'Diễn biến tâm lí nhân vật thường được khắc họa trong hoàn cảnh nào?',
                ['Trong tình huống có xung đột, biến cố, thử thách',
                 'Khi nhân vật đang ngủ',
                 'Khi không có sự kiện gì xảy ra',
                 'Chỉ ở phần kết thúc truyện'],
                0, 'Tâm lí nhân vật bộc lộ rõ nhất khi đối mặt với xung đột, biến cố hoặc lựa chọn khó khăn.', 'kho');
            $this->quiz($L, 'Ngôn ngữ nhân vật (lời thoại, độc thoại) giúp người đọc hiểu gì?',
                ['Tính cách, quan niệm và tâm trạng của nhân vật',
                 'Tiểu sử của tác giả',
                 'Hoàn cảnh sáng tác',
                 'Số lượng nhân vật trong truyện'],
                0, 'Lời nói là "cửa sổ" tâm hồn: qua đối thoại và độc thoại, tính cách nhân vật hiện lên chân thực.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phương diện với cách phân tích nhân vật.',
                [['Ngoại hình', 'Miêu tả dáng vẻ, phục sức gợi tính cách'],
                 ['Hành động', 'Việc làm bộc lộ bản chất con người'],
                 ['Lời nói', 'Đối thoại, độc thoại thể hiện quan niệm'],
                 ['Tâm lí', 'Diễn biến nội tâm trước biến cố']],
                'Bốn phương diện khắc họa nhân vật trong tác phẩm văn xuôi.', 'kho');
            $this->matching($L, 'Nối mỗi loại tình huống với tác dụng.',
                [['Tình huống hành động', 'Thử thách bản lĩnh qua việc làm'],
                 ['Tình huống tâm lí', 'Bộc lộ diễn biến nội tâm'],
                 ['Tình huống nhận thức', 'Nhân vật vỡ lẽ ra điều gì đó'],
                 ['Tình huống éo le', 'Đặt nhân vật vào lựa chọn khó khăn']],
                'Mỗi loại tình huống mở ra một chiều sâu khác nhau của nhân vật.', 'kho');
            $this->matching($L, 'Nối mỗi nhận xét với phương diện phân tích.',
                [['Nhân vật có dáng vẻ lam lũ', 'Ngoại hình'],
                 ['Nhân vật dám đứng ra bảo vệ lẽ phải', 'Hành động'],
                 ['Lời nói chân thành, mộc mạc', 'Lời nói'],
                 ['Nỗi day dứt không nguôi', 'Tâm lí']],
                'Khi viết bài, mỗi luận điểm về nhân vật cần gắn với dẫn chứng thuộc phương diện tương ứng.', 'kho');
            $this->matching($L, 'Nối mỗi bước phân tích với việc cần làm.',
                [['Giới thiệu', 'Nêu tác giả, tác phẩm, nhân vật'],
                 ['Phân tích', 'Triển khai các luận điểm về nhân vật'],
                 ['Đánh giá', 'Nhận xét nghệ thuật xây dựng nhân vật'],
                 ['Kết luận', 'Khái quát ý nghĩa nhân vật']],
                'Dàn ý phân tích nhân vật: giới thiệu → phân tích → đánh giá → kết luận.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm KHẮC HỌA NGOẠI HÌNH hoặc KHẮC HỌA TÂM LÍ.',
                [['Khuôn mặt khắc khổ, hằn nếp nhăn', 'Ngoại hình'],
                 ['Bàn tay chai sần, nứt nẻ', 'Ngoại hình'],
                 ['Chiếc áo vá nhiều miếng', 'Ngoại hình'],
                 ['Nỗi ân hận giằng xé trong lòng', 'Tâm lí'],
                 ['Niềm hi vọng le lói', 'Tâm lí'],
                 ['Sự giằng co giữa tình và lí', 'Tâm lí']],
                'Ngoại hình gợi hoàn cảnh, số phận; tâm lí gợi chiều sâu nội tâm.', 'kho');
            $this->sortQ($L, 'Kéo mỗi biểu hiện vào nhóm TÍNH CÁCH TỐT hoặc TÍNH CÁCH XẤU của nhân vật.',
                [['Dũng cảm đấu tranh cho lẽ phải', 'Tốt'],
                 ['Hi sinh vì người khác', 'Tốt'],
                 ['Kiên cường vượt qua nghịch cảnh', 'Tốt'],
                 ['Tham lam, ích kỉ', 'Xấu'],
                 ['Hèn nhát, nhu nhược', 'Xấu'],
                 ['Giả dối, lừa lọc', 'Xấu']],
                'Đánh giá tính cách nhân vật phải dựa trên hành động, việc làm cụ thể.', 'kho');
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm LUẬN ĐIỂM PHÂN TÍCH hoặc DẪN CHỨNG.',
                [['Nhân vật hiện lên với vẻ đẹp kiên cường', 'Luận điểm'],
                 ['Tình huống truyện độc đáo, giàu kịch tính', 'Luận điểm'],
                 ['Chi tiết nhân vật cắn răng chịu đựng', 'Dẫn chứng'],
                 ['Lời thoại khẳng định quyết tâm', 'Dẫn chứng'],
                 ['Diễn biến tâm lí được miêu tả tinh tế', 'Luận điểm'],
                 ['Đoạn miêu tả nội tâm trước biến cố', 'Dẫn chứng']],
                'Luận điểm là nhận định; dẫn chứng là chi tiết trong tác phẩm làm sáng tỏ nhận định.', 'kho');
            $this->sortQ($L, 'Kéo mỗi vai trò vào nhóm NHÂN VẬT CHÍNH DIỆN hoặc PHẢN DIỆN (trong xung đột).',
                [['Người bảo vệ lẽ phải', 'Chính diện'],
                 ['Người hi sinh vì cộng đồng', 'Chính diện'],
                 ['Người dám nhận lỗi, sửa sai', 'Chính diện'],
                 ['Kẻ chà đạp lên người khác', 'Phản diện'],
                 ['Kẻ mưu mô, thủ đoạn', 'Phản diện'],
                 ['Kẻ hèn nhát, phản bội', 'Phản diện']],
                'Xung đột giữa chính diện và phản diện tạo kịch tính cho tác phẩm.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tính cách nhân vật bộc lộ qua ngoại hình, hành động, lời nói và diễn biến ___ ___.', [[0, 'tâm lí']],
                'Tâm lí là chiều sâu nội tâm, bộc lộ rõ nhất trước biến cố.', 'kho');
            $this->fill($L, 'Hoàn cảnh đặc biệt bộc lộ tính cách nhân vật và chủ đề tác phẩm gọi là ___ ___ ___.', [[0, 'tình huống truyện']],
                'Tình huống truyện là "cái lò" thử thách nhân vật.', 'kho');
            $this->fill($L, 'Lời nói thầm trong tâm trí nhân vật, không phát ra thành tiếng gọi là ___ ___ ___ ___.', [[0, 'độc thoại nội tâm']],
                'Độc thoại nội tâm giúp người đọc thâm nhập thế giới bên trong nhân vật.', 'kho');
            $this->fill($L, 'Khi phân tích nhân vật, mỗi nhận định cần có ___ ___ từ tác phẩm làm sáng tỏ.', [[0, 'dẫn chứng']],
                'Dẫn chứng là chi tiết, câu văn trong tác phẩm.', 'kho');
        }
    }

    private function seedNv12VanXuoi2(): void
    {
        $L = 'ngu-van-thpt-12-phan-tich-van-xuoi-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Giá trị hiện thực của tác phẩm văn học là gì?',
                ['Khả năng phản ánh chân thực đời sống xã hội, con người thời đại',
                 'Số lượng bản in của tác phẩm',
                 'Độ dài của tác phẩm',
                 'Số nhân vật xuất hiện'],
                0, 'Giá trị hiện thực: tác phẩm như "tấm gương" phản ánh đời sống, xã hội một thời.', 'kho');
            $this->quiz($L, 'Giá trị nhân đạo của tác phẩm thể hiện qua điều gì?',
                ['Sự đồng cảm, ngợi ca cái tốt, phê phán cái xấu, đề cao con người',
                 'Việc miêu tả thiên nhiên đẹp',
                 'Số lượng chi tiết nghệ thuật',
                 'Độ dài của các đoạn miêu tả'],
                0, 'Giá trị nhân đạo là tình cảm nhân văn: yêu thương, đồng cảm, đấu tranh cho con người.', 'kho');
            $this->quiz($L, 'Khi đánh giá giá trị nghệ thuật của truyện, cần chú ý những yếu tố nào?',
                ['Tình huống truyện, kết cấu, ngôn ngữ, giọng điệu, chi tiết nghệ thuật',
                 'Chỉ chú ý đến độ dài tác phẩm',
                 'Chỉ đếm số nhân vật',
                 'Chỉ xem năm sáng tác'],
                0, 'Giá trị nghệ thuật nằm ở cách xây dựng tình huống, kết cấu, ngôn ngữ, giọng điệu...', 'kho');
            $this->quiz($L, 'Mối quan hệ giữa giá trị nội dung và giá trị nghệ thuật là gì?',
                ['Nội dung quyết định, nghệ thuật là phương tiện thể hiện; hai mặt thống nhất',
                 'Hoàn toàn tách rời nhau',
                 'Chỉ cần một trong hai',
                 'Nghệ thuật quan trọng hơn nội dung'],
                0, 'Nội dung và nghệ thuật thống nhất: tư tưởng sâu sắc cần hình thức nghệ thuật đặc sắc để thể hiện.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi giá trị với biểu hiện của nó.',
                [['Giá trị hiện thực', 'Phản ánh đời sống, xã hội chân thực'],
                 ['Giá trị nhân đạo', 'Đồng cảm, ngợi ca, đấu tranh cho con người'],
                 ['Giá trị thẩm mĩ', 'Cái đẹp trong ngôn ngữ, hình tượng'],
                 ['Giá trị nhận thức', 'Giúp hiểu sâu về đời sống, con người']],
                'Bốn giá trị cơ bản của tác phẩm văn học.', 'kho');
            $this->matching($L, 'Nối mỗi yếu tố nghệ thuật với tác dụng.',
                [['Tình huống truyện', 'Tạo kịch tính, bộc lộ tính cách'],
                 ['Kết cấu', 'Sắp xếp sự kiện hợp lí, hấp dẫn'],
                 ['Ngôn ngữ', 'Gợi hình, gợi cảm, đậm cá tính'],
                 ['Giọng điệu', 'Tạo sắc thái tình cảm bao trùm']],
                'Đánh giá nghệ thuật cần chỉ ra tác dụng cụ thể của từng yếu tố.', 'kho');
            $this->matching($L, 'Nối mỗi biểu hiện với giá trị tương ứng.',
                [['Phản ánh cảnh đói nghèo', 'Hiện thực'],
                 ['Ca ngợi lòng dũng cảm', 'Nhân đạo'],
                 ['Lên án thói ích kỉ', 'Nhân đạo'],
                 ['Vẽ nên bức tranh quê hương', 'Hiện thực']],
                'Một chi tiết có thể mang nhiều giá trị; cần xác định giá trị nổi bật.', 'kho');
            $this->matching($L, 'Nối mỗi phần của bài nghị luận văn học với nội dung.',
                [['Mở bài', 'Giới thiệu tác giả, tác phẩm, vấn đề'],
                 ['Thân bài', 'Phân tích, chứng minh, đánh giá'],
                 ['Kết bài', 'Khái quát giá trị, nêu cảm nhận'],
                 ['Dẫn chứng', 'Chi tiết trong tác phẩm']],
                'Bố cục bài nghị luận văn học: mở – thân – kết, dẫn chứng lấy từ tác phẩm.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm GIÁ TRỊ NỘI DUNG hoặc GIÁ TRỊ NGHỆ THUẬT.',
                [['Phản ánh chân thực xã hội', 'Nội dung'],
                 ['Thể hiện tình yêu thương con người', 'Nội dung'],
                 ['Lên án cái xấu, cái ác', 'Nội dung'],
                 ['Tình huống truyện độc đáo', 'Nghệ thuật'],
                 ['Ngôn ngữ giàu hình ảnh', 'Nghệ thuật'],
                 ['Kết cấu chặt chẽ, hấp dẫn', 'Nghệ thuật']],
                'Nội dung là tư tưởng, tình cảm; nghệ thuật là cách thể hiện.', 'kho');
            $this->sortQ($L, 'Kéo mỗi biểu hiện vào nhóm GIÁ TRỊ NHÂN ĐẠO hoặc KHÔNG PHẢI.',
                [['Đồng cảm với số phận bất hạnh', 'Nhân đạo'],
                 ['Ngợi ca vẻ đẹp tâm hồn', 'Nhân đạo'],
                 ['Đấu tranh cho lẽ phải', 'Nhân đạo'],
                 ['Cổ vũ thói ích kỉ', 'Không phải'],
                 ['Chế giễu nỗi đau người khác', 'Không phải'],
                 ['Vô cảm trước bất công', 'Không phải']],
                'Giá trị nhân đạo là thước đo tình người trong tác phẩm.', 'kho');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm NGHỆ THUẬT XÂY DỰNG TRUYỆN hoặc NGHỆ THUẬT NGÔN TỪ.',
                [['Tình huống truyện', 'Xây dựng truyện'],
                 ['Kết cấu', 'Xây dựng truyện'],
                 ['Xây dựng nhân vật', 'Xây dựng truyện'],
                 ['Từ ngữ gợi hình', 'Ngôn từ'],
                 ['Biện pháp tu từ', 'Ngôn từ'],
                 ['Giọng điệu', 'Ngôn từ']],
                'Nghệ thuật truyện gồm nghệ thuật xây dựng (tình huống, kết cấu, nhân vật) và nghệ thuật ngôn từ.', 'kho');
            $this->sortQ($L, 'Kéo mỗi câu văn vào nhóm LỜI NGƯỜI KỂ hoặc LỜI NHÂN VẬT.',
                [['Cánh đồng trải dài tít tắp.', 'Người kể'],
                 ['Nắng chiều nhuộm vàng mái rạ.', 'Người kể'],
                 ['— Mẹ ơi, con đã về!', 'Nhân vật'],
                 ['— Con nhớ mẹ nhiều lắm.', 'Nhân vật'],
                 ['Gió thổi rì rào qua rặng tre.', 'Người kể'],
                 ['— Ngày mai con lại đi học.', 'Nhân vật']],
                'Lời nhân vật thường có dấu gạch ngang đầu dòng (đối thoại); lời người kể là miêu tả, trần thuật.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Khả năng phản ánh chân thực đời sống xã hội của tác phẩm gọi là giá trị ___ ___.', [[0, 'hiện thực']],
                'Giá trị hiện thực: tác phẩm phản ánh đời sống như tấm gương.', 'kho');
            $this->fill($L, 'Tình cảm nhân văn: đồng cảm, ngợi ca cái tốt, đấu tranh cho con người gọi là giá trị ___ ___.', [[0, 'nhân đạo']],
                'Giá trị nhân đạo là linh hồn của tác phẩm chân chính.', 'kho');
            $this->fill($L, 'Nội dung và nghệ thuật có mối quan hệ ___ ___ với nhau.', [[0, 'thống nhất']],
                'Tư tưởng sâu sắc cần hình thức nghệ thuật đặc sắc.', 'kho');
            $this->fill($L, 'Khi đánh giá nghệ thuật truyện cần chú ý tình huống, kết cấu, ngôn ngữ và ___ ___.', [[0, 'giọng điệu']],
                'Giọng điệu tạo sắc thái tình cảm bao trùm tác phẩm.', 'kho');
        }
    }

    private function seedNv12Tho1(): void
    {
        $L = 'ngu-van-thpt-12-phan-tich-tho-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tứ thơ là gì?',
                ['Ý tưởng trung tâm, làm nên mạch xuyên suốt của bài thơ',
                 'Nhan đề của bài thơ',
                 'Khổ thơ đầu tiên',
                 'Tên của tác giả'],
                0, 'Tứ thơ là "xương sống" ý tưởng, gắn kết các hình ảnh, cảm xúc trong bài.', 'kho');
            $this->quiz($L, 'Hình tượng thơ được tạo nên từ đâu?',
                ['Từ ngôn ngữ giàu sức gợi và các biện pháp tu từ',
                 'Từ ảnh minh họa kèm theo',
                 'Từ số lượng khổ thơ',
                 'Từ năm sáng tác'],
                0, 'Hình tượng thơ là hình ảnh nghệ thuật được tạo bằng ngôn từ, nhạc điệu và biện pháp tu từ.', 'kho');
            $this->quiz($L, 'Khi phân tích một khổ thơ, cần chú ý điều gì?',
                ['Từ ngữ, hình ảnh, biện pháp tu từ và cảm xúc của khổ thơ',
                 'Chỉ đếm số chữ trong khổ',
                 'Chỉ xem vị trí của khổ thơ',
                 'Chỉ đọc lướt qua'],
                0, 'Phân tích khổ thơ: đi từ từ ngữ, hình ảnh đặc sắc đến biện pháp tu từ và cảm xúc chủ đạo.', 'kho');
            $this->quiz($L, 'Mạch cảm xúc trong bài thơ thường diễn biến như thế nào?',
                ['Vận động, phát triển từ đầu đến cuối bài theo một logic',
                 'Đứng yên không thay đổi',
                 'Ngẫu nhiên, không theo quy luật',
                 'Chỉ có ở khổ cuối'],
                0, 'Cảm xúc trữ tình vận động thành mạch: có thể từ nhớ đến thương, từ buồn đến hi vọng...', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi khái niệm với nội dung khi phân tích thơ.',
                [['Tứ thơ', 'Ý tưởng trung tâm của bài thơ'],
                 ['Hình tượng thơ', 'Hình ảnh nghệ thuật giàu sức gợi'],
                 ['Mạch cảm xúc', 'Sự vận động của cảm xúc'],
                 ['Chủ thể trữ tình', 'Người bộc lộ cảm xúc']],
                'Bốn khái niệm nền tảng khi phân tích một bài thơ.', 'kho');
            $this->matching($L, 'Nối mỗi biện pháp tu từ với tác dụng trong thơ.',
                [['So sánh', 'Làm hình ảnh cụ thể, sinh động'],
                 ['Nhân hóa', 'Vật vô tri có hồn như con người'],
                 ['Ẩn dụ', 'Gợi liên tưởng sâu sắc, hàm súc'],
                 ['Điệp ngữ', 'Nhấn mạnh, tạo nhịp điệu']],
                'Biện pháp tu từ là "gia vị" nghệ thuật quan trọng của thơ.', 'kho');
            $this->matching($L, 'Nối mỗi hình ảnh thiên nhiên với cảm xúc thường gợi.',
                [['Trăng thu', 'Nỗi nhớ, sự sum họp'],
                 ['Mưa ngâu', 'Nỗi buồn, sự chia li'],
                 ['Nắng mới', 'Niềm vui, sức sống'],
                 ['Sóng biển', 'Khát vọng, dạt dào']],
                'Thiên nhiên trong thơ thường mang tâm trạng của con người (tả cảnh ngụ tình).', 'kho');
            $this->matching($L, 'Nối mỗi bước phân tích với việc cần làm.',
                [['Xác định tứ thơ', 'Tìm ý tưởng trung tâm'],
                 ['Phân tích hình tượng', 'Đi sâu từ ngữ, biện pháp tu từ'],
                 ['Theo dõi cảm xúc', 'Nắm mạch vận động tình cảm'],
                 ['Đánh giá', 'Nhận xét giá trị tư tưởng, nghệ thuật']],
                'Trình tự phân tích thơ: tứ thơ → hình tượng → cảm xúc → đánh giá.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm NỘI DUNG hoặc NGHỆ THUẬT của bài thơ.',
                [['Tứ thơ', 'Nội dung'], ['Cảm xúc trữ tình', 'Nội dung'], ['Tư tưởng', 'Nội dung'],
                 ['Thể thơ, vần, nhịp', 'Nghệ thuật'], ['Từ ngữ, hình ảnh', 'Nghệ thuật'], ['Biện pháp tu từ', 'Nghệ thuật']],
                'Phân tích thơ cần song hành nội dung và nghệ thuật.', 'kho');
            $this->sortQ($L, 'Kéo mỗi hình ảnh vào nhóm HÌNH ẢNH THỰC hoặc HÌNH ẢNH TƯỢNG TRƯNG.',
                [['Cánh đồng lúa chín', 'Thực'],
                 ['Con đò trên sông', 'Thực'],
                 ['Ngọn đèn trong đêm', 'Tượng trưng'],
                 ['Mùa xuân', 'Tượng trưng'],
                 ['Dòng sông quê hương', 'Thực'],
                 ['Cánh chim bay về tổ', 'Tượng trưng']],
                'Hình ảnh tượng trưng mang nghĩa bóng sâu xa vượt lên hình ảnh cụ thể.', 'kho');
            $this->sortQ($L, 'Kéo mỗi từ ngữ vào nhóm GỢI HÌNH ẢNH hoặc GỢI CẢM XÚC.',
                [['Lấp lánh', 'Hình ảnh'], ['Mênh mông', 'Hình ảnh'], ['Rực rỡ', 'Hình ảnh'],
                 ['Bâng khuâng', 'Cảm xúc'], ['Xao xuyến', 'Cảm xúc'], ['Ngậm ngùi', 'Cảm xúc']],
                'Từ ngữ trong thơ vừa gợi hình vừa gợi cảm, tạo sức ám ảnh.', 'kho');
            $this->sortQ($L, 'Kéo mỗi cặp câu vào nhóm CÓ VẦN hoặc KHÔNG VẦN.',
                [['Trăng lên – trăng tàn (vần "ăng")', 'Có vần'],
                 ['Sông sâu – sông dài (vần "ông")', 'Có vần'],
                 ['Hoa nở – chim hót (không cùng vần)', 'Không vần'],
                 ['Nắng vàng – mưa rơi (không cùng vần)', 'Không vần'],
                 ['Đò đưa – đò đầy (vần "o")', 'Có vần'],
                 ['Biển xanh – trời cao (không cùng vần)', 'Không vần']],
                'Vần là sự lặp lại phần vần của tiếng ở vị trí gieo vần.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ý tưởng trung tâm, làm nên mạch xuyên suốt của bài thơ gọi là ___ ___.', [[0, 'tứ thơ']],
                'Tứ thơ gắn kết các hình ảnh, cảm xúc thành chỉnh thể.', 'kho');
            $this->fill($L, 'Sự vận động, phát triển của cảm xúc từ đầu đến cuối bài thơ gọi là ___ ___ ___.', [[0, 'mạch cảm xúc']],
                'Theo dõi mạch cảm xúc giúp nắm cấu tứ của bài thơ.', 'kho');
            $this->fill($L, 'Hình ảnh mang nghĩa bóng sâu xa, vượt lên hình ảnh cụ thể gọi là hình ảnh ___ ___.', [[0, 'tượng trưng']],
                'Ví dụ: ngọn đèn tượng trưng cho niềm tin, hi vọng.', 'kho');
            $this->fill($L, 'Khi phân tích khổ thơ cần chú ý từ ngữ, hình ảnh, biện pháp tu từ và ___ ___.', [[0, 'cảm xúc']],
                'Cảm xúc là linh hồn của mỗi khổ thơ.', 'kho');
        }
    }

    private function seedNv12Tho2(): void
    {
        $L = 'ngu-van-thpt-12-phan-tich-tho-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Giá trị tư tưởng của bài thơ thể hiện qua điều gì?',
                ['Tình cảm, quan niệm, triết lí mà bài thơ gửi gắm',
                 'Số lượng khổ thơ trong bài',
                 'Năm sáng tác bài thơ',
                 'Độ dài của nhan đề'],
                0, 'Giá trị tư tưởng là chiều sâu nhận thức, tình cảm nhân văn của bài thơ.', 'kho');
            $this->quiz($L, 'Đặc sắc nghệ thuật của thơ thường nằm ở những yếu tố nào?',
                ['Thể thơ, vần nhịp, từ ngữ, hình ảnh, biện pháp tu từ, giọng điệu',
                 'Chỉ nằm ở nhan đề',
                 'Chỉ nằm ở số câu',
                 'Chỉ nằm ở tên tác giả'],
                0, 'Nghệ thuật thơ là sự kết hợp của nhạc điệu, ngôn từ, hình ảnh và giọng điệu.', 'kho');
            $this->quiz($L, 'Kết bài của bài văn phân tích thơ nên làm gì?',
                ['Khái quát giá trị bài thơ và nêu cảm nhận của người viết',
                 'Kể lại nội dung bài thơ',
                 'Phê phán tác giả',
                 'Viết tiếp một khổ thơ mới'],
                0, 'Kết bài khái quát lại giá trị tư tưởng, nghệ thuật và để lại cảm nhận chân thành.', 'kho');
            $this->quiz($L, 'Vì sao khi phân tích thơ cần kết hợp nội dung và nghệ thuật?',
                ['Vì tư tưởng sâu sắc cần hình thức nghệ thuật đặc sắc để thể hiện',
                 'Vì quy định bắt buộc của đề bài',
                 'Vì để bài viết dài hơn',
                 'Vì hai mặt hoàn toàn tách rời'],
                0, 'Nội dung và nghệ thuật thống nhất: phân tích tách rời sẽ phiến diện.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi giá trị với biểu hiện trong thơ.',
                [['Giá trị tư tưởng', 'Tình cảm, triết lí sâu sắc'],
                 ['Giá trị thẩm mĩ', 'Vẻ đẹp ngôn từ, hình ảnh'],
                 ['Giá trị nhân văn', 'Tình yêu con người, cuộc sống'],
                 ['Giá trị nhận thức', 'Hiểu thêm về đời sống, tâm hồn']],
                'Bốn giá trị cần đánh giá khi kết luận về một bài thơ.', 'kho');
            $this->matching($L, 'Nối mỗi yếu tố nghệ thuật với đặc sắc.',
                [['Thể thơ', 'Phù hợp với cảm xúc, nội dung'],
                 ['Nhạc điệu', 'Vần, nhịp tạo âm hưởng'],
                 ['Từ ngữ', 'Giàu sức gợi, chính xác'],
                 ['Giọng điệu', 'Sắc thái tình cảm riêng']],
                'Mỗi yếu tố nghệ thuật góp phần tạo nên phong cách riêng của bài thơ.', 'kho');
            $this->matching($L, 'Nối mỗi giọng điệu thơ với sắc thái.',
                [['Hào hùng', 'Khí thế mạnh mẽ'],
                 ['Trầm lắng', 'Suy tư sâu xa'],
                 ['Dí dỏm', 'Hài hước, hóm hỉnh'],
                 ['Thiết tha', 'Dạt dào tình cảm']],
                'Giọng điệu là "vân tay" cảm xúc của nhà thơ.', 'kho');
            $this->matching($L, 'Nối mỗi phần của bài văn với nội dung.',
                [['Mở bài', 'Giới thiệu tác giả, bài thơ'],
                 ['Thân bài', 'Phân tích hình tượng, cảm xúc'],
                 ['Đánh giá', 'Nhận xét giá trị tư tưởng, nghệ thuật'],
                 ['Kết bài', 'Khái quát, nêu cảm nhận']],
                'Bố cục bài phân tích thơ: mở – thân (phân tích + đánh giá) – kết.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÁNH GIÁ NỘI DUNG hoặc ĐÁNH GIÁ NGHỆ THUẬT.',
                [['Bài thơ chan chứa tình yêu quê hương', 'Nội dung'],
                 ['Thể hiện triết lí sâu sắc về cuộc đời', 'Nội dung'],
                 ['Ngợi ca vẻ đẹp tâm hồn con người', 'Nội dung'],
                 ['Thể thơ tự do phóng khoáng', 'Nghệ thuật'],
                 ['Hình ảnh thơ giàu sức gợi', 'Nghệ thuật'],
                 ['Giọng thơ thiết tha, trầm lắng', 'Nghệ thuật']],
                'Đánh giá toàn diện cần cả hai mặt nội dung và nghệ thuật.', 'kho');
            $this->sortQ($L, 'Kéo mỗi từ ngữ vào nhóm NGÔN NGỮ BÌNH DỊ hoặc NGÔN NGỮ TRAU CHUỐT.',
                [['Mẹ, quê, sông, nắng', 'Bình dị'],
                 ['Ruộng đồng, bến nước', 'Bình dị'],
                 ['Ngọc ngà, châu báu (ước lệ)', 'Trau chuốt'],
                 ['Trâm anh, đài các (ước lệ)', 'Trau chuốt'],
                 ['Cơm, áo, mồ hôi', 'Bình dị'],
                 ['Vàng son, lầu hồng (ước lệ)', 'Trau chuốt']],
                'Ngôn ngữ thơ có thể bình dị, gần gũi hoặc trau chuốt, ước lệ tùy phong cách.', 'kho');
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm THUỘC MỞ BÀI hoặc THUỘC KẾT BÀI.',
                [['Giới thiệu tác giả, hoàn cảnh sáng tác', 'Mở bài'],
                 ['Nêu bài thơ và vấn đề phân tích', 'Mở bài'],
                 ['Khái quát giá trị bài thơ', 'Kết bài'],
                 ['Nêu cảm nhận, ấn tượng của bản thân', 'Kết bài'],
                 ['Dẫn dắt cảm xúc vào đề', 'Mở bài'],
                 ['Mở ra suy ngẫm cho người đọc', 'Kết bài']],
                'Mở bài giới thiệu; kết bài khái quát và để lại dư âm.', 'kho');
            $this->sortQ($L, 'Kéo mỗi biểu hiện vào nhóm THƠ TRỮ TÌNH hoặc KHÔNG PHẢI TRỮ TÌNH.',
                [['Bộc lộ cảm xúc, tâm trạng', 'Trữ tình'],
                 ['Có chủ thể trữ tình "tôi"', 'Trữ tình'],
                 ['Ngôn ngữ giàu nhạc điệu, cảm xúc', 'Trữ tình'],
                 ['Kể chuỗi sự kiện dài', 'Không phải'],
                 ['Xây dựng cốt truyện phức tạp', 'Không phải'],
                 ['Nhiều nhân vật, xung đột', 'Không phải']],
                'Thơ trữ tình lấy cảm xúc làm trung tâm, khác tự sự lấy sự kiện làm trung tâm.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tình cảm, quan niệm, triết lí mà bài thơ gửi gắm tạo nên giá trị ___ ___ ___.', [[0, 'tư tưởng']],
                'Giá trị tư tưởng là chiều sâu nhận thức của bài thơ.', 'kho');
            $this->fill($L, 'Sự kết hợp của nhạc điệu, ngôn từ, hình ảnh tạo nên đặc sắc ___ ___ ___ của bài thơ.', [[0, 'nghệ thuật']],
                'Nghệ thuật thơ: thể thơ, vần nhịp, từ ngữ, hình ảnh, giọng điệu.', 'kho');
            $this->fill($L, 'Phần cuối bài văn phân tích thơ cần khái quát giá trị và nêu ___ ___ của người viết.', [[0, 'cảm nhận']],
                'Cảm nhận chân thành để lại dư âm cho bài viết.', 'kho');
            $this->fill($L, 'Nội dung và nghệ thuật trong tác phẩm có mối quan hệ ___ ___ biện chứng.', [[0, 'thống nhất']],
                'Phân tích cần kết hợp cả hai mặt.', 'kho');
        }
    }

    // ================= TIẾNG VIỆT 10 =================

    private function seedTv10VanChuong1(): void
    {
        $L = 'tieng-viet-thpt-10-pcnc-van-chuong-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phong cách ngôn ngữ văn chương có mấy đặc trưng cơ bản?',
                ['Ba đặc trưng: tính hình tượng, tính truyền cảm, tính cá thể hóa',
                 'Một đặc trưng duy nhất',
                 'Hai đặc trưng',
                 'Bốn đặc trưng'],
                0, 'Ba đặc trưng của phong cách ngôn ngữ văn chương: hình tượng, truyền cảm, cá thể hóa.');
            $this->quiz($L, 'Tính hình tượng của ngôn ngữ văn chương là gì?',
                ['Gợi lên hình ảnh cụ thể, sinh động trong tâm trí người đọc',
                 'Dùng nhiều con số, số liệu',
                 'Dùng câu ngắn, khô khan',
                 'Tránh mọi hình ảnh so sánh'],
                0, 'Tính hình tượng: ngôn ngữ gợi hình ảnh cụ thể thay vì khái niệm trừu tượng.');
            $this->quiz($L, 'Tính truyền cảm của ngôn ngữ văn chương thể hiện qua đâu?',
                ['Qua khả năng gợi cảm xúc, lay động lòng người đọc',
                 'Qua việc liệt kê số liệu',
                 'Qua câu văn khô khan, lạnh lùng',
                 'Qua việc tránh bộc lộ cảm xúc'],
                0, 'Ngôn ngữ văn chương không chỉ truyền đạt thông tin mà còn truyền cảm xúc.');
            $this->quiz($L, 'Tính cá thể hóa trong phong cách ngôn ngữ văn chương là gì?',
                ['Dấu ấn riêng, phong cách riêng của từng tác giả',
                 'Mọi tác giả viết giống nhau',
                 'Không có phong cách riêng',
                 'Bắt chước người khác'],
                0, 'Mỗi nhà văn có "giọng" riêng: cách dùng từ, đặt câu, tạo hình ảnh mang dấu ấn cá nhân.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đặc trưng với biểu hiện của nó.',
                [['Tính hình tượng', 'Gợi hình ảnh cụ thể, sinh động'],
                 ['Tính truyền cảm', 'Gợi cảm xúc, lay động lòng người'],
                 ['Tính cá thể hóa', 'Dấu ấn phong cách riêng của tác giả'],
                 ['Ngôn ngữ văn chương', 'Ngôn ngữ toàn dân được chọn lọc, tinh luyện']],
                'Ba đặc trưng tạo nên sức hấp dẫn của ngôn ngữ văn chương.');
            $this->matching($L, 'Nối mỗi ví dụ với đặc trưng thể hiện.',
                [['"Mặt trời xuống biển như hòn lửa"', 'Tính hình tượng'],
                 ['Câu văn khiến người đọc rưng rưng', 'Tính truyền cảm'],
                 ['Giọng văn hóm hỉnh riêng của tác giả', 'Tính cá thể hóa'],
                 ['"Cánh đồng vàng rực"', 'Tính hình tượng']],
                'Nhận diện đặc trưng qua ví dụ cụ thể.');
            $this->matching($L, 'Nối mỗi phong cách với chức năng chính.',
                [['Văn chương', 'Thẩm mĩ, biểu cảm'],
                 ['Báo chí', 'Thông tin thời sự'],
                 ['Khoa học', 'Nhận thức, lí luận'],
                 ['Hành chính', 'Giao tiếp công vụ']],
                'Mỗi phong cách ngôn ngữ phục vụ một chức năng xã hội khác nhau.');
            $this->matching($L, 'Nối mỗi thể loại với phong cách ngôn ngữ.',
                [['Truyện ngắn, thơ', 'Văn chương'],
                 ['Bản tin, phóng sự', 'Báo chí'],
                 ['Luận văn, giáo trình', 'Khoa học'],
                 ['Đơn từ, công văn', 'Hành chính']],
                'Thể loại văn bản gắn liền với phong cách ngôn ngữ tương ứng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm VĂN CHƯƠNG hoặc BÁO CHÍ.',
                [['Giàu hình ảnh, cảm xúc', 'Văn chương'],
                 ['Dấu ấn cá nhân đậm nét', 'Văn chương'],
                 ['Nhiều biện pháp tu từ', 'Văn chương'],
                 ['Ngắn gọn, thông tin nhanh', 'Báo chí'],
                 ['Ngôn ngữ rõ ràng, dễ hiểu', 'Báo chí'],
                 ['Tính thời sự cao', 'Báo chí']],
                'Văn chương thiên về thẩm mĩ, cảm xúc; báo chí thiên về thông tin nhanh, chính xác.');
            $this->sortQ($L, 'Kéo mỗi câu văn vào nhóm CÓ TÍNH HÌNH TƯỢNG hoặc KHÔNG.',
                [['Nắng vàng óng ả trên cánh đồng', 'Có'],
                 ['Trăng lưỡi liềm treo đầu ngọn tre', 'Có'],
                 ['Sương mù giăng mờ mặt hồ', 'Có'],
                 ['Nhiệt độ hôm nay là 30 độ C', 'Không'],
                 ['Cuộc họp bắt đầu lúc 8 giờ', 'Không'],
                 ['Dân số là 5 triệu người', 'Không']],
                'Tính hình tượng gợi hình ảnh cụ thể; câu thông báo khô khan không có tính hình tượng.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUỘC VĂN CHƯƠNG hoặc THUỘC KHOA HỌC.',
                [['Cảm xúc dạt dào', 'Văn chương'],
                 ['Hình ảnh sinh động', 'Văn chương'],
                 ['Giọng điệu trữ tình', 'Văn chương'],
                 ['Thuật ngữ chính xác', 'Khoa học'],
                 ['Lập luận logic chặt chẽ', 'Khoa học'],
                 ['Số liệu khách quan', 'Khoa học']],
                'Văn chương dùng cảm xúc, hình ảnh; khoa học dùng thuật ngữ, logic, số liệu.');
            $this->sortQ($L, 'Kéo mỗi biểu hiện vào nhóm TÍNH TRUYỀN CẢM hoặc KHÔNG.',
                [['Câu văn khiến người đọc xúc động', 'Truyền cảm'],
                 ['Lời thơ lay động lòng người', 'Truyền cảm'],
                 ['Giọng văn đầy yêu thương', 'Truyền cảm'],
                 ['Câu văn liệt kê khô khan', 'Không'],
                 ['Thông báo hành chính', 'Không'],
                 ['Bảng số liệu thống kê', 'Không']],
                'Tính truyền cảm là khả năng lay động cảm xúc người đọc.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ba đặc trưng của phong cách ngôn ngữ văn chương là tính hình tượng, tính truyền cảm và tính ___ ___ ___.', [[0, 'cá thể hóa']],
                'Cá thể hóa: dấu ấn phong cách riêng của tác giả.');
            $this->fill($L, 'Khả năng gợi lên hình ảnh cụ thể, sinh động gọi là tính ___ ___.', [[0, 'hình tượng']],
                'Tính hình tượng làm nên sức hấp dẫn của văn chương.');
            $this->fill($L, 'Khả năng gợi cảm xúc, lay động lòng người đọc gọi là tính ___ ___.', [[0, 'truyền cảm']],
                'Văn chương không chỉ nói cho trí óc mà còn nói với trái tim.');
            $this->fill($L, 'Dấu ấn riêng, phong cách riêng của từng tác giả gọi là tính ___ ___ ___.', [[0, 'cá thể hóa']],
                'Đọc vài câu đã nhận ra giọng văn của tác giả quen thuộc.');
        }
    }

    private function seedTv10VanChuong2(): void
    {
        $L = 'tieng-viet-thpt-10-pcnc-van-chuong-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dấu hiệu nào giúp nhận diện phong cách ngôn ngữ văn chương trong văn bản?',
                ['Ngôn ngữ giàu hình ảnh, cảm xúc; nhiều biện pháp tu từ; dấu ấn cá nhân',
                 'Nhiều con số, bảng biểu thống kê',
                 'Câu văn ngắn gọn, khô khan như thông báo',
                 'Dùng toàn thuật ngữ chuyên ngành'],
                0, 'Văn bản văn chương dễ nhận ra qua hình ảnh, cảm xúc và giọng điệu riêng.');
            $this->quiz($L, 'Vì sao văn bản văn chương thường dùng nhiều biện pháp tu từ?',
                ['Để tăng tính hình tượng, truyền cảm cho lời văn',
                 'Để làm văn bản dài hơn',
                 'Để khó hiểu hơn',
                 'Để giống văn bản khoa học'],
                0, 'Biện pháp tu từ là phương tiện tạo hình ảnh và cảm xúc trong văn chương.');
            $this->quiz($L, 'Ngôn ngữ văn chương khác ngôn ngữ hằng ngày ở điểm nào?',
                ['Được chọn lọc, chau chuốt, giàu sức gợi hơn',
                 'Hoàn toàn giống nhau',
                 'Khô khan hơn',
                 'Không có cảm xúc'],
                0, 'Ngôn ngữ văn chương là ngôn ngữ toàn dân được chọn lọc, tinh luyện.');
            $this->quiz($L, 'Khi gặp văn bản có tính thời sự, ngắn gọn, khách quan, đó là phong cách nào?',
                ['Phong cách ngôn ngữ báo chí',
                 'Phong cách ngôn ngữ văn chương',
                 'Phong cách ngôn ngữ khoa học',
                 'Phong cách ngôn ngữ hành chính'],
                0, 'Tính thời sự, ngắn gọn, khách quan là dấu hiệu của phong cách báo chí.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đoạn trích (tự viết) với phong cách ngôn ngữ.',
                [['Nắng chiều nhuộm vàng con đường làng.', 'Văn chương'],
                 ['Chiều nay, giá vàng tăng 50 nghìn đồng.', 'Báo chí'],
                 ['Quang hợp là quá trình tổng hợp chất hữu cơ.', 'Khoa học'],
                 ['Kính gửi: Ban Giám hiệu nhà trường.', 'Hành chính']],
                'Nhận diện phong cách qua đặc điểm ngôn ngữ của văn bản.');
            $this->matching($L, 'Nối mỗi dấu hiệu với phong cách tương ứng.',
                [['Giàu hình ảnh, cảm xúc', 'Văn chương'],
                 ['Thông tin nhanh, ngắn gọn', 'Báo chí'],
                 ['Thuật ngữ, lập luận chặt chẽ', 'Khoa học'],
                 ['Khuôn mẫu, trang trọng', 'Hành chính']],
                'Mỗi phong cách có hệ dấu hiệu ngôn ngữ đặc trưng.');
            $this->matching($L, 'Nối mỗi biện pháp tu từ với tác dụng trong văn chương.',
                [['So sánh', 'Gợi hình ảnh cụ thể'],
                 ['Nhân hóa', 'Vật vô tri có hồn'],
                 ['Điệp ngữ', 'Nhấn mạnh, tạo nhịp'],
                 ['Ẩn dụ', 'Gợi liên tưởng sâu sắc']],
                'Biện pháp tu từ là "linh hồn" của tính hình tượng, truyền cảm.');
            $this->matching($L, 'Nối mỗi thể loại văn chương với đặc điểm ngôn ngữ.',
                [['Thơ', 'Ngôn ngữ hàm súc, giàu nhạc điệu'],
                 ['Truyện ngắn', 'Ngôn ngữ kể, tả sinh động'],
                 ['Tùy bút', 'Ngôn ngữ tự do, trữ tình'],
                 ['Kí', 'Ngôn ngữ chân thực, gợi cảm']],
                'Mỗi thể loại văn chương có sắc thái ngôn ngữ riêng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu văn vào nhóm VĂN CHƯƠNG hoặc BÁO CHÍ.',
                [['Hoàng hôn buông xuống, mặt sông lấp lánh ánh vàng.', 'Văn chương'],
                 ['Chiều nay, tại sông Hồng xảy ra vụ tai nạn.', 'Báo chí'],
                 ['Tiếng chim ríu rít gọi bình minh.', 'Văn chương'],
                 ['Sáng nay, giá xăng giảm 500 đồng một lít.', 'Báo chí'],
                 ['Mẹ ngồi khâu áo dưới ánh đèn dầu.', 'Văn chương'],
                 ['Tối qua, đội tuyển thắng 2-0.', 'Báo chí']],
                'Câu văn chương giàu hình ảnh, cảm xúc; câu báo chí đưa thông tin nhanh, khách quan.');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm VĂN CHƯƠNG hoặc KHOA HỌC.',
                [['Dùng nhiều tính từ gợi cảm', 'Văn chương'],
                 ['Câu văn có nhạc điệu', 'Văn chương'],
                 ['Bộc lộ cảm xúc trực tiếp', 'Văn chương'],
                 ['Dùng thuật ngữ chuyên ngành', 'Khoa học'],
                 ['Câu văn logic, chặt chẽ', 'Khoa học'],
                 ['Trình bày khách quan, vô cảm', 'Khoa học']],
                'Văn chương cảm tính, hình ảnh; khoa học lí tính, chính xác.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm GIÚP NHẬN DIỆN VĂN CHƯƠNG hoặc KHÔNG.',
                [['Nhiều hình ảnh so sánh', 'Giúp'],
                 ['Giọng điệu trữ tình', 'Giúp'],
                 ['Từ ngữ giàu sức gợi', 'Giúp'],
                 ['Bảng số liệu khô khan', 'Không'],
                 ['Thuật ngữ toán học', 'Không'],
                 ['Mẫu đơn hành chính', 'Không']],
                'Dấu hiệu văn chương: hình ảnh, cảm xúc, giọng điệu, từ ngữ gợi cảm.');
            $this->sortQ($L, 'Kéo mỗi văn bản vào nhóm THUỘC VĂN CHƯƠNG hoặc KHÔNG THUỘC.',
                [['Bài thơ về mẹ', 'Thuộc'],
                 ['Truyện ngắn về tuổi thơ', 'Thuộc'],
                 ['Tùy bút về Hà Nội', 'Thuộc'],
                 ['Bản tin thời sự', 'Không thuộc'],
                 ['Báo cáo khoa học', 'Không thuộc'],
                 ['Đơn xin nghỉ học', 'Không thuộc']],
                'Thơ, truyện, tùy bút thuộc văn chương; tin tức, báo cáo, đơn từ thì không.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Văn bản giàu hình ảnh, cảm xúc, có dấu ấn cá nhân thuộc phong cách ngôn ngữ ___ ___.', [[0, 'văn chương']],
                'Đó là ba dấu hiệu nhận biết phong cách văn chương.');
            $this->fill($L, 'Ngôn ngữ văn chương là ngôn ngữ toàn dân được ___ ___ , tinh luyện.', [[0, 'chọn lọc']],
                'Nhà văn chọn lọc từ ngữ để đạt hiệu quả thẩm mĩ cao nhất.');
            $this->fill($L, 'Biện pháp tu từ giúp tăng tính ___ ___ và tính truyền cảm cho lời văn.', [[0, 'hình tượng']],
                'So sánh, nhân hóa, ẩn dụ... tạo hình ảnh sinh động.');
            $this->fill($L, 'Văn bản đưa tin nhanh, ngắn gọn, khách quan thuộc phong cách ngôn ngữ ___ ___.', [[0, 'báo chí']],
                'Phân biệt văn chương với báo chí qua chức năng và đặc điểm ngôn ngữ.');
        }
    }

    private function seedTv10BaoChi1(): void
    {
        $L = 'tieng-viet-thpt-10-pcnc-bao-chi-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Chức năng chính của phong cách ngôn ngữ báo chí là gì?',
                ['Thông tin thời sự về đời sống xã hội',
                 'Biểu lộ cảm xúc cá nhân',
                 'Nghiên cứu khoa học',
                 'Giao tiếp hành chính'],
                0, 'Báo chí ra đời để thông tin nhanh về các sự kiện thời sự.');
            $this->quiz($L, 'Đặc trưng nào KHÔNG thuộc phong cách ngôn ngữ báo chí?',
                ['Tính hình tượng, truyền cảm mạnh như văn chương',
                 'Tính thông tin thời sự',
                 'Tính ngắn gọn',
                 'Tính sinh động, hấp dẫn'],
                0, 'Báo chí cần ngắn gọn, thông tin nhanh; tính hình tượng đậm nét là của văn chương.');
            $this->quiz($L, 'Ngôn ngữ báo chí yêu cầu gì về cách diễn đạt?',
                ['Rõ ràng, chính xác, dễ hiểu với đông đảo người đọc',
                 'Càng hoa mĩ càng tốt',
                 'Dùng nhiều từ cổ, khó hiểu',
                 'Viết dài dòng, lan man'],
                0, 'Báo chí hướng tới công chúng rộng rãi nên ngôn ngữ phải rõ ràng, dễ hiểu.');
            $this->quiz($L, 'Tính thời sự trong báo chí có nghĩa là gì?',
                ['Thông tin kịp thời về sự kiện đang hoặc vừa diễn ra',
                 'Viết về chuyện cổ tích',
                 'Nhắc lại sự kiện cách đây hàng trăm năm',
                 'Dự đoán tương lai xa'],
                0, 'Thời sự là tính kịp thời: đưa tin nhanh về cái đang diễn ra.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đặc trưng báo chí với biểu hiện.',
                [['Tính thông tin', 'Đưa tin đầy đủ ai, cái gì, ở đâu, khi nào'],
                 ['Tính thời sự', 'Đưa tin kịp thời, nóng hổi'],
                 ['Tính ngắn gọn', 'Câu văn súc tích, đi thẳng vào vấn đề'],
                 ['Tính sinh động', 'Cách viết hấp dẫn, lôi cuốn']],
                'Bốn đặc trưng cơ bản của phong cách ngôn ngữ báo chí.');
            $this->matching($L, 'Nối mỗi thể loại báo chí với đặc điểm.',
                [['Bản tin', 'Đưa tin ngắn gọn, khách quan'],
                 ['Phóng sự', 'Phản ánh sâu, có miêu tả, bình luận'],
                 ['Xã luận', 'Bình luận vấn đề thời sự'],
                 ['Phỏng vấn', 'Hỏi – đáp với nhân vật']],
                'Các thể loại báo chí phổ biến trên báo in và báo mạng.');
            $this->matching($L, 'Nối mỗi yếu tố của bản tin với nội dung.',
                [['Ai', 'Chủ thể của sự kiện'],
                 ['Cái gì', 'Sự việc diễn ra'],
                 ['Ở đâu', 'Địa điểm sự kiện'],
                 ['Khi nào', 'Thời gian sự kiện']],
                'Bản tin trả lời các câu hỏi: ai, cái gì, ở đâu, khi nào, như thế nào.');
            $this->matching($L, 'Nối mỗi câu với phong cách ngôn ngữ.',
                [['Chiều nay, giá vàng tăng mạnh.', 'Báo chí'],
                 ['Nắng vàng óng ả trên đồng.', 'Văn chương'],
                 ['Đơn xin nghỉ phép của tôi.', 'Hành chính'],
                 ['Định lí được chứng minh như sau.', 'Khoa học']],
                'Nhận diện phong cách qua mục đích và cách diễn đạt.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm BÁO CHÍ hoặc VĂN CHƯƠNG.',
                [['Thông tin nhanh, kịp thời', 'Báo chí'],
                 ['Ngôn ngữ rõ ràng, dễ hiểu', 'Báo chí'],
                 ['Câu văn ngắn gọn, súc tích', 'Báo chí'],
                 ['Giàu hình ảnh, nhạc điệu', 'Văn chương'],
                 ['Bộc lộ cảm xúc sâu sắc', 'Văn chương'],
                 ['Dấu ấn cá nhân đậm nét', 'Văn chương']],
                'Báo chí: nhanh, gọn, rõ; văn chương: đẹp, cảm xúc, cá tính.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ TÍNH THỜI SỰ hoặc KHÔNG.',
                [['Sáng nay, bão đổ bộ vào miền Trung.', 'Có'],
                 ['Tối qua, đội tuyển giành chiến thắng.', 'Có'],
                 ['Ngày xửa ngày xưa, có một nàng công chúa.', 'Không'],
                 ['Truyền thuyết kể rằng...', 'Không'],
                 ['Chiều nay, giá xăng giảm.', 'Có'],
                 ['Hôm qua, trường tổ chức hội thi.', 'Có']],
                'Tính thời sự: thông tin về sự kiện đang hoặc vừa diễn ra.');
            $this->sortQ($L, 'Kéo mỗi cách viết tít báo vào nhóm HAY hoặc CHƯA HAY.',
                [['Ngắn gọn, nêu trúng sự kiện', 'Hay'],
                 ['Gây tò mò, hấp dẫn', 'Hay'],
                 ['Chính xác, không giật tít', 'Hay'],
                 ['Dài dòng, lan man', 'Chưa hay'],
                 ['Giật tít sai sự thật', 'Chưa hay'],
                 ['Mơ hồ, không rõ sự kiện', 'Chưa hay']],
                'Tít báo hay: ngắn, trúng, hấp dẫn nhưng phải trung thực.');
            $this->sortQ($L, 'Kéo mỗi văn bản vào nhóm THUỘC BÁO CHÍ hoặc KHÔNG.',
                [['Bản tin thời sự', 'Thuộc'],
                 ['Bài phóng sự điều tra', 'Thuộc'],
                 ['Bài phỏng vấn', 'Thuộc'],
                 ['Bài thơ trữ tình', 'Không'],
                 ['Đơn xin việc', 'Không'],
                 ['Luận văn thạc sĩ', 'Không']],
                'Bản tin, phóng sự, phỏng vấn, xã luận thuộc phong cách báo chí.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Chức năng chính của phong cách ngôn ngữ báo chí là ___ ___ ___ ___.', [[0, 'thông tin thời sự']],
                'Báo chí sinh ra để thông tin kịp thời về đời sống.');
            $this->fill($L, 'Ngôn ngữ báo chí yêu cầu rõ ràng, chính xác và ___ ___.', [[0, 'dễ hiểu']],
                'Báo chí hướng tới đông đảo công chúng.');
            $this->fill($L, 'Đặc trưng về sự kịp thời của thông tin báo chí gọi là tính ___ ___.', [[0, 'thời sự']],
                'Tin càng nóng, càng mới càng có giá trị báo chí.');
            $this->fill($L, 'Câu văn báo chí cần ___ ___, súc tích, đi thẳng vào vấn đề.', [[0, 'ngắn gọn']],
                'Ngắn gọn giúp người đọc nắm tin nhanh.');
        }
    }

    private function seedTv10BaoChi2(): void
    {
        $L = 'tieng-viet-thpt-10-pcnc-bao-chi-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bản tin khác phóng sự ở điểm nào?',
                ['Bản tin đưa tin ngắn gọn, khách quan; phóng sự phản ánh sâu, có miêu tả, bình luận',
                 'Bản tin dài hơn phóng sự',
                 'Phóng sự không cần sự thật',
                 'Hai thể loại hoàn toàn giống nhau'],
                0, 'Bản tin: ngắn, khách quan. Phóng sự: sâu, có miêu tả, bình luận, giàu hình ảnh hơn.');
            $this->quiz($L, 'Một bản tin đầy đủ thường trả lời những câu hỏi nào?',
                ['Ai, cái gì, ở đâu, khi nào, như thế nào',
                 'Vì sao tác giả viết bài',
                 'Tác giả là ai',
                 'Bài báo dài bao nhiêu'],
                0, 'Bản tin cung cấp thông tin cơ bản: chủ thể, sự việc, địa điểm, thời gian, diễn biến.');
            $this->quiz($L, 'Ngôn ngữ của phóng sự có gì khác bản tin?',
                ['Sinh động, giàu hình ảnh, có miêu tả và bình luận hơn',
                 'Khô khan hơn bản tin',
                 'Không cần chính xác',
                 'Chỉ gồm số liệu'],
                0, 'Phóng sự vẫn đảm bảo sự thật nhưng cách viết sinh động, hấp dẫn hơn bản tin.');
            $this->quiz($L, 'Tít (tiêu đề) của bản tin báo chí nên như thế nào?',
                ['Ngắn gọn, nêu trúng sự kiện, hấp dẫn nhưng trung thực',
                 'Càng dài càng tốt',
                 'Giật tít sai sự thật để câu view',
                 'Mơ hồ, khó hiểu'],
                0, 'Tít báo là "bộ mặt" của tin: ngắn, trúng, hấp dẫn và phải trung thực.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thể loại với mục đích.',
                [['Bản tin', 'Thông báo nhanh sự kiện'],
                 ['Phóng sự', 'Phản ánh sâu sự kiện, vấn đề'],
                 ['Phỏng vấn', 'Lấy ý kiến nhân vật'],
                 ['Bình luận', 'Đánh giá vấn đề thời sự']],
                'Mỗi thể loại báo chí có mục đích và cách viết riêng.');
            $this->matching($L, 'Nối mỗi phần của bản tin với nội dung.',
                [['Tít', 'Nêu vắn tắt sự kiện'],
                 ['Sa-pô', 'Tóm tắt nội dung chính'],
                 ['Thân tin', 'Triển khai chi tiết sự kiện'],
                 ['Kết tin', 'Thông tin bổ sung, triển vọng']],
                'Cấu trúc bản tin: tít – sa-pô – thân tin – kết tin.');
            $this->matching($L, 'Nối mỗi câu với thể loại phù hợp.',
                [['Chiều nay, hội chợ khai mạc.', 'Bản tin'],
                 ['Lặn lội theo chân người gánh hàng rong...', 'Phóng sự'],
                 ['— Ông đánh giá thế nào về...?', 'Phỏng vấn'],
                 ['Cần nhìn thẳng vào vấn đề...', 'Bình luận']],
                'Nhận diện thể loại qua cách mở đầu và giọng văn.');
            $this->matching($L, 'Nối mỗi yêu cầu với lí do.',
                [['Trung thực', 'Báo chí phải tôn trọng sự thật'],
                 ['Kịp thời', 'Tin cũ mất giá trị thời sự'],
                 ['Ngắn gọn', 'Người đọc cần nắm tin nhanh'],
                 ['Hấp dẫn', 'Thu hút người đọc']],
                'Bốn yêu cầu cơ bản đối với tin tức báo chí.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm BẢN TIN hoặc PHÓNG SỰ.',
                [['Ngắn gọn, khách quan', 'Bản tin'],
                 ['Trả lời ai, cái gì, ở đâu, khi nào', 'Bản tin'],
                 ['Đưa tin nhanh', 'Bản tin'],
                 ['Phản ánh sâu, chi tiết', 'Phóng sự'],
                 ['Có miêu tả, bình luận', 'Phóng sự'],
                 ['Giàu hình ảnh, sinh động', 'Phóng sự']],
                'Bản tin thiên về thông báo nhanh; phóng sự thiên về phản ánh sâu.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm PHÙ HỢP hoặc KHÔNG PHÙ HỢP với ngôn ngữ bản tin.',
                [['Chiều nay, lễ hội khai mạc tại công viên.', 'Phù hợp'],
                 ['Sáng nay, trường tổ chức lễ khai giảng.', 'Phù hợp'],
                 ['Hoàng hôn buông xuống mặt sông lấp lánh.', 'Không phù hợp'],
                 ['Nỗi nhớ quê da diết trong lòng người.', 'Không phù hợp'],
                 ['Tối qua, đội bóng thắng 2-1.', 'Phù hợp'],
                 ['Trăng lên cao, gió thổi vi vu.', 'Không phù hợp']],
                'Ngôn ngữ bản tin: thông tin rõ ràng, không hoa mĩ như văn chương.');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm ĐÚNG hoặc SAI đạo đức báo chí.',
                [['Kiểm chứng thông tin trước khi đăng', 'Đúng'],
                 ['Tôn trọng sự thật', 'Đúng'],
                 ['Bảo vệ nguồn tin', 'Đúng'],
                 ['Bịa đặt tin để câu view', 'Sai'],
                 ['Giật tít sai sự thật', 'Sai'],
                 ['Xúc phạm danh dự người khác', 'Sai']],
                'Đạo đức báo chí: trung thực, kiểm chứng, tôn trọng con người.');
            $this->sortQ($L, 'Kéo mỗi phần vào nhóm ĐẦU BẢN TIN hoặc CUỐI BẢN TIN.',
                [['Tít', 'Đầu'],
                 ['Sa-pô tóm tắt', 'Đầu'],
                 ['Thông tin quan trọng nhất', 'Đầu'],
                 ['Chi tiết bổ sung', 'Cuối'],
                 ['Triển vọng sự kiện', 'Cuối'],
                 ['Lời kết', 'Cuối']],
                'Bản tin viết theo cấu trúc "tháp ngược": quan trọng nhất lên đầu.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thể loại đưa tin ngắn gọn, khách quan gọi là ___ ___.', [[0, 'bản tin']],
                'Bản tin là thể loại cơ bản nhất của báo chí.');
            $this->fill($L, 'Thể loại phản ánh sâu sự kiện, có miêu tả và bình luận gọi là ___ ___.', [[0, 'phóng sự']],
                'Phóng sự sâu và sinh động hơn bản tin.');
            $this->fill($L, 'Phần tóm tắt nội dung chính ngay sau tít báo gọi là ___ ___.', [[0, 'sa-pô']],
                'Sa-pô giúp người đọc nắm nhanh nội dung tin.');
            $this->fill($L, 'Nguyên tắc hàng đầu của báo chí là tôn trọng ___ ___.', [[0, 'sự thật']],
                'Trung thực là đạo đức nghề báo.');
        }
    }

    // ================= TIẾNG VIỆT 11 =================

    private function seedTv11Cau1(): void
    {
        $L = 'tieng-viet-thpt-11-cau-thanh-phan-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Câu đơn là gì?',
                ['Câu có một cụm chủ ngữ – vị ngữ',
                 'Câu có nhiều cụm chủ ngữ – vị ngữ',
                 'Câu không có chủ ngữ',
                 'Câu chỉ có một từ'],
                0, 'Câu đơn = một cụm C–V. Ví dụ: "Em / đang học bài."');
            $this->quiz($L, 'Câu ghép là gì?',
                ['Câu có từ hai cụm chủ ngữ – vị ngữ trở lên',
                 'Câu có một cụm chủ ngữ – vị ngữ',
                 'Câu không có vị ngữ',
                 'Câu đặc biệt'],
                0, 'Câu ghép gồm nhiều vế, mỗi vế có cấu tạo như một câu đơn.');
            $this->quiz($L, 'Chủ ngữ trong câu trả lời cho câu hỏi nào?',
                ['Ai? Cái gì? Con gì?',
                 'Làm gì? Thế nào? Là gì?',
                 'Khi nào? Ở đâu? Vì sao?',
                 'Bao nhiêu? Mấy?'],
                0, 'Chủ ngữ nêu đối tượng được nói tới; vị ngữ trả lời Làm gì? Thế nào? Là gì?');
            $this->quiz($L, 'Trong câu "Mẹ / đang nấu cơm", "đang nấu cơm" là thành phần gì?',
                ['Vị ngữ',
                 'Chủ ngữ',
                 'Trạng ngữ',
                 'Bổ ngữ'],
                0, '"Đang nấu cơm" trả lời câu hỏi "Làm gì?" → là vị ngữ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại câu với đặc điểm.',
                [['Câu đơn', 'Một cụm chủ ngữ – vị ngữ'],
                 ['Câu ghép', 'Từ hai cụm chủ ngữ – vị ngữ trở lên'],
                 ['Câu đặc biệt', 'Không có cấu tạo chủ – vị'],
                 ['Câu rút gọn', 'Lược bớt thành phần khôi phục được']],
                'Bốn loại câu cần phân biệt trong chương trình.');
            $this->matching($L, 'Nối mỗi câu với loại câu tương ứng.',
                [['Em đang học bài.', 'Câu đơn'],
                 ['Trời mưa, đường trơn.', 'Câu ghép'],
                 ['Ôi! Đẹp quá!', 'Câu đặc biệt'],
                 ['Đang học bài. (khôi phục: Em đang học bài)', 'Câu rút gọn']],
                'Xác định loại câu qua số cụm C–V và khả năng khôi phục thành phần.');
            $this->matching($L, 'Nối mỗi thành phần với câu hỏi nhận diện.',
                [['Chủ ngữ', 'Ai? Cái gì? Con gì?'],
                 ['Vị ngữ', 'Làm gì? Thế nào? Là gì?'],
                 ['Trạng ngữ chỉ thời gian', 'Khi nào?'],
                 ['Trạng ngữ chỉ nơi chốn', 'Ở đâu?']],
                'Đặt câu hỏi là cách nhanh nhất để xác định thành phần câu.');
            $this->matching($L, 'Nối mỗi vế câu ghép với quan hệ.',
                [['Trời mưa to, đường ngập nước.', 'Nguyên nhân – kết quả'],
                 ['Bạn học giỏi, tôi cũng cố gắng.', 'So sánh, tương phản'],
                 ['Học bài xong, em đi ngủ.', 'Nối tiếp'],
                 ['Nếu chăm chỉ, bạn sẽ tiến bộ.', 'Điều kiện – kết quả']],
                'Các vế câu ghép liên kết bằng quan hệ ý nghĩa nhất định.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU ĐƠN hoặc CÂU GHÉP.',
                [['Chim hót líu lo.', 'Câu đơn'],
                 ['Mẹ đang nấu cơm.', 'Câu đơn'],
                 ['Em thích đọc sách.', 'Câu đơn'],
                 ['Trời nắng, em đi chơi.', 'Câu ghép'],
                 ['Mẹ nấu cơm, bố đọc báo.', 'Câu ghép'],
                 ['Học xong, em giúp mẹ.', 'Câu ghép']],
                'Đếm số cụm chủ ngữ – vị ngữ: một cụm là câu đơn, từ hai cụm là câu ghép.');
            $this->sortQ($L, 'Kéo mỗi thành phần trong câu "Sáng nay, Lan / chăm chỉ / học bài" vào nhóm đúng.',
                [['Sáng nay', 'Trạng ngữ'],
                 ['Lan', 'Chủ ngữ'],
                 ['chăm chỉ', 'Vị ngữ (phần phụ)'],
                 ['học bài', 'Vị ngữ (trung tâm)'],
                 ['Lan', 'Chủ ngữ'],
                 ['Sáng nay', 'Trạng ngữ']],
                'Trạng ngữ chỉ thời gian "sáng nay"; chủ ngữ "Lan"; vị ngữ "chăm chỉ học bài".');
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm CỤM CHỦ – VỊ hoặc KHÔNG PHẢI.',
                [['Em / đang học', 'Cụm C–V'],
                 ['Trời / mưa to', 'Cụm C–V'],
                 ['Mẹ / nấu cơm', 'Cụm C–V'],
                 ['trên cánh đồng', 'Không phải'],
                 ['rất chăm chỉ', 'Không phải'],
                 ['buổi sáng mùa thu', 'Không phải']],
                'Cụm C–V phải có đủ chủ ngữ và vị ngữ tạo thành nòng cốt câu.');
            $this->sortQ($L, 'Kéo mỗi quan hệ từ vào nhóm NỐI VẾ GHÉP hoặc KHÔNG.',
                [['và', 'Nối vế'],
                 ['nhưng', 'Nối vế'],
                 ['vì... nên', 'Nối vế'],
                 ['trên', 'Không'],
                 ['rất', 'Không'],
                 ['những', 'Không']],
                'Quan hệ từ nối các vế câu ghép: và, nhưng, vì... nên, nếu... thì...');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Câu có một cụm chủ ngữ – vị ngữ gọi là câu ___.', [[0, 'đơn']],
                'Câu đơn: một nòng cốt C–V.');
            $this->fill($L, 'Câu có từ hai cụm chủ ngữ – vị ngữ trở lên gọi là câu ___.', [[0, 'ghép']],
                'Mỗi vế câu ghép có cấu tạo như một câu đơn.');
            $this->fill($L, 'Thành phần trả lời câu hỏi "Ai? Cái gì?" gọi là ___ ___.', [[0, 'chủ ngữ']],
                'Chủ ngữ nêu đối tượng được nói tới.');
            $this->fill($L, 'Thành phần trả lời câu hỏi "Làm gì? Thế nào?" gọi là ___ ___.', [[0, 'vị ngữ']],
                'Vị ngữ nêu hoạt động, tính chất của chủ ngữ.');
        }
    }

    private function seedTv11Cau2(): void
    {
        $L = 'tieng-viet-thpt-11-cau-thanh-phan-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trạng ngữ trong câu có chức năng gì?',
                ['Chỉ thời gian, nơi chốn, nguyên nhân, mục đích... của sự việc',
                 'Nêu đối tượng chính của câu',
                 'Thay thế hoàn toàn vị ngữ',
                 'Làm câu ngắn lại'],
                0, 'Trạng ngữ là thành phần phụ, bổ sung ý nghĩa hoàn cảnh cho câu.');
            $this->quiz($L, 'Câu đặc biệt là gì?',
                ['Câu không có cấu tạo chủ ngữ – vị ngữ, thường là một cụm từ',
                 'Câu rất dài',
                 'Câu có nhiều vế',
                 'Câu thiếu dấu câu'],
                0, 'Câu đặc biệt: "Ôi!", "Mưa!", "Buổi sáng mùa thu." — không phân tích được C–V.');
            $this->quiz($L, 'Câu rút gọn khác câu đặc biệt ở điểm nào?',
                ['Câu rút gọn lược thành phần nhưng khôi phục được; câu đặc biệt không khôi phục theo cấu tạo C–V',
                 'Hoàn toàn giống nhau',
                 'Câu rút gọn dài hơn',
                 'Câu đặc biệt luôn có chủ ngữ'],
                0, 'Rút gọn: "Đang học bài." → khôi phục "Em đang học bài."; đặc biệt: "Ôi!" không khôi phục C–V.');
            $this->quiz($L, 'Thành phần biệt lập trong câu là gì?',
                ['Thành phần không tham gia diễn đạt ý chính: gọi đáp, cảm thán, tình thái...',
                 'Chủ ngữ của câu',
                 'Vị ngữ của câu',
                 'Trạng ngữ của câu'],
                0, 'Thành phần biệt lập: "Này, bạn ơi!", "Thật may, trời tạnh." — tách khỏi nòng cốt câu.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi trạng ngữ với loại của nó.',
                [['Sáng nay', 'Trạng ngữ chỉ thời gian'],
                 ['Trên cánh đồng', 'Trạng ngữ chỉ nơi chốn'],
                 ['Vì trời mưa', 'Trạng ngữ chỉ nguyên nhân'],
                 ['Để giúp mẹ', 'Trạng ngữ chỉ mục đích']],
                'Trạng ngữ có nhiều loại theo ý nghĩa: thời gian, nơi chốn, nguyên nhân, mục đích...');
            $this->matching($L, 'Nối mỗi câu với loại câu.',
                [['Mưa! (không phân tích C–V được)', 'Câu đặc biệt'],
                 ['— Đi đâu đấy? — Đi học. (khôi phục: Tôi đi học)', 'Câu rút gọn'],
                 ['Trên trời, chim bay lượn.', 'Câu đơn có trạng ngữ'],
                 ['Ôi, đẹp quá!', 'Câu đặc biệt cảm thán']],
                'Phân biệt câu đặc biệt, câu rút gọn và câu đơn có trạng ngữ.');
            $this->matching($L, 'Nối mỗi thành phần biệt lập với ví dụ.',
                [['Thành phần gọi đáp', 'Này, bạn nghe tôi nói!'],
                 ['Thành phần cảm thán', 'Ôi, vui quá!'],
                 ['Thành phần tình thái', 'Có lẽ, trời sẽ mưa.'],
                 ['Thành phần phụ chú', 'Lan – bạn thân tôi – rất giỏi.']],
                'Bốn loại thành phần biệt lập thường gặp.');
            $this->matching($L, 'Nối mỗi câu hỏi với thành phần cần tìm.',
                [['Khi nào?', 'Trạng ngữ thời gian'],
                 ['Ở đâu?', 'Trạng ngữ nơi chốn'],
                 ['Vì sao?', 'Trạng ngữ nguyên nhân'],
                 ['Để làm gì?', 'Trạng ngữ mục đích']],
                'Đặt câu hỏi đúng giúp xác định loại trạng ngữ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm TRẠNG NGỮ hoặc KHÔNG PHẢI TRẠNG NGỮ.',
                [['Sáng nay', 'Trạng ngữ'],
                 ['Trên sân trường', 'Trạng ngữ'],
                 ['Vì trời mưa to', 'Trạng ngữ'],
                 ['Em học sinh', 'Không phải'],
                 ['đang đọc sách', 'Không phải'],
                 ['rất chăm chỉ', 'Không phải']],
                'Trạng ngữ đứng đầu câu, bổ sung hoàn cảnh, thường tách bằng dấu phẩy.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU ĐẶC BIỆT hoặc CÂU RÚT GỌN.',
                [['Ôi!', 'Đặc biệt'],
                 ['Mưa!', 'Đặc biệt'],
                 ['Buổi sáng mùa thu trong lành.', 'Đặc biệt'],
                 ['— Ăn cơm chưa? — Rồi. (Tôi ăn rồi)', 'Rút gọn'],
                 ['Đang làm bài tập. (Em đang làm)', 'Rút gọn'],
                 ['— Đi đâu? — Đi chợ. (Tôi đi chợ)', 'Rút gọn']],
                'Câu đặc biệt không khôi phục C–V; câu rút gọn khôi phục được thành phần đã lược.');
            $this->sortQ($L, 'Kéo mỗi thành phần biệt lập vào nhóm đúng loại.',
                [['Này (trong "Này, lại đây!")', 'Gọi đáp'],
                 ['Ôi (trong "Ôi, hay quá!")', 'Cảm thán'],
                 ['Có lẽ (trong "Có lẽ trời mưa")', 'Tình thái'],
                 ['Thật may (trong "Thật may, kịp giờ")', 'Tình thái'],
                 ['Bạn ơi (trong "Bạn ơi, chờ tôi")', 'Gọi đáp'],
                 ['Chao ôi (trong "Chao ôi, đẹp!")', 'Cảm thán']],
                'Gọi đáp: gọi người nghe; cảm thán: bộc lộ cảm xúc; tình thái: thái độ người nói.');
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm THÀNH PHẦN CHÍNH hoặc THÀNH PHẦN PHỤ của câu.',
                [['Chủ ngữ', 'Chính'],
                 ['Vị ngữ', 'Chính'],
                 ['Trạng ngữ', 'Phụ'],
                 ['Thành phần biệt lập', 'Phụ'],
                 ['Chủ ngữ', 'Chính'],
                 ['Vị ngữ', 'Chính']],
                'Thành phần chính tạo nòng cốt C–V; thành phần phụ bổ sung ý nghĩa.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thành phần chỉ thời gian, nơi chốn, nguyên nhân của sự việc gọi là ___ ___.', [[0, 'trạng ngữ']],
                'Trạng ngữ là thành phần phụ của câu.');
            $this->fill($L, 'Câu không có cấu tạo chủ ngữ – vị ngữ gọi là câu ___ ___.', [[0, 'đặc biệt']],
                'Ví dụ: "Ôi!", "Mưa rào!"');
            $this->fill($L, 'Câu lược bớt thành phần nhưng khôi phục được gọi là câu ___ ___.', [[0, 'rút gọn']],
                'Rút gọn tránh lặp từ, làm câu gọn hơn.');
            $this->fill($L, 'Thành phần không tham gia diễn đạt ý chính của câu gọi là thành phần ___ ___.', [[0, 'biệt lập']],
                'Ví dụ: thành phần gọi đáp, cảm thán, tình thái.');
        }
    }

    private function seedTv11TuTu1(): void
    {
        $L = 'tieng-viet-thpt-11-bien-phap-tu-tu-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Câu "Mặt trời xuống biển như hòn lửa" sử dụng biện pháp tu từ nào?',
                ['So sánh',
                 'Nhân hóa',
                 'Ẩn dụ',
                 'Hoán dụ'],
                0, 'Có từ so sánh "như": mặt trời được so sánh với hòn lửa.');
            $this->quiz($L, 'Biện pháp nhân hóa là gì?',
                ['Gán đặc điểm, hành động của con người cho sự vật',
                 'So sánh hai sự vật với nhau',
                 'Gọi tên sự vật bằng tên sự vật khác',
                 'Lặp lại từ ngữ'],
                0, 'Nhân hóa làm vật vô tri trở nên có hồn như con người.');
            $this->quiz($L, 'Câu "Người ta là hoa đất" (tự viết theo lối tục ngữ) sử dụng biện pháp nào?',
                ['Ẩn dụ',
                 'So sánh',
                 'Nhân hóa',
                 'Hoán dụ'],
                0, 'Không có từ so sánh mà gọi thẳng "người ta" là "hoa đất" → ẩn dụ phẩm chất.');
            $this->quiz($L, 'Câu "Cả lớp vỗ tay" (ý nói học sinh cả lớp) sử dụng biện pháp nào?',
                ['Hoán dụ',
                 'So sánh',
                 'Nhân hóa',
                 'Ẩn dụ'],
                0, 'Lấy vật chứa (lớp) để chỉ vật bị chứa (học sinh) → hoán dụ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi biện pháp với dấu hiệu nhận biết.',
                [['So sánh', 'Có từ so sánh: như, tựa, bằng...'],
                 ['Nhân hóa', 'Vật có hành động, tình cảm như người'],
                 ['Ẩn dụ', 'Gọi tên sự vật bằng tên sự vật khác (không có từ so sánh)'],
                 ['Hoán dụ', 'Gọi tên bằng sự vật có quan hệ gần gũi']],
                'Bốn biện pháp tu từ từ vựng cơ bản và dấu hiệu nhận biết.');
            $this->matching($L, 'Nối mỗi câu (tự viết) với biện pháp tu từ.',
                [['Trăng tròn như chiếc đĩa bạc.', 'So sánh'],
                 ['Chị gió đùa vui với lá.', 'Nhân hóa'],
                 ['Đầu bạc răng long vẫn học.', 'Ẩn dụ (tuổi già)'],
                 ['Cả trường reo hò.', 'Hoán dụ (học sinh cả trường)']],
                'Xác định biện pháp qua dấu hiệu và mối quan hệ giữa các sự vật.');
            $this->matching($L, 'Nối mỗi kiểu ẩn dụ với ví dụ.',
                [['Ẩn dụ hình thức', 'Về thăm quê, "thuyền" đã xa bờ (thuyền = người đi xa)'],
                 ['Ẩn dụ phẩm chất', 'Người tốt như "hoa thơm"'],
                 ['Ẩn dụ cách thức', '"Ăn" điểm cao (ăn = đạt được)'],
                 ['Ẩn dụ chuyển đổi cảm giác', 'Nắng "giòn" (thị giác → thính giác)']],
                'Ẩn dụ có bốn kiểu: hình thức, cách thức, phẩm chất, chuyển đổi cảm giác.');
            $this->matching($L, 'Nối mỗi kiểu hoán dụ với ví dụ.',
                [['Lấy vật chứa chỉ vật bị chứa', 'Cả lớp (học sinh cả lớp) vỗ tay.'],
                 ['Lấy dấu hiệu chỉ sự vật', 'Đầu bạc (người già) tiễn đầu xanh.'],
                 ['Lấy bộ phận chỉ toàn thể', 'Một trăm mái nhà (gia đình) mới.'],
                 ['Lấy cụ thể chỉ trừu tượng', 'Ăn cơm (bữa ăn) rồi đi.']],
                'Bốn kiểu hoán dụ dựa trên mối quan hệ gần gũi giữa các sự vật.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm SO SÁNH hoặc NHÂN HÓA.',
                [['Mắt sáng như sao.', 'So sánh'],
                 ['Da trắng như tuyết.', 'So sánh'],
                 ['Chạy nhanh như gió.', 'So sánh'],
                 ['Ông mặt trời cười tươi.', 'Nhân hóa'],
                 ['Chị mây dạo chơi.', 'Nhân hóa'],
                 ['Bác đồng hồ thức suốt đêm.', 'Nhân hóa']],
                'So sánh có từ "như"; nhân hóa gán hành động người cho vật.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ẨN DỤ hoặc HOÁN DỤ.',
                [['Thuyền về bến đỗ bình yên. (thuyền = người)', 'Ẩn dụ'],
                 ['Ăn quả nhớ kẻ trồng cây. (ăn quả = hưởng thụ)', 'Ẩn dụ'],
                 ['Cả nhà quây quần. (nhà = người trong nhà)', 'Hoán dụ'],
                 ['Áo nâu ra trận. (áo nâu = nông dân)', 'Hoán dụ'],
                 ['Lửa thử vàng, gian nan thử sức.', 'Ẩn dụ'],
                 ['Bàn tay ta làm nên tất cả. (bàn tay = sức lao động)', 'Hoán dụ']],
                'Ẩn dụ dựa trên nét tương đồng; hoán dụ dựa trên quan hệ gần gũi.');
            $this->sortQ($L, 'Kéo mỗi từ ngữ vào nhóm TỪ SO SÁNH hoặc KHÔNG PHẢI.',
                [['như', 'So sánh'],
                 ['tựa như', 'So sánh'],
                 ['bằng', 'So sánh'],
                 ['là', 'Không phải'],
                 ['rất', 'Không phải'],
                 ['đã', 'Không phải']],
                'Từ so sánh báo hiệu biện pháp so sánh: như, tựa, bằng, tựa như...');
            $this->sortQ($L, 'Kéo mỗi tác dụng vào nhóm CỦA SO SÁNH hoặc CỦA NHÂN HÓA.',
                [['Làm hình ảnh cụ thể, dễ hình dung', 'So sánh'],
                 ['Nhấn mạnh đặc điểm sự vật', 'So sánh'],
                 ['Vật vô tri trở nên sinh động', 'Nhân hóa'],
                 ['Gần gũi, có hồn như con người', 'Nhân hóa'],
                 ['Gợi liên tưởng rõ nét', 'So sánh'],
                 ['Thiên nhiên có tình cảm', 'Nhân hóa']],
                'So sánh làm rõ đặc điểm; nhân hóa làm vật có hồn.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Biện pháp đối chiếu hai sự vật có nét tương đồng, thường có từ "như" gọi là ___ ___.', [[0, 'so sánh']],
                'So sánh: A như B.');
            $this->fill($L, 'Biện pháp gán đặc điểm của con người cho sự vật gọi là ___ ___.', [[0, 'nhân hóa']],
                'Nhân hóa: vật như có hồn người.');
            $this->fill($L, 'Biện pháp gọi tên sự vật này bằng tên sự vật khác có nét tương đồng (không có từ so sánh) gọi là ___ ___.', [[0, 'ẩn dụ']],
                'Ẩn dụ: gọi thẳng tên, không dùng từ so sánh.');
            $this->fill($L, 'Biện pháp gọi tên bằng sự vật có quan hệ gần gũi (vật chứa – vật bị chứa...) gọi là ___ ___.', [[0, 'hoán dụ']],
                'Hoán dụ dựa trên quan hệ gần gũi, không phải tương đồng.');
        }
    }

    private function seedTv11TuTu2(): void
    {
        $L = 'tieng-viet-thpt-11-bien-phap-tu-tu-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Câu "Ngày ngày mặt trời đi qua trên lăng / Thấy một mặt trời trong lăng rất đỏ" (tự viết mô phỏng) – từ "mặt trời" lặp lại là biện pháp gì?',
                ['Điệp ngữ',
                 'Liệt kê',
                 'Nói quá',
                 'Chơi chữ'],
                0, 'Lặp lại từ "mặt trời" để nhấn mạnh, tạo nhịp → điệp ngữ.');
            $this->quiz($L, 'Biện pháp liệt kê là gì?',
                ['Kể ra hàng loạt sự vật, hiện tượng cùng loại',
                 'Lặp lại một từ nhiều lần',
                 'Phóng đại sự thật',
                 'Dùng từ đồng âm'],
                0, 'Liệt kê tạo ấn tượng về sự đầy đủ, phong phú, dồn dập.');
            $this->quiz($L, 'Câu "Đứt từng khúc ruột" (tự viết) sử dụng biện pháp nào?',
                ['Nói quá',
                 'Nói giảm',
                 'Liệt kê',
                 'Điệp ngữ'],
                0, 'Phóng đại nỗi đau lên mức "đứt ruột" → nói quá (khoa trương).');
            $this->quiz($L, 'Câu "Bà già đi chợ Cầu Đông / Bói một quẻ xem..." – cách dùng từ "bói" nhiều nghĩa thuộc biện pháp nào?',
                ['Chơi chữ',
                 'So sánh',
                 'Nhân hóa',
                 'Ẩn dụ'],
                0, 'Chơi chữ: dùng từ đồng âm, đa nghĩa tạo hiệu quả hài hước, sâu sắc.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi biện pháp với dấu hiệu nhận biết.',
                [['Điệp ngữ', 'Lặp lại từ, cụm từ có chủ ý'],
                 ['Liệt kê', 'Kể ra hàng loạt sự vật cùng loại'],
                 ['Nói quá', 'Phóng đại mức độ, tính chất'],
                 ['Chơi chữ', 'Dùng từ đồng âm, đa nghĩa']],
                'Bốn biện pháp tu từ nâng cao và dấu hiệu nhận biết.');
            $this->matching($L, 'Nối mỗi câu (tự viết) với biện pháp tu từ.',
                [['Tre xanh, tre đỏ, tre vàng...', 'Liệt kê + điệp ngữ'],
                 ['Nhớ ai như nhớ thuốc lào.', 'So sánh (kết hợp)'],
                 ['Chết đứt từng khúc ruột.', 'Nói quá'],
                 ['Con ngựa đá con ngựa đá.', 'Chơi chữ (đồng âm)']],
                'Một câu có thể kết hợp nhiều biện pháp tu từ.');
            $this->matching($L, 'Nối mỗi biện pháp với tác dụng.',
                [['Điệp ngữ', 'Nhấn mạnh, tạo nhịp điệu'],
                 ['Liệt kê', 'Gợi sự đầy đủ, dồn dập'],
                 ['Nói quá', 'Nhấn mạnh, gây ấn tượng mạnh'],
                 ['Nói giảm', 'Diễn đạt tế nhị, lịch sự']],
                'Mỗi biện pháp có tác dụng nghệ thuật riêng.');
            $this->matching($L, 'Nối mỗi ví dụ nói giảm với cách diễn đạt thẳng.',
                [['Đi xa (mất)', 'Chết'],
                 ['Không được khỏe', 'Ốm, yếu'],
                 ['Có tuổi', 'Già'],
                 ['Yên nghỉ', 'Chết']],
                'Nói giảm nói tránh: diễn đạt tế nhị điều nhạy cảm.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐIỆP NGỮ hoặc LIỆT KÊ.',
                [['Đẹp quá, đẹp quá đi!', 'Điệp ngữ'],
                 ['Nhớ mãi, nhớ mãi nụ cười ấy.', 'Điệp ngữ'],
                 ['Xanh xanh, xanh ngát một màu.', 'Điệp ngữ'],
                 ['Bàn, ghế, tủ, giường đầy đủ.', 'Liệt kê'],
                 ['Cơm, áo, gạo, tiền lo toan.', 'Liệt kê'],
                 ['Sách, vở, bút, thước ngăn nắp.', 'Liệt kê']],
                'Điệp ngữ lặp lại từ ngữ; liệt kê kể ra hàng loạt sự vật.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm NÓI QUÁ hoặc NÓI GIẢM.',
                [['Đau đứt từng khúc ruột.', 'Nói quá'],
                 ['Nhanh như chớp.', 'Nói quá'],
                 ['Dốc hết ruột gan.', 'Nói quá'],
                 ['Cụ đã đi xa.', 'Nói giảm'],
                 ['Bác ấy không được khỏe.', 'Nói giảm'],
                 ['Mẹ đã có tuổi rồi.', 'Nói giảm']],
                'Nói quá phóng đại; nói giảm diễn đạt tế nhị, tránh thô.');
            $this->sortQ($L, 'Kéo mỗi cặp từ vào nhóm CHƠI CHỮ hoặc KHÔNG.',
                [['đá (đá bóng) – đá (hòn đá)', 'Chơi chữ'],
                 ['lợi (lợi ích) – lợi (răng lợi)', 'Chơi chữ'],
                 ['chín (số 9) – chín (chín chắn)', 'Chơi chữ'],
                 ['xanh – đỏ', 'Không'],
                 ['to – nhỏ', 'Không'],
                 ['nhanh – chậm', 'Không']],
                'Chơi chữ dùng từ đồng âm khác nghĩa tạo hiệu quả bất ngờ.');
            $this->sortQ($L, 'Kéo mỗi tác dụng vào nhóm CỦA ĐIỆP NGỮ hoặc CỦA LIỆT KÊ.',
                [['Nhấn mạnh ý', 'Điệp ngữ'],
                 ['Tạo nhịp điệu dồn dập', 'Điệp ngữ'],
                 ['Gợi cảm xúc mãnh liệt', 'Điệp ngữ'],
                 ['Gợi sự đầy đủ, phong phú', 'Liệt kê'],
                 ['Khắc họa chi tiết', 'Liệt kê'],
                 ['Tạo ấn tượng bao quát', 'Liệt kê']],
                'Điệp ngữ nhấn mạnh bằng lặp lại; liệt kê gây ấn tượng bằng số lượng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Biện pháp lặp lại từ ngữ có chủ ý để nhấn mạnh gọi là ___ ___.', [[0, 'điệp ngữ']],
                'Điệp ngữ vừa nhấn mạnh vừa tạo nhịp điệu.');
            $this->fill($L, 'Biện pháp kể ra hàng loạt sự vật cùng loại gọi là ___ ___.', [[0, 'liệt kê']],
                'Liệt kê gợi sự đầy đủ, dồn dập.');
            $this->fill($L, 'Biện pháp phóng đại mức độ, tính chất của sự vật gọi là ___ ___.', [[0, 'nói quá']],
                'Nói quá còn gọi là khoa trương, ngoa dụ.');
            $this->fill($L, 'Biện pháp dùng từ đồng âm, đa nghĩa tạo hiệu quả bất ngờ gọi là ___ ___.', [[0, 'chơi chữ']],
                'Chơi chữ đòi hỏi sự tinh tế trong dùng từ.');
        }
    }

    // ================= TIẾNG VIỆT 12 =================

    private function seedTv12ChuaLoi1(): void
    {
        $L = 'tieng-viet-thpt-12-chua-loi-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Câu "Anh ấy là người rất bao dung, luôn giúp đỡ mọi người" – từ nào dùng SAI nghĩa?',
                ['Không có từ sai',
                 'bao dung',
                 'giúp đỡ',
                 'mọi người'],
                0, 'Câu này dùng từ đúng nghĩa, không có lỗi.', 'kho');
            $this->quiz($L, 'Câu "Cô ấy có một vẻ đẹp lộng lẫy, kiêu sa" – cách kết hợp từ nào chưa chính xác?',
                ['Không có lỗi kết hợp từ',
                 'vẻ đẹp lộng lẫy',
                 'lộng lẫy, kiêu sa',
                 'cô ấy có'],
                0, 'Các kết hợp từ đều tự nhiên, đúng nghĩa.', 'kho');
            $this->quiz($L, 'Từ nào dùng SAI trong câu: "Chúng ta cần phải đấu tranh chống lại cái xấu"?',
                ['Không có từ sai',
                 'đấu tranh',
                 'chống lại',
                 'cái xấu'],
                0, 'Câu đúng: "đấu tranh chống lại cái xấu" là kết hợp chuẩn.', 'kho');
            $this->quiz($L, 'Lỗi "dùng từ sai nghĩa" là lỗi gì?',
                ['Dùng từ không đúng với nghĩa vốn có của nó trong ngữ cảnh',
                 'Viết sai chính tả',
                 'Thiếu dấu câu',
                 'Câu quá ngắn'],
                0, 'Ví dụ: "bộc bạch" dùng sai thành "bộc lộ" không đúng ngữ cảnh.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lỗi dùng từ với ví dụ.',
                [['Lặp từ', 'Anh ấy ấy rất tốt.'],
                 ['Dùng từ sai nghĩa', 'Cô ấy rất "bộc trực" (ý nói thẳng thắn nhưng sai sắc thái).'],
                 ['Kết hợp từ gượng ép', '"Nắng chói chang" dùng cho đêm tối.'],
                 ['Dùng từ Hán Việt sai', '"Tử nạn" dùng cho người bị thương nhẹ.']],
                'Bốn lỗi dùng từ thường gặp khi viết văn.', 'kho');
            $this->matching($L, 'Nối mỗi câu sai với cách chữa.',
                [['Anh ấy ấy đi học.', 'Bỏ từ lặp: Anh ấy đi học.'],
                 ['Cô rất xinh xắn đẹp.', 'Bỏ từ thừa: Cô rất xinh.'],
                 ['Bạn Lan bạn ấy giỏi.', 'Bỏ lặp: Bạn Lan rất giỏi.'],
                 ['Em em thích đọc sách.', 'Bỏ lặp: Em thích đọc sách.']],
                'Lỗi lặp từ: chữa bằng cách bỏ từ thừa, giữ câu gọn.', 'kho');
            $this->matching($L, 'Nối mỗi từ Hán Việt với nghĩa đúng.',
                [['Tổ quốc', 'Đất nước'],
                 ['Đồng bào', 'Người cùng nòi giống'],
                 ['Giang sơn', 'Sông núi, đất nước'],
                 ['Phụ mẫu', 'Cha mẹ']],
                'Hiểu đúng nghĩa từ Hán Việt giúp dùng từ chính xác.', 'kho');
            $this->matching($L, 'Nối mỗi cặp từ với quan hệ.',
                [['Xinh – đẹp', 'Gần nghĩa (tránh lặp)'],
                 ['To – nhỏ', 'Trái nghĩa'],
                 ['Nhanh – chậm', 'Trái nghĩa'],
                 ['Vui – buồn', 'Trái nghĩa']],
                'Dùng từ gần nghĩa lặp lại gây lỗi lặp từ; cần thay bằng từ khác nghĩa.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (lỗi dùng từ).',
                [['Em rất yêu quý mẹ.', 'Đúng'],
                 ['Bạn ấy học rất chăm chỉ.', 'Đúng'],
                 ['Cô giáo giảng bài hay.', 'Đúng'],
                 ['Anh ấy ấy rất tốt bụng.', 'Sai (lặp từ)'],
                 ['Em em thích đá bóng.', 'Sai (lặp từ)'],
                 ['Cô ấy ấy xinh đẹp.', 'Sai (lặp từ)']],
                'Lặp từ "ấy ấy", "em em" là lỗi dùng từ cần chữa.', 'kho');
            $this->sortQ($L, 'Kéo mỗi kết hợp từ vào nhóm TỰ NHIÊN hoặc GƯỢNG ÉP.',
                [['Nắng chói chang', 'Tự nhiên'],
                 ['Mưa rào rào', 'Tự nhiên'],
                 ['Gió thổi vi vu', 'Tự nhiên'],
                 ['Nắng lạnh buốt', 'Gượng ép'],
                 ['Mưa khô khốc', 'Gượng ép'],
                 ['Gió im phăng phắc (sai ngữ cảnh)', 'Gượng ép']],
                'Kết hợp từ phải phù hợp nghĩa và ngữ cảnh.', 'kho');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ THUẦN VIỆT hoặc TỪ HÁN VIỆT.',
                [['Mẹ', 'Thuần Việt'],
                 ['Trời', 'Thuần Việt'],
                 ['Cơm', 'Thuần Việt'],
                 ['Phụ mẫu', 'Hán Việt'],
                 ['Thiên nhiên', 'Hán Việt'],
                 ['Tổ quốc', 'Hán Việt']],
                'Phân biệt để dùng đúng sắc thái: Hán Việt trang trọng, thuần Việt gần gũi.', 'kho');
            $this->sortQ($L, 'Kéo mỗi cách diễn đạt vào nhóm TRANG TRỌNG hoặc THÂN MẬT.',
                [['Kính thưa quý vị', 'Trang trọng'],
                 ['Trân trọng cảm ơn', 'Trang trọng'],
                 ['Kính gửi', 'Trang trọng'],
                 ['Ê, lại đây!', 'Thân mật'],
                 ['Chào cậu nhé!', 'Thân mật'],
                 ['Đi chơi không?', 'Thân mật']],
                'Dùng từ phải phù hợp phong cách: trang trọng trong văn bản chính thức.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Lỗi dùng một từ hai lần trong câu mà không có chủ ý gọi là lỗi ___ ___.', [[0, 'lặp từ']],
                'Chữa lỗi lặp từ bằng cách bỏ từ thừa.', 'kho');
            $this->fill($L, 'Dùng từ không đúng với nghĩa vốn có của nó gọi là lỗi dùng từ ___ ___.', [[0, 'sai nghĩa']],
                'Cần tra từ điển khi nghi ngờ nghĩa của từ.', 'kho');
            $this->fill($L, 'Kết hợp các từ không phù hợp với nhau gọi là kết hợp từ ___ ___.', [[0, 'gượng ép']],
                'Ví dụ: "nắng lạnh buốt" là kết hợp gượng ép.', 'kho');
            $this->fill($L, 'Khi viết văn bản trang trọng, nên dùng từ ___ ___ thay cho từ khẩu ngữ.', [[0, 'Hán Việt']],
                'Từ Hán Việt mang sắc thái trang trọng, lịch sự.', 'kho');
        }
    }

    private function seedTv12ChuaLoi2(): void
    {
        $L = 'tieng-viet-thpt-12-chua-loi-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Câu "Qua bài thơ, cho em hiểu thêm về quê hương" mắc lỗi gì?',
                ['Thiếu chủ ngữ',
                 'Thiếu vị ngữ',
                 'Sai chính tả',
                 'Thừa dấu câu'],
                0, 'Cụm "qua bài thơ" là trạng ngữ, câu thiếu chủ ngữ → chữa: "Bài thơ cho em hiểu thêm..."', 'kho');
            $this->quiz($L, 'Câu "Em rất thích đọc sách và mẹ em cũng vậy" có lỗi gì?',
                ['Không có lỗi',
                 'Thiếu chủ ngữ',
                 'Sai trật tự từ',
                 'Lặp từ'],
                0, 'Câu ghép đúng cấu trúc, đủ thành phần, không mắc lỗi.', 'kho');
            $this->quiz($L, 'Câu "Những bông hoa nở rộ khoe sắc trong nắng sớm mai" – thành phần nào là chủ ngữ?',
                ['Những bông hoa',
                 'nở rộ',
                 'khoe sắc',
                 'trong nắng sớm mai'],
                0, '"Những bông hoa" trả lời "Cái gì?" → chủ ngữ; "nở rộ khoe sắc..." là vị ngữ.', 'kho');
            $this->quiz($L, 'Lỗi "câu lủng củng" thường do nguyên nhân nào?',
                ['Sắp xếp các thành phần, vế câu lộn xộn, thiếu mạch lạc',
                 'Câu quá ngắn',
                 'Dùng từ Hán Việt',
                 'Viết đúng chính tả'],
                0, 'Câu lủng củng: ý tứ rối rắm, các vế không liên kết chặt chẽ.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lỗi đặt câu với ví dụ.',
                [['Thiếu chủ ngữ', 'Qua câu chuyện, cho ta bài học.'],
                 ['Thiếu vị ngữ', 'Những học sinh chăm chỉ trong lớp.'],
                 ['Sai trật tự từ', 'Em đi học sáng nay sớm.'],
                 ['Lủng củng', 'Việc học, em, rất thích, vì hay.']],
                'Bốn lỗi đặt câu thường gặp.', 'kho');
            $this->matching($L, 'Nối mỗi câu sai với cách chữa đúng.',
                [['Qua bài văn, giúp em hiểu bài.', 'Bài văn giúp em hiểu bài.'],
                 ['Những bạn học giỏi.', 'Những bạn học giỏi được khen thưởng.'],
                 ['Em đi chơi chiều hôm qua.', 'Chiều hôm qua, em đi chơi.'],
                 ['Vì trời mưa nên đường, trơn trượt.', 'Vì trời mưa nên đường trơn trượt.']],
                'Chữa lỗi: bổ sung thành phần thiếu, sắp xếp lại trật tự.', 'kho');
            $this->matching($L, 'Nối mỗi câu với thành phần còn thiếu.',
                [['Đang học bài. (thiếu ai?)', 'Thiếu chủ ngữ'],
                 ['Bạn Lan rất... (rất sao?)', 'Thiếu vị ngữ'],
                 ['Sáng nay, ... (ai làm gì?)', 'Thiếu chủ – vị'],
                 ['Em / đang đọc sách. (đủ)', 'Không thiếu']],
                'Xác định thành phần thiếu là bước đầu để chữa lỗi.', 'kho');
            $this->matching($L, 'Nối mỗi cách sắp xếp với đánh giá.',
                [['Trạng ngữ – chủ ngữ – vị ngữ', 'Trật tự tự nhiên'],
                 ['Chủ ngữ – vị ngữ – trạng ngữ', 'Trật tự tự nhiên'],
                 ['Vị ngữ – chủ ngữ – trạng ngữ', 'Lộn xộn, sai'],
                 ['Trạng ngữ – vị ngữ – chủ ngữ', 'Lộn xộn, sai']],
                'Trật tự tự nhiên của câu tiếng Việt: (trạng ngữ) – chủ ngữ – vị ngữ.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (lỗi cấu trúc).',
                [['Em đang học bài.', 'Đúng'],
                 ['Mẹ nấu cơm rất ngon.', 'Đúng'],
                 ['Chim hót líu lo trên cành.', 'Đúng'],
                 ['Qua câu chuyện, cho em bài học.', 'Sai (thiếu chủ ngữ)'],
                 ['Những người nông dân chăm chỉ.', 'Sai (thiếu vị ngữ)'],
                 ['Vì học giỏi nên bạn, được khen.', 'Sai (dấu phẩy sai)']],
                'Câu đúng phải đủ nòng cốt C–V và sắp xếp hợp lí.', 'kho');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm THIẾU CHỦ NGỮ hoặc THIẾU VỊ NGỮ.',
                [['Qua bài thơ, ta thêm yêu quê hương.', 'Thiếu chủ ngữ'],
                 ['Nhờ chăm chỉ, bạn ấy tiến bộ.', 'Thiếu chủ ngữ (vế đầu)'],
                 ['Những bông hoa đẹp.', 'Thiếu vị ngữ'],
                 ['Các bạn học sinh giỏi.', 'Thiếu vị ngữ'],
                 ['Em thích đọc truyện.', 'Không thiếu'],
                 ['Trời đang mưa to.', 'Không thiếu']],
                'Thiếu C hoặc V làm câu không trọn ý.', 'kho');
            $this->sortQ($L, 'Kéo mỗi cách đặt dấu phẩy vào nhóm ĐÚNG hoặc SAI.',
                [['Sáng nay, em đi học.', 'Đúng'],
                 ['Vì trời mưa, đường trơn.', 'Đúng'],
                 ['Em, đang học bài.', 'Sai'],
                 ['Mẹ, nấu cơm ngon.', 'Sai'],
                 ['Bạn Lan, học rất giỏi.', 'Sai'],
                 ['Chiều qua, bố về sớm.', 'Đúng']],
                'Dấu phẩy tách trạng ngữ, không tách chủ ngữ với vị ngữ.', 'kho');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm MẠCH LẠC hoặc LỦNG CỦNG.',
                [['Em thích đọc sách vì sách mở mang tri thức.', 'Mạch lạc'],
                 ['Sau khi học xong, em giúp mẹ việc nhà.', 'Mạch lạc'],
                 ['Trời đẹp, chúng em đi dã ngoại.', 'Mạch lạc'],
                 ['Việc học em rất vì thích hay.', 'Lủng củng'],
                 ['Sách em đọc thích vì hay nhiều.', 'Lủng củng'],
                 ['Mẹ nấu cơm em ăn ngon rất.', 'Lủng củng']],
                'Câu mạch lạc: trật tự hợp lí, ý rõ ràng.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Câu "Qua bài thơ, cho em hiểu thêm" mắc lỗi thiếu ___ ___.', [[0, 'chủ ngữ']],
                'Chữa: "Bài thơ cho em hiểu thêm về quê hương."', 'kho');
            $this->fill($L, 'Câu "Những học sinh chăm chỉ" mắc lỗi thiếu ___ ___.', [[0, 'vị ngữ']],
                'Chữa: "Những học sinh chăm chỉ được khen thưởng."', 'kho');
            $this->fill($L, 'Trật tự tự nhiên của câu: (trạng ngữ) – chủ ngữ – ___ ___.', [[0, 'vị ngữ']],
                'Không đảo lộn trật tự gây khó hiểu.', 'kho');
            $this->fill($L, 'Dấu phẩy dùng để tách ___ ___ với nòng cốt câu, không tách chủ ngữ với vị ngữ.', [[0, 'trạng ngữ']],
                'Ví dụ: "Sáng nay, em đi học."', 'kho');
        }
    }

    private function seedTv12LienKet1(): void
    {
        $L = 'tieng-viet-thpt-12-lien-ket-van-ban-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phép liên kết bằng cách lặp lại từ ngữ gọi là phép gì?',
                ['Phép lặp từ ngữ',
                 'Phép nối',
                 'Phép thế',
                 'Phép đồng nghĩa'],
                0, 'Phép lặp: lặp lại từ ngữ ở các câu để liên kết. Ví dụ: "Mẹ tôi... Mẹ tôi..."');
            $this->quiz($L, 'Trong đoạn: "Lan rất chăm. Cô ấy luôn dậy sớm." – từ "cô ấy" thực hiện phép liên kết nào?',
                ['Phép thế',
                 'Phép lặp',
                 'Phép nối',
                 'Phép trái nghĩa'],
                0, '"Cô ấy" thay thế cho "Lan" ở câu trước → phép thế.');
            $this->quiz($L, 'Phép nối trong liên kết câu sử dụng yếu tố nào?',
                ['Quan hệ từ, từ nối: và, nhưng, vì, tuy...',
                 'Từ láy',
                 'Từ Hán Việt',
                 'Dấu chấm than'],
                0, 'Phép nối dùng từ nối để liên kết ý nghĩa giữa các câu.');
            $this->quiz($L, 'Cặp từ "vui – buồn", "nhanh – chậm" tạo liên kết bằng phép nào?',
                ['Phép trái nghĩa',
                 'Phép lặp',
                 'Phép thế',
                 'Phép nối'],
                0, 'Dùng cặp từ trái nghĩa ở các câu tạo sự liên kết đối lập.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép liên kết với ví dụ.',
                [['Phép lặp', 'Hoa đẹp. Hoa thơm ngát.'],
                 ['Phép thế', 'Nam đến. Cậu ấy cười tươi.'],
                 ['Phép nối', 'Trời mưa. Vì vậy, đường trơn.'],
                 ['Phép đồng nghĩa', 'Mẹ vui. Bà cũng hạnh phúc.']],
                'Bốn phép liên kết thường gặp trong văn bản.');
            $this->matching($L, 'Nối mỗi từ thay thế với từ được thay.',
                [['Cô ấy', 'Lan (câu trước)'],
                 ['Việc này', 'Sự việc đã nêu'],
                 ['Ở đó', 'Địa điểm đã nêu'],
                 ['Như vậy', 'Cách thức đã nêu']],
                'Từ thay thế giúp tránh lặp từ, câu văn gọn hơn.');
            $this->matching($L, 'Nối mỗi từ nối với quan hệ ý nghĩa.',
                [['Vì vậy', 'Kết quả'],
                 ['Tuy nhiên', 'Tương phản'],
                 ['Ngoài ra', 'Bổ sung'],
                 ['Tóm lại', 'Tổng kết']],
                'Từ nối vừa liên kết hình thức vừa thể hiện quan hệ ý nghĩa.');
            $this->matching($L, 'Nối mỗi cặp từ với phép liên kết.',
                [['Mẹ – bà (gần nghĩa)', 'Đồng nghĩa'],
                 ['Nóng – lạnh', 'Trái nghĩa'],
                 ['Học – hành (lặp)', 'Lặp từ ngữ'],
                 ['Bạn ấy (thay tên)', 'Thế']],
                'Nhận diện phép liên kết qua mối quan hệ từ ngữ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cặp câu vào nhóm CÓ PHÉP LẶP hoặc KHÔNG.',
                [['Em yêu trường. Trường đẹp lắm.', 'Có (lặp "trường")'],
                 ['Mẹ hiền. Mẹ đảm đang.', 'Có (lặp "mẹ")'],
                 ['Trời xanh. Mây trắng.', 'Không'],
                 ['Chim hót. Hoa nở.', 'Không'],
                 ['Sách hay. Sách bổ ích.', 'Có (lặp "sách")'],
                 ['Biển rộng. Sóng to.', 'Không']],
                'Phép lặp: từ ngữ xuất hiện ở cả hai câu.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ THAY THẾ hoặc KHÔNG PHẢI.',
                [['ấy', 'Thay thế'],
                 ['đó', 'Thay thế'],
                 ['việc này', 'Thay thế'],
                 ['như vậy', 'Thay thế'],
                 ['rất', 'Không phải'],
                 ['những', 'Không phải']],
                'Từ thay thế: ấy, đó, này, việc này, như vậy...');
            $this->sortQ($L, 'Kéo mỗi từ nối vào nhóm QUAN HỆ NGUYÊN NHÂN hoặc TƯƠNG PHẢN.',
                [['Vì vậy', 'Nguyên nhân – kết quả'],
                 ['Do đó', 'Nguyên nhân – kết quả'],
                 ['Cho nên', 'Nguyên nhân – kết quả'],
                 ['Tuy nhiên', 'Tương phản'],
                 ['Nhưng', 'Tương phản'],
                 ['Mặc dù... nhưng', 'Tương phản']],
                'Từ nối thể hiện quan hệ ý nghĩa giữa các câu.');
            $this->sortQ($L, 'Kéo mỗi đoạn vào nhóm LIÊN KẾT TỐT hoặc RỜI RẠC.',
                [['Lan chăm học. Cô ấy luôn đứng đầu lớp.', 'Tốt (phép thế)'],
                 ['Trời mưa. Vì vậy, em mang ô.', 'Tốt (phép nối)'],
                 ['Hoa hồng đẹp. Xe máy chạy nhanh.', 'Rời rạc'],
                 ['Mẹ nấu cơm. Bóng đá hay.', 'Rời rạc'],
                 ['Sách bổ ích. Vì thế, em đọc nhiều.', 'Tốt (phép nối)'],
                 ['Em thích vẽ. Em vẽ đẹp.', 'Tốt (phép lặp)']],
                'Liên kết tốt: các câu gắn bó bằng phép liên kết rõ ràng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phép liên kết bằng cách lặp lại từ ngữ ở các câu gọi là phép ___.', [[0, 'lặp']],
                'Ví dụ: "Trường em đẹp. Trường em rộng."');
            $this->fill($L, 'Phép liên kết bằng cách dùng từ thay thế (ấy, đó, việc này) gọi là phép ___.', [[0, 'thế']],
                'Phép thế giúp tránh lặp từ.');
            $this->fill($L, 'Phép liên kết bằng quan hệ từ, từ nối (và, nhưng, vì vậy) gọi là phép ___.', [[0, 'nối']],
                'Phép nối thể hiện quan hệ ý nghĩa giữa các câu.');
            $this->fill($L, 'Dùng cặp từ đồng nghĩa hoặc trái nghĩa ở các câu tạo liên kết bằng phép ___ nghĩa.', [[0, 'đồng']],
                'Hoặc phép trái nghĩa với cặp từ đối lập.');
        }
    }

    private function seedTv12LienKet2(): void
    {
        $L = 'tieng-viet-thpt-12-lien-ket-van-ban-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đoạn văn được coi là mạch lạc khi nào?',
                ['Các câu liên kết chặt chẽ, triển khai đúng chủ đề theo trình tự hợp lí',
                 'Các câu đều rất dài',
                 'Đoạn văn càng dài càng tốt',
                 'Không cần câu chủ đề'],
                0, 'Mạch lạc = liên kết chặt + đúng chủ đề + trình tự hợp lí.');
            $this->quiz($L, 'Câu chủ đề của đoạn văn thường đứng ở vị trí nào?',
                ['Đầu hoặc cuối đoạn văn',
                 'Luôn ở giữa đoạn',
                 'Không bao giờ xuất hiện',
                 'Ở đoạn khác'],
                0, 'Câu chủ đề nêu ý chính, thường đứng đầu (diễn dịch) hoặc cuối (quy nạp) đoạn.');
            $this->quiz($L, 'Trình tự triển khai đoạn văn nào là hợp lí?',
                ['Theo thời gian, không gian hoặc logic từ chung đến riêng',
                 'Ngẫu nhiên, không theo trật tự',
                 'Nhảy cóc giữa các ý',
                 'Lặp đi lặp lại một ý'],
                0, 'Trình tự hợp lí giúp người đọc theo dõi dễ dàng.');
            $this->quiz($L, 'Dấu hiệu của đoạn văn thiếu mạch lạc là gì?',
                ['Các câu rời rạc, lạc chủ đề, sắp xếp lộn xộn',
                 'Đoạn văn ngắn',
                 'Dùng nhiều dấu câu',
                 'Có câu chủ đề'],
                0, 'Thiếu mạch lạc: câu không liên kết, ý lộn xộn, lạc đề.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cách triển khai với mô tả.',
                [['Diễn dịch', 'Câu chủ đề đầu đoạn, các câu sau triển khai'],
                 ['Quy nạp', 'Các câu dẫn dắt, câu chủ đề cuối đoạn'],
                 ['Song hành', 'Các câu ngang nhau, không có câu chủ đề rõ'],
                 ['Móc xích', 'Câu sau tiếp nối ý câu trước']],
                'Bốn cách triển khai đoạn văn thường gặp.');
            $this->matching($L, 'Nối mỗi trình tự với ví dụ.',
                [['Thời gian', 'Sáng... trưa... chiều...'],
                 ['Không gian', 'Từ xa... đến gần...'],
                 ['Logic chung – riêng', 'Học sinh nói chung... bạn Lan nói riêng...'],
                 ['Nguyên nhân – kết quả', 'Vì mưa... nên đường trơn...']],
                'Trình tự hợp lí tạo mạch lạc cho đoạn văn.');
            $this->matching($L, 'Nối mỗi đoạn với cách triển khai.',
                [['Học tập rất quan trọng. Nó giúp ta... Vì vậy...', 'Diễn dịch'],
                 ['Lan chăm học. Lan giúp bạn. Lan thật đáng quý.', 'Quy nạp'],
                 ['Mùa xuân ấm. Mùa hạ nóng. Mùa thu mát.', 'Song hành'],
                 ['Trời mưa. Mưa làm đường trơn. Đường trơn gây tai nạn.', 'Móc xích']],
                'Nhận diện cách triển khai qua vị trí câu chủ đề và mạch ý.');
            $this->matching($L, 'Nối mỗi lỗi với cách khắc phục.',
                [['Lạc chủ đề', 'Bám sát câu chủ đề khi viết'],
                 ['Câu rời rạc', 'Dùng phép liên kết giữa các câu'],
                 ['Sắp xếp lộn xộn', 'Sắp xếp theo trình tự hợp lí'],
                 ['Thiếu câu chủ đề', 'Xác định ý chính trước khi viết']],
                'Khắc phục lỗi giúp đoạn văn mạch lạc hơn.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm ĐOẠN VĂN MẠCH LẠC hoặc KHÔNG MẠCH LẠC.',
                [['Các câu liên kết chặt chẽ', 'Mạch lạc'],
                 ['Triển khai đúng chủ đề', 'Mạch lạc'],
                 ['Sắp xếp theo trình tự hợp lí', 'Mạch lạc'],
                 ['Các câu rời rạc', 'Không mạch lạc'],
                 ['Lạc sang chủ đề khác', 'Không mạch lạc'],
                 ['Ý lộn xộn, nhảy cóc', 'Không mạch lạc']],
                'Mạch lạc là yêu cầu hàng đầu của đoạn văn.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU CHỦ ĐỀ hoặc CÂU TRIỂN KHAI.',
                [['Đọc sách mang lại nhiều lợi ích.', 'Chủ đề'],
                 ['Sách mở mang tri thức.', 'Triển khai'],
                 ['Sách bồi đắp tâm hồn.', 'Triển khai'],
                 ['Vì vậy, hãy đọc sách mỗi ngày.', 'Triển khai (kết)'],
                 ['Tập thể dục rất tốt cho sức khỏe.', 'Chủ đề'],
                 ['Nó giúp cơ thể dẻo dai.', 'Triển khai']],
                'Câu chủ đề nêu ý chính; câu triển khai làm rõ ý chính.');
            $this->sortQ($L, 'Sắp xếp các câu sau thành đoạn văn mạch lạc (kéo vào nhóm theo thứ tự 1-2-3).',
                [['Sáng sớm, em thức dậy.', '1 - Mở đầu'],
                 ['Em đánh răng, rửa mặt.', '2 - Tiếp nối'],
                 ['Sau đó, em ăn sáng rồi đi học.', '3 - Kết thúc'],
                 ['Tối qua, em xem phim.', 'Ngoài mạch'],
                 ['Con mèo nhà em rất ngoan.', 'Ngoài mạch'],
                 ['Hôm nay trời nắng đẹp.', 'Ngoài mạch']],
                'Đoạn văn theo trình tự thời gian: sáng sớm → vệ sinh → ăn sáng đi học.');
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm THUỘC CHỦ ĐỀ "lợi ích đọc sách" hoặc LẠC CHỦ ĐỀ.',
                [['Mở mang tri thức', 'Thuộc chủ đề'],
                 ['Bồi đắp tâm hồn', 'Thuộc chủ đề'],
                 ['Rèn kĩ năng tập trung', 'Thuộc chủ đề'],
                 ['Cách nấu món phở ngon', 'Lạc chủ đề'],
                 ['Giá vé xem phim', 'Lạc chủ đề'],
                 ['Luật bóng đá mới', 'Lạc chủ đề']],
                'Mọi câu trong đoạn phải hướng về chủ đề chung.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Câu nêu ý chính của đoạn văn gọi là câu ___ ___.', [[0, 'chủ đề']],
                'Câu chủ đề thường đứng đầu hoặc cuối đoạn.');
            $this->fill($L, 'Đoạn văn có các câu liên kết chặt chẽ, đúng chủ đề gọi là đoạn văn ___ ___.', [[0, 'mạch lạc']],
                'Mạch lạc là yêu cầu quan trọng nhất của đoạn văn.');
            $this->fill($L, 'Cách triển khai câu chủ đề đứng đầu đoạn gọi là ___ ___.', [[0, 'diễn dịch']],
                'Diễn dịch: từ chung đến riêng.');
            $this->fill($L, 'Cách triển khai câu chủ đề đứng cuối đoạn gọi là ___ ___.', [[0, 'quy nạp']],
                'Quy nạp: từ riêng đến chung.');
        }
    }

    // ================= TIẾNG ANH 10 =================

    private function seedEn10Thi1(): void
    {
        $L = 'tieng-anh-thpt-10-cac-thi-co-ban-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the correct answer: She ___ to school every day. (Cô ấy đi học mỗi ngày.)',
                ['go', 'goes', 'going', 'gone'], 1,
                'Thì hiện tại đơn diễn tả thói quen; chủ ngữ "she" (ngôi 3 số ít) → động từ thêm "s/es".');
            $this->quiz($L, 'Choose the correct answer: Look! The baby ___. (Nhìn kìa! Em bé đang khóc.)',
                ['cry', 'cries', 'is crying', 'crieing'], 2,
                '"Look!" là dấu hiệu của thì hiện tại tiếp diễn: am/is/are + V-ing.');
            $this->quiz($L, 'Choose the correct answer: They ___ football now. (Bây giờ họ đang đá bóng.)',
                ['play', 'plays', 'are playing', 'is playing'], 2,
                '"Now" là dấu hiệu hiện tại tiếp diễn; chủ ngữ "they" → "are playing".');
            $this->quiz($L, 'Choose the correct answer: Water ___ at 100°C. (Nước sôi ở 100°C.)',
                ['boil', 'boils', 'is boiling', 'boiled'], 1,
                'Sự thật hiển nhiên dùng hiện tại đơn; chủ ngữ "water" (số ít) → "boils".');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each sentence with the correct tense. (Nối mỗi câu với thì đúng.)',
                [['She works in a bank.', 'Present simple'],
                 ['She is working now.', 'Present continuous'],
                 ['They play tennis every Sunday.', 'Present simple'],
                 ['Look! They are playing tennis.', 'Present continuous']],
                'Present simple: thói quen, sự thật. Present continuous: đang diễn ra lúc nói.');
            $this->matching($L, 'Match each time marker with the tense it signals. (Nối dấu hiệu với thì.)',
                [['every day', 'Present simple'],
                 ['usually', 'Present simple'],
                 ['now', 'Present continuous'],
                 ['at the moment', 'Present continuous']],
                'Dấu hiệu giúp nhận biết thì: every day/usually → hiện tại đơn; now/at the moment → tiếp diễn.');
            $this->matching($L, 'Match each subject with the correct verb form. (Nối chủ ngữ với dạng động từ.)',
                [['I', 'am'],
                 ['He', 'is'],
                 ['They', 'are'],
                 ['She', 'plays']],
                'I + am; he/she/it + is (V-s); you/we/they + are (V nguyên mẫu).');
            $this->matching($L, 'Match each verb with its -ing form. (Nối động từ với dạng V-ing.)',
                [['swim', 'swimming'],
                 ['write', 'writing'],
                 ['run', 'running'],
                 ['play', 'playing']],
                'Chú ý chính tả: swim → swimming (gấp đôi phụ âm), write → writing (bỏ e).');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each sentence into PRESENT SIMPLE or PRESENT CONTINUOUS.',
                [['She reads books every night.', 'Present simple'],
                 ['Water boils at 100 degrees.', 'Present simple'],
                 ['He is reading now.', 'Present continuous'],
                 ['Look! She is dancing.', 'Present continuous'],
                 ['They go to school by bus.', 'Present simple'],
                 ['Listen! The baby is crying.', 'Present continuous']],
                'Thói quen, sự thật → present simple; đang diễn ra (now, look!, listen!) → present continuous.');
            $this->sortQ($L, 'Drag each verb into CORRECT or INCORRECT third-person form.',
                [['plays', 'Correct'],
                 ['goes', 'Correct'],
                 ['watches', 'Correct'],
                 ['playes', 'Incorrect'],
                 ['gos', 'Incorrect'],
                 ['watchs', 'Incorrect']],
                'Ngôi 3 số ít: play → plays, go → goes, watch → watches.');
            $this->sortQ($L, 'Drag each word into TIME MARKER OF PRESENT SIMPLE or PRESENT CONTINUOUS.',
                [['every day', 'Present simple'],
                 ['usually', 'Present simple'],
                 ['sometimes', 'Present simple'],
                 ['now', 'Present continuous'],
                 ['at the moment', 'Present continuous'],
                 ['Look!', 'Present continuous']],
                'Dấu hiệu thời gian là "chìa khóa" chọn thì đúng.');
            $this->sortQ($L, 'Drag each sentence into AFFIRMATIVE or NEGATIVE.',
                [['She likes tea.', 'Affirmative'],
                 ['They are playing.', 'Affirmative'],
                 ['She does not like tea.', 'Negative'],
                 ['They are not playing.', 'Negative'],
                 ['He watches TV.', 'Affirmative'],
                 ['He does not watch TV.', 'Negative']],
                'Phủ định hiện tại đơn: do/does + not; tiếp diễn: am/is/are + not.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'She ___ (go) to school by bike every day.', [[0, 'goes']],
                'Hiện tại đơn, chủ ngữ ngôi 3 số ít → "goes".');
            $this->fill($L, 'Look! They ___ (play) football in the yard.', [[0, 'are playing']],
                '"Look!" → hiện tại tiếp diễn: are + playing.');
            $this->fill($L, 'Water ___ (boil) at 100°C.', [[0, 'boils']],
                'Sự thật hiển nhiên → hiện tại đơn, chủ ngữ số ít → "boils".');
            $this->fill($L, 'I ___ (do) my homework at the moment.', [[0, 'am doing']],
                '"At the moment" → hiện tại tiếp diễn: am + doing.');
        }
    }

    private function seedEn10Thi2(): void
    {
        $L = 'tieng-anh-thpt-10-cac-thi-co-ban-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the correct answer: She ___ to Hanoi yesterday. (Hôm qua cô ấy đã đi Hà Nội.)',
                ['go', 'goes', 'went', 'gone'], 2,
                '"Yesterday" là dấu hiệu quá khứ đơn; "go" → "went" (bất quy tắc).');
            $this->quiz($L, 'Choose the correct answer: They ___ the match last night. (Tối qua họ đã xem trận đấu.)',
                ['watch', 'watched', 'watching', 'watches'], 1,
                '"Last night" → quá khứ đơn; động từ có quy tắc thêm "-ed".');
            $this->quiz($L, 'Choose the correct answer: I think it ___ tomorrow. (Tôi nghĩ ngày mai trời sẽ mưa.)',
                ['rains', 'rained', 'will rain', 'is raining'], 2,
                'Dự đoán tương lai → "will + V" (tương lai đơn).');
            $this->quiz($L, 'Choose the correct answer: ___ you help me? (Bạn sẽ giúp tôi chứ? – quyết định lúc nói)',
                ['Do', 'Did', 'Will', 'Are'], 2,
                'Đề nghị, quyết định ngay lúc nói → "Will you...?"');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each sentence with the correct tense. (Nối mỗi câu với thì đúng.)',
                [['She visited Hue last year.', 'Past simple'],
                 ['She will visit Hue next year.', 'Future simple'],
                 ['They played chess yesterday.', 'Past simple'],
                 ['They will play chess tomorrow.', 'Future simple']],
                'Last year/yesterday → past simple; next year/tomorrow → future simple.');
            $this->matching($L, 'Match each verb with its past form. (Nối động từ với dạng quá khứ.)',
                [['go', 'went'],
                 ['eat', 'ate'],
                 ['see', 'saw'],
                 ['play', 'played']],
                'Động từ bất quy tắc cần học thuộc: go-went, eat-ate, see-saw.');
            $this->matching($L, 'Match each time marker with the tense. (Nối dấu hiệu với thì.)',
                [['yesterday', 'Past simple'],
                 ['last week', 'Past simple'],
                 ['tomorrow', 'Future simple'],
                 ['next month', 'Future simple']],
                'Yesterday/last... → quá khứ; tomorrow/next... → tương lai.');
            $this->matching($L, 'Match each use with the tense. (Nối cách dùng với thì.)',
                [['Finished action in the past', 'Past simple'],
                 ['Decision at the moment of speaking', 'Future simple'],
                 ['Prediction about the future', 'Future simple'],
                 ['Habit in the past with "used to"', 'Past simple']],
                'Quá khứ đơn: hành động đã xong. Tương lai đơn: quyết định, dự đoán.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each sentence into PAST SIMPLE or FUTURE SIMPLE.',
                [['She bought a book yesterday.', 'Past simple'],
                 ['They watched TV last night.', 'Past simple'],
                 ['She will buy a book tomorrow.', 'Future simple'],
                 ['They will watch TV tonight.', 'Future simple'],
                 ['He went home late.', 'Past simple'],
                 ['He will go home late.', 'Future simple']],
                'Yesterday/last night → past simple; tomorrow/tonight (sắp tới) → future simple.');
            $this->sortQ($L, 'Drag each verb into REGULAR or IRREGULAR past form.',
                [['played', 'Regular'],
                 ['watched', 'Regular'],
                 ['cleaned', 'Regular'],
                 ['went', 'Irregular'],
                 ['ate', 'Irregular'],
                 ['saw', 'Irregular']],
                'Có quy tắc thêm -ed; bất quy tắc phải học thuộc.');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT.',
                [['She went to school yesterday.', 'Correct'],
                 ['They will come tomorrow.', 'Correct'],
                 ['She go to school yesterday.', 'Incorrect'],
                 ['They will comes tomorrow.', 'Incorrect'],
                 ['He ate rice last night.', 'Correct'],
                 ['He will ate rice tonight.', 'Incorrect']],
                'Past simple: V-ed/V2. Future simple: will + V nguyên mẫu (không chia).');
            $this->sortQ($L, 'Drag each time word into PAST or FUTURE marker.',
                [['yesterday', 'Past'],
                 ['last Monday', 'Past'],
                 ['in 2020', 'Past'],
                 ['tomorrow', 'Future'],
                 ['next week', 'Future'],
                 ['tonight (upcoming)', 'Future']],
                'Dấu hiệu thời gian quyết định thì của câu.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'She ___ (go) to the market yesterday.', [[0, 'went']],
                '"Yesterday" → quá khứ đơn; go → went.');
            $this->fill($L, 'They ___ (watch) a film last night.', [[0, 'watched']],
                '"Last night" → quá khứ đơn; watch → watched.');
            $this->fill($L, 'I think it ___ (rain) tomorrow.', [[0, 'will rain']],
                'Dự đoán tương lai → will + V nguyên mẫu.');
            $this->fill($L, '___ you open the door for me? (đề nghị)', [[0, 'Will']],
                'Đề nghị lịch sự: "Will you...?"');
        }
    }

    private function seedEn10Dk1a(): void
    {
        $L = 'tieng-anh-thpt-10-cau-dieu-kien-1-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the correct answer: If it rains, we ___ at home. (Nếu trời mưa, chúng tôi sẽ ở nhà.)',
                ['stay', 'will stay', 'stayed', 'staying'], 1,
                'Câu điều kiện loại 1: If + hiện tại đơn, will + V.');
            $this->quiz($L, 'Choose the correct answer: If she ___ hard, she will pass the exam. (Nếu cô ấy học chăm, cô ấy sẽ đỗ.)',
                ['study', 'studies', 'will study', 'studied'], 1,
                'Mệnh đề If của loại 1 dùng hiện tại đơn; chủ ngữ "she" → "studies".');
            $this->quiz($L, 'What does a type 1 conditional express? (Câu điều kiện loại 1 diễn tả điều gì?)',
                ['A real possibility in the present or future (điều có thể xảy ra)',
                 'An impossible dream (điều không thể)',
                 'A past regret (điều hối tiếc trong quá khứ)',
                 'A general truth only (chỉ sự thật hiển nhiên)'],
                0, 'Loại 1: điều kiện có thể xảy ra ở hiện tại/tương lai.');
            $this->quiz($L, 'Choose the correct punctuation: If you are tired ___ go to bed.',
                ['___ , you should', '___ you should', '___ . you should', '___ ; you should'], 0,
                'Khi mệnh đề If đứng trước, dùng dấu phẩy ngăn cách hai mệnh đề.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each part to complete the conditional. (Nối để hoàn thành câu điều kiện.)',
                [['If it rains,', 'we will stay home.'],
                 ['If she studies,', 'she will pass.'],
                 ['We will go out,', 'if the weather is nice.'],
                 ['He will be late,', 'if he does not hurry.']],
                'If + present simple, will + V. Mệnh đề If đứng trước hay sau đều được.');
            $this->matching($L, 'Match each conditional type with its form. (Nối loại câu với cấu trúc.)',
                [['Type 0', 'If + present simple, present simple'],
                 ['Type 1', 'If + present simple, will + V'],
                 ['Real possibility', 'Type 1'],
                 ['General truth', 'Type 0']],
                'Type 0: sự thật hiển nhiên. Type 1: điều có thể xảy ra.');
            $this->matching($L, 'Match each sentence with its meaning. (Nối câu với ý nghĩa.)',
                [['If you heat ice, it melts.', 'General truth (type 0)'],
                 ['If you help me, I will thank you.', 'Possible future (type 1)'],
                 ['If she calls, tell her I am out.', 'Possible future (type 1)'],
                 ['If water is cold, it freezes.', 'General truth (type 0)']],
                'Phân biệt type 0 (sự thật) và type 1 (khả năng tương lai).');
            $this->matching($L, 'Match each clause with its name. (Nối mệnh đề với tên gọi.)',
                [['If it rains', 'If-clause (mệnh đề điều kiện)'],
                 ['we will stay', 'Main clause (mệnh đề chính)'],
                 ['If she is free', 'If-clause'],
                 ['she will come', 'Main clause']],
                'Câu điều kiện gồm mệnh đề If và mệnh đề chính.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each sentence into TYPE 0 or TYPE 1.',
                [['If you heat water, it boils.', 'Type 0'],
                 ['If you press the button, the light turns on.', 'Type 0'],
                 ['If it rains, we will cancel.', 'Type 1'],
                 ['If she comes, I will be happy.', 'Type 1'],
                 ['If you mix red and blue, you get purple.', 'Type 0'],
                 ['If he studies, he will pass.', 'Type 1']],
                'Type 0: sự thật hiển nhiên (hiện tại đơn cả hai vế). Type 1: will ở mệnh đề chính.');
            $this->sortQ($L, 'Drag each verb form into IF-CLAUSE or MAIN CLAUSE of type 1.',
                [['rains', 'If-clause'],
                 ['studies', 'If-clause'],
                 ['is', 'If-clause'],
                 ['will stay', 'Main clause'],
                 ['will pass', 'Main clause'],
                 ['will be', 'Main clause']],
                'Type 1: If + hiện tại đơn; mệnh đề chính: will + V.');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT type 1.',
                [['If it rains, we will stay.', 'Correct'],
                 ['If she comes, I will meet her.', 'Correct'],
                 ['If it will rain, we stay.', 'Incorrect'],
                 ['If she will come, I meet her.', 'Incorrect'],
                 ['We will go if the sun shines.', 'Correct'],
                 ['If they will help, we thank them.', 'Incorrect']],
                'Mệnh đề If của loại 1 KHÔNG dùng "will".');
            $this->sortQ($L, 'Drag each part into BEFORE COMMA or AFTER COMMA.',
                [['If you are tired', 'Before comma'],
                 ['if it rains', 'Before comma'],
                 ['you should rest.', 'After comma'],
                 ['we will stay home.', 'After comma'],
                 ['If she is free', 'Before comma'],
                 ['she will join us.', 'After comma']],
                'Mệnh đề If đứng trước → có dấu phẩy; đứng sau → không cần dấu phẩy.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'If it rains, we ___ (stay) at home.', [[0, 'will stay']],
                'Type 1: If + hiện tại đơn, will + V.');
            $this->fill($L, 'If she ___ (study) hard, she will pass.', [[0, 'studies']],
                'Mệnh đề If dùng hiện tại đơn; "she" → "studies".');
            $this->fill($L, 'We will go out if the weather ___ (be) nice.', [[0, 'is']],
                'Mệnh đề If đứng sau, vẫn dùng hiện tại đơn.');
            $this->fill($L, 'Type 1: If + present simple, ___ + V (bare infinitive).', [[0, 'will']],
                'Công thức: If + S + V(s/es), S + will + V.');
        }
    }

    private function seedEn10Dk1b(): void
    {
        $L = 'tieng-anh-thpt-10-cau-dieu-kien-1-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the correct answer: If you ___ me, I will help you. (Nếu bạn tin tôi, tôi sẽ giúp bạn.)',
                ['trust', 'trusts', 'will trust', 'trusted'], 0,
                'Chủ ngữ "you" → động từ nguyên mẫu "trust" trong mệnh đề If.');
            $this->quiz($L, 'Choose the correct answer: She will be angry if he ___ late. (Cô ấy sẽ giận nếu anh ấy đến muộn.)',
                ['is', 'will be', 'was', 'be'], 0,
                'Mệnh đề If dùng hiện tại đơn: "is late".');
            $this->quiz($L, 'Choose the correct answer: If we do not hurry, we ___ the bus. (Nếu không nhanh, chúng ta sẽ lỡ xe.)',
                ['miss', 'will miss', 'missed', 'missing'], 1,
                'Mệnh đề chính của loại 1: will + V → "will miss".');
            $this->quiz($L, 'Which sentence is a correct type 1 conditional?',
                ['If she has time, she will visit us.',
                 'If she will have time, she visits us.',
                 'If she had time, she will visit us.',
                 'If she has time, she visits us tomorrow.'],
                0, 'Đúng cấu trúc: If + hiện tại đơn, will + V. Câu cuối là type 0 nhưng "tomorrow" không hợp.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match to complete the type 1 sentences. (Nối để hoàn thành câu.)',
                [['If you eat too much,', 'you will feel sick.'],
                 ['If he does not sleep,', 'he will be tired.'],
                 ['She will call you,', 'if she arrives early.'],
                 ['They will win,', 'if they try their best.']],
                'Luyện đặt câu điều kiện loại 1 với tình huống quen thuộc.');
            $this->matching($L, 'Match each Vietnamese meaning with the English sentence.',
                [['Nếu bạn cố gắng, bạn sẽ thành công.', 'If you try, you will succeed.'],
                 ['Nếu trời đẹp, chúng ta sẽ đi chơi.', 'If the weather is nice, we will go out.'],
                 ['Nếu anh ấy đến, hãy gọi tôi.', 'If he comes, call me.'],
                 ['Nếu em đói, hãy ăn cơm.', 'If you are hungry, eat rice.']],
                'Dịch câu điều kiện loại 1 sang tiếng Anh.');
            $this->matching($L, 'Match each situation with the right advice (type 1).',
                [['You feel tired.', 'If you are tired, you should rest.'],
                 ['It is raining.', 'If it rains, take an umbrella.'],
                 ['She is hungry.', 'If she is hungry, she will eat.'],
                 ['He has a test.', 'If he studies, he will pass.']],
                'Câu điều kiện loại 1 dùng để đưa lời khuyên trong tình huống thực tế.');
            $this->matching($L, 'Match each error with its correction.',
                [['If it will rain,', 'If it rains,'],
                 ['she will comes,', 'she will come,'],
                 ['If he studys,', 'If he studies,'],
                 ['we will stayed,', 'we will stay,']],
                'Lỗi thường gặp: dùng will trong mệnh đề If; chia sai động từ sau will.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each sentence into TYPE 1 or NOT TYPE 1.',
                [['If you run, you will catch the bus.', 'Type 1'],
                 ['If she is sick, she will see a doctor.', 'Type 1'],
                 ['If I were you, I would go.', 'Not type 1 (type 2)'],
                 ['If he had studied, he would have passed.', 'Not type 1 (type 3)'],
                 ['If they invite us, we will join.', 'Type 1'],
                 ['If water boils, it evaporates.', 'Not type 1 (type 0)']],
                'Type 1 có "will" ở mệnh đề chính và hiện tại đơn ở mệnh đề If.');
            $this->sortQ($L, 'Drag each verb into the correct blank: "If she ___ (come), we ___ (be) happy."',
                [['comes', 'If-clause'],
                 ['will be', 'Main clause'],
                 ['come', 'Wrong form'],
                 ['is', 'Wrong meaning'],
                 ['will come', 'Wrong (will in If)'],
                 ['be', 'Wrong form']],
                'If + hiện tại đơn (comes), mệnh đề chính will + V (will be).');
            $this->sortQ($L, 'Drag each sentence into WITH COMMA or WITHOUT COMMA.',
                [['If it rains, we stay home.', 'With comma'],
                 ['If she calls, tell me.', 'With comma'],
                 ['We stay home if it rains.', 'Without comma'],
                 ['Tell me if she calls.', 'Without comma'],
                 ['If you try, you will win.', 'With comma'],
                 ['You will win if you try.', 'Without comma']],
                'Mệnh đề If đứng trước → có dấu phẩy; đứng sau → không.');
            $this->sortQ($L, 'Drag each phrase into REAL POSSIBILITY (type 1) or GENERAL TRUTH (type 0).',
                [['If you drop your phone, it may break.', 'Real possibility'],
                 ['If she saves money, she will travel.', 'Real possibility'],
                 ['If you freeze water, it becomes ice.', 'General truth'],
                 ['If you do not water plants, they die.', 'General truth'],
                 ['If he apologizes, she will forgive him.', 'Real possibility'],
                 ['If you add sugar, tea gets sweet.', 'General truth']],
                'Khả năng có thể xảy ra → type 1; sự thật hiển nhiên → type 0.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'If you ___ (try) your best, you will succeed.', [[0, 'try']],
                'Chủ ngữ "you" → động từ nguyên mẫu trong mệnh đề If.');
            $this->fill($L, 'She will call if she ___ (arrive) early.', [[0, 'arrives']],
                'Mệnh đề If: hiện tại đơn, "she" → "arrives".');
            $this->fill($L, 'If they ___ (not/hurry), they will miss the train.', [[0, 'do not hurry']],
                'Phủ định mệnh đề If: do not + V.');
            $this->fill($L, 'If he apologizes, she ___ (forgive) him.', [[0, 'will forgive']],
                'Mệnh đề chính: will + V nguyên mẫu.');
        }
    }

    // ================= TIẾNG ANH 11 =================

    private function seedEn11BiDong1(): void
    {
        $L = 'tieng-anh-thpt-11-cau-bi-dong-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the passive: They make shoes in this factory. (Họ sản xuất giày ở nhà máy này.)',
                ['Shoes are made in this factory.',
                 'Shoes is made in this factory.',
                 'Shoes were made in this factory.',
                 'Shoes are make in this factory.'],
                0, 'Hiện tại đơn bị động: S + am/is/are + V-ed. "Shoes" số nhiều → "are made".');
            $this->quiz($L, 'Choose the passive: She wrote a letter yesterday. (Hôm qua cô ấy đã viết một lá thư.)',
                ['A letter was written yesterday.',
                 'A letter is written yesterday.',
                 'A letter were written yesterday.',
                 'A letter was wrote yesterday.'],
                0, 'Quá khứ đơn bị động: was/were + V-ed. "Write" → "written" (bất quy tắc).');
            $this->quiz($L, 'Choose the passive: They will build a bridge. (Họ sẽ xây một cây cầu.)',
                ['A bridge will be built.',
                 'A bridge will build.',
                 'A bridge is built.',
                 'A bridge will be build.'],
                0, 'Tương lai đơn bị động: will + be + V-ed. "Build" → "built".');
            $this->quiz($L, 'Choose the passive: She is cleaning the room now. (Bây giờ cô ấy đang dọn phòng.)',
                ['The room is being cleaned now.',
                 'The room is cleaned now.',
                 'The room was being cleaned now.',
                 'The room are being cleaned now.'],
                0, 'Hiện tại tiếp diễn bị động: am/is/are + being + V-ed.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each active sentence with its passive form. (Nối câu chủ động với bị động.)',
                [['They grow rice.', 'Rice is grown.'],
                 ['He broke the window.', 'The window was broken.'],
                 ['We will paint the house.', 'The house will be painted.'],
                 ['She is washing dishes.', 'Dishes are being washed.']],
                'Tân ngữ câu chủ động → chủ ngữ câu bị động; chia "be" theo thì.');
            $this->matching($L, 'Match each tense with its passive form. (Nối thì với dạng bị động.)',
                [['Present simple', 'am/is/are + V-ed'],
                 ['Past simple', 'was/were + V-ed'],
                 ['Future simple', 'will be + V-ed'],
                 ['Present continuous', 'am/is/are + being + V-ed']],
                'Công thức bị động: be (chia theo thì) + V-ed/V3.');
            $this->matching($L, 'Match each verb with its past participle. (Nối động từ với phân từ 2.)',
                [['make', 'made'],
                 ['write', 'written'],
                 ['break', 'broken'],
                 ['build', 'built']],
                'Phân từ 2 của động từ bất quy tắc cần học thuộc.');
            $this->matching($L, 'Match each sentence with the doer (agent). (Nối câu với tác nhân.)',
                [['The cake was baked by my mother.', 'my mother'],
                 ['The room was cleaned by him.', 'him'],
                 ['English is spoken by many people.', 'many people'],
                 ['The song was sung by her.', 'her']],
                'Tác nhân đứng sau "by" trong câu bị động; có thể lược bỏ nếu không quan trọng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each sentence into ACTIVE or PASSIVE.',
                [['They make cars.', 'Active'],
                 ['Cars are made.', 'Passive'],
                 ['She wrote a poem.', 'Active'],
                 ['A poem was written.', 'Passive'],
                 ['He will fix the bike.', 'Active'],
                 ['The bike will be fixed.', 'Passive']],
                'Active: S + V + O. Passive: S + be + V-ed (+ by O).');
            $this->sortQ($L, 'Drag each passive form into the correct TENSE.',
                [['is cleaned', 'Present simple'],
                 ['was cleaned', 'Past simple'],
                 ['will be cleaned', 'Future simple'],
                 ['are cleaned', 'Present simple'],
                 ['were cleaned', 'Past simple'],
                 ['is being cleaned', 'Present continuous']],
                'Dạng của "be" cho biết thì của câu bị động.');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT passive.',
                [['The room is cleaned daily.', 'Correct'],
                 ['The letter was sent yesterday.', 'Correct'],
                 ['The room is clean daily.', 'Incorrect'],
                 ['The letter was sended yesterday.', 'Incorrect'],
                 ['Rice is grown in Asia.', 'Correct'],
                 ['Rice is grow in Asia.', 'Incorrect']],
                'Bị động cần "be + V-ed/V3", không phải tính từ hay V nguyên mẫu.');
            $this->sortQ($L, 'Drag each verb into REGULAR or IRREGULAR participle.',
                [['cleaned', 'Regular'],
                 ['painted', 'Regular'],
                 ['washed', 'Regular'],
                 ['written', 'Irregular'],
                 ['broken', 'Irregular'],
                 ['grown', 'Irregular']],
                'Phân từ 2: có quy tắc thêm -ed; bất quy tắc biến đổi đặc biệt.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Rice ___ (grow) in the Mekong Delta.', [[0, 'is grown']],
                'Hiện tại đơn bị động: is + grown (chủ ngữ "rice" không đếm được → is).');
            $this->fill($L, 'The window ___ (break) last night.', [[0, 'was broken']],
                'Quá khứ đơn bị động: was + broken.');
            $this->fill($L, 'A new school ___ (build) next year.', [[0, 'will be built']],
                'Tương lai đơn bị động: will be + built.');
            $this->fill($L, 'The floor ___ (clean) now. Please wait.', [[0, 'is being cleaned']],
                'Hiện tại tiếp diễn bị động: is being + cleaned.');
        }
    }

    private function seedEn11BiDong2(): void
    {
        $L = 'tieng-anh-thpt-11-cau-bi-dong-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the passive: You must clean the room. (Bạn phải dọn phòng.)',
                ['The room must be cleaned.',
                 'The room must cleaned.',
                 'The room must be clean.',
                 'The room must being cleaned.'],
                0, 'Bị động với modal: S + modal + be + V-ed.', 'kho');
            $this->quiz($L, 'Choose the passive: They have built a new bridge. (Họ đã xây cầu mới.)',
                ['A new bridge has been built.',
                 'A new bridge has built.',
                 'A new bridge have been built.',
                 'A new bridge has been build.'],
                0, 'Hiện tại hoàn thành bị động: have/has + been + V-ed.', 'kho');
            $this->quiz($L, 'Choose the passive: She gave me a gift. (Cô ấy đã tặng tôi một món quà.)',
                ['I was given a gift. / A gift was given to me.',
                 'I was gave a gift.',
                 'A gift was gave to me.',
                 'I gave a gift.'],
                0, 'Câu có hai tân ngữ: đưa tân ngữ gián tiếp ("me" → "I") hoặc trực tiếp lên làm chủ ngữ.', 'kho');
            $this->quiz($L, 'Which sentence is correct?',
                ['The work should be finished today.',
                 'The work should finished today.',
                 'The work should be finish today.',
                 'The work should being finished today.'],
                0, 'Modal + be + V-ed: "should be finished".', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each modal active with its passive. (Nối câu modal với bị động.)',
                [['You must do homework.', 'Homework must be done.'],
                 ['We should protect forests.', 'Forests should be protected.'],
                 ['You can use this pen.', 'This pen can be used.'],
                 ['They may cancel the trip.', 'The trip may be cancelled.']],
                'Modal passive: S + modal + be + V-ed.', 'kho');
            $this->matching($L, 'Match each tense with its passive form (advanced).',
                [['Modal (must/can/should)', 'modal + be + V-ed'],
                 ['Present perfect', 'have/has + been + V-ed'],
                 ['Past perfect', 'had + been + V-ed'],
                 ['Be going to', 'am/is/are + going to + be + V-ed']],
                'Dạng bị động nâng cao: modal, hoàn thành, be going to.', 'kho');
            $this->matching($L, 'Match each verb with its past participle.',
                [['do', 'done'],
                 ['give', 'given'],
                 ['take', 'taken'],
                 ['speak', 'spoken']],
                'Phân từ 2 bất quy tắc thường gặp.', 'kho');
            $this->matching($L, 'Match each double-object verb with its two objects.',
                [['give me a book', 'me (indirect) + a book (direct)'],
                 ['send her a letter', 'her (indirect) + a letter (direct)'],
                 ['buy him a pen', 'him (indirect) + a pen (direct)'],
                 ['tell us a story', 'us (indirect) + a story (direct)']],
                'Động từ hai tân ngữ: tân ngữ gián tiếp (người) + tân ngữ trực tiếp (vật).', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each passive into MODAL or PERFECT.',
                [['must be cleaned', 'Modal'],
                 ['should be done', 'Modal'],
                 ['can be used', 'Modal'],
                 ['has been built', 'Perfect'],
                 ['have been sold', 'Perfect'],
                 ['had been written', 'Perfect']],
                'Modal + be + V-ed; have/has/had + been + V-ed.', 'kho');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT.',
                [['Homework must be done.', 'Correct'],
                 ['The bridge has been built.', 'Correct'],
                 ['Homework must done.', 'Incorrect'],
                 ['The bridge has built.', 'Incorrect'],
                 ['This can be repaired.', 'Correct'],
                 ['This can repaired.', 'Incorrect']],
                'Sau modal và sau "been" phải là V-ed/V3.', 'kho');
            $this->sortQ($L, 'Drag each passive into WITH AGENT or WITHOUT AGENT (agent can be omitted).',
                [['was written by To Huu', 'With agent'],
                 ['was built in 2020', 'Without agent'],
                 ['is spoken in many countries', 'Without agent'],
                 ['was painted by my father', 'With agent'],
                 ['were made in Vietnam', 'Without agent'],
                 ['was discovered by scientists', 'With agent']],
                'Lược bỏ "by + tác nhân" khi tác nhân không quan trọng hoặc hiển nhiên.', 'kho');
            $this->sortQ($L, 'Drag each structure into PASSIVE or NOT PASSIVE.',
                [['be + V-ed', 'Passive'],
                 ['modal + be + V-ed', 'Passive'],
                 ['have + been + V-ed', 'Passive'],
                 ['be + V-ing', 'Not passive (continuous active)'],
                 ['be + adjective', 'Not passive'],
                 ['V + O', 'Not passive (active)']],
                'Dấu hiệu bị động: be/modal/have + (been) + V-ed/V3.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'The room must ___ (clean) before 8 a.m.', [[0, 'be cleaned']],
                'Modal passive: must + be + cleaned.', 'kho');
            $this->fill($L, 'A new hospital has ___ (build) in our town.', [[0, 'been built']],
                'Present perfect passive: has + been + built.', 'kho');
            $this->fill($L, 'I was ___ (give) a nice present.', [[0, 'given']],
                'Bị động hai tân ngữ: I was given a present.', 'kho');
            $this->fill($L, 'The road is going to ___ (repair) next month.', [[0, 'be repaired']],
                'Be going to passive: be going to + be + V-ed.', 'kho');
        }
    }

    private function seedEn11TuongThuat1(): void
    {
        $L = 'tieng-anh-thpt-11-cau-tuong-thuat-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the reported speech: She said, "I am tired." (Cô ấy nói: "Tôi mệt.")',
                ['She said (that) she was tired.',
                 'She said (that) she is tired.',
                 'She said (that) she had been tired.',
                 'She says (that) she was tired.'],
                0, 'Tường thuật: lùi thì hiện tại đơn → quá khứ đơn; "I" → "she".');
            $this->quiz($L, 'Choose the reported speech: He said, "I will come tomorrow." (Anh ấy nói: "Ngày mai tôi sẽ đến.")',
                ['He said (that) he would come the next day.',
                 'He said (that) he will come tomorrow.',
                 'He said (that) he comes the next day.',
                 'He says (that) he would come tomorrow.'],
                0, 'Will → would; tomorrow → the next day; "I" → "he".');
            $this->quiz($L, 'Choose the reported question: She asked, "Are you free?" (Cô ấy hỏi: "Bạn có rảnh không?")',
                ['She asked if I was free.',
                 'She asked are you free.',
                 'She asked that I was free.',
                 'She asked if I am free.'],
                0, 'Câu hỏi Yes/No → asked + if/whether + S + V (lùi thì, không đảo ngữ).');
            $this->quiz($L, 'Choose the reported question: He asked, "Where do you live?" (Anh ấy hỏi: "Bạn sống ở đâu?")',
                ['He asked where I lived.',
                 'He asked where do I live.',
                 'He asked where I live.',
                 'He asked that where I lived.'],
                0, 'Câu hỏi Wh- → asked + từ để hỏi + S + V (lùi thì, không đảo ngữ).');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each direct speech with its reported form. (Nối câu trực tiếp với tường thuật.)',
                [['"I like tea," she said.', 'She said she liked tea.'],
                 ['"I am busy," he said.', 'He said he was busy.'],
                 ['"We won," they said.', 'They said they had won.'],
                 ['"I can swim," she said.', 'She said she could swim.']],
                'Lùi thì: hiện tại → quá khứ; quá khứ → quá khứ hoàn thành; can → could.');
            $this->matching($L, 'Match each tense with its backshift. (Nối thì với dạng lùi thì.)',
                [['Present simple', 'Past simple'],
                 ['Present continuous', 'Past continuous'],
                 ['Past simple', 'Past perfect'],
                 ['Will', 'Would']],
                'Quy tắc lùi thì (backshift) trong câu tường thuật.');
            $this->matching($L, 'Match each time word with its reported form. (Nối trạng từ với dạng tường thuật.)',
                [['now', 'then'],
                 ['today', 'that day'],
                 ['tomorrow', 'the next day'],
                 ['here', 'there']],
                'Đổi trạng từ chỉ thời gian, nơi chốn khi tường thuật.');
            $this->matching($L, 'Match each question type with its reported form.',
                [['Yes/No question', 'asked + if/whether + S + V'],
                 ['Wh-question', 'asked + wh-word + S + V'],
                 ['Statement', 'said/told + (that) + S + V'],
                 ['"Do you like it?"', 'asked if I liked it']],
                'Câu hỏi tường thuật không đảo ngữ, không dùng dấu hỏi.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each pair into CORRECT or INCORRECT backshift.',
                [['am → was', 'Correct'],
                 ['will → would', 'Correct'],
                 ['can → could', 'Correct'],
                 ['am → is', 'Incorrect'],
                 ['will → will', 'Incorrect'],
                 ['can → can', 'Incorrect']],
                'Lùi thì: am → was, will → would, can → could.');
            $this->sortQ($L, 'Drag each reported sentence into STATEMENT or QUESTION.',
                [['She said she was tired.', 'Statement'],
                 ['He told me he liked music.', 'Statement'],
                 ['She asked if I was free.', 'Question'],
                 ['He asked where I lived.', 'Question'],
                 ['They said they had won.', 'Statement'],
                 ['She asked whether he came.', 'Question']],
                'Statement: said/told + that. Question: asked + if/whether/wh-word.');
            $this->sortQ($L, 'Drag each change into NEEDED or NOT NEEDED in reported speech.',
                [['I → she (đổi đại từ)', 'Needed'],
                 ['now → then', 'Needed'],
                 ['am → was (lùi thì)', 'Needed'],
                 ['giữ nguyên dấu hỏi', 'Not needed'],
                 ['giữ đảo ngữ câu hỏi', 'Not needed'],
                 ['said → says (đổi thì tường thuật)', 'Not needed']],
                'Tường thuật: đổi đại từ, lùi thì, đổi trạng từ; bỏ đảo ngữ và dấu hỏi.');
            $this->sortQ($L, 'Drag each verb into SAID or TOLD pattern.',
                [['She said she was busy.', 'Said'],
                 ['She said to me she was busy.', 'Said'],
                 ['She told me she was busy.', 'Told'],
                 ['He told her the news.', 'Told'],
                 ['He said he was late.', 'Said'],
                 ['They told us a story.', 'Told']],
                '"Said" không có tân ngữ trực tiếp (hoặc said to + O); "told" + tân ngữ người.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'She said, "I am happy." → She said she ___ happy.', [[0, 'was']],
                'Lùi thì: am → was.');
            $this->fill($L, '"I will help," he said. → He said he ___ help.', [[0, 'would']],
                'Will → would trong tường thuật.');
            $this->fill($L, 'She asked, "Are you tired?" → She asked ___ I was tired.', [[0, 'if']],
                'Câu hỏi Yes/No → if/whether.');
            $this->fill($L, 'He asked, "Where do you live?" → He asked where I ___.', [[0, 'lived']],
                'Wh-question: asked + where + S + V lùi thì; không đảo ngữ.');
        }
    }

    private function seedEn11TuongThuat2(): void
    {
        $L = 'tieng-anh-thpt-11-cau-tuong-thuat-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the reported speech: She said, "Close the door!" (Cô ấy nói: "Đóng cửa lại!")',
                ['She told me to close the door.',
                 'She told me close the door.',
                 'She said me to close the door.',
                 'She told to me close the door.'],
                0, 'Mệnh lệnh tường thuật: told/asked + O + to-V.', 'kho');
            $this->quiz($L, 'Choose the reported speech: He said, "Do not touch it!" (Anh ấy nói: "Đừng chạm vào!")',
                ['He told me not to touch it.',
                 'He told me to not touch it.',
                 'He told me do not touch it.',
                 'He said me not to touch it.'],
                0, 'Mệnh lệnh phủ định: told + O + not to-V.', 'kho');
            $this->quiz($L, 'Choose the reported speech: She said, "If I were rich, I would travel." (Câu điều kiện loại 2)',
                ['She said if she were rich, she would travel. (giữ nguyên)',
                 'She said if she had been rich, she would have travelled.',
                 'She said if she is rich, she will travel.',
                 'She says if she were rich, she would travel.'],
                0, 'Câu điều kiện loại 2, 3 khi tường thuật giữ nguyên thì.', 'kho');
            $this->quiz($L, 'Choose the reported request: "Please help me," she said. (Cô ấy nói: "Làm ơn giúp tôi.")',
                ['She asked me to help her.',
                 'She asked me help her.',
                 'She said me to help her.',
                 'She asked to me help her.'],
                0, 'Yêu cầu lịch sự: asked + O + to-V; "me" → "her".', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each command with its reported form. (Nối mệnh lệnh với tường thuật.)',
                [['"Open the window," she said.', 'She told me to open the window.'],
                 ['"Do not run," he said.', 'He told me not to run.'],
                 ['"Please sit down," she said.', 'She asked me to sit down.'],
                 ['"Be quiet," he said.', 'He told me to be quiet.']],
                'Mệnh lệnh: told/asked + O + (not) to-V.', 'kho');
            $this->matching($L, 'Match each reporting verb with its pattern.',
                [['told', 'told + O + to-V'],
                 ['asked', 'asked + O + to-V'],
                 ['advised', 'advised + O + to-V'],
                 ['ordered', 'ordered + O + to-V']],
                'Các động từ tường thuật mệnh lệnh đều theo mẫu V + O + to-V.', 'kho');
            $this->matching($L, 'Match each conditional with its reported form.',
                [['Type 1: "If it rains, I will stay."', 'He said if it rained, he would stay.'],
                 ['Type 2: "If I were you, I would go."', 'She said if she were me, she would go. (giữ nguyên)'],
                 ['Type 3: "If I had known, I would have come."', 'He said if he had known, he would have come. (giữ nguyên)'],
                 ['"Study hard," she said.', 'She told me to study hard.']],
                'Type 1 lùi thì; type 2, 3 giữ nguyên.', 'kho');
            $this->matching($L, 'Match each pronoun change.',
                [['I (she speaking)', 'she'],
                 ['my (she speaking)', 'her'],
                 ['me (she speaking to me)', 'her'],
                 ['we (they speaking)', 'they']],
                'Đổi đại từ theo người nói trong câu tường thuật.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each reported form into COMMAND or STATEMENT.',
                [['She told me to go.', 'Command'],
                 ['He asked me to wait.', 'Command'],
                 ['She said she was tired.', 'Statement'],
                 ['He said he would come.', 'Statement'],
                 ['They ordered us to stop.', 'Command'],
                 ['She told me she liked tea.', 'Statement']],
                'Command: told/asked + O + to-V. Statement: said + that-clause.', 'kho');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT reported command.',
                [['She told me to open the door.', 'Correct'],
                 ['He asked her to help him.', 'Correct'],
                 ['She told me open the door.', 'Incorrect'],
                 ['He asked me helping him.', 'Incorrect'],
                 ['They told us not to smoke.', 'Correct'],
                 ['She said me to go.', 'Incorrect']],
                'Mệnh lệnh tường thuật: told/asked + O + to-V.', 'kho');
            $this->sortQ($L, 'Drag each conditional into BACKSHIFT or KEEP THE SAME.',
                [['Type 1', 'Backshift'],
                 ['Type 2', 'Keep the same'],
                 ['Type 3', 'Keep the same'],
                 ['"If I have time, I will help."', 'Backshift'],
                 ['"If I were rich, I would travel."', 'Keep the same'],
                 ['"If I had known, I would have called."', 'Keep the same']],
                'Chỉ type 1 lùi thì; type 2, 3 giữ nguyên.', 'kho');
            $this->sortQ($L, 'Drag each reporting verb into CORRECT PATTERN or WRONG.',
                [['told me to go', 'Correct'],
                 ['asked him to wait', 'Correct'],
                 ['told to me go', 'Wrong'],
                 ['said me to go', 'Wrong'],
                 ['advised her to study', 'Correct'],
                 ['asked to him wait', 'Wrong']],
                'Mẫu đúng: told/asked/advised + O (người) + to-V.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Close the door," she said. → She told me ___ close the door.', [[0, 'to']],
                'Told + O + to-V.', 'kho');
            $this->fill($L, '"Do not smoke," he said. → He told me ___ to smoke.', [[0, 'not']],
                'Phủ định: told + O + not to-V.', 'kho');
            $this->fill($L, 'Type 2 conditional in reported speech ___ the same (keep/change).', [[0, 'keeps']],
                'Câu điều kiện loại 2, 3 giữ nguyên thì khi tường thuật.', 'kho');
            $this->fill($L, '"Please wait," she said. → She ___ me to wait.', [[0, 'asked']],
                'Yêu cầu lịch sự dùng "asked".', 'kho');
        }
    }

    // ================= TIẾNG ANH 12 =================

    private function seedEn12Qh1(): void
    {
        $L = 'tieng-anh-thpt-12-menh-de-quan-he-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the reduced form: The boy who plays football is my friend. (Cậu bé đang đá bóng là bạn tôi.)',
                ['The boy playing football is my friend.',
                 'The boy played football is my friend.',
                 'The boy to play football is my friend.',
                 'The boy plays football is my friend.'],
                0, 'Mệnh đề quan hệ chủ động rút gọn bằng V-ing: who plays → playing.', 'kho');
            $this->quiz($L, 'Choose the reduced form: The car which was made in Japan is expensive. (Chiếc xe sản xuất tại Nhật rất đắt.)',
                ['The car made in Japan is expensive.',
                 'The car making in Japan is expensive.',
                 'The car to make in Japan is expensive.',
                 'The car makes in Japan is expensive.'],
                0, 'Mệnh đề quan hệ bị động rút gọn bằng V-ed: which was made → made.', 'kho');
            $this->quiz($L, 'Choose the reduced form: She is the girl who is talking to Nam. (Cô ấy là cô gái đang nói chuyện với Nam.)',
                ['She is the girl talking to Nam.',
                 'She is the girl talked to Nam.',
                 'She is the girl to talk to Nam.',
                 'She is the girl talks to Nam.'],
                0, 'Chủ động ở thì tiếp diễn vẫn rút gọn bằng V-ing: who is talking → talking.', 'kho');
            $this->quiz($L, 'When can we reduce a relative clause with V-ing?',
                ['When it is active (who/which + V) – Khi mệnh đề ở dạng chủ động',
                 'When it is passive – Khi mệnh đề ở dạng bị động',
                 'When it has a modal verb – Khi có động từ khuyết thiếu',
                 'Never – Không bao giờ'],
                0, 'V-ing dùng cho mệnh đề quan hệ chủ động; V-ed dùng cho bị động.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each relative clause with its reduced form. (Nối mệnh đề với dạng rút gọn.)',
                [['who plays tennis', 'playing tennis'],
                 ['which was built in 2000', 'built in 2000'],
                 ['who is waiting outside', 'waiting outside'],
                 ['which were made of wood', 'made of wood']],
                'Chủ động → V-ing; bị động (be + V-ed) → V-ed.', 'kho');
            $this->matching($L, 'Match each reduction type with the condition.',
                [['V-ing', 'Active relative clause'],
                 ['V-ed', 'Passive relative clause'],
                 ['to-V', 'After ordinal numbers, superlatives'],
                 ['No reduction', 'Non-defining clause with comma + to-V']],
                'Ba cách rút gọn và điều kiện sử dụng.', 'kho');
            $this->matching($L, 'Match each sentence with its full form.',
                [['The man standing there is my uncle.', 'The man who is standing there...'],
                 ['The book bought yesterday is interesting.', 'The book which was bought yesterday...'],
                 ['Students cheating will be punished.', 'Students who cheat will be punished.'],
                 ['The house painted white is mine.', 'The house which was painted white...']],
                'Dạng rút gọn tương ứng với mệnh đề đầy đủ.', 'kho');
            $this->matching($L, 'Match each participle with its use.',
                [['playing', 'Active reduction (V-ing)'],
                 ['played', 'Passive reduction (V-ed)'],
                 ['broken', 'Passive reduction (V-ed)'],
                 ['running', 'Active reduction (V-ing)']],
                'V-ing: chủ động; V-ed: bị động.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each reduced clause into V-ING (active) or V-ED (passive).',
                [['playing football', 'V-ing'],
                 ['talking loudly', 'V-ing'],
                 ['waiting outside', 'V-ing'],
                 ['made in Japan', 'V-ed'],
                 ['written in 2020', 'V-ed'],
                 ['broken into pieces', 'V-ed']],
                'V-ing: người/vật tự thực hiện hành động; V-ed: chịu tác động.', 'kho');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT reduction.',
                [['The boy playing chess is smart.', 'Correct'],
                 ['The cake baked by mom is delicious.', 'Correct'],
                 ['The boy played chess is smart.', 'Incorrect'],
                 ['The cake baking by mom is delicious.', 'Incorrect'],
                 ['Students wearing uniforms look nice.', 'Correct'],
                 ['Students worn uniforms look nice.', 'Incorrect']],
                'Chủ động phải dùng V-ing, bị động phải dùng V-ed.', 'kho');
            $this->sortQ($L, 'Drag each clause into REDUCIBLE or NOT REDUCIBLE with V-ing/V-ed.',
                [['who plays (xác định)', 'Reducible'],
                 ['which was made (xác định)', 'Reducible'],
                 ['My brother, who lives in Hanoi, (không xác định)', 'Reducible (V-ing vẫn được)'],
                 ['The reason why he left', 'Not (không có who/which + V)'],
                 ['whose bike is red', 'Not (whose không rút gọn)'],
                 ['whom I met', 'Not (thiếu chủ ngữ + V)']],
                'Chỉ rút gọn được mệnh đề có đại từ quan hệ làm chủ ngữ + động từ.', 'kho');
            $this->sortQ($L, 'Drag each noun phrase into ACTIVE MEANING or PASSIVE MEANING.',
                [['the boy playing', 'Active'],
                 ['the girl singing', 'Active'],
                 ['the car made', 'Passive'],
                 ['the letter written', 'Passive'],
                 ['the dog running', 'Active'],
                 ['the bridge built', 'Passive']],
                'V-ing: danh từ tự làm hành động; V-ed: danh từ chịu hành động.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'The girl who is singing is my sister. → The girl ___ is my sister.', [[0, 'singing']],
                'Chủ động → V-ing.', 'kho');
            $this->fill($L, 'The book which was written in 2020 is famous. → The book ___ in 2020 is famous.', [[0, 'written']],
                'Bị động → V-ed.', 'kho');
            $this->fill($L, 'Students who cheat will be punished. → Students ___ will be punished.', [[0, 'cheating']],
                'Chủ động → cheating.', 'kho');
            $this->fill($L, 'Reduce with V-ing when the relative clause is ___; with V-ed when it is passive.', [[0, 'active']],
                'Quy tắc rút gọn cơ bản.', 'kho');
        }
    }

    private function seedEn12Qh2(): void
    {
        $L = 'tieng-anh-thpt-12-menh-de-quan-he-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the reduced form: She was the first student to finish the test. (Cô ấy là học sinh đầu tiên làm xong bài.)',
                ['She was the first student finishing the test.',
                 'She was the first student finished the test.',
                 'She was the first student finish the test.',
                 'She was the first student to finishing the test.'],
                0, 'Sau số thứ tự (the first), mệnh đề quan hệ rút gọn bằng to-V — câu này đã ở dạng rút gọn đúng.', 'kho');
            $this->quiz($L, 'Choose the correct reduction: He is the best player to join the team. (full: ...who will join...)',
                ['Correct: to-V after superlative',
                 'Should be "joining"',
                 'Should be "joined"',
                 'Should be "joins"'],
                0, 'Sau so sánh nhất (the best) dùng to-V để rút gọn.', 'kho');
            $this->quiz($L, 'Which reduction is correct? The house to be built next year... (Ngôi nhà sẽ được xây năm sau...)',
                ['Correct: to be built (passive purpose)',
                 'Should be "building"',
                 'Should be "built"',
                 'Should be "build"'],
                0, 'Dạng bị động của to-V: to be + V-ed.', 'kho');
            $this->quiz($L, 'Choose the correct sentence:',
                ['English is the only subject to learn by heart. (sai – không rút gọn kiểu này)',
                 'She is the tallest girl standing in the corner.',
                 'He was the last person left the room.',
                 'This is the best film watched ever.'],
                1, '"The tallest girl standing..." rút gọn đúng bằng V-ing (who is standing). Các câu còn lại sai dạng rút gọn.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each situation with the correct reduction.',
                [['After "the first"', 'to-V: the first to come'],
                 ['After "the best"', 'to-V: the best to choose'],
                 ['After "the only"', 'to-V: the only to survive'],
                 ['Active meaning', 'V-ing: the boy playing']],
                'To-V dùng sau số thứ tự, so sánh nhất, "the only".', 'kho');
            $this->matching($L, 'Match each full clause with its to-V reduction.',
                [['who will come first', 'to come first'],
                 ['which is the best', 'to be the best (hiếm)'],
                 ['who should be chosen', 'to be chosen'],
                 ['who can help us', 'to help us (chỉ mục đích)']],
                'To-V thường rút gọn mệnh đề chỉ thứ tự, sự duy nhất, mục đích.', 'kho');
            $this->matching($L, 'Match each sentence with the reduction type used.',
                [['The man talking is my dad.', 'V-ing'],
                 ['The letter sent yesterday arrived.', 'V-ed'],
                 ['She was the last to leave.', 'to-V'],
                 ['He is the best to win.', 'to-V']],
                'Nhận diện ba dạng rút gọn trong câu.', 'kho');
            $this->matching($L, 'Match each error with its correction.',
                [['the first finishing', 'the first to finish'],
                 ['the best played', 'the best to play'],
                 ['the only swimming', 'the only to swim'],
                 ['the last broke', 'the last to break']],
                'Sau the first/best/only/last phải dùng to-V, không dùng V-ing/V-ed.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each phrase into TO-V or V-ING/V-ED reduction.',
                [['the first to come', 'To-V'],
                 ['the best to choose', 'To-V'],
                 ['the only to know', 'To-V'],
                 ['the boy playing', 'V-ing'],
                 ['the cake baked', 'V-ed'],
                 ['the last to leave', 'To-V']],
                'To-V sau số thứ tự/so sánh nhất/the only; V-ing/V-ed cho nghĩa chủ động/bị động.', 'kho');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT.',
                [['She was the first to arrive.', 'Correct'],
                 ['He is the best to win the prize.', 'Correct'],
                 ['She was the first arriving.', 'Incorrect'],
                 ['He is the best winning the prize.', 'Incorrect'],
                 ['They were the last to leave.', 'Correct'],
                 ['They were the last leaving.', 'Incorrect']],
                'Sau the first/best/last bắt buộc dùng to-V.', 'kho');
            $this->sortQ($L, 'Drag each clause into ACTIVE (V-ing/to-V) or PASSIVE (V-ed/to be V-ed).',
                [['to finish (chủ động)', 'Active'],
                 ['playing (chủ động)', 'Active'],
                 ['to be built (bị động)', 'Passive'],
                 ['written (bị động)', 'Passive'],
                 ['to help (chủ động)', 'Active'],
                 ['to be chosen (bị động)', 'Passive']],
                'To-V và V-ing mang nghĩa chủ động; to be + V-ed và V-ed mang nghĩa bị động.', 'kho');
            $this->sortQ($L, 'Drag each relative pronoun situation into CAN REDUCE WITH TO-V or CANNOT.',
                [['the first person who came', 'Can'],
                 ['the only girl who knows', 'Can'],
                 ['the best film which won', 'Can'],
                 ['my brother, who came, (có dấu phẩy)', 'Cannot (to-V)'],
                 ['the reason why he left', 'Cannot'],
                 ['the girl whose hat is red', 'Cannot']],
                'To-V không dùng cho mệnh đề không xác định (có dấu phẩy).', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'She was the first student ___ (finish) the exam.', [[0, 'to finish']],
                'Sau "the first" dùng to-V.', 'kho');
            $this->fill($L, 'He is the best player ___ (join) our team.', [[0, 'to join']],
                'Sau so sánh nhất dùng to-V.', 'kho');
            $this->fill($L, 'The bridge ___ (build) next year will be long.', [[0, 'to be built']],
                'Bị động tương lai: to be + built.', 'kho');
            $this->fill($L, 'After "the first", "the last", "the only", reduce with ___.', [[0, 'to-V']],
                'Quy tắc rút gọn với to-V.', 'kho');
        }
    }

    private function seedEn12DaoNgu1(): void
    {
        $L = 'tieng-anh-thpt-12-dao-ngu-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the correct inversion: Never ___ such a beautiful sight. (Tôi chưa bao giờ thấy cảnh đẹp như vậy.)',
                ['have I seen',
                 'I have seen',
                 'have I see',
                 'I have saw'],
                0, 'Trạng từ phủ định đứng đầu → đảo trợ động từ lên trước chủ ngữ: Never have I seen...', 'kho');
            $this->quiz($L, 'Choose the correct inversion: Rarely ___ to class late. (Cô ấy hiếm khi đi học muộn.)',
                ['does she go',
                 'she goes',
                 'does she goes',
                 'she does go'],
                0, 'Rarely đứng đầu → đảo: Rarely does she go... (does + V nguyên mẫu).', 'kho');
            $this->quiz($L, 'Choose the correct inversion: Hardly ___ when it started to rain. (Anh ấy vừa đến thì trời mưa.)',
                ['had he arrived',
                 'he had arrived',
                 'had he arrive',
                 'he has arrived'],
                0, 'Hardly + quá khứ hoàn thành đảo ngữ: Hardly had he arrived when...', 'kho');
            $this->quiz($L, 'What happens when a negative adverb comes first?',
                ['The auxiliary verb moves before the subject (đảo trợ động từ lên trước chủ ngữ)',
                 'Nothing changes (không có gì thay đổi)',
                 'The verb moves to the end (động từ ra cuối câu)',
                 'We add "not" twice (thêm "not" hai lần)'],
                0, 'Nguyên tắc đảo ngữ: trạng từ phủ định đầu câu → trợ động từ + chủ ngữ + động từ chính.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each normal sentence with its inversion. (Nối câu thường với đảo ngữ.)',
                [['I have never seen this.', 'Never have I seen this.'],
                 ['She rarely goes out.', 'Rarely does she go out.'],
                 ['He had hardly arrived when it rained.', 'Hardly had he arrived when it rained.'],
                 ['They seldom eat out.', 'Seldom do they eat out.']],
                'Mẫu đảo ngữ cơ bản với trạng từ phủ định.', 'kho');
            $this->matching($L, 'Match each adverb with its meaning. (Nối trạng từ với nghĩa.)',
                [['Never', 'Không bao giờ'],
                 ['Rarely', 'Hiếm khi'],
                 ['Hardly/Scarcely', 'Vừa mới... thì...'],
                 ['Seldom', 'Ít khi']],
                'Các trạng từ phủ định thường gây đảo ngữ.', 'kho');
            $this->matching($L, 'Match each structure with its pattern.',
                [['Never + inversion', 'Never + have/has + S + V-ed'],
                 ['Rarely + inversion', 'Rarely + do/does + S + V'],
                 ['Hardly + inversion', 'Hardly + had + S + V-ed + when'],
                 ['No sooner + inversion', 'No sooner + had + S + V-ed + than']],
                'Công thức đảo ngữ với từng trạng từ.', 'kho');
            $this->matching($L, 'Match each half to complete the inversion.',
                [['No sooner had she left', 'than it rained.'],
                 ['Hardly had he slept', 'when the phone rang.'],
                 ['Only when he grew up', 'did he understand.'],
                 ['Never before', 'had I felt so happy.']],
                'Hoàn thành câu đảo ngữ với vế còn lại.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each sentence into INVERSION or NORMAL ORDER.',
                [['Never have I seen this.', 'Inversion'],
                 ['Rarely does she come.', 'Inversion'],
                 ['I have never seen this.', 'Normal'],
                 ['She rarely comes.', 'Normal'],
                 ['Hardly had he arrived.', 'Inversion'],
                 ['He had hardly arrived.', 'Normal']],
                'Đảo ngữ: trạng từ phủ định đầu câu + trợ động từ + chủ ngữ.', 'kho');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT inversion.',
                [['Never have I been there.', 'Correct'],
                 ['Rarely does he smoke.', 'Correct'],
                 ['Never I have been there.', 'Incorrect'],
                 ['Rarely he does smoke.', 'Incorrect'],
                 ['Seldom do they argue.', 'Correct'],
                 ['Seldom they do argue.', 'Incorrect']],
                'Trợ động từ phải đứng trước chủ ngữ trong câu đảo ngữ.', 'kho');
            $this->sortQ($L, 'Drag each adverb into CAUSES INVERSION or NOT.',
                [['Never', 'Causes'],
                 ['Rarely', 'Causes'],
                 ['Hardly', 'Causes'],
                 ['Usually', 'Not'],
                 ['Often', 'Not'],
                 ['Always', 'Not']],
                'Chỉ trạng từ mang nghĩa phủ định mới gây đảo ngữ.', 'kho');
            $this->sortQ($L, 'Drag each part into the right position: "___ ___ ___ seen." (Never / have / I)',
                [['Never', 'Position 1'],
                 ['have', 'Position 2'],
                 ['I', 'Position 3'],
                 ['seen', 'Position 4'],
                 ['I have', 'Wrong order'],
                 ['have never', 'Wrong order']],
                'Thứ tự: Never + have + I + seen.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Never ___ I seen such a sight. (have/has)', [[0, 'have']],
                'Never + have + I + V-ed.', 'kho');
            $this->fill($L, 'Rarely ___ she go out at night. (do/does)', [[0, 'does']],
                'Rarely + does + she + V.', 'kho');
            $this->fill($L, 'Hardly had he arrived ___ it rained. (when/than)', [[0, 'when']],
                'Hardly... when...; No sooner... than...', 'kho');
            $this->fill($L, 'No sooner had she left ___ it started to rain. (when/than)', [[0, 'than']],
                'No sooner... than...', 'kho');
        }
    }

    private function seedEn12DaoNgu2(): void
    {
        $L = 'tieng-anh-thpt-12-dao-ngu-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Choose the inversion: If you should need help, call me. → ___',
                ['Should you need help, call me.',
                 'Should you need help, you call me.',
                 'You should need help, call me.',
                 'Should need you help, call me.'],
                0, 'Đảo ngữ câu điều kiện loại 1: Should + S + V...', 'kho');
            $this->quiz($L, 'Choose the inversion: If I were rich, I would travel. → ___',
                ['Were I rich, I would travel.',
                 'Was I rich, I would travel.',
                 'Were rich I, I would travel.',
                 'If were I rich, I would travel.'],
                0, 'Đảo ngữ câu điều kiện loại 2: Were + S... (bỏ "if").', 'kho');
            $this->quiz($L, 'Choose the inversion: If he had studied, he would have passed. → ___',
                ['Had he studied, he would have passed.',
                 'Has he studied, he would have passed.',
                 'Had studied he, he would have passed.',
                 'If had he studied, he would have passed.'],
                0, 'Đảo ngữ câu điều kiện loại 3: Had + S + V-ed... (bỏ "if").', 'kho');
            $this->quiz($L, 'Choose the correct inversion with "only": He understood only when he grew up.',
                ['Only when he grew up did he understand.',
                 'Only when he grew up he understood.',
                 'Only when did he grew up he understood.',
                 'Only when he grew up he did understand.'],
                0, 'Only + cụm trạng từ đầu câu → đảo: did + S + V.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Match each conditional with its inversion. (Nối câu điều kiện với đảo ngữ.)',
                [['If you should need help,', 'Should you need help,'],
                 ['If I were you,', 'Were I you,'],
                 ['If he had known,', 'Had he known,'],
                 ['If she comes,', 'Should she come,']],
                'Đảo ngữ câu điều kiện: bỏ "if", đảo should/were/had lên đầu.', 'kho');
            $this->matching($L, 'Match each "only" phrase with its inversion.',
                [['Only then', 'Only then did I realize.'],
                 ['Only by working hard', 'Only by working hard can you succeed.'],
                 ['Only when', 'Only when he came did she leave.'],
                 ['Only after', 'Only after the rain did we go out.']],
                'Only + cụm từ đầu câu gây đảo ngữ.', 'kho');
            $this->matching($L, 'Match each inversion with its normal form.',
                [['Should you need help, call me.', 'If you should need help, call me.'],
                 ['Were I rich, I would travel.', 'If I were rich, I would travel.'],
                 ['Had he studied, he would have passed.', 'If he had studied, he would have passed.'],
                 ['Never have I lied.', 'I have never lied.']],
                'Chuyển qua lại giữa dạng thường và đảo ngữ.', 'kho');
            $this->matching($L, 'Match each auxiliary with its inversion use.',
                [['Should (đảo ĐK loại 1)', 'Should you need...'],
                 ['Were (đảo ĐK loại 2)', 'Were I rich...'],
                 ['Had (đảo ĐK loại 3)', 'Had he known...'],
                 ['Did (đảo với only)', 'Only then did I...']],
                'Trợ động từ đảo lên đầu trong từng loại đảo ngữ.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Drag each sentence into CONDITIONAL INVERSION or NEGATIVE-ADVERB INVERSION.',
                [['Should you need help, call me.', 'Conditional'],
                 ['Were I you, I would go.', 'Conditional'],
                 ['Had he tried, he would win.', 'Conditional'],
                 ['Never have I seen this.', 'Negative adverb'],
                 ['Rarely does she eat out.', 'Negative adverb'],
                 ['Only then did I know.', 'Negative adverb (only)']],
                'Hai nhóm đảo ngữ chính: câu điều kiện và trạng từ phủ định/only.', 'kho');
            $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT inversion.',
                [['Were I rich, I would travel.', 'Correct'],
                 ['Had she come, we would start.', 'Correct'],
                 ['Should you need, call me.', 'Correct'],
                 ['Was I rich, I would travel.', 'Incorrect'],
                 ['Have he studied, he would pass.', 'Incorrect'],
                 ['Should need you, call me.', 'Incorrect']],
                'Đảo ĐK: Were/Had/Should + S + V (bỏ "if", giữ đúng trợ động từ).', 'kho');
            $this->sortQ($L, 'Drag each "if" out: which word replaces "if"?',
                [['If you should go → ___ you go', 'Should'],
                 ['If I were free → ___ I free', 'Were'],
                 ['If he had left → ___ he left', 'Had'],
                 ['If she should call → ___ she call', 'Should'],
                 ['If they were here → ___ they here', 'Were'],
                 ['If we had known → ___ we known', 'Had']],
                'Bỏ "if", đưa should/were/had lên đầu câu.', 'kho');
            $this->sortQ($L, 'Drag each sentence into WITH "IF" or WITHOUT "IF" (inversion).',
                [['If you should need help...', 'With if'],
                 ['If I were rich...', 'With if'],
                 ['Should you need help...', 'Without if'],
                 ['Were I rich...', 'Without if'],
                 ['Had he known...', 'Without if'],
                 ['If he had known...', 'With if']],
                'Câu đảo ngữ điều kiện không còn từ "if".', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'If you should need help → ___ you need help...', [[0, 'Should']],
                'Đảo ngữ ĐK loại 1: Should + S + V.', 'kho');
            $this->fill($L, 'If I were rich → ___ I rich...', [[0, 'Were']],
                'Đảo ngữ ĐK loại 2: Were + S...', 'kho');
            $this->fill($L, 'If he had studied → ___ he studied...', [[0, 'Had']],
                'Đảo ngữ ĐK loại 3: Had + S + V-ed.', 'kho');
            $this->fill($L, 'Only then ___ I realize the truth. (do/did)', [[0, 'did']],
                'Only then + did + S + V.', 'kho');
        }
    }
}
