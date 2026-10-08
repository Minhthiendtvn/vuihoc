<?php

namespace Database\Seeders;

use App\Models\FillAnswer;
use App\Models\Lesson;
use App\Models\MatchingPair;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SortItem;
use Illuminate\Database\Seeder;

/**
 * Dữ liệu câu hỏi mẫu — TỰ VIẾT 100% TIẾNG VIỆT, không copy từ nguồn nào.
 * Mỗi CHỦ ĐỀ có đủ câu hỏi cho cả 4 kiểu chơi (quiz/matching/sort/fill),
 * mỗi kiểu ≥ 3 câu. Bài học thứ nhất của mỗi kỹ năng chứa quiz + matching,
 * bài học thứ hai chứa sort + fill.
 */
class QuestionSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];

    public function run(): void
    {
        $this->seedToan();
        $this->seedTiengViet();
        $this->seedTiengAnh();
        $this->seedKhoaHoc();
        $this->seedLichSu();
    }

    // ---------------- helpers ----------------

    private function newQuestion(string $lessonSlug, string $gameType, string $prompt, string $explanation, string $difficulty = 'de', int $points = 10): Question
    {
        if (! isset($this->lessonBySlug[$lessonSlug])) {
            $this->lessonBySlug[$lessonSlug] = Lesson::where('slug', $lessonSlug)->firstOrFail();
        }
        $lesson = $this->lessonBySlug[$lessonSlug];
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

    /** $answers: mảng các cặp [blank_index, answer_text]; cùng blank_index có thể có nhiều đáp án chấp nhận. */
    private function fill(string $lesson, string $prompt, array $answers, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'fill', $prompt, $explanation, $difficulty);
        foreach ($answers as $i => [$blankIndex, $text]) {
            FillAnswer::create([
                'question_id' => $q->id, 'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1,
            ]);
        }
    }

    // ================= TOÁN =================

    private function seedToan(): void
    {
        // ---- Số tự nhiên: cộng trừ ----
        $this->quiz('toan-cong-tru-so-tu-nhien', '125 + 78 = ?',
            ['193', '203', '213', '183'], 1,
            'Đặt tính rồi cộng: 125 + 78 = 203. Nhớ 1 ở hàng chục khi 5 + 8 = 13.');
        $this->quiz('toan-cong-tru-so-tu-nhien', '940 − 267 = ?',
            ['663', '673', '683', '763'], 1,
            '940 − 267 = 673. Mượn 1 ở hàng trăm vì 4 chục không trừ được 6 chục.');
        $this->quiz('toan-cong-tru-so-tu-nhien', 'Một cửa hàng bán 245 cuốn sách buổi sáng và 318 cuốn buổi chiều. Cả ngày bán được bao nhiêu cuốn?',
            ['563', '553', '573', '463'], 0,
            'Cả ngày bán được 245 + 318 = 563 cuốn sách.');

        $this->matching('toan-cong-tru-so-tu-nhien', 'Nối mỗi phép tính với kết quả đúng.',
            [['45 + 27', '72'], ['96 − 38', '58'], ['123 + 45', '168'], ['200 − 75', '125']],
            '45 + 27 = 72; 96 − 38 = 58; 123 + 45 = 168; 200 − 75 = 125.');
        $this->matching('toan-cong-tru-so-tu-nhien', 'Nối mỗi bài toán lời văn với phép tính đúng.',
            [['An có 15 viên bi, Bình cho thêm 8 viên. An có tất cả bao nhiêu viên?', '15 + 8'],
             ['Lan có 20 cái kẹo, ăn hết 6 cái. Lan còn mấy cái?', '20 − 6'],
             ['Lớp 6A có 32 bạn, lớp 6B có 29 bạn. Cả hai lớp có mấy bạn?', '32 + 29']],
            'Thêm vào thì cộng, bớt đi thì trừ: 15 + 8, 20 − 6, 32 + 29.');
        $this->matching('toan-cong-tru-so-tu-nhien', 'Nối mỗi tổng hoặc hiệu với giá trị của nó.',
            [['Tổng của 150 và 230', '380'], ['Hiệu của 500 và 145', '355'], ['Tổng của 999 và 1', '1000']],
            '150 + 230 = 380; 500 − 145 = 355; 999 + 1 = 1000.');

        // ---- Số tự nhiên: nhân chia ----
        $this->sortQ('toan-nhan-chia-so-tu-nhien', 'Kéo mỗi phép tính vào nhóm ĐÚNG hoặc SAI.',
            [['12 × 5 = 60', 'Đúng'], ['7 × 8 = 54', 'Sai'], ['144 : 12 = 12', 'Đúng'], ['96 : 4 = 26', 'Sai']],
            '12 × 5 = 60 đúng; 7 × 8 = 56 nên 54 là sai; 144 : 12 = 12 đúng; 96 : 4 = 24 nên 26 là sai.');
        $this->sortQ('toan-nhan-chia-so-tu-nhien', 'Kéo mỗi phép tính vào nhóm kết quả CHẴN hoặc LẺ.',
            [['15 × 3 = 45', 'Lẻ'], ['24 : 2 = 12', 'Chẵn'], ['7 × 6 = 42', 'Chẵn'], ['9 × 5 = 45', 'Lẻ']],
            'Số lẻ nhân số lẻ cho kết quả lẻ; số chẵn chia hết cho 2 cho kết quả chẵn.');
        $this->sortQ('toan-nhan-chia-so-tu-nhien', 'Kéo mỗi phép tính vào nhóm NHÂN hoặc CHIA.',
            [['36 × 2', 'Nhân'], ['100 : 4', 'Chia'], ['15 × 4', 'Nhân'], ['81 : 9', 'Chia']],
            'Dấu × là phép nhân, dấu : là phép chia.');
        $this->fill('toan-nhan-chia-so-tu-nhien', '25 × 4 = ___', [[0, '100']],
            '25 × 4 = 100. Có thể nhẩm: 25 × 4 = (20 × 4) + (5 × 4) = 80 + 20 = 100.');
        $this->fill('toan-nhan-chia-so-tu-nhien', 'Một hộp có 12 cái bánh. 8 hộp như vậy có tất cả ___ cái bánh.', [[0, '96']],
            '12 × 8 = 96 cái bánh.');
        $this->fill('toan-nhan-chia-so-tu-nhien', '360 : 9 = ___', [[0, '40']],
            '360 : 9 = 40. Nhẩm: 36 : 9 = 4 nên 360 : 9 = 40.');

        // ---- Phân số: cộng trừ ----
        $this->quiz('toan-cong-tru-phan-so', '1/3 + 2/3 = ?',
            ['1', '5/3', '3/9', '5/9'], 0,
            'Hai phân số cùng mẫu số: cộng tử số, giữ nguyên mẫu số. 1/3 + 2/3 = 3/3 = 1.', 'trung_binh');
        $this->quiz('toan-cong-tru-phan-so', '3/4 − 1/4 = ?',
            ['2/8', '1/2', '3/8', '1/4'], 1,
            '3/4 − 1/4 = 2/4, rút gọn được 1/2.', 'trung_binh');
        $this->quiz('toan-cong-tru-phan-so', '1/2 + 1/4 = ?',
            ['2/6', '3/4', '1/6', '2/4'], 1,
            'Quy đồng: 1/2 = 2/4. Vậy 2/4 + 1/4 = 3/4.', 'trung_binh');

        $this->matching('toan-cong-tru-phan-so', 'Nối mỗi phép tính với kết quả đúng (đã rút gọn).',
            [['1/5 + 2/5', '3/5'], ['5/6 − 1/6', '2/3'], ['3/8 + 1/8', '1/2'], ['7/10 − 3/10', '2/5']],
            'Cùng mẫu số thì cộng/trừ tử số: 4/6 rút gọn = 2/3; 4/8 rút gọn = 1/2; 4/10 rút gọn = 2/5.', 'trung_binh');
        $this->matching('toan-cong-tru-phan-so', 'Nối mỗi yêu cầu với kết quả đúng.',
            [['Quy đồng 1/3 và 1/6', '2/6 và 1/6'], ['Quy đồng 1/4 và 1/2', '1/4 và 2/4'], ['Phân số tối giản của 4/8', '1/2']],
            'Quy đồng là đưa về cùng mẫu số; tối giản là chia cả tử và mẫu cho ước chung lớn nhất.', 'trung_binh');
        $this->matching('toan-cong-tru-phan-so', 'Nối mỗi phép tính với kết quả đúng.',
            [['1/2 + 1/3', '5/6'], ['2/3 − 1/6', '1/2'], ['3/4 + 1/8', '7/8']],
            '1/2 + 1/3 = 3/6 + 2/6 = 5/6; 2/3 − 1/6 = 4/6 − 1/6 = 1/2; 3/4 + 1/8 = 6/8 + 1/8 = 7/8.', 'trung_binh');

        // ---- Phân số: nhân chia ----
        $this->sortQ('toan-nhan-chia-phan-so', 'Kéo mỗi giá trị vào nhóm LỚN HƠN 1 hoặc NHỎ HƠN 1.',
            [['3/2', 'Lớn hơn 1'], ['1/2', 'Nhỏ hơn 1'], ['5/3', 'Lớn hơn 1'], ['2/7', 'Nhỏ hơn 1']],
            'Phân số có tử lớn hơn mẫu thì lớn hơn 1, tử nhỏ hơn mẫu thì nhỏ hơn 1.', 'trung_binh');
        $this->sortQ('toan-nhan-chia-phan-so', 'Kéo mỗi phép tính vào nhóm NHÂN hoặc CHIA.',
            [['2/3 × 4/5', 'Nhân'], ['5/6 : 2/3', 'Chia'], ['1/2 × 3/4', 'Nhân'], ['7/8 : 1/4', 'Chia']],
            'Dấu × là phép nhân, dấu : là phép chia.', 'trung_binh');
        $this->sortQ('toan-nhan-chia-phan-so', 'Kéo mỗi phép tính vào nhóm ĐÚNG hoặc SAI.',
            [['1/2 × 4 = 2', 'Đúng'], ['2/3 : 2 = 1/3', 'Đúng'], ['3/5 × 5/3 = 1', 'Đúng'], ['4/7 : 4 = 1/28', 'Sai']],
            '4/7 : 4 = 4/28 = 1/7, nên đáp án 1/28 là sai.', 'trung_binh');
        $this->fill('toan-nhan-chia-phan-so', '2/3 × 3/4 = ___', [[0, '1/2']],
            'Nhân tử với tử, mẫu với mẫu: 6/12 = 1/2.', 'trung_binh');
        $this->fill('toan-nhan-chia-phan-so', '3/5 : 2 = ___', [[0, '3/10']],
            '3/5 : 2 = 3/5 × 1/2 = 3/10.', 'trung_binh');
        $this->fill('toan-nhan-chia-phan-so', '5/6 : 5 = ___', [[0, '1/6']],
            '5/6 : 5 = 5/6 × 1/5 = 5/30 = 1/6.', 'trung_binh');

        // ---- Đại số: thu gọn đơn thức ----
        $this->quiz('toan-thu-gon-don-thuc', 'Thu gọn đơn thức 2x · 3x² ta được',
            ['6x³', '5x³', '6x²', '5x²'], 0,
            'Nhân hệ số: 2 × 3 = 6; nhân biến: x · x² = x³. Kết quả 6x³.', 'trung_binh');
        $this->quiz('toan-thu-gon-don-thuc', 'Bậc của đơn thức 4x²y³ là',
            ['5', '6', '2', '3'], 0,
            'Bậc của đơn thức là tổng số mũ của các biến: 2 + 3 = 5.', 'trung_binh');
        $this->quiz('toan-thu-gon-don-thuc', 'Đơn thức nào đồng dạng với 5xy²?',
            ['3xy²', '3x²y', '5x²y²', '3x²y²'], 0,
            'Hai đơn thức đồng dạng có cùng phần biến: cùng là xy².', 'trung_binh');

        $this->matching('toan-thu-gon-don-thuc', 'Nối mỗi phép nhân đơn thức với kết quả thu gọn.',
            [['2a · 5a', '10a²'], ['3x² · 2x', '6x³'], ['4y · y³', '4y⁴']],
            'Nhân hệ số với hệ số, cộng số mũ của cùng biến: a·a = a², x²·x = x³, y·y³ = y⁴.', 'trung_binh');
        $this->matching('toan-thu-gon-don-thuc', 'Nối mỗi đơn thức với bậc của nó.',
            [['7x', '1'], ['x³y²', '5'], ['9', '0']],
            'Bậc là tổng số mũ các biến; đơn thức hằng (số) có bậc 0.', 'trung_binh');
        $this->matching('toan-thu-gon-don-thuc', 'Nối mỗi phép cộng/trừ đơn thức đồng dạng với kết quả.',
            [['5x + 3x', '8x'], ['7y² − 2y²', '5y²'], ['4ab + ab', '5ab']],
            'Chỉ cộng/trừ được các đơn thức đồng dạng: cộng hệ số, giữ nguyên phần biến.', 'trung_binh');

        // ---- Đại số: giá trị biểu thức ----
        $this->sortQ('toan-gia-tri-bieu-thuc', 'Kéo mỗi phép tính vào nhóm làm TRƯỚC hoặc làm SAU.',
            [['Phép tính trong ngoặc', 'Trước'], ['Phép nhân', 'Trước'], ['Phép cộng', 'Sau'], ['Phép trừ', 'Sau']],
            'Thứ tự: trong ngoặc trước, rồi đến nhân/chia, cuối cùng cộng/trừ.', 'kho');
        $this->sortQ('toan-gia-tri-bieu-thuc', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['Với x = 2 thì 3x + 1 = 7', 'Đúng'], ['Với x = 3 thì 2x² = 12', 'Sai'], ['Với y = 5 thì 10 − y = 5', 'Đúng'], ['Với a = 4 thì a² + 1 = 17', 'Đúng']],
            'Thay x = 3 vào 2x² được 2 × 9 = 18, không phải 12.', 'kho');
        $this->sortQ('toan-gia-tri-bieu-thuc', 'Kéo mỗi biểu thức vào nhóm MỘT BIẾN hoặc HAI BIẾN.',
            [['3x + 2', 'Một biến'], ['xy + 5', 'Hai biến'], ['x² − 4x + 1', 'Một biến'], ['2ab − b', 'Hai biến']],
            'Biểu thức một biến chỉ chứa một chữ (x), hai biến chứa hai chữ (x và y, hoặc a và b).', 'kho');
        $this->fill('toan-gia-tri-bieu-thuc', 'Với x = 5, giá trị của biểu thức 2x + 3 là ___', [[0, '13']],
            'Thay x = 5: 2 × 5 + 3 = 10 + 3 = 13.', 'kho');
        $this->fill('toan-gia-tri-bieu-thuc', 'Với a = 2 và b = 3, giá trị của biểu thức a² + b là ___', [[0, '7']],
            'Thay a = 2, b = 3: 2² + 3 = 4 + 3 = 7.', 'kho');
        $this->fill('toan-gia-tri-bieu-thuc', 'Với x = 4, giá trị của biểu thức 5x − 7 là ___', [[0, '13']],
            'Thay x = 4: 5 × 4 − 7 = 20 − 7 = 13.', 'kho');

        // ---- Hình học: góc và đường thẳng ----
        $this->quiz('toan-goc-va-duong-thang', 'Góc nào sau đây là góc nhọn?',
            ['Góc 45 độ', 'Góc 90 độ', 'Góc 120 độ', 'Góc 180 độ'], 0,
            'Góc nhọn có số đo nhỏ hơn 90 độ. Góc 45 độ là góc nhọn.');
        $this->quiz('toan-goc-va-duong-thang', 'Hai đường thẳng không bao giờ cắt nhau gọi là hai đường thẳng',
            ['song song', 'vuông góc', 'cắt nhau', 'trùng nhau'], 0,
            'Hai đường thẳng song song không có điểm chung nào.');
        $this->quiz('toan-goc-va-duong-thang', 'Góc bẹt có số đo bằng',
            ['90 độ', '180 độ', '360 độ', '270 độ'], 1,
            'Góc bẹt tạo bởi hai tia đối nhau, số đo 180 độ.');

        $this->matching('toan-goc-va-duong-thang', 'Nối mỗi loại góc với số đo của nó.',
            [['Góc vuông', '90 độ'], ['Góc nhọn', 'Nhỏ hơn 90 độ'], ['Góc tù', 'Lớn hơn 90 độ và nhỏ hơn 180 độ']],
            'Góc vuông = 90°; góc nhọn < 90°; góc tù nằm giữa 90° và 180°.');
        $this->matching('toan-goc-va-duong-thang', 'Nối mỗi khái niệm với mô tả đúng.',
            [['Góc bẹt', '180 độ'], ['Hai đường thẳng vuông góc', 'Tạo thành góc 90 độ'], ['Đường trung trực', 'Vuông góc và đi qua trung điểm của đoạn thẳng']],
            'Góc bẹt = 180°; vuông góc tạo góc 90°; trung trực vừa vuông góc vừa qua trung điểm.');
        $this->matching('toan-goc-va-duong-thang', 'Nối mỗi khái niệm với ý nghĩa của nó.',
            [['Hai tia đối nhau', 'Tạo thành một góc bẹt'], ['Tia phân giác', 'Chia góc thành hai góc bằng nhau'], ['Hai góc kề bù', 'Có tổng số đo bằng 180 độ']],
            'Tia phân giác chia đôi góc; hai góc kề bù bù nhau thành 180°.');

        // ---- Hình học: tam giác ----
        $this->sortQ('toan-tam-giac', 'Kéo mỗi mô tả vào loại tam giác đúng.',
            [['Tam giác có ba góc nhọn', 'Tam giác nhọn'], ['Tam giác có một góc tù', 'Tam giác tù'], ['Tam giác có một góc vuông', 'Tam giác vuông'], ['Tam giác có góc A = 100°', 'Tam giác tù']],
            'Góc 100° là góc tù, nên tam giác có góc 100° là tam giác tù.', 'trung_binh');
        $this->sortQ('toan-tam-giac', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['Tổng ba góc trong tam giác bằng 180 độ', 'Đúng'], ['Tam giác đều có ba góc bằng 60 độ', 'Đúng'], ['Tam giác vuông có hai góc vuông', 'Sai'], ['Một tam giác có thể có hai góc tù', 'Sai']],
            'Tam giác chỉ có tối đa một góc vuông hoặc một góc tù, vì tổng ba góc là 180°.', 'trung_binh');
        $this->sortQ('toan-tam-giac', 'Kéo mỗi yếu tố vào nhóm CẠNH hoặc GÓC của tam giác ABC.',
            [['AB', 'Cạnh'], ['Góc A', 'Góc'], ['BC', 'Cạnh'], ['Góc C', 'Góc']],
            'Tam giác ABC có ba cạnh AB, BC, CA và ba góc A, B, C.', 'trung_binh');
        $this->fill('toan-tam-giac', 'Tam giác ABC có góc A = 60°, góc B = 70°. Góc C bằng ___ độ.', [[0, '50']],
            'Góc C = 180° − 60° − 70° = 50°.', 'trung_binh');
        $this->fill('toan-tam-giac', 'Tổng số đo ba góc trong của một tam giác bằng ___ độ.', [[0, '180']],
            'Đây là tính chất cơ bản nhất của tam giác.', 'trung_binh');
        $this->fill('toan-tam-giac', 'Tam giác đều có mỗi góc bằng ___ độ.', [[0, '60']],
            'Ba góc bằng nhau, tổng 180°, nên mỗi góc 180° : 3 = 60°.', 'trung_binh');
    }

    // ================= TIẾNG VIỆT =================

    private function seedTiengViet(): void
    {
        // ---- Từ loại ----
        $this->quiz('tv-cac-tu-loai', 'Từ nào sau đây là danh từ?',
            ['học sinh', 'chạy', 'đẹp', 'nhanh'], 0,
            'Danh từ chỉ người, vật, hiện tượng. "Học sinh" chỉ người nên là danh từ.');
        $this->quiz('tv-cac-tu-loai', 'Từ nào sau đây là động từ?',
            ['bơi', 'bàn', 'xanh', 'rất'], 0,
            'Động từ chỉ hành động, trạng thái. "Bơi" là hành động nên là động từ.');
        $this->quiz('tv-cac-tu-loai', 'Từ nào sau đây là tính từ?',
            ['cao', 'nhà', 'uống', 'mỗi'], 0,
            'Tính từ chỉ đặc điểm, tính chất. "Cao" chỉ đặc điểm chiều cao nên là tính từ.');

        $this->matching('tv-cac-tu-loai', 'Nối mỗi từ với từ loại của nó.',
            [['sách', 'Danh từ'], ['hát', 'Động từ'], ['đỏ', 'Tính từ']],
            '"Sách" chỉ đồ vật (danh từ), "hát" chỉ hành động (động từ), "đỏ" chỉ màu sắc (tính từ).');
        $this->matching('tv-cac-tu-loai', 'Nối mỗi từ với từ loại của nó.',
            [['con mèo', 'Danh từ'], ['nhảy', 'Động từ'], ['vui', 'Tính từ']],
            '"Con mèo" chỉ con vật (danh từ), "nhảy" chỉ hành động (động từ), "vui" chỉ trạng thái (tính từ).');
        $this->matching('tv-cac-tu-loai', 'Nối mỗi từ với từ loại của nó.',
            [['một', 'Số từ'], ['những', 'Lượng từ'], ['đang', 'Phó từ']],
            '"Một" chỉ số lượng (số từ), "những" chỉ lượng khái quát (lượng từ), "đang" bổ nghĩa cho động từ (phó từ).');

        // ---- Câu đơn ----
        $this->sortQ('tv-cau-don', 'Kéo mỗi bộ phận vào nhóm CHỦ NGỮ hoặc VỊ NGỮ.',
            [['Mẹ', 'Chủ ngữ'], ['đang nấu cơm', 'Vị ngữ'], ['Chim', 'Chủ ngữ'], ['hót líu lo', 'Vị ngữ']],
            'Chủ ngữ trả lời "ai? cái gì?", vị ngữ trả lời "làm gì? thế nào?".', 'trung_binh');
        $this->sortQ('tv-cau-don', 'Kéo mỗi câu vào kiểu câu đúng.',
            [['Hôm nay trời đẹp.', 'Câu kể'], ['Bạn tên gì?', 'Câu hỏi'], ['Ôi, đẹp quá!', 'Câu cảm'], ['Hãy giữ trật tự!', 'Câu khiến']],
            'Câu kể kết thúc bằng dấu chấm, câu hỏi bằng dấu hỏi, câu cảm bộc lộ cảm xúc, câu khiến ra lệnh/yêu cầu.', 'trung_binh');
        $this->sortQ('tv-cau-don', 'Kéo mỗi thành phần vào nhóm CHÍNH hoặc PHỤ.',
            [['Chủ ngữ', 'Thành phần chính'], ['Vị ngữ', 'Thành phần chính'], ['Trạng ngữ', 'Thành phần phụ'], ['Bổ ngữ', 'Thành phần phụ']],
            'Chủ ngữ và vị ngữ là hai thành phần chính bắt buộc của câu.', 'trung_binh');
        $this->fill('tv-cau-don', 'Trong câu "Hoa nở rộ", chủ ngữ là ___', [[0, 'Hoa']],
            'Chủ ngữ trả lời câu hỏi "cái gì nở rộ?" — đó là "Hoa".', 'trung_binh');
        $this->fill('tv-cau-don', 'Trong câu "Bé đang chơi", vị ngữ là ___', [[0, 'đang chơi']],
            'Vị ngữ trả lời "bé làm gì?" — đó là "đang chơi".', 'trung_binh');
        $this->fill('tv-cau-don', 'Câu "Trời mưa to quá!" thuộc kiểu câu ___', [[0, 'cảm'], [0, 'câu cảm']],
            'Câu bộc lộ cảm xúc ngạc nhiên, kết thúc bằng dấu chấm than là câu cảm.', 'trung_binh');

        // ---- Dấu hỏi ngã ----
        $this->quiz('tv-dau-hoi-dau-nga', 'Từ nào viết ĐÚNG chính tả?',
            ['nghỉ ngơi', 'nghĩ ngơi', 'nghỉ ngoi', 'nghĩ ngoi'], 0,
            '"Nghỉ ngơi" (dấu hỏi) nghĩa là dừng làm việc để thư giãn.');
        $this->quiz('tv-dau-hoi-dau-nga', 'Từ nào viết ĐÚNG chính tả?',
            ['suy nghĩ', 'suy nghỉ', 'xuy nghĩ', 'xuy nghỉ'], 0,
            '"Suy nghĩ" (dấu ngã) nghĩa là dùng trí óc để xem xét.');
        $this->quiz('tv-dau-hoi-dau-nga', 'Từ nào viết ĐÚNG chính tả?',
            ['mãi mãi', 'mải mãi', 'mãi mải', 'mải mải'], 0,
            '"Mãi mãi" (dấu ngã) nghĩa là lâu dài, không bao giờ hết.');

        $this->matching('tv-dau-hoi-dau-nga', 'Nối mỗi từ với dấu thanh đúng của nó.',
            [['nghỉ', 'dấu hỏi'], ['nghĩ', 'dấu ngã'], ['mãi', 'dấu ngã'], ['mải', 'dấu hỏi']],
            '"Nghỉ ngơi" dấu hỏi, "suy nghĩ" dấu ngã, "mãi mãi" dấu ngã, "mải mê" dấu hỏi.');
        $this->matching('tv-dau-hoi-dau-nga', 'Nối mỗi từ với dấu thanh đúng của nó.',
            [['vẻ đẹp', 'dấu hỏi'], ['vẽ tranh', 'dấu ngã'], ['kẻ thù', 'dấu hỏi']],
            '"Vẻ đẹp" dấu hỏi, "vẽ tranh" dấu ngã, "kẻ thù" dấu hỏi.');
        $this->matching('tv-dau-hoi-dau-nga', 'Nối mỗi từ với dấu thanh đúng của nó.',
            [['cũ', 'dấu ngã'], ['củ khoai', 'dấu hỏi'], ['đũa', 'dấu ngã'], ['đủ', 'dấu hỏi']],
            '"Cũ" (không mới) dấu ngã, "củ khoai" dấu hỏi, "đũa" (dụng cụ ăn) dấu ngã, "đủ" dấu hỏi.');

        // ---- Âm đầu ----
        $this->sortQ('tv-am-dau', 'Kéo mỗi từ vào nhóm âm đầu CH hoặc TR.',
            [['chú', 'ch'], ['trâu', 'tr'], ['chim', 'ch'], ['tre', 'tr']],
            '"Chú, chim" bắt đầu bằng ch; "trâu, tre" bắt đầu bằng tr.');
        $this->sortQ('tv-am-dau', 'Kéo mỗi từ vào nhóm âm đầu D, GI hoặc R.',
            [['dê', 'd'], ['gió', 'gi'], ['rừng', 'r'], ['dao', 'd']],
            '"Dê, dao" âm d; "gió" âm gi; "rừng" âm r.');
        $this->sortQ('tv-am-dau', 'Kéo mỗi cách viết vào nhóm ĐÚNG hoặc SAI.',
            [['con trâu', 'Đúng'], ['cái chổi', 'Đúng'], ['con dun (con giun)', 'Sai'], ['mặt chời (mặt trời)', 'Sai']],
            '"Con giun" viết gi, "mặt trời" viết tr.');
        $this->fill('tv-am-dau', 'Điền âm đầu còn thiếu: ___im hót líu lo.', [[0, 'ch']],
            '"Chim" viết với âm đầu ch.');
        $this->fill('tv-am-dau', 'Điền âm đầu còn thiếu: con ___âu ăn cỏ.', [[0, 'tr']],
            '"Trâu" viết với âm đầu tr.');
        $this->fill('tv-am-dau', 'Điền âm đầu còn thiếu: ___ó thổi mạnh.', [[0, 'gi']],
            '"Gió" viết với âm đầu gi.');

        // ---- Văn miêu tả ----
        $this->quiz('tv-bai-van-mieu-ta', 'Bài văn miêu tả thường gồm mấy phần?',
            ['2 phần', '3 phần', '4 phần', '5 phần'], 1,
            'Bài văn miêu tả gồm 3 phần: mở bài, thân bài, kết bài.', 'trung_binh');
        $this->quiz('tv-bai-van-mieu-ta', 'Phần nào nêu cảm nghĩ của người viết về đối tượng miêu tả?',
            ['Mở bài', 'Thân bài', 'Kết bài', 'Tựa đề'], 2,
            'Kết bài nêu cảm nghĩ, tình cảm của người viết.', 'trung_binh');
        $this->quiz('tv-bai-van-mieu-ta', 'Từ ngữ nào gợi hình ảnh rõ nhất?',
            ['lấp lánh', 'vui', 'rất', 'đã'], 0,
            '"Lấp lánh" là từ láy gợi hình ảnh ánh sáng nhấp nháy, rất hợp văn miêu tả.', 'trung_binh');

        $this->matching('tv-bai-van-mieu-ta', 'Nối mỗi phần của bài văn với nhiệm vụ của nó.',
            [['Mở bài', 'Giới thiệu đối tượng miêu tả'], ['Thân bài', 'Tả chi tiết đối tượng'], ['Kết bài', 'Nêu cảm nghĩ của người viết']],
            'Mở bài giới thiệu, thân bài tả chi tiết từ bao quát đến cụ thể, kết bài nêu cảm nghĩ.', 'trung_binh');
        $this->matching('tv-bai-van-mieu-ta', 'Nối mỗi kiểu bài với nội dung miêu tả.',
            [['Tả người', 'Miêu tả ngoại hình, tính cách'], ['Tả cảnh', 'Miêu tả phong cảnh'], ['Tả đồ vật', 'Miêu tả hình dáng, công dụng']],
            'Mỗi đối tượng có trọng tâm miêu tả khác nhau.', 'trung_binh');
        $this->matching('tv-bai-van-mieu-ta', 'Nối mỗi loại từ với ví dụ đúng.',
            [['Từ láy', 'lấp lánh, rì rào'], ['Từ ghép', 'xanh biếc, tươi tốt'], ['So sánh', 'đẹp như tranh vẽ']],
            'Từ láy gợi hình gợi cảm, từ ghép gọi tên sự vật, so sánh làm hình ảnh sinh động.', 'trung_binh');

        // ---- Biện pháp tu từ ----
        $this->sortQ('tv-bien-phap-tu-tu', 'Kéo mỗi câu vào biện pháp tu từ đúng.',
            [['Mặt trời như quả cầu lửa', 'So sánh'], ['Chị gió thì thầm', 'Nhân hoá'], ['Trăng tròn như cái đĩa', 'So sánh'], ['Thuyền về có nhớ bến chăng (thuyền chỉ người đi xa)', 'Ẩn dụ']],
            'So sánh dùng từ "như"; nhân hoá gán đặc điểm con người cho sự vật; ẩn dụ gọi tên sự vật này bằng tên sự vật khác.', 'trung_binh');
        $this->sortQ('tv-bien-phap-tu-tu', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['"Hoa cười" là nhân hoá', 'Đúng'], ['"Như" là từ thường dùng trong so sánh', 'Đúng'], ['"Mắt sáng như sao" là ẩn dụ', 'Sai'], ['Nhân hoá gán đặc điểm con người cho sự vật', 'Đúng']],
            '"Mắt sáng như sao" có từ "như" nên là so sánh, không phải ẩn dụ.', 'trung_binh');
        $this->sortQ('tv-bien-phap-tu-tu', 'Kéo mỗi câu vào nhóm CÓ hoặc KHÔNG dùng biện pháp tu từ.',
            [['Cánh đồng vàng ươm', 'Không'], ['Cánh đồng như tấm thảm vàng', 'Có biện pháp tu từ'], ['Trời xanh', 'Không'], ['Chú ong chăm chỉ như người thợ', 'Có biện pháp tu từ']],
            'Câu có từ so sánh "như" hoặc gán đặc điểm con người là có biện pháp tu từ.', 'trung_binh');
        $this->fill('tv-bien-phap-tu-tu', 'Điền từ còn thiếu: "Trăng tròn ___ cái đĩa".', [[0, 'như']],
            'Từ "như" nối hai sự vật để so sánh.', 'trung_binh');
        $this->fill('tv-bien-phap-tu-tu', 'Biện pháp gán đặc điểm của con người cho sự vật gọi là ___.', [[0, 'nhân hoá'], [0, 'nhân hóa']],
            'Ví dụ: "chị gió thì thầm", "hoa cười".', 'trung_binh');
        $this->fill('tv-bien-phap-tu-tu', 'Trong câu "Lá vàng rơi", từ "vàng" thuộc từ loại ___.', [[0, 'tính từ']],
            '"Vàng" chỉ màu sắc của lá nên là tính từ.', 'trung_binh');
    }

    // ================= TIẾNG ANH =================

    private function seedTiengAnh(): void
    {
        // ---- Family vocabulary ----
        $this->quiz('en-my-family', 'What is "bố" in English?',
            ['father', 'mother', 'brother', 'sister'], 0,
            '"Father" means "bố". "Mother" is "mẹ", "brother" is "anh/em trai", "sister" is "chị/em gái".');
        $this->quiz('en-my-family', 'What is "chị gái" in English?',
            ['brother', 'sister', 'aunt', 'cousin'], 1,
            '"Sister" means "chị gái" or "em gái". "Brother" means "anh/em trai".');
        $this->quiz('en-my-family', 'What is "ông" in English?',
            ['grandmother', 'grandfather', 'uncle', 'father'], 1,
            '"Grandfather" means "ông". "Grandmother" means "bà".');

        $this->matching('en-my-family', 'Match each English word with its Vietnamese meaning.',
            [['father', 'bố'], ['mother', 'mẹ'], ['brother', 'anh/em trai'], ['sister', 'chị/em gái']],
            'father = bố, mother = mẹ, brother = anh/em trai, sister = chị/em gái.');
        $this->matching('en-my-family', 'Match each English word with its Vietnamese meaning.',
            [['grandfather', 'ông'], ['grandmother', 'bà'], ['uncle', 'chú/bác trai']],
            'grandfather = ông, grandmother = bà, uncle = chú/bác trai.');
        $this->matching('en-my-family', 'Match each English word with its Vietnamese meaning.',
            [['aunt', 'cô/dì'], ['cousin', 'anh chị em họ'], ['son', 'con trai']],
            'aunt = cô/dì, cousin = anh chị em họ, son = con trai.');

        // ---- At school vocabulary ----
        $this->sortQ('en-at-school', 'Drag each word into SCHOOL THINGS or PEOPLE.',
            [['book', 'School things'], ['teacher', 'People'], ['pen', 'School things'], ['student', 'People']],
            'Book and pen are things; teacher and student are people.');
        $this->sortQ('en-at-school', 'Drag each statement into TRUE or FALSE.',
            [['"ruler" means "thước kẻ"', 'Đúng'], ['"eraser" means "bút chì"', 'Sai'], ['"classroom" means "lớp học"', 'Đúng'], ['"notebook" means "cục tẩy"', 'Sai']],
            '"Eraser" means "cục tẩy", "notebook" means "vở".');
        $this->sortQ('en-at-school', 'Drag each word into IN THE CLASSROOM or IN THE BAG.',
            [['desk', 'In the classroom'], ['pencil', 'In the bag'], ['board', 'In the classroom'], ['ruler', 'In the bag']],
            'Desk and board stay in the classroom; pencil and ruler go in the bag.');
        $this->fill('en-at-school', 'I write with a ___. (bút)', [[0, 'pen']],
            'A pen is used for writing.');
        $this->fill('en-at-school', 'The ___ teaches us English. (giáo viên)', [[0, 'teacher']],
            'A teacher teaches students.');
        $this->fill('en-at-school', 'Open your ___ to page 10. (sách)', [[0, 'book']],
            '"Book" means "sách".');

        // ---- Present simple ----
        $this->quiz('en-present-simple', 'She ___ to school every day.',
            ['go', 'goes', 'going', 'gone'], 1,
            'With he/she/it, add -s/-es to the verb: she goes.', 'trung_binh');
        $this->quiz('en-present-simple', 'They ___ football on Sundays.',
            ['plays', 'play', 'playing', 'played'], 1,
            'With I/you/we/they, keep the base verb: they play.', 'trung_binh');
        $this->quiz('en-present-simple', 'He ___ like milk.',
            ['don’t', 'doesn’t', 'isn’t', 'not'], 1,
            'Negative with he/she/it uses "doesn’t": he doesn’t like milk.', 'trung_binh');

        $this->matching('en-present-simple', 'Match each subject with the correct verb form of "go".',
            [['I / you / we / they', 'go'], ['he / she / it', 'goes']],
            'I/you/we/they + go; he/she/it + goes.', 'trung_binh');
        $this->matching('en-present-simple', 'Match each full form with its short form.',
            [['do not', 'don’t'], ['does not', 'doesn’t']],
            'do not = don’t; does not = doesn’t.', 'trung_binh');
        $this->matching('en-present-simple', 'Match each sentence start with the correct verb.',
            [['She ___ (watch) TV', 'watches'], ['They ___ (study) English', 'study'], ['He ___ (have) a bike', 'has']],
            'she watches, they study, he has.', 'trung_binh');

        // ---- Prepositions ----
        $this->sortQ('en-prepositions', 'Drag each sentence into the correct preposition: ON, IN or UNDER.',
            [['The picture is ___ the wall', 'on'], ['The fish is ___ the water', 'in'], ['The cat is ___ the chair', 'under'], ['The apple is ___ the box', 'in']],
            'on = on a surface, in = inside, under = below.');
        $this->sortQ('en-prepositions', 'Drag each statement into TRUE or FALSE.',
            [['"on the wall" is correct', 'Đúng'], ['"in the table" (trên mặt bàn) is correct', 'Sai'], ['"behind the house" is correct', 'Đúng'], ['"next to me" means "bên cạnh tôi"', 'Đúng']],
            'On a table surface we say "on the table", not "in the table".');
        $this->sortQ('en-prepositions', 'Drag each phrase into INDOORS or OUTDOORS.',
            [['in the kitchen', 'Indoors'], ['in the garden', 'Outdoors'], ['in the bedroom', 'Indoors'], ['on the playground', 'Outdoors']],
            'Kitchen and bedroom are indoors; garden and playground are outdoors.');
        $this->fill('en-prepositions', 'The picture is ___ the wall.', [[0, 'on']],
            'Pictures hang ON the wall (on a surface).');
        $this->fill('en-prepositions', 'She sits ___ me. (bên cạnh tôi)', [[0, 'next to']],
            '"Next to" means "bên cạnh".');
        $this->fill('en-prepositions', 'The dog is ___ the table. (dưới gầm bàn)', [[0, 'under']],
            '"Under" means "phía dưới".');

        // ---- Health vocabulary ----
        $this->quiz('en-health', 'What is "đau đầu" in English?',
            ['headache', 'stomachache', 'toothache', 'backache'], 0,
            '"Headache" means "đau đầu". "Head" = đầu, "ache" = đau.');
        $this->quiz('en-health', 'What is "khoẻ mạnh" in English?',
            ['sick', 'tired', 'healthy', 'weak'], 2,
            '"Healthy" means "khoẻ mạnh". "Sick" means "ốm".');
        $this->quiz('en-health', 'What is "bác sĩ" in English?',
            ['teacher', 'doctor', 'farmer', 'nurse'], 1,
            '"Doctor" means "bác sĩ". "Nurse" means "y tá".');

        $this->matching('en-health', 'Match each English word with its Vietnamese meaning.',
            [['headache', 'đau đầu'], ['fever', 'sốt'], ['cough', 'ho']],
            'headache = đau đầu, fever = sốt, cough = ho.');
        $this->matching('en-health', 'Match each English word with its Vietnamese meaning.',
            [['healthy', 'khoẻ mạnh'], ['sick', 'ốm'], ['tired', 'mệt']],
            'healthy = khoẻ mạnh, sick = ốm, tired = mệt.');
        $this->matching('en-health', 'Match each English word with its Vietnamese meaning.',
            [['doctor', 'bác sĩ'], ['medicine', 'thuốc'], ['hospital', 'bệnh viện']],
            'doctor = bác sĩ, medicine = thuốc, hospital = bệnh viện.');

        // ---- Travel vocabulary ----
        $this->sortQ('en-travel', 'Drag each word into THINGS TO BRING or PLACES.',
            [['suitcase', 'Things to bring'], ['hotel', 'Places'], ['ticket', 'Things to bring'], ['beach', 'Places']],
            'Suitcase and ticket are things we bring; hotel and beach are places we visit.');
        $this->sortQ('en-travel', 'Drag each statement into TRUE or FALSE.',
            [['"passport" means "hộ chiếu"', 'Đúng'], ['"train" means "máy bay"', 'Sai'], ['"map" means "bản đồ"', 'Đúng'], ['"camera" means "máy ảnh"', 'Đúng']],
            '"Train" means "tàu hoả"; "plane" means "máy bay".');
        $this->sortQ('en-travel', 'Drag each "by ..." phrase into the correct vehicle.',
            [['by plane', 'Máy bay'], ['by train', 'Tàu hoả'], ['by bus', 'Xe buýt'], ['by bike', 'Xe đạp']],
            'by plane = bằng máy bay, by train = bằng tàu hoả, by bus = bằng xe buýt, by bike = bằng xe đạp.');
        $this->fill('en-travel', 'We stay at a ___ when we travel. (khách sạn)', [[0, 'hotel']],
            '"Hotel" means "khách sạn".');
        $this->fill('en-travel', 'I need a ___ to get on the plane. (vé)', [[0, 'ticket']],
            '"Ticket" means "vé".');
        $this->fill('en-travel', 'We swim at the ___. (bãi biển)', [[0, 'beach']],
            '"Beach" means "bãi biển".');
    }

    // ================= KHOA HỌC =================

    private function seedKhoaHoc(): void
    {
        // ---- Hệ xương ----
        $this->quiz('kh-he-xuong', 'Xương nào bảo vệ não của chúng ta?',
            ['Xương sọ', 'Xương sườn', 'Xương đùi', 'Xương tay'], 0,
            'Xương sọ tạo thành hộp sọ bao bọc và bảo vệ não.');
        $this->quiz('kh-he-xuong', 'Vai trò chính của hệ xương là gì?',
            ['Nâng đỡ và bảo vệ cơ thể', 'Tiêu hoá thức ăn', 'Bơm máu đi khắp cơ thể', 'Giúp hô hấp'], 0,
            'Hệ xương nâng đỡ cơ thể, tạo khung và bảo vệ các cơ quan bên trong như não, tim, phổi.');
        $this->quiz('kh-he-xuong', 'Xương dài nhất trong cơ thể người là xương nào?',
            ['Xương tay', 'Xương sườn', 'Xương đùi', 'Xương sọ'], 2,
            'Xương đùi là xương dài và chắc nhất trong cơ thể người.');

        $this->matching('kh-he-xuong', 'Nối mỗi xương với vai trò của nó.',
            [['Xương sọ', 'Bảo vệ não'], ['Xương sườn', 'Bảo vệ tim và phổi'], ['Cột sống', 'Nâng đỡ cơ thể']],
            'Xương sọ bảo vệ não, xương sườn bảo vệ tim phổi, cột sống nâng đỡ toàn cơ thể.');
        $this->matching('kh-he-xuong', 'Nối mỗi bộ phận với mô tả đúng.',
            [['Xương đùi', 'Xương dài nhất cơ thể'], ['Xương tay', 'Giúp cử động linh hoạt'], ['Khớp', 'Nối các xương với nhau']],
            'Khớp là chỗ nối giữa hai xương, giúp cơ thể cử động.');
        $this->matching('kh-he-xuong', 'Nối mỗi yếu tố với tác dụng của nó với xương.',
            [['Canxi', 'Giúp xương chắc khoẻ'], ['Sữa', 'Thực phẩm giàu canxi'], ['Tập thể dục', 'Giúp xương phát triển tốt']],
            'Canxi (có nhiều trong sữa) và vận động giúp xương chắc khoẻ.');

        // ---- Hệ tiêu hoá ----
        $this->sortQ('kh-he-tieu-hoa', 'Kéo mỗi cơ quan vào nhóm TRƯỚC DẠ DÀY hoặc SAU DẠ DÀY (theo đường đi của thức ăn).',
            [['Miệng', 'Trước dạ dày'], ['Ruột non', 'Sau dạ dày'], ['Thực quản', 'Trước dạ dày'], ['Ruột già', 'Sau dạ dày']],
            'Đường đi của thức ăn: miệng → thực quản → dạ dày → ruột non → ruột già.', 'trung_binh');
        $this->sortQ('kh-he-tieu-hoa', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['Thức ăn được nghiền nát ở miệng', 'Đúng'], ['Dạ dày nằm sau ruột non', 'Sai'], ['Ruột non hấp thụ chất dinh dưỡng', 'Đúng'], ['Gan tiết dịch giúp tiêu hoá', 'Đúng']],
            'Thứ tự đúng: miệng → thực quản → dạ dày → ruột non → ruột già.', 'trung_binh');
        $this->sortQ('kh-he-tieu-hoa', 'Kéo mỗi cơ quan vào nhóm CƠ QUAN TIÊU HOÁ hoặc KHÔNG PHẢI.',
            [['Dạ dày', 'Cơ quan tiêu hoá'], ['Tim', 'Không phải'], ['Ruột non', 'Cơ quan tiêu hoá'], ['Phổi', 'Không phải']],
            'Tim thuộc hệ tuần hoàn, phổi thuộc hệ hô hấp.', 'trung_binh');
        $this->fill('kh-he-tieu-hoa', 'Thức ăn từ miệng đi xuống ___ rồi mới đến dạ dày.', [[0, 'thực quản']],
            'Thực quản là ống nối miệng với dạ dày.', 'trung_binh');
        $this->fill('kh-he-tieu-hoa', 'Chất dinh dưỡng được hấp thụ chủ yếu ở ___.', [[0, 'ruột non']],
            'Ruột non có nhiều nếp gấp giúp hấp thụ chất dinh dưỡng vào máu.', 'trung_binh');
        $this->fill('kh-he-tieu-hoa', 'Cơ quan nhào trộn thức ăn thành chất lỏng là ___.', [[0, 'dạ dày']],
            'Dạ dày co bóp và tiết dịch vị để tiêu hoá thức ăn.', 'trung_binh');

        // ---- Trạng thái của chất ----
        $this->quiz('kh-trang-thai-chat', 'Nước đá tồn tại ở trạng thái nào?',
            ['Rắn', 'Lỏng', 'Khí', 'Không xác định'], 0,
            'Nước đá có hình dạng cố định nên ở trạng thái rắn.');
        $this->quiz('kh-trang-thai-chat', 'Chất khí có đặc điểm nào?',
            ['Có hình dạng cố định', 'Lan toả, chiếm đầy không gian chứa nó', 'Chảy được như nước', 'Cứng và nặng'], 1,
            'Chất khí không có hình dạng cố định, luôn lan toả chiếm đầy bình chứa.');
        $this->quiz('kh-trang-thai-chat', 'Khi đun sôi, nước chuyển từ thể lỏng sang thể nào?',
            ['Rắn', 'Khí (hơi nước)', 'Không đổi', 'Lỏng đặc'], 1,
            'Nước sôi bốc hơi thành hơi nước ở thể khí.');

        $this->matching('kh-trang-thai-chat', 'Nối mỗi ví dụ với trạng thái của nó.',
            [['Đá', 'Rắn'], ['Nước', 'Lỏng'], ['Hơi nước', 'Khí']],
            'Đá: rắn; nước: lỏng; hơi nước: khí.');
        $this->matching('kh-trang-thai-chat', 'Nối mỗi ví dụ với trạng thái của nó.',
            [['Sắt', 'Rắn'], ['Dầu ăn', 'Lỏng'], ['Không khí', 'Khí']],
            'Sắt: rắn; dầu ăn: lỏng; không khí: khí.');
        $this->matching('kh-trang-thai-chat', 'Nối mỗi quá trình với sự chuyển thể tương ứng.',
            [['Nóng chảy', 'Rắn thành lỏng'], ['Đông đặc', 'Lỏng thành rắn'], ['Bay hơi', 'Lỏng thành khí']],
            'Đá tan: nóng chảy; nước đóng băng: đông đặc; nước bốc hơi: bay hơi.');

        // ---- Nước ----
        $this->sortQ('kh-nuoc', 'Kéo mỗi mô tả vào nhóm TÍNH CHẤT CỦA NƯỚC hoặc KHÔNG PHẢI.',
            [['Không màu', 'Tính chất của nước'], ['Có mùi thơm', 'Không phải'], ['Không mùi', 'Tính chất của nước'], ['Có vị ngọt', 'Không phải']],
            'Nước tinh khiết không màu, không mùi, không vị.');
        $this->sortQ('kh-nuoc', 'Kéo mỗi hành động vào nhóm TIẾT KIỆM NƯỚC hoặc LÃNG PHÍ NƯỚC.',
            [['Khoá vòi khi đánh răng', 'Tiết kiệm nước'], ['Xả nước liên tục khi rửa rau', 'Lãng phí nước'], ['Dùng nước mưa để tưới cây', 'Tiết kiệm nước'], ['Để vòi chảy khi không dùng', 'Lãng phí nước']],
            'Nước sạch có hạn, cần dùng tiết kiệm mỗi ngày.');
        $this->sortQ('kh-nuoc', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['Nước sôi ở 100 độ C (điều kiện thường)', 'Đúng'], ['Nước đá nhẹ hơn nước lỏng nên nổi lên', 'Đúng'], ['Con người có thể sống thiếu nước cả tháng', 'Sai'], ['Nước bao phủ khoảng 70% bề mặt Trái Đất', 'Đúng']],
            'Con người chỉ sống được vài ngày nếu thiếu nước.');
        $this->fill('kh-nuoc', 'Nhiệt độ sôi của nước ở điều kiện thường là ___ độ C.', [[0, '100']],
            'Nước sôi ở 100°C và đóng băng ở 0°C.');
        $this->fill('kh-nuoc', 'Nước tồn tại ở ba trạng thái: rắn, lỏng và ___.', [[0, 'khí']],
            'Ba trạng thái: nước đá (rắn), nước (lỏng), hơi nước (khí).');
        $this->fill('kh-nuoc', 'Để cơ thể khoẻ mạnh, mỗi ngày chúng ta cần uống đủ ___.', [[0, 'nước']],
            'Nước chiếm phần lớn cơ thể người và rất cần cho mọi hoạt động sống.');

        // ---- Nguồn năng lượng ----
        $this->quiz('kh-nguon-nang-luong', 'Nguồn năng lượng nào sau đây là năng lượng tái tạo?',
            ['Gió', 'Than đá', 'Dầu mỏ', 'Khí đốt'], 0,
            'Gió không bao giờ cạn kiệt nên là năng lượng tái tạo. Than đá, dầu mỏ, khí đốt sẽ cạn dần.', 'trung_binh');
        $this->quiz('kh-nguon-nang-luong', 'Tấm pin mặt trời biến đổi năng lượng mặt trời thành dạng năng lượng nào?',
            ['Nhiệt năng', 'Điện năng', 'Hoá năng', 'Cơ năng'], 1,
            'Pin mặt trời biến ánh sáng mặt trời thành điện năng.', 'trung_binh');
        $this->quiz('kh-nguon-nang-luong', 'Nguồn năng lượng nào KHÔNG tái tạo được?',
            ['Mặt trời', 'Gió', 'Than đá', 'Nước chảy'], 2,
            'Than đá hình thành qua hàng triệu năm nên dùng hết là cạn kiệt.', 'trung_binh');

        $this->matching('kh-nguon-nang-luong', 'Nối mỗi nguồn năng lượng với loại của nó.',
            [['Mặt trời', 'Năng lượng tái tạo'], ['Than đá', 'Năng lượng không tái tạo'], ['Gió', 'Năng lượng tái tạo']],
            'Mặt trời, gió, nước là tái tạo; than đá, dầu mỏ là không tái tạo.', 'trung_binh');
        $this->matching('kh-nguon-nang-luong', 'Nối mỗi nguồn với ứng dụng của nó.',
            [['Nước chảy', 'Nhà máy thuỷ điện'], ['Dầu mỏ', 'Nhiên liệu xe cộ'], ['Củi', 'Đun nấu ở nông thôn']],
            'Thuỷ điện dùng sức nước, xe cộ dùng xăng dầu từ dầu mỏ.', 'trung_binh');
        $this->matching('kh-nguon-nang-luong', 'Nối mỗi hành động với ý nghĩa của nó.',
            [['Tắt đèn khi ra khỏi phòng', 'Tiết kiệm điện'], ['Để ti vi mở cả ngày', 'Lãng phí năng lượng'], ['Dùng điện mặt trời', 'Năng lượng sạch']],
            'Tiết kiệm điện và dùng năng lượng sạch giúp bảo vệ môi trường.', 'trung_binh');

        // ---- Điện ----
        $this->sortQ('kh-dien', 'Kéo mỗi vật liệu vào nhóm VẬT DẪN ĐIỆN hoặc VẬT CÁCH ĐIỆN.',
            [['Dây đồng', 'Vật dẫn điện'], ['Nhựa', 'Vật cách điện'], ['Sắt', 'Vật dẫn điện'], ['Gỗ khô', 'Vật cách điện']],
            'Kim loại (đồng, sắt) dẫn điện; nhựa, gỗ khô cách điện.', 'trung_binh');
        $this->sortQ('kh-dien', 'Kéo mỗi bộ phận vào nhóm THUỘC MẠCH ĐIỆN hoặc KHÔNG THUỘC.',
            [['Nguồn điện (pin)', 'Thuộc mạch điện'], ['Bóng đèn', 'Thuộc mạch điện'], ['Cái bàn', 'Không thuộc'], ['Công tắc', 'Thuộc mạch điện']],
            'Mạch điện đơn giản gồm: nguồn điện, dây dẫn, bóng đèn, công tắc.', 'trung_binh');
        $this->sortQ('kh-dien', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['Dòng điện có tác dụng phát sáng', 'Đúng'], ['Nhựa dẫn điện rất tốt', 'Sai'], ['Mạch điện kín thì đèn sáng', 'Đúng'], ['Được phép chạm tay vào ổ điện', 'Sai']],
            'Nhựa cách điện; tuyệt đối không chạm vào ổ điện vì rất nguy hiểm.', 'trung_binh');
        $this->fill('kh-dien', 'Mạch điện đơn giản gồm nguồn điện, dây dẫn, bóng đèn và ___.', [[0, 'công tắc']],
            'Công tắc dùng để đóng, ngắt dòng điện.', 'trung_binh');
        $this->fill('kh-dien', 'Dòng điện chạy qua dây tóc làm bóng đèn phát ___.', [[0, 'sáng']],
            'Dòng điện có tác dụng nhiệt và phát sáng.', 'trung_binh');
        $this->fill('kh-dien', 'Vật liệu cho dòng điện đi qua dễ dàng gọi là vật ___ điện.', [[0, 'dẫn']],
            'Kim loại là vật dẫn điện tốt.', 'trung_binh');
    }

    // ================= LỊCH SỬ =================

    private function seedLichSu(): void
    {
        // ---- Nước Văn Lang ----
        $this->quiz('ls-nuoc-van-lang', 'Nước Văn Lang do ai dựng nên?',
            ['Các vua Hùng', 'An Dương Vương', 'Ngô Quyền', 'Đinh Bộ Lĩnh'], 0,
            'Các vua Hùng là những người đầu tiên dựng nước Văn Lang – nhà nước đầu tiên của người Việt.');
        $this->quiz('ls-nuoc-van-lang', 'Kinh đô của nước Văn Lang đặt ở đâu?',
            ['Phong Châu', 'Hoa Lư', 'Thăng Long', 'Cổ Loa'], 0,
            'Kinh đô Văn Lang đặt ở Phong Châu (thuộc Phú Thọ ngày nay).');
        $this->quiz('ls-nuoc-van-lang', 'Người đứng đầu nước Văn Lang được gọi là gì?',
            ['Vua Hùng', 'Hoàng đế', 'Tù trưởng', 'Quan lang'], 0,
            'Người đứng đầu nước Văn Lang gọi là Vua Hùng (Hùng Vương).');

        $this->matching('ls-nuoc-van-lang', 'Nối mỗi tên gọi với ý nghĩa lịch sử của nó.',
            [['Vua Hùng', 'Người dựng nước Văn Lang'], ['Phong Châu', 'Kinh đô nước Văn Lang'], ['Lạc tướng', 'Quan lại cai quản địa phương']],
            'Vua Hùng đứng đầu, đóng đô ở Phong Châu, Lạc tướng giúp việc cai trị các bộ.');
        $this->matching('ls-nuoc-van-lang', 'Nối mỗi di sản với thời kỳ của nó.',
            [['Trống đồng Đông Sơn', 'Văn hoá thời Văn Lang'], ['Nghề trồng lúa nước', 'Nghề chính của cư dân Văn Lang'], ['Truyện Sơn Tinh – Thuỷ Tinh', 'Truyền thuyết thời Hùng Vương']],
            'Trống đồng Đông Sơn là biểu tượng văn hoá rực rỡ thời Văn Lang.');
        $this->matching('ls-nuoc-van-lang', 'Nối mỗi nhân vật/sự kiện với mô tả đúng.',
            [['An Dương Vương', 'Xây thành Cổ Loa'], ['Nỏ thần', 'Vũ khí lợi hại của An Dương Vương'], ['Nước Âu Lạc', 'Nước kế tiếp sau Văn Lang']],
            'An Dương Vương lập nước Âu Lạc, xây thành Cổ Loa với nỏ thần.');

        // ---- Anh hùng dân tộc ----
        $this->sortQ('ls-anh-hung-dan-toc', 'Kéo mỗi anh hùng vào cuộc khởi nghĩa mà người đó lãnh đạo.',
            [['Hai Bà Trưng', 'Chống quân Hán'], ['Bà Triệu', 'Chống quân Ngô'], ['Lý Bí', 'Chống quân Lương'], ['Ngô Quyền', 'Chống quân Nam Hán']],
            'Hai Bà Trưng (năm 40), Bà Triệu (năm 248), Lý Bí (năm 542), Ngô Quyền (năm 938).');
        $this->sortQ('ls-anh-hung-dan-toc', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['Hai Bà Trưng khởi nghĩa năm 40', 'Đúng'], ['Ngô Quyền thắng trận Bạch Đằng năm 938', 'Đúng'], ['Bà Triệu khởi nghĩa năm 248', 'Đúng'], ['Lý Bí xưng đế năm 679', 'Sai']],
            'Lý Bí xưng đế năm 544, lập nước Vạn Xuân.');
        $this->sortQ('ls-anh-hung-dan-toc', 'Kéo mỗi tên gọi vào nhóm ANH HÙNG hoặc ĐỊA DANH.',
            [['Hai Bà Trưng', 'Anh hùng'], ['Bạch Đằng', 'Địa danh'], ['Ngô Quyền', 'Anh hùng'], ['Hát Môn', 'Địa danh']],
            'Hát Môn là nơi Hai Bà Trưng tuẫn tiết; Bạch Đằng là nơi Ngô Quyền đại phá quân Nam Hán.');
        $this->fill('ls-anh-hung-dan-toc', 'Hai Bà Trưng phất cờ khởi nghĩa năm ___.', [[0, '40']],
            'Cuộc khởi nghĩa Hai Bà Trưng bùng nổ năm 40 sau Công nguyên.');
        $this->fill('ls-anh-hung-dan-toc', 'Ngô Quyền đánh tan quân Nam Hán trên sông ___ năm 938.', [[0, 'Bạch Đằng']],
            'Trận Bạch Đằng năm 938 chấm dứt hơn 1000 năm Bắc thuộc.');
        $this->fill('ls-anh-hung-dan-toc', 'Bà Triệu từng nói: "Tôi muốn cưỡi cơn ___, đạp luồng sóng dữ..."', [[0, 'gió']],
            'Câu nói thể hiện khí phách của người nữ anh hùng dân tộc.');

        // ---- Đinh Bộ Lĩnh ----
        $this->quiz('ls-dinh-bo-linh', 'Đinh Bộ Lĩnh đã dẹp loạn bao nhiêu sứ quân?',
            ['10', '12', '14', '16'], 1,
            'Đinh Bộ Lĩnh dẹp loạn 12 sứ quân, thống nhất đất nước.');
        $this->quiz('ls-dinh-bo-linh', 'Đinh Bộ Lĩnh đặt tên nước ta là gì?',
            ['Đại Cồ Việt', 'Đại Việt', 'Đại Nam', 'Âu Lạc'], 0,
            'Năm 968, Đinh Bộ Lĩnh lên ngôi, đặt tên nước là Đại Cồ Việt.');
        $this->quiz('ls-dinh-bo-linh', 'Kinh đô của nhà Đinh đặt ở đâu?',
            ['Hoa Lư', 'Thăng Long', 'Phong Châu', 'Cổ Loa'], 0,
            'Nhà Đinh đóng đô ở Hoa Lư (Ninh Bình ngày nay).');

        $this->matching('ls-dinh-bo-linh', 'Nối mỗi tên gọi với ý nghĩa lịch sử của nó.',
            [['Đinh Bộ Lĩnh', 'Người dẹp loạn 12 sứ quân'], ['Đại Cồ Việt', 'Tên nước thời nhà Đinh'], ['Hoa Lư', 'Kinh đô nhà Đinh']],
            'Đinh Bộ Lĩnh lập nước Đại Cồ Việt, đóng đô ở Hoa Lư.');
        $this->matching('ls-dinh-bo-linh', 'Nối mỗi sự kiện với năm diễn ra.',
            [['968', 'Đinh Bộ Lĩnh lên ngôi hoàng đế'], ['Đinh Tiên Hoàng', 'Hiệu của Đinh Bộ Lĩnh khi lên ngôi'], ['980', 'Lê Hoàn lên ngôi, mở đầu nhà Tiền Lê']],
            'Năm 968 Đinh Bộ Lĩnh xưng đế hiệu Đinh Tiên Hoàng.');
        $this->matching('ls-dinh-bo-linh', 'Nối mỗi chi tiết với ý nghĩa của nó.',
            [['Cờ lau tập trận', 'Trò chơi tuổi thơ của Đinh Bộ Lĩnh'], ['Hoa Lư', 'Thuộc Ninh Bình ngày nay'], ['Đại Cồ Việt', 'Có nghĩa là nước Việt lớn']],
            'Thuở nhỏ Đinh Bộ Lĩnh thường lấy cờ lau tập trận với bạn bè.');

        // ---- Lê Hoàn ----
        $this->sortQ('ls-le-hoan', 'Kéo mỗi nhân vật vào triều đại đúng.',
            [['Đinh Bộ Lĩnh', 'Nhà Đinh'], ['Lê Hoàn', 'Nhà Tiền Lê'], ['Đinh Tiên Hoàng', 'Nhà Đinh'], ['Lê Đại Hành', 'Nhà Tiền Lê']],
            'Lê Đại Hành chính là hiệu của vua Lê Hoàn nhà Tiền Lê.');
        $this->sortQ('ls-le-hoan', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['Lê Hoàn đánh thắng quân Tống năm 981', 'Đúng'], ['Nhà Tiền Lê đóng đô ở Thăng Long', 'Sai'], ['Lê Hoàn còn được gọi là Lê Đại Hành', 'Đúng'], ['Nhà Đinh tồn tại hơn 100 năm', 'Sai']],
            'Nhà Tiền Lê tiếp tục đóng đô ở Hoa Lư; nhà Đinh chỉ tồn tại 12 năm (968–980).');
        $this->sortQ('ls-le-hoan', 'Kéo mỗi nhân vật vào nhóm VUA hoặc TƯỚNG.',
            [['Lê Hoàn', 'Vua'], ['Phạm Cự Lượng', 'Tướng'], ['Đinh Tiên Hoàng', 'Vua'], ['Đinh Điền', 'Tướng']],
            'Phạm Cự Lượng và Đinh Điền là các tướng tài thời Đinh – Tiền Lê.');
        $this->fill('ls-le-hoan', 'Lê Hoàn đánh tan quân Tống xâm lược năm ___.', [[0, '981']],
            'Chiến thắng năm 981 bảo vệ vững chắc nền độc lập non trẻ.');
        $this->fill('ls-le-hoan', 'Nhà Tiền Lê tiếp tục đóng đô ở ___ như nhà Đinh.', [[0, 'Hoa Lư']],
            'Hoa Lư là kinh đô của cả nhà Đinh và nhà Tiền Lê.');
        $this->fill('ls-le-hoan', 'Lê Hoàn lên ngôi vua, lấy hiệu là ___.', [[0, 'Lê Đại Hành']],
            'Lê Đại Hành nghĩa là vị vua lớn họ Lê.');

        // ---- Ba lần kháng chiến ----
        $this->quiz('ls-dong-bo-dau', 'Quân dân Đại Việt đã đánh bại quân Nguyên – Mông mấy lần?',
            ['1 lần', '2 lần', '3 lần', '4 lần'], 2,
            'Ba lần kháng chiến thắng lợi: 1258, 1285 và 1287–1288.', 'trung_binh');
        $this->quiz('ls-dong-bo-dau', 'Cuộc kháng chiến lần thứ hai chống quân Nguyên – Mông diễn ra năm nào?',
            ['1258', '1285', '1287', '1288'], 1,
            'Lần 1: 1258; lần 2: 1285; lần 3: 1287–1288.', 'trung_binh');
        $this->quiz('ls-dong-bo-dau', 'Ai là tổng chỉ huy cuộc kháng chiến lần 2 và lần 3?',
            ['Trần Hưng Đạo', 'Trần Quang Khải', 'Trần Nhật Duật', 'Phạm Ngũ Lão'], 0,
            'Trần Hưng Đạo (Trần Quốc Tuấn) là tổng chỉ huy kháng chiến.', 'trung_binh');

        $this->matching('ls-dong-bo-dau', 'Nối mỗi năm với cuộc kháng chiến tương ứng.',
            [['1258', 'Kháng chiến lần thứ nhất'], ['1285', 'Kháng chiến lần thứ hai'], ['1287 – 1288', 'Kháng chiến lần thứ ba']],
            'Cả ba lần quân Nguyên – Mông đều bị đánh bại.', 'trung_binh');
        $this->matching('ls-dong-bo-dau', 'Nối mỗi nhân vật với vai trò của người đó.',
            [['Trần Hưng Đạo', 'Tổng chỉ huy kháng chiến'], ['Trần Quang Khải', 'Tướng trận Chương Dương, Tây Kết'], ['Vua Trần Nhân Tông', 'Vua lãnh đạo kháng chiến']],
            'Vua tôi nhà Trần đồng lòng đánh giặc.', 'trung_binh');
        $this->matching('ls-dong-bo-dau', 'Nối mỗi địa danh với sự kiện lịch sử.',
            [['Bạch Đằng 1288', 'Ô Mã Nhi bị bắt sống'], ['Vạn Kiếp', 'Căn cứ của quân dân Đại Việt'], ['Đông Bộ Đầu', 'Trận thắng quân Nguyên lần 2']],
            'Trận Bạch Đằng 1288 tiêu diệt hoàn toàn đạo quân xâm lược.', 'trung_binh');

        // ---- Trần Hưng Đạo ----
        $this->sortQ('ls-tran-hung-dao', 'Kéo mỗi tác phẩm vào nhóm CỦA TRẦN HƯNG ĐẠO hoặc KHÔNG PHẢI.',
            [['Hịch tướng sĩ', 'Của Trần Hưng Đạo'], ['Binh thư yếu lược', 'Của Trần Hưng Đạo'], ['Đại Việt sử ký toàn thư', 'Không phải'], ['Nam quốc sơn hà', 'Không phải']],
            '"Nam quốc sơn hà" gắn với Lý Thường Kiệt; "Đại Việt sử ký toàn thư" do Ngô Sĩ Liên biên soạn.', 'trung_binh');
        $this->sortQ('ls-tran-hung-dao', 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.',
            [['Trần Hưng Đạo tên thật là Trần Quốc Tuấn', 'Đúng'], ['Hịch tướng sĩ kêu gọi tướng sĩ đánh giặc', 'Đúng'], ['Trần Hưng Đạo là vua nhà Trần', 'Sai'], ['Trần Hưng Đạo mất năm 1300', 'Đúng']],
            'Ông là tướng, không phải vua; là bậc anh hùng dân tộc.', 'trung_binh');
        $this->sortQ('ls-tran-hung-dao', 'Kéo mỗi trận đánh vào cuộc kháng chiến đúng.',
            [['Trận Chương Dương', 'Lần 2 (1285)'], ['Trận Bạch Đằng', 'Lần 3 (1288)'], ['Trận Tây Kết', 'Lần 2 (1285)'], ['Trận Vân Đồn', 'Lần 3 (1288)']],
            'Chương Dương, Tây Kết thuộc lần 2; Vân Đồn, Bạch Đằng thuộc lần 3.', 'trung_binh');
        $this->fill('ls-tran-hung-dao', 'Trần Hưng Đạo tên thật là ___.', [[0, 'Trần Quốc Tuấn']],
            'Trần Quốc Tuấn được phong Hưng Đạo Đại Vương.', 'trung_binh');
        $this->fill('ls-tran-hung-dao', 'Tác phẩm kêu gọi tướng sĩ đánh giặc của ông có tên là ___.', [[0, 'Hịch tướng sĩ']],
            'Hịch tướng sĩ là áng văn bất hủ về lòng yêu nước.', 'trung_binh');
        $this->fill('ls-tran-hung-dao', 'Nhân dân ta tôn kính gọi ông là ___.', [[0, 'Đức Thánh Trần']],
            'Đền thờ ông có ở nhiều nơi, tiêu biểu là đền Kiếp Bạc.', 'trung_binh');
    }
}
