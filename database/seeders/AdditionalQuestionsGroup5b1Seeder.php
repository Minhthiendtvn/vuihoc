<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Bổ sung câu hỏi cho 15 bài Âm nhạc HIỆN CÓ (nhóm G5, phần b1 - nửa đầu).
 *
 * Mỗi bài: 5 câu MỚI cho mỗi kiểu chơi (quiz/matching/sort/fill).
 * Nội dung tiếng Việt tự viết 100%, bám topic + khối lớp + độ khó của bài,
 * không trùng prompt đã có.
 * Idempotent: chạy lại không thêm câu mới (giới hạn 8 câu/kiểu/bài + kiểm tra prompt trùng).
 */
class AdditionalQuestionsGroup5b1Seeder extends Seeder
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
        $this->seedAnBayNotNhac();
        $this->seedAnCaoDoTruongDo();
        $this->seedAnNhip2434();
        $this->seedAnCacDauLang();
        $this->seedAnDanBauTranhSaoTrong();
        $this->seedAnHatBaiThieuNhi();
        $this->seedAnNotNhacCaoDoLop61();
        $this->seedAnNotNhacCaoDoLop62();
        $this->seedAnNotNhacCaoDoLop71();
        $this->seedAnNotNhacCaoDoLop72();
        $this->seedAnNhipDauLangLop71();
        $this->seedAnNhipDauLangLop72();
        $this->seedAnNhipDauLangLop81();
        $this->seedAnNhipDauLangLop82();
        $this->seedAnNhacCuDanTocLop81();
    }

    private function seedAnBayNotNhac(): void
    {
        $s = 'an-bay-not-nhac';
        $d = 'de';

        $this->quiz($s, 'Nốt Đô có ký hiệu quốc tế là chữ gì?', ['C', 'D', 'E', 'F'], 0, 'Trong ký hiệu quốc tế, nốt Đô được viết bằng chữ C.', $d);
        $this->quiz($s, 'Nốt nào đứng ngay trước nốt Son?', ['Mi', 'Fa', 'Rê', 'La'], 1, 'Thứ tự 7 nốt nhạc là Đô, Rê, Mi, Fa, Son, La, Si nên nốt Fa đứng ngay trước nốt Son.', $d);
        $this->quiz($s, 'Nốt thứ hai trong thang nhạc 7 nốt là nốt nào?', ['Đô', 'Rê', 'Mi', 'Fa'], 1, 'Thang nhạc cơ bản bắt đầu bằng Đô, nốt thứ hai là nốt Rê.', $d);
        $this->quiz($s, 'Ký hiệu chữ D trong âm nhạc quốc tế chỉ nốt nào?', ['Đô', 'Rê', 'Mi', 'La'], 1, 'Trong ký hiệu quốc tế, chữ D ứng với nốt Rê.', $d);
        $this->quiz($s, 'Từ nốt Đô đến nốt Si đi qua tất cả bao nhiêu nốt?', ['5 nốt', '6 nốt', '7 nốt', '8 nốt'], 2, 'Thang nhạc cơ bản gồm 7 nốt: Đô, Rê, Mi, Fa, Son, La, Si.', $d);

        $this->matching($s, 'Nối mỗi nốt nhạc với số thứ tự của nó trong thang nhạc.', [['Đô', 'Số 1'], ['Rê', 'Số 2'], ['Son', 'Số 5'], ['Si', 'Số 7']], 'Nhớ số thứ tự giúp em đọc nốt nhạc nhanh và chính xác hơn.', $d);
        $this->matching($s, 'Nối mỗi ký hiệu chữ cái quốc tế với nốt nhạc tương ứng.', [['A', 'La'], ['B', 'Si'], ['E', 'Mi'], ['G', 'Son']], 'Ký hiệu quốc tế dùng 7 chữ cái từ A đến G để gọi tên 7 nốt nhạc.', $d);
        $this->matching($s, 'Nối mỗi nốt nhạc với nốt đứng ngay trước nó.', [['Mi', 'Rê'], ['Son', 'Fa'], ['La', 'Son'], ['Si', 'La']], 'Nhớ nốt kề trước, kề sau giúp em đọc nhanh các nốt liên tiếp trong bản nhạc.', $d);
        $this->matching($s, 'Nối mỗi nốt nhạc với nốt đứng ngay sau nó.', [['Đô', 'Rê'], ['Rê', 'Mi'], ['Fa', 'Son'], ['La', 'Si']], 'Nốt đứng sau nốt La là nốt Si, kết thúc thang nhạc 7 nốt.', $d);
        $this->matching($s, 'Nối mỗi nốt nhạc với ký hiệu quốc tế còn thiếu.', [['Đô', 'C'], ['Fa', 'F'], ['Mi', 'E'], ['Rê', 'D']], 'Cặp ký hiệu Đô-C, Rê-D, Mi-E, Fa-F thường xuất hiện trong các bài tập đọc nốt.', $d);

        $this->sortQ($s, 'Kéo mỗi nốt vào nhóm NỐT Ở VỊ TRÍ LẺ hoặc NỐT Ở VỊ TRÍ CHẴN trong thang nhạc.', [['Đô', 'Vị trí lẻ'], ['Rê', 'Vị trí chẵn'], ['Mi', 'Vị trí lẻ'], ['Fa', 'Vị trí chẵn'], ['Son', 'Vị trí lẻ'], ['La', 'Vị trí chẵn']], 'Đô là nốt 1, Rê là nốt 2... cứ thế xen kẽ vị trí lẻ và chẵn tới nốt Si là nốt 7.', $d);
        $this->sortQ($s, 'Kéo mỗi ký hiệu vào nhóm LÀ NỐT NHẠC hoặc KHÔNG PHẢI NỐT NHẠC.', [['C', 'Là nốt nhạc'], ['D', 'Là nốt nhạc'], ['E', 'Là nốt nhạc'], ['X', 'Không phải nốt nhạc'], ['Q', 'Không phải nốt nhạc'], ['M', 'Không phải nốt nhạc']], 'Ký hiệu quốc tế của nốt nhạc chỉ dùng các chữ cái từ A đến G.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nốt vào nhóm HAI NỐT KỀ NHAU hoặc HAI NỐT CÁCH NHAU.', [['Đô - Rê', 'Kề nhau'], ['Mi - Fa', 'Kề nhau'], ['Son - La', 'Kề nhau'], ['Đô - Mi', 'Cách nhau'], ['Rê - Son', 'Cách nhau'], ['Fa - Si', 'Cách nhau']], 'Hai nốt kề nhau là hai nốt đứng liền nhau trong thang nhạc 7 nốt.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về thang 7 nốt nhạc.', [['Thang nhạc cơ bản có 7 nốt', 'Đúng'], ['Sau nốt Si là nốt Đô cao hơn', 'Đúng'], ['Nốt Rê đứng giữa Đô và Mi', 'Đúng'], ['Nốt Mi đứng sau nốt Fa', 'Sai'], ['Có 8 nốt nhạc cơ bản', 'Sai']], 'Thang 7 nốt đi từ Đô lên Si, hết Si lại quay về Đô ở cao độ cao hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi nốt vào nhóm TRẦM HƠN NỐT MI hoặc BỔNG HƠN NỐT MI.', [['Đô', 'Trầm hơn nốt Mi'], ['Rê', 'Trầm hơn nốt Mi'], ['Fa', 'Bổng hơn nốt Mi'], ['Son', 'Bổng hơn nốt Mi'], ['La', 'Bổng hơn nốt Mi'], ['Si', 'Bổng hơn nốt Mi']], 'Cao độ tăng dần theo thứ tự Đô, Rê, Mi, Fa, Son, La, Si.', $d);

        $this->fill($s, 'Nốt đầu tiên trong thang nhạc 7 nốt là nốt ___.', [[0, 'Đô']], 'Thang nhạc cơ bản bắt đầu bằng nốt Đô và kết thúc bằng nốt Si.', $d);
        $this->fill($s, 'Trong ký hiệu quốc tế, nốt Son được viết bằng chữ ___.', [[0, 'G']], 'Nốt Son ứng với chữ G trong hệ ký hiệu 7 chữ cái A đến G.', $d);
        $this->fill($s, 'Sau nốt La trong thang nhạc là nốt ___.', [[0, 'Si']], 'Thứ tự cuối của thang nhạc là La rồi tới Si.', $d);
        $this->fill($s, 'Thang nhạc cơ bản bắt đầu bằng nốt Đô và kết thúc bằng nốt ___.', [[0, 'Si']], 'Nốt Si là nốt thứ bảy, nốt cuối cùng của thang nhạc cơ bản.', $d);
        $this->fill($s, 'Nốt ___ đứng ngay giữa nốt Đô và nốt Mi.', [[0, 'Rê']], 'Nốt Rê là nốt thứ hai, nằm giữa nốt Đô và nốt Mi.', $d);
    }

    private function seedAnCaoDoTruongDo(): void
    {
        $s = 'an-cao-do-truong-do';
        $d = 'trung_binh';

        $this->quiz($s, 'Nốt móc đơn có trường độ bằng bao nhiêu nốt đen?', ['Một nốt đen', 'Nửa nốt đen', 'Hai nốt đen', 'Bốn nốt đen'], 1, 'Nốt móc đơn dài bằng một nửa nốt đen, tức là hai nốt móc đơn mới bằng một nốt đen.', $d);
        $this->quiz($s, 'Nốt tròn dài gấp mấy lần nốt trắng?', ['2 lần', '4 lần', '8 lần', 'Bằng nhau'], 0, 'Nốt tròn có trường độ dài nhất, gấp đôi nốt trắng và gấp 4 lần nốt đen.', $d);
        $this->quiz($s, 'Trong 4 hình nốt: tròn, trắng, đen, móc đơn — hình nào ngắn nhất?', ['Nốt tròn', 'Nốt trắng', 'Nốt đen', 'Nốt móc đơn'], 3, 'Nốt móc đơn là hình nốt ngắn nhất trong bốn hình nốt cơ bản em đã học.', $d);
        $this->quiz($s, 'Muốn viết một nốt ngân dài 2 phách (khi phách là nốt đen), em dùng hình nốt nào?', ['Nốt tròn', 'Nốt trắng', 'Nốt đen', 'Nốt móc đơn'], 1, 'Nốt trắng dài đúng 2 phách khi đơn vị phách là nốt đen.', $d);
        $this->quiz($s, 'Dấu chấm dôi đặt sau nốt nhạc có tác dụng gì?', ['Tăng thêm một nửa giá trị của nốt', 'Giảm đi một nửa giá trị của nốt', 'Làm nốt nhạc cao hơn', 'Làm nốt nhạc ngân ngắn hơn'], 0, 'Dấu chấm dôi làm nốt nhạc dài thêm bằng một nửa giá trị của chính nốt đó.', $d);

        $this->matching($s, 'Nối mỗi hình nốt với số nốt đen tương đương (đơn vị phách là nốt đen).', [['Nốt tròn', '4 nốt đen'], ['Nốt trắng', '2 nốt đen'], ['Nốt đen', '1 nốt đen'], ['Nốt móc đơn', '1/2 nốt đen']], 'Nhớ tỉ lệ trường độ giúp em tính tổng giá trị các nốt trong một ô nhịp.', $d);
        $this->matching($s, 'Nối mỗi bộ phận của nốt nhạc với tên gọi của nó.', [['Phần hình bầu dục', 'Đầu nốt'], ['Đường kẻ dọc', 'Thân nốt'], ['Phần cong ở đầu thân nốt', 'Móc nốt'], ['Đường gạch ngang nối các thân nốt', 'Đuôi chùm']], 'Một nốt nhạc gồm đầu nốt, thân nốt và có thể có thêm móc hoặc đuôi chùm.', $d);
        $this->matching($s, 'Nối mỗi khái niệm âm nhạc với ví dụ đúng của nó.', [['Cao độ', 'Nốt nằm cao trên khuông nhạc'], ['Trường độ', 'Nốt trắng dài hơn nốt đen'], ['Âm sắc', 'Tiếng đàn khác tiếng sáo'], ['Cường độ', 'Hát to hay hát nhỏ']], 'Cao độ là trầm hay bổng, trường độ là dài hay ngắn của âm thanh.', $d);
        $this->matching($s, 'Nối mỗi cách đếm với hình nốt tương ứng (đơn vị phách là nốt đen).', [['Đếm "1-2-3-4"', 'Nốt tròn'], ['Đếm "1-2"', 'Nốt trắng'], ['Đếm "1"', 'Nốt đen'], ['Đếm "1 và"', 'Nốt móc đơn']], 'Đếm phách đều giúp em giữ đúng trường độ của từng hình nốt khi hát.', $d);
        $this->matching($s, 'Nối mỗi ký hiệu với ý nghĩa của nó.', [['Dấu chấm dôi', 'Tăng thêm một nửa giá trị nốt'], ['Khuông nhạc', 'Gồm 5 dòng kẻ'], ['Khóa Son', 'Xác định vị trí nốt Son'], ['Vạch nhịp', 'Ngăn cách các ô nhịp']], 'Các ký hiệu này cùng nhau tạo nên một bản nhạc hoàn chỉnh.', $d);

        $this->sortQ($s, 'Kéo mỗi hình nốt vào nhóm CÓ THÂN NỐT hoặc KHÔNG CÓ THÂN NỐT.', [['Nốt trắng', 'Có thân nốt'], ['Nốt đen', 'Có thân nốt'], ['Nốt móc đơn', 'Có thân nốt'], ['Nốt tròn', 'Không có thân nốt']], 'Chỉ nốt tròn là không có thân nốt, các hình nốt còn lại đều có thân nốt.', $d);
        $this->sortQ($s, 'Kéo mỗi hình nốt vào nhóm NGÂN TỪ 2 PHÁCH TRỞ LÊN hoặc NGẮN HƠN 2 PHÁCH.', [['Nốt tròn', 'Từ 2 phách trở lên'], ['Nốt trắng', 'Từ 2 phách trở lên'], ['Nốt đen', 'Ngắn hơn 2 phách'], ['Nốt móc đơn', 'Ngắn hơn 2 phách']], 'Nốt tròn dài 4 phách, nốt trắng dài 2 phách, nốt đen 1 phách, nốt móc đơn nửa phách.', $d);
        $this->sortQ($s, 'Kéo mỗi mô tả vào nhóm CAO ĐỘ hoặc TRƯỜNG ĐỘ.', [['Nốt nằm cao trên khuông nhạc', 'Cao độ'], ['Âm thanh trầm hay bổng', 'Cao độ'], ['Nốt ngân dài 4 phách', 'Trường độ'], ['Nốt ngắn hay dài', 'Trường độ']], 'Vị trí trên khuông nhạc quyết định cao độ, hình dạng nốt quyết định trường độ.', $d);
        $this->sortQ($s, 'Kéo mỗi hình nốt vào nhóm CÓ MÓC NỐT hoặc KHÔNG CÓ MÓC NỐT.', [['Nốt móc đơn', 'Có móc nốt'], ['Nốt đen', 'Không có móc nốt'], ['Nốt trắng', 'Không có móc nốt'], ['Nốt tròn', 'Không có móc nốt']], 'Móc nốt là dấu hiệu nhận biết nốt móc đơn trong các hình nốt cơ bản.', $d);
        $this->sortQ($s, 'Kéo mỗi hình nốt vào nhóm BẰNG HOẶC DÀI HƠN NỐT TRẮNG hoặc NGẮN HƠN NỐT TRẮNG.', [['Nốt tròn', 'Bằng hoặc dài hơn nốt trắng'], ['Nốt trắng', 'Bằng hoặc dài hơn nốt trắng'], ['Nốt đen', 'Ngắn hơn nốt trắng'], ['Nốt móc đơn', 'Ngắn hơn nốt trắng']], 'Nốt trắng là mốc giữa: tròn dài hơn, đen và móc đơn ngắn hơn.', $d);

        $this->fill($s, 'Nốt tròn dài gấp ___ lần nốt trắng.', [[0, '2']], 'Nốt tròn dài 4 phách, nốt trắng dài 2 phách nên tròn gấp đôi trắng.', $d);
        $this->fill($s, 'Nốt móc đơn có trường độ bằng ___ nốt đen.', [[0, 'nửa']], 'Hai nốt móc đơn ghép lại mới dài bằng một nốt đen.', $d);
        $this->fill($s, 'Nốt đen dài gấp ___ lần nốt móc đơn.', [[0, '2']], 'Nốt đen dài 1 phách, nốt móc đơn dài nửa phách nên đen gấp đôi móc đơn.', $d);
        $this->fill($s, 'Một nốt trắng có dấu chấm dôi dài bằng ___ nốt đen.', [[0, '3']], 'Nốt trắng dài 2 phách, cộng thêm một nửa của 2 là 1, tổng cộng 3 phách.', $d);
        $this->fill($s, 'Nốt nhạc nằm càng thấp trên khuông nhạc thì cao độ càng ___.', [[0, 'thấp']], 'Vị trí càng thấp trên khuông nhạc thì âm thanh càng trầm, cao độ càng thấp.', $d);
    }

    private function seedAnNhip2434(): void
    {
        $s = 'an-nhip-2-4-3-4';
        $d = 'trung_binh';

        $this->quiz($s, 'Trong nhịp 3/4, phách nào là phách mạnh?', ['Phách 1', 'Phách 2', 'Phách 3', 'Cả ba phách đều mạnh'], 0, 'Trong nhịp 3/4, phách đầu tiên là phách mạnh, hai phách sau là phách nhẹ.', $d);
        $this->quiz($s, 'Số 3 ở tử số của số chỉ nhịp 3/4 cho biết điều gì?', ['Mỗi ô nhịp có 3 phách', 'Có 3 nốt nhạc trong ô nhịp', 'Bài nhạc có 3 ô nhịp', 'Nhịp 3/4 dài gấp 3 lần nhịp 2/4'], 0, 'Tử số của số chỉ nhịp cho biết mỗi ô nhịp có bao nhiêu phách.', $d);
        $this->quiz($s, 'Tổ hợp nốt nào sau đây vừa đủ một ô nhịp 3/4 (đơn vị phách là nốt đen)?', ['1 nốt trắng và 1 nốt đen', '2 nốt trắng', '1 nốt tròn', '4 nốt đen'], 0, 'Một nốt trắng dài 2 phách cộng một nốt đen dài 1 phách vừa đủ 3 phách của ô nhịp 3/4.', $d);
        $this->quiz($s, 'Bài hành khúc (march) thường được viết ở nhịp nào?', ['2/4', '3/4', '6/8', 'Nhịp tự do'], 0, 'Nhịp 2/4 khỏe khoắn, dứt khoát rất hợp với các bài hành khúc.', $d);
        $this->quiz($s, 'Điệu valse (nhảy điệu waltz) thường được viết ở nhịp nào?', ['2/4', '3/4', '4/4', '6/8'], 1, 'Điệu valse uyển chuyển với 3 phách mỗi ô nhịp, thường được viết ở nhịp 3/4.', $d);

        $this->matching($s, 'Nối mỗi số chỉ nhịp với số phách trong một ô nhịp.', [['2/4', '2 phách'], ['3/4', '3 phách'], ['4/4', '4 phách'], ['2/2', '2 phách']], 'Tử số của số chỉ nhịp luôn cho biết số phách trong mỗi ô nhịp.', $d);
        $this->matching($s, 'Nối mỗi ký hiệu trong bản nhạc với ý nghĩa của nó.', [['Vạch nhịp', 'Ngăn cách các ô nhịp'], ['Vạch nhịp kép', 'Kết thúc một đoạn nhạc'], ['Số chỉ nhịp', 'Quy định số phách mỗi ô'], ['Dấu lặng', 'Chỗ im lặng theo nhịp']], 'Các ký hiệu này giúp người chơi nhạc đọc và giữ đúng nhịp của bản nhạc.', $d);
        $this->matching($s, 'Nối mỗi phách với tính chất của nó.', [['Phách 1 trong nhịp 3/4', 'Phách mạnh'], ['Phách 2 trong nhịp 3/4', 'Phách nhẹ'], ['Phách 3 trong nhịp 3/4', 'Phách nhẹ'], ['Phách 1 trong nhịp 2/4', 'Phách mạnh']], 'Phách đầu tiên của mọi ô nhịp luôn là phách mạnh nhất.', $d);
        $this->matching($s, 'Nối mỗi cách vỗ tay theo nhịp với loại nhịp tương ứng.', [['Vỗ theo kiểu "1-2, 1-2"', 'Nhịp 2/4'], ['Vỗ theo kiểu "1-2-3, 1-2-3"', 'Nhịp 3/4'], ['Vỗ theo kiểu "mạnh-nhẹ, mạnh-nhẹ"', 'Nhịp 2/4'], ['Vỗ theo kiểu "mạnh-nhẹ-nhẹ"', 'Nhịp 3/4']], 'Vỗ tay theo phách mạnh nhẹ giúp cảm nhận rõ đặc trưng của từng loại nhịp.', $d);
        $this->matching($s, 'Nối mỗi mô tả nhịp với tên nhịp đúng.', [['2 phách, mạnh-nhẹ', 'Nhịp 2/4'], ['3 phách, mạnh-nhẹ-nhẹ', 'Nhịp 3/4'], ['4 phách mỗi ô nhịp', 'Nhịp 4/4'], ['2 phách, đơn vị phách là nốt trắng', 'Nhịp 2/2']], 'Mẫu số của số chỉ nhịp cho biết đơn vị phách: 4 là nốt đen, 2 là nốt trắng.', $d);

        $this->sortQ($s, 'Kéo mỗi số chỉ nhịp vào nhóm NHỊP CÓ SỐ PHÁCH CHẴN hoặc NHỊP CÓ SỐ PHÁCH LẺ.', [['2/4', 'Số phách chẵn'], ['4/4', 'Số phách chẵn'], ['2/2', 'Số phách chẵn'], ['3/4', 'Số phách lẻ'], ['3/8', 'Số phách lẻ']], 'Nhịp 2/4 và 4/4 có số phách chẵn, nhịp 3/4 và 3/8 có số phách lẻ.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ hợp nốt (đơn vị phách là nốt đen) vào nhóm VỪA ĐỦ 1 Ô NHỊP 2/4 hoặc VỪA ĐỦ 1 Ô NHỊP 3/4.', [['2 nốt đen', 'Vừa đủ ô nhịp 2/4'], ['1 nốt trắng', 'Vừa đủ ô nhịp 2/4'], ['3 nốt đen', 'Vừa đủ ô nhịp 3/4'], ['1 nốt trắng và 1 nốt đen', 'Vừa đủ ô nhịp 3/4']], 'Tổng trường độ các nốt trong ô nhịp phải bằng đúng số phách của nhịp đó.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về nhịp 2/4 và 3/4.', [['Nhịp 2/4 mỗi ô nhịp có 2 phách', 'Đúng'], ['Phách 1 của nhịp 3/4 là phách mạnh', 'Đúng'], ['Số 4 ở mẫu số chỉ đơn vị phách là nốt đen', 'Đúng'], ['Nhịp 3/4 mỗi ô nhịp có 2 phách', 'Sai'], ['Nhịp 2/4 thường dùng cho điệu valse', 'Sai']], 'Nhịp 2/4 có 2 phách khỏe khoắn, nhịp 3/4 có 3 phách uyển chuyển.', $d);
        $this->sortQ($s, 'Kéo mỗi kiểu vỗ tay vào nhóm NHỊP 2/4 hoặc NHỊP 3/4.', [['"MẠNH-nhẹ, MẠNH-nhẹ"', 'Nhịp 2/4'], ['Vỗ 2 lần trong mỗi ô nhịp', 'Nhịp 2/4'], ['"MẠNH-nhẹ-nhẹ"', 'Nhịp 3/4'], ['Vỗ 3 lần trong mỗi ô nhịp', 'Nhịp 3/4']], 'Số lần vỗ trong một ô nhịp bằng đúng số phách của nhịp đó.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm NHỊP 2/4 hoặc NHỊP 3/4.', [['Khỏe khoắn, dứt khoát', 'Nhịp 2/4'], ['Thường dùng cho bài hành khúc', 'Nhịp 2/4'], ['Mỗi ô nhịp có 2 phách', 'Nhịp 2/4'], ['Uyển chuyển, du dương', 'Nhịp 3/4'], ['Thường dùng cho điệu valse', 'Nhịp 3/4'], ['Mỗi ô nhịp có 3 phách', 'Nhịp 3/4']], 'Cảm giác khi nghe giúp em đoán được bản nhạc đang ở nhịp 2/4 hay 3/4.', $d);

        $this->fill($s, 'Nhịp 3/4 mỗi ô nhịp có ___ phách.', [[0, '3']], 'Tử số 3 cho biết mỗi ô nhịp của nhịp 3/4 có 3 phách.', $d);
        $this->fill($s, 'Trong nhịp 2/4, phách thứ ___ là phách nhẹ.', [[0, '2']], 'Nhịp 2/4 có phách 1 mạnh và phách 2 nhẹ.', $d);
        $this->fill($s, 'Số 4 ở mẫu số của số chỉ nhịp cho biết đơn vị phách là nốt ___.', [[0, 'đen']], 'Mẫu số 4 nghĩa là lấy nốt đen làm đơn vị đếm phách.', $d);
        $this->fill($s, 'Điệu valse thường được viết ở nhịp ___.', [[0, '3/4']], 'Điệu valse 3 phách uyển chuyển gắn liền với nhịp 3/4.', $d);
        $this->fill($s, 'Trong nhịp 3/4, phách 2 và phách 3 đều là phách ___.', [[0, 'nhẹ']], 'Chỉ phách 1 là phách mạnh, các phách còn lại trong ô nhịp đều là phách nhẹ.', $d);
    }

    private function seedAnCacDauLang(): void
    {
        $s = 'an-cac-dau-lang';
        $d = 'trung_binh';

        $this->quiz($s, 'Dấu lặng nào có thời gian nghỉ dài nhất?', ['Dấu lặng tròn', 'Dấu lặng trắng', 'Dấu lặng đen', 'Dấu lặng móc đơn'], 0, 'Dấu lặng tròn nghỉ 4 phách, dài nhất trong các dấu lặng cơ bản.', $d);
        $this->quiz($s, 'Một ô nhịp 3/4 gồm 1 nốt trắng và 1 dấu lặng thì dấu lặng đó là loại nào?', ['Dấu lặng tròn', 'Dấu lặng trắng', 'Dấu lặng đen', 'Dấu lặng móc đơn'], 2, 'Nốt trắng dài 2 phách, ô nhịp 3/4 còn thiếu 1 phách nên cần dấu lặng đen.', $d);
        $this->quiz($s, 'Khi gặp dấu lặng móc đơn, người hát phải nghỉ bao lâu?', ['1 phách', 'Nửa phách', '2 phách', '4 phách'], 1, 'Dấu lặng móc đơn nghỉ nửa phách, bằng thời gian của một nốt móc đơn.', $d);
        $this->quiz($s, 'Dấu lặng trắng tương ứng với nốt nhạc nào?', ['Nốt tròn', 'Nốt trắng', 'Nốt đen', 'Nốt móc đơn'], 1, 'Mỗi dấu lặng tương ứng với một nốt nhạc có cùng trường độ, dấu lặng trắng ứng với nốt trắng.', $d);
        $this->quiz($s, 'Một ô nhịp 4/4 gồm 1 nốt trắng và 1 dấu lặng trắng có tổng cộng mấy phách?', ['2 phách', '3 phách', '4 phách', '5 phách'], 2, 'Nốt trắng dài 2 phách cộng dấu lặng trắng nghỉ 2 phách vừa đủ 4 phách của ô nhịp 4/4.', $d);

        $this->matching($s, 'Nối mỗi dấu lặng với mô tả hình dạng đặc trưng của nó.', [['Dấu lặng tròn', 'Hình chữ nhật treo dưới dòng kẻ'], ['Dấu lặng trắng', 'Hình chữ nhật nằm trên dòng kẻ'], ['Dấu lặng đen', 'Hình tia chớp zíc zắc'], ['Dấu lặng móc đơn', 'Hình móc cong']], 'Nhìn hình dạng là em có thể nhận ra ngay loại dấu lặng trong bản nhạc.', $d);
        $this->matching($s, 'Nối mỗi ô nhịp với dấu lặng cần thêm để vừa đủ số phách.', [['Ô nhịp 4/4 đã có 1 nốt trắng', 'Dấu lặng trắng'], ['Ô nhịp 2/4 đã có 1 nốt đen', 'Dấu lặng đen'], ['Ô nhịp 3/4 đã có 1 nốt trắng', 'Dấu lặng đen'], ['Ô nhịp 4/4 đã có 1 nốt tròn', 'Không cần thêm dấu lặng']], 'Dấu lặng bù vào phần còn thiếu để tổng thời gian vừa đúng số phách của ô nhịp.', $d);
        $this->matching($s, 'Nối mỗi dấu lặng với nốt nhạc dài bằng nó.', [['Dấu lặng tròn', 'Nốt tròn'], ['Dấu lặng trắng', 'Nốt trắng'], ['Dấu lặng đen', 'Nốt đen'], ['Dấu lặng móc đơn', 'Nốt móc đơn']], 'Dấu lặng và nốt nhạc tương ứng luôn có trường độ bằng nhau.', $d);
        $this->matching($s, 'Nối mỗi tình huống khi gặp dấu lặng với việc cần làm.', [['Gặp dấu lặng đen', 'Nghỉ đúng 1 phách'], ['Gặp dấu lặng ở đầu ô nhịp', 'Giữ im lặng rồi hát tiếp'], ['Gặp nhiều dấu lặng liên tiếp', 'Đếm phách đều trong đầu'], ['Gặp dấu lặng ở cuối bài', 'Kết thúc gọn gàng']], 'Đếm phách đều trong đầu giúp em không bị lỡ nhịp khi gặp dấu lặng.', $d);
        $this->matching($s, 'Nối mỗi dấu lặng với số nốt móc đơn có thời gian bằng nó.', [['Dấu lặng tròn', '8 nốt móc đơn'], ['Dấu lặng trắng', '4 nốt móc đơn'], ['Dấu lặng đen', '2 nốt móc đơn'], ['Dấu lặng móc đơn', '1 nốt móc đơn']], 'Quy đổi ra nốt móc đơn giúp em hình dung rõ độ dài nghỉ của từng dấu lặng.', $d);

        $this->sortQ($s, 'Kéo mỗi dấu lặng vào nhóm NGHỈ TỪ 2 PHÁCH TRỞ LÊN hoặc NGHỈ DƯỚI 2 PHÁCH.', [['Dấu lặng tròn', 'Từ 2 phách trở lên'], ['Dấu lặng trắng', 'Từ 2 phách trở lên'], ['Dấu lặng đen', 'Dưới 2 phách'], ['Dấu lặng móc đơn', 'Dưới 2 phách']], 'Dấu lặng tròn nghỉ 4 phách, lặng trắng nghỉ 2 phách, lặng đen nghỉ 1 phách, lặng móc đơn nghỉ nửa phách.', $d);
        $this->sortQ($s, 'Kéo mỗi dấu lặng vào nhóm NGHỈ TRÒN PHÁCH hoặc NGHỈ NỬA PHÁCH.', [['Dấu lặng tròn', 'Nghỉ tròn phách'], ['Dấu lặng trắng', 'Nghỉ tròn phách'], ['Dấu lặng đen', 'Nghỉ tròn phách'], ['Dấu lặng móc đơn', 'Nghỉ nửa phách']], 'Chỉ dấu lặng móc đơn là nghỉ nửa phách, các dấu lặng còn lại đều nghỉ tròn phách.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về dấu lặng.', [['Dấu lặng là chỗ im lặng trong bản nhạc', 'Đúng'], ['Dấu lặng đen nghỉ bằng 1 nốt đen', 'Đúng'], ['Dấu lặng tròn nghỉ lâu nhất', 'Đúng'], ['Khi gặp dấu lặng vẫn hát tiếp', 'Sai'], ['Dấu lặng chỉ dùng ở cuối bài', 'Sai']], 'Dấu lặng có thể xuất hiện ở bất cứ đâu trong bản nhạc, chỗ nào cần im lặng.', $d);
        $this->sortQ($s, 'Kéo mỗi dấu lặng vào nhóm HÌNH CHỮ NHẬT hoặc HÌNH DẠNG KHÁC.', [['Dấu lặng tròn', 'Hình chữ nhật'], ['Dấu lặng trắng', 'Hình chữ nhật'], ['Dấu lặng đen', 'Hình dạng khác'], ['Dấu lặng móc đơn', 'Hình dạng khác']], 'Dấu lặng tròn và lặng trắng đều có dạng hình chữ nhật nhỏ trên khuông nhạc.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ hợp trong ô nhịp 2/4 vào nhóm VỪA ĐỦ hoặc CÒN THIẾU.', [['1 nốt đen và 1 dấu lặng đen', 'Vừa đủ'], ['2 nốt đen', 'Vừa đủ'], ['1 dấu lặng trắng', 'Vừa đủ'], ['1 nốt đen', 'Còn thiếu'], ['1 nốt móc đơn và 1 dấu lặng đen', 'Còn thiếu']], 'Ô nhịp 2/4 cần tổng cộng 2 phách, thiếu thì phải bù thêm nốt hoặc dấu lặng.', $d);

        $this->fill($s, 'Dấu lặng tròn có thời gian nghỉ bằng ___ nốt đen.', [[0, '4']], 'Dấu lặng tròn nghỉ 4 phách, bằng thời gian của 4 nốt đen.', $d);
        $this->fill($s, 'Dấu lặng ___ có thời gian nghỉ bằng một nốt trắng.', [[0, 'trắng']], 'Dấu lặng trắng nghỉ 2 phách, bằng đúng một nốt trắng.', $d);
        $this->fill($s, 'Khi gặp dấu lặng, người hát phải giữ ___ trong đúng số phách quy định.', [[0, 'im lặng']], 'Dấu lặng là ký hiệu chỉ chỗ người hát tạm ngừng phát ra âm thanh.', $d);
        $this->fill($s, 'Một ô nhịp 2/4 đã có 1 nốt đen thì cần thêm 1 dấu lặng ___ để vừa đủ.', [[0, 'đen']], 'Ô nhịp 2/4 còn thiếu 1 phách nên cần thêm dấu lặng đen.', $d);
        $this->fill($s, 'Dấu lặng móc đơn có thời gian nghỉ bằng ___ phách.', [[0, 'nửa']], 'Dấu lặng móc đơn nghỉ nửa phách, ngắn nhất trong các dấu lặng cơ bản.', $d);
    }

    private function seedAnDanBauTranhSaoTrong(): void
    {
        $s = 'an-dan-bau-tranh-sao-trong';
        $d = 'de';

        $this->quiz($s, 'Đàn bầu phát ra âm thanh nhờ bộ phận nào?', ['Dây đàn rung khi gảy', 'Mặt trống khi vỗ', 'Ống sáo khi thổi', 'Phím đàn khi bấm'], 0, 'Đàn bầu chỉ có một dây, người chơi gảy dây để tạo ra âm thanh ngân nga.', $d);
        $this->quiz($s, 'Sáo trúc được chơi bằng cách nào?', ['Thổi hơi', 'Gảy dây', 'Vỗ mặt trống', 'Kéo vĩ'], 0, 'Sáo là nhạc cụ bộ hơi, người chơi thổi hơi vào miệng sáo và bấm các lỗ để tạo nốt.', $d);
        $this->quiz($s, 'Đàn tranh được chơi bằng cách nào?', ['Gảy dây', 'Thổi hơi', 'Vỗ tay', 'Kéo vĩ'], 0, 'Người chơi đàn tranh dùng móng gảy để gảy các dây đàn.', $d);
        $this->quiz($s, 'Trống cơm có hình dạng đặc biệt như thế nào?', ['Hai mặt da, thân phình ở giữa', 'Chỉ có một mặt da', 'Hình ống dài', 'Hình tròn dẹt như cái nia'], 0, 'Trống cơm bịt da hai mặt, thân phình ở giữa, người chơi vỗ hai tay lên mặt trống.', $d);
        $this->quiz($s, 'Nhạc cụ nào sau đây thuộc bộ gõ?', ['Trống cơm', 'Đàn bầu', 'Sáo', 'Đàn tranh'], 0, 'Trống cơm tạo ra âm thanh bằng cách vỗ lên mặt trống nên thuộc bộ gõ.', $d);

        $this->matching($s, 'Nối mỗi nhạc cụ với đặc điểm cấu tạo nổi bật của nó.', [['Đàn bầu', 'Chỉ có một dây đàn'], ['Trống cơm', 'Bịt da ở hai mặt'], ['Sáo', 'Có các lỗ bấm trên ống'], ['Đàn tranh', 'Có nhiều dây đàn']], 'Mỗi nhạc cụ dân tộc đều có cấu tạo riêng tạo nên âm sắc đặc trưng.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với động tác chơi nhạc cụ đó.', [['Đàn bầu', 'Gảy dây bằng que tre'], ['Sáo', 'Thổi hơi và bấm lỗ'], ['Trống cơm', 'Vỗ hai tay lên mặt trống'], ['Đàn tranh', 'Gảy dây bằng móng gảy']], 'Cách chơi khác nhau tạo nên tiếng đàn, tiếng sáo, tiếng trống rất riêng.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với chất liệu chính làm nên nó.', [['Đàn bầu', 'Gỗ và dây kim loại'], ['Sáo', 'Ống trúc'], ['Trống cơm', 'Gỗ và da'], ['Đàn tranh', 'Gỗ và dây tơ']], 'Nhạc cụ dân tộc thường làm từ những vật liệu gần gũi trong đời sống.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với vai trò trong dàn nhạc dân tộc.', [['Đàn bầu', 'Tạo giai điệu ngân nga'], ['Sáo', 'Thổi giai điệu chính'], ['Trống cơm', 'Giữ nhịp cho cả dàn nhạc'], ['Đàn tranh', 'Đệm và tấu giai điệu']], 'Mỗi nhạc cụ đảm nhận một vai trò riêng làm nên bản hòa tấu hay.', $d);
        $this->matching($s, 'Nối mỗi bộ nhạc cụ với nhạc cụ đại diện.', [['Bộ dây', 'Đàn bầu'], ['Bộ hơi', 'Sáo'], ['Bộ gõ', 'Trống cơm'], ['Bộ dây gảy', 'Đàn tranh']], 'Nhạc cụ dân tộc được chia thành các bộ: dây, hơi, gõ theo cách tạo ra âm thanh.', $d);

        $this->sortQ($s, 'Kéo mỗi nhạc cụ vào nhóm CHƠI BẰNG TAY hoặc CHƠI BẰNG MIỆNG.', [['Đàn bầu', 'Chơi bằng tay'], ['Đàn tranh', 'Chơi bằng tay'], ['Trống cơm', 'Chơi bằng tay'], ['Sáo', 'Chơi bằng miệng']], 'Chỉ sáo là nhạc cụ thổi bằng miệng, các nhạc cụ còn lại đều chơi bằng tay.', $d);
        $this->sortQ($s, 'Kéo mỗi nhạc cụ vào nhóm CÓ DÂY hoặc KHÔNG CÓ DÂY.', [['Đàn bầu', 'Có dây'], ['Đàn tranh', 'Có dây'], ['Sáo', 'Không có dây'], ['Trống cơm', 'Không có dây']], 'Đàn bầu và đàn tranh thuộc bộ dây, sáo thuộc bộ hơi, trống cơm thuộc bộ gõ.', $d);
        $this->sortQ($s, 'Kéo mỗi nhạc cụ vào nhóm DÙNG HƠI THỔI hoặc KHÔNG DÙNG HƠI THỔI.', [['Sáo', 'Dùng hơi thổi'], ['Đàn bầu', 'Không dùng hơi thổi'], ['Đàn tranh', 'Không dùng hơi thổi'], ['Trống cơm', 'Không dùng hơi thổi']], 'Sáo là nhạc cụ duy nhất trong bốn nhạc cụ này dùng hơi thổi để tạo âm thanh.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về nhạc cụ dân tộc.', [['Đàn bầu chỉ có một dây', 'Đúng'], ['Sáo thuộc bộ hơi', 'Đúng'], ['Đàn tranh có nhiều dây', 'Đúng'], ['Trống cơm chơi bằng cách thổi', 'Sai'], ['Đàn bầu thuộc bộ gõ', 'Sai']], 'Đàn bầu là nhạc cụ độc đáo chỉ có một dây của Việt Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi nhạc cụ vào nhóm NHẠC CỤ VIỆT NAM hoặc NHẠC CỤ PHƯƠNG TÂY.', [['Đàn bầu', 'Nhạc cụ Việt Nam'], ['Sáo trúc', 'Nhạc cụ Việt Nam'], ['Trống cơm', 'Nhạc cụ Việt Nam'], ['Đàn tranh', 'Nhạc cụ Việt Nam'], ['Violin', 'Nhạc cụ phương Tây'], ['Piano', 'Nhạc cụ phương Tây']], 'Đàn bầu, sáo trúc, trống cơm, đàn tranh đều là nhạc cụ dân tộc của Việt Nam.', $d);

        $this->fill($s, 'Đàn bầu tạo ra âm thanh khi người chơi ___ dây đàn.', [[0, 'gảy']], 'Người chơi đàn bầu dùng que tre gảy vào dây đàn duy nhất để tạo âm thanh.', $d);
        $this->fill($s, 'Sáo trúc là nhạc cụ thuộc bộ hơi, chơi bằng cách ___ hơi vào ống sáo.', [[0, 'thổi']], 'Người chơi sáo thổi hơi vào miệng sáo và bấm các lỗ để tạo ra các nốt nhạc.', $d);
        $this->fill($s, 'Trống cơm có ___ mặt da.', [[0, '2']], 'Trống cơm được bịt da ở cả hai mặt, người chơi vỗ hai tay lên mặt trống.', $d);
        $this->fill($s, 'Người chơi đàn tranh dùng ___ để gảy dây.', [[0, 'móng gảy']], 'Móng gảy đeo ở đầu ngón tay giúp tiếng đàn tranh trong và rõ hơn.', $d);
        $this->fill($s, 'Đàn bầu còn được gọi là độc huyền cầm vì chỉ có ___ dây.', [[0, 'một']], 'Độc huyền cầm nghĩa là đàn một dây, tên gọi khác của đàn bầu.', $d);
    }

    private function seedAnHatBaiThieuNhi(): void
    {
        $s = 'an-hat-bai-thieu-nhi';
        $d = 'de';

        $this->quiz($s, 'Khi hát, tư thế đúng là như thế nào?', ['Đứng hoặc ngồi thẳng lưng, thả lỏng vai', 'Cúi gập người xuống', 'Ngửa cổ ra sau', 'Nằm hát cho thoải mái'], 0, 'Tư thế thẳng lưng, thả lỏng vai giúp hơi thở lưu thông và giọng hát vang rõ.', $d);
        $this->quiz($s, 'Kỹ thuật lấy hơi đúng khi hát là gì?', ['Hít sâu, lấy hơi bằng bụng', 'Hít nông bằng ngực', 'Vừa hát vừa thở gấp', 'Nín thở suốt câu hát'], 0, 'Lấy hơi sâu bằng bụng giúp em có đủ hơi để hát trọn vẹn từng câu hát.', $d);
        $this->quiz($s, 'Hát to quá mức so với cả lớp khi hát tập thể sẽ gây ra điều gì?', ['Làm át tiếng mọi người, mất sự hòa hợp', 'Giúp bài hát hay hơn', 'Không ảnh hưởng gì', 'Được thầy cô khen ngợi'], 0, 'Hát tập thể cần mọi người hát vừa phải, hòa giọng với nhau mới hay.', $d);
        $this->quiz($s, 'Muốn thuộc lời bài hát nhanh, cách nào hiệu quả nhất?', ['Nghe và hát theo nhiều lần', 'Chỉ đọc lời một lần', 'Hát thật to một lần', 'Nhờ người khác hát hộ'], 0, 'Nghe và hát theo nhiều lần giúp em thuộc cả giai điệu lẫn lời bài hát.', $d);
        $this->quiz($s, 'Khi hát những nốt cao, em cần làm gì?', ['Mở rộng miệng, giữ hơi đều', 'Cố gào thật to', 'Bịt mũi lại', 'Cúi đầu xuống'], 0, 'Mở rộng khoang miệng và giữ hơi đều giúp nốt cao vang mà không bị chói.', $d);

        $this->matching($s, 'Nối mỗi bộ phận cơ thể với vai trò của nó khi hát.', [['Phổi', 'Cung cấp hơi'], ['Cổ họng', 'Tạo ra âm thanh'], ['Miệng', 'Phát âm rõ lời'], ['Tai', 'Nghe và điều chỉnh giọng']], 'Hát hay là sự phối hợp nhịp nhàng của hơi thở, cổ họng, miệng và đôi tai.', $d);
        $this->matching($s, 'Nối mỗi kỹ thuật hát với tác dụng của nó.', [['Lấy hơi sâu', 'Hát được câu dài'], ['Mở miệng rộng', 'Âm thanh vang rõ'], ['Nghe nhạc đệm', 'Hát đúng nhịp'], ['Thả lỏng cơ thể', 'Giọng hát tự nhiên']], 'Nắm vững các kỹ thuật cơ bản giúp giọng hát của em ngày càng tiến bộ.', $d);
        $this->matching($s, 'Nối mỗi lỗi thường gặp khi hát với cách khắc phục.', [['Hát lệch tông', 'Nghe kỹ giai điệu mẫu'], ['Hát hụt hơi', 'Luyện lấy hơi sâu'], ['Hát sai nhịp', 'Đếm nhịp theo nhạc đệm'], ['Quên lời bài hát', 'Nghe và hát theo nhiều lần']], 'Ai cũng mắc lỗi khi mới tập hát, quan trọng là biết cách sửa.', $d);
        $this->matching($s, 'Nối mỗi cách luyện tập với kết quả đạt được.', [['Khởi động giọng', 'Giọng ấm và linh hoạt'], ['Hát to dần từng ngày', 'Tăng sức bền hơi'], ['Hát theo nhóm', 'Rèn sự hòa hợp'], ['Ghi âm lại giọng hát', 'Nghe và tự sửa lỗi']], 'Luyện tập đều đặn mỗi ngày giúp giọng hát khỏe và hay hơn.', $d);
        $this->matching($s, 'Nối mỗi yếu tố của bài hát thiếu nhi với ý nghĩa của nó.', [['Giai điệu vui tươi', 'Tạo cảm giác vui vẻ'], ['Lời ca trong sáng', 'Dễ nhớ dễ thuộc'], ['Nhịp điệu rõ ràng', 'Dễ hát theo'], ['Nội dung gần gũi', 'Gắn với tuổi thơ']], 'Bài hát thiếu nhi hay có giai điệu vui, lời ca trong sáng và gần gũi với các em.', $d);

        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm GIÚP GIỌNG KHỎE hoặc HẠI GIỌNG HÁT.', [['Uống nước ấm', 'Giúp giọng khỏe'], ['Khởi động giọng trước khi hát', 'Giúp giọng khỏe'], ['Ngủ đủ giấc', 'Giúp giọng khỏe'], ['Hét to liên tục', 'Hại giọng hát'], ['Uống nước đá lạnh', 'Hại giọng hát'], ['Ăn đồ cay nóng nhiều', 'Hại giọng hát']], 'Bảo vệ giọng hát bằng thói quen tốt mỗi ngày rất quan trọng với người thích hát.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN trước giờ biểu diễn.', [['Khởi động giọng', 'Nên'], ['Uống nước ấm', 'Nên'], ['Thở sâu để thư giãn', 'Nên'], ['Ăn no căng bụng', 'Không nên'], ['Hét đùa giỡn', 'Không nên']], 'Chuẩn bị kỹ trước giờ biểu diễn giúp em tự tin và hát hay hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi âm thanh vào nhóm TIẾNG HÁT hoặc TIẾNG NHẠC CỤ.', [['Giọng hát của bạn', 'Tiếng hát'], ['Tiếng hát bè', 'Tiếng hát'], ['Tiếng đàn ghi-ta', 'Tiếng nhạc cụ'], ['Tiếng sáo', 'Tiếng nhạc cụ'], ['Tiếng trống', 'Tiếng nhạc cụ']], 'Tiếng hát là nhạc cụ tự nhiên nhất mà ai cũng có.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm HÁT ĐÚNG NHỊP hoặc HÁT SAI NHỊP.', [['Nghe nhạc đệm và hát theo', 'Hát đúng nhịp'], ['Đếm nhịp trong đầu khi hát', 'Hát đúng nhịp'], ['Hát vội vàng không nghe nhạc', 'Hát sai nhịp'], ['Ngừng hát giữa chừng không theo nhịp', 'Hát sai nhịp']], 'Luôn nghe nhạc đệm và đếm nhịp trong đầu để không bị sai nhịp.', $d);
        $this->sortQ($s, 'Kéo mỗi cảm xúc vào nhóm PHÙ HỢP hoặc KHÔNG PHÙ HỢP khi hát bài thiếu nhi vui tươi.', [['Vui vẻ', 'Phù hợp'], ['Hồn nhiên', 'Phù hợp'], ['Buồn bã', 'Không phù hợp'], ['Giận dữ', 'Không phù hợp']], 'Hát bài thiếu nhi vui tươi cần cảm xúc vui vẻ, hồn nhiên mới truyền cảm.', $d);

        $this->fill($s, 'Khi hát, em cần đứng thẳng lưng và ___ lỏng vai.', [[0, 'thả']], 'Thả lỏng vai giúp hơi thở lưu thông tốt, giọng hát tự nhiên hơn.', $d);
        $this->fill($s, 'Muốn hát được câu dài, em phải lấy hơi thật ___.', [[0, 'sâu']], 'Hơi sâu dự trữ nhiều không khí giúp em hát trọn câu dài không bị hụt hơi.', $d);
        $this->fill($s, 'Hát ___ tông nghĩa là hát sai cao độ so với nhạc đệm.', [[0, 'lệch']], 'Hát lệch tông là hát cao hơn hoặc thấp hơn tông của nhạc đệm.', $d);
        $this->fill($s, 'Khi hát tập thể, em phải nghe ___ để hát cùng nhịp với mọi người.', [[0, 'nhạc đệm']], 'Nhạc đệm là chỗ dựa giúp cả lớp hát đều nhịp và đúng giai điệu.', $d);
        $this->fill($s, 'Trước khi biểu diễn, ca sĩ thường ___ giọng để giọng ấm lên.', [[0, 'khởi động']], 'Khởi động giọng vài phút giúp cổ họng linh hoạt, tránh bị khàn khi hát.', $d);
    }

    private function seedAnNotNhacCaoDoLop61(): void
    {
        $s = 'an-not-nhac-cao-do-lop-6-1';
        $d = 'de';

        $this->quiz($s, 'Nốt nhạc được ký hiệu bằng chữ F là nốt nào?', ['Đô', 'Mi', 'Fa', 'Son'], 2, 'Trong ký hiệu quốc tế, chữ F ứng với nốt Fa.', $d);
        $this->quiz($s, 'Trong thang nhạc 7 nốt, nốt La là nốt thứ mấy?', ['Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'], 2, 'Thứ tự 7 nốt là Đô (1), Rê (2), Mi (3), Fa (4), Son (5), La (6), Si (7).', $d);
        $this->quiz($s, 'Nốt nào có cao độ cao hơn nốt Mi một bậc?', ['Rê', 'Fa', 'Son', 'Đô'], 1, 'Nốt đứng ngay sau nốt Mi trong thang nhạc là nốt Fa.', $d);
        $this->quiz($s, 'Ký hiệu chữ E ứng với nốt nhạc nào?', ['Đô', 'Rê', 'Mi', 'Fa'], 2, 'Trong ký hiệu quốc tế, chữ E ứng với nốt Mi.', $d);
        $this->quiz($s, 'Hai nốt nào sau đây kề nhau trong thang nhạc?', ['Đô - Mi', 'Rê - Fa', 'Mi - Fa', 'Fa - La'], 2, 'Nốt Mi và nốt Fa đứng liền nhau trong thang nhạc 7 nốt.', $d);

        $this->matching($s, 'Nối mỗi nốt nhạc với nốt cao hơn nó một bậc.', [['Đô', 'Rê'], ['Mi', 'Fa'], ['Fa', 'Son'], ['La', 'Si']], 'Nốt cao hơn một bậc là nốt đứng ngay sau nó trong thang nhạc.', $d);
        $this->matching($s, 'Nối mỗi cặp nốt với số bậc cách nhau giữa chúng.', [['Đô - Mi', '2 bậc'], ['Rê - Son', '3 bậc'], ['Đô - Son', '4 bậc'], ['Mi - Si', '4 bậc']], 'Đếm số nốt từ nốt dưới lên nốt trên sẽ ra số bậc cách nhau.', $d);
        $this->matching($s, 'Nối mỗi nốt với nốt thấp hơn nó một bậc.', [['Rê', 'Đô'], ['Fa', 'Mi'], ['Son', 'Fa'], ['Si', 'La']], 'Nốt thấp hơn một bậc là nốt đứng ngay trước nó trong thang nhạc.', $d);
        $this->matching($s, 'Nối mỗi hình nốt với số phách của nó (đơn vị phách là nốt đen).', [['Nốt tròn', '4 phách'], ['Nốt trắng', '2 phách'], ['Nốt đen', '1 phách'], ['Nốt móc đơn', 'Nửa phách']], 'Hình dạng nốt nhạc cho biết nốt ngân dài hay ngắn.', $d);
        $this->matching($s, 'Nối mỗi nốt với số thứ tự khi đếm ngược từ nốt Si.', [['Si', 'Số 1'], ['La', 'Số 2'], ['Son', 'Số 3'], ['Fa', 'Số 4']], 'Đếm ngược từ Si giúp em thuộc thứ tự nốt theo cả hai chiều.', $d);

        $this->sortQ($s, 'Kéo mỗi nốt vào nhóm ĐỨNG TRƯỚC NỐT FA hoặc ĐỨNG SAU NỐT FA.', [['Đô', 'Trước nốt Fa'], ['Rê', 'Trước nốt Fa'], ['Mi', 'Trước nốt Fa'], ['Son', 'Sau nốt Fa'], ['La', 'Sau nốt Fa'], ['Si', 'Sau nốt Fa']], 'Nốt Fa là nốt thứ tư, chia thang nhạc thành hai nửa bằng nhau.', $d);
        $this->sortQ($s, 'Kéo mỗi nốt vào nhóm KÝ HIỆU QUỐC TẾ LÀ NGUYÊN ÂM hoặc PHỤ ÂM.', [['La (A)', 'Nguyên âm'], ['Mi (E)', 'Nguyên âm'], ['Đô (C)', 'Phụ âm'], ['Rê (D)', 'Phụ âm'], ['Fa (F)', 'Phụ âm'], ['Son (G)', 'Phụ âm']], 'Trong 7 ký hiệu A đến G, chỉ A và E là nguyên âm.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về nốt nhạc.', [['Nốt Si là nốt cuối của thang nhạc', 'Đúng'], ['Ký hiệu G là nốt Son', 'Đúng'], ['Có 7 nốt nhạc cơ bản', 'Đúng'], ['Nốt Rê đứng sau nốt Mi', 'Sai'], ['Nốt La đứng trước nốt Son', 'Sai']], 'Thuộc thứ tự Đô, Rê, Mi, Fa, Son, La, Si giúp em trả lời đúng mọi câu hỏi.', $d);
        $this->sortQ($s, 'Kéo mỗi nốt vào nhóm THẤP HƠN NỐT SON hoặc CAO HƠN NỐT SON.', [['Đô', 'Thấp hơn nốt Son'], ['Mi', 'Thấp hơn nốt Son'], ['Fa', 'Thấp hơn nốt Son'], ['La', 'Cao hơn nốt Son'], ['Si', 'Cao hơn nốt Son']], 'Nốt Son là nốt thứ năm, các nốt trước nó thấp hơn, các nốt sau nó cao hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp ký hiệu - tên nốt vào nhóm ĐÚNG hoặc SAI.', [['C - Đô', 'Đúng'], ['D - Rê', 'Đúng'], ['F - Fa', 'Đúng'], ['E - Mi', 'Đúng'], ['B - La', 'Sai'], ['G - Si', 'Sai']], 'B là nốt Si và G là nốt Son, đừng nhầm lẫn hai ký hiệu này.', $d);

        $this->fill($s, 'Nốt nhạc thứ ba trong thang nhạc là nốt ___.', [[0, 'Mi']], 'Đếm từ Đô lên: Đô (1), Rê (2), Mi (3).', $d);
        $this->fill($s, 'Ký hiệu quốc tế của nốt Fa là chữ ___.', [[0, 'F']], 'Nốt Fa ứng với chữ F trong hệ ký hiệu 7 chữ cái.', $d);
        $this->fill($s, 'Nốt ___ đứng ngay sau nốt Mi.', [[0, 'Fa']], 'Nốt Fa là nốt thứ tư, đứng ngay sau nốt Mi.', $d);
        $this->fill($s, 'Từ nốt Đô đếm lên, nốt thứ sáu là nốt ___.', [[0, 'La']], 'Đô (1), Rê (2), Mi (3), Fa (4), Son (5), La (6).', $d);
        $this->fill($s, 'Trong 7 nốt nhạc, nốt ___ có cao độ cao nhất.', [[0, 'Si']], 'Nốt Si là nốt thứ bảy, nốt cao nhất của thang nhạc cơ bản.', $d);
    }

    private function seedAnNotNhacCaoDoLop62(): void
    {
        $s = 'an-not-nhac-cao-do-lop-6-2';
        $d = 'de';

        $this->quiz($s, 'Các dòng kẻ của khuông nhạc được đánh số từ đâu?', ['Từ dưới lên trên', 'Từ trên xuống dưới', 'Từ giữa ra hai bên', 'Tùy ý người viết nhạc'], 0, 'Dòng kẻ của khuông nhạc được đánh số từ 1 đến 5 theo thứ tự từ dưới lên trên.', $d);
        $this->quiz($s, 'Nốt nhạc nằm ngoài khuông nhạc được viết thêm gì?', ['Dòng kẻ phụ', 'Dấu chấm', 'Dấu sao', 'Không viết thêm gì'], 0, 'Dòng kẻ phụ là những dòng kẻ ngắn vẽ thêm để viết các nốt nằm ngoài khuông nhạc.', $d);
        $this->quiz($s, 'Khóa Son có hình dạng giống chữ cái nào?', ['G', 'S', 'C', 'O'], 0, 'Khóa Son có hình xoắn giống chữ G, vòng xoắn ôm lấy dòng kẻ thứ hai là vị trí nốt Son.', $d);
        $this->quiz($s, 'Khe nhạc nằm giữa dòng kẻ thứ nhất và thứ hai (khóa Son) chứa nốt nào?', ['Mi', 'Fa', 'Son', 'La'], 1, 'Bốn khe nhạc từ dưới lên lần lượt chứa các nốt Fa, La, Đô, Mi.', $d);
        $this->quiz($s, 'Nốt La trong khóa Son nằm ở đâu?', ['Dòng kẻ thứ hai', 'Khe thứ hai', 'Dòng kẻ thứ ba', 'Ngoài khuông nhạc'], 1, 'Nốt La nằm trong khe thứ hai, tức là khoảng trống giữa dòng kẻ thứ hai và thứ ba.', $d);

        $this->matching($s, 'Nối mỗi dòng kẻ (đếm từ dưới lên, khóa Son) với nốt nhạc trên đó.', [['Dòng kẻ 1', 'Mi'], ['Dòng kẻ 2', 'Son'], ['Dòng kẻ 3', 'Si'], ['Dòng kẻ 5', 'Fa']], 'Năm dòng kẻ từ dưới lên lần lượt là các nốt Mi, Son, Si, Rê, Fa.', $d);
        $this->matching($s, 'Nối mỗi nốt nhạc trong khe với số thứ tự khe của nó (khóa Son).', [['Fa', 'Khe 1'], ['La', 'Khe 2'], ['Đô', 'Khe 3'], ['Mi', 'Khe 4']], 'Bốn khe nhạc từ dưới lên lần lượt chứa các nốt Fa, La, Đô, Mi.', $d);
        $this->matching($s, 'Nối mỗi bộ phận của khuông nhạc với mô tả đúng của nó.', [['Dòng kẻ', 'Đường kẻ ngang dài'], ['Khe nhạc', 'Khoảng trống giữa hai dòng kẻ'], ['Khóa nhạc', 'Ký hiệu viết ở đầu khuông'], ['Dòng kẻ phụ', 'Dòng kẻ ngắn vẽ thêm ngoài khuông']], 'Khuông nhạc gồm 5 dòng kẻ tạo thành 4 khe nhạc, mở đầu bằng khóa nhạc.', $d);
        $this->matching($s, 'Nối mỗi nốt nhạc với vị trí của nó so với nốt Son (khóa Son).', [['Mi', 'Thấp hơn nốt Son'], ['Fa', 'Thấp hơn nốt Son'], ['La', 'Cao hơn nốt Son'], ['Si', 'Cao hơn nốt Son']], 'Nốt Son nằm trên dòng kẻ thứ hai, là mốc để xác định các nốt xung quanh.', $d);
        $this->matching($s, 'Nối mỗi mô tả vị trí với tên nốt đúng (khóa Son).', [['Nốt trên dòng kẻ thứ nhất', 'Mi'], ['Nốt trong khe thứ hai', 'La'], ['Nốt trên dòng kẻ thứ năm', 'Fa'], ['Nốt trên dòng kẻ thứ ba', 'Si']], 'Nhớ vị trí từng nốt trên khuông nhạc khóa Son giúp em đọc nhạc nhanh.', $d);

        $this->sortQ($s, 'Kéo mỗi nốt (khóa Son) vào nhóm TRÊN DÒNG KẺ 1-2 hoặc TRÊN DÒNG KẺ 4-5.', [['Mi', 'Dòng kẻ 1-2'], ['Son', 'Dòng kẻ 1-2'], ['Rê', 'Dòng kẻ 4-5'], ['Fa', 'Dòng kẻ 4-5']], 'Nốt Mi ở dòng 1, nốt Son ở dòng 2, nốt Rê ở dòng 4, nốt Fa ở dòng 5.', $d);
        $this->sortQ($s, 'Kéo mỗi nốt (khóa Son) vào nhóm TRONG KHE LẺ (1, 3) hoặc TRONG KHE CHẴN (2, 4).', [['Fa', 'Khe lẻ'], ['Đô', 'Khe lẻ'], ['La', 'Khe chẵn'], ['Mi', 'Khe chẵn']], 'Nốt Fa ở khe 1, nốt Đô ở khe 3, nốt La ở khe 2, nốt Mi ở khe 4.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về khuông nhạc.', [['Khuông nhạc có 5 dòng kẻ', 'Đúng'], ['Dòng kẻ đánh số từ dưới lên trên', 'Đúng'], ['Nốt Son nằm trên dòng kẻ thứ hai', 'Đúng'], ['Khóa Son viết ở cuối khuông nhạc', 'Sai'], ['Khuông nhạc có 5 khe nhạc', 'Sai']], 'Khuông nhạc có 5 dòng kẻ tạo thành 4 khe nhạc, khóa nhạc viết ở đầu khuông.', $d);
        $this->sortQ($s, 'Kéo mỗi nốt (khóa Son) vào nhóm CAO HƠN NỐT SON hoặc THẤP HƠN NỐT SON.', [['La', 'Cao hơn nốt Son'], ['Si', 'Cao hơn nốt Son'], ['Đô', 'Cao hơn nốt Son'], ['Mi', 'Thấp hơn nốt Son'], ['Fa', 'Thấp hơn nốt Son']], 'Các nốt nằm phía trên dòng kẻ thứ hai cao hơn nốt Son, phía dưới thì thấp hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi thứ vào nhóm CÓ TRÊN KHUÔNG NHẠC hoặc KHÔNG CÓ TRÊN KHUÔNG NHẠC.', [['Dòng kẻ', 'Có trên khuông nhạc'], ['Khóa nhạc', 'Có trên khuông nhạc'], ['Nốt nhạc', 'Có trên khuông nhạc'], ['Con chim', 'Không có trên khuông nhạc'], ['Bông hoa', 'Không có trên khuông nhạc']], 'Trên khuông nhạc chỉ có dòng kẻ, khe nhạc, khóa nhạc, nốt nhạc và các ký hiệu âm nhạc.', $d);

        $this->fill($s, 'Các dòng kẻ của khuông nhạc được đánh số từ ___ lên trên.', [[0, 'dưới']], 'Dòng kẻ số 1 là dòng dưới cùng, số 5 là dòng trên cùng.', $d);
        $this->fill($s, 'Nốt nhạc nằm ngoài khuông nhạc cần vẽ thêm ___ kẻ phụ.', [[0, 'dòng']], 'Dòng kẻ phụ giúp viết được các nốt quá cao hoặc quá thấp so với khuông nhạc.', $d);
        $this->fill($s, 'Khóa Son có hình dạng giống chữ cái ___.', [[0, 'G']], 'Hình xoắn của khóa Son giống chữ G và ôm lấy dòng kẻ thứ hai.', $d);
        $this->fill($s, 'Khe nhạc thứ nhất (từ dưới lên) trong khóa Son chứa nốt ___.', [[0, 'Fa']], 'Khe đầu tiên nằm giữa dòng kẻ 1 và dòng kẻ 2 chứa nốt Fa.', $d);
        $this->fill($s, 'Nốt ___ nằm trên dòng kẻ thứ hai của khuông nhạc khóa Son.', [[0, 'Son']], 'Khóa Son cho biết vị trí của nốt Son chính là dòng kẻ thứ hai.', $d);
    }

    private function seedAnNotNhacCaoDoLop71(): void
    {
        $s = 'an-not-nhac-cao-do-lop-7-1';
        $d = 'de';

        $this->quiz($s, 'Hai nốt Đô và Rê tạo thành quãng mấy?', ['Quãng 2', 'Quãng 3', 'Quãng 4', 'Quãng 5'], 0, 'Hai nốt kề nhau như Đô và Rê tạo thành quãng 2.', $d);
        $this->quiz($s, 'Hai nốt Đô và La tạo thành quãng mấy?', ['Quãng 5', 'Quãng 6', 'Quãng 7', 'Quãng 8'], 1, 'Đếm từ Đô lên La: Đô (1), Rê (2), Mi (3), Fa (4), Son (5), La (6) nên là quãng 6.', $d);
        $this->quiz($s, 'Trong một quãng, nốt có cao độ thấp hơn thường được gọi là nốt nào?', ['Nốt dưới', 'Nốt trên', 'Nốt giữa', 'Nốt ngoài'], 0, 'Trong một quãng, nốt thấp hơn gọi là nốt dưới, nốt cao hơn gọi là nốt trên.', $d);
        $this->quiz($s, 'Ví dụ nào sau đây là quãng 2?', ['Đô - Rê', 'Đô - Mi', 'Đô - Fa', 'Đô - Son'], 0, 'Quãng 2 gồm hai nốt kề nhau, trong các đáp án chỉ Đô - Rê là hai nốt kề nhau.', $d);
        $this->quiz($s, 'Hai nốt Si (thấp) và Đô (cao hơn) tạo thành quãng mấy?', ['Quãng 2', 'Quãng 7', 'Quãng 8', 'Quãng 3'], 0, 'Nốt Si và nốt Đô kề nhau nên tạo thành quãng 2.', $d);

        $this->matching($s, 'Nối mỗi tên quãng với cặp nốt tương ứng (đếm từ nốt Đô).', [['Quãng 2', 'Đô - Rê'], ['Quãng 3', 'Đô - Mi'], ['Quãng 6', 'Đô - La'], ['Quãng 7', 'Đô - Si']], 'Tên quãng bằng số nốt đếm được từ nốt dưới lên nốt trên.', $d);
        $this->matching($s, 'Nối mỗi quãng với số nốt đếm được từ nốt dưới lên nốt trên.', [['Quãng 2', '2 nốt'], ['Quãng 4', '4 nốt'], ['Quãng 5', '5 nốt'], ['Quãng 8', '8 nốt']], 'Quãng mấy thì đếm được bấy nhiêu nốt từ nốt dưới tới nốt trên.', $d);
        $this->matching($s, 'Nối mỗi cặp nốt kề nhau với tên quãng của chúng.', [['Đô - Rê', 'Quãng 2'], ['Mi - Fa', 'Quãng 2'], ['Son - La', 'Quãng 2'], ['La - Si', 'Quãng 2']], 'Mọi cặp nốt kề nhau trong thang nhạc đều tạo thành quãng 2.', $d);
        $this->matching($s, 'Nối mỗi quãng với ví dụ đúng (đếm từ nốt Đô).', [['Quãng 3', 'Đô - Mi'], ['Quãng 4', 'Đô - Fa'], ['Quãng 5', 'Đô - Son'], ['Quãng 6', 'Đô - La']], 'Đô - Mi là quãng 3, Đô - Fa là quãng 4, Đô - Son là quãng 5, Đô - La là quãng 6.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ về quãng với ý nghĩa của nó.', [['Quãng', 'Khoảng cách cao độ giữa hai nốt'], ['Bát độ', 'Tên gọi khác của quãng 8'], ['Nốt dưới', 'Nốt thấp hơn trong quãng'], ['Nốt trên', 'Nốt cao hơn trong quãng']], 'Hiểu đúng thuật ngữ giúp em nói và viết về quãng chính xác.', $d);

        $this->sortQ($s, 'Kéo mỗi quãng vào nhóm QUÃNG 2-3 hoặc QUÃNG 6-7-8.', [['Quãng 2', 'Quãng 2-3'], ['Quãng 3', 'Quãng 2-3'], ['Quãng 6', 'Quãng 6-7-8'], ['Quãng 7', 'Quãng 6-7-8'], ['Quãng 8', 'Quãng 6-7-8']], 'Quãng 2 và 3 là quãng hẹp, quãng 6, 7, 8 là quãng rộng.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nốt vào nhóm TẠO THÀNH QUÃNG 2 hoặc KHÔNG PHẢI QUÃNG 2.', [['Đô - Rê', 'Quãng 2'], ['Mi - Fa', 'Quãng 2'], ['Đô - Mi', 'Không phải quãng 2'], ['Fa - La', 'Không phải quãng 2']], 'Chỉ hai nốt kề nhau mới tạo thành quãng 2.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về quãng.', [['Quãng là khoảng cách cao độ giữa hai nốt', 'Đúng'], ['Đô và Mi tạo thành quãng 3', 'Đúng'], ['Quãng 8 còn gọi là bát độ', 'Đúng'], ['Si và Đô kề nhau tạo thành quãng 2', 'Đúng'], ['Đô và Son tạo thành quãng 4', 'Sai']], 'Đô và Son tạo thành quãng 5 chứ không phải quãng 4.', $d);
        $this->sortQ($s, 'Kéo mỗi quãng vào nhóm QUÃNG CHẴN hoặc QUÃNG LẺ.', [['Quãng 2', 'Quãng chẵn'], ['Quãng 4', 'Quãng chẵn'], ['Quãng 8', 'Quãng chẵn'], ['Quãng 3', 'Quãng lẻ'], ['Quãng 5', 'Quãng lẻ'], ['Quãng 7', 'Quãng lẻ']], 'Quãng 2, 4, 8 là quãng chẵn; quãng 3, 5, 7 là quãng lẻ.', $d);
        $this->sortQ($s, 'Kéo mỗi quãng vào nhóm NGHE GẦN NHAU hoặc NGHE XA NHAU.', [['Quãng 2', 'Nghe gần nhau'], ['Quãng 3', 'Nghe gần nhau'], ['Quãng 7', 'Nghe xa nhau'], ['Quãng 8', 'Nghe xa nhau']], 'Quãng càng rộng thì hai nốt nghe càng xa nhau về cao độ.', $d);

        $this->fill($s, 'Hai nốt Đô và Rê tạo thành quãng ___.', [[0, '2']], 'Hai nốt kề nhau luôn tạo thành quãng 2.', $d);
        $this->fill($s, 'Hai nốt Đô và Si tạo thành quãng ___.', [[0, '7']], 'Đếm từ Đô lên Si được 7 nốt nên là quãng 7.', $d);
        $this->fill($s, 'Trong một quãng, nốt có cao độ thấp hơn gọi là nốt ___.', [[0, 'dưới']], 'Nốt dưới là nốt thấp hơn, nốt trên là nốt cao hơn trong cùng một quãng.', $d);
        $this->fill($s, 'Quãng ___ gồm hai nốt kề nhau như Đô - Rê.', [[0, '2']], 'Quãng 2 là quãng hẹp nhất, gồm hai nốt đứng liền nhau.', $d);
        $this->fill($s, 'Đếm từ nốt Đô lên, nốt thứ tư là nốt Fa nên Đô - Fa là quãng ___.', [[0, '4']], 'Đô (1), Rê (2), Mi (3), Fa (4) nên Đô - Fa là quãng 4.', $d);
    }

    private function seedAnNotNhacCaoDoLop72(): void
    {
        $s = 'an-not-nhac-cao-do-lop-7-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Nốt Fa có dấu thăng (#) sẽ thành nốt gì?', ['Fa thăng, cao hơn Fa nửa cung', 'Fa giáng, thấp hơn Fa nửa cung', 'Vẫn là nốt Fa như cũ', 'Thành nốt Son'], 0, 'Dấu thăng nâng nốt nhạc lên nửa cung nên Fa thăng cao hơn Fa nửa cung.', $d);
        $this->quiz($s, 'Dấu bình đặt trước nốt đã có dấu thăng sẽ làm gì?', ['Trả nốt về cao độ ban đầu', 'Nâng nốt lên thêm nửa cung', 'Hạ nốt xuống thêm nửa cung', 'Xóa nốt nhạc đó đi'], 0, 'Dấu bình hủy bỏ tác dụng của dấu thăng hoặc dấu giáng đặt trước đó.', $d);
        $this->quiz($s, 'Trong âm giai Đô trưởng, cặp nốt nào cách nhau nửa cung?', ['Mi - Fa', 'Đô - Rê', 'Rê - Mi', 'Fa - Son'], 0, 'Trong âm giai Đô trưởng, Mi - Fa và Si - Đô cách nhau nửa cung, các cặp còn lại cách nhau một cung.', $d);
        $this->quiz($s, 'Các dấu thăng, giáng viết ở đầu khuông nhạc (sau khóa nhạc) được gọi là gì?', ['Hóa biểu', 'Dấu lặng', 'Dấu chấm dôi', 'Vạch nhịp'], 0, 'Hóa biểu có hiệu lực với mọi nốt cùng tên trong suốt bản nhạc.', $d);
        $this->quiz($s, 'Nốt Si có dấu giáng (b) sẽ thấp hơn nốt Si bình thường bao nhiêu?', ['Nửa cung', 'Một cung', 'Một cung rưỡi', 'Hai cung'], 0, 'Dấu giáng hạ nốt nhạc xuống nửa cung.', $d);

        $this->matching($s, 'Nối mỗi tên dấu hóa với ký hiệu viết của nó.', [['Dấu thăng', '#'], ['Dấu giáng', 'b'], ['Dấu bình', '♮'], ['Dấu thăng kép', 'x']], 'Mỗi dấu hóa có một ký hiệu riêng để viết trước nốt nhạc.', $d);
        $this->matching($s, 'Nối mỗi nốt trong âm giai Đô trưởng với bậc của nó.', [['Đô', 'Bậc 1'], ['Mi', 'Bậc 3'], ['Son', 'Bậc 5'], ['Si', 'Bậc 7']], 'Âm giai Đô trưởng gồm 8 nốt từ bậc 1 (Đô) đến bậc 8 (Đô cao hơn).', $d);
        $this->matching($s, 'Nối mỗi cặp nốt kề nhau trong âm giai Đô trưởng với khoảng cách của chúng.', [['Đô - Rê', 'Một cung'], ['Mi - Fa', 'Nửa cung'], ['Son - La', 'Một cung'], ['Si - Đô', 'Nửa cung']], 'Công thức âm giai trưởng: chỉ Mi - Fa và Si - Đô là nửa cung.', $d);
        $this->matching($s, 'Nối mỗi dấu hóa với phạm vi hiệu lực của nó.', [['Dấu thăng viết trước nốt', 'Chỉ nốt đó trong ô nhịp'], ['Dấu giáng viết trước nốt', 'Chỉ nốt đó trong ô nhịp'], ['Hóa biểu ở đầu khuông nhạc', 'Mọi nốt cùng tên trong bài'], ['Dấu bình', 'Hủy dấu thăng, giáng trước đó']], 'Dấu hóa viết trước nốt chỉ có hiệu lực trong ô nhịp chứa nó.', $d);
        $this->matching($s, 'Nối mỗi ký hiệu nốt với cách đọc đúng.', [['Fa#', 'Fa thăng'], ['Sib', 'Si giáng'], ['Đô♮', 'Đô bình'], ['Mi#', 'Mi thăng']], 'Đọc tên nốt trước rồi đọc tên dấu hóa sau, ví dụ Fa thăng, Si giáng.', $d);

        $this->sortQ($s, 'Kéo mỗi nốt có dấu hóa vào nhóm CAO HƠN NỐT GỐC hoặc THẤP HƠN NỐT GỐC.', [['Fa#', 'Cao hơn nốt gốc'], ['Đô#', 'Cao hơn nốt gốc'], ['Sib', 'Thấp hơn nốt gốc'], ['Mib', 'Thấp hơn nốt gốc']], 'Dấu thăng nâng nốt lên, dấu giáng hạ nốt xuống nửa cung.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nốt vào nhóm CÁCH NHAU NỬA CUNG hoặc CÁCH NHAU MỘT CUNG.', [['Mi - Fa', 'Nửa cung'], ['Si - Đô', 'Nửa cung'], ['Đô - Rê', 'Một cung'], ['Fa - Son', 'Một cung']], 'Trong âm giai Đô trưởng chỉ có hai cặp nửa cung là Mi - Fa và Si - Đô.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về dấu hóa.', [['Dấu thăng nâng nốt lên nửa cung', 'Đúng'], ['Dấu giáng hạ nốt xuống nửa cung', 'Đúng'], ['Âm giai Đô trưởng có 8 nốt', 'Đúng'], ['Dấu bình nâng nốt lên nửa cung', 'Sai'], ['Mi và Fa cách nhau một cung', 'Sai']], 'Dấu bình không nâng hay hạ mà trả nốt về cao độ ban đầu.', $d);
        $this->sortQ($s, 'Kéo mỗi ký hiệu vào nhóm DẤU HÓA hoặc KHÔNG PHẢI DẤU HÓA.', [['#', 'Dấu hóa'], ['b', 'Dấu hóa'], ['♮', 'Dấu hóa'], ['Dấu lặng đen', 'Không phải dấu hóa'], ['Vạch nhịp', 'Không phải dấu hóa']], 'Dấu hóa gồm dấu thăng, dấu giáng, dấu bình và dấu thăng kép.', $d);
        $this->sortQ($s, 'Kéo mỗi nốt vào nhóm CÓ TRONG ÂM GIAI ĐÔ TRƯỞNG hoặc KHÔNG CÓ.', [['Đô', 'Có trong âm giai'], ['Mi', 'Có trong âm giai'], ['Son', 'Có trong âm giai'], ['Fa#', 'Không có trong âm giai'], ['Sib', 'Không có trong âm giai']], 'Âm giai Đô trưởng không có dấu hóa nào, gồm 7 nốt tự nhiên từ Đô đến Si.', $d);

        $this->fill($s, 'Dấu thăng (#) đặt trước nốt Fa biến nó thành nốt Fa ___.', [[0, 'thăng']], 'Đọc là Fa thăng, cao hơn nốt Fa thường nửa cung.', $d);
        $this->fill($s, 'Nốt Si giáng thấp hơn nốt Si bình thường ___ cung.', [[0, 'nửa']], 'Dấu giáng luôn hạ nốt nhạc xuống đúng nửa cung.', $d);
        $this->fill($s, 'Các dấu thăng, giáng viết ở đầu khuông nhạc gọi là ___ biểu.', [[0, 'hóa']], 'Hóa biểu cho biết giọng của bản nhạc ngay từ khi bắt đầu.', $d);
        $this->fill($s, 'Trong âm giai Đô trưởng, hai cặp nốt cách nhau nửa cung là Mi - Fa và Si - ___.', [[0, 'Đô']], 'Si - Đô là cặp nửa cung thứ hai trong âm giai Đô trưởng.', $d);
        $this->fill($s, 'Dấu ___ dùng để hủy bỏ tác dụng của dấu thăng hoặc dấu giáng đặt trước đó.', [[0, 'bình']], 'Dấu bình trả nốt nhạc về cao độ tự nhiên ban đầu.', $d);
    }

    private function seedAnNhipDauLangLop71(): void
    {
        $s = 'an-nhip-dau-lang-lop-7-1';
        $d = 'de';

        $this->quiz($s, 'Số chỉ nhịp được viết ở vị trí nào trong bản nhạc?', ['Đầu bản nhạc, sau khóa nhạc', 'Cuối bản nhạc', 'Giữa mỗi ô nhịp', 'Dưới khuông nhạc'], 0, 'Số chỉ nhịp viết ở đầu bản nhạc, ngay sau khóa nhạc để người chơi biết nhịp của bài.', $d);
        $this->quiz($s, 'Trong nhịp 3/4, một ô nhịp gồm 1 nốt đen và 1 dấu lặng đen thì còn thiếu mấy phách?', ['Thiếu 1 phách', 'Vừa đủ', 'Thừa 1 phách', 'Thiếu 2 phách'], 0, 'Một nốt đen 1 phách cộng một dấu lặng đen 1 phách mới được 2 phách, còn thiếu 1 phách.', $d);
        $this->quiz($s, 'Phách mạnh trong ô nhịp thường được thể hiện như thế nào khi hát?', ['Hát nhấn mạnh hơn', 'Hát nhỏ đi', 'Bỏ qua không hát', 'Hát nhanh hơn'], 0, 'Phách mạnh được nhấn mạnh hơn các phách nhẹ để tạo cảm giác nhịp rõ ràng.', $d);
        $this->quiz($s, 'Vạch nhịp dùng để làm gì?', ['Ngăn cách các ô nhịp', 'Kết thúc bài nhạc', 'Chỉ chỗ nghỉ', 'Tăng tốc độ bài hát'], 0, 'Vạch nhịp là đường kẻ dọc ngăn cách các ô nhịp với nhau trên khuông nhạc.', $d);
        $this->quiz($s, 'Một ô nhịp 2/4 gồm 2 nốt móc đơn và 1 nốt đen có vừa đủ không?', ['Vừa đủ 2 phách', 'Thiếu nửa phách', 'Thừa nửa phách', 'Thiếu 1 phách'], 0, 'Hai nốt móc đơn dài 1 phách cộng một nốt đen 1 phách vừa đủ 2 phách của ô nhịp 2/4.', $d);

        $this->matching($s, 'Nối mỗi ký hiệu nhịp với vị trí của nó trong bản nhạc.', [['Tử số của số chỉ nhịp', 'Viết ở trên'], ['Mẫu số của số chỉ nhịp', 'Viết ở dưới'], ['Số chỉ nhịp', 'Sau khóa nhạc'], ['Vạch nhịp', 'Cuối mỗi ô nhịp']], 'Số chỉ nhịp gồm hai con số xếp chồng, viết ngay sau khóa nhạc ở đầu bản nhạc.', $d);
        $this->matching($s, 'Nối mỗi loại vạch nhịp với ý nghĩa của nó.', [['Vạch nhịp đơn', 'Ngăn cách hai ô nhịp'], ['Vạch nhịp kép', 'Kết thúc một đoạn nhạc'], ['Vạch kết thúc', 'Kết thúc bài nhạc'], ['Dấu lặp lại', 'Chơi lại đoạn nhạc đó']], 'Các loại vạch nhịp giúp người chơi biết cấu trúc của bản nhạc.', $d);
        $this->matching($s, 'Nối mỗi kiểu đếm phách với số phách trong ô nhịp.', [['Đếm "1-2"', '2 phách'], ['Đếm "1-2-3"', '3 phách'], ['Vỗ "mạnh-nhẹ"', '2 phách'], ['Vỗ "mạnh-nhẹ-nhẹ"', '3 phách']], 'Đếm đúng số phách giúp em giữ nhịp đều khi hát.', $d);
        $this->matching($s, 'Nối mỗi dấu lặng với nốt nhạc nghỉ bằng nó.', [['Dấu lặng đen', 'Nốt đen'], ['Dấu lặng trắng', 'Nốt trắng'], ['Dấu lặng tròn', 'Nốt tròn'], ['Dấu lặng móc đơn', 'Nốt móc đơn']], 'Mỗi dấu lặng tương ứng với một nốt nhạc có cùng thời gian.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ về nhịp với ví dụ của nó.', [['Ô nhịp', 'Phần nhạc giữa hai vạch nhịp'], ['Phách', 'Đơn vị đếm nhịp'], ['Phách mạnh', 'Phách 1 trong ô nhịp'], ['Số chỉ nhịp', 'Ký hiệu 2/4 ở đầu bản nhạc']], 'Hiểu đúng thuật ngữ giúp em đọc bản nhạc dễ dàng hơn.', $d);

        $this->sortQ($s, 'Kéo mỗi tổ hợp (đơn vị nốt đen) vào nhóm VỪA ĐỦ Ô NHỊP 2/4 hoặc CHƯA ĐỦ.', [['2 nốt đen', 'Vừa đủ ô nhịp 2/4'], ['1 nốt trắng', 'Vừa đủ ô nhịp 2/4'], ['1 nốt đen và 1 dấu lặng đen', 'Vừa đủ ô nhịp 2/4'], ['1 nốt đen', 'Chưa đủ']], 'Ô nhịp 2/4 cần tổng cộng 2 phách mới vừa đủ.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ hợp (đơn vị nốt đen) vào nhóm VỪA ĐỦ Ô NHỊP 3/4 hoặc CHƯA ĐỦ.', [['3 nốt đen', 'Vừa đủ ô nhịp 3/4'], ['1 nốt trắng và 1 nốt đen', 'Vừa đủ ô nhịp 3/4'], ['2 nốt đen', 'Chưa đủ'], ['1 nốt trắng', 'Chưa đủ']], 'Ô nhịp 3/4 cần tổng cộng 3 phách mới vừa đủ.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về nhịp và dấu lặng.', [['Nhịp 2/4 mỗi ô nhịp có 2 phách', 'Đúng'], ['Dấu lặng đen nghỉ 1 phách', 'Đúng'], ['Phách 1 luôn là phách mạnh', 'Đúng'], ['Vạch nhịp ngăn cách các ô nhịp', 'Đúng'], ['Số chỉ nhịp viết ở cuối bản nhạc', 'Sai']], 'Số chỉ nhịp luôn viết ở đầu bản nhạc, ngay sau khóa nhạc.', $d);
        $this->sortQ($s, 'Kéo mỗi ký hiệu vào nhóm LIÊN QUAN ĐẾN NHỊP hoặc KHÔNG LIÊN QUAN.', [['Số chỉ nhịp', 'Liên quan đến nhịp'], ['Vạch nhịp', 'Liên quan đến nhịp'], ['Dấu lặng', 'Liên quan đến nhịp'], ['Khóa Son', 'Không liên quan'], ['Dòng kẻ phụ', 'Không liên quan']], 'Số chỉ nhịp, vạch nhịp và dấu lặng đều là ký hiệu liên quan đến nhịp.', $d);
        $this->sortQ($s, 'Kéo mỗi phách vào nhóm NHẤN MẠNH hoặc NHẸ NHÀNG khi hát.', [['Phách 1 của nhịp 2/4', 'Nhấn mạnh'], ['Phách 1 của nhịp 3/4', 'Nhấn mạnh'], ['Phách 2 của nhịp 2/4', 'Nhẹ nhàng'], ['Phách 2 của nhịp 3/4', 'Nhẹ nhàng'], ['Phách 3 của nhịp 3/4', 'Nhẹ nhàng']], 'Phách đầu tiên của mọi ô nhịp luôn được nhấn mạnh nhất.', $d);

        $this->fill($s, 'Số chỉ nhịp được viết ở ___ bản nhạc, ngay sau khóa nhạc.', [[0, 'đầu']], 'Nhìn vào đầu bản nhạc là em biết ngay bài hát ở nhịp nào.', $d);
        $this->fill($s, 'Một ô nhịp 3/4 gồm 1 nốt đen và 1 dấu lặng đen thì còn thiếu ___ phách.', [[0, '1']], 'Mới có 2 phách, ô nhịp 3/4 cần 3 phách nên còn thiếu 1 phách.', $d);
        $this->fill($s, 'Vạch nhịp dùng để ___ cách các ô nhịp với nhau.', [[0, 'ngăn']], 'Vạch nhịp là đường kẻ dọc ngăn cách các ô nhịp trên khuông nhạc.', $d);
        $this->fill($s, 'Trong nhịp 2/4, phách ___ được hát nhấn mạnh hơn.', [[0, '1']], 'Phách 1 là phách mạnh, phách 2 là phách nhẹ trong nhịp 2/4.', $d);
        $this->fill($s, 'Dấu lặng ___ nghỉ đúng 1 phách.', [[0, 'đen']], 'Dấu lặng đen có thời gian nghỉ bằng một nốt đen.', $d);
    }

    private function seedAnNhipDauLangLop72(): void
    {
        $s = 'an-nhip-dau-lang-lop-7-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Nốt móc đơn có dấu chấm dôi dài bao nhiêu phách (đơn vị nốt đen)?', ['3/4 phách', '1 phách', '1,5 phách', '2 phách'], 0, 'Nốt móc đơn dài nửa phách, cộng thêm một nửa của nửa phách là 1/4, tổng cộng 3/4 phách.', $d);
        $this->quiz($s, 'Trong nhịp 4/4, ngoài phách 1 thì phách nào còn được nhấn?', ['Phách 3 (mạnh vừa)', 'Phách 2', 'Phách 4', 'Không phách nào'], 0, 'Nhịp 4/4 có phách 1 mạnh nhất và phách 3 mạnh vừa, phách 2 và 4 là phách nhẹ.', $d);
        $this->quiz($s, 'Một ô nhịp 4/4 gồm 1 nốt trắng chấm dôi và 1 nốt đen có vừa đủ không?', ['Vừa đủ 4 phách', 'Thiếu 1 phách', 'Thừa 1 phách', 'Thiếu nửa phách'], 0, 'Nốt trắng chấm dôi dài 3 phách cộng nốt đen 1 phách vừa đủ 4 phách của ô nhịp 4/4.', $d);
        $this->quiz($s, 'Nốt nhạc có hai dấu chấm dôi thì dài thêm bao nhiêu so với nốt gốc?', ['3/4 giá trị nốt', 'Một nửa giá trị nốt', 'Gấp đôi giá trị nốt', '1/4 giá trị nốt'], 0, 'Chấm thứ nhất thêm 1/2, chấm thứ hai thêm 1/4, tổng cộng nốt dài thêm 3/4 giá trị gốc.', $d);
        $this->quiz($s, 'Ký hiệu chữ C có gạch dọc (¢) thay cho số chỉ nhịp nào?', ['2/2', '4/4', '3/4', '2/4'], 0, 'Chữ C có gạch dọc là ký hiệu của nhịp 2/2, mỗi ô nhịp có 2 phách với đơn vị phách là nốt trắng.', $d);

        $this->matching($s, 'Nối mỗi nốt chấm dôi với trường độ của nó (đơn vị nốt đen).', [['Nốt đen chấm dôi', '1,5 phách'], ['Nốt trắng chấm dôi', '3 phách'], ['Nốt tròn chấm dôi', '6 phách'], ['Nốt móc đơn chấm dôi', '0,75 phách']], 'Dấu chấm dôi làm nốt dài thêm một nửa giá trị của chính nó.', $d);
        $this->matching($s, 'Nối mỗi phách của nhịp 4/4 với cách nhấn khi đếm.', [['Phách 1', 'Nhấn mạnh nhất'], ['Phách 2', 'Nhẹ'], ['Phách 3', 'Nhấn mạnh vừa'], ['Phách 4', 'Nhẹ']], 'Nhịp 4/4 đếm theo kiểu mạnh - nhẹ - mạnh vừa - nhẹ.', $d);
        $this->matching($s, 'Nối mỗi ký hiệu nhịp với ý nghĩa của nó.', [['Chữ C', 'Nhịp 4/4'], ['Chữ C có gạch dọc', 'Nhịp 2/2'], ['Số 3/4', '3 phách mỗi ô nhịp'], ['Số 6/8', 'Nhịp kép 2 phách']], 'Ngoài số chỉ nhịp, người ta còn dùng chữ C và C gạch dọc để viết tắt.', $d);
        $this->matching($s, 'Nối mỗi hình nốt với số nốt móc kép tương đương.', [['1 nốt đen', '4 nốt móc kép'], ['1 nốt trắng', '8 nốt móc kép'], ['1 nốt tròn', '16 nốt móc kép'], ['1 nốt móc đơn', '2 nốt móc kép']], 'Nốt móc kép ngắn bằng một nửa nốt móc đơn.', $d);
        $this->matching($s, 'Nối mỗi tổ hợp nốt với tổng trường độ của nó (đơn vị nốt đen).', [['1 nốt trắng và 1 nốt đen', '3 phách'], ['1 nốt đen chấm dôi và 1 nốt móc đơn', '2 phách'], ['4 nốt móc đơn', '2 phách'], ['1 nốt tròn', '4 phách']], 'Cộng trường độ các nốt giúp em kiểm tra ô nhịp đã vừa đủ chưa.', $d);

        $this->sortQ($s, 'Kéo mỗi nốt chấm dôi vào nhóm DÀI HƠN 2 PHÁCH hoặc NGẮN HƠN 2 PHÁCH.', [['Nốt trắng chấm dôi', 'Dài hơn 2 phách'], ['Nốt tròn chấm dôi', 'Dài hơn 2 phách'], ['Nốt đen chấm dôi', 'Ngắn hơn 2 phách'], ['Nốt móc đơn chấm dôi', 'Ngắn hơn 2 phách']], 'Trắng chấm dôi dài 3 phách, tròn chấm dôi dài 6 phách, đen chấm dôi dài 1,5 phách.', $d);
        $this->sortQ($s, 'Kéo mỗi phách của nhịp 4/4 vào nhóm CÓ NHẤN hoặc KHÔNG NHẤN.', [['Phách 1', 'Có nhấn'], ['Phách 3', 'Có nhấn'], ['Phách 2', 'Không nhấn'], ['Phách 4', 'Không nhấn']], 'Phách 1 nhấn mạnh nhất, phách 3 nhấn mạnh vừa, phách 2 và 4 nhẹ.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về nhịp 4/4 và dấu chấm dôi.', [['Nốt đen chấm dôi dài 1,5 phách', 'Đúng'], ['Chữ C là ký hiệu của nhịp 4/4', 'Đúng'], ['Phách 3 của nhịp 4/4 là phách mạnh vừa', 'Đúng'], ['Dấu chấm dôi làm nốt ngắn đi', 'Sai'], ['Nhịp 4/4 mỗi ô nhịp có 3 phách', 'Sai']], 'Dấu chấm dôi luôn làm nốt dài thêm, không bao giờ làm nốt ngắn đi.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ hợp (đơn vị nốt đen) vào nhóm VỪA ĐỦ 1 Ô NHỊP 4/4 hoặc CHƯA ĐỦ.', [['1 nốt tròn', 'Vừa đủ ô nhịp 4/4'], ['2 nốt trắng', 'Vừa đủ ô nhịp 4/4'], ['1 nốt trắng chấm dôi và 1 nốt đen', 'Vừa đủ ô nhịp 4/4'], ['3 nốt đen', 'Chưa đủ'], ['1 nốt trắng và 1 nốt đen', 'Chưa đủ']], 'Ô nhịp 4/4 cần tổng cộng 4 phách mới vừa đủ.', $d);
        $this->sortQ($s, 'Kéo mỗi ký hiệu vào nhóm VIẾT TẮT SỐ CHỈ NHỊP hoặc KHÔNG PHẢI.', [['Chữ C', 'Viết tắt số chỉ nhịp'], ['Chữ C có gạch dọc', 'Viết tắt số chỉ nhịp'], ['Số 4/4', 'Không phải viết tắt'], ['Vạch nhịp', 'Không phải viết tắt']], 'Chữ C thay cho 4/4, chữ C gạch dọc thay cho 2/2.', $d);

        $this->fill($s, 'Nốt móc đơn có dấu chấm dôi dài ___ phách (đơn vị nốt đen).', [[0, '3/4']], 'Nửa phách cộng thêm 1/4 phách bằng 3/4 phách.', $d);
        $this->fill($s, 'Trong nhịp 4/4, ngoài phách 1 thì phách ___ cũng được nhấn mạnh vừa.', [[0, '3']], 'Phách 3 là phách mạnh vừa trong nhịp 4/4.', $d);
        $this->fill($s, 'Chữ C có gạch dọc (¢) là ký hiệu viết tắt của nhịp ___.', [[0, '2/2']], 'Nhịp 2/2 có 2 phách mỗi ô, đơn vị phách là nốt trắng.', $d);
        $this->fill($s, 'Một nốt đen chấm dôi và một nốt móc đơn cộng lại dài ___ phách.', [[0, '2']], '1,5 phách cộng 0,5 phách bằng 2 phách.', $d);
        $this->fill($s, 'Dấu chấm dôi thứ hai làm nốt dài thêm ___ giá trị của nốt gốc.', [[0, '1/4']], 'Chấm thứ hai thêm một nửa của chấm thứ nhất, tức là 1/4 giá trị nốt.', $d);
    }

    private function seedAnNhipDauLangLop81(): void
    {
        $s = 'an-nhip-dau-lang-lop-8-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Trong nhịp 6/8, mỗi phách được chia thành mấy phần bằng nhau?', ['2 phần', '3 phần', '4 phần', '6 phần'], 1, 'Nhịp 6/8 là nhịp kép, mỗi phách chia thành 3 phần bằng nhau.', $d);
        $this->quiz($s, 'Nhịp 9/8 có mấy phách trong mỗi ô nhịp?', ['2 phách', '3 phách', '6 phách', '9 phách'], 1, 'Nhịp 9/8 là nhịp kép có 3 phách, mỗi phách tương đương 3 nốt móc đơn.', $d);
        $this->quiz($s, 'Điểm khác nhau cơ bản giữa nhịp đơn và nhịp kép là gì?', ['Nhịp đơn mỗi phách chia 2 phần, nhịp kép chia 3 phần', 'Nhịp đơn luôn nhanh hơn nhịp kép', 'Nhịp kép chỉ dùng cho nhạc buồn', 'Hai loại nhịp không khác nhau'], 0, 'Nhịp đơn mỗi phách chia thành 2 phần đều nhau, nhịp kép mỗi phách chia thành 3 phần.', $d);
        $this->quiz($s, 'Số chỉ nhịp nào sau đây là nhịp kép?', ['2/4', '3/4', '6/8', '4/4'], 2, 'Nhịp 6/8 có 2 phách, mỗi phách chia 3 phần nên là nhịp kép.', $d);
        $this->quiz($s, 'Một ô nhịp 6/8 chứa tối đa bao nhiêu nốt móc đơn?', ['4 nốt', '6 nốt', '8 nốt', '12 nốt'], 1, 'Mỗi phách của nhịp 6/8 tương đương 3 nốt móc đơn, 2 phách là 6 nốt móc đơn.', $d);

        $this->matching($s, 'Nối mỗi số chỉ nhịp với số phách của nó.', [['2/4', '2 phách'], ['3/4', '3 phách'], ['6/8', '2 phách'], ['9/8', '3 phách']], 'Nhịp 6/8 và 9/8 tuy tử số lớn nhưng số phách chính chỉ là 2 và 3.', $d);
        $this->matching($s, 'Nối mỗi số chỉ nhịp với cách chia mỗi phách.', [['2/4', 'Mỗi phách chia 2 phần'], ['3/4', 'Mỗi phách chia 2 phần'], ['6/8', 'Mỗi phách chia 3 phần'], ['9/8', 'Mỗi phách chia 3 phần']], 'Cách chia phách là dấu hiệu phân biệt nhịp đơn và nhịp kép.', $d);
        $this->matching($s, 'Nối mỗi loại nhịp với số chỉ nhịp đúng.', [['Nhịp đơn 2 phách', '2/4'], ['Nhịp đơn 3 phách', '3/4'], ['Nhịp kép 2 phách', '6/8'], ['Nhịp kép 3 phách', '9/8']], 'Nhịp đơn thường gặp là 2/4, 3/4; nhịp kép thường gặp là 6/8, 9/8.', $d);
        $this->matching($s, 'Nối mỗi giá trị với số lượng tương đương trong nhịp 6/8.', [['1 phách', '3 nốt móc đơn'], ['1 ô nhịp', '6 nốt móc đơn'], ['1 ô nhịp', '2 phách'], ['1 phách', '1 nốt đen chấm dôi']], 'Một phách của nhịp 6/8 dài bằng một nốt đen có dấu chấm dôi.', $d);
        $this->matching($s, 'Nối mỗi cảm giác âm nhạc với loại nhịp thường tạo ra nó.', [['Vui nhộn, nhịp nhàng', 'Nhịp kép 6/8'], ['Khỏe khoắn, dứt khoát', 'Nhịp 2/4'], ['Uyển chuyển, du dương', 'Nhịp 3/4'], ['Trang trọng, vững chãi', 'Nhịp 4/4']], 'Mỗi loại nhịp mang một cảm xúc riêng khi nghe.', $d);

        $this->sortQ($s, 'Kéo mỗi số chỉ nhịp vào nhóm PHÁCH CHIA 2 PHẦN hoặc PHÁCH CHIA 3 PHẦN.', [['2/4', 'Phách chia 2 phần'], ['3/4', 'Phách chia 2 phần'], ['4/4', 'Phách chia 2 phần'], ['6/8', 'Phách chia 3 phần'], ['9/8', 'Phách chia 3 phần'], ['12/8', 'Phách chia 3 phần']], 'Nhịp có mẫu số là 4 thuộc nhịp đơn, mẫu số là 8 thường thuộc nhịp kép.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về nhịp kép.', [['Nhịp 6/8 là nhịp kép', 'Đúng'], ['Mỗi phách của 6/8 tương đương 3 nốt móc đơn', 'Đúng'], ['Nhịp 2/4 là nhịp đơn', 'Đúng'], ['Nhịp 6/8 mỗi ô nhịp có 6 phách', 'Sai'], ['Nhịp kép mỗi phách chia thành 2 phần', 'Sai']], 'Nhịp 6/8 chỉ có 2 phách chính, mỗi phách chia thành 3 phần.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ hợp nốt vào nhóm VỪA ĐỦ 1 Ô NHỊP 6/8 hoặc CHƯA ĐỦ.', [['6 nốt móc đơn', 'Vừa đủ ô nhịp 6/8'], ['2 nốt đen chấm dôi', 'Vừa đủ ô nhịp 6/8'], ['3 nốt móc đơn', 'Chưa đủ'], ['1 nốt đen chấm dôi', 'Chưa đủ']], 'Ô nhịp 6/8 cần tổng cộng 2 phách, mỗi phách bằng 3 nốt móc đơn.', $d);
        $this->sortQ($s, 'Kéo mỗi số chỉ nhịp vào nhóm ĐƠN VỊ PHÁCH LÀ NỐT ĐEN hoặc ĐƠN VỊ LÀ NỐT MÓC ĐƠN.', [['2/4', 'Đơn vị phách là nốt đen'], ['3/4', 'Đơn vị phách là nốt đen'], ['4/4', 'Đơn vị phách là nốt đen'], ['6/8', 'Đơn vị là nốt móc đơn'], ['9/8', 'Đơn vị là nốt móc đơn']], 'Mẫu số 4 nghĩa là đơn vị phách là nốt đen, mẫu số 8 là nốt móc đơn.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm NHỊP ĐƠN hoặc NHỊP KÉP.', [['Mỗi phách chia 2 phần', 'Nhịp đơn'], ['Đếm phách kiểu "1-2, 1-2"', 'Nhịp đơn'], ['Mỗi phách chia 3 phần', 'Nhịp kép'], ['Cảm giác nhịp nhàng, luyến láy', 'Nhịp kép']], 'Nhịp kép với phách chia 3 tạo cảm giác luyến láy, nhịp nhàng đặc trưng.', $d);

        $this->fill($s, 'Nhịp 9/8 là nhịp kép, mỗi ô nhịp có ___ phách chính.', [[0, '3']], 'Nhịp 9/8 có 3 phách, mỗi phách tương đương 3 nốt móc đơn.', $d);
        $this->fill($s, 'Một phách của nhịp 6/8 dài bằng ___ nốt móc đơn.', [[0, '3']], 'Vì là nhịp kép nên mỗi phách chia thành 3 phần đều nhau.', $d);
        $this->fill($s, 'Nhịp đơn mỗi phách chia thành 2 phần, nhịp kép mỗi phách chia thành ___ phần.', [[0, '3']], 'Đây là điểm khác biệt cơ bản nhất giữa nhịp đơn và nhịp kép.', $d);
        $this->fill($s, 'Một ô nhịp 6/8 chứa vừa đủ ___ nốt đen chấm dôi.', [[0, '2']], 'Mỗi nốt đen chấm dôi dài 1,5 phách đen, tức đúng 1 phách của nhịp 6/8.', $d);
        $this->fill($s, 'Nhịp ___ là nhịp kép có 2 phách, mỗi phách chia thành 3 phần.', [[0, '6/8']], 'Nhịp 6/8 là nhịp kép phổ biến nhất mà em được học.', $d);
    }

    private function seedAnNhipDauLangLop82(): void
    {
        $s = 'an-nhip-dau-lang-lop-8-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Nốt móc kép có trường độ bằng bao nhiêu nốt móc đơn?', ['Nửa nốt móc đơn', 'Một nốt móc đơn', 'Hai nốt móc đơn', 'Bốn nốt móc đơn'], 0, 'Nốt móc kép ngắn bằng một nửa nốt móc đơn, tức bằng 1/4 phách.', $d);
        $this->quiz($s, 'Một ô nhịp 4/4 chứa tối đa bao nhiêu nốt móc kép?', ['8 nốt', '12 nốt', '16 nốt', '32 nốt'], 2, 'Mỗi phách chứa được 4 nốt móc kép, ô nhịp 4/4 có 4 phách nên tối đa 16 nốt.', $d);
        $this->quiz($s, 'Dấu luyến nối hai nốt CÙNG cao độ có tác dụng gì?', ['Cộng dồn trường độ, chỉ phát âm nốt đầu', 'Ngắt quãng giữa hai nốt', 'Chơi hai nốt rời nhau', 'Tăng âm lượng nốt thứ hai'], 0, 'Hai nốt cùng cao độ có dấu luyến được ngân liền thành một âm dài bằng tổng hai nốt.', $d);
        $this->quiz($s, 'Khi đếm "1-e-&-a" trong một phách, mỗi phách được chia thành mấy phần?', ['2 phần', '3 phần', '4 phần', '8 phần'], 2, 'Cách đếm "1-e-&-a" chia mỗi phách thành 4 phần đều nhau, tương ứng các nốt móc kép.', $d);
        $this->quiz($s, 'Dấu staccato (chấm nhỏ trên nốt nhạc) yêu cầu chơi nốt như thế nào?', ['Chơi nảy, ngắn gọn', 'Chơi thật to', 'Ngân thật dài', 'Chơi thật chậm'], 0, 'Dấu staccato làm nốt nhạc vang lên ngắn gọn, nảy từng nốt.', $d);

        $this->matching($s, 'Nối mỗi hình nốt với số nốt móc kép tương đương.', [['Nốt móc kép', '1 nốt móc kép'], ['Nốt móc đơn', '2 nốt móc kép'], ['Nốt đen', '4 nốt móc kép'], ['Nốt trắng', '8 nốt móc kép']], 'Nốt móc kép là đơn vị nhỏ nhất trong các hình nốt cơ bản em đã học.', $d);
        $this->matching($s, 'Nối mỗi ký hiệu diễn cảm với cách thể hiện đúng.', [['Dấu staccato (chấm)', 'Chơi nảy, ngắn gọn'], ['Dấu luyến', 'Ngân liền mạch các nốt'], ['Dấu nhấn (>)', 'Chơi mạnh nốt đó'], ['Dấu luyến cùng cao độ', 'Cộng dồn trường độ']], 'Các dấu diễn cảm giúp bản nhạc được thể hiện sinh động, có cảm xúc.', $d);
        $this->matching($s, 'Nối mỗi cách đếm chia nhỏ phách với hình nốt tương ứng.', [['Đếm "1 và"', 'Nốt móc đơn'], ['Đếm "1-e-&-a"', 'Nốt móc kép'], ['Đếm "1"', 'Nốt đen'], ['Đếm "1-2-3-4" cho một nốt', 'Nốt tròn']], 'Chia nhỏ phách và đếm đều giúp chơi đúng các nốt ngắn.', $d);
        $this->matching($s, 'Nối mỗi nhóm nốt với tổng trường độ của nó (đơn vị nốt đen).', [['8 nốt móc kép', '2 phách'], ['4 nốt móc đơn', '2 phách'], ['2 nốt đen', '2 phách'], ['1 nốt trắng', '2 phách']], 'Các nhóm nốt khác nhau có thể dài bằng nhau về trường độ.', $d);
        $this->matching($s, 'Nối mỗi dấu lặng nhỏ với thời gian nghỉ của nó (đơn vị nốt đen).', [['Dấu lặng móc kép', '1/4 phách'], ['Dấu lặng móc đơn', '1/2 phách'], ['Dấu lặng đen', '1 phách'], ['Dấu lặng trắng', '2 phách']], 'Mỗi dấu lặng nghỉ đúng bằng trường độ của nốt nhạc tương ứng.', $d);

        $this->sortQ($s, 'Kéo mỗi hình nốt vào nhóm NGẮN HƠN NỐT MÓC ĐƠN hoặc DÀI HƠN NỐT MÓC ĐƠN.', [['Nốt móc kép', 'Ngắn hơn nốt móc đơn'], ['Nốt đen', 'Dài hơn nốt móc đơn'], ['Nốt trắng', 'Dài hơn nốt móc đơn'], ['Nốt tròn', 'Dài hơn nốt móc đơn']], 'Nốt móc kép là hình nốt ngắn nhất, chỉ bằng nửa nốt móc đơn.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về nốt ngắn và dấu diễn cảm.', [['Nốt móc kép bằng 1/4 phách', 'Đúng'], ['Dấu staccato yêu cầu chơi nảy gọn', 'Đúng'], ['8 nốt móc kép dài bằng 1 nốt trắng', 'Đúng'], ['Dấu luyến cùng cao độ cộng dồn trường độ', 'Đúng'], ['Nốt móc kép dài hơn nốt móc đơn', 'Sai']], 'Nốt móc kép ngắn hơn nốt móc đơn một nửa.', $d);
        $this->sortQ($s, 'Kéo mỗi ký hiệu vào nhóm LÀM THAY ĐỔI TRƯỜNG ĐỘ hoặc LÀM THAY ĐỔI CÁCH CHƠI.', [['Dấu chấm dôi', 'Thay đổi trường độ'], ['Dấu luyến cùng cao độ', 'Thay đổi trường độ'], ['Dấu staccato', 'Thay đổi cách chơi'], ['Dấu nhấn', 'Thay đổi cách chơi']], 'Dấu chấm dôi và dấu luyến thay đổi độ dài nốt, dấu staccato và dấu nhấn thay đổi cách thể hiện.', $d);
        $this->sortQ($s, 'Kéo mỗi nhóm nốt vào nhóm VỪA ĐỦ 1 Ô NHỊP 2/4 hoặc VƯỢT QUÁ.', [['8 nốt móc đơn', 'Vừa đủ ô nhịp 2/4'], ['4 nốt móc kép và 1 nốt đen', 'Vừa đủ ô nhịp 2/4'], ['4 nốt đen', 'Vượt quá'], ['2 nốt trắng', 'Vượt quá']], 'Ô nhịp 2/4 chỉ chứa vừa đủ 2 phách, nhiều hơn là vượt quá.', $d);
        $this->sortQ($s, 'Kéo mỗi hình nốt vào nhóm CÓ MÓC NỐT hoặc KHÔNG CÓ MÓC NỐT.', [['Nốt móc đơn', 'Có móc nốt'], ['Nốt móc kép', 'Có móc nốt'], ['Nốt đen', 'Không có móc nốt'], ['Nốt trắng', 'Không có móc nốt']], 'Móc nốt là dấu hiệu nhận biết các nốt có trường độ ngắn.', $d);

        $this->fill($s, 'Nốt móc kép ngắn bằng ___ nốt móc đơn.', [[0, 'nửa']], 'Nốt móc kép dài 1/4 phách, nốt móc đơn dài 1/2 phách.', $d);
        $this->fill($s, 'Một ô nhịp 4/4 chứa tối đa ___ nốt móc kép.', [[0, '16']], 'Mỗi phách 4 nốt móc kép, 4 phách là 16 nốt.', $d);
        $this->fill($s, 'Dấu ___ trên nốt nhạc yêu cầu chơi nảy, ngắn gọn từng nốt.', [[0, 'staccato']], 'Staccato là ký hiệu diễn cảm cho cách chơi nảy gọn.', $d);
        $this->fill($s, 'Hai nốt cùng cao độ nối bằng dấu luyến được ngân dài bằng ___ trường độ hai nốt.', [[0, 'tổng']], 'Dấu luyến cộng dồn trường độ và chỉ phát âm ở nốt đầu tiên.', $d);
        $this->fill($s, 'Chia một phách thành 4 phần đều nhau ta được các nốt ___ kép.', [[0, 'móc']], 'Bốn phần của một phách tương ứng với bốn nốt móc kép.', $d);
    }

    private function seedAnNhacCuDanTocLop81(): void
    {
        $s = 'an-nhac-cu-dan-toc-lop-8-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Đàn nhị có mấy dây?', ['1 dây', '2 dây', '3 dây', '4 dây'], 1, 'Đàn nhị có 2 dây, người chơi dùng vĩ kéo trên dây để tạo âm thanh.', $d);
        $this->quiz($s, 'Đàn nguyệt có hình dạng đặc biệt gì?', ['Mặt đàn hình tròn như mặt trăng', 'Thân đàn hình ống dài', 'Có tới 16 dây đàn', 'Chơi bằng cách thổi hơi'], 0, 'Đàn nguyệt có mặt đàn hình tròn như mặt trăng, còn được gọi là nguyệt cầm.', $d);
        $this->quiz($s, 'Nhạc cụ nào sau đây thuộc bộ dây kéo?', ['Đàn nhị', 'Đàn tranh', 'Đàn bầu', 'Sáo'], 0, 'Đàn nhị dùng vĩ kéo trên dây nên thuộc bộ dây kéo.', $d);
        $this->quiz($s, 'Đàn t\'rưng được làm từ chất liệu gì?', ['Các ống tre, nứa', 'Kim loại', 'Đá', 'Nhựa'], 0, 'Đàn t\'rưng gồm nhiều ống tre, nứa dài ngắn khác nhau, gõ vào để tạo âm thanh.', $d);
        $this->quiz($s, 'Đàn bầu tạo ra nhiều cao độ khác nhau bằng cách nào?', ['Chạm tay tạo điểm nút trên dây khi gảy', 'Thay dây đàn liên tục', 'Thổi hơi vào dây đàn', 'Vỗ vào thân đàn'], 0, 'Người chơi đàn bầu chạm nhẹ ngón tay lên dây để tạo điểm nút, kết hợp rung cần đàn để có nhiều cao độ.', $d);

        $this->matching($s, 'Nối mỗi nhạc cụ với bộ phận đặc trưng nhất của nó.', [['Đàn bầu', 'Cần đàn rung'], ['Đàn tranh', 'Ngựa đàn'], ['Đàn nhị', 'Bầu cộng hưởng'], ['Đàn nguyệt', 'Mặt đàn hình tròn']], 'Mỗi nhạc cụ dân tộc đều có bộ phận đặc trưng tạo nên âm sắc riêng.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với tư thế chơi phổ biến.', [['Đàn tranh', 'Ngồi gảy đàn'], ['Đàn nhị', 'Ngồi kéo vĩ'], ['Sáo', 'Ngồi hoặc đứng thổi'], ['Trống chầu', 'Ngồi gõ trống']], 'Tư thế chơi thoải mái giúp nghệ sĩ thể hiện trọn vẹn bản nhạc.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với chất liệu chính làm nên nó.', [['Đàn t\'rưng', 'Tre, nứa'], ['Đàn đá', 'Đá'], ['Sáo', 'Trúc'], ['Đàn nhị', 'Gỗ và da']], 'Nhạc cụ dân tộc thường được làm từ vật liệu sẵn có trong tự nhiên.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với tên gọi khác của nó.', [['Đàn nguyệt', 'Nguyệt cầm'], ['Đàn bầu', 'Độc huyền cầm'], ['Đàn nhị', 'Đàn cò'], ['Đàn tranh', 'Thập lục huyền cầm']], 'Tên gọi khác thường gợi đúng đặc điểm nổi bật của nhạc cụ.', $d);
        $this->matching($s, 'Nối mỗi bộ nhạc cụ với nhạc cụ tiêu biểu.', [['Bộ dây gảy', 'Đàn tranh'], ['Bộ dây kéo', 'Đàn nhị'], ['Bộ hơi', 'Sáo'], ['Bộ gõ', 'Trống cơm']], 'Nhạc cụ dân tộc được phân thành các bộ theo cách tạo ra âm thanh.', $d);

        $this->sortQ($s, 'Kéo mỗi nhạc cụ vào nhóm GẢY DÂY hoặc KÉO DÂY.', [['Đàn tranh', 'Gảy dây'], ['Đàn nguyệt', 'Gảy dây'], ['Đàn bầu', 'Gảy dây'], ['Đàn nhị', 'Kéo dây']], 'Đàn nhị là nhạc cụ duy nhất trong bốn nhạc cụ này chơi bằng cách kéo vĩ.', $d);
        $this->sortQ($s, 'Kéo mỗi nhạc cụ vào nhóm DƯỚI 5 DÂY hoặc TỪ 5 DÂY TRỞ LÊN.', [['Đàn bầu', 'Dưới 5 dây'], ['Đàn nhị', 'Dưới 5 dây'], ['Đàn nguyệt', 'Dưới 5 dây'], ['Đàn tranh', 'Từ 5 dây trở lên'], ['Đàn t\'rưng', 'Từ 5 dây trở lên']], 'Đàn bầu chỉ có 1 dây, đàn nhị và đàn nguyệt có 2 dây, đàn tranh có 16 dây.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về nhạc cụ dân tộc.', [['Đàn nhị dùng vĩ để kéo', 'Đúng'], ['Đàn nguyệt còn gọi là nguyệt cầm', 'Đúng'], ['Đàn t\'rưng làm từ tre nứa', 'Đúng'], ['Đàn bầu có 16 dây', 'Sai'], ['Đàn tranh thuộc bộ hơi', 'Sai']], 'Đàn bầu chỉ có 1 dây và đàn tranh thuộc bộ dây gảy.', $d);
        $this->sortQ($s, 'Kéo mỗi nhạc cụ vào nhóm CHƠI BẰNG VĨ hoặc KHÔNG DÙNG VĨ.', [['Đàn nhị', 'Chơi bằng vĩ'], ['Đàn tranh', 'Không dùng vĩ'], ['Đàn bầu', 'Không dùng vĩ'], ['Sáo', 'Không dùng vĩ']], 'Trong nhạc cụ dân tộc Việt Nam, đàn nhị là nhạc cụ tiêu biểu chơi bằng vĩ.', $d);
        $this->sortQ($s, 'Kéo mỗi nhạc cụ vào nhóm ÂM THANH TRẦM ẤM hoặc ÂM THANH TRONG CAO.', [['Đàn bầu', 'Âm thanh trầm ấm'], ['Đàn nhị', 'Âm thanh trầm ấm'], ['Sáo', 'Âm thanh trong cao'], ['Đàn tranh', 'Âm thanh trong cao']], 'Mỗi nhạc cụ có màu sắc âm thanh riêng tạo nên sự phong phú của dàn nhạc dân tộc.', $d);

        $this->fill($s, 'Đàn nhị có ___ dây.', [[0, '2']], 'Đàn nhị có 2 dây, ít hơn đàn tranh nhưng nhiều hơn đàn bầu.', $d);
        $this->fill($s, 'Đàn nguyệt còn được gọi là ___ cầm.', [[0, 'nguyệt']], 'Nguyệt cầm nghĩa là đàn mặt trăng, đúng với hình dạng mặt đàn tròn.', $d);
        $this->fill($s, 'Đàn t\'rưng được làm từ các ống ___.', [[0, 'tre']], 'Các ống tre dài ngắn khác nhau tạo ra các cao độ khác nhau khi gõ.', $d);
        $this->fill($s, 'Người chơi đàn nhị dùng ___ để kéo trên dây đàn.', [[0, 'vĩ']], 'Chiếc vĩ kéo trên dây đàn làm dây rung và phát ra âm thanh.', $d);
        $this->fill($s, 'Đàn bầu tạo ra nhiều cao độ khác nhau nhờ kỹ thuật chạm tạo ___ trên dây.', [[0, 'điểm nút']], 'Điểm nút chia dây thành các đoạn rung khác nhau, tạo ra các cao độ khác nhau.', $d);
    }
}
