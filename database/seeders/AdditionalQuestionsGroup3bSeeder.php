<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdditionalQuestionsGroup3bSeeder extends Seeder
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
        $this->seedCnQuyTacAnToanDien();
        $this->seedCnXuLySuCoDien();
        $this->seedCnGoKimLoaiNhua();
        $this->seedCnBuaKimTuaVitCua();
        $this->seedCnCacBuocTrongCay();
        $this->seedCnTuoiNuocBonPhan();
        $this->seedCnAnToanDienLop61();
        $this->seedCnAnToanDienLop62();
        $this->seedCnAnToanDienLop71();
        $this->seedCnAnToanDienLop72();
        $this->seedCnVatLieuDungCuLop71();
        $this->seedCnVatLieuDungCuLop72();
        $this->seedCnVatLieuDungCuLop81();
        $this->seedCnVatLieuDungCuLop82();
        $this->seedCnTrongTrotLop81();
        $this->seedCnTrongTrotLop82();
        $this->seedCnTrongTrotLop91();
        $this->seedCnTrongTrotLop92();
        $this->seedCongNgheThpt10Lop101();
        $this->seedCongNgheThpt10Lop102();
        $this->seedCongNgheThpt10Lop103();
        $this->seedCongNgheThpt10Lop104();
        $this->seedCongNgheThpt11Lop111();
        $this->seedCongNgheThpt11Lop112();
        $this->seedCongNgheThpt11Lop113();
        $this->seedCongNgheThpt11Lop114();
        $this->seedCongNgheThpt12Lop121();
        $this->seedCongNgheThpt12Lop122();
        $this->seedCongNgheThpt12Lop123();
        $this->seedCongNgheThpt12Lop124();
        $this->seedTopUps();
    }

    // ===== Top-up: bu them cho cac nhom chua du 8 cau (prompt trung voi brief nen bi skip) =====
    private function seedTopUps(): void
    {
        $this->sortQ('cong-nghe-thpt-11-lop-11-2', 'Xếp mỗi ý vào nhóm HÌNH CHIẾU VUÔNG GÓC hoặc HÌNH CHIẾU TRỤC ĐO.', [['Hình chiếu đứng', 'HÌNH CHIẾU VUÔNG GÓC'], ['Hình chiếu bằng', 'HÌNH CHIẾU VUÔNG GÓC'], ['Trục đo vuông góc đều', 'HÌNH CHIẾU TRỤC ĐO'], ['Trục đo xiên góc cân', 'HÌNH CHIẾU TRỤC ĐO']], 'Hình chiếu đứng, bằng, cạnh là vuông góc; các loại trục đo thuộc nhóm trục đo.', 'kho');
        $this->matching('cong-nghe-thpt-11-lop-11-3', 'Ghép mỗi con số, kí hiệu trên bản vẽ với ý nghĩa của nó.', [['Ø25', 'Đường kính 25mm'], ['R10', 'Bán kính 10mm'], ['□30', 'Tiết diện vuông cạnh 30mm'], ['2:1', 'Tỉ lệ phóng to gấp đôi']], 'Đọc nhanh kí hiệu giúp hiểu bản vẽ chi tiết chính xác.', 'trung_binh');
        $this->sortQ('cong-nghe-thpt-11-lop-11-3', 'Kéo mỗi đường nét vào nhóm NÉT ĐẬM hoặc NÉT MẢNH.', [['Nét liền đậm', 'NÉT ĐẬM'], ['Nét đứt mảnh', 'NÉT MẢNH'], ['Nét chấm gạch mảnh', 'NÉT MẢNH'], ['Nét liền mảnh', 'NÉT MẢNH']], 'Chỉ nét liền đậm vẽ cạnh thấy; các nét còn lại đều là nét mảnh.', 'trung_binh');
        $this->matching('cong-nghe-thpt-11-lop-11-4', 'Nối mỗi thông tin với nơi đọc được nó trên bản vẽ lắp.', [['Tên sản phẩm', 'Khung tên'], ['Số lượng chi tiết', 'Bảng kê'], ['Cách lắp ráp', 'Hình biểu diễn'], ['Vật liệu chi tiết', 'Bảng kê']], 'Khung tên, bảng kê và hình biểu diễn mỗi nơi cho một loại thông tin.', 'kho');
        $this->sortQ('cong-nghe-thpt-12-lop-12-1', 'Kéo mỗi phát minh vào nhóm THẾ KỶ 18-19 hoặc THẾ KỶ 20-21.', [['Máy hơi nước', 'THẾ KỶ 18-19'], ['Bóng đèn điện', 'THẾ KỶ 18-19'], ['Máy tính điện tử', 'THẾ KỶ 20-21'], ['Điện thoại thông minh', 'THẾ KỶ 20-21']], 'Máy hơi nước, điện thuộc thế kỷ 18-19; máy tính, smartphone thuộc thế kỷ 20-21.', 'trung_binh');
        $this->sortQ('cong-nghe-thpt-12-lop-12-2', 'Kéo mỗi ví dụ vào nhóm TỰ ĐỘNG HÓA TRONG NHÀ hoặc TRONG NHÀ MÁY.', [['Máy giặt', 'TỰ ĐỘNG HÓA TRONG NHÀ'], ['Robot hút bụi', 'TỰ ĐỘNG HÓA TRONG NHÀ'], ['Robot hàn', 'TỰ ĐỘNG HÓA TRONG NHÀ MÁY'], ['Dây chuyền đóng gói', 'TỰ ĐỘNG HÓA TRONG NHÀ MÁY']], 'Tự động hóa có mặt cả trong gia đình và trong nhà máy sản xuất.', 'kho');
    }

    // ===== 1. cn-quy-tac-an-toan-dien (Lop 6, de) =====
    private function seedCnQuyTacAnToanDien(): void
    {
        $L = 'cn-quy-tac-an-toan-dien'; $D = 'de';
        $this->quiz($L, 'Khi thấy ổ điện phát ra tia lửa điện bất thường, em nên làm gì?', ['Báo ngay cho người lớn và tránh xa', 'Tò mò chạm tay vào xem thử', 'Tự tháo ổ điện ra kiểm tra', 'Dùng nước để dập tia lửa'], 0, 'Tia lửa điện là dấu hiệu chập điện rất nguy hiểm. Em phải tránh xa và báo ngay cho người lớn xử lý.', $D);
        $this->quiz($L, 'Vì sao không được cắm quá nhiều thiết bị vào cùng một ổ điện?', ['Vì ổ điện sẽ bị quá tải, dễ gây cháy', 'Vì các thiết bị sẽ chạy nhanh hơn', 'Vì tiền điện sẽ rẻ hơn', 'Vì ổ điện sẽ bền hơn'], 0, 'Cắm quá nhiều thiết bị khiến ổ điện quá tải, dây nóng lên và có thể gây cháy.', $D);
        $this->quiz($L, 'Khi rút phích cắm điện, cách làm nào là đúng?', ['Cầm vào phần nhựa của phích rồi rút thẳng', 'Nắm dây điện giật mạnh ra', 'Dùng vật kim loại nạy phích ra', 'Nhờ bạn nhỏ kéo giúp một đầu'], 0, 'Rút phích phải cầm vào phần nhựa cách điện và rút thẳng, không được giật mạnh dây điện.', $D);
        $this->quiz($L, 'Dấu hiệu nào cho thấy đồ điện trong nhà đang bị rò điện?', ['Chạm vào vỏ thấy tê tê và vỏ nóng bất thường', 'Đồ điện chạy êm và mát', 'Đèn báo sáng bình thường', 'Phích cắm chắc chắn'], 0, 'Vỏ thiết bị nóng bất thường hoặc chạm vào thấy tê là dấu hiệu rò điện, phải ngắt điện và báo người lớn.', $D);
        $this->quiz($L, 'Khi trời có sấm sét, vì sao không nên đứng trú dưới cây to một mình?', ['Vì cây cao dễ bị sét đánh trúng', 'Vì cây sẽ che hết mưa', 'Vì dưới cây thường có rắn', 'Vì cây to hút hết không khí'], 0, 'Cây cao là điểm dễ bị sét đánh nhất khi có giông. Khi có sấm sét nên vào nhà trú ẩn.', $D);
        $this->matching($L, 'Nối mỗi vật dụng với khả năng gây nguy hiểm về điện.', [['Dây điện bị hở vỏ', 'Rất nguy hiểm'], ['Ổ điện có nắp che', 'An toàn hơn'], ['Tay ướt chạm công tắc', 'Rất nguy hiểm'], ['Gậy gỗ khô', 'Cách điện tốt']], 'Vật dẫn điện bị hở và tay ướt rất nguy hiểm, còn vật cách điện khô ráo thì an toàn hơn.', $D);
        $this->matching($L, 'Nối mỗi hành động với kết quả có thể xảy ra.', [['Chạm tay ướt vào ổ điện', 'Bị điện giật'], ['Rút phích khi không dùng', 'Tiết kiệm và an toàn'], ['Đứng xa dây điện đứt', 'An toàn'], ['Chọc vật kim loại vào ổ điện', 'Bị điện giật']], 'Tay ướt và vật kim loại dẫn điện gây giật, còn tránh xa nguồn nguy hiểm giúp an toàn.', $D);
        $this->matching($L, 'Nối mỗi bộ phận điện trong nhà với nhiệm vụ của nó.', [['Cầu dao', 'Ngắt điện khi sửa chữa'], ['Cầu chì', 'Tự đứt khi quá tải'], ['Ổ cắm', 'Nơi lấy điện dùng'], ['Dây dẫn', 'Truyền điện đi']], 'Mỗi bộ phận trong hệ thống điện gia đình đều có nhiệm vụ riêng để điện được dùng an toàn.', $D);
        $this->matching($L, 'Nối mỗi câu nói với việc nên làm hay không nên làm.', [['Lau khô tay trước khi bật công tắc', 'Nên làm'], ['Chơi gần trạm biến áp', 'Không nên làm'], ['Báo người lớn khi thấy dây hở', 'Nên làm'], ['Kéo mạnh dây khi rút phích', 'Không nên làm']], 'Giữ tay khô, tránh xa nơi nguy hiểm và báo người lớn là những việc nên làm khi dùng điện.', $D);
        $this->matching($L, 'Nối mỗi vật liệu với vai trò của nó trong an toàn điện.', [['Nhựa', 'Vỏ bọc cách điện'], ['Đồng', 'Lõi dây dẫn điện'], ['Cao su', 'Găng tay cách điện'], ['Sắt', 'Dẫn điện, cần cẩn thận']], 'Nhựa và cao su cách điện nên dùng làm vỏ bọc, còn đồng và sắt dẫn điện nên cẩn thận khi chạm vào.', $D);
        $this->sortQ($L, 'Kéo mỗi hành động vào nhóm ĐÚNG hoặc SAI khi dùng điện.', [['Lau khô tay trước khi cắm điện', 'ĐÚNG'], ['Chạm vào ổ điện khi tay ướt', 'SAI'], ['Báo người lớn khi thấy dây hở', 'ĐÚNG'], ['Thả diều gần đường dây điện', 'SAI'], ['Rút phích bằng cách cầm phần nhựa', 'ĐÚNG'], ['Kéo mạnh dây điện', 'SAI']], 'Tay khô, báo người lớn và cầm đúng chỗ là đúng; tay ướt, thả diều gần dây điện và giật dây là sai.', $D);
        $this->sortQ($L, 'Kéo mỗi đồ vật vào nhóm DẪN ĐIỆN hoặc CÁCH ĐIỆN.', [['Dây đồng', 'DẪN ĐIỆN'], ['Thanh sắt', 'DẪN ĐIỆN'], ['Găng tay cao su', 'CÁCH ĐIỆN'], ['Vỏ nhựa', 'CÁCH ĐIỆN'], ['Gậy gỗ khô', 'CÁCH ĐIỆN'], ['Nước', 'DẪN ĐIỆN']], 'Kim loại và nước dẫn điện; cao su, nhựa và gỗ khô cách điện.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm LÀM NGAY hoặc TUYỆT ĐỐI KHÔNG khi thấy sự cố điện.', [['Ngắt cầu dao', 'LÀM NGAY'], ['Báo người lớn', 'LÀM NGAY'], ['Dùng tay kéo nạn nhân bị giật', 'TUYỆT ĐỐI KHÔNG'], ['Đứng xa khu vực nguy hiểm', 'LÀM NGAY'], ['Tưới nước vào ổ điện đang cháy', 'TUYỆT ĐỐI KHÔNG'], ['Chạm vào dây điện đứt', 'TUYỆT ĐỐI KHÔNG']], 'Khi có sự cố điện phải ngắt điện, báo người lớn và tránh xa; tuyệt đối không chạm tay trần hay dùng nước.', $D);
        $this->sortQ($L, 'Kéo mỗi đồ điện vào nhóm NÊN ĐỂ XA NƯỚC hoặc DÙNG ĐƯỢC GẦN NƯỚC.', [['Ấm đun điện', 'NÊN ĐỂ XA NƯỚC'], ['Máy sấy tóc', 'NÊN ĐỂ XA NƯỚC'], ['Bàn là điện', 'NÊN ĐỂ XA NƯỚC'], ['Máy bơm nước', 'DÙNG ĐƯỢC GẦN NƯỚC']], 'Đồ điện thông thường phải để xa nước, chỉ thiết bị chuyên dụng chống nước như máy bơm mới dùng gần nước được.', $D);
        $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm GIÚP AN TOÀN ĐIỆN hoặc GÂY MẤT AN TOÀN.', [['Kiểm tra dây điện định kỳ', 'GIÚP AN TOÀN ĐIỆN'], ['Dùng đồ điện đúng công suất', 'GIÚP AN TOÀN ĐIỆN'], ['Tự ý sửa đồ điện khi chưa biết', 'GÂY MẤT AN TOÀN'], ['Để đồ điện gần bồn nước', 'GÂY MẤT AN TOÀN']], 'Kiểm tra định kỳ và dùng đúng cách giúp an toàn; tự sửa bừa và để đồ điện gần nước gây mất an toàn.', $D);
        $this->fill($L, 'Rút phích điện phải cầm vào phần ___ nhựa, không được kéo mạnh dây điện.', [[0, 'phích']], 'Cầm vào phần phích nhựa cách điện rồi rút thẳng, kéo dây sẽ làm đứt lõi và hở điện.', $D);
        $this->fill($L, 'Ổ điện bị ___ tải khi cắm quá nhiều thiết bị cùng một lúc.', [[0, 'quá']], 'Quá tải làm dây điện nóng lên, dễ gây chập cháy nên không cắm quá nhiều thiết bị vào một ổ.', $D);
        $this->fill($L, 'Cầu ___ dùng để ngắt điện trước khi sửa chữa thiết bị trong nhà.', [[0, 'dao']], 'Ngắt cầu dao là việc đầu tiên phải làm trước khi sửa chữa bất kỳ thiết bị điện nào.', $D);
        $this->fill($L, 'Nước là chất ___ điện nên tay ướt rất nguy hiểm khi chạm vào đồ điện.', [[0, 'dẫn']], 'Nước dẫn điện tốt nên tay ướt chạm vào đồ điện dễ bị điện giật.', $D);
        $this->fill($L, 'Thấy dây điện đứt rơi xuống đường, em phải không ___ lại gần và báo ngay cho người lớn.', [[0, 'đến']], 'Dây điện đứt vẫn có thể còn điện, tuyệt đối không đến gần mà phải báo ngay cho người lớn.', $D);
    }

    // ===== 2. cn-xu-ly-su-co-dien (Lop 6, trung_binh) =====
    private function seedCnXuLySuCoDien(): void
    {
        $L = 'cn-xu-ly-su-co-dien'; $D = 'trung_binh';
        $this->quiz($L, 'Sau khi tách được người bị điện giật ra khỏi nguồn điện, việc tiếp theo là gì?', ['Kiểm tra hơi thở và gọi cấp cứu 115 ngay', 'Cho nạn nhân uống nước đường', 'Lay mạnh cho nạn nhân tỉnh dậy', 'Để nạn nhân nằm đó chờ tự tỉnh'], 0, 'Sau khi tách nguồn điện, phải kiểm tra hơi thở của nạn nhân và gọi ngay cấp cứu 115.', $D);
        $this->quiz($L, 'Khi bị điện giật nhẹ (thấy tê tay), em nên làm gì?', ['Rút tay ra ngay và báo cho người lớn biết', 'Tiếp tục chạm vào để kiểm tra', 'Dùng nước rửa tay rồi chạm lại', 'Không nói với ai vì sợ bị mắng'], 0, 'Bị điện giật dù nhẹ cũng phải rút tay ra ngay và báo người lớn để kiểm tra, tránh nguy hiểm lớn hơn.', $D);
        $this->quiz($L, 'Vì sao không được dùng thang kim loại khi sửa chữa điện trên cao?', ['Vì kim loại dẫn điện, rất dễ bị giật', 'Vì thang kim loại quá nặng', 'Vì thang kim loại dễ bị gỉ', 'Vì thang kim loại đắt tiền'], 0, 'Thang kim loại dẫn điện nên khi sửa điện trên cao phải dùng thang gỗ hoặc thang cách điện.', $D);
        $this->quiz($L, 'Khi dây điện đứt rơi xuống vũng nước, điều nguy hiểm nhất là gì?', ['Nước dẫn điện làm vùng nguy hiểm lan rộng', 'Nước làm dây điện bị gỉ nhanh', 'Nước làm mất điện cả khu vực', 'Nước làm dây điện nổi lên'], 0, 'Nước dẫn điện nên vùng nước quanh dây đứt đều nguy hiểm, phải đứng thật xa và báo người lớn.', $D);
        $this->quiz($L, 'Số điện thoại nào cần gọi khi có người bị tai nạn cần cấp cứu y tế?', ['115', '114', '113', '1080'], 0, '115 là số cấp cứu y tế, gọi ngay khi có người bị điện giật hoặc tai nạn cần cứu chữa.', $D);
        $this->matching($L, 'Nối mỗi bước cứu người bị điện giật với thứ tự thực hiện.', [['Bước 1', 'Ngắt nguồn điện'], ['Bước 2', 'Tách nạn nhân bằng vật cách điện'], ['Bước 3', 'Gọi cấp cứu 115'], ['Bước 4', 'Sơ cứu nạn nhân']], 'Cứu người bị điện giật phải theo đúng trình tự: ngắt điện, tách nạn nhân, gọi cấp cứu rồi sơ cứu.', $D);
        $this->matching($L, 'Nối mỗi sự cố điện với dấu hiệu nhận biết của nó.', [['Chập điện', 'Tia lửa và tiếng nổ'], ['Rò điện', 'Vỏ thiết bị nóng, tê khi chạm'], ['Quá tải', 'Dây điện nóng bất thường'], ['Đứt dây', 'Mất điện đột ngột']], 'Nhận biết dấu hiệu của từng sự cố giúp xử lý đúng cách và kịp thời.', $D);
        $this->matching($L, 'Nối mỗi số điện thoại với tình huống cần gọi.', [['114', 'Cháy do chập điện'], ['115', 'Người bị điện giật'], ['113', 'Mất an ninh trật tự'], ['Tổng đài điện lực', 'Dây điện đứt ngoài đường']], 'Nhớ đúng số điện thoại khẩn cấp cho từng tình huống giúp cứu người kịp thời.', $D);
        $this->matching($L, 'Nối mỗi vật với việc có dùng được để tách nạn nhân khỏi nguồn điện không.', [['Gậy gỗ khô', 'Dùng được'], ['Thanh sắt', 'Không dùng được'], ['Chổi nhựa cán dài', 'Dùng được'], ['Dây thép', 'Không dùng được']], 'Chỉ dùng vật cách điện khô ráo như gậy gỗ, chổi nhựa để tách nạn nhân; vật kim loại dẫn điện rất nguy hiểm.', $D);
        $this->matching($L, 'Nối mỗi hành động sai lầm với hậu quả có thể xảy ra.', [['Dùng tay kéo nạn nhân', 'Bị giật theo'], ['Tưới nước vào ổ điện cháy', 'Cháy lan nặng hơn'], ['Đứng gần dây điện đứt', 'Bị điện giật lan'], ['Gọi cấp cứu kịp thời', 'Nạn nhân được cứu']], 'Hành động sai khi cứu người bị điện giật có thể khiến chính mình gặp nguy hiểm.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm ĐÚNG hoặc SAI khi thấy người bị điện giật.', [['Ngắt cầu dao trước', 'ĐÚNG'], ['Dùng tay kéo nạn nhân', 'SAI'], ['Gọi cấp cứu 115', 'ĐÚNG'], ['Dùng gậy gỗ gạt dây điện', 'ĐÚNG'], ['Đứng nhìn không làm gì', 'SAI'], ['Đổ nước vào người nạn nhân', 'SAI']], 'Phải ngắt điện, dùng vật cách điện và gọi cấp cứu; không dùng tay trần hay đổ nước.', $D);
        $this->sortQ($L, 'Kéo mỗi vật vào nhóm DÙNG ĐƯỢC hoặc KHÔNG DÙNG ĐƯỢC để cứu người bị giật.', [['Gậy tre khô', 'DÙNG ĐƯỢC'], ['Ghế nhựa', 'DÙNG ĐƯỢC'], ['Xà beng sắt', 'KHÔNG DÙNG ĐƯỢC'], ['Khăn khô', 'DÙNG ĐƯỢC'], ['Dây xích sắt', 'KHÔNG DÙNG ĐƯỢC'], ['Tay trần', 'KHÔNG DÙNG ĐƯỢC']], 'Vật cách điện khô ráo thì dùng được; vật kim loại và tay trần tuyệt đối không dùng.', $D);
        $this->sortQ($L, 'Kéo mỗi tình huống vào đúng SỐ ĐIỆN THOẠI cần gọi.', [['Cháy do chập điện', '114'], ['Người bị điện giật bất tỉnh', '115'], ['Dây điện đứt rơi xuống đường', 'Tổng đài điện lực'], ['Kẻ gian đột nhập', '113']], 'Cháy gọi 114, cấp cứu người gọi 115, sự cố lưới điện gọi tổng đài điện lực.', $D);
        $this->sortQ($L, 'Kéo mỗi bước sơ cứu vào nhóm LÀM TRƯỚC hoặc LÀM SAU.', [['Ngắt nguồn điện', 'LÀM TRƯỚC'], ['Tách nạn nhân khỏi dây điện', 'LÀM TRƯỚC'], ['Đặt nạn nhân nằm nơi thoáng', 'LÀM SAU'], ['Gọi cấp cứu', 'LÀM SAU'], ['Kiểm tra hơi thở', 'LÀM SAU']], 'Phải đảm bảo an toàn bằng cách ngắt và tách nguồn điện trước, rồi mới sơ cứu nạn nhân.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm SỰ CỐ ĐIỆN hoặc BÌNH THƯỜNG.', [['Ổ điện bốc mùi khét', 'SỰ CỐ ĐIỆN'], ['Dây điện nóng bất thường', 'SỰ CỐ ĐIỆN'], ['Đèn sáng ổn định', 'BÌNH THƯỜNG'], ['Tia lửa khi cắm điện', 'SỰ CỐ ĐIỆN'], ['Vỏ máy quạt mát', 'BÌNH THƯỜNG']], 'Mùi khét, dây nóng và tia lửa là dấu hiệu sự cố điện cần xử lý ngay.', $D);
        $this->fill($L, 'Khi cứu người bị điện giật, việc đầu tiên là ___ nguồn điện.', [[0, 'ngắt']], 'Ngắt nguồn điện là việc đầu tiên và quan trọng nhất để đảm bảo an toàn cho người cứu.', $D);
        $this->fill($L, 'Dùng vật ___ điện như gậy gỗ khô để gạt dây điện ra khỏi nạn nhân.', [[0, 'cách']], 'Vật cách điện khô ráo giúp tách nạn nhân khỏi nguồn điện mà người cứu không bị giật theo.', $D);
        $this->fill($L, 'Số điện thoại cấp cứu y tế khi có người bị điện giật là ___.', [[0, '115']], '115 là số cấp cứu y tế, cần gọi ngay khi có người bị điện giật nặng.', $D);
        $this->fill($L, 'Nếu nạn nhân ngừng thở sau khi bị điện giật, phải tiến hành hô hấp ___ tạo và gọi cấp cứu ngay.', [[0, 'nhân']], 'Hô hấp nhân tạo kịp thời kết hợp gọi cấp cứu giúp tăng cơ hội cứu sống nạn nhân.', $D);
        $this->fill($L, 'Tuyệt đối không đứng gần dây điện đứt vì điện có thể ___ qua mặt đất ẩm ướt.', [[0, 'truyền']], 'Điện truyền qua mặt đất ẩm nên phải đứng thật xa dây điện đứt.', $D);
    }

    // ===== 3. cn-go-kim-loai-nhua (Lop 7, de) =====
    private function seedCnGoKimLoaiNhua(): void
    {
        $L = 'cn-go-kim-loai-nhua'; $D = 'de';
        $this->quiz($L, 'Gỗ có tính chất nổi bật nào?', ['Nhẹ, dễ gia công và cách điện', 'Rất cứng và dẫn điện tốt', 'Nặng và dễ bị gỉ', 'Trong suốt và giòn'], 0, 'Gỗ nhẹ, dễ cưa bào gia công và không dẫn điện nên được dùng làm bàn ghế, cán dụng cụ.', $D);
        $this->quiz($L, 'Kim loại nào nhẹ, thường dùng làm vỏ máy bay và lon nước ngọt?', ['Nhôm', 'Sắt', 'Chì', 'Gang'], 0, 'Nhôm nhẹ, không gỉ nên được dùng làm vỏ máy bay, lon nước ngọt và khung cửa.', $D);
        $this->quiz($L, 'Vì sao cán búa thường được làm bằng gỗ?', ['Vì gỗ nhẹ, chắc và giảm rung khi đóng', 'Vì gỗ dẫn điện tốt', 'Vì gỗ rất rẻ tiền', 'Vì gỗ không bao giờ hỏng'], 0, 'Cán búa bằng gỗ vừa nhẹ chắc, vừa giảm rung truyền lên tay khi đóng.', $D);
        $this->quiz($L, 'Nhựa được sản xuất chủ yếu từ nguyên liệu nào?', ['Dầu mỏ và khí thiên nhiên', 'Cát và đá vôi', 'Quặng sắt', 'Gỗ rừng'], 0, 'Nhựa là vật liệu nhân tạo được chế tạo chủ yếu từ dầu mỏ và khí thiên nhiên.', $D);
        $this->quiz($L, 'Vật liệu nào sau đây KHÔNG bị ăn mòn khi để ngoài trời mưa?', ['Nhựa', 'Sắt không sơn', 'Thép không sơn', 'Tôn kẽm trầy xước'], 0, 'Nhựa không thấm nước và không bị gỉ nên bền khi để ngoài trời mưa.', $D);
        $this->matching($L, 'Nối mỗi vật liệu với nguồn gốc của nó.', [['Gỗ', 'Từ cây gỗ'], ['Sắt', 'Từ quặng sắt'], ['Nhựa', 'Từ dầu mỏ'], ['Tre', 'Từ cây tre']], 'Gỗ và tre từ thực vật, sắt từ quặng trong lòng đất, nhựa từ dầu mỏ.', $D);
        $this->matching($L, 'Nối mỗi tính chất với ví dụ vật liệu có tính chất đó.', [['Cứng và nặng', 'Sắt'], ['Nhẹ và dẻo', 'Nhựa'], ['Dễ gia công', 'Gỗ'], ['Dẫn điện tốt', 'Đồng']], 'Mỗi vật liệu có tính chất nổi bật riêng phù hợp với từng công dụng khác nhau.', $D);
        $this->matching($L, 'Nối mỗi đồ vật trong nhà với vật liệu chính làm ra nó.', [['Bàn học', 'Gỗ'], ['Xoong nồi', 'Nhôm'], ['Chai nước suối', 'Nhựa'], ['Then cửa', 'Sắt']], 'Đồ vật được làm từ vật liệu phù hợp với tính chất và công dụng của nó.', $D);
        $this->matching($L, 'Nối mỗi vật liệu với cách bảo quản đúng.', [['Gỗ', 'Để nơi khô ráo'], ['Sắt', 'Sơn chống gỉ'], ['Nhựa', 'Tránh nắng gắt lâu ngày'], ['Vải', 'Giặt rồi phơi khô']], 'Bảo quản đúng cách giúp vật liệu bền lâu: gỗ tránh ẩm, sắt tránh gỉ, nhựa tránh nắng gắt.', $D);
        $this->matching($L, 'Nối mỗi công dụng với vật liệu phù hợp nhất.', [['Làm dây điện', 'Đồng'], ['Làm áo mưa', 'Nhựa'], ['Làm bàn ghế', 'Gỗ'], ['Làm dao kéo', 'Thép']], 'Đồng dẫn điện tốt làm dây điện, nhựa chống nước làm áo mưa, gỗ dễ gia công làm bàn ghế.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm KIM LOẠI hoặc KHÔNG PHẢI KIM LOẠI.', [['Sắt', 'KIM LOẠI'], ['Nhôm', 'KIM LOẠI'], ['Gỗ', 'KHÔNG PHẢI KIM LOẠI'], ['Nhựa', 'KHÔNG PHẢI KIM LOẠI'], ['Đồng', 'KIM LOẠI'], ['Cao su', 'KHÔNG PHẢI KIM LOẠI']], 'Sắt, nhôm, đồng là kim loại; gỗ, nhựa, cao su không phải kim loại.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm DẪN ĐIỆN hoặc CÁCH ĐIỆN.', [['Đồng', 'DẪN ĐIỆN'], ['Sắt', 'DẪN ĐIỆN'], ['Gỗ khô', 'CÁCH ĐIỆN'], ['Nhựa', 'CÁCH ĐIỆN']], 'Kim loại dẫn điện tốt; gỗ khô và nhựa cách điện nên dùng làm vỏ bọc an toàn.', $D);
        $this->sortQ($L, 'Kéo mỗi đồ vật vào nhóm ĐỒ GỖ, ĐỒ KIM LOẠI hoặc ĐỒ NHỰA.', [['Tủ quần áo', 'ĐỒ GỖ'], ['Thước kẻ học sinh', 'ĐỒ NHỰA'], ['Ổ khóa', 'ĐỒ KIM LOẠI'], ['Rổ rá', 'ĐỒ NHỰA'], ['Bàn là', 'ĐỒ KIM LOẠI'], ['Đũa ăn', 'ĐỒ GỖ']], 'Nhận biết vật liệu làm ra đồ vật giúp chọn và bảo quản đồ dùng đúng cách.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm NHẸ hoặc NẶNG.', [['Nhựa', 'NHẸ'], ['Nhôm', 'NHẸ'], ['Chì', 'NẶNG'], ['Gang', 'NẶNG']], 'Nhựa và nhôm nhẹ nên dễ mang vác; chì và gang nặng nên chắc chắn.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm BỀN VỚI NƯỚC hoặc DỄ HỎNG KHI ƯỚT.', [['Nhựa', 'BỀN VỚI NƯỚC'], ['Inox', 'BỀN VỚI NƯỚC'], ['Gỗ không sơn', 'DỄ HỎNG KHI ƯỚT'], ['Giấy', 'DỄ HỎNG KHI ƯỚT'], ['Sắt không sơn', 'DỄ HỎNG KHI ƯỚT']], 'Nhựa và inox bền với nước; gỗ, giấy và sắt không sơn dễ hỏng khi bị ướt lâu.', $D);
        $this->fill($L, 'Cán búa thường làm bằng ___ vì nhẹ, chắc và giảm rung cho tay.', [[0, 'gỗ']], 'Gỗ nhẹ, chắc và giảm rung nên rất phù hợp làm cán búa, cán cuốc.', $D);
        $this->fill($L, '___ là kim loại nhẹ, không gỉ, thường dùng làm vỏ lon nước ngọt.', [[0, 'Nhôm']], 'Nhôm nhẹ và không bị gỉ nên được dùng làm lon nước ngọt, khung cửa.', $D);
        $this->fill($L, 'Dây điện thường dùng lõi ___ vì kim loại này dẫn điện rất tốt.', [[0, 'đồng']], 'Đồng dẫn điện tốt và dẻo nên được dùng làm lõi dây điện.', $D);
        $this->fill($L, 'Đồ nhựa để ngoài nắng gắt lâu ngày sẽ bị giòn và ___ màu.', [[0, 'phai']], 'Nắng gắt làm nhựa lão hóa, giòn và phai màu nên cần che chắn.', $D);
        $this->fill($L, 'Sắt thép muốn bền khi để ngoài trời cần được ___ chống gỉ.', [[0, 'sơn']], 'Lớp sơn ngăn không khí và nước tiếp xúc với sắt nên chống được gỉ sét.', $D);
    }

    // ===== 4. cn-bua-kim-tua-vit-cua (Lop 7, trung_binh) =====
    private function seedCnBuaKimTuaVitCua(): void
    {
        $L = 'cn-bua-kim-tua-vit-cua'; $D = 'trung_binh';
        $this->quiz($L, 'Kìm có những công dụng chính nào?', ['Kẹp giữ, cắt dây và nhổ đinh', 'Chỉ dùng để đóng đinh', 'Chỉ dùng để cưa gỗ', 'Chỉ dùng để đo đạc'], 0, 'Kìm là dụng cụ đa năng: kẹp giữ vật, cắt dây kim loại và nhổ đinh.', $D);
        $this->quiz($L, 'Muốn tháo chiếc ốc vít có đầu hình chữ thập, em cần dùng loại nào?', ['Tua vít bốn cạnh (bake)', 'Tua vít đầu dẹt', 'Búa đinh', 'Cưa tay'], 0, 'Ốc đầu chữ thập phải dùng tua vít bốn cạnh vừa khít mới vặn được, không làm trờn đầu ốc.', $D);
        $this->quiz($L, 'Vì sao không được dùng búa để vặn ốc vít?', ['Vì búa dùng để đóng gõ, vặn ốc cần tua vít', 'Vì búa quá nhẹ', 'Vì ốc vít sợ tiếng động', 'Vì búa làm bằng gỗ'], 0, 'Mỗi dụng cụ có công dụng riêng: búa để đóng gõ, tua vít để vặn ốc; dùng sai sẽ hỏng cả dụng cụ lẫn ốc.', $D);
        $this->quiz($L, 'Khi cưa gỗ, để đường cưa thẳng và an toàn em cần làm gì?', ['Đánh dấu đường cưa và cưa đều tay theo dấu', 'Cưa thật mạnh không cần nhìn', 'Nhờ bạn giữ lưỡi cưa', 'Cưa khi gỗ đang ướt sũng'], 0, 'Đánh dấu trước và cưa đều tay theo đường dấu giúp đường cưa thẳng và an toàn.', $D);
        $this->quiz($L, 'Sau khi dùng xong cưa, cách bảo quản đúng là gì?', ['Lau sạch, che lưỡi cưa và cất nơi khô ráo', 'Để cưa ngoài sân cho tiện', 'Ngâm cưa trong nước', 'Vứt cưa bừa bãi'], 0, 'Lau sạch, che lưỡi cưa sắc và cất nơi khô ráo giúp cưa bền và không gây tai nạn.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ với các bộ phận chính của nó.', [['Búa', 'Đầu búa và cán búa'], ['Kìm', 'Hai má kìm và tay cầm'], ['Tua vít', 'Thân và đầu vít'], ['Cưa', 'Lưỡi cưa và cán cưa']], 'Mỗi dụng cụ cầm tay đều gồm phần làm việc và phần tay cầm.', $D);
        $this->matching($L, 'Nối mỗi việc cần làm với dụng cụ phù hợp nhất.', [['Nhổ đinh cong', 'Kìm'], ['Đóng đinh vào gỗ', 'Búa'], ['Cắt dây kẽm', 'Kìm cắt'], ['Xẻ tấm ván', 'Cưa']], 'Chọn đúng dụng cụ cho từng việc giúp làm nhanh, đẹp và an toàn.', $D);
        $this->matching($L, 'Nối mỗi loại dụng cụ vặn với đầu ốc phù hợp.', [['Tua vít dẹt', 'Ốc đầu dẹt'], ['Tua vít bốn cạnh', 'Ốc đầu chữ thập'], ['Cờ lê', 'Bu lông đai ốc'], ['Mỏ lết', 'Đai ốc nhiều cỡ']], 'Đầu dụng cụ phải vừa khít với đầu ốc mới vặn được chắc và không làm hỏng ốc.', $D);
        $this->matching($L, 'Nối mỗi nguy hiểm với cách phòng tránh khi dùng dụng cụ.', [['Lưỡi cưa sắc', 'Đeo găng tay'], ['Búa văng khi đóng', 'Cầm chắc cán búa'], ['Mạt gỗ bay vào mắt', 'Đeo kính bảo hộ'], ['Cán dụng cụ ướt', 'Lau khô tay cầm']], 'Dùng dụng cụ phải phòng tránh nguy hiểm bằng đồ bảo hộ và thao tác đúng.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ với vật liệu thường làm phần làm việc của nó.', [['Lưỡi cưa', 'Thép'], ['Đầu búa', 'Thép'], ['Má kìm', 'Thép'], ['Thân tua vít', 'Thép cứng']], 'Phần làm việc của dụng cụ cơ khí thường làm bằng thép để cứng và bền.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm DÙNG ĐỂ ĐO hoặc DÙNG ĐỂ GIA CÔNG.', [['Thước dây', 'DÙNG ĐỂ ĐO'], ['Búa', 'DÙNG ĐỂ GIA CÔNG'], ['Cưa', 'DÙNG ĐỂ GIA CÔNG'], ['Êke', 'DÙNG ĐỂ ĐO']], 'Thước và êke dùng để đo vẽ; búa và cưa dùng để gia công vật liệu.', $D);
        $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm ĐÚNG KỸ THUẬT hoặc SAI KỸ THUẬT.', [['Cầm chắc cán búa khi đóng', 'ĐÚNG KỸ THUẬT'], ['Dùng răng cắn dây điện', 'SAI KỸ THUẬT'], ['Đánh dấu trước khi cưa', 'ĐÚNG KỸ THUẬT'], ['Cưa giật mạnh không giữ vật', 'SAI KỸ THUẬT']], 'Thao tác đúng kỹ thuật giúp công việc đạt chất lượng và đảm bảo an toàn.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm CÓ LƯỠI SẮC hoặc KHÔNG CÓ LƯỠI SẮC.', [['Cưa', 'CÓ LƯỠI SẮC'], ['Dao rọc giấy', 'CÓ LƯỠI SẮC'], ['Búa', 'KHÔNG CÓ LƯỠI SẮC'], ['Tua vít', 'KHÔNG CÓ LƯỠI SẮC']], 'Dụng cụ có lưỡi sắc như cưa, dao cần cất giữ cẩn thận hơn.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TRƯỚC KHI DÙNG hoặc SAU KHI DÙNG dụng cụ.', [['Kiểm tra dụng cụ còn tốt', 'TRƯỚC KHI DÙNG'], ['Đeo đồ bảo hộ', 'TRƯỚC KHI DÙNG'], ['Lau sạch và cất gọn', 'SAU KHI DÙNG'], ['Mài lại lưỡi cưa đã cùn', 'SAU KHI DÙNG']], 'Trước khi dùng phải kiểm tra và bảo hộ; sau khi dùng phải vệ sinh, cất gọn.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm KẸP GIỮ, ĐÓNG GÕ hoặc CẮT.', [['Kìm', 'KẸP GIỮ'], ['Êtô', 'KẸP GIỮ'], ['Búa', 'ĐÓNG GÕ'], ['Cưa', 'CẮT']], 'Kìm và êtô để kẹp giữ, búa để đóng gõ, cưa để cắt vật liệu.', $D);
        $this->fill($L, 'Kìm vừa dùng để kẹp giữ vật vừa dùng để ___ dây kim loại nhỏ.', [[0, 'cắt']], 'Kìm là dụng cụ đa năng: kẹp giữ, cắt dây và nhổ đinh.', $D);
        $this->fill($L, 'Đầu búa thường làm bằng ___ nên rất cứng và nặng.', [[0, 'thép']], 'Đầu búa bằng thép cứng mới đủ lực đóng đinh vào gỗ.', $D);
        $this->fill($L, 'Trước khi cưa, phải dùng bút chì ___ dấu đường cần cắt.', [[0, 'đánh']], 'Đánh dấu đường cưa trước giúp đường cưa thẳng và chính xác.', $D);
        $this->fill($L, 'Tay cầm của kìm điện thường được bọc ___ để cách điện an toàn.', [[0, 'nhựa']], 'Lớp nhựa bọc tay cầm cách điện, bảo vệ người dùng khi làm việc với điện.', $D);
        $this->fill($L, 'Không dùng dụng cụ đã bị ___ cán vì rất dễ gây tai nạn.', [[0, 'hỏng']], 'Dụng cụ hỏng, nứt cán có thể gãy văng khi dùng, phải sửa hoặc thay mới.', $D);
    }

    // ===== 5. cn-cac-buoc-trong-cay (Lop 8, de) =====
    private function seedCnCacBuocTrongCay(): void
    {
        $L = 'cn-cac-buoc-trong-cay'; $D = 'de';
        $this->quiz($L, 'Vì sao phải chọn hạt giống tốt, không sâu bệnh?', ['Vì cây con khỏe, ít bệnh và cho năng suất cao', 'Vì hạt giống tốt rẻ tiền hơn', 'Vì hạt giống tốt đẹp mắt hơn', 'Vì hạt giống tốt không cần chăm sóc'], 0, 'Hạt giống tốt quyết định cây khỏe mạnh, ít sâu bệnh và năng suất cao.', $D);
        $this->quiz($L, 'Gieo hạt quá dày sẽ gây ra điều gì?', ['Cây chen chúc, thiếu ánh sáng nên còi cọc', 'Cây lớn nhanh hơn', 'Cây cho nhiều quả hơn', 'Cây không cần tưới nước'], 0, 'Gieo quá dày khiến cây tranh nhau ánh sáng, nước và dinh dưỡng nên còi cọc.', $D);
        $this->quiz($L, 'Bón lót là gì?', ['Bón phân vào đất trước khi gieo trồng', 'Bón phân sau khi thu hoạch', 'Bón phân lên lá cây', 'Bón phân vào buổi trưa'], 0, 'Bón lót là bón phân vào đất trước khi trồng để đất giàu dinh dưỡng ngay từ đầu.', $D);
        $this->quiz($L, 'Ngay sau khi trồng cây con, cần làm gì?', ['Tưới nước giữ ẩm cho đất', 'Bón thật nhiều phân', 'Nhổ cây lên kiểm tra rễ', 'Phơi cây ngoài nắng gắt'], 0, 'Tưới nước ngay sau khi trồng giúp rễ tiếp xúc đất tốt và cây nhanh bén rễ.', $D);
        $this->quiz($L, 'Vì sao đất trồng cần được làm nhỏ và san phẳng?', ['Để hạt tiếp xúc tốt với đất và thoát nước đều', 'Để đất đẹp mắt hơn', 'Để cỏ dễ mọc hơn', 'Để đất khô nhanh hơn'], 0, 'Đất tơi nhỏ, phẳng giúp hạt tiếp xúc tốt, giữ ẩm đều và thoát nước tốt.', $D);
        $this->matching($L, 'Nối mỗi bước trồng cây với công việc cụ thể.', [['Làm đất', 'Xới tơi và san phẳng'], ['Gieo hạt', 'Đặt hạt đúng độ sâu'], ['Chăm sóc', 'Tưới nước, bón phân'], ['Thu hoạch', 'Hái quả đúng lúc']], 'Trồng cây gồm các bước nối tiếp: làm đất, gieo trồng, chăm sóc rồi thu hoạch.', $D);
        $this->matching($L, 'Nối mỗi loại cây với cách nhân giống phổ biến.', [['Lúa', 'Gieo hạt'], ['Mía', 'Trồng bằng hom'], ['Khoai lang', 'Trồng bằng dây'], ['Chuối', 'Tách chồi con']], 'Mỗi loại cây có cách nhân giống phù hợp: gieo hạt, trồng hom, trồng dây hay tách chồi.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ làm vườn với công dụng của nó.', [['Cuốc', 'Xới đất'], ['Bình tưới', 'Tưới nước'], ['Kéo tỉa', 'Tỉa cành lá'], ['Xẻng', 'Đào hố trồng']], 'Mỗi dụng cụ làm vườn có công dụng riêng giúp các bước trồng cây thuận lợi.', $D);
        $this->matching($L, 'Nối mỗi thời vụ với loại cây trồng phù hợp.', [['Vụ đông', 'Rau cải'], ['Vụ hè thu', 'Lúa'], ['Quanh năm', 'Rau muống'], ['Vụ xuân', 'Ngô']], 'Trồng đúng thời vụ giúp cây gặp thời tiết thuận lợi, sinh trưởng tốt.', $D);
        $this->matching($L, 'Nối mỗi việc làm vườn với thời điểm thực hiện.', [['Bón lót', 'Trước khi trồng'], ['Tỉa thưa', 'Khi cây mọc quá dày'], ['Vun gốc', 'Khi cây đã lớn'], ['Phòng trừ sâu', 'Khi phát hiện sâu bệnh']], 'Mỗi việc chăm sóc cây đều có thời điểm thích hợp để đạt hiệu quả cao.', $D);
        $this->sortQ($L, 'Kéo mỗi công việc vào nhóm LÀM ĐẤT hoặc CHĂM SÓC.', [['Xới đất', 'LÀM ĐẤT'], ['Bón lót', 'LÀM ĐẤT'], ['Tưới nước', 'CHĂM SÓC'], ['Bắt sâu', 'CHĂM SÓC']], 'Làm đất và bón lót thuộc khâu chuẩn bị; tưới nước, bắt sâu thuộc khâu chăm sóc.', $D);
        $this->sortQ($L, 'Kéo mỗi loại cây vào nhóm CÂY NGẮN NGÀY hoặc CÂY DÀI NGÀY.', [['Rau muống', 'CÂY NGẮN NGÀY'], ['Lúa', 'CÂY NGẮN NGÀY'], ['Cà phê', 'CÂY DÀI NGÀY'], ['Xoài', 'CÂY DÀI NGÀY']], 'Rau, lúa là cây ngắn ngày; cà phê, xoài là cây dài ngày cho thu hoạch nhiều năm.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm ĐÚNG hoặc SAI kỹ thuật trồng cây.', [['Gieo hạt đúng mật độ', 'ĐÚNG'], ['Gieo hạt quá sâu', 'SAI'], ['Tưới nước sau khi gieo', 'ĐÚNG'], ['Trồng cây nơi tối hoàn toàn', 'SAI']], 'Gieo đúng mật độ, tưới đủ ẩm và đủ ánh sáng là kỹ thuật đúng khi trồng cây.', $D);
        $this->sortQ($L, 'Kéo mỗi loại đất vào nhóm TỐT hoặc XẤU cho trồng cây.', [['Đất tơi xốp giàu mùn', 'TỐT'], ['Đất cát pha', 'TỐT'], ['Đất sét nặng úng nước', 'XẤU'], ['Đất nhiễm mặn', 'XẤU']], 'Đất tơi xốp, giàu dinh dưỡng tốt cho cây; đất úng, nhiễm mặn xấu cho cây.', $D);
        $this->sortQ($L, 'Kéo mỗi bước vào nhóm TRƯỚC KHI GIEO hoặc SAU KHI GIEO.', [['Chọn giống', 'TRƯỚC KHI GIEO'], ['Làm đất', 'TRƯỚC KHI GIEO'], ['Tỉa cây', 'SAU KHI GIEO'], ['Thu hoạch', 'SAU KHI GIEO']], 'Chọn giống và làm đất làm trước khi gieo; tỉa cây và thu hoạch làm sau khi gieo.', $D);
        $this->fill($L, 'Hạt giống tốt phải chắc, ___ đều và không bị sâu bệnh.', [[0, 'mẩy']], 'Hạt chắc mẩy chứa nhiều dinh dưỡng giúp cây con khỏe mạnh.', $D);
        $this->fill($L, 'Gieo hạt quá ___ làm cây chen chúc nhau, còi cọc.', [[0, 'dày']], 'Gieo đúng mật độ để mỗi cây đủ ánh sáng, nước và dinh dưỡng.', $D);
        $this->fill($L, 'Bón ___ là bón phân vào đất trước khi gieo trồng.', [[0, 'lót']], 'Bón lót cung cấp dinh dưỡng cho đất ngay từ khi bắt đầu trồng.', $D);
        $this->fill($L, 'Sau khi gieo hạt phải tưới nước giữ ___ cho đất để hạt nảy mầm.', [[0, 'ẩm']], 'Đất đủ ẩm là điều kiện cần để hạt hút nước và nảy mầm.', $D);
        $this->fill($L, 'Cây con mới trồng cần được ___ bớt nắng gắt trong vài ngày đầu.', [[0, 'che']], 'Che nắng giúp cây con không bị héo khi rễ chưa bén đất.', $D);
    }

    // ===== 6. cn-tuoi-nuoc-bon-phan (Lop 8, trung_binh) =====
    private function seedCnTuoiNuocBonPhan(): void
    {
        $L = 'cn-tuoi-nuoc-bon-phan'; $D = 'trung_binh';
        $this->quiz($L, 'Phân đạm (N) có vai trò chính gì đối với cây trồng?', ['Giúp cây phát triển thân, lá xanh tốt', 'Giúp rễ phát triển mạnh', 'Giúp quả ngọt và chắc', 'Giúp hoa có màu đẹp'], 0, 'Đạm giúp cây phát triển thân lá; lân giúp rễ và ra hoa; kali giúp quả ngọt, cây cứng cáp.', $D);
        $this->quiz($L, 'Phân lân (P) có tác dụng chính gì?', ['Giúp rễ phát triển và cây ra hoa, kết quả', 'Giúp lá xanh đậm', 'Giúp thân cây cao nhanh', 'Giúp quả to bất thường'], 0, 'Lân kích thích rễ phát triển, giúp cây ra hoa và đậu quả tốt.', $D);
        $this->quiz($L, 'Phân kali (K) có tác dụng gì với cây trồng?', ['Giúp cây cứng cáp, quả ngọt và chống chịu tốt', 'Giúp lá to bất thường', 'Giúp cây ra nhiều hoa đực', 'Giúp rễ ăn sâu bất thường'], 0, 'Kali giúp cây cứng cáp, tăng sức chống chịu sâu bệnh và làm quả ngọt hơn.', $D);
        $this->quiz($L, 'Vì sao nên tưới nước cho cây vào sáng sớm?', ['Vì ít bốc hơi, cây hấp thụ tốt và ít nấm bệnh', 'Vì sáng sớm nước ấm hơn', 'Vì cây chỉ uống nước buổi sáng', 'Vì buổi sáng không ai tưới'], 0, 'Tưới sáng sớm giúp nước thấm sâu, ít bốc hơi và lá khô nhanh nên ít nấm bệnh.', $D);
        $this->quiz($L, 'Bón phân cho cây vào lúc nào là tốt nhất?', ['Chiều mát khi đất còn ẩm', 'Trưa nắng gắt', 'Lúc cây đang héo rũ', 'Khi đất khô nứt nẻ'], 0, 'Bón phân chiều mát, đất ẩm giúp phân tan và rễ hấp thụ tốt, tránh cháy lá.', $D);
        $this->matching($L, 'Nối mỗi nguyên tố dinh dưỡng với vai trò của nó.', [['Đạm (N)', 'Phát triển thân lá'], ['Lân (P)', 'Phát triển bộ rễ'], ['Kali (K)', 'Cứng cây, ngọt quả'], ['Canxi (Ca)', 'Chắc thành tế bào']], 'Mỗi nguyên tố dinh dưỡng có vai trò riêng không thể thay thế đối với cây.', $D);
        $this->matching($L, 'Nối mỗi loại phân với ví dụ cụ thể.', [['Phân chuồng', 'Phân bò đã ủ hoai'], ['Phân xanh', 'Cây họ đậu ủ'], ['Phân đạm', 'Phân urê'], ['Phân NPK', 'Phân tổng hợp']], 'Phân chuồng, phân xanh là hữu cơ; urê, NPK là phân hóa học.', $D);
        $this->matching($L, 'Nối mỗi cách tưới với ưu điểm của nó.', [['Tưới phun mưa', 'Tưới đều trên diện rộng'], ['Tưới nhỏ giọt', 'Tiết kiệm nước nhất'], ['Tưới rãnh', 'Đơn giản, ít tốn kém'], ['Tưới thủ công', 'Chủ động từng cây']], 'Mỗi cách tưới có ưu điểm riêng phù hợp với từng loại cây và điều kiện.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu của cây với nguyên nhân có thể.', [['Lá vàng úa', 'Thiếu đạm'], ['Rễ kém phát triển', 'Thiếu lân'], ['Quả nhỏ, nhạt', 'Thiếu kali'], ['Lá héo rũ', 'Thiếu nước']], 'Quan sát dấu hiệu trên cây giúp đoán cây đang thiếu chất gì để bổ sung.', $D);
        $this->matching($L, 'Nối mỗi việc bón phân với thời điểm thực hiện.', [['Bón lót', 'Trước khi trồng'], ['Bón thúc lần 1', 'Khi cây bén rễ'], ['Bón thúc lần 2', 'Trước khi ra hoa'], ['Bón phân chuồng', 'Ủ hoai trước khi bón']], 'Bón đúng thời điểm giúp cây nhận dinh dưỡng đúng lúc cần nhất.', $D);
        $this->sortQ($L, 'Kéo mỗi loại phân vào nhóm PHÂN ĐƠN hoặc PHÂN HỖN HỢP.', [['Urê', 'PHÂN ĐƠN'], ['Kali clorua', 'PHÂN ĐƠN'], ['NPK', 'PHÂN HỖN HỢP'], ['DAP', 'PHÂN HỖN HỢP']], 'Phân đơn chỉ chứa một nguyên tố; phân hỗn hợp chứa nhiều nguyên tố dinh dưỡng.', $D);
        $this->sortQ($L, 'Kéo mỗi cách làm vào nhóm ĐÚNG hoặc SAI khi tưới nước.', [['Tưới vào sáng sớm', 'ĐÚNG'], ['Tưới đủ ẩm, không để úng', 'ĐÚNG'], ['Tưới lúc trưa nắng gắt', 'SAI'], ['Tưới ào ạt gây xói đất', 'SAI']], 'Tưới sáng sớm, vừa đủ ẩm là đúng; tưới trưa nắng và tưới ào ạt là sai.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm CÂY KHỎE hoặc CÂY YẾU.', [['Lá xanh tốt', 'CÂY KHỎE'], ['Thân mập, cứng cáp', 'CÂY KHỎE'], ['Lá vàng rụng nhiều', 'CÂY YẾU'], ['Rễ thối đen', 'CÂY YẾU']], 'Cây khỏe lá xanh, thân cứng; cây yếu lá vàng, rễ thối.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN khi bón phân.', [['Bón đúng liều lượng', 'NÊN'], ['Bón khi đất đủ ẩm', 'NÊN'], ['Bón phân sát gốc cây non', 'KHÔNG NÊN'], ['Bón phân tươi chưa ủ hoai', 'KHÔNG NÊN']], 'Bón đúng liều, đúng lúc cây hấp thụ tốt; bón sai gây cháy rễ và bệnh.', $D);
        $this->sortQ($L, 'Kéo mỗi loại nước vào nhóm DÙNG ĐƯỢC hoặc KHÔNG DÙNG ĐƯỢC để tưới cây.', [['Nước giếng sạch', 'DÙNG ĐƯỢC'], ['Nước mưa', 'DÙNG ĐƯỢC'], ['Nước thải công nghiệp', 'KHÔNG DÙNG ĐƯỢC'], ['Nước nhiễm mặn', 'KHÔNG DÙNG ĐƯỢC']], 'Chỉ tưới bằng nước sạch; nước thải và nước mặn làm hại cây và đất.', $D);
        $this->fill($L, 'Urê là loại phân ___ cung cấp đạm cho cây trồng.', [[0, 'đạm']], 'Urê chứa hàm lượng đạm cao, giúp cây phát triển thân lá xanh tốt.', $D);
        $this->fill($L, 'Phân ___ cung cấp đồng thời cả đạm, lân và kali cho cây.', [[0, 'NPK']], 'NPK là phân hỗn hợp chứa ba nguyên tố dinh dưỡng chính.', $D);
        $this->fill($L, 'Tưới nước lúc ___ nắng gắt làm cây dễ bị sốc nhiệt.', [[0, 'trưa']], 'Trưa nắng nước bốc hơi nhanh và lá ướt dễ bị cháy nên không tưới lúc này.', $D);
        $this->fill($L, 'Bón phân khi đất ___ giúp rễ cây dễ hấp thụ dinh dưỡng.', [[0, 'ẩm']], 'Đất ẩm giúp phân tan ra để rễ hút được; đất khô phân khó tan.', $D);
        $this->fill($L, 'Không bón phân ___ chưa ủ hoai vì gây nóng rễ và sinh bệnh cho cây.', [[0, 'tươi']], 'Phân tươi chưa ủ chứa mầm bệnh và sinh nhiệt làm cháy rễ cây.', $D);
    }

    // ===== 7. cn-an-toan-dien-lop-6-1 (Lop 6, de) =====
    private function seedCnAnToanDienLop61(): void
    {
        $L = 'cn-an-toan-dien-lop-6-1'; $D = 'de';
        $this->quiz($L, 'Khi dây điện trong nhà bị đứt rơi xuống sàn, em nên làm gì?', ['Đứng xa, không đến gần và báo ngay người lớn', 'Nhặt dây lên xem thử', 'Dùng tay nối hai đầu dây lại', 'Giấu đi không nói với ai'], 0, 'Dây điện đứt vẫn có thể còn điện, phải đứng xa và báo ngay cho người lớn.', $D);
        $this->quiz($L, 'Ổ điện trong nhà nên đặt ở vị trí nào để an toàn cho trẻ nhỏ?', ['Nơi cao, ngoài tầm với và có nắp che', 'Ngay sát mặt đất cho tiện', 'Cạnh bồn rửa mặt', 'Sau cánh cửa ra vào'], 0, 'Ổ điện nên đặt cao, có nắp che để trẻ nhỏ không chạm vào được.', $D);
        $this->quiz($L, 'Vì sao không được chơi gần trạm biến áp?', ['Vì điện áp ở đó rất cao, cực kỳ nguy hiểm', 'Vì ở đó ồn ào khó chịu', 'Vì ở đó không có bóng mát', 'Vì ở đó hay có chó dữ'], 0, 'Trạm biến áp có điện áp rất cao, đến gần có thể bị phóng điện gây nguy hiểm tính mạng.', $D);
        $this->quiz($L, 'Khi thấy bạn chọc que sắt vào ổ điện, em nên làm gì?', ['Ngăn bạn dừng lại và báo người lớn', 'Đứng xem cho vui', 'Cùng bạn chọc thử', 'Bỏ đi không quan tâm'], 0, 'Chọc vật kim loại vào ổ điện rất nguy hiểm, phải ngăn bạn lại và báo người lớn ngay.', $D);
        $this->quiz($L, 'Đồ điện bị rơi xuống nước (như máy sấy tóc) thì phải làm sao?', ['Ngắt điện, không chạm vào và báo người lớn', 'Vớt lên dùng tiếp ngay', 'Dùng tay vớt lên thật nhanh', 'Đổ nước đi rồi cắm điện thử'], 0, 'Đồ điện rơi xuống nước rất nguy hiểm, phải ngắt điện trước và báo người lớn xử lý.', $D);
        $this->matching($L, 'Nối mỗi đồ vật với mức độ nguy hiểm về điện.', [['Dây điện bị tróc vỏ', 'Rất nguy hiểm'], ['Ổ điện có nắp che', 'Ít nguy hiểm hơn'], ['Vỏ nhựa đồ điện khô', 'An toàn khi dùng đúng'], ['Kim loại bị ướt', 'Rất nguy hiểm']], 'Dây hở và kim loại ướt rất nguy hiểm; đồ điện nguyên vẹn, khô ráo thì an toàn hơn.', $D);
        $this->matching($L, 'Nối mỗi nơi với mức độ an toàn về điện.', [['Trạm biến áp', 'Cấm đến gần'], ['Cột điện cao thế', 'Nguy hiểm'], ['Phòng khách khô ráo', 'An toàn'], ['Nhà tắm ẩm ướt', 'Cần cẩn thận']], 'Nơi có điện áp cao thì cấm đến gần; nơi ẩm ướt dùng điện phải cẩn thận.', $D);
        $this->matching($L, 'Nối mỗi hành vi của bạn nhỏ với lời khuyên đúng.', [['Chơi thả diều dưới đường dây', 'Dừng lại ngay'], ['Lau khô tay trước khi bật đèn', 'Làm rất tốt'], ['Tò mò tháo ổ điện', 'Tuyệt đối không'], ['Báo cô giáo khi thấy dây hở', 'Làm rất tốt']], 'Thả diều gần dây điện và tháo ổ điện là việc tuyệt đối không được làm.', $D);
        $this->matching($L, 'Nối mỗi tình huống trời mưa bão với việc nên làm.', [['Có sấm sét', 'Vào nhà trú ẩn'], ['Dây điện đứt ngoài đường', 'Đứng xa, báo người lớn'], ['Ở trong nhà', 'Không chạm đồ điện ướt'], ['Đi ngoài đường', 'Tránh xa cột điện']], 'Khi mưa bão có sấm sét phải trú trong nhà, tránh xa cột điện và dây đứt.', $D);
        $this->matching($L, 'Nối mỗi vật liệu với việc có dẫn điện hay không.', [['Sắt', 'Có dẫn điện'], ['Nhựa', 'Không dẫn điện'], ['Gỗ khô', 'Không dẫn điện'], ['Nước', 'Có dẫn điện']], 'Kim loại và nước dẫn điện; nhựa và gỗ khô không dẫn điện.', $D);
        $this->sortQ($L, 'Kéo mỗi đồ vật vào nhóm NGUY HIỂM hoặc AN TOÀN với trẻ nhỏ.', [['Ổ điện không nắp che', 'NGUY HIỂM'], ['Dây điện bị hở', 'NGUY HIỂM'], ['Đồ chơi bằng nhựa', 'AN TOÀN'], ['Công tắc có vỏ bọc', 'AN TOÀN']], 'Ổ điện hở và dây điện tróc vỏ nguy hiểm với trẻ nhỏ; đồ chơi nhựa an toàn.', $D);
        $this->sortQ($L, 'Kéo mỗi nơi vào nhóm ĐƯỢC CHƠI hoặc KHÔNG ĐƯỢC CHƠI.', [['Sân trường', 'ĐƯỢC CHƠI'], ['Công viên', 'ĐƯỢC CHƠI'], ['Gần trạm biến áp', 'KHÔNG ĐƯỢC CHƠI'], ['Dưới đường dây cao thế', 'KHÔNG ĐƯỢC CHƠI']], 'Không chơi gần trạm biến áp và dưới đường dây cao thế vì rất nguy hiểm.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm NÊN LÀM hoặc KHÔNG NÊN LÀM khi trời mưa bão.', [['Vào nhà trú mưa', 'NÊN LÀM'], ['Tắt bớt thiết bị điện', 'NÊN LÀM'], ['Đứng trú dưới cây to', 'KHÔNG NÊN LÀM'], ['Chạm vào cột điện ướt', 'KHÔNG NÊN LÀM']], 'Mưa bão nên vào nhà trú, tắt bớt đồ điện; không trú dưới cây to hay chạm cột điện.', $D);
        $this->sortQ($L, 'Kéo mỗi câu nói vào nhóm ĐÚNG hoặc SAI.', [['Tay ướt không chạm đồ điện', 'ĐÚNG'], ['Thấy dây hở phải báo người lớn', 'ĐÚNG'], ['Dây điện đứt vẫn an toàn', 'SAI'], ['Trẻ nhỏ có thể tự sửa ổ điện', 'SAI']], 'Tay ướt không chạm đồ điện và thấy dây hở phải báo người lớn là đúng.', $D);
        $this->sortQ($L, 'Kéo mỗi vật vào nhóm DẪN ĐIỆN hoặc KHÔNG DẪN ĐIỆN.', [['Đinh sắt', 'DẪN ĐIỆN'], ['Dây đồng', 'DẪN ĐIỆN'], ['Thước nhựa', 'KHÔNG DẪN ĐIỆN'], ['Giấy khô', 'KHÔNG DẪN ĐIỆN']], 'Sắt, đồng dẫn điện; nhựa và giấy khô không dẫn điện.', $D);
        $this->fill($L, 'Trạm biến áp có biển ___ báo nguy hiểm, cấm đến gần.', [[0, 'cảnh']], 'Biển cảnh báo ở trạm biến áp nhắc mọi người không đến gần vì điện áp rất cao.', $D);
        $this->fill($L, 'Không chơi thả diều dưới đường dây ___ thế.', [[0, 'cao']], 'Dây diều vướng vào đường dây cao thế có thể gây điện giật rất nguy hiểm.', $D);
        $this->fill($L, 'Ổ điện nên có ___ che để trẻ nhỏ không chọc tay vào được.', [[0, 'nắp']], 'Nắp che ổ điện ngăn trẻ nhỏ chọc tay hay vật lạ vào ổ điện.', $D);
        $this->fill($L, 'Thấy bạn nghịch ổ điện, em phải ___ bạn dừng lại ngay.', [[0, 'nhắc']], 'Nhắc bạn dừng lại và báo người lớn khi thấy bạn nghịch ổ điện.', $D);
        $this->fill($L, 'Dây điện bị đứt vẫn có thể còn ___, tuyệt đối không chạm vào.', [[0, 'điện']], 'Dây đứt vẫn có thể còn điện nên tuyệt đối không chạm vào.', $D);
    }

    // ===== 8. cn-an-toan-dien-lop-6-2 (Lop 6, de) =====
    private function seedCnAnToanDienLop62(): void
    {
        $L = 'cn-an-toan-dien-lop-6-2'; $D = 'de';
        $this->quiz($L, 'Khi cắm phích điện vào ổ, nên làm thế nào?', ['Cắm chắc chắn, tay khô ráo', 'Cắm lỏng lẻo cho dễ rút', 'Tay ướt cắm cho nhanh', 'Nhờ em nhỏ cắm giúp'], 0, 'Cắm phích phải chắc chắn và tay khô ráo để tiếp xúc tốt, không bị tia lửa.', $D);
        $this->quiz($L, 'Vì sao không nên để dây điện bị gập, xoắn nhiều?', ['Vì dễ đứt lõi bên trong gây chập cháy', 'Vì dây sẽ ngắn lại', 'Vì dây sẽ đẹp hơn', 'Vì dây gập tốn điện hơn'], 0, 'Dây điện bị gập xoắn nhiều dễ đứt lõi, hở điện gây chập cháy.', $D);
        $this->quiz($L, 'Bàn là sau khi dùng xong nên để như thế nào?', ['Dựng đứng, rút phích, để nguội nơi an toàn', 'Để nằm ngang trên quần áo', 'Cắm điện để lần sau dùng', 'Để gần rèm cửa'], 0, 'Bàn là nóng phải dựng đứng, rút phích và để nơi an toàn tránh gây cháy.', $D);
        $this->quiz($L, 'Thói quen nào sau đây nguy hiểm khi dùng điện thoại?', ['Vừa sạc pin vừa dùng điện thoại', 'Sạc pin đầy rồi rút ra', 'Dùng ốp lưng bảo vệ', 'Để điện thoại nơi khô ráo'], 0, 'Vừa sạc vừa dùng điện thoại dễ gây cháy nổ pin, rất nguy hiểm.', $D);
        $this->quiz($L, 'Khi gia đình đi vắng nhiều ngày, nên làm gì với đồ điện?', ['Rút phích các đồ điện không cần thiết', 'Để nguyên tất cả', 'Bật tất cả đèn cho sáng', 'Mở tivi cho vui nhà'], 0, 'Đi vắng nhiều ngày nên rút phích đồ điện để an toàn và tiết kiệm điện.', $D);
        $this->matching($L, 'Nối mỗi đồ điện với cách dùng an toàn của nó.', [['Bàn là', 'Rút phích sau khi dùng'], ['Quạt điện', 'Không thò tay vào cánh'], ['Nồi cơm điện', 'Để nơi khô ráo'], ['Máy sấy tóc', 'Tránh xa nước']], 'Mỗi đồ điện có quy tắc dùng an toàn riêng cần ghi nhớ.', $D);
        $this->matching($L, 'Nối mỗi thói quen dùng điện với đánh giá.', [['Tắt đèn khi ra khỏi phòng', 'Tốt'], ['Rút phích khi không dùng', 'Tốt'], ['Vừa sạc vừa chơi điện thoại', 'Xấu'], ['Cắm nhiều đồ vào một ổ', 'Xấu']], 'Tắt đồ không dùng và rút phích là thói quen tốt; vừa sạc vừa dùng rất xấu.', $D);
        $this->matching($L, 'Nối mỗi việc với thời điểm nên làm.', [['Rút phích bàn là', 'Ngay sau khi dùng'], ['Kiểm tra dây điện', 'Định kỳ'], ['Lau chùi đồ điện', 'Khi đã ngắt điện'], ['Thay bóng đèn', 'Khi đã tắt công tắc']], 'Lau chùi, thay bóng đèn chỉ làm khi đã ngắt điện để an toàn.', $D);
        $this->matching($L, 'Nối mỗi đồ điện với nơi đặt an toàn.', [['Tủ lạnh', 'Nơi khô thoáng'], ['Máy giặt', 'Nơi thoát nước tốt'], ['Ấm điện', 'Xa tầm tay trẻ nhỏ'], ['Ổ điện', 'Nơi cao ráo']], 'Đồ điện cần đặt nơi khô ráo, thoáng mát và xa tầm tay trẻ nhỏ.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu hỏng với việc cần làm.', [['Dây điện sờn vỏ', 'Ngừng dùng, báo người lớn'], ['Phích cắm bị lỏng', 'Không dùng nữa'], ['Ổ điện nóng bất thường', 'Ngắt điện, báo người lớn'], ['Đồ điện có mùi khét', 'Rút phích ngay']], 'Thấy đồ điện có dấu hiệu hỏng phải ngừng dùng và báo người lớn ngay.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN khi dùng bàn là.', [['Rút phích sau khi dùng', 'NÊN'], ['Dựng đứng bàn là khi nghỉ tay', 'NÊN'], ['Để bàn là nằm ngang khi đang nóng', 'KHÔNG NÊN'], ['Để trẻ nhỏ chơi gần bàn là', 'KHÔNG NÊN']], 'Dùng bàn là phải dựng đứng khi nghỉ, rút phích sau khi dùng và xa trẻ nhỏ.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm ĐÚNG hoặc SAI khi cắm điện.', [['Cắm phích chắc chắn', 'ĐÚNG'], ['Kiểm tra phích trước khi cắm', 'ĐÚNG'], ['Tay ướt cắm điện', 'SAI'], ['Kéo dây để rút phích', 'SAI']], 'Cắm điện đúng là tay khô, cắm chắc; sai là tay ướt và kéo dây.', $D);
        $this->sortQ($L, 'Kéo mỗi đồ điện vào nhóm DÙNG XONG RÚT PHÍCH hoặc CÓ THỂ ĐỂ NGUYÊN.', [['Bàn là', 'DÙNG XONG RÚT PHÍCH'], ['Máy sấy tóc', 'DÙNG XONG RÚT PHÍCH'], ['Tủ lạnh', 'CÓ THỂ ĐỂ NGUYÊN'], ['Đồng hồ treo tường', 'CÓ THỂ ĐỂ NGUYÊN']], 'Đồ sinh nhiệt dùng xong phải rút phích; tủ lạnh cần chạy liên tục.', $D);
        $this->sortQ($L, 'Kéo mỗi nơi vào nhóm NÊN hoặc KHÔNG NÊN đặt đồ điện.', [['Nơi khô ráo', 'NÊN'], ['Nơi thoáng mát', 'NÊN'], ['Gần bồn rửa mặt', 'KHÔNG NÊN'], ['Trên sàn nhà ướt', 'KHÔNG NÊN']], 'Đồ điện phải đặt nơi khô ráo, tránh xa nguồn nước.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm AN TOÀN hoặc NGUY HIỂM.', [['Dùng đồ điện đúng điện áp', 'AN TOÀN'], ['Để đồ điện xa nước', 'AN TOÀN'], ['Tự tháo đồ điện đang cắm', 'NGUY HIỂM'], ['Dùng dây điện sờn vỏ', 'NGUY HIỂM']], 'Dùng đúng điện áp và xa nước thì an toàn; tự tháo đồ đang cắm rất nguy hiểm.', $D);
        $this->fill($L, 'Cắm phích điện phải cắm ___ chắn và tay phải khô ráo.', [[0, 'chắc']], 'Phích cắm chắc chắn giúp tiếp xúc tốt, không phát tia lửa.', $D);
        $this->fill($L, 'Không để dây điện bị ___ xoắn nhiều vì dễ đứt lõi gây chập.', [[0, 'gập']], 'Dây bị gập xoắn nhiều dễ đứt lõi đồng bên trong.', $D);
        $this->fill($L, 'Vừa ___ vừa dùng điện thoại rất nguy hiểm, dễ cháy nổ pin.', [[0, 'sạc']], 'Vừa sạc vừa dùng làm pin nóng lên, dễ cháy nổ.', $D);
        $this->fill($L, 'Đồ điện lâu ngày không dùng nên ___ phích để an toàn.', [[0, 'rút']], 'Rút phích đồ không dùng vừa an toàn vừa tiết kiệm điện.', $D);
        $this->fill($L, 'Bàn là đang nóng phải để ___ tầm tay của trẻ em.', [[0, 'xa']], 'Bàn là nóng để gần trẻ em dễ gây bỏng và cháy.', $D);
    }

    // ===== 9. cn-an-toan-dien-lop-7-1 (Lop 7, de) =====
    private function seedCnAnToanDienLop71(): void
    {
        $L = 'cn-an-toan-dien-lop-7-1'; $D = 'de';
        $this->quiz($L, 'Khi đã ngắt được điện để cứu người bị giật, việc tiếp theo là gì?', ['Kiểm tra hơi thở và gọi cấp cứu 115', 'Cho nạn nhân ăn kẹo', 'Đưa nạn nhân đi chơi', 'Để nạn nhân tự nghỉ ngơi'], 0, 'Sau khi ngắt điện phải kiểm tra hơi thở nạn nhân và gọi ngay 115.', $D);
        $this->quiz($L, 'Vì sao phải gọi cấp cứu 115 ngay khi có người bị điện giật nặng?', ['Vì cần bác sĩ cứu chữa kịp thời', 'Vì để ghi hình lại', 'Vì để báo công an bắt người', 'Vì để xin nghỉ học'], 0, 'Người bị điện giật nặng cần được cấp cứu y tế càng sớm càng tốt.', $D);
        $this->quiz($L, 'Khi dây điện đang cháy, có được dùng nước để dập lửa không?', ['Không, vì nước dẫn điện rất nguy hiểm', 'Có, nước dập lửa tốt nhất', 'Có, càng nhiều nước càng tốt', 'Có nếu là nước mưa'], 0, 'Cháy do điện tuyệt đối không dùng nước vì nước dẫn điện gây nguy hiểm.', $D);
        $this->quiz($L, 'Loại bình chữa cháy nào dùng được cho đám cháy do điện?', ['Bình bột khô hoặc bình CO2', 'Bình nước', 'Xô nước', 'Vòi nước'], 0, 'Cháy điện dùng bình bột khô hoặc CO2, không dùng nước.', $D);
        $this->quiz($L, 'Sau sự cố chập điện, trước khi dùng điện trở lại cần làm gì?', ['Để thợ điện kiểm tra, sửa xong mới đóng cầu dao', 'Tự đóng cầu dao ngay', 'Cắm thử đồ điện xem sao', 'Dùng tay sờ thử dây điện'], 0, 'Sau chập điện phải để thợ kiểm tra sửa chữa xong mới được dùng điện lại.', $D);
        $this->matching($L, 'Nối mỗi loại đám cháy với cách dập đúng.', [['Cháy do điện', 'Ngắt điện rồi dùng bình CO2'], ['Cháy dầu mỡ', 'Dùng chăn ướt đậy lại'], ['Cháy quần áo trên người', 'Nằm lăn để dập lửa'], ['Cháy nhỏ mới phát', 'Dùng bình bột khô']], 'Mỗi loại đám cháy có cách dập riêng; cháy điện phải ngắt điện trước.', $D);
        $this->matching($L, 'Nối mỗi bước xử lý khi chập điện với thứ tự.', [['Bước 1', 'Ngắt cầu dao'], ['Bước 2', 'Báo người lớn'], ['Bước 3', 'Gọi 114 nếu có cháy'], ['Bước 4', 'Không dùng điện khi chưa sửa xong']], 'Xử lý chập điện theo trình tự: ngắt điện, báo người lớn, gọi cứu hỏa nếu cháy.', $D);
        $this->matching($L, 'Nối mỗi vật với cách dùng khi có cháy do điện.', [['Bình CO2', 'Dập đám cháy điện'], ['Chăn ướt', 'Đậy vật cháy nhỏ'], ['Nước', 'Không dùng cho cháy điện'], ['Cát khô', 'Dập lửa nhỏ']], 'Bình CO2 và cát khô dập được cháy điện; nước tuyệt đối không dùng.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu sự cố với nguyên nhân có thể.', [['Cầu dao nhảy liên tục', 'Quá tải hoặc chập điện'], ['Mùi khét từ ổ điện', 'Dây điện nóng chảy'], ['Đèn chớp tắt liên tục', 'Tiếp xúc kém'], ['Tiếng nổ lách tách', 'Phóng điện']], 'Nhận biết dấu hiệu giúp đoán nguyên nhân sự cố điện để xử lý đúng.', $D);
        $this->matching($L, 'Nối mỗi người với việc nên làm khi có sự cố điện.', [['Học sinh', 'Báo người lớn và tránh xa'], ['Người lớn', 'Ngắt điện và xử lý'], ['Thợ điện', 'Sửa chữa'], ['Lính cứu hỏa', 'Dập cháy']], 'Học sinh khi gặp sự cố điện chỉ nên báo người lớn và tránh xa, không tự xử lý.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm ĐÚNG hoặc SAI khi ổ điện bốc khói.', [['Ngắt cầu dao ngay', 'ĐÚNG'], ['Báo người lớn', 'ĐÚNG'], ['Dùng nước để dập', 'SAI'], ['Tiếp tục cắm điện dùng', 'SAI']], 'Ổ điện bốc khói phải ngắt cầu dao và báo người lớn; không dùng nước, không dùng tiếp.', $D);
        $this->sortQ($L, 'Kéo mỗi vật vào nhóm DẬP ĐƯỢC hoặc KHÔNG DẬP ĐƯỢC đám cháy điện.', [['Bình CO2', 'DẬP ĐƯỢC'], ['Bình bột khô', 'DẬP ĐƯỢC'], ['Cát khô', 'DẬP ĐƯỢC'], ['Nước', 'KHÔNG DẬP ĐƯỢC']], 'Bình CO2, bột khô và cát khô dập được cháy điện; nước thì không.', $D);
        $this->sortQ($L, 'Kéo mỗi hành động vào nhóm BÌNH TĨNH hoặc HOẢNG LOẠN khi có sự cố.', [['Ngắt điện rồi báo người lớn', 'BÌNH TĨNH'], ['Gọi số điện thoại khẩn cấp', 'BÌNH TĨNH'], ['La hét chạy tán loạn', 'HOẢNG LOẠN'], ['Chạm bừa vào đồ điện', 'HOẢNG LOẠN']], 'Gặp sự cố phải bình tĩnh ngắt điện, báo người lớn và gọi số khẩn cấp.', $D);
        $this->sortQ($L, 'Kéo mỗi số điện thoại vào đúng TRƯỜNG HỢP cần gọi.', [['114', 'Cháy nổ'], ['115', 'Cấp cứu người'], ['113', 'An ninh trật tự'], ['Tổng đài điện lực', 'Sự cố lưới điện']], 'Cháy gọi 114, cứu người gọi 115, sự cố điện lưới gọi tổng đài điện lực.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm LÀM NGAY hoặc ĐỢI NGƯỜI LỚN.', [['Tránh xa khu vực nguy hiểm', 'LÀM NGAY'], ['Gọi điện báo tin', 'LÀM NGAY'], ['Tự sửa dây điện cháy', 'ĐỢI NGƯỜI LỚN'], ['Tự đóng lại cầu dao', 'ĐỢI NGƯỜI LỚN']], 'Học sinh chỉ làm việc an toàn như tránh xa và báo tin; sửa chữa phải đợi người lớn.', $D);
        $this->fill($L, 'Cháy do điện phải dùng bình ___ hoặc bột khô, tuyệt đối không dùng nước.', [[0, 'CO2']], 'Bình CO2 và bột khô dập được cháy điện mà không dẫn điện như nước.', $D);
        $this->fill($L, 'Khi cầu dao bị nhảy, không đóng lại ngay mà phải tìm ___ nhân sự cố.', [[0, 'nguyên']], 'Phải tìm nguyên nhân cầu dao nhảy và khắc phục xong mới đóng lại.', $D);
        $this->fill($L, 'Sau khi ngắt điện, dùng ___ khô để gạt dây điện ra khỏi nạn nhân.', [[0, 'gậy']], 'Gậy gỗ khô cách điện, dùng để tách nạn nhân khỏi nguồn điện an toàn.', $D);
        $this->fill($L, 'Gọi số ___ khi có cháy do chập điện.', [[0, '114']], '114 là số cứu hỏa, gọi ngay khi có cháy do chập điện.', $D);
        $this->fill($L, 'Sự cố điện phải do ___ điện kiểm tra xong mới được dùng lại.', [[0, 'thợ']], 'Chỉ thợ điện mới được kiểm tra và sửa chữa sự cố điện.', $D);
    }

    // ===== 10. cn-an-toan-dien-lop-7-2 (Lop 7, trung_binh) =====
    private function seedCnAnToanDienLop72(): void
    {
        $L = 'cn-an-toan-dien-lop-7-2'; $D = 'trung_binh';
        $this->quiz($L, 'Ngoài dùng bóng LED, cách nào giúp điều hòa tốn ít điện hơn?', ['Đóng kín cửa và vệ sinh máy định kỳ', 'Mở cửa cho thoáng khi bật', 'Để nhiệt độ 18 độ', 'Bật điều hòa cả ngày'], 0, 'Đóng kín cửa, để 26-27 độ và vệ sinh định kỳ giúp điều hòa tiết kiệm điện.', $D);
        $this->quiz($L, 'Vì sao nên chọn thiết bị có nhãn năng lượng nhiều sao?', ['Vì càng nhiều sao càng tiết kiệm điện', 'Vì nhiều sao đẹp hơn', 'Vì nhiều sao đắt hơn', 'Vì nhiều sao nặng hơn'], 0, 'Nhãn năng lượng càng nhiều sao thì thiết bị càng tiết kiệm điện.', $D);
        $this->quiz($L, 'Tivi để ở chế độ chờ (đèn đỏ) có tốn điện không?', ['Có tốn điện nên rút phích khi không dùng', 'Không tốn chút nào', 'Chỉ tốn khi xem', 'Chế độ chờ còn tốn hơn khi xem'], 0, 'Chế độ chờ vẫn tốn điện nên rút phích tivi khi không dùng lâu.', $D);
        $this->quiz($L, 'Giờ cao điểm dùng điện trong ngày thường là khi nào?', ['Buổi tối khi mọi nhà cùng dùng nhiều', 'Lúc nửa đêm', 'Sáng sớm tinh mơ', 'Giữa trưa nắng'], 0, 'Buổi tối là giờ cao điểm, nên hạn chế dùng nhiều thiết bị cùng lúc.', $D);
        $this->quiz($L, 'Tiết kiệm điện góp phần bảo vệ môi trường vì sao?', ['Vì giảm đốt than, dầu nên giảm khí thải', 'Vì điện không liên quan môi trường', 'Vì tiết kiệm điện làm trời mát', 'Vì nhà máy điện thích thế'], 0, 'Phần lớn điện từ đốt than, dầu nên tiết kiệm điện giúp giảm khí thải ô nhiễm.', $D);
        $this->matching($L, 'Nối mỗi thiết bị với cách dùng tiết kiệm điện.', [['Điều hòa', 'Để 26-27 độ'], ['Tủ lạnh', 'Không mở cửa lâu'], ['Máy giặt', 'Giặt đủ tải'], ['Bàn là', 'Là nhiều đồ một lúc']], 'Dùng thiết bị đúng cách giúp tiết kiệm điện đáng kể mỗi tháng.', $D);
        $this->matching($L, 'Nối mỗi nhãn năng lượng với ý nghĩa của nó.', [['5 sao', 'Rất tiết kiệm điện'], ['3 sao', 'Tiết kiệm trung bình'], ['1 sao', 'Ít tiết kiệm'], ['Không có nhãn', 'Chưa được đánh giá']], 'Chọn thiết bị nhiều sao năng lượng để tiết kiệm điện lâu dài.', $D);
        $this->matching($L, 'Nối mỗi khung giờ với cách dùng điện hợp lý.', [['Giờ cao điểm buổi tối', 'Hạn chế dùng nhiều'], ['Ban ngày', 'Tận dụng ánh sáng tự nhiên'], ['Đêm khuya', 'Tắt bớt thiết bị'], ['Cuối tuần', 'Giặt ủi tập trung']], 'Dùng điện hợp lý theo khung giờ giúp giảm quá tải và tiết kiệm.', $D);
        $this->matching($L, 'Nối mỗi hành động với mức độ tiết kiệm điện.', [['Tắt đèn khi ra ngoài', 'Tiết kiệm nhiều'], ['Rút sạc khi pin đầy', 'Tiết kiệm ít nhưng nên làm'], ['Để tivi chờ cả ngày', 'Lãng phí'], ['Mở tủ lạnh thật lâu', 'Lãng phí']], 'Mọi hành động nhỏ đều góp phần tiết kiệm hoặc lãng phí điện.', $D);
        $this->matching($L, 'Nối mỗi nguồn điện với đặc điểm của nó.', [['Điện mặt trời', 'Sạch và tái tạo'], ['Điện gió', 'Sạch và tái tạo'], ['Nhiệt điện than', 'Gây ô nhiễm'], ['Thủy điện', 'Tái tạo được']], 'Điện mặt trời, gió sạch và tái tạo; nhiệt điện than gây ô nhiễm.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TIẾT KIỆM hoặc LÃNG PHÍ điện.', [['Dùng bóng đèn LED', 'TIẾT KIỆM'], ['Tắt quạt khi ra ngoài', 'TIẾT KIỆM'], ['Bật đèn giữa ban ngày', 'LÃNG PHÍ'], ['Mở điều hòa mà mở cửa', 'LÃNG PHÍ']], 'Dùng LED và tắt đồ không dùng là tiết kiệm; bật đèn ban ngày là lãng phí.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm NÊN RÚT PHÍCH hoặc ĐỂ NGUYÊN khi không dùng lâu.', [['Sạc điện thoại', 'NÊN RÚT PHÍCH'], ['Tivi', 'NÊN RÚT PHÍCH'], ['Máy tính', 'NÊN RÚT PHÍCH'], ['Tủ lạnh', 'ĐỂ NGUYÊN']], 'Tủ lạnh cần chạy liên tục; các thiết bị khác không dùng lâu nên rút phích.', $D);
        $this->sortQ($L, 'Kéo mỗi nguồn năng lượng vào nhóm TÁI TẠO hoặc KHÔNG TÁI TẠO.', [['Mặt trời', 'TÁI TẠO'], ['Gió', 'TÁI TẠO'], ['Than đá', 'KHÔNG TÁI TẠO'], ['Dầu mỏ', 'KHÔNG TÁI TẠO']], 'Mặt trời, gió tái tạo được; than đá, dầu mỏ dùng hết là hết.', $D);
        $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm TỐT hoặc CHƯA TỐT.', [['Tận dụng ánh sáng tự nhiên', 'TỐT'], ['Vệ sinh điều hòa định kỳ', 'TỐT'], ['Là từng chiếc áo một', 'CHƯA TỐT'], ['Để máy tính bật cả đêm', 'CHƯA TỐT']], 'Tận dụng ánh sáng tự nhiên và gom đồ để là giúp tiết kiệm điện.', $D);
        $this->sortQ($L, 'Kéo mỗi mức sao năng lượng vào nhóm TIẾT KIỆM NHIỀU hoặc TIẾT KIỆM ÍT.', [['5 sao', 'TIẾT KIỆM NHIỀU'], ['4 sao', 'TIẾT KIỆM NHIỀU'], ['2 sao', 'TIẾT KIỆM ÍT'], ['1 sao', 'TIẾT KIỆM ÍT']], 'Thiết bị 4-5 sao tiết kiệm nhiều điện hơn thiết bị 1-2 sao.', $D);
        $this->fill($L, 'Thiết bị dán nhãn ___ sao năng lượng là loại tiết kiệm điện nhất.', [[0, '5']], 'Nhãn 5 sao là mức tiết kiệm điện cao nhất hiện nay.', $D);
        $this->fill($L, 'Tivi ở chế độ ___ vẫn tốn điện nên cần rút phích khi không dùng.', [[0, 'chờ']], 'Chế độ chờ vẫn tiêu thụ điện nên rút phích để tiết kiệm.', $D);
        $this->fill($L, 'Nên hạn chế dùng nhiều điện vào giờ ___ điểm buổi tối.', [[0, 'cao']], 'Giờ cao điểm lưới điện quá tải, hạn chế dùng giúp ổn định điện.', $D);
        $this->fill($L, 'Tiết kiệm điện giúp giảm đốt than, dầu để bảo vệ ___ trường.', [[0, 'môi']], 'Ít đốt nhiên liệu hóa thạch thì ít khí thải, môi trường sạch hơn.', $D);
        $this->fill($L, 'Điều hòa nên vệ sinh ___ kỳ để chạy êm và ít tốn điện.', [[0, 'định']], 'Vệ sinh định kỳ giúp điều hòa làm lạnh tốt và tiết kiệm điện.', $D);
    }

    // ===== 11. cn-vat-lieu-dung-cu-lop-7-1 (Lop 7, de) =====
    private function seedCnVatLieuDungCuLop71(): void
    {
        $L = 'cn-vat-lieu-dung-cu-lop-7-1'; $D = 'de';
        $this->quiz($L, 'Thép là hợp kim của sắt với nguyên tố nào?', ['Cacbon', 'Nhôm', 'Đồng', 'Kẽm'], 0, 'Thép là hợp kim của sắt và cacbon nên cứng hơn sắt rất nhiều.', $D);
        $this->quiz($L, 'Vì sao xoong nồi thường được làm bằng nhôm?', ['Vì nhôm nhẹ và dẫn nhiệt tốt', 'Vì nhôm rất rẻ', 'Vì nhôm đẹp mắt', 'Vì nhôm không cần rửa'], 0, 'Nhôm nhẹ, dẫn nhiệt tốt nên nấu nhanh và dễ nhấc khi nấu ăn.', $D);
        $this->quiz($L, 'Cao su có tính chất nổi bật nào?', ['Đàn hồi và cách điện tốt', 'Cứng và giòn', 'Dẫn điện rất tốt', 'Trong suốt'], 0, 'Cao su đàn hồi, cách điện nên dùng làm lốp xe, găng tay cách điện.', $D);
        $this->quiz($L, 'Thủy tinh có những tính chất nào?', ['Trong suốt, giòn và cách điện', 'Dẻo và dẫn điện', 'Mềm và thấm nước', 'Đục và dẫn nhiệt tốt'], 0, 'Thủy tinh trong suốt nhưng giòn, dễ vỡ và không dẫn điện.', $D);
        $this->quiz($L, 'Vật liệu nào vừa nhẹ vừa không gỉ, thường dùng làm khung cửa?', ['Nhôm', 'Sắt', 'Gang', 'Chì'], 0, 'Nhôm nhẹ, không gỉ nên rất phù hợp làm khung cửa, khung kính.', $D);
        $this->matching($L, 'Nối mỗi kim loại với tính chất nổi bật của nó.', [['Sắt', 'Cứng nhưng dễ gỉ'], ['Nhôm', 'Nhẹ và không gỉ'], ['Đồng', 'Dẫn điện tốt'], ['Inox', 'Sáng bóng, khó gỉ']], 'Mỗi kim loại có tính chất riêng phù hợp với từng công dụng.', $D);
        $this->matching($L, 'Nối mỗi vật liệu phi kim với ứng dụng của nó.', [['Gỗ', 'Bàn ghế'], ['Nhựa', 'Chai lọ'], ['Cao su', 'Lốp xe'], ['Thủy tinh', 'Cốc chén']], 'Vật liệu phi kim như gỗ, nhựa, cao su, thủy tinh có nhiều ứng dụng đời sống.', $D);
        $this->matching($L, 'Nối mỗi đồ dùng học tập với vật liệu làm ra nó.', [['Thước kẻ', 'Nhựa'], ['Compa', 'Kim loại'], ['Bút chì', 'Gỗ'], ['Cục tẩy', 'Cao su']], 'Đồ dùng học tập được làm từ vật liệu phù hợp với công dụng.', $D);
        $this->matching($L, 'Nối mỗi tính chất với vật liệu có tính chất đó.', [['Trong suốt', 'Thủy tinh'], ['Đàn hồi', 'Cao su'], ['Dễ dát mỏng', 'Nhôm'], ['Chịu nhiệt tốt', 'Gốm sứ']], 'Nhận biết tính chất giúp chọn đúng vật liệu cho từng mục đích.', $D);
        $this->matching($L, 'Nối mỗi vật liệu với cách nhận biết đơn giản.', [['Sắt', 'Nam châm hút được'], ['Nhôm', 'Nhẹ, màu trắng bạc'], ['Đồng', 'Màu đỏ đặc trưng'], ['Nhựa', 'Nhẹ, sờ không lạnh tay']], 'Có thể nhận biết vật liệu qua màu sắc, khối lượng và từ tính.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm KIM LOẠI hoặc PHI KIM.', [['Sắt', 'KIM LOẠI'], ['Đồng', 'KIM LOẠI'], ['Gỗ', 'PHI KIM'], ['Nhựa', 'PHI KIM']], 'Sắt, đồng là kim loại; gỗ, nhựa là phi kim.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm DẪN NHIỆT TỐT hoặc DẪN NHIỆT KÉM.', [['Nhôm', 'DẪN NHIỆT TỐT'], ['Đồng', 'DẪN NHIỆT TỐT'], ['Gỗ', 'DẪN NHIỆT KÉM'], ['Nhựa', 'DẪN NHIỆT KÉM']], 'Kim loại dẫn nhiệt tốt nên làm xoong nồi; gỗ, nhựa dẫn nhiệt kém nên làm tay cầm.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm CỨNG GIÒN hoặc MỀM DẺO.', [['Thủy tinh', 'CỨNG GIÒN'], ['Gốm sứ', 'CỨNG GIÒN'], ['Cao su', 'MỀM DẺO'], ['Nhựa dẻo', 'MỀM DẺO']], 'Thủy tinh, gốm sứ cứng nhưng giòn dễ vỡ; cao su, nhựa dẻo mềm dẻo.', $D);
        $this->sortQ($L, 'Kéo mỗi đồ vật vào nhóm CHỊU NHIỆT hoặc KHÔNG CHỊU NHIỆT.', [['Xoong nhôm', 'CHỊU NHIỆT'], ['Cốc thủy tinh chịu nhiệt', 'CHỊU NHIỆT'], ['Chai nhựa', 'KHÔNG CHỊU NHIỆT'], ['Hộp xốp', 'KHÔNG CHỊU NHIỆT']], 'Xoong nhôm và cốc chịu nhiệt dùng được với nhiệt độ cao; nhựa, xốp thì không.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm TÁI CHẾ ĐƯỢC hoặc KHÓ TÁI CHẾ.', [['Nhôm', 'TÁI CHẾ ĐƯỢC'], ['Giấy', 'TÁI CHẾ ĐƯỢC'], ['Thủy tinh', 'TÁI CHẾ ĐƯỢC'], ['Túi ni lông mỏng bẩn', 'KHÓ TÁI CHẾ']], 'Nhôm, giấy, thủy tinh tái chế được nhiều lần; ni lông mỏng bẩn khó tái chế.', $D);
        $this->fill($L, 'Thép là hợp kim của sắt và ___ nên cứng hơn sắt nhiều.', [[0, 'cacbon']], 'Thêm cacbon vào sắt tạo thành thép cứng và bền hơn.', $D);
        $this->fill($L, 'Xoong nồi làm bằng nhôm vì nhẹ và dẫn ___ tốt.', [[0, 'nhiệt']], 'Nhôm dẫn nhiệt tốt nên nấu ăn nhanh chín và tiết kiệm nhiên liệu.', $D);
        $this->fill($L, 'Lốp xe làm bằng cao su vì cao su có tính đàn ___.', [[0, 'hồi']], 'Tính đàn hồi giúp lốp xe êm và bám đường tốt.', $D);
        $this->fill($L, 'Thước nhựa không dẫn điện nên ___ toàn khi dùng gần ổ điện.', [[0, 'an']], 'Dụng cụ bằng nhựa cách điện nên an toàn hơn khi dùng gần nguồn điện.', $D);
        $this->fill($L, 'Cốc thủy tinh trong ___ nên nhìn rõ nước bên trong.', [[0, 'suốt']], 'Thủy tinh trong suốt nên được dùng làm cốc, chai lọ.', $D);
    }

    // ===== 12. cn-vat-lieu-dung-cu-lop-7-2 (Lop 7, de) =====
    private function seedCnVatLieuDungCuLop72(): void
    {
        $L = 'cn-vat-lieu-dung-cu-lop-7-2'; $D = 'de';
        $this->quiz($L, 'Thước cuộn (thước dây) thường dùng để đo gì?', ['Đo độ dài lớn như chiều dài sân, phòng', 'Đo độ dày tờ giấy', 'Đo đường kính viên bi', 'Đo nhiệt độ'], 0, 'Thước cuộn dài nhiều mét, dùng đo độ dài lớn như sân trường, căn phòng.', $D);
        $this->quiz($L, 'Thước kẹp dùng để đo gì?', ['Đo chi tiết nhỏ với độ chính xác cao', 'Đo chiều dài sân bóng', 'Đo cân nặng', 'Đo thời gian'], 0, 'Thước kẹp đo được chi tiết nhỏ chính xác đến từng phần mười milimét.', $D);
        $this->quiz($L, 'Khi cần vẽ một đường thẳng dài trên giấy, nên dùng dụng cụ nào?', ['Thước thẳng', 'Compa', 'Êke', 'Bút lông'], 0, 'Thước thẳng dùng để kẻ các đường thẳng dài và chính xác.', $D);
        $this->quiz($L, 'Bút đánh dấu (bút lông) khác bút chì ở điểm nào?', ['Nét đậm, khó tẩy, giữ dấu lâu', 'Nét mờ dễ tẩy', 'Viết được dưới nước', 'Không bao giờ hết mực'], 0, 'Bút đánh dấu cho nét đậm khó phai, dùng khi cần dấu lâu dài.', $D);
        $this->quiz($L, 'Vì sao khi đo độ dài phải đặt mắt nhìn vuông góc với vạch thước?', ['Để tránh đọc sai số đo', 'Để thước không bị gãy', 'Để nhìn cho đẹp', 'Để thước sáng hơn'], 0, 'Nhìn xiên vạch thước sẽ đọc sai số; nhìn vuông góc cho số đo chính xác.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ đo với đại lượng mà nó đo.', [['Thước dây', 'Chiều dài'], ['Cân đồng hồ', 'Khối lượng'], ['Ca đong', 'Thể tích'], ['Nhiệt kế', 'Nhiệt độ']], 'Mỗi dụng cụ đo một đại lượng khác nhau: dài, nặng, thể tích, nhiệt độ.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ vẽ với công dụng của nó.', [['Compa', 'Vẽ đường tròn'], ['Êke', 'Vẽ góc vuông'], ['Thước thẳng', 'Kẻ đường thẳng'], ['Thước đo góc', 'Đo góc']], 'Compa vẽ tròn, êke vẽ vuông góc, thước thẳng kẻ đường thẳng.', $D);
        $this->matching($L, 'Nối mỗi đơn vị đo độ dài với kí hiệu của nó.', [['Mét', 'm'], ['Xentimét', 'cm'], ['Milimét', 'mm'], ['Kilômét', 'km']], 'Ghi nhớ kí hiệu các đơn vị đo độ dài: m, cm, mm, km.', $D);
        $this->matching($L, 'Nối mỗi thao tác đo với đánh giá đúng hay sai.', [['Đặt thước sát vào vật', 'Đúng'], ['Đo nhiều lần lấy trung bình', 'Đúng'], ['Nhìn nghiêng vạch thước', 'Sai'], ['Đoán chừng bằng mắt', 'Sai']], 'Đo đúng là đặt thước sát vật, nhìn vuông góc và đo nhiều lần.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ với môn học hay dùng nó nhất.', [['Compa', 'Toán'], ['Thước kẹp', 'Công nghệ'], ['Ống nghiệm', 'Hóa học'], ['Kính lúp', 'Sinh học']], 'Mỗi môn học có dụng cụ đặc trưng phục vụ thí nghiệm, thực hành.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm ĐO CHÍNH XÁC CAO hoặc ĐO THÔNG THƯỜNG.', [['Thước kẹp', 'ĐO CHÍNH XÁC CAO'], ['Panme', 'ĐO CHÍNH XÁC CAO'], ['Thước kẻ học sinh', 'ĐO THÔNG THƯỜNG'], ['Thước dây', 'ĐO THÔNG THƯỜNG']], 'Thước kẹp, panme đo chính xác cao; thước kẻ, thước dây đo thông thường.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn vị vào nhóm ĐƠN VỊ ĐỘ DÀI hoặc ĐƠN VỊ KHÁC.', [['Mét', 'ĐƠN VỊ ĐỘ DÀI'], ['Xentimét', 'ĐƠN VỊ ĐỘ DÀI'], ['Kilôgam', 'ĐƠN VỊ KHÁC'], ['Giờ', 'ĐƠN VỊ KHÁC']], 'Mét, xentimét đo độ dài; kilôgam đo khối lượng; giờ đo thời gian.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN khi bảo quản dụng cụ đo.', [['Lau sạch sau khi dùng', 'NÊN'], ['Để thước nơi khô ráo', 'NÊN'], ['Bẻ cong thước', 'KHÔNG NÊN'], ['Làm rơi thước kẹp', 'KHÔNG NÊN']], 'Giữ dụng cụ đo sạch, khô và tránh va đập để đo luôn chính xác.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm DÙNG ĐỂ ĐO, VẼ hoặc CẮT.', [['Thước dây', 'DÙNG ĐỂ ĐO'], ['Compa', 'DÙNG ĐỂ VẼ'], ['Kéo', 'DÙNG ĐỂ CẮT'], ['Êke', 'DÙNG ĐỂ VẼ']], 'Thước dây để đo, compa êke để vẽ, kéo để cắt.', $D);
        $this->sortQ($L, 'Kéo mỗi cách đọc số đo vào nhóm ĐÚNG hoặc SAI.', [['Mắt nhìn vuông góc vạch chia', 'ĐÚNG'], ['Đọc số gần vạch nhất', 'ĐÚNG'], ['Nhìn xiên từ một phía', 'SAI'], ['Đoán số liệu cho nhanh', 'SAI']], 'Đọc đúng là nhìn vuông góc và đọc số gần vạch nhất.', $D);
        $this->fill($L, 'Thước ___ dùng để đo độ dài lớn như chiều dài sân trường.', [[0, 'cuộn']], 'Thước cuộn dài nhiều mét, tiện đo những khoảng dài.', $D);
        $this->fill($L, 'Muốn đo đường kính viên bi thật chính xác, nên dùng thước ___.', [[0, 'kẹp']], 'Thước kẹp đo chi tiết nhỏ với độ chính xác rất cao.', $D);
        $this->fill($L, 'Khi đo, mắt phải nhìn ___ góc với mặt thước.', [[0, 'vuông']], 'Nhìn vuông góc tránh sai số khi đọc số đo.', $D);
        $this->fill($L, 'Đo nhiều lần rồi lấy giá trị trung ___ cho kết quả chính xác hơn.', [[0, 'bình']], 'Lấy trung bình nhiều lần đo giúp giảm sai số ngẫu nhiên.', $D);
        $this->fill($L, 'Vạch số 0 của thước phải đặt ___ với một đầu của vật cần đo.', [[0, 'trùng']], 'Đặt vạch 0 trùng đầu vật thì số đo mới chính xác.', $D);
    }

    // ===== 13. cn-vat-lieu-dung-cu-lop-8-1 (Lop 8, trung_binh) =====
    private function seedCnVatLieuDungCuLop81(): void
    {
        $L = 'cn-vat-lieu-dung-cu-lop-8-1'; $D = 'trung_binh';
        $this->quiz($L, 'Mỏ lết dùng để làm gì?', ['Vặn các loại bu lông, đai ốc nhiều cỡ khác nhau', 'Đóng đinh vào tường', 'Cưa gỗ thành tấm', 'Đo độ dài'], 0, 'Mỏ lết điều chỉnh được độ mở nên vặn được nhiều cỡ bu lông, đai ốc.', $D);
        $this->quiz($L, 'Đục gỗ dùng để làm gì?', ['Đục lỗ, gọt đẽo trên gỗ', 'Vặn ốc vít', 'Cắt dây điện', 'Đo góc vuông'], 0, 'Đục có lưỡi sắc, dùng búa gõ để đục lỗ và gọt đẽo gỗ.', $D);
        $this->quiz($L, 'Vì sao lưỡi cưa có răng cưa?', ['Vì răng cưa giúp cắt vật liệu dễ dàng hơn', 'Vì cho đẹp mắt', 'Vì để cưa nặng hơn', 'Vì để cưa không bị gỉ'], 0, 'Răng cưa sắc nhọn giúp lưỡi cưa ăn sâu và cắt vật liệu dễ dàng.', $D);
        $this->quiz($L, 'Khi dùng khoan tay để khoan gỗ, cần chú ý gì để an toàn?', ['Kẹp chặt vật, đeo kính bảo hộ', 'Khoan thật nhanh cho xong', 'Nhờ bạn giữ mũi khoan', 'Khoan khi vật đang lung lay'], 0, 'Khoan phải kẹp chặt vật, đeo kính bảo hộ tránh mạt văng vào mắt.', $D);
        $this->quiz($L, 'Dũa dùng để làm gì trong gia công?', ['Làm nhẵn, mài bớt cạnh sắc của vật liệu', 'Đóng đinh', 'Vặn ốc', 'Đo độ dài'], 0, 'Dũa có bề mặt nhám, dùng làm nhẵn và mài bớt cạnh sắc sau khi cắt.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ với công dụng chi tiết của nó.', [['Mỏ lết', 'Vặn đai ốc nhiều cỡ'], ['Đục gỗ', 'Đục lỗ trên gỗ'], ['Dũa', 'Làm nhẵn bề mặt'], ['Êtô', 'Kẹp giữ vật']], 'Mỗi dụng cụ cơ khí có công dụng chuyên biệt trong gia công.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ cắt với vật liệu gia công phù hợp.', [['Cưa sắt', 'Thanh kim loại'], ['Cưa gỗ', 'Tấm ván'], ['Khoan tay', 'Gỗ mỏng'], ['Kéo cắt tôn', 'Tấm kim loại mỏng']], 'Chọn dụng cụ cắt phù hợp với từng loại vật liệu.', $D);
        $this->matching($L, 'Nối mỗi bộ phận của khoan tay với nhiệm vụ của nó.', [['Mũi khoan', 'Tạo lỗ'], ['Tay quay', 'Tạo lực quay'], ['Bầu cặp', 'Giữ chặt mũi khoan'], ['Tay cầm', 'Giữ khoan chắc']], 'Các bộ phận của khoan tay phối hợp để tạo lỗ trên vật liệu.', $D);
        $this->matching($L, 'Nối mỗi loại kìm với công dụng riêng của nó.', [['Kìm cắt', 'Cắt dây kim loại'], ['Kìm nhọn', 'Kẹp chi tiết nhỏ'], ['Kìm bấm cos', 'Bấm đầu dây điện'], ['Kìm mỏ quạ', 'Kẹp ống tròn']], 'Có nhiều loại kìm chuyên dụng cho từng công việc khác nhau.', $D);
        $this->matching($L, 'Nối mỗi sai lầm với hậu quả khi dùng dụng cụ.', [['Dùng tua vít làm đục', 'Hỏng tua vít'], ['Cầm lưỡi cưa bằng tay không', 'Đứt tay'], ['Đóng búa không đeo kính', 'Mạt văng vào mắt'], ['Để dụng cụ bừa bãi', 'Dễ gây tai nạn']], 'Dùng sai dụng cụ và bất cẩn gây hỏng đồ và tai nạn.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm GIA CÔNG GỖ hoặc GIA CÔNG KIM LOẠI.', [['Cưa gỗ', 'GIA CÔNG GỖ'], ['Đục gỗ', 'GIA CÔNG GỖ'], ['Cưa sắt', 'GIA CÔNG KIM LOẠI'], ['Dũa kim loại', 'GIA CÔNG KIM LOẠI']], 'Dụng cụ gia công gỗ khác dụng cụ gia công kim loại về độ cứng lưỡi.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm DÙNG TAY hoặc DÙNG ĐIỆN.', [['Tua vít', 'DÙNG TAY'], ['Búa', 'DÙNG TAY'], ['Máy khoan', 'DÙNG ĐIỆN'], ['Máy mài', 'DÙNG ĐIỆN']], 'Dụng cụ cầm tay dùng sức người; máy dùng điện mạnh và nhanh hơn.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm AN TOÀN hoặc NGUY HIỂM khi khoan.', [['Kẹp chặt vật cần khoan', 'AN TOÀN'], ['Đeo kính bảo hộ', 'AN TOÀN'], ['Giữ vật bằng tay không', 'NGUY HIỂM'], ['Khoan khi mũi khoan bị lỏng', 'NGUY HIỂM']], 'Khoan an toàn là kẹp chặt vật, đeo kính và kiểm tra mũi khoan chắc chắn.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm CẮT GỌT hoặc ĐO ĐẠC.', [['Dao rọc giấy', 'CẮT GỌT'], ['Dũa', 'CẮT GỌT'], ['Thước kẹp', 'ĐO ĐẠC'], ['Thước dây', 'ĐO ĐẠC']], 'Dao, dũa để cắt gọt; thước kẹp, thước dây để đo đạc.', $D);
        $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm ĐÚNG hoặc SAI kỹ thuật cưa.', [['Cưa theo đường đã đánh dấu', 'ĐÚNG'], ['Đẩy cưa nhẹ nhàng đều tay', 'ĐÚNG'], ['Cưa giật mạnh không giữ vật', 'SAI'], ['Dùng cưa cùn rỉ sét', 'SAI']], 'Cưa đúng là theo dấu, đều tay; cưa giật mạnh và cưa cùn là sai.', $D);
        $this->fill($L, 'Mỏ ___ dùng để vặn các loại bu lông, đai ốc nhiều cỡ khác nhau.', [[0, 'lết']], 'Mỏ lết điều chỉnh được độ mở nên rất tiện khi vặn nhiều cỡ ốc.', $D);
        $this->fill($L, '___ dùng để kẹp giữ vật thật chặt khi gia công.', [[0, 'Êtô']], 'Êtô kẹp chặt vật giúp gia công chính xác và an toàn.', $D);
        $this->fill($L, 'Lưỡi cưa có ___ cưa sắc giúp cắt vật liệu dễ dàng.', [[0, 'răng']], 'Răng cưa càng sắc thì cắt càng nhanh và nhẹ tay.', $D);
        $this->fill($L, 'Khi khoan phải đeo ___ bảo hộ để mạt không văng vào mắt.', [[0, 'kính']], 'Kính bảo hộ là đồ bảo hộ bắt buộc khi khoan, mài, cắt.', $D);
        $this->fill($L, 'Dũa dùng để làm ___ bề mặt kim loại sau khi cắt.', [[0, 'nhẵn']], 'Dũa làm nhẵn cạnh sắc, giúp sản phẩm đẹp và an toàn khi cầm.', $D);
    }

    // ===== 14. cn-vat-lieu-dung-cu-lop-8-2 (Lop 8, trung_binh) =====
    private function seedCnVatLieuDungCuLop82(): void
    {
        $L = 'cn-vat-lieu-dung-cu-lop-8-2'; $D = 'trung_binh';
        $this->quiz($L, 'Vì sao nên đo đạc kỹ trước khi cắt vật liệu?', ['Để tránh cắt sai gây lãng phí vật liệu', 'Để tốn nhiều thời gian hơn', 'Để vật liệu đẹp hơn', 'Để không cần dùng thước'], 0, 'Đo đạc kỹ trước khi cắt giúp cắt chính xác, tránh lãng phí vật liệu.', $D);
        $this->quiz($L, 'Giấy vụn, báo cũ có thể tái chế thành gì?', ['Giấy mới, bìa carton', 'Quần áo', 'Đồ nhựa', 'Thủy tinh'], 0, 'Giấy vụn được tái chế thành giấy mới, bìa carton giúp tiết kiệm gỗ.', $D);
        $this->quiz($L, 'Vỏ chai nhựa PET sau tái chế có thể làm thành gì?', ['Sợi vải may quần áo', 'Thực phẩm', 'Thuốc uống', 'Phân bón'], 0, 'Chai nhựa PET tái chế thành sợi vải, vừa giảm rác vừa tiết kiệm tài nguyên.', $D);
        $this->quiz($L, 'Rác hữu cơ như rau củ thừa nên xử lý thế nào là tốt nhất?', ['Ủ thành phân compost bón cây', 'Đốt bừa bãi', 'Vứt xuống sông', 'Chôn lẫn pin cũ'], 0, 'Ủ rác hữu cơ thành phân compost vừa giảm rác vừa có phân bón sạch.', $D);
        $this->quiz($L, 'Học sinh tiết kiệm vật liệu trong học tập bằng việc nào?', ['Dùng giấy hai mặt, giữ gìn đồ dùng', 'Xé giấy làm đồ chơi rồi vứt', 'Mua nhiều bút rồi bỏ', 'Vứt thước kẻ còn dùng được'], 0, 'Dùng giấy hai mặt và giữ gìn đồ dùng là cách tiết kiệm vật liệu đơn giản.', $D);
        $this->matching($L, 'Nối mỗi vật liệu phế thải với sản phẩm tái chế từ nó.', [['Giấy vụn', 'Giấy tái chế'], ['Chai nhựa', 'Sợi vải'], ['Lon nhôm', 'Nhôm mới'], ['Vỏ hộp thiếc', 'Thép tái chế']], 'Nhiều phế thải có thể tái chế thành sản phẩm mới có ích.', $D);
        $this->matching($L, 'Nối mỗi loại rác với cách bỏ đúng.', [['Rác tái chế', 'Thùng riêng để tái chế'], ['Rác hữu cơ', 'Ủ làm phân'], ['Pin cũ', 'Điểm thu gom riêng'], ['Rác còn lại', 'Thùng rác thường']], 'Phân loại rác giúp tái chế dễ dàng và xử lý rác nguy hại an toàn.', $D);
        $this->matching($L, 'Nối mỗi việc tiết kiệm với lợi ích của nó.', [['Dùng giấy hai mặt', 'Giảm chặt cây'], ['Tái chế nhôm', 'Tiết kiệm quặng'], ['Ủ rác hữu cơ', 'Có phân bón sạch'], ['Sửa đồ hỏng', 'Giảm rác thải']], 'Tiết kiệm và tái chế giúp bảo vệ tài nguyên và môi trường.', $D);
        $this->matching($L, 'Nối mỗi vật liệu với thời gian phân hủy ước tính.', [['Giấy', 'Vài tuần'], ['Vỏ chuối', 'Vài tuần'], ['Túi ni lông', 'Hàng trăm năm'], ['Chai thủy tinh', 'Hàng nghìn năm']], 'Ni lông và thủy tinh phân hủy rất lâu nên cần hạn chế và tái chế.', $D);
        $this->matching($L, 'Nối mỗi hành vi với đánh giá nên hay không nên.', [['Phân loại rác tại nhà', 'Nên làm'], ['Mang túi vải đi chợ', 'Nên làm'], ['Đốt rác bừa bãi', 'Không nên'], ['Vứt pin vào thùng rác thường', 'Không nên']], 'Phân loại rác và dùng túi vải là việc nên làm; đốt rác bừa bãi thì không.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TIẾT KIỆM hoặc LÃNG PHÍ vật liệu.', [['Dùng giấy hai mặt', 'TIẾT KIỆM'], ['Giữ gìn bút thước', 'TIẾT KIỆM'], ['Xé giấy làm đồ chơi rồi vứt', 'LÃNG PHÍ'], ['Mua đồ dùng rồi bỏ phí', 'LÃNG PHÍ']], 'Dùng giấy hai mặt và giữ gìn đồ dùng là tiết kiệm; xé vứt bừa bãi là lãng phí.', $D);
        $this->sortQ($L, 'Kéo mỗi vật vào nhóm TÁI CHẾ ĐƯỢC hoặc KHÓ TÁI CHẾ.', [['Chai nhựa sạch', 'TÁI CHẾ ĐƯỢC'], ['Giấy báo cũ', 'TÁI CHẾ ĐƯỢC'], ['Băng keo dính bẩn', 'KHÓ TÁI CHẾ'], ['Túi ni lông bẩn', 'KHÓ TÁI CHẾ']], 'Vật sạch như chai nhựa, giấy báo tái chế được; đồ bẩn dính khó tái chế.', $D);
        $this->sortQ($L, 'Kéo mỗi loại rác vào nhóm RÁC HỮU CƠ hoặc RÁC VÔ CƠ.', [['Vỏ rau củ', 'RÁC HỮU CƠ'], ['Cơm thừa', 'RÁC HỮU CƠ'], ['Chai nhựa', 'RÁC VÔ CƠ'], ['Lon bia', 'RÁC VÔ CƠ']], 'Rau củ, cơm thừa là hữu cơ; nhựa, kim loại là vô cơ.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN khi dùng vật liệu.', [['Tận dụng vật liệu thừa', 'NÊN'], ['Sửa chữa đồ còn dùng được', 'NÊN'], ['Cắt bừa không đo đạc', 'KHÔNG NÊN'], ['Vứt vật liệu còn dùng được', 'KHÔNG NÊN']], 'Tận dụng và sửa chữa là nên; cắt bừa và vứt phí là không nên.', $D);
        $this->sortQ($L, 'Kéo mỗi sản phẩm vào nhóm TỪ VẬT LIỆU TÁI CHẾ hoặc TỪ NGUYÊN LIỆU MỚI.', [['Giấy tái chế', 'TỪ VẬT LIỆU TÁI CHẾ'], ['Vải từ chai PET', 'TỪ VẬT LIỆU TÁI CHẾ'], ['Chai nhựa mới', 'TỪ NGUYÊN LIỆU MỚI'], ['Túi giấy kraft mới', 'TỪ NGUYÊN LIỆU MỚI']], 'Dùng sản phẩm tái chế giúp giảm khai thác tài nguyên mới.', $D);
        $this->fill($L, 'Rác hữu cơ có thể ủ thành phân ___ cơ để bón cho cây.', [[0, 'hữu']], 'Phân hữu cơ từ rác ủ vừa sạch vừa tốt cho đất.', $D);
        $this->fill($L, 'Chai nhựa PET tái chế có thể kéo thành ___ để may quần áo.', [[0, 'sợi']], 'Sợi vải từ chai PET là ứng dụng tái chế nhựa phổ biến.', $D);
        $this->fill($L, 'Đo đạc cẩn thận trước khi cắt giúp tránh ___ phí vật liệu.', [[0, 'lãng']], 'Cắt sai phải bỏ đi gây lãng phí vật liệu và công sức.', $D);
        $this->fill($L, 'Dùng giấy hai mặt là cách tiết kiệm ___ đơn giản nhất.', [[0, 'giấy']], 'Viết hai mặt giấy giúp giảm một nửa lượng giấy tiêu thụ.', $D);
        $this->fill($L, 'Đồ điện tử hỏng chứa chất độc, phải thu gom ___ không vứt bừa bãi.', [[0, 'riêng']], 'Rác điện tử là rác nguy hại, phải thu gom riêng để xử lý.', $D);
    }

    // ===== 15. cn-trong-trot-lop-8-1 (Lop 8, trung_binh) =====
    private function seedCnTrongTrotLop81(): void
    {
        $L = 'cn-trong-trot-lop-8-1'; $D = 'trung_binh';
        $this->quiz($L, 'Mật độ gieo trồng là gì?', ['Số cây trồng trên một đơn vị diện tích', 'Số hạt trong một quả', 'Chiều cao của cây', 'Số lá trên một cây'], 0, 'Mật độ gieo trồng hợp lý giúp mỗi cây đủ ánh sáng và dinh dưỡng.', $D);
        $this->quiz($L, 'Vì sao phải gieo trồng đúng thời vụ?', ['Vì cây gặp thời tiết thuận lợi, năng suất cao', 'Vì đúng thời vụ thì không cần chăm sóc', 'Vì trái vụ cây lớn nhanh hơn', 'Vì thời vụ không quan trọng'], 0, 'Đúng thời vụ cây gặp nhiệt độ, mưa nắng thuận lợi nên sinh trưởng tốt.', $D);
        $this->quiz($L, 'Luân canh là biện pháp gì?', ['Thay đổi loại cây trồng qua các vụ', 'Trồng một loại cây mãi mãi', 'Trồng cây theo hàng ngang', 'Tưới nước luân phiên'], 0, 'Luân canh giúp đất không bị kiệt dinh dưỡng và hạn chế sâu bệnh.', $D);
        $this->quiz($L, 'Đất bị chua (pH thấp) cần được xử lý thế nào?', ['Bón vôi để khử chua', 'Bón thêm phân đạm', 'Tưới thật nhiều nước', 'Để nguyên không xử lý'], 0, 'Vôi có tính kiềm giúp trung hòa độ chua, cải tạo đất.', $D);
        $this->quiz($L, 'Vì sao phải làm cỏ thường xuyên cho cây trồng?', ['Vì cỏ tranh nước, dinh dưỡng và là nơi trú của sâu bệnh', 'Vì cỏ làm đất đẹp hơn', 'Vì cỏ giúp cây lớn nhanh', 'Vì làm cỏ cho vui'], 0, 'Cỏ dại tranh dinh dưỡng với cây và là nơi sâu bệnh trú ẩn.', $D);
        $this->matching($L, 'Nối mỗi bước trồng trọt với nội dung chi tiết.', [['Chọn giống', 'Hạt chắc mẩy, sạch bệnh'], ['Làm đất', 'Cày bừa tơi xốp'], ['Gieo trồng', 'Đúng mật độ, thời vụ'], ['Chăm sóc', 'Tưới, bón, phòng trừ']], 'Quy trình trồng trọt gồm các bước liên hoàn từ chọn giống đến chăm sóc.', $D);
        $this->matching($L, 'Nối mỗi loại đất với cây trồng phù hợp.', [['Đất phù sa', 'Lúa nước'], ['Đất đỏ bazan', 'Cà phê'], ['Đất cát pha', 'Lạc, đậu phộng'], ['Đất phèn', 'Cần cải tạo trước']], 'Mỗi loại đất phù hợp với từng loại cây trồng khác nhau.', $D);
        $this->matching($L, 'Nối mỗi biện pháp kỹ thuật với mục đích của nó.', [['Luân canh', 'Tránh sâu bệnh, tốt đất'], ['Bón vôi', 'Khử chua đất'], ['Lên luống', 'Thoát nước tốt'], ['Che phủ đất', 'Giữ ẩm cho đất']], 'Các biện pháp kỹ thuật giúp đất tốt và cây khỏe hơn.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ với công việc đồng áng của nó.', [['Cày', 'Lật đất'], ['Bừa', 'Làm nhỏ đất'], ['Liềm', 'Gặt lúa'], ['Quang gánh', 'Chở lúa']], 'Dụng cụ nông nghiệp truyền thống gắn với từng công việc đồng áng.', $D);
        $this->matching($L, 'Nối mỗi thời vụ ở miền Bắc với thời gian gieo trồng.', [['Vụ xuân', 'Tháng 1-2'], ['Vụ hè thu', 'Tháng 5-6'], ['Vụ đông', 'Tháng 9-10'], ['Gieo mạ vụ mùa', 'Tháng 6']], 'Mỗi vụ trong năm có thời gian gieo trồng phù hợp với thời tiết.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm CHUẨN BỊ hoặc THỰC HIỆN.', [['Chọn giống', 'CHUẨN BỊ'], ['Làm đất', 'CHUẨN BỊ'], ['Gieo hạt', 'THỰC HIỆN'], ['Thu hoạch', 'THỰC HIỆN']], 'Chọn giống, làm đất là chuẩn bị; gieo hạt, thu hoạch là thực hiện.', $D);
        $this->sortQ($L, 'Kéo mỗi loại cây vào nhóm ƯA NƯỚC hoặc CHỊU HẠN.', [['Lúa nước', 'ƯA NƯỚC'], ['Rau muống', 'ƯA NƯỚC'], ['Xương rồng', 'CHỊU HẠN'], ['Lạc', 'CHỊU HẠN']], 'Lúa, rau muống cần nhiều nước; xương rồng, lạc chịu hạn tốt.', $D);
        $this->sortQ($L, 'Kéo mỗi biện pháp vào nhóm CẢI TẠO ĐẤT hoặc CHĂM SÓC CÂY.', [['Bón vôi khử chua', 'CẢI TẠO ĐẤT'], ['Bón phân hữu cơ', 'CẢI TẠO ĐẤT'], ['Tỉa cành', 'CHĂM SÓC CÂY'], ['Bắt sâu', 'CHĂM SÓC CÂY']], 'Bón vôi, bón hữu cơ cải tạo đất; tỉa cành, bắt sâu chăm sóc cây.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm ĐÚNG hoặc SAI kỹ thuật.', [['Gieo đúng thời vụ', 'ĐÚNG'], ['Làm cỏ thường xuyên', 'ĐÚNG'], ['Trồng quá dày', 'SAI'], ['Bỏ mặc sâu bệnh', 'SAI']], 'Đúng kỹ thuật là gieo đúng vụ, làm cỏ và phòng trừ sâu bệnh.', $D);
        $this->sortQ($L, 'Kéo mỗi loại giống vào nhóm GIỐNG TỐT hoặc GIỐNG KÉM.', [['Hạt chắc mẩy', 'GIỐNG TỐT'], ['Cây con khỏe mạnh', 'GIỐNG TỐT'], ['Hạt lép, sâu bệnh', 'GIỐNG KÉM'], ['Cây con còi cọc', 'GIỐNG KÉM']], 'Giống tốt là hạt mẩy, cây khỏe; giống kém là hạt lép, cây còi.', $D);
        $this->fill($L, 'Gieo trồng đúng ___ vụ giúp cây sinh trưởng tốt, năng suất cao.', [[0, 'thời']], 'Đúng thời vụ là yếu tố quan trọng quyết định năng suất cây trồng.', $D);
        $this->fill($L, 'Đất bị chua cần bón ___ để khử chua, cải tạo đất.', [[0, 'vôi']], 'Vôi trung hòa axit trong đất chua, giúp cây hấp thụ dinh dưỡng tốt.', $D);
        $this->fill($L, '___ canh là thay đổi loại cây trồng qua các vụ để đất tốt hơn.', [[0, 'Luân']], 'Luân canh tránh đất bị kiệt dinh dưỡng và giảm sâu bệnh.', $D);
        $this->fill($L, 'Mật độ gieo trồng quá dày làm cây thiếu ánh sáng và chất dinh ___.', [[0, 'dưỡng']], 'Mật độ hợp lý để cây đủ ánh sáng và dinh dưỡng phát triển.', $D);
        $this->fill($L, 'Cỏ dại tranh nước và chất dinh dưỡng với cây ___.', [[0, 'trồng']], 'Phải làm cỏ thường xuyên để cây trồng không bị tranh dinh dưỡng.', $D);
    }

    // ===== 16. cn-trong-trot-lop-8-2 (Lop 8, trung_binh) =====
    private function seedCnTrongTrotLop82(): void
    {
        $L = 'cn-trong-trot-lop-8-2'; $D = 'trung_binh';
        $this->quiz($L, 'Phân chuồng cần được xử lý thế nào trước khi bón cho cây?', ['Ủ hoai mục', 'Bón tươi ngay', 'Phơi một nắng', 'Trộn với cát'], 0, 'Phân chuồng phải ủ hoai để diệt mầm bệnh và không gây nóng rễ.', $D);
        $this->quiz($L, 'Vì sao không nên tưới nước cho cây vào buổi trưa nắng gắt?', ['Vì nước bốc hơi nhanh và cây dễ sốc nhiệt', 'Vì trưa nước lạnh hơn', 'Vì cây ngủ trưa', 'Vì trưa không có nước'], 0, 'Tưới trưa nắng nước bốc hơi nhanh, lá ướt dễ cháy và cây sốc nhiệt.', $D);
        $this->quiz($L, 'Tưới phun mưa phù hợp với loại cây nào?', ['Rau màu và cây thấp', 'Cây cổ thụ', 'Cây trong nhà kính kín', 'Cây sa mạc'], 0, 'Phun mưa tưới đều như mưa tự nhiên, hợp với rau màu và cây thấp.', $D);
        $this->quiz($L, 'Bón phân quá gần gốc cây non sẽ gây hại gì?', ['Cháy rễ làm cây héo', 'Cây lớn nhanh gấp đôi', 'Cây ra hoa sớm', 'Không ảnh hưởng gì'], 0, 'Phân đậm đặc gần gốc non gây cháy rễ, cây héo rũ.', $D);
        $this->quiz($L, 'Nước tưới bị nhiễm phèn ảnh hưởng gì đến cây trồng?', ['Cây vàng lá, sinh trưởng kém', 'Cây xanh tốt hơn', 'Cây ra nhiều quả', 'Không ảnh hưởng'], 0, 'Nước phèn làm đất chua thêm, cây vàng lá và kém phát triển.', $D);
        $this->matching($L, 'Nối mỗi loại phân hữu cơ với nguồn gốc của nó.', [['Phân chuồng', 'Phân gia súc'], ['Phân xanh', 'Cây họ đậu ủ'], ['Phân rác', 'Rác hữu cơ ủ hoai'], ['Tro bếp', 'Rơm rạ đốt']], 'Phân hữu cơ từ nguồn tự nhiên, an toàn và cải tạo đất tốt.', $D);
        $this->matching($L, 'Nối mỗi cách bón phân với thời điểm thực hiện.', [['Bón lót', 'Trước khi gieo'], ['Bón thúc', 'Khi cây đang lớn'], ['Bón vãi', 'Rải đều mặt ruộng'], ['Bón theo hốc', 'Bón vào gốc cây']], 'Mỗi cách bón phù hợp với từng giai đoạn và loại cây.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu của cây với nguyên nhân có thể.', [['Lá vàng từ gốc lên', 'Thiếu đạm'], ['Cây đổ rạp', 'Thiếu kali'], ['Rễ thối đen', 'Úng nước'], ['Lá quăn queo', 'Sâu bệnh']], 'Quan sát dấu hiệu giúp chẩn đoán đúng bệnh của cây.', $D);
        $this->matching($L, 'Nối mỗi phương pháp tưới với đặc điểm của nó.', [['Tưới nhỏ giọt', 'Tiết kiệm nước nhất'], ['Tưới phun mưa', 'Như mưa tự nhiên'], ['Tưới tràn', 'Tốn nhiều nước'], ['Tưới rãnh', 'Cho cây trồng theo hàng']], 'Chọn phương pháp tưới phù hợp giúp tiết kiệm nước và cây tốt.', $D);
        $this->matching($L, 'Nối mỗi việc làm với đánh giá khi chăm sóc cây.', [['Tưới vào sáng sớm', 'Đúng'], ['Xới đất quanh gốc', 'Đúng'], ['Bón phân lúc trưa nắng', 'Sai'], ['Để cỏ mọc um tùm', 'Sai']], 'Chăm sóc đúng là tưới sáng sớm, xới đất; sai là bón trưa nắng, bỏ cỏ.', $D);
        $this->sortQ($L, 'Kéo mỗi loại phân vào nhóm PHÂN HỮU CƠ hoặc PHÂN VÔ CƠ.', [['Phân bò ủ hoai', 'PHÂN HỮU CƠ'], ['Phân xanh', 'PHÂN HỮU CƠ'], ['Urê', 'PHÂN VÔ CƠ'], ['Supe lân', 'PHÂN VÔ CƠ']], 'Phân bò, phân xanh là hữu cơ; urê, supe lân là vô cơ (hóa học).', $D);
        $this->sortQ($L, 'Kéo mỗi thời điểm vào nhóm NÊN hoặc KHÔNG NÊN bón phân.', [['Chiều mát, đất ẩm', 'NÊN'], ['Sau cơn mưa nhỏ', 'NÊN'], ['Trưa nắng gắt', 'KHÔNG NÊN'], ['Khi cây đang héo nặng', 'KHÔNG NÊN']], 'Bón chiều mát đất ẩm cây hấp thụ tốt; trưa nắng và cây héo thì không.', $D);
        $this->sortQ($L, 'Kéo mỗi cách tưới vào nhóm TIẾT KIỆM NƯỚC hoặc TỐN NƯỚC.', [['Tưới nhỏ giọt', 'TIẾT KIỆM NƯỚC'], ['Tưới phun mưa', 'TIẾT KIỆM NƯỚC'], ['Tưới tràn', 'TỐN NƯỚC'], ['Tưới ào ạt', 'TỐN NƯỚC']], 'Nhỏ giọt và phun mưa tiết kiệm nước; tưới tràn tốn nhiều nước.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm THIẾU NƯỚC hoặc THỪA NƯỚC.', [['Lá héo rũ', 'THIẾU NƯỚC'], ['Đất nứt nẻ', 'THIẾU NƯỚC'], ['Rễ thối đen', 'THỪA NƯỚC'], ['Nước đọng mặt luống', 'THỪA NƯỚC']], 'Thiếu nước lá héo, đất nứt; thừa nước rễ thối, nước đọng.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TỐT hoặc XẤU cho đất.', [['Bón phân hữu cơ', 'TỐT'], ['Trồng cây che phủ đất', 'TỐT'], ['Lạm dụng phân hóa học', 'XẤU'], ['Đốt rơm rạ', 'XẤU']], 'Phân hữu cơ và che phủ tốt cho đất; lạm dụng hóa học và đốt rơm hại đất.', $D);
        $this->fill($L, 'Phân chuồng phải ủ ___ trước khi bón để diệt mầm bệnh.', [[0, 'hoai']], 'Ủ hoai giúp phân an toàn, không gây nóng rễ và sạch mầm bệnh.', $D);
        $this->fill($L, 'Tưới ___ giọt là phương pháp tiết kiệm nước nhất hiện nay.', [[0, 'nhỏ']], 'Tưới nhỏ giọt đưa nước trực tiếp đến gốc, ít hao phí nhất.', $D);
        $this->fill($L, 'Không bón phân lúc trưa ___ vì phân dễ bay hơi, cây dễ cháy lá.', [[0, 'nắng']], 'Trưa nắng bón phân vừa hao phí vừa hại cây.', $D);
        $this->fill($L, 'Nước tưới nhiễm ___ làm cây vàng lá, sinh trưởng kém.', [[0, 'phèn']], 'Nước phèn chua làm hại rễ, cây vàng lá còi cọc.', $D);
        $this->fill($L, 'Bón phân quá liều gây ___ rễ khiến cây héo rũ.', [[0, 'cháy']], 'Phân quá liều làm cháy rễ, cây không hút được nước nên héo.', $D);
    }

    // ===== 17. cn-trong-trot-lop-9-1 (Lop 9, trung_binh) =====
    private function seedCnTrongTrotLop91(): void
    {
        $L = 'cn-trong-trot-lop-9-1'; $D = 'trung_binh';
        $this->quiz($L, 'Ong ký sinh có lợi gì cho cây trồng?', ['Đẻ trứng vào sâu hại để diệt sâu', 'Ăn lá cây', 'Hút mật làm hại hoa', 'Đục thân cây'], 0, 'Ong ký sinh đẻ trứng vào cơ thể sâu hại, ấu trùng nở ra ăn sâu hại.', $D);
        $this->quiz($L, 'Biện pháp canh tác nào giúp hạn chế sâu bệnh hiệu quả?', ['Luân canh và vệ sinh đồng ruộng', 'Trồng dày đặc', 'Bỏ hoang đất', 'Tưới nước mặn'], 0, 'Luân canh cắt nguồn thức ăn của sâu bệnh; vệ sinh đồng ruộng diệt nơi trú ẩn.', $D);
        $this->quiz($L, 'Bẫy đèn thường dùng để diệt loại sâu nào?', ['Sâu có cánh hoạt động ban đêm', 'Sâu trong đất', 'Rệp trên lá', 'Ốc sên'], 0, 'Bẫy đèn thu hút sâu bướm bay đêm đến để tiêu diệt.', $D);
        $this->quiz($L, 'Vì sao phải tuân thủ thời gian cách ly sau khi phun thuốc bảo vệ thực vật?', ['Để thuốc phân hủy hết, đảm bảo an toàn thực phẩm', 'Để thuốc ngấm lâu hơn', 'Để tiết kiệm thuốc', 'Để sâu quen thuốc'], 0, 'Thời gian cách ly giúp dư lượng thuốc phân hủy, nông sản an toàn khi thu hoạch.', $D);
        $this->quiz($L, 'Bệnh đạo ôn hại lúa có biểu hiện đặc trưng gì?', ['Vết bệnh hình thoi trên lá', 'Lá bị cuốn tròn', 'Thân bị đục lỗ', 'Rễ bị thối'], 0, 'Đạo ôn tạo vết hình thoi màu xám trên lá lúa, lây lan rất nhanh.', $D);
        $this->matching($L, 'Nối mỗi thiên địch với con mồi của nó.', [['Ong mắt đỏ', 'Trứng sâu'], ['Bọ rùa', 'Rệp muội'], ['Nhện', 'Sâu nhỏ'], ['Ếch nhái', 'Côn trùng']], 'Thiên địch là bạn của nhà nông, giúp diệt sâu hại tự nhiên.', $D);
        $this->matching($L, 'Nối mỗi biện pháp phòng trừ với ví dụ của nó.', [['Thủ công', 'Bắt sâu bằng tay'], ['Sinh học', 'Thả ong ký sinh'], ['Hóa học', 'Phun thuốc trừ sâu'], ['Canh tác', 'Luân canh cây trồng']], 'Có bốn nhóm biện pháp phòng trừ: thủ công, sinh học, hóa học và canh tác.', $D);
        $this->matching($L, 'Nối mỗi loại sâu bệnh với cây trồng thường bị hại.', [['Sâu cuốn lá', 'Lúa'], ['Sâu đục thân', 'Ngô'], ['Rệp muội', 'Rau cải'], ['Bệnh đạo ôn', 'Lúa']], 'Mỗi loại cây có những sâu bệnh đặc trưng cần phòng trừ.', $D);
        $this->matching($L, 'Nối mỗi nguyên tắc với nội dung khi dùng thuốc bảo vệ thực vật.', [['Đúng thuốc', 'Trị đúng sâu bệnh'], ['Đúng liều lượng', 'Không tăng giảm'], ['Đúng lúc', 'Phun khi sâu còn non'], ['Đúng cách', 'Phun đều, đúng kỹ thuật']], 'Nguyên tắc 4 đúng giúp dùng thuốc hiệu quả và an toàn.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu với loại gây hại.', [['Lá bị cuốn lại', 'Sâu cuốn lá'], ['Thân bị đục lỗ', 'Sâu đục thân'], ['Lá vàng đốm lan rộng', 'Bệnh hại'], ['Quả bị thối nhũn', 'Bệnh hại']], 'Dấu hiệu trên cây giúp phân biệt sâu hại và bệnh hại để xử lý.', $D);
        $this->sortQ($L, 'Kéo mỗi biện pháp vào nhóm PHÒNG NGỪA hoặc TRỪ DIỆT.', [['Vệ sinh đồng ruộng', 'PHÒNG NGỪA'], ['Chọn giống kháng bệnh', 'PHÒNG NGỪA'], ['Phun thuốc', 'TRỪ DIỆT'], ['Bắt sâu bằng tay', 'TRỪ DIỆT']], 'Phòng ngừa là ngăn sâu bệnh xuất hiện; trừ diệt là diệt khi đã có.', $D);
        $this->sortQ($L, 'Kéo mỗi con vật vào nhóm CÓ ÍCH hoặc CÓ HẠI cho cây trồng.', [['Ong mật', 'CÓ ÍCH'], ['Bọ rùa', 'CÓ ÍCH'], ['Sâu đục thân', 'CÓ HẠI'], ['Chuột đồng', 'CÓ HẠI']], 'Ong, bọ rùa có ích; sâu đục thân, chuột đồng có hại.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm AN TOÀN hoặc NGUY HIỂM khi phun thuốc.', [['Đeo khẩu trang, găng tay', 'AN TOÀN'], ['Mặc đồ bảo hộ', 'AN TOÀN'], ['Phun ngược chiều gió', 'NGUY HIỂM'], ['Ăn uống khi đang phun', 'NGUY HIỂM']], 'Phun thuốc phải bảo hộ đầy đủ, xuôi chiều gió và không ăn uống.', $D);
        $this->sortQ($L, 'Kéo mỗi biện pháp vào nhóm SINH HỌC hoặc HÓA HỌC.', [['Thả thiên địch', 'SINH HỌC'], ['Dùng bẫy pheromone', 'SINH HỌC'], ['Phun thuốc trừ sâu', 'HÓA HỌC'], ['Rải thuốc diệt cỏ', 'HÓA HỌC']], 'Sinh học dùng sinh vật và bẫy; hóa học dùng thuốc.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm SÂU HẠI hoặc BỆNH HẠI.', [['Lá bị gặm nham nhở', 'SÂU HẠI'], ['Thân cây bị đục lỗ', 'SÂU HẠI'], ['Lá có đốm vàng lan rộng', 'BỆNH HẠI'], ['Quả bị thối nhũn', 'BỆNH HẠI']], 'Sâu hại gặm, đục; bệnh hại gây đốm, thối.', $D);
        $this->fill($L, 'Ong ___ sinh đẻ trứng vào cơ thể sâu hại để diệt sâu.', [[0, 'ký']], 'Ong ký sinh là thiên địch quan trọng giúp diệt sâu hại tự nhiên.', $D);
        $this->fill($L, 'Bẫy ___ dùng ánh sáng để thu hút và bắt sâu bay vào ban đêm.', [[0, 'đèn']], 'Nhiều sâu bướm bị thu hút bởi ánh sáng đèn vào ban đêm.', $D);
        $this->fill($L, 'Phun thuốc phải tuân thủ thời gian ___ ly để đảm bảo an toàn thực phẩm.', [[0, 'cách']], 'Hết thời gian cách ly, dư lượng thuốc mới phân hủy hết.', $D);
        $this->fill($L, 'Vệ sinh ___ ruộng sau thu hoạch giúp diệt nơi trú ẩn của sâu bệnh.', [[0, 'đồng']], 'Dọn sạch tàn dư cây trồng sau thu hoạch giảm sâu bệnh vụ sau.', $D);
        $this->fill($L, 'Ưu tiên biện pháp sinh học giúp bảo vệ môi trường và sức ___ con người.', [[0, 'khỏe']], 'Biện pháp sinh học an toàn, không gây ô nhiễm như thuốc hóa học.', $D);
    }

    // ===== 18. cn-trong-trot-lop-9-2 (Lop 9, kho) =====
    private function seedCnTrongTrotLop92(): void
    {
        $L = 'cn-trong-trot-lop-9-2'; $D = 'kho';
        $this->quiz($L, 'Vì sao nông sản sau thu hoạch phải phân loại trước khi bảo quản?', ['Để loại quả hỏng và bảo quản đồng đều', 'Để quả đẹp hơn', 'Để bán được giá cao hơn thôi', 'Để tốn thêm công sức'], 0, 'Phân loại loại bỏ quả hỏng, dập để không lây sang quả lành và bảo quản đồng đều.', $D);
        $this->quiz($L, 'Bảo quản lạnh nông sản dựa trên nguyên lý nào?', ['Nhiệt độ thấp làm chậm hô hấp và vi sinh vật', 'Lạnh làm quả chín nhanh', 'Lạnh diệt hết vitamin', 'Lạnh làm quả to ra'], 0, 'Nhiệt độ thấp làm chậm quá trình hô hấp của quả và ức chế vi sinh vật gây thối.', $D);
        $this->quiz($L, 'Nhà kính (greenhouse) giúp trồng trọt như thế nào?', ['Chủ động nhiệt độ, ẩm độ nên trồng được trái vụ', 'Chỉ để trồng hoa', 'Làm cây lớn nhanh gấp mười lần', 'Không cần tưới nước'], 0, 'Nhà kính điều khiển được tiểu khí hậu nên trồng được quanh năm, kể cả trái vụ.', $D);
        $this->quiz($L, 'Công nghệ IoT được ứng dụng trong nông nghiệp ra sao?', ['Cảm biến tự động tưới và giám sát cây trồng', 'Chỉ để chụp ảnh', 'Thay người thu hoạch hoàn toàn', 'Làm đất thay máy cày'], 0, 'Cảm biến IoT đo ẩm đất, nhiệt độ để tưới tự động và cảnh báo sâu bệnh.', $D);
        $this->quiz($L, 'Chiếu xạ bảo quản thực phẩm có tác dụng gì?', ['Diệt vi sinh vật, kéo dài thời gian bảo quản', 'Làm thực phẩm phóng xạ nguy hiểm', 'Làm thực phẩm chín nhanh', 'Thay thế hoàn toàn tủ lạnh'], 0, 'Chiếu xạ liều cho phép diệt vi sinh vật, kéo dài bảo quản mà vẫn an toàn.', $D);
        $this->matching($L, 'Nối mỗi phương pháp bảo quản với nguyên lý của nó.', [['Phơi sấy khô', 'Giảm độ ẩm'], ['Bảo quản lạnh', 'Ức chế vi sinh vật'], ['Đóng hộp', 'Tiệt trùng và kín'], ['Muối chua', 'Lên men lactic']], 'Mỗi phương pháp bảo quản dựa trên một nguyên lý khoa học riêng.', $D);
        $this->matching($L, 'Nối mỗi công nghệ với ứng dụng trong nông nghiệp.', [['IoT', 'Tưới tự động'], ['Drone', 'Phun thuốc'], ['Nhà kính', 'Trồng trái vụ'], ['Thủy canh', 'Trồng không cần đất']], 'Công nghệ hiện đại giúp nông nghiệp chính xác và hiệu quả hơn.', $D);
        $this->matching($L, 'Nối mỗi nông sản với điều kiện bảo quản phù hợp.', [['Thóc', 'Khô ráo, thoáng'], ['Rau tươi', 'Lạnh và ẩm'], ['Trái cây', 'Mát, tránh dập'], ['Hạt giống', 'Khô và kín']], 'Mỗi nông sản cần điều kiện bảo quản riêng để giữ chất lượng lâu.', $D);
        $this->matching($L, 'Nối mỗi giai đoạn sau thu hoạch với công việc của nó.', [['Phân loại', 'Loại quả hỏng'], ['Làm sạch', 'Rửa bụi đất'], ['Đóng gói', 'Bao bì phù hợp'], ['Vận chuyển', 'Nhanh, tránh dập']], 'Sau thu hoạch cần phân loại, làm sạch, đóng gói và vận chuyển cẩn thận.', $D);
        $this->matching($L, 'Nối mỗi tổn thất sau thu hoạch với nguyên nhân của nó.', [['Hao hụt khối lượng', 'Hô hấp và bay hơi'], ['Thối hỏng', 'Vi sinh vật'], ['Mất giá trị', 'Dập nát'], ['Nảy mầm', 'Ẩm độ cao']], 'Hiểu nguyên nhân tổn thất giúp bảo quản nông sản tốt hơn.', $D);
        $this->sortQ($L, 'Kéo mỗi phương pháp vào nhóm BẢO QUẢN TRUYỀN THỐNG hoặc HIỆN ĐẠI.', [['Phơi nắng', 'BẢO QUẢN TRUYỀN THỐNG'], ['Muối chua', 'BẢO QUẢN TRUYỀN THỐNG'], ['Kho lạnh', 'BẢO QUẢN HIỆN ĐẠI'], ['Chiếu xạ', 'BẢO QUẢN HIỆN ĐẠI']], 'Phơi, muối là truyền thống; kho lạnh, chiếu xạ là hiện đại.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm ĐÚNG hoặc SAI khi bảo quản thóc.', [['Phơi khô trước khi cất', 'ĐÚNG'], ['Dùng bao bì sạch', 'ĐÚNG'], ['Để thóc nơi ẩm ướt', 'SAI'], ['Trộn thóc ướt với thóc khô', 'SAI']], 'Thóc phải phơi khô, để nơi khô ráo; ẩm ướt làm thóc mốc, nảy mầm.', $D);
        $this->sortQ($L, 'Kéo mỗi công nghệ vào nhóm TIẾT KIỆM TÀI NGUYÊN hoặc TỐN TÀI NGUYÊN.', [['Tưới nhỏ giọt', 'TIẾT KIỆM TÀI NGUYÊN'], ['Thủy canh tuần hoàn', 'TIẾT KIỆM TÀI NGUYÊN'], ['Tưới tràn', 'TỐN TÀI NGUYÊN'], ['Canh tác bừa bãi', 'TỐN TÀI NGUYÊN']], 'Công nghệ chính xác tiết kiệm nước và phân bón; canh tác bừa bãi tốn tài nguyên.', $D);
        $this->sortQ($L, 'Kéo mỗi nông sản vào nhóm BẢO QUẢN KHÔ hoặc BẢO QUẢN LẠNH.', [['Thóc gạo', 'BẢO QUẢN KHÔ'], ['Đậu đỗ', 'BẢO QUẢN KHÔ'], ['Rau ăn lá', 'BẢO QUẢN LẠNH'], ['Dâu tây', 'BẢO QUẢN LẠNH']], 'Hạt khô bảo quản khô; rau quả tươi bảo quản lạnh.', $D);
        $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm GIÚP hoặc HẠI việc bảo quản.', [['Nhiệt độ thấp', 'GIÚP'], ['Bao bì kín', 'GIÚP'], ['Ẩm độ cao', 'HẠI'], ['Va đập mạnh', 'HẠI']], 'Lạnh và kín giúp bảo quản; ẩm cao và va đập làm hỏng nông sản.', $D);
        $this->fill($L, 'Bảo quản ___ làm chậm quá trình hô hấp của nông sản tươi.', [[0, 'lạnh']], 'Nhiệt độ thấp làm quả hô hấp chậm nên tươi lâu hơn.', $D);
        $this->fill($L, '___ canh là trồng cây trong dung dịch dinh dưỡng mà không cần đất.', [[0, 'Thủy']], 'Thủy canh trồng cây bằng dung dịch dinh dưỡng tuần hoàn.', $D);
        $this->fill($L, 'Nhà ___ giúp chủ động điều khiển nhiệt độ, ẩm độ khi trồng cây.', [[0, 'kính']], 'Nhà kính tạo tiểu khí hậu ổn định, trồng được trái vụ.', $D);
        $this->fill($L, 'Cảm biến độ ẩm đất giúp hệ thống tưới ___ động bật tắt hợp lý.', [[0, 'tự']], 'Tưới tự động theo cảm biến giúp tiết kiệm nước và công sức.', $D);
        $this->fill($L, 'Thu hoạch đúng độ chín giúp nông sản đạt chất lượng và ___ quản lâu hơn.', [[0, 'bảo']], 'Đúng độ chín thì nông sản ngon và bảo quản được lâu.', $D);
    }

    // ===== 19. cong-nghe-thpt-10-lop-10-1 (Lop 10, trung_binh) =====
    private function seedCongNgheThpt10Lop101(): void
    {
        $L = 'cong-nghe-thpt-10-lop-10-1'; $D = 'trung_binh';
        $this->quiz($L, 'Điện trở được đo bằng đơn vị nào?', ['Ôm (Ω)', 'Ampe (A)', 'Vôn (V)', 'Oát (W)'], 0, 'Ôm là đơn vị đo điện trở; ampe đo dòng điện; vôn đo điện áp; oát đo công suất.', $D);
        $this->quiz($L, 'Ampe kế được mắc như thế nào trong mạch điện?', ['Mắc nối tiếp với thiết bị', 'Mắc song song với thiết bị', 'Mắc tùy ý', 'Không cần mắc vào mạch'], 0, 'Ampe kế mắc nối tiếp để dòng điện đi qua nó; vôn kế mắc song song.', $D);
        $this->quiz($L, 'Công của dòng điện được tính bằng công thức nào?', ['A = U.I.t', 'A = U/I', 'A = I/R', 'A = U + I'], 0, 'Công của dòng điện A = UIt, với U là điện áp, I là cường độ, t là thời gian.', $D);
        $this->quiz($L, 'Một ấm điện 220V – 1000W dùng trong 1 giờ tiêu thụ bao nhiêu điện năng?', ['1 kWh (1 số điện)', '0,5 kWh', '2 kWh', '100 kWh'], 0, 'A = P.t = 1000W × 1h = 1kWh, tức 1 số điện.', $D);
        $this->quiz($L, 'Vì sao dây dẫn điện thường được làm bằng đồng?', ['Vì đồng có điện trở suất nhỏ, dẫn điện tốt', 'Vì đồng rẻ nhất', 'Vì đồng nhẹ nhất', 'Vì đồng không bao giờ đứt'], 0, 'Đồng dẫn điện tốt, dẻo dễ kéo sợi nên được dùng làm dây dẫn.', $D);
        $this->matching($L, 'Nối mỗi đại lượng điện với kí hiệu của nó.', [['Cường độ dòng điện', 'I'], ['Điện áp', 'U'], ['Điện trở', 'R'], ['Công suất', 'P']], 'Kí hiệu các đại lượng: I cường độ, U điện áp, R điện trở, P công suất.', $D);
        $this->matching($L, 'Nối mỗi đơn vị đo với tên đại lượng.', [['Ampe', 'Cường độ dòng điện'], ['Vôn', 'Điện áp'], ['Ôm', 'Điện trở'], ['Oát', 'Công suất']], 'Ampe đo cường độ, vôn đo điện áp, ôm đo điện trở, oát đo công suất.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ đo với đại lượng mà nó đo.', [['Ampe kế', 'Dòng điện'], ['Vôn kế', 'Điện áp'], ['Ôm kế', 'Điện trở'], ['Công tơ điện', 'Điện năng']], 'Mỗi dụng cụ đo một đại lượng điện khác nhau.', $D);
        $this->matching($L, 'Nối mỗi công thức với tên gọi của nó.', [['I = U/R', 'Định luật Ôm'], ['P = U.I', 'Công suất điện'], ['A = P.t', 'Điện năng tiêu thụ'], ['Q = I².R.t', 'Định luật Jun – Lenxơ']], 'Các công thức cơ bản của mạch điện một chiều.', $D);
        $this->matching($L, 'Nối mỗi thiết bị với sự chuyển hóa năng lượng của nó.', [['Bóng đèn', 'Điện thành ánh sáng'], ['Bàn là', 'Điện thành nhiệt'], ['Quạt điện', 'Điện thành cơ năng'], ['Loa', 'Điện thành âm thanh']], 'Thiết bị điện chuyển điện năng thành các dạng năng lượng khác.', $D);
        $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm MẮC NỐI TIẾP hoặc MẮC SONG SONG trong mạch.', [['Ampe kế', 'MẮC NỐI TIẾP'], ['Cầu chì', 'MẮC NỐI TIẾP'], ['Vôn kế', 'MẮC SONG SONG'], ['Ổ cắm', 'MẮC SONG SONG']], 'Ampe kế, cầu chì mắc nối tiếp; vôn kế mắc song song.', $D);
        $this->sortQ($L, 'Kéo mỗi số liệu vào nhóm ĐÚNG hoặc SAI về đơn vị đo.', [['Cường độ 2A', 'ĐÚNG'], ['Điện áp 220V', 'ĐÚNG'], ['Điện trở 5V', 'SAI'], ['Công suất 100Ω', 'SAI']], 'Điện trở đo bằng ôm, công suất đo bằng oát; ghi sai đơn vị là sai.', $D);
        $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm DẪN ĐIỆN TỐT hoặc DẪN ĐIỆN KÉM.', [['Đồng', 'DẪN ĐIỆN TỐT'], ['Nhôm', 'DẪN ĐIỆN TỐT'], ['Sứ', 'DẪN ĐIỆN KÉM'], ['Nhựa', 'DẪN ĐIỆN KÉM']], 'Đồng, nhôm dẫn điện tốt; sứ, nhựa cách điện.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm CÔNG SUẤT LỚN hoặc CÔNG SUẤT NHỎ.', [['Bếp điện', 'CÔNG SUẤT LỚN'], ['Điều hòa', 'CÔNG SUẤT LỚN'], ['Sạc điện thoại', 'CÔNG SUẤT NHỎ'], ['Bóng đèn LED', 'CÔNG SUẤT NHỎ']], 'Bếp điện, điều hòa công suất lớn; sạc, LED công suất nhỏ.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['Dòng điện là dòng chuyển dời có hướng của điện tích', 'ĐÚNG'], ['Điện trở càng lớn thì dòng điện càng nhỏ (U không đổi)', 'ĐÚNG'], ['Vôn kế được mắc nối tiếp trong mạch', 'SAI'], ['Công suất càng lớn càng tốn điện', 'ĐÚNG']], 'Vôn kế mắc song song; các nhận định còn lại đều đúng.', $D);
        $this->fill($L, 'Dụng cụ đo cường độ dòng điện được gọi là ___ kế.', [[0, 'ampe']], 'Ampe kế mắc nối tiếp trong mạch để đo cường độ dòng điện.', $D);
        $this->fill($L, 'Điện trở có đơn vị đo là ___ (Ω).', [[0, 'ôm']], 'Ôm là đơn vị đo điện trở trong hệ SI.', $D);
        $this->fill($L, 'Công thức tính điện năng tiêu thụ: A = P × ___.', [[0, 't']], 'A = Pt, với t là thời gian sử dụng thiết bị điện.', $D);
        $this->fill($L, '1 kWh chính là 1 số điện, tương đương 3,6 triệu ___.', [[0, 'jun']], '1kWh = 3,6 triệu jun, là đơn vị điện năng trên công tơ.', $D);
        $this->fill($L, 'Dây dẫn có điện trở càng ___ thì dòng điện càng khó đi qua.', [[0, 'lớn']], 'Theo định luật Ôm, điện trở lớn thì cường độ dòng điện nhỏ.', $D);
    }

    // ===== 20. cong-nghe-thpt-10-lop-10-2 (Lop 10, kho) =====
    private function seedCongNgheThpt10Lop102(): void
    {
        $L = 'cong-nghe-thpt-10-lop-10-2'; $D = 'kho';
        $this->quiz($L, 'Ba điện trở 2Ω, 3Ω và 5Ω mắc nối tiếp có điện trở tương đương là bao nhiêu?', ['10Ω', '6Ω', '5Ω', '1Ω'], 0, 'Nối tiếp: R = 2 + 3 + 5 = 10Ω.', $D);
        $this->quiz($L, 'Hai điện trở 6Ω mắc song song có điện trở tương đương là bao nhiêu?', ['3Ω', '12Ω', '6Ω', '36Ω'], 0, 'Hai điện trở bằng nhau mắc song song: R = 6/2 = 3Ω.', $D);
        $this->quiz($L, 'Trong mạch mắc nối tiếp, nếu một bóng đèn bị cháy thì các bóng còn lại thế nào?', ['Tắt theo vì mạch bị hở', 'Vẫn sáng bình thường', 'Sáng hơn', 'Cháy theo'], 0, 'Mạch nối tiếp chỉ có một đường đi nên một chỗ hở cả mạch tắt.', $D);
        $this->quiz($L, 'Trong mạch mắc song song, nếu một nhánh bị đứt thì các nhánh còn lại thế nào?', ['Vẫn hoạt động bình thường', 'Tắt hết', 'Cháy hết', 'Sáng yếu đi'], 0, 'Các nhánh song song độc lập nên một nhánh đứt không ảnh hưởng nhánh khác.', $D);
        $this->quiz($L, 'Cầu chì trong mạng điện gia đình được mắc nối tiếp hay song song với thiết bị?', ['Mắc nối tiếp', 'Mắc song song', 'Không mắc vào mạch', 'Mắc tùy ý'], 0, 'Cầu chì mắc nối tiếp để khi quá dòng nó đứt, ngắt cả mạch bảo vệ thiết bị.', $D);
        $this->matching($L, 'Nối mỗi cách mắc với công thức tính điện trở tương đương.', [['Nối tiếp', 'R = R1 + R2'], ['Song song', '1/R = 1/R1 + 1/R2'], ['Hai R bằng nhau song song', 'R = R1/2'], ['Ba R nối tiếp', 'R = R1 + R2 + R3']], 'Nối tiếp cộng trực tiếp; song song cộng nghịch đảo.', $D);
        $this->matching($L, 'Nối mỗi cách mắc với đặc điểm của dòng điện.', [['Nối tiếp', 'I bằng nhau mọi nơi'], ['Song song', 'I chia theo các nhánh'], ['Nối tiếp', 'U chia theo điện trở'], ['Song song', 'U bằng nhau các nhánh']], 'Nối tiếp I chung, U chia; song song U chung, I chia.', $D);
        $this->matching($L, 'Nối mỗi ví dụ thực tế với cách mắc tương ứng.', [['Đèn nháy trang trí', 'Nối tiếp'], ['Ổ cắm trong nhà', 'Song song'], ['Cầu chì', 'Nối tiếp'], ['Các bóng đèn các phòng', 'Song song']], 'Đồ gia dụng mắc song song để dùng độc lập; công tắc, cầu chì mắc nối tiếp.', $D);
        $this->matching($L, 'Nối mỗi bài toán (R1 = 4Ω, R2 = 4Ω, U = 8V) với kết quả.', [['Nối tiếp: R tương đương', '8Ω'], ['Song song: R tương đương', '2Ω'], ['Nối tiếp: I qua mạch', '1A'], ['Song song: I mạch chính', '4A']], 'Nối tiếp R=8Ω, I=1A; song song R=2Ω, I=4A.', $D);
        $this->matching($L, 'Nối mỗi sự cố trong mạch với nguyên nhân của nó.', [['Cả dãy đèn tắt', 'Một bóng cháy (nối tiếp)'], ['Cầu dao nhảy', 'Ngắn mạch'], ['Đèn sáng mờ', 'Điện áp thấp'], ['Dây dẫn nóng', 'Quá tải']], 'Hiểu nguyên nhân giúp khắc phục sự cố mạch điện nhanh chóng.', $D);
        $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm MẠCH NỐI TIẾP hoặc MẠCH SONG SONG.', [['Dòng điện như nhau mọi nơi', 'MẠCH NỐI TIẾP'], ['Một chỗ đứt cả mạch tắt', 'MẠCH NỐI TIẾP'], ['Điện áp bằng nhau các nhánh', 'MẠCH SONG SONG'], ['Mỗi nhánh hoạt động độc lập', 'MẠCH SONG SONG']], 'Nối tiếp chung dòng; song song chung áp và độc lập.', $D);
        $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm LỚN HƠN hoặc NHỎ HƠN điện trở thành phần.', [['Nối tiếp 9Ω (R1=6Ω, R2=3Ω)', 'LỚN HƠN'], ['Nối tiếp 6Ω (ba R 2Ω)', 'LỚN HƠN'], ['Song song 2Ω (R1=6Ω, R2=3Ω)', 'NHỎ HƠN'], ['Song song 5Ω (hai R 10Ω)', 'NHỎ HƠN']], 'Nối tiếp R tương đương lớn hơn từng R; song song nhỏ hơn từng R.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm MẮC NỐI TIẾP hoặc SONG SONG trong thực tế.', [['Công tắc', 'MẮC NỐI TIẾP'], ['Cầu chì', 'MẮC NỐI TIẾP'], ['Bóng đèn các phòng', 'MẮC SONG SONG'], ['Tivi, tủ lạnh', 'MẮC SONG SONG']], 'Công tắc, cầu chì nối tiếp; đồ dùng gia đình song song.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['Song song: U các nhánh bằng nhau', 'ĐÚNG'], ['Nối tiếp: I qua các R bằng nhau', 'ĐÚNG'], ['Nối tiếp: R tương đương nhỏ hơn từng R', 'SAI'], ['Song song: một nhánh đứt cả mạch tắt', 'SAI']], 'Nối tiếp R tương đương lớn hơn; song song một nhánh đứt các nhánh khác vẫn chạy.', $D);
        $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm DÒNG ĐIỆN TĂNG hoặc GIẢM (U không đổi).', [['Thêm điện trở nối tiếp', 'GIẢM'], ['Tăng điện trở tương đương', 'GIẢM'], ['Thêm nhánh song song', 'TĂNG'], ['Giảm điện trở tương đương', 'TĂNG']], 'Theo định luật Ôm, R tăng thì I giảm và ngược lại.', $D);
        $this->fill($L, 'Mạch nối tiếp: điện trở tương đương ___ tổng các điện trở thành phần.', [[0, 'bằng']], 'R nối tiếp bằng tổng các điện trở thành phần.', $D);
        $this->fill($L, 'Mạch song song: nghịch đảo điện trở tương đương bằng ___ các nghịch đảo thành phần.', [[0, 'tổng']], 'Công thức: 1/R = 1/R1 + 1/R2 + ...', $D);
        $this->fill($L, 'Công tắc luôn được mắc ___ tiếp với thiết bị để đóng ngắt dòng điện.', [[0, 'nối']], 'Công tắc mắc nối tiếp mới ngắt được dòng qua thiết bị.', $D);
        $this->fill($L, 'Hai điện trở 10Ω mắc song song có điện trở tương đương là ___ Ω.', [[0, '5']], 'Hai R bằng nhau song song: R = 10/2 = 5Ω.', $D);
        $this->fill($L, 'Ba điện trở 2Ω mắc nối tiếp có điện trở tương đương là ___ Ω.', [[0, '6']], 'R = 2 + 2 + 2 = 6Ω.', $D);
    }

    // ===== 21. cong-nghe-thpt-10-lop-10-3 (Lop 10, trung_binh) =====
    private function seedCongNgheThpt10Lop103(): void
    {
        $L = 'cong-nghe-thpt-10-lop-10-3'; $D = 'trung_binh';
        $this->quiz($L, 'Dòng điện như thế nào được coi là nguy hiểm đối với con người?', ['Dòng trên 10mA đi qua tim', 'Dòng dưới 1mA', 'Dòng một chiều 5V', 'Mọi dòng điện đều an toàn'], 0, 'Dòng điện trên 10mA qua cơ thể, nhất là qua tim, có thể gây nguy hiểm tính mạng.', $D);
        $this->quiz($L, 'Điện áp an toàn cho phép đối với người thường là bao nhiêu?', ['Dưới 24V', '220V', '380V', '1000V'], 0, 'Điện áp dưới 24V (khô ráo) được coi là an toàn với người.', $D);
        $this->quiz($L, 'Aptomat (CB) khác cầu chì ở điểm nào?', ['Tự ngắt và đóng lại được nhiều lần', 'Chỉ dùng một lần', 'Không bảo vệ được', 'Chỉ dùng cho đèn'], 0, 'Aptomat tự ngắt khi sự cố và đóng lại dùng tiếp; cầu chì đứt phải thay mới.', $D);
        $this->quiz($L, 'Dây tiếp đất (dây nối đất) có tác dụng gì?', ['Dẫn dòng điện rò xuống đất, bảo vệ người', 'Làm đẹp cho thiết bị', 'Tăng công suất thiết bị', 'Giảm tiền điện'], 0, 'Dây tiếp đất dẫn dòng rò xuống đất nên vỏ thiết bị không gây giật.', $D);
        $this->quiz($L, 'Khi sửa chữa điện, ngoài ngắt nguồn còn nên làm gì?', ['Dùng bút thử điện kiểm tra trước khi chạm', 'Sờ tay thử xem còn điện không', 'Nhờ trẻ nhỏ giữ dây', 'Làm một mình trên thang kim loại'], 0, 'Sau khi ngắt điện phải dùng bút thử điện kiểm tra chắc chắn hết điện mới sửa.', $D);
        $this->matching($L, 'Nối mỗi thiết bị bảo vệ với chức năng của nó.', [['Aptomat', 'Tự ngắt khi quá tải'], ['Cầu chì', 'Đứt khi quá dòng'], ['Rơ le chống giật', 'Ngắt khi rò điện'], ['Ổn áp', 'Ổn định điện áp']], 'Mỗi thiết bị bảo vệ đảm nhận một chức năng an toàn riêng.', $D);
        $this->matching($L, 'Nối mỗi nguyên nhân tai nạn điện với biện pháp phòng tránh.', [['Chạm trực tiếp', 'Không chạm vật mang điện'], ['Chập điện', 'Dùng aptomat'], ['Sét đánh', 'Lắp cột thu lôi'], ['Rò điện', 'Dùng dây tiếp đất']], 'Mỗi nguyên nhân có biện pháp phòng tránh tương ứng.', $D);
        $this->matching($L, 'Nối mỗi mức điện áp với đánh giá mức độ.', [['Dưới 12V', 'An toàn'], ['Điện 220V gia đình', 'Nguy hiểm'], ['Dây cao thế', 'Cực kỳ nguy hiểm'], ['Pin 1,5V', 'An toàn']], 'Điện áp càng cao càng nguy hiểm; dưới 24V thì an toàn.', $D);
        $this->matching($L, 'Nối mỗi việc làm với đánh giá đúng hay sai về an toàn.', [['Sửa điện khi đã ngắt nguồn', 'Đúng'], ['Dùng bút thử điện kiểm tra', 'Đúng'], ['Đứng trên thang kim loại sửa điện', 'Sai'], ['Tay ướt bật công tắc', 'Sai']], 'Ngắt nguồn và kiểm tra bằng bút thử điện là đúng; thang kim loại và tay ướt là sai.', $D);
        $this->matching($L, 'Nối mỗi hiện tượng khi có giông sét với cách phòng tránh.', [['Sấm sét lớn', 'Không trú dưới cây to'], ['Đang ở ngoài đồng', 'Ngồi thấp, tránh chỗ cao'], ['Đang ở trong nhà', 'Không chạm vật kim loại'], ['Đang đi xe ô tô', 'Ở yên trong xe']], 'Khi có sét phải tránh chỗ cao, cây to và vật kim loại.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm AN TOÀN hoặc NGUY HIỂM.', [['Ngắt điện trước khi sửa', 'AN TOÀN'], ['Dùng bút thử điện kiểm tra', 'AN TOÀN'], ['Sờ vào dây trần', 'NGUY HIỂM'], ['Sửa điện một mình trên cao', 'NGUY HIỂM']], 'Ngắt điện và kiểm tra là an toàn; sờ dây trần rất nguy hiểm.', $D);
        $this->sortQ($L, 'Kéo mỗi mức điện áp vào nhóm AN TOÀN hoặc NGUY HIỂM với người.', [['12V', 'AN TOÀN'], ['24V', 'AN TOÀN'], ['220V', 'NGUY HIỂM'], ['10000V', 'NGUY HIỂM']], 'Dưới 24V an toàn; 220V trở lên nguy hiểm.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm BẢO VỆ QUÁ TẢI hoặc BẢO VỆ RÒ ĐIỆN.', [['Aptomat', 'BẢO VỆ QUÁ TẢI'], ['Cầu chì', 'BẢO VỆ QUÁ TẢI'], ['ELCB chống giật', 'BẢO VỆ RÒ ĐIỆN'], ['Dây tiếp đất', 'BẢO VỆ RÒ ĐIỆN']], 'Aptomat, cầu chì chống quá tải; ELCB và tiếp đất chống rò điện.', $D);
        $this->sortQ($L, 'Kéo mỗi hành động vào nhóm NÊN hoặc KHÔNG NÊN khi có bão lớn.', [['Rút phích đồ điện', 'NÊN'], ['Tắt aptomat tổng', 'NÊN'], ['Ra sân xem sét', 'KHÔNG NÊN'], ['Gọi điện thoại có dây', 'KHÔNG NÊN']], 'Bão lớn nên ngắt bớt điện; không ra ngoài xem sét.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['Điện áp càng cao càng nguy hiểm', 'ĐÚNG'], ['Aptomat có thể thay cầu chì', 'ĐÚNG'], ['Dòng 5mA tuyệt đối an toàn', 'SAI'], ['Chim đậu dây điện vì chân cách điện', 'SAI']], 'Chim không bị giật vì không tạo mạch kín qua người nó, không phải do chân cách điện.', $D);
        $this->fill($L, 'Điện áp an toàn với người thường dưới ___ V.', [[0, '24']], 'Dưới 24V được coi là điện áp an toàn với người trong điều kiện khô ráo.', $D);
        $this->fill($L, 'Thiết bị tự động ngắt mạch khi có dòng điện rò gọi là rơ le chống ___.', [[0, 'giật']], 'Rơ le chống giật bảo vệ người khi thiết bị bị rò điện.', $D);
        $this->fill($L, 'Cột thu ___ giúp bảo vệ ngôi nhà khỏi bị sét đánh.', [[0, 'lôi']], 'Cột thu lôi dẫn sét xuống đất, bảo vệ công trình.', $D);
        $this->fill($L, 'Trước khi sửa điện phải dùng bút thử điện kiểm tra xem còn ___ không.', [[0, 'điện']], 'Bút thử điện giúp xác nhận đã hết điện trước khi chạm tay vào.', $D);
        $this->fill($L, 'Dây ___ đất của vỏ máy giặt giúp an toàn khi máy bị rò điện.', [[0, 'tiếp']], 'Dây tiếp đất dẫn dòng rò xuống đất nên chạm vỏ máy không bị giật.', $D);
    }

    // ===== 22. cong-nghe-thpt-10-lop-10-4 (Lop 10, kho) =====
    private function seedCongNgheThpt10Lop104(): void
    {
        $L = 'cong-nghe-thpt-10-lop-10-4'; $D = 'kho';
        $this->quiz($L, 'Khi ép tim ngoài lồng ngực, nạn nhân phải được đặt ở tư thế nào?', ['Nằm ngửa trên mặt phẳng cứng', 'Nằm nghiêng trên nệm mềm', 'Ngồi dựa vào tường', 'Nằm sấp'], 0, 'Nạn nhân nằm ngửa trên mặt cứng thì lực ép tim mới hiệu quả.', $D);
        $this->quiz($L, 'Độ sâu ép tim ngoài lồng ngực cho người lớn là bao nhiêu?', ['5 – 6 cm', '1 – 2 cm', '10 – 12 cm', 'Càng sâu càng tốt'], 0, 'Ép sâu 5-6cm với tần suất 100-120 lần/phút cho người lớn.', $D);
        $this->quiz($L, 'Khi hà hơi thổi ngạt cho nạn nhân, cần làm gì với mũi nạn nhân?', ['Bịt kín mũi lại', 'Để mũi thoáng', 'Nhỏ nước vào mũi', 'Bịt một bên mũi'], 0, 'Bịt mũi để hơi thổi vào miệng không thoát ra ngoài.', $D);
        $this->quiz($L, 'Tỉ lệ ép tim và thổi ngạt khi sơ cứu một mình là bao nhiêu?', ['30 lần ép tim : 2 lần thổi ngạt', '5 : 1', '15 : 5', '10 : 10'], 0, 'Tỉ lệ chuẩn là 30:2, lặp lại liên tục đến khi có y tế.', $D);
        $this->quiz($L, 'Sau khi nạn nhân bị điện giật đã tỉnh lại, cần làm gì tiếp?', ['Giữ ấm, theo dõi và đưa đi bệnh viện', 'Cho về nhà ngay', 'Cho ăn uống no nê', 'Để nạn nhân tự lái xe về'], 0, 'Nạn nhân tỉnh vẫn cần theo dõi và đi bệnh viện vì có thể biến chứng muộn.', $D);
        $this->matching($L, 'Nối mỗi bước sơ cứu điện giật với nội dung của nó.', [['Tách nguồn', 'Ngắt điện, tách nạn nhân'], ['Kiểm tra', 'Hơi thở và mạch'], ['Ép tim', '100-120 lần/phút'], ['Thổi ngạt', 'Tỉ lệ 30:2']], 'Trình tự sơ cứu: tách nguồn, kiểm tra, ép tim thổi ngạt.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu với mức độ của nạn nhân.', [['Tỉnh táo', 'Nhẹ'], ['Bỏng da', 'Trung bình'], ['Ngừng thở', 'Nặng'], ['Tim ngừng đập', 'Rất nặng']], 'Đánh giá mức độ giúp sơ cứu đúng và gọi hỗ trợ kịp thời.', $D);
        $this->matching($L, 'Nối mỗi vật dụng với vai trò khi sơ cứu.', [['Gậy gỗ khô', 'Tách nguồn điện'], ['Khăn khô', 'Lót tay cầm'], ['Điện thoại', 'Gọi cấp cứu 115'], ['Chăn mỏng', 'Giữ ấm nạn nhân']], 'Mỗi vật dụng có vai trò riêng trong quá trình sơ cứu.', $D);
        $this->matching($L, 'Nối mỗi số điện thoại khẩn cấp với chức năng.', [['115', 'Cấp cứu y tế'], ['114', 'Cứu hỏa'], ['113', 'Công an'], ['111', 'Bảo vệ trẻ em']], 'Ghi nhớ các số khẩn cấp để gọi đúng khi cần.', $D);
        $this->matching($L, 'Nối mỗi sai lầm với hậu quả khi sơ cứu.', [['Chạm tay trần vào nạn nhân', 'Bị giật theo'], ['Ngừng ép tim quá sớm', 'Mất cơ hội sống'], ['Cho nạn nhân bất tỉnh uống nước', 'Dễ bị sặc'], ['Di chuyển mạnh nạn nhân', 'Tổn thương thêm']], 'Sai lầm khi sơ cứu có thể làm nạn nhân nặng thêm.', $D);
        $this->sortQ($L, 'Kéo mỗi hành động vào nhóm ĐÚNG hoặc SAI khi sơ cứu.', [['Ép tim ngay khi tim ngừng', 'ĐÚNG'], ['Gọi 115 đồng thời', 'ĐÚNG'], ['Chờ người nhà đến mới cứu', 'SAI'], ['Bỏ cuộc sau 1 phút', 'SAI']], 'Sơ cứu phải bắt đầu ngay, kiên trì đến khi y tế đến.', $D);
        $this->sortQ($L, 'Kéo mỗi bước vào nhóm TRƯỚC hoặc SAU khi tách nguồn điện.', [['Ngắt cầu dao', 'TRƯỚC'], ['Dùng gậy gạt dây điện', 'TRƯỚC'], ['Ép tim thổi ngạt', 'SAU'], ['Đưa nạn nhân đi viện', 'SAU']], 'Phải tách nguồn an toàn trước rồi mới chạm vào nạn nhân sơ cứu.', $D);
        $this->sortQ($L, 'Kéo mỗi vật vào nhóm DÙNG ĐƯỢC hoặc KHÔNG để tách nguồn điện.', [['Gậy tre khô', 'DÙNG ĐƯỢC'], ['Ghế gỗ', 'DÙNG ĐƯỢC'], ['Thang nhôm', 'KHÔNG'], ['Dây cáp sắt', 'KHÔNG']], 'Vật cách điện khô dùng được; vật kim loại không dùng được.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm CÒN SỐNG hoặc NGUY KỊCH.', [['Còn thở', 'CÒN SỐNG'], ['Mạch còn đập', 'CÒN SỐNG'], ['Ngừng thở', 'NGUY KỊCH'], ['Đồng tử giãn', 'NGUY KỊCH']], 'Ngừng thở, đồng tử giãn là dấu hiệu nguy kịch cần ép tim ngay.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm LÀM NGAY hoặc SAU KHI ỔN ĐỊNH.', [['Ép tim thổi ngạt', 'LÀM NGAY'], ['Cầm máu vết bỏng', 'LÀM NGAY'], ['Hỏi tên tuổi nạn nhân', 'SAU KHI ỔN ĐỊNH'], ['Đưa đi bệnh viện', 'SAU KHI ỔN ĐỊNH']], 'Cứu sống là ưu tiên làm ngay; thủ tục khác làm sau.', $D);
        $this->fill($L, 'Ép tim ngoài lồng ngực cho người lớn với độ sâu khoảng ___ cm.', [[0, '5']], 'Ép sâu 5-6cm, tần suất 100-120 lần/phút mới hiệu quả.', $D);
        $this->fill($L, 'Tỉ lệ ép tim và thổi ngạt là ___ lần ép tim ứng với 2 lần thổi ngạt.', [[0, '30']], 'Tỉ lệ 30:2 được lặp lại liên tục khi sơ cứu.', $D);
        $this->fill($L, 'Khi thổi ngạt phải ___ mũi nạn nhân để hơi không thoát ra ngoài.', [[0, 'bịt']], 'Bịt mũi giúp hơi thổi vào phổi nạn nhân hiệu quả.', $D);
        $this->fill($L, 'Nạn nhân bị điện giật cần được đặt nằm nơi ___ ráo, thoáng khí.', [[0, 'khô']], 'Nơi khô ráo thoáng khí giúp sơ cứu an toàn và hiệu quả.', $D);
        $this->fill($L, 'Không cho nạn nhân đang bất tỉnh ___ nước vì rất dễ bị sặc.', [[0, 'uống']], 'Người bất tỉnh không nuốt được, cho uống nước dễ sặc nguy hiểm.', $D);
    }

    // ===== 23. cong-nghe-thpt-11-lop-11-1 (Lop 11, trung_binh) =====
    private function seedCongNgheThpt11Lop111(): void
    {
        $L = 'cong-nghe-thpt-11-lop-11-1'; $D = 'trung_binh';
        $this->quiz($L, 'Nét đứt mảnh trên bản vẽ kỹ thuật dùng để vẽ gì?', ['Cạnh khuất, đường bao khuất', 'Cạnh thấy', 'Đường tâm', 'Khung tên'], 0, 'Nét đứt mảnh vẽ các cạnh khuất không nhìn thấy được.', $D);
        $this->quiz($L, 'Nét lượn sóng trên bản vẽ kỹ thuật dùng để vẽ gì?', ['Đường giới hạn của hình cắt một phần', 'Đường tròn', 'Khung bản vẽ', 'Chữ số'], 0, 'Nét lượn sóng vẽ đường giới hạn hình cắt, hình trích một phần.', $D);
        $this->quiz($L, 'Khung tên trên bản vẽ kỹ thuật được đặt ở vị trí nào?', ['Góc dưới bên phải', 'Góc trên bên trái', 'Chính giữa', 'Tùy ý'], 0, 'Khung tên luôn đặt ở góc dưới bên phải của bản vẽ.', $D);
        $this->quiz($L, 'Tỉ lệ 1:2 trên bản vẽ có ý nghĩa gì?', ['Hình vẽ nhỏ bằng một nửa vật thật', 'Hình vẽ to gấp đôi vật thật', 'Vật thật dài 1m', 'Không có ý nghĩa'], 0, 'Tỉ lệ 1:2 là thu nhỏ, hình vẽ bằng nửa kích thước thật.', $D);
        $this->quiz($L, 'Chữ số kích thước trên bản vẽ kỹ thuật có đơn vị mặc định là gì?', ['Milimét (mm)', 'Xentimét (cm)', 'Mét (m)', 'Kilômét (km)'], 0, 'Đơn vị mặc định trên bản vẽ kỹ thuật là milimét, không cần ghi đơn vị.', $D);
        $this->matching($L, 'Nối mỗi loại nét vẽ với ứng dụng của nó.', [['Nét liền đậm', 'Cạnh thấy'], ['Nét đứt mảnh', 'Cạnh khuất'], ['Nét chấm gạch mảnh', 'Đường tâm'], ['Nét lượn sóng', 'Giới hạn hình cắt']], 'Mỗi loại nét vẽ một đối tượng khác nhau trên bản vẽ.', $D);
        $this->matching($L, 'Nối mỗi khổ giấy với kích thước của nó.', [['A4', '210 × 297'], ['A3', '297 × 420'], ['A2', '420 × 594'], ['A1', '594 × 841']], 'Các khổ giấy A có quan hệ gấp đôi diện tích liên tiếp.', $D);
        $this->matching($L, 'Nối mỗi tỉ lệ với ý nghĩa của nó.', [['1:1', 'Bằng vật thật'], ['2:1', 'Phóng to gấp đôi'], ['1:2', 'Thu nhỏ một nửa'], ['1:5', 'Thu nhỏ nhiều']], 'Tỉ lệ cho biết hình vẽ to hay nhỏ hơn vật thật bao nhiêu.', $D);
        $this->matching($L, 'Nối mỗi thành phần với nội dung trong khung tên.', [['Tên gọi', 'Tên chi tiết'], ['Vật liệu', 'Chất liệu chế tạo'], ['Tỉ lệ', 'Tỉ lệ bản vẽ'], ['Người vẽ', 'Họ tên người vẽ']], 'Khung tên chứa thông tin quản lý bản vẽ: tên, vật liệu, tỉ lệ.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ với công dụng trong vẽ kỹ thuật.', [['Compa', 'Vẽ đường tròn'], ['Êke', 'Vẽ góc vuông'], ['Thước tỉ lệ', 'Đo theo tỉ lệ'], ['Bút chì cứng', 'Vẽ nét mảnh']], 'Dụng cụ vẽ kỹ thuật mỗi loại một công dụng riêng.', $D);
        $this->sortQ($L, 'Kéo mỗi loại nét vào nhóm NÉT THẤY hoặc NÉT KHUẤT.', [['Nét liền đậm', 'NÉT THẤY'], ['Nét liền mảnh', 'NÉT THẤY'], ['Nét đứt mảnh', 'NÉT KHUẤT'], ['Nét chấm gạch mảnh', 'NÉT THẤY']], 'Nét liền và chấm gạch vẽ phần thấy; nét đứt vẽ phần khuất.', $D);
        $this->sortQ($L, 'Kéo mỗi khổ giấy vào nhóm LỚN HƠN A4 hoặc NHỎ HƠN A4.', [['A3', 'LỚN HƠN A4'], ['A2', 'LỚN HƠN A4'], ['A5', 'NHỎ HƠN A4'], ['A6', 'NHỎ HƠN A4']], 'Số càng nhỏ khổ càng lớn: A1 > A2 > A3 > A4 > A5.', $D);
        $this->sortQ($L, 'Kéo mỗi tỉ lệ vào nhóm PHÓNG TO hoặc THU NHỎ.', [['2:1', 'PHÓNG TO'], ['5:1', 'PHÓNG TO'], ['1:2', 'THU NHỎ'], ['1:10', 'THU NHỎ']], 'Tỉ lệ >1 phóng to; tỉ lệ <1 thu nhỏ.', $D);
        $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm CÓ hoặc KHÔNG trong khung tên.', [['Tên chi tiết', 'CÓ'], ['Vật liệu', 'CÓ'], ['Ngày vẽ', 'CÓ'], ['Giá tiền sản phẩm', 'KHÔNG']], 'Khung tên có tên, vật liệu, ngày vẽ; không có giá tiền.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['Nét đứt vẽ cạnh khuất', 'ĐÚNG'], ['Tỉ lệ 1:1 là nguyên hình', 'ĐÚNG'], ['Khung tên đặt ở góc trên', 'SAI'], ['Đường tâm vẽ bằng nét liền đậm', 'SAI']], 'Khung tên ở góc dưới phải; đường tâm vẽ nét chấm gạch mảnh.', $D);
        $this->fill($L, 'Cạnh khuất trên bản vẽ được vẽ bằng nét ___ mảnh.', [[0, 'đứt']], 'Nét đứt mảnh là quy ước vẽ các cạnh bị khuất.', $D);
        $this->fill($L, 'Khung tên được đặt ở góc dưới bên ___ của bản vẽ.', [[0, 'phải']], 'Vị trí khung tên thống nhất ở góc dưới bên phải.', $D);
        $this->fill($L, 'Tỉ lệ ___ là tỉ lệ phóng to gấp đôi vật thật.', [[0, '2:1']], 'Tỉ lệ 2:1 nghĩa là 2 đơn vị trên vẽ bằng 1 đơn vị thật.', $D);
        $this->fill($L, 'Nét ___ sóng dùng để vẽ đường giới hạn của hình cắt một phần.', [[0, 'lượn']], 'Nét lượn sóng vẽ tự do, không dùng thước.', $D);
        $this->fill($L, 'Chữ và số trên bản vẽ kỹ thuật phải viết theo kiểu chữ ___ thuật.', [[0, 'kỹ']], 'Chữ kỹ thuật viết rõ ràng, thống nhất theo tiêu chuẩn.', $D);
    }

    // ===== 24. cong-nghe-thpt-11-lop-11-2 (Lop 11, kho) =====
    private function seedCongNgheThpt11Lop112(): void
    {
        $L = 'cong-nghe-thpt-11-lop-11-2'; $D = 'kho';
        $this->quiz($L, 'Hình chiếu cạnh được đặt ở vị trí nào so với hình chiếu đứng?', ['Bên phải hình chiếu đứng', 'Bên trái hình chiếu đứng', 'Phía trên hình chiếu đứng', 'Phía dưới hình chiếu đứng'], 0, 'Hình chiếu cạnh đặt bên phải, hình chiếu bằng đặt phía dưới hình chiếu đứng.', $D);
        $this->quiz($L, 'Phép chiếu song song là gì?', ['Phép chiếu có các tia chiếu song song với nhau', 'Phép chiếu có tia chiếu vuông góc', 'Phép chiếu có tia đồng quy', 'Phép chiếu không có tia chiếu'], 0, 'Phép chiếu song song gồm vuông góc và xiên góc, tia chiếu song song nhau.', $D);
        $this->quiz($L, 'Hình chiếu trục đo vuông góc đều có đặc điểm gì?', ['Ba trục hợp nhau góc 120 độ', 'Hai trục vuông góc', 'Ba trục trùng nhau', 'Không có trục nào'], 0, 'Trục đo vuông góc đều: ba góc trục bằng nhau 120°, hệ số biến dạng bằng 1.', $D);
        $this->quiz($L, 'Hình chiếu phối cảnh khác hình chiếu trục đo ở điểm nào?', ['Có điểm tụ nên giống mắt người nhìn', 'Không thể hiện chiều sâu', 'Chỉ vẽ được hình vuông', 'Không dùng trong kỹ thuật'], 0, 'Phối cảnh có điểm tụ, càng xa càng nhỏ, giống mắt nhìn thực tế.', $D);
        $this->quiz($L, 'Muốn biết chiều cao của vật thể, ta xem hình chiếu nào?', ['Hình chiếu đứng hoặc hình chiếu cạnh', 'Hình chiếu bằng', 'Hình chiếu trục đo xiên', 'Không xem được'], 0, 'Chiều cao thể hiện trên hình chiếu đứng và hình chiếu cạnh.', $D);
        $this->matching($L, 'Nối mỗi hình chiếu với hướng nhìn tương ứng.', [['Hình chiếu đứng', 'Nhìn từ phía trước'], ['Hình chiếu bằng', 'Nhìn từ phía trên'], ['Hình chiếu cạnh', 'Nhìn từ phía trái'], ['Hình chiếu trục đo', 'Nhìn xiên góc']], 'Mỗi hình chiếu là kết quả nhìn vật thể từ một hướng.', $D);
        $this->matching($L, 'Nối mỗi khối hình học với hình chiếu bằng của nó.', [['Hình trụ đứng', 'Hình tròn'], ['Hình nón cụt đứng', 'Hình tròn'], ['Hình hộp chữ nhật', 'Hình chữ nhật'], ['Hình chóp tứ giác', 'Hình vuông']], 'Khối tròn xoay đứng có hình chiếu bằng là hình tròn.', $D);
        $this->matching($L, 'Nối mỗi loại hình chiếu với đặc điểm của nó.', [['Trục đo vuông góc đều', 'Ba góc trục 120°'], ['Trục đo xiên góc cân', 'Hai hệ số bằng nhau'], ['Phối cảnh', 'Có điểm tụ'], ['Vuông góc', 'Tia chiếu vuông góc']], 'Mỗi loại hình chiếu có đặc điểm và ứng dụng riêng.', $D);
        $this->matching($L, 'Nối mỗi kích thước với hình chiếu thể hiện nó.', [['Chiều cao', 'Hình chiếu đứng'], ['Chiều rộng', 'Hình chiếu bằng'], ['Chiều dài', 'Cả đứng và bằng'], ['Đường kính đáy trụ', 'Hình chiếu bằng']], 'Mỗi kích thước thể hiện rõ trên hình chiếu nhất định.', $D);
        $this->matching($L, 'Nối mỗi vật thể với số hình chiếu vuông góc cần thiết.', [['Khối hộp đơn giản', '2 hình'], ['Chi tiết phức tạp', '3 hình'], ['Vật tròn xoay', '2 hình'], ['Minh họa nhanh', '1 hình trục đo']], 'Vật đơn giản cần ít hình chiếu; vật phức tạp cần đủ 3 hình.', $D);
        $this->sortQ($L, 'Kéo mỗi mô tả vào nhóm HÌNH CHIẾU ĐỨNG, BẰNG hoặc CẠNH.', [['Nhìn vật từ phía trước', 'HÌNH CHIẾU ĐỨNG'], ['Nhìn vật từ phía trên', 'HÌNH CHIẾU BẰNG'], ['Nhìn vật từ phía trái', 'HÌNH CHIẾU CẠNH'], ['Đặt phía dưới hình đứng', 'HÌNH CHIẾU BẰNG']], 'Vị trí và hướng nhìn xác định tên từng hình chiếu.', $D);
        $this->sortQ($L, 'Kéo mỗi hình vào nhóm CÓ hoặc KHÔNG phải hình chiếu vuông góc.', [['Hình chiếu đứng', 'CÓ'], ['Hình chiếu bằng', 'CÓ'], ['Hình chiếu trục đo', 'KHÔNG'], ['Hình chiếu phối cảnh', 'KHÔNG']], 'Ba hình chiếu đứng, bằng, cạnh là vuông góc; trục đo và phối cảnh thì không.', $D);
        $this->sortQ($L, 'Kéo mỗi khối vào nhóm HÌNH CHIẾU BẰNG LÀ TRÒN hoặc KHÔNG.', [['Hình trụ đứng', 'HÌNH CHIẾU BẰNG LÀ TRÒN'], ['Hình nón cụt đứng', 'HÌNH CHIẾU BẰNG LÀ TRÒN'], ['Hình hộp', 'KHÔNG'], ['Hình chóp tứ giác', 'KHÔNG']], 'Khối tròn xoay đặt đứng có hình chiếu bằng là hình tròn.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['Hình chiếu cạnh đặt bên phải hình đứng', 'ĐÚNG'], ['Trục đo thể hiện cả ba chiều', 'ĐÚNG'], ['Phép chiếu vuông góc có tia xiên', 'SAI'], ['Hình bằng đặt phía trên hình đứng', 'SAI']], 'Vuông góc thì tia chiếu vuông góc; hình bằng đặt phía dưới hình đứng.', $D);
        $this->sortQ($L, 'Kéo mỗi kích thước vào nhóm thể hiện TRÊN HÌNH ĐỨNG hoặc HÌNH BẰNG.', [['Chiều cao', 'TRÊN HÌNH ĐỨNG'], ['Chiều dài', 'TRÊN HÌNH ĐỨNG'], ['Chiều rộng', 'TRÊN HÌNH BẰNG'], ['Đường kính đáy', 'TRÊN HÌNH BẰNG']], 'Hình đứng thể hiện dài và cao; hình bằng thể hiện dài và rộng.', $D);
        $this->fill($L, 'Hình chiếu ___ là hình chiếu thu được khi nhìn vật thể từ trên xuống.', [[0, 'bằng']], 'Hình chiếu bằng nhìn từ trên xuống, đặt phía dưới hình chiếu đứng.', $D);
        $this->fill($L, 'Trong hình chiếu trục đo vuông góc đều, ba trục hợp nhau góc ___ độ.', [[0, '120']], 'Ba góc trục bằng nhau 120° là đặc trưng của trục đo vuông góc đều.', $D);
        $this->fill($L, 'Phép chiếu có các tia chiếu song song với nhau gọi là phép chiếu ___ song.', [[0, 'song']], 'Phép chiếu song song gồm hai loại: vuông góc và xiên góc.', $D);
        $this->fill($L, 'Hình chiếu ___ cảnh có điểm tụ nên giống như mắt người nhìn.', [[0, 'phối']], 'Phối cảnh dùng nhiều trong kiến trúc để minh họa công trình.', $D);
        $this->fill($L, 'Muốn biết đầy đủ ba kích thước dài, rộng, cao cần ít nhất ___ hình chiếu vuông góc.', [[0, '2']], 'Hai hình chiếu vuông góc (đứng và bằng) đã đủ thể hiện ba kích thước.', $D);
    }

    // ===== 25. cong-nghe-thpt-11-lop-11-3 (Lop 11, trung_binh) =====
    private function seedCongNgheThpt11Lop113(): void
    {
        $L = 'cong-nghe-thpt-11-lop-11-3'; $D = 'trung_binh';
        $this->quiz($L, 'Kích thước lắp ghép trên bản vẽ chi tiết cho biết điều gì?', ['Kích thước các bề mặt tiếp xúc với chi tiết khác', 'Kích thước bao ngoài cùng', 'Vị trí các lỗ', 'Màu sắc chi tiết'], 0, 'Kích thước lắp ghép ghi các bề mặt tiếp xúc để lắp vừa với chi tiết khác.', $D);
        $this->quiz($L, 'Yêu cầu kỹ thuật trên bản vẽ chi tiết thường ghi những gì?', ['Vật liệu, xử lý nhiệt và độ nhẵn bề mặt', 'Giá bán sản phẩm', 'Tên khách hàng', 'Địa chỉ nhà máy'], 0, 'Yêu cầu kỹ thuật ghi vật liệu, nhiệt luyện, độ nhẵn để chế tạo đúng.', $D);
        $this->quiz($L, 'Kí hiệu "□40" trên bản vẽ kỹ thuật có ý nghĩa gì?', ['Tiết diện vuông có cạnh 40mm', 'Đường kính 40mm', 'Bán kính 40mm', 'Chiều dài 40m'], 0, 'Kí hiệu □ chỉ tiết diện vuông, số sau là độ dài cạnh.', $D);
        $this->quiz($L, 'Đường ghi kích thước trên bản vẽ được vẽ bằng nét gì?', ['Nét liền mảnh', 'Nét liền đậm', 'Nét đứt', 'Nét chấm gạch'], 0, 'Đường ghi, đường gióng kích thước đều vẽ bằng nét liền mảnh.', $D);
        $this->quiz($L, 'Mũi tên chỉ kích thước được đặt ở vị trí nào?', ['Hai đầu của đường ghi kích thước', 'Giữa đường ghi', 'Ngoài khung tên', 'Tùy ý người vẽ'], 0, 'Hai mũi tên ở hai đầu đường ghi, chạm vào đường gióng.', $D);
        $this->matching($L, 'Nối mỗi kí hiệu với ý nghĩa trên bản vẽ.', [['Ø', 'Đường kính'], ['R', 'Bán kính'], ['□', 'Tiết diện vuông'], ['Sr', 'Bán kính mặt cầu']], 'Các kí hiệu giúp đọc nhanh kích thước trên bản vẽ.', $D);
        $this->matching($L, 'Nối mỗi loại kích thước với nội dung của nó.', [['Định hình', 'Độ lớn của chi tiết'], ['Định vị', 'Vị trí tương quan'], ['Lắp ghép', 'Bề mặt tiếp xúc'], ['Bao ngoài', 'Kích thước lớn nhất']], 'Ba loại kích thước: định hình, định vị và lắp ghép.', $D);
        $this->matching($L, 'Nối mỗi bước với nội dung khi đọc bản vẽ chi tiết.', [['Khung tên', 'Tên và vật liệu'], ['Hình biểu diễn', 'Hình dạng chi tiết'], ['Kích thước', 'Độ lớn các phần'], ['Yêu cầu kỹ thuật', 'Cách gia công']], 'Đọc bản vẽ theo trình tự: khung tên, hình vẽ, kích thước, yêu cầu.', $D);
        $this->matching($L, 'Nối mỗi đường nét với vai trò khi ghi kích thước.', [['Đường gióng', 'Giới hạn phạm vi'], ['Đường ghi', 'Chứa chữ số'], ['Mũi tên', 'Chỉ hai đầu'], ['Chữ số', 'Giá trị đo']], 'Bộ phận ghi kích thước gồm đường gióng, đường ghi, mũi tên và chữ số.', $D);
        $this->matching($L, 'Nối mỗi yêu cầu kỹ thuật với ý nghĩa của nó.', [['Độ nhẵn bề mặt', 'Độ bóng láng'], ['Nhiệt luyện', 'Tăng độ cứng'], ['Mạ kẽm', 'Chống gỉ'], ['Dung sai', 'Độ chính xác']], 'Yêu cầu kỹ thuật quyết định chất lượng chế tạo chi tiết.', $D);
        $this->sortQ($L, 'Kéo mỗi kí hiệu vào nhóm ĐƯỜNG KÍNH hoặc BÁN KÍNH.', [['Ø30', 'ĐƯỜNG KÍNH'], ['Ø12', 'ĐƯỜNG KÍNH'], ['R15', 'BÁN KÍNH'], ['R8', 'BÁN KÍNH']], 'Ø chỉ đường kính, R chỉ bán kính.', $D);
        $this->sortQ($L, 'Kéo mỗi kích thước vào nhóm ĐỊNH HÌNH hoặc ĐỊNH VỊ.', [['Chiều dài chi tiết', 'ĐỊNH HÌNH'], ['Đường kính lỗ', 'ĐỊNH HÌNH'], ['Khoảng cách hai lỗ', 'ĐỊNH VỊ'], ['Tâm lỗ cách mép', 'ĐỊNH VỊ']], 'Định hình cho độ lớn; định vị cho vị trí tương quan.', $D);
        $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm ĐỌC Ở KHUNG TÊN hoặc ĐỌC Ở HÌNH VẼ.', [['Tên chi tiết', 'ĐỌC Ở KHUNG TÊN'], ['Vật liệu', 'ĐỌC Ở KHUNG TÊN'], ['Hình dạng', 'ĐỌC Ở HÌNH VẼ'], ['Kích thước', 'ĐỌC Ở HÌNH VẼ']], 'Khung tên cho thông tin chung; hình vẽ cho hình dạng và kích thước.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['Ø chỉ đường kính', 'ĐÚNG'], ['Đường ghi vẽ nét liền mảnh', 'ĐÚNG'], ['Số kích thước ghi thêm đơn vị mm', 'SAI'], ['R chỉ đường kính', 'SAI']], 'Đơn vị mm là mặc định nên không ghi; R chỉ bán kính.', $D);
        $this->sortQ($L, 'Kéo mỗi con số vào nhóm KÍCH THƯỚC hoặc SỐ THỨ TỰ.', [['Ø20', 'KÍCH THƯỚC'], ['R10', 'KÍCH THƯỚC'], ['Số 1 trong vòng tròn', 'SỐ THỨ TỰ'], ['Số 2 trong khung tên', 'SỐ THỨ TỰ']], 'Số kèm Ø, R là kích thước; số thứ tự đánh dấu chi tiết.', $D);
        $this->fill($L, 'Kí hiệu ___ trên bản vẽ chỉ tiết diện vuông của chi tiết.', [[0, '□']], 'Kí hiệu □ đặt trước số chỉ cạnh của tiết diện vuông.', $D);
        $this->fill($L, 'Đường ___ kích thước được vẽ bằng nét liền mảnh.', [[0, 'ghi']], 'Đường ghi chứa chữ số kích thước, vẽ bằng nét liền mảnh.', $D);
        $this->fill($L, 'Kích thước ___ vị cho biết vị trí tương quan giữa các phần của chi tiết.', [[0, 'định']], 'Kích thước định vị xác định vị trí các lỗ, rãnh so với nhau.', $D);
        $this->fill($L, 'Chữ số kích thước được đặt ở ___ đường ghi kích thước.', [[0, 'trên']], 'Chữ số đặt phía trên đường ghi, ở khoảng giữa.', $D);
        $this->fill($L, 'Đơn vị milimét trên bản vẽ kỹ thuật được viết tắt là ___.', [[0, 'mm']], 'mm là đơn vị mặc định nên thường không cần ghi trên bản vẽ.', $D);
    }

    // ===== 26. cong-nghe-thpt-11-lop-11-4 (Lop 11, kho) =====
    private function seedCongNgheThpt11Lop114(): void
    {
        $L = 'cong-nghe-thpt-11-lop-11-4'; $D = 'kho';
        $this->quiz($L, 'Hình cắt trên bản vẽ lắp có tác dụng gì?', ['Thấy rõ cấu tạo bên trong của sản phẩm', 'Làm bản vẽ đẹp hơn', 'Giảm số chi tiết', 'Thay thế hình chiếu'], 0, 'Hình cắt giúp thấy cấu tạo bên trong và cách lắp các chi tiết.', $D);
        $this->quiz($L, 'Số thứ tự của chi tiết trên bản vẽ lắp được ghi ở đâu?', ['Trên đường dẫn kẻ từ chi tiết đó', 'Trong khung tên', 'Ngoài lề bản vẽ', 'Tùy ý'], 0, 'Mỗi chi tiết có số thứ tự trên đường dẫn, tương ứng với bảng kê.', $D);
        $this->quiz($L, 'Mặt đứng trên bản vẽ nhà cho biết điều gì?', ['Hình dáng bên ngoài của ngôi nhà', 'Bố trí các phòng', 'Cấu tạo móng', 'Hệ thống điện'], 0, 'Mặt đứng thể hiện hình dáng, chiều cao bên ngoài ngôi nhà.', $D);
        $this->quiz($L, 'Mặt cắt trên bản vẽ nhà thể hiện điều gì?', ['Cấu tạo bên trong như tường, sàn, mái', 'Màu sơn tường', 'Giá xây dựng', 'Tên chủ nhà'], 0, 'Mặt cắt tưởng tượng cắt dọc nhà để thấy cấu tạo bên trong.', $D);
        $this->quiz($L, 'Tỉ lệ thường dùng để vẽ mặt bằng ngôi nhà là bao nhiêu?', ['1:50 hoặc 1:100', '2:1', '1:1', '10:1'], 0, 'Nhà lớn nên vẽ thu nhỏ 1:50 hoặc 1:100 cho vừa khổ giấy.', $D);
        $this->matching($L, 'Nối mỗi hình thức với nội dung trên bản vẽ nhà.', [['Mặt bằng', 'Bố trí các phòng'], ['Mặt đứng', 'Hình dáng bên ngoài'], ['Mặt cắt', 'Cấu tạo bên trong'], ['Phối cảnh', 'Hình ảnh 3D']], 'Bản vẽ nhà gồm mặt bằng, mặt đứng, mặt cắt và phối cảnh.', $D);
        $this->matching($L, 'Nối mỗi kí hiệu với ý nghĩa trên bản vẽ nhà.', [['Cửa đi', 'Ô trống trên tường'], ['Cửa sổ', 'Ô nhỏ trên tường'], ['Cầu thang', 'Đường dích dắc'], ['Hướng Bắc', 'Mũi tên chỉ hướng']], 'Các kí hiệu giúp đọc nhanh bản vẽ nhà.', $D);
        $this->matching($L, 'Nối mỗi cột trong bảng kê với nội dung của nó.', [['Số thứ tự', 'Vị trí chi tiết'], ['Tên gọi', 'Tên chi tiết'], ['Số lượng', 'Số cái cần dùng'], ['Vật liệu', 'Chất liệu chế tạo']], 'Bảng kê liệt kê đầy đủ thông tin từng chi tiết của sản phẩm.', $D);
        $this->matching($L, 'Nối mỗi bước với trình tự đọc bản vẽ lắp.', [['Khung tên', 'Tên sản phẩm'], ['Bảng kê', 'Các chi tiết'], ['Hình biểu diễn', 'Cách lắp ráp'], ['Kích thước', 'Lắp ghép']], 'Đọc bản vẽ lắp theo trình tự để hiểu cách ráp sản phẩm.', $D);
        $this->matching($L, 'Nối mỗi loại bản vẽ với mục đích sử dụng.', [['Bản vẽ chi tiết', 'Chế tạo chi tiết'], ['Bản vẽ lắp', 'Lắp ráp sản phẩm'], ['Bản vẽ nhà', 'Xây dựng'], ['Sơ đồ nguyên lý', 'Hiểu nguyên lý']], 'Mỗi loại bản vẽ phục vụ một mục đích khác nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi nội dung vào nhóm BẢN VẼ LẮP hoặc BẢN VẼ NHÀ.', [['Bảng kê', 'BẢN VẼ LẮP'], ['Số thứ tự chi tiết', 'BẢN VẼ LẮP'], ['Mặt bằng', 'BẢN VẼ NHÀ'], ['Mặt đứng', 'BẢN VẼ NHÀ']], 'Bảng kê, số thứ tự thuộc bản vẽ lắp; mặt bằng, mặt đứng thuộc bản vẽ nhà.', $D);
        $this->sortQ($L, 'Kéo mỗi hình thức vào nhóm THẤY BÊN NGOÀI hoặc BÊN TRONG.', [['Mặt đứng', 'THẤY BÊN NGOÀI'], ['Phối cảnh', 'THẤY BÊN NGOÀI'], ['Mặt cắt', 'THẤY BÊN TRONG'], ['Mặt bằng', 'THẤY BÊN TRONG']], 'Mặt đứng, phối cảnh thấy ngoài; mặt cắt, mặt bằng thấy trong.', $D);
        $this->sortQ($L, 'Kéo mỗi kí hiệu vào nhóm CỬA hoặc KHÁC.', [['Cửa đi', 'CỬA'], ['Cửa sổ', 'CỬA'], ['Cầu thang', 'KHÁC'], ['Bồn rửa', 'KHÁC']], 'Cửa đi, cửa sổ là kí hiệu cửa; cầu thang, bồn rửa thì khác.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['Bảng kê liệt kê các chi tiết', 'ĐÚNG'], ['Bản vẽ lắp có đánh số thứ tự', 'ĐÚNG'], ['Mặt bằng nhìn từ dưới lên', 'SAI'], ['Mặt đứng thể hiện nội thất', 'SAI']], 'Mặt bằng nhìn từ trên xuống; nội thất thể hiện trên mặt bằng.', $D);
        $this->sortQ($L, 'Kéo mỗi tỉ lệ vào nhóm BẢN VẼ CHI TIẾT hoặc BẢN VẼ NHÀ.', [['2:1', 'BẢN VẼ CHI TIẾT'], ['1:1', 'BẢN VẼ CHI TIẾT'], ['1:50', 'BẢN VẼ NHÀ'], ['1:100', 'BẢN VẼ NHÀ']], 'Chi tiết nhỏ vẽ phóng to hoặc nguyên hình; nhà vẽ thu nhỏ nhiều.', $D);
        $this->fill($L, 'Số thứ tự của chi tiết được ghi trên đường ___ kẻ từ chi tiết đó.', [[0, 'dẫn']], 'Đường dẫn nối chi tiết với số thứ tự tương ứng trong bảng kê.', $D);
        $this->fill($L, 'Mặt ___ là hình cắt tưởng tượng theo chiều đứng của ngôi nhà.', [[0, 'cắt']], 'Mặt cắt cho thấy tường, sàn, mái và chiều cao các tầng.', $D);
        $this->fill($L, 'Tỉ lệ ___ thường được dùng để vẽ mặt bằng ngôi nhà.', [[0, '1:50']], 'Tỉ lệ 1:50 hoặc 1:100 vừa khổ giấy khi vẽ nhà.', $D);
        $this->fill($L, '___ tên các phòng được ghi ngay bên trong mặt bằng.', [[0, 'Tên']], 'Ghi tên phòng giúp đọc nhanh công năng từng không gian.', $D);
        $this->fill($L, 'Bản vẽ nhà giúp người thợ ___ dựng đúng theo thiết kế.', [[0, 'xây']], 'Thợ xây căn cứ bản vẽ để thi công đúng thiết kế.', $D);
    }

    // ===== 27. cong-nghe-thpt-12-lop-12-1 (Lop 12, trung_binh) =====
    private function seedCongNgheThpt12Lop121(): void
    {
        $L = 'cong-nghe-thpt-12-lop-12-1'; $D = 'trung_binh';
        $this->quiz($L, 'Cuộc cách mạng công nghiệp lần thứ hai gắn với thành tựu nào?', ['Điện năng và dây chuyền sản xuất hàng loạt', 'Máy hơi nước', 'Máy tính điện tử', 'Trí tuệ nhân tạo'], 0, 'CMCN lần 2 cuối thế kỷ 19 gắn với điện năng và sản xuất hàng loạt.', $D);
        $this->quiz($L, 'Cuộc cách mạng công nghiệp lần thứ ba gắn với thành tựu nào?', ['Máy tính và tự động hóa', 'Máy hơi nước', 'Điện thoại di động', 'Internet vạn vật'], 0, 'CMCN lần 3 giữa thế kỷ 20 gắn với máy tính, tự động hóa.', $D);
        $this->quiz($L, 'Big Data là gì?', ['Dữ liệu lớn', 'Máy tính lớn', 'Mạng internet lớn', 'Nhà máy lớn'], 0, 'Big Data là dữ liệu lớn, được phân tích để hỗ trợ quyết định.', $D);
        $this->quiz($L, 'Công nghệ in 3D được ứng dụng trong sản xuất như thế nào?', ['Tạo mẫu nhanh và chi tiết phức tạp', 'In tiền giấy', 'In sách báo', 'Vẽ tranh'], 0, 'In 3D tạo mẫu nhanh, chế tạo chi tiết phức tạp khó gia công thường.', $D);
        $this->quiz($L, 'Điện toán đám mây giúp doanh nghiệp điều gì?', ['Lưu trữ và truy cập dữ liệu mọi nơi', 'Giảm nhân viên', 'Tăng giá bán', 'Không cần máy tính'], 0, 'Đám mây lưu trữ dữ liệu, truy cập mọi nơi, tiết kiệm hạ tầng.', $D);
        $this->matching($L, 'Nối mỗi cuộc cách mạng công nghiệp với thời gian diễn ra.', [['Lần 1', 'Cuối thế kỷ 18'], ['Lần 2', 'Cuối thế kỷ 19'], ['Lần 3', 'Giữa thế kỷ 20'], ['Lần 4', 'Đầu thế kỷ 21']], 'Bốn cuộc CMCN gắn với máy hơi nước, điện, máy tính và số hóa.', $D);
        $this->matching($L, 'Nối mỗi công nghệ 4.0 với ứng dụng của nó.', [['AI', 'Nhận diện hình ảnh'], ['IoT', 'Kết nối thiết bị'], ['Big Data', 'Phân tích dữ liệu'], ['In 3D', 'Tạo mẫu nhanh']], 'Các công nghệ 4.0 ứng dụng rộng rãi trong sản xuất và đời sống.', $D);
        $this->matching($L, 'Nối mỗi lợi ích với ví dụ của công nghệ trong sản xuất.', [['Năng suất cao', 'Dây chuyền tự động'], ['Chất lượng ổn định', 'Kiểm tra tự động'], ['An toàn', 'Robot làm việc nguy hiểm'], ['Giảm chi phí', 'Ít nhân công']], 'Công nghệ giúp sản xuất năng suất, chất lượng và an toàn hơn.', $D);
        $this->matching($L, 'Nối mỗi ngành với ứng dụng công nghệ của nó.', [['Y tế', 'Robot phẫu thuật'], ['Nông nghiệp', 'Drone phun thuốc'], ['Giáo dục', 'Học trực tuyến'], ['Giao thông', 'Xe tự lái']], 'Công nghệ len lỏi vào mọi ngành nghề trong đời sống.', $D);
        $this->matching($L, 'Nối mỗi thách thức với giải pháp tương ứng.', [['Nguy cơ thất nghiệp', 'Đào tạo lại lao động'], ['An ninh mạng', 'Bảo mật thông tin'], ['Khoảng cách số', 'Phổ cập công nghệ'], ['Phụ thuộc công nghệ', 'Có phương án dự phòng']], 'Công nghệ mang cơ hội nhưng cũng đặt ra thách thức cần giải quyết.', $D);
        $this->sortQ($L, 'Kéo mỗi thành tựu vào nhóm CMCN LẦN 1-2 hoặc LẦN 3-4.', [['Máy hơi nước', 'CMCN LẦN 1-2'], ['Điện năng', 'CMCN LẦN 1-2'], ['Máy tính', 'CMCN LẦN 3-4'], ['Trí tuệ nhân tạo', 'CMCN LẦN 3-4']], 'Lần 1-2: hơi nước, điện; lần 3-4: máy tính, số hóa, AI.', $D);
        $this->sortQ($L, 'Kéo mỗi công nghệ vào nhóm PHẦN CỨNG hoặc PHẦN MỀM.', [['Robot', 'PHẦN CỨNG'], ['Cảm biến', 'PHẦN CỨNG'], ['Trí tuệ nhân tạo', 'PHẦN MỀM'], ['Phần mềm quản lý', 'PHẦN MỀM']], 'Robot, cảm biến là phần cứng; AI, phần mềm là phần mềm.', $D);
        $this->sortQ($L, 'Kéo mỗi ứng dụng vào nhóm CÔNG NGHỆ TRONG SẢN XUẤT hoặc TRONG ĐỜI SỐNG.', [['Dây chuyền tự động', 'CÔNG NGHỆ TRONG SẢN XUẤT'], ['Robot hàn', 'CÔNG NGHỆ TRONG SẢN XUẤT'], ['Nhà thông minh', 'CÔNG NGHỆ TRONG ĐỜI SỐNG'], ['Mua sắm online', 'CÔNG NGHỆ TRONG ĐỜI SỐNG']], 'Công nghệ vừa phục vụ sản xuất vừa phục vụ đời sống.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['IoT là kết nối vạn vật', 'ĐÚNG'], ['CMCN 4.0 dựa trên số hóa', 'ĐÚNG'], ['AI sẽ thay thế hết con người', 'SAI'], ['In 3D chỉ để làm đồ chơi', 'SAI']], 'AI hỗ trợ con người chứ không thay thế hết; in 3D ứng dụng rộng.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm CƠ HỘI hoặc THÁCH THỨC của công nghệ.', [['Việc làm mới xuất hiện', 'CƠ HỘI'], ['Năng suất lao động cao', 'CƠ HỘI'], ['Nguy cơ mất an ninh mạng', 'THÁCH THỨC'], ['Một số nghề biến mất', 'THÁCH THỨC']], 'Công nghệ mở cơ hội việc làm mới nhưng cũng tạo thách thức.', $D);
        $this->fill($L, 'Cách mạng công nghiệp lần thứ hai gắn với năng lượng ___ và dây chuyền sản xuất.', [[0, 'điện']], 'Điện năng giúp sản xuất hàng loạt với dây chuyền vào cuối thế kỷ 19.', $D);
        $this->fill($L, 'Dữ liệu lớn trong công nghệ 4.0 được gọi là Big ___.', [[0, 'Data']], 'Big Data là nguồn tài nguyên quan trọng của CMCN 4.0.', $D);
        $this->fill($L, 'Công nghệ in ___ D giúp tạo mẫu nhanh chi tiết phức tạp.', [[0, '3']], 'In 3D xếp lớp vật liệu tạo chi tiết theo thiết kế số.', $D);
        $this->fill($L, 'Xe tự ___ là ứng dụng của trí tuệ nhân tạo trong giao thông.', [[0, 'lái']], 'Xe tự lái dùng AI, cảm biến để di chuyển không cần người lái.', $D);
        $this->fill($L, 'Công nghệ giúp con người làm việc năng ___ và an toàn hơn.', [[0, 'suất']], 'Năng suất lao động tăng là lợi ích lớn nhất của công nghệ.', $D);
    }

    // ===== 28. cong-nghe-thpt-12-lop-12-2 (Lop 12, kho) =====
    private function seedCongNgheThpt12Lop122(): void
    {
        $L = 'cong-nghe-thpt-12-lop-12-2'; $D = 'kho';
        $this->quiz($L, 'PLC là thiết bị gì trong công nghiệp?', ['Bộ điều khiển logic khả trình', 'Máy phát điện', 'Động cơ điện', 'Máy biến áp'], 0, 'PLC là bộ điều khiển lập trình được, dùng phổ biến trong tự động hóa.', $D);
        $this->quiz($L, 'Cảm biến trong hệ thống tự động có nhiệm vụ gì?', ['Thu thập thông tin từ môi trường', 'Phát ra lệnh điều khiển', 'Thực hiện hành động', 'Hiển thị kết quả'], 0, 'Cảm biến thu thập nhiệt độ, áp suất, vị trí để bộ điều khiển xử lý.', $D);
        $this->quiz($L, 'Cơ cấu chấp hành trong hệ thống tự động là gì?', ['Biến tín hiệu điều khiển thành hành động', 'Thu thập dữ liệu', 'Lưu trữ chương trình', 'Hiển thị thông số'], 0, 'Chấp hành như động cơ, xi lanh biến tín hiệu thành chuyển động.', $D);
        $this->quiz($L, 'Robot cộng tác (cobot) khác robot công nghiệp truyền thống ở điểm nào?', ['Làm việc an toàn bên cạnh con người', 'To lớn hơn nhiều', 'Không cần điện', 'Chỉ làm một việc'], 0, 'Cobot nhỏ gọn, an toàn, làm việc cùng con người không cần rào chắn.', $D);
        $this->quiz($L, 'Hệ thống SCADA dùng để làm gì?', ['Giám sát và điều khiển từ xa qua máy tính', 'Chơi game', 'Soạn thảo văn bản', 'Nghe nhạc'], 0, 'SCADA giám sát, thu thập dữ liệu và điều khiển hệ thống từ xa.', $D);
        $this->matching($L, 'Nối mỗi bộ phận với ví dụ cụ thể.', [['Cảm biến', 'Nhiệt kế điện tử'], ['Bộ điều khiển', 'PLC'], ['Cơ cấu chấp hành', 'Động cơ'], ['Hiển thị', 'Màn hình']], 'Hệ thống tự động gồm cảm biến, điều khiển, chấp hành và hiển thị.', $D);
        $this->matching($L, 'Nối mỗi loại robot với công việc của nó.', [['Robot hàn', 'Hàn khung ô tô'], ['Robot gắp', 'Đóng gói sản phẩm'], ['Robot sơn', 'Sơn vỏ xe'], ['AGV', 'Vận chuyển hàng']], 'Robot chuyên dụng cho từng công đoạn sản xuất.', $D);
        $this->matching($L, 'Nối mỗi mức độ tự động với ví dụ.', [['Bán tự động', 'Máy có người vận hành'], ['Tự động', 'Dây chuyền sản xuất'], ['Thông minh', 'AI điều khiển'], ['Thủ công', 'Làm bằng tay']], 'Từ thủ công đến thông minh là các mức tự động hóa tăng dần.', $D);
        $this->matching($L, 'Nối mỗi ưu điểm với hạn chế tương ứng của tự động hóa.', [['Chính xác cao', 'Chi phí đầu tư cao'], ['Làm việc liên tục', 'Một số việc làm mất đi'], ['Thay người nơi nguy hiểm', 'Bảo trì phức tạp'], ['Tốc độ nhanh', 'Cần chuyên gia vận hành']], 'Tự động hóa có ưu điểm lớn nhưng cũng có hạn chế cần cân nhắc.', $D);
        $this->matching($L, 'Nối mỗi tín hiệu trong hệ thống với loại của nó.', [['Nhiệt độ', 'Tín hiệu tương tự'], ['Áp suất', 'Tín hiệu tương tự'], ['Bật/tắt', 'Tín hiệu số'], ['Đếm sản phẩm', 'Tín hiệu số']], 'Tín hiệu tương tự biến thiên liên tục; tín hiệu số chỉ có 0/1.', $D);
        $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm ĐẦU VÀO, XỬ LÝ hoặc ĐẦU RA.', [['Cảm biến', 'ĐẦU VÀO'], ['Nút nhấn', 'ĐẦU VÀO'], ['PLC', 'XỬ LÝ'], ['Động cơ', 'ĐẦU RA']], 'Đầu vào thu thập, xử lý ra quyết định, đầu ra thực hiện.', $D);
        $this->sortQ($L, 'Kéo mỗi công việc vào nhóm NÊN hoặc KHÔNG NÊN tự động hóa.', [['Lặp đi lặp lại', 'NÊN'], ['Nguy hiểm, độc hại', 'NÊN'], ['Sáng tạo nghệ thuật', 'KHÔNG NÊN'], ['Chăm sóc tinh thần', 'KHÔNG NÊN']], 'Việc lặp lại, nguy hiểm nên tự động; việc sáng tạo, cảm xúc thì không.', $D);
        $this->sortQ($L, 'Kéo mỗi hệ thống vào nhóm TỰ ĐỘNG HÓA ĐƠN GIẢN hoặc PHỨC TẠP.', [['Đèn tự động bật tắt', 'TỰ ĐỘNG HÓA ĐƠN GIẢN'], ['Máy giặt', 'TỰ ĐỘNG HÓA ĐƠN GIẢN'], ['Dây chuyền ô tô', 'TỰ ĐỘNG HÓA PHỨC TẠP'], ['Nhà máy thông minh', 'TỰ ĐỘNG HÓA PHỨC TẠP']], 'Từ thiết bị đơn giản đến nhà máy thông minh là các mức phức tạp.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['PLC là bộ điều khiển lập trình', 'ĐÚNG'], ['Cảm biến thu thập thông tin', 'ĐÚNG'], ['Robot thay thế hết con người', 'SAI'], ['Tự động hóa chỉ tốn điện vô ích', 'SAI']], 'Robot hỗ trợ chứ không thay hết người; tự động hóa nâng năng suất.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm CẢM BIẾN hoặc CHẤP HÀNH.', [['Cảm biến quang', 'CẢM BIẾN'], ['Nhiệt kế', 'CẢM BIẾN'], ['Xi lanh khí nén', 'CHẤP HÀNH'], ['Động cơ bước', 'CHẤP HÀNH']], 'Cảm biến thu thập; chấp hành thực hiện hành động.', $D);
        $this->fill($L, 'PLC là viết tắt của bộ điều khiển logic khả ___.', [[0, 'trình']], 'PLC lập trình được nên linh hoạt với nhiều quy trình sản xuất.', $D);
        $this->fill($L, 'Cơ cấu ___ hành biến tín hiệu điều khiển thành chuyển động.', [[0, 'chấp']], 'Chấp hành như động cơ, xi lanh thực hiện lệnh từ PLC.', $D);
        $this->fill($L, 'Robot ___ tác làm việc an toàn bên cạnh con người.', [[0, 'cộng']], 'Robot cộng tác (cobot) hỗ trợ người trong sản xuất linh hoạt.', $D);
        $this->fill($L, 'Hệ thống ___ cho phép giám sát và điều khiển từ xa qua máy tính.', [[0, 'SCADA']], 'SCADA dùng trong điện, nước, giao thông để điều khiển từ xa.', $D);
        $this->fill($L, 'Tự động hóa giúp giảm lao động chân tay đơn ___.', [[0, 'điệu']], 'Máy móc thay người làm việc đơn điệu, nặng nhọc, nguy hiểm.', $D);
    }

    // ===== 29. cong-nghe-thpt-12-lop-12-3 (Lop 12, trung_binh) =====
    private function seedCongNgheThpt12Lop123(): void
    {
        $L = 'cong-nghe-thpt-12-lop-12-3'; $D = 'trung_binh';
        $this->quiz($L, 'Ô nhiễm đất chủ yếu do nguyên nhân nào?', ['Rác thải, hóa chất và thuốc bảo vệ thực vật', 'Mưa nhiều', 'Nắng gắt', 'Gió to'], 0, 'Hóa chất, thuốc trừ sâu và rác thải ngấm vào đất gây ô nhiễm đất.', $D);
        $this->quiz($L, 'Ô nhiễm tiếng ồn gây hại gì cho con người?', ['Ảnh hưởng thính giác và hệ thần kinh', 'Làm da đẹp hơn', 'Giúp ngủ ngon', 'Tăng trí nhớ'], 0, 'Tiếng ồn lớn kéo dài gây điếc, căng thẳng thần kinh.', $D);
        $this->quiz($L, 'Mưa axit hình thành từ những khí nào?', ['SO2 và NOx', 'O2 và N2', 'Hơi nước', 'CO2 tinh khiết'], 0, 'SO2, NOx từ khí thải kết hợp hơi nước tạo mưa axit.', $D);
        $this->quiz($L, 'Hiện tượng phú dưỡng nguồn nước do đâu?', ['Dư thừa đạm, lân từ phân bón', 'Thiếu nước', 'Nước quá sạch', 'Cá quá nhiều'], 0, 'Đạm, lân dư làm tảo nở hoa, hết ôxy, cá chết.', $D);
        $this->quiz($L, 'Rác thải nhựa gây hại cho đại dương như thế nào?', ['Sinh vật ăn nhầm và vi nhựa độc hại', 'Làm nước trong hơn', 'Giúp cá lớn nhanh', 'Không gây hại gì'], 0, 'Nhựa vỡ thành vi nhựa, sinh vật biển ăn nhầm gây chết.', $D);
        $this->matching($L, 'Nối mỗi loại ô nhiễm với nguồn gây chính.', [['Ô nhiễm không khí', 'Khí thải'], ['Ô nhiễm nước', 'Nước thải'], ['Ô nhiễm đất', 'Rác và hóa chất'], ['Ô nhiễm tiếng ồn', 'Giao thông']], 'Mỗi loại ô nhiễm có nguồn gây đặc trưng cần kiểm soát.', $D);
        $this->matching($L, 'Nối mỗi chất gây ô nhiễm với tác hại của nó.', [['CO', 'Gây ngộ độc'], ['PM2.5', 'Bệnh đường hô hấp'], ['Thủy ngân', 'Hại thần kinh'], ['Thuốc trừ sâu', 'Tồn dư độc hại']], 'Các chất ô nhiễm gây hại sức khỏe theo cách khác nhau.', $D);
        $this->matching($L, 'Nối mỗi hiện tượng môi trường với nguyên nhân của nó.', [['Mưa axit', 'Khí SO2'], ['Phú dưỡng', 'Đạm, lân dư'], ['Nóng lên toàn cầu', 'Khí CO2'], ['Thủng tầng ôzôn', 'Khí CFC']], 'Hiểu nguyên nhân giúp tìm giải pháp khắc phục đúng.', $D);
        $this->matching($L, 'Nối mỗi hoạt động với loại ô nhiễm nó gây ra.', [['Đốt rơm rạ', 'Không khí'], ['Xả thải chưa xử lý', 'Nước'], ['Chôn lấp bừa bãi', 'Đất'], ['Mở loa quá to', 'Tiếng ồn']], 'Hoạt động con người là nguồn ô nhiễm chính.', $D);
        $this->matching($L, 'Nối mỗi biện pháp với tác dụng bảo vệ môi trường.', [['Xử lý khí thải', 'Không khí sạch'], ['Xử lý nước thải', 'Nước sạch'], ['Phân loại rác', 'Giảm chôn lấp'], ['Trồng cây xanh', 'Lọc bụi']], 'Mỗi biện pháp góp phần giảm một loại ô nhiễm.', $D);
        $this->sortQ($L, 'Kéo mỗi khí vào nhóm GÂY Ô NHIỄM hoặc KHÔNG GÂY Ô NHIỄM.', [['CO', 'GÂY Ô NHIỄM'], ['SO2', 'GÂY Ô NHIỄM'], ['O2', 'KHÔNG GÂY Ô NHIỄM'], ['N2', 'KHÔNG GÂY Ô NHIỄM']], 'CO, SO2 độc hại; O2, N2 là khí tự nhiên của không khí.', $D);
        $this->sortQ($L, 'Kéo mỗi nguồn ô nhiễm vào nhóm TỰ NHIÊN hoặc NHÂN TẠO.', [['Núi lửa phun', 'TỰ NHIÊN'], ['Bão cát', 'TỰ NHIÊN'], ['Nhà máy', 'NHÂN TẠO'], ['Xe cộ', 'NHÂN TẠO']], 'Ô nhiễm có nguồn tự nhiên và nguồn do con người.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm GÂY Ô NHIỄM hoặc BẢO VỆ MÔI TRƯỜNG.', [['Xả rác bừa bãi', 'GÂY Ô NHIỄM'], ['Đốt túi ni lông', 'GÂY Ô NHIỄM'], ['Trồng cây xanh', 'BẢO VỆ MÔI TRƯỜNG'], ['Đi xe đạp', 'BẢO VỆ MÔI TRƯỜNG']], 'Hành động nhỏ mỗi ngày quyết định môi trường sạch hay bẩn.', $D);
        $this->sortQ($L, 'Kéo mỗi chất thải vào nhóm PHÂN HỦY NHANH hoặc PHÂN HỦY LÂU.', [['Rau thừa', 'PHÂN HỦY NHANH'], ['Giấy', 'PHÂN HỦY NHANH'], ['Nhựa', 'PHÂN HỦY LÂU'], ['Thủy tinh', 'PHÂN HỦY LÂU']], 'Hữu cơ phân hủy nhanh; nhựa, thủy tinh tồn tại hàng trăm năm.', $D);
        $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm NGUYÊN NHÂN hoặc HẬU QUẢ ô nhiễm.', [['Xả thải công nghiệp', 'NGUYÊN NHÂN'], ['Đốt rừng bừa bãi', 'NGUYÊN NHÂN'], ['Cá chết hàng loạt', 'HẬU QUẢ'], ['Băng tan', 'HẬU QUẢ']], 'Nguyên nhân do con người gây ra hậu quả cho môi trường.', $D);
        $this->fill($L, 'Khí ___ gây ngộ độc nguy hiểm khi hít phải trong không gian kín.', [[0, 'CO']], 'CO không màu không mùi, ngộ độc có thể tử vong.', $D);
        $this->fill($L, 'Mưa ___ làm chua đất, hại cây trồng và ăn mòn công trình.', [[0, 'axit']], 'Mưa axit từ khí thải công nghiệp gây hại lớn.', $D);
        $this->fill($L, 'Hiện tượng nước giàu dinh dưỡng gây tảo nở hoa gọi là phú ___.', [[0, 'dưỡng']], 'Phú dưỡng làm hết ôxy nước, cá chết hàng loạt.', $D);
        $this->fill($L, 'Ô nhiễm ___ ồn ảnh hưởng thính giác và hệ thần kinh.', [[0, 'tiếng']], 'Tiếng ồn vượt ngưỡng kéo dài gây hại sức khỏe.', $D);
        $this->fill($L, 'Vi ___ từ rác nhựa đang đe dọa sinh vật biển.', [[0, 'nhựa']], 'Vi nhựa xâm nhập chuỗi thức ăn, đe dọa cả con người.', $D);
    }

    // ===== 30. cong-nghe-thpt-12-lop-12-4 (Lop 12, kho) =====
    private function seedCongNgheThpt12Lop124(): void
    {
        $L = 'cong-nghe-thpt-12-lop-12-4'; $D = 'kho';
        $this->quiz($L, 'Ba trụ cột của phát triển bền vững là gì?', ['Kinh tế – Xã hội – Môi trường', 'Tiền – Quyền – Danh', 'Sản xuất – Buôn bán – Tiêu dùng', 'Học – Hành – Thi'], 0, 'Bền vững là hài hòa kinh tế, xã hội và môi trường.', $D);
        $this->quiz($L, 'Năng lượng mặt trời có nhược điểm gì?', ['Phụ thuộc thời tiết, chi phí đầu tư cao', 'Gây ô nhiễm nặng', 'Không bao giờ hết', 'Dùng được ban đêm'], 0, 'Điện mặt trời sạch nhưng phụ thuộc nắng và đầu tư ban đầu cao.', $D);
        $this->quiz($L, 'Kinh tế tuần hoàn là mô hình gì?', ['Tái sử dụng, tái chế để giảm rác thải', 'Kinh tế đi vòng tròn', 'Chỉ mua không bán', 'Sản xuất thật nhiều'], 0, 'Kinh tế tuần hoàn giữ tài nguyên trong vòng đời dài, giảm rác.', $D);
        $this->quiz($L, 'Chứng chỉ xanh cho công trình có ý nghĩa gì?', ['Tiết kiệm năng lượng, thân thiện môi trường', 'Sơn màu xanh', 'Trồng nhiều cây', 'Xây nhanh'], 0, 'Công trình xanh tiết kiệm năng lượng, nước và ít phát thải.', $D);
        $this->quiz($L, 'Vì sao bảo vệ rừng giúp phát triển bền vững?', ['Giữ đa dạng sinh học, chống biến đổi khí hậu', 'Để lấy gỗ', 'Để làm du lịch', 'Để nuôi thú'], 0, 'Rừng là lá phổi xanh, giữ đa dạng sinh học và điều hòa khí hậu.', $D);
        $this->matching($L, 'Nối mỗi trụ cột với nội dung của phát triển bền vững.', [['Kinh tế', 'Tăng trưởng ổn định'], ['Xã hội', 'Công bằng, tiến bộ'], ['Môi trường', 'Bảo vệ lâu dài'], ['Văn hóa', 'Giữ bản sắc']], 'Bốn nội dung hài hòa tạo nên phát triển bền vững.', $D);
        $this->matching($L, 'Nối mỗi chữ R với hành động cụ thể.', [['Reduce', 'Giảm thiểu tiêu dùng'], ['Reuse', 'Tái sử dụng'], ['Recycle', 'Tái chế'], ['Refuse', 'Từ chối đồ dùng một lần']], 'Nguyên tắc 4R giúp giảm rác thải hiệu quả.', $D);
        $this->matching($L, 'Nối mỗi nguồn năng lượng với đặc điểm của nó.', [['Mặt trời', 'Vô tận, sạch'], ['Gió', 'Sạch, tái tạo'], ['Than đá', 'Gây ô nhiễm'], ['Hạt nhân', 'Mạnh nhưng rủi ro']], 'Năng lượng tái tạo sạch nhưng phụ thuộc tự nhiên.', $D);
        $this->matching($L, 'Nối mỗi hành động với trụ cột phát triển bền vững.', [['Tiết kiệm điện', 'Môi trường'], ['Giúp đỡ cộng đồng', 'Xã hội'], ['Khởi nghiệp xanh', 'Kinh tế'], ['Giữ gìn văn hóa', 'Văn hóa']], 'Mỗi hành động góp vào một trụ cột bền vững.', $D);
        $this->matching($L, 'Nối mỗi mục tiêu với ví dụ thực hiện.', [['Nước sạch', 'Lọc nước sinh hoạt'], ['Năng lượng sạch', 'Điện mặt trời'], ['Tiêu dùng bền vững', 'Dùng túi vải'], ['Ứng phó khí hậu', 'Trồng rừng']], 'Mục tiêu bền vững được thực hiện bằng hành động cụ thể.', $D);
        $this->sortQ($L, 'Kéo mỗi nguồn năng lượng vào nhóm TÁI TẠO hoặc KHÔNG TÁI TẠO.', [['Mặt trời', 'TÁI TẠO'], ['Gió', 'TÁI TẠO'], ['Dầu mỏ', 'KHÔNG TÁI TẠO'], ['Khí đốt', 'KHÔNG TÁI TẠO']], 'Mặt trời, gió tái tạo; dầu, khí đốt dùng hết là hết.', $D);
        $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BỀN VỮNG hoặc KHÔNG BỀN VỮNG.', [['Dùng túi vải', 'BỀN VỮNG'], ['Tiết kiệm nước', 'BỀN VỮNG'], ['Xả rác bừa bãi', 'KHÔNG BỀN VỮNG'], ['Chặt rừng bừa bãi', 'KHÔNG BỀN VỮNG']], 'Sống xanh là bền vững; hủy hoại môi trường thì không.', $D);
        $this->sortQ($L, 'Kéo mỗi hành động vào nhóm REDUCE, REUSE hoặc RECYCLE.', [['Mang bình nước riêng', 'REDUCE'], ['In giấy hai mặt', 'REDUCE'], ['Dùng lại chai lọ', 'REUSE'], ['Bán ve chai', 'RECYCLE']], 'Reduce giảm, reuse dùng lại, recycle tái chế.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI.', [['Bền vững gồm 3 trụ cột', 'ĐÚNG'], ['Năng lượng tái tạo không bao giờ hết', 'ĐÚNG'], ['Tăng trưởng bằng mọi giá', 'SAI'], ['Học sinh không thể góp sức', 'SAI']], 'Ai cũng có thể góp sức bảo vệ môi trường từ việc nhỏ.', $D);
        $this->sortQ($L, 'Kéo mỗi giải pháp vào nhóm CÁ NHÂN hoặc CỘNG ĐỒNG.', [['Tắt đèn khi ra ngoài', 'CÁ NHÂN'], ['Phân loại rác tại nhà', 'CÁ NHÂN'], ['Trồng rừng', 'CỘNG ĐỒNG'], ['Xây nhà máy xử lý rác', 'CỘNG ĐỒNG']], 'Bảo vệ môi trường cần cả cá nhân và cộng đồng chung tay.', $D);
        $this->fill($L, 'Phát triển bền vững gồm ba trụ cột: kinh tế, xã hội và ___ trường.', [[0, 'môi']], 'Ba trụ cột phải hài hòa, không đánh đổi môi trường lấy tăng trưởng.', $D);
        $this->fill($L, 'Kinh tế ___ hoàn hướng tới tái sử dụng và giảm rác thải.', [[0, 'tuần']], 'Kinh tế tuần hoàn khác kinh tế tuyến tính khai thác-bỏ đi.', $D);
        $this->fill($L, 'Năng lượng mặt trời là nguồn năng lượng sạch và ___ tạo.', [[0, 'tái']], 'Năng lượng tái tạo không cạn kiệt và ít phát thải.', $D);
        $this->fill($L, 'Mỗi học sinh có thể góp sức bằng việc nhỏ như tắt điện khi không ___.', [[0, 'dùng']], 'Tiết kiệm điện, nước từ việc nhỏ mỗi ngày rất có ý nghĩa.', $D);
        $this->fill($L, 'Bảo vệ ___ là bảo vệ lá phổi xanh của Trái Đất.', [[0, 'rừng']], 'Rừng hấp thụ CO2, giữ đa dạng sinh học và điều hòa khí hậu.', $D);
    }
}
