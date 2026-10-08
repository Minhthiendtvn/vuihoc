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
 * Dữ liệu NHÓM NĂNG KHIẾU – HƯỚNG NGHIỆP bậc THPT (lớp 10, 11, 12).
 *
 * Tạo 12 topics mới cho 4 môn năng khiếu/hướng nghiệp, mỗi topic đúng 1 khối:
 *  - am-nhac-thpt-10 (🎵): "Nhạc lý nâng cao"              — lớp 10
 *  - am-nhac-thpt-11 (🎶): "Âm nhạc Việt Nam hiện đại"     — lớp 11
 *  - am-nhac-thpt-12 (🎼): "Thưởng thức âm nhạc"           — lớp 12
 *  - my-thuat-thpt-10 (🎨): "Lịch sử mỹ thuật"             — lớp 10
 *  - my-thuat-thpt-11 (🖌️): "Thiết kế cơ bản"              — lớp 11
 *  - my-thuat-thpt-12 (🖼️): "Mỹ thuật ứng dụng"            — lớp 12
 *  - gdtc-thpt-10 (⚽): "Luật bóng đá"                     — lớp 10
 *  - gdtc-thpt-11 (🏐): "Luật bóng chuyền và cầu lông"     — lớp 11
 *  - gdtc-thpt-12 (🏃): "Dinh dưỡng và rèn luyện"          — lớp 12
 *  - trai-nghiem-huong-nghiep-thpt-10 (🧭): "Kỹ năng học tập THPT" — lớp 10
 *  - trai-nghiem-huong-nghiep-thpt-11 (💼): "Khám phá nghề nghiệp" — lớp 11
 *  - trai-nghiem-huong-nghiep-thpt-12 (🎓): "Chuẩn bị tương lai"   — lớp 12
 *
 * Mỗi topic 2 skills, mỗi skill 2 bài học (slug {topic-slug}-lop-{grade}-{n},
 * n = 1..4 trong phạm vi topic), status 'published', grade = khối,
 * is_demo = true. Mỗi bài đúng 16 câu: 4 quiz + 4 matching + 4 sort + 4 fill.
 * Tổng: 48 bài, 768 câu. Các môn nặng về LÝ THUYẾT, không yêu cầu thực hành
 * nhạc cụ / vẽ / vận động. Nội dung TIẾNG VIỆT TỰ VIẾT 100%, mỗi câu có
 * explanation.
 *
 * Lưu ý: cột difficulty của lessons/questions là enum ('de','trung_binh','kho')
 * nên 'medium' → 'trung_binh', 'hard' → 'kho'.
 *
 * Idempotent: topic/skill dùng firstOrCreate theo slug; lesson bỏ qua khi
 * slug đã tồn tại; câu hỏi bỏ qua khi (lesson_id, game_type) đã được seed.
 */
class HighSchoolGroupNangKhieuHuongNghiepSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $gradeBySlug = [];

    /** Cấu trúc: topic_slug => [subject, grade, name, icon, description, skills...] */
    private array $plan = [
        'am-nhac-thpt-10' => [
            'subject' => 'am-nhac', 'grade' => 10, 'icon' => '🎵',
            'name' => 'Nhạc lý nâng cao',
            'description' => 'Củng cố và nâng cao kiến thức nhạc lý ở bậc THPT: cấu tạo hợp âm, vòng hợp âm thông dụng và kỹ năng đọc bản nhạc nhanh, chính xác.',
            'skills' => [
                ['slug' => 'am-nhac-thpt-10-hop-am', 'name' => 'Hợp âm cơ bản',
                 'description' => 'Hiểu hợp âm là gì, cách cấu tạo hợp âm ba trưởng – thứ và cách gọi tên hợp âm theo ký hiệu quốc tế.'],
                ['slug' => 'am-nhac-thpt-10-doc-nhac', 'name' => 'Đọc nhạc nâng cao',
                 'description' => 'Đọc nhanh và chính xác bản nhạc: các loại khóa, nhịp phức tạp, dấu hóa và kỹ thuật đọc lướt.'],
            ],
        ],
        'am-nhac-thpt-11' => [
            'subject' => 'am-nhac', 'grade' => 11, 'icon' => '🎶',
            'name' => 'Âm nhạc Việt Nam hiện đại',
            'description' => 'Tìm hiểu các dòng nhạc của Việt Nam thời hiện đại và những nhạc sĩ có đóng góp tiêu biểu. Chỉ học khái niệm và kiến thức chung, không trích lời bài hát có bản quyền.',
            'skills' => [
                ['slug' => 'am-nhac-thpt-11-dong-nhac', 'name' => 'Các dòng nhạc Việt Nam',
                 'description' => 'Nhận biết các dòng nhạc chính của Việt Nam hiện đại: nhạc cách mạng, nhạc trữ tình, nhạc trẻ và dòng nhạc dân gian đương đại.'],
                ['slug' => 'am-nhac-thpt-11-nhac-si', 'name' => 'Nhạc sĩ tiêu biểu',
                 'description' => 'Hiểu vai trò của người nhạc sĩ trong đời sống âm nhạc và những đóng góp tiêu biểu theo các thế hệ.'],
            ],
        ],
        'am-nhac-thpt-12' => [
            'subject' => 'am-nhac', 'grade' => 12, 'icon' => '🎼',
            'name' => 'Thưởng thức âm nhạc',
            'description' => 'Mở rộng hiểu biết về các thể loại âm nhạc trên thế giới và những hình thức tác phẩm kinh điển của âm nhạc bác học phương Tây.',
            'skills' => [
                ['slug' => 'am-nhac-thpt-12-the-loai', 'name' => 'Các thể loại âm nhạc thế giới',
                 'description' => 'Nhận biết các thể loại lớn: nhạc cổ điển, jazz, blues, rock, pop, hip-hop, nhạc điện tử và âm nhạc dân gian các dân tộc.'],
                ['slug' => 'am-nhac-thpt-12-tac-pham', 'name' => 'Tác phẩm kinh điển',
                 'description' => 'Làm quen với các hình thức tác phẩm lớn: giao hưởng, concerto, opera, nhạc kịch và nhạc thính phòng.'],
            ],
        ],
        'my-thuat-thpt-10' => [
            'subject' => 'my-thuat', 'grade' => 10, 'icon' => '🎨',
            'name' => 'Lịch sử mỹ thuật',
            'description' => 'Hành trình mỹ thuật từ phương Đông đến phương Tây: những nền mỹ thuật lớn, đặc điểm phong cách và giá trị để lại cho nhân loại.',
            'skills' => [
                ['slug' => 'my-thuat-thpt-10-phuong-dong', 'name' => 'Mỹ thuật phương Đông',
                 'description' => 'Khám phá mỹ thuật Trung Hoa, Nhật Bản và truyền thống mỹ thuật Việt Nam: chất liệu, đề tài và triết lý.'],
                ['slug' => 'my-thuat-thpt-10-phuong-tay', 'name' => 'Mỹ thuật phương Tây',
                 'description' => 'Từ mỹ thuật cổ đại Ai Cập, Hy Lạp – La Mã đến Phục hưng và các trào lưu nghệ thuật hiện đại.'],
            ],
        ],
        'my-thuat-thpt-11' => [
            'subject' => 'my-thuat', 'grade' => 11, 'icon' => '🖌️',
            'name' => 'Thiết kế cơ bản',
            'description' => 'Những nguyên lý nền tảng của thiết kế và ngôn ngữ màu sắc: kiến thức cốt lõi cho mọi ngành thiết kế.',
            'skills' => [
                ['slug' => 'my-thuat-thpt-11-nguyen-ly', 'name' => 'Nguyên lý thiết kế',
                 'description' => 'Nắm các nguyên lý tổ chức thị giác: cân bằng, tương phản, nhấn mạnh, nhịp điệu, tỷ lệ, thống nhất, khoảng trắng.'],
                ['slug' => 'my-thuat-thpt-11-mau-sac', 'name' => 'Màu sắc trong thiết kế',
                 'description' => 'Vòng tròn màu, các hòa sắc cơ bản, tâm lý màu sắc và cách phối màu hiệu quả.'],
            ],
        ],
        'my-thuat-thpt-12' => [
            'subject' => 'my-thuat', 'grade' => 12, 'icon' => '🖼️',
            'name' => 'Mỹ thuật ứng dụng',
            'description' => 'Mỹ thuật đi vào đời sống: thiết kế đồ họa, kiến trúc và điêu khắc trong không gian công cộng.',
            'skills' => [
                ['slug' => 'my-thuat-thpt-12-do-hoa', 'name' => 'Thiết kế đồ họa',
                 'description' => 'Từ logo, nhận diện thương hiệu đến poster và typography: thiết kế phục vụ truyền thông.'],
                ['slug' => 'my-thuat-thpt-12-kien-truc', 'name' => 'Kiến trúc và điêu khắc',
                 'description' => 'Các phong cách kiến trúc tiêu biểu, ngôn ngữ điêu khắc và vai trò của mỹ thuật trong không gian sống.'],
            ],
        ],
        'gdtc-thpt-10' => [
            'subject' => 'gdtc', 'grade' => 10, 'icon' => '⚽',
            'name' => 'Luật bóng đá',
            'description' => 'Nắm vững luật thi đấu bóng đá 11 người: sân bãi, cầu thủ, các tình huống phạm lỗi và chiến thuật cơ bản.',
            'skills' => [
                ['slug' => 'gdtc-thpt-10-luat-cb', 'name' => 'Luật thi đấu cơ bản',
                 'description' => 'Sân thi đấu, số lượng cầu thủ, thời gian trận đấu, luật việt vị, các quả phạt và thẻ phạt.'],
                ['slug' => 'gdtc-thpt-10-vi-tri', 'name' => 'Vị trí và chiến thuật',
                 'description' => 'Các vị trí trên sân, vai trò từng tuyến và những sơ đồ chiến thuật phổ biến.'],
            ],
        ],
        'gdtc-thpt-11' => [
            'subject' => 'gdtc', 'grade' => 11, 'icon' => '🏐',
            'name' => 'Luật bóng chuyền và cầu lông',
            'description' => 'Luật thi đấu hai môn thể thao phổ biến trong trường học: bóng chuyền và cầu lông.',
            'skills' => [
                ['slug' => 'gdtc-thpt-11-bong-chuyen', 'name' => 'Luật bóng chuyền',
                 'description' => 'Sân bãi, đội hình 6 người, cách tính điểm rally, luân chuyển vị trí và các lỗi thường gặp.'],
                ['slug' => 'gdtc-thpt-11-cau-long', 'name' => 'Luật cầu lông',
                 'description' => 'Sân cầu lông, cách tính điểm 21, luật giao cầu và các lỗi trong thi đấu.'],
            ],
        ],
        'gdtc-thpt-12' => [
            'subject' => 'gdtc', 'grade' => 12, 'icon' => '🏃',
            'name' => 'Dinh dưỡng và rèn luyện',
            'description' => 'Kiến thức nền tảng về dinh dưỡng thể thao và cách lập kế hoạch rèn luyện khoa học, an toàn.',
            'skills' => [
                ['slug' => 'gdtc-thpt-12-dinh-duong', 'name' => 'Dinh dưỡng thể thao',
                 'description' => 'Các nhóm chất dinh dưỡng, vai trò của nước và chế độ ăn trước – trong – sau buổi tập.'],
                ['slug' => 'gdtc-thpt-12-ke-hoach', 'name' => 'Lập kế hoạch rèn luyện',
                 'description' => 'Nguyên tắc tập luyện khoa học, cấu trúc buổi tập và phòng tránh chấn thương.'],
            ],
        ],
        'trai-nghiem-huong-nghiep-thpt-10' => [
            'subject' => 'trai-nghiem-huong-nghiep', 'grade' => 10, 'icon' => '🧭',
            'name' => 'Kỹ năng học tập THPT',
            'description' => 'Trang bị kỹ năng học tập bậc THPT: quản lý thời gian, phương pháp ghi nhớ và ôn tập hiệu quả.',
            'skills' => [
                ['slug' => 'tn-hn-thpt-10-quan-ly-thoi-gian', 'name' => 'Quản lý thời gian',
                 'description' => 'Sắp xếp ưu tiên, lập kế hoạch học tập và chiến thắng sự trì hoãn.'],
                ['slug' => 'tn-hn-thpt-10-phuong-phap-hoc', 'name' => 'Phương pháp học hiệu quả',
                 'description' => 'Ghi chú thông minh, kỹ thuật ghi nhớ lâu và cách ôn thi khoa học.'],
            ],
        ],
        'trai-nghiem-huong-nghiep-thpt-11' => [
            'subject' => 'trai-nghiem-huong-nghiep', 'grade' => 11, 'icon' => '💼',
            'name' => 'Khám phá nghề nghiệp',
            'description' => 'Bắt đầu hành trình hướng nghiệp: tìm hiểu các nhóm nghề trong xã hội và khám phá chính bản thân mình.',
            'skills' => [
                ['slug' => 'tn-hn-thpt-11-nhom-nghe', 'name' => 'Các nhóm nghề',
                 'description' => 'Sáu nhóm tính cách nghề nghiệp Holland và bức tranh các lĩnh vực nghề nghiệp hiện nay.'],
                ['slug' => 'tn-hn-thpt-11-ban-than', 'name' => 'Tìm hiểu bản thân',
                 'description' => 'Khám phá sở thích, năng lực, tính cách và giá trị sống để định hướng nghề nghiệp.'],
            ],
        ],
        'trai-nghiem-huong-nghiep-thpt-12' => [
            'subject' => 'trai-nghiem-huong-nghiep', 'grade' => 12, 'icon' => '🎓',
            'name' => 'Chuẩn bị tương lai',
            'description' => 'Năm học quyết định: chọn ngành chọn trường đúng đắn và chuẩn bị hồ sơ, kỹ năng phỏng vấn thật tốt.',
            'skills' => [
                ['slug' => 'tn-hn-thpt-12-chon-nganh', 'name' => 'Chọn ngành chọn trường',
                 'description' => 'Tiêu chí chọn ngành, cách tìm hiểu trường đại học và các phương thức xét tuyển.'],
                ['slug' => 'tn-hn-thpt-12-phong-van', 'name' => 'Kỹ năng phỏng vấn và hồ sơ',
                 'description' => 'Viết CV, chuẩn bị hồ sơ ứng tuyển và trả lời phỏng vấn tự tin.'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->plan as $topicSlug => $cfg) {
            $subject = Subject::where('slug', $cfg['subject'])->firstOrFail();
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
            foreach ($cfg['skills'] as $s) {
                $maxSkillOrder = (int) Skill::where('topic_id', $topic->id)->max('sort_order');
                $skill = Skill::firstOrCreate(
                    ['slug' => $s['slug']],
                    [
                        'topic_id' => $topic->id,
                        'name' => $s['name'],
                        'description' => $s['description'],
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

        // ---- Âm nhạc: lớp 10 ----
        $this->seedAmNhac101(); $this->seedAmNhac102();
        $this->seedAmNhac103(); $this->seedAmNhac104();
        // ---- Âm nhạc: lớp 11 ----
        $this->seedAmNhac111(); $this->seedAmNhac112();
        $this->seedAmNhac113(); $this->seedAmNhac114();
        // ---- Âm nhạc: lớp 12 ----
        $this->seedAmNhac121(); $this->seedAmNhac122();
        $this->seedAmNhac123(); $this->seedAmNhac124();
        // ---- Mỹ thuật: lớp 10 ----
        $this->seedMyThuat101(); $this->seedMyThuat102();
        $this->seedMyThuat103(); $this->seedMyThuat104();
        // ---- Mỹ thuật: lớp 11 ----
        $this->seedMyThuat111(); $this->seedMyThuat112();
        $this->seedMyThuat113(); $this->seedMyThuat114();
        // ---- Mỹ thuật: lớp 12 ----
        $this->seedMyThuat121(); $this->seedMyThuat122();
        $this->seedMyThuat123(); $this->seedMyThuat124();
        // ---- GDTC: lớp 10 ----
        $this->seedGdtc101(); $this->seedGdtc102();
        $this->seedGdtc103(); $this->seedGdtc104();
        // ---- GDTC: lớp 11 ----
        $this->seedGdtc111(); $this->seedGdtc112();
        $this->seedGdtc113(); $this->seedGdtc114();
        // ---- GDTC: lớp 12 ----
        $this->seedGdtc121(); $this->seedGdtc122();
        $this->seedGdtc123(); $this->seedGdtc124();
        // ---- Trải nghiệm & Hướng nghiệp: lớp 10 ----
        $this->seedTnHn101(); $this->seedTnHn102();
        $this->seedTnHn103(); $this->seedTnHn104();
        // ---- Trải nghiệm & Hướng nghiệp: lớp 11 ----
        $this->seedTnHn111(); $this->seedTnHn112();
        $this->seedTnHn113(); $this->seedTnHn114();
        // ---- Trải nghiệm & Hướng nghiệp: lớp 12 ----
        $this->seedTnHn121(); $this->seedTnHn122();
        $this->seedTnHn123(); $this->seedTnHn124();
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
     * Siêu dữ liệu 48 bài học: [$topicSlug][$num] =>
     * [title, objective, difficulty, duration, instructions].
     * difficulty dùng enum DB: 'trung_binh' (= medium), 'kho' (= hard).
     */
    private function lessonMeta(string $topicSlug, int $num): array
    {
        $plan = [
            'am-nhac-thpt-10' => [
                1 => ['title' => 'Hợp âm là gì: cấu tạo hợp âm ba trưởng và thứ',
                    'objective' => 'Nhận biết khái niệm hợp âm; phân biệt hợp âm trưởng và hợp âm thứ qua cấu tạo quãng 3.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Hợp âm ba (triad) gồm 3 nốt xếp chồng theo quãng 3: nốt gốc (root), nốt bậc 3 và nốt bậc 5. Hợp âm trưởng: quãng 3 trưởng + quãng 5 đúng (ví dụ C – E – G). Hợp âm thứ: quãng 3 thứ + quãng 5 đúng (ví dụ C – Eb – G). Ký hiệu quốc tế: C là Đô trưởng, Cm là Đô thứ.'],
                2 => ['title' => 'Vòng hợp âm thông dụng và cách gọi tên hợp âm',
                    'objective' => 'Gọi tên hợp âm theo ký hiệu quốc tế; nhận biết các vòng hợp âm phổ biến như I – V – vi – IV.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Hợp âm được ký hiệu bằng chữ cái: C, D, E, F, G, A, B tương ứng Đô, Rê, Mi, Fa, Sol, La, Si. Chữ m viết sau nghĩa là thứ (Am = La thứ). Số 7 nghĩa là hợp âm 7 (G7 = Sol 7). Vòng hợp âm là chuỗi hợp âm lặp lại tạo nên khung của bài hát, ví dụ vòng C – G – Am – F rất phổ biến trong nhạc nhẹ.'],
                3 => ['title' => 'Khóa nhạc, nốt nhạc và các loại nhịp',
                    'objective' => 'Nhận biết khóa Sol, khóa Fa; đọc nốt trên khuông nhạc; phân biệt nhịp đơn và nhịp kép.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Khóa Sol (khóa Treble) dùng cho giọng cao và tay phải piano; khóa Fa (khóa Bass) dùng cho giọng trầm và tay trái piano. Nốt nhạc xếp trên 5 dòng kẻ và 4 khe. Nhịp 2/4, 3/4, 4/4 là nhịp đơn; nhịp 6/8, 9/8, 12/8 là nhịp kép (mỗi phách chia làm 3).'],
                4 => ['title' => 'Dấu hóa, nhịp phức và kỹ thuật đọc bản nhạc nhanh',
                    'objective' => 'Hiểu tác dụng của dấu thăng, giáng, bình; đọc được bản nhạc có nhiều dấu hóa và nhịp đổi.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Dấu thăng (#) nâng nốt lên nửa cung, dấu giáng (b) hạ nốt xuống nửa cung, dấu bình (♮) hủy tác dụng của thăng/giáng. Dấu hóa ghi ở đầu khuông nhạc (bộ khóa) áp dụng cho cả bản nhạc. Kỹ thuật đọc nhanh: đọc theo cụm nốt thay vì từng nốt, nhìn trước 1–2 ô nhịp, nhận diện mẫu hợp âm quen thuộc.'],
            ],
            'am-nhac-thpt-11' => [
                1 => ['title' => 'Nhạc cách mạng và nhạc tiền chiến',
                    'objective' => 'Hiểu bối cảnh ra đời của nhạc cách mạng và nhạc tiền chiến; nhận biết đặc điểm nội dung của hai dòng nhạc.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Nhạc cách mạng ra đời trong hai cuộc kháng chiến, nội dung ca ngợi quê hương, cổ vũ chiến đấu; tiêu biểu là Quốc ca Tiến quân ca. Nhạc tiền chiến (trước năm 1945) mang màu sắc lãng mạn, trữ tình, ca ngợi tình yêu quê hương đất nước. Cả hai dòng đều có giá trị lịch sử và nghệ thuật lớn.'],
                2 => ['title' => 'Nhạc trữ tình, nhạc trẻ và dòng nhạc dân gian đương đại',
                    'objective' => 'Phân biệt nhạc trữ tình, nhạc trẻ; hiểu khái niệm phối khí mới cho chất liệu dân gian.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Nhạc trữ tình (bolero) có giai điệu chậm, lời ca da diết về tình yêu quê hương. Nhạc trẻ gắn với đời sống đô thị hiện đại, tiết tấu sôi động, hòa âm theo xu hướng quốc tế. Dòng nhạc dân gian đương đại dùng chất liệu dân ca, nhạc cụ dân tộc phối với hòa âm hiện đại.'],
                3 => ['title' => 'Nhạc sĩ là ai: vai trò của người sáng tác',
                    'objective' => 'Hiểu công việc của nhạc sĩ: sáng tác giai điệu, viết lời, phối khí; phân biệt nhạc sĩ với ca sĩ.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Nhạc sĩ là người sáng tác âm nhạc: viết giai điệu, lời ca và ý tưởng hòa âm. Ca sĩ là người thể hiện tác phẩm. Một người có thể vừa là nhạc sĩ vừa là ca sĩ. Tác quyền bảo vệ quyền lợi của người sáng tác khi tác phẩm được sử dụng.'],
                4 => ['title' => 'Các thế hệ nhạc sĩ Việt Nam hiện đại',
                    'objective' => 'Nhận biết các thế hệ nhạc sĩ theo thời kỳ lịch sử và đóng góp chung của mỗi thế hệ.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Nhạc sĩ Việt Nam hiện đại thường được xếp theo thế hệ gắn với thời kỳ lịch sử: thế hệ tiền chiến đặt nền móng tân nhạc; thế hệ kháng chiến với nhạc cách mạng; thế hệ sau năm 1975 phát triển nhạc nhẹ; thế hệ đương đại hội nhập quốc tế. Mỗi thế hệ đều để lại những tác phẩm gắn với đời sống dân tộc.'],
            ],
            'am-nhac-thpt-12' => [
                1 => ['title' => 'Nhạc cổ điển, jazz và blues',
                    'objective' => 'Hiểu khái niệm âm nhạc bác học; nhận biết đặc điểm của jazz và blues.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Nhạc cổ điển (âm nhạc bác học) là dòng nhạc nghệ thuật phương Tây, được ghi chép bằng bản nhạc chuẩn xác, biểu diễn trong nhà hát. Jazz ra đời ở Mỹ đầu thế kỷ 20, đặc trưng bởi ứng tác (improvisation) và tiết tấu swing. Blues là tiền thân của jazz, giai điệu buồn, cấu trúc 12 ô nhịp.'],
                2 => ['title' => 'Rock, pop, hip-hop và nhạc điện tử',
                    'objective' => 'Phân biệt rock, pop, hip-hop, EDM qua đặc điểm tiết tấu, nhạc cụ và cách thể hiện.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Rock dùng guitar điện, trống, bass với âm thanh mạnh mẽ. Pop có giai điệu dễ nhớ, hướng đến đại chúng. Hip-hop nổi bật với rap (đọc có nhịp điệu trên nền beat). Nhạc điện tử (EDM) được tạo chủ yếu bằng máy tính và thiết bị điện tử, thường dùng trong vũ trường và lễ hội âm nhạc.'],
                3 => ['title' => 'Giao hưởng và concerto',
                    'objective' => 'Hiểu cấu tạo của bản giao hưởng (4 chương) và concerto (nhạc cụ độc tấu với dàn nhạc).',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Giao hưởng (symphony) là tác phẩm lớn cho dàn nhạc giao hưởng, thường gồm 4 chương với tốc độ khác nhau: nhanh – chậm – vừa – nhanh. Concerto là tác phẩm cho một nhạc cụ độc tấu (violin, piano...) đối thoại với dàn nhạc, thường có 3 chương. Cả hai đều là đỉnh cao của âm nhạc bác học.'],
                4 => ['title' => 'Opera, nhạc kịch và nhạc thính phòng',
                    'objective' => 'Phân biệt opera với nhạc kịch; hiểu khái niệm nhạc thính phòng và các biên chế biểu diễn.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Opera là vở nhạc kịch mà diễn viên hát toàn bộ lời thoại, kết hợp dàn nhạc, phục trang và sân khấu hoành tráng. Nhạc kịch hiện đại (musical) xen kẽ hát và thoại. Nhạc thính phòng (chamber music) viết cho nhóm nhỏ nhạc công (song tấu, tam tấu, tứ tấu), biểu diễn trong không gian nhỏ, đòi hỏi sự phối hợp tinh tế.'],
            ],
            'my-thuat-thpt-10' => [
                1 => ['title' => 'Mỹ thuật Trung Hoa và Nhật Bản',
                    'objective' => 'Nhận biết tranh thủy mặc Trung Hoa và tranh khắc gỗ ukiyo-e Nhật Bản qua chất liệu và đặc điểm.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Tranh thủy mặc Trung Hoa dùng mực đen trên giấy hoặc lụa, đề tài sơn thủy, coi trọng khoảng trống và khí vận. Tranh khắc gỗ ukiyo-e của Nhật Bản in nhiều bản màu, đề tài đời sống, phong cảnh, nổi tiếng với hình ảnh sóng và núi Phú Sĩ. Cả hai đều ảnh hưởng sâu đến mỹ thuật thế giới.'],
                2 => ['title' => 'Mỹ thuật truyền thống Việt Nam',
                    'objective' => 'Nhận biết tranh dân gian Đông Hồ, Hàng Trống; hiểu các chất liệu truyền thống: sơn mài, lụa, khắc gỗ.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Tranh Đông Hồ (Bắc Ninh) in từ ván khắc gỗ trên giấy điệp, màu từ thiên nhiên, đề tài dân gian. Tranh Hàng Trống (Hà Nội) in nét rồi tô màu tay, đề tài thờ cúng, chúc tụng. Sơn mài và lụa là chất liệu đặc sắc của hội họa Việt Nam hiện đại.'],
                3 => ['title' => 'Mỹ thuật Ai Cập, Hy Lạp và La Mã cổ đại',
                    'objective' => 'Nhận biết đặc điểm mỹ thuật Ai Cập (luật frontal) và điêu khắc Hy Lạp – La Mã (tả thực, cân đối).',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Mỹ thuật Ai Cập gắn với tín ngưỡng, tuân thủ luật frontal: mặt vẽ nghiêng, mắt nhìn thẳng, thân nhìn thẳng. Hy Lạp cổ đại đề cao vẻ đẹp cơ thể con người, điêu khắc tả thực, cân đối theo tỷ lệ vàng. La Mã kế thừa Hy Lạp, phát triển nghệ thuật chân dung và kiến trúc vòm, mái vòm.'],
                4 => ['title' => 'Phục hưng, Ấn tượng và nghệ thuật hiện đại',
                    'objective' => 'Hiểu tinh thần Phục hưng; nhận biết trường phái Ấn tượng và các trào lưu nghệ thuật hiện đại.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Phục hưng (thế kỷ 14–16) hồi sinh tinh thần cổ điển, phát minh luật phối cảnh, đề cao con người. Ấn tượng (cuối thế kỷ 19) vẽ ngoài trời, chấm phá màu để ghi lại ấn tượng ánh sáng. Nghệ thuật hiện đại thế kỷ 20 bùng nổ nhiều trào lưu: lập thể, siêu thực, trừu tượng... phá vỡ quy tắc tạo hình truyền thống.'],
            ],
            'my-thuat-thpt-11' => [
                1 => ['title' => 'Cân bằng, tương phản và điểm nhấn',
                    'objective' => 'Vận dụng cân bằng đối xứng/bất đối xứng; tạo tương phản và điểm nhấn trong bố cục.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Cân bằng đối xứng tạo cảm giác trang trọng, ổn định; cân bằng bất đối xứng tạo sự năng động. Tương phản (sáng–tối, to–nhỏ, nóng–lạnh) giúp các yếu tố nổi bật. Điểm nhấn là vị trí thu hút mắt nhìn đầu tiên, thường đặt theo quy tắc một phần ba.'],
                2 => ['title' => 'Nhịp điệu, tỷ lệ, thống nhất và khoảng trắng',
                    'objective' => 'Hiểu nhịp điệu thị giác, tỷ lệ vàng, tính thống nhất và vai trò của khoảng trắng.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Nhịp điệu là sự lặp lại có quy luật tạo chuyển động cho mắt. Tỷ lệ vàng (khoảng 1:1,618) được coi là tỷ lệ hài hòa. Thống nhất là sự gắn kết các yếu tố thành tổng thể. Khoảng trắng (negative space) giúp bố cục thoáng, sang và dễ đọc, không phải chỗ trống lãng phí.'],
                3 => ['title' => 'Vòng tròn màu và các nhóm màu',
                    'objective' => 'Đọc vòng tròn màu; phân biệt màu nóng/lạnh, màu bậc 1/2/3 và các cặp màu bổ túc.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Vòng tròn màu gồm 12 màu: 3 màu bậc 1 (đỏ, vàng, lam), 3 màu bậc 2 (cam, lục, tím) pha từ hai màu bậc 1, 6 màu bậc 3. Màu nóng (đỏ, cam, vàng) gợi năng lượng; màu lạnh (lam, lục, tím) gợi sự yên tĩnh. Hai màu đối diện nhau là cặp bổ túc, đặt cạnh nhau tạo tương phản mạnh.'],
                4 => ['title' => 'Phối màu và tâm lý màu sắc',
                    'objective' => 'Vận dụng các công thức phối màu; hiểu ý nghĩa tâm lý của màu sắc trong thiết kế.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Các công thức phối màu phổ biến: đơn sắc (một màu nhiều sắc độ), tương đồng (màu kề nhau), bổ túc (hai màu đối diện), bộ ba (tam giác đều trên vòng tròn màu). Mỗi màu gợi cảm xúc riêng: đỏ – nhiệt huyết, xanh lam – tin cậy, xanh lá – thiên nhiên, vàng – lạc quan, đen – sang trọng, trắng – tinh khiết.'],
            ],
            'my-thuat-thpt-12' => [
                1 => ['title' => 'Logo và nhận diện thương hiệu',
                    'objective' => 'Hiểu vai trò của logo; nhận biết các loại logo và nguyên tắc thiết kế logo tốt.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Logo là dấu hiệu nhận biết cốt lõi của thương hiệu, gồm các loại: chữ (logotype), biểu tượng (symbol), kết hợp cả hai. Logo tốt phải đơn giản, dễ nhớ, bền vững theo thời gian và dùng được ở mọi kích thước. Hệ nhận diện thương hiệu gồm logo, màu sắc, font chữ, danh thiếp, bao bì... thống nhất với nhau.'],
                2 => ['title' => 'Poster, typography và lưới bố cục',
                    'objective' => 'Hiểu cấu trúc poster hiệu quả; nắm kiến thức cơ bản về typography và lưới bố cục.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Poster hiệu quả có thứ bậc thông tin rõ: tiêu đề nổi bật, hình ảnh thu hút, thông tin chi tiết vừa đủ. Typography là nghệ thuật chữ: chọn font phù hợp (serif trang trọng, sans-serif hiện đại), chú ý cỡ chữ, giãn dòng, căn lề. Lưới bố cục (grid) giúp sắp xếp các yếu tố gọn gàng, nhất quán trên nhiều trang.'],
                3 => ['title' => 'Các phong cách kiến trúc tiêu biểu',
                    'objective' => 'Nhận biết kiến trúc cổ điển, Gothic, hiện đại và kiến trúc truyền thống Việt Nam.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Kiến trúc cổ điển Hy Lạp – La Mã dùng hệ cột và thức cột nghiêm ngặt. Gothic nổi bật với vòm nhọn, cửa sổ kính màu, tháp cao vút. Kiến trúc hiện đại đề cao công năng, vật liệu mới (thép, kính, bê tông). Kiến trúc truyền thống Việt Nam: mái cong, đình chùa, dùng gỗ và ngói, hài hòa với thiên nhiên.'],
                4 => ['title' => 'Điêu khắc: hình thức và chất liệu',
                    'objective' => 'Phân biệt điêu khắc tròn và phù điêu; hiểu các chất liệu điêu khắc và vai trò của tượng đài.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Điêu khắc tròn là tượng có thể xem từ mọi phía; phù điêu là hình nổi trên mặt phẳng (nổi cao, nổi thấp). Chất liệu đa dạng: đá, gỗ, đồng, đất nung, vật liệu composite hiện đại. Tượng đài trong không gian công cộng tôn vinh nhân vật, sự kiện lịch sử, đòi hỏi sự trang nghiêm và bền vững.'],
            ],
            'gdtc-thpt-10' => [
                1 => ['title' => 'Sân thi đấu, cầu thủ và thời gian thi đấu',
                    'objective' => 'Nắm kích thước sân, số cầu thủ, thời gian thi đấu và quy định thay người cơ bản.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Sân bóng đá 11 người dài 90–120m, rộng 45–90m (tiêu chuẩn quốc tế khoảng 105×68m). Mỗi đội 11 cầu thủ gồm 1 thủ môn; trận đấu không tiếp tục nếu một đội còn dưới 7 người. Trận đấu gồm 2 hiệp, mỗi hiệp 45 phút, nghỉ giữa hiệp tối đa 15 phút. Bóng đá hiện đại cho thay tối đa 5 cầu thủ mỗi trận.'],
                2 => ['title' => 'Việt vị, phạm lỗi và các quả phạt',
                    'objective' => 'Hiểu luật việt vị; phân biệt các quả phạt trực tiếp, gián tiếp, phạt đền và thẻ phạt.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Việt vị: cầu thủ đứng gần khung thành đối phương hơn bóng và cầu thủ áp chót của đối phương tại thời điểm đồng đội chuyền bóng, và tham gia tình huống. Không việt vị khi nhận bóng từ phát bóng, ném biên, phạt góc. Phạm lỗi trong vòng cấm bị phạt đền. Thẻ vàng cảnh cáo; hai thẻ vàng hoặc thẻ đỏ trực tiếp bị truất quyền thi đấu.'],
                3 => ['title' => 'Các vị trí trên sân bóng đá',
                    'objective' => 'Nhận biết 4 tuyến: thủ môn, hậu vệ, tiền vệ, tiền đạo và vai trò của từng vị trí.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Thủ môn là người duy nhất được dùng tay trong vòng cấm, có nhiệm vụ bảo vệ khung thành. Hậu vệ ngăn chặn đối phương ghi bàn (trung vệ, hậu vệ biên). Tiền vệ là cầu nối giữa phòng ngự và tấn công. Tiền đạo có nhiệm vụ ghi bàn, gồm tiền đạo cắm và tiền đạo cánh.'],
                4 => ['title' => 'Sơ đồ chiến thuật cơ bản',
                    'objective' => 'Đọc sơ đồ chiến thuật 4-4-2, 4-3-3, 3-5-2; hiểu khái niệm pressing và phản công.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Sơ đồ chiến thuật ghi số cầu thủ từng tuyến không tính thủ môn: 4-4-2 cân bằng, 4-3-3 thiên về tấn công biên, 3-5-2 chắc chắn ở giữa sân. Pressing là gây áp lực ngay bên phần sân đối phương để giành bóng. Phản công là chuyển nhanh từ phòng ngự sang tấn công khi cướp được bóng.'],
            ],
            'gdtc-thpt-11' => [
                1 => ['title' => 'Luật bóng chuyền: sân, đội hình và cách tính điểm',
                    'objective' => 'Nắm kích thước sân, chiều cao lưới, đội hình 6 người và cách tính điểm theo thể thức rally.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Sân bóng chuyền dài 18m, rộng 9m; lưới cao 2,43m (nam) và 2,24m (nữ). Mỗi đội 6 người trên sân. Tính điểm theo thể thức rally: mỗi pha bóng đều có điểm, đội thắng pha bóng được phát bóng tiếp. Thắng 1 set khi đạt 25 điểm và hơn đối phương ít nhất 2 điểm; set 5 (nếu có) đánh đến 15 điểm.'],
                2 => ['title' => 'Luật bóng chuyền: luân chuyển, chạm bóng và lỗi thường gặp',
                    'objective' => 'Hiểu luật luân chuyển vị trí, số lần chạm bóng và các lỗi kỹ thuật thường gặp.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Khi giành quyền phát bóng từ đối phương, đội phải luân chuyển vị trí theo chiều kim đồng hồ. Mỗi đội được chạm bóng tối đa 3 lần trước khi đưa bóng sang sân đối phương (chắn bóng không tính). Lỗi thường gặp: chạm lưới, dẫm vạch giữa sân, bóng chạm ăng-ten, cầu thủ hàng sau tấn công trên vạch 3m.'],
                3 => ['title' => 'Luật cầu lông: sân, cách tính điểm và giao cầu',
                    'objective' => 'Nắm kích thước sân đơn/đôi, cách tính điểm 21 và quy định giao cầu đúng luật.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Sân cầu lông dài 13,4m; rộng 6,1m (đánh đôi) và 5,18m (đánh đơn). Lưới cao 1,55m ở cột. Mỗi set đánh đến 21 điểm theo thể thức rally, phải hơn đối phương 2 điểm; nếu 29 đều, ai được 30 trước thì thắng. Giao cầu phải đánh từ dưới thắt lưng, cầu bay chéo sang ô đối diện.'],
                4 => ['title' => 'Luật cầu lông: lỗi và tình huống đặc biệt',
                    'objective' => 'Nhận biết các lỗi: chạm lưới, cầu ngoài sân, giao cầu sai; hiểu quy định đánh lại và đổi sân.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Lỗi trong cầu lông: cầu rơi ngoài vạch giới hạn, cầu không qua lưới, chạm lưới khi cầu còn trong cuộc, giao cầu sai quy định. Khi có sự cố ngoài ý muốn (cầu mắc trên lưới), trọng tài cho đánh lại pha bóng đó. Đổi sân khi kết thúc mỗi set và giữa set 3 khi một bên đạt 11 điểm.'],
            ],
            'gdtc-thpt-12' => [
                1 => ['title' => 'Các nhóm chất dinh dưỡng cho người tập luyện',
                    'objective' => 'Kể tên 6 nhóm chất dinh dưỡng và vai trò của từng nhóm với người tập thể thao.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Sáu nhóm chất: bột đường (carbohydrate) là nhiên liệu chính cho vận động; đạm (protein) xây dựng và phục hồi cơ bắp; chất béo cung cấp năng lượng dự trữ; vitamin và khoáng chất điều hòa hoạt động cơ thể; nước chiếm 60–70% cơ thể, mất nước làm giảm hiệu suất. Người tập luyện cần ăn đa dạng, đủ chất, không bỏ bữa.'],
                2 => ['title' => 'Ăn uống trước, trong và sau khi tập luyện',
                    'objective' => 'Biết cách ăn uống hợp lý quanh buổi tập: thời điểm, loại thực phẩm và bù nước.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Trước tập 2–3 giờ: ăn bữa giàu bột đường, ít béo, dễ tiêu. Trong buổi tập kéo dài: bù nước thường xuyên, có thể dùng nước có điện giải. Sau tập 30–60 phút: bổ sung đạm và bột đường để phục hồi cơ. Tránh ăn quá no ngay trước tập, tránh đồ uống có cồn và nhiều đường.'],
                3 => ['title' => 'Nguyên tắc tập luyện khoa học',
                    'objective' => 'Nắm các nguyên tắc: tăng tiến dần, đặc thù, đa dạng, nghỉ ngơi hồi phục.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Tăng tiến dần: tăng khối lượng, cường độ từ từ để cơ thể thích nghi. Đặc thù: tập đúng nhóm cơ và kỹ năng của môn mình chơi. Đa dạng: kết hợp sức bền, sức mạnh, linh hoạt để phát triển toàn diện. Nghỉ ngơi: cơ bắp phát triển trong lúc nghỉ; tập quá sức gây quá tải, chấn thương và sa sút phong độ.'],
                4 => ['title' => 'Khởi động, thả lỏng và phòng tránh chấn thương',
                    'objective' => 'Hiểu vai trò của khởi động và thả lỏng; nhận biết chấn thương thường gặp và cách xử trí ban đầu.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Khởi động 10–15 phút làm nóng cơ, tăng nhịp tim, bôi trơn khớp, giảm nguy cơ chấn thương. Thả lỏng sau tập giúp cơ thể trở lại trạng thái bình thường, giảm đau cơ. Chấn thương thường gặp: bong gân, căng cơ, trật khớp. Xử trí ban đầu theo nguyên tắc RICE: nghỉ ngơi, chườm lạnh, băng ép, kê cao; chấn thương nặng phải đến cơ sở y tế.'],
            ],
            'trai-nghiem-huong-nghiep-thpt-10' => [
                1 => ['title' => 'Sắp xếp ưu tiên với ma trận Eisenhower',
                    'objective' => 'Phân loại công việc theo mức độ quan trọng – khẩn cấp; lập kế hoạch tuần hiệu quả.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Ma trận Eisenhower chia việc thành 4 ô: quan trọng và khẩn cấp (làm ngay), quan trọng nhưng không khẩn cấp (lên lịch), khẩn cấp nhưng không quan trọng (ủy thác), không quan trọng cũng không khẩn cấp (loại bỏ). Học sinh nên dành nhiều thời gian cho ô 2: ôn bài, đọc sách. Lập kế hoạch tuần vào đầu tuần, mỗi tối rà soát lại.'],
                2 => ['title' => 'Kỹ thuật Pomodoro và vượt qua trì hoãn',
                    'objective' => 'Áp dụng Pomodoro 25/5; nhận biết nguyên nhân trì hoãn và cách khắc phục.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Pomodoro: học tập trung 25 phút, nghỉ 5 phút; sau 4 phiên nghỉ dài 15–30 phút. Trì hoãn thường do việc quá lớn (chia nhỏ ra), sợ thất bại (bắt đầu từ phần dễ nhất) hoặc xao nhãng (tắt thông báo, dọn bàn học). Quy tắc 2 phút: việc gì làm được trong 2 phút thì làm ngay.'],
                3 => ['title' => 'Ghi chú Cornell và kỹ thuật Feynman',
                    'objective' => 'Ghi chú theo phương pháp Cornell; dùng kỹ thuật Feynman để kiểm tra mức độ hiểu bài.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Ghi chú Cornell chia trang thành 3 phần: ghi chú chính, cột từ khóa/câu hỏi, phần tóm tắt cuối trang. Kỹ thuật Feynman: giải thích lại kiến thức bằng lời đơn giản như đang dạy người khác; chỗ nào lúng túng chính là chỗ chưa hiểu, cần học lại. Dạy lại cho bạn là cách học sâu nhất.'],
                4 => ['title' => 'Ôn tập ngắt quãng và chiến lược làm bài thi',
                    'objective' => 'Áp dụng spaced repetition; xây dựng chiến lược phân bổ thời gian và kiểm tra bài thi.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Ôn tập ngắt quãng (spaced repetition): ôn lại sau 1 ngày, 3 ngày, 1 tuần, 1 tháng thay vì nhồi nhét một lúc. Tự kiểm tra bằng cách đóng sách và viết ra những gì nhớ được. Khi làm bài thi: đọc hết đề, làm câu dễ trước, phân bổ thời gian theo điểm số, dành 5–10 phút cuối kiểm tra lại.'],
            ],
            'trai-nghiem-huong-nghiep-thpt-11' => [
                1 => ['title' => 'Sáu nhóm tính cách nghề nghiệp Holland',
                    'objective' => 'Nhận biết 6 nhóm R-I-A-S-E-C và ví dụ nghề đặc trưng của mỗi nhóm.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Lý thuyết Holland chia tính cách nghề nghiệp thành 6 nhóm: Realistic (kỹ thuật – thợ, kỹ sư), Investigative (nghiên cứu – nhà khoa học, bác sĩ), Artistic (nghệ thuật – họa sĩ, nhạc sĩ), Social (xã hội – giáo viên, tư vấn), Enterprising (quản lý – doanh nhân, lãnh đạo), Conventional (nghiệp vụ – kế toán, hành chính). Mỗi người là sự kết hợp của 2–3 nhóm.'],
                2 => ['title' => 'Các lĩnh vực nghề nghiệp trong xã hội hiện đại',
                    'objective' => 'Có cái nhìn tổng quan về các lĩnh vực: công nghệ, y tế, giáo dục, kinh tế, nghệ thuật, dịch vụ.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Các lĩnh vực lớn: công nghệ thông tin (lập trình, dữ liệu, an ninh mạng), y tế – sức khỏe, giáo dục – đào tạo, kinh tế – tài chính – kinh doanh, kỹ thuật – xây dựng, nghệ thuật – truyền thông, dịch vụ – du lịch. Công nghệ và chuyển đổi số đang tạo ra nhiều nghề mới, đồng thời thay đổi yêu cầu của nghề truyền thống.'],
                3 => ['title' => 'Sở thích, năng lực và tính cách',
                    'objective' => 'Phân biệt sở thích – năng lực – tính cách; biết cách tự đánh giá bản thân khách quan.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Sở thích là điều bạn thích làm; năng lực là điều bạn làm tốt; tính cách là cách bạn hành xử ổn định. Ba yếu tố này có thể khác nhau: thích vẽ chưa chắc vẽ giỏi. Tự đánh giá qua kết quả học tập các môn, nhận xét của thầy cô bạn bè, trải nghiệm hoạt động ngoại khóa và các bài trắc nghiệm hướng nghiệp uy tín.'],
                4 => ['title' => 'Giá trị nghề nghiệp và mục tiêu cuộc đời',
                    'objective' => 'Xác định giá trị nghề nghiệp của bản thân; đặt mục tiêu theo nguyên tắc SMART.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Giá trị nghề nghiệp là điều bạn coi trọng trong công việc: thu nhập, ổn định, sáng tạo, giúp đỡ người khác, danh tiếng, tự do... Không có giá trị nào đúng cho mọi người, quan trọng là trung thực với mình. Mục tiêu SMART: cụ thể (Specific), đo được (Measurable), khả thi (Achievable), phù hợp (Relevant), có thời hạn (Time-bound).'],
            ],
            'trai-nghiem-huong-nghiep-thpt-12' => [
                1 => ['title' => 'Tiêu chí chọn ngành học phù hợp',
                    'objective' => 'Liệt kê các tiêu chí chọn ngành: bản thân, nhu cầu xã hội, điều kiện gia đình; tránh các sai lầm phổ biến.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Chọn ngành dựa trên 3 chân kiềng: hiểu mình (sở thích, năng lực), hiểu nghề (công việc thực tế, thu nhập, cơ hội), hiểu điều kiện (học lực, tài chính gia đình). Sai lầm phổ biến: chọn theo phong trào, chọn vì tên ngành nghe hay, chọn theo ý cha mẹ mà bỏ qua bản thân, chỉ nhìn điểm chuẩn năm trước.'],
                2 => ['title' => 'Tìm hiểu trường và phương thức xét tuyển',
                    'objective' => 'Biết cách tìm hiểu thông tin trường; nắm các phương thức xét tuyển đại học phổ biến.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Tìm hiểu trường qua website chính thức: chương trình đào tạo, học phí, cơ sở vật chất, việc làm sau tốt nghiệp. Các phương thức xét tuyển phổ biến: điểm thi tốt nghiệp THPT, xét học bạ, đánh giá năng lực/tư duy, xét tuyển thẳng, chứng chỉ quốc tế. Nên đăng ký nhiều nguyện vọng theo thứ tự ưu tiên từ cao đến thấp.'],
                3 => ['title' => 'Viết CV và chuẩn bị hồ sơ ấn tượng',
                    'objective' => 'Biết cấu trúc một CV tốt; chuẩn bị hồ sơ ứng tuyển học bổng/việc làm đầy đủ.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'CV tốt gồm: thông tin liên hệ, mục tiêu ngắn gọn, học vấn, kinh nghiệm/hoạt động, kỹ năng, chứng chỉ. Nguyên tắc: trung thực tuyệt đối, gọn trong 1–2 trang, không lỗi chính tả, dùng động từ hành động và con số cụ thể. Hồ sơ ứng tuyển thường gồm CV, thư giới thiệu bản thân, bằng cấp/chứng chỉ và ảnh chân dung lịch sự.'],
                4 => ['title' => 'Kỹ năng trả lời phỏng vấn',
                    'objective' => 'Chuẩn bị câu trả lời cho câu hỏi thường gặp; thể hiện tác phong chuyên nghiệp khi phỏng vấn.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Chuẩn bị trước: tìm hiểu về trường/công ty, luyện trả lời các câu hỏi thường gặp (giới thiệu bản thân, điểm mạnh – điểm yếu, vì sao chọn chúng tôi). Trong phỏng vấn: đến sớm, ăn mặc lịch sự, giao tiếp bằng mắt, trả lời ngắn gọn có ví dụ cụ thể, đặt câu hỏi ngược lại khi được mời. Sau phỏng vấn, gửi thư cảm ơn là điểm cộng lớn.'],
            ],
        ];

        return $plan[$topicSlug][$num];
    }

    // ================= ÂM NHẠC – LỚP 10 =================

    private function seedAmNhac101(): void
    {
        $L = 'am-nhac-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hợp âm ba (triad) gồm mấy nốt?',
                ['2 nốt', '3 nốt', '4 nốt', '5 nốt'], 1,
                'Hợp âm ba gồm 3 nốt xếp chồng theo quãng 3: nốt gốc, nốt bậc 3 và nốt bậc 5.');
            $this->quiz($L, 'Hợp âm Đô trưởng (C) gồm các nốt nào?',
                ['C – E – G', 'C – Eb – G', 'C – D – G', 'C – E – A'], 0,
                'Hợp âm trưởng = quãng 3 trưởng + quãng 5 đúng, nên Đô trưởng gồm C – E – G.');
            $this->quiz($L, 'Hợp âm Đô thứ (Cm) khác Đô trưởng ở điểm nào?',
                ['Nốt bậc 3 hạ xuống nửa cung (Eb thay cho E)', 'Nốt bậc 5 hạ xuống nửa cung',
                 'Thêm một nốt bậc 7', 'Bỏ nốt bậc 5'], 0,
                'Hợp âm thứ dùng quãng 3 thứ nên nốt bậc 3 hạ nửa cung: C – Eb – G.', 'trung_binh');
            $this->quiz($L, 'Nốt thấp nhất trong hợp âm ba được gọi là gì?',
                ['Nốt gốc (root)', 'Nốt bậc 3', 'Nốt bậc 5', 'Nốt quãng 8'], 0,
                'Nốt gốc là nốt thấp nhất và cũng là nốt đặt tên cho hợp âm.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi ký hiệu với tên gọi đúng của nó.',
                [['C', 'Đô trưởng'], ['Am', 'La thứ'], ['G7', 'Sol 7'], ['Dm', 'Rê thứ']],
                'C = Đô trưởng, Am = La thứ, G7 = Sol 7, Dm = Rê thứ.');
            $this->matching($L, 'Nối mỗi hợp âm với cấu tạo nốt đúng của nó.',
                [['C (Đô trưởng)', 'C – E – G'], ['Cm (Đô thứ)', 'C – Eb – G'],
                 ['G (Sol trưởng)', 'G – B – D'], ['Am (La thứ)', 'A – C – E']],
                'Hợp âm trưởng: nốt bậc 3 trưởng; hợp âm thứ: nốt bậc 3 hạ nửa cung.', 'trung_binh');
            $this->matching($L, 'Nối mỗi thuật ngữ với ý nghĩa của nó.',
                [['Nốt gốc (root)', 'Nốt thấp nhất, đặt tên cho hợp âm'],
                 ['Quãng 3 trưởng', 'Khoảng cách 2 cung, ví dụ C – E'],
                 ['Quãng 5 đúng', 'Khoảng cách 3,5 cung, ví dụ C – G'],
                 ['Triad', 'Hợp âm ba']],
                'Nắm chắc thuật ngữ giúp đọc hiểu tài liệu nhạc lý dễ dàng.');
            $this->matching($L, 'Nối mỗi chữ cái ký hiệu với nốt nhạc tương ứng.',
                [['C', 'Đô'], ['D', 'Rê'], ['F', 'Fa'], ['G', 'Sol']],
                'Thứ tự chữ cái: C = Đô, D = Rê, E = Mi, F = Fa, G = Sol, A = La, B = Si.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hợp âm vào nhóm HỢP ÂM TRƯỞNG hoặc HỢP ÂM THỨ.',
                [['C', 'Hợp âm trưởng'], ['G', 'Hợp âm trưởng'], ['F', 'Hợp âm trưởng'],
                 ['Am', 'Hợp âm thứ'], ['Dm', 'Hợp âm thứ'], ['Em', 'Hợp âm thứ']],
                'Ký hiệu không có chữ m là trưởng; có chữ m là thứ.');
            $this->sortQ($L, 'Kéo mỗi cách gọi tên vào nhóm ĐÚNG hoặc SAI.',
                [['C = Đô trưởng', 'Đúng'], ['Cm = Đô thứ', 'Đúng'], ['F = Fa trưởng', 'Đúng'],
                 ['Am = La trưởng', 'Sai'], ['G7 = Sol thứ', 'Sai'], ['Em = Mi trưởng', 'Sai']],
                'Am là La thứ, G7 là Sol 7, Em là Mi thứ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cặp nốt vào nhóm QUÃNG 3 hoặc QUÃNG 5.',
                [['C – E', 'Quãng 3'], ['C – Eb', 'Quãng 3'], ['G – B', 'Quãng 3'],
                 ['C – G', 'Quãng 5'], ['A – E', 'Quãng 5'], ['D – A', 'Quãng 5']],
                'Quãng 3 là khoảng cách giữa nốt gốc và nốt bậc 3; quãng 5 là giữa nốt gốc và nốt bậc 5.');
            $this->sortQ($L, 'Kéo mỗi cấu tạo nốt vào nhóm HỢP ÂM TRƯỞNG hoặc HỢP ÂM THỨ.',
                [['C – E – G', 'Trưởng'], ['F – A – C', 'Trưởng'], ['G – B – D', 'Trưởng'],
                 ['A – C – E', 'Thứ'], ['D – F – A', 'Thứ'], ['E – G – B', 'Thứ']],
                'Nốt bậc 3 trưởng (cách nốt gốc 2 cung) cho hợp âm trưởng; bậc 3 thứ (1,5 cung) cho hợp âm thứ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hợp âm ba gồm ___ nốt xếp chồng theo quãng 3.', [[0, '3']],
                'Triad = hợp âm 3 nốt: nốt gốc, nốt bậc 3, nốt bậc 5.');
            $this->fill($L, 'Hợp âm Đô trưởng được ký hiệu quốc tế là ___.', [[0, 'C']],
                'Chữ C tương ứng với nốt Đô; không có chữ m nên là hợp âm trưởng.');
            $this->fill($L, 'Chữ m trong ký hiệu Am cho biết đây là hợp âm ___ (trưởng/thứ).', [[0, 'thứ']],
                'Chữ m viết sau tên nốt nghĩa là minor – hợp âm thứ.');
            $this->fill($L, 'Nốt thấp nhất, đặt tên cho hợp âm được gọi là nốt ___.', [[0, 'gốc']],
                'Nốt gốc (root) là nền tảng của hợp âm.');
        }
    }

    private function seedAmNhac102(): void
    {
        $L = 'am-nhac-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Vòng hợp âm C – G – Am – F gồm các bậc nào trong giọng Đô trưởng?',
                ['I – V – vi – IV', 'I – IV – V – vi', 'ii – V – I – vi', 'I – vi – IV – V'], 0,
                'Trong giọng Đô trưởng: C là bậc I, G là bậc V, Am là bậc vi, F là bậc IV.', 'trung_binh');
            $this->quiz($L, 'Chữ cái B trong ký hiệu quốc tế tương ứng với nốt nào?',
                ['Si', 'La', 'Mi', 'Đô'], 0,
                'Thứ tự: A = La, B = Si, C = Đô, D = Rê, E = Mi, F = Fa, G = Sol.');
            $this->quiz($L, 'Hợp âm Dm là hợp âm gì?',
                ['Rê thứ', 'Rê trưởng', 'Rê 7', 'Đô thứ'], 0,
                'D = Rê, chữ m nghĩa là thứ, vậy Dm là Rê thứ.');
            $this->quiz($L, 'Số 7 trong ký hiệu G7 có ý nghĩa gì?',
                ['Hợp âm có thêm nốt bậc 7', 'Hợp âm gồm 7 nốt', 'Chơi hợp âm 7 lần', 'Nhịp 7/8'], 0,
                'Số 7 nghĩa là hợp âm 7: hợp âm ba thêm nốt bậc 7, âm sắc căng hơn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chữ cái với nốt nhạc tương ứng.',
                [['A', 'La'], ['B', 'Si'], ['E', 'Mi'], ['D', 'Rê']],
                'A = La, B = Si, E = Mi, D = Rê.');
            $this->matching($L, 'Nối mỗi ký hiệu với tên gọi đúng của nó.',
                [['F', 'Fa trưởng'], ['Em', 'Mi thứ'], ['D7', 'Rê 7'], ['Bm', 'Si thứ']],
                'F = Fa trưởng, Em = Mi thứ, D7 = Rê 7, Bm = Si thứ.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Vòng hợp âm', 'Chuỗi hợp âm lặp lại tạo khung cho bài hát'],
                 ['I – IV – V', 'Vòng hợp âm cơ bản với 3 hợp âm'],
                 ['Hợp âm 7', 'Hợp âm ba thêm nốt bậc 7, âm sắc căng hơn'],
                 ['Tonic', 'Hợp âm chủ (bậc I), tạo cảm giác kết thúc']],
                'Vòng hợp âm là khung xương của hầu hết các bài nhạc nhẹ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hoạt động đệm hát với mô tả đúng.',
                [['Đệm hát', 'Chơi hợp âm theo vòng để đệm cho giọng hát'],
                 ['Chuyển hợp âm', 'Đổi hợp âm đúng nhịp khi đệm'],
                 ['Vòng C – G – Am – F', 'Vòng I – V – vi – IV rất phổ biến'],
                 ['Hợp âm chủ', 'Hợp âm bậc I, điểm tựa của bài hát']],
                'Đệm hát tốt cần chuyển hợp âm mượt mà, đúng nhịp.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hợp âm vào nhóm TRƯỞNG hoặc THỨ.',
                [['D', 'Trưởng'], ['Bb', 'Trưởng'], ['E', 'Trưởng'],
                 ['Cm', 'Thứ'], ['Fm', 'Thứ'], ['Gm', 'Thứ']],
                'Không có chữ m là trưởng; có chữ m là thứ. Bb là Si giáng trưởng.');
            $this->sortQ($L, 'Kéo mỗi cách gọi tên vào nhóm ĐÚNG hoặc SAI.',
                [['B = Si trưởng', 'Đúng'], ['A = La trưởng', 'Đúng'], ['G = Sol trưởng', 'Đúng'],
                 ['Dm = Rê trưởng', 'Sai'], ['E7 = Mi trưởng', 'Sai'], ['Am = La thứ', 'Đúng']],
                'Dm là Rê thứ, E7 là Mi 7 (không phải Mi trưởng).');
            $this->sortQ($L, 'Kéo mỗi hợp âm vào nhóm CÓ SỐ 7 hoặc KHÔNG CÓ SỐ 7.',
                [['G7', 'Có số 7'], ['D7', 'Có số 7'], ['A7', 'Có số 7'],
                 ['C', 'Không có số 7'], ['Am', 'Không có số 7'], ['F', 'Không có số 7']],
                'Số 7 cho biết hợp âm có thêm nốt bậc 7.');
            $this->sortQ($L, 'Kéo mỗi hợp âm vào đúng bậc trong giọng Đô trưởng.',
                [['C', 'Bậc I'], ['F', 'Bậc IV'], ['G', 'Bậc V'],
                 ['Am', 'Bậc vi'], ['Dm', 'Bậc ii'], ['Em', 'Bậc iii']],
                'Giọng Đô trưởng: C = I, Dm = ii, Em = iii, F = IV, G = V, Am = vi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trong giọng Đô trưởng, hợp âm bậc V là ___.', [[0, 'G']],
                'Bậc V của giọng Đô trưởng là Sol, ký hiệu G.');
            $this->fill($L, 'Ký hiệu quốc tế của nốt La là chữ ___.', [[0, 'A']],
                'A = La trong hệ ký hiệu chữ cái quốc tế.');
            $this->fill($L, 'Vòng I – V – vi – IV trong giọng Đô trưởng là C – G – ___ – F.', [[0, 'Am']],
                'Bậc vi của giọng Đô trưởng là La thứ, ký hiệu Am.');
            $this->fill($L, 'Hợp âm Rê 7 được ký hiệu là ___.', [[0, 'D7']],
                'D = Rê, số 7 cho biết đây là hợp âm 7.');
        }
    }

    private function seedAmNhac103(): void
    {
        $L = 'am-nhac-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khóa Sol còn được gọi là gì?',
                ['Khóa Treble', 'Khóa Bass', 'Khóa Alto', 'Khóa Tenor'], 0,
                'Khóa Sol còn gọi là khóa Treble, dùng cho giọng cao và tay phải piano.');
            $this->quiz($L, 'Khuông nhạc có bao nhiêu dòng kẻ?',
                ['4 dòng', '5 dòng', '6 dòng', '3 dòng'], 1,
                'Khuông nhạc chuẩn gồm 5 dòng kẻ và 4 khe.');
            $this->quiz($L, 'Nhịp 3/4 có nghĩa là gì?',
                ['Mỗi ô nhịp có 3 phách, mỗi phách bằng một nốt đen',
                 'Mỗi ô nhịp có 4 phách', 'Bản nhạc có 3 ô nhịp',
                 'Mỗi ô nhịp có 3 phách, mỗi phách bằng một nốt móc đơn'], 0,
                'Số trên cho biết số phách mỗi ô nhịp, số dưới cho biết trường độ mỗi phách.', 'trung_binh');
            $this->quiz($L, 'Nhịp nào sau đây là nhịp kép?',
                ['6/8', '2/4', '3/4', '4/4'], 0,
                'Nhịp kép (6/8, 9/8, 12/8) có mỗi phách chia làm 3 phần bằng nhau.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Khóa Sol', 'Dùng cho giọng cao, tay phải piano'],
                 ['Khóa Fa', 'Dùng cho giọng trầm, tay trái piano'],
                 ['Khuông nhạc', 'Gồm 5 dòng kẻ và 4 khe'],
                 ['Ô nhịp', 'Đoạn nhạc giữa hai vạch nhịp']],
                'Khóa nhạc xác định vị trí các nốt trên khuông nhạc.');
            $this->matching($L, 'Nối mỗi số chỉ nhịp với ý nghĩa của nó.',
                [['2/4', 'Nhịp đơn: 2 phách mỗi ô nhịp'],
                 ['3/4', 'Nhịp đơn: 3 phách mỗi ô nhịp'],
                 ['6/8', 'Nhịp kép: mỗi phách chia làm 3'],
                 ['4/4', 'Nhịp đơn phổ biến nhất']],
                'Nhịp đơn mỗi phách chia 2; nhịp kép mỗi phách chia 3.', 'trung_binh');
            $this->matching($L, 'Nối mỗi vị trí trên khóa Sol với nốt nhạc đúng.',
                [['Dòng 1 (dưới cùng)', 'Nốt Mi'], ['Khe 1', 'Nốt Fa'],
                 ['Dòng 2', 'Nốt Sol'], ['Khe 2', 'Nốt La']],
                'Khóa Sol: các dòng là Mi – Sol – Si – Rê – Fa; các khe là Fa – La – Đô – Mi.', 'trung_binh');
            $this->matching($L, 'Nối mỗi loại nốt với trường độ của nó trong nhịp 4/4.',
                [['Nốt tròn', 'Dài 4 phách'], ['Nốt trắng', 'Dài 2 phách'],
                 ['Nốt đen', 'Dài 1 phách'], ['Nốt móc đơn', 'Dài nửa phách']],
                'Nốt tròn = 2 nốt trắng = 4 nốt đen = 8 nốt móc đơn.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi số chỉ nhịp vào nhóm NHỊP ĐƠN hoặc NHỊP KÉP.',
                [['2/4', 'Nhịp đơn'], ['3/4', 'Nhịp đơn'], ['4/4', 'Nhịp đơn'],
                 ['6/8', 'Nhịp kép'], ['9/8', 'Nhịp kép'], ['12/8', 'Nhịp kép']],
                'Nhịp đơn: mẫu số là 4; nhịp kép: mẫu số là 8 và tử số chia hết cho 3.');
            $this->sortQ($L, 'Kéo mỗi đối tượng vào nhóm dùng KHÓA SOL hoặc KHÓA FA.',
                [['Giọng nữ cao', 'Khóa Sol'], ['Violin', 'Khóa Sol'], ['Tay phải piano', 'Khóa Sol'],
                 ['Giọng nam trầm', 'Khóa Fa'], ['Cello', 'Khóa Fa'], ['Tay trái piano', 'Khóa Fa']],
                'Khóa Sol cho âm vực cao, khóa Fa cho âm vực trầm.');
            $this->sortQ($L, 'Kéo mỗi loại nốt vào nhóm trường độ DÀI, TRUNG BÌNH hoặc NGẮN.',
                [['Nốt tròn', 'Dài'], ['Nốt trắng', 'Dài'],
                 ['Nốt đen', 'Trung bình'], ['Dấu lặng đen', 'Trung bình'],
                 ['Nốt móc đơn', 'Ngắn'], ['Nốt móc kép', 'Ngắn']],
                'So sánh tương đối: tròn > trắng > đen > móc đơn > móc kép.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Khuông nhạc có 5 dòng kẻ', 'Đúng'], ['Nhịp 6/8 là nhịp kép', 'Đúng'],
                 ['Nốt đen dài 1 phách', 'Đúng'], ['Vạch nhịp chia các ô nhịp', 'Đúng'],
                 ['Khóa Fa dùng cho giọng cao', 'Sai'], ['Nhịp 3/4 có 4 phách', 'Sai']],
                'Khóa Fa dùng cho giọng trầm; nhịp 3/4 có 3 phách mỗi ô nhịp.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Khuông nhạc gồm 5 dòng kẻ và ___ khe.', [[0, '4']],
                '5 dòng kẻ tạo thành 4 khe ở giữa.');
            $this->fill($L, 'Khóa dùng cho tay phải đàn piano là khóa ___.', [[0, 'Sol']],
                'Tay phải chơi ở âm vực cao nên dùng khóa Sol.');
            $this->fill($L, 'Nhịp 6/8 là nhịp ___ (đơn/kép).', [[0, 'kép']],
                'Mẫu số là 8 và mỗi phách chia 3 nên 6/8 là nhịp kép.');
            $this->fill($L, 'Trong nhịp 4/4, nốt tròn dài ___ phách.', [[0, '4']],
                'Nốt tròn là nốt dài nhất, bằng 4 phách trong nhịp 4/4.');
        }
    }

    private function seedAmNhac104(): void
    {
        $L = 'am-nhac-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dấu thăng (#) có tác dụng gì?',
                ['Nâng nốt lên nửa cung', 'Hạ nốt xuống nửa cung',
                 'Hủy dấu hóa trước đó', 'Kéo dài trường độ nốt'], 0,
                'Dấu thăng nâng cao độ của nốt lên nửa cung, ví dụ Fa thành Fa thăng.');
            $this->quiz($L, 'Dấu bình (♮) có tác dụng gì?',
                ['Hủy tác dụng của dấu thăng/giáng trước đó', 'Nâng nốt lên một cung',
                 'Hạ nốt xuống một cung', 'Lặp lại nốt trước đó'], 0,
                'Dấu bình trả nốt về cao độ tự nhiên, hủy thăng hoặc giáng.');
            $this->quiz($L, 'Bộ khóa (dấu hóa ghi ở đầu khuông nhạc) có hiệu lực trong phạm vi nào?',
                ['Cả bản nhạc', 'Chỉ một ô nhịp', 'Chỉ một nốt nhạc', 'Chỉ một dòng nhạc'], 0,
                'Bộ khóa áp dụng cho mọi nốt tương ứng trong toàn bộ bản nhạc.', 'trung_binh');
            $this->quiz($L, 'Kỹ thuật nào giúp đọc bản nhạc nhanh và chính xác?',
                ['Đọc theo cụm nốt và nhìn trước 1–2 ô nhịp', 'Đọc từng nốt một thật chậm',
                 'Chỉ nhìn nốt, bỏ qua số chỉ nhịp', 'Bỏ qua mọi dấu hóa'], 0,
                'Đọc theo cụm, nhìn trước và nhận diện mẫu quen thuộc là bí quyết đọc lướt.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dấu hóa với tác dụng của nó.',
                [['Dấu thăng (#)', 'Nâng nốt lên nửa cung'],
                 ['Dấu giáng (b)', 'Hạ nốt xuống nửa cung'],
                 ['Dấu bình (♮)', 'Hủy thăng/giáng trước đó'],
                 ['Bộ khóa', 'Dấu hóa ở đầu khuông, hiệu lực cả bản nhạc']],
                'Thăng nâng, giáng hạ, bình hủy; bộ khóa áp dụng toàn bản nhạc.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Nửa cung', 'Khoảng cách nhỏ nhất giữa hai nốt kề, ví dụ Mi – Fa'],
                 ['Một cung', 'Bằng hai nửa cung, ví dụ Đô – Rê'],
                 ['Dấu hóa bất thường', 'Dấu hóa chỉ hiệu lực trong ô nhịp đó'],
                 ['Nhịp đổi', 'Bản nhạc thay đổi số chỉ nhịp giữa chừng']],
                'Mi – Fa và Si – Đô là hai cặp nửa cung tự nhiên.', 'trung_binh');
            $this->matching($L, 'Nối mỗi kỹ thuật đọc nhanh với mô tả đúng.',
                [['Đọc theo cụm', 'Nhận diện nhóm nốt quen thuộc thay vì từng nốt'],
                 ['Nhìn trước', 'Mắt đi trước tay 1–2 ô nhịp'],
                 ['Nhận diện mẫu hợp âm', 'Thấy thế hợp âm quen thuộc là chơi ngay'],
                 ['Giữ nhịp ổn định', 'Không dừng lại khi đọc sai một nốt']],
                'Đọc lướt là kỹ năng quan trọng của người chơi nhạc.', 'trung_binh');
            $this->matching($L, 'Nối mỗi ký hiệu với tên gọi đúng.',
                [['F#', 'Fa thăng'], ['Bb', 'Si giáng'], ['C#', 'Đô thăng'], ['Eb', 'Mi giáng']],
                '# là thăng, b là giáng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi dấu hóa vào nhóm NÂNG, HẠ hoặc HỦY cao độ.',
                [['# (thăng)', 'Nâng lên nửa cung'], ['x (thăng kép)', 'Nâng lên nửa cung'],
                 ['b (giáng)', 'Hạ xuống nửa cung'], ['bb (giáng kép)', 'Hạ xuống nửa cung'],
                 ['♮ (bình)', 'Hủy dấu hóa'], ['♮ sau dấu #', 'Hủy dấu hóa']],
                'Thăng (kể cả thăng kép) nâng cao độ; giáng hạ cao độ; bình hủy bỏ.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Dấu thăng nâng nốt lên nửa cung', 'Đúng'], ['Dấu bình hủy dấu thăng', 'Đúng'],
                 ['Nhịp kép mỗi phách chia làm 3', 'Đúng'], ['9/8 là nhịp kép', 'Đúng'],
                 ['Bộ khóa chỉ hiệu lực một ô nhịp', 'Sai'], ['Đọc nhanh nên dừng lại khi sai nốt', 'Sai']],
                'Bộ khóa hiệu lực cả bản nhạc; khi đọc sai nốt vẫn phải giữ nhịp đi tiếp.');
            $this->sortQ($L, 'Kéo mỗi cặp nốt vào nhóm NỬA CUNG hoặc MỘT CUNG.',
                [['Mi – Fa', 'Nửa cung'], ['Si – Đô', 'Nửa cung'],
                 ['Đô – Rê', 'Một cung'], ['Fa – Sol', 'Một cung'],
                 ['La – Si', 'Một cung'], ['Mi – Fa#', 'Một cung']],
                'Mi – Fa và Si – Đô là nửa cung tự nhiên; Fa# cách Mi một cung.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi số chỉ nhịp vào nhóm NHỊP ĐƠN, NHỊP KÉP hoặc NHỊP HỖN HỢP.',
                [['2/4', 'Nhịp đơn'], ['3/4', 'Nhịp đơn'],
                 ['6/8', 'Nhịp kép'], ['9/8', 'Nhịp kép'],
                 ['5/4', 'Nhịp hỗn hợp'], ['7/8', 'Nhịp hỗn hợp']],
                'Nhịp hỗn hợp (5/4, 7/8...) là sự kết hợp của nhịp đơn và nhịp kép.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dấu ___ nâng nốt nhạc lên nửa cung.', [[0, 'thăng']],
                'Dấu thăng (#) nâng cao độ nốt lên nửa cung.');
            $this->fill($L, 'Dấu bình được ký hiệu là ___.', [[0, '♮']],
                'Ký hiệu ♮ hủy tác dụng của dấu thăng hoặc giáng trước đó.');
            $this->fill($L, 'Khoảng cách giữa nốt Mi và nốt Fa là ___ cung.', [[0, 'nửa']],
                'Mi – Fa là cặp nửa cung tự nhiên.');
            $this->fill($L, 'Để đọc nhanh, nên đọc theo ___ nốt thay vì đọc từng nốt một.', [[0, 'cụm']],
                'Đọc theo cụm giúp mắt nhận diện mẫu quen thuộc nhanh hơn.');
        }
    }

    // ================= ÂM NHẠC – LỚP 11 =================

    private function seedAmNhac111(): void
    {
        $L = 'am-nhac-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nhạc cách mạng Việt Nam ra đời trong bối cảnh nào?',
                ['Hai cuộc kháng chiến chống Pháp và chống Mỹ', 'Thời kỳ đổi mới',
                 'Thời Pháp thuộc trước năm 1945', 'Thời kỳ hội nhập quốc tế'], 0,
                'Nhạc cách mạng ra đời và phát triển mạnh trong hai cuộc kháng chiến.');
            $this->quiz($L, 'Quốc ca của Việt Nam có tên là gì?',
                ['Tiến quân ca', 'Giải phóng miền Nam', 'Như có Bác Hồ', 'Hồn tử sĩ'], 0,
                'Tiến quân ca được chọn làm Quốc ca của Việt Nam.');
            $this->quiz($L, 'Nhạc tiền chiến là dòng nhạc của thời kỳ nào?',
                ['Trước năm 1945', '1945 – 1954', '1954 – 1975', 'Sau năm 1975'], 0,
                'Nhạc tiền chiến là dòng tân nhạc trước Cách mạng Tháng Tám năm 1945.');
            $this->quiz($L, 'Đặc điểm nội dung nổi bật của nhạc cách mạng là gì?',
                ['Ca ngợi quê hương, cổ vũ chiến đấu', 'Tình yêu đôi lứa lãng mạn',
                 'Hài hước châm biếm', 'Triết lý trừu tượng'], 0,
                'Nhạc cách mạng ca ngợi quê hương đất nước và cổ vũ tinh thần chiến đấu.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dòng nhạc với đặc điểm của nó.',
                [['Nhạc cách mạng', 'Ra đời trong kháng chiến, cổ vũ chiến đấu'],
                 ['Nhạc tiền chiến', 'Trước 1945, màu sắc lãng mạn trữ tình'],
                 ['Tiến quân ca', 'Quốc ca Việt Nam'],
                 ['Tân nhạc', 'Nhạc Việt Nam hiện đại theo lối phương Tây']],
                'Hai dòng nhạc đều có giá trị lịch sử và nghệ thuật lớn.');
            $this->matching($L, 'Nối mỗi nội dung với dòng nhạc tiêu biểu cho nó.',
                [['Ca ngợi quê hương', 'Nhạc cách mạng'],
                 ['Tình yêu lãng mạn', 'Nhạc tiền chiến'],
                 ['Cổ vũ chiến đấu', 'Nhạc cách mạng'],
                 ['Trữ tình da diết', 'Nhạc tiền chiến']],
                'Nội dung phản ánh bối cảnh lịch sử của từng dòng nhạc.');
            $this->matching($L, 'Nối mỗi mốc thời gian với sự kiện tương ứng.',
                [['Trước 1945', 'Thời kỳ nhạc tiền chiến'],
                 ['1945', 'Cách mạng Tháng Tám thành công'],
                 ['1946 – 1954', 'Kháng chiến chống Pháp'],
                 ['1954 – 1975', 'Kháng chiến chống Mỹ']],
                'Dòng nhạc cách mạng gắn liền với các giai đoạn kháng chiến.');
            $this->matching($L, 'Nối mỗi thành phần của bài hát với vai trò của nó.',
                [['Giai điệu', 'Phần nhạc, đường nét âm thanh'],
                 ['Lời ca', 'Phần lời của bài hát'],
                 ['Nhạc sĩ', 'Người sáng tác'],
                 ['Ca sĩ', 'Người thể hiện']],
                'Một bài hát hoàn chỉnh gồm giai điệu và lời ca.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào đúng dòng nhạc: NHẠC CÁCH MẠNG hoặc NHẠC TIỀN CHIẾN.',
                [['Ca ngợi chiến đấu', 'Nhạc cách mạng'], ['Cổ vũ tinh thần kháng chiến', 'Nhạc cách mạng'],
                 ['Gắn với hai cuộc kháng chiến', 'Nhạc cách mạng'],
                 ['Lãng mạn trữ tình', 'Nhạc tiền chiến'], ['Trước năm 1945', 'Nhạc tiền chiến'],
                 ['Ca ngợi tình yêu quê hương', 'Nhạc tiền chiến']],
                'Nhạc cách mạng hào hùng, cổ vũ; nhạc tiền chiến lãng mạn, trữ tình.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Tiến quân ca là Quốc ca Việt Nam', 'Đúng'], ['Nhạc cách mạng gắn với kháng chiến', 'Đúng'],
                 ['Nhạc sĩ là người sáng tác', 'Đúng'], ['Tân nhạc chịu ảnh hưởng âm nhạc phương Tây', 'Đúng'],
                 ['Nhạc tiền chiến ra đời sau 1975', 'Sai'], ['Ca sĩ là người sáng tác nhạc', 'Sai']],
                'Nhạc tiền chiến ra đời trước 1945; ca sĩ là người thể hiện tác phẩm.');
            $this->sortQ($L, 'Kéo mỗi sự kiện/dòng nhạc vào nhóm TRƯỚC 1945 hoặc SAU 1945.',
                [['Nhạc tiền chiến', 'Trước 1945'], ['Tân nhạc hình thành', 'Trước 1945'],
                 ['Phong trào lãng mạn', 'Trước 1945'],
                 ['Tiến quân ca thành Quốc ca', 'Sau 1945'], ['Nhạc kháng chiến chống Pháp', 'Sau 1945'],
                 ['Nhạc kháng chiến chống Mỹ', 'Sau 1945']],
                'Năm 1945 là mốc phân chia hai giai đoạn âm nhạc.');
            $this->sortQ($L, 'Kéo mỗi vai trò vào nhóm NGƯỜI SÁNG TÁC, NGƯỜI THỂ HIỆN hoặc NGƯỜI THƯỞNG THỨC.',
                [['Nhạc sĩ', 'Người sáng tác'], ['Người viết lời', 'Người sáng tác'],
                 ['Ca sĩ', 'Người thể hiện'], ['Dàn nhạc', 'Người thể hiện'],
                 ['Khán giả', 'Người thưởng thức'], ['Người nghe đài', 'Người thưởng thức']],
                'Chu trình âm nhạc: sáng tác – thể hiện – thưởng thức.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Quốc ca Việt Nam có tên là Tiến ___ ca.', [[0, 'quân']],
                'Tiến quân ca là Quốc ca chính thức của Việt Nam.');
            $this->fill($L, 'Nhạc tiền chiến là dòng nhạc trước năm ___.', [[0, '1945']],
                'Tiền chiến nghĩa là trước chiến tranh, tức trước năm 1945.');
            $this->fill($L, 'Người sáng tác âm nhạc được gọi là nhạc ___.', [[0, 'sĩ']],
                'Nhạc sĩ là người viết nên giai điệu và lời ca.');
            $this->fill($L, 'Nhạc cách mạng ra đời trong các cuộc ___ chiến.', [[0, 'kháng']],
                'Kháng chiến chống Pháp và chống Mỹ là bối cảnh của nhạc cách mạng.');
        }
    }

    private function seedAmNhac112(): void
    {
        $L = 'am-nhac-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nhạc trữ tình (bolero) có đặc điểm giai điệu nào?',
                ['Chậm rãi, da diết', 'Nhanh, sôi động', 'Không có giai điệu', 'Tiết tấu phức tạp'], 0,
                'Nhạc trữ tình có giai điệu chậm, lời ca da diết về tình yêu quê hương.');
            $this->quiz($L, 'Nhạc trẻ thường gắn với đời sống nào?',
                ['Đô thị hiện đại', 'Nông thôn xưa', 'Chiến trường', 'Cung đình'], 0,
                'Nhạc trẻ phản ánh đời sống đô thị hiện đại với tiết tấu sôi động.');
            $this->quiz($L, 'Dòng nhạc dân gian đương đại sử dụng chất liệu gì?',
                ['Dân ca và nhạc cụ dân tộc phối với hòa âm hiện đại', 'Chỉ dùng nhạc cụ phương Tây',
                 'Chỉ hát không có nhạc đệm', 'Nhạc cổ điển nguyên bản'], 0,
                'Dân gian đương đại kết hợp chất liệu truyền thống với hòa âm hiện đại.');
            $this->quiz($L, 'Hòa âm trong nhạc trẻ hiện nay theo xu hướng nào?',
                ['Xu hướng quốc tế', 'Chỉ theo lối cổ điển', 'Không dùng hòa âm', 'Chỉ dùng một hợp âm'], 0,
                'Nhạc trẻ Việt Nam hòa âm theo xu hướng âm nhạc quốc tế.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dòng nhạc với đặc điểm của nó.',
                [['Nhạc trữ tình', 'Giai điệu chậm, lời ca da diết'],
                 ['Nhạc trẻ', 'Tiết tấu sôi động, gắn đời sống đô thị'],
                 ['Dân gian đương đại', 'Chất liệu dân ca kết hợp hòa âm hiện đại'],
                 ['Bolero', 'Tên gọi phổ biến của nhạc trữ tình']],
                'Mỗi dòng nhạc phục vụ một nhu cầu thưởng thức khác nhau.');
            $this->matching($L, 'Nối mỗi thuật ngữ âm nhạc với ý nghĩa của nó.',
                [['Phối khí', 'Cách sắp xếp các bè nhạc cụ cho bài hát'],
                 ['Hòa âm', 'Cách dùng hợp âm làm nền cho giai điệu'],
                 ['Tiết tấu', 'Nhịp điệu nhanh chậm của bài hát'],
                 ['Ca từ', 'Lời ca của bài hát']],
                'Phối khí và hòa âm quyết định màu sắc hiện đại của bài hát.');
            $this->matching($L, 'Nối mỗi nhạc cụ với đặc điểm của nó.',
                [['Đàn bầu', 'Nhạc cụ dân tộc một dây'],
                 ['Sáo trúc', 'Nhạc cụ hơi dân tộc'],
                 ['Trống cơm', 'Nhạc cụ gõ dân tộc'],
                 ['Đàn tranh', 'Nhạc cụ dây gảy dân tộc']],
                'Nhạc cụ dân tộc là chất liệu quý của dòng nhạc dân gian đương đại.');
            $this->matching($L, 'Nối mỗi tính từ với đối tượng nó thường mô tả.',
                [['Da diết', 'Cảm xúc của nhạc trữ tình'],
                 ['Sôi động', 'Không khí của nhạc trẻ'],
                 ['Hiện đại', 'Phong cách hòa âm mới'],
                 ['Truyền thống', 'Chất liệu dân gian']],
                'Tính từ giúp diễn đạt cảm nhận âm nhạc chính xác hơn.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào đúng dòng nhạc: NHẠC TRỮ TÌNH hoặc NHẠC TRẺ.',
                [['Giai điệu chậm', 'Nhạc trữ tình'], ['Lời ca da diết', 'Nhạc trữ tình'],
                 ['Bolero', 'Nhạc trữ tình'],
                 ['Tiết tấu sôi động', 'Nhạc trẻ'], ['Hòa âm xu hướng quốc tế', 'Nhạc trẻ'],
                 ['Gắn với đời sống đô thị', 'Nhạc trẻ']],
                'Trữ tình chậm và da diết; nhạc trẻ nhanh và hiện đại.');
            $this->sortQ($L, 'Kéo mỗi nhạc cụ vào nhóm DÂN TỘC hoặc PHƯƠNG TÂY.',
                [['Đàn bầu', 'Dân tộc'], ['Sáo trúc', 'Dân tộc'], ['Đàn tranh', 'Dân tộc'],
                 ['Piano', 'Phương Tây'], ['Violin', 'Phương Tây'], ['Guitar điện', 'Phương Tây']],
                'Nhạc cụ dân tộc làm nên màu sắc riêng của âm nhạc Việt Nam.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nhạc trữ tình còn gọi là bolero', 'Đúng'],
                 ['Dân gian đương đại dùng chất liệu dân ca', 'Đúng'],
                 ['Nhạc trẻ có tiết tấu sôi động', 'Đúng'],
                 ['Phối khí là cách sắp xếp bè nhạc cụ', 'Đúng'],
                 ['Nhạc trẻ không dùng hòa âm', 'Sai'], ['Đàn bầu có 16 dây', 'Sai']],
                'Nhạc trẻ vẫn dùng hòa âm; đàn bầu chỉ có một dây.');
            $this->sortQ($L, 'Kéo mỗi dòng nhạc/hình thức vào nhóm TỐC ĐỘ CHẬM hoặc NHANH.',
                [['Nhạc trữ tình', 'Chậm'], ['Bolero', 'Chậm'], ['Dân ca ru con', 'Chậm'],
                 ['Nhạc dance', 'Nhanh'], ['Nhạc rock', 'Nhanh'], ['EDM', 'Nhanh']],
                'Tốc độ là đặc điểm dễ nhận biết nhất của dòng nhạc.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nhạc trữ tình còn được gọi là nhạc ___.', [[0, 'bolero']],
                'Bolero là tên gọi phổ biến của dòng nhạc trữ tình.');
            $this->fill($L, 'Dòng nhạc dùng chất liệu dân ca phối hòa âm hiện đại gọi là dân gian ___ đại.', [[0, 'đương']],
                'Đương đại nghĩa là của thời hiện nay.');
            $this->fill($L, '___ khí là cách sắp xếp các bè nhạc cụ cho một bài hát.', [[0, 'Phối']],
                'Phối khí quyết định màu sắc âm thanh của bản nhạc.');
            $this->fill($L, 'Nhạc trẻ thường có tiết tấu ___ động.', [[0, 'sôi']],
                'Tiết tấu sôi động phù hợp với đời sống đô thị hiện đại.');
        }
    }

    private function seedAmNhac113(): void
    {
        $L = 'am-nhac-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Công việc chính của nhạc sĩ là gì?',
                ['Sáng tác âm nhạc', 'Chỉ hát biểu diễn', 'Chỉ dạy nhạc', 'Chỉ bán nhạc cụ'], 0,
                'Nhạc sĩ là người sáng tác: viết giai điệu, lời ca và ý tưởng hòa âm.');
            $this->quiz($L, 'Ca sĩ khác nhạc sĩ ở điểm cơ bản nào?',
                ['Ca sĩ thể hiện tác phẩm, nhạc sĩ sáng tác', 'Ca sĩ sáng tác, nhạc sĩ hát',
                 'Hai vai trò không khác nhau', 'Ca sĩ không cần đến nhạc sĩ'], 0,
                'Nhạc sĩ tạo ra tác phẩm, ca sĩ là người thể hiện tác phẩm đó.');
            $this->quiz($L, 'Tác quyền trong âm nhạc có ý nghĩa gì?',
                ['Bảo vệ quyền lợi người sáng tác khi tác phẩm được sử dụng',
                 'Cấm mọi người nghe nhạc', 'Chỉ cho phép ca sĩ hát', 'Thu thuế nhạc cụ'], 0,
                'Tác quyền đảm bảo người sáng tác được hưởng lợi khi tác phẩm được khai thác.');
            $this->quiz($L, 'Người vừa sáng tác vừa tự hát ca khúc của mình được gọi là gì?',
                ['Nhạc sĩ – ca sĩ', 'Chỉ là ca sĩ', 'Chỉ là nhạc sĩ', 'Nhà sản xuất'], 0,
                'Nhiều nghệ sĩ đảm nhận cả hai vai trò sáng tác và biểu diễn.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi vai trò với công việc tương ứng.',
                [['Nhạc sĩ', 'Người sáng tác âm nhạc'],
                 ['Ca sĩ', 'Người thể hiện tác phẩm'],
                 ['Nhạc công', 'Người chơi nhạc cụ'],
                 ['Khán giả', 'Người thưởng thức âm nhạc']],
                'Mỗi vai trò góp một mắt xích trong đời sống âm nhạc.');
            $this->matching($L, 'Nối mỗi công đoạn sáng tác với mô tả của nó.',
                [['Sáng tác giai điệu', 'Viết đường nét âm thanh của bài hát'],
                 ['Viết lời ca', 'Viết phần lời cho bài hát'],
                 ['Phối khí', 'Sắp xếp các bè nhạc cụ'],
                 ['Hòa âm', 'Dùng hợp âm làm nền cho giai điệu']],
                'Một ca khúc hoàn chỉnh cần cả giai điệu, lời ca và hòa âm phối khí.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Tác quyền', 'Quyền lợi của người sáng tác'],
                 ['Biểu diễn', 'Trình bày tác phẩm trước khán giả'],
                 ['Thu âm', 'Ghi lại âm thanh vào bản ghi'],
                 ['Phát hành', 'Đưa tác phẩm đến với công chúng']],
                'Tác quyền bảo vệ người sáng tác trong mọi hình thức khai thác.');
            $this->matching($L, 'Nối mỗi yếu tố với vai trò của nó trong thành công của bài hát.',
                [['Giai điệu hay', 'Thu hút người nghe ngay từ đầu'],
                 ['Lời ca ý nghĩa', 'Chạm đến cảm xúc người nghe'],
                 ['Phối khí tốt', 'Làm bài hát sang và hiện đại hơn'],
                 ['Ca sĩ hợp giọng', 'Thể hiện trọn vẹn tinh thần bài hát']],
                'Bài hát thành công là sự kết hợp của nhiều yếu tố.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi công việc vào nhóm của NHẠC SĨ hoặc CA SĨ.',
                [['Viết giai điệu', 'Nhạc sĩ'], ['Viết lời ca', 'Nhạc sĩ'], ['Lên ý tưởng hòa âm', 'Nhạc sĩ'],
                 ['Hát biểu diễn', 'Ca sĩ'], ['Luyện thanh', 'Ca sĩ'], ['Thể hiện cảm xúc bài hát', 'Ca sĩ']],
                'Nhạc sĩ sáng tạo tác phẩm, ca sĩ truyền tải tác phẩm đến khán giả.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nhạc sĩ là người sáng tác', 'Đúng'], ['Ca sĩ là người thể hiện', 'Đúng'],
                 ['Tác quyền bảo vệ người sáng tác', 'Đúng'],
                 ['Một người có thể vừa sáng tác vừa hát', 'Đúng'],
                 ['Nhạc công là người nghe nhạc', 'Sai'], ['Phối khí là viết lời bài hát', 'Sai']],
                'Nhạc công là người chơi nhạc cụ; phối khí là sắp xếp bè nhạc cụ.');
            $this->sortQ($L, 'Kéo mỗi công đoạn vào nhóm TRƯỚC hoặc SAU khi bài hát ra mắt.',
                [['Sáng tác', 'Trước khi ra mắt'], ['Thu âm', 'Trước khi ra mắt'], ['Phối khí', 'Trước khi ra mắt'],
                 ['Biểu diễn live', 'Sau khi ra mắt'], ['Phát hành', 'Sau khi ra mắt'], ['Quảng bá', 'Sau khi ra mắt']],
                'Bài hát phải được sáng tác, thu âm, phối khí xong mới phát hành.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm thuộc về SÁNG TÁC hoặc BIỂU DIỄN.',
                [['Giai điệu', 'Sáng tác'], ['Lời ca', 'Sáng tác'], ['Ý tưởng hòa âm', 'Sáng tác'],
                 ['Giọng hát', 'Biểu diễn'], ['Phong cách trình diễn', 'Biểu diễn'], ['Tương tác khán giả', 'Biểu diễn']],
                'Sáng tác tạo ra tác phẩm, biểu diễn đưa tác phẩm đến công chúng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Người sáng tác âm nhạc được gọi là nhạc ___.', [[0, 'sĩ']],
                'Nhạc sĩ là người viết nên giai điệu và lời ca.');
            $this->fill($L, '___ quyền bảo vệ quyền lợi của người sáng tác.', [[0, 'Tác']],
                'Tác quyền đảm bảo thu nhập chính đáng cho nhạc sĩ.');
            $this->fill($L, 'Ca sĩ là người ___ hiện tác phẩm âm nhạc.', [[0, 'thể']],
                'Thể hiện là đưa tác phẩm đến với khán giả bằng giọng hát.');
            $this->fill($L, 'Người vừa sáng tác vừa tự hát gọi là nhạc sĩ – ca ___.', [[0, 'sĩ']],
                'Nhiều nghệ sĩ thành công ở cả hai vai trò.');
        }
    }

    private function seedAmNhac114(): void
    {
        $L = 'am-nhac-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thế hệ nhạc sĩ tiền chiến có đóng góp lớn nào?',
                ['Đặt nền móng cho tân nhạc Việt Nam', 'Phát triển nhạc EDM',
                 'Sáng tạo nhạc rap', 'Du nhập opera vào Việt Nam'], 0,
                'Thế hệ tiền chiến đặt nền móng cho tân nhạc – nhạc Việt Nam hiện đại.');
            $this->quiz($L, 'Dòng nhạc gắn liền với thế hệ nhạc sĩ thời kháng chiến là gì?',
                ['Nhạc cách mạng', 'Nhạc tiền chiến', 'Nhạc bolero', 'Nhạc thính phòng'], 0,
                'Thế hệ kháng chiến để lại dòng nhạc cách mạng hào hùng.');
            $this->quiz($L, 'Thế hệ nhạc sĩ sau năm 1975 phát triển mạnh dòng nhạc nào?',
                ['Nhạc nhẹ', 'Nhạc cách mạng', 'Nhạc tiền chiến', 'Nhạc cung đình'], 0,
                'Sau 1975, nhạc nhẹ (nhạc giải trí hiện đại) phát triển mạnh mẽ.');
            $this->quiz($L, 'Thế hệ nhạc sĩ đương đại có đặc điểm nổi bật nào?',
                ['Hội nhập quốc tế', 'Chỉ sáng tác nhạc cách mạng',
                 'Không dùng nhạc cụ điện tử', 'Chỉ hát dân ca'], 0,
                'Nhạc sĩ đương đại sáng tác theo xu hướng quốc tế, hội nhập thế giới.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thế hệ nhạc sĩ với đóng góp tiêu biểu.',
                [['Thế hệ tiền chiến', 'Đặt nền móng tân nhạc'],
                 ['Thế hệ kháng chiến', 'Gắn với nhạc cách mạng'],
                 ['Thế hệ sau 1975', 'Phát triển nhạc nhẹ'],
                 ['Thế hệ đương đại', 'Hội nhập quốc tế']],
                'Mỗi thế hệ gắn với một thời kỳ lịch sử và dòng nhạc riêng.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Tân nhạc', 'Nhạc Việt Nam hiện đại'],
                 ['Nhạc nhẹ', 'Nhạc giải trí hiện đại'],
                 ['Hội nhập', 'Hòa vào xu hướng của thế giới'],
                 ['Di sản', 'Những giá trị để lại cho đời sau']],
                'Di sản của các thế hệ làm giàu cho nền âm nhạc dân tộc.');
            $this->matching($L, 'Nối mỗi mốc thời gian với ý nghĩa của nó.',
                [['Trước 1945', 'Thời kỳ tiền chiến'],
                 ['1945 – 1975', 'Thời kỳ kháng chiến'],
                 ['Sau 1975', 'Thời kỳ phát triển nhạc nhẹ'],
                 ['Hiện nay', 'Thời kỳ đương đại, hội nhập']],
                'Lịch sử âm nhạc song hành cùng lịch sử dân tộc.');
            $this->matching($L, 'Nối mỗi hành động với ý nghĩa của nó trong sự phát triển âm nhạc.',
                [['Kế thừa', 'Tiếp nối thành quả của thế hệ trước'],
                 ['Phát huy', 'Làm cho giá trị tốt đẹp lan tỏa hơn'],
                 ['Sáng tạo', 'Tạo ra cái mới cho âm nhạc'],
                 ['Bảo tồn', 'Giữ gìn những giá trị truyền thống']],
                'Âm nhạc phát triển nhờ vừa kế thừa vừa sáng tạo.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thế hệ/dòng nhạc vào nhóm TRƯỚC 1975 hoặc SAU 1975.',
                [['Tiền chiến', 'Trước 1975'], ['Kháng chiến chống Pháp', 'Trước 1975'],
                 ['Kháng chiến chống Mỹ', 'Trước 1975'],
                 ['Nhạc nhẹ sau 1975', 'Sau 1975'], ['Nhạc trẻ đương đại', 'Sau 1975'],
                 ['Hội nhập quốc tế', 'Sau 1975']],
                'Năm 1975 là mốc đất nước thống nhất, mở ra giai đoạn mới.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Thế hệ tiền chiến đặt nền móng tân nhạc', 'Đúng'],
                 ['Thế hệ kháng chiến gắn với nhạc cách mạng', 'Đúng'],
                 ['Thế hệ đương đại hội nhập quốc tế', 'Đúng'],
                 ['Nhạc nhẹ phát triển mạnh sau 1975', 'Đúng'],
                 ['Tân nhạc ra đời sau năm 2000', 'Sai'],
                 ['Các thế hệ không liên quan đến nhau', 'Sai']],
                'Tân nhạc hình thành từ trước 1945; các thế hệ kế thừa lẫn nhau.');
            $this->sortQ($L, 'Kéo mỗi dòng nhạc vào đúng thời kỳ của nó.',
                [['Nhạc tiền chiến', 'Trước 1945'], ['Tân nhạc', 'Trước 1945'],
                 ['Nhạc kháng chiến', '1945 – 1975'], ['Nhạc đỏ', '1945 – 1975'],
                 ['Nhạc nhẹ', 'Sau 1975'], ['V-pop hiện đại', 'Sau 1975']],
                'Mỗi thời kỳ lịch sử sản sinh những dòng nhạc đặc trưng.');
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm SÁNG TÁC, BẢO TỒN hoặc PHÁT TRIỂN.',
                [['Viết ca khúc mới', 'Sáng tác'], ['Phối khí mới cho bài cũ', 'Sáng tác'],
                 ['Lưu giữ dân ca', 'Bảo tồn'], ['Dạy nhạc truyền thống', 'Bảo tồn'],
                 ['Đưa nhạc Việt ra thế giới', 'Phát triển'], ['Kết hợp hiện đại – truyền thống', 'Phát triển']],
                'Ba hướng hoạt động cùng làm giàu cho nền âm nhạc.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thế hệ tiền chiến đặt nền móng cho ___ nhạc Việt Nam.', [[0, 'tân']],
                'Tân nhạc là nhạc Việt Nam hiện đại theo lối phương Tây.');
            $this->fill($L, 'Thế hệ kháng chiến gắn với dòng nhạc cách ___.', [[0, 'mạng']],
                'Nhạc cách mạng cổ vũ tinh thần chiến đấu bảo vệ đất nước.');
            $this->fill($L, 'Thế hệ sau 1975 phát triển mạnh dòng nhạc ___.', [[0, 'nhẹ']],
                'Nhạc nhẹ là nhạc giải trí hiện đại sau chiến tranh.');
            $this->fill($L, 'Thế hệ đương đại có xu hướng hội ___ quốc tế.', [[0, 'nhập']],
                'Hội nhập giúp âm nhạc Việt Nam vươn ra thế giới.');
        }
    }

    // ================= ÂM NHẠC – LỚP 12 =================

    private function seedAmNhac121(): void
    {
        $L = 'am-nhac-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Âm nhạc bác học còn được gọi là gì?',
                ['Nhạc cổ điển', 'Nhạc trẻ', 'Nhạc dance', 'Nhạc rap'], 0,
                'Âm nhạc bác học là dòng nhạc nghệ thuật phương Tây, thường gọi là nhạc cổ điển.');
            $this->quiz($L, 'Jazz ra đời ở đâu vào đầu thế kỷ 20?',
                ['Mỹ', 'Pháp', 'Việt Nam', 'Nhật Bản'], 0,
                'Jazz ra đời ở Mỹ đầu thế kỷ 20, từ cộng đồng người Mỹ gốc Phi.');
            $this->quiz($L, 'Đặc trưng nổi bật của jazz là gì?',
                ['Ứng tác (improvisation) và tiết tấu swing', 'Chỉ hát không dùng nhạc cụ',
                 'Không có nhịp điệu', 'Chỉ dùng duy nhất một loại trống'], 0,
                'Jazz đề cao sự ứng tác tự do và tiết tấu swing đặc trưng.');
            $this->quiz($L, 'Blues có đặc điểm nào?',
                ['Giai điệu buồn, cấu trúc 12 ô nhịp', 'Giai điệu vui nhộn',
                 'Không có lời hát', 'Tiết tấu rất nhanh'], 0,
                'Blues là tiền thân của jazz với giai điệu buồn và cấu trúc 12 ô nhịp.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thể loại với đặc điểm của nó.',
                [['Nhạc cổ điển', 'Âm nhạc bác học phương Tây'],
                 ['Jazz', 'Ứng tác và tiết tấu swing'],
                 ['Blues', 'Tiền thân của jazz, giai điệu buồn'],
                 ['Swing', 'Kiểu nhấn nhịp đặc trưng của jazz']],
                'Ba thể loại có mối quan hệ nguồn gốc với nhau.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Bản nhạc', 'Văn bản ghi chép âm nhạc chuẩn xác'],
                 ['Nhà hát', 'Nơi biểu diễn nhạc cổ điển'],
                 ['Ứng tác', 'Chơi nhạc tự do không theo bản nhạc'],
                 ['12 ô nhịp', 'Cấu trúc điển hình của blues']],
                'Nhạc cổ điển dựa vào bản nhạc, jazz đề cao ứng tác.');
            $this->matching($L, 'Nối mỗi nhạc cụ với nhóm của nó.',
                [['Violin', 'Nhạc cụ dây kéo'],
                 ['Piano', 'Nhạc cụ phím'],
                 ['Kèn trumpet', 'Nhạc cụ hơi đồng'],
                 ['Trống jazz', 'Nhạc cụ gõ']],
                'Dàn nhạc jazz và cổ điển dùng nhiều nhóm nhạc cụ khác nhau.');
            $this->matching($L, 'Nối mỗi mốc thời gian với sự kiện âm nhạc.',
                [['Thế kỷ 17 – 18', 'Thời kỳ cổ điển viên mãn ở châu Âu'],
                 ['Đầu thế kỷ 20', 'Jazz ra đời ở Mỹ'],
                 ['Cuối thế kỷ 19', 'Blues hình thành ở Mỹ'],
                 ['Hiện nay', 'Jazz và cổ điển cùng tồn tại']],
                'Các thể loại ra đời ở những thời điểm lịch sử khác nhau.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào đúng thể loại: NHẠC CỔ ĐIỂN, JAZZ hoặc BLUES.',
                [['Bản nhạc chuẩn xác', 'Nhạc cổ điển'], ['Biểu diễn trong nhà hát', 'Nhạc cổ điển'],
                 ['Ứng tác tự do', 'Jazz'], ['Tiết tấu swing', 'Jazz'],
                 ['Giai điệu buồn', 'Blues'], ['Cấu trúc 12 ô nhịp', 'Blues']],
                'Cổ điển chuẩn mực, jazz tự do, blues trầm buồn.');
            $this->sortQ($L, 'Kéo mỗi nhạc cụ vào nhóm DÂY, PHÍM, HƠI hoặc GÕ.',
                [['Violin', 'Dây'], ['Cello', 'Dây'],
                 ['Piano', 'Phím'],
                 ['Kèn trumpet', 'Hơi'], ['Kèn saxophone', 'Hơi'],
                 ['Trống', 'Gõ']],
                'Phân loại nhạc cụ theo cách tạo ra âm thanh.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Jazz ra đời ở Mỹ đầu thế kỷ 20', 'Đúng'], ['Blues là tiền thân của jazz', 'Đúng'],
                 ['Nhạc cổ điển được ghi bằng bản nhạc', 'Đúng'], ['Swing là tiết tấu của jazz', 'Đúng'],
                 ['Ứng tác là chơi theo bản nhạc', 'Sai'], ['Blues có giai điệu vui nhộn', 'Sai']],
                'Ứng tác là chơi tự do; blues có giai điệu buồn.');
            $this->sortQ($L, 'Kéo mỗi thể loại vào châu lục nơi nó hình thành.',
                [['Nhạc cổ điển', 'Châu Âu'], ['Opera', 'Châu Âu'], ['Giao hưởng', 'Châu Âu'],
                 ['Jazz', 'Châu Mỹ'], ['Blues', 'Châu Mỹ'], ['Hip-hop', 'Châu Mỹ']],
                'Nhạc bác học hình thành ở châu Âu; jazz, blues, hip-hop ở châu Mỹ.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Jazz ra đời ở ___ đầu thế kỷ 20.', [[0, 'Mỹ']],
                'Jazz là thể loại âm nhạc đặc trưng của nước Mỹ.');
            $this->fill($L, 'Đặc trưng của jazz là ứng ___ và tiết tấu swing.', [[0, 'tác']],
                'Ứng tác là chơi nhạc tự do, không theo bản nhạc có sẵn.');
            $this->fill($L, 'Blues có cấu trúc điển hình gồm 12 ô ___.', [[0, 'nhịp']],
                'Cấu trúc 12 ô nhịp là khung chuẩn của blues.');
            $this->fill($L, 'Âm nhạc bác học còn được gọi là nhạc cổ ___.', [[0, 'điển']],
                'Nhạc cổ điển là tên gọi phổ biến của âm nhạc bác học.');
        }
    }

    private function seedAmNhac122(): void
    {
        $L = 'am-nhac-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Rock sử dụng nhạc cụ đặc trưng nào?',
                ['Guitar điện', 'Đàn bầu', 'Sáo trúc', 'Đàn tranh'], 0,
                'Guitar điện cùng trống và bass tạo nên âm thanh mạnh mẽ của rock.');
            $this->quiz($L, 'Nhạc pop hướng đến đối tượng nào?',
                ['Đại chúng', 'Chỉ giới chuyên môn', 'Chỉ trẻ em', 'Chỉ người cao tuổi'], 0,
                'Pop là nhạc đại chúng với giai điệu dễ nhớ, dễ hát theo.');
            $this->quiz($L, 'Hip-hop nổi bật với hình thức thể hiện nào?',
                ['Rap', 'Opera', 'Hát chầu văn', 'Hát quan họ'], 0,
                'Rap – đọc có nhịp điệu trên nền beat – là dấu ấn của hip-hop.');
            $this->quiz($L, 'Nhạc điện tử (EDM) được tạo ra chủ yếu bằng gì?',
                ['Máy tính và thiết bị điện tử', 'Chỉ nhạc cụ dân tộc',
                 'Chỉ hát a cappella', 'Chỉ dùng piano'], 0,
                'EDM được sản xuất chủ yếu bằng máy tính và thiết bị điện tử.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thể loại với đặc điểm của nó.',
                [['Rock', 'Guitar điện, trống, bass; âm thanh mạnh mẽ'],
                 ['Pop', 'Giai điệu dễ nhớ, hướng đến đại chúng'],
                 ['Hip-hop', 'Nổi bật với rap trên nền beat'],
                 ['EDM', 'Tạo bằng máy tính, dùng trong lễ hội']],
                'Bốn thể loại đại chúng phổ biến nhất hiện nay.');
            $this->matching($L, 'Nối mỗi thuật ngữ với ý nghĩa của nó.',
                [['Guitar điện', 'Nhạc cụ đặc trưng của rock'],
                 ['Beat', 'Nền nhịp điệu của hip-hop'],
                 ['Rap', 'Đọc có nhịp điệu trên nền nhạc'],
                 ['DJ', 'Người chơi và phối nhạc điện tử']],
                'Mỗi thể loại có ngôn ngữ và nhân vật đặc trưng riêng.');
            $this->matching($L, 'Nối mỗi nghệ sĩ với vai trò của họ.',
                [['Ban nhạc rock', 'Nhóm chơi nhạc rock'],
                 ['Ca sĩ pop', 'Người hát nhạc pop'],
                 ['Rapper', 'Người hát rap'],
                 ['Producer', 'Người sản xuất âm nhạc']],
                'Ngành công nghiệp âm nhạc có nhiều vai trò chuyên môn.');
            $this->matching($L, 'Nối mỗi tính từ với thể loại nó thường mô tả.',
                [['Mạnh mẽ', 'Âm thanh của rock'],
                 ['Dễ nhớ', 'Giai điệu của pop'],
                 ['Cá tính', 'Phong cách của hip-hop'],
                 ['Sôi động', 'Không khí của lễ hội EDM']],
                'Tính từ giúp phân biệt màu sắc của từng thể loại.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào đúng thể loại: ROCK, POP hoặc EDM.',
                [['Guitar điện', 'Rock'], ['Âm thanh mạnh mẽ', 'Rock'],
                 ['Giai điệu dễ nhớ', 'Pop'], ['Hướng đến đại chúng', 'Pop'],
                 ['Tạo bằng máy tính', 'EDM'], ['Dùng trong lễ hội', 'EDM']],
                'Rock mạnh mẽ, pop đại chúng, EDM điện tử.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào đúng thể loại của nó.',
                [['Rap', 'Hip-hop'], ['Đọc có nhịp điệu', 'Hip-hop'],
                 ['DJ', 'EDM'], ['Remix', 'EDM'],
                 ['Ban nhạc', 'Rock'], ['Chơi live', 'Rock']],
                'Hip-hop có rap, EDM có DJ, rock có ban nhạc chơi live.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Rock dùng guitar điện', 'Đúng'], ['Pop có giai điệu dễ nhớ', 'Đúng'],
                 ['Hip-hop nổi bật với rap', 'Đúng'], ['EDM tạo bằng máy tính', 'Đúng'],
                 ['Rap là hát opera', 'Sai'], ['EDM chỉ dùng đàn bầu', 'Sai']],
                'Rap và opera là hai hình thức hoàn toàn khác nhau.');
            $this->sortQ($L, 'Kéo mỗi nhạc cụ vào nhóm ĐIỆN TỬ hoặc TRUYỀN THỐNG.',
                [['Guitar điện', 'Điện tử'], ['Synthesizer', 'Điện tử'], ['Trống điện', 'Điện tử'],
                 ['Đàn bầu', 'Truyền thống'], ['Sáo trúc', 'Truyền thống'], ['Đàn tranh', 'Truyền thống']],
                'Nhạc cụ điện tử dùng điện để tạo và khuếch đại âm thanh.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nhạc cụ đặc trưng của rock là guitar ___.', [[0, 'điện']],
                'Guitar điện tạo nên âm thanh mạnh mẽ đặc trưng của rock.');
            $this->fill($L, 'Hip-hop nổi bật với hình thức ___.', [[0, 'rap']],
                'Rap là đọc có nhịp điệu trên nền beat.');
            $this->fill($L, 'EDM là chữ viết tắt của nhạc ___ tử.', [[0, 'điện']],
                'Electronic Dance Music – nhạc điện tử.');
            $this->fill($L, 'Pop có giai điệu dễ ___ và hướng đến đại chúng.', [[0, 'nhớ']],
                'Giai điệu dễ nhớ là bí quyết thành công của nhạc pop.');
        }
    }

    private function seedAmNhac123(): void
    {
        $L = 'am-nhac-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bản giao hưởng (symphony) thường gồm mấy chương?',
                ['4 chương', '2 chương', '3 chương', '5 chương'], 0,
                'Giao hưởng điển hình gồm 4 chương với tốc độ khác nhau.');
            $this->quiz($L, 'Concerto là tác phẩm dành cho đối tượng nào?',
                ['Một nhạc cụ độc tấu đối thoại với dàn nhạc', 'Chỉ dàn hợp xướng',
                 'Chỉ một ca sĩ đơn ca', 'Chỉ riêng bộ gõ'], 0,
                'Concerto là cuộc đối thoại giữa nhạc cụ độc tấu và dàn nhạc.');
            $this->quiz($L, 'Concerto thường có mấy chương?',
                ['3 chương', '4 chương', '1 chương', '6 chương'], 0,
                'Concerto điển hình gồm 3 chương: nhanh – chậm – nhanh.');
            $this->quiz($L, 'Thứ tự tốc độ các chương trong một bản giao hưởng thường là gì?',
                ['Nhanh – chậm – vừa – nhanh', 'Chậm – chậm – chậm – chậm',
                 'Nhanh – nhanh – nhanh – nhanh', 'Không có quy định chung'], 0,
                'Cấu trúc 4 chương nhanh – chậm – vừa – nhanh tạo sự cân bằng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hình thức tác phẩm với đặc điểm của nó.',
                [['Giao hưởng', 'Tác phẩm lớn cho dàn nhạc, thường 4 chương'],
                 ['Concerto', 'Nhạc cụ độc tấu với dàn nhạc, thường 3 chương'],
                 ['Dàn nhạc giao hưởng', 'Tập thể nhạc công biểu diễn giao hưởng'],
                 ['Nhạc trưởng', 'Người chỉ huy dàn nhạc']],
                'Giao hưởng và concerto là hai đỉnh cao của âm nhạc bác học.');
            $this->matching($L, 'Nối mỗi chương với tính chất thường thấy.',
                [['Chương 1', 'Thường nhanh, trình bày chủ đề chính'],
                 ['Chương 2', 'Thường chậm, trữ tình'],
                 ['Chương 4', 'Thường nhanh, kết thúc hoành tráng'],
                 ['Đoạn độc tấu', 'Nhạc cụ solo thể hiện kỹ thuật']],
                'Sự tương phản tốc độ tạo nên kịch tính cho tác phẩm.');
            $this->matching($L, 'Nối mỗi nhạc cụ với vai trò độc tấu concerto.',
                [['Violin', 'Độc tấu concerto phổ biến, âm vực cao'],
                 ['Piano', 'Độc tấu concerto phổ biến, âm vực rộng'],
                 ['Cello', 'Độc tấu với âm sắc trầm ấm'],
                 ['Sáo', 'Độc tấu với âm sắc thanh thoát']],
                'Nhiều nhạc cụ đều có concerto viết riêng cho mình.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Đối thoại', 'Nhạc cụ solo trò chuyện với dàn nhạc'],
                 ['Hòa tấu', 'Nhiều nhạc cụ cùng chơi'],
                 ['Đỉnh cao', 'Vị trí của giao hưởng trong âm nhạc bác học'],
                 ['Thính phòng', 'Không gian biểu diễn nhỏ, ít người']],
                'Concerto là nghệ thuật đối thoại âm thanh.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm GIAO HƯỞNG hoặc CONCERTO.',
                [['4 chương', 'Giao hưởng'], ['Dàn nhạc là trung tâm', 'Giao hưởng'],
                 ['Nhạc trưởng chỉ huy', 'Giao hưởng'],
                 ['3 chương', 'Concerto'], ['Nhạc cụ độc tấu', 'Concerto'],
                 ['Solo thể hiện kỹ thuật', 'Concerto']],
                'Giao hưởng tôn vinh tập thể, concerto tôn vinh cá nhân độc tấu.');
            $this->sortQ($L, 'Kéo mỗi chương/đoạn vào nhóm TỐC ĐỘ NHANH hoặc CHẬM.',
                [['Chương 1 giao hưởng', 'Nhanh'], ['Chương 4 giao hưởng', 'Nhanh'],
                 ['Mở đầu concerto', 'Nhanh'],
                 ['Chương 2 giao hưởng', 'Chậm'], ['Chương chậm của concerto', 'Chậm'],
                 ['Đoạn trữ tình', 'Chậm']],
                'Chương nhanh mở đầu và kết thúc, chương chậm ở giữa.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Giao hưởng thường có 4 chương', 'Đúng'], ['Concerto thường có 3 chương', 'Đúng'],
                 ['Nhạc trưởng chỉ huy dàn nhạc', 'Đúng'], ['Violin có thể độc tấu concerto', 'Đúng'],
                 ['Concerto không cần dàn nhạc', 'Sai'], ['Giao hưởng là tác phẩm nhỏ', 'Sai']],
                'Concerto luôn có dàn nhạc đệm cho độc tấu; giao hưởng là tác phẩm lớn.');
            $this->sortQ($L, 'Kéo mỗi đối tượng vào nhóm DÀN NHẠC hoặc ĐỘC TẤU.',
                [['Nhiều nhạc công', 'Dàn nhạc'], ['Nhạc trưởng', 'Dàn nhạc'], ['Các bè dây', 'Dàn nhạc'],
                 ['Một nghệ sĩ', 'Độc tấu'], ['Thể hiện kỹ thuật cá nhân', 'Độc tấu'], ['Piano solo', 'Độc tấu']],
                'Dàn nhạc là tập thể, độc tấu là cá nhân nổi bật.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bản giao hưởng thường gồm ___ chương.', [[0, '4']],
                'Cấu trúc 4 chương là chuẩn mực của giao hưởng cổ điển.');
            $this->fill($L, 'Concerto là tác phẩm cho nhạc cụ ___ tấu với dàn nhạc.', [[0, 'độc']],
                'Độc tấu đối thoại với dàn nhạc là linh hồn của concerto.');
            $this->fill($L, 'Người chỉ huy dàn nhạc được gọi là nhạc ___.', [[0, 'trưởng']],
                'Nhạc trưởng dùng đũa chỉ huy để điều khiển dàn nhạc.');
            $this->fill($L, 'Chương 2 của giao hưởng thường có tốc độ ___.', [[0, 'chậm']],
                'Chương chậm mang màu sắc trữ tình, tương phản với các chương nhanh.');
        }
    }

    private function seedAmNhac124(): void
    {
        $L = 'am-nhac-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trong opera, diễn viên thể hiện lời thoại bằng cách nào?',
                ['Hát toàn bộ lời thoại', 'Chỉ nói không hát', 'Chỉ múa minh họa', 'Diễn kịch câm'], 0,
                'Opera là vở nhạc kịch mà diễn viên hát toàn bộ lời thoại.');
            $this->quiz($L, 'Nhạc kịch hiện đại (musical) khác opera ở điểm nào?',
                ['Xen kẽ hát và thoại', 'Chỉ hát không có thoại',
                 'Không dùng âm nhạc', 'Không có diễn viên'], 0,
                'Musical xen kẽ các đoạn hát và thoại, gần gũi hơn opera.');
            $this->quiz($L, 'Nhạc thính phòng được viết cho đối tượng nào?',
                ['Nhóm nhỏ nhạc công', 'Dàn nhạc hàng trăm người',
                 'Một mình ca sĩ', 'Dàn hợp xướng lớn'], 0,
                'Nhạc thính phòng dành cho nhóm nhỏ: song tấu, tam tấu, tứ tấu...');
            $this->quiz($L, 'Tứ tấu (quartet) gồm mấy nhạc công?',
                ['4 người', '2 người', '3 người', '8 người'], 0,
                'Tứ tấu = 4 nhạc công, ví dụ tứ tấu dây nổi tiếng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hình thức với đặc điểm của nó.',
                [['Opera', 'Hát toàn bộ lời thoại, sân khấu hoành tráng'],
                 ['Nhạc kịch', 'Xen kẽ hát và thoại'],
                 ['Nhạc thính phòng', 'Nhóm nhỏ nhạc công'],
                 ['Sân khấu', 'Nơi biểu diễn opera và nhạc kịch']],
                'Opera hoành tráng, nhạc kịch gần gũi, thính phòng tinh tế.');
            $this->matching($L, 'Nối mỗi tên gọi với số nhạc công tương ứng.',
                [['Song tấu', '2 nhạc công'], ['Tam tấu', '3 nhạc công'],
                 ['Tứ tấu', '4 nhạc công'], ['Độc tấu', '1 nhạc công']],
                'Tên gọi thính phòng cho biết số người biểu diễn.');
            $this->matching($L, 'Nối mỗi yếu tố sân khấu với vai trò của nó.',
                [['Phục trang', 'Trang phục biểu diễn'],
                 ['Dàn nhạc', 'Đệm nhạc cho vở opera'],
                 ['Kịch bản', 'Cốt truyện của vở diễn'],
                 ['Ánh sáng', 'Tạo không khí cho sân khấu']],
                'Opera là nghệ thuật tổng hợp nhiều yếu tố.');
            $this->matching($L, 'Nối mỗi tính chất với hình thức nghệ thuật tương ứng.',
                [['Tổng hợp', 'Opera: nhạc + kịch + mỹ thuật'],
                 ['Tinh tế', 'Nhạc thính phòng'],
                 ['Hoành tráng', 'Quy mô của opera'],
                 ['Gần gũi', 'Không gian thính phòng']],
                'Mỗi hình thức có không gian thưởng thức riêng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm OPERA, NHẠC KỊCH hoặc THÍNH PHÒNG.',
                [['Hát toàn bộ lời thoại', 'Opera'], ['Sân khấu hoành tráng', 'Opera'],
                 ['Xen kẽ hát và thoại', 'Nhạc kịch'], ['Hiện đại, gần gũi', 'Nhạc kịch'],
                 ['Nhóm nhỏ nhạc công', 'Thính phòng'], ['Không gian biểu diễn nhỏ', 'Thính phòng']],
                'Ba hình thức, ba quy mô và phong cách khác nhau.');
            $this->sortQ($L, 'Kéo mỗi tên gọi vào đúng số nhạc công.',
                [['Độc tấu', '1 người'], ['Song tấu', '2 người'], ['Tam tấu', '3 người'],
                 ['Tứ tấu', '4 người'], ['Ngũ tấu', '5 người'], ['Dàn nhạc', 'Hàng chục người']],
                'Tiền tố Hán Việt cho biết số lượng: song = 2, tam = 3, tứ = 4.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Opera hát toàn bộ lời thoại', 'Đúng'], ['Musical xen kẽ hát và thoại', 'Đúng'],
                 ['Thính phòng dành cho nhóm nhỏ', 'Đúng'], ['Thính phòng cần phối hợp tinh tế', 'Đúng'],
                 ['Tứ tấu gồm 3 người', 'Sai'], ['Opera không cần dàn nhạc', 'Sai']],
                'Tứ tấu gồm 4 người; opera luôn có dàn nhạc đệm.');
            $this->sortQ($L, 'Kéo mỗi hình thức vào nhóm NGHỆ THUẬT TỔNG HỢP hoặc THUẦN ÂM NHẠC.',
                [['Opera', 'Tổng hợp'], ['Nhạc kịch', 'Tổng hợp'], ['Múa trong opera', 'Tổng hợp'],
                 ['Giao hưởng', 'Thuần âm nhạc'], ['Concerto', 'Thuần âm nhạc'], ['Tứ tấu dây', 'Thuần âm nhạc']],
                'Opera và nhạc kịch kết hợp âm nhạc với kịch nghệ, mỹ thuật sân khấu.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trong opera, diễn viên ___ toàn bộ lời thoại.', [[0, 'hát']],
                'Hát thay cho nói là đặc trưng của opera.');
            $this->fill($L, 'Tứ tấu gồm ___ nhạc công.', [[0, '4']],
                'Tứ = 4, ví dụ tứ tấu dây với 2 violin, 1 viola, 1 cello.');
            $this->fill($L, 'Nhạc kịch hiện đại còn được gọi là ___.', [[0, 'musical']],
                'Musical là tên tiếng Anh của nhạc kịch hiện đại.');
            $this->fill($L, 'Nhạc thính phòng được biểu diễn trong không gian ___.', [[0, 'nhỏ']],
                'Không gian nhỏ giúp người nghe cảm nhận sự tinh tế.');
        }
    }

    // ================= MỸ THUẬT – LỚP 10 =================

    private function seedMyThuat101(): void
    {
        $L = 'my-thuat-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tranh thủy mặc Trung Hoa thường dùng chất liệu nào?',
                ['Mực đen trên giấy hoặc lụa', 'Sơn dầu trên vải bố',
                 'Màu nước trên giấy điệp', 'Phấn màu trên giấy đen'], 0,
                'Thủy mặc dùng mực đen (thủy = nước, mặc = mực) trên giấy hoặc lụa.');
            $this->quiz($L, 'Tranh khắc gỗ ukiyo-e là của nước nào?',
                ['Nhật Bản', 'Trung Hoa', 'Việt Nam', 'Hàn Quốc'], 0,
                'Ukiyo-e là tranh khắc gỗ in nhiều bản màu nổi tiếng của Nhật Bản.');
            $this->quiz($L, 'Đề tài tiêu biểu của tranh thủy mặc Trung Hoa là gì?',
                ['Sơn thủy (núi non, sông nước)', 'Chân dung vua chúa',
                 'Cảnh sinh hoạt cung đình', 'Tranh quảng cáo'], 0,
                'Tranh thủy mặc chuộng đề tài sơn thủy, thể hiện triết lý hòa hợp với thiên nhiên.');
            $this->quiz($L, 'Điểm đặc sắc trong bố cục tranh thủy mặc là gì?',
                ['Coi trọng khoảng trống', 'Lấp kín mọi chỗ trống',
                 'Chỉ vẽ ở góc tranh', 'Không có bố cục'], 0,
                'Khoảng trống trong thủy mặc mang ý nghĩa triết học, gợi không gian vô tận.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dòng tranh với đặc điểm của nó.',
                [['Tranh thủy mặc', 'Mực đen trên giấy/lụa, đề tài sơn thủy'],
                 ['Tranh ukiyo-e', 'Khắc gỗ Nhật Bản, in nhiều bản màu'],
                 ['Khoảng trống', 'Yếu tố triết học trong thủy mặc'],
                 ['Núi Phú Sĩ', 'Đề tài nổi tiếng của ukiyo-e']],
                'Hai dòng tranh tiêu biểu của mỹ thuật Đông Á.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Thủy', 'Nước'], ['Mặc', 'Mực'],
                 ['Sơn thủy', 'Núi non và sông nước'], ['Khắc gỗ', 'Kỹ thuật in từ ván gỗ khắc']],
                'Tên gọi thủy mặc phản ánh chất liệu mực và nước.');
            $this->matching($L, 'Nối mỗi quốc gia với dòng tranh tiêu biểu.',
                [['Trung Hoa', 'Tranh thủy mặc'],
                 ['Nhật Bản', 'Tranh ukiyo-e'],
                 ['Việt Nam', 'Tranh Đông Hồ, Hàng Trống'],
                 ['Châu Âu', 'Tranh sơn dầu']],
                'Mỗi nền văn hóa có dòng tranh đặc trưng riêng.');
            $this->matching($L, 'Nối mỗi đặc điểm với dòng tranh tương ứng.',
                [['Một màu mực đen', 'Thủy mặc'],
                 ['Nhiều bản màu', 'Ukiyo-e'],
                 ['Đề tài đời sống', 'Ukiyo-e'],
                 ['Khí vận', 'Thủy mặc']],
                'Thủy mặc tối giản một màu, ukiyo-e rực rỡ nhiều màu.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm TRANH THỦY MẶC hoặc UKIYO-E.',
                [['Mực đen trên giấy', 'Thủy mặc'], ['Đề tài sơn thủy', 'Thủy mặc'],
                 ['Coi trọng khoảng trống', 'Thủy mặc'],
                 ['Khắc gỗ in nhiều bản', 'Ukiyo-e'], ['Đề tài đời sống, phong cảnh', 'Ukiyo-e'],
                 ['Núi Phú Sĩ', 'Ukiyo-e']],
                'Thủy mặc Trung Hoa tối giản, ukiyo-e Nhật Bản rực rỡ.');
            $this->sortQ($L, 'Kéo mỗi chất liệu vào nhóm PHƯƠNG ĐÔNG hoặc PHƯƠNG TÂY.',
                [['Mực tàu', 'Phương Đông'], ['Giấy dó', 'Phương Đông'], ['Lụa', 'Phương Đông'],
                 ['Sơn dầu', 'Phương Tây'], ['Vải bố', 'Phương Tây'], ['Đá cẩm thạch', 'Phương Tây']],
                'Chất liệu phản ánh điều kiện tự nhiên và văn hóa mỗi vùng.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Thủy mặc dùng mực đen trên giấy hoặc lụa', 'Đúng'],
                 ['Ukiyo-e là tranh khắc gỗ của Nhật Bản', 'Đúng'],
                 ['Tranh thủy mặc coi trọng khoảng trống', 'Đúng'],
                 ['Sơn thủy là đề tài núi non sông nước', 'Đúng'],
                 ['Ukiyo-e chỉ in một bản duy nhất', 'Sai'],
                 ['Thủy mặc dùng sơn dầu', 'Sai']],
                'Ukiyo-e in được nhiều bản; thủy mặc dùng mực chứ không phải sơn dầu.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUỘC VỀ CHẤT LIỆU hoặc ĐỀ TÀI.',
                [['Mực đen', 'Chất liệu'], ['Giấy', 'Chất liệu'], ['Ván gỗ khắc', 'Chất liệu'],
                 ['Sơn thủy', 'Đề tài'], ['Đời sống', 'Đề tài'], ['Phong cảnh', 'Đề tài']],
                'Chất liệu là vật liệu tạo hình, đề tài là nội dung thể hiện.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tranh thủy mặc dùng ___ đen trên giấy hoặc lụa.', [[0, 'mực']],
                'Thủy mặc nghĩa là dùng nước pha mực để vẽ.');
            $this->fill($L, 'Ukiyo-e là tranh khắc gỗ nổi tiếng của Nhật ___.', [[0, 'Bản']],
                'Ukiyo-e in nhiều bản màu, đề tài đời sống và phong cảnh.');
            $this->fill($L, 'Đề tài tiêu biểu của thủy mặc là ___ thủy.', [[0, 'sơn']],
                'Sơn thủy là núi non và sông nước.');
            $this->fill($L, 'Tranh thủy mặc đặc biệt coi trọng khoảng ___.', [[0, 'trống']],
                'Khoảng trống mang ý nghĩa triết học sâu sắc.');
        }
    }

    private function seedMyThuat102(): void
    {
        $L = 'my-thuat-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tranh Đông Hồ có nguồn gốc từ đâu?',
                ['Bắc Ninh', 'Hà Nội', 'Huế', 'Hội An'], 0,
                'Tranh Đông Hồ là dòng tranh dân gian của làng Đông Hồ, Bắc Ninh.');
            $this->quiz($L, 'Tranh Đông Hồ được in bằng kỹ thuật nào?',
                ['Khắc gỗ trên giấy điệp', 'Vẽ tay bằng cọ lông',
                 'In lưới hiện đại', 'Chụp ảnh in ra'], 0,
                'Tranh Đông Hồ in từ ván khắc gỗ trên giấy điệp quét điệp.');
            $this->quiz($L, 'Tranh Hàng Trống khác tranh Đông Hồ ở điểm nào?',
                ['In nét rồi tô màu bằng tay', 'Chỉ dùng một màu đen',
                 'Vẽ trên lụa', 'Không có màu sắc'], 0,
                'Tranh Hàng Trống in nét đen rồi nghệ nhân tô màu thủ công.');
            $this->quiz($L, 'Chất liệu nào là đặc sắc của hội họa Việt Nam hiện đại?',
                ['Sơn mài và lụa', 'Sơn dầu và vải bố',
                 'Màu nước và giấy canson', 'Phấn tiên và giấy đen'], 0,
                'Sơn mài và tranh lụa là hai chất liệu đặc sắc của mỹ thuật Việt Nam.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dòng tranh với đặc điểm của nó.',
                [['Tranh Đông Hồ', 'Bắc Ninh, khắc gỗ, giấy điệp, màu thiên nhiên'],
                 ['Tranh Hàng Trống', 'Hà Nội, in nét rồi tô màu tay'],
                 ['Sơn mài', 'Chất liệu đặc sắc của hội họa Việt Nam'],
                 ['Tranh lụa', 'Vẽ trên lụa, màu sắc nhẹ nhàng']],
                'Bốn di sản tạo hình tiêu biểu của Việt Nam.');
            $this->matching($L, 'Nối mỗi chất liệu với mô tả của nó.',
                [['Giấy điệp', 'Giấy quét bột điệp óng ánh của Đông Hồ'],
                 ['Ván khắc gỗ', 'Khuôn in của tranh dân gian'],
                 ['Màu thiên nhiên', 'Màu từ cây cỏ, đất đá'],
                 ['Sơn mài', 'Sơn ta mài nhiều lớp, óng sâu']],
                'Chất liệu thiên nhiên làm nên vẻ đẹp mộc mạc.');
            $this->matching($L, 'Nối mỗi đề tài với dòng tranh thường thể hiện.',
                [['Đám cưới chuột', 'Tranh Đông Hồ'],
                 ['Thờ cúng, chúc tụng', 'Tranh Hàng Trống'],
                 ['Sinh hoạt dân gian', 'Tranh Đông Hồ'],
                 ['Phong cảnh, chân dung', 'Sơn mài, lụa hiện đại']],
                'Đề tài phản ánh đời sống và tín ngưỡng dân gian.');
            $this->matching($L, 'Nối mỗi địa danh với dòng tranh của nó.',
                [['Bắc Ninh', 'Tranh Đông Hồ'],
                 ['Hà Nội', 'Tranh Hàng Trống'],
                 ['Làng nghề', 'Nơi sản xuất tranh dân gian'],
                 ['Phố cổ', 'Nơi bán tranh Hàng Trống xưa']],
                'Tranh dân gian gắn với làng nghề truyền thống.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm TRANH ĐÔNG HỒ hoặc HÀNG TRỐNG.',
                [['Làng Đông Hồ, Bắc Ninh', 'Đông Hồ'], ['In từ ván khắc gỗ', 'Đông Hồ'],
                 ['Giấy điệp, màu thiên nhiên', 'Đông Hồ'],
                 ['Phố Hàng Trống, Hà Nội', 'Hàng Trống'], ['In nét rồi tô màu tay', 'Hàng Trống'],
                 ['Đề tài thờ cúng', 'Hàng Trống']],
                'Đông Hồ in ván gỗ, Hàng Trống tô màu tay.');
            $this->sortQ($L, 'Kéo mỗi chất liệu vào nhóm TRUYỀN THỐNG hoặc HIỆN ĐẠI.',
                [['Ván khắc gỗ', 'Truyền thống'], ['Giấy điệp', 'Truyền thống'],
                 ['Màu thiên nhiên', 'Truyền thống'],
                 ['Sơn mài', 'Hiện đại'], ['Lụa', 'Hiện đại'], ['Toan vải bố', 'Hiện đại']],
                'Sơn mài và lụa đưa mỹ thuật Việt Nam lên tầm hiện đại.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Tranh Đông Hồ có nguồn gốc từ Bắc Ninh', 'Đúng'],
                 ['Tranh Đông Hồ in từ ván khắc gỗ', 'Đúng'],
                 ['Sơn mài là chất liệu đặc sắc của Việt Nam', 'Đúng'],
                 ['Tranh Hàng Trống tô màu bằng tay', 'Đúng'],
                 ['Tranh Đông Hồ vẽ trên lụa', 'Sai'],
                 ['Màu Đông Hồ làm từ hóa chất công nghiệp', 'Sai']],
                'Đông Hồ in trên giấy điệp, màu từ thiên nhiên.');
            $this->sortQ($L, 'Kéo mỗi công đoạn vào nhóm IN ẤN hoặc TÔ MÀU.',
                [['Khắc ván gỗ', 'In ấn'], ['Quét điệp lên giấy', 'In ấn'], ['In từng lớp màu', 'In ấn'],
                 ['Tô màu bằng tay', 'Tô màu'], ['Điểm mắt, tô má hồng', 'Tô màu'], ['Vẽ chi tiết trang trí', 'Tô màu']],
                'Đông Hồ thiên về in, Hàng Trống kết hợp in nét và tô tay.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tranh Đông Hồ có nguồn gốc từ tỉnh Bắc ___.', [[0, 'Ninh']],
                'Làng Đông Hồ, huyện Thuận Thành, Bắc Ninh.');
            $this->fill($L, 'Tranh Đông Hồ được in trên giấy ___.', [[0, 'điệp']],
                'Giấy điệp được quét bột vỏ điệp óng ánh.');
            $this->fill($L, 'Tranh Hàng Trống sau khi in nét được ___ màu bằng tay.', [[0, 'tô']],
                'Nghệ nhân tô màu thủ công nên mỗi bức là duy nhất.');
            $this->fill($L, 'Hai chất liệu đặc sắc của hội họa Việt Nam hiện đại là sơn mài và ___.', [[0, 'lụa']],
                'Sơn mài óng sâu, tranh lụa nhẹ nhàng tinh tế.');
        }
    }

    private function seedMyThuat103(): void
    {
        $L = 'my-thuat-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Luật frontal trong mỹ thuật Ai Cập cổ đại quy định điều gì?',
                ['Mặt vẽ nghiêng, mắt nhìn thẳng, thân nhìn thẳng',
                 'Vẽ đúng như mắt nhìn thấy', 'Chỉ vẽ màu đen trắng', 'Không được vẽ người'], 0,
                'Luật frontal là quy tắc tạo hình cứng nhắc của Ai Cập cổ đại.');
            $this->quiz($L, 'Điêu khắc Hy Lạp cổ đại đề cao điều gì?',
                ['Vẻ đẹp cơ thể con người, tả thực và cân đối', 'Sự trừu tượng tuyệt đối',
                 'Màu sắc rực rỡ', 'Kích thước khổng lồ'], 0,
                'Hy Lạp tôn vinh vẻ đẹp con người với tỷ lệ cân đối, tả thực.');
            $this->quiz($L, 'Người La Mã kế thừa Hy Lạp và phát triển mạnh lĩnh vực nào?',
                ['Chân dung và kiến trúc vòm', 'Tranh thủy mặc',
                 'Tranh khắc gỗ', 'Tranh lụa'], 0,
                'La Mã phát triển nghệ thuật chân dung và kiến trúc vòm, mái vòm.');
            $this->quiz($L, 'Mỹ thuật Ai Cập cổ đại gắn liền với điều gì?',
                ['Tín ngưỡng và đời sống tâm linh', 'Thương mại quốc tế',
                 'Thể thao giải trí', 'Khoa học kỹ thuật'], 0,
                'Nghệ thuật Ai Cập phục vụ tín ngưỡng, lăng mộ và thần linh.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nền mỹ thuật với đặc điểm của nó.',
                [['Ai Cập', 'Luật frontal, gắn với tín ngưỡng'],
                 ['Hy Lạp', 'Tả thực, cân đối, tôn vinh con người'],
                 ['La Mã', 'Chân dung, kiến trúc vòm'],
                 ['Luật frontal', 'Mặt nghiêng, mắt và thân nhìn thẳng']],
                'Ba nền mỹ thuật đặt nền móng cho nghệ thuật phương Tây.');
            $this->matching($L, 'Nối mỗi thành tựu với nền văn minh tương ứng.',
                [['Kim tự tháp', 'Ai Cập'],
                 ['Tượng thần vệ nữ', 'Hy Lạp'],
                 ['Đấu trường Colosseum', 'La Mã'],
                 ['Tỷ lệ vàng', 'Hy Lạp']],
                'Mỗi nền văn minh để lại những kiệt tác bất hủ.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Tả thực', 'Mô tả đúng như thực tế'],
                 ['Cân đối', 'Các phần hài hòa về tỷ lệ'],
                 ['Tín ngưỡng', 'Niềm tin tôn giáo'],
                 ['Kiến trúc vòm', 'Kết cấu mái cong chịu lực tốt']],
                'Từ vựng cơ bản khi học lịch sử mỹ thuật.');
            $this->matching($L, 'Nối mỗi loại hình với ví dụ tiêu biểu.',
                [['Điêu khắc', 'Tượng thần Hy Lạp'],
                 ['Kiến trúc', 'Kim tự tháp Ai Cập'],
                 ['Hội họa', 'Tranh tường lăng mộ Ai Cập'],
                 ['Chân dung', 'Tượng chân dung La Mã']],
                'Ba nền văn minh đều phát triển đa dạng loại hình.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào đúng nền mỹ thuật: AI CẬP, HY LẠP hoặc LA MÃ.',
                [['Luật frontal', 'Ai Cập'], ['Gắn với tín ngưỡng', 'Ai Cập'],
                 ['Tả thực, cân đối', 'Hy Lạp'], ['Tôn vinh vẻ đẹp con người', 'Hy Lạp'],
                 ['Chân dung', 'La Mã'], ['Kiến trúc vòm, mái vòm', 'La Mã']],
                'Ai Cập tâm linh, Hy Lạp duy mỹ, La Mã thực dụng.');
            $this->sortQ($L, 'Kéo mỗi công trình/tác phẩm vào đúng nền văn minh.',
                [['Kim tự tháp', 'Ai Cập'], ['Tượng nhân sư', 'Ai Cập'],
                 ['Đền Parthenon', 'Hy Lạp'], ['Tượng lực sĩ', 'Hy Lạp'],
                 ['Đấu trường Colosseum', 'La Mã'], ['Khải hoàn môn', 'La Mã']],
                'Công trình tiêu biểu phản ánh trình độ của mỗi nền văn minh.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Mỹ thuật Ai Cập tuân thủ luật frontal', 'Đúng'],
                 ['Hy Lạp đề cao vẻ đẹp con người', 'Đúng'],
                 ['La Mã phát triển chân dung và kiến trúc vòm', 'Đúng'],
                 ['Ai Cập gắn với tín ngưỡng', 'Đúng'],
                 ['Hy Lạp theo trường phái trừu tượng', 'Sai'],
                 ['La Mã không có kiến trúc', 'Sai']],
                'Hy Lạp tả thực chứ không trừu tượng; La Mã có nền kiến trúc vĩ đại.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUỘC VỀ TÔN GIÁO hoặc THẾ TỤC.',
                [['Lăng mộ Pharaoh', 'Tôn giáo'], ['Tượng thần linh', 'Tôn giáo'],
                 ['Tranh tường lăng mộ', 'Tôn giáo'],
                 ['Tượng vận động viên', 'Thế tục'], ['Chân dung cá nhân', 'Thế tục'],
                 ['Công trình công cộng', 'Thế tục']],
                'Ai Cập nặng tôn giáo, Hy Lạp – La Mã cân bằng cả hai.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Quy tắc tạo hình đặc trưng của Ai Cập cổ đại gọi là luật ___.', [[0, 'frontal']],
                'Luật frontal: mặt nghiêng, mắt và thân nhìn thẳng.');
            $this->fill($L, 'Điêu khắc Hy Lạp đề cao vẻ đẹp ___ thể con người.', [[0, 'cơ']],
                'Người Hy Lạp tôn vinh cơ thể khỏe đẹp, cân đối.');
            $this->fill($L, 'Người La Mã phát triển mạnh nghệ thuật chân dung và kiến trúc ___.', [[0, 'vòm']],
                'Kết cấu vòm giúp La Mã xây dựng những công trình vĩ đại.');
            $this->fill($L, 'Công trình lăng mộ hình chóp của Ai Cập gọi là kim tự ___.', [[0, 'tháp']],
                'Kim tự tháp là biểu tượng của nền văn minh Ai Cập.');
        }
    }

    private function seedMyThuat104(): void
    {
        $L = 'my-thuat-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phong trào Phục hưng diễn ra vào khoảng thời gian nào?',
                ['Thế kỷ 14 – 16', 'Thế kỷ 10 – 12', 'Thế kỷ 19 – 20', 'Thế kỷ 20 – 21'], 0,
                'Phục hưng (Renaissance) hồi sinh văn hóa cổ điển vào thế kỷ 14–16.');
            $this->quiz($L, 'Thành tựu kỹ thuật quan trọng của hội họa Phục hưng là gì?',
                ['Luật phối cảnh', 'Kỹ thuật in lưới', 'Chụp ảnh', 'Vẽ bằng máy tính'], 0,
                'Luật phối cảnh giúp vẽ không gian ba chiều trên mặt phẳng.', 'trung_binh');
            $this->quiz($L, 'Trường phái Ấn tượng có đặc điểm nào?',
                ['Vẽ ngoài trời, chấm phá màu ghi lại ấn tượng ánh sáng',
                 'Vẽ trong xưởng tối', 'Chỉ dùng màu đen trắng', 'Không vẽ phong cảnh'], 0,
                'Họa sĩ Ấn tượng vẽ ngoài trời, chấm phá màu để bắt lấy ánh sáng.');
            $this->quiz($L, 'Nghệ thuật hiện đại thế kỷ 20 có đặc điểm gì?',
                ['Bùng nổ nhiều trào lưu, phá vỡ quy tắc truyền thống',
                 'Chỉ có một phong cách duy nhất', 'Quay lại vẽ như Ai Cập cổ đại',
                 'Không còn ai sáng tác'], 0,
                'Thế kỷ 20 chứng kiến lập thể, siêu thực, trừu tượng và nhiều trào lưu khác.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thời kỳ/trào lưu với đặc điểm của nó.',
                [['Phục hưng', 'Thế kỷ 14–16, hồi sinh cổ điển, đề cao con người'],
                 ['Ấn tượng', 'Cuối thế kỷ 19, vẽ ngoài trời, chấm phá ánh sáng'],
                 ['Lập thể', 'Phân mảnh hình khối, nhiều góc nhìn'],
                 ['Trừu tượng', 'Không mô tả đối tượng cụ thể']],
                'Dòng chảy mỹ thuật phương Tây từ cổ điển đến hiện đại.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Phối cảnh', 'Kỹ thuật vẽ không gian 3 chiều'],
                 ['Chấm phá', 'Đặt các nét màu nhỏ cạnh nhau'],
                 ['Ánh sáng', 'Yếu tố được họa sĩ Ấn tượng săn đuổi'],
                 ['Siêu thực', 'Vẽ thế giới mơ, vô thức']],
                'Từ vựng then chốt của mỹ thuật cận – hiện đại.');
            $this->matching($L, 'Nối mỗi trào lưu với thời gian tương ứng.',
                [['Phục hưng', 'Thế kỷ 14 – 16'],
                 ['Ấn tượng', 'Cuối thế kỷ 19'],
                 ['Hiện đại', 'Thế kỷ 20'],
                 ['Cổ đại', 'Trước Công nguyên']],
                'Trình tự thời gian của các trào lưu lớn.');
            $this->matching($L, 'Nối mỗi tinh thần với thời kỳ tương ứng.',
                [['Đề cao con người', 'Phục hưng'],
                 ['Tôn vinh ánh sáng', 'Ấn tượng'],
                 ['Phá vỡ quy tắc', 'Hiện đại'],
                 ['Tín ngưỡng thần linh', 'Cổ đại']],
                'Mỗi thời kỳ có tinh thần nghệ thuật riêng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm PHỤC HƯNG, ẤN TƯỢNG hoặc HIỆN ĐẠI.',
                [['Luật phối cảnh', 'Phục hưng'], ['Đề cao con người', 'Phục hưng'],
                 ['Vẽ ngoài trời', 'Ấn tượng'], ['Chấm phá ánh sáng', 'Ấn tượng'],
                 ['Trào lưu lập thể', 'Hiện đại'], ['Tranh trừu tượng', 'Hiện đại']],
                'Phục hưng chuẩn mực, Ấn tượng cảm xúc, hiện đại phá cách.');
            $this->sortQ($L, 'Kéo mỗi thời kỳ vào đúng thế kỷ của nó.',
                [['Phục hưng', 'Thế kỷ 14 – 16'], ['Baroque', 'Thế kỷ 17'],
                 ['Ấn tượng', 'Cuối thế kỷ 19'], ['Hiện đại', 'Thế kỷ 20'],
                 ['Ai Cập cổ đại', 'Trước Công nguyên'], ['Hy Lạp cổ đại', 'Trước Công nguyên']],
                'Nắm trình tự thời gian giúp hiểu sự phát triển của mỹ thuật.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Phục hưng phát minh luật phối cảnh', 'Đúng'],
                 ['Họa sĩ Ấn tượng vẽ ngoài trời', 'Đúng'],
                 ['Nghệ thuật hiện đại có nhiều trào lưu', 'Đúng'],
                 ['Phục hưng đề cao con người', 'Đúng'],
                 ['Ấn tượng chỉ vẽ trong xưởng tối', 'Sai'],
                 ['Hiện đại tuân thủ mọi quy tắc cổ điển', 'Sai']],
                'Ấn tượng vẽ ngoài trời; hiện đại phá vỡ quy tắc truyền thống.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm KỸ THUẬT hoặc TINH THẦN.',
                [['Luật phối cảnh', 'Kỹ thuật'], ['Chấm phá màu', 'Kỹ thuật'],
                 ['Phân mảnh hình khối', 'Kỹ thuật'],
                 ['Đề cao con người', 'Tinh thần'], ['Tôn vinh ánh sáng', 'Tinh thần'],
                 ['Tự do sáng tạo', 'Tinh thần']],
                'Mỗi trào lưu vừa có kỹ thuật mới vừa có tinh thần mới.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phong trào Phục hưng diễn ra vào thế kỷ 14 – ___.', [[0, '16']],
                'Renaissance kéo dài từ thế kỷ 14 đến thế kỷ 16.');
            $this->fill($L, 'Thành tựu kỹ thuật của Phục hưng là luật phối ___.', [[0, 'cảnh']],
                'Phối cảnh tạo chiều sâu không gian trên mặt phẳng.');
            $this->fill($L, 'Họa sĩ Ấn tượng thường vẽ ngoài ___ để bắt ánh sáng.', [[0, 'trời']],
                'Vẽ ngoài trời (plein air) là đặc trưng của trường phái Ấn tượng.');
            $this->fill($L, 'Trào lưu phân mảnh hình khối, nhiều góc nhìn gọi là lập ___.', [[0, 'thể']],
                'Lập thể là một trong những trào lưu hiện đại tiêu biểu.');
        }
    }

    // ================= MỸ THUẬT – LỚP 11 =================

    private function seedMyThuat111(): void
    {
        $L = 'my-thuat-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cân bằng đối xứng trong thiết kế tạo cảm giác gì?',
                ['Trang trọng, ổn định', 'Hỗn loạn, khó chịu',
                 'Buồn bã, ảm đạm', 'Không có cảm giác gì'], 0,
                'Đối xứng hai bên tạo sự trang trọng và ổn định cho bố cục.');
            $this->quiz($L, 'Tương phản trong thiết kế được tạo ra bằng cách nào?',
                ['Đặt các yếu tố đối lập cạnh nhau: sáng–tối, to–nhỏ, nóng–lạnh',
                 'Dùng duy nhất một màu', 'Xếp mọi thứ giống hệt nhau', 'Bỏ hết chữ trong thiết kế'], 0,
                'Tương phản giúp các yếu tố nổi bật và thu hút sự chú ý.');
            $this->quiz($L, 'Điểm nhấn trong bố cục thường được đặt theo quy tắc nào?',
                ['Quy tắc một phần ba', 'Đặt ở chính giữa tuyệt đối',
                 'Đặt ở góc khuất nhất', 'Không cần quy tắc'], 0,
                'Quy tắc một phần ba giúp điểm nhấn nằm ở vị trí thu hút mắt nhìn.', 'trung_binh');
            $this->quiz($L, 'Cân bằng bất đối xứng tạo cảm giác gì?',
                ['Năng động, hiện đại', 'Nhàm chán, đơn điệu',
                 'Lộn xộn, thiếu tổ chức', 'Buồn ngủ'], 0,
                'Bất đối xứng cân bằng bằng thị giác tạo sự năng động, hiện đại.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nguyên lý với mô tả của nó.',
                [['Cân bằng', 'Phân bổ thị giác hài hòa các yếu tố'],
                 ['Tương phản', 'Đặt yếu tố đối lập cạnh nhau'],
                 ['Nhấn mạnh', 'Tạo điểm thu hút mắt nhìn đầu tiên'],
                 ['Bố cục', 'Cách sắp xếp các yếu tố trong thiết kế']],
                'Ba nguyên lý nền tảng của mọi thiết kế.');
            $this->matching($L, 'Nối mỗi cặp tương phản với ví dụ của nó.',
                [['Sáng – tối', 'Chữ trắng trên nền đen'],
                 ['To – nhỏ', 'Tiêu đề lớn, nội dung nhỏ'],
                 ['Nóng – lạnh', 'Đỏ đặt cạnh xanh lam'],
                 ['Đậm – nhạt', 'Chữ đậm cạnh chữ thường']],
                'Tương phản càng mạnh, sự chú ý càng cao.');
            $this->matching($L, 'Nối mỗi loại cân bằng với đặc điểm của nó.',
                [['Đối xứng', 'Hai bên giống nhau, trang trọng'],
                 ['Bất đối xứng', 'Khác nhau nhưng vẫn hài hòa, năng động'],
                 ['Hướng tâm', 'Các yếu tố hướng về một điểm giữa'],
                 ['Mất cân bằng', 'Bố cục lệch, gây khó chịu']],
                'Cân bằng tốt là nền tảng của thiết kế đẹp.');
            $this->matching($L, 'Nối mỗi vị trí với vai trò trong bố cục.',
                [['Điểm nhấn', 'Nơi mắt nhìn đến đầu tiên'],
                 ['Tiêu đề', 'Thông tin quan trọng nhất'],
                 ['Nền', 'Phần nâng đỡ các yếu tố chính'],
                 ['Lề trang', 'Khoảng cách an toàn quanh nội dung']],
                'Thứ bậc thị giác dẫn dắt mắt người xem.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cặp yếu tố vào nhóm CÓ TƯƠNG PHẢN hoặc KHÔNG TƯƠNG PHẢN.',
                [['Chữ trắng trên nền đen', 'Có tương phản'], ['Tiêu đề to, chữ nhỏ', 'Có tương phản'],
                 ['Màu đỏ cạnh xanh lam', 'Có tương phản'],
                 ['Chữ xám trên nền xám', 'Không tương phản'], ['Mọi chữ cùng cỡ', 'Không tương phản'],
                 ['Một màu duy nhất', 'Không tương phản']],
                'Tương phản cần sự đối lập rõ rệt giữa các yếu tố.');
            $this->sortQ($L, 'Kéo mỗi bố cục vào nhóm ĐỐI XỨNG hoặc BẤT ĐỐI XỨNG.',
                [['Hai bên giống hệt nhau', 'Đối xứng'], ['Logo chính giữa', 'Đối xứng'],
                 ['Chia đôi cân bằng', 'Đối xứng'],
                 ['Một bên to một bên nhỏ nhưng hài hòa', 'Bất đối xứng'],
                 ['Chữ lệch trái, ảnh lệch phải', 'Bất đối xứng'], ['Bố cục tạp chí hiện đại', 'Bất đối xứng']],
                'Cả hai loại cân bằng đều có thể đẹp nếu dùng đúng.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Cân bằng đối xứng tạo cảm giác trang trọng', 'Đúng'],
                 ['Tương phản giúp yếu tố nổi bật', 'Đúng'],
                 ['Điểm nhấn thu hút mắt nhìn đầu tiên', 'Đúng'],
                 ['Quy tắc một phần ba dùng để đặt điểm nhấn', 'Đúng'],
                 ['Tương phản làm thiết kế mờ nhạt', 'Sai'],
                 ['Không cần điểm nhấn trong thiết kế', 'Sai']],
                'Tương phản và điểm nhấn là công cụ dẫn dắt thị giác.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm TẠO ĐIỂM NHẤN TỐT hoặc KHÔNG TỐT.',
                [['Màu nổi bật giữa nền trầm', 'Tốt'], ['Kích thước lớn khác biệt', 'Tốt'],
                 ['Vị trí theo quy tắc một phần ba', 'Tốt'],
                 ['Mọi yếu tố đều nổi bật như nhau', 'Không tốt'], ['Điểm nhấn đặt ở góc khuất', 'Không tốt'],
                 ['Quá nhiều điểm nhấn', 'Không tốt']],
                'Điểm nhấn chỉ hiệu quả khi nó thực sự nổi bật giữa tổng thể.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cân bằng ___ xứng tạo cảm giác trang trọng, ổn định.', [[0, 'đối']],
                'Đối xứng là hai bên bố cục giống nhau.');
            $this->fill($L, 'Đặt các yếu tố đối lập cạnh nhau gọi là tương ___.', [[0, 'phản']],
                'Tương phản: sáng–tối, to–nhỏ, nóng–lạnh.');
            $this->fill($L, 'Vị trí thu hút mắt nhìn đầu tiên gọi là điểm ___.', [[0, 'nhấn']],
                'Điểm nhấn thường đặt theo quy tắc một phần ba.');
            $this->fill($L, 'Cân bằng bất đối xứng tạo cảm giác năng động, hiện ___.', [[0, 'đại']],
                'Thiết kế hiện đại ưa dùng cân bằng bất đối xứng.');
        }
    }

    private function seedMyThuat112(): void
    {
        $L = 'my-thuat-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nhịp điệu trong thiết kế được tạo ra bằng cách nào?',
                ['Lặp lại các yếu tố có quy luật', 'Dùng ngẫu nhiên không quy luật',
                 'Chỉ dùng một yếu tố duy nhất', 'Xóa bớt mọi chi tiết'], 0,
                'Nhịp điệu là sự lặp lại có quy luật tạo chuyển động cho mắt.');
            $this->quiz($L, 'Tỷ lệ vàng có giá trị xấp xỉ bao nhiêu?',
                ['1 : 1,618', '1 : 1', '1 : 2', '1 : 3'], 0,
                'Tỷ lệ vàng khoảng 1:1,618 được coi là tỷ lệ hài hòa nhất.', 'trung_binh');
            $this->quiz($L, 'Tính thống nhất trong thiết kế có nghĩa là gì?',
                ['Các yếu tố gắn kết thành một tổng thể', 'Mọi thứ phải giống hệt nhau',
                 'Chỉ dùng một màu duy nhất', 'Không có quy tắc nào'], 0,
                'Thống nhất là sự gắn kết, không phải sự đồng nhất máy móc.');
            $this->quiz($L, 'Khoảng trắng (negative space) trong thiết kế có vai trò gì?',
                ['Giúp bố cục thoáng, sang và dễ đọc', 'Là chỗ trống lãng phí cần lấp đầy',
                 'Làm thiết kế trở nên nghèo nàn', 'Không có vai trò gì'], 0,
                'Khoảng trắng là công cụ thiết kế quan trọng, không phải chỗ thừa.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nguyên lý với mô tả của nó.',
                [['Nhịp điệu', 'Lặp lại có quy luật tạo chuyển động'],
                 ['Tỷ lệ', 'Quan hệ kích thước giữa các yếu tố'],
                 ['Thống nhất', 'Gắn kết các yếu tố thành tổng thể'],
                 ['Khoảng trắng', 'Vùng trống giúp bố cục thoáng']],
                'Bốn nguyên lý hoàn thiện một thiết kế chuyên nghiệp.');
            $this->matching($L, 'Nối mỗi khái niệm với ví dụ của nó.',
                [['Nhịp điệu', 'Hàng cột lặp lại đều đặn'],
                 ['Tỷ lệ vàng', '1 : 1,618 trong kiến trúc, logo'],
                 ['Thống nhất', 'Bộ nhận diện dùng chung màu và font'],
                 ['Khoảng trắng', 'Lề rộng quanh nội dung trang']],
                'Nguyên lý được nhận ra qua các ví dụ thực tế.');
            $this->matching($L, 'Nối mỗi con số với ý nghĩa của nó.',
                [['1 : 1,618', 'Tỷ lệ vàng'],
                 ['1/3', 'Quy tắc một phần ba'],
                 ['2', 'Số nhóm trong cân bằng đối xứng'],
                 ['0', 'Không nên có quá nhiều điểm nhấn']],
                'Con số giúp ghi nhớ các quy tắc thiết kế.');
            $this->matching($L, 'Nối mỗi lỗi với cách khắc phục.',
                [['Bố cục rối', 'Tăng khoảng trắng, giảm yếu tố'],
                 ['Thiếu điểm nhấn', 'Tạo tương phản cho yếu tố chính'],
                 ['Rời rạc', 'Dùng màu sắc, font chữ thống nhất'],
                 ['Nặng nề', 'Cân bằng lại phân bổ thị giác']],
                'Mọi lỗi bố cục đều có cách khắc phục.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm CÓ NHỊP ĐIỆU hoặc KHÔNG CÓ NHỊP ĐIỆU.',
                [['Hàng cột lặp đều', 'Có nhịp điệu'], ['Họa tiết lặp có quy luật', 'Có nhịp điệu'],
                 ['Dãy đèn đường đều nhau', 'Có nhịp điệu'],
                 ['Xếp ngẫu nhiên lộn xộn', 'Không có nhịp điệu'], ['Một chi tiết đơn lẻ', 'Không có nhịp điệu'],
                 ['Màu sắc hỗn độn', 'Không có nhịp điệu']],
                'Nhịp điệu cần sự lặp lại có quy luật.');
            $this->sortQ($L, 'Kéo mỗi thiết kế vào nhóm CÓ KHOẢNG TRẮNG TỐT hoặc QUÁ CHẬT CHỘI.',
                [['Lề rộng, thoáng', 'Khoảng trắng tốt'], ['Nội dung có chỗ thở', 'Khoảng trắng tốt'],
                 ['Ít yếu tố, sang trọng', 'Khoảng trắng tốt'],
                 ['Nhồi nhét mọi chỗ trống', 'Quá chật chội'], ['Chữ chen chúc', 'Quá chật chội'],
                 ['Không có lề', 'Quá chật chội']],
                'Khoảng trắng là dấu hiệu của thiết kế chuyên nghiệp.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nhịp điệu là sự lặp lại có quy luật', 'Đúng'],
                 ['Tỷ lệ vàng khoảng 1:1,618', 'Đúng'],
                 ['Khoảng trắng giúp bố cục thoáng', 'Đúng'],
                 ['Thống nhất là sự gắn kết tổng thể', 'Đúng'],
                 ['Khoảng trắng là chỗ trống lãng phí', 'Sai'],
                 ['Thống nhất nghĩa là mọi thứ giống hệt nhau', 'Sai']],
                'Khoảng trắng có giá trị; thống nhất không đồng nghĩa với đơn điệu.');
            $this->sortQ($L, 'Kéo mỗi cặp tỷ lệ vào nhóm CÂN ĐỐI HÀI HÒA hoặc LỆCH LẠC.',
                [['1 : 1,618 (tỷ lệ vàng)', 'Hài hòa'], ['2 : 3', 'Hài hòa'], ['3 : 5', 'Hài hòa'],
                 ['1 : 10', 'Lệch lạc'], ['Quá to so với khung', 'Lệch lạc'],
                 ['Quá nhỏ khó nhìn', 'Lệch lạc']],
                'Tỷ lệ hài hòa tạo cảm giác dễ chịu cho mắt.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Sự lặp lại có quy luật tạo chuyển động cho mắt gọi là nhịp ___.', [[0, 'điệu']],
                'Nhịp điệu thị giác giống như nhịp điệu trong âm nhạc.');
            $this->fill($L, 'Tỷ lệ vàng có giá trị xấp xỉ 1 : 1,___.', [[0, '618']],
                '1,618 là con số của tỷ lệ hài hòa.');
            $this->fill($L, 'Vùng trống giúp bố cục thoáng gọi là khoảng ___.', [[0, 'trắng']],
                'Khoảng trắng (negative space) là công cụ thiết kế.');
            $this->fill($L, 'Sự gắn kết các yếu tố thành tổng thể gọi là tính thống ___.', [[0, 'nhất']],
                'Thống nhất qua màu sắc, font chữ, phong cách.');
        }
    }

    private function seedMyThuat113(): void
    {
        $L = 'my-thuat-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Vòng tròn màu cơ bản gồm bao nhiêu màu?',
                ['12 màu', '6 màu', '3 màu', '24 màu'], 0,
                'Vòng tròn màu chuẩn gồm 12 màu: 3 bậc 1, 3 bậc 2, 6 bậc 3.');
            $this->quiz($L, 'Ba màu bậc 1 (màu gốc) là những màu nào?',
                ['Đỏ, vàng, lam', 'Cam, lục, tím', 'Đen, trắng, xám', 'Hồng, nâu, be'], 0,
                'Đỏ, vàng, lam là 3 màu gốc không pha được từ màu khác.');
            $this->quiz($L, 'Cặp màu bổ túc là cặp màu như thế nào?',
                ['Hai màu đối diện nhau trên vòng tròn màu', 'Hai màu kề nhau',
                 'Hai màu giống nhau', 'Màu đen và trắng'], 0,
                'Màu bổ túc đối diện nhau, đặt cạnh nhau tạo tương phản mạnh.', 'trung_binh');
            $this->quiz($L, 'Nhóm màu nóng gồm những màu nào?',
                ['Đỏ, cam, vàng', 'Lam, lục, tím', 'Đen, trắng, xám', 'Nâu, be, hồng'], 0,
                'Màu nóng gợi cảm giác ấm áp, năng lượng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bậc màu với các màu thuộc bậc đó.',
                [['Màu bậc 1', 'Đỏ, vàng, lam'],
                 ['Màu bậc 2', 'Cam, lục, tím'],
                 ['Màu bậc 3', 'Pha từ một màu bậc 1 và một màu bậc 2'],
                 ['Màu trung tính', 'Đen, trắng, xám']],
                'Ba bậc màu tạo nên vòng tròn 12 màu.');
            $this->matching($L, 'Nối mỗi cặp màu với quan hệ của chúng.',
                [['Đỏ – xanh lá', 'Cặp bổ túc'],
                 ['Vàng – tím', 'Cặp bổ túc'],
                 ['Đỏ – cam', 'Màu kề nhau'],
                 ['Lam – tím', 'Màu kề nhau']],
                'Bổ túc đối diện nhau, màu kề nhau nằm cạnh nhau.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nhóm màu với cảm giác nó gợi ra.',
                [['Màu nóng', 'Năng lượng, ấm áp'],
                 ['Màu lạnh', 'Yên tĩnh, mát mẻ'],
                 ['Màu sáng', 'Nhẹ nhàng, tươi vui'],
                 ['Màu trầm', 'Sang trọng, nghiêm túc']],
                'Nhiệt độ màu ảnh hưởng đến cảm xúc người xem.');
            $this->matching($L, 'Nối mỗi màu bậc 2 với cách pha của nó.',
                [['Cam', 'Đỏ + vàng'],
                 ['Lục', 'Vàng + lam'],
                 ['Tím', 'Đỏ + lam'],
                 ['Nâu', 'Pha nhiều màu với nhau']],
                'Màu bậc 2 pha từ hai màu bậc 1.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi màu vào nhóm MÀU NÓNG hoặc MÀU LẠNH.',
                [['Đỏ', 'Màu nóng'], ['Cam', 'Màu nóng'], ['Vàng', 'Màu nóng'],
                 ['Lam', 'Màu lạnh'], ['Lục', 'Màu lạnh'], ['Tím', 'Màu lạnh']],
                'Nóng: đỏ–cam–vàng; lạnh: lam–lục–tím.');
            $this->sortQ($L, 'Kéo mỗi màu vào đúng BẬC của nó.',
                [['Đỏ', 'Bậc 1'], ['Vàng', 'Bậc 1'], ['Lam', 'Bậc 1'],
                 ['Cam', 'Bậc 2'], ['Lục', 'Bậc 2'], ['Tím', 'Bậc 2']],
                'Bậc 1 là màu gốc, bậc 2 pha từ hai màu bậc 1.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Vòng tròn màu có 12 màu', 'Đúng'],
                 ['Đỏ, vàng, lam là màu bậc 1', 'Đúng'],
                 ['Màu bổ túc đối diện nhau trên vòng tròn', 'Đúng'],
                 ['Màu nóng gợi năng lượng', 'Đúng'],
                 ['Cam là màu bậc 1', 'Sai'],
                 ['Đỏ và cam là cặp bổ túc', 'Sai']],
                'Cam là màu bậc 2; bổ túc của đỏ là xanh lá.');
            $this->sortQ($L, 'Kéo mỗi cặp màu vào nhóm BỔ TÚC hoặc KỀ NHAU.',
                [['Đỏ – xanh lá', 'Bổ túc'], ['Vàng – tím', 'Bổ túc'], ['Cam – lam', 'Bổ túc'],
                 ['Đỏ – cam', 'Kề nhau'], ['Vàng – lục', 'Kề nhau'], ['Lam – tím', 'Kề nhau']],
                'Bổ túc đối diện nhau, kề nhau nằm cạnh nhau trên vòng tròn.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Vòng tròn màu cơ bản gồm ___ màu.', [[0, '12']],
                '12 màu: 3 bậc 1, 3 bậc 2, 6 bậc 3.');
            $this->fill($L, 'Ba màu bậc 1 là đỏ, vàng và ___.', [[0, 'lam']],
                'Đỏ, vàng, lam là ba màu gốc.');
            $this->fill($L, 'Hai màu đối diện nhau trên vòng tròn gọi là cặp màu bổ ___.', [[0, 'túc']],
                'Màu bổ túc tạo tương phản mạnh khi đặt cạnh nhau.');
            $this->fill($L, 'Màu cam được pha từ đỏ và ___.', [[0, 'vàng']],
                'Cam là màu bậc 2: đỏ + vàng.');
        }
    }

    private function seedMyThuat114(): void
    {
        $L = 'my-thuat-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phối màu đơn sắc là cách phối như thế nào?',
                ['Một màu với nhiều sắc độ khác nhau', 'Nhiều màu đối diện nhau',
                 'Chỉ dùng màu đen trắng', 'Không dùng màu nào'], 0,
                'Đơn sắc dùng một màu gốc với các sắc độ đậm nhạt khác nhau.');
            $this->quiz($L, 'Phối màu bổ túc sử dụng những màu nào?',
                ['Hai màu đối diện nhau trên vòng tròn màu', 'Hai màu kề nhau',
                 'Ba màu bất kỳ', 'Chỉ một màu'], 0,
                'Bổ túc tạo tương phản mạnh, cần tiết chế khi dùng.', 'trung_binh');
            $this->quiz($L, 'Màu xanh lam thường gợi cảm giác gì?',
                ['Tin cậy, yên bình', 'Nóng nảy, giận dữ',
                 'Buồn bã tuyệt vọng', 'Không gợi cảm giác gì'], 0,
                'Xanh lam gắn với bầu trời, biển cả: tin cậy và yên bình.');
            $this->quiz($L, 'Nguyên tắc quan trọng khi chọn màu cho thiết kế là gì?',
                ['Phù hợp với thông điệp và đối tượng', 'Dùng càng nhiều màu càng tốt',
                 'Chỉ dùng màu mình thích', 'Bắt chước đối thủ'], 0,
                'Màu sắc phải phục vụ thông điệp và đối tượng người xem.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi công thức phối màu với mô tả của nó.',
                [['Đơn sắc', 'Một màu với nhiều sắc độ'],
                 ['Tương đồng', 'Các màu kề nhau trên vòng tròn'],
                 ['Bổ túc', 'Hai màu đối diện nhau'],
                 ['Bộ ba', 'Ba màu tạo tam giác đều']],
                'Bốn công thức phối màu cơ bản của thiết kế.');
            $this->matching($L, 'Nối mỗi màu với cảm xúc nó thường gợi ra.',
                [['Đỏ', 'Nhiệt huyết, mạnh mẽ'],
                 ['Xanh lam', 'Tin cậy, yên bình'],
                 ['Xanh lá', 'Thiên nhiên, tươi mới'],
                 ['Vàng', 'Lạc quan, vui vẻ']],
                'Tâm lý màu sắc là công cụ truyền thông hiệu quả.');
            $this->matching($L, 'Nối mỗi màu với cảm xúc tiếp theo.',
                [['Đen', 'Sang trọng, quyền lực'],
                 ['Trắng', 'Tinh khiết, tối giản'],
                 ['Tím', 'Sáng tạo, huyền bí'],
                 ['Cam', 'Trẻ trung, năng động']],
                'Mỗi màu mang một ngôn ngữ cảm xúc riêng.');
            $this->matching($L, 'Nối mỗi lĩnh vực với màu sắc thường dùng.',
                [['Ngân hàng', 'Xanh lam (tin cậy)'],
                 ['Thực phẩm nhanh', 'Đỏ, vàng (kích thích)'],
                 ['Môi trường', 'Xanh lá (thiên nhiên)'],
                 ['Thời trang cao cấp', 'Đen, trắng (sang trọng)']],
                'Thương hiệu chọn màu theo thông điệp muốn truyền tải.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cách phối màu vào đúng công thức của nó.',
                [['Một màu nhiều sắc độ', 'Đơn sắc'], ['Xanh lam đậm nhạt', 'Đơn sắc'],
                 ['Đỏ, cam, vàng', 'Tương đồng'], ['Lam, lục, lam ngọc', 'Tương đồng'],
                 ['Đỏ – xanh lá', 'Bổ túc'], ['Vàng – tím', 'Bổ túc']],
                'Đơn sắc an toàn, tương đồng hài hòa, bổ túc nổi bật.');
            $this->sortQ($L, 'Kéo mỗi màu vào nhóm CẢM XÚC TÍCH CỰC hoặc TRANG TRỌNG.',
                [['Vàng (lạc quan)', 'Tích cực'], ['Cam (năng động)', 'Tích cực'], ['Xanh lá (tươi mới)', 'Tích cực'],
                 ['Đen (sang trọng)', 'Trang trọng'], ['Trắng (tinh khiết)', 'Trang trọng'],
                 ['Xám (trung tính)', 'Trang trọng']],
                'Cảm xúc màu sắc phụ thuộc vào văn hóa và ngữ cảnh.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Đơn sắc là một màu nhiều sắc độ', 'Đúng'],
                 ['Bổ túc là hai màu đối diện nhau', 'Đúng'],
                 ['Xanh lam gợi sự tin cậy', 'Đúng'],
                 ['Chọn màu phải phù hợp thông điệp', 'Đúng'],
                 ['Dùng càng nhiều màu càng đẹp', 'Sai'],
                 ['Màu sắc không ảnh hưởng cảm xúc', 'Sai']],
                'Quá nhiều màu gây rối; màu sắc tác động mạnh đến cảm xúc.');
            $this->sortQ($L, 'Kéo mỗi màu vào nhóm NÓNG hoặc LẠNH theo tâm lý.',
                [['Đỏ (nhiệt huyết)', 'Nóng'], ['Cam (trẻ trung)', 'Nóng'], ['Vàng (lạc quan)', 'Nóng'],
                 ['Lam (tin cậy)', 'Lạnh'], ['Lục (thiên nhiên)', 'Lạnh'], ['Tím (huyền bí)', 'Lạnh']],
                'Nhiệt độ tâm lý của màu ảnh hưởng đến không khí thiết kế.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phối một màu với nhiều sắc độ gọi là phối màu đơn ___.', [[0, 'sắc']],
                'Đơn sắc an toàn, tinh tế, dễ dùng.');
            $this->fill($L, 'Hai màu đối diện nhau trên vòng tròn gọi là phối màu bổ ___.', [[0, 'túc']],
                'Bổ túc tạo tương phản mạnh mẽ.');
            $this->fill($L, 'Màu xanh lam thường gợi cảm giác tin ___.', [[0, 'cậy']],
                'Nhiều ngân hàng, công nghệ dùng xanh lam.');
            $this->fill($L, 'Màu đỏ thường gợi sự nhiệt ___.', [[0, 'huyết']],
                'Đỏ là màu của năng lượng và đam mê.');
        }
    }

    // ================= MỸ THUẬT – LỚP 12 =================

    private function seedMyThuat121(): void
    {
        $L = 'my-thuat-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Logo là gì trong hệ nhận diện thương hiệu?',
                ['Dấu hiệu nhận biết cốt lõi của thương hiệu', 'Một bức tranh trang trí',
                 'Một bài hát quảng cáo', 'Một đoạn phim giới thiệu'], 0,
                'Logo là dấu hiệu nhận biết trung tâm, xuất hiện trên mọi ấn phẩm.');
            $this->quiz($L, 'Loại logo chỉ dùng chữ để thể hiện tên thương hiệu gọi là gì?',
                ['Logotype', 'Symbol', 'Mascot', 'Slogan'], 0,
                'Logotype là logo dạng chữ, thiết kế riêng cho tên thương hiệu.', 'trung_binh');
            $this->quiz($L, 'Một logo tốt cần có phẩm chất nào?',
                ['Đơn giản, dễ nhớ, bền vững theo thời gian', 'Càng phức tạp càng tốt',
                 'Thay đổi liên tục theo mốt', 'Chỉ dùng được ở một kích thước'], 0,
                'Logo tốt đơn giản, dễ nhớ và dùng được ở mọi kích thước.');
            $this->quiz($L, 'Hệ nhận diện thương hiệu gồm những gì?',
                ['Logo, màu sắc, font chữ, danh thiếp, bao bì thống nhất',
                 'Chỉ một logo duy nhất', 'Chỉ màu sắc', 'Chỉ danh thiếp'], 0,
                'Nhận diện thương hiệu là hệ thống các yếu tố thống nhất với nhau.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại logo với mô tả của nó.',
                [['Logotype', 'Logo dạng chữ'],
                 ['Symbol', 'Logo dạng biểu tượng'],
                 ['Logo kết hợp', 'Cả chữ và biểu tượng'],
                 ['Mascot', 'Logo dạng nhân vật']],
                'Bốn loại logo phổ biến trong thiết kế thương hiệu.');
            $this->matching($L, 'Nối mỗi yếu tố nhận diện với vai trò của nó.',
                [['Logo', 'Dấu hiệu nhận biết cốt lõi'],
                 ['Màu sắc', 'Tạo cảm xúc thương hiệu'],
                 ['Font chữ', 'Thể hiện tính cách thương hiệu'],
                 ['Bao bì', 'Điểm chạm với khách hàng']],
                'Mọi yếu tố phải thống nhất với nhau.');
            $this->matching($L, 'Nối mỗi phẩm chất với ý nghĩa của nó.',
                [['Đơn giản', 'Dễ nhìn, dễ áp dụng'],
                 ['Dễ nhớ', 'Khách hàng nhận ra ngay'],
                 ['Bền vững', 'Không lỗi mốt theo thời gian'],
                 ['Linh hoạt', 'Dùng được mọi kích thước']],
                'Bốn phẩm chất của logo tốt.');
            $this->matching($L, 'Nối mỗi ấn phẩm với vị trí của logo.',
                [['Danh thiếp', 'Logo ở mặt trước'],
                 ['Bao bì', 'Logo nổi bật trên sản phẩm'],
                 ['Website', 'Logo ở góc trên trái'],
                 ['Biển hiệu', 'Logo cỡ lớn ngoài cửa hàng']],
                'Logo xuất hiện nhất quán trên mọi điểm chạm.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi loại logo vào đúng nhóm của nó.',
                [['Logo chỉ có chữ', 'Logotype'], ['Tên thương hiệu cách điệu', 'Logotype'],
                 ['Hình biểu tượng', 'Symbol'], ['Icon không chữ', 'Symbol'],
                 ['Chữ kết hợp hình', 'Kết hợp'], ['Nhân vật đại diện', 'Mascot']],
                'Phân loại logo theo hình thức thể hiện.');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm LOGO TỐT hoặc LOGO KÉM.',
                [['Đơn giản', 'Logo tốt'], ['Dễ nhớ', 'Logo tốt'], ['Dùng được mọi kích thước', 'Logo tốt'],
                 ['Quá phức tạp', 'Logo kém'], ['Khó nhận biết', 'Logo kém'], ['Lỗi mốt nhanh', 'Logo kém']],
                'Logo tốt bền vững, logo kém gây nhầm lẫn.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Logo là dấu hiệu nhận biết cốt lõi', 'Đúng'],
                 ['Logotype là logo dạng chữ', 'Đúng'],
                 ['Logo tốt cần đơn giản, dễ nhớ', 'Đúng'],
                 ['Nhận diện gồm nhiều yếu tố thống nhất', 'Đúng'],
                 ['Logo càng phức tạp càng tốt', 'Sai'],
                 ['Mỗi ấn phẩm dùng một logo khác nhau', 'Sai']],
                'Logo phải đơn giản và nhất quán trên mọi ấn phẩm.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUỘC HỆ NHẬN DIỆN hoặc KHÔNG THUỘC.',
                [['Logo', 'Thuộc hệ nhận diện'], ['Màu sắc thương hiệu', 'Thuộc hệ nhận diện'],
                 ['Font chữ riêng', 'Thuộc hệ nhận diện'],
                 ['Sở thích cá nhân của giám đốc', 'Không thuộc'], ['Màu ngẫu nhiên mỗi lần in', 'Không thuộc'],
                 ['Font chữ thay đổi tùy hứng', 'Không thuộc']],
                'Nhận diện thương hiệu đòi hỏi sự nhất quán tuyệt đối.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dấu hiệu nhận biết cốt lõi của thương hiệu gọi là ___.', [[0, 'logo']],
                'Logo là trung tâm của hệ nhận diện thương hiệu.');
            $this->fill($L, 'Logo chỉ dùng chữ thể hiện tên thương hiệu gọi là ___.', [[0, 'logotype']],
                'Logotype = logo dạng chữ.');
            $this->fill($L, 'Logo tốt phải đơn giản, dễ nhớ và ___ vững theo thời gian.', [[0, 'bền']],
                'Logo bền vững không bị lỗi mốt.');
            $this->fill($L, 'Hệ nhận diện gồm logo, màu sắc, font chữ phải ___ nhất với nhau.', [[0, 'thống']],
                'Thống nhất tạo nên sức mạnh thương hiệu.');
        }
    }

    private function seedMyThuat122(): void
    {
        $L = 'my-thuat-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Poster hiệu quả cần có thứ bậc thông tin như thế nào?',
                ['Tiêu đề nổi bật, hình ảnh thu hút, chi tiết vừa đủ',
                 'Mọi thông tin to bằng nhau', 'Chỉ có hình không có chữ', 'Chỉ có chữ không có hình'], 0,
                'Thứ bậc rõ ràng giúp người xem nắm thông tin theo trình tự.');
            $this->quiz($L, 'Typography là nghệ thuật gì?',
                ['Nghệ thuật chữ trong thiết kế', 'Nghệ thuật vẽ tranh',
                 'Nghệ thuật chụp ảnh', 'Nghệ thuật làm phim'], 0,
                'Typography là nghệ thuật sắp đặt và thiết kế chữ.');
            $this->quiz($L, 'Font serif thường tạo cảm giác gì?',
                ['Trang trọng, cổ điển', 'Hiện đại, tối giản',
                 'Vui nhộn, trẻ con', 'Không có cảm giác gì'], 0,
                'Serif có chân chữ, tạo cảm giác trang trọng, truyền thống.', 'trung_binh');
            $this->quiz($L, 'Lưới bố cục (grid) trong thiết kế có tác dụng gì?',
                ['Sắp xếp các yếu tố gọn gàng, nhất quán', 'Làm thiết kế rối mắt',
                 'Xóa bỏ mọi quy tắc', 'Chỉ dùng để trang trí'], 0,
                'Lưới giúp căn chỉnh các yếu tố thẳng hàng, chuyên nghiệp.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thành phần poster với vai trò của nó.',
                [['Tiêu đề', 'Thông tin nổi bật nhất'],
                 ['Hình ảnh', 'Thu hút sự chú ý'],
                 ['Thông tin chi tiết', 'Nội dung vừa đủ, dễ đọc'],
                 ['Lời kêu gọi', 'Thúc đẩy người xem hành động']],
                'Bốn thành phần của poster hiệu quả.');
            $this->matching($L, 'Nối mỗi loại font với cảm giác của nó.',
                [['Serif', 'Trang trọng, cổ điển'],
                 ['Sans-serif', 'Hiện đại, tối giản'],
                 ['Script', 'Mềm mại, lãng mạn'],
                 ['Display', 'Ấn tượng, trang trí']],
                'Chọn font phù hợp với thông điệp thiết kế.');
            $this->matching($L, 'Nối mỗi thuật ngữ typography với ý nghĩa.',
                [['Cỡ chữ', 'Độ lớn của chữ'],
                 ['Giãn dòng', 'Khoảng cách giữa các dòng'],
                 ['Căn lề', 'Cách sắp xếp chữ theo lề'],
                 ['Đậm nhạt', 'Độ dày của nét chữ']],
                'Bốn yếu tố cơ bản của typography.');
            $this->matching($L, 'Nối mỗi công cụ với tác dụng của nó.',
                [['Lưới bố cục', 'Căn chỉnh gọn gàng'],
                 ['Thứ bậc chữ', 'Dẫn dắt mắt đọc'],
                 ['Khoảng trắng', 'Tạo sự thoáng đãng'],
                 ['Màu sắc', 'Tạo cảm xúc']],
                'Công cụ thiết kế phục vụ poster hiệu quả.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi loại font vào nhóm SERIF hoặc SANS-SERIF theo cảm giác.',
                [['Trang trọng, cổ điển', 'Serif'], ['Có chân chữ', 'Serif'],
                 ['Báo chí truyền thống', 'Serif'],
                 ['Hiện đại, tối giản', 'Sans-serif'], ['Không chân chữ', 'Sans-serif'],
                 ['Website công nghệ', 'Sans-serif']],
                'Serif có chân, sans-serif không chân.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm QUAN TRỌNG NHẤT hoặc CHI TIẾT trong poster.',
                [['Tiêu đề chính', 'Quan trọng nhất'], ['Hình ảnh chủ đạo', 'Quan trọng nhất'],
                 ['Ngày giờ sự kiện', 'Chi tiết'], ['Địa điểm', 'Chi tiết'],
                 ['Giá vé', 'Chi tiết'], ['Thông tin liên hệ', 'Chi tiết']],
                'Thứ bậc thông tin: chính nổi bật, chi tiết vừa đủ.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Poster cần thứ bậc thông tin rõ ràng', 'Đúng'],
                 ['Typography là nghệ thuật chữ', 'Đúng'],
                 ['Lưới bố cục giúp sắp xếp gọn gàng', 'Đúng'],
                 ['Serif tạo cảm giác trang trọng', 'Đúng'],
                 ['Poster nên nhồi nhét thật nhiều chữ', 'Sai'],
                 ['Dùng 5-6 font khác nhau trong một poster là tốt', 'Sai']],
                'Poster cần thoáng; chỉ nên dùng 1-2 font.');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm NÊN LÀM hoặc KHÔNG NÊN khi thiết kế poster.',
                [['Tạo thứ bậc chữ rõ ràng', 'Nên làm'], ['Dùng lưới căn chỉnh', 'Nên làm'],
                 ['Chừa khoảng trắng', 'Nên làm'],
                 ['Nhồi nhét chữ', 'Không nên'], ['Dùng quá nhiều font', 'Không nên'],
                 ['Màu sắc hỗn độn', 'Không nên']],
                'Poster tốt: rõ ràng, gọn gàng, có điểm nhấn.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nghệ thuật chữ trong thiết kế gọi là ___.', [[0, 'typography']],
                'Typography quyết định vẻ đẹp của phần chữ.');
            $this->fill($L, 'Font có chân chữ tạo cảm giác trang trọng gọi là ___.', [[0, 'serif']],
                'Serif = font có chân.');
            $this->fill($L, 'Công cụ giúp căn chỉnh các yếu tố gọn gàng gọi là ___ bố cục.', [[0, 'lưới']],
                'Lưới (grid) là khung căn chỉnh vô hình.');
            $this->fill($L, 'Trong poster, ___ đề là thông tin nổi bật nhất.', [[0, 'tiêu']],
                'Tiêu đề thu hút sự chú ý đầu tiên.');
        }
    }

    private function seedMyThuat123(): void
    {
        $L = 'my-thuat-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kiến trúc cổ điển Hy Lạp – La Mã nổi bật với yếu tố nào?',
                ['Hệ cột và thức cột nghiêm ngặt', 'Vòm nhọn và tháp cao',
                 'Mái cong và dùng gỗ', 'Thép và kính'], 0,
                'Thức cột (Doric, Ionic, Corinthian) là dấu ấn của kiến trúc cổ điển.');
            $this->quiz($L, 'Kiến trúc Gothic có đặc điểm nào?',
                ['Vòm nhọn, cửa sổ kính màu, tháp cao vút', 'Mái bằng bê tông',
                 'Nhà tranh vách đất', 'Chỉ xây nhà một tầng'], 0,
                'Gothic vươn cao lên trời với vòm nhọn và kính màu rực rỡ.');
            $this->quiz($L, 'Kiến trúc hiện đại đề cao điều gì?',
                ['Công năng và vật liệu mới (thép, kính, bê tông)', 'Trang trí rườm rà',
                 'Bắt chước hoàn toàn cổ điển', 'Chỉ dùng gỗ và tranh'], 0,
                'Hiện đại: hình thức theo công năng, vật liệu công nghiệp.');
            $this->quiz($L, 'Kiến trúc truyền thống Việt Nam có đặc điểm nào?',
                ['Mái cong, dùng gỗ và ngói, hài hòa thiên nhiên', 'Tháp nhọn bằng đá',
                 'Vòm cuốn bằng gạch', 'Nhà kính toàn bộ'], 0,
                'Đình chùa Việt Nam mái cong, kết cấu gỗ, hòa với thiên nhiên.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phong cách với đặc điểm của nó.',
                [['Cổ điển', 'Hệ cột, thức cột nghiêm ngặt'],
                 ['Gothic', 'Vòm nhọn, kính màu, tháp cao'],
                 ['Hiện đại', 'Công năng, thép, kính, bê tông'],
                 ['Truyền thống Việt Nam', 'Mái cong, gỗ, ngói']],
                'Bốn phong cách kiến trúc tiêu biểu.');
            $this->matching($L, 'Nối mỗi công trình với phong cách của nó.',
                [['Đền Parthenon', 'Cổ điển Hy Lạp'],
                 ['Nhà thờ Đức Bà Paris', 'Gothic'],
                 ['Nhà thép kính', 'Hiện đại'],
                 ['Chùa Một Cột', 'Truyền thống Việt Nam']],
                'Công trình tiêu biểu minh họa cho phong cách.');
            $this->matching($L, 'Nối mỗi vật liệu với phong cách thường dùng.',
                [['Đá cẩm thạch', 'Cổ điển'],
                 ['Đá và kính màu', 'Gothic'],
                 ['Thép, kính', 'Hiện đại'],
                 ['Gỗ, ngói', 'Truyền thống Việt Nam']],
                'Vật liệu định hình phong cách kiến trúc.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Thức cột', 'Hệ quy tắc về cột cổ điển'],
                 ['Vòm nhọn', 'Kết cấu đặc trưng Gothic'],
                 ['Công năng', 'Mục đích sử dụng của công trình'],
                 ['Mái cong', 'Dấu ấn kiến trúc Việt Nam']],
                'Từ vựng cơ bản của lịch sử kiến trúc.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào đúng phong cách: CỔ ĐIỂN, GOTHIC, HIỆN ĐẠI hoặc TRUYỀN THỐNG VN.',
                [['Hệ cột nghiêm ngặt', 'Cổ điển'], ['Thức cột Doric', 'Cổ điển'],
                 ['Vòm nhọn', 'Gothic'], ['Cửa sổ kính màu', 'Gothic'],
                 ['Thép và kính', 'Hiện đại'], ['Đề cao công năng', 'Hiện đại']],
                'Mỗi phong cách có ngôn ngữ hình thức riêng.');
            $this->sortQ($L, 'Kéo mỗi công trình vào đúng phong cách của nó.',
                [['Đền Parthenon', 'Cổ điển'], ['Đấu trường Colosseum', 'Cổ điển'],
                 ['Nhà thờ Đức Bà Paris', 'Gothic'], ['Tháp đồng hồ Big Ben', 'Gothic'],
                 ['Chùa Một Cột', 'Truyền thống VN'], ['Nhà Rồng', 'Truyền thống VN']],
                'Nhận diện phong cách qua công trình thực tế.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Kiến trúc cổ điển dùng hệ cột', 'Đúng'],
                 ['Gothic có vòm nhọn và kính màu', 'Đúng'],
                 ['Hiện đại đề cao công năng', 'Đúng'],
                 ['Kiến trúc Việt Nam có mái cong', 'Đúng'],
                 ['Gothic dùng mái cong gỗ', 'Sai'],
                 ['Hiện đại chỉ dùng gỗ và tranh', 'Sai']],
                'Mái cong gỗ là của Việt Nam; hiện đại dùng thép, kính, bê tông.');
            $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm TỰ NHIÊN TRUYỀN THỐNG hoặc CÔNG NGHIỆP HIỆN ĐẠI.',
                [['Gỗ', 'Tự nhiên'], ['Ngói', 'Tự nhiên'], ['Đá', 'Tự nhiên'],
                 ['Thép', 'Công nghiệp'], ['Kính', 'Công nghiệp'], ['Bê tông', 'Công nghiệp']],
                'Vật liệu công nghiệp làm nên kiến trúc hiện đại.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Kiến trúc cổ điển nổi bật với hệ cột và thức ___.', [[0, 'cột']],
                'Thức cột là quy tắc nghiêm ngặt về cột.');
            $this->fill($L, 'Kiến trúc Gothic đặc trưng với vòm ___ và cửa sổ kính màu.', [[0, 'nhọn']],
                'Vòm nhọn giúp công trình vươn cao.');
            $this->fill($L, 'Kiến trúc hiện đại đề cao ___ năng sử dụng.', [[0, 'công']],
                'Hình thức phục vụ công năng là triết lý hiện đại.');
            $this->fill($L, 'Kiến trúc truyền thống Việt Nam có mái ___, dùng gỗ và ngói.', [[0, 'cong']],
                'Mái cong là dấu ấn đặc trưng của đình chùa Việt Nam.');
        }
    }

    private function seedMyThuat124(): void
    {
        $L = 'my-thuat-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Điêu khắc tròn khác phù điêu ở điểm nào?',
                ['Xem được từ mọi phía', 'Chỉ xem được một mặt',
                 'Không có hình khối', 'Chỉ vẽ trên giấy'], 0,
                'Điêu khắc tròn là tượng độc lập, phù điêu là hình nổi trên mặt phẳng.');
            $this->quiz($L, 'Phù điêu là gì?',
                ['Hình nổi trên mặt phẳng', 'Tượng đứng độc lập',
                 'Tranh vẽ trên tường', 'Ảnh chụp'], 0,
                'Phù điêu có nổi cao và nổi thấp, gắn trên mặt phẳng nền.');
            $this->quiz($L, 'Chất liệu truyền thống nào thường dùng trong điêu khắc?',
                ['Đá, gỗ, đồng', 'Giấy, bút chì', 'Vải, len', 'Nhựa đường'], 0,
                'Đá, gỗ, đồng là ba chất liệu điêu khắc truyền thống.');
            $this->quiz($L, 'Tượng đài trong không gian công cộng có vai trò gì?',
                ['Tôn vinh nhân vật, sự kiện lịch sử', 'Trang trí cho vui mắt',
                 'Quảng cáo sản phẩm', 'Không có vai trò gì'], 0,
                'Tượng đài mang ý nghĩa tưởng niệm, giáo dục truyền thống.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hình thức điêu khắc với đặc điểm của nó.',
                [['Điêu khắc tròn', 'Tượng độc lập, xem mọi phía'],
                 ['Phù điêu nổi cao', 'Hình nổi hẳn trên mặt phẳng'],
                 ['Phù điêu nổi thấp', 'Hình nổi nhẹ trên mặt phẳng'],
                 ['Tượng đài', 'Tượng lớn nơi công cộng']],
                'Các hình thức điêu khắc cơ bản.');
            $this->matching($L, 'Nối mỗi chất liệu với đặc tính của nó.',
                [['Đá', 'Bền vững, chắc chắn'],
                 ['Gỗ', 'Ấm áp, dễ chạm khắc'],
                 ['Đồng', 'Sang trọng, đúc được chi tiết'],
                 ['Đất nung', 'Mộc mạc, gần gũi']],
                'Chất liệu ảnh hưởng đến cảm xúc của tác phẩm.');
            $this->matching($L, 'Nối mỗi công đoạn với mô tả của nó.',
                [['Tạo mẫu', 'Dựng hình dáng ban đầu'],
                 ['Chạm khắc', 'Gọt đẽo tạo hình'],
                 ['Đúc đồng', 'Rót kim loại vào khuôn'],
                 ['Hoàn thiện', 'Mài nhẵn, đánh bóng']],
                'Quy trình tạo ra một tác phẩm điêu khắc.');
            $this->matching($L, 'Nối mỗi vị trí đặt tượng với mục đích.',
                [['Quảng trường', 'Tôn vinh, tưởng niệm'],
                 ['Bảo tàng', 'Trưng bày nghệ thuật'],
                 ['Đình chùa', 'Tín ngưỡng'],
                 ['Công viên', 'Trang trí không gian']],
                'Vị trí đặt tượng gắn với mục đích sử dụng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi mô tả vào nhóm ĐIÊU KHẮC TRÒN hoặc PHÙ ĐIÊU.',
                [['Tượng đứng độc lập', 'Tròn'], ['Xem được mọi phía', 'Tròn'], ['Tượng đài', 'Tròn'],
                 ['Hình nổi trên tường', 'Phù điêu'], ['Nổi cao, nổi thấp', 'Phù điêu'],
                 ['Gắn trên mặt phẳng', 'Phù điêu']],
                'Tròn độc lập, phù điêu gắn nền.');
            $this->sortQ($L, 'Kéo mỗi chất liệu vào nhóm TRUYỀN THỐNG hoặc HIỆN ĐẠI.',
                [['Đá', 'Truyền thống'], ['Gỗ', 'Truyền thống'], ['Đồng', 'Truyền thống'],
                 ['Composite', 'Hiện đại'], ['Inox', 'Hiện đại'], ['Nhựa tổng hợp', 'Hiện đại']],
                'Chất liệu mới mở rộng khả năng sáng tạo.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Điêu khắc tròn xem được mọi phía', 'Đúng'],
                 ['Phù điêu là hình nổi trên mặt phẳng', 'Đúng'],
                 ['Đá, gỗ, đồng là chất liệu truyền thống', 'Đúng'],
                 ['Tượng đài tôn vinh nhân vật lịch sử', 'Đúng'],
                 ['Phù điêu là tượng đứng độc lập', 'Sai'],
                 ['Điêu khắc không cần chất liệu', 'Sai']],
                'Phù điêu gắn trên nền; chất liệu là yếu tố cốt lõi của điêu khắc.');
            $this->sortQ($L, 'Kéo mỗi yêu cầu vào nhóm TƯỢNG ĐÀI hoặc TƯỢNG TRANG TRÍ.',
                [['Trang nghiêm', 'Tượng đài'], ['Bền vững lâu dài', 'Tượng đài'], ['Ý nghĩa lịch sử', 'Tượng đài'],
                 ['Đẹp mắt', 'Trang trí'], ['Hài hòa không gian', 'Trang trí'], ['Nhỏ xinh', 'Trang trí']],
                'Tượng đài nặng về ý nghĩa, tượng trang trí nặng về thẩm mỹ.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tượng có thể xem từ mọi phía gọi là điêu khắc ___.', [[0, 'tròn']],
                'Điêu khắc tròn là tượng độc lập ba chiều.');
            $this->fill($L, 'Hình nổi trên mặt phẳng gọi là phù ___.', [[0, 'điêu']],
                'Phù điêu có nổi cao và nổi thấp.');
            $this->fill($L, 'Ba chất liệu điêu khắc truyền thống là đá, gỗ và ___.', [[0, 'đồng']],
                'Đồng đúc được những chi tiết tinh xảo.');
            $this->fill($L, 'Tượng lớn nơi công cộng để tôn vinh lịch sử gọi là tượng ___.', [[0, 'đài']],
                'Tượng đài mang ý nghĩa tưởng niệm, giáo dục.');
        }
    }

    // ================= GDTC – LỚP 10 =================

    private function seedGdtc101(): void
    {
        $L = 'gdtc-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Mỗi đội bóng đá 11 người có bao nhiêu cầu thủ trên sân?',
                ['11 cầu thủ', '10 cầu thủ', '9 cầu thủ', '12 cầu thủ'], 0,
                'Mỗi đội có 11 cầu thủ gồm 1 thủ môn và 10 cầu thủ khác.');
            $this->quiz($L, 'Một trận đấu bóng đá gồm mấy hiệp, mỗi hiệp bao lâu?',
                ['2 hiệp, mỗi hiệp 45 phút', '2 hiệp, mỗi hiệp 30 phút',
                 '4 hiệp, mỗi hiệp 20 phút', '1 hiệp 90 phút'], 0,
                'Trận đấu chuẩn gồm 2 hiệp 45 phút, nghỉ giữa hiệp tối đa 15 phút.');
            $this->quiz($L, 'Trận đấu không được tiếp tục khi một đội còn bao nhiêu cầu thủ?',
                ['Dưới 7 người', 'Dưới 5 người', 'Dưới 9 người', 'Dưới 11 người'], 0,
                'Luật quy định trận đấu dừng khi một đội còn dưới 7 cầu thủ.');
            $this->quiz($L, 'Bóng đá hiện đại cho phép thay tối đa bao nhiêu cầu thủ mỗi trận?',
                ['5 cầu thủ', '3 cầu thủ', '7 cầu thủ', 'Không giới hạn'], 0,
                'Luật hiện hành cho phép thay tối đa 5 cầu thủ mỗi trận.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thông số sân với giá trị của nó.',
                [['Chiều dài sân', '90 – 120m'],
                 ['Chiều rộng sân', '45 – 90m'],
                 ['Sân tiêu chuẩn quốc tế', 'Khoảng 105 × 68m'],
                 ['Khung thành', 'Rộng 7,32m, cao 2,44m']],
                'Kích thước sân bóng đá 11 người theo luật FIFA.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Hiệp đấu', '45 phút thi đấu'],
                 ['Nghỉ giữa hiệp', 'Tối đa 15 phút'],
                 ['Bù giờ', 'Thời gian cộng thêm khi có gián đoạn'],
                 ['Hiệp phụ', '2 hiệp 15 phút khi cần phân thắng bại']],
                'Thời gian thi đấu được tính cả phút bù giờ.');
            $this->matching($L, 'Nối mỗi vị trí với số lượng trên sân.',
                [['Thủ môn', '1 người mỗi đội'],
                 ['Cầu thủ', '11 người mỗi đội'],
                 ['Tối thiểu', '7 người để trận đấu tiếp tục'],
                 ['Thay người', 'Tối đa 5 lượt mỗi trận']],
                'Con số cơ bản về nhân sự trong bóng đá.');
            $this->matching($L, 'Nối mỗi khu vực sân với đặc điểm của nó.',
                [['Vòng cấm địa', 'Khu vực 16m50, thủ môn được dùng tay'],
                 ['Chấm phạt đền', 'Cách khung thành 11m'],
                 ['Vòng tròn giữa sân', 'Nơi giao bóng bắt đầu trận đấu'],
                 ['Khu vực phạt góc', 'Góc sân để đá phạt góc']],
                'Các khu vực quan trọng trên sân bóng đá.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thông số vào nhóm ĐÚNG hoặc SAI theo luật bóng đá.',
                [['Mỗi đội 11 cầu thủ', 'Đúng'], ['Mỗi hiệp 45 phút', 'Đúng'],
                 ['Nghỉ giữa hiệp tối đa 15 phút', 'Đúng'],
                 ['Mỗi đội 12 cầu thủ', 'Sai'], ['Mỗi hiệp 30 phút', 'Sai'],
                 ['Thay người không giới hạn', 'Sai']],
                'Nắm chắc các con số cơ bản của luật bóng đá.');
            $this->sortQ($L, 'Kéo mỗi khu vực vào nhóm ĐƯỢC DÙNG TAY hoặc KHÔNG ĐƯỢC DÙNG TAY (cầu thủ thường).',
                [['Thủ môn trong vòng cấm', 'Được dùng tay'],
                 ['Cầu thủ thường trong vòng cấm', 'Không được dùng tay'],
                 ['Cầu thủ ở giữa sân', 'Không được dùng tay'],
                 ['Thủ môn ngoài vòng cấm', 'Không được dùng tay'],
                 ['Cầu thủ ném biên', 'Được dùng tay'],
                 ['Thủ môn phát bóng', 'Được dùng tay']],
                'Chỉ thủ môn trong vòng cấm và cầu thủ ném biên được dùng tay.');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm TRẬN ĐẤU TIẾP TỤC hoặc DỪNG LẠI.',
                [['Đội còn 8 cầu thủ', 'Tiếp tục'], ['Đội còn 7 cầu thủ', 'Tiếp tục'],
                 ['Bóng ra ngoài biên', 'Tiếp tục'],
                 ['Đội còn 6 cầu thủ', 'Dừng lại'], ['Trọng tài thổi còi hết giờ', 'Dừng lại'],
                 ['Cầu thủ chấn thương nặng', 'Dừng lại']],
                'Dưới 7 cầu thủ hoặc hết giờ thì trận đấu dừng.');
            $this->sortQ($L, 'Kéo mỗi khoảng thời gian vào đúng giai đoạn trận đấu.',
                [['45 phút', 'Một hiệp đấu'], ['15 phút', 'Nghỉ giữa hiệp tối đa'],
                 ['Vài phút', 'Bù giờ mỗi hiệp'],
                 ['90 phút', 'Cả trận đấu'], ['30 phút', 'Hai hiệp phụ'],
                 ['120 phút', 'Cả trận có hiệp phụ']],
                'Tổng thời gian một trận đấu chuẩn là 90 phút.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mỗi đội bóng đá có ___ cầu thủ trên sân.', [[0, '11']],
                '11 cầu thủ gồm 1 thủ môn và 10 cầu thủ khác.');
            $this->fill($L, 'Mỗi hiệp đấu bóng đá kéo dài ___ phút.', [[0, '45']],
                'Trận đấu có 2 hiệp, mỗi hiệp 45 phút.');
            $this->fill($L, 'Trận đấu dừng khi một đội còn dưới ___ cầu thủ.', [[0, '7']],
                'Quy định tối thiểu 7 người để trận đấu tiếp tục.');
            $this->fill($L, 'Mỗi trận được thay tối đa ___ cầu thủ.', [[0, '5']],
                'Luật hiện hành cho phép 5 lượt thay người.');
        }
    }

    private function seedGdtc102(): void
    {
        $L = 'gdtc-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cầu thủ bị việt vị khi nào?',
                ['Đứng gần khung thành đối phương hơn bóng và cầu thủ áp chót đối phương lúc đồng đội chuyền bóng',
                 'Đứng ở sân nhà mình', 'Đứng ngang hàng hậu vệ đối phương',
                 'Đứng trong vòng cấm đội mình'], 0,
                'Việt vị xét tại thời điểm đồng đội chuyền bóng, không phải lúc nhận bóng.', 'trung_binh');
            $this->quiz($L, 'Tình huống nào KHÔNG bị thổi việt vị?',
                ['Nhận bóng từ quả ném biên', 'Nhận bóng từ đường chuyền của đồng đội ở giữa sân',
                 'Đứng sau hàng hậu vệ đối phương nhận bóng', 'Đứng trong vòng cấm đối phương'], 0,
                'Không việt vị khi nhận bóng từ phát bóng, ném biên và phạt góc.');
            $this->quiz($L, 'Phạm lỗi trong vòng cấm địa của đội mình bị phạt gì?',
                ['Phạt đền', 'Phạt góc', 'Ném biên', 'Phát bóng'], 0,
                'Lỗi trực tiếp trong vòng cấm bị phạt đền (penalty).');
            $this->quiz($L, 'Cầu thủ nhận hai thẻ vàng trong một trận sẽ bị gì?',
                ['Truất quyền thi đấu', 'Chỉ bị nhắc nhở',
                 'Bị phạt tiền', 'Được tiếp tục thi đấu'], 0,
                'Hai thẻ vàng bằng một thẻ đỏ: cầu thủ phải rời sân.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại phạt với tình huống áp dụng.',
                [['Phạt đền', 'Phạm lỗi trong vòng cấm địa'],
                 ['Phạt trực tiếp', 'Phạm lỗi nghiêm trọng ngoài vòng cấm'],
                 ['Phạt gián tiếp', 'Lỗi nhẹ như việt vị, thủ môn giữ bóng quá lâu'],
                 ['Phạt góc', 'Bóng ra ngoài qua đường biên ngang do đội phòng ngự chạm cuối']],
                'Mỗi tình huống phạm lỗi có hình thức phạt tương ứng.');
            $this->matching($L, 'Nối mỗi loại thẻ với ý nghĩa của nó.',
                [['Thẻ vàng', 'Cảnh cáo cầu thủ'],
                 ['Thẻ đỏ', 'Truất quyền thi đấu'],
                 ['Hai thẻ vàng', 'Thành một thẻ đỏ'],
                 ['Thẻ đỏ trực tiếp', 'Lỗi đặc biệt nghiêm trọng']],
                'Hệ thống thẻ phạt kiểm soát hành vi cầu thủ.');
            $this->matching($L, 'Nối mỗi tình huống với kết luận việt vị.',
                [['Nhận bóng từ ném biên', 'Không việt vị'],
                 ['Nhận bóng từ phạt góc', 'Không việt vị'],
                 ['Nhận bóng từ phát bóng', 'Không việt vị'],
                 ['Đứng sau hậu vệ cuối nhận đường chuyền', 'Việt vị']],
                'Ba tình huống được miễn việt vị theo luật.');
            $this->matching($L, 'Nối mỗi hành vi với mức phạt tương ứng.',
                [['Chơi bóng bằng tay cố ý', 'Phạt trực tiếp'],
                 ['Ngáng chân đối phương', 'Phạt trực tiếp'],
                 ['Câu giờ', 'Thẻ vàng'],
                 ['Đánh nguội đối phương', 'Thẻ đỏ trực tiếp']],
                'Mức độ nghiêm trọng quyết định hình thức kỷ luật.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm VIỆT VỊ hoặc KHÔNG VIỆT VỊ.',
                [['Đứng sau hậu vệ cuối nhận bóng', 'Việt vị'],
                 ['Tham gia tình huống khi ở vị trí việt vị', 'Việt vị'],
                 ['Nhận bóng từ quả ném biên', 'Không việt vị'],
                 ['Nhận bóng từ quả phạt góc', 'Không việt vị'],
                 ['Đứng ngang hàng hậu vệ đối phương', 'Không việt vị'],
                 ['Đứng ở sân nhà', 'Không việt vị']],
                'Việt vị cần cả vị trí và sự tham gia tình huống.');
            $this->sortQ($L, 'Kéo mỗi lỗi vào nhóm PHẠT TRỰC TIẾP hoặc PHẠT GIÁN TIẾP.',
                [['Đá đối phương', 'Trực tiếp'], ['Ngáng chân', 'Trực tiếp'], ['Chơi bóng bằng tay', 'Trực tiếp'],
                 ['Việt vị', 'Gián tiếp'], ['Thủ môn giữ bóng quá 6 giây', 'Gián tiếp'],
                 ['Chắn đường không tranh bóng', 'Gián tiếp']],
                'Phạt trực tiếp được sút thẳng vào khung thành.');
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm THẺ VÀNG hoặc THẺ ĐỎ.',
                [['Câu giờ', 'Thẻ vàng'], ['Phản ứng trọng tài', 'Thẻ vàng'], ['Cởi áo ăn mừng', 'Thẻ vàng'],
                 ['Đánh nguội', 'Thẻ đỏ'], ['Phạm lỗi từ phía sau nguy hiểm', 'Thẻ đỏ'],
                 ['Cố tình dùng tay cản bàn thắng', 'Thẻ đỏ']],
                'Thẻ đỏ cho hành vi bạo lực hoặc ngăn cản bàn thắng rõ ràng.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Phạm lỗi trong vòng cấm bị phạt đền', 'Đúng'],
                 ['Hai thẻ vàng thành một thẻ đỏ', 'Đúng'],
                 ['Nhận bóng từ ném biên không việt vị', 'Đúng'],
                 ['Thẻ vàng nghĩa là truất quyền thi đấu', 'Sai'],
                 ['Việt vị bị phạt đền', 'Sai'],
                 ['Được dùng tay chơi bóng', 'Sai']],
                'Việt vị chỉ bị phạt gián tiếp; thẻ vàng chỉ là cảnh cáo.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phạm lỗi trong vòng cấm địa bị phạt ___ (penalty).', [[0, 'đền']],
                'Phạt đền đá từ chấm 11m.');
            $this->fill($L, 'Cầu thủ nhận hai thẻ vàng sẽ bị truất quyền thi ___.', [[0, 'đấu']],
                'Hai thẻ vàng tương đương một thẻ đỏ.');
            $this->fill($L, 'Nhận bóng trực tiếp từ quả ném biên thì không bị thổi việt ___.', [[0, 'vị']],
                'Ném biên, phạt góc, phát bóng được miễn việt vị.');
            $this->fill($L, 'Thẻ ___ dùng để cảnh cáo cầu thủ.', [[0, 'vàng']],
                'Thẻ vàng là hình thức kỷ luật nhẹ nhất.');
        }
    }

    private function seedGdtc103(): void
    {
        $L = 'gdtc-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ai là người duy nhất được dùng tay chơi bóng trong vòng cấm?',
                ['Thủ môn', 'Trung vệ', 'Tiền đạo', 'Tiền vệ'], 0,
                'Thủ môn được dùng tay trong vòng cấm địa của đội mình.');
            $this->quiz($L, 'Nhiệm vụ chính của hậu vệ là gì?',
                ['Ngăn chặn đối phương ghi bàn', 'Ghi thật nhiều bàn thắng',
                 'Chỉ đứng giữa sân', 'Phát bóng lên'], 0,
                'Hậu vệ là tuyến phòng ngự bảo vệ khung thành.');
            $this->quiz($L, 'Tiền vệ có vai trò gì trong đội bóng?',
                ['Cầu nối giữa phòng ngự và tấn công', 'Chỉ phòng ngự',
                 'Chỉ tấn công', 'Thay thủ môn'], 0,
                'Tiền vệ điều tiết lối chơi, nối hai tuyến phòng ngự – tấn công.');
            $this->quiz($L, 'Tiền đạo cắm thường đứng ở vị trí nào?',
                ['Cao nhất trên hàng công, gần khung thành đối phương', 'Trước khung thành đội nhà',
                 'Dọc đường biên', 'Trong khung thành'], 0,
                'Tiền đạo cắm là mũi nhọn ghi bàn chính của đội.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tuyến với nhiệm vụ chính của nó.',
                [['Thủ môn', 'Bảo vệ khung thành'],
                 ['Hậu vệ', 'Ngăn chặn đối phương ghi bàn'],
                 ['Tiền vệ', 'Cầu nối phòng ngự – tấn công'],
                 ['Tiền đạo', 'Ghi bàn vào lưới đối phương']],
                'Bốn tuyến với bốn nhiệm vụ rõ ràng.');
            $this->matching($L, 'Nối mỗi vị trí cụ thể với tuyến của nó.',
                [['Trung vệ', 'Hậu vệ'],
                 ['Hậu vệ biên', 'Hậu vệ'],
                 ['Tiền vệ trung tâm', 'Tiền vệ'],
                 ['Tiền đạo cánh', 'Tiền đạo']],
                'Mỗi tuyến có nhiều vị trí chuyên môn hóa.');
            $this->matching($L, 'Nối mỗi vị trí với đặc điểm của nó.',
                [['Thủ môn', 'Được dùng tay trong vòng cấm'],
                 ['Trung vệ', 'Không chiến tốt, cản phá'],
                 ['Tiền vệ phòng ngự', 'Thu hồi bóng, che chắn'],
                 ['Tiền đạo cắm', 'Dứt điểm, ghi bàn']],
                'Đặc điểm vị trí quyết định yêu cầu thể chất, kỹ thuật.');
            $this->matching($L, 'Nối mỗi kỹ năng với vị trí cần nó nhất.',
                [['Phản xạ', 'Thủ môn'],
                 ['Không chiến', 'Trung vệ'],
                 ['Chuyền bóng', 'Tiền vệ'],
                 ['Dứt điểm', 'Tiền đạo']],
                'Mỗi vị trí cần những kỹ năng nổi trội riêng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi vị trí vào đúng tuyến: PHÒNG NGỰ, TIỀN VỆ hoặc TẤN CÔNG.',
                [['Trung vệ', 'Phòng ngự'], ['Hậu vệ biên', 'Phòng ngự'],
                 ['Tiền vệ trung tâm', 'Tiền vệ'], ['Tiền vệ cánh', 'Tiền vệ'],
                 ['Tiền đạo cắm', 'Tấn công'], ['Tiền đạo cánh', 'Tấn công']],
                'Thủ môn đứng riêng, còn lại chia 3 tuyến.');
            $this->sortQ($L, 'Kéo mỗi nhiệm vụ vào đúng vị trí của nó.',
                [['Bảo vệ khung thành', 'Thủ môn'], ['Dùng tay trong vòng cấm', 'Thủ môn'],
                 ['Cản phá, không chiến', 'Hậu vệ'], ['Ngăn đối phương ghi bàn', 'Hậu vệ'],
                 ['Điều tiết lối chơi', 'Tiền vệ'], ['Ghi bàn', 'Tiền đạo']],
                'Nhiệm vụ gắn liền với vị trí trên sân.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Thủ môn được dùng tay trong vòng cấm', 'Đúng'],
                 ['Hậu vệ ngăn đối phương ghi bàn', 'Đúng'],
                 ['Tiền vệ là cầu nối hai tuyến', 'Đúng'],
                 ['Tiền đạo có nhiệm vụ ghi bàn', 'Đúng'],
                 ['Tiền đạo phải bảo vệ khung thành', 'Sai'],
                 ['Hậu vệ được dùng tay trong vòng cấm', 'Sai']],
                'Chỉ thủ môn (trong vòng cấm) được dùng tay.');
            $this->sortQ($L, 'Kéo mỗi vị trí vào nhóm GẦN KHUNG THÀNH MÌNH hoặc GẦN KHUNG THÀNH ĐỐI PHƯƠNG.',
                [['Thủ môn', 'Gần khung thành mình'], ['Trung vệ', 'Gần khung thành mình'],
                 ['Hậu vệ biên', 'Gần khung thành mình'],
                 ['Tiền đạo cắm', 'Gần khung thành đối phương'], ['Tiền đạo cánh', 'Gần khung thành đối phương'],
                 ['Tiền vệ tấn công', 'Gần khung thành đối phương']],
                'Vị trí trên sân phản ánh nhiệm vụ phòng ngự hay tấn công.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Người duy nhất được dùng tay trong vòng cấm là thủ ___.', [[0, 'môn']],
                'Thủ môn bảo vệ khung thành của đội mình.');
            $this->fill($L, 'Tuyến ngăn chặn đối phương ghi bàn là hàng hậu ___.', [[0, 'vệ']],
                'Hậu vệ gồm trung vệ và hậu vệ biên.');
            $this->fill($L, 'Tiền vệ là cầu nối giữa phòng ngự và tấn ___.', [[0, 'công']],
                'Tiền vệ điều tiết nhịp độ trận đấu.');
            $this->fill($L, 'Vị trí có nhiệm vụ chính là ghi bàn là tiền ___.', [[0, 'đạo']],
                'Tiền đạo là mũi nhọn tấn công của đội bóng.');
        }
    }

    private function seedGdtc104(): void
    {
        $L = 'gdtc-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Sơ đồ 4-4-2 có nghĩa là gì?',
                ['4 hậu vệ, 4 tiền vệ, 2 tiền đạo', '4 thủ môn, 4 hậu vệ, 2 tiền đạo',
                 '4 tiền đạo, 4 tiền vệ, 2 hậu vệ', '4 hiệp đấu'], 0,
                'Con số ghi số cầu thủ từng tuyến, không tính thủ môn.', 'trung_binh');
            $this->quiz($L, 'Sơ đồ nào thiên về tấn công biên?',
                ['4-3-3', '5-3-2', '4-4-2 phòng ngự', '1-1-8'], 0,
                '4-3-3 với 3 tiền đạo tạo sức ép lớn ở hai biên.', 'trung_binh');
            $this->quiz($L, 'Pressing trong bóng đá là gì?',
                ['Gây áp lực ngay bên phần sân đối phương để giành bóng',
                 'Lùi sâu phòng ngự', 'Giữ bóng ở sân nhà', 'Đá bóng lên cao'], 0,
                'Pressing là chiến thuật pressing tầm cao, cướp bóng từ xa.');
            $this->quiz($L, 'Phản công là chiến thuật như thế nào?',
                ['Chuyển nhanh từ phòng ngự sang tấn công khi cướp được bóng',
                 'Chậm rãi triển khai bóng', 'Chỉ phòng ngự không tấn công',
                 'Đá bóng ra ngoài biên'], 0,
                'Phản công tận dụng khoảng trống khi đối phương dâng cao.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sơ đồ với đặc điểm của nó.',
                [['4-4-2', 'Cân bằng công thủ'],
                 ['4-3-3', 'Thiên về tấn công biên'],
                 ['3-5-2', 'Chắc chắn ở giữa sân'],
                 ['5-3-2', 'Phòng ngự số đông']],
                'Sơ đồ chiến thuật thể hiện ý đồ của huấn luyện viên.');
            $this->matching($L, 'Nối mỗi thuật ngữ chiến thuật với ý nghĩa.',
                [['Pressing', 'Gây áp lực giành bóng từ xa'],
                 ['Phản công', 'Tấn công nhanh khi cướp bóng'],
                 ['Kiểm soát bóng', 'Giữ bóng, điều tiết nhịp độ'],
                 ['Phòng ngự số đông', 'Lùi sâu bảo vệ khung thành']],
                'Chiến thuật là cách tổ chức lối chơi của cả đội.');
            $this->matching($L, 'Nối mỗi con số trong sơ đồ với tuyến tương ứng (ví dụ 4-4-2).',
                [['Số đầu (4)', 'Hàng hậu vệ'],
                 ['Số giữa (4)', 'Hàng tiền vệ'],
                 ['Số cuối (2)', 'Hàng tiền đạo'],
                 ['Thủ môn', 'Không tính trong sơ đồ']],
                'Đọc sơ đồ từ dưới lên: hậu vệ – tiền vệ – tiền đạo.');
            $this->matching($L, 'Nối mỗi tình huống với chiến thuật phù hợp.',
                [['Cần gỡ hòa gấp', 'Tấn công tổng lực'],
                 ['Đang dẫn bàn', 'Phòng ngự chắc chắn'],
                 ['Đối phương dâng cao', 'Phản công nhanh'],
                 ['Muốn giữ nhịp', 'Kiểm soát bóng']],
                'Chiến thuật thay đổi theo diễn biến trận đấu.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sơ đồ vào nhóm THIÊN VỀ TẤN CÔNG hoặc THIÊN VỀ PHÒNG NGỰ.',
                [['4-3-3', 'Tấn công'], ['3-4-3', 'Tấn công'], ['4-2-4', 'Tấn công'],
                 ['5-3-2', 'Phòng ngự'], ['5-4-1', 'Phòng ngự'], ['4-5-1', 'Phòng ngự']],
                'Nhiều tiền đạo = tấn công, nhiều hậu vệ = phòng ngự.');
            $this->sortQ($L, 'Kéo mỗi chiến thuật vào nhóm CHỦ ĐỘNG GIÀNH BÓNG hoặc CHỜ CƠ HỘI.',
                [['Pressing tầm cao', 'Chủ động'], ['Tấn công tổng lực', 'Chủ động'],
                 ['Phản công', 'Chờ cơ hội'], ['Phòng ngự phản công', 'Chờ cơ hội'],
                 ['Bẫy việt vị', 'Chờ cơ hội'], ['Kiểm soát bóng', 'Chủ động']],
                'Hai triết lý: chủ động áp đặt hoặc chờ đối phương sơ hở.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['4-4-2 là sơ đồ cân bằng', 'Đúng'],
                 ['Pressing là gây áp lực giành bóng', 'Đúng'],
                 ['Phản công chuyển nhanh khi cướp bóng', 'Đúng'],
                 ['Sơ đồ không tính thủ môn', 'Đúng'],
                 ['4-3-3 là sơ đồ phòng ngự', 'Sai'],
                 ['Phản công là đá bóng ra ngoài', 'Sai']],
                '4-3-3 thiên về tấn công; phản công là tấn công nhanh.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUỘC VỀ CHIẾN THUẬT hoặc KỸ THUẬT CÁ NHÂN.',
                [['Sơ đồ đội hình', 'Chiến thuật'], ['Pressing', 'Chiến thuật'], ['Phản công', 'Chiến thuật'],
                 ['Sút bóng', 'Kỹ thuật'], ['Rê bóng qua người', 'Kỹ thuật'], ['Đánh đầu', 'Kỹ thuật']],
                'Chiến thuật là của tập thể, kỹ thuật là của cá nhân.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Sơ đồ 4-4-2 gồm 4 hậu vệ, 4 tiền vệ và 2 tiền ___.', [[0, 'đạo']],
                'Con số trong sơ đồ không tính thủ môn.');
            $this->fill($L, 'Chiến thuật gây áp lực giành bóng từ xa gọi là ___.', [[0, 'pressing']],
                'Pressing tầm cao là vũ khí của bóng đá hiện đại.');
            $this->fill($L, 'Chuyển nhanh từ phòng ngự sang tấn công gọi là phản ___.', [[0, 'công']],
                'Phản công tận dụng khoảng trống phía sau hàng thủ đối phương.');
            $this->fill($L, 'Sơ đồ 4-3-3 thiên về tấn công ___.', [[0, 'biên']],
                'Ba tiền đạo tạo sức ép ở hai cánh.');
        }
    }

    // ================= GDTC – LỚP 11 =================

    private function seedGdtc111(): void
    {
        $L = 'gdtc-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kích thước sân bóng chuyền là bao nhiêu?',
                ['Dài 18m, rộng 9m', 'Dài 20m, rộng 10m',
                 'Dài 15m, rộng 7m', 'Dài 25m, rộng 12m'], 0,
                'Sân bóng chuyền tiêu chuẩn dài 18m, rộng 9m.');
            $this->quiz($L, 'Chiều cao lưới bóng chuyền nam là bao nhiêu?',
                ['2,43m', '2,24m', '2,00m', '2,55m'], 0,
                'Lưới nam cao 2,43m, lưới nữ cao 2,24m.');
            $this->quiz($L, 'Mỗi đội bóng chuyền có bao nhiêu người trên sân?',
                ['6 người', '5 người', '7 người', '11 người'], 0,
                'Bóng chuyền thi đấu 6 đấu 6 trên sân.');
            $this->quiz($L, 'Thể thức tính điểm rally trong bóng chuyền có nghĩa là gì?',
                ['Mỗi pha bóng đều có điểm', 'Chỉ đội phát bóng mới được điểm',
                 'Mỗi set chỉ tính 10 điểm', 'Không tính điểm'], 0,
                'Rally scoring: đội thắng pha bóng nào được điểm pha đó.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thông số với giá trị của nó.',
                [['Chiều dài sân', '18m'],
                 ['Chiều rộng sân', '9m'],
                 ['Lưới nam', '2,43m'],
                 ['Lưới nữ', '2,24m']],
                'Các con số chuẩn của sân bóng chuyền.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Rally', 'Mỗi pha bóng đều tính điểm'],
                 ['Set đấu', 'Ván đấu, thắng khi đạt 25 điểm'],
                 ['Phát bóng', 'Mở đầu mỗi pha bóng'],
                 ['Điểm số', 'Đơn vị tính kết quả']],
                'Hiểu thuật ngữ giúp theo dõi trận đấu dễ dàng.');
            $this->matching($L, 'Nối mỗi mốc điểm với ý nghĩa của nó.',
                [['25 điểm', 'Thắng 1 set (hơn 2 điểm)'],
                 ['15 điểm', 'Thắng set 5 quyết định'],
                 ['Hơn 2 điểm', 'Điều kiện thắng set'],
                 ['3 set thắng', 'Thắng cả trận đấu']],
                'Trận đấu diễn ra theo thể thức 5 set thắng 3.');
            $this->matching($L, 'Nối mỗi vị trí với vai trò của nó.',
                [['Chuyền hai', 'Người kiến tạo, chuyền bóng'],
                 ['Chủ công', 'Người đập bóng ghi điểm'],
                 ['Libero', 'Chuyên phòng thủ, áo khác màu'],
                 ['Phụ công', 'Chắn bóng trên lưới']],
                'Bốn vị trí chuyên môn trong bóng chuyền.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thông số vào nhóm ĐÚNG hoặc SAI theo luật bóng chuyền.',
                [['Sân dài 18m rộng 9m', 'Đúng'], ['Lưới nam cao 2,43m', 'Đúng'],
                 ['Mỗi đội 6 người', 'Đúng'],
                 ['Sân dài 25m', 'Sai'], ['Lưới nam cao 3m', 'Sai'], ['Mỗi đội 11 người', 'Sai']],
                'Ghi nhớ các con số chuẩn của bóng chuyền.');
            $this->sortQ($L, 'Kéo mỗi vị trí vào nhóm HÀNG TRÊN LƯỚI hoặc HÀNG PHÒNG THỦ.',
                [['Chủ công', 'Trên lưới'], ['Phụ công', 'Trên lưới'], ['Chuyền hai', 'Trên lưới'],
                 ['Libero', 'Phòng thủ'], ['Chuyên đỡ bóng', 'Phòng thủ'], ['Bảo vệ sân sau', 'Phòng thủ']],
                'Ba người hàng trên, ba người hàng dưới.');
            $this->sortQ($L, 'Kéo mỗi tình huống ghi điểm vào đúng đội được điểm (thể thức rally).',
                [['Đội A thắng pha bóng', 'Đội A được điểm'],
                 ['Đội B phát bóng hỏng', 'Đội A được điểm'],
                 ['Đội B thắng pha bóng', 'Đội B được điểm'],
                 ['Đội A đập bóng ra ngoài', 'Đội B được điểm'],
                 ['Đội B chạm lưới', 'Đội A được điểm'],
                 ['Đội A chắn bóng tốt', 'Đội A được điểm']],
                'Thể thức rally: thắng pha bóng là có điểm.');
            $this->sortQ($L, 'Kéo mỗi mốc điểm vào đúng set đấu.',
                [['25 điểm', 'Set 1 – 4'], ['Hơn đối phương 2 điểm', 'Set 1 – 4'],
                 ['15 điểm', 'Set 5'], ['Hơn đối phương 2 điểm', 'Set 5'],
                 ['3 set thắng', 'Cả trận'], ['Tối đa 5 set', 'Cả trận']],
                'Set 5 quyết định chỉ đánh đến 15 điểm.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Sân bóng chuyền dài 18m và rộng ___m.', [[0, '9']],
                'Kích thước chuẩn: 18m × 9m.');
            $this->fill($L, 'Lưới bóng chuyền nam cao 2,___m.', [[0, '43']],
                'Lưới nam 2,43m, lưới nữ 2,24m.');
            $this->fill($L, 'Mỗi đội bóng chuyền có ___ người trên sân.', [[0, '6']],
                'Bóng chuyền thi đấu 6 đấu 6.');
            $this->fill($L, 'Thắng một set cần đạt 25 điểm và hơn đối phương ít nhất ___ điểm.', [[0, '2']],
                'Điều kiện thắng set: đủ điểm và hơn 2 điểm.');
        }
    }

    private function seedGdtc112(): void
    {
        $L = 'gdtc-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi giành quyền phát bóng, đội bóng chuyền phải làm gì?',
                ['Luân chuyển vị trí theo chiều kim đồng hồ', 'Giữ nguyên vị trí',
                 'Đổi toàn bộ đội hình', 'Rời sân nghỉ'], 0,
                'Luân chuyển đảm bảo mọi cầu thủ đều qua các vị trí.');
            $this->quiz($L, 'Mỗi đội được chạm bóng tối đa bao nhiêu lần trước khi đưa bóng sang sân đối phương?',
                ['3 lần', '2 lần', '4 lần', 'Không giới hạn'], 0,
                'Tối đa 3 lần chạm, riêng chắn bóng không tính là một lần chạm.');
            $this->quiz($L, 'Hành vi nào là lỗi trong bóng chuyền?',
                ['Chạm lưới khi bóng còn trong cuộc', 'Đập bóng qua lưới',
                 'Chắn bóng', 'Phát bóng qua lưới'], 0,
                'Chạm lưới khi tham gia pha bóng là lỗi kỹ thuật.');
            $this->quiz($L, 'Cầu thủ hàng sau tấn công có quy định gì?',
                ['Không được tấn công trên vạch 3m', 'Được tấn công tự do mọi nơi',
                 'Không được chạm bóng', 'Phải đứng yên'], 0,
                'Cầu thủ hàng sau chỉ được tấn công sau vạch 3m.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi quy định với nội dung của nó.',
                [['Luân chuyển', 'Đổi vị trí theo chiều kim đồng hồ'],
                 ['3 lần chạm', 'Tối đa trước khi đưa bóng sang'],
                 ['Chắn bóng', 'Không tính là một lần chạm'],
                 ['Vạch 3m', 'Giới hạn tấn công của hàng sau']],
                'Bốn quy định quan trọng của bóng chuyền.');
            $this->matching($L, 'Nối mỗi lỗi với mô tả của nó.',
                [['Chạm lưới', 'Chạm lưới khi bóng trong cuộc'],
                 ['Dẫm vạch giữa', 'Chân qua hẳn vạch giữa sân'],
                 ['Bóng chạm ăng-ten', 'Bóng chạm cột ăng-ten'],
                 ['Quá 3 lần chạm', 'Chạm bóng lần thứ 4']],
                'Các lỗi kỹ thuật thường gặp trong bóng chuyền.');
            $this->matching($L, 'Nối mỗi kỹ thuật với mục đích của nó.',
                [['Phát bóng', 'Mở đầu pha bóng, ghi điểm trực tiếp'],
                 ['Chuyền bóng', 'Kiến tạo cho đồng đội'],
                 ['Đập bóng', 'Ghi điểm bằng cú đánh mạnh'],
                 ['Chắn bóng', 'Ngăn cú đập của đối phương']],
                'Bốn kỹ thuật cơ bản của bóng chuyền.');
            $this->matching($L, 'Nối mỗi vị trí phát bóng với thứ tự.',
                [['Người phát bóng', 'Đứng sau vạch cuối sân'],
                 ['Luân chuyển', 'Người mới chuyển đến vị trí số 1 phát bóng'],
                 ['Phát bóng hỏng', 'Mất điểm, đối phương phát bóng'],
                 ['Phát bóng ăn điểm', 'Ghi điểm trực tiếp']],
                'Phát bóng mở đầu mọi pha bóng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm HỢP LỆ hoặc PHẠM LỖI.',
                [['Đập bóng qua lưới', 'Hợp lệ'], ['Chắn bóng trên lưới', 'Hợp lệ'],
                 ['Phát bóng qua lưới', 'Hợp lệ'],
                 ['Chạm lưới khi bóng trong cuộc', 'Phạm lỗi'], ['Bóng chạm ăng-ten', 'Phạm lỗi'],
                 ['Chạm bóng 4 lần', 'Phạm lỗi']],
                'Phân biệt hành vi hợp lệ và lỗi kỹ thuật.');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm ĐƯỢC TÍNH hoặc KHÔNG TÍNH là một lần chạm.',
                [['Chuyền bóng', 'Được tính'], ['Đập bóng', 'Được tính'], ['Đỡ bóng', 'Được tính'],
                 ['Chắn bóng', 'Không tính'], ['Chạm bóng khi chắn', 'Không tính'],
                 ['Bóng chạm tay rồi ra ngoài', 'Được tính']],
                'Chắn bóng là ngoại lệ không tính vào 3 lần chạm.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Luân chuyển theo chiều kim đồng hồ', 'Đúng'],
                 ['Tối đa 3 lần chạm bóng', 'Đúng'],
                 ['Chắn bóng không tính là chạm', 'Đúng'],
                 ['Hàng sau không tấn công trên vạch 3m', 'Đúng'],
                 ['Được chạm lưới thoải mái', 'Sai'],
                 ['Được chạm bóng 5 lần', 'Sai']],
                'Chạm lưới là lỗi; tối đa 3 lần chạm.');
            $this->sortQ($L, 'Kéo mỗi kỹ thuật vào nhóm TẤN CÔNG hoặc PHÒNG THỦ.',
                [['Đập bóng', 'Tấn công'], ['Phát bóng tấn công', 'Tấn công'],
                 ['Chắn bóng', 'Phòng thủ'], ['Đỡ bóng', 'Phòng thủ'],
                 ['Cứu bóng', 'Phòng thủ'], ['Chuyền bóng', 'Tấn công']],
                'Chuyền bóng kiến tạo thuộc về tấn công.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Khi giành quyền phát bóng, đội phải ___ chuyển vị trí.', [[0, 'luân']],
                'Luân chuyển theo chiều kim đồng hồ.');
            $this->fill($L, 'Mỗi đội được chạm bóng tối đa ___ lần.', [[0, '3']],
                'Quá 3 lần chạm là phạm lỗi.');
            $this->fill($L, 'Chắn bóng ___ tính là một lần chạm.', [[0, 'không']],
                'Chắn bóng là ngoại lệ của luật 3 lần chạm.');
            $this->fill($L, 'Cầu thủ hàng sau không được tấn công trên vạch ___m.', [[0, '3']],
                'Vạch 3m giới hạn khu vực tấn công của hàng sau.');
        }
    }

    private function seedGdtc113(): void
    {
        $L = 'gdtc-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kích thước sân cầu lông đánh đơn là bao nhiêu?',
                ['Dài 13,4m, rộng 5,18m', 'Dài 13,4m, rộng 6,1m',
                 'Dài 18m, rộng 9m', 'Dài 10m, rộng 5m'], 0,
                'Sân đơn hẹp hơn sân đôi: rộng 5,18m so với 6,1m.');
            $this->quiz($L, 'Mỗi set cầu lông đánh đến bao nhiêu điểm?',
                ['21 điểm', '25 điểm', '15 điểm', '11 điểm'], 0,
                'Cầu lông tính điểm rally, mỗi set đến 21 điểm.');
            $this->quiz($L, 'Quy định giao cầu đúng luật trong cầu lông là gì?',
                ['Đánh từ dưới thắt lưng, cầu bay chéo sang ô đối diện',
                 'Đánh từ trên đầu', 'Giao cầu thẳng sang ô đối diện',
                 'Giao cầu bằng chân'], 0,
                'Giao cầu phải từ dưới thắt lưng và bay chéo sân.');
            $this->quiz($L, 'Chiều cao lưới cầu lông ở giữa sân là bao nhiêu?',
                ['1,524m', '1,55m', '2,43m', '1,00m'], 0,
                'Lưới cao 1,55m ở cột và 1,524m ở giữa do võng xuống.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thông số sân với giá trị của nó.',
                [['Chiều dài sân', '13,4m'],
                 ['Rộng sân đôi', '6,1m'],
                 ['Rộng sân đơn', '5,18m'],
                 ['Cao lưới ở cột', '1,55m']],
                'Các con số chuẩn của sân cầu lông.');
            $this->matching($L, 'Nối mỗi mốc điểm với ý nghĩa của nó.',
                [['21 điểm', 'Thắng 1 set'],
                 ['Hơn 2 điểm', 'Điều kiện thắng set'],
                 ['29 đều', 'Ai được 30 trước thì thắng'],
                 ['Thắng 2 set', 'Thắng cả trận']],
                'Luật tính điểm 21 của cầu lông.');
            $this->matching($L, 'Nối mỗi quy định giao cầu với nội dung.',
                [['Dưới thắt lưng', 'Điểm tiếp xúc cầu khi giao'],
                 ['Bay chéo', 'Hướng cầu phải đi'],
                 ['Vợt hướng xuống', 'Tư thế giao cầu đúng'],
                 ['Đứng trong ô', 'Vị trí chân khi giao cầu']],
                'Bốn yêu cầu của quả giao cầu hợp lệ.');
            $this->matching($L, 'Nối mỗi dụng cụ với đặc điểm của nó.',
                [['Vợt cầu lông', 'Nhẹ, cán dài, mặt lưới'],
                 ['Quả cầu', 'Có lông vũ hoặc nhựa'],
                 ['Lưới', 'Cao 1,55m ở cột'],
                 ['Giày', 'Đế mềm, chống trượt']],
                'Dụng cụ thi đấu cầu lông.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thông số vào nhóm SÂN ĐƠN hoặc SÂN ĐÔI.',
                [['Rộng 5,18m', 'Sân đơn'], ['Hẹp hơn', 'Sân đơn'],
                 ['Rộng 6,1m', 'Sân đôi'], ['Rộng hơn', 'Sân đôi'],
                 ['Dài 13,4m', 'Cả hai'], ['Lưới 1,55m', 'Cả hai']],
                'Sân đơn và đôi chỉ khác chiều rộng.');
            $this->sortQ($L, 'Kéo mỗi quả giao cầu vào nhóm HỢP LỆ hoặc PHẠM LUẬT.',
                [['Đánh từ dưới thắt lưng', 'Hợp lệ'], ['Cầu bay chéo sân', 'Hợp lệ'],
                 ['Chân đứng trong ô giao cầu', 'Hợp lệ'],
                 ['Đánh từ trên đầu', 'Phạm luật'], ['Cầu bay thẳng', 'Phạm luật'],
                 ['Chân dẫm vạch khi giao', 'Phạm luật']],
                'Giao cầu sai bị mất điểm ngay.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Mỗi set đánh đến 21 điểm', 'Đúng'],
                 ['Giao cầu từ dưới thắt lưng', 'Đúng'],
                 ['Sân đơn rộng 5,18m', 'Đúng'],
                 ['Thắng 2 set là thắng trận', 'Đúng'],
                 ['Giao cầu được đánh từ trên đầu', 'Sai'],
                 ['29 đều thì đánh tiếp không giới hạn', 'Sai']],
                '29 đều: ai lên 30 trước thắng; giao cầu phải từ dưới thắt lưng.');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm ĐƯỢC ĐIỂM hoặc MẤT ĐIỂM.',
                [['Cầu rơi trong sân đối phương', 'Được điểm'],
                 ['Đối phương đánh cầu ra ngoài', 'Được điểm'],
                 ['Đối phương giao cầu lỗi', 'Được điểm'],
                 ['Mình đánh cầu ra ngoài', 'Mất điểm'],
                 ['Mình đánh cầu không qua lưới', 'Mất điểm'],
                 ['Mình chạm lưới', 'Mất điểm']],
                'Thể thức rally: mọi pha bóng đều có điểm.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Sân cầu lông dài 13,___m.', [[0, '4']],
                'Chiều dài chuẩn của sân cầu lông là 13,4m.');
            $this->fill($L, 'Mỗi set cầu lông đánh đến ___ điểm.', [[0, '21']],
                'Thể thức rally, thắng set khi đạt 21 và hơn 2 điểm.');
            $this->fill($L, 'Giao cầu phải đánh từ dưới thắt ___.', [[0, 'lưng']],
                'Điểm tiếp xúc cầu không được cao hơn thắt lưng.');
            $this->fill($L, 'Quả giao cầu phải bay ___ sang ô đối diện.', [[0, 'chéo']],
                'Giao cầu chéo sân là quy định bắt buộc.');
        }
    }

    private function seedGdtc114(): void
    {
        $L = 'gdtc-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hành vi nào là lỗi trong cầu lông?',
                ['Chạm lưới khi cầu còn trong cuộc', 'Đánh cầu qua lưới',
                 'Giao cầu đúng luật', 'Đỡ cầu trong sân'], 0,
                'Chạm lưới khi cầu còn trong cuộc là lỗi, mất điểm.');
            $this->quiz($L, 'Khi nào trọng tài cho đánh lại pha bóng trong cầu lông?',
                ['Có sự cố ngoài ý muốn như cầu mắc trên lưới', 'Khi cầu thủ yêu cầu',
                 'Khi khán giả la ó', 'Khi trời mưa nhẹ'], 0,
                'Sự cố khách quan không xác định được thì đánh lại.');
            $this->quiz($L, 'Trong trận 3 set, khi nào hai bên đổi sân?',
                ['Giữa set 3 khi một bên đạt 11 điểm', 'Không bao giờ đổi sân',
                 'Chỉ đổi khi hết trận', 'Đổi mỗi khi ghi điểm'], 0,
                'Đổi sân giữa set 3 ở điểm 11 để đảm bảo công bằng.');
            $this->quiz($L, 'Cầu rơi như thế nào thì tính là ngoài sân?',
                ['Chạm đất ngoài vạch giới hạn', 'Chạm đúng vạch',
                 'Chạm lưới rồi qua', 'Bay cao qua đầu'], 0,
                'Cầu chạm vạch vẫn tính trong sân; ngoài vạch mới là ngoài.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lỗi với mô tả của nó.',
                [['Cầu ngoài sân', 'Rơi ngoài vạch giới hạn'],
                 ['Cầu không qua lưới', 'Rơi lưới bên mình'],
                 ['Chạm lưới', 'Chạm lưới khi cầu trong cuộc'],
                 ['Giao cầu sai', 'Vi phạm quy định giao cầu']],
                'Bốn lỗi phổ biến trong cầu lông.');
            $this->matching($L, 'Nối mỗi tình huống với cách xử lý.',
                [['Cầu mắc trên lưới', 'Đánh lại pha bóng'],
                 ['Không xác định được', 'Đánh lại pha bóng'],
                 ['Cầu thủ chấn thương', 'Tạm dừng, xử lý y tế'],
                 ['Tranh cãi điểm số', 'Trọng tài quyết định']],
                'Trọng tài là người quyết định cuối cùng.');
            $this->matching($L, 'Nối mỗi thời điểm với quy định đổi sân.',
                [['Hết set 1', 'Đổi sân'],
                 ['Hết set 2', 'Đổi sân'],
                 ['Giữa set 3 (11 điểm)', 'Đổi sân'],
                 ['Trong set', 'Không đổi sân']],
                'Đổi sân đảm bảo công bằng về điều kiện thi đấu.');
            $this->matching($L, 'Nối mỗi vị trí cầu với kết luận.',
                [['Chạm vạch', 'Trong sân'],
                 ['Ngoài vạch', 'Ngoài sân'],
                 ['Trên lưới', 'Chưa kết thúc pha bóng'],
                 ['Dưới lưới', 'Pha bóng kết thúc']],
                'Vạch giới hạn là căn cứ xác định trong – ngoài.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm LỖI hoặc KHÔNG LỖI.',
                [['Cầu rơi ngoài vạch', 'Lỗi'], ['Chạm lưới khi cầu trong cuộc', 'Lỗi'],
                 ['Giao cầu từ trên đầu', 'Lỗi'],
                 ['Cầu chạm vạch', 'Không lỗi'], ['Đánh cầu qua lưới', 'Không lỗi'],
                 ['Giao cầu đúng luật', 'Không lỗi']],
                'Cầu chạm vạch vẫn tính là trong sân.');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm ĐÁNH LẠI hoặc TÍNH ĐIỂM.',
                [['Cầu mắc trên lưới', 'Đánh lại'], ['Sự cố ngoài ý muốn', 'Đánh lại'],
                 ['Cầu rơi trong sân đối phương', 'Tính điểm'], ['Đối phương đánh ra ngoài', 'Tính điểm'],
                 ['Đối phương chạm lưới', 'Tính điểm'], ['Mình giao cầu lỗi', 'Tính điểm']],
                'Chỉ sự cố khách quan mới được đánh lại.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Chạm lưới khi cầu trong cuộc là lỗi', 'Đúng'],
                 ['Cầu chạm vạch tính trong sân', 'Đúng'],
                 ['Giữa set 3 đổi sân ở điểm 11', 'Đúng'],
                 ['Sự cố ngoài ý muốn được đánh lại', 'Đúng'],
                 ['Cầu ngoài vạch vẫn tính điểm', 'Sai'],
                 ['Không bao giờ được đổi sân', 'Sai']],
                'Cầu ngoài vạch là mất điểm; đổi sân là quy định bắt buộc.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm DO TRỌNG TÀI QUYẾT ĐỊNH hoặc TỰ ĐỘNG.',
                [['Tranh cãi điểm số', 'Trọng tài'], ['Cho đánh lại', 'Trọng tài'],
                 ['Xử lý chấn thương', 'Trọng tài'],
                 ['Cầu ra ngoài vạch', 'Tự động'], ['Hết set', 'Tự động'], ['Đủ 21 điểm', 'Tự động']],
                'Trọng tài quyết định các tình huống tranh cãi.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Chạm lưới khi cầu còn trong cuộc là ___ lỗi.', [[0, 'phạm']],
                'Phạm lỗi chạm lưới bị mất điểm ngay.');
            $this->fill($L, 'Cầu chạm vạch giới hạn vẫn tính là ___ sân.', [[0, 'trong']],
                'Chỉ cầu hoàn toàn ngoài vạch mới tính ngoài.');
            $this->fill($L, 'Giữa set 3, khi một bên đạt 11 điểm thì hai bên đổi ___.', [[0, 'sân']],
                'Đổi sân đảm bảo công bằng điều kiện thi đấu.');
            $this->fill($L, 'Khi có sự cố ngoài ý muốn, trọng tài cho ___ lại pha bóng.', [[0, 'đánh']],
                'Đánh lại khi không thể xác định đúng sai.');
        }
    }

    // ================= GDTC – LỚP 12 =================

    private function seedGdtc121(): void
    {
        $L = 'gdtc-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nhóm chất nào là nhiên liệu chính cho hoạt động vận động?',
                ['Bột đường (carbohydrate)', 'Chất béo', 'Vitamin', 'Nước'], 0,
                'Bột đường cung cấp năng lượng nhanh nhất cho cơ bắp hoạt động.');
            $this->quiz($L, 'Đạm (protein) có vai trò gì với người tập thể thao?',
                ['Xây dựng và phục hồi cơ bắp', 'Chỉ tạo cảm giác no',
                 'Làm tăng mỡ cơ thể', 'Không có vai trò gì'], 0,
                'Đạm là vật liệu xây dựng và sửa chữa các sợi cơ sau tập luyện.');
            $this->quiz($L, 'Nước chiếm khoảng bao nhiêu phần trăm cơ thể người?',
                ['60 – 70%', '10 – 20%', '90 – 95%', '30 – 40%'], 0,
                'Nước chiếm 60–70% cơ thể; mất nước làm giảm hiệu suất vận động.');
            $this->quiz($L, 'Người tập luyện thể thao nên ăn uống như thế nào?',
                ['Đa dạng, đủ chất, không bỏ bữa', 'Chỉ ăn đạm, bỏ tinh bột',
                 'Nhịn ăn để giảm cân nhanh', 'Chỉ uống nước thay cơm'], 0,
                'Chế độ ăn cân bằng, đủ 6 nhóm chất là nền tảng của tập luyện.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nhóm chất với vai trò của nó.',
                [['Bột đường', 'Nhiên liệu chính cho vận động'],
                 ['Đạm', 'Xây dựng và phục hồi cơ bắp'],
                 ['Chất béo', 'Năng lượng dự trữ'],
                 ['Nước', 'Chiếm 60–70% cơ thể']],
                'Bốn nhóm chất quan trọng nhất với người tập luyện.');
            $this->matching($L, 'Nối tiếp mỗi nhóm chất với vai trò của nó.',
                [['Vitamin', 'Điều hòa hoạt động cơ thể'],
                 ['Khoáng chất', 'Cần cho xương, máu, cơ'],
                 ['Chất xơ', 'Tốt cho tiêu hóa'],
                 ['Điện giải', 'Bù khi ra mồ hôi nhiều']],
                'Các vi chất tuy cần ít nhưng không thể thiếu.');
            $this->matching($L, 'Nối mỗi thực phẩm với nhóm chất chính của nó.',
                [['Cơm, bánh mì', 'Bột đường'],
                 ['Thịt, trứng, sữa', 'Đạm'],
                 ['Dầu ăn, mỡ', 'Chất béo'],
                 ['Rau, trái cây', 'Vitamin, khoáng chất']],
                'Ăn đa dạng thực phẩm để đủ các nhóm chất.');
            $this->matching($L, 'Nối mỗi dấu hiệu với nguyên nhân dinh dưỡng.',
                [['Mệt nhanh khi tập', 'Thiếu năng lượng, thiếu nước'],
                 ['Chuột rút', 'Thiếu điện giải, khoáng chất'],
                 ['Lâu phục hồi', 'Thiếu đạm sau tập'],
                 ['Chóng mặt', 'Hạ đường huyết, thiếu ăn']],
                'Dấu hiệu cơ thể phản ánh chế độ dinh dưỡng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thực phẩm vào nhóm GIÀU ĐẠM hoặc GIÀU BỘT ĐƯỜNG.',
                [['Thịt gà', 'Giàu đạm'], ['Trứng', 'Giàu đạm'], ['Sữa', 'Giàu đạm'],
                 ['Cơm', 'Giàu bột đường'], ['Bánh mì', 'Giàu bột đường'], ['Khoai lang', 'Giàu bột đường']],
                'Đạm từ động vật, bột đường từ ngũ cốc, củ.');
            $this->sortQ($L, 'Kéo mỗi nhóm chất vào nhóm CUNG CẤP NĂNG LƯỢNG hoặc ĐIỀU HÒA CƠ THỂ.',
                [['Bột đường', 'Năng lượng'], ['Chất béo', 'Năng lượng'], ['Đạm', 'Năng lượng'],
                 ['Vitamin', 'Điều hòa'], ['Khoáng chất', 'Điều hòa'], ['Nước', 'Điều hòa']],
                'Ba nhóm sinh năng lượng, các vi chất điều hòa.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Bột đường là nhiên liệu chính khi vận động', 'Đúng'],
                 ['Đạm giúp phục hồi cơ bắp', 'Đúng'],
                 ['Nước chiếm 60–70% cơ thể', 'Đúng'],
                 ['Nên ăn đa dạng, không bỏ bữa', 'Đúng'],
                 ['Nhịn ăn giúp tập luyện tốt hơn', 'Sai'],
                 ['Vitamin không cần thiết', 'Sai']],
                'Nhịn ăn gây thiếu năng lượng; vitamin rất cần thiết.');
            $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm TỐT hoặc XẤU cho người tập luyện.',
                [['Ăn đủ bữa', 'Tốt'], ['Uống đủ nước', 'Tốt'], ['Ăn nhiều rau quả', 'Tốt'],
                 ['Bỏ bữa sáng', 'Xấu'], ['Uống nước ngọt có ga', 'Xấu'], ['Ăn đồ chiên rán nhiều', 'Xấu']],
                'Thói quen ăn uống quyết định hiệu quả tập luyện.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nhiên liệu chính cho vận động là bột ___ (carbohydrate).', [[0, 'đường']],
                'Bột đường cung cấp năng lượng nhanh cho cơ bắp.');
            $this->fill($L, 'Chất xây dựng và phục hồi cơ bắp là ___ (protein).', [[0, 'đạm']],
                'Sau tập luyện cần bổ sung đạm để phục hồi cơ.');
            $this->fill($L, 'Nước chiếm khoảng 60 – 70% cơ ___.', [[0, 'thể']],
                'Mất nước làm giảm rõ rệt hiệu suất vận động.');
            $this->fill($L, 'Người tập luyện cần ăn đa dạng và không bỏ ___.', [[0, 'bữa']],
                'Bỏ bữa gây thiếu năng lượng khi tập.');
        }
    }

    private function seedGdtc122(): void
    {
        $L = 'gdtc-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trước buổi tập 2–3 giờ nên ăn như thế nào?',
                ['Bữa giàu bột đường, ít béo, dễ tiêu', 'Ăn thật no với nhiều mỡ',
                 'Nhịn ăn hoàn toàn', 'Chỉ uống cà phê'], 0,
                'Bữa trước tập cần năng lượng dễ tiêu, tránh đầy bụng.');
            $this->quiz($L, 'Trong buổi tập kéo dài cần chú ý điều gì?',
                ['Bù nước thường xuyên', 'Không uống gì cả',
                 'Ăn một bữa thịnh soạn', 'Uống nước ngọt có ga'], 0,
                'Mất nước làm giảm hiệu suất; nên bù nước đều đặn.');
            $this->quiz($L, 'Sau buổi tập 30–60 phút nên bổ sung gì?',
                ['Đạm và bột đường để phục hồi cơ', 'Chỉ uống nước lọc',
                 'Ăn thật nhiều đồ ngọt', 'Không ăn gì'], 0,
                'Cửa sổ vàng sau tập: đạm sửa chữa cơ, bột đường nạp năng lượng.');
            $this->quiz($L, 'Điều nào nên tránh quanh buổi tập?',
                ['Ăn quá no ngay trước tập và đồ uống có cồn', 'Uống nước lọc',
                 'Ăn nhẹ dễ tiêu', 'Khởi động kỹ'], 0,
                'Ăn no gây khó tiêu; cồn làm mất nước và giảm hiệu suất.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thời điểm với chế độ ăn phù hợp.',
                [['Trước tập 2–3 giờ', 'Bữa giàu bột đường, ít béo, dễ tiêu'],
                 ['Trong buổi tập', 'Bù nước thường xuyên'],
                 ['Sau tập 30–60 phút', 'Bổ sung đạm và bột đường'],
                 ['Ngày nghỉ', 'Ăn cân bằng, đủ chất']],
                'Dinh dưỡng quanh buổi tập quyết định hiệu quả phục hồi.');
            $this->matching($L, 'Nối mỗi loại đồ uống với đánh giá của nó.',
                [['Nước lọc', 'Tốt nhất cho bù nước'],
                 ['Nước điện giải', 'Tốt khi tập kéo dài, ra nhiều mồ hôi'],
                 ['Nước ngọt có ga', 'Nhiều đường, không tốt'],
                 ['Đồ uống có cồn', 'Gây mất nước, cần tránh']],
                'Chọn đồ uống đúng giúp duy trì hiệu suất.');
            $this->matching($L, 'Nối mỗi sai lầm với hậu quả của nó.',
                [['Ăn quá no trước tập', 'Đầy bụng, khó vận động'],
                 ['Nhịn ăn khi tập', 'Thiếu năng lượng, chóng mặt'],
                 ['Không bù nước', 'Mệt nhanh, giảm hiệu suất'],
                 ['Uống rượu bia', 'Mất nước, lâu phục hồi']],
                'Sai lầm dinh dưỡng phá hỏng buổi tập.');
            $this->matching($L, 'Nối mỗi mục tiêu với cách ăn phù hợp.',
                [['Tăng cơ', 'Đủ đạm, đủ năng lượng'],
                 ['Giảm mỡ', 'Thâm hụt năng lượng hợp lý'],
                 ['Tăng sức bền', 'Đủ bột đường'],
                 ['Phục hồi nhanh', 'Ăn sớm sau tập']],
                'Dinh dưỡng phục vụ mục tiêu tập luyện.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi món ăn/thức uống vào nhóm NÊN DÙNG hoặc NÊN TRÁNH quanh buổi tập.',
                [['Chuối', 'Nên dùng'], ['Nước lọc', 'Nên dùng'], ['Cơm, bánh mì', 'Nên dùng'],
                 ['Đồ chiên rán nhiều mỡ', 'Nên tránh'], ['Nước ngọt có ga', 'Nên tránh'],
                 ['Rượu bia', 'Nên tránh']],
                'Ưu tiên thực phẩm dễ tiêu, đủ năng lượng.');
            $this->sortQ($L, 'Kéo mỗi hành động vào đúng thời điểm: TRƯỚC, TRONG hoặc SAU buổi tập.',
                [['Ăn bữa giàu bột đường', 'Trước tập'], ['Khởi động kỹ', 'Trước tập'],
                 ['Bù nước thường xuyên', 'Trong tập'], ['Lau mồ hôi, nghỉ ngắn', 'Trong tập'],
                 ['Bổ sung đạm', 'Sau tập'], ['Thả lỏng, giãn cơ', 'Sau tập']],
                'Mỗi giai đoạn có nhiệm vụ dinh dưỡng riêng.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Trước tập nên ăn dễ tiêu, giàu bột đường', 'Đúng'],
                 ['Trong tập cần bù nước thường xuyên', 'Đúng'],
                 ['Sau tập 30–60 phút nên bổ sung đạm', 'Đúng'],
                 ['Nên tránh đồ uống có cồn', 'Đúng'],
                 ['Nên ăn thật no ngay trước khi tập', 'Sai'],
                 ['Nhịn ăn giúp tập khỏe hơn', 'Sai']],
                'Ăn no gây ì ạch; nhịn ăn gây thiếu năng lượng.');
            $this->sortQ($L, 'Kéo mỗi loại nước vào nhóm TỐT hoặc KHÔNG TỐT khi tập luyện.',
                [['Nước lọc', 'Tốt'], ['Nước điện giải', 'Tốt'],
                 ['Nước ngọt có ga', 'Không tốt'], ['Rượu bia', 'Không tốt'],
                 ['Cà phê đặc', 'Không tốt'], ['Nước tăng lực nhiều đường', 'Không tốt']],
                'Nước lọc là lựa chọn tốt nhất cho hầu hết buổi tập.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trước tập 2–3 giờ nên ăn bữa giàu bột đường, ít béo, dễ ___.', [[0, 'tiêu']],
                'Thức ăn dễ tiêu tránh đầy bụng khi vận động.');
            $this->fill($L, 'Trong buổi tập kéo dài cần bù ___ thường xuyên.', [[0, 'nước']],
                'Mất nước làm giảm hiệu suất và gây mệt mỏi.');
            $this->fill($L, 'Sau tập 30–60 phút nên bổ sung đạm và bột ___.', [[0, 'đường']],
                'Đây là thời điểm vàng để phục hồi cơ bắp.');
            $this->fill($L, 'Quanh buổi tập nên tránh đồ uống có ___.', [[0, 'cồn']],
                'Cồn gây mất nước và làm chậm phục hồi.');
        }
    }

    private function seedGdtc123(): void
    {
        $L = 'gdtc-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nguyên tắc tăng tiến dần trong tập luyện có nghĩa là gì?',
                ['Tăng khối lượng, cường độ từ từ để cơ thể thích nghi',
                 'Tập thật nặng ngay từ đầu', 'Tập cùng một mức mãi mãi',
                 'Nghỉ tập nhiều tuần'], 0,
                'Tăng dần giúp cơ thể thích nghi an toàn, tránh quá tải.');
            $this->quiz($L, 'Nguyên tắc đặc thù trong tập luyện là gì?',
                ['Tập đúng nhóm cơ và kỹ năng của môn mình chơi',
                 'Tập mọi thứ giống nhau', 'Chỉ tập một bài duy nhất',
                 'Tập theo cảm hứng'], 0,
                'Muốn giỏi môn nào phải tập đặc thù cho môn đó.');
            $this->quiz($L, 'Vì sao nghỉ ngơi là một phần của kế hoạch tập luyện?',
                ['Cơ bắp phát triển trong lúc nghỉ', 'Nghỉ ngơi làm yếu cơ',
                 'Nghỉ để tránh phải tập', 'Nghỉ không có tác dụng gì'], 0,
                'Phục hồi giúp cơ bắp lớn hơn, khỏe hơn sau mỗi buổi tập.');
            $this->quiz($L, 'Tập quá sức có thể gây hậu quả gì?',
                ['Quá tải, chấn thương và sa sút phong độ', 'Khỏe hơn ngay lập tức',
                 'Không có hậu quả gì', 'Chỉ hơi mệt một chút'], 0,
                'Quá tải là nguyên nhân hàng đầu gây chấn thương.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nguyên tắc với nội dung của nó.',
                [['Tăng tiến dần', 'Tăng khối lượng từ từ'],
                 ['Đặc thù', 'Tập đúng môn, đúng nhóm cơ'],
                 ['Đa dạng', 'Kết hợp sức bền, sức mạnh, linh hoạt'],
                 ['Nghỉ ngơi', 'Phục hồi để phát triển']],
                'Bốn nguyên tắc vàng của tập luyện khoa học.');
            $this->matching($L, 'Nối mỗi yếu tố thể lực với cách rèn luyện.',
                [['Sức bền', 'Chạy bộ, bơi lội đều đặn'],
                 ['Sức mạnh', 'Tập tạ, chống đẩy'],
                 ['Linh hoạt', 'Giãn cơ, yoga'],
                 ['Tốc độ', 'Chạy nước rút, bài tập bùng nổ']],
                'Phát triển toàn diện các tố chất thể lực.');
            $this->matching($L, 'Nối mỗi dấu hiệu với ý nghĩa của nó.',
                [['Tiến bộ đều', 'Kế hoạch phù hợp'],
                 ['Mệt mỏi kéo dài', 'Dấu hiệu quá tải'],
                 ['Đau nhức bất thường', 'Nguy cơ chấn thương'],
                 ['Chán tập', 'Cần đổi mới, nghỉ ngơi']],
                'Lắng nghe cơ thể để điều chỉnh kế hoạch.');
            $this->matching($L, 'Nối mỗi giai đoạn với nhiệm vụ của nó.',
                [['Khởi động', 'Làm nóng cơ thể'],
                 ['Phần chính', 'Tập theo kế hoạch'],
                 ['Thả lỏng', 'Đưa cơ thể về bình thường'],
                 ['Nghỉ giữa buổi', 'Bù nước, hồi sức ngắn']],
                'Cấu trúc chuẩn của một buổi tập.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cách tập vào nhóm KHOA HỌC hoặc KHÔNG KHOA HỌC.',
                [['Tăng dần cường độ', 'Khoa học'], ['Tập đúng môn mình chơi', 'Khoa học'],
                 ['Nghỉ ngơi hợp lý', 'Khoa học'],
                 ['Tập nặng ngay từ đầu', 'Không khoa học'], ['Tập quá sức liên tục', 'Không khoa học'],
                 ['Không bao giờ nghỉ', 'Không khoa học']],
                'Khoa học = từ từ, đặc thù, có nghỉ ngơi.');
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm RÈN SỨC BỀN, SỨC MẠNH hoặc LINH HOẠT.',
                [['Chạy bộ đều', 'Sức bền'], ['Bơi lội', 'Sức bền'],
                 ['Chống đẩy', 'Sức mạnh'], ['Tập tạ', 'Sức mạnh'],
                 ['Giãn cơ', 'Linh hoạt'], ['Yoga', 'Linh hoạt']],
                'Kết hợp đa dạng để phát triển toàn diện.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nên tăng cường độ từ từ', 'Đúng'],
                 ['Cơ bắp phát triển trong lúc nghỉ', 'Đúng'],
                 ['Nên tập đặc thù theo môn', 'Đúng'],
                 ['Tập quá sức gây chấn thương', 'Đúng'],
                 ['Càng tập nặng càng tốt', 'Sai'],
                 ['Nghỉ ngơi là lười biếng', 'Sai']],
                'Nghỉ ngơi là một phần của tập luyện, không phải lười biếng.');
            $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm BÌNH THƯỜNG hoặc CẢNH BÁO QUÁ TẢI.',
                [['Hơi mệt sau buổi tập', 'Bình thường'], ['Đau cơ nhẹ 1-2 ngày', 'Bình thường'],
                 ['Ngủ ngon sau tập', 'Bình thường'],
                 ['Mệt mỏi kéo dài nhiều ngày', 'Cảnh báo'], ['Đau khớp dữ dội', 'Cảnh báo'],
                 ['Mất ngủ, chán ăn', 'Cảnh báo']],
                'Quá tải cần giảm cường độ và nghỉ ngơi ngay.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nguyên tắc tăng ___ dần giúp cơ thể thích nghi an toàn.', [[0, 'tiến']],
                'Tăng khối lượng, cường độ từ từ, không vội vàng.');
            $this->fill($L, 'Muốn giỏi môn nào phải tập ___ thù cho môn đó.', [[0, 'đặc']],
                'Nguyên tắc đặc thù: tập đúng nhóm cơ, đúng kỹ năng.');
            $this->fill($L, 'Cơ bắp phát triển trong lúc nghỉ ___.', [[0, 'ngơi']],
                'Nghỉ ngơi là một phần không thể thiếu của kế hoạch.');
            $this->fill($L, 'Tập quá sức gây quá tải, chấn thương và sa sút phong ___.', [[0, 'độ']],
                'Lắng nghe cơ thể để tránh tập quá sức.');
        }
    }

    private function seedGdtc124(): void
    {
        $L = 'gdtc-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khởi động trước khi tập có tác dụng gì?',
                ['Làm nóng cơ, tăng nhịp tim, bôi trơn khớp', 'Làm cơ thể lạnh đi',
                 'Không có tác dụng gì', 'Chỉ để giết thời gian'], 0,
                'Khởi động 10–15 phút giúp giảm nguy cơ chấn thương.');
            $this->quiz($L, 'Thả lỏng sau buổi tập giúp gì?',
                ['Cơ thể trở lại bình thường, giảm đau cơ', 'Tăng đau nhức',
                 'Không cần thiết', 'Làm cơ teo đi'], 0,
                'Thả lỏng giúp đào thải chất cặn, giảm đau cơ hôm sau.');
            $this->quiz($L, 'Nguyên tắc RICE khi xử trí chấn thương nhẹ gồm những gì?',
                ['Nghỉ ngơi, chườm lạnh, băng ép, kê cao', 'Chạy tiếp ngay',
                 'Xoa dầu nóng mạnh', 'Bỏ mặc không xử lý'], 0,
                'RICE: Rest – Ice – Compression – Elevation.', 'trung_binh');
            $this->quiz($L, 'Chấn thương nào sau đây thường gặp khi chơi thể thao?',
                ['Bong gân, căng cơ, trật khớp', 'Gãy xương sườn do ho',
                 'Cảm cúm', 'Đau răng'], 0,
                'Bong gân, căng cơ, trật khớp là ba chấn thương phổ biến.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi giai đoạn với tác dụng của nó.',
                [['Khởi động', 'Làm nóng cơ, tăng nhịp tim'],
                 ['Phần chính', 'Rèn luyện theo kế hoạch'],
                 ['Thả lỏng', 'Hồi phục, giảm đau cơ'],
                 ['Nghỉ ngơi', 'Cơ bắp phát triển']],
                'Bốn giai đoạn của một buổi tập hoàn chỉnh.');
            $this->matching($L, 'Nối mỗi chữ trong RICE với ý nghĩa.',
                [['R – Rest', 'Nghỉ ngơi, dừng vận động'],
                 ['I – Ice', 'Chườm lạnh giảm sưng'],
                 ['C – Compression', 'Băng ép hạn chế sưng'],
                 ['E – Elevation', 'Kê cao giảm phù nề']],
                'RICE là nguyên tắc sơ cứu chấn thương nhẹ.');
            $this->matching($L, 'Nối mỗi chấn thương với dấu hiệu của nó.',
                [['Bong gân', 'Sưng, đau ở khớp'],
                 ['Căng cơ', 'Đau nhói ở bắp cơ'],
                 ['Trật khớp', 'Khớp biến dạng, đau dữ dội'],
                 ['Chuột rút', 'Cơ co cứng đột ngột']],
                'Nhận biết chấn thương để xử trí đúng.');
            $this->matching($L, 'Nối mỗi biện pháp với mục đích phòng tránh.',
                [['Khởi động kỹ', 'Giảm nguy cơ chấn thương'],
                 ['Dụng cụ phù hợp', 'Bảo vệ cơ thể'],
                 ['Tập đúng kỹ thuật', 'Tránh sai tư thế'],
                 ['Không quá sức', 'Tránh quá tải']],
                'Phòng tránh tốt hơn chữa trị.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hoạt động vào đúng giai đoạn: KHỞI ĐỘNG, PHẦN CHÍNH hoặc THẢ LỎNG.',
                [['Chạy nhẹ, xoay khớp', 'Khởi động'], ['Giãn cơ động', 'Khởi động'],
                 ['Tập theo kế hoạch', 'Phần chính'], ['Thi đấu, bài tập nặng', 'Phần chính'],
                 ['Đi bộ nhẹ', 'Thả lỏng'], ['Giãn cơ tĩnh', 'Thả lỏng']],
                'Trình tự chuẩn: khởi động – chính – thả lỏng.');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm NÊN LÀM hoặc KHÔNG NÊN khi bị bong gân nhẹ.',
                [['Nghỉ ngơi', 'Nên làm'], ['Chườm lạnh', 'Nên làm'], ['Kê cao', 'Nên làm'],
                 ['Cố chạy tiếp', 'Không nên'], ['Xoa bóp mạnh', 'Không nên'], ['Chườm nóng ngay', 'Không nên']],
                'Bong gân nhẹ xử trí theo RICE, không chườm nóng sớm.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Khởi động giúp giảm chấn thương', 'Đúng'],
                 ['Thả lỏng giúp giảm đau cơ', 'Đúng'],
                 ['RICE gồm nghỉ, chườm lạnh, băng ép, kê cao', 'Đúng'],
                 ['Chấn thương nặng cần đến cơ sở y tế', 'Đúng'],
                 ['Không cần khởi động khi tập nhẹ', 'Sai'],
                 ['Bong gân nên chườm nóng ngay', 'Sai']],
                'Luôn khởi động; bong gân chườm lạnh trước, không chườm nóng sớm.');
            $this->sortQ($L, 'Kéo mỗi chấn thương vào nhóm NHẸ (tự xử trí) hoặc NẶNG (đi bệnh viện).',
                [['Chuột rút', 'Nhẹ'], ['Căng cơ nhẹ', 'Nhẹ'], ['Bong gân nhẹ', 'Nhẹ'],
                 ['Trật khớp', 'Nặng'], ['Nghi gãy xương', 'Nặng'], ['Chấn thương đầu', 'Nặng']],
                'Chấn thương nặng phải đến cơ sở y tế ngay.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trước khi tập cần ___ động 10–15 phút.', [[0, 'khởi']],
                'Khởi động làm nóng cơ, bôi trơn khớp.');
            $this->fill($L, 'Sau buổi tập cần thả ___ để cơ thể hồi phục.', [[0, 'lỏng']],
                'Thả lỏng giúp giảm đau cơ hôm sau.');
            $this->fill($L, 'Nguyên tắc xử trí chấn thương nhẹ là ___.', [[0, 'RICE']],
                'RICE: nghỉ ngơi, chườm lạnh, băng ép, kê cao.');
            $this->fill($L, 'Chấn thương nặng phải đến cơ sở y ___.', [[0, 'tế']],
                'Không tự xử trí chấn thương nghiêm trọng.');
        }
    }

    // ================= TRẢI NGHIỆM & HƯỚNG NGHIỆP – LỚP 10 =================

    private function seedTnHn101(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ma trận Eisenhower chia công việc theo hai tiêu chí nào?',
                ['Quan trọng và khẩn cấp', 'Dễ và khó', 'Thích và không thích', 'Dài và ngắn'], 0,
                'Hai trục quan trọng – khẩn cấp tạo thành 4 ô công việc.');
            $this->quiz($L, 'Công việc quan trọng và khẩn cấp nên xử lý thế nào?',
                ['Làm ngay', 'Lên lịch làm sau', 'Ủy thác cho người khác', 'Loại bỏ'], 0,
                'Ô 1 (quan trọng + khẩn cấp) cần làm ngay lập tức.');
            $this->quiz($L, 'Học sinh nên dành nhiều thời gian nhất cho ô nào?',
                ['Quan trọng nhưng không khẩn cấp', 'Khẩn cấp nhưng không quan trọng',
                 'Không quan trọng cũng không khẩn cấp', 'Chỉ làm việc khẩn cấp'], 0,
                'Ô 2 (quan trọng, không khẩn cấp) như ôn bài, đọc sách tạo nên thành công lâu dài.', 'trung_binh');
            $this->quiz($L, 'Việc không quan trọng cũng không khẩn cấp nên làm gì?',
                ['Loại bỏ hoặc hạn chế tối đa', 'Ưu tiên làm trước',
                 'Dành cả ngày để làm', 'Nhờ người khác làm'], 0,
                'Ô 4 là những việc gây lãng phí thời gian như lướt mạng vô định.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi ô trong ma trận Eisenhower với cách xử lý.',
                [['Quan trọng + khẩn cấp', 'Làm ngay'],
                 ['Quan trọng + không khẩn cấp', 'Lên lịch thực hiện'],
                 ['Khẩn cấp + không quan trọng', 'Ủy thác cho người khác'],
                 ['Không quan trọng + không khẩn cấp', 'Loại bỏ']],
                'Bốn ô, bốn cách xử lý khác nhau.');
            $this->matching($L, 'Nối mỗi ví dụ với ô phù hợp của nó.',
                [['Ôn bài kiểm tra ngày mai', 'Quan trọng + khẩn cấp'],
                 ['Đọc sách mở rộng', 'Quan trọng + không khẩn cấp'],
                 ['Trả lời tin nhắn không gấp', 'Khẩn cấp + không quan trọng'],
                 ['Lướt mạng vô định', 'Không quan trọng + không khẩn cấp']],
                'Phân loại đúng giúp sắp xếp thời gian hợp lý.');
            $this->matching($L, 'Nối mỗi thói quen với đánh giá của nó.',
                [['Lập kế hoạch tuần', 'Thói quen tốt'],
                 ['Rà soát mỗi tối', 'Thói quen tốt'],
                 ['Làm việc theo cảm hứng', 'Thói quen xấu'],
                 ['Để việc đến phút cuối', 'Thói quen xấu']],
                'Kỷ luật thời gian tạo nên sự khác biệt.');
            $this->matching($L, 'Nối mỗi công cụ với tác dụng của nó.',
                [['Thời khóa biểu', 'Khung thời gian cố định'],
                 ['To-do list', 'Danh sách việc cần làm'],
                 ['Lịch tuần', 'Kế hoạch 7 ngày'],
                 ['Báo thức', 'Nhắc nhở thời gian']],
                'Công cụ hỗ trợ quản lý thời gian hiệu quả.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi công việc vào đúng ô của ma trận Eisenhower.',
                [['Nộp bài tập hôm nay', 'Quan trọng + khẩn cấp'], ['Ôn thi ngày mai', 'Quan trọng + khẩn cấp'],
                 ['Đọc sách mở rộng', 'Quan trọng + không khẩn cấp'], ['Rèn kỹ năng', 'Quan trọng + không khẩn cấp'],
                 ['Lướt mạng xã hội', 'Không quan trọng + không khẩn cấp'],
                 ['Chơi game hàng giờ', 'Không quan trọng + không khẩn cấp']],
                'Ưu tiên ô 1, đầu tư ô 2, hạn chế ô 4.');
            $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm TỐT hoặc XẤU trong quản lý thời gian.',
                [['Lập kế hoạch tuần', 'Tốt'], ['Rà soát mỗi tối', 'Tốt'], ['Ưu tiên việc quan trọng', 'Tốt'],
                 ['Nước đến chân mới nhảy', 'Xấu'], ['Lướt điện thoại khi học', 'Xấu'],
                 ['Không có kế hoạch', 'Xấu']],
                'Thói quen tốt tạo nên người quản lý thời gian giỏi.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Ma trận Eisenhower có 4 ô', 'Đúng'],
                 ['Việc quan trọng + khẩn cấp cần làm ngay', 'Đúng'],
                 ['Nên đầu tư vào việc quan trọng nhưng không khẩn cấp', 'Đúng'],
                 ['Việc vô bổ nên loại bỏ', 'Đúng'],
                 ['Mọi việc đều quan trọng như nhau', 'Sai'],
                 ['Không cần lập kế hoạch', 'Sai']],
                'Phân loại ưu tiên là kỹ năng cốt lõi.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN ƯU TIÊN hoặc NÊN HẠN CHẾ.',
                [['Ôn bài', 'Ưu tiên'], ['Đọc sách', 'Ưu tiên'], ['Tập thể dục', 'Ưu tiên'],
                 ['Lướt mạng vô định', 'Hạn chế'], ['Chơi game quá đà', 'Hạn chế'],
                 ['Tám chuyện hàng giờ', 'Hạn chế']],
                'Ưu tiên việc quan trọng, hạn chế việc vô bổ.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ma trận Eisenhower chia việc theo mức độ quan trọng và ___ cấp.', [[0, 'khẩn']],
                'Hai tiêu chí: quan trọng và khẩn cấp.');
            $this->fill($L, 'Việc quan trọng và khẩn cấp cần làm ___.', [[0, 'ngay']],
                'Ô 1 là ưu tiên số một.');
            $this->fill($L, 'Học sinh nên đầu tư nhiều vào việc quan trọng nhưng không khẩn ___.', [[0, 'cấp']],
                'Ô 2 tạo nên thành công lâu dài.');
            $this->fill($L, 'Việc không quan trọng cũng không khẩn cấp nên ___ bỏ.', [[0, 'loại']],
                'Ô 4 là kẻ cắp thời gian.');
        }
    }

    private function seedTnHn102(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kỹ thuật Pomodoro có cấu trúc như thế nào?',
                ['Học 25 phút, nghỉ 5 phút', 'Học 5 phút, nghỉ 25 phút',
                 'Học liên tục 4 giờ', 'Nghỉ cả buổi'], 0,
                'Pomodoro: 25 phút tập trung + 5 phút nghỉ; sau 4 phiên nghỉ dài.');
            $this->quiz($L, 'Sau 4 phiên Pomodoro nên nghỉ bao lâu?',
                ['15 – 30 phút', '1 phút', '5 phút', 'Cả ngày'], 0,
                'Nghỉ dài giúp não phục hồi sau chuỗi tập trung.');
            $this->quiz($L, 'Nguyên nhân phổ biến của sự trì hoãn là gì?',
                ['Việc quá lớn, sợ thất bại, bị xao nhãng', 'Việc quá dễ',
                 'Có quá nhiều thời gian', 'Được mọi người giúp đỡ'], 0,
                'Hiểu nguyên nhân mới khắc phục được trì hoãn.');
            $this->quiz($L, 'Quy tắc 2 phút trong quản lý công việc là gì?',
                ['Việc gì làm được trong 2 phút thì làm ngay', 'Chỉ làm việc 2 phút mỗi ngày',
                 'Nghỉ ngơi 2 phút mỗi giờ', 'Đếm đến 2 rồi bỏ cuộc'], 0,
                'Quy tắc 2 phút giúp xử lý ngay việc nhỏ, tránh tồn đọng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi yếu tố Pomodoro với thời gian của nó.',
                [['Một phiên tập trung', '25 phút'],
                 ['Nghỉ ngắn', '5 phút'],
                 ['Nghỉ dài', '15 – 30 phút'],
                 ['Chu kỳ nghỉ dài', 'Sau 4 phiên']],
                'Cấu trúc chuẩn của kỹ thuật Pomodoro.');
            $this->matching($L, 'Nối mỗi nguyên nhân trì hoãn với cách khắc phục.',
                [['Việc quá lớn', 'Chia nhỏ ra từng phần'],
                 ['Sợ thất bại', 'Bắt đầu từ phần dễ nhất'],
                 ['Bị xao nhãng', 'Tắt thông báo, dọn bàn học'],
                 ['Không có động lực', 'Nhớ lại mục tiêu của mình']],
                'Mỗi nguyên nhân có một giải pháp riêng.');
            $this->matching($L, 'Nối mỗi hành động với tác dụng của nó.',
                [['Tắt thông báo', 'Giảm xao nhãng'],
                 ['Dọn bàn học', 'Tạo không gian tập trung'],
                 ['Chia nhỏ việc', 'Việc bớt đáng sợ hơn'],
                 ['Bắt đầu ngay', 'Phá vỡ quán tính trì hoãn']],
                'Hành động nhỏ tạo đà cho sự tập trung.');
            $this->matching($L, 'Nối mỗi quy tắc với nội dung của nó.',
                [['Quy tắc 2 phút', 'Việc nhỏ làm ngay'],
                 ['Ăn ếch', 'Làm việc khó nhất trước'],
                 ['Không chạm điện thoại', 'Giờ học tập trung'],
                 ['Một việc một lúc', 'Không đa nhiệm']],
                'Các quy tắc chống trì hoãn hiệu quả.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm GIÚP TẬP TRUNG hoặc GÂY XAO NHÃNG.',
                [['Tắt thông báo điện thoại', 'Tập trung'], ['Dọn bàn học gọn gàng', 'Tập trung'],
                 ['Học theo Pomodoro', 'Tập trung'],
                 ['Vừa học vừa lướt mạng', 'Xao nhãng'], ['Để điện thoại cạnh bên', 'Xao nhãng'],
                 ['Học trong phòng ồn ào', 'Xao nhãng']],
                'Môi trường quyết định khả năng tập trung.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm LÀM NGAY (quy tắc 2 phút) hoặc ĐỂ SAU.',
                [['Trả lời tin nhắn ngắn', 'Làm ngay'], ['Ghi nhanh ý tưởng', 'Làm ngay'],
                 ['Cất sách lên kệ', 'Làm ngay'],
                 ['Viết bài luận dài', 'Để sau'], ['Ôn cả chương khó', 'Để sau'],
                 ['Làm dự án lớn', 'Để sau']],
                'Quy tắc 2 phút chỉ áp dụng cho việc nhỏ.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Pomodoro: học 25 phút, nghỉ 5 phút', 'Đúng'],
                 ['Sau 4 phiên nên nghỉ dài 15–30 phút', 'Đúng'],
                 ['Chia nhỏ việc giúp bớt trì hoãn', 'Đúng'],
                 ['Tắt thông báo giúp tập trung', 'Đúng'],
                 ['Đa nhiệm giúp học hiệu quả hơn', 'Sai'],
                 ['Trì hoãn không có cách khắc phục', 'Sai']],
                'Đa nhiệm làm giảm chất lượng; trì hoãn hoàn toàn khắc phục được.');
            $this->sortQ($L, 'Kéo mỗi nguyên nhân vào nhóm NGUYÊN NHÂN TRÌ HOÃN hoặc KHÔNG PHẢI.',
                [['Việc quá lớn', 'Trì hoãn'], ['Sợ thất bại', 'Trì hoãn'], ['Bị xao nhãng', 'Trì hoãn'],
                 ['Việc rõ ràng, vừa sức', 'Không phải'], ['Có kế hoạch cụ thể', 'Không phải'],
                 ['Môi trường yên tĩnh', 'Không phải']],
                'Nhận diện đúng nguyên nhân để khắc phục.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Pomodoro: học tập trung 25 phút rồi nghỉ ___ phút.', [[0, '5']],
                'Chu kỳ 25/5 là cốt lõi của Pomodoro.');
            $this->fill($L, 'Sau 4 phiên Pomodoro nên nghỉ dài 15 – 30 ___.', [[0, 'phút']],
                'Nghỉ dài giúp não phục hồi.');
            $this->fill($L, 'Khi việc quá lớn gây trì hoãn, hãy ___ nhỏ nó ra.', [[0, 'chia']],
                'Việc nhỏ bớt đáng sợ, dễ bắt đầu hơn.');
            $this->fill($L, 'Quy tắc 2 phút: việc làm được trong 2 phút thì làm ___.', [[0, 'ngay']],
                'Xử lý ngay việc nhỏ tránh tồn đọng.');
        }
    }

    private function seedTnHn103(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phương pháp ghi chú Cornell chia trang giấy thành mấy phần?',
                ['3 phần', '2 phần', '4 phần', '1 phần'], 0,
                'Cornell gồm: ghi chú chính, cột từ khóa/câu hỏi, phần tóm tắt.');
            $this->quiz($L, 'Cột từ khóa/câu hỏi trong ghi chú Cornell dùng để làm gì?',
                ['Ghi câu hỏi, từ khóa để tự kiểm tra', 'Vẽ tranh trang trí',
                 'Ghi chép đầy đủ bài giảng', 'Để trống không dùng'], 0,
                'Cột này giúp ôn tập bằng cách tự đặt câu hỏi.');
            $this->quiz($L, 'Kỹ thuật Feynman là gì?',
                ['Giải thích lại kiến thức bằng lời đơn giản như đang dạy người khác',
                 'Học thuộc lòng từng chữ', 'Chép bài nhiều lần',
                 'Nghe giảng mà không ghi chép'], 0,
                'Giải thích được bằng lời đơn giản chứng tỏ đã hiểu sâu.', 'trung_binh');
            $this->quiz($L, 'Theo kỹ thuật Feynman, khi nào bạn biết mình chưa hiểu bài?',
                ['Khi lúng túng không giải thích được', 'Khi đọc thuộc lòng được',
                 'Khi chép bài đầy đủ', 'Khi nghe giảng chăm chú'], 0,
                'Chỗ lúng túng chính là chỗ cần học lại.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phần của ghi chú Cornell với nội dung của nó.',
                [['Ghi chú chính', 'Nội dung bài học'],
                 ['Cột từ khóa', 'Câu hỏi, từ khóa để tự kiểm tra'],
                 ['Phần tóm tắt', 'Ý chính của cả trang'],
                 ['Tiêu đề', 'Chủ đề bài học, ngày tháng']],
                'Bốn phần của trang ghi chú Cornell.');
            $this->matching($L, 'Nối mỗi bước Feynman với mô tả của nó.',
                [['Chọn khái niệm', 'Xác định điều cần học'],
                 ['Giải thích đơn giản', 'Dạy lại như cho người mới'],
                 ['Tìm chỗ lúng túng', 'Phát hiện điểm chưa hiểu'],
                 ['Học lại và đơn giản hóa', 'Hoàn thiện hiểu biết']],
                'Bốn bước của kỹ thuật Feynman.');
            $this->matching($L, 'Nối mỗi cách học với mức độ hiệu quả.',
                [['Dạy lại cho bạn', 'Hiểu sâu nhất'],
                 ['Tự giải thích', 'Hiểu khá sâu'],
                 ['Đọc lại nhiều lần', 'Hiểu nông'],
                 ['Tô highlight', 'Hiệu quả thấp']],
                'Học chủ động hiệu quả hơn học thụ động.');
            $this->matching($L, 'Nối mỗi thói quen ghi chú với đánh giá.',
                [['Ghi ý chính bằng lời mình', 'Tốt'],
                 ['Vẽ sơ đồ tư duy', 'Tốt'],
                 ['Chép nguyên văn slide', 'Kém'],
                 ['Không ghi gì cả', 'Kém']],
                'Ghi chú tốt là xử lý thông tin, không phải sao chép.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cách học vào nhóm CHỦ ĐỘNG hoặc THỤ ĐỘNG.',
                [['Tự giải thích lại bài', 'Chủ động'], ['Dạy lại cho bạn', 'Chủ động'],
                 ['Làm bài tập vận dụng', 'Chủ động'],
                 ['Đọc lướt tài liệu', 'Thụ động'], ['Tô highlight', 'Thụ động'],
                 ['Nghe giảng không ghi chép', 'Thụ động']],
                'Học chủ động ghi nhớ lâu hơn nhiều.');
            $this->sortQ($L, 'Kéo mỗi phần vào đúng vị trí trong trang Cornell.',
                [['Nội dung bài học', 'Cột ghi chú chính'], ['Từ khóa, câu hỏi', 'Cột bên trái'],
                 ['Tóm tắt ý chính', 'Cuối trang'], ['Chủ đề, ngày tháng', 'Đầu trang'],
                 ['Vẽ trang trí', 'Không thuộc Cornell'], ['Chép nguyên văn', 'Không thuộc Cornell']],
                'Bố cục Cornell tối ưu cho việc ôn tập.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Cornell chia trang thành 3 phần chính', 'Đúng'],
                 ['Feynman là giải thích bằng lời đơn giản', 'Đúng'],
                 ['Dạy lại cho bạn giúp hiểu sâu', 'Đúng'],
                 ['Chỗ lúng túng là chỗ cần học lại', 'Đúng'],
                 ['Chép nguyên văn là cách ghi chú tốt', 'Sai'],
                 ['Tô highlight nhiều là đủ để nhớ', 'Sai']],
                'Hiểu sâu đến từ xử lý thông tin chủ động.');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm NÊN LÀM hoặc KHÔNG NÊN khi ghi chú.',
                [['Ghi ý chính bằng lời mình', 'Nên làm'], ['Vẽ sơ đồ tóm tắt', 'Nên làm'],
                 ['Ghi câu hỏi tự kiểm tra', 'Nên làm'],
                 ['Chép nguyên văn từng chữ', 'Không nên'], ['Viết kín mít không khoảng trống', 'Không nên'],
                 ['Không bao giờ xem lại', 'Không nên']],
                'Ghi chú tốt phục vụ việc ôn tập sau này.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phương pháp ghi chú ___ chia trang thành 3 phần.', [[0, 'Cornell']],
                'Cornell: ghi chú chính, cột từ khóa, phần tóm tắt.');
            $this->fill($L, 'Kỹ thuật ___ là giải thích lại kiến thức bằng lời đơn giản.', [[0, 'Feynman']],
                'Dạy lại được cho người khác chứng tỏ đã hiểu sâu.');
            $this->fill($L, 'Trong Feynman, chỗ ___ túng chính là chỗ chưa hiểu.', [[0, 'lúng']],
                'Phát hiện điểm yếu để học lại đúng chỗ.');
            $this->fill($L, 'Dạy lại cho bạn là cách học ___ nhất.', [[0, 'sâu']],
                'Giảng dạy đòi hỏi hiểu biết thấu đáo.');
        }
    }

    private function seedTnHn104(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ôn tập ngắt quãng (spaced repetition) là gì?',
                ['Ôn lại sau các khoảng thời gian tăng dần', 'Ôn một lần duy nhất',
                 'Nhồi nhét một lúc trước thi', 'Không bao giờ ôn lại'], 0,
                'Ôn sau 1 ngày, 3 ngày, 1 tuần, 1 tháng giúp nhớ lâu.');
            $this->quiz($L, 'Kỹ thuật tự kiểm tra (retrieval practice) thực hiện thế nào?',
                ['Đóng sách và viết ra những gì nhớ được', 'Đọc đi đọc lại tài liệu',
                 'Nhìn đáp án rồi học thuộc', 'Nhờ bạn đọc hộ'], 0,
                'Tự gợi nhớ củng cố trí nhớ mạnh hơn đọc lại.', 'trung_binh');
            $this->quiz($L, 'Khi làm bài thi nên bắt đầu từ đâu?',
                ['Câu dễ trước, câu khó sau', 'Câu khó nhất trước',
                 'Làm ngẫu nhiên', 'Bỏ hết câu dễ'], 0,
                'Làm câu dễ trước đảm bảo điểm và tạo tâm lý tốt.');
            $this->quiz($L, 'Nên dành bao nhiêu thời gian cuối giờ để kiểm tra bài thi?',
                ['5 – 10 phút', 'Không cần kiểm tra', '1 phút', 'Cả buổi'], 0,
                'Kiểm tra lại giúp phát hiện sai sót đáng tiếc.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi kỹ thuật ôn tập với cách thực hiện.',
                [['Ôn ngắt quãng', 'Ôn lại sau khoảng thời gian tăng dần'],
                 ['Tự kiểm tra', 'Đóng sách viết ra điều đã nhớ'],
                 ['Nhồi nhét', 'Học dồn một lúc (kém hiệu quả)'],
                 ['Ôn xen kẽ', 'Trộn nhiều chủ đề khi ôn']],
                'Bốn cách ôn tập với hiệu quả khác nhau.');
            $this->matching($L, 'Nối mỗi giai đoạn ôn thi với việc nên làm.',
                [['Trước 1 tháng', 'Học đều các chủ đề'],
                 ['Trước 1 tuần', 'Ôn lại điểm yếu'],
                 ['Trước 1 ngày', 'Nghỉ ngơi, ôn nhẹ'],
                 ['Trong phòng thi', 'Bình tĩnh, phân bổ thời gian']],
                'Kế hoạch ôn thi theo từng giai đoạn.');
            $this->matching($L, 'Nối mỗi chiến lược làm bài với mô tả.',
                [['Đọc hết đề', 'Nắm toàn bộ yêu cầu'],
                 ['Câu dễ trước', 'Đảm bảo điểm chắc chắn'],
                 ['Phân bổ thời gian', 'Theo điểm số từng câu'],
                 ['Kiểm tra lại', 'Phát hiện sai sót']],
                'Bốn bước làm bài thi thông minh.');
            $this->matching($L, 'Nối mỗi sai lầm với hậu quả của nó.',
                [['Nhồi nhét', 'Quên nhanh sau thi'],
                 ['Thức khuya', 'Mệt mỏi, kém tập trung'],
                 ['Bỏ câu dễ', 'Mất điểm đáng tiếc'],
                 ['Không kiểm tra', 'Sai sót không phát hiện']],
                'Tránh sai lầm để thi tốt hơn.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cách ôn vào nhóm HIỆU QUẢ hoặc KÉM HIỆU QUẢ.',
                [['Ôn ngắt quãng', 'Hiệu quả'], ['Tự kiểm tra', 'Hiệu quả'],
                 ['Ôn xen kẽ chủ đề', 'Hiệu quả'],
                 ['Nhồi nhét một lúc', 'Kém hiệu quả'], ['Chỉ đọc lướt', 'Kém hiệu quả'],
                 ['Tô highlight', 'Kém hiệu quả']],
                'Ôn cách quãng và tự kiểm tra nhớ lâu nhất.');
            $this->sortQ($L, 'Kéo mỗi việc vào đúng thời điểm: TRƯỚC THI hay TRONG PHÒNG THI.',
                [['Ôn ngắt quãng', 'Trước thi'], ['Ngủ đủ giấc', 'Trước thi'], ['Chuẩn bị dụng cụ', 'Trước thi'],
                 ['Đọc hết đề', 'Trong phòng thi'], ['Làm câu dễ trước', 'Trong phòng thi'],
                 ['Kiểm tra lại bài', 'Trong phòng thi']],
                'Chuẩn bị tốt trước thi, chiến lược tốt trong thi.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nên ôn lại sau khoảng thời gian tăng dần', 'Đúng'],
                 ['Tự kiểm tra giúp nhớ lâu hơn đọc lại', 'Đúng'],
                 ['Nên làm câu dễ trước', 'Đúng'],
                 ['Nên dành 5–10 phút kiểm tra lại', 'Đúng'],
                 ['Nhồi nhét là cách ôn tốt nhất', 'Sai'],
                 ['Thức khuya trước thi giúp tỉnh táo', 'Sai']],
                'Nhồi nhét và thức khuya đều phản tác dụng.');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm NÊN LÀM hoặc KHÔNG NÊN trước ngày thi.',
                [['Ngủ đủ giấc', 'Nên làm'], ['Ôn nhẹ nhàng', 'Nên làm'], ['Chuẩn bị dụng cụ', 'Nên làm'],
                 ['Thức khuya nhồi nhét', 'Không nên'], ['Học bài mới hoàn toàn', 'Không nên'],
                 ['Lo lắng mất ngủ', 'Không nên']],
                'Ngày trước thi cần nghỉ ngơi, giữ tâm lý thoải mái.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ôn lại sau các khoảng thời gian tăng dần gọi là ôn tập ngắt ___.', [[0, 'quãng']],
                'Spaced repetition: 1 ngày, 3 ngày, 1 tuần, 1 tháng.');
            $this->fill($L, 'Đóng sách và viết ra điều đã nhớ gọi là tự kiểm ___.', [[0, 'tra']],
                'Retrieval practice củng cố trí nhớ rất mạnh.');
            $this->fill($L, 'Khi làm bài thi nên làm câu ___ trước.', [[0, 'dễ']],
                'Câu dễ đảm bảo điểm số và tâm lý tốt.');
            $this->fill($L, 'Nên dành 5 – 10 phút cuối giờ để kiểm tra ___ bài thi.', [[0, 'lại']],
                'Kiểm tra lại phát hiện sai sót đáng tiếc.');
        }
    }

    // ================= TRẢI NGHIỆM & HƯỚNG NGHIỆP – LỚP 11 =================

    private function seedTnHn111(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Lý thuyết Holland chia tính cách nghề nghiệp thành mấy nhóm?',
                ['6 nhóm', '4 nhóm', '8 nhóm', '10 nhóm'], 0,
                'Holland có 6 nhóm: R – I – A – S – E – C.');
            $this->quiz($L, 'Nhóm Realistic (R) phù hợp với công việc nào?',
                ['Kỹ thuật, thợ, kỹ sư', 'Họa sĩ, nhạc sĩ', 'Giáo viên, tư vấn', 'Kế toán, hành chính'], 0,
                'Realistic là nhóm kỹ thuật, thích làm việc với máy móc, công cụ.');
            $this->quiz($L, 'Nhóm Artistic (A) phù hợp với công việc nào?',
                ['Họa sĩ, nhạc sĩ, thiết kế', 'Bác sĩ, nhà khoa học',
                 'Kỹ sư, thợ máy', 'Kế toán, thủ kho'], 0,
                'Artistic là nhóm nghệ thuật, sáng tạo.');
            $this->quiz($L, 'Mã Holland của một người thường gồm mấy nhóm?',
                ['2 – 3 nhóm kết hợp', 'Chỉ 1 nhóm duy nhất', 'Cả 6 nhóm', 'Không có nhóm nào'], 0,
                'Mỗi người là sự kết hợp của 2–3 nhóm, ví dụ mã SAE.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nhóm Holland với mô tả của nó.',
                [['Realistic (R)', 'Kỹ thuật – thợ, kỹ sư'],
                 ['Investigative (I)', 'Nghiên cứu – nhà khoa học, bác sĩ'],
                 ['Artistic (A)', 'Nghệ thuật – họa sĩ, nhạc sĩ'],
                 ['Social (S)', 'Xã hội – giáo viên, tư vấn']],
                'Bốn trong sáu nhóm tính cách Holland.');
            $this->matching($L, 'Nối tiếp hai nhóm còn lại với mô tả.',
                [['Enterprising (E)', 'Quản lý – doanh nhân, lãnh đạo'],
                 ['Conventional (C)', 'Nghiệp vụ – kế toán, hành chính'],
                 ['Mã Holland', 'Sự kết hợp 2–3 nhóm của một người'],
                 ['Trắc nghiệm', 'Công cụ xác định nhóm của bản thân']],
                'Sáu nhóm tạo nên bức tranh tính cách nghề nghiệp.');
            $this->matching($L, 'Nối mỗi nghề với nhóm Holland phù hợp.',
                [['Thợ điện', 'Realistic'],
                 ['Nhà nghiên cứu', 'Investigative'],
                 ['Nhà văn', 'Artistic'],
                 ['Y tá', 'Social']],
                'Nghề nghiệp phản ánh nhóm tính cách.');
            $this->matching($L, 'Nối tiếp mỗi nghề với nhóm Holland phù hợp.',
                [['Giám đốc', 'Enterprising'],
                 ['Kế toán', 'Conventional'],
                 ['Kiến trúc sư', 'Artistic'],
                 ['Kỹ sư cầu đường', 'Realistic']],
                'Chọn nghề hợp nhóm tính cách giúp gắn bó lâu dài.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nghề vào đúng nhóm Holland của nó.',
                [['Thợ máy', 'Realistic'], ['Kỹ sư', 'Realistic'],
                 ['Bác sĩ', 'Investigative'], ['Nhà khoa học', 'Investigative'],
                 ['Họa sĩ', 'Artistic'], ['Nhạc sĩ', 'Artistic']],
                'R: kỹ thuật, I: nghiên cứu, A: nghệ thuật.');
            $this->sortQ($L, 'Kéo tiếp mỗi nghề vào đúng nhóm Holland.',
                [['Giáo viên', 'Social'], ['Tư vấn viên', 'Social'],
                 ['Doanh nhân', 'Enterprising'], ['Lãnh đạo', 'Enterprising'],
                 ['Kế toán', 'Conventional'], ['Thư ký', 'Conventional']],
                'S: xã hội, E: quản lý, C: nghiệp vụ.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Holland có 6 nhóm tính cách', 'Đúng'],
                 ['Mỗi người kết hợp 2–3 nhóm', 'Đúng'],
                 ['Realistic hợp với kỹ thuật', 'Đúng'],
                 ['Artistic hợp với nghệ thuật', 'Đúng'],
                 ['Mọi người chỉ thuộc 1 nhóm duy nhất', 'Sai'],
                 ['Chọn nghề không cần hợp tính cách', 'Sai']],
                'Hiểu nhóm của mình giúp chọn nghề phù hợp.');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm THÍCH LÀM VIỆC VỚI NGƯỜI hoặc VỚI SỰ VẬT.',
                [['Social', 'Với người'], ['Enterprising', 'Với người'],
                 ['Realistic', 'Với sự vật'], ['Investigative', 'Với sự vật'],
                 ['Artistic', 'Với ý tưởng'], ['Conventional', 'Với số liệu']],
                'Xu hướng đối tượng làm việc của từng nhóm.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Lý thuyết Holland chia tính cách nghề nghiệp thành ___ nhóm.', [[0, '6']],
                'Sáu nhóm: R – I – A – S – E – C.');
            $this->fill($L, 'Nhóm R (Realistic) phù hợp với công việc ___ thuật.', [[0, 'kỹ']],
                'Realistic thích làm việc với máy móc, công cụ.');
            $this->fill($L, 'Nhóm A (Artistic) là nhóm nghệ thuật, ___ tạo.', [[0, 'sáng']],
                'Artistic hợp với họa sĩ, nhạc sĩ, thiết kế.');
            $this->fill($L, 'Mã Holland của một người thường kết hợp 2 – 3 ___.', [[0, 'nhóm']],
                'Ví dụ mã SAE: Social – Artistic – Enterprising.');
        }
    }

    private function seedTnHn112(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Lĩnh vực công nghệ thông tin gồm những nghề nào?',
                ['Lập trình, dữ liệu, an ninh mạng', 'Bác sĩ, y tá',
                 'Giáo viên, giảng viên', 'Đầu bếp, phục vụ'], 0,
                'CNTT gồm lập trình viên, chuyên gia dữ liệu, an ninh mạng...');
            $this->quiz($L, 'Xu hướng nào đang tạo ra nhiều nghề mới hiện nay?',
                ['Công nghệ và chuyển đổi số', 'Nông nghiệp thuần túy',
                 'Thủ công truyền thống', 'Không có xu hướng nào'], 0,
                'Chuyển đổi số sinh ra nhiều nghề mới và thay đổi nghề cũ.');
            $this->quiz($L, 'Lĩnh vực y tế – sức khỏe gồm những nghề nào?',
                ['Bác sĩ, điều dưỡng, dược sĩ', 'Kỹ sư, thợ máy',
                 'Họa sĩ, nhạc sĩ', 'Kế toán, kiểm toán'], 0,
                'Y tế là lĩnh vực luôn có nhu cầu nhân lực cao.');
            $this->quiz($L, 'Nghề nào thuộc lĩnh vực nghệ thuật – truyền thông?',
                ['Thiết kế đồ họa, quay phim', 'Kế toán, kiểm toán',
                 'Bác sĩ, y tá', 'Kỹ sư xây dựng'], 0,
                'Nghệ thuật – truyền thông cần sự sáng tạo.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lĩnh vực với các nghề tiêu biểu.',
                [['Công nghệ thông tin', 'Lập trình, dữ liệu, an ninh mạng'],
                 ['Y tế – sức khỏe', 'Bác sĩ, điều dưỡng, dược sĩ'],
                 ['Giáo dục – đào tạo', 'Giáo viên, giảng viên, tư vấn'],
                 ['Kinh tế – kinh doanh', 'Kế toán, marketing, quản trị']],
                'Bốn lĩnh vực nghề nghiệp lớn trong xã hội.');
            $this->matching($L, 'Nối tiếp mỗi lĩnh vực với các nghề tiêu biểu.',
                [['Kỹ thuật – xây dựng', 'Kỹ sư, kiến trúc sư'],
                 ['Nghệ thuật – truyền thông', 'Thiết kế, quay phim, báo chí'],
                 ['Dịch vụ – du lịch', 'Hướng dẫn viên, lễ tân, đầu bếp'],
                 ['Nông nghiệp công nghệ cao', 'Kỹ sư nông nghiệp, chăn nuôi']],
                'Xã hội hiện đại có rất nhiều lĩnh vực nghề nghiệp.');
            $this->matching($L, 'Nối mỗi nghề mới với lĩnh vực của nó.',
                [['Chuyên gia dữ liệu', 'Công nghệ thông tin'],
                 ['An ninh mạng', 'Công nghệ thông tin'],
                 ['Marketing số', 'Kinh tế – kinh doanh'],
                 ['Y tế từ xa', 'Y tế – sức khỏe']],
                'Nghề mới ra đời từ chuyển đổi số.');
            $this->matching($L, 'Nối mỗi kỹ năng với lĩnh vực cần nó.',
                [['Lập trình', 'Công nghệ'],
                 ['Giao tiếp', 'Dịch vụ, giáo dục'],
                 ['Sáng tạo', 'Nghệ thuật'],
                 ['Phân tích số liệu', 'Kinh tế, công nghệ']],
                'Mỗi lĩnh vực đòi hỏi bộ kỹ năng riêng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nghề vào đúng lĩnh vực: CÔNG NGHỆ, Y TẾ, GIÁO DỤC hoặc KINH TẾ.',
                [['Lập trình viên', 'Công nghệ'], ['An ninh mạng', 'Công nghệ'],
                 ['Bác sĩ', 'Y tế'], ['Dược sĩ', 'Y tế'],
                 ['Giáo viên', 'Giáo dục'], ['Giảng viên', 'Giáo dục']],
                'Bốn lĩnh vực, mỗi lĩnh vực hai nghề tiêu biểu.');
            $this->sortQ($L, 'Kéo tiếp mỗi nghề vào đúng lĩnh vực.',
                [['Kế toán', 'Kinh tế'], ['Marketing', 'Kinh tế'],
                 ['Kiến trúc sư', 'Kỹ thuật'], ['Kỹ sư xây dựng', 'Kỹ thuật'],
                 ['Hướng dẫn viên', 'Dịch vụ'], ['Đầu bếp', 'Dịch vụ']],
                'Mở rộng bức tranh nghề nghiệp xã hội.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Chuyển đổi số tạo ra nhiều nghề mới', 'Đúng'],
                 ['Y tế luôn có nhu cầu nhân lực cao', 'Đúng'],
                 ['Mỗi lĩnh vực cần bộ kỹ năng riêng', 'Đúng'],
                 ['Công nghệ thay đổi yêu cầu nghề truyền thống', 'Đúng'],
                 ['Xã hội chỉ có một vài nghề', 'Sai'],
                 ['Nghề nghiệp không bao giờ thay đổi', 'Sai']],
                'Thị trường nghề nghiệp luôn vận động.');
            $this->sortQ($L, 'Kéo mỗi nghề vào nhóm NGHỀ MỚI hoặc NGHỀ TRUYỀN THỐNG.',
                [['Chuyên gia dữ liệu', 'Nghề mới'], ['An ninh mạng', 'Nghề mới'],
                 ['Marketing số', 'Nghề mới'],
                 ['Thợ mộc', 'Truyền thống'], ['Thợ may', 'Truyền thống'], ['Nông dân', 'Truyền thống']],
                'Nghề mới gắn với công nghệ số.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Lập trình, dữ liệu, an ninh mạng thuộc lĩnh vực công nghệ thông ___.', [[0, 'tin']],
                'CNTT là lĩnh vực phát triển nhanh nhất hiện nay.');
            $this->fill($L, '___ đổi số đang tạo ra nhiều nghề mới.', [[0, 'Chuyển']],
                'Chuyển đổi số thay đổi mọi lĩnh vực.');
            $this->fill($L, 'Bác sĩ, điều dưỡng, dược sĩ thuộc lĩnh vực y ___.', [[0, 'tế']],
                'Y tế luôn cần nhân lực chất lượng cao.');
            $this->fill($L, 'Thiết kế đồ họa thuộc lĩnh vực nghệ thuật – truyền ___.', [[0, 'thông']],
                'Truyền thông cần sự sáng tạo không ngừng.');
        }
    }

    private function seedTnHn113(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Sở thích, năng lực và tính cách khác nhau ở điểm nào?',
                ['Sở thích là điều thích làm, năng lực là điều làm tốt, tính cách là cách hành xử',
                 'Ba khái niệm hoàn toàn giống nhau', 'Chỉ có sở thích là quan trọng',
                 'Không có khái niệm nào đúng'], 0,
                'Phân biệt rõ ba yếu tố để tự đánh giá chính xác.');
            $this->quiz($L, 'Vì sao thích một nghề chưa đủ để chọn nghề đó?',
                ['Vì thích chưa chắc đã có năng lực phù hợp', 'Vì sở thích không quan trọng',
                 'Vì nghề nào cũng giống nhau', 'Vì không ai thích nghề nào cả'], 0,
                'Thích vẽ chưa chắc vẽ giỏi; cần cả năng lực và tính cách phù hợp.');
            $this->quiz($L, 'Cách nào giúp tự đánh giá bản thân khách quan?',
                ['Kết hợp kết quả học tập, nhận xét của thầy cô bạn bè và trắc nghiệm uy tín',
                 'Chỉ nghe theo cảm tính', 'Chỉ hỏi một người bạn',
                 'Đoán mò'], 0,
                'Đánh giá đa chiều giúp nhìn mình khách quan hơn.');
            $this->quiz($L, 'Hoạt động ngoại khóa giúp gì cho việc tìm hiểu bản thân?',
                ['Bộc lộ sở thích, năng lực thật qua trải nghiệm', 'Chỉ để vui chơi',
                 'Không có tác dụng gì', 'Làm mất thời gian học'], 0,
                'Trải nghiệm thực tế là cách tốt nhất để hiểu mình.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi khái niệm với định nghĩa của nó.',
                [['Sở thích', 'Điều bạn thích làm'],
                 ['Năng lực', 'Điều bạn làm tốt'],
                 ['Tính cách', 'Cách bạn hành xử ổn định'],
                 ['Giá trị', 'Điều bạn coi trọng']],
                'Bốn khái niệm nền tảng của hướng nghiệp.');
            $this->matching($L, 'Nối mỗi ví dụ với khái niệm tương ứng.',
                [['Thích vẽ tranh', 'Sở thích'],
                 ['Vẽ đẹp', 'Năng lực'],
                 ['Kiên nhẫn, tỉ mỉ', 'Tính cách'],
                 ['Coi trọng sáng tạo', 'Giá trị']],
                'Phân biệt qua ví dụ cụ thể.');
            $this->matching($L, 'Nối mỗi kênh thông tin với vai trò của nó.',
                [['Kết quả học tập', 'Phản ánh năng lực các môn'],
                 ['Nhận xét thầy cô', 'Góc nhìn khách quan'],
                 ['Bạn bè', 'Người hiểu tính cách bạn'],
                 ['Trắc nghiệm uy tín', 'Công cụ khoa học']],
                'Bốn kênh giúp tự đánh giá đa chiều.');
            $this->matching($L, 'Nối mỗi hoạt động với điều nó bộc lộ.',
                [['Thi học sinh giỏi', 'Năng lực học tập'],
                 ['Làm lớp trưởng', 'Năng lực lãnh đạo'],
                 ['Vẽ tranh tường', 'Năng khiếu nghệ thuật'],
                 ['Tình nguyện', 'Tấm lòng nhân ái']],
                'Hoạt động bộc lộ con người thật của bạn.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi mô tả vào nhóm SỞ THÍCH, NĂNG LỰC hoặc TÍNH CÁCH.',
                [['Thích đọc sách', 'Sở thích'], ['Thích chơi bóng đá', 'Sở thích'],
                 ['Giải toán nhanh', 'Năng lực'], ['Viết văn hay', 'Năng lực'],
                 ['Kiên nhẫn', 'Tính cách'], ['Hòa đồng', 'Tính cách']],
                'Thích – giỏi – cách hành xử là ba phạm trù khác nhau.');
            $this->sortQ($L, 'Kéo mỗi cách tự đánh giá vào nhóm KHÁCH QUAN hoặc CHỦ QUAN.',
                [['Xem kết quả học tập', 'Khách quan'], ['Làm trắc nghiệm uy tín', 'Khách quan'],
                 ['Hỏi ý kiến thầy cô', 'Khách quan'],
                 ['Chỉ theo cảm tính', 'Chủ quan'], ['Tự phong mình giỏi', 'Chủ quan'],
                 ['Nghe lời đồn', 'Chủ quan']],
                'Đánh giá khách quan dựa trên bằng chứng.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Thích chưa chắc đã giỏi', 'Đúng'],
                 ['Nên đánh giá bản thân đa chiều', 'Đúng'],
                 ['Trải nghiệm giúp hiểu mình hơn', 'Đúng'],
                 ['Sở thích, năng lực, tính cách có thể khác nhau', 'Đúng'],
                 ['Chỉ cần thích là chọn được nghề', 'Sai'],
                 ['Tự đánh giá không cần thiết', 'Sai']],
                'Hướng nghiệp cần hiểu mình một cách đầy đủ.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm BÊN TRONG hoặc BÊN NGOÀI con người.',
                [['Sở thích', 'Bên trong'], ['Năng lực', 'Bên trong'], ['Tính cách', 'Bên trong'],
                 ['Nhu cầu xã hội', 'Bên ngoài'], ['Thu nhập nghề', 'Bên ngoài'],
                 ['Điều kiện gia đình', 'Bên ngoài']],
                'Chọn nghề cần cân bằng yếu tố trong và ngoài.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Điều bạn thích làm gọi là sở ___.', [[0, 'thích']],
                'Sở thích là điểm khởi đầu của hướng nghiệp.');
            $this->fill($L, 'Điều bạn làm tốt gọi là năng ___.', [[0, 'lực']],
                'Năng lực cần được rèn luyện và kiểm chứng.');
            $this->fill($L, 'Thích vẽ chưa chắc đã vẽ ___.', [[0, 'giỏi']],
                'Sở thích và năng lực có thể khác nhau.');
            $this->fill($L, 'Trải nghiệm thực tế là cách tốt nhất để hiểu ___.', [[0, 'mình']],
                'Hiểu mình là bước đầu của chọn nghề đúng.');
        }
    }

    private function seedTnHn114(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Giá trị nghề nghiệp là gì?',
                ['Điều bạn coi trọng trong công việc', 'Mức lương duy nhất',
                 'Tên công ty', 'Địa điểm làm việc'], 0,
                'Giá trị nghề nghiệp: thu nhập, ổn định, sáng tạo, giúp người...');
            $this->quiz($L, 'Mục tiêu SMART có nghĩa là gì?',
                ['Cụ thể, đo được, khả thi, phù hợp, có thời hạn',
                 'Thật lớn lao, viển vông', 'Chung chung là được',
                 'Không cần thời hạn'], 0,
                'SMART: Specific, Measurable, Achievable, Relevant, Time-bound.', 'trung_binh');
            $this->quiz($L, 'Chữ S trong SMART nghĩa là gì?',
                ['Specific – cụ thể', 'Simple – đơn giản',
                 'Strong – mạnh mẽ', 'Short – ngắn gọn'], 0,
                'Mục tiêu cụ thể mới có thể hành động được.');
            $this->quiz($L, 'Vì sao mục tiêu cần có thời hạn?',
                ['Tạo động lực và cam kết hành động', 'Để gây áp lực vô ích',
                 'Không có lý do gì', 'Để khoe với bạn bè'], 0,
                'Thời hạn biến ước mơ thành kế hoạch hành động.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chữ trong SMART với ý nghĩa của nó.',
                [['S – Specific', 'Cụ thể'],
                 ['M – Measurable', 'Đo được'],
                 ['A – Achievable', 'Khả thi'],
                 ['R – Relevant', 'Phù hợp']],
                'Bốn trong năm tiêu chí SMART.');
            $this->matching($L, 'Nối tiếp chữ T và ví dụ mục tiêu SMART.',
                [['T – Time-bound', 'Có thời hạn'],
                 ['Mục tiêu tốt', 'Đỗ đại học ngành X năm 2027'],
                 ['Mục tiêu kém', 'Học giỏi (chung chung)'],
                 ['Kế hoạch', 'Các bước để đạt mục tiêu']],
                'Mục tiêu tốt phải cụ thể và có thời hạn.');
            $this->matching($L, 'Nối mỗi giá trị nghề nghiệp với mô tả.',
                [['Thu nhập cao', 'Coi trọng tiền bạc'],
                 ['Ổn định', 'Coi trọng sự chắc chắn'],
                 ['Sáng tạo', 'Coi trọng tự do thể hiện'],
                 ['Giúp đỡ người khác', 'Coi trọng ý nghĩa nhân văn']],
                'Mỗi người có hệ giá trị khác nhau.');
            $this->matching($L, 'Nối mỗi giá trị với nghề phù hợp.',
                [['Sáng tạo', 'Thiết kế, nghệ thuật'],
                 ['Giúp người', 'Y tế, giáo dục'],
                 ['Thu nhập', 'Kinh doanh, tài chính'],
                 ['Ổn định', 'Công chức, viên chức']],
                'Giá trị dẫn lối chọn nghề phù hợp.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi mục tiêu vào nhóm SMART hoặc KHÔNG SMART.',
                [['Đỗ ngành X năm 2027', 'SMART'], ['Đạt 8.0 IELTS trong 1 năm', 'SMART'],
                 ['Học giỏi', 'Không SMART'], ['Thành công', 'Không SMART'],
                 ['Giàu có', 'Không SMART'], ['Kiếm 10 triệu/tháng sau 2 năm', 'SMART']],
                'SMART: cụ thể, đo được, có thời hạn.');
            $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm ƯU TIÊN VẬT CHẤT hoặc TINH THẦN.',
                [['Thu nhập cao', 'Vật chất'], ['Ổn định', 'Vật chất'], ['Danh tiếng', 'Vật chất'],
                 ['Sáng tạo', 'Tinh thần'], ['Giúp đỡ người khác', 'Tinh thần'],
                 ['Tự do', 'Tinh thần']],
                'Không có giá trị nào đúng cho mọi người.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Mục tiêu cần cụ thể và có thời hạn', 'Đúng'],
                 ['Giá trị nghề nghiệp khác nhau ở mỗi người', 'Đúng'],
                 ['Trung thực với mình khi chọn giá trị', 'Đúng'],
                 ['Mục tiêu SMART dễ hành động hơn', 'Đúng'],
                 ['Mục tiêu càng chung chung càng tốt', 'Sai'],
                 ['Không cần đặt mục tiêu cuộc đời', 'Sai']],
                'Mục tiêu rõ ràng dẫn lối hành động.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUỘC VỀ MỤC TIÊU hoặc KẾ HOẠCH.',
                [['Đích đến', 'Mục tiêu'], ['Thời hạn', 'Mục tiêu'], ['Kết quả mong muốn', 'Mục tiêu'],
                 ['Các bước thực hiện', 'Kế hoạch'], ['Nguồn lực cần có', 'Kế hoạch'],
                 ['Lịch trình chi tiết', 'Kế hoạch']],
                'Mục tiêu là đích, kế hoạch là đường đi.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Điều bạn coi trọng trong công việc gọi là giá trị nghề ___.', [[0, 'nghiệp']],
                'Giá trị nghề nghiệp dẫn lối chọn nghề.');
            $this->fill($L, 'Mục tiêu SMART phải cụ thể, đo được, khả thi, phù hợp và có thời ___.', [[0, 'hạn']],
                'Thời hạn tạo cam kết hành động.');
            $this->fill($L, 'Chữ S trong SMART nghĩa là ___ (cụ thể).', [[0, 'Specific']],
                'Specific: mục tiêu phải rõ ràng, cụ thể.');
            $this->fill($L, 'Không có giá trị nghề nghiệp nào đúng cho ___ người.', [[0, 'mọi']],
                'Quan trọng là trung thực với chính mình.');
        }
    }

    // ================= TRẢI NGHIỆM & HƯỚNG NGHIỆP – LỚP 12 =================

    private function seedTnHn121(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Chọn ngành học nên dựa trên mấy chân kiềng?',
                ['3: hiểu mình, hiểu nghề, hiểu điều kiện', '1: điểm chuẩn năm trước',
                 '2: ý cha mẹ và bạn bè', 'Không cần căn cứ gì'], 0,
                'Ba chân kiềng giúp quyết định cân bằng và bền vững.');
            $this->quiz($L, 'Hiểu mình trong chọn ngành gồm những gì?',
                ['Sở thích, năng lực', 'Chiều cao, cân nặng',
                 'Màu sắc yêu thích', 'Món ăn yêu thích'], 0,
                'Sở thích và năng lực là nền tảng của hiểu mình.');
            $this->quiz($L, 'Sai lầm phổ biến nào cần tránh khi chọn ngành?',
                ['Chọn theo phong trào, theo tên ngành nghe hay', 'Tìm hiểu kỹ về nghề',
                 'Hỏi ý kiến thầy cô', 'Tham quan trường'], 0,
                'Chọn theo phong trào dễ dẫn đến hối hận sau này.');
            $this->quiz($L, 'Vì sao không nên chỉ nhìn điểm chuẩn năm trước để chọn ngành?',
                ['Điểm chuẩn thay đổi theo từng năm', 'Điểm chuẩn không bao giờ đổi',
                 'Điểm chuẩn không quan trọng', 'Năm nào cũng giống nhau'], 0,
                'Điểm chuẩn biến động theo số lượng và chất lượng thí sinh.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chân kiềng với nội dung của nó.',
                [['Hiểu mình', 'Sở thích, năng lực'],
                 ['Hiểu nghề', 'Công việc thực tế, thu nhập, cơ hội'],
                 ['Hiểu điều kiện', 'Học lực, tài chính gia đình'],
                 ['Quyết định', 'Cân bằng cả 3 chân kiềng']],
                'Ba chân kiềng của chọn ngành đúng.');
            $this->matching($L, 'Nối mỗi sai lầm với hậu quả của nó.',
                [['Chọn theo phong trào', 'Dễ hối hận'],
                 ['Chọn vì tên hay', 'Không hợp thực tế'],
                 ['Bỏ qua bản thân', 'Chán nản khi học'],
                 ['Chỉ nhìn điểm chuẩn', 'Đánh giá sai cơ hội']],
                'Bốn sai lầm phổ biến cần tránh.');
            $this->matching($L, 'Nối mỗi việc nên làm với tác dụng của nó.',
                [['Tìm hiểu nghề', 'Biết công việc thực tế'],
                 ['Hỏi người trong nghề', 'Góc nhìn thực tế'],
                 ['Trải nghiệm', 'Cảm nhận phù hợp'],
                 ['Tham quan trường', 'Hiểu môi trường học']],
                'Tìm hiểu kỹ trước khi quyết định.');
            $this->matching($L, 'Nối mỗi câu hỏi với chân kiềng nó thuộc về.',
                [['Mình thích gì, giỏi gì?', 'Hiểu mình'],
                 ['Nghề này làm gì mỗi ngày?', 'Hiểu nghề'],
                 ['Học phí có phù hợp?', 'Hiểu điều kiện'],
                 ['Cơ hội việc làm ra sao?', 'Hiểu nghề']],
                'Tự hỏi đúng câu hỏi để chọn đúng ngành.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cách chọn ngành vào nhóm ĐÚNG ĐẮN hoặc SAI LẦM.',
                [['Dựa trên hiểu mình, hiểu nghề', 'Đúng đắn'], ['Tìm hiểu kỹ trước khi chọn', 'Đúng đắn'],
                 ['Cân nhắc điều kiện gia đình', 'Đúng đắn'],
                 ['Chọn theo phong trào', 'Sai lầm'], ['Chọn vì tên ngành hay', 'Sai lầm'],
                 ['Bỏ qua sở thích bản thân', 'Sai lầm']],
                'Chọn ngành là quyết định quan trọng của cuộc đời.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào đúng chân kiềng: HIỂU MÌNH, HIỂU NGHỀ hoặc HIỂU ĐIỀU KIỆN.',
                [['Sở thích', 'Hiểu mình'], ['Năng lực', 'Hiểu mình'],
                 ['Công việc thực tế', 'Hiểu nghề'], ['Thu nhập, cơ hội', 'Hiểu nghề'],
                 ['Học lực', 'Hiểu điều kiện'], ['Tài chính gia đình', 'Hiểu điều kiện']],
                'Ba chân kiềng thiếu một là quyết định khập khiễng.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Chọn ngành cần hiểu mình, hiểu nghề', 'Đúng'],
                 ['Nên tránh chọn theo phong trào', 'Đúng'],
                 ['Điểm chuẩn thay đổi theo từng năm', 'Đúng'],
                 ['Cần cân nhắc điều kiện gia đình', 'Đúng'],
                 ['Tên ngành hay là đủ để chọn', 'Sai'],
                 ['Không cần tìm hiểu về nghề', 'Sai']],
                'Tìm hiểu kỹ là cách tôn trọng tương lai của mình.');
            $this->sortQ($L, 'Kéo mỗi nguồn thông tin vào nhóm ĐÁNG TIN hoặc CẦN KIỂM CHỨNG.',
                [['Website chính thức của trường', 'Đáng tin'], ['Người đang làm nghề đó', 'Đáng tin'],
                 ['Thầy cô tư vấn', 'Đáng tin'],
                 ['Tin đồn trên mạng', 'Kiểm chứng'], ['Quảng cáo một chiều', 'Kiểm chứng'],
                 ['Lời rủ rê của bạn', 'Kiểm chứng']],
                'Thông tin chính thống đáng tin hơn tin đồn.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Chọn ngành dựa trên 3 chân kiềng: hiểu mình, hiểu nghề và hiểu điều ___.', [[0, 'kiện']],
                'Điều kiện gồm học lực và tài chính gia đình.');
            $this->fill($L, 'Sai lầm phổ biến là chọn ngành theo phong ___.', [[0, 'trào']],
                'Phong trào qua đi, hối hận ở lại.');
            $this->fill($L, 'Điểm chuẩn ___ đổi theo từng năm.', [[0, 'thay']],
                'Không nên chỉ dựa vào điểm chuẩn năm trước.');
            $this->fill($L, 'Hiểu mình gồm sở thích và năng ___.', [[0, 'lực']],
                'Nền tảng đầu tiên của chọn ngành đúng.');
        }
    }

    private function seedTnHn122(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nên tìm hiểu thông tin trường đại học ở đâu là đáng tin nhất?',
                ['Website chính thức của trường', 'Tin đồn trên mạng xã hội',
                 'Nghe bạn bè kể lại', 'Quảng cáo một chiều'], 0,
                'Website chính thức cung cấp thông tin chính xác nhất.');
            $this->quiz($L, 'Phương thức xét tuyển nào dùng điểm thi tốt nghiệp THPT?',
                ['Xét điểm thi tốt nghiệp', 'Xét học bạ',
                 'Xét tuyển thẳng', 'Chứng chỉ quốc tế'], 0,
                'Đây là phương thức truyền thống và phổ biến nhất.');
            $this->quiz($L, 'Xét học bạ là phương thức dựa trên gì?',
                ['Điểm trung bình các năm học THPT', 'Điểm thi tốt nghiệp',
                 'Kết quả phỏng vấn', 'Chiều cao cân nặng'], 0,
                'Xét học bạ dùng điểm học bạ THPT để xét tuyển.');
            $this->quiz($L, 'Khi đăng ký nguyện vọng nên sắp xếp thế nào?',
                ['Từ cao đến thấp theo mức độ ưu tiên', 'Ngẫu nhiên',
                 'Chỉ đăng ký 1 nguyện vọng', 'Từ thấp đến cao'], 0,
                'Xếp nguyện vọng yêu thích nhất lên đầu để tối đa cơ hội.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phương thức xét tuyển với mô tả của nó.',
                [['Điểm thi tốt nghiệp', 'Dùng điểm kỳ thi THPT'],
                 ['Xét học bạ', 'Dùng điểm trung bình THPT'],
                 ['Đánh giá năng lực', 'Kỳ thi riêng của trường'],
                 ['Xét tuyển thẳng', 'Dành cho đối tượng ưu tiên']],
                'Bốn phương thức xét tuyển phổ biến.');
            $this->matching($L, 'Nối mỗi thông tin cần tìm với nơi tra cứu.',
                [['Chương trình đào tạo', 'Website của trường'],
                 ['Học phí', 'Website của trường'],
                 ['Việc làm sau tốt nghiệp', 'Báo cáo của trường'],
                 ['Học bổng', 'Phòng tuyển sinh']],
                'Tìm hiểu kỹ trước khi đăng ký.');
            $this->matching($L, 'Nối mỗi đối tượng với phương thức phù hợp.',
                [['Học sinh giỏi quốc gia', 'Xét tuyển thẳng'],
                 ['Có IELTS cao', 'Chứng chỉ quốc tế'],
                 ['Học đều các năm', 'Xét học bạ'],
                 ['Thi tốt nghiệp tốt', 'Xét điểm thi']],
                'Mỗi phương thức phù hợp với một thế mạnh.');
            $this->matching($L, 'Nối mỗi bước với trình tự đăng ký.',
                [['Tìm hiểu', 'Bước 1: thu thập thông tin'],
                 ['Chọn ngành trường', 'Bước 2: quyết định'],
                 ['Chuẩn bị hồ sơ', 'Bước 3: giấy tờ'],
                 ['Đăng ký', 'Bước 4: nộp nguyện vọng']],
                'Quy trình 4 bước đăng ký xét tuyển.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phương thức vào nhóm DỰA TRÊN ĐIỂM SỐ hoặc DỰA TRÊN THÀNH TÍCH.',
                [['Điểm thi tốt nghiệp', 'Điểm số'], ['Xét học bạ', 'Điểm số'],
                 ['Đánh giá năng lực', 'Điểm số'],
                 ['Giải học sinh giỏi', 'Thành tích'], ['Chứng chỉ quốc tế', 'Thành tích'],
                 ['Ưu tiên khu vực', 'Thành tích']],
                'Hai nhóm phương thức xét tuyển chính.');
            $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm CẦN TÌM HIỂU hoặc KHÔNG CẦN.',
                [['Chương trình đào tạo', 'Cần tìm hiểu'], ['Học phí', 'Cần tìm hiểu'],
                 ['Cơ sở vật chất', 'Cần tìm hiểu'], ['Việc làm sau tốt nghiệp', 'Cần tìm hiểu'],
                 ['Màu sơn tường trường', 'Không cần'], ['Biển số xe hiệu trưởng', 'Không cần']],
                'Tập trung vào thông tin ảnh hưởng đến việc học.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Website trường là nguồn tin đáng tin nhất', 'Đúng'],
                 ['Nên đăng ký nhiều nguyện vọng', 'Đúng'],
                 ['Xếp nguyện vọng từ cao đến thấp', 'Đúng'],
                 ['Có nhiều phương thức xét tuyển', 'Đúng'],
                 ['Chỉ nên đăng ký 1 nguyện vọng', 'Sai'],
                 ['Không cần tìm hiểu về trường', 'Sai']],
                'Nhiều nguyện vọng tăng cơ hội trúng tuyển.');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm NÊN LÀM hoặc KHÔNG NÊN khi chọn trường.',
                [['Tham quan trường', 'Nên làm'], ['Hỏi sinh viên đang học', 'Nên làm'],
                 ['So sánh nhiều trường', 'Nên làm'],
                 ['Nghe theo tin đồn', 'Không nên'], ['Chọn vì bạn bè chọn', 'Không nên'],
                 ['Bỏ qua học phí', 'Không nên']],
                'Chọn trường cần thông tin đầy đủ, khách quan.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nguồn thông tin đáng tin nhất về trường là ___ chính thức.', [[0, 'website']],
                'Website của trường cập nhật thông tin chính xác.');
            $this->fill($L, 'Phương thức dùng điểm trung bình THPT gọi là xét học ___.', [[0, 'bạ']],
                'Xét học bạ không cần chờ điểm thi tốt nghiệp.');
            $this->fill($L, 'Nên đăng ký ___ nguyện vọng để tăng cơ hội.', [[0, 'nhiều']],
                'Nhiều nguyện vọng, nhiều cơ hội trúng tuyển.');
            $this->fill($L, 'Xếp nguyện vọng yêu thích nhất lên ___ đầu.', [[0, 'hàng']],
                'Thứ tự ưu tiên từ cao đến thấp.');
        }
    }

    private function seedTnHn123(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Một CV tốt gồm những phần nào?',
                ['Thông tin liên hệ, mục tiêu, học vấn, kinh nghiệm, kỹ năng',
                 'Chỉ họ tên', 'Chỉ ảnh cá nhân', 'Chỉ sở thích'], 0,
                'CV đầy đủ giúp nhà tuyển dụng đánh giá nhanh ứng viên.');
            $this->quiz($L, 'Nguyên tắc quan trọng nhất khi viết CV là gì?',
                ['Trung thực tuyệt đối', 'Phóng đại thành tích',
                 'Bịa thêm kinh nghiệm', 'Dùng thông tin giả'], 0,
                'Trung thực là nền tảng của mọi hồ sơ ứng tuyển.');
            $this->quiz($L, 'CV nên dài bao nhiêu?',
                ['Gọn trong 1–2 trang', 'Càng dài càng tốt, 10 trang',
                 'Chỉ 2 dòng', 'Không giới hạn'], 0,
                'CV ngắn gọn giúp người đọc nắm bắt nhanh.');
            $this->quiz($L, 'Hồ sơ ứng tuyển thường gồm những gì?',
                ['CV, thư giới thiệu, bằng cấp/chứng chỉ, ảnh', 'Chỉ cần ảnh',
                 'Chỉ cần CMND', 'Không cần gì cả'], 0,
                'Bộ hồ sơ đầy đủ thể hiện sự chuyên nghiệp.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phần của CV với nội dung của nó.',
                [['Thông tin liên hệ', 'Họ tên, điện thoại, email'],
                 ['Mục tiêu', 'Định hướng ngắn gọn'],
                 ['Học vấn', 'Trường, ngành, thành tích'],
                 ['Kinh nghiệm', 'Hoạt động, việc làm đã qua']],
                'Bốn phần cốt lõi của CV.');
            $this->matching($L, 'Nối tiếp mỗi phần với nội dung của nó.',
                [['Kỹ năng', 'Ngoại ngữ, tin học, mềm'],
                 ['Chứng chỉ', 'IELTS, tin học...'],
                 ['Sở thích', 'Điểm nhấn cá nhân (chọn lọc)'],
                 ['Người tham chiếu', 'Thầy cô, người hướng dẫn']],
                'Các phần bổ sung làm CV nổi bật.');
            $this->matching($L, 'Nối mỗi nguyên tắc với ý nghĩa của nó.',
                [['Trung thực', 'Không bịa đặt thông tin'],
                 ['Ngắn gọn', 'Gọn trong 1–2 trang'],
                 ['Không lỗi chính tả', 'Thể hiện sự cẩn thận'],
                 ['Con số cụ thể', 'Chứng minh bằng kết quả']],
                'Bốn nguyên tắc vàng khi viết CV.');
            $this->matching($L, 'Nối mỗi lỗi với hậu quả của nó.',
                [['Sai chính tả', 'Mất điểm chuyên nghiệp'],
                 ['CV quá dài', 'Người đọc bỏ qua'],
                 ['Thông tin giả', 'Mất uy tín hoàn toàn'],
                 ['Ảnh thiếu lịch sự', 'Ấn tượng xấu']],
                'Lỗi nhỏ có thể đánh mất cơ hội lớn.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nội dung vào nhóm NÊN CÓ hoặc KHÔNG NÊN trong CV.',
                [['Thông tin liên hệ', 'Nên có'], ['Học vấn', 'Nên có'], ['Kỹ năng', 'Nên có'],
                 ['Thông tin bịa đặt', 'Không nên'], ['Ảnh thiếu lịch sự', 'Không nên'],
                 ['Lỗi chính tả', 'Không nên']],
                'CV cần đầy đủ, trung thực và chỉn chu.');
            $this->sortQ($L, 'Kéo mỗi cách viết vào nhóm TỐT hoặc KÉM.',
                [['Dùng động từ hành động', 'Tốt'], ['Nêu con số cụ thể', 'Tốt'],
                 ['Ngắn gọn, rõ ràng', 'Tốt'],
                 ['Chung chung, sáo rỗng', 'Kém'], ['Dài dòng lan man', 'Kém'],
                 ['Phóng đại sự thật', 'Kém']],
                'Cách viết quyết định sức thuyết phục của CV.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['CV phải trung thực tuyệt đối', 'Đúng'],
                 ['CV nên gọn trong 1–2 trang', 'Đúng'],
                 ['Không để lỗi chính tả trong CV', 'Đúng'],
                 ['Nên dùng con số cụ thể', 'Đúng'],
                 ['Bịa thêm kinh nghiệm cho đẹp CV', 'Sai'],
                 ['CV càng dài càng ấn tượng', 'Sai']],
                'Trung thực và ngắn gọn là hai nguyên tắc cốt lõi.');
            $this->sortQ($L, 'Kéo mỗi giấy tờ vào nhóm THUỘC HỒ SƠ ỨNG TUYỂN hoặc KHÔNG THUỘC.',
                [['CV', 'Thuộc hồ sơ'], ['Thư giới thiệu', 'Thuộc hồ sơ'],
                 ['Bằng cấp, chứng chỉ', 'Thuộc hồ sơ'], ['Ảnh chân dung', 'Thuộc hồ sơ'],
                 ['Hóa đơn điện nước', 'Không thuộc'], ['Giấy khám sức khỏe cũ', 'Không thuộc']],
                'Hồ sơ ứng tuyển gồm các giấy tờ liên quan đến năng lực.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nguyên tắc quan trọng nhất khi viết CV là trung ___.', [[0, 'thực']],
                'Trung thực tuyệt đối, không bịa đặt.');
            $this->fill($L, 'CV nên gọn trong 1 – 2 ___.', [[0, 'trang']],
                'Ngắn gọn giúp người đọc nắm bắt nhanh.');
            $this->fill($L, 'Trong CV không được để lỗi chính ___.', [[0, 'tả']],
                'Lỗi chính tả thể hiện sự cẩu thả.');
            $this->fill($L, 'Nên dùng động từ hành động và con số cụ ___ trong CV.', [[0, 'thể']],
                'Con số chứng minh kết quả cụ thể.');
        }
    }

    private function seedTnHn124(): void
    {
        $L = 'trai-nghiem-huong-nghiep-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trước buổi phỏng vấn nên chuẩn bị gì?',
                ['Tìm hiểu về trường/công ty, luyện trả lời câu hỏi thường gặp',
                 'Không cần chuẩn bị gì', 'Đến muộn cho ấn tượng',
                 'Ăn mặc xuề xòa'], 0,
                'Chuẩn bị kỹ là chìa khóa của phỏng vấn thành công.');
            $this->quiz($L, 'Trong phỏng vấn nên thể hiện tác phong nào?',
                ['Đến sớm, ăn mặc lịch sự, giao tiếp bằng mắt', 'Đến muộn, ăn mặc luộm thuộm',
                 'Nhìn đi chỗ khác', 'Ngắt lời người hỏi'], 0,
                'Tác phong chuyên nghiệp tạo ấn tượng tốt đầu tiên.');
            $this->quiz($L, 'Khi trả lời câu hỏi phỏng vấn nên làm gì?',
                ['Trả lời ngắn gọn, có ví dụ cụ thể', 'Trả lời lan man không trọng tâm',
                 'Nói dối để gây ấn tượng', 'Im lặng không trả lời'], 0,
                'Câu trả lời có ví dụ cụ thể thuyết phục hơn lời nói suông.');
            $this->quiz($L, 'Sau buổi phỏng vấn nên làm gì để ghi điểm?',
                ['Gửi thư cảm ơn', 'Quên luôn buổi phỏng vấn',
                 'Gọi điện hối thúc kết quả', 'Không làm gì cả'], 0,
                'Thư cảm ơn thể hiện sự chuyên nghiệp và chân thành.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi giai đoạn phỏng vấn với việc nên làm.',
                [['Trước phỏng vấn', 'Tìm hiểu, luyện trả lời'],
                 ['Trong phỏng vấn', 'Tự tin, trung thực'],
                 ['Khi được hỏi ngược', 'Đặt câu hỏi thông minh'],
                 ['Sau phỏng vấn', 'Gửi thư cảm ơn']],
                'Bốn giai đoạn của một buổi phỏng vấn.');
            $this->matching($L, 'Nối mỗi câu hỏi thường gặp với gợi ý trả lời.',
                [['Giới thiệu bản thân', 'Ngắn gọn, nổi bật điểm mạnh'],
                 ['Điểm mạnh – điểm yếu', 'Trung thực, có hướng khắc phục'],
                 ['Vì sao chọn chúng tôi?', 'Thể hiện đã tìm hiểu kỹ'],
                 ['Mục tiêu 5 năm?', 'Rõ ràng, phù hợp']],
                'Chuẩn bị trước các câu hỏi kinh điển.');
            $this->matching($L, 'Nối mỗi hành động với đánh giá của nó.',
                [['Đến sớm 10–15 phút', 'Chuyên nghiệp'],
                 ['Giao tiếp bằng mắt', 'Tự tin'],
                 ['Đặt câu hỏi ngược', 'Chủ động, quan tâm'],
                 ['Ngắt lời', 'Thiếu lịch sự']],
                'Ngôn ngữ cơ thể nói lên rất nhiều điều.');
            $this->matching($L, 'Nối mỗi lỗi với cách tránh.',
                [['Đến muộn', 'Đi sớm, tính trước thời gian'],
                 ['Không tìm hiểu', 'Nghiên cứu trước về nơi phỏng vấn'],
                 ['Trả lời lan man', 'Luyện trả lời ngắn gọn'],
                 ['Ăn mặc xuề xòa', 'Chuẩn bị trang phục lịch sự']],
                'Tránh lỗi cơ bản để không mất điểm đáng tiếc.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm NÊN LÀM hoặc KHÔNG NÊN khi phỏng vấn.',
                [['Đến sớm', 'Nên làm'], ['Ăn mặc lịch sự', 'Nên làm'],
                 ['Giao tiếp bằng mắt', 'Nên làm'], ['Gửi thư cảm ơn sau đó', 'Nên làm'],
                 ['Đến muộn', 'Không nên'], ['Ngắt lời người hỏi', 'Không nên']],
                'Tác phong quyết định ấn tượng đầu tiên.');
            $this->sortQ($L, 'Kéo mỗi cách trả lời vào nhóm TỐT hoặc KÉM.',
                [['Ngắn gọn, có ví dụ', 'Tốt'], ['Trung thực', 'Tốt'], ['Tự tin', 'Tốt'],
                 ['Lan man', 'Kém'], ['Nói dối', 'Kém'], ['Im lặng', 'Kém']],
                'Trả lời tốt: ngắn gọn, thật, tự tin.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nên tìm hiểu trước về nơi phỏng vấn', 'Đúng'],
                 ['Nên luyện trả lời câu hỏi thường gặp', 'Đúng'],
                 ['Gửi thư cảm ơn sau phỏng vấn là điểm cộng', 'Đúng'],
                 ['Đặt câu hỏi ngược thể hiện sự quan tâm', 'Đúng'],
                 ['Đến muộn tạo ấn tượng mạnh', 'Sai'],
                 ['Không cần chuẩn bị gì', 'Sai']],
                'Chuẩn bị kỹ là tôn trọng cơ hội của mình.');
            $this->sortQ($L, 'Kéo mỗi việc vào đúng giai đoạn: TRƯỚC, TRONG hoặc SAU phỏng vấn.',
                [['Tìm hiểu về công ty', 'Trước'], ['Luyện trả lời', 'Trước'],
                 ['Trả lời tự tin', 'Trong'], ['Đặt câu hỏi ngược', 'Trong'],
                 ['Gửi thư cảm ơn', 'Sau'], ['Chờ kết quả', 'Sau']],
                'Mỗi giai đoạn có nhiệm vụ riêng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trước phỏng vấn nên tìm ___ về nơi mình ứng tuyển.', [[0, 'hiểu']],
                'Hiểu biết thể hiện sự nghiêm túc.');
            $this->fill($L, 'Trong phỏng vấn nên giao tiếp bằng ___.', [[0, 'mắt']],
                'Giao tiếp bằng mắt thể hiện sự tự tin.');
            $this->fill($L, 'Trả lời phỏng vấn nên ngắn gọn và có ví dụ cụ ___.', [[0, 'thể']],
                'Ví dụ cụ thể tăng sức thuyết phục.');
            $this->fill($L, 'Sau phỏng vấn nên gửi thư cảm ___.', [[0, 'ơn']],
                'Thư cảm ơn là điểm cộng lớn.');
        }
    }
}
