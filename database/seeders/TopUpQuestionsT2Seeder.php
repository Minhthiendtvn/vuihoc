<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * VÁ câu hỏi còn thiếu cho VuiHoc — nhóm T2 (Lịch sử + Toán, 55 combo bài×kiểu, 138 câu).
 *
 * Đầu vào: database/seeders/_briefs/TOPUP_T2.json — mỗi combo {slug, title, grade,
 * difficulty, subject, topic, type, have, need, existing_prompts}.
 * Nội dung tiếng Việt tự viết 100%, bám topic + khối lớp + độ khó, prompt khác hẳn
 * các prompt đã có.
 * Idempotent: chạy lại không thêm câu mới (guard: giới hạn 8 câu/kiểu/bài + kiểm tra
 * prompt trùng không phân biệt hoa thường).
 */
class TopUpQuestionsT2Seeder extends Seeder
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
        $this->seedT2C00();
        $this->seedT2C01();
        $this->seedT2C02();
        $this->seedT2C03();
        $this->seedT2C04();
        $this->seedT2C05();
        $this->seedT2C06();
        $this->seedT2C07();
        $this->seedT2C08();
        $this->seedT2C09();
        $this->seedT2C10();
        $this->seedT2C11();
        $this->seedT2C12();
        $this->seedT2C13();
        $this->seedT2C14();
        $this->seedT2C15();
        $this->seedT2C16();
        $this->seedT2C17();
        $this->seedT2C18();
        $this->seedT2C19();
        $this->seedT2C20();
        $this->seedT2C21();
        $this->seedT2C22();
        $this->seedT2C23();
        $this->seedT2C24();
        $this->seedT2C25();
        $this->seedT2C26();
        $this->seedT2C27();
        $this->seedT2C28();
        $this->seedT2C29();
        $this->seedT2C30();
        $this->seedT2C31();
        $this->seedT2C32();
        $this->seedT2C33();
        $this->seedT2C34();
        $this->seedT2C35();
        $this->seedT2C36();
        $this->seedT2C37();
        $this->seedT2C38();
        $this->seedT2C39();
        $this->seedT2C40();
        $this->seedT2C41();
        $this->seedT2C42();
        $this->seedT2C43();
        $this->seedT2C44();
        $this->seedT2C45();
        $this->seedT2C46();
        $this->seedT2C47();
        $this->seedT2C48();
        $this->seedT2C49();
        $this->seedT2C50();
        $this->seedT2C51();
        $this->seedT2C52();
        $this->seedT2C53();
        $this->seedT2C54();
    }

    private function seedT2C00(): void
    {
        $s = 'toan-cong-tru-so-tu-nhien';
        $d = 'de';

        $this->matching($s, 'Nối mỗi phép cộng với tổng của nó.', [['245 + 355', '600'], ['789 + 211', '1000'], ['456 + 344', '800'], ['125 + 875', '1000']], 'Cộng nhẩm các số tròn trăm trước sẽ giúp em tính nhanh và ít nhầm lẫn hơn.', $d);
        $this->matching($s, 'Nối mỗi câu đố cộng nhẩm với đáp án đúng.', [['400 + 250', '650'], ['900 − 350', '550'], ['120 + 480', '600'], ['1000 − 120', '880']], 'Tách số thành các phần tròn trăm, tròn chục giúp phép cộng trừ nhẩm trở nên dễ dàng.', $d);
    }

    private function seedT2C01(): void
    {
        $s = 'toan-cong-tru-so-tu-nhien';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi phép tính vào nhóm "KẾT QUẢ TRÒN CHỤC" hoặc "KHÁC".', [['340 + 60 = 400', 'KẾT QUẢ TRÒN CHỤC'], ['275 + 125 = 400', 'KẾT QUẢ TRÒN CHỤC'], ['463 + 137 = 600', 'KẾT QUẢ TRÒN CHỤC'], ['123 + 456 = 579', 'KHÁC'], ['789 − 123 = 666', 'KHÁC'], ['905 − 70 = 835', 'KHÁC']], 'Kết quả tròn chục là kết quả có chữ số hàng đơn vị bằng 0, rất dễ nhận ra khi tính nhẩm.', $d);
        $this->sortQ($s, 'Kéo mỗi phép trừ vào nhóm "CÓ MƯỢN" hoặc "KHÔNG MƯỢN".', [['500 − 270', 'CÓ MƯỢN'], ['742 − 118', 'KHÔNG MƯỢN'], ['1000 − 456', 'CÓ MƯỢN'], ['689 − 125', 'KHÔNG MƯỢN']], 'Khi chữ số của số bị trừ nhỏ hơn chữ số của số trừ ở cùng một hàng, ta phải mượn 1 ở hàng kế tiếp.', $d);
        $this->sortQ($s, 'Kéo mỗi số vào nhóm "CHIA HẾT CHO 10" hoặc "KHÔNG CHIA HẾT CHO 10".', [['340', 'CHIA HẾT CHO 10'], ['1000', 'CHIA HẾT CHO 10'], ['890', 'CHIA HẾT CHO 10'], ['75', 'KHÔNG CHIA HẾT CHO 10'], ['206', 'KHÔNG CHIA HẾT CHO 10'], ['432', 'KHÔNG CHIA HẾT CHO 10']], 'Số chia hết cho 10 là số có chữ số hàng đơn vị bằng 0.', $d);
    }

    private function seedT2C02(): void
    {
        $s = 'toan-cong-tru-so-tu-nhien';
        $d = 'de';

        $this->fill($s, '925 − 375 = ___.', [[0, '550']], 'Trừ từng hàng từ phải sang trái: 925 − 375 = 550.', $d);
        $this->fill($s, 'Số liền trước của 1000 là ___.', [[0, '999']], 'Số liền trước của một số là số đó trừ đi 1, nên số liền trước của 1000 là 999.', $d);
        $this->fill($s, 'Tổng của 348 và 152 là ___.', [[0, '500']], '348 + 152 = 500, đây là một cặp số cộng lại tròn trăm rất hay gặp khi tính nhẩm.', $d);
    }

    private function seedT2C03(): void
    {
        $s = 'toan-nhan-chia-so-tu-nhien';
        $d = 'trung_binh';

        $this->quiz($s, '25 × 12 = ?', ['300', '250', '280', '320'], 0, '25 × 12 = 25 × 4 × 3 = 100 × 3 = 300.', $d);
        $this->quiz($s, 'Một hộp có 6 cái bánh. 8 hộp như thế có bao nhiêu cái bánh?', ['48', '42', '54', '40'], 0, 'Số bánh là 6 × 8 = 48 cái.', $d);
        $this->quiz($s, 'Kết quả của phép chia 96 : 8 là?', ['12', '11', '13', '14'], 0, '96 : 8 = 12 vì 12 × 8 = 96.', $d);
    }

    private function seedT2C04(): void
    {
        $s = 'toan-nhan-chia-so-tu-nhien';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi phép nhân nhẩm với kết quả.', [['25 × 4', '100'], ['125 × 8', '1000'], ['50 × 6', '300'], ['12 × 50', '600']], 'Nhớ các cặp nhân quen thuộc như 25 × 4 = 100 và 125 × 8 = 1000 giúp tính nhẩm rất nhanh.', $d);
        $this->matching($s, 'Nối mỗi phép tính có số 0 với quy tắc đã dùng.', [['7 × 0', 'Bằng 0'], ['0 : 9', 'Bằng 0'], ['0 + 125', 'Bằng 125'], ['460 − 0', 'Bằng 460']], 'Số nào nhân với 0 cũng bằng 0, còn cộng hoặc trừ với 0 thì số đó giữ nguyên.', $d);
        $this->matching($s, 'Nối mỗi số với bội số của 25 gần nhất và lớn hơn nó.', [['60', '75'], ['120', '125'], ['210', '225'], ['340', '350']], 'Tìm bội số gần nhất giúp em ước lượng nhanh trong các bài toán thực tế.', $d);
    }

    private function seedT2C05(): void
    {
        $s = 'toan-cong-tru-phan-so';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi phép cộng phân số vào nhóm "QUY ĐỒNG RỒI CỘNG" hoặc "CỘNG TRỰC TIẾP".', [['1/3 + 1/6', 'QUY ĐỒNG RỒI CỘNG'], ['1/4 + 1/2', 'QUY ĐỒNG RỒI CỘNG'], ['2/7 + 3/7', 'CỘNG TRỰC TIẾP'], ['5/9 − 2/9', 'CỘNG TRỰC TIẾP']], 'Phân số cùng mẫu thì cộng trừ trực tiếp tử số, khác mẫu phải quy đồng mẫu số trước.', $d);
        $this->sortQ($s, 'Kéo mỗi kết quả vào nhóm "LỚN HƠN 1" hoặc "NHỎ HƠN 1".', [['1/2 + 3/4 = 5/4', 'LỚN HƠN 1'], ['3/4 + 1/2 = 5/4', 'LỚN HƠN 1'], ['1/3 + 1/6 = 1/2', 'NHỎ HƠN 1'], ['1/5 + 1/10 = 3/10', 'NHỎ HƠN 1']], 'Phân số có tử lớn hơn mẫu thì lớn hơn 1, tử nhỏ hơn mẫu thì nhỏ hơn 1.', $d);
        $this->sortQ($s, 'Kéo mỗi phân số vào nhóm "RÚT GỌN ĐƯỢC NỮA" hoặc "ĐÃ TỐI GIẢN".', [['4/6', 'RÚT GỌN ĐƯỢC NỮA'], ['6/10', 'RÚT GỌN ĐƯỢC NỮA'], ['3/7', 'ĐÃ TỐI GIẢN'], ['5/9', 'ĐÃ TỐI GIẢN']], 'Phân số tối giản là phân số mà tử và mẫu không còn ước chung nào ngoài 1.', $d);
    }

    private function seedT2C06(): void
    {
        $s = 'toan-cong-tru-phan-so';
        $d = 'trung_binh';

        $this->fill($s, '3/5 − 1/5 = ___.', [[0, '2/5']], 'Hai phân số cùng mẫu số: trừ tử số, giữ nguyên mẫu số, được 2/5.', $d);
        $this->fill($s, '5/6 − 1/3 = ___.', [[0, '1/2']], 'Quy đồng 1/3 = 2/6, rồi 5/6 − 2/6 = 3/6 = 1/2.', $d);
        $this->fill($s, 'Muốn trừ hai phân số cùng mẫu số, ta trừ hai tử số và giữ nguyên ___ số.', [[0, 'mẫu']], 'Quy tắc trừ phân số cùng mẫu: trừ tử, giữ nguyên mẫu số.', $d);
    }

    private function seedT2C07(): void
    {
        $s = 'toan-nhan-chia-phan-so';
        $d = 'trung_binh';

        $this->quiz($s, '2/3 × 3/4 = ?', ['1/2', '5/6', '6/7', '2/9'], 0, 'Nhân tử với tử, mẫu với mẫu: 6/12, rút gọn được 1/2.', $d);
        $this->quiz($s, '5/6 : 5 = ?', ['1/6', '5', '1/5', '6/5'], 0, 'Chia cho 5 tức là nhân với 1/5: 5/6 × 1/5 = 1/6.', $d);
        $this->quiz($s, '3/4 của 20 bằng bao nhiêu?', ['15', '12', '16', '18'], 0, 'Muốn tìm phân số của một số, ta nhân số đó với phân số: 20 × 3/4 = 15.', $d);
    }

    private function seedT2C08(): void
    {
        $s = 'toan-nhan-chia-phan-so';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi phép nhân phân số với kết quả đã rút gọn.', [['1/2 × 4/5', '2/5'], ['3/4 × 2/3', '1/2'], ['2/7 × 3/4', '3/14'], ['5/6 × 3/10', '1/4']], 'Nhân phân số: tử nhân tử, mẫu nhân mẫu, rồi rút gọn kết quả nếu được.', $d);
        $this->matching($s, 'Nối mỗi cặp phân số với tích của chúng.', [['1/2 và 1/3', '1/6'], ['2/3 và 3/4', '1/2'], ['1/5 và 3/4', '3/20'], ['2/7 và 7/10', '1/5']], 'Tích của hai phân số cũng là một phân số, tính bằng cách nhân tử với tử và mẫu với mẫu.', $d);
        $this->matching($s, 'Nối mỗi bài toán chia đều với phép tính chia phân số đúng.', [['Chia 3/4 cái bánh cho 3 bạn', '3/4 : 3'], ['5 kg gạo, mỗi túi 1/2 kg, được mấy túi', '5 : 1/2'], ['Chia 2/3 lít nước vào chai 1/6 lít', '2/3 : 1/6'], ['Sợi dây 4/5 m, cắt mỗi đoạn 1/5 m', '4/5 : 1/5']], 'Bài toán chia đều một lượng thành các phần bằng nhau dùng phép chia phân số.', $d);
    }

    private function seedT2C09(): void
    {
        $s = 'toan-thu-gon-don-thuc';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi phép nâng lên lũy thừa với đơn thức thu gọn.', [['(2xy)²', '4x²y²'], ['(−3x)²', '9x²'], ['(xy³)²', 'x²y⁶'], ['(2x²)³', '8x⁶']], 'Lũy thừa của một tích bằng tích các lũy thừa: nâng cả hệ số và từng biến lên số mũ đó.', $d);
        $this->matching($s, 'Nối mỗi đơn thức đã thu gọn với phần hệ số của nó.', [['7x²y', '7'], ['−3a³b²', '−3'], ['(1/2)x⁴', '1/2'], ['−xy', '−1']], 'Hệ số là phần số đứng trước phần biến; đơn thức −xy có hệ số là −1.', $d);
        $this->matching($s, 'Nối mỗi biểu thức với kết quả sau khi cộng các đơn thức đồng dạng.', [['4x + 5x', '9x'], ['7y² − 3y²', '4y²'], ['2ab + 6ab', '8ab'], ['9m³ − 4m³', '5m³']], 'Chỉ cộng trừ được các đơn thức đồng dạng: cộng hệ số, giữ nguyên phần biến.', $d);
    }

    private function seedT2C10(): void
    {
        $s = 'toan-thu-gon-don-thuc';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi đơn thức vào nhóm "BẬC CHẴN" hoặc "BẬC LẺ".', [['5x⁴', 'BẬC CHẴN'], ['2a²b²', 'BẬC CHẴN'], ['−3xy²', 'BẬC LẺ'], ['7x', 'BẬC LẺ']], 'Bậc của đơn thức là tổng số mũ của các biến; ví dụ −3xy² có bậc 1 + 2 = 3 là bậc lẻ.', $d);
        $this->sortQ($s, 'Kéo mỗi đơn thức vào nhóm "ĐỒNG DẠNG VỚI 3xy" hoặc "KHÔNG".', [['5xy', 'ĐỒNG DẠNG VỚI 3xy'], ['−2xy', 'ĐỒNG DẠNG VỚI 3xy'], ['4x²y', 'KHÔNG'], ['3xy²', 'KHÔNG']], 'Hai đơn thức đồng dạng khi có cùng phần biến, kể cả số mũ của từng biến.', $d);
        $this->sortQ($s, 'Kéo mỗi kết quả thu gọn vào nhóm "BẬC 2" hoặc "KHÁC BẬC 2".', [['x · x = x²', 'BẬC 2'], ['2x · 3x = 6x²', 'BẬC 2'], ['x² · x = x³', 'KHÁC BẬC 2'], ['4x', 'KHÁC BẬC 2']], 'Khi nhân các đơn thức, số mũ của cùng một biến được cộng lại với nhau.', $d);
    }

    private function seedT2C11(): void
    {
        $s = 'toan-thu-gon-don-thuc';
        $d = 'trung_binh';

        $this->fill($s, 'Thu gọn: 2x² · 5x = ___x³.', [[0, '10']], 'Nhân hệ số 2 × 5 = 10 và cộng số mũ x² · x = x³, được 10x³.', $d);
        $this->fill($s, 'Bậc của đơn thức 4a²b là ___.', [[0, '3']], 'Bậc bằng tổng số mũ của các biến: 2 + 1 = 3.', $d);
        $this->fill($s, '5x³ + 2x³ = ___x³.', [[0, '7']], 'Hai đơn thức đồng dạng nên cộng hệ số: 5 + 2 = 7, giữ nguyên x³.', $d);
    }

    private function seedT2C12(): void
    {
        $s = 'toan-gia-tri-bieu-thuc';
        $d = 'kho';

        $this->quiz($s, 'Với x = −3, giá trị của biểu thức x³ là?', ['−27', '27', '9', '−9'], 0, '(−3)³ = (−3) × (−3) × (−3) = −27.', $d);
        $this->quiz($s, 'Với a = 2, b = −1, giá trị của biểu thức a² − 2b là?', ['6', '2', '0', '4'], 0, 'Thay số: 2² − 2 × (−1) = 4 + 2 = 6.', $d);
        $this->quiz($s, 'Với x = 5, giá trị của biểu thức (x − 3)² là?', ['4', '2', '25', '16'], 0, 'Tính trong ngoặc trước: (5 − 3)² = 2² = 4.', $d);
    }

    private function seedT2C13(): void
    {
        $s = 'toan-gia-tri-bieu-thuc';
        $d = 'kho';

        $this->matching($s, 'Nối mỗi biểu thức với giá trị của nó tại x = 2.', [['3x + 1', '7'], ['x² − 3', '1'], ['5 − x', '3'], ['2x²', '8']], 'Thay x = 2 vào từng biểu thức rồi tính theo đúng thứ tự phép tính.', $d);
        $this->matching($s, 'Nối mỗi biểu thức với giá trị của nó tại x = −2.', [['x² + 1', '5'], ['−3x', '6'], ['x − 4', '−6'], ['2x² − 5', '3']], 'Khi thay số âm, nhớ đặt trong ngoặc để không nhầm dấu: (−2)² = 4.', $d);
        $this->matching($s, 'Nối mỗi công thức hình học với biểu thức đại số của nó.', [['Diện tích hình vuông cạnh a', 'a²'], ['Diện tích hình chữ nhật dài x rộng y', 'xy'], ['Chu vi hình vuông cạnh a', '4a'], ['Diện tích tam giác đáy b cao h', 'bh/2']], 'Biểu thức đại số giúp ta viết gọn các công thức hình học quen thuộc.', $d);
    }

    private function seedT2C14(): void
    {
        $s = 'toan-goc-va-duong-thang';
        $d = 'de';

        $this->matching($s, 'Nối mỗi ký hiệu với hình mà nó chỉ.', [['∠ABC', 'Góc có đỉnh B'], ['AB ⊥ CD', 'Hai đường thẳng vuông góc'], ['a ∥ b', 'Hai đường thẳng song song'], ['∠xOy = 90°', 'Góc vuông']], 'Ký hiệu ⊥ chỉ quan hệ vuông góc, ký hiệu ∥ chỉ quan hệ song song giữa hai đường thẳng.', $d);
    }

    private function seedT2C15(): void
    {
        $s = 'toan-goc-va-duong-thang';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi góc vào nhóm "GÓC BẸT" hoặc "KHÔNG PHẢI GÓC BẸT".', [['Góc 180°', 'GÓC BẸT'], ['Góc tạo bởi hai tia đối nhau', 'GÓC BẸT'], ['Góc 90°', 'KHÔNG PHẢI GÓC BẸT'], ['Góc 175°', 'KHÔNG PHẢI GÓC BẸT']], 'Góc bẹt có số đo đúng bằng 180°, tạo bởi hai tia đối nhau.', $d);
        $this->sortQ($s, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Góc vuông có số đo 90°', 'ĐÚNG'], ['Góc bẹt có số đo 180°', 'ĐÚNG'], ['Hai đường thẳng song song cắt nhau tại một điểm', 'SAI'], ['Góc tù lớn hơn góc bẹt', 'SAI']], 'Hai đường thẳng song song không bao giờ cắt nhau, và góc tù nhỏ hơn 180° nên nhỏ hơn góc bẹt.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp đường thẳng vào nhóm "VUÔNG GÓC" hoặc "KHÔNG VUÔNG GÓC".', [['Hai cạnh kề của hình chữ nhật', 'VUÔNG GÓC'], ['Kim giờ và kim phút lúc 3 giờ', 'VUÔNG GÓC'], ['Hai cạnh đối của hình chữ nhật', 'KHÔNG VUÔNG GÓC'], ['Hai đường ray xe lửa', 'KHÔNG VUÔNG GÓC']], 'Hai đường thẳng vuông góc cắt nhau tạo thành góc 90°, còn hai đường ray xe lửa thì song song.', $d);
    }

    private function seedT2C16(): void
    {
        $s = 'toan-goc-va-duong-thang';
        $d = 'de';

        $this->fill($s, 'Góc có số đo bằng 90° gọi là góc ___.', [[0, 'vuông']], 'Góc vuông là góc có số đo đúng bằng 90°.', $d);
        $this->fill($s, 'Hai đường thẳng cắt nhau tạo thành góc vuông gọi là hai đường thẳng vuông ___.', [[0, 'góc']], 'Đó chính là định nghĩa của hai đường thẳng vuông góc.', $d);
        $this->fill($s, 'Số đo của góc bẹt là ___ độ.', [[0, '180']], 'Góc bẹt tạo bởi hai tia đối nhau nên có số đo 180°.', $d);
    }

    private function seedT2C17(): void
    {
        $s = 'toan-tam-giac';
        $d = 'trung_binh';

        $this->quiz($s, 'Tam giác có hai cạnh bằng nhau gọi là tam giác gì?', ['Tam giác cân', 'Tam giác đều', 'Tam giác vuông', 'Tam giác tù'], 0, 'Tam giác cân là tam giác có hai cạnh bằng nhau.', $d);
        $this->quiz($s, 'Tam giác ABC có góc A = 60°, góc B = 60°. Góc C bằng bao nhiêu độ?', ['60°', '50°', '70°', '80°'], 0, 'Tổng ba góc tam giác bằng 180° nên góc C = 180° − 60° − 60° = 60°.', $d);
        $this->quiz($s, 'Trong tam giác đều, mỗi góc bằng bao nhiêu độ?', ['60°', '90°', '45°', '70°'], 0, 'Tam giác đều có ba góc bằng nhau, mỗi góc bằng 180° : 3 = 60°.', $d);
    }

    private function seedT2C18(): void
    {
        $s = 'toan-tam-giac';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi tam giác với tính chất đặc trưng của nó.', [['Tam giác đều', 'Ba cạnh bằng nhau'], ['Tam giác cân', 'Hai góc ở đáy bằng nhau'], ['Tam giác vuông', 'Có một góc 90°'], ['Tam giác tù', 'Có một góc lớn hơn 90°']], 'Mỗi loại tam giác được phân biệt bằng tính chất về cạnh hoặc về góc của nó.', $d);
        $this->matching($s, 'Nối mỗi bộ ba góc với loại tam giác tương ứng.', [['60°, 60°, 60°', 'Tam giác đều'], ['90°, 45°, 45°', 'Tam giác vuông cân'], ['100°, 50°, 30°', 'Tam giác tù'], ['80°, 60°, 40°', 'Tam giác nhọn']], 'Nhìn vào góc lớn nhất của bộ ba ta biết ngay tam giác thuộc loại nào.', $d);
        $this->matching($s, 'Nối mỗi đường đặc biệt trong tam giác với định nghĩa của nó.', [['Trung tuyến', 'Đoạn thẳng nối đỉnh với trung điểm cạnh đối diện'], ['Đường cao', 'Đoạn thẳng từ đỉnh vuông góc với cạnh đối diện'], ['Đường trung trực', 'Đường thẳng vuông góc với cạnh tại trung điểm'], ['Đường phân giác', 'Tia chia góc thành hai phần bằng nhau']], 'Bốn đường đặc biệt này giúp ta nghiên cứu sâu các tính chất của tam giác.', $d);
    }

    private function seedT2C19(): void
    {
        $s = 'toan-tam-giac';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi tam giác vào nhóm "CÓ TRỤC ĐỐI XỨNG" hoặc "KHÔNG".', [['Tam giác đều', 'CÓ TRỤC ĐỐI XỨNG'], ['Tam giác cân', 'CÓ TRỤC ĐỐI XỨNG'], ['Tam giác thường (không cân)', 'KHÔNG'], ['Tam giác vuông không cân', 'KHÔNG']], 'Tam giác cân và tam giác đều có trục đối xứng, tam giác thường thì không.', $d);
    }

    private function seedT2C20(): void
    {
        $s = 'ls-nuoc-van-lang';
        $d = 'de';

        $this->matching($s, 'Nối mỗi câu chuyện truyền thuyết với nhân vật chính.', [['Sự tích bánh chưng bánh giầy', 'Lang Liêu'], ['Sơn Tinh Thủy Tinh', 'Mị Nương'], ['Thánh Gióng', 'Phù Đổng Thiên Vương'], ['Sự tích trầu cau', 'Tân và Lang']], 'Các truyền thuyết thời Hùng Vương gửi gắm ước mơ và đạo lý của người Việt cổ.', $d);
    }

    private function seedT2C21(): void
    {
        $s = 'ls-nuoc-van-lang';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi đồ vật, món ăn vào nhóm "THỜI HÙNG VƯƠNG CÓ" hoặc "CHƯA CÓ".', [['Bánh chưng', 'THỜI HÙNG VƯƠNG CÓ'], ['Trống đồng', 'THỜI HÙNG VƯƠNG CÓ'], ['Xe đạp', 'CHƯA CÓ'], ['Điện thoại', 'CHƯA CÓ']], 'Thời Hùng Vương người Việt đã biết gói bánh chưng và đúc trống đồng, còn xe đạp điện thoại là đồ hiện đại.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm "GẮN VỚI THỜI HÙNG VƯƠNG" hoặc "KHÔNG".', [['Phong Châu', 'GẮN VỚI THỜI HÙNG VƯƠNG'], ['Đền Hùng (Phú Thọ)', 'GẮN VỚI THỜI HÙNG VƯƠNG'], ['Thăng Long', 'KHÔNG'], ['Hoa Lư', 'KHÔNG']], 'Phong Châu là kinh đô nước Văn Lang, Đền Hùng là nơi thờ các vua Hùng ở Phú Thọ.', $d);
        $this->sortQ($s, 'Kéo mỗi sự vật vào nhóm "TRUYỀN THUYẾT KỂ" hoặc "DI SẢN CÒN LẠI".', [['Ngựa sắt của Thánh Gióng', 'TRUYỀN THUYẾT KỂ'], ['Bánh chưng của Lang Liêu', 'TRUYỀN THUYẾT KỂ'], ['Trống đồng Đông Sơn', 'DI SẢN CÒN LẠI'], ['Thành Cổ Loa', 'DI SẢN CÒN LẠI']], 'Truyền thuyết kể lại bằng lời, còn trống đồng và thành Cổ Loa là di sản ta vẫn thấy được ngày nay.', $d);
    }

    private function seedT2C22(): void
    {
        $s = 'ls-nuoc-van-lang';
        $d = 'de';

        $this->fill($s, 'Người đứng đầu nước Văn Lang gọi là Hùng ___.', [[0, 'Vương']], 'Vua Hùng Vương là người đứng đầu nhà nước Văn Lang.', $d);
        $this->fill($s, 'Con trai vua Hùng gọi là Quan ___, con gái gọi là Mị Nương.', [[0, 'Lang']], 'Quan Lang và Mị Nương là cách gọi con vua thời Hùng Vương.', $d);
        $this->fill($s, 'Lạc tướng đứng đầu các ___ trong nước Văn Lang.', [[0, 'bộ']], 'Nước Văn Lang chia thành 15 bộ, mỗi bộ do một Lạc tướng đứng đầu.', $d);
    }

    private function seedT2C23(): void
    {
        $s = 'ls-anh-hung-dan-toc';
        $d = 'de';

        $this->quiz($s, 'Bà Triệu quê ở đâu?', ['Thanh Hóa', 'Nghệ An', 'Hà Nội', 'Hải Phòng'], 0, 'Bà Triệu quê ở vùng núi Nưa, thuộc tỉnh Thanh Hóa ngày nay.', $d);
        $this->quiz($s, 'Lý Bí lên ngôi hoàng đế, xưng là gì?', ['Lý Nam Đế', 'Lý Thái Tổ', 'Lý Thánh Tông', 'Lý Nhân Tông'], 0, 'Năm 544, Lý Bí lên ngôi, xưng là Lý Nam Đế, đặt tên nước là Vạn Xuân.', $d);
        $this->quiz($s, 'Ngô Quyền xưng vương, đóng đô ở đâu?', ['Cổ Loa', 'Hoa Lư', 'Thăng Long', 'Phong Châu'], 0, 'Sau chiến thắng Bạch Đằng 938, Ngô Quyền xưng vương và đóng đô ở Cổ Loa.', $d);
    }

    private function seedT2C24(): void
    {
        $s = 'ls-anh-hung-dan-toc';
        $d = 'de';

        $this->matching($s, 'Nối mỗi nhân vật với triều đại phong kiến phương Bắc mà người đó chống lại.', [['Hai Bà Trưng', 'Nhà Đông Hán'], ['Bà Triệu', 'Nhà Đông Ngô'], ['Lý Bí', 'Nhà Lương'], ['Ngô Quyền', 'Nam Hán']], 'Các anh hùng dân tộc đều đứng lên chống lại ách đô hộ của phong kiến phương Bắc.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với tên nước do mình lập ra.', [['Lý Bí', 'Nước Vạn Xuân'], ['Ngô Quyền', 'Nhà Ngô độc lập'], ['Đinh Bộ Lĩnh', 'Nước Đại Cồ Việt'], ['Lý Công Uẩn', 'Nước Đại Việt']], 'Sau khi giành độc lập, các anh hùng đặt tên nước để khẳng định chủ quyền dân tộc.', $d);
        $this->matching($s, 'Nối mỗi biệt danh với nhân vật lịch sử.', [['Vua Đen', 'Mai Thúc Loan'], ['Bố Cái Đại Vương', 'Phùng Hưng'], ['Tiền Ngô Vương', 'Ngô Quyền'], ['Lý Nam Đế', 'Lý Bí']], 'Nhân dân đặt biệt danh để ghi nhớ công lao của các anh hùng dân tộc.', $d);
    }

    private function seedT2C25(): void
    {
        $s = 'ls-anh-hung-dan-toc';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "THẾ KỈ III" hoặc "THẾ KỈ KHÁC".', [['Bà Triệu (248)', 'THẾ KỈ III'], ['Hai Bà Trưng (40)', 'THẾ KỈ KHÁC'], ['Lý Bí (542)', 'THẾ KỈ KHÁC'], ['Phùng Hưng (791)', 'THẾ KỈ KHÁC']], 'Bà Triệu khởi nghĩa năm 248 thuộc thế kỉ III, các nhân vật còn lại thuộc thế kỉ khác.', $d);
        $this->sortQ($s, 'Kéo mỗi cuộc khởi nghĩa vào nhóm "CHỐNG PHONG KIẾN PHƯƠNG BẮC" hoặc "KHÁC".', [['Khởi nghĩa Hai Bà Trưng', 'CHỐNG PHONG KIẾN PHƯƠNG BẮC'], ['Khởi nghĩa Bà Triệu', 'CHỐNG PHONG KIẾN PHƯƠNG BẮC'], ['Khởi nghĩa Lam Sơn', 'CHỐNG PHONG KIẾN PHƯƠNG BẮC'], ['Khởi nghĩa Yên Thế', 'KHÁC']], 'Khởi nghĩa Yên Thế do Hoàng Hoa Thám lãnh đạo chống thực dân Pháp, không phải chống phong kiến phương Bắc.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "NỮ ANH HÙNG" hoặc "NAM ANH HÙNG".', [['Trưng Trắc', 'NỮ ANH HÙNG'], ['Bà Triệu', 'NỮ ANH HÙNG'], ['Ngô Quyền', 'NAM ANH HÙNG'], ['Lý Bí', 'NAM ANH HÙNG']], 'Trưng Trắc và Bà Triệu là hai nữ anh hùng tiêu biểu trong lịch sử chống ngoại xâm.', $d);
    }

    private function seedT2C26(): void
    {
        $s = 'ls-dinh-bo-linh';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "SỨ QUÂN" hoặc "KHÔNG PHẢI SỨ QUÂN".', [['Đỗ Cảnh Thạc', 'SỨ QUÂN'], ['Kiều Công Hãn', 'SỨ QUÂN'], ['Đinh Liễn', 'KHÔNG PHẢI SỨ QUÂN'], ['Nguyễn Bặc', 'KHÔNG PHẢI SỨ QUÂN']], 'Đỗ Cảnh Thạc và Kiều Công Hãn là hai trong 12 sứ quân, còn Đinh Liễn là con Đinh Bộ Lĩnh, Nguyễn Bặc là tướng của nhà Đinh.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm "TRƯỚC KHI ĐINH BỘ LĨNH LÊN NGÔI" hoặc "SAU".', [['Dẹp loạn 12 sứ quân', 'TRƯỚC KHI ĐINH BỘ LĨNH LÊN NGÔI'], ['Cờ lau tập trận thuở nhỏ', 'TRƯỚC KHI ĐINH BỘ LĨNH LÊN NGÔI'], ['Đặt quốc hiệu Đại Cồ Việt', 'SAU'], ['Phong vương cho các con', 'SAU']], 'Đinh Bộ Lĩnh dẹp xong loạn 12 sứ quân mới lên ngôi năm 968, rồi đặt quốc hiệu và phong vương.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm "GẮN VỚI QUÊ HƯƠNG ĐINH BỘ LĨNH" hoặc "KHÔNG".', [['Hoa Lư (Ninh Bình)', 'GẮN VỚI QUÊ HƯƠNG ĐINH BỘ LĨNH'], ['Động Hoa Lư', 'GẮN VỚI QUÊ HƯƠNG ĐINH BỘ LĨNH'], ['Cổ Loa', 'KHÔNG'], ['Thăng Long', 'KHÔNG']], 'Đinh Bộ Lĩnh quê ở Hoa Lư, Ninh Bình, sau này cũng chọn Hoa Lư làm kinh đô.', $d);
    }

    private function seedT2C27(): void
    {
        $s = 'ls-dinh-bo-linh';
        $d = 'de';

        $this->fill($s, 'Năm 968, Đinh Bộ Lĩnh đặt quốc hiệu là Đại Cồ ___.', [[0, 'Việt']], 'Đại Cồ Việt là quốc hiệu đầu tiên của nhà nước phong kiến độc lập do Đinh Bộ Lĩnh đặt.', $d);
        $this->fill($s, 'Đinh Tiên Hoàng bị ám sát năm ___, kết thúc triều Đinh.', [[0, '979']], 'Năm 979, Đinh Tiên Hoàng và Đinh Liễn bị Đỗ Thích ám sát, triều Đinh kết thúc.', $d);
        $this->fill($s, 'Thời nhỏ, Đinh Bộ Lĩnh thường lấy bông lau làm ___ để tập trận cùng trẻ chăn trâu.', [[0, 'cờ']], 'Tích "cờ lau tập trận" kể về tuổi thơ của Đinh Bộ Lĩnh ở vùng Hoa Lư.', $d);
    }

    private function seedT2C28(): void
    {
        $s = 'ls-le-hoan';
        $d = 'de';

        $this->quiz($s, 'Lê Hoàn quê ở đâu?', ['Thanh Hóa', 'Ninh Bình', 'Hà Nam', 'Nghệ An'], 0, 'Lê Hoàn quê ở Xuân Lập, Thọ Xuân, tỉnh Thanh Hóa.', $d);
        $this->quiz($s, 'Sau khi lên ngôi, Lê Hoàn lấy niên hiệu là gì?', ['Thiên Phúc', 'Thuận Thiên', 'Thái Bình', 'Cảnh Thụy'], 0, 'Lê Hoàn lên ngôi năm 980, lấy niên hiệu Thiên Phúc.', $d);
        $this->quiz($s, 'Ai là người chỉ huy quân Tống xâm lược Đại Cồ Việt năm 981?', ['Hầu Nhân Bảo', 'Ô Mã Nhi', 'Sầm Nghi Đống', 'Liễu Thăng'], 0, 'Năm 981, nhà Tống cử Hầu Nhân Bảo đem quân xâm lược, bị Lê Hoàn đánh bại.', $d);
    }

    private function seedT2C29(): void
    {
        $s = 'ls-le-hoan';
        $d = 'de';

        $this->matching($s, 'Nối mỗi nhân vật với quan hệ trong hoàng tộc nhà Tiền Lê.', [['Lê Hoàn', 'Người sáng lập nhà Tiền Lê'], ['Lê Long Đĩnh', 'Vị vua cuối cùng nhà Tiền Lê'], ['Lê Trung Tông', 'Vị vua ở ngôi chỉ 3 ngày'], ['Dương Vân Nga', 'Hoàng hậu của hai triều Đinh – Lê']], 'Nhà Tiền Lê trải qua ba đời vua: Lê Hoàn, Lê Trung Tông và Lê Long Đĩnh.', $d);
        $this->matching($s, 'Nối mỗi sự kiện thời Tiền Lê với địa điểm diễn ra.', [['Lê Hoàn đăng cơ năm 980', 'Kinh đô Hoa Lư'], ['Đại phá quân Tống năm 981', 'Sông Bạch Đằng'], ['Lê Hoàn cày tịch điền', 'Núi Đọi (Hà Nam)'], ['Lê Hoàn băng hà năm 1005', 'Kinh đô Hoa Lư']], 'Các sự kiện lớn thời Tiền Lê đều gắn với kinh đô Hoa Lư và những địa danh lịch sử.', $d);
        $this->matching($s, 'Nối mỗi niên hiệu với vị vua đã dùng nó.', [['Thiên Phúc', 'Lê Đại Hành'], ['Ứng Thiên', 'Lê Trung Tông'], ['Cảnh Thụy', 'Lê Long Đĩnh'], ['Thái Bình', 'Đinh Tiên Hoàng']], 'Mỗi vị vua khi lên ngôi đều đặt niên hiệu riêng để tính năm trị vì.', $d);
    }

    private function seedT2C30(): void
    {
        $s = 'ls-le-hoan';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "NHÀ TIỀN LÊ" hoặc "TRIỀU KHÁC".', [['Lê Hoàn', 'NHÀ TIỀN LÊ'], ['Lê Long Đĩnh', 'NHÀ TIỀN LÊ'], ['Đinh Tiên Hoàng', 'TRIỀU KHÁC'], ['Lý Thái Tổ', 'TRIỀU KHÁC']], 'Nhà Tiền Lê (980–1009) nằm giữa nhà Đinh và nhà Lý trong lịch sử Việt Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm "TRONG ĐỜI LÊ HOÀN" hoặc "KHÁC".', [['Đánh thắng quân Tống năm 981', 'TRONG ĐỜI LÊ HOÀN'], ['Lê Hoàn mất năm 1005', 'TRONG ĐỜI LÊ HOÀN'], ['Lý Công Uẩn dời đô năm 1010', 'KHÁC'], ['Chiến thắng Bạch Đằng năm 938', 'KHÁC']], 'Lê Hoàn trị vì từ 980 đến 1005, nên các sự kiện ngoài khoảng này không thuộc đời ông.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm "VỀ TRẬN BẠCH ĐẰNG 981" hoặc "KHÔNG".', [['Quân ta đóng cọc trên sông', 'VỀ TRẬN BẠCH ĐẰNG 981'], ['Hầu Nhân Bảo tử trận', 'VỀ TRẬN BẠCH ĐẰNG 981'], ['Ngô Quyền chỉ huy', 'KHÔNG'], ['Thoát Hoan rút chạy', 'KHÔNG']], 'Trận Bạch Đằng 981 do Lê Hoàn chỉ huy, khác với trận Bạch Đằng 938 của Ngô Quyền và 1288 của Trần Hưng Đạo.', $d);
    }

    private function seedT2C31(): void
    {
        $s = 'ls-dong-bo-dau';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi lần kháng chiến với kết quả của nó.', [['Lần 1 (1258)', 'Quân Mông Cổ rút khỏi Thăng Long'], ['Lần 2 (1285)', 'Thoát Hoan chui ống đồng chạy về nước'], ['Lần 3 (1287–1288)', 'Ô Mã Nhi bị bắt ở Bạch Đằng'], ['Hội nghị Bình Than (1282)', 'Trần Quốc Tuấn được giao chỉ huy kháng chiến']], 'Ba lần kháng chiến chống quân Nguyên – Mông đều kết thúc bằng thắng lợi của quân dân Đại Việt.', $d);
    }

    private function seedT2C32(): void
    {
        $s = 'ls-dong-bo-dau';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi trận đánh vào nhóm "TRẬN THỦY CHIẾN" hoặc "TRẬN BỘ CHIẾN".', [['Bạch Đằng 1288', 'TRẬN THỦY CHIẾN'], ['Vân Đồn 1287', 'TRẬN THỦY CHIẾN'], ['Tây Kết 1285', 'TRẬN BỘ CHIẾN'], ['Chương Dương 1285', 'TRẬN BỘ CHIẾN'], ['Hàm Tử 1285', 'TRẬN BỘ CHIẾN'], ['Vạn Kiếp 1285', 'TRẬN BỘ CHIẾN']], 'Nhà Trần thắng cả trên sông (Bạch Đằng, Vân Đồn) và trên bộ (Tây Kết, Chương Dương, Hàm Tử).', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "VUA TRẦN" hoặc "TƯỚNG TRẦN".', [['Trần Thánh Tông', 'VUA TRẦN'], ['Trần Nhân Tông', 'VUA TRẦN'], ['Trần Hưng Đạo', 'TƯỚNG TRẦN'], ['Trần Quang Khải', 'TƯỚNG TRẦN']], 'Trong kháng chiến, vua Trần trực tiếp ra trận cùng các tướng lĩnh chỉ huy.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm "KẾ VƯỜN KHÔNG NHÀ TRỐNG" hoặc "KHÁC".', [['Dân rút vào rừng, mang hết lương thực', 'KẾ VƯỜN KHÔNG NHÀ TRỐNG'], ['Bỏ trống kinh thành Thăng Long', 'KẾ VƯỜN KHÔNG NHÀ TRỐNG'], ['Quyết chiến trực diện ngay biên giới', 'KHÁC'], ['Đầu hàng quân giặc', 'KHÁC']], 'Kế vườn không nhà trống làm quân giặc thiếu lương thực, kiệt sức mà không đánh được trận nào.', $d);
    }

    private function seedT2C33(): void
    {
        $s = 'ls-dong-bo-dau';
        $d = 'trung_binh';

        $this->fill($s, 'Năm 1285, quân Nguyên ồ ạt tiến vào Đại Việt, mở đầu cuộc kháng chiến lần thứ ___.', [[0, 'hai']], 'Cuộc kháng chiến lần thứ hai diễn ra năm 1285 do Thoát Hoan chỉ huy quân Nguyên.', $d);
        $this->fill($s, 'Trần Hưng Đạo được vua Trần phong làm Quốc công Tiết ___.', [[0, 'chế']], 'Quốc công Tiết chế là chức tổng chỉ huy quân đội cao nhất trong kháng chiến.', $d);
        $this->fill($s, 'Trận Vân Đồn năm 1287 do Trần Khánh ___ chỉ huy, phá tan đoàn thuyền lương của giặc.', [[0, 'Dư']], 'Trần Khánh Dư đánh tan đoàn thuyền lương ở Vân Đồn khiến quân Nguyên đói khát.', $d);
    }

    private function seedT2C34(): void
    {
        $s = 'ls-tran-hung-dao';
        $d = 'trung_binh';

        $this->quiz($s, 'Tên thật của Trần Hưng Đạo là gì?', ['Trần Quốc Tuấn', 'Trần Quang Khải', 'Trần Thủ Độ', 'Trần Khánh Dư'], 0, 'Trần Hưng Đạo tên thật là Trần Quốc Tuấn, con trai của An Sinh vương Trần Liễu.', $d);
        $this->quiz($s, 'Câu nói "Đầu tôi chưa rơi xuống đất, xin bệ hạ đừng lo" là của ai?', ['Trần Hưng Đạo', 'Trần Quang Khải', 'Trần Bình Trọng', 'Phạm Ngũ Lão'], 0, 'Khi vua Trần hỏi có nên hàng giặc, Trần Hưng Đạo đã khẳng khái đáp như vậy.', $d);
        $this->quiz($s, 'Trần Hưng Đạo được nhân dân tôn thờ với danh hiệu gì?', ['Đức Thánh Trần', 'Hưng Đạo Đại Vương', 'An Sinh Vương', 'Thái Sư Trần Thủ Độ'], 0, 'Nhân dân tôn thờ Trần Hưng Đạo là Đức Thánh Trần, lập đền thờ ở Kiếp Bạc.', $d);
    }

    private function seedT2C35(): void
    {
        $s = 'ls-tran-hung-dao';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi người thân với quan hệ của họ với Trần Hưng Đạo.', [['An Sinh vương Trần Liễu', 'Cha đẻ'], ['Trần Thái Tông', 'Bác ruột'], ['Trần Thánh Tông', 'Anh họ'], ['Phạm Ngũ Lão', 'Con rể']], 'Trần Hưng Đạo xuất thân hoàng tộc nhà Trần nên được vua tin cậy giao trọng trách.', $d);
        $this->matching($s, 'Nối mỗi câu nói nổi tiếng với hoàn cảnh ra đời của nó.', [['Ta thà làm quỷ nước Nam chứ không thèm làm vương đất Bắc', 'Khi sứ Nguyên sang dụ hàng'], ['Đầu tôi chưa rơi xuống đất, xin bệ hạ đừng lo', 'Khi vua hỏi có nên hàng giặc'], ['Khoan thư sức dân để làm kế sâu rễ bền gốc', 'Lời dặn vua về kế giữ nước lâu dài'], ['Nếu bệ hạ muốn hàng, xin chém đầu thần trước', 'Tại hội nghị Diên Hồng năm 1284']], 'Những câu nói của Trần Hưng Đạo thể hiện khí phách quyết không chịu khuất phục giặc.', $d);
        $this->matching($s, 'Nối mỗi địa danh với sự kiện gắn với Trần Hưng Đạo.', [['Vạn Kiếp', 'Đại bản doanh của Trần Hưng Đạo'], ['Bạch Đằng', 'Nơi ông bày trận cọc gỗ năm 1288'], ['Kiếp Bạc', 'Đền thờ Đức Thánh Trần'], ['Chi Lăng', 'Nơi quân ta phục kích nhưng không phải trận của ông']], 'Vạn Kiếp là đại bản doanh, Bạch Đằng là chiến trường, Kiếp Bạc là nơi thờ ông.', $d);
    }

    private function seedT2C36(): void
    {
        $s = 'ls-tran-hung-dao';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi câu nói vào nhóm "CỦA TRẦN HƯNG ĐẠO" hoặc "CỦA NGƯỜI KHÁC".', [['Đầu tôi chưa rơi xuống đất, xin bệ hạ đừng lo', 'CỦA TRẦN HƯNG ĐẠO'], ['Ta thà làm quỷ nước Nam chứ không thèm làm vương đất Bắc', 'CỦA TRẦN HƯNG ĐẠO'], ['Không thành công cũng thành nhân', 'CỦA NGƯỜI KHÁC'], ['Đánh cho để dài tóc, đánh cho để đen răng', 'CỦA NGƯỜI KHÁC']], 'Hai câu đầu là của Trần Hưng Đạo, câu thứ ba của Nguyễn Thái Học, câu cuối của Nguyễn Huệ.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "HỌ TRẦN" hoặc "KHÁC HỌ".', [['Trần Quốc Tuấn', 'HỌ TRẦN'], ['Trần Quang Khải', 'HỌ TRẦN'], ['Phạm Ngũ Lão', 'KHÁC HỌ'], ['Yết Kiêu', 'KHÁC HỌ']], 'Phạm Ngũ Lão và Yết Kiêu là gia tướng tài giỏi phò tá Trần Hưng Đạo.', $d);
        $this->sortQ($s, 'Kéo mỗi tác phẩm vào nhóm "BINH PHÁP" hoặc "VĂN HỌC".', [['Binh thư yếu lược', 'BINH PHÁP'], ['Vạn Kiếp tông bí truyền thư', 'BINH PHÁP'], ['Hịch tướng sĩ', 'VĂN HỌC'], ['Tứ thư thuyết ước', 'VĂN HỌC']], 'Trần Hưng Đạo vừa là nhà quân sự với các binh thư, vừa là tác giả áng văn Hịch tướng sĩ.', $d);
    }

    private function seedT2C37(): void
    {
        $s = 'toan-so-tu-nhien-lop-6-1';
        $d = 'de';

        $this->matching($s, 'Nối mỗi phép tính cộng nhẩm với mẹo tính đã dùng.', [['98 + 37', 'Cộng 100 rồi trừ 3'], ['199 + 56', 'Cộng 200 rồi trừ 1'], ['1000 − 298', 'Trừ 300 rồi cộng 2'], ['76 + 24', 'Tròn trăm, bằng 100']], 'Làm tròn số lên rồi điều chỉnh lại là mẹo tính nhẩm rất hiệu quả.', $d);
    }

    private function seedT2C38(): void
    {
        $s = 'toan-phan-so-lop-6-1';
        $d = 'de';

        $this->matching($s, 'Nối mỗi phép trừ phân số với kết quả của nó.', [['5/6 − 1/6', '2/3'], ['3/4 − 1/2', '1/4'], ['7/8 − 3/8', '1/2'], ['4/5 − 1/5', '3/5']], 'Trừ phân số cùng mẫu thì trừ tử số, khác mẫu thì quy đồng trước rồi mới trừ.', $d);
        $this->matching($s, 'Nối mỗi hỗn số với phân số bằng nó.', [['1 1/2', '3/2'], ['2 1/4', '9/4'], ['3 2/3', '11/3'], ['1 3/5', '8/5']], 'Đổi hỗn số thành phân số: lấy phần nguyên nhân mẫu cộng tử, giữ nguyên mẫu số.', $d);
    }

    private function seedT2C39(): void
    {
        $s = 'toan-phan-so-lop-6-2';
        $d = 'de';

        $this->matching($s, 'Nối mỗi phép so sánh phân số với dấu cần điền vào chỗ trống.', [['1/2 ... 2/4', '='], ['3/5 ... 1/2', '>'], ['2/7 ... 1/3', '<'], ['5/6 ... 7/8', '<']], 'So sánh phân số bằng cách quy đồng mẫu số hoặc so với phân số trung gian quen thuộc.', $d);
    }

    private function seedT2C40(): void
    {
        $s = 'toan-phan-so-lop-7-1';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi số thập phân với phân số bằng nó.', [['0,5', '1/2'], ['0,25', '1/4'], ['0,75', '3/4'], ['0,2', '1/5']], 'Số thập phân hữu hạn đều viết được dưới dạng phân số có mẫu là lũy thừa của 10 rồi rút gọn.', $d);
    }

    private function seedT2C41(): void
    {
        $s = 'toan-phan-so-lop-7-2';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi bài toán tỉ số với phép tính đúng.', [['Tìm 2/5 của 30', '30 × 2/5'], ['Số nào bằng 3/4 của 20', '20 × 3/4'], ['Tìm tỉ số của 3 và 4', '3 : 4'], ['Một nửa của 50', '50 : 2']], 'Muốn tìm phân số của một số ta nhân số đó với phân số, tìm tỉ số ta dùng phép chia.', $d);
        $this->matching($s, 'Nối mỗi phép tính với kết quả là số nguyên.', [['5/6 × 12', '10'], ['3/8 × 16', '6'], ['7/10 × 20', '14'], ['4/9 × 18', '8']], 'Khi tử số chia hết cho mẫu số sau khi rút gọn, tích của phân số với số tự nhiên là số nguyên.', $d);
    }

    private function seedT2C42(): void
    {
        $s = 'toan-bieu-thuc-dai-so-lop-8-1';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi phép nhân đa thức với kết quả thu gọn.', [['x(x + 3)', 'x² + 3x'], ['2a(a − 5)', '2a² − 10a'], ['(x + 2)(x + 3)', 'x² + 5x + 6'], ['3b(2b + 1)', '6b² + 3b']], 'Nhân đa thức: nhân mỗi hạng tử của đa thức này với từng hạng tử của đa thức kia rồi thu gọn.', $d);
    }

    private function seedT2C43(): void
    {
        $s = 'ls-dung-nuoc-lop-6-1';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi nghề vào nhóm "NGHỀ CHÍNH CỦA NGƯỜI LẠC VIỆT" hoặc "KHÔNG".', [['Trồng lúa nước', 'NGHỀ CHÍNH CỦA NGƯỜI LẠC VIỆT'], ['Đúc trống đồng', 'NGHỀ CHÍNH CỦA NGƯỜI LẠC VIỆT'], ['Buôn bán với nước ngoài bằng tàu lớn', 'KHÔNG'], ['Khai thác dầu mỏ', 'KHÔNG']], 'Người Lạc Việt sống chủ yếu bằng nghề trồng lúa nước, đồng thời giỏi đúc đồng.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm "ĐÚNG VỀ NƯỚC VĂN LANG" hoặc "SAI".', [['Nước Văn Lang có 15 bộ', 'ĐÚNG VỀ NƯỚC VĂN LANG'], ['Kinh đô Văn Lang ở Phong Châu', 'ĐÚNG VỀ NƯỚC VĂN LANG'], ['Vua Hùng xưng là Hoàng đế', 'SAI'], ['Người Văn Lang dùng chữ Quốc ngữ', 'SAI']], 'Người đứng đầu Văn Lang gọi là Hùng Vương, chữ Quốc ngữ mãi sau này mới có.', $d);
    }

    private function seedT2C44(): void
    {
        $s = 'ls-dung-nuoc-lop-6-2';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm "VỀ AN DƯƠNG VƯƠNG" hoặc "KHÔNG".', [['Xây thành Cổ Loa', 'VỀ AN DƯƠNG VƯƠNG'], ['Có nỏ thần', 'VỀ AN DƯƠNG VƯƠNG'], ['Dẹp loạn 12 sứ quân', 'KHÔNG'], ['Đặt quốc hiệu Đại Cồ Việt', 'KHÔNG']], 'An Dương Vương xây thành Cổ Loa và có nỏ thần; dẹp loạn 12 sứ quân và đặt quốc hiệu Đại Cồ Việt là việc của Đinh Bộ Lĩnh.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm "TRƯỚC NĂM 208 TCN" hoặc "SAU NĂM 208 TCN".', [['Thục Phán lên ngôi An Dương Vương', 'TRƯỚC NĂM 208 TCN'], ['An Dương Vương xây thành Cổ Loa', 'TRƯỚC NĂM 208 TCN'], ['Triệu Đà đánh chiếm Âu Lạc', 'SAU NĂM 208 TCN'], ['Âu Lạc sáp nhập vào nước Nam Việt', 'SAU NĂM 208 TCN']], 'Năm 208 TCN (có tài liệu ghi 179 TCN) nước Âu Lạc mất, mở đầu thời Bắc thuộc.', $d);
    }

    private function seedT2C45(): void
    {
        $s = 'ls-dung-nuoc-lop-7-1';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "NGHĨA QUÂN HAI BÀ TRƯNG" hoặc "QUÂN ĐÔ HỘ".', [['Trưng Trắc', 'NGHĨA QUÂN HAI BÀ TRƯNG'], ['Thi Sách', 'NGHĨA QUÂN HAI BÀ TRƯNG'], ['Tô Định', 'QUÂN ĐÔ HỘ'], ['Mã Viện', 'QUÂN ĐÔ HỘ']], 'Tô Định là thái thú đô hộ tàn ác, Mã Viện là tướng nhà Hán sang đàn áp khởi nghĩa.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm "GẮN VỚI HAI BÀ TRƯNG" hoặc "KHÔNG".', [['Mê Linh', 'GẮN VỚI HAI BÀ TRƯNG'], ['Hát Môn', 'GẮN VỚI HAI BÀ TRƯNG'], ['Luy Lâu', 'KHÔNG'], ['Cổ Loa', 'KHÔNG']], 'Hát Môn là quê hương, Mê Linh là nơi Hai Bà Trưng xưng vương; Luy Lâu là trị sở của chính quyền đô hộ.', $d);
    }

    private function seedT2C46(): void
    {
        $s = 'ls-dung-nuoc-lop-7-2';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "THAM GIA TRẬN BẠCH ĐẰNG 938" hoặc "KHÔNG".', [['Ngô Quyền', 'THAM GIA TRẬN BẠCH ĐẰNG 938'], ['Lưu Hoằng Tháo', 'THAM GIA TRẬN BẠCH ĐẰNG 938'], ['Kiều Công Tiễn', 'KHÔNG'], ['Dương Đình Nghệ', 'KHÔNG']], 'Kiều Công Tiễn bị Ngô Quyền giết trước trận đánh, Dương Đình Nghệ mất năm 937.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm "ĐÚNG VỀ TRẬN BẠCH ĐẰNG 938" hoặc "SAI".', [['Quân ta đóng cọc gỗ trên sông', 'ĐÚNG VỀ TRẬN BẠCH ĐẰNG 938'], ['Lưu Hoằng Tháo tử trận', 'ĐÚNG VỀ TRẬN BẠCH ĐẰNG 938'], ['Quân ta đánh lúc nước triều đang lên', 'SAI'], ['Ngô Quyền xưng vương ngay trong trận', 'SAI']], 'Ngô Quyền nhử giặc vào bãi cọc lúc triều rút, rồi xưng vương vào năm 939.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm "TRƯỚC TRẬN BẠCH ĐẰNG 938" hoặc "SAU".', [['Dương Đình Nghệ bị Kiều Công Tiễn giết', 'TRƯỚC TRẬN BẠCH ĐẰNG 938'], ['Kiều Công Tiễn cầu cứu quân Nam Hán', 'TRƯỚC TRẬN BẠCH ĐẰNG 938'], ['Ngô Quyền xưng vương', 'SAU'], ['Ngô Quyền đóng đô ở Cổ Loa', 'SAU']], 'Trận Bạch Đằng 938 là mốc chia: trước đó là loạn Kiều Công Tiễn, sau đó là nhà Ngô độc lập.', $d);
    }

    private function seedT2C47(): void
    {
        $s = 'ls-dinh-tien-le-lop-7-1';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm "CẢI CÁCH CỦA ĐINH TIÊN HOÀNG" hoặc "KHÔNG".', [['Đặt quốc hiệu Đại Cồ Việt', 'CẢI CÁCH CỦA ĐINH TIÊN HOÀNG'], ['Đúc tiền Thái Bình hưng bảo', 'CẢI CÁCH CỦA ĐINH TIÊN HOÀNG'], ['Phong vương cho các con', 'CẢI CÁCH CỦA ĐINH TIÊN HOÀNG'], ['Dời đô về Thăng Long', 'KHÔNG']], 'Dời đô về Thăng Long là việc của Lý Công Uẩn năm 1010, không phải thời Đinh.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "CON CỦA ĐINH TIÊN HOÀNG" hoặc "KHÔNG".', [['Đinh Liễn', 'CON CỦA ĐINH TIÊN HOÀNG'], ['Đinh Toàn (Phế Đế)', 'CON CỦA ĐINH TIÊN HOÀNG'], ['Đinh Hạng Lang', 'CON CỦA ĐINH TIÊN HOÀNG'], ['Nguyễn Bặc', 'KHÔNG']], 'Nguyễn Bặc là đại thần khai quốc nhà Đinh, không phải con vua.', $d);
        $this->sortQ($s, 'Kéo mỗi năm vào nhóm "THUỘC TRIỀU ĐINH" hoặc "KHÁC".', [['968', 'THUỘC TRIỀU ĐINH'], ['975', 'THUỘC TRIỀU ĐINH'], ['980', 'KHÁC'], ['1010', 'KHÁC']], 'Triều Đinh tồn tại từ 968 đến 979; năm 980 đã là nhà Tiền Lê, 1010 là nhà Lý.', $d);
    }

    private function seedT2C48(): void
    {
        $s = 'ls-dinh-tien-le-lop-7-2';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm "CHÍNH SÁCH CỦA LÊ HOÀN" hoặc "KHÔNG".', [['Cày tịch điền đầu xuân', 'CHÍNH SÁCH CỦA LÊ HOÀN'], ['Đặt quan trấn giữ biên giới', 'CHÍNH SÁCH CỦA LÊ HOÀN'], ['Thân chinh đánh Chiêm Thành 982', 'CHÍNH SÁCH CỦA LÊ HOÀN'], ['Mở khoa thi Nho học', 'KHÔNG']], 'Khoa thi Nho học đầu tiên mở năm 1075 thời nhà Lý, không phải thời Tiền Lê.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "THỜI TIỀN LÊ" hoặc "KHÁC".', [['Phạm Cự Lượng', 'THỜI TIỀN LÊ'], ['Đào Cam Mộc', 'THỜI TIỀN LÊ'], ['Trần Thủ Độ', 'KHÁC'], ['Ngô Sĩ Liên', 'KHÁC']], 'Trần Thủ Độ là người nhà Trần, Ngô Sĩ Liên là sử gia thời Lê sơ.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm "NĂM 981" hoặc "NĂM KHÁC".', [['Đại phá quân Tống trên sông Bạch Đằng', 'NĂM 981'], ['Lê Hoàn lên ngôi', 'NĂM KHÁC'], ['Lê Hoàn mất', 'NĂM KHÁC'], ['Lê Hoàn thân chinh đánh Chiêm Thành', 'NĂM KHÁC']], 'Lê Hoàn lên ngôi 980, đánh Chiêm Thành 982, mất 1005; chỉ trận Bạch Đằng chống Tống là năm 981.', $d);
    }

    private function seedT2C49(): void
    {
        $s = 'ls-dinh-tien-le-lop-8-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi đơn vị hành chính vào nhóm "THỜI ĐINH – TIỀN LÊ CÓ" hoặc "KHÔNG CÓ".', [['Đạo', 'THỜI ĐINH – TIỀN LÊ CÓ'], ['Châu', 'THỜI ĐINH – TIỀN LÊ CÓ'], ['Lộ', 'KHÔNG CÓ'], ['Phủ', 'KHÔNG CÓ']], 'Thời Đinh – Tiền Lê cả nước chia thành 10 đạo; lộ và phủ là đơn vị hành chính thời Trần trở đi.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm "NHÀ NƯỚC QUÂN CHỦ" hoặc "KHÔNG PHẢI".', [['Vua đứng đầu, nắm mọi quyền hành', 'NHÀ NƯỚC QUÂN CHỦ'], ['Triều đình có quân đội riêng', 'NHÀ NƯỚC QUÂN CHỦ'], ['Dân trực tiếp bầu ra vua', 'KHÔNG PHẢI'], ['Vua do dân làng cử ra', 'KHÔNG PHẢI']], 'Nhà nước Đinh – Tiền Lê là nhà nước quân chủ: vua là người đứng đầu tối cao.', $d);
    }

    private function seedT2C50(): void
    {
        $s = 'ls-dinh-tien-le-lop-8-2';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm "KINH TẾ NÔNG NGHIỆP" hoặc "THỦ CÔNG – THƯƠNG NGHIỆP".', [['Trồng lúa hai vụ', 'KINH TẾ NÔNG NGHIỆP'], ['Chăn nuôi trâu bò', 'KINH TẾ NÔNG NGHIỆP'], ['Đúc tiền đồng', 'THỦ CÔNG – THƯƠNG NGHIỆP'], ['Dệt vải', 'THỦ CÔNG – THƯƠNG NGHIỆP']], 'Thời Đinh – Tiền Lê nông nghiệp là gốc, thủ công nghiệp như đúc tiền, dệt vải cũng phát triển.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm "ĐỜI SỐNG VẬT CHẤT" hoặc "ĐỜI SỐNG TINH THẦN".', [['Nhà ở, cơm ăn, áo mặc', 'ĐỜI SỐNG VẬT CHẤT'], ['Ruộng lúa, trâu bò', 'ĐỜI SỐNG VẬT CHẤT'], ['Tín ngưỡng thờ cúng tổ tiên', 'ĐỜI SỐNG TINH THẦN'], ['Lễ hội làng', 'ĐỜI SỐNG TINH THẦN']], 'Đời sống vật chất là ăn ở sinh hoạt, đời sống tinh thần là tín ngưỡng, lễ hội.', $d);
        $this->sortQ($s, 'Kéo mỗi nghề vào nhóm "THỜI ĐINH – TIỀN LÊ PHÁT TRIỂN" hoặc "CHƯA CÓ".', [['Đúc đồng', 'THỜI ĐINH – TIỀN LÊ PHÁT TRIỂN'], ['Rèn sắt', 'THỜI ĐINH – TIỀN LÊ PHÁT TRIỂN'], ['Dệt vải', 'THỜI ĐINH – TIỀN LÊ PHÁT TRIỂN'], ['Khai thác dầu khí', 'CHƯA CÓ'], ['Sản xuất ô tô', 'CHƯA CÓ']], 'Nghề đúc đồng, rèn sắt, dệt vải phát triển mạnh; dầu khí và ô tô là ngành hiện đại.', $d);
    }

    private function seedT2C51(): void
    {
        $s = 'ls-chong-nguyen-mong-lop-8-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi trận đánh vào nhóm "KHÁNG CHIẾN LẦN 2 (1285)" hoặc "LẦN KHÁC".', [['Tây Kết', 'KHÁNG CHIẾN LẦN 2 (1285)'], ['Hàm Tử', 'KHÁNG CHIẾN LẦN 2 (1285)'], ['Chương Dương', 'KHÁNG CHIẾN LẦN 2 (1285)'], ['Bạch Đằng', 'LẦN KHÁC'], ['Vân Đồn', 'LẦN KHÁC']], 'Tây Kết, Hàm Tử, Chương Dương là các trận phản công năm 1285; Bạch Đằng và Vân Đồn thuộc lần 3.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "TỬ TRẬN VÌ NƯỚC" hoặc "KHÁC".', [['Trần Bình Trọng', 'TỬ TRẬN VÌ NƯỚC'], ['Toa Đô', 'TỬ TRẬN VÌ NƯỚC'], ['Ô Mã Nhi', 'KHÁC'], ['Thoát Hoan', 'KHÁC']], 'Trần Bình Trọng thà chết không hàng; Toa Đô tử trận ở Tây Kết; Ô Mã Nhi bị bắt, Thoát Hoan chạy thoát.', $d);
        $this->sortQ($s, 'Kéo mỗi kế sách vào nhóm "CỦA TRẦN HƯNG ĐẠO" hoặc "CỦA NGƯỜI KHÁC".', [['Vườn không nhà trống', 'CỦA TRẦN HƯNG ĐẠO'], ['Đóng cọc gỗ ở sông Bạch Đằng', 'CỦA TRẦN HƯNG ĐẠO'], ['Đánh nhanh thắng nhanh', 'CỦA NGƯỜI KHÁC'], ['Dụ hàng vua quan nhà Trần', 'CỦA NGƯỜI KHÁC']], 'Đánh nhanh thắng nhanh và dụ hàng là ý đồ của quân Nguyên, đều bị ta bẻ gãy.', $d);
    }

    private function seedT2C52(): void
    {
        $s = 'ls-chong-nguyen-mong-lop-8-2';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm "PHE TA Ở TRẬN BẠCH ĐẰNG 1288" hoặc "KHÔNG".', [['Trần Hưng Đạo', 'PHE TA Ở TRẬN BẠCH ĐẰNG 1288'], ['Yết Kiêu', 'PHE TA Ở TRẬN BẠCH ĐẰNG 1288'], ['Dã Tượng', 'PHE TA Ở TRẬN BẠCH ĐẰNG 1288'], ['Trần Ích Tắc', 'KHÔNG'], ['Trần Kiện', 'KHÔNG']], 'Yết Kiêu, Dã Tượng là gia tướng trung thành; Trần Ích Tắc và Trần Kiện đã đầu hàng giặc.', $d);
    }

    private function seedT2C53(): void
    {
        $s = 'toan-thpt-lop-12-lop-12-1';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi hàm số với đạo hàm cấp hai của nó.', [['y = x³', "y'' = 6x"], ['y = x⁴', "y'' = 12x²"], ['y = 2x²', "y'' = 4"], ['y = x⁵', "y'' = 20x³"]], 'Đạo hàm cấp hai là đạo hàm của đạo hàm cấp một, dùng để xét tính lồi lõm của đồ thị.', $d);
        $this->matching($s, 'Nối mỗi hàm số với giá trị đạo hàm của nó tại x = 1.', [['y = x³', '3'], ['y = 3x²', '6'], ['y = x² + x', '3'], ['y = 4x', '4']], 'Tính đạo hàm trước rồi thay x = 1 vào kết quả để được giá trị cần tìm.', $d);
    }

    private function seedT2C54(): void
    {
        $s = 'lich-su-thpt-12-lop-12-3';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi sự kiện lịch sử hiện đại với mốc năm của nó.', [['Cách mạng tháng Tám thành công', '1945'], ['Chiến thắng Điện Biên Phủ', '1954'], ['Giải phóng miền Nam, thống nhất đất nước', '1975'], ['Đại hội VI, mở đầu công cuộc Đổi mới', '1986']], 'Bốn mốc son này định hình lịch sử Việt Nam hiện đại từ 1945 đến nay.', $d);
        $this->matching($s, 'Nối mỗi thành tựu của công cuộc Đổi mới với lĩnh vực của nó.', [['Xuất khẩu gạo trong top đầu thế giới', 'Nông nghiệp'], ['Thu hút vốn đầu tư nước ngoài FDI', 'Công nghiệp – đầu tư'], ['Phổ cập giáo dục các cấp', 'Giáo dục'], ['Xóa đói giảm nghèo', 'Xã hội']], 'Từ 1986, Đổi mới mang lại thành tựu toàn diện trên mọi lĩnh vực đời sống.', $d);
    }
}
