<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Bổ sung câu hỏi cho nhóm G2 (Tiếng Việt + Ngữ văn) — 61 bài học hiện có.
 *
 * Mỗi bài được viết 5 câu MỚI cho mỗi kiểu chơi (quiz/matching/sort/fill),
 * nội dung tiếng Việt tự viết, bám đúng topic + khối lớp + độ khó của bài,
 * không trùng prompt với các câu đã có.
 *
 * Idempotent: helper tự bỏ qua khi prompt đã tồn tại hoặc khi mỗi kiểu
 * đã đủ 8 câu/bài; chạy lại không thêm câu mới.
 */
class AdditionalQuestionsGroup2Seeder extends Seeder
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
        $this->seedNvDanhTuDongTu();
        $this->seedNvTinhTu();
        $this->seedNvPhanBietTuLoai();
        $this->seedNvSoSanhNhanHoa();
        $this->seedNvAnDuHoanDu();
        $this->seedNvBoCucMieuTa();
        $this->seedNvDauCau();
        $this->seedNvTuLoaiLop61();
        $this->seedNvTuLoaiLop62();
        $this->seedNvTuLoaiLop71();
        $this->seedNvTuLoaiLop72();
        $this->seedNvBienPhapTuTuLop71();
        $this->seedNvBienPhapTuTuLop72();
        $this->seedNvBienPhapTuTuLop81();
        $this->seedNvBienPhapTuTuLop82();
        $this->seedNvVanMieuTaLop81();
        $this->seedNvVanMieuTaLop82();
        $this->seedNvVanMieuTaLop91();
        $this->seedNvVanMieuTaLop92();
        $this->seedNguVanThpt10Truyen1();
        $this->seedNguVanThpt10Truyen2();
        $this->seedNguVanThpt10Tho1();
        $this->seedNguVanThpt10Tho2();
        $this->seedNguVanThpt11TuTuong1();
        $this->seedNguVanThpt11TuTuong2();
        $this->seedNguVanThpt11HienTuong1();
        $this->seedNguVanThpt11HienTuong2();
        $this->seedNguVanThpt12VanXuoi1();
        $this->seedNguVanThpt12VanXuoi2();
        $this->seedNguVanThpt12Tho1();
        $this->seedNguVanThpt12Tho2();
        $this->seedTvCacTuLoai();
        $this->seedTvCauDon();
        $this->seedTvDauHoiDauNga();
        $this->seedTvAmDau();
        $this->seedTvBaiVanMieuTa();
        $this->seedTvBienPhapTuTu();
        $this->seedTvTuVaCauLop61();
        $this->seedTvTuVaCauLop62();
        $this->seedTvTuVaCauLop71();
        $this->seedTvTuVaCauLop72();
        $this->seedTvChinhTaLop61();
        $this->seedTvChinhTaLop62();
        $this->seedTvChinhTaLop71();
        $this->seedTvChinhTaLop72();
        $this->seedTvVanMieuTaLop71();
        $this->seedTvVanMieuTaLop72();
        $this->seedTvVanMieuTaLop81();
        $this->seedTvVanMieuTaLop82();
        $this->seedTiengVietThpt10VanChuong1();
        $this->seedTiengVietThpt10VanChuong2();
        $this->seedTiengVietThpt10BaoChi1();
        $this->seedTiengVietThpt10BaoChi2();
        $this->seedTiengVietThpt11Cau1();
        $this->seedTiengVietThpt11Cau2();
        $this->seedTiengVietThpt11BienPhap1();
        $this->seedTiengVietThpt11BienPhap2();
        $this->seedTiengVietThpt12ChuaLoi1();
        $this->seedTiengVietThpt12ChuaLoi2();
        $this->seedTiengVietThpt12LienKet1();
        $this->seedTiengVietThpt12LienKet2();
    }

    /* ============ nv-danh-tu-dong-tu (Ngữ văn 6, de) ============ */
    private function seedNvDanhTuDongTu(): void
    {
        $s = 'nv-danh-tu-dong-tu'; $d = 'de';
        $this->quiz($s, 'Trong câu "Chim bay về tổ.", từ nào là động từ?', ['chim', 'bay', 'về', 'tổ'], 1, '"Bay" chỉ hành động của con chim nên là động từ.', $d);
        $this->quiz($s, 'Từ nào sau đây là danh từ chỉ vật?', ['cái bàn', 'chạy', 'vui vẻ', 'thầy giáo'], 0, '"Cái bàn" chỉ một đồ vật cụ thể nên là danh từ chỉ vật.', $d);
        $this->quiz($s, 'Từ "hạnh phúc" thuộc từ loại nào?', ['Danh từ', 'Động từ', 'Tính từ', 'Đại từ'], 0, '"Hạnh phúc" chỉ một trạng thái, khái niệm trừu tượng nên thuộc danh từ.', $d);
        $this->quiz($s, 'Trong câu "Em bé đang ngủ ngon.", cụm vị ngữ là gì?', ['Em bé', 'đang ngủ ngon', 'Em', 'ngủ ngon'], 1, 'Vị ngữ "đang ngủ ngon" cho biết em bé thế nào, với từ trung tâm "ngủ" là động từ.', $d);
        $this->quiz($s, 'Cặp từ nào gồm một danh từ và một động từ?', ['sách – đọc', 'đỏ – xanh', 'chạy – nhảy', 'niềm vui – nỗi buồn'], 0, '"Sách" là danh từ chỉ vật, "đọc" là động từ chỉ hành động.', $d);

        $this->matching($s, 'Nối mỗi từ loại với định nghĩa đúng của nó.', [['Danh từ', 'Từ chỉ người, vật, hiện tượng, khái niệm'], ['Động từ', 'Từ chỉ hành động, trạng thái của sự vật'], ['Tính từ', 'Từ chỉ đặc điểm, tính chất của sự vật'], ['Đại từ', 'Từ dùng để xưng hô hoặc thay thế danh từ']], 'Nhớ đúng định nghĩa sẽ giúp nhận diện từ loại nhanh và chính xác.', $d);
        $this->matching($s, 'Nối mỗi từ với nhóm danh từ đúng của nó.', [['bác sĩ', 'Danh từ chỉ người'], ['quyển sách', 'Danh từ chỉ vật'], ['cơn mưa', 'Danh từ chỉ hiện tượng'], ['tình bạn', 'Danh từ chỉ khái niệm']], 'Danh từ được chia nhỏ theo đối tượng mà nó gọi tên: người, vật, hiện tượng hay khái niệm.', $d);
        $this->matching($s, 'Nối mỗi từ với ví dụ minh họa đúng.', [['Danh từ chỉ người', 'cô giáo'], ['Danh từ chỉ vật', 'cái cặp'], ['Động từ chỉ hành động', 'đá bóng'], ['Động từ chỉ trạng thái', 'ngủ say']], 'Động từ chia thành chỉ hành động (làm gì) và chỉ trạng thái (tồn tại, cảm xúc).', $d);
        $this->matching($s, 'Nối mỗi từ trong câu "Bầy ong chăm chỉ hút mật." với từ loại của nó.', [['bầy ong', 'Danh từ'], ['chăm chỉ', 'Tính từ'], ['hút', 'Động từ'], ['mật', 'Danh từ']], 'Xác định từ loại phải đặt từ vào trong câu cụ thể, không đoán theo cảm tính.', $d);
        $this->matching($s, 'Nối mỗi câu hỏi nhận diện với từ loại cần tìm.', [['Từ nào chỉ người, vật?', 'Danh từ'], ['Từ nào chỉ hành động?', 'Động từ'], ['Từ nào chỉ đặc điểm?', 'Tính từ'], ['Từ nào thay thế tên gọi?', 'Đại từ']], 'Mỗi từ loại trả lời một câu hỏi nhận diện riêng, đó là mẹo làm bài nhanh nhất.', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc TÍNH TỪ.', [['cô giáo', 'DANH TỪ'], ['xanh biếc', 'TÍNH TỪ'], ['quyển vở', 'DANH TỪ'], ['vui vẻ', 'TÍNH TỪ'], ['bông hoa', 'DANH TỪ'], ['tròn xoe', 'TÍNH TỪ']], 'Danh từ gọi tên sự vật, tính từ miêu tả đặc điểm của sự vật đó.', $d);
        $this->sortQ($s, 'Kéo mỗi danh từ vào nhóm CHỈ HIỆN TƯỢNG hoặc CHỈ KHÁI NIỆM.', [['cơn bão', 'CHỈ HIỆN TƯỢNG'], ['tình yêu', 'CHỈ KHÁI NIỆM'], ['trận mưa', 'CHỈ HIỆN TƯỢNG'], ['niềm tin', 'CHỈ KHÁI NIỆM']], 'Hiện tượng là cái xảy ra trong tự nhiên, khái niệm là cái trừu tượng do con người nhận thức.', $d);
        $this->sortQ($s, 'Kéo mỗi động từ vào nhóm CHỈ HÀNH ĐỘNG hoặc CHỈ TRẠNG THÁI.', [['chạy', 'CHỈ HÀNH ĐỘNG'], ['ngủ', 'CHỈ TRẠNG THÁI'], ['nhảy', 'CHỈ HÀNH ĐỘNG'], ['buồn', 'CHỈ TRẠNG THÁI'], ['bơi', 'CHỈ HÀNH ĐỘNG'], ['tồn tại', 'CHỈ TRẠNG THÁI']], 'Động từ hành động trả lời "làm gì?", động từ trạng thái diễn tả sự tồn tại hay cảm xúc.', $d);
        $this->sortQ($s, 'Kéo mỗi danh từ vào nhóm ĐẾM ĐƯỢC hoặc KHÔNG ĐẾM ĐƯỢC.', [['quả táo', 'ĐẾM ĐƯỢC'], ['nước', 'KHÔNG ĐẾM ĐƯỢC'], ['quyển sách', 'ĐẾM ĐƯỢC'], ['không khí', 'KHÔNG ĐẾM ĐƯỢC']], 'Danh từ đếm được đi với số từ (một, hai...), danh từ không đếm được chỉ chất liệu, khối lượng chung chung.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ DANH TỪ CHỈ NGƯỜI hoặc KHÔNG CÓ.', [['Thầy giáo đang giảng bài.', 'CÓ DANH TỪ CHỈ NGƯỜI'], ['Trời mưa to quá.', 'KHÔNG CÓ'], ['Bác nông dân cày ruộng.', 'CÓ DANH TỪ CHỈ NGƯỜI'], ['Gió thổi vi vu.', 'KHÔNG CÓ']], 'Danh từ chỉ người gọi tên con người: thầy giáo, bác nông dân, em bé...', $d);

        $this->fill($s, 'Từ chỉ hành động, trạng thái của sự vật gọi là động ___.', [[0, 'từ']], 'Động từ là từ loại chỉ hành động (chạy, nhảy) hoặc trạng thái (ngủ, buồn) của sự vật.', $d);
        $this->fill($s, 'Trong câu "Gà gáy vang cả xóm.", từ "gáy" thuộc từ loại ___.', [[0, 'động từ']], '"Gáy" chỉ hành động kêu của con gà nên là động từ.', $d);
        $this->fill($s, 'Danh từ chỉ tên riêng của người, địa danh gọi là danh từ ___.', [[0, 'riêng']], 'Danh từ riêng (Hà Nội, Lan) viết hoa chữ cái đầu, phân biệt với danh từ chung.', $d);
        $this->fill($s, '"Bầy chim ___ líu lo trên cành." Điền động từ còn thiếu chỉ tiếng kêu của chim.', [[0, 'hót']], 'Chim kêu là "hót"; động từ "hót" chỉ hành động phát ra tiếng của chim.', $d);
        $this->fill($s, 'Trong cụm "màu xanh của lá", từ trung tâm "màu" là danh từ nên cả cụm là cụm ___ từ.', [[0, 'danh']], 'Cụm từ được gọi tên theo từ loại của từ trung tâm: trung tâm là danh từ thì là cụm danh từ.', $d);
    }

    /* ============ nv-tinh-tu (Ngữ văn 6, de) ============ */
    private function seedNvTinhTu(): void
    {
        $s = 'nv-tinh-tu'; $d = 'de';
        $this->quiz($s, 'Từ nào sau đây là tính từ chỉ màu sắc?', ['đỏ rực', 'chạy nhanh', 'quyển sách', 'cô giáo'], 0, '"Đỏ rực" miêu tả màu sắc của sự vật nên là tính từ.', $d);
        $this->quiz($s, 'Trong câu "Dòng sông quê em rất hiền hòa.", tính từ là từ nào?', ['dòng sông', 'quê em', 'rất', 'hiền hòa'], 3, '"Hiền hòa" chỉ tính chất của dòng sông nên là tính từ trong câu.', $d);
        $this->quiz($s, 'Câu nào có tính từ chỉ kích thước?', ['Con voi to lớn.', 'Chim hót hay.', 'Bé chạy nhanh.', 'Mẹ nấu cơm.'], 0, '"To lớn" chỉ kích thước của con voi, các câu còn lại không có tính từ chỉ kích thước.', $d);
        $this->quiz($s, 'Trong câu "Cậu bé thông minh giải bài rất nhanh.", có mấy tính từ?', ['1', '2', '3', '0'], 1, 'Có 2 tính từ: "thông minh" (chỉ tính cách) và "nhanh" (chỉ mức độ).', $d);
        $this->quiz($s, 'Từ nào sau đây KHÔNG phải tính từ?', ['cao', 'thấp', 'bàn', 'đẹp'], 2, '"Bàn" là danh từ chỉ đồ vật, còn lại đều là tính từ chỉ đặc điểm.', $d);

        $this->matching($s, 'Nối mỗi tính từ với nhóm nghĩa của nó.', [['xanh ngắt', 'Chỉ màu sắc'], ['hiền lành', 'Chỉ tính cách'], ['to lớn', 'Chỉ kích thước'], ['ngọt ngào', 'Chỉ mùi vị, cảm giác']], 'Tính từ được chia nhóm theo đặc điểm mà nó miêu tả: màu sắc, hình dáng, tính cách...', $d);
        $this->matching($s, 'Nối mỗi câu với tính từ xuất hiện trong câu.', [['"Bầu trời trong xanh."', 'trong xanh'], ['"Cô bé rất ngoan."', 'ngoan'], ['"Quả dưa hấu ngọt lịm."', 'ngọt lịm'], ['"Ngọn núi cao chót vót."', 'cao chót vót']], 'Đọc kĩ câu văn, tìm từ chỉ đặc điểm, tính chất của sự vật được nói tới.', $d);
        $this->matching($s, 'Nối mỗi cặp từ trái nghĩa với nhau.', [['cao', 'thấp'], ['vui', 'buồn'], ['nhanh', 'chậm'], ['sáng', 'tối']], 'Tính từ thường đi thành cặp trái nghĩa, học theo cặp sẽ nhớ lâu hơn.', $d);
        $this->matching($s, 'Nối mỗi từ láy với tác dụng gợi tả của nó.', [['long lanh', 'Gợi tả ánh sáng đẹp'], ['rì rào', 'Gợi tả âm thanh'], ['mênh mông', 'Gợi tả không gian rộng'], ['thơm phức', 'Gợi tả mùi hương']], 'Từ láy là "vũ khí" của văn miêu tả: gợi hình, gợi thanh, gợi cảm xúc rất tốt.', $d);
        $this->matching($s, 'Nối mỗi thành phần câu với ví dụ đúng.', [['Chủ ngữ', '"Đàn chim" trong "Đàn chim bay về."'], ['Vị ngữ', '"bay về" trong "Đàn chim bay về."'], ['Tính từ làm vị ngữ', '"đẹp" trong "Hoa rất đẹp."'], ['Cụm tính từ', '"rất chăm chỉ"']], 'Tính từ có thể đứng một mình hoặc cùng phụ từ tạo thành cụm tính từ làm vị ngữ.', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TÍNH TỪ CHỈ MÀU SẮC hoặc TÍNH TỪ CHỈ HÌNH DÁNG.', [['đỏ au', 'TÍNH TỪ CHỈ MÀU SẮC'], ['tròn xoe', 'TÍNH TỪ CHỈ HÌNH DÁNG'], ['vàng óng', 'TÍNH TỪ CHỈ MÀU SẮC'], ['dài ngoằng', 'TÍNH TỪ CHỈ HÌNH DÁNG'], ['xanh mướt', 'TÍNH TỪ CHỈ MÀU SẮC'], ['méo mó', 'TÍNH TỪ CHỈ HÌNH DÁNG']], 'Màu sắc là cái nhìn thấy (đỏ, xanh), hình dáng là đường nét bên ngoài (tròn, dài).', $d);
        $this->sortQ($s, 'Kéo mỗi tính từ vào nhóm CHỈ TÍNH CÁCH TỐT hoặc CHỈ TÍNH CÁCH CHƯA TỐT.', [['hiền lành', 'CHỈ TÍNH CÁCH TỐT'], ['lười biếng', 'CHỈ TÍNH CÁCH CHƯA TỐT'], ['chăm chỉ', 'CHỈ TÍNH CÁCH TỐT'], ['ích kỉ', 'CHỈ TÍNH CÁCH CHƯA TỐT']], 'Tính từ chỉ tính cách con người có thể mang nghĩa khen hoặc chê.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ LÁY hoặc TỪ GHÉP.', [['lung linh', 'TỪ LÁY'], ['bàn ghế', 'TỪ GHÉP'], ['róc rách', 'TỪ LÁY'], ['sách vở', 'TỪ GHÉP'], ['lấp lánh', 'TỪ LÁY'], ['quần áo', 'TỪ GHÉP']], 'Từ láy lặp lại âm hoặc vần, từ ghép kết hợp hai tiếng đều có nghĩa.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ TÍNH TỪ LÀM VỊ NGỮ hoặc KHÔNG.', [['Trời hôm nay đẹp quá!', 'CÓ TÍNH TỪ LÀM VỊ NGỮ'], ['Mẹ đi chợ sớm.', 'KHÔNG'], ['Bé rất ngoan.', 'CÓ TÍNH TỪ LÀM VỊ NGỮ'], ['Chim bay về tổ.', 'KHÔNG']], 'Khi tính từ đứng sau chủ ngữ để nhận xét, nó đang làm vị ngữ của câu.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TÍNH TỪ hoặc ĐỘNG TỪ.', [['tươi tắn', 'TÍNH TỪ'], ['nhảy múa', 'ĐỘNG TỪ'], ['mát rượi', 'TÍNH TỪ'], ['reo hò', 'ĐỘNG TỪ']], 'Tính từ trả lời "thế nào?", động từ trả lời "làm gì?".', $d);

        $this->fill($s, 'Trong câu "Mặt hồ phẳng ___.", điền tính từ còn thiếu chỉ mặt hồ yên tĩnh.', [[0, 'lặng']], '"Phẳng lặng" là tính từ láy gợi tả mặt hồ yên tĩnh, không gợn sóng.', $d);
        $this->fill($s, 'Từ chỉ tính cách tốt của con người như "chăm chỉ" thuộc từ loại ___ từ.', [[0, 'tính']], 'Mọi từ chỉ đặc điểm, tính chất, tính cách đều thuộc từ loại tính từ.', $d);
        $this->fill($s, 'Trong cụm "rất xinh đẹp", từ chỉ mức độ "rất" là phụ từ đứng ___ từ trung tâm.', [[0, 'trước']], 'Cấu tạo cụm tính từ: phụ từ chỉ mức độ (rất, hơi, quá) đứng trước từ trung tâm.', $d);
        $this->fill($s, '"Nụ cười ___ hiền." Điền tính từ còn thiếu chỉ nụ cười đẹp, dễ mến.', [[0, 'tươi']], '"Tươi" là tính từ chỉ vẻ rạng rỡ; "nụ cười tươi" gợi cảm giác thân thiện.', $d);
        $this->fill($s, 'Câu "Hoa nở ___ rỡ." cần điền tính từ láy chỉ hoa nở đẹp: "rực ___".', [[0, 'rỡ']], '"Rực rỡ" là tính từ láy quen thuộc để tả hoa nở đẹp, màu sắc tươi sáng.', $d);
    }

    /* ============ nv-phan-biet-tu-loai (Ngữ văn 6, trung_binh) ============ */
    private function seedNvPhanBietTuLoai(): void
    {
        $s = 'nv-phan-biet-tu-loai'; $d = 'trung_binh';
        $this->quiz($s, 'Trong câu "Đàn cò trắng bay lượn trên cánh đồng.", từ "trắng" thuộc từ loại nào?', ['Danh từ', 'Động từ', 'Tính từ', 'Quan hệ từ'], 2, '"Trắng" chỉ màu sắc của đàn cò nên là tính từ, dù đứng sau danh từ.', $d);
        $this->quiz($s, 'Từ "sáng" trong câu nào là danh từ?', ['Buổi sáng đẹp trời.', 'Đèn sáng trưng.', 'Em học bài sáng nay.', 'Trời sáng dần.'], 0, 'Trong "buổi sáng", "sáng" kết hợp với "buổi" chỉ thời gian nên là danh từ.', $d);
        $this->quiz($s, 'Muốn xác định đúng từ loại của một từ đa nghĩa, cách làm đúng là gì?', ['Đoán theo nghĩa quen thuộc nhất', 'Đặt từ vào ngữ cảnh cụ thể của câu', 'Tra từ điển rồi áp dụng máy móc', 'Xem từ đứng ở đầu hay cuối câu'], 1, 'Cùng một từ có thể thuộc từ loại khác nhau tùy ngữ cảnh, nên phải xét trong câu cụ thể.', $d);
        $this->quiz($s, 'Trong câu "Bạn ấy hát rất hay.", từ "hay" là gì?', ['Danh từ', 'Động từ', 'Tính từ', 'Phụ từ'], 2, '"Hay" ở đây chỉ mức độ hay của tiếng hát, bổ nghĩa cho động từ "hát" nên là tính từ.', $d);
        $this->quiz($s, 'Cặp từ nào sau đây đều là động từ?', ['ăn – uống', 'đẹp – xinh', 'bàn – ghế', 'vui – buồn'], 0, '"Ăn" và "uống" đều chỉ hành động nên đều là động từ.', $d);

        $this->matching($s, 'Nối mỗi câu với từ loại của từ "chạy" trong câu đó.', [['"Ngựa chạy nhanh."', 'Động từ'], ['"Cuộc chạy bắt đầu."', 'Danh từ'], ['"Anh ấy chạy bộ mỗi sáng."', 'Động từ'], ['"Đường chạy rất dài."', 'Danh từ']], 'Từ "chạy" khi chỉ hành động là động từ, khi chỉ sự việc (cuộc chạy, đường chạy) là danh từ.', $d);
        $this->matching($s, 'Nối mỗi từ trong câu "Cô giáo hiền giảng bài hay." với từ loại của nó.', [['cô giáo', 'Danh từ'], ['hiền', 'Tính từ'], ['giảng', 'Động từ'], ['hay', 'Tính từ']], 'Phân tích từng từ trong ngữ cảnh câu: "hiền" tả cô giáo, "giảng" là hành động, "hay" tả cách giảng.', $d);
        $this->matching($s, 'Nối mỗi lỗi thường gặp với cách khắc phục đúng.', [['Đoán từ loại theo nghĩa quen thuộc', 'Đặt từ vào câu cụ thể rồi xét'], ['Nhầm tính từ với động từ', 'Hỏi "làm gì?" hay "thế nào?"'], ['Bỏ qua phụ từ đi kèm', 'Xét cả cụm từ, không xét từ đơn lẻ'], ['Nhầm danh từ với tính từ', 'Hỏi "cái gì?" hay "thế nào?"']], 'Mẹo vàng: đặt câu hỏi "là gì/làm gì/thế nào?" cho từ cần xác định.', $d);
        $this->matching($s, 'Nối mỗi từ "đẹp" trong các câu với từ loại của nó.', [['"Cảnh đẹp quá!"', 'Tính từ'], ['"Vẻ đẹp của quê hương."', 'Danh từ'], ['"Em bé đẹp như tranh."', 'Tính từ'], ['"Cái đẹp cần được giữ gìn."', 'Danh từ']], '"Đẹp" là tính từ, nhưng "vẻ đẹp", "cái đẹp" chỉ khái niệm nên là danh từ.', $d);
        $this->matching($s, 'Nối mỗi nhóm từ với đặc điểm nhận diện.', [['Danh từ', 'Đi được với "những, các, một"'], ['Động từ', 'Đi được với "đang, sẽ, đã"'], ['Tính từ', 'Đi được với "rất, hơi, quá"'], ['Đại từ', 'Thay thế được cho danh từ']], 'Phụ từ đi kèm là "chìa khóa": đang/sẽ/đã đi với động từ, rất/hơi/quá đi với tính từ.', $d);

        $this->sortQ($s, 'Xét từ "mưa" trong mỗi câu, kéo vào nhóm "MƯA" LÀ DANH TỪ hoặc LÀ ĐỘNG TỪ.', [['Cơn mưa rào ập tới.', 'LÀ DANH TỪ'], ['Trời đang mưa to.', 'LÀ ĐỘNG TỪ'], ['Mưa mùa hạ mát rượi.', 'LÀ DANH TỪ'], ['Ngoài sân mưa lất phất.', 'LÀ ĐỘNG TỪ']], '"Cơn mưa" chỉ hiện tượng (danh từ), "trời mưa" chỉ hành động của trời (động từ).', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc TÍNH TỪ trong câu "Vườn hoa thơm ngát."', [['vườn', 'DANH TỪ'], ['hoa', 'DANH TỪ'], ['thơm', 'TÍNH TỪ'], ['ngát', 'TÍNH TỪ']], '"Vườn, hoa" gọi tên sự vật; "thơm ngát" miêu tả mùi hương của hoa.', $d);
        $this->sortQ($s, 'Kéo mỗi cách xác định từ loại vào nhóm ĐÚNG hoặc SAI.', [['Đặt từ vào câu cụ thể', 'ĐÚNG'], ['Đoán theo nghĩa quen thuộc', 'SAI'], ['Xét chức năng ngữ pháp trong câu', 'ĐÚNG'], ['Chỉ nhìn hình thức của từ', 'SAI']], 'Từ loại là phạm trù ngữ pháp, phải xét vai trò của từ trong câu cụ thể.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm CÓ THỂ THUỘC NHIỀU TỪ LOẠI hoặc CHỈ MỘT TỪ LOẠI.', [['đá (quả bóng đá / đá bóng)', 'NHIỀU TỪ LOẠI'], ['và', 'CHỈ MỘT TỪ LOẠI'], ['hay (hát hay / cái hay)', 'NHIỀU TỪ LOẠI'], ['những', 'CHỈ MỘT TỪ LOẠI']], 'Từ thực từ (danh, động, tính) dễ chuyển loại theo ngữ cảnh; hư từ (và, những) thì ổn định.', $d);
        $this->sortQ($s, 'Kéo mỗi từ trong câu "Mẹ hiền nấu cơm ngon." vào nhóm đúng.', [['Mẹ', 'DANH TỪ'], ['hiền', 'TÍNH TỪ'], ['nấu', 'ĐỘNG TỪ'], ['ngon', 'TÍNH TỪ'], ['cơm', 'DANH TỪ']], '"Hiền" tả mẹ, "nấu" là hành động, "ngon" tả cơm — mỗi từ một vai trò rõ ràng.', $d);

        $this->fill($s, 'Muốn xác định đúng từ loại, phải xét từ trong ___ cảnh cụ thể của câu.', [[0, 'ngữ']], 'Ngữ cảnh quyết định từ loại: cùng một hình thức từ có thể thuộc loại khác nhau.', $d);
        $this->fill($s, 'Trong câu "Tiếng hát hay quá.", từ "hát" là ___ từ vì chỉ hành động phát ra tiếng.', [[0, 'động']], '"Hát" chỉ hành động của con người nên là động từ, dù đứng sau danh từ "tiếng".', $d);
        $this->fill($s, 'Phụ từ "đang, sẽ, đã" thường đi kèm với động ___.', [[0, 'từ']], '"Đang chạy, sẽ đi, đã làm" — nhóm phụ từ chỉ thời gian là dấu hiệu của động từ.', $d);
        $this->fill($s, 'Trong "niềm vui của em", từ "vui" kết hợp với "niềm" tạo thành danh ___.', [[0, 'từ']], '"Niềm, nỗi, sự, cái" biến tính từ thành danh từ chỉ khái niệm: niềm vui, nỗi buồn.', $d);
        $this->fill($s, 'Từ "xanh" trong "lá xanh" là tính từ, nhưng trong "màu xanh" thì "xanh" vẫn là tính từ bổ nghĩa cho danh từ "___".', [[0, 'màu']], '"Màu xanh": "màu" là danh từ trung tâm, "xanh" là tính từ bổ nghĩa cho nó.', $d);
    }

    /* ============ nv-so-sanh-nhan-hoa (Ngữ văn 7, trung_binh) ============ */
    private function seedNvSoSanhNhanHoa(): void
    {
        $s = 'nv-so-sanh-nhan-hoa'; $d = 'trung_binh';
        $this->quiz($s, 'Câu "Dòng sông như dải lụa mềm." sử dụng biện pháp tu từ nào?', ['Nhân hoá', 'So sánh', 'Ẩn dụ', 'Hoán dụ'], 1, 'Có từ so sánh "như" đối chiếu "dòng sông" với "dải lụa" nên là phép so sánh.', $d);
        $this->quiz($s, 'Câu "Ông mặt trời nhô lên khỏi rặng tre." sử dụng biện pháp tu từ nào?', ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Điệp ngữ'], 1, 'Gọi mặt trời là "ông" (xưng hô như con người) nên là phép nhân hoá.', $d);
        $this->quiz($s, 'Trong phép so sánh, hai sự vật được đối chiếu phải có đặc điểm gì?', ['Hoàn toàn giống nhau', 'Có nét tương đồng', 'Trái ngược nhau', 'Không liên quan'], 1, 'So sánh dựa trên nét tương đồng giữa hai sự vật khác loại, không cần giống hoàn toàn.', $d);
        $this->quiz($s, 'Câu nào sau đây sử dụng phép nhân hoá?', ['Trăng tròn như cái đĩa.', 'Chị gió đùa nghịch với tóc em.', 'Mắt em sáng như sao.', 'Núi cao như chạm trời.'], 1, '"Chị gió đùa nghịch" gán hành động, xưng hô của con người cho gió nên là nhân hoá.', $d);
        $this->quiz($s, 'Tác dụng chung của so sánh và nhân hoá trong văn miêu tả là gì?', ['Làm câu văn dài hơn', 'Làm hình ảnh sinh động, gợi cảm', 'Làm câu văn khó hiểu', 'Thay thế từ ngữ đơn giản'], 1, 'Cả hai đều làm cho sự vật hiện lên cụ thể, sinh động và gợi cảm xúc.', $d);

        $this->matching($s, 'Nối mỗi câu với biện pháp tu từ được sử dụng.', [['"Mây trắng như bông."', 'So sánh'], ['"Bác chuồn chuồn đậu trên lá."', 'Nhân hoá'], ['"Tóc bà trắng như cước."', 'So sánh'], ['"Anh gà trống gáy vang."', 'Nhân hoá']], 'Có từ "như" là so sánh; xưng "bác", "anh" với vật là nhân hoá.', $d);
        $this->matching($s, 'Nối mỗi bộ phận với vai trò trong phép so sánh.', [['Sự vật được so sánh', 'Vế A'], ['Từ so sánh', '"như, tựa, là..."'], ['Sự vật dùng để so sánh', 'Vế B'], ['Nét tương đồng', 'Điểm chung của A và B']], 'Mô hình so sánh đầy đủ: A (vế được so sánh) + từ so sánh + B (vế so sánh).', $d);
        $this->matching($s, 'Nối mỗi cách nhân hoá với ví dụ đúng.', [['Xưng hô như con người', '"chị gió, ông mặt trời"'], ['Gán hành động của người', '"hoa cười, lá hát"'], ['Gán suy nghĩ, tình cảm', '"trăng buồn, sao nhớ"'], ['Trò chuyện với sự vật', '"Hỡi con sông quê hương!"']], 'Nhân hoá có nhiều cách: xưng hô, gán hành động, gán tình cảm hoặc đối thoại.', $d);
        $this->matching($s, 'Nối mỗi hình ảnh so sánh với ý nghĩa gợi ra.', [['"Trẻ em như búp trên cành"', 'Ngợi ca vẻ đẹp, sức sống trẻ thơ'], ['"Công cha như núi Thái Sơn"', 'Ca ngợi công ơn to lớn của cha'], ['"Mặt trời như quả cầu lửa"', 'Gợi tả sự rực rỡ, nóng bỏng'], ['"Lòng mẹ như biển Thái Bình"', 'Diễn tả tình mẹ bao la']], 'So sánh trong ca dao, tục ngữ thường mang ý nghĩa ngợi ca sâu sắc.', $d);
        $this->matching($s, 'Nối mỗi từ với vai trò của nó trong câu so sánh.', [['"tựa"', 'Từ so sánh'], ['"là"', 'Từ so sánh (ngang bằng)'], ['"bao nhiêu"', 'Từ so sánh (bấy nhiêu)'], ['"khác"', 'Không phải từ so sánh']], 'Ngoài "như", các từ "tựa, là, giống, bao nhiêu... bấy nhiêu" cũng là từ so sánh.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm SO SÁNH NGANG BẰNG hoặc SO SÁNH HƠN KÉM.', [['"Trăng tròn như cái đĩa."', 'SO SÁNH NGANG BẰNG'], ['"Núi cao hơn nhà."', 'SO SÁNH HƠN KÉM'], ['"Mẹ là ngọn đèn soi sáng."', 'SO SÁNH NGANG BẰNG'], ['"Sông sâu hơn giếng."', 'SO SÁNH HƠN KÉM']], 'So sánh ngang bằng dùng "như, là, tựa"; so sánh hơn kém dùng "hơn, kém, nhất".', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm NHÂN HOÁ BẰNG XƯNG HÔ hoặc NHÂN HOÁ BẰNG HÀNH ĐỘNG.', [['"cô mây"', 'NHÂN HOÁ BẰNG XƯNG HÔ'], ['"lá reo vui"', 'NHÂN HOÁ BẰNG HÀNH ĐỘNG'], ['"bác gấu"', 'NHÂN HOÁ BẰNG XƯNG HÔ'], ['"sóng vỗ về"', 'NHÂN HOÁ BẰNG HÀNH ĐỘNG'], ['"chị ong"', 'NHÂN HOÁ BẰNG XƯNG HÔ'], ['"trăng mỉm cười"', 'NHÂN HOÁ BẰNG HÀNH ĐỘNG']], 'Xưng hô (cô, bác, anh) hoặc gán hành động người (reo, cười, vỗ về) đều là nhân hoá.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ BIỆN PHÁP TU TỪ hoặc KHÔNG CÓ.', [['"Ve kêu râm ran."', 'KHÔNG CÓ'], ['"Ve như dàn đồng ca mùa hạ."', 'CÓ BIỆN PHÁP TU TỪ'], ['"Mưa rơi lộp độp."', 'KHÔNG CÓ'], ['"Mưa nhảy múa trên mái nhà."', 'CÓ BIỆN PHÁP TU TỪ']], 'Câu kể đơn thuần không có tu từ; câu có "như" hoặc gán hành động người mới có tu từ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm TỪ SO SÁNH hoặc KHÔNG PHẢI TỪ SO SÁNH.', [['như', 'TỪ SO SÁNH'], ['tựa', 'TỪ SO SÁNH'], ['rất', 'KHÔNG PHẢI TỪ SO SÁNH'], ['giống', 'TỪ SO SÁNH'], ['là', 'TỪ SO SÁNH'], ['và', 'KHÔNG PHẢI TỪ SO SÁNH']], '"Rất" là phụ từ chỉ mức độ, "và" là quan hệ từ — không phải từ so sánh.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm NHÂN HOÁ hoặc ẨN DỤ.', [['"Ông trăng tròn vành vạnh."', 'NHÂN HOÁ'], ['"Thuyền về có nhớ bến chăng?"', 'ẨN DỤ'], ['"Bé mèo con meo meo đòi ăn."', 'NHÂN HOÁ'], ['"Người cha mái tóc bạc."', 'ẨN DỤ']], 'Nhân hoá gán đặc điểm người cho vật; ẩn dụ gọi vật này bằng tên vật khác tương đồng.', $d);

        $this->fill($s, 'Trong câu "Lúa chín vàng như ___ mật.", điền sự vật so sánh quen thuộc chỉ màu vàng óng.', [[0, 'giọt']], '"Vàng như giọt mật" là hình ảnh so sánh quen thuộc gợi màu lúa chín óng ả.', $d);
        $this->fill($s, 'Phép nhân hoá biến sự vật vô tri thành có hồn như con ___.', [[0, 'người']], 'Nhân hoá là gán cho sự vật đặc điểm, hành động, tình cảm của con người.', $d);
        $this->fill($s, 'Trong "Trẻ em như búp trên cành", vế A là "trẻ em", vế B là "búp trên ___".', [[0, 'cành']], 'Mô hình so sánh: A (trẻ em) + "như" + B (búp trên cành), nét tương đồng là non nớt, đầy sức sống.', $d);
        $this->fill($s, 'Câu "Sóng ___ bờ cát." Điền động từ nhân hoá chỉ hành động vỗ về của sóng.', [[0, 'vỗ về']], '"Vỗ về" là hành động của con người, gán cho sóng tạo phép nhân hoá sinh động.', $d);
        $this->fill($s, 'So sánh "khác" với ẩn dụ ở chỗ so sánh vẫn giữ từ so sánh như "như", còn ẩn dụ đã ___ bỏ nó.', [[0, 'lược']], 'Ẩn dụ là phép so sánh ngầm: lược bỏ từ so sánh, gọi thẳng tên sự vật so sánh.', $d);
    }

    /* ============ nv-an-du-hoan-du (Ngữ văn 7, kho) ============ */
    private function seedNvAnDuHoanDu(): void
    {
        $s = 'nv-an-du-hoan-du'; $d = 'kho';
        $this->quiz($s, 'Câu "Ngày ngày mặt trời đi qua trên lăng / Thấy một mặt trời trong lăng rất đỏ" — "mặt trời trong lăng" là biện pháp gì?', ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Hoán dụ'], 2, '"Mặt trời trong lăng" ẩn dụ chỉ Bác Hồ dựa trên nét tương đồng (tỏa sáng, vĩ đại), không có từ so sánh.', $d);
        $this->quiz($s, '"Áo chàm đưa buổi phân li" — "áo chàm" là biện pháp tu từ gì?', ['Ẩn dụ', 'Hoán dụ', 'So sánh', 'Nhân hoá'], 1, '"Áo chàm" lấy dấu hiệu (màu áo) để chỉ người Việt Bắc — quan hệ gần gũi nên là hoán dụ.', $d);
        $this->quiz($s, 'Điểm khác nhau CƠ BẢN giữa hoán dụ và ẩn dụ là gì?', ['Hoán dụ dựa trên nét tương đồng, ẩn dụ dựa trên quan hệ gần gũi', 'Ẩn dụ dựa trên nét tương đồng, hoán dụ dựa trên quan hệ gần gũi', 'Hoán dụ có từ so sánh, ẩn dụ không có', 'Ẩn dụ chỉ dùng trong thơ, hoán dụ chỉ dùng trong văn xuôi'], 1, 'Ẩn dụ: A giống B (tương đồng). Hoán dụ: A gần gũi/đi kèm với B (bộ phận–toàn thể, vật chứa–vật bị chứa...).', $d);
        $this->quiz($s, 'Câu "Một cây làm chẳng nên non / Ba cây chụm lại nên hòn núi cao" — "cây" là biện pháp gì?', ['Hoán dụ', 'Ẩn dụ', 'So sánh', 'Nhân hoá'], 1, '"Cây" ẩn dụ chỉ con người dựa trên nét tương đồng (đơn lẻ thì yếu, đoàn kết thì mạnh).', $d);
        $this->quiz($s, 'Câu "Cả trường reo hò cổ vũ." — "cả trường" là biện pháp gì?', ['Ẩn dụ', 'Hoán dụ', 'Nói quá', 'Liệt kê'], 1, 'Lấy vật chứa (trường) để chỉ vật bị chứa (học sinh trong trường) — quan hệ gần gũi nên là hoán dụ.', $d);

        $this->matching($s, 'Nối mỗi câu thơ với kiểu ẩn dụ của nó.', [['"Người cha mái tóc bạc" (chỉ Bác Hồ)', 'Ẩn dụ phẩm chất'], ['"Thuyền về có nhớ bến chăng?"', 'Ẩn dụ hình thức'], ['"Ăn quả nhớ kẻ trồng cây"', 'Ẩn dụ cách thức'], ['"Mặt trời trong lăng"', 'Ẩn dụ phẩm chất']], 'Bốn kiểu ẩn dụ: hình thức, cách thức, phẩm chất và chuyển đổi cảm giác.', $d);
        $this->matching($s, 'Nối mỗi câu với kiểu hoán dụ của nó.', [['"Bàn tay ta làm nên tất cả"', 'Lấy bộ phận chỉ toàn thể'], ['"Cả lớp vỗ tay"', 'Lấy vật chứa chỉ vật bị chứa'], ['"Áo nâu liền với áo xanh"', 'Lấy dấu hiệu chỉ sự vật'], ['"Tay kẻ nọ, chân người kia"', 'Lấy bộ phận chỉ toàn thể']], 'Bốn kiểu hoán dụ quen thuộc: bộ phận–toàn thể, vật chứa–vật bị chứa, dấu hiệu–sự vật, cụ thể–trừu tượng.', $d);
        $this->matching($s, 'Nối mỗi hình ảnh với cơ sở tạo phép tu từ.', [['"Thuyền – bến" nhớ nhau', 'Tương đồng (tình cảm thủy chung)'], ['"Bàn tay" chỉ người lao động', 'Gần gũi (bộ phận – toàn thể)'], ['"Mặt trời" chỉ Bác Hồ', 'Tương đồng (tỏa sáng, vĩ đại)'], ['"Áo chàm" chỉ người Việt Bắc', 'Gần gũi (dấu hiệu – sự vật)']], 'Tương đồng sinh ẩn dụ, gần gũi sinh hoán dụ — đây là chìa khóa phân biệt hai phép.', $d);
        $this->matching($s, 'Nối mỗi nhận định với phép tu từ đúng.', [['Gọi tên A bằng tên B có nét giống nhau', 'Ẩn dụ'], ['Gọi tên A bằng tên B có quan hệ đi kèm', 'Hoán dụ'], ['Lược bỏ từ so sánh', 'Ẩn dụ'], ['"Sen" chỉ người quân tử', 'Ẩn dụ']], '"Sen chỉ người quân tử" dựa trên nét tương đồng (thanh cao) nên là ẩn dụ, không phải hoán dụ.', $d);
        $this->matching($s, 'Nối mỗi câu ca dao với biện pháp tu từ.', [['"Thân em như tấm lụa đào"', 'So sánh'], ['"Bầu ơi thương lấy bí cùng"', 'Nhân hoá'], ['"Một cây làm chẳng nên non"', 'Ẩn dụ'], ['"Tay làm hàm nhai"', 'Hoán dụ']], '"Tay làm hàm nhai": lấy bộ phận (tay, hàm) chỉ con người lao động và hưởng thụ — hoán dụ.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm ẨN DỤ PHẨM CHẤT hoặc ẨN DỤ HÌNH THỨC.', [['"Người cha mái tóc bạc" (chỉ Bác)', 'ẨN DỤ PHẨM CHẤT'], ['"Thuyền về có nhớ bến chăng"', 'ẨN DỤ HÌNH THỨC'], ['"Mặt trời trong lăng rất đỏ"', 'ẨN DỤ PHẨM CHẤT'], ['"Về thăm quê Bác làng Sen"', 'ẨN DỤ HÌNH THỨC']], 'Ẩn dụ phẩm chất dựa vào đức tính, ẩn dụ hình thức dựa vào hình dáng bên ngoài.', $d);
        $this->sortQ($s, 'Kéo mỗi hình ảnh vào nhóm LẤY VẬT CHỨA CHỈ VẬT BỊ CHỨA hoặc LẤY DẤU HIỆU CHỈ SỰ VẬT.', [['"Cả nhà đang ăn cơm"', 'VẬT CHỨA – VẬT BỊ CHỨA'], ['"Áo trắng đến trường"', 'DẤU HIỆU – SỰ VẬT'], ['"Cả trường đi tham quan"', 'VẬT CHỨA – VẬT BỊ CHỨA'], ['"Mũ cối ra trận"', 'DẤU HIỆU – SỰ VẬT'], ['"Xóm làng rộn ràng"', 'VẬT CHỨA – VẬT BỊ CHỨA'], ['"Khăn quàng đỏ tung bay"', 'DẤU HIỆU – SỰ VẬT']], '"Áo trắng" chỉ học sinh, "mũ cối" chỉ bộ đội — đều là lấy dấu hiệu đặc trưng để gọi tên.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm DỰA TRÊN TƯƠNG ĐỒNG hoặc DỰA TRÊN GẦN GŨI.', [['"Uống nước nhớ nguồn"', 'DỰA TRÊN TƯƠNG ĐỒNG'], ['"Tay làm hàm nhai"', 'DỰA TRÊN GẦN GŨI'], ['"Gần mực thì đen"', 'DỰA TRÊN TƯƠNG ĐỒNG'], ['"Nhà có khách quý"', 'DỰA TRÊN GẦN GŨI']], 'Tương đồng: A giống B. Gần gũi: A đi kèm/ là một phần của B.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ HOÁN DỤ hoặc KHÔNG CÓ HOÁN DỤ.', [['"Đầu xanh đã tội tình gì?"', 'CÓ HOÁN DỤ'], ['"Trăng lên đầu núi."', 'KHÔNG CÓ HOÁN DỤ'], ['"Tóc bạc phơ theo gió."', 'CÓ HOÁN DỤ'], ['"Sông chảy êm đềm."', 'KHÔNG CÓ HOÁN DỤ']], '"Đầu xanh" chỉ tuổi trẻ, "tóc bạc" chỉ người già — lấy dấu hiệu chỉ con người.', $d);
        $this->sortQ($s, 'Kéo mỗi quan hệ vào nhóm SINH ẨN DỤ hoặc SINH HOÁN DỤ.', [['Tương đồng về hình thức', 'SINH ẨN DỤ'], ['Bộ phận – toàn thể', 'SINH HOÁN DỤ'], ['Tương đồng về phẩm chất', 'SINH ẨN DỤ'], ['Dấu hiệu – sự vật', 'SINH HOÁN DỤ']], 'Nhớ công thức: tương đồng → ẩn dụ, gần gũi → hoán dụ.', $d);

        $this->fill($s, 'Ẩn dụ là so sánh ngầm: gọi tên sự vật này bằng tên sự vật khác mà không dùng từ so ___.', [[0, 'sánh']], 'Ẩn dụ lược bỏ từ so sánh ("như"), gọi thẳng tên: "Người cha" thay vì "như người cha".', $d);
        $this->fill($s, '"Làng Sen" chỉ quê Bác là lấy địa danh để chỉ con người — quan hệ gần gũi nên là hoán ___.', [[0, 'dụ']], 'Địa danh gắn liền với con người: lấy nơi chốn chỉ người là hoán dụ kiểu gần gũi.', $d);
        $this->fill($s, 'Bốn kiểu ẩn dụ gồm: hình thức, cách thức, phẩm chất và chuyển đổi cảm ___.', [[0, 'giác']], 'Chuyển đổi cảm giác: "nắng ửng hồng" — màu sắc mà như có thể sờ thấy.', $d);
        $this->fill($s, 'Câu "___ ơi thương lấy bí cùng" dùng nhân hoá xưng hô "bầu ơi" với quả bầu.', [[0, 'Bầu']], 'Đây là câu ca dao quen thuộc mở đầu bằng phép nhân hoá "Bầu ơi".', $d);
        $this->fill($s, 'Hoán dụ "sen" trong "gần bùn mà chẳng hôi tanh mùi bùn" thực chất là ___ dụ vì dựa trên nét tương đồng thanh cao.', [[0, 'ẩn']], 'Cẩn thận: hoa sen chỉ người quân tử dựa trên phẩm chất tương đồng → ẩn dụ, không phải hoán dụ.', $d);
    }

    /* ============ nv-bo-cuc-mieu-ta (Ngữ văn 8, trung_binh) ============ */
    private function seedNvBoCucMieuTa(): void
    {
        $s = 'nv-bo-cuc-mieu-ta'; $d = 'trung_binh';
        $this->quiz($s, 'Phần thân bài của bài văn miêu tả thường được sắp xếp theo trình tự nào?', ['Tùy ý, không cần trình tự', 'Từ bao quát đến chi tiết hoặc theo thời gian, không gian', 'Chỉ tả một chi tiết duy nhất', 'Liệt kê càng nhiều càng tốt'], 1, 'Thân bài tả chi tiết theo trình tự hợp lí: không gian (xa–gần), thời gian (sáng–tối) hoặc bao quát–chi tiết.', $d);
        $this->quiz($s, 'Khi tả người, phần mở bài thường làm gì?', ['Kể toàn bộ cuộc đời nhân vật', 'Giới thiệu người được tả trong hoàn cảnh cụ thể', 'Nêu cảm nghĩ dài dòng', 'Liệt kê các bộ phận cơ thể'], 1, 'Mở bài giới thiệu đối tượng trong một hoàn cảnh, ấn tượng cụ thể để dẫn vào bài.', $d);
        $this->quiz($s, 'Câu văn nào sau đây phù hợp với phần kết bài của văn miêu tả?', ['Hôm nay trời nắng to.', 'Em rất yêu quý khu vườn của ông.', 'Khu vườn rộng khoảng 500m2.', 'Trong vườn có nhiều loại cây.'], 1, 'Kết bài bộc lộ tình cảm, suy nghĩ của người viết về đối tượng được tả.', $d);
        $this->quiz($s, 'Muốn tả cảnh sinh động, người viết cần kết hợp các giác quan nào?', ['Chỉ cần mắt nhìn', 'Mắt nhìn, tai nghe, mũi ngửi, cảm xúc', 'Chỉ cần tưởng tượng', 'Chỉ cần nghe kể lại'], 1, 'Quan sát bằng nhiều giác quan (nhìn, nghe, ngửi, chạm) giúp chi tiết giàu sức gợi.', $d);
        $this->quiz($s, 'Lỗi nào sau đây cần tránh khi viết bài văn miêu tả?', ['Dùng từ ngữ gợi hình, gợi cảm', 'Tả lan man, thiếu trình tự và chi tiết tiêu biểu', 'Kết hợp miêu tả với bộc lộ cảm xúc', 'Quan sát kĩ đối tượng trước khi tả'], 1, 'Tả lan man, dàn trải, thiếu chọn lọc chi tiết tiêu biểu làm bài văn nhạt nhòa.', $d);

        $this->matching($s, 'Nối mỗi trình tự miêu tả với cách tả phù hợp.', [['Trình tự không gian', 'Tả từ xa đến gần, từ ngoài vào trong'], ['Trình tự thời gian', 'Tả theo diễn biến sáng – trưa – chiều – tối'], ['Từ bao quát đến chi tiết', 'Tả toàn cảnh rồi đi vào từng bộ phận'], ['Theo cảm xúc', 'Tả gắn với diễn biến tình cảm người viết']], 'Chọn trình tự phù hợp với đối tượng: tả cảnh hay dùng không gian/thời gian, tả người hay dùng bao quát–chi tiết.', $d);
        $this->matching($s, 'Nối mỗi đối tượng với giác quan chủ yếu khi quan sát.', [['Màu sắc của hoa', 'Thị giác (mắt)'], ['Tiếng suối chảy', 'Thính giác (tai)'], ['Hương thơm của lúa', 'Khứu giác (mũi)'], ['Mặt nước mát lạnh', 'Xúc giác (da)']], 'Mỗi giác quan thu nhận một loại chi tiết riêng, càng nhiều giác quan bài văn càng giàu.', $d);
        $this->matching($s, 'Nối mỗi phần của bài văn tả cảnh với nội dung của nó.', [['Mở bài', 'Giới thiệu cảnh được tả và ấn tượng chung'], ['Thân bài', 'Tả chi tiết cảnh vật theo trình tự'], ['Kết bài', 'Nêu cảm nghĩ về cảnh vật'], ['Nhan đề', 'Gợi mở nội dung, gây tò mò']], 'Ba phần mở – thân – kết có nhiệm vụ riêng, liên kết chặt chẽ với nhau.', $d);
        $this->matching($s, 'Nối mỗi biện pháp tu từ với tác dụng khi tả cảnh.', [['So sánh', 'Làm hình ảnh cụ thể, dễ hình dung'], ['Nhân hoá', 'Làm cảnh vật có hồn, sinh động'], ['Từ láy', 'Gợi hình, gợi thanh tinh tế'], ['Điệp ngữ', 'Nhấn mạnh, tạo nhịp điệu']], 'Tu từ là "gia vị" không thể thiếu của văn miêu tả hay.', $d);
        $this->matching($s, 'Nối mỗi câu văn với trình tự miêu tả được dùng.', [['"Xa xa, dãy núi mờ ảo; gần hơn, cánh đồng lúa chín vàng."', 'Không gian (xa – gần)'], ['"Buổi sáng sương giăng; trưa nắng rực; chiều mây hồng."', 'Thời gian'], ['"Nhìn chung, khu vườn rất đẹp; mỗi góc vườn lại có nét riêng."', 'Bao quát – chi tiết'], ['"Càng ngắm, em càng yêu quê hương."', 'Cảm xúc']], 'Nhận diện trình tự giúp học cách tổ chức đoạn văn, bài văn mạch lạc.', $d);

        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm QUAN SÁT BẰNG MẮT hoặc BẰNG MŨI.', [['Hoa phượng đỏ rực', 'QUAN SÁT BẰNG MẮT'], ['Hương bưởi thơm ngát', 'QUAN SÁT BẰNG MŨI'], ['Mây trắng bồng bềnh', 'QUAN SÁT BẰNG MẮT'], ['Mùi rơm rạ nồng nàn', 'QUAN SÁT BẰNG MŨI']], 'Mắt thu màu sắc, hình dáng; mũi thu hương thơm, mùi vị.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm MỞ BÀI TRỰC TIẾP hoặc MỞ BÀI GIÁN TIẾP.', [['"Mẹ em là người em yêu quý nhất."', 'MỞ BÀI TRỰC TIẾP'], ['"Mỗi lần về quê, em lại nhớ nụ cười của bà."', 'MỞ BÀI GIÁN TIẾP'], ['"Khu vườn nhà em rất đẹp."', 'MỞ BÀI TRỰC TIẾP'], ['"Tiếng ve râm ran gọi hè về, gợi nhớ cây phượng sân trường."', 'MỞ BÀI GIÁN TIẾP']], 'Mở bài trực tiếp giới thiệu ngay đối tượng; gián tiếp dẫn dắt từ chuyện khác rồi mới vào đề.', $d);
        $this->sortQ($s, 'Kéo mỗi cách viết vào nhóm NÊN LÀM hoặc NÊN TRÁNH khi tả người.', [['Chọn chi tiết tiêu biểu', 'NÊN LÀM'], ['Liệt kê chung chung', 'NÊN TRÁNH'], ['Kết hợp tả ngoại hình và tính cách', 'NÊN LÀM'], ['Tả dàn trải, thiếu trọng tâm', 'NÊN TRÁNH']], 'Chi tiết tiêu biểu là chi tiết gợi được tính cách, số phận — linh hồn của bài tả người.', $d);
        $this->sortQ($s, 'Kéo mỗi nội dung vào nhóm THUỘC THÂN BÀI hoặc THUỘC KẾT BÀI.', [['Tả từng bộ phận của đối tượng', 'THUỘC THÂN BÀI'], ['Bộc lộ tình cảm của người viết', 'THUỘC KẾT BÀI'], ['Tả theo trình tự không gian', 'THUỘC THÂN BÀI'], ['Khẳng định lại ấn tượng chung', 'THUỘC KẾT BÀI']], 'Thân bài là "xương sống" tả chi tiết; kết bài đọng lại cảm xúc.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ GỢI THANH hoặc TỪ GỢI HÌNH.', [['róc rách', 'TỪ GỢI THANH'], ['lấp lánh', 'TỪ GỢI HÌNH'], ['rì rào', 'TỪ GỢI THANH'], ['mênh mông', 'TỪ GỢI HÌNH'], ['lộp độp', 'TỪ GỢI THANH'], ['chót vót', 'TỪ GỢI HÌNH']], 'Từ gợi thanh tả âm thanh, từ gợi hình tả dáng vẻ — đều là chất liệu quý của văn miêu tả.', $d);

        $this->fill($s, 'Phần mở bài của văn miêu tả có nhiệm vụ ___ thiệu đối tượng được miêu tả.', [[0, 'giới']], 'Mở bài giới thiệu đối tượng trong hoàn cảnh, ấn tượng cụ thể để dẫn dắt người đọc.', $d);
        $this->fill($s, 'Khi tả cảnh theo trình tự thời gian, có thể tả từ buổi sáng đến buổi trưa rồi đến buổi ___.', [[0, 'chiều']], 'Trình tự thời gian: sáng – trưa – chiều – tối, theo diễn biến tự nhiên của cảnh vật.', $d);
        $this->fill($s, 'Chi tiết làm nổi bật tính cách, số phận của nhân vật gọi là chi tiết tiêu ___.', [[0, 'biểu']], 'Chi tiết tiêu biểu là linh hồn của bài văn tả người, tả vật.', $d);
        $this->fill($s, 'Để bài văn miêu tả sinh động, cần dùng từ ngữ gợi hình, gợi thanh và gợi ___.', [[0, 'cảm']], 'Từ ngữ gợi cảm khơi dậy cảm xúc, làm bài văn có hồn.', $d);
        $this->fill($s, 'Phần kết bài của văn miêu tả thường bộc lộ tình cảm, ___ nghĩ của người viết.', [[0, 'suy']], 'Kết bài đọng lại cảm xúc: yêu mến, tự hào, nhớ nhung về đối tượng được tả.', $d);
    }

    /* ============ nv-dau-cau (Ngữ văn 8, de) ============ */
    private function seedNvDauCau(): void
    {
        $s = 'nv-dau-cau'; $d = 'de';
        $this->quiz($s, 'Câu cảm thán "Ôi, quê hương đẹp quá!" cần dùng dấu câu nào ở cuối?', ['Dấu chấm', 'Dấu chấm than', 'Dấu chấm hỏi', 'Dấu phẩy'], 1, 'Câu cảm thán bộc lộ cảm xúc mạnh nên kết thúc bằng dấu chấm than (!).', $d);
        $this->quiz($s, 'Dấu chấm phẩy (;) thường dùng để làm gì?', ['Kết thúc câu hỏi', 'Ngăn cách các vế câu có cấu tạo phức tạp', 'Trích dẫn lời nói', 'Nhấn mạnh cảm xúc'], 1, 'Dấu chấm phẩy ngăn cách các vế câu hoặc bộ phận đẳng lập đã có dấu phẩy bên trong.', $d);
        $this->quiz($s, 'Trong câu "Mẹ dặn: "Con nhớ mang áo mưa nhé!"", dấu hai chấm có tác dụng gì?', ['Liệt kê', 'Báo hiệu lời nói trực tiếp', 'Giải thích nguyên nhân', 'Ngăn cách chủ ngữ'], 1, 'Dấu hai chấm đứng trước lời dẫn trực tiếp để báo hiệu lời nói của nhân vật.', $d);
        $this->quiz($s, 'Câu nào dùng dấu câu ĐÚNG?', ['"Bạn đi đâu?"', '"Bạn đi đâu."', '"Bạn đi đâu!"', '"Bạn đi đâu,"'], 0, 'Câu hỏi phải kết thúc bằng dấu chấm hỏi (?).', $d);
        $this->quiz($s, 'Dấu ngoặc đơn ( ) thường dùng để làm gì?', ['Trích dẫn lời nhân vật', 'Chú thích, giải thích thêm', 'Kết thúc đoạn văn', 'Liệt kê các ý'], 1, 'Nội dung trong ngoặc đơn là phần chú thích, bổ sung cho bộ phận đứng trước.', $d);

        $this->matching($s, 'Hãy nối mỗi dấu câu với công dụng của nó.', [['Dấu chấm than (!)', 'Kết thúc câu cảm thán, câu cầu khiến'], ['Dấu chấm phẩy (;)', 'Ngăn cách các vế câu phức tạp'], ['Dấu ngoặc đơn ( )', 'Chú thích, giải thích thêm'], ['Dấu gạch ngang (–)', 'Đánh dấu lời đối thoại, liệt kê']], 'Mỗi dấu câu có công dụng riêng, dùng sai sẽ làm câu văn khó hiểu hoặc sai nghĩa.', $d);
        $this->matching($s, 'Nối mỗi câu với dấu câu cần điền vào cuối câu.', [['"Em bé cười khanh khách"', 'Dấu chấm than (!)'], ['"Ai đã lấy bút của tớ"', 'Dấu chấm hỏi (?)'], ['"Hôm nay trời đẹp"', 'Dấu chấm (.)'], ['"Hãy giữ trật tự"', 'Dấu chấm than (!)']], 'Xác định kiểu câu (kể, hỏi, cảm, cầu khiến) rồi chọn dấu kết thúc phù hợp.', $d);
        $this->matching($s, 'Nối mỗi trường hợp với dấu câu nên dùng.', [['Liệt kê các món đồ', 'Dấu phẩy (,)'], ['Dẫn lời nói trực tiếp', 'Dấu hai chấm (:)'], ['Chú thích ngày sinh', 'Dấu ngoặc đơn ( )'], ['Đối thoại của nhân vật', 'Dấu gạch ngang (–)']], 'Dấu câu không chỉ kết thúc câu mà còn tổ chức, làm rõ cấu trúc bên trong câu.', $d);
        $this->matching($s, 'Nối mỗi lỗi dùng dấu câu với cách sửa đúng.', [['Thiếu dấu cuối câu', 'Thêm dấu chấm/hỏi/than phù hợp'], ['Dùng dấu phẩy tách chủ ngữ – vị ngữ', 'Bỏ dấu phẩy sai'], ['Lạm dụng dấu chấm than', 'Chỉ dùng khi thực sự cảm thán'], ['Quên dấu hai chấm trước lời dẫn', 'Thêm dấu hai chấm']], 'Lỗi dấu câu phổ biến nhất là tách chủ ngữ – vị ngữ bằng dấu phẩy và lạm dụng chấm than.', $d);
        $this->matching($s, 'Nối mỗi kí hiệu với tên gọi đúng.', [['!', 'Dấu chấm than'], [';', 'Dấu chấm phẩy'], [':', 'Dấu hai chấm'], ['…', 'Dấu ba chấm']], 'Dấu ba chấm (…) diễn tả lời nói ngập ngừng, còn dang dở hoặc liệt kê chưa hết.', $d);

        $this->sortQ($s, 'Kéo mỗi dấu vào nhóm DẤU KẾT THÚC CÂU hoặc DẤU DÙNG TRONG CÂU.', [['.', 'DẤU KẾT THÚC CÂU'], [',', 'DẤU DÙNG TRONG CÂU'], ['?', 'DẤU KẾT THÚC CÂU'], [';', 'DẤU DÙNG TRONG CÂU'], ['!', 'DẤU KẾT THÚC CÂU'], [':', 'DẤU DÙNG TRONG CÂU']], 'Dấu kết thúc câu: . ? ! Dấu dùng trong câu: , ; : – ( ) " " …', $d);
        $this->sortQ($s, 'Dựa vào sắc thái, kéo mỗi câu (chưa có dấu cuối) vào nhóm CÂU CẢM hoặc CÂU KỂ.', [['Than ôi thời gian trôi nhanh', 'CÂU CẢM'], ['Hôm nay lớp học rất vui', 'CÂU KỂ'], ['Đẹp quá cánh đồng lúa chín', 'CÂU CẢM'], ['Mẹ đi chợ từ sớm', 'CÂU KỂ']], 'Câu cảm bộc lộ cảm xúc (thán từ "than ôi", "đẹp quá") dùng dấu chấm than.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm DÙNG DẤU NGOẶC KÉP ĐÚNG hoặc SAI.', [['Cô giáo nói: "Các em mở sách ra."', 'ĐÚNG'], ['"Hôm nay" trời đẹp quá.', 'SAI'], ['Bạn Nam hỏi: "Mấy giờ rồi?"', 'ĐÚNG'], ['Em "rất" thích đọc sách.', 'SAI']], 'Ngoặc kép đánh dấu lời nói trực tiếp hoặc tên tác phẩm, không dùng để nhấn mạnh từ ngẫu nhiên.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm NÊN DÙNG DẤU BA CHẤM hoặc KHÔNG NÊN.', [['Lời nói ngập ngừng, bỏ dở', 'NÊN DÙNG'], ['Kết thúc câu kể thông thường', 'KHÔNG NÊN'], ['Liệt kê còn tiếp diễn', 'NÊN DÙNG'], ['Câu hỏi cần trả lời ngay', 'KHÔNG NÊN']], 'Dấu ba chấm (…) gợi sự ngập ngừng, dở dang hoặc liệt kê chưa hết.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU CẦU KHIẾN hoặc CÂU NGHI VẤN.', [['Hãy mở cửa sổ ra!', 'CÂU CẦU KHIẾN'], ['Bạn có thích đọc sách không?', 'CÂU NGHI VẤN'], ['Xin mời các bạn đứng lên!', 'CÂU CẦU KHIẾN'], ['Ai là lớp trưởng?', 'CÂU NGHI VẤN']], 'Câu cầu khiến dùng để yêu cầu, đề nghị (dấu ! hoặc .); câu nghi vấn dùng để hỏi (dấu ?).', $d);

        $this->fill($s, 'Dấu ___ phẩy dùng để ngăn cách các vế của câu ghép.', [[0, 'chấm']], 'Dấu chấm phẩy (;) ngăn cách các vế câu ghép có cấu tạo phức tạp, đã chứa dấu phẩy.', $d);
        $this->fill($s, 'Câu cầu khiến "Hãy giữ im lặng" có thể kết thúc bằng dấu chấm than hoặc dấu ___.', [[0, 'chấm']], 'Câu cầu khiến nhẹ nhàng (đề nghị, mời) có thể kết thúc bằng dấu chấm.', $d);
        $this->fill($s, 'Dấu gạch ___ dùng để đánh dấu lời đối thoại của nhân vật.', [[0, 'ngang']], 'Trong văn xuôi, mỗi lượt lời nhân vật được đánh dấu bằng dấu gạch ngang (–) đầu dòng.', $d);
        $this->fill($s, '"Chú thích: Hà Nội (thủ đô của Việt Nam) rất đẹp." Phần trong ngoặc đơn có tác dụng ___ thích.', [[0, 'chú']], 'Ngoặc đơn chứa phần chú thích, giải thích thêm cho từ ngữ đứng trước.', $d);
        $this->fill($s, 'Không được dùng dấu phẩy để ngăn cách ___ ngữ với vị ngữ.', [[0, 'chủ']], 'Đây là lỗi dấu câu kinh điển: chủ ngữ và vị ngữ là nòng cốt câu, không tách bằng dấu phẩy.', $d);
    }

    /* ============ nv-tu-loai-lop-6-1 (Ngữ văn 6, de) ============ */
    private function seedNvTuLoaiLop61(): void
    {
        $s = 'nv-tu-loai-lop-6-1'; $d = 'de';
        $this->quiz($s, 'Từ nào sau đây là danh từ chỉ hiện tượng tự nhiên?', ['cơn gió', 'cái bàn', 'chạy nhảy', 'xanh biếc'], 0, '"Cơn gió" chỉ một hiện tượng trong tự nhiên nên là danh từ chỉ hiện tượng.', $d);
        $this->quiz($s, 'Từ nào sau đây là danh từ chỉ khái niệm?', ['niềm vui', 'quyển vở', 'con mèo', 'bông hoa'], 0, '"Niềm vui" chỉ một khái niệm trừu tượng, không sờ thấy được.', $d);
        $this->quiz($s, 'Trong các từ sau, từ nào là danh từ riêng?', ['sông Hồng', 'dòng sông', 'con sông', 'sông quê'], 0, '"Sông Hồng" là tên riêng của một con sông cụ thể, phải viết hoa.', $d);
        $this->quiz($s, 'Cụm từ "các bạn học sinh chăm ngoan" có từ trung tâm là từ nào?', ['các', 'bạn', 'học sinh', 'chăm ngoan'], 1, 'Từ trung tâm của cụm danh từ là danh từ "bạn"; "các" là phụ trước, "học sinh chăm ngoan" là phụ sau.', $d);
        $this->quiz($s, 'Từ nào sau đây KHÔNG phải danh từ?', ['tình thương', 'hạnh phúc', 'vui vẻ', 'kỉ niệm'], 2, '"Vui vẻ" chỉ tính chất nên là tính từ; còn lại đều là danh từ chỉ khái niệm.', $d);

        $this->matching($s, 'Nối mỗi danh từ với loại danh từ của nó.', [['Hà Nội', 'Danh từ riêng'], ['học sinh', 'Danh từ chung'], ['cơn mưa', 'Danh từ chỉ hiện tượng'], ['lòng dũng cảm', 'Danh từ chỉ khái niệm']], 'Danh từ riêng chỉ tên riêng cụ thể; danh từ chung chỉ loại sự vật nói chung.', $d);
        $this->matching($s, 'Nối mỗi cụm danh từ với cấu tạo của nó.', [['những cánh đồng', 'Phụ trước + từ trung tâm'], ['bông hoa đẹp', 'Từ trung tâm + phụ sau'], ['các em học sinh', 'Phụ trước + từ trung tâm + phụ sau'], ['một bầu trời', 'Phụ trước + từ trung tâm']], 'Cụm danh từ gồm từ trung tâm (danh từ) và các phụ từ đứng trước, đứng sau.', $d);
        $this->matching($s, 'Nối mỗi tên riêng với quy tắc viết đúng.', [['Nguyễn Du', 'Viết hoa chữ cái đầu mỗi tiếng'], ['sông Cửu Long', 'Viết hoa chữ cái đầu mỗi tiếng'], ['Việt Nam', 'Viết hoa chữ cái đầu mỗi tiếng'], ['Trường Sa', 'Viết hoa chữ cái đầu mỗi tiếng']], 'Tên riêng tiếng Việt viết hoa chữ cái đầu của MỌI tiếng tạo thành tên.', $d);
        $this->matching($s, 'Nối mỗi từ với nghĩa của nó trong cụm danh từ.', [['những', 'Phụ từ chỉ số nhiều'], ['cái', 'Phụ từ chỉ đơn vị'], ['đẹp', 'Phụ từ bổ nghĩa (tính từ)'], ['kia', 'Phụ từ chỉ định']], 'Phụ từ trong cụm danh từ giúp xác định số lượng, đặc điểm, vị trí của sự vật.', $d);
        $this->matching($s, 'Nối mỗi câu với danh từ trung tâm của cụm danh từ trong câu.', [['"Những bông hoa thơm ngát."', 'bông'], ['"Các chiến sĩ dũng cảm."', 'chiến sĩ'], ['"Một bầu trời trong xanh."', 'bầu'], ['"Con đường làng quanh co."', 'đường']], 'Tìm danh từ làm "hạt nhân" của cụm, các từ còn lại chỉ là phụ từ bổ nghĩa.', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DANH TỪ CHỈ NGƯỜI hoặc DANH TỪ CHỈ VẬT.', [['bác sĩ', 'DANH TỪ CHỈ NGƯỜI'], ['cái kéo', 'DANH TỪ CHỈ VẬT'], ['cô giáo', 'DANH TỪ CHỈ NGƯỜI'], ['quyển truyện', 'DANH TỪ CHỈ VẬT'], ['chú bộ đội', 'DANH TỪ CHỈ NGƯỜI'], ['chiếc xe đạp', 'DANH TỪ CHỈ VẬT']], 'Danh từ chỉ người gọi tên con người; danh từ chỉ vật gọi tên đồ vật, cây cối, con vật.', $d);
        $this->sortQ($s, 'Kéo mỗi cụm từ vào nhóm CÓ TỪ TRUNG TÂM LÀ DANH TỪ hoặc KHÔNG PHẢI.', [['những đám mây', 'CÓ TỪ TRUNG TÂM LÀ DANH TỪ'], ['đang bay cao', 'KHÔNG PHẢI'], ['một giấc mơ đẹp', 'CÓ TỪ TRUNG TÂM LÀ DANH TỪ'], ['rất chăm chỉ', 'KHÔNG PHẢI']], '"Đang bay cao" trung tâm là động từ, "rất chăm chỉ" trung tâm là tính từ.', $d);
        $this->sortQ($s, 'Kéo mỗi danh từ vào nhóm DANH TỪ ĐƠN VỊ hoặc DANH TỪ CHỈ SỰ VẬT.', [['cái (cái bàn)', 'DANH TỪ ĐƠN VỊ'], ['bàn', 'DANH TỪ CHỈ SỰ VẬT'], ['con (con mèo)', 'DANH TỪ ĐƠN VỊ'], ['mèo', 'DANH TỪ CHỈ SỰ VẬT']], 'Danh từ đơn vị (cái, con, chiếc...) đi kèm để đếm sự vật, danh từ chỉ sự vật gọi tên sự vật.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm VIẾT HOA ĐÚNG hoặc VIẾT HOA SAI.', [['Hồ Gươm', 'VIẾT HOA ĐÚNG'], ['hồ gươm', 'VIẾT HOA SAI'], ['Bác Hồ', 'VIẾT HOA ĐÚNG'], ['bác hồ', 'VIẾT HOA SAI']], 'Danh từ riêng phải viết hoa tất cả các tiếng, viết thường là sai chính tả.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DANH TỪ TRỪU TƯỢNG hoặc DANH TỪ CỤ THỂ.', [['tình bạn', 'DANH TỪ TRỪU TƯỢNG'], ['ngôi nhà', 'DANH TỪ CỤ THỂ'], ['ước mơ', 'DANH TỪ TRỪU TƯỢNG'], ['cây bàng', 'DANH TỪ CỤ THỂ']], 'Danh từ trừu tượng chỉ khái niệm không nhìn thấy; danh từ cụ thể chỉ sự vật hữu hình.', $d);

        $this->fill($s, 'Cụm danh từ gồm từ trung tâm và các phụ từ đứng trước hoặc đứng ___ nó.', [[0, 'sau']], 'Ví dụ: "những bông hoa đẹp" — "những" phụ trước, "đẹp" phụ sau, "bông" là trung tâm.', $d);
        $this->fill($s, 'Danh từ "sông" là danh từ chung, còn "sông Đà" là danh từ ___.', [[0, 'riêng']], '"Sông Đà" là tên riêng của một con sông cụ thể nên phải viết hoa.', $d);
        $this->fill($s, 'Trong cụm "các bạn nhỏ", phụ từ chỉ số nhiều "các" đứng ___ từ trung tâm "bạn".', [[0, 'trước']], 'Phụ từ chỉ lượng (các, những, mọi) luôn đứng trước từ trung tâm trong cụm danh từ.', $d);
        $this->fill($s, '"___ mơ" là danh từ chỉ khái niệm, thường đi với từ "giấc" tạo thành cụm danh từ.', [[0, 'Ước']], '"Giấc mơ, ước mơ" chỉ khát vọng, mong muốn — danh từ trừu tượng.', $d);
        $this->fill($s, 'Từ "cái" trong "cái đẹp" biến tính từ thành danh từ chỉ khái ___.', [[0, 'niệm']], '"Cái đẹp, cái hay" là cách danh từ hóa tính từ để chỉ khái niệm trừu tượng.', $d);
    }

    /* ============ nv-tu-loai-lop-6-2 (Ngữ văn 6, de) ============ */
    private function seedNvTuLoaiLop62(): void
    {
        $s = 'nv-tu-loai-lop-6-2'; $d = 'de';
        $this->quiz($s, 'Từ nào sau đây là động từ chỉ trạng thái?', ['ngủ', 'chạy', 'nhảy', 'bơi'], 0, '"Ngủ" diễn tả trạng thái nghỉ ngơi của con người, không phải hành động mạnh.', $d);
        $this->quiz($s, 'Từ nào sau đây là tính từ chỉ âm thanh?', ['rì rào', 'đỏ thắm', 'to lớn', 'tròn trịa'], 0, '"Rì rào" gợi tả âm thanh (của lá, của sóng), thuộc nhóm tính từ gợi thanh.', $d);
        $this->quiz($s, 'Trong câu "Bé đang tập đi.", cụm động từ là gì?', ['Bé', 'đang tập đi', 'tập', 'đi'], 1, 'Cụm động từ "đang tập đi" gồm phụ từ "đang" + động từ trung tâm "tập" + bổ ngữ "đi".', $d);
        $this->quiz($s, 'Trong các cụm từ sau, cụm nào là cụm tính từ?', ['rất nhanh nhẹn', 'những bông hoa', 'đang học bài', 'cái bàn gỗ'], 0, '"Rất nhanh nhẹn" có từ trung tâm "nhanh nhẹn" là tính từ, "rất" là phụ từ chỉ mức độ.', $d);
        $this->quiz($s, 'Từ "yêu" trong câu "Em yêu quê hương." thuộc từ loại nào?', ['Danh từ', 'Động từ', 'Tính từ', 'Đại từ'], 1, '"Yêu" chỉ tình cảm, trạng thái tâm lí của con người nên là động từ chỉ trạng thái.', $d);

        $this->matching($s, 'Hãy nối mỗi động từ với nhóm nghĩa của nó.', [['đọc sách', 'Động từ chỉ hành động'], ['suy nghĩ', 'Động từ chỉ hoạt động trí tuệ'], ['tồn tại', 'Động từ chỉ trạng thái tồn tại'], ['mong muốn', 'Động từ chỉ tình cảm, ý chí']], 'Động từ không chỉ có hành động chân tay mà còn có hoạt động trí tuệ, tình cảm.', $d);
        $this->matching($s, 'Nối mỗi tính từ với sự vật nó thường miêu tả.', [['mặn mòi', 'Vị của biển, của mồ hôi'], ['thơm phức', 'Mùi của thức ăn'], ['mượt mà', 'Bề mặt của tóc, vải'], ['chói chang', 'Ánh nắng mặt trời']], 'Mỗi tính từ gắn với một nhóm sự vật quen thuộc, học theo nhóm dễ nhớ.', $d);
        $this->matching($s, 'Hãy nối mỗi cụm từ với loại cụm của nó.', [['sẽ đến trường', 'Cụm động từ'], ['hơi buồn ngủ', 'Cụm tính từ'], ['mấy quyển sách', 'Cụm danh từ'], ['đã ăn cơm', 'Cụm động từ']], 'Gọi tên cụm theo từ loại của từ trung tâm: động từ → cụm động từ.', $d);
        $this->matching($s, 'Nối mỗi phụ từ với từ loại nó thường đi kèm.', [['đang, sẽ, đã', 'Động từ'], ['rất, hơi, quá', 'Tính từ'], ['những, các, mọi', 'Danh từ'], ['không, chưa', 'Động từ / Tính từ']], 'Phụ từ là "bạn đồng hành" giúp nhận diện từ loại nhanh chóng.', $d);
        $this->matching($s, 'Nối mỗi câu với động từ chính trong câu.', [['"Mẹ nấu cơm rất ngon."', 'nấu'], ['"Chim hót líu lo."', 'hót'], ['"Em bé ngủ say."', 'ngủ'], ['"Hoa nở rộ."', 'nở']], 'Động từ chính là từ trung tâm của vị ngữ, trả lời câu hỏi "làm gì?".', $d);

        $this->sortQ($s, 'Kéo mỗi động từ vào nhóm CHỈ HÀNH ĐỘNG CHÂN TAY hoặc CHỈ HOẠT ĐỘNG TRÍ TUỆ.', [['viết', 'CHỈ HÀNH ĐỘNG CHÂN TAY'], ['suy nghĩ', 'CHỈ HOẠT ĐỘNG TRÍ TUỆ'], ['đá bóng', 'CHỈ HÀNH ĐỘNG CHÂN TAY'], ['tưởng tượng', 'CHỈ HOẠT ĐỘNG TRÍ TUỆ'], ['gặt lúa', 'CHỈ HÀNH ĐỘNG CHÂN TAY'], ['ghi nhớ', 'CHỈ HOẠT ĐỘNG TRÍ TUỆ']], 'Động từ trí tuệ (nghĩ, nhớ, hiểu) diễn ra trong đầu; động từ chân tay dùng cơ thể.', $d);
        $this->sortQ($s, 'Kéo mỗi tính từ vào nhóm CHỈ MỨC ĐỘ hoặc CHỈ TÍNH CHẤT.', [['rất', 'CHỈ MỨC ĐỘ'], ['đẹp', 'CHỈ TÍNH CHẤT'], ['quá', 'CHỈ MỨC ĐỘ'], ['thơm', 'CHỈ TÍNH CHẤT']], '"Rất, quá, hơi" là phụ từ chỉ mức độ đi kèm tính từ; "đẹp, thơm" mới là tính từ chỉ tính chất.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi cụm từ vào nhóm CỤM ĐỘNG TỪ hoặc CỤM TÍNH TỪ.', [['đang chơi đùa', 'CỤM ĐỘNG TỪ'], ['rất hồn nhiên', 'CỤM TÍNH TỪ'], ['sẽ về quê', 'CỤM ĐỘNG TỪ'], ['hơi se lạnh', 'CỤM TÍNH TỪ']], 'Trung tâm "chơi, về" là động từ; trung tâm "hồn nhiên, lạnh" là tính từ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm ĐỘNG TỪ CHỈ TÌNH CẢM hoặc ĐỘNG TỪ CHỈ HÀNH ĐỘNG.', [['thương', 'ĐỘNG TỪ CHỈ TÌNH CẢM'], ['cày', 'ĐỘNG TỪ CHỈ HÀNH ĐỘNG'], ['nhớ', 'ĐỘNG TỪ CHỈ TÌNH CẢM'], ['xây', 'ĐỘNG TỪ CHỈ HÀNH ĐỘNG']], 'Động từ tình cảm (yêu, thương, nhớ, ghét) diễn tả đời sống nội tâm con người.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm VỊ NGỮ LÀ ĐỘNG TỪ hoặc VỊ NGỮ LÀ TÍNH TỪ.', [['"Gió thổi mạnh."', 'VỊ NGỮ LÀ ĐỘNG TỪ'], ['"Trời trong xanh."', 'VỊ NGỮ LÀ TÍNH TỪ'], ['"Mẹ đi làm."', 'VỊ NGỮ LÀ ĐỘNG TỪ'], ['"Bé rất ngoan."', 'VỊ NGỮ LÀ TÍNH TỪ']], 'Vị ngữ cho biết chủ ngữ "làm gì" (động từ) hoặc "thế nào" (tính từ).', $d);

        $this->fill($s, 'Cụm động từ gồm động từ trung tâm và các phụ từ như "đang, sẽ, đã" đứng ___ nó.', [[0, 'trước']], 'Phụ từ chỉ thời gian (đang, sẽ, đã, vừa) luôn đứng trước động từ trung tâm.', $d);
        $this->fill($s, 'Trong cụm "hát rất hay", từ trung tâm "hát" là động từ, "rất hay" là phần phụ ___.', [[0, 'sau']], 'Cấu tạo cụm động từ: phụ trước + động từ trung tâm + phụ sau (bổ ngữ).', $d);
        $this->fill($s, 'Động từ "suy nghĩ, tưởng tượng" thuộc nhóm động từ chỉ hoạt động trí ___.', [[0, 'tuệ']], 'Động từ trí tuệ diễn tả hoạt động của bộ óc: nghĩ, hiểu, nhớ, đoán.', $d);
        $this->fill($s, 'Tính từ "thơm phức" gợi tả mùi hương, thuộc nhóm tính từ gợi ___.', [[0, 'cảm']], 'Tính từ gợi cảm khơi dậy cảm giác, cảm xúc: thơm phức, mặn mòi, êm ái.', $d);
        $this->fill($s, 'Trong câu "Em ___ bài rất chăm.", điền động từ còn thiếu chỉ hoạt động học tập.', [[0, 'học']], '"Học bài" là cụm động từ quen thuộc chỉ hoạt động học tập của học sinh.', $d);
    }

    /* ============ nv-tu-loai-lop-7-1 (Ngữ văn 7, de) ============ */
    private function seedNvTuLoaiLop71(): void
    {
        $s = 'nv-tu-loai-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Trong câu "Tiếng sáo diều vi vu.", từ "sáo" thuộc từ loại nào?', ['Danh từ', 'Động từ', 'Tính từ', 'Quan hệ từ'], 0, '"Sáo diều" là một loại sáo, chỉ đồ vật nên "sáo" là danh từ.', $d);
        $this->quiz($s, 'Trong câu "Em bé sáo rỗng chẳng biết gì.", từ "sáo" thuộc từ loại nào?', ['Danh từ', 'Động từ', 'Tính từ', 'Đại từ'], 2, '"Sáo rỗng" chỉ tính chất rỗng tuếch, nông cạn nên "sáo" ở đây là tính từ.', $d);
        $this->quiz($s, 'Vì sao cùng một từ "hay" lúc là tính từ, lúc là danh từ?', ['Vì từ điển ghi sai', 'Vì từ loại phụ thuộc vào ngữ cảnh sử dụng', 'Vì người nói tùy thích', 'Vì từ "hay" là ngoại lệ duy nhất'], 1, 'Từ loại không gắn chặt với hình thức từ mà do vai trò của từ trong câu quyết định.', $d);
        $this->quiz($s, 'Trong câu "Cánh đồng lúa chín vàng.", từ "chín" thuộc từ loại nào?', ['Danh từ', 'Động từ', 'Tính từ', 'Số từ'], 2, '"Chín" ở đây chỉ trạng thái chín của lúa (trái nghĩa với "xanh") nên là tính từ.', $d);
        $this->quiz($s, 'Câu nào có từ "ăn" là danh từ?', ['Bé đang ăn cơm.', 'Mâm cỗ có nhiều món ăn.', 'Ăn uống phải điều độ.', 'Cả nhà ăn tối.'], 1, '"Món ăn" chỉ đồ ăn thức uống — sự vật cụ thể nên là danh từ.', $d);

        $this->matching($s, 'Nối mỗi câu với từ loại của từ "tốt" trong câu.', [['"Bạn ấy học tốt."', 'Tính từ'], ['"Cái tốt cần phát huy."', 'Danh từ'], ['"Làm việc tốt nhé!"', 'Tính từ'], ['"Điều tốt đẹp sẽ đến."', 'Danh từ']], '"Học tốt": tốt bổ nghĩa cho động từ → tính từ. "Cái tốt": chỉ khái niệm → danh từ.', $d);
        $this->matching($s, 'Nối mỗi từ "xanh" với từ loại của nó trong câu.', [['"Lá còn xanh."', 'Tính từ'], ['"Màu xanh của trời."', 'Tính từ'], ['"Tuổi xanh tươi đẹp."', 'Danh từ'], ['"Núi xanh sừng sững."', 'Tính từ']], '"Tuổi xanh": "xanh" chỉ tuổi trẻ — khái niệm nên là danh từ; còn lại chỉ màu sắc → tính từ.', $d);
        $this->matching($s, 'Nối mỗi bước với việc cần làm khi xác định từ loại.', [['Bước 1', 'Đọc kĩ cả câu, nắm nghĩa chung'], ['Bước 2', 'Xác định vai trò ngữ pháp của từ'], ['Bước 3', 'Đặt câu hỏi: là gì? làm gì? thế nào?'], ['Bước 4', 'Kết luận từ loại của từ']], 'Quy trình 4 bước giúp xác định từ loại chính xác, tránh đoán mò.', $d);
        $this->matching($s, 'Nối mỗi từ đa nghĩa với ví dụ chuyển loại của nó.', [['"đá" (danh từ → động từ)', 'quả bóng đá → đá bóng'], ['"ăn" (động từ → danh từ)', 'ăn cơm → món ăn'], ['"đẹp" (tính từ → danh từ)', 'cô gái đẹp → vẻ đẹp'], ['"chạy" (động từ → danh từ)', 'chạy nhanh → cuộc chạy']], 'Nhiều từ thực từ chuyển loại linh hoạt khi đi vào ngữ cảnh khác nhau.', $d);
        $this->matching($s, 'Nối mỗi câu với lỗi sai khi xác định từ loại.', [['"Hoa hồng" → "hồng" là tính từ', 'Sai: "hồng" là danh từ (tên hoa)'], ['"Ăn cơm" → "ăn" là danh từ', 'Sai: "ăn" là động từ'], ['"Rất đẹp" → "rất" là tính từ', 'Sai: "rất" là phụ từ'], ['"Đang chạy" → "đang" là động từ', 'Sai: "đang" là phụ từ']], 'Phụ từ (đang, rất) không phải từ loại chính; phải xét vai trò thực trong câu.', $d);

        $this->sortQ($s, 'Xét từ "ngọt" trong mỗi câu, kéo vào nhóm "NGỌT" LÀ TÍNH TỪ hoặc LÀ DANH TỪ.', [['"Quả ngọt lịm."', 'LÀ TÍNH TỪ'], ['"Vị ngọt của mía."', 'LÀ DANH TỪ'], ['"Lời ngọt ngào."', 'LÀ TÍNH TỪ'], ['"Cái ngọt đầu môi."', 'LÀ DANH TỪ']], '"Vị ngọt, cái ngọt" chỉ khái niệm vị giác → danh từ; "ngọt lịm, ngọt ngào" tả đặc điểm → tính từ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ trong câu "Trẻ em vui đùa hồn nhiên." vào nhóm đúng.', [['Trẻ em', 'DANH TỪ'], ['vui đùa', 'ĐỘNG TỪ'], ['hồn nhiên', 'TÍNH TỪ']], 'Ba từ loại cơ bản cùng xuất hiện trong một câu ngắn.', $d);
        $this->sortQ($s, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI về từ loại.', [['Từ loại phụ thuộc ngữ cảnh', 'ĐÚNG'], ['Một từ chỉ thuộc một từ loại duy nhất', 'SAI'], ['Phụ từ "đang" là động từ', 'SAI'], ['"Cái đẹp" là danh từ', 'ĐÚNG']], '"Đang" là phụ từ chỉ thời gian, không phải động từ; "cái đẹp" là danh từ chỉ khái niệm.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ THỰC TỪ hoặc HƯ TỪ.', [['bàn', 'TỪ THỰC TỪ'], ['và', 'HƯ TỪ'], ['chạy', 'TỪ THỰC TỪ'], ['của', 'HƯ TỪ'], ['đẹp', 'TỪ THỰC TỪ'], ['những', 'HƯ TỪ']], 'Thực từ (danh, động, tính) có nghĩa thực; hư từ (quan hệ từ, phụ từ...) chỉ có nghĩa ngữ pháp.', $d);
        $this->sortQ($s, 'Kéo mỗi cách làm vào nhóm GIÚP XÁC ĐỊNH ĐÚNG hoặc DỄ SAI khi tìm từ loại.', [['Đặt từ vào câu cụ thể', 'GIÚP XÁC ĐỊNH ĐÚNG'], ['Nhớ nghĩa quen thuộc rồi đoán', 'DỄ SAI'], ['Xét từ đi kèm (đang, rất, những)', 'GIÚP XÁC ĐỊNH ĐÚNG'], ['Chỉ nhìn một từ đơn lẻ', 'DỄ SAI']], 'Từ đi kèm là manh mối quý: "đang" → động từ, "rất" → tính từ, "những" → danh từ.', $d);

        $this->fill($s, 'Từ "hay" trong "bài hát hay" là tính từ, nhưng trong "cái hay của truyện" thì "hay" là danh ___.', [[0, 'từ']], '"Cái hay" chỉ khái niệm trừu tượng nên đã chuyển thành danh từ.', $d);
        $this->fill($s, 'Muốn biết từ thuộc loại nào, hãy đặt câu hỏi: "là gì?" cho danh từ, "làm gì?" cho động từ và "thế nào?" cho tính ___.', [[0, 'từ']], 'Ba câu hỏi vàng giúp phân biệt ba từ loại cơ bản nhanh chóng.', $d);
        $this->fill($s, 'Từ "sáng" trong "buổi sáng" là danh từ, nhưng trong "trời sáng" thì "sáng" là tính ___.', [[0, 'từ']], '"Trời sáng" — "sáng" chỉ trạng thái của trời nên là tính từ.', $d);
        $this->fill($s, 'Phụ từ "rất" thường đi với tính từ, còn phụ từ "___" thường đi với động từ.', [[0, 'đang']], '"Đang học, sẽ đi, đã làm" — nhóm phụ từ thời gian là dấu hiệu của động từ.', $d);
        $this->fill($s, 'Trong câu "Mẹ ___ cơm rất ngon.", từ còn thiếu vừa là động từ vừa hợp nghĩa là "nấu".', [[0, 'nấu']], '"Nấu cơm" là cụm động từ quen thuộc; "nấu" chỉ hành động chế biến thức ăn.', $d);
    }

    /* ============ nv-tu-loai-lop-7-2 (Ngữ văn 7, trung_binh) ============ */
    private function seedNvTuLoaiLop72(): void
    {
        $s = 'nv-tu-loai-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Từ nào sau đây là lượng từ chỉ số ít?', ['những', 'các', 'mỗi', 'mọi'], 2, '"Mỗi" chỉ từng đơn vị riêng lẻ (mỗi người), còn "những, các, mọi" chỉ số nhiều.', $d);
        $this->quiz($s, 'Trong câu "Quyển sách đặt trên bàn.", từ "trên" là gì?', ['Danh từ', 'Động từ', 'Tính từ', 'Quan hệ từ'], 3, '"Trên" nối "quyển sách" với "bàn", biểu thị quan hệ vị trí nên là quan hệ từ.', $d);
        $this->quiz($s, 'Từ "với" trong câu "Em đi chơi với bạn." biểu thị quan hệ gì?', ['Nguyên nhân', 'Cùng tham gia', 'Tương phản', 'Mục đích'], 1, '"Với" ở đây nối hai người cùng thực hiện hành động đi chơi.', $d);
        $this->quiz($s, 'Cụm từ nào có lượng từ đứng trước danh từ?', ['học sinh giỏi', 'những bông hoa', 'chạy rất nhanh', 'cái bàn gỗ'], 1, '"Những" là lượng từ chỉ số nhiều đứng trước danh từ trung tâm "bông".', $d);
        $this->quiz($s, 'Câu nào có quan hệ từ chỉ nguyên nhân?', ['Vì trời mưa nên đường trơn.', 'Mẹ và em đi chợ.', 'Chim hót trên cành.', 'Bé chơi với mèo.'], 0, '"Vì... nên..." biểu thị quan hệ nguyên nhân – kết quả.', $d);

        $this->matching($s, 'Hãy nối mỗi lượng từ với ý nghĩa của nó.', [['những, các', 'Chỉ số nhiều'], ['mỗi', 'Chỉ từng đơn vị'], ['mọi', 'Chỉ toàn thể'], ['mấy', 'Chỉ số lượng không xác định']], 'Lượng từ giúp xác định số lượng của sự vật: ít, nhiều, từng cái hay tất cả.', $d);
        $this->matching($s, 'Hãy nối mỗi quan hệ từ với quan hệ nó biểu thị.', [['và', 'Quan hệ đẳng lập (ngang hàng)'], ['vì', 'Quan hệ nguyên nhân'], ['nhưng', 'Quan hệ tương phản'], ['để', 'Quan hệ mục đích']], 'Quan hệ từ là "cầu nối" ý nghĩa giữa các từ ngữ, vế câu.', $d);
        $this->matching($s, 'Nối mỗi câu với quan hệ từ xuất hiện trong câu.', [['"Tôi học bài để thi tốt."', 'để (mục đích)'], ['"Trời mưa nhưng em vẫn đi."', 'nhưng (tương phản)'], ['"Bút của em rất đẹp."', 'của (sở hữu)'], ['"Chim đậu trên cành."', 'trên (vị trí)']], 'Đọc câu, tìm từ nối và xác định mối quan hệ mà nó biểu thị.', $d);
        $this->matching($s, 'Nối mỗi từ trong câu "Mọi người đều yêu quý cô." với từ loại của nó.', [['Mọi', 'Lượng từ'], ['người', 'Danh từ'], ['đều', 'Phụ từ'], ['yêu quý', 'Động từ']], '"Mọi" chỉ toàn thể, "đều" là phụ từ nhấn mạnh, "yêu quý" là động từ tình cảm.', $d);
        $this->matching($s, 'Nối mỗi cặp quan hệ từ với cách dùng đúng.', [['vì ... nên ...', 'Nguyên nhân – kết quả'], ['tuy ... nhưng ...', 'Tương phản, nhượng bộ'], ['nếu ... thì ...', 'Điều kiện – kết quả'], ['càng ... càng ...', 'Tăng tiến']], 'Quan hệ từ thường đi thành cặp, tạo nên các kiểu câu ghép quen thuộc.', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm LƯỢNG TỪ hoặc PHỤ TỪ.', [['những', 'LƯỢNG TỪ'], ['rất', 'PHỤ TỪ'], ['mỗi', 'LƯỢNG TỪ'], ['đang', 'PHỤ TỪ'], ['các', 'LƯỢNG TỪ'], ['hơi', 'PHỤ TỪ']], 'Lượng từ chỉ số lượng sự vật; phụ từ bổ nghĩa cho động từ, tính từ.', $d);
        $this->sortQ($s, 'Kéo mỗi quan hệ từ vào nhóm CHỈ QUAN HỆ ĐẲNG LẬP hoặc CHỈ QUAN HỆ CHÍNH PHỤ.', [['và', 'ĐẲNG LẬP'], ['vì', 'CHÍNH PHỤ'], ['hoặc', 'ĐẲNG LẬP'], ['tuy', 'CHÍNH PHỤ']], '"Và, hoặc" nối các thành phần ngang hàng; "vì, tuy, nếu" tạo quan hệ chính – phụ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm LƯỢNG TỪ CHỈ TOÀN THỂ hoặc CHỈ SỐ LƯỢNG XÁC ĐỊNH.', [['mọi', 'CHỈ TOÀN THỂ'], ['một', 'SỐ LƯỢNG XÁC ĐỊNH'], ['tất cả', 'CHỈ TOÀN THỂ'], ['hai', 'SỐ LƯỢNG XÁC ĐỊNH']], '"Mọi, tất cả" bao quát toàn bộ; "một, hai" chỉ số lượng cụ thể.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ QUAN HỆ TỪ hoặc KHÔNG CÓ QUAN HỆ TỪ.', [['"Mẹ đi chợ với em."', 'CÓ QUAN HỆ TỪ'], ['"Chim hót líu lo."', 'KHÔNG CÓ'], ['"Sách để trên bàn."', 'CÓ QUAN HỆ TỪ'], ['"Bé cười tươi."', 'KHÔNG CÓ']], '"Với" và "trên" là quan hệ từ; hai câu còn lại không có từ nối.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm QUAN HỆ TỪ hoặc DANH TỪ.', [['của', 'QUAN HỆ TỪ'], ['nhà', 'DANH TỪ'], ['với', 'QUAN HỆ TỪ'], ['trường', 'DANH TỪ']], '"Của, với" chỉ làm nhiệm vụ nối, không gọi tên sự vật nên là quan hệ từ.', $d);

        $this->fill($s, 'Từ "mọi" trong "mọi người" chỉ toàn thể nên thuộc nhóm lượng ___.', [[0, 'từ']], 'Lượng từ gồm: chỉ số nhiều (những, các), số ít (mỗi), toàn thể (mọi, tất cả).', $d);
        $this->fill($s, 'Từ nối "nhưng" biểu thị quan hệ tương ___, thường đi cặp với "tuy".', [[0, 'phản']], '"Tuy khó nhưng vui" — cặp quan hệ từ "tuy... nhưng..." diễn tả sự tương phản.', $d);
        $this->fill($s, 'Trong "quyển vở của em", từ "của" là quan hệ từ chỉ quan hệ sở ___.', [[0, 'hữu']], '"Của" nối vật sở hữu với chủ sở hữu: vở của em, nhà của bạn.', $d);
        $this->fill($s, 'Cặp quan hệ từ "nếu ... ___ ..." diễn tả quan hệ điều kiện – kết quả.', [[0, 'thì']], '"Nếu chăm học thì sẽ giỏi" — "nếu... thì..." là cặp quan hệ từ quen thuộc.', $d);
        $this->fill($s, 'Trong "các bạn học sinh", lượng từ "các" đứng trước danh từ trung tâm "___".', [[0, 'bạn']], 'Cấu tạo: lượng từ (các) + danh từ trung tâm (bạn) + phụ sau (học sinh).', $d);
    }

    /* ============ nv-bien-phap-tu-tu-lop-7-1 (Ngữ văn 7, de) ============ */
    private function seedNvBienPhapTuTuLop71(): void
    {
        $s = 'nv-bien-phap-tu-tu-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Câu "Cánh diều bay cao như chim ưng." có mấy vế so sánh?', ['1', '2', '3', '0'], 1, 'Vế A là "cánh diều", vế B là "chim ưng", nối bằng từ so sánh "như".', $d);
        $this->quiz($s, 'Từ so sánh trong câu "Mặt hồ phẳng lặng tựa gương." là từ nào?', ['mặt hồ', 'phẳng lặng', 'tựa', 'gương'], 2, '"Tựa" là từ so sánh nối "mặt hồ" với "gương".', $d);
        $this->quiz($s, 'Câu nào sau đây KHÔNG sử dụng phép so sánh?', ['Nước trong như mắt mèo.', 'Trời hôm nay rất đẹp.', 'Tóc bà trắng như cước.', 'Môi đỏ như son.'], 1, '"Trời hôm nay rất đẹp" chỉ là câu kể miêu tả, không đối chiếu hai sự vật.', $d);
        $this->quiz($s, 'Nét tương đồng giữa "trẻ em" và "búp trên cành" trong câu "Trẻ em như búp trên cành." là gì?', ['Đều màu xanh', 'Đều non nớt, tràn đầy sức sống', 'Đều mọc trên cây', 'Đều nhỏ bé'], 1, 'Cả trẻ em và búp non đều non nớt, mơn mởn, hứa hẹn tương lai tươi sáng.', $d);
        $this->quiz($s, 'Phép so sánh thường được dùng nhiều nhất trong loại văn nào?', ['Văn nghị luận', 'Văn miêu tả', 'Văn bản hành chính', 'Đơn từ'], 1, 'Văn miêu tả cần hình ảnh cụ thể, sinh động nên rất chuộng phép so sánh.', $d);

        $this->matching($s, 'Nối mỗi câu với vế B (sự vật dùng để so sánh).', [['"Trẻ em như búp trên cành."', 'búp trên cành'], ['"Công cha như núi Thái Sơn."', 'núi Thái Sơn'], ['"Mắt sáng như sao."', 'sao'], ['"Lời ru như mật ngọt."', 'mật ngọt']], 'Vế B là hình ảnh quen thuộc, cụ thể giúp người đọc hình dung vế A rõ hơn.', $d);
        $this->matching($s, 'Nối mỗi từ so sánh với sắc thái của nó.', [['như', 'So sánh ngang bằng, phổ biến nhất'], ['tựa', 'So sánh nhẹ nhàng, văn chương'], ['là', 'So sánh khẳng định (A chính là B)'], ['bao nhiêu ... bấy nhiêu', 'So sánh tương ứng']], 'Mỗi từ so sánh mang một sắc thái riêng, tạo nên vẻ đẹp đa dạng cho câu văn.', $d);
        $this->matching($s, 'Nối mỗi câu ca dao với hình ảnh so sánh trong đó.', [['"Thân em như tấm lụa đào."', 'tấm lụa đào'], ['"Ước gì sông rộng một gang."', 'Không có so sánh'], ['"Công cha như núi Thái Sơn."', 'núi Thái Sơn'], ['"Nhiễu điều phủ lấy giá gương."', 'Không có so sánh']], 'Không phải câu ca dao nào cũng có so sánh; phải tìm từ so sánh và hai vế.', $d);
        $this->matching($s, 'Nối mỗi kiểu so sánh với ví dụ đúng.', [['So sánh ngang bằng', '"Trăng tròn như cái đĩa."'], ['So sánh hơn', '"Núi cao hơn đồi."'], ['So sánh nhất', '"Sông này sâu nhất vùng."'], ['So sánh kép', '"Càng học càng thấy hay."']], 'So sánh có nhiều kiểu: ngang bằng, hơn kém và so sánh kép "càng... càng...".', $d);
        $this->matching($s, 'Nối mỗi hình ảnh so sánh với tác dụng của nó.', [['"Mặt trời như quả cầu lửa"', 'Gợi tả sự rực rỡ'], ['"Tóc bà trắng như cước"', 'Gợi tả sự già nua'], ['"Da trắng như bông"', 'Gợi tả vẻ đẹp mịn màng'], ['"Chạy nhanh như gió"', 'Gợi tả tốc độ']], 'So sánh giúp trừu tượng thành cụ thể, xa lạ thành gần gũi.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm SO SÁNH NGANG BẰNG hoặc SO SÁNH HƠN KÉM.', [['"Hoa đẹp như tranh."', 'SO SÁNH NGANG BẰNG'], ['"Em cao hơn bạn."', 'SO SÁNH HƠN KÉM'], ['"Nước trong tựa pha lê."', 'SO SÁNH NGANG BẰNG'], ['"Bài này khó hơn bài kia."', 'SO SÁNH HƠN KÉM']], '"Như, tựa" → ngang bằng; "hơn, kém, nhất" → hơn kém.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ ĐỦ 3 BỘ PHẬN SO SÁNH hoặc THIẾU BỘ PHẬN.', [['"Mây trắng như bông."', 'CÓ ĐỦ 3 BỘ PHẬN'], ['"Đẹp như tranh."', 'THIẾU BỘ PHẬN'], ['"Lúa chín vàng như mật."', 'CÓ ĐỦ 3 BỘ PHẬN'], ['"Nhanh như gió."', 'THIẾU BỘ PHẬN']], '"Đẹp như tranh", "nhanh như gió" thiếu vế A (chủ thể được so sánh).', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ SO SÁNH NGANG BẰNG hoặc TỪ SO SÁNH HƠN KÉM.', [['như', 'NGANG BẰNG'], ['hơn', 'HƠN KÉM'], ['tựa', 'NGANG BẰNG'], ['nhất', 'HƠN KÉM'], ['là', 'NGANG BẰNG'], ['kém', 'HƠN KÉM']], 'Nhóm ngang bằng: như, tựa, là, giống. Nhóm hơn kém: hơn, kém, nhất.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm SO SÁNH ĐÚNG hoặc SO SÁNH KHẬP KHIỄNG.', [['"Mắt sáng như sao."', 'SO SÁNH ĐÚNG'], ['"Bàn cứng như sắt."', 'SO SÁNH ĐÚNG'], ['"Chữ đẹp như gà bới."', 'SO SÁNH KHẬP KHIỄNG'], ['"Cá tươi như rau."', 'SO SÁNH KHẬP KHIỄNG']], 'So sánh khập khiễng khi hai vế không có nét tương đồng hợp lí, gây buồn cười.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ PHÉP SO SÁNH hoặc CHỈ LÀ CÂU KỂ.', [['"Sương trắng như sữa."', 'CÓ PHÉP SO SÁNH'], ['"Sáng nay sương nhiều."', 'CHỈ LÀ CÂU KỂ'], ['"Ve kêu như xé vải."', 'CÓ PHÉP SO SÁNH'], ['"Mùa hè ve kêu to."', 'CHỈ LÀ CÂU KỂ']], 'Câu kể chỉ thông báo sự việc; câu so sánh đối chiếu hai sự vật với nhau.', $d);

        $this->fill($s, 'Mô hình đầy đủ của phép so sánh gồm: vế A, từ so sánh và vế ___.', [[0, 'B']], 'Ví dụ: "Trẻ em (A) như (từ so sánh) búp trên cành (B)".', $d);
        $this->fill($s, 'Trong câu "Lòng mẹ bao la như biển Thái Bình.", từ so sánh là từ "___".', [[0, 'như']], '"Như" là từ so sánh phổ biến nhất, nối "lòng mẹ" với "biển Thái Bình".', $d);
        $this->fill($s, 'So sánh làm cho hình ảnh trở nên cụ thể, sinh động và giàu sức ___ tả.', [[0, 'gợi']], 'Sức gợi là khả năng khơi dậy liên tưởng, cảm xúc ở người đọc.', $d);
        $this->fill($s, 'Câu "Tóc bà bạc phơ ___ cước." Điền từ so sánh còn thiếu.', [[0, 'như']], '"Trắng/bạc như cước" là hình ảnh so sánh quen thuộc chỉ mái tóc bạc của người già.', $d);
        $this->fill($s, 'Phép so sánh đối chiếu hai sự vật dựa trên nét ___ đồng giữa chúng.', [[0, 'tương']], 'Không có nét tương đồng thì phép so sánh trở nên khập khiễng, vô nghĩa.', $d);
    }

    /* ============ nv-bien-phap-tu-tu-lop-7-2 (Ngữ văn 7, de) ============ */
    private function seedNvBienPhapTuTuLop72(): void
    {
        $s = 'nv-bien-phap-tu-tu-lop-7-2'; $d = 'de';
        $this->quiz($s, 'Câu "Dòng sông hiền hòa ôm ấp xóm làng." sử dụng cách nhân hoá nào?', ['Xưng hô như con người', 'Gán hành động của con người', 'Trò chuyện với sự vật', 'Gán suy nghĩ cho sự vật'], 1, '"Ôm ấp" là hành động của con người được gán cho dòng sông.', $d);
        $this->quiz($s, 'Câu nào sau đây sử dụng phép nhân hoá bằng cách xưng hô?', ['Mây đen kéo đến.', 'Cô mây trắng bay lững lờ.', 'Trời nắng gắt.', 'Gió thổi mạnh.'], 1, 'Gọi mây là "cô" — xưng hô như với con người nên là nhân hoá.', $d);
        $this->quiz($s, 'Phép nhân hoá khác phép so sánh ở điểm nào?', ['Nhân hoá không dùng từ "như"', 'Nhân hoá gán đặc điểm người cho vật, so sánh đối chiếu hai sự vật', 'Nhân hoá chỉ dùng trong thơ', 'So sánh hay hơn nhân hoá'], 1, 'So sánh: A như B. Nhân hoá: vật mang đặc điểm của người (không cần vật B).', $d);
        $this->quiz($s, 'Câu "Trăng đã lên cao, trăng tròn vành vạnh." có phép nhân hoá không?', ['Có, vì gọi "trăng"', 'Không, chỉ là câu kể miêu tả', 'Có, vì trăng tròn', 'Không, vì thiếu từ "như"'], 1, 'Câu chỉ miêu tả trăng bằng từ ngữ thông thường, không gán đặc điểm người.', $d);
        $this->quiz($s, 'Tác dụng của phép nhân hoá trong bài "Đêm nay Bác không ngủ" là gì?', ['Làm Bác trở nên xa lạ', 'Làm thiên nhiên gần gũi, thể hiện tình cảm với Bác', 'Làm câu thơ dài hơn', 'Thay thế từ ngữ khó'], 1, 'Nhân hoá thiên nhiên (mưa, mái lá) làm cảnh vật có hồn, chan hòa tình cảm với con người.', $d);

        $this->matching($s, 'Nối mỗi câu với cách nhân hoá được sử dụng.', [['"Chị ong nâu nâu nâu."', 'Xưng hô (chị)'], ['"Hoa cười ngọc thốt đoan trang."', 'Gán hành động (cười)'], ['"Trăng buồn lặng lẽ."', 'Gán tâm trạng (buồn)'], ['"Hỡi ơi con sông quê!"', 'Trò chuyện, gọi đáp']], 'Bốn cách nhân hoá: xưng hô, hành động, tâm trạng và đối thoại với sự vật.', $d);
        $this->matching($s, 'Nối mỗi sự vật với hành động nhân hoá phù hợp.', [['mặt trời', 'mỉm cười'], ['dòng sông', 'thì thầm'], ['cơn gió', 'đùa nghịch'], ['ngọn núi', 'đứng trầm ngâm']], 'Chọn hành động của người phù hợp với đặc điểm của sự vật để nhân hoá tự nhiên.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với biện pháp tu từ của nó.', [['"Lá vàng rơi như mưa."', 'So sánh'], ['"Lá thì thầm cùng gió."', 'Nhân hoá'], ['"Sương giăng mờ ảo."', 'Không có tu từ'], ['"Nắng nhảy nhót trên cành."', 'Nhân hoá']], '"Nhảy nhót" là hành động của trẻ em gán cho nắng → nhân hoá.', $d);
        $this->matching($s, 'Nối mỗi chi tiết nhân hoá với tác dụng của nó.', [['"Ông mặt trời"', 'Gần gũi, thân thương'], ['"Chị gió thì thầm"', 'Sinh động, có hồn'], ['"Bác gấu đen"', 'Đáng yêu, ngộ nghĩnh'], ['"Mẹ thiên nhiên"', 'Thiêng liêng, trân trọng']], 'Nhân hoá không chỉ làm đẹp câu văn mà còn thể hiện tình cảm của người viết.', $d);
        $this->matching($s, 'Nối mỗi cách nói với đánh giá đúng/sai về nhân hoá.', [['"Cô trăng rằm"', 'Là nhân hoá (xưng hô)'], ['"Trăng tròn"', 'Không phải nhân hoá'], ['"Trăng mỉm cười"', 'Là nhân hoá (hành động)'], ['"Trăng sáng"', 'Không phải nhân hoá']], '"Tròn, sáng" là đặc điểm vốn có của trăng, không phải đặc điểm gán thêm của người.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm NHÂN HOÁ BẰNG TÂM TRẠNG hoặc NHÂN HOÁ BẰNG LỜI NÓI.', [['"Trăng buồn soi bóng."', 'NHÂN HOÁ BẰNG TÂM TRẠNG'], ['"Gió hỏi: bạn đi đâu?"', 'NHÂN HOÁ BẰNG LỜI NÓI'], ['"Sao nhớ ai mà lấp lánh?"', 'NHÂN HOÁ BẰNG TÂM TRẠNG'], ['"Sông hát: ơi quê hương!"', 'NHÂN HOÁ BẰNG LỜI NÓI']], 'Gán tâm trạng (buồn, nhớ) hoặc cho sự vật cất lời nói đều là nhân hoá.', $d);
        $this->sortQ($s, 'Kéo mỗi cách diễn đạt vào nhóm LÀ NHÂN HOÁ hoặc CHỈ LÀ CÂU KỂ.', [['"Mưa rơi tí tách."', 'CHỈ LÀ CÂU KỂ'], ['"Mưa nhảy múa."', 'LÀ NHÂN HOÁ'], ['"Nắng chiếu xuống sân."', 'CHỈ LÀ CÂU KỂ'], ['"Nắng đùa giỡn."', 'LÀ NHÂN HOÁ'], ['"Lá rụng đầy sân."', 'CHỈ LÀ CÂU KỂ'], ['"Lá xì xào trò chuyện."', 'LÀ NHÂN HOÁ']], 'Câu kể tả đúng bản chất sự vật; nhân hoá "khoác" thêm đặc điểm con người.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm NHÂN HOÁ TRONG THƠ THIẾU NHI hoặc TRONG CA DAO.', [['"Chú voi con thật ngoan."', 'TRONG THƠ THIẾU NHI'], ['"Trâu ơi ta bảo trâu này."', 'TRONG CA DAO'], ['"Bé gà con chíp chíp."', 'TRONG THƠ THIẾU NHI'], ['"Cò ơi, cò bay la."', 'TRONG CA DAO']], 'Cả thơ thiếu nhi và ca dao đều yêu thích phép nhân hoá vì gần gũi, dễ cảm.', $d);
        $this->sortQ($s, 'Kéo mỗi từ xưng hô vào nhóm DÙNG ĐỂ NHÂN HOÁ hoặc KHÔNG.', [['ông (ông mặt trời)', 'DÙNG ĐỂ NHÂN HOÁ'], ['cái (cái bàn)', 'KHÔNG'], ['chị (chị ong)', 'DÙNG ĐỂ NHÂN HOÁ'], ['con (con mèo)', 'KHÔNG']], '"Ông, chị, bác, cô" là từ xưng hô của người; "cái, con" là từ chỉ đơn vị sự vật.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm NHÂN HOÁ hoặc SO SÁNH.', [['"Bé mèo gừ gừ đòi ăn."', 'NHÂN HOÁ'], ['"Mắt mèo sáng như đèn."', 'SO SÁNH'], ['"Anh gà trống oai vệ."', 'NHÂN HOÁ'], ['"Lông trắng như tuyết."', 'SO SÁNH']], 'Có "như" → so sánh; xưng "bé, anh" + hành động người → nhân hoá.', $d);

        $this->fill($s, 'Gọi mặt trời là "ông", gọi gió là "chị" là nhân hoá bằng cách xưng ___.', [[0, 'hô']], 'Xưng hô như với con người là cách nhân hoá đơn giản, phổ biến nhất.', $d);
        $this->fill($s, 'Câu "Hoa ___ cùng bướm." Điền động từ nhân hoá chỉ hành động trò chuyện vui vẻ.', [[0, 'đùa vui']], '"Đùa vui" là hành động của con người, gán cho hoa tạo sự sinh động.', $d);
        $this->fill($s, 'Phép nhân hoá làm cho thế giới sự vật trở nên gần gũi, sinh động như thế giới con ___.', [[0, 'người']], 'Nhân hoá xóa nhòa ranh giới giữa người và vật, thể hiện tình yêu thiên nhiên.', $d);
        $this->fill($s, 'Trong câu "Nắng ___ trên sân.", điền động từ nhân hoá chỉ hành động vui đùa của nắng.', [[0, 'nhảy múa']], '"Nhảy múa" vốn là hành động của con người, gán cho nắng rất sinh động.', $d);
        $this->fill($s, 'Nhân hoá khác so sánh ở chỗ không cần đối chiếu hai sự vật mà ___ đặc điểm người cho vật.', [[0, 'gán']], 'Công thức nhớ: so sánh "A như B", nhân hoá "vật + đặc điểm người".', $d);
    }

    /* ============ nv-bien-phap-tu-tu-lop-8-1 (Ngữ văn 8, trung_binh) ============ */
    private function seedNvBienPhapTuTuLop81(): void
    {
        $s = 'nv-bien-phap-tu-tu-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Câu "Đầu tường lửa lựu lập lòe đơm bông" — "lửa lựu" là biện pháp gì?', ['So sánh', 'Ẩn dụ', 'Hoán dụ', 'Nhân hoá'], 1, '"Lửa lựu" gọi hoa lựu bằng tên "lửa" dựa trên nét tương đồng màu đỏ rực — ẩn dụ hình thức.', $d);
        $this->quiz($s, 'Câu "Thuyền về có nhớ bến chăng" — "thuyền", "bến" ẩn dụ cho ai?', ['Người đi xa và người ở lại', 'Con thuyền và bến đỗ thật', 'Người lái đò', 'Khách qua đường'], 0, 'Dựa trên quan hệ gắn bó thủy chung, "thuyền" ẩn dụ cho người ra đi, "bến" cho người ở lại.', $d);
        $this->quiz($s, 'Vì sao ẩn dụ được coi là "so sánh ngầm"?', ['Vì ẩn dụ luôn đi kèm từ "như"', 'Vì ẩn dụ lược bỏ từ so sánh, gọi thẳng tên sự vật', 'Vì ẩn dụ khó hiểu hơn so sánh', 'Vì ẩn dụ chỉ dùng trong văn xuôi'], 1, 'Ẩn dụ giữ nguyên cơ chế đối chiếu của so sánh nhưng lược bỏ từ so sánh và vế được so sánh.', $d);
        $this->quiz($s, 'Câu "Ăn quả nhớ kẻ trồng cây" sử dụng kiểu ẩn dụ nào?', ['Ẩn dụ hình thức', 'Ẩn dụ cách thức', 'Ẩn dụ phẩm chất', 'Ẩn dụ chuyển đổi cảm giác'], 1, '"Ăn quả – trồng cây" là cách thức lao động và hưởng thụ, khuyên nhớ ơn người có công — ẩn dụ cách thức.', $d);
        $this->quiz($s, 'Hình ảnh "mặt trời trong lăng" trong thơ Tố Hữu ẩn dụ cho ai?', ['Người chiến sĩ', 'Bác Hồ', 'Người nông dân', 'Thế hệ trẻ'], 1, 'Bác Hồ vĩ đại, tỏa sáng như mặt trời — ẩn dụ phẩm chất dựa trên nét tương đồng cao quý.', $d);

        $this->matching($s, 'Nối mỗi câu thơ với kiểu ẩn dụ của nó.', [['"Lửa lựu lập lòe" (hoa lựu như lửa)', 'Ẩn dụ hình thức'], ['"Ăn quả nhớ kẻ trồng cây"', 'Ẩn dụ cách thức'], ['"Mặt trời trong lăng" (chỉ Bác)', 'Ẩn dụ phẩm chất'], ['"Nắng ửng hồng trên má em"', 'Ẩn dụ chuyển đổi cảm giác']], 'Bốn kiểu ẩn dụ: hình thức (hình dáng), cách thức (cách làm), phẩm chất (đức tính), chuyển đổi cảm giác.', $d);
        $this->matching($s, 'Hãy nối mỗi hình ảnh ẩn dụ với ý nghĩa của nó.', [['"Thuyền – bến"', 'Tình cảm thủy chung, nhớ nhung'], ['"Lửa lựu"', 'Vẻ đẹp rực rỡ của hoa lựu'], ['"Người cha" (mái tóc bạc)', 'Tình cảm kính yêu Bác Hồ'], ['"Cây" đơn lẻ và "rừng cây"', 'Sức mạnh đoàn kết']], 'Giải mã ẩn dụ là tìm nét tương đồng giữa hình ảnh và điều được nói tới.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với biện pháp tu từ của nó.', [['"Bóng hồng nhác thấy nẻo xa."', 'Ẩn dụ'], ['"Trăng tròn như cái đĩa."', 'So sánh'], ['"Chị gió đùa vui."', 'Nhân hoá'], ['"Cả lớp cười ồ."', 'Hoán dụ']], '"Bóng hồng" chỉ Thúy Kiều dựa trên nét đẹp tương đồng — ẩn dụ; "cả lớp" là hoán dụ.', $d);
        $this->matching($s, 'Nối mỗi kiểu ẩn dụ với ví dụ đúng.', [['Ẩn dụ hình thức', '"Thuyền về có nhớ bến chăng"'], ['Ẩn dụ cách thức', '"Ăn quả nhớ kẻ trồng cây"'], ['Ẩn dụ phẩm chất', '"Người cha mái tóc bạc"'], ['Ẩn dụ chuyển đổi cảm giác', '"Tiếng suối trong như tiếng hát xa"']], '"Tiếng suối trong như tiếng hát" — cảm giác thính giác được diễn tả bằng hình ảnh trong trẻo của thị giác.', $d);
        $this->matching($s, 'Nối mỗi nhận định với đánh giá đúng/sai về ẩn dụ.', [['Ẩn dụ phải có từ "như"', 'Sai'], ['Ẩn dụ dựa trên nét tương đồng', 'Đúng'], ['"Sen" chỉ người quân tử là ẩn dụ', 'Đúng'], ['Ẩn dụ và so sánh hoàn toàn giống nhau', 'Sai']], 'Ẩn dụ khác so sánh ở chỗ lược bỏ từ so sánh; nhưng cùng dựa trên nét tương đồng.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm ẨN DỤ HÌNH THỨC hoặc ẨN DỤ CÁCH THỨC.', [['"Lửa lựu lập lòe đơm bông."', 'ẨN DỤ HÌNH THỨC'], ['"Uống nước nhớ nguồn."', 'ẨN DỤ CÁCH THỨC'], ['"Thuyền về có nhớ bến chăng?"', 'ẨN DỤ HÌNH THỨC'], ['"Ăn quả nhớ kẻ trồng cây."', 'ẨN DỤ CÁCH THỨC']], 'Hình thức: giống nhau về dáng vẻ. Cách thức: giống nhau về cách hành động, ứng xử.', $d);
        $this->sortQ($s, 'Kéo mỗi hình ảnh vào nhóm ẨN DỤ hoặc SO SÁNH.', [['"Người cha mái tóc bạc."', 'ẨN DỤ'], ['"Bác như người cha."', 'SO SÁNH'], ['"Mặt trời trong lăng."', 'ẨN DỤ'], ['"Bác vĩ đại như mặt trời."', 'SO SÁNH']], 'Có từ "như" → so sánh; gọi thẳng "người cha", "mặt trời" → ẩn dụ.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ ẨN DỤ PHẨM CHẤT hoặc KHÔNG.', [['"Sen gần bùn mà chẳng hôi tanh."', 'CÓ ẨN DỤ PHẨM CHẤT'], ['"Hoa sen nở trong đầm."', 'KHÔNG'], ['"Trúc dẫu cháy vẫn ngay thẳng."', 'CÓ ẨN DỤ PHẨM CHẤT'], ['"Tre mọc thành bụi."', 'KHÔNG']], 'Sen, trúc chỉ người quân tử thanh cao, ngay thẳng — ẩn dụ phẩm chất quen thuộc.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp hình ảnh vào nhóm CÓ QUAN HỆ TƯƠNG ĐỒNG hoặc KHÔNG.', [['mặt trời – Bác Hồ (tỏa sáng)', 'CÓ TƯƠNG ĐỒNG'], ['bàn tay – người lao động (bộ phận)', 'KHÔNG (gần gũi)'], ['thuyền – bến (gắn bó)', 'CÓ TƯƠNG ĐỒNG'], ['áo nâu – nông dân (dấu hiệu)', 'KHÔNG (gần gũi)']], 'Tương đồng → ẩn dụ; gần gũi (bộ phận, dấu hiệu) → hoán dụ.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm ẨN DỤ TRONG CA DAO hoặc TRONG THƠ HIỆN ĐẠI.', [['"Thân em như tấm lụa đào."', 'TRONG CA DAO'], ['"Ngày ngày mặt trời đi qua trên lăng."', 'TRONG THƠ HIỆN ĐẠI'], ['"Một cây làm chẳng nên non."', 'TRONG CA DAO'], ['"Người cha mái tóc bạc."', 'TRONG THƠ HIỆN ĐẠI']], 'Ẩn dụ xuất hiện trong mọi thời kì văn học, từ ca dao đến thơ hiện đại.', $d);

        $this->fill($s, 'Ẩn dụ "thuyền – bến" dựa trên nét tương đồng về tình cảm thủy ___, gắn bó.', [[0, 'chung']], '"Thuyền về có nhớ bến chăng" — nỗi nhớ của người ra đi với người ở lại.', $d);
        $this->fill($s, 'Câu "Về thăm quê Bác làng Sen" — "làng Sen" gợi nhớ Bác Hồ bằng quan hệ gần gũi nên là hoán ___, không phải ẩn dụ.', [[0, 'dụ']], 'Địa danh gắn liền với con người: lấy nơi chốn chỉ người là hoán dụ.', $d);
        $this->fill($s, 'Ẩn dụ chuyển đổi cảm giác: dùng cảm giác này để diễn tả cảm giác ___.', [[0, 'khác']], 'Ví dụ: "nắng giòn tan" — thị giác mà như nghe được, sờ được.', $d);
        $this->fill($s, '"Uống nước nhớ ___" là câu tục ngữ dùng ẩn dụ cách thức khuyên nhớ ơn người có công.', [[0, 'nguồn']], '"Uống nước" (hưởng thụ) — "nhớ nguồn" (nhớ người tạo ra), cách thức ứng xử đẹp.', $d);
        $this->fill($s, 'Muốn giải mã ẩn dụ, phải tìm ra nét ___ đồng giữa hình ảnh và sự vật được nói tới.', [[0, 'tương']], 'Không tìm được nét tương đồng thì chưa hiểu được ẩn dụ.', $d);
    }

    /* ============ nv-bien-phap-tu-tu-lop-8-2 (Ngữ văn 8, trung_binh) ============ */
    private function seedNvBienPhapTuTuLop82(): void
    {
        $s = 'nv-bien-phap-tu-tu-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Câu "Tay người vun trồng, tay người gặt hái." — "tay người" là biện pháp gì?', ['Ẩn dụ', 'Hoán dụ', 'So sánh', 'Điệp ngữ'], 1, 'Lấy bộ phận (tay) chỉ toàn thể (con người lao động) — quan hệ gần gũi nên là hoán dụ.', $d);
        $this->quiz($s, 'Câu "Vì lợi ích mười năm trồng cây, vì lợi ích trăm năm trồng người." — "trồng người" là biện pháp gì?', ['Hoán dụ', 'Ẩn dụ', 'Nói quá', 'Liệt kê'], 1, '"Trồng người" (giáo dục) tương đồng với "trồng cây" (vun xới) — ẩn dụ cách thức.', $d);
        $this->quiz($s, 'Hoán dụ "áo vải" trong câu "Áo vải đã đem lại vinh quang" chỉ ai?', ['Người thợ may', 'Người nông dân, quần chúng lao động', 'Người bán vải', 'Người lính'], 1, '"Áo vải" là dấu hiệu trang phục của người lao động nghèo — lấy dấu hiệu chỉ sự vật.', $d);
        $this->quiz($s, 'Điểm giống nhau giữa ẩn dụ và hoán dụ là gì?', ['Đều có từ "như"', 'Đều gọi tên sự vật này bằng tên sự vật khác', 'Đều dựa trên quan hệ gần gũi', 'Đều chỉ dùng trong thơ'], 1, 'Cả hai đều là cách gọi tên gián tiếp; khác nhau ở cơ sở: tương đồng hay gần gũi.', $d);
        $this->quiz($s, 'Câu "Một hòn đất ném đi, một hòn chì ném lại." sử dụng biện pháp gì?', ['Hoán dụ', 'Ẩn dụ', 'So sánh', 'Nhân hoá'], 1, '"Hòn đất, hòn chì" ẩn dụ cho hành động, cách ứng xử (nhẹ nhàng hay nặng nề) — tương đồng cách thức.', $d);

        $this->matching($s, 'Nối mỗi câu với kiểu hoán dụ của nó.', [['"Đầu xanh có tội tình gì?"', 'Lấy dấu hiệu chỉ sự vật'], ['"Nhà ấy giàu lắm."', 'Lấy vật chứa chỉ vật bị chứa'], ['"Chân cứng đá mềm."', 'Lấy bộ phận chỉ toàn thể'], ['"Miệng ăn núi lở."', 'Lấy bộ phận chỉ toàn thể']], '"Đầu xanh" (tóc đen) chỉ tuổi trẻ; "nhà" chỉ người trong nhà.', $d);
        $this->matching($s, 'Hãy nối mỗi hình ảnh hoán dụ với ý nghĩa của nó.', [['"Bàn tay"', 'Người lao động'], ['"Áo nâu"', 'Người nông dân'], ['"Mũ cối"', 'Người bộ đội'], ['"Khăn quàng đỏ"', 'Đội viên thiếu niên']], 'Dấu hiệu trang phục, đồ dùng đặc trưng thường được dùng làm hoán dụ chỉ con người.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với biện pháp tu từ của nó.', [['"Tay làm hàm nhai."', 'Hoán dụ'], ['"Lá lành đùm lá rách."', 'Ẩn dụ'], ['"Cả trường reo hò."', 'Hoán dụ'], ['"Gần mực thì đen."', 'Ẩn dụ']], '"Lá lành đùm lá rách": lá tương đồng với người — ẩn dụ; "gần mực thì đen" cũng là ẩn dụ.', $d);
        $this->matching($s, 'Nối mỗi quan hệ gần gũi với ví dụ hoán dụ.', [['Bộ phận – toàn thể', '"Bàn tay ta làm nên tất cả."'], ['Vật chứa – vật bị chứa', '"Cả lớp vỗ tay."'], ['Dấu hiệu – sự vật', '"Áo chàm đưa buổi phân li."'], ['Cụ thể – trừu tượng', '"Tay trắng làm nên."']], '"Tay trắng" (cụ thể) chỉ sự nghèo khó (trừu tượng) — hoán dụ ít gặp nhưng thú vị.', $d);
        $this->matching($s, 'Nối mỗi nhận định với phép tu từ đúng.', [['Dựa trên nét tương đồng', 'Ẩn dụ'], ['Dựa trên quan hệ gần gũi', 'Hoán dụ'], ['"Sen" chỉ người quân tử', 'Ẩn dụ'], ['"Tay" chỉ người lao động', 'Hoán dụ']], 'Sen ~ người quân tử (thanh cao): tương đồng. Tay là bộ phận của người: gần gũi.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm HOÁN DỤ LẤY BỘ PHẬN hoặc LẤY VẬT CHỨA.', [['"Bàn tay ta làm nên tất cả."', 'LẤY BỘ PHẬN'], ['"Cả nhà quây quần."', 'LẤY VẬT CHỨA'], ['"Chân lấm tay bùn."', 'LẤY BỘ PHẬN'], ['"Xóm làng rộn tiếng cười."', 'LẤY VẬT CHỨA'], ['"Mắt sáng, tay nhanh."', 'LẤY BỘ PHẬN'], ['"Trường em đạt giải nhất."', 'LẤY VẬT CHỨA']], '"Chân, tay, mắt" là bộ phận con người; "nhà, xóm, trường" là nơi chứa con người.', $d);
        $this->sortQ($s, 'Kéo mỗi hình ảnh vào nhóm CHỈ NGƯỜI LAO ĐỘNG hoặc CHỈ TẦNG LỚP KHÁC.', [['"Bàn tay chai sần"', 'CHỈ NGƯỜI LAO ĐỘNG'], ['"Áo nâu sồng"', 'CHỈ NGƯỜI LAO ĐỘNG'], ['"Mũ cối"', 'CHỈ TẦNG LỚP KHÁC'], ['"Chân lấm tay bùn"', 'CHỈ NGƯỜI LAO ĐỘNG']], '"Mũ cối" chỉ bộ đội — tầng lớp khác với nông dân "áo nâu sồng".', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ HOÁN DỤ hoặc CHỈ CÓ ẨN DỤ.', [['"Áo chàm về bản."', 'CÓ HOÁN DỤ'], ['"Thuyền về nhớ bến."', 'CHỈ CÓ ẨN DỤ'], ['"Cả xóm đi cấy."', 'CÓ HOÁN DỤ'], ['"Mặt trời trong lăng."', 'CHỈ CÓ ẨN DỤ']], '"Áo chàm, cả xóm" là gần gũi → hoán dụ; "thuyền–bến, mặt trời" là tương đồng → ẩn dụ.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp từ vào nhóm QUAN HỆ GẦN GŨI hoặc QUAN HỆ TƯƠNG ĐỒNG.', [['tay – người', 'GẦN GŨI'], ['sen – người quân tử', 'TƯƠNG ĐỒNG'], ['trường – học sinh', 'GẦN GŨI'], ['lửa – hoa lựu', 'TƯƠNG ĐỒNG']], 'Tay là một phần của người; sen giống người quân tử ở vẻ thanh cao.', $d);
        $this->sortQ($s, 'Kéo mỗi câu tục ngữ vào nhóm CÓ HOÁN DỤ hoặc KHÔNG.', [['"Tay làm hàm nhai."', 'CÓ HOÁN DỤ'], ['"Có công mài sắt."', 'KHÔNG'], ['"Miệng ăn núi lở."', 'CÓ HOÁN DỤ'], ['"Uống nước nhớ nguồn."', 'KHÔNG (ẩn dụ)']], '"Có công mài sắt" là nói về sự kiên trì (không phải hoán dụ); "uống nước nhớ nguồn" là ẩn dụ.', $d);

        $this->fill($s, 'Hoán dụ lấy cái cụ thể để chỉ cái trừu tượng: "tay trắng" chỉ sự nghèo ___.', [[0, 'khó']], '"Tay trắng" (hai bàn tay không) chỉ cảnh nghèo khó, không có của cải.', $d);
        $this->fill($s, '"Mắt sáng như sao" là so sánh, còn "đôi mắt ấy" chỉ cô gái là lấy bộ phận chỉ toàn thể — tức hoán ___.', [[0, 'dụ']], 'Cùng nói về mắt nhưng cơ chế khác nhau: tương đồng → so sánh, gần gũi → hoán dụ.', $d);
        $this->fill($s, 'Câu "Xóm ___ rộn ràng ngày mùa." Điền từ hoán dụ lấy nơi chốn chỉ người dân.', [[0, 'làng']], '"Xóm làng" là nơi ở, dùng để chỉ người dân trong xóm làng — hoán dụ vật chứa.', $d);
        $this->fill($s, 'Hoán dụ và ẩn dụ đều gọi tên gián tiếp, nhưng hoán dụ dựa trên quan hệ gần ___.', [[0, 'gũi']], 'Gần gũi: bộ phận–toàn thể, vật chứa–vật bị chứa, dấu hiệu–sự vật.', $d);
        $this->fill($s, '"Khăn quàng đỏ tung bay" — "khăn quàng đỏ" là dấu hiệu chỉ đội viên, thuộc kiểu hoán dụ lấy dấu ___ chỉ sự vật.', [[0, 'hiệu']], 'Khăn quàng đỏ là dấu hiệu đặc trưng của đội viên thiếu niên tiền phong.', $d);
    }

    /* ============ nv-van-mieu-ta-lop-8-1 (Ngữ văn 8, trung_binh) ============ */
    private function seedNvVanMieuTaLop81(): void
    {
        $s = 'nv-van-mieu-ta-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Khi tả cảnh buổi bình minh, chi tiết nào nên được tả đầu tiên?', ['Tiếng chim hót líu lo', 'Bầu trời ửng hồng phía đông', 'Hương lúa chín thơm', 'Cảm xúc của người viết'], 1, 'Tả cảnh thường từ bao quát đến chi tiết: bầu trời chung trước, chi tiết cụ thể sau.', $d);
        $this->quiz($s, 'Câu văn nào tả âm thanh của buổi sáng quê hương hay nhất?', ['Buổi sáng rất yên tĩnh.', 'Tiếng gà gáy vang, chim hót líu lo chào ngày mới.', 'Sáng nay trời đẹp.', 'Mọi người thức dậy.'], 1, 'Câu có từ ngữ gợi thanh cụ thể ("gáy vang", "hót líu lo") sinh động hơn câu chung chung.', $d);
        $this->quiz($s, 'Trong văn miêu tả, so sánh "Sương giăng như tấm voan mỏng" có tác dụng gì?', ['Làm câu văn dài hơn', 'Làm hình ảnh sương cụ thể, mềm mại, dễ hình dung', 'Thay thế từ "sương"', 'Làm câu văn khó hiểu'], 1, 'So sánh biến làn sương trừu tượng thành "tấm voan mỏng" cụ thể, gợi cảm.', $d);
        $this->quiz($s, 'Khi tả cảnh, yếu tố nào giúp bài văn không bị khô khan?', ['Liệt kê thật nhiều chi tiết', 'Lồng cảm xúc của người viết vào cảnh vật', 'Chỉ tả bằng mắt nhìn', 'Tránh dùng biện pháp tu từ'], 1, 'Cảm xúc là "linh hồn": cảnh vật qua lăng kính tình cảm sẽ có hồn, sâu sắc.', $d);
        $this->quiz($s, 'Trình tự nào hợp lí khi tả một khu vườn?', ['Tả chi tiết từng lá cây trước', 'Từ cổng vườn vào sâu bên trong, từ bao quát đến chi tiết', 'Tả lung tung theo ý thích', 'Chỉ tả những cây to'], 1, 'Trình tự không gian (ngoài–trong, xa–gần) giúp người đọc hình dung như đang dạo bước.', $d);

        $this->matching($s, 'Nối mỗi thời điểm với nét đặc trưng khi tả.', [['Buổi sáng', 'Sương giăng, chim hót, nắng nhẹ'], ['Buổi trưa', 'Nắng gắt, ve kêu râm ran'], ['Buổi chiều', 'Nắng nhạt, mây hồng, gió mát'], ['Buổi tối', 'Trăng lên, sao lấp lánh, yên tĩnh']], 'Mỗi thời điểm có "chân dung" riêng; nắm được sẽ tả cảnh đúng và giàu chi tiết.', $d);
        $this->matching($s, 'Nối mỗi giác quan với chi tiết quan sát được khi tả cánh đồng.', [['Mắt', 'Lúa chín vàng óng'], ['Tai', 'Tiếng sáo diều vi vu'], ['Mũi', 'Hương lúa thơm ngát'], ['Da', 'Gió mát rượi']], 'Tả cánh đồng bằng cả bốn giác quan sẽ đầy đặn, sống động.', $d);
        $this->matching($s, 'Nối mỗi câu văn với biện pháp tu từ được dùng.', [['"Sương giăng như tấm voan."', 'So sánh'], ['"Hàng cây hát ca."', 'Nhân hoá'], ['"Nắng vàng rực rỡ."', 'Không có tu từ'], ['"Gió thì thầm."', 'Nhân hoá']], '"Nắng vàng rực rỡ" chỉ dùng tính từ miêu tả, chưa gán đặc điểm người hay đối chiếu.', $d);
        $this->matching($s, 'Nối mỗi lỗi khi tả cảnh với cách khắc phục.', [['Tả chung chung, sáo rỗng', 'Chọn chi tiết tiêu biểu, cụ thể'], ['Thiếu trình tự', 'Sắp xếp theo không gian hoặc thời gian'], ['Thiếu cảm xúc', 'Lồng tình cảm vào từng chi tiết'], ['Lặp từ nhiều lần', 'Dùng từ đồng nghĩa, đa dạng diễn đạt']], 'Bốn lỗi kinh điển của văn tả cảnh và cách chữa tương ứng.', $d);
        $this->matching($s, 'Nối mỗi đối tượng với trọng tâm khi tả.', [['Cây cổ thụ', 'Hình dáng, tán lá, rễ cây'], ['Dòng sông', 'Mặt nước, dòng chảy, hai bờ'], ['Ngọn núi', 'Độ cao, dáng núi, mây phủ'], ['Cánh đồng', 'Màu sắc, hương thơm, âm thanh']], 'Mỗi đối tượng có những nét riêng cần ưu tiên, không tả dàn trải như nhau.', $d);

        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm TẢ MÙA HẠ hoặc TẢ MÙA THU.', [['Ve kêu râm ran', 'TẢ MÙA HẠ'], ['Lá vàng rơi', 'TẢ MÙA THU'], ['Nắng gắt chói chang', 'TẢ MÙA HẠ'], ['Heo may se lạnh', 'TẢ MÙA THU'], ['Phượng nở đỏ rực', 'TẢ MÙA HẠ'], ['Cúc vàng nở rộ', 'TẢ MÙA THU']], 'Mỗi mùa có "đặc sản" riêng: hè – ve, phượng; thu – lá vàng, heo may.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ HÌNH ẢNH SO SÁNH hoặc KHÔNG.', [['"Mây trắng như bông."', 'CÓ HÌNH ẢNH SO SÁNH'], ['"Trời hôm nay nhiều mây."', 'KHÔNG'], ['"Sông như dải lụa."', 'CÓ HÌNH ẢNH SO SÁNH'], ['"Chiều nay gió to."', 'KHÔNG']], 'Câu có "như" đối chiếu hai sự vật mới là câu có hình ảnh so sánh.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm TẢ BẰNG THỊ GIÁC hoặc TẢ BẰNG THÍNH GIÁC.', [['Nắng vàng óng', 'TẢ BẰNG THỊ GIÁC'], ['Suối róc rách', 'TẢ BẰNG THÍNH GIÁC'], ['Mây trắng bồng bềnh', 'TẢ BẰNG THỊ GIÁC'], ['Chim hót líu lo', 'TẢ BẰNG THÍNH GIÁC']], 'Thị giác thu hình ảnh, thính giác thu âm thanh — hai giác quan chủ lực của văn tả cảnh.', $d);
        $this->sortQ($s, 'Kéo mỗi cách mở bài vào nhóm MỞ BÀI HAY hoặc MỞ BÀI NHẠT khi tả cảnh.', [['"Mỗi sớm mai, quê hương em đẹp như tranh vẽ."', 'MỞ BÀI HAY'], ['"Hôm nay em tả cảnh quê em."', 'MỞ BÀI NHẠT'], ['"Tiếng sáo diều vi vu gọi em về với đồng quê."', 'MỞ BÀI HAY'], ['"Em sẽ tả cảnh cánh đồng."', 'MỞ BÀI NHẠT']], 'Mở bài hay gợi hình, gợi cảm ngay; mở bài nhạt chỉ thông báo khô khan.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm TẢ TĨNH hoặc TẢ ĐỘNG.', [['"Mặt hồ phẳng lặng."', 'TẢ TĨNH'], ['"Sóng vỗ rì rào."', 'TẢ ĐỘNG'], ['"Núi đứng sừng sững."', 'TẢ TĨNH'], ['"Lá reo vui trong gió."', 'TẢ ĐỘNG']], 'Kết hợp tả tĩnh (dáng vẻ) và tả động (chuyển động) cảnh vật sẽ sống động.', $d);

        $this->fill($s, 'Tả cảnh buổi sáng nên bắt đầu từ khung cảnh ___ quát rồi mới đi vào chi tiết.', [[0, 'bao']], 'Trình tự bao quát – chi tiết giúp người đọc định hình không gian rồi mới thưởng thức nét đẹp riêng.', $d);
        $this->fill($s, 'Câu "Ve kêu râm ran" gợi tả âm thanh đặc trưng của mùa ___.', [[0, 'hạ']], 'Tiếng ve là "đặc sản" âm thanh của mùa hạ, gắn với hoa phượng, nắng gắt.', $d);
        $this->fill($s, 'Khi tả cảnh, ngoài quan sát bằng mắt còn cần lắng nghe bằng tai và cảm nhận bằng trái ___.', [[0, 'tim']], 'Cảm xúc chân thành là thứ làm cho cảnh vật trong văn có hồn.', $d);
        $this->fill($s, '"Dòng sông ___ lượn như dải lụa." Điền động từ còn thiếu chỉ dáng chảy mềm mại.', [[0, 'uốn']], '"Uốn lượn" gợi tả dòng sông mềm mại, quanh co — từ ngữ đắt giá của văn tả cảnh.', $d);
        $this->fill($s, 'Từ láy "lấp ___" thường dùng để tả ánh trăng, ánh sao nhấp nháy.', [[0, 'lánh']], '"Lấp lánh" là từ láy gợi hình ánh sáng đẹp, rất hợp tả đêm trăng.', $d);
    }

    /* ============ nv-van-mieu-ta-lop-8-2 (Ngữ văn 8, trung_binh) ============ */
    private function seedNvVanMieuTaLop82(): void
    {
        $s = 'nv-van-mieu-ta-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Dấu hai chấm trong câu "Mùa thu có nhiều đặc sản: cốm, hồng, bưởi." có tác dụng gì?', ['Báo hiệu lời nói', 'Báo hiệu phần liệt kê, giải thích', 'Ngăn cách hai vế câu', 'Nhấn mạnh cảm xúc'], 1, 'Dấu hai chấm đứng trước phần liệt kê cụ thể hóa ý "đặc sản" nêu trước đó.', $d);
        $this->quiz($s, 'Câu nào dùng dấu phẩy ĐÚNG?', ['"Mẹ, đi chợ về."', '"Mùa xuân, cây cối đâm chồi nảy lộc."', '"Em, học bài đi."', '"Bạn, Nam đến rồi."'], 1, '"Mùa xuân" là trạng ngữ chỉ thời gian, ngăn cách với nòng cốt câu bằng dấu phẩy.', $d);
        $this->quiz($s, 'Dấu ngoặc kép trong câu "Bài thơ "Sang thu" rất hay." có tác dụng gì?', ['Trích dẫn lời nói', 'Đánh dấu tên tác phẩm', 'Nhấn mạnh từ ngữ', 'Chú thích thêm'], 1, 'Tên tác phẩm, tờ báo được đặt trong ngoặc kép theo quy tắc chính tả.', $d);
        $this->quiz($s, 'Khi viết đoạn đối thoại, mỗi lượt lời nhân vật được đánh dấu bằng gì?', ['Dấu phẩy đầu dòng', 'Dấu gạch ngang đầu dòng', 'Dấu chấm than', 'Dấu hai chấm cuối dòng'], 1, 'Quy tắc: xuống dòng, gạch ngang (–) rồi mới viết lời nhân vật.', $d);
        $this->quiz($s, 'Câu "Bạn có mang ô không – trời sắp mưa đấy!" nên sửa dấu gạch ngang thành dấu gì?', ['Dấu phẩy', 'Dấu chấm', 'Dấu hai chấm', 'Giữ nguyên'], 0, '"Trời sắp mưa đấy" là bộ phận giải thích thêm, nên ngăn cách bằng dấu phẩy.', $d);

        $this->matching($s, 'Hãy nối mỗi dấu câu với ví dụ sử dụng đúng.', [['Dấu phẩy', '"Mùa hè, ve kêu râm ran."'], ['Dấu hai chấm', '"Có ba màu: đỏ, vàng, xanh."'], ['Dấu ngoặc kép', 'Tên bài thơ "Quê hương".'], ['Dấu gạch ngang', '– Cháu chào bác ạ!']], 'Học dấu câu qua ví dụ cụ thể sẽ nhớ lâu và dùng đúng hơn học lí thuyết suông.', $d);
        $this->matching($s, 'Hãy nối mỗi lỗi dùng dấu câu với cách sửa.', [['"Mẹ đi, chợ." (thừa dấu)', 'Bỏ dấu phẩy sai'], ['"Bạn khỏe không." (thiếu dấu hỏi)', 'Thêm dấu chấm hỏi'], ['""Đẹp quá"" (nhân đôi ngoặc)', 'Chỉ dùng một cặp ngoặc kép'], ['"Ôi! đẹp quá." (sai vị trí)', 'Đặt dấu than cuối câu cảm']], 'Lỗi dấu câu thường do vội vàng; đọc lại câu một lần sẽ phát hiện ngay.', $d);
        $this->matching($s, 'Nối mỗi câu với dấu câu còn thiếu ở cuối.', [['"Em thích ăn kem"', 'Dấu chấm (.)'], ['"Mấy giờ rồi"', 'Dấu chấm hỏi (?)'], ['"Cố lên"', 'Dấu chấm than (!)'], ['"Chúc bạn sinh nhật vui vẻ"', 'Dấu chấm than (!)']], 'Câu chúc, câu động viên mang sắc thái cảm xúc nên dùng dấu chấm than.', $d);
        $this->matching($s, 'Nối mỗi thành phần câu với dấu câu thường đi kèm.', [['Trạng ngữ đứng đầu câu', 'Dấu phẩy'], ['Lời nói trực tiếp', 'Dấu hai chấm + ngoặc kép'], ['Bộ phận liệt kê', 'Dấu phẩy'], ['Phần chú thích', 'Dấu ngoặc đơn']], 'Mỗi thành phần có "người bạn dấu câu" riêng, nhớ cặp đôi sẽ ít sai.', $d);
        $this->matching($s, 'Nối mỗi câu với kiểu câu của nó.', [['"Trời sắp mưa."', 'Câu kể'], ['"Bạn đi đâu đấy?"', 'Câu hỏi'], ['"Đẹp quá!"', 'Câu cảm'], ['"Hãy cố gắng lên!"', 'Câu cầu khiến']], 'Xác định đúng kiểu câu là bước đầu để đặt dấu câu đúng.', $d);

        $this->sortQ($s, 'Kéo mỗi dấu vào nhóm DẤU NGĂN CÁCH BỘ PHẬN hoặc DẤU BÁO HIỆU.', [[',', 'DẤU NGĂN CÁCH BỘ PHẬN'], [':', 'DẤU BÁO HIỆU'], [';', 'DẤU NGĂN CÁCH BỘ PHẬN'], ['"', 'DẤU BÁO HIỆU']], 'Dấu hai chấm và ngoặc kép "báo hiệu" có nội dung đặc biệt phía sau (liệt kê, lời dẫn).', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm ĐẶT DẤU PHẨY ĐÚNG hoặc SAI.', [['"Sáng nay, em đi học sớm."', 'ĐÚNG'], ['"Em, đi học sớm."', 'SAI'], ['"Hoa hồng, hoa cúc đua nở."', 'ĐÚNG'], ['"Mẹ nấu, cơm ngon."', 'SAI']], '"Em đi học" không tách chủ – vị; "mẹ nấu cơm" cũng vậy — đây là lỗi rất phổ biến.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm NÊN DÙNG DẤU CHẤM PHẨY hoặc DẤU PHẨY.', [['Ngăn cách hai vế câu ghép dài', 'DẤU CHẤM PHẨY'], ['Ngăn cách các từ liệt kê', 'DẤU PHẨY'], ['Ngăn cách vế đã có dấu phẩy bên trong', 'DẤU CHẤM PHẨY'], ['Ngăn cách trạng ngữ với câu', 'DẤU PHẨY']], 'Khi vế câu đã phức tạp (có dấu phẩy bên trong), dùng chấm phẩy để phân định rõ ràng.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU TRỰC TIẾP hoặc CÂU GIÁN TIẾP.', [['Cô nói: "Mở sách ra."', 'CÂU TRỰC TIẾP'], ['Cô bảo mở sách ra.', 'CÂU GIÁN TIẾP'], ['Bạn hỏi: "Mấy giờ?"', 'CÂU TRỰC TIẾP'], ['Bạn hỏi mấy giờ.', 'CÂU GIÁN TIẾP']], 'Câu trực tiếp giữ nguyên lời nói (có ngoặc kép); gián tiếp thuật lại ý.', $d);
        $this->sortQ($s, 'Kéo mỗi câu (chưa có dấu cuối) vào nhóm CẦN DẤU CHẤM HỎI hoặc DẤU CHẤM THAN.', [['Bạn có khỏe không', 'CẦN DẤU CHẤM HỎI'], ['Tuyệt vời quá', 'CẦN DẤU CHẤM THAN'], ['Ai đã làm việc này', 'CẦN DẤU CHẤM HỎI'], ['Hoan hô đội ta thắng', 'CẦN DẤU CHẤM THAN']], 'Câu hỏi có từ nghi vấn (có... không, ai); câu cảm có thán từ, từ cảm thán.', $d);

        $this->fill($s, 'Dấu ___ chấm dùng để ngăn cách các vế câu ghép có cấu tạo phức tạp.', [[0, 'chấm']], 'Dấu chấm phẩy (;) "nặng" hơn dấu phẩy, "nhẹ" hơn dấu chấm — dùng cho vế câu phức tạp.', $d);
        $this->fill($s, 'Tên tác phẩm như "Truyện Kiều" được đặt trong dấu ngoặc ___.', [[0, 'kép']], 'Quy tắc chính tả: tên sách, báo, bài hát đặt trong ngoặc kép.', $d);
        $this->fill($s, 'Khi viết lời đối thoại, xuống dòng và đặt dấu gạch ___ trước lời nhân vật.', [[0, 'ngang']], 'Ví dụ: – Cháu chào bác ạ! / – Chào cháu!', $d);
        $this->fill($s, 'Dấu hai chấm đứng trước phần liệt kê, giải thích hoặc lời dẫn ___ tiếp.', [[0, 'trực']], 'Ba công dụng chính của dấu hai chấm: liệt kê, giải thích, dẫn lời trực tiếp.', $d);
        $this->fill($s, 'Câu cảm thán và câu cầu khiến mạnh đều kết thúc bằng dấu chấm ___.', [[0, 'than']], 'Dấu chấm than (!) thể hiện cảm xúc mạnh hoặc mệnh lệnh dứt khoát.', $d);
    }

    /* ============ nv-van-mieu-ta-lop-9-1 (Ngữ văn 9, trung_binh) ============ */
    private function seedNvVanMieuTaLop91(): void
    {
        $s = 'nv-van-mieu-ta-lop-9-1'; $d = 'trung_binh';
        $this->quiz($s, 'Khi tả chân dung nhân vật, chi tiết nào quan trọng nhất?', ['Chiều cao, cân nặng chính xác', 'Nét riêng nổi bật gợi tính cách', 'Màu sắc quần áo', 'Số đo các bộ phận'], 1, 'Chân dung văn học cần nét riêng gợi tính cách, không phải bản mô tả kĩ thuật.', $d);
        $this->quiz($s, 'Khi tả hoạt động của nhân vật, nên chú ý điều gì?', ['Kể lại mọi hành động từ sáng đến tối', 'Chọn hoạt động tiêu biểu bộc lộ tính cách', 'Chỉ tả hoạt động vui chơi', 'Tránh tả cử chỉ, điệu bộ'], 1, 'Hoạt động tiêu biểu (một việc làm, một cử chỉ) nói lên tính cách hơn trăm lời kể.', $d);
        $this->quiz($s, 'Câu văn nào tả tính cách nhân vật qua hành động?', ['Bạn ấy cao 1m60.', 'Bạn ấy luôn nhường nhịn em nhỏ.', 'Bạn ấy mặc áo xanh.', 'Bạn ấy có mái tóc dài.'], 1, '"Nhường nhịn em nhỏ" là hành động bộc lộ tính cách nhân hậu, không phải đặc điểm ngoại hình.', $d);
        $this->quiz($s, 'Trong bài văn tả người, có nên xen kể chuyện không?', ['Không, chỉ tả thôi', 'Có, xen kể chuyện ngắn làm nổi bật tính cách', 'Chỉ kể, không tả', 'Tùy thích, không quan trọng'], 1, 'Tả kết hợp kể: một kỉ niệm nhỏ giúp tính cách nhân vật hiện lên chân thực.', $d);
        $this->quiz($s, 'Lỗi "tả người như chụp ảnh chứng minh thư" nghĩa là gì?', ['Tả quá đẹp', 'Tả chung chung, liệt kê, thiếu nét riêng', 'Tả quá ngắn', 'Tả sai sự thật'], 1, 'Liệt kê máy móc (mắt, mũi, miệng...) mà không có chi tiết đắt giá, thiếu cá tính.', $d);

        $this->matching($s, 'Nối mỗi nội dung với phần phù hợp trong bài văn tả người.', [['Giới thiệu nhân vật', 'Mở bài'], ['Tả ngoại hình, tính cách', 'Thân bài'], ['Kể kỉ niệm đáng nhớ', 'Thân bài'], ['Nêu tình cảm của mình', 'Kết bài']], 'Bố cục tả người cũng ba phần, nhưng thân bài chia thành ngoại hình – tính cách – kỉ niệm.', $d);
        $this->matching($s, 'Nối mỗi bộ phận ngoại hình với từ ngữ miêu tả phù hợp.', [['Đôi mắt', 'long lanh, hiền từ'], ['Mái tóc', 'đen nhánh, óng ả'], ['Nụ cười', 'tươi tắn, rạng rỡ'], ['Dáng đi', 'nhanh nhẹn, khoan thai']], 'Mỗi bộ phận có vốn từ miêu tả riêng; dùng đúng từ làm câu văn đắt giá.', $d);
        $this->matching($s, 'Nối mỗi tính cách với biểu hiện qua hành động.', [['Nhân hậu', 'giúp đỡ người khó khăn'], ['Chăm chỉ', 'dậy sớm học bài'], ['Dũng cảm', 'cứu bạn gặp nạn'], ['Khiêm tốn', 'không khoe khoang']], 'Tính cách trừu tượng phải được "dịch" thành hành động cụ thể mới thuyết phục.', $d);
        $this->matching($s, 'Nối mỗi câu văn với đối tượng được tả.', [['"Mái tóc bà bạc phơ."', 'Người già'], ['"Đôi mắt em long lanh."', 'Trẻ em'], ['"Dáng thầy cao gầy."', 'Thầy giáo'], ['"Bàn tay mẹ chai sần."', 'Người mẹ']], 'Chi tiết ngoại hình gắn với lứa tuổi, nghề nghiệp — tả đúng sẽ rất chân thực.', $d);
        $this->matching($s, 'Nối mỗi cách tả với đánh giá.', [['Tả nét riêng nổi bật', 'Hay, có cá tính'], ['Liệt kê chung chung', 'Nhạt, thiếu ấn tượng'], ['Kết hợp tả và kể', 'Sinh động, thuyết phục'], ['Tả một chiều, phiến diện', 'Chưa sâu sắc']], 'Văn tả người hay khi có nét riêng + kể chuyện + cảm xúc chân thành.', $d);

        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm TẢ NGOẠI HÌNH hoặc TẢ TÍNH CÁCH.', [['"Mái tóc đen nhánh."', 'TẢ NGOẠI HÌNH'], ['"Luôn giúp đỡ bạn bè."', 'TẢ TÍNH CÁCH'], ['"Đôi mắt sáng."', 'TẢ NGOẠI HÌNH'], ['"Kiên trì vượt khó."', 'TẢ TÍNH CÁCH'], ['"Dáng người cao ráo."', 'TẢ NGOẠI HÌNH'], ['"Thật thà, ngay thẳng."', 'TẢ TÍNH CÁCH']], 'Ngoại hình: cái nhìn thấy. Tính cách: cái thể hiện qua hành động, lời nói.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CHI TIẾT ĐẮT GIÁ hoặc CHI TIẾT NHẠT.', [['"Bàn tay mẹ chai sần vì lam lũ."', 'CHI TIẾT ĐẮT GIÁ'], ['"Mẹ có hai mắt, một mũi."', 'CHI TIẾT NHẠT'], ['"Nụ cười của bà hiền như nắng sớm."', 'CHI TIẾT ĐẮT GIÁ'], ['"Bà cao khoảng 1m55."', 'CHI TIẾT NHẠT']], 'Chi tiết đắt giá gợi được số phận, tính cách; chi tiết nhạt chỉ là liệt kê.', $d);
        $this->sortQ($s, 'Kéo mỗi nội dung vào nhóm NÊN KỂ XEN hoặc KHÔNG NÊN khi tả người.', [['Kỉ niệm làm nổi bật tính cách', 'NÊN KỂ XEN'], ['Chuyện không liên quan', 'KHÔNG NÊN'], ['Việc làm đáng nhớ của nhân vật', 'NÊN KỂ XEN'], ['Chuyện của người khác', 'KHÔNG NÊN']], 'Chỉ xen kể chuyện phục vụ việc khắc họa nhân vật, tránh lan man.', $d);
        $this->sortQ($s, 'Kéo mỗi đoạn văn vào nhóm MỞ BÀI TRỰC TIẾP hoặc GIÁN TIẾP (bài tả mẹ).', [['"Mẹ em là người phụ nữ tuyệt vời."', 'TRỰC TIẾP'], ['"Mỗi lần nghe tiếng rao đêm, em nhớ mẹ."', 'GIÁN TIẾP'], ['"Em yêu mẹ em nhất trên đời."', 'TRỰC TIẾP'], ['"Mùi hương bồ kết gợi nhớ mái tóc mẹ."', 'GIÁN TIẾP']], 'Mở bài gián tiếp đi từ kỉ niệm, cảm xúc rồi mới giới thiệu nhân vật.', $d);
        $this->sortQ($s, 'Kéo mỗi từ ngữ vào nhóm TẢ NGƯỜI GIÀ hoặc TẢ TRẺ EM.', [['tóc bạc phơ', 'TẢ NGƯỜI GIÀ'], ['má phúng phính', 'TẢ TRẺ EM'], ['lưng còng', 'TẢ NGƯỜI GIÀ'], ['mắt tròn xoe', 'TẢ TRẺ EM']], 'Từ ngữ miêu tả phải phù hợp lứa tuổi mới chân thực, tránh "râu ông nọ cắm cằm bà kia".', $d);

        $this->fill($s, 'Tả người cần làm nổi bật ngoại hình tiêu biểu và nét tính ___ đặc trưng.', [[0, 'cách']], 'Ngoại hình là "vỏ", tính cách là "hồn" — bài văn tả người cần cả hai.', $d);
        $this->fill($s, 'Một kỉ niệm nhỏ được kể xen sẽ làm cho nhân vật thêm chân thực, sinh ___.', [[0, 'động']], 'Chuyện kể là "bằng chứng sống" cho những lời nhận xét về tính cách.', $d);
        $this->fill($s, 'Tránh tả người theo kiểu liệt kê máy móc từ đầu đến chân mà thiếu chi tiết ___ biểu.', [[0, 'tiêu']], 'Một chi tiết tiêu biểu (bàn tay chai sần) đáng giá hơn mười chi tiết chung chung.', $d);
        $this->fill($s, 'Khi tả tính cách, nên thể hiện qua hành động, lời nói cụ ___ của nhân vật.', [[0, 'thể']], '"Nói có sách, mách có chứng": tính cách phải được minh họa bằng việc làm cụ thể.', $d);
        $this->fill($s, 'Phần kết bài văn tả người thường bày tỏ tình cảm, lòng ___ trọng của người viết.', [[0, 'kính']], 'Kết bài là nơi gửi gắm tình cảm: yêu thương, kính trọng, biết ơn nhân vật.', $d);
    }

    /* ============ nv-van-mieu-ta-lop-9-2 (Ngữ văn 9, kho) ============ */
    private function seedNvVanMieuTaLop92(): void
    {
        $s = 'nv-van-mieu-ta-lop-9-2'; $d = 'kho';
        $this->quiz($s, 'Khi tả con vật, chi tiết nào tạo nên "cá tính" riêng cho con vật?', ['Màu lông chung chung', 'Thói quen, điệu bộ đặc trưng', 'Số lượng chân', 'Nơi ở'], 1, 'Thói quen, điệu bộ (mèo lười nằm sưởi nắng) làm con vật có "tính cách" riêng.', $d);
        $this->quiz($s, 'Khi tả đồ vật, ngoài hình dáng cần tả thêm yếu tố nào để bài văn sâu sắc?', ['Giá tiền chính xác', 'Công dụng và kỉ niệm gắn với đồ vật', 'Nơi sản xuất', 'Màu sắc bao bì'], 1, 'Đồ vật gắn với kỉ niệm, công dụng sẽ có "hồn", gợi cảm xúc cho người đọc.', $d);
        $this->quiz($s, 'Câu văn nào tả cây cối có hồn nhất?', ['Cây cao 5 mét.', 'Cây bàng xòe tán như chiếc ô khổng lồ che mát sân trường.', 'Trong sân có cây bàng.', 'Cây bàng thuộc họ Bàng.'], 1, 'So sánh "như chiếc ô khổng lồ" + công dụng "che mát" làm cây bàng sống động.', $d);
        $this->quiz($s, 'Trong văn tả cảnh lớp 9, "điểm nhìn" có vai trò gì?', ['Không quan trọng', 'Quyết định trình tự và cách tổ chức chi tiết miêu tả', 'Chỉ để trang trí', 'Thay thế cho bố cục'], 1, 'Điểm nhìn (đứng ở đâu, nhìn theo hướng nào) chi phối toàn bộ cách tả cảnh.', $d);
        $this->quiz($s, 'Vì sao khi tả cảnh cần tránh "tả như bưu thiếp"?', ['Vì bưu thiếp đẹp quá', 'Vì chỉ liệt kê cảnh đẹp chung chung, thiếu cảm xúc và nét riêng', 'Vì không được dùng ảnh', 'Vì bưu thiếp không có chữ'], 1, '"Tả như bưu thiếp" là lối tả sáo rỗng: đẹp mà vô hồn, thiếu dấu ấn cá nhân.', $d);

        $this->matching($s, 'Nối mỗi đối tượng với điểm cần chú ý khi tả.', [['Con mèo', 'Bộ lông, đôi mắt, thói quen'], ['Chiếc cặp sách', 'Hình dáng, ngăn cặp, kỉ niệm'], ['Cây phượng', 'Thân, tán lá, hoa, gắn với mùa hè'], ['Cơn mưa rào', 'Mây đen, tiếng sấm, hạt mưa']], 'Mỗi đối tượng có "bộ chi tiết đặc trưng" riêng cần nắm trước khi viết.', $d);
        $this->matching($s, 'Nối mỗi trình tự với đối tượng tả phù hợp.', [['Từ ngoài vào trong', 'Tả đồ vật (chiếc cặp)'], ['Từ trên xuống dưới', 'Tả cây cối'], ['Từ bao quát đến chi tiết', 'Tả con vật'], ['Theo diễn biến', 'Tả cơn mưa']], 'Đồ vật: ngoài–trong; cây: gốc–ngọn; hiện tượng: diễn biến trước–trong–sau.', $d);
        $this->matching($s, 'Nối mỗi biện pháp tu từ với ví dụ trong văn tả con vật.', [['So sánh', '"Lông trắng như bông."'], ['Nhân hoá', '"Chú mèo lười biếng nằm sưởi nắng."'], ['Từ láy', '"Đôi mắt tròn xoe."'], ['Điệp ngữ', '"Meo meo, meo meo gọi bạn."']], 'Văn tả con vật rất hợp với nhân hoá — con vật như có tính cách con người.', $d);
        $this->matching($s, 'Nối mỗi câu văn với nhận xét về chất lượng.', [['"Cây bàng như chiếc ô khổng lồ."', 'Hay: so sánh đắt giá'], ['"Cây bàng to."', 'Nhạt: chung chung'], ['"Mưa như trút nước."', 'Hay: gợi tả mạnh'], ['"Trời mưa to."', 'Nhạt: đơn điệu']], 'Câu văn hay có hình ảnh, so sánh, từ ngữ đắt; câu nhạt chỉ thông báo sự việc.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với vai trò trong bài văn tả cảnh sâu sắc.', [['Chi tiết tiêu biểu', 'Tạo ấn tượng đậm nét'], ['Cảm xúc người viết', 'Làm bài văn có hồn'], ['Biện pháp tu từ', 'Tăng sức gợi hình'], ['Điểm nhìn rõ ràng', 'Tổ chức mạch lạc']], 'Bài văn tả cảnh đạt điểm cao khi hội đủ: chi tiết hay + cảm xúc + tu từ + mạch lạc.', $d);

        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm TẢ CON VẬT hoặc TẢ CÂY CỐI.', [['Bộ lông mượt mà', 'TẢ CON VẬT'], ['Tán lá xum xuê', 'TẢ CÂY CỐI'], ['Đôi tai vểnh', 'TẢ CON VẬT'], ['Rễ cây ngoằn ngoèo', 'TẢ CÂY CỐI'], ['Chiếc đuôi dài', 'TẢ CON VẬT'], ['Hoa nở rộ', 'TẢ CÂY CỐI']], 'Mỗi nhóm đối tượng có hệ chi tiết riêng, không lẫn lộn.', $d);
        $this->sortQ($s, 'Kéo mỗi cách viết vào nhóm CÓ ĐIỂM NHÌN RÕ hoặc MƠ HỒ.', [['"Từ trên đồi nhìn xuống, thung lũng..."', 'CÓ ĐIỂM NHÌN RÕ'], ['"Chỗ này đẹp, chỗ kia cũng đẹp."', 'MƠ HỒ'], ['"Đứng bên bờ sông, em thấy..."', 'CÓ ĐIỂM NHÌN RÕ'], ['"Cảnh vật nói chung là đẹp."', 'MƠ HỒ']], 'Điểm nhìn rõ giúp người đọc "đi theo" người viết một cách tự nhiên.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm TẢ CÓ CẢM XÚC hoặc TẢ KHÔ KHAN.', [['"Em yêu biết mấy cánh đồng quê!"', 'TẢ CÓ CẢM XÚC'], ['"Cánh đồng rộng 10 ha."', 'TẢ KHÔ KHAN'], ['"Chú mèo như người bạn thân của em."', 'TẢ CÓ CẢM XÚC'], ['"Con mèo nặng 3 kg."', 'TẢ KHÔ KHAN']], 'Số liệu khô khan thuộc văn thuyết minh; văn miêu tả cần cảm xúc, hình ảnh.', $d);
        $this->sortQ($s, 'Kéo mỗi nội dung vào nhóm THUỘC PHẦN TẢ HÌNH DÁNG hoặc TẢ CÔNG DỤNG (đồ vật).', [['"Chiếc cặp màu xanh."', 'TẢ HÌNH DÁNG'], ['"Cặp giúp em đựng sách vở."', 'TẢ CÔNG DỤNG'], ['"Quai cặp chắc chắn."', 'TẢ HÌNH DÁNG'], ['"Cặp gắn với năm học đầu tiên."', 'TẢ CÔNG DỤNG']], 'Tả đồ vật hay: hình dáng + công dụng + kỉ niệm gắn bó.', $d);
        $this->sortQ($s, 'Kéo mỗi từ láy vào nhóm TẢ ÂM THANH MƯA hoặc TẢ HÌNH ẢNH MƯA.', [['lộp độp', 'TẢ ÂM THANH MƯA'], ['trắng xóa', 'TẢ HÌNH ẢNH MƯA'], ['rào rào', 'TẢ ÂM THANH MƯA'], ['mịt mù', 'TẢ HÌNH ẢNH MƯA']], 'Từ láy gợi thanh (lộp độp) và gợi hình (trắng xóa) cùng làm nên cơn mưa trong văn.', $d);

        $this->fill($s, 'Tả con vật cần chú ý bộ lông, dáng vẻ và những thói quen ___ đáo riêng của nó.', [[0, 'độc']], 'Thói quen độc đáo (mèo thích nằm sưởi nắng) tạo nên "cá tính" cho con vật.', $d);
        $this->fill($s, 'Tả đồ vật nên kết hợp hình dáng, công dụng và kỉ ___ gắn bó của người viết.', [[0, 'niệm']], 'Kỉ niệm biến đồ vật vô tri thành vật có hồn, đáng trân trọng.', $d);
        $this->fill($s, 'Điểm ___ là vị trí quan sát, quyết định trình tự miêu tả cảnh vật.', [[0, 'nhìn']], 'Xác định điểm nhìn trước khi tả giúp bài văn có mạch, không lộn xộn.', $d);
        $this->fill($s, 'Lối tả liệt kê cảnh đẹp chung chung, thiếu cảm xúc bị chê là "tả như bưu ___".', [[0, 'thiếp']], 'Bưu thiếp đẹp nhưng vô hồn — bài văn cần dấu ấn cảm xúc riêng của người viết.', $d);
        $this->fill($s, 'Từ láy "rào ___" gợi tả âm thanh dữ dội của cơn mưa rào mùa hạ.', [[0, 'rào']], '"Rào rào" là từ láy tượng thanh quen thuộc chỉ tiếng mưa lớn.', $d);
    }

    /* ============ ngu-van-thpt-10-doc-hieu-truyen-1 (Ngữ văn 10, trung_binh) ============ */
    private function seedNguVanThpt10Truyen1(): void
    {
        $s = 'ngu-van-thpt-10-doc-hieu-truyen-1'; $d = 'trung_binh';
        $this->quiz($s, 'Trong truyện ngắn, "nút thắt" (xung đột) thường xuất hiện ở phần nào của cốt truyện?', ['Mở đầu', 'Thắt nút', 'Phát triển', 'Kết thúc'], 1, 'Cốt truyện gồm: mở đầu – thắt nút – phát triển – cao trào – mở nút; xung đột nảy sinh ở phần thắt nút.', $d);
        $this->quiz($s, 'Người kể chuyện ngôi thứ ba có ưu điểm gì so với ngôi thứ nhất?', ['Gần gũi, chân thực hơn', 'Quan sát toàn diện, khách quan hơn', 'Hài hước hơn', 'Ngắn gọn hơn'], 1, 'Ngôi thứ ba đứng ngoài câu chuyện nên bao quát được nhiều nhân vật, sự việc một cách khách quan.', $d);
        $this->quiz($s, 'Chi tiết nào sau đây giúp nhận biết nhân vật chính trong truyện ngắn?', ['Xuất hiện nhiều nhất, gắn với chủ đề', 'Xuất hiện đầu tiên', 'Có tên đẹp nhất', 'Nói nhiều nhất'], 0, 'Nhân vật chính là người gắn bó mật thiết với chủ đề, xuất hiện xuyên suốt các sự kiện quan trọng.', $d);
        $this->quiz($s, 'Tình huống truyện "nhặt được của rơi" trong truyện ngắn thường có tác dụng gì?', ['Làm truyện dài hơn', 'Thử thách nhân cách, bộc lộ tính cách nhân vật', 'Giới thiệu phong cảnh', 'Kết thúc truyện'], 1, 'Tình huống là "phép thử" đặt nhân vật trước lựa chọn, từ đó tính cách bộc lộ rõ nhất.', $d);
        $this->quiz($s, 'Lời kể của nhân vật "tôi" trong truyện ngắn thường mang sắc thái gì?', ['Khách quan tuyệt đối', 'Chủ quan, gắn với điểm nhìn hạn chế của nhân vật', 'Hài hước, dí dỏm', 'Trang trọng, cổ điển'], 1, 'Người kể ngôi thứ nhất chỉ biết những gì nhân vật trải qua nên lời kể mang tính chủ quan, hạn chế.', $d);

        $this->matching($s, 'Nối mỗi chặng của cốt truyện với vai trò của nó.', [['Mở đầu', 'Giới thiệu nhân vật, hoàn cảnh'], ['Thắt nút', 'Nảy sinh mâu thuẫn, xung đột'], ['Cao trào', 'Kịch tính lên đến đỉnh điểm'], ['Mở nút', 'Tháo gỡ xung đột, kết thúc']], 'Nắm vững 5 chặng của cốt truyện giúp tóm tắt và phân tích truyện dễ dàng.', $d);
        $this->matching($s, 'Nối mỗi ngôi kể với đặc điểm của nó.', [['Ngôi thứ nhất', 'Xưng "tôi", tham gia câu chuyện'], ['Ngôi thứ ba', 'Kể như người ngoài cuộc'], ['Ngôi thứ nhất hạn chế', 'Chỉ biết những gì nhân vật biết'], ['Ngôi thứ ba toàn tri', 'Biết hết suy nghĩ mọi nhân vật']], 'Ngôi kể quyết định điểm nhìn và độ tin cậy của lời kể.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với vai trò trong truyện ngắn.', [['Nhân vật', 'Người thực hiện hành động, bộc lộ chủ đề'], ['Tình huống', 'Hoàn cảnh thử thách nhân vật'], ['Chi tiết', 'Điểm nhấn nghệ thuật gợi chủ đề'], ['Lời kể', 'Cách thức dẫn dắt câu chuyện']], 'Bốn yếu tố cơ bản tạo nên một truyện ngắn hoàn chỉnh.', $d);
        $this->matching($s, 'Nối mỗi loại nhân vật với dấu hiệu nhận biết.', [['Nhân vật chính', 'Gắn với chủ đề, xuất hiện xuyên suốt'], ['Nhân vật phụ', 'Làm nền, tôn nhân vật chính'], ['Nhân vật phản diện', 'Đại diện cái xấu, tạo xung đột'], ['Nhân vật chính diện', 'Đại diện cái tốt, lí tưởng']], 'Phân loại nhân vật giúp xác định đúng vai trò của từng người trong tác phẩm.', $d);
        $this->matching($s, 'Nối mỗi thể loại với đặc điểm tự sự của nó.', [['Truyện ngắn', 'Cốt truyện gọn, ít nhân vật'], ['Tiểu thuyết', 'Dung lượng lớn, nhiều tuyến truyện'], ['Truyện cổ tích', 'Yếu tố hoang đường, kết thúc có hậu'], ['Truyện cười', 'Gây cười qua tình huống oái oăm']], 'Mỗi thể loại tự sự có "luật chơi" riêng về dung lượng và cách kể.', $d);

        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm THUỘC CỐT TRUYỆN hoặc THUỘC NHÂN VẬT.', [['Thắt nút', 'THUỘC CỐT TRUYỆN'], ['Ngoại hình', 'THUỘC NHÂN VẬT'], ['Cao trào', 'THUỘC CỐT TRUYỆN'], ['Tính cách', 'THUỘC NHÂN VẬT'], ['Mở nút', 'THUỘC CỐT TRUYỆN'], ['Lời nói', 'THUỘC NHÂN VẬT']], 'Cốt truyện là chuỗi sự kiện; nhân vật được khắc họa qua ngoại hình, hành động, lời nói.', $d);
        $this->sortQ($s, 'Kéo mỗi cách kể vào nhóm NGÔI THỨ NHẤT hoặc NGÔI THỨ BA.', [['"Tôi bước vào lớp."', 'NGÔI THỨ NHẤT'], ['"Nó bước vào lớp."', 'NGÔI THỨ BA'], ['"Chúng tôi reo hò."', 'NGÔI THỨ NHẤT'], ['"Cả lớp reo hò."', 'NGÔI THỨ BA']], 'Xưng "tôi/chúng tôi" và tham gia sự việc → ngôi thứ nhất; kể về "nó/họ" → ngôi thứ ba.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm TẠO TÌNH HUỐNG hoặc TẢ PHONG CẢNH.', [['Nhặt được chiếc ví', 'TẠO TÌNH HUỐNG'], ['Hoàng hôn buông xuống', 'TẢ PHONG CẢNH'], ['Gặp lại bạn cũ', 'TẠO TÌNH HUỐNG'], ['Cánh đồng lúa chín', 'TẢ PHONG CẢNH']], 'Tình huống là sự kiện đặt nhân vật vào thử thách; phong cảnh là bối cảnh diễn ra sự việc.', $d);
        $this->sortQ($s, 'Kéo mỗi câu hỏi đọc hiểu vào nhóm VỀ CỐT TRUYỆN hoặc VỀ NHÂN VẬT.', [['Truyện kể về việc gì?', 'VỀ CỐT TRUYỆN'], ['Nhân vật có tính cách gì?', 'VỀ NHÂN VẬT'], ['Cao trào ở đoạn nào?', 'VỀ CỐT TRUYỆN'], ['Ai là nhân vật chính?', 'VỀ NHÂN VẬT']], 'Câu hỏi đọc hiểu thường xoay quanh hai trục: chuyện gì xảy ra và ai là người trong chuyện.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm TRUYỆN NGẮN hoặc TIỂU THUYẾT.', [['Ít nhân vật', 'TRUYỆN NGẮN'], ['Nhiều chương hồi', 'TIỂU THUYẾT'], ['Một tình huống trung tâm', 'TRUYỆN NGẮN'], ['Nhiều tuyến truyện', 'TIỂU THUYẾT']], 'Truyện ngắn "nhỏ mà có võ": gọn nhưng xoáy sâu vào một tình huống, một khoảnh khắc.', $d);

        $this->fill($s, 'Phần mở đầu của cốt truyện có nhiệm vụ giới thiệu nhân vật và hoàn ___.', [[0, 'cảnh']], 'Mở đầu đặt "sân khấu": ai, ở đâu, trong hoàn cảnh nào trước khi xung đột nảy sinh.', $d);
        $this->fill($s, 'Người kể xưng "tôi", trực tiếp tham gia vào câu chuyện thuộc ngôi kể thứ ___.', [[0, 'nhất']], 'Ngôi thứ nhất tạo cảm giác chân thực, gần gũi nhưng điểm nhìn bị hạn chế.', $d);
        $this->fill($s, 'Hoàn cảnh đặc biệt đặt nhân vật trước thử thách, làm bộc lộ tính cách gọi là tình ___ truyện.', [[0, 'huống']], 'Tình huống là "lò lửa" thử vàng: nhân cách nhân vật bộc lộ rõ nhất trong tình huống éo le.', $d);
        $this->fill($s, 'Nhân vật gắn bó mật thiết nhất với chủ đề, xuất hiện xuyên suốt tác phẩm là nhân vật ___.', [[0, 'chính']], 'Mọi sự kiện, chi tiết trong truyện đều hướng về nhân vật chính và chủ đề.', $d);
        $this->fill($s, 'Cốt truyện là chuỗi sự kiện được sắp xếp theo quan hệ nhân ___, tạo thành mạch truyện.', [[0, 'quả']], 'Sự kiện trước là nguyên nhân của sự kiện sau — đó là logic của cốt truyện.', $d);
    }

    /* ============ ngu-van-thpt-10-doc-hieu-truyen-2 (Ngữ văn 10, trung_binh) ============ */
    private function seedNguVanThpt10Truyen2(): void
    {
        $s = 'ngu-van-thpt-10-doc-hieu-truyen-2'; $d = 'trung_binh';
        $this->quiz($s, 'Chi tiết "chiếc lá cuối cùng" trong truyện ngắn cùng tên có ý nghĩa gì?', ['Chỉ là chiếc lá bình thường', 'Biểu tượng của niềm tin, sự sống', 'Báo hiệu mùa đông đến', 'Trang trí cho câu chuyện'], 1, 'Chiếc lá do cụ Behrman vẽ trở thành biểu tượng của niềm tin giúp Giôn-xi vượt qua bệnh tật.', $d);
        $this->quiz($s, 'Muốn xác định chủ đề của truyện ngắn, cách làm đúng là gì?', ['Đọc tên truyện rồi đoán', 'Xâu chuỗi các chi tiết, sự kiện để tìm vấn đề trung tâm', 'Hỏi ý kiến bạn bè', 'Chỉ đọc đoạn kết'], 1, 'Chủ đề ẩn sau toàn bộ hệ thống chi tiết, sự kiện — phải đọc kĩ và tổng hợp.', $d);
        $this->quiz($s, 'Thông điệp "sự sống nảy sinh từ cái chết" trong "Chiếc lá cuối cùng" thể hiện tư tưởng gì?', ['Bi quan về cuộc đời', 'Ngợi ca tình yêu thương, sự hi sinh', 'Chê bai xã hội', 'Ca ngợi thiên nhiên'], 1, 'Cụ Behrman hi sinh để vẽ chiếc lá, cứu sống Giôn-xi — tình người cao đẹp.', $d);
        $this->quiz($s, 'Tư tưởng "ở hiền gặp lành" trong truyện cổ tích thể hiện qua yếu tố nào?', ['Kết thúc có hậu cho người tốt', 'Nhân vật có phép thuật', 'Truyện rất dài', 'Có nhiều nhân vật'], 0, 'Kết thúc có hậu là cách dân gian gửi gắm niềm tin vào công lí, điều thiện.', $d);
        $this->quiz($s, 'Khi phân tích nhân vật, yếu tố nào KHÔNG nên bỏ qua?', ['Ngoại hình, hành động, lời nói, suy nghĩ', 'Màu sắc quần áo', 'Chiều cao chính xác', 'Ngày sinh của nhân vật'], 0, 'Nhân vật được khắc họa qua bốn phương diện: ngoại hình, hành động, lời nói và đời sống nội tâm.', $d);

        $this->matching($s, 'Nối mỗi loại chi tiết với vai trò của nó.', [['Chi tiết nghệ thuật', 'Tiêu biểu, giàu sức gợi, thể hiện chủ đề'], ['Chi tiết thông thường', 'Dựng bối cảnh, nối mạch truyện'], ['Chi tiết kì ảo', 'Tạo sức hấp dẫn, gửi gắm ước mơ'], ['Chi tiết đắt giá', 'Điểm nhấn làm sáng chủ đề']], 'Chi tiết nghệ thuật là "viên ngọc" của truyện ngắn — nhỏ nhưng chiếu sáng cả tác phẩm.', $d);
        $this->matching($s, 'Nối mỗi bước đọc hiểu truyện với việc cần làm.', [['Tóm tắt', 'Nắm cốt truyện, nhân vật chính'], ['Phân tích chi tiết', 'Tìm chi tiết nghệ thuật tiêu biểu'], ['Xác định chủ đề', 'Tìm vấn đề trung tâm'], ['Rút thông điệp', 'Hiểu điều tác giả gửi gắm']], 'Quy trình 4 bước giúp đọc hiểu truyện ngắn có hệ thống, không bỏ sót.', $d);
        $this->matching($s, 'Nối mỗi chi tiết trong "Chiếc lá cuối cùng" với ý nghĩa của nó.', [['Chiếc lá cuối cùng', 'Niềm tin vào sự sống'], ['Cụ Behrman', 'Tình yêu thương, sự hi sinh'], ['Bệnh tật của Giôn-xi', 'Thử thách của số phận'], ['Bức vẽ chiếc lá', 'Nghệ thuật phục vụ con người']], 'Mỗi chi tiết trong truyện hay đều mang một ý nghĩa biểu tượng sâu sắc.', $d);
        $this->matching($s, 'Nối mỗi mức độ câu hỏi với ví dụ.', [['Nhận biết', '"Truyện kể về ai?"'], ['Thông hiểu', '"Vì sao nhân vật hành động như vậy?"'], ['Vận dụng', '"Em học được gì từ nhân vật?"'], ['Vận dụng cao', '"So sánh hai nhân vật trong hai truyện."']], 'Câu hỏi đọc hiểu đi từ dễ đến khó: nhận biết → hiểu → vận dụng vào đời sống.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với nội dung đúng.', [['Cốt truyện', 'Chuỗi sự kiện có quan hệ nhân quả'], ['Chủ đề', 'Vấn đề trung tâm của tác phẩm'], ['Thông điệp', 'Điều tác giả muốn gửi gắm'], ['Tư tưởng', 'Quan niệm sâu sắc về con người, cuộc đời']], 'Bốn khái niệm nền tảng của đọc hiểu truyện: chuyện gì – về vấn đề gì – nhắn gửi gì.', $d);

        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm CHI TIẾT BIỂU TƯỢNG hoặc CHI TIẾT THÔNG THƯỜNG.', [['Chiếc lá cuối cùng', 'CHI TIẾT BIỂU TƯỢNG'], ['Bữa ăn sáng', 'CHI TIẾT THÔNG THƯỜNG'], ['Ngọn đèn trong đêm', 'CHI TIẾT BIỂU TƯỢNG'], ['Con đường làng', 'CHI TIẾT THÔNG THƯỜNG']], 'Chi tiết biểu tượng mang nghĩa bóng sâu xa; chi tiết thông thường chỉ dựng bối cảnh.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm THỂ HIỆN LÒNG NHÂN ÁI hoặc KHÔNG.', [['Cụ Behrman vẽ chiếc lá trong đêm mưa', 'THỂ HIỆN LÒNG NHÂN ÁI'], ['Bỏ mặc người bệnh', 'KHÔNG'], ['Chia sẻ bữa ăn với người nghèo', 'THỂ HIỆN LÒNG NHÂN ÁI'], ['Cười nhạo nỗi đau người khác', 'KHÔNG']], 'Đánh giá nhân vật qua hành động là cách đọc hiểu cơ bản và hiệu quả.', $d);
        $this->sortQ($s, 'Kéo mỗi câu hỏi vào nhóm CÂU HỎI NHẬN BIẾT hoặc CÂU HỎI VẬN DỤNG.', [['"Nhân vật chính là ai?"', 'CÂU HỎI NHẬN BIẾT'], ['"Em rút ra bài học gì?"', 'CÂU HỎI VẬN DỤNG'], ['"Truyện có mấy nhân vật?"', 'CÂU HỎI NHẬN BIẾT'], ['"Nếu là nhân vật, em sẽ làm gì?"', 'CÂU HỎI VẬN DỤNG']], 'Nhận biết hỏi "cái gì có trong truyện"; vận dụng hỏi "em nghĩ gì, làm gì".', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm THỂ HIỆN TƯ TƯỞNG NHÂN ĐẠO hoặc KHÔNG.', [['Ngợi ca tình yêu thương', 'THỂ HIỆN TƯ TƯỞNG NHÂN ĐẠO'], ['Cổ súy bạo lực', 'KHÔNG'], ['Đồng cảm với người nghèo', 'THỂ HIỆN TƯ TƯỞNG NHÂN ĐẠO'], ['Chế giễu người yếu thế', 'KHÔNG']], 'Tư tưởng nhân đạo: yêu thương, đồng cảm, đấu tranh cho con người.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm BƯỚC ĐỌC HIỂU ĐÚNG THỨ TỰ hoặc SAI THỨ TỰ.', [['Đọc kĩ văn bản trước', 'ĐÚNG THỨ TỰ'], ['Rút thông điệp trước khi đọc', 'SAI THỨ TỰ'], ['Tóm tắt rồi phân tích', 'ĐÚNG THỨ TỰ'], ['Đoán chủ đề khi chưa đọc', 'SAI THỨ TỰ']], 'Đọc hiểu phải đi từ văn bản: đọc kĩ → tóm tắt → phân tích → khái quát.', $d);

        $this->fill($s, 'Chi tiết tiêu biểu, giàu sức gợi, góp phần thể hiện chủ đề gọi là chi tiết nghệ ___.', [[0, 'thuật']], 'Chi tiết nghệ thuật là "linh hồn" của truyện ngắn: chiếc lá cuối cùng, bát cháo hành...', $d);
        $this->fill($s, 'Vấn đề trung tâm mà toàn bộ tác phẩm hướng tới gọi là ___ đề của tác phẩm.', [[0, 'chủ']], 'Mọi chi tiết, sự kiện, nhân vật đều phục vụ việc thể hiện chủ đề.', $d);
        $this->fill($s, 'Điều tốt đẹp tác giả muốn gửi gắm tới người đọc qua câu chuyện gọi là thông ___.', [[0, 'điệp']], 'Thông điệp là "món quà" tác giả trao cho độc giả sau khi gấp sách lại.', $d);
        $this->fill($s, 'Khi đọc hiểu, sau khi tóm tắt cốt truyện cần phân tích hệ thống chi ___ nghệ thuật.', [[0, 'tiết']], 'Chi tiết nghệ thuật là chìa khóa mở ra chủ đề và thông điệp của truyện.', $d);
        $this->fill($s, 'Tư tưởng nhân đạo thể hiện qua sự đồng cảm, ngợi ca cái tốt và ___ tranh cho con người.', [[0, 'đấu']], 'Ba biểu hiện của tư tưởng nhân đạo: đồng cảm – ngợi ca – đấu tranh.', $d);
    }

    /* ============ ngu-van-thpt-10-doc-hieu-tho-1 (Ngữ văn 10, trung_binh) ============ */
    private function seedNguVanThpt10Tho1(): void
    {
        $s = 'ngu-van-thpt-10-doc-hieu-tho-1'; $d = 'trung_binh';
        $this->quiz($s, 'Thơ song thất lục bát có đặc điểm gì về số chữ?', ['Câu 7 chữ và câu 6-8 chữ xen kẽ', 'Toàn câu 5 chữ', 'Toàn câu 7 chữ', 'Tự do số chữ'], 0, 'Song thất lục bát: hai câu 7 chữ (song thất) tiếp một cặp lục bát (6-8).', $d);
        $this->quiz($s, 'Thơ thất ngôn bát cú Đường luật có bao nhiêu câu?', ['4 câu', '6 câu', '8 câu', '10 câu'], 2, 'Thất ngôn bát cú: 8 câu, mỗi câu 7 chữ, gồm đề – thực – luận – kết.', $d);
        $this->quiz($s, 'Nhịp 2/2/3 trong câu thơ lục bát 6 chữ có tác dụng gì?', ['Làm câu thơ dài hơn', 'Tạo tiết tấu, nhạc tính cho câu thơ', 'Làm câu thơ khó hiểu', 'Không có tác dụng gì'], 1, 'Nhịp là cách ngắt câu tạo tiết tấu; nhịp 2/2/3 quen thuộc của câu 6 rất uyển chuyển.', $d);
        $this->quiz($s, 'Vần chân trong thơ là gì?', ['Vần ở đầu câu thơ', 'Vần ở cuối câu thơ', 'Vần ở giữa câu thơ', 'Vần trong từ láy'], 1, 'Vần chân (vần cuối câu) phổ biến nhất; vần lưng (giữa câu) ít gặp hơn.', $d);
        $this->quiz($s, 'Thơ tự do của phong trào Thơ mới khác thơ Đường luật ở điểm nào?', ['Không có cảm xúc', 'Không bị ràng buộc chặt về niêm, luật, vần', 'Không có vần điệu', 'Không có hình ảnh'], 1, 'Thơ tự do phá bỏ niêm luật gò bó nhưng vẫn có nhạc tính, vần điệu linh hoạt.', $d);

        $this->matching($s, 'Nối mỗi thể thơ với đặc điểm số chữ của nó.', [['Lục bát', 'Câu 6 và câu 8 xen kẽ'], ['Song thất lục bát', 'Hai câu 7 + cặp lục bát'], ['Thất ngôn tứ tuyệt', '4 câu, mỗi câu 7 chữ'], ['Ngũ ngôn', 'Mỗi câu 5 chữ']], 'Nhận diện thể thơ trước tiên qua số chữ mỗi câu — dấu hiệu dễ thấy nhất.', $d);
        $this->matching($s, 'Hãy nối mỗi khái niệm với nội dung đúng.', [['Nhịp thơ', 'Cách ngắt câu tạo tiết tấu'], ['Vần thơ', 'Âm thanh lặp lại tạo nhạc tính'], ['Niêm', 'Quy tắc về thanh trong thơ Đường'], ['Đối', 'Hai câu sóng đôi về ý và từ loại']], 'Nhịp, vần, niêm, đối là bốn yếu tố hình thức quan trọng của thơ cách luật.', $d);
        $this->matching($s, 'Nối mỗi cách gieo vần với mô tả của nó.', [['Vần chân', 'Vần ở cuối các câu thơ'], ['Vần lưng', 'Vần ở giữa câu thơ'], ['Vần liền', 'Câu nọ vần câu kia liên tiếp'], ['Vần cách', 'Vần cách một câu']], 'Thơ lục bát gieo vần chân: tiếng thứ 6 câu 6 vần với tiếng thứ 6 câu 8.', $d);
        $this->matching($s, 'Hãy nối mỗi dấu hiệu với thể thơ tương ứng.', [['Câu 6-8 xen kẽ', 'Lục bát'], ['8 câu 7 chữ, có niêm luật', 'Thất ngôn bát cú'], ['Số chữ tự do', 'Thơ tự do'], ['4 câu 5 chữ', 'Ngũ ngôn tứ tuyệt']], 'Dấu hiệu hình thức giúp nhận diện thể thơ nhanh trước khi đi vào nội dung.', $d);
        $this->matching($s, 'Nối mỗi thể thơ với ví dụ tác phẩm.', [['Lục bát', 'Truyện Kiều'], ['Song thất lục bát', 'Chinh phụ ngâm'], ['Thất ngôn bát cú', 'Qua đèo Ngang'], ['Thơ tự do', 'Sóng (Xuân Quỳnh)']], 'Gắn thể thơ với tác phẩm tiêu biểu giúp nhớ lâu và hiểu sâu.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi thể thơ vào nhóm THƠ CÁCH LUẬT hoặc THƠ TỰ DO.', [['Lục bát', 'THƠ CÁCH LUẬT'], ['Thơ tự do', 'THƠ TỰ DO'], ['Thất ngôn bát cú', 'THƠ CÁCH LUẬT'], ['Thơ văn xuôi', 'THƠ TỰ DO']], 'Thơ cách luật bị ràng buộc về số câu, số chữ, vần, nhịp; thơ tự do phóng khoáng hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi tiếng trong câu "Trăm năm trong cõi người ta" vào nhóm THANH BẰNG hoặc THANH TRẮC.', [['Trăm, năm, trong, ta', 'THANH BẰNG'], ['cõi, người', 'THANH TRẮC']], 'Thanh bằng: không dấu, huyền. Thanh trắc: sắc, hỏi, ngã, nặng.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu thơ vào nhóm CÂU 6 CHỮ hoặc CÂU 8 CHỮ.', [['"Trăm năm trong cõi người ta"', 'CÂU 6 CHỮ'], ['"Chữ tài chữ mệnh khéo là ghét nhau"', 'CÂU 8 CHỮ'], ['"Trải qua một cuộc bể dâu"', 'CÂU 6 CHỮ'], ['"Những điều trông thấy mà đau đớn lòng"', 'CÂU 8 CHỮ']], 'Đếm số tiếng mỗi câu là cách nhận diện lục bát đơn giản nhất.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm THUỘC NHẠC TÍNH hoặc THUỘC HÌNH ẢNH của bài thơ.', [['Vần', 'THUỘC NHẠC TÍNH'], ['So sánh', 'THUỘC HÌNH ẢNH'], ['Nhịp', 'THUỘC NHẠC TÍNH'], ['Ẩn dụ', 'THUỘC HÌNH ẢNH']], 'Nhạc tính (vần, nhịp) làm thơ du dương; hình ảnh (tu từ) làm thơ đẹp, sâu.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp câu vào nhóm CÓ VẦN VỚI NHAU hoặc KHÔNG.', [['"ta" – "là" (Trăm năm... khéo là)', 'CÓ VẦN VỚI NHAU'], ['"người" – "trời"', 'KHÔNG'], ['"dâu" – "đầu" (bể dâu – đớn lòng: dâu/đâu)', 'CÓ VẦN VỚI NHAU'], ['"sông" – "núi"', 'KHÔNG']], '"Ta – là" vần a; "dâu – đâu" vần âu; "người – trời", "sông – núi" không vần.', $d);

        $this->fill($s, 'Thơ song thất lục bát gồm hai câu 7 chữ rồi đến một cặp ___ bát.', [[0, 'lục']], 'Cấu trúc: song thất (7-7) + lục bát (6-8) lặp lại.', $d);
        $this->fill($s, 'Trong thơ Đường luật, hai câu 3-4 gọi là cặp ___, hai câu 5-6 gọi là cặp luận.', [[0, 'thực']], 'Bố cục thất ngôn bát cú: đề (1-2) – thực (3-4) – luận (5-6) – kết (7-8).', $d);
        $this->fill($s, 'Âm thanh lặp lại ở cuối câu thơ tạo nhạc tính gọi là ___.', [[0, 'vần']], 'Vần là "linh hồn nhạc tính" của thơ, giúp câu thơ du dương, dễ nhớ.', $d);
        $this->fill($s, 'Thanh bằng gồm thanh không dấu và thanh ___.', [[0, 'huyền']], 'Bằng: ngang + huyền (trầm, bổng nhẹ). Trắc: sắc, hỏi, ngã, nặng.', $d);
        $this->fill($s, 'Thơ không bị ràng buộc về số chữ, số câu, niêm luật gọi là thơ tự ___.', [[0, 'do']], 'Thơ tự do của Thơ mới giải phóng cảm xúc khỏi khuôn khổ gò bó.', $d);
    }

    /* ============ ngu-van-thpt-10-doc-hieu-tho-2 (Ngữ văn 10, trung_binh) ============ */
    private function seedNguVanThpt10Tho2(): void
    {
        $s = 'ngu-van-thpt-10-doc-hieu-tho-2'; $d = 'trung_binh';
        $this->quiz($s, 'Hình ảnh "con cò" trong ca dao thường tượng trưng cho ai?', ['Người nông dân lam lũ', 'Người mẹ tần tảo', 'Người chiến sĩ', 'Trẻ em'], 1, '"Con cò" lặn lội bờ sông là hình ảnh ẩn dụ quen thuộc chỉ người mẹ, người phụ nữ tần tảo.', $d);
        $this->quiz($s, 'Chủ thể trữ tình trong bài thơ "Sóng" của Xuân Quỳnh là ai?', ['Người con gái đang yêu', 'Con sóng biển', 'Người kể chuyện', 'Tác giả'], 0, 'Nhân vật "em" trong bài thơ là người con gái đang yêu, mượn sóng để nói lòng mình.', $d);
        $this->quiz($s, 'Cảm xúc chủ đạo trong bài "Đây thôn Vĩ Dạ" của Hàn Mặc Tử là gì?', ['Vui tươi, rộn rã', 'Nhớ nhung, khắc khoải, u hoài', 'Giận dữ, phẫn nộ', 'Thờ ơ, lạnh nhạt'], 1, 'Nỗi nhớ quê hương da diết pha chút mặc cảm, u hoài bao trùm cả bài thơ.', $d);
        $this->quiz($s, 'Từ "thôn Vĩ" gợi cho người đọc liên tưởng đến không gian nào?', ['Phố thị ồn ào', 'Làng quê xứ Huế thơ mộng', 'Chiến trường ác liệt', 'Biển cả mênh mông'], 1, 'Vĩ Dạ là làng quê bên sông Hương (Huế) với vườn tược, trăng, đò — không gian thơ mộng.', $d);
        $this->quiz($s, 'Biện pháp tu từ nào giúp hình ảnh thơ trở nên giàu sức gợi nhất?', ['Liệt kê', 'Ẩn dụ, so sánh', 'Nói giảm', 'Chơi chữ'], 1, 'Ẩn dụ, so sánh biến hình ảnh cụ thể thành biểu tượng đa nghĩa, giàu sức gợi.', $d);

        $this->matching($s, 'Nối mỗi hình ảnh thơ với cảm xúc nó thường gợi.', [['Trăng thu', 'Nỗi nhớ, sự trong sáng'], ['Con đò', 'Chia li, đợi chờ'], ['Mùa thu', 'Nỗi buồn man mác'], ['Cánh chim', 'Khát vọng tự do']], 'Hình ảnh thơ mang "mã cảm xúc" quen thuộc, thuộc lòng sẽ cảm thơ nhanh hơn.', $d);
        $this->matching($s, 'Nối mỗi giọng điệu thơ với sắc thái tình cảm.', [['Trầm lắng', 'Suy tư, chiêm nghiệm'], ['Da diết', 'Nhớ nhung tha thiết'], ['Hào sảng', 'Phấn chấn, lạc quan'], ['U hoài', 'Buồn man mác, tiếc nuối']], 'Giọng điệu là "giọng nói" cảm xúc của bài thơ, bao trùm từ đầu đến cuối.', $d);
        $this->matching($s, 'Hãy nối mỗi bước cảm thụ thơ với việc cần làm.', [['Đọc diễn cảm', 'Cảm nhận nhạc điệu, nhịp thơ'], ['Tìm hình ảnh', 'Gạch chân hình ảnh đắt giá'], ['Giải mã tu từ', 'Tìm nét tương đồng, ý nghĩa'], ['Khái quát cảm xúc', 'Xác định tình cảm chủ đạo']], 'Cảm thơ theo trình tự: nghe nhạc thơ → thấy hình ảnh → hiểu ý nghĩa → cảm tình cảm.', $d);
        $this->matching($s, 'Nối mỗi từ láy trong thơ với tác dụng gợi tả.', [['"xào xạc" (lá thu)', 'Gợi tả âm thanh'], ['"lấp lánh" (sao)', 'Gợi tả ánh sáng'], ['"mênh mông" (biển)', 'Gợi tả không gian'], ['"da diết" (nỗi nhớ)', 'Gợi tả cảm xúc']], 'Từ láy trong thơ là "viên ngọc" gợi hình, gợi thanh, gợi cảm xúc.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với nội dung đúng.', [['Hình ảnh thơ', 'Hình ảnh gợi từ ngôn ngữ thơ'], ['Tứ thơ', 'Ý tưởng trung tâm của bài thơ'], ['Chủ thể trữ tình', 'Người bộc lộ cảm xúc trong thơ'], ['Giọng điệu', 'Sắc thái tình cảm bao trùm']], 'Bốn khái niệm "xương sống" khi đọc hiểu thơ trữ tình.', $d);

        $this->sortQ($s, 'Kéo mỗi hình ảnh vào nhóm HÌNH ẢNH ƯỚC LỆ hoặc HÌNH ẢNH SÁNG TẠO.', [['"Con cò" (ca dao)', 'HÌNH ẢNH ƯỚC LỆ'], ['"Sóng" (Xuân Quỳnh)', 'HÌNH ẢNH SÁNG TẠO'], ['"Trăng thu"', 'HÌNH ẢNH ƯỚC LỆ'], ['"Thuyền và biển"', 'HÌNH ẢNH SÁNG TẠO']], 'Hình ảnh ước lệ quen thuộc, dùng nhiều đời; hình ảnh sáng tạo mang dấu ấn riêng tác giả.', $d);
        $this->sortQ($s, 'Kéo mỗi từ ngữ cảm xúc vào nhóm CẢM XÚC NHỚ NHUNG hoặc CẢM XÚC VUI SƯỚNG.', [['da diết', 'CẢM XÚC NHỚ NHUNG'], ['rộn ràng', 'CẢM XÚC VUI SƯỚNG'], ['khắc khoải', 'CẢM XÚC NHỚ NHUNG'], ['hân hoan', 'CẢM XÚC VUI SƯỚNG'], ['bâng khuâng', 'CẢM XÚC NHỚ NHUNG'], ['tưng bừng', 'CẢM XÚC VUI SƯỚNG']], 'Từ ngữ cảm xúc là "nhiệt kế" đo cảm xúc chủ đạo của bài thơ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi vai trò vào nhóm CHỦ THỂ TRỮ TÌNH hoặc ĐỐI TƯỢNG TRỮ TÌNH.', [['"Em" trong "Sóng"', 'CHỦ THỂ TRỮ TÌNH'], ['"Sóng" trong "Sóng"', 'ĐỐI TƯỢNG TRỮ TÌNH'], ['"Anh" trong thơ tình', 'CHỦ THỂ TRỮ TÌNH'], ['"Trăng" được ngắm', 'ĐỐI TƯỢNG TRỮ TÌNH']], 'Chủ thể là người bộc lộ cảm xúc; đối tượng là cái khơi gợi cảm xúc ấy.', $d);
        $this->sortQ($s, 'Kéo mỗi câu thơ vào nhóm GIÀU NHẠC TÍNH hoặc ÍT NHẠC TÍNH.', [['"Sóng bắt đầu từ gió..."', 'GIÀU NHẠC TÍNH'], ['"Hôm nay trời đẹp."', 'ÍT NHẠC TÍNH'], ['"Thuyền về có nhớ bến chăng?"', 'GIÀU NHẠC TÍNH'], ['"Tôi đi học."', 'ÍT NHẠC TÍNH']], 'Nhạc tính đến từ vần, nhịp, từ láy — câu văn xuôi thông thường ít nhạc tính.', $d);
        $this->sortQ($s, 'Kéo mỗi hình ảnh vào nhóm GỢI NIỀM VUI hoặc GỢI NỖI BUỒN.', [['Nắng sớm mai', 'GỢI NIỀM VUI'], ['Lá vàng rơi', 'GỢI NỖI BUỒN'], ['Chim hót líu lo', 'GỢI NIỀM VUI'], ['Mưa dầm dề', 'GỢI NỖI BUỒN']], 'Cùng là thiên nhiên nhưng mỗi hình ảnh mang một "mã cảm xúc" khác nhau.', $d);

        $this->fill($s, 'Người trực tiếp bộc lộ cảm xúc, suy nghĩ trong bài thơ gọi là chủ thể trữ ___.', [[0, 'tình']], 'Chủ thể trữ tình có thể là "tôi", "em", "anh" — tiếng nói cảm xúc của bài thơ.', $d);
        $this->fill($s, 'Sắc thái tình cảm bao trùm toàn bộ bài thơ, tạo nên "giọng" riêng gọi là giọng ___.', [[0, 'điệu']], 'Giọng điệu da diết, trầm lắng hay hào sảng quyết định cảm nhận chung về bài thơ.', $d);
        $this->fill($s, 'Hình ảnh "sóng" trong thơ Xuân Quỳnh tượng trưng cho tình yêu và khát vọng của người con ___.', [[0, 'gái']], 'Sóng – em: hai hình tượng song hành diễn tả tình yêu mãnh liệt, chung thủy.', $d);
        $this->fill($s, 'Muốn cảm thụ thơ hay, trước hết phải đọc ___ cảm để cảm nhận nhạc điệu của câu thơ.', [[0, 'diễn']], 'Đọc diễn cảm giúp "nghe" được nhịp, vần — cánh cửa đầu tiên vào thế giới thơ.', $d);
        $this->fill($s, 'Ý tưởng trung tâm, làm nên "xương sống" tư tưởng của bài thơ gọi là ___ thơ.', [[0, 'tứ']], 'Tứ thơ là hạt nhân ý nghĩa mà mọi hình ảnh, cảm xúc trong bài đều hướng tới.', $d);
    }

    /* ============ ngu-van-thpt-11-tu-tuong-dao-li-1 (Ngữ văn 11, trung_binh) ============ */
    private function seedNguVanThpt11TuTuong1(): void
    {
        $s = 'ngu-van-thpt-11-tu-tuong-dao-li-1'; $d = 'trung_binh';
        $this->quiz($s, 'Đề bài "Suy nghĩ về lòng nhân ái" thuộc dạng nghị luận nào?', ['Nghị luận về tư tưởng đạo lí', 'Nghị luận về hiện tượng đời sống', 'Nghị luận văn học', 'Thuyết minh'], 0, '"Lòng nhân ái" là một quan niệm đạo đức, lối sống — thuộc tư tưởng đạo lí.', $d);
        $this->quiz($s, 'Thao tác lập luận phân tích khác giải thích ở điểm nào?', ['Phân tích chia nhỏ vấn đề để làm rõ, giải thích làm sáng tỏ khái niệm', 'Phân tích không cần dẫn chứng', 'Giải thích không cần lí lẽ', 'Hai thao tác hoàn toàn giống nhau'], 0, 'Giải thích: làm rõ "là gì". Phân tích: chia nhỏ "gồm những gì, vì sao".', $d);
        $this->quiz($s, 'Dẫn chứng nào phù hợp nhất cho đề "bàn về lòng kiên trì"?', ['Câu chuyện Edison thử hàng nghìn lần mới ra bóng đèn', 'Một bài hát hay', 'Món ăn ngon', 'Trận bóng đá'], 0, 'Edison kiên trì thí nghiệm là dẫn chứng tiêu biểu, xác thực cho lòng kiên trì.', $d);
        $this->quiz($s, 'Luận cứ trong bài nghị luận là gì?', ['Ý kiến chính cần chứng minh', 'Lí lẽ và dẫn chứng làm sáng tỏ luận điểm', 'Phần mở bài', 'Câu kết bài'], 1, 'Luận điểm là ý chính; luận cứ (lí lẽ + dẫn chứng) là "vũ khí" chứng minh luận điểm.', $d);
        $this->quiz($s, 'Câu tục ngữ "Có công mài sắt, có ngày nên kim" bàn về tư tưởng nào?', ['Lòng nhân ái', 'Lòng kiên trì, nhẫn nại', 'Tính trung thực', 'Lòng yêu nước'], 1, 'Mài sắt thành kim — hình ảnh ẩn dụ cho sự kiên trì sẽ thành công.', $d);

        $this->matching($s, 'Hãy nối mỗi thao tác lập luận với nội dung của nó.', [['Giải thích', 'Làm rõ khái niệm, bản chất vấn đề'], ['Chứng minh', 'Dùng dẫn chứng, lí lẽ làm sáng tỏ'], ['Phân tích', 'Chia nhỏ vấn đề để xem xét'], ['Bình luận', 'Đánh giá, bày tỏ quan điểm']], 'Bốn thao tác cơ bản của nghị luận: giải thích – chứng minh – phân tích – bình luận.', $d);
        $this->matching($s, 'Hãy nối mỗi câu tục ngữ với chủ đề tư tưởng của nó.', [['"Uống nước nhớ nguồn"', 'Lòng biết ơn'], ['"Có công mài sắt"', 'Lòng kiên trì'], ['"Lá lành đùm lá rách"', 'Lòng nhân ái'], ['"Ăn quả nhớ kẻ trồng cây"', 'Lòng biết ơn']], 'Tục ngữ là "kho" dẫn chứng quý cho nghị luận về tư tưởng đạo lí.', $d);
        $this->matching($s, 'Hãy nối mỗi đề bài với dạng nghị luận tương ứng.', [['"Bàn về lòng dũng cảm"', 'Tư tưởng đạo lí'], ['"Suy nghĩ về bạo lực học đường"', 'Hiện tượng đời sống'], ['"Phân tích nhân vật Chí Phèo"', 'Nghị luận văn học'], ['"Bàn về tính trung thực"', 'Tư tưởng đạo lí']], 'Tư tưởng đạo lí bàn quan niệm sống; hiện tượng đời sống bàn sự việc cụ thể.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với vai trò của nó trong bài văn.', [['Luận điểm', 'Ý kiến chính của bài'], ['Luận cứ', 'Lí lẽ, dẫn chứng chứng minh'], ['Mở bài', 'Nêu vấn đề nghị luận'], ['Kết bài', 'Khẳng định lại, rút bài học']], 'Ba phần mở – thân – kết và hai yếu tố luận điểm – luận cứ tạo nên bài văn hoàn chỉnh.', $d);
        $this->matching($s, 'Nối mỗi danh ngôn với bài học có thể dùng làm dẫn chứng.', [['"Thất bại là mẹ thành công"', 'Lòng kiên trì'], ['"Yêu thương cho đi là còn mãi"', 'Lòng nhân ái'], ['"Học, học nữa, học mãi"', 'Tinh thần học tập'], ['"Đoàn kết là sức mạnh"', 'Tinh thần đoàn kết']], 'Danh ngôn của vĩ nhân là dẫn chứng "sang" cho bài nghị luận.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi đề bài vào nhóm TƯ TƯỞNG ĐẠO LÍ hoặc HIỆN TƯỢNG ĐỜI SỐNG.', [['"Bàn về lòng yêu nước"', 'TƯ TƯỞNG ĐẠO LÍ'], ['"Suy nghĩ về tai nạn giao thông"', 'HIỆN TƯỢNG ĐỜI SỐNG'], ['"Bàn về tính khiêm tốn"', 'TƯ TƯỞNG ĐẠO LÍ'], ['"Suy nghĩ về nghiện game"', 'HIỆN TƯỢNG ĐỜI SỐNG']], 'Tư tưởng: quan niệm, phẩm chất. Hiện tượng: sự việc nổi bật trong đời sống.', $d);
        $this->sortQ($s, 'Kéo mỗi dẫn chứng vào nhóm TIÊU BIỂU hoặc CHUNG CHUNG.', [['Edison kiên trì thí nghiệm', 'TIÊU BIỂU'], ['"Có người rất kiên trì"', 'CHUNG CHUNG'], ['Hồ Chí Minh ra đi tìm đường cứu nước', 'TIÊU BIỂU'], ['"Nhiều người đã thành công"', 'CHUNG CHUNG']], 'Dẫn chứng tiêu biểu: cụ thể, xác thực, nổi tiếng; tránh chung chung, mơ hồ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi ý vào nhóm LUẬN ĐIỂM hoặc LUẬN CỨ.', [['"Kiên trì là chìa khóa thành công."', 'LUẬN ĐIỂM'], ['"Edison thất bại hàng nghìn lần."', 'LUẬN CỨ'], ['"Nhân ái làm đẹp cuộc đời."', 'LUẬN ĐIỂM'], ['"Câu chuyện cậu bé nhường áo ấm."', 'LUẬN CỨ']], 'Luận điểm là ý kiến; luận cứ là bằng chứng cụ thể chứng minh ý kiến ấy.', $d);
        $this->sortQ($s, 'Kéo mỗi thao tác vào nhóm DÙNG Ở MỞ BÀI hoặc DÙNG Ở THÂN BÀI.', [['Nêu vấn đề', 'DÙNG Ở MỞ BÀI'], ['Chứng minh bằng dẫn chứng', 'DÙNG Ở THÂN BÀI'], ['Dẫn dắt vào đề', 'DÙNG Ở MỞ BÀI'], ['Phân tích, bình luận', 'DÙNG Ở THÂN BÀI']], 'Mở bài: nêu vấn đề. Thân bài: giải thích, chứng minh, phân tích, bình luận.', $d);
        $this->sortQ($s, 'Kéo mỗi biểu hiện vào nhóm CỦA LÒNG NHÂN ÁI hoặc KHÔNG PHẢI.', [['Giúp đỡ người hoạn nạn', 'CỦA LÒNG NHÂN ÁI'], ['Vô cảm trước nỗi đau', 'KHÔNG PHẢI'], ['Chia sẻ với người nghèo', 'CỦA LÒNG NHÂN ÁI'], ['Ích kỉ, chỉ nghĩ cho mình', 'KHÔNG PHẢI']], 'Nhân ái thể hiện qua hành động cụ thể: giúp đỡ, chia sẻ, đồng cảm.', $d);

        $this->fill($s, 'Bài văn bàn về quan niệm sống, phẩm chất đạo đức thuộc dạng nghị luận về tư tưởng đạo ___.', [[0, 'lí']], 'Tư tưởng đạo lí: lòng nhân ái, kiên trì, trung thực, yêu nước...', $d);
        $this->fill($s, 'Thao tác dùng lí lẽ và dẫn chứng để làm sáng tỏ luận điểm gọi là chứng ___.', [[0, 'minh']], 'Chứng minh là "xương sống" của bài nghị luận: nói phải có sách, mách phải có chứng.', $d);
        $this->fill($s, 'Lí lẽ và dẫn chứng dùng để làm sáng tỏ luận điểm gọi chung là luận ___.', [[0, 'cứ']], 'Luận cứ càng tiêu biểu, xác thực thì sức thuyết phục càng cao.', $d);
        $this->fill($s, 'Phần cuối bài văn khẳng định lại vấn đề và rút ra bài học cho bản thân gọi là ___ bài.', [[0, 'kết']], 'Kết bài "chốt" lại vấn đề và mở ra bài học nhận thức, hành động.', $d);
        $this->fill($s, 'Dẫn chứng trong bài nghị luận phải tiêu biểu, xác thực và ___ hợp với luận điểm.', [[0, 'phù']], 'Dẫn chứng lạc đề, chung chung làm bài văn mất điểm dù lí lẽ hay.', $d);
    }

    /* ============ ngu-van-thpt-11-tu-tuong-dao-li-2 (Ngữ văn 11, trung_binh) ============ */
    private function seedNguVanThpt11TuTuong2(): void
    {
        $s = 'ngu-van-thpt-11-tu-tuong-dao-li-2'; $d = 'trung_binh';
        $this->quiz($s, 'Khi phân tích đề "Bàn về lòng biết ơn", việc đầu tiên cần làm là gì?', ['Viết ngay mở bài', 'Xác định vấn đề nghị luận và phạm vi bàn luận', 'Tìm dẫn chứng thật nhiều', 'Viết kết bài trước'], 1, 'Phân tích đề: xác định đúng vấn đề (biết ơn là gì, biểu hiện, ý nghĩa) tránh lạc đề.', $d);
        $this->quiz($s, 'Dàn ý phần thân bài cho đề tư tưởng đạo lí thường gồm những ý nào?', ['Giải thích – biểu hiện – ý nghĩa – bài học', 'Chỉ kể chuyện', 'Chỉ nêu dẫn chứng', 'Mở bài – kết bài'], 0, 'Dàn ý kinh điển: giải thích khái niệm → biểu hiện → ý nghĩa → phản đề → bài học.', $d);
        $this->quiz($s, 'Thao tác "phản đề" (lật lại vấn đề) trong bài nghị luận có tác dụng gì?', ['Làm bài văn dài hơn', 'Xem xét mặt trái, làm lập luận sâu sắc, toàn diện', 'Phủ nhận hoàn toàn luận điểm', 'Thay thế phần kết bài'], 1, 'Phản đề: phê phán biểu hiện trái ngược (vô ơn, ích kỉ) để khẳng định mặt đúng đắn.', $d);
        $this->quiz($s, 'Câu chuyển đoạn trong bài văn nghị luận có tác dụng gì?', ['Trang trí cho đẹp', 'Liên kết các đoạn, dẫn dắt mạch lập luận', 'Thay thế luận điểm', 'Kết thúc bài văn'], 1, 'Câu chuyển đoạn là "cây cầu" nối các luận điểm, giúp bài văn mạch lạc.', $d);
        $this->quiz($s, 'Bài học rút ra ở kết bài nên hướng tới đối tượng nào?', ['Chỉ tác giả', 'Bản thân người viết và mọi người', 'Chỉ thầy cô', 'Không cần đối tượng'], 1, 'Bài học phải chân thành, gắn với bản thân ("em sẽ...") mới thuyết phục.', $d);

        $this->matching($s, 'Hãy nối mỗi phần của thân bài với nội dung cần triển khai.', [['Giải thích', 'Làm rõ khái niệm (biết ơn là gì?)'], ['Biểu hiện', 'Nêu việc làm cụ thể trong đời sống'], ['Ý nghĩa', 'Vai trò đối với cá nhân, xã hội'], ['Bài học', 'Nhận thức và hành động của bản thân']], 'Dàn ý 4 ý: giải thích – biểu hiện – ý nghĩa – bài học là khung vững chắc.', $d);
        $this->matching($s, 'Hãy nối mỗi lỗi thường gặp với cách khắc phục.', [['Lạc đề', 'Phân tích kĩ đề trước khi viết'], ['Dẫn chứng chung chung', 'Dùng dẫn chứng cụ thể, tiêu biểu'], ['Thiếu liên kết', 'Dùng câu chuyển đoạn'], ['Kết bài sáo rỗng', 'Rút bài học chân thành cho bản thân']], 'Bốn lỗi "kinh điển" và cách chữa — thuộc lòng để tránh mất điểm oan.', $d);
        $this->matching($s, 'Nối mỗi dạng đề với hướng triển khai phù hợp.', [['"Bàn về lòng dũng cảm"', 'Giải thích – biểu hiện – ý nghĩa'], ['"Suy nghĩ về câu tục ngữ..."', 'Giải thích nghĩa đen, nghĩa bóng'], ['"Bàn về tính khiêm tốn"', 'Nêu biểu hiện, phản đề kiêu ngạo'], ['"Bàn về tình bạn"', 'Ý nghĩa, cách giữ gìn']], 'Mỗi dạng đề có hướng đi riêng; nắm được sẽ không bị "bí" khi làm bài.', $d);
        $this->matching($s, 'Hãy nối mỗi câu nói với bài học có thể rút ra.', [['"Ăn quả nhớ kẻ trồng cây"', 'Lòng biết ơn'], ['"Thất bại là mẹ thành công"', 'Không nản chí'], ['"Học thầy không tày học bạn"', 'Học hỏi mọi người'], ['"Gần mực thì đen"', 'Chọn bạn mà chơi']], 'Tục ngữ, danh ngôn vừa là dẫn chứng vừa gợi ra bài học sâu sắc.', $d);
        $this->matching($s, 'Nối mỗi thao tác với ví dụ minh họa.', [['Giải thích', '"Biết ơn là ghi nhớ công ơn..."'], ['Chứng minh', '"Câu chuyện anh hùng..."'], ['Phân tích', '"Biết ơn gồm: nhớ ơn, đền ơn..."'], ['Bình luận', '"Đó là đạo lí tốt đẹp cần giữ gìn."']], 'Nhận diện thao tác qua câu văn giúp học cách viết từng đoạn văn.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi cách mở bài vào nhóm MỞ BÀI HAY hoặc MỞ BÀI CHƯA HAY.', [['"Trong cuộc sống, lòng biết ơn là...", dẫn từ câu chuyện cụ thể', 'MỞ BÀI HAY'], ['"Hôm nay em sẽ bàn về lòng biết ơn."', 'MỞ BÀI CHƯA HAY'], ['Dẫn danh ngôn rồi vào vấn đề', 'MỞ BÀI HAY'], ['"Đề bài yêu cầu bàn về..."', 'MỞ BÀI CHƯA HAY']], 'Mở bài hay: dẫn dắt tự nhiên (chuyện, danh ngôn). Chưa hay: thông báo khô khan.', $d);
        $this->sortQ($s, 'Kéo mỗi dẫn chứng vào nhóm PHÙ HỢP hoặc KHÔNG PHÙ HỢP với đề "bàn về lòng hiếu thảo".', [['Câu chuyện con chăm sóc mẹ già', 'PHÙ HỢP'], ['Trận bóng đá hay', 'KHÔNG PHÙ HỢP'], ['Tấm gương học sinh phụng dưỡng cha mẹ', 'PHÙ HỢP'], ['Món ăn ngon', 'KHÔNG PHÙ HỢP']], 'Dẫn chứng phải "trúng" vấn đề nghị luận, không phải hay là được.', $d);
        $this->sortQ($s, 'Kéo mỗi ý vào nhóm THUỘC PHẦN GIẢI THÍCH hoặc THUỘC PHẦN BÀI HỌC.', [['"Biết ơn là ghi nhớ công ơn."', 'THUỘC PHẦN GIẢI THÍCH'], ['"Em sẽ cố gắng học tốt để đền đáp."', 'THUỘC PHẦN BÀI HỌC'], ['"Trung thực là không gian dối."', 'THUỘC PHẦN GIẢI THÍCH'], ['"Mỗi người cần rèn tính trung thực."', 'THUỘC PHẦN BÀI HỌC']], 'Giải thích: làm rõ khái niệm. Bài học: bản thân sẽ làm gì.', $d);
        $this->sortQ($s, 'Kéo mỗi biểu hiện vào nhóm CỦA LÒNG BIẾT ƠN hoặc CỦA SỰ VÔ ƠN.', [['Nhớ ơn thầy cô', 'CỦA LÒNG BIẾT ƠN'], ['Quên công cha mẹ', 'CỦA SỰ VÔ ƠN'], ['Đền đáp người giúp mình', 'CỦA LÒNG BIẾT ƠN'], ['Vô cảm với ân nhân', 'CỦA SỰ VÔ ƠN']], 'Phản đề vô ơn giúp khẳng định giá trị của lòng biết ơn.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU CHUYỂN ĐOẠN TỐT hoặc CHƯA TỐT.', [['"Không chỉ vậy, lòng biết ơn còn..."', 'CÂU CHUYỂN ĐOẠN TỐT'], ['"Sang đoạn tiếp theo."', 'CHƯA TỐT'], ['"Bên cạnh ý nghĩa đó..."', 'CÂU CHUYỂN ĐOẠN TỐT'], ['"Hết."', 'CHƯA TỐT']], 'Câu chuyển đoạn tốt vừa nối ý cũ vừa mở ý mới một cách tự nhiên.', $d);

        $this->fill($s, 'Trước khi viết bài, cần phân ___ đề để xác định đúng vấn đề nghị luận.', [[0, 'tích']], 'Phân tích đề: tìm từ khóa, xác định vấn đề, phạm vi và yêu cầu của đề.', $d);
        $this->fill($s, 'Thao tác xem xét mặt trái của vấn đề (phê phán biểu hiện sai trái) gọi là phản ___.', [[0, 'đề']], 'Phản đề làm bài văn sâu sắc, tránh một chiều, phiến diện.', $d);
        $this->fill($s, 'Câu nối các đoạn văn, dẫn dắt mạch lập luận gọi là câu chuyển ___.', [[0, 'đoạn']], 'Thiếu câu chuyển đoạn, bài văn rời rạc như "gạch xếp chồng".', $d);
        $this->fill($s, 'Dàn ý thân bài nghị luận tư tưởng thường có: giải thích, biểu hiện, ý nghĩa và bài ___.', [[0, 'học']], 'Bốn ý này tạo thành "bộ khung" vững chắc cho mọi đề tư tưởng đạo lí.', $d);
        $this->fill($s, 'Kết bài cần khẳng định lại vấn đề và rút ra bài học nhận thức, hành ___ cho bản thân.', [[0, 'động']], 'Bài học hành động ("em sẽ...") thể hiện sự chân thành, thuyết phục.', $d);
    }

    /* ============ ngu-van-thpt-11-hien-tuong-doi-song-1 (Ngữ văn 11, trung_binh) ============ */
    private function seedNguVanThpt11HienTuong1(): void
    {
        $s = 'ngu-van-thpt-11-hien-tuong-doi-song-1'; $d = 'trung_binh';
        $this->quiz($s, 'Đề "Suy nghĩ về hiện tượng xả rác bừa bãi" thuộc dạng nghị luận nào?', ['Tư tưởng đạo lí', 'Hiện tượng đời sống', 'Nghị luận văn học', 'Thuyết minh'], 1, '"Xả rác bừa bãi" là sự việc cụ thể, nổi bật trong đời sống — thuộc hiện tượng đời sống.', $d);
        $this->quiz($s, 'Khi bắt tay làm bài nghị luận về hiện tượng đời sống, việc cần làm trước tiên là gì?', ['Nêu giải pháp ngay', 'Mô tả, nhận diện hiện tượng', 'Kết luận', 'Phê phán'], 1, 'Phải mô tả hiện tượng (là gì, biểu hiện ra sao) trước khi đánh giá, tìm nguyên nhân.', $d);
        $this->quiz($s, 'Khi đánh giá hiện tượng đời sống, cần đảm bảo yêu cầu gì?', ['Chỉ nêu mặt tốt', 'Chỉ nêu mặt xấu', 'Nhìn nhận khách quan, nhiều chiều', 'Tránh đưa ra ý kiến'], 2, 'Đánh giá khách quan: thấy cả mặt tích cực và tiêu cực (nếu có), tránh phiến diện.', $d);
        $this->quiz($s, 'Dẫn chứng cho bài nghị luận về hiện tượng đời sống thường lấy từ đâu?', ['Truyện cổ tích', 'Thực tế đời sống, báo chí', 'Thần thoại', 'Truyện cười'], 1, 'Hiện tượng đời sống cần dẫn chứng thực tế: số liệu, sự việc báo chí đã đưa.', $d);
        $this->quiz($s, 'Hiện tượng "sống ảo" của giới trẻ hiện nay mang tính chất gì?', ['Hoàn toàn tích cực', 'Hoàn toàn tiêu cực', 'Có cả mặt tích cực và tiêu cực', 'Không đáng bàn'], 2, 'Sống ảo giúp kết nối nhưng cũng gây lãng phí thời gian, xa rời thực tế — cần nhìn hai mặt.', $d);

        $this->matching($s, 'Hãy nối mỗi hiện tượng với tính chất của nó.', [['Hiến máu nhân đạo', 'Tích cực'], ['Bạo lực học đường', 'Tiêu cực'], ['Tai nạn giao thông', 'Tiêu cực'], ['Tình nguyện mùa hè xanh', 'Tích cực']], 'Xác định tính chất hiện tượng là bước đầu để định hướng đánh giá.', $d);
        $this->matching($s, 'Hãy nối mỗi bước làm bài với việc cần làm.', [['Mô tả hiện tượng', 'Nêu biểu hiện cụ thể'], ['Đánh giá', 'Nhận xét mặt tốt, mặt xấu'], ['Tìm nguyên nhân', 'Lí giải vì sao xảy ra'], ['Đề xuất giải pháp', 'Nêu cách khắc phục']], 'Dàn ý 4 bước: mô tả – đánh giá – nguyên nhân – giải pháp.', $d);
        $this->matching($s, 'Hãy nối mỗi hiện tượng với biểu hiện cụ thể.', [['Xả rác bừa bãi', 'Vứt rác xuống đường, kênh rạch'], ['Bạo lực học đường', 'Đánh nhau, bắt nạt bạn'], ['Sống ảo', 'Chụp ảnh khoe mẽ, câu like'], ['Gian lận thi cử', 'Quay cóp, mang tài liệu']], 'Mô tả hiện tượng phải cụ thể, sinh động bằng những biểu hiện dễ thấy.', $d);
        $this->matching($s, 'Hãy nối mỗi hiện tượng tích cực với ý nghĩa của nó.', [['Hiến máu nhân đạo', 'Cứu người, lan tỏa yêu thương'], ['Trồng cây gây rừng', 'Bảo vệ môi trường'], ['Giúp đỡ người nghèo', 'Sẻ chia, gắn kết cộng đồng'], ['Học tập tốt', 'Phát triển bản thân, cống hiến']], 'Hiện tượng tích cực cần được ngợi ca và nhân rộng.', $d);
        $this->matching($s, 'Nối mỗi nguồn dẫn chứng với độ tin cậy.', [['Báo chí chính thống', 'Đáng tin cậy'], ['Tin đồn mạng xã hội', 'Cần kiểm chứng'], ['Số liệu thống kê', 'Đáng tin cậy'], ['Lời kể không rõ nguồn', 'Cần kiểm chứng']], 'Dẫn chứng thực tế phải có nguồn rõ ràng, đáng tin cậy.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi hiện tượng vào nhóm TÍCH CỰC hoặc TIÊU CỰC.', [['Nhặt được của rơi trả lại', 'TÍCH CỰC'], ['Quay cóp trong thi cử', 'TIÊU CỰC'], ['Tham gia tình nguyện', 'TÍCH CỰC'], ['Nói tục, chửi bậy', 'TIÊU CỰC'], ['Giúp bạn học yếu', 'TÍCH CỰC'], ['Bắt nạt bạn bè', 'TIÊU CỰC']], 'Phân loại tính chất giúp định hướng thái độ đánh giá đúng đắn.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi việc làm vào nhóm BIỂU HIỆN CỦA LỐI SỐNG ĐẸP hoặc KHÔNG PHẢI.', [['Xếp hàng nơi công cộng', 'BIỂU HIỆN CỦA LỐI SỐNG ĐẸP'], ['Chen lấn, xô đẩy', 'KHÔNG PHẢI'], ['Giữ gìn vệ sinh chung', 'BIỂU HIỆN CỦA LỐI SỐNG ĐẸP'], ['Vẽ bậy lên tường', 'KHÔNG PHẢI']], 'Lối sống đẹp thể hiện qua những hành vi văn minh hằng ngày.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi nhận định vào nhóm ĐÁNH GIÁ KHÁCH QUAN hoặc PHIẾN DIỆN.', [['"Game có mặt tốt và mặt xấu."', 'ĐÁNH GIÁ KHÁCH QUAN'], ['"Game chỉ toàn hại."', 'PHIẾN DIỆN'], ['"Mạng xã hội vừa kết nối vừa gây nghiện."', 'ĐÁNH GIÁ KHÁCH QUAN'], ['"Mạng xã hội chẳng có ích gì."', 'PHIẾN DIỆN']], 'Đánh giá khách quan nhìn nhiều chiều; phiến diện chỉ thấy một mặt.', $d);
        $this->sortQ($s, 'Kéo mỗi ý vào nhóm THUỘC PHẦN MÔ TẢ hoặc THUỘC PHẦN ĐÁNH GIÁ.', [['"Hiện tượng diễn ra phổ biến ở..."', 'THUỘC PHẦN MÔ TẢ'], ['"Hiện tượng này rất đáng lên án vì..."', 'THUỘC PHẦN ĐÁNH GIÁ'], ['"Biểu hiện cụ thể là..."', 'THUỘC PHẦN MÔ TẢ'], ['"Hậu quả của nó là..."', 'THUỘC PHẦN ĐÁNH GIÁ']], 'Mô tả: hiện tượng là gì, ra sao. Đánh giá: tốt hay xấu, hậu quả gì.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn tin vào nhóm ĐÁNG TIN CẬY hoặc CẦN KIỂM CHỨNG khi lấy dẫn chứng.', [['Báo chí chính thống', 'ĐÁNG TIN CẬY'], ['Tin nhắn lan truyền', 'CẦN KIỂM CHỨNG'], ['Số liệu cơ quan chức năng', 'ĐÁNG TIN CẬY'], ['Bình luận ẩn danh', 'CẦN KIỂM CHỨNG']], 'Dẫn chứng từ nguồn không rõ ràng làm giảm sức thuyết phục của bài văn.', $d);

        $this->fill($s, 'Dạng nghị luận bàn về sự việc nổi bật trong đời sống gọi là nghị luận về hiện tượng đời ___.', [[0, 'sống']], 'Hiện tượng đời sống: bạo lực học đường, ô nhiễm môi trường, sống ảo...', $d);
        $this->fill($s, 'Bước đầu tiên khi làm bài là mô tả và nhận ___ hiện tượng.', [[0, 'diện']], 'Nhận diện: hiện tượng là gì, biểu hiện cụ thể ra sao, ở đâu.', $d);
        $this->fill($s, 'Khi đánh giá hiện tượng cần nhìn nhận khách ___, thấy cả mặt tốt và mặt xấu.', [[0, 'quan']], 'Khách quan, nhiều chiều — tránh khen quá hoặc chê quá một chiều.', $d);
        $this->fill($s, 'Dẫn chứng cho dạng bài này nên lấy từ thực ___ đời sống có thật.', [[0, 'tế']], 'Số liệu, sự việc có thật từ báo chí làm bài văn thuyết phục.', $d);
        $this->fill($s, 'Hiện tượng tiêu cực cần bị phê phán, lên án; hiện tượng tích cực cần được ngợi ca, nhân ___.', [[0, 'rộng']], 'Thái độ rõ ràng: cái tốt nhân rộng, cái xấu đẩy lùi.', $d);
    }

    /* ============ ngu-van-thpt-11-hien-tuong-doi-song-2 (Ngữ văn 11, trung_binh) ============ */
    private function seedNguVanThpt11HienTuong2(): void
    {
        $s = 'ngu-van-thpt-11-hien-tuong-doi-song-2'; $d = 'trung_binh';
        $this->quiz($s, 'Nguyên nhân chủ quan của hiện tượng học sinh quay cóp là gì?', ['Đề thi quá khó', 'Ý thức kém, lười học của học sinh', 'Phòng thi rộng', 'Giám thị ít'], 1, 'Nguyên nhân chủ quan đến từ chính con người: ý thức, thái độ, thói quen.', $d);
        $this->quiz($s, 'Nguyên nhân khách quan của hiện tượng ô nhiễm môi trường là gì?', ['Ý thức người dân kém', 'Công nghiệp phát triển thiếu kiểm soát', 'Người dân lười biếng', 'Thiếu ý thức cá nhân'], 1, 'Nguyên nhân khách quan đến từ điều kiện bên ngoài: quản lí, công nghệ, kinh tế.', $d);
        $this->quiz($s, 'Giải pháp khắc phục hiện tượng tiêu cực cần đảm bảo yêu cầu gì?', ['Thật nhiều giải pháp', 'Khả thi, cụ thể, phù hợp từng đối tượng', 'Thật gay gắt', 'Chỉ cần một giải pháp'], 1, 'Giải pháp phải làm được trong thực tế, rõ người thực hiện và cách thực hiện.', $d);
        $this->quiz($s, 'Với hiện tượng tích cực như "hiến máu nhân đạo", bài văn nên tập trung vào điều gì?', ['Phê phán', 'Ngợi ca ý nghĩa và cách nhân rộng', 'Tìm nguyên nhân xấu', 'Bàn hậu quả'], 1, 'Hiện tượng tích cực: phân tích ý nghĩa, biểu dương và đề xuất nhân rộng.', $d);
        $this->quiz($s, 'Phần liên hệ bản thân trong bài nghị luận về hiện tượng đời sống có tác dụng gì?', ['Làm bài dài hơn', 'Thể hiện nhận thức và trách nhiệm của người viết', 'Thay thế kết bài', 'Kể chuyện cá nhân'], 1, 'Liên hệ bản thân ("em sẽ...") thể hiện trách nhiệm công dân, làm bài văn chân thành.', $d);

        $this->matching($s, 'Hãy nối mỗi hiện tượng với nguyên nhân chủ yếu.', [['Quay cóp thi cử', 'Ý thức học sinh kém'], ['Ô nhiễm kênh rạch', 'Xả rác bừa bãi'], ['Tai nạn giao thông', 'Vi phạm luật, phóng nhanh'], ['Bạo lực học đường', 'Thiếu kĩ năng sống, ảnh hưởng xấu']], 'Tìm đúng nguyên nhân mới đề xuất được giải pháp trúng đích.', $d);
        $this->matching($s, 'Hãy nối mỗi hiện tượng với giải pháp phù hợp.', [['Xả rác bừa bãi', 'Tuyên truyền + xử phạt'], ['Quay cóp', 'Giáo dục ý thức + kỉ luật nghiêm'], ['Nghiện game', 'Quản lí thời gian, tìm thú vui lành mạnh'], ['Bạo lực học đường', 'Giáo dục kĩ năng sống, phối hợp gia đình – trường']], 'Giải pháp phải "đúng bệnh": mỗi hiện tượng cần cách chữa riêng.', $d);
        $this->matching($s, 'Hãy nối mỗi đối tượng với trách nhiệm trong giải pháp.', [['Học sinh', 'Tự giác chấp hành'], ['Nhà trường', 'Giáo dục, quản lí'], ['Gia đình', 'Quan tâm, dạy dỗ'], ['Xã hội', 'Tạo môi trường lành mạnh']], 'Khắc phục hiện tượng tiêu cực cần sự chung tay của nhiều phía.', $d);
        $this->matching($s, 'Hãy nối mỗi hiện tượng với bài học rút ra.', [['Lãng phí thức ăn', 'Trân trọng thành quả lao động'], ['Vô cảm', 'Sống yêu thương, sẻ chia'], ['Gian lận', 'Trung thực là vốn quý'], ['Ỷ lại', 'Tự lập, tự cường']], 'Mỗi hiện tượng tiêu cực đều để lại một bài học cho người trẻ.', $d);
        $this->matching($s, 'Nối mỗi nhóm nguyên nhân với ví dụ.', [['Chủ quan', 'Lười học nên quay cóp'], ['Khách quan', 'Thiếu thùng rác công cộng'], ['Chủ quan', 'Thích thể hiện nên bạo lực'], ['Khách quan', 'Luật xử phạt chưa nghiêm']], 'Chủ quan: do con người. Khách quan: do điều kiện, quản lí.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi nguyên nhân vào nhóm CHỦ QUAN hoặc KHÁCH QUAN.', [['Ý thức kém', 'CHỦ QUAN'], ['Thiếu chế tài xử phạt', 'KHÁCH QUAN'], ['Lười biếng', 'CHỦ QUAN'], ['Quản lí lỏng lẻo', 'KHÁCH QUAN'], ['Ích kỉ cá nhân', 'CHỦ QUAN'], ['Ảnh hưởng mạng xã hội xấu', 'KHÁCH QUAN']], 'Chủ quan từ bên trong con người; khách quan từ môi trường, thể chế.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi giải pháp vào nhóm KHẢ THI hoặc CHUNG CHUNG.', [['"Mỗi lớp đặt thùng rác phân loại."', 'KHẢ THI'], ['"Mọi người hãy có ý thức."', 'CHUNG CHUNG'], ['"Tổ chức ngày chủ nhật xanh hằng tháng."', 'KHẢ THI'], ['"Cần thay đổi nhận thức xã hội."', 'CHUNG CHUNG']], 'Giải pháp khả thi: cụ thể, làm được, rõ người thực hiện.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi ý vào nhóm THUỘC PHẦN NGUYÊN NHÂN hoặc THUỘC PHẦN GIẢI PHÁP.', [['"Do ý thức người dân còn hạn chế."', 'THUỘC PHẦN NGUYÊN NHÂN'], ['"Cần tăng cường tuyên truyền."', 'THUỘC PHẦN GIẢI PHÁP'], ['"Một phần do quản lí lỏng lẻo."', 'THUỘC PHẦN NGUYÊN NHÂN'], ['"Nhà trường nên tổ chức..."', 'THUỘC PHẦN GIẢI PHÁP']], 'Nguyên nhân: vì sao xảy ra. Giải pháp: làm gì để khắc phục.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi hành động vào nhóm GÓP PHẦN KHẮC PHỤC hoặc LÀM TRẦM TRỌNG THÊM.', [['Tố giác hành vi gian lận', 'GÓP PHẦN KHẮC PHỤC'], ['Bao che cho bạn quay cóp', 'LÀM TRẦM TRỌNG THÊM'], ['Tham gia dọn vệ sinh', 'GÓP PHẦN KHẮC PHỤC'], ['Xả rác nơi công cộng', 'LÀM TRẦM TRỌNG THÊM']], 'Mỗi hành động nhỏ của cá nhân đều ảnh hưởng đến hiện tượng chung.', $d);
        $this->sortQ($s, 'Kéo mỗi ý kiến vào nhóm GIẢI PHÁP CHO HỌC SINH hoặc CHO NHÀ TRƯỜNG.', [['"Tự giác không quay cóp."', 'CHO HỌC SINH'], ['"Tăng cường giám thị coi thi."', 'CHO NHÀ TRƯỜNG'], ['"Lập nhóm học tập giúp nhau."', 'CHO HỌC SINH'], ['"Tổ chức tuyên truyền ý thức."', 'CHO NHÀ TRƯỜNG']], 'Giải pháp phân theo đối tượng thực hiện mới cụ thể, khả thi.', $d);

        $this->fill($s, 'Nguyên nhân của hiện tượng gồm nguyên nhân chủ ___ và nguyên nhân khách quan.', [[0, 'quan']], 'Chủ quan: do con người. Khách quan: do hoàn cảnh, điều kiện bên ngoài.', $d);
        $this->fill($s, 'Giải pháp đề xuất phải cụ thể, khả ___ và phù hợp với từng đối tượng.', [[0, 'thi']], 'Giải pháp "trên trời" không làm được sẽ bị đánh giá là sáo rỗng.', $d);
        $this->fill($s, 'Với hiện tượng tích cực, cần nêu ý nghĩa và cách nhân ___ hiện tượng.', [[0, 'rộng']], 'Lan tỏa điều tốt đẹp là trách nhiệm của mỗi người, nhất là người trẻ.', $d);
        $this->fill($s, 'Phần liên ___ bản thân giúp bài văn thêm sâu sắc và chân thành.', [[0, 'hệ']], '"Em sẽ..." — lời hứa hành động của người viết trước hiện tượng xã hội.', $d);
        $this->fill($s, 'Khắc phục hiện tượng tiêu cực cần sự chung tay của gia đình, nhà trường và toàn xã ___.', [[0, 'hội']], 'Không ai đứng ngoài cuộc trước những vấn đề chung của cộng đồng.', $d);
    }

    /* ============ ngu-van-thpt-12-phan-tich-van-xuoi-1 (Ngữ văn 12, kho) ============ */
    private function seedNguVanThpt12VanXuoi1(): void
    {
        $s = 'ngu-van-thpt-12-phan-tich-van-xuoi-1'; $d = 'kho';
        $this->quiz($s, 'Khi phân tích nhân vật Chí Phèo, chi tiết nào thể hiện rõ nhất bi kịch bị cự tuyệt quyền làm người?', ['Chí ăn vạ', 'Chí khóc khi bị Thị Nở từ chối', 'Chí uống rượu', 'Chí chửi đời'], 1, 'Tiếng khóc của Chí khi bị cự tuyệt là đỉnh điểm bi kịch: khao khát lương thiện bị dập tắt.', $d);
        $this->quiz($s, 'Tình huống "nhặt vợ" trong "Vợ nhặt" của Kim Lân có ý nghĩa gì?', ['Tạo tiếng cười', 'Bộc lộ khát vọng sống, tình người trong nạn đói', 'Kéo dài truyện', 'Giới thiệu phong tục'], 1, 'Giữa nạn đói thảm khốc, việc Tràng "nhặt" được vợ thắp lên niềm tin vào sự sống, tình người.', $d);
        $this->quiz($s, 'Độc thoại nội tâm khác lời thoại ở điểm nào?', ['Dài hơn', 'Là lời thầm trong tâm trí, không phát ra thành tiếng', 'Có nhiều người nghe', 'Viết bằng thơ'], 1, 'Độc thoại nội tâm bộc lộ trực tiếp đời sống tâm hồn nhân vật mà lời thoại không thể hiện hết.', $d);
        $this->quiz($s, 'Khi phân tích diễn biến tâm lí nhân vật, cần chú ý điều gì?', ['Chỉ tả ngoại hình', 'Đặt tâm lí trong hoàn cảnh, tình huống cụ thể', 'Đoán mò cảm xúc', 'Bỏ qua chi tiết'], 1, 'Tâm lí không trừu tượng: nó nảy sinh, vận động trong hoàn cảnh và tình huống nhất định.', $d);
        $this->quiz($s, 'Chi tiết "bát cháo hành" trong "Chí Phèo" có vai trò gì trong phân tích nhân vật?', ['Chỉ là món ăn', 'Thể hiện tình người, khơi dậy lương thiện trong Chí', 'Làm truyện dài hơn', 'Giới thiệu ẩm thực'], 1, 'Bát cháo hành của Thị Nở là chi tiết nghệ thuật: tình thương đánh thức phần người trong Chí.', $d);

        $this->matching($s, 'Hãy nối mỗi phương diện với cách phân tích nhân vật.', [['Ngoại hình', 'Miêu tả dáng vẻ, gắn với số phận'], ['Hành động', 'Việc làm bộc lộ tính cách'], ['Lời nói', 'Thoại, độc thoại bộc lộ tâm lí'], ['Suy nghĩ', 'Độc thoại nội tâm, giấc mơ']], 'Bốn phương diện khắc họa nhân vật: nhìn (ngoại hình) – làm (hành động) – nói – nghĩ.', $d);
        $this->matching($s, 'Nối mỗi loại tình huống với tác dụng khi phân tích.', [['Tình huống éo le', 'Thử thách, bộc lộ bản chất'], ['Tình huống bất ngờ', 'Tạo kịch tính, hé mở chủ đề'], ['Tình huống có vấn đề', 'Đặt ra câu hỏi tư tưởng'], ['Tình huống tâm lí', 'Khám phá đời sống nội tâm']], 'Tình huống là "phòng thí nghiệm" của nhà văn để thử nghiệm nhân vật.', $d);
        $this->matching($s, 'Nối mỗi nhận xét với phương diện phân tích phù hợp.', [['"Chí Phèo khóc nức nở"', 'Diễn biến tâm lí'], ['"Thị Nở xấu ma chê quỷ hờn"', 'Ngoại hình'], ['"Tràng cười tủm tỉm"', 'Hành động, cử chỉ'], ['"Tôi muốn làm người lương thiện"', 'Lời nói, khát vọng']], 'Mỗi chi tiết thuộc một phương diện; phân loại đúng giúp phân tích có hệ thống.', $d);
        $this->matching($s, 'Nối mỗi bước phân tích nhân vật với việc cần làm.', [['Giới thiệu', 'Đặt nhân vật trong tác phẩm'], ['Phân tích', 'Làm rõ tính cách qua dẫn chứng'], ['Đánh giá', 'Nhận xét vai trò, ý nghĩa'], ['Kết luận', 'Khái quát giá trị nhân vật']], 'Trình tự phân tích: giới thiệu → phân tích → đánh giá → kết luận.', $d);
        $this->matching($s, 'Nối mỗi chi tiết trong "Vợ nhặt" với ý nghĩa.', [['"Nồi cháo cám"', 'Tình mẹ con, niềm tin'], ['"Lá cờ đỏ"', 'Khát vọng đổi đời'], ['"Bà cụ Tứ"', 'Tình mẫu tử thiêng liêng'], ['"Tràng"', 'Khát vọng hạnh phúc']], 'Mỗi chi tiết trong "Vợ nhặt" đều hướng về chủ đề: khát vọng sống và tình người.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi chi tiết vào nhóm KHẮC HỌA NGOẠI HÌNH hoặc KHẮC HỌA TÂM LÍ.', [['"Mặt Chí méo xệch"', 'KHẮC HỌA NGOẠI HÌNH'], ['"Chí khóc nức nở"', 'KHẮC HỌA TÂM LÍ'], ['"Thị Nở xấu xí"', 'KHẮC HỌA NGOẠI HÌNH'], ['"Tràng băn khoăn"', 'KHẮC HỌA TÂM LÍ'], ['"Bà cụ Tứ rưng rưng"', 'KHẮC HỌA TÂM LÍ'], ['"Lão Hạc gầy gò"', 'KHẮC HỌA NGOẠI HÌNH']], 'Ngoại hình: cái nhìn thấy. Tâm lí: cái cảm nhận qua hành động, lời nói.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi biểu hiện vào nhóm TÍNH CÁCH TỐT hoặc TÍNH CÁCH XẤU của nhân vật.', [['Bá Kiến gian hùng', 'TÍNH CÁCH XẤU'], ['Thị Nở chân thành', 'TÍNH CÁCH TỐT'], ['Lí Cường hống hách', 'TÍNH CÁCH XẤU'], ['Bà cụ Tứ nhân hậu', 'TÍNH CÁCH TỐT']], 'Đánh giá tính cách phải dựa trên hành động cụ thể trong tác phẩm.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi ý vào nhóm LUẬN ĐIỂM PHÂN TÍCH hoặc DẪN CHỨNG.', [['"Chí Phèo khao khát lương thiện."', 'LUẬN ĐIỂM PHÂN TÍCH'], ['"Hắn khóc khi bị từ chối."', 'DẪN CHỨNG'], ['"Tràng giàu tình người."', 'LUẬN ĐIỂM PHÂN TÍCH'], ['"Tràng chia sẻ thức ăn."', 'DẪN CHỨNG']], 'Luận điểm là nhận định; dẫn chứng là chi tiết trong tác phẩm chứng minh nhận định.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm CHÍNH DIỆN hoặc PHẢN DIỆN.', [['Chí Phèo (khát vọng lương thiện)', 'CHÍNH DIỆN'], ['Bá Kiến', 'PHẢN DIỆN'], ['Bà cụ Tứ', 'CHÍNH DIỆN'], ['Lí Cường', 'PHẢN DIỆN']], 'Chính diện mang lí tưởng, tình người; phản diện đại diện cái xấu, cái ác.', $d);
        $this->sortQ($s, 'Kéo mỗi lời nói vào nhóm LỜI THOẠI hoặc ĐỘC THOẠI NỘI TÂM.', [['"Tôi muốn làm người lương thiện!"', 'LỜI THOẠI'], ['"Hắn nghĩ thầm: đời mình rồi sẽ ra sao?"', 'ĐỘC THOẠI NỘI TÂM'], ['"Bà ơi, con đói quá!"', 'LỜI THOẠI'], ['"Trong lòng nàng dấy lên nỗi nhớ."', 'ĐỘC THOẠI NỘI TÂM']], 'Lời thoại phát ra thành tiếng; độc thoại nội tâm chỉ diễn ra trong tâm trí.', $d);

        $this->fill($s, 'Tính cách nhân vật bộc lộ qua ngoại hình, hành động, lời nói và diễn biến tâm ___.', [[0, 'lí']], 'Bốn phương diện khắc họa nhân vật phải được phân tích toàn diện, không bỏ sót.', $d);
        $this->fill($s, 'Lời thầm trong tâm trí nhân vật, không phát ra thành tiếng gọi là độc thoại nội ___.', [[0, 'tâm']], 'Độc thoại nội tâm là "cửa sổ" nhìn thẳng vào đời sống tinh thần nhân vật.', $d);
        $this->fill($s, 'Hoàn cảnh đặc biệt bộc lộ tính cách và chủ đề gọi là tình ___ truyện.', [[0, 'huống']], 'Phân tích nhân vật không thể tách khỏi tình huống — nơi tính cách bộc lộ rõ nhất.', $d);
        $this->fill($s, 'Mỗi nhận định khi phân tích nhân vật cần có dẫn ___ từ tác phẩm làm sáng tỏ.', [[0, 'chứng']], 'Nhận định không dẫn chứng là cảm tính; dẫn chứng không nhận định là liệt kê.', $d);
        $this->fill($s, 'Chi tiết "bát cháo hành" là chi tiết nghệ thuật thể hiện tình ___ đánh thức lương thiện.', [[0, 'người']], 'Tình người của Thị Nở là "liều thuốc" làm sống dậy phần người trong Chí Phèo.', $d);
    }

    /* ============ ngu-van-thpt-12-phan-tich-van-xuoi-2 (Ngữ văn 12, kho) ============ */
    private function seedNguVanThpt12VanXuoi2(): void
    {
        $s = 'ngu-van-thpt-12-phan-tich-van-xuoi-2'; $d = 'kho';
        $this->quiz($s, 'Giá trị hiện thực của "Chí Phèo" thể hiện ở điểm nào?', ['Tả cảnh đẹp', 'Phản ánh xã hội nông thôn Việt Nam trước Cách mạng với áp bức, tha hóa', 'Kể chuyện tình yêu', 'Ca ngợi thiên nhiên'], 1, 'Nam Cao phơi bày bộ mặt thật của xã hội thực dân phong kiến: người nông dân bị đẩy vào tha hóa.', $d);
        $this->quiz($s, 'Giá trị nhân đạo của "Vợ nhặt" thể hiện qua điều gì?', ['Tố cáo nạn đói', 'Trân trọng khát vọng sống và tình người trong nạn đói', 'Tả cảnh đói kém', 'Kể chuyện ma'], 1, 'Giữa nạn đói, Kim Lân vẫn thấy ánh sáng: tình người, khát vọng hạnh phúc — đó là nhân đạo.', $d);
        $this->quiz($s, 'Khi đánh giá nghệ thuật xây dựng tình huống truyện, cần chú ý điều gì?', ['Tình huống có độc đáo, éo le và giàu ý nghĩa không', 'Tình huống có dài không', 'Tình huống có nhiều nhân vật không', 'Tình huống có hài hước không'], 0, 'Tình huống hay phải độc đáo, bất ngờ mà hợp lí, hàm chứa vấn đề tư tưởng sâu sắc.', $d);
        $this->quiz($s, 'Giá trị nội dung và giá trị nghệ thuật của tác phẩm có mối quan hệ như thế nào?', ['Không liên quan', 'Thống nhất biện chứng: nội dung quyết định, nghệ thuật thể hiện', 'Nghệ thuật quan trọng hơn', 'Nội dung quan trọng hơn hẳn'], 1, 'Nội dung hay cần nghệ thuật hay để thể hiện; nghệ thuật chỉ có giá trị khi phục vụ nội dung.', $d);
        $this->quiz($s, 'Ngôn ngữ nhân vật trong "Chí Phèo" (tiếng chửi) có giá trị nghệ thuật gì?', ['Làm truyện hài hước', 'Bộc lộ bi kịch, tính cách và hiện thực xã hội', 'Kéo dài truyện', 'Trang trí'], 1, 'Tiếng chửi của Chí là "ngôn ngữ bi kịch": vừa tố cáo xã hội vừa bộc lộ khát vọng giao tiếp.', $d);

        $this->matching($s, 'Nối mỗi giá trị với biểu hiện của nó trong "Chí Phèo".', [['Giá trị hiện thực', 'Xã hội nông thôn tha hóa'], ['Giá trị nhân đạo', 'Thương cảm, bênh vực người nông dân'], ['Giá trị tư tưởng', 'Tố cáo xã hội phi nhân'], ['Giá trị nghệ thuật', 'Tình huống, ngôn ngữ độc đáo']], 'Một tác phẩm lớn hội tụ cả bốn giá trị: hiện thực – nhân đạo – tư tưởng – nghệ thuật.', $d);
        $this->matching($s, 'Nối mỗi yếu tố nghệ thuật với tác dụng của nó.', [['Tình huống truyện', 'Tạo kịch tính, bộc lộ chủ đề'], ['Chi tiết nghệ thuật', 'Điểm nhấn giàu sức gợi'], ['Ngôn ngữ', 'Khắc họa tính cách, không khí'], ['Kết cấu', 'Tổ chức mạch truyện hợp lí']], 'Nghệ thuật là "phương tiện" chở nội dung đến với người đọc.', $d);
        $this->matching($s, 'Hãy nối mỗi biểu hiện với giá trị tương ứng.', [['Tố cáo áp bức', 'Giá trị hiện thực'], ['Ngợi ca tình người', 'Giá trị nhân đạo'], ['Tình huống độc đáo', 'Giá trị nghệ thuật'], ['Triết lí nhân sinh', 'Giá trị tư tưởng']], 'Phân biệt các giá trị giúp đánh giá tác phẩm toàn diện, không phiến diện.', $d);
        $this->matching($s, 'Hãy nối mỗi phần của bài nghị luận văn học với nội dung.', [['Mở bài', 'Giới thiệu tác giả, tác phẩm, vấn đề'], ['Thân bài', 'Phân tích, chứng minh'], ['Kết bài', 'Khái quát giá trị'], ['Dẫn chứng', 'Chi tiết trong tác phẩm']], 'Bố cục nghị luận văn học cũng ba phần, nhưng thân bài đi sâu phân tích nghệ thuật.', $d);
        $this->matching($s, 'Nối mỗi tác phẩm với giá trị nổi bật.', [['"Chí Phèo"', 'Tố cáo xã hội, thương người nông dân'], ['"Vợ nhặt"', 'Ngợi ca tình người trong nạn đói'], ['"Hai đứa trẻ"', 'Thương cảm kiếp người tàn tạ'], ['"Chữ người tử tù"', 'Ngợi ca vẻ đẹp thiên lương']], 'Mỗi tác phẩm có một "điểm sáng" giá trị riêng cần nắm vững.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi nhận định vào nhóm GIÁ TRỊ NỘI DUNG hoặc GIÁ TRỊ NGHỆ THUẬT.', [['"Tố cáo xã hội thực dân phong kiến."', 'GIÁ TRỊ NỘI DUNG'], ['"Tình huống truyện độc đáo."', 'GIÁ TRỊ NGHỆ THUẬT'], ['"Ngợi ca tình mẫu tử."', 'GIÁ TRỊ NỘI DUNG'], ['"Ngôn ngữ đặc sắc."', 'GIÁ TRỊ NGHỆ THUẬT'], ['"Thương cảm người nghèo."', 'GIÁ TRỊ NỘI DUNG'], ['"Kết cấu đảo ngược."', 'GIÁ TRỊ NGHỆ THUẬT']], 'Nội dung: tác phẩm nói gì. Nghệ thuật: tác phẩm nói như thế nào.', $d);
        $this->sortQ($s, 'Kéo mỗi biểu hiện vào nhóm THỂ HIỆN GIÁ TRỊ NHÂN ĐẠO hoặc KHÔNG.', [['Đồng cảm với người khổ', 'THỂ HIỆN GIÁ TRỊ NHÂN ĐẠO'], ['Cổ súy bạo lực', 'KHÔNG'], ['Ngợi ca tình người', 'THỂ HIỆN GIÁ TRỊ NHÂN ĐẠO'], ['Khinh miệt người nghèo', 'KHÔNG']], 'Nhân đạo: yêu thương, đồng cảm, đấu tranh cho con người.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi yếu tố vào nhóm NGHỆ THUẬT XÂY DỰNG TRUYỆN hoặc NGHỆ THUẬT NGÔN TỪ.', [['Tình huống', 'NGHỆ THUẬT XÂY DỰNG TRUYỆN'], ['Từ ngữ gợi hình', 'NGHỆ THUẬT NGÔN TỪ'], ['Kết cấu', 'NGHỆ THUẬT XÂY DỰNG TRUYỆN'], ['Giọng điệu', 'NGHỆ THUẬT NGÔN TỪ']], 'Xây dựng truyện: cốt, tình huống, kết cấu. Ngôn từ: từ ngữ, giọng điệu, hình ảnh.', $d);
        $this->sortQ($s, 'Kéo mỗi câu văn vào nhóm LỜI NGƯỜI KỂ CHUYỆN hoặc LỜI NHÂN VẬT.', [['"Làng Vũ Đại ngày ấy..."', 'LỜI NGƯỜI KỂ CHUYỆN'], ['"Tao muốn làm người lương thiện!"', 'LỜI NHÂN VẬT'], ['"Tràng là người nông dân nghèo."', 'LỜI NGƯỜI KỂ CHUYỆN'], ['"U ơi, con đói!"', 'LỜI NHÂN VẬT']], 'Lời người kể dẫn dắt; lời nhân vật bộc lộ trực tiếp tính cách, tâm lí.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm MANG GIÁ TRỊ TỐ CÁO hoặc NGỢI CA.', [['Bá Kiến bóc lột', 'MANG GIÁ TRỊ TỐ CÁO'], ['Bát cháo hành', 'NGỢI CA'], ['Nạn đói thảm khốc', 'MANG GIÁ TRỊ TỐ CÁO'], ['Tình mẫu tử bà cụ Tứ', 'NGỢI CA']], 'Tố cáo cái xấu, ngợi ca cái tốt — hai mặt của giá trị hiện thực và nhân đạo.', $d);

        $this->fill($s, 'Khả năng phản ánh chân thực đời sống xã hội của tác phẩm gọi là giá trị hiện ___.', [[0, 'thực']], 'Giá trị hiện thực: tấm gương phản chiếu đời sống xã hội đương thời.', $d);
        $this->fill($s, 'Tình cảm yêu thương con người, đấu tranh cho con người gọi là giá trị nhân ___.', [[0, 'đạo']], 'Nhân đạo là "trái tim" của văn học: đồng cảm, ngợi ca, đấu tranh.', $d);
        $this->fill($s, 'Nội dung và nghệ thuật có mối quan hệ thống nhất biện ___.', [[0, 'chứng']], 'Nội dung quyết định nghệ thuật; nghệ thuật làm nội dung thêm sâu sắc, hấp dẫn.', $d);
        $this->fill($s, 'Khi đánh giá nghệ thuật truyện cần chú ý tình huống, kết cấu, ngôn ngữ và chi tiết nghệ ___.', [[0, 'thuật']], 'Bốn yếu tố nghệ thuật cơ bản của truyện ngắn cần phân tích toàn diện.', $d);
        $this->fill($s, 'Tiếng chửi của Chí Phèo là ngôn ngữ bi kịch vừa tố cáo xã hội vừa bộc lộ khát ___ làm người.', [[0, 'vọng']], 'Khát vọng lương thiện bị dập tắt — bi kịch lớn nhất của Chí Phèo.', $d);
    }

    /* ============ ngu-van-thpt-12-phan-tich-tho-1 (Ngữ văn 12, kho) ============ */
    private function seedNguVanThpt12Tho1(): void
    {
        $s = 'ngu-van-thpt-12-phan-tich-tho-1'; $d = 'kho';
        $this->quiz($s, 'Tứ thơ trong bài "Sóng" của Xuân Quỳnh là gì?', ['Con sóng biển', 'Khát vọng tình yêu mãnh liệt, chung thủy của người con gái', 'Gió biển', 'Mây trời'], 1, 'Sóng chỉ là hình tượng; tứ thơ là khát vọng yêu tha thiết, chung thủy được gửi qua sóng.', $d);
        $this->quiz($s, 'Hình tượng "sóng" và "em" trong bài "Sóng" có mối quan hệ gì?', ['Không liên quan', 'Song hành, soi chiếu lẫn nhau', 'Đối lập', 'Nhân quả'], 1, 'Sóng – em song hành: sóng diễn tả những trạng thái của tình yêu người con gái.', $d);
        $this->quiz($s, 'Khi phân tích một khổ thơ, yếu tố nào cần được chú ý đầu tiên?', ['Số chữ', 'Từ ngữ, hình ảnh trung tâm của khổ', 'Tên tác giả', 'Năm sáng tác'], 1, 'Từ ngữ, hình ảnh là "chất liệu" trực tiếp tạo nên giá trị của khổ thơ.', $d);
        $this->quiz($s, 'Mạch cảm xúc trong bài "Đây thôn Vĩ Dạ" diễn biến như thế nào?', ['Vui – buồn – vui', 'Nhớ nhung tha thiết → mặc cảm, u hoài', 'Giận dữ → tha thứ', 'Thờ ơ → xúc động'], 1, 'Bài thơ mở đầu bằng nỗi nhớ cảnh, người rồi chìm dần vào mặc cảm chia li, u hoài.', $d);
        $this->quiz($s, 'Câu hỏi tu từ "Sao anh không về chơi thôn Vĩ?" có tác dụng gì?', ['Hỏi để biết đáp án', 'Bộc lộ nỗi nhớ, lời trách yêu và khát vọng đoàn tụ', 'Kể chuyện', 'Tả cảnh'], 1, 'Câu hỏi không cần trả lời mà là tiếng lòng: nhớ nhung, trách móc yêu thương.', $d);

        $this->matching($s, 'Hãy nối mỗi khái niệm với nội dung khi phân tích thơ.', [['Tứ thơ', 'Ý tưởng trung tâm của bài thơ'], ['Hình tượng', 'Hình ảnh mang nghĩa biểu tượng'], ['Mạch cảm xúc', 'Sự vận động của cảm xúc'], ['Giọng điệu', 'Sắc thái tình cảm bao trùm']], 'Bốn khái niệm "vàng" khi phân tích thơ trữ tình hiện đại.', $d);
        $this->matching($s, 'Nối mỗi biện pháp tu từ với tác dụng trong bài "Sóng".', [['Ẩn dụ (sóng – em)', 'Diễn tả tình yêu đa trạng thái'], ['Điệp ngữ ("dữ dội – dịu êm")', 'Nhấn mạnh đối cực của sóng/tình yêu'], ['Câu hỏi tu từ', 'Bộc lộ khát vọng'], ['Nhân hoá', 'Sóng có tâm hồn như con người']], 'Hệ thống tu từ làm nên sức hấp dẫn đặc biệt của bài thơ tình nổi tiếng.', $d);
        $this->matching($s, 'Nối mỗi hình ảnh thiên nhiên với cảm xúc thường gợi trong thơ.', [['Biển', 'Khát vọng lớn lao'], ['Trăng', 'Nỗi nhớ, vẻ đẹp'], ['Mùa thu', 'Nỗi buồn man mác'], ['Dòng sông', 'Chảy trôi, chia li']], 'Thiên nhiên trong thơ luôn mang theo "hành lí" cảm xúc quen thuộc.', $d);
        $this->matching($s, 'Nối mỗi bước phân tích khổ thơ với việc cần làm.', [['Đọc kĩ', 'Cảm nhận chung, xác định cảm xúc'], ['Tìm từ khóa', 'Gạch chân từ ngữ đắt giá'], ['Giải mã', 'Tìm ý nghĩa hình ảnh, tu từ'], ['Khái quát', 'Nêu giá trị của khổ thơ']], 'Phân tích khổ thơ theo trình tự: cảm nhận → tìm từ khóa → giải mã → khái quát.', $d);
        $this->matching($s, 'Nối mỗi cặp "sóng – em" với trạng thái tình yêu.', [['"Dữ dội – dịu êm"', 'Tình yêu nhiều cung bậc'], ['"Ồn ào – lặng lẽ"', 'Khi sôi nổi, khi trầm tư'], ['"Sóng không hiểu nổi mình"', 'Trăn trở về tình yêu'], ['"Sóng tìm ra tận bể"', 'Khát vọng lớn lao']], 'Mỗi trạng thái của sóng là một trạng thái của tình yêu người con gái.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi yếu tố vào nhóm NỘI DUNG hoặc NGHỆ THUẬT của bài thơ.', [['Khát vọng tình yêu', 'NỘI DUNG'], ['Thể thơ năm chữ', 'NGHỆ THUẬT'], ['Nỗi nhớ quê hương', 'NỘI DUNG'], ['Nhạc điệu du dương', 'NGHỆ THUẬT'], ['Triết lí nhân sinh', 'NỘI DUNG'], ['Hình ảnh ẩn dụ', 'NGHỆ THUẬT']], 'Nội dung: thơ nói gì. Nghệ thuật: thơ nói bằng cách nào.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi hình ảnh vào nhóm HÌNH ẢNH THỰC hoặc HÌNH ẢNH TƯỢNG TRƯNG.', [['"Vườn ai mướt quá xanh như ngọc"', 'HÌNH ẢNH THỰC'], ['"Sóng" (tình yêu)', 'HÌNH ẢNH TƯỢNG TRƯNG'], ['"Nắng hàng cau"', 'HÌNH ẢNH THỰC'], ['"Thuyền – biển" (tình yêu)', 'HÌNH ẢNH TƯỢNG TRƯNG']], 'Hình ảnh thực tả cái nhìn thấy; hình ảnh tượng trưng mang nghĩa bóng sâu xa.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ ngữ vào nhóm GỢI HÌNH ẢNH hoặc GỢI CẢM XÚC.', [['"mướt quá"', 'GỢI HÌNH ẢNH'], ['"da diết"', 'GỢI CẢM XÚC'], ['"xanh như ngọc"', 'GỢI HÌNH ẢNH'], ['"khắc khoải"', 'GỢI CẢM XÚC']], 'Từ ngữ gợi hình vẽ nên bức tranh; từ ngữ gợi cảm xúc chạm đến trái tim.', $d);
        $this->sortQ($s, 'Kéo mỗi câu thơ vào nhóm CÓ NHẠC TÍNH CAO hoặc BÌNH THƯỜNG.', [['"Sóng bắt đầu từ gió / Gió bắt đầu từ đâu?"', 'CÓ NHẠC TÍNH CAO'], ['"Hôm nay tôi đi học."', 'BÌNH THƯỜNG'], ['"Thuyền về có nhớ bến chăng?"', 'CÓ NHẠC TÍNH CAO'], ['"Trời hôm nay nắng."', 'BÌNH THƯỜNG']], 'Vần, điệp ngữ, nhịp điệu tạo nên nhạc tính đặc trưng của thơ.', $d);
        $this->sortQ($s, 'Kéo mỗi khổ thơ trong "Sóng" vào nhóm NÓI VỀ SÓNG hoặc NÓI VỀ EM.', [['Khổ 1: "Dữ dội và dịu êm..."', 'NÓI VỀ SÓNG'], ['Khổ 2: "Ôi con sóng ngày xưa..."', 'NÓI VỀ SÓNG'], ['Khổ 5: "Con sóng dưới lòng sâu..."', 'NÓI VỀ EM'], ['Khổ 9: "Làm sao được tan ra..."', 'NÓI VỀ EM']], 'Bài thơ chuyển dần từ sóng sang em: sóng là "cái cớ" để nói lòng mình.', $d);

        $this->fill($s, 'Ý tưởng trung tâm làm nên mạch xuyên suốt bài thơ gọi là ___ thơ.', [[0, 'tứ']], 'Tứ thơ là "linh hồn": mọi hình ảnh, cảm xúc đều phục vụ tứ thơ.', $d);
        $this->fill($s, 'Sự vận động, phát triển của cảm xúc từ đầu đến cuối bài thơ gọi là mạch cảm ___.', [[0, 'xúc']], 'Theo dõi mạch cảm xúc giúp thấy bài thơ "sống" và phát triển ra sao.', $d);
        $this->fill($s, 'Hình ảnh mang nghĩa bóng sâu xa, vượt lên hình ảnh cụ thể gọi là hình ảnh tượng ___.', [[0, 'trưng']], '"Sóng" vượt lên con sóng biển để tượng trưng cho tình yêu.', $d);
        $this->fill($s, 'Khi phân tích khổ thơ cần chú ý từ ngữ, hình ảnh, biện pháp tu từ và giọng ___.', [[0, 'điệu']], 'Bốn yếu tố không thể bỏ qua khi "mổ xẻ" một khổ thơ.', $d);
        $this->fill($s, 'Câu hỏi không cần trả lời, dùng để bộc lộ cảm xúc gọi là câu hỏi tu ___.', [[0, 'từ']], 'Câu hỏi tu từ là tiếng lòng: "Sao anh không về chơi thôn Vĩ?"', $d);
    }

    /* ============ ngu-van-thpt-12-phan-tich-tho-2 (Ngữ văn 12, kho) ============ */
    private function seedNguVanThpt12Tho2(): void
    {
        $s = 'ngu-van-thpt-12-phan-tich-tho-2'; $d = 'kho';
        $this->quiz($s, 'Giá trị tư tưởng của bài thơ "Sóng" thể hiện qua điều gì?', ['Tả biển đẹp', 'Khát vọng tình yêu chân thành, chung thủy của người phụ nữ', 'Kể chuyện biển', 'Tả sóng to'], 1, 'Vượt lên hình ảnh sóng, bài thơ là tuyên ngôn về tình yêu: mãnh liệt mà chung thủy.', $d);
        $this->quiz($s, 'Đặc sắc nghệ thuật của thơ Xuân Quỳnh thường nằm ở đâu?', ['Từ Hán Việt', 'Ngôn ngữ giản dị, giàu nữ tính và hình ảnh ẩn dụ', 'Thể thơ Đường luật', 'Kể chuyện dài'], 1, 'Thơ Xuân Quỳnh: lời thơ giản dị, chân thành, giàu cảm xúc nữ tính và triết lí nhẹ nhàng.', $d);
        $this->quiz($s, 'Một kết bài hay của bài văn phân tích thơ cần đạt được điều gì?', ['Kể lại nội dung', 'Khái quát giá trị và nêu cảm nhận của người viết', 'Chê bai tác giả', 'Viết thật dài'], 1, 'Kết bài "đóng" vấn đề: khẳng định giá trị tư tưởng, nghệ thuật và cảm nhận riêng.', $d);
        $this->quiz($s, 'Tại sao phân tích thơ mà tách rời nội dung và nghệ thuật sẽ thiếu toàn diện?', ['Để bài dài hơn', 'Vì nghệ thuật là phương tiện thể hiện nội dung, tách rời sẽ phiến diện', 'Vì thầy cô yêu cầu', 'Để khoe kiến thức'], 1, 'Nội dung và nghệ thuật thống nhất biện chứng; phân tích một mặt là chưa đủ.', $d);
        $this->quiz($s, 'Hình ảnh "thuyền và biển" trong thơ tình thường tượng trưng cho điều gì?', ['Giao thông đường thủy', 'Tình yêu gắn bó, chung thủy', 'Du lịch biển', 'Đánh cá'], 1, '"Chỉ có thuyền mới hiểu biển mênh mông" — tình yêu cần sự thấu hiểu, gắn bó.', $d);

        $this->matching($s, 'Nối mỗi giá trị với biểu hiện trong bài "Sóng".', [['Giá trị tư tưởng', 'Khát vọng yêu chân thành'], ['Giá trị nhân văn', 'Trân trọng tình yêu con người'], ['Đặc sắc nghệ thuật', 'Ẩn dụ sóng – em'], ['Giá trị thẩm mĩ', 'Ngôn ngữ đẹp, nhạc tính']], 'Một bài thơ hay hội tụ cả giá trị tư tưởng và đặc sắc nghệ thuật.', $d);
        $this->matching($s, 'Nối mỗi yếu tố nghệ thuật với đặc sắc trong thơ Xuân Quỳnh.', [['Ngôn ngữ', 'Giản dị, chân thành'], ['Hình ảnh', 'Ẩn dụ sóng – em'], ['Thể thơ', 'Năm chữ nhịp nhàng'], ['Giọng điệu', 'Tâm tình, thủ thỉ']], 'Phong cách Xuân Quỳnh: giản dị mà sâu sắc, nữ tính mà mạnh mẽ.', $d);
        $this->matching($s, 'Nối mỗi giọng điệu thơ với sắc thái trong các bài thơ đã học.', [['Da diết ("Sóng")', 'Khát vọng tình yêu'], ['U hoài ("Đây thôn Vĩ Dạ")', 'Nhớ nhung, mặc cảm'], ['Trầm lắng ("Đất Nước")', 'Suy tư, chiêm nghiệm'], ['Hào sảng ("Tây Tiến")', 'Phấn chấn, bi tráng']], 'Mỗi bài thơ một giọng điệu — "dấu vân tay" cảm xúc của tác giả.', $d);
        $this->matching($s, 'Nối mỗi phần của bài văn phân tích thơ với nội dung.', [['Mở bài', 'Giới thiệu tác giả, tác phẩm'], ['Thân bài', 'Phân tích nội dung, nghệ thuật'], ['Kết bài', 'Khái quát giá trị, cảm nhận'], ['Luận điểm', 'Nhận định về khổ thơ, hình ảnh']], 'Bố cục chuẩn giúp bài phân tích thơ mạch lạc, đầy đủ.', $d);
        $this->matching($s, 'Nối mỗi hình ảnh trong "Đây thôn Vĩ Dạ" với ý nghĩa.', [['"Vườn mướt xanh như ngọc"', 'Vẻ đẹp thôn Vĩ'], ['"Trăng"', 'Nỗi nhớ, khát vọng'], ['"Đò"', 'Chia li, đợi chờ'], ['"Áo trắng"', 'Hình bóng người thương']], 'Mỗi hình ảnh trong bài thơ đều thấm đẫm nỗi nhớ và mặc cảm của thi nhân.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi nhận định vào nhóm ĐÁNH GIÁ NỘI DUNG hoặc ĐÁNH GIÁ NGHỆ THUẬT.', [['"Bài thơ ngợi ca tình yêu chung thủy."', 'ĐÁNH GIÁ NỘI DUNG'], ['"Ẩn dụ sóng – em độc đáo."', 'ĐÁNH GIÁ NGHỆ THUẬT'], ['"Thể hiện khát vọng hạnh phúc."', 'ĐÁNH GIÁ NỘI DUNG'], ['"Ngôn ngữ giản dị, giàu nhạc tính."', 'ĐÁNH GIÁ NGHỆ THUẬT']], 'Đánh giá nội dung: thơ nói gì. Đánh giá nghệ thuật: thơ hay ở cách nói.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ ngữ vào nhóm NGÔN NGỮ BÌNH DỊ hoặc NGÔN NGỮ TRAU CHUỐT.', [['"con sóng"', 'NGÔN NGỮ BÌNH DỊ'], ['"mướt quá xanh như ngọc"', 'NGÔN NGỮ TRAU CHUỐT'], ['"em"', 'NGÔN NGỮ BÌNH DỊ'], ['"khuôn trăng đầy đặn"', 'NGÔN NGỮ TRAU CHUỐT']], 'Xuân Quỳnh chuộng bình dị; Hàn Mặc Tử chuộng trau chuốt, tượng trưng.', $d);
        $this->sortQ($s, 'Kéo mỗi ý vào nhóm THUỘC MỞ BÀI hoặc THUỘC KẾT BÀI của bài phân tích.', [['Giới thiệu tác giả Xuân Quỳnh', 'THUỘC MỞ BÀI'], ['Khẳng định giá trị bài thơ', 'THUỘC KẾT BÀI'], ['Nêu vấn đề nghị luận', 'THUỘC MỞ BÀI'], ['Nêu cảm nhận của bản thân', 'THUỘC KẾT BÀI']], 'Mở bài: dẫn vào vấn đề. Kết bài: khái quát + cảm nhận.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi biểu hiện vào nhóm THƠ TRỮ TÌNH hoặc KHÔNG PHẢI TRỮ TÌNH.', [['Bộc lộ cảm xúc "em nhớ anh"', 'THƠ TRỮ TÌNH'], ['Kể sự kiện lịch sử', 'KHÔNG PHẢI TRỮ TÌNH'], ['"Lòng em nhớ anh da diết"', 'THƠ TRỮ TÌNH'], ['Thuyết minh cách làm bài', 'KHÔNG PHẢI TRỮ TÌNH']], 'Trữ tình: bộc lộ trực tiếp cảm xúc, tâm trạng của chủ thể.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ TÍNH TRIẾT LÍ hoặc CHỈ CẢM XÚC THUẦN TÚY.', [['"Sóng tìm ra tận bể" (khát vọng)', 'CÓ TÍNH TRIẾT LÍ'], ['"Em vui quá!"', 'CHỈ CẢM XÚC THUẦN TÚY'], ['"Ôi con sóng nhớ bờ"', 'CÓ TÍNH TRIẾT LÍ'], ['"Hôm nay trời đẹp."', 'CHỈ CẢM XÚC THUẦN TÚY']], 'Thơ hay vừa lay động cảm xúc vừa gợi suy ngẫm triết lí nhân sinh.', $d);

        $this->fill($s, 'Tình cảm, quan niệm, triết lí mà bài thơ gửi gắm tạo nên giá trị tư ___ của thơ.', [[0, 'tưởng']], 'Giá trị tư tưởng là "chiều sâu" làm nên sức sống lâu bền của bài thơ.', $d);
        $this->fill($s, 'Sự kết hợp của nhạc điệu, ngôn từ, hình ảnh tạo nên đặc sắc nghệ ___ của bài thơ.', [[0, 'thuật']], 'Nghệ thuật là "tấm áo đẹp" giúp tư tưởng thơ đến với người đọc.', $d);
        $this->fill($s, 'Phần cuối bài văn phân tích thơ cần khái quát giá trị và nêu cảm ___ của người viết.', [[0, 'nhận']], 'Cảm nhận chân thành là "dấu ấn" riêng, tránh sáo rỗng, chung chung.', $d);
        $this->fill($s, 'Nội dung và nghệ thuật trong tác phẩm có mối quan hệ thống nhất biện ___.', [[0, 'chứng']], 'Tách rời hai mặt sẽ dẫn đến phân tích phiến diện, một chiều.', $d);
        $this->fill($s, 'Thơ Xuân Quỳnh nổi bật với ngôn ngữ giản dị, giọng điệu tâm tình và hình ảnh ___ dụ sóng – em.', [[0, 'ẩn']], 'Ẩn dụ sóng – em là sáng tạo nghệ thuật đặc sắc nhất của bài "Sóng".', $d);
    }

    /* ============ tv-cac-tu-loai (Tiếng Việt 6, de) ============ */
    private function seedTvCacTuLoai(): void
    {
        $s = 'tv-cac-tu-loai'; $d = 'de';
        $this->quiz($s, 'Từ nào sau đây là danh từ chỉ con vật?', ['con trâu', 'chạy nhảy', 'xanh mướt', 'vui vẻ'], 0, '"Con trâu" gọi tên một con vật cụ thể nên là danh từ.', $d);
        $this->quiz($s, 'Từ nào sau đây là động từ chỉ hành động?', ['ngủ', 'đọc', 'buồn', 'đẹp'], 1, '"Đọc" chỉ hành động đọc sách, báo — động từ chỉ hành động rõ rệt.', $d);
        $this->quiz($s, 'Từ nào sau đây là tính từ chỉ tính cách?', ['cao', 'hiền lành', 'chạy', 'bàn'], 1, '"Hiền lành" chỉ tính nết của con người nên là tính từ chỉ tính cách.', $d);
        $this->quiz($s, 'Trong câu "Mèo con đang đùa nghịch.", có mấy từ loại khác nhau?', ['1', '2', '3', '4'], 2, 'Có 3 từ loại: danh từ (mèo, con), phụ từ (đang), động từ (đùa nghịch).', $d);
        $this->quiz($s, 'Từ "tươi" trong câu "Hoa nở tươi." thuộc từ loại nào?', ['Danh từ', 'Động từ', 'Tính từ', 'Đại từ'], 2, '"Tươi" chỉ trạng thái tươi tắn của hoa nên là tính từ.', $d);

        $this->matching($s, 'Nối mỗi từ với từ loại đúng của nó.', [['sông', 'Danh từ'], ['bơi', 'Động từ'], ['trong', 'Tính từ'], ['hát', 'Động từ']], 'Đọc từ trong ngữ cảnh câu để xác định đúng từ loại, tránh đoán mò.', $d);
        $this->matching($s, 'Nối mỗi nhóm từ với câu hỏi nhận diện.', [['Danh từ', 'Ai? Cái gì?'], ['Động từ', 'Làm gì?'], ['Tính từ', 'Thế nào?'], ['Đại từ', 'Thay thế cho ai, cái gì?']], 'Bốn câu hỏi vàng giúp nhận diện bốn từ loại cơ bản nhanh chóng.', $d);
        $this->matching($s, 'Nối mỗi từ trong câu "Bé Lan hát rất hay." với từ loại của nó.', [['Bé Lan', 'Danh từ'], ['hát', 'Động từ'], ['rất', 'Phụ từ'], ['hay', 'Tính từ']], '"Rất" là phụ từ chỉ mức độ đi kèm tính từ "hay".', $d);
        $this->matching($s, 'Nối mỗi từ loại với ví dụ đúng.', [['Danh từ', 'ngôi trường'], ['Động từ', 'học bài'], ['Tính từ', 'xinh đẹp'], ['Đại từ', 'chúng em']], 'Học từ loại qua ví dụ cụ thể, gần gũi sẽ nhớ lâu hơn.', $d);
        $this->matching($s, 'Nối mỗi câu với từ loại của từ in đậm trong câu.', [['"Trời **mưa** to."', 'Động từ'], ['"Cơn **mưa** rào."', 'Danh từ'], ['"Hoa **thơm** ngát."', 'Tính từ'], ['"Em **yêu** mẹ."', 'Động từ']], 'Cùng hình thức "mưa" nhưng vai trò khác nhau: hành động hay sự vật.', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc ĐỘNG TỪ.', [['trường học', 'DANH TỪ'], ['học bài', 'ĐỘNG TỪ'], ['cánh đồng', 'DANH TỪ'], ['cày ruộng', 'ĐỘNG TỪ'], ['bông hoa', 'DANH TỪ'], ['tưới cây', 'ĐỘNG TỪ']], 'Danh từ gọi tên; cụm động từ chỉ hành động.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TÍNH TỪ hoặc DANH TỪ.', [['đẹp', 'TÍNH TỪ'], ['vẻ đẹp', 'DANH TỪ'], ['vui', 'TÍNH TỪ'], ['niềm vui', 'DANH TỪ']], '"Vẻ đẹp, niềm vui" là danh từ chỉ khái niệm được tạo từ tính từ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm CHỈ NGƯỜI hoặc CHỈ VẬT.', [['thầy giáo', 'CHỈ NGƯỜI'], ['cái cặp', 'CHỈ VẬT'], ['bạn học', 'CHỈ NGƯỜI'], ['quyển sách', 'CHỈ VẬT']], 'Danh từ chỉ người gọi tên con người; chỉ vật gọi tên đồ vật.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ ĐỦ 3 TỪ LOẠI (danh – động – tính) hoặc KHÔNG.', [['"Bé ngoan học giỏi."', 'CÓ ĐỦ 3 TỪ LOẠI'], ['"Mẹ đi chợ."', 'KHÔNG'], ['"Hoa thơm khoe sắc."', 'CÓ ĐỦ 3 TỪ LOẠI'], ['"Trời mưa."', 'KHÔNG']], '"Bé (danh) ngoan (tính) học (động) giỏi (tính)" — đủ cả ba từ loại.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ CHỈ SỰ VẬT hoặc TỪ CHỈ ĐẶC ĐIỂM.', [['ngôi nhà', 'TỪ CHỈ SỰ VẬT'], ['cao ráo', 'TỪ CHỈ ĐẶC ĐIỂM'], ['dòng sông', 'TỪ CHỈ SỰ VẬT'], ['hiền hòa', 'TỪ CHỈ ĐẶC ĐIỂM']], 'Sự vật: cái tồn tại. Đặc điểm: cái gắn với sự vật để miêu tả nó.', $d);

        $this->fill($s, 'Từ chỉ người, vật, hiện tượng, khái niệm gọi là danh ___.', [[0, 'từ']], 'Danh từ là từ loại gọi tên mọi sự vật, hiện tượng trong thế giới.', $d);
        $this->fill($s, 'Trong câu "Chim ___ trên cành.", điền động từ chỉ tiếng kêu của chim: "hót".', [[0, 'hót']], '"Hót" là động từ đặc trưng chỉ tiếng kêu của các loài chim.', $d);
        $this->fill($s, 'Từ "xinh đẹp" chỉ đặc điểm của sự vật nên thuộc từ loại tính ___.', [[0, 'từ']], 'Mọi từ chỉ đặc điểm, tính chất đều là tính từ.', $d);
        $this->fill($s, '"Em ___ giúp mẹ quét nhà." Điền đại từ xưng hô phù hợp: "em".', [[0, 'em']], 'Đại từ "em" dùng để xưng hô, thay thế tên người nói.', $d);
        $this->fill($s, 'Câu "Gió thổi ___." cần tính từ chỉ mức độ mạnh: "mạnh".', [[0, 'mạnh']], '"Mạnh" là tính từ bổ nghĩa cho động từ "thổi", chỉ mức độ của gió.', $d);
    }

    /* ============ tv-cau-don (Tiếng Việt 6, trung_binh) ============ */
    private function seedTvCauDon(): void
    {
        $s = 'tv-cau-don'; $d = 'trung_binh';
        $this->quiz($s, 'Câu đơn là câu như thế nào?', ['Có nhiều cụm chủ – vị', 'Có một cụm chủ ngữ – vị ngữ', 'Không có chủ ngữ', 'Không có vị ngữ'], 1, 'Câu đơn chỉ có một cụm chủ – vị; từ hai cụm trở lên là câu ghép.', $d);
        $this->quiz($s, 'Trong câu "Đàn cá tung tăng bơi lội.", chủ ngữ là gì?', ['Đàn cá', 'tung tăng bơi lội', 'Đàn', 'bơi lội'], 0, 'Chủ ngữ "đàn cá" trả lời câu hỏi "ai?", vị ngữ "tung tăng bơi lội" trả lời "làm gì?".', $d);
        $this->quiz($s, 'Trong câu "Mùa xuân về.", vị ngữ là gì?', ['Mùa xuân', 'về', 'Mùa', 'xuân về'], 1, 'Vị ngữ "về" cho biết mùa xuân làm gì (đến).', $d);
        $this->quiz($s, 'Câu nào sau đây là câu ghép?', ['Trời mưa to.', 'Trời mưa to, đường trơn trượt.', 'Mẹ đi chợ.', 'Bé ngủ ngon.'], 1, '"Trời mưa to, đường trơn trượt" có hai cụm chủ – vị nên là câu ghép.', $d);
        $this->quiz($s, 'Thành phần trạng ngữ trong câu "Sáng nay, em đi học sớm." là gì?', ['Sáng nay', 'em', 'đi học sớm', 'đi học'], 0, 'Trạng ngữ "sáng nay" chỉ thời gian, đứng đầu câu, ngăn cách bằng dấu phẩy.', $d);

        $this->matching($s, 'Nối mỗi câu với chủ ngữ của nó.', [['"Mẹ nấu cơm."', 'Mẹ'], ['"Chim hót líu lo."', 'Chim'], ['"Hoa nở rộ."', 'Hoa'], ['"Bé cười tươi."', 'Bé']], 'Chủ ngữ thường đứng đầu câu, trả lời "ai? cái gì?".', $d);
        $this->matching($s, 'Nối mỗi câu với vị ngữ của nó.', [['"Mẹ nấu cơm."', 'nấu cơm'], ['"Chim hót líu lo."', 'hót líu lo'], ['"Hoa nở rộ."', 'nở rộ'], ['"Bé cười tươi."', 'cười tươi']], 'Vị ngữ trả lời "làm gì? thế nào?", thường đứng sau chủ ngữ.', $d);
        $this->matching($s, 'Nối mỗi thành phần với câu hỏi dùng để tìm nó.', [['Chủ ngữ', 'Ai? Cái gì?'], ['Vị ngữ', 'Làm gì? Thế nào?'], ['Trạng ngữ', 'Khi nào? Ở đâu? Vì sao?'], ['Bổ ngữ', 'Bổ sung ý nghĩa cho động từ, tính từ']], 'Ba câu hỏi vàng: ai/cái gì – làm gì/thế nào – khi nào/ở đâu.', $d);
        $this->matching($s, 'Nối mỗi câu với loại câu của nó.', [['"Gió thổi mạnh."', 'Câu đơn'], ['"Mưa to, gió lớn."', 'Câu ghép'], ['"Trăng lên cao."', 'Câu đơn'], ['"Em học bài, mẹ nấu cơm."', 'Câu ghép']], 'Đếm số cụm chủ – vị: một là đơn, từ hai trở lên là ghép.', $d);
        $this->matching($s, 'Nối mỗi cụm từ với vai trò trong câu "Ve sầu kêu râm ran.".', [['Ve sầu', 'Chủ ngữ'], ['kêu', 'Vị ngữ (trung tâm)'], ['râm ran', 'Bổ ngữ'], ['cả câu', 'Câu đơn']], 'Phân tích đầy đủ: chủ ngữ + vị ngữ (động từ trung tâm + bổ ngữ).', $d);

        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm CHỦ NGỮ hoặc VỊ NGỮ trong các câu đơn.', [['"Mẹ" (Mẹ nấu cơm)', 'CHỦ NGỮ'], ['"nấu cơm" (Mẹ nấu cơm)', 'VỊ NGỮ'], ['"Trăng" (Trăng lên cao)', 'CHỦ NGỮ'], ['"lên cao" (Trăng lên cao)', 'VỊ NGỮ'], ['"Bé" (Bé hát hay)', 'CHỦ NGỮ'], ['"hát hay" (Bé hát hay)', 'VỊ NGỮ']], 'Chủ ngữ nêu đối tượng; vị ngữ nêu hoạt động, đặc điểm của đối tượng.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU ĐƠN hoặc CÂU GHÉP.', [['"Lá rơi đầy sân."', 'CÂU ĐƠN'], ['"Gió thổi, lá rơi."', 'CÂU GHÉP'], ['"Mẹ về nhà."', 'CÂU ĐƠN'], ['"Trời tối, đèn sáng."', 'CÂU GHÉP']], 'Câu ghép có từ hai cụm chủ – vị trở lên, thường ngăn cách bằng dấu phẩy.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phần vào nhóm THÀNH PHẦN CHÍNH hoặc THÀNH PHẦN PHỤ.', [['Chủ ngữ', 'THÀNH PHẦN CHÍNH'], ['Trạng ngữ', 'THÀNH PHẦN PHỤ'], ['Vị ngữ', 'THÀNH PHẦN CHÍNH'], ['Bổ ngữ', 'THÀNH PHẦN PHỤ']], 'Chính: chủ – vị (nòng cốt). Phụ: trạng ngữ, bổ ngữ, định ngữ.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU KỂ hoặc CÂU CẢM.', [['"Hôm nay trời đẹp."', 'CÂU KỂ'], ['"Đẹp quá!"', 'CÂU CẢM'], ['"Em đi học."', 'CÂU KỂ'], ['"Tuyệt vời!"', 'CÂU CẢM']], 'Câu kể kết thúc bằng dấu chấm; câu cảm bộc lộ cảm xúc, dùng dấu chấm than.', $d);
        $this->sortQ($s, 'Kéo mỗi cụm từ vào nhóm LÀM CHỦ NGỮ ĐƯỢC hoặc KHÔNG.', [['"Đàn chim"', 'LÀM CHỦ NGỮ ĐƯỢC'], ['"đang bay"', 'KHÔNG'], ['"Mùa xuân"', 'LÀM CHỦ NGỮ ĐƯỢC'], ['"rất đẹp"', 'KHÔNG']], 'Chủ ngữ thường là danh từ hoặc cụm danh từ.', $d);

        $this->fill($s, 'Trong câu "Sáo diều vi vu.", chủ ngữ là "sáo ___".', [[0, 'diều']], '"Sáo diều" là cụm danh từ làm chủ ngữ, "vi vu" là vị ngữ.', $d);
        $this->fill($s, 'Trong câu "Mưa rơi tí tách.", vị ngữ là "rơi tí ___".', [[0, 'tách']], 'Vị ngữ "rơi tí tách" gồm động từ "rơi" và từ láy "tí tách" bổ sung.', $d);
        $this->fill($s, 'Câu "Gió thổi mạnh." là câu ___ vì chỉ có một cụm chủ – vị.', [[0, 'đơn']], 'Một cụm chủ – vị ("gió / thổi mạnh") → câu đơn.', $d);
        $this->fill($s, 'Thành phần chỉ thời gian, nơi chốn đứng đầu câu gọi là trạng ___.', [[0, 'ngữ']], 'Trạng ngữ bổ sung hoàn cảnh cho nòng cốt câu, thường ngăn cách bằng dấu phẩy.', $d);
        $this->fill($s, 'Câu "Trời mưa, đường ___." là câu ghép còn thiếu vị ngữ của vế thứ hai: "trơn".', [[0, 'trơn']], 'Vế 2: "đường (chủ ngữ) / trơn (vị ngữ)" — câu ghép hai vế.', $d);
    }

    /* ============ tv-dau-hoi-dau-nga (Tiếng Việt 6, de) ============ */
    private function seedTvDauHoiDauNga(): void
    {
        $s = 'tv-dau-hoi-dau-nga'; $d = 'de';
        $this->quiz($s, 'Từ nào dưới đây viết đúng chính tả?', ['nghỉ ngơi', 'nghĩ ngơi', 'nghỉ ngoi', 'nghi ngơi'], 0, '"Nghỉ ngơi" viết với dấu hỏi ở cả hai tiếng.', $d);
        $this->quiz($s, 'Trong bốn từ sau, từ nào viết đúng chính tả?', ['suy nghĩ', 'suy nghỉ', 'suy ngĩ', 'suy nghỹ'], 0, '"Suy nghĩ" viết với dấu ngã ở tiếng "nghĩ".', $d);
        $this->quiz($s, 'Từ nào viết SAI chính tả?', ['mạnh mẽ', 'mãnh mẽ', 'vui vẻ', 'mới mẻ'], 1, '"Mạnh mẽ" viết với dấu nặng và ngã, không phải "mãnh mẽ".', $d);
        $this->quiz($s, 'Từ "cũ kĩ" viết đúng là?', ['cũ kĩ (hỏi – ngã)', 'củ kĩ', 'cũ kỉ', 'củ kỉ'], 0, '"Cũ" dấu hỏi, "kĩ" dấu ngã — cặp từ dễ nhầm cần nhớ kĩ.', $d);
        $this->quiz($s, 'Trong câu "Bé ___ xuống đất.", từ điền đúng là?', ['ngã', 'ngả', 'ngà', 'ngã ba'], 0, '"Ngã" (dấu ngã) chỉ hành động té xuống; "ngả" là nghiêng, "ngà" là ngà voi, "ngã ba" là nơi giao nhau.', $d);

        $this->matching($s, 'Hãy nối mỗi từ với dấu thanh đúng của nó.', [['nghỉ', 'Dấu hỏi'], ['ngã', 'Dấu ngã'], ['ngủ', 'Dấu hỏi'], ['nghĩ', 'Dấu ngã']], '"Nghỉ, ngủ" dấu hỏi; "ngã, nghĩ" dấu ngã — học thuộc lòng các cặp dễ nhầm.', $d);
        $this->matching($s, 'Nối mỗi cặp từ dễ nhầm với cách viết đúng.', [['nghỉ ngơi', 'Dấu hỏi – dấu hỏi'], ['suy nghĩ', 'Không dấu – dấu ngã'], ['mạnh mẽ', 'Dấu nặng – dấu ngã'], ['vui vẻ', 'Không dấu – dấu hỏi']], 'Mỗi cặp từ có "công thức dấu" riêng, cần học thuộc từng cặp.', $d);
        $this->matching($s, 'Nối mỗi từ với nghĩa của nó để nhớ dấu.', [['"ngã" (dấu ngã)', 'Té, đổ xuống'], ['"ngả" (dấu hỏi)', 'Nghiêng về phía'], ['"nghỉ" (dấu hỏi)', 'Ngừng làm việc'], ['"nghĩ" (dấu ngã)', 'Suy xét trong đầu']], 'Nhớ nghĩa giúp nhớ dấu: ngã = té (mạnh, đột ngột → ngã).', $d);
        $this->matching($s, 'Nối mỗi câu với từ viết đúng điền vào chỗ trống.', [['"Em bé ___ xuống đất."', 'ngã'], ['"Cây ___ về phía sông."', 'ngả'], ['"Mẹ cho em ___ trưa."', 'nghỉ'], ['"Bạn ấy đang ___ bài."', 'nghĩ']], 'Đặt từ vào ngữ cảnh câu để chọn dấu hỏi hay ngã cho đúng.', $d);
        $this->matching($s, 'Nối mỗi từ láy với dấu thanh đúng của nó.', [['lấp lánh', 'Không dấu – dấu hỏi'], ['rõ ràng', 'Dấu hỏi – dấu huyền'], ['vội vàng', 'Dấu hỏi – dấu huyền'], ['mạnh mẽ', 'Dấu nặng – dấu ngã']], 'Từ láy cũng phải viết đúng dấu từng tiếng, không được đoán mò.', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DẤU HỎI hoặc DẤU NGÃ.', [['nghỉ', 'DẤU HỎI'], ['ngã', 'DẤU NGÃ'], ['ngủ', 'DẤU HỎI'], ['nghĩ', 'DẤU NGÃ'], ['hỏi', 'DẤU HỎI'], ['ngã ba', 'DẤU NGÃ']], 'Học thuộc lòng từng từ: nghỉ, ngủ, hỏi (hỏi); ngã, nghĩ, ngã ba (ngã).', $d);
        $this->sortQ($s, 'Kéo mỗi từ (theo nghĩa đã cho) vào nhóm VIẾT VỚI DẤU HỎI hoặc DẤU NGÃ.', [['"mở" (mở cửa)', 'DẤU HỎI'], ['"mỡ" (mỡ heo)', 'DẤU NGÃ'], ['"kẻ" (kẻ vạch)', 'DẤU HỎI'], ['"kẽ" (kẽ tay)', 'DẤU NGÃ']], '"Mở cửa" dấu hỏi, "mỡ heo" dấu ngã — nghĩa khác nhau, dấu khác nhau.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm VIẾT ĐÚNG hoặc VIẾT SAI chính tả.', [['"Em nghỉ ngơi."', 'VIẾT ĐÚNG'], ['"Em nghĩ ngơi."', 'VIẾT SAI'], ['"Bạn suy nghĩ kĩ."', 'VIẾT ĐÚNG'], ['"Bạn suy nghỉ kĩ."', 'VIẾT SAI']], '"Nghỉ ngơi" (hỏi–hỏi), "suy nghĩ" (không–ngã) — sai một dấu là sai cả từ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ LÁY VIẾT ĐÚNG hoặc VIẾT SAI.', [['vui vẻ', 'VIẾT ĐÚNG'], ['vui vẽ', 'VIẾT SAI'], ['mạnh mẽ', 'VIẾT ĐÚNG'], ['mạnh mẻ', 'VIẾT SAI']], '"Vẻ" (vẻ đẹp) dấu hỏi; "mẽ" dấu ngã — nhầm là sai nghĩa.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp từ vào nhóm ĐỒNG ÂM KHÁC DẤU hoặc KHÁC NGHĨA.', [['ngã – ngả', 'ĐỒNG ÂM KHÁC DẤU'], ['nghỉ – nghĩ', 'ĐỒNG ÂM KHÁC DẤU'], ['bàn – ghế', 'KHÁC NGHĨA'], ['mở – mỡ', 'ĐỒNG ÂM KHÁC DẤU']], 'Đồng âm khác dấu: phát âm gần giống nhưng nghĩa và dấu khác nhau.', $d);

        $this->fill($s, '"Suy ___" (suy xét): điền dấu đúng cho tiếng thứ hai — dấu ngã: "nghĩ".', [[0, 'nghĩ']], '"Suy nghĩ" viết với dấu ngã ở "nghĩ" — cặp từ dễ sai cần nhớ kĩ.', $d);
        $this->fill($s, 'Từ trái nghĩa với "mới" là "___" (viết đúng dấu hỏi): "cũ".', [[0, 'cũ']], '"Cũ" viết với dấu hỏi; đừng nhầm thành "củ" (củ khoai).', $d);
        $this->fill($s, '"Nghỉ ___" (ngừng làm việc): điền tiếng đúng dấu hỏi: "ngơi".', [[0, 'ngơi']], '"Nghỉ ngơi": hỏi – hỏi. "Nghĩ ngợi": ngã – hỏi.', $d);
        $this->fill($s, 'Câu "Em bé bị ___." (té xuống): điền từ đúng dấu ngã: "ngã".', [[0, 'ngã']], '"Ngã" (té) dấu ngã; "ngả" (nghiêng) dấu hỏi — phân biệt theo nghĩa.', $d);
        $this->fill($s, '"Mạnh ___" (khỏe khoắn): điền tiếng đúng dấu ngã: "mẽ".', [[0, 'mẽ']], '"Mạnh mẽ": nặng – ngã. Nhớ công thức dấu của từng cặp từ.', $d);
    }

    /* ============ tv-am-dau (Tiếng Việt 6, de) ============ */
    private function seedTvAmDau(): void
    {
        $s = 'tv-am-dau'; $d = 'de';
        $this->quiz($s, 'Từ nào viết ĐÚNG chính tả?', ['chăm chỉ', 'trăm chỉ', 'chăm chĩ', 'trăm chĩ'], 0, '"Chăm chỉ" viết với âm đầu "ch" ở cả hai tiếng.', $d);
        $this->quiz($s, 'Chọn từ có âm đầu viết đúng là "d".', ['da diết', 'gia diết', 'ra diết', 'dda diết'], 0, '"Da diết" (nhớ nhung tha thiết) viết với âm đầu "d"; các cách viết còn lại đều sai.', $d);
        $this->quiz($s, 'Chọn từ có âm đầu viết đúng là "gi".', ['gian nan', 'dan nan', 'ran nan', 'dian nan'], 0, '"Gian nan" (khó khăn, vất vả) viết với âm đầu "gi"; các cách viết còn lại đều sai.', $d);
        $this->quiz($s, 'Chọn từ có âm đầu viết đúng là "r".', ['rộn ràng', 'dộn dàng', 'rộn rằng', 'dộn rằng'], 0, '"Rộn ràng" (vui tươi, náo nức) viết với âm đầu "r"; các cách viết còn lại đều sai.', $d);
        $this->quiz($s, 'Từ "rực rỡ" viết đúng với âm đầu nào?', ['r', 'd', 'gi', 'ch'], 0, '"Rực rỡ" viết với âm đầu "r" ở cả hai tiếng.', $d);

        $this->matching($s, 'Nối mỗi từ với âm đầu đúng của nó.', [['chim', 'ch'], ['trống', 'tr'], ['diều', 'd'], ['gió', 'gi']], 'Học thuộc âm đầu của từng từ qua ví dụ cụ thể, gần gũi.', $d);
        $this->matching($s, 'Nối mỗi chỗ trống với âm đầu đúng.', [['"___im hót"', 'ch'], ['"con ___âu"', 'tr'], ['"___ó thổi"', 'gi'], ['"___òng sông"', 'd']], '"Chim hót, con trâu, gió thổi, dòng sông" — những cụm từ quen thuộc.', $d);
        $this->matching($s, 'Nối mỗi cặp từ dễ nhầm với cách phân biệt.', [['chăm – trăm', '"chăm" (siêng năng) viết ch'], ['trong – chong', '"trong" (trong trẻo) viết tr'], ['dòng – giòng', '"dòng" (dòng sông) viết d'], ['gió – ró', '"gió" viết gi']], 'Nhớ nghĩa giúp nhớ âm đầu: chăm chỉ (siêng) khác trăm (số đếm).', $d);
        $this->matching($s, 'Nối mỗi từ láy với âm đầu của nó.', [['rì rào', 'r'], ['róc rách', 'r'], ['lung linh', 'l'], ['xôn xao', 'x']], 'Từ láy thường giữ nguyên âm đầu ở cả hai tiếng.', $d);
        $this->matching($s, 'Nối mỗi câu với từ viết đúng điền vào chỗ trống.', [['"___e con chạy nhảy."', 'dê'], ['"Mẹ ___ cơm."', 'nấu (n)'], ['"Bé ___ bóng."', 'đá'], ['"Hoa ___ thơm."', 'nở']], 'Mở rộng: luyện thêm các âm đầu dễ nhầm khác (d, n, đ).', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm VIẾT VỚI CH hoặc VIẾT VỚI TR.', [['chim', 'VIẾT VỚI CH'], ['trống', 'VIẾT VỚI TR'], ['chăm', 'VIẾT VỚI CH'], ['trong', 'VIẾT VỚI TR'], ['chạy', 'VIẾT VỚI CH'], ['tre', 'VIẾT VỚI TR']], 'Phân biệt ch/tr qua nghĩa và thói quen sử dụng, tra từ điển khi nghi ngờ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm VIẾT VỚI D, GI hoặc R.', [['diều', 'VIẾT VỚI D'], ['gió', 'VIẾT VỚI GI'], ['rừng', 'VIẾT VỚI R'], ['dòng', 'VIẾT VỚI D'], ['giúp', 'VIẾT VỚI GI'], ['reo', 'VIẾT VỚI R']], 'Ba âm đầu d/gi/r phát âm gần giống nhau ở nhiều vùng miền nên dễ nhầm.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm VIẾT VỚI S hoặc VIẾT VỚI X.', [['sáng', 'VIẾT VỚI S'], ['xanh', 'VIẾT VỚI X'], ['sông', 'VIẾT VỚI S'], ['xe', 'VIẾT VỚI X']], 'Mở rộng thêm cặp s/x cũng rất dễ nhầm lẫn.', $d);
        $this->sortQ($s, 'Kéo mỗi cách viết vào nhóm ĐÚNG hoặc SAI chính tả.', [['chăm chỉ', 'ĐÚNG'], ['trăm chỉ', 'SAI'], ['trong trẻo', 'ĐÚNG'], ['chong trẻo', 'SAI'], ['dòng sông', 'ĐÚNG'], ['giòng sông', 'SAI']], 'Đọc to từ lên và đối chiếu với nghĩa để kiểm tra chính tả.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm ÂM ĐẦU ĐÚNG hoặc SAI theo nghĩa đã cho.', [['"chăm" (siêng năng)', 'ÂM ĐẦU ĐÚNG'], ['"trăm" (siêng năng)', 'SAI'], ['"trâu" (con vật)', 'ÂM ĐẦU ĐÚNG'], ['"châu" (con vật)', 'SAI']], 'Âm đầu sai làm sai nghĩa: "chăm" (siêng) khác "trăm" (100).', $d);

        $this->fill($s, 'Điền âm đầu còn thiếu: "___im hót líu lo." — đáp án: "ch".', [[0, 'ch']], '"Chim" viết với âm đầu "ch" — từ quen thuộc cần nhớ.', $d);
        $this->fill($s, 'Điền âm đầu còn thiếu: "con ___âu ăn cỏ." — đáp án: "tr".', [[0, 'tr']], '"Trâu" viết với âm đầu "tr" — đừng nhầm với "châu".', $d);
        $this->fill($s, 'Điền âm đầu còn thiếu: "___ó thổi mạnh." — đáp án: "gi".', [[0, 'gi']], '"Gió" viết với âm đầu "gi".', $d);
        $this->fill($s, 'Từ chỉ sự siêng năng viết đúng là "chăm ___": "chỉ".', [[0, 'chỉ']], '"Chăm chỉ": ch – ch. Nghĩa là siêng năng, cần mẫn.', $d);
        $this->fill($s, '"___ong trẻo" (âm thanh trong): điền âm đầu đúng "tr" — "trong trẻo".', [[0, 'tr']], '"Trong trẻo" chỉ âm thanh trong, vang — viết với "tr".', $d);
    }

    /* ============ tv-bai-van-mieu-ta (Tiếng Việt 7, trung_binh) ============ */
    private function seedTvBaiVanMieuTa(): void
    {
        $s = 'tv-bai-van-mieu-ta'; $d = 'trung_binh';
        $this->quiz($s, 'Một bài văn miêu tả đầy đủ thường có mấy phần?', ['2 phần', '3 phần', '4 phần', '1 phần'], 1, 'Ba phần: mở bài – thân bài – kết bài.', $d);
        $this->quiz($s, 'Phần nào của bài văn miêu tả tả chi tiết đối tượng?', ['Mở bài', 'Thân bài', 'Kết bài', 'Nhan đề'], 1, 'Thân bài là phần tả chi tiết từng bộ phận theo trình tự hợp lí.', $d);
        $this->quiz($s, 'Từ ngữ nào dưới đây gợi tả hình ảnh rõ nét nhất?', ['Rất đẹp', 'Lấp lánh', 'Tốt', 'Nhiều'], 1, '"Lấp lánh" là từ láy gợi hình cụ thể; "rất đẹp" chung chung.', $d);
        $this->quiz($s, 'Muốn viết bài văn miêu tả hay, việc đầu tiên cần làm là gì?', ['Viết ngay mở bài', 'Quan sát kĩ đối tượng', 'Học thuộc bài mẫu', 'Vẽ tranh minh họa'], 1, 'Quan sát kĩ bằng nhiều giác quan là nền tảng của mọi bài văn miêu tả hay.', $d);
        $this->quiz($s, 'Câu nào là câu văn miêu tả?', ['Hôm nay là thứ hai.', '"Dòng sông uốn lượn như dải lụa."', 'Em đi học lúc 6 giờ.', 'Lớp có 40 học sinh.'], 1, 'Câu văn miêu tả vẽ nên hình ảnh cụ thể, sinh động của sự vật.', $d);

        $this->matching($s, 'Hãy nối mỗi phần của bài văn với nhiệm vụ của nó.', [['Mở bài', 'Giới thiệu đối tượng'], ['Thân bài', 'Tả chi tiết'], ['Kết bài', 'Nêu cảm nghĩ'], ['Nhan đề', 'Gợi mở nội dung']], 'Ba phần ba nhiệm vụ, liên kết chặt chẽ tạo thành bài văn hoàn chỉnh.', $d);
        $this->matching($s, 'Hãy nối mỗi kiểu bài với nội dung miêu tả.', [['Tả người', 'Ngoại hình, tính cách'], ['Tả cảnh', 'Không gian, thời gian'], ['Tả con vật', 'Hình dáng, thói quen'], ['Tả đồ vật', 'Hình dáng, công dụng']], 'Mỗi kiểu bài có trọng tâm riêng, không tả dàn trải như nhau.', $d);
        $this->matching($s, 'Nối mỗi loại từ với ví dụ đúng trong văn miêu tả.', [['Từ láy gợi hình', 'lấp lánh'], ['Từ láy gợi thanh', 'rì rào'], ['Tính từ', 'xanh mướt'], ['Động từ', 'uốn lượn']], 'Vốn từ phong phú là "nguyên liệu" không thể thiếu của văn miêu tả.', $d);
        $this->matching($s, 'Nối mỗi giác quan với chi tiết quan sát được.', [['Mắt', 'Màu sắc, hình dáng'], ['Tai', 'Âm thanh'], ['Mũi', 'Mùi hương'], ['Tay', 'Cảm giác chạm']], 'Càng nhiều giác quan, bài văn càng giàu chi tiết, sống động.', $d);
        $this->matching($s, 'Nối mỗi câu văn với nhận xét.', [['"Sông như dải lụa."', 'Hay: có so sánh'], ['"Sông dài."', 'Nhạt: chung chung'], ['"Trăng tròn vành vạnh."', 'Hay: gợi hình'], ['"Trời tối."', 'Nhạt: đơn điệu']], 'Câu văn hay có hình ảnh, từ ngữ đắt; câu nhạt chỉ thông báo.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm VĂN MIÊU TẢ hoặc VĂN KỂ CHUYỆN.', [['"Dòng sông uốn lượn."', 'VĂN MIÊU TẢ'], ['"Hôm qua em đi chơi."', 'VĂN KỂ CHUYỆN'], ['"Hoa nở rực rỡ."', 'VĂN MIÊU TẢ'], ['"Mẹ kể chuyện cổ tích."', 'VĂN KỂ CHUYỆN']], 'Miêu tả: vẽ hình ảnh. Kể chuyện: thuật lại sự việc diễn ra.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm GỢI HÌNH ẢNH hoặc GỢI ÂM THANH.', [['lấp lánh', 'GỢI HÌNH ẢNH'], ['róc rách', 'GỢI ÂM THANH'], ['mênh mông', 'GỢI HÌNH ẢNH'], ['rì rào', 'GỢI ÂM THANH'], ['chót vót', 'GỢI HÌNH ẢNH'], ['lộp độp', 'GỢI ÂM THANH']], 'Từ gợi hình vẽ dáng vẻ; từ gợi thanh tái hiện âm thanh.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm NÊN LÀM hoặc NÊN TRÁNH khi viết văn miêu tả.', [['Quan sát kĩ', 'NÊN LÀM'], ['Tả chung chung', 'NÊN TRÁNH'], ['Dùng từ ngữ gợi tả', 'NÊN LÀM'], ['Chép bài mẫu', 'NÊN TRÁNH']], 'Quan sát kĩ + từ ngữ gợi tả = nền tảng của bài văn hay.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm CHI TIẾT TIÊU BIỂU hoặc CHUNG CHUNG.', [['"Bàn tay mẹ chai sần"', 'CHI TIẾT TIÊU BIỂU'], ['"Mẹ có hai tay"', 'CHUNG CHUNG'], ['"Nụ cười hiền như nắng"', 'CHI TIẾT TIÊU BIỂU'], ['"Bạn ấy cao"', 'CHUNG CHUNG']], 'Chi tiết tiêu biểu gợi được tính cách, số phận; chi tiết chung chung ai cũng có.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm MỞ BÀI hoặc KẾT BÀI.', [['"Quê hương em rất đẹp."', 'MỞ BÀI'], ['"Em yêu quê hương tha thiết."', 'KẾT BÀI'], ['"Mỗi hè em về thăm bà."', 'MỞ BÀI'], ['"Mãi mãi em nhớ quê hương."', 'KẾT BÀI']], 'Mở bài giới thiệu; kết bài bộc lộ tình cảm, ấn tượng sâu đậm.', $d);

        $this->fill($s, 'Bài văn miêu tả gồm ba phần: mở bài, thân bài và kết ___.', [[0, 'bài']], 'Ba phần ba nhiệm vụ: giới thiệu – tả chi tiết – nêu cảm nghĩ.', $d);
        $this->fill($s, 'Muốn tả hay phải quan sát kĩ bằng nhiều giác ___.', [[0, 'quan']], 'Mắt, tai, mũi, tay — càng nhiều giác quan càng nhiều chi tiết hay.', $d);
        $this->fill($s, 'Từ láy "lấp lánh" có tác dụng gợi hình, gợi ___.', [[0, 'cảm']], 'Từ ngữ gợi cảm khơi dậy cảm xúc, làm bài văn có hồn.', $d);
        $this->fill($s, 'Phần thân bài tả chi tiết đối tượng theo trình tự hợp ___.', [[0, 'lí']], 'Trình tự không gian, thời gian hoặc bao quát – chi tiết.', $d);
        $this->fill($s, 'Để bài văn sinh động cần dùng từ ngữ gợi hình và biện pháp tu ___.', [[0, 'từ']], 'So sánh, nhân hoá là "gia vị" không thể thiếu của văn miêu tả.', $d);
    }

    /* ============ tv-bien-phap-tu-tu (Tiếng Việt 7, trung_binh) ============ */
    private function seedTvBienPhapTuTu(): void
    {
        $s = 'tv-bien-phap-tu-tu'; $d = 'trung_binh';
        $this->quiz($s, 'Câu "Mặt trời như quả cầu lửa." dùng biện pháp tu từ nào?', ['Nhân hoá', 'So sánh', 'Ẩn dụ', 'Hoán dụ'], 1, 'Có từ "như" đối chiếu mặt trời với quả cầu lửa → so sánh.', $d);
        $this->quiz($s, 'Câu "Chị gió thì thầm." dùng biện pháp tu từ nào?', ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Liệt kê'], 1, 'Gán hành động "thì thầm" và xưng hô "chị" cho gió → nhân hoá.', $d);
        $this->quiz($s, 'Biện pháp tu từ nào được dùng trong câu "Thuyền về có nhớ bến chăng?"', ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Điệp ngữ'], 2, '"Thuyền – bến" gọi người ra đi và người ở lại → ẩn dụ.', $d);
        $this->quiz($s, 'Tác dụng chính của các biện pháp tu từ là gì?', ['Làm câu dài hơn', 'Làm hình ảnh sinh động, gợi cảm', 'Làm câu khó hiểu', 'Thay từ khó'], 1, 'Tu từ làm lời văn cụ thể, sinh động, giàu sức gợi và cảm xúc.', $d);
        $this->quiz($s, 'Câu nào KHÔNG dùng biện pháp tu từ?', ['"Trăng tròn như đĩa."', '"Gió đùa vui."', '"Hôm nay trời nắng."', '"Mây trắng như bông."'], 2, '"Hôm nay trời nắng" chỉ là câu kể thông thường, không đối chiếu hay nhân hoá.', $d);

        $this->matching($s, 'Nối mỗi biện pháp với dấu hiệu nhận biết.', [['So sánh', 'Từ "như, tựa, là"'], ['Nhân hoá', 'Xưng hô, hành động của người'], ['Ẩn dụ', 'Gọi tên gián tiếp, không có "như"'], ['Hoán dụ', 'Quan hệ gần gũi']], 'Dấu hiệu là "chìa khóa" nhận diện nhanh biện pháp tu từ.', $d);
        $this->matching($s, 'Nối mỗi câu với biện pháp tu từ của nó.', [['"Mắt sáng như sao."', 'So sánh'], ['"Ông trăng mỉm cười."', 'Nhân hoá'], ['"Người cha mái tóc bạc."', 'Ẩn dụ'], ['"Cả lớp cười ồ."', 'Hoán dụ']], 'Luyện nhận diện qua các câu thơ, câu văn quen thuộc.', $d);
        $this->matching($s, 'Nối mỗi tác dụng với biện pháp tương ứng.', [['Làm hình ảnh cụ thể', 'So sánh'], ['Làm sự vật có hồn', 'Nhân hoá'], ['Gợi liên tưởng sâu xa', 'Ẩn dụ'], ['Gọi tên gián tiếp', 'Hoán dụ']], 'Mỗi biện pháp có "sở trường" riêng trong việc làm đẹp câu văn.', $d);
        $this->matching($s, 'Nối mỗi từ với vai trò trong phép tu từ.', [['"như"', 'Từ so sánh'], ['"ông" (ông trăng)', 'Xưng hô nhân hoá'], ['"bến" (nhớ bến)', 'Hình ảnh ẩn dụ'], ['"tay" (bàn tay)', 'Hình ảnh hoán dụ']], 'Từ ngữ là "viên gạch" xây nên phép tu từ.', $d);
        $this->matching($s, 'Nối mỗi câu ca dao với biện pháp tu từ.', [['"Thân em như tấm lụa đào."', 'So sánh'], ['"Trâu ơi ta bảo trâu này."', 'Nhân hoá'], ['"Một cây làm chẳng nên non."', 'Ẩn dụ'], ['"Tay làm hàm nhai."', 'Hoán dụ']], 'Ca dao là "kho tàng" các biện pháp tu từ của dân gian.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm SO SÁNH hoặc NHÂN HOÁ.', [['"Mây trắng như bông."', 'SO SÁNH'], ['"Cô mây dạo chơi."', 'NHÂN HOÁ'], ['"Nước trong như gương."', 'SO SÁNH'], ['"Anh nắng tinh nghịch."', 'NHÂN HOÁ']], 'Có "như" → so sánh; xưng "cô, anh" → nhân hoá.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm ẨN DỤ hoặc HOÁN DỤ.', [['"Thuyền về nhớ bến."', 'ẨN DỤ'], ['"Cả trường reo hò."', 'HOÁN DỤ'], ['"Mặt trời trong lăng."', 'ẨN DỤ'], ['"Áo nâu ra đồng."', 'HOÁN DỤ']], 'Tương đồng → ẩn dụ; gần gũi → hoán dụ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÓ hoặc KHÔNG dùng biện pháp tu từ.', [['"Ve kêu râm ran."', 'KHÔNG'], ['"Ve như dàn nhạc."', 'CÓ'], ['"Mưa rơi."', 'KHÔNG'], ['"Mưa nhảy múa."', 'CÓ']], 'Câu kể đơn thuần không có tu từ; câu có đối chiếu, nhân hoá mới có.', $d);
        $this->sortQ($s, 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI về tu từ.', [['"So sánh cần từ như."', 'ĐÚNG'], ['"Nhân hoá gán đặc điểm người."', 'ĐÚNG'], ['"Ẩn dụ luôn có từ như."', 'SAI'], ['"Hoán dụ dựa trên gần gũi."', 'ĐÚNG']], 'Ẩn dụ lược bỏ từ so sánh — đó là điểm khác biệt với so sánh.', $d);
        $this->sortQ($s, 'Kéo mỗi hình ảnh vào nhóm TU TỪ TRONG CA DAO hoặc TRONG THƠ HIỆN ĐẠI.', [['"Thân em như lụa đào."', 'TRONG CA DAO'], ['"Sóng – em"', 'TRONG THƠ HIỆN ĐẠI'], ['"Con cò lặn lội"', 'TRONG CA DAO'], ['"Mặt trời trong lăng"', 'TRONG THƠ HIỆN ĐẠI']], 'Tu từ hiện diện trong mọi thời kì văn học Việt Nam.', $d);

        $this->fill($s, 'Điền từ còn thiếu: "Trăng tròn ___ cái đĩa." — đáp án: "như".', [[0, 'như']], '"Như" là từ so sánh nối "trăng" với "cái đĩa".', $d);
        $this->fill($s, 'Biện pháp gán đặc điểm của con người cho sự vật gọi là nhân ___.', [[0, 'hoá']], 'Nhân hoá: "chị gió, ông trăng, cô mây".', $d);
        $this->fill($s, 'Trong câu "Lá vàng rơi.", từ "vàng" thuộc từ loại tính ___.', [[0, 'từ']], '"Vàng" chỉ màu sắc của lá nên là tính từ.', $d);
        $this->fill($s, 'Phép tu từ gọi tên gián tiếp dựa trên nét tương đồng gọi là ___ dụ.', [[0, 'ẩn']], 'Ẩn dụ: "thuyền – bến", "mặt trời trong lăng".', $d);
        $this->fill($s, 'Phép tu từ gọi tên gián tiếp dựa trên quan hệ gần gũi gọi là ___ dụ.', [[0, 'hoán']], 'Hoán dụ: "bàn tay" (bộ phận), "cả lớp" (vật chứa).', $d);
    }

    /* ============ tv-tu-va-cau-lop-6-1 (Tiếng Việt 6, de) ============ */
    private function seedTvTuVaCauLop61(): void
    {
        $s = 'tv-tu-va-cau-lop-6-1'; $d = 'de';
        $this->quiz($s, 'Từ nào sau đây là từ láy toàn bộ?', ['xanh xanh', 'lấp lánh', 'vui vẻ', 'chăm chỉ'], 0, '"Xanh xanh" lặp lại toàn bộ cả âm và vần; các từ còn lại là láy bộ phận.', $d);
        $this->quiz($s, 'Từ nào sau đây là từ ghép đẳng lập?', ['bàn ghế', 'xanh ngắt', 'lấp lánh', 'rì rào'], 0, '"Bàn ghế" gồm hai tiếng đều có nghĩa, bình đẳng nhau — từ ghép đẳng lập.', $d);
        $this->quiz($s, 'Từ "lấp lánh" gồm mấy tiếng?', ['1', '2', '3', '4'], 1, '"Lấp lánh" gồm hai tiếng: "lấp" và "lánh" (láy vần).', $d);
        $this->quiz($s, 'Từ nào dưới đây chỉ gồm một tiếng (từ đơn)?', ['hoa', 'hoa hồng', 'xinh đẹp', 'lung linh'], 0, '"Hoa" chỉ có một tiếng; còn lại đều có hai tiếng trở lên.', $d);
        $this->quiz($s, 'Từ láy "rì rào" gợi tả điều gì?', ['Màu sắc', 'Âm thanh', 'Mùi hương', 'Hình dáng'], 1, '"Rì rào" là từ láy tượng thanh, gợi tả tiếng lá cây, tiếng sóng.', $d);

        $this->matching($s, 'Hãy nối mỗi từ với loại từ của nó.', [['hoa', 'Từ đơn'], ['bàn ghế', 'Từ ghép'], ['lung linh', 'Từ láy'], ['xanh xanh', 'Từ láy toàn bộ']], 'Từ đơn: một tiếng. Từ phức: từ ghép và từ láy.', $d);
        $this->matching($s, 'Hãy nối mỗi từ với số tiếng của nó.', [['hoa', '1 tiếng'], ['bàn ghế', '2 tiếng'], ['lung linh', '2 tiếng'], ['xanh xanh', '2 tiếng']], 'Đếm số tiếng giúp phân biệt từ đơn (1 tiếng) với từ phức (2+ tiếng).', $d);
        $this->matching($s, 'Hãy nối mỗi từ láy với tác dụng gợi tả của nó.', [['lấp lánh', 'Gợi tả ánh sáng'], ['rì rào', 'Gợi tả âm thanh'], ['mênh mông', 'Gợi tả không gian'], ['thơm phức', 'Gợi tả mùi hương']], 'Từ láy giàu sức gợi: hình, thanh, hương đều diễn tả được.', $d);
        $this->matching($s, 'Hãy nối mỗi từ ghép với nghĩa của các tiếng tạo thành.', [['bàn ghế', 'Hai tiếng đều có nghĩa'], ['xanh ngắt', 'Tiếng chính + tiếng bổ sung'], ['quần áo', 'Hai tiếng đều có nghĩa'], ['đỏ rực', 'Tiếng chính + tiếng bổ sung']], 'Từ ghép đẳng lập: hai tiếng bình đẳng. Từ ghép chính phụ: một tiếng chính.', $d);
        $this->matching($s, 'Nối mỗi từ láy với kiểu láy của nó.', [['lấp lánh', 'Láy vần'], ['xanh xanh', 'Láy toàn bộ'], ['rì rào', 'Láy âm đầu'], ['lung linh', 'Láy vần']], 'Láy âm đầu: lặp phụ âm. Láy vần: lặp vần. Láy toàn bộ: lặp cả tiếng.', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ LÁY TOÀN BỘ hoặc TỪ LÁY BỘ PHẬN.', [['xanh xanh', 'TỪ LÁY TOÀN BỘ'], ['lấp lánh', 'TỪ LÁY BỘ PHẬN'], ['đỏ đỏ', 'TỪ LÁY TOÀN BỘ'], ['rì rào', 'TỪ LÁY BỘ PHẬN']], 'Láy toàn bộ lặp cả tiếng; láy bộ phận chỉ lặp âm đầu hoặc vần.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ GHÉP ĐẲNG LẬP hoặc TỪ GHÉP CHÍNH PHỤ.', [['bàn ghế', 'TỪ GHÉP ĐẲNG LẬP'], ['xanh ngắt', 'TỪ GHÉP CHÍNH PHỤ'], ['sách vở', 'TỪ GHÉP ĐẲNG LẬP'], ['đỏ rực', 'TỪ GHÉP CHÍNH PHỤ']], 'Đẳng lập: hai tiếng ngang nhau. Chính phụ: một tiếng làm rõ nghĩa cho tiếng kia.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm TỪ ĐƠN hoặc TỪ PHỨC.', [['cây', 'TỪ ĐƠN'], ['cây cối', 'TỪ PHỨC'], ['nhà', 'TỪ ĐƠN'], ['nhà cửa', 'TỪ PHỨC'], ['sông', 'TỪ ĐƠN'], ['sông núi', 'TỪ PHỨC']], 'Từ đơn: một tiếng. Từ phức: từ ghép + từ láy.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ láy vào nhóm LÁY ÂM ĐẦU hoặc LÁY VẦN.', [['rì rào', 'LÁY ÂM ĐẦU'], ['lấp lánh', 'LÁY VẦN'], ['xôn xao', 'LÁY ÂM ĐẦU'], ['lung linh', 'LÁY VẦN']], '"Rì rào": lặp âm "r". "Lấp lánh": lặp vần "ấp/ánh" gần nhau.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TỪ LÁY GỢI THANH hoặc GỢI HÌNH.', [['róc rách', 'TỪ LÁY GỢI THANH'], ['mênh mông', 'TỪ LÁY GỢI HÌNH'], ['lộp độp', 'TỪ LÁY GỢI THANH'], ['chót vót', 'TỪ LÁY GỢI HÌNH']], 'Gợi thanh: âm thanh. Gợi hình: dáng vẻ, không gian.', $d);

        $this->fill($s, 'Từ "xanh xanh" lặp lại toàn bộ tiếng nên gọi là từ láy toàn ___.', [[0, 'bộ']], 'Láy toàn bộ: xanh xanh, đỏ đỏ. Láy bộ phận: lấp lánh, rì rào.', $d);
        $this->fill($s, 'Từ "quần áo" gồm hai tiếng đều có nghĩa, bình đẳng nhau nên là từ ghép đẳng ___.', [[0, 'lập']], 'Từ ghép đẳng lập: bàn ghế, quần áo, sách vở.', $d);
        $this->fill($s, 'Từ chỉ gồm một tiếng như "cây", "nhà" gọi là từ ___.', [[0, 'đơn']], 'Từ đơn là đơn vị nhỏ nhất; kết hợp lại tạo thành từ phức.', $d);
        $this->fill($s, 'Từ láy "long lanh" gợi tả ánh sáng đẹp, lung ___.', [[0, 'linh']], '"Lung linh" là từ láy quen thuộc tả ánh sáng huyền ảo, đẹp.', $d);
        $this->fill($s, 'Từ "rì rào" lặp lại âm đầu "r" nên thuộc kiểu láy âm ___.', [[0, 'đầu']], 'Láy âm đầu: rì rào, xôn xao. Láy vần: lấp lánh, lung linh.', $d);
    }

    /* ============ tv-tu-va-cau-lop-6-2 (Tiếng Việt 6, de) ============ */
    private function seedTvTuVaCauLop62(): void
    {
        $s = 'tv-tu-va-cau-lop-6-2'; $d = 'de';
        $this->quiz($s, 'Từ nào sau đây là danh từ chỉ hiện tượng?', ['cơn mưa', 'cái ô', 'chạy nhanh', 'vui vẻ'], 0, '"Cơn mưa" chỉ hiện tượng tự nhiên.', $d);
        $this->quiz($s, 'Từ nào sau đây là động từ chỉ trạng thái?', ['đá bóng', 'buồn ngủ', 'viết bài', 'gặt lúa'], 1, '"Buồn ngủ" diễn tả trạng thái của con người, không phải hành động mạnh.', $d);
        $this->quiz($s, 'Từ nào sau đây là tính từ chỉ màu sắc?', ['vàng óng', 'hát hay', 'bàn gỗ', 'cô giáo'], 0, '"Vàng óng" chỉ màu sắc rực rỡ, thường tả lúa chín, nắng.', $d);
        $this->quiz($s, 'Trong câu "Gà trống gáy vang.", động từ là từ nào?', ['Gà trống', 'gáy', 'vang', 'Gà'], 1, '"Gáy" chỉ hành động kêu của gà trống; "vang" là tính từ bổ nghĩa.', $d);
        $this->quiz($s, 'Từ "sách" trong cụm "quyển sách hay" thuộc từ loại nào?', ['Danh từ', 'Động từ', 'Tính từ', 'Đại từ'], 0, '"Sách" gọi tên đồ vật; "quyển" là phụ từ chỉ đơn vị, "hay" là tính từ.', $d);

        $this->matching($s, 'Nối mỗi từ với từ loại của nó trong câu "Nắng sớm ấm áp.".', [['Nắng', 'Danh từ'], ['sớm', 'Tính từ'], ['ấm', 'Tính từ'], ['áp', 'Tính từ (trong "ấm áp")']], '"Ấm áp" là tính từ ghép chỉ cảm giác dễ chịu của nắng sớm.', $d);
        $this->matching($s, 'Nối mỗi danh từ với loại danh từ của nó.', [['Bác Hồ', 'Danh từ riêng'], ['người nông dân', 'Danh từ chung'], ['trận bão', 'Danh từ chỉ hiện tượng'], ['ước mơ', 'Danh từ chỉ khái niệm']], 'Ôn lại phân loại danh từ qua các ví dụ gần gũi.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với động từ chính trong câu.', [['"Mẹ quét nhà."', 'quét'], ['"Bé tập viết."', 'tập'], ['"Chim bay cao."', 'bay'], ['"Hoa tỏa hương."', 'tỏa']], 'Động từ chính là trung tâm của vị ngữ, trả lời "làm gì?".', $d);
        $this->matching($s, 'Hãy nối mỗi tính từ với đặc điểm nó diễn tả.', [['cao lớn', 'Kích thước'], ['hiền hậu', 'Tính cách'], ['ngọt ngào', 'Mùi vị'], ['nhanh nhẹn', 'Dáng vẻ hoạt động']], 'Tính từ miêu tả nhiều khía cạnh: kích thước, tính cách, mùi vị...', $d);
        $this->matching($s, 'Nối mỗi từ trong câu "Em bé cười khanh khách." với từ loại.', [['Em bé', 'Danh từ'], ['cười', 'Động từ'], ['khanh khách', 'Từ láy (tính từ)'], ['cả câu', 'Câu đơn']], '"Khanh khách" là từ láy tượng thanh bổ nghĩa cho "cười".', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm DANH TỪ CHỈ NGƯỜI hoặc DANH TỪ CHỈ SỰ VẬT.', [['cô giáo', 'DANH TỪ CHỈ NGƯỜI'], ['ngôi trường', 'DANH TỪ CHỈ SỰ VẬT'], ['bác nông dân', 'DANH TỪ CHỈ NGƯỜI'], ['cánh đồng', 'DANH TỪ CHỈ SỰ VẬT']], 'Người: cô giáo, bác nông dân. Sự vật: trường học, cánh đồng.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm ĐỘNG TỪ hoặc TÍNH TỪ.', [['nhảy múa', 'ĐỘNG TỪ'], ['duyên dáng', 'TÍNH TỪ'], ['ca hát', 'ĐỘNG TỪ'], ['dịu dàng', 'TÍNH TỪ']], '"Làm gì?" → động từ. "Thế nào?" → tính từ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm TÍNH TỪ CHỈ MÀU SẮC hoặc CHỈ ÂM THANH.', [['đỏ rực', 'CHỈ MÀU SẮC'], ['rì rào', 'CHỈ ÂM THANH'], ['xanh biếc', 'CHỈ MÀU SẮC'], ['lộp độp', 'CHỈ ÂM THANH']], 'Màu sắc: cái nhìn thấy. Âm thanh: cái nghe thấy.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm DANH TỪ RIÊNG hoặc DANH TỪ CHUNG.', [['sông Hồng', 'DANH TỪ RIÊNG'], ['dòng sông', 'DANH TỪ CHUNG'], ['Hà Nội', 'DANH TỪ RIÊNG'], ['thành phố', 'DANH TỪ CHUNG']], 'Danh từ riêng: tên cụ thể, viết hoa. Danh từ chung: tên loại chung.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ TÍNH TỪ hoặc KHÔNG CÓ TÍNH TỪ.', [['"Trời trong xanh."', 'CÓ TÍNH TỪ'], ['"Mẹ đi chợ."', 'KHÔNG CÓ TÍNH TỪ'], ['"Bé rất ngoan."', 'CÓ TÍNH TỪ'], ['"Chim bay."', 'KHÔNG CÓ TÍNH TỪ']], 'Tìm từ chỉ đặc điểm, tính chất trong câu.', $d);

        $this->fill($s, 'Từ chỉ người, vật, hiện tượng, khái niệm gọi là danh ___.', [[0, 'từ']], 'Danh từ là từ loại cơ bản nhất, gọi tên mọi sự vật.', $d);
        $this->fill($s, 'Từ chỉ hoạt động, trạng thái của sự vật gọi là động ___.', [[0, 'từ']], 'Động từ trả lời câu hỏi "làm gì?".', $d);
        $this->fill($s, 'Từ "hiền hậu" chỉ tính nết con người nên là tính ___.', [[0, 'từ']], 'Tính từ chỉ tính cách: hiền hậu, chăm chỉ, thật thà.', $d);
        $this->fill($s, 'Danh từ riêng như "Hồ Gươm" phải viết ___ chữ cái đầu mỗi tiếng.', [[0, 'hoa']], 'Viết hoa là quy tắc bắt buộc với danh từ riêng.', $d);
        $this->fill($s, 'Trong câu "Ve ___ râm ran.", điền động từ chỉ tiếng kêu của ve: "kêu".', [[0, 'kêu']], '"Kêu râm ran" là cụm từ quen thuộc tả tiếng ve mùa hè.', $d);
    }

    /* ============ tv-tu-va-cau-lop-7-1 (Tiếng Việt 7, trung_binh) ============ */
    private function seedTvTuVaCauLop71(): void
    {
        $s = 'tv-tu-va-cau-lop-7-1'; $d = 'trung_binh';
        $this->quiz($s, 'Cụm từ nào dưới đây có từ trung tâm là danh từ?', ['đang bay', 'những cánh én', 'rất đẹp', 'hát hay'], 1, '"Những cánh én" có từ trung tâm "cánh" là danh từ.', $d);
        $this->quiz($s, 'Trong cụm "mấy chú chim non", từ trung tâm là từ nào?', ['mấy', 'chú', 'chim', 'non'], 2, '"Chim" là danh từ trung tâm; "mấy, chú" phụ trước, "non" phụ sau.', $d);
        $this->quiz($s, 'Cụm từ nào dưới đây có từ trung tâm là động từ?', ['sẽ đi thăm', 'những bông hoa', 'rất xinh', 'cái bàn'], 0, '"Sẽ đi thăm" có động từ trung tâm "đi" và phụ từ "sẽ".', $d);
        $this->quiz($s, 'Cụm tính từ "hơi se lạnh" có cấu tạo nào?', ['Phụ trước + tính từ trung tâm', 'Tính từ + phụ sau', 'Phụ trước + phụ sau', 'Chỉ có từ trung tâm'], 0, '"Hơi" là phụ từ chỉ mức độ đứng trước tính từ trung tâm "se lạnh".', $d);
        $this->quiz($s, 'Trong câu "Đàn cò trắng bay lượn.", cụm danh từ làm chủ ngữ là gì?', ['Đàn cò trắng', 'bay lượn', 'Đàn', 'trắng'], 0, '"Đàn cò trắng": trung tâm "cò", phụ trước "đàn", phụ sau "trắng".', $d);

        $this->matching($s, 'Nối mỗi cụm từ với loại cụm từ của nó.', [['những đám mây', 'Cụm danh từ'], ['đang trôi', 'Cụm động từ'], ['rất êm đềm', 'Cụm tính từ'], ['mấy con thuyền', 'Cụm danh từ']], 'Gọi tên cụm theo từ loại của từ trung tâm.', $d);
        $this->matching($s, 'Hãy nối mỗi cụm từ với từ trung tâm của nó.', [['những cánh đồng', 'cánh'], ['đang gặt lúa', 'gặt'], ['rất chăm chỉ', 'chăm chỉ'], ['một bầu trời', 'bầu']], 'Từ trung tâm là "hạt nhân" ý nghĩa của cụm từ.', $d);
        $this->matching($s, 'Nối mỗi từ với vai trò của nó trong cụm "sẽ đến trường".', [['sẽ', 'Phụ trước (thời gian)'], ['đến', 'Động từ trung tâm'], ['trường', 'Phụ sau (bổ ngữ)'], ['cả cụm', 'Cụm động từ']], 'Cấu tạo cụm động từ: phụ trước + trung tâm + phụ sau.', $d);
        $this->matching($s, 'Nối mỗi cụm từ với thành phần câu nó đảm nhận trong "Chim hót líu lo.".', [['Chim', 'Chủ ngữ'], ['hót', 'Vị ngữ (trung tâm)'], ['líu lo', 'Bổ ngữ'], ['cả câu', 'Câu đơn']], 'Cụm từ có thể đảm nhận các thành phần câu khác nhau.', $d);
        $this->matching($s, 'Nối mỗi phụ từ với vị trí của nó trong cụm.', [['những, các', 'Phụ trước cụm danh từ'], ['đang, sẽ', 'Phụ trước cụm động từ'], ['rất, hơi', 'Phụ trước cụm tính từ'], ['lên, xuống, ra, vào', 'Phụ sau cụm động từ']], 'Vị trí phụ từ giúp nhận diện loại cụm từ nhanh chóng.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi cụm từ vào nhóm CỤM DANH TỪ hoặc CỤM ĐỘNG TỪ.', [['những bông hoa', 'CỤM DANH TỪ'], ['đang nở rộ', 'CỤM ĐỘNG TỪ'], ['mấy chú bé', 'CỤM DANH TỪ'], ['sẽ về quê', 'CỤM ĐỘNG TỪ'], ['cánh diều', 'CỤM DANH TỪ'], ['đã bay cao', 'CỤM ĐỘNG TỪ']], 'Trung tâm là danh từ → cụm danh từ; là động từ → cụm động từ.', $d);
        $this->sortQ($s, 'Kéo mỗi cụm từ vào nhóm CỤM TÍNH TỪ hoặc KHÔNG PHẢI CỤM TÍNH TỪ.', [['rất vui vẻ', 'CỤM TÍNH TỪ'], ['những niềm vui', 'KHÔNG PHẢI CỤM TÍNH TỪ'], ['hơi buồn', 'CỤM TÍNH TỪ'], ['đang cười', 'KHÔNG PHẢI CỤM TÍNH TỪ']], 'Cụm tính từ có từ trung tâm là tính từ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ trong cụm "những học sinh chăm ngoan" vào nhóm đúng.', [['những', 'PHỤ TRƯỚC'], ['học sinh', 'TỪ TRUNG TÂM'], ['chăm ngoan', 'PHỤ SAU']], 'Ba thành phần của cụm từ: phụ trước – trung tâm – phụ sau.', $d);
        $this->sortQ($s, 'Kéo mỗi cụm từ vào nhóm THEO TỪ TRUNG TÂM LÀ DANH TỪ hoặc ĐỘNG TỪ.', [['cánh đồng lúa', 'TRUNG TÂM LÀ DANH TỪ'], ['đang cấy lúa', 'TRUNG TÂM LÀ ĐỘNG TỪ'], ['bông lúa chín', 'TRUNG TÂM LÀ DANH TỪ'], ['sẽ gặt hái', 'TRUNG TÂM LÀ ĐỘNG TỪ']], 'Tìm "hạt nhân" của cụm rồi gọi tên theo từ loại của nó.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm PHỤ TRƯỚC hoặc PHỤ SAU trong cụm từ.', [['những', 'PHỤ TRƯỚC'], ['đẹp (hoa đẹp)', 'PHỤ SAU'], ['đang', 'PHỤ TRƯỚC'], ['lên (bay lên)', 'PHỤ SAU']], 'Phụ trước đứng trước trung tâm; phụ sau đứng sau trung tâm.', $d);

        $this->fill($s, 'Trong cụm từ, ngoài từ trung tâm còn có các phụ từ đứng trước và đứng ___ nó.', [[0, 'sau']], 'Cấu tạo đầy đủ: phụ trước + từ trung tâm + phụ sau.', $d);
        $this->fill($s, '"Đàn cò trắng đang bay" — cụm "đàn cò trắng" là cụm danh ___.', [[0, 'từ']], 'Trung tâm "cò" là danh từ nên cả cụm là cụm danh từ.', $d);
        $this->fill($s, '"Sẽ đến trường" có động từ trung tâm "đến" nên là cụm động ___.', [[0, 'từ']], 'Phụ từ "sẽ" chỉ thời gian tương lai đi kèm động từ.', $d);
        $this->fill($s, 'Trong cụm "rất đẹp", từ trung tâm là tính từ "___".', [[0, 'đẹp']], '"Rất" là phụ từ chỉ mức độ, "đẹp" là từ trung tâm.', $d);
        $this->fill($s, 'Cụm danh từ thường làm chủ ngữ, cụm động từ và cụm tính từ thường làm vị ___.', [[0, 'ngữ']], 'Vị trí cú pháp quen thuộc của các cụm từ trong câu.', $d);
    }

    /* ============ tv-tu-va-cau-lop-7-2 (Tiếng Việt 7, trung_binh) ============ */
    private function seedTvTuVaCauLop72(): void
    {
        $s = 'tv-tu-va-cau-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Câu "Trên cành cây, chim hót líu lo." có mấy cụm chủ – vị?', ['1', '2', '3', '0'], 0, 'Chỉ có một cụm chủ – vị: "chim / hót líu lo" ("trên cành cây" là trạng ngữ).', $d);
        $this->quiz($s, 'Trong câu "Bác nông dân đang cày ruộng.", vị ngữ là gì?', ['Bác nông dân', 'đang cày ruộng', 'Bác', 'cày ruộng'], 1, 'Vị ngữ "đang cày ruộng" cho biết bác nông dân đang làm gì.', $d);
        $this->quiz($s, 'Câu nào sau đây là câu đơn có trạng ngữ?', ['"Sáng nay, em đi học."', '"Trời mưa."', '"Mẹ nấu cơm."', '"Bé ngủ."'], 0, '"Sáng nay" là trạng ngữ chỉ thời gian đứng đầu câu.', $d);
        $this->quiz($s, 'Trong câu "Hoa phượng nở đỏ rực sân trường.", bộ phận nào là bổ ngữ?', ['Hoa phượng', 'nở', 'đỏ rực', 'sân trường'], 3, '"Sân trường" bổ sung nơi chốn cho động từ "nở" — là bổ ngữ.', $d);
        $this->quiz($s, 'Câu "Em thích đọc sách." có cấu tạo như thế nào?', ['Chủ ngữ + vị ngữ (vị ngữ là cụm động từ)', 'Chỉ có chủ ngữ', 'Chỉ có vị ngữ', 'Hai cụm chủ – vị'], 0, '"Em / thích đọc sách": vị ngữ là cụm động từ "thích đọc sách".', $d);

        $this->matching($s, 'Hãy nối mỗi câu với chủ ngữ của nó.', [['"Mặt trời lên cao."', 'Mặt trời'], ['"Gió thổi vi vu."', 'Gió'], ['"Lá rơi đầy sân."', 'Lá'], ['"Trẻ em vui đùa."', 'Trẻ em']], 'Chủ ngữ trả lời "ai? cái gì?" và thường đứng đầu câu.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với vị ngữ của nó.', [['"Mặt trời lên cao."', 'lên cao'], ['"Gió thổi vi vu."', 'thổi vi vu'], ['"Lá rơi đầy sân."', 'rơi đầy sân'], ['"Trẻ em vui đùa."', 'vui đùa']], 'Vị ngữ trả lời "làm gì? thế nào?" về chủ ngữ.', $d);
        $this->matching($s, 'Hãy nối mỗi thành phần câu với câu hỏi dùng để tìm nó.', [['Trạng ngữ', 'Khi nào? Ở đâu?'], ['Định ngữ', 'Nào? (bổ nghĩa danh từ)'], ['Bổ ngữ', 'Bổ sung cho động từ, tính từ'], ['Chủ ngữ', 'Ai? Cái gì?']], 'Mỗi thành phần có câu hỏi nhận diện riêng.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với loại câu của nó.', [['"Trời nắng đẹp."', 'Câu đơn'], ['"Mưa tạnh, nắng lên."', 'Câu ghép'], ['"Chim hót."', 'Câu đơn'], ['"Em học, bạn chơi."', 'Câu ghép']], 'Đếm cụm chủ – vị để phân biệt câu đơn và câu ghép.', $d);
        $this->matching($s, 'Nối mỗi cụm từ trong câu "Cánh đồng lúa chín vàng." với vai trò.', [['Cánh đồng', 'Chủ ngữ'], ['lúa', 'Định ngữ (làm rõ "cánh đồng")'], ['chín vàng', 'Vị ngữ'], ['cả câu', 'Câu đơn']], 'Phân tích chi tiết từng thành phần giúp nắm vững cấu tạo câu.', $d);

        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU ĐƠN CÓ TRẠNG NGỮ hoặc CÂU ĐƠN KHÔNG CÓ TRẠNG NGỮ.', [['"Sáng nay, em đi học."', 'CÓ TRẠNG NGỮ'], ['"Em đi học."', 'KHÔNG CÓ TRẠNG NGỮ'], ['"Trên sân, các bạn chơi đùa."', 'CÓ TRẠNG NGỮ'], ['"Các bạn chơi đùa."', 'KHÔNG CÓ TRẠNG NGỮ']], 'Trạng ngữ đứng đầu câu, ngăn cách bằng dấu phẩy, bổ sung hoàn cảnh.', $d);
        $this->sortQ($s, 'Kéo mỗi cụm từ vào nhóm CHỦ NGỮ hoặc VỊ NGỮ trong câu "Ve sầu kêu râm ran.".', [['Ve sầu', 'CHỦ NGỮ'], ['kêu râm ran', 'VỊ NGỮ']], 'Câu đơn điển hình: danh từ làm chủ ngữ, cụm động từ làm vị ngữ.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU KỂ hoặc CÂU BỘC LỘ CẢM XÚC.', [['"Quê hương em rất đẹp."', 'CÂU KỂ'], ['"Quê hương em đẹp quá!"', 'CÂU BỘC LỘ CẢM XÚC'], ['"Lúa chín vàng."', 'CÂU KỂ'], ['"Lúa chín vàng óng ả!"', 'CÂU BỘC LỘ CẢM XÚC']], 'Câu cảm thán bộc lộ cảm xúc mạnh, kết thúc bằng dấu chấm than.', $d);
        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm THÀNH PHẦN CHÍNH hoặc THÀNH PHẦN PHỤ của câu.', [['Chủ ngữ', 'THÀNH PHẦN CHÍNH'], ['Vị ngữ', 'THÀNH PHẦN CHÍNH'], ['Trạng ngữ', 'THÀNH PHẦN PHỤ'], ['Định ngữ', 'THÀNH PHẦN PHỤ']], 'Nòng cốt câu: chủ – vị. Các thành phần còn lại là phụ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÂU ĐƠN hoặc CÂU GHÉP.', [['"Sông chảy êm đềm."', 'CÂU ĐƠN'], ['"Nước lên, thuyền trôi."', 'CÂU GHÉP'], ['"Trăng sáng vằng vặc."', 'CÂU ĐƠN'], ['"Gió ngừng, lá lặng."', 'CÂU GHÉP']], 'Câu ghép thường có hai vế đối nhau, ngăn cách bằng dấu phẩy.', $d);

        $this->fill($s, 'Câu đơn có một cụm chủ ngữ – vị ___.', [[0, 'ngữ']], 'Một cụm chủ – vị là dấu hiệu nhận biết câu đơn.', $d);
        $this->fill($s, 'Trong câu "Ong bay vù vù.", chủ ngữ là "___".', [[0, 'ong']], '"Ong / bay vù vù": chủ ngữ "ong", vị ngữ "bay vù vù".', $d);
        $this->fill($s, 'Trong câu, bộ phận dùng để trả lời các câu hỏi "làm gì?", "thế nào?" được gọi là vị ___.', [[0, 'ngữ']], 'Vị ngữ là thành phần chính thứ hai của câu, sau chủ ngữ.', $d);
        $this->fill($s, 'Câu "Mây đen kéo đến, trời sắp mưa." là câu ___ (đơn hay ghép).', [[0, 'ghép']], 'Hai cụm chủ – vị: "mây đen / kéo đến" và "trời / sắp mưa" → câu ghép.', $d);
        $this->fill($s, 'Trạng ngữ "sáng nay" trong câu "Sáng nay, em đi học." chỉ thời ___.', [[0, 'gian']], 'Trạng ngữ chỉ thời gian trả lời câu hỏi "khi nào?".', $d);
    }

    /* ============ tv-chinh-ta-lop-6-1 (Tiếng Việt 6, de) ============ */
    private function seedTvChinhTaLop61(): void
    {
        $s = 'tv-chinh-ta-lop-6-1'; $d = 'de';
        $this->quiz($s, 'Chọn cách viết đúng của từ chỉ sự siêng năng.', ['chăm chỉ', 'trăm chỉ', 'chăm chĩ', 'trăm chĩ'], 0, '"Chăm chỉ" nghĩa là siêng năng, viết với âm đầu "ch"; "trăm" là số đếm 100.', $d);
        $this->quiz($s, 'Từ nào sau đây viết đúng với âm đầu "x"?', ['xinh đẹp', 'sinh đẹp', 'xinh đep', 'sinh đep'], 0, '"Xinh đẹp" viết với âm đầu "x"; các cách viết còn lại đều sai chính tả.', $d);
        $this->quiz($s, 'Từ nào viết SAI chính tả?', ['trong trắng', 'chong trắng', 'trắng trẻo', 'trong trẻo'], 1, '"Trong trắng" (trong sạch) viết với "tr", không phải "chong trắng".', $d);
        $this->quiz($s, 'Từ "rõ ràng" viết đúng với âm đầu nào?', ['r', 'd', 'gi', 'tr'], 0, '"Rõ ràng" viết với âm đầu "r" ở cả hai tiếng.', $d);
        $this->quiz($s, 'Cặp từ nào sau đây đều viết ĐÚNG chính tả?', ['chăm chỉ – siêng năng', 'trăm chỉ – siêng năng', 'chăm chỉ – siêng nang', 'trăm chỉ – siêng nang'], 0, '"Chăm chỉ" (ch), "siêng năng" (s) đều viết đúng.', $d);

        $this->matching($s, 'Hãy nối mỗi âm đầu với từ viết đúng có âm đầu đó.', [['ch', 'chăm chỉ'], ['tr', 'trong trẻo'], ['s', 'sáng sủa'], ['x', 'xinh xắn']], 'Bốn cặp âm đầu dễ nhầm: ch/tr, s/x, d/gi/r.', $d);
        $this->matching($s, 'Hãy nối mỗi từ láy với âm đầu đúng của nó.', [['rõ ràng', 'r'], ['rực rỡ', 'r'], ['rì rào', 'r'], ['róc rách', 'r']], 'Nhóm từ láy âm đầu "r" rất phong phú và hay nhầm với "d", "gi".', $d);
        $this->matching($s, 'Hãy nối mỗi chỗ trống với âm đầu đúng.', [['"___ăm chỉ"', 'ch'], ['"___ong trẻo"', 'tr'], ['"___áng sủa"', 's'], ['"___inh xắn"', 'x']], '"Chăm chỉ, trong trẻo, sáng sủa, xinh xắn" — bốn từ mẫu cho bốn âm đầu.', $d);
        $this->matching($s, 'Nối mỗi cặp từ dễ nhầm với nghĩa để phân biệt.', [['chăm (chăm chỉ)', 'Siêng năng'], ['trăm (một trăm)', 'Số đếm 100'], ['trong (trong trẻo)', 'Trong, sạch'], ['chong (chong chóng)', 'Đồ chơi quay']], 'Nhớ nghĩa để nhớ âm đầu: chăm chỉ khác một trăm.', $d);
        $this->matching($s, 'Nối mỗi từ với cách viết đúng.', [['siêng năng', 's – n'], ['sáng sủa', 's – s'], ['xôn xao', 'x – x'], ['sôi nổi', 's – n']], 'Nhóm từ âm đầu "s" cũng dễ nhầm với "x", cần luyện kĩ.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm VIẾT VỚI CH hoặc VIẾT VỚI TR.', [['chăm', 'VIẾT VỚI CH'], ['trăm', 'VIẾT VỚI TR'], ['chong', 'VIẾT VỚI CH'], ['trong', 'VIẾT VỚI TR']], '"Chăm" (siêng) viết ch; "trăm" (100) viết tr — nghĩa khác nhau.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm VIẾT VỚI S hoặc VIẾT VỚI X.', [['sáng', 'VIẾT VỚI S'], ['xinh', 'VIẾT VỚI X'], ['sôi', 'VIẾT VỚI S'], ['xanh', 'VIẾT VỚI X'], ['sạch', 'VIẾT VỚI S'], ['xếp', 'VIẾT VỚI X']], 'Phân biệt s/x qua nghĩa và thói quen, tra từ điển khi không chắc.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm VIẾT VỚI R hoặc VIẾT VỚI D.', [['rõ', 'VIẾT VỚI R'], ['dõng', 'SAI'], ['rực', 'VIẾT VỚI R'], ['dực', 'SAI']], 'Chú ý: "dõng, dực" là cách viết sai, không có trong từ điển.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm VIẾT VỚI GI hoặc VIẾT VỚI D.', [['gió', 'VIẾT VỚI GI'], ['dó', 'SAI'], ['giúp', 'VIẾT VỚI GI'], ['dúp', 'SAI']], '"Dó, dúp" là cách viết sai chính tả cần tránh.', $d);
        $this->sortQ($s, 'Kéo mỗi cách viết vào nhóm ĐÚNG hoặc SAI.', [['chăm chỉ', 'ĐÚNG'], ['trăm chỉ', 'SAI'], ['sáng sủa', 'ĐÚNG'], ['sáng xủa', 'SAI'], ['xinh xắn', 'ĐÚNG'], ['xinh xắng', 'SAI']], 'Đọc to và đối chiếu nghĩa để tự kiểm tra chính tả.', $d);

        $this->fill($s, 'Từ chỉ sự sáng rõ, gọn gàng viết đúng là "sáng ___": "sủa".', [[0, 'sủa']], '"Sáng sủa" (s – s): sáng rõ, thoáng đãng.', $d);
        $this->fill($s, '"___inh xắn" (xinh đẹp): điền âm đầu đúng "x" — "xinh xắn".', [[0, 'x']], '"Xinh xắn" viết với âm đầu "x" ở cả hai tiếng.', $d);
        $this->fill($s, 'Từ "___ăm chỉ" (siêng năng): điền âm đầu đúng "ch" — "chăm chỉ".', [[0, 'ch']], '"Chăm chỉ" viết với "ch", đừng nhầm với "trăm" (số 100).', $d);
        $this->fill($s, '"Rõ ___" (minh bạch): điền tiếng đúng "ràng" — "rõ ràng".', [[0, 'ràng']], '"Rõ ràng" viết với âm đầu "r" ở cả hai tiếng.', $d);
        $this->fill($s, 'Từ "___ong trẻo" (âm thanh trong): điền âm đầu "tr" — "trong trẻo".', [[0, 'tr']], '"Trong trẻo" viết với "tr", nghĩa là trong và vang.', $d);
    }

    /* ============ tv-chinh-ta-lop-6-2 (Tiếng Việt 6, de) ============ */
    private function seedTvChinhTaLop62(): void
    {
        $s = 'tv-chinh-ta-lop-6-2'; $d = 'de';
        $this->quiz($s, 'Từ nào sau đây viết đúng với dấu hỏi?', ['nghỉ ngơi', 'nghĩ ngơi', 'nghỉ ngoi', 'nghĩ ngoi'], 0, '"Nghỉ ngơi" viết với dấu hỏi ở cả hai tiếng; các cách viết còn lại đều sai.', $d);
        $this->quiz($s, 'Từ nào sau đây viết đúng với dấu ngã?', ['suy nghĩ', 'suy nghỉ', 'suy ngĩ', 'suy nghỹ'], 0, '"Suy nghĩ" viết với dấu ngã ở tiếng "nghĩ"; các cách viết còn lại đều sai.', $d);
        $this->quiz($s, 'Từ nào viết SAI chính tả?', ['giữ gìn', 'dữ gìn', 'gìn giữ', 'giữ gìn'], 1, '"Giữ gìn" viết với "gi"; "dữ gìn" là sai.', $d);
        $this->quiz($s, 'Từ "lẽ phải" viết đúng là?', ['lẽ phải (ngã – hỏi)', 'lẻ phải', 'lẽ phãi', 'lẻ phãi'], 0, '"Lẽ" dấu ngã, "phải" dấu hỏi — cặp từ cần nhớ kĩ.', $d);
        $this->quiz($s, 'Cặp từ nào sau đây đều viết đúng dấu thanh?', ['nghỉ ngơi – suy nghĩ', 'nghĩ ngơi – suy nghỉ', 'nghỉ ngơi – suy nghỉ', 'nghĩ ngơi – suy nghĩ'], 0, '"Nghỉ ngơi" (hỏi – hỏi) và "suy nghĩ" (không dấu – ngã) đều viết đúng dấu thanh.', $d);

        $this->matching($s, 'Hãy nối mỗi từ với dấu thanh đúng của nó.', [['vẻ', 'Dấu hỏi'], ['vẽ', 'Dấu ngã'], ['kỉ', 'Dấu hỏi'], ['kĩ', 'Dấu ngã']], '"Vẻ vang" (hỏi), "vẽ tranh" (ngã); "kỉ niệm" (hỏi), "kĩ thuật" (ngã).', $d);
        $this->matching($s, 'Hãy nối mỗi câu với từ viết đúng điền vào chỗ trống.', [['"Đội bóng giành ___ vang."', 'vẻ'], ['"Em ___ một bức tranh."', 'vẽ'], ['"Đó là ___ niệm đẹp."', 'kỉ'], ['"Anh ấy rất có ___ thuật."', 'kĩ']], 'Đặt vào ngữ cảnh để chọn dấu hỏi hay ngã cho đúng.', $d);
        $this->matching($s, 'Nối mỗi từ với nghĩa của nó.', [['"vẻ" (vẻ vang)', 'Vinh quang'], ['"vẽ" (vẽ tranh)', 'Tạo hình bằng bút'], ['"kỉ" (kỉ niệm)', 'Điều đáng nhớ'], ['"kĩ" (kĩ thuật)', 'Phương pháp, tay nghề']], 'Nghĩa khác nhau → dấu khác nhau, dù phát âm gần giống.', $d);
        $this->matching($s, 'Hãy nối mỗi từ láy với dấu thanh của nó.', [['vui vẻ', 'Không – hỏi'], ['mạnh mẽ', 'Nặng – ngã'], ['lẻ loi', 'Hỏi – hỏi'], ['vội vã', 'Hỏi – ngã']], 'Từ láy cũng phải đúng dấu từng tiếng.', $d);
        $this->matching($s, 'Nối mỗi cặp dễ nhầm với mẹo nhớ.', [['vẻ – vẽ', '"Vẻ vang" (hỏi) vinh quang'], ['kỉ – kĩ', '"Kỉ niệm" (hỏi) đáng nhớ'], ['giữ – dữ', '"Giữ gìn" (gi) cẩn thận'], ['lẽ – lẻ', '"Lẽ phải" (ngã) đúng đắn']], 'Mẹo: gắn từ với một cụm quen thuộc để nhớ dấu.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm DẤU HỎI hoặc DẤU NGÃ.', [['vẻ', 'DẤU HỎI'], ['vẽ', 'DẤU NGÃ'], ['kỉ', 'DẤU HỎI'], ['kĩ', 'DẤU NGÃ'], ['lẻ', 'DẤU HỎI'], ['lẽ', 'DẤU NGÃ']], 'Ba cặp "kinh điển": vẻ–vẽ, kỉ–kĩ, lẻ–lẽ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm VIẾT ĐÚNG hoặc VIẾT SAI.', [['"Em giữ gìn sách vở."', 'VIẾT ĐÚNG'], ['"Em dữ gìn sách vở."', 'VIẾT SAI'], ['"Đó là kỉ niệm đẹp."', 'VIẾT ĐÚNG'], ['"Đó là kĩ niệm đẹp."', 'VIẾT SAI']], '"Giữ gìn" viết gi; "kỉ niệm" viết k + hỏi.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ (theo nghĩa đã cho) vào nhóm DẤU HỎI hoặc DẤU NGÃ.', [['"mở" (mở cửa)', 'DẤU HỎI'], ['"mỡ" (dầu mỡ)', 'DẤU NGÃ'], ['"củ" (củ khoai)', 'DẤU HỎI'], ['"cũ" (đồ cũ)', 'DẤU NGÃ']], 'Nghĩa quyết định dấu: mở cửa (hỏi), dầu mỡ (ngã).', $d);
        $this->sortQ($s, 'Kéo mỗi cách viết vào nhóm ĐÚNG hoặc SAI.', [['vẻ vang', 'ĐÚNG'], ['vẽ vang', 'SAI'], ['kĩ thuật', 'ĐÚNG'], ['kỉ thuật', 'SAI']], '"Vẻ vang" (vinh quang) khác "vẽ" (vẽ tranh); "kĩ thuật" khác "kỉ niệm".', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm VIẾT VỚI GI hoặc VIẾT VỚI D.', [['giữ', 'VIẾT VỚI GI'], ['dữ', 'VIẾT VỚI D'], ['gìn', 'VIẾT VỚI GI'], ['dìn', 'SAI']], '"Giữ gìn" viết gi; "dữ" (hung dữ) viết d — nghĩa khác nhau.', $d);

        $this->fill($s, '"Vẻ ___" (vinh quang): điền tiếng đúng dấu hỏi — "vang".', [[0, 'vang']], '"Vẻ vang": hỏi – không dấu. Nghĩa là vinh quang, rạng rỡ.', $d);
        $this->fill($s, 'Từ chỉ điều đáng nhớ viết đúng là "kỉ ___": "niệm".', [[0, 'niệm']], '"Kỉ niệm" viết với "k" và dấu hỏi ở "kỉ".', $d);
        $this->fill($s, '"Giữ ___" (bảo quản cẩn thận): điền tiếng đúng "gìn" — "giữ gìn".', [[0, 'gìn']], '"Giữ gìn" viết với âm đầu "gi" ở cả hai tiếng.', $d);
        $this->fill($s, '"Lẽ ___" (điều đúng đắn): điền tiếng đúng dấu hỏi — "phải".', [[0, 'phải']], '"Lẽ phải": ngã – hỏi. Nghĩa là điều đúng đắn, hợp đạo lí.', $d);
        $this->fill($s, 'Từ "vội ___" (gấp gáp): điền tiếng đúng dấu ngã — "vã".', [[0, 'vã']], '"Vội vã": hỏi – ngã. Nghĩa là gấp gáp, vội vàng.', $d);
    }

    /* ============ tv-chinh-ta-lop-7-1 (Tiếng Việt 7, trung_binh) ============ */
    private function seedTvChinhTaLop71(): void
    {
        $s = 'tv-chinh-ta-lop-7-1'; $d = 'trung_binh';
        $this->quiz($s, 'Tên riêng nào viết SAI chính tả?', ['hồ Chí Minh', 'Hồ Chí Minh', 'Hồ chí Minh', 'hồ chí minh'], 0, '"hồ Chí Minh" sai vì danh từ riêng phải viết hoa chữ cái đầu của mọi tiếng.', $d);
        $this->quiz($s, 'Địa danh nào viết đúng chính tả?', ['Vịnh Hạ Long', 'Vịnh hạ Long', 'vịnh Hạ Long', 'Vịnh Hạ long'], 0, '"Vịnh Hạ Long" viết hoa chữ cái đầu cả ba tiếng mới đúng quy tắc tên riêng.', $d);
        $this->quiz($s, 'Từ Hán Việt nào sau đây viết sai chính tả?', ['tỗ quốc', 'tổ quốc', 'tổ quấc', 'tỗ quấc'], 0, 'Viết đúng là "tổ quốc" (dấu hỏi ở "tổ"); các cách viết còn lại đều sai.', $d);
        $this->quiz($s, 'Chọn cách viết đúng của tên tác giả Truyện Kiều.', ['Nguyễn Du', 'Nguyễn du', 'nguyễn Du', 'nguyễn du'], 0, '"Nguyễn Du" — tên riêng phải viết hoa chữ cái đầu của mỗi tiếng.', $d);
        $this->quiz($s, 'Từ nào viết SAI chính tả?', ['giang sơn', 'giang xơn', 'sơn hà', 'đất nước'], 1, '"Giang sơn" viết với "s"; "giang xơn" là sai.', $d);

        $this->matching($s, 'Hãy nối mỗi tên riêng với quy tắc viết hoa.', [['Hồ Chí Minh', 'Viết hoa mọi tiếng'], ['sông Cửu Long', 'Viết hoa mọi tiếng'], ['Trường Sa', 'Viết hoa mọi tiếng'], ['Nguyễn Trãi', 'Viết hoa mọi tiếng']], 'Tên riêng tiếng Việt: viết hoa chữ cái đầu của tất cả các tiếng.', $d);
        $this->matching($s, 'Hãy nối mỗi từ Hán Việt với nghĩa của nó.', [['tổ quốc', 'Đất nước'], ['giang sơn', 'Đất nước, non sông'], ['đồng bào', 'Người cùng nòi giống'], ['quê hương', 'Nơi chôn rau cắt rốn']], 'Từ Hán Việt thường trang trọng, dùng trong văn viết, diễn văn.', $d);
        $this->matching($s, 'Hãy nối mỗi tên với loại danh từ riêng.', [['Nguyễn Du', 'Tên người'], ['sông Hồng', 'Tên sông'], ['Hà Nội', 'Tên địa danh'], ['Trường Sơn', 'Tên núi']], 'Danh từ riêng gồm: tên người, tên địa danh, tên sông núi...', $d);
        $this->matching($s, 'Hãy nối mỗi tên gọi với cách viết đúng.', [['Bác Hồ', 'Viết hoa'], ['bác nông dân', 'Viết thường'], ['Cô giáo', 'Viết hoa khi là tên gọi'], ['cô bé', 'Viết thường']], '"Bác Hồ" là tên gọi riêng; "bác nông dân" là danh từ chung.', $d);
        $this->matching($s, 'Nối mỗi từ với nhóm từ của nó.', [['tổ quốc', 'Từ Hán Việt'], ['đất nước', 'Từ thuần Việt'], ['giang sơn', 'Từ Hán Việt'], ['quê hương', 'Từ thuần Việt']], 'Từ Hán Việt gốc Hán; từ thuần Việt do dân gian sáng tạo.', $d);

        $this->sortQ($s, 'Kéo mỗi từ vào nhóm VIẾT HOA hoặc KHÔNG VIẾT HOA khi đứng giữa câu.', [['Hà Nội', 'VIẾT HOA'], ['thành phố', 'KHÔNG VIẾT HOA'], ['Bác Hồ', 'VIẾT HOA'], ['người dân', 'KHÔNG VIẾT HOA'], ['Trường Sa', 'VIẾT HOA'], ['biển đảo', 'KHÔNG VIẾT HOA']], 'Danh từ riêng luôn viết hoa dù đứng ở đâu trong câu.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi tên vào nhóm VIẾT HOA ĐÚNG hoặc VIẾT SAI.', [['Nguyễn Du', 'VIẾT HOA ĐÚNG'], ['Nguyễn du', 'VIẾT SAI'], ['Vịnh Hạ Long', 'VIẾT HOA ĐÚNG'], ['Vịnh hạ Long', 'VIẾT SAI']], 'Viết hoa thiếu một tiếng cũng là sai chính tả tên riêng.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm TỪ HÁN VIỆT hoặc TỪ THUẦN VIỆT.', [['tổ quốc', 'TỪ HÁN VIỆT'], ['đất nước', 'TỪ THUẦN VIỆT'], ['sơn hà', 'TỪ HÁN VIỆT'], ['non sông', 'TỪ THUẦN VIỆT']], 'Hán Việt: tổ quốc, sơn hà. Thuần Việt: đất nước, non sông.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm DANH TỪ RIÊNG hoặc DANH TỪ CHUNG.', [['Hồ Gươm', 'DANH TỪ RIÊNG'], ['hồ nước', 'DANH TỪ CHUNG'], ['Cửu Long', 'DANH TỪ RIÊNG'], ['dòng sông', 'DANH TỪ CHUNG']], 'Danh từ riêng: tên cụ thể. Danh từ chung: tên loại chung.', $d);
        $this->sortQ($s, 'Kéo mỗi cách viết tên vào nhóm ĐÚNG hoặc SAI.', [['Trần Hưng Đạo', 'ĐÚNG'], ['Trần hưng Đạo', 'SAI'], ['Điện Biên Phủ', 'ĐÚNG'], ['Điện biên Phủ', 'SAI']], 'Tên riêng nhiều tiếng: viết hoa TẤT CẢ các tiếng.', $d);

        $this->fill($s, 'Đối với tên riêng chỉ người và địa danh, cần viết ___ chữ cái đầu của mỗi tiếng.', [[0, 'hoa']], 'Quy tắc vàng: danh từ riêng viết hoa mọi tiếng.', $d);
        $this->fill($s, 'Viết đúng tên thủ đô của Việt Nam: Hà ___.', [[0, 'Nội']], '"Hà Nội" — viết hoa cả hai tiếng.', $d);
        $this->fill($s, 'Từ Hán Việt chỉ đất nước, viết đúng là "tổ ___": "quốc".', [[0, 'quốc']], '"Tổ quốc" là từ Hán Việt trang trọng chỉ đất nước.', $d);
        $this->fill($s, 'Hãy viết đúng tên của đại thi hào dân tộc: Nguyễn ___.', [[0, 'Du']], '"Nguyễn Du" — tác giả Truyện Kiều, danh nhân văn hóa thế giới.', $d);
        $this->fill($s, 'Tên quần đảo thiêng liêng của Tổ quốc viết đúng là "Trường ___": "Sa".', [[0, 'Sa']], '"Trường Sa" — viết hoa cả hai tiếng, tên riêng địa danh.', $d);
    }

    /* ============ tv-chinh-ta-lop-7-2 (Tiếng Việt 7, trung_binh) ============ */
    private function seedTvChinhTaLop72(): void
    {
        $s = 'tv-chinh-ta-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Chọn từ viết đúng trong các từ sau.', ['rực rỡ', 'rực rở', 'rực rỡ', 'rực rỡ'], 0, '"Rực rỡ" viết với dấu nặng ở "rực" và dấu ngã ở "rỡ"; các cách viết còn lại đều sai.', $d);
        $this->quiz($s, 'Dấu câu nào dùng để kết thúc câu cảm thán?', ['Dấu chấm than', 'Dấu chấm', 'Dấu chấm hỏi', 'Dấu phẩy'], 0, 'Câu cảm thán bộc lộ cảm xúc mạnh nên kết thúc bằng dấu chấm than (!).', $d);
        $this->quiz($s, 'Cuối câu kể (câu trần thuật) thường đặt dấu câu nào?', ['Dấu chấm hỏi', 'Dấu chấm than', 'Dấu chấm', 'Dấu phẩy'], 2, 'Câu kể (câu trần thuật) kết thúc bằng dấu chấm (.).', $d);
        $this->quiz($s, 'Từ nào viết SAI chính tả?', ['lộng lẫy', 'lộng lẩy', 'lấp lánh', 'rực rỡ'], 1, '"Lộng lẫy" viết với dấu nặng và ngã; "lộng lẩy" là sai.', $d);
        $this->quiz($s, 'Từ "chú đáo" viết đúng là?', ['chu đáo', 'chú đáo', 'chu đao', 'chú đao'], 0, '"Chu đáo" viết với "ch" và dấu hỏi, nghĩa là cẩn thận, đầy đủ.', $d);

        $this->matching($s, 'Hãy nối mỗi từ khó với cách viết đúng của nó.', [['rực rỡ', 'r – r (nặng – ngã)'], ['lộng lẫy', 'l – l (nặng – ngã)'], ['trau chuốt', 'tr – ch'], ['chu đáo', 'ch – đ (hỏi – ngã)']], 'Nhóm từ láy "khó nhằn" cần học thuộc lòng từng từ.', $d);
        $this->matching($s, 'Hãy nối mỗi dấu câu với công dụng của nó.', [['Dấu chấm (.)', 'Kết thúc câu kể'], ['Dấu hỏi (?)', 'Kết thúc câu hỏi'], ['Dấu than (!)', 'Kết thúc câu cảm'], ['Dấu phẩy (,)', 'Ngăn cách bộ phận']], 'Ôn lại công dụng các dấu câu cơ bản.', $d);
        $this->matching($s, 'Hãy nối mỗi từ với nghĩa của nó.', [['rực rỡ', 'Tươi sáng, chói lọi'], ['lộng lẫy', 'Đẹp lộng lẫy, sang trọng'], ['trau chuốt', 'Chăm chút cho đẹp'], ['chu đáo', 'Cẩn thận, đầy đủ']], 'Nhớ nghĩa giúp viết đúng chính tả và dùng từ đúng.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với dấu câu đúng ở cuối câu.', [['"Bạn có khỏe không"', '?'], ['"Hôm nay trời đẹp"', '.'], ['"Tuyệt vời quá"', '!'], ['"Hãy cố lên"', '!']], 'Xác định kiểu câu rồi chọn dấu kết thúc phù hợp.', $d);
        $this->matching($s, 'Nối mỗi lỗi chính tả với nguyên nhân.', [['Nhầm r/d', 'Phát âm địa phương'], ['Nhầm hỏi/ngã', 'Không nhớ quy tắc'], ['Nhầm ch/tr', 'Phát âm địa phương'], ['Viết thiếu dấu', 'Vội vàng, cẩu thả']], 'Biết nguyên nhân mới khắc phục được lỗi chính tả triệt để.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi câu (chưa có dấu cuối câu) vào nhóm CẦN DẤU CHẤM THAN hoặc CẦN DẤU CHẤM.', [['Đẹp quá', 'CẦN DẤU CHẤM THAN'], ['Hôm nay trời đẹp', 'CẦN DẤU CHẤM'], ['Hoan hô', 'CẦN DẤU CHẤM THAN'], ['Em đi học', 'CẦN DẤU CHẤM']], 'Câu cảm xúc → dấu than; câu kể → dấu chấm.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm VIẾT ĐÚNG hoặc VIẾT SAI.', [['rực rỡ', 'VIẾT ĐÚNG'], ['rực rở', 'VIẾT SAI'], ['lộng lẫy', 'VIẾT ĐÚNG'], ['lộng lẩy', 'VIẾT SAI'], ['trau chuốt', 'VIẾT ĐÚNG'], ['trau chuốc', 'VIẾT SAI']], 'Nhóm từ láy dễ sai: rực rỡ, lộng lẫy, trau chuốt.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu (chưa có dấu cuối câu) vào nhóm CẦN DẤU HỎI hoặc CẦN DẤU CHẤM.', [['Bạn tên gì', 'CẦN DẤU HỎI'], ['Tôi tên Lan', 'CẦN DẤU CHẤM'], ['Mấy giờ rồi', 'CẦN DẤU HỎI'], ['Bây giờ là 7 giờ', 'CẦN DẤU CHẤM']], 'Câu có từ nghi vấn (gì, mấy, ai, có... không) → dấu hỏi.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm ÂM ĐẦU X hoặc ÂM ĐẦU S.', [['xinh', 'ÂM ĐẦU X'], ['sáng', 'ÂM ĐẦU S'], ['xanh', 'ÂM ĐẦU X'], ['sạch', 'ÂM ĐẦU S']], 'Ôn lại cặp âm đầu x/s dễ nhầm.', $d);
        $this->sortQ($s, 'Kéo mỗi cách viết vào nhóm ĐÚNG hoặc SAI.', [['chu đáo', 'ĐÚNG'], ['chú đáo', 'SAI'], ['cẩn thận', 'ĐÚNG'], ['cẫn thận', 'SAI']], '"Chu đáo" (ch – hỏi), "cẩn thận" (c – hỏi) — nhớ kĩ dấu.', $d);

        $this->fill($s, 'Từ chỉ sự rực rỡ, tươi sáng viết đúng là "rực ___": "rỡ".', [[0, 'rỡ']], '"Rực rỡ": nặng – ngã. Tả màu sắc tươi sáng, chói lọi.', $d);
        $this->fill($s, 'Cuối câu hỏi phải đặt dấu chấm ___.', [[0, 'hỏi']], 'Dấu chấm hỏi (?) là dấu hiệu nhận biết câu nghi vấn.', $d);
        $this->fill($s, 'Từ chỉ sự chăm chút cho đẹp viết đúng là "trau ___": "chuốt".', [[0, 'chuốt']], '"Trau chuốt": tr – ch. Nghĩa là chăm chút cho đẹp, hoàn hảo.', $d);
        $this->fill($s, 'Loại dấu ___ được dùng để ngăn cách các vế câu hoặc các bộ phận liệt kê.', [[0, 'phẩy']], 'Dấu phẩy (,) là dấu câu được dùng nhiều nhất trong câu.', $d);
        $this->fill($s, 'Từ "___ đáo" (cẩn thận): điền âm đầu đúng "ch" — "chu đáo".', [[0, 'ch']], '"Chu đáo": ch – đ (hỏi – ngã). Nghĩa là cẩn thận, đầy đủ.', $d);
    }

    /* ============ tv-van-mieu-ta-lop-7-1 (Tiếng Việt 7, de) ============ */
    private function seedTvVanMieuTaLop71(): void
    {
        $s = 'tv-van-mieu-ta-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Văn miêu tả có nhiệm vụ gì?', ['Kể lại sự việc', 'Tái hiện hình ảnh sự vật như đang hiện ra trước mắt', 'Giải thích khái niệm', 'Bàn luận vấn đề'], 1, 'Văn miêu tả giúp người đọc hình dung rõ sự vật như đang tận mắt trông thấy.', $d);
        $this->quiz($s, 'Từ nào gợi tả âm thanh của mưa?', ['lộp độp', 'lấp lánh', 'xanh mướt', 'mênh mông'], 0, '"Lộp độp" là từ láy tượng thanh gợi tả tiếng mưa rơi.', $d);
        $this->quiz($s, 'Từ nào gợi tả màu sắc của lá?', ['rì rào', 'xanh mướt', 'lộp độp', 'vi vu'], 1, '"Xanh mướt" là tính từ gợi tả màu xanh tươi tốt của lá cây.', $d);
        $this->quiz($s, 'Câu nào dưới đây là câu văn miêu tả đúng nghĩa?', ['"Em đi học lúc 6 giờ."', '"Dòng sông uốn lượn như dải lụa."', '"Lớp có 40 bạn."', '"Hôm nay là thứ ba."'], 1, 'Câu văn miêu tả vẽ nên hình ảnh cụ thể, sinh động bằng so sánh.', $d);
        $this->quiz($s, 'Khi tả cảnh vật, cần quan sát bằng gì?', ['Chỉ bằng mắt', 'Nhiều giác quan: mắt, tai, mũi...', 'Chỉ bằng tưởng tượng', 'Chỉ nghe kể lại'], 1, 'Càng nhiều giác quan, chi tiết càng phong phú, bài văn càng sinh động.', $d);

        $this->matching($s, 'Hãy nối mỗi từ với giác quan nó gợi tả.', [['lấp lánh', 'Thị giác (mắt)'], ['rì rào', 'Thính giác (tai)'], ['thơm ngát', 'Khứu giác (mũi)'], ['mát rượi', 'Xúc giác (da)']], 'Mỗi từ gợi tả gắn với một giác quan nhất định.', $d);
        $this->matching($s, 'Hãy nối mỗi biện pháp tu từ với ví dụ của nó.', [['So sánh', '"Trăng như cái đĩa."'], ['Nhân hoá', '"Chị gió đùa vui."'], ['Từ láy', '"Lấp lánh, rì rào."'], ['Điệp ngữ', '"Nhớ ai, nhớ ai..."']], 'Tu từ là "gia vị" làm văn miêu tả thêm sinh động.', $d);
        $this->matching($s, 'Hãy nối mỗi đối tượng với từ ngữ miêu tả phù hợp.', [['Dòng sông', 'uốn lượn, hiền hòa'], ['Cánh đồng', 'bát ngát, vàng óng'], ['Ngọn núi', 'sừng sững, chót vót'], ['Khu vườn', 'xum xuê, thơm ngát']], 'Mỗi đối tượng có vốn từ miêu tả đặc trưng riêng.', $d);
        $this->matching($s, 'Hãy nối mỗi câu văn với biện pháp tu từ được dùng.', [['"Sông như dải lụa."', 'So sánh'], ['"Gió thì thầm."', 'Nhân hoá'], ['"Nắng vàng rực."', 'Không có tu từ'], ['"Mây trắng như bông."', 'So sánh']], '"Nắng vàng rực" chỉ dùng tính từ, chưa có đối chiếu hay nhân hoá.', $d);
        $this->matching($s, 'Nối mỗi loại văn với đặc điểm của nó.', [['Văn miêu tả', 'Tái hiện hình ảnh'], ['Văn tự sự', 'Kể sự việc'], ['Văn biểu cảm', 'Bộc lộ cảm xúc'], ['Văn nghị luận', 'Bàn luận vấn đề']], 'Phân biệt các phương thức biểu đạt cơ bản.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi từ láy vào nhóm GỢI HÌNH ẢNH hoặc GỢI ÂM THANH.', [['lấp lánh', 'GỢI HÌNH ẢNH'], ['rì rào', 'GỢI ÂM THANH'], ['mênh mông', 'GỢI HÌNH ẢNH'], ['lộp độp', 'GỢI ÂM THANH'], ['chót vót', 'GỢI HÌNH ẢNH'], ['vi vu', 'GỢI ÂM THANH']], 'Gợi hình: dáng vẻ. Gợi thanh: âm thanh.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm VĂN MIÊU TẢ hoặc VĂN TỰ SỰ.', [['"Hoa nở rực rỡ."', 'VĂN MIÊU TẢ'], ['"Hôm qua em đi chơi."', 'VĂN TỰ SỰ'], ['"Trăng tròn vành vạnh."', 'VĂN MIÊU TẢ'], ['"Mẹ kể chuyện."', 'VĂN TỰ SỰ']], 'Miêu tả vẽ hình ảnh; tự sự kể diễn biến sự việc.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm TẢ NGƯỜI hoặc TẢ CẢNH.', [['hiền hậu', 'TẢ NGƯỜI'], ['bát ngát', 'TẢ CẢNH'], ['chăm chỉ', 'TẢ NGƯỜI'], ['mênh mông', 'TẢ CẢNH']], 'Từ ngữ miêu tả phải phù hợp với đối tượng được tả.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm QUAN SÁT BẰNG MẮT hoặc BẰNG TAI.', [['Hoa đỏ rực', 'QUAN SÁT BẰNG MẮT'], ['Chim hót líu lo', 'QUAN SÁT BẰNG TAI'], ['Mây trắng', 'QUAN SÁT BẰNG MẮT'], ['Suối róc rách', 'QUAN SÁT BẰNG TAI']], 'Mắt thu hình ảnh; tai thu âm thanh.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU VĂN HAY hoặc CÂU VĂN NHẠT.', [['"Sông như dải lụa mềm."', 'CÂU VĂN HAY'], ['"Sông dài."', 'CÂU VĂN NHẠT'], ['"Trăng tròn vành vạnh."', 'CÂU VĂN HAY'], ['"Trời tối."', 'CÂU VĂN NHẠT']], 'Câu văn hay có hình ảnh, so sánh, từ ngữ đắt giá.', $d);

        $this->fill($s, 'Nhờ văn miêu tả, người đọc hình dung rõ sự vật như đang tận ___ nhìn thấy.', [[0, 'mắt']], 'Tái hiện hình ảnh chân thực, sống động là nhiệm vụ của văn miêu tả.', $d);
        $this->fill($s, 'Từ "rì rào" gợi tả ___ thanh của gió, của lá.', [[0, 'âm']], '"Rì rào" là từ láy tượng thanh quen thuộc.', $d);
        $this->fill($s, 'Với hình ảnh "trăng như chiếc đĩa bạc", biện pháp tu từ được dùng là so ___.', [[0, 'sánh']], 'Có từ "như" đối chiếu trăng với chiếc đĩa → so sánh.', $d);
        $this->fill($s, 'Muốn tả cảnh hay, cần quan sát sự vật bằng nhiều giác ___.', [[0, 'quan']], 'Mắt, tai, mũi — càng nhiều giác quan càng nhiều chi tiết hay.', $d);
        $this->fill($s, 'Từ "lấp lánh" gợi tả ánh sáng đẹp, thuộc nhóm từ gợi ___ ảnh.', [[0, 'hình']], 'Từ láy gợi hình vẽ nên dáng vẻ, màu sắc của sự vật.', $d);
    }

    /* ============ tv-van-mieu-ta-lop-7-2 (Tiếng Việt 7, trung_binh) ============ */
    private function seedTvVanMieuTaLop72(): void
    {
        $s = 'tv-van-mieu-ta-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Bố cục của bài văn tả người thường gồm mấy phần?', ['2', '3', '4', '5'], 1, 'Ba phần: mở bài – thân bài – kết bài.', $d);
        $this->quiz($s, 'Nhiệm vụ chính của phần mở bài trong bài văn tả người là gì?', ['Liệt kê chi tiết', 'Giới thiệu người được tả', 'Kể toàn bộ cuộc đời', 'Nêu cảm nghĩ dài dòng'], 1, 'Mở bài giới thiệu nhân vật trong hoàn cảnh cụ thể.', $d);
        $this->quiz($s, 'Trình tự hợp lí nhất khi tả ngoại hình của một người là gì?', ['Tùy ý', 'Từ bao quát đến chi tiết', 'Chỉ tả một bộ phận', 'Từ chân lên đầu'], 1, 'Tả từ dáng vẻ chung rồi đi vào từng nét riêng nổi bật.', $d);
        $this->quiz($s, 'Trong các câu sau, câu nào tả ngoại hình con người?', ['"Bạn ấy rất tốt bụng."', '"Mái tóc bạn ấy đen nhánh."', '"Bạn ấy học giỏi."', '"Bạn ấy chăm chỉ."'], 1, '"Mái tóc đen nhánh" tả đặc điểm bên ngoài nhìn thấy được.', $d);
        $this->quiz($s, 'Khi tả người, ngoài ngoại hình còn nên tả gì?', ['Chỉ tả quần áo', 'Tính cách qua hành động, lời nói', 'Chỉ tả chiều cao', 'Không cần tả thêm'], 1, 'Tính cách là "hồn" của bài văn tả người, thể hiện qua việc làm cụ thể.', $d);

        $this->matching($s, 'Hãy nối mỗi phần của bài văn tả người với nội dung của nó.', [['Mở bài', 'Giới thiệu nhân vật'], ['Thân bài', 'Tả ngoại hình, tính cách'], ['Kết bài', 'Nêu tình cảm'], ['Nhan đề', 'Gợi mở']], 'Bố cục ba phần quen thuộc của bài văn tả người.', $d);
        $this->matching($s, 'Hãy nối mỗi bộ phận với từ ngữ miêu tả phù hợp.', [['Đôi mắt', 'long lanh'], ['Mái tóc', 'óng ả'], ['Nụ cười', 'rạng rỡ'], ['Làn da', 'trắng hồng']], 'Mỗi bộ phận có vốn từ miêu tả riêng, dùng đúng sẽ đắt giá.', $d);
        $this->matching($s, 'Hãy nối mỗi tính cách với biểu hiện của nó.', [['Chăm chỉ', 'dậy sớm học bài'], ['Thật thà', 'không gian dối'], ['Nhân hậu', 'giúp đỡ mọi người'], ['Vui vẻ', 'luôn cười tươi']], 'Tính cách phải được thể hiện qua hành động cụ thể.', $d);
        $this->matching($s, 'Hãy nối mỗi câu văn với người được tả.', [['"Tóc bà bạc phơ."', 'Bà'], ['"Mắt em tròn xoe."', 'Em bé'], ['"Dáng thầy cao gầy."', 'Thầy giáo'], ['"Tay mẹ chai sần."', 'Mẹ']], 'Chi tiết ngoại hình gắn với lứa tuổi, vai trò của nhân vật.', $d);
        $this->matching($s, 'Nối mỗi cách tả với đánh giá.', [['Có nét riêng', 'Hay'], ['Chung chung', 'Nhạt'], ['Kết hợp kể', 'Sinh động'], ['Liệt kê máy móc', 'Khô khan']], 'Bài văn tả người hay khi có nét riêng và cảm xúc chân thành.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm TẢ HOẠT ĐỘNG hoặc TẢ NGOẠI HÌNH.', [['"Bạn chạy nhanh như gió."', 'TẢ HOẠT ĐỘNG'], ['"Mái tóc bạn đen nhánh."', 'TẢ NGOẠI HÌNH'], ['"Bà kể chuyện hay."', 'TẢ HOẠT ĐỘNG'], ['"Đôi mắt bà hiền từ."', 'TẢ NGOẠI HÌNH']], 'Hoạt động: việc làm. Ngoại hình: dáng vẻ bên ngoài.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm TẢ NGOẠI HÌNH hoặc TẢ TÍNH CÁCH.', [['"Dáng người cao ráo."', 'TẢ NGOẠI HÌNH'], ['"Luôn giúp đỡ bạn bè."', 'TẢ TÍNH CÁCH'], ['"Nụ cười tươi tắn."', 'TẢ NGOẠI HÌNH'], ['"Tính tình hiền lành."', 'TẢ TÍNH CÁCH']], 'Ngoại hình nhìn thấy; tính cách thể hiện qua hành động.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi ý vào phần phù hợp của bài văn tả người.', [['Giới thiệu bà', 'MỞ BÀI'], ['Tả mái tóc bà', 'THÂN BÀI'], ['Kể kỉ niệm với bà', 'THÂN BÀI'], ['Yêu quý bà', 'KẾT BÀI']], 'Sắp xếp ý đúng phần giúp bài văn mạch lạc.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm TẢ DÁNG VẺ hoặc TẢ KHUÔN MẶT.', [['cao ráo', 'TẢ DÁNG VẺ'], ['tròn trịa', 'TẢ KHUÔN MẶT'], ['nhanh nhẹn', 'TẢ DÁNG VẺ'], ['phúc hậu', 'TẢ KHUÔN MẶT']], 'Dáng vẻ: toàn thân. Khuôn mặt: nét mặt cụ thể.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm NÊN TẢ hoặc KHÔNG NÊN TẢ.', [['Nét riêng nổi bật', 'NÊN TẢ'], ['Chi tiết nhạy cảm', 'KHÔNG NÊN TẢ'], ['Việc làm tiêu biểu', 'NÊN TẢ'], ['Điểm yếu để chê bai', 'KHÔNG NÊN TẢ']], 'Tả người với thái độ trân trọng, nhân ái.', $d);

        $this->fill($s, 'Một bài văn miêu tả hoàn chỉnh gồm 3 phần: mở bài, thân bài và ___ bài.', [[0, 'kết']], 'Ba phần: giới thiệu – tả chi tiết – nêu cảm nghĩ.', $d);
        $this->fill($s, 'Trình tự hợp lí khi tả người là đi từ bao ___ đến chi tiết.', [[0, 'quát']], 'Dáng vẻ chung trước, nét riêng nổi bật sau.', $d);
        $this->fill($s, 'Câu văn "Đôi mắt bồ câu long lanh" thuộc phần tả ngoại ___.', [[0, 'hình']], 'Ngoại hình: những đặc điểm bên ngoài nhìn thấy được.', $d);
        $this->fill($s, 'Ở phần kết ___, người viết nêu cảm nghĩ của mình về người được tả.', [[0, 'bài']], 'Kết bài bộc lộ tình cảm: yêu thương, kính trọng.', $d);
        $this->fill($s, 'Tính cách nhân vật được thể hiện qua hành động, lời nói cụ ___.', [[0, 'thể']], 'Việc làm cụ thể là "bằng chứng" cho tính cách.', $d);
    }

    /* ============ tv-van-mieu-ta-lop-8-1 (Tiếng Việt 8, trung_binh) ============ */
    private function seedTvVanMieuTaLop81(): void
    {
        $s = 'tv-van-mieu-ta-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Yếu tố nào được coi là quan trọng nhất khi tả cảnh?', ['Liệt kê nhiều chi tiết', 'Quan sát tinh tế và cảm xúc chân thành', 'Viết thật dài', 'Dùng từ khó'], 1, 'Quan sát tinh tế cho chi tiết hay; cảm xúc chân thành cho bài văn có hồn.', $d);
        $this->quiz($s, 'Câu "Sương giăng mờ như tấm voan mỏng." dùng biện pháp tu từ gì?', ['Nhân hoá', 'So sánh', 'Ẩn dụ', 'Hoán dụ'], 1, 'Có từ "như" đối chiếu sương với tấm voan → so sánh.', $d);
        $this->quiz($s, 'Câu "Hàng cây rì rào hát ca." dùng biện pháp tu từ gì?', ['So sánh', 'Nhân hoá', 'Ẩn dụ', 'Liệt kê'], 1, 'Gán hành động "hát ca" của người cho hàng cây → nhân hoá.', $d);
        $this->quiz($s, 'Khi tả cảnh, người viết thường gắn cảnh vật với yếu tố nào?', ['Thời gian, không gian cụ thể', 'Số liệu thống kê', 'Công thức toán học', 'Danh sách dài'], 0, 'Cảnh vật luôn gắn với thời điểm (sáng, chiều) và không gian (quê hương, sân trường).', $d);
        $this->quiz($s, 'Câu văn nào tả cảnh buổi sáng hay nhất?', ['"Sáng nay trời sáng."', '"Sương sớm giăng mờ, chim hót líu lo chào ngày mới."', '"Buổi sáng có sương."', '"Trời sáng rồi."'], 1, 'Câu có nhiều chi tiết gợi hình, gợi thanh cụ thể sinh động hơn hẳn.', $d);

        $this->matching($s, 'Hãy nối mỗi cảnh với nét đặc trưng theo thời điểm.', [['Bình minh', 'Sương giăng, nắng nhẹ'], ['Hoàng hôn', 'Nắng nhạt, mây hồng'], ['Đêm trăng', 'Trăng sáng, sao lấp lánh'], ['Trưa hè', 'Nắng gắt, ve kêu']], 'Mỗi thời điểm có "chân dung" riêng cần nắm vững khi tả.', $d);
        $this->matching($s, 'Nối mỗi biện pháp tu từ với tác dụng của nó khi tả cảnh.', [['So sánh', 'Hình ảnh cụ thể'], ['Nhân hoá', 'Cảnh vật có hồn'], ['Từ láy', 'Gợi hình, gợi thanh'], ['Điệp ngữ', 'Tạo nhịp điệu']], 'Tu từ là "linh hồn" của văn tả cảnh hay.', $d);
        $this->matching($s, 'Hãy nối mỗi câu văn với biện pháp tu từ được dùng.', [['"Sương như voan mỏng."', 'So sánh'], ['"Cây hát ca."', 'Nhân hoá'], ['"Nắng vàng rực rỡ."', 'Không có tu từ'], ['"Gió mơn man."', 'Nhân hoá']], '"Mơn man" là cử chỉ âu yếm của người gán cho gió → nhân hoá.', $d);
        $this->matching($s, 'Hãy nối mỗi đối tượng tả cảnh với chi tiết miêu tả.', [['Dòng sông', 'uốn lượn, hiền hòa'], ['Cánh đồng', 'vàng óng, bát ngát'], ['Khu vườn', 'xum xuê, thơm ngát'], ['Con đường', 'quanh co, rợp bóng']], 'Mỗi đối tượng có hệ chi tiết đặc trưng riêng.', $d);
        $this->matching($s, 'Nối mỗi giác quan với vai trò khi tả cảnh.', [['Mắt', 'Thu hình ảnh'], ['Tai', 'Thu âm thanh'], ['Mũi', 'Thu hương thơm'], ['Cảm xúc', 'Làm cảnh có hồn']], 'Bốn "kênh" thu thập chất liệu cho bài văn tả cảnh.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi chi tiết vào nhóm TẢ MÙA XUÂN hoặc TẢ MÙA ĐÔNG.', [['Hoa đào nở', 'TẢ MÙA XUÂN'], ['Gió bấc lạnh', 'TẢ MÙA ĐÔNG'], ['Én bay về', 'TẢ MÙA XUÂN'], ['Sương muối', 'TẢ MÙA ĐÔNG'], ['Mưa phùn', 'TẢ MÙA XUÂN'], ['Cây trơ cành', 'TẢ MÙA ĐÔNG']], 'Xuân: đào, én, mưa phùn. Đông: gió bấc, sương muối.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÓ SO SÁNH hoặc KHÔNG CÓ SO SÁNH.', [['"Mây như bông."', 'CÓ SO SÁNH'], ['"Trời nhiều mây."', 'KHÔNG CÓ SO SÁNH'], ['"Sông như lụa."', 'CÓ SO SÁNH'], ['"Chiều gió to."', 'KHÔNG CÓ SO SÁNH']], 'Dấu hiệu so sánh: từ "như" và hai sự vật đối chiếu.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÓ NHÂN HOÁ hoặc KHÔNG CÓ NHÂN HOÁ.', [['"Gió đùa vui."', 'CÓ NHÂN HOÁ'], ['"Gió thổi mạnh."', 'KHÔNG CÓ NHÂN HOÁ'], ['"Trăng mỉm cười."', 'CÓ NHÂN HOÁ'], ['"Trăng lên cao."', 'KHÔNG CÓ NHÂN HOÁ']], 'Nhân hoá gán đặc điểm người; câu kể chỉ tả đúng bản chất.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi chi tiết vào nhóm TẢ BUỔI SÁNG hoặc TẢ BUỔI TỐI.', [['Sương giăng', 'TẢ BUỔI SÁNG'], ['Sao lấp lánh', 'TẢ BUỔI TỐI'], ['Chim hót', 'TẢ BUỔI SÁNG'], ['Trăng lên', 'TẢ BUỔI TỐI']], 'Sáng: sương, chim hót. Tối: trăng, sao.', $d);
        $this->sortQ($s, 'Kéo mỗi cách tả vào nhóm TẢ TĨNH hoặc TẢ ĐỘNG.', [['"Hồ phẳng lặng."', 'TẢ TĨNH'], ['"Sóng vỗ rì rào."', 'TẢ ĐỘNG'], ['"Núi sừng sững."', 'TẢ TĨNH'], ['"Lá reo vui."', 'TẢ ĐỘNG']], 'Kết hợp tả tĩnh và tả động, cảnh vật sẽ sống động.', $d);

        $this->fill($s, 'Phép nhân hoá gán cho sự vật những đặc điểm vốn có của con ___.', [[0, 'người']], 'Nhân hoá: "chị gió, ông trăng, hàng cây hát ca".', $d);
        $this->fill($s, 'Bức tranh buổi sáng quê hương thường có sương sớm, mặt ___ và tiếng chim.', [[0, 'trời']], '"Mặt trời" lên — chi tiết quen thuộc của buổi sáng.', $d);
        $this->fill($s, 'Từ "bát ngát" thường dùng để tả cánh ___ rộng lớn.', [[0, 'đồng']], '"Cánh đồng bát ngát" — cụm từ quen thuộc tả không gian rộng.', $d);
        $this->fill($s, 'Câu "Dòng sông như dải lụa." dùng biện pháp so ___.', [[0, 'sánh']], 'Có từ "như" đối chiếu sông với dải lụa → so sánh.', $d);
        $this->fill($s, 'Tả cảnh cần kết hợp quan sát tinh tế và cảm ___ chân thành.', [[0, 'xúc']], 'Cảm xúc là "linh hồn" làm bài văn tả cảnh có chiều sâu.', $d);
    }

    /* ============ tv-van-mieu-ta-lop-8-2 (Tiếng Việt 8, trung_binh) ============ */
    private function seedTvVanMieuTaLop82(): void
    {
        $s = 'tv-van-mieu-ta-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Một đoạn văn miêu tả được đánh giá là hay cần có những gì?', ['Thật dài', 'Hình ảnh sinh động và cảm xúc chân thành', 'Nhiều số liệu', 'Từ ngữ khó hiểu'], 1, 'Hình ảnh sinh động cho "xác", cảm xúc chân thành cho "hồn".', $d);
        $this->quiz($s, 'Câu "Ve kêu râm ran báo hiệu hè về." gợi cho người đọc điều gì?', ['Sự ồn ào khó chịu', 'Không khí mùa hè rộn rã', 'Nỗi buồn', 'Sự yên tĩnh'], 1, 'Tiếng ve là "tín hiệu" quen thuộc báo hiệu mùa hè trong văn học.', $d);
        $this->quiz($s, 'Câu nào dưới đây bộc lộ trực tiếp cảm xúc của người viết trong đoạn văn tả cảnh?', ['"Trời nắng to."', '"Em yêu biết mấy quê hương!"', '"Cây cao 5m."', '"Sông dài 10km."'], 1, 'Câu cảm thán bộc lộ trực tiếp tình cảm của người viết.', $d);
        $this->quiz($s, 'Điều gì cần tránh khi viết đoạn văn miêu tả?', ['Dùng từ ngữ gợi tả', 'Lặp đi lặp lại một từ', 'Bộc lộ cảm xúc', 'Dùng biện pháp tu từ'], 1, 'Lặp từ làm đoạn văn đơn điệu, nghèo nàn — cần dùng từ đồng nghĩa thay thế.', $d);
        $this->quiz($s, 'Câu kết đoạn văn tả cảnh nên làm gì?', ['Kể chuyện khác', 'Bày tỏ tình cảm, suy nghĩ', 'Liệt kê tiếp', 'Dừng đột ngột'], 1, 'Câu kết đọng lại cảm xúc, ấn tượng sâu đậm về cảnh vật.', $d);

        $this->matching($s, 'Hãy nối mỗi câu văn với cảm nhận nó gợi ra.', [['"Nắng vàng rực rỡ."', 'Rộn ràng, tươi vui'], ['"Mưa dầm dề."', 'Buồn man mác'], ['"Trăng sáng vằng vặc."', 'Thơ mộng, yên bình'], ['"Gió bấc lạnh buốt."', 'Buồn, cô đơn']], 'Mỗi chi tiết thiên nhiên mang một "mã cảm xúc" riêng.', $d);
        $this->matching($s, 'Hãy nối mỗi chủ đề với câu mở đoạn phù hợp.', [['Mùa hè', '"Ve kêu râm ran báo hiệu hè về."'], ['Mùa thu', '"Lá vàng rơi đầy sân."'], ['Quê hương', '"Quê hương em rất đẹp."'], ['Sân trường', '"Sân trường em rợp bóng cây."']], 'Câu mở đoạn giới thiệu chủ đề một cách tự nhiên, gợi hình.', $d);
        $this->matching($s, 'Hãy nối mỗi từ với sắc thái cảm xúc của nó.', [['rộn ràng', 'Vui tươi'], ['man mác', 'Buồn nhẹ'], ['hân hoan', 'Vui sướng'], ['bâng khuâng', 'Băn khoăn, nhớ nhung']], 'Từ ngữ cảm xúc là "nhiệt kế" của đoạn văn miêu tả.', $d);
        $this->matching($s, 'Hãy nối mỗi lỗi thường gặp với cách khắc phục.', [['Lặp từ', 'Dùng từ đồng nghĩa'], ['Thiếu cảm xúc', 'Bộc lộ tình cảm'], ['Chung chung', 'Chọn chi tiết tiêu biểu'], ['Thiếu mạch lạc', 'Sắp xếp theo trình tự']], 'Bốn lỗi kinh điển của đoạn văn miêu tả và cách chữa.', $d);
        $this->matching($s, 'Nối mỗi biện pháp với tác dụng trong đoạn văn.', [['So sánh', 'Hình ảnh cụ thể'], ['Nhân hoá', 'Sinh động, có hồn'], ['Từ láy', 'Gợi hình, gợi thanh'], ['Câu cảm thán', 'Bộc lộ cảm xúc']], 'Phối hợp nhiều biện pháp, đoạn văn sẽ giàu sức biểu cảm.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÂU VĂN HAY hoặc CHƯA HAY.', [['"Sông uốn lượn như dải lụa."', 'CÂU VĂN HAY'], ['"Sông dài."', 'CHƯA HAY'], ['"Trăng tròn vành vạnh."', 'CÂU VĂN HAY'], ['"Trời tối."', 'CHƯA HAY']], 'Câu văn hay: hình ảnh, so sánh, từ đắt. Chưa hay: chung chung.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm GỢI CẢM XÚC VUI hoặc BUỒN.', [['rộn ràng', 'GỢI CẢM XÚC VUI'], ['man mác', 'GỢI CẢM XÚC BUỒN'], ['tưng bừng', 'GỢI CẢM XÚC VUI'], ['hắt hiu', 'GỢI CẢM XÚC BUỒN']], 'Từ ngữ cảm xúc quyết định "màu sắc" tình cảm của đoạn văn.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi ý vào nhóm NÊN CÓ hoặc KHÔNG NÊN CÓ trong đoạn văn tả cảnh.', [['Chi tiết tiêu biểu', 'NÊN CÓ'], ['Lặp từ nhiều lần', 'KHÔNG NÊN CÓ'], ['Cảm xúc chân thành', 'NÊN CÓ'], ['Số liệu khô khan', 'KHÔNG NÊN CÓ']], 'Đoạn văn tả cảnh cần: chi tiết hay + cảm xúc, tránh lặp từ, số liệu.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm GẦN NGHĨA VỚI "ĐẸP" hoặc KHÔNG GẦN NGHĨA.', [['xinh đẹp', 'GẦN NGHĨA VỚI "ĐẸP"'], ['xấu xí', 'KHÔNG GẦN NGHĨA'], ['lộng lẫy', 'GẦN NGHĨA VỚI "ĐẸP"'], ['xập xệ', 'KHÔNG GẦN NGHĨA']], 'Vốn từ đồng nghĩa giúp tránh lặp từ "đẹp" nhàm chán.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU MỞ ĐOẠN TỐT hoặc CHƯA TỐT.', [['"Mùa thu về, lá vàng rơi."', 'CÂU MỞ ĐOẠN TỐT'], ['"Hôm nay em viết đoạn văn."', 'CHƯA TỐT'], ['"Quê hương em đẹp lắm."', 'CÂU MỞ ĐOẠN TỐT'], ['"Em sẽ tả cảnh."', 'CHƯA TỐT']], 'Câu mở đoạn tốt giới thiệu chủ đề tự nhiên, gợi hình.', $d);

        $this->fill($s, 'Hình ảnh sinh động cùng cảm ___ chân thành làm nên đoạn văn miêu tả hay.', [[0, 'xúc']], 'Hình ảnh cho "xác", cảm xúc cho "hồn" của đoạn văn.', $d);
        $this->fill($s, 'Một lỗi cần tránh khi viết là ___ lại cùng một từ quá nhiều lần.', [[0, 'lặp']], 'Lặp từ làm đoạn văn đơn điệu — hãy dùng từ đồng nghĩa thay thế.', $d);
        $this->fill($s, 'Từ "rộn ràng" gợi cảm xúc vui ___ của ngày hội.', [[0, 'tươi']], '"Vui tươi" là cụm từ quen thuộc chỉ niềm vui rạng rỡ.', $d);
        $this->fill($s, 'Ở câu kết đoạn văn tả cảnh, em nên bày tỏ tình cảm và suy ___ của mình.', [[0, 'nghĩ']], 'Câu kết đọng lại ấn tượng, tình cảm sâu đậm.', $d);
        $this->fill($s, 'Từ đồng nghĩa với "đẹp" như "xinh đẹp" giúp tránh ___ từ nhàm chán.', [[0, 'lặp']], 'Vốn từ đồng nghĩa phong phú là "vũ khí" chống lặp từ.', $d);
    }

    /* ============ tieng-viet-thpt-10-pcnc-van-chuong-1 (Tiếng Việt 10, trung_binh) ============ */
    private function seedTiengVietThpt10VanChuong1(): void
    {
        $s = 'tieng-viet-thpt-10-pcnc-van-chuong-1'; $d = 'trung_binh';
        $this->quiz($s, 'Phong cách ngôn ngữ văn chương KHÔNG có đặc trưng nào sau đây?', ['Tính hình tượng', 'Tính truyền cảm', 'Tính cá thể hóa', 'Tính khuôn mẫu, công thức'], 3, 'Khuôn mẫu, công thức là đặc trưng của phong cách hành chính, không phải văn chương.', $d);
        $this->quiz($s, 'Tính hình tượng của ngôn ngữ văn chương thể hiện rõ nhất qua yếu tố nào?', ['Số liệu thống kê', 'Hình ảnh, biện pháp tu từ', 'Thuật ngữ khoa học', 'Mẫu đơn hành chính'], 1, 'Hình ảnh và tu từ làm ngôn ngữ văn chương gợi hình, gợi cảm, đa nghĩa.', $d);
        $this->quiz($s, 'Tính truyền cảm trong thơ Xuân Quỳnh thể hiện qua điều gì?', ['Vần điệu khô khan', 'Cảm xúc chân thành, mãnh liệt', 'Từ ngữ khó hiểu', 'Cấu trúc phức tạp'], 1, 'Cảm xúc chân thành của tác giả lay động, truyền sang người đọc — đó là truyền cảm.', $d);
        $this->quiz($s, 'Tính cá thể hóa trong phong cách ngôn ngữ văn chương nghĩa là gì?', ['Ai viết cũng giống nhau', 'Mỗi tác giả có dấu ấn ngôn ngữ riêng', 'Chỉ một người được viết', 'Viết theo mẫu chung'], 1, 'Nguyễn Du, Hồ Chí Minh, Xuân Quỳnh — mỗi người một giọng văn không lẫn vào đâu.', $d);
        $this->quiz($s, 'Chức năng chính của phong cách ngôn ngữ văn chương là gì?', ['Thông báo nhanh', 'Biểu hiện và khơi gợi cảm xúc, thẩm mĩ', 'Ra lệnh', 'Giao dịch hành chính'], 1, 'Văn chương trước hết phục vụ nhu cầu thẩm mĩ, tình cảm của con người.', $d);

        $this->matching($s, 'Hãy nối mỗi đặc trưng với biểu hiện của nó.', [['Tính hình tượng', 'Hình ảnh, tu từ giàu sức gợi'], ['Tính truyền cảm', 'Cảm xúc lay động lòng người'], ['Tính cá thể hóa', 'Dấu ấn riêng của tác giả'], ['Tính đa nghĩa', 'Một lời nhiều nghĩa']], 'Bốn đặc trưng làm nên "căn cước" của phong cách ngôn ngữ văn chương.', $d);
        $this->matching($s, 'Hãy nối mỗi ví dụ với đặc trưng thể hiện.', [['"Sóng – em" (Xuân Quỳnh)', 'Tính hình tượng'], ['"Thương ai..." (ca dao)', 'Tính truyền cảm'], ['Giọng thơ Hàn Mặc Tử', 'Tính cá thể hóa'], ['"Thuyền – bến"', 'Tính đa nghĩa']], 'Học đặc trưng qua ví dụ cụ thể sẽ hiểu sâu, nhớ lâu.', $d);
        $this->matching($s, 'Hãy nối mỗi phong cách với chức năng chính.', [['Văn chương', 'Thẩm mĩ, biểu cảm'], ['Báo chí', 'Thông tin thời sự'], ['Khoa học', 'Nhận thức, lí trí'], ['Hành chính', 'Giao dịch, quản lí']], 'Mỗi phong cách ngôn ngữ sinh ra để phục vụ một nhu cầu giao tiếp riêng.', $d);
        $this->matching($s, 'Nối mỗi thể loại với phong cách ngôn ngữ của nó.', [['Truyện ngắn', 'Văn chương'], ['Bản tin', 'Báo chí'], ['Luận văn', 'Khoa học'], ['Đơn xin phép', 'Hành chính']], 'Thể loại văn bản quyết định phong cách ngôn ngữ được sử dụng.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với phong cách tương ứng.', [['Giàu hình ảnh, cảm xúc', 'Văn chương'], ['Ngắn gọn, khách quan', 'Báo chí'], ['Chính xác, logic', 'Khoa học'], ['Khuôn mẫu, trang trọng', 'Hành chính']], 'Dấu hiệu ngôn ngữ giúp nhận diện phong cách nhanh chóng.', $d);

        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm VĂN CHƯƠNG hoặc KHOA HỌC.', [['Giàu hình ảnh', 'VĂN CHƯƠNG'], ['Chính xác, khách quan', 'KHOA HỌC'], ['Truyền cảm', 'VĂN CHƯƠNG'], ['Thuật ngữ chuyên ngành', 'KHOA HỌC'], ['Đa nghĩa', 'VĂN CHƯƠNG'], ['Đơn nghĩa', 'KHOA HỌC']], 'Văn chương: cảm xúc, hình ảnh. Khoa học: lí trí, chính xác.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu văn vào nhóm CÓ TÍNH HÌNH TƯỢNG hoặc KHÔNG.', [['"Sóng vỗ rì rào."', 'CÓ TÍNH HÌNH TƯỢNG'], ['"Nhiệt độ là 30 độ C."', 'KHÔNG'], ['"Trăng tròn vành vạnh."', 'CÓ TÍNH HÌNH TƯỢNG'], ['"Diện tích là 50m2."', 'KHÔNG']], 'Tính hình tượng: ngôn ngữ gợi hình ảnh cụ thể, sinh động.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm THUỘC VĂN CHƯƠNG hoặc THUỘC BÁO CHÍ.', [['Cảm xúc dạt dào', 'THUỘC VĂN CHƯƠNG'], ['Thông tin thời sự', 'THUỘC BÁO CHÍ'], ['Dấu ấn cá nhân', 'THUỘC VĂN CHƯƠNG'], ['Tính khách quan', 'THUỘC BÁO CHÍ']], 'Văn chương cá thể, cảm xúc; báo chí khách quan, thời sự.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi biểu hiện vào nhóm TÍNH TRUYỀN CẢM hoặc KHÔNG.', [['Lay động lòng người', 'TÍNH TRUYỀN CẢM'], ['Khô khan, vô cảm', 'KHÔNG'], ['Gợi cảm xúc mạnh', 'TÍNH TRUYỀN CẢM'], ['Chỉ thông báo sự việc', 'KHÔNG']], 'Truyền cảm là khả năng "lây" cảm xúc từ tác giả sang người đọc.', $d);
        $this->sortQ($s, 'Kéo mỗi văn bản vào nhóm THUỘC PHONG CÁCH VĂN CHƯƠNG hoặc KHÔNG.', [['Bài thơ "Sóng"', 'THUỘC PHONG CÁCH VĂN CHƯƠNG'], ['Bản tin thời sự', 'KHÔNG'], ['Truyện ngắn "Lão Hạc"', 'THUỘC PHONG CÁCH VĂN CHƯƠNG'], ['Hợp đồng mua bán', 'KHÔNG']], 'Thơ, truyện thuộc văn chương; tin tức, hợp đồng thuộc phong cách khác.', $d);

        $this->fill($s, 'Ba đặc trưng của phong cách ngôn ngữ văn chương: tính hình tượng, tính truyền cảm và tính cá thể ___.', [[0, 'hóa']], 'Ba đặc trưng "vàng" làm nên bản sắc của ngôn ngữ văn chương.', $d);
        $this->fill($s, 'Khả năng gợi lên hình ảnh cụ thể, sinh động của ngôn ngữ văn chương gọi là tính hình ___.', [[0, 'tượng']], 'Hình tượng là "linh hồn" của văn chương: nói một gợi mười.', $d);
        $this->fill($s, 'Khả năng lay động cảm xúc người đọc gọi là tính truyền ___.', [[0, 'cảm']], 'Văn chương hay là văn chương "lây" được cảm xúc sang người đọc.', $d);
        $this->fill($s, 'Dấu ấn ngôn ngữ riêng không lẫn của mỗi tác giả gọi là tính cá thể ___.', [[0, 'hóa']], 'Đọc một câu đã biết là Nguyễn Du hay Hồ Chí Minh — đó là cá thể hóa.', $d);
        $this->fill($s, 'Chức năng chính của phong cách văn chương là thẩm mĩ và biểu ___.', [[0, 'cảm']], 'Văn chương phục vụ nhu cầu đẹp và nhu cầu bộc lộ tình cảm của con người.', $d);
    }

    /* ============ tieng-viet-thpt-10-pcnc-van-chuong-2 (Tiếng Việt 10, trung_binh) ============ */
    private function seedTiengVietThpt10VanChuong2(): void
    {
        $s = 'tieng-viet-thpt-10-pcnc-van-chuong-2'; $d = 'trung_binh';
        $this->quiz($s, 'Dấu hiệu nào KHÔNG giúp nhận diện phong cách ngôn ngữ văn chương?', ['Giàu hình ảnh', 'Số liệu khô khan', 'Cảm xúc dạt dào', 'Biện pháp tu từ'], 1, 'Số liệu khô khan là dấu hiệu của phong cách khoa học, báo chí.', $d);
        $this->quiz($s, 'Điều gì khiến văn bản văn chương ưa chuộng sử dụng nhiều biện pháp tu từ?', ['Để khoe kiến thức', 'Để tăng tính hình tượng và truyền cảm', 'Để làm khó người đọc', 'Để viết dài hơn'], 1, 'Tu từ làm lời văn đẹp, gợi hình, lay động cảm xúc — đúng bản chất văn chương.', $d);
        $this->quiz($s, 'Ngôn ngữ văn chương khác ngôn ngữ sinh hoạt hằng ngày ở điểm nào?', ['Không có cảm xúc', 'Được chọn lọc, tinh luyện, giàu hình ảnh', 'Khó hiểu hơn hẳn', 'Chỉ dùng từ Hán Việt'], 1, 'Văn chương lấy ngôn ngữ toàn dân rồi gọt giũa, tinh luyện thành nghệ thuật.', $d);
        $this->quiz($s, 'Đoạn văn "Sáng nay, giá vàng tăng mạnh..." thuộc phong cách nào?', ['Văn chương', 'Báo chí', 'Khoa học', 'Hành chính'], 1, 'Thông tin thời sự, ngắn gọn, khách quan → phong cách báo chí.', $d);
        $this->quiz($s, 'Câu "Ôi con sóng nhớ bờ!" thuộc phong cách nào?', ['Báo chí', 'Văn chương', 'Hành chính', 'Khoa học'], 1, 'Câu cảm thán giàu cảm xúc, hình ảnh → phong cách văn chương.', $d);

        $this->matching($s, 'Nối mỗi đoạn trích với phong cách ngôn ngữ của nó.', [['"Trăng sáng vằng vặc..."', 'Văn chương'], ['"Theo tin mới nhất..."', 'Báo chí'], ['"Định lí cho rằng..."', 'Khoa học'], ['"Căn cứ quyết định..."', 'Hành chính']], 'Nhận diện phong cách qua dấu hiệu ngôn ngữ đặc trưng.', $d);
        $this->matching($s, 'Hãy nối mỗi dấu hiệu với phong cách tương ứng.', [['Từ ngữ gợi cảm', 'Văn chương'], ['Số liệu, thời sự', 'Báo chí'], ['Thuật ngữ, logic', 'Khoa học'], ['Mẫu câu khuôn sáo', 'Hành chính']], 'Mỗi phong cách có "bộ dấu hiệu" riêng để nhận biết.', $d);
        $this->matching($s, 'Hãy nối mỗi biện pháp tu từ với tác dụng trong văn chương.', [['Ẩn dụ', 'Gợi liên tưởng sâu xa'], ['Nhân hoá', 'Làm sự vật có hồn'], ['Điệp ngữ', 'Tạo nhịp điệu, nhấn mạnh'], ['Nói quá', 'Tăng sức biểu cảm']], 'Tu từ là "vũ khí" chủ lực của phong cách văn chương.', $d);
        $this->matching($s, 'Hãy nối mỗi thể loại văn chương với đặc điểm ngôn ngữ.', [['Thơ', 'Ngắn gọn, giàu nhạc tính'], ['Truyện ngắn', 'Kể chuyện, miêu tả'], ['Kí', 'Ghi chép chân thực'], ['Tùy bút', 'Phóng khoáng, trữ tình']], 'Mỗi thể loại văn chương có yêu cầu ngôn ngữ riêng.', $d);
        $this->matching($s, 'Nối mỗi câu với phong cách của nó.', [['"Em yêu anh nhiều lắm!"', 'Văn chương'], ['"Giá xăng tăng 500 đồng."', 'Báo chí'], ['"Nước sôi ở 100 độ C."', 'Khoa học'], ['"Kính gửi Ban giám hiệu."', 'Hành chính']], 'Luyện nhận diện nhanh qua các câu ví dụ gần gũi.', $d);

        $this->sortQ($s, 'Kéo mỗi câu văn vào nhóm VĂN CHƯƠNG hoặc KHOA HỌC.', [['"Sóng nhớ bờ da diết."', 'VĂN CHƯƠNG'], ['"Sóng biển cao 2 mét."', 'KHOA HỌC'], ['"Trăng buồn lặng lẽ."', 'VĂN CHƯƠNG'], ['"Mặt trăng cách trái đất 384.000km."', 'KHOA HỌC']], 'Cùng nói về sóng, trăng nhưng cách nói quyết định phong cách.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi yếu tố vào nhóm GIÚP NHẬN DIỆN VĂN CHƯƠNG hoặc KHÔNG.', [['Hình ảnh ẩn dụ', 'GIÚP NHẬN DIỆN VĂN CHƯƠNG'], ['Số liệu thống kê', 'KHÔNG'], ['Cảm xúc dạt dào', 'GIÚP NHẬN DIỆN VĂN CHƯƠNG'], ['Mẫu đơn sẵn', 'KHÔNG']], 'Dấu hiệu văn chương: hình ảnh, cảm xúc, tu từ, cá tính.', $d);
        $this->sortQ($s, 'Kéo mỗi văn bản vào nhóm THUỘC VĂN CHƯƠNG hoặc THUỘC BÁO CHÍ.', [['Truyện cổ tích', 'THUỘC VĂN CHƯƠNG'], ['Phóng sự điều tra', 'THUỘC BÁO CHÍ'], ['Bài thơ', 'THUỘC VĂN CHƯƠNG'], ['Bản tin', 'THUỘC BÁO CHÍ']], 'Thể loại quyết định phong cách: truyện/thơ → văn chương; tin/phóng sự → báo chí.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm VĂN CHƯƠNG hoặc HÀNH CHÍNH.', [['Sáng tạo, cá tính', 'VĂN CHƯƠNG'], ['Khuôn mẫu, trang trọng', 'HÀNH CHÍNH'], ['Đa nghĩa, gợi cảm', 'VĂN CHƯƠNG'], ['Đơn nghĩa, rõ ràng', 'HÀNH CHÍNH']], 'Văn chương tự do sáng tạo; hành chính khuôn mẫu, chuẩn xác.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm NGÔN NGỮ TOÀN DÂN hoặc NGÔN NGỮ NGHỆ THUẬT.', [['"Ăn cơm chưa?"', 'NGÔN NGỮ TOÀN DÂN'], ['"Cơm ngon như mẹ nấu."', 'NGÔN NGỮ NGHỆ THUẬT'], ['"Đi đâu đấy?"', 'NGÔN NGỮ TOÀN DÂN'], ['"Chân trời như dải lụa."', 'NGÔN NGỮ NGHỆ THUẬT']], 'Văn chương lấy ngôn ngữ toàn dân rồi tinh luyện thành nghệ thuật.', $d);

        $this->fill($s, 'Văn bản giàu hình ảnh, cảm xúc, dấu ấn cá nhân thuộc phong cách ngôn ngữ văn ___.', [[0, 'chương']], 'Ba dấu hiệu vàng nhận diện phong cách văn chương.', $d);
        $this->fill($s, 'Ngôn ngữ văn chương là ngôn ngữ toàn dân được chọn lọc, tinh ___.', [[0, 'luyện']], 'Nhà văn "gọt giũa" lời ăn tiếng nói hằng ngày thành nghệ thuật.', $d);
        $this->fill($s, 'Biện pháp tu từ giúp tăng tính hình tượng và tính truyền ___ cho lời văn.', [[0, 'cảm']], 'Tu từ là "bí quyết" làm nên sức hấp dẫn của văn chương.', $d);
        $this->fill($s, 'Văn bản đưa tin nhanh, ngắn gọn, khách quan thuộc phong cách ngôn ngữ báo ___.', [[0, 'chí']], 'Đối lập với văn chương: báo chí cần nhanh, gọn, khách quan.', $d);
        $this->fill($s, 'Dấu ấn riêng của tác giả trong cách dùng từ, đặt câu gọi là tính cá thể ___.', [[0, 'hóa']], 'Cá thể hóa là đặc trưng phân biệt văn chương với các phong cách khác.', $d);
    }

    /* ============ tieng-viet-thpt-10-pcnc-bao-chi-1 (Tiếng Việt 10, trung_binh) ============ */
    private function seedTiengVietThpt10BaoChi1(): void
    {
        $s = 'tieng-viet-thpt-10-pcnc-bao-chi-1'; $d = 'trung_binh';
        $this->quiz($s, 'Phong cách ngôn ngữ báo chí chủ yếu đảm nhận chức năng gì?', ['Biểu cảm thẩm mĩ', 'Thông tin thời sự', 'Giao dịch hành chính', 'Nghiên cứu khoa học'], 1, 'Báo chí ra đời để đưa tin nhanh về các sự kiện thời sự trong đời sống.', $d);
        $this->quiz($s, 'Trong các đặc trưng sau, đặc trưng nào không phải của phong cách ngôn ngữ báo chí?', ['Tính thời sự', 'Tính ngắn gọn', 'Tính đa nghĩa, mơ hồ', 'Tính khách quan'], 2, 'Báo chí cần rõ ràng, chính xác; đa nghĩa mơ hồ là của văn chương.', $d);
        $this->quiz($s, 'Về cách diễn đạt, ngôn ngữ báo chí đặt ra yêu cầu gì?', ['Cầu kì, hoa mĩ', 'Rõ ràng, chính xác, ngắn gọn', 'Mơ hồ, đa nghĩa', 'Dài dòng, lan man'], 1, 'Tin tức cần đến nhanh, hiểu ngay — nên ngôn ngữ phải gọn, rõ, chuẩn.', $d);
        $this->quiz($s, 'Hiểu thế nào cho đúng về tính thời sự trong báo chí?', ['Viết về quá khứ xa xưa', 'Kịp thời phản ánh sự kiện đang diễn ra', 'Viết về tương lai xa', 'Không cần thời gian'], 1, 'Thời sự = tính kịp thời: tin càng nóng, càng mới càng có giá trị.', $d);
        $this->quiz($s, 'Tít báo "Bão số 3 đổ bộ vào miền Trung" thể hiện đặc trưng nào?', ['Tính thời sự, ngắn gọn', 'Tính hình tượng', 'Tính cá thể hóa', 'Tính đa nghĩa'], 0, 'Tít báo ngắn gọn, thông tin sự kiện nóng hổi — đậm chất báo chí.', $d);

        $this->matching($s, 'Hãy nối mỗi đặc trưng báo chí với biểu hiện.', [['Tính thời sự', 'Đưa tin kịp thời'], ['Tính ngắn gọn', 'Câu văn súc tích'], ['Tính khách quan', 'Phản ánh trung thực'], ['Tính sinh động', 'Hấp dẫn người đọc']], 'Bốn đặc trưng làm nên "chất" báo chí: nhanh – gọn – thật – hay.', $d);
        $this->matching($s, 'Hãy nối mỗi thể loại báo chí với đặc điểm.', [['Bản tin', 'Ngắn gọn, khách quan'], ['Phóng sự', 'Miêu tả sâu, có bình luận'], ['Xã luận', 'Bày tỏ quan điểm'], ['Phỏng vấn', 'Hỏi – đáp trực tiếp']], 'Mỗi thể loại báo chí có cách đưa tin riêng.', $d);
        $this->matching($s, 'Hãy nối mỗi yếu tố của bản tin với nội dung.', [['Tít', 'Tóm tắt sự kiện'], ['Sapo', 'Mở đầu ngắn gọn'], ['Thân tin', 'Chi tiết sự kiện'], ['Kết', 'Thông tin bổ sung']], 'Cấu trúc bản tin: tít – sapo – thân – kết, đi từ khái quát đến chi tiết.', $d);
        $this->matching($s, 'Nối mỗi câu với phong cách ngôn ngữ của nó.', [['"Bão đổ bộ lúc 5 giờ sáng."', 'Báo chí'], ['"Bão gầm thét như con thú dữ."', 'Văn chương'], ['"Theo số liệu thống kê..."', 'Khoa học'], ['"Kính gửi..."', 'Hành chính']], 'Cùng nói về bão nhưng mỗi phong cách một cách diễn đạt.', $d);
        $this->matching($s, 'Nối mỗi yêu cầu với lí do.', [['Ngắn gọn', 'Độc giả ít thời gian'], ['Chính xác', 'Tạo niềm tin'], ['Kịp thời', 'Tin nóng có giá trị'], ['Khách quan', 'Tránh thiên kiến']], 'Yêu cầu của báo chí xuất phát từ nhu cầu thực tế của độc giả.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi đặc điểm vào nhóm BÁO CHÍ hoặc VĂN CHƯƠNG.', [['Thời sự', 'BÁO CHÍ'], ['Cảm xúc dạt dào', 'VĂN CHƯƠNG'], ['Ngắn gọn', 'BÁO CHÍ'], ['Hình ảnh ẩn dụ', 'VĂN CHƯƠNG'], ['Khách quan', 'BÁO CHÍ'], ['Cá thể hóa', 'VĂN CHƯƠNG']], 'Báo chí: nhanh, gọn, thật. Văn chương: đẹp, cảm xúc, cá tính.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÓ TÍNH THỜI SỰ hoặc KHÔNG.', [['"Sáng nay, bão đổ bộ."', 'CÓ TÍNH THỜI SỰ'], ['"Ngày xưa có một ông vua."', 'KHÔNG'], ['"Chiều qua, đội tuyển thắng."', 'CÓ TÍNH THỜI SỰ'], ['"Truyện kể rằng..."', 'KHÔNG']], 'Thời sự gắn với "hôm nay", "sáng nay" — sự kiện đang diễn ra.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi cách viết tít báo vào nhóm HAY hoặc CHƯA HAY.', [['"Bão số 3 đổ bộ miền Trung"', 'HAY'], ['"Có một cơn bão đã đổ bộ vào miền Trung vào sáng ngày hôm nay"', 'CHƯA HAY'], ['"Đội tuyển thắng 2-0"', 'HAY'], ['"Trận đấu đã diễn ra và kết quả là..."', 'CHƯA HAY']], 'Tít báo hay: ngắn gọn, đủ thông tin, gây chú ý.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi văn bản vào nhóm THUỘC BÁO CHÍ hoặc KHÔNG.', [['Bản tin thời sự', 'THUỘC BÁO CHÍ'], ['Bài thơ tình', 'KHÔNG'], ['Phóng sự', 'THUỘC BÁO CHÍ'], ['Truyện ngắn', 'KHÔNG']], 'Tin, phóng sự, xã luận, phỏng vấn thuộc báo chí.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm NGÔN NGỮ BÁO CHÍ hoặc VĂN CHƯƠNG.', [['"Giá vàng hôm nay tăng."', 'NGÔN NGỮ BÁO CHÍ'], ['"Vàng óng như nắng mai."', 'VĂN CHƯƠNG'], ['"Tai nạn làm 2 người bị thương."', 'NGÔN NGỮ BÁO CHÍ'], ['"Nỗi đau như dao cắt."', 'VĂN CHƯƠNG']], 'Báo chí: thông tin khô, gọn. Văn chương: hình ảnh, cảm xúc.', $d);

        $this->fill($s, 'Chức năng chính của phong cách ngôn ngữ báo chí là thông tin thời ___.', [[0, 'sự']], 'Báo chí = thông tin thời sự: nhanh, mới, nóng hổi.', $d);
        $this->fill($s, 'Ngôn ngữ báo chí yêu cầu rõ ràng, chính xác và ngắn ___.', [[0, 'gọn']], 'Ngắn gọn là "luật vàng": độc giả không có thời gian đọc dài.', $d);
        $this->fill($s, 'Đặc trưng về sự kịp thời của thông tin báo chí gọi là tính thời ___.', [[0, 'sự']], 'Tin cũ là tin chết — thời sự quyết định giá trị bản tin.', $d);
        $this->fill($s, 'Câu văn báo chí cần súc ___, đi thẳng vào vấn đề.', [[0, 'tích']], '"Súc tích": ngắn mà đủ ý, không lan man, rườm rà.', $d);
        $this->fill($s, 'Nguyên tắc hàng đầu của báo chí là tôn trọng sự ___.', [[0, 'thật']], 'Tin giả là "tử huyệt" của báo chí — sự thật là sinh mệnh.', $d);
    }

    /* ============ tieng-viet-thpt-10-pcnc-bao-chi-2 (Tiếng Việt 10, trung_binh) ============ */
    private function seedTiengVietThpt10BaoChi2(): void
    {
        $s = 'tieng-viet-thpt-10-pcnc-bao-chi-2'; $d = 'trung_binh';
        $this->quiz($s, 'Điểm khác biệt cơ bản giữa bản tin và phóng sự là gì?', ['Bản tin ngắn gọn, khách quan; phóng sự sâu, có miêu tả, bình luận', 'Bản tin dài hơn phóng sự', 'Phóng sự không cần sự thật', 'Hai thể loại giống nhau'], 0, 'Bản tin: đưa tin nhanh, gọn. Phóng sự: phản ánh sâu, sinh động, có quan điểm.', $d);
        $this->quiz($s, 'Để được coi là đầy đủ, một bản tin cần trả lời những câu hỏi nào?', ['Ai? Cái gì? Ở đâu? Khi nào?', 'Tại sao văn chương hay?', 'Ai đẹp nhất?', 'Món gì ngon?'], 0, 'Công thức 5W1H: Ai, làm gì, ở đâu, khi nào, tại sao, như thế nào.', $d);
        $this->quiz($s, 'Ngôn ngữ của phóng sự khác bản tin ở điểm nào?', ['Khô khan hơn', 'Sinh động hơn, có miêu tả, biểu cảm', 'Không cần chính xác', 'Viết bằng thơ'], 1, 'Phóng sự được phép miêu tả, kể chuyện sinh động; bản tin phải gọn, khách quan.', $d);
        $this->quiz($s, 'Một tít báo đạt chuẩn cần đảm bảo yêu cầu gì?', ['Thật dài', 'Ngắn gọn, hấp dẫn, nêu bật sự kiện', 'Mơ hồ, đa nghĩa', 'Viết bằng thơ'], 1, 'Tít báo là "bộ mặt" bản tin: ngắn, rõ, hút mắt người đọc.', $d);
        $this->quiz($s, 'Phần sapo trong bản tin có vai trò gì?', ['Kết luận', 'Tóm tắt nội dung chính ngay sau tít', 'Quảng cáo', 'Bình luận dài'], 1, 'Sapo giúp độc giả nắm nhanh nội dung trước khi đọc chi tiết.', $d);

        $this->matching($s, 'Nối mỗi thể loại với mục đích của nó.', [['Bản tin', 'Đưa tin nhanh'], ['Phóng sự', 'Phản ánh sâu sự kiện'], ['Xã luận', 'Bày tỏ quan điểm'], ['Phỏng vấn', 'Lấy ý kiến trực tiếp']], 'Mỗi thể loại phục vụ một mục đích thông tin riêng.', $d);
        $this->matching($s, 'Nối mỗi phần của bản tin với nội dung của nó.', [['Tít', 'Tên bản tin'], ['Sapo', 'Tóm tắt chính'], ['Thân tin', 'Diễn biến chi tiết'], ['Ảnh minh họa', 'Tăng tính chân thực']], 'Cấu trúc chuẩn giúp bản tin dễ đọc, dễ nắm bắt.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với thể loại phù hợp.', [['"Lúc 5h sáng, bão đổ bộ."', 'Bản tin'], ['"Tôi đã đến làng chài lúc bình minh..."', 'Phóng sự'], ['"Chúng ta cần chung tay..."', 'Xã luận'], ['"Ông nghĩ gì về..." – "Tôi cho rằng..."', 'Phỏng vấn']], 'Nhận diện thể loại qua giọng văn và cách trình bày.', $d);
        $this->matching($s, 'Nối mỗi yêu cầu với lí do của nó.', [['Tít ngắn gọn', 'Thu hút người đọc'], ['Thông tin chính xác', 'Tạo uy tín'], ['Có ảnh minh họa', 'Tăng sức thuyết phục'], ['Ngôn ngữ trong sáng', 'Dễ hiểu']], 'Yêu cầu nghề báo xuất phát từ nhu cầu độc giả và đạo đức nghề nghiệp.', $d);
        $this->matching($s, 'Nối mỗi câu hỏi 5W1H với ví dụ.', [['Ai?', '"Đội cứu hộ"'], ['Ở đâu?', '"Tại miền Trung"'], ['Khi nào?', '"Lúc 5 giờ sáng"'], ['Như thế nào?', '"Gió giật cấp 12"']], '5W1H là "xương sống" của mọi bản tin đầy đủ.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi đặc điểm vào nhóm BẢN TIN hoặc PHÓNG SỰ.', [['Ngắn gọn', 'BẢN TIN'], ['Miêu tả sinh động', 'PHÓNG SỰ'], ['Khách quan tuyệt đối', 'BẢN TIN'], ['Có bình luận', 'PHÓNG SỰ'], ['Trả lời 5W1H', 'BẢN TIN'], ['Kể chuyện', 'PHÓNG SỰ']], 'Bản tin: nhanh, gọn, khách quan. Phóng sự: sâu, sinh động, có quan điểm.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm PHÙ HỢP hoặc KHÔNG PHÙ HỢP với ngôn ngữ bản tin.', [['"Lúc 5h, bão đổ bộ."', 'PHÙ HỢP'], ['"Ôi cơn bão hung dữ quá!"', 'KHÔNG PHÙ HỢP'], ['"Thiệt hại ước 10 tỉ đồng."', 'PHÙ HỢP'], ['"Bão ơi, sao nỡ..."', 'KHÔNG PHÙ HỢP']], 'Bản tin cần khách quan, không cảm thán, không trữ tình.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi việc làm vào nhóm ĐÚNG hoặc SAI đạo đức báo chí.', [['Kiểm chứng thông tin', 'ĐÚNG'], ['Bịa tin câu view', 'SAI'], ['Tôn trọng sự thật', 'ĐÚNG'], ['Xuyên tạc sự kiện', 'SAI']], 'Đạo đức nghề báo: sự thật là trên hết.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi phần vào nhóm ĐẦU BẢN TIN hoặc CUỐI BẢN TIN.', [['Tít', 'ĐẦU BẢN TIN'], ['Sapo', 'ĐẦU BẢN TIN'], ['Chi tiết bổ sung', 'CUỐI BẢN TIN'], ['Kết luận', 'CUỐI BẢN TIN']], 'Bản tin đi từ khái quát (tít, sapo) đến chi tiết.', $d);
        $this->sortQ($s, 'Kéo mỗi tít báo vào nhóm HAY hoặc CHƯA HAY.', [['"Lũ lụt miền Trung: 10 người mất tích"', 'HAY'], ['"Có một vài chuyện xảy ra ở miền Trung"', 'CHƯA HAY'], ['"Giá xăng tăng lần thứ 3"', 'HAY'], ['"Hôm nay có nhiều tin tức"', 'CHƯA HAY']], 'Tít hay: cụ thể, ngắn gọn, nêu bật sự kiện.', $d);

        $this->fill($s, 'Thể loại đưa tin ngắn gọn, khách quan về sự kiện gọi là bản ___.', [[0, 'tin']], 'Bản tin là thể loại cơ bản nhất của báo chí.', $d);
        $this->fill($s, 'Thể loại phản ánh sâu sự kiện, có miêu tả và bình luận gọi là phóng ___.', [[0, 'sự']], 'Phóng sự "đi sâu" hơn bản tin, sinh động như văn chương.', $d);
        $this->fill($s, 'Phần tóm tắt nội dung chính ngay sau tít báo gọi là ___.', [[0, 'sapo']], 'Sapo (chapeau): đoạn mở đầu ngắn tóm tắt bản tin.', $d);
        $this->fill($s, 'Bản tin đầy đủ cần trả lời: ai, cái gì, ở đâu, khi nào, tại sao và như thế ___.', [[0, 'nào']], 'Công thức 5W1H đảm bảo bản tin đầy đủ thông tin.', $d);
        $this->fill($s, 'Phóng sự khác bản tin ở chỗ có thêm miêu tả sinh động và lời bình ___ của tác giả.', [[0, 'luận']], 'Bình luận là "gia vị" quan điểm của phóng sự.', $d);
    }

    /* ============ tieng-viet-thpt-11-cau-thanh-phan-1 (Tiếng Việt 11, trung_binh) ============ */
    private function seedTiengVietThpt11Cau1(): void
    {
        $s = 'tieng-viet-thpt-11-cau-thanh-phan-1'; $d = 'trung_binh';
        $this->quiz($s, 'Câu "Mẹ đang nấu cơm." là loại câu gì?', ['Câu đơn', 'Câu ghép', 'Câu đặc biệt', 'Câu rút gọn'], 0, 'Một cụm chủ – vị ("mẹ / đang nấu cơm") → câu đơn.', $d);
        $this->quiz($s, 'Câu "Trời mưa to, đường phố ngập nước." là loại câu gì?', ['Câu đơn', 'Câu ghép đẳng lập', 'Câu đặc biệt', 'Câu cảm thán'], 1, 'Hai vế ngang hàng, không phụ thuộc nhau → câu ghép đẳng lập.', $d);
        $this->quiz($s, 'Chủ ngữ trong câu "Những bông hoa thơm ngát." là gì?', ['Những bông hoa', 'thơm ngát', 'Những', 'hoa thơm'], 0, 'Chủ ngữ là cụm danh từ "những bông hoa", vị ngữ là "thơm ngát".', $d);
        $this->quiz($s, 'Trong câu "Em / học bài.", bộ phận "học bài" là gì?', ['Chủ ngữ', 'Vị ngữ', 'Trạng ngữ', 'Bổ ngữ'], 1, '"Học bài" trả lời "em làm gì?" → vị ngữ.', $d);
        $this->quiz($s, 'Câu ghép chính phụ khác câu ghép đẳng lập ở điểm nào?', ['Số lượng vế', 'Có vế chính – vế phụ, nối bằng quan hệ từ', 'Độ dài câu', 'Dấu câu'], 1, 'Câu ghép chính phụ có quan hệ chính – phụ (vì... nên, tuy... nhưng...).', $d);

        $this->matching($s, 'Nối mỗi loại câu với đặc điểm của nó.', [['Câu đơn', 'Một cụm chủ – vị'], ['Câu ghép đẳng lập', 'Các vế ngang hàng'], ['Câu ghép chính phụ', 'Vế chính – vế phụ'], ['Câu đặc biệt', 'Không có cấu tạo chủ – vị']], 'Bốn loại câu cơ bản theo cấu tạo ngữ pháp.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với loại câu tương ứng.', [['"Gió thổi mạnh."', 'Câu đơn'], ['"Mưa tạnh, nắng lên."', 'Câu ghép đẳng lập'], ['"Vì mưa nên đường trơn."', 'Câu ghép chính phụ'], ['"Mưa! Mưa to quá!"', 'Câu đặc biệt']], 'Nhận diện loại câu qua số vế và quan hệ giữa các vế.', $d);
        $this->matching($s, 'Hãy nối mỗi thành phần với câu hỏi nhận diện.', [['Chủ ngữ', 'Ai? Cái gì?'], ['Vị ngữ', 'Làm gì? Thế nào?'], ['Trạng ngữ', 'Khi nào? Ở đâu?'], ['Định ngữ', 'Nào? (thuộc tính)']], 'Bốn câu hỏi vàng nhận diện thành phần câu.', $d);
        $this->matching($s, 'Nối mỗi vế câu ghép với quan hệ của nó.', [['"Trời mưa" (Trời mưa, đường trơn)', 'Vế đẳng lập'], ['"Vì trời mưa" (Vì mưa nên trơn)', 'Vế phụ chỉ nguyên nhân'], ['"nên đường trơn"', 'Vế chính chỉ kết quả'], ['"Mưa tạnh" (Mưa tạnh, nắng lên)', 'Vế đẳng lập']], 'Vế phụ bổ nghĩa cho vế chính; vế đẳng lập ngang hàng nhau.', $d);
        $this->matching($s, 'Nối mỗi quan hệ từ với loại câu ghép nó tạo ra.', [['và, rồi', 'Câu ghép đẳng lập'], ['vì... nên...', 'Câu ghép chính phụ'], ['tuy... nhưng...', 'Câu ghép chính phụ'], ['vừa... vừa...', 'Câu ghép đẳng lập']], 'Quan hệ từ là "dấu hiệu" nhận biết loại câu ghép.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÂU ĐƠN hoặc CÂU GHÉP.', [['"Lá rơi đầy sân."', 'CÂU ĐƠN'], ['"Gió thổi, lá bay."', 'CÂU GHÉP'], ['"Mẹ về nhà."', 'CÂU ĐƠN'], ['"Trời tối, đèn sáng."', 'CÂU GHÉP']], 'Đếm cụm chủ – vị: một là đơn, nhiều là ghép.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU GHÉP ĐẲNG LẬP hoặc CÂU GHÉP CHÍNH PHỤ.', [['"Mưa tạnh, nắng lên."', 'CÂU GHÉP ĐẲNG LẬP'], ['"Vì mưa nên đường trơn."', 'CÂU GHÉP CHÍNH PHỤ'], ['"Gió thổi, mây bay."', 'CÂU GHÉP ĐẲNG LẬP'], ['"Tuy mệt nhưng vui."', 'CÂU GHÉP CHÍNH PHỤ']], 'Có quan hệ từ chỉ nguyên nhân, tương phản → chính phụ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi cụm từ vào nhóm CỤM CHỦ – VỊ hoặc KHÔNG PHẢI.', [['"trời / mưa"', 'CỤM CHỦ – VỊ'], ['"rất đẹp"', 'KHÔNG PHẢI'], ['"em / học bài"', 'CỤM CHỦ – VỊ'], ['"trên sân"', 'KHÔNG PHẢI']], 'Cụm chủ – vị phải có đủ hai thành phần chính.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi quan hệ từ vào nhóm NỐI VẾ GHÉP hoặc KHÔNG.', [['và', 'NỐI VẾ GHÉP'], ['rất', 'KHÔNG'], ['nhưng', 'NỐI VẾ GHÉP'], ['đang', 'KHÔNG']], '"Rất, đang" là phụ từ, không có chức năng nối vế câu.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phần trong câu "Sáng nay, Lan / học bài." vào nhóm đúng.', [['Sáng nay', 'TRẠNG NGỮ'], ['Lan', 'CHỦ NGỮ'], ['học bài', 'VỊ NGỮ']], 'Phân tích đầy đủ ba thành phần của câu đơn có trạng ngữ.', $d);

        $this->fill($s, 'Người ta gọi câu chỉ có một cụm chủ ngữ – vị ngữ là câu ___.', [[0, 'đơn']], 'Một cụm chủ – vị là dấu hiệu của câu đơn.', $d);
        $this->fill($s, 'Câu gồm từ hai cụm chủ ngữ – vị ngữ trở lên được gọi là câu ___.', [[0, 'ghép']], 'Nhiều vế câu hợp lại thành câu ghép.', $d);
        $this->fill($s, 'Thành phần trả lời câu hỏi "Ai? Cái gì?" trong câu gọi là chủ ___.', [[0, 'ngữ']], 'Chủ ngữ nêu đối tượng được nói tới trong câu.', $d);
        $this->fill($s, 'Thành phần trả lời câu hỏi "Làm gì? Thế nào?" gọi là vị ___.', [[0, 'ngữ']], 'Vị ngữ nêu hoạt động, đặc điểm của chủ ngữ.', $d);
        $this->fill($s, 'Câu ghép có vế chính – vế phụ nối bằng quan hệ từ gọi là câu ghép chính ___.', [[0, 'phụ']], 'Ví dụ: "Vì trời mưa (phụ) nên đường trơn (chính)".', $d);
    }

    /* ============ tieng-viet-thpt-11-cau-thanh-phan-2 (Tiếng Việt 11, trung_binh) ============ */
    private function seedTiengVietThpt11Cau2(): void
    {
        $s = 'tieng-viet-thpt-11-cau-thanh-phan-2'; $d = 'trung_binh';
        $this->quiz($s, 'Trạng ngữ "trong vườn" trong câu "Trong vườn, hoa nở rộ." chỉ gì?', ['Thời gian', 'Nơi chốn', 'Nguyên nhân', 'Mục đích'], 1, '"Trong vườn" trả lời "ở đâu?" → trạng ngữ chỉ nơi chốn.', $d);
        $this->quiz($s, 'Câu "Mưa!" là loại câu gì?', ['Câu đơn', 'Câu ghép', 'Câu đặc biệt', 'Câu cảm thán dài'], 2, '"Mưa!" không có cấu tạo chủ – vị, chỉ gồm một từ → câu đặc biệt.', $d);
        $this->quiz($s, 'Điểm phân biệt câu rút gọn với câu đặc biệt là gì?', ['Câu rút gọn khôi phục được thành phần lược bỏ, câu đặc biệt thì không', 'Câu rút gọn dài hơn', 'Câu đặc biệt có chủ ngữ', 'Hai loại giống nhau'], 0, 'Rút gọn: lược bỏ nhưng khôi phục được. Đặc biệt: vốn không có cấu tạo chủ – vị.', $d);
        $this->quiz($s, 'Thành phần "thưa bác" trong câu "Thưa bác, cháu về ạ." là gì?', ['Chủ ngữ', 'Vị ngữ', 'Thành phần biệt lập (gọi – đáp)', 'Trạng ngữ'], 2, '"Thưa bác" là thành phần gọi – đáp, không tham gia diễn đạt ý chính.', $d);
        $this->quiz($s, 'Câu "Lan ơi!" thuộc loại câu nào?', ['Câu đơn đầy đủ', 'Câu đặc biệt (gọi – đáp)', 'Câu ghép', 'Câu kể'], 1, 'Câu chỉ gồm từ gọi – đáp, không có chủ – vị → câu đặc biệt.', $d);

        $this->matching($s, 'Hãy nối mỗi trạng ngữ với loại của nó.', [['Sáng nay', 'Trạng ngữ chỉ thời gian'], ['Trong vườn', 'Trạng ngữ chỉ nơi chốn'], ['Vì trời mưa', 'Trạng ngữ chỉ nguyên nhân'], ['Để thi tốt', 'Trạng ngữ chỉ mục đích']], 'Bốn loại trạng ngữ: thời gian, nơi chốn, nguyên nhân, mục đích.', $d);
        $this->matching($s, 'Nối mỗi câu với loại câu của nó.', [['"Mưa!"', 'Câu đặc biệt'], ['"Đi học!" (khôi phục: Em đi học)', 'Câu rút gọn'], ['"Trời mưa."', 'Câu đơn'], ['"Lan ơi!"', 'Câu đặc biệt']], 'Phân biệt câu đặc biệt, câu rút gọn và câu đơn đầy đủ.', $d);
        $this->matching($s, 'Hãy nối mỗi thành phần biệt lập với ví dụ.', [['Gọi – đáp', '"Thưa bác, ..."'], ['Tình thái', '"Hình như trời sắp mưa."'], ['Cảm thán', '"Trời ơi, đẹp quá!"'], ['Chú thích', '"Hà Nội (thủ đô) rất đẹp."']], 'Bốn loại thành phần biệt lập không tham gia nòng cốt câu.', $d);
        $this->matching($s, 'Hãy nối mỗi câu hỏi với thành phần cần tìm.', [['"Khi nào?"', 'Trạng ngữ thời gian'], ['"Ở đâu?"', 'Trạng ngữ nơi chốn'], ['"Vì sao?"', 'Trạng ngữ nguyên nhân'], ['"Để làm gì?"', 'Trạng ngữ mục đích']], 'Câu hỏi là "chìa khóa" nhận diện loại trạng ngữ.', $d);
        $this->matching($s, 'Nối mỗi câu rút gọn với thành phần được khôi phục.', [['"Đi đâu đấy?" – "Đi chợ."', 'Chủ ngữ "tôi"'], ['"Ăn cơm chưa?" – "Rồi."', 'Vị ngữ "ăn rồi"'], ['"Ai đấy?" – "Tớ."', 'Chủ ngữ "tớ"'], ['"Học không?" – "Có."', 'Vị ngữ "học"']], 'Câu rút gọn lược bỏ thành phần đã rõ trong ngữ cảnh hội thoại.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi cụm từ vào nhóm TRẠNG NGỮ hoặc KHÔNG PHẢI TRẠNG NGỮ.', [['Sáng nay', 'TRẠNG NGỮ'], ['em', 'KHÔNG PHẢI TRẠNG NGỮ'], ['trong vườn', 'TRẠNG NGỮ'], ['học bài', 'KHÔNG PHẢI TRẠNG NGỮ']], 'Trạng ngữ bổ sung hoàn cảnh: thời gian, nơi chốn...', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÂU ĐẶC BIỆT hoặc CÂU RÚT GỌN.', [['"Mưa!"', 'CÂU ĐẶC BIỆT'], ['"Đi chợ." (khôi phục được)', 'CÂU RÚT GỌN'], ['"Ồ!"', 'CÂU ĐẶC BIỆT'], ['"Rồi ạ." (khôi phục được)', 'CÂU RÚT GỌN']], 'Khôi phục được → rút gọn. Không có cấu tạo chủ – vị → đặc biệt.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi thành phần biệt lập vào nhóm đúng loại.', [['"Thưa cô"', 'GỌI – ĐÁP'], ['"Hình như"', 'TÌNH THÁI'], ['"Trời ơi"', 'CẢM THÁN'], ['"(thủ đô)"', 'CHÚ THÍCH']], 'Bốn loại thành phần biệt lập: gọi – đáp, tình thái, cảm thán, chú thích.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi ý vào nhóm THÀNH PHẦN CHÍNH hoặc THÀNH PHẦN PHỤ của câu.', [['Chủ ngữ', 'THÀNH PHẦN CHÍNH'], ['Vị ngữ', 'THÀNH PHẦN CHÍNH'], ['Trạng ngữ', 'THÀNH PHẦN PHỤ'], ['Thành phần biệt lập', 'THÀNH PHẦN PHỤ']], 'Chính: chủ – vị. Phụ: trạng ngữ, biệt lập...', $d);
        $this->sortQ($s, 'Kéo mỗi trạng ngữ vào nhóm CHỈ THỜI GIAN hoặc CHỈ NƠI CHỐN.', [['Sáng nay', 'CHỈ THỜI GIAN'], ['Trong vườn', 'CHỈ NƠI CHỐN'], ['Chiều qua', 'CHỈ THỜI GIAN'], ['Trên sân', 'CHỈ NƠI CHỐN']], '"Khi nào?" → thời gian. "Ở đâu?" → nơi chốn.', $d);

        $this->fill($s, 'Thành phần chỉ thời gian, nơi chốn, nguyên nhân của sự việc gọi là trạng ___.', [[0, 'ngữ']], 'Trạng ngữ bổ sung hoàn cảnh cho nòng cốt câu.', $d);
        $this->fill($s, 'Câu không có cấu tạo chủ ngữ – vị ngữ gọi là câu đặc ___.', [[0, 'biệt']], 'Câu đặc biệt: "Mưa!", "Lan ơi!", "Ồ!"...', $d);
        $this->fill($s, 'Câu lược bớt thành phần nhưng khôi phục được gọi là câu rút ___.', [[0, 'gọn']], 'Rút gọn thường gặp trong hội thoại, khẩu ngữ.', $d);
        $this->fill($s, 'Thành phần không tham gia diễn đạt ý chính của câu gọi là thành phần biệt ___.', [[0, 'lập']], 'Biệt lập: gọi – đáp, tình thái, cảm thán, chú thích.', $d);
        $this->fill($s, 'Trạng ngữ thường đứng ở ___ câu và ngăn cách bằng dấu phẩy.', [[0, 'đầu']], 'Vị trí quen thuộc: "Sáng nay, em đi học."', $d);
    }

    /* ============ tieng-viet-thpt-11-bien-phap-tu-tu-1 (Tiếng Việt 11, trung_binh) ============ */
    private function seedTiengVietThpt11BienPhap1(): void
    {
        $s = 'tieng-viet-thpt-11-bien-phap-tu-tu-1'; $d = 'trung_binh';
        $this->quiz($s, 'Câu "Mặt trời xuống biển như hòn lửa." sử dụng biện pháp tu từ nào?', ['Nhân hoá', 'So sánh', 'Ẩn dụ', 'Hoán dụ'], 1, 'Có từ "như" đối chiếu mặt trời với hòn lửa → so sánh.', $d);
        $this->quiz($s, 'Câu "Cả làng Vũ Đại nhao lên." ("làng" chỉ người dân) sử dụng biện pháp nào?', ['Ẩn dụ', 'Hoán dụ', 'So sánh', 'Nhân hoá'], 1, 'Lấy vật chứa (làng) chỉ vật bị chứa (dân làng) → hoán dụ.', $d);
        $this->quiz($s, 'Biện pháp nhân hóa khác ẩn dụ ở điểm nào?', ['Nhân hóa gán đặc điểm người cho vật; ẩn dụ gọi tên gián tiếp theo tương đồng', 'Nhân hóa khó hơn', 'Ẩn dụ chỉ dùng trong thơ', 'Hai phép giống nhau'], 0, 'Nhân hóa: vật + đặc điểm người. Ẩn dụ: A gọi bằng tên B tương đồng.', $d);
        $this->quiz($s, 'Câu "Thôn Đoài ngồi nhớ thôn Đông." ("thôn" chỉ người) sử dụng biện pháp nào?', ['So sánh', 'Hoán dụ', 'Nhân hoá', 'Ẩn dụ'], 1, 'Lấy địa danh (thôn) chỉ con người ở đó → hoán dụ vật chứa.', $d);
        $this->quiz($s, 'Hình ảnh "giọt sương" trong thơ thường tượng trưng cho điều gì?', ['Sự giàu sang', 'Vẻ đẹp mong manh, trong sáng', 'Sức mạnh', 'Nỗi buồn chiến tranh'], 1, 'Giọt sương long lanh mà dễ tan — biểu tượng của vẻ đẹp mong manh.', $d);

        $this->matching($s, 'Hãy nối mỗi biện pháp với dấu hiệu nhận biết.', [['So sánh', 'Từ "như, tựa, là"'], ['Nhân hóa', 'Đặc điểm con người'], ['Ẩn dụ', 'Gọi tên gián tiếp, tương đồng'], ['Hoán dụ', 'Quan hệ gần gũi']], 'Bốn biện pháp tu từ từ vựng quan trọng nhất bậc THPT.', $d);
        $this->matching($s, 'Nối mỗi câu với biện pháp tu từ của nó.', [['"Trăng tròn như đĩa."', 'So sánh'], ['"Ông trăng mỉm cười."', 'Nhân hóa'], ['"Thuyền về nhớ bến."', 'Ẩn dụ'], ['"Cả trường reo hò."', 'Hoán dụ']], 'Luyện nhận diện nhanh bốn biện pháp qua ví dụ kinh điển.', $d);
        $this->matching($s, 'Hãy nối mỗi kiểu ẩn dụ với ví dụ.', [['Ẩn dụ hình thức', '"Lửa lựu" (hoa như lửa)'], ['Ẩn dụ cách thức', '"Ăn quả nhớ kẻ trồng cây"'], ['Ẩn dụ phẩm chất', '"Sen" (người quân tử)'], ['Ẩn dụ chuyển đổi cảm giác', '"Nắng ửng hồng"']], 'Bốn kiểu ẩn dụ theo chương trình Ngữ văn.', $d);
        $this->matching($s, 'Hãy nối mỗi kiểu hoán dụ với ví dụ.', [['Bộ phận – toàn thể', '"Bàn tay ta..."'], ['Vật chứa – vật bị chứa', '"Cả lớp..."'], ['Dấu hiệu – sự vật', '"Áo chàm..."'], ['Cụ thể – trừu tượng', '"Tay trắng..."']], 'Bốn kiểu hoán dụ quen thuộc cần nắm vững.', $d);
        $this->matching($s, 'Nối mỗi hình ảnh trong "Truyện Kiều" với biện pháp.', [['"Bóng hồng"', 'Ẩn dụ (chỉ Thúy Kiều)'], ['"Tuyết"', 'Ẩn dụ (làn da trắng)'], ['"Mây"', 'Ẩn dụ (mái tóc)'], ['"Hoa"', 'Ẩn dụ (vẻ đẹp)']], 'Nguyễn Du là "bậc thầy" ẩn dụ: tả người đẹp bằng thiên nhiên.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm SO SÁNH hoặc NHÂN HÓA.', [['"Mây trắng như bông."', 'SO SÁNH'], ['"Cô mây dạo chơi."', 'NHÂN HÓA'], ['"Nước trong như gương."', 'SO SÁNH'], ['"Anh nắng tinh nghịch."', 'NHÂN HÓA']], 'Có "như" → so sánh; xưng hô người → nhân hóa.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm ẨN DỤ hoặc HOÁN DỤ.', [['"Thuyền về nhớ bến."', 'ẨN DỤ'], ['"Cả làng nhao lên."', 'HOÁN DỤ'], ['"Mặt trời trong lăng."', 'ẨN DỤ'], ['"Thôn Đoài nhớ thôn Đông."', 'HOÁN DỤ']], 'Tương đồng → ẩn dụ; gần gũi (làng–dân) → hoán dụ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ ngữ vào nhóm TỪ SO SÁNH hoặc KHÔNG PHẢI.', [['như', 'TỪ SO SÁNH'], ['rất', 'KHÔNG PHẢI'], ['tựa', 'TỪ SO SÁNH'], ['và', 'KHÔNG PHẢI']], '"Rất" là phụ từ, "và" là quan hệ từ — không phải từ so sánh.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi tác dụng vào nhóm CỦA SO SÁNH hoặc CỦA NHÂN HÓA.', [['Làm hình ảnh cụ thể', 'CỦA SO SÁNH'], ['Làm sự vật có hồn', 'CỦA NHÂN HÓA'], ['Đối chiếu hai sự vật', 'CỦA SO SÁNH'], ['Gán tình cảm người', 'CỦA NHÂN HÓA']], 'So sánh: đối chiếu. Nhân hóa: gán đặc điểm người.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ TU TỪ TỪ VỰNG hoặc KHÔNG.', [['"Trăng như đĩa."', 'CÓ TU TỪ TỪ VỰNG'], ['"Trời tối."', 'KHÔNG'], ['"Gió thì thầm."', 'CÓ TU TỪ TỪ VỰNG'], ['"Mưa rơi."', 'KHÔNG']], 'Tu từ từ vựng: so sánh, nhân hóa, ẩn dụ, hoán dụ...', $d);

        $this->fill($s, 'Biện pháp đối chiếu hai sự vật tương đồng, thường có từ "như" gọi là so ___.', [[0, 'sánh']], 'So sánh là biện pháp tu từ cơ bản, phổ biến nhất.', $d);
        $this->fill($s, 'Biện pháp gán đặc điểm của con người cho sự vật gọi là nhân ___.', [[0, 'hóa']], 'Nhân hóa làm thế giới sự vật gần gũi, sinh động.', $d);
        $this->fill($s, 'Biện pháp gọi tên gián tiếp theo nét tương đồng (không có từ so sánh) gọi là ẩn ___.', [[0, 'dụ']], 'Ẩn dụ là "so sánh ngầm": lược bỏ từ so sánh.', $d);
        $this->fill($s, 'Biện pháp gọi tên theo quan hệ gần gũi (vật chứa – vật bị chứa...) gọi là hoán ___.', [[0, 'dụ']], 'Hoán dụ: "cả lớp" (trường), "bàn tay" (người lao động).', $d);
        $this->fill($s, 'Bốn biện pháp tu từ từ vựng chính: so sánh, nhân hóa, ẩn dụ và hoán ___.', [[0, 'dụ']], 'Nắm vững bốn phép này là nắm được "xương sống" tu từ từ vựng.', $d);
    }

    /* ============ tieng-viet-thpt-11-bien-phap-tu-tu-2 (Tiếng Việt 11, trung_binh) ============ */
    private function seedTiengVietThpt11BienPhap2(): void
    {
        $s = 'tieng-viet-thpt-11-bien-phap-tu-tu-2'; $d = 'trung_binh';
        $this->quiz($s, 'Biện pháp điệp ngữ là gì?', ['Lặp lại từ ngữ có chủ ý để nhấn mạnh', 'Nói ngược lại', 'Viết sai chính tả', 'Liệt kê đồ vật'], 0, 'Điệp ngữ: lặp từ/cụm từ có chủ ý tạo nhịp điệu, nhấn mạnh.', $d);
        $this->quiz($s, 'Thế nào là biện pháp liệt kê?', ['Kể ra hàng loạt sự vật cùng loại', 'Nói quá sự thật', 'Nói giảm ý nghĩa', 'Chơi chữ'], 0, 'Liệt kê: kể ra hàng loạt để làm nổi bật, đầy đủ.', $d);
        $this->quiz($s, 'Câu "Đứt từng khúc ruột." sử dụng biện pháp nào?', ['Nói quá', 'Nói giảm', 'Liệt kê', 'Chơi chữ'], 0, 'Phóng đại nỗi đau ("đứt ruột") → nói quá.', $d);
        $this->quiz($s, 'Biện pháp nói giảm nói tránh có tác dụng gì?', ['Làm câu văn thô tục', 'Diễn đạt tế nhị, lịch sự', 'Phóng đại sự thật', 'Gây cười'], 1, 'Nói giảm: "đi xa" thay "chết" — tế nhị, tránh thô tục.', $d);
        $this->quiz($s, 'Câu "Bà già đi chợ Cầu Đông." — cách dùng từ "bà" nhiều nghĩa thuộc biện pháp nào?', ['Chơi chữ', 'Nói quá', 'Liệt kê', 'Điệp ngữ'], 0, 'Chơi chữ: dùng từ đồng âm, đa nghĩa tạo hiệu quả bất ngờ, thú vị.', $d);

        $this->matching($s, 'Hãy nối mỗi biện pháp với dấu hiệu nhận biết.', [['Điệp ngữ', 'Lặp lại từ ngữ'], ['Liệt kê', 'Kể ra hàng loạt'], ['Nói quá', 'Phóng đại'], ['Nói giảm', 'Diễn đạt tế nhị']], 'Bốn biện pháp tu từ cú pháp/ngữ nghĩa quan trọng.', $d);
        $this->matching($s, 'Nối mỗi câu với biện pháp tu từ của nó.', [['"Nhớ ai, nhớ ai..."', 'Điệp ngữ'], ['"Hoa, lá, chim, bướm..."', 'Liệt kê'], ['"Nặng như núi."', 'Nói quá'], ['"Bác đã đi xa."', 'Nói giảm']], 'Nhận diện qua dấu hiệu: lặp từ, kể loạt, phóng đại, tế nhị.', $d);
        $this->matching($s, 'Nối mỗi biện pháp với tác dụng của nó.', [['Điệp ngữ', 'Nhấn mạnh, tạo nhịp'], ['Liệt kê', 'Đầy đủ, cụ thể'], ['Nói quá', 'Tăng sức biểu cảm'], ['Chơi chữ', 'Hài hước, bất ngờ']], 'Mỗi biện pháp có "sở trường" riêng.', $d);
        $this->matching($s, 'Hãy nối mỗi ví dụ nói giảm với cách diễn đạt thẳng.', [['"đi xa"', 'chết'], ['"không được khỏe"', 'ốm'], ['"có tuổi"', 'già'], ['"yếu"', 'kém']], 'Nói giảm thay cách nói thẳng bằng cách nói nhẹ nhàng, lịch sự.', $d);
        $this->matching($s, 'Nối mỗi câu với kiểu chơi chữ.', [['"Bà già đi chợ Cầu Đông"', 'Dùng từ đa nghĩa'], ['"Con ngựa đá con ngựa đá"', 'Dùng từ đồng âm'], ['"Tài đến, tai đến"', 'Đồng âm khác nghĩa'], ['"Mênh mông, mong manh"', 'Láy âm đầu']], 'Chơi chữ dựa trên sự trùng hợp âm thanh, đa nghĩa của từ.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm ĐIỆP NGỮ hoặc LIỆT KÊ.', [['"Nhớ nhà, nhớ mẹ..."', 'ĐIỆP NGỮ'], ['"Bàn, ghế, sách, vở..."', 'LIỆT KÊ'], ['"Đẹp quá, đẹp quá!"', 'ĐIỆP NGỮ'], ['"Hoa, lá, cỏ, cây..."', 'LIỆT KÊ']], 'Điệp: lặp lại. Liệt kê: kể ra nhiều thứ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm NÓI QUÁ hoặc NÓI GIẢM.', [['"Đói rã ruột."', 'NÓI QUÁ'], ['"Bác đã đi xa."', 'NÓI GIẢM'], ['"Đẹp nghiêng nước nghiêng thành."', 'NÓI QUÁ'], ['"Cháu hơi mệt."', 'NÓI GIẢM']], 'Nói quá phóng đại; nói giảm làm nhẹ đi cho tế nhị.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi cặp từ vào nhóm CHƠI CHỮ hoặc KHÔNG.', [['"tài – tai"', 'CHƠI CHỮ'], ['"bàn – ghế"', 'KHÔNG'], ['"đá (đá bóng – hòn đá)"', 'CHƠI CHỮ'], ['"sông – núi"', 'KHÔNG']], 'Chơi chữ lợi dụng đồng âm, đa nghĩa.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi tác dụng vào nhóm CỦA ĐIỆP NGỮ hoặc CỦA LIỆT KÊ.', [['Nhấn mạnh', 'CỦA ĐIỆP NGỮ'], ['Cụ thể, đầy đủ', 'CỦA LIỆT KÊ'], ['Tạo nhịp điệu', 'CỦA ĐIỆP NGỮ'], ['Bao quát', 'CỦA LIỆT KÊ']], 'Điệp ngữ: nhấn + nhịp. Liệt kê: đủ + cụ thể.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÓ BIỆN PHÁP CÚ PHÁP hoặc KHÔNG.', [['"Anh đi, em ở."', 'CÓ BIỆN PHÁP CÚ PHÁP'], ['"Trời mưa."', 'KHÔNG'], ['"Nhớ ai, nhớ ai tha thiết."', 'CÓ BIỆN PHÁP CÚ PHÁP'], ['"Mẹ nấu cơm."', 'KHÔNG']], 'Biện pháp cú pháp: điệp ngữ, liệt kê, đảo ngữ, câu hỏi tu từ...', $d);

        $this->fill($s, 'Biện pháp lặp lại từ ngữ có chủ ý để nhấn mạnh gọi là điệp ___.', [[0, 'ngữ']], 'Điệp ngữ tạo nhịp điệu và nhấn mạnh cảm xúc.', $d);
        $this->fill($s, 'Biện pháp kể ra hàng loạt sự vật cùng loại gọi là liệt ___.', [[0, 'kê']], 'Liệt kê làm cho sự vật hiện lên đầy đủ, cụ thể.', $d);
        $this->fill($s, 'Biện pháp phóng đại mức độ, tính chất của sự vật gọi là nói ___.', [[0, 'quá']], 'Nói quá: "đói rã ruột", "đẹp nghiêng nước nghiêng thành".', $d);
        $this->fill($s, 'Biện pháp dùng từ đồng âm, đa nghĩa tạo hiệu quả bất ngờ gọi là chơi ___.', [[0, 'chữ']], 'Chơi chữ: "Con ngựa đá con ngựa đá không đá con ngựa".', $d);
        $this->fill($s, 'Biện pháp diễn đạt tế nhị thay cho cách nói thẳng, thô gọi là nói ___.', [[0, 'giảm']], 'Nói giảm nói tránh: "đi xa", "không được khỏe".', $d);
    }

    /* ============ tieng-viet-thpt-12-chua-loi-1 (Tiếng Việt 12, kho) ============ */
    private function seedTiengVietThpt12ChuaLoi1(): void
    {
        $s = 'tieng-viet-thpt-12-chua-loi-1'; $d = 'kho';
        $this->quiz($s, 'Câu "Cô ấy có vẻ đẹp lộng lẫy, kiêu sa." — cách kết hợp từ nào CHƯA chính xác?', ['Không có lỗi', '"Kiêu sa" không hợp với "lộng lẫy" (kiêu sa mang nghĩa chê)', 'Thiếu chủ ngữ', 'Sai dấu câu'], 1, '"Kiêu sa" (kiêu căng) mang sắc thái chê, không kết hợp được với lời khen "lộng lẫy".', $d);
        $this->quiz($s, 'Từ nào dùng SAI trong câu: "Chúng ta cần đấu tranh chống lại cái xấu."?', ['Không có từ sai', 'Thừa từ "lại" (đấu tranh đã hàm ý chống)', 'Thiếu từ', 'Sai dấu câu'], 1, '"Đấu tranh chống lại" thừa từ: "đấu tranh" đã bao hàm nghĩa "chống".', $d);
        $this->quiz($s, 'Hiểu thế nào về lỗi "dùng từ sai nghĩa"?', ['Dùng từ không đúng nghĩa vốn có của nó', 'Viết sai chính tả', 'Thiếu dấu câu', 'Sai ngữ pháp câu'], 0, 'Ví dụ: "béo bở" (chỉ lợi lộc) dùng cho người béo — sai nghĩa.', $d);
        $this->quiz($s, 'Câu "Anh ấy là người bao dung, luôn giúp đỡ mọi người." — từ nào dùng SAI nghĩa?', ['Không sai', '"Bao dung" dùng chưa hợp (bao dung là tha thứ lỗi lầm)', 'Thiếu vị ngữ', 'Sai dấu phẩy'], 1, '"Bao dung" nghĩa là rộng lượng tha thứ; giúp đỡ mọi người hợp với "nhân hậu" hơn.', $d);
        $this->quiz($s, 'Câu nào dùng từ Hán Việt SAI phong cách?', ['"Ông ấy từ trần."', '"Nó ngoẻo rồi." (trong văn bản trang trọng)', '"Bác ấy mất."', '"Cụ đã qua đời."'], 1, '"Ngoẻo" là từ khẩu ngữ thô tục, không dùng trong văn bản trang trọng.', $d);

        $this->matching($s, 'Hãy nối mỗi lỗi dùng từ với ví dụ.', [['Dùng từ sai nghĩa', '"Béo bở" chỉ người béo'], ['Lặp từ', '"Đấu tranh chống lại"'], ['Kết hợp từ gượng ép', '"Kiêu sa, lộng lẫy"'], ['Sai phong cách', '"Ngoẻo" trong văn trang trọng']], 'Bốn lỗi dùng từ kinh điển cần nhận diện và chữa.', $d);
        $this->matching($s, 'Nối mỗi câu sai với cách chữa đúng.', [['"Đấu tranh chống lại cái xấu"', '"Đấu tranh chống cái xấu"'], ['"Vẻ đẹp kiêu sa, lộng lẫy"', '"Vẻ đẹp lộng lẫy, kiêu hãnh"'], ['"Bao dung giúp đỡ mọi người"', '"Nhân hậu, hay giúp đỡ"'], ['"Nó ngoẻo rồi" (trang trọng)', '"Ông ấy đã từ trần"']], 'Chữa lỗi: bỏ từ thừa, thay từ sai nghĩa, đúng phong cách.', $d);
        $this->matching($s, 'Hãy nối mỗi từ Hán Việt với nghĩa đúng.', [['từ trần', 'chết (trang trọng)'], ['hi sinh', 'chết vì nghĩa lớn'], ['quá cố', 'đã chết'], ['thăng thiên', 'chết (vua chúa)']], 'Từ Hán Việt có sắc thái trang trọng, phân biệt mức độ rõ ràng.', $d);
        $this->matching($s, 'Nối mỗi cặp từ với quan hệ của chúng.', [['bao dung – nhân hậu', 'Gần nghĩa, khác sắc thái'], ['từ trần – ngoẻo', 'Trái phong cách'], ['kiên trì – ngoan cố', 'Khen – chê'], ['thật thà – ngây thơ', 'Gần nghĩa']], 'Từ gần nghĩa khác nhau ở sắc thái: khen, chê, trung hòa.', $d);
        $this->matching($s, 'Nối mỗi từ với sắc thái của nó.', [['bao dung', 'Tha thứ, rộng lượng'], ['nhân hậu', 'Thương người, hay giúp đỡ'], ['kiêu sa', 'Kiêu căng (chê)'], ['kiêu hãnh', 'Tự hào (khen)']], '"Kiêu sa" và "kiêu hãnh" chỉ khác một chữ mà sắc thái trái ngược.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (lỗi dùng từ).', [['"Em sẽ cố gắng học tốt."', 'ĐÚNG'], ['"Đấu tranh chống lại cái xấu."', 'SAI (thừa từ)'], ['"Bạn ấy rất nhân hậu."', 'ĐÚNG'], ['"Vẻ đẹp kiêu sa, lộng lẫy."', 'SAI (sai nghĩa)']], 'Nhận diện lỗi: thừa từ, sai nghĩa, sai phong cách.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi kết hợp từ vào nhóm TỰ NHIÊN hoặc GƯỢNG ÉP.', [['"Nắng vàng rực rỡ."', 'TỰ NHIÊN'], ['"Nắng vàng kiêu sa."', 'GƯỢNG ÉP'], ['"Tình bạn thắm thiết."', 'TỰ NHIÊN'], ['"Tình bạn béo bở."', 'GƯỢNG ÉP']], 'Kết hợp từ phải hợp nghĩa, hợp sắc thái — "béo bở" không đi với "tình bạn".', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm TỪ THUẦN VIỆT hoặc TỪ HÁN VIỆT.', [['chết', 'TỪ THUẦN VIỆT'], ['từ trần', 'TỪ HÁN VIỆT'], ['đi', 'TỪ THUẦN VIỆT'], ['quá cố', 'TỪ HÁN VIỆT']], 'Thuần Việt: nôm na, gần gũi. Hán Việt: trang trọng, uy nghi.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi cách diễn đạt vào nhóm TRANG TRỌNG hoặc THÂN MẬT.', [['"Ông đã từ trần."', 'TRANG TRỌNG'], ['"Nó ngoẻo rồi."', 'THÂN MẬT (thô)'], ['"Kính thưa..."', 'TRANG TRỌNG'], ['"Ê, mày ơi!"', 'THÂN MẬT']], 'Chọn từ đúng phong cách: trang trọng dùng Hán Việt, thân mật dùng khẩu ngữ.', $d);
        $this->sortQ($s, 'Kéo mỗi từ vào nhóm MANG NGHĨA KHEN hoặc CHÊ.', [['kiên trì', 'MANG NGHĨA KHEN'], ['ngoan cố', 'MANG NGHĨA CHÊ'], ['tiết kiệm', 'MANG NGHĨA KHEN'], ['keo kiệt', 'MANG NGHĨA CHÊ']], 'Cặp từ cùng chỉ một tính chất nhưng sắc thái khen – chê đối lập.', $d);

        $this->fill($s, 'Lỗi dùng một từ hai lần không chủ ý trong câu gọi là lỗi lặp ___.', [[0, 'từ']], 'Ví dụ: "đấu tranh chống lại" — "chống" đã có trong "đấu tranh".', $d);
        $this->fill($s, 'Dùng từ không đúng với nghĩa vốn có của nó gọi là lỗi dùng từ sai ___.', [[0, 'nghĩa']], 'Ví dụ: "béo bở" chỉ lợi lộc, không dùng để tả người béo.', $d);
        $this->fill($s, 'Kết hợp các từ không phù hợp về nghĩa, sắc thái gọi là kết hợp từ gượng ___.', [[0, 'ép']], 'Ví dụ: "kiêu sa" (chê) không đi với "lộng lẫy" (khen).', $d);
        $this->fill($s, 'Khi viết văn bản trang trọng, nên dùng từ Hán ___ thay cho từ khẩu ngữ thô tục.', [[0, 'Việt']], '"Từ trần" thay "ngoẻo" — đúng phong cách, tôn trọng người đọc.', $d);
        $this->fill($s, '"Kiên trì" mang nghĩa khen, còn "ngoan ___" cùng tính chất nhưng mang nghĩa chê.', [[0, 'cố']], 'Cặp khen – chê: kiên trì/ngoan cố, tiết kiệm/keo kiệt, thẳng thắn/bỗ bã.', $d);
    }

    /* ============ tieng-viet-thpt-12-chua-loi-2 (Tiếng Việt 12, kho) ============ */
    private function seedTiengVietThpt12ChuaLoi2(): void
    {
        $s = 'tieng-viet-thpt-12-chua-loi-2'; $d = 'kho';
        $this->quiz($s, 'Câu "Qua bài thơ, cho em hiểu thêm về quê hương." mắc lỗi gì?', ['Sai chính tả', 'Thiếu chủ ngữ', 'Thừa vị ngữ', 'Sai dấu câu'], 1, 'Câu chỉ có trạng ngữ "qua bài thơ" và vị ngữ, thiếu chủ ngữ "ai hiểu?".', $d);
        $this->quiz($s, 'Câu "Em rất thích đọc sách và mẹ em cũng vậy." có lỗi gì?', ['Thiếu chủ ngữ', 'Lỗi logic: "cũng vậy" gây hiểu nhầm', 'Sai chính tả', 'Không có lỗi'], 1, '"Mẹ em cũng vậy" — cũng thích đọc sách hay cũng "rất thích"? Câu mơ hồ, lủng củng.', $d);
        $this->quiz($s, 'Câu "Những bông hoa nở rộ khoe sắc trong nắng sớm mai." — thành phần nào là chủ ngữ?', ['Những bông hoa', 'nở rộ khoe sắc', 'trong nắng sớm mai', 'Những'], 0, 'Chủ ngữ là cụm danh từ "những bông hoa"; còn lại là vị ngữ và trạng ngữ.', $d);
        $this->quiz($s, 'Nguyên nhân nào thường dẫn đến lỗi "câu lủng củng"?', ['Câu quá ngắn', 'Sắp xếp từ ngữ lộn xộn, thiếu mạch lạc', 'Dùng từ Hán Việt', 'Viết chữ đẹp'], 1, 'Lủng củng: ý lộn xộn, từ ngữ sắp xếp thiếu logic, người đọc khó theo dõi.', $d);
        $this->quiz($s, 'Câu "Tôi đi học, mẹ đi chợ, em ở nhà." có lỗi gì?', ['Không có lỗi', 'Thiếu quan hệ từ nối, câu ghép đẳng lập cần dấu phẩy đã đúng', 'Sai chính tả', 'Thừa chủ ngữ'], 0, 'Câu ghép đẳng lập ba vế ngăn cách bằng dấu phẩy — viết đúng, không lỗi.', $d);

        $this->matching($s, 'Hãy nối mỗi lỗi đặt câu với ví dụ.', [['Thiếu chủ ngữ', '"Qua bài thơ, cho em hiểu..."'], ['Thiếu vị ngữ', '"Những học sinh chăm chỉ."'], ['Lủng củng', '"Em thích đọc sách và mẹ cũng vậy."'], ['Sai trật tự', '"Đẹp quê hương em lắm."']], 'Bốn lỗi đặt câu kinh điển trong bài thi THPT.', $d);
        $this->matching($s, 'Hãy nối mỗi câu sai với cách chữa đúng.', [['"Qua bài thơ, cho em hiểu..."', '"Qua bài thơ, em hiểu thêm..."'], ['"Những học sinh chăm chỉ."', '"Những học sinh chăm chỉ được khen."'], ['"Đẹp quê hương em lắm."', '"Quê hương em đẹp lắm."'], ['"Mẹ, đi chợ."', '"Mẹ đi chợ."']], 'Chữa lỗi: thêm thành phần thiếu, sắp xếp lại trật tự, bỏ dấu sai.', $d);
        $this->matching($s, 'Hãy nối mỗi câu với thành phần còn thiếu.', [['"Qua truyện, thấy rõ..."', 'Thiếu chủ ngữ'], ['"Các bạn học sinh giỏi."', 'Thiếu vị ngữ'], ['"Đang mưa." (đầy đủ?)', 'Không thiếu (câu đặc biệt)'], ['"Vì trời mưa."', 'Thiếu vế chính']], '"Vì trời mưa" là vế phụ đứng một mình — thiếu vế chính nên câu chưa hoàn chỉnh.', $d);
        $this->matching($s, 'Hãy nối mỗi cách sắp xếp với đánh giá.', [['Chủ ngữ – vị ngữ', 'Trật tự tự nhiên'], ['Vị ngữ – chủ ngữ (đảo)', 'Nhấn mạnh, có chủ ý'], ['Trạng ngữ đầu câu', 'Tự nhiên, phổ biến'], ['Tách chủ – vị bằng phẩy', 'Sai quy tắc']], 'Trật tự tự nhiên: trạng ngữ – chủ ngữ – vị ngữ.', $d);
        $this->matching($s, 'Nối mỗi dấu phẩy với cách dùng đúng/sai.', [['Ngăn cách trạng ngữ', 'Đúng'], ['Ngăn cách liệt kê', 'Đúng'], ['Tách chủ ngữ – vị ngữ', 'Sai'], ['Ngăn cách vế ghép', 'Đúng']], 'Lỗi "tách chủ – vị bằng dấu phẩy" rất phổ biến trong bài thi.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (lỗi cấu trúc).', [['"Em đi học."', 'ĐÚNG'], ['"Qua bài thơ, cho em hiểu."', 'SAI (thiếu chủ ngữ)'], ['"Trời mưa to."', 'ĐÚNG'], ['"Những học sinh chăm chỉ."', 'SAI (thiếu vị ngữ)']], 'Câu đúng phải có đủ nòng cốt chủ – vị (trừ câu đặc biệt).', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm THIẾU CHỦ NGỮ hoặc THIẾU VỊ NGỮ.', [['"Qua phim, thấy rõ tình mẹ."', 'THIẾU CHỦ NGỮ'], ['"Những chiến sĩ dũng cảm."', 'THIẾU VỊ NGỮ'], ['"Nhờ bạn, tôi tiến bộ."', 'THIẾU CHỦ NGỮ'], ['"Cánh đồng lúa chín."', 'THIẾU VỊ NGỮ']], '"Cánh đồng lúa chín" mới chỉ là cụm danh từ, chưa thành câu.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi cách đặt dấu phẩy vào nhóm ĐÚNG hoặc SAI.', [['"Sáng nay, em đi học."', 'ĐÚNG'], ['"Em, đi học."', 'SAI'], ['"Mưa tạnh, nắng lên."', 'ĐÚNG'], ['"Mẹ nấu, cơm ngon."', 'SAI']], 'Dấu phẩy không được tách chủ ngữ với vị ngữ.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm MẠCH LẠC hoặc LỦNG CỦNG.', [['"Em thích đọc sách. Mẹ cũng thích."', 'MẠCH LẠC'], ['"Em thích đọc sách và mẹ em cũng vậy."', 'LỦNG CỦNG'], ['"Trời mưa. Đường trơn."', 'MẠCH LẠC'], ['"Trời mưa và đường nó cũng thế."', 'LỦNG CỦNG']], 'Câu lủng củng: ý lộn xộn, đại từ mơ hồ ("cũng vậy", "cũng thế").', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU ĐƠN ĐẦY ĐỦ hoặc CÂU THIẾU THÀNH PHẦN.', [['"Chim hót líu lo."', 'CÂU ĐƠN ĐẦY ĐỦ'], ['"Qua bài hát, thấy hay."', 'CÂU THIẾU THÀNH PHẦN'], ['"Hoa nở rộ."', 'CÂU ĐƠN ĐẦY ĐỦ'], ['"Những đám mây trắng."', 'CÂU THIẾU THÀNH PHẦN']], 'Kiểm tra nòng cốt chủ – vị trước khi kết luận câu đúng hay sai.', $d);

        $this->fill($s, 'Câu "Qua bài thơ, cho em hiểu thêm." mắc lỗi thiếu chủ ___.', [[0, 'ngữ']], 'Chữa: "Qua bài thơ, em hiểu thêm về quê hương." — thêm chủ ngữ "em".', $d);
        $this->fill($s, 'Câu "Những học sinh chăm chỉ." mắc lỗi thiếu vị ___.', [[0, 'ngữ']], 'Chữa: "Những học sinh chăm chỉ được nhà trường khen thưởng."', $d);
        $this->fill($s, 'Trật tự tự nhiên của câu: (trạng ngữ) – chủ ngữ – vị ___.', [[0, 'ngữ']], 'Ví dụ: "Sáng nay (trạng), em (chủ) đi học (vị)."', $d);
        $this->fill($s, 'Dấu phẩy dùng để tách trạng ngữ với nòng cốt câu, không tách chủ ngữ với vị ___.', [[0, 'ngữ']], 'Lỗi kinh điển: "Em, đi học." — sai quy tắc dấu câu.', $d);
        $this->fill($s, 'Câu lủng củng thường do sắp xếp từ ngữ lộn xộn, thiếu mạch ___.', [[0, 'lạc']], 'Viết xong nên đọc lại để kiểm tra mạch lạc của câu văn.', $d);
    }

    /* ============ tieng-viet-thpt-12-lien-ket-van-ban-1 (Tiếng Việt 12, trung_binh) ============ */
    private function seedTiengVietThpt12LienKet1(): void
    {
        $s = 'tieng-viet-thpt-12-lien-ket-van-ban-1'; $d = 'trung_binh';
        $this->quiz($s, 'Phép liên kết bằng cách lặp lại từ ngữ ở các câu gọi là phép gì?', ['Phép thế', 'Phép lặp', 'Phép nối', 'Phép tương phản'], 1, 'Lặp lại từ ngữ (ví dụ lặp "Trường Sa") ở các câu tạo liên kết chặt chẽ.', $d);
        $this->quiz($s, 'Trong đoạn: "Lan rất chăm. Cô ấy luôn dậy sớm." — "cô ấy" thực hiện phép liên kết nào?', ['Phép lặp', 'Phép thế', 'Phép nối', 'Phép tỉnh lược'], 1, '"Cô ấy" thay thế cho "Lan" ở câu trước → phép thế.', $d);
        $this->quiz($s, 'Để thực hiện phép nối, người viết thường sử dụng yếu tố nào?', ['Từ ngữ lặp lại', 'Quan hệ từ, từ nối (và, nhưng, vì vậy)', 'Từ thay thế', 'Dấu câu'], 1, 'Phép nối dùng quan hệ từ, cặp từ hô ứng để nối các câu, đoạn.', $d);
        $this->quiz($s, 'Các cặp từ trái nghĩa như "vui – buồn", "nhanh – chậm" tạo liên kết bằng phép nào?', ['Phép lặp', 'Phép thế', 'Phép tương liên (đồng nghĩa, trái nghĩa)', 'Phép nối'], 2, 'Dùng cặp từ trái nghĩa, đồng nghĩa ở các câu tạo liên kết tương liên.', $d);
        $this->quiz($s, 'Đoạn văn thiếu liên kết sẽ như thế nào?', ['Hay hơn', 'Rời rạc, khó hiểu', 'Ngắn gọn hơn', 'Sinh động hơn'], 1, 'Thiếu liên kết, các câu "đứng riêng lẻ", người đọc khó nắm mạch ý.', $d);

        $this->matching($s, 'Hãy nối mỗi phép liên kết với ví dụ.', [['Phép lặp', '"Trường Sa... Trường Sa..."'], ['Phép thế', '"Lan... cô ấy..."'], ['Phép nối', '"... Vì vậy, ..."'], ['Phép tương liên', '"vui... buồn..."']], 'Bốn phép liên kết câu cơ bản: lặp – thế – nối – tương liên.', $d);
        $this->matching($s, 'Hãy nối mỗi từ thay thế với từ được thay.', [['cô ấy', 'Lan'], ['việc này', 'sự việc nêu trước'], ['như vậy', 'cách làm nêu trước'], ['đó', 'sự vật nêu trước']], 'Từ thay thế giúp tránh lặp từ, câu văn gọn, mạch lạc.', $d);
        $this->matching($s, 'Hãy nối mỗi từ nối với quan hệ ý nghĩa.', [['vì vậy', 'Kết quả'], ['nhưng', 'Tương phản'], ['hơn nữa', 'Bổ sung'], ['tuy nhiên', 'Nhượng bộ']], 'Từ nối vừa liên kết vừa thể hiện quan hệ ý nghĩa giữa các câu.', $d);
        $this->matching($s, 'Nối mỗi cặp từ với phép liên kết tạo ra.', [['vui – buồn', 'Tương liên (trái nghĩa)'], ['đẹp – xinh', 'Tương liên (đồng nghĩa)'], ['nhanh – chậm', 'Tương liên (trái nghĩa)'], ['to – lớn', 'Tương liên (đồng nghĩa)']], 'Cặp từ đồng/trái nghĩa ở các câu tạo liên kết tương liên.', $d);
        $this->matching($s, 'Nối mỗi đoạn với phép liên kết nổi bật.', [['Lặp tên "Bác"', 'Phép lặp'], ['"Ông... Người..."', 'Phép thế'], ['"Tuy nhiên, ..." ', 'Phép nối'], ['"Yêu... ghét..."', 'Phép tương liên']], 'Nhận diện phép liên kết qua dấu hiệu từ ngữ trong đoạn.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi cặp câu vào nhóm CÓ PHÉP LẶP hoặc KHÔNG.', [['"Lan chăm. Lan giỏi."', 'CÓ PHÉP LẶP'], ['"Lan chăm. Cô ấy giỏi."', 'KHÔNG (phép thế)'], ['"Biển đẹp. Biển rộng."', 'CÓ PHÉP LẶP'], ['"Trời nắng. Mưa to."', 'KHÔNG']], 'Lặp: dùng lại đúng từ ngữ. Thế: dùng từ khác thay thế.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ vào nhóm TỪ THAY THẾ hoặc KHÔNG PHẢI.', [['cô ấy', 'TỪ THAY THẾ'], ['Lan', 'KHÔNG PHẢI'], ['việc này', 'TỪ THAY THẾ'], ['bàn', 'KHÔNG PHẢI'], ['đó', 'TỪ THAY THẾ'], ['sách', 'KHÔNG PHẢI']], 'Từ thay thế: ấy, đó, này, kia, việc này...', $d);
        $this->sortQ($s, 'Hãy kéo mỗi từ nối vào nhóm QUAN HỆ NGUYÊN NHÂN hoặc TƯƠNG PHẢN.', [['vì vậy', 'QUAN HỆ NGUYÊN NHÂN'], ['nhưng', 'QUAN HỆ TƯƠNG PHẢN'], ['do đó', 'QUAN HỆ NGUYÊN NHÂN'], ['tuy nhiên', 'QUAN HỆ TƯƠNG PHẢN']], 'Từ nối thể hiện quan hệ ý nghĩa giữa các câu, đoạn.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi đoạn vào nhóm LIÊN KẾT TỐT hoặc RỜI RẠC.', [['Các câu có từ nối, lặp, thế', 'LIÊN KẾT TỐT'], ['Các câu đứng riêng lẻ', 'RỜI RẠC'], ['Dùng "cô ấy" thay "Lan"', 'LIÊN KẾT TỐT'], ['Câu trước câu sau không liên quan', 'RỜI RẠC']], 'Liên kết tốt: câu sau "bám" câu trước bằng phép liên kết.', $d);
        $this->sortQ($s, 'Kéo mỗi phép vào nhóm LIÊN KẾT HÌNH THỨC hoặc LIÊN KẾT Ý NGHĨA.', [['Phép lặp', 'LIÊN KẾT HÌNH THỨC'], ['Phép tương liên', 'LIÊN KẾT Ý NGHĨA'], ['Phép thế', 'LIÊN KẾT HÌNH THỨC'], ['Phép nối', 'LIÊN KẾT HÌNH THỨC']], 'Hình thức: lặp, thế, nối (từ ngữ). Ý nghĩa: tương liên (quan hệ nghĩa).', $d);

        $this->fill($s, 'Khi các câu dùng chung một từ ngữ được lặp lại, đó là phép ___.', [[0, 'lặp']], 'Phép lặp: đơn giản mà hiệu quả, nhấn mạnh đối tượng.', $d);
        $this->fill($s, 'Dùng các từ như "ấy", "đó", "việc này" để thay thế gọi là phép ___.', [[0, 'thế']], 'Phép thế tránh lặp từ, làm câu văn gọn gàng.', $d);
        $this->fill($s, 'Các quan hệ từ, từ nối như "và", "nhưng", "vì vậy" tạo nên phép ___.', [[0, 'nối']], 'Phép nối thể hiện rõ quan hệ ý nghĩa giữa các câu.', $d);
        $this->fill($s, 'Dùng cặp từ đồng nghĩa hoặc trái nghĩa ở các câu tạo liên kết bằng phép tương ___.', [[0, 'liên']], 'Phép tương liên: "vui – buồn", "đẹp – xinh".', $d);
        $this->fill($s, 'Bốn phép liên kết câu: lặp, thế, nối và tương ___.', [[0, 'liên']], 'Nắm vững bốn phép này là nắm được "chất keo" của văn bản.', $d);
    }

    /* ============ tieng-viet-thpt-12-lien-ket-van-ban-2 (Tiếng Việt 12, trung_binh) ============ */
    private function seedTiengVietThpt12LienKet2(): void
    {
        $s = 'tieng-viet-thpt-12-lien-ket-van-ban-2'; $d = 'trung_binh';
        $this->quiz($s, 'Một đoạn văn được đánh giá là mạch lạc khi đáp ứng điều kiện nào?', ['Thật dài', 'Các câu liên kết chặt chẽ, cùng hướng về chủ đề', 'Nhiều từ khó', 'Viết bằng thơ'], 1, 'Mạch lạc = liên kết chặt + thống nhất chủ đề.', $d);
        $this->quiz($s, 'Trong đoạn văn, câu chủ đề thường được đặt ở đâu?', ['Giữa đoạn', 'Đầu hoặc cuối đoạn', 'Không có vị trí', 'Ở đoạn khác'], 1, 'Diễn dịch: chủ đề đầu đoạn. Quy nạp: chủ đề cuối đoạn.', $d);
        $this->quiz($s, 'Đâu là trình tự triển khai hợp lí cho một đoạn văn?', ['Lộn xộn tùy ý', 'Theo mạch logic: diễn dịch, quy nạp, song hành...', 'Viết câu dài nhất trước', 'Ngẫu nhiên'], 1, 'Đoạn văn cần trình tự triển khai rõ ràng, hợp logic.', $d);
        $this->quiz($s, 'Nhận biết đoạn văn thiếu mạch lạc qua dấu hiệu nào?', ['Câu văn hay', 'Các câu rời rạc, lạc chủ đề', 'Đoạn văn ngắn', 'Dùng nhiều từ láy'], 1, 'Lạc chủ đề, câu trước câu sau không liên quan → thiếu mạch lạc.', $d);
        $this->quiz($s, 'Cách triển khai diễn dịch khác quy nạp ở điểm nào?', ['Diễn dịch: chủ đề đầu đoạn; quy nạp: chủ đề cuối đoạn', 'Diễn dịch dài hơn', 'Quy nạp không có chủ đề', 'Hai cách giống nhau'], 0, 'Diễn dịch đi từ khái quát đến cụ thể; quy nạp từ cụ thể đến khái quát.', $d);

        $this->matching($s, 'Hãy nối mỗi cách triển khai với mô tả.', [['Diễn dịch', 'Chủ đề đầu đoạn'], ['Quy nạp', 'Chủ đề cuối đoạn'], ['Song hành', 'Các câu ngang hàng'], ['Móc xích', 'Câu sau nối câu trước']], 'Bốn cách triển khai đoạn văn cơ bản.', $d);
        $this->matching($s, 'Hãy nối mỗi trình tự với ví dụ.', [['Thời gian', '"Sáng... trưa... chiều..."'], ['Không gian', '"Xa... gần..."'], ['Tăng tiến', '"Từ thấp đến cao..."'], ['Đối lập', '"Một mặt... mặt khác..."']], 'Trình tự triển khai giúp đoạn văn có "đường đi" rõ ràng.', $d);
        $this->matching($s, 'Nối mỗi đoạn với cách triển khai của nó.', [['Chủ đề đầu, triển khai sau', 'Diễn dịch'], ['Triển khai rồi chốt chủ đề', 'Quy nạp'], ['Các ý ngang nhau', 'Song hành'], ['Ý sau tiếp ý trước', 'Móc xích']], 'Nhận diện cách triển khai qua vị trí câu chủ đề.', $d);
        $this->matching($s, 'Hãy nối mỗi lỗi với cách khắc phục.', [['Lạc chủ đề', 'Bám sát câu chủ đề'], ['Thiếu liên kết', 'Dùng phép liên kết'], ['Lộn xộn', 'Sắp xếp theo trình tự'], ['Câu chủ đề mờ', 'Viết rõ ý chính']], 'Bốn lỗi mạch lạc và cách chữa tương ứng.', $d);
        $this->matching($s, 'Nối mỗi câu với vai trò trong đoạn.', [['Câu nêu ý chính', 'Câu chủ đề'], ['Câu làm rõ ý chính', 'Câu triển khai'], ['Câu chuyển ý', 'Câu nối đoạn'], ['Câu ví dụ', 'Câu minh họa']], 'Mỗi câu trong đoạn văn có một "nhiệm vụ" riêng.', $d);

        $this->sortQ($s, 'Hãy kéo mỗi đặc điểm vào nhóm ĐOẠN VĂN MẠCH LẠC hoặc KHÔNG MẠCH LẠC.', [['Thống nhất chủ đề', 'ĐOẠN VĂN MẠCH LẠC'], ['Lạc chủ đề', 'KHÔNG MẠCH LẠC'], ['Liên kết chặt chẽ', 'ĐOẠN VĂN MẠCH LẠC'], ['Câu rời rạc', 'KHÔNG MẠCH LẠC']], 'Mạch lạc = một chủ đề + liên kết chặt.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi câu vào nhóm CÂU CHỦ ĐỀ hoặc CÂU TRIỂN KHAI.', [['"Đọc sách rất bổ ích."', 'CÂU CHỦ ĐỀ'], ['"Sách mở ra tri thức."', 'CÂU TRIỂN KHAI'], ['"Tập thể dục tốt cho sức khỏe."', 'CÂU CHỦ ĐỀ'], ['"Chạy bộ giúp tim khỏe."', 'CÂU TRIỂN KHAI']], 'Câu chủ đề nêu ý chính; câu triển khai làm rõ ý chính.', $d);
        $this->sortQ($s, 'Kéo mỗi đoạn vào nhóm TRIỂN KHAI DIỄN DỊCH hoặc QUY NẠP.', [['Chủ đề ở đầu đoạn', 'TRIỂN KHAI DIỄN DỊCH'], ['Chủ đề ở cuối đoạn', 'TRIỂN KHAI QUY NẠP']], 'Vị trí câu chủ đề quyết định cách triển khai.', $d);
        $this->sortQ($s, 'Hãy kéo mỗi ý vào nhóm THUỘC CHỦ ĐỀ "lợi ích đọc sách" hoặc LẠC CHỦ ĐỀ.', [['"Sách mở mang tri thức."', 'THUỘC CHỦ ĐỀ'], ['"Bóng đá rất hấp dẫn."', 'LẠC CHỦ ĐỀ'], ['"Đọc sách rèn tư duy."', 'THUỘC CHỦ ĐỀ'], ['"Game online gây nghiện."', 'LẠC CHỦ ĐỀ']], 'Mọi câu trong đoạn phải hướng về chủ đề chung.', $d);
        $this->sortQ($s, 'Kéo mỗi câu vào nhóm CÂU NỐI ĐOẠN TỐT hoặc CHƯA TỐT.', [['"Bên cạnh đó, ..." ', 'CÂU NỐI ĐOẠN TỐT'], ['"Sang chuyện khác."', 'CHƯA TỐT'], ['"Không chỉ vậy, ..." ', 'CÂU NỐI ĐOẠN TỐT'], ['"Hết đoạn."', 'CHƯA TỐT']], 'Câu nối đoạn vừa kết ý cũ vừa mở ý mới tự nhiên.', $d);

        $this->fill($s, 'Câu nêu ý chính của đoạn văn gọi là câu chủ ___.', [[0, 'đề']], 'Câu chủ đề là "linh hồn" của đoạn văn.', $d);
        $this->fill($s, 'Đoạn văn có các câu liên kết chặt chẽ, đúng chủ đề gọi là đoạn văn mạch ___.', [[0, 'lạc']], 'Mạch lạc là yêu cầu hàng đầu của đoạn văn, bài văn.', $d);
        $this->fill($s, 'Cách triển khai câu chủ đề đứng đầu đoạn gọi là diễn ___.', [[0, 'dịch']], 'Diễn dịch: từ khái quát đến cụ thể.', $d);
        $this->fill($s, 'Cách triển khai câu chủ đề đứng cuối đoạn gọi là quy ___.', [[0, 'nạp']], 'Quy nạp: từ cụ thể đến khái quát.', $d);
        $this->fill($s, 'Muốn đoạn văn mạch lạc, các câu phải thống nhất chủ đề và liên ___ chặt chẽ.', [[0, 'kết']], 'Chủ đề + liên kết = mạch lạc.', $d);
    }
}










