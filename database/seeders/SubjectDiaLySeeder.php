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
 * Môn Địa lý — nội dung TỰ VIẾT 100% tiếng Việt, bám chương trình lớp 6-9.
 * Idempotent: Subject/Topic/Skill/Lesson dùng updateOrCreate theo slug;
 * câu hỏi bỏ qua khi đã tồn tại theo (lesson_id, game_type).
 */
class SubjectDiaLySeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(
            ['slug' => 'dia-ly'],
            [
                'name' => 'Địa lý',
                'icon' => '🌍',
                'color' => '#0ea5e9',
                'description' => 'Khám phá châu lục, thủ đô và địa hình Việt Nam qua trò chơi.',
                'sort_order' => 7,
                'is_published' => true,
                'is_demo' => true,
            ]
        );

        $topics = [
            ['name' => 'Châu lục và đại dương', 'slug' => 'dl-chau-luc-dai-duong', 'icon' => '🌊',
             'description' => 'Các châu lục, đại dương trên Trái Đất và vị trí địa lí Việt Nam.',
             'grade_min' => 6, 'grade_max' => 7],
            ['name' => 'Thủ đô các nước', 'slug' => 'dl-thu-do-cac-nuoc', 'icon' => '🏛️',
             'description' => 'Thủ đô các nước châu Á và trên thế giới.',
             'grade_min' => 7, 'grade_max' => 8],
            ['name' => 'Địa hình và khí hậu Việt Nam', 'slug' => 'dl-dia-hinh-viet-nam', 'icon' => '⛰️',
             'description' => 'Địa hình, sông ngòi và khí hậu nhiệt đới gió mùa của Việt Nam.',
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
            'dl-chau-luc-dai-duong' => [
                ['name' => 'Châu lục và đại dương trên Trái Đất', 'slug' => 'kn-dl-chau-luc-1'],
                ['name' => 'Vị trí địa lí Việt Nam', 'slug' => 'kn-dl-vi-tri-vn-1'],
            ],
            'dl-thu-do-cac-nuoc' => [
                ['name' => 'Thủ đô các nước trên thế giới', 'slug' => 'kn-dl-thu-do-1'],
            ],
            'dl-dia-hinh-viet-nam' => [
                ['name' => 'Địa hình, sông ngòi và khí hậu', 'slug' => 'kn-dl-dia-hinh-1'],
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
            'kn-dl-chau-luc-1' => [
                [
                    'title' => 'Các châu lục trên thế giới', 'slug' => 'dl-cac-chau-luc',
                    'objective' => 'Kể được tên 7 châu lục, biết châu Á có diện tích và dân số lớn nhất.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Trái Đất có 7 châu lục: Á, Âu, Phi, Bắc Mỹ, Nam Mỹ, Đại Dương, Nam Cực. Châu Á lớn nhất cả về diện tích và dân số.',
                ],
                [
                    'title' => 'Các đại dương', 'slug' => 'dl-dai-duong',
                    'objective' => 'Kể được tên 4 đại dương, biết Thái Bình Dương lớn nhất, Bắc Băng Dương nhỏ nhất.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Có 4 đại dương: Thái Bình Dương, Đại Tây Dương, Ấn Độ Dương, Bắc Băng Dương. Biển Đông thuộc Thái Bình Dương.',
                ],
            ],
            'kn-dl-vi-tri-vn-1' => [
                [
                    'title' => 'Vị trí địa lí Việt Nam', 'slug' => 'dl-vi-tri-viet-nam',
                    'objective' => 'Xác định được vị trí, hình dạng, thủ đô và các vùng giáp ranh của Việt Nam.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Việt Nam hình chữ S, thủ đô Hà Nội, phía bắc giáp Trung Quốc, phía tây giáp Lào và Campuchia, phía đông và nam giáp Biển Đông.',
                ],
            ],
            'kn-dl-thu-do-1' => [
                [
                    'title' => 'Thủ đô các nước châu Á', 'slug' => 'dl-thu-do-chau-a',
                    'objective' => 'Nhớ được thủ đô của các nước châu Á tiêu biểu: Nhật Bản, Trung Quốc, Hàn Quốc, Thái Lan...',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Học theo nhóm: Đông Nam Á (Bangkok, Viêng Chăn, Phnom Penh, Jakarta...), Đông Á (Tokyo, Bắc Kinh, Seoul).',
                ],
                [
                    'title' => 'Thủ đô các nước trên thế giới', 'slug' => 'dl-thu-do-the-gioi',
                    'objective' => 'Nhớ được thủ đô của các nước tiêu biểu ở châu Âu, châu Mỹ, châu Phi, châu Đại Dương.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Chú ý các cặp dễ nhầm: thủ đô của Úc là Canberra (không phải Sydney), của Mỹ là Washington D.C. (không phải New York).',
                ],
            ],
            'kn-dl-dia-hinh-1' => [
                [
                    'title' => 'Địa hình và sông ngòi Việt Nam', 'slug' => 'dl-dia-hinh-song-ngoi',
                    'objective' => 'Biết dãy Hoàng Liên Sơn, đỉnh Fansipan, các đồng bằng và sông lớn của Việt Nam.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Địa hình Việt Nam 3/4 là đồi núi. Fansipan (3 143 m) là nóc nhà Đông Dương. Hai đồng bằng lớn: sông Hồng và sông Cửu Long.',
                ],
                [
                    'title' => 'Khí hậu Việt Nam', 'slug' => 'dl-khi-hau',
                    'objective' => 'Nêu được đặc điểm khí hậu nhiệt đới gió mùa và sự phân hoá theo miền.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Việt Nam có khí hậu nhiệt đới gió mùa: nóng ẩm, mưa nhiều. Miền Bắc có mùa đông lạnh, miền Nam hai mùa mưa – khô.',
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

        $this->seedCacChauLuc();
        $this->seedDaiDuong();
        $this->seedViTriVietNam();
        $this->seedThuDoChauA();
        $this->seedThuDoTheGioi();
        $this->seedDiaHinhSongNgoi();
        $this->seedKhiHau();
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

    private function seedCacChauLuc(): void
    {
        $L = 'dl-cac-chau-luc';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Châu lục nào có diện tích lớn nhất thế giới?',
                ['châu Á', 'châu Phi', 'châu Âu', 'châu Đại Dương'], 0,
                'Châu Á có diện tích lớn nhất thế giới, chiếm khoảng 1/3 diện tích đất liền.');
            $this->quiz($L, 'Châu lục nào có dân số đông nhất thế giới?',
                ['châu Á', 'châu Âu', 'châu Phi', 'châu Mỹ'], 0,
                'Châu Á cũng là châu lục đông dân nhất, chiếm hơn một nửa dân số thế giới.');
            $this->quiz($L, 'Việt Nam thuộc châu lục nào?',
                ['châu Á', 'châu Âu', 'châu Phi', 'châu Mỹ'], 0,
                'Việt Nam nằm ở khu vực Đông Nam Á, thuộc châu Á.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi châu lục với đặc điểm nổi bật của nó.',
                [['châu Á', 'Diện tích và dân số lớn nhất'],
                 ['châu Phi', 'Có hoang mạc Xa-ha-ra rộng lớn'],
                 ['châu Nam Cực', 'Lục địa lạnh nhất, quanh năm băng tuyết'],
                 ['châu Đại Dương', 'Châu lục nhỏ nhất']],
                'Châu Á lớn nhất; châu Phi có Xa-ha-ra; Nam Cực lạnh nhất; Đại Dương nhỏ nhất.');
            $this->matching($L, 'Nối mỗi quốc gia với châu lục của nó.',
                [['Việt Nam', 'châu Á'], ['Ai Cập', 'châu Phi'],
                 ['Pháp', 'châu Âu'], ['Bra-xin', 'châu Nam Mỹ']],
                'Việt Nam ở châu Á, Ai Cập ở châu Phi, Pháp ở châu Âu, Bra-xin ở châu Nam Mỹ.');
            $this->matching($L, 'Nối tên tiếng Anh với tên tiếng Việt của châu lục.',
                [['Asia', 'châu Á'], ['Africa', 'châu Phi'], ['Europe', 'châu Âu']],
                'Asia là châu Á, Africa là châu Phi, Europe là châu Âu.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi quốc gia vào nhóm THUỘC CHÂU Á hoặc KHÔNG THUỘC CHÂU Á.',
                [['Việt Nam', 'Thuộc châu Á'], ['Nhật Bản', 'Thuộc châu Á'],
                 ['Pháp', 'Không thuộc châu Á'], ['Ai Cập', 'Không thuộc châu Á']],
                'Việt Nam, Nhật Bản ở châu Á. Pháp ở châu Âu, Ai Cập ở châu Phi.');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm CHÂU LỤC hoặc ĐẠI DƯƠNG.',
                [['châu Phi', 'Châu lục'], ['châu Âu', 'Châu lục'],
                 ['Thái Bình Dương', 'Đại dương'], ['Ấn Độ Dương', 'Đại dương']],
                'Châu Phi, châu Âu là châu lục. Thái Bình Dương, Ấn Độ Dương là đại dương.');
            $this->sortQ($L, 'Kéo mỗi châu lục vào nhóm chủ yếu ở BÁN CẦU BẮC hoặc BÁN CẦU NAM.',
                [['châu Âu', 'Bán cầu Bắc'], ['châu Á', 'Bán cầu Bắc'],
                 ['châu Nam Cực', 'Bán cầu Nam'], ['châu Đại Dương', 'Bán cầu Nam']],
                'Châu Âu, châu Á nằm chủ yếu ở bán cầu Bắc; Nam Cực và Đại Dương ở bán cầu Nam.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Châu lục có diện tích lớn nhất thế giới là châu ___.', [[0, 'Á']],
                'Châu Á chiếm khoảng 1/3 diện tích đất liền của Trái Đất.');
            $this->fill($L, 'Việt Nam nằm ở khu vực Đông Nam Á, thuộc châu ___.', [[0, 'Á']],
                'Việt Nam là một quốc gia thuộc châu Á.');
            $this->fill($L, 'Châu ___ là châu lục lạnh nhất, quanh năm bao phủ bởi băng tuyết.', [[0, 'Nam Cực']],
                'Châu Nam Cực nằm quanh cực Nam, là nơi lạnh nhất Trái Đất.');
        }
    }

    private function seedDaiDuong(): void
    {
        $L = 'dl-dai-duong';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đại dương nào lớn nhất thế giới?',
                ['Thái Bình Dương', 'Đại Tây Dương', 'Ấn Độ Dương', 'Bắc Băng Dương'], 0,
                'Thái Bình Dương lớn nhất, chiếm khoảng 1/3 diện tích bề mặt Trái Đất.');
            $this->quiz($L, 'Đại dương nào nhỏ nhất thế giới?',
                ['Thái Bình Dương', 'Đại Tây Dương', 'Ấn Độ Dương', 'Bắc Băng Dương'], 3,
                'Bắc Băng Dương nằm quanh Bắc Cực, là đại dương nhỏ nhất và lạnh nhất.');
            $this->quiz($L, 'Biển Đông thuộc đại dương nào?',
                ['Thái Bình Dương', 'Đại Tây Dương', 'Ấn Độ Dương', 'Bắc Băng Dương'], 0,
                'Biển Đông là biển ven bờ ở phía tây của Thái Bình Dương.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đại dương với đặc điểm của nó.',
                [['Thái Bình Dương', 'Lớn nhất thế giới'],
                 ['Đại Tây Dương', 'Nằm giữa châu Mỹ và châu Âu – châu Phi'],
                 ['Ấn Độ Dương', 'Nằm phía nam châu Á'],
                 ['Bắc Băng Dương', 'Nhỏ nhất, quanh năm băng giá']],
                'Thái Bình Dương lớn nhất; Bắc Băng Dương nhỏ nhất và lạnh nhất.');
            $this->matching($L, 'Nối mỗi vùng biển với đại dương của nó.',
                [['Biển Đông', 'Thái Bình Dương'],
                 ['Biển Đỏ', 'Ấn Độ Dương'],
                 ['Biển Ca-ri-bê', 'Đại Tây Dương']],
                'Biển Đông thuộc Thái Bình Dương; Biển Đỏ thuộc Ấn Độ Dương; Ca-ri-bê thuộc Đại Tây Dương.');
            $this->matching($L, 'Nối mỗi địa danh biển đảo với loại hình của nó.',
                [['Hạ Long', 'Vịnh'], ['Phú Quốc', 'Đảo'], ['Hoàng Sa', 'Quần đảo']],
                'Vịnh Hạ Long, đảo Phú Quốc, quần đảo Hoàng Sa đều thuộc vùng biển Việt Nam.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm ĐẠI DƯƠNG hoặc CHÂU LỤC.',
                [['Đại Tây Dương', 'Đại dương'], ['Bắc Băng Dương', 'Đại dương'],
                 ['châu Bắc Mỹ', 'Châu lục'], ['châu Nam Cực', 'Châu lục']],
                'Đại Tây Dương, Bắc Băng Dương là đại dương; Bắc Mỹ, Nam Cực là châu lục.');
            $this->sortQ($L, 'Kéo mỗi đại dương vào nhóm ĐẠI DƯƠNG LỚN hoặc ĐẠI DƯƠNG NHỎ.',
                [['Thái Bình Dương', 'Đại dương lớn'], ['Đại Tây Dương', 'Đại dương lớn'],
                 ['Ấn Độ Dương', 'Đại dương nhỏ'], ['Bắc Băng Dương', 'Đại dương nhỏ']],
                'Thái Bình Dương và Đại Tây Dương là hai đại dương lớn nhất.');
            $this->sortQ($L, 'Kéo mỗi vùng biển vào nhóm GIÁP VIỆT NAM hoặc KHÔNG GIÁP VIỆT NAM.',
                [['Biển Đông', 'Giáp Việt Nam'], ['Vịnh Bắc Bộ', 'Giáp Việt Nam'],
                 ['Biển Đỏ', 'Không giáp Việt Nam'], ['Biển Ca-ri-bê', 'Không giáp Việt Nam']],
                'Việt Nam giáp Biển Đông với đường bờ biển dài hơn 3 260 km.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đại dương lớn nhất thế giới là ___ Bình Dương.', [[0, 'Thái']],
                'Thái Bình Dương chiếm khoảng một nửa diện tích các đại dương.');
            $this->fill($L, 'Đại dương nhỏ nhất, nằm quanh Bắc Cực là ___ Băng Dương.', [[0, 'Bắc']],
                'Bắc Băng Dương quanh năm bao phủ bởi băng.');
            $this->fill($L, 'Nước ta có đường bờ biển dài hơn 3 260 km giáp với ___ Đông.', [[0, 'Biển']],
                'Biển Đông mang lại nguồn lợi thuỷ sản và dầu khí cho Việt Nam.');
        }
    }

    private function seedViTriVietNam(): void
    {
        $L = 'dl-vi-tri-viet-nam';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thủ đô của Việt Nam là thành phố nào?',
                ['Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Huế'], 0,
                'Hà Nội là thủ đô của nước Cộng hoà xã hội chủ nghĩa Việt Nam.', 'trung_binh');
            $this->quiz($L, 'Lãnh thổ Việt Nam có hình dạng giống chữ cái nào?',
                ['Chữ S', 'Chữ U', 'Chữ V', 'Chữ O'], 0,
                'Việt Nam có hình chữ S kéo dài từ bắc xuống nam.', 'trung_binh');
            $this->quiz($L, 'Phía đông và phía nam của Việt Nam giáp với đâu?',
                ['Biển Đông', 'Lào', 'Campuchia', 'Trung Quốc'], 0,
                'Phía đông và phía nam Việt Nam giáp Biển Đông.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hướng với vùng giáp ranh của Việt Nam.',
                [['Phía bắc', 'Trung Quốc'],
                 ['Phía tây', 'Lào và Campuchia'],
                 ['Phía đông và nam', 'Biển Đông']],
                'Bắc giáp Trung Quốc, tây giáp Lào – Campuchia, đông và nam giáp Biển Đông.', 'trung_binh');
            $this->matching($L, 'Nối mỗi thành phố với vùng của nó.',
                [['Hà Nội', 'Đồng bằng sông Hồng'],
                 ['Huế', 'Miền Trung'],
                 ['Cần Thơ', 'Đồng bằng sông Cửu Long']],
                'Hà Nội ở đồng bằng sông Hồng, Huế ở miền Trung, Cần Thơ ở đồng bằng sông Cửu Long.', 'trung_binh');
            $this->matching($L, 'Nối mỗi di sản với loại hình của nó.',
                [['Vịnh Hạ Long', 'Di sản thiên nhiên'],
                 ['Phố cổ Hội An', 'Di sản văn hoá'],
                 ['Cố đô Huế', 'Di sản văn hoá']],
                'Vịnh Hạ Long là di sản thiên nhiên; Hội An và Cố đô Huế là di sản văn hoá.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi địa phương vào nhóm GIÁP BIỂN hoặc KHÔNG GIÁP BIỂN.',
                [['Đà Nẵng', 'Giáp biển'], ['Khánh Hoà', 'Giáp biển'],
                 ['Hà Nội', 'Không giáp biển'], ['Lào Cai', 'Không giáp biển']],
                'Đà Nẵng, Khánh Hoà giáp biển; Hà Nội, Lào Cai không giáp biển.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm MIỀN BẮC hoặc MIỀN NAM.',
                [['Hà Nội', 'Miền Bắc'], ['Sa Pa', 'Miền Bắc'],
                 ['TP. Hồ Chí Minh', 'Miền Nam'], ['Cần Thơ', 'Miền Nam']],
                'Hà Nội, Sa Pa ở miền Bắc; TP. Hồ Chí Minh, Cần Thơ ở miền Nam.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm TRONG NƯỚC hoặc NGOÀI NƯỚC.',
                [['Vịnh Hạ Long', 'Trong nước'], ['Sông Mê Kông (đoạn qua Việt Nam)', 'Trong nước'],
                 ['Tháp Eiffel', 'Ngoài nước'], ['Vạn Lý Trường Thành', 'Ngoài nước']],
                'Vịnh Hạ Long ở Việt Nam; tháp Eiffel ở Pháp; Vạn Lý Trường Thành ở Trung Quốc.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thủ đô của nước ta là thành phố ___ Nội.', [[0, 'Hà']],
                'Hà Nội là trung tâm chính trị, văn hoá của cả nước.', 'trung_binh');
            $this->fill($L, 'Việt Nam nằm ở phía đông bán đảo Đông Dương, thuộc khu vực Đông Nam ___.', [[0, 'Á']],
                'Đông Nam Á là tiểu vùng thuộc châu Á.', 'trung_binh');
            $this->fill($L, 'Hai quần đảo xa bờ của Việt Nam là Hoàng Sa và ___ Sa.', [[0, 'Trường']],
                'Hoàng Sa và Trường Sa là hai quần đảo thuộc chủ quyền Việt Nam.', 'trung_binh');
        }
    }

    private function seedThuDoChauA(): void
    {
        $L = 'dl-thu-do-chau-a';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thủ đô của Nhật Bản là thành phố nào?',
                ['Tokyo', 'Osaka', 'Kyoto', 'Seoul'], 0,
                'Tokyo là thủ đô của Nhật Bản.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của Trung Quốc là thành phố nào?',
                ['Bắc Kinh', 'Thượng Hải', 'Quảng Châu', 'Hồng Kông'], 0,
                'Bắc Kinh là thủ đô của Trung Quốc.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của Hàn Quốc là thành phố nào?',
                ['Seoul', 'Busan', 'Tokyo', 'Bangkok'], 0,
                'Seoul là thủ đô của Hàn Quốc.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nước Đông Nam Á với thủ đô của nó.',
                [['Việt Nam', 'Hà Nội'], ['Thái Lan', 'Bangkok'],
                 ['Lào', 'Viêng Chăn'], ['Campuchia', 'Phnom Penh']],
                'Hà Nội, Bangkok, Viêng Chăn, Phnom Penh là thủ đô bốn nước Đông Nam Á.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước Đông Á – Nam Á với thủ đô của nó.',
                [['Nhật Bản', 'Tokyo'], ['Trung Quốc', 'Bắc Kinh'],
                 ['Hàn Quốc', 'Seoul'], ['Ấn Độ', 'New Delhi']],
                'Tokyo, Bắc Kinh, Seoul, New Delhi là thủ đô các nước Đông Á và Nam Á.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước Đông Nam Á (đảo) với thủ đô của nó.',
                [['Indonesia', 'Jakarta'], ['Malaysia', 'Kuala Lumpur'],
                 ['Singapore', 'Singapore'], ['Philippines', 'Manila']],
                'Jakarta, Kuala Lumpur, Singapore, Manila là thủ đô các nước Đông Nam Á hải đảo.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc KHÔNG PHẢI THỦ ĐÔ.',
                [['Hà Nội', 'Thủ đô'], ['Tokyo', 'Thủ đô'],
                 ['TP. Hồ Chí Minh', 'Không phải thủ đô'], ['Osaka', 'Không phải thủ đô']],
                'Hà Nội, Tokyo là thủ đô. TP. Hồ Chí Minh và Osaka chỉ là thành phố lớn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm ĐÔNG NAM Á hoặc ĐÔNG Á.',
                [['Bangkok', 'Đông Nam Á'], ['Jakarta', 'Đông Nam Á'],
                 ['Tokyo', 'Đông Á'], ['Seoul', 'Đông Á']],
                'Bangkok, Jakarta thuộc Đông Nam Á; Tokyo, Seoul thuộc Đông Á.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm NƯỚC LÁNG GIỀNG hoặc NƯỚC Ở XA.',
                [['Viêng Chăn (Lào)', 'Nước láng giềng'], ['Phnom Penh (Campuchia)', 'Nước láng giềng'],
                 ['Tokyo (Nhật Bản)', 'Nước ở xa'], ['Seoul (Hàn Quốc)', 'Nước ở xa']],
                'Lào và Campuchia là hai nước láng giềng phía tây của Việt Nam.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thủ đô của Thái Lan là ___.', [[0, 'Bangkok']],
                'Bangkok là thủ đô và thành phố lớn nhất Thái Lan.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Indonesia là ___.', [[0, 'Jakarta']],
                'Jakarta nằm trên đảo Java, là thủ đô của Indonesia.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Malaysia là Kuala ___.', [[0, 'Lumpur']],
                'Kuala Lumpur là thủ đô của Malaysia.', 'trung_binh');
        }
    }

    private function seedThuDoTheGioi(): void
    {
        $L = 'dl-thu-do-the-gioi';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thủ đô của Pháp, nơi có tháp Eiffel, là thành phố nào?',
                ['Paris', 'London', 'Berlin', 'Rome'], 0,
                'Paris là thủ đô của Pháp, nổi tiếng với tháp Eiffel.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của nước Mỹ là thành phố nào?',
                ['Washington D.C.', 'New York', 'Los Angeles', 'Chicago'], 0,
                'Washington D.C. là thủ đô của Mỹ; New York chỉ là thành phố lớn nhất.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của nước Anh là thành phố nào?',
                ['London', 'Manchester', 'Paris', 'Dublin'], 0,
                'London là thủ đô của Anh.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nước châu Âu với thủ đô của nó.',
                [['Pháp', 'Paris'], ['Đức', 'Berlin'],
                 ['Ý', 'Rome'], ['Nga', 'Mát-xcơ-va']],
                'Paris, Berlin, Rome, Mát-xcơ-va là thủ đô các nước châu Âu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước châu Mỹ – châu Đại Dương với thủ đô của nó.',
                [['Mỹ', 'Washington D.C.'], ['Canada', 'Ottawa'],
                 ['Bra-xin', 'Brasilia'], ['Úc', 'Canberra']],
                'Chú ý: thủ đô Úc là Canberra (không phải Sydney).', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước châu Á – châu Phi với thủ đô của nó.',
                [['Ai Cập', 'Cairo'], ['Thổ Nhĩ Kỳ', 'Ankara'], ['Ả Rập Xê Út', 'Riyadh']],
                'Cairo, Ankara, Riyadh là thủ đô các nước Tây Á – Bắc Phi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm CHÂU ÂU hoặc CHÂU MỸ.',
                [['Paris', 'châu Âu'], ['Berlin', 'châu Âu'],
                 ['Washington D.C.', 'châu Mỹ'], ['Brasilia', 'châu Mỹ']],
                'Paris, Berlin ở châu Âu; Washington D.C., Brasilia ở châu Mỹ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc THÀNH PHỐ LỚN (không phải thủ đô).',
                [['Paris', 'Thủ đô'], ['London', 'Thủ đô'],
                 ['New York', 'Không phải thủ đô'], ['Sydney', 'Không phải thủ đô']],
                'New York không phải thủ đô Mỹ; Sydney không phải thủ đô Úc.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm CHÂU Á hoặc CHÂU ÂU.',
                [['Tokyo', 'châu Á'], ['Hà Nội', 'châu Á'],
                 ['Paris', 'châu Âu'], ['Berlin', 'châu Âu']],
                'Tokyo, Hà Nội ở châu Á; Paris, Berlin ở châu Âu.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thủ đô của Pháp, nơi có tháp Eiffel, là ___.', [[0, 'Paris']],
                'Paris là thủ đô và trung tâm văn hoá của nước Pháp.', 'trung_binh');
            $this->fill($L, 'Thủ đô của nước Nga là ___.', [[0, 'Mát-xcơ-va']],
                'Mát-xcơ-va (Moscow) là thủ đô của Liên bang Nga.', 'trung_binh');
            $this->fill($L, 'Thủ đô của nước Úc là ___.', [[0, 'Canberra']],
                'Nhiều người nhầm Sydney, nhưng thủ đô của Úc là Canberra.', 'trung_binh');
        }
    }

    private function seedDiaHinhSongNgoi(): void
    {
        $L = 'dl-dia-hinh-song-ngoi';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dãy núi nào có đỉnh Fansipan, nóc nhà Đông Dương?',
                ['Hoàng Liên Sơn', 'Trường Sơn', 'Tam Đảo', 'Bạch Mã'], 0,
                'Dãy Hoàng Liên Sơn ở Tây Bắc có đỉnh Fansipan cao 3 143 m.', 'trung_binh');
            $this->quiz($L, 'Đỉnh Fansipan cao bao nhiêu mét?',
                ['3 143 m', '2 143 m', '3 413 m', '1 343 m'], 0,
                'Fansipan cao 3 143 m, là đỉnh núi cao nhất Việt Nam.', 'trung_binh');
            $this->quiz($L, 'Đồng bằng lớn nhất Việt Nam là đồng bằng nào?',
                ['Đồng bằng sông Cửu Long', 'Đồng bằng sông Hồng', 'Duyên hải miền Trung', 'Đông Nam Bộ'], 0,
                'Đồng bằng sông Cửu Long là đồng bằng châu thổ lớn nhất nước ta.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi con sông với vùng đồng bằng nó bồi đắp.',
                [['Sông Hồng', 'Đồng bằng sông Hồng'],
                 ['Sông Mê Kông', 'Đồng bằng sông Cửu Long'],
                 ['Sông Đồng Nai', 'Đông Nam Bộ']],
                'Sông Hồng bồi đắp đồng bằng Bắc Bộ; sông Mê Kông bồi đắp đồng bằng Nam Bộ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dãy núi, cao nguyên với vùng của nó.',
                [['Hoàng Liên Sơn', 'Tây Bắc'],
                 ['Trường Sơn', 'Miền Trung'],
                 ['Tây Nguyên', 'Cao nguyên đất đỏ ba-zan']],
                'Hoàng Liên Sơn ở Tây Bắc, Trường Sơn chạy dọc miền Trung, Tây Nguyên là vùng cao nguyên.', 'trung_binh');
            $this->matching($L, 'Nối mỗi địa danh với tên gọi khác hoặc danh hiệu của nó.',
                [['Fansipan', 'Nóc nhà Đông Dương'],
                 ['Sông Hồng', 'Sông Cái'],
                 ['Sông Mê Kông', 'Cửu Long']],
                'Fansipan được mệnh danh là nóc nhà Đông Dương.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm MIỀN NÚI hoặc ĐỒNG BẰNG.',
                [['Hoàng Liên Sơn', 'Miền núi'], ['Tây Nguyên', 'Miền núi'],
                 ['Đồng bằng sông Hồng', 'Đồng bằng'], ['Đồng bằng sông Cửu Long', 'Đồng bằng']],
                '3/4 diện tích Việt Nam là đồi núi; đồng bằng tập trung ở hai châu thổ lớn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi con sông vào nhóm MIỀN BẮC hoặc MIỀN NAM.',
                [['Sông Hồng', 'Miền Bắc'], ['Sông Đà', 'Miền Bắc'],
                 ['Sông Mê Kông', 'Miền Nam'], ['Sông Đồng Nai', 'Miền Nam']],
                'Sông Hồng, sông Đà ở miền Bắc; Mê Kông, Đồng Nai ở miền Nam.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm ĐỊA HÌNH CAO hoặc ĐỊA HÌNH THẤP.',
                [['Núi Fansipan', 'Địa hình cao'], ['Cao nguyên Đắk Lắk', 'Địa hình cao'],
                 ['Đồng bằng sông Cửu Long', 'Địa hình thấp'], ['Vùng ven biển', 'Địa hình thấp']],
                'Núi và cao nguyên là địa hình cao; đồng bằng, ven biển là địa hình thấp.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đỉnh núi cao nhất Việt Nam là ___.', [[0, 'Fansipan']],
                'Fansipan cao 3 143 m, thuộc dãy Hoàng Liên Sơn.', 'trung_binh');
            $this->fill($L, 'Con sông lớn chảy qua miền Nam, đổ ra biển bằng chín cửa, là sông Mê ___.', [[0, 'Kông']],
                'Sông Mê Kông còn gọi là sông Cửu Long (chín rồng).', 'trung_binh');
            $this->fill($L, 'Đồng bằng châu thổ lớn nhất nước ta là đồng bằng sông Cửu ___.', [[0, 'Long']],
                'Đồng bằng sông Cửu Long là vựa lúa lớn nhất cả nước.', 'trung_binh');
        }
    }

    private function seedKhiHau(): void
    {
        $L = 'dl-khi-hau';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Việt Nam thuộc kiểu khí hậu nào?',
                ['Nhiệt đới gió mùa', 'Ôn đới', 'Hàn đới', 'Địa Trung Hải'], 0,
                'Việt Nam có khí hậu nhiệt đới gió mùa: nóng ẩm, mưa nhiều.', 'trung_binh');
            $this->quiz($L, 'Miền Nam Việt Nam một năm có mấy mùa rõ rệt?',
                ['2 mùa: mưa và khô', '4 mùa', '3 mùa', 'Quanh năm một mùa'], 0,
                'Miền Nam có hai mùa rõ rệt: mùa mưa và mùa khô.', 'trung_binh');
            $this->quiz($L, 'Gió mùa đông bắc thổi về miền Bắc gây ra hiện tượng gì?',
                ['Mùa đông lạnh', 'Mùa hè nóng', 'Mưa đá', 'Hạn hán'], 0,
                'Gió mùa đông bắc mang không khí lạnh khiến miền Bắc có mùa đông lạnh.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi miền với đặc điểm khí hậu của nó.',
                [['Miền Bắc', 'Có mùa đông lạnh'],
                 ['Miền Nam', 'Hai mùa mưa – khô'],
                 ['Miền Trung', 'Mưa nhiều vào mùa thu đông']],
                'Khí hậu phân hoá theo miền: Bắc có đông lạnh, Nam hai mùa, Trung mưa lệch mùa.', 'trung_binh');
            $this->matching($L, 'Nối mỗi loại gió với mùa nó thịnh hành.',
                [['Gió mùa đông bắc', 'Mùa đông'],
                 ['Gió mùa tây nam', 'Mùa hè'],
                 ['Tín phong', 'Thổi quanh năm']],
                'Gió mùa đổi hướng theo mùa: đông bắc vào mùa đông, tây nam vào mùa hè.', 'trung_binh');
            $this->matching($L, 'Nối mỗi yếu tố với giá trị đặc trưng của khí hậu Việt Nam.',
                [['Nhiệt độ trung bình năm', 'Trên 21°C'],
                 ['Lượng mưa trung bình năm', '1 500 – 2 000 mm'],
                 ['Độ ẩm không khí', 'Cao, trên 80%']],
                'Khí hậu nhiệt đới gió mùa: nóng, ẩm, mưa nhiều.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tháng vào nhóm MÙA MƯA hoặc MÙA KHÔ (ở miền Nam).',
                [['Tháng 7', 'Mùa mưa'], ['Tháng 8', 'Mùa mưa'],
                 ['Tháng 1', 'Mùa khô'], ['Tháng 3', 'Mùa khô']],
                'Miền Nam mưa nhiều từ tháng 5 đến tháng 10, khô từ tháng 11 đến tháng 4.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm KHÍ HẬU NHIỆT ĐỚI hoặc KHÔNG PHẢI.',
                [['Nóng ẩm, mưa nhiều', 'Khí hậu nhiệt đới'],
                 ['Nhiệt độ cao quanh năm', 'Khí hậu nhiệt đới'],
                 ['Quanh năm băng tuyết', 'Không phải nhiệt đới'],
                 ['Mùa đông kéo dài nửa năm', 'Không phải nhiệt đới']],
                'Nhiệt đới: nóng ẩm mưa nhiều, nhiệt độ cao quanh năm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi ảnh hưởng của khí hậu vào nhóm THUẬN LỢI hoặc KHÓ KHĂN.',
                [['Trồng lúa quanh năm', 'Thuận lợi'],
                 ['Nhiều loại cây ăn trái', 'Thuận lợi'],
                 ['Bão lũ hằng năm', 'Khó khăn'],
                 ['Hạn hán, xâm nhập mặn', 'Khó khăn']],
                'Khí hậu thuận lợi cho nông nghiệp nhưng cũng gây bão lũ, hạn hán.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Việt Nam nằm trong vùng khí hậu nhiệt đới ___ mùa.', [[0, 'gió']],
                'Gió mùa là nét đặc trưng: đổi hướng theo mùa.', 'trung_binh');
            $this->fill($L, 'Miền Nam có hai mùa rõ rệt là mùa mưa và mùa ___.', [[0, 'khô']],
                'Mùa khô miền Nam kéo dài từ khoảng tháng 11 đến tháng 4.', 'trung_binh');
            $this->fill($L, 'Vào mùa đông, gió mùa ___ bắc mang không khí lạnh về miền Bắc.', [[0, 'đông']],
                'Gió mùa đông bắc thổi từ phương bắc xuống vào mùa đông.', 'trung_binh');
        }
    }
}
