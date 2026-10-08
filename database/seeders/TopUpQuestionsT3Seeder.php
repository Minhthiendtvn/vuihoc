<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Vá câu hỏi còn thiếu nhóm T3 (Tiếng Anh + Tiếng Việt + Trải nghiệm & Hướng nghiệp + Tin học + GDTC).
 *
 * 45 combo bài×kiểu, 100 câu mới. Nội dung tự viết 100%, bám topic + khối lớp + độ khó,
 * prompt khác hẳn các prompt đã có.
 * Idempotent: guard bỏ qua khi đã đủ 8 câu/kiểu hoặc prompt trùng.
 */
class TopUpQuestionsT3Seeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $countCache = [];

    private function lesson(string $slug): \App\Models\Lesson {
        if (!isset($this->lessonBySlug[$slug])) {
            $this->lessonBySlug[$slug] = \App\Models\Lesson::where('slug', $slug)->firstOrFail();
        }
        return $this->lessonBySlug[$slug];
    }
    private function qCount(string $lessonSlug, string $type): int {
        $key = $lessonSlug . ':' . $type;
        if (!isset($this->countCache[$key])) {
            $this->countCache[$key] = \App\Models\Question::where('lesson_id', $this->lesson($lessonSlug)->id)->where('game_type', $type)->count();
        }
        return $this->countCache[$key];
    }
    private function bumpCount(string $lessonSlug, string $type): void { $this->countCache[$lessonSlug . ':' . $type]++; }
    private function promptExists(string $lessonSlug, string $type, string $prompt): bool {
        return \App\Models\Question::where('lesson_id', $this->lesson($lessonSlug)->id)->where('game_type', $type)->where('prompt', $prompt)->exists();
    }
    private function newQuestion(string $lessonSlug, string $gameType, string $prompt, string $explanation, string $difficulty = 'de'): \App\Models\Question {
        $lesson = $this->lesson($lessonSlug);
        if (!isset($this->orderByLesson[$lessonSlug])) {
            $this->orderByLesson[$lessonSlug] = (int) \App\Models\Question::where('lesson_id', $lesson->id)->max('sort_order');
        }
        $this->orderByLesson[$lessonSlug]++;
        return \App\Models\Question::create([
            'lesson_id' => $lesson->id, 'game_type' => $gameType, 'prompt' => $prompt,
            'explanation' => $explanation, 'difficulty' => $difficulty, 'points' => 10,
            'sort_order' => $this->orderByLesson[$lessonSlug],
            'grade' => $lesson->grade, 'is_demo' => true,
        ]);
    }
    private function quiz(string $lesson, string $prompt, array $options, int $correct, string $explanation, string $difficulty = 'de'): void {
        if ($this->qCount($lesson, 'quiz') >= 8 || $this->promptExists($lesson, 'quiz', $prompt)) return;
        $q = $this->newQuestion($lesson, 'quiz', $prompt, $explanation, $difficulty);
        foreach ($options as $i => $text) { \App\Models\QuestionOption::create(['question_id' => $q->id, 'option_text' => $text, 'is_correct' => $i === $correct, 'sort_order' => $i + 1]); }
        $this->bumpCount($lesson, 'quiz');
    }
    private function matching(string $lesson, string $prompt, array $pairs, string $explanation, string $difficulty = 'de'): void {
        if ($this->qCount($lesson, 'matching') >= 8 || $this->promptExists($lesson, 'matching', $prompt)) return;
        $q = $this->newQuestion($lesson, 'matching', $prompt, $explanation, $difficulty);
        foreach ($pairs as $i => [$left, $right]) { \App\Models\MatchingPair::create(['question_id' => $q->id, 'left_text' => $left, 'right_text' => $right, 'sort_order' => $i + 1]); }
        $this->bumpCount($lesson, 'matching');
    }
    private function sortQ(string $lesson, string $prompt, array $items, string $explanation, string $difficulty = 'de'): void {
        if ($this->qCount($lesson, 'sort') >= 8 || $this->promptExists($lesson, 'sort', $prompt)) return;
        $q = $this->newQuestion($lesson, 'sort', $prompt, $explanation, $difficulty);
        foreach ($items as $i => [$text, $category]) { \App\Models\SortItem::create(['question_id' => $q->id, 'item_text' => $text, 'category' => $category, 'sort_order' => $i + 1]); }
        $this->bumpCount($lesson, 'sort');
    }
    private function fill(string $lesson, string $prompt, array $answers, string $explanation, string $difficulty = 'de'): void {
        if ($this->qCount($lesson, 'fill') >= 8 || $this->promptExists($lesson, 'fill', $prompt)) return;
        if (!str_contains($prompt, '___')) return;
        $q = $this->newQuestion($lesson, 'fill', $prompt, $explanation, $difficulty);
        foreach ($answers as $i => [$blankIndex, $text]) { \App\Models\FillAnswer::create(['question_id' => $q->id, 'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1]); }
        $this->bumpCount($lesson, 'fill');
    }

    public function run(): void
    {
        $this->seedT300();
        $this->seedT301();
        $this->seedT302();
        $this->seedT303();
        $this->seedT304();
        $this->seedT305();
        $this->seedT306();
        $this->seedT307();
        $this->seedT308();
        $this->seedT309();
        $this->seedT310();
        $this->seedT311();
        $this->seedT312();
        $this->seedT313();
        $this->seedT314();
        $this->seedT315();
        $this->seedT316();
        $this->seedT317();
        $this->seedT318();
        $this->seedT319();
        $this->seedT320();
        $this->seedT321();
        $this->seedT322();
        $this->seedT323();
        $this->seedT324();
        $this->seedT325();
        $this->seedT326();
        $this->seedT327();
        $this->seedT328();
        $this->seedT329();
        $this->seedT330();
        $this->seedT331();
        $this->seedT332();
        $this->seedT333();
        $this->seedT334();
        $this->seedT335();
        $this->seedT336();
        $this->seedT337();
        $this->seedT338();
        $this->seedT339();
        $this->seedT340();
        $this->seedT341();
        $this->seedT342();
        $this->seedT343();
        $this->seedT344();
    }

    private function seedT300(): void
    {
        $s = 'tv-cac-tu-loai';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DANH TỪ CHỈ NGƯỜI hoặc DANH TỪ CHỈ VẬT.', [['thầy giáo', 'DANH TỪ CHỈ NGƯỜI'], ['bác sĩ', 'DANH TỪ CHỈ NGƯỜI'], ['cái bàn', 'DANH TỪ CHỈ VẬT'], ['dòng sông', 'DANH TỪ CHỈ VẬT']], 'Danh từ chỉ người nêu tên người, danh từ chỉ vật nêu tên đồ vật hoặc cảnh vật.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm ĐỘNG TỪ CHỈ HOẠT ĐỘNG hoặc TÍNH TỪ CHỈ MÀU SẮC.', [['chạy', 'ĐỘNG TỪ CHỈ HOẠT ĐỘNG'], ['bơi', 'ĐỘNG TỪ CHỈ HOẠT ĐỘNG'], ['xanh', 'TÍNH TỪ CHỈ MÀU SẮC'], ['đỏ', 'TÍNH TỪ CHỈ MÀU SẮC']], 'Động từ chỉ hoạt động của người, vật; tính từ chỉ màu sắc nêu đặc điểm nhìn thấy được.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ CHỈ SỐ LƯỢNG hoặc TỪ CHỈ THỜI GIAN.', [['hai', 'TỪ CHỈ SỐ LƯỢNG'], ['nhiều', 'TỪ CHỈ SỐ LƯỢNG'], ['sáng nay', 'TỪ CHỈ THỜI GIAN'], ['hôm qua', 'TỪ CHỈ THỜI GIAN']], 'Từ chỉ số lượng trả lời câu hỏi "bao nhiêu", từ chỉ thời gian trả lời câu hỏi "khi nào".', $d);
    }

    private function seedT301(): void
    {
        $s = 'tv-cac-tu-loai';
        $d = 'de';

        $this->fill($s, 'Từ chỉ hoạt động, trạng thái của sự vật gọi là động ___.', [[0, 'từ']], 'Động từ là từ loại chỉ hoạt động, trạng thái của người, vật, hiện tượng.', $d);
        $this->fill($s, 'Trong câu "Hoa ___ rất thơm.", điền động từ phù hợp chỉ hoạt động của hoa.', [[0, 'nở']], 'Hoa "nở" là động từ chỉ hoạt động của hoa, điền vào chỗ trống hợp nghĩa nhất.', $d);
        $this->fill($s, 'Từ "nhanh nhẹn" chỉ tính chất của con người nên thuộc từ loại tính ___.', [[0, 'từ']], 'Tính từ là từ loại chỉ đặc điểm, tính chất của sự vật, con người.', $d);
    }

    private function seedT302(): void
    {
        $s = 'tv-cau-don';
        $d = 'trung_binh';

        $this->quiz($s, 'Trong câu "Những chú chim đang hót líu lo trên cây.", chủ ngữ là gì?', ['Những chú chim', 'đang hót líu lo', 'trên cây', 'líu lo'], 0, 'Chủ ngữ trả lời câu hỏi "ai / con gì", ở đây là "Những chú chim".', $d);
        $this->quiz($s, 'Trong câu "Mưa rào bất chợt đổ xuống.", vị ngữ là gì?', ['bất chợt đổ xuống', 'Mưa rào', 'đổ xuống ào ào', 'bất chợt'], 0, 'Vị ngữ trả lời câu hỏi "làm gì / thế nào", ở đây là "bất chợt đổ xuống".', $d);
        $this->quiz($s, 'Câu nào sau đây là câu đơn?', ['Hoa nở rộ trong vườn.', 'Trời nắng, chim hót vang.', 'Mẹ đi chợ, bố ở nhà.', 'Em học bài và em chơi game.'], 0, 'Câu đơn chỉ có một cụm chủ ngữ – vị ngữ; các câu còn lại có hai cụm nên là câu ghép.', $d);
    }

    private function seedT303(): void
    {
        $s = 'tv-cau-don';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi câu với chủ ngữ của nó (bộ 2).', [['Đàn ong chăm chỉ bay đi hút mật.', 'Đàn ong chăm chỉ'], ['Bà em kể chuyện cổ tích.', 'Bà em'], ['Mặt trời lên cao.', 'Mặt trời'], ['Tiếng trống trường vang xa.', 'Tiếng trống trường']], 'Chủ ngữ là thành phần nêu đối tượng được nói đến trong câu, trả lời câu hỏi "ai / cái gì / con gì".', $d);
        $this->matching($s, 'Nối mỗi câu với vị ngữ của nó (bộ 2).', [['Cánh đồng lúa chín vàng.', 'chín vàng'], ['Em đang làm bài tập.', 'đang làm bài tập'], ['Chú mèo nằm ngủ.', 'nằm ngủ'], ['Lá cờ tung bay.', 'tung bay']], 'Vị ngữ nêu hoạt động, trạng thái của chủ ngữ, trả lời câu hỏi "làm gì / thế nào / ra sao".', $d);
        $this->matching($s, 'Nối mỗi câu với trạng ngữ của nó.', [['Buổi chiều, các bạn đá bóng.', 'Buổi chiều'], ['Trên cành cây, chim hót líu lo.', 'Trên cành cây'], ['Nhờ chăm chỉ, em học giỏi.', 'Nhờ chăm chỉ'], ['Ngoài sân, hoa nở rộ.', 'Ngoài sân']], 'Trạng ngữ bổ sung ý nghĩa thời gian, nơi chốn, nguyên nhân cho câu.', $d);
    }

    private function seedT304(): void
    {
        $s = 'tv-dau-hoi-dau-nga';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DẤU HỎI hoặc DẤU NGÃ (bộ 2).', [['mở', 'DẤU HỎI'], ['hỏi', 'DẤU HỎI'], ['mỡ', 'DẤU NGÃ'], ['ngã', 'DẤU NGÃ']], '"Mở, hỏi" viết với dấu hỏi; "mỡ, ngã" viết với dấu ngã — nhớ mặt chữ để không nhầm.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm VIẾT ĐÚNG hoặc VIẾT SAI (bộ 2).', [['nghỉ', 'VIẾT ĐÚNG'], ['mỏi', 'VIẾT ĐÚNG'], ['ngỉ', 'VIẾT SAI'], ['mõi', 'VIẾT SAI']], '"Nghỉ, mỏi" mới viết đúng; "ngỉ, mõi" là cách viết sai dấu cần tránh.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DẤU HỎI hoặc DẤU NGÃ theo nghĩa đã cho.', [['mở cửa', 'DẤU HỎI'], ['hỏi thăm', 'DẤU HỎI'], ['mỡ màng', 'DẤU NGÃ'], ['nghĩ ngợi', 'DẤU NGÃ']], 'Hiểu nghĩa của từ giúp chọn đúng dấu: "mở cửa, hỏi thăm" dấu hỏi; "mỡ màng, nghĩ ngợi" dấu ngã.', $d);
    }

    private function seedT305(): void
    {
        $s = 'tv-dau-hoi-dau-nga';
        $d = 'de';

        $this->fill($s, '"Bé ___ tay mẹ đi chợ." (nghĩa là nắm lấy): điền tiếng đúng dấu ngã.', [[0, 'nắm']], '"Nắm tay" viết với dấu ngã; đừng nhầm thành "nắm" với dấu hỏi là sai.', $d);
        $this->fill($s, '"Đứa ___ xinh xắn." (nhỏ, dễ thương): điền tiếng đúng dấu hỏi.', [[0, 'nhỏ']], '"Nhỏ" viết với dấu hỏi; nghĩa là bé, xinh xắn.', $d);
        $this->fill($s, '"___ vở bài tập về nhà thật cẩn thận." (nghĩa là ghi bài vào vở): điền tiếng đúng dấu hỏi.', [[0, 'Chép']], '"Chép bài" viết với dấu hỏi; chép cẩn thận giúp vở sạch đẹp.', $d);
    }

    private function seedT306(): void
    {
        $s = 'tv-am-dau';
        $d = 'de';

        $this->quiz($s, 'Từ nào viết đúng với âm đầu "ch"?', ['chăm chỉ', 'trăm chỉ', 'dăm chỉ', 'răm chỉ'], 0, '"Chăm chỉ" viết với "ch"; các cách viết còn lại đều sai chính tả.', $d);
        $this->quiz($s, 'Từ nào viết đúng với âm đầu "tr"?', ['chồng cây', 'trồng cây', 'dồng cây', 'rồng cây'], 1, '"Trồng cây" viết với "tr"; nghĩa là cho cây xuống đất để cây lớn lên.', $d);
        $this->quiz($s, 'Âm đầu của từ "giảng bài" là gì?', ['gi', 'd', 'r', 'tr'], 0, '"Giảng" viết với "gi"; "giảng bài" nghĩa là dạy bài cho học trò.', $d);
    }

    private function seedT307(): void
    {
        $s = 'tv-am-dau';
        $d = 'de';

        $this->matching($s, 'Nối mỗi từ với âm đầu của nó (bộ 2).', [['chú chim', 'ch'], ['trái bưởi', 'tr'], ['dòng sông', 'd'], ['giọt sương', 'gi']], 'Nhớ mặt chữ: "chim" viết ch, "trái" viết tr, "dòng" viết d, "giọt" viết gi.', $d);
        $this->matching($s, 'Nối mỗi từ với nghĩa của nó.', [['rì rào', 'Tiếng gió thổi'], ['giảng bài', 'Dạy học trò'], ['trồng cây', 'Cho cây xuống đất'], ['dòng sông', 'Nước chảy dài']], 'Hiểu nghĩa của từ giúp viết đúng âm đầu và dùng từ chính xác.', $d);
        $this->matching($s, 'Nối mỗi cặp từ dễ nhầm với cách viết đúng.', [['"dành dụm" hay "giành dụm"?', 'dành dụm'], ['"trở về" hay "chở về"?', 'trở về'], ['"rực rỡ" hay "dực dỡ"?', 'rực rỡ'], ['"dạy học" hay "giạy học"?', 'dạy học']], 'Các cặp từ dễ nhầm âm đầu cần học thuộc mặt chữ đúng.', $d);
    }

    private function seedT308(): void
    {
        $s = 'tv-bai-van-mieu-ta';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm TẢ NGƯỜI hoặc TẢ CẢNH.', [['Mẹ em có mái tóc dài đen nhánh.', 'TẢ NGƯỜI'], ['Bà em cười hiền hậu.', 'TẢ NGƯỜI'], ['Buổi sáng, cánh đồng phủ sương trắng.', 'TẢ CẢNH'], ['Dòng sông uốn lượn quanh làng.', 'TẢ CẢNH']], 'Tả người tập trung vào hình dáng, cử chỉ; tả cảnh tập trung vào khung cảnh thiên nhiên.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm TẢ BAO QUÁT hoặc TẢ CHI TIẾT.', [['Khu vườn rộng và xanh tốt.', 'TẢ BAO QUÁT'], ['Nhìn từ xa, ngôi trường như bức tranh.', 'TẢ BAO QUÁT'], ['Những cánh hoa hồng đỏ thắm còn đọng sương.', 'TẢ CHI TIẾT'], ['Tiếng chim hót véo von trên cành.', 'TẢ CHI TIẾT']], 'Tả bao quát nêu cái nhìn chung, tả chi tiết đi sâu vào từng đặc điểm cụ thể.', $d);
        $this->sortQ($s, 'Kéo mỗi cách viết vào nhóm SINH ĐỘNG hoặc KHÔ KHAN.', [['"Nắng vàng óng như mật ong rót xuống sân."', 'SINH ĐỘNG'], ['"Bà cười, nếp nhăn xô lại như sóng."', 'SINH ĐỘNG'], ['"Trời nắng."', 'KHÔ KHAN'], ['"Có một cái cây."', 'KHÔ KHAN']], 'Cách viết sinh động dùng hình ảnh so sánh và từ ngữ gợi cảm; cách viết khô khan chỉ nêu sự việc.', $d);
    }

    private function seedT309(): void
    {
        $s = 'tv-bai-van-mieu-ta';
        $d = 'trung_binh';

        $this->fill($s, 'Mở bài gián tiếp là dẫn dắt từ câu chuyện khác rồi mới giới thiệu ___ tả.', [[0, 'đối tượng']], 'Mở bài gián tiếp không vào thẳng đối tượng mà dẫn dắt vòng vo rồi mới giới thiệu đối tượng tả.', $d);
        $this->fill($s, 'Khi tả cảnh, nên tả theo trình tự không gian: từ ___ tới gần.', [[0, 'xa']], 'Trình tự từ xa tới gần giúp người đọc hình dung khung cảnh một cách có lớp lang.', $d);
        $this->fill($s, 'Kết bài mở rộng nêu cảm nghĩ, ___ ước của người viết về đối tượng.', [[0, 'mong']], 'Kết bài mở rộng không chỉ tả mà còn bày tỏ tình cảm, mong ước với đối tượng.', $d);
    }

    private function seedT310(): void
    {
        $s = 'tv-bien-phap-tu-tu';
        $d = 'trung_binh';

        $this->quiz($s, 'Câu "Trăng tròn như cái đĩa bạc." dùng biện pháp tu từ nào?', ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Điệp ngữ'], 0, 'Câu có từ "như" so sánh vầng trăng với cái đĩa bạc nên dùng biện pháp so sánh.', $d);
        $this->quiz($s, 'Câu "Hoa cười trong nắng sớm." dùng biện pháp tu từ nào?', ['Nhân hoá', 'So sánh', 'Hoán dụ', 'Nói quá'], 0, 'Gán cho hoa hành động "cười" của con người nên đây là biện pháp nhân hoá.', $d);
        $this->quiz($s, 'Câu nào dùng biện pháp nhân hoá?', ['Mây trắng bồng bềnh trôi.', 'Gió thì thầm bên tai em.', 'Nước sông trong vắt.', 'Trời xanh ngắt.'], 1, '"Gió thì thầm" gán cho gió hành động nói của con người nên là nhân hoá.', $d);
    }

    private function seedT311(): void
    {
        $s = 'tv-bien-phap-tu-tu';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi câu với biện pháp tu từ của nó (bộ 2).', [['"Mẹ là ngọn lửa ấm."', 'Ẩn dụ'], ['"Ve kêu râm ran như dàn đồng ca."', 'So sánh'], ['"Ông mặt trời mỉm cười."', 'Nhân hoá'], ['"Nắng vàng như rót mật."', 'So sánh']], 'Nhận diện biện pháp qua dấu hiệu: "như" là so sánh, "là" là ẩn dụ, gán hành động người cho vật là nhân hoá.', $d);
        $this->matching($s, 'Nối mỗi từ ngữ với biện pháp tu từ nó tạo nên.', [['như', 'So sánh'], ['là', 'Ẩn dụ'], ['thì thầm (cho gió)', 'Nhân hoá'], ['trăm, nghìn (phóng đại)', 'Nói quá']], 'Mỗi từ ngữ là dấu hiệu đặc trưng giúp nhận ra biện pháp tu từ trong câu.', $d);
        $this->matching($s, 'Nối mỗi câu thơ với biện pháp tu từ (bộ 2).', [['"Chị Hằng Nga mỉm cười."', 'Nhân hoá'], ['"Áo chàm đưa buổi phân li."', 'Hoán dụ'], ['"Quê hương là chùm khế ngọt."', 'Ẩn dụ'], ['"Đẹp như tiên giáng trần."', 'So sánh']], 'Trong thơ ca, các biện pháp tu từ làm câu thơ giàu hình ảnh và cảm xúc hơn.', $d);
    }

    private function seedT312(): void
    {
        $s = 'en-my-family';
        $d = 'de';

        $this->sortQ($s, 'Drag each word into HAS "GRAND" or NOT.', [['grandfather', 'HAS "GRAND"'], ['grandmother', 'HAS "GRAND"'], ['father', 'NOT'], ['mother', 'NOT']], '“Grandfather” and “grandmother” have the word “grand”; “father” and “mother” do not. (“Ông/bà” có từ “grand”, “bố/mẹ” thì không.)', $d);
        $this->sortQ($s, 'Drag each phrase into MY FAMILY or NOT FAMILY.', [['my dad', 'MY FAMILY'], ['my sister', 'MY FAMILY'], ['my teacher', 'NOT FAMILY'], ['my friend', 'NOT FAMILY']], '“My dad” and “my sister” are family; “my teacher” and “my friend” are not. (“Bố” và “chị/em gái” là gia đình; “thầy cô” và “bạn” thì không.)', $d);
        $this->sortQ($s, 'Drag each word into OLDER THAN YOU or YOUNGER THAN YOU.', [['father', 'OLDER THAN YOU'], ['grandmother', 'OLDER THAN YOU'], ['younger brother', 'YOUNGER THAN YOU'], ['baby sister', 'YOUNGER THAN YOU']], 'Parents and grandparents are older than you; younger brothers and sisters are younger. (Bố mẹ, ông bà lớn tuổi hơn em; em trai/em gái nhỏ tuổi hơn.)', $d);
    }

    private function seedT313(): void
    {
        $s = 'en-my-family';
        $d = 'de';

        $this->fill($s, 'My ___ is my mother’s husband. (Bố tôi là chồng của mẹ tôi.)', [[0, 'father']], 'The mother’s husband is the “father”. (Chồng của mẹ chính là “bố”.)', $d);
        $this->fill($s, 'His ___ is his father’s mother. (Bà nội của anh ấy là mẹ của bố anh ấy.)', [[0, 'grandmother']], 'The father’s mother is the “grandmother”. (Mẹ của bố chính là “bà”.)', $d);
        $this->fill($s, '“Chú/bác (em trai của bố)” in English is ___.', [[0, 'uncle']], '“Chú/bác (em trai của bố)” in English is “uncle”.', $d);
    }

    private function seedT314(): void
    {
        $s = 'en-at-school';
        $d = 'de';

        $this->quiz($s, 'What is "bút chì" in English?', ['pen', 'pencil', 'ruler', 'book'], 1, '“Bút chì” in English is “pencil”. Don’t mix it up with “pen” (bút mực).', $d);
        $this->quiz($s, 'What is "cặp sách" in English?', ['school bag', 'pencil case', 'notebook', 'blackboard'], 0, '“Cặp sách” in English is “school bag”. (Cái cặp em đeo đi học.)', $d);
        $this->quiz($s, 'Which of these do you write with?', ['book', 'pen', 'desk', 'board'], 1, 'You write with a “pen”. A book is for reading, a desk is for sitting at. (Em viết bằng “bút mực”; sách để đọc, bàn để ngồi học.)', $d);
    }

    private function seedT315(): void
    {
        $s = 'en-at-school';
        $d = 'de';

        $this->matching($s, 'Match each school word with its Vietnamese meaning (set 5).', [['pencil', 'bút chì'], ['school bag', 'cặp sách'], ['pencil case', 'hộp bút'], ['blackboard', 'bảng đen']], 'Learn these school words: pencil (bút chì), school bag (cặp sách), pencil case (hộp bút), blackboard (bảng đen).', $d);
        $this->matching($s, 'Match each school word with its Vietnamese meaning (set 6).', [['chalk', 'phấn'], ['desk', 'bàn học'], ['chair', 'ghế'], ['classroom', 'lớp học']], 'More school words: chalk (phấn), desk (bàn học), chair (ghế), classroom (lớp học).', $d);
        $this->matching($s, 'Match each subject with its Vietnamese meaning (set 2).', [['Maths', 'Toán'], ['Literature', 'Ngữ văn'], ['History', 'Lịch sử'], ['Geography', 'Địa lý']], 'School subjects: Maths (Toán), Literature (Ngữ văn), History (Lịch sử), Geography (Địa lý).', $d);
    }

    private function seedT316(): void
    {
        $s = 'en-present-simple';
        $d = 'trung_binh';

        $this->sortQ($s, 'Drag each sentence into HE/SHE/IT or I/YOU/WE/THEY.', [['She plays tennis.', 'HE/SHE/IT'], ['He reads books.', 'HE/SHE/IT'], ['They play football.', 'I/YOU/WE/THEY'], ['We go to school.', 'I/YOU/WE/THEY']], 'Knowing the subject helps you choose the right verb form. (Biết chủ ngữ giúp em chia động từ đúng.)', $d);
        $this->sortQ($s, 'Drag each sentence into CORRECT or WRONG.', [['She watches TV.', 'CORRECT'], ['He goes home.', 'CORRECT'], ['She watch TV.', 'WRONG'], ['He go home.', 'WRONG']], 'With he/she/it, the verb needs -s or -es. “She watch” and “He go” are wrong. (Với he/she/it, động từ phải thêm -s/-es.)', $d);
        $this->sortQ($s, 'Drag each verb into ADD -ES or ADD -S.', [['watch', 'ADD -ES'], ['go', 'ADD -ES'], ['play', 'ADD -S'], ['read', 'ADD -S']], 'Verbs ending in -ch, -sh, -s, -x, -o add -es; others add -s. (Động từ tận cùng -ch, -sh, -s, -x, -o thêm -es, còn lại thêm -s.)', $d);
    }

    private function seedT317(): void
    {
        $s = 'en-present-simple';
        $d = 'trung_binh';

        $this->fill($s, 'He ___ (watch) TV every evening.', [[0, 'watches']], 'With “he”, “watch” becomes “watches” (add -es). (Với chủ ngữ “he”, động từ “watch” thêm -es thành “watches”.)', $d);
        $this->fill($s, 'I ___ (brush) my teeth twice a day.', [[0, 'brush']], 'With “I”, the verb stays the same: “brush”. (Với chủ ngữ “I”, động từ giữ nguyên.)', $d);
        $this->fill($s, 'The sun ___ (rise) in the east.', [[0, 'rises']], '“The sun” is singular, so “rise” becomes “rises”. (Chủ ngữ số ít “the sun” nên động từ thêm -s.)', $d);
    }

    private function seedT318(): void
    {
        $s = 'en-prepositions';
        $d = 'de';

        $this->quiz($s, 'The cat is ___ the box. (Con mèo ở DƯỚI cái hộp.)', ['in', 'on', 'under', 'next to'], 2, '“Dưới cái hộp” in English is “under the box”. (Vị trí bên dưới dùng “under”.)', $d);
        $this->quiz($s, 'The picture is ___ the wall. (Bức tranh TREO TRÊN tường.)', ['on', 'in', 'under', 'behind'], 0, 'A picture hanging on the wall uses “on”: “on the wall”. (Tranh treo trên tường dùng “on”.)', $d);
        $this->quiz($s, 'She sits ___ me in class. (Bạn ấy ngồi CẠNH tôi trong lớp.)', ['next to', 'on', 'in', 'under'], 0, '“Ngồi cạnh” in English is “sit next to”. (Vị trí bên cạnh dùng “next to”.)', $d);
    }

    private function seedT319(): void
    {
        $s = 'en-prepositions';
        $d = 'de';

        $this->matching($s, 'Match each preposition with its Vietnamese meaning (set 3).', [['behind', 'phía sau'], ['in front of', 'phía trước'], ['between', 'ở giữa'], ['next to', 'bên cạnh']], 'behind (phía sau), in front of (phía trước), between (ở giữa), next to (bên cạnh).', $d);
        $this->matching($s, 'Match each preposition with its Vietnamese meaning (set 4).', [['above', 'phía trên'], ['below', 'phía dưới'], ['near', 'gần'], ['far from', 'xa']], 'above (phía trên), below (phía dưới), near (gần), far from (xa).', $d);
        $this->matching($s, 'Match each sentence with the correct preposition.', [['The dog is ___ the table.', 'under'], ['The bag is ___ the chair.', 'on'], ['The school is ___ my house.', 'near'], ['The cat is ___ the box.', 'in']], 'Choose the preposition that fits the picture: under (dưới), on (trên), near (gần), in (trong).', $d);
    }

    private function seedT320(): void
    {
        $s = 'en-health';
        $d = 'de';

        $this->sortQ($s, 'Drag each word into BODY PART or NOT BODY PART.', [['head', 'BODY PART'], ['arm', 'BODY PART'], ['table', 'NOT BODY PART'], ['book', 'NOT BODY PART']], '“Head” and “arm” are body parts; “table” and “book” are things. (“Đầu” và “cánh tay” là bộ phận cơ thể; “bàn” và “sách” là đồ vật.)', $d);
        $this->sortQ($s, 'Drag each word into FEELING GOOD or FEELING BAD.', [['healthy', 'FEELING GOOD'], ['strong', 'FEELING GOOD'], ['tired', 'FEELING BAD'], ['sick', 'FEELING BAD']], '“Healthy” and “strong” are good feelings; “tired” and “sick” are bad. (“Khỏe mạnh” là cảm giác tốt; “mệt” và “ốm” là cảm giác xấu.)', $d);
        $this->sortQ($s, 'Drag each food into HEALTHY FOOD or JUNK FOOD.', [['apple', 'HEALTHY FOOD'], ['milk', 'HEALTHY FOOD'], ['candy', 'JUNK FOOD'], ['soft drink', 'JUNK FOOD']], 'Apples and milk are healthy; candy and soft drinks are junk food. (Táo và sữa tốt cho sức khỏe; kẹo và nước ngọt thì không.)', $d);
    }

    private function seedT321(): void
    {
        $s = 'en-health';
        $d = 'de';

        $this->fill($s, 'I have a ___. My throat hurts. (Tôi bị đau họng.)', [[0, 'sore throat']], '“Đau họng” in English is “a sore throat”.', $d);
        $this->fill($s, 'She has a ___. Her nose is running. (Cô ấy bị cảm lạnh.)', [[0, 'cold']], '“Cảm lạnh” in English is “a cold”.', $d);
        $this->fill($s, 'Brush your ___ twice a day to keep them clean. (Hãy đánh răng hai lần mỗi ngày.)', [[0, 'teeth']], 'The plural of “tooth” is “teeth”. (Số nhiều của “tooth” là “teeth”.)', $d);
    }

    private function seedT322(): void
    {
        $s = 'en-travel';
        $d = 'de';

        $this->quiz($s, 'What is "vé máy bay" in English?', ['plane ticket', 'train ticket', 'bus stop', 'passport'], 0, '“Vé máy bay” in English is “plane ticket”. “Train ticket” is vé tàu. (Vé đi máy bay dùng “plane ticket”.)', $d);
        $this->quiz($s, 'What is "khách sạn" in English?', ['hotel', 'airport', 'station', 'ticket'], 0, '“Khách sạn” in English is “hotel” — the place you sleep when you travel. (Nơi nghỉ khi đi du lịch là “hotel”.)', $d);
        $this->quiz($s, 'What is "sân bay" in English?', ['airport', 'hotel', 'beach', 'map'], 0, '“Sân bay” in English is “airport” — where planes take off and land. (Nơi máy bay cất/hạ cánh là “airport”.)', $d);
    }

    private function seedT323(): void
    {
        $s = 'en-travel';
        $d = 'de';

        $this->matching($s, 'Match each travel word with its Vietnamese meaning (set C).', [['hotel', 'khách sạn'], ['airport', 'sân bay'], ['ticket', 'vé'], ['suitcase', 'va li']], 'hotel (khách sạn), airport (sân bay), ticket (vé), suitcase (va li).', $d);
        $this->matching($s, 'Match each travel word with its Vietnamese meaning (set D).', [['camera', 'máy ảnh'], ['beach', 'bãi biển'], ['tourist', 'khách du lịch'], ['trip', 'chuyến đi']], 'camera (máy ảnh), beach (bãi biển), tourist (khách du lịch), trip (chuyến đi).', $d);
        $this->matching($s, 'Match each vehicle with the place you catch it.', [['plane', 'airport'], ['train', 'station'], ['bus', 'bus stop'], ['taxi', 'street']], 'You catch a plane at the airport, a train at the station, a bus at the bus stop. (Đón máy bay ở sân bay, tàu ở ga, xe buýt ở trạm.)', $d);
    }

    private function seedT324(): void
    {
        $s = 'th-tep-va-thu-muc';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi tình huống mất tệp với cách phòng tránh.', [['Virus mã hoá tệp', 'Sao lưu định kỳ ra ổ khác'], ['Xoá nhầm tệp', 'Kiểm tra thùng rác trước khi xoá vĩnh viễn'], ['Ổ cứng hỏng', 'Lưu thêm bản trên đám mây'], ['Quên chỗ lưu tệp', 'Đặt tên rõ ràng, lưu đúng thư mục']], 'Phòng tránh mất tệp bằng cách sao lưu, đặt tên rõ ràng và cẩn thận khi xoá.', $d);
    }

    private function seedT325(): void
    {
        $s = 'th-tep-va-thu-muc';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi việc vào nhóm "NÊN LÀM" hoặc "KHÔNG NÊN LÀM" khi tắt máy.', [['Lưu tệp trước khi tắt', 'NÊN LÀM'], ['Đóng phần mềm đang mở', 'NÊN LÀM'], ['Rút điện đột ngột', 'KHÔNG NÊN LÀM'], ['Tắt máy khi đang cập nhật', 'KHÔNG NÊN LÀM']], 'Trước khi tắt máy phải lưu tệp và đóng phần mềm; rút điện đột ngột hoặc tắt khi đang cập nhật dễ hỏng dữ liệu.', $d);
    }

    private function seedT326(): void
    {
        $s = 'tt-luat-bong-ro-co-ban';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi tình huống phạm luật với hình phạt.', [['Đi bộ (travelling)', 'Mất bóng, đối phương phát bóng biên'], ['Chạm bóng 2 lần khi dẫn', 'Mất bóng'], ['Lỗi cá nhân nghiêm trọng', 'Đối phương được ném phạt'], ['Hết 24 giây tấn công', 'Mất bóng, đối phương phát bóng']], 'Phạm luật trong bóng rổ thường bị mất bóng; lỗi nghiêm trọng đối phương được ném phạt.', $d);
    }

    private function seedT327(): void
    {
        $s = 'tt-luat-bong-ro-co-ban';
        $d = 'trung_binh';

        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Ném phạt 1 quả / Ném phạt 2 quả trở lên.', [['Bị phạm lỗi và ném trúng rổ (and-one)', 'Ném phạt 1 quả'], ['Lỗi kỹ thuật của đối phương', 'Ném phạt 1 quả'], ['Bị phạm lỗi khi ném 2 điểm', 'Ném phạt 2 quả trở lên'], ['Bị phạm lỗi khi ném 3 điểm', 'Ném phạt 2 quả trở lên']], 'Ném trúng mà còn bị phạm lỗi thì được ném phạt 1 quả cộng thêm; phạm lỗi khi ném 2-3 điểm thì ném phạt 2-3 quả.', $d);
    }

    private function seedT328(): void
    {
        $s = 'tt-ve-sinh-ca-nhan-khi-tap';
        $d = 'de';

        $this->matching($s, 'Nối mỗi sai lầm vệ sinh với hậu quả.', [['Không tắm sau khi tập', 'Cơ thể có mùi, dễ viêm da'], ['Dùng chung khăn mặt', 'Lây bệnh về da'], ['Mặc lại quần áo ướt mồ hôi', 'Dễ cảm lạnh, nổi mụn'], ['Uống nước lã sau khi tập', 'Dễ đau bụng']], 'Giữ vệ sinh cá nhân sau khi tập giúp cơ thể khỏe mạnh, tránh bệnh tật.', $d);
    }

    private function seedT329(): void
    {
        $s = 'tnhn-lap-thoi-gian-bieu';
        $d = 'de';

        $this->sortQ($s, 'Xếp các việc vào nhóm: Làm ngay hôm nay / Lên lịch cuối tuần.', [['Ôn bài kiểm tra ngày mai', 'Làm ngay hôm nay'], ['Làm bài tập về nhà', 'Làm ngay hôm nay'], ['Dọn tủ sách', 'Lên lịch cuối tuần'], ['Sắp xếp lại góc học tập', 'Lên lịch cuối tuần']], 'Việc gấp làm ngay hôm nay; việc không gấp thì lên lịch cuối tuần để không dồn việc.', $d);
    }

    private function seedT330(): void
    {
        $s = 'tnhn-giao-tiep-lam-viec-nhom';
        $d = 'de';

        $this->matching($s, 'Nối mỗi câu nói trong nhóm với ý nghĩa.', [['"Mình làm phần này nhé."', 'Nhận việc'], ['"Bạn giúp mình với."', 'Nhờ giúp đỡ'], ['"Ý kiến hay đấy!"', 'Khen ngợi'], ['"Để mình kiểm tra lại."', 'Chịu trách nhiệm']], 'Lời nói trong nhóm có thể là nhận việc, nhờ giúp đỡ, khen ngợi hoặc chịu trách nhiệm.', $d);
        $this->matching($s, 'Nối mỗi biểu hiện với điều nó thể hiện.', [['Nhìn bạn khi bạn nói', 'Tôn trọng'], ['Ghi chép ý kiến chung', 'Trách nhiệm'], ['Cười khi bạn nói sai', 'Thiếu tôn trọng'], ['Bỏ về giữa chừng', 'Thiếu trách nhiệm']], 'Biểu hiện tôn trọng và trách nhiệm giúp nhóm tin tưởng nhau; ngược lại làm mất đoàn kết.', $d);
    }

    private function seedT331(): void
    {
        $s = 'tnhn-giao-tiep-lam-viec-nhom';
        $d = 'de';

        $this->sortQ($s, 'Xếp các việc vào nhóm: Nói trước lớp / Chuẩn bị ở nhà.', [['Tập nói thử trước gương', 'Chuẩn bị ở nhà'], ['Chuẩn bị slide', 'Chuẩn bị ở nhà'], ['Nói to, rõ ràng', 'Nói trước lớp'], ['Nhìn khán giả khi nói', 'Nói trước lớp']], 'Chuẩn bị kỹ ở nhà giúp em tự tin khi nói trước lớp.', $d);
    }

    private function seedT332(): void
    {
        $s = 'tnhn-an-toan-giao-thong';
        $d = 'de';

        $this->matching($s, 'Nối mỗi việc làm khi đi bộ với đánh giá.', [['Đi trên vỉa hè', 'An toàn'], ['Nhìn trước sau khi qua đường', 'An toàn'], ['Chạy ào qua đường', 'Nguy hiểm'], ['Vừa đi vừa cúi đầu xem điện thoại', 'Nguy hiểm']], 'Đi bộ an toàn là đi trên vỉa hè và quan sát kỹ; chạy ào qua đường hoặc xem điện thoại rất nguy hiểm.', $d);
    }

    private function seedT333(): void
    {
        $s = 'tnhn-phan-loai-rac';
        $d = 'de';

        $this->matching($s, 'Nối mỗi đồ vật với nhóm rác của nó (bộ 2).', [['Vỏ hộp sữa', 'Rác tái chế'], ['Xương cá', 'Rác hữu cơ'], ['Bóng đèn huỳnh quang vỡ', 'Rác nguy hại'], ['Giấy ăn đã dùng', 'Rác còn lại']], 'Vỏ hộp sữa tái chế được; xương cá là rác hữu cơ; bóng đèn vỡ là rác nguy hại phải gói cẩn thận.', $d);
        $this->matching($s, 'Nối mỗi khẩu hiệu với ý nghĩa.', [['"Nói không với túi nilon"', 'Hạn chế nhựa dùng một lần'], ['"Mỗi tuần một cây xanh"', 'Trồng cây bảo vệ môi trường'], ['"Rác đúng nơi, phố thêm xinh"', 'Bỏ rác đúng chỗ'], ['"Tiết kiệm điện hôm nay"', 'Dùng điện hợp lý']], 'Mỗi khẩu hiệu môi trường đều nhắc một hành động cụ thể ai cũng làm được.', $d);
    }

    private function seedT334(): void
    {
        $s = 'tnhn-phan-loai-rac';
        $d = 'de';

        $this->sortQ($s, 'Xếp các việc vào nhóm: Giảm rác / Tăng rác.', [['Mang hộp cơm riêng', 'Giảm rác'], ['Dùng khăn vải thay khăn giấy', 'Giảm rác'], ['Xin thêm túi nilon', 'Tăng rác'], ['Mua nước chai nhựa mỗi ngày', 'Tăng rác']], 'Dùng đồ bền, dùng nhiều lần giúp giảm rác; đồ nhựa dùng một lần làm tăng rác.', $d);
        $this->sortQ($s, 'Xếp các loại rác vào nhóm: Phân huỷ nhanh / Phân huỷ chậm.', [['Vỏ chuối', 'Phân huỷ nhanh'], ['Lá rau úa', 'Phân huỷ nhanh'], ['Chai nhựa', 'Phân huỷ chậm'], ['Túi nilon', 'Phân huỷ chậm']], 'Rác hữu cơ phân huỷ nhanh; nhựa và nilon cần hàng trăm năm mới phân huỷ nên phải hạn chế.', $d);
    }

    private function seedT335(): void
    {
        $s = 'tnhn-cac-nghe-quen-thuoc';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi nghề với kiến thức cần học giỏi.', [['Bác sĩ', 'Sinh học, Hoá học'], ['Kỹ sư xây dựng', 'Toán, Vật lý'], ['Nhà văn', 'Ngữ văn'], ['Phiên dịch viên', 'Ngoại ngữ']], 'Mỗi nghề cần nền kiến thức riêng; học tốt các môn liên quan là bước chuẩn bị từ bây giờ.', $d);
        $this->matching($s, 'Nối mỗi nghề với tính cách phù hợp.', [['Giáo viên mầm non', 'Yêu trẻ, kiên nhẫn'], ['Lập trình viên', 'Tỉ mỉ, thích máy tính'], ['Hướng dẫn viên du lịch', 'Hoạt bát, thích đi đây đó'], ['Kế toán', 'Cẩn thận, chính xác']], 'Tính cách phù hợp giúp em gắn bó lâu dài và phát huy tốt trong nghề.', $d);
        $this->matching($s, 'Nối mỗi công việc với nghề làm ra nó.', [['Khám bệnh, kê đơn', 'Bác sĩ'], ['Thiết kế nhà cửa', 'Kiến trúc sư'], ['Sửa điện trong nhà', 'Thợ điện'], ['Cắt tóc', 'Thợ cắt tóc']], 'Mỗi công việc trong đời sống đều gắn với một nghề nghiệp cụ thể.', $d);
    }

    private function seedT336(): void
    {
        $s = 'tnhn-uoc-mo-nghe-nghiep-cua-em';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi ước mơ với môn học cần chú trọng.', [['Làm bác sĩ', 'Sinh học'], ['Làm kiến trúc sư', 'Toán, Mĩ thuật'], ['Làm nhà báo', 'Ngữ văn'], ['Làm lập trình viên', 'Tin học, Toán']], 'Muốn theo nghề nào thì ngay từ bây giờ hãy chú trọng các môn học liên quan.', $d);
        $this->matching($s, 'Nối mỗi thói quen xấu với ảnh hưởng tới ước mơ.', [['Lười học', 'Thiếu kiến thức nền'], ['Ngại giao tiếp', 'Khó làm việc nhóm'], ['Dễ bỏ cuộc', 'Không vượt qua khó khăn'], ['Thức khuya chơi game', 'Sức khoẻ giảm, học kém']], 'Thói quen xấu hôm nay có thể cản trở ước mơ ngày mai; sửa sớm thì tốt sớm.', $d);
    }

    private function seedT337(): void
    {
        $s = 'tnhn-uoc-mo-nghe-nghiep-cua-em';
        $d = 'trung_binh';

        $this->sortQ($s, 'Xếp các câu nói vào nhóm: Nên nghe / Không nên nghe khi chọn nghề.', [['"Hãy chọn nghề hợp với sở thích và năng lực."', 'Nên nghe'], ['"Tìm hiểu kỹ nghề trước khi quyết định."', 'Nên nghe'], ['"Nghề nào lương cao thì chọn, kệ có thích không."', 'Không nên nghe'], ['"Con trai thì phải làm nghề này, con gái nghề kia."', 'Không nên nghe']], 'Chọn nghề nên dựa vào sở thích, năng lực và tìm hiểu kỹ, không chạy theo lương hay định kiến.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Chuẩn bị cho ước mơ / Chưa liên quan.', [['Đọc sách về nghề mình thích', 'Chuẩn bị cho ước mơ'], ['Hỏi chuyện người đang làm nghề đó', 'Chuẩn bị cho ước mơ'], ['Chơi game cả ngày', 'Chưa liên quan'], ['Ngủ nướng tới trưa', 'Chưa liên quan']], 'Ước mơ cần hành động cụ thể mỗi ngày chứ không chỉ nói suông.', $d);
    }

    private function seedT338(): void
    {
        $s = 'tin-hoc-thpt-10-lop-10-2';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi tình huống soạn thảo với cách làm trong Word.', [['Muốn chữ bao quanh ảnh', 'Chọn kiểu Text Wrapping'], ['Muốn bảng có đường viền đẹp', 'Dùng Borders and Shading'], ['Muốn chèn ảnh từ máy', 'Vào Insert → Pictures'], ['Muốn gộp nhiều ô thành một', 'Dùng Merge Cells']], 'Các lệnh trong Word giúp văn bản có bảng biểu và hình ảnh trình bày đẹp, chuyên nghiệp.', $d);
    }

    private function seedT339(): void
    {
        $s = 'tin-hoc-thpt-10-lop-10-3';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi tình huống tính toán với hàm Excel phù hợp.', [['Tính tổng điểm cả lớp', 'SUM'], ['Tìm điểm cao nhất', 'MAX'], ['Đếm số học sinh', 'COUNTA'], ['Tính điểm trung bình', 'AVERAGE']], 'Chọn đúng hàm Excel giúp tính toán nhanh: SUM tính tổng, MAX tìm lớn nhất, AVERAGE tính trung bình.', $d);
    }

    private function seedT340(): void
    {
        $s = 'tin-hoc-thpt-12-lop-12-1';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi bước giải bài toán với ví dụ.', [['Xác định Input', 'Nhập điểm 3 môn'], ['Xác định Output', 'Điểm trung bình'], ['Mô tả thuật toán', 'Các bước tính toán'], ['Kiểm thử', 'Chạy với điểm mẫu']], 'Giải bài toán bằng máy tính gồm: xác định input/output, mô tả thuật toán rồi kiểm thử.', $d);
    }

    private function seedT341(): void
    {
        $s = 'tin-hoc-thpt-12-lop-12-2';
        $d = 'kho';

        $this->matching($s, 'Nối mỗi bài toán với cấu trúc điều khiển chính.', [['Xếp loại học lực theo điểm', 'Rẽ nhánh (if-else)'], ['Tính tổng 100 số đầu tiên', 'Vòng lặp for'], ['Nhập điểm tới khi hợp lệ', 'Vòng lặp while'], ['Kiểm tra số nguyên tố', 'Rẽ nhánh + vòng lặp']], 'Rẽ nhánh dùng khi có điều kiện lựa chọn; vòng lặp dùng khi lặp lại công việc nhiều lần.', $d);
    }

    private function seedT342(): void
    {
        $s = 'tin-hoc-thpt-12-lop-12-4';
        $d = 'kho';

        $this->matching($s, 'Nối mỗi đoạn code Python với kết quả in ra.', [["for i in range(3):\n    print(i)", '0 1 2'], ["x = 5\nif x > 3:\n    print('lon')", 'lon'], ["n = 0\nwhile n < 2:\n    n += 1\nprint(n)", '2'], ['print(7 % 3)', '1']], 'Đọc kỹ từng dòng code: range(3) cho 0,1,2; 7 % 3 là phép chia lấy dư bằng 1.', $d);
    }

    private function seedT343(): void
    {
        $s = 'gdtc-thpt-11-lop-11-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Trong bóng chuyền, mỗi đội được chạm bóng tối đa mấy lần trước khi đưa bóng sang sân đối phương?', ['2 lần', '3 lần', '4 lần', 'Không giới hạn'], 1, 'Mỗi đội được chạm bóng tối đa 3 lần (không tính lần chắn bóng) rồi phải đưa bóng sang sân đối phương.', $d);
    }

    private function seedT344(): void
    {
        $s = 'gdtc-thpt-11-lop-11-3';
        $d = 'trung_binh';

        $this->quiz($s, 'Trong cầu lông, khi tỉ số 20-20 thì set đấu kết thúc khi nào?', ['Đội nào hơn 2 điểm thì thắng', 'Đánh tiếp tới 21 là thắng ngay', 'Đánh tới 30 điểm mới xong', 'Bốc thăm quyết định'], 0, 'Khi 20-20, đội nào dẫn trước 2 điểm thì thắng set; nếu tới 29-29, đội ghi điểm 30 thắng.', $d);
    }
}
