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
 * Seeder dữ liệu THPT cho 3 môn: GDCD, Tin học, Công nghệ (lớp 10, 11, 12).
 *
 * Tạo 9 topics mới (mỗi môn 3 topics, mỗi topic đúng 1 khối lớp,
 * grade_min = grade_max), 18 skills (2 skill/topic), 36 lessons
 * (2 bài/skill, slug {topic-slug}-lop-{grade}-{n} với n = 1..4 theo topic).
 * Mỗi bài: đúng 4 quiz + 4 matching + 4 sort + 4 fill (16 câu).
 * Nội dung TIẾNG VIỆT TỰ VIẾT 100%, bám chương trình THPT Việt Nam.
 *
 * Idempotent: topic/skill firstOrCreate theo slug; bài học bỏ qua khi slug
 * đã tồn tại; câu hỏi bỏ qua khi (lesson_id, game_type) đã được seed.
 */
class HighSchoolGroupGdcdTinHocCongNgheSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $gradeBySlug = [];

    /** topic_slug => [subject_slug, name, icon, grade, description, [skill_name, skill_desc]...] */
    private array $topics = [
        'gdcd-thpt-10' => ['gdcd', 'Pháp luật đại cương', '⚖️', 10,
            'Những kiến thức nền tảng về pháp luật: khái niệm, đặc trưng, bản chất, vai trò, vi phạm pháp luật và trách nhiệm pháp lí.',
            [['Khái niệm pháp luật', 'Hiểu pháp luật là gì, các đặc trưng, nguồn và hệ thống pháp luật Việt Nam.'],
             ['Vi phạm pháp luật và trách nhiệm', 'Nhận biết các loại vi phạm pháp luật và trách nhiệm pháp lí tương ứng.']]],
        'gdcd-thpt-11' => ['gdcd', 'Kinh tế và thị trường', '💰', 11,
            'Kiến thức kinh tế cơ bản: sản xuất, tiêu dùng, thị trường và cạnh tranh trong nền kinh tế thị trường.',
            [['Sản xuất và tiêu dùng', 'Các yếu tố của quá trình sản xuất và văn hoá tiêu dùng.'],
             ['Thị trường và cạnh tranh', 'Chức năng của thị trường và vai trò của cạnh tranh.']]],
        'gdcd-thpt-12' => ['gdcd', 'Quyền và nghĩa vụ công dân', '🤝', 12,
            'Các quyền cơ bản và nghĩa vụ của công dân Việt Nam theo Hiến pháp và pháp luật.',
            [['Quyền cơ bản của công dân', 'Quyền bình đẳng và các quyền tự do cơ bản của công dân.'],
             ['Nghĩa vụ công dân', 'Các nghĩa vụ của công dân: tuân thủ pháp luật, nộp thuế, bảo vệ Tổ quốc.']]],
        'tin-hoc-thpt-10' => ['tin-hoc', 'Tin học ứng dụng', '💻', 10,
            'Kỹ năng tin học ứng dụng: soạn thảo văn bản nâng cao và bảng tính điện tử.',
            [['Soạn thảo văn bản nâng cao', 'Định dạng văn bản, mục lục, bảng biểu và chèn đối tượng.'],
             ['Bảng tính điện tử', 'Các hàm cơ bản trong Excel: SUM, AVERAGE, MAX, MIN, IF, sắp xếp và lọc dữ liệu.']]],
        'tin-hoc-thpt-11' => ['tin-hoc', 'Mạng máy tính', '🌐', 11,
            'Kiến thức về mạng máy tính, Internet và an toàn thông tin trên mạng.',
            [['Khái niệm mạng máy tính', 'Mạng máy tính là gì, phân loại mạng, mô hình và thiết bị mạng.'],
             ['Internet và an toàn mạng', 'Các dịch vụ Internet cơ bản và cách bảo vệ an toàn thông tin.']]],
        'tin-hoc-thpt-12' => ['tin-hoc', 'Thuật toán và lập trình cơ bản', '🧠', 12,
            'Tư duy thuật toán và lập trình Python cơ bản: biến, câu lệnh điều kiện, vòng lặp.',
            [['Thuật toán', 'Khái niệm thuật toán, các cách mô tả và cấu trúc điều khiển cơ bản.'],
             ['Lập trình Python cơ bản', 'Lệnh print, biến, kiểu dữ liệu, if, for, while trong Python.']]],
        'cong-nghe-thpt-10' => ['cong-nghe', 'Công nghệ điện', '🔌', 10,
            'Kiến thức điện cơ bản: các đại lượng điện, mạch điện và an toàn điện trong gia đình.',
            [['Mạch điện cơ bản', 'Dòng điện, điện áp, điện trở và các cách mắc mạch điện.'],
             ['An toàn điện trong gia đình', 'Nguyên nhân tai nạn điện, cách phòng tránh và sơ cứu.']]],
        'cong-nghe-thpt-11' => ['cong-nghe', 'Vẽ kỹ thuật', '📐', 11,
            'Kiến thức vẽ kỹ thuật: đường nét, hình chiếu và cách đọc bản vẽ.',
            [['Bản vẽ kỹ thuật cơ bản', 'Khái niệm bản vẽ kỹ thuật, các loại đường nét, hình chiếu.'],
             ['Đọc bản vẽ', 'Cách đọc bản vẽ chi tiết, bản vẽ lắp và bản vẽ nhà.']]],
        'cong-nghe-thpt-12' => ['cong-nghe', 'Công nghệ và đời sống', '🏠', 12,
            'Công nghệ trong sản xuất hiện đại và trách nhiệm bảo vệ môi trường.',
            [['Công nghệ trong sản xuất', 'Cách mạng công nghiệp, tự động hoá và robot trong sản xuất.'],
             ['Bảo vệ môi trường', 'Ô nhiễm môi trường, biện pháp bảo vệ và phát triển bền vững.']]],
    ];

    public function run(): void
    {
        foreach ($this->topics as $topicSlug => [$subjectSlug, $name, $icon, $grade, $desc, $skills]) {
            $subject = Subject::where('slug', $subjectSlug)->firstOrFail();
            $maxTopicOrder = (int) Topic::where('subject_id', $subject->id)->max('sort_order');
            $topic = Topic::firstOrCreate(
                ['slug' => $topicSlug],
                [
                    'subject_id' => $subject->id,
                    'name' => $name,
                    'description' => $desc,
                    'icon' => $icon,
                    'sort_order' => $maxTopicOrder + 1,
                    'grade_min' => $grade,
                    'grade_max' => $grade,
                    'is_published' => true,
                    'is_demo' => true,
                ]
            );
            $skillModels = [];
            foreach ($skills as $i => [$skillName, $skillDesc]) {
                $skillModels[] = Skill::firstOrCreate(
                    ['slug' => "{$topicSlug}-skill-" . ($i + 1)],
                    [
                        'topic_id' => $topic->id,
                        'name' => $skillName,
                        'description' => $skillDesc,
                        'sort_order' => $i + 1,
                        'is_demo' => true,
                    ]
                );
            }
            // Mỗi skill 2 bài: skill 1 -> bài 1,2 ; skill 2 -> bài 3,4
            foreach ($skillModels as $s => $skill) {
                for ($n = 1; $n <= 2; $n++) {
                    $num = $s * 2 + $n;
                    $meta = $this->lessonMeta($topicSlug, $num);
                    $this->createLesson($skill, $topicSlug, $grade, $num, $meta);
                }
            }
        }

        // ---- GDCD ----
        $this->seedGdcd101(); $this->seedGdcd102(); $this->seedGdcd103(); $this->seedGdcd104();
        $this->seedGdcd111(); $this->seedGdcd112(); $this->seedGdcd113(); $this->seedGdcd114();
        $this->seedGdcd121(); $this->seedGdcd122(); $this->seedGdcd123(); $this->seedGdcd124();
        // ---- Tin học ----
        $this->seedTin101(); $this->seedTin102(); $this->seedTin103(); $this->seedTin104();
        $this->seedTin111(); $this->seedTin112(); $this->seedTin113(); $this->seedTin114();
        $this->seedTin121(); $this->seedTin122(); $this->seedTin123(); $this->seedTin124();
        // ---- Công nghệ ----
        $this->seedCn101(); $this->seedCn102(); $this->seedCn103(); $this->seedCn104();
        $this->seedCn111(); $this->seedCn112(); $this->seedCn113(); $this->seedCn114();
        $this->seedCn121(); $this->seedCn122(); $this->seedCn123(); $this->seedCn124();
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
     * Siêu dữ liệu 36 bài học: [$topicSlug][$num] =>
     * [title, objective, difficulty, duration, instructions].
     */
    private function lessonMeta(string $topicSlug, int $num): array
    {
        $plan = [
            'gdcd-thpt-10' => [
                1 => ['title' => 'Khái niệm và các đặc trưng cơ bản của pháp luật',
                    'objective' => 'Nêu được khái niệm pháp luật; phân biệt 3 đặc trưng cơ bản; nhận biết văn bản quy phạm pháp luật và thứ bậc hiệu lực.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Pháp luật là hệ thống quy tắc xử sự chung do Nhà nước ban hành và bảo đảm thực hiện bằng quyền lực Nhà nước. Ba đặc trưng: tính quy phạm phổ biến (áp dụng chung cho mọi người), tính xác định chặt chẽ về mặt hình thức (ghi rõ trong văn bản, công khai), tính được bảo đảm thực hiện bằng quyền lực Nhà nước (cưỡng chế thi hành). Ở Việt Nam, văn bản quy phạm pháp luật là nguồn chính; Hiến pháp có hiệu lực pháp lí cao nhất.'],
                2 => ['title' => 'Bản chất và vai trò của pháp luật',
                    'objective' => 'Phân tích được bản chất giai cấp và bản chất xã hội của pháp luật; nêu được vai trò của pháp luật đối với Nhà nước, xã hội và công dân.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Pháp luật có hai mặt bản chất: bản chất giai cấp (thể hiện ý chí của giai cấp cầm quyền được nâng lên thành luật) và bản chất xã hội (bắt nguồn từ thực tiễn đời sống xã hội, điều chỉnh các quan hệ xã hội). Vai trò: với Nhà nước là công cụ quản lí xã hội; với xã hội là giữ gìn trật tự, an toàn; với công dân là bảo vệ quyền và lợi ích hợp pháp; với kinh tế là tạo môi trường ổn định để phát triển.'],
                3 => ['title' => 'Vi phạm pháp luật và các loại vi phạm',
                    'objective' => 'Nêu được khái niệm vi phạm pháp luật; phân biệt 4 loại vi phạm: hình sự, hành chính, dân sự, kỉ luật.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Vi phạm pháp luật là hành vi trái pháp luật, có lỗi, do người có năng lực trách nhiệm pháp lí thực hiện, xâm hại các quan hệ xã hội được pháp luật bảo vệ. Bốn loại: vi phạm hình sự (tội phạm, nguy hiểm cho xã hội, bị xử lí theo Bộ luật Hình sự), vi phạm hành chính (xâm phạm quy tắc quản lí nhà nước, ví dụ vượt đèn đỏ), vi phạm dân sự (xâm phạm quan hệ tài sản, nhân thân, ví dụ vi phạm hợp đồng), vi phạm kỉ luật (vi phạm nội quy cơ quan, trường học).'],
                4 => ['title' => 'Trách nhiệm pháp lí và các loại trách nhiệm',
                    'objective' => 'Nêu được khái niệm trách nhiệm pháp lí; phân biệt trách nhiệm hình sự, hành chính, dân sự, kỉ luật; vận dụng vào tình huống.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Trách nhiệm pháp lí là nghĩa vụ mà cá nhân, tổ chức phải gánh chịu hậu quả bất lợi do hành vi vi phạm pháp luật của mình gây ra, theo quy định của pháp luật. Tương ứng 4 loại vi phạm là 4 loại trách nhiệm: hình sự (hình phạt như phạt tù, phạt tiền), hành chính (phạt tiền, tước giấy phép...), dân sự (bồi thường thiệt hại, khôi phục tình trạng ban đầu), kỉ luật (khiển trách, cảnh cáo, cách chức...). Tuổi chịu trách nhiệm hình sự: từ đủ 14 tuổi với tội rất nghiêm trọng, đặc biệt nghiêm trọng; từ đủ 16 tuổi chịu trách nhiệm về mọi tội phạm.'],
            ],
            'gdcd-thpt-11' => [
                1 => ['title' => 'Sản xuất của cải vật chất và các yếu tố sản xuất',
                    'objective' => 'Nêu được vai trò của sản xuất của cải vật chất; kể được 3 yếu tố cơ bản của quá trình sản xuất.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Sản xuất của cải vật chất là hoạt động cơ bản, quyết định sự tồn tại và phát triển của xã hội. Ba yếu tố cơ bản của quá trình sản xuất: sức lao động (năng lực lao động của con người), đối tượng lao động (những gì con người tác động vào, ví dụ đất đai, nguyên liệu) và tư liệu lao động (công cụ, máy móc, nhà xưởng — trong đó công cụ lao động là yếu tố quan trọng nhất, thể hiện trình độ phát triển).'],
                2 => ['title' => 'Tiêu dùng và văn hoá tiêu dùng',
                    'objective' => 'Hiểu tiêu dùng là gì; nêu được các nguyên tắc của văn hoá tiêu dùng và trách nhiệm của người tiêu dùng.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Tiêu dùng là việc sử dụng sản phẩm, dịch vụ để thoả mãn nhu cầu đời sống. Văn hoá tiêu dùng là cách ứng xử văn minh trong tiêu dùng: tiêu dùng hợp lí, tiết kiệm, có kế hoạch; tôn trọng quyền lợi người khác; bảo vệ môi trường (hạn chế rác thải nhựa, túi ni lông); không tiêu dùng sản phẩm gây hại sức khoẻ, vi phạm pháp luật. Người tiêu dùng có quyền được bảo vệ (Luật Bảo vệ quyền lợi người tiêu dùng) và có nghĩa vụ tìm hiểu thông tin, sử dụng đúng hướng dẫn.'],
                3 => ['title' => 'Thị trường và chức năng của thị trường',
                    'objective' => 'Nêu được khái niệm thị trường; phân tích 3 chức năng: thừa nhận, điều tiết – kích thích, thông tin.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Thị trường là nơi gặp gỡ giữa người mua và người bán, là tổng hoà các quan hệ mua bán hàng hoá, dịch vụ. Ba chức năng: (1) Thừa nhận: thị trường thừa nhận giá trị sử dụng và giá trị của hàng hoá thông qua việc mua bán; (2) Điều tiết, kích thích: điều tiết sản xuất và lưu thông, kích thích lực lượng sản xuất phát triển; (3) Thông tin: cung cấp thông tin về cung – cầu, giá cả giúp người sản xuất, kinh doanh quyết định đúng. Giá cả thị trường hình thành trên cơ sở quan hệ cung – cầu.'],
                4 => ['title' => 'Cạnh tranh trong nền kinh tế thị trường',
                    'objective' => 'Nêu được khái niệm cạnh tranh; phân tích tính hai mặt của cạnh tranh; nêu các hành vi cạnh tranh không lành mạnh bị cấm.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Cạnh tranh là sự ganh đua giữa các chủ thể kinh tế nhằm giành điều kiện thuận lợi trong sản xuất, tiêu thụ để thu lợi nhuận cao nhất. Mặt tích cực: kích thích lực lượng sản xuất phát triển, tăng năng suất, hạ giá thành, nâng cao chất lượng, đáp ứng nhu cầu người tiêu dùng. Mặt tiêu cực: có thể dẫn đến cạnh tranh không lành mạnh (hàng giả, quảng cáo sai sự thật, bán phá giá, đầu cơ), gây thiệt hại cho xã hội. Pháp luật cấm các hành vi cạnh tranh không lành mạnh để bảo vệ môi trường kinh doanh công bằng.'],
            ],
            'gdcd-thpt-12' => [
                1 => ['title' => 'Quyền bình đẳng của công dân trước pháp luật',
                    'objective' => 'Nêu được nội dung quyền bình đẳng của công dân: trước pháp luật, trong hôn nhân gia đình, trong lao động, trong kinh doanh.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Mọi công dân đều bình đẳng trước pháp luật: không ai bị phân biệt đối xử, ai vi phạm đều bị xử lí. Bình đẳng trong hôn nhân và gia đình: vợ chồng bình đẳng về quyền và nghĩa vụ. Bình đẳng trong lao động: mọi người có quyền làm việc, lựa chọn nghề nghiệp, được trả lương xứng đáng, nam nữ bình đẳng. Bình đẳng trong kinh doanh: mọi cá nhân, tổ chức có quyền tự do kinh doanh ngành nghề mà pháp luật không cấm.'],
                2 => ['title' => 'Các quyền tự do cơ bản: dân chủ, khiếu nại và tố cáo',
                    'objective' => 'Nêu được quyền bầu cử, ứng cử; phân biệt khiếu nại và tố cáo; biết cách thực hiện đúng pháp luật.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Công dân từ đủ 18 tuổi có quyền bầu cử; từ đủ 21 tuổi có quyền ứng cử vào Quốc hội, HĐND. Quyền khiếu nại: công dân khiếu nại quyết định hành chính, hành vi hành chính mà mình cho là trái pháp luật, xâm phạm quyền lợi của mình. Quyền tố cáo: công dân tố cáo hành vi vi phạm pháp luật của bất kì cá nhân, tổ chức nào gây thiệt hại cho Nhà nước, xã hội. Khi thực hiện phải trung thực, đúng trình tự, không được lợi dụng để vu khống, xúc phạm người khác.'],
                3 => ['title' => 'Nghĩa vụ tuân thủ pháp luật và nghĩa vụ nộp thuế',
                    'objective' => 'Nêu được nghĩa vụ tuân thủ pháp luật của công dân; hiểu vì sao phải nộp thuế và các loại thuế cơ bản.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Mọi công dân có nghĩa vụ tuân thủ Hiến pháp và pháp luật, tôn trọng quyền tự do của người khác. Nộp thuế là nghĩa vụ của công dân và tổ chức: thuế là nguồn thu chủ yếu của ngân sách nhà nước, dùng để xây dựng đất nước, đảm bảo quốc phòng, an sinh xã hội. Các loại thuế phổ biến: thuế thu nhập cá nhân, thuế giá trị gia tăng (VAT), thuế thu nhập doanh nghiệp, thuế tiêu thụ đặc biệt. Trốn thuế là hành vi vi phạm pháp luật.'],
                4 => ['title' => 'Nghĩa vụ bảo vệ Tổ quốc',
                    'objective' => 'Nêu được nội dung nghĩa vụ bảo vệ Tổ quốc; hiểu nghĩa vụ quân sự và các hình thức tham gia bảo vệ Tổ quốc.',
                    'difficulty' => 'kho', 'duration' => 18,
                    'instructions' => 'Bảo vệ Tổ quốc là nghĩa vụ thiêng liêng và quyền cao quý của công dân. Nội dung: trung thành với Tổ quốc; thực hiện nghĩa vụ quân sự; tham gia bảo vệ an ninh quốc gia, trật tự an toàn xã hội; tố giác hành vi xâm phạm an ninh quốc gia. Nghĩa vụ quân sự: công dân nam từ đủ 18 tuổi đến hết 25 tuổi (27 tuổi với người đã tốt nghiệp cao đẳng, đại học) trong độ tuổi gọi nhập ngũ; thực hiện 24 tháng. Có thể tham gia dân quân tự vệ, dự bị động viên.'],
            ],
            'tin-hoc-thpt-10' => [
                1 => ['title' => 'Định dạng văn bản và tạo mục lục tự động',
                    'objective' => 'Biết định dạng kí tự, đoạn văn, trang; tạo mục lục tự động bằng Heading Styles trong Word.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Định dạng kí tự: phông chữ, cỡ chữ, kiểu chữ (đậm B, nghiêng I, gạch chân U), màu chữ. Định dạng đoạn văn: căn lề (trái, phải, giữa, đều hai bên), giãn dòng, thụt đầu dòng, khoảng cách trước/sau đoạn. Định dạng trang: khổ giấy, lề, hướng giấy, đánh số trang. Mục lục tự động: áp dụng Heading 1, Heading 2 cho các tiêu đề, rồi vào References > Table of Contents để tạo; khi sửa nội dung thì Update Table để cập nhật.'],
                2 => ['title' => 'Bảng biểu và chèn đối tượng trong Word',
                    'objective' => 'Tạo và định dạng bảng; chèn hình ảnh, SmartArt, biểu đồ; tạo tiêu đề cho bảng và hình.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Tạo bảng: Insert > Table, chọn số hàng/cột; có thể thêm/xoá hàng cột, gộp/tách ô, tô màu nền, kẻ viền. Chèn hình ảnh: Insert > Pictures, chỉnh kích thước, kiểu bao quanh văn bản (In line, Square, Tight...). Chèn SmartArt để vẽ sơ đồ, Insert > Chart để vẽ biểu đồ. Đặt Caption (References > Insert Caption) cho bảng/hình để đánh số tự động "Bảng 1", "Hình 1".'],
                3 => ['title' => 'Hàm cơ bản trong Excel: SUM, AVERAGE, MAX, MIN',
                    'objective' => 'Sử dụng thành thạo các hàm SUM, AVERAGE, MAX, MIN, COUNT; hiểu địa chỉ ô và vùng dữ liệu.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Công thức trong Excel luôn bắt đầu bằng dấu =. Địa chỉ ô: cột + hàng (ví dụ B3); vùng dữ liệu: ô đầu:ô cuối (ví dụ B2:B10). Các hàm cơ bản: SUM(vùng) tính tổng, AVERAGE(vùng) tính trung bình cộng, MAX(vùng) tìm giá trị lớn nhất, MIN(vùng) tìm giá trị nhỏ nhất, COUNT(vùng) đếm ô chứa số. Ví dụ: =SUM(B2:B10) tính tổng điểm từ B2 đến B10.'],
                4 => ['title' => 'Hàm điều kiện IF và sắp xếp, lọc dữ liệu',
                    'objective' => 'Sử dụng hàm IF, COUNTIF, SUMIF; thực hiện sắp xếp và lọc dữ liệu (Filter) trong Excel.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Hàm IF kiểm tra điều kiện: =IF(điều kiện, giá trị_nếu_đúng, giá trị_nếu_sai). Ví dụ: =IF(B2>=5,"Đạt","Chưa đạt"). COUNTIF(vùng, điều kiện) đếm theo điều kiện; SUMIF(vùng_đk, điều kiện, vùng_tính) tính tổng theo điều kiện. Sắp xếp: chọn vùng dữ liệu > Data > Sort (A→Z tăng dần, Z→A giảm dần). Lọc: Data > Filter, bấm mũi tên ở tiêu đề cột để chọn giá trị cần hiển thị.'],
            ],
            'tin-hoc-thpt-11' => [
                1 => ['title' => 'Mạng máy tính và phân loại mạng',
                    'objective' => 'Nêu được khái niệm mạng máy tính; phân biệt LAN, WAN, MAN; biết các kiểu kết nối cơ bản.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Mạng máy tính là tập hợp các máy tính được kết nối với nhau để chia sẻ tài nguyên (dữ liệu, máy in, kết nối Internet...). Phân loại theo phạm vi: LAN (mạng cục bộ: trong một toà nhà, trường học), MAN (mạng đô thị: trong một thành phố), WAN (mạng diện rộng: giữa các tỉnh, quốc gia; Internet là WAN lớn nhất). Phân loại theo kiểu kết nối: mạng có dây (cáp) và mạng không dây (Wi-Fi). Lợi ích: chia sẻ tài nguyên, trao đổi thông tin nhanh, tiết kiệm chi phí.'],
                2 => ['title' => 'Mô hình mạng và thiết bị mạng',
                    'objective' => 'Phân biệt mô hình khách – chủ và mạng ngang hàng; kể tên và chức năng các thiết bị mạng cơ bản.',
                    'difficulty' => 'trung_binh', 'duration' => 18,
                    'instructions' => 'Mô hình khách – chủ (client – server): có máy chủ (server) cung cấp tài nguyên, dịch vụ; các máy khách (client) sử dụng. Mạng ngang hàng (peer-to-peer): các máy bình đẳng, vừa dùng vừa chia sẻ tài nguyên. Thiết bị mạng: card mạng NIC (gắn trong máy để kết nối), hub/switch (nối nhiều máy trong LAN; switch thông minh hơn hub), router (định tuyến, nối các mạng với nhau, phát Wi-Fi), modem (chuyển đổi tín hiệu để kết nối Internet), cáp mạng và access point.'],
                3 => ['title' => 'Internet và các dịch vụ cơ bản',
                    'objective' => 'Hiểu Internet là gì; kể được các dịch vụ: Web, email, tìm kiếm, lưu trữ đám mây; hiểu địa chỉ IP và tên miền.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Internet là mạng máy tính toàn cầu, kết nối hàng tỉ thiết bị. Mỗi thiết bị có địa chỉ IP (ví dụ 192.168.1.1); tên miền (ví dụ google.com) giúp dễ nhớ, được DNS dịch thành IP. Các dịch vụ: World Wide Web (truy cập trang web qua trình duyệt), thư điện tử email, tìm kiếm thông tin, mạng xã hội, lưu trữ đám mây (Google Drive, OneDrive), học trực tuyến, thương mại điện tử. URL là địa chỉ của một trang web, bắt đầu bằng http:// hoặc https:// (bảo mật hơn).'],
                4 => ['title' => 'An toàn thông tin và phòng chống mã độc',
                    'objective' => 'Nhận biết các nguy cơ mất an toàn thông tin; biết cách phòng chống virus, bảo vệ tài khoản cá nhân.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Nguy cơ: virus, worm, trojan (mã độc phá hoại, đánh cắp dữ liệu), phishing (giả mạo để lừa mật khẩu), ransomware (mã hoá dữ liệu đòi tiền chuộc). Phòng chống: cài phần mềm diệt virus có bản quyền và cập nhật thường xuyên; không mở tệp đính kèm, liên kết lạ; đặt mật khẩu mạnh (dài, có chữ hoa, chữ thường, số, kí tự đặc biệt), bật xác thực hai yếu tố; sao lưu dữ liệu quan trọng; cập nhật hệ điều hành; không dùng Wi-Fi công cộng cho giao dịch quan trọng.'],
            ],
            'tin-hoc-thpt-12' => [
                1 => ['title' => 'Khái niệm thuật toán và các cách mô tả',
                    'objective' => 'Nêu được khái niệm thuật toán; mô tả được thuật toán bằng liệt kê các bước và sơ đồ khối.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Thuật toán là dãy hữu hạn các thao tác được sắp xếp theo trình tự xác định để giải một bài toán. Tính chất: tính dừng (phải kết thúc sau hữu hạn bước), tính xác định (mỗi bước rõ ràng, không mơ hồ), tính đúng đắn (cho kết quả đúng). Hai cách mô tả phổ biến: liệt kê từng bước bằng ngôn ngữ tự nhiên, và sơ đồ khối (hình ô van: bắt đầu/kết thúc; hình chữ nhật: tính toán; hình thoi: điều kiện rẽ nhánh; mũi tên: hướng thực hiện).'],
                2 => ['title' => 'Cấu trúc rẽ nhánh và lặp trong thuật toán',
                    'objective' => 'Phân biệt cấu trúc tuần tự, rẽ nhánh, lặp; vẽ được sơ đồ khối cho bài toán đơn giản có rẽ nhánh và lặp.',
                    'difficulty' => 'kho', 'duration' => 18,
                    'instructions' => 'Ba cấu trúc điều khiển cơ bản: tuần tự (thực hiện các bước theo thứ tự từ trên xuống), rẽ nhánh (nếu điều kiện đúng thì làm việc A, ngược lại làm việc B — dạng đầy đủ; hoặc dạng thiếu: chỉ làm việc A khi điều kiện đúng), lặp (lặp lại một khối thao tác khi điều kiện còn đúng — lặp với số lần biết trước như for, hoặc lặp với điều kiện như while). Mọi thuật toán đều có thể xây dựng từ ba cấu trúc này.'],
                3 => ['title' => 'Lệnh print, biến và kiểu dữ liệu trong Python',
                    'objective' => 'Viết được chương trình Python đầu tiên với print; khai báo và sử dụng biến; phân biệt các kiểu dữ liệu cơ bản.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Python không cần khai báo kiểu biến trước, chỉ cần gán giá trị: ten = "An". Lệnh print() in ra màn hình: print("Xin chào"). Các kiểu dữ liệu cơ bản: int (số nguyên: 5, -3), float (số thực: 3.5), str (chuỗi kí tự trong ngoặc kép: "hello"), bool (True/False). Nhập dữ liệu từ bàn phím: input() luôn trả về chuỗi, muốn tính toán phải đổi kiểu: tuoi = int(input("Nhập tuổi: ")). Chú thích bằng dấu #.'],
                4 => ['title' => 'Vòng lặp for, while và câu lệnh if trong Python',
                    'objective' => 'Sử dụng được if/elif/else, vòng lặp for và while để giải bài toán đơn giản.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Câu lệnh if rẽ nhánh: if điều_kiện: (khối lệnh thụt đầu dòng); thêm elif và else cho nhiều nhánh. Vòng lặp for duyệt dãy số: for i in range(5): chạy i từ 0 đến 4; range(1, 11) từ 1 đến 10. Vòng lặp while lặp khi điều kiện còn đúng: while n > 0: ... Chú ý thụt đầu dòng (thường 4 dấu cách) để xác định khối lệnh; lệnh break thoát vòng lặp, continue bỏ qua lần lặp hiện tại.'],
            ],
            'cong-nghe-thpt-10' => [
                1 => ['title' => 'Dòng điện, điện áp và các đại lượng điện',
                    'objective' => 'Nêu được khái niệm dòng điện, điện áp, điện trở, công suất; biết đơn vị đo và dụng cụ đo.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Dòng điện là dòng chuyển dời có hướng của các hạt mang điện; cường độ dòng điện (I) đo bằng ampe (A), đo bằng ampe kế mắc nối tiếp. Điện áp (U) là hiệu điện thế giữa hai điểm, đo bằng vôn (V), đo bằng vôn kế mắc song song. Điện trở (R) cản trở dòng điện, đo bằng ôm (Ω). Định luật Ôm: I = U / R. Công suất điện P = U × I, đo bằng oát (W); điện năng tiêu thụ A = P × t, đo bằng kWh (số điện).'],
                2 => ['title' => 'Mạch điện một chiều: mắc nối tiếp và song song',
                    'objective' => 'Phân biệt cách mắc nối tiếp và song song; tính được điện trở tương đương, dòng điện, điện áp trong mạch đơn giản.',
                    'difficulty' => 'kho', 'duration' => 18,
                    'instructions' => 'Mắc nối tiếp: các thiết bị nối đuôi nhau thành một vòng kín; dòng điện qua các thiết bị bằng nhau (I = I1 = I2), điện áp toàn mạch bằng tổng điện áp từng phần (U = U1 + U2), điện trở tương đương R = R1 + R2. Mắc song song: các thiết bị nối chung hai đầu; điện áp trên các nhánh bằng nhau (U = U1 = U2), dòng điện toàn mạch bằng tổng dòng các nhánh (I = I1 + I2), 1/R = 1/R1 + 1/R2. Trong gia đình, các thiết bị mắc song song để mỗi thiết bị được điện áp 220V.'],
                3 => ['title' => 'Nguyên nhân tai nạn điện và cách phòng tránh',
                    'objective' => 'Kể được các nguyên nhân gây tai nạn điện; nêu các biện pháp phòng tránh trong gia đình.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Nguyên nhân tai nạn điện: chạm trực tiếp vào vật mang điện (ổ cắm hở, dây trần), chạm gián tiếp qua vật dẫn bị nhiễm điện (vỏ máy hỏng cách điện), vi phạm khoảng cách an toàn với lưới điện cao áp, sét đánh, dùng thiết bị điện kém chất lượng. Phòng tránh: không chạm tay ướt vào thiết bị điện; ngắt điện trước khi sửa chữa; dùng aptomat, cầu dao chống rò; nối đất cho thiết bị vỏ kim loại; giữ khoảng cách an toàn với đường dây cao áp; kiểm tra định kì hệ thống điện.'],
                4 => ['title' => 'Sơ cứu người bị điện giật',
                    'objective' => 'Nêu đúng trình tự các bước sơ cứu người bị điện giật; biết cách hô hấp nhân tạo và ép tim ngoài lồng ngực.',
                    'difficulty' => 'kho', 'duration' => 18,
                    'instructions' => 'Trình tự sơ cứu: (1) Nhanh chóng tách nạn nhân khỏi nguồn điện bằng vật cách điện (gậy gỗ khô, vải khô) — tuyệt đối không chạm tay trần vào nạn nhân khi chưa ngắt điện; (2) Ngắt nguồn điện (cầu dao, aptomat); (3) Đặt nạn nhân nằm nơi thoáng, nới lỏng quần áo, kiểm tra hô hấp; (4) Nếu ngừng thở: hô hấp nhân tạo (thổi ngạt) kết hợp ép tim ngoài lồng ngực (100–120 lần/phút); (5) Gọi cấp cứu 115 và đưa đến cơ sở y tế gần nhất. Sơ cứu càng sớm, cơ hội cứu sống càng cao.'],
            ],
            'cong-nghe-thpt-11' => [
                1 => ['title' => 'Khái niệm bản vẽ kỹ thuật và các loại đường nét',
                    'objective' => 'Nêu được vai trò của bản vẽ kỹ thuật; nhận biết và vẽ đúng các loại đường nét cơ bản.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Bản vẽ kỹ thuật là ngôn ngữ chung của ngành kỹ thuật, dùng để diễn đạt hình dạng, kích thước, vật liệu của sản phẩm. Các loại đường nét: nét liền đậm (cạnh thấy, đường bao thấy), nét liền mảnh (đường kích thước, đường gióng, đường gạch gạch), nét đứt (cạnh khuất), nét chấm gạch mảnh (đường tâm, trục đối xứng), nét lượn sóng (đường giới hạn một phần hình cắt). Khổ giấy tiêu chuẩn: A0, A1, A2, A3, A4; khung tên ghi thông tin bản vẽ.'],
                2 => ['title' => 'Hình chiếu và hình chiếu trục đo',
                    'objective' => 'Hiểu phép chiếu vuông góc; xác định được hình chiếu đứng, bằng, cạnh; nhận biết hình chiếu trục đo.',
                    'difficulty' => 'kho', 'duration' => 18,
                    'instructions' => 'Phép chiếu vuông góc: chiếu vật thể lên mặt phẳng bằng các tia chiếu vuông góc với mặt phẳng chiếu. Ba hình chiếu cơ bản: hình chiếu đứng (nhìn từ trước), hình chiếu bằng (nhìn từ trên), hình chiếu cạnh (nhìn từ trái). Vị trí: hình chiếu bằng đặt dưới hình chiếu đứng, hình chiếu cạnh đặt bên phải hình chiếu đứng, các hình chiếu liên hệ với nhau theo quy tắc (ngang bằng, dọc thẳng). Hình chiếu trục đo (ví dụ trục đo vuông góc đều) thể hiện cả ba chiều trong một hình, giúp hình dung vật thể dễ dàng.'],
                3 => ['title' => 'Đọc bản vẽ chi tiết máy đơn giản',
                    'objective' => 'Đọc được khung tên, hình biểu diễn, kích thước, kí hiệu trên bản vẽ chi tiết đơn giản.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Trình tự đọc bản vẽ chi tiết: (1) Đọc khung tên: tên chi tiết, vật liệu, tỉ lệ, người vẽ; (2) Phân tích hình biểu diễn: có mấy hình chiếu, mặt cắt nào; (3) Đọc kích thước: kích thước định hình (dài, rộng, cao), kích thước định vị trí tương đối; (4) Đọc yêu cầu kỹ thuật: độ nhám bề mặt, dung sai, nhiệt luyện. Ví dụ: chi tiết dạng hộp chữ nhật 60×40×20 mm, vật liệu thép CT3, có 2 lỗ khoan đường kính 10 mm.'],
                4 => ['title' => 'Đọc bản vẽ lắp và bản vẽ nhà',
                    'objective' => 'Phân biệt bản vẽ chi tiết, bản vẽ lắp, bản vẽ nhà; đọc được thông tin cơ bản trên bản vẽ lắp và bản vẽ nhà.',
                    'difficulty' => 'kho', 'duration' => 20,
                    'instructions' => 'Bản vẽ lắp diễn tả hình dạng, kết cấu và vị trí tương đối của các chi tiết trong một sản phẩm, kèm bảng kê (số thứ tự, tên gọi, số lượng, vật liệu từng chi tiết). Đọc bản vẽ lắp: xem hình biểu diễn chung, đối chiếu số thứ tự với bảng kê, hiểu nguyên lí làm việc. Bản vẽ nhà gồm: mặt bằng (bố trí các phòng nhìn từ trên), mặt đứng (hình dáng bên ngoài), mặt cắt (cấu tạo bên trong). Kí hiệu thường gặp: cửa đi, cửa sổ, cầu thang, hướng bắc, cốt cao độ.'],
            ],
            'cong-nghe-thpt-12' => [
                1 => ['title' => 'Cách mạng công nghiệp và công nghệ trong sản xuất',
                    'objective' => 'Kể được 4 cuộc cách mạng công nghiệp; nêu vai trò của công nghệ trong sản xuất hiện đại.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Bốn cuộc cách mạng công nghiệp: lần 1 (cuối thế kỉ 18): cơ khí hoá với máy hơi nước; lần 2 (cuối thế kỉ 19): điện khí hoá, sản xuất hàng loạt; lần 3 (giữa thế kỉ 20): tự động hoá với máy tính, công nghệ thông tin; lần 4 (hiện nay): số hoá, trí tuệ nhân tạo, Internet vạn vật (IoT), dữ liệu lớn. Công nghệ giúp tăng năng suất, nâng cao chất lượng, giảm chi phí, tiết kiệm tài nguyên và bảo vệ môi trường.'],
                2 => ['title' => 'Tự động hoá và robot trong sản xuất',
                    'objective' => 'Hiểu khái niệm tự động hoá; kể được ứng dụng của robot trong sản xuất và đời sống.',
                    'difficulty' => 'kho', 'duration' => 18,
                    'instructions' => 'Tự động hoá là ứng dụng kỹ thuật điều khiển để máy móc tự vận hành, giảm sự can thiệp của con người. Dây chuyền tự động: cảm biến thu thập thông tin, bộ điều khiển (PLC, máy tính) xử lí, cơ cấu chấp hành thực hiện. Robot công nghiệp: tay máy lắp ráp ô tô, robot hàn, robot sơn, robot đóng gói; robot phục vụ: giao hàng, y tế, gia đình. Ưu điểm: chính xác, nhanh, làm việc liên tục, thay con người trong môi trường nguy hiểm. Thách thức: chi phí đầu tư cao, đòi hỏi nhân lực trình độ cao.'],
                3 => ['title' => 'Ô nhiễm môi trường và nguyên nhân',
                    'objective' => 'Kể được các loại ô nhiễm môi trường; phân tích nguyên nhân từ hoạt động sản xuất và sinh hoạt.',
                    'difficulty' => 'trung_binh', 'duration' => 15,
                    'instructions' => 'Các loại ô nhiễm: ô nhiễm không khí (khí thải công nghiệp, giao thông: CO, SO₂, bụi mịn), ô nhiễm nước (nước thải chưa xử lí, hoá chất nông nghiệp), ô nhiễm đất (rác thải, thuốc trừ sâu, kim loại nặng), ô nhiễm tiếng ồn và ô nhiễm ánh sáng. Nguyên nhân: sản xuất công nghiệp xả thải vượt chuẩn, giao thông, nông nghiệp lạm dụng hoá chất, sinh hoạt (rác thải nhựa, túi ni lông), khai thác tài nguyên bừa bãi. Hậu quả: ảnh hưởng sức khoẻ, biến đổi khí hậu, suy giảm đa dạng sinh học.'],
                4 => ['title' => 'Biện pháp bảo vệ môi trường và phát triển bền vững',
                    'objective' => 'Nêu được các biện pháp bảo vệ môi trường; hiểu khái niệm phát triển bền vững và trách nhiệm của bản thân.',
                    'difficulty' => 'kho', 'duration' => 18,
                    'instructions' => 'Biện pháp: xử lí chất thải trước khi thải ra môi trường (khí thải, nước thải); áp dụng công nghệ sạch, tiết kiệm năng lượng; phân loại rác tại nguồn và tái chế; trồng cây xanh; sử dụng năng lượng tái tạo (mặt trời, gió). Phát triển bền vững là phát triển đáp ứng nhu cầu hiện tại mà không làm tổn hại khả năng đáp ứng nhu cầu của thế hệ tương lai (kinh tế – xã hội – môi trường hài hoà). Mỗi học sinh có thể: tiết kiệm điện nước, hạn chế nhựa dùng một lần, tham gia trồng cây, tuyên truyền bảo vệ môi trường.'],
            ],
        ];

        return $plan[$topicSlug][$num];
    }

    // ================= GDCD LỚP 10 =================

    private function seedGdcd101(): void
    {
        $L = 'gdcd-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Pháp luật là hệ thống các quy tắc xử sự như thế nào?',
                ['Do Nhà nước ban hành và bảo đảm thực hiện', 'Do mỗi cá nhân tự đặt ra theo ý thích',
                 'Do phong tục tập quán hình thành tự phát', 'Do tổ chức xã hội tự nguyện đề ra'], 0,
                'Pháp luật là hệ thống quy tắc xử sự chung do Nhà nước ban hành, có tính bắt buộc và được bảo đảm thực hiện bằng quyền lực Nhà nước.');
            $this->quiz($L, 'Đặc trưng nào sau đây là đặc trưng cơ bản của pháp luật?',
                ['Tính quy phạm phổ biến', 'Tính tự nguyện', 'Tính linh hoạt theo sở thích cá nhân', 'Tính khuyến khích'], 0,
                'Ba đặc trưng cơ bản: tính quy phạm phổ biến, tính xác định chặt chẽ về mặt hình thức, tính được bảo đảm thực hiện bằng quyền lực Nhà nước.');
            $this->quiz($L, 'Pháp luật được bảo đảm thực hiện chủ yếu bằng cách nào?',
                ['Bằng quyền lực Nhà nước', 'Bằng sự tự giác của mọi người', 'Bằng dư luận xã hội', 'Bằng phong tục tập quán'], 0,
                'Pháp luật được bảo đảm thực hiện bằng các biện pháp cưỡng chế của Nhà nước như xử phạt hành chính, truy tố, xét xử.');
            $this->quiz($L, 'Nguồn chính của pháp luật trong hệ thống pháp luật Việt Nam là gì?',
                ['Văn bản quy phạm pháp luật', 'Án lệ', 'Tập quán pháp', 'Học thuyết pháp lí'], 0,
                'Ở Việt Nam, văn bản quy phạm pháp luật (Hiến pháp, luật, nghị định...) là nguồn chính, quan trọng nhất của pháp luật.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đặc trưng của pháp luật với nội dung tương ứng.',
                [['Tính quy phạm phổ biến', 'Áp dụng chung cho mọi người trong xã hội'],
                 ['Tính xác định chặt chẽ về hình thức', 'Được quy định rõ trong văn bản, công khai'],
                 ['Bảo đảm bằng quyền lực Nhà nước', 'Nhà nước có biện pháp cưỡng chế thi hành'],
                 ['Tính linh hoạt theo ý thích', 'Không phải đặc trưng của pháp luật']],
                'Ba đặc trưng cơ bản của pháp luật là quy phạm phổ biến, xác định chặt chẽ về hình thức và được bảo đảm bằng quyền lực Nhà nước.');
            $this->matching($L, 'Nối mỗi loại văn bản với cơ quan ban hành.',
                [['Hiến pháp, Bộ luật, Luật', 'Quốc hội ban hành'],
                 ['Nghị định', 'Chính phủ ban hành'],
                 ['Thông tư', 'Bộ trưởng ban hành'],
                 ['Quyết định, chỉ thị của UBND', 'Ủy ban nhân dân ban hành']],
                'Thẩm quyền ban hành văn bản tương ứng với vị trí của cơ quan trong bộ máy nhà nước.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa của nó.',
                [['Quy phạm pháp luật', 'Quy tắc xử sự chung do Nhà nước đặt ra'],
                 ['Quan hệ pháp luật', 'Quan hệ xã hội được pháp luật điều chỉnh'],
                 ['Chế tài', 'Biện pháp cưỡng chế áp dụng khi vi phạm'],
                 ['Chủ thể pháp luật', 'Cá nhân, tổ chức tham gia quan hệ pháp luật']],
                'Nắm vững các khái niệm cơ bản giúp hiểu đúng cách pháp luật vận hành.', 'trung_binh');
            $this->matching($L, 'Nối mỗi văn bản với hiệu lực pháp lí của nó.',
                [['Hiến pháp', 'Văn bản có hiệu lực pháp lí cao nhất'],
                 ['Luật, Bộ luật', 'Do Quốc hội ban hành, dưới Hiến pháp'],
                 ['Nghị định', 'Văn bản dưới luật, hướng dẫn thi hành luật'],
                 ['Quyết định hành chính cá biệt', 'Áp dụng cho trường hợp cụ thể']],
                'Thứ bậc hiệu lực: Hiến pháp > Luật > văn bản dưới luật; văn bản dưới không được trái văn bản trên.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm HỢP PHÁP hoặc VI PHẠM PHÁP LUẬT.',
                [['Chấp hành đèn tín hiệu giao thông', 'Hợp pháp'], ['Đội mũ bảo hiểm khi đi xe máy', 'Hợp pháp'],
                 ['Đóng thuế đúng hạn', 'Hợp pháp'], ['Vượt đèn đỏ', 'Vi phạm pháp luật'],
                 ['Lái xe khi đã uống rượu bia', 'Vi phạm pháp luật'], ['Trốn thuế', 'Vi phạm pháp luật']],
                'Hành vi hợp pháp là hành vi phù hợp với quy định của pháp luật; ngược lại là vi phạm pháp luật.');
            $this->sortQ($L, 'Kéo mỗi văn bản vào nhóm VĂN BẢN LUẬT hoặc VĂN BẢN DƯỚI LUẬT.',
                [['Bộ luật Dân sự', 'Văn bản luật'], ['Luật Giao thông đường bộ', 'Văn bản luật'],
                 ['Hiến pháp', 'Văn bản luật'], ['Nghị định của Chính phủ', 'Văn bản dưới luật'],
                 ['Thông tư của Bộ', 'Văn bản dưới luật'], ['Quyết định của UBND tỉnh', 'Văn bản dưới luật']],
                'Văn bản luật do Quốc hội ban hành; văn bản dưới luật do Chính phủ, Bộ, UBND... ban hành để hướng dẫn thi hành.');
            $this->sortQ($L, 'Kéo mỗi nội dung vào nhóm ĐẶC TRƯNG CỦA PHÁP LUẬT hoặc KHÔNG PHẢI.',
                [['Tính quy phạm phổ biến', 'Đặc trưng của pháp luật'],
                 ['Tính xác định chặt chẽ về hình thức', 'Đặc trưng của pháp luật'],
                 ['Được bảo đảm bằng quyền lực Nhà nước', 'Đặc trưng của pháp luật'],
                 ['Chỉ áp dụng cho một số người', 'Không phải'], ['Tự nguyện tuân theo hay không', 'Không phải'],
                 ['Thay đổi theo ý thích cá nhân', 'Không phải']],
                'Pháp luật áp dụng chung cho mọi người, có tính bắt buộc chứ không tự nguyện.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Pháp luật do Nhà nước ban hành', 'Đúng'], ['Mọi người đều phải tuân thủ pháp luật', 'Đúng'],
                 ['Hiến pháp có hiệu lực pháp lí cao nhất', 'Đúng'], ['Pháp luật chỉ áp dụng cho người lớn', 'Sai'],
                 ['Vi phạm pháp luật không bị xử lí', 'Sai'], ['Tập quán là nguồn chính của pháp luật Việt Nam', 'Sai']],
                'Pháp luật áp dụng với mọi người; nguồn chính ở Việt Nam là văn bản quy phạm pháp luật.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Pháp luật là hệ thống các ___ xử sự chung do Nhà nước ban hành.', [[0, 'quy tắc']],
                'Pháp luật gồm các quy tắc xử sự chung, áp dụng cho mọi người trong xã hội.');
            $this->fill($L, 'Pháp luật được Nhà nước bảo đảm thực hiện bằng ___ lực Nhà nước.', [[0, 'quyền']],
                'Tính cưỡng chế là đặc trưng quan trọng, phân biệt pháp luật với đạo đức, phong tục.');
            $this->fill($L, 'Văn bản có hiệu lực pháp lí cao nhất ở Việt Nam là ___ pháp.', [[0, 'Hiến']],
                'Hiến pháp là đạo luật gốc; mọi văn bản khác không được trái với Hiến pháp.');
            $this->fill($L, 'Tính quy phạm ___ biến là đặc trưng thể hiện pháp luật áp dụng chung cho mọi người.', [[0, 'phổ']],
                'Tính quy phạm phổ biến: khuôn mẫu chung cho hành vi của mọi cá nhân, tổ chức.');
        }
    }

    private function seedGdcd102(): void
    {
        $L = 'gdcd-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bản chất giai cấp của pháp luật thể hiện ở điểm nào?',
                ['Pháp luật thể hiện ý chí của giai cấp cầm quyền', 'Pháp luật thể hiện ý chí của mọi cá nhân',
                 'Pháp luật không liên quan đến giai cấp', 'Pháp luật chỉ bảo vệ người nghèo'], 0,
                'Pháp luật là ý chí của giai cấp cầm quyền được nâng lên thành luật, thể hiện qua các quy định bảo vệ lợi ích của giai cấp đó.');
            $this->quiz($L, 'Bản chất xã hội của pháp luật thể hiện ở điểm nào?',
                ['Pháp luật bắt nguồn từ thực tiễn đời sống xã hội', 'Pháp luật chỉ phục vụ một nhóm người',
                 'Pháp luật xa rời thực tế đời sống', 'Pháp luật do cá nhân tuỳ ý đặt ra'], 0,
                'Các quy phạm pháp luật bắt nguồn từ thực tiễn đời sống xã hội, nhằm điều chỉnh các quan hệ xã hội cho ổn định, phát triển.');
            $this->quiz($L, 'Vai trò của pháp luật đối với Nhà nước là gì?',
                ['Là công cụ quản lí Nhà nước hữu hiệu', 'Thay thế hoàn toàn đạo đức xã hội',
                 'Làm suy yếu quyền lực Nhà nước', 'Chỉ dùng để trừng phạt công dân'], 0,
                'Pháp luật là phương tiện để Nhà nước quản lí mọi mặt đời sống xã hội một cách thống nhất, hiệu quả.', 'trung_binh');
            $this->quiz($L, 'Pháp luật có vai trò gì đối với công dân?',
                ['Bảo vệ quyền và lợi ích hợp pháp của công dân', 'Hạn chế mọi quyền tự do của công dân',
                 'Buộc công dân tuân theo ý chí cá nhân', 'Không liên quan đến đời sống công dân'], 0,
                'Pháp luật ghi nhận và bảo vệ các quyền cơ bản của công dân, đồng thời quy định nghĩa vụ công dân phải thực hiện.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi mặt bản chất của pháp luật với nội dung tương ứng.',
                [['Bản chất giai cấp', 'Thể hiện ý chí của giai cấp cầm quyền được nâng lên thành luật'],
                 ['Bản chất xã hội', 'Bắt nguồn từ thực tiễn đời sống xã hội'],
                 ['Tính giai cấp', 'Pháp luật phục vụ lợi ích của giai cấp thống trị'],
                 ['Tính xã hội', 'Pháp luật điều chỉnh các quan hệ xã hội chung']],
                'Pháp luật vừa mang tính giai cấp vừa mang tính xã hội; hai mặt thống nhất với nhau.');
            $this->matching($L, 'Nối mỗi vai trò của pháp luật với đối tượng tác động.',
                [['Đối với Nhà nước', 'Công cụ quản lí mọi mặt đời sống xã hội'],
                 ['Đối với xã hội', 'Giữ gìn trật tự, an toàn xã hội'],
                 ['Đối với công dân', 'Bảo vệ quyền và lợi ích hợp pháp'],
                 ['Đối với kinh tế', 'Tạo môi trường ổn định để phát triển']],
                'Pháp luật có vai trò to lớn đối với Nhà nước, xã hội, công dân và phát triển kinh tế.', 'trung_binh');
            $this->matching($L, 'Nối mỗi chức năng của pháp luật với ví dụ minh hoạ.',
                [['Chức năng điều chỉnh', 'Quy định độ tuổi kết hôn'],
                 ['Chức năng bảo vệ', 'Trừng trị tội phạm trộm cắp'],
                 ['Chức năng giáo dục', 'Tuyên truyền pháp luật trong trường học'],
                 ['Chức năng tổ chức', 'Quy định cơ cấu bộ máy Nhà nước']],
                'Các chức năng của pháp luật thể hiện qua việc điều chỉnh hành vi, bảo vệ các quan hệ xã hội và giáo dục ý thức công dân.');
            $this->matching($L, 'Nối mỗi nhận định với đánh giá ĐÚNG hoặc SAI về bản chất pháp luật.',
                [['Pháp luật thể hiện ý chí giai cấp cầm quyền', 'Đúng'],
                 ['Pháp luật là công cụ quản lí của Nhà nước', 'Đúng'],
                 ['Pháp luật xa rời đời sống xã hội', 'Sai'],
                 ['Pháp luật chỉ để trừng phạt', 'Sai']],
                'Pháp luật gắn với đời sống xã hội và có nhiều vai trò, không chỉ trừng phạt.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm BẢN CHẤT GIAI CẤP hoặc BẢN CHẤT XÃ HỘI.',
                [['Thể hiện ý chí giai cấp cầm quyền', 'Bản chất giai cấp'],
                 ['Bảo vệ lợi ích giai cấp thống trị', 'Bản chất giai cấp'],
                 ['Do giai cấp cầm quyền đặt ra', 'Bản chất giai cấp'],
                 ['Bắt nguồn từ thực tiễn đời sống', 'Bản chất xã hội'],
                 ['Điều chỉnh quan hệ xã hội chung', 'Bản chất xã hội'],
                 ['Đáp ứng nhu cầu chung của xã hội', 'Bản chất xã hội']],
                'Bản chất giai cấp gắn với ý chí giai cấp cầm quyền; bản chất xã hội gắn với đời sống chung của xã hội.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi vai trò vào nhóm VAI TRÒ VỚI NHÀ NƯỚC hoặc VAI TRÒ VỚI CÔNG DÂN.',
                [['Công cụ quản lí xã hội', 'Vai trò với Nhà nước'],
                 ['Tổ chức bộ máy nhà nước', 'Vai trò với Nhà nước'],
                 ['Thực hiện chính sách, pháp luật', 'Vai trò với Nhà nước'],
                 ['Bảo vệ quyền lợi hợp pháp', 'Vai trò với công dân'],
                 ['Ghi nhận các quyền cơ bản', 'Vai trò với công dân'],
                 ['Bảo đảm công bằng xã hội', 'Vai trò với công dân']],
                'Với Nhà nước, pháp luật là công cụ quản lí; với công dân, pháp luật bảo vệ quyền và lợi ích hợp pháp.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Pháp luật có tính giai cấp', 'Đúng'], ['Pháp luật có tính xã hội', 'Đúng'],
                 ['Pháp luật giúp giữ gìn trật tự xã hội', 'Đúng'], ['Pháp luật do toàn dân trực tiếp đặt ra', 'Sai'],
                 ['Pháp luật chỉ phục vụ giai cấp cầm quyền', 'Sai'], ['Pháp luật cản trở phát triển kinh tế', 'Sai']],
                'Pháp luật vừa mang tính giai cấp vừa mang tính xã hội, thúc đẩy kinh tế – xã hội phát triển.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm THỂ HIỆN CHỨC NĂNG ĐIỀU CHỈNH hoặc CHỨC NĂNG BẢO VỆ.',
                [['Quy định tốc độ tối đa khi tham gia giao thông', 'Chức năng điều chỉnh'],
                 ['Quy định điều kiện kinh doanh', 'Chức năng điều chỉnh'],
                 ['Quy định nghĩa vụ nộp thuế', 'Chức năng điều chỉnh'],
                 ['Xử phạt người trộm cắp tài sản', 'Chức năng bảo vệ'],
                 ['Truy tố tội phạm giết người', 'Chức năng bảo vệ'],
                 ['Buộc bồi thường thiệt hại', 'Chức năng bảo vệ']],
                'Chức năng điều chỉnh định hướng hành vi; chức năng bảo vệ trừng trị hành vi xâm hại.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Pháp luật là ý chí của giai cấp ___ quyền được nâng lên thành luật.', [[0, 'cầm']],
                'Bản chất giai cấp: pháp luật thể hiện và bảo vệ ý chí, lợi ích của giai cấp cầm quyền.');
            $this->fill($L, 'Các quy phạm pháp luật bắt nguồn từ thực tiễn đời sống ___ hội.', [[0, 'xã']],
                'Bản chất xã hội: pháp luật bắt nguồn từ thực tiễn và phục vụ nhu cầu chung của xã hội.');
            $this->fill($L, 'Đối với Nhà nước, pháp luật là công cụ ___ lí xã hội hữu hiệu.', [[0, 'quản']],
                'Nhờ pháp luật, Nhà nước quản lí mọi mặt đời sống xã hội một cách thống nhất.');
            $this->fill($L, 'Đối với công dân, pháp luật bảo vệ quyền và lợi ích ___ pháp.', [[0, 'hợp']],
                'Pháp luật ghi nhận các quyền cơ bản và bảo vệ lợi ích hợp pháp của công dân.');
        }
    }

    private function seedGdcd103(): void
    {
        $L = 'gdcd-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Vi phạm pháp luật là gì?',
                ['Hành vi trái pháp luật, có lỗi, do người có năng lực trách nhiệm pháp lí thực hiện',
                 'Mọi hành vi bị xã hội lên án', 'Hành vi vi phạm đạo đức', 'Hành vi gây thiệt hại dù vô ý hoàn toàn'], 0,
                'Vi phạm pháp luật phải đủ 4 dấu hiệu: trái pháp luật, có lỗi, do người có năng lực trách nhiệm pháp lí thực hiện, xâm hại quan hệ xã hội được pháp luật bảo vệ.');
            $this->quiz($L, 'Hành vi nào sau đây là vi phạm hành chính?',
                ['Vượt đèn đỏ khi tham gia giao thông', 'Trộm cắp tài sản có giá trị lớn',
                 'Vi phạm hợp đồng mua bán', 'Đi học muộn vi phạm nội quy trường'], 0,
                'Vượt đèn đỏ xâm phạm quy tắc quản lí nhà nước về giao thông nên là vi phạm hành chính.');
            $this->quiz($L, 'Hành vi nào sau đây là vi phạm dân sự?',
                ['Không trả tiền thuê nhà đúng hợp đồng', 'Đánh người gây thương tích nặng',
                 'Buôn bán hàng cấm', 'Nhận hối lộ'], 0,
                'Vi phạm hợp đồng thuê nhà xâm phạm quan hệ tài sản thuộc lĩnh vực dân sự.', 'trung_binh');
            $this->quiz($L, 'Hành vi nào sau đây là vi phạm kỉ luật?',
                ['Giáo viên bỏ tiết dạy không lí do', 'Lái xe vượt quá tốc độ quy định',
                 'Trốn thuế thu nhập', 'Cố ý gây thương tích'], 0,
                'Giáo viên bỏ tiết vi phạm nội quy, quy chế của cơ quan nên là vi phạm kỉ luật.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại vi phạm với ví dụ tương ứng.',
                [['Vi phạm hình sự', 'Trộm cắp tài sản có giá trị lớn'],
                 ['Vi phạm hành chính', 'Vượt đèn đỏ, không đội mũ bảo hiểm'],
                 ['Vi phạm dân sự', 'Vi phạm hợp đồng mua bán'],
                 ['Vi phạm kỉ luật', 'Học sinh đánh nhau trong trường']],
                'Bốn loại vi phạm: hình sự, hành chính, dân sự, kỉ luật — phân biệt theo lĩnh vực và mức độ nguy hiểm.');
            $this->matching($L, 'Nối mỗi loại vi phạm với văn bản xử lí chủ yếu.',
                [['Vi phạm hình sự', 'Bộ luật Hình sự'],
                 ['Vi phạm hành chính', 'Luật Xử lí vi phạm hành chính'],
                 ['Vi phạm dân sự', 'Bộ luật Dân sự'],
                 ['Vi phạm kỉ luật', 'Nội quy, quy chế cơ quan, tổ chức']],
                'Mỗi loại vi phạm được xử lí theo văn bản pháp luật tương ứng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dấu hiệu với nội dung của vi phạm pháp luật.',
                [['Trái pháp luật', 'Hành vi không phù hợp với quy định của pháp luật'],
                 ['Có lỗi', 'Người thực hiện nhận thức được hành vi của mình'],
                 ['Năng lực trách nhiệm pháp lí', 'Đủ tuổi và đủ khả năng nhận thức, điều khiển hành vi'],
                 ['Xâm hại quan hệ được bảo vệ', 'Gây thiệt hại cho quan hệ xã hội pháp luật bảo vệ']],
                'Thiếu một trong các dấu hiệu thì chưa đủ cơ sở kết luận là vi phạm pháp luật.');
            $this->matching($L, 'Nối mỗi hành vi với loại vi phạm tương ứng.',
                [['Buôn bán ma tuý', 'Vi phạm hình sự'],
                 ['Đỗ xe sai quy định', 'Vi phạm hành chính'],
                 ['Không bồi thường khi làm hỏng đồ của bạn', 'Vi phạm dân sự'],
                 ['Công nhân tự ý nghỉ việc nhiều ngày', 'Vi phạm kỉ luật']],
                'Xác định đúng loại vi phạm là cơ sở để áp dụng đúng biện pháp xử lí.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm VI PHẠM HÌNH SỰ hoặc VI PHẠM HÀNH CHÍNH.',
                [['Trộm cắp xe máy', 'Vi phạm hình sự'], ['Cố ý gây thương tích', 'Vi phạm hình sự'],
                 ['Lừa đảo chiếm đoạt tài sản', 'Vi phạm hình sự'], ['Vượt đèn đỏ', 'Vi phạm hành chính'],
                 ['Không đội mũ bảo hiểm', 'Vi phạm hành chính'], ['Xả rác nơi công cộng', 'Vi phạm hành chính']],
                'Vi phạm hình sự nguy hiểm cho xã hội (tội phạm); vi phạm hành chính xâm phạm quy tắc quản lí nhà nước.');
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm VI PHẠM DÂN SỰ hoặc VI PHẠM KỈ LUẬT.',
                [['Vay tiền không trả', 'Vi phạm dân sự'], ['Vi phạm hợp đồng lao động về lương', 'Vi phạm dân sự'],
                 ['Làm hỏng tài sản của người khác', 'Vi phạm dân sự'], ['Đi làm muộn nhiều lần', 'Vi phạm kỉ luật'],
                 ['Học sinh quay cóp trong giờ kiểm tra', 'Vi phạm kỉ luật'], ['Cán bộ bỏ họp không phép', 'Vi phạm kỉ luật']],
                'Vi phạm dân sự xâm phạm quan hệ tài sản, nhân thân; vi phạm kỉ luật vi phạm nội quy cơ quan, tổ chức.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Vi phạm hình sự còn gọi là tội phạm', 'Đúng'],
                 ['Mọi vi phạm pháp luật đều là tội phạm', 'Sai'],
                 ['Trẻ em 10 tuổi phải chịu trách nhiệm hình sự', 'Sai'],
                 ['Vi phạm hành chính bị xử phạt hành chính', 'Đúng'],
                 ['Vi phạm kỉ luật chỉ xảy ra trong cơ quan, trường học', 'Đúng'],
                 ['Vô ý gây thiệt hại không bao giờ là vi phạm', 'Sai']],
                'Chỉ vi phạm hình sự mới là tội phạm; lỗi vô ý vẫn có thể cấu thành vi phạm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm CÓ DẤU HIỆU VI PHẠM PHÁP LUẬT hoặc CHƯA ĐỦ DẤU HIỆU.',
                [['Người 20 tuổi cố ý đập phá tài sản người khác', 'Có dấu hiệu vi phạm'],
                 ['Người 25 tuổi lái xe vượt đèn đỏ', 'Có dấu hiệu vi phạm'],
                 ['Người 30 tuổi không trả nợ đúng hẹn', 'Có dấu hiệu vi phạm'],
                 ['Trẻ 8 tuổi làm vỡ lọ hoa của hàng xóm', 'Chưa đủ dấu hiệu'],
                 ['Người bị tâm thần đập phá đồ đạc', 'Chưa đủ dấu hiệu'],
                 ['Tai nạn do thiên tai gây thiệt hại', 'Chưa đủ dấu hiệu']],
                'Trẻ nhỏ, người mất năng lực hành vi, sự kiện bất khả kháng chưa đủ dấu hiệu vi phạm pháp luật.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Vi phạm pháp luật là hành vi ___ pháp luật, có lỗi của chủ thể.', [[0, 'trái']],
                'Dấu hiệu đầu tiên của vi phạm pháp luật là hành vi trái với quy định của pháp luật.');
            $this->fill($L, 'Vi phạm pháp luật nguy hiểm cho xã hội, bị coi là tội phạm là vi phạm ___ sự.', [[0, 'hình']],
                'Vi phạm hình sự là loại vi phạm nguy hiểm nhất, bị xử lí theo Bộ luật Hình sự.');
            $this->fill($L, 'Hành vi vượt đèn đỏ, lấn làn đường là vi phạm hành ___.', [[0, 'chính']],
                'Vi phạm hành chính xâm phạm các quy tắc quản lí nhà nước.');
            $this->fill($L, 'Vi phạm nội quy cơ quan, trường học được gọi là vi phạm ___ luật.', [[0, 'kỉ']],
                'Vi phạm kỉ luật bị xử lí bằng các hình thức kỉ luật như khiển trách, cảnh cáo.');
        }
    }

    private function seedGdcd104(): void
    {
        $L = 'gdcd-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trách nhiệm pháp lí là gì?',
                ['Nghĩa vụ phải gánh chịu hậu quả bất lợi do hành vi vi phạm pháp luật gây ra',
                 'Nghĩa vụ đạo đức với gia đình', 'Sự tự nguyện sửa chữa lỗi lầm', 'Hình thức khen thưởng'], 0,
                'Trách nhiệm pháp lí là nghĩa vụ mà cá nhân, tổ chức phải gánh chịu hậu quả bất lợi theo quy định của pháp luật.', 'trung_binh');
            $this->quiz($L, 'Người trộm cắp tài sản phải chịu trách nhiệm nào?',
                ['Trách nhiệm hình sự', 'Trách nhiệm hành chính', 'Trách nhiệm dân sự', 'Trách nhiệm kỉ luật'], 0,
                'Trộm cắp tài sản là tội phạm nên người vi phạm phải chịu trách nhiệm hình sự (hình phạt).');
            $this->quiz($L, 'Người vi phạm hợp đồng mua bán phải chịu trách nhiệm nào là chủ yếu?',
                ['Trách nhiệm dân sự', 'Trách nhiệm hình sự', 'Trách nhiệm hành chính', 'Trách nhiệm kỉ luật'], 0,
                'Vi phạm hợp đồng thuộc lĩnh vực dân sự nên chủ yếu phải bồi thường thiệt hại (trách nhiệm dân sự).', 'trung_binh');
            $this->quiz($L, 'Theo quy định, người từ đủ bao nhiêu tuổi phải chịu trách nhiệm hình sự về mọi tội phạm?',
                ['Từ đủ 16 tuổi', 'Từ đủ 14 tuổi', 'Từ đủ 18 tuổi', 'Từ đủ 20 tuổi'], 0,
                'Từ đủ 14 đến dưới 16 tuổi chỉ chịu trách nhiệm về tội rất nghiêm trọng, đặc biệt nghiêm trọng; từ đủ 16 tuổi chịu trách nhiệm về mọi tội phạm.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại trách nhiệm với hình thức xử lí tương ứng.',
                [['Trách nhiệm hình sự', 'Phạt tù, phạt tiền, cải tạo không giam giữ'],
                 ['Trách nhiệm hành chính', 'Phạt tiền, tước giấy phép, tịch thu tang vật'],
                 ['Trách nhiệm dân sự', 'Bồi thường thiệt hại, khôi phục tình trạng ban đầu'],
                 ['Trách nhiệm kỉ luật', 'Khiển trách, cảnh cáo, cách chức, buộc thôi việc']],
                'Mỗi loại vi phạm tương ứng với một loại trách nhiệm pháp lí và hình thức xử lí riêng.');
            $this->matching($L, 'Nối mỗi tình huống với loại trách nhiệm pháp lí.',
                [['Giết người', 'Trách nhiệm hình sự'],
                 ['Buôn bán hàng giả', 'Trách nhiệm hành chính hoặc hình sự'],
                 ['Làm vỡ kính nhà hàng xóm', 'Trách nhiệm dân sự'],
                 ['Giáo viên đánh học sinh', 'Trách nhiệm kỉ luật và hành chính']],
                'Xác định đúng loại trách nhiệm giúp áp dụng đúng biện pháp xử lí.', 'trung_binh');
            $this->matching($L, 'Nối mỗi độ tuổi với trách nhiệm hình sự theo quy định.',
                [['Dưới 14 tuổi', 'Không phải chịu trách nhiệm hình sự'],
                 ['Từ đủ 14 đến dưới 16 tuổi', 'Chỉ chịu về tội rất nghiêm trọng, đặc biệt nghiêm trọng'],
                 ['Từ đủ 16 tuổi trở lên', 'Chịu trách nhiệm về mọi tội phạm'],
                 ['Người chưa thành niên phạm tội', 'Được áp dụng chính sách khoan hồng đặc biệt']],
                'Pháp luật có chính sách riêng, khoan hồng với người chưa thành niên phạm tội.');
            $this->matching($L, 'Nối mỗi khái niệm với nội dung tương ứng.',
                [['Trách nhiệm pháp lí', 'Hậu quả bất lợi phải gánh chịu khi vi phạm'],
                 ['Hình phạt', 'Biện pháp cưỡng chế nghiêm khắc nhất của Nhà nước'],
                 ['Bồi thường thiệt hại', 'Khắc phục hậu quả vật chất đã gây ra'],
                 ['Kỉ luật', 'Xử lí vi phạm nội quy cơ quan, tổ chức']],
                'Các khái niệm thể hiện các khía cạnh khác nhau của trách nhiệm pháp lí.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hình thức xử lí vào nhóm TRÁCH NHIỆM HÌNH SỰ hoặc TRÁCH NHIỆM HÀNH CHÍNH.',
                [['Phạt tù có thời hạn', 'Trách nhiệm hình sự'], ['Tù chung thân', 'Trách nhiệm hình sự'],
                 ['Cải tạo không giam giữ', 'Trách nhiệm hình sự'], ['Phạt tiền vi phạm giao thông', 'Trách nhiệm hành chính'],
                 ['Tước giấy phép lái xe', 'Trách nhiệm hành chính'], ['Tịch thu tang vật vi phạm', 'Trách nhiệm hành chính']],
                'Hình phạt (tù, cải tạo) thuộc trách nhiệm hình sự; phạt tiền, tước giấy phép thuộc trách nhiệm hành chính.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm TRÁCH NHIỆM DÂN SỰ hoặc TRÁCH NHIỆM KỈ LUẬT.',
                [['Phải đền tiền làm hỏng xe của bạn', 'Trách nhiệm dân sự'],
                 ['Phải trả lại tiền vay quá hạn', 'Trách nhiệm dân sự'],
                 ['Bồi thường khi vi phạm hợp đồng', 'Trách nhiệm dân sự'],
                 ['Bị khiển trách vì đi làm muộn', 'Trách nhiệm kỉ luật'],
                 ['Bị cảnh cáo vì bỏ họp', 'Trách nhiệm kỉ luật'],
                 ['Bị cách chức vì thiếu trách nhiệm', 'Trách nhiệm kỉ luật']],
                'Trách nhiệm dân sự khắc phục thiệt hại tài sản; trách nhiệm kỉ luật xử lí vi phạm nội quy.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Ai vi phạm pháp luật đều phải chịu trách nhiệm pháp lí', 'Đúng'],
                 ['Trách nhiệm hình sự là nghiêm khắc nhất', 'Đúng'],
                 ['Vi phạm dân sự phải đi tù', 'Sai'],
                 ['Người dưới 14 tuổi không chịu trách nhiệm hình sự', 'Đúng'],
                 ['Trốn thuế chỉ bị nhắc nhở', 'Sai'],
                 ['Kỉ luật chỉ áp dụng trong quân đội', 'Sai']],
                'Mức độ nghiêm khắc: hình sự > hành chính > dân sự, kỉ luật; trốn thuế bị xử phạt nghiêm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm PHẢI BỒI THƯỜNG THIỆT HẠI hoặc KHÔNG PHẢI BỒI THƯỜNG.',
                [['Làm vỡ điện thoại của bạn', 'Phải bồi thường thiệt hại'],
                 ['Gây tai nạn làm hỏng xe người khác', 'Phải bồi thường thiệt hại'],
                 ['Chó nhà mình cắn người đi đường', 'Phải bồi thường thiệt hại'],
                 ['Nhặt được của rơi trả lại người mất', 'Không phải bồi thường'],
                 ['Giúp đỡ người gặp nạn', 'Không phải bồi thường'],
                 ['Thiệt hại do bão lũ gây ra', 'Không phải bồi thường']],
                'Ai gây thiệt hại cho người khác do hành vi trái pháp luật của mình thì phải bồi thường.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trách nhiệm ___ lí là hậu quả bất lợi mà người vi phạm phải gánh chịu.', [[0, 'pháp']],
                'Trách nhiệm pháp lí phát sinh khi có hành vi vi phạm pháp luật.');
            $this->fill($L, 'Hình thức xử lí nghiêm khắc nhất là phạt tù thuộc trách nhiệm ___ sự.', [[0, 'hình']],
                'Trách nhiệm hình sự áp dụng với tội phạm, do Toà án quyết định bằng bản án.');
            $this->fill($L, 'Người làm hỏng tài sản của người khác phải ___ thường thiệt hại.', [[0, 'bồi']],
                'Bồi thường thiệt hại là hình thức chủ yếu của trách nhiệm dân sự.');
            $this->fill($L, 'Người từ đủ ___ tuổi phải chịu trách nhiệm hình sự về mọi tội phạm.', [[0, '16']],
                'Từ đủ 16 tuổi trở lên phải chịu trách nhiệm hình sự về mọi tội phạm.');
        }
    }

    // ================= GDCD LỚP 11 =================

    private function seedGdcd111(): void
    {
        $L = 'gdcd-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Sản xuất của cải vật chất có vai trò như thế nào đối với xã hội?',
                ['Là hoạt động cơ bản, quyết định sự tồn tại và phát triển của xã hội',
                 'Chỉ là hoạt động phụ, không quan trọng', 'Chỉ cần thiết với người nghèo', 'Đã lỗi thời trong thời đại số'], 0,
                'Sản xuất của cải vật chất tạo ra tư liệu sinh hoạt, là cơ sở cho mọi hoạt động khác của xã hội.');
            $this->quiz($L, 'Yếu tố nào sau đây KHÔNG phải là yếu tố cơ bản của quá trình sản xuất?',
                ['Tiền lương', 'Sức lao động', 'Đối tượng lao động', 'Tư liệu lao động'], 0,
                'Ba yếu tố cơ bản: sức lao động, đối tượng lao động, tư liệu lao động. Tiền lương là thu nhập, không phải yếu tố sản xuất.');
            $this->quiz($L, 'Trong tư liệu lao động, yếu tố nào quan trọng nhất, thể hiện trình độ phát triển sản xuất?',
                ['Công cụ lao động', 'Nhà xưởng', 'Kho bãi', 'Phương tiện vận chuyển'], 0,
                'Công cụ lao động là yếu tố quan trọng nhất, là thước đo trình độ phát triển của lực lượng sản xuất.', 'trung_binh');
            $this->quiz($L, 'Ví dụ nào sau đây thuộc về "đối tượng lao động"?',
                ['Đất đai, nguyên liệu để sản xuất', 'Máy móc trong nhà máy', 'Sức khoẻ của công nhân', 'Tiền vốn đầu tư'], 0,
                'Đối tượng lao động là những gì con người tác động vào trong quá trình sản xuất như đất đai, nguyên liệu.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi yếu tố sản xuất với ví dụ tương ứng.',
                [['Sức lao động', 'Tay nghề, sức khoẻ của công nhân'],
                 ['Đối tượng lao động', 'Đất đai, quặng sắt, bông'],
                 ['Tư liệu lao động', 'Máy móc, công cụ, nhà xưởng'],
                 ['Công cụ lao động', 'Máy cày, máy dệt, búa']],
                'Ba yếu tố cơ bản kết hợp với nhau tạo thành quá trình sản xuất.');
            $this->matching($L, 'Nối mỗi khái niệm với nội dung tương ứng.',
                [['Lực lượng sản xuất', 'Người lao động cùng công cụ lao động'],
                 ['Quan hệ sản xuất', 'Quan hệ giữa người với người trong sản xuất'],
                 ['Tư liệu sản xuất', 'Tư liệu lao động và đối tượng lao động'],
                 ['Sản xuất của cải vật chất', 'Tạo ra sản phẩm đáp ứng nhu cầu đời sống']],
                'Lực lượng sản xuất và quan hệ sản xuất là hai mặt của phương thức sản xuất.', 'trung_binh');
            $this->matching($L, 'Nối mỗi ngành với loại sản phẩm làm ra.',
                [['Nông nghiệp', 'Lúa gạo, rau củ, thịt'],
                 ['Công nghiệp', 'Máy móc, quần áo, điện'],
                 ['Xây dựng', 'Nhà cửa, cầu đường'],
                 ['Dịch vụ', 'Vận tải, du lịch, giáo dục']],
                'Các ngành sản xuất tạo ra của cải vật chất và dịch vụ phục vụ đời sống.');
            $this->matching($L, 'Nối mỗi nhận định với ĐÚNG hoặc SAI.',
                [['Sản xuất quyết định sự tồn tại của xã hội', 'Đúng'],
                 ['Công cụ lao động thể hiện trình độ sản xuất', 'Đúng'],
                 ['Tiền vốn là yếu tố cơ bản của sản xuất', 'Sai'],
                 ['Sản xuất chỉ cần sức lao động là đủ', 'Sai']],
                'Quá trình sản xuất cần sự kết hợp của cả ba yếu tố cơ bản.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm SỨC LAO ĐỘNG, ĐỐI TƯỢNG LAO ĐỘNG hoặc TƯ LIỆU LAO ĐỘNG.',
                [['Tay nghề của thợ may', 'Sức lao động'], ['Sức khoẻ công nhân', 'Sức lao động'],
                 ['Vải để may quần áo', 'Đối tượng lao động'], ['Gỗ để đóng bàn ghế', 'Đối tượng lao động'],
                 ['Máy may công nghiệp', 'Tư liệu lao động'], ['Nhà xưởng sản xuất', 'Tư liệu lao động']],
                'Sức lao động là năng lực con người; đối tượng lao động là thứ bị tác động; tư liệu lao động là công cụ, phương tiện.');
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm SẢN XUẤT VẬT CHẤT hoặc KHÔNG PHẢI SẢN XUẤT VẬT CHẤT.',
                [['Trồng lúa', 'Sản xuất vật chất'], ['Dệt vải', 'Sản xuất vật chất'],
                 ['Lắp ráp xe máy', 'Sản xuất vật chất'], ['Xem phim giải trí', 'Không phải sản xuất vật chất'],
                 ['Đi du lịch', 'Không phải sản xuất vật chất'], ['Chơi game', 'Không phải sản xuất vật chất']],
                'Sản xuất vật chất tạo ra sản phẩm vật chất; tiêu dùng, giải trí không phải sản xuất.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Ba yếu tố sản xuất luôn kết hợp với nhau', 'Đúng'],
                 ['Máy móc càng hiện đại, năng suất càng cao', 'Đúng'],
                 ['Sản xuất không cần đối tượng lao động', 'Sai'],
                 ['Nông nghiệp không phải sản xuất vật chất', 'Sai'],
                 ['Con người là yếu tố quyết định trong sản xuất', 'Đúng'],
                 ['Tư liệu lao động gồm công cụ và nhà xưởng', 'Đúng']],
                'Con người sử dụng công cụ tác động vào đối tượng lao động để tạo ra sản phẩm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm CÔNG CỤ THÔ SƠ hoặc CÔNG CỤ HIỆN ĐẠI.',
                [['Cày bừa bằng trâu', 'Công cụ thô sơ'], ['Liềm gặt lúa', 'Công cụ thô sơ'],
                 ['Khung cửi dệt vải', 'Công cụ thô sơ'], ['Máy gặt đập liên hợp', 'Công cụ hiện đại'],
                 ['Robot lắp ráp ô tô', 'Công cụ hiện đại'], ['Máy tính điều khiển', 'Công cụ hiện đại']],
                'Trình độ công cụ lao động phản ánh trình độ phát triển của lực lượng sản xuất.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ba yếu tố cơ bản của quá trình sản xuất là: sức lao động, đối tượng lao động và ___ lao động.', [[0, 'tư liệu']],
                'Tư liệu lao động gồm công cụ, máy móc, nhà xưởng phục vụ sản xuất.');
            $this->fill($L, 'Trong tư liệu lao động, ___ cụ lao động là yếu tố quan trọng nhất.', [[0, 'công']],
                'Công cụ lao động là thước đo trình độ phát triển của lực lượng sản xuất.');
            $this->fill($L, 'Đất đai, nguyên liệu mà con người tác động vào gọi là đối tượng ___ động.', [[0, 'lao']],
                'Đối tượng lao động có thể là tự nhiên (đất, quặng) hoặc đã qua chế biến (nguyên liệu).');
            $this->fill($L, 'Sản xuất của cải vật chất là hoạt động cơ bản, quyết định sự tồn tại và ___ triển của xã hội.', [[0, 'phát']],
                'Mọi hoạt động khác của xã hội đều dựa trên nền tảng sản xuất vật chất.');
        }
    }

    private function seedGdcd112(): void
    {
        $L = 'gdcd-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tiêu dùng là gì?',
                ['Việc sử dụng sản phẩm, dịch vụ để thoả mãn nhu cầu đời sống',
                 'Việc sản xuất ra hàng hoá', 'Việc bán hàng để thu lợi nhuận', 'Việc tích trữ tiền bạc'], 0,
                'Tiêu dùng là khâu cuối của quá trình tái sản xuất: sử dụng sản phẩm để thoả mãn nhu cầu.');
            $this->quiz($L, 'Hành vi nào sau đây thể hiện văn hoá tiêu dùng?',
                ['Mang túi vải đi chợ, hạn chế túi ni lông', 'Mua sắm vượt quá khả năng chi trả',
                 'Vứt rác bừa bãi sau khi ăn uống', 'Mua hàng giả, hàng nhái vì rẻ'], 0,
                'Văn hoá tiêu dùng là ứng xử văn minh: hợp lí, tiết kiệm, bảo vệ môi trường, tôn trọng người khác.');
            $this->quiz($L, 'Người tiêu dùng có quyền nào sau đây theo pháp luật?',
                ['Được bảo đảm an toàn, được cung cấp thông tin trung thực về hàng hoá',
                 'Được dùng thử miễn phí mọi sản phẩm', 'Được trả lại hàng bất cứ lúc nào không cần lí do', 'Được mua hàng với giá rẻ nhất'], 0,
                'Luật Bảo vệ quyền lợi người tiêu dùng ghi nhận quyền được an toàn, được thông tin đầy đủ, trung thực.', 'trung_binh');
            $this->quiz($L, 'Việc làm nào sau đây là trách nhiệm của người tiêu dùng?',
                ['Tìm hiểu kĩ thông tin hàng hoá trước khi mua', 'Ép người bán giảm giá bằng mọi cách',
                 'Dùng xong vứt bao bì bừa bãi', 'Mua hàng không cần hoá đơn để được rẻ'], 0,
                'Người tiêu dùng có nghĩa vụ tìm hiểu thông tin, sử dụng đúng hướng dẫn và bảo vệ môi trường.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nguyên tắc với ví dụ về văn hoá tiêu dùng.',
                [['Tiêu dùng hợp lí', 'Mua sắm theo kế hoạch, phù hợp thu nhập'],
                 ['Tiết kiệm', 'Tắt điện khi ra khỏi phòng, dùng nước vừa đủ'],
                 ['Bảo vệ môi trường', 'Phân loại rác, hạn chế nhựa dùng một lần'],
                 ['Tôn trọng người khác', 'Xếp hàng khi mua vé, không chen lấn']],
                'Văn hoá tiêu dùng thể hiện lối sống văn minh của mỗi người.');
            $this->matching($L, 'Nối mỗi quyền của người tiêu dùng với nội dung.',
                [['Quyền được an toàn', 'Hàng hoá không gây hại sức khoẻ, tính mạng'],
                 ['Quyền được thông tin', 'Biết đầy đủ, trung thực về hàng hoá'],
                 ['Quyền lựa chọn', 'Tự do chọn hàng hoá, dịch vụ phù hợp'],
                 ['Quyền khiếu nại', 'Yêu cầu bồi thường khi quyền lợi bị xâm phạm']],
                'Luật Bảo vệ quyền lợi người tiêu dùng (2023) ghi nhận các quyền cơ bản này.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hành vi với đánh giá VĂN MINH hoặc THIẾU VĂN HOÁ.',
                [['Mang cốc cá nhân đi mua cà phê', 'Văn minh'],
                 ['Tắt vòi nước khi đánh răng', 'Văn minh'],
                 ['Chen lấn khi xếp hàng mua hàng', 'Thiếu văn hoá'],
                 ['Bỏ rác đúng nơi quy định', 'Văn minh']],
                'Hành vi tiêu dùng văn minh vừa tiết kiệm vừa bảo vệ môi trường.');
            $this->matching($L, 'Nối mỗi tình huống với cách xử lí đúng của người tiêu dùng.',
                [['Mua phải hàng giả', 'Khiếu nại, yêu cầu bồi thường'],
                 ['Hàng hoá không đúng quảng cáo', 'Phản ánh với người bán, cơ quan chức năng'],
                 ['Không rõ hạn sử dụng', 'Hỏi kĩ trước khi mua, không mua hàng mập mờ'],
                 ['Bị ép mua hàng', 'Từ chối, báo cơ quan chức năng nếu bị đe doạ']],
                'Người tiêu dùng thông thái biết bảo vệ quyền lợi hợp pháp của mình.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm TIÊU DÙNG VĂN MINH hoặc TIÊU DÙNG THIẾU VĂN HOÁ.',
                [['Lập kế hoạch chi tiêu hằng tháng', 'Tiêu dùng văn minh'],
                 ['Dùng túi vải thay túi ni lông', 'Tiêu dùng văn minh'],
                 ['Tắt thiết bị điện khi không dùng', 'Tiêu dùng văn minh'],
                 ['Mua sắm theo cảm hứng, nợ nần', 'Tiêu dùng thiếu văn hoá'],
                 ['Xả rác bừa bãi nơi công cộng', 'Tiêu dùng thiếu văn hoá'],
                 ['Lãng phí thức ăn', 'Tiêu dùng thiếu văn hoá']],
                'Tiêu dùng văn minh: hợp lí, tiết kiệm, có trách nhiệm với môi trường và cộng đồng.');
            $this->sortQ($L, 'Kéo mỗi nhu cầu vào nhóm NHU CẦU THIẾT YẾU hoặc NHU CẦU KHÔNG THIẾT YẾU.',
                [['Ăn uống hằng ngày', 'Nhu cầu thiết yếu'], ['Quần áo mặc', 'Nhu cầu thiết yếu'],
                 ['Thuốc chữa bệnh', 'Nhu cầu thiết yếu'], ['Đồ hiệu xa xỉ vượt khả năng', 'Nhu cầu không thiết yếu'],
                 ['Đồ chơi sưu tầm đắt tiền', 'Nhu cầu không thiết yếu'], ['Du lịch sang chảnh vay nợ', 'Nhu cầu không thiết yếu']],
                'Ưu tiên đáp ứng nhu cầu thiết yếu trước, cân nhắc kĩ với nhu cầu không thiết yếu.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Người tiêu dùng có quyền được thông tin trung thực', 'Đúng'],
                 ['Nên mua hàng không rõ nguồn gốc vì rẻ', 'Sai'],
                 ['Phân loại rác là trách nhiệm của người tiêu dùng', 'Đúng'],
                 ['Tiêu dùng không ảnh hưởng đến môi trường', 'Sai'],
                 ['Có thể khiếu nại khi mua phải hàng giả', 'Đúng'],
                 ['Nên đọc kĩ hướng dẫn trước khi dùng sản phẩm', 'Đúng']],
                'Tiêu dùng có trách nhiệm vừa bảo vệ mình vừa bảo vệ môi trường.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TIẾT KIỆM hoặc LÃNG PHÍ.',
                [['Dùng nước vo gạo tưới cây', 'Tiết kiệm'], ['Tận dụng giấy một mặt', 'Tiết kiệm'],
                 ['Đi bộ quãng đường ngắn', 'Tiết kiệm'], ['Bật điều hoà rồi mở cửa', 'Lãng phí'],
                 ['Để vòi nước chảy khi đánh răng', 'Lãng phí'], ['Mua nhiều đồ rồi bỏ không dùng', 'Lãng phí']],
                'Tiết kiệm trong tiêu dùng là biểu hiện của văn hoá và trách nhiệm công dân.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tiêu dùng là việc sử dụng sản phẩm, dịch vụ để thoả mãn nhu cầu đời ___.', [[0, 'sống']],
                'Tiêu dùng là khâu không thể thiếu, nối tiếp sản xuất và phân phối.');
            $this->fill($L, 'Văn hoá tiêu dùng là cách ứng xử ___ minh trong tiêu dùng.', [[0, 'văn']],
                'Tiêu dùng văn minh: hợp lí, tiết kiệm, bảo vệ môi trường.');
            $this->fill($L, 'Người tiêu dùng có quyền được cung cấp thông tin đầy đủ, ___ thực về hàng hoá.', [[0, 'trung']],
                'Thông tin trung thực giúp người tiêu dùng lựa chọn đúng đắn.');
            $this->fill($L, 'Hạn chế túi ni lông, phân loại rác tại nguồn là góp phần bảo vệ ___ trường.', [[0, 'môi']],
                'Mỗi hành vi tiêu dùng đều tác động đến môi trường xung quanh.');
        }
    }

    private function seedGdcd113(): void
    {
        $L = 'gdcd-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thị trường là gì?',
                ['Nơi gặp gỡ giữa người mua và người bán; tổng hoà các quan hệ mua bán hàng hoá, dịch vụ',
                 'Chỉ là cái chợ trong khu dân cư', 'Nơi Nhà nước phân phối hàng hoá', 'Kho chứa hàng của doanh nghiệp'], 0,
                'Thị trường theo nghĩa rộng là tổng hoà các quan hệ mua bán, trao đổi hàng hoá, dịch vụ.');
            $this->quiz($L, 'Chức năng thừa nhận của thị trường thể hiện ở điểm nào?',
                ['Thị trường thừa nhận giá trị sử dụng và giá trị của hàng hoá qua việc mua bán',
                 'Thị trường cung cấp thông tin giá cả', 'Thị trường điều tiết sản xuất', 'Thị trường trừng phạt người vi phạm'], 0,
                'Hàng hoá chỉ thực sự có giá trị khi được thị trường thừa nhận, tức là có người mua.', 'trung_binh');
            $this->quiz($L, 'Khi cung lớn hơn cầu về một mặt hàng, giá cả thường biến động thế nào?',
                ['Giá có xu hướng giảm', 'Giá có xu hướng tăng', 'Giá không thay đổi', 'Hàng hoá biến mất khỏi thị trường'], 0,
                'Quy luật cung – cầu: cung vượt cầu thì giá giảm; cầu vượt cung thì giá tăng.');
            $this->quiz($L, 'Chức năng thông tin của thị trường giúp ích gì cho người sản xuất?',
                ['Biết được nhu cầu, giá cả để quyết định sản xuất, kinh doanh đúng',
                 'Được Nhà nước bao tiêu sản phẩm', 'Không cần quan tâm đến khách hàng', 'Tự ý định giá cao'], 0,
                'Thông tin thị trường về cung, cầu, giá cả giúp người sản xuất điều chỉnh kịp thời.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chức năng của thị trường với nội dung tương ứng.',
                [['Chức năng thừa nhận', 'Thừa nhận giá trị sử dụng và giá trị của hàng hoá'],
                 ['Chức năng điều tiết, kích thích', 'Điều tiết sản xuất, lưu thông; kích thích phát triển'],
                 ['Chức năng thông tin', 'Cung cấp thông tin về cung – cầu, giá cả'],
                 ['Vai trò chung', 'Là cầu nối giữa sản xuất và tiêu dùng']],
                'Ba chức năng cơ bản của thị trường: thừa nhận, điều tiết – kích thích, thông tin.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tình huống cung – cầu với xu hướng giá cả.',
                [['Cung vượt cầu', 'Giá có xu hướng giảm'],
                 ['Cầu vượt cung', 'Giá có xu hướng tăng'],
                 ['Cung bằng cầu', 'Giá ổn định'],
                 ['Hàng hoá khan hiếm dịp lễ', 'Giá thường tăng cao']],
                'Giá cả thị trường hình thành trên cơ sở quan hệ cung – cầu.');
            $this->matching($L, 'Nối mỗi loại thị trường với ví dụ.',
                [['Thị trường hàng hoá', 'Chợ, siêu thị, cửa hàng'],
                 ['Thị trường lao động', 'Tuyển dụng việc làm'],
                 ['Thị trường chứng khoán', 'Mua bán cổ phiếu'],
                 ['Thị trường bất động sản', 'Mua bán nhà đất']],
                'Thị trường gồm nhiều loại, mỗi loại trao đổi một đối tượng khác nhau.');
            $this->matching($L, 'Nối mỗi khái niệm với nội dung tương ứng.',
                [['Cung', 'Khối lượng hàng hoá người bán muốn và có thể bán'],
                 ['Cầu', 'Khối lượng hàng hoá người mua muốn và có thể mua'],
                 ['Giá cả thị trường', 'Giá hình thành từ quan hệ cung – cầu'],
                 ['Sức mua', 'Khả năng thanh toán của người tiêu dùng']],
                'Cung – cầu – giá cả là ba yếu tố cốt lõi vận hành thị trường.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm CUNG VƯỢT CẦU hoặc CẦU VƯỢT CUNG.',
                [['Vải thiều được mùa, bán không hết', 'Cung vượt cầu'],
                 ['Khẩu trang khan hiếm mùa dịch', 'Cầu vượt cung'],
                 ['Vé xe Tết cháy vé', 'Cầu vượt cung'],
                 ['Quần áo tồn kho phải giảm giá', 'Cung vượt cầu'],
                 ['Nhà trọ gần trường luôn kín phòng', 'Cầu vượt cung'],
                 ['Rau củ ùn ứ phải đổ bỏ', 'Cung vượt cầu']],
                'Cung vượt cầu: hàng ế, giá giảm; cầu vượt cung: hàng khan, giá tăng.');
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm THỂ HIỆN CHỨC NĂNG THỪA NHẬN hoặc CHỨC NĂNG THÔNG TIN.',
                [['Sản phẩm mới được khách hàng đón nhận', 'Chức năng thừa nhận'],
                 ['Hàng tồn kho vì không ai mua', 'Chức năng thừa nhận'],
                 ['Xem giá cả trên sàn thương mại điện tử', 'Chức năng thông tin'],
                 ['Nghe tin giá xăng tăng để quyết định nhập hàng', 'Chức năng thông tin'],
                 ['Mẫu mã đẹp bán chạy', 'Chức năng thừa nhận'],
                 ['Theo dõi nhu cầu để điều chỉnh sản xuất', 'Chức năng thông tin']],
                'Thừa nhận: thị trường chấp nhận hay từ chối hàng hoá; thông tin: cung cấp dữ liệu để quyết định.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Thị trường nối sản xuất với tiêu dùng', 'Đúng'],
                 ['Giá cả do quan hệ cung – cầu quyết định', 'Đúng'],
                 ['Cung vượt cầu làm giá tăng', 'Sai'],
                 ['Thị trường chỉ gồm chợ truyền thống', 'Sai'],
                 ['Thông tin thị trường giúp kinh doanh hiệu quả', 'Đúng'],
                 ['Chức năng điều tiết giúp phân bổ nguồn lực', 'Đúng']],
                'Hiểu đúng quy luật cung – cầu giúp tham gia thị trường hiệu quả.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi chủ thể vào nhóm BÊN CUNG hoặc BÊN CẦU.',
                [['Nông dân bán lúa', 'Bên cung'], ['Công ty may bán quần áo', 'Bên cung'],
                 ['Tiểu thương bán rau', 'Bên cung'], ['Người đi chợ mua thực phẩm', 'Bên cầu'],
                 ['Học sinh mua sách vở', 'Bên cầu'], ['Gia đình thuê nhà trọ', 'Bên cầu']],
                'Bên cung là người bán; bên cầu là người mua trên thị trường.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thị trường là nơi gặp gỡ giữa người mua và người ___.', [[0, 'bán']],
                'Thị trường là tổng hoà các quan hệ mua bán hàng hoá, dịch vụ.');
            $this->fill($L, 'Khi cung vượt cầu, giá cả có xu hướng ___.', [[0, 'giảm']],
                'Hàng hoá dư thừa, người bán phải hạ giá để bán được hàng.');
            $this->fill($L, 'Chức năng ___ nhận thể hiện việc thị trường chấp nhận giá trị của hàng hoá.', [[0, 'thừa']],
                'Hàng hoá chỉ có giá trị thực khi được thị trường thừa nhận qua mua bán.');
            $this->fill($L, 'Giá cả thị trường hình thành trên cơ sở quan hệ cung – ___.', [[0, 'cầu']],
                'Cung – cầu – giá cả là ba yếu tố cốt lõi của thị trường.');
        }
    }

    private function seedGdcd114(): void
    {
        $L = 'gdcd-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cạnh tranh trong kinh tế thị trường là gì?',
                ['Sự ganh đua giữa các chủ thể kinh tế để giành điều kiện thuận lợi, thu lợi nhuận cao nhất',
                 'Sự hợp tác giúp đỡ lẫn nhau', 'Sự độc quyền của một doanh nghiệp', 'Sự can thiệp của Nhà nước vào giá cả'], 0,
                'Cạnh tranh là động lực phát triển của nền kinh tế thị trường.', 'trung_binh');
            $this->quiz($L, 'Mặt tích cực của cạnh tranh là gì?',
                ['Kích thích lực lượng sản xuất phát triển, nâng cao chất lượng, hạ giá thành',
                 'Làm mọi doanh nghiệp đều phá sản', 'Khiến giá cả luôn tăng cao', 'Triệt tiêu mọi sáng kiến'], 0,
                'Cạnh tranh lành mạnh thúc đẩy tiến bộ kĩ thuật, mang lại lợi ích cho người tiêu dùng.');
            $this->quiz($L, 'Hành vi nào sau đây là cạnh tranh KHÔNG lành mạnh?',
                ['Quảng cáo sai sự thật về sản phẩm', 'Cải tiến mẫu mã sản phẩm',
                 'Hạ giá thành nhờ công nghệ mới', 'Nâng cao chất lượng dịch vụ'], 0,
                'Quảng cáo sai sự thật lừa dối người tiêu dùng, là hành vi cạnh tranh không lành mạnh bị pháp luật cấm.');
            $this->quiz($L, 'Vì sao pháp luật cấm các hành vi cạnh tranh không lành mạnh?',
                ['Để bảo vệ môi trường kinh doanh công bằng và quyền lợi người tiêu dùng',
                 'Để Nhà nước độc quyền kinh doanh', 'Để giá cả luôn ở mức cao', 'Để hạn chế số doanh nghiệp'], 0,
                'Pháp luật bảo đảm cạnh tranh lành mạnh, chống hàng giả, gian lận thương mại.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi mặt của cạnh tranh với biểu hiện tương ứng.',
                [['Mặt tích cực', 'Nâng cao chất lượng, hạ giá thành sản phẩm'],
                 ['Mặt tích cực', 'Thúc đẩy tiến bộ khoa học kĩ thuật'],
                 ['Mặt tiêu cực', 'Sản xuất hàng giả, hàng nhái'],
                 ['Mặt tiêu cực', 'Bán phá giá để triệt hạ đối thủ']],
                'Cạnh tranh có tính hai mặt: thúc đẩy phát triển nhưng cũng có thể gây hậu quả xấu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hành vi với đánh giá LÀNH MẠNH hoặc KHÔNG LÀNH MẠNH.',
                [['Cải tiến công nghệ để giảm giá thành', 'Lành mạnh'],
                 ['Quảng cáo sai sự thật', 'Không lành mạnh'],
                 ['Đầu cơ tích trữ đẩy giá lên', 'Không lành mạnh'],
                 ['Nâng cao chất lượng phục vụ', 'Lành mạnh']],
                'Cạnh tranh lành mạnh dựa trên chất lượng, giá cả thực; không lành mạnh dùng thủ đoạn gian dối.');
            $this->matching($L, 'Nối mỗi biện pháp với mục đích trong quản lí cạnh tranh.',
                [['Luật Cạnh tranh', 'Điều chỉnh hành vi cạnh tranh, chống độc quyền'],
                 ['Chống hàng giả', 'Bảo vệ người tiêu dùng và doanh nghiệp chân chính'],
                 ['Xử phạt quảng cáo sai sự thật', 'Bảo đảm thông tin trung thực'],
                 ['Khuyến khích sáng tạo', 'Tạo động lực cạnh tranh bằng chất lượng']],
                'Nhà nước quản lí cạnh tranh bằng pháp luật để giữ môi trường kinh doanh công bằng.');
            $this->matching($L, 'Nối mỗi tình huống với cách ứng xử đúng của doanh nghiệp.',
                [['Đối thủ hạ giá', 'Nâng cao chất lượng, tối ưu chi phí'],
                 ['Bị làm hàng giả', 'Báo cơ quan chức năng, bảo vệ thương hiệu'],
                 ['Muốn thu hút khách', 'Khuyến mãi trung thực, dịch vụ tốt'],
                 ['Thị trường biến động', 'Nghiên cứu nhu cầu, đổi mới sản phẩm']],
                'Doanh nghiệp chân chính cạnh tranh bằng chất lượng và uy tín.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm CẠNH TRANH LÀNH MẠNH hoặc KHÔNG LÀNH MẠNH.',
                [['Hạ giá nhờ cải tiến kĩ thuật', 'Cạnh tranh lành mạnh'],
                 ['Nâng cao chất lượng sản phẩm', 'Cạnh tranh lành mạnh'],
                 ['Chăm sóc khách hàng tốt', 'Cạnh tranh lành mạnh'],
                 ['Làm hàng giả nhãn hiệu nổi tiếng', 'Không lành mạnh'],
                 ['Quảng cáo sai sự thật', 'Không lành mạnh'],
                 ['Bán phá giá để diệt đối thủ', 'Không lành mạnh']],
                'Lành mạnh: dựa vào chất lượng, hiệu quả thực; không lành mạnh: gian dối, thủ đoạn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi kết quả vào nhóm MẶT TÍCH CỰC hoặc MẶT TIÊU CỰC của cạnh tranh.',
                [['Giá cả hợp lí hơn', 'Mặt tích cực'], ['Chất lượng hàng hoá nâng cao', 'Mặt tích cực'],
                 ['Nhiều lựa chọn cho người mua', 'Mặt tích cực'], ['Hàng giả tràn lan', 'Mặt tiêu cực'],
                 ['Môi trường bị ô nhiễm do chạy theo lợi nhuận', 'Mặt tiêu cực'], ['Gian lận thương mại', 'Mặt tiêu cực']],
                'Cạnh tranh có tính hai mặt; pháp luật phát huy mặt tích cực, hạn chế mặt tiêu cực.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Cạnh tranh là động lực của kinh tế thị trường', 'Đúng'],
                 ['Mọi hành vi cạnh tranh đều tốt', 'Sai'],
                 ['Pháp luật cấm cạnh tranh không lành mạnh', 'Đúng'],
                 ['Bán phá giá là cạnh tranh lành mạnh', 'Sai'],
                 ['Người tiêu dùng hưởng lợi từ cạnh tranh lành mạnh', 'Đúng'],
                 ['Độc quyền là kết quả tốt của cạnh tranh', 'Sai']],
                'Cạnh tranh lành mạnh có lợi; cạnh tranh không lành mạnh bị pháp luật xử lí.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm DOANH NGHIỆP NÊN LÀM hoặc KHÔNG NÊN LÀM.',
                [['Đầu tư nghiên cứu cải tiến sản phẩm', 'Nên làm'], ['Giữ uy tín với khách hàng', 'Nên làm'],
                 ['Tuân thủ pháp luật về cạnh tranh', 'Nên làm'], ['Mua chuộc cán bộ để trúng thầu', 'Không nên làm'],
                 ['Vu khống đối thủ cạnh tranh', 'Không nên làm'], ['Trốn thuế để hạ giá thành', 'Không nên làm']],
                'Doanh nghiệp phát triển bền vững nhờ uy tín, chất lượng và tuân thủ pháp luật.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cạnh tranh là sự ___ đua giữa các chủ thể kinh tế trên thị trường.', [[0, 'ganh']],
                'Ganh đua để giành điều kiện thuận lợi trong sản xuất, tiêu thụ.');
            $this->fill($L, 'Mặt tích cực của cạnh tranh là kích thích lực lượng sản xuất ___ triển.', [[0, 'phát']],
                'Cạnh tranh thúc đẩy tiến bộ kĩ thuật, nâng cao năng suất.');
            $this->fill($L, 'Quảng cáo sai sự thật, làm hàng giả là cạnh tranh không ___ mạnh.', [[0, 'lành']],
                'Các hành vi cạnh tranh không lành mạnh bị pháp luật nghiêm cấm.');
            $this->fill($L, 'Luật ___ tranh điều chỉnh hành vi cạnh tranh, chống độc quyền.', [[0, 'Cạnh']],
                'Nhà nước dùng pháp luật để bảo đảm môi trường cạnh tranh công bằng.');
        }
    }

    // ================= GDCD LỚP 12 =================

    private function seedGdcd121(): void
    {
        $L = 'gdcd-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Quyền bình đẳng của công dân trước pháp luật có nghĩa là gì?',
                ['Mọi công dân đều bình đẳng, không ai bị phân biệt đối xử, ai vi phạm đều bị xử lí',
                 'Mọi người đều được miễn trách nhiệm', 'Người có chức vụ được ưu ái hơn', 'Chỉ áp dụng với người thành niên'], 0,
                'Bình đẳng trước pháp luật: mọi công dân đều bình đẳng về quyền và nghĩa vụ, không phân biệt.');
            $this->quiz($L, 'Bình đẳng trong lao động thể hiện ở điểm nào?',
                ['Mọi người có quyền làm việc, lựa chọn nghề nghiệp, nam nữ bình đẳng',
                 'Chỉ nam giới được làm việc nặng', 'Người khuyết tật không được tuyển dụng', 'Lương nam luôn cao hơn lương nữ'], 0,
                'Mọi công dân bình đẳng trong việc thực hiện quyền lao động, không phân biệt giới tính.');
            $this->quiz($L, 'Bình đẳng trong kinh doanh có nghĩa là gì?',
                ['Mọi cá nhân, tổ chức có quyền tự do kinh doanh ngành nghề pháp luật không cấm',
                 'Ai cũng được kinh doanh mọi mặt hàng', 'Không cần đăng kí kinh doanh', 'Được trốn thuế khi kinh doanh nhỏ'], 0,
                'Tự do kinh doanh trong khuôn khổ pháp luật; ngành nghề cấm thì không được kinh doanh.', 'trung_binh');
            $this->quiz($L, 'Bình đẳng trong hôn nhân và gia đình thể hiện ở điểm nào?',
                ['Vợ chồng bình đẳng về quyền và nghĩa vụ trong gia đình',
                 'Chồng quyết định mọi việc', 'Vợ phải nghe theo nhà chồng', 'Con trai được ưu ái hơn con gái'], 0,
                'Luật Hôn nhân và Gia đình quy định vợ chồng bình đẳng, con cái không phân biệt trai gái.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lĩnh vực với nội dung bình đẳng tương ứng.',
                [['Trước pháp luật', 'Không phân biệt đối xử, vi phạm đều bị xử lí'],
                 ['Trong hôn nhân, gia đình', 'Vợ chồng bình đẳng về quyền và nghĩa vụ'],
                 ['Trong lao động', 'Bình đẳng về việc làm, tiền lương, không phân biệt giới'],
                 ['Trong kinh doanh', 'Tự do kinh doanh ngành nghề pháp luật không cấm']],
                'Quyền bình đẳng của công dân thể hiện trên nhiều lĩnh vực của đời sống.');
            $this->matching($L, 'Nối mỗi tình huống với ĐÚNG (bình đẳng) hoặc SAI (phân biệt đối xử).',
                [['Tuyển dụng không phân biệt nam nữ', 'Đúng'],
                 ['Trả lương bằng nhau cho công việc như nhau', 'Đúng'],
                 ['Chỉ tuyển nam vì cho rằng nữ yếu hơn', 'Sai'],
                 ['Ưu tiên con trai đi học hơn con gái', 'Sai']],
                'Mọi hành vi phân biệt đối xử về giới tính đều vi phạm quyền bình đẳng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi văn bản với quyền bình đẳng được ghi nhận.',
                [['Hiến pháp', 'Mọi công dân đều bình đẳng trước pháp luật'],
                 ['Bộ luật Lao động', 'Bình đẳng trong lao động, cấm phân biệt đối xử'],
                 ['Luật Hôn nhân và Gia đình', 'Vợ chồng bình đẳng'],
                 ['Luật Doanh nghiệp', 'Bình đẳng trong kinh doanh']],
                'Quyền bình đẳng được ghi nhận trong Hiến pháp và cụ thể hoá trong các luật.');
            $this->matching($L, 'Nối mỗi hành vi với đánh giá VI PHẠM hoặc KHÔNG VI PHẠM quyền bình đẳng.',
                [['Ép con gái nghỉ học sớm đi làm', 'Vi phạm'],
                 ['Từ chối tuyển người khuyết tật đủ năng lực', 'Vi phạm'],
                 ['Chia tài sản thừa kế công bằng cho các con', 'Không vi phạm'],
                 ['Vợ chồng cùng quyết định việc lớn', 'Không vi phạm']],
                'Phân biệt đối xử, ép buộc trong gia đình và lao động là vi phạm quyền bình đẳng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BẢO ĐẢM BÌNH ĐẲNG hoặc PHÂN BIỆT ĐỐI XỬ.',
                [['Tuyển dụng dựa trên năng lực', 'Bảo đảm bình đẳng'],
                 ['Trả lương theo hiệu quả công việc', 'Bảo đảm bình đẳng'],
                 ['Tạo điều kiện cho người khuyết tật làm việc', 'Bảo đảm bình đẳng'],
                 ['Chỉ nhận nam giới vào làm', 'Phân biệt đối xử'],
                 ['Ép phụ nữ nghỉ việc khi mang thai', 'Phân biệt đối xử'],
                 ['Trả lương nữ thấp hơn nam cùng công việc', 'Phân biệt đối xử']],
                'Bảo đảm bình đẳng: đánh giá theo năng lực, không phân biệt giới tính, hoàn cảnh.');
            $this->sortQ($L, 'Kéo mỗi nội dung vào nhóm QUYỀN BÌNH ĐẲNG TRONG LAO ĐỘNG hoặc TRONG HÔN NHÂN.',
                [['Được lựa chọn nghề nghiệp', 'Trong lao động'], ['Được trả lương xứng đáng', 'Trong lao động'],
                 ['Được nghỉ thai sản theo quy định', 'Trong lao động'], ['Vợ chồng cùng nuôi dạy con', 'Trong hôn nhân'],
                 ['Cùng quyết định chi tiêu gia đình', 'Trong hôn nhân'], ['Bình đẳng về tài sản chung', 'Trong hôn nhân']],
                'Bình đẳng trong lao động gắn với việc làm; bình đẳng trong hôn nhân gắn với gia đình.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Mọi công dân bình đẳng trước pháp luật', 'Đúng'],
                 ['Nam nữ bình đẳng trong mọi lĩnh vực', 'Đúng'],
                 ['Người vi phạm có chức vụ được giảm nhẹ', 'Sai'],
                 ['Trẻ em gái cũng có quyền đi học như trẻ em trai', 'Đúng'],
                 ['Kinh doanh ma tuý là quyền tự do kinh doanh', 'Sai'],
                 ['Vợ chồng phải tôn trọng nhau', 'Đúng']],
                'Bình đẳng là quyền cơ bản; ngành nghề pháp luật cấm thì không ai được kinh doanh.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm THỂ HIỆN BÌNH ĐẲNG GIỚI hoặc CHƯA BÌNH ĐẲNG GIỚI.',
                [['Nữ sinh được học lập trình như nam sinh', 'Thể hiện bình đẳng giới'],
                 ['Phụ nữ làm lãnh đạo doanh nghiệp', 'Thể hiện bình đẳng giới'],
                 ['Chồng chia sẻ việc nhà với vợ', 'Thể hiện bình đẳng giới'],
                 ['"Con gái học nhiều làm gì"', 'Chưa bình đẳng giới'],
                 ['Chỉ con trai được thừa kế đất', 'Chưa bình đẳng giới'],
                 ['Nữ bị ép nghỉ việc vì mang thai', 'Chưa bình đẳng giới']],
                'Bình đẳng giới: nam nữ có vị trí, vai trò ngang nhau trong mọi lĩnh vực.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mọi công dân đều ___ đẳng trước pháp luật.', [[0, 'bình']],
                'Bình đẳng trước pháp luật là quyền cơ bản được Hiến pháp ghi nhận.');
            $this->fill($L, 'Trong lao động, nam nữ ___ đẳng về quyền làm việc và tiền lương.', [[0, 'bình']],
                'Cấm phân biệt đối xử về giới trong tuyển dụng và trả lương.');
            $this->fill($L, 'Vợ chồng bình đẳng về quyền và nghĩa vụ trong ___ nhân và gia đình.', [[0, 'hôn']],
                'Luật Hôn nhân và Gia đình bảo vệ sự bình đẳng giữa vợ và chồng.');
            $this->fill($L, 'Mọi cá nhân có quyền tự do kinh doanh những ngành nghề pháp luật không ___.', [[0, 'cấm']],
                'Tự do kinh doanh trong khuôn khổ pháp luật.');
        }
    }

    private function seedGdcd122(): void
    {
        $L = 'gdcd-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Công dân từ đủ bao nhiêu tuổi có quyền bầu cử?',
                ['Từ đủ 18 tuổi', 'Từ đủ 16 tuổi', 'Từ đủ 20 tuổi', 'Từ đủ 21 tuổi'], 0,
                'Công dân đủ 18 tuổi trở lên có quyền bầu cử theo quy định của pháp luật.');
            $this->quiz($L, 'Công dân từ đủ bao nhiêu tuổi có quyền ứng cử vào Quốc hội, HĐND?',
                ['Từ đủ 21 tuổi', 'Từ đủ 18 tuổi', 'Từ đủ 25 tuổi', 'Từ đủ 30 tuổi'], 0,
                'Quyền ứng cử: công dân từ đủ 21 tuổi trở lên.', 'trung_binh');
            $this->quiz($L, 'Khiếu nại và tố cáo khác nhau ở điểm cơ bản nào?',
                ['Khiếu nại bảo vệ quyền lợi của mình; tố cáo vì lợi ích chung, tố giác vi phạm',
                 'Khiếu nại là của cán bộ; tố cáo là của dân', 'Khiếu nại bằng miệng; tố cáo bằng văn bản', 'Không có điểm khác nhau'], 0,
                'Khiếu nại: đề nghị xem xét lại quyết định xâm phạm quyền lợi của mình. Tố cáo: báo hành vi vi phạm pháp luật gây thiệt hại chung.', 'trung_binh');
            $this->quiz($L, 'Khi thực hiện quyền khiếu nại, tố cáo, công dân phải tuân thủ yêu cầu nào?',
                ['Trung thực, đúng trình tự, không vu khống', 'Có thể bịa đặt để gây sức ép',
                 'Tố cáo nặc danh là tốt nhất', 'Kêu gọi đám đông gây rối'], 0,
                'Pháp luật nghiêm cấm lợi dụng khiếu nại, tố cáo để vu khống, xúc phạm người khác.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi quyền với độ tuổi hoặc điều kiện thực hiện.',
                [['Quyền bầu cử', 'Công dân từ đủ 18 tuổi'],
                 ['Quyền ứng cử', 'Công dân từ đủ 21 tuổi'],
                 ['Quyền khiếu nại', 'Khi quyền lợi của mình bị xâm phạm'],
                 ['Quyền tố cáo', 'Khi phát hiện hành vi vi phạm pháp luật']],
                'Các quyền dân chủ, khiếu nại, tố cáo được Hiến pháp ghi nhận.');
            $this->matching($L, 'Nối mỗi tình huống với quyền phù hợp: KHIẾU NẠI hay TỐ CÁO.',
                [['Bị xử phạt giao thông oan', 'Khiếu nại'],
                 ['Phát hiện cán bộ tham nhũng', 'Tố cáo'],
                 ['Bị thu hồi đất không đúng quy định', 'Khiếu nại'],
                 ['Thấy cơ sở xả thải ra sông', 'Tố cáo']],
                'Bảo vệ quyền lợi của mình thì khiếu nại; tố giác vi phạm vì lợi ích chung thì tố cáo.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hành vi với ĐÚNG hoặc SAI khi thực hiện quyền.',
                [['Tố cáo trung thực, có chứng cứ', 'Đúng'],
                 ['Khiếu nại đúng cơ quan có thẩm quyền', 'Đúng'],
                 ['Bịa đặt để vu khống người khác', 'Sai'],
                 ['Lợi dụng tố cáo để tống tiền', 'Sai']],
                'Thực hiện quyền phải trung thực; vu khống, lợi dụng là vi phạm pháp luật.');
            $this->matching($L, 'Nối mỗi nguyên tắc bầu cử với nội dung.',
                [['Phổ thông', 'Mọi công dân đủ điều kiện đều được bầu cử'],
                 ['Bình đẳng', 'Mỗi cử tri một phiếu, giá trị ngang nhau'],
                 ['Trực tiếp', 'Cử tri trực tiếp bỏ phiếu'],
                 ['Bỏ phiếu kín', 'Không ai được biết nội dung phiếu của mình']],
                'Bầu cử đại biểu Quốc hội và HĐND theo 4 nguyên tắc này.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm NÊN KHIẾU NẠI hoặc NÊN TỐ CÁO.',
                [['Bị tính tiền điện sai cao bất thường', 'Nên khiếu nại'],
                 ['Bị kỷ luật oan ở cơ quan', 'Nên khiếu nại'],
                 ['Thấy hàng xóm đổ trộm rác thải', 'Nên tố cáo'],
                 ['Phát hiện điểm bán hàng giả', 'Nên tố cáo'],
                 ['Bị phạt hành chính không đúng lỗi', 'Nên khiếu nại'],
                 ['Thấy cán bộ nhận hối lộ', 'Nên tố cáo']],
                'Quyền lợi mình bị xâm phạm: khiếu nại; phát hiện vi phạm hại chung: tố cáo.');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm THỰC HIỆN ĐÚNG hoặc LỢI DỤNG quyền.',
                [['Gửi đơn đúng cơ quan thẩm quyền', 'Thực hiện đúng'],
                 ['Cung cấp chứng cứ trung thực', 'Thực hiện đúng'],
                 ['Chờ giải quyết theo trình tự', 'Thực hiện đúng'],
                 ['Bịa chuyện vu khống lãnh đạo', 'Lợi dụng quyền'],
                 ['Tụ tập đông người gây rối', 'Lợi dụng quyền'],
                 ['Đe doạ người bị tố cáo', 'Lợi dụng quyền']],
                'Thực hiện quyền đúng pháp luật; lợi dụng quyền để vu khống, gây rối là vi phạm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Đủ 18 tuổi được đi bầu cử', 'Đúng'], ['Đủ 21 tuổi được ứng cử', 'Đúng'],
                 ['Mỗi cử tri chỉ có một phiếu bầu', 'Đúng'], ['Có thể nhờ người khác bỏ phiếu hộ', 'Sai'],
                 ['Tố cáo phải trung thực', 'Đúng'], ['Khiếu nại sai sự thật không sao', 'Sai']],
                'Bầu cử: phổ thông, bình đẳng, trực tiếp, bỏ phiếu kín; khiếu nại, tố cáo phải trung thực.');
            $this->sortQ($L, 'Kéo mỗi quyền vào nhóm QUYỀN DÂN CHỦ hoặc QUYỀN KHIẾU NẠI, TỐ CÁO.',
                [['Quyền bầu cử', 'Quyền dân chủ'], ['Quyền ứng cử', 'Quyền dân chủ'],
                 ['Quyền biểu quyết', 'Quyền dân chủ'], ['Quyền khiếu nại quyết định sai', 'Quyền khiếu nại, tố cáo'],
                 ['Quyền tố cáo tham nhũng', 'Quyền khiếu nại, tố cáo'], ['Quyền được giải quyết đơn', 'Quyền khiếu nại, tố cáo']],
                'Quyền dân chủ: tham gia quản lí nhà nước; khiếu nại, tố cáo: bảo vệ quyền lợi và đấu tranh chống vi phạm.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Công dân từ đủ 18 tuổi có quyền bầu ___.', [[0, 'cử']],
                'Quyền bầu cử là quyền dân chủ cơ bản của công dân.');
            $this->fill($L, 'Công dân từ đủ 21 tuổi có quyền ___ cử.', [[0, 'ứng']],
                'Đủ 21 tuổi trở lên được ứng cử đại biểu Quốc hội, HĐND.');
            $this->fill($L, 'Khi quyền lợi của mình bị xâm phạm, công dân có quyền khiếu ___.', [[0, 'nại']],
                'Khiếu nại nhằm bảo vệ quyền và lợi ích hợp pháp của chính mình.');
            $this->fill($L, 'Phát hiện hành vi vi phạm pháp luật, công dân có quyền ___ cáo.', [[0, 'tố']],
                'Tố cáo góp phần đấu tranh chống vi phạm, bảo vệ lợi ích chung.');
        }
    }

    private function seedGdcd123(): void
    {
        $L = 'gdcd-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Vì sao công dân phải tuân thủ pháp luật?',
                ['Vì pháp luật bảo đảm trật tự, an toàn xã hội và quyền lợi của mọi người',
                 'Vì sợ bị phạt', 'Vì đó là thói quen', 'Vì mọi người đều làm vậy'], 0,
                'Tuân thủ pháp luật là nghĩa vụ và cũng là biểu hiện của nếp sống văn minh.');
            $this->quiz($L, 'Thuế là gì?',
                ['Khoản thu bắt buộc của Nhà nước từ tổ chức, cá nhân để chi cho hoạt động công',
                 'Khoản đóng góp tự nguyện', 'Tiền phạt vi phạm', 'Tiền quyên góp từ thiện'], 0,
                'Thuế là nguồn thu chủ yếu của ngân sách nhà nước, dùng để xây dựng đất nước.');
            $this->quiz($L, 'Loại thuế nào đánh vào hàng hoá, dịch vụ mà người tiêu dùng đang phải trả hằng ngày?',
                ['Thuế giá trị gia tăng (VAT)', 'Thuế thu nhập cá nhân', 'Thuế thu nhập doanh nghiệp', 'Thuế môn bài'], 0,
                'VAT đánh vào giá trị tăng thêm của hàng hoá, dịch vụ; người tiêu dùng cuối cùng chịu thuế này.', 'trung_binh');
            $this->quiz($L, 'Hành vi trốn thuế bị xử lí như thế nào?',
                ['Bị xử phạt hành chính hoặc truy cứu trách nhiệm hình sự',
                 'Chỉ bị nhắc nhở', 'Không bị xử lí nếu số tiền nhỏ', 'Được miễn nếu là lần đầu'], 0,
                'Trốn thuế là vi phạm pháp luật, tuỳ mức độ bị phạt tiền hoặc phạt tù.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại thuế với đối tượng chịu thuế.',
                [['Thuế thu nhập cá nhân', 'Người có thu nhập từ tiền lương, kinh doanh'],
                 ['Thuế giá trị gia tăng', 'Hàng hoá, dịch vụ tiêu dùng'],
                 ['Thuế thu nhập doanh nghiệp', 'Lợi nhuận của doanh nghiệp'],
                 ['Thuế tiêu thụ đặc biệt', 'Rượu, bia, thuốc lá, ô tô, xe máy phân khối lớn']],
                'Mỗi loại thuế đánh vào một đối tượng khác nhau.', 'trung_binh');
            $this->matching($L, 'Nối mỗi khoản chi ngân sách với mục đích.',
                [['Xây cầu đường, trường học', 'Phát triển kinh tế – xã hội'],
                 ['Trả lương cán bộ, công chức', 'Hoạt động bộ máy nhà nước'],
                 ['Trợ cấp người nghèo, chính sách xã hội', 'An sinh xã hội'],
                 ['Mua sắm quốc phòng', 'Bảo đảm quốc phòng, an ninh']],
                'Tiền thuế được dùng cho các hoạt động công vì lợi ích chung.');
            $this->matching($L, 'Nối mỗi nghĩa vụ với nội dung tương ứng.',
                [['Tuân thủ pháp luật', 'Sống và làm việc theo Hiến pháp, pháp luật'],
                 ['Nộp thuế', 'Kê khai và nộp thuế đầy đủ, đúng hạn'],
                 ['Tôn trọng quyền người khác', 'Không xâm phạm tự do, lợi ích hợp pháp của người khác'],
                 ['Bảo vệ môi trường', 'Không xả thải bừa bãi, giữ gìn vệ sinh chung']],
                'Nghĩa vụ của công dân gắn liền với quyền của công dân.');
            $this->matching($L, 'Nối mỗi hành vi với ĐÚNG (thực hiện nghĩa vụ) hoặc SAI.',
                [['Kê khai thuế trung thực', 'Đúng'],
                 ['Chấp hành tín hiệu giao thông', 'Đúng'],
                 ['Mua hoá đơn để trốn thuế', 'Sai'],
                 ['Xả rác thải công nghiệp ra sông', 'Sai']],
                'Thực hiện nghĩa vụ là trách nhiệm và cũng là danh dự của công dân.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm THỰC HIỆN NGHĨA VỤ hoặc VI PHẠM NGHĨA VỤ.',
                [['Nộp thuế đúng hạn', 'Thực hiện nghĩa vụ'], ['Chấp hành luật giao thông', 'Thực hiện nghĩa vụ'],
                 ['Đi bầu cử đầy đủ', 'Thực hiện nghĩa vụ'], ['Trốn thuế', 'Vi phạm nghĩa vụ'],
                 ['Buôn lậu hàng hoá', 'Vi phạm nghĩa vụ'], ['Phá hoại tài sản công', 'Vi phạm nghĩa vụ']],
                'Thực hiện nghĩa vụ: tuân thủ pháp luật; vi phạm: trốn thuế, buôn lậu, phá hoại.');
            $this->sortQ($L, 'Kéo mỗi khoản tiền vào nhóm THUẾ hoặc KHÔNG PHẢI THUẾ.',
                [['VAT khi mua hàng', 'Thuế'], ['Thuế thu nhập từ lương', 'Thuế'],
                 ['Thuế trước bạ khi mua xe', 'Thuế'], ['Tiền phạt vi phạm giao thông', 'Không phải thuế'],
                 ['Tiền ủng hộ từ thiện', 'Không phải thuế'], ['Tiền học phí', 'Không phải thuế']],
                'Thuế là khoản thu bắt buộc của Nhà nước; tiền phạt, từ thiện, học phí không phải thuế.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Nộp thuế là nghĩa vụ của công dân', 'Đúng'],
                 ['Tiền thuế dùng xây dựng đất nước', 'Đúng'],
                 ['Trốn thuế không bị xử lí', 'Sai'],
                 ['Mọi công dân phải tuân thủ pháp luật', 'Đúng'],
                 ['Chỉ doanh nghiệp mới phải nộp thuế', 'Sai'],
                 ['Tuân thủ pháp luật là tự nguyện', 'Sai']],
                'Nộp thuế là nghĩa vụ bắt buộc; cá nhân có thu nhập cũng phải nộp thuế.');
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm TRUNG THỰC VỀ THUẾ hoặc GIAN DỐI VỀ THUẾ.',
                [['Kê khai đúng doanh thu', 'Trung thực về thuế'], ['Nộp thuế đúng thời hạn', 'Trung thực về thuế'],
                 ['Lấy hoá đơn khi mua hàng', 'Trung thực về thuế'], ['Khai thấp doanh thu để nộp ít thuế', 'Gian dối về thuế'],
                 ['Mua bán hoá đơn khống', 'Gian dối về thuế'], ['Hai sổ sách kế toán', 'Gian dối về thuế']],
                'Trung thực về thuế là nghĩa vụ; gian dối về thuế bị xử phạt nghiêm.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mọi công dân có nghĩa vụ tuân thủ Hiến pháp và ___ luật.', [[0, 'pháp']],
                'Tuân thủ pháp luật là nghĩa vụ cơ bản của mọi công dân.');
            $this->fill($L, 'Thuế là nguồn thu chủ yếu của ngân sách ___ nước.', [[0, 'nhà']],
                'Ngân sách nhà nước chi cho phát triển, quốc phòng, an sinh xã hội.');
            $this->fill($L, 'Thuế đánh vào hàng hoá, dịch vụ tiêu dùng hằng ngày là thuế giá trị ___ tăng.', [[0, 'gia']],
                'VAT hiện nay ở Việt Nam phổ biến là 10% (một số mặt hàng được giảm).');
            $this->fill($L, 'Hành vi ___ thuế là vi phạm pháp luật và bị xử lí nghiêm.', [[0, 'trốn']],
                'Trốn thuế tuỳ mức độ bị phạt hành chính hoặc truy cứu trách nhiệm hình sự.');
        }
    }

    private function seedGdcd124(): void
    {
        $L = 'gdcd-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bảo vệ Tổ quốc được xác định như thế nào đối với công dân?',
                ['Là nghĩa vụ thiêng liêng và quyền cao quý của công dân',
                 'Là việc riêng của quân đội', 'Là nghĩa vụ tự nguyện', 'Chỉ áp dụng khi có chiến tranh'], 0,
                'Hiến pháp khẳng định bảo vệ Tổ quốc là nghĩa vụ thiêng liêng và quyền cao quý của công dân.');
            $this->quiz($L, 'Độ tuổi gọi nhập ngũ thực hiện nghĩa vụ quân sự là bao nhiêu?',
                ['Nam từ đủ 18 đến hết 25 tuổi (27 tuổi nếu đã tốt nghiệp cao đẳng, đại học)',
                 'Mọi công dân từ 16 tuổi', 'Nam từ 20 đến 30 tuổi', 'Chỉ sinh viên đại học'], 0,
                'Luật Nghĩa vụ quân sự quy định độ tuổi gọi nhập ngũ như trên.', 'trung_binh');
            $this->quiz($L, 'Thời gian phục vụ tại ngũ trong thời bình là bao lâu?',
                ['24 tháng', '12 tháng', '18 tháng', '36 tháng'], 0,
                'Thời hạn phục vụ tại ngũ trong thời bình của hạ sĩ quan, binh sĩ là 24 tháng.');
            $this->quiz($L, 'Hành vi nào sau đây thể hiện nghĩa vụ bảo vệ Tổ quốc?',
                ['Tố giác hành vi xâm phạm an ninh quốc gia', 'Trốn nghĩa vụ quân sự',
                 'Tuyên truyền chống Nhà nước', 'Buôn lậu qua biên giới'], 0,
                'Tố giác hành vi xâm phạm an ninh quốc gia là góp phần bảo vệ Tổ quốc.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nội dung với yêu cầu của nghĩa vụ bảo vệ Tổ quốc.',
                [['Trung thành với Tổ quốc', 'Tuyệt đối trung thành, sẵn sàng chiến đấu hi sinh'],
                 ['Nghĩa vụ quân sự', 'Nhập ngũ khi được gọi, phục vụ 24 tháng'],
                 ['Bảo vệ an ninh quốc gia', 'Giữ gìn trật tự an toàn xã hội'],
                 ['Tố giác vi phạm', 'Báo hành vi xâm phạm an ninh quốc gia']],
                'Nghĩa vụ bảo vệ Tổ quốc gồm nhiều nội dung cụ thể.', 'trung_binh');
            $this->matching($L, 'Nối mỗi lực lượng với vai trò bảo vệ Tổ quốc.',
                [['Quân đội nhân dân', 'Lực lượng nòng cốt bảo vệ Tổ quốc'],
                 ['Công an nhân dân', 'Bảo vệ an ninh quốc gia, trật tự an toàn xã hội'],
                 ['Dân quân tự vệ', 'Lực lượng vũ trang quần chúng ở cơ sở'],
                 ['Dự bị động viên', 'Lực lượng sẵn sàng bổ sung cho quân đội']],
                'Bảo vệ Tổ quốc là sự nghiệp của toàn dân với các lực lượng khác nhau.');
            $this->matching($L, 'Nối mỗi hành vi với ĐÚNG (bảo vệ Tổ quốc) hoặc SAI.',
                [['Tham gia nghĩa vụ quân sự', 'Đúng'],
                 ['Tuyên truyền, xuyên tạc chống Nhà nước', 'Sai'],
                 ['Trốn tránh nghĩa vụ quân sự', 'Sai'],
                 ['Giữ gìn bí mật nhà nước', 'Đúng']],
                'Trốn nghĩa vụ quân sự, chống phá Nhà nước là vi phạm pháp luật.');
            $this->matching($L, 'Nối mỗi khái niệm với nội dung tương ứng.',
                [['Nghĩa vụ quân sự', 'Nghĩa vụ vẻ vang, nhập ngũ bảo vệ Tổ quốc'],
                 ['Dân quân tự vệ', 'Lực lượng vũ trang ở xã, phường, cơ quan'],
                 ['An ninh quốc gia', 'Sự ổn định, an toàn của đất nước'],
                 ['Phản động', 'Hành vi chống phá Nhà nước, chế độ']],
                'Hiểu đúng các khái niệm để thực hiện tốt nghĩa vụ công dân.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm GÓP PHẦN BẢO VỆ TỔ QUỐC hoặc ĐI NGƯỢC LẠI.',
                [['Sẵn sàng nhập ngũ khi được gọi', 'Góp phần bảo vệ Tổ quốc'],
                 ['Tố giác kẻ xấu xâm phạm an ninh', 'Góp phần bảo vệ Tổ quốc'],
                 ['Giữ gìn bí mật nhà nước', 'Góp phần bảo vệ Tổ quốc'],
                 ['Trốn nghĩa vụ quân sự', 'Đi ngược lại'],
                 ['Tuyên truyền thông tin sai sự thật', 'Đi ngược lại'],
                 ['Tiếp tay cho buôn lậu biên giới', 'Đi ngược lại']],
                'Mỗi công dân đều có thể góp phần bảo vệ Tổ quốc bằng việc làm cụ thể.');
            $this->sortQ($L, 'Kéo mỗi đối tượng vào nhóm TRONG ĐỘ TUỔI GỌI NHẬP NGŨ hoặc KHÔNG.',
                [['Nam 20 tuổi, sức khoẻ tốt', 'Trong độ tuổi gọi nhập ngũ'],
                 ['Nam 23 tuổi đã tốt nghiệp đại học', 'Trong độ tuổi gọi nhập ngũ'],
                 ['Nam 15 tuổi', 'Không'], ['Nữ 20 tuổi (tự nguyện)', 'Không bắt buộc'],
                 ['Nam 30 tuổi', 'Không'], ['Người đang mắc bệnh hiểm nghèo', 'Không']],
                'Nam 18–25 tuổi (27 với tốt nghiệp CĐ, ĐH) trong diện gọi nhập ngũ; nữ tự nguyện.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Bảo vệ Tổ quốc là nghĩa vụ của mọi công dân', 'Đúng'],
                 ['Thời gian tại ngũ thời bình là 24 tháng', 'Đúng'],
                 ['Trốn nghĩa vụ quân sự không bị xử lí', 'Sai'],
                 ['Nữ giới không thể tham gia bảo vệ Tổ quốc', 'Sai'],
                 ['Tố giác tội phạm là góp phần bảo vệ Tổ quốc', 'Đúng'],
                 ['Bảo vệ Tổ quốc chỉ là việc của bộ đội', 'Sai']],
                'Bảo vệ Tổ quốc là sự nghiệp toàn dân; ai cũng có thể đóng góp.');
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm ĐƯỢC KHEN THƯỞNG hoặc BỊ XỬ LÍ.',
                [['Dũng cảm bắt cướp', 'Được khen thưởng'], ['Tình nguyện nhập ngũ', 'Được khen thưởng'],
                 ['Cứu người trong thiên tai', 'Được khen thưởng'], ['Trốn nghĩa vụ quân sự', 'Bị xử lí'],
                 ['Chống người thi hành công vụ', 'Bị xử lí'], ['Tiết lộ bí mật nhà nước', 'Bị xử lí']],
                'Việc tốt được khen thưởng; vi phạm nghĩa vụ bị xử lí theo pháp luật.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bảo vệ Tổ quốc là nghĩa vụ thiêng liêng và ___ cao quý của công dân.', [[0, 'quyền']],
                'Hiến pháp năm 2013 khẳng định nội dung này.');
            $this->fill($L, 'Công dân nam từ đủ 18 tuổi đến hết 25 tuổi trong độ tuổi gọi ___ ngũ.', [[0, 'nhập']],
                'Được gọi nhập ngũ là thực hiện nghĩa vụ quân sự vẻ vang.');
            $this->fill($L, 'Thời gian phục vụ tại ngũ trong thời bình là ___ tháng.', [[0, '24']],
                'Hạ sĩ quan, binh sĩ phục vụ tại ngũ 24 tháng trong thời bình.');
            $this->fill($L, 'Hành vi trốn nghĩa vụ quân sự sẽ bị xử ___ theo quy định của pháp luật.', [[0, 'phạt']],
                'Trốn nghĩa vụ quân sự bị xử phạt hành chính hoặc truy cứu trách nhiệm hình sự.');
        }
    }

    // ================= TIN HỌC LỚP 10 =================

    private function seedTin101(): void
    {
        $L = 'tin-hoc-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Để tạo mục lục tự động trong Word, trước tiên cần làm gì?',
                ['Áp dụng Heading Styles (Heading 1, Heading 2...) cho các tiêu đề',
                 'Tô đậm toàn bộ văn bản', 'Chèn số trang', 'In đậm tiêu đề bằng tay'], 0,
                'Word dựa vào các Heading Styles để nhận biết tiêu đề và tạo mục lục tự động.');
            $this->quiz($L, 'Sau khi tạo mục lục tự động, nếu sửa nội dung văn bản thì làm thế nào để cập nhật mục lục?',
                ['Bấm chuột phải vào mục lục, chọn Update Table', 'Tạo lại mục lục từ đầu',
                 'Không thể cập nhật', 'Xoá mục lục rồi gõ tay lại'], 0,
                'Update Table giúp cập nhật số trang và tiêu đề mới mà không cần tạo lại.');
            $this->quiz($L, 'Phím tắt nào dùng để in đậm văn bản trong Word?',
                ['Ctrl + B', 'Ctrl + I', 'Ctrl + U', 'Ctrl + D'], 0,
                'Ctrl + B: đậm (Bold); Ctrl + I: nghiêng (Italic); Ctrl + U: gạch chân (Underline).', 'trung_binh');
            $this->quiz($L, 'Để căn đều hai bên đoạn văn trong Word, ta chọn kiểu căn lề nào?',
                ['Justify', 'Left', 'Center', 'Right'], 0,
                'Justify căn đều hai bên; Left căn trái; Center căn giữa; Right căn phải.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phím tắt với chức năng trong Word.',
                [['Ctrl + B', 'In đậm văn bản'],
                 ['Ctrl + I', 'In nghiêng văn bản'],
                 ['Ctrl + U', 'Gạch chân văn bản'],
                 ['Ctrl + S', 'Lưu văn bản']],
                'Ghi nhớ phím tắt giúp soạn thảo nhanh hơn.', 'trung_binh');
            $this->matching($L, 'Nối mỗi kiểu căn lề với tác dụng.',
                [['Left', 'Căn thẳng lề trái'],
                 ['Center', 'Căn giữa dòng'],
                 ['Right', 'Căn thẳng lề phải'],
                 ['Justify', 'Căn đều hai bên']],
                'Chọn kiểu căn lề phù hợp giúp văn bản đẹp, dễ đọc.');
            $this->matching($L, 'Nối mỗi thẻ trong Word với nhóm chức năng.',
                [['Home', 'Định dạng kí tự, đoạn văn'],
                 ['Insert', 'Chèn bảng, hình ảnh, số trang'],
                 ['References', 'Tạo mục lục, chú thích'],
                 ['Layout', 'Định dạng trang: lề, hướng giấy, khổ giấy']],
                'Mỗi thẻ (tab) trên thanh Ribbon quản lí một nhóm chức năng.');
            $this->matching($L, 'Nối mỗi bước với thứ tự tạo mục lục tự động.',
                [['Bước 1', 'Áp dụng Heading cho các tiêu đề'],
                 ['Bước 2', 'Đặt con trỏ nơi muốn chèn mục lục'],
                 ['Bước 3', 'Vào References > Table of Contents'],
                 ['Bước 4', 'Chọn mẫu mục lục, bấm OK']],
                'Thực hiện đúng trình tự để có mục lục tự động chính xác.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm ĐỊNH DẠNG KÍ TỰ hoặc ĐỊNH DẠNG ĐOẠN VĂN.',
                [['Đổi phông chữ', 'Định dạng kí tự'], ['Tăng cỡ chữ', 'Định dạng kí tự'],
                 ['Đổi màu chữ', 'Định dạng kí tự'], ['Căn lề đoạn văn', 'Định dạng đoạn văn'],
                 ['Giãn dòng 1.5', 'Định dạng đoạn văn'], ['Thụt đầu dòng', 'Định dạng đoạn văn']],
                'Định dạng kí tự tác động lên chữ; định dạng đoạn văn tác động lên cả đoạn.');
            $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm ĐỊNH DẠNG TRANG hoặc CHÈN ĐỐI TƯỢNG.',
                [['Chỉnh lề trang', 'Định dạng trang'], ['Chọn khổ giấy A4', 'Định dạng trang'],
                 ['Xoay ngang trang giấy', 'Định dạng trang'], ['Chèn số trang', 'Chèn đối tượng'],
                 ['Chèn hình ảnh', 'Chèn đối tượng'], ['Chèn bảng', 'Chèn đối tượng']],
                'Định dạng trang (Layout) khác với chèn đối tượng (Insert).');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Heading Styles giúp tạo mục lục tự động', 'Đúng'],
                 ['Update Table cập nhật lại mục lục', 'Đúng'],
                 ['Ctrl + B để in nghiêng', 'Sai'],
                 ['Justify là căn đều hai bên', 'Đúng'],
                 ['Mục lục tự động không cập nhật được', 'Sai'],
                 ['Nên dùng Heading thay vì tự tô đậm tiêu đề', 'Đúng']],
                'Dùng Heading Styles là cách làm văn bản chuyên nghiệp.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm TRƯỚC KHI TẠO MỤC LỤC hoặc SAU KHI TẠO MỤC LỤC.',
                [['Áp dụng Heading cho tiêu đề', 'Trước khi tạo mục lục'],
                 ['Soạn thảo xong nội dung', 'Trước khi tạo mục lục'],
                 ['Chọn References > Table of Contents', 'Sau khi tạo mục lục'],
                 ['Bấm Update Table khi sửa bài', 'Sau khi tạo mục lục'],
                 ['Định dạng Heading đẹp', 'Trước khi tạo mục lục'],
                 ['Kiểm tra số trang trong mục lục', 'Sau khi tạo mục lục']],
                'Chuẩn bị Heading trước, tạo mục lục sau, cập nhật khi sửa đổi.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Để tạo mục lục tự động, trước tiên phải áp dụng ___ Styles cho các tiêu đề.', [[0, 'Heading']],
                'Word nhận biết tiêu đề qua Heading 1, Heading 2, Heading 3...');
            $this->fill($L, 'Chức năng tạo mục lục nằm trong thẻ ___ (References).', [[0, 'References']],
                'Vào References > Table of Contents để chèn mục lục.');
            $this->fill($L, 'Phím tắt Ctrl + ___ dùng để in đậm văn bản.', [[0, 'B']],
                'B là viết tắt của Bold (in đậm).');
            $this->fill($L, 'Kiểu căn lề ___ giúp văn bản thẳng đều cả hai bên.', [[0, 'Justify']],
                'Justify thường dùng cho thân bài văn bản hành chính.');
        }
    }

    private function seedTin102(): void
    {
        $L = 'tin-hoc-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Để chèn một bảng 3 hàng 4 cột trong Word, ta vào đâu?',
                ['Insert > Table, chọn 4 cột 3 hàng', 'Home > Table', 'Layout > Table', 'File > Table'], 0,
                'Insert > Table cho phép chọn số hàng, số cột hoặc vẽ bảng tuỳ ý.');
            $this->quiz($L, 'Muốn gộp nhiều ô trong bảng thành một ô, ta dùng lệnh nào?',
                ['Merge Cells', 'Split Cells', 'Delete Cells', 'Insert Cells'], 0,
                'Merge Cells gộp các ô đã chọn; Split Cells tách một ô thành nhiều ô.', 'trung_binh');
            $this->quiz($L, 'Để hình ảnh không che mất chữ, nên chọn kiểu bao quanh văn bản nào?',
                ['Square hoặc Tight', 'In line with Text luôn tốt nhất', 'Behind Text cho mọi trường hợp', 'Không thể chỉnh'], 0,
                'Square/Tight cho chữ bao quanh hình; In line đặt hình như một kí tự trong dòng.');
            $this->quiz($L, 'Để đánh số tự động "Bảng 1", "Hình 1" cho bảng biểu và hình ảnh, ta dùng chức năng nào?',
                ['Insert Caption', 'Insert Table', 'Insert Picture', 'Insert Header'], 0,
                'References > Insert Caption tạo nhãn đánh số tự động cho bảng, hình, công thức.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lệnh với chức năng khi làm việc với bảng.',
                [['Merge Cells', 'Gộp các ô đã chọn thành một ô'],
                 ['Split Cells', 'Tách một ô thành nhiều ô'],
                 ['Insert Row', 'Chèn thêm hàng'],
                 ['Delete Column', 'Xoá cột đã chọn']],
                'Các lệnh xử lí bảng nằm trong thẻ Layout khi đang chọn bảng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi kiểu bao quanh với đặc điểm.',
                [['In Line with Text', 'Hình nằm như một kí tự trong dòng'],
                 ['Square', 'Chữ bao quanh theo hình vuông'],
                 ['Tight', 'Chữ bao sát theo hình dạng ảnh'],
                 ['Behind Text', 'Hình nằm dưới lớp chữ']],
                'Chọn kiểu bao quanh phù hợp giúp trình bày đẹp.');
            $this->matching($L, 'Nối mỗi đối tượng với thẻ chèn tương ứng.',
                [['Bảng', 'Insert > Table'],
                 ['Hình ảnh', 'Insert > Pictures'],
                 ['Biểu đồ', 'Insert > Chart'],
                 ['Sơ đồ SmartArt', 'Insert > SmartArt']],
                'Thẻ Insert tập trung các lệnh chèn đối tượng vào văn bản.');
            $this->matching($L, 'Nối mỗi thao tác với mục đích.',
                [['Tô màu nền cho ô tiêu đề', 'Làm nổi bật hàng tiêu đề'],
                 ['Kẻ viền cho bảng', 'Phân định rõ các ô'],
                 ['Căn giữa nội dung trong ô', 'Trình bày cân đối'],
                 ['Đặt Caption cho bảng', 'Đánh số, tiện tham chiếu']],
                'Định dạng bảng hợp lí giúp người đọc dễ theo dõi.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm XỬ LÍ BẢNG hoặc XỬ LÍ HÌNH ẢNH.',
                [['Gộp ô', 'Xử lí bảng'], ['Tách ô', 'Xử lí bảng'],
                 ['Thêm hàng', 'Xử lí bảng'], ['Thay đổi kích thước ảnh', 'Xử lí hình ảnh'],
                 ['Chọn kiểu bao quanh chữ', 'Xử lí hình ảnh'], ['Cắt xén ảnh', 'Xử lí hình ảnh']],
                'Thao tác bảng khác thao tác hình ảnh; chọn đúng đối tượng trước khi chỉnh.');
            $this->sortQ($L, 'Kéo mỗi bước vào nhóm TẠO BẢNG hoặc CHÈN HÌNH.',
                [['Vào Insert > Table', 'Tạo bảng'], ['Chọn số hàng, số cột', 'Tạo bảng'],
                 ['Nhập dữ liệu vào các ô', 'Tạo bảng'], ['Vào Insert > Pictures', 'Chèn hình'],
                 ['Chọn tệp hình ảnh', 'Chèn hình'], ['Chỉnh vị trí, kích thước', 'Chèn hình']],
                'Tạo bảng: Insert > Table; chèn hình: Insert > Pictures.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Merge Cells dùng để gộp ô', 'Đúng'], ['Split Cells dùng để tách ô', 'Đúng'],
                 ['Caption đánh số tự động cho bảng, hình', 'Đúng'], ['Không thể thay đổi kích thước ảnh trong Word', 'Sai'],
                 ['Bảng trong Word không kẻ được viền', 'Sai'], ['SmartArt dùng để vẽ sơ đồ', 'Đúng']],
                'Word hỗ trợ đầy đủ công cụ xử lí bảng và hình ảnh.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đối tượng vào nhóm NÊN ĐẶT CAPTION hoặc KHÔNG CẦN.',
                [['Bảng số liệu quan trọng', 'Nên đặt Caption'], ['Hình minh hoạ trong báo cáo', 'Nên đặt Caption'],
                 ['Biểu đồ kết quả', 'Nên đặt Caption'], ['Ảnh trang trí góc trang', 'Không cần'],
                 ['Hình nền mờ trang trí', 'Không cần'], ['Biểu tượng nhỏ minh hoạ', 'Không cần']],
                'Bảng, hình quan trọng nên đặt Caption để tham chiếu trong văn bản.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Để gộp nhiều ô trong bảng thành một ô, ta dùng lệnh ___ Cells.', [[0, 'Merge']],
                'Merge Cells nằm trong thẻ Layout khi đang chọn bảng.');
            $this->fill($L, 'Để tách một ô thành nhiều ô, ta dùng lệnh ___ Cells.', [[0, 'Split']],
                'Split Cells cho phép chia ô theo số hàng, số cột tuỳ chọn.');
            $this->fill($L, 'Chức năng Insert ___ tạo nhãn đánh số tự động cho bảng và hình.', [[0, 'Caption']],
                'Caption giúp tham chiếu "như Bảng 1 cho thấy..." chính xác.');
            $this->fill($L, 'Kiểu bao quanh ___ đặt hình ảnh như một kí tự trong dòng văn bản.', [[0, 'In Line']],
                'In Line with Text là kiểu mặc định khi chèn hình.');
        }
    }

    private function seedTin103(): void
    {
        $L = 'tin-hoc-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Công thức trong Excel luôn bắt đầu bằng kí tự nào?',
                ['Dấu =', 'Dấu +', 'Dấu @', 'Dấu #'], 0,
                'Mọi công thức trong Excel đều bắt đầu bằng dấu bằng (=).');
            $this->quiz($L, 'Công thức =SUM(B2:B10) có ý nghĩa gì?',
                ['Tính tổng các giá trị từ ô B2 đến ô B10', 'Tính tổng hai ô B2 và B10',
                 'Đếm số ô từ B2 đến B10', 'Tính trung bình từ B2 đến B10'], 0,
                'SUM(vùng) tính tổng; B2:B10 là vùng gồm các ô từ B2 đến B10.');
            $this->quiz($L, 'Hàm nào dùng để tìm giá trị lớn nhất trong một vùng dữ liệu?',
                ['MAX', 'MIN', 'SUM', 'AVERAGE'], 0,
                'MAX tìm giá trị lớn nhất; MIN tìm giá trị nhỏ nhất; AVERAGE tính trung bình.', 'trung_binh');
            $this->quiz($L, 'Địa chỉ ô ở cột C, hàng 5 được viết như thế nào?',
                ['C5', '5C', 'C-5', 'CC5'], 0,
                'Địa chỉ ô = tên cột + số hàng, ví dụ C5.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hàm với chức năng của nó.',
                [['SUM', 'Tính tổng các giá trị'],
                 ['AVERAGE', 'Tính giá trị trung bình cộng'],
                 ['MAX', 'Tìm giá trị lớn nhất'],
                 ['MIN', 'Tìm giá trị nhỏ nhất']],
                'Bốn hàm cơ bản hay dùng nhất trong Excel.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cách viết với ý nghĩa.',
                [['B3', 'Ô ở cột B, hàng 3'],
                 ['B2:B10', 'Vùng từ ô B2 đến ô B10'],
                 ['=A1+A2', 'Công thức cộng hai ô A1 và A2'],
                 ['=SUM(C1:C5)', 'Tổng các ô từ C1 đến C5']],
                'Địa chỉ ô và vùng dữ liệu là kiến thức nền khi dùng hàm.');
            $this->matching($L, 'Nối mỗi hàm với ví dụ sử dụng.',
                [['SUM', '=SUM(D2:D10) tính tổng điểm'],
                 ['AVERAGE', '=AVERAGE(D2:D10) tính điểm trung bình'],
                 ['COUNT', '=COUNT(D2:D10) đếm số bài có điểm'],
                 ['MAX', '=MAX(D2:D10) tìm điểm cao nhất']],
                'COUNT đếm các ô chứa số trong vùng dữ liệu.');
            $this->matching($L, 'Nối mỗi thành phần với vai trò trong bảng tính.',
                [['Ô (cell)', 'Giao của một cột và một hàng'],
                 ['Hàng (row)', 'Tập hợp các ô theo chiều ngang'],
                 ['Cột (column)', 'Tập hợp các ô theo chiều dọc'],
                 ['Trang tính (sheet)', 'Bảng chứa các ô, hàng, cột']],
                'Hiểu cấu trúc bảng tính giúp thao tác chính xác.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi công thức vào nhóm TÍNH TỔNG hoặc TÌM GIÁ TRỊ LỚN NHẤT/NHỎ NHẤT.',
                [['=SUM(A1:A5)', 'Tính tổng'], ['=SUM(B2:B20)+C1', 'Tính tổng'],
                 ['=A1+A2+A3', 'Tính tổng'], ['=MAX(A1:A5)', 'Tìm giá trị lớn nhất/nhỏ nhất'],
                 ['=MIN(B2:B20)', 'Tìm giá trị lớn nhất/nhỏ nhất'], ['=MAX(C1:C10)', 'Tìm giá trị lớn nhất/nhỏ nhất']],
                'SUM tính tổng; MAX/MIN tìm giá trị lớn nhất/nhỏ nhất trong vùng.');
            $this->sortQ($L, 'Kéo mỗi cách viết vào nhóm ĐỊA CHỈ Ô hoặc VÙNG DỮ LIỆU.',
                [['D7', 'Địa chỉ ô'], ['AB12', 'Địa chỉ ô'], ['Z100', 'Địa chỉ ô'],
                 ['A1:A10', 'Vùng dữ liệu'], ['B2:D5', 'Vùng dữ liệu'], ['C1:C100', 'Vùng dữ liệu']],
                'Địa chỉ ô: cột + hàng; vùng dữ liệu: ô đầu : ô cuối.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Công thức Excel bắt đầu bằng dấu =', 'Đúng'],
                 ['=SUM(A1:A5) tính tổng từ A1 đến A5', 'Đúng'],
                 ['AVERAGE tính tổng các giá trị', 'Sai'],
                 ['Địa chỉ ô C5 ở cột C hàng 5', 'Đúng'],
                 ['COUNT đếm các ô chứa số', 'Đúng'],
                 ['Không thể dùng hàm trong Excel', 'Sai']],
                'Nắm vững cú pháp hàm giúp tính toán nhanh, chính xác.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhu cầu vào nhóm DÙNG HÀM PHÙ HỢP.',
                [['Tính tổng tiền bán hàng', 'SUM'], ['Tính điểm trung bình', 'AVERAGE'],
                 ['Tìm nhiệt độ cao nhất', 'MAX'], ['Tìm giá thấp nhất', 'MIN'],
                 ['Đếm số học sinh đạt điểm', 'COUNT'], ['Tính tổng cộng nhiều cột', 'SUM']],
                'Chọn đúng hàm cho đúng nhu cầu tính toán.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mọi công thức trong Excel đều bắt đầu bằng dấu ___.', [[0, '=']],
                'Dấu = báo cho Excel biết đây là công thức cần tính toán.');
            $this->fill($L, 'Hàm ___ dùng để tính tổng các giá trị trong một vùng.', [[0, 'SUM']],
                'Ví dụ: =SUM(A1:A10) tính tổng từ ô A1 đến ô A10.');
            $this->fill($L, 'Hàm AVERAGE dùng để tính giá trị trung bình ___.', [[0, 'cộng']],
                'Trung bình cộng = tổng các giá trị chia cho số lượng.');
            $this->fill($L, 'Vùng dữ liệu từ ô B2 đến ô B10 được viết là ___ .', [[0, 'B2:B10']],
                'Cách viết vùng: địa chỉ ô đầu, dấu hai chấm, địa chỉ ô cuối.');
        }
    }

    private function seedTin104(): void
    {
        $L = 'tin-hoc-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cú pháp đúng của hàm IF trong Excel là gì?',
                ['=IF(điều kiện, giá trị_nếu_đúng, giá trị_nếu_sai)',
                 '=IF(giá trị_nếu_đúng, giá trị_nếu_sai, điều kiện)',
                 '=IF(điều kiện; giá trị_nếu_đúng)',
                 '=IF(điều kiện) + (kết quả)'], 0,
                'Cú pháp: =IF(điều kiện, giá trị khi đúng, giá trị khi sai).', 'trung_binh');
            $this->quiz($L, 'Công thức =IF(B2>=5,"Đạt","Chưa đạt") cho kết quả gì khi B2 = 7?',
                ['Đạt', 'Chưa đạt', 'TRUE', 'Báo lỗi'], 0,
                'Vì 7 >= 5 là đúng nên hàm trả về "Đạt".');
            $this->quiz($L, 'Hàm COUNTIF(vùng, điều kiện) dùng để làm gì?',
                ['Đếm số ô thoả mãn điều kiện', 'Tính tổng theo điều kiện',
                 'Tìm giá trị lớn nhất', 'Đếm tất cả các ô'], 0,
                'COUNTIF đếm các ô trong vùng thoả mãn điều kiện cho trước.', 'trung_binh');
            $this->quiz($L, 'Để lọc dữ liệu trong Excel, ta dùng chức năng nào trong thẻ Data?',
                ['Filter', 'Sort', 'Merge', 'Freeze'], 0,
                'Data > Filter tạo nút lọc ở tiêu đề cột để hiển thị dữ liệu theo tiêu chí.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hàm với chức năng của nó.',
                [['IF', 'Trả về giá trị theo điều kiện đúng/sai'],
                 ['COUNTIF', 'Đếm các ô thoả mãn điều kiện'],
                 ['SUMIF', 'Tính tổng các ô thoả mãn điều kiện'],
                 ['AVERAGEIF', 'Tính trung bình các ô thoả mãn điều kiện']],
                'Nhóm hàm điều kiện giúp thống kê theo tiêu chí.', 'trung_binh');
            $this->matching($L, 'Nối mỗi công thức với kết quả (giả sử A1=8).',
                [['=IF(A1>=5,"Đạt","Rớt")', 'Đạt'],
                 ['=IF(A1<5,"Yếu","Khá")', 'Khá'],
                 ['=COUNTIF(A1:A10,">=5")', 'Đếm số ô từ A1 đến A10 có giá trị >= 5'],
                 ['=SUMIF(A1:A10,">=5")', 'Tính tổng các ô >= 5']],
                'Thay giá trị vào điều kiện rồi xác định nhánh đúng/sai.');
            $this->matching($L, 'Nối mỗi thao tác với vị trí lệnh.',
                [['Sắp xếp dữ liệu', 'Data > Sort'],
                 ['Lọc dữ liệu', 'Data > Filter'],
                 ['Xoá bộ lọc', 'Data > Clear (trong nhóm Sort & Filter)'],
                 ['Sắp xếp nhiều cấp', 'Data > Sort > Add Level']],
                'Các lệnh sắp xếp, lọc đều nằm trong thẻ Data.');
            $this->matching($L, 'Nối mỗi kiểu sắp xếp với ý nghĩa.',
                [['A → Z', 'Tăng dần (số nhỏ đến lớn, chữ A đến Z)'],
                 ['Z → A', 'Giảm dần (số lớn đến nhỏ, chữ Z đến A)'],
                 ['Sắp xếp theo ngày', 'Từ quá khứ đến hiện tại hoặc ngược lại'],
                 ['Sắp xếp nhiều cột', 'Ưu tiên cột 1, rồi đến cột 2...']],
                'Chọn đúng thứ tự sắp xếp phục vụ nhu cầu thống kê.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi công thức vào nhóm ĐÚNG CÚ PHÁP hoặc SAI CÚ PHÁP.',
                [['=IF(A1>5,"Đạt","Rớt")', 'Đúng cú pháp'],
                 ['=COUNTIF(B1:B10,">=5")', 'Đúng cú pháp'],
                 ['=SUMIF(C1:C10,">0")', 'Đúng cú pháp'],
                 ['=IF(A1>5;"Đạt")', 'Sai cú pháp'],
                 ['IF(A1>5,"Đạt","Rớt")', 'Sai cú pháp'],
                 ['=COUNTIF(">=5",B1:B10)', 'Sai cú pháp']],
                'Công thức phải bắt đầu bằng =, đủ 3 đối số cho IF, đúng thứ tự đối số.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhu cầu vào nhóm DÙNG SORT hoặc DÙNG FILTER.',
                [['Xếp học sinh theo điểm từ cao xuống thấp', 'Dùng Sort'],
                 ['Sắp tên theo vần A-Z', 'Dùng Sort'],
                 ['Chỉ hiện các bạn điểm >= 8', 'Dùng Filter'],
                 ['Xem riêng các đơn hàng trong tháng 5', 'Dùng Filter'],
                 ['Sắp xếp ngày sinh tăng dần', 'Dùng Sort'],
                 ['Lọc ra các sản phẩm còn hàng', 'Dùng Filter']],
                'Sort sắp xếp lại thứ tự; Filter ẩn/hiện theo tiêu chí mà không xoá dữ liệu.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['IF có 3 đối số: điều kiện, giá trị đúng, giá trị sai', 'Đúng'],
                 ['COUNTIF đếm theo điều kiện', 'Đúng'],
                 ['Filter xoá hẳn dữ liệu không thoả mãn', 'Sai'],
                 ['Sort A→Z là sắp xếp tăng dần', 'Đúng'],
                 ['Chuỗi văn bản trong công thức đặt trong ngoặc kép', 'Đúng'],
                 ['IF chỉ dùng được với số', 'Sai']],
                'Filter chỉ ẩn dữ liệu; IF dùng được với cả số và chuỗi.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi bước vào nhóm TRƯỚC KHI LỌC hoặc KHI ĐANG LỌC.',
                [['Chọn vùng dữ liệu có tiêu đề cột', 'Trước khi lọc'],
                 ['Bật Data > Filter', 'Trước khi lọc'],
                 ['Bấm mũi tên ở tiêu đề cột', 'Khi đang lọc'],
                 ['Chọn giá trị cần hiển thị', 'Khi đang lọc'],
                 ['Bấm Clear để hiện tất cả', 'Khi đang lọc'],
                 ['Chuẩn bị dữ liệu đầy đủ', 'Trước khi lọc']],
                'Chuẩn bị vùng dữ liệu có hàng tiêu đề trước, rồi mới bật Filter.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cú pháp hàm IF: =IF(điều kiện, giá trị_nếu_đúng, giá trị_nếu ___).', [[0, 'sai']],
                'Đối số thứ ba là giá trị trả về khi điều kiện sai.');
            $this->fill($L, 'Hàm ___ đếm số ô trong vùng thoả mãn điều kiện cho trước.', [[0, 'COUNTIF']],
                'Ví dụ: =COUNTIF(A1:A10,">=5") đếm số điểm từ 5 trở lên.');
            $this->fill($L, 'Để lọc dữ liệu, ta vào thẻ Data rồi chọn ___.', [[0, 'Filter']],
                'Filter giúp hiển thị dữ liệu theo tiêu chí mà không xoá dữ liệu gốc.');
            $this->fill($L, 'Sắp xếp A → Z là sắp xếp theo thứ tự ___ dần.', [[0, 'tăng']],
                'Tăng dần: số từ nhỏ đến lớn, chữ từ A đến Z.');
        }
    }

    // ================= TIN HỌC LỚP 11 =================

    private function seedTin111(): void
    {
        $L = 'tin-hoc-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Mạng máy tính là gì?',
                ['Tập hợp các máy tính được kết nối để chia sẻ tài nguyên',
                 'Một chiếc máy tính cấu hình cao', 'Phần mềm diệt virus', 'Hệ điều hành mạng'], 0,
                'Mạng máy tính gồm các máy tính kết nối với nhau nhằm chia sẻ tài nguyên và trao đổi thông tin.');
            $this->quiz($L, 'Mạng LAN là mạng có phạm vi như thế nào?',
                ['Trong một toà nhà, trường học, cơ quan (phạm vi hẹp)',
                 'Trong một thành phố', 'Trên phạm vi cả nước', 'Trên toàn thế giới'], 0,
                'LAN (Local Area Network): mạng cục bộ phạm vi hẹp; MAN: đô thị; WAN: diện rộng.');
            $this->quiz($L, 'Internet được xem là mạng loại nào?',
                ['WAN lớn nhất toàn cầu', 'LAN', 'MAN', 'Mạng ngang hàng'], 0,
                'Internet là mạng diện rộng (WAN) lớn nhất, kết nối hàng tỉ thiết bị toàn cầu.', 'trung_binh');
            $this->quiz($L, 'Lợi ích nào sau đây là của mạng máy tính?',
                ['Chia sẻ tài nguyên, trao đổi thông tin nhanh, tiết kiệm chi phí',
                 'Làm máy tính chạy chậm hơn', 'Tốn nhiều tiền mua máy in cho mỗi máy', 'Không thể gửi email'], 0,
                'Nhờ mạng, nhiều máy dùng chung máy in, dữ liệu, kết nối Internet.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại mạng với phạm vi tương ứng.',
                [['LAN', 'Mạng cục bộ: một toà nhà, trường học'],
                 ['MAN', 'Mạng đô thị: trong một thành phố'],
                 ['WAN', 'Mạng diện rộng: giữa các tỉnh, quốc gia'],
                 ['Internet', 'Mạng WAN lớn nhất toàn cầu']],
                'Phân loại mạng theo phạm vi địa lí: LAN < MAN < WAN.', 'trung_binh');
            $this->matching($L, 'Nối mỗi kiểu kết nối với đặc điểm.',
                [['Mạng có dây', 'Kết nối bằng cáp, ổn định, tốc độ cao'],
                 ['Mạng không dây', 'Kết nối qua sóng Wi-Fi, linh hoạt'],
                 ['Cáp quang', 'Truyền bằng ánh sáng, rất nhanh'],
                 ['Wi-Fi', 'Chuẩn mạng không dây phổ biến']],
                'Mạng có dây ổn định hơn; mạng không dây tiện lợi, linh động.');
            $this->matching($L, 'Nối mỗi lợi ích với ví dụ trong trường học.',
                [['Chia sẻ tài nguyên', 'Nhiều máy dùng chung một máy in'],
                 ['Trao đổi thông tin', 'Gửi bài tập qua email, nhóm chat'],
                 ['Tiết kiệm chi phí', 'Không cần mua phần mềm cho từng máy'],
                 ['Quản lí tập trung', 'Giáo viên quản lí các máy học sinh']],
                'Mạng LAN trong phòng tin học mang lại nhiều lợi ích thiết thực.');
            $this->matching($L, 'Nối mỗi khái niệm với nội dung tương ứng.',
                [['Máy chủ (server)', 'Máy cung cấp tài nguyên, dịch vụ'],
                 ['Máy khách (client)', 'Máy sử dụng tài nguyên, dịch vụ'],
                 ['Tài nguyên mạng', 'Dữ liệu, máy in, phần mềm dùng chung'],
                 ['Giao thức mạng', 'Quy tắc giao tiếp giữa các máy tính']],
                'Các khái niệm nền tảng để hiểu cách mạng máy tính hoạt động.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm MẠNG LAN, MAN hoặc WAN.',
                [['Mạng phòng tin học của trường', 'Mạng LAN'],
                 ['Mạng Wi-Fi trong gia đình', 'Mạng LAN'],
                 ['Mạng cáp quang toàn thành phố', 'Mạng MAN'],
                 ['Mạng truyền hình cáp thành phố', 'Mạng MAN'],
                 ['Mạng Internet', 'Mạng WAN'],
                 ['Mạng nối chi nhánh các tỉnh', 'Mạng WAN']],
                'LAN: phạm vi hẹp; MAN: thành phố; WAN: diện rộng.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm MẠNG CÓ DÂY hoặc MẠNG KHÔNG DÂY.',
                [['Kết nối bằng cáp mạng', 'Mạng có dây'], ['Tốc độ ổn định, ít nhiễu', 'Mạng có dây'],
                 ['Cần đi dây phức tạp', 'Mạng có dây'], ['Kết nối qua sóng Wi-Fi', 'Mạng không dây'],
                 ['Dùng được mọi nơi trong vùng phủ sóng', 'Mạng không dây'], ['Dễ bị nhiễu sóng', 'Mạng không dây']],
                'Có dây: ổn định nhưng kém linh động; không dây: linh động nhưng dễ nhiễu.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Mạng máy tính giúp chia sẻ tài nguyên', 'Đúng'],
                 ['LAN có phạm vi hẹp hơn WAN', 'Đúng'],
                 ['Internet là mạng LAN lớn nhất', 'Sai'],
                 ['Mạng không dây kết nối qua cáp', 'Sai'],
                 ['Nhiều máy có thể dùng chung máy in qua mạng', 'Đúng'],
                 ['Mạng máy tính không cần thiết bị kết nối', 'Sai']],
                'Mạng máy tính cần thiết bị kết nối; Internet là WAN lớn nhất toàn cầu.');
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm CẦN MẠNG MÁY TÍNH hoặc KHÔNG CẦN.',
                [['Gửi email cho bạn', 'Cần mạng máy tính'], ['Học trực tuyến', 'Cần mạng máy tính'],
                 ['In tài liệu từ máy tính qua máy in mạng', 'Cần mạng máy tính'],
                 ['Soạn thảo văn bản offline', 'Không cần'], ['Chơi game offline một mình', 'Không cần'],
                 ['Nghe nhạc đã tải sẵn', 'Không cần']],
                'Các hoạt động trao đổi, chia sẻ qua nhiều thiết bị cần mạng máy tính.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mạng máy tính là tập hợp các máy tính được ___ nối với nhau.', [[0, 'kết']],
                'Kết nối để chia sẻ tài nguyên và trao đổi thông tin.');
            $this->fill($L, 'LAN là mạng cục bộ có phạm vi ___ như một toà nhà, trường học.', [[0, 'hẹp']],
                'LAN < MAN < WAN theo phạm vi địa lí tăng dần.');
            $this->fill($L, 'Internet là mạng ___ lớn nhất trên toàn cầu.', [[0, 'WAN']],
                'WAN là mạng diện rộng; Internet kết nối hàng tỉ thiết bị.');
            $this->fill($L, 'Nhờ mạng máy tính, nhiều máy có thể dùng chung một máy ___ .', [[0, 'in']],
                'Chia sẻ tài nguyên giúp tiết kiệm chi phí đáng kể.');
        }
    }

    private function seedTin112(): void
    {
        $L = 'tin-hoc-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trong mô hình khách – chủ (client – server), máy chủ có vai trò gì?',
                ['Cung cấp tài nguyên, dịch vụ cho các máy khách', 'Chỉ dùng để chơi game',
                 'Không có vai trò gì', 'Ngăn không cho máy khác kết nối'], 0,
                'Server lưu trữ dữ liệu, cung cấp dịch vụ; client là máy sử dụng dịch vụ đó.');
            $this->quiz($L, 'Thiết bị nào dùng để kết nối nhiều máy tính trong mạng LAN?',
                ['Switch (hoặc Hub)', 'Máy in', 'Loa', 'Webcam'], 0,
                'Switch/hub là thiết bị trung tâm nối nhiều máy tính trong mạng LAN.', 'trung_binh');
            $this->quiz($L, 'Router trong mạng gia đình có chức năng chính là gì?',
                ['Định tuyến và phát Wi-Fi, nối mạng gia đình với Internet',
                 'In tài liệu', 'Lưu trữ dữ liệu', 'Quét virus'], 0,
                'Router định tuyến gói tin giữa các mạng và thường tích hợp phát Wi-Fi.');
            $this->quiz($L, 'Mạng ngang hàng (peer-to-peer) khác mô hình khách – chủ ở điểm nào?',
                ['Các máy bình đẳng, vừa dùng vừa chia sẻ tài nguyên', 'Phải có máy chủ mạnh',
                 'Không thể chia sẻ tệp', 'Chỉ dùng trong công ty lớn'], 0,
                'Mạng ngang hàng không phân biệt server/client, phù hợp mạng nhỏ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thiết bị mạng với chức năng của nó.',
                [['Card mạng (NIC)', 'Gắn trong máy tính để kết nối mạng'],
                 ['Switch', 'Nối nhiều máy trong LAN, chuyển tiếp thông minh'],
                 ['Router', 'Định tuyến, nối các mạng, phát Wi-Fi'],
                 ['Modem', 'Chuyển đổi tín hiệu để kết nối Internet']],
                'Mỗi thiết bị đảm nhận một vai trò riêng trong hệ thống mạng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi mô hình mạng với đặc điểm.',
                [['Khách – chủ', 'Có máy chủ cung cấp dịch vụ, máy khách sử dụng'],
                 ['Ngang hàng', 'Các máy bình đẳng, vừa dùng vừa chia sẻ'],
                 ['Mô hình khách – chủ', 'Phù hợp mạng lớn, quản lí tập trung'],
                 ['Mô hình ngang hàng', 'Phù hợp mạng nhỏ, đơn giản']],
                'Chọn mô hình phù hợp với quy mô và nhu cầu quản lí.');
            $this->matching($L, 'Nối mỗi ví dụ với mô hình mạng tương ứng.',
                [['Website trường học lưu trên máy chủ', 'Khách – chủ'],
                 ['Chia sẻ tệp giữa 2 máy trong phòng', 'Ngang hàng'],
                 ['Hệ thống email công ty', 'Khách – chủ'],
                 ['Chia sẻ nhạc giữa các máy bạn bè', 'Ngang hàng']],
                'Nhận biết mô hình qua cách tổ chức và chia sẻ tài nguyên.');
            $this->matching($L, 'Nối mỗi loại cáp với đặc điểm.',
                [['Cáp xoắn đôi', 'Phổ biến trong mạng LAN gia đình, văn phòng'],
                 ['Cáp quang', 'Tốc độ rất cao, khoảng cách xa'],
                 ['Cáp đồng trục', 'Ít dùng hiện nay, trước đây cho truyền hình cáp'],
                 ['Không dây (Wi-Fi)', 'Không cần cáp, dùng sóng vô tuyến']],
                'Cáp quang cho tốc độ cao nhất; cáp xoắn đôi phổ biến, rẻ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm THIẾT BỊ KẾT NỐI hoặc THIẾT BỊ ĐẦU CUỐI.',
                [['Switch', 'Thiết bị kết nối'], ['Router', 'Thiết bị kết nối'],
                 ['Modem', 'Thiết bị kết nối'], ['Máy tính cá nhân', 'Thiết bị đầu cuối'],
                 ['Điện thoại thông minh', 'Thiết bị đầu cuối'], ['Máy in mạng', 'Thiết bị đầu cuối']],
                'Thiết bị kết nối trung chuyển tín hiệu; thiết bị đầu cuối là nơi người dùng làm việc.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm MÔ HÌNH KHÁCH – CHỦ hoặc NGANG HÀNG.',
                [['Học sinh nộp bài lên hệ thống của trường', 'Mô hình khách – chủ'],
                 ['Truy cập website tin tức', 'Mô hình khách – chủ'],
                 ['Hai máy copy phim cho nhau', 'Ngang hàng'],
                 ['Chia sẻ ảnh qua Bluetooth', 'Ngang hàng'],
                 ['Đăng nhập email', 'Mô hình khách – chủ'],
                 ['Chơi game LAN đối kháng', 'Ngang hàng']],
                'Có máy chủ phục vụ: khách – chủ; các máy bình đẳng: ngang hàng.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Server cung cấp dịch vụ cho client', 'Đúng'],
                 ['Router có thể phát Wi-Fi', 'Đúng'],
                 ['Switch dùng để in tài liệu', 'Sai'],
                 ['Mạng ngang hàng cần máy chủ mạnh', 'Sai'],
                 ['Card mạng giúp máy tính kết nối mạng', 'Đúng'],
                 ['Modem chuyển đổi tín hiệu Internet', 'Đúng']],
                'Hiểu đúng chức năng từng thiết bị để lắp đặt, sử dụng mạng hiệu quả.');
            $this->sortQ($L, 'Kéo mỗi nhu cầu vào nhóm THIẾT BỊ PHÙ HỢP.',
                [['Nối 20 máy trong phòng tin học', 'Switch'],
                 ['Phát Wi-Fi cho cả nhà', 'Router Wi-Fi'],
                 ['Máy tính bàn kết nối mạng dây', 'Card mạng + cáp'],
                 ['Kết nối Internet từ nhà mạng', 'Modem'],
                 ['Mở rộng vùng phủ Wi-Fi', 'Access point'],
                 ['Chia sẻ Internet cho nhiều thiết bị', 'Router']],
                'Chọn đúng thiết bị cho từng nhu cầu sử dụng mạng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trong mô hình khách – chủ, máy ___ cung cấp tài nguyên và dịch vụ.', [[0, 'chủ']],
                'Server (máy chủ) phục vụ các client (máy khách).');
            $this->fill($L, 'Thiết bị dùng để nối nhiều máy tính trong mạng LAN là ___ .', [[0, 'Switch']],
                'Switch chuyển tiếp dữ liệu thông minh đến đúng máy nhận.');
            $this->fill($L, 'Thiết bị định tuyến và phát Wi-Fi trong gia đình là ___ .', [[0, 'Router']],
                'Router nối mạng gia đình với mạng Internet của nhà cung cấp.');
            $this->fill($L, 'Mạng ___ hàng gồm các máy bình đẳng, vừa dùng vừa chia sẻ tài nguyên.', [[0, 'ngang']],
                'Peer-to-peer phù hợp với mạng nhỏ, không cần máy chủ riêng.');
        }
    }

    private function seedTin113(): void
    {
        $L = 'tin-hoc-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Địa chỉ IP dùng để làm gì?',
                ['Định danh duy nhất cho mỗi thiết bị trên mạng', 'Đặt tên cho website',
                 'Mã hoá dữ liệu', 'Tăng tốc độ mạng'], 0,
                'Mỗi thiết bị tham gia mạng có một địa chỉ IP duy nhất, ví dụ 192.168.1.1.');
            $this->quiz($L, 'Tên miền (domain) như google.com có tác dụng gì?',
                ['Giúp con người dễ nhớ địa chỉ website thay vì nhớ dãy số IP',
                 'Làm website chạy nhanh hơn', 'Bảo mật tuyệt đối website', 'Miễn phí truy cập Internet'], 0,
                'Hệ thống DNS dịch tên miền thành địa chỉ IP để máy tính hiểu được.', 'trung_binh');
            $this->quiz($L, 'URL bắt đầu bằng https:// khác http:// ở điểm nào?',
                ['https mã hoá dữ liệu, bảo mật hơn', 'https nhanh hơn nhiều lần',
                 'https miễn phí', 'Không có điểm khác'], 0,
                'HTTPS mã hoá dữ liệu truyền đi, an toàn hơn khi đăng nhập, thanh toán.');
            $this->quiz($L, 'Dịch vụ nào sau đây KHÔNG phải là dịch vụ Internet?',
                ['Soạn thảo văn bản offline', 'Thư điện tử email', 'Tìm kiếm thông tin', 'Lưu trữ đám mây'], 0,
                'Email, tìm kiếm, lưu trữ đám mây đều là dịch vụ trên Internet; soạn thảo offline thì không.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dịch vụ Internet với ví dụ.',
                [['World Wide Web', 'Truy cập trang web bằng trình duyệt'],
                 ['Thư điện tử', 'Gửi, nhận email'],
                 ['Tìm kiếm', 'Tìm thông tin bằng công cụ tìm kiếm'],
                 ['Lưu trữ đám mây', 'Lưu tệp trên Google Drive, OneDrive']],
                'Internet cung cấp nhiều dịch vụ phục vụ học tập, làm việc.', 'trung_binh');
            $this->matching($L, 'Nối mỗi khái niệm với nội dung tương ứng.',
                [['Địa chỉ IP', 'Dãy số định danh thiết bị trên mạng'],
                 ['Tên miền', 'Tên dễ nhớ của website, ví dụ google.com'],
                 ['DNS', 'Dịch tên miền thành địa chỉ IP'],
                 ['URL', 'Địa chỉ đầy đủ của một trang web']],
                'Các khái niệm cơ bản khi tìm hiểu cách Internet hoạt động.');
            $this->matching($L, 'Nối mỗi trình duyệt với tên gọi.',
                [['Chrome', 'Trình duyệt của Google'],
                 ['Edge', 'Trình duyệt của Microsoft'],
                 ['Firefox', 'Trình duyệt mã nguồn mở của Mozilla'],
                 ['Cốc Cốc', 'Trình duyệt của Việt Nam']],
                'Trình duyệt là phần mềm để truy cập World Wide Web.');
            $this->matching($L, 'Nối mỗi hoạt động với dịch vụ Internet phù hợp.',
                [['Gửi bài tập cho thầy cô', 'Thư điện tử'],
                 ['Tra cứu tài liệu học tập', 'Tìm kiếm thông tin'],
                 ['Lưu bài thuyết trình để mở mọi nơi', 'Lưu trữ đám mây'],
                 ['Mua sách trực tuyến', 'Thương mại điện tử']],
                'Chọn đúng dịch vụ giúp học tập, mua sắm hiệu quả.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm DỊCH VỤ INTERNET hoặc KHÔNG DÙNG INTERNET.',
                [['Gửi email', 'Dịch vụ Internet'], ['Xem video trực tuyến', 'Dịch vụ Internet'],
                 ['Lưu tệp lên Drive', 'Dịch vụ Internet'], ['Soạn văn bản offline', 'Không dùng Internet'],
                 ['Chơi game đã cài sẵn', 'Không dùng Internet'], ['Nghe nhạc đã tải về', 'Không dùng Internet']],
                'Dịch vụ Internet cần kết nối mạng; hoạt động offline thì không.');
            $this->sortQ($L, 'Kéo mỗi địa chỉ vào nhóm TÊN MIỀN hoặc ĐỊA CHỈ IP.',
                [['google.com', 'Tên miền'], ['vuihoc.vn', 'Tên miền'],
                 ['edu.vn', 'Tên miền'], ['192.168.1.1', 'Địa chỉ IP'],
                 ['8.8.8.8', 'Địa chỉ IP'], ['172.16.0.1', 'Địa chỉ IP']],
                'Tên miền gồm chữ; địa chỉ IP gồm 4 nhóm số cách nhau bằng dấu chấm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Mỗi thiết bị trên mạng có IP riêng', 'Đúng'],
                 ['DNS dịch tên miền thành IP', 'Đúng'],
                 ['https bảo mật hơn http', 'Đúng'],
                 ['Tên miền và IP là một', 'Sai'],
                 ['Không có Internet vẫn gửi được email', 'Sai'],
                 ['Trình duyệt dùng để vào web', 'Đúng']],
                'Hiểu đúng giúp sử dụng Internet an toàn, hiệu quả.');
            $this->sortQ($L, 'Kéo mỗi nhu cầu vào nhóm DỊCH VỤ PHÙ HỢP.',
                [['Cần gửi tài liệu cho bạn ở xa', 'Email'],
                 ['Cần tìm tài liệu ôn thi', 'Công cụ tìm kiếm'],
                 ['Cần lưu ảnh để xem trên mọi thiết bị', 'Lưu trữ đám mây'],
                 ['Cần đọc tin tức hằng ngày', 'Trang web tin tức'],
                 ['Cần học bài giảng video', 'Học trực tuyến'],
                 ['Cần mua đồ dùng học tập', 'Thương mại điện tử']],
                'Mỗi nhu cầu có dịch vụ Internet phù hợp riêng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mỗi thiết bị tham gia mạng được định danh bằng địa chỉ ___.', [[0, 'IP']],
                'Ví dụ địa chỉ IP: 192.168.1.1.');
            $this->fill($L, 'Hệ thống ___ dịch tên miền thành địa chỉ IP.', [[0, 'DNS']],
                'Nhờ DNS, ta chỉ cần nhớ google.com thay vì dãy số IP.');
            $this->fill($L, 'URL bắt đầu bằng ___ mã hoá dữ liệu nên bảo mật hơn http.', [[0, 'https']],
                'Nên ưu tiên trang https khi đăng nhập hoặc thanh toán.');
            $this->fill($L, 'Phần mềm dùng để truy cập World Wide Web gọi là trình ___.', [[0, 'duyệt']],
                'Ví dụ: Chrome, Edge, Firefox, Cốc Cốc.');
        }
    }

    private function seedTin114(): void
    {
        $L = 'tin-hoc-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phishing là hình thức tấn công mạng nào?',
                ['Giả mạo website, email để lừa lấy mật khẩu, thông tin cá nhân',
                 'Làm hỏng phần cứng máy tính', 'Tăng tốc độ mạng', 'Sao lưu dữ liệu'], 0,
                'Phishing lừa người dùng nhập thông tin vào trang giả mạo.', 'trung_binh');
            $this->quiz($L, 'Ransomware gây hại như thế nào?',
                ['Mã hoá dữ liệu rồi đòi tiền chuộc để mở khoá',
                 'Xoá hệ điều hành ngay lập tức', 'Làm màn hình tối đen', 'Gửi email rác'], 0,
                'Ransomware mã hoá tệp tin, nạn nhân phải trả tiền mới lấy lại được dữ liệu.');
            $this->quiz($L, 'Mật khẩu nào sau đây là mạnh nhất?',
                ['An@2026_hocTot!', '123456', 'password', 'ngay sinh'], 0,
                'Mật khẩu mạnh: dài, gồm chữ hoa, chữ thường, số và kí tự đặc biệt, không đoán được.');
            $this->quiz($L, 'Khi nhận được email lạ có tệp đính kèm, nên làm gì?',
                ['Không mở, xoá email hoặc báo cáo spam', 'Mở ngay để xem nội dung',
                 'Chuyển tiếp cho bạn bè', 'Tải tệp về máy rồi mở'], 0,
                'Tệp đính kèm lạ có thể chứa mã độc; tuyệt đối không mở.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại mã độc với cách gây hại.',
                [['Virus', 'Lây lan qua tệp, phá hoại hoặc đánh cắp dữ liệu'],
                 ['Trojan', 'Giả dạng phần mềm hữu ích để xâm nhập'],
                 ['Worm', 'Tự lây lan qua mạng không cần tệp chủ'],
                 ['Ransomware', 'Mã hoá dữ liệu đòi tiền chuộc']],
                'Nhận biết các loại mã độc để phòng tránh hiệu quả.', 'trung_binh');
            $this->matching($L, 'Nối mỗi biện pháp với tác dụng bảo vệ.',
                [['Phần mềm diệt virus', 'Phát hiện, loại bỏ mã độc'],
                 ['Mật khẩu mạnh', 'Ngăn người khác đoán, dò mật khẩu'],
                 ['Xác thực hai yếu tố', 'Thêm lớp bảo vệ khi đăng nhập'],
                 ['Sao lưu dữ liệu', 'Khôi phục khi mất hoặc bị mã hoá dữ liệu']],
                'Kết hợp nhiều biện pháp tạo thành "lá chắn" nhiều lớp.');
            $this->matching($L, 'Nối mỗi tình huống với cách xử lí đúng.',
                [['Nhận link lạ từ người quen', 'Hỏi lại người gửi trước khi bấm'],
                 ['Website yêu cầu nhập mật khẩu ngân hàng lạ', 'Không nhập, kiểm tra kĩ địa chỉ web'],
                 ['Máy tính chạy chậm bất thường', 'Quét virus toàn hệ thống'],
                 ['Quên đăng xuất ở máy công cộng', 'Đổi mật khẩu ngay khi có thể']],
                'Cảnh giác và xử lí đúng giúp tránh mất tài khoản, dữ liệu.');
            $this->matching($L, 'Nối mỗi thói quen với ĐÚNG (an toàn) hoặc SAI (nguy hiểm).',
                [['Cập nhật hệ điều hành thường xuyên', 'Đúng'],
                 ['Dùng chung một mật khẩu mọi nơi', 'Sai'],
                 ['Đăng xuất sau khi dùng máy công cộng', 'Đúng'],
                 ['Cài phần mềm crack không rõ nguồn', 'Sai']],
                'Thói quen tốt tạo nên an toàn thông tin bền vững.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi mật khẩu vào nhóm MẠNH hoặc YẾU.',
                [['Hs@2026#Tin_hoc', 'Mạnh'], ['Qw!9xLm$2vB', 'Mạnh'],
                 ['Xy7&kP!mQ2@z', 'Mạnh'], ['123456', 'Yếu'],
                 ['password', 'Yếu'], ['ngaysinh2009', 'Yếu']],
                'Mật khẩu mạnh: dài, đủ loại kí tự, không chứa thông tin cá nhân.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Kiểm tra kĩ link trước khi bấm', 'An toàn'],
                 ['Bật xác thực hai yếu tố', 'An toàn'],
                 ['Sao lưu dữ liệu quan trọng', 'An toàn'],
                 ['Mở tệp đính kèm từ email lạ', 'Nguy hiểm'],
                 ['Dùng Wi-Fi công cộng để chuyển tiền', 'Nguy hiểm'],
                 ['Chia sẻ mật khẩu cho bạn bè', 'Nguy hiểm']],
                'An toàn thông tin bắt đầu từ thói quen cẩn trọng hằng ngày.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Nên cài phần mềm diệt virus có bản quyền', 'Đúng'],
                 ['Phishing là lừa đảo qua giả mạo', 'Đúng'],
                 ['Mật khẩu mạnh nên có kí tự đặc biệt', 'Đúng'],
                 ['Có thể mở mọi tệp đính kèm', 'Sai'],
                 ['Nên sao lưu dữ liệu quan trọng', 'Đúng'],
                 ['Wi-Fi công cộng an toàn tuyệt đối', 'Sai']],
                'Không có gì an toàn tuyệt đối; cảnh giác là biện pháp tốt nhất.');
            $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm NGHI MÁY NHIỄM MÃ ĐỘC hoặc BÌNH THƯỜNG.',
                [['Máy chạy chậm đột ngột', 'Nghi nhiễm mã độc'],
                 ['Xuất hiện quảng cáo lạ liên tục', 'Nghi nhiễm mã độc'],
                 ['Tệp tin tự đổi tên, mất dữ liệu', 'Nghi nhiễm mã độc'],
                 ['Máy khởi động hơi lâu khi cũ', 'Bình thường'],
                 ['Quạt kêu to khi chạy nặng', 'Bình thường'],
                 ['Pin hao khi dùng nhiều', 'Bình thường']],
                'Dấu hiệu bất thường đột ngột cần được quét virus kiểm tra.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hình thức giả mạo để lừa lấy mật khẩu gọi là ___ .', [[0, 'phishing']],
                'Luôn kiểm tra kĩ địa chỉ website trước khi nhập thông tin quan trọng.');
            $this->fill($L, 'Mã độc mã hoá dữ liệu rồi đòi tiền chuộc gọi là ___ .', [[0, 'ransomware']],
                'Sao lưu dữ liệu thường xuyên là cách đối phó hiệu quả.');
            $this->fill($L, 'Mật khẩu mạnh nên gồm chữ hoa, chữ thường, số và kí tự ___ biệt.', [[0, 'đặc']],
                'Mật khẩu càng dài, càng đa dạng kí tự càng khó bị dò.');
            $this->fill($L, 'Bật xác thực hai yếu tố giúp tài khoản an toàn hơn khi ___ nhập.', [[0, 'đăng']],
                'Kể cả lộ mật khẩu, kẻ xấu vẫn khó đăng nhập nếu không có mã xác thực.');
        }
    }

    // ================= TIN HỌC LỚP 12 =================

    private function seedTin121(): void
    {
        $L = 'tin-hoc-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thuật toán là gì?',
                ['Dãy hữu hạn các thao tác theo trình tự xác định để giải một bài toán',
                 'Một ngôn ngữ lập trình', 'Một loại máy tính', 'Một phần mềm diệt virus'], 0,
                'Thuật toán mô tả cách giải bài toán bằng các bước rõ ràng, hữu hạn.');
            $this->quiz($L, 'Tính chất nào sau đây KHÔNG phải của thuật toán?',
                ['Tính vô hạn (không bao giờ dừng)', 'Tính dừng', 'Tính xác định', 'Tính đúng đắn'], 0,
                'Thuật toán phải dừng sau hữu hạn bước; tính vô hạn là sai.', 'trung_binh');
            $this->quiz($L, 'Trong sơ đồ khối, hình thoi dùng để biểu diễn gì?',
                ['Điều kiện rẽ nhánh', 'Thao tác tính toán', 'Bắt đầu / kết thúc', 'Hướng thực hiện'], 0,
                'Hình thoi: điều kiện rẽ nhánh; hình chữ nhật: tính toán; ô van: bắt đầu/kết thúc; mũi tên: hướng đi.');
            $this->quiz($L, 'Cách mô tả thuật toán nào trực quan bằng hình vẽ?',
                ['Sơ đồ khối', 'Liệt kê bằng lời', 'Viết code ngay', 'Vẽ tranh minh hoạ'], 0,
                'Sơ đồ khối dùng các hình chuẩn để mô tả thuật toán một cách trực quan.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hình trong sơ đồ khối với ý nghĩa.',
                [['Hình ô van', 'Bắt đầu / kết thúc'],
                 ['Hình chữ nhật', 'Thao tác tính toán, gán giá trị'],
                 ['Hình thoi', 'Điều kiện rẽ nhánh'],
                 ['Mũi tên', 'Hướng thực hiện các bước']],
                'Bốn kí hiệu cơ bản của sơ đồ khối.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tính chất của thuật toán với nội dung.',
                [['Tính dừng', 'Phải kết thúc sau hữu hạn bước'],
                 ['Tính xác định', 'Mỗi bước rõ ràng, không mơ hồ'],
                 ['Tính đúng đắn', 'Cho kết quả đúng với mọi dữ liệu hợp lệ'],
                 ['Tính phổ dụng', 'Giải được một lớp bài toán, không chỉ một trường hợp']],
                'Một dãy bước chỉ là thuật toán khi thoả mãn các tính chất này.');
            $this->matching($L, 'Nối mỗi cách mô tả với đặc điểm.',
                [['Liệt kê các bước', 'Viết bằng ngôn ngữ tự nhiên, dễ hiểu'],
                 ['Sơ đồ khối', 'Trực quan bằng hình vẽ chuẩn'],
                 ['Giả mã (pseudocode)', 'Gần với ngôn ngữ lập trình'],
                 ['Code thật', 'Máy tính thực thi được ngay']],
                'Tuỳ mục đích mà chọn cách mô tả thuật toán phù hợp.');
            $this->matching($L, 'Nối mỗi bài toán với ý tưởng thuật toán.',
                [['Tính tổng 1 đến n', 'Cộng dồn từng số vào biến tổng'],
                 ['Tìm số lớn nhất trong dãy', 'So sánh từng số với giá trị lớn nhất hiện tại'],
                 ['Kiểm tra số nguyên tố', 'Thử chia cho các số từ 2 đến căn bậc hai'],
                 ['Đếm số chẵn trong dãy', 'Duyệt dãy, đếm số chia hết cho 2']],
                'Mỗi bài toán quen thuộc có một ý tưởng thuật toán điển hình.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi bước vào nhóm ĐÚNG TRÌNH TỰ hoặc SAI TRÌNH TỰ (thuật toán pha trà).',
                [['B1: Đun sôi nước', 'Đúng trình tự'], ['B2: Cho trà vào ấm', 'Đúng trình tự'],
                 ['B3: Rót nước sôi vào ấm', 'Đúng trình tự'], ['B4: Chờ 3 phút rồi rót ra chén', 'Đúng trình tự'],
                 ['Rót nước trước khi đun sôi', 'Sai trình tự'], ['Uống trước khi pha', 'Sai trình tự']],
                'Thuật toán yêu cầu các bước theo trình tự xác định, không thể đảo lộn tuỳ ý.');
            $this->sortQ($L, 'Kéo mỗi kí hiệu vào nhóm SƠ ĐỒ KHỐI hoặc KHÔNG PHẢI.',
                [['Hình ô van', 'Sơ đồ khối'], ['Hình chữ nhật', 'Sơ đồ khối'],
                 ['Hình thoi', 'Sơ đồ khối'], ['Mũi tên', 'Sơ đồ khối'],
                 ['Hình ngôi sao', 'Không phải'], ['Hình trái tim', 'Không phải']],
                'Sơ đồ khối chỉ dùng các kí hiệu chuẩn: ô van, chữ nhật, thoi, mũi tên.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Thuật toán phải dừng sau hữu hạn bước', 'Đúng'],
                 ['Mỗi bước của thuật toán phải rõ ràng', 'Đúng'],
                 ['Sơ đồ khối dùng hình thoi cho điều kiện', 'Đúng'],
                 ['Thuật toán có thể mơ hồ, tuỳ hiểu', 'Sai'],
                 ['Có thể mô tả thuật toán bằng lời', 'Đúng'],
                 ['Mọi dãy bước đều là thuật toán', 'Sai']],
                'Dãy bước phải có tính dừng, xác định, đúng đắn mới là thuật toán.');
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm LÀ THUẬT TOÁN hoặc KHÔNG PHẢI THUẬT TOÁN.',
                [['Công thức nấu ăn các bước rõ ràng', 'Là thuật toán'],
                 ['Các bước giải phương trình bậc hai', 'Là thuật toán'],
                 ['Hướng dẫn lắp ráp đồ chơi', 'Là thuật toán'],
                 ['"Nấu ăn ngon tùy cảm hứng"', 'Không phải thuật toán'],
                 ['"Làm bài tập tuỳ hứng"', 'Không phải thuật toán'],
                 ['Dãy bước không bao giờ kết thúc', 'Không phải thuật toán']],
                'Thuật toán: hữu hạn bước, rõ ràng, cho kết quả xác định.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thuật toán là dãy ___ hạn các thao tác theo trình tự xác định.', [[0, 'hữu']],
                'Hữu hạn bước là tính chất bắt buộc (tính dừng).');
            $this->fill($L, 'Trong sơ đồ khối, hình ___ dùng để biểu diễn điều kiện rẽ nhánh.', [[0, 'thoi']],
                'Từ hình thoi toả ra hai nhánh Đúng/Sai.');
            $this->fill($L, 'Hình chữ nhật trong sơ đồ khối biểu diễn thao tác tính toán hoặc ___ giá trị.', [[0, 'gán']],
                'Ví dụ: gán tổng = tổng + i.');
            $this->fill($L, 'Tính ___ định yêu cầu mỗi bước của thuật toán phải rõ ràng, không mơ hồ.', [[0, 'xác']],
                'Người và máy thực hiện đều hiểu đúng một cách duy nhất.');
        }
    }

    private function seedTin122(): void
    {
        $L = 'tin-hoc-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cấu trúc nào thực hiện các bước theo đúng thứ tự từ trên xuống?',
                ['Cấu trúc tuần tự', 'Cấu trúc rẽ nhánh', 'Cấu trúc lặp', 'Cấu trúc ngẫu nhiên'], 0,
                'Tuần tự: làm bước 1, rồi bước 2, rồi bước 3... theo thứ tự.');
            $this->quiz($L, 'Cấu trúc rẽ nhánh dạng đầy đủ có đặc điểm gì?',
                ['Nếu điều kiện đúng làm việc A, ngược lại làm việc B',
                 'Làm cả việc A và việc B', 'Không kiểm tra điều kiện', 'Lặp lại nhiều lần'], 0,
                'Rẽ nhánh đầy đủ (if-else): đúng thì làm A, sai thì làm B.', 'trung_binh');
            $this->quiz($L, 'Cấu trúc lặp dùng khi nào?',
                ['Khi cần thực hiện lặp đi lặp lại một khối thao tác',
                 'Khi chỉ làm một lần duy nhất', 'Khi không cần điều kiện', 'Khi muốn dừng chương trình'], 0,
                'Lặp (for, while) thực hiện lại khối lệnh khi điều kiện còn đúng.');
            $this->quiz($L, 'Bài toán "tính tổng các số từ 1 đến 100" phù hợp với cấu trúc nào nhất?',
                ['Cấu trúc lặp', 'Chỉ cấu trúc tuần tự', 'Cấu trúc rẽ nhánh', 'Không cần cấu trúc nào'], 0,
                'Cộng dồn 100 số là công việc lặp đi lặp lại — dùng cấu trúc lặp.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cấu trúc với đặc điểm của nó.',
                [['Tuần tự', 'Thực hiện các bước theo thứ tự từ trên xuống'],
                 ['Rẽ nhánh', 'Chọn việc làm theo điều kiện đúng/sai'],
                 ['Lặp', 'Lặp lại khối thao tác khi điều kiện còn đúng'],
                 ['Kết hợp', 'Mọi thuật toán đều xây dựng từ 3 cấu trúc trên']],
                'Ba cấu trúc điều khiển cơ bản của thuật toán.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tình huống với cấu trúc phù hợp.',
                [['Nhập điểm rồi tính trung bình', 'Tuần tự'],
                 ['Điểm >= 5 thì Đạt, ngược lại Chưa đạt', 'Rẽ nhánh'],
                 ['Cộng dồn 100 số', 'Lặp'],
                 ['Kiểm tra từng học sinh có đạt không', 'Lặp kết hợp rẽ nhánh']],
                'Phân tích bài toán để chọn cấu trúc điều khiển đúng.');
            $this->matching($L, 'Nối mỗi dạng lặp với đặc điểm.',
                [['Lặp với số lần biết trước', 'Ví dụ: lặp đúng 10 lần (for)'],
                 ['Lặp với điều kiện', 'Lặp khi điều kiện còn đúng (while)'],
                 ['Rẽ nhánh dạng thiếu', 'Chỉ làm việc A khi điều kiện đúng'],
                 ['Rẽ nhánh dạng đầy đủ', 'Đúng làm A, sai làm B']],
                'Phân biệt các dạng để vẽ sơ đồ khối chính xác.');
            $this->matching($L, 'Nối mỗi sơ đồ khối với cấu trúc nó thể hiện.',
                [['Một đường thẳng các hình chữ nhật', 'Tuần tự'],
                 ['Hình thoi rẽ hai nhánh', 'Rẽ nhánh'],
                 ['Mũi tên quay vòng lại', 'Lặp'],
                 ['Hình thoi + mũi tên quay lại', 'Lặp có điều kiện']],
                'Đọc sơ đồ khối: nhìn hình dạng để nhận ra cấu trúc.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi bài toán vào nhóm DÙNG RẼ NHÁNH hoặc DÙNG LẶP.',
                [['Xếp loại học sinh theo điểm', 'Dùng rẽ nhánh'],
                 ['Kiểm tra số chẵn hay lẻ', 'Dùng rẽ nhánh'],
                 ['Tính tổng 1 đến n', 'Dùng lặp'],
                 ['In bảng cửu chương', 'Dùng lặp'],
                 ['So sánh hai số', 'Dùng rẽ nhánh'],
                 ['Đếm số học sinh đạt', 'Dùng lặp']],
                'So sánh, phân loại: rẽ nhánh; làm đi làm lại nhiều lần: lặp.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi mô tả vào nhóm RẼ NHÁNH DẠNG THIẾU hoặc DẠNG ĐẦY ĐỦ.',
                [['Nếu trời mưa thì mang ô', 'Dạng thiếu'],
                 ['Nếu điểm >= 5 thì mang ô, không thì ở nhà', 'Dạng đầy đủ'],
                 ['Nếu đúng thì cộng điểm thưởng', 'Dạng thiếu'],
                 ['Đúng thì Đạt, sai thì Rớt', 'Dạng đầy đủ'],
                 ['Nếu còn tiền thì mua sách', 'Dạng thiếu'],
                 ['Mưa thì nghỉ, nắng thì đi chơi', 'Dạng đầy đủ']],
                'Dạng thiếu: chỉ làm khi đúng; dạng đầy đủ: đúng làm A, sai làm B.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Mọi thuật toán đều dùng được 3 cấu trúc cơ bản', 'Đúng'],
                 ['Cấu trúc lặp giúp tránh viết lại nhiều lần', 'Đúng'],
                 ['Rẽ nhánh không cần điều kiện', 'Sai'],
                 ['Vòng lặp vô hạn là thuật toán tốt', 'Sai'],
                 ['Tuần tự là thực hiện theo thứ tự', 'Đúng'],
                 ['Có thể lồng các cấu trúc vào nhau', 'Đúng']],
                'Vòng lặp vô hạn vi phạm tính dừng; các cấu trúc có thể lồng nhau.');
            $this->sortQ($L, 'Kéo mỗi công việc vào nhóm NÊN DÙNG MÁY (lặp) hoặc LÀM TAY.',
                [['Tính tổng 1 triệu số', 'Nên dùng máy (lặp)'],
                 ['Sắp xếp 10.000 tên', 'Nên dùng máy (lặp)'],
                 ['Kiểm tra 1 phép tính đơn giản', 'Làm tay'],
                 ['Đếm 5 quả táo', 'Làm tay'],
                 ['Tìm kiếm trong 1 triệu hồ sơ', 'Nên dùng máy (lặp)'],
                 ['So sánh 2 số', 'Làm tay']],
                'Công việc lặp lại với khối lượng lớn nên giao cho máy tính.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cấu trúc ___ tự thực hiện các bước theo đúng thứ tự từ trên xuống.', [[0, 'tuần']],
                'Tuần tự là cấu trúc đơn giản nhất.');
            $this->fill($L, 'Cấu trúc rẽ ___ chọn việc làm dựa trên điều kiện đúng hay sai.', [[0, 'nhánh']],
                'Dạng đầy đủ: đúng làm A, sai làm B; dạng thiếu: chỉ làm khi đúng.');
            $this->fill($L, 'Cấu trúc ___ thực hiện lặp đi lặp lại một khối thao tác.', [[0, 'lặp']],
                'Lặp với số lần biết trước (for) hoặc lặp theo điều kiện (while).');
            $this->fill($L, 'Mọi thuật toán đều có thể xây dựng từ ba cấu trúc điều khiển cơ ___.', [[0, 'bản']],
                'Tuần tự, rẽ nhánh, lặp là nền tảng của mọi thuật toán.');
        }
    }

    private function seedTin123(): void
    {
        $L = 'tin-hoc-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Lệnh nào trong Python dùng để in ra màn hình?',
                ['print()', 'write()', 'echo()', 'show()'], 0,
                'print("Xin chào") sẽ in dòng chữ Xin chào ra màn hình.');
            $this->quiz($L, 'Trong Python, câu lệnh nào tạo biến tên bằng giá trị "An"?',
                ['ten = "An"', 'ten := "An"', 'string ten = "An"', 'var ten = "An"'], 0,
                'Python gán biến bằng dấu =, không cần khai báo kiểu trước.', 'trung_binh');
            $this->quiz($L, 'Biến x = 3.5 trong Python có kiểu dữ liệu gì?',
                ['float (số thực)', 'int (số nguyên)', 'str (chuỗi)', 'bool (logic)'], 0,
                'Số có phần thập phân là float; số nguyên là int; chữ trong ngoặc kép là str.');
            $this->quiz($L, 'Lệnh input() trong Python trả về kiểu dữ liệu gì?',
                ['Luôn trả về chuỗi (str)', 'Trả về số nguyên', 'Trả về số thực', 'Tuỳ người nhập'], 0,
                'Muốn tính toán phải đổi kiểu: tuoi = int(input("Nhập tuổi: ")).', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lệnh Python với chức năng.',
                [['print()', 'In dữ liệu ra màn hình'],
                 ['input()', 'Nhập dữ liệu từ bàn phím'],
                 ['int()', 'Đổi sang số nguyên'],
                 ['#', 'Viết chú thích trong code']],
                'Bốn lệnh, kí hiệu cơ bản nhất khi bắt đầu học Python.', 'trung_binh');
            $this->matching($L, 'Nối mỗi giá trị với kiểu dữ liệu trong Python.',
                [['5', 'int'],
                 ['3.14', 'float'],
                 ['"hello"', 'str'],
                 ['True', 'bool']],
                'Bốn kiểu dữ liệu cơ bản: int, float, str, bool.');
            $this->matching($L, 'Nối mỗi đoạn code với kết quả in ra.',
                [['print(2 + 3)', '5'],
                 ['print("2" + "3")', '23'],
                 ['print(10 - 4)', '6'],
                 ['print("An" * 2)', 'AnAn']],
                'Phép + với số là cộng; với chuỗi là nối chuỗi.');
            $this->matching($L, 'Nối mỗi tên biến với ĐÚNG (hợp lệ) hoặc SAI (không hợp lệ) trong Python.',
                [['diem_toan', 'Đúng'],
                 ['_ten', 'Đúng'],
                 ['2ten', 'Sai'],
                 ['ho ten', 'Sai']],
                'Tên biến không bắt đầu bằng số, không chứa dấu cách.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm INT, FLOAT, STR hoặc BOOL.',
                [['10', 'INT'], ['-7', 'INT'], ['3.5', 'FLOAT'],
                 ['0.25', 'FLOAT'], ['"Python"', 'STR'], ['True', 'BOOL']],
                'int: số nguyên; float: số thực; str: chuỗi trong ngoặc kép; bool: True/False.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu lệnh vào nhóm IN RA MÀN HÌNH hoặc NHẬP TỪ BÀN PHÍM.',
                [['print("Chào")', 'In ra màn hình'], ['print(x)', 'In ra màn hình'],
                 ['print(1 + 2)', 'In ra màn hình'], ['ten = input()', 'Nhập từ bàn phím'],
                 ['n = input("Nhập n: ")', 'Nhập từ bàn phím'], ['s = input()', 'Nhập từ bàn phím']],
                'print: xuất dữ liệu; input: nhập dữ liệu từ bàn phím.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Python không cần khai báo kiểu biến trước', 'Đúng'],
                 ['input() luôn trả về chuỗi', 'Đúng'],
                 ['print() dùng để nhập dữ liệu', 'Sai'],
                 ['Biến trong Python phân biệt chữ hoa, chữ thường', 'Đúng'],
                 ['Có thể gán lại giá trị mới cho biến', 'Đúng'],
                 ['Tên biến được bắt đầu bằng số', 'Sai']],
                'Python linh hoạt nhưng vẫn có quy tắc đặt tên biến.');
            $this->sortQ($L, 'Kéo mỗi đoạn code vào nhóm CHẠY ĐÚNG hoặc BÁO LỖI.',
                [['x = 5\nprint(x)', 'Chạy đúng'], ['ten = "An"\nprint(ten)', 'Chạy đúng'],
                 ['print(2 * 3)', 'Chạy đúng'], ['print("a" + 5)', 'Báo lỗi'],
                 ['2ten = "An"', 'Báo lỗi'], ['print(chua_co_bien)', 'Báo lỗi']],
                'Không cộng chuỗi với số; tên biến sai quy tắc; biến phải được gán trước khi dùng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Lệnh ___() trong Python dùng để in dữ liệu ra màn hình.', [[0, 'print']],
                'Ví dụ: print("Xin chào các bạn").');
            $this->fill($L, 'Lệnh ___() dùng để nhập dữ liệu từ bàn phím.', [[0, 'input']],
                'input() luôn trả về chuỗi, cần đổi kiểu khi tính toán.');
            $this->fill($L, 'Để đổi chuỗi sang số nguyên, ta dùng hàm ___().', [[0, 'int']],
                'Ví dụ: n = int(input("Nhập n: ")).');
            $this->fill($L, 'Kí hiệu ___ dùng để viết chú thích trong chương trình Python.', [[0, '#']],
                'Chú thích giúp code dễ đọc, dễ bảo trì.');
        }
    }

    private function seedTin124(): void
    {
        $L = 'tin-hoc-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đoạn code sau in ra gì? for i in range(3): print(i)',
                ['0 1 2 (mỗi số một dòng)', '1 2 3', '0 1 2 3', '3 2 1'], 0,
                'range(3) tạo dãy 0, 1, 2 — bắt đầu từ 0.', 'trung_binh');
            $this->quiz($L, 'range(1, 6) tạo ra dãy số nào?',
                ['1, 2, 3, 4, 5', '1, 2, 3, 4, 5, 6', '0, 1, 2, 3, 4, 5', '1, 6'], 0,
                'range(a, b) chạy từ a đến b-1.');
            $this->quiz($L, 'Trong Python, khối lệnh sau if được xác định bằng cách nào?',
                ['Thụt đầu dòng (thường 4 dấu cách)', 'Dấu ngoặc nhọn {}', 'Từ khoá begin/end', 'Dấu chấm phẩy'], 0,
                'Python dùng thụt đầu dòng để xác định khối lệnh — rất đặc trưng.', 'trung_binh');
            $this->quiz($L, 'Vòng lặp while tiếp tục chạy khi nào?',
                ['Khi điều kiện còn đúng (True)', 'Khi điều kiện sai', 'Chỉ chạy đúng 1 lần', 'Chạy mãi mãi'], 0,
                'while điều_kiện: lặp lại khối lệnh chừng nào điều kiện còn đúng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu lệnh với chức năng trong Python.',
                [['if / elif / else', 'Rẽ nhánh theo điều kiện'],
                 ['for', 'Lặp với số lần biết trước'],
                 ['while', 'Lặp khi điều kiện còn đúng'],
                 ['break', 'Thoát khỏi vòng lặp ngay lập tức']],
                'Các câu lệnh điều khiển luồng thực hiện chương trình.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đoạn code với số lần lặp.',
                [['for i in range(5)', '5 lần'],
                 ['for i in range(1, 11)', '10 lần'],
                 ['while n > 0 (n giảm dần từ 3)', '3 lần'],
                 ['for i in range(2, 9, 2)', '4 lần (2, 4, 6, 8)']],
                'range(a, b, bước) cho phép chỉ định bước nhảy.');
            $this->matching($L, 'Nối mỗi đoạn code với kết quả.',
                [['x = 7\nif x > 5:\n    print("Lớn")', 'In ra: Lớn'],
                 ['x = 3\nif x > 5:\n    print("Lớn")\nelse:\n    print("Nhỏ")', 'In ra: Nhỏ'],
                 ['s = 0\nfor i in range(1, 4):\n    s = s + i\nprint(s)', 'In ra: 6'],
                 ['n = 3\nwhile n > 0:\n    print(n)\n    n = n - 1', 'In ra: 3, 2, 1']],
                'Đọc code theo từng dòng, chú ý thụt đầu dòng và điều kiện.', 'trung_binh');
            $this->matching($L, 'Nối mỗi toán tử so sánh với ý nghĩa.',
                [['==', 'So sánh bằng'],
                 ['!=', 'So sánh khác'],
                 ['>=', 'Lớn hơn hoặc bằng'],
                 ['<=', 'Nhỏ hơn hoặc bằng']],
                'Chú ý == (so sánh) khác = (gán giá trị).');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đoạn code vào nhóm VÒNG LẶP FOR hoặc WHILE.',
                [['for i in range(10):', 'Vòng lặp for'],
                 ['for ch in "abc":', 'Vòng lặp for'],
                 ['while x < 5:', 'Vòng lặp while'],
                 ['while True:', 'Vòng lặp while'],
                 ['for i in range(1, 101):', 'Vòng lặp for'],
                 ['while n != 0:', 'Vòng lặp while']],
                'for: lặp theo dãy biết trước; while: lặp theo điều kiện.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm DÙNG IF hoặc DÙNG VÒNG LẶP.',
                [['Xếp loại theo điểm số', 'Dùng if'],
                 ['Kiểm tra mật khẩu đúng không', 'Dùng if'],
                 ['In bảng cửu chương', 'Dùng vòng lặp'],
                 ['Tính tổng 100 số', 'Dùng vòng lặp'],
                 ['Phân loại chẵn lẻ', 'Dùng if'],
                 ['Nhập lại đến khi đúng', 'Dùng vòng lặp']],
                'So sánh, phân loại một lần: if; làm đi làm lại: vòng lặp.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Python dùng thụt đầu dòng xác định khối lệnh', 'Đúng'],
                 ['range(5) tạo dãy 0 đến 4', 'Đúng'],
                 ['while lặp khi điều kiện còn đúng', 'Đúng'],
                 ['break dùng để thoát vòng lặp', 'Đúng'],
                 ['== dùng để gán giá trị', 'Sai'],
                 ['Có thể lồng vòng lặp trong vòng lặp', 'Đúng']],
                '= gán giá trị; == so sánh bằng nhau.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đoạn code vào nhóm IN RA 6 hoặc KHÔNG.',
                [['print(2 * 3)', 'In ra 6'],
                 ['s = 0\nfor i in range(1, 4): s += i\nprint(s)', 'In ra 6'],
                 ['print(10 - 4)', 'In ra 6'],
                 ['print(2 + 2)', 'Không'],
                 ['print("6")', 'Không (in chuỗi)'],
                 ['print(12 // 2)', 'In ra 6']],
                '// là phép chia lấy phần nguyên: 12 // 2 = 6.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Câu lệnh ___ dùng để rẽ nhánh theo điều kiện trong Python.', [[0, 'if']],
                'Cú pháp: if điều_kiện: rồi thụt đầu dòng viết khối lệnh.');
            $this->fill($L, 'Vòng lặp ___ dùng khi biết trước số lần lặp, thường kết hợp với range().', [[0, 'for']],
                'Ví dụ: for i in range(5): lặp 5 lần với i từ 0 đến 4.');
            $this->fill($L, 'Vòng lặp ___ lặp lại khối lệnh chừng nào điều kiện còn đúng.', [[0, 'while']],
                'Chú ý cập nhật biến điều kiện để tránh lặp vô hạn.');
            $this->fill($L, 'Trong Python, khối lệnh được xác định bằng cách ___ đầu dòng.', [[0, 'thụt']],
                'Thường thụt 4 dấu cách; sai thụt đầu dòng sẽ báo lỗi.');
        }
    }

    // ================= CÔNG NGHỆ LỚP 10 =================

    private function seedCn101(): void
    {
        $L = 'cong-nghe-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cường độ dòng điện được đo bằng đơn vị nào?',
                ['Ampe (A)', 'Vôn (V)', 'Ôm (Ω)', 'Oát (W)'], 0,
                'Cường độ dòng điện I đo bằng ampe (A); điện áp đo bằng vôn (V); điện trở đo bằng ôm (Ω).');
            $this->quiz($L, 'Điện áp (hiệu điện thế) được đo bằng dụng cụ nào và mắc thế nào?',
                ['Vôn kế, mắc song song', 'Ampe kế, mắc nối tiếp', 'Ôm kế, mắc nối tiếp', 'Oát kế, mắc song song'], 0,
                'Vôn kế đo điện áp, mắc song song với đoạn mạch; ampe kế đo dòng điện, mắc nối tiếp.', 'trung_binh');
            $this->quiz($L, 'Theo định luật Ôm, cường độ dòng điện qua vật dẫn được tính bằng công thức nào?',
                ['I = U / R', 'I = U × R', 'I = U + R', 'I = R / U'], 0,
                'Định luật Ôm: I = U/R — dòng điện tỉ lệ thuận với điện áp, tỉ lệ nghịch với điện trở.');
            $this->quiz($L, 'Một bóng đèn 220V – 100W có ý nghĩa gì?',
                ['Đèn hoạt động bình thường ở 220V với công suất 100W',
                 'Đèn chịu được tối đa 100V', 'Đèn tiêu thụ 220W', 'Đèn dùng điện 100V'], 0,
                'Số liệu định mức: điện áp định mức 220V, công suất định mức 100W.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đại lượng điện với đơn vị đo.',
                [['Cường độ dòng điện', 'Ampe (A)'],
                 ['Điện áp', 'Vôn (V)'],
                 ['Điện trở', 'Ôm (Ω)'],
                 ['Công suất điện', 'Oát (W)']],
                'Ghi nhớ đúng đơn vị của từng đại lượng điện.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dụng cụ đo với cách mắc.',
                [['Ampe kế', 'Mắc nối tiếp với đoạn mạch'],
                 ['Vôn kế', 'Mắc song song với đoạn mạch'],
                 ['Ôm kế', 'Đo trực tiếp điện trở (ngắt điện)'],
                 ['Công tơ điện', 'Đo điện năng tiêu thụ của gia đình']],
                'Mắc sai dụng cụ có thể làm hỏng thiết bị hoặc cho kết quả sai.');
            $this->matching($L, 'Nối mỗi công thức với ý nghĩa.',
                [['I = U / R', 'Định luật Ôm'],
                 ['P = U × I', 'Công suất điện'],
                 ['A = P × t', 'Điện năng tiêu thụ'],
                 ['R = U / I', 'Tính điện trở từ U và I']],
                'Các công thức cơ bản nhất của mạch điện.');
            $this->matching($L, 'Nối mỗi số liệu với ý nghĩa trên thiết bị.',
                [['220V', 'Điện áp định mức'],
                 ['100W', 'Công suất định mức'],
                 ['50Hz', 'Tần số dòng điện xoay chiều'],
                 ['10A – 250V (ổ cắm)', 'Dòng điện và điện áp tối đa cho phép']],
                'Dùng thiết bị đúng số liệu định mức để an toàn, bền lâu.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đại lượng vào nhóm ĐO BẰNG AMPE KẾ, VÔN KẾ hoặc ÔM KẾ.',
                [['Cường độ dòng điện 2A', 'Ampe kế'], ['Dòng điện qua bóng đèn', 'Ampe kế'],
                 ['Điện áp 220V', 'Vôn kế'], ['Hiệu điện thế hai đầu pin', 'Vôn kế'],
                 ['Điện trở dây dẫn', 'Ôm kế'], ['Điện trở của bàn là', 'Ôm kế']],
                'Ampe kế đo dòng điện; vôn kế đo điện áp; ôm kế đo điện trở.');
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm DÙNG ĐIỆN NĂNG THÀNH NHIỆT, ÁNH SÁNG hoặc CƠ NĂNG.',
                [['Bàn là', 'Thành nhiệt'], ['Bếp điện', 'Thành nhiệt'],
                 ['Bóng đèn', 'Thành ánh sáng'], ['Đèn học', 'Thành ánh sáng'],
                 ['Quạt điện', 'Thành cơ năng'], ['Máy bơm nước', 'Thành cơ năng']],
                'Thiết bị điện biến điện năng thành các dạng năng lượng khác.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['I = U / R là định luật Ôm', 'Đúng'],
                 ['Đơn vị công suất là oát (W)', 'Đúng'],
                 ['Ampe kế mắc song song', 'Sai'],
                 ['1 kWh chính là 1 "số điện"', 'Đúng'],
                 ['Điện trở đo bằng vôn', 'Sai'],
                 ['P = U × I tính công suất', 'Đúng']],
                'Ampe kế mắc nối tiếp; điện trở đo bằng ôm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TIẾT KIỆM ĐIỆN hoặc LÃNG PHÍ ĐIỆN.',
                [['Dùng bóng đèn LED', 'Tiết kiệm điện'], ['Tắt đèn khi ra khỏi phòng', 'Tiết kiệm điện'],
                 ['Dùng điều hoà 26–28 độ', 'Tiết kiệm điện'], ['Bật đèn cả ngày không tắt', 'Lãng phí điện'],
                 ['Để TV ở chế độ chờ cả đêm', 'Lãng phí điện'], ['Dùng bếp điện đun nước thừa', 'Lãng phí điện']],
                'Tiết kiệm điện vừa giảm chi phí vừa bảo vệ môi trường.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cường độ dòng điện được đo bằng đơn vị ___ (A).', [[0, 'ampe']],
                'Kí hiệu I, đo bằng ampe kế mắc nối tiếp.');
            $this->fill($L, 'Định luật Ôm được viết: I = U / ___.', [[0, 'R']],
                'R là điện trở của vật dẫn, đo bằng ôm.');
            $this->fill($L, 'Công suất điện được tính: P = U × ___.', [[0, 'I']],
                'Đơn vị công suất là oát (W).');
            $this->fill($L, 'Điện năng tiêu thụ được đo bằng kWh, còn gọi là số ___.', [[0, 'điện']],
                'Công tơ điện trong gia đình đếm số điện đã dùng.');
        }
    }

    private function seedCn102(): void
    {
        $L = 'cong-nghe-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trong mạch mắc nối tiếp, cường độ dòng điện qua các thiết bị như thế nào?',
                ['Bằng nhau: I = I1 = I2', 'Thiết bị đầu lớn hơn', 'Thiết bị cuối lớn hơn', 'Bằng 0'], 0,
                'Mạch nối tiếp chỉ có một đường đi nên dòng điện qua mọi thiết bị bằng nhau.');
            $this->quiz($L, 'Hai điện trở R1 = 4Ω, R2 = 6Ω mắc nối tiếp có điện trở tương đương là bao nhiêu?',
                ['10Ω', '2,4Ω', '24Ω', '5Ω'], 0,
                'Nối tiếp: R = R1 + R2 = 4 + 6 = 10Ω.', 'trung_binh');
            $this->quiz($L, 'Trong mạch mắc song song, điện áp trên các nhánh như thế nào?',
                ['Bằng nhau: U = U1 = U2', 'Nhánh đầu lớn hơn', 'Nhánh cuối nhỏ hơn', 'Bằng 0'], 0,
                'Các nhánh song song cùng nối vào hai điểm chung nên điện áp bằng nhau.');
            $this->quiz($L, 'Vì sao các thiết bị điện trong gia đình được mắc song song?',
                ['Để mỗi thiết bị được điện áp 220V và hoạt động độc lập',
                 'Để tiết kiệm dây dẫn', 'Để dòng điện lớn hơn', 'Để dễ sửa chữa hơn'], 0,
                'Mắc song song: mỗi thiết bị chịu đủ 220V, tắt một thiết bị các thiết bị khác vẫn hoạt động.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cách mắc với đặc điểm.',
                [['Mắc nối tiếp', 'Dòng điện bằng nhau, điện áp chia nhỏ'],
                 ['Mắc song song', 'Điện áp bằng nhau, dòng điện chia nhỏ'],
                 ['Nối tiếp: R tương đương', 'R = R1 + R2'],
                 ['Song song: R tương đương', '1/R = 1/R1 + 1/R2']],
                'Nắm chắc đặc điểm hai cách mắc để tính toán mạch điện.', 'trung_binh');
            $this->matching($L, 'Nối mỗi ví dụ với cách mắc tương ứng.',
                [['Các bóng đèn trang trí Noel nối đuôi nhau', 'Nối tiếp'],
                 ['Các ổ cắm trong nhà', 'Song song'],
                 ['Dây tóc bóng đèn với công tắc', 'Nối tiếp'],
                 ['Quạt, TV, tủ lạnh trong nhà', 'Song song']],
                'Thiết bị gia đình mắc song song để dùng độc lập với điện áp 220V.');
            $this->matching($L, 'Nối mỗi bài toán với kết quả (R1=6Ω, R2=3Ω).',
                [['Nối tiếp: R tương đương', '9Ω'],
                 ['Song song: R tương đương', '2Ω'],
                 ['Nối tiếp với U=18V: dòng điện', '2A'],
                 ['Song song với U=6V: dòng qua R1', '1A']],
                'Áp dụng công thức và định luật Ôm để tính.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hiện tượng với nguyên nhân.',
                [['Một bóng đèn dây tóc đứt, cả dây đèn tắt', 'Mắc nối tiếp'],
                 ['Tắt quạt, TV vẫn chạy', 'Mắc song song'],
                 ['Chạm tay vào 2 đầu pin không giật', 'Điện áp pin quá nhỏ'],
                 ['Cầu chì nổ khi quá tải', 'Dòng điện vượt mức cho phép']],
                'Giải thích hiện tượng điện trong đời sống bằng kiến thức mạch điện.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm MẮC NỐI TIẾP hoặc MẮC SONG SONG.',
                [['Dòng điện qua các thiết bị bằng nhau', 'Mắc nối tiếp'],
                 ['Một thiết bị hỏng, cả mạch ngừng', 'Mắc nối tiếp'],
                 ['R tương đương = R1 + R2', 'Mắc nối tiếp'],
                 ['Điện áp các nhánh bằng nhau', 'Mắc song song'],
                 ['Tắt một thiết bị, các thiết bị khác vẫn chạy', 'Mắc song song'],
                 ['1/R = 1/R1 + 1/R2', 'Mắc song song']],
                'Nối tiếp: chung dòng; song song: chung áp.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thiết bị/ví dụ vào nhóm MẮC NỐI TIẾP hoặc SONG SONG trong thực tế.',
                [['Công tắc với bóng đèn', 'Mắc nối tiếp'], ['Cầu chì với mạch điện', 'Mắc nối tiếp'],
                 ['Ampe kế với đoạn mạch', 'Mắc nối tiếp'], ['Các phòng trong nhà', 'Mắc song song'],
                 ['Vôn kế với đoạn mạch', 'Mắc song song'], ['Các ổ cắm điện', 'Mắc song song']],
                'Công tắc, cầu chì, ampe kế mắc nối tiếp; thiết bị dùng điện mắc song song.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Nối tiếp: I bằng nhau ở mọi vị trí', 'Đúng'],
                 ['Song song: U bằng nhau các nhánh', 'Đúng'],
                 ['Hai điện trở 4Ω, 6Ω nối tiếp được 10Ω', 'Đúng'],
                 ['Thiết bị gia đình mắc nối tiếp', 'Sai'],
                 ['Song song: R tương đương nhỏ hơn từng R', 'Đúng'],
                 ['Nối tiếp: U toàn mạch bằng U từng phần', 'Sai']],
                'Nối tiếp: U toàn mạch = tổng U từng phần; thiết bị gia đình mắc song song.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm ĐIỆN TRỞ TƯƠNG ĐƯƠNG NỐI TIẾP hoặc SONG SONG (R1=10Ω, R2=10Ω).',
                [['20Ω', 'Nối tiếp'], ['R1+R2=20', 'Nối tiếp'],
                 ['5Ω', 'Song song'], ['Bằng một nửa mỗi điện trở', 'Song song'],
                 ['Lớn hơn từng điện trở', 'Nối tiếp'], ['Nhỏ hơn từng điện trở', 'Song song']],
                'Nối tiếp làm R tăng; song song làm R giảm.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mạch nối tiếp: dòng điện qua các thiết bị ___ nhau.', [[0, 'bằng']],
                'Chỉ một đường đi nên I1 = I2 = I.');
            $this->fill($L, 'Mạch song song: điện áp trên các nhánh ___ nhau.', [[0, 'bằng']],
                'Các nhánh cùng nối vào hai điểm chung nên U1 = U2 = U.');
            $this->fill($L, 'Hai điện trở mắc nối tiếp: R = R1 ___ R2.', [[0, '+']],
                'Điện trở tương đương nối tiếp bằng tổng các điện trở thành phần.');
            $this->fill($L, 'Các thiết bị điện trong gia đình được mắc ___ song để dùng độc lập.', [[0, 'song']],
                'Mắc song song giúp mỗi thiết bị được đủ điện áp 220V.');
        }
    }

    private function seedCn103(): void
    {
        $L = 'cong-nghe-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nguyên nhân nào sau đây dễ gây tai nạn điện trong gia đình?',
                ['Chạm tay ướt vào ổ cắm, thiết bị điện', 'Dùng thiết bị đúng điện áp',
                 'Ngắt điện trước khi sửa chữa', 'Lắp aptomat chống rò'], 0,
                'Tay ướt dẫn điện tốt, chạm vào thiết bị điện rất dễ bị giật.');
            $this->quiz($L, 'Vì sao chim đậu trên dây điện cao thế thường không bị giật?',
                ['Vì chim chỉ đậu trên một dây, không tạo thành mạch kín qua người',
                 'Vì chim có lông cách điện tuyệt đối', 'Vì dây cao thế không có điện', 'Vì chim bay nhanh'], 0,
                'Không có hiệu điện thế đáng kể qua cơ thể chim nên không có dòng điện nguy hiểm.', 'trung_binh');
            $this->quiz($L, 'Thiết bị nào giúp tự động ngắt điện khi có sự cố rò điện, quá tải?',
                ['Aptomat (cầu dao tự động)', 'Ổ cắm điện', 'Bóng đèn', 'Quạt điện'], 0,
                'Aptomat tự ngắt khi quá tải, ngắn mạch; loại chống rò còn bảo vệ khi rò điện.');
            $this->quiz($L, 'Khi sửa chữa điện trong nhà, việc đầu tiên phải làm là gì?',
                ['Ngắt nguồn điện (tắt aptomat, cầu dao)', 'Mang găng tay vải',
                 'Đứng trên ghế nhựa', 'Nhờ người giữ thang'], 0,
                'Ngắt điện là nguyên tắc an toàn đầu tiên và quan trọng nhất.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nguyên nhân với cách phòng tránh.',
                [['Tay ướt chạm thiết bị điện', 'Lau khô tay trước khi dùng điện'],
                 ['Dây điện bị hở, tróc vỏ', 'Thay dây mới, bọc cách điện'],
                 ['Vỏ máy bị nhiễm điện', 'Nối đất, dùng aptomat chống rò'],
                 ['Quá tải ổ cắm', 'Không cắm nhiều thiết bị công suất lớn chung ổ']],
                'Phòng tránh từ nguyên nhân giúp ngăn ngừa tai nạn điện.', 'trung_binh');
            $this->matching($L, 'Nối mỗi thiết bị bảo vệ với chức năng.',
                [['Aptomat', 'Tự ngắt khi quá tải, ngắn mạch'],
                 ['Cầu chì', 'Đứt khi dòng vượt mức, bảo vệ mạch'],
                 ['Aptomat chống rò', 'Ngắt khi có dòng rò ra vỏ'],
                 ['Dây nối đất', 'Dẫn dòng rò xuống đất, an toàn cho người']],
                'Hệ thống bảo vệ nhiều lớp giúp sử dụng điện an toàn.');
            $this->matching($L, 'Nối mỗi tình huống với mức độ nguy hiểm.',
                [['Chạm vào dây trần 220V', 'Rất nguy hiểm'],
                 ['Đến gần đường dây cao áp', 'Rất nguy hiểm'],
                 ['Dùng điện thoại khi đang sạc pin phồng', 'Nguy hiểm'],
                 ['Rút phích cắm bằng cách kéo dây', 'Nguy hiểm, dễ hở dây']],
                'Điện áp càng cao, nguy hiểm càng lớn; luôn giữ khoảng cách an toàn.');
            $this->matching($L, 'Nối mỗi việc làm với ĐÚNG (an toàn) hoặc SAI (nguy hiểm).',
                [['Ngắt điện trước khi thay bóng đèn', 'Đúng'],
                 ['Dùng bút thử điện kiểm tra', 'Đúng'],
                 ['Thả diều gần đường dây điện', 'Sai'],
                 ['Tự ý trèo lên cột điện', 'Sai']],
                'Tuân thủ quy tắc an toàn điện trong mọi tình huống.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm AN TOÀN ĐIỆN hoặc MẤT AN TOÀN ĐIỆN.',
                [['Lau khô tay trước khi bật công tắc', 'An toàn điện'],
                 ['Ngắt aptomat khi sửa điện', 'An toàn điện'],
                 ['Dùng thiết bị đúng điện áp định mức', 'An toàn điện'],
                 ['Cắm quá nhiều thiết bị vào một ổ', 'Mất an toàn điện'],
                 ['Dùng dây điện bị tróc vỏ', 'Mất an toàn điện'],
                 ['Sờ tay ướt vào ổ cắm', 'Mất an toàn điện']],
                'An toàn điện bắt đầu từ những thói quen đúng hằng ngày.');
            $this->sortQ($L, 'Kéo mỗi nguyên nhân vào nhóm CHẠM TRỰC TIẾP hoặc CHẠM GIÁN TIẾP.',
                [['Chạm vào dây trần đang có điện', 'Chạm trực tiếp'],
                 ['Chọc tay vào ổ cắm', 'Chạm trực tiếp'],
                 ['Chạm vào vỏ máy giặt bị rò điện', 'Chạm gián tiếp'],
                 ['Chạm vào tủ lạnh nhiễm điện', 'Chạm gián tiếp'],
                 ['Nắm dây điện bị đứt vỏ', 'Chạm trực tiếp'],
                 ['Chạm vào lan can nhiễm điện', 'Chạm gián tiếp']],
                'Chạm trực tiếp: chạm vật mang điện; gián tiếp: chạm vật dẫn bị nhiễm điện.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Phải ngắt điện trước khi sửa chữa điện', 'Đúng'],
                 ['Aptomat tự ngắt khi quá tải', 'Đúng'],
                 ['Tay ướt chạm thiết bị điện rất nguy hiểm', 'Đúng'],
                 ['Có thể đứng gần đường dây cao thế', 'Sai'],
                 ['Dây nối đất giúp an toàn khi rò điện', 'Đúng'],
                 ['Trẻ em được tự ý sửa điện', 'Sai']],
                'Giữ khoảng cách an toàn với lưới điện cao áp; trẻ em không tự sửa điện.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đồ vật vào nhóm VẬT CÁCH ĐIỆN hoặc VẬT DẪN ĐIỆN.',
                [['Gậy gỗ khô', 'Vật cách điện'], ['Vải khô', 'Vật cách điện'],
                 ['Nhựa', 'Vật cách điện'], ['Kim loại', 'Vật dẫn điện'],
                 ['Nước', 'Vật dẫn điện'], ['Cơ thể người', 'Vật dẫn điện']],
                'Dùng vật cách điện để tách nạn nhân khỏi nguồn điện khi cứu người.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trước khi sửa chữa điện, việc đầu tiên là ___ nguồn điện.', [[0, 'ngắt']],
                'Ngắt aptomat, cầu dao là nguyên tắc an toàn số một.');
            $this->fill($L, 'Thiết bị tự động ngắt điện khi quá tải, ngắn mạch gọi là ___ .', [[0, 'aptomat']],
                'Aptomat (cầu dao tự động) bảo vệ mạch điện gia đình.');
            $this->fill($L, 'Không chạm tay ___ vào ổ cắm, công tắc điện.', [[0, 'ướt']],
                'Nước dẫn điện tốt nên tay ướt rất dễ bị điện giật.');
            $this->fill($L, 'Dây ___ đất giúp dẫn dòng điện rò xuống đất, bảo vệ người sử dụng.', [[0, 'nối']],
                'Thiết bị vỏ kim loại nên được nối đất an toàn.');
        }
    }

    private function seedCn104(): void
    {
        $L = 'cong-nghe-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi thấy người bị điện giật mà chưa ngắt được điện, ta phải làm gì đầu tiên?',
                ['Dùng vật cách điện (gậy gỗ khô) tách nạn nhân khỏi nguồn điện',
                 'Dùng tay kéo nạn nhân ra', 'Dội nước vào nạn nhân', 'Đứng nhìn chờ người khác'], 0,
                'Tuyệt đối không chạm tay trần vào nạn nhân khi chưa ngắt điện.', 'trung_binh');
            $this->quiz($L, 'Số điện thoại cấp cứu khi có người bị điện giật là số nào?',
                ['115', '113', '114', '100'], 0,
                '115 là số cấp cứu y tế; 113 công an; 114 cứu hoả.');
            $this->quiz($L, 'Khi nạn nhân ngừng thở sau điện giật, cần thực hiện biện pháp nào?',
                ['Hô hấp nhân tạo kết hợp ép tim ngoài lồng ngực', 'Cho uống nước đường',
                 'Để nạn nhân nằm yên chờ tỉnh', 'Xoa dầu nóng'], 0,
                'Hô hấp nhân tạo + ép tim 100–120 lần/phút, càng sớm càng tốt.');
            $this->quiz($L, 'Tần suất ép tim ngoài lồng ngực khi sơ cứu là bao nhiêu?',
                ['100–120 lần/phút', '30–40 lần/phút', '200 lần/phút', 'Càng nhanh càng tốt'], 0,
                'Ép tim 100–120 lần/phút, ấn sâu khoảng 5cm ở người lớn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bước sơ cứu với nội dung (theo trình tự).',
                [['Bước 1', 'Tách nạn nhân khỏi nguồn điện bằng vật cách điện'],
                 ['Bước 2', 'Ngắt nguồn điện (aptomat, cầu dao)'],
                 ['Bước 3', 'Đặt nạn nhân nơi thoáng, kiểm tra hô hấp'],
                 ['Bước 4', 'Hô hấp nhân tạo, ép tim nếu ngừng thở; gọi 115']],
                'Trình tự sơ cứu đúng: tách nguồn – ngắt điện – kiểm tra – cấp cứu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi số điện thoại khẩn cấp với chức năng.',
                [['115', 'Cấp cứu y tế'],
                 ['113', 'Công an'],
                 ['114', 'Cứu hoả'],
                 ['111', 'Bảo vệ trẻ em']],
                'Ghi nhớ các số khẩn cấp để gọi giúp đỡ kịp thời.');
            $this->matching($L, 'Nối mỗi vật dụng với vai trò khi cứu người bị điện giật.',
                [['Gậy gỗ khô', 'Tách nạn nhân khỏi nguồn điện'],
                 ['Vải khô dày', 'Lót tay khi cần chạm vào nạn nhân'],
                 ['Ghế nhựa', 'Đứng lên để cách điện với đất'],
                 ['Tay trần', 'Tuyệt đối không dùng khi chưa ngắt điện']],
                'Chỉ dùng vật cách điện, không dùng tay trần hay vật dẫn điện.');
            $this->matching($L, 'Nối mỗi dấu hiệu với tình trạng nạn nhân.',
                [['Còn thở, còn tỉnh', 'Đặt nằm nghiêng, theo dõi, gọi cấp cứu'],
                 ['Ngừng thở nhưng còn mạch', 'Hô hấp nhân tạo ngay'],
                 ['Ngừng thở, ngừng tim', 'Hô hấp nhân tạo + ép tim ngoài lồng ngực'],
                 ['Bị bỏng do điện', 'Che vết bỏng sạch, đưa đi bệnh viện']],
                'Đánh giá nhanh tình trạng để sơ cứu đúng cách.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm ĐÚNG hoặc SAI khi cứu người bị điện giật.',
                [['Dùng gậy gỗ khô gạt dây điện ra', 'Đúng'],
                 ['Ngắt aptomat ngay', 'Đúng'],
                 ['Gọi cấp cứu 115', 'Đúng'],
                 ['Dùng tay trần kéo nạn nhân', 'Sai'],
                 ['Dội nước vào người đang bị giật', 'Sai'],
                 ['Chờ người khác đến mới cứu', 'Sai']],
                'Hành động nhanh, đúng cách trong "thời gian vàng" quyết định sự sống.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi vật vào nhóm DÙNG ĐƯỢC hoặc KHÔNG DÙNG ĐƯỢC để tách nguồn điện.',
                [['Gậy gỗ khô', 'Dùng được'], ['Ghế nhựa khô', 'Dùng được'],
                 ['Vải khô dày', 'Dùng được'], ['Thanh sắt', 'Không dùng được'],
                 ['Tay trần ướt', 'Không dùng được'], ['Dây thép', 'Không dùng được']],
                'Chỉ dùng vật cách điện khô ráo; kim loại và nước đều dẫn điện.');
            $this->sortQ($L, 'Kéo mỗi bước vào nhóm LÀM TRƯỚC hoặc LÀM SAU khi sơ cứu.',
                [['Tách nạn nhân khỏi nguồn điện', 'Làm trước'],
                 ['Ngắt nguồn điện', 'Làm trước'],
                 ['Kiểm tra hô hấp', 'Làm sau'],
                 ['Hô hấp nhân tạo nếu cần', 'Làm sau'],
                 ['Đảm bảo an toàn cho bản thân', 'Làm trước'],
                 ['Đưa nạn nhân đến bệnh viện', 'Làm sau']],
                'An toàn cho người cứu và tách nguồn điện luôn là ưu tiên đầu tiên.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Sơ cứu càng sớm, cơ hội cứu sống càng cao', 'Đúng'],
                 ['Ép tim 100–120 lần/phút', 'Đúng'],
                 ['Được chạm tay trần vào nạn nhân chưa tách điện', 'Sai'],
                 ['Số cấp cứu y tế là 115', 'Đúng'],
                 ['Nạn nhân tỉnh lại thì không cần đi bệnh viện', 'Sai'],
                 ['Bỏng điện cần được xử lí tại bệnh viện', 'Đúng']],
                'Dù nạn nhân tỉnh lại vẫn cần kiểm tra y tế vì có thể có tổn thương bên trong.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Khi cứu người bị điện giật, tuyệt đối không dùng tay ___ chạm vào nạn nhân khi chưa ngắt điện.', [[0, 'trần']],
                'Dùng gậy gỗ khô, vải khô để tách nạn nhân khỏi nguồn điện.');
            $this->fill($L, 'Số điện thoại cấp cứu y tế là ___.', [[0, '115']],
                'Gọi 115 ngay sau khi tách nạn nhân khỏi nguồn điện.');
            $this->fill($L, 'Tần suất ép tim ngoài lồng ngực là 100–120 ___/phút.', [[0, 'lần']],
                'Ép tim đúng nhịp giúp duy trì tuần hoàn máu cho nạn nhân.');
            $this->fill($L, 'Sơ cứu càng ___, cơ hội cứu sống nạn nhân càng cao.', [[0, 'sớm']],
                '"Thời gian vàng" trong sơ cứu điện giật là những phút đầu tiên.');
        }
    }

    // ================= CÔNG NGHỆ LỚP 11 =================

    private function seedCn111(): void
    {
        $L = 'cong-nghe-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bản vẽ kỹ thuật là gì?',
                ['Ngôn ngữ chung của ngành kỹ thuật, diễn đạt hình dạng, kích thước, vật liệu sản phẩm',
                 'Tranh vẽ nghệ thuật', 'Bản đồ địa lí', 'Sơ đồ tư duy'], 0,
                'Bản vẽ kỹ thuật giúp người thiết kế, chế tạo hiểu đúng ý nhau.');
            $this->quiz($L, 'Đường nét nào dùng để vẽ cạnh thấy, đường bao thấy?',
                ['Nét liền đậm', 'Nét liền mảnh', 'Nét đứt', 'Nét chấm gạch'], 0,
                'Nét liền đậm: cạnh thấy; nét đứt: cạnh khuất; nét chấm gạch mảnh: đường tâm, trục đối xứng.', 'trung_binh');
            $this->quiz($L, 'Đường tâm, trục đối xứng được vẽ bằng nét nào?',
                ['Nét chấm gạch mảnh', 'Nét liền đậm', 'Nét đứt', 'Nét lượn sóng'], 0,
                'Nét chấm gạch mảnh (gạch dài chấm ngắn xen kẽ) vẽ đường tâm, trục đối xứng.');
            $this->quiz($L, 'Khổ giấy A4 có kích thước bao nhiêu?',
                ['210 × 297 mm', '297 × 420 mm', '420 × 594 mm', '148 × 210 mm'], 0,
                'A4: 210×297 mm; A3: 297×420 mm; mỗi khổ sau gấp đôi khổ trước.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại đường nét với công dụng.',
                [['Nét liền đậm', 'Vẽ cạnh thấy, đường bao thấy'],
                 ['Nét liền mảnh', 'Vẽ đường kích thước, đường gióng, đường gạch gạch'],
                 ['Nét đứt', 'Vẽ cạnh khuất'],
                 ['Nét chấm gạch mảnh', 'Vẽ đường tâm, trục đối xứng']],
                'Vẽ đúng loại nét là yêu cầu cơ bản của bản vẽ kỹ thuật.', 'trung_binh');
            $this->matching($L, 'Nối mỗi khổ giấy với kích thước.',
                [['A4', '210 × 297 mm'],
                 ['A3', '297 × 420 mm'],
                 ['A2', '420 × 594 mm'],
                 ['A1', '594 × 841 mm']],
                'Các khổ giấy theo tiêu chuẩn, khổ sau gấp đôi khổ trước.');
            $this->matching($L, 'Nối mỗi thành phần với nội dung trong khung tên.',
                [['Tên gọi', 'Tên của chi tiết, sản phẩm'],
                 ['Vật liệu', 'Vật liệu chế tạo'],
                 ['Tỉ lệ', 'Tỉ lệ giữa kích thước vẽ và thực tế'],
                 ['Người vẽ, ngày vẽ', 'Thông tin trách nhiệm']],
                'Khung tên đặt ở góc dưới bên phải bản vẽ.');
            $this->matching($L, 'Nối mỗi tỉ lệ với ý nghĩa.',
                [['1:1', 'Kích thước vẽ bằng thực tế'],
                 ['2:1', 'Phóng to gấp đôi'],
                 ['1:2', 'Thu nhỏ một nửa'],
                 ['1:10', 'Thu nhỏ mười lần']],
                'Chọn tỉ lệ phù hợp để bản vẽ vừa rõ vừa gọn.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đường nét vào nhóm NÉT ĐẬM hoặc NÉT MẢNH.',
                [['Nét liền đậm', 'Nét đậm'], ['Nét liền mảnh', 'Nét mảnh'],
                 ['Nét đứt mảnh', 'Nét mảnh'], ['Nét chấm gạch mảnh', 'Nét mảnh'],
                 ['Nét lượn sóng', 'Nét mảnh'], ['Nét liền đậm chỉ dùng cho bao thấy', 'Nét đậm']],
                'Chỉ có nét liền đậm là nét đậm; các nét còn lại đều là nét mảnh.');
            $this->sortQ($L, 'Kéo mỗi đối tượng vào nhóm VẼ BẰNG NÉT LIỀN ĐẬM hoặc NÉT ĐỨT.',
                [['Cạnh thấy của hộp', 'Nét liền đậm'], ['Đường bao thấy', 'Nét liền đậm'],
                 ['Cạnh bị che khuất', 'Nét đứt'], ['Lỗ bị che phía sau', 'Nét đứt'],
                 ['Đường viền ngoài vật thể', 'Nét liền đậm'], ['Gân bị khuất', 'Nét đứt']],
                'Thấy: nét liền đậm; khuất: nét đứt.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Bản vẽ kỹ thuật là ngôn ngữ của ngành kỹ thuật', 'Đúng'],
                 ['Đường tâm vẽ bằng nét chấm gạch mảnh', 'Đúng'],
                 ['Khung tên đặt ở góc dưới bên phải', 'Đúng'],
                 ['Cạnh khuất vẽ bằng nét liền đậm', 'Sai'],
                 ['A3 gấp đôi A4', 'Đúng'],
                 ['Tỉ lệ 1:1 nghĩa là thu nhỏ', 'Sai']],
                'Cạnh khuất vẽ nét đứt; tỉ lệ 1:1 là vẽ đúng kích thước thật.');
            $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm DÙNG TRONG VẼ KỸ THUẬT hoặc KHÔNG.',
                [['Thước kẻ', 'Dùng trong vẽ kỹ thuật'], ['Compa', 'Dùng trong vẽ kỹ thuật'],
                 ['Êke', 'Dùng trong vẽ kỹ thuật'], ['Bút chì kỹ thuật', 'Dùng trong vẽ kỹ thuật'],
                 ['Cọ vẽ màu nước', 'Không'], ['Bảng pha màu', 'Không']],
                'Vẽ kỹ thuật cần độ chính xác cao với thước, compa, êke.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bản vẽ kỹ thuật là ___ ngữ chung của ngành kỹ thuật.', [[0, 'ngôn']],
                'Mọi kĩ sư, công nhân đều đọc hiểu bản vẽ theo chuẩn chung.');
            $this->fill($L, 'Cạnh thấy, đường bao thấy được vẽ bằng nét liền ___.', [[0, 'đậm']],
                'Nét liền đậm là nét cơ bản nhất, làm nổi bật hình dạng vật thể.');
            $this->fill($L, 'Cạnh khuất được vẽ bằng nét ___.', [[0, 'đứt']],
                'Nét đứt giúp hình dung phần bị che khuất của vật thể.');
            $this->fill($L, 'Đường tâm, trục đối xứng được vẽ bằng nét chấm ___ mảnh.', [[0, 'gạch']],
                'Nét chấm gạch mảnh gồm gạch dài và chấm xen kẽ.');
        }
    }

    private function seedCn112(): void
    {
        $L = 'cong-nghe-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phép chiếu vuông góc là gì?',
                ['Chiếu vật thể lên mặt phẳng bằng các tia chiếu vuông góc với mặt phẳng chiếu',
                 'Chiếu bằng tia xiên', 'Chụp ảnh vật thể', 'Vẽ phác vật thể'], 0,
                'Tia chiếu vuông góc với mặt phẳng chiếu tạo ra hình chiếu vuông góc.', 'trung_binh');
            $this->quiz($L, 'Hình chiếu đứng là hình chiếu nhìn vật thể từ hướng nào?',
                ['Từ phía trước', 'Từ phía trên', 'Từ phía trái', 'Từ phía sau'], 0,
                'Hình chiếu đứng: nhìn từ trước; hình chiếu bằng: nhìn từ trên; hình chiếu cạnh: nhìn từ trái.');
            $this->quiz($L, 'Vị trí của hình chiếu bằng so với hình chiếu đứng là ở đâu?',
                ['Đặt phía dưới hình chiếu đứng', 'Đặt phía trên hình chiếu đứng',
                 'Đặt bên trái hình chiếu đứng', 'Đặt đè lên hình chiếu đứng'], 0,
                'Quy tắc: hình chiếu bằng dưới hình chiếu đứng; hình chiếu cạnh bên phải hình chiếu đứng.');
            $this->quiz($L, 'Hình chiếu trục đo có ưu điểm gì?',
                ['Thể hiện cả ba chiều trong một hình, dễ hình dung vật thể',
                 'Vẽ rất nhanh', 'Không cần thước', 'Chỉ cần một hình chiếu'], 0,
                'Hình chiếu trục đo (ví dụ vuông góc đều) giúp hình dung không gian vật thể dễ dàng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hình chiếu với hướng nhìn.',
                [['Hình chiếu đứng', 'Nhìn từ phía trước'],
                 ['Hình chiếu bằng', 'Nhìn từ phía trên'],
                 ['Hình chiếu cạnh', 'Nhìn từ phía trái'],
                 ['Hình chiếu trục đo', 'Nhìn theo hướng trục đo, thấy 3 chiều']],
                'Ba hình chiếu vuông góc cho biết đầy đủ hình dạng vật thể.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hình chiếu với vị trí đặt.',
                [['Hình chiếu đứng', 'Vị trí chính giữa'],
                 ['Hình chiếu bằng', 'Đặt phía dưới hình chiếu đứng'],
                 ['Hình chiếu cạnh', 'Đặt bên phải hình chiếu đứng'],
                 ['Kích thước dài', 'Thể hiện trên hình chiếu đứng và bằng']],
                'Các hình chiếu liên hệ với nhau: ngang bằng, dọc thẳng.');
            $this->matching($L, 'Nối mỗi khối hình học với các hình chiếu của nó.',
                [['Hình hộp chữ nhật', 'Ba hình chiếu đều là hình chữ nhật'],
                 ['Hình trụ', 'Đứng: chữ nhật; Bằng: hình tròn'],
                 ['Hình nón', 'Đứng: tam giác; Bằng: hình tròn'],
                 ['Hình cầu', 'Ba hình chiếu đều là hình tròn']],
                'Nhận dạng khối qua hình chiếu là kĩ năng quan trọng.');
            $this->matching($L, 'Nối mỗi loại hình chiếu trục đo với đặc điểm.',
                [['Trục đo vuông góc đều', 'Ba trục bằng nhau, góc 120 độ'],
                 ['Trục đo xiên', 'Một mặt song song với mặt phẳng chiếu'],
                 ['Hình chiếu phối cảnh', 'Gần với mắt nhìn thực tế'],
                 ['Hình chiếu vuông góc', 'Chính xác kích thước từng mặt']],
                'Mỗi loại hình chiếu có ưu điểm riêng khi sử dụng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi mô tả vào nhóm HÌNH CHIẾU ĐỨNG, BẰNG hoặc CẠNH.',
                [['Nhìn từ phía trước', 'Hình chiếu đứng'], ['Thấy mặt chính của vật', 'Hình chiếu đứng'],
                 ['Nhìn từ phía trên xuống', 'Hình chiếu bằng'], ['Thấy mặt trên của vật', 'Hình chiếu bằng'],
                 ['Nhìn từ phía trái', 'Hình chiếu cạnh'], ['Thấy mặt bên của vật', 'Hình chiếu cạnh']],
                'Đứng: trước; Bằng: trên; Cạnh: trái.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi vật thể vào nhóm CÓ HÌNH CHIẾU BẰNG LÀ HÌNH TRÒN hoặc KHÔNG.',
                [['Lon sữa hình trụ', 'Hình tròn'], ['Cái phễu hình nón', 'Hình tròn'],
                 ['Quả bóng', 'Hình tròn'], ['Hộp phấn hình hộp', 'Không'],
                 ['Quyển sách', 'Không'], ['Viên gạch', 'Không']],
                'Vật tròn xoay có hình chiếu bằng (nhìn từ trên) là hình tròn.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Hình chiếu bằng đặt dưới hình chiếu đứng', 'Đúng'],
                 ['Hình chiếu cạnh đặt bên phải hình chiếu đứng', 'Đúng'],
                 ['Hình trụ có hình chiếu bằng là hình tròn', 'Đúng'],
                 ['Hình chiếu trục đo thể hiện 3 chiều', 'Đúng'],
                 ['Chỉ cần một hình chiếu là đủ', 'Sai'],
                 ['Các hình chiếu độc lập, không liên quan', 'Sai']],
                'Các hình chiếu liên hệ chặt chẽ: ngang bằng, dọc thẳng.');
            $this->sortQ($L, 'Kéo mỗi kích thước vào nhóm THỂ HIỆN TRÊN HÌNH CHIẾU ĐỨNG hoặc HÌNH CHIẾU BẰNG.',
                [['Chiều cao vật thể', 'Hình chiếu đứng'], ['Chiều dài vật thể', 'Hình chiếu đứng'],
                 ['Chiều dài vật thể', 'Hình chiếu bằng'], ['Chiều rộng vật thể', 'Hình chiếu bằng'],
                 ['Chiều cao vật thể', 'Hình chiếu đứng'], ['Chiều rộng vật thể', 'Hình chiếu bằng']],
                'Đứng: dài × cao; Bằng: dài × rộng; Cạnh: rộng × cao.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hình chiếu ___ là hình chiếu nhìn vật thể từ phía trước.', [[0, 'đứng']],
                'Hình chiếu đứng thể hiện hình dạng chính của vật thể.');
            $this->fill($L, 'Hình chiếu bằng được đặt phía ___ hình chiếu đứng.', [[0, 'dưới']],
                'Quy tắc bố trí: bằng ở dưới, cạnh ở bên phải hình chiếu đứng.');
            $this->fill($L, 'Hình chiếu ___ đặt bên phải hình chiếu đứng.', [[0, 'cạnh']],
                'Hình chiếu cạnh là hình nhìn từ phía trái vật thể.');
            $this->fill($L, 'Hình chiếu trục đo thể hiện cả ba ___ trong một hình.', [[0, 'chiều']],
                'Giúp hình dung vật thể trong không gian dễ dàng.');
        }
    }

    private function seedCn113(): void
    {
        $L = 'cong-nghe-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi đọc bản vẽ chi tiết, nội dung nào cần đọc đầu tiên?',
                ['Khung tên: tên chi tiết, vật liệu, tỉ lệ', 'Kích thước chi tiết',
                 'Yêu cầu kỹ thuật', 'Hình cắt'], 0,
                'Đọc khung tên trước để biết tổng quan về chi tiết.', 'trung_binh');
            $this->quiz($L, 'Kí hiệu "Ø20" trên bản vẽ có ý nghĩa gì?',
                ['Đường kính bằng 20 mm', 'Bán kính bằng 20 mm', 'Dài 20 mm', 'Rộng 20 mm'], 0,
                'Ø là kí hiệu đường kính; R là kí hiệu bán kính.');
            $this->quiz($L, 'Kích thước định hình cho biết điều gì?',
                ['Độ lớn của chi tiết: dài, rộng, cao, đường kính', 'Vị trí tương đối giữa các phần',
                 'Độ nhám bề mặt', 'Vật liệu chế tạo'], 0,
                'Kích thước định hình: độ lớn; kích thước định vị: vị trí tương đối.');
            $this->quiz($L, 'Trên bản vẽ, con số kích thước được ghi ở đâu?',
                ['Phía trên đường kích thước', 'Phía dưới đường gióng', 'Trong khung tên', 'Ngoài bản vẽ'], 0,
                'Con số kích thước ghi phía trên đường kích thước, đơn vị mặc định là mm.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi kí hiệu với ý nghĩa trên bản vẽ.',
                [['Ø', 'Đường kính'],
                 ['R', 'Bán kính'],
                 ['M', 'Ren hệ mét'],
                 ['□', 'Hình vuông (tiết diện vuông)']],
                'Các kí hiệu giúp đọc kích thước nhanh, chính xác.', 'trung_binh');
            $this->matching($L, 'Nối mỗi bước với nội dung khi đọc bản vẽ chi tiết.',
                [['Bước 1', 'Đọc khung tên'],
                 ['Bước 2', 'Phân tích hình biểu diễn'],
                 ['Bước 3', 'Đọc kích thước'],
                 ['Bước 4', 'Đọc yêu cầu kỹ thuật']],
                'Trình tự đọc giúp không bỏ sót thông tin.');
            $this->matching($L, 'Nối mỗi loại kích thước với nội dung.',
                [['Kích thước định hình', 'Độ lớn: dài, rộng, cao'],
                 ['Kích thước định vị', 'Vị trí tương đối giữa các phần'],
                 ['Kích thước lắp ghép', 'Kích thước liên quan đến lắp ráp'],
                 ['Đơn vị mặc định', 'Milimét (mm)']],
                'Phân biệt các loại kích thước để đọc đúng.');
            $this->matching($L, 'Nối mỗi yêu cầu kỹ thuật với ý nghĩa.',
                [['Độ nhám bề mặt', 'Mức độ nhẵn, bóng của bề mặt'],
                 ['Dung sai', 'Phạm vi cho phép của kích thước'],
                 ['Nhiệt luyện', 'Tôi, ram để tăng độ cứng'],
                 ['Vật liệu', 'Loại vật liệu chế tạo chi tiết']],
                'Yêu cầu kỹ thuật quyết định chất lượng chế tạo.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi kí hiệu vào nhóm KÍCH THƯỚC DÀI hoặc ĐƯỜNG KÍNH/BÁN KÍNH.',
                [['120', 'Kích thước dài'], ['60', 'Kích thước dài'],
                 ['Ø20', 'Đường kính/Bán kính'], ['Ø10', 'Đường kính/Bán kính'],
                 ['R15', 'Đường kính/Bán kính'], ['200', 'Kích thước dài']],
                'Ø: đường kính; R: bán kính; số thường: kích thước dài.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm ĐỌC Ở KHUNG TÊN hoặc ĐỌC Ở HÌNH VẼ.',
                [['Tên chi tiết', 'Khung tên'], ['Vật liệu', 'Khung tên'],
                 ['Tỉ lệ', 'Khung tên'], ['Hình dạng chi tiết', 'Hình vẽ'],
                 ['Kích thước các phần', 'Hình vẽ'], ['Vị trí lỗ', 'Hình vẽ']],
                'Khung tên: thông tin chung; hình vẽ: hình dạng, kích thước cụ thể.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Đọc khung tên trước khi đọc hình vẽ', 'Đúng'],
                 ['Ø là kí hiệu đường kính', 'Đúng'],
                 ['Đơn vị kích thước mặc định là cm', 'Sai'],
                 ['Kích thước định vị cho biết vị trí tương đối', 'Đúng'],
                 ['Con số kích thước ghi dưới đường kích thước', 'Sai'],
                 ['Cần đọc cả yêu cầu kỹ thuật', 'Đúng']],
                'Đơn vị mặc định là mm; số kích thước ghi phía trên đường kích thước.');
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm ĐỌC ĐƯỢC THÔNG TIN hoặc THIẾU THÔNG TIN.',
                [['Có tên, vật liệu, kích thước đầy đủ', 'Đọc được thông tin'],
                 ['Có khung tên và hình biểu diễn rõ', 'Đọc được thông tin'],
                 ['Thiếu khung tên', 'Thiếu thông tin'],
                 ['Không ghi vật liệu', 'Thiếu thông tin'],
                 ['Thiếu kích thước', 'Thiếu thông tin'],
                 ['Hình vẽ mờ, không rõ', 'Thiếu thông tin']],
                'Bản vẽ đầy đủ: khung tên, hình biểu diễn, kích thước, yêu cầu kỹ thuật.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Kí hiệu ___ trên bản vẽ chỉ đường kính của chi tiết tròn.', [[0, 'Ø']],
                'Ví dụ: Ø20 nghĩa là đường kính 20 mm.');
            $this->fill($L, 'Kí hiệu R trên bản vẽ chỉ bán ___ của cung tròn.', [[0, 'kính']],
                'Ví dụ: R15 nghĩa là bán kính 15 mm.');
            $this->fill($L, 'Đơn vị kích thước mặc định trên bản vẽ kỹ thuật là ___ (mm).', [[0, 'milimét']],
                'Nếu dùng đơn vị khác phải ghi rõ.');
            $this->fill($L, 'Khi đọc bản vẽ chi tiết, ta đọc ___ tên trước tiên.', [[0, 'khung']],
                'Khung tên cho biết tên, vật liệu, tỉ lệ của chi tiết.');
        }
    }

    private function seedCn114(): void
    {
        $L = 'cong-nghe-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bản vẽ lắp khác bản vẽ chi tiết ở điểm nào?',
                ['Diễn tả vị trí tương đối của các chi tiết trong sản phẩm, kèm bảng kê',
                 'Chỉ vẽ một chi tiết duy nhất', 'Không có kích thước', 'Chỉ dùng cho nhà cửa'], 0,
                'Bản vẽ lắp thể hiện cách lắp các chi tiết thành sản phẩm hoàn chỉnh.', 'trung_binh');
            $this->quiz($L, 'Bảng kê trên bản vẽ lắp cho biết thông tin gì?',
                ['Số thứ tự, tên gọi, số lượng, vật liệu từng chi tiết',
                 'Giá tiền sản phẩm', 'Tên người mua', 'Ngày sản xuất'], 0,
                'Đối chiếu số thứ tự trên hình vẽ với bảng kê để biết từng chi tiết.');
            $this->quiz($L, 'Trên bản vẽ nhà, mặt bằng cho biết điều gì?',
                ['Bố trí các phòng, cửa đi, cửa sổ nhìn từ trên xuống',
                 'Hình dáng bên ngoài ngôi nhà', 'Cấu tạo móng nhà', 'Màu sơn tường'], 0,
                'Mặt bằng: bố trí không gian; mặt đứng: hình dáng bên ngoài; mặt cắt: cấu tạo bên trong.');
            $this->quiz($L, 'Kí hiệu mũi tên chỉ hướng Bắc trên bản vẽ nhà có tác dụng gì?',
                ['Xác định hướng của ngôi nhà', 'Trang trí bản vẽ',
                 'Chỉ lối vào', 'Chỉ hướng gió'], 0,
                'Biết hướng nhà giúp bố trí phòng ốc hợp phong thuỷ, đón nắng, tránh nắng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại bản vẽ với nội dung thể hiện.',
                [['Bản vẽ chi tiết', 'Hình dạng, kích thước của một chi tiết'],
                 ['Bản vẽ lắp', 'Cách lắp các chi tiết thành sản phẩm'],
                 ['Bản vẽ nhà (mặt bằng)', 'Bố trí các phòng'],
                 ['Bản vẽ nhà (mặt đứng)', 'Hình dáng bên ngoài ngôi nhà']],
                'Mỗi loại bản vẽ phục vụ một mục đích khác nhau.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hình thức với nội dung trên bản vẽ nhà.',
                [['Mặt bằng', 'Bố trí phòng, cửa, cầu thang'],
                 ['Mặt đứng', 'Hình dáng mặt ngoài ngôi nhà'],
                 ['Mặt cắt', 'Cấu tạo bên trong: tường, sàn, mái'],
                 ['Phối cảnh', 'Hình ảnh 3D minh hoạ ngôi nhà']],
                'Bộ hồ sơ bản vẽ nhà gồm nhiều hình thức bổ sung cho nhau.');
            $this->matching($L, 'Nối mỗi kí hiệu trên bản vẽ nhà với ý nghĩa.',
                [['Cửa đi', 'Lối ra vào các phòng'],
                 ['Cửa sổ', 'Lấy sáng, thông gió'],
                 ['Cầu thang', 'Lối lên xuống giữa các tầng'],
                 ['Mũi tên hướng Bắc', 'Xác định hướng ngôi nhà']],
                'Đọc đúng kí hiệu giúp hiểu bố trí ngôi nhà.');
            $this->matching($L, 'Nối mỗi bước với trình tự đọc bản vẽ lắp.',
                [['Bước 1', 'Xem hình biểu diễn chung của sản phẩm'],
                 ['Bước 2', 'Đối chiếu số thứ tự với bảng kê'],
                 ['Bước 3', 'Tìm hiểu từng chi tiết'],
                 ['Bước 4', 'Hiểu nguyên lí làm việc của sản phẩm']],
                'Đọc bản vẽ lắp theo trình tự từ tổng thể đến chi tiết.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm BẢN VẼ CHI TIẾT hoặc BẢN VẼ LẮP.',
                [['Kích thước một chi tiết', 'Bản vẽ chi tiết'],
                 ['Vật liệu một chi tiết', 'Bản vẽ chi tiết'],
                 ['Vị trí tương đối các chi tiết', 'Bản vẽ lắp'],
                 ['Bảng kê chi tiết', 'Bản vẽ lắp'],
                 ['Độ nhám một bề mặt', 'Bản vẽ chi tiết'],
                 ['Cách lắp ráp sản phẩm', 'Bản vẽ lắp']],
                'Chi tiết: một chi tiết; lắp: nhiều chi tiết thành sản phẩm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hình thức vào nhóm BẢN VẼ NHÀ hoặc KHÔNG PHẢI.',
                [['Mặt bằng tầng 1', 'Bản vẽ nhà'], ['Mặt đứng chính', 'Bản vẽ nhà'],
                 ['Mặt cắt A-A', 'Bản vẽ nhà'], ['Bảng kê chi tiết máy', 'Không phải'],
                 ['Hình chiếu trục đo chi tiết', 'Không phải'], ['Sơ đồ mạch điện', 'Không phải']],
                'Bản vẽ nhà: mặt bằng, mặt đứng, mặt cắt.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Bản vẽ lắp có bảng kê chi tiết', 'Đúng'],
                 ['Mặt bằng cho biết bố trí các phòng', 'Đúng'],
                 ['Bản vẽ chi tiết vẽ nhiều chi tiết', 'Sai'],
                 ['Mặt đứng thể hiện hình dáng bên ngoài', 'Đúng'],
                 ['Không cần đọc bảng kê khi đọc bản vẽ lắp', 'Sai'],
                 ['Mặt cắt cho thấy cấu tạo bên trong', 'Đúng']],
                'Bản vẽ chi tiết chỉ vẽ một chi tiết; đọc bản vẽ lắp phải đối chiếu bảng kê.');
            $this->sortQ($L, 'Kéo mỗi nhu cầu vào nhóm LOẠI BẢN VẼ PHÙ HỢP.',
                [['Chế tạo một chi tiết máy', 'Bản vẽ chi tiết'],
                 ['Tiện một trục', 'Bản vẽ chi tiết'],
                 ['Lắp ráp chiếc quạt điện', 'Bản vẽ lắp'],
                 ['Sửa chữa xe máy', 'Bản vẽ lắp'],
                 ['Xây một ngôi nhà', 'Bản vẽ nhà'],
                 ['Bố trí nội thất phòng khách', 'Bản vẽ nhà (mặt bằng)']],
                'Chọn đúng loại bản vẽ cho từng công việc cụ thể.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bản vẽ ___ diễn tả cách lắp các chi tiết thành sản phẩm hoàn chỉnh.', [[0, 'lắp']],
                'Bản vẽ lắp kèm theo bảng kê các chi tiết.');
            $this->fill($L, 'Bảng ___ liệt kê số thứ tự, tên gọi, số lượng, vật liệu từng chi tiết.', [[0, 'kê']],
                'Đối chiếu số thứ tự trên hình vẽ với bảng kê.');
            $this->fill($L, 'Trên bản vẽ nhà, ___ bằng cho biết bố trí các phòng.', [[0, 'mặt']],
                'Mặt bằng là hình nhìn từ trên xuống của ngôi nhà.');
            $this->fill($L, 'Mặt ___ thể hiện hình dáng bên ngoài của ngôi nhà.', [[0, 'đứng']],
                'Kết hợp mặt bằng, mặt đứng, mặt cắt để hiểu đầy đủ ngôi nhà.');
        }
    }

    // ================= CÔNG NGHỆ LỚP 12 =================

    private function seedCn121(): void
    {
        $L = 'cong-nghe-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cuộc cách mạng công nghiệp lần thứ nhất gắn với thành tựu nào?',
                ['Máy hơi nước, cơ khí hoá sản xuất', 'Điện khí hoá', 'Máy tính điện tử', 'Trí tuệ nhân tạo'], 0,
                'Lần 1 (cuối thế kỉ 18): máy hơi nước; lần 2: điện; lần 3: máy tính; lần 4: số hoá, AI.');
            $this->quiz($L, 'Cuộc cách mạng công nghiệp lần thứ tư (4.0) đặc trưng bởi công nghệ nào?',
                ['Trí tuệ nhân tạo, Internet vạn vật (IoT), dữ liệu lớn',
                 'Máy hơi nước', 'Điện khí hoá', 'Dây chuyền lắp ráp thủ công'], 0,
                'Cách mạng 4.0: số hoá, kết nối vạn vật, AI, robot thông minh.', 'trung_binh');
            $this->quiz($L, 'Công nghệ mang lại lợi ích nào cho sản xuất?',
                ['Tăng năng suất, nâng cao chất lượng, giảm chi phí',
                 'Làm tăng giá thành', 'Giảm chất lượng sản phẩm', 'Gây thất nghiệp hoàn toàn'], 0,
                'Công nghệ giúp sản xuất hiệu quả hơn, tiết kiệm tài nguyên.');
            $this->quiz($L, 'Internet vạn vật (IoT) là gì?',
                ['Các thiết bị kết nối Internet, thu thập và trao đổi dữ liệu với nhau',
                 'Mạng Internet tốc độ cao', 'Một loại máy tính', 'Phần mềm diệt virus'], 0,
                'IoT: tủ lạnh, camera, cảm biến... kết nối mạng, hoạt động thông minh.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cuộc cách mạng công nghiệp với thành tựu đặc trưng.',
                [['Lần 1', 'Máy hơi nước, cơ khí hoá'],
                 ['Lần 2', 'Điện khí hoá, sản xuất hàng loạt'],
                 ['Lần 3', 'Máy tính, tự động hoá, công nghệ thông tin'],
                 ['Lần 4', 'Số hoá, AI, IoT, dữ liệu lớn']],
                'Bốn cuộc cách mạng công nghiệp đã thay đổi nền sản xuất thế giới.', 'trung_binh');
            $this->matching($L, 'Nối mỗi công nghệ 4.0 với ứng dụng.',
                [['Trí tuệ nhân tạo (AI)', 'Nhận diện khuôn mặt, trợ lí ảo'],
                 ['IoT', 'Nhà thông minh, cảm biến nông nghiệp'],
                 ['Dữ liệu lớn (Big Data)', 'Phân tích hành vi khách hàng'],
                 ['In 3D', 'Tạo mẫu nhanh, chi tiết phức tạp']],
                'Các công nghệ 4.0 đang ứng dụng rộng rãi trong đời sống.');
            $this->matching($L, 'Nối mỗi lợi ích với ví dụ của công nghệ trong sản xuất.',
                [['Tăng năng suất', 'Máy gặt đập thay hàng chục nhân công'],
                 ['Nâng cao chất lượng', 'Robot hàn chính xác hơn người'],
                 ['Giảm chi phí', 'Tự động hoá giảm nhân công, phế phẩm'],
                 ['Bảo vệ môi trường', 'Công nghệ sạch giảm khí thải']],
                'Công nghệ hiện đại mang lại hiệu quả toàn diện.');
            $this->matching($L, 'Nối mỗi ngành với ứng dụng công nghệ.',
                [['Nông nghiệp', 'Máy bay phun thuốc, cảm biến tưới thông minh'],
                 ['Công nghiệp', 'Robot lắp ráp, dây chuyền tự động'],
                 ['Y tế', 'Chẩn đoán bằng AI, phẫu thuật robot'],
                 ['Giáo dục', 'Học trực tuyến, lớp học thông minh']],
                'Công nghệ thấm sâu vào mọi lĩnh vực đời sống.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thành tựu vào nhóm CÁCH MẠNG LẦN 1–2 hoặc LẦN 3–4.',
                [['Máy hơi nước', 'Lần 1–2'], ['Điện khí hoá', 'Lần 1–2'],
                 ['Sản xuất hàng loạt', 'Lần 1–2'], ['Máy tính điện tử', 'Lần 3–4'],
                 ['Trí tuệ nhân tạo', 'Lần 3–4'], ['Internet vạn vật', 'Lần 3–4']],
                'Lần 1–2: cơ khí, điện; lần 3–4: máy tính, số hoá, AI.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi ứng dụng vào nhóm CÔNG NGHỆ TRONG SẢN XUẤT hoặc TRONG ĐỜI SỐNG.',
                [['Robot lắp ráp ô tô', 'Trong sản xuất'], ['Dây chuyền đóng gói tự động', 'Trong sản xuất'],
                 ['Máy CNC gia công chi tiết', 'Trong sản xuất'], ['Trợ lí ảo trên điện thoại', 'Trong đời sống'],
                 ['Nhà thông minh', 'Trong đời sống'], ['Thanh toán không tiền mặt', 'Trong đời sống']],
                'Công nghệ vừa phục vụ sản xuất vừa phục vụ đời sống hằng ngày.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Cách mạng 4.0 gắn với số hoá và AI', 'Đúng'],
                 ['Công nghệ giúp tăng năng suất', 'Đúng'],
                 ['Máy hơi nước thuộc cách mạng lần 3', 'Sai'],
                 ['IoT là các thiết bị kết nối Internet', 'Đúng'],
                 ['Công nghệ chỉ có lợi, không có thách thức', 'Sai'],
                 ['Tự động hoá giảm sức lao động chân tay', 'Đúng']],
                'Công nghệ mang lại lợi ích lớn nhưng cũng đặt ra thách thức việc làm, an ninh mạng.');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm ỨNG DỤNG CÔNG NGHỆ TỐT hoặc CHƯA TỐT.',
                [['Dùng cảm biến tưới tiết kiệm nước', 'Tốt'],
                 ['Học trực tuyến hiệu quả', 'Tốt'],
                 ['Lạm dụng AI làm bài hộ', 'Chưa tốt'],
                 ['Nghiện mạng xã hội', 'Chưa tốt'],
                 ['Dùng phần mềm quản lí bán hàng', 'Tốt'],
                 ['Phát tán tin giả', 'Chưa tốt']],
                'Dùng công nghệ đúng cách mang lại lợi ích; lạm dụng gây hại.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cuộc cách mạng công nghiệp lần thứ nhất gắn với máy hơi ___.', [[0, 'nước']],
                'Máy hơi nước của James Watt mở đầu cơ khí hoá sản xuất.');
            $this->fill($L, 'Cách mạng công nghiệp 4.0 đặc trưng bởi số hoá, trí tuệ nhân tạo và Internet ___ vật.', [[0, 'vạn']],
                'IoT kết nối mọi thiết bị thành hệ thống thông minh.');
            $this->fill($L, 'Công nghệ giúp tăng năng suất, nâng cao chất lượng và ___ chi phí sản xuất.', [[0, 'giảm']],
                'Sản xuất hiệu quả hơn nhờ công nghệ hiện đại.');
            $this->fill($L, 'Trí tuệ nhân tạo được viết tắt là ___.', [[0, 'AI']],
                'AI ứng dụng trong nhận diện, trợ lí ảo, chẩn đoán y tế...');
        }
    }

    private function seedCn122(): void
    {
        $L = 'cong-nghe-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tự động hoá là gì?',
                ['Ứng dụng kỹ thuật điều khiển để máy móc tự vận hành, giảm can thiệp của con người',
                 'Làm việc hoàn toàn bằng tay', 'Tắt hết máy móc', 'Chỉ dùng trong quân sự'], 0,
                'Tự động hoá: cảm biến – bộ điều khiển – cơ cấu chấp hành phối hợp vận hành.');
            $this->quiz($L, 'Bộ phận nào đóng vai trò "bộ não" trong hệ thống tự động?',
                ['Bộ điều khiển (PLC, máy tính)', 'Cảm biến', 'Động cơ', 'Dây dẫn'], 0,
                'Cảm biến thu thập thông tin; bộ điều khiển xử lí; cơ cấu chấp hành thực hiện.', 'trung_binh');
            $this->quiz($L, 'Robot công nghiệp thường được dùng để làm gì?',
                ['Hàn, sơn, lắp ráp, đóng gói trong nhà máy', 'Nấu ăn gia đình',
                 'Dạy học thay giáo viên', 'Chăm sóc người bệnh tại nhà'], 0,
                'Robot công nghiệp làm việc chính xác, liên tục trong môi trường nguy hiểm.');
            $this->quiz($L, 'Ưu điểm lớn nhất của robot so với lao động thủ công là gì?',
                ['Chính xác, nhanh, làm việc liên tục, thay con người nơi nguy hiểm',
                 'Không bao giờ hỏng', 'Không tốn điện', 'Rẻ hơn mọi trường hợp'], 0,
                'Robot vẫn cần bảo trì, đầu tư ban đầu cao nhưng hiệu quả lâu dài.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bộ phận với vai trò trong hệ thống tự động.',
                [['Cảm biến', 'Thu thập thông tin (nhiệt độ, ánh sáng...)'],
                 ['Bộ điều khiển', 'Xử lí thông tin, ra quyết định'],
                 ['Cơ cấu chấp hành', 'Thực hiện hành động (động cơ, van...)'],
                 ['Nguồn năng lượng', 'Cung cấp điện cho hệ thống']],
                'Hệ thống tự động gồm 4 khối cơ bản phối hợp với nhau.', 'trung_binh');
            $this->matching($L, 'Nối mỗi loại robot với ứng dụng.',
                [['Robot hàn', 'Hàn khung xe ô tô'],
                 ['Robot sơn', 'Sơn xe, đồ gỗ'],
                 ['Tay máy lắp ráp', 'Lắp linh kiện điện tử'],
                 ['Robot giao hàng', 'Giao hàng tự động']],
                'Robot chuyên dụng cho từng công việc cụ thể.');
            $this->matching($L, 'Nối mỗi ví dụ tự động hoá với nơi ứng dụng.',
                [['Đèn tự bật khi trời tối', 'Gia đình thông minh'],
                 ['Tưới cây tự động theo độ ẩm', 'Nông nghiệp'],
                 ['Dây chuyền đóng gói tự động', 'Nhà máy'],
                 ['Cửa tự mở khi có người', 'Toà nhà, siêu thị']],
                'Tự động hoá có mặt khắp nơi trong đời sống.');
            $this->matching($L, 'Nối mỗi ưu điểm với thách thức tương ứng của tự động hoá.',
                [['Tăng năng suất', 'Chi phí đầu tư ban đầu cao'],
                 ['Chính xác, ít lỗi', 'Cần nhân lực trình độ cao vận hành'],
                 ['An toàn nơi nguy hiểm', 'Người lao động cần chuyển đổi nghề'],
                 ['Hoạt động liên tục', 'Cần bảo trì, bảo dưỡng định kì']],
                'Tự động hoá có ưu điểm lớn nhưng cũng đặt ra thách thức.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm THU THẬP, XỬ LÍ hoặc THỰC HIỆN.',
                [['Cảm biến nhiệt độ', 'Thu thập'], ['Camera', 'Thu thập'],
                 ['PLC', 'Xử lí'], ['Máy tính điều khiển', 'Xử lí'],
                 ['Động cơ', 'Thực hiện'], ['Van điện từ', 'Thực hiện']],
                'Cảm biến: mắt tai; bộ điều khiển: bộ não; cơ cấu chấp hành: tay chân.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi công việc vào nhóm NÊN DÙNG ROBOT hoặc NÊN DÙNG NGƯỜI.',
                [['Hàn trong môi trường độc hại', 'Nên dùng robot'],
                 ['Lắp ráp lặp đi lặp lại hàng nghìn lần', 'Nên dùng robot'],
                 ['Sơn trong buồng hoá chất', 'Nên dùng robot'],
                 ['Sáng tạo nghệ thuật', 'Nên dùng người'],
                 ['Chăm sóc, an ủi người bệnh', 'Nên dùng người'],
                 ['Giải quyết tình huống bất ngờ', 'Nên dùng người']],
                'Robot mạnh ở công việc lặp lại, nguy hiểm; con người mạnh ở sáng tạo, cảm xúc.');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Tự động hoá giảm can thiệp của con người', 'Đúng'],
                 ['Cảm biến có vai trò thu thập thông tin', 'Đúng'],
                 ['Robot không bao giờ cần bảo trì', 'Sai'],
                 ['Tự động hoá chỉ dùng trong nhà máy', 'Sai'],
                 ['PLC là một loại bộ điều khiển', 'Đúng'],
                 ['Robot thay thế hoàn toàn con người', 'Sai']],
                'Robot hỗ trợ con người, không thay thế hoàn toàn; vẫn cần bảo trì.');
            $this->sortQ($L, 'Kéo mỗi hệ thống vào nhóm TỰ ĐỘNG HOÁ ĐƠN GIẢN hoặc PHỨC TẠP.',
                [['Đèn cảm biến chuyển động', 'Đơn giản'], ['Bình nóng lạnh tự ngắt', 'Đơn giản'],
                 ['Tưới cây hẹn giờ', 'Đơn giản'], ['Dây chuyền sản xuất ô tô', 'Phức tạp'],
                 ['Nhà máy thông minh', 'Phức tạp'], ['Robot phẫu thuật', 'Phức tạp']],
                'Từ thiết bị đơn giản đến hệ thống phức tạp đều là tự động hoá.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tự động hoá là ứng dụng kỹ thuật ___ khiển để máy móc tự vận hành.', [[0, 'điều']],
                'Giảm sự can thiệp trực tiếp của con người vào sản xuất.');
            $this->fill($L, 'Bộ phận thu thập thông tin trong hệ thống tự động là cảm ___.', [[0, 'biến']],
                'Ví dụ: cảm biến nhiệt độ, ánh sáng, chuyển động.');
            $this->fill($L, 'Bộ điều khiển phổ biến trong công nghiệp là ___ (bộ điều khiển logic khả trình).', [[0, 'PLC']],
                'PLC xử lí tín hiệu và điều khiển máy móc hoạt động.');
            $this->fill($L, 'Robot giúp thay con người làm việc trong môi trường ___ hiểm.', [[0, 'nguy']],
                'Ví dụ: hàn, sơn trong môi trường độc hại.');
        }
    }

    private function seedCn123(): void
    {
        $L = 'cong-nghe-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ô nhiễm không khí chủ yếu do nguyên nhân nào?',
                ['Khí thải công nghiệp, giao thông, bụi mịn', 'Trồng nhiều cây xanh',
                 'Dùng năng lượng mặt trời', 'Đi bộ nhiều'], 0,
                'Khí thải chứa CO, SO₂, bụi mịn gây ô nhiễm không khí nghiêm trọng.');
            $this->quiz($L, 'Nguyên nhân chính gây ô nhiễm nguồn nước là gì?',
                ['Nước thải công nghiệp, sinh hoạt chưa xử lí xả ra sông', 'Mưa nhiều',
                 'Nuôi cá', 'Tắm sông'], 0,
                'Nước thải chưa qua xử lí mang hoá chất, chất hữu cơ gây ô nhiễm.', 'trung_binh');
            $this->quiz($L, 'Bụi mịn PM2.5 nguy hiểm như thế nào?',
                ['Xâm nhập sâu vào phổi, gây bệnh hô hấp, tim mạch',
                 'Chỉ gây ngứa mắt nhẹ', 'Không ảnh hưởng sức khoẻ', 'Chỉ làm bẩn quần áo'], 0,
                'Bụi mịn PM2.5 nhỏ hơn 2.5 micromet, rất nguy hiểm cho sức khoẻ.');
            $this->quiz($L, 'Hoạt động nông nghiệp nào gây ô nhiễm môi trường?',
                ['Lạm dụng thuốc trừ sâu, phân hoá học', 'Trồng rau sạch',
                 'Luân canh cây trồng', 'Dùng phân hữu cơ'], 0,
                'Hoá chất nông nghiệp ngấm vào đất, nước, tồn dư trong thực phẩm.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại ô nhiễm với biểu hiện.',
                [['Ô nhiễm không khí', 'Khói bụi, mùi hôi, bụi mịn'],
                 ['Ô nhiễm nước', 'Nước đổi màu, có mùi, cá chết'],
                 ['Ô nhiễm đất', 'Đất bạc màu, nhiễm kim loại nặng'],
                 ['Ô nhiễm tiếng ồn', 'Tiếng ồn vượt mức gây khó chịu, điếc']],
                'Nhận biết các loại ô nhiễm qua biểu hiện thực tế.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nguồn gây ô nhiễm với loại ô nhiễm chính.',
                [['Nhà máy xả khói', 'Ô nhiễm không khí'],
                 ['Nước thải khu công nghiệp', 'Ô nhiễm nước'],
                 ['Rác thải nhựa', 'Ô nhiễm đất và đại dương'],
                 ['Xe cộ đông đúc', 'Ô nhiễm không khí và tiếng ồn']],
                'Mỗi hoạt động gây ra loại ô nhiễm đặc trưng.');
            $this->matching($L, 'Nối mỗi chất gây ô nhiễm với tác hại.',
                [['Bụi mịn PM2.5', 'Bệnh hô hấp, tim mạch'],
                 ['Thuốc trừ sâu tồn dư', 'Ngộ độc thực phẩm, ung thư'],
                 ['Kim loại nặng (chì, thuỷ ngân)', 'Tổn thương thần kinh, thận'],
                 ['Túi ni lông', 'Tắc cống, hại sinh vật biển']],
                'Hiểu tác hại để có ý thức phòng tránh.');
            $this->matching($L, 'Nối mỗi hiện tượng với nguyên nhân môi trường.',
                [['Hiệu ứng nhà kính', 'Khí CO₂, mêtan tăng cao'],
                 ['Mưa axit', 'Khí SO₂, NO₂ từ nhà máy'],
                 ['Suy giảm tầng ôzôn', 'Khí CFC'],
                 ['Nước biển dâng', 'Băng tan do nóng lên toàn cầu']],
                'Các vấn đề môi trường toàn cầu đều do hoạt động con người.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm GÂY Ô NHIỄM hoặc BẢO VỆ MÔI TRƯỜNG.',
                [['Xả thải chưa xử lí ra sông', 'Gây ô nhiễm'],
                 ['Đốt rác thải bừa bãi', 'Gây ô nhiễm'],
                 ['Lạm dụng thuốc trừ sâu', 'Gây ô nhiễm'],
                 ['Phân loại rác tại nguồn', 'Bảo vệ môi trường'],
                 ['Trồng cây xanh', 'Bảo vệ môi trường'],
                 ['Dùng túi vải thay ni lông', 'Bảo vệ môi trường']],
                'Mỗi hành động hằng ngày đều tác động đến môi trường.');
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm Ô NHIỄM KHÔNG KHÍ, NƯỚC hoặc ĐẤT.',
                [['Khói đen từ nhà máy', 'Ô nhiễm không khí'], ['Bụi mịn dày đặc', 'Ô nhiễm không khí'],
                 ['Sông đổi màu, bốc mùi', 'Ô nhiễm nước'], ['Cá chết hàng loạt', 'Ô nhiễm nước'],
                 ['Đất nhiễm thuốc trừ sâu', 'Ô nhiễm đất'], ['Rác thải chôn lấp', 'Ô nhiễm đất']],
                'Phân loại đúng giúp có biện pháp xử lí phù hợp.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Bụi mịn PM2.5 gây bệnh hô hấp', 'Đúng'],
                 ['Nước thải cần xử lí trước khi xả', 'Đúng'],
                 ['Túi ni lông phân huỷ nhanh', 'Sai'],
                 ['Mưa axit do khí thải công nghiệp', 'Đúng'],
                 ['Ô nhiễm đất không ảnh hưởng sức khoẻ', 'Sai'],
                 ['Tiếng ồn lớn gây hại thính giác', 'Đúng']],
                'Túi ni lông cần hàng trăm năm mới phân huỷ; ô nhiễm đất ảnh hưởng qua thực phẩm.');
            $this->sortQ($L, 'Kéo mỗi nguồn thải vào nhóm CÔNG NGHIỆP, NÔNG NGHIỆP hoặc SINH HOẠT.',
                [['Khí thải nhà máy', 'Công nghiệp'], ['Nước thải khu chế xuất', 'Công nghiệp'],
                 ['Thuốc trừ sâu', 'Nông nghiệp'], ['Phân hoá học dư thừa', 'Nông nghiệp'],
                 ['Rác thải gia đình', 'Sinh hoạt'], ['Nước thải nhà vệ sinh', 'Sinh hoạt']],
                'Ô nhiễm đến từ cả ba nguồn: công nghiệp, nông nghiệp, sinh hoạt.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bụi mịn ___ rất nguy hiểm vì xâm nhập sâu vào phổi.', [[0, 'PM2.5']],
                'PM2.5 nhỏ hơn 2.5 micromet, gây bệnh hô hấp, tim mạch.');
            $this->fill($L, 'Nước thải cần được xử lí trước khi ___ ra môi trường.', [[0, 'xả']],
                'Xả thải chưa xử lí là nguyên nhân chính gây ô nhiễm nước.');
            $this->fill($L, 'Lạm dụng thuốc trừ sâu gây ô nhiễm đất, nước và tồn dư trong thực ___.', [[0, 'phẩm']],
                'Ưu tiên biện pháp sinh học, dùng thuốc đúng liều lượng.');
            $this->fill($L, 'Túi ni lông cần hàng trăm năm mới phân ___.', [[0, 'huỷ']],
                'Hạn chế nhựa dùng một lần để bảo vệ môi trường.');
        }
    }

    private function seedCn124(): void
    {
        $L = 'cong-nghe-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phát triển bền vững là gì?',
                ['Phát triển đáp ứng nhu cầu hiện tại mà không tổn hại khả năng của thế hệ tương lai',
                 'Phát triển kinh tế bằng mọi giá', 'Chỉ tập trung tăng trưởng GDP', 'Ngừng mọi hoạt động sản xuất'], 0,
                'Bền vững: hài hoà kinh tế – xã hội – môi trường.', 'trung_binh');
            $this->quiz($L, 'Biện pháp nào sau đây góp phần bảo vệ môi trường?',
                ['Phân loại rác tại nguồn và tái chế', 'Xả rác bừa bãi',
                 'Đốt rác thải nhựa', 'Xả thải trực tiếp ra sông'], 0,
                'Phân loại, tái chế giảm rác thải, tiết kiệm tài nguyên.');
            $this->quiz($L, 'Nguồn năng lượng nào sau đây là năng lượng tái tạo?',
                ['Mặt trời, gió', 'Than đá', 'Dầu mỏ', 'Khí đốt'], 0,
                'Năng lượng tái tạo: mặt trời, gió, thuỷ triều...; hoá thạch: than, dầu, khí.');
            $this->quiz($L, 'Học sinh có thể góp phần bảo vệ môi trường bằng việc làm nào?',
                ['Tiết kiệm điện nước, hạn chế nhựa dùng một lần, trồng cây',
                 'Xả rác bừa bãi', 'Dùng nhiều túi ni lông', 'Đốt rác trong sân trường'], 0,
                'Mỗi hành động nhỏ hằng ngày đều góp phần bảo vệ môi trường.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi biện pháp với tác dụng bảo vệ môi trường.',
                [['Xử lí khí thải, nước thải', 'Giảm ô nhiễm trước khi thải ra'],
                 ['Công nghệ sạch', 'Tiết kiệm tài nguyên, ít phát thải'],
                 ['Tái chế rác thải', 'Giảm rác, tiết kiệm nguyên liệu'],
                 ['Trồng cây xanh', 'Lọc không khí, chống xói mòn']],
                'Bảo vệ môi trường cần nhiều biện pháp đồng bộ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nguồn năng lượng với loại.',
                [['Mặt trời', 'Năng lượng tái tạo'],
                 ['Gió', 'Năng lượng tái tạo'],
                 ['Than đá', 'Năng lượng hoá thạch'],
                 ['Dầu mỏ', 'Năng lượng hoá thạch']],
                'Chuyển sang năng lượng tái tạo giúp giảm phát thải.');
            $this->matching($L, 'Nối mỗi trụ cột với nội dung của phát triển bền vững.',
                [['Kinh tế', 'Tăng trưởng hiệu quả, ổn định'],
                 ['Xã hội', 'Công bằng, an sinh, giáo dục'],
                 ['Môi trường', 'Bảo vệ tài nguyên, hệ sinh thái'],
                 ['Bền vững', 'Ba trụ cột hài hoà, lâu dài']],
                'Thiếu một trụ cột thì phát triển không bền vững.');
            $this->matching($L, 'Nối mỗi việc làm của học sinh với ý nghĩa.',
                [['Tiết kiệm điện', 'Giảm tiêu thụ năng lượng'],
                 ['Mang bình nước cá nhân', 'Giảm chai nhựa dùng một lần'],
                 ['Tham gia trồng cây', 'Tăng mảng xanh'],
                 ['Tuyên truyền bảo vệ môi trường', 'Lan toả ý thức cộng đồng']],
                'Học sinh hoàn toàn có thể hành động vì môi trường.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BẢO VỆ MÔI TRƯỜNG hoặc GÂY HẠI MÔI TRƯỜNG.',
                [['Đi bộ, đi xe đạp quãng ngắn', 'Bảo vệ môi trường'],
                 ['Tái chế giấy, chai nhựa', 'Bảo vệ môi trường'],
                 ['Tiết kiệm nước', 'Bảo vệ môi trường'],
                 ['Đốt rơm rạ ngoài đồng', 'Gây hại môi trường'],
                 ['Xả pin cũ bừa bãi', 'Gây hại môi trường'],
                 ['Dùng nhiều đồ nhựa một lần', 'Gây hại môi trường']],
                'Lựa chọn hằng ngày quyết định môi trường xung quanh.');
            $this->sortQ($L, 'Kéo mỗi nguồn năng lượng vào nhóm TÁI TẠO hoặc HOÁ THẠCH.',
                [['Điện mặt trời', 'Tái tạo'], ['Điện gió', 'Tái tạo'],
                 ['Thuỷ điện', 'Tái tạo'], ['Than đá', 'Hoá thạch'],
                 ['Dầu mỏ', 'Hoá thạch'], ['Khí thiên nhiên', 'Hoá thạch']],
                'Năng lượng tái tạo sạch và không cạn kiệt.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.',
                [['Phát triển bền vững hài hoà 3 trụ cột', 'Đúng'],
                 ['Tái chế giúp tiết kiệm tài nguyên', 'Đúng'],
                 ['Năng lượng mặt trời là tái tạo', 'Đúng'],
                 ['Bảo vệ môi trường là việc của nhà nước', 'Sai'],
                 ['Pin cũ nên bỏ chung rác thường', 'Sai'],
                 ['Trồng cây giúp lọc không khí', 'Đúng']],
                'Bảo vệ môi trường là trách nhiệm của mọi người; pin cũ là rác nguy hại.');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm 3R: REDUCE, REUSE hoặc RECYCLE.',
                [['Hạn chế mua đồ không cần thiết', 'Reduce (giảm thiểu)'],
                 ['Mang túi vải đi chợ', 'Reduce (giảm thiểu)'],
                 ['Dùng lại chai thuỷ tinh đựng nước', 'Reuse (tái sử dụng)'],
                 ['Tận dụng giấy một mặt', 'Reuse (tái sử dụng)'],
                 ['Bán giấy vụn, chai nhựa phế liệu', 'Recycle (tái chế)'],
                 ['Ủ rác hữu cơ làm phân bón', 'Recycle (tái chế)']],
                '3R: giảm thiểu – tái sử dụng – tái chế là nguyên tắc vàng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phát triển ___ vững là phát triển đáp ứng nhu cầu hiện tại mà không tổn hại thế hệ tương lai.', [[0, 'bền']],
                'Ba trụ cột: kinh tế – xã hội – môi trường hài hoà.');
            $this->fill($L, 'Nguyên tắc 3R gồm: giảm thiểu, tái sử dụng và tái ___.', [[0, 'chế']],
                'Reduce – Reuse – Recycle giúp giảm rác thải hiệu quả.');
            $this->fill($L, 'Điện mặt trời, điện gió là nguồn năng lượng tái ___.', [[0, 'tạo']],
                'Năng lượng tái tạo sạch, không cạn kiệt, ít phát thải.');
            $this->fill($L, 'Pin cũ là rác nguy hại, cần thu gom riêng, không bỏ chung rác ___ thường.', [[0, 'thải']],
                'Pin chứa kim loại nặng gây ô nhiễm đất, nước nghiêm trọng.');
        }
    }
}
