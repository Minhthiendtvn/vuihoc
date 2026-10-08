<?php

namespace Database\Seeders;

use App\Models\FillAnswer;
use App\Models\Lesson;
use App\Models\MatchingPair;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Skill;
use App\Models\SortItem;
use App\Models\Topic;
use Illuminate\Database\Seeder;

/**
 * Phủ dữ liệu theo khối lớp cho NHÓM A (Toán, Tiếng Việt, Tiếng Anh).
 *
 * Với mỗi topic và mỗi lớp trong [grade_min..grade_max]: tạo 2 bài học mới
 * gắn vào skill đầu tiên (sort_order nhỏ nhất) của topic, slug
 * {topic-slug}-lop-{grade}-1/2. Mỗi bài: ≥4 quiz + ≥4 matching + ≥4 sort
 * + ≥4 fill (≥16 câu). Nội dung TỰ VIẾT 100% tiếng Việt, bám chương trình
 * đúng khối lớp, độ khó tăng dần theo lớp.
 *
 * Idempotent: bài học bỏ qua khi slug đã tồn tại; câu hỏi bỏ qua khi
 * (lesson_id, game_type) đã được seed.
 */
class GradeCoverageGroupASeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $gradeBySlug = [];

    /** topic_slug => [grade_min, grade_max] */
    private array $coverage = [
        'toan-so-tu-nhien'      => [6, 7],
        'toan-phan-so'          => [6, 7],
        'toan-bieu-thuc-dai-so' => [7, 8],
        'toan-hinh-hoc-phang'   => [7, 8],
        'tv-tu-va-cau'          => [6, 7],
        'tv-chinh-ta'           => [6, 7],
        'tv-van-mieu-ta'        => [7, 8],
        'en-tu-vung-lop-6'      => [6, 7],
        'en-ngu-phap-co-ban'    => [6, 7],
        'en-tu-vung-lop-7'      => [7, 8],
    ];

    public function run(): void
    {
        foreach ($this->coverage as $topicSlug => [$gmin, $gmax]) {
            $topic = Topic::where('slug', $topicSlug)->firstOrFail();
            $skill = Skill::where('topic_id', $topic->id)->orderBy('sort_order')->firstOrFail();
            for ($grade = $gmin; $grade <= $gmax; $grade++) {
                for ($num = 1; $num <= 2; $num++) {
                    $meta = $this->lessonMeta($topicSlug, $grade, $num);
                    $this->createLesson($skill, $topicSlug, $grade, $num, $meta);
                }
            }
        }

        // ---- Toán ----
        $this->seedToanSoTuNhien61(); $this->seedToanSoTuNhien62();
        $this->seedToanSoTuNhien71(); $this->seedToanSoTuNhien72();
        $this->seedToanPhanSo61(); $this->seedToanPhanSo62();
        $this->seedToanPhanSo71(); $this->seedToanPhanSo72();
        $this->seedToanDaiSo71(); $this->seedToanDaiSo72();
        $this->seedToanDaiSo81(); $this->seedToanDaiSo82();
        $this->seedToanHinhHoc71(); $this->seedToanHinhHoc72();
        $this->seedToanHinhHoc81(); $this->seedToanHinhHoc82();
        // ---- Tiếng Việt ----
        $this->seedTvTuCau61(); $this->seedTvTuCau62();
        $this->seedTvTuCau71(); $this->seedTvTuCau72();
        $this->seedTvChinhTa61(); $this->seedTvChinhTa62();
        $this->seedTvChinhTa71(); $this->seedTvChinhTa72();
        $this->seedTvMieuTa71(); $this->seedTvMieuTa72();
        $this->seedTvMieuTa81(); $this->seedTvMieuTa82();
        // ---- Tiếng Anh ----
        $this->seedEnVocab661(); $this->seedEnVocab662();
        $this->seedEnVocab671(); $this->seedEnVocab672();
        $this->seedEnGrammar61(); $this->seedEnGrammar62();
        $this->seedEnGrammar71(); $this->seedEnGrammar72();
        $this->seedEnVocab771(); $this->seedEnVocab772();
        $this->seedEnVocab781(); $this->seedEnVocab782();
        $this->seedSortExtra();
    }

    // ---------------- helpers ----------------

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
     * Siêu dữ liệu 40 bài học: [$topicSlug][$grade][$num] =>
     * [title, objective, difficulty, duration, instructions].
     */
    private function lessonMeta(string $topicSlug, int $grade, int $num): array
    {
        $plan = [
            'toan-so-tu-nhien' => [
                6 => [
                    1 => ['title' => 'Số tự nhiên lớp 6: phép cộng và phép trừ (1)',
                        'objective' => 'Thực hiện thành thạo phép cộng, trừ số tự nhiên; vận dụng tính chất giao hoán, kết hợp để tính nhanh.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Cộng, trừ theo hàng từ phải sang trái. Tính chất giao hoán: a + b = b + a. Tính chất kết hợp: (a + b) + c = a + (b + c). Nhóm các số tròn chục, tròn trăm để tính nhanh.'],
                    2 => ['title' => 'Số tự nhiên lớp 6: phép nhân, phép chia và thứ tự thực hiện (2)',
                        'objective' => 'Thực hiện phép nhân, phép chia hết và chia có dư; nắm thứ tự ưu tiên: ngoặc → nhân, chia → cộng, trừ.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Trong phép chia có dư: số bị chia = thương × số chia + số dư (số dư nhỏ hơn số chia). Thứ tự thực hiện: tính trong ngoặc trước, rồi đến nhân – chia, cuối cùng là cộng – trừ.'],
                ],
                7 => [
                    1 => ['title' => 'Số tự nhiên lớp 7: lũy thừa với số mũ tự nhiên (1)',
                        'objective' => 'Hiểu định nghĩa lũy thừa a^n; nhân, chia hai lũy thừa cùng cơ số.',
                        'difficulty' => 'de', 'duration' => 12,
                        'instructions' => 'Lũy thừa bậc n của a là tích của n thừa số a: a^n = a × a × ... × a (n lần). Nhân hai lũy thừa cùng cơ số: a^m × a^n = a^(m+n). Chia hai lũy thừa cùng cơ số: a^m : a^n = a^(m−n) (với m ≥ n, a ≠ 0).'],
                    2 => ['title' => 'Số tự nhiên lớp 7: biểu thức phức tạp và tính nhanh (2)',
                        'objective' => 'Tính giá trị biểu thức có lũy thừa và ngoặc; vận dụng tính chất phân phối để tính nhanh.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Thứ tự ưu tiên đầy đủ: ngoặc → lũy thừa → nhân, chia → cộng, trừ. Tính chất phân phối: a × (b + c) = a × b + a × c. Dùng tính chất này để tính nhanh các tích gần số tròn như 99, 101.'],
                ],
            ],
            'toan-phan-so' => [
                6 => [
                    1 => ['title' => 'Phân số lớp 6: cộng, trừ phân số đơn giản (1)',
                        'objective' => 'Cộng, trừ hai phân số cùng mẫu số; rút gọn kết quả về phân số tối giản.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Muốn cộng (hoặc trừ) hai phân số cùng mẫu số, ta cộng (hoặc trừ) hai tử số và giữ nguyên mẫu số. Sau khi tính, nhớ rút gọn kết quả nếu chưa tối giản.'],
                    2 => ['title' => 'Phân số lớp 6: rút gọn, quy đồng và so sánh (2)',
                        'objective' => 'Rút gọn phân số; quy đồng mẫu số; so sánh hai phân số.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Rút gọn: chia cả tử và mẫu cho ước chung lớn nhất. Quy đồng: tìm mẫu số chung (thường là bội chung nhỏ nhất), nhân cả tử và mẫu với thừa số phụ. So sánh: phân số nào có tử lớn hơn (khi cùng mẫu dương) thì lớn hơn.'],
                ],
                7 => [
                    1 => ['title' => 'Phân số lớp 7: nhân, chia phân số và hỗn số (1)',
                        'objective' => 'Nhân, chia hai phân số; đổi hỗn số thành phân số và ngược lại.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Nhân hai phân số: nhân tử với tử, mẫu với mẫu. Chia: nhân phân số bị chia với phân số nghịch đảo của số chia. Hỗn số a b/c = (a × c + b)/c.'],
                    2 => ['title' => 'Phân số lớp 7: biểu thức phân số và bài toán ứng dụng (2)',
                        'objective' => 'Tính giá trị biểu thức có nhiều phép tính với phân số; giải bài toán thực tế đơn giản.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Với biểu thức nhiều phép tính, tuân thủ thứ tự ưu tiên và dùng tính chất phân phối để tính nhanh, ví dụ (a + b) × c = a × c + b × c. Với bài toán thực tế, xác định "cả" tương ứng với 1 rồi tính từng phần.'],
                ],
            ],
            'toan-bieu-thuc-dai-so' => [
                7 => [
                    1 => ['title' => 'Biểu thức đại số lớp 7: biểu thức chữ và giá trị biểu thức (1)',
                        'objective' => 'Nhận biết biểu thức đại số; tính giá trị của biểu thức khi biết giá trị của biến.',
                        'difficulty' => 'de', 'duration' => 12,
                        'instructions' => 'Biểu thức đại số là biểu thức gồm các số, chữ và phép tính. Muốn tính giá trị của biểu thức, ta thay giá trị của biến vào rồi thực hiện phép tính. Đơn thức gồm hệ số và phần biến, ví dụ trong 5x² thì 5 là hệ số, x² là phần biến.'],
                    2 => ['title' => 'Biểu thức đại số lớp 7: đơn thức đồng dạng (2)',
                        'objective' => 'Nhận biết hai đơn thức đồng dạng; cộng, trừ các đơn thức đồng dạng.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Hai đơn thức đồng dạng là hai đơn thức có hệ số khác 0 và có cùng phần biến. Muốn cộng (trừ) các đơn thức đồng dạng, ta cộng (trừ) các hệ số và giữ nguyên phần biến.'],
                ],
                8 => [
                    1 => ['title' => 'Biểu thức đại số lớp 8: đa thức và thu gọn đa thức (1)',
                        'objective' => 'Nhận biết đa thức, bậc của đa thức; thu gọn đa thức và tìm hệ số.',
                        'difficulty' => 'trung_binh', 'duration' => 15,
                        'instructions' => 'Đa thức là tổng của những đơn thức. Bậc của đa thức là bậc của hạng tử có bậc cao nhất. Thu gọn đa thức: cộng, trừ các hạng tử đồng dạng. Hệ số cao nhất là hệ số của hạng tử bậc cao nhất; hệ số tự do là hạng tử không chứa biến.'],
                    2 => ['title' => 'Biểu thức đại số lớp 8: nhân đơn thức, đa thức (2)',
                        'objective' => 'Nhân hai đơn thức; nhân đơn thức với đa thức; nhận biết hằng đẳng thức cơ bản.',
                        'difficulty' => 'trung_binh', 'duration' => 15,
                        'instructions' => 'Nhân hai đơn thức: nhân hệ số với hệ số, nhân phần biến với phần biến. Nhân đơn thức với đa thức: nhân đơn thức với từng hạng tử rồi cộng lại. Hằng đẳng thức đáng nhớ: (a + b)² = a² + 2ab + b²; (a − b)(a + b) = a² − b².'],
                ],
            ],
            'toan-hinh-hoc-phang' => [
                7 => [
                    1 => ['title' => 'Hình học phẳng lớp 7: góc và cách đo góc (1)',
                        'objective' => 'Nhận biết các loại góc: nhọn, vuông, tù, bẹt; đo góc bằng thước đo độ.',
                        'difficulty' => 'de', 'duration' => 12,
                        'instructions' => 'Góc nhọn nhỏ hơn 90°, góc vuông bằng 90°, góc tù lớn hơn 90° và nhỏ hơn 180°, góc bẹt bằng 180°. Dùng thước đo độ đặt tâm trùng đỉnh, một cạnh trùng vạch 0° để đọc số đo.'],
                    2 => ['title' => 'Hình học phẳng lớp 7: góc kề bù và góc đối đỉnh (2)',
                        'objective' => 'Nhận biết hai góc kề bù, hai góc đối đỉnh; tính số đo góc; hiểu tia phân giác.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Hai góc kề bù là hai góc kề nhau có tổng số đo 180°. Hai góc đối đỉnh thì bằng nhau. Tia phân giác của một góc là tia nằm trong góc và chia góc thành hai góc bằng nhau.'],
                ],
                8 => [
                    1 => ['title' => 'Hình học phẳng lớp 8: tổng ba góc tam giác, tam giác cân (1)',
                        'objective' => 'Vận dụng tổng ba góc trong tam giác bằng 180°; nhận biết và vận dụng tính chất tam giác cân, tam giác đều.',
                        'difficulty' => 'trung_binh', 'duration' => 15,
                        'instructions' => 'Tổng ba góc trong của một tam giác bằng 180°. Tam giác cân có hai cạnh bên bằng nhau và hai góc ở đáy bằng nhau. Tam giác đều có ba cạnh bằng nhau và ba góc đều bằng 60°.'],
                    2 => ['title' => 'Hình học phẳng lớp 8: tam giác đồng dạng cơ bản (2)',
                        'objective' => 'Nhận biết hai tam giác đồng dạng; vận dụng tỉ số đồng dạng tính độ dài cạnh.',
                        'difficulty' => 'trung_binh', 'duration' => 15,
                        'instructions' => 'Hai tam giác đồng dạng (kí hiệu ~) có các góc tương ứng bằng nhau và các cạnh tương ứng tỉ lệ. Tỉ số đồng dạng k cho biết cạnh của tam giác này gấp k lần cạnh tương ứng của tam giác kia; tỉ số chu vi cũng bằng k. Các trường hợp đồng dạng: cạnh – cạnh – cạnh, góc – góc, cạnh – góc – cạnh.'],
                ],
            ],
            'tv-tu-va-cau' => [
                6 => [
                    1 => ['title' => 'Từ và câu lớp 6: từ đơn, từ ghép, từ láy (1)',
                        'objective' => 'Phân biệt từ đơn, từ phức; nhận biết từ ghép và từ láy.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Từ đơn chỉ gồm một tiếng. Từ phức gồm hai tiếng trở lên, chia thành từ ghép (các tiếng đều có nghĩa, ví dụ bàn ghế) và từ láy (láy lại âm hoặc vần, ví dụ xinh xắn, rì rào).'],
                    2 => ['title' => 'Từ và câu lớp 6: danh từ, động từ, tính từ (2)',
                        'objective' => 'Nhận biết danh từ, động từ, tính từ trong câu.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Danh từ chỉ người, vật, hiện tượng (danh từ riêng chỉ tên riêng phải viết hoa). Động từ chỉ hoạt động, trạng thái. Tính từ chỉ đặc điểm, tính chất.'],
                ],
                7 => [
                    1 => ['title' => 'Từ và câu lớp 7: cụm danh từ, cụm động từ, cụm tính từ (1)',
                        'objective' => 'Nhận biết cụm từ; xác định từ trung tâm và phần phụ trong cụm danh từ, động từ, tính từ.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Cụm từ gồm từ trung tâm và phần phụ (phụ trước, phụ sau). Cụm danh từ có từ trung tâm là danh từ (ví dụ những bông hoa đẹp), cụm động từ có từ trung tâm là động từ (đang nở rộ), cụm tính từ có từ trung tâm là tính từ (rất thơm).'],
                    2 => ['title' => 'Từ và câu lớp 7: câu đơn, chủ ngữ – vị ngữ (2)',
                        'objective' => 'Nhận biết câu đơn; xác định chủ ngữ và vị ngữ trong câu.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Câu đơn có một cụm chủ ngữ – vị ngữ. Chủ ngữ trả lời câu hỏi Ai? Cái gì? Con gì? Vị ngữ trả lời câu hỏi Làm gì? Thế nào? Là gì?'],
                ],
            ],
            'tv-chinh-ta' => [
                6 => [
                    1 => ['title' => 'Chính tả lớp 6: phân biệt ch/tr, s/x, r/d/gi (1)',
                        'objective' => 'Viết đúng các từ có âm đầu ch/tr, s/x, r/d/gi dễ nhầm lẫn.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Muốn viết đúng, hãy đọc kĩ và ghi nhớ mặt chữ của từng từ: chăm chỉ (ch), trong trẻo (tr), sáng sủa (s), xinh xắn (x), rực rỡ (r), dịu dàng (d). Khi nghi ngờ, tra từ điển hoặc hỏi thầy cô.'],
                    2 => ['title' => 'Chính tả lớp 6: dấu hỏi, dấu ngã (2)',
                        'objective' => 'Viết đúng dấu hỏi và dấu ngã trong các từ thường nhầm.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Ghi nhớ theo cặp dễ nhầm: củ (hỏi) – cũ (ngã), nghỉ (hỏi) – nghĩ (ngã), vẻ (hỏi) – vẽ (ngã). Đặt từ vào nghĩa của câu để chọn dấu đúng: "nghỉ ngơi" (dấu hỏi), "suy nghĩ" (dấu ngã).'],
                ],
                7 => [
                    1 => ['title' => 'Chính tả lớp 7: viết hoa tên riêng, từ Hán Việt (1)',
                        'objective' => 'Viết hoa đúng tên người, tên địa lí; viết đúng các từ Hán Việt thông dụng.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Tên riêng (người, địa danh) viết hoa chữ cái đầu mỗi tiếng: Hà Nội, Nguyễn Du, Sông Hồng. Từ Hán Việt là từ gốc Hán đã Việt hoá, cần nhớ mặt chữ: tổ quốc, giang sơn, đồng bào.'],
                    2 => ['title' => 'Chính tả lớp 7: từ dễ nhầm và dấu câu (2)',
                        'objective' => 'Viết đúng các từ khó dễ nhầm; đặt dấu chấm, hỏi, chấm than, phẩy đúng chỗ.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Một số từ khó cần học thuộc: xán lạn, trau chuốt, dè dặt. Về dấu câu: cuối câu kể đặt dấu chấm, cuối câu hỏi đặt dấu hỏi, cuối câu cảm thán đặt dấu chấm than, dấu phẩy ngăn cách các bộ phận trong câu.'],
                ],
            ],
            'tv-van-mieu-ta' => [
                7 => [
                    1 => ['title' => 'Văn miêu tả lớp 7: nhận biết và từ ngữ miêu tả (1)',
                        'objective' => 'Nhận biết văn miêu tả; dùng từ ngữ gợi hình, gợi âm thanh và biện pháp tu từ đơn giản.',
                        'difficulty' => 'de', 'duration' => 12,
                        'instructions' => 'Văn miêu tả giúp người đọc hình dung sự vật như đang tận mắt trông thấy. Dùng từ láy gợi hình (xanh biếc), gợi âm thanh (rì rào) và các biện pháp so sánh, nhân hoá để câu văn sinh động.'],
                    2 => ['title' => 'Văn miêu tả lớp 7: tả người (2)',
                        'objective' => 'Nắm bố cục bài văn tả người; tả ngoại hình và tính cách theo trình tự hợp lí.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Bài văn tả người có 3 phần: mở bài giới thiệu người được tả, thân bài tả chi tiết (ngoại hình từ bao quát đến chi tiết, rồi tính cách, hoạt động), kết bài nêu cảm nghĩ.'],
                ],
                8 => [
                    1 => ['title' => 'Văn miêu tả lớp 8: tả cảnh và biện pháp tu từ (1)',
                        'objective' => 'Tả cảnh gắn với thời gian, không gian; vận dụng so sánh, nhân hoá.',
                        'difficulty' => 'trung_binh', 'duration' => 15,
                        'instructions' => 'Tả cảnh cần quan sát tinh tế và gắn với thời gian, không gian cụ thể (buổi sáng, hoàng hôn...). Dùng so sánh (trăng như chiếc đĩa bạc) và nhân hoá (chị gió đùa vui) để cảnh vật có hồn.'],
                    2 => ['title' => 'Văn miêu tả lớp 8: cảm thụ và viết đoạn văn (2)',
                        'objective' => 'Cảm thụ cái hay của đoạn văn miêu tả; viết đoạn văn có hình ảnh và cảm xúc, tránh lỗi thường gặp.',
                        'difficulty' => 'trung_binh', 'duration' => 15,
                        'instructions' => 'Đoạn văn miêu tả hay cần hình ảnh sinh động và cảm xúc chân thành. Khi viết: quan sát kĩ, sắp xếp ý hợp lí, dùng từ gợi hình gợi cảm, tránh lặp từ và liệt kê số liệu khô khan.'],
                ],
            ],
            'en-tu-vung-lop-6' => [
                6 => [
                    1 => ['title' => 'Từ vựng lớp 6: chủ đề gia đình – family (1)',
                        'objective' => 'Nhớ và dùng đúng các từ vựng cơ bản về gia đình.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Học từ vựng theo nhóm: bố mẹ (father, mother), anh chị em (brother, sister), ông bà (grandfather, grandmother), chú bác cô dì (uncle, aunt). Đặt câu đơn giản với mỗi từ mới, ví dụ: She is my sister.'],
                    2 => ['title' => 'Từ vựng lớp 6: chủ đề trường học – school (2)',
                        'objective' => 'Nhớ và dùng đúng các từ vựng cơ bản về trường học, đồ dùng học tập, môn học.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Học từ vựng theo nhóm: con người (teacher, student), nơi chốn (classroom, library), đồ dùng (pen, book, ruler), môn học (math, English, music).'],
                ],
                7 => [
                    1 => ['title' => 'Từ vựng lớp 7: hoạt động hằng ngày – daily life (1)',
                        'objective' => 'Nhớ và dùng đúng các cụm từ chỉ hoạt động hằng ngày và thời gian.',
                        'difficulty' => 'de', 'duration' => 12,
                        'instructions' => 'Học cụm động từ theo trình tự một ngày: get up (thức dậy), have breakfast (ăn sáng), go to school (đi học), do homework (làm bài tập), go to bed (đi ngủ). Chú ý các cụm thời gian: in the morning, in the afternoon, in the evening.'],
                    2 => ['title' => 'Từ vựng lớp 7: thời tiết và thiên nhiên – weather (2)',
                        'objective' => 'Nhớ và dùng đúng từ vựng thời tiết, mùa và cảnh quan thiên nhiên.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Phân biệt danh từ và tính từ thời tiết: rain (danh từ) – rainy (tính từ), sun – sunny, cloud – cloudy, wind – windy. Nhớ 4 mùa: spring, summer, autumn, winter và các cảnh quan: mountain, river, forest, beach.'],
                ],
            ],
            'en-ngu-phap-co-ban' => [
                6 => [
                    1 => ['title' => 'Ngữ pháp lớp 6: động từ to be (1)',
                        'objective' => 'Dùng đúng am/is/are trong câu khẳng định, phủ định, nghi vấn.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'I đi với am, he/she/it (ngôi thứ ba số ít) đi với is, you/we/they đi với are. Câu phủ định thêm not sau to be (is not = is not, are not = are not). Câu hỏi đảo to be lên trước chủ ngữ: Are you ready?'],
                    2 => ['title' => 'Ngữ pháp lớp 6: thì hiện tại đơn (2)',
                        'objective' => 'Chia đúng động từ thì hiện tại đơn; dùng thì này diễn tả thói quen.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Thì hiện tại đơn diễn tả thói quen, sự thật hiển nhiên. Với chủ ngữ ngôi thứ ba số ít (he, she, it), động từ thêm s/es: play → plays, go → goes, watch → watches, study → studies. Dấu hiệu: every day, usually, sometimes.'],
                ],
                7 => [
                    1 => ['title' => 'Ngữ pháp lớp 7: câu phủ định, nghi vấn hiện tại đơn (1)',
                        'objective' => 'Dùng đúng do/does, don’t/doesn’t trong câu phủ định và nghi vấn.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Câu phủ định: chủ ngữ + do/does + not + động từ nguyên mẫu (She does not like tea). Câu hỏi: Do/Does + chủ ngữ + động từ nguyên mẫu? (Does she swim?). Chú ý: sau does/doesn’t, động từ luôn ở dạng nguyên mẫu, không thêm s.'],
                    2 => ['title' => 'Ngữ pháp lớp 7: thì hiện tại tiếp diễn (2)',
                        'objective' => 'Chia đúng thì hiện tại tiếp diễn; phân biệt với thì hiện tại đơn.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Hiện tại tiếp diễn: am/is/are + động từ thêm -ing, diễn tả hành động đang xảy ra lúc nói. Dấu hiệu: now, at the moment, Look!, Listen!. Chú ý chính tả: swim → swimming, write → writing, run → running.'],
                ],
            ],
            'en-tu-vung-lop-7' => [
                7 => [
                    1 => ['title' => 'Từ vựng lớp 7: sức khỏe – health (1)',
                        'objective' => 'Nhớ và dùng đúng từ vựng về bệnh thường gặp, bộ phận cơ thể và lời khuyên sức khỏe.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Học từ vựng theo nhóm: các cơn đau (headache, stomachache, toothache), người và nơi chữa bệnh (doctor, nurse, hospital), lời khuyên (should + động từ: You should rest).'],
                    2 => ['title' => 'Từ vựng lớp 7: du lịch – travel (2)',
                        'objective' => 'Nhớ và dùng đúng từ vựng về phương tiện, địa điểm và hoạt động du lịch.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Học từ vựng theo nhóm: giấy tờ và vé (passport, ticket), phương tiện (plane, train, bus, ship), nơi đến (hotel, beach, museum), hoạt động (visit, take photos, buy souvenirs).'],
                ],
                8 => [
                    1 => ['title' => 'Từ vựng lớp 8: môi trường – environment (1)',
                        'objective' => 'Nhớ và dùng đúng từ vựng về ô nhiễm, bảo vệ môi trường và các hành động xanh.',
                        'difficulty' => 'trung_binh', 'duration' => 15,
                        'instructions' => 'Phân biệt từ loại: pollution (danh từ) – polluted (tính từ) – pollute (động từ). Học các hành động bảo vệ môi trường: plant trees, save water, recycle, reduce plastic. Cấu trúc khuyên nhủ: should/should not + động từ.'],
                    2 => ['title' => 'Từ vựng lớp 8: cộng đồng – community (2)',
                        'objective' => 'Nhớ và dùng đúng từ vựng về hoạt động tình nguyện, từ thiện và cộng đồng.',
                        'difficulty' => 'trung_binh', 'duration' => 15,
                        'instructions' => 'Học từ vựng theo nhóm: con người (volunteer, donor), hành động (donate, help, raise money), tổ chức và khái niệm (charity, community). Đặt câu kể về hoạt động tình nguyện bằng thì quá khứ đơn.'],
                ],
            ],
        ];

        return $plan[$topicSlug][$grade][$num];
    }

    // ================= TOÁN =================

    // ---------- Toán: Số tự nhiên ----------

    private function seedToanSoTuNhien61(): void
    {
        $L = 'toan-so-tu-nhien-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kết quả của phép tính 125 + 75 là bao nhiêu?',
                ['150', '200', '210', '190'], 1,
                'Đặt tính theo hàng: 125 + 75 = 200.');
            $this->quiz($L, 'Kết quả của phép tính 1 000 − 356 là bao nhiêu?',
                ['644', '544', '654', '646'], 0,
                '1 000 − 356 = 644. Khi trừ có nhớ, cần mượn 1 ở hàng cao hơn.');
            $this->quiz($L, 'Tính nhanh: 23 + 47 + 77 = ?',
                ['147', '137', '157', '140'], 0,
                'Nhóm 23 + 77 = 100 trước, rồi cộng 47 được 147.');
            $this->quiz($L, 'Số liền sau của 9 999 là số nào?',
                ['9 998', '10 000', '10 001', '9 990'], 1,
                'Số liền sau của một số tự nhiên bằng số đó cộng 1: 9 999 + 1 = 10 000.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép tính với kết quả đúng của nó.',
                [['125 + 75', '200'], ['300 − 120', '180'],
                 ['45 + 55', '100'], ['500 − 250', '250']],
                'Cộng trừ các số tròn chục, tròn trăm trước rồi tính tiếp.');
            $this->matching($L, 'Nối mỗi tính chất với ví dụ minh hoạ.',
                [['Tính chất giao hoán', '15 + 28 = 28 + 15'],
                 ['Tính chất kết hợp', '(12 + 38) + 62 = 12 + (38 + 62)']],
                'Giao hoán: đổi chỗ các số hạng, tổng không đổi. Kết hợp: nhóm các số hạng theo cách khác, tổng không đổi.');
            $this->matching($L, 'Nối mỗi số với số liền sau của nó.',
                [['99', '100'], ['199', '200'], ['999', '1 000']],
                'Số liền sau = số đó cộng thêm 1.');
            $this->matching($L, 'Nối mỗi phép trừ với hiệu của nó.',
                [['1 000 − 1', '999'], ['500 − 1', '499'], ['100 − 1', '99']],
                'Trừ đi 1 ta được số liền trước.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi số vào nhóm SỐ CHẴN hoặc SỐ LẺ.',
                [['124', 'Số chẵn'], ['238', 'Số chẵn'],
                 ['137', 'Số lẻ'], ['355', 'Số lẻ']],
                'Số chẵn có chữ số tận cùng là 0, 2, 4, 6, 8; số lẻ tận cùng là 1, 3, 5, 7, 9.');
            $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm KẾT QUẢ LỚN HƠN 100 hoặc KHÔNG VƯỢT QUÁ 100.',
                [['65 + 50', 'Lớn hơn 100'], ['99 + 2', 'Lớn hơn 100'],
                 ['40 + 35', 'Không vượt quá 100'], ['100 − 50', 'Không vượt quá 100']],
                '65 + 50 = 115 > 100; 99 + 2 = 101 > 100; 40 + 35 = 75; 100 − 50 = 50.');
            $this->sortQ($L, 'Kéo mỗi số vào nhóm SỐ LIỀN TRƯỚC hoặc SỐ LIỀN SAU của 500.',
                [['499', 'Số liền trước'], ['498', 'Số liền trước'],
                 ['501', 'Số liền sau'], ['502', 'Số liền sau']],
                'Số liền trước của 500 là 499, số liền sau là 501.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '125 + 75 = ___.', [[0, '200']],
                'Đặt tính cộng theo hàng: 5 + 5 = 10 viết 0 nhớ 1, tiếp tục được 200.');
            $this->fill($L, 'Số liền sau của 999 là ___.', [[0, '1000']],
                '999 + 1 = 1 000.');
            $this->fill($L, 'Khi cộng hai số, ta có thể đổi chỗ các số hạng mà tổng không đổi. Đó là tính chất giao ___.', [[0, 'hoán']],
                'Tính chất giao hoán của phép cộng: a + b = b + a.');
            $this->fill($L, '1 000 − ___ = 750.', [[0, '250']],
                'Số trừ = số bị trừ − hiệu = 1 000 − 750 = 250.');
        }
    }

    private function seedToanSoTuNhien62(): void
    {
        $L = 'toan-so-tu-nhien-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kết quả của phép tính 25 × 4 là bao nhiêu?',
                ['75', '100', '125', '90'], 1,
                '25 × 4 = 100. Nhớ: 25 × 4 = 100 rất hay dùng để tính nhanh.');
            $this->quiz($L, 'Kết quả của phép tính 100 : 4 là bao nhiêu?',
                ['20', '25', '40', '30'], 1,
                '100 : 4 = 25.');
            $this->quiz($L, 'Phép chia 47 cho 5 cho kết quả nào?',
                ['9 dư 2', '9 dư 4', '8 dư 7', '9 dư 1'], 0,
                '47 = 9 × 5 + 2, vậy thương là 9 và số dư là 2.');
            $this->quiz($L, 'Giá trị của biểu thức 12 + 3 × 4 là bao nhiêu?',
                ['60', '24', '15', '20'], 1,
                'Thực hiện phép nhân trước: 3 × 4 = 12, rồi cộng: 12 + 12 = 24.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép tính với kết quả đúng của nó.',
                [['12 × 5', '60'], ['96 : 8', '12'],
                 ['7 × 9', '63'], ['81 : 9', '9']],
                'Học thuộc bảng nhân giúp tính nhanh các phép tính này.');
            $this->matching($L, 'Nối mỗi phép chia với thương và số dư của nó.',
                [['17 : 5', '3 dư 2'], ['23 : 4', '5 dư 3'],
                 ['30 : 7', '4 dư 2'], ['50 : 6', '8 dư 2']],
                'Số bị chia = thương × số chia + số dư, số dư luôn nhỏ hơn số chia.');
            $this->matching($L, 'Nối mỗi từ với ý nghĩa của nó trong phép chia.',
                [['Thương', 'Kết quả của phép chia'],
                 ['Số dư', 'Phần còn lại sau khi chia hết'],
                 ['Số bị chia', 'Số đem chia cho số khác']],
                'Trong phép chia 47 : 5 = 9 dư 2: 47 là số bị chia, 9 là thương, 2 là số dư.');
            $this->matching($L, 'Nối mỗi biểu thức với phép tính cần làm trước.',
                [['(5 + 3) × 2', 'Tính trong ngoặc trước'],
                 ['5 + 3 × 2', 'Tính phép nhân trước'],
                 ['(20 − 8) : 4', 'Tính trong ngoặc trước']],
                'Ngoặc luôn được ưu tiên cao nhất, sau đó mới đến nhân – chia.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi số vào nhóm SỐ CHẴN hoặc SỐ LẺ.',
                [['12', 'Số chẵn'], ['34', 'Số chẵn'],
                 ['15', 'Số lẻ'], ['27', 'Số lẻ']],
                'Số chẵn chia hết cho 2, số lẻ chia cho 2 dư 1.');
            $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm tính đúng thứ tự ưu tiên.',
                [['(10 + 5) × 2: tính cộng trước', 'Đúng thứ tự'],
                 ['10 + 5 × 2: tính nhân trước', 'Đúng thứ tự'],
                 ['(20 − 8) : 4: tính trừ trước', 'Đúng thứ tự'],
                 ['20 − 8 : 4: tính cộng trước', 'Sai thứ tự']],
                'Có ngoặc thì tính trong ngoặc trước; không có ngoặc thì nhân – chia trước, cộng – trừ sau.');
            $this->sortQ($L, 'Kéo mỗi số vào nhóm CHIA HẾT CHO 5 hoặc KHÔNG CHIA HẾT CHO 5.',
                [['25', 'Chia hết cho 5'], ['100', 'Chia hết cho 5'],
                 ['37', 'Không chia hết cho 5'], ['42', 'Không chia hết cho 5']],
                'Số chia hết cho 5 có chữ số tận cùng là 0 hoặc 5.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '7 × 8 = ___.', [[0, '56']],
                'Bảng cửu chương: bảy nhân tám bằng năm mươi sáu.');
            $this->fill($L, 'Trong phép chia 23 cho 5, số dư là ___.', [[0, '3']],
                '23 = 4 × 5 + 3, vậy số dư là 3.');
            $this->fill($L, 'Tính 20 − 4 × 3: ta thực hiện phép ___ trước.', [[0, 'nhân']],
                'Không có ngoặc nên nhân – chia làm trước, cộng – trừ làm sau.');
            $this->fill($L, '100 : ___ = 25.', [[0, '4']],
                'Số chia = số bị chia : thương = 100 : 25 = 4.');
        }
    }

    private function seedToanSoTuNhien71(): void
    {
        $L = 'toan-so-tu-nhien-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Giá trị của 2³ là bao nhiêu?',
                ['6', '8', '9', '12'], 1,
                '2³ = 2 × 2 × 2 = 8. Chú ý 2³ khác 2 × 3 = 6.');
            $this->quiz($L, 'Giá trị của 5² + 3² là bao nhiêu?',
                ['34', '16', '64', '25'], 0,
                '5² = 25, 3² = 9, tổng là 34.');
            $this->quiz($L, 'Giá trị của 10³ là bao nhiêu?',
                ['30', '300', '1 000', '10 000'], 2,
                '10³ = 10 × 10 × 10 = 1 000.');
            $this->quiz($L, 'Kết quả của a⁵ : a² (với a ≠ 0) là gì?',
                ['a³', 'a⁷', 'a²', 'a¹⁰'], 0,
                'Chia hai lũy thừa cùng cơ số: trừ hai số mũ, a⁵ : a² = a^(5−2) = a³.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lũy thừa với giá trị của nó.',
                [['2⁴', '16'], ['3³', '27'], ['4²', '16'], ['5²', '25']],
                '2⁴ = 16, 3³ = 27, 4² = 16, 5² = 25.');
            $this->matching($L, 'Nối mỗi công thức với cách đọc của nó.',
                [['a^m × a^n', 'a mũ (m + n)'],
                 ['a^m : a^n', 'a mũ (m − n)'],
                 ['(a^m)^n', 'a mũ (m × n)']],
                'Nhân cùng cơ số: cộng số mũ. Chia cùng cơ số: trừ số mũ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi lũy thừa đặc biệt với giá trị của nó.',
                [['2⁰', '1'], ['1⁵', '1'], ['0³', '0'], ['3⁰', '1']],
                'Mọi số khác 0 mũ 0 đều bằng 1; 1 mũ mấy cũng bằng 1; 0 mũ số dương bằng 0.');
            $this->matching($L, 'Nối mỗi thành phần của lũy thừa với giá trị của nó.',
                [['Cơ số của 7⁴', '7'], ['Số mũ của 7⁴', '4'],
                 ['Cơ số của 2¹⁰', '2'], ['Số mũ của 2¹⁰', '10']],
                'Trong a^n, a là cơ số, n là số mũ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi lũy thừa vào nhóm LỚN HƠN 20 hoặc KHÔNG LỚN HƠN 20.',
                [['2⁴ = 16', 'Không lớn hơn 20'], ['3³ = 27', 'Lớn hơn 20'],
                 ['5² = 25', 'Lớn hơn 20'], ['2³ = 8', 'Không lớn hơn 20']],
                'Tính giá trị rồi so sánh với 20.');
            $this->sortQ($L, 'Kéo mỗi đẳng thức vào nhóm ĐÚNG hoặc SAI.',
                [['2³ = 8', 'Đúng'], ['3² = 6', 'Sai'],
                 ['5⁰ = 1', 'Đúng'], ['1³ = 3', 'Sai']],
                '3² = 9 (không phải 6), 1³ = 1 (không phải 3).');
            $this->sortQ($L, 'Kéo mỗi số vào nhóm SỐ CHÍNH PHƯƠNG hoặc KHÔNG PHẢI.',
                [['9', 'Số chính phương'], ['16', 'Số chính phương'],
                 ['25', 'Số chính phương'], ['7', 'Không phải số chính phương']],
                'Số chính phương là bình phương của một số tự nhiên: 9 = 3², 16 = 4², 25 = 5².', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '2 mũ 5 bằng ___.', [[0, '32']],
                '2⁵ = 2 × 2 × 2 × 2 × 2 = 32.');
            $this->fill($L, 'a^m × a^n = a^___ (viết tổng hai số mũ).', [[0, 'm+n']],
                'Nhân hai lũy thừa cùng cơ số: giữ nguyên cơ số, cộng hai số mũ.', 'trung_binh');
            $this->fill($L, 'Mọi số khác 0 mũ 0 đều bằng ___.', [[0, '1']],
                'Quy ước: a⁰ = 1 với mọi a ≠ 0.');
            $this->fill($L, '3³ − 2³ = ___.', [[0, '19']],
                '3³ = 27, 2³ = 8, hiệu là 19.');
        }
    }

    private function seedToanSoTuNhien72(): void
    {
        $L = 'toan-so-tu-nhien-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Giá trị của 2³ × 2² là bao nhiêu?',
                ['32', '64', '12', '16'], 0,
                '2³ × 2² = 2^(3+2) = 2⁵ = 32.', 'trung_binh');
            $this->quiz($L, 'Giá trị của 100 − (2 × 3²) là bao nhiêu?',
                ['82', '96', '72', '90'], 0,
                'Tính lũy thừa trước: 3² = 9; rồi nhân: 2 × 9 = 18; cuối cùng 100 − 18 = 82.', 'trung_binh');
            $this->quiz($L, 'Tính nhanh: 12 × 25 = ?',
                ['300', '250', '320', '275'], 0,
                '12 × 25 = 3 × 4 × 25 = 3 × 100 = 300.', 'trung_binh');
            $this->quiz($L, 'Giá trị của (2 × 5)³ là bao nhiêu?',
                ['1 000', '100', '30', '60'], 0,
                '(2 × 5)³ = 10³ = 1 000.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi biểu thức với giá trị của nó.',
                [['3² + 4²', '25'], ['2 × 5³', '250'],
                 ['(10 − 4)²', '36'], ['5² − 3²', '16']],
                '3² + 4² = 9 + 16 = 25; 2 × 125 = 250; 6² = 36; 25 − 9 = 16.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phép tính nhanh với cách nhóm hợp lí.',
                [['15 × 99', '15 × (100 − 1) = 1 485'],
                 ['25 × 16', '25 × 4 × 4 = 400'],
                 ['12 × 101', '12 × 100 + 12 = 1 212']],
                'Dùng tính chất phân phối với các số gần tròn trăm để tính nhanh.', 'trung_binh');
            $this->matching($L, 'Nối mỗi số với bình phương của nó.',
                [['12', '144'], ['15', '225'], ['20', '400']],
                '12² = 144, 15² = 225, 20² = 400. Nên nhớ các bình phương thông dụng.');
            $this->matching($L, 'Nối mỗi lũy thừa với giá trị của nó.',
                [['2¹⁰', '1 024'], ['3⁴', '81'], ['5³', '125'], ['4³', '64']],
                '2¹⁰ = 1 024; 3⁴ = 81; 5³ = 125; 4³ = 64.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm CÓ GIÁ TRỊ BẰNG 100 hoặc KHÁC 100.',
                [['10²', 'Bằng 100'], ['4 × 25', 'Bằng 100'],
                 ['2⁶ = 64', 'Khác 100'], ['5³ = 125', 'Khác 100']],
                '10² = 100; 4 × 25 = 100; 2⁶ = 64; 5³ = 125.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đẳng thức vào nhóm ĐÚNG hoặc SAI.',
                [['(3 + 2)² = 25', 'Đúng'], ['2⁴ = 8', 'Sai'],
                 ['10² = 20', 'Sai'], ['3² + 4² = 5²', 'Đúng']],
                '2⁴ = 16 (không phải 8); 10² = 100 (không phải 20); 9 + 16 = 25 = 5².', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi số vào nhóm SỐ CHÍNH PHƯƠNG hoặc KHÔNG PHẢI.',
                [['144', 'Số chính phương'], ['100', 'Số chính phương'],
                 ['50', 'Không phải số chính phương'], ['90', 'Không phải số chính phương']],
                '144 = 12², 100 = 10² là số chính phương; 50 và 90 không phải.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '12 × 101 = 12 × 100 + 12 = ___.', [[0, '1212']],
                'Dùng tính chất phân phối: 12 × 101 = 1 200 + 12 = 1 212.', 'trung_binh');
            $this->fill($L, '2⁴ × 2 = 2^___.', [[0, '5']],
                '2⁴ × 2¹ = 2^(4+1) = 2⁵.', 'trung_binh');
            $this->fill($L, 'Tính nhanh: 99 + 98 + 101 + 102 = (99 + 101) + (98 + 102) = ___.', [[0, '400']],
                'Nhóm các cặp bù nhau thành số tròn trăm: 200 + 200 = 400.', 'trung_binh');
            $this->fill($L, 'Số dư của 7² khi chia cho 5 là ___.', [[0, '4']],
                '7² = 49; 49 : 5 = 9 dư 4.', 'trung_binh');
        }
    }

    // ---------- Toán: Phân số ----------

    private function seedToanPhanSo61(): void
    {
        $L = 'toan-phan-so-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kết quả của 1/4 + 2/4 là bao nhiêu?',
                ['3/8', '3/4', '1/2', '4/8'], 1,
                'Cùng mẫu số 4: cộng hai tử số 1 + 2 = 3, giữ nguyên mẫu số, được 3/4.');
            $this->quiz($L, 'Kết quả của 5/6 − 1/6 (rút gọn) là bao nhiêu?',
                ['2/3', '4/6', '1/3', '5/6'], 0,
                '5/6 − 1/6 = 4/6, rút gọn chia cả tử và mẫu cho 2 được 2/3.');
            $this->quiz($L, 'Kết quả của 2/5 + 1/5 là bao nhiêu?',
                ['3/10', '3/5', '2/5', '1/5'], 1,
                'Cùng mẫu số 5: 2 + 1 = 3, giữ nguyên mẫu số, được 3/5.');
            $this->quiz($L, 'Phân số nào bằng 1/2?',
                ['1/3', '2/4', '3/5', '2/3'], 1,
                'Nhân cả tử và mẫu của 1/2 với 2 được 2/4, hai phân số bằng nhau.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép tính với kết quả đã rút gọn.',
                [['1/3 + 1/3', '2/3'], ['3/4 − 1/4', '1/2'],
                 ['2/7 + 3/7', '5/7'], ['5/8 − 3/8', '1/4']],
                'Cùng mẫu số thì cộng/trừ tử số, giữ nguyên mẫu số, rồi rút gọn nếu được.');
            $this->matching($L, 'Nối mỗi phân số với phân số bằng nó.',
                [['1/2', '2/4'], ['1/3', '2/6'], ['3/4', '6/8']],
                'Nhân (hoặc chia) cả tử và mẫu cho cùng một số khác 0 thì được phân số bằng nó.');
            $this->matching($L, 'Nối mỗi phân số với cách đọc đúng.',
                [['3/5', 'ba phần năm'], ['7/10', 'bảy phần mười'], ['2/3', 'hai phần ba']],
                'Đọc tử số trước, rồi đọc "phần", rồi đọc mẫu số.');
            $this->matching($L, 'Nối mỗi thành phần của phân số với giá trị của nó.',
                [['Tử số của 5/8', '5'], ['Mẫu số của 5/8', '8'],
                 ['Tử số của 3/7', '3'], ['Mẫu số của 3/7', '7']],
                'Trong phân số a/b, a là tử số, b là mẫu số (b khác 0).');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm LỚN HƠN 1/2 hoặc NHỎ HƠN 1/2.',
                [['3/4', 'Lớn hơn 1/2'], ['2/3', 'Lớn hơn 1/2'],
                 ['1/4', 'Nhỏ hơn 1/2'], ['1/3', 'Nhỏ hơn 1/2']],
                '3/4 = 0,75 > 0,5; 2/3 ≈ 0,67 > 0,5; 1/4 = 0,25 < 0,5; 1/3 ≈ 0,33 < 0,5.');
            $this->sortQ($L, 'Kéo mỗi cặp phân số vào nhóm CÙNG MẪU SỐ hoặc KHÁC MẪU SỐ.',
                [['1/5 và 3/5', 'Cùng mẫu số'], ['2/7 và 5/7', 'Cùng mẫu số'],
                 ['2/7 và 4/9', 'Khác mẫu số'], ['1/3 và 1/4', 'Khác mẫu số']],
                'Cùng mẫu số thì cộng trừ trực tiếp được; khác mẫu số phải quy đồng trước.');
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm PHÂN SỐ TỐI GIẢN hoặc CHƯA TỐI GIẢN.',
                [['3/4', 'Tối giản'], ['5/7', 'Tối giản'],
                 ['2/4', 'Chưa tối giản'], ['6/9', 'Chưa tối giản']],
                '2/4 rút gọn được thành 1/2; 6/9 rút gọn được thành 2/3.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '1/5 + 2/5 = ___.', [[0, '3/5']],
                'Cùng mẫu số 5: cộng tử số 1 + 2 = 3, giữ nguyên mẫu số.');
            $this->fill($L, 'Muốn cộng hai phân số cùng mẫu số, ta cộng hai ___ số và giữ nguyên mẫu số.', [[0, 'tử']],
                'Quy tắc: cộng tử số, giữ nguyên mẫu số.');
            $this->fill($L, '7/9 − 4/9 = ___.', [[0, '3/9']],
                'Cùng mẫu số 9: 7 − 4 = 3, được 3/9 (có thể rút gọn thành 1/3).');
            $this->fill($L, 'Rút gọn phân số 4/6 ta được ___.', [[0, '2/3']],
                'Chia cả tử và mẫu cho 2 được 2/3.');
        }
    }

    private function seedToanPhanSo62(): void
    {
        $L = 'toan-phan-so-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Muốn quy đồng mẫu số của 1/2 và 1/3, mẫu số chung nhỏ nhất là bao nhiêu?',
                ['6', '5', '12', '9'], 0,
                'Bội chung nhỏ nhất của 2 và 3 là 6.');
            $this->quiz($L, 'So sánh 3/5 và 2/3, kết luận nào đúng?',
                ['3/5 > 2/3', '3/5 < 2/3', '3/5 = 2/3', 'Không so sánh được'], 1,
                'Quy đồng: 3/5 = 9/15, 2/3 = 10/15. Vì 9/15 < 10/15 nên 3/5 < 2/3.');
            $this->quiz($L, 'Rút gọn phân số 8/12 ta được phân số nào?',
                ['2/3', '4/6', '1/2', '8/3'], 0,
                'Chia cả tử và mẫu cho 4 được 2/3.');
            $this->quiz($L, 'Trong các phân số 3/7, 5/7, 2/7, 6/7, phân số nào lớn nhất?',
                ['3/7', '5/7', '2/7', '6/7'], 3,
                'Cùng mẫu số dương: phân số nào có tử số lớn nhất thì lớn nhất, đó là 6/7.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phân số với dạng rút gọn của nó.',
                [['6/9', '2/3'], ['10/15', '2/3'], ['4/10', '2/5'], ['9/12', '3/4']],
                'Chia cả tử và mẫu cho ước chung lớn nhất để được phân số tối giản.');
            $this->matching($L, 'Nối mỗi phép so sánh với dấu đúng.',
                [['1/2 và 1/3', '>'], ['2/5 và 3/5', '<'], ['4/7 và 4/7', '=']],
                '1/2 = 0,5 > 1/3 ≈ 0,33; cùng mẫu 5 thì 2 < 3.');
            $this->matching($L, 'Nối mỗi cặp phân số với mẫu số chung nhỏ nhất.',
                [['1/4 và 1/6', '12'], ['1/2 và 2/5', '10'], ['1/3 và 1/9', '9']],
                'Mẫu số chung nhỏ nhất là bội chung nhỏ nhất của các mẫu số.');
            $this->matching($L, 'Nối mỗi phân số với vị trí của nó so với số 1.',
                [['5/4', 'Lớn hơn 1'], ['3/4', 'Nhỏ hơn 1'], ['7/7', 'Bằng 1']],
                'Tử số lớn hơn mẫu số thì phân số lớn hơn 1; tử bằng mẫu thì bằng 1.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm LỚN HƠN 1 hoặc NHỎ HƠN 1.',
                [['5/4', 'Lớn hơn 1'], ['7/5', 'Lớn hơn 1'],
                 ['3/4', 'Nhỏ hơn 1'], ['2/5', 'Nhỏ hơn 1']],
                'So sánh tử số với mẫu số: tử lớn hơn mẫu thì phân số lớn hơn 1.');
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm TỐI GIẢN hoặc CHƯA TỐI GIẢN.',
                [['7/11', 'Tối giản'], ['4/9', 'Tối giản'],
                 ['8/10', 'Chưa tối giản'], ['15/25', 'Chưa tối giản']],
                '8/10 = 4/5; 15/25 = 3/5 sau khi rút gọn.');
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm PHÂN SỐ DƯƠNG hoặc PHÂN SỐ ÂM.',
                [['3/5', 'Phân số dương'], ['7/8', 'Phân số dương'],
                 ['−2/3', 'Phân số âm'], ['−5/7', 'Phân số âm']],
                'Phân số mang dấu trừ là phân số âm, nhỏ hơn 0.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mẫu số chung nhỏ nhất của 1/4 và 1/6 là ___.', [[0, '12']],
                'Bội chung nhỏ nhất của 4 và 6 là 12.');
            $this->fill($L, 'So sánh: 5/8 ___ 7/8 (điền dấu <, > hoặc =).', [[0, '<']],
                'Cùng mẫu số 8, tử số 5 < 7 nên 5/8 < 7/8.');
            $this->fill($L, 'Rút gọn 15/20 = ___.', [[0, '3/4']],
                'Chia cả tử và mẫu cho 5 được 3/4.');
            $this->fill($L, 'Quy đồng mẫu số của 2/3 và 3/4: hai phân số mới là 8/12 và ___.', [[0, '9/12']],
                'Mẫu số chung 12: 2/3 = 8/12; 3/4 = 9/12.');
        }
    }

    private function seedToanPhanSo71(): void
    {
        $L = 'toan-phan-so-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kết quả của 2/3 × 3/4 là bao nhiêu?',
                ['5/12', '1/2', '5/7', '6/7'], 1,
                'Nhân tử với tử, mẫu với mẫu: (2 × 3)/(3 × 4) = 6/12 = 1/2.', 'trung_binh');
            $this->quiz($L, 'Kết quả của 4/5 : 2/5 là bao nhiêu?',
                ['2', '8/25', '2/5', '4/25'], 0,
                'Nhân với phân số nghịch đảo: 4/5 × 5/2 = 20/10 = 2.', 'trung_binh');
            $this->quiz($L, 'Hỗn số 1 1/2 bằng phân số nào?',
                ['3/2', '1/2', '2/3', '5/2'], 0,
                '1 1/2 = (1 × 2 + 1)/2 = 3/2.', 'trung_binh');
            $this->quiz($L, 'Kết quả của 3/7 × 0 là bao nhiêu?',
                ['3/7', '0', '1', '7/3'], 1,
                'Mọi số nhân với 0 đều bằng 0.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép tính với kết quả của nó.',
                [['2/5 × 5/8', '1/4'], ['3/4 : 3', '1/4'],
                 ['7/9 × 3/7', '1/3'], ['5/6 : 5', '1/6']],
                'Nhân tử với tử, mẫu với mẫu rồi rút gọn; chia cho số tự nhiên là nhân mẫu với số đó.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hỗn số với phân số bằng nó.',
                [['1 2/3', '5/3'], ['2 1/4', '9/4'], ['3 1/2', '7/2']],
                'Hỗn số a b/c = (a × c + b)/c.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phân số với phân số nghịch đảo của nó.',
                [['2/3', '3/2'], ['5/7', '7/5'], ['4', '1/4']],
                'Phân số nghịch đảo của a/b là b/a; tích của chúng bằng 1.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phép chia với phép nhân tương đương.',
                [['2/3 : 4/5', '2/3 × 5/4'], ['7/8 : 7', '7/8 × 1/7']],
                'Chia cho một phân số bằng nhân với phân số nghịch đảo của nó.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm LỚN HƠN 1/2 hoặc NHỎ HƠN 1/2.',
                [['5/8', 'Lớn hơn 1/2'], ['4/7', 'Lớn hơn 1/2'],
                 ['3/8', 'Nhỏ hơn 1/2'], ['2/5', 'Nhỏ hơn 1/2']],
                '5/8 = 0,625 > 0,5; 4/7 ≈ 0,57 > 0,5; 3/8 = 0,375 < 0,5; 2/5 = 0,4 < 0,5.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm KẾT QUẢ LÀ SỐ NGUYÊN hoặc KHÔNG PHẢI SỐ NGUYÊN.',
                [['3/4 : 3/4', 'Số nguyên'], ['2/3 × 3/2', 'Số nguyên'],
                 ['1/2 + 1/3', 'Không phải số nguyên'], ['2/5 × 5/6', 'Không phải số nguyên']],
                '3/4 : 3/4 = 1; 2/3 × 3/2 = 1; 1/2 + 1/3 = 5/6; 2/5 × 5/6 = 1/3.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm PHÂN SỐ ÂM hoặc PHÂN SỐ DƯƠNG.',
                [['−3/4', 'Phân số âm'], ['−7/2', 'Phân số âm'],
                 ['5/6', 'Phân số dương'], ['9/4', 'Phân số dương']],
                'Phân số âm nhỏ hơn 0, phân số dương lớn hơn 0.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '2/7 × 3 = ___.', [[0, '6/7']],
                'Nhân tử số với 3, giữ nguyên mẫu số: 6/7.', 'trung_binh');
            $this->fill($L, 'Muốn chia một phân số cho một phân số, ta nhân phân số bị chia với phân số ___ đảo của số chia.', [[0, 'nghịch']],
                'Quy tắc: a/b : c/d = a/b × d/c.', 'trung_binh');
            $this->fill($L, 'Hỗn số 2 3/5 = ___.', [[0, '13/5']],
                '2 3/5 = (2 × 5 + 3)/5 = 13/5.', 'trung_binh');
            $this->fill($L, '5/6 : 5/3 = ___.', [[0, '1/2']],
                '5/6 × 3/5 = 15/30 = 1/2.', 'trung_binh');
        }
    }

    private function seedToanPhanSo72(): void
    {
        $L = 'toan-phan-so-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Giá trị của 1/2 + 1/3 − 1/6 là bao nhiêu?',
                ['2/3', '5/6', '1', '1/3'], 0,
                'Mẫu số chung 6: 3/6 + 2/6 − 1/6 = 4/6 = 2/3.', 'trung_binh');
            $this->quiz($L, 'Giá trị của (2/3 + 1/4) × 12 là bao nhiêu?',
                ['11', '12', '13', '14'], 0,
                'Dùng tính chất phân phối: 2/3 × 12 + 1/4 × 12 = 8 + 3 = 11.', 'trung_binh');
            $this->quiz($L, 'Một vòi nước chảy đầy bể trong 4 giờ. Trong 1 giờ, vòi chảy được mấy phần bể?',
                ['1/4', '4', '1/2', '3/4'], 0,
                'Cả bể là 1, chia đều cho 4 giờ nên mỗi giờ chảy được 1/4 bể.', 'trung_binh');
            $this->quiz($L, 'Tìm x biết x − 1/3 = 5/6. Giá trị của x là?',
                ['7/6', '1/2', '1/3', '2/3'], 0,
                'x = 5/6 + 1/3 = 5/6 + 2/6 = 7/6.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi biểu thức với giá trị của nó.',
                [['(1/2 + 1/4) × 4', '3'], ['5/6 − 1/3 + 1/2', '1'],
                 ['2/3 × (3/4 + 1/2)', '5/6'], ['(1 − 1/3) : 2/3', '1']],
                '(3/4) × 4 = 3; 5/6 − 2/6 + 3/6 = 1; 2/3 × 5/4 = 5/6; (2/3) : (2/3) = 1.', 'trung_binh');
            $this->matching($L, 'Nối mỗi bài toán với phép tính phù hợp.',
                [['Đã đi 1/3 quãng đường, đi thêm 1/4 quãng đường', '1/3 + 1/4'],
                 ['Cả quãng đường trừ phần đã đi', '1 − (1/3 + 1/4)'],
                 ['2/3 của 90 quả cam', '2/3 × 90']],
                'Xác định "cả" là 1, rồi cộng trừ các phần hoặc nhân với phân số chỉ phần.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phân số với số thập phân bằng nó.',
                [['1/4', '0,25'], ['3/4', '0,75'], ['1/5', '0,2'], ['2/5', '0,4']],
                'Chia tử cho mẫu: 1 : 4 = 0,25; 3 : 4 = 0,75.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phương trình với nghiệm của nó.',
                [['x + 2/5 = 1', 'x = 3/5'], ['x − 1/4 = 1/2', 'x = 3/4'], ['2x = 4/5', 'x = 2/5']],
                'Chuyển vế đổi dấu: x = 1 − 2/5 = 3/5; x = 1/2 + 1/4 = 3/4; x = (4/5) : 2 = 2/5.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm CÓ GIÁ TRỊ BẰNG 1 hoặc KHÁC 1.',
                [['1/2 + 1/3 + 1/6', 'Bằng 1'], ['3/4 : 3/4', 'Bằng 1'],
                 ['2/5 + 1/5', 'Khác 1'], ['1/2 × 3/4', 'Khác 1']],
                '3/6 + 2/6 + 1/6 = 1; 3/4 : 3/4 = 1; 2/5 + 1/5 = 3/5; 1/2 × 3/4 = 3/8.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm LỚN HƠN 3/4 hoặc NHỎ HƠN 3/4.',
                [['7/8', 'Lớn hơn 3/4'], ['4/5', 'Lớn hơn 3/4'],
                 ['2/3', 'Nhỏ hơn 3/4'], ['5/8', 'Nhỏ hơn 3/4']],
                '7/8 = 0,875 > 0,75; 4/5 = 0,8 > 0,75; 2/3 ≈ 0,67 < 0,75; 5/8 = 0,625 < 0,75.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi phân số vào nhóm SỐ THẬP PHÂN HỮU HẠN hoặc VÔ HẠN TUẦN HOÀN.',
                [['1/4', 'Hữu hạn'], ['3/8', 'Hữu hạn'],
                 ['1/3', 'Vô hạn tuần hoàn'], ['2/9', 'Vô hạn tuần hoàn']],
                '1/4 = 0,25; 3/8 = 0,375 là hữu hạn; 1/3 = 0,333...; 2/9 = 0,222... là vô hạn tuần hoàn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '1/2 + 1/4 + 1/8 = ___.', [[0, '7/8']],
                'Mẫu số chung 8: 4/8 + 2/8 + 1/8 = 7/8.', 'trung_binh');
            $this->fill($L, '(2/5 + 3/10) × 10 = ___.', [[0, '7']],
                'Phân phối: 2/5 × 10 + 3/10 × 10 = 4 + 3 = 7.', 'trung_binh');
            $this->fill($L, 'Tìm x: x + 3/7 = 1, vậy x = ___.', [[0, '4/7']],
                'x = 1 − 3/7 = 4/7.', 'trung_binh');
            $this->fill($L, '2/3 của 90 bằng ___.', [[0, '60']],
                '2/3 × 90 = 60.', 'trung_binh');
        }
    }

    // ---------- Toán: Biểu thức đại số ----------

    private function seedToanDaiSo71(): void
    {
        $L = 'toan-bieu-thuc-dai-so-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Biểu thức nào là biểu thức đại số?',
                ['5 + 3', 'x + 5', '7 − 2', '100'], 1,
                'Biểu thức đại số chứa chữ đại diện cho số, ví dụ x + 5.');
            $this->quiz($L, 'Giá trị của biểu thức x + 7 tại x = 3 là bao nhiêu?',
                ['10', '21', '4', '13'], 0,
                'Thay x = 3 vào: 3 + 7 = 10.');
            $this->quiz($L, 'Giá trị của 2x tại x = 5 là bao nhiêu?',
                ['7', '10', '25', '12'], 1,
                'Thay x = 5 vào: 2 × 5 = 10.');
            $this->quiz($L, 'Biểu thức diễn đạt "gấp đôi của a rồi cộng thêm 3" là gì?',
                ['2a + 3', 'a + 2 + 3', '2(a + 3)', 'a² + 3'], 0,
                '"Gấp đôi của a" là 2a, "cộng thêm 3" là + 3, được 2a + 3.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi biểu thức với giá trị của nó tại x = 4.',
                [['x + 5', '9'], ['2x − 3', '5'], ['x²', '16'], ['10 − x', '6']],
                'Thay x = 4: 4 + 5 = 9; 8 − 3 = 5; 4² = 16; 10 − 4 = 6.');
            $this->matching($L, 'Nối mỗi cụm từ với biểu thức đại số tương ứng.',
                [['Tổng của x và 8', 'x + 8'], ['Tích của 5 và y', '5y'], ['Hiệu của m và n', 'm − n']],
                'Dịch lời văn thành biểu thức: tổng → +, tích → ×, hiệu → −.');
            $this->matching($L, 'Nối mỗi đơn thức với bậc của nó.',
                [['3x²', 'Bậc 2'], ['5xy', 'Bậc 2'], ['7x', 'Bậc 1']],
                'Bậc của đơn thức là tổng số mũ của các biến: x² bậc 2, xy bậc 1 + 1 = 2.');
            $this->matching($L, 'Nối mỗi biểu thức với giá trị của nó tại a = 2, b = 3.',
                [['a + b', '5'], ['2a − b', '1'], ['ab', '6']],
                'Thay a = 2, b = 3: 2 + 3 = 5; 4 − 3 = 1; 2 × 3 = 6.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm BIỂU THỨC ĐẠI SỐ hoặc BIỂU THỨC SỐ.',
                [['x + 1', 'Biểu thức đại số'], ['3y − 2', 'Biểu thức đại số'],
                 ['5 + 7', 'Biểu thức số'], ['12 × 3', 'Biểu thức số']],
                'Biểu thức đại số chứa chữ, biểu thức số chỉ chứa số.');
            $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm BẬC 1 hoặc BẬC 2.',
                [['5x', 'Bậc 1'], ['−2y', 'Bậc 1'],
                 ['3x²', 'Bậc 2'], ['7xy', 'Bậc 2']],
                'Bậc là tổng số mũ của biến: x bậc 1, x² bậc 2, xy bậc 2.');
            $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm HỆ SỐ DƯƠNG hoặc HỆ SỐ ÂM.',
                [['4x', 'Hệ số dương'], ['2xy', 'Hệ số dương'],
                 ['−3y', 'Hệ số âm'], ['−x²', 'Hệ số âm']],
                'Hệ số là phần số đứng trước biến: 4x có hệ số 4, −x² có hệ số −1.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Giá trị của biểu thức 3x − 1 tại x = 4 là ___.', [[0, '11']],
                'Thay x = 4: 3 × 4 − 1 = 12 − 1 = 11.');
            $this->fill($L, 'Trong đơn thức 5x², hệ số là ___.', [[0, '5']],
                'Hệ số là phần số đứng trước phần biến.');
            $this->fill($L, 'Biểu thức diễn đạt "một nửa của n" là ___.', [[0, 'n/2']],
                'Một nửa của n nghĩa là n chia 2.');
            $this->fill($L, 'Giá trị của x² + 2x tại x = 3 là ___.', [[0, '15']],
                'Thay x = 3: 9 + 6 = 15.');
        }
    }

    private function seedToanDaiSo72(): void
    {
        $L = 'toan-bieu-thuc-dai-so-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cặp đơn thức nào đồng dạng với nhau?',
                ['3x² và 5x²', '2x và 3y', '4xy và 4x', 'x³ và 3x'], 0,
                'Hai đơn thức đồng dạng có cùng phần biến: 3x² và 5x² cùng phần biến x².', 'trung_binh');
            $this->quiz($L, 'Kết quả của 3x + 5x là gì?',
                ['8x', '8x²', '15x', '8'], 0,
                'Cộng hệ số, giữ nguyên phần biến: (3 + 5)x = 8x.', 'trung_binh');
            $this->quiz($L, 'Kết quả của 7xy − 2xy là gì?',
                ['5xy', '5', '9xy', '5x²y²'], 0,
                '(7 − 2)xy = 5xy.', 'trung_binh');
            $this->quiz($L, 'Thu gọn: 4x² − x² + 2x² = ?',
                ['5x²', '6x²', '5x', '7x²'], 0,
                '(4 − 1 + 2)x² = 5x².', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép tính với kết quả của nó.',
                [['2x + 3x', '5x'], ['9y − 4y', '5y'],
                 ['x² + 3x²', '4x²'], ['6ab − 2ab', '4ab']],
                'Cộng trừ hệ số, giữ nguyên phần biến.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cặp đơn thức với nhận xét đúng.',
                [['3x và 7x', 'Đồng dạng'], ['2xy và 5xy', 'Đồng dạng'],
                 ['3x² và 3x', 'Không đồng dạng'], ['4xy và 4yx', 'Đồng dạng']],
                'xy và yx là cùng phần biến nên đồng dạng; x² và x khác phần biến.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đơn thức với hệ số của nó.',
                [['5x³', '5'], ['−2xy²', '−2'], ['7x', '7']],
                'Hệ số là phần số đứng trước biến.', 'trung_binh');
            $this->matching($L, 'Nối mỗi biểu thức với giá trị của nó tại x = 2.',
                [['3x', '6'], ['x²', '4'], ['5 − x', '3'], ['2x + 1', '5']],
                'Thay x = 2: 6; 4; 3; 5.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm ĐỒNG DẠNG VỚI 3x hoặc KHÔNG ĐỒNG DẠNG.',
                [['5x', 'Đồng dạng'], ['−x', 'Đồng dạng'],
                 ['3x²', 'Không đồng dạng'], ['4y', 'Không đồng dạng']],
                'Đồng dạng với 3x nghĩa là có cùng phần biến x.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm ĐỒNG DẠNG VỚI 2xy hoặc KHÔNG ĐỒNG DẠNG.',
                [['7xy', 'Đồng dạng'], ['−3yx', 'Đồng dạng'],
                 ['2x²y', 'Không đồng dạng'], ['5xy²', 'Không đồng dạng']],
                'yx chính là xy nên đồng dạng; x²y và xy² khác phần biến.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi phép thu gọn vào nhóm ĐÚNG hoặc SAI.',
                [['2x + 3x = 5x', 'Đúng'], ['4y − y = 3y', 'Đúng'],
                 ['x + x² = 2x²', 'Sai'], ['5ab − 2ab = 3a²b²', 'Sai']],
                'x và x² không đồng dạng nên không cộng được; 5ab − 2ab = 3ab.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '5x + 2x − x = ___x.', [[0, '6']],
                '(5 + 2 − 1)x = 6x.', 'trung_binh');
            $this->fill($L, 'Hai đơn thức đồng dạng là hai đơn thức có hệ số khác 0 và có cùng phần ___.', [[0, 'biến']],
                'Định nghĩa: cùng phần biến thì đồng dạng.', 'trung_binh');
            $this->fill($L, '9xy − 4xy = ___xy.', [[0, '5']],
                '(9 − 4)xy = 5xy.', 'trung_binh');
            $this->fill($L, 'Thu gọn: 2x² + 5x² − x² = ___x².', [[0, '6']],
                '(2 + 5 − 1)x² = 6x².', 'trung_binh');
        }
    }

    private function seedToanDaiSo81(): void
    {
        $L = 'toan-bieu-thuc-dai-so-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bậc của đa thức 3x² + 2x + 1 là bao nhiêu?',
                ['1', '2', '3', '5'], 1,
                'Bậc của đa thức là bậc cao nhất của các hạng tử: x² có bậc 2.', 'trung_binh');
            $this->quiz($L, 'Thu gọn đa thức x² + 3x + 2x² − x ta được gì?',
                ['3x² + 2x', '3x² + 4x', 'x² + 2x', '4x² + 2x'], 0,
                'x² + 2x² = 3x²; 3x − x = 2x. Kết quả: 3x² + 2x.', 'trung_binh');
            $this->quiz($L, 'Đa thức nào là đa thức một biến?',
                ['x + y', '2x² − 3x + 1', 'xy + 1', 'x² + y²'], 1,
                'Đa thức một biến chỉ chứa một biến x.', 'trung_binh');
            $this->quiz($L, 'Hệ số cao nhất của đa thức 5x³ − 2x + 7 là bao nhiêu?',
                ['7', '−2', '5', '3'], 2,
                'Hệ số cao nhất là hệ số của hạng tử bậc cao nhất x³, tức là 5.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đa thức với bậc của nó.',
                [['4x³ + 2x', '3'], ['x² − 5', '2'], ['7x − 1', '1'], ['9', '0']],
                'Bậc là số mũ cao nhất của biến; đa thức hằng (số) có bậc 0.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phép tính với kết quả của nó.',
                [['(x² + 2x) + (3x² − x)', '4x² + x'],
                 ['(5x − 1) − (2x − 1)', '3x'],
                 ['(2y² + 3y) − y²', 'y² + 3y']],
                'Bỏ ngoặc rồi cộng trừ các hạng tử đồng dạng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đa thức với giá trị của nó tại x = 1.',
                [['x² + 2x + 1', '4'], ['x³ − x', '0'], ['2x − 3', '−1']],
                'Thay x = 1: 1 + 2 + 1 = 4; 1 − 1 = 0; 2 − 3 = −1.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hạng tử với bậc của nó.',
                [['3x²y', '3'], ['5xy', '2'], ['−2x', '1']],
                'Bậc của hạng tử là tổng số mũ của các biến.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi đa thức vào nhóm BẬC 2 hoặc BẬC 3.',
                [['x² + 1', 'Bậc 2'], ['5x² − 3x', 'Bậc 2'],
                 ['x³ + 2', 'Bậc 3'], ['4x³ − x', 'Bậc 3']],
                'Bậc của đa thức là bậc cao nhất trong các hạng tử.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đa thức vào nhóm MỘT BIẾN hoặc NHIỀU BIẾN.',
                [['3x² + 2', 'Một biến'], ['x⁵ − x', 'Một biến'],
                 ['xy + 1', 'Nhiều biến'], ['x² + y²', 'Nhiều biến']],
                'Một biến chỉ chứa x; nhiều biến chứa từ hai biến trở lên.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đa thức vào nhóm ĐÃ THU GỌN hoặc CHƯA THU GỌN.',
                [['x² + 2x', 'Đã thu gọn'], ['3y + 1', 'Đã thu gọn'],
                 ['x² + x + x²', 'Chưa thu gọn'], ['2y + 3y² + y', 'Chưa thu gọn']],
                'Chưa thu gọn khi còn các hạng tử đồng dạng chưa gộp.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bậc của đa thức 2x⁴ − x + 5 là ___.', [[0, '4']],
                'Hạng tử bậc cao nhất là 2x⁴ nên đa thức bậc 4.', 'trung_binh');
            $this->fill($L, 'Thu gọn: 3x² + 4x − x² + x = ___.', [[0, '2x²+5x']],
                '3x² − x² = 2x²; 4x + x = 5x.', 'trung_binh');
            $this->fill($L, 'Hệ số tự do của đa thức x² − 5x + 6 là ___.', [[0, '6']],
                'Hệ số tự do là hạng tử không chứa biến.', 'trung_binh');
            $this->fill($L, 'Giá trị của đa thức x² − 3x + 2 tại x = 2 là ___.', [[0, '0']],
                'Thay x = 2: 4 − 6 + 2 = 0.', 'trung_binh');
        }
    }

    private function seedToanDaiSo82(): void
    {
        $L = 'toan-bieu-thuc-dai-so-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Kết quả của 2x × 3x² là gì?',
                ['6x³', '5x³', '6x²', '5x²'], 0,
                'Nhân hệ số: 2 × 3 = 6; nhân biến: x × x² = x³.', 'trung_binh');
            $this->quiz($L, 'Khai triển x(x + 2) ta được gì?',
                ['x² + 2x', 'x² + 2', '2x²', 'x + 2x'], 0,
                'Nhân đơn thức với từng hạng tử: x × x + x × 2 = x² + 2x.', 'trung_binh');
            $this->quiz($L, 'Khai triển (x + 1)(x + 2) ta được gì?',
                ['x² + 3x + 2', 'x² + 2x + 1', 'x² + x + 2', '2x + 3'], 0,
                'Nhân mỗi hạng tử của ngoặc thứ nhất với từng hạng tử ngoặc thứ hai: x² + 2x + x + 2 = x² + 3x + 2.', 'trung_binh');
            $this->quiz($L, 'Kết quả của 3x × (−2y) là gì?',
                ['−6xy', '6xy', '−5xy', '−6x²y'], 0,
                'Nhân hệ số: 3 × (−2) = −6; nhân biến: x × y = xy.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép nhân đơn thức với kết quả của nó.',
                [['4x × 2x', '8x²'], ['5y × 3y²', '15y³'], ['(−2x) × 3x', '−6x²']],
                'Nhân hệ số với hệ số, nhân phần biến với phần biến.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phép khai triển với kết quả của nó.',
                [['2x(x − 3)', '2x² − 6x'], ['(x + 3)(x − 3)', 'x² − 9'], ['(x + 2)²', 'x² + 4x + 4']],
                'Áp dụng quy tắc nhân đa thức và hằng đẳng thức.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hằng đẳng thức với tên gọi của nó.',
                [['(a + b)²', 'Bình phương của một tổng'],
                 ['(a − b)(a + b)', 'Hiệu hai bình phương'],
                 ['(a − b)²', 'Bình phương của một hiệu']],
                'Học thuộc ba hằng đẳng thức cơ bản để khai triển nhanh.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phép tính với bậc của kết quả.',
                [['2x² × 3x', 'Bậc 3'], ['x(x² + 1)', 'Bậc 3'], ['(x + 1)(x + 2)', 'Bậc 2']],
                'Nhân các đa thức: cộng số mũ của biến.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phép khai triển vào nhóm ĐÚNG hoặc SAI.',
                [['2x × 3x = 6x²', 'Đúng'], ['(x + 2)(x + 3) = x² + 5x + 6', 'Đúng'],
                 ['x(x + 1) = x² + 1', 'Sai'], ['(x − 1)² = x² − 1', 'Sai']],
                'x(x + 1) = x² + x; (x − 1)² = x² − 2x + 1.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm BẬC 2 hoặc BẬC 3.',
                [['(x + 1)²', 'Bậc 2'], ['x(2x + 1)', 'Bậc 2'],
                 ['2x(x² + 1)', 'Bậc 3'], ['x²(x + 1)', 'Bậc 3']],
                'Khai triển rồi xem số mũ cao nhất của biến.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hằng đẳng thức vào nhóm BÌNH PHƯƠNG hoặc HIỆU HAI BÌNH PHƯƠNG.',
                [['(a + b)²', 'Bình phương'], ['(x − 3)²', 'Bình phương'],
                 ['a² − b²', 'Hiệu hai bình phương'], ['(x − 2)(x + 2)', 'Hiệu hai bình phương']],
                '(x − 2)(x + 2) = x² − 4 là hiệu hai bình phương.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '3x² × 2x = ___x³.', [[0, '6']],
                'Nhân hệ số: 3 × 2 = 6.', 'trung_binh');
            $this->fill($L, 'Khai triển: (x + 5)(x − 5) = x² − ___.', [[0, '25']],
                'Hiệu hai bình phương: x² − 5² = x² − 25.', 'trung_binh');
            $this->fill($L, '(2x + 1)² = 4x² + 4x + ___.', [[0, '1']],
                '(2x + 1)² = 4x² + 2 × 2x × 1 + 1 = 4x² + 4x + 1.', 'trung_binh');
            $this->fill($L, '2x(x² − 3x + 1) = 2x³ − 6x² + ___.', [[0, '2x']],
                'Nhân 2x với từng hạng tử: 2x × 1 = 2x.', 'trung_binh');
        }
    }

    // ---------- Toán: Hình học phẳng ----------

    private function seedToanHinhHoc71(): void
    {
        $L = 'toan-hinh-hoc-phang-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Góc vuông có số đo bao nhiêu độ?',
                ['90°', '180°', '45°', '360°'], 0,
                'Góc vuông là góc có số đo bằng 90°.');
            $this->quiz($L, 'Góc nào sau đây là góc nhọn?',
                ['120°', '95°', '60°', '180°'], 2,
                'Góc nhọn có số đo nhỏ hơn 90°, trong các đáp án chỉ có 60°.');
            $this->quiz($L, 'Góc bẹt có số đo bao nhiêu độ?',
                ['90°', '180°', '270°', '360°'], 1,
                'Góc bẹt có hai cạnh là hai tia đối nhau, số đo 180°.');
            $this->quiz($L, 'Hai đường thẳng vuông góc với nhau tạo thành góc bao nhiêu độ?',
                ['45°', '90°', '180°', '60°'], 1,
                'Vuông góc nghĩa là tạo thành góc vuông 90°.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại góc với số đo của nó.',
                [['Góc nhọn', 'Nhỏ hơn 90°'], ['Góc tù', 'Lớn hơn 90° và nhỏ hơn 180°'],
                 ['Góc vuông', 'Bằng 90°'], ['Góc bẹt', 'Bằng 180°']],
                'Nhớ bốn mốc: 90° là vuông, 180° là bẹt.');
            $this->matching($L, 'Nối mỗi số đo với loại góc tương ứng.',
                [['30°', 'Góc nhọn'], ['100°', 'Góc tù'], ['90°', 'Góc vuông'], ['180°', 'Góc bẹt']],
                'So sánh số đo với 90° và 180° để phân loại.');
            $this->matching($L, 'Nối mỗi dụng cụ với công dụng của nó.',
                [['Thước đo độ', 'Đo số đo góc'], ['Compa', 'Vẽ đường tròn'], ['Ê-ke', 'Vẽ góc vuông']],
                'Thước đo độ có vạch chia từ 0° đến 180°.');
            $this->matching($L, 'Nối mỗi thành phần của góc xOy với tên gọi.',
                [['Đỉnh của góc xOy', 'Điểm O'], ['Hai cạnh của góc xOy', 'Tia Ox và tia Oy']],
                'Góc xOy có đỉnh O và hai cạnh Ox, Oy.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi góc vào nhóm GÓC NHỌN hoặc GÓC TÙ.',
                [['40°', 'Góc nhọn'], ['75°', 'Góc nhọn'],
                 ['110°', 'Góc tù'], ['135°', 'Góc tù']],
                'Nhỏ hơn 90° là nhọn, lớn hơn 90° (và nhỏ hơn 180°) là tù.');
            $this->sortQ($L, 'Kéo mỗi số đo vào nhóm NHỎ HƠN 90° hoặc LỚN HƠN 90°.',
                [['25°', 'Nhỏ hơn 90°'], ['89°', 'Nhỏ hơn 90°'],
                 ['91°', 'Lớn hơn 90°'], ['150°', 'Lớn hơn 90°']],
                'So sánh trực tiếp với 90°.');
            $this->sortQ($L, 'Kéo mỗi hình vào nhóm CÓ GÓC VUÔNG hoặc KHÔNG CÓ GÓC VUÔNG.',
                [['Hình vuông', 'Có góc vuông'], ['Hình chữ nhật', 'Có góc vuông'],
                 ['Tam giác đều', 'Không có góc vuông'], ['Hình bình hành (không vuông)', 'Không có góc vuông']],
                'Hình vuông và hình chữ nhật có 4 góc vuông; tam giác đều có 3 góc 60°.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Góc có số đo 90° gọi là góc ___.', [[0, 'vuông']],
                'Góc vuông là góc có số đo bằng 90°.');
            $this->fill($L, 'Góc nhọn có số đo ___ hơn 90° (điền: lớn hay nhỏ).', [[0, 'nhỏ']],
                'Định nghĩa: góc nhọn là góc có số đo nhỏ hơn 90°.');
            $this->fill($L, 'Một vòng tròn đầy đủ có số đo ___ độ.', [[0, '360']],
                'Góc đầy (một vòng tròn) bằng 360°.');
            $this->fill($L, 'Tia nằm trong góc và chia góc thành hai góc bằng nhau gọi là tia phân ___.', [[0, 'giác']],
                'Tia phân giác chia đôi số đo của góc.');
        }
    }

    private function seedToanHinhHoc72(): void
    {
        $L = 'toan-hinh-hoc-phang-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hai góc kề bù có tổng số đo bằng bao nhiêu?',
                ['90°', '180°', '360°', '270°'], 1,
                'Kề bù nghĩa là kề nhau và bù nhau, tổng bằng 180°.', 'trung_binh');
            $this->quiz($L, 'Hai góc đối đỉnh thì như thế nào với nhau?',
                ['Bằng nhau', 'Bù nhau', 'Phụ nhau', 'Kề nhau'], 0,
                'Tính chất: hai góc đối đỉnh thì bằng nhau.', 'trung_binh');
            $this->quiz($L, 'Góc A bằng 60°, góc B kề bù với góc A. Góc B bằng bao nhiêu?',
                ['120°', '60°', '30°', '90°'], 0,
                'Góc B = 180° − 60° = 120°.', 'trung_binh');
            $this->quiz($L, 'Tia phân giác của góc 80° chia góc thành hai góc, mỗi góc bao nhiêu độ?',
                ['40°', '80°', '20°', '160°'], 0,
                'Tia phân giác chia đôi góc: 80° : 2 = 40°.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi khái niệm với định nghĩa của nó.',
                [['Góc kề bù', 'Hai góc kề nhau có tổng số đo 180°'],
                 ['Góc đối đỉnh', 'Hai góc mà mỗi cạnh của góc này là tia đối của một cạnh góc kia'],
                 ['Tia phân giác', 'Tia nằm trong góc và chia góc thành hai góc bằng nhau']],
                'Học thuộc định nghĩa để nhận biết đúng các loại góc.', 'trung_binh');
            $this->matching($L, 'Nối mỗi góc với số đo của góc kề bù với nó.',
                [['60°', '120°'], ['45°', '135°'], ['90°', '90°'], ['130°', '50°']],
                'Góc kề bù = 180° − số đo góc đã cho.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hình với số cặp góc đối đỉnh nó tạo ra.',
                [['Hai đường thẳng cắt nhau', '2 cặp góc đối đỉnh'],
                 ['Ba đường thẳng đồng quy đôi một cắt nhau', '6 cặp góc đối đỉnh']],
                'Mỗi giao điểm của hai đường thẳng tạo ra 2 cặp góc đối đỉnh.', 'trung_binh');
            $this->matching($L, 'Nối mỗi góc với số đo mỗi phần khi vẽ tia phân giác.',
                [['100°', '50°'], ['70°', '35°'], ['120°', '60°']],
                'Tia phân giác chia đôi số đo góc.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cặp góc vào nhóm KỀ BÙ hoặc KHÔNG KỀ BÙ.',
                [['60° và 120°', 'Kề bù'], ['90° và 90°', 'Kề bù'],
                 ['60° và 60°', 'Không kề bù'], ['45° và 100°', 'Không kề bù']],
                'Kề bù khi tổng hai góc bằng 180°.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tổng vào nhóm BẰNG 180° hoặc KHÁC 180°.',
                [['70° + 110°', 'Bằng 180°'], ['90° + 90°', 'Bằng 180°'],
                 ['60° + 60°', 'Khác 180°'], ['45° + 100°', 'Khác 180°']],
                '70 + 110 = 180; 90 + 90 = 180; 60 + 60 = 120; 45 + 100 = 145.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cặp góc vào nhóm ĐỐI ĐỈNH hoặc KHÔNG ĐỐI ĐỈNH.',
                [['Hai góc đối nhau qua giao điểm O', 'Đối đỉnh'],
                 ['Hai góc kề nhau chung cạnh', 'Không đối đỉnh'],
                 ['Hai góc so le trong', 'Không đối đỉnh']],
                'Đối đỉnh là hai góc đối nhau qua giao điểm của hai đường thẳng cắt nhau.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hai góc có tổng số đo bằng 180° gọi là hai góc ___ nhau.', [[0, 'bù']],
                'Bù nhau nghĩa là tổng bằng 180°.', 'trung_binh');
            $this->fill($L, 'Góc kề bù với góc 55° có số đo ___ độ.', [[0, '125']],
                '180° − 55° = 125°.', 'trung_binh');
            $this->fill($L, 'Hai đường thẳng cắt nhau tạo ra ___ cặp góc đối đỉnh.', [[0, '2']],
                'Mỗi giao điểm cho 2 cặp góc đối đỉnh bằng nhau.', 'trung_binh');
            $this->fill($L, 'Tia Oz là tia phân giác của góc xOy = 100°, góc xOz = ___ độ.', [[0, '50']],
                'Tia phân giác chia đôi: 100° : 2 = 50°.', 'trung_binh');
        }
    }

    private function seedToanHinhHoc81(): void
    {
        $L = 'toan-hinh-hoc-phang-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tổng ba góc trong của một tam giác bằng bao nhiêu?',
                ['90°', '180°', '270°', '360°'], 1,
                'Định lí: tổng ba góc trong của một tam giác bằng 180°.', 'trung_binh');
            $this->quiz($L, 'Tam giác có hai góc 60° và 70°, góc còn lại bằng bao nhiêu?',
                ['50°', '60°', '40°', '70°'], 0,
                'Góc còn lại = 180° − 60° − 70° = 50°.', 'trung_binh');
            $this->quiz($L, 'Tam giác cân có đặc điểm gì?',
                ['Hai cạnh bên bằng nhau', 'Ba cạnh bằng nhau', 'Một góc vuông', 'Hai góc tù'], 0,
                'Tam giác cân có hai cạnh bên bằng nhau và hai góc ở đáy bằng nhau.', 'trung_binh');
            $this->quiz($L, 'Mỗi góc của tam giác đều bằng bao nhiêu?',
                ['60°', '90°', '45°', '120°'], 0,
                'Ba góc bằng nhau, tổng 180° nên mỗi góc 60°.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại tam giác với đặc điểm của nó.',
                [['Tam giác cân', 'Hai cạnh bên bằng nhau, hai góc ở đáy bằng nhau'],
                 ['Tam giác đều', 'Ba cạnh bằng nhau, ba góc bằng 60°'],
                 ['Tam giác vuông', 'Có một góc bằng 90°'],
                 ['Tam giác tù', 'Có một góc lớn hơn 90°']],
                'Dựa vào cạnh và góc để phân loại tam giác.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cặp góc với số đo góc còn lại (tổng 180°).',
                [['90° và 40°', '50°'], ['70° và 60°', '50°'],
                 ['100° và 30°', '50°'], ['80° và 80°', '20°']],
                'Góc còn lại = 180° − tổng hai góc đã biết.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tam giác với tên gọi theo góc.',
                [['Có góc 120°', 'Tam giác tù'], ['Có góc 90°', 'Tam giác vuông'],
                 ['Ba góc đều nhọn', 'Tam giác nhọn']],
                'Tên gọi theo góc lớn nhất của tam giác.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cạnh trong tam giác vuông với tên gọi.',
                [['Cạnh đối diện góc vuông', 'Cạnh huyền'],
                 ['Hai cạnh tạo thành góc vuông', 'Cạnh góc vuông']],
                'Cạnh huyền là cạnh dài nhất trong tam giác vuông.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tam giác vào nhóm TAM GIÁC CÂN hoặc KHÔNG PHẢI TAM GIÁC CÂN.',
                [['Có hai cạnh bằng nhau', 'Tam giác cân'], ['Có hai góc bằng nhau', 'Tam giác cân'],
                 ['Ba cạnh đôi một khác nhau', 'Không phải tam giác cân'], ['Có một góc 90° (không thêm gì)', 'Không phải tam giác cân']],
                'Tam giác cân khi có hai cạnh bằng nhau hoặc hai góc bằng nhau.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi bộ ba góc vào nhóm TỔNG ĐÚNG 180° hoặc SAI.',
                [['60°, 60°, 60°', 'Tổng đúng 180°'], ['90°, 45°, 45°', 'Tổng đúng 180°'],
                 ['70°, 70°, 70°', 'Tổng sai'], ['100°, 50°, 40°', 'Tổng sai']],
                '70 + 70 + 70 = 210 ≠ 180; 100 + 50 + 40 = 190 ≠ 180 nên không phải ba góc tam giác.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tam giác vào nhóm TAM GIÁC VUÔNG hoặc KHÔNG VUÔNG.',
                [['Có góc 90°', 'Tam giác vuông'], ['Các góc 90°, 60°, 30°', 'Tam giác vuông'],
                 ['Có góc 120°', 'Không vuông'], ['Ba góc 60°', 'Không vuông']],
                'Tam giác vuông có đúng một góc vuông.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tổng ba góc trong của tam giác bằng ___ độ.', [[0, '180']],
                'Định lí tổng ba góc trong tam giác.', 'trung_binh');
            $this->fill($L, 'Tam giác ABC có góc A = 50°, góc B = 60° thì góc C = ___ độ.', [[0, '70']],
                'Góc C = 180° − 50° − 60° = 70°.', 'trung_binh');
            $this->fill($L, 'Trong tam giác cân, hai góc ở ___ bằng nhau.', [[0, 'đáy']],
                'Tính chất tam giác cân: hai góc ở đáy bằng nhau.', 'trung_binh');
            $this->fill($L, 'Tam giác có ba cạnh bằng nhau gọi là tam giác ___.', [[0, 'đều']],
                'Tam giác đều: ba cạnh bằng nhau, ba góc 60°.', 'trung_binh');
        }
    }

    private function seedToanHinhHoc82(): void
    {
        $L = 'toan-hinh-hoc-phang-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hai tam giác đồng dạng thì các góc tương ứng như thế nào?',
                ['Bằng nhau', 'Bù nhau', 'Phụ nhau', 'Gấp đôi nhau'], 0,
                'Định nghĩa: hai tam giác đồng dạng có các góc tương ứng bằng nhau.', 'trung_binh');
            $this->quiz($L, 'Tam giác ABC đồng dạng với tam giác DEF theo tỉ số k = 2. Biết AB = 3, độ dài DE là bao nhiêu?',
                ['6', '1,5', '5', '9'], 0,
                'DE = k × AB = 2 × 3 = 6.', 'trung_binh');
            $this->quiz($L, 'Hai tam giác đồng dạng theo tỉ số 3 thì tỉ số chu vi của chúng bằng bao nhiêu?',
                ['3', '6', '9', '1/3'], 0,
                'Tỉ số chu vi của hai tam giác đồng dạng bằng tỉ số đồng dạng.', 'trung_binh');
            $this->quiz($L, 'Trường hợp đồng dạng cạnh – cạnh – cạnh (c-c-c) nghĩa là gì?',
                ['Ba cạnh tương ứng tỉ lệ', 'Ba góc bằng nhau', 'Hai cạnh và góc xen giữa', 'Cạnh huyền – cạnh góc vuông'], 0,
                'Nếu ba cạnh của tam giác này tỉ lệ với ba cạnh của tam giác kia thì hai tam giác đồng dạng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi trường hợp đồng dạng với điều kiện của nó.',
                [['Cạnh – cạnh – cạnh', 'Ba cặp cạnh tương ứng tỉ lệ'],
                 ['Góc – góc', 'Hai cặp góc tương ứng bằng nhau'],
                 ['Cạnh – góc – cạnh', 'Hai cặp cạnh tỉ lệ và góc xen giữa bằng nhau']],
                'Ba trường hợp đồng dạng cơ bản của tam giác.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tỉ số đồng dạng với tỉ số chu vi tương ứng.',
                [['k = 2', 'Tỉ số chu vi = 2'], ['k = 3', 'Tỉ số chu vi = 3'], ['k = 1/2', 'Tỉ số chu vi = 1/2']],
                'Tỉ số chu vi bằng tỉ số đồng dạng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dữ kiện với độ dài cạnh tương ứng (k = 2).',
                [['BC = 4 thì EF = ?', '8'], ['AB = 5 thì DE = ?', '10'], ['AC = 7 thì DF = ?', '14']],
                'Cạnh tương ứng = k × cạnh đã biết = 2 × cạnh đã biết.', 'trung_binh');
            $this->matching($L, 'Nối mỗi kí hiệu với ý nghĩa của nó.',
                [['~', 'Đồng dạng'], ['≅', 'Bằng nhau'], ['k', 'Tỉ số đồng dạng']],
                'ΔABC ~ ΔDEF nghĩa là hai tam giác đồng dạng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cặp hình vào nhóm LUÔN ĐỒNG DẠNG hoặc CHƯA CHẮC ĐỒNG DẠNG.',
                [['Hai hình vuông bất kì', 'Luôn đồng dạng'],
                 ['Hai đường tròn bất kì', 'Luôn đồng dạng'],
                 ['Hai hình chữ nhật bất kì', 'Chưa chắc đồng dạng'],
                 ['Hai tam giác thường bất kì', 'Chưa chắc đồng dạng']],
                'Hình vuông, đường tròn, tam giác đều luôn đồng dạng với nhau; hình chữ nhật thì chưa chắc.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tỉ số đồng dạng vào nhóm LỚN HƠN 1 hoặc NHỎ HƠN 1.',
                [['k = 2', 'Lớn hơn 1'], ['k = 3', 'Lớn hơn 1'],
                 ['k = 1/2', 'Nhỏ hơn 1'], ['k = 2/3', 'Nhỏ hơn 1']],
                'k > 1: hình đồng dạng lớn hơn hình gốc; k < 1: nhỏ hơn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cặp yếu tố (trong ΔABC ~ ΔDEF) vào nhóm GÓC TƯƠNG ỨNG hoặc CẠNH TƯƠNG ỨNG.',
                [['Góc A và góc D', 'Góc tương ứng'], ['Góc B và góc E', 'Góc tương ứng'],
                 ['Cạnh AB và cạnh DE', 'Cạnh tương ứng'], ['Cạnh BC và cạnh EF', 'Cạnh tương ứng']],
                'Thứ tự các đỉnh trong kí hiệu đồng dạng cho biết cặp tương ứng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hai tam giác đồng dạng có các cặp cạnh tương ứng tỉ ___.', [[0, 'lệ']],
                'Định nghĩa: các cạnh tương ứng tỉ lệ với nhau.', 'trung_binh');
            $this->fill($L, 'ΔABC ~ ΔDEF theo tỉ số k = 2, nếu AB = 4 thì DE = ___.', [[0, '8']],
                'DE = 2 × AB = 8.', 'trung_binh');
            $this->fill($L, 'Tỉ số diện tích của hai tam giác đồng dạng theo tỉ số 2 bằng ___.', [[0, '4']],
                'Tỉ số diện tích bằng bình phương tỉ số đồng dạng: 2² = 4.', 'trung_binh');
            $this->fill($L, 'Kí hiệu hai tam giác đồng dạng là dấu ___.', [[0, '~']],
                'Ví dụ: ΔABC ~ ΔDEF.', 'trung_binh');
        }
    }

    // ================= TIẾNG VIỆT =================

    // ---------- Từ và câu ----------

    private function seedTvTuCau61(): void
    {
        $L = 'tv-tu-va-cau-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Từ nào sau đây là từ láy?',
                ['bàn ghế', 'xinh xắn', 'học sinh', 'sách vở'], 1,
                'Xinh xắn láy lại âm đầu x, là từ láy; các từ còn lại là từ ghép.');
            $this->quiz($L, 'Từ nào sau đây là từ ghép?',
                ['lấp lánh', 'bàn ghế', 'rì rào', 'lung linh'], 1,
                'Bàn ghế gồm hai tiếng đều có nghĩa (bàn + ghế) nên là từ ghép.');
            $this->quiz($L, 'Từ "học sinh" gồm mấy tiếng?',
                ['1 tiếng', '2 tiếng', '3 tiếng', '4 tiếng'], 1,
                '"Học sinh" gồm hai tiếng: học và sinh.');
            $this->quiz($L, 'Từ nào sau đây là từ đơn?',
                ['cây', 'trường học', 'xe đạp', 'bàn ghế'], 0,
                'Từ đơn chỉ gồm một tiếng; các từ còn lại gồm hai tiếng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với loại từ của nó.',
                [['xinh xắn', 'Từ láy'], ['bàn ghế', 'Từ ghép'],
                 ['lấp lánh', 'Từ láy'], ['sách vở', 'Từ ghép']],
                'Từ láy láy lại âm hoặc vần; từ ghép gồm các tiếng đều có nghĩa.');
            $this->matching($L, 'Nối mỗi từ với số tiếng của nó.',
                [['cây', '1 tiếng'], ['học sinh', '2 tiếng'],
                 ['xe', '1 tiếng'], ['bàn học', '2 tiếng']],
                'Đếm số tiếng (âm tiết) tạo thành từ.');
            $this->matching($L, 'Nối mỗi từ láy với tác dụng gợi tả của nó.',
                [['lung linh', 'Gợi hình ảnh ánh sáng lấp lánh'],
                 ['rì rào', 'Gợi âm thanh của gió, của lá'],
                 ['thoăn thoắt', 'Gợi dáng vẻ nhanh nhẹn']],
                'Từ láy giàu sức gợi hình, gợi âm thanh.');
            $this->matching($L, 'Nối mỗi từ ghép với nghĩa của các tiếng tạo thành.',
                [['bàn ghế', 'bàn + ghế'], ['quần áo', 'quần + áo'], ['sách vở', 'sách + vở']],
                'Từ ghép đẳng lập: các tiếng có nghĩa ngang nhau gộp lại.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ LÁY hoặc TỪ GHÉP.',
                [['xinh xắn', 'Từ láy'], ['lấp lánh', 'Từ láy'],
                 ['bàn ghế', 'Từ ghép'], ['sách vở', 'Từ ghép']],
                'Từ láy láy âm/vần; từ ghép gồm các tiếng có nghĩa.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ ĐƠN hoặc TỪ PHỨC.',
                [['cây', 'Từ đơn'], ['nhà', 'Từ đơn'],
                 ['trường học', 'Từ phức'], ['xe đạp', 'Từ phức']],
                'Từ đơn 1 tiếng, từ phức từ 2 tiếng trở lên.');
            $this->sortQ($L, 'Kéo mỗi từ láy vào nhóm LÁY ÂM ĐẦU hoặc LÁY VẦN.',
                [['rì rào', 'Láy âm đầu'], ['xinh xắn', 'Láy âm đầu'],
                 ['lung linh', 'Láy vần'], ['mấp mô', 'Láy vần']],
                'Rì rào láy âm r; lung linh láy vần inh.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ "rì rào" là từ ___.', [[0, 'láy']],
                'Rì rào láy lại âm đầu r nên là từ láy.');
            $this->fill($L, 'Từ "bàn ghế" được tạo bởi hai tiếng đều có nghĩa, gọi là từ ___.', [[0, 'ghép']],
                'Từ ghép gồm các tiếng có nghĩa kết hợp với nhau.');
            $this->fill($L, 'Từ chỉ gồm một tiếng gọi là từ ___.', [[0, 'đơn']],
                'Ví dụ: cây, nhà, xe là từ đơn.');
            $this->fill($L, 'Từ láy "lung linh" gợi tả ánh sáng lấp ___.', [[0, 'lánh']],
                'Lung linh gợi hình ảnh ánh sáng nhấp nháy đẹp mắt.');
        }
    }

    private function seedTvTuCau62(): void
    {
        $L = 'tv-tu-va-cau-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Từ nào sau đây là danh từ?',
                ['chạy', 'đẹp', 'bàn', 'nhanh'], 2,
                'Bàn chỉ đồ vật nên là danh từ.');
            $this->quiz($L, 'Từ nào sau đây là động từ?',
                ['xanh', 'nhảy', 'nhà', 'cao'], 1,
                'Nhảy chỉ hoạt động nên là động từ.');
            $this->quiz($L, 'Từ nào sau đây là tính từ?',
                ['sách', 'viết', 'đỏ', 'trường'], 2,
                'Đỏ chỉ màu sắc, đặc điểm nên là tính từ.');
            $this->quiz($L, 'Trong câu "Chim hót líu lo", động từ là từ nào?',
                ['chim', 'hót', 'líu lo', 'cả câu'], 1,
                'Hót là hoạt động của chim nên là động từ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với từ loại của nó.',
                [['học sinh', 'Danh từ'], ['chạy', 'Động từ'],
                 ['đẹp', 'Tính từ'], ['vui vẻ', 'Tính từ']],
                'Danh từ chỉ người/vật, động từ chỉ hoạt động, tính từ chỉ đặc điểm.');
            $this->matching($L, 'Nối mỗi danh từ với loại danh từ.',
                [['bàn ghế', 'Danh từ chung'], ['Hà Nội', 'Danh từ riêng'], ['niềm vui', 'Danh từ trừu tượng']],
                'Danh từ riêng chỉ tên riêng, viết hoa; danh từ chung chỉ loại sự vật.');
            $this->matching($L, 'Nối mỗi câu với động từ chính trong câu.',
                [['Mẹ nấu cơm', 'nấu'], ['Bé cười tươi', 'cười'], ['Chim bay cao', 'bay']],
                'Động từ chính diễn tả hoạt động chính của chủ ngữ.');
            $this->matching($L, 'Nối mỗi tính từ với đặc điểm nó diễn tả.',
                [['cao', 'Chiều cao'], ['đỏ', 'Màu sắc'], ['nhanh', 'Tốc độ'], ['ngọt', 'Vị']],
                'Tính từ chỉ đặc điểm, tính chất của sự vật.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc ĐỘNG TỪ.',
                [['sách', 'Danh từ'], ['trường', 'Danh từ'],
                 ['đọc', 'Động từ'], ['viết', 'Động từ']],
                'Sách, trường chỉ sự vật; đọc, viết chỉ hoạt động.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TÍNH TỪ hoặc KHÔNG PHẢI TÍNH TỪ.',
                [['đẹp', 'Tính từ'], ['cao', 'Tính từ'],
                 ['chạy', 'Không phải tính từ'], ['nhà', 'Không phải tính từ']],
                'Tính từ chỉ đặc điểm, tính chất.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ RIÊNG hoặc DANH TỪ CHUNG.',
                [['Hà Nội', 'Danh từ riêng'], ['sông Hồng', 'Danh từ riêng'],
                 ['học sinh', 'Danh từ chung'], ['quyển sách', 'Danh từ chung']],
                'Danh từ riêng chỉ tên riêng cụ thể, viết hoa chữ cái đầu.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ chỉ người, vật, hiện tượng gọi là danh ___.', [[0, 'từ']],
                'Định nghĩa danh từ.');
            $this->fill($L, 'Từ chỉ hoạt động, trạng thái gọi là động ___.', [[0, 'từ']],
                'Định nghĩa động từ.');
            $this->fill($L, 'Từ "xanh biếc" là tính ___.', [[0, 'từ']],
                'Xanh biếc chỉ màu sắc nên là tính từ.');
            $this->fill($L, 'Danh từ chỉ tên riêng của người, địa danh phải viết ___ chữ cái đầu.', [[0, 'hoa']],
                'Quy tắc viết hoa danh từ riêng.');
        }
    }

    private function seedTvTuCau71(): void
    {
        $L = 'tv-tu-va-cau-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cụm từ nào là cụm danh từ?',
                ['những bông hoa đẹp', 'đang nở rộ', 'rất thơm', 'hát hay'], 0,
                'Cụm danh từ có từ trung tâm là danh từ: những (phụ trước) + bông hoa (trung tâm) + đẹp (phụ sau).', 'trung_binh');
            $this->quiz($L, 'Trong cụm "những quyển sách hay", từ trung tâm là từ nào?',
                ['những', 'sách', 'hay', 'quyển'], 1,
                'Từ trung tâm là danh từ "sách"; "những" là phụ trước, "hay" là phụ sau.', 'trung_binh');
            $this->quiz($L, 'Cụm từ nào là cụm động từ?',
                ['con mèo mun', 'đang chạy nhanh', 'rất cao', 'những ngôi sao'], 1,
                'Cụm động từ có từ trung tâm là động từ: đang (phụ trước) + chạy (trung tâm) + nhanh (phụ sau).', 'trung_binh');
            $this->quiz($L, 'Cụm tính từ thường có cấu trúc nào?',
                ['phụ trước + tính từ trung tâm + phụ sau', 'danh từ + động từ', 'chủ ngữ + vị ngữ', 'từ láy + từ ghép'], 0,
                'Ví dụ: rất (phụ trước) + đẹp (trung tâm) + lắm (phụ sau).', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cụm từ với loại cụm từ.',
                [['những cánh đồng lúa', 'Cụm danh từ'],
                 ['đang gặt hái', 'Cụm động từ'],
                 ['rất xanh tốt', 'Cụm tính từ']],
                'Xác định từ trung tâm: danh từ, động từ hay tính từ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cụm từ với từ trung tâm của nó.',
                [['mấy con chim sẻ', 'chim'], ['đang học bài', 'học'], ['hơi buồn', 'buồn']],
                'Bỏ phần phụ đi, từ còn lại nêu ý chính là từ trung tâm.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ với vai trò của nó trong cụm từ.',
                [['những', 'Phụ trước'], ['đang', 'Phụ trước'], ['lắm', 'Phụ sau'], ['quá', 'Phụ sau']],
                'Phụ trước đứng trước từ trung tâm, phụ sau đứng sau.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cụm từ với thành phần câu nó đảm nhận (trong câu đã cho).',
                [['những bông hoa (trong "Những bông hoa nở")', 'Chủ ngữ'],
                 ['đang nở rộ (trong "Hoa đang nở rộ")', 'Vị ngữ']],
                'Cụm danh từ thường làm chủ ngữ, cụm động từ thường làm vị ngữ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm CỤM DANH TỪ hoặc CỤM ĐỘNG TỪ.',
                [['những ngôi nhà cao', 'Cụm danh từ'], ['con sông quê hương', 'Cụm danh từ'],
                 ['đang chảy xiết', 'Cụm động từ'], ['sẽ về thăm', 'Cụm động từ']],
                'Xem từ trung tâm là danh từ hay động từ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm theo từ trung tâm của nó.',
                [['các bạn học sinh', 'Trung tâm là danh từ'], ['một buổi sáng đẹp', 'Trung tâm là danh từ'],
                 ['đang vui chơi', 'Trung tâm là động từ'], ['đã lớn khôn', 'Trung tâm là động từ']],
                'Học sinh, buổi sáng là danh từ; vui chơi, lớn là động từ/tính từ trung tâm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm PHỤ TRƯỚC hoặc PHỤ SAU.',
                [['những', 'Phụ trước'], ['các', 'Phụ trước'], ['đang', 'Phụ trước'],
                 ['lắm', 'Phụ sau'], ['quá', 'Phụ sau']],
                'Phụ trước bổ sung ý nghĩa phía trước từ trung tâm, phụ sau bổ sung phía sau.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trong cụm từ, ngoài từ trung tâm còn có phần ___.', [[0, 'phụ']],
                'Cụm từ = từ trung tâm + phần phụ (phụ trước, phụ sau).', 'trung_binh');
            $this->fill($L, '"Những cánh cò trắng" là cụm danh ___.', [[0, 'từ']],
                'Từ trung tâm "cánh cò" là danh từ.', 'trung_binh');
            $this->fill($L, '"Đang bay lượn" là cụm động ___.', [[0, 'từ']],
                'Từ trung tâm "bay" là động từ.', 'trung_binh');
            $this->fill($L, 'Trong cụm "rất đẹp", từ trung tâm là từ "___".', [[0, 'đẹp']],
                '"Rất" là phụ trước, "đẹp" là tính từ trung tâm.', 'trung_binh');
        }
    }

    private function seedTvTuCau72(): void
    {
        $L = 'tv-tu-va-cau-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Câu đơn là câu như thế nào?',
                ['Có một cụm chủ ngữ – vị ngữ', 'Có hai cụm chủ ngữ – vị ngữ', 'Không có chủ ngữ', 'Chỉ có một từ'], 0,
                'Câu đơn chỉ có một cụm chủ ngữ – vị ngữ tạo thành một nòng cốt câu.', 'trung_binh');
            $this->quiz($L, 'Trong câu "Mẹ em đang nấu cơm", chủ ngữ là gì?',
                ['Mẹ em', 'đang nấu', 'cơm', 'đang nấu cơm'], 0,
                'Chủ ngữ trả lời câu hỏi Ai? – "Mẹ em".', 'trung_binh');
            $this->quiz($L, 'Trong câu "Hoa phượng nở đỏ rực", vị ngữ là gì?',
                ['Hoa phượng', 'nở đỏ rực', 'đỏ rực', 'Hoa'], 1,
                'Vị ngữ trả lời câu hỏi Làm gì? Thế nào? – "nở đỏ rực".', 'trung_binh');
            $this->quiz($L, 'Câu nào sau đây là câu đơn?',
                ['Trời mưa, đường trơn.', 'Trời đang mưa to.', 'Vì trời mưa nên đường trơn.', 'Trời mưa và gió thổi mạnh.'], 1,
                '"Trời đang mưa to." chỉ có một cụm chủ ngữ – vị ngữ (Trời / đang mưa to).', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu với chủ ngữ của nó.',
                [['Chim én bay về', 'Chim én'],
                 ['Các bạn học sinh chăm chỉ', 'Các bạn học sinh'],
                 ['Mùa xuân đến', 'Mùa xuân']],
                'Chủ ngữ trả lời câu hỏi Ai? Cái gì? Con gì?', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với vị ngữ của nó.',
                [['Mẹ đi chợ', 'đi chợ'], ['Hoa nở thơm ngát', 'nở thơm ngát'], ['Bé cười toe toét', 'cười toe toét']],
                'Vị ngữ trả lời câu hỏi Làm gì? Thế nào? Là gì?', 'trung_binh');
            $this->matching($L, 'Nối mỗi thành phần câu với câu hỏi dùng để tìm nó.',
                [['Chủ ngữ', 'Ai? Cái gì? Con gì?'],
                 ['Vị ngữ', 'Làm gì? Thế nào? Là gì?']],
                'Dùng câu hỏi để xác định thành phần câu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với loại câu của nó.',
                [['Trời mưa.', 'Câu đơn'], ['Gió thổi, lá rơi.', 'Câu ghép']],
                'Câu ghép có từ hai cụm chủ ngữ – vị ngữ trở lên.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU ĐƠN hoặc CÂU GHÉP.',
                [['Mẹ đang nấu cơm.', 'Câu đơn'], ['Em học bài.', 'Câu đơn'],
                 ['Trời mưa, em ở nhà.', 'Câu ghép'], ['Chim hót, hoa nở.', 'Câu ghép']],
                'Đếm số cụm chủ ngữ – vị ngữ trong câu.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm CHỦ NGỮ hoặc VỊ NGỮ.',
                [['Các bạn', 'Chủ ngữ'], ['Những chú chim', 'Chủ ngữ'],
                 ['đang đá bóng', 'Vị ngữ'], ['hót líu lo', 'Vị ngữ']],
                'Chủ ngữ nêu đối tượng, vị ngữ nêu hoạt động, đặc điểm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU KỂ hoặc CÂU HỎI.',
                [['Hôm nay trời đẹp.', 'Câu kể'], ['Em thích đọc sách.', 'Câu kể'],
                 ['Bạn tên là gì?', 'Câu hỏi'], ['Mấy giờ rồi?', 'Câu hỏi']],
                'Câu hỏi dùng để hỏi và cuối câu đặt dấu hỏi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Câu đơn có một cụm chủ ___ – vị ngữ.', [[0, 'ngữ']],
                'Nòng cốt của câu đơn là một cụm chủ ngữ – vị ngữ.', 'trung_binh');
            $this->fill($L, 'Trong câu "Ve sầu kêu râm ran", chủ ngữ là "___ sầu".', [[0, 'Ve']],
                'Chủ ngữ trả lời câu hỏi Con gì? – "Ve sầu".', 'trung_binh');
            $this->fill($L, 'Bộ phận trả lời câu hỏi "làm gì?", "thế nào?" trong câu gọi là vị ___.', [[0, 'ngữ']],
                'Vị ngữ nêu hoạt động, đặc điểm của chủ ngữ.', 'trung_binh');
            $this->fill($L, 'Câu "Gió thổi mạnh, lá rơi đầy sân" là câu ___ (đơn hay ghép).', [[0, 'ghép']],
                'Câu có hai cụm chủ ngữ – vị ngữ nên là câu ghép.', 'trung_binh');
        }
    }

    // ---------- Chính tả ----------

    private function seedTvChinhTa61(): void
    {
        $L = 'tv-chinh-ta-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['chăm chỉ', 'trăm chỉ', 'chăm trỉ', 'trăm chĩ'], 0,
                '"Chăm chỉ" (siêng năng) viết với ch.');
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['sáng sủa', 'xáng xủa', 'sáng xủa', 'xáng sủa'], 0,
                '"Sáng sủa" viết với s.');
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['rực rỡ', 'dực dỡ', 'rực dỡ', 'gực gỡ'], 0,
                '"Rực rỡ" viết với r.');
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['trong trẻo', 'chong trẻo', 'trong trẽo', 'chong chẻo'], 0,
                '"Trong trẻo" viết với tr.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi âm đầu với từ viết đúng có âm đầu đó.',
                [['Viết với ch', 'chăm chỉ'], ['Viết với tr', 'trong trẻo'],
                 ['Viết với s', 'sáng sủa'], ['Viết với x', 'xinh xắn']],
                'Ghi nhớ mặt chữ của từng từ để không nhầm âm đầu.');
            $this->matching($L, 'Nối mỗi từ láy với âm đầu đúng của nó.',
                [['rì rào', 'viết với r'], ['lung linh', 'viết với l'],
                 ['xôn xao', 'viết với x'], ['dịu dàng', 'viết với d']],
                'Từ láy thường giữ nguyên âm đầu ở cả hai tiếng.');
            $this->matching($L, 'Nối mỗi chỗ trống với âm đầu đúng.',
                [['___e chở (bao bọc)', 'ch'], ['___ang trại (nơi chăn nuôi)', 'tr'],
                 ['___uối nguồn (dòng nước)', 's'], ['___e mưa (vật che)', 'ch']],
                'Dựa vào nghĩa của từ để chọn âm đầu đúng: che chở, trang trại, suối nguồn.');
            $this->matching($L, 'Nối mỗi cặp từ dễ nhầm với cách phân biệt.',
                [['chăm chỉ – trăm năm', 'chăm (ch) khác trăm (tr)'],
                 ['sáng sủa – xinh xắn', 'sáng (s) khác xinh (x)']],
                'Đọc kĩ nghĩa từng từ để nhớ mặt chữ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm VIẾT VỚI CH hoặc VIẾT VỚI TR.',
                [['chăm chỉ', 'Viết với ch'], ['che chở', 'Viết với ch'],
                 ['trong trẻo', 'Viết với tr'], ['trang trại', 'Viết với tr']],
                'Ghi nhớ: chăm, che viết ch; trong, trang viết tr.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm VIẾT VỚI S hoặc VIẾT VỚI X.',
                [['sáng sủa', 'Viết với s'], ['suối nguồn', 'Viết với s'],
                 ['xinh xắn', 'Viết với x'], ['xôn xao', 'Viết với x']],
                'Ghi nhớ mặt chữ từng từ.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm VIẾT VỚI R hoặc VIẾT VỚI D.',
                [['rực rỡ', 'Viết với r'], ['rì rào', 'Viết với r'],
                 ['dịu dàng', 'Viết với d'], ['dũng cảm', 'Viết với d']],
                'Rực rỡ, rì rào viết r; dịu dàng, dũng cảm viết d.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ chỉ sự siêng năng, viết đúng là "chăm ___".', [[0, 'chỉ']],
                '"Chăm chỉ" viết với ch.');
            $this->fill($L, '"___áng sủa": điền âm đầu đúng để được từ chỉ sự sáng rõ.', [[0, 's']],
                '"Sáng sủa" viết với s.');
            $this->fill($L, '"___ong trẻo": điền âm đầu đúng để được từ chỉ âm thanh trong.', [[0, 'tr']],
                '"Trong trẻo" viết với tr.');
            $this->fill($L, '"xinh xắn": từ này viết với âm đầu ___.', [[0, 'x']],
                '"Xinh xắn" viết với x.');
        }
    }

    private function seedTvChinhTa62(): void
    {
        $L = 'tv-chinh-ta-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['củ cải', 'cũ cãi', 'củ cãi', 'cũ cải'], 0,
                '"Củ cải" (loại rau) viết củ với dấu hỏi.');
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['nghỉ ngơi', 'nghĩ ngơi', 'nghỉ ngoi', 'nghĩ ngoi'], 0,
                '"Nghỉ ngơi" viết nghỉ với dấu hỏi.');
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['vẻ đẹp', 'vẽ đẹp', 'vẽ dẹp', 'vẻ dẹp'], 0,
                '"Vẻ đẹp" viết vẻ với dấu hỏi.');
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['suy nghĩ', 'suy nghỉ', 'suy ngĩ', 'suy nghi'], 0,
                '"Suy nghĩ" viết nghĩ với dấu ngã.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với dấu thanh đúng của nó.',
                [['củ', 'Dấu hỏi'], ['cũ', 'Dấu ngã'],
                 ['nghỉ', 'Dấu hỏi'], ['nghĩ', 'Dấu ngã']],
                'Củ – cũ, nghỉ – nghĩ là các cặp dễ nhầm dấu hỏi/ngã.');
            $this->matching($L, 'Nối mỗi câu với từ viết đúng điền vào chỗ trống.',
                [['Em ___ ngơi sau giờ học.', 'nghỉ'],
                 ['Bài toán cần ___ nghĩ kĩ.', 'suy'],
                 ['Đôi giày này đã ___.', 'cũ'],
                 ['Mẹ mua ___ cải về nấu canh.', 'củ']],
                'Dựa vào nghĩa của câu để chọn từ đúng dấu.');
            $this->matching($L, 'Nối mỗi từ với dấu thanh của nó.',
                [['nghỉ ngơi', 'Dấu hỏi'], ['suy nghĩ', 'Dấu ngã'],
                 ['củ cải', 'Dấu hỏi'], ['cũ kĩ', 'Dấu ngã']],
                'Nghỉ, củ dấu hỏi; nghĩ, cũ dấu ngã.');
            $this->matching($L, 'Nối mỗi từ láy với dấu thanh của nó.',
                [['thỏ thẻ', 'Dấu hỏi'], ['mỏng manh', 'Dấu hỏi'],
                 ['lặng lẽ', 'Dấu ngã'], ['vội vàng', 'Dấu ngã']],
                'Thỏ, mỏng dấu hỏi; lặng, vội dấu ngã.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DẤU HỎI hoặc DẤU NGÃ.',
                [['củ', 'Dấu hỏi'], ['nghỉ', 'Dấu hỏi'], ['vẻ', 'Dấu hỏi'],
                 ['cũ', 'Dấu ngã'], ['nghĩ', 'Dấu ngã'], ['vẽ', 'Dấu ngã']],
                'Củ, nghỉ, vẻ dấu hỏi; cũ, nghĩ, vẽ dấu ngã.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm VIẾT VỚI DẤU HỎI hoặc VIẾT VỚI DẤU NGÃ.',
                [['nhỏ nhắn', 'Dấu hỏi'], ['thỏ thẻ', 'Dấu hỏi'],
                 ['lặng lẽ', 'Dấu ngã'], ['vội vàng', 'Dấu ngã']],
                'Nhỏ, thỏ dấu hỏi; lặng, vội dấu ngã.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm VIẾT ĐÚNG hoặc VIẾT SAI.',
                [['Em nghỉ ngơi sau giờ học.', 'Viết đúng'],
                 ['Củ cải rất ngon.', 'Viết đúng'],
                 ['Bạn ấy củ kĩ lắm.', 'Viết sai'],
                 ['Em suy nghỉ mãi.', 'Viết sai']],
                '"Cũ kĩ" phải viết dấu ngã; "suy nghĩ" phải viết dấu ngã.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"nghỉ ngơi" viết với dấu ___ (hỏi hay ngã).', [[0, 'hỏi']],
                'Nghỉ ngơi: nghỉ dấu hỏi.');
            $this->fill($L, '"suy nghĩ" viết với dấu ___.', [[0, 'ngã']],
                'Suy nghĩ: nghĩ dấu ngã.');
            $this->fill($L, 'Từ trái nghĩa với "mới" là "___" (viết đúng dấu).', [[0, 'cũ']],
                '"Cũ" viết với dấu ngã.');
            $this->fill($L, '"___ đẹp": điền từ đúng để được cụm từ chỉ nét đẹp.', [[0, 'vẻ']],
                '"Vẻ đẹp" viết vẻ với dấu hỏi.');
        }
    }

    private function seedTvChinhTa71(): void
    {
        $L = 'tv-chinh-ta-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tên riêng nào viết đúng chính tả?',
                ['hà nội', 'Hà nội', 'Hà Nội', 'HÀ NỘI'], 2,
                'Tên riêng viết hoa chữ cái đầu mỗi tiếng: Hà Nội.', 'trung_binh');
            $this->quiz($L, 'Tên người nào viết đúng chính tả?',
                ['nguyễn du', 'Nguyễn Du', 'Nguyễn du', 'nguyễn Du'], 1,
                'Tên người viết hoa chữ cái đầu mỗi tiếng: Nguyễn Du.', 'trung_binh');
            $this->quiz($L, 'Từ Hán Việt nào viết đúng chính tả?',
                ['tổ quốc', 'tỗ quốc', 'tổ quấc', 'tổ cuốc'], 0,
                '"Tổ quốc" là từ Hán Việt chỉ đất nước.', 'trung_binh');
            $this->quiz($L, 'Tên địa danh nào viết đúng chính tả?',
                ['sông hồng', 'Sông Hồng', 'Sông hồng', 'sông Hồng'], 1,
                'Tên địa lí viết hoa chữ cái đầu mỗi tiếng: Sông Hồng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tên riêng với quy tắc viết hoa.',
                [['Hà Nội', 'Viết hoa chữ cái đầu mỗi tiếng'],
                 ['Nguyễn Du', 'Viết hoa chữ cái đầu mỗi tiếng'],
                 ['Việt Nam', 'Viết hoa chữ cái đầu mỗi tiếng']],
                'Tên riêng gồm nhiều tiếng thì mỗi tiếng đều viết hoa chữ cái đầu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ Hán Việt với nghĩa của nó.',
                [['tổ quốc', 'Đất nước'], ['giang sơn', 'Sông núi, đất nước'], ['đồng bào', 'Người cùng một nước']],
                'Từ Hán Việt gốc Hán đã được Việt hoá.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tên với loại danh từ riêng.',
                [['Nguyễn Du', 'Tên người'], ['Hà Nội', 'Tên địa lí'], ['Trường Sa', 'Tên địa lí']],
                'Danh từ riêng chỉ tên riêng cụ thể.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tên gọi với cách viết đúng.',
                [['Bác Hồ', 'Viết hoa cả hai tiếng'],
                 ['sông Hồng', 'Viết hoa Sông và Hồng'],
                 ['biển Đông', 'Viết hoa Biển và Đông']],
                'Tên riêng nhiều tiếng: viết hoa chữ cái đầu mỗi tiếng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tên vào nhóm VIẾT HOA ĐÚNG hoặc VIẾT SAI.',
                [['Hà Nội', 'Viết đúng'], ['Nguyễn Du', 'Viết đúng'],
                 ['hà nội', 'Viết sai'], ['Sông hồng', 'Viết sai']],
                'Tên riêng phải viết hoa chữ cái đầu mỗi tiếng.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ HÁN VIỆT hoặc TỪ THUẦN VIỆT.',
                [['tổ quốc', 'Từ Hán Việt'], ['giang sơn', 'Từ Hán Việt'],
                 ['đất nước', 'Từ thuần Việt'], ['quê hương', 'Từ thuần Việt']],
                'Từ Hán Việt có gốc từ tiếng Hán.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ RIÊNG hoặc DANH TỪ CHUNG.',
                [['Hà Nội', 'Danh từ riêng'], ['Nguyễn Du', 'Danh từ riêng'],
                 ['học sinh', 'Danh từ chung'], ['quyển sách', 'Danh từ chung']],
                'Danh từ riêng chỉ một đối tượng cụ thể duy nhất.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tên riêng chỉ người, địa danh phải viết ___ chữ cái đầu mỗi tiếng.', [[0, 'hoa']],
                'Quy tắc viết hoa tên riêng.', 'trung_binh');
            $this->fill($L, 'Viết đúng tên thủ đô: ___ Nội.', [[0, 'Hà']],
                'Thủ đô nước ta là Hà Nội.', 'trung_binh');
            $this->fill($L, 'Từ Hán Việt chỉ đất nước: ___ quốc.', [[0, 'tổ']],
                '"Tổ quốc" nghĩa là đất nước.', 'trung_binh');
            $this->fill($L, 'Viết đúng tên đại thi hào dân tộc: Nguyễn ___.', [[0, 'Du']],
                'Nguyễn Du là tác giả Truyện Kiều.', 'trung_binh');
        }
    }

    private function seedTvChinhTa72(): void
    {
        $L = 'tv-chinh-ta-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['xán lạn', 'sán lạn', 'xán lạn', 'sáng lạn'], 0,
                '"Xán lạn" (rực rỡ, tươi sáng) viết với x.', 'trung_binh');
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['trau chuốt', 'trao chuốt', 'trau truốt', 'trao truốt'], 0,
                '"Trau chuốt" (chăm chút) viết trau với tr, chuốt với ch.', 'trung_binh');
            $this->quiz($L, 'Dấu nào được đặt ở cuối câu kể?',
                ['dấu chấm', 'dấu hỏi', 'dấu chấm than', 'dấu phẩy'], 0,
                'Câu kể kết thúc bằng dấu chấm.', 'trung_binh');
            $this->quiz($L, 'Từ nào viết đúng chính tả?',
                ['dè dặt', 'dề dặt', 'dè dặc', 'dề dặc'], 0,
                '"Dè dặt" (thận trọng) viết dè với dấu huyền.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ khó với cách viết đúng của nó.',
                [['xán lạn', 'Viết với x'], ['trau chuốt', 'Viết trau với tr, chuốt với ch'],
                 ['dè dặt', 'Viết với dấu huyền']],
                'Học thuộc mặt chữ các từ khó dễ nhầm.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dấu câu với công dụng của nó.',
                [['Dấu chấm', 'Kết thúc câu kể'],
                 ['Dấu hỏi', 'Kết thúc câu hỏi'],
                 ['Dấu chấm than', 'Kết thúc câu cảm thán'],
                 ['Dấu phẩy', 'Ngăn cách các bộ phận trong câu']],
                'Đặt dấu câu đúng giúp câu rõ nghĩa.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ với nghĩa của nó.',
                [['xán lạn', 'Rực rỡ, tươi sáng'], ['trau chuốt', 'Chăm chút cho đẹp'],
                 ['dè dặt', 'Thận trọng, e ngại']],
                'Hiểu nghĩa giúp nhớ mặt chữ của từ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với dấu câu đúng ở cuối câu.',
                [['Bạn tên là gì', 'Dấu hỏi'], ['Em đi học', 'Dấu chấm'],
                 ['Trời đẹp quá', 'Dấu chấm than']],
                'Câu hỏi – dấu hỏi; câu kể – dấu chấm; câu cảm thán – dấu chấm than.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm VIẾT ĐÚNG hoặc VIẾT SAI.',
                [['xán lạn', 'Viết đúng'], ['trau chuốt', 'Viết đúng'],
                 ['sán lạn', 'Viết sai'], ['trao chuốt', 'Viết sai']],
                'Xán lạn viết x, trau chuốt viết tr/ch.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu (chưa có dấu cuối câu) vào nhóm CẦN DẤU HỎI hoặc CẦN DẤU CHẤM.',
                [['Bạn có khỏe không', 'Cần dấu hỏi'], ['Mấy giờ bạn đến', 'Cần dấu hỏi'],
                 ['Em đang học bài', 'Cần dấu chấm'], ['Trời hôm nay đẹp', 'Cần dấu chấm']],
                'Câu dùng để hỏi thì cuối câu đặt dấu hỏi.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm ÂM ĐẦU X hoặc ÂM ĐẦU S.',
                [['xán lạn', 'Âm đầu x'], ['xôn xao', 'Âm đầu x'],
                 ['sáng sủa', 'Âm đầu s'], ['suôn sẻ', 'Âm đầu s']],
                'Ghi nhớ mặt chữ từng từ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ chỉ sự rực rỡ, tươi sáng viết đúng là "___ lạn".', [[0, 'xán']],
                '"Xán lạn" viết với x.', 'trung_binh');
            $this->fill($L, 'Cuối câu hỏi phải đặt dấu ___.', [[0, 'hỏi']],
                'Câu hỏi kết thúc bằng dấu hỏi.', 'trung_binh');
            $this->fill($L, 'Từ chỉ sự chăm chút cho đẹp: "trau ___".', [[0, 'chuốt']],
                '"Trau chuốt" viết chuốt với ch.', 'trung_binh');
            $this->fill($L, 'Dấu ___ dùng để ngăn cách các vế câu hoặc các bộ phận liệt kê.', [[0, 'phẩy']],
                'Dấu phẩy ngăn cách các bộ phận trong câu.', 'trung_binh');
        }
    }

    // ---------- Văn miêu tả ----------

    private function seedTvMieuTa71(): void
    {
        $L = 'tv-van-mieu-ta-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Văn miêu tả là loại văn có nhiệm vụ gì?',
                ['Kể lại sự việc', 'Tả sự vật, con người, cảnh vật', 'Nêu ý kiến', 'Giải thích vấn đề'], 1,
                'Văn miêu tả giúp người đọc hình dung sự vật như đang tận mắt trông thấy.');
            $this->quiz($L, 'Từ nào gợi tả âm thanh?',
                ['xanh biếc', 'rì rào', 'cao lớn', 'trắng tinh'], 1,
                'Rì rào gợi âm thanh của gió, của lá cây.');
            $this->quiz($L, 'Từ nào gợi tả màu sắc?',
                ['xanh biếc', 'rì rào', 'ầm ầm', 'thoăn thoắt'], 0,
                'Xanh biếc gợi màu xanh đẹp mắt.');
            $this->quiz($L, 'Câu nào là câu văn miêu tả?',
                ['Hôm qua em đi học.', 'Cánh đồng lúa chín vàng óng, trải dài tít tắp.', 'Em thích đọc sách.', 'Bạn Lan học giỏi.'], 1,
                'Câu tả cánh đồng lúa giúp người đọc hình dung cảnh vật.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với giác quan nó gợi tả.',
                [['rì rào', 'Thính giác'], ['xanh biếc', 'Thị giác'],
                 ['thơm ngát', 'Khứu giác'], ['mềm mại', 'Xúc giác']],
                'Từ ngữ miêu tả tác động vào các giác quan của người đọc.');
            $this->matching($L, 'Nối mỗi biện pháp tu từ với ví dụ của nó.',
                [['So sánh', 'Trăng như chiếc thuyền vàng'],
                 ['Nhân hoá', 'Chú gà trống oai vệ dạo bước']],
                'So sánh dùng từ "như"; nhân hoá gán đặc điểm con người cho vật.');
            $this->matching($L, 'Nối mỗi đối tượng với từ ngữ miêu tả phù hợp.',
                [['dòng sông', 'trong vắt, hiền hoà'],
                 ['cánh đồng', 'vàng óng, bát ngát'],
                 ['ngọn núi', 'cao vời vợi, hùng vĩ']],
                'Chọn từ ngữ phù hợp với đặc điểm của đối tượng tả.');
            $this->matching($L, 'Nối mỗi câu văn với biện pháp tu từ được dùng.',
                [['Mặt trời như quả cầu lửa.', 'So sánh'],
                 ['Hàng tre đung đưa chào đón.', 'Nhân hoá']],
                '"Như" báo hiệu so sánh; "chào đón" là hành động của con người.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm GỢI HÌNH ẢNH hoặc GỢI ÂM THANH.',
                [['xanh biếc', 'Gợi hình ảnh'], ['cao lớn', 'Gợi hình ảnh'],
                 ['rì rào', 'Gợi âm thanh'], ['ầm ầm', 'Gợi âm thanh']],
                'Từ gợi hình tác động vào thị giác, từ gợi âm thanh tác động vào thính giác.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm VĂN MIÊU TẢ hoặc VĂN TỰ SỰ.',
                [['Cánh đồng lúa chín vàng óng.', 'Văn miêu tả'],
                 ['Dòng sông trong vắt soi bóng mây.', 'Văn miêu tả'],
                 ['Sáng nay em đi học sớm.', 'Văn tự sự'],
                 ['Hôm qua lớp em thi văn nghệ.', 'Văn tự sự']],
                'Miêu tả thì tả, tự sự thì kể.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TẢ NGƯỜI hoặc TẢ CẢNH.',
                [['hiền hậu', 'Tả người'], ['chăm chỉ', 'Tả người'],
                 ['bát ngát', 'Tả cảnh'], ['trong vắt', 'Tả cảnh']],
                'Chọn từ ngữ phù hợp với đối tượng được tả.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Văn miêu tả giúp người đọc hình dung rõ sự vật như đang tận ___ trông thấy.', [[0, 'mắt']],
                'Miêu tả tái hiện sự vật sinh động như trước mắt.');
            $this->fill($L, 'Từ "rì rào" gợi tả ___ thanh của gió.', [[0, 'âm']],
                'Rì rào là từ gợi âm thanh.');
            $this->fill($L, 'Biện pháp "trăng như chiếc đĩa bạc" là biện pháp so ___.', [[0, 'sánh']],
                'Có từ "như" nối hai sự vật để so sánh.');
            $this->fill($L, 'Khi tả cảnh, cần quan sát bằng nhiều giác ___.', [[0, 'quan']],
                'Quan sát bằng mắt, tai, mũi... để tả đầy đủ.');
        }
    }

    private function seedTvMieuTa72(): void
    {
        $L = 'tv-van-mieu-ta-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bài văn tả người thường có bố cục mấy phần?',
                ['2 phần', '3 phần', '4 phần', '5 phần'], 1,
                'Bố cục 3 phần: mở bài, thân bài, kết bài.', 'trung_binh');
            $this->quiz($L, 'Phần mở bài của bài văn tả người thường làm gì?',
                ['Tả chi tiết', 'Giới thiệu người được tả', 'Kết luận', 'Nêu cảm nghĩ'], 1,
                'Mở bài giới thiệu người được tả (là ai, quan hệ với mình).', 'trung_binh');
            $this->quiz($L, 'Khi tả ngoại hình người, nên tả theo trình tự nào?',
                ['Ngẫu nhiên', 'Từ bao quát đến chi tiết', 'Từ chi tiết đến bao quát', 'Chỉ tả khuôn mặt'], 1,
                'Tả từ bao quát (dáng người, tuổi) đến chi tiết (khuôn mặt, mái tóc...).', 'trung_binh');
            $this->quiz($L, 'Câu nào tả ngoại hình của người?',
                ['Mẹ em rất hiền.', 'Mẹ em có mái tóc dài đen nhánh.', 'Em yêu mẹ lắm.', 'Mẹ nấu ăn ngon.'], 1,
                'Câu tả mái tóc là tả ngoại hình cụ thể.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phần của bài văn tả người với nội dung của nó.',
                [['Mở bài', 'Giới thiệu người được tả'],
                 ['Thân bài', 'Tả chi tiết ngoại hình, tính cách, hoạt động'],
                 ['Kết bài', 'Nêu cảm nghĩ về người được tả']],
                'Bố cục 3 phần rõ ràng giúp bài văn mạch lạc.', 'trung_binh');
            $this->matching($L, 'Nối mỗi bộ phận với từ ngữ miêu tả phù hợp.',
                [['mái tóc', 'đen nhánh, dài mượt'],
                 ['đôi mắt', 'sáng long lanh'],
                 ['nụ cười', 'hiền hậu, tươi tắn']],
                'Chọn từ ngữ gợi hình, gợi cảm cho từng bộ phận.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tính cách với biểu hiện của nó.',
                [['chăm chỉ', 'Dậy sớm học bài'],
                 ['hiền hậu', 'Hay giúp đỡ mọi người'],
                 ['dũng cảm', 'Dám làm việc khó']],
                'Tả tính cách qua hành động cụ thể sẽ thuyết phục hơn.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu văn với người được tả.',
                [['Tóc bà bạc trắng như cước.', 'Tả bà'],
                 ['Bố có đôi bàn tay chai sần.', 'Tả bố'],
                 ['Mẹ có giọng nói ấm áp.', 'Tả mẹ']],
                'Mỗi câu văn tập trung vào một đặc điểm nổi bật.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm TẢ NGOẠI HÌNH hoặc TẢ TÍNH CÁCH.',
                [['Mái tóc dài đen nhánh.', 'Tả ngoại hình'],
                 ['Đôi mắt sáng long lanh.', 'Tả ngoại hình'],
                 ['Bà rất hiền hậu.', 'Tả tính cách'],
                 ['Mẹ chăm chỉ, chịu khó.', 'Tả tính cách']],
                'Ngoại hình là những gì nhìn thấy; tính cách là phẩm chất bên trong.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi ý vào phần phù hợp của bài văn tả người.',
                [['Người em yêu quý nhất là mẹ.', 'Mở bài'],
                 ['Mẹ em năm nay ba mươi lăm tuổi.', 'Mở bài'],
                 ['Mẹ có dáng người thon thả.', 'Thân bài'],
                 ['Em rất yêu và kính trọng mẹ.', 'Kết bài']],
                'Mở bài giới thiệu, thân bài tả chi tiết, kết bài nêu cảm nghĩ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TẢ DÁNG VẺ hoặc TẢ KHUÔN MẶT.',
                [['cao ráo', 'Tả dáng vẻ'], ['thon thả', 'Tả dáng vẻ'],
                 ['phúc hậu', 'Tả khuôn mặt'], ['rạng rỡ', 'Tả khuôn mặt']],
                'Dáng vẻ là vóc dáng chung; khuôn mặt là nét mặt cụ thể.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bài văn miêu tả thường có 3 phần: mở bài, thân bài và ___ bài.', [[0, 'kết']],
                'Kết bài nêu cảm nghĩ của người viết.', 'trung_binh');
            $this->fill($L, 'Khi tả người, nên tả từ bao ___ đến chi tiết.', [[0, 'quát']],
                'Trình tự từ bao quát đến chi tiết giúp người đọc hình dung dễ dàng.', 'trung_binh');
            $this->fill($L, '"Đôi mắt bồ câu long lanh" là câu văn tả ngoại ___.', [[0, 'hình']],
                'Tả đôi mắt là tả ngoại hình.', 'trung_binh');
            $this->fill($L, 'Phần kết ___ nêu cảm nghĩ của người viết về người được tả.', [[0, 'bài']],
                'Kết bài bộc lộ tình cảm, cảm nghĩ.', 'trung_binh');
        }
    }

    private function seedTvMieuTa81(): void
    {
        $L = 'tv-van-mieu-ta-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi tả cảnh, yếu tố nào quan trọng nhất?',
                ['Kể nhiều sự việc', 'Quan sát tinh tế', 'Nêu ý kiến', 'Đặt nhiều câu hỏi'], 1,
                'Quan sát tinh tế bằng nhiều giác quan là nền tảng của bài văn tả cảnh hay.', 'trung_binh');
            $this->quiz($L, 'Câu "Sương giăng mờ như tấm voan mỏng" dùng biện pháp tu từ gì?',
                ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Hoán dụ'], 0,
                'Có từ "như" so sánh sương với tấm voan mỏng.', 'trung_binh');
            $this->quiz($L, 'Câu "Hàng cây rì rào hát ca" dùng biện pháp tu từ gì?',
                ['So sánh', 'Nhân hoá', 'Liệt kê', 'Điệp ngữ'], 1,
                '"Hát ca" là hành động của con người được gán cho hàng cây.', 'trung_binh');
            $this->quiz($L, 'Tả cảnh thường gắn với yếu tố nào?',
                ['Thời gian và không gian cụ thể', 'Các con số', 'Công thức', 'Định nghĩa'], 0,
                'Cảnh bình minh khác cảnh hoàng hôn; cần gắn với thời gian, không gian cụ thể.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cảnh với nét đặc trưng theo thời điểm.',
                [['Bình minh trên biển', 'Mặt trời nhô lên, mặt biển lấp lánh'],
                 ['Hoàng hôn trên đồng', 'Nắng vàng nhạt, khói lam chiều'],
                 ['Đêm trăng quê', 'Trăng sáng vằng vặc, tiếng côn trùng']],
                'Mỗi thời điểm cho cảnh vật một vẻ đẹp riêng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi biện pháp tu từ với tác dụng của nó.',
                [['So sánh', 'Làm hình ảnh sinh động, dễ hình dung'],
                 ['Nhân hoá', 'Làm cảnh vật có hồn như con người']],
                'Biện pháp tu từ làm câu văn giàu hình ảnh, cảm xúc.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu văn với biện pháp tu từ được dùng.',
                [['Nắng vàng như mật ong.', 'So sánh'],
                 ['Gió thì thầm cùng lá.', 'Nhân hoá'],
                 ['Trăng tròn như chiếc đĩa bạc.', 'So sánh']],
                'Nhận diện qua từ ngữ đặc trưng: "như", hành động của con người.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đối tượng tả cảnh với chi tiết miêu tả.',
                [['bầu trời', 'xanh trong, cao vời vợi'],
                 ['mặt hồ', 'phẳng lặng như gương'],
                 ['hàng tre', 'xanh mướt, đung đưa']],
                'Mỗi đối tượng có những chi tiết đặc trưng riêng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ SO SÁNH hoặc KHÔNG CÓ SO SÁNH.',
                [['Trăng như chiếc thuyền.', 'Có so sánh'],
                 ['Mây trắng như bông.', 'Có so sánh'],
                 ['Nắng vàng rực rỡ.', 'Không có so sánh'],
                 ['Gió thổi mạnh.', 'Không có so sánh']],
                'So sánh thường có từ "như", "tựa", "giống".', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ NHÂN HOÁ hoặc KHÔNG CÓ NHÂN HOÁ.',
                [['Chị gió đùa vui cùng lá.', 'Có nhân hoá'],
                 ['Ông mặt trời cười tươi.', 'Có nhân hoá'],
                 ['Lá rơi đầy sân.', 'Không có nhân hoá'],
                 ['Trời nhiều mây.', 'Không có nhân hoá']],
                'Nhân hoá gán cho vật đặc điểm, hành động của con người.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm TẢ BUỔI SÁNG hoặc TẢ BUỔI TỐI.',
                [['Sương sớm long lanh.', 'Tả buổi sáng'],
                 ['Chim hót líu lo.', 'Tả buổi sáng'],
                 ['Trăng lên cao.', 'Tả buổi tối'],
                 ['Sao nhấp nháy.', 'Tả buổi tối']],
                'Chi tiết phải phù hợp với thời điểm được tả.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Câu "Dòng sông như dải lụa" dùng biện pháp so ___.', [[0, 'sánh']],
                'Có từ "như" nối dòng sông với dải lụa.', 'trung_binh');
            $this->fill($L, 'Nhân hoá là gán cho sự vật đặc điểm của con ___.', [[0, 'người']],
                'Định nghĩa biện pháp nhân hoá.', 'trung_binh');
            $this->fill($L, 'Khi tả cảnh buổi sáng, có thể tả sương sớm, mặt ___ và tiếng chim.', [[0, 'trời']],
                'Mặt trời là chi tiết đặc trưng của buổi sáng.', 'trung_binh');
            $this->fill($L, 'Từ "bát ngát" thường dùng để tả cánh ___.', [[0, 'đồng']],
                '"Cánh đồng bát ngát" là cụm từ quen thuộc.', 'trung_binh');
        }
    }

    private function seedTvMieuTa82(): void
    {
        $L = 'tv-van-mieu-ta-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đoạn văn miêu tả hay cần có gì?',
                ['Nhiều số liệu', 'Hình ảnh sinh động và cảm xúc', 'Câu thật dài', 'Nhiều thuật ngữ'], 1,
                'Hình ảnh sinh động và cảm xúc chân thành làm nên đoạn văn hay.', 'trung_binh');
            $this->quiz($L, 'Câu "Ve kêu râm ran báo hiệu hè về" gợi cho người đọc điều gì?',
                ['Âm thanh mùa hè', 'Hình ảnh mùa đông', 'Mùi hương', 'Vị ngon'], 0,
                'Tiếng ve râm ran là âm thanh đặc trưng của mùa hè.', 'trung_binh');
            $this->quiz($L, 'Trong đoạn văn tả cảnh, câu nào nêu cảm xúc của người viết?',
                ['Em rất yêu quê hương em.', 'Cây cao 5 mét.', 'Sông dài 10 km.', 'Trời có nhiều mây.'], 0,
                'Câu bộc lộ tình cảm là câu nêu cảm xúc.', 'trung_binh');
            $this->quiz($L, 'Khi viết đoạn văn miêu tả nên tránh điều gì?',
                ['Dùng từ ngữ gợi hình', 'Lặp đi lặp lại một từ', 'Quan sát kĩ', 'Sắp xếp ý hợp lí'], 1,
                'Lặp từ làm câu văn đơn điệu, nên thay bằng từ đồng nghĩa.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu văn với cảm nhận nó gợi ra.',
                [['Nắng vàng óng ả trải khắp cánh đồng.', 'Cảm giác ấm áp, trù phú'],
                 ['Gió heo may se lạnh.', 'Cảm giác thu về'],
                 ['Mưa xuân lất phất bay.', 'Cảm giác mát lành, tươi mới']],
                'Câu văn hay gợi được cảm giác, cảm xúc cho người đọc.', 'trung_binh');
            $this->matching($L, 'Nối mỗi chủ đề với câu mở đoạn phù hợp.',
                [['Tả dòng sông quê', 'Dòng sông quê em trong vắt, hiền hoà.'],
                 ['Tả con đường làng', 'Con đường làng rợp bóng tre xanh.']],
                'Câu mở đoạn giới thiệu đối tượng được tả.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ với sắc thái cảm xúc của nó.',
                [['lấp lánh', 'Tích cực, đẹp'], ['rộn ràng', 'Tích cực, vui'],
                 ['u ám', 'Tiêu cực, buồn'], ['hiu quạnh', 'Tiêu cực, buồn']],
                'Chọn từ có sắc thái phù hợp với cảm xúc muốn diễn tả.', 'trung_binh');
            $this->matching($L, 'Nối mỗi lỗi thường gặp với cách khắc phục.',
                [['Lặp từ "đẹp" nhiều lần', 'Thay bằng từ đồng nghĩa: xinh, duyên dáng'],
                 ['Câu văn khô khan', 'Thêm từ ngữ gợi hình, gợi cảm']],
                'Sửa lỗi giúp đoạn văn sinh động hơn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU VĂN HAY hoặc CHƯA HAY.',
                [['Cánh đồng lúa chín vàng óng, hương lúa thơm ngát.', 'Câu văn hay'],
                 ['Dòng sông trong vắt, soi bóng mây trời.', 'Câu văn hay'],
                 ['Cánh đồng có lúa.', 'Chưa hay'],
                 ['Sông rất dài.', 'Chưa hay']],
                'Câu văn hay có hình ảnh cụ thể, từ ngữ gợi cảm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm GỢI CẢM XÚC VUI hoặc BUỒN.',
                [['rộn ràng', 'Vui'], ['tươi tắn', 'Vui'],
                 ['ảm đạm', 'Buồn'], ['hiu quạnh', 'Buồn']],
                'Sắc thái của từ quyết định cảm xúc của câu văn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi ý vào nhóm NÊN CÓ hoặc KHÔNG NÊN CÓ trong đoạn văn tả cảnh.',
                [['Chi tiết quan sát tinh tế', 'Nên có'],
                 ['Cảm xúc của người viết', 'Nên có'],
                 ['Liệt kê số liệu khô khan', 'Không nên có'],
                 ['Lặp lại một ý nhiều lần', 'Không nên có']],
                'Đoạn văn tả cảnh cần chi tiết sinh động và cảm xúc, tránh khô khan, lặp ý.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đoạn văn miêu tả hay cần có hình ảnh sinh động và cảm ___ chân thành.', [[0, 'xúc']],
                'Cảm xúc chân thành làm đoạn văn có hồn.', 'trung_binh');
            $this->fill($L, 'Khi viết, tránh ___ lại cùng một từ nhiều lần.', [[0, 'lặp']],
                'Lặp từ làm văn đơn điệu.', 'trung_binh');
            $this->fill($L, 'Từ "rộn ràng" gợi cảm xúc vui ___.', [[0, 'tươi']],
                '"Vui tươi" là cụm từ quen thuộc.', 'trung_binh');
            $this->fill($L, 'Kết đoạn văn tả cảnh nên bày tỏ tình cảm, suy ___ của mình.', [[0, 'nghĩ']],
                'Kết đoạn bộc lộ tình cảm, suy nghĩ về cảnh vật.', 'trung_binh');
        }
    }

    // ================= TIẾNG ANH =================

    // ---------- Từ vựng lớp 6 ----------

    private function seedEnVocab661(): void
    {
        $L = 'en-tu-vung-lop-6-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Father" nghĩa là gì?',
                ['mẹ', 'bố', 'anh trai', 'chị gái'], 1,
                '"Father" nghĩa là bố.');
            $this->quiz($L, '"Sister" nghĩa là gì?',
                ['anh trai', 'chị/em gái', 'bố', 'ông'], 1,
                '"Sister" nghĩa là chị gái hoặc em gái.');
            $this->quiz($L, 'Từ nào có nghĩa là "ông"?',
                ['grandmother', 'grandfather', 'uncle', 'aunt'], 1,
                '"Grandfather" nghĩa là ông; "grandmother" là bà.');
            $this->quiz($L, 'Trong câu "My mother is a teacher.", từ "mother" nghĩa là gì?',
                ['bố', 'mẹ', 'cô', 'dì'], 1,
                '"Mother" nghĩa là mẹ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ tiếng Anh với nghĩa tiếng Việt.',
                [['father', 'bố'], ['mother', 'mẹ'],
                 ['brother', 'anh/em trai'], ['sister', 'chị/em gái']],
                'Bốn từ cơ bản nhất về gia đình.');
            $this->matching($L, 'Nối mỗi từ tiếng Anh với nghĩa tiếng Việt.',
                [['grandfather', 'ông'], ['grandmother', 'bà'],
                 ['uncle', 'chú/bác'], ['aunt', 'cô/dì']],
                'Ông bà và chú bác cô dì trong gia đình.');
            $this->matching($L, 'Nối mỗi câu tiếng Anh với nghĩa tiếng Việt.',
                [['I love my family.', 'Tôi yêu gia đình tôi.'],
                 ['She is my sister.', 'Cô ấy là chị/em gái tôi.'],
                 ['He is my brother.', 'Anh ấy là anh/em trai tôi.']],
                'Luyện đọc hiểu câu đơn giản về gia đình.');
            $this->matching($L, 'Nối mỗi từ với nghĩa của nó.',
                [['son', 'con trai'], ['daughter', 'con gái'],
                 ['parents', 'bố mẹ'], ['children', 'con cái']],
                'Son – daughter, parents – children là các cặp từ liên quan.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm chỉ NGƯỜI NAM hoặc NGƯỜI NỮ.',
                [['father', 'Người nam'], ['brother', 'Người nam'], ['uncle', 'Người nam'],
                 ['mother', 'Người nữ'], ['sister', 'Người nữ'], ['aunt', 'Người nữ']],
                'Father, brother, uncle là nam; mother, sister, aunt là nữ.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm THẾ HỆ ÔNG BÀ hoặc THẾ HỆ BỐ MẸ.',
                [['grandfather', 'Thế hệ ông bà'], ['grandmother', 'Thế hệ ông bà'],
                 ['father', 'Thế hệ bố mẹ'], ['mother', 'Thế hệ bố mẹ']],
                'Grandfather/grandmother là ông bà; father/mother là bố mẹ.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm CHỦ ĐỀ GIA ĐÌNH hoặc CHỦ ĐỀ TRƯỜNG HỌC.',
                [['father', 'Gia đình'], ['sister', 'Gia đình'],
                 ['teacher', 'Trường học'], ['student', 'Trường học']],
                'Phân biệt từ vựng theo chủ đề.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Bố" trong tiếng Anh là ___.', [[0, 'father']],
                'Father = bố.');
            $this->fill($L, 'She is my ___. (Cô ấy là chị gái tôi.)', [[0, 'sister']],
                'Sister = chị/em gái.');
            $this->fill($L, 'Grand___ nghĩa là "ông".', [[0, 'father']],
                'Grandfather = ông (ghép grand + father).');
            $this->fill($L, '"Gia đình" trong tiếng Anh là ___.', [[0, 'family']],
                'Family = gia đình.');
        }
    }

    private function seedEnVocab662(): void
    {
        $L = 'en-tu-vung-lop-6-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Teacher" nghĩa là gì?',
                ['học sinh', 'giáo viên', 'lớp học', 'quyển sách'], 1,
                '"Teacher" nghĩa là giáo viên.');
            $this->quiz($L, '"Book" nghĩa là gì?',
                ['bút', 'sách', 'bàn', 'bảng'], 1,
                '"Book" nghĩa là quyển sách.');
            $this->quiz($L, 'Từ nào có nghĩa là "lớp học"?',
                ['school', 'classroom', 'library', 'playground'], 1,
                '"Classroom" là lớp học; "school" là trường học.');
            $this->quiz($L, '"Pen" nghĩa là gì?',
                ['bút chì', 'bút mực', 'thước kẻ', 'cục tẩy'], 1,
                '"Pen" là bút mực; "pencil" là bút chì.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ tiếng Anh với nghĩa tiếng Việt.',
                [['teacher', 'giáo viên'], ['student', 'học sinh'],
                 ['classroom', 'lớp học'], ['school', 'trường học']],
                'Từ vựng cơ bản về trường học.');
            $this->matching($L, 'Nối mỗi đồ dùng học tập với nghĩa của nó.',
                [['pen', 'bút mực'], ['pencil', 'bút chì'],
                 ['ruler', 'thước kẻ'], ['eraser', 'cục tẩy']],
                'Bốn đồ dùng học tập thông dụng nhất.');
            $this->matching($L, 'Nối mỗi môn học với nghĩa của nó.',
                [['math', 'môn Toán'], ['English', 'môn tiếng Anh'],
                 ['music', 'môn Âm nhạc'], ['art', 'môn Mĩ thuật']],
                'Tên các môn học bằng tiếng Anh.');
            $this->matching($L, 'Nối mỗi câu tiếng Anh với nghĩa tiếng Việt.',
                [['I go to school by bike.', 'Tôi đi học bằng xe đạp.'],
                 ['She likes English.', 'Cô ấy thích môn tiếng Anh.']],
                'Luyện đọc hiểu câu đơn giản về trường học.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm chỉ NGƯỜI hoặc chỉ ĐỒ VẬT.',
                [['teacher', 'Người'], ['student', 'Người'],
                 ['book', 'Đồ vật'], ['pen', 'Đồ vật']],
                'Teacher, student là người; book, pen là đồ vật.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm MÔN HỌC hoặc ĐỒ DÙNG HỌC TẬP.',
                [['math', 'Môn học'], ['English', 'Môn học'],
                 ['pencil', 'Đồ dùng học tập'], ['ruler', 'Đồ dùng học tập']],
                'Math, English là môn học; pencil, ruler là đồ dùng.');
            $this->sortQ($L, 'Kéo mỗi nơi chốn vào nhóm TRONG LỚP HỌC hoặc NGOÀI LỚP HỌC.',
                [['classroom', 'Trong lớp học'], ['board', 'Trong lớp học'],
                 ['playground', 'Ngoài lớp học'], ['garden', 'Ngoài lớp học']],
                'Board (bảng) ở trong lớp; playground (sân chơi) ở ngoài.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Giáo viên" trong tiếng Anh là ___.', [[0, 'teacher']],
                'Teacher = giáo viên.');
            $this->fill($L, 'I write with a ___. (Tôi viết bằng bút mực.)', [[0, 'pen']],
                'Pen = bút mực.');
            $this->fill($L, '"Thư viện" trong tiếng Anh là ___.', [[0, 'library']],
                'Library = thư viện.');
            $this->fill($L, 'She is a ___. (Cô ấy là học sinh.)', [[0, 'student']],
                'Student = học sinh.');
        }
    }

    private function seedEnVocab671(): void
    {
        $L = 'en-tu-vung-lop-6-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Get up" nghĩa là gì?',
                ['đi ngủ', 'thức dậy', 'ăn sáng', 'đi học'], 1,
                '"Get up" nghĩa là thức dậy, ra khỏi giường.');
            $this->quiz($L, '"Have breakfast" nghĩa là gì?',
                ['ăn trưa', 'ăn sáng', 'ăn tối', 'uống nước'], 1,
                '"Have breakfast" nghĩa là ăn sáng.');
            $this->quiz($L, 'Cụm từ nào có nghĩa là "làm bài tập về nhà"?',
                ['do homework', 'play games', 'watch TV', 'go out'], 0,
                '"Do homework" nghĩa là làm bài tập về nhà.');
            $this->quiz($L, '"Brush teeth" nghĩa là gì?',
                ['rửa mặt', 'đánh răng', 'gội đầu', 'tắm'], 1,
                '"Brush teeth" nghĩa là đánh răng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cụm từ với nghĩa của nó.',
                [['get up', 'thức dậy'], ['go to bed', 'đi ngủ'],
                 ['have lunch', 'ăn trưa'], ['do homework', 'làm bài tập']],
                'Các hoạt động trong một ngày.');
            $this->matching($L, 'Nối mỗi cụm thời gian với nghĩa của nó.',
                [['in the morning', 'buổi sáng'], ['in the afternoon', 'buổi chiều'],
                 ['in the evening', 'buổi tối'], ['at night', 'ban đêm']],
                'Cụm giới từ chỉ thời gian trong ngày.');
            $this->matching($L, 'Nối mỗi từ với nghĩa của nó.',
                [['hobby', 'sở thích'], ['free time', 'thời gian rảnh'],
                 ['sport', 'môn thể thao'], ['game', 'trò chơi']],
                'Từ vựng về sở thích và giải trí.');
            $this->matching($L, 'Nối mỗi câu với nghĩa của nó.',
                [['I play football after school.', 'Tôi chơi bóng đá sau giờ học.'],
                 ['She watches TV in the evening.', 'Cô ấy xem ti-vi vào buổi tối.']],
                'Luyện đọc hiểu câu về hoạt động hằng ngày.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm BUỔI SÁNG hoặc BUỔI TỐI.',
                [['get up', 'Buổi sáng'], ['have breakfast', 'Buổi sáng'],
                 ['go to bed', 'Buổi tối'], ['brush teeth (before bed)', 'Buổi tối']],
                'Thức dậy, ăn sáng vào buổi sáng; đi ngủ vào buổi tối.');
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm Ở NHÀ hoặc Ở TRƯỜNG.',
                [['do homework', 'Ở nhà'], ['have dinner', 'Ở nhà'],
                 ['learn English', 'Ở trường'], ['play with friends (at break)', 'Ở trường']],
                'Làm bài tập, ăn tối ở nhà; học và chơi ở trường.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm ĐỘNG TỪ hoặc DANH TỪ.',
                [['play', 'Động từ'], ['watch', 'Động từ'],
                 ['hobby', 'Danh từ'], ['game', 'Danh từ']],
                'Play, watch chỉ hành động; hobby, game chỉ sự vật.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Thức dậy" trong tiếng Anh là get ___.', [[0, 'up']],
                'Get up = thức dậy.');
            $this->fill($L, 'I ___ homework every evening. (Tôi làm bài tập mỗi tối.)', [[0, 'do']],
                'Do homework = làm bài tập về nhà.');
            $this->fill($L, '"Sở thích" trong tiếng Anh là ___.', [[0, 'hobby']],
                'Hobby = sở thích.');
            $this->fill($L, 'She ___ to bed at 10 p.m. (Cô ấy đi ngủ lúc 10 giờ tối.)', [[0, 'goes']],
                'Go to bed = đi ngủ; chủ ngữ she nên động từ thêm s.');
        }
    }

    private function seedEnVocab672(): void
    {
        $L = 'en-tu-vung-lop-6-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Sunny" nghĩa là gì?',
                ['mưa', 'nắng', 'nhiều mây', 'nhiều gió'], 1,
                '"Sunny" là tính từ chỉ trời nắng.', 'trung_binh');
            $this->quiz($L, '"Rainy" nghĩa là gì?',
                ['nắng', 'mưa', 'lạnh', 'nóng'], 1,
                '"Rainy" là tính từ chỉ trời mưa.', 'trung_binh');
            $this->quiz($L, 'Từ nào có nghĩa là "cầu vồng"?',
                ['cloud', 'rainbow', 'storm', 'wind'], 1,
                '"Rainbow" nghĩa là cầu vồng.', 'trung_binh');
            $this->quiz($L, 'Câu "It\'s windy today." nghĩa là gì?',
                ['Hôm nay trời nắng.', 'Hôm nay trời mưa.', 'Hôm nay trời nhiều gió.', 'Hôm nay trời lạnh.'], 2,
                '"Windy" nghĩa là nhiều gió.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tính từ thời tiết với nghĩa của nó.',
                [['sunny', 'nắng'], ['cloudy', 'nhiều mây'],
                 ['windy', 'nhiều gió'], ['snowy', 'có tuyết']],
                'Các tính từ miêu tả thời tiết.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ chỉ cảnh quan với nghĩa của nó.',
                [['mountain', 'núi'], ['river', 'sông'],
                 ['forest', 'rừng'], ['beach', 'bãi biển']],
                'Từ vựng về cảnh quan thiên nhiên.', 'trung_binh');
            $this->matching($L, 'Nối mỗi mùa với đặc điểm của nó.',
                [['spring', 'mùa xuân, hoa nở'], ['summer', 'mùa hè, nóng'],
                 ['autumn', 'mùa thu, lá vàng'], ['winter', 'mùa đông, lạnh']],
                'Bốn mùa trong năm và đặc điểm mỗi mùa.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với nghĩa của nó.',
                [['It is raining.', 'Trời đang mưa.'],
                 ['The sun is shining.', 'Mặt trời đang chiếu sáng.']],
                'Câu miêu tả thời tiết ở thì hiện tại tiếp diễn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm THỜI TIẾT ĐẸP hoặc THỜI TIẾT XẤU.',
                [['sunny', 'Thời tiết đẹp'], ['fine', 'Thời tiết đẹp'],
                 ['stormy', 'Thời tiết xấu'], ['rainy', 'Thời tiết xấu']],
                'Sunny, fine là đẹp; stormy (bão), rainy là xấu.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm THIÊN NHIÊN hoặc THỜI TIẾT.',
                [['mountain', 'Thiên nhiên'], ['river', 'Thiên nhiên'],
                 ['sunny', 'Thời tiết'], ['windy', 'Thời tiết']],
                'Mountain, river là cảnh quan; sunny, windy là thời tiết.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc TÍNH TỪ.',
                [['rain', 'Danh từ'], ['sun', 'Danh từ'],
                 ['rainy', 'Tính từ'], ['sunny', 'Tính từ']],
                'Danh từ thêm y thành tính từ: rain → rainy.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Nắng" (tính từ) trong tiếng Anh là ___.', [[0, 'sunny']],
                'Sunny là tính từ của sun.', 'trung_binh');
            $this->fill($L, 'It is ___ today. Take your umbrella! (Hôm nay trời mưa. Hãy mang ô!)', [[0, 'raining']],
                'It is raining = Trời đang mưa.', 'trung_binh');
            $this->fill($L, '"Dòng sông" trong tiếng Anh là ___.', [[0, 'river']],
                'River = dòng sông.', 'trung_binh');
            $this->fill($L, 'There are four ___ in a year: spring, summer, autumn and winter.', [[0, 'seasons']],
                'Seasons = các mùa.', 'trung_binh');
        }
    }

    // ---------- Ngữ pháp cơ bản ----------

    private function seedEnGrammar61(): void
    {
        $L = 'en-ngu-phap-co-ban-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Điền từ đúng: "I ___ a student."',
                ['am', 'is', 'are', 'be'], 0,
                'Chủ ngữ I luôn đi với am.');
            $this->quiz($L, 'Điền từ đúng: "She ___ my sister."',
                ['am', 'is', 'are', 'be'], 1,
                'Chủ ngữ ngôi thứ ba số ít (she) đi với is.');
            $this->quiz($L, 'Điền từ đúng: "They ___ happy."',
                ['am', 'is', 'are', 'be'], 2,
                'Chủ ngữ số nhiều (they) đi với are.');
            $this->quiz($L, 'Điền từ đúng: "___ you a teacher?"',
                ['Am', 'Is', 'Are', 'Be'], 2,
                'Câu hỏi đảo to be lên trước chủ ngữ: Are you...?');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chủ ngữ với dạng to be đúng.',
                [['I', 'am'], ['he / she / it', 'is'], ['you / we / they', 'are']],
                'Quy tắc chia động từ to be ở hiện tại.');
            $this->matching($L, 'Nối mỗi câu tiếng Anh với nghĩa tiếng Việt.',
                [['I am happy.', 'Tôi vui.'], ['She is tall.', 'Cô ấy cao.'],
                 ['They are friends.', 'Họ là bạn bè.']],
                'Câu đơn giản với động từ to be.');
            $this->matching($L, 'Nối mỗi câu khẳng định với câu phủ định của nó.',
                [['I am a student.', 'I am not a student.'],
                 ['She is tall.', 'She is not tall.'],
                 ['They are late.', 'They are not late.']],
                'Câu phủ định: thêm not sau to be.');
            $this->matching($L, 'Nối mỗi câu hỏi với câu trả lời đúng.',
                [['Are you OK?', 'Yes, I am.'], ['Is she a teacher?', 'No, she is not.']],
                'Trả lời câu hỏi Yes/No với to be.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chủ ngữ vào nhóm đi với AM, IS hoặc ARE.',
                [['I', 'Đi với am'], ['he', 'Đi với is'], ['she', 'Đi với is'],
                 ['you', 'Đi với are'], ['they', 'Đi với are']],
                'I + am; he/she/it + is; you/we/they + are.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU KHẲNG ĐỊNH hoặc CÂU PHỦ ĐỊNH.',
                [['I am fine.', 'Khẳng định'], ['They are students.', 'Khẳng định'],
                 ['She is not here.', 'Phủ định'], ['He is not tall.', 'Phủ định']],
                'Câu phủ định có not sau to be.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU KỂ hoặc CÂU HỎI.',
                [['She is my mother.', 'Câu kể'], ['I am 12 years old.', 'Câu kể'],
                 ['Are you ready?', 'Câu hỏi'], ['Is he at home?', 'Câu hỏi']],
                'Câu hỏi đảo to be lên đầu câu.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'I ___ 12 years old. (Tôi 12 tuổi.)', [[0, 'am']],
                'I đi với am.');
            $this->fill($L, 'She ___ not my teacher. (Cô ấy không phải giáo viên của tôi.)', [[0, 'is']],
                'She đi với is; phủ định thêm not.');
            $this->fill($L, '___ they your friends? (Họ có phải bạn của bạn không?)', [[0, 'Are']],
                'Câu hỏi với they dùng Are đứng đầu.');
            $this->fill($L, 'We ___ happy today. (Hôm nay chúng tôi vui.)', [[0, 'are']],
                'We đi với are.');
        }
    }

    private function seedEnGrammar62(): void
    {
        $L = 'en-ngu-phap-co-ban-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Điền từ đúng: "She ___ to school every day."',
                ['go', 'goes', 'going', 'gone'], 1,
                'Chủ ngữ ngôi thứ ba số ít (she) nên động từ thêm s: goes.');
            $this->quiz($L, 'Điền từ đúng: "They ___ football after school."',
                ['plays', 'play', 'playing', 'played'], 1,
                'Chủ ngữ số nhiều (they) nên động từ giữ nguyên: play.');
            $this->quiz($L, 'Điền từ đúng: "He ___ TV in the evening."',
                ['watch', 'watches', 'watching', 'watched'], 1,
                'He là ngôi thứ ba số ít, watch thêm es thành watches.');
            $this->quiz($L, 'Thì hiện tại đơn dùng để diễn tả điều gì?',
                ['Hành động đang xảy ra', 'Thói quen hằng ngày', 'Hành động đã xong', 'Dự định tương lai'], 1,
                'Hiện tại đơn diễn tả thói quen, sự thật hiển nhiên.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chủ ngữ với dạng động từ "play" đúng.',
                [['I / you / we / they', 'play'], ['he / she / it', 'plays']],
                'Ngôi thứ ba số ít thêm s vào động từ.');
            $this->matching($L, 'Nối mỗi câu với nghĩa của nó.',
                [['I get up at 6 a.m.', 'Tôi thức dậy lúc 6 giờ sáng.'],
                 ['She goes to bed late.', 'Cô ấy đi ngủ muộn.']],
                'Câu thì hiện tại đơn diễn tả thói quen.');
            $this->matching($L, 'Nối mỗi động từ với dạng thêm s/es đúng.',
                [['play', 'plays'], ['go', 'goes'], ['watch', 'watches'], ['study', 'studies']],
                'Go, watch thêm es; study đổi y thành ies.');
            $this->matching($L, 'Nối mỗi trạng từ với nghĩa của nó.',
                [['every day', 'mỗi ngày'], ['usually', 'thường xuyên'],
                 ['sometimes', 'thỉnh thoảng'], ['always', 'luôn luôn']],
                'Các trạng từ là dấu hiệu của thì hiện tại đơn.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi động từ vào nhóm THÊM -S hoặc THÊM -ES (ngôi thứ ba số ít).',
                [['play', 'Thêm -s'], ['read', 'Thêm -s'],
                 ['go', 'Thêm -es'], ['watch', 'Thêm -es']],
                'Động từ tận cùng bằng o, ch, sh, x, s thêm es.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI ngữ pháp.',
                [['She likes apples.', 'Đúng'], ['They play games.', 'Đúng'],
                 ['He go to school.', 'Sai'], ['She watch TV.', 'Sai']],
                'He, she là ngôi thứ ba số ít, động từ phải thêm s/es.');
            $this->sortQ($L, 'Kéo mỗi chủ ngữ vào nhóm NGÔI THỨ BA SỐ ÍT hoặc KHÁC.',
                [['he', 'Ngôi thứ ba số ít'], ['she', 'Ngôi thứ ba số ít'],
                 ['I', 'Khác'], ['they', 'Khác']],
                'Chỉ he, she, it mới thêm s vào động từ.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'She ___ (go) to school by bike.', [[0, 'goes']],
                'She là ngôi thứ ba số ít: go → goes.');
            $this->fill($L, 'They ___ (play) chess every Sunday.', [[0, 'play']],
                'They là số nhiều: động từ giữ nguyên.');
            $this->fill($L, 'He ___ (watch) TV after dinner.', [[0, 'watches']],
                'Watch tận cùng bằng ch nên thêm es.');
            $this->fill($L, 'My mother ___ (cook) very well.', [[0, 'cooks']],
                'My mother = she: cook → cooks.');
        }
    }

    private function seedEnGrammar71(): void
    {
        $L = 'en-ngu-phap-co-ban-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Điền từ đúng: "She ___ like coffee."',
                ['do not', 'does not', 'is not', 'not'], 1,
                'Chủ ngữ she dùng does not (doesn’t).', 'trung_binh');
            $this->quiz($L, 'Điền từ đúng: "___ they play badminton?"',
                ['Does', 'Do', 'Are', 'Is'], 1,
                'Chủ ngữ they dùng Do đứng đầu câu hỏi.', 'trung_binh');
            $this->quiz($L, 'Điền từ đúng: "He does not ___ TV."',
                ['watches', 'watch', 'watching', 'watched'], 1,
                'Sau does/does not, động từ ở dạng nguyên mẫu.', 'trung_binh');
            $this->quiz($L, 'Điền từ đúng: "___ she go to school by bus?"',
                ['Does', 'Do', 'Is', 'Are'], 0,
                'Chủ ngữ she dùng Does đứng đầu câu hỏi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chủ ngữ với trợ động từ phủ định đúng.',
                [['I / you / we / they', 'do not (don’t)'],
                 ['he / she / it', 'does not (doesn’t)']],
                'Ngôi thứ ba số ít dùng does not.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu khẳng định với câu phủ định của nó.',
                [['I like tea.', 'I do not like tea.'],
                 ['She plays tennis.', 'She does not play tennis.']],
                'Phủ định: thêm do/does + not, động từ về nguyên mẫu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu hỏi với câu trả lời ngắn đúng.',
                [['Do you like fish?', 'Yes, I do.'],
                 ['Does he swim well?', 'No, he does not.']],
                'Trả lời ngắn lặp lại trợ động từ do/does.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với nghĩa của nó.',
                [['They do not watch TV.', 'Họ không xem ti-vi.'],
                 ['Does she cook well?', 'Cô ấy nấu ăn có ngon không?']],
                'Do not = không; câu hỏi Does...? để hỏi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm DÙNG DO hoặc DÙNG DOES.',
                [['Do they like music?', 'Dùng do'], ['I do not know.', 'Dùng do'],
                 ['Does she dance well?', 'Dùng does'], ['He does not swim.', 'Dùng does']],
                'Ngôi thứ ba số ít dùng does, các ngôi còn lại dùng do.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI ngữ pháp.',
                [['They do not play chess.', 'Đúng'], ['Do you eat rice?', 'Đúng'],
                 ['She does not likes fish.', 'Sai'], ['Does he goes home?', 'Sai']],
                'Sau does/does not, động từ không thêm s.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU PHỦ ĐỊNH hoặc CÂU NGHI VẤN.',
                [['I do not like rain.', 'Câu phủ định'], ['She does not sing.', 'Câu phủ định'],
                 ['Do you speak English?', 'Câu nghi vấn'], ['Does it rain much?', 'Câu nghi vấn']],
                'Phủ định có not; nghi vấn đảo do/does lên đầu.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'He ___ (not/like) fish.', [[0, 'does not like']],
                'He dùng does not + động từ nguyên mẫu.', 'trung_binh');
            $this->fill($L, '___ you play football? (Bạn có chơi bóng đá không?)', [[0, 'Do']],
                'Câu hỏi với you dùng Do.', 'trung_binh');
            $this->fill($L, 'She does not ___ (go) to school on Sunday.', [[0, 'go']],
                'Sau does not, động từ ở dạng nguyên mẫu.', 'trung_binh');
            $this->fill($L, 'They ___ (not/watch) TV in the morning.', [[0, 'do not watch']],
                'They dùng do not + động từ nguyên mẫu.', 'trung_binh');
        }
    }

    private function seedEnGrammar72(): void
    {
        $L = 'en-ngu-phap-co-ban-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Điền từ đúng: "I ___ reading a book now."',
                ['am', 'is', 'are', 'be'], 0,
                'I đi với am trong thì hiện tại tiếp diễn.', 'trung_binh');
            $this->quiz($L, 'Điền từ đúng: "She ___ playing chess at the moment."',
                ['am', 'is', 'are', 'be'], 1,
                'She đi với is + động từ thêm -ing.', 'trung_binh');
            $this->quiz($L, 'Thì hiện tại tiếp diễn dùng để diễn tả điều gì?',
                ['Thói quen', 'Hành động đang xảy ra lúc nói', 'Sự thật hiển nhiên', 'Hành động đã kết thúc'], 1,
                'Hiện tại tiếp diễn diễn tả hành động đang xảy ra tại thời điểm nói.', 'trung_binh');
            $this->quiz($L, 'Điền cụm từ đúng: "They ___ football now."',
                ['are playing', 'is playing', 'plays', 'play'], 0,
                'They + are + V-ing: are playing.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chủ ngữ với cấu trúc tiếp diễn đúng.',
                [['I', 'am + V-ing'], ['he / she / it', 'is + V-ing'], ['you / we / they', 'are + V-ing']],
                'Công thức: am/is/are + động từ thêm -ing.', 'trung_binh');
            $this->matching($L, 'Nối mỗi động từ với dạng V-ing đúng.',
                [['play', 'playing'], ['swim', 'swimming'],
                 ['write', 'writing'], ['run', 'running']],
                'Swim gấp đôi phụ âm cuối: swimming; write bỏ e: writing.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với nghĩa của nó.',
                [['I am studying now.', 'Bây giờ tôi đang học.'],
                 ['She is cooking dinner.', 'Cô ấy đang nấu bữa tối.']],
                'Now cho biết hành động đang xảy ra.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dấu hiệu với thì của nó.',
                [['now, at the moment', 'Hiện tại tiếp diễn'],
                 ['every day, usually', 'Hiện tại đơn']],
                'Dấu hiệu giúp nhận biết thì của câu.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm HIỆN TẠI ĐƠN hoặc HIỆN TẠI TIẾP DIỄN.',
                [['She plays tennis.', 'Hiện tại đơn'], ['They go to school.', 'Hiện tại đơn'],
                 ['She is playing tennis now.', 'Hiện tại tiếp diễn'], ['They are going home.', 'Hiện tại tiếp diễn']],
                'Tiếp diễn có am/is/are + V-ing và thường có now.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi dạng V-ing vào nhóm ĐÚNG hoặc SAI chính tả.',
                [['playing', 'Đúng'], ['writing', 'Đúng'],
                 ['swiming', 'Sai'], ['runing', 'Sai']],
                'Đúng là swimming, running (gấp đôi phụ âm cuối).', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI ngữ pháp.',
                [['We are eating.', 'Đúng'], ['She is singing.', 'Đúng'],
                 ['He is watch TV.', 'Sai'], ['I am do homework.', 'Sai']],
                'Sau is/am phải là V-ing: is watching, am doing.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Look! The baby ___ (cry).', [[0, 'is crying']],
                'Look! là dấu hiệu tiếp diễn; the baby = it → is crying.', 'trung_binh');
            $this->fill($L, 'I ___ (do) my homework now.', [[0, 'am doing']],
                'I + am + V-ing: am doing.', 'trung_binh');
            $this->fill($L, 'They ___ (play) football at the moment.', [[0, 'are playing']],
                'They + are + V-ing: are playing.', 'trung_binh');
            $this->fill($L, 'Dấu hiệu "now" cho biết câu dùng thì hiện tại tiếp ___.', [[0, 'diễn']],
                'Now, at the moment là dấu hiệu của hiện tại tiếp diễn.', 'trung_binh');
        }
    }

    // ---------- Từ vựng lớp 7 ----------

    private function seedEnVocab771(): void
    {
        $L = 'en-tu-vung-lop-7-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Headache" nghĩa là gì?',
                ['đau bụng', 'đau đầu', 'đau răng', 'cảm lạnh'], 1,
                '"Headache" nghĩa là đau đầu (head + ache).', 'trung_binh');
            $this->quiz($L, '"Fever" nghĩa là gì?',
                ['ho', 'sốt', 'đau họng', 'chóng mặt'], 1,
                '"Fever" nghĩa là sốt.', 'trung_binh');
            $this->quiz($L, 'Từ nào có nghĩa là "bác sĩ"?',
                ['nurse', 'doctor', 'patient', 'dentist'], 1,
                '"Doctor" là bác sĩ; "nurse" là y tá.', 'trung_binh');
            $this->quiz($L, '"Take medicine" nghĩa là gì?',
                ['uống thuốc', 'khám bệnh', 'nghỉ ngơi', 'tập thể dục'], 0,
                '"Take medicine" nghĩa là uống thuốc.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với nghĩa của nó.',
                [['headache', 'đau đầu'], ['stomachache', 'đau bụng'],
                 ['toothache', 'đau răng'], ['backache', 'đau lưng']],
                'Các từ chỉ cơn đau ghép với ache.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ với nghĩa của nó.',
                [['doctor', 'bác sĩ'], ['nurse', 'y tá'],
                 ['hospital', 'bệnh viện'], ['medicine', 'thuốc']],
                'Từ vựng về người và nơi chữa bệnh.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với nghĩa của nó.',
                [['I have a fever.', 'Tôi bị sốt.'],
                 ['You should rest.', 'Bạn nên nghỉ ngơi.'],
                 ['Drink more water.', 'Hãy uống nhiều nước.']],
                'Câu nói về bệnh và lời khuyên sức khỏe.', 'trung_binh');
            $this->matching($L, 'Nối mỗi bộ phận cơ thể với nghĩa của nó.',
                [['head', 'đầu'], ['eye', 'mắt'], ['hand', 'tay'], ['leg', 'chân']],
                'Từ vựng về các bộ phận cơ thể.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm BỆNH hoặc BỘ PHẬN CƠ THỂ.',
                [['fever', 'Bệnh'], ['headache', 'Bệnh'],
                 ['eye', 'Bộ phận cơ thể'], ['hand', 'Bộ phận cơ thể']],
                'Fever, headache là bệnh; eye, hand là bộ phận.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm NGƯỜI hoặc ĐỊA ĐIỂM.',
                [['doctor', 'Người'], ['nurse', 'Người'],
                 ['hospital', 'Địa điểm'], ['pharmacy', 'Địa điểm']],
                'Doctor, nurse là người; hospital, pharmacy (hiệu thuốc) là nơi chốn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi lời khuyên vào nhóm NÊN hoặc KHÔNG NÊN (khi bị ốm).',
                [['rest', 'Nên'], ['drink water', 'Nên'],
                 ['play games late', 'Không nên'], ['eat ice-cream', 'Không nên']],
                'Khi ốm nên nghỉ ngơi, uống nước; không nên thức khuya.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Đau đầu" trong tiếng Anh là ___.', [[0, 'headache']],
                'Headache = đau đầu.', 'trung_binh');
            $this->fill($L, 'I have a ___. (Tôi bị sốt.)', [[0, 'fever']],
                'Fever = sốt.', 'trung_binh');
            $this->fill($L, '"Bệnh viện" trong tiếng Anh là ___.', [[0, 'hospital']],
                'Hospital = bệnh viện.', 'trung_binh');
            $this->fill($L, 'You should ___ medicine and rest. (Bạn nên uống thuốc và nghỉ ngơi.)', [[0, 'take']],
                'Take medicine = uống thuốc.', 'trung_binh');
        }
    }

    private function seedEnVocab772(): void
    {
        $L = 'en-tu-vung-lop-7-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Ticket" nghĩa là gì?',
                ['hành lý', 'vé', 'hộ chiếu', 'khách sạn'], 1,
                '"Ticket" nghĩa là vé (vé xe, vé máy bay...).', 'trung_binh');
            $this->quiz($L, '"Passport" nghĩa là gì?',
                ['vé máy bay', 'hộ chiếu', 'bản đồ', 'vali'], 1,
                '"Passport" nghĩa là hộ chiếu.', 'trung_binh');
            $this->quiz($L, 'Từ nào có nghĩa là "bãi biển"?',
                ['mountain', 'beach', 'river', 'lake'], 1,
                '"Beach" nghĩa là bãi biển.', 'trung_binh');
            $this->quiz($L, '"Souvenir" nghĩa là gì?',
                ['đồ lưu niệm', 'thức ăn', 'quần áo', 'tiền'], 0,
                '"Souvenir" nghĩa là đồ lưu niệm.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với nghĩa của nó.',
                [['ticket', 'vé'], ['passport', 'hộ chiếu'],
                 ['luggage', 'hành lý'], ['map', 'bản đồ']],
                'Những thứ cần chuẩn bị khi đi du lịch.', 'trung_binh');
            $this->matching($L, 'Nối mỗi phương tiện với nghĩa của nó.',
                [['plane', 'máy bay'], ['train', 'tàu hỏa'],
                 ['bus', 'xe buýt'], ['ship', 'tàu thủy']],
                'Các phương tiện giao thông khi đi du lịch.', 'trung_binh');
            $this->matching($L, 'Nối mỗi địa điểm với nghĩa của nó.',
                [['hotel', 'khách sạn'], ['beach', 'bãi biển'],
                 ['museum', 'bảo tàng'], ['market', 'chợ']],
                'Các địa điểm thường ghé thăm khi du lịch.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với nghĩa của nó.',
                [['I want to book a room.', 'Tôi muốn đặt một phòng.'],
                 ['How much is the ticket?', 'Vé giá bao nhiêu?']],
                'Câu giao tiếp khi đi du lịch.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm PHƯƠNG TIỆN hoặc ĐỊA ĐIỂM.',
                [['plane', 'Phương tiện'], ['train', 'Phương tiện'],
                 ['hotel', 'Địa điểm'], ['beach', 'Địa điểm']],
                'Plane, train để di chuyển; hotel, beach là nơi đến.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm TRONG NƯỚC hoặc NGOÀI NƯỚC (với Việt Nam).',
                [['Ha Long Bay', 'Trong nước'], ['Hoi An', 'Trong nước'],
                 ['Eiffel Tower', 'Ngoài nước'], ['Great Wall', 'Ngoài nước']],
                'Ha Long Bay, Hoi An ở Việt Nam; Eiffel Tower ở Pháp.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc ĐỘNG TỪ.',
                [['travel', 'Động từ'], ['visit', 'Động từ'],
                 ['ticket', 'Danh từ'], ['hotel', 'Danh từ']],
                'Travel, visit chỉ hành động; ticket, hotel chỉ sự vật.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Vé" trong tiếng Anh là ___.', [[0, 'ticket']],
                'Ticket = vé.', 'trung_binh');
            $this->fill($L, 'I need a ___ to travel abroad. (Tôi cần hộ chiếu để đi nước ngoài.)', [[0, 'passport']],
                'Passport = hộ chiếu.', 'trung_binh');
            $this->fill($L, '"Khách sạn" trong tiếng Anh là ___.', [[0, 'hotel']],
                'Hotel = khách sạn.', 'trung_binh');
            $this->fill($L, 'We ___ Ha Long Bay last summer. (Chúng tôi đã đi thăm vịnh Hạ Long hè năm ngoái.)', [[0, 'visited']],
                'Last summer là dấu hiệu quá khứ đơn: visit → visited.', 'trung_binh');
        }
    }

    private function seedEnVocab781(): void
    {
        $L = 'en-tu-vung-lop-7-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Pollution" nghĩa là gì?',
                ['ô nhiễm', 'bảo vệ', 'tái chế', 'môi trường'], 0,
                '"Pollution" nghĩa là sự ô nhiễm.', 'trung_binh');
            $this->quiz($L, '"Environment" nghĩa là gì?',
                ['khí hậu', 'môi trường', 'thiên nhiên', 'động vật'], 1,
                '"Environment" nghĩa là môi trường.', 'trung_binh');
            $this->quiz($L, 'Từ nào có nghĩa là "tái chế"?',
                ['reuse', 'recycle', 'reduce', 'protect'], 1,
                '"Recycle" nghĩa là tái chế.', 'trung_binh');
            $this->quiz($L, '"Plant trees" nghĩa là gì?',
                ['chặt cây', 'trồng cây', 'tưới cây', 'hái quả'], 1,
                '"Plant trees" nghĩa là trồng cây.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với nghĩa của nó.',
                [['pollution', 'ô nhiễm'], ['environment', 'môi trường'],
                 ['recycle', 'tái chế'], ['protect', 'bảo vệ']],
                'Từ vựng cốt lõi về môi trường.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cụm từ với nghĩa của nó.',
                [['plant trees', 'trồng cây'], ['save water', 'tiết kiệm nước'],
                 ['clean up', 'dọn dẹp'], ['pick up litter', 'nhặt rác']],
                'Các hành động bảo vệ môi trường.', 'trung_binh');
            $this->matching($L, 'Nối mỗi vấn đề môi trường với giải pháp phù hợp.',
                [['air pollution', 'plant more trees'],
                 ['plastic waste', 'recycle plastic bags'],
                 ['water waste', 'save water']],
                'Mỗi vấn đề môi trường đều có giải pháp tương ứng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ với từ loại của nó.',
                [['pollution', 'danh từ'], ['polluted', 'tính từ'], ['pollute', 'động từ']],
                'Cùng gốc từ nhưng khác từ loại: pollution – polluted – pollute.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm TỐT CHO MÔI TRƯỜNG hoặc CÓ HẠI.',
                [['plant trees', 'Tốt cho môi trường'], ['recycle', 'Tốt cho môi trường'],
                 ['litter', 'Có hại cho môi trường'], ['waste water', 'Có hại cho môi trường']],
                'Trồng cây, tái chế thì tốt; xả rác, lãng phí nước thì có hại.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc ĐỘNG TỪ.',
                [['pollution', 'Danh từ'], ['environment', 'Danh từ'],
                 ['protect', 'Động từ'], ['recycle', 'Động từ']],
                'Pollution, environment là danh từ; protect, recycle là động từ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm VẤN ĐỀ hoặc GIẢI PHÁP.',
                [['air pollution', 'Vấn đề'], ['plastic waste', 'Vấn đề'],
                 ['plant trees', 'Giải pháp'], ['save water', 'Giải pháp']],
                'Nhận diện vấn đề và đề xuất giải pháp.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Ô nhiễm" trong tiếng Anh là ___.', [[0, 'pollution']],
                'Pollution = sự ô nhiễm.', 'trung_binh');
            $this->fill($L, 'We should ___ the environment. (Chúng ta nên bảo vệ môi trường.)', [[0, 'protect']],
                'Protect = bảo vệ; should + động từ nguyên mẫu.', 'trung_binh');
            $this->fill($L, '"Tái chế" trong tiếng Anh là ___.', [[0, 'recycle']],
                'Recycle = tái chế.', 'trung_binh');
            $this->fill($L, 'Do not ___ rubbish on the street. (Đừng xả rác trên đường.)', [[0, 'litter']],
                'Litter vừa là danh từ (rác) vừa là động từ (xả rác).', 'trung_binh');
        }
    }

    private function seedEnVocab782(): void
    {
        $L = 'en-tu-vung-lop-7-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Volunteer" (danh từ) nghĩa là gì?',
                ['tình nguyện viên', 'khách du lịch', 'học sinh', 'giáo viên'], 0,
                '"Volunteer" nghĩa là tình nguyện viên.', 'trung_binh');
            $this->quiz($L, '"Donate" nghĩa là gì?',
                ['quyên góp', 'mua bán', 'vay mượn', 'tiết kiệm'], 0,
                '"Donate" nghĩa là quyên góp, tặng.', 'trung_binh');
            $this->quiz($L, 'Từ nào có nghĩa là "cộng đồng"?',
                ['society', 'community', 'family', 'team'], 1,
                '"Community" nghĩa là cộng đồng.', 'trung_binh');
            $this->quiz($L, '"Charity" nghĩa là gì?',
                ['từ thiện', 'lễ hội', 'cuộc thi', 'buổi họp'], 0,
                '"Charity" nghĩa là từ thiện, lòng nhân ái.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với nghĩa của nó.',
                [['volunteer', 'tình nguyện viên'], ['donate', 'quyên góp'],
                 ['charity', 'từ thiện'], ['community', 'cộng đồng']],
                'Từ vựng về hoạt động xã hội.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hoạt động tình nguyện với nghĩa của nó.',
                [['help the poor', 'giúp người nghèo'],
                 ['clean the park', 'dọn công viên'],
                 ['teach children', 'dạy trẻ em']],
                'Các hoạt động tình nguyện phổ biến.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với nghĩa của nó.',
                [['We raised money for charity.', 'Chúng tôi đã quyên tiền làm từ thiện.'],
                 ['She volunteers at weekends.', 'Cô ấy làm tình nguyện vào cuối tuần.']],
                'Câu kể về hoạt động tình nguyện.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ với từ đồng nghĩa của nó.',
                [['help', 'assist'], ['give', 'donate'], ['group', 'community']],
                'Mở rộng vốn từ bằng từ đồng nghĩa.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm TÌNH NGUYỆN hoặc CÁ NHÂN.',
                [['help the poor', 'Tình nguyện'], ['clean the park', 'Tình nguyện'],
                 ['play games', 'Cá nhân'], ['watch TV', 'Cá nhân']],
                'Hoạt động tình nguyện giúp đỡ cộng đồng.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm chỉ NGƯỜI hoặc chỉ HÀNH ĐỘNG.',
                [['volunteer', 'Người'], ['donor', 'Người'],
                 ['donate', 'Hành động'], ['help', 'Hành động']],
                'Volunteer, donor là người; donate, help là hành động.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm thuộc CỘNG ĐỒNG hoặc thuộc GIA ĐÌNH.',
                [['neighbor', 'Cộng đồng'], ['volunteer', 'Cộng đồng'],
                 ['parents', 'Gia đình'], ['cousin', 'Gia đình']],
                'Neighbor (hàng xóm) thuộc cộng đồng; parents, cousin thuộc gia đình.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '"Tình nguyện viên" trong tiếng Anh là ___.', [[0, 'volunteer']],
                'Volunteer = tình nguyện viên.', 'trung_binh');
            $this->fill($L, 'We ___ books to the village school. (Chúng tôi quyên góp sách cho trường làng.)', [[0, 'donate']],
                'Donate = quyên góp.', 'trung_binh');
            $this->fill($L, '"Cộng đồng" trong tiếng Anh là ___.', [[0, 'community']],
                'Community = cộng đồng.', 'trung_binh');
            $this->fill($L, 'She does ___ work every summer. (Cô ấy làm từ thiện mỗi hè.)', [[0, 'charity']],
                'Charity work = công việc từ thiện.', 'trung_binh');
        }
    }

    /**
     * Bổ sung 1 câu sort cho các bài còn thiếu (đợt seed đầu chỉ tạo 3 câu sort).
     * Idempotent: chỉ thêm khi số câu sort của bài < 4.
     */
    private function seedSortExtra(): void
    {
        $extra = [
            'en-tu-vung-lop-6-lop-6-1' => [
                'Kéo mỗi từ vào nhóm GIA ĐÌNH NHỎ (bố mẹ, anh chị em) hoặc GIA ĐÌNH MỞ RỘNG.',
                [['father', 'Gia đình nhỏ'], ['sister', 'Gia đình nhỏ'],
                 ['grandfather', 'Gia đình mở rộng'], ['aunt', 'Gia đình mở rộng']],
                'Father, sister thuộc gia đình nhỏ; grandfather, aunt thuộc gia đình mở rộng.', 'de'],
            'en-tu-vung-lop-6-lop-6-2' => [
                'Kéo mỗi từ vào nhóm SỐ ÍT hoặc SỐ NHIỀU.',
                [['teacher', 'Số ít'], ['book', 'Số ít'],
                 ['teachers', 'Số nhiều'], ['books', 'Số nhiều']],
                'Danh từ số nhiều thường thêm s: teachers, books.', 'de'],
            'en-tu-vung-lop-6-lop-7-1' => [
                'Kéo mỗi hoạt động vào nhóm TRƯỚC KHI ĐI HỌC hoặc SAU KHI ĐI HỌC.',
                [['get up', 'Trước khi đi học'], ['have breakfast', 'Trước khi đi học'],
                 ['do homework', 'Sau khi đi học'], ['go to bed', 'Sau khi đi học']],
                'Buổi sáng thức dậy và ăn sáng; buổi tối làm bài tập rồi đi ngủ.', 'de'],
            'en-tu-vung-lop-6-lop-7-2' => [
                'Kéo mỗi từ vào nhóm GỢI MÙA NÓNG hoặc GỢI MÙA LẠNH.',
                [['summer', 'Mùa nóng'], ['sunny', 'Mùa nóng'],
                 ['winter', 'Mùa lạnh'], ['snowy', 'Mùa lạnh']],
                'Summer, sunny gợi mùa nóng; winter, snowy gợi mùa lạnh.', 'trung_binh'],
            'en-ngu-phap-co-ban-lop-6-1' => [
                'Kéo mỗi câu vào nhóm CÓ "NOT" hoặc KHÔNG CÓ "NOT".',
                [['I am not late.', 'Có "not"'], ['She is not here.', 'Có "not"'],
                 ['I am fine.', 'Không có "not"'], ['They are happy.', 'Không có "not"']],
                'Câu phủ định có not sau động từ to be.', 'de'],
            'en-ngu-phap-co-ban-lop-6-2' => [
                'Kéo mỗi câu vào nhóm CÓ TRẠNG TỪ CHỈ TẦN SUẤT hoặc KHÔNG CÓ.',
                [['I always get up early.', 'Có trạng từ tần suất'],
                 ['They usually watch TV.', 'Có trạng từ tần suất'],
                 ['She plays tennis.', 'Không có'], ['He swims well.', 'Không có']],
                'Always, usually là trạng từ chỉ tần suất, dấu hiệu của hiện tại đơn.', 'de'],
            'en-ngu-phap-co-ban-lop-7-1' => [
                'Kéo mỗi câu vào nhóm DÙNG "DO" hoặc DÙNG "DOES".',
                [['Do you like music?', 'Dùng "do"'], ['I do not know.', 'Dùng "do"'],
                 ['Does she sing well?', 'Dùng "does"'], ['He does not swim.', 'Dùng "does"']],
                'Ngôi thứ ba số ít dùng does, các ngôi còn lại dùng do.', 'trung_binh'],
            'en-ngu-phap-co-ban-lop-7-2' => [
                'Kéo mỗi từ vào nhóm CÓ ĐUÔI "-ING" hoặc KHÔNG CÓ.',
                [['playing', 'Có "-ing"'], ['swimming', 'Có "-ing"'],
                 ['play', 'Không có "-ing"'], ['swim', 'Không có "-ing"']],
                'Thì tiếp diễn dùng động từ thêm -ing.', 'trung_binh'],
            'en-tu-vung-lop-7-lop-7-1' => [
                'Kéo mỗi từ vào nhóm TRIỆU CHỨNG BỆNH hoặc LỜI KHUYÊN SỨC KHỎE.',
                [['fever', 'Triệu chứng'], ['headache', 'Triệu chứng'],
                 ['rest', 'Lời khuyên'], ['drink water', 'Lời khuyên']],
                'Fever, headache là triệu chứng; rest, drink water là lời khuyên.', 'trung_binh'],
            'en-tu-vung-lop-7-lop-7-2' => [
                'Kéo mỗi phương tiện vào nhóm ĐƯỜNG BỘ hoặc ĐƯỜNG HÀNG KHÔNG / ĐƯỜNG THỦY.',
                [['bus', 'Đường bộ'], ['train', 'Đường bộ'],
                 ['plane', 'Hàng không / đường thủy'], ['ship', 'Hàng không / đường thủy']],
                'Bus, train đi trên đường bộ; plane bay, ship đi trên biển.', 'trung_binh'],
            'en-tu-vung-lop-7-lop-8-1' => [
                'Kéo mỗi từ vào nhóm ĐỘNG TỪ hoặc TÍNH TỪ.',
                [['protect', 'Động từ'], ['recycle', 'Động từ'],
                 ['polluted', 'Tính từ'], ['clean', 'Tính từ']],
                'Protect, recycle chỉ hành động; polluted, clean chỉ tính chất.', 'trung_binh'],
            'en-tu-vung-lop-7-lop-8-2' => [
                'Kéo mỗi từ vào nhóm NGƯỜI GIÚP ĐỠ hoặc NGƯỜI ĐƯỢC GIÚP ĐỠ.',
                [['volunteer', 'Người giúp đỡ'], ['donor', 'Người giúp đỡ'],
                 ['the poor', 'Người được giúp đỡ'], ['children', 'Người được giúp đỡ']],
                'Volunteer, donor là người cho đi; the poor, children là người nhận.', 'trung_binh'],
            'tv-chinh-ta-lop-7-1' => [
                'Kéo mỗi từ vào nhóm VIẾT HOA hoặc KHÔNG VIẾT HOA (khi đứng giữa câu).',
                [['Hà Nội', 'Viết hoa'], ['Nguyễn Du', 'Viết hoa'],
                 ['học sinh', 'Không viết hoa'], ['quyển sách', 'Không viết hoa']],
                'Tên riêng viết hoa mọi vị trí; danh từ chung giữa câu không viết hoa.', 'trung_binh'],
            'tv-chinh-ta-lop-7-2' => [
                'Kéo mỗi câu (chưa có dấu cuối câu) vào nhóm CẦN DẤU CHẤM THAN hoặc CẦN DẤU CHẤM.',
                [['Trời đẹp quá', 'Cần dấu chấm than'], ['Hay quá', 'Cần dấu chấm than'],
                 ['Em đi học', 'Cần dấu chấm'], ['Mẹ đang nấu cơm', 'Cần dấu chấm']],
                'Câu cảm thán bộc lộ cảm xúc đặt dấu chấm than; câu kể đặt dấu chấm.', 'trung_binh'],
            'tv-van-mieu-ta-lop-7-1' => [
                'Kéo mỗi từ láy vào nhóm GỢI HÌNH ẢNH hoặc GỢI ÂM THANH.',
                [['lung linh', 'Gợi hình ảnh'], ['mấp mô', 'Gợi hình ảnh'],
                 ['rì rào', 'Gợi âm thanh'], ['ầm ầm', 'Gợi âm thanh']],
                'Lung linh, mấp mô gợi hình; rì rào, ầm ầm gợi âm thanh.', 'de'],
            'tv-van-mieu-ta-lop-7-2' => [
                'Kéo mỗi câu vào nhóm TẢ HOẠT ĐỘNG hoặc TẢ NGOẠI HÌNH.',
                [['Mẹ đang cấy lúa thoăn thoắt.', 'Tả hoạt động'],
                 ['Bố đang cày ruộng.', 'Tả hoạt động'],
                 ['Tóc bà bạc trắng như cước.', 'Tả ngoại hình'],
                 ['Dáng mẹ thon thả.', 'Tả ngoại hình']],
                'Tả hoạt động nêu việc làm; tả ngoại hình nêu nét nhìn thấy được.', 'trung_binh'],
            'tv-van-mieu-ta-lop-8-1' => [
                'Kéo mỗi chi tiết vào nhóm TẢ MÙA XUÂN hoặc TẢ MÙA ĐÔNG.',
                [['Hoa đào nở rộ.', 'Tả mùa xuân'], ['Mưa xuân lất phất.', 'Tả mùa xuân'],
                 ['Gió bấc lạnh buốt.', 'Tả mùa đông'], ['Sương muối trắng xóa.', 'Tả mùa đông']],
                'Chi tiết phải phù hợp với mùa được tả.', 'trung_binh'],
            'tv-van-mieu-ta-lop-8-2' => [
                'Kéo mỗi từ vào nhóm GẦN NGHĨA VỚI "ĐẸP" hoặc KHÔNG GẦN NGHĨA.',
                [['xinh', 'Gần nghĩa với "đẹp"'], ['duyên dáng', 'Gần nghĩa với "đẹp"'],
                 ['xấu xí', 'Không gần nghĩa'], ['cao lớn', 'Không gần nghĩa']],
                'Dùng từ đồng nghĩa, gần nghĩa để tránh lặp từ khi viết văn.', 'trung_binh'],
            'tv-tu-va-cau-lop-7-2' => [
                'Kéo mỗi cụm từ vào nhóm CHỦ NGỮ hoặc VỊ NGỮ.',
                [['Mẹ em', 'Chủ ngữ'], ['Chim én', 'Chủ ngữ'],
                 ['đang nấu cơm', 'Vị ngữ'], ['bay về tổ', 'Vị ngữ']],
                'Chủ ngữ nêu đối tượng; vị ngữ nêu hoạt động, đặc điểm.', 'trung_binh'],
            'tv-chinh-ta-lop-6-1' => [
                'Kéo mỗi từ vào nhóm VIẾT VỚI GI hoặc VIẾT VỚI D.',
                [['giờ giấc', 'Viết với gi'], ['gia đình', 'Viết với gi'],
                 ['dịu dàng', 'Viết với d'], ['dũng cảm', 'Viết với d']],
                'Giờ giấc, gia đình viết gi; dịu dàng, dũng cảm viết d.', 'de'],
            'tv-chinh-ta-lop-6-2' => [
                'Kéo mỗi từ (theo nghĩa đã cho) vào nhóm DẤU HỎI hoặc DẤU NGÃ.',
                [['củ (khoai)', 'Dấu hỏi'], ['nghỉ (học)', 'Dấu hỏi'],
                 ['cũ (đồ vật)', 'Dấu ngã'], ['ngã (ba đường)', 'Dấu ngã']],
                'Củ, nghỉ dấu hỏi; cũ, ngã dấu ngã.', 'de'],
            'tv-tu-va-cau-lop-6-1' => [
                'Kéo mỗi từ vào nhóm TỪ ĐƠN hoặc TỪ LÁY.',
                [['cây', 'Từ đơn'], ['nhà', 'Từ đơn'],
                 ['xinh xắn', 'Từ láy'], ['rì rào', 'Từ láy']],
                'Từ đơn chỉ có một tiếng; từ láy láy lại âm hoặc vần.', 'de'],
            'tv-tu-va-cau-lop-6-2' => [
                'Kéo mỗi từ vào nhóm DANH TỪ CHUNG hoặc DANH TỪ RIÊNG.',
                [['học sinh', 'Danh từ chung'], ['bàn ghế', 'Danh từ chung'],
                 ['Hà Nội', 'Danh từ riêng'], ['Nguyễn Du', 'Danh từ riêng']],
                'Danh từ riêng chỉ tên riêng cụ thể, viết hoa chữ cái đầu.', 'de'],
            'tv-tu-va-cau-lop-7-1' => [
                'Kéo mỗi cụm từ vào nhóm CỤM TÍNH TỪ hoặc CỤM DANH TỪ.',
                [['rất đẹp', 'Cụm tính từ'], ['hơi buồn', 'Cụm tính từ'],
                 ['những bông hoa', 'Cụm danh từ'], ['các bạn nhỏ', 'Cụm danh từ']],
                'Xem từ trung tâm là tính từ hay danh từ.', 'trung_binh'],
            'toan-hinh-hoc-phang-lop-7-2' => [
                'Kéo mỗi cặp góc vào nhóm BÙ NHAU (tổng 180°) hoặc PHỤ NHAU (tổng 90°).',
                [['60° và 120°', 'Bù nhau'], ['45° và 135°', 'Bù nhau'],
                 ['30° và 60°', 'Phụ nhau'], ['20° và 70°', 'Phụ nhau']],
                'Bù nhau: tổng 180°; phụ nhau: tổng 90°.', 'trung_binh'],
            'toan-hinh-hoc-phang-lop-8-1' => [
                'Kéo mỗi tam giác vào nhóm TAM GIÁC ĐỀU hoặc TAM GIÁC CÂN (không đều).',
                [['Ba cạnh bằng nhau', 'Tam giác đều'], ['Ba góc bằng 60°', 'Tam giác đều'],
                 ['Hai cạnh bằng nhau, góc đỉnh khác 60°', 'Tam giác cân'],
                 ['Hai góc ở đáy bằng nhau (khác 60°)', 'Tam giác cân']],
                'Tam giác đều là trường hợp đặc biệt của tam giác cân.', 'trung_binh'],
            'toan-hinh-hoc-phang-lop-8-2' => [
                'Kéo mỗi khẳng định về tam giác đồng dạng vào nhóm ĐÚNG hoặc SAI.',
                [['Các góc tương ứng bằng nhau.', 'Đúng'],
                 ['Tỉ số chu vi bằng tỉ số đồng dạng.', 'Đúng'],
                 ['Mọi tam giác đều đều đồng dạng với nhau.', 'Đúng'],
                 ['Hai tam giác đồng dạng thì diện tích bằng nhau.', 'Sai']],
                'Diện tích tỉ lệ với bình phương tỉ số đồng dạng, chỉ bằng nhau khi k = 1.', 'trung_binh'],
            'toan-bieu-thuc-dai-so-lop-8-1' => [
                'Kéo mỗi đa thức vào nhóm ĐÃ THU GỌN hoặc CHƯA THU GỌN.',
                [['2x² + 3x', 'Đã thu gọn'], ['x³ − 1', 'Đã thu gọn'],
                 ['x² + x² + x', 'Chưa thu gọn'], ['3y + 2y² + y', 'Chưa thu gọn']],
                'Còn hạng tử đồng dạng chưa gộp là chưa thu gọn.', 'trung_binh'],
            'toan-bieu-thuc-dai-so-lop-8-2' => [
                'Kéo mỗi phép nhân vào nhóm KẾT QUẢ ĐÚNG hoặc SAI.',
                [['3x × 2x = 6x²', 'Đúng'], ['x(x + 2) = x² + 2x', 'Đúng'],
                 ['2x × 3y = 5xy', 'Sai'], ['(x + 1)² = x² + 1', 'Sai']],
                '2x × 3y = 6xy; (x + 1)² = x² + 2x + 1.', 'trung_binh'],
            'toan-hinh-hoc-phang-lop-7-1' => [
                'Kéo mỗi mô tả vào nhóm GÓC VUÔNG hoặc GÓC BẸT.',
                [['Góc có số đo 90°', 'Góc vuông'],
                 ['Góc tạo bởi hai tia vuông góc', 'Góc vuông'],
                 ['Góc có số đo 180°', 'Góc bẹt'],
                 ['Góc tạo bởi hai tia đối nhau', 'Góc bẹt']],
                'Vuông góc tạo góc 90°; hai tia đối nhau tạo góc bẹt 180°.', 'de'],
            'toan-phan-so-lop-7-2' => [
                'Kéo mỗi biểu thức vào nhóm CÓ GIÁ TRỊ BẰNG 2 hoặc KHÁC 2.',
                [['(1/2 + 1/2) × 2', 'Bằng 2'], ['4/3 : 2/3', 'Bằng 2'],
                 ['1/2 + 1/4', 'Khác 2'], ['3/4 × 2', 'Khác 2']],
                '(1/2 + 1/2) × 2 = 2; 4/3 : 2/3 = 2; 1/2 + 1/4 = 3/4; 3/4 × 2 = 3/2.', 'trung_binh'],
            'toan-bieu-thuc-dai-so-lop-7-1' => [
                'Kéo mỗi biểu thức (tại x = 3) vào nhóm CÓ GIÁ TRỊ BẰNG 10 hoặc KHÁC 10.',
                [['x + 7', 'Bằng 10'], ['2x + 4', 'Bằng 10'],
                 ['3x', 'Khác 10'], ['x²', 'Khác 10']],
                'Thay x = 3: 3 + 7 = 10; 6 + 4 = 10; 3 × 3 = 9; 3² = 9.', 'de'],
            'toan-bieu-thuc-dai-so-lop-7-2' => [
                'Kéo mỗi đơn thức vào nhóm ĐỒNG DẠNG VỚI x² hoặc KHÔNG ĐỒNG DẠNG.',
                [['5x²', 'Đồng dạng'], ['−3x²', 'Đồng dạng'],
                 ['5x', 'Không đồng dạng'], ['2xy', 'Không đồng dạng']],
                'Đồng dạng với x² nghĩa là có cùng phần biến x².', 'trung_binh'],
            'toan-phan-so-lop-6-1' => [
                'Kéo mỗi phép tính vào nhóm KẾT QUẢ BẰNG 1/2 hoặc KHÁC 1/2.',
                [['1/4 + 1/4', 'Bằng 1/2'], ['3/4 − 1/4', 'Bằng 1/2'],
                 ['1/3 + 1/3', 'Khác 1/2'], ['2/5 + 1/5', 'Khác 1/2']],
                '1/4 + 1/4 = 2/4 = 1/2; 3/4 − 1/4 = 2/4 = 1/2; 1/3 + 1/3 = 2/3; 2/5 + 1/5 = 3/5.', 'de'],
            'toan-phan-so-lop-6-2' => [
                'Kéo mỗi phân số vào nhóm RÚT GỌN ĐƯỢC THÀNH 1/2 hoặc KHÔNG.',
                [['2/4', 'Được 1/2'], ['3/6', 'Được 1/2'],
                 ['2/3', 'Không'], ['3/5', 'Không']],
                '2/4 = 1/2; 3/6 = 1/2; 2/3 và 3/5 đã tối giản, khác 1/2.', 'de'],
            'toan-phan-so-lop-7-1' => [
                'Kéo mỗi phép tính vào nhóm KẾT QUẢ LÀ SỐ NGUYÊN hoặc KHÔNG PHẢI SỐ NGUYÊN.',
                [['2/3 : 2/3', 'Số nguyên'], ['3/4 × 4/3', 'Số nguyên'],
                 ['1/2 × 2/3', 'Không phải số nguyên'], ['3/5 : 3', 'Không phải số nguyên']],
                '2/3 : 2/3 = 1; 3/4 × 4/3 = 1; 1/2 × 2/3 = 1/3; 3/5 : 3 = 1/5.', 'trung_binh'],
            'toan-so-tu-nhien-lop-6-1' => [
                'Kéo mỗi số vào nhóm CHIA HẾT CHO 10 hoặc KHÔNG CHIA HẾT CHO 10.',
                [['100', 'Chia hết cho 10'], ['250', 'Chia hết cho 10'],
                 ['125', 'Không chia hết cho 10'], ['37', 'Không chia hết cho 10']],
                'Số chia hết cho 10 có chữ số tận cùng là 0.', 'de'],
            'toan-so-tu-nhien-lop-6-2' => [
                'Kéo mỗi biểu thức vào nhóm tính đúng thứ tự ưu tiên.',
                [['5 + 3 × 2: tính nhân trước', 'Đúng thứ tự'],
                 ['20 − 12 : 4: tính chia trước', 'Đúng thứ tự'],
                 ['(5 + 3) × 2: tính cộng trước vì có ngoặc', 'Đúng thứ tự'],
                 ['(20 − 12) : 4: tính nhân trước', 'Sai thứ tự']],
                'Có ngoặc thì tính trong ngoặc trước; không có ngoặc thì nhân – chia trước.', 'de'],
            'toan-so-tu-nhien-lop-7-1' => [
                'Kéo mỗi lũy thừa vào nhóm CÓ GIÁ TRỊ BẰNG 64 hoặc KHÁC 64.',
                [['2⁶', 'Bằng 64'], ['4³', 'Bằng 64'], ['8²', 'Bằng 64'], ['2⁵', 'Khác 64']],
                '2⁶ = 64; 4³ = 64; 8² = 64; 2⁵ = 32.', 'trung_binh'],
            'toan-so-tu-nhien-lop-7-2' => [
                'Kéo mỗi biểu thức vào nhóm CÓ GIÁ TRỊ BẰNG 1 000 hoặc KHÁC 1 000.',
                [['10³', 'Bằng 1 000'], ['(2 × 5)³', 'Bằng 1 000'],
                 ['2¹⁰ = 1 024', 'Khác 1 000'], ['5⁴ = 625', 'Khác 1 000']],
                '10³ = 1 000; (2 × 5)³ = 10³ = 1 000; 2¹⁰ = 1 024; 5⁴ = 625.', 'trung_binh'],
        ];
        foreach ($extra as $slug => [$prompt, $items, $explanation, $difficulty]) {
            $lesson = $this->lesson($slug);
            $sortCount = Question::where('lesson_id', $lesson->id)->where('game_type', 'sort')->count();
            if ($sortCount < 4) {
                $this->sortQ($slug, $prompt, $items, $explanation, $difficulty);
            }
        }
    }
}
