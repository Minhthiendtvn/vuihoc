<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdditionalQuestionsGroup1Seeder extends Seeder
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
        $this->seedTh01(); $this->seedTh02(); $this->seedTh03(); $this->seedTh04(); $this->seedTh05(); $this->seedTh06();
        $this->seedTh07(); $this->seedTh08(); $this->seedTh09(); $this->seedTh10(); $this->seedTh11(); $this->seedTh12();
        $this->seedTh13(); $this->seedTh14(); $this->seedTh15(); $this->seedTh16(); $this->seedTh17(); $this->seedTh18();
        $this->seedTh19(); $this->seedTh20(); $this->seedTh21(); $this->seedTh22(); $this->seedTh23(); $this->seedTh24();
        $this->seedTh25(); $this->seedTh26(); $this->seedTh27(); $this->seedTh28(); $this->seedTh29(); $this->seedTh30();
        $this->seedTo01(); $this->seedTo02(); $this->seedTo03(); $this->seedTo04(); $this->seedTo05(); $this->seedTo06();
        $this->seedTo07(); $this->seedTo08(); $this->seedTo09(); $this->seedTo10(); $this->seedTo11(); $this->seedTo12();
        $this->seedTo13(); $this->seedTo14(); $this->seedTo15(); $this->seedTo16(); $this->seedTo17(); $this->seedTo18();
        $this->seedTo19(); $this->seedTo20(); $this->seedTo21(); $this->seedTo22(); $this->seedTo23(); $this->seedTo24();
        $this->seedTo25(); $this->seedTo26(); $this->seedTo27(); $this->seedTo28(); $this->seedTo29(); $this->seedTo30();
        $this->seedTo31(); $this->seedTo32(); $this->seedTo33(); $this->seedTo34(); $this->seedTo35(); $this->seedTo36();
    }

    // 1. th-cac-bo-phan-may-tinh — Các bộ phận của máy tính (lớp 6, dễ)
    private function seedTh01(): void {
        $L = 'th-cac-bo-phan-may-tinh'; $D = 'de';
        $this->quiz($L, 'Loa máy tính thuộc loại thiết bị nào?', ['Thiết bị vào', 'Thiết bị ra', 'Thiết bị lưu trữ', 'Bộ xử lý trung tâm'], 1, 'Loa phát âm thanh từ máy tính ra ngoài nên là thiết bị ra.', $D);
        $this->quiz($L, 'Ổ cứng (HDD/SSD) có chức năng gì?', ['Lưu trữ dữ liệu lâu dài', 'Xử lý mọi phép tính', 'Hiển thị hình ảnh', 'Kết nối mạng Internet'], 0, 'Ổ cứng dùng để lưu trữ dữ liệu lâu dài, tắt máy dữ liệu vẫn còn.', $D);
        $this->quiz($L, 'Máy in thuộc loại thiết bị nào?', ['Thiết bị vào', 'Thiết bị ra', 'Thiết bị lưu trữ tạm thời', 'Bộ nhớ trong'], 1, 'Máy in đưa thông tin từ máy tính ra giấy nên là thiết bị ra.', $D);
        $this->quiz($L, 'Bộ phận nào thực hiện mọi phép tính và điều khiển hoạt động của máy tính?', ['Màn hình', 'Bàn phím', 'CPU', 'Loa'], 2, 'CPU (bộ xử lý trung tâm) thực hiện mọi phép tính và điều khiển toàn bộ máy tính.', $D);
        $this->quiz($L, 'USB (ổ đĩa flash) dùng để làm gì?', ['Tăng tốc độ mạng', 'Lưu trữ và mang dữ liệu đi', 'Phát Wi-Fi', 'Làm mát máy tính'], 1, 'USB là thiết bị lưu trữ di động, giúp chép và mang dữ liệu đi dễ dàng.', $D);
        $this->matching($L, 'Nối mỗi linh kiện với nhiệm vụ chính của nó.', [['CPU', 'Tính toán và điều khiển'], ['RAM', 'Lưu dữ liệu tạm khi máy chạy'], ['Ổ cứng', 'Lưu dữ liệu lâu dài'], ['Màn hình', 'Hiển thị hình ảnh']], 'Mỗi linh kiện có một nhiệm vụ riêng trong máy tính.', $D);
        $this->matching($L, 'Nối mỗi thiết bị với nhóm vào hoặc ra.', [['Bàn phím', 'Thiết bị vào'], ['Chuột', 'Thiết bị vào'], ['Máy in', 'Thiết bị ra'], ['Loa', 'Thiết bị ra']], 'Thiết bị vào đưa dữ liệu vào máy, thiết bị ra đưa kết quả ra ngoài.', $D);
        $this->matching($L, 'Nối từ viết tắt với tên đầy đủ của linh kiện.', [['CPU', 'Central Processing Unit'], ['RAM', 'Random Access Memory'], ['ROM', 'Read Only Memory'], ['USB', 'Universal Serial Bus']], 'Các linh kiện thường được gọi bằng tên viết tắt tiếng Anh.', $D);
        $this->matching($L, 'Nối mỗi nhóm thiết bị với một ví dụ đúng.', [['Thiết bị vào', 'Webcam'], ['Thiết bị ra', 'Máy chiếu'], ['Lưu trữ di động', 'Thẻ nhớ'], ['Bộ xử lý', 'CPU']], 'Xếp đúng thiết bị vào từng nhóm giúp nhớ chức năng của chúng.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với thiết bị phù hợp.', [['Nghe nhạc', 'Loa'], ['Đưa văn bản giấy vào máy', 'Máy quét'], ['Chiếu bài thuyết trình', 'Máy chiếu'], ['Lưu bài tập mang về nhà', 'USB']], 'Chọn thiết bị đúng với nhu cầu giúp học tập hiệu quả hơn.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "CÓ TRONG LAPTOP" hoặc "THƯỜNG GẮN RỜI".', [['Bàn phím tích hợp', 'CÓ TRONG LAPTOP'], ['Touchpad', 'CÓ TRONG LAPTOP'], ['Máy in', 'THƯỜNG GẮN RỜI'], ['Máy chiếu', 'THƯỜNG GẮN RỜI']], 'Laptop gộp sẵn bàn phím và touchpad, còn máy in, máy chiếu thường gắn rời.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "XUẤT HÌNH ẢNH" hoặc "XUẤT ÂM THANH".', [['Màn hình', 'XUẤT HÌNH ẢNH'], ['Máy chiếu', 'XUẤT HÌNH ẢNH'], ['Loa', 'XUẤT ÂM THANH'], ['Tai nghe', 'XUẤT ÂM THANH']], 'Màn hình, máy chiếu xuất hình ảnh; loa, tai nghe xuất âm thanh.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị lưu trữ vào nhóm "GẮN TRONG MÁY" hoặc "CẦM TAY MANG ĐI".', [['Ổ cứng trong', 'GẮN TRONG MÁY'], ['Ổ SSD trong', 'GẮN TRONG MÁY'], ['USB', 'CẦM TAY MANG ĐI'], ['Thẻ nhớ', 'CẦM TAY MANG ĐI']], 'Ổ cứng, SSD gắn trong máy; USB, thẻ nhớ nhỏ gọn mang theo được.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "PHÁT RA ÂM THANH" hoặc "THU ÂM THANH".', [['Loa', 'PHÁT RA ÂM THANH'], ['Tai nghe', 'PHÁT RA ÂM THANH'], ['Micro', 'THU ÂM THANH'], ['Webcam có micro', 'THU ÂM THANH']], 'Loa, tai nghe phát âm thanh ra; micro thu âm thanh vào máy.', $D);
        $this->sortQ($L, 'Kéo mỗi nơi lưu dữ liệu vào nhóm "MẤT KHI TẮT MÁY" hoặc "CÒN KHI TẮT MÁY".', [['RAM', 'MẤT KHI TẮT MÁY'], ['Ổ cứng', 'CÒN KHI TẮT MÁY'], ['USB', 'CÒN KHI TẮT MÁY'], ['Thẻ nhớ', 'CÒN KHI TẮT MÁY']], 'RAM là bộ nhớ tạm nên mất dữ liệu khi tắt máy; ổ cứng, USB giữ dữ liệu lâu dài.', $D);
        $this->fill($L, 'Thiết bị dùng để in văn bản ra giấy là máy ___.', [[0, 'in']], 'Máy in đưa nội dung từ máy tính ra giấy.', $D);
        $this->fill($L, 'Ổ ___ dùng để lưu dữ liệu lâu dài trong máy tính.', [[0, 'cứng']], 'Ổ cứng lưu dữ liệu lâu dài, tắt máy vẫn còn.', $D);
        $this->fill($L, 'CPU là viết tắt của cụm từ ___ Processing Unit.', [[0, 'Central']], 'CPU là viết tắt của Central Processing Unit – đơn vị xử lý trung tâm.', $D);
        $this->fill($L, 'Thiết bị ___ vừa thu hình ảnh vừa thu âm thanh khi học trực tuyến.', [[0, 'webcam']], 'Webcam giúp thu hình và tiếng khi học trực tuyến.', $D);
        $this->fill($L, 'Muốn nghe nhạc mà không làm phiền người khác, em dùng tai ___.', [[0, 'nghe']], 'Tai nghe giúp nghe âm thanh riêng, không ảnh hưởng xung quanh.', $D);
    }

    // 2. th-chuc-nang-phan-cung — Chức năng của từng bộ phận (lớp 6, trung bình)
    private function seedTh02(): void {
        $L = 'th-chuc-nang-phan-cung'; $D = 'trung_binh';
        $this->quiz($L, 'Muốn chụp ảnh màn hình máy tính đang hiển thị, em dùng thiết bị nào?', ['Webcam', 'Máy quét', 'Phím Print Screen trên bàn phím', 'Micro'], 2, 'Phím Print Screen chụp lại hình ảnh đang hiển thị trên màn hình.', $D);
        $this->quiz($L, 'Đơn vị nào dùng để đo dung lượng lưu trữ của ổ cứng?', ['Hz', 'GB', 'DPI', 'Pixel'], 1, 'Dung lượng ổ cứng thường đo bằng GB (gigabyte) hoặc TB.', $D);
        $this->quiz($L, 'Thiết bị nào sau đây KHÔNG phải là thiết bị lưu trữ?', ['Ổ cứng', 'USB', 'Màn hình', 'Thẻ nhớ'], 2, 'Màn hình là thiết bị hiển thị, không dùng để lưu trữ dữ liệu.', $D);
        $this->quiz($L, 'Muốn máy tính phát ra âm thanh to hơn, em điều chỉnh ở đâu?', ['Tăng độ sáng màn hình', 'Chỉnh âm lượng loa', 'Thay bàn phím mới', 'Gắn thêm USB'], 1, 'Âm lượng loa quyết định độ to nhỏ của âm thanh phát ra.', $D);
        $this->quiz($L, 'RAM càng lớn thì máy tính sẽ như thế nào?', ['Chạy nhiều chương trình cùng lúc mượt hơn', 'Màn hình sáng hơn', 'Loa to hơn', 'Máy nhẹ hơn'], 0, 'RAM lớn giúp máy chạy nhiều chương trình cùng lúc mà không bị chậm.', $D);
        $this->matching($L, 'Nối mỗi đơn vị với thứ nó dùng để đo.', [['GB', 'Dung lượng lưu trữ'], ['GHz', 'Tốc độ xử lý CPU'], ['Inch', 'Kích thước màn hình'], ['DPI', 'Độ nhạy của chuột']], 'Mỗi đơn vị đo một đặc tính khác nhau của phần cứng.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với thiết bị đáp ứng tốt nhất.', [['Chụp ảnh tự sướng', 'Webcam'], ['Nghe nhạc ngoài trời', 'Loa di động'], ['Lưu 100 GB dữ liệu', 'Ổ cứng ngoài'], ['Gõ văn bản dài', 'Bàn phím cơ']], 'Chọn thiết bị phù hợp giúp công việc thuận tiện hơn.', $D);
        $this->matching($L, 'Nối mỗi bộ phận với vai trò của nó khi chơi game.', [['Card đồ họa', 'Vẽ hình ảnh mượt mà'], ['CPU', 'Tính toán tình huống game'], ['RAM', 'Chứa dữ liệu game đang chạy'], ['Loa', 'Phát âm thanh game']], 'Chơi game mượt cần sự phối hợp của nhiều bộ phận.', $D);
        $this->matching($L, 'Nối mỗi cổng kết nối với thiết bị thường cắm vào.', [['Cổng USB', 'Chuột, bàn phím, USB'], ['Cổng HDMI', 'Màn hình, máy chiếu'], ['Cổng tai nghe', 'Tai nghe, loa'], ['Cổng mạng LAN', 'Dây mạng Internet']], 'Mỗi cổng trên máy tính có chức năng kết nối riêng.', $D);
        $this->matching($L, 'Nối mỗi thao tác với bộ phận thực hiện chính.', [['Nhấn phím', 'Bàn phím'], ['Di chuyển con trỏ', 'Chuột'], ['Nhìn kết quả', 'Màn hình'], ['Lưu bài làm', 'Ổ cứng']], 'Mỗi thao tác của người dùng gắn với một bộ phận cụ thể.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "DÙNG ĐIỆN TRỰC TIẾP" hoặc "DÙNG PIN/SẠC".', [['Máy tính để bàn', 'DÙNG ĐIỆN TRỰC TIẾP'], ['Màn hình rời', 'DÙNG ĐIỆN TRỰC TIẾP'], ['Laptop', 'DÙNG PIN/SẠC'], ['Điện thoại', 'DÙNG PIN/SẠC']], 'Máy để bàn cắm điện trực tiếp; laptop, điện thoại dùng pin sạc.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "CÓ BÀN PHÍM VẬT LÝ" hoặc "KHÔNG CÓ".', [['Máy tính để bàn', 'CÓ BÀN PHÍM VẬT LÝ'], ['Laptop', 'CÓ BÀN PHÍM VẬT LÝ'], ['Máy tính bảng', 'KHÔNG CÓ'], ['Điện thoại', 'KHÔNG CÓ']], 'Máy tính bảng và điện thoại dùng bàn phím cảm ứng trên màn hình.', $D);
        $this->sortQ($L, 'Kéo mỗi linh kiện vào nhóm "QUYẾT ĐỊNH TỐC ĐỘ" hoặc "QUYẾT ĐỊNH DUNG LƯỢNG".', [['CPU', 'QUYẾT ĐỊNH TỐC ĐỘ'], ['RAM', 'QUYẾT ĐỊNH TỐC ĐỘ'], ['Ổ cứng', 'QUYẾT ĐỊNH DUNG LƯỢNG'], ['USB', 'QUYẾT ĐỊNH DUNG LƯỢNG']], 'CPU, RAM ảnh hưởng tốc độ; ổ cứng, USB quyết định chứa được bao nhiêu.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "LÀM MÁY CHẠY NHANH HƠN" hoặc "KHÔNG ẢNH HƯỞNG TỐC ĐỘ".', [['Gắn thêm RAM', 'LÀM MÁY CHẠY NHANH HƠN'], ['Dùng ổ SSD thay HDD', 'LÀM MÁY CHẠY NHANH HƠN'], ['Đổi hình nền đẹp hơn', 'KHÔNG ẢNH HƯỞNG TỐC ĐỘ'], ['Gắn thêm loa ngoài', 'KHÔNG ẢNH HƯỞNG TỐC ĐỘ']], 'Nâng cấp RAM, SSD giúp máy nhanh hơn; đổi hình nền, gắn loa không ảnh hưởng tốc độ.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "ĐƯA DỮ LIỆU VÀO" hoặc "ĐƯA KẾT QUẢ RA".', [['Máy quét', 'ĐƯA DỮ LIỆU VÀO'], ['Bàn vẽ điện tử', 'ĐƯA DỮ LIỆU VÀO'], ['Máy chiếu', 'ĐƯA KẾT QUẢ RA'], ['Máy in 3D', 'ĐƯA KẾT QUẢ RA']], 'Máy quét, bàn vẽ đưa dữ liệu vào; máy chiếu, máy in 3D đưa kết quả ra.', $D);
        $this->fill($L, 'Đơn vị đo dung lượng lưu trữ thường dùng là ___ (viết tắt).', [[0, 'GB']], 'Ổ cứng, USB thường có dung lượng tính bằng GB hoặc TB.', $D);
        $this->fill($L, 'Thiết bị vừa là thiết bị vào vừa là thiết bị ra, dùng để lưu trữ di động là ___.', [[0, 'USB']], 'USB vừa nhận dữ liệu từ máy vừa đưa dữ liệu vào máy khác.', $D);
        $this->fill($L, 'Tốc độ xử lý của CPU thường được đo bằng đơn vị ___.', [[0, 'GHz']], 'GHz càng cao thì CPU xử lý càng nhanh.', $D);
        $this->fill($L, 'Muốn hiển thị bài thuyết trình cho cả lớp xem, em dùng máy ___.', [[0, 'chiếu']], 'Máy chiếu phóng to hình ảnh từ máy tính lên màn hình lớn.', $D);
        $this->fill($L, 'Thiết bị ___ giúp đưa văn bản trên giấy vào máy tính dưới dạng ảnh.', [[0, 'máy quét']], 'Máy quét (scanner) biến tài liệu giấy thành tệp ảnh trong máy.', $D);
    }

    // 3. th-he-dieu-hanh-ung-dung — Hệ điều hành và phần mềm ứng dụng (lớp 7, dễ)
    private function seedTh03(): void {
        $L = 'th-he-dieu-hanh-ung-dung'; $D = 'de';
        $this->quiz($L, 'Đâu là hệ điều hành trên điện thoại?', ['Microsoft Word', 'Android', 'Zalo', 'Google Chrome'], 1, 'Android là hệ điều hành phổ biến trên điện thoại.', $D);
        $this->quiz($L, 'Phần mềm nào dùng để vẽ tranh trên máy tính?', ['Paint', 'Excel', 'Notepad', 'Calculator'], 0, 'Paint là phần mềm vẽ đơn giản có sẵn trong Windows.', $D);
        $this->quiz($L, 'Muốn nghe nhạc trên máy tính, em mở phần mềm nào?', ['Trình phát nhạc (Media Player)', 'Trình soạn thảo văn bản', 'Bảng tính', 'Trình duyệt tệp'], 0, 'Trình phát nhạc chuyên dùng để mở và nghe các tệp âm thanh.', $D);
        $this->quiz($L, 'Hệ điều hành có nhiệm vụ gì?', ['Quản lý mọi hoạt động của máy tính', 'Chỉ dùng để chơi game', 'Chỉ dùng để gõ văn bản', 'Làm mát máy tính'], 0, 'Hệ điều hành quản lý phần cứng, phần mềm và mọi hoạt động của máy.', $D);
        $this->quiz($L, 'Phần mềm nào sau đây là phần mềm ứng dụng?', ['Windows', 'Linux', 'PowerPoint', 'macOS'], 2, 'PowerPoint phục vụ nhu cầu thuyết trình nên là phần mềm ứng dụng.', $D);
        $this->matching($L, 'Nối mỗi phần mềm với nhóm của nó.', [['Windows', 'Hệ điều hành'], ['Word', 'Phần mềm ứng dụng'], ['Excel', 'Phần mềm ứng dụng'], ['macOS', 'Hệ điều hành']], 'Hệ điều hành quản lý máy; phần mềm ứng dụng phục vụ công việc cụ thể.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu học tập với phần mềm phù hợp.', [['Soạn bài văn', 'Word'], ['Vẽ biểu đồ điểm số', 'Excel'], ['Làm slide thuyết trình', 'PowerPoint'], ['Tra cứu bài học trên mạng', 'Trình duyệt web']], 'Mỗi phần mềm ứng dụng phục vụ một nhu cầu khác nhau.', $D);
        $this->matching($L, 'Nối mỗi hệ điều hành với công ty phát triển nó.', [['Windows', 'Microsoft'], ['macOS', 'Apple'], ['Android', 'Google'], ['iOS', 'Apple']], 'Mỗi hệ điều hành do một công ty công nghệ phát triển.', $D);
        $this->matching($L, 'Nối mỗi biểu tượng công việc với phần mềm dùng để làm.', [['Gõ văn bản', 'Word'], ['Tính toán bảng điểm', 'Excel'], ['Chiếu slide', 'PowerPoint'], ['Ghi chú nhanh', 'Notepad']], 'Chọn đúng phần mềm giúp công việc nhanh và đẹp hơn.', $D);
        $this->matching($L, 'Nối mỗi phần mềm với thiết bị nó thường chạy trên.', [['Windows', 'Máy tính để bàn, laptop'], ['Android', 'Điện thoại, máy tính bảng'], ['iOS', 'iPhone, iPad'], ['Linux', 'Máy chủ, máy tính']], 'Mỗi hệ điều hành thường gắn với một nhóm thiết bị nhất định.', $D);
        $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm "SOẠN THẢO" hoặc "TÍNH TOÁN".', [['Word', 'SOẠN THẢO'], ['Notepad', 'SOẠN THẢO'], ['Excel', 'TÍNH TOÁN'], ['Calculator', 'TÍNH TOÁN']], 'Word, Notepad dùng soạn văn bản; Excel, Calculator dùng tính toán.', $D);
        $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm "CÓ SẴN TRONG WINDOWS" hoặc "CẦN CÀI THÊM".', [['Paint', 'CÓ SẴN TRONG WINDOWS'], ['Notepad', 'CÓ SẴN TRONG WINDOWS'], ['Zalo', 'CẦN CÀI THÊM'], ['Unikey', 'CẦN CÀI THÊM']], 'Paint, Notepad có sẵn theo Windows; Zalo, Unikey phải cài thêm.', $D);
        $this->sortQ($L, 'Kéo mỗi công việc vào nhóm "LÀM TRÊN ĐIỆN THOẠI ĐƯỢC" hoặc "NÊN LÀM TRÊN MÁY TÍNH".', [['Nhắn tin', 'LÀM TRÊN ĐIỆN THOẠI ĐƯỢC'], ['Chụp ảnh', 'LÀM TRÊN ĐIỆN THOẠI ĐƯỢC'], ['Soạn báo cáo dài', 'NÊN LÀM TRÊN MÁY TÍNH'], ['Vẽ sơ đồ phức tạp', 'NÊN LÀM TRÊN MÁY TÍNH']], 'Việc đơn giản làm trên điện thoại; việc phức tạp nên dùng máy tính.', $D);
        $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm "LÀM VIỆC" hoặc "GIẢI TRÍ".', [['Word', 'LÀM VIỆC'], ['Excel', 'LÀM VIỆC'], ['Trò chơi', 'GIẢI TRÍ'], ['Trình phát nhạc', 'GIẢI TRÍ']], 'Phần mềm ứng dụng phục vụ cả học tập, làm việc và giải trí.', $D);
        $this->sortQ($L, 'Kéo mỗi hệ điều hành vào nhóm "CHO MÁY TÍNH" hoặc "CHO ĐIỆN THOẠI".', [['Windows', 'CHO MÁY TÍNH'], ['macOS', 'CHO MÁY TÍNH'], ['Android', 'CHO ĐIỆN THOẠI'], ['iOS', 'CHO ĐIỆN THOẠI']], 'Windows, macOS chạy trên máy tính; Android, iOS chạy trên điện thoại.', $D);
        $this->fill($L, 'Phần mềm ___ dùng để làm bài thuyết trình với các slide.', [[0, 'PowerPoint']], 'PowerPoint là phần mềm tạo slide thuyết trình phổ biến.', $D);
        $this->fill($L, 'Hệ điều hành ___ rất phổ biến trên máy tính cá nhân.', [[0, 'Windows']], 'Windows của Microsoft là hệ điều hành máy tính phổ biến nhất.', $D);
        $this->fill($L, 'Để gõ được tiếng Việt có dấu, em cần cài phần mềm ___ .', [[0, 'Unikey']], 'Unikey là phần mềm gõ tiếng Việt miễn phí, phổ biến.', $D);
        $this->fill($L, 'Phần mềm ___ dùng để tính toán với bảng số liệu.', [[0, 'Excel']], 'Excel là phần mềm bảng tính giúp tính toán nhanh và vẽ biểu đồ.', $D);
        $this->fill($L, 'Mỗi phần mềm ứng dụng phục vụ một ___ cụ thể của người dùng.', [[0, 'nhu cầu']], 'Phần mềm ứng dụng được tạo ra để đáp ứng từng nhu cầu công việc.', $D);
    }

    // 4. th-tep-va-thu-muc — Tệp và thư mục (lớp 7, trung bình)
    private function seedTh04(): void {
        $L = 'th-tep-va-thu-muc'; $D = 'trung_binh';
        $this->quiz($L, 'Tệp trình chiếu PowerPoint thường có phần mở rộng nào?', ['.docx', '.pptx', '.xlsx', '.txt'], 1, 'Tệp PowerPoint thường có đuôi .pptx.', $D);
        $this->quiz($L, 'Muốn đổi tên một tệp, em làm thế nào?', ['Nhấn phím Delete', 'Nhấn phím F2 hoặc chuột phải chọn Rename', 'Nhấn Ctrl + C', 'Kéo tệp vào thùng rác'], 1, 'Nhấn F2 hoặc chuột phải chọn Rename để đổi tên tệp.', $D);
        $this->quiz($L, 'Đâu là tên tệp hợp lệ trong Windows?', ['bai:van.doc', 'bai*van.doc', 'bai-van.doc', 'bai?van.doc'], 2, 'Tên tệp không được chứa các ký tự : * ? " < > |.', $D);
        $this->quiz($L, 'Muốn tìm nhanh một tệp trong máy, em dùng cách nào?', ['Mở từng thư mục một', 'Dùng ô tìm kiếm (Search)', 'Tắt máy rồi mở lại', 'Xóa bớt tệp'], 1, 'Ô tìm kiếm giúp tìm tệp nhanh theo tên.', $D);
        $this->quiz($L, 'Thư mục có thể chứa gì bên trong?', ['Chỉ chứa tệp', 'Chỉ chứa thư mục', 'Cả tệp và thư mục con', 'Không chứa gì'], 2, 'Thư mục có thể chứa cả tệp và các thư mục con bên trong.', $D);
        $this->matching($L, 'Nối mỗi đuôi tệp với phần mềm thường dùng để mở.', [['.docx', 'Word'], ['.xlsx', 'Excel'], ['.pptx', 'PowerPoint'], ['.pdf', 'Trình đọc PDF']], 'Mỗi loại tệp thường gắn với một phần mềm chuyên mở nó.', $D);
        $this->matching($L, 'Nối mỗi thao tác với phím tắt tương ứng.', [['Sao chép', 'Ctrl + C'], ['Cắt', 'Ctrl + X'], ['Dán', 'Ctrl + V'], ['Đổi tên', 'F2']], 'Phím tắt giúp thao tác với tệp nhanh hơn.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với cách sắp xếp hợp lý.', [['Bài tập các môn', 'Thư mục riêng từng môn'], ['Ảnh gia đình', 'Thư mục Ảnh'], ['Nhạc yêu thích', 'Thư mục Nhạc'], ['Tài liệu học tập', 'Thư mục Tài liệu']], 'Đặt tệp vào thư mục theo chủ đề giúp tìm lại dễ dàng.', $D);
        $this->matching($L, 'Nối mỗi biểu tượng với ý nghĩa của nó.', [['Thư mục màu vàng', 'Nơi chứa tệp'], ['Thùng rác', 'Chứa tệp đã xóa'], ['Ổ đĩa C', 'Ổ đĩa hệ thống'], ['Tệp có dấu *', 'Tệp chưa được lưu']], 'Nhận biết biểu tượng giúp thao tác đúng trên máy tính.', $D);
        $this->matching($L, 'Nối mỗi việc với kết quả của nó.', [['Nhấn Delete', 'Tệp vào thùng rác'], ['Ctrl + C rồi Ctrl + V', 'Tạo bản sao của tệp'], ['Kéo tệp sang thư mục khác', 'Di chuyển tệp'], ['F2', 'Đổi tên tệp']], 'Mỗi thao tác trên tệp cho một kết quả khác nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi đuôi tệp vào nhóm "TÀI LIỆU" hoặc "ĐA PHƯƠNG TIỆN".', [['.docx', 'TÀI LIỆU'], ['.pdf', 'TÀI LIỆU'], ['.mp3', 'ĐA PHƯƠNG TIỆN'], ['.mp4', 'ĐA PHƯƠNG TIỆN']], 'Tệp tài liệu chứa chữ; tệp đa phương tiện chứa nhạc, video.', $D);
        $this->sortQ($L, 'Kéo mỗi tên tệp vào nhóm "HỢP LỆ" hoặc "KHÔNG HỢP LỆ".', [['bai-tap-1.docx', 'HỢP LỆ'], ['anh_dep.jpg', 'HỢP LỆ'], ['bai:tap.docx', 'KHÔNG HỢP LỆ'], ['nhac*hay.mp3', 'KHÔNG HỢP LỆ']], 'Tên tệp không được chứa : * ? " < > |.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "GIỮ TỆP AN TOÀN" hoặc "DỄ MẤT TỆP".', [['Sao lưu vào USB', 'GIỮ TỆP AN TOÀN'], ['Lưu vào 2 nơi khác nhau', 'GIỮ TỆP AN TOÀN'], ['Chỉ lưu trên màn hình nền', 'DỄ MẤT TỆP'], ['Không bao giờ sao lưu', 'DỄ MẤT TỆP']], 'Sao lưu nhiều nơi giúp tệp an toàn khi máy gặp sự cố.', $D);
        $this->sortQ($L, 'Kéo mỗi đuôi tệp vào nhóm "MỞ ĐƯỢC NGAY" hoặc "CẦN PHẦN MỀM RIÊNG".', [['.txt', 'MỞ ĐƯỢC NGAY'], ['.jpg', 'MỞ ĐƯỢC NGAY'], ['.psd', 'CẦN PHẦN MỀM RIÊNG'], ['.rar', 'CẦN PHẦN MỀM RIÊNG']], 'Tệp .txt, .jpg mở được ngay; .psd, .rar cần phần mềm chuyên dụng.', $D);
        $this->sortQ($L, 'Kéo mỗi cách đặt tên vào nhóm "DỄ TÌM" hoặc "KHÓ TÌM".', [['bai-tap-toan-tuan-3.docx', 'DỄ TÌM'], ['anh-da-lat-2024.jpg', 'DỄ TÌM'], ['a1.docx', 'KHÓ TÌM'], ['moi.docx', 'KHÓ TÌM']], 'Tên tệp rõ nghĩa, có chủ đề giúp tìm lại nhanh chóng.', $D);
        $this->fill($L, 'Tệp bảng tính Excel thường có đuôi ___.', [[0, '.xlsx']], 'Đuôi .xlsx cho biết đó là tệp bảng tính Excel.', $D);
        $this->fill($L, 'Để tạo bản sao của tệp, em dùng lệnh sao chép rồi ___.', [[0, 'dán']], 'Sao chép (Ctrl + C) rồi dán (Ctrl + V) tạo bản sao tệp.', $D);
        $this->fill($L, 'Muốn khôi phục tệp đã xóa nhầm, em mở ___ rác.', [[0, 'thùng']], 'Tệp bị xóa thường nằm trong thùng rác trước khi xóa hẳn.', $D);
        $this->fill($L, 'Phím tắt ___ dùng để đổi tên nhanh tệp đang chọn.', [[0, 'F2']], 'Nhấn F2 khi đang chọn tệp để đổi tên nhanh.', $D);
        $this->fill($L, 'Phần mở rộng của tệp cho biết ___ của tệp đó.', [[0, 'loại']], 'Nhìn đuôi tệp ta biết đó là văn bản, ảnh, nhạc hay video.', $D);
    }

    // 5. th-mat-khau-bao-mat — Mật khẩu mạnh và bảo mật cá nhân (lớp 8, trung bình)
    private function seedTh05(): void {
        $L = 'th-mat-khau-bao-mat'; $D = 'trung_binh';
        $this->quiz($L, 'Mật khẩu nào sau đây là yếu nhất?', ['Xk9#mQ2!vL', '123456', 'An@2024!', 'Tm#7kL2$pQ'], 1, 'Mật khẩu 123456 quá đơn giản, dễ bị đoán ra.', $D);
        $this->quiz($L, 'Xác thực hai yếu tố (2FA) là gì?', ['Nhập mật khẩu hai lần giống nhau', 'Ngoài mật khẩu còn cần một mã xác nhận thứ hai', 'Dùng hai tài khoản khác nhau', 'Đổi mật khẩu hai lần một năm'], 1, '2FA yêu cầu thêm mã xác nhận gửi về điện thoại, tăng bảo mật.', $D);
        $this->quiz($L, 'Khi thấy đường link lạ trong tin nhắn của bạn bè, em nên làm gì?', ['Nhấn vào ngay để xem', 'Hỏi lại bạn bè trước khi nhấn', 'Chia sẻ cho nhiều người cùng xem', 'Nhập mật khẩu để mở link'], 1, 'Tài khoản bạn bè có thể bị hack để gửi link độc; nên hỏi lại trước.', $D);
        $this->quiz($L, 'Thông tin nào sau đây là thông tin cá nhân cần bảo vệ?', ['Tên món ăn yêu thích', 'Số điện thoại của em', 'Tên trường học', 'Màu sắc yêu thích'], 1, 'Số điện thoại là thông tin cá nhân, kẻ xấu có thể lợi dụng.', $D);
        $this->quiz($L, 'Nên đổi mật khẩu trong trường hợp nào?', ['Mỗi khi rảnh rỗi', 'Khi nghi ngờ mật khẩu bị lộ', 'Khi quên tên đăng nhập', 'Khi máy chạy chậm'], 1, 'Khi nghi lộ mật khẩu, đổi ngay để bảo vệ tài khoản.', $D);
        $this->matching($L, 'Nối mỗi tình huống với cách xử lý an toàn.', [['Nhận tin nhắn trúng thưởng lạ', 'Không nhấn link, xóa tin nhắn'], ['Bạn rủ cho mượn tài khoản game', 'Từ chối, giữ tài khoản riêng'], ['Quên đăng xuất ở quán net', 'Nhờ người tin cậy đăng xuất giúp'], ['Có người xin mã OTP', 'Tuyệt đối không cho']], 'Xử lý đúng tình huống giúp bảo vệ tài khoản và thông tin cá nhân.', $D);
        $this->matching($L, 'Nối mỗi mật khẩu với đánh giá của nó.', [['NguyenVanA2001', 'Yếu – chứa tên và năm sinh'], ['#kP9!mZ2@qW', 'Mạnh – đủ loại ký tự'], ['abcdef', 'Yếu – quá ngắn, dễ đoán'], ['Matkhau123', 'Yếu – từ thông dụng + số']], 'Mật khẩu mạnh dài, đủ chữ hoa, chữ thường, số và ký tự đặc biệt.', $D);
        $this->matching($L, 'Nối mỗi thói quen với đánh giá tốt hay xấu.', [['Dùng mật khẩu khác nhau cho mỗi tài khoản', 'Tốt'], ['Ghi mật khẩu ra giấy dán ở máy', 'Xấu'], ['Bật xác thực hai bước', 'Tốt'], ['Chia sẻ mật khẩu cho bạn thân', 'Xấu']], 'Thói quen tốt giúp tài khoản an toàn hơn mỗi ngày.', $D);
        $this->matching($L, 'Nối mỗi loại thông tin với cách chia sẻ đúng.', [['Ảnh thẻ học sinh', 'Chỉ cho người tin cậy'], ['Sở thích âm nhạc', 'Có thể chia sẻ công khai'], ['Địa chỉ nhà', 'Giữ bí mật'], ['Tên đội bóng yêu thích', 'Có thể chia sẻ công khai']], 'Thông tin nhạy cảm giữ bí mật, thông tin chung có thể chia sẻ.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu với việc nên làm.', [['Tài khoản tự gửi tin nhắn lạ', 'Đổi mật khẩu ngay'], ['Có đăng nhập lạ từ nơi xa', 'Kiểm tra và đăng xuất thiết bị lạ'], ['Quên mật khẩu', 'Dùng chức năng quên mật khẩu chính thức'], ['Muốn bảo mật cao hơn', 'Bật xác thực hai bước']], 'Phát hiện sớm dấu hiệu bất thường giúp xử lý kịp thời.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "BẢO VỆ TÀI KHOẢN" hoặc "GÂY NGUY HIỂM".', [['Đặt mật khẩu dài, khó đoán', 'BẢO VỆ TÀI KHOẢN'], ['Đăng xuất sau khi dùng máy chung', 'BẢO VỆ TÀI KHOẢN'], ['Dùng chung một mật khẩu mọi nơi', 'GÂY NGUY HIỂM'], ['Nhấn vào link trúng thưởng lạ', 'GÂY NGUY HIỂM']], 'Thói quen tốt bảo vệ tài khoản, thói quen xấu gây nguy hiểm.', $D);
        $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm "GIỮ BÍ MẬT" hoặc "CÓ THỂ CÔNG KHAI".', [['Mật khẩu', 'GIỮ BÍ MẬT'], ['Mã OTP', 'GIỮ BÍ MẬT'], ['Tên nickname trong game', 'CÓ THỂ CÔNG KHAI'], ['Món ăn yêu thích', 'CÓ THỂ CÔNG KHAI']], 'Mật khẩu, mã OTP tuyệt đối giữ bí mật.', $D);
        $this->sortQ($L, 'Kéo mỗi mật khẩu vào nhóm "ĐỦ MẠNH" hoặc "CẦN ĐỔI NGAY".', [['Tr#9kLm2$Qx', 'ĐỦ MẠNH'], ['Ab@2024!xY', 'ĐỦ MẠNH'], ['12345678', 'CẦN ĐỔI NGAY'], ['password', 'CẦN ĐỔI NGAY']], 'Mật khẩu mạnh dài và đủ loại ký tự; mật khẩu đơn giản cần đổi ngay.', $D);
        $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm "VĂN MINH MẠNG" hoặc "THIẾU VĂN MINH".', [['Không bình luận ác ý', 'VĂN MINH MẠNG'], ['Tôn trọng ý kiến người khác', 'VĂN MINH MẠNG'], ['Chia sẻ tin giả', 'THIẾU VĂN MINH'], ['Lấy ảnh người khác đăng bêu xấu', 'THIẾU VĂN MINH']], 'Ứng xử văn minh giúp môi trường mạng lành mạnh.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "NÊN LÀM" hoặc "KHÔNG NÊN" khi dùng Wi-Fi công cộng.', [['Không đăng nhập tài khoản ngân hàng', 'NÊN LÀM'], ['Đăng xuất sau khi dùng xong', 'NÊN LÀM'], ['Mua sắm online bằng thẻ', 'KHÔNG NÊN'], ['Nhập mật khẩu quan trọng', 'KHÔNG NÊN']], 'Wi-Fi công cộng kém an toàn, hạn chế dùng việc quan trọng.', $D);
        $this->fill($L, 'Không bao giờ chia sẻ mã ___ cho bất kỳ ai, kể cả người tự xưng là nhân viên.', [[0, 'OTP']], 'Mã OTP là chìa khóa thứ hai của tài khoản, tuyệt đối giữ bí mật.', $D);
        $this->fill($L, 'Nên đặt mật khẩu khác nhau cho từng ___ khoản.', [[0, 'tài']], 'Mỗi tài khoản một mật khẩu riêng để lộ một chỗ không mất tất cả.', $D);
        $this->fill($L, 'Khi nhận link lạ, em nên ___ lại người gửi trước khi nhấn vào.', [[0, 'hỏi']], 'Hỏi lại người gửi giúp tránh bẫy lừa đảo qua tài khoản bị hack.', $D);
        $this->fill($L, 'Bật xác thực hai ___ giúp tài khoản an toàn hơn nhiều.', [[0, 'bước']], 'Xác thực hai bước yêu cầu thêm mã xác nhận ngoài mật khẩu.', $D);
        $this->fill($L, 'Sau khi dùng máy tính ở quán, em phải đăng ___ khỏi mọi tài khoản.', [[0, 'xuất']], 'Đăng xuất tránh người khác dùng tài khoản của em.', $D);
    }

    // 6. th-go-phim-tat — Gõ phím và phím tắt văn bản (lớp 8, dễ)
    private function seedTh06(): void {
        $L = 'th-go-phim-tat'; $D = 'de';
        $this->quiz($L, 'Tổ hợp phím nào dùng để cắt (cut) nội dung?', ['Ctrl + C', 'Ctrl + X', 'Ctrl + V', 'Ctrl + Z'], 1, 'Ctrl + X dùng để cắt nội dung đã chọn.', $D);
        $this->quiz($L, 'Muốn hoàn tác (undo) thao tác vừa làm, em nhấn phím nào?', ['Ctrl + Y', 'Ctrl + Z', 'Ctrl + S', 'Ctrl + P'], 1, 'Ctrl + Z giúp hoàn tác thao tác vừa thực hiện.', $D);
        $this->quiz($L, 'Phím nào dùng để xóa ký tự bên phải con trỏ?', ['Backspace', 'Delete', 'Enter', 'Shift'], 1, 'Phím Delete xóa ký tự bên phải con trỏ.', $D);
        $this->quiz($L, 'Tổ hợp phím nào dùng để in văn bản?', ['Ctrl + P', 'Ctrl + I', 'Ctrl + O', 'Ctrl + N'], 0, 'Ctrl + P mở hộp thoại in văn bản.', $D);
        $this->quiz($L, 'Khi gõ 10 ngón, ngón trỏ tay trái đặt ở phím nào lúc bắt đầu?', ['Phím A', 'Phím F', 'Phím J', 'Phím K'], 1, 'Ngón trỏ tay trái đặt ở phím F, tay phải ở phím J (có gờ nổi).', $D);
        $this->matching($L, 'Nối mỗi tổ hợp phím với chức năng của nó.', [['Ctrl + X', 'Cắt'], ['Ctrl + Z', 'Hoàn tác'], ['Ctrl + P', 'In văn bản'], ['Ctrl + A', 'Chọn tất cả']], 'Nhớ phím tắt giúp soạn thảo văn bản nhanh hơn.', $D);
        $this->matching($L, 'Nối mỗi phím với công dụng của nó.', [['Enter', 'Xuống dòng mới'], ['Backspace', 'Xóa ký tự bên trái'], ['Delete', 'Xóa ký tự bên phải'], ['Tab', 'Thụt đầu dòng']], 'Mỗi phím đặc biệt có một công dụng riêng khi soạn thảo.', $D);
        $this->matching($L, 'Nối mỗi ngón tay với hàng phím nó hay dùng.', [['Ngón út trái', 'Phím Shift trái, phím A'], ['Ngón trỏ phải', 'Phím J, H và xung quanh'], ['Ngón cái', 'Phím cách (Space)'], ['Ngón út phải', 'Phím Enter, Shift phải']], 'Gõ 10 ngón đúng kỹ thuật giúp gõ nhanh và ít mỏi.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với phím tắt phù hợp.', [['Lưu nhanh', 'Ctrl + S'], ['In đậm chữ đã chọn', 'Ctrl + B'], ['Mở tệp mới', 'Ctrl + N'], ['Tìm kiếm trong văn bản', 'Ctrl + F']], 'Dùng phím tắt đúng lúc tiết kiệm nhiều thời gian.', $D);
        $this->matching($L, 'Nối mỗi tổ hợp phím với nhóm chức năng.', [['Ctrl + B', 'Định dạng chữ'], ['Ctrl + E', 'Căn giữa đoạn văn'], ['Ctrl + L', 'Căn trái đoạn văn'], ['Ctrl + J', 'Căn đều hai bên']], 'Nhóm phím Ctrl + B/I/U định dạng chữ; Ctrl + E/L/R/J căn lề.', $D);
        $this->sortQ($L, 'Kéo mỗi phím tắt vào nhóm "SOẠN THẢO" hoặc "TÌM KIẾM – IN ẤN".', [['Ctrl + C', 'SOẠN THẢO'], ['Ctrl + V', 'SOẠN THẢO'], ['Ctrl + F', 'TÌM KIẾM – IN ẤN'], ['Ctrl + P', 'TÌM KIẾM – IN ẤN']], 'Ctrl + C/V phục vụ soạn thảo; Ctrl + F/P phục vụ tìm kiếm, in ấn.', $D);
        $this->sortQ($L, 'Kéo mỗi phím vào nhóm "HÀNG PHÍM CƠ SỞ" hoặc "HÀNG PHÍM SỐ".', [['A', 'HÀNG PHÍM CƠ SỞ'], ['S', 'HÀNG PHÍM CƠ SỞ'], ['1', 'HÀNG PHÍM SỐ'], ['5', 'HÀNG PHÍM SỐ']], 'Hàng phím cơ sở (A S D F...) là nơi đặt tay khi bắt đầu gõ.', $D);
        $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm "GÕ NHANH" hoặc "GÕ CHẬM".', [['Nhìn màn hình khi gõ', 'GÕ NHANH'], ['Gõ đủ 10 ngón', 'GÕ NHANH'], ['Nhìn bàn phím từng phím', 'GÕ CHẬM'], ['Gõ bằng 2 ngón trỏ', 'GÕ CHẬM']], 'Gõ 10 ngón, nhìn màn hình giúp tốc độ tăng dần theo thời gian.', $D);
        $this->sortQ($L, 'Kéo mỗi phím tắt vào nhóm "ĐÃ HỌC" hoặc "MỚI".', [['Ctrl + C (sao chép)', 'ĐÃ HỌC'], ['Ctrl + V (dán)', 'ĐÃ HỌC'], ['Ctrl + Shift + S (lưu thành tệp mới)', 'MỚI'], ['Alt + Tab (chuyển cửa sổ)', 'MỚI']], 'Ôn lại phím tắt đã học và khám phá thêm phím tắt mới hữu ích.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "ĐÚNG TƯ THẾ" hoặc "SAI TƯ THẾ" khi gõ phím.', [['Ngồi thẳng lưng', 'ĐÚNG TƯ THẾ'], ['Cổ tay thả lỏng', 'ĐÚNG TƯ THẾ'], ['Cúi sát màn hình', 'SAI TƯ THẾ'], ['Tì cổ tay mạnh xuống bàn', 'SAI TƯ THẾ']], 'Tư thế đúng giúp gõ lâu không mỏi và bảo vệ mắt, cột sống.', $D);
        $this->fill($L, 'Để sao chép nội dung đã chọn, em nhấn ___ + C.', [[0, 'Ctrl']], 'Ctrl + C là phím tắt sao chép phổ biến nhất.', $D);
        $this->fill($L, 'Phím tắt ___ + Z giúp hoàn tác thao tác vừa làm.', [[0, 'Ctrl']], 'Lỡ tay xóa nhầm, nhấn Ctrl + Z để hoàn tác ngay.', $D);
        $this->fill($L, 'Hai phím có gờ nổi để định vị ngón trỏ là phím F và phím ___.', [[0, 'J']], 'Gờ nổi trên F và J giúp đặt tay đúng vị trí mà không cần nhìn.', $D);
        $this->fill($L, 'Phím ___ dùng để xóa ký tự bên trái con trỏ.', [[0, 'Backspace']], 'Backspace xóa ngược về phía trước con trỏ.', $D);
        $this->fill($L, 'Muốn chọn toàn bộ văn bản, em nhấn Ctrl + ___.', [[0, 'A']], 'Ctrl + A chọn tất cả nội dung trong tài liệu.', $D);
    }

    // 7. th-phan-cung-may-tinh-lop-6-1 — Phần cứng máy tính lớp 6: các bộ phận chính (1) (lớp 6, dễ)
    private function seedTh07(): void {
        $L = 'th-phan-cung-may-tinh-lop-6-1'; $D = 'de';
        $this->quiz($L, 'Bộ phận nào giúp máy tính kết nối Internet không dây?', ['Card Wi-Fi', 'Ổ cứng', 'Loa', 'Máy in'], 0, 'Card Wi-Fi giúp máy tính bắt sóng mạng không dây.', $D);
        $this->quiz($L, 'Muốn máy tính hiển thị to hơn, em có thể làm gì?', ['Dùng màn hình lớn hơn', 'Gõ phím mạnh hơn', 'Tăng âm lượng loa', 'Cắm thêm USB'], 0, 'Màn hình càng lớn thì hình ảnh hiển thị càng to.', $D);
        $this->quiz($L, 'Thiết bị nào sau đây là bộ phận chính KHÔNG thể thiếu của máy tính?', ['Máy in', 'Bộ xử lý (CPU)', 'Webcam', 'Máy chiếu'], 1, 'CPU là bộ phận chính, không có CPU máy tính không hoạt động được.', $D);
        $this->quiz($L, 'Bàn di chuột (mousepad) có tác dụng gì?', ['Giúp chuột di chuyển mượt và chính xác hơn', 'Làm mát máy tính', 'Tăng âm lượng', 'Sạc pin cho chuột'], 0, 'Bàn di chuột tạo bề mặt phẳng giúp chuột hoạt động chính xác.', $D);
        $this->quiz($L, 'Máy tính bảng (tablet) khác điện thoại ở điểm nào?', ['Màn hình lớn hơn, tiện học tập', 'Không gọi điện được', 'Không chụp ảnh được', 'Không có loa'], 0, 'Máy tính bảng có màn hình lớn hơn, tiện cho học tập và đọc sách.', $D);
        $this->matching($L, 'Nối mỗi bộ phận với việc nó giúp em làm.', [['Màn hình', 'Xem bài giảng'], ['Loa', 'Nghe bài hát'], ['Bàn phím', 'Gõ bài văn'], ['Máy in', 'In phiếu bài tập']], 'Mỗi bộ phận hỗ trợ một hoạt động học tập khác nhau.', $D);
        $this->matching($L, 'Nối mỗi thiết bị với đặc điểm của nó.', [['Laptop', 'Gập gọn, mang đi được'], ['Máy tính để bàn', 'Mạnh, đặt cố định'], ['Máy tính bảng', 'Màn hình cảm ứng'], ['Điện thoại', 'Nhỏ gọn trong túi']], 'Mỗi loại máy tính có ưu điểm riêng phù hợp nhu cầu.', $D);
        $this->matching($L, 'Nối mỗi bộ phận với vị trí của nó trên laptop.', [['Màn hình', 'Nắp máy'], ['Bàn phím', 'Mặt đế'], ['Touchpad', 'Dưới bàn phím'], ['Loa', 'Hai bên thân máy']], 'Laptop gộp mọi bộ phận trong một khối gọn nhẹ.', $D);
        $this->matching($L, 'Nối mỗi cổng với thiết bị hay cắm vào đó.', [['Cổng sạc', 'Dây sạc'], ['Cổng USB', 'Chuột không dây'], ['Jack tai nghe', 'Tai nghe'], ['Cổng HDMI', 'Dây nối máy chiếu']], 'Nhận biết các cổng giúp cắm thiết bị đúng chỗ.', $D);
        $this->matching($L, 'Nối mỗi việc bảo quản với bộ phận cần bảo quản.', [['Không ăn uống gần máy', 'Bàn phím'], ['Dán miếng chống xước', 'Màn hình'], ['Để nơi khô ráo', 'Thân máy'], ['Sạc đúng cách', 'Pin laptop']], 'Bảo quản đúng cách giúp máy tính bền lâu.', $D);
        $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm "BẮT BUỘC CÓ" hoặc "CÓ THỂ GẮN THÊM".', [['CPU', 'BẮT BUỘC CÓ'], ['Màn hình', 'BẮT BUỘC CÓ'], ['Máy in', 'CÓ THỂ GẮN THÊM'], ['Webcam rời', 'CÓ THỂ GẮN THÊM']], 'CPU, màn hình là bộ phận chính; máy in, webcam rời gắn thêm khi cần.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "DÙNG HÀNG NGÀY" hoặc "THỈNH THOẢNG MỚI DÙNG".', [['Bàn phím', 'DÙNG HÀNG NGÀY'], ['Màn hình', 'DÙNG HÀNG NGÀY'], ['Máy quét', 'THỈNH THOẢNG MỚI DÙNG'], ['Máy chiếu', 'THỈNH THOẢNG MỚI DÙNG']], 'Bàn phím, màn hình dùng mỗi ngày; máy quét, máy chiếu dùng khi cần.', $D);
        $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm "ƯU ĐIỂM CỦA LAPTOP" hoặc "ƯU ĐIỂM CỦA MÁY BÀN".', [['Mang đi học được', 'ƯU ĐIỂM CỦA LAPTOP'], ['Gọn nhẹ', 'ƯU ĐIỂM CỦA LAPTOP'], ['Màn hình rất to', 'ƯU ĐIỂM CỦA MÁY BÀN'], ['Dễ nâng cấp linh kiện', 'ƯU ĐIỂM CỦA MÁY BÀN']], 'Laptop gọn nhẹ mang đi; máy bàn màn hình to, dễ nâng cấp.', $D);
        $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm "NHẬP" hoặc "XUẤT".', [['Bàn vẽ điện tử', 'NHẬP'], ['Tay cầm chơi game', 'NHẬP'], ['Tai nghe', 'XUẤT'], ['Máy in ảnh', 'XUẤT']], 'Bàn vẽ, tay cầm đưa dữ liệu vào; tai nghe, máy in ảnh đưa kết quả ra.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "GIỮ MÁY BỀN" hoặc "LÀM MÁY NHANH HỎNG".', [['Tắt máy đúng cách', 'GIỮ MÁY BỀN'], ['Để máy nơi thoáng mát', 'GIỮ MÁY BỀN'], ['Vừa sạc vừa chơi game nặng', 'LÀM MÁY NHANH HỎNG'], ['Để nước đổ vào bàn phím', 'LÀM MÁY NHANH HỎNG']], 'Dùng và bảo quản đúng cách giúp máy tính bền lâu.', $D);
        $this->fill($L, 'Thiết bị giúp máy tính bắt sóng Wi-Fi gọi là card ___.', [[0, 'Wi-Fi']], 'Card Wi-Fi giúp máy tính kết nối mạng không dây.', $D);
        $this->fill($L, 'Bàn di chuột giúp chuột di chuyển ___ xác hơn.', [[0, 'chính']], 'Mousepad tạo bề mặt phẳng, chuột di chuyển mượt và chính xác.', $D);
        $this->fill($L, 'Máy tính ___ gộp mọi bộ phận trong một khối có thể gập gọn.', [[0, 'xách tay']], 'Máy tính xách tay (laptop) gọn nhẹ, mang đi dễ dàng.', $D);
        $this->fill($L, 'Muốn trình chiếu bài học cho cả lớp, thầy cô dùng máy ___.', [[0, 'chiếu']], 'Máy chiếu phóng hình ảnh từ máy tính lên màn hình lớn.', $D);
        $this->fill($L, 'Bộ phận ___ là trái tim xử lý của mọi máy tính.', [[0, 'CPU']], 'CPU thực hiện tính toán và điều khiển toàn bộ máy tính.', $D);
    }

    // 8. th-phan-cung-may-tinh-lop-6-2 — Phần cứng máy tính lớp 6: thiết bị vào – ra (2) (lớp 6, dễ)
    private function seedTh08(): void {
        $L = 'th-phan-cung-may-tinh-lop-6-2'; $D = 'de';
        $this->quiz($L, 'Thiết bị nào sau đây là thiết bị vào?', ['Máy in', 'Bàn vẽ điện tử', 'Loa', 'Màn hình'], 1, 'Bàn vẽ điện tử đưa nét vẽ vào máy tính nên là thiết bị vào.', $D);
        $this->quiz($L, 'Thiết bị nào sau đây là thiết bị ra?', ['Bàn phím', 'Chuột', 'Tai nghe', 'Micro'], 2, 'Tai nghe phát âm thanh ra ngoài nên là thiết bị ra.', $D);
        $this->quiz($L, 'Máy quét (scanner) là thiết bị vào hay ra?', ['Thiết bị vào', 'Thiết bị ra', 'Vừa vào vừa ra', 'Không phải cả hai'], 0, 'Máy quét đưa hình ảnh từ giấy vào máy tính nên là thiết bị vào.', $D);
        $this->quiz($L, 'Màn hình cảm ứng vừa hiển thị vừa nhận thao tác chạm, nó thuộc loại nào?', ['Chỉ là thiết bị vào', 'Chỉ là thiết bị ra', 'Vừa vào vừa ra', 'Thiết bị lưu trữ'], 2, 'Màn hình cảm ứng vừa hiển thị (ra) vừa nhận chạm (vào).', $D);
        $this->quiz($L, 'Muốn đưa giọng nói vào máy tính để máy ghi lại, em dùng thiết bị nào?', ['Loa', 'Micro', 'Máy in', 'Tai nghe'], 1, 'Micro thu âm thanh đưa vào máy tính.', $D);
        $this->matching($L, 'Nối mỗi thiết bị với loại của nó.', [['Bàn vẽ điện tử', 'Thiết bị vào'], ['Máy quét', 'Thiết bị vào'], ['Máy chiếu', 'Thiết bị ra'], ['Máy in 3D', 'Thiết bị ra']], 'Thiết bị vào đưa dữ liệu vào máy; thiết bị ra đưa kết quả ra ngoài.', $D);
        $this->matching($L, 'Nối mỗi thiết bị với công việc nó làm.', [['Tay cầm chơi game', 'Điều khiển nhân vật'], ['Máy in ảnh', 'In ảnh ra giấy'], ['Bàn phím', 'Gõ chữ'], ['Loa', 'Phát nhạc']], 'Mỗi thiết bị phục vụ một công việc cụ thể.', $D);
        $this->matching($L, 'Nối mỗi việc học với thiết bị hỗ trợ.', [['Vẽ tranh trên máy', 'Bàn vẽ điện tử'], ['Học phát âm tiếng Anh', 'Tai nghe có micro'], ['In bài kiểm tra', 'Máy in'], ['Thuyết trình nhóm', 'Máy chiếu']], 'Chọn đúng thiết bị giúp việc học thuận lợi hơn.', $D);
        $this->matching($L, 'Nối mỗi thiết bị với giác quan con người tương ứng.', [['Webcam', 'Thị giác – nhìn'], ['Micro', 'Thính giác – nghe'], ['Loa', 'Thính giác – nghe'], ['Màn hình', 'Thị giác – nhìn']], 'Thiết bị vào/ra hoạt động như các giác quan của máy tính.', $D);
        $this->matching($L, 'Nối mỗi cặp thiết bị với điểm giống nhau.', [['Bàn phím – Chuột', 'Đều là thiết bị vào'], ['Màn hình – Loa', 'Đều là thiết bị ra'], ['USB – Thẻ nhớ', 'Đều lưu trữ di động'], ['Webcam – Micro', 'Đều thu dữ liệu vào']], 'Nhóm thiết bị theo điểm chung giúp dễ ghi nhớ.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "VÀO" hoặc "RA".', [['Tay cầm chơi game', 'VÀO'], ['Bút cảm ứng', 'VÀO'], ['Máy in', 'RA'], ['Màn hình', 'RA']], 'Tay cầm, bút cảm ứng đưa lệnh vào; máy in, màn hình đưa kết quả ra.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "THIẾT BỊ VÀO" hoặc "THIẾT BỊ RA".', [['Máy quét vân tay', 'THIẾT BỊ VÀO'], ['Bàn phím', 'THIẾT BỊ VÀO'], ['Tai nghe', 'THIẾT BỊ RA'], ['Máy chiếu', 'THIẾT BỊ RA']], 'Máy quét vân tay, bàn phím là thiết bị vào; tai nghe, máy chiếu là thiết bị ra.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "DÙNG TAY" hoặc "DÙNG GIỌNG NÓI".', [['Chuột', 'DÙNG TAY'], ['Bàn phím', 'DÙNG TAY'], ['Micro', 'DÙNG GIỌNG NÓI'], ['Loa thông minh', 'DÙNG GIỌNG NÓI']], 'Chuột, bàn phím điều khiển bằng tay; micro, loa thông minh dùng giọng nói.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "CHO HÌNH ẢNH" hoặc "CHO ÂM THANH".', [['Máy ảnh kỹ thuật số', 'CHO HÌNH ẢNH'], ['Máy quét', 'CHO HÌNH ẢNH'], ['Micro', 'CHO ÂM THANH'], ['Đàn organ điện tử', 'CHO ÂM THANH']], 'Máy ảnh, máy quét đưa hình ảnh vào; micro, đàn organ đưa âm thanh vào.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "VỪA VÀO VỪA RA" hoặc "CHỈ MỘT CHIỀU".', [['Màn hình cảm ứng', 'VỪA VÀO VỪA RA'], ['USB', 'VỪA VÀO VỪA RA'], ['Bàn phím', 'CHỈ MỘT CHIỀU'], ['Máy in', 'CHỈ MỘT CHIỀU']], 'Màn hình cảm ứng, USB hai chiều; bàn phím chỉ vào, máy in chỉ ra.', $D);
        $this->fill($L, 'Bàn vẽ điện tử là thiết bị ___ (vào/ra).', [[0, 'vào']], 'Bàn vẽ đưa nét vẽ vào máy tính nên là thiết bị vào.', $D);
        $this->fill($L, 'Tai nghe là thiết bị ___ (vào/ra).', [[0, 'ra']], 'Tai nghe phát âm thanh ra ngoài nên là thiết bị ra.', $D);
        $this->fill($L, 'Máy ___ biến văn bản giấy thành tệp trong máy tính.', [[0, 'quét']], 'Máy quét đưa tài liệu giấy vào máy dưới dạng ảnh.', $D);
        $this->fill($L, 'Thiết bị ___ thu giọng nói đưa vào máy tính.', [[0, 'micro']], 'Micro thu âm thanh từ bên ngoài vào máy.', $D);
        $this->fill($L, 'Màn hình cảm ứng vừa là thiết bị vào vừa là thiết bị ___.', [[0, 'ra']], 'Màn hình cảm ứng hiển thị (ra) và nhận thao tác chạm (vào).', $D);
    }

    // 9. th-phan-cung-may-tinh-lop-7-1 — Phần cứng máy tính lớp 7: bộ xử lý và bộ nhớ (1) (lớp 7, dễ)
    private function seedTh09(): void {
        $L = 'th-phan-cung-may-tinh-lop-7-1'; $D = 'de';
        $this->quiz($L, 'ROM có đặc điểm gì?', ['Mất dữ liệu khi tắt máy', 'Giữ dữ liệu khi tắt máy, chỉ đọc', 'Chạy nhanh hơn RAM', 'Dùng để gõ văn bản'], 1, 'ROM là bộ nhớ chỉ đọc, giữ dữ liệu kể cả khi tắt máy.', $D);
        $this->quiz($L, 'Đơn vị nhỏ nhất của thông tin trong máy tính là gì?', ['Byte', 'Bit', 'GB', 'Pixel'], 1, 'Bit là đơn vị thông tin nhỏ nhất, có 2 trạng thái 0 và 1.', $D);
        $this->quiz($L, '1 Byte bằng bao nhiêu Bit?', ['4 Bit', '8 Bit', '10 Bit', '16 Bit'], 1, '1 Byte gồm 8 Bit.', $D);
        $this->quiz($L, 'Ổ SSD khác ổ HDD ở điểm nào?', ['SSD không có đĩa quay, chạy nhanh và êm hơn', 'SSD to hơn HDD', 'SSD rẻ hơn HDD', 'SSD cần cắm điện liên tục'], 0, 'SSD dùng chip nhớ nên nhanh, êm và bền hơn HDD dùng đĩa quay.', $D);
        $this->quiz($L, 'Muốn lưu được nhiều phim, ảnh, em cần ổ cứng có gì?', ['Tốc độ CPU cao', 'Dung lượng lớn', 'Màn hình to', 'Loa hay'], 1, 'Dung lượng ổ cứng càng lớn thì chứa được càng nhiều dữ liệu.', $D);
        $this->matching($L, 'Nối mỗi đơn vị lưu trữ với giá trị của nó.', [['1 KB', '1024 Byte'], ['1 MB', '1024 KB'], ['1 GB', '1024 MB'], ['1 TB', '1024 GB']], 'Các đơn vị lưu trữ tăng dần theo bội số 1024.', $D);
        $this->matching($L, 'Nối mỗi linh kiện với đặc điểm của nó.', [['CPU', 'Xử lý nhanh hay chậm'], ['RAM', 'Mất dữ liệu khi tắt máy'], ['ROM', 'Giữ dữ liệu khi tắt máy'], ['Ổ cứng', 'Lưu được rất nhiều']], 'Mỗi linh kiện có đặc điểm riêng cần ghi nhớ.', $D);
        $this->matching($L, 'Nối mỗi loại bộ nhớ với ví dụ.', [['Bộ nhớ tạm', 'RAM'], ['Bộ nhớ chỉ đọc', 'ROM'], ['Lưu trữ trong', 'Ổ SSD'], ['Lưu trữ ngoài', 'USB']], 'Phân biệt các loại bộ nhớ theo tính chất của chúng.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với linh kiện cần nâng cấp.', [['Máy chạy chậm khi mở nhiều tab', 'Thêm RAM'], ['Hết chỗ lưu phim', 'Ổ cứng lớn hơn'], ['Mở phần mềm rất lâu', 'Dùng ổ SSD'], ['Máy xử lý chậm mọi việc', 'CPU mạnh hơn']], 'Nâng cấp đúng linh kiện giải quyết đúng vấn đề của máy.', $D);
        $this->matching($L, 'Nối mỗi thuật ngữ với nghĩa của nó.', [['Bit', 'Đơn vị thông tin nhỏ nhất'], ['Byte', 'Gồm 8 Bit'], ['Cache', 'Bộ nhớ đệm siêu nhanh của CPU'], ['Chip', 'Mạch điện tử tích hợp']], 'Hiểu thuật ngữ giúp đọc hiểu thông số máy tính.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn vị vào nhóm "NHỎ HƠN 1 MB" hoặc "LỚN HƠN 1 MB".', [['Bit', 'NHỎ HƠN 1 MB'], ['KB', 'NHỎ HƠN 1 MB'], ['GB', 'LỚN HƠN 1 MB'], ['TB', 'LỚN HƠN 1 MB']], 'Bit, KB nhỏ hơn 1 MB; GB, TB lớn hơn 1 MB.', $D);
        $this->sortQ($L, 'Kéo mỗi linh kiện vào nhóm "TRONG THÂN MÁY" hoặc "GẮN NGOÀI".', [['CPU', 'TRONG THÂN MÁY'], ['RAM', 'TRONG THÂN MÁY'], ['USB', 'GẮN NGOÀI'], ['Ổ cứng di động', 'GẮN NGOÀI']], 'CPU, RAM nằm trong thân máy; USB, ổ cứng di động gắn ngoài.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị lưu trữ vào nhóm "NHANH" hoặc "CHẬM HƠN".', [['Ổ SSD', 'NHANH'], ['RAM', 'NHANH'], ['Ổ HDD', 'CHẬM HƠN'], ['Đĩa DVD', 'CHẬM HƠN']], 'SSD, RAM truy xuất nhanh; HDD, đĩa DVD chậm hơn.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "CỦA BỘ NHỚ" hoặc "CỦA BỘ XỬ LÝ".', [['Chứa dữ liệu đang dùng', 'CỦA BỘ NHỚ'], ['Lưu bài tập lâu dài', 'CỦA BỘ NHỚ'], ['Tính toán phép cộng', 'CỦA BỘ XỬ LÝ'], ['Điều khiển các bộ phận', 'CỦA BỘ XỬ LÝ']], 'Bộ nhớ chứa dữ liệu; bộ xử lý (CPU) tính toán và điều khiển.', $D);
        $this->sortQ($L, 'Kéo mỗi thứ vào nhóm "ĐO BẰNG GB" hoặc "KHÔNG ĐO BẰNG GB".', [['Dung lượng USB', 'ĐO BẰNG GB'], ['Dung lượng ổ cứng', 'ĐO BẰNG GB'], ['Tốc độ CPU', 'KHÔNG ĐO BẰNG GB'], ['Kích thước màn hình', 'KHÔNG ĐO BẰNG GB']], 'Dung lượng lưu trữ đo bằng GB; tốc độ CPU đo bằng GHz.', $D);
        $this->fill($L, 'Đơn vị thông tin nhỏ nhất trong máy tính là ___.', [[0, 'bit']], 'Bit chỉ có hai trạng thái 0 và 1.', $D);
        $this->fill($L, '1 KB bằng ___ Byte.', [[0, '1024']], 'Trong máy tính, 1 KB = 1024 Byte.', $D);
        $this->fill($L, 'ROM là bộ nhớ chỉ ___, không ghi thêm được.', [[0, 'đọc']], 'ROM (Read Only Memory) là bộ nhớ chỉ đọc.', $D);
        $this->fill($L, 'Ổ ___ chạy nhanh và êm hơn ổ HDD truyền thống.', [[0, 'SSD']], 'SSD dùng chip nhớ nên nhanh, êm và chịu va đập tốt.', $D);
        $this->fill($L, 'Muốn máy mở nhiều chương trình cùng lúc mượt mà, em cần ___ lớn.', [[0, 'RAM']], 'RAM lớn chứa được nhiều dữ liệu đang chạy cùng lúc.', $D);
    }

    // 10. th-phan-cung-may-tinh-lop-7-2 — Phần cứng máy tính lớp 7: bảo quản thiết bị (2) (lớp 7, trung bình)
    private function seedTh10(): void {
        $L = 'th-phan-cung-may-tinh-lop-7-2'; $D = 'trung_binh';
        $this->quiz($L, 'Vì sao không nên để máy tính dưới ánh nắng trực tiếp?', ['Máy sẽ chạy nhanh hơn', 'Nhiệt độ cao làm hỏng linh kiện', 'Màn hình sẽ sáng hơn', 'Pin sạc nhanh hơn'], 1, 'Nắng nóng làm linh kiện quá nhiệt, giảm tuổi thọ máy.', $D);
        $this->quiz($L, 'Nên vệ sinh bàn phím bằng gì?', ['Khăn ẩm vắt khô hoặc chổi mềm', 'Nước đổ trực tiếp', 'Xà phòng bột', 'Máy sấy nóng'], 0, 'Khăn ẩm vắt khô hoặc chổi mềm làm sạch mà không gây chập điện.', $D);
        $this->quiz($L, 'Khi sấm sét lớn, nên làm gì với máy tính?', ['Rút dây mạng và dây điện để phòng sét đánh', 'Mở thêm nhiều chương trình', 'Cắm sạc đầy pin', 'Để máy chạy bình thường'], 0, 'Sét có thể lan theo đường điện, mạng làm cháy máy; nên rút dây.', $D);
        $this->quiz($L, 'Thói quen nào giúp pin laptop bền lâu?', ['Vừa sạc vừa chơi game nặng liên tục', 'Sạc đầy rồi dùng, tránh để pin cạn kiệt', 'Để pin cạn hẳn nhiều lần', 'Cắm sạc cả tuần không rút'], 1, 'Tránh để pin cạn kiệt và hạn chế vừa sạc vừa tải nặng giúp pin bền.', $D);
        $this->quiz($L, 'Vì sao nên tắt hẳn máy tính khi không dùng lâu ngày?', ['Để máy nghỉ, tiết kiệm điện', 'Để máy chạy nhanh hơn lúc mở', 'Để chuột nghỉ ngơi', 'Để màn hình sáng hơn'], 0, 'Tắt máy khi không dùng giúp tiết kiệm điện và tăng tuổi thọ linh kiện.', $D);
        $this->matching($L, 'Nối mỗi thói quen với tác hại của nó.', [['Để cốc nước cạnh bàn phím', 'Nước đổ gây chập mạch'], ['Bịt kín khe tản nhiệt', 'Máy quá nhiệt, chạy chậm'], ['Rút USB đột ngột khi đang chép', 'Mất dữ liệu, hỏng USB'], ['Gập màn hình laptop quá mạnh', 'Hỏng bản lề, vỡ màn hình']], 'Thói quen xấu gây hỏng hóc tốn kém.', $D);
        $this->matching($L, 'Nối mỗi bộ phận với cách vệ sinh đúng.', [['Màn hình', 'Lau bằng khăn mềm khô'], ['Bàn phím', 'Dốc ngược gõ nhẹ cho bụi rơi'], ['Thân máy', 'Lau khăn ẩm vắt khô'], ['Khe tản nhiệt', 'Thổi bụi bằng bóng hơi']], 'Vệ sinh đúng cách giữ máy sạch mà không gây hỏng.', $D);
        $this->matching($L, 'Nối mỗi tình huống với cách xử lý đúng.', [['Máy nóng bất thường', 'Tắt bớt chương trình, kê máy thoáng'], ['Nước đổ vào bàn phím', 'Tắt máy ngay, dốc ngược cho ráo'], ['Mất điện đột ngột', 'Chờ có điện ổn định rồi mở lại'], ['Chuột không di chuyển', 'Kiểm tra pin hoặc dây cắm']], 'Xử lý đúng tình huống tránh hỏng hóc nặng thêm.', $D);
        $this->matching($L, 'Nối mỗi thiết bị bảo vệ với công dụng.', [['Ổn áp', 'Giữ điện áp ổn định'], ['Túi chống sốc', 'Bảo vệ laptop khi mang đi'], ['Miếng dán màn hình', 'Chống xước màn hình'], ['Đế tản nhiệt', 'Giúp laptop mát hơn']], 'Phụ kiện bảo vệ giúp thiết bị bền lâu hơn.', $D);
        $this->matching($L, 'Nối mỗi nơi đặt máy với đánh giá.', [['Bàn học khô ráo', 'Tốt'], ['Cạnh cửa sổ nắng gắt', 'Xấu'], ['Nơi thoáng mát', 'Tốt'], ['Sàn nhà ẩm ướt', 'Xấu']], 'Đặt máy nơi khô ráo, thoáng mát là tốt nhất.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "NÊN LÀM" hoặc "KHÔNG NÊN LÀM".', [['Tắt máy bằng Start → Shut down', 'NÊN LÀM'], ['Rút USB an toàn trước khi tháo', 'NÊN LÀM'], ['Nhấn nút nguồn để tắt máy đột ngột', 'KHÔNG NÊN LÀM'], ['Để laptop trên chăn khi dùng', 'KHÔNG NÊN LÀM']], 'Tắt máy đúng cách, để máy thoáng giúp máy bền.', $D);
        $this->sortQ($L, 'Kéo mỗi vật vào nhóm "ĐƯỢC ĐỂ GẦN MÁY" hoặc "TRÁNH ĐỂ GẦN MÁY".', [['Sách vở', 'ĐƯỢC ĐỂ GẦN MÁY'], ['Cốc nước có nắp', 'ĐƯỢC ĐỂ GẦN MÁY'], ['Cốc nước không nắp', 'TRÁNH ĐỂ GẦN MÁY'], ['Nam châm mạnh', 'TRÁNH ĐỂ GẦN MÁY']], 'Nước và nam châm mạnh có thể gây hại cho máy tính.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "TIẾT KIỆM ĐIỆN" hoặc "TỐN ĐIỆN".', [['Giảm độ sáng màn hình', 'TIẾT KIỆM ĐIỆN'], ['Tắt máy khi không dùng', 'TIẾT KIỆM ĐIỆN'], ['Để màn hình sáng tối đa', 'TỐN ĐIỆN'], ['Mở máy cả ngày không dùng', 'TỐN ĐIỆN']], 'Giảm độ sáng, tắt máy khi không dùng giúp tiết kiệm điện.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm "MÁY CẦN NGHỈ" hoặc "MÁY BÌNH THƯỜNG".', [['Quạt kêu to, máy nóng ran', 'MÁY CẦN NGHỈ'], ['Chạy chậm bất thường', 'MÁY CẦN NGHỈ'], ['Khởi động nhanh', 'MÁY BÌNH THƯỜNG'], ['Mở phần mềm mượt mà', 'MÁY BÌNH THƯỜNG']], 'Máy nóng, chậm bất thường là dấu hiệu cần cho máy nghỉ.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "BẢO VỆ DỮ LIỆU" hoặc "KHÔNG LIÊN QUAN".', [['Sao lưu bài tập vào USB', 'BẢO VỆ DỮ LIỆU'], ['Lưu bài lên drive', 'BẢO VỆ DỮ LIỆU'], ['Đổi hình nền', 'KHÔNG LIÊN QUAN'], ['Tăng âm lượng', 'KHÔNG LIÊN QUAN']], 'Sao lưu nhiều nơi giúp dữ liệu an toàn khi máy hỏng.', $D);
        $this->fill($L, 'Không nên để máy tính dưới ánh ___ trực tiếp.', [[0, 'nắng']], 'Nắng nóng làm linh kiện quá nhiệt, nhanh hỏng.', $D);
        $this->fill($L, 'Khi sấm sét lớn, nên ___ dây mạng và dây điện của máy tính.', [[0, 'rút']], 'Rút dây phòng sét lan theo đường điện làm cháy máy.', $D);
        $this->fill($L, 'Vệ sinh màn hình bằng khăn ___ khô để tránh xước.', [[0, 'mềm']], 'Khăn mềm khô lau sạch bụi mà không làm xước màn hình.', $D);
        $this->fill($L, 'Không nên bịt kín khe ___ nhiệt của laptop.', [[0, 'tản']], 'Bịt khe tản nhiệt khiến máy quá nhiệt, chạy chậm.', $D);
        $this->fill($L, 'Trước khi rút USB, em nên tháo USB một cách an ___.', [[0, 'toàn']], 'Tháo USB an toàn tránh mất dữ liệu và hỏng USB.', $D);
    }

    // 11. th-phan-mem-tep-thu-muc-lop-7-1 — Phần mềm lớp 7: hệ thống và ứng dụng (1) (lớp 7, dễ)
    private function seedTh11(): void {
        $L = 'th-phan-mem-tep-thu-muc-lop-7-1'; $D = 'de';
        $this->quiz($L, 'Phần mềm nào sau đây là hệ điều hành?', ['Ubuntu', 'Zalo', 'Unikey', 'Cốc Cốc'], 0, 'Ubuntu là hệ điều hành mã nguồn mở miễn phí.', $D);
        $this->quiz($L, 'Muốn gọi video cho bạn bè, em dùng phần mềm nào?', ['Zalo', 'Word', 'Excel', 'Notepad'], 0, 'Zalo là phần mềm nhắn tin, gọi video phổ biến.', $D);
        $this->quiz($L, 'Phần mềm diệt virus thuộc loại nào?', ['Hệ điều hành', 'Phần mềm ứng dụng', 'Trình điều khiển chuột', 'Phần mềm hệ thống'], 1, 'Phần mềm diệt virus phục vụ nhu cầu bảo vệ máy nên là phần mềm ứng dụng.', $D);
        $this->quiz($L, 'Không có phần mềm ứng dụng thì máy tính sẽ ra sao?', ['Vẫn chạy nhưng không làm được việc cụ thể', 'Cháy máy ngay', 'Không mở được nguồn', 'Màn hình vỡ'], 0, 'Thiếu phần mềm ứng dụng, máy vẫn chạy nhưng người dùng khó làm việc cụ thể.', $D);
        $this->quiz($L, 'Phần mềm nào dùng để lướt web?', ['Trình duyệt web', 'Máy tính bỏ túi', 'Đồng hồ', 'Lịch'], 0, 'Trình duyệt web như Chrome, Cốc Cốc dùng để lướt web.', $D);
        $this->matching($L, 'Nối mỗi phần mềm với loại của nó.', [['Ubuntu', 'Hệ điều hành'], ['Zalo', 'Phần mềm ứng dụng'], ['Cốc Cốc', 'Phần mềm ứng dụng'], ['iOS', 'Hệ điều hành']], 'Ubuntu, iOS là hệ điều hành; Zalo, Cốc Cốc là phần mềm ứng dụng.', $D);
        $this->matching($L, 'Nối mỗi công việc với phần mềm phù hợp.', [['Nhắn tin cho bạn', 'Zalo'], ['Xem video bài giảng', 'Trình phát video'], ['Chụp ảnh màn hình', 'Snipping Tool'], ['Nén tệp để gửi', 'WinRAR']], 'Mỗi công việc có phần mềm chuyên dụng riêng.', $D);
        $this->matching($L, 'Nối mỗi loại phần mềm với ví dụ.', [['Hệ điều hành máy tính', 'Windows'], ['Trình duyệt web', 'Chrome'], ['Soạn thảo văn bản', 'Word'], ['Nhắn tin', 'Messenger']], 'Nhận biết loại phần mềm qua ví dụ quen thuộc.', $D);
        $this->matching($L, 'Nối mỗi phần mềm với thiết bị phù hợp.', [['Zalo PC', 'Máy tính'], ['Zalo mobile', 'Điện thoại'], ['Word', 'Máy tính'], ['Game mobile', 'Điện thoại']], 'Một số phần mềm có phiên bản riêng cho từng thiết bị.', $D);
        $this->matching($L, 'Nối mỗi việc với phần mềm hệ thống hay ứng dụng thực hiện.', [['Quản lý tệp trong máy', 'Hệ điều hành'], ['Nhận diện chuột mới cắm', 'Hệ điều hành'], ['Soạn đơn xin phép', 'Phần mềm ứng dụng'], ['Chỉnh sửa ảnh', 'Phần mềm ứng dụng']], 'Hệ điều hành lo việc chung; phần mềm ứng dụng lo việc cụ thể.', $D);
        $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm "HỆ ĐIỀU HÀNH" hoặc "ỨNG DỤNG".', [['Linux', 'HỆ ĐIỀU HÀNH'], ['Android', 'HỆ ĐIỀU HÀNH'], ['Unikey', 'ỨNG DỤNG'], ['WinRAR', 'ỨNG DỤNG']], 'Linux, Android là hệ điều hành; Unikey, WinRAR là phần mềm ứng dụng.', $D);
        $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm "MIỄN PHÍ" hoặc "THƯỜNG TRẢ PHÍ".', [['Unikey', 'MIỄN PHÍ'], ['Cốc Cốc', 'MIỄN PHÍ'], ['Word bản quyền', 'THƯỜNG TRẢ PHÍ'], ['Photoshop', 'THƯỜNG TRẢ PHÍ']], 'Unikey, Cốc Cốc miễn phí; Word bản quyền, Photoshop thường trả phí.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "CẦN MẠNG" hoặc "KHÔNG CẦN MẠNG".', [['Lướt web', 'CẦN MẠNG'], ['Gọi video', 'CẦN MẠNG'], ['Gõ văn bản offline', 'KHÔNG CẦN MẠNG'], ['Vẽ tranh trong Paint', 'KHÔNG CẦN MẠNG']], 'Lướt web, gọi video cần mạng; gõ văn bản, vẽ tranh làm offline được.', $D);
        $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm "HỌC TẬP" hoặc "LIÊN LẠC".', [['Word', 'HỌC TẬP'], ['Từ điển', 'HỌC TẬP'], ['Zalo', 'LIÊN LẠC'], ['Messenger', 'LIÊN LẠC']], 'Word, từ điển phục vụ học tập; Zalo, Messenger phục vụ liên lạc.', $D);
        $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm "CỦA VIỆT NAM" hoặc "CỦA NƯỚC NGOÀI".', [['Cốc Cốc', 'CỦA VIỆT NAM'], ['Zalo', 'CỦA VIỆT NAM'], ['Chrome', 'CỦA NƯỚC NGOÀI'], ['Word', 'CỦA NƯỚC NGOÀI']], 'Cốc Cốc, Zalo do người Việt phát triển.', $D);
        $this->fill($L, 'Ubuntu là một hệ điều hành ___ phí, mã nguồn mở.', [[0, 'miễn']], 'Ubuntu miễn phí và ai cũng có thể tải về dùng.', $D);
        $this->fill($L, 'Phần mềm ___ giúp nén tệp để gửi đi nhẹ hơn.', [[0, 'WinRAR']], 'WinRAR nén nhiều tệp thành một tệp gọn nhẹ.', $D);
        $this->fill($L, 'Muốn nhắn tin, gọi video miễn phí, em có thể dùng ___.', [[0, 'Zalo']], 'Zalo là ứng dụng nhắn tin, gọi video phổ biến ở Việt Nam.', $D);
        $this->fill($L, 'Phần mềm diệt ___ giúp bảo vệ máy khỏi mã độc.', [[0, 'virus']], 'Phần mềm diệt virus quét và loại bỏ mã độc trong máy.', $D);
        $this->fill($L, 'Hệ điều hành là phần mềm ___ nhất, quản lý toàn bộ máy tính.', [[0, 'quan trọng']], 'Không có hệ điều hành, máy tính không thể hoạt động.', $D);
    }

    // 12. th-phan-mem-tep-thu-muc-lop-7-2 — Tệp và thư mục lớp 7: phần mở rộng (2) (lớp 7, dễ)
    private function seedTh12(): void {
        $L = 'th-phan-mem-tep-thu-muc-lop-7-2'; $D = 'de';
        $this->quiz($L, 'Tệp âm thanh thường có phần mở rộng nào?', ['.mp3', '.docx', '.jpg', '.pptx'], 0, 'Tệp nhạc thường có đuôi .mp3.', $D);
        $this->quiz($L, 'Tệp video thường có phần mở rộng nào?', ['.txt', '.mp4', '.xlsx', '.png'], 1, 'Tệp video thường có đuôi .mp4.', $D);
        $this->quiz($L, 'Trong tên tệp "bai-tap.docx", phần mở rộng là gì?', ['bai-tap', '.docx', 'bai', 'tập.docx'], 1, 'Phần mở rộng là .docx, nằm sau dấu chấm.', $D);
        $this->quiz($L, 'Tệp nén thường có phần mở rộng nào?', ['.zip', '.jpg', '.mp3', '.docx'], 0, 'Tệp nén thường có đuôi .zip hoặc .rar.', $D);
        $this->quiz($L, 'Vì sao không nên đổi bừa phần mở rộng của tệp?', ['Tệp có thể không mở được nữa', 'Tệp sẽ đẹp hơn', 'Tệp chạy nhanh hơn', 'Tệp nhẹ hơn'], 0, 'Đổi sai đuôi khiến máy không biết dùng phần mềm nào để mở tệp.', $D);
        $this->matching($L, 'Nối mỗi phần mở rộng với loại tệp.', [['.mp3', 'Âm thanh'], ['.mp4', 'Video'], ['.zip', 'Tệp nén'], ['.exe', 'Phần mềm cài đặt']], 'Mỗi đuôi tệp cho biết một loại nội dung khác nhau.', $D);
        $this->matching($L, 'Nối mỗi tệp với cách mở đúng.', [['Phim.mp4', 'Trình phát video'], ['Nhac.mp3', 'Trình phát nhạc'], ['Tailieu.zip', 'Giải nén trước khi xem'], ['Game.exe', 'Chạy để cài đặt']], 'Mở tệp đúng cách giúp xem được nội dung.', $D);
        $this->matching($L, 'Nối mỗi đuôi ảnh với đặc điểm.', [['.jpg', 'Ảnh chụp, dung lượng nhỏ'], ['.png', 'Ảnh nền trong suốt được'], ['.gif', 'Ảnh động'], ['.bmp', 'Ảnh dung lượng lớn']], 'Mỗi định dạng ảnh có ưu điểm riêng.', $D);
        $this->matching($L, 'Nối mỗi đuôi văn bản với phần mềm tạo ra.', [['.docx', 'Word'], ['.txt', 'Notepad'], ['.pdf', 'Nhiều phần mềm'], ['.pptx', 'PowerPoint']], 'Đuôi tệp gợi ý phần mềm đã tạo ra nó.', $D);
        $this->matching($L, 'Nối mỗi việc với đuôi tệp sẽ tạo ra.', [['Chụp ảnh màn hình', '.png'], ['Thu âm giọng nói', '.mp3'], ['Quay video bài giảng', '.mp4'], ['Soạn văn bản', '.docx']], 'Mỗi hoạt động tạo ra tệp có đuôi tương ứng.', $D);
        $this->sortQ($L, 'Kéo mỗi đuôi tệp vào nhóm "NGHE – XEM" hoặc "ĐỌC – VIẾT".', [['.mp3', 'NGHE – XEM'], ['.mp4', 'NGHE – XEM'], ['.docx', 'ĐỌC – VIẾT'], ['.pdf', 'ĐỌC – VIẾT']], 'Nhạc, video để nghe xem; văn bản, PDF để đọc viết.', $D);
        $this->sortQ($L, 'Kéo mỗi đuôi tệp vào nhóm "ẢNH" hoặc "KHÔNG PHẢI ẢNH".', [['.png', 'ẢNH'], ['.gif', 'ẢNH'], ['.mp3', 'KHÔNG PHẢI ẢNH'], ['.xlsx', 'KHÔNG PHẢI ẢNH']], '.png, .gif là ảnh; .mp3 là nhạc, .xlsx là bảng tính.', $D);
        $this->sortQ($L, 'Kéo mỗi đuôi tệp vào nhóm "DUNG LƯỢNG NHỎ" hoặc "DUNG LƯỢNG LỚN".', [['.txt', 'DUNG LƯỢNG NHỎ'], ['.jpg', 'DUNG LƯỢNG NHỎ'], ['.mp4', 'DUNG LƯỢNG LỚN'], ['.zip chứa phim', 'DUNG LƯỢNG LỚN']], 'Văn bản, ảnh thường nhẹ; video thường nặng.', $D);
        $this->sortQ($L, 'Kéo mỗi đuôi tệp vào nhóm "MỞ TRÊN ĐIỆN THOẠI ĐƯỢC" hoặc "THƯỜNG MỞ TRÊN MÁY TÍNH".', [['.jpg', 'MỞ TRÊN ĐIỆN THOẠI ĐƯỢC'], ['.mp3', 'MỞ TRÊN ĐIỆN THOẠI ĐƯỢC'], ['.exe', 'THƯỜNG MỞ TRÊN MÁY TÍNH'], ['.psd', 'THƯỜNG MỞ TRÊN MÁY TÍNH']], 'Ảnh, nhạc mở được trên điện thoại; .exe, .psd cần máy tính.', $D);
        $this->sortQ($L, 'Kéo mỗi tên tệp vào nhóm "CÓ ĐUÔI RÕ RÀNG" hoặc "THIẾU ĐUÔI".', [['bai-hat.mp3', 'CÓ ĐUÔI RÕ RÀNG'], ['anh-dep.png', 'CÓ ĐUÔI RÕ RÀNG'], ['bai-hat', 'THIẾU ĐUÔI'], ['tai-lieu', 'THIẾU ĐUÔI']], 'Tệp nên có đuôi rõ ràng để máy biết cách mở.', $D);
        $this->fill($L, 'Tệp nhạc thường có đuôi ___.', [[0, '.mp3']], 'Đuôi .mp3 cho biết đó là tệp âm thanh.', $D);
        $this->fill($L, 'Tệp video thường có đuôi ___.', [[0, '.mp4']], 'Đuôi .mp4 cho biết đó là tệp video.', $D);
        $this->fill($L, 'Tệp nén thường có đuôi .zip hoặc ___.', [[0, '.rar']], '.zip và .rar đều là định dạng tệp nén.', $D);
        $this->fill($L, 'Phần mở rộng nằm sau dấu ___ trong tên tệp.', [[0, 'chấm']], 'Dấu chấm ngăn cách tên tệp và phần mở rộng.', $D);
        $this->fill($L, 'Không nên đổi bừa phần mở rộng vì tệp có thể không ___ được nữa.', [[0, 'mở']], 'Đổi sai đuôi khiến máy không nhận ra loại tệp.', $D);
    }

    // 13. th-phan-mem-tep-thu-muc-lop-8-1 — Quản lý tệp lớp 8: thư mục (1) (lớp 8, trung bình)
    private function seedTh13(): void {
        $L = 'th-phan-mem-tep-thu-muc-lop-8-1'; $D = 'trung_binh';
        $this->quiz($L, 'Phím tắt nào dùng để cắt (cut) tệp?', ['Ctrl + C', 'Ctrl + X', 'Ctrl + V', 'Ctrl + Z'], 1, 'Ctrl + X cắt tệp đã chọn để di chuyển đi nơi khác.', $D);
        $this->quiz($L, 'Muốn chọn nhiều tệp rời nhau, em giữ phím nào khi nhấn chuột?', ['Shift', 'Ctrl', 'Alt', 'Tab'], 1, 'Giữ Ctrl rồi nhấn chuột để chọn nhiều tệp rời nhau.', $D);
        $this->quiz($L, 'Muốn xóa hẳn tệp mà không qua thùng rác, em nhấn phím nào?', ['Delete', 'Shift + Delete', 'Ctrl + Delete', 'Alt + Delete'], 1, 'Shift + Delete xóa hẳn tệp, không đưa vào thùng rác.', $D);
        $this->quiz($L, 'Thư mục con là gì?', ['Thư mục nằm trong một thư mục khác', 'Thư mục rỗng', 'Thư mục đã bị xóa', 'Thư mục trên màn hình nền'], 0, 'Thư mục con là thư mục nằm bên trong một thư mục khác.', $D);
        $this->quiz($L, 'Nên sắp xếp thư mục bài tập như thế nào là hợp lý?', ['Mỗi môn một thư mục con, đặt tên rõ ràng', 'Tất cả vào một thư mục chung', 'Đặt tên tệp là a, b, c', 'Lưu hết ra màn hình nền'], 0, 'Chia thư mục theo môn, đặt tên rõ ràng giúp tìm bài nhanh.', $D);
        $this->matching($L, 'Nối mỗi phím tắt với thao tác trên tệp.', [['Ctrl + X', 'Cắt tệp'], ['Ctrl + C', 'Sao chép tệp'], ['Ctrl + V', 'Dán tệp'], ['Shift + Delete', 'Xóa hẳn tệp']], 'Phím tắt giúp quản lý tệp nhanh chóng.', $D);
        $this->matching($L, 'Nối mỗi cách chọn với kết quả.', [['Nhấn Ctrl + A', 'Chọn tất cả'], ['Giữ Ctrl + nhấn chuột', 'Chọn nhiều tệp rời nhau'], ['Giữ Shift + nhấn chuột', 'Chọn nhiều tệp liên tiếp'], ['Kéo khung chuột', 'Chọn vùng tệp']], 'Mỗi cách chọn phù hợp một nhu cầu khác nhau.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với cách làm.', [['Tìm bài tập toán tuần trước', 'Mở thư mục Toán, dùng ô tìm kiếm'], ['Chép bài sang USB', 'Sao chép rồi dán vào USB'], ['Gộp bài các môn vào một chỗ', 'Tạo thư mục Bài tập, thêm thư mục con'], ['Lấy lại tệp xóa nhầm', 'Mở thùng rác, chọn khôi phục']], 'Biết cách làm giúp quản lý tệp hiệu quả.', $D);
        $this->matching($L, 'Nối mỗi lệnh chuột phải với ý nghĩa.', [['New → Folder', 'Tạo thư mục mới'], ['Rename', 'Đổi tên'], ['Properties', 'Xem thông tin chi tiết'], ['Send to', 'Gửi nhanh sang nơi khác']], 'Menu chuột phải chứa nhiều lệnh hữu ích với tệp.', $D);
        $this->matching($L, 'Nối mỗi nơi lưu với đặc điểm.', [['Màn hình nền', 'Tiện nhưng dễ bừa bộn'], ['Ổ D', 'Gọn gàng, an toàn hơn'], ['USB', 'Mang đi được'], ['Thùng rác', 'Chứa tệp chờ xóa hẳn']], 'Chọn nơi lưu phù hợp giúp tệp gọn gàng, an toàn.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "SẮP XẾP HỢP LÝ" hoặc "BỪA BỘN".', [['Chia thư mục theo môn học', 'SẮP XẾP HỢP LÝ'], ['Đặt tên tệp rõ nghĩa', 'SẮP XẾP HỢP LÝ'], ['Lưu tất cả ra màn hình nền', 'BỪA BỘN'], ['Đặt tên tệp là 1, 2, 3', 'BỪA BỘN']], 'Sắp xếp hợp lý giúp tìm tệp nhanh, máy gọn gàng.', $D);
        $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm "LÀM MẤT TỆP" hoặc "AN TOÀN".', [['Shift + Delete bừa bãi', 'LÀM MẤT TỆP'], ['Xóa thư mục chưa kiểm tra', 'LÀM MẤT TỆP'], ['Sao lưu trước khi xóa', 'AN TOÀN'], ['Dùng thùng rác khi xóa nhầm', 'AN TOÀN']], 'Cẩn thận khi xóa giúp tránh mất tệp quan trọng.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "NÊN" hoặc "KHÔNG NÊN" khi dùng USB.', [['Tháo an toàn trước khi rút', 'NÊN'], ['Quét virus cho USB lạ', 'NÊN'], ['Rút đột ngột khi đang chép', 'KHÔNG NÊN'], ['Cắm USB lạ vào máy không quét', 'KHÔNG NÊN']], 'Dùng USB cẩn thận bảo vệ cả dữ liệu và máy tính.', $D);
        $this->sortQ($L, 'Kéo mỗi cách đặt tên thư mục vào nhóm "RÕ RÀNG" hoặc "MƠ HỒ".', [['Bai-tap-Toan', 'RÕ RÀNG'], ['Anh-gia-dinh-2024', 'RÕ RÀNG'], ['Thu-muc-moi', 'MƠ HỒ'], ['123', 'MƠ HỒ']], 'Tên thư mục rõ ràng giúp nhận biết nội dung bên trong.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "TIẾT KIỆM DUNG LƯỢNG" hoặc "TỐN DUNG LƯỢNG".', [['Xóa tệp trùng lặp', 'TIẾT KIỆM DUNG LƯỢNG'], ['Nén tệp ít dùng', 'TIẾT KIỆM DUNG LƯỢNG'], ['Lưu 5 bản sao giống nhau', 'TỐN DUNG LƯỢNG'], ['Để thùng rác đầy ắp', 'TỐN DUNG LƯỢNG']], 'Dọn dẹp thường xuyên giúp ổ đĩa luôn đủ chỗ trống.', $D);
        $this->fill($L, 'Giữ phím ___ rồi nhấn chuột để chọn nhiều tệp rời nhau.', [[0, 'Ctrl']], 'Ctrl + nhấn chuột chọn được nhiều tệp không liên tiếp.', $D);
        $this->fill($L, 'Nhấn ___ + Delete để xóa hẳn tệp không qua thùng rác.', [[0, 'Shift']], 'Shift + Delete xóa vĩnh viễn, cần cẩn thận.', $D);
        $this->fill($L, 'Thư mục nằm trong một thư mục khác gọi là thư mục ___.', [[0, 'con']], 'Thư mục con giúp phân loại chi tiết hơn.', $D);
        $this->fill($L, 'Muốn tạo thư mục mới, em nhấn chuột phải rồi chọn New → ___.', [[0, 'Folder']], 'Lệnh New → Folder tạo thư mục mới tại vị trí hiện tại.', $D);
        $this->fill($L, 'Nên đặt tên tệp rõ ___ để dễ tìm lại sau này.', [[0, 'nghĩa']], 'Tên tệp rõ nghĩa giúp nhận biết nội dung mà không cần mở.', $D);
    }

    // 14. th-phan-mem-tep-thu-muc-lop-8-2 — Phần mềm lớp 8: bản quyền phần mềm (2) (lớp 8, trung bình)
    private function seedTh14(): void {
        $L = 'th-phan-mem-tep-thu-muc-lop-8-2'; $D = 'trung_binh';
        $this->quiz($L, 'Phần mềm nguồn mở (open source) có đặc điểm gì?', ['Được xem và sửa mã nguồn tự do', 'Không ai được dùng', 'Chỉ chạy trên một máy', 'Tự xóa sau 30 ngày'], 0, 'Phần mềm nguồn mở cho phép xem, sửa và chia sẻ mã nguồn tự do.', $D);
        $this->quiz($L, 'Đâu là ví dụ về phần mềm nguồn mở?', ['LibreOffice', 'Phần mềm crack', 'Game lậu', 'Phim lậu'], 0, 'LibreOffice là bộ phần mềm văn phòng nguồn mở miễn phí.', $D);
        $this->quiz($L, 'Vì sao phần mềm crack thường chứa virus?', ['Kẻ bẻ khóa cài mã độc vào để trục lợi', 'Phần mềm crack chạy nhanh hơn', 'Nhà sản xuất tặng kèm', 'Máy tính tự sinh ra'], 0, 'Kẻ phát tán crack thường lồng mã độc để đánh cắp thông tin.', $D);
        $this->quiz($L, 'Khi mua phần mềm bản quyền, người dùng thường được gì?', ['Mã kích hoạt hợp pháp và hỗ trợ cập nhật', 'Quyền chia sẻ cho cả xóm', 'Được sửa mã nguồn tùy ý', 'Miễn phí mãi mãi mọi phiên bản'], 0, 'Bản quyền cho mã kích hoạt hợp pháp, được cập nhật và hỗ trợ.', $D);
        $this->quiz($L, 'Hành vi nào sau đây tôn trọng bản quyền phần mềm?', ['Mua bản quyền hoặc dùng phần mềm miễn phí hợp pháp', 'Tải crack về dùng', 'Chia sẻ key lậu cho bạn bè', 'Bẻ khóa phần mềm trả phí'], 0, 'Dùng bản quyền hoặc phần mềm miễn phí hợp pháp là tôn trọng tác giả.', $D);
        $this->matching($L, 'Nối mỗi loại giấy phép với đặc điểm.', [['Bản quyền thương mại', 'Trả phí, dùng hợp pháp'], ['Miễn phí (freeware)', 'Dùng miễn phí, không sửa mã nguồn'], ['Nguồn mở', 'Miễn phí, được xem sửa mã nguồn'], ['Dùng thử', 'Miễn phí trong thời gian ngắn']], 'Mỗi loại giấy phép có quyền lợi và giới hạn riêng.', $D);
        $this->matching($L, 'Nối mỗi phần mềm với loại giấy phép.', [['LibreOffice', 'Nguồn mở, miễn phí'], ['Unikey', 'Miễn phí'], ['Windows bản quyền', 'Trả phí'], ['Game crack', 'Vi phạm bản quyền']], 'Chọn phần mềm hợp pháp để dùng an toàn.', $D);
        $this->matching($L, 'Nối mỗi hành vi với hậu quả có thể gặp.', [['Dùng phần mềm crack', 'Nhiễm virus, mất dữ liệu'], ['Mua bản quyền', 'Được hỗ trợ, cập nhật'], ['Chia sẻ key lậu', 'Vi phạm pháp luật'], ['Dùng phần mềm nguồn mở', 'An toàn, miễn phí']], 'Hành vi vi phạm bản quyền tiềm ẩn nhiều rủi ro.', $D);
        $this->matching($L, 'Nối mỗi khái niệm với nghĩa của nó.', [['Bản quyền', 'Quyền hợp pháp của tác giả'], ['Giấy phép', 'Điều khoản cho phép sử dụng'], ['Mã nguồn', 'Lệnh lập trình tạo nên phần mềm'], ['Crack', 'Bẻ khóa trái phép']], 'Hiểu khái niệm giúp tôn trọng bản quyền đúng cách.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với lựa chọn hợp pháp.', [['Soạn văn bản miễn phí', 'Dùng LibreOffice'], ['Gõ tiếng Việt', 'Dùng Unikey'], ['Hệ điều hành miễn phí', 'Dùng Ubuntu'], ['Chỉnh ảnh đơn giản', 'Dùng Paint hoặc GIMP']], 'Có nhiều phần mềm miễn phí, hợp pháp thay thế phần mềm trả phí.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "HỢP PHÁP" hoặc "VI PHẠM".', [['Mua phần mềm bản quyền', 'HỢP PHÁP'], ['Dùng phần mềm nguồn mở', 'HỢP PHÁP'], ['Tải crack về dùng', 'VI PHẠM'], ['Bán key lậu', 'VI PHẠM']], 'Mua bản quyền hoặc dùng nguồn mở là hợp pháp.', $D);
        $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm "AN TOÀN" hoặc "RỦI RO CAO".', [['Unikey chính chủ', 'AN TOÀN'], ['LibreOffice', 'AN TOÀN'], ['Crack tải từ web lạ', 'RỦI RO CAO'], ['Keygen không rõ nguồn', 'RỦI RO CAO']], 'Phần mềm chính chủ an toàn; crack, keygen lạ rủi ro nhiễm mã độc.', $D);
        $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm "PHẦN MỀM NGUỒN MỞ" hoặc "PHẦN MỀM ĐỘC QUYỀN".', [['Được xem mã nguồn', 'PHẦN MỀM NGUỒN MỞ'], ['Cộng đồng cùng phát triển', 'PHẦN MỀM NGUỒN MỞ'], ['Mã nguồn bí mật', 'PHẦN MỀM ĐỘC QUYỀN'], ['Công ty sở hữu toàn quyền', 'PHẦN MỀM ĐỘC QUYỀN']], 'Nguồn mở công khai mã nguồn; độc quyền giữ bí mật.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "NÊN KHUYÊN BẠN" hoặc "KHÔNG NÊN".', [['Dùng phần mềm miễn phí hợp pháp', 'NÊN KHUYÊN BẠN'], ['Mua chung bản quyền gia đình', 'NÊN KHUYÊN BẠN'], ['Gửi link crack cho bạn', 'KHÔNG NÊN'], ['Chỉ cách bẻ khóa', 'KHÔNG NÊN']], 'Khuyên bạn dùng phần mềm hợp pháp là việc nên làm.', $D);
        $this->sortQ($L, 'Kéo mỗi lợi ích vào nhóm "CỦA BẢN QUYỀN" hoặc "CỦA CRACK".', [['Được cập nhật thường xuyên', 'CỦA BẢN QUYỀN'], ['Được hỗ trợ kỹ thuật', 'CỦA BẢN QUYỀN'], ['Dễ nhiễm virus', 'CỦA CRACK'], ['Không được cập nhật', 'CỦA CRACK']], 'Bản quyền an toàn, được hỗ trợ; crack nhiều rủi ro.', $D);
        $this->fill($L, 'Dùng phần mềm crack là vi phạm ___ quyền phần mềm.', [[0, 'bản']], 'Bẻ khóa phần mềm xâm phạm quyền hợp pháp của tác giả.', $D);
        $this->fill($L, 'Phần mềm nguồn ___ cho phép xem và sửa mã nguồn tự do.', [[0, 'mở']], 'Nguồn mở khuyến khích cộng đồng cùng phát triển.', $D);
        $this->fill($L, 'LibreOffice là bộ phần mềm văn phòng nguồn mở, ___ phí.', [[0, 'miễn']], 'LibreOffice miễn phí, thay thế tốt cho bộ Office trả phí.', $D);
        $this->fill($L, 'Phần mềm crack thường bị kẻ xấu lồng ___ độc vào.', [[0, 'mã']], 'Mã độc trong crack có thể đánh cắp thông tin cá nhân.', $D);
        $this->fill($L, 'Tôn trọng bản quyền là tôn trọng công sức của ___ giả.', [[0, 'tác']], 'Tác giả bỏ công sức tạo phần mềm xứng đáng được bảo vệ.', $D);
    }

    // 15. th-su-dung-an-toan-lop-8-1 — Sử dụng an toàn lớp 8: mật khẩu mạnh (1) (lớp 8, trung bình)
    private function seedTh15(): void {
        $L = 'th-su-dung-an-toan-lop-8-1'; $D = 'trung_binh';
        $this->quiz($L, 'Mật khẩu mạnh nên bao gồm những gì?', ['Chữ hoa, chữ thường, số và ký tự đặc biệt', 'Chỉ chữ thường', 'Chỉ các số', 'Tên mình viết liền'], 0, 'Mật khẩu mạnh kết hợp nhiều loại ký tự khác nhau.', $D);
        $this->quiz($L, 'Vì sao không nên dùng ngày sinh làm mật khẩu?', ['Dễ bị người quen đoán ra', 'Máy không chấp nhận số', 'Ngày sinh quá dài', 'Ngày sinh thay đổi mỗi năm'], 0, 'Ngày sinh dễ đoán, nhất là với người quen biết em.', $D);
        $this->quiz($L, 'Trình quản lý mật khẩu (password manager) có tác dụng gì?', ['Lưu và tạo mật khẩu mạnh an toàn', 'Đoán mật khẩu của người khác', 'Chia sẻ mật khẩu công khai', 'Xóa mọi mật khẩu'], 0, 'Trình quản lý mật khẩu giúp tạo và lưu mật khẩu mạnh an toàn.', $D);
        $this->quiz($L, 'Khi tạo tài khoản mới, việc đầu tiên về bảo mật nên làm là gì?', ['Đặt mật khẩu mạnh, bật xác thực hai bước', 'Chia sẻ tài khoản cho bạn bè', 'Dùng mật khẩu 123456', 'Bỏ qua mọi cảnh báo'], 0, 'Mật khẩu mạnh và xác thực hai bước bảo vệ tài khoản ngay từ đầu.', $D);
        $this->quiz($L, 'Dấu hiệu nào cho thấy mật khẩu của em có thể đã bị lộ?', ['Có đăng nhập lạ mà em không thực hiện', 'Máy chạy nhanh hơn', 'Màn hình sáng hơn', 'Nhận được email chúc mừng'], 0, 'Đăng nhập lạ là dấu hiệu tài khoản có thể đã bị xâm nhập.', $D);
        $this->matching($L, 'Nối mỗi mật khẩu với đánh giá.', [['P@ssw0rd!', 'Khá mạnh'], ['qwerty', 'Rất yếu'], ['EmYeuMe2024#', 'Khá mạnh'], ['111111', 'Rất yếu']], 'Mật khẩu đủ dài, đủ loại ký tự sẽ mạnh hơn.', $D);
        $this->matching($L, 'Nối mỗi sai lầm với hậu quả.', [['Dùng một mật khẩu mọi nơi', 'Lộ một chỗ, mất tất cả'], ['Cho bạn mượn tài khoản', 'Bạn có thể đổi mật khẩu'], ['Lưu mật khẩu trên trình duyệt máy lạ', 'Người khác đăng nhập được'], ['Không đăng xuất máy chung', 'Người sau dùng tài khoản của em']], 'Sai lầm về mật khẩu dẫn đến mất tài khoản.', $D);
        $this->matching($L, 'Nối mỗi biện pháp với tác dụng.', [['Mật khẩu dài 12 ký tự', 'Khó bị đoán mò'], ['Xác thực hai bước', 'Chặn kẻ trộm dù biết mật khẩu'], ['Đổi mật khẩu định kỳ', 'Giảm rủi ro lộ lâu ngày'], ['Không dùng Wi-Fi lạ cho việc quan trọng', 'Tránh bị nghe lén']], 'Kết hợp nhiều biện pháp giúp tài khoản an toàn tối đa.', $D);
        $this->matching($L, 'Nối mỗi tình huống với việc nên làm.', [['Bạn xin mật khẩu Wi-Fi nhà', 'Hỏi ý kiến bố mẹ trước'], ['Web yêu cầu mật khẩu quá đơn giản', 'Tạo mật khẩu mạnh hơn'], ['Quên mật khẩu email', 'Dùng chức năng khôi phục chính thức'], ['Nghi có người đoán mật khẩu', 'Đổi mật khẩu ngay']], 'Xử lý đúng giúp giữ an toàn cho tài khoản.', $D);
        $this->matching($L, 'Nối mỗi loại ký tự với ví dụ.', [['Chữ hoa', 'A, B, C'], ['Chữ thường', 'a, b, c'], ['Chữ số', '1, 2, 3'], ['Ký tự đặc biệt', '@, #, !']], 'Mật khẩu mạnh nên có đủ 4 loại ký tự.', $D);
        $this->sortQ($L, 'Kéo mỗi mật khẩu vào nhóm "MẠNH" hoặc "YẾU".', [['K#7mQ2!vL9x', 'MẠNH'], ['Tr@ng2024!An', 'MẠNH'], ['abc123', 'YẾU'], ['thanh1990', 'YẾU']], 'Mật khẩu dài, đủ loại ký tự là mạnh; ngắn, dễ đoán là yếu.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "NÊN" hoặc "KHÔNG NÊN".', [['Bật xác thực hai bước', 'NÊN'], ['Đổi mật khẩu khi nghi bị lộ', 'NÊN'], ['Dán mật khẩu lên màn hình', 'KHÔNG NÊN'], ['Dùng tên mình làm mật khẩu', 'KHÔNG NÊN']], 'Thói quen tốt bảo vệ tài khoản mỗi ngày.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "AN TOÀN" hoặc "NGUY HIỂM".', [['Đăng xuất máy tính chung', 'AN TOÀN'], ['Dùng trình quản lý mật khẩu', 'AN TOÀN'], ['Lưu mật khẩu vào ghi chú điện thoại không khóa', 'NGUY HIỂM'], ['Nhập mật khẩu trên web lạ', 'NGUY HIỂM']], 'Cẩn thận nơi nhập và lưu mật khẩu.', $D);
        $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm "DÙNG LÀM MẬT KHẨU ĐƯỢC" hoặc "KHÔNG NÊN".', [['Chuỗi ngẫu nhiên dài', 'DÙNG LÀM MẬT KHẨU ĐƯỢC'], ['Câu dễ nhớ + ký tự đặc biệt', 'DÙNG LÀM MẬT KHẨU ĐƯỢC'], ['Số điện thoại', 'KHÔNG NÊN'], ['Tên trường học', 'KHÔNG NÊN']], 'Thông tin cá nhân dễ đoán, không nên làm mật khẩu.', $D);
        $this->sortQ($L, 'Kéo mỗi hành động vào nhóm "KHI BỊ LỘ MẬT KHẨU" nên làm.', [['Đổi mật khẩu ngay', 'KHI BỊ LỘ MẬT KHẨU'], ['Kiểm tra đăng nhập lạ', 'KHI BỊ LỘ MẬT KHẨU'], ['Báo cho người thân biết', 'KHI BỊ LỘ MẬT KHẨU'], ['Đăng mật khẩu mới lên mạng', 'KHÔNG NÊN']], 'Khi lộ mật khẩu: đổi ngay, kiểm tra và báo người thân.', $D);
        $this->fill($L, 'Mật khẩu mạnh nên có cả chữ hoa, chữ thường, số và ký tự đặc ___.', [[0, 'biệt']], 'Đủ 4 loại ký tự giúp mật khẩu khó bị đoán mò.', $D);
        $this->fill($L, 'Không nên dùng ___ sinh làm mật khẩu vì dễ bị đoán.', [[0, 'ngày']], 'Ngày sinh là thông tin dễ đoán, nhất là với người quen.', $D);
        $this->fill($L, 'Xác thực hai bước yêu cầu thêm một ___ xác nhận ngoài mật khẩu.', [[0, 'mã']], 'Mã xác nhận gửi về điện thoại tăng lớp bảo vệ thứ hai.', $D);
        $this->fill($L, 'Trình quản lý mật khẩu giúp tạo và ___ mật khẩu an toàn.', [[0, 'lưu']], 'Không cần nhớ hết mật khẩu phức tạp nhờ trình quản lý.', $D);
        $this->fill($L, 'Thấy đăng nhập ___ mà mình không thực hiện, hãy đổi mật khẩu ngay.', [[0, 'lạ']], 'Đăng nhập lạ là dấu hiệu tài khoản có thể đã bị xâm nhập.', $D);
    }

    // 16. th-su-dung-an-toan-lop-8-2 — Sử dụng an toàn lớp 8: virus máy tính (2) (lớp 8, trung bình)
    private function seedTh16(): void {
        $L = 'th-su-dung-an-toan-lop-8-2'; $D = 'trung_binh';
        $this->quiz($L, 'Trojan (ngựa thành Troy) là loại mã độc nào?', ['Giả dạng phần mềm tốt để lừa người dùng cài vào', 'Tự nhân bản qua mạng', 'Mã hóa dữ liệu đòi tiền chuộc', 'Hiển thị quảng cáo'], 0, 'Trojan giả dạng phần mềm hữu ích để lừa người dùng.', $D);
        $this->quiz($L, 'Worm (sâu máy tính) lây lan bằng cách nào?', ['Tự nhân bản và lan qua mạng', 'Qua đường ăn uống', 'Qua sóng Wi-Fi yếu', 'Qua màn hình cảm ứng'], 0, 'Worm tự sao chép và lan truyền qua mạng mà không cần người dùng.', $D);
        $this->quiz($L, 'Phần mềm quảng cáo độc hại (adware) gây phiền gì?', ['Hiện quảng cáo liên tục, làm chậm máy', 'Tăng tốc độ mạng', 'Làm màn hình đẹp hơn', 'Tự dọn rác máy tính'], 0, 'Adware hiện quảng cáo ồ ạt gây khó chịu và chậm máy.', $D);
        $this->quiz($L, 'Cách phòng virus hiệu quả nhất là gì?', ['Cài phần mềm diệt virus uy tín, cập nhật thường xuyên', 'Không bao giờ mở máy', 'Xóa hết tệp trong máy', 'Tắt mạng vĩnh viễn'], 0, 'Phần mềm diệt virus cập nhật thường xuyên là lá chắn tốt nhất.', $D);
        $this->quiz($L, 'Khi phần mềm diệt virus báo tệp đáng ngờ, em nên làm gì?', ['Xóa hoặc cách ly tệp theo hướng dẫn', 'Tắt phần mềm diệt virus đi', 'Mở tệp ra xem thử', 'Gửi tệp cho bạn bè'], 0, 'Nghe theo phần mềm diệt virus: cách ly hoặc xóa tệp đáng ngờ.', $D);
        $this->matching($L, 'Nối mỗi loại mã độc với cách gây hại.', [['Virus', 'Gắn vào tệp, lây khi mở tệp'], ['Worm', 'Tự lan qua mạng'], ['Trojan', 'Giả dạng phần mềm tốt'], ['Ransomware', 'Mã hóa dữ liệu đòi tiền']], 'Mỗi loại mã độc có cách tấn công riêng.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu với khả năng nhiễm mã độc.', [['Máy chậm bất thường', 'Có thể nhiễm'], ['Xuất hiện quảng cáo lạ liên tục', 'Có thể nhiễm'], ['Tệp tự đổi tên, biến mất', 'Có thể nhiễm'], ['Máy chạy mượt, ổn định', 'Bình thường']], 'Nhận biết sớm dấu hiệu giúp xử lý kịp thời.', $D);
        $this->matching($L, 'Nối mỗi nguồn lây với cách phòng tránh.', [['USB lạ', 'Quét virus trước khi mở'], ['Email lạ có tệp đính kèm', 'Không mở, xóa đi'], ['Web crack, phim lậu', 'Không tải về'], ['Link lạ trong tin nhắn', 'Không nhấn vào']], 'Tránh nguồn lây là cách phòng virus đơn giản nhất.', $D);
        $this->matching($L, 'Nối mỗi việc với đánh giá phòng virus.', [['Cập nhật phần mềm diệt virus', 'Tốt'], ['Sao lưu dữ liệu quan trọng', 'Tốt'], ['Tắt tường lửa để chơi game', 'Xấu'], ['Mở mọi tệp đính kèm', 'Xấu']], 'Thói quen tốt giúp máy luôn an toàn.', $D);
        $this->matching($L, 'Nối mỗi thuật ngữ với nghĩa của nó.', [['Mã độc', 'Phần mềm gây hại'], ['Tường lửa', 'Chặn truy cập trái phép'], ['Bản vá', 'Sửa lỗi bảo mật'], ['Quét virus', 'Tìm và diệt mã độc']], 'Hiểu thuật ngữ giúp dùng máy tính an toàn hơn.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "PHÒNG VIRUS" hoặc "DỄ NHIỄM VIRUS".', [['Quét USB lạ trước khi mở', 'PHÒNG VIRUS'], ['Cập nhật hệ điều hành', 'PHÒNG VIRUS'], ['Tải crack từ web lạ', 'DỄ NHIỄM VIRUS'], ['Nhấn link trúng thưởng', 'DỄ NHIỄM VIRUS']], 'Thói quen cẩn thận giúp tránh xa virus.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm "NGHI NHIỄM VIRUS" hoặc "BÌNH THƯỜNG".', [['Máy tự mở web lạ', 'NGHI NHIỄM VIRUS'], ['Tệp biến mất bất thường', 'NGHI NHIỄM VIRUS'], ['Mở Word nhanh', 'BÌNH THƯỜNG'], ['Nghe nhạc mượt mà', 'BÌNH THƯỜNG']], 'Máy có biểu hiện lạ có thể đã nhiễm mã độc.', $D);
        $this->sortQ($L, 'Kéo mỗi loại mã độc vào nhóm "TỐNG TIỀN" hoặc "KHÔNG TỐNG TIỀN".', [['Ransomware', 'TỐNG TIỀN'], ['Adware', 'KHÔNG TỐNG TIỀN'], ['Virus thường', 'KHÔNG TỐNG TIỀN'], ['Spyware (gián điệp)', 'KHÔNG TỐNG TIỀN']], 'Ransomware mã hóa dữ liệu rồi đòi tiền chuộc.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "KHI NGHI NHIỄM VIRUS" nên làm.', [['Ngắt mạng ngay', 'KHI NGHI NHIỄM VIRUS'], ['Quét toàn bộ máy', 'KHI NGHI NHIỄM VIRUS'], ['Sao lưu tệp sạch', 'KHI NGHI NHIỄM VIRUS'], ['Tiếp tục mở tệp lạ', 'KHÔNG NÊN']], 'Nghi nhiễm virus: ngắt mạng, quét máy, sao lưu tệp sạch.', $D);
        $this->sortQ($L, 'Kéo mỗi thứ vào nhóm "PHẦN MỀM BẢO VỆ" hoặc "MÃ ĐỘC".', [['Phần mềm diệt virus', 'PHẦN MỀM BẢO VỆ'], ['Tường lửa', 'PHẦN MỀM BẢO VỆ'], ['Trojan', 'MÃ ĐỘC'], ['Worm', 'MÃ ĐỘC']], 'Phần mềm bảo vệ chống lại mã độc.', $D);
        $this->fill($L, 'Mã độc tự nhân bản và lan qua mạng gọi là ___.', [[0, 'worm']], 'Worm (sâu máy tính) lan truyền mà không cần người dùng mở tệp.', $D);
        $this->fill($L, 'Mã độc giả dạng phần mềm tốt để lừa người dùng gọi là ___.', [[0, 'trojan']], 'Trojan (ngựa thành Troy) ẩn mình trong phần mềm có vẻ hữu ích.', $D);
        $this->fill($L, 'Nên cập nhật phần mềm diệt virus ___ xuyên để nhận diện mã độc mới.', [[0, 'thường']], 'Mã độc mới xuất hiện mỗi ngày nên cần cập nhật thường xuyên.', $D);
        $this->fill($L, '___ lửa giúp chặn các truy cập trái phép vào máy tính.', [[0, 'Tường']], 'Tường lửa (firewall) là lớp bảo vệ mạng cho máy tính.', $D);
        $this->fill($L, 'Không mở tệp đính kèm trong email ___ không rõ người gửi.', [[0, 'lạ']], 'Tệp đính kèm email lạ là nguồn lây virus phổ biến.', $D);
    }

    // 17. th-su-dung-an-toan-lop-9-1 — Sử dụng an toàn lớp 9: quyền riêng tư trực tuyến (1) (lớp 9, trung bình)
    private function seedTh17(): void {
        $L = 'th-su-dung-an-toan-lop-9-1'; $D = 'trung_binh';
        $this->quiz($L, 'Vì sao không nên đăng ảnh vé máy bay lên mạng?', ['Lộ mã đặt chỗ, họ tên, hành trình', 'Ảnh vé máy bay xấu', 'Máy bay sẽ bị trễ', 'Vé sẽ bị rách'], 0, 'Vé máy bay chứa mã đặt chỗ và thông tin cá nhân, kẻ xấu có thể lợi dụng.', $D);
        $this->quiz($L, 'Chế độ "bạn bè" khi đăng bài trên mạng xã hội có nghĩa gì?', ['Chỉ bạn bè mới xem được bài', 'Cả thế giới xem được', 'Không ai xem được', 'Chỉ mình xem được'], 0, 'Chế độ bạn bè giới hạn người xem bài đăng.', $D);
        $this->quiz($L, 'Khi bị người lạ nhắn tin làm quen và xin ảnh, em nên làm gì?', ['Không trả lời, chặn và báo người lớn', 'Gửi ảnh ngay cho lịch sự', 'Hẹn gặp mặt trực tiếp', 'Cho số điện thoại'], 0, 'Người lạ xin ảnh có thể có ý đồ xấu; cần chặn và báo người lớn.', $D);
        $this->quiz($L, 'Tin giả (fake news) có đặc điểm gì?', ['Giật tít sốc, không nguồn tin cậy', 'Luôn có ảnh đẹp', 'Được chia sẻ nhiều', 'Viết rất dài'], 0, 'Tin giả thường giật tít, thiếu nguồn kiểm chứng.', $D);
        $this->quiz($L, 'Trước khi chia sẻ một tin tức, em nên làm gì?', ['Kiểm tra nguồn tin có đáng tin không', 'Chia sẻ ngay cho nóng', 'Thêm thắt cho hấp dẫn', 'Xóa nguồn tin gốc'], 0, 'Kiểm chứng nguồn tin giúp không lan truyền tin giả.', $D);
        $this->matching($L, 'Nối mỗi việc với mức độ riêng tư nên đặt.', [['Ảnh đại diện', 'Công khai được'], ['Số điện thoại', 'Chỉ mình tôi'], ['Bài đăng vui', 'Bạn bè'], ['Nhật ký cá nhân', 'Chỉ mình tôi']], 'Đặt chế độ riêng tư phù hợp từng loại thông tin.', $D);
        $this->matching($L, 'Nối mỗi hành vi với hậu quả.', [['Đăng ảnh nhạy cảm', 'Bị phát tán, khó gỡ'], ['Chia sẻ tin giả', 'Gây hoang mang, có thể bị phạt'], ['Bình luận ác ý', 'Làm tổn thương người khác'], ['Lộ địa chỉ nhà', 'Nguy hiểm an toàn cá nhân']], 'Hành vi thiếu suy nghĩ trên mạng gây hậu quả thật.', $D);
        $this->matching($L, 'Nối mỗi tình huống với cách xử lý.', [['Thấy tin giật gân', 'Kiểm chứng trước khi tin'], ['Bạn đăng ảnh mình không xin phép', 'Nhắn bạn gỡ xuống'], ['Bị tag vào bài không thích', 'Gỡ tag, đặt lại quyền'], ['Người lạ xin kết bạn', 'Không chấp nhận bừa']], 'Chủ động bảo vệ hình ảnh và thông tin của mình.', $D);
        $this->matching($L, 'Nối mỗi quyền với ý nghĩa trên mạng.', [['Quyền riêng tư', 'Được giữ kín thông tin cá nhân'], ['Quyền được quên', 'Được yêu cầu gỡ thông tin cũ'], ['Quyền tác giả', 'Ảnh, bài viết của mình được bảo vệ'], ['Quyền phản hồi', 'Được lên tiếng khi bị xâm phạm']], 'Người dùng mạng có các quyền cần được tôn trọng.', $D);
        $this->matching($L, 'Nối mỗi dấu hiệu với đánh giá tin tức.', [['Có nguồn báo chính thống', 'Đáng tin'], ['Giật tít sốc, chữ in hoa', 'Nghi là tin giả'], ['Không tác giả, không ngày đăng', 'Nghi là tin giả'], ['Nhiều báo lớn cùng đưa', 'Đáng tin']], 'Nhận biết tin giả qua các dấu hiệu.', $D);
        $this->sortQ($L, 'Kéo mỗi thứ vào nhóm "NÊN ĐĂNG" hoặc "KHÔNG NÊN ĐĂNG".', [['Ảnh chuyến đi chơi', 'NÊN ĐĂNG'], ['Thành tích học tập', 'NÊN ĐĂNG'], ['Ảnh giấy tờ tùy thân', 'KHÔNG NÊN ĐĂNG'], ['Địa chỉ nhà riêng', 'KHÔNG NÊN ĐĂNG']], 'Chia sẻ niềm vui được; giấy tờ, địa chỉ tuyệt đối không.', $D);
        $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm "TÔN TRỌNG NGƯỜI KHÁC" hoặc "XÂM PHẠM".', [['Xin phép trước khi đăng ảnh bạn', 'TÔN TRỌNG NGƯỜI KHÁC'], ['Gỡ ảnh khi bạn yêu cầu', 'TÔN TRỌNG NGƯỜI KHÁC'], ['Đăng ảnh dìm hàng bạn', 'XÂM PHẠM'], ['Đọc trộm tin nhắn người khác', 'XÂM PHẠM']], 'Tôn trọng quyền riêng tư của người khác như của mình.', $D);
        $this->sortQ($L, 'Kéo mỗi tin vào nhóm "NÊN TIN" hoặc "NÊN NGHI NGỜ".', [['Báo chính thống đăng', 'NÊN TIN'], ['Thầy cô chia sẻ', 'NÊN TIN'], ['Tin nhắn nặc danh giật gân', 'NÊN NGHI NGỜ'], ['Trang lạ không nguồn', 'NÊN NGHI NGỜ']], 'Tin từ nguồn uy tín đáng tin hơn tin nặc danh.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "BẢO VỆ DẤU CHÂN SỐ" hoặc "LÀM XẤU DẤU CHÂN SỐ".', [['Đăng nội dung tích cực', 'BẢO VỆ DẤU CHÂN SỐ'], ['Suy nghĩ trước khi đăng', 'BẢO VỆ DẤU CHÂN SỐ'], ['Chửi bới trên mạng', 'LÀM XẤU DẤU CHÂN SỐ'], ['Chia sẻ tin chưa kiểm chứng', 'LÀM XẤU DẤU CHÂN SỐ']], 'Dấu chân số đẹp giúp tương lai tốt đẹp hơn.', $D);
        $this->sortQ($L, 'Kéo mỗi cài đặt vào nhóm "NÊN BẬT" hoặc "NÊN TẮT".', [['Xét duyệt tag trước khi hiện', 'NÊN BẬT'], ['Ẩn số điện thoại', 'NÊN BẬT'], ['Công khai mọi bài viết', 'NÊN TẮT'], ['Cho người lạ nhắn tin tự do', 'NÊN TẮT']], 'Cài đặt riêng tư chặt chẽ bảo vệ tài khoản tốt hơn.', $D);
        $this->fill($L, 'Mọi thứ đăng lên mạng tạo thành "dấu chân ___" của em.', [[0, 'số']], 'Dấu chân số theo em suốt đời, hãy giữ nó đẹp.', $D);
        $this->fill($L, 'Đăng ảnh có mặt bạn bè cần xin ___ trước.', [[0, 'phép']], 'Xin phép thể hiện sự tôn trọng quyền riêng tư của bạn.', $D);
        $this->fill($L, 'Tin ___ là tin sai sự thật lan truyền trên mạng.', [[0, 'giả']], 'Tin giả gây hoang mang, cần kiểm chứng trước khi tin.', $D);
        $this->fill($L, 'Không đăng ảnh giấy tờ ___ thân lên mạng.', [[0, 'tùy']], 'Giấy tờ tùy thân chứa thông tin nhạy cảm, kẻ xấu có thể lợi dụng.', $D);
        $this->fill($L, 'Hãy kiểm tra cài đặt riêng ___ của tài khoản thường xuyên.', [[0, 'tư']], 'Cài đặt riêng tư giúp kiểm soát ai xem được thông tin của em.', $D);
    }

    // 18. th-su-dung-an-toan-lop-9-2 — Sử dụng an toàn lớp 9: ứng phó sự cố mạng (2) (lớp 9, khó)
    private function seedTh18(): void {
        $L = 'th-su-dung-an-toan-lop-9-2'; $D = 'kho';
        $this->quiz($L, 'Khi phát hiện tài khoản ngân hàng của bố mẹ có giao dịch lạ, em nên làm gì?', ['Báo ngay cho bố mẹ để khóa thẻ', 'Giấu đi không nói', 'Tự chuyển tiền đi nơi khác', 'Đăng lên mạng hỏi ý kiến'], 0, 'Báo ngay cho bố mẹ để kịp khóa thẻ, trình báo ngân hàng.', $D);
        $this->quiz($L, 'Bị kẻ xấu đe dọa tung ảnh riêng tư để tống tiền, em nên làm gì?', ['Giữ bình tĩnh, không chuyển tiền, báo người lớn và công an', 'Chuyển tiền ngay cho yên', 'Xóa hết bằng chứng', 'Tự đi gặp kẻ xấu'], 0, 'Không nhượng bộ kẻ tống tiền; giữ bằng chứng, báo người lớn và công an.', $D);
        $this->quiz($L, 'Đường dây nóng bảo vệ trẻ em trên môi trường mạng ở Việt Nam là số nào?', ['111', '113', '114', '115'], 0, 'Tổng đài 111 tiếp nhận hỗ trợ trẻ em, kể cả trên môi trường mạng.', $D);
        $this->quiz($L, 'Khi mua hàng online bị lừa không nhận được hàng, em cần giữ lại gì?', ['Tin nhắn, hóa đơn, bằng chứng giao dịch', 'Xóa hết cho đỡ tức', 'Chặn shop là xong', 'Không cần giữ gì'], 0, 'Bằng chứng giao dịch cần thiết để khiếu nại, trình báo.', $D);
        $this->quiz($L, 'Vì sao không nên tự ý "hack lại" kẻ đã hack mình?', ['Vi phạm pháp luật, có thể bị xử lý', 'Hack lại rất dễ', 'Kẻ xấu sẽ sợ', 'Công an khuyến khích'], 0, 'Tự ý xâm nhập hệ thống người khác là vi phạm pháp luật.', $D);
        $this->matching($L, 'Nối mỗi sự cố với nơi cần báo.', [['Bị lừa tiền online', 'Công an'], ['Bị bắt nạt mạng nghiêm trọng', 'Thầy cô, bố mẹ, tổng đài 111'], ['Tài khoản ngân hàng bất thường', 'Ngân hàng'], ['Thấy web lừa đảo', 'Báo cáo cho nhà mạng, công an']], 'Báo đúng nơi giúp xử lý sự cố nhanh và hiệu quả.', $D);
        $this->matching($L, 'Nối mỗi bước với thứ tự khi bị hack email.', [['Đổi mật khẩu email', 'Bước 1'], ['Kiểm tra email khôi phục', 'Bước 2'], ['Đăng xuất thiết bị lạ', 'Bước 3'], ['Báo bạn bè cẩn thận tin nhắn lạ', 'Bước 4']], 'Xử lý theo trình tự giúp lấy lại tài khoản an toàn.', $D);
        $this->matching($L, 'Nối mỗi loại lừa đảo với dấu hiệu.', [['Giả danh công an', 'Đe dọa, yêu cầu chuyển tiền'], ['Việc nhẹ lương cao', 'Yêu cầu đóng phí trước'], ['Trúng thưởng', 'Yêu cầu nộp thuế nhận thưởng'], ['Giả danh người thân', 'Nhắn tin vay tiền gấp']], 'Nhận diện chiêu lừa giúp tránh trở thành nạn nhân.', $D);
        $this->matching($L, 'Nối mỗi bằng chứng với cách lưu giữ.', [['Tin nhắn đe dọa', 'Chụp màn hình, không xóa'], ['Lịch sử chuyển tiền', 'Lưu sao kê, biên lai'], ['Link web lừa đảo', 'Copy link, chụp màn hình'], ['Cuộc gọi đe dọa', 'Ghi âm nếu có thể']], 'Bằng chứng đầy đủ giúp cơ quan chức năng xử lý.', $D);
        $this->matching($L, 'Nối mỗi hành động với đánh giá khi bị tống tiền mạng.', [['Không chuyển tiền', 'Đúng'], ['Giữ lại bằng chứng', 'Đúng'], ['Báo người lớn', 'Đúng'], ['Tự đi gặp kẻ xấu', 'Sai – rất nguy hiểm']], 'Bình tĩnh, giữ bằng chứng và nhờ người lớn giúp đỡ.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "NÊN" hoặc "KHÔNG NÊN" khi bị lừa tiền.', [['Giữ lại mọi bằng chứng', 'NÊN'], ['Báo công an', 'NÊN'], ['Xóa hết tin nhắn', 'KHÔNG NÊN'], ['Tự đi đòi tiền kẻ lừa', 'KHÔNG NÊN']], 'Giữ bằng chứng và báo công an là cách xử lý đúng.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "ĐÚNG" hoặc "SAI" khi bị đe dọa trên mạng.', [['Báo ngay cho bố mẹ', 'ĐÚNG'], ['Gọi tổng đài 111', 'ĐÚNG'], ['Nghe lời kẻ xấu chuyển tiền', 'SAI'], ['Giấu kín không nói với ai', 'SAI']], 'Khi bị đe dọa, hãy nói với người lớn tin cậy ngay.', $D);
        $this->sortQ($L, 'Kéo mỗi số điện thoại vào nhóm "KHẨN CẤP" hoặc "KHÔNG PHẢI".', [['111 – bảo vệ trẻ em', 'KHẨN CẤP'], ['113 – công an', 'KHẨN CẤP'], ['Số điện thoại của shop quần áo', 'KHÔNG PHẢI'], ['Số tổng đài game', 'KHÔNG PHẢI']], 'Nhớ số khẩn cấp để gọi khi gặp nguy hiểm thật sự.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm "LỪA ĐẢO" hoặc "BÌNH THƯỜNG".', [['Việc nhẹ lương cao, đóng phí trước', 'LỪA ĐẢO'], ['Trúng thưởng yêu cầu nộp tiền', 'LỪA ĐẢO'], ['Shop có địa chỉ rõ ràng, đánh giá tốt', 'BÌNH THƯỜNG'], ['Người thân gọi video trực tiếp', 'BÌNH THƯỜNG']], 'Chiêu "việc nhẹ lương cao", "trúng thưởng" thường là lừa đảo.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "GIỮ AN TOÀN" hoặc "GÂY NGUY HIỂM" sau sự cố.', [['Đổi mọi mật khẩu liên quan', 'GIỮ AN TOÀN'], ['Bật xác thực hai bước', 'GIỮ AN TOÀN'], ['Dùng lại mật khẩu cũ', 'GÂY NGUY HIỂM'], ['Bỏ qua cảnh báo bảo mật', 'GÂY NGUY HIỂM']], 'Sau sự cố cần tăng cường bảo mật ngay.', $D);
        $this->fill($L, 'Bị hack tài khoản, hãy đổi ___ khẩu ngay lập tức.', [[0, 'mật']], 'Đổi mật khẩu nhanh giúp chặn kẻ xấu tiếp tục truy cập.', $D);
        $this->fill($L, 'Bị lừa tiền online, cần giữ lại bằng ___ để trình báo.', [[0, 'chứng']], 'Bằng chứng giao dịch giúp công an điều tra.', $D);
        $this->fill($L, 'Trẻ em bị hại trên mạng có thể gọi tổng đài ___.', [[0, '111']], 'Tổng đài 111 hỗ trợ, bảo vệ trẻ em miễn phí.', $D);
        $this->fill($L, 'Không bao giờ tự ý đi gặp mặt người ___ quen trên mạng.', [[0, 'lạ']], 'Gặp người lạ quen trên mạng rất nguy hiểm.', $D);
        $this->fill($L, 'Khi bị đe dọa trên mạng, hãy bình tĩnh và báo ngay cho người ___.', [[0, 'lớn']], 'Người lớn tin cậy sẽ giúp em xử lý đúng cách.', $D);
    }

    // 19. tin-hoc-thpt-10-lop-10-1 — Định dạng văn bản và tạo mục lục tự động (lớp 10, trung bình)
    private function seedTh19(): void {
        $L = 'tin-hoc-thpt-10-lop-10-1'; $D = 'trung_binh';
        $this->quiz($L, 'Để tạo tiêu đề có đánh số thứ bậc (Heading 1, Heading 2...), ta dùng nhóm lệnh nào?', ['Styles trong thẻ Home', 'Font trong thẻ Insert', 'Page Layout', 'View'], 0, 'Nhóm Styles trong thẻ Home chứa các kiểu Heading để tạo tiêu đề phân cấp.', $D);
        $this->quiz($L, 'Phím tắt nào dùng để gạch chân văn bản trong Word?', ['Ctrl + B', 'Ctrl + I', 'Ctrl + U', 'Ctrl + S'], 2, 'Ctrl + U dùng để gạch chân (Underline) văn bản.', $D);
        $this->quiz($L, 'Muốn chèn số trang vào văn bản, ta vào đâu?', ['Insert → Page Number', 'Home → Font', 'View → Zoom', 'File → Save'], 0, 'Lệnh Insert → Page Number dùng để chèn số trang.', $D);
        $this->quiz($L, 'Để ngắt sang trang mới, ta nhấn tổ hợp phím nào?', ['Ctrl + Enter', 'Shift + Enter', 'Alt + Enter', 'Ctrl + Shift'], 0, 'Ctrl + Enter chèn ngắt trang, đưa con trỏ sang trang mới.', $D);
        $this->quiz($L, 'Mục lục tự động dựa vào đâu để liệt kê các tiêu đề?', ['Các đoạn áp dụng Heading Styles', 'Màu sắc của chữ', 'Độ dài văn bản', 'Tên tệp'], 0, 'Word dựa vào các đoạn định dạng Heading Styles để tạo mục lục tự động.', $D);
        $this->matching($L, 'Nối mỗi phím tắt với chức năng trong Word.', [['Ctrl + I', 'In nghiêng'], ['Ctrl + U', 'Gạch chân'], ['Ctrl + E', 'Căn giữa'], ['Ctrl + L', 'Căn trái']], 'Nhóm phím tắt định dạng giúp soạn thảo nhanh.', $D);
        $this->matching($L, 'Nối mỗi thẻ với nhóm lệnh định dạng của nó.', [['Home', 'Font, Paragraph, Styles'], ['Insert', 'Page Number, Table, Picture'], ['References', 'Table of Contents'], ['Layout', 'Margins, Orientation']], 'Mỗi thẻ trong Word chứa một nhóm lệnh riêng.', $D);
        $this->matching($L, 'Nối mỗi bước với thứ tự khi tạo mục lục tự động.', [['Áp dụng Heading Styles', 'Bước 1'], ['Đặt con trỏ nơi cần chèn', 'Bước 2'], ['References → Table of Contents', 'Bước 3'], ['Update Table khi sửa nội dung', 'Bước 4']], 'Tạo mục lục tự động theo đúng trình tự 4 bước.', $D);
        $this->matching($L, 'Nối mỗi kiểu căn lề với phím tắt.', [['Căn giữa', 'Ctrl + E'], ['Căn trái', 'Ctrl + L'], ['Căn phải', 'Ctrl + R'], ['Căn đều hai bên', 'Ctrl + J']], 'Bốn kiểu căn lề có bốn phím tắt tương ứng.', $D);
        $this->matching($L, 'Nối mỗi đối tượng với nơi chèn nó.', [['Số trang', 'Đầu trang hoặc chân trang'], ['Mục lục', 'Đầu văn bản'], ['Tiêu đề bài', 'Đầu trang đầu tiên'], ['Chữ ký', 'Cuối văn bản']], 'Đặt đối tượng đúng vị trí giúp văn bản chuyên nghiệp.', $D);
        $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm "ĐỊNH DẠNG KÍ TỰ" hoặc "ĐỊNH DẠNG ĐOẠN VĂN".', [['In đậm', 'ĐỊNH DẠNG KÍ TỰ'], ['Đổi màu chữ', 'ĐỊNH DẠNG KÍ TỰ'], ['Căn giữa', 'ĐỊNH DẠNG ĐOẠN VĂN'], ['Giãn dòng', 'ĐỊNH DẠNG ĐOẠN VĂN']], 'Định dạng kí tự tác động chữ; định dạng đoạn văn tác động cả đoạn.', $D);
        $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm "LÀM TRƯỚC" hoặc "LÀM SAU" khi tạo mục lục.', [['Áp dụng Heading cho tiêu đề', 'LÀM TRƯỚC'], ['Chèn mục lục tự động', 'LÀM SAU'], ['Cập nhật mục lục', 'LÀM SAU'], ['In văn bản', 'LÀM SAU']], 'Phải áp dụng Heading trước, rồi mới chèn mục lục tự động.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['Mục lục tự động cập nhật khi sửa tiêu đề', 'ĐÚNG'], ['Heading Styles giúp tạo mục lục', 'ĐÚNG'], ['Mục lục tự động gõ tay từng dòng', 'SAI'], ['Xóa Heading không ảnh hưởng mục lục', 'SAI']], 'Mục lục tự động liên kết với Heading Styles.', $D);
        $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm "THẺ HOME" hoặc "THẺ INSERT".', [['In đậm chữ', 'THẺ HOME'], ['Căn lề đoạn văn', 'THẺ HOME'], ['Chèn bảng', 'THẺ INSERT'], ['Chèn số trang', 'THẺ INSERT']], 'Thẻ Home chứa định dạng; thẻ Insert chứa chèn đối tượng.', $D);
        $this->sortQ($L, 'Kéo mỗi phím tắt vào nhóm "ĐỊNH DẠNG" hoặc "KHÁC".', [['Ctrl + B', 'ĐỊNH DẠNG'], ['Ctrl + I', 'ĐỊNH DẠNG'], ['Ctrl + P', 'KHÁC'], ['Ctrl + S', 'KHÁC']], 'Ctrl + B/I/U định dạng chữ; Ctrl + P/S dùng để in, lưu.', $D);
        $this->fill($L, 'Phím tắt Ctrl + ___ dùng để in nghiêng văn bản.', [[0, 'I']], 'Ctrl + I (Italic) in nghiêng chữ đã chọn.', $D);
        $this->fill($L, 'Phím tắt Ctrl + ___ dùng để gạch chân văn bản.', [[0, 'U']], 'Ctrl + U (Underline) gạch chân chữ đã chọn.', $D);
        $this->fill($L, 'Để ngắt sang trang mới, ta nhấn Ctrl + ___.', [[0, 'Enter']], 'Ctrl + Enter chèn ngắt trang nhanh.', $D);
        $this->fill($L, 'Lệnh chèn số trang nằm trong thẻ ___.', [[0, 'Insert']], 'Insert → Page Number chèn số trang vào văn bản.', $D);
        $this->fill($L, 'Sau khi sửa tiêu đề, cần ___ Table để cập nhật mục lục.', [[0, 'Update']], 'Nhấn Update Table để mục lục phản ánh nội dung mới.', $D);
    }

    // 20. tin-hoc-thpt-10-lop-10-2 — Bảng biểu và chèn đối tượng trong Word (lớp 10, trung bình)
    private function seedTh20(): void {
        $L = 'tin-hoc-thpt-10-lop-10-2'; $D = 'trung_binh';
        $this->quiz($L, 'Để chèn thêm một hàng vào bảng, ta dùng lệnh nào?', ['Insert Rows', 'Merge Cells', 'Split Table', 'Delete Row'], 0, 'Lệnh Insert Rows chèn thêm hàng vào bảng.', $D);
        $this->quiz($L, 'Để xóa một cột trong bảng, ta dùng lệnh nào?', ['Delete Columns', 'Merge Cells', 'Insert Columns', 'Split Cells'], 0, 'Lệnh Delete Columns xóa cột đang chọn.', $D);
        $this->quiz($L, 'Kiểu bao quanh "In Front of Text" có nghĩa gì?', ['Hình nằm trên chữ', 'Hình nằm dưới chữ', 'Hình như kí tự trong dòng', 'Hình biến mất'], 0, 'In Front of Text đặt hình nổi trên lớp chữ.', $D);
        $this->quiz($L, 'Muốn chèn biểu đồ vào văn bản, ta vào đâu?', ['Insert → Chart', 'Home → Font', 'View → Zoom', 'File → Print'], 0, 'Insert → Chart chèn biểu đồ minh họa số liệu.', $D);
        $this->quiz($L, 'Chức năng Caption trong Word dùng để làm gì?', ['Tạo nhãn đánh số tự động cho bảng, hình', 'Đổi màu chữ', 'Chèn nhạc nền', 'Tạo mục lục'], 0, 'Caption tạo nhãn "Bảng 1", "Hình 1"... tự động đánh số.', $D);
        $this->matching($L, 'Nối mỗi lệnh bảng với chức năng.', [['Insert Rows', 'Chèn thêm hàng'], ['Delete Columns', 'Xóa cột'], ['Merge Cells', 'Gộp ô'], ['Split Cells', 'Tách ô']], 'Nhóm lệnh làm việc với hàng, cột, ô của bảng.', $D);
        $this->matching($L, 'Nối mỗi kiểu bao quanh với đặc điểm.', [['In Line with Text', 'Hình như kí tự trong dòng'], ['Square', 'Chữ bao quanh hình'], ['In Front of Text', 'Hình nổi trên chữ'], ['Behind Text', 'Hình chìm dưới chữ']], 'Kiểu bao quanh quyết định cách hình và chữ xếp chồng.', $D);
        $this->matching($L, 'Nối mỗi đối tượng với thẻ chèn.', [['Bảng', 'Insert → Table'], ['Hình ảnh', 'Insert → Pictures'], ['Biểu đồ', 'Insert → Chart'], ['Hình vẽ', 'Insert → Shapes']], 'Thẻ Insert chứa mọi lệnh chèn đối tượng.', $D);
        $this->matching($L, 'Nối mỗi thao tác với mục đích.', [['Gộp ô tiêu đề', 'Tạo ô tiêu đề chung'], ['Tô màu hàng đầu', 'Làm nổi bật tiêu đề bảng'], ['Căn giữa nội dung ô', 'Bảng đẹp, dễ đọc'], ['Đánh số Caption', 'Tham chiếu bảng, hình dễ dàng']], 'Trình bày bảng đẹp giúp văn bản chuyên nghiệp.', $D);
        $this->matching($L, 'Nối mỗi loại đường viền với tác dụng.', [['Viền ngoài đậm', 'Nhấn mạnh khung bảng'], ['Đường kẻ mờ', 'Ngăn cách nhẹ các ô'], ['Không viền', 'Bảng chìm, gọn gàng'], ['Viền màu', 'Trang trí bảng']], 'Đường viền giúp bảng rõ ràng và đẹp mắt.', $D);
        $this->sortQ($L, 'Kéo mỗi thao tác vào nhóm "THÊM" hoặc "BỚT" trong bảng.', [['Insert Rows', 'THÊM'], ['Insert Columns', 'THÊM'], ['Delete Rows', 'BỚT'], ['Delete Columns', 'BỚT']], 'Insert thêm hàng/cột; Delete bớt hàng/cột.', $D);
        $this->sortQ($L, 'Kéo mỗi đối tượng vào nhóm "CHÈN ĐƯỢC" hoặc "KHÔNG CHÈN TRỰC TIẾP".', [['Hình ảnh', 'CHÈN ĐƯỢC'], ['Biểu đồ', 'CHÈN ĐƯỢC'], ['Video đang phát trực tuyến', 'KHÔNG CHÈN TRỰC TIẾP'], ['File cài đặt .exe', 'KHÔNG CHÈN TRỰC TIẾP']], 'Word chèn được hình, biểu đồ; không chèn trực tiếp file .exe.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['Merge Cells gộp nhiều ô thành một', 'ĐÚNG'], ['Split Cells tách một ô thành nhiều ô', 'ĐÚNG'], ['Bảng trong Word không đổi được cỡ chữ', 'SAI'], ['Hình chèn vào không di chuyển được', 'SAI']], 'Bảng và hình trong Word chỉnh sửa linh hoạt.', $D);
        $this->sortQ($L, 'Kéo mỗi bước vào nhóm "TRƯỚC KHI CHÈN BẢNG" hoặc "SAU KHI CHÈN BẢNG".', [['Đặt con trỏ đúng vị trí', 'TRƯỚC KHI CHÈN BẢNG'], ['Chọn số hàng, số cột', 'TRƯỚC KHI CHÈN BẢNG'], ['Nhập dữ liệu vào ô', 'SAU KHI CHÈN BẢNG'], ['Định dạng viền bảng', 'SAU KHI CHÈN BẢNG']], 'Chèn bảng trước, rồi nhập liệu và định dạng sau.', $D);
        $this->sortQ($L, 'Kéo mỗi kiểu bao quanh vào nhóm "HÌNH LINH HOẠT" hoặc "HÌNH CỐ ĐỊNH".', [['Square', 'HÌNH LINH HOẠT'], ['In Front of Text', 'HÌNH LINH HOẠT'], ['In Line with Text', 'HÌNH CỐ ĐỊNH'], ['Behind Text', 'HÌNH LINH HOẠT']], 'In Line with Text gắn hình như kí tự; các kiểu khác di chuyển tự do.', $D);
        $this->fill($L, 'Để chèn thêm hàng vào bảng, ta dùng lệnh Insert ___.', [[0, 'Rows']], 'Insert Rows chèn thêm hàng phía trên hoặc dưới.', $D);
        $this->fill($L, 'Để xóa cột đang chọn trong bảng, ta dùng lệnh Delete ___.', [[0, 'Columns']], 'Delete Columns xóa cột chứa con trỏ.', $D);
        $this->fill($L, 'Muốn chèn biểu đồ minh họa số liệu, ta vào Insert → ___.', [[0, 'Chart']], 'Chart chèn biểu đồ cột, tròn, đường... vào văn bản.', $D);
        $this->fill($L, 'Kiểu bao quanh ___ đặt hình ảnh nổi trên lớp chữ.', [[0, 'In Front of Text']], 'In Front of Text giúp hình nổi lên trên văn bản.', $D);
        $this->fill($L, 'Chức năng ___ tạo nhãn "Hình 1", "Bảng 1" đánh số tự động.', [[0, 'Caption']], 'Insert Caption tạo nhãn đánh số tự động cho đối tượng.', $D);
    }

    // 21. tin-hoc-thpt-10-lop-10-3 — Hàm cơ bản trong Excel: SUM, AVERAGE, MAX, MIN (lớp 10, trung bình)
    private function seedTh21(): void {
        $L = 'tin-hoc-thpt-10-lop-10-3'; $D = 'trung_binh';
        $this->quiz($L, 'Công thức =AVERAGE(B2:B6) tính gì?', ['Tổng các ô B2 đến B6', 'Trung bình cộng các ô B2 đến B6', 'Giá trị lớn nhất', 'Đếm số ô'], 1, 'AVERAGE tính trung bình cộng của vùng dữ liệu.', $D);
        $this->quiz($L, 'Công thức =MAX(C2:C10) cho kết quả gì?', ['Giá trị lớn nhất trong vùng C2:C10', 'Giá trị nhỏ nhất', 'Tổng các giá trị', 'Số lượng ô'], 0, 'MAX tìm giá trị lớn nhất trong vùng.', $D);
        $this->quiz($L, 'Công thức =MIN(D2:D8) cho kết quả gì?', ['Giá trị nhỏ nhất trong vùng D2:D8', 'Giá trị lớn nhất', 'Tổng các giá trị', 'Trung bình cộng'], 0, 'MIN tìm giá trị nhỏ nhất trong vùng.', $D);
        $this->quiz($L, 'Trong Excel, dấu nào dùng để ngăn cách các ô trong vùng?', ['Dấu chấm (.)', 'Dấu hai chấm (:)', 'Dấu phẩy (,)', 'Dấu chấm phẩy (;)'], 1, 'Dấu hai chấm nối ô đầu và ô cuối của vùng, ví dụ B2:B10.', $D);
        $this->quiz($L, 'Công thức =SUM(A1:A5) với A1:A5 là 2, 4, 6, 8, 10 cho kết quả?', ['20', '30', '10', '5'], 1, 'SUM cộng tất cả: 2+4+6+8+10 = 30.', $D);
        $this->matching($L, 'Nối mỗi hàm với ví dụ sử dụng.', [['SUM', 'Tính tổng điểm cả lớp'], ['AVERAGE', 'Tính điểm trung bình'], ['MAX', 'Tìm điểm cao nhất'], ['MIN', 'Tìm điểm thấp nhất']], 'Bốn hàm cơ bản phục vụ tính toán điểm số.', $D);
        $this->matching($L, 'Nối mỗi công thức với kết quả (A1=5, A2=15).', [['=SUM(A1:A2)', '20'], ['=AVERAGE(A1:A2)', '10'], ['=MAX(A1:A2)', '15'], ['=MIN(A1:A2)', '5']], 'Thay số vào công thức để tính kết quả.', $D);
        $this->matching($L, 'Nối mỗi thành phần với vai trò trong công thức.', [['Dấu =', 'Bắt đầu công thức'], ['Tên hàm', 'Phép tính cần làm'], ['Vùng dữ liệu', 'Các ô tham gia tính'], ['Dấu ngoặc', 'Bao quanh đối số']], 'Công thức Excel gồm dấu =, tên hàm và đối số.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với hàm phù hợp.', [['Tính tổng tiền bán hàng', 'SUM'], ['Tính nhiệt độ trung bình tuần', 'AVERAGE'], ['Tìm chiều cao lớn nhất', 'MAX'], ['Tìm giá rẻ nhất', 'MIN']], 'Chọn hàm đúng giúp tính toán nhanh chóng.', $D);
        $this->matching($L, 'Nối mỗi cách viết với ý nghĩa.', [['B5', 'Ô ở cột B hàng 5'], ['B2:B10', 'Vùng từ B2 đến B10'], ['=A1+5', 'Cộng 5 vào ô A1'], ['Sheet2!A1', 'Ô A1 của trang Sheet2']], 'Địa chỉ ô và vùng là nền tảng của công thức.', $D);
        $this->sortQ($L, 'Kéo mỗi công thức vào nhóm "TÍNH TỔNG" hoặc "TÍNH TRUNG BÌNH".', [['=SUM(A1:A10)', 'TÍNH TỔNG'], ['=B1+B2+B3', 'TÍNH TỔNG'], ['=AVERAGE(A1:A10)', 'TÍNH TRUNG BÌNH'], ['=(B1+B2)/2', 'TÍNH TRUNG BÌNH']], 'SUM tính tổng; AVERAGE tính trung bình cộng.', $D);
        $this->sortQ($L, 'Kéo mỗi công thức vào nhóm "ĐÚNG CÚ PHÁP" hoặc "SAI CÚ PHÁP".', [['=SUM(A1:A5)', 'ĐÚNG CÚ PHÁP'], ['=MAX(B2:B9)', 'ĐÚNG CÚ PHÁP'], ['SUM(A1:A5)', 'SAI CÚ PHÁP'], ['=SUM[A1:A5]', 'SAI CÚ PHÁP']], 'Công thức phải bắt đầu bằng dấu = và dùng ngoặc tròn.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['SUM bỏ qua ô trống trong vùng', 'ĐÚNG'], ['AVERAGE tính cả ô chứa chữ', 'SAI'], ['MAX/MIN so sánh được số', 'ĐÚNG'], ['Công thức không cần dấu =', 'SAI']], 'Hàm số bỏ qua ô trống và ô chứa chữ khi tính.', $D);
        $this->sortQ($L, 'Kéo mỗi cách viết vào nhóm "MỘT Ô" hoặc "MỘT VÙNG".', [['C7', 'MỘT Ô'], ['A1', 'MỘT Ô'], ['C2:C20', 'MỘT VÙNG'], ['B1:D5', 'MỘT VÙNG']], 'Một ô viết địa chỉ đơn; vùng viết ô đầu:ô cuối.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm vào nhóm "THỐNG KÊ" hoặc "KHÁC".', [['SUM', 'THỐNG KÊ'], ['AVERAGE', 'THỐNG KÊ'], ['SQRT', 'KHÁC'], ['CONCAT', 'KHÁC']], 'SUM, AVERAGE, MAX, MIN là các hàm thống kê cơ bản.', $D);
        $this->fill($L, 'Hàm ___ dùng để tìm giá trị lớn nhất trong một vùng.', [[0, 'MAX']], 'MAX trả về giá trị lớn nhất của vùng dữ liệu.', $D);
        $this->fill($L, 'Hàm ___ dùng để tìm giá trị nhỏ nhất trong một vùng.', [[0, 'MIN']], 'MIN trả về giá trị nhỏ nhất của vùng dữ liệu.', $D);
        $this->fill($L, 'Công thức =AVERAGE(A1:A5) tính ___ bình cộng của vùng A1:A5.', [[0, 'trung']], 'AVERAGE là hàm tính trung bình cộng.', $D);
        $this->fill($L, 'Trong vùng B2:B10, dấu ___ nối ô đầu và ô cuối của vùng.', [[0, ':']], 'Dấu hai chấm xác định một vùng ô liên tiếp.', $D);
        $this->fill($L, 'Công thức =SUM(3, 7, 10) cho kết quả bằng ___.', [[0, '20']], 'SUM cộng các số: 3 + 7 + 10 = 20.', $D);
    }

    // 22. tin-hoc-thpt-10-lop-10-4 — Hàm điều kiện IF và sắp xếp, lọc dữ liệu (lớp 10, khó)
    private function seedTh22(): void {
        $L = 'tin-hoc-thpt-10-lop-10-4'; $D = 'kho';
        $this->quiz($L, 'Công thức =IF(A1>10,"Lớn","Nhỏ") với A1 = 5 cho kết quả gì?', ['Lớn', 'Nhỏ', 'TRUE', 'FALSE'], 1, 'A1 = 5 không lớn hơn 10 nên trả về "Nhỏ".', $D);
        $this->quiz($L, 'Hàm SUMIF(vùng_đk, điều_kiện, vùng_tính) dùng để làm gì?', ['Tính tổng các ô thỏa điều kiện', 'Đếm số ô thỏa điều kiện', 'Tìm giá trị lớn nhất', 'Sắp xếp dữ liệu'], 0, 'SUMIF tính tổng có điều kiện.', $D);
        $this->quiz($L, 'Để sắp xếp điểm từ cao xuống thấp, ta chọn kiểu sắp xếp nào?', ['A → Z', 'Z → A', 'Tăng dần', 'Ngẫu nhiên'], 1, 'Sắp xếp giảm dần (Z → A / Largest to Smallest) cho điểm cao lên trước.', $D);
        $this->quiz($L, 'Khi lọc dữ liệu, các hàng không thỏa điều kiện sẽ ra sao?', ['Bị ẩn tạm thời', 'Bị xóa vĩnh viễn', 'Được tô màu đỏ', 'Được nhân đôi'], 0, 'Lọc chỉ ẩn tạm thời các hàng không thỏa điều kiện.', $D);
        $this->quiz($L, 'Công thức =IF(B2>=8,"Giỏi",IF(B2>=6.5,"Khá","Trung bình")) với B2 = 7 cho kết quả?', ['Giỏi', 'Khá', 'Trung bình', 'Lỗi'], 1, 'B2 = 7 không đạt 8 nhưng đạt 6.5 nên kết quả là "Khá".', $D);
        $this->matching($L, 'Nối mỗi hàm với chức năng.', [['IF', 'Trả về giá trị theo điều kiện'], ['COUNTIF', 'Đếm ô thỏa điều kiện'], ['SUMIF', 'Tính tổng có điều kiện'], ['AVERAGEIF', 'Tính trung bình có điều kiện']], 'Nhóm hàm IF mở rộng xử lý dữ liệu theo điều kiện.', $D);
        $this->matching($L, 'Nối mỗi công thức với kết quả (A1 = 8).', [['=IF(A1>10,"Đạt","Chưa")', 'Chưa'], ['=IF(A1>=8,"Đạt","Chưa")', 'Đạt'], ['=IF(A1=8,"Đúng","Sai")', 'Đúng'], ['=IF(A1<5,"Nhỏ","Lớn")', 'Lớn']], 'So sánh điều kiện với A1 = 8 để cho kết quả.', $D);
        $this->matching($L, 'Nối mỗi toán tử với ý nghĩa trong điều kiện.', [['=', 'Bằng'], ['<>', 'Khác'], ['>=', 'Lớn hơn hoặc bằng'], ['<=', 'Nhỏ hơn hoặc bằng']], 'Toán tử so sánh dùng trong điều kiện của hàm IF.', $D);
        $this->matching($L, 'Nối mỗi thao tác với vị trí trong thẻ Data.', [['Sort', 'Sắp xếp A-Z, Z-A'], ['Filter', 'Lọc dữ liệu'], ['Remove Duplicates', 'Xóa trùng lặp'], ['Text to Columns', 'Tách cột']], 'Thẻ Data chứa các công cụ xử lý dữ liệu.', $D);
        $this->matching($L, 'Nối mỗi nhu cầu với công cụ phù hợp.', [['Xếp hạng điểm', 'Sort'], ['Xem học sinh giỏi', 'Filter'], ['Đếm số điểm 10', 'COUNTIF'], ['Tính tổng điểm tổ 1', 'SUMIF']], 'Chọn đúng công cụ giúp xử lý bảng điểm nhanh.', $D);
        $this->sortQ($L, 'Kéo mỗi công thức vào nhóm "ĐÚNG CÚ PHÁP" hoặc "SAI CÚ PHÁP".', [['=IF(A1>5,"Cao","Thấp")', 'ĐÚNG CÚ PHÁP'], ['=COUNTIF(B2:B10,">=5")', 'ĐÚNG CÚ PHÁP'], ['=IF(A1>5,"Cao")', 'SAI CÚ PHÁP'], ['IF(A1>5,"Cao","Thấp")', 'SAI CÚ PHÁP']], 'IF cần đủ 3 đối số và bắt đầu bằng dấu =.', $D);
        $this->sortQ($L, 'Kéo mỗi nhu cầu vào nhóm "DÙNG SORT" hoặc "DÙNG FILTER".', [['Xếp tên theo A-Z', 'DÙNG SORT'], ['Xem điểm cao nhất trước', 'DÙNG SORT'], ['Chỉ xem học sinh nữ', 'DÙNG FILTER'], ['Ẩn học sinh nghỉ học', 'DÙNG FILTER']], 'Sort sắp xếp lại; Filter ẩn tạm thời theo điều kiện.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['IF có thể lồng nhau nhiều tầng', 'ĐÚNG'], ['Lọc làm mất dữ liệu gốc', 'SAI'], ['Sắp xếp thay đổi thứ tự hàng', 'ĐÚNG'], ['COUNTIF đếm được ô chữ', 'ĐÚNG']], 'IF lồng nhau xử lý nhiều điều kiện; lọc không làm mất dữ liệu.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm vào nhóm "CÓ ĐIỀU KIỆN" hoặc "KHÔNG ĐIỀU KIỆN".', [['SUMIF', 'CÓ ĐIỀU KIỆN'], ['COUNTIF', 'CÓ ĐIỀU KIỆN'], ['SUM', 'KHÔNG ĐIỀU KIỆN'], ['MAX', 'KHÔNG ĐIỀU KIỆN']], 'Nhóm hàm *IF tính toán có kèm điều kiện.', $D);
        $this->sortQ($L, 'Kéo mỗi bước vào nhóm "TRƯỚC KHI LỌC" hoặc "SAU KHI LỌC".', [['Chọn vùng dữ liệu', 'TRƯỚC KHI LỌC'], ['Bật Filter trong thẻ Data', 'TRƯỚC KHI LỌC'], ['Chọn điều kiện ở mũi tên', 'SAU KHI LỌC'], ['Tắt Filter để hiện lại', 'SAU KHI LỌC']], 'Bật Filter trước, chọn điều kiện và tắt Filter sau.', $D);
        $this->fill($L, 'Hàm ___ tính tổng các ô thỏa mãn điều kiện cho trước.', [[0, 'SUMIF']], 'SUMIF(vùng_đk, điều_kiện, vùng_tính) tính tổng có điều kiện.', $D);
        $this->fill($L, 'Công thức =IF(A1>5,"Cao","Thấp") thiếu dấu ___ ở đầu nên sai cú pháp.', [[0, '=']], 'Mọi công thức Excel đều phải bắt đầu bằng dấu =.', $D);
        $this->fill($L, 'Toán tử ___ có nghĩa là "khác" trong điều kiện Excel.', [[0, '<>']], 'Dấu <> dùng để so sánh khác nhau.', $D);
        $this->fill($L, 'Để xem điểm từ cao xuống thấp, ta sắp xếp theo thứ tự ___ dần.', [[0, 'giảm']], 'Sắp xếp giảm dần đưa giá trị lớn lên trước.', $D);
        $this->fill($L, 'Hàm IF có thể ___ nhau để xử lý nhiều điều kiện.', [[0, 'lồng']], 'IF lồng nhau như =IF(đk1,...,IF(đk2,...)) xử lý nhiều mức.', $D);
    }

    // 23. tin-hoc-thpt-11-lop-11-1 — Mạng máy tính và phân loại mạng (lớp 11, trung bình)
    private function seedTh23(): void {
        $L = 'tin-hoc-thpt-11-lop-11-1'; $D = 'trung_binh';
        $this->quiz($L, 'Mạng MAN có phạm vi khoảng bao nhiêu?', ['Một phòng', 'Một thành phố', 'Một quốc gia', 'Toàn cầu'], 1, 'MAN (Metropolitan Area Network) là mạng đô thị, phạm vi một thành phố.', $D);
        $this->quiz($L, 'Mạng WAN có đặc điểm gì?', ['Phạm vi rộng, liên quốc gia', 'Chỉ trong một phòng', 'Không cần thiết bị', 'Tốc độ luôn nhanh nhất'], 0, 'WAN là mạng diện rộng, kết nối các vùng, quốc gia.', $D);
        $this->quiz($L, 'Mạng không dây Wi-Fi thuộc loại nào?', ['Mạng có dây', 'Mạng không dây (wireless)', 'Mạng điện thoại', 'Mạng truyền hình'], 1, 'Wi-Fi truyền dữ liệu qua sóng vô tuyến nên là mạng không dây.', $D);
        $this->quiz($L, 'Lợi ích nào KHÔNG phải của mạng máy tính?', ['Chia sẻ tài nguyên', 'Giao tiếp nhanh', 'Tốn thêm chi phí thiết bị', 'Truy cập thông tin'], 2, 'Tốn chi phí thiết bị là nhược điểm, không phải lợi ích.', $D);
        $this->quiz($L, 'Mạng máy tính trong một trường học thường là loại nào?', ['LAN', 'WAN', 'MAN', 'Internet'], 0, 'Mạng trong trường học, phạm vi hẹp, thường là mạng LAN.', $D);
        $this->matching($L, 'Nối mỗi loại mạng với phạm vi.', [['LAN', 'Phòng, tòa nhà'], ['MAN', 'Thành phố'], ['WAN', 'Quốc gia, toàn cầu'], ['PAN', 'Quanh một người']], 'Bốn loại mạng phân theo phạm vi từ hẹp đến rộng.', $D);
        $this->matching($L, 'Nối mỗi ví dụ với loại mạng.', [['Wi-Fi quán cà phê', 'LAN không dây'], ['Mạng trường học', 'LAN'], ['Mạng truyền hình cáp thành phố', 'MAN'], ['Internet', 'WAN']], 'Nhận biết loại mạng qua ví dụ thực tế.', $D);
        $this->matching($L, 'Nối mỗi kiểu kết nối với đặc điểm.', [['Có dây', 'Ổn định, tốc độ cao'], ['Không dây', 'Tiện lợi, di động'], ['Cáp quang', 'Rất nhanh, xa'], ['Bluetooth', 'Gần, tiết kiệm pin']], 'Mỗi kiểu kết nối có ưu nhược điểm riêng.', $D);
        $this->matching($L, 'Nối mỗi lợi ích với ví dụ.', [['Chia sẻ máy in', 'Cả phòng dùng chung một máy in'], ['Giao tiếp', 'Gửi email, chat'], ['Chia sẻ dữ liệu', 'Lấy tài liệu từ máy khác'], ['Giải trí', 'Chơi game online cùng nhau']], 'Mạng máy tính mang lại nhiều lợi ích thiết thực.', $D);
        $this->matching($L, 'Nối mỗi khái niệm với nội dung.', [['Máy trạm', 'Máy người dùng cuối'], ['Máy chủ', 'Cung cấp tài nguyên'], ['Đường truyền', 'Kênh chuyển dữ liệu'], ['Giao thức', 'Quy tắc trao đổi dữ liệu']], 'Các thành phần cơ bản tạo nên mạng máy tính.', $D);
        $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm "LAN" hoặc "WAN".', [['Mạng lớp học', 'LAN'], ['Wi-Fi gia đình', 'LAN'], ['Internet', 'WAN'], ['Mạng ngân hàng toàn quốc', 'WAN']], 'LAN phạm vi hẹp; WAN phạm vi rộng.', $D);
        $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm "MẠNG CÓ DÂY" hoặc "MẠNG KHÔNG DÂY".', [['Dùng cáp mạng', 'MẠNG CÓ DÂY'], ['Tốc độ ổn định', 'MẠNG CÓ DÂY'], ['Dùng sóng Wi-Fi', 'MẠNG KHÔNG DÂY'], ['Di chuyển tự do', 'MẠNG KHÔNG DÂY']], 'Mạng có dây ổn định; mạng không dây tiện di chuyển.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['LAN có phạm vi hẹp hơn WAN', 'ĐÚNG'], ['Internet là mạng WAN lớn nhất', 'ĐÚNG'], ['Mạng không dây không cần thiết bị phát', 'SAI'], ['Mạng máy tính chỉ dùng để chơi game', 'SAI']], 'Hiểu đúng về các loại mạng máy tính.', $D);
        $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm "CẦN MẠNG" hoặc "KHÔNG CẦN MẠNG".', [['Học trực tuyến', 'CẦN MẠNG'], ['Gửi email', 'CẦN MẠNG'], ['Gõ văn bản offline', 'KHÔNG CẦN MẠNG'], ['Vẽ tranh trong Paint', 'KHÔNG CẦN MẠNG']], 'Học online, gửi mail cần mạng; gõ văn bản, vẽ tranh không cần.', $D);
        $this->sortQ($L, 'Kéo mỗi loại mạng vào nhóm theo phạm vi từ hẹp đến rộng.', [['PAN', 'HẸP NHẤT'], ['LAN', 'HẸP'], ['MAN', 'RỘNG'], ['WAN', 'RỘNG NHẤT']], 'PAN < LAN < MAN < WAN theo phạm vi phủ sóng.', $D);
        $this->fill($L, 'MAN là mạng có phạm vi một thành ___.', [[0, 'phố']], 'MAN (Metropolitan Area Network) là mạng đô thị.', $D);
        $this->fill($L, 'WAN là mạng ___ rộng, kết nối nhiều vùng, quốc gia.', [[0, 'diện']], 'WAN (Wide Area Network) là mạng diện rộng.', $D);
        $this->fill($L, 'Nhờ mạng máy tính, nhiều người có thể ___ sẻ tài nguyên cho nhau.', [[0, 'chia']], 'Chia sẻ tài nguyên là lợi ích lớn của mạng máy tính.', $D);
        $this->fill($L, 'Wi-Fi truyền dữ liệu bằng sóng vô ___.', [[0, 'tuyến']], 'Sóng vô tuyến giúp kết nối không cần dây.', $D);
        $this->fill($L, 'Máy ___ trong mạng có nhiệm vụ cung cấp tài nguyên, dịch vụ.', [[0, 'chủ']], 'Máy chủ (server) phục vụ các máy trạm (client).', $D);
    }

    // 24. tin-hoc-thpt-11-lop-11-2 — Mô hình mạng và thiết bị mạng (lớp 11, trung bình)
    private function seedTh24(): void {
        $L = 'tin-hoc-thpt-11-lop-11-2'; $D = 'trung_binh';
        $this->quiz($L, 'Switch trong mạng LAN có chức năng gì?', ['Chuyển tiếp dữ liệu đến đúng máy nhận', 'Phát Wi-Fi', 'Lưu trữ dữ liệu', 'In tài liệu'], 0, 'Switch chuyển gói tin đến đúng cổng của máy nhận.', $D);
        $this->quiz($L, 'Modem trong gia đình có nhiệm vụ gì?', ['Biến đổi tín hiệu để kết nối Internet', 'Phát nhạc', 'In ảnh', 'Sạc điện thoại'], 0, 'Modem biến đổi tín hiệu giữa đường truyền và mạng gia đình.', $D);
        $this->quiz($L, 'Trong mô hình ngang hàng, mỗi máy tính có vai trò gì?', ['Vừa dùng vừa chia sẻ tài nguyên', 'Chỉ dùng không chia sẻ', 'Chỉ làm máy chủ', 'Không kết nối gì'], 0, 'Mạng ngang hàng: các máy bình đẳng, vừa dùng vừa chia sẻ.', $D);
        $this->quiz($L, 'Ưu điểm của mô hình khách – chủ là gì?', ['Quản lý tập trung, bảo mật tốt', 'Không cần máy chủ', 'Rẻ nhất mọi trường hợp', 'Không cần quản trị'], 0, 'Mô hình khách – chủ quản lý tập trung, dễ bảo mật.', $D);
        $this->quiz($L, 'Cáp xoắn (UTP) thường dùng để làm gì?', ['Nối máy tính vào mạng LAN', 'Sạc điện thoại', 'Nối loa', 'Treo quần áo'], 0, 'Cáp UTP là loại cáp mạng phổ biến nối máy vào LAN.', $D);
        $this->matching($L, 'Nối mỗi thiết bị với chức năng.', [['Switch', 'Chia cổng mạng LAN'], ['Router', 'Định tuyến, phát Wi-Fi'], ['Modem', 'Kết nối đường truyền Internet'], ['Access Point', 'Mở rộng sóng Wi-Fi']], 'Mỗi thiết bị mạng có vai trò riêng.', $D);
        $this->matching($L, 'Nối mỗi mô hình với đặc điểm.', [['Khách – chủ', 'Có máy chủ quản lý tập trung'], ['Ngang hàng', 'Các máy bình đẳng'], ['Khách – chủ', 'Bảo mật tốt'], ['Ngang hàng', 'Dễ cài đặt, quy mô nhỏ']], 'Hai mô hình mạng phổ biến với đặc điểm khác nhau.', $D);
        $this->matching($L, 'Nối mỗi ví dụ với mô hình mạng.', [['Mạng công ty có máy chủ', 'Khách – chủ'], ['Chia sẻ file giữa 2 laptop', 'Ngang hàng'], ['Hệ thống ngân hàng', 'Khách – chủ'], ['Chơi game LAN 3 máy', 'Ngang hàng']], 'Nhận biết mô hình qua ví dụ thực tế.', $D);
        $this->matching($L, 'Nối mỗi loại cáp với đặc điểm.', [['Cáp UTP', 'Rẻ, dùng cho LAN'], ['Cáp quang', 'Rất nhanh, đường dài'], ['Cáp đồng trục', 'Ít dùng hiện nay'], ['Không dây', 'Tiện, không cần dây']], 'Mỗi loại đường truyền có ưu điểm riêng.', $D);
        $this->matching($L, 'Nối mỗi tầng thiết bị với ví dụ.', [['Thiết bị đầu cuối', 'Máy tính, điện thoại'], ['Thiết bị kết nối', 'Switch, Router'], ['Đường truyền', 'Cáp, sóng Wi-Fi'], ['Máy chủ', 'Server lưu web']], 'Mạng gồm thiết bị đầu cuối, thiết bị kết nối và đường truyền.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "KẾT NỐI MẠNG" hoặc "ĐẦU CUỐI".', [['Switch', 'KẾT NỐI MẠNG'], ['Router', 'KẾT NỐI MẠNG'], ['Laptop', 'ĐẦU CUỐI'], ['Điện thoại', 'ĐẦU CUỐI']], 'Switch, Router kết nối; laptop, điện thoại là đầu cuối.', $D);
        $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm "NÊN DÙNG KHÁCH – CHỦ" hoặc "NGANG HÀNG ĐƯỢC".', [['Công ty 100 nhân viên', 'NÊN DÙNG KHÁCH – CHỦ'], ['Ngân hàng', 'NÊN DÙNG KHÁCH – CHỦ'], ['2 máy ở nhà', 'NGANG HÀNG ĐƯỢC'], ['Nhóm 3 bạn chia sẻ file', 'NGANG HÀNG ĐƯỢC']], 'Quy mô lớn, cần bảo mật dùng khách – chủ; nhỏ dùng ngang hàng.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['Router có thể phát Wi-Fi', 'ĐÚNG'], ['Switch chia nhiều cổng mạng', 'ĐÚNG'], ['Modem dùng để in', 'SAI'], ['Mạng ngang hàng cần máy chủ mạnh', 'SAI']], 'Hiểu đúng chức năng từng thiết bị mạng.', $D);
        $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm "CÓ TRONG NHÀ" hoặc "CỦA NHÀ MẠNG".', [['Router Wi-Fi', 'CÓ TRONG NHÀ'], ['Laptop', 'CÓ TRONG NHÀ'], ['Trạm phát sóng', 'CỦA NHÀ MẠNG'], ['Cáp quang biển', 'CỦA NHÀ MẠNG']], 'Thiết bị trong nhà do người dùng quản lý; hạ tầng lớn của nhà mạng.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "QUẢN TRỊ MẠNG" hoặc "NGƯỜI DÙNG".', [['Đặt mật khẩu Wi-Fi', 'QUẢN TRỊ MẠNG'], ['Chia băng thông', 'QUẢN TRỊ MẠNG'], ['Kết nối Wi-Fi', 'NGƯỜI DÙNG'], ['Lướt web', 'NGƯỜI DÙNG']], 'Quản trị mạng lo cấu hình; người dùng chỉ sử dụng.', $D);
        $this->fill($L, 'Thiết bị chia nhiều cổng để nối các máy trong mạng LAN là ___.', [[0, 'switch']], 'Switch chuyển dữ liệu đến đúng máy nhận trong LAN.', $D);
        $this->fill($L, 'Thiết bị biến đổi tín hiệu để nhà dùng Internet là ___.', [[0, 'modem']], 'Modem nối mạng gia đình với đường truyền của nhà mạng.', $D);
        $this->fill($L, 'Mạng ___ hàng gồm các máy bình đẳng, vừa dùng vừa chia sẻ.', [[0, 'ngang']], 'Mạng ngang hàng (peer-to-peer) không có máy chủ trung tâm.', $D);
        $this->fill($L, 'Cáp ___ là loại cáp mạng phổ biến, rẻ tiền cho mạng LAN.', [[0, 'UTP']], 'Cáp xoắn UTP thường dùng nối máy tính vào mạng.', $D);
        $this->fill($L, 'Ưu điểm của mô hình khách – chủ là quản lý tập ___ .', [[0, 'trung']], 'Quản lý tập trung giúp bảo mật và dễ quản trị.', $D);
    }

    // 25. tin-hoc-thpt-11-lop-11-3 — Internet và các dịch vụ cơ bản (lớp 11, trung bình)
    private function seedTh25(): void {
        $L = 'tin-hoc-thpt-11-lop-11-3'; $D = 'trung_binh';
        $this->quiz($L, 'DNS có chức năng gì?', ['Dịch tên miền thành địa chỉ IP', 'Tăng tốc CPU', 'Diệt virus', 'Nén tệp'], 0, 'DNS (Domain Name System) dịch tên miền thành địa chỉ IP.', $D);
        $this->quiz($L, 'Email hoạt động dựa trên dịch vụ nào của Internet?', ['Thư điện tử (E-mail)', 'Truyền hình', 'Điện thoại cố định', 'Fax'], 0, 'Email là dịch vụ thư điện tử trên Internet.', $D);
        $this->quiz($L, 'Trong địa chỉ "https://vuihoc.vn", phần nào là tên miền?', ['https://', 'vuihoc.vn', '//', 'https'], 1, 'vuihoc.vn là tên miền của website.', $D);
        $this->quiz($L, 'Cloud (đám mây) trong tin học có nghĩa gì?', ['Lưu trữ, dùng dịch vụ qua Internet', 'Mây trên trời', 'Máy tính bị hỏng', 'Mạng nội bộ'], 0, 'Điện toán đám mây cung cấp lưu trữ, dịch vụ qua Internet.', $D);
        $this->quiz($L, 'Thương mại điện tử là gì?', ['Mua bán hàng hóa qua mạng', 'Chợ truyền thống', 'Siêu thị mini', 'Cửa hàng sách'], 0, 'Thương mại điện tử là mua bán qua các sàn, website.', $D);
        $this->matching($L, 'Nối mỗi dịch vụ với ví dụ.', [['Thư điện tử', 'Gmail'], ['Mạng xã hội', 'Facebook'], ['Thương mại điện tử', 'Shopee'], ['Lưu trữ đám mây', 'Google Drive']], 'Internet cung cấp nhiều dịch vụ thiết thực.', $D);
        $this->matching($L, 'Nối mỗi khái niệm với nội dung.', [['Địa chỉ IP', 'Định danh thiết bị trên mạng'], ['Tên miền', 'Tên dễ nhớ của website'], ['URL', 'Địa chỉ đầy đủ của trang web'], ['DNS', 'Dịch tên miền thành IP']], 'Các khái niệm nền tảng của Internet.', $D);
        $this->matching($L, 'Nối mỗi trình duyệt với đặc điểm.', [['Chrome', 'Phổ biến của Google'], ['Cốc Cốc', 'Của Việt Nam'], ['Firefox', 'Mã nguồn mở'], ['Edge', 'Có sẵn trong Windows']], 'Nhiều trình duyệt để lựa chọn lướt web.', $D);
        $this->matching($L, 'Nối mỗi hoạt động với dịch vụ phù hợp.', [['Gửi thư xin việc', 'Email'], ['Họp trực tuyến', 'Zoom, Meet'], ['Xem phim', 'Youtube'], ['Mua sách', 'Sàn thương mại điện tử']], 'Chọn đúng dịch vụ cho từng nhu cầu.', $D);
        $this->matching($L, 'Nối mỗi phần của URL với ý nghĩa.', [['https://', 'Giao thức bảo mật'], ['www', 'World Wide Web'], ['vuihoc.vn', 'Tên miền'], ['/tro-choi', 'Đường dẫn trang']], 'URL gồm giao thức, tên miền và đường dẫn.', $D);
        $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm "DỊCH VỤ INTERNET" hoặc "KHÔNG DÙNG INTERNET".', [['Học trực tuyến', 'DỊCH VỤ INTERNET'], ['Đặt đồ ăn online', 'DỊCH VỤ INTERNET'], ['Đá bóng ngoài sân', 'KHÔNG DÙNG INTERNET'], ['Đọc sách giấy', 'KHÔNG DÙNG INTERNET']], 'Học online, đặt đồ ăn cần Internet.', $D);
        $this->sortQ($L, 'Kéo mỗi địa chỉ vào nhóm "TÊN MIỀN" hoặc "ĐỊA CHỈ IP".', [['google.com', 'TÊN MIỀN'], ['vuihoc.vn', 'TÊN MIỀN'], ['142.250.1.1', 'ĐỊA CHỈ IP'], ['8.8.8.8', 'ĐỊA CHỈ IP']], 'Tên miền dễ nhớ; địa chỉ IP là dãy số.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['https an toàn hơn http', 'ĐÚNG'], ['Mỗi thiết bị có địa chỉ IP riêng', 'ĐÚNG'], ['Internet và Wi-Fi là một', 'SAI'], ['Email gửi được tệp đính kèm', 'ĐÚNG']], 'Hiểu đúng về Internet và các dịch vụ.', $D);
        $this->sortQ($L, 'Kéo mỗi nhu cầu vào nhóm "DỊCH VỤ PHÙ HỢP".', [['Lưu bài tập online', 'Google Drive'], ['Hỏi bài bạn bè', 'Zalo'], ['Tra cứu kiến thức', 'Google Tìm kiếm'], ['Xem thời tiết', 'Ứng dụng thời tiết']], 'Mỗi nhu cầu có dịch vụ Internet phù hợp.', $D);
        $this->sortQ($L, 'Kéo mỗi website vào nhóm "HỌC TẬP" hoặc "GIẢI TRÍ".', [['Vuihoc', 'HỌC TẬP'], ['Thư viện học liệu', 'HỌC TẬP'], ['Web game', 'GIẢI TRÍ'], ['Web phim', 'GIẢI TRÍ']], 'Internet phục vụ cả học tập và giải trí.', $D);
        $this->fill($L, 'Hệ thống ___ có nhiệm vụ dịch tên miền thành địa chỉ IP.', [[0, 'DNS']], 'DNS giúp ta chỉ cần nhớ tên miền dễ đọc.', $D);
        $this->fill($L, 'Phần mềm dùng để lướt web gọi là trình ___.', [[0, 'duyệt']], 'Trình duyệt (browser) mở các trang web.', $D);
        $this->fill($L, 'https mã hóa dữ liệu nên ___ mật hơn http.', [[0, 'bảo']], 'Chữ "s" trong https nghĩa là secure – bảo mật.', $D);
        $this->fill($L, 'Mua bán hàng hóa qua mạng gọi là thương mại điện ___.', [[0, 'tử']], 'Thương mại điện tử phát triển mạnh trên Internet.', $D);
        $this->fill($L, 'Lưu trữ dữ liệu trên máy chủ qua Internet gọi là lưu trữ đám ___.', [[0, 'mây']], 'Đám mây giúp truy cập dữ liệu mọi lúc, mọi nơi.', $D);
    }

    // 26. tin-hoc-thpt-11-lop-11-4 — An toàn thông tin và phòng chống mã độc (lớp 11, khó)
    private function seedTh26(): void {
        $L = 'tin-hoc-thpt-11-lop-11-4'; $D = 'kho';
        $this->quiz($L, 'Spyware (phần mềm gián điệp) gây hại bằng cách nào?', ['Lén thu thập thông tin người dùng', 'Làm màn hình đẹp hơn', 'Tăng tốc máy tính', 'Tự dọn rác'], 0, 'Spyware lén theo dõi, đánh cắp thông tin cá nhân.', $D);
        $this->quiz($L, 'Keylogger là loại mã độc nào?', ['Ghi lại phím người dùng gõ để đánh cắp mật khẩu', 'Chơi nhạc', 'Vẽ tranh', 'Nén tệp'], 0, 'Keylogger ghi lại thao tác bàn phím để trộm mật khẩu.', $D);
        $this->quiz($L, 'Tấn công DDoS là gì?', ['Làm nghẽn dịch vụ bằng lượng truy cập khổng lồ', 'Tặng quà online', 'Quảng cáo sản phẩm', 'Cập nhật phần mềm'], 0, 'DDoS dùng nhiều máy cùng tấn công làm sập dịch vụ.', $D);
        $this->quiz($L, 'Mã độc tống tiền (ransomware) thường lây qua đâu?', ['Email lừa đảo có tệp đính kèm', 'Không khí', 'Nước uống', 'Ánh sáng'], 0, 'Ransomware thường lây qua email lừa đảo, link độc.', $D);
        $this->quiz($L, 'Biện pháp nào giúp giảm thiệt hại khi bị ransomware?', ['Sao lưu dữ liệu thường xuyên ở nơi riêng', 'Tắt máy vĩnh viễn', 'Xóa hết dữ liệu', 'Trả tiền chuộc ngay'], 0, 'Có bản sao lưu thì không sợ mất dữ liệu khi bị mã hóa.', $D);
        $this->matching($L, 'Nối mỗi loại mã độc với cách gây hại.', [['Spyware', 'Đánh cắp thông tin lén lút'], ['Keylogger', 'Ghi lại phím gõ'], ['Ransomware', 'Mã hóa dữ liệu tống tiền'], ['Botnet', 'Biến máy thành "tay sai" tấn công']], 'Mỗi mã độc có mục đích gây hại khác nhau.', $D);
        $this->matching($L, 'Nối mỗi biện pháp với tác dụng.', [['Sao lưu dữ liệu', 'Khôi phục khi bị mã hóa'], ['Cập nhật phần mềm', 'Vá lỗ hổng bảo mật'], ['Không mở link lạ', 'Tránh bẫy phishing'], ['Dùng mật khẩu mạnh', 'Khó bị đoán mò']], 'Phòng bệnh hơn chữa bệnh trong an toàn thông tin.', $D);
        $this->matching($L, 'Nối mỗi tình huống với cách xử lý.', [['Nghi bị keylogger', 'Quét virus, đổi mật khẩu trên máy sạch'], ['Bị ransomware', 'Ngắt mạng, không trả tiền, báo công an'], ['Lộ mật khẩu', 'Đổi ngay, bật 2FA'], ['Web giả mạo ngân hàng', 'Không nhập thông tin, báo cáo']], 'Xử lý đúng hạn chế thiệt hại.', $D);
        $this->matching($L, 'Nối mỗi thuật ngữ với nghĩa.', [['Phishing', 'Lừa đảo qua giả mạo'], ['Malware', 'Phần mềm độc hại chung'], ['Firewall', 'Tường lửa'], ['Backup', 'Sao lưu dữ liệu']], 'Thuật ngữ an toàn thông tin cần nắm vững.', $D);
        $this->matching($L, 'Nối mỗi thói quen với ĐÚNG (an toàn) hoặc SAI.', [['Kiểm tra kỹ địa chỉ web ngân hàng', 'ĐÚNG'], ['Dùng phần mềm bản quyền', 'ĐÚNG'], ['Tải phần mềm từ web lạ', 'SAI'], ['Tắt cập nhật bảo mật', 'SAI']], 'Thói quen tốt tạo lá chắn an toàn.', $D);
        $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm "NGHI BỊ MÃ ĐỘC" hoặc "BÌNH THƯỜNG".', [['Tệp bị mã hóa hàng loạt', 'NGHI BỊ MÃ ĐỘC'], ['Xuất hiện đòi tiền chuộc', 'NGHI BỊ MÃ ĐỘC'], ['Máy chạy ổn định', 'BÌNH THƯỜNG'], ['Mở ứng dụng nhanh', 'BÌNH THƯỜNG']], 'Tệp bị mã hóa, đòi tiền chuộc là dấu hiệu ransomware.', $D);
        $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm "AN TOÀN" hoặc "NGUY HIỂM".', [['Sao lưu dữ liệu định kỳ', 'AN TOÀN'], ['Cập nhật hệ điều hành', 'AN TOÀN'], ['Mở tệp đính kèm lạ', 'NGUY HIỂM'], ['Dùng USB lạ không quét', 'NGUY HIỂM']], 'Sao lưu và cập nhật giúp an toàn trước mã độc.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['Không nên trả tiền chuộc cho ransomware', 'ĐÚNG'], ['Sao lưu giúp khôi phục dữ liệu', 'ĐÚNG'], ['Phishing chỉ qua điện thoại', 'SAI'], ['Phần mềm lậu luôn an toàn', 'SAI']], 'Hiểu đúng giúp phòng chống mã độc hiệu quả.', $D);
        $this->sortQ($L, 'Kéo mỗi loại mã độc vào nhóm "ĐÁNH CẮP" hoặc "PHÁ HOẠI".', [['Spyware', 'ĐÁNH CẮP'], ['Keylogger', 'ĐÁNH CẮP'], ['Worm phá hệ thống', 'PHÁ HOẠI'], ['Virus xóa tệp', 'PHÁ HOẠI']], 'Spyware, keylogger trộm thông tin; worm, virus phá hoại.', $D);
        $this->sortQ($L, 'Kéo mỗi việc vào nhóm "KHI BỊ TẤN CÔNG" nên làm.', [['Ngắt kết nối mạng', 'KHI BỊ TẤN CÔNG'], ['Báo quản trị viên', 'KHI BỊ TẤN CÔNG'], ['Giữ nguyên hiện trường', 'KHI BỊ TẤN CÔNG'], ['Tự ý xóa hết mọi thứ', 'KHÔNG NÊN']], 'Bị tấn công: ngắt mạng, báo cáo, giữ bằng chứng.', $D);
        $this->fill($L, 'Phần mềm lén thu thập thông tin người dùng gọi là ___.', [[0, 'spyware']], 'Spyware (phần mềm gián điệp) đánh cắp thông tin lén lút.', $D);
        $this->fill($L, 'Mã độc ghi lại phím người dùng gõ gọi là ___.', [[0, 'keylogger']], 'Keylogger giúp kẻ xấu biết được mật khẩu đã gõ.', $D);
        $this->fill($L, 'Sao lưu dữ liệu thường xuyên giúp ___ phục khi bị mã độc phá.', [[0, 'khôi']], 'Có bản sao lưu thì không lo mất dữ liệu.', $D);
        $this->fill($L, 'Không nên trả tiền ___ cho kẻ tấn công ransomware.', [[0, 'chuộc']], 'Trả tiền không đảm bảo lấy lại dữ liệu và tiếp tay tội phạm.', $D);
        $this->fill($L, 'Tấn công ___ dùng lượng truy cập khổng lồ làm nghẽn dịch vụ.', [[0, 'DDoS']], 'DDoS (từ chối dịch vụ phân tán) làm sập website, dịch vụ.', $D);
    }

    // 27. tin-hoc-thpt-12-lop-12-1 — Khái niệm thuật toán và các cách mô tả (lớp 12, trung bình)
    private function seedTh27(): void {
        $L = 'tin-hoc-thpt-12-lop-12-1'; $D = 'trung_binh';
        $this->quiz($L, 'Tính dừng của thuật toán có nghĩa gì?', ['Thuật toán phải kết thúc sau hữu hạn bước', 'Thuật toán chạy mãi mãi', 'Thuật toán không cần đầu vào', 'Thuật toán chỉ có một bước'], 0, 'Tính dừng: thuật toán phải kết thúc sau số bước hữu hạn.', $D);
        $this->quiz($L, 'Trong sơ đồ khối, hình bình hành dùng để biểu diễn gì?', ['Nhập/Xuất dữ liệu', 'Tính toán', 'Điều kiện', 'Bắt đầu'], 0, 'Hình bình hành biểu diễn thao tác nhập, xuất dữ liệu.', $D);
        $this->quiz($L, 'Hình oval (elip) trong sơ đồ khối dùng để biểu diễn gì?', ['Bắt đầu/Kết thúc', 'Tính toán', 'Điều kiện rẽ nhánh', 'Nhập dữ liệu'], 0, 'Hình oval đánh dấu điểm bắt đầu và kết thúc thuật toán.', $D);
        $this->quiz($L, 'Cách mô tả thuật toán bằng ngôn ngữ tự nhiên có nhược điểm gì?', ['Dễ gây mơ hồ, dài dòng', 'Không ai hiểu được', 'Không mô tả được', 'Quá ngắn gọn'], 0, 'Ngôn ngữ tự nhiên dài dòng và có thể gây hiểu mơ hồ.', $D);
        $this->quiz($L, 'Ví dụ nào sau đây là một thuật toán?', ['Công thức nấu phở bò', 'Bức tranh phong cảnh', 'Bài hát', 'Giấc mơ'], 0, 'Công thức nấu ăn là dãy bước theo trình tự – một thuật toán đời thường.', $D);
        $this->matching($L, 'Nối mỗi hình sơ đồ khối với ý nghĩa.', [['Oval', 'Bắt đầu/Kết thúc'], ['Hình bình hành', 'Nhập/Xuất'], ['Hình chữ nhật', 'Tính toán, gán'], ['Hình thoi', 'Điều kiện rẽ nhánh']], 'Bốn hình cơ bản của sơ đồ khối.', $D);
        $this->matching($L, 'Nối mỗi tính chất với nội dung.', [['Tính dừng', 'Kết thúc sau hữu hạn bước'], ['Tính xác định', 'Mỗi bước rõ ràng'], ['Tính đúng đắn', 'Cho kết quả đúng'], ['Tính phổ dụng', 'Giải được lớp bài toán']], 'Bốn tính chất quan trọng của thuật toán.', $D);
        $this->matching($L, 'Nối mỗi cách mô tả với đặc điểm.', [['Liệt kê bước', 'Dễ viết, dễ hiểu'], ['Sơ đồ khối', 'Trực quan bằng hình'], ['Mã giả', 'Gần với lập trình'], ['Ngôn ngữ tự nhiên', 'Dễ gây mơ hồ']], 'Mỗi cách mô tả có ưu nhược điểm riêng.', $D);
        $this->matching($L, 'Nối mỗi bài toán với ý tưởng thuật toán.', [['Tìm số lớn nhất', 'So sánh lần lượt'], ['Sắp xếp điểm', 'Đổi chỗ theo thứ tự'], ['Tính tổng 1..n', 'Cộng dồn trong vòng lặp'], ['Kiểm tra số nguyên tố', 'Thử chia hết']], 'Mỗi bài toán có ý tưởng thuật toán phù hợp.', $D);
        $this->matching($L, 'Nối mỗi đầu vào – đầu ra của bài toán.', [['Giải phương trình bậc nhất', 'Vào: a, b – Ra: x'], ['Tính diện tích hình chữ nhật', 'Vào: dài, rộng – Ra: diện tích'], ['Tìm số lớn nhất', 'Vào: dãy số – Ra: số lớn nhất'], ['Kiểm tra chẵn lẻ', 'Vào: n – Ra: chẵn/lẻ']], 'Mọi bài toán đều có đầu vào (input) và đầu ra (output).', $D);
        $this->sortQ($L, 'Kéo mỗi bước vào nhóm "ĐÚNG TRÌNH TỰ" hoặc "SAI TRÌNH TỰ" (pha trà).', [['Đun sôi nước', 'ĐÚNG TRÌNH TỰ'], ['Cho trà vào ấm', 'ĐÚNG TRÌNH TỰ'], ['Rót nước sôi vào ấm', 'ĐÚNG TRÌNH TỰ'], ['Rót nước trước khi đun sôi', 'SAI TRÌNH TỰ']], 'Thuật toán pha trà phải đúng trình tự các bước.', $D);
        $this->sortQ($L, 'Kéo mỗi kí hiệu vào nhóm "SƠ ĐỒ KHỐI" hoặc "KHÔNG PHẢI".', [['Hình thoi', 'SƠ ĐỒ KHỐI'], ['Hình chữ nhật', 'SƠ ĐỒ KHỐI'], ['Nốt nhạc', 'KHÔNG PHẢI'], ['Ngôi sao', 'KHÔNG PHẢI']], 'Sơ đồ khối dùng oval, bình hành, chữ nhật, thoi.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['Thuật toán có tính dừng', 'ĐÚNG'], ['Mỗi bước phải xác định', 'ĐÚNG'], ['Thuật toán chạy vô hạn vẫn tốt', 'SAI'], ['Sơ đồ khối dùng hình vẽ', 'ĐÚNG']], 'Thuật toán phải dừng, xác định và đúng đắn.', $D);
        $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm "LÀ THUẬT TOÁN" hoặc "KHÔNG PHẢI".', [['Công thức nấu ăn', 'LÀ THUẬT TOÁN'], ['Hướng dẫn lắp ráp', 'LÀ THUẬT TOÁN'], ['Bức ảnh', 'KHÔNG PHẢI'], ['Bản nhạc', 'KHÔNG PHẢI']], 'Dãy bước theo trình tự là thuật toán.', $D);
        $this->sortQ($L, 'Kéo mỗi cách mô tả vào nhóm "TRỰC QUAN" hoặc "BẰNG CHỮ".', [['Sơ đồ khối', 'TRỰC QUAN'], ['Lưu đồ', 'TRỰC QUAN'], ['Liệt kê các bước', 'BẰNG CHỮ'], ['Mã giả', 'BẰNG CHỮ']], 'Sơ đồ khối trực quan; liệt kê bước, mã giả dùng chữ.', $D);
        $this->fill($L, 'Thuật toán phải kết thúc sau ___ hạn các bước.', [[0, 'hữu']], 'Tính dừng: số bước của thuật toán là hữu hạn.', $D);
        $this->fill($L, 'Trong sơ đồ khối, hình bình hành biểu diễn nhập/___ dữ liệu.', [[0, 'xuất']], 'Hình bình hành là thao tác vào/ra dữ liệu.', $D);
        $this->fill($L, 'Hình ___ trong sơ đồ khối đánh dấu bắt đầu và kết thúc.', [[0, 'oval']], 'Hình oval (elip) là điểm bắt đầu/kết thúc.', $D);
        $this->fill($L, 'Tính xác ___ yêu cầu mỗi bước của thuật toán phải rõ ràng.', [[0, 'định']], 'Tính xác định: không bước nào được mơ hồ.', $D);
        $this->fill($L, 'Mọi bài toán đều gồm đầu vào (input) và đầu ___ (output).', [[0, 'ra']], 'Xác định input, output là bước đầu giải bài toán.', $D);
    }

    // 28. tin-hoc-thpt-12-lop-12-2 — Cấu trúc rẽ nhánh và lặp trong thuật toán (lớp 12, khó)
    private function seedTh28(): void {
        $L = 'tin-hoc-thpt-12-lop-12-2'; $D = 'kho';
        $this->quiz($L, 'Cấu trúc rẽ nhánh dạng thiếu có đặc điểm gì?', ['Chỉ thực hiện khi điều kiện đúng, sai thì bỏ qua', 'Luôn thực hiện cả hai nhánh', 'Không có điều kiện', 'Lặp vô hạn'], 0, 'Rẽ nhánh dạng thiếu: điều kiện sai thì bỏ qua, đi tiếp.', $D);
        $this->quiz($L, 'Vòng lặp với số lần biết trước phù hợp khi nào?', ['Biết trước số lần lặp', 'Không biết điều kiện dừng', 'Muốn lặp vô hạn', 'Không cần lặp'], 0, 'Biết trước số lần lặp thì dùng vòng lặp xác định.', $D);
        $this->quiz($L, 'Vòng lặp với số lần chưa biết trước dừng khi nào?', ['Khi điều kiện không còn thỏa mãn', 'Khi hết pin', 'Khi người dùng tắt máy', 'Không bao giờ dừng'], 0, 'Vòng lặp không xác định dừng khi điều kiện sai.', $D);
        $this->quiz($L, 'Bài toán "nhập điểm đến khi nhập -1 thì dừng" dùng cấu trúc nào?', ['Lặp với số lần chưa biết trước', 'Tuần tự', 'Rẽ nhánh dạng đủ', 'Không dùng cấu trúc'], 0, 'Chưa biết nhập bao nhiêu lần nên dùng lặp không xác định.', $D);
        $this->quiz($L, 'Điều gì xảy ra nếu điều kiện lặp luôn đúng?', ['Vòng lặp vô hạn', 'Chương trình chạy nhanh hơn', 'Kết quả chính xác hơn', 'Máy tự tắt'], 0, 'Điều kiện lặp luôn đúng gây lặp vô hạn, treo chương trình.', $D);
        $this->matching($L, 'Nối mỗi cấu trúc với ví dụ.', [['Tuần tự', 'Rửa rau → thái → nấu'], ['Rẽ nhánh', 'Nếu mưa thì mang ô'], ['Lặp xác định', 'Chép bài 5 lần'], ['Lặp không xác định', 'Nhập đến khi đúng mật khẩu']], 'Mỗi cấu trúc phù hợp một tình huống.', $D);
        $this->matching($L, 'Nối mỗi tình huống với cấu trúc phù hợp.', [['Tính tiền điện theo bậc', 'Rẽ nhánh'], ['In bảng cửu chương', 'Lặp'], ['Nấu cơm theo các bước', 'Tuần tự'], ['Đoán số đến khi đúng', 'Lặp']], 'Chọn cấu trúc đúng giúp thuật toán gọn, rõ.', $D);
        $this->matching($L, 'Nối mỗi dạng lặp với đặc điểm.', [['Lặp xác định', 'Biết trước số lần'], ['Lặp không xác định', 'Dừng theo điều kiện'], ['Lặp vô hạn', 'Lỗi cần tránh'], ['Lặp lồng nhau', 'Vòng lặp trong vòng lặp']], 'Phân biệt các dạng vòng lặp.', $D);
        $this->matching($L, 'Nối mỗi bài toán với cấu trúc chính.', [['Xếp loại học lực', 'Rẽ nhánh'], ['Tính giai thừa n!', 'Lặp'], ['Đổi tiền', 'Tuần tự + rẽ nhánh'], ['Tìm ước chung lớn nhất', 'Lặp']], 'Bài toán phức tạp kết hợp nhiều cấu trúc.', $D);
        $this->matching($L, 'Nối mỗi thành phần vòng lặp với vai trò.', [['Biến đếm', 'Đếm số lần lặp'], ['Điều kiện', 'Quyết định tiếp tục hay dừng'], ['Thân lặp', 'Việc làm mỗi lần lặp'], ['Bước nhảy', 'Thay đổi biến đếm']], 'Vòng lặp gồm biến đếm, điều kiện, thân lặp.', $D);
        $this->sortQ($L, 'Kéo mỗi bài toán vào nhóm "DÙNG RẼ NHÁNH" hoặc "DÙNG LẶP".', [['Xếp loại điểm', 'DÙNG RẼ NHÁNH'], ['Tính tiền taxi theo km', 'DÙNG RẼ NHÁNH'], ['In 100 dòng', 'DÙNG LẶP'], ['Cộng dồn đến khi đủ 100', 'DÙNG LẶP']], 'Rẽ nhánh chọn theo điều kiện; lặp làm lại nhiều lần.', $D);
        $this->sortQ($L, 'Kéo mỗi mô tả vào nhóm "RẼ NHÁNH DẠNG THIẾU" hoặc "DẠNG ĐẦY ĐỦ".', [['Sai thì bỏ qua', 'RẼ NHÁNH DẠNG THIẾU'], ['Chỉ có nhánh đúng', 'RẼ NHÁNH DẠNG THIẾU'], ['Có cả hai nhánh', 'DẠNG ĐẦY ĐỦ'], ['If...else...', 'DẠNG ĐẦY ĐỦ']], 'Dạng thiếu chỉ có nhánh đúng; dạng đủ có cả hai nhánh.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['Lặp vô hạn là lỗi cần tránh', 'ĐÚNG'], ['Ba cấu trúc đủ xây dựng mọi thuật toán', 'ĐÚNG'], ['Rẽ nhánh không cần điều kiện', 'SAI'], ['Tuần tự thực hiện ngẫu nhiên', 'SAI']], 'Ba cấu trúc tuần tự, rẽ nhánh, lặp là nền tảng.', $D);
        $this->sortQ($L, 'Kéo mỗi công việc vào nhóm "MÁY LÀM TỐT (lặp)" hoặc "NGƯỜI LÀM TỐT".', [['Tính tổng 1 triệu số', 'MÁY LÀM TỐT (lặp)'], ['Kiểm tra 1 vạn tài khoản', 'MÁY LÀM TỐT (lặp)'], ['Sáng tác thơ', 'NGƯỜI LÀM TỐT'], ['An ủi bạn buồn', 'NGƯỜI LÀM TỐT']], 'Máy giỏi việc lặp lại; người giỏi việc sáng tạo, cảm xúc.', $D);
        $this->sortQ($L, 'Kéo mỗi vòng lặp vào nhóm "BIẾT TRƯỚC SỐ LẦN" hoặc "CHƯA BIẾT".', [['For i từ 1 đến 10', 'BIẾT TRƯỚC SỐ LẦN'], ['In bảng cửu chương', 'BIẾT TRƯỚC SỐ LẦN'], ['Nhập đến khi đúng', 'CHƯA BIẾT'], ['Đoán số', 'CHƯA BIẾT']], 'Biết trước số lần dùng lặp xác định.', $D);
        $this->fill($L, 'Rẽ nhánh dạng ___ chỉ thực hiện khi điều kiện đúng.', [[0, 'thiếu']], 'Dạng thiếu bỏ qua khi điều kiện sai.', $D);
        $this->fill($L, 'Vòng lặp ___ hạn là lỗi lập trình cần tránh.', [[0, 'vô']], 'Lặp vô hạn khiến chương trình treo.', $D);
        $this->fill($L, 'Ba cấu trúc cơ bản: tuần tự, rẽ nhánh và ___.', [[0, 'lặp']], 'Mọi thuật toán đều xây từ ba cấu trúc này.', $D);
        $this->fill($L, 'Bài toán nhập đến khi đúng mật khẩu dùng lặp ___ biết trước số lần.', [[0, 'chưa']], 'Chưa biết nhập mấy lần nên dùng lặp không xác định.', $D);
        $this->fill($L, 'Trong vòng lặp, ___ kiện quyết định tiếp tục hay dừng.', [[0, 'điều']], 'Điều kiện lặp sai thì vòng lặp kết thúc.', $D);
    }

    // 29. tin-hoc-thpt-12-lop-12-3 — Lệnh print, biến và kiểu dữ liệu trong Python (lớp 12, trung bình)
    private function seedTh29(): void {
        $L = 'tin-hoc-thpt-12-lop-12-3'; $D = 'trung_binh';
        $this->quiz($L, 'Lệnh print("Xin chào", "các bạn") in ra gì?', ['Xin chào các bạn', 'Xin chào,các bạn', 'Lỗi', 'Không in gì'], 0, 'print ngăn cách các giá trị bằng dấu cách.', $D);
        $this->quiz($L, 'Trong Python, lệnh nào gán giá trị 10 cho biến n?', ['n = 10', 'n == 10', '10 = n', 'n := 10'], 0, 'Dấu = dùng để gán giá trị cho biến.', $D);
        $this->quiz($L, 'Biến s = "123" có kiểu dữ liệu gì?', ['int', 'str', 'float', 'bool'], 1, 'Giá trị trong ngoặc kép là chuỗi (str).', $D);
        $this->quiz($L, 'Hàm int("45") trả về gì?', ['Số nguyên 45', 'Chuỗi "45"', 'Lỗi', 'Số 4.5'], 0, 'int() đổi chuỗi số thành số nguyên.', $D);
        $this->quiz($L, 'Tên biến nào sau đây HỢP LỆ trong Python?', ['diem_toan', '2diem', 'diem-toan', 'diem toan'], 0, 'Tên biến không bắt đầu bằng số, không chứa dấu gạch ngang, khoảng trắng.', $D);
        $this->matching($L, 'Nối mỗi lệnh với chức năng.', [['print()', 'In ra màn hình'], ['input()', 'Nhập từ bàn phím'], ['int()', 'Đổi sang số nguyên'], ['float()', 'Đổi sang số thực']], 'Bốn lệnh, hàm cơ bản trong Python.', $D);
        $this->matching($L, 'Nối mỗi giá trị với kiểu dữ liệu.', [['2024', 'int'], ['3.14', 'float'], ['"Python"', 'str'], ['True', 'bool']], 'Python có các kiểu int, float, str, bool.', $D);
        $this->matching($L, 'Nối mỗi đoạn code với kết quả in ra.', [['print(2 + 3)', '5'], ['print("2" + "3")', '23'], ['print(2 * 3)', '6'], ['print(10 / 4)', '2.5']], 'Phép + với chuỗi là nối chuỗi; với số là cộng.', $D);
        $this->matching($L, 'Nối mỗi tên biến với ĐÚNG/SAI trong Python.', [['_diem', 'ĐÚNG'], ['diem1', 'ĐÚNG'], ['1diem', 'SAI'], ['diem-toan', 'SAI']], 'Tên biến không bắt đầu bằng số, không chứa dấu trừ.', $D);
        $this->matching($L, 'Nối mỗi hàm chuyển đổi với kết quả.', [['int(3.9)', '3'], ['float("2.5")', '2.5'], ['str(100)', '"100"'], ['int("abc")', 'Lỗi']], 'Chuyển đổi kiểu cần giá trị phù hợp.', $D);
        $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm "INT", "FLOAT" hoặc "STR".', [['25', 'INT'], ['-7', 'INT'], ['2.5', 'FLOAT'], ['"25"', 'STR']], 'Số nguyên, số thực và chuỗi khác nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi câu lệnh vào nhóm "IN RA" hoặc "NHẬP VÀO".', [['print("Chào")', 'IN RA'], ['print(x)', 'IN RA'], ['input()', 'NHẬP VÀO'], ['name = input()', 'NHẬP VÀO']], 'print in ra; input nhập vào.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['Python phân biệt chữ hoa, chữ thường', 'ĐÚNG'], ['Biến không cần khai báo kiểu trước', 'ĐÚNG'], ['Tên biến được bắt đầu bằng số', 'SAI'], ['# là chú thích một dòng', 'ĐÚNG']], 'Python linh hoạt, phân biệt hoa thường.', $D);
        $this->sortQ($L, 'Kéo mỗi đoạn code vào nhóm "CHẠY ĐÚNG" hoặc "BÁO LỖI".', [['x = 5; print(x)', 'CHẠY ĐÚNG'], ['print("OK")', 'CHẠY ĐÚNG'], ['2x = 5', 'BÁO LỖI'], ['print("a" + 1)', 'BÁO LỖI']], 'Tên biến sai quy tắc hoặc cộng chuỗi với số sẽ lỗi.', $D);
        $this->sortQ($L, 'Kéo mỗi phép toán vào nhóm "SỐ" hoặc "CHUỖI".', [['3 + 4', 'SỐ'], ['10 / 2', 'SỐ'], ['"a" + "b"', 'CHUỖI'], ['"Hi" * 3', 'CHUỖI']], 'Phép + với số là cộng; với chuỗi là nối.', $D);
        $this->fill($L, 'Lệnh ___() dùng để in dữ liệu ra màn hình trong Python.', [[0, 'print']], 'print() là lệnh xuất dữ liệu cơ bản nhất.', $D);
        $this->fill($L, 'Lệnh ___() nhập dữ liệu từ bàn phím, luôn trả về chuỗi.', [[0, 'input']], 'input() đọc một dòng văn bản từ bàn phím.', $D);
        $this->fill($L, 'Hàm ___() đổi chuỗi số thành số thực.', [[0, 'float']], 'float("3.14") cho số thực 3.14.', $D);
        $this->fill($L, 'Kí hiệu ___ dùng để viết chú thích trong Python.', [[0, '#']], 'Mọi thứ sau # trên cùng dòng là chú thích.', $D);
        $this->fill($L, 'Tên biến trong Python không được bắt đầu bằng chữ ___.', [[0, 'số']], 'Tên biến bắt đầu bằng chữ cái hoặc dấu gạch dưới.', $D);
    }

    // 30. tin-hoc-thpt-12-lop-12-4 — Vòng lặp for, while và câu lệnh if trong Python (lớp 12, khó)
    private function seedTh30(): void {
        $L = 'tin-hoc-thpt-12-lop-12-4'; $D = 'kho';
        $this->quiz($L, 'Đoạn code "for i in range(1, 4): print(i)" in ra gì?', ['1 2 3', '1 2 3 4', '0 1 2 3', '4 3 2 1'], 0, 'range(1, 4) tạo dãy 1, 2, 3 (không gồm 4).', $D);
        $this->quiz($L, 'Đoạn code "s = 0; for i in range(1, 6): s += i; print(s)" in ra gì?', ['15', '10', '21', '6'], 0, 'Tổng 1+2+3+4+5 = 15.', $D);
        $this->quiz($L, 'Câu lệnh break trong vòng lặp có tác dụng gì?', ['Thoát khỏi vòng lặp ngay', 'Bỏ qua lần lặp hiện tại', 'Tạm dừng 1 giây', 'In ra màn hình'], 0, 'break thoát hẳn khỏi vòng lặp gần nhất.', $D);
        $this->quiz($L, 'Câu lệnh continue trong vòng lặp có tác dụng gì?', ['Bỏ qua phần còn lại, sang lần lặp tiếp', 'Thoát vòng lặp', 'Lặp vô hạn', 'Dừng chương trình'], 0, 'continue bỏ qua lần lặp hiện tại, sang lần tiếp theo.', $D);
        $this->quiz($L, 'Đoạn code "n = 3; while n > 0: print(n); n -= 1" in ra gì?', ['3 2 1', '3 2 1 0', '1 2 3', 'Lặp vô hạn'], 0, 'n giảm dần 3, 2, 1 rồi dừng khi n = 0.', $D);
        $this->matching($L, 'Nối mỗi câu lệnh với chức năng.', [['if', 'Rẽ nhánh theo điều kiện'], ['for', 'Lặp số lần biết trước'], ['while', 'Lặp theo điều kiện'], ['elif', 'Điều kiện tiếp theo']], 'Bốn câu lệnh điều khiển luồng chương trình.', $D);
        $this->matching($L, 'Nối mỗi đoạn code với số lần lặp.', [['for i in range(5)', '5 lần'], ['for i in range(2, 8)', '6 lần'], ['while n > 0 (n=3)', '3 lần'], ['for i in range(0, 10, 2)', '5 lần']], 'Đếm số lần lặp của từng vòng lặp.', $D);
        $this->matching($L, 'Nối mỗi đoạn code với kết quả.', [['for i in range(3): print(i)', '0 1 2'], ['s=0; for i in range(1,4): s+=i', 's = 6'], ['if 5 > 3: print("A")', 'A'], ['x=4; if x%2==0: print("chẵn")', 'chẵn']], 'Đọc hiểu code Python cơ bản.', $D);
        $this->matching($L, 'Nối mỗi toán tử với ý nghĩa.', [['==', 'So sánh bằng'], ['!=', 'So sánh khác'], ['%', 'Chia lấy dư'], ['//', 'Chia lấy phần nguyên']], 'Toán tử so sánh và số học trong Python.', $D);
        $this->matching($L, 'Nối mỗi tình huống với vòng lặp phù hợp.', [['In bảng cửu chương', 'for'], ['Nhập đến khi đúng', 'while'], ['Duyệt danh sách điểm', 'for'], ['Đoán số', 'while']], 'Biết trước số lần dùng for; chưa biết dùng while.', $D);
        $this->sortQ($L, 'Kéo mỗi đoạn code vào nhóm "VÒNG LẶP FOR" hoặc "WHILE".', [['for i in range(10)', 'VÒNG LẶP FOR'], ['for c in "abc"', 'VÒNG LẶP FOR'], ['while x < 5', 'WHILE'], ['while True', 'WHILE']], 'for lặp theo dãy; while lặp theo điều kiện.', $D);
        $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm "DÙNG IF" hoặc "DÙNG VÒNG LẶP".', [['Xếp loại điểm', 'DÙNG IF'], ['Kiểm tra chẵn lẻ', 'DÙNG IF'], ['Tính tổng 1..100', 'DÙNG VÒNG LẶP'], ['In 10 dòng', 'DÙNG VÒNG LẶP']], 'If rẽ nhánh một lần; vòng lặp làm lại nhiều lần.', $D);
        $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm "ĐÚNG" hoặc "SAI".', [['Thụt đầu dòng xác định khối lệnh', 'ĐÚNG'], ['break thoát khỏi vòng lặp', 'ĐÚNG'], ['range(5) gồm số 5', 'SAI'], ['while cần điều kiện dừng', 'ĐÚNG']], 'Thụt đầu dòng là cú pháp quan trọng của Python.', $D);
        $this->sortQ($L, 'Kéo mỗi đoạn code vào nhóm "IN RA 6" hoặc "KHÔNG".', [['print(2 * 3)', 'IN RA 6'], ['s=0\nfor i in range(1,4): s+=i\nprint(s)', 'IN RA 6'], ['print(2 + 3)', 'KHÔNG'], ['print("6")', 'KHÔNG']], 'Đọc kết quả in ra của code.', $D);
        $this->sortQ($L, 'Kéo mỗi lệnh vào nhóm "ĐIỀU KHIỂN VÒNG LẶP" hoặc "KHÁC".', [['break', 'ĐIỀU KHIỂN VÒNG LẶP'], ['continue', 'ĐIỀU KHIỂN VÒNG LẶP'], ['print', 'KHÁC'], ['input', 'KHÁC']], 'break, continue điều khiển luồng vòng lặp.', $D);
        $this->fill($L, 'Câu lệnh ___ dùng để thoát khỏi vòng lặp ngay lập tức.', [[0, 'break']], 'break kết thúc vòng lặp gần nhất.', $D);
        $this->fill($L, 'Câu lệnh ___ bỏ qua lần lặp hiện tại, sang lần tiếp theo.', [[0, 'continue']], 'continue nhảy sang lần lặp kế tiếp.', $D);
        $this->fill($L, 'range(1, 6) tạo dãy số từ 1 đến ___.', [[0, '5']], 'range(a, b) không bao gồm b.', $D);
        $this->fill($L, 'Trong Python, khối lệnh được xác định bằng cách thụt ___ dòng.', [[0, 'đầu']], 'Thụt đầu dòng thay cho dấu ngoặc nhọn.', $D);
        $this->fill($L, 'Toán tử ___ dùng để chia lấy phần dư trong Python.', [[0, '%']], 'Ví dụ 7 % 3 = 1.', $D);
    }

    // 31. toan-cong-tru-so-tu-nhien — Cộng và trừ số tự nhiên (lớp 6, dễ)
    private function seedTo01(): void {
        $L = 'toan-cong-tru-so-tu-nhien'; $D = 'de';
        $this->quiz($L, '356 + 244 = ?', ['500', '600', '590', '610'], 1, '356 + 244 = 600.', $D);
        $this->quiz($L, '1000 − 456 = ?', ['544', '454', '644', '554'], 0, '1000 − 456 = 544.', $D);
        $this->quiz($L, 'Một lớp có 38 học sinh, trong đó 21 bạn nữ. Số bạn nam là?', ['17', '18', '59', '21'], 0, 'Số bạn nam = 38 − 21 = 17.', $D);
        $this->quiz($L, 'Tính nhanh: 47 + 53 + 28 = ?', ['128', '118', '138', '108'], 0, '47 + 53 = 100, 100 + 28 = 128.', $D);
        $this->quiz($L, 'Số liền trước của 1000 là số nào?', ['999', '1001', '100', '990'], 0, 'Số liền trước của 1000 là 999.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả đúng.', [['245 + 155', '400'], ['800 − 325', '475'], ['136 + 264', '400'], ['1000 − 1', '999']], 'Thực hiện phép cộng, trừ cẩn thận.', $D);
        $this->matching($L, 'Nối mỗi bài toán lời văn với phép tính đúng.', [['Có 250 viên bi, cho bạn 80 viên', '250 − 80'], ['Mua thêm 120 quyển vở', '250 + 120'], ['Ăn hết 15 cái kẹo trong 60 cái', '60 − 15'], ['Được tặng 45 sticker', '60 + 45']], 'Bài toán "cho đi, ăn hết" dùng phép trừ; "thêm, tặng" dùng phép cộng.', $D);
        $this->matching($L, 'Nối mỗi tính chất với ví dụ minh họa.', [['Giao hoán', 'a + b = b + a'], ['Kết hợp', '(a + b) + c = a + (b + c)'], ['Cộng với 0', 'a + 0 = a'], ['Trừ chính nó', 'a − a = 0']], 'Tính chất giúp tính nhanh và kiểm tra kết quả.', $D);
        $this->matching($L, 'Nối mỗi số với số liền sau của nó.', [['199', '200'], ['999', '1000'], ['1009', '1010'], ['2025', '2026']], 'Số liền sau hơn số đã cho 1 đơn vị.', $D);
        $this->matching($L, 'Nối mỗi phép trừ với hiệu của nó.', [['500 − 125', '375'], ['700 − 250', '450'], ['1000 − 999', '1'], ['640 − 40', '600']], 'Trừ cẩn thận từng hàng đơn vị, chục, trăm.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "ĐÚNG" hoặc "SAI".', [['125 + 75 = 200', 'ĐÚNG'], ['300 − 150 = 150', 'ĐÚNG'], ['250 + 250 = 400', 'SAI'], ['900 − 100 = 700', 'SAI']], 'Kiểm tra lại mỗi phép tính trước khi kết luận.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm kết quả "CHẴN" hoặc "LẺ".', [['120 + 34', 'CHẴN'], ['55 + 45', 'CHẴN'], ['101 + 24', 'LẺ'], ['200 − 7', 'LẺ']], 'Chẵn ± chẵn = chẵn; lẻ ± chẵn = lẻ.', $D);
        $this->sortQ($L, 'Kéo mỗi bài toán vào nhóm dùng "PHÉP CỘNG" hoặc "PHÉP TRỪ".', [['Tổng số sách hai ngăn', 'PHÉP CỘNG'], ['Số tiền còn lại sau khi mua', 'PHÉP TRỪ'], ['Số học sinh cả lớp', 'PHÉP CỘNG'], ['Số bi cho bạn', 'PHÉP TRỪ']], '"Tổng, thêm" dùng cộng; "còn lại, cho đi" dùng trừ.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "LỚN HƠN 500" hoặc "NHỎ HƠN 500".', [['750', 'LỚN HƠN 500'], ['999', 'LỚN HƠN 500'], ['499', 'NHỎ HƠN 500'], ['250', 'NHỎ HƠN 500']], 'So sánh các số tự nhiên theo hàng trăm, chục, đơn vị.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "CÓ NHỚ" hoặc "KHÔNG NHỚ" khi cộng.', [['58 + 47', 'CÓ NHỚ'], ['36 + 25', 'CÓ NHỚ'], ['120 + 30', 'KHÔNG NHỚ'], ['200 + 150', 'KHÔNG NHỚ']], 'Cộng có nhớ khi tổng hàng đơn vị từ 10 trở lên.', $D);
        $this->fill($L, '356 + 244 = ___.', [[0, '600']], '356 + 244 = 600.', $D);
        $this->fill($L, 'Số liền sau của 999 là ___.', [[0, '1000']], 'Số liền sau hơn số đã cho 1 đơn vị.', $D);
        $this->fill($L, '1000 − ___ = 750.', [[0, '250']], 'Số trừ = 1000 − 750 = 250.', $D);
        $this->fill($L, 'Tính chất giao hoán: a + b = b + ___.', [[0, 'a']], 'Đổi chỗ các số hạng, tổng không đổi.', $D);
        $this->fill($L, 'Một cửa hàng bán 180 kg gạo buổi sáng, 240 kg buổi chiều. Cả ngày bán ___ kg.', [[0, '420']], '180 + 240 = 420 kg.', $D);
    }

    // 32. toan-nhan-chia-so-tu-nhien — Nhân và chia số tự nhiên (lớp 6, trung bình)
    private function seedTo02(): void {
        $L = 'toan-nhan-chia-so-tu-nhien'; $D = 'trung_binh';
        $this->quiz($L, '12 × 15 = ?', ['170', '180', '165', '195'], 1, '12 × 15 = 180.', $D);
        $this->quiz($L, '144 : 12 = ?', ['11', '12', '14', '10'], 1, '144 : 12 = 12.', $D);
        $this->quiz($L, 'Phép chia 50 cho 6 cho thương và số dư nào?', ['Thương 8, dư 2', 'Thương 7, dư 8', 'Thương 8, dư 0', 'Thương 9, dư 2'], 0, '50 = 6 × 8 + 2 nên thương 8, dư 2.', $D);
        $this->quiz($L, 'Giá trị của 15 + 20 : 4 là?', ['20', '35', '10', '25'], 0, 'Nhân chia trước: 20 : 4 = 5, rồi 15 + 5 = 20.', $D);
        $this->quiz($L, 'Một tá bút chì có 12 chiếc. 5 tá có bao nhiêu chiếc?', ['60', '50', '72', '48'], 0, '12 × 5 = 60 chiếc.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả đúng.', [['15 × 6', '90'], ['96 : 8', '12'], ['25 × 8', '200'], ['180 : 9', '20']], 'Nhân chia cẩn thận từng bước.', $D);
        $this->matching($L, 'Nối mỗi phép chia với thương và số dư.', [['47 : 5', 'Thương 9, dư 2'], ['38 : 4', 'Thương 9, dư 2'], ['53 : 6', 'Thương 8, dư 5'], ['29 : 3', 'Thương 9, dư 2']], 'Số dư luôn nhỏ hơn số chia.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với phép tính làm trước.', [['12 + 3 × 4', 'Nhân trước'], ['(12 + 3) × 4', 'Ngoặc trước'], ['100 − 20 : 5', 'Chia trước'], ['2 × (5 + 5)', 'Ngoặc trước']], 'Thứ tự: ngoặc → nhân chia → cộng trừ.', $D);
        $this->matching($L, 'Nối mỗi bài toán với phép tính đúng.', [['4 hộp, mỗi hộp 12 cái', '12 × 4'], ['Chia 60 kẹo cho 5 bạn', '60 : 5'], ['Gấp 3 lần 25', '25 × 3'], ['100 chia đều 4 phần', '100 : 4']], '"Mỗi, gấp" dùng nhân; "chia đều" dùng chia.', $D);
        $this->matching($L, 'Nối mỗi tính chất với ví dụ.', [['Giao hoán của nhân', '3 × 5 = 5 × 3'], ['Kết hợp của nhân', '(2 × 3) × 4 = 2 × (3 × 4)'], ['Nhân với 1', '7 × 1 = 7'], ['Nhân với 0', '9 × 0 = 0']], 'Tính chất của phép nhân giúp tính nhanh.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "ĐÚNG" hoặc "SAI".', [['12 × 5 = 60', 'ĐÚNG'], ['100 : 4 = 25', 'ĐÚNG'], ['15 × 4 = 50', 'SAI'], ['81 : 9 = 8', 'SAI']], 'Kiểm tra bảng nhân, chia trước khi kết luận.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm kết quả "CHẴN" hoặc "LẺ".', [['13 × 4', 'CHẴN'], ['25 × 6', 'CHẴN'], ['11 × 7', 'LẺ'], ['9 × 9', 'LẺ']], 'Tích có ít nhất một thừa số chẵn thì chẵn.', $D);
        $this->sortQ($L, 'Kéo mỗi phép chia vào nhóm "CHIA HẾT" hoặc "CÓ DƯ".', [['48 : 6', 'CHIA HẾT'], ['100 : 25', 'CHIA HẾT'], ['50 : 6', 'CÓ DƯ'], ['37 : 5', 'CÓ DƯ']], 'Chia hết khi số dư bằng 0.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "CHIA HẾT CHO 3" hoặc "KHÔNG".', [['27', 'CHIA HẾT CHO 3'], ['45', 'CHIA HẾT CHO 3'], ['28', 'KHÔNG'], ['50', 'KHÔNG']], 'Số chia hết cho 3 khi tổng các chữ số chia hết cho 3.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "LÀM NHÂN TRƯỚC" hoặc "LÀM CỘNG TRƯỚC".', [['5 + 2 × 3', 'LÀM NHÂN TRƯỚC'], ['20 − 4 × 2', 'LÀM NHÂN TRƯỚC'], ['(5 + 2) × 3', 'LÀM CỘNG TRƯỚC'], ['(20 − 4) × 2', 'LÀM CỘNG TRƯỚC']], 'Nhân chia trước, cộng trừ sau; ngoặc trước nhất.', $D);
        $this->fill($L, '12 × 15 = ___.', [[0, '180']], '12 × 15 = 180.', $D);
        $this->fill($L, '144 : 12 = ___.', [[0, '12']], '144 : 12 = 12.', $D);
        $this->fill($L, 'Trong phép chia 50 cho 6, số dư là ___.', [[0, '2']], '50 = 6 × 8 + 2.', $D);
        $this->fill($L, 'Tính 30 − 12 : 3: ta thực hiện phép ___ trước.', [[0, 'chia']], 'Nhân chia trước, cộng trừ sau: 12 : 3 = 4, 30 − 4 = 26.', $D);
        $this->fill($L, 'Một lớp xếp 8 hàng, mỗi hàng 6 bạn. Cả lớp có ___ bạn.', [[0, '48']], '8 × 6 = 48 bạn.', $D);
    }

    // 33. toan-cong-tru-phan-so — Cộng và trừ phân số (lớp 6, trung bình)
    private function seedTo03(): void {
        $L = 'toan-cong-tru-phan-so'; $D = 'trung_binh';
        $this->quiz($L, '2/7 + 3/7 = ?', ['5/7', '5/14', '1', '6/7'], 0, 'Cùng mẫu số: cộng tử số, giữ mẫu số: 5/7.', $D);
        $this->quiz($L, '5/8 − 3/8 = ? (rút gọn)', ['2/8', '1/4', '1/2', '2/5'], 1, '5/8 − 3/8 = 2/8 = 1/4.', $D);
        $this->quiz($L, '1/3 + 1/6 = ?', ['2/9', '1/2', '2/6', '1/9'], 1, 'Quy đồng: 2/6 + 1/6 = 3/6 = 1/2.', $D);
        $this->quiz($L, '3/4 − 1/2 = ?', ['2/2', '1/4', '1/2', '2/4'], 1, '3/4 − 2/4 = 1/4.', $D);
        $this->quiz($L, 'An ăn 1/4 cái bánh, Bình ăn 2/4 cái bánh. Cả hai ăn mấy phần bánh?', ['3/4', '3/8', '1/2', '1'], 0, '1/4 + 2/4 = 3/4 cái bánh.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả đã rút gọn.', [['1/6 + 2/6', '1/2'], ['7/8 − 3/8', '1/2'], ['2/5 + 1/5', '3/5'], ['4/9 − 1/9', '1/3']], 'Cộng trừ cùng mẫu rồi rút gọn kết quả.', $D);
        $this->matching($L, 'Nối mỗi phép tính khác mẫu với kết quả.', [['1/2 + 1/3', '5/6'], ['3/4 − 1/3', '5/12'], ['2/3 + 1/6', '5/6'], ['5/6 − 1/2', '1/3']], 'Khác mẫu số phải quy đồng trước.', $D);
        $this->matching($L, 'Nối mỗi phân số với phân số bằng nó.', [['1/2', '2/4'], ['2/3', '4/6'], ['3/5', '6/10'], ['1/4', '2/8']], 'Nhân cả tử và mẫu với cùng một số được phân số bằng nhau.', $D);
        $this->matching($L, 'Nối mỗi bài toán với phép tính đúng.', [['Còn lại sau khi ăn 1/3', '1 − 1/3'], ['Tổng hai phần việc', '1/4 + 1/4'], ['Phần hơn của 3/5 so với 1/5', '3/5 − 1/5'], ['Nửa còn lại của 1/2', '1/2 − 1/4']], 'Đọc kỹ bài toán để chọn phép tính đúng.', $D);
        $this->matching($L, 'Nối mỗi phân số với cách đọc đúng.', [['3/4', 'Ba phần tư'], ['2/5', 'Hai phần năm'], ['5/6', 'Năm phần sáu'], ['1/8', 'Một phần tám']], 'Đọc tử số trước, mẫu số sau.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "ĐÚNG" hoặc "SAI".', [['1/4 + 2/4 = 3/4', 'ĐÚNG'], ['5/6 − 1/6 = 2/3', 'ĐÚNG'], ['1/2 + 1/2 = 2/4', 'SAI'], ['3/5 − 1/5 = 2/5', 'ĐÚNG']], 'Cùng mẫu: cộng/trừ tử số, giữ nguyên mẫu số.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp phân số vào nhóm "CÙNG MẪU" hoặc "KHÁC MẪU".', [['2/7 và 5/7', 'CÙNG MẪU'], ['1/3 và 2/3', 'CÙNG MẪU'], ['1/2 và 1/3', 'KHÁC MẪU'], ['3/4 và 2/5', 'KHÁC MẪU']], 'Khác mẫu số phải quy đồng trước khi cộng trừ.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "LỚN HƠN 1/2" hoặc "NHỎ HƠN 1/2".', [['3/4', 'LỚN HƠN 1/2'], ['4/5', 'LỚN HƠN 1/2'], ['1/4', 'NHỎ HƠN 1/2'], ['2/5', 'NHỎ HƠN 1/2']], 'So sánh tử số với một nửa mẫu số.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "TỐI GIẢN" hoặc "RÚT GỌN ĐƯỢC".', [['3/4', 'TỐI GIẢN'], ['2/5', 'TỐI GIẢN'], ['4/6', 'RÚT GỌN ĐƯỢC'], ['6/8', 'RÚT GỌN ĐƯỢC']], 'Phân số tối giản khi tử và mẫu không cùng chia hết cho số nào > 1.', $D);
        $this->sortQ($L, 'Kéo mỗi kết quả vào nhóm "BẰNG 1" hoặc "KHÁC 1".', [['1/3 + 2/3', 'BẰNG 1'], ['2/5 + 3/5', 'BẰNG 1'], ['1/4 + 1/4', 'KHÁC 1'], ['3/8 + 3/8', 'KHÁC 1']], 'Tổng các phần bằng cả cái bánh thì bằng 1.', $D);
        $this->fill($L, '2/7 + 3/7 = ___.', [[0, '5/7']], 'Cùng mẫu số: cộng tử số, giữ mẫu số.', $D);
        $this->fill($L, 'Muốn cộng hai phân số khác mẫu số, ta phải ___ đồng mẫu số trước.', [[0, 'quy']], 'Quy đồng đưa về cùng mẫu số rồi cộng tử số.', $D);
        $this->fill($L, '1/2 + 1/4 = ___.', [[0, '3/4']], 'Quy đồng: 2/4 + 1/4 = 3/4.', $D);
        $this->fill($L, 'Rút gọn phân số 6/8 ta được ___.', [[0, '3/4']], 'Chia cả tử và mẫu cho 2: 6/8 = 3/4.', $D);
        $this->fill($L, 'Một chai nước đã uống 3/5 chai. Phần còn lại là ___ chai.', [[0, '2/5']], '1 − 3/5 = 2/5 chai.', $D);
    }

    // 34. toan-nhan-chia-phan-so — Nhân và chia phân số (lớp 6, trung bình)
    private function seedTo04(): void {
        $L = 'toan-nhan-chia-phan-so'; $D = 'trung_binh';
        $this->quiz($L, '1/2 × 2/5 = ?', ['2/7', '1/5', '2/10', '3/7'], 1, 'Nhân tử với tử, mẫu với mẫu: 2/10 = 1/5.', $D);
        $this->quiz($L, '3/4 : 3 = ?', ['1/4', '9/4', '3/7', '1/2'], 0, '3/4 : 3 = 3/4 × 1/3 = 3/12 = 1/4.', $D);
        $this->quiz($L, 'Phân số nghịch đảo của 2/5 là?', ['5/2', '2/5', '−2/5', '1/5'], 0, 'Nghịch đảo của 2/5 là 5/2.', $D);
        $this->quiz($L, '4/7 × 0 = ?', ['4/7', '0', '1', '7/4'], 1, 'Số nào nhân với 0 cũng bằng 0.', $D);
        $this->quiz($L, '2/3 của 12 bằng bao nhiêu?', ['8', '6', '18', '4'], 0, '2/3 × 12 = 24/3 = 8.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả đúng.', [['2/5 × 5/6', '1/3'], ['3/8 : 3/4', '1/2'], ['1/3 × 9', '3'], ['5/6 : 5', '1/6']], 'Nhân tử với tử, mẫu với mẫu; chia thì nhân nghịch đảo.', $D);
        $this->matching($L, 'Nối mỗi phân số với phân số nghịch đảo.', [['3/4', '4/3'], ['5/2', '2/5'], ['7', '1/7'], ['1/9', '9']], 'Nghịch đảo đổi chỗ tử số và mẫu số.', $D);
        $this->matching($L, 'Nối mỗi bài toán với phép tính đúng.', [['1/2 của 30', '1/2 × 30'], ['Chia 2/3 cho 4', '2/3 : 4'], ['Gấp 3 lần 2/7', '2/7 × 3'], ['3/5 chia thành 2 phần', '3/5 : 2']], '"Của" nghĩa là nhân; "chia thành phần" là phép chia.', $D);
        $this->matching($L, 'Nối mỗi phép chia với phép nhân tương đương.', [['2/3 : 4/5', '2/3 × 5/4'], ['5/6 : 2', '5/6 × 1/2'], ['7/8 : 7', '7/8 × 1/7'], ['1/2 : 1/4', '1/2 × 4']], 'Chia một phân số bằng nhân với nghịch đảo của nó.', $D);
        $this->matching($L, 'Nối mỗi phân số với giá trị so với 1.', [['5/4', 'Lớn hơn 1'], ['7/7', 'Bằng 1'], ['3/8', 'Nhỏ hơn 1'], ['9/5', 'Lớn hơn 1']], 'Tử lớn hơn mẫu thì phân số lớn hơn 1.', $D);
        $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm "LỚN HƠN 1" hoặc "NHỎ HƠN 1".', [['7/5', 'LỚN HƠN 1'], ['4/3', 'LỚN HƠN 1'], ['2/7', 'NHỎ HƠN 1'], ['5/9', 'NHỎ HƠN 1']], 'Tử số lớn hơn mẫu số thì phân số lớn hơn 1.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "NHÂN" hoặc "CHIA".', [['2/3 × 4/5', 'NHÂN'], ['1/2 × 6', 'NHÂN'], ['3/4 : 2', 'CHIA'], ['5/6 : 5/3', 'CHIA']], 'Nhận biết phép tính qua dấu × và :.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "ĐÚNG" hoặc "SAI".', [['1/2 × 2/3 = 1/3', 'ĐÚNG'], ['3/4 : 1/2 = 3/2', 'ĐÚNG'], ['2/5 × 5/2 = 1', 'ĐÚNG'], ['1/3 : 3 = 1', 'SAI']], 'Nhân nghịch đảo, rút gọn cẩn thận.', $D);
        $this->sortQ($L, 'Kéo mỗi kết quả vào nhóm "LÀ SỐ NGUYÊN" hoặc "KHÔNG".', [['2/3 × 9', 'LÀ SỐ NGUYÊN'], ['3/4 × 8', 'LÀ SỐ NGUYÊN'], ['1/2 × 5', 'KHÔNG'], ['2/7 × 3', 'KHÔNG']], 'Nhân phân số với số nguyên có thể ra số nguyên.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "NGHỊCH ĐẢO ĐÚNG" hoặc "SAI".', [['2/3 ↔ 3/2', 'NGHỊCH ĐẢO ĐÚNG'], ['4 ↔ 1/4', 'NGHỊCH ĐẢO ĐÚNG'], ['2/5 ↔ 5/3', 'SAI'], ['7/8 ↔ 8/6', 'SAI']], 'Nghịch đảo đổi chỗ tử và mẫu cho nhau.', $D);
        $this->fill($L, '1/2 × 2/5 = ___.', [[0, '1/5']], 'Nhân tử với tử, mẫu với mẫu rồi rút gọn: 2/10 = 1/5.', $D);
        $this->fill($L, 'Phân số nghịch đảo của 3/7 là ___.', [[0, '7/3']], 'Nghịch đảo đổi chỗ tử số và mẫu số.', $D);
        $this->fill($L, 'Muốn chia một phân số, ta nhân với phân số nghịch ___ của số chia.', [[0, 'đảo']], 'a/b : c/d = a/b × d/c.', $D);
        $this->fill($L, '3/4 của 20 bằng ___.', [[0, '15']], '3/4 × 20 = 60/4 = 15.', $D);
        $this->fill($L, '5/6 : 5 = ___.', [[0, '1/6']], '5/6 × 1/5 = 5/30 = 1/6.', $D);
    }

    // 35. toan-thu-gon-don-thuc — Thu gọn đơn thức (lớp 7, trung bình)
    private function seedTo05(): void {
        $L = 'toan-thu-gon-don-thuc'; $D = 'trung_binh';
        $this->quiz($L, 'Thu gọn đơn thức 3x · 4x ta được?', ['7x', '12x²', '12x', '7x²'], 1, 'Nhân hệ số: 3 × 4 = 12; nhân phần biến: x × x = x².', $D);
        $this->quiz($L, 'Bậc của đơn thức 2x³y là?', ['3', '4', '5', '2'], 1, 'Bậc = tổng số mũ của biến: 3 + 1 = 4.', $D);
        $this->quiz($L, 'Đơn thức nào đồng dạng với 3x²y?', ['5x²y', '3xy²', '5x²', '3y²'], 0, 'Đồng dạng khi cùng phần biến x²y.', $D);
        $this->quiz($L, 'Kết quả của 8xy − 3xy là?', ['5xy', '11xy', '5x²y²', '11'], 0, 'Trừ hệ số: 8 − 3 = 5, giữ phần biến xy.', $D);
        $this->quiz($L, 'Hệ số của đơn thức −7x²y³ là?', ['−7', '7', '2', '5'], 0, 'Hệ số là phần số đứng trước biến: −7.', $D);
        $this->matching($L, 'Nối mỗi phép nhân đơn thức với kết quả thu gọn.', [['2x · 5x', '10x²'], ['3y · 2y²', '6y³'], ['4x · 3y', '12xy'], ['x² · x³', 'x⁵']], 'Nhân hệ số với hệ số, biến với biến.', $D);
        $this->matching($L, 'Nối mỗi đơn thức với bậc của nó.', [['5x²', 'Bậc 2'], ['3xy²', 'Bậc 3'], ['−2x³y²', 'Bậc 5'], ['7', 'Bậc 0']], 'Bậc là tổng số mũ của các biến.', $D);
        $this->matching($L, 'Nối mỗi phép cộng/trừ đơn thức đồng dạng với kết quả.', [['4x + 6x', '10x'], ['9y² − 4y²', '5y²'], ['2xy + 5xy', '7xy'], ['10x³ − x³', '9x³']], 'Cộng/trừ hệ số, giữ nguyên phần biến.', $D);
        $this->matching($L, 'Nối mỗi đơn thức với phần biến của nó.', [['6x²y', 'x²y'], ['−3xy³', 'xy³'], ['5x', 'x'], ['12', 'không có biến']], 'Phần biến là phần chữ trong đơn thức.', $D);
        $this->matching($L, 'Nối mỗi cặp đơn thức với nhận xét.', [['3x và 5x', 'Đồng dạng'], ['2x² và 2x³', 'Không đồng dạng'], ['4xy và −xy', 'Đồng dạng'], ['x²y và xy²', 'Không đồng dạng']], 'Đồng dạng khi cùng phần biến.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm "ĐỒNG DẠNG VỚI 2x²" hoặc "KHÔNG".', [['5x²', 'ĐỒNG DẠNG VỚI 2x²'], ['−3x²', 'ĐỒNG DẠNG VỚI 2x²'], ['2x³', 'KHÔNG'], ['2xy', 'KHÔNG']], 'Cùng phần biến x² mới đồng dạng.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm "BẬC 3" hoặc "KHÁC BẬC 3".', [['4x²y', 'BẬC 3'], ['x³', 'BẬC 3'], ['5x²', 'KHÁC BẬC 3'], ['2xy', 'KHÁC BẬC 3']], 'Bậc 3 khi tổng số mũ bằng 3.', $D);
        $this->sortQ($L, 'Kéo mỗi phép thu gọn vào nhóm "ĐÚNG" hoặc "SAI".', [['3x + 2x = 5x', 'ĐÚNG'], ['4y − y = 3y', 'ĐÚNG'], ['2x + 3y = 5xy', 'SAI'], ['x² + x² = 2x²', 'ĐÚNG']], 'Chỉ cộng/trừ được đơn thức đồng dạng.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm "HỆ SỐ DƯƠNG" hoặc "HỆ SỐ ÂM".', [['5x²', 'HỆ SỐ DƯƠNG'], ['3xy', 'HỆ SỐ DƯƠNG'], ['−2x', 'HỆ SỐ ÂM'], ['−7y³', 'HỆ SỐ ÂM']], 'Dấu của hệ số quyết định dấu đơn thức.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "ĐƠN THỨC" hoặc "KHÔNG PHẢI".', [['5x²y', 'ĐƠN THỨC'], ['−3x', 'ĐƠN THỨC'], ['x + y', 'KHÔNG PHẢI'], ['2/x', 'KHÔNG PHẢI']], 'Đơn thức là tích của số và biến.', $D);
        $this->fill($L, 'Thu gọn: 3x · 4x = ___x².', [[0, '12']], 'Nhân hệ số 3 × 4 = 12, x × x = x².', $D);
        $this->fill($L, 'Bậc của đơn thức 5x²y³ là ___.', [[0, '5']], 'Bậc = 2 + 3 = 5.', $D);
        $this->fill($L, 'Hai đơn thức đồng dạng có cùng phần ___.', [[0, 'biến']], 'Cùng phần biến và hệ số khác 0.', $D);
        $this->fill($L, '6xy − 2xy = ___xy.', [[0, '4']], 'Trừ hệ số: 6 − 2 = 4.', $D);
        $this->fill($L, 'Hệ số của đơn thức −9x³ là ___.', [[0, '−9']], 'Hệ số là −9.', $D);
    }

    // 36. toan-gia-tri-bieu-thuc — Giá trị của biểu thức đại số (lớp 7, khó)
    private function seedTo06(): void {
        $L = 'toan-gia-tri-bieu-thuc'; $D = 'kho';
        $this->quiz($L, 'Với x = 3, giá trị của biểu thức x² − 2x là?', ['3', '0', '15', '9'], 0, 'Thay x = 3: 9 − 6 = 3.', $D);
        $this->quiz($L, 'Với a = 4, giá trị của biểu thức 3a² là?', ['48', '24', '12', '36'], 0, '3 × 4² = 3 × 16 = 48.', $D);
        $this->quiz($L, 'Với x = 2, y = 5, giá trị của biểu thức 2x + 3y là?', ['19', '29', '21', '25'], 0, '2 × 2 + 3 × 5 = 4 + 15 = 19.', $D);
        $this->quiz($L, 'Với x = −2, giá trị của biểu thức x² là?', ['−4', '4', '0', '−2'], 1, '(−2)² = 4.', $D);
        $this->quiz($L, 'Với m = 3, n = 2, giá trị của biểu thức m³ − n² là?', ['23', '19', '27', '5'], 0, '27 − 4 = 23.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị tại x = 5.', [['2x + 1', '11'], ['x² − 10', '15'], ['30 − 2x', '20'], ['x² + x', '30']], 'Thay x = 5 rồi tính theo thứ tự.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị tại a = 3, b = 2.', [['a + b', '5'], ['2a − b', '4'], ['ab', '6'], ['a² + b²', '13']], 'Thay cả a và b rồi tính.', $D);
        $this->matching($L, 'Nối mỗi bước với thứ tự tính giá trị biểu thức.', [['Thay giá trị vào biến', 'Bước 1'], ['Tính lũy thừa', 'Bước 2'], ['Nhân, chia', 'Bước 3'], ['Cộng, trừ', 'Bước 4']], 'Tính theo đúng thứ tự ưu tiên.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị tại x = −1.', [['x²', '1'], ['2x', '−2'], ['x³', '−1'], ['5 − x', '6']], 'Chú ý dấu khi thay số âm.', $D);
        $this->matching($L, 'Nối mỗi cụm từ với biểu thức đại số.', [['Bình phương của x', 'x²'], ['Gấp ba lần y', '3y'], ['Tổng của a và b', 'a + b'], ['Hiệu của m và n', 'm − n']], 'Dịch lời văn thành biểu thức.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm làm "TRƯỚC" hoặc "SAU" (tại x = 2).', [['2² (lũy thừa)', 'TRƯỚC'], ['3 × x (nhân)', 'TRƯỚC'], ['+ 5 (cộng)', 'SAU'], ['− 1 (trừ)', 'SAU']], 'Lũy thừa → nhân chia → cộng trừ.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Thay x = 2 vào 3x được 6', 'ĐÚNG'], ['(−3)² = −9', 'SAI'], ['Với x = 0, 5x + 2 = 2', 'ĐÚNG'], ['x² luôn không âm', 'ĐÚNG']], 'Chú ý dấu ngoặc với số âm.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "MỘT BIẾN" hoặc "HAI BIẾN".', [['3x + 2', 'MỘT BIẾN'], ['x² − 5', 'MỘT BIẾN'], ['2x + 3y', 'HAI BIẾN'], ['ab − 1', 'HAI BIẾN']], 'Đếm số biến khác nhau trong biểu thức.', $D);
        $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm "DƯƠNG" hoặc "ÂM" (tại x = −2).', [['x²', 'DƯƠNG'], ['−x', 'DƯƠNG'], ['3x', 'ÂM'], ['x − 1', 'ÂM']], 'Thay x = −2: x² = 4 > 0; 3x = −6 < 0.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức (tại x = 4) vào nhóm "BẰNG 10" hoặc "KHÁC 10".', [['2x + 2', 'BẰNG 10'], ['x + 6', 'BẰNG 10'], ['3x', 'KHÁC 10'], ['x² − 6', 'BẰNG 10']], 'Thay x = 4: 2×4+2 = 10; 3×4 = 12.', $D);
        $this->fill($L, 'Với x = 6, giá trị của biểu thức 3x − 5 là ___.', [[0, '13']], '3 × 6 − 5 = 18 − 5 = 13.', $D);
        $this->fill($L, 'Với a = 3, b = 4, giá trị của biểu thức 2a + b² là ___.', [[0, '22']], '2 × 3 + 16 = 22.', $D);
        $this->fill($L, 'Với x = −3, giá trị của biểu thức x² + 1 là ___.', [[0, '10']], '(−3)² + 1 = 9 + 1 = 10.', $D);
        $this->fill($L, 'Muốn tính giá trị biểu thức, trước hết ta ___ giá trị vào biến.', [[0, 'thay']], 'Thay số vào chỗ của biến rồi tính.', $D);
        $this->fill($L, 'Với x = 10, giá trị của biểu thức x : 2 + 7 là ___.', [[0, '12']], '10 : 2 + 7 = 5 + 7 = 12.', $D);
    }

    // 37. toan-goc-va-duong-thang — Góc và đường thẳng (lớp 7, dễ)
    private function seedTo07(): void {
        $L = 'toan-goc-va-duong-thang'; $D = 'de';
        $this->quiz($L, 'Góc tù có số đo như thế nào?', ['Lớn hơn 90° và nhỏ hơn 180°', 'Bằng 90°', 'Nhỏ hơn 90°', 'Bằng 180°'], 0, 'Góc tù: 90° < số đo < 180°.', $D);
        $this->quiz($L, 'Góc 45° là loại góc nào?', ['Góc nhọn', 'Góc vuông', 'Góc tù', 'Góc bẹt'], 0, '45° < 90° nên là góc nhọn.', $D);
        $this->quiz($L, 'Dụng cụ nào dùng để đo góc?', ['Thước kẻ', 'Thước đo góc (thước đo độ)', 'Compa', 'Êke'], 1, 'Thước đo góc dùng để đo số đo của góc.', $D);
        $this->quiz($L, 'Hai đường thẳng cắt nhau tạo ra mấy góc?', ['2 góc', '4 góc', '1 góc', '3 góc'], 1, 'Hai đường thẳng cắt nhau tạo ra 4 góc.', $D);
        $this->quiz($L, 'Góc 120° là loại góc nào?', ['Góc nhọn', 'Góc vuông', 'Góc tù', 'Góc bẹt'], 2, '120° nằm giữa 90° và 180° nên là góc tù.', $D);
        $this->matching($L, 'Nối mỗi số đo với loại góc.', [['30°', 'Góc nhọn'], ['90°', 'Góc vuông'], ['150°', 'Góc tù'], ['180°', 'Góc bẹt']], 'Phân loại góc theo số đo.', $D);
        $this->matching($L, 'Nối mỗi khái niệm với mô tả đúng.', [['Góc nhọn', 'Nhỏ hơn 90°'], ['Góc vuông', 'Bằng 90°'], ['Góc tù', 'Lớn hơn 90°, nhỏ hơn 180°'], ['Góc bẹt', 'Bằng 180°']], 'Bốn loại góc cơ bản.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ với công dụng.', [['Thước đo góc', 'Đo số đo góc'], ['Êke', 'Vẽ góc vuông'], ['Compa', 'Vẽ đường tròn'], ['Thước kẻ', 'Vẽ đoạn thẳng']], 'Mỗi dụng cụ hình học có công dụng riêng.', $D);
        $this->matching($L, 'Nối mỗi thành phần của góc xOy với tên gọi.', [['O', 'Đỉnh'], ['Ox', 'Cạnh'], ['Oy', 'Cạnh'], ['xOy', 'Góc']], 'Góc gồm đỉnh và hai cạnh.', $D);
        $this->matching($L, 'Nối mỗi cặp đường thẳng với tên gọi.', [['Không bao giờ cắt nhau', 'Song song'], ['Cắt nhau tạo góc 90°', 'Vuông góc'], ['Cắt nhau không vuông góc', 'Cắt nhau'], ['Trùng nhau', 'Trùng nhau']], 'Các vị trí tương đối của hai đường thẳng.', $D);
        $this->sortQ($L, 'Kéo mỗi góc vào nhóm "GÓC NHỌN" hoặc "GÓC TÙ".', [['35°', 'GÓC NHỌN'], ['80°', 'GÓC NHỌN'], ['100°', 'GÓC TÙ'], ['170°', 'GÓC TÙ']], 'Nhỏ hơn 90° là nhọn; lớn hơn 90° (nhỏ hơn 180°) là tù.', $D);
        $this->sortQ($L, 'Kéo mỗi số đo vào nhóm "NHỎ HƠN 90°" hoặc "LỚN HƠN 90°".', [['45°', 'NHỎ HƠN 90°'], ['89°', 'NHỎ HƠN 90°'], ['91°', 'LỚN HƠN 90°'], ['135°', 'LỚN HƠN 90°']], 'So sánh số đo với 90°.', $D);
        $this->sortQ($L, 'Kéo mỗi hình vào nhóm "CÓ GÓC VUÔNG" hoặc "KHÔNG".', [['Hình vuông', 'CÓ GÓC VUÔNG'], ['Hình chữ nhật', 'CÓ GÓC VUÔNG'], ['Hình tròn', 'KHÔNG'], ['Hình bình hành xiên', 'KHÔNG']], 'Hình vuông, chữ nhật có 4 góc vuông.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp đường thẳng vào nhóm "SONG SONG" hoặc "KHÔNG".', [['Hai mép thước kẻ', 'SONG SONG'], ['Đường ray xe lửa', 'SONG SONG'], ['Hai đường cắt nhau', 'KHÔNG'], ['Hai đường vuông góc', 'KHÔNG']], 'Song song là không bao giờ cắt nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi mô tả vào nhóm "GÓC VUÔNG" hoặc "GÓC BẸT".', [['Số đo 90°', 'GÓC VUÔNG'], ['Hai cạnh vuông góc', 'GÓC VUÔNG'], ['Số đo 180°', 'GÓC BẸT'], ['Hai cạnh là hai tia đối nhau', 'GÓC BẸT']], 'Góc vuông 90°; góc bẹt 180°.', $D);
        $this->fill($L, 'Góc có số đo lớn hơn 90° và nhỏ hơn 180° gọi là góc ___.', [[0, 'tù']], 'Góc tù nằm giữa góc vuông và góc bẹt.', $D);
        $this->fill($L, 'Dụng cụ dùng để đo số đo của góc là thước đo ___.', [[0, 'góc']], 'Thước đo góc (thước đo độ) có vạch chia độ.', $D);
        $this->fill($L, 'Hai đường thẳng không bao giờ cắt nhau gọi là hai đường thẳng song ___.', [[0, 'song']], 'Song song là vị trí đặc biệt của hai đường thẳng.', $D);
        $this->fill($L, 'Góc tạo bởi hai tia đối nhau có số đo ___ độ.', [[0, '180']], 'Hai tia đối nhau tạo thành góc bẹt 180°.', $D);
        $this->fill($L, 'Trong góc xOy, điểm O gọi là ___ của góc.', [[0, 'đỉnh']], 'Đỉnh là điểm chung của hai cạnh.', $D);
    }

    // 38. toan-tam-giac — Tam giác và các góc (lớp 7, trung bình)
    private function seedTo08(): void {
        $L = 'toan-tam-giac'; $D = 'trung_binh';
        $this->quiz($L, 'Tam giác có một góc 90° gọi là tam giác gì?', ['Tam giác vuông', 'Tam giác đều', 'Tam giác tù', 'Tam giác nhọn'], 0, 'Tam giác có một góc vuông gọi là tam giác vuông.', $D);
        $this->quiz($L, 'Tam giác ABC có góc A = 80°, góc B = 50°. Góc C bằng?', ['50°', '60°', '40°', '100°'], 0, 'Góc C = 180° − 80° − 50° = 50°.', $D);
        $this->quiz($L, 'Tam giác có ba góc nhọn gọi là tam giác gì?', ['Tam giác nhọn', 'Tam giác vuông', 'Tam giác tù', 'Tam giác cân'], 0, 'Ba góc đều nhọn thì là tam giác nhọn.', $D);
        $this->quiz($L, 'Trong tam giác vuông, cạnh dài nhất gọi là gì?', ['Cạnh huyền', 'Cạnh góc vuông', 'Cạnh đáy', 'Cạnh bên'], 0, 'Cạnh huyền đối diện góc vuông, dài nhất.', $D);
        $this->quiz($L, 'Tam giác cân có góc ở đỉnh 40°. Mỗi góc ở đáy bằng?', ['70°', '60°', '80°', '50°'], 0, 'Mỗi góc đáy = (180° − 40°) : 2 = 70°.', $D);
        $this->matching($L, 'Nối mỗi loại tam giác với đặc điểm.', [['Tam giác đều', 'Ba cạnh bằng nhau'], ['Tam giác cân', 'Hai cạnh bằng nhau'], ['Tam giác vuông', 'Một góc 90°'], ['Tam giác tù', 'Một góc lớn hơn 90°']], 'Phân loại tam giác theo cạnh và góc.', $D);
        $this->matching($L, 'Nối mỗi cặp góc với số đo góc còn lại.', [['70° và 60°', '50°'], ['90° và 30°', '60°'], ['45° và 45°', '90°'], ['100° và 40°', '40°']], 'Góc còn lại = 180° trừ hai góc đã biết.', $D);
        $this->matching($L, 'Nối mỗi tam giác với tên gọi theo góc.', [['Có góc 120°', 'Tam giác tù'], ['Có góc 90°', 'Tam giác vuông'], ['Ba góc 60°', 'Tam giác đều'], ['Ba góc nhọn', 'Tam giác nhọn']], 'Tên gọi theo góc của tam giác.', $D);
        $this->matching($L, 'Nối mỗi cạnh trong tam giác vuông với tên gọi.', [['Cạnh đối diện góc vuông', 'Cạnh huyền'], ['Cạnh tạo góc vuông', 'Cạnh góc vuông'], ['Cạnh dài nhất', 'Cạnh huyền'], ['Hai cạnh nhỏ', 'Cạnh góc vuông']], 'Tam giác vuông có 1 cạnh huyền, 2 cạnh góc vuông.', $D);
        $this->matching($L, 'Nối mỗi tam giác đặc biệt với số đo góc.', [['Tam giác đều', 'Ba góc 60°'], ['Tam giác vuông cân', '90°, 45°, 45°'], ['Tam giác cân đỉnh 100°', 'Đáy mỗi góc 40°'], ['Tam giác vuông 30°-60°', '90°, 60°, 30°']], 'Tam giác đặc biệt có số đo góc đặc biệt.', $D);
        $this->sortQ($L, 'Kéo mỗi mô tả vào loại tam giác đúng.', [['Ba cạnh bằng nhau', 'TAM GIÁC ĐỀU'], ['Hai cạnh bằng nhau', 'TAM GIÁC CÂN'], ['Một góc vuông', 'TAM GIÁC VUÔNG'], ['Ba cạnh khác nhau', 'TAM GIÁC THƯỜNG']], 'Phân loại tam giác theo cạnh.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Tổng ba góc tam giác bằng 180°', 'ĐÚNG'], ['Tam giác có hai góc tù', 'SAI'], ['Tam giác đều cũng là tam giác cân', 'ĐÚNG'], ['Tam giác có 4 góc', 'SAI']], 'Tam giác chỉ có tối đa một góc tù hoặc vuông.', $D);
        $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm "CẠNH" hoặc "GÓC" của tam giác ABC.', [['AB', 'CẠNH'], ['BC', 'CẠNH'], ['Góc A', 'GÓC'], ['Góc B', 'GÓC']], 'Tam giác có 3 cạnh và 3 góc.', $D);
        $this->sortQ($L, 'Kéo mỗi bộ ba góc vào nhóm "TẠO THÀNH TAM GIÁC" hoặc "KHÔNG".', [['60°, 60°, 60°', 'TẠO THÀNH TAM GIÁC'], ['90°, 45°, 45°', 'TẠO THÀNH TAM GIÁC'], ['100°, 50°, 50°', 'KHÔNG'], ['90°, 90°, 10°', 'KHÔNG']], 'Tổng ba góc phải đúng bằng 180°.', $D);
        $this->sortQ($L, 'Kéo mỗi tam giác vào nhóm "CÂN" hoặc "KHÔNG CÂN".', [['Hai góc đáy bằng nhau', 'CÂN'], ['Hai cạnh bằng nhau', 'CÂN'], ['Ba cạnh khác nhau', 'KHÔNG CÂN'], ['Ba góc khác nhau', 'KHÔNG CÂN']], 'Tam giác cân có hai cạnh, hai góc đáy bằng nhau.', $D);
        $this->fill($L, 'Tam giác có một góc vuông gọi là tam giác ___.', [[0, 'vuông']], 'Tam giác vuông có một góc 90°.', $D);
        $this->fill($L, 'Trong tam giác vuông, cạnh dài nhất gọi là cạnh ___.', [[0, 'huyền']], 'Cạnh huyền đối diện với góc vuông.', $D);
        $this->fill($L, 'Tam giác ABC có góc A = 90°, góc B = 35°. Góc C = ___ độ.', [[0, '55']], 'Góc C = 180° − 90° − 35° = 55°.', $D);
        $this->fill($L, 'Tam giác có hai cạnh bằng nhau gọi là tam giác ___.', [[0, 'cân']], 'Tam giác cân có hai cạnh bên bằng nhau.', $D);
        $this->fill($L, 'Tam giác đều có ba góc bằng nhau, mỗi góc ___ độ.', [[0, '60']], '180° : 3 = 60°.', $D);
    }

    // 39. toan-so-tu-nhien-lop-6-1 — Số tự nhiên lớp 6: phép cộng và phép trừ (1) (lớp 6, dễ)
    private function seedTo09(): void {
        $L = 'toan-so-tu-nhien-lop-6-1'; $D = 'de';
        $this->quiz($L, 'Kết quả của 456 + 244 là?', ['600', '700', '690', '710'], 1, '456 + 244 = 700.', $D);
        $this->quiz($L, 'Kết quả của 2000 − 875 là?', ['1125', '1225', '1025', '1325'], 0, '2000 − 875 = 1125.', $D);
        $this->quiz($L, 'Tính nhanh: 15 + 85 + 40 = ?', ['140', '130', '150', '120'], 0, '15 + 85 = 100, 100 + 40 = 140.', $D);
        $this->quiz($L, 'Số liền trước của 10 000 là số nào?', ['9999', '10001', '9000', '9990'], 0, 'Số liền trước của 10 000 là 9999.', $D);
        $this->quiz($L, 'Thư viện có 1250 cuốn sách, mua thêm 350 cuốn. Thư viện có tất cả?', ['1600', '1500', '1700', '1400'], 0, '1250 + 350 = 1600 cuốn.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả đúng.', [['325 + 175', '500'], ['900 − 450', '450'], ['567 + 133', '700'], ['1500 − 750', '750']], 'Cộng trừ cẩn thận từng hàng.', $D);
        $this->matching($L, 'Nối mỗi tính chất với ví dụ minh họa.', [['Giao hoán', '25 + 75 = 75 + 25'], ['Kết hợp', '(10 + 20) + 30 = 10 + (20 + 30)'], ['Cộng với 0', '99 + 0 = 99'], ['Số đối của trừ', '50 − 50 = 0']], 'Tính chất giúp tính nhanh.', $D);
        $this->matching($L, 'Nối mỗi số với số liền trước của nó.', [['1000', '999'], ['500', '499'], ['2024', '2023'], ['101', '100']], 'Số liền trước kém số đã cho 1 đơn vị.', $D);
        $this->matching($L, 'Nối mỗi bài toán với phép tính đúng.', [['Tổng hai số', 'Cộng'], ['Hiệu hai số', 'Trừ'], ['Số còn lại', 'Trừ'], ['Thêm vào', 'Cộng']], 'Đọc kỹ đề để chọn phép tính.', $D);
        $this->matching($L, 'Nối mỗi phép trừ với hiệu của nó.', [['1000 − 250', '750'], ['800 − 800', '0'], ['645 − 145', '500'], ['2024 − 24', '2000']], 'Trừ từ hàng đơn vị sang hàng cao hơn.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "CHIA HẾT CHO 10" hoặc "KHÔNG".', [['120', 'CHIA HẾT CHO 10'], ['500', 'CHIA HẾT CHO 10'], ['123', 'KHÔNG'], ['457', 'KHÔNG']], 'Số chia hết cho 10 có chữ số tận cùng là 0.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "SỐ CHẴN" hoặc "SỐ LẺ".', [['246', 'SỐ CHẴN'], ['1000', 'SỐ CHẴN'], ['135', 'SỐ LẺ'], ['2025', 'SỐ LẺ']], 'Số chẵn tận cùng 0, 2, 4, 6, 8.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "KẾT QUẢ LỚN HƠN 1000" hoặc "KHÔNG".', [['700 + 450', 'KẾT QUẢ LỚN HƠN 1000'], ['2000 − 500', 'KẾT QUẢ LỚN HƠN 1000'], ['300 + 400', 'KHÔNG'], ['900 − 100', 'KHÔNG']], 'Ước lượng trước khi tính chính xác.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "SỐ LIỀN TRƯỚC" hoặc "SỐ LIỀN SAU" của 1000.', [['999', 'SỐ LIỀN TRƯỚC'], ['998', 'SỐ LIỀN TRƯỚC'], ['1001', 'SỐ LIỀN SAU'], ['1002', 'SỐ LIỀN SAU']], 'Liền trước kém 1, liền sau hơn 1.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "TÍNH NHANH ĐƯỢC" hoặc "TÍNH THƯỜNG".', [['25 + 75 + 13', 'TÍNH NHANH ĐƯỢC'], ['48 + 52 + 100', 'TÍNH NHANH ĐƯỢC'], ['123 + 457', 'TÍNH THƯỜNG'], ['789 − 123', 'TÍNH THƯỜNG']], 'Nhóm các số tròn chục, tròn trăm để tính nhanh.', $D);
        $this->fill($L, '456 + 244 = ___.', [[0, '700']], '456 + 244 = 700.', $D);
        $this->fill($L, 'Số liền trước của 5000 là ___.', [[0, '4999']], 'Số liền trước kém 1 đơn vị.', $D);
        $this->fill($L, 'Tính chất kết hợp: (a + b) + c = a + (b + ___).', [[0, 'c']], 'Nhóm các số hạng tùy ý, tổng không đổi.', $D);
        $this->fill($L, '2000 − ___ = 1500.', [[0, '500']], 'Số trừ = 2000 − 1500 = 500.', $D);
        $this->fill($L, 'Số chẵn là số có chữ số tận cùng là 0, 2, 4, 6, ___.', [[0, '8']], 'Năm chữ số chẵn: 0, 2, 4, 6, 8.', $D);
    }

    // 40. toan-so-tu-nhien-lop-6-2 — Số tự nhiên lớp 6: phép nhân, phép chia và thứ tự thực hiện (2) (lớp 6, dễ)
    private function seedTo10(): void {
        $L = 'toan-so-tu-nhien-lop-6-2'; $D = 'de';
        $this->quiz($L, 'Kết quả của 15 × 6 là?', ['80', '90', '75', '95'], 1, '15 × 6 = 90.', $D);
        $this->quiz($L, 'Kết quả của 120 : 6 là?', ['20', '12', '30', '18'], 0, '120 : 6 = 20.', $D);
        $this->quiz($L, 'Phép chia 38 cho 4 cho thương và dư nào?', ['Thương 9, dư 2', 'Thương 8, dư 6', 'Thương 9, dư 0', 'Thương 10, dư 2'], 0, '38 = 4 × 9 + 2.', $D);
        $this->quiz($L, 'Giá trị của 20 − 2 × 5 là?', ['90', '10', '0', '15'], 1, 'Nhân trước: 2 × 5 = 10, 20 − 10 = 10.', $D);
        $this->quiz($L, 'Có 7 gói kẹo, mỗi gói 9 cái. Tất cả có bao nhiêu cái?', ['63', '56', '72', '54'], 0, '7 × 9 = 63 cái.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả đúng.', [['18 × 5', '90'], ['150 : 5', '30'], ['12 × 12', '144'], ['200 : 8', '25']], 'Nhân chia cẩn thận.', $D);
        $this->matching($L, 'Nối mỗi phép chia với thương và số dư.', [['45 : 6', 'Thương 7, dư 3'], ['52 : 7', 'Thương 7, dư 3'], ['61 : 8', 'Thương 7, dư 5'], ['33 : 4', 'Thương 8, dư 1']], 'Số dư luôn nhỏ hơn số chia.', $D);
        $this->matching($L, 'Nối mỗi từ với ý nghĩa trong phép chia.', [['Số bị chia', 'Số đem chia'], ['Số chia', 'Số dùng để chia'], ['Thương', 'Kết quả phép chia'], ['Số dư', 'Phần còn lại']], 'Bốn thành phần của phép chia có dư.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với phép tính làm trước.', [['8 + 2 × 5', 'Nhân trước'], ['(8 + 2) × 5', 'Ngoặc trước'], ['50 − 30 : 6', 'Chia trước'], ['3 × (4 + 6)', 'Ngoặc trước']], 'Ngoặc → nhân chia → cộng trừ.', $D);
        $this->matching($L, 'Nối mỗi bài toán với phép tính đúng.', [['Gấp 4 lần 15', '15 × 4'], ['Chia 84 cho 7', '84 : 7'], ['Mỗi hộp 6 bút, 9 hộp', '6 × 9'], ['90 chia 3 phần', '90 : 3']], '"Gấp, mỗi" dùng nhân; "chia" dùng phép chia.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "TÍNH ĐÚNG THỨ TỰ" hoặc "SAI".', [['6 + 2 × 3 = 12', 'TÍNH ĐÚNG THỨ TỰ'], ['(6 + 2) × 3 = 24', 'TÍNH ĐÚNG THỨ TỰ'], ['6 + 2 × 3 = 24', 'SAI'], ['20 − 10 : 2 = 5', 'SAI']], 'Nhân chia trước cộng trừ; ngoặc trước nhất.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "SỐ CHẴN" hoặc "SỐ LẺ".', [['88', 'SỐ CHẴN'], ['124', 'SỐ CHẴN'], ['77', 'SỐ LẺ'], ['203', 'SỐ LẺ']], 'Chẵn tận cùng 0, 2, 4, 6, 8.', $D);
        $this->sortQ($L, 'Kéo mỗi phép chia vào nhóm "CHIA HẾT" hoặc "CÓ DƯ".', [['72 : 8', 'CHIA HẾT'], ['125 : 5', 'CHIA HẾT'], ['43 : 6', 'CÓ DƯ'], ['100 : 7', 'CÓ DƯ']], 'Chia hết khi dư bằng 0.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "CHIA HẾT CHO 5" hoặc "KHÔNG".', [['35', 'CHIA HẾT CHO 5'], ['120', 'CHIA HẾT CHO 5'], ['37', 'KHÔNG'], ['123', 'KHÔNG']], 'Số chia hết cho 5 tận cùng 0 hoặc 5.', $D);
        $this->sortQ($L, 'Kéo mỗi tích vào nhóm "LỚN HƠN 100" hoặc "KHÔNG".', [['15 × 8', 'LỚN HƠN 100'], ['25 × 5', 'LỚN HƠN 100'], ['9 × 9', 'KHÔNG'], ['12 × 7', 'KHÔNG']], 'Ước lượng tích trước khi tính.', $D);
        $this->fill($L, '15 × 6 = ___.', [[0, '90']], '15 × 6 = 90.', $D);
        $this->fill($L, 'Trong phép chia 38 cho 4, số dư là ___.', [[0, '2']], '38 = 4 × 9 + 2.', $D);
        $this->fill($L, 'Tính 50 − 6 × 4: ta thực hiện phép ___ trước.', [[0, 'nhân']], 'Nhân trước: 6 × 4 = 24, 50 − 24 = 26.', $D);
        $this->fill($L, '150 : ___ = 30.', [[0, '5']], 'Số chia = 150 : 30 = 5.', $D);
        $this->fill($L, 'Số chia hết cho 5 có chữ số tận cùng là 0 hoặc ___.', [[0, '5']], 'Dấu hiệu chia hết cho 5.', $D);
    }

    // 41. toan-so-tu-nhien-lop-7-1 — Số tự nhiên lớp 7: lũy thừa với số mũ tự nhiên (1) (lớp 7, dễ)
    private function seedTo11(): void {
        $L = 'toan-so-tu-nhien-lop-7-1'; $D = 'de';
        $this->quiz($L, 'Giá trị của 3² là?', ['6', '9', '5', '12'], 1, '3² = 3 × 3 = 9.', $D);
        $this->quiz($L, 'Giá trị của 2⁴ là?', ['8', '16', '12', '6'], 1, '2⁴ = 2 × 2 × 2 × 2 = 16.', $D);
        $this->quiz($L, 'Trong lũy thừa 5³, số 5 gọi là gì?', ['Số mũ', 'Cơ số', 'Lũy thừa', 'Tích'], 1, '5 là cơ số, 3 là số mũ.', $D);
        $this->quiz($L, 'Giá trị của 1¹⁰ là?', ['10', '1', '0', '100'], 1, '1 mũ bao nhiêu cũng bằng 1.', $D);
        $this->quiz($L, 'Giá trị của 4² − 3² là?', ['7', '1', '25', '9'], 0, '16 − 9 = 7.', $D);
        $this->matching($L, 'Nối mỗi lũy thừa với giá trị của nó.', [['2³', '8'], ['3³', '27'], ['4²', '16'], ['5³', '125']], 'Tính lũy thừa bằng cách nhân lặp.', $D);
        $this->matching($L, 'Nối mỗi công thức với cách đọc.', [['a²', 'a bình phương'], ['a³', 'a lập phương'], ['a¹', 'a'], ['a⁰ (a≠0)', '1']], 'Cách đọc các lũy thừa đặc biệt.', $D);
        $this->matching($L, 'Nối mỗi lũy thừa đặc biệt với giá trị.', [['7⁰', '1'], ['0⁵', '0'], ['1⁹⁹', '1'], ['10²', '100']], 'Số khác 0 mũ 0 bằng 1; 0 mũ dương bằng 0.', $D);
        $this->matching($L, 'Nối mỗi thành phần với tên gọi.', [['Trong 2⁵, số 2', 'Cơ số'], ['Trong 2⁵, số 5', 'Số mũ'], ['Trong 2⁵, 32', 'Giá trị lũy thừa'], ['2⁵', 'Lũy thừa']], 'Lũy thừa gồm cơ số và số mũ.', $D);
        $this->matching($L, 'Nối mỗi phép tính lũy thừa với kết quả.', [['2² × 2³', '2⁵ = 32'], ['5⁴ : 5²', '5² = 25'], ['3² + 4²', '25'], ['10³ − 10²', '900']], 'Cùng cơ số: nhân cộng mũ, chia trừ mũ.', $D);
        $this->sortQ($L, 'Kéo mỗi lũy thừa vào nhóm "BẰNG 64" hoặc "KHÁC 64".', [['2⁶', 'BẰNG 64'], ['8²', 'BẰNG 64'], ['4³', 'BẰNG 64'], ['3⁴', 'KHÁC 64']], '2⁶ = 8² = 4³ = 64; 3⁴ = 81.', $D);
        $this->sortQ($L, 'Kéo mỗi lũy thừa vào nhóm "LỚN HƠN 50" hoặc "KHÔNG".', [['3⁴', 'LỚN HƠN 50'], ['7²', 'KHÔNG'], ['2⁶', 'LỚN HƠN 50'], ['5²', 'KHÔNG']], '3⁴ = 81 > 50; 7² = 49 < 50.', $D);
        $this->sortQ($L, 'Kéo mỗi đẳng thức vào nhóm "ĐÚNG" hoặc "SAI".', [['2³ = 8', 'ĐÚNG'], ['3² = 9', 'ĐÚNG'], ['2⁴ = 8', 'SAI'], ['5² = 10', 'SAI']], '2⁴ = 16; 5² = 25.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "SỐ CHÍNH PHƯƠNG" hoặc "KHÔNG".', [['36', 'SỐ CHÍNH PHƯƠNG'], ['49', 'SỐ CHÍNH PHƯƠNG'], ['50', 'KHÔNG'], ['30', 'KHÔNG']], 'Số chính phương là bình phương của số tự nhiên.', $D);
        $this->sortQ($L, 'Kéo mỗi lũy thừa vào nhóm "CƠ SỐ 2" hoặc "CƠ SỐ KHÁC".', [['2⁵', 'CƠ SỐ 2'], ['2¹⁰', 'CƠ SỐ 2'], ['3²', 'CƠ SỐ KHÁC'], ['10³', 'CƠ SỐ KHÁC']], 'Nhận biết cơ số của lũy thừa.', $D);
        $this->fill($L, '3² = ___.', [[0, '9']], '3 × 3 = 9.', $D);
        $this->fill($L, 'a³ : a = a^___ (với a ≠ 0).', [[0, '2']], 'Trừ số mũ: 3 − 1 = 2.', $D);
        $this->fill($L, 'Số 0 mũ 0 không xác định, nhưng mọi số khác 0 mũ 0 đều bằng ___.', [[0, '1']], 'a⁰ = 1 với a ≠ 0.', $D);
        $this->fill($L, '2⁴ + 2³ = ___.', [[0, '24']], '16 + 8 = 24.', $D);
        $this->fill($L, 'Trong lũy thừa aⁿ, n gọi là số ___.', [[0, 'mũ']], 'n cho biết nhân a với chính nó bao nhiêu lần.', $D);
    }

    // 42. toan-so-tu-nhien-lop-7-2 — Số tự nhiên lớp 7: biểu thức phức tạp và tính nhanh (2) (lớp 7, trung bình)
    private function seedTo12(): void {
        $L = 'toan-so-tu-nhien-lop-7-2'; $D = 'trung_binh';
        $this->quiz($L, 'Giá trị của 3³ : 3 là?', ['9', '27', '3', '81'], 0, '3³ : 3 = 27 : 3 = 9.', $D);
        $this->quiz($L, 'Tính nhanh: 25 × 16 = ?', ['400', '410', '350', '416'], 0, '25 × 16 = 25 × 4 × 4 = 100 × 4 = 400.', $D);
        $this->quiz($L, 'Giá trị của 50 + 10 × 2² là?', ['90', '240', '60', '140'], 0, '2² = 4, 10 × 4 = 40, 50 + 40 = 90.', $D);
        $this->quiz($L, 'Tính nhanh: 98 + 37 + 2 = ?', ['137', '127', '147', '135'], 0, '98 + 2 = 100, 100 + 37 = 137.', $D);
        $this->quiz($L, 'Giá trị của (3 + 2)² là?', ['25', '13', '10', '11'], 0, 'Ngoặc trước: 5² = 25.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị của nó.', [['2³ + 3²', '17'], ['100 − 4 × 5', '80'], ['(6 + 4) × 3', '30'], ['5² − 2³', '17']], 'Tính theo đúng thứ tự ưu tiên.', $D);
        $this->matching($L, 'Nối mỗi phép tính nhanh với cách nhóm hợp lý.', [['25 × 16', '25 × 4 × 4'], ['98 + 37 + 2', '(98 + 2) + 37'], ['12 × 101', '12 × 100 + 12'], ['50 × 22', '50 × 20 + 50 × 2']], 'Nhóm số tròn để tính nhanh.', $D);
        $this->matching($L, 'Nối mỗi số với lập phương của nó.', [['2', '8'], ['3', '27'], ['4', '64'], ['5', '125']], 'Lập phương: nhân số với chính nó 3 lần.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với thứ tự tính đúng.', [['2 + 3 × 4²', 'Lũy thừa → nhân → cộng'], ['(2 + 3) × 4', 'Ngoặc → nhân'], ['100 : 10²', 'Lũy thừa → chia'], ['5³ − 5 × 4', 'Lũy thừa → nhân → trừ']], 'Ngoặc → lũy thừa → nhân chia → cộng trừ.', $D);
        $this->matching($L, 'Nối mỗi số dư với phép chia.', [['10² chia cho 3', 'Dư 1'], ['2³ chia cho 5', 'Dư 3'], ['3² chia cho 4', 'Dư 1'], ['5² chia cho 6', 'Dư 1']], 'Tính lũy thừa rồi chia lấy dư.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "BẰNG 1000" hoặc "KHÁC".', [['10³', 'BẰNG 1000'], ['(2 × 5)³', 'BẰNG 1000'], ['100²', 'KHÁC'], ['20³', 'KHÁC']], '10³ = 1000; (2×5)³ = 10³ = 1000.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "BẰNG 64" hoặc "KHÁC".', [['8²', 'BẰNG 64'], ['4³', 'BẰNG 64'], ['2⁶', 'BẰNG 64'], ['6²', 'KHÁC']], '8² = 4³ = 2⁶ = 64.', $D);
        $this->sortQ($L, 'Kéo mỗi đẳng thức vào nhóm "ĐÚNG" hoặc "SAI".', [['(2 × 3)² = 36', 'ĐÚNG'], ['2² × 3² = 36', 'ĐÚNG'], ['(2 + 3)² = 13', 'SAI'], ['5² − 4² = 9', 'ĐÚNG']], '(2+3)² = 25; 5² − 4² = 25 − 16 = 9.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "SỐ CHÍNH PHƯƠNG" hoặc "KHÔNG".', [['64', 'SỐ CHÍNH PHƯƠNG'], ['81', 'SỐ CHÍNH PHƯƠNG'], ['70', 'KHÔNG'], ['90', 'KHÔNG']], '64 = 8²; 81 = 9².', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "TÍNH TRONG NGOẶC TRƯỚC" hoặc "KHÔNG".', [['(15 + 5) × 2', 'TÍNH TRONG NGOẶC TRƯỚC'], ['100 − (3 × 4)', 'TÍNH TRONG NGOẶC TRƯỚC'], ['15 + 5 × 2', 'KHÔNG'], ['100 − 3 × 4', 'KHÔNG']], 'Ngoặc luôn được ưu tiên tính trước.', $D);
        $this->fill($L, '25 × 16 = 25 × 4 × 4 = ___.', [[0, '400']], '100 × 4 = 400.', $D);
        $this->fill($L, '3³ : 3 = 3^___.', [[0, '2']], 'Trừ số mũ: 3 − 1 = 2, 3² = 9.', $D);
        $this->fill($L, 'Tính nhanh: 97 + 3 + 25 = (97 + 3) + 25 = ___.', [[0, '125']], '100 + 25 = 125.', $D);
        $this->fill($L, 'Số dư của 5² khi chia cho 6 là ___.', [[0, '1']], '25 = 6 × 4 + 1.', $D);
        $this->fill($L, '(4 + 6)² = ___² = 100.', [[0, '10']], 'Ngoặc trước: 10² = 100.', $D);
    }

    // 43. toan-phan-so-lop-6-1 — Phân số lớp 6: cộng, trừ phân số đơn giản (1) (lớp 6, dễ)
    private function seedTo13(): void {
        $L = 'toan-phan-so-lop-6-1'; $D = 'de';
        $this->quiz($L, 'Kết quả của 3/8 + 1/8 là?', ['4/8 = 1/2', '4/16', '3/8', '5/8'], 0, '3/8 + 1/8 = 4/8 = 1/2.', $D);
        $this->quiz($L, 'Kết quả của 9/10 − 3/10 (rút gọn) là?', ['6/10', '3/5', '1/2', '6/7'], 1, '9/10 − 3/10 = 6/10 = 3/5.', $D);
        $this->quiz($L, 'Kết quả của 1/3 + 1/3 + 1/3 là?', ['3/9', '1', '3/6', '1/3'], 1, '3/3 = 1.', $D);
        $this->quiz($L, 'Phân số nào bằng 3/4?', ['6/8', '3/8', '4/3', '9/16'], 0, 'Nhân tử và mẫu với 2: 3/4 = 6/8.', $D);
        $this->quiz($L, 'Mẹ chia cái bánh thành 8 phần, em ăn 3 phần. Em ăn mấy phần bánh?', ['3/8', '5/8', '3/5', '8/3'], 0, 'Ăn 3 trong 8 phần là 3/8.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả đã rút gọn.', [['2/9 + 4/9', '2/3'], ['8/10 − 3/10', '1/2'], ['1/6 + 5/6', '1'], ['7/12 − 1/12', '1/2']], 'Cộng trừ tử số, giữ mẫu số, rồi rút gọn.', $D);
        $this->matching($L, 'Nối mỗi phân số với phân số bằng nó.', [['2/3', '4/6'], ['5/8', '10/16'], ['1/5', '3/15'], ['4/7', '8/14']], 'Nhân cả tử và mẫu với cùng số.', $D);
        $this->matching($L, 'Nối mỗi phân số với cách đọc đúng.', [['5/8', 'Năm phần tám'], ['3/10', 'Ba phần mười'], ['7/9', 'Bảy phần chín'], ['2/11', 'Hai phần mười một']], 'Đọc tử số rồi đến mẫu số.', $D);
        $this->matching($L, 'Nối mỗi thành phần với tên gọi.', [['Trong 3/5, số 3', 'Tử số'], ['Trong 3/5, số 5', 'Mẫu số'], ['Dấu gạch ngang', 'Dấu phân số'], ['3/5', 'Phân số']], 'Phân số gồm tử số và mẫu số.', $D);
        $this->matching($L, 'Nối mỗi bài toán với phép tính đúng.', [['Ăn 2/8 rồi ăn thêm 3/8', '2/8 + 3/8'], ['Còn lại sau khi dùng 1/4', '1 − 1/4'], ['Hơn kém giữa 5/6 và 1/6', '5/6 − 1/6'], ['Tổng 1/10 và 3/10', '1/10 + 3/10']], 'Chọn phép tính theo lời văn.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "KẾT QUẢ BẰNG 1" hoặc "KHÁC".', [['3/5 + 2/5', 'KẾT QUẢ BẰNG 1'], ['1/4 + 3/4', 'KẾT QUẢ BẰNG 1'], ['2/7 + 3/7', 'KHÁC'], ['1/6 + 1/6', 'KHÁC']], 'Tổng tử số bằng mẫu số thì kết quả bằng 1.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "LỚN HƠN 1/2" hoặc "NHỎ HƠN 1/2".', [['5/8', 'LỚN HƠN 1/2'], ['3/4', 'LỚN HƠN 1/2'], ['1/3', 'NHỎ HƠN 1/2'], ['2/5', 'NHỎ HƠN 1/2']], 'So sánh với một nửa.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp phân số vào nhóm "CÙNG MẪU SỐ" hoặc "KHÁC MẪU SỐ".', [['4/9 và 5/9', 'CÙNG MẪU SỐ'], ['1/6 và 5/6', 'CÙNG MẪU SỐ'], ['2/3 và 3/4', 'KHÁC MẪU SỐ'], ['1/2 và 3/5', 'KHÁC MẪU SỐ']], 'Cùng mẫu số cộng trừ trực tiếp được.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "PHÂN SỐ TỐI GIẢN" hoặc "CHƯA TỐI GIẢN".', [['5/6', 'PHÂN SỐ TỐI GIẢN'], ['7/10', 'PHÂN SỐ TỐI GIẢN'], ['4/10', 'CHƯA TỐI GIẢN'], ['9/12', 'CHƯA TỐI GIẢN']], 'Tối giản khi tử mẫu không cùng chia hết cho số > 1.', $D);
        $this->sortQ($L, 'Kéo mỗi kết quả vào nhóm "ĐÚNG" hoặc "SAI".', [['2/5 + 1/5 = 3/5', 'ĐÚNG'], ['7/8 − 2/8 = 5/8', 'ĐÚNG'], ['1/4 + 1/4 = 2/8', 'SAI'], ['6/7 − 6/7 = 0', 'ĐÚNG']], '1/4 + 1/4 = 2/4 = 1/2.', $D);
        $this->fill($L, '3/8 + 1/8 = ___.', [[0, '4/8']], 'Cùng mẫu: cộng tử số, giữ mẫu số.', $D);
        $this->fill($L, 'Muốn trừ hai phân số cùng mẫu số, ta trừ hai ___ số và giữ nguyên mẫu số.', [[0, 'tử']], 'Quy tắc trừ phân số cùng mẫu.', $D);
        $this->fill($L, '9/10 − 3/10 = ___ (rút gọn).', [[0, '3/5']], '6/10 = 3/5.', $D);
        $this->fill($L, 'Rút gọn phân số 6/9 ta được ___.', [[0, '2/3']], 'Chia cả tử và mẫu cho 3.', $D);
        $this->fill($L, 'Trong phân số 5/8, số 8 gọi là ___ số.', [[0, 'mẫu']], 'Mẫu số cho biết chia thành mấy phần bằng nhau.', $D);
    }

    // 44. toan-phan-so-lop-6-2 — Phân số lớp 6: rút gọn, quy đồng và so sánh (2) (lớp 6, dễ)
    private function seedTo14(): void {
        $L = 'toan-phan-so-lop-6-2'; $D = 'de';
        $this->quiz($L, 'Rút gọn phân số 10/15 ta được?', ['2/3', '5/3', '1/2', '10/3'], 0, 'Chia cả tử và mẫu cho 5: 10/15 = 2/3.', $D);
        $this->quiz($L, 'So sánh 2/5 và 3/5, kết luận nào đúng?', ['2/5 < 3/5', '2/5 > 3/5', '2/5 = 3/5', 'Không so sánh được'], 0, 'Cùng mẫu số, tử số nhỏ hơn thì phân số nhỏ hơn.', $D);
        $this->quiz($L, 'Mẫu số chung nhỏ nhất của 1/3 và 1/4 là?', ['12', '7', '24', '6'], 0, 'BCNN của 3 và 4 là 12.', $D);
        $this->quiz($L, 'Phân số nào lớn nhất trong 1/6, 1/4, 1/3?', ['1/3', '1/6', '1/4', 'Bằng nhau'], 0, 'Cùng tử số, mẫu số nhỏ hơn thì phân số lớn hơn.', $D);
        $this->quiz($L, 'Quy đồng 1/2 và 2/3 với mẫu số chung 6 được?', ['3/6 và 4/6', '1/6 và 2/6', '2/6 và 3/6', '6/6 và 6/6'], 0, '1/2 = 3/6; 2/3 = 4/6.', $D);
        $this->matching($L, 'Nối mỗi phân số với dạng rút gọn.', [['10/15', '2/3'], ['12/18', '2/3'], ['8/20', '2/5'], ['9/12', '3/4']], 'Chia cả tử và mẫu cho ước chung lớn nhất.', $D);
        $this->matching($L, 'Nối mỗi phép so sánh với dấu đúng.', [['3/7 ... 5/7', '<'], ['4/9 ... 4/9', '='], ['7/8 ... 5/8', '>'], ['2/3 ... 1/2', '>']], 'Cùng mẫu so tử; khác mẫu quy đồng rồi so.', $D);
        $this->matching($L, 'Nối mỗi cặp phân số với mẫu số chung nhỏ nhất.', [['1/2 và 1/3', '6'], ['1/4 và 1/6', '12'], ['2/3 và 1/5', '15'], ['3/4 và 1/8', '8']], 'Mẫu số chung nhỏ nhất là BCNN của các mẫu.', $D);
        $this->matching($L, 'Nối mỗi phân số với vị trí so với 1.', [['5/5', 'Bằng 1'], ['7/4', 'Lớn hơn 1'], ['3/8', 'Nhỏ hơn 1'], ['9/9', 'Bằng 1']], 'Tử bằng mẫu thì bằng 1; tử lớn hơn mẫu thì lớn hơn 1.', $D);
        $this->matching($L, 'Nối mỗi phân số với phân số bằng nó.', [['1/3', '2/6'], ['3/4', '9/12'], ['2/5', '6/15'], ['5/6', '10/12']], 'Nhân cả tử và mẫu với cùng một số tự nhiên.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "RÚT GỌN ĐƯỢC THÀNH 2/3" hoặc "KHÔNG".', [['10/15', 'RÚT GỌN ĐƯỢC THÀNH 2/3'], ['12/18', 'RÚT GỌN ĐƯỢC THÀNH 2/3'], ['3/4', 'KHÔNG'], ['5/6', 'KHÔNG']], '10/15 = 12/18 = 2/3.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "LỚN HƠN 1" hoặc "NHỎ HƠN 1".', [['8/5', 'LỚN HƠN 1'], ['11/6', 'LỚN HƠN 1'], ['4/7', 'NHỎ HƠN 1'], ['9/10', 'NHỎ HƠN 1']], 'Tử lớn hơn mẫu thì phân số lớn hơn 1.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "TỐI GIẢN" hoặc "CHƯA TỐI GIẢN".', [['4/5', 'TỐI GIẢN'], ['7/8', 'TỐI GIẢN'], ['6/9', 'CHƯA TỐI GIẢN'], ['10/25', 'CHƯA TỐI GIẢN']], '6/9 = 2/3; 10/25 = 2/5.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "BẰNG 1/2" hoặc "KHÁC".', [['3/6', 'BẰNG 1/2'], ['5/10', 'BẰNG 1/2'], ['2/3', 'KHÁC'], ['4/5', 'KHÁC']], 'Rút gọn rồi so sánh với 1/2.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp vào nhóm "PHÂN SỐ BẰNG NHAU" hoặc "KHÁC NHAU".', [['1/2 và 2/4', 'PHÂN SỐ BẰNG NHAU'], ['3/5 và 6/10', 'PHÂN SỐ BẰNG NHAU'], ['2/3 và 3/4', 'KHÁC NHAU'], ['1/4 và 2/5', 'KHÁC NHAU']], 'Quy đồng hoặc rút gọn để kiểm tra.', $D);
        $this->fill($L, 'Mẫu số chung nhỏ nhất của 1/3 và 1/4 là ___.', [[0, '12']], 'BCNN(3, 4) = 12.', $D);
        $this->fill($L, 'So sánh: 4/9 ___ 7/9 (điền dấu <, > hoặc =).', [[0, '<']], 'Cùng mẫu số, 4 < 7 nên 4/9 < 7/9.', $D);
        $this->fill($L, 'Rút gọn 12/16 = ___.', [[0, '3/4']], 'Chia cả tử và mẫu cho 4.', $D);
        $this->fill($L, 'Quy đồng 1/2 và 1/3: hai phân số mới là 3/6 và ___.', [[0, '2/6']], '1/3 = 2/6.', $D);
        $this->fill($L, 'Phân số có tử số bằng mẫu số thì bằng ___.', [[0, '1']], 'Ví dụ 5/5 = 1.', $D);
    }

    // 45. toan-phan-so-lop-7-1 — Phân số lớp 7: nhân, chia phân số và hỗn số (1) (lớp 7, trung bình)
    private function seedTo15(): void {
        $L = 'toan-phan-so-lop-7-1'; $D = 'trung_binh';
        $this->quiz($L, 'Kết quả của 3/5 × 5/9 là?', ['1/3', '15/45', '3/9', '8/14'], 0, '3/5 × 5/9 = 15/45 = 1/3.', $D);
        $this->quiz($L, 'Kết quả của 7/8 : 7/4 là?', ['1/2', '49/32', '2', '7/2'], 0, '7/8 × 4/7 = 28/56 = 1/2.', $D);
        $this->quiz($L, 'Hỗn số 2 1/4 bằng phân số nào?', ['9/4', '8/4', '7/4', '9/8'], 0, '2 1/4 = (2×4+1)/4 = 9/4.', $D);
        $this->quiz($L, 'Kết quả của 5/6 × 6 là?', ['5', '30/6', '11/6', '5/36'], 0, '5/6 × 6 = 30/6 = 5.', $D);
        $this->quiz($L, 'Số nghịch đảo của 1 1/2 là?', ['2/3', '3/2', '1/2', '2'], 0, '1 1/2 = 3/2, nghịch đảo là 2/3.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả.', [['4/7 × 7/8', '1/2'], ['9/10 : 3/5', '3/2'], ['2/3 × 12', '8'], ['5/9 : 5', '1/9']], 'Nhân rút gọn chéo; chia nhân nghịch đảo.', $D);
        $this->matching($L, 'Nối mỗi hỗn số với phân số bằng nó.', [['1 2/3', '5/3'], ['2 3/5', '13/5'], ['3 1/2', '7/2'], ['4 1/4', '17/4']], 'Hỗn số = (nguyên × mẫu + tử)/mẫu.', $D);
        $this->matching($L, 'Nối mỗi phân số với nghịch đảo của nó.', [['4/9', '9/4'], ['6/5', '5/6'], ['8', '1/8'], ['1/3', '3']], 'Đổi chỗ tử và mẫu.', $D);
        $this->matching($L, 'Nối mỗi phép chia với phép nhân tương đương.', [['3/5 : 9/10', '3/5 × 10/9'], ['7/8 : 7', '7/8 × 1/7'], ['2 : 4/5', '2 × 5/4'], ['5/6 : 5/6', '5/6 × 6/5']], 'Chia bằng nhân với nghịch đảo.', $D);
        $this->matching($L, 'Nối mỗi bài toán với phép tính đúng.', [['3/4 của 40', '3/4 × 40'], ['Chia 5/6 cho 2', '5/6 : 2'], ['Gấp đôi 1 1/2', '1 1/2 × 2'], ['Nửa của 7/8', '7/8 : 2']], 'Đọc kỹ lời văn chọn phép tính.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "KẾT QUẢ LÀ SỐ NGUYÊN" hoặc "KHÔNG".', [['3/4 × 8', 'KẾT QUẢ LÀ SỐ NGUYÊN'], ['2/5 × 15', 'KẾT QUẢ LÀ SỐ NGUYÊN'], ['1/2 × 7', 'KHÔNG'], ['3/8 × 5', 'KHÔNG']], '3/4 × 8 = 6; 2/5 × 15 = 6.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "LỚN HƠN 1" hoặc "NHỎ HƠN 1".', [['9/7', 'LỚN HƠN 1'], ['5/4', 'LỚN HƠN 1'], ['3/8', 'NHỎ HƠN 1'], ['7/10', 'NHỎ HƠN 1']], 'Tử lớn hơn mẫu thì lớn hơn 1.', $D);
        $this->sortQ($L, 'Kéo mỗi hỗn số vào nhóm "LỚN HƠN 2" hoặc "NHỎ HƠN 2".', [['2 1/3', 'LỚN HƠN 2'], ['3 3/4', 'LỚN HƠN 2'], ['1 1/2', 'NHỎ HƠN 2'], ['1 7/8', 'NHỎ HƠN 2']], 'Phần nguyên quyết định so với 2.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "ĐÚNG" hoặc "SAI".', [['2/3 × 3/4 = 1/2', 'ĐÚNG'], ['5/6 : 5 = 1/6', 'ĐÚNG'], ['1/2 : 2 = 1', 'SAI'], ['4/5 × 5/4 = 1', 'ĐÚNG']], '1/2 : 2 = 1/4.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp vào nhóm "NGHỊCH ĐẢO CỦA NHAU" hoặc "KHÔNG".', [['2/7 và 7/2', 'NGHỊCH ĐẢO CỦA NHAU'], ['5 và 1/5', 'NGHỊCH ĐẢO CỦA NHAU'], ['3/4 và 4/5', 'KHÔNG'], ['6/7 và 7/8', 'KHÔNG']], 'Tích hai số nghịch đảo bằng 1.', $D);
        $this->fill($L, '3/5 × 5/9 = ___ (rút gọn).', [[0, '1/3']], '15/45 = 1/3.', $D);
        $this->fill($L, 'Hỗn số 3 2/5 = ___.', [[0, '17/5']], '(3×5+2)/5 = 17/5.', $D);
        $this->fill($L, 'Số nghịch đảo của 4/9 là ___.', [[0, '9/4']], 'Đổi chỗ tử và mẫu.', $D);
        $this->fill($L, '7/8 : 7/4 = ___.', [[0, '1/2']], '7/8 × 4/7 = 28/56 = 1/2.', $D);
        $this->fill($L, '2/3 của 27 bằng ___.', [[0, '18']], '2/3 × 27 = 54/3 = 18.', $D);
    }

    // 46. toan-phan-so-lop-7-2 — Phân số lớp 7: biểu thức phân số và bài toán ứng dụng (2) (lớp 7, trung bình)
    private function seedTo16(): void {
        $L = 'toan-phan-so-lop-7-2'; $D = 'trung_binh';
        $this->quiz($L, 'Giá trị của 2/3 − 1/6 + 1/2 là?', ['1', '2/3', '5/6', '1/2'], 0, 'Quy đồng mẫu 6: 4/6 − 1/6 + 3/6 = 6/6 = 1.', $D);
        $this->quiz($L, 'Giá trị của (1/2 + 1/4) : 1/2 là?', ['3/2', '3/4', '1/2', '3'], 0, '3/4 : 1/2 = 3/4 × 2 = 3/2.', $D);
        $this->quiz($L, 'Một công việc, đội A làm một mình hết 6 ngày. Mỗi ngày đội A làm được mấy phần công việc?', ['1/6', '6', '1/3', '1/2'], 0, 'Mỗi ngày làm 1/6 công việc.', $D);
        $this->quiz($L, 'Tìm x biết 2x = 3/4. Giá trị của x là?', ['3/8', '3/2', '8/3', '2/3'], 0, 'x = 3/4 : 2 = 3/8.', $D);
        $this->quiz($L, 'Giá trị của 3/5 × 10 − 4 là?', ['2', '6', '26', '10'], 0, '3/5 × 10 = 6, 6 − 4 = 2.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị của nó.', [['1/3 + 1/6', '1/2'], ['5/6 − 1/3', '1/2'], ['2/5 × 5/2', '1'], ['3/4 : 3/8', '2']], 'Tính cẩn thận theo thứ tự.', $D);
        $this->matching($L, 'Nối mỗi bài toán với phép tính phù hợp.', [['Nửa của 2/3', '2/3 : 2'], ['Tổng 1/4 và 1/2', '1/4 + 1/2'], ['Gấp 3 lần 2/9', '2/9 × 3'], ['Chia 4/5 thành 4 phần', '4/5 : 4']], 'Dịch lời văn thành phép tính.', $D);
        $this->matching($L, 'Nối mỗi phân số với số thập phân bằng nó.', [['1/2', '0,5'], ['3/4', '0,75'], ['1/4', '0,25'], ['2/5', '0,4']], 'Chia tử cho mẫu được số thập phân.', $D);
        $this->matching($L, 'Nối mỗi phương trình với nghiệm.', [['x + 1/2 = 1', 'x = 1/2'], ['x − 1/3 = 1/3', 'x = 2/3'], ['2x = 1', 'x = 1/2'], ['x : 2 = 1/4', 'x = 1/2']], 'Tìm x bằng phép tính ngược.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với cách tính nhanh.', [['(1/2 + 1/2) × 7', '1 × 7'], ['3/4 × 8 + 3/4 × 8', 'Phân phối'], ['(2/3 + 1/3) : 5', '1 : 5'], ['10 × 1/5 + 10 × 4/5', '10 × 1']], 'Nhóm hợp lý để tính nhanh.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "BẰNG 1" hoặc "KHÁC 1".', [['1/2 + 1/2', 'BẰNG 1'], ['3/4 : 3/4', 'BẰNG 1'], ['2/3 + 1/6', 'KHÁC 1'], ['5/6 − 1/6', 'KHÁC 1']], '2/3 + 1/6 = 5/6; 5/6 − 1/6 = 2/3.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "BẰNG 2" hoặc "KHÁC 2".', [['4/3 + 2/3', 'BẰNG 2'], ['1/2 × 4', 'BẰNG 2'], ['3/4 + 3/4', 'KHÁC 2'], ['5/6 + 5/6', 'KHÁC 2']], '3/4 + 3/4 = 3/2; 5/6 + 5/6 = 5/3.', $D);
        $this->sortQ($L, 'Kéo mỗi phân số vào nhóm "LỚN HƠN 1/2" hoặc "NHỎ HƠN 1/2".', [['4/7', 'LỚN HƠN 1/2'], ['5/8', 'LỚN HƠN 1/2'], ['3/8', 'NHỎ HƠN 1/2'], ['2/9', 'NHỎ HƠN 1/2']], 'So sánh chéo với 1/2.', $D);
        $this->sortQ($L, 'Kéo mỗi số thập phân vào nhóm "BẰNG PHÂN SỐ HỮU HẠN" hoặc "KHÁC".', [['0,5', 'BẰNG PHÂN SỐ HỮU HẠN'], ['0,75', 'BẰNG PHÂN SỐ HỮU HẠN'], ['0,333...', 'KHÁC'], ['0,1666...', 'KHÁC']], '0,5 = 1/2; 0,75 = 3/4; 0,333... = 1/3 vô hạn tuần hoàn.', $D);
        $this->sortQ($L, 'Kéo mỗi bài toán vào nhóm "DÙNG PHÉP NHÂN" hoặc "DÙNG PHÉP CHIA".', [['2/3 của 30', 'DÙNG PHÉP NHÂN'], ['Gấp 4 lần 3/7', 'DÙNG PHÉP NHÂN'], ['Chia 3/4 cho 3', 'DÙNG PHÉP CHIA'], ['Nửa của 5/6', 'DÙNG PHÉP CHIA']], '"Của, gấp" dùng nhân; "chia, nửa" dùng chia.', $D);
        $this->fill($L, '2/3 − 1/6 + 1/2 = ___.', [[0, '1']], 'Quy đồng mẫu 6: 4/6 − 1/6 + 3/6 = 1.', $D);
        $this->fill($L, '(1/4 + 3/4) × 5 = ___.', [[0, '5']], '1 × 5 = 5.', $D);
        $this->fill($L, 'Tìm x: 2x = 3/4, vậy x = ___.', [[0, '3/8']], 'x = 3/4 : 2 = 3/8.', $D);
        $this->fill($L, '3/5 của 35 bằng ___.', [[0, '21']], '3/5 × 35 = 105/5 = 21.', $D);
        $this->fill($L, 'Một vòi chảy 1/5 bể mỗi giờ. Sau 3 giờ chảy được ___ bể.', [[0, '3/5']], '1/5 × 3 = 3/5 bể.', $D);
    }

    // 47. toan-bieu-thuc-dai-so-lop-7-1 — Biểu thức đại số lớp 7: biểu thức chữ và giá trị biểu thức (1) (lớp 7, dễ)
    private function seedTo17(): void {
        $L = 'toan-bieu-thuc-dai-so-lop-7-1'; $D = 'de';
        $this->quiz($L, 'Biểu thức nào là biểu thức số (không chứa chữ)?', ['3 × 5 + 2', '3x + 2', '5y − 1', '2a'], 0, '3 × 5 + 2 chỉ chứa số và phép tính.', $D);
        $this->quiz($L, 'Giá trị của biểu thức 5x tại x = 4 là?', ['9', '20', '54', '45'], 1, '5 × 4 = 20.', $D);
        $this->quiz($L, 'Giá trị của x − 3 tại x = 10 là?', ['7', '13', '30', '−7'], 0, '10 − 3 = 7.', $D);
        $this->quiz($L, 'Biểu thức diễn đạt "ba lần a" là?', ['3a', 'a + 3', 'a³', '3 + a'], 0, '"Ba lần a" nghĩa là 3 × a = 3a.', $D);
        $this->quiz($L, 'Trong biểu thức 4x + 7, 4 gọi là gì?', ['Biến số', 'Hệ số', 'Hằng số', 'Số mũ'], 1, '4 là hệ số của biến x.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị tại x = 6.', [['x + 4', '10'], ['2x', '12'], ['x − 1', '5'], ['30 : x', '5']], 'Thay x = 6 rồi tính.', $D);
        $this->matching($L, 'Nối mỗi cụm từ với biểu thức đại số.', [['Gấp đôi của b', '2b'], ['Hơn a 5 đơn vị', 'a + 5'], ['Kém c 2 đơn vị', 'c − 2'], ['Nửa của d', 'd : 2']], 'Dịch lời văn thành biểu thức chữ.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị tại a = 5.', [['a²', '25'], ['3a', '15'], ['a + 10', '15'], ['2a − 3', '7']], 'Thay a = 5: a² = 25; 3a = 15.', $D);
        $this->matching($L, 'Nối mỗi thành phần với tên gọi.', [['Trong 7x, số 7', 'Hệ số'], ['Trong 7x, chữ x', 'Biến số'], ['Trong 7x + 2, số 2', 'Hằng số'], ['7x + 2', 'Biểu thức']], 'Biểu thức gồm hệ số, biến số, hằng số.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị tại n = 3.', [['n³', '27'], ['4n', '12'], ['n + 9', '12'], ['2n + 1', '7']], 'n³ = 27; 4n = 12.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức (tại x = 5) vào nhóm "BẰNG 15" hoặc "KHÁC".', [['3x', 'BẰNG 15'], ['x + 10', 'BẰNG 15'], ['2x + 1', 'KHÁC'], ['x²', 'KHÁC']], '3×5 = 15; 5+10 = 15; 2×5+1 = 11.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "BIỂU THỨC ĐẠI SỐ" hoặc "BIỂU THỨC SỐ".', [['4x + 1', 'BIỂU THỨC ĐẠI SỐ'], ['2y − 5', 'BIỂU THỨC ĐẠI SỐ'], ['3 × 4 + 2', 'BIỂU THỨC SỐ'], ['100 − 50', 'BIỂU THỨC SỐ']], 'Biểu thức đại số chứa chữ; biểu thức số chỉ chứa số.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm "BẬC 1" hoặc "BẬC 2".', [['5x', 'BẬC 1'], ['−3y', 'BẬC 1'], ['2x²', 'BẬC 2'], ['7y²', 'BẬC 2']], 'Bậc là số mũ của biến.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "GIÁ TRỊ DƯƠNG" hoặc "ÂM" (tại x = 2).', [['3x + 1', 'GIÁ TRỊ DƯƠNG'], ['x²', 'GIÁ TRỊ DƯƠNG'], ['x − 5', 'ÂM'], ['1 − x', 'ÂM']], 'Thay x = 2: 3×2+1 = 7 > 0; 2−5 = −3 < 0.', $D);
        $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm "PHÉP NHÂN" hoặc "PHÉP CỘNG".', [['Gấp ba lần a', 'PHÉP NHÂN'], ['Tích của x và y', 'PHÉP NHÂN'], ['Tổng của a và b', 'PHÉP CỘNG'], ['Hơn kém nhau 4', 'PHÉP CỘNG']], '"Gấp, tích" là nhân; "tổng, hơn kém" là cộng/trừ.', $D);
        $this->fill($L, 'Giá trị của biểu thức 4x + 2 tại x = 5 là ___.', [[0, '22']], '4 × 5 + 2 = 22.', $D);
        $this->fill($L, 'Trong đơn thức 8y³, hệ số là ___.', [[0, '8']], 'Hệ số là phần số 8.', $D);
        $this->fill($L, 'Biểu thức diễn đạt "gấp ba của m" là ___.', [[0, '3m']], '"Gấp ba" nghĩa là nhân với 3.', $D);
        $this->fill($L, 'Giá trị của 2x² tại x = 3 là ___.', [[0, '18']], '2 × 9 = 18.', $D);
        $this->fill($L, 'Biểu thức chỉ chứa số và phép tính gọi là biểu thức ___.', [[0, 'số']], 'Biểu thức số không chứa chữ.', $D);
    }

    // 48. toan-bieu-thuc-dai-so-lop-7-2 — Biểu thức đại số lớp 7: đơn thức đồng dạng (2) (lớp 7, trung bình)
    private function seedTo18(): void {
        $L = 'toan-bieu-thuc-dai-so-lop-7-2'; $D = 'trung_binh';
        $this->quiz($L, 'Cặp đơn thức nào đồng dạng?', ['2x²y và 5x²y', '2x²y và 5xy²', '3x và 3y', '4x² và 4x'], 0, 'Cùng phần biến x²y nên đồng dạng.', $D);
        $this->quiz($L, 'Kết quả của 9x² − 4x² là?', ['5x²', '13x²', '5x⁴', '36x²'], 0, 'Trừ hệ số: 9 − 4 = 5, giữ x².', $D);
        $this->quiz($L, 'Thu gọn: 2xy + 3xy − xy = ?', ['4xy', '6xy', '5xy', '0'], 0, '2 + 3 − 1 = 4, được 4xy.', $D);
        $this->quiz($L, 'Đơn thức nào KHÔNG đồng dạng với 4ab?', ['−2ab', '7ba', '4a²b', 'ab'], 2, '4a²b có phần biến a²b khác ab.', $D);
        $this->quiz($L, 'Giá trị của 3x² tại x = −2 là?', ['12', '−12', '36', '−36'], 0, '3 × (−2)² = 3 × 4 = 12.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả.', [['5x + 3x', '8x'], ['10y − 6y', '4y'], ['x² + 2x²', '3x²'], ['7ab − 2ab', '5ab']], 'Cộng trừ hệ số của đơn thức đồng dạng.', $D);
        $this->matching($L, 'Nối mỗi cặp đơn thức với nhận xét.', [['6x³ và −x³', 'Đồng dạng'], ['2xy và 2x²y', 'Không đồng dạng'], ['5mn và 5nm', 'Đồng dạng'], ['3a²b và 3ab²', 'Không đồng dạng']], 'mn = nm nên đồng dạng; a²b ≠ ab².', $D);
        $this->matching($L, 'Nối mỗi đơn thức với hệ số.', [['−5x²', '−5'], ['12xy', '12'], ['x³', '1'], ['−y', '−1']], 'Hệ số 1 và −1 thường viết gọn.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với giá trị tại x = 3.', [['2x²', '18'], ['x² + x', '12'], ['5x − x²', '6'], ['x³ : x', '9']], 'Thay x = 3: 2×9 = 18; 9+3 = 12.', $D);
        $this->matching($L, 'Nối mỗi phép thu gọn nhiều hạng tử với kết quả.', [['x + 2x + 3x', '6x'], ['5y² − 2y² + y²', '4y²'], ['4ab + ab − 2ab', '3ab'], ['10x³ − 3x³ − x³', '6x³']], 'Gộp các hệ số của hạng tử đồng dạng.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm "ĐỒNG DẠNG VỚI 5xy" hoặc "KHÔNG".', [['−3xy', 'ĐỒNG DẠNG VỚI 5xy'], ['12yx', 'ĐỒNG DẠNG VỚI 5xy'], ['5x²y', 'KHÔNG'], ['5xy²', 'KHÔNG']], 'yx = xy nên đồng dạng.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm "ĐỒNG DẠNG VỚI −2x²" hoặc "KHÔNG".', [['7x²', 'ĐỒNG DẠNG VỚI −2x²'], ['x²', 'ĐỒNG DẠNG VỚI −2x²'], ['−2x³', 'KHÔNG'], ['−2x', 'KHÔNG']], 'Cùng phần biến x².', $D);
        $this->sortQ($L, 'Kéo mỗi phép thu gọn vào nhóm "ĐÚNG" hoặc "SAI".', [['4x + 5x = 9x', 'ĐÚNG'], ['8y − 3y = 5y', 'ĐÚNG'], ['2x + 3x² = 5x³', 'SAI'], ['6ab − ab = 5ab', 'ĐÚNG']], 'Không cộng được đơn thức không đồng dạng.', $D);
        $this->sortQ($L, 'Kéo mỗi đơn thức vào nhóm "BẬC 2" hoặc "BẬC 3".', [['3x²', 'BẬC 2'], ['5xy', 'BẬC 2'], ['2x³', 'BẬC 3'], ['4x²y', 'BẬC 3']], 'Bậc = tổng số mũ: xy bậc 2; x²y bậc 3.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "THU GỌN ĐƯỢC" hoặc "KHÔNG".', [['3x + 2x', 'THU GỌN ĐƯỢC'], ['5y − y', 'THU GỌN ĐƯỢC'], ['2x + 3y', 'KHÔNG'], ['4a² + 5a', 'KHÔNG']], 'Chỉ thu gọn được các hạng tử đồng dạng.', $D);
        $this->fill($L, '7x − 3x + 2x = ___x.', [[0, '6']], '7 − 3 + 2 = 6.', $D);
        $this->fill($L, 'Đơn thức 5x²y và −2x²y là hai đơn thức đồng ___.', [[0, 'dạng']], 'Cùng phần biến x²y.', $D);
        $this->fill($L, '12ab − 5ab = ___ab.', [[0, '7']], '12 − 5 = 7.', $D);
        $this->fill($L, 'Thu gọn: 3x³ + x³ − 2x³ = ___x³.', [[0, '2']], '3 + 1 − 2 = 2.', $D);
        $this->fill($L, 'Hệ số của đơn thức −x² là ___.', [[0, '−1']], '−x² = −1 × x².', $D);
    }

    // 49. toan-bieu-thuc-dai-so-lop-8-1 — Biểu thức đại số lớp 8: đa thức và thu gọn đa thức (1) (lớp 8, trung bình)
    private function seedTo19(): void {
        $L = 'toan-bieu-thuc-dai-so-lop-8-1'; $D = 'trung_binh';
        $this->quiz($L, 'Bậc của đa thức 4x³ − x + 2 là?', ['3', '4', '2', '1'], 0, 'Hạng tử bậc cao nhất là 4x³ nên bậc là 3.', $D);
        $this->quiz($L, 'Thu gọn đa thức 2x² + 5x − x² + 3x ta được?', ['x² + 8x', '3x² + 8x', 'x² + 2x', '2x² + 8x'], 0, '2x² − x² = x²; 5x + 3x = 8x.', $D);
        $this->quiz($L, 'Đa thức nào là đa thức một biến?', ['x² + 2x + 1', 'xy + 1', 'x + y + z', '2ab'], 0, 'Chỉ chứa biến x.', $D);
        $this->quiz($L, 'Hệ số tự do của đa thức 3x² − 4x + 9 là?', ['9', '3', '−4', '2'], 0, 'Hệ số tự do là hạng tử không chứa biến: 9.', $D);
        $this->quiz($L, 'Giá trị của đa thức x² + 1 tại x = −3 là?', ['10', '−8', '8', '7'], 0, '(−3)² + 1 = 9 + 1 = 10.', $D);
        $this->matching($L, 'Nối mỗi đa thức với bậc của nó.', [['2x³ + x', 'Bậc 3'], ['5x² − 3', 'Bậc 2'], ['7x + 1', 'Bậc 1'], ['4', 'Bậc 0']], 'Bậc của đa thức là bậc cao nhất của hạng tử.', $D);
        $this->matching($L, 'Nối mỗi phép tính với kết quả.', [['(x² + 2x) + (3x² − x)', '4x² + x'], ['(5x − 1) − (2x − 1)', '3x'], ['(x + 3) + (x − 3)', '2x'], ['(2x² + 1) − (x² + 1)', 'x²']], 'Bỏ ngoặc rồi gộp hạng tử đồng dạng.', $D);
        $this->matching($L, 'Nối mỗi đa thức với giá trị tại x = 2.', [['x² + 3x', '10'], ['2x² − x', '6'], ['x³ − 7', '1'], ['5 − x', '3']], 'Thay x = 2: 4+6 = 10; 8−2 = 6.', $D);
        $this->matching($L, 'Nối mỗi hạng tử với bậc của nó.', [['Trong 3x²y, hạng tử 3x²y', 'Bậc 3'], ['Trong x³, hạng tử x³', 'Bậc 3'], ['Trong 5xy, hạng tử 5xy', 'Bậc 2'], ['Trong 7, hạng tử 7', 'Bậc 0']], 'Bậc hạng tử = tổng số mũ các biến.', $D);
        $this->matching($L, 'Nối mỗi đa thức với dạng thu gọn.', [['x + x + x', '3x'], ['2x² + 3 − x²', 'x² + 3'], ['4y − y + 2', '3y + 2'], ['x³ + 2x³ − x³', '2x³']], 'Gộp các hạng tử đồng dạng.', $D);
        $this->sortQ($L, 'Kéo mỗi đa thức vào nhóm "BẬC 2" hoặc "BẬC 3".', [['3x² + x', 'BẬC 2'], ['5 − 2x²', 'BẬC 2'], ['x³ + 1', 'BẬC 3'], ['4x³ − x²', 'BẬC 3']], 'Bậc cao nhất quyết định bậc đa thức.', $D);
        $this->sortQ($L, 'Kéo mỗi đa thức vào nhóm "MỘT BIẾN" hoặc "NHIỀU BIẾN".', [['x² + 5x', 'MỘT BIẾN'], ['2y³ − y', 'MỘT BIẾN'], ['xy + 2', 'NHIỀU BIẾN'], ['x² + y²', 'NHIỀU BIẾN']], 'Một biến chỉ chứa một chữ cái.', $D);
        $this->sortQ($L, 'Kéo mỗi đa thức vào nhóm "ĐÃ THU GỌN" hoặc "CHƯA THU GỌN".', [['3x² + 2x', 'ĐÃ THU GỌN'], ['5x + 1', 'ĐÃ THU GỌN'], ['2x + 3x', 'CHƯA THU GỌN'], ['x² + 4 − x²', 'CHƯA THU GỌN']], 'Chưa thu gọn khi còn hạng tử đồng dạng.', $D);
        $this->sortQ($L, 'Kéo mỗi đa thức vào nhóm "CÓ HỆ SỐ TỰ DO" hoặc "KHÔNG".', [['x² + 3', 'CÓ HỆ SỐ TỰ DO'], ['2x − 5', 'CÓ HỆ SỐ TỰ DO'], ['x³ + x', 'KHÔNG'], ['4x² − 2x', 'KHÔNG']], 'Hệ số tự do là hạng tử không chứa biến.', $D);
        $this->sortQ($L, 'Kéo mỗi phép tính vào nhóm "ĐÚNG" hoặc "SAI".', [['(x + 2) + (3x − 2) = 4x', 'ĐÚNG'], ['(5x² − x) − x² = 4x² − x', 'ĐÚNG'], ['(x + 1) − (x + 1) = 2', 'SAI'], ['2x² + 3x² = 5x²', 'ĐÚNG']], '(x+1) − (x+1) = 0.', $D);
        $this->fill($L, 'Bậc của đa thức 5x³ − 2x² + x là ___.', [[0, '3']], 'Hạng tử bậc cao nhất là 5x³.', $D);
        $this->fill($L, 'Thu gọn: 4x² + 2x − 2x² + 5x = ___.', [[0, '2x² + 7x']], '4x² − 2x² = 2x²; 2x + 5x = 7x.', $D);
        $this->fill($L, 'Hệ số tự do của đa thức 2x² − x + 11 là ___.', [[0, '11']], 'Hạng tử không chứa biến là 11.', $D);
        $this->fill($L, 'Giá trị của đa thức x² − 4 tại x = 3 là ___.', [[0, '5']], '9 − 4 = 5.', $D);
        $this->fill($L, 'Đa thức thu gọn là đa thức không còn hạng tử ___ dạng.', [[0, 'đồng']], 'Đã gộp hết các hạng tử đồng dạng.', $D);
    }

    // 50. toan-bieu-thuc-dai-so-lop-8-2 — Biểu thức đại số lớp 8: nhân đơn thức, đa thức (2) (lớp 8, trung bình)
    private function seedTo20(): void {
        $L = 'toan-bieu-thuc-dai-so-lop-8-2'; $D = 'trung_binh';
        $this->quiz($L, 'Kết quả của 5x × 2x³ là?', ['10x⁴', '7x⁴', '10x³', '7x³'], 0, 'Nhân hệ số 5×2 = 10; x × x³ = x⁴.', $D);
        $this->quiz($L, 'Khai triển 2x(x − 3) ta được?', ['2x² − 6x', '2x² − 3', '2x − 6x', '2x² + 6x'], 0, 'Nhân phân phối: 2x×x − 2x×3.', $D);
        $this->quiz($L, 'Khai triển (x − 2)(x + 3) ta được?', ['x² + x − 6', 'x² + 5x − 6', 'x² − x − 6', 'x² − 5x + 6'], 0, 'x×x + 3x − 2x − 6 = x² + x − 6.', $D);
        $this->quiz($L, '(x + 3)² bằng?', ['x² + 6x + 9', 'x² + 9', 'x² + 3x + 9', '2x + 6'], 0, 'Bình phương của tổng: x² + 2×3x + 9.', $D);
        $this->quiz($L, 'Kết quả của (−4x) × 3y là?', ['−12xy', '12xy', '−7xy', '−12x²y²'], 0, 'Nhân hệ số −4×3 = −12, giữ xy.', $D);
        $this->matching($L, 'Nối mỗi phép nhân đơn thức với kết quả.', [['3x × 4y', '12xy'], ['2x² × 5x', '10x³'], ['−x × 6x', '−6x²'], ['7y × 2y²', '14y³']], 'Nhân hệ số với hệ số, biến với biến.', $D);
        $this->matching($L, 'Nối mỗi phép khai triển với kết quả.', [['x(x + 5)', 'x² + 5x'], ['3(x − 2)', '3x − 6'], ['2x(x² + 1)', '2x³ + 2x'], ['−2(x + 4)', '−2x − 8']], 'Nhân đơn thức với từng hạng tử.', $D);
        $this->matching($L, 'Nối mỗi hằng đẳng thức với tên gọi.', [['(a + b)²', 'Bình phương của tổng'], ['(a − b)²', 'Bình phương của hiệu'], ['a² − b²', 'Hiệu hai bình phương'], ['(a + b)(a − b)', 'Hiệu hai bình phương']], 'Ba hằng đẳng thức đáng nhớ.', $D);
        $this->matching($L, 'Nối mỗi phép tính với bậc của kết quả.', [['x² × x³', 'Bậc 5'], ['2x × 3x²', 'Bậc 3'], ['x(x + 1)', 'Bậc 2'], ['(x + 1)(x + 2)', 'Bậc 2']], 'Bậc của tích = tổng các bậc.', $D);
        $this->matching($L, 'Nối mỗi biểu thức với dạng khai triển đúng.', [['(x + 4)²', 'x² + 8x + 16'], ['(x − 5)²', 'x² − 10x + 25'], ['(2x + 1)(2x − 1)', '4x² − 1'], ['(x + 2)(x − 2)', 'x² − 4']], 'Áp dụng hằng đẳng thức.', $D);
        $this->sortQ($L, 'Kéo mỗi phép khai triển vào nhóm "ĐÚNG" hoặc "SAI".', [['x(x + 2) = x² + 2x', 'ĐÚNG'], ['2(x − 1) = 2x − 2', 'ĐÚNG'], ['3x(x + 1) = 3x² + 1', 'SAI'], ['(x + 1)² = x² + 1', 'SAI']], '3x(x+1) = 3x² + 3x; (x+1)² = x² + 2x + 1.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "BẬC 2" hoặc "BẬC 3".', [['x² + 5', 'BẬC 2'], ['3x × 2x', 'BẬC 2'], ['x³ − 1', 'BẬC 3'], ['2x² × x', 'BẬC 3']], 'Tích hai đơn thức bậc 1 cho bậc 2.', $D);
        $this->sortQ($L, 'Kéo mỗi hằng đẳng thức vào nhóm "BÌNH PHƯƠNG" hoặc "HIỆU HAI BÌNH PHƯƠNG".', [['(x + 2)²', 'BÌNH PHƯƠNG'], ['(3 − y)²', 'BÌNH PHƯƠNG'], ['x² − 9', 'HIỆU HAI BÌNH PHƯƠNG'], ['4a² − 25', 'HIỆU HAI BÌNH PHƯƠNG']], 'a² − b² = (a − b)(a + b).', $D);
        $this->sortQ($L, 'Kéo mỗi phép nhân vào nhóm "KẾT QUẢ ĐÚNG" hoặc "SAI".', [['4x × 2x = 8x²', 'KẾT QUẢ ĐÚNG'], ['−3x × x = −3x²', 'KẾT QUẢ ĐÚNG'], ['5x × 3 = 15x²', 'SAI'], ['2x² × 3x³ = 6x⁵', 'KẾT QUẢ ĐÚNG']], '5x × 3 = 15x.', $D);
        $this->sortQ($L, 'Kéo mỗi biểu thức vào nhóm "CÓ DẠNG HẰNG ĐẲNG THỨC" hoặc "KHÔNG".', [['x² + 4x + 4', 'CÓ DẠNG HẰNG ĐẲNG THỨC'], ['x² − 16', 'CÓ DẠNG HẰNG ĐẲNG THỨC'], ['x² + x + 1', 'KHÔNG'], ['2x² + 3x', 'KHÔNG']], 'x²+4x+4 = (x+2)²; x²−16 = (x−4)(x+4).', $D);
        $this->fill($L, '4x² × 3x = ___x³.', [[0, '12']], 'Nhân hệ số 4×3 = 12.', $D);
        $this->fill($L, 'Khai triển: (x + 4)(x − 4) = x² − ___.', [[0, '16']], 'Hiệu hai bình phương: x² − 16.', $D);
        $this->fill($L, '(3x − 1)² = 9x² − 6x + ___.', [[0, '1']], 'Bình phương của hiệu: 9x² − 6x + 1.', $D);
        $this->fill($L, '3x(2x² + x) = 6x³ + ___x².', [[0, '3']], 'Nhân phân phối: 3x × x = 3x².', $D);
        $this->fill($L, '(x + 5)(x + 1) = x² + 6x + ___.', [[0, '5']], 'x×1 + 5×x... = x² + 6x + 5.', $D);
    }

    // 51. toan-hinh-hoc-phang-lop-7-1 — Hình học phẳng lớp 7: góc và cách đo góc (1) (lớp 7, dễ)
    private function seedTo21(): void {
        $L = 'toan-hinh-hoc-phang-lop-7-1'; $D = 'de';
        $this->quiz($L, 'Góc 60° là loại góc nào?', ['Góc nhọn', 'Góc vuông', 'Góc tù', 'Góc bẹt'], 0, '60° < 90° nên là góc nhọn.', $D);
        $this->quiz($L, 'Hai góc phụ nhau có tổng số đo bằng?', ['90°', '180°', '360°', '45°'], 0, 'Hai góc phụ nhau tổng bằng 90°.', $D);
        $this->quiz($L, 'Góc tạo bởi kim giờ và kim phút lúc 3 giờ là?', ['90°', '180°', '60°', '120°'], 0, 'Lúc 3 giờ, hai kim vuông góc: 90°.', $D);
        $this->quiz($L, 'Muốn vẽ góc 50°, em dùng dụng cụ nào?', ['Thước đo góc', 'Compa', 'Thước kẻ', 'Bút chì'], 0, 'Thước đo góc dùng để vẽ góc theo số đo.', $D);
        $this->quiz($L, 'Góc bẹt gấp mấy lần góc vuông?', ['2 lần', '4 lần', '1 lần', '3 lần'], 0, '180° : 90° = 2 lần.', $D);
        $this->matching($L, 'Nối mỗi loại góc với số đo.', [['Góc nhọn', 'Nhỏ hơn 90°'], ['Góc vuông', '90°'], ['Góc tù', '90° đến 180°'], ['Góc bẹt', '180°']], 'Bốn loại góc cơ bản.', $D);
        $this->matching($L, 'Nối mỗi số đo với loại góc.', [['25°', 'Góc nhọn'], ['90°', 'Góc vuông'], ['135°', 'Góc tù'], ['180°', 'Góc bẹt']], 'Phân loại theo số đo.', $D);
        $this->matching($L, 'Nối mỗi dụng cụ với công dụng.', [['Thước đo góc', 'Đo và vẽ góc'], ['Êke 45°', 'Vẽ góc vuông, 45°'], ['Compa', 'Vẽ đường tròn'], ['Thước thẳng', 'Vẽ đoạn thẳng']], 'Dụng cụ hình học lớp 7.', $D);
        $this->matching($L, 'Nối mỗi giờ với góc giữa hai kim.', [['3 giờ', '90°'], ['6 giờ', '180°'], ['12 giờ', '0°'], ['9 giờ', '90°']], 'Mỗi giờ kim giờ quay 30°.', $D);
        $this->matching($L, 'Nối mỗi cặp góc với tên gọi.', [['Tổng 90°', 'Phụ nhau'], ['Tổng 180°', 'Bù nhau'], ['Chung đỉnh, chung cạnh', 'Kề nhau'], ['Đối nhau qua đỉnh', 'Đối đỉnh']], 'Các cặp góc đặc biệt.', $D);
        $this->sortQ($L, 'Kéo mỗi góc vào nhóm "GÓC NHỌN" hoặc "GÓC TÙ".', [['20°', 'GÓC NHỌN'], ['75°', 'GÓC NHỌN'], ['110°', 'GÓC TÙ'], ['160°', 'GÓC TÙ']], 'Nhọn < 90° < tù < 180°.', $D);
        $this->sortQ($L, 'Kéo mỗi số đo vào nhóm "NHỎ HƠN 90°" hoặc "LỚN HƠN 90°".', [['30°', 'NHỎ HƠN 90°'], ['85°', 'NHỎ HƠN 90°'], ['95°', 'LỚN HƠN 90°'], ['150°', 'LỚN HƠN 90°']], 'So sánh với góc vuông.', $D);
        $this->sortQ($L, 'Kéo mỗi hình vào nhóm "CÓ GÓC VUÔNG" hoặc "KHÔNG".', [['Êke', 'CÓ GÓC VUÔNG'], ['Khung cửa', 'CÓ GÓC VUÔNG'], ['Quả bóng', 'KHÔNG'], ['Mặt đồng hồ tròn', 'KHÔNG']], 'Êke và khung cửa có góc vuông.', $D);
        $this->sortQ($L, 'Kéo mỗi mô tả vào nhóm "GÓC NHỌN" hoặc "GÓC BẸT".', [['Nhỏ hơn góc vuông', 'GÓC NHỌN'], ['Số đo 30°', 'GÓC NHỌN'], ['Hai tia đối nhau', 'GÓC BẸT'], ['Số đo 180°', 'GÓC BẸT']], 'Góc nhọn < 90°; góc bẹt = 180°.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp góc vào nhóm "PHỤ NHAU" hoặc "BÙ NHAU".', [['30° và 60°', 'PHỤ NHAU'], ['45° và 45°', 'PHỤ NHAU'], ['100° và 80°', 'BÙ NHAU'], ['120° và 60°', 'BÙ NHAU']], 'Phụ nhau tổng 90°; bù nhau tổng 180°.', $D);
        $this->fill($L, 'Góc có số đo 180° gọi là góc ___.', [[0, 'bẹt']], 'Góc bẹt tạo bởi hai tia đối nhau.', $D);
        $this->fill($L, 'Hai góc có tổng bằng 90° gọi là hai góc phụ ___.', [[0, 'nhau']], 'Phụ nhau: tổng 90°.', $D);
        $this->fill($L, 'Lúc 6 giờ, kim giờ và kim phút tạo thành góc ___.', [[0, 'bẹt']], 'Hai kim đối nhau: góc 180°.', $D);
        $this->fill($L, 'Tia nằm giữa hai cạnh của góc và chia góc thành hai phần bằng nhau gọi là tia phân ___.', [[0, 'giác']], 'Tia phân giác chia đôi góc.', $D);
        $this->fill($L, 'Góc vuông có số đo ___ độ.', [[0, '90']], 'Góc vuông = 90°.', $D);
    }

    // 52. toan-hinh-hoc-phang-lop-7-2 — Hình học phẳng lớp 7: góc kề bù và góc đối đỉnh (2) (lớp 7, trung bình)
    private function seedTo22(): void {
        $L = 'toan-hinh-hoc-phang-lop-7-2'; $D = 'trung_binh';
        $this->quiz($L, 'Góc kề bù với góc 70° có số đo?', ['110°', '70°', '20°', '90°'], 0, '180° − 70° = 110°.', $D);
        $this->quiz($L, 'Hai góc đối đỉnh thì?', ['Bằng nhau', 'Bù nhau', 'Phụ nhau', 'Khác nhau'], 0, 'Hai góc đối đỉnh luôn bằng nhau.', $D);
        $this->quiz($L, 'Góc A = 120°, góc B đối đỉnh với góc A. Góc B bằng?', ['120°', '60°', '90°', '180°'], 0, 'Đối đỉnh thì bằng nhau: 120°.', $D);
        $this->quiz($L, 'Tia phân giác của góc 100° chia góc thành hai góc mỗi góc?', ['50°', '100°', '200°', '25°'], 0, '100° : 2 = 50°.', $D);
        $this->quiz($L, 'Hai đường thẳng cắt nhau tạo ra mấy cặp góc kề bù?', ['4 cặp', '2 cặp', '1 cặp', '8 cặp'], 0, 'Mỗi góc có 2 góc kề bù, tổng 4 cặp.', $D);
        $this->matching($L, 'Nối mỗi khái niệm với định nghĩa.', [['Góc kề bù', 'Kề nhau, tổng 180°'], ['Góc đối đỉnh', 'Đối nhau qua đỉnh, bằng nhau'], ['Tia phân giác', 'Chia góc thành hai phần bằng nhau'], ['Góc bù nhau', 'Tổng số đo 180°']], 'Các khái niệm về góc.', $D);
        $this->matching($L, 'Nối mỗi góc với số đo góc kề bù.', [['30°', '150°'], ['90°', '90°'], ['120°', '60°'], ['45°', '135°']], 'Góc kề bù = 180° trừ góc đã biết.', $D);
        $this->matching($L, 'Nối mỗi hình với số cặp góc đối đỉnh.', [['Hai đường thẳng cắt nhau', '2 cặp'], ['Ba đường thẳng đồng quy', '6 cặp'], ['Một đường thẳng', '0 cặp'], ['Hai tia đối nhau', '0 cặp']], 'n đường thẳng đồng quy tạo n(n−1)/2 cặp... thực tế 2 đường tạo 2 cặp.', $D);
        $this->matching($L, 'Nối mỗi góc với số đo mỗi phần khi vẽ tia phân giác.', [['80°', '40°'], ['120°', '60°'], ['90°', '45°'], ['150°', '75°']], 'Tia phân giác chia đôi số đo góc.', $D);
        $this->matching($L, 'Nối mỗi cặp góc với quan hệ.', [['50° và 130°', 'Bù nhau'], ['20° và 70°', 'Phụ nhau'], ['60° và 60° đối đỉnh', 'Bằng nhau'], ['30° và 150° kề nhau', 'Kề bù']], 'Quan hệ giữa các cặp góc.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp góc vào nhóm "KỀ BÙ" hoặc "KHÔNG".', [['70° và 110° kề nhau', 'KỀ BÙ'], ['90° và 90° kề nhau', 'KỀ BÙ'], ['70° và 110° rời nhau', 'KHÔNG'], ['50° và 60°', 'KHÔNG']], 'Kề bù vừa kề nhau vừa bù nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi tổng vào nhóm "BẰNG 180°" hoặc "KHÁC".', [['70° + 110°', 'BẰNG 180°'], ['90° + 90°', 'BẰNG 180°'], ['60° + 60°', 'KHÁC'], ['100° + 70°', 'KHÁC']], 'Tổng 180° là điều kiện bù nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp góc vào nhóm "ĐỐI ĐỈNH" hoặc "KHÔNG".', [['Hai góc đối nhau qua giao điểm', 'ĐỐI ĐỈNH'], ['Hai góc kề nhau', 'KHÔNG'], ['Hai góc chung đỉnh, cạnh đối nhau', 'ĐỐI ĐỈNH'], ['Hai góc rời nhau', 'KHÔNG']], 'Đối đỉnh: chung đỉnh, mỗi cạnh đối nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp góc vào nhóm "BÙ NHAU" hoặc "PHỤ NHAU".', [['110° và 70°', 'BÙ NHAU'], ['150° và 30°', 'BÙ NHAU'], ['40° và 50°', 'PHỤ NHAU'], ['35° và 55°', 'PHỤ NHAU']], 'Bù nhau tổng 180°; phụ nhau tổng 90°.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Đối đỉnh thì bằng nhau', 'ĐÚNG'], ['Kề bù thì tổng 180°', 'ĐÚNG'], ['Phụ nhau thì tổng 180°', 'SAI'], ['Tia phân giác chia đôi góc', 'ĐÚNG']], 'Phụ nhau tổng 90°.', $D);
        $this->fill($L, 'Hai góc vừa kề nhau vừa bù nhau gọi là hai góc kề ___.', [[0, 'bù']], 'Kề bù: kề nhau và tổng 180°.', $D);
        $this->fill($L, 'Góc kề bù với góc 35° có số đo ___ độ.', [[0, '145']], '180° − 35° = 145°.', $D);
        $this->fill($L, 'Hai góc đối đỉnh thì ___ nhau.', [[0, 'bằng']], 'Tính chất quan trọng của góc đối đỉnh.', $D);
        $this->fill($L, 'Tia phân giác của góc 70° tạo với mỗi cạnh một góc ___ độ.', [[0, '35']], '70° : 2 = 35°.', $D);
        $this->fill($L, 'Hai góc có tổng 90° gọi là hai góc phụ ___.', [[0, 'nhau']], 'Phụ nhau: tổng bằng 90°.', $D);
    }

    // 53. toan-hinh-hoc-phang-lop-8-1 — Hình học phẳng lớp 8: tổng ba góc tam giác, tam giác cân (1) (lớp 8, trung bình)
    private function seedTo23(): void {
        $L = 'toan-hinh-hoc-phang-lop-8-1'; $D = 'trung_binh';
        $this->quiz($L, 'Tam giác có hai góc 50° và 60°, góc còn lại bằng?', ['70°', '60°', '80°', '90°'], 0, '180° − 50° − 60° = 70°.', $D);
        $this->quiz($L, 'Tam giác cân có góc ở đỉnh 80°, góc ở đáy bằng?', ['50°', '100°', '80°', '40°'], 0, '(180° − 80°) : 2 = 50°.', $D);
        $this->quiz($L, 'Tam giác nào sau đây là tam giác vuông cân?', ['Góc 90°, 45°, 45°', 'Góc 90°, 60°, 30°', 'Góc 60°, 60°, 60°', 'Góc 90°, 80°, 10°'], 0, 'Vuông cân: một góc 90°, hai góc 45°.', $D);
        $this->quiz($L, 'Trong tam giác cân, hai cạnh bằng nhau gọi là?', ['Cạnh bên', 'Cạnh đáy', 'Cạnh huyền', 'Cạnh đối'], 0, 'Hai cạnh bằng nhau là hai cạnh bên.', $D);
        $this->quiz($L, 'Tam giác ABC cân tại A có góc B = 65°. Góc A bằng?', ['50°', '65°', '115°', '60°'], 0, 'Góc C = góc B = 65°, góc A = 180° − 130° = 50°.', $D);
        $this->matching($L, 'Nối mỗi loại tam giác với đặc điểm.', [['Tam giác cân', 'Hai cạnh bên bằng nhau'], ['Tam giác đều', 'Ba cạnh bằng nhau'], ['Tam giác vuông cân', 'Vuông và hai cạnh góc vuông bằng nhau'], ['Tam giác thường', 'Không có gì đặc biệt']], 'Phân loại tam giác theo cạnh.', $D);
        $this->matching($L, 'Nối mỗi cặp góc với số đo góc còn lại.', [['50° và 60°', '70°'], ['90° và 45°', '45°'], ['80° và 80°', '20°'], ['100° và 30°', '50°']], 'Góc còn lại = 180° trừ tổng hai góc.', $D);
        $this->matching($L, 'Nối mỗi tam giác với tên gọi theo góc.', [['Góc 90°, 60°, 30°', 'Tam giác vuông'], ['Ba góc 60°', 'Tam giác đều'], ['Góc 120°, 30°, 30°', 'Tam giác tù'], ['Góc 70°, 60°, 50°', 'Tam giác nhọn']], 'Tên gọi theo góc lớn nhất.', $D);
        $this->matching($L, 'Nối mỗi yếu tố trong tam giác cân với tên gọi.', [['Hai cạnh bằng nhau', 'Cạnh bên'], ['Cạnh còn lại', 'Cạnh đáy'], ['Hai góc ở đáy', 'Bằng nhau'], ['Đường cao từ đỉnh', 'Cũng là trung tuyến']], 'Tính chất tam giác cân.', $D);
        $this->matching($L, 'Nối mỗi tam giác với góc ở đỉnh.', [['Cân, đáy mỗi góc 70°', 'Đỉnh 40°'], ['Cân, đáy mỗi góc 55°', 'Đỉnh 70°'], ['Đều', 'Đỉnh 60°'], ['Vuông cân', 'Đỉnh 90°']], 'Góc đỉnh = 180° trừ hai góc đáy.', $D);
        $this->sortQ($L, 'Kéo mỗi tam giác vào nhóm "TAM GIÁC CÂN" hoặc "KHÔNG".', [['Hai cạnh 5cm, 5cm', 'TAM GIÁC CÂN'], ['Hai góc đáy 60°, 60°', 'TAM GIÁC CÂN'], ['Ba cạnh 3, 4, 5', 'KHÔNG'], ['Ba góc 50°, 60°, 70°', 'KHÔNG']], 'Cân khi có hai cạnh hoặc hai góc bằng nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi bộ ba góc vào nhóm "TỔNG ĐÚNG 180°" hoặc "SAI".', [['70°, 60°, 50°', 'TỔNG ĐÚNG 180°'], ['90°, 45°, 45°', 'TỔNG ĐÚNG 180°'], ['80°, 70°, 40°', 'SAI'], ['100°, 50°, 20°', 'SAI']], 'Tổng ba góc tam giác luôn bằng 180°.', $D);
        $this->sortQ($L, 'Kéo mỗi tam giác vào nhóm "TAM GIÁC VUÔNG" hoặc "KHÔNG".', [['Góc 90°, 50°, 40°', 'TAM GIÁC VUÔNG'], ['Cạnh 3, 4, 5', 'TAM GIÁC VUÔNG'], ['Góc 80°, 60°, 40°', 'KHÔNG'], ['Ba cạnh 5, 5, 6', 'KHÔNG']], 'Vuông khi có góc 90° hoặc 3²+4² = 5².', $D);
        $this->sortQ($L, 'Kéo mỗi tam giác vào nhóm "TAM GIÁC ĐỀU" hoặc "CHỈ CÂN".', [['Ba cạnh bằng nhau', 'TAM GIÁC ĐỀU'], ['Ba góc 60°', 'TAM GIÁC ĐỀU'], ['Hai cạnh bằng nhau', 'CHỈ CÂN'], ['Hai góc đáy bằng nhau', 'CHỈ CÂN']], 'Đều là trường hợp đặc biệt của cân.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Tam giác cân có hai góc đáy bằng nhau', 'ĐÚNG'], ['Tam giác đều mỗi góc 60°', 'ĐÚNG'], ['Tam giác có hai góc vuông', 'SAI'], ['Góc ngoài bằng tổng hai góc trong không kề', 'ĐÚNG']], 'Tam giác không thể có hai góc vuông.', $D);
        $this->fill($L, 'Tam giác có hai góc 50° và 60° thì góc còn lại bằng ___ độ.', [[0, '70']], '180° − 110° = 70°.', $D);
        $this->fill($L, 'Trong tam giác cân, hai cạnh bằng nhau gọi là hai cạnh ___.', [[0, 'bên']], 'Cạnh thứ ba gọi là cạnh đáy.', $D);
        $this->fill($L, 'Tam giác ABC cân tại A, góc B = 70° thì góc C = ___ độ.', [[0, '70']], 'Hai góc ở đáy bằng nhau.', $D);
        $this->fill($L, 'Tam giác vuông cân có hai góc nhọn mỗi góc ___ độ.', [[0, '45']], '(180° − 90°) : 2 = 45°.', $D);
        $this->fill($L, 'Góc ngoài của tam giác bằng tổng hai góc trong không ___ với nó.', [[0, 'kề']], 'Tính chất góc ngoài của tam giác.', $D);
    }

    // 54. toan-hinh-hoc-phang-lop-8-2 — Hình học phẳng lớp 8: tam giác đồng dạng cơ bản (2) (lớp 8, trung bình)
    private function seedTo24(): void {
        $L = 'toan-hinh-hoc-phang-lop-8-2'; $D = 'trung_binh';
        $this->quiz($L, 'Hai tam giác đồng dạng thì các cạnh tương ứng?', ['Tỉ lệ với nhau', 'Bằng nhau', 'Vuông góc', 'Song song'], 0, 'Đồng dạng: góc bằng nhau, cạnh tỉ lệ.', $D);
        $this->quiz($L, 'ΔABC ~ ΔDEF theo tỉ số k = 3. Biết AB = 2, độ dài DE là?', ['6', '5', '2/3', '1'], 0, 'DE = k × AB = 3 × 2 = 6.', $D);
        $this->quiz($L, 'Hai tam giác đồng dạng theo tỉ số 2 thì tỉ số diện tích bằng?', ['4', '2', '8', '1'], 0, 'Tỉ số diện tích = k² = 4.', $D);
        $this->quiz($L, 'Trường hợp đồng dạng góc – góc (g-g) nghĩa là?', ['Hai góc tương ứng bằng nhau', 'Ba cạnh tỉ lệ', 'Hai cạnh bằng nhau', 'Một góc vuông'], 0, 'Hai tam giác có hai cặp góc bằng nhau thì đồng dạng.', $D);
        $this->quiz($L, 'Mọi tam giác đều có đồng dạng với nhau không?', ['Có', 'Không', 'Tùy trường hợp', 'Chỉ khi cùng màu'], 0, 'Mọi tam giác đều có ba góc 60° nên luôn đồng dạng.', $D);
        $this->matching($L, 'Nối mỗi trường hợp đồng dạng với điều kiện.', [['c-c-c', 'Ba cạnh tỉ lệ'], ['g-g', 'Hai góc bằng nhau'], ['c-g-c', 'Hai cạnh tỉ lệ, góc xen giữa bằng nhau'], ['Không có', 'Một cạnh bằng nhau']], 'Ba trường hợp đồng dạng của tam giác.', $D);
        $this->matching($L, 'Nối mỗi tỉ số đồng dạng với tỉ số chu vi.', [['k = 2', 'Tỉ số chu vi = 2'], ['k = 3', 'Tỉ số chu vi = 3'], ['k = 1/2', 'Tỉ số chu vi = 1/2'], ['k = 5', 'Tỉ số chu vi = 5']], 'Tỉ số chu vi bằng tỉ số đồng dạng.', $D);
        $this->matching($L, 'Nối mỗi dữ kiện với độ dài cạnh (k = 2).', [['AB = 3', 'DE = 6'], ['BC = 4', 'EF = 8'], ['AC = 5', 'DF = 10'], ['Chu vi ABC = 12', 'Chu vi DEF = 24']], 'Cạnh tương ứng = k × cạnh đã biết.', $D);
        $this->matching($L, 'Nối mỗi kí hiệu với ý nghĩa.', [['~', 'Đồng dạng'], ['=', 'Bằng nhau'], ['k', 'Tỉ số đồng dạng'], ['∠', 'Góc']], 'Kí hiệu trong hình học.', $D);
        $this->matching($L, 'Nối mỗi cặp tam giác với kết luận.', [['Hai tam giác đều', 'Đồng dạng'], ['Hai tam giác vuông cân', 'Đồng dạng'], ['Hai tam giác thường', 'Chưa chắc'], ['Hai tam giác vuông bất kỳ', 'Chưa chắc']], 'Tam giác đều, vuông cân luôn đồng dạng với nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp hình vào nhóm "LUÔN ĐỒNG DẠNG" hoặc "CHƯA CHẮC".', [['Hai hình vuông', 'LUÔN ĐỒNG DẠNG'], ['Hai hình tròn', 'LUÔN ĐỒNG DẠNG'], ['Hai hình chữ nhật', 'CHƯA CHẮC'], ['Hai tam giác thường', 'CHƯA CHẮC']], 'Hình vuông, hình tròn luôn đồng dạng với nhau.', $D);
        $this->sortQ($L, 'Kéo mỗi tỉ số vào nhóm "HÌNH ẢNH PHÓNG TO" hoặc "THU NHỎ".', [['k = 2', 'HÌNH ẢNH PHÓNG TO'], ['k = 3', 'HÌNH ẢNH PHÓNG TO'], ['k = 1/2', 'THU NHỎ'], ['k = 0,5', 'THU NHỎ']], 'k > 1 phóng to; k < 1 thu nhỏ.', $D);
        $this->sortQ($L, 'Kéo mỗi cặp yếu tố (ΔABC ~ ΔDEF) vào nhóm "GÓC" hoặc "CẠNH" tương ứng.', [['Góc A và góc D', 'GÓC'], ['Góc B và góc E', 'GÓC'], ['AB và DE', 'CẠNH'], ['BC và EF', 'CẠNH']], 'Thứ tự đỉnh cho biết cặp tương ứng.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Đồng dạng thì góc tương ứng bằng nhau', 'ĐÚNG'], ['Bằng nhau thì đồng dạng', 'ĐÚNG'], ['Đồng dạng thì diện tích bằng nhau', 'SAI'], ['Tỉ số diện tích = bình phương tỉ số đồng dạng', 'ĐÚNG']], 'Bằng nhau là đồng dạng tỉ số 1.', $D);
        $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm "ĐỦ KẾT LUẬN ĐỒNG DẠNG" hoặc "CHƯA ĐỦ".', [['Hai cặp góc bằng nhau', 'ĐỦ KẾT LUẬN ĐỒNG DẠNG'], ['Ba cặp cạnh tỉ lệ', 'ĐỦ KẾT LUẬN ĐỒNG DẠNG'], ['Một cặp góc bằng nhau', 'CHƯA ĐỦ'], ['Một cặp cạnh tỉ lệ', 'CHƯA ĐỦ']], 'Cần đủ điều kiện của một trường hợp.', $D);
        $this->fill($L, 'Hai tam giác đồng dạng có các góc tương ứng ___ nhau.', [[0, 'bằng']], 'Góc bằng nhau, cạnh tỉ lệ.', $D);
        $this->fill($L, 'ΔABC ~ ΔDEF theo tỉ số k = 3, AB = 5 thì DE = ___.', [[0, '15']], 'DE = 3 × 5 = 15.', $D);
        $this->fill($L, 'Tỉ số diện tích của hai tam giác đồng dạng theo tỉ số 3 bằng ___.', [[0, '9']], 'Tỉ số diện tích = k² = 9.', $D);
        $this->fill($L, 'Kí hiệu hai tam giác đồng dạng là dấu ___ (viết tên).', [[0, 'ngã']], 'Dấu ~ đọc là "đồng dạng".', $D);
        $this->fill($L, 'Hai tam giác bằng nhau thì ___ đồng dạng với nhau.', [[0, 'cũng']], 'Bằng nhau là đồng dạng với tỉ số k = 1.', $D);
    }

    // 55. toan-thpt-lop-10-lop-10-1 — Mệnh đề và tính đúng – sai của mệnh đề (lớp 10, trung bình)
    private function seedTo25(): void {
        $L = 'toan-thpt-lop-10-lop-10-1'; $D = 'trung_binh';
        $this->quiz($L, 'Trong các câu sau, câu nào là mệnh đề?', ['Số 5 là số nguyên tố.', 'Hôm nay trời đẹp quá!', 'Bạn tên gì?', 'Hãy làm bài tập đi!'], 0, 'Mệnh đề là câu khẳng định có tính đúng hoặc sai xác định.', $D);
        $this->quiz($L, 'Mệnh đề phủ định của "π là số hữu tỉ" là?', ['π không là số hữu tỉ.', 'π là số nguyên.', 'π là số tự nhiên.', 'π là số chẵn.'], 0, 'Phủ định thêm "không" vào mệnh đề gốc.', $D);
        $this->quiz($L, 'Mệnh đề "∃x ∈ ℝ, x² < 0" là mệnh đề đúng hay sai?', ['Sai', 'Đúng', 'Vừa đúng vừa sai', 'Không xác định'], 0, 'Bình phương mọi số thực đều không âm nên mệnh đề sai.', $D);
        $this->quiz($L, 'Mệnh đề kéo theo "Nếu 12 chia hết cho 4 thì 12 chia hết cho 2" là?', ['Mệnh đề đúng', 'Mệnh đề sai', 'Không phải mệnh đề', 'Câu hỏi'], 0, 'Giả thiết đúng, kết luận đúng nên mệnh đề đúng.', $D);
        $this->quiz($L, 'Ký hiệu ∀ đọc là gì?', ['Với mọi', 'Tồn tại', 'Thuộc', 'Suy ra'], 0, '∀ đọc là "với mọi".', $D);
        $this->matching($L, 'Nối mỗi mệnh đề với tính đúng – sai.', [['"9 là số chính phương"', 'Đúng'], ['"7 là số chẵn"', 'Sai'], ['"√2 là số vô tỉ"', 'Đúng'], ['"1 + 1 = 3"', 'Sai']], 'Xét tính đúng sai của mệnh đề.', $D);
        $this->matching($L, 'Nối mỗi ký hiệu với cách đọc.', [['∀', 'Với mọi'], ['∃', 'Tồn tại'], ['∈', 'Thuộc'], ['⇒', 'Suy ra']], 'Ký hiệu logic cơ bản.', $D);
        $this->matching($L, 'Nối mỗi câu với phân loại.', [['"Hà Nội là thủ đô Việt Nam"', 'Mệnh đề đúng'], ['"2 > 5"', 'Mệnh đề sai'], ['"x + 1 = 5"', 'Không phải mệnh đề'], ['"Đẹp quá!"', 'Không phải mệnh đề']], 'Câu có biến hoặc cảm thán không phải mệnh đề.', $D);
        $this->matching($L, 'Nối mỗi mệnh đề với mệnh đề phủ định.', [['"x > 3"', '"x ≤ 3"'], ['"n là số chẵn"', '"n không là số chẵn"'], ['"∀x, x ≥ 0"', '"∃x, x < 0"'], ['"Trời mưa"', '"Trời không mưa"']], 'Phủ định của ∀ là ∃ và ngược lại.', $D);
        $this->matching($L, 'Nối mỗi dạng mệnh đề với ví dụ.', [['Mệnh đề điều kiện', '"Nếu... thì..."'], ['Mệnh đề tương đương', '"... khi và chỉ khi..."'], ['Mệnh đề phủ định', '"Không phải..."'], ['Mệnh đề chứa biến', '"x + 2 = 7"']], 'Các dạng mệnh đề thường gặp.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "MỆNH ĐỀ ĐÚNG" hoặc "MỆNH ĐỀ SAI".', [['"16 chia hết cho 4"', 'MỆNH ĐỀ ĐÚNG'], ['"Số 0 là số dương"', 'MỆNH ĐỀ SAI'], ['"Tam giác có 3 cạnh"', 'MỆNH ĐỀ ĐÚNG'], ['"5 < 3"', 'MỆNH ĐỀ SAI']], 'Xét tính đúng sai rõ ràng.', $D);
        $this->sortQ($L, 'Kéo mỗi mệnh đề vào nhóm chứa "∀" hoặc "∃".', [['"∀n ∈ ℕ, n ≥ 0"', '∀'], ['"Mọi số đều dương"', '∀'], ['"∃x, x² = 2"', '∃'], ['"Có số chia hết cho 3"', '∃']], '"Mọi, tất cả" ứng với ∀; "có, tồn tại" ứng với ∃.', $D);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm "MỆNH ĐỀ" hoặc "KHÔNG PHẢI".', [['"Paris là thủ đô Pháp"', 'MỆNH ĐỀ'], ['"2 + 2 = 4"', 'MỆNH ĐỀ'], ['"Bạn bao nhiêu tuổi?"', 'KHÔNG PHẢI'], ['"Chúc ngủ ngon!"', 'KHÔNG PHẢI']], 'Câu hỏi, câu cảm thán không phải mệnh đề.', $D);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm "CÓ TÍNH ĐÚNG SAI" hoặc "KHÔNG".', [['"Số 11 là số nguyên tố"', 'CÓ TÍNH ĐÚNG SAI'], ['"Trái Đất hình cầu"', 'CÓ TÍNH ĐÚNG SAI'], ['"x là số đẹp"', 'KHÔNG'], ['"Hãy im lặng!"', 'KHÔNG']], 'Mệnh đề phải khẳng định được đúng hoặc sai.', $D);
        $this->sortQ($L, 'Kéo mỗi mệnh đề vào nhóm "ĐÚNG" hoặc "SAI" (mệnh đề kéo theo).', [['"Nếu 6 ⋮ 3 thì 6 ⋮ 2" – Đúng', 'ĐÚNG'], ['"Nếu 5 > 3 thì 5 < 3" – Sai', 'SAI'], ['"Nếu 4 ⋮ 2 thì 4 là số lẻ" – Sai', 'SAI'], ['"Nếu 9 là số chính phương thì 3² = 9" – Đúng', 'ĐÚNG']], 'P ⇒ Q sai chỉ khi P đúng mà Q sai.', $D);
        $this->fill($L, 'Mệnh đề phủ định của "x ≥ 5" là "x ___ 5".', [[0, '<']], 'Phủ định của ≥ là <.', $D);
        $this->fill($L, 'Mệnh đề "∃n ∈ ℕ, n ⋮ 3" là mệnh đề ___ (đúng/sai).', [[0, 'đúng']], 'Ví dụ n = 3 chia hết cho 3.', $D);
        $this->fill($L, 'Câu cảm thán ___ phải là mệnh đề (điền "có" hoặc "không").', [[0, 'không']], 'Câu cảm thán không khẳng định đúng sai.', $D);
        $this->fill($L, 'Ký hiệu ∀ đọc là "với ___".', [[0, 'mọi']], '∀ là ký hiệu "với mọi".', $D);
        $this->fill($L, 'Mệnh đề "Nếu P thì Q" sai khi P đúng mà Q ___.', [[0, 'sai']], 'P ⇒ Q chỉ sai trong trường hợp này.', $D);
    }

    // 56. toan-thpt-lop-10-lop-10-2 — Tập hợp và các phép toán trên tập hợp (lớp 10, trung bình)
    private function seedTo26(): void {
        $L = 'toan-thpt-lop-10-lop-10-2'; $D = 'trung_binh';
        $this->quiz($L, 'Cho A = {2; 4; 6}. Khẳng định nào đúng?', ['4 ∈ A', '5 ∈ A', '3 ∈ A', '7 ∈ A'], 0, '4 là phần tử của A.', $D);
        $this->quiz($L, 'Cho A = {1; 3; 5}, B = {3; 5; 7}. A ∪ B bằng?', ['{1; 3; 5; 7}', '{3; 5}', '{1; 7}', '{1; 3}'], 0, 'Hợp gồm tất cả phần tử của cả hai tập.', $D);
        $this->quiz($L, 'Cho A = {1; 3; 5}, B = {3; 5; 7}. A \\ B bằng?', ['{1}', '{7}', '{3; 5}', '{1; 3}'], 0, 'Hiệu A \\ B gồm phần tử thuộc A nhưng không thuộc B.', $D);
        $this->quiz($L, 'Tập hợp {x ∈ ℤ | −2 < x < 2} có bao nhiêu phần tử?', ['3', '4', '2', '5'], 0, 'Các số nguyên: −1, 0, 1 → 3 phần tử.', $D);
        $this->quiz($L, 'Ký hiệu ⊂ có nghĩa là gì?', ['Là tập con của', 'Thuộc', 'Hợp', 'Giao'], 0, 'A ⊂ B nghĩa là A là tập con của B.', $D);
        $this->matching($L, 'Nối mỗi tập hợp với số phần tử.', [['{1; 2; 3; 4}', '4 phần tử'], ['{x ∈ ℕ | x < 3}', '3 phần tử'], ['∅', '0 phần tử'], ['{5}', '1 phần tử']], 'Đếm số phần tử của tập hợp.', $D);
        $this->matching($L, 'Cho A = {1; 2}, B = {2; 3; 4}. Nối mỗi phép toán với kết quả.', [['A ∩ B', '{2}'], ['A ∪ B', '{1; 2; 3; 4}'], ['A \\ B', '{1}'], ['B \\ A', '{3; 4}']], 'Giao, hợp, hiệu của hai tập hợp.', $D);
        $this->matching($L, 'Nối mỗi ký hiệu với ý nghĩa.', [['∈', 'Thuộc'], ['∉', 'Không thuộc'], ['⊂', 'Là tập con'], ['∅', 'Tập rỗng']], 'Ký hiệu tập hợp cơ bản.', $D);
        $this->matching($L, 'Nối mỗi cách viết với tập hợp số.', [['[1; 5]', '{x ∈ ℝ | 1 ≤ x ≤ 5}'], ['(1; 5)', '{x ∈ ℝ | 1 < x < 5}'], ['[1; 5)', '{x ∈ ℝ | 1 ≤ x < 5}'], ['(1; 5]', '{x ∈ ℝ | 1 < x ≤ 5}']], 'Khoảng, đoạn trên trục số.', $D);
        $this->matching($L, 'Nối mỗi tập hợp với cách liệt kê.', [['{x ∈ ℕ | x ≤ 3}', '{0; 1; 2; 3}'], ['{x ∈ ℕ* | x < 4}', '{1; 2; 3}'], ['{x ∈ ℤ | |x| < 2}', '{−1; 0; 1}'], ['{2n | n ∈ ℕ, n < 3}', '{0; 2; 4}']], 'Liệt kê phần tử thỏa tính chất.', $D);
        $this->sortQ($L, 'Xét tập hợp {3; 5; 7}. Kéo mỗi số vào nhóm "THUỘC" hoặc "KHÔNG THUỘC".', [['3', 'THUỘC'], ['7', 'THUỘC'], ['4', 'KHÔNG THUỘC'], ['6', 'KHÔNG THUỘC']], 'Kiểm tra phần tử có trong tập không.', $D);
        $this->sortQ($L, 'Xét tập hợp {1; 2; 3; 4}. Kéo mỗi tập hợp vào nhóm "LÀ TẬP CON" hoặc "KHÔNG".', [['{1; 2}', 'LÀ TẬP CON'], ['{2; 4}', 'LÀ TẬP CON'], ['{1; 5}', 'KHÔNG'], ['{0}', 'KHÔNG']], 'Tập con có mọi phần tử đều thuộc tập mẹ.', $D);
        $this->sortQ($L, 'Kéo mỗi số vào nhóm "SỐ HỮU TỈ (ℚ)" hoặc "SỐ VÔ TỈ".', [['1/2', 'SỐ HỮU TỈ (ℚ)'], ['−3', 'SỐ HỮU TỈ (ℚ)'], ['√2', 'SỐ VÔ TỈ'], ['π', 'SỐ VÔ TỈ']], 'Số hữu tỉ viết được dạng phân số.', $D);
        $this->sortQ($L, 'Kéo mỗi tập hợp vào nhóm "HỮU HẠN" hoặc "VÔ HẠN".', [['{1; 2; 3}', 'HỮU HẠN'], ['Tập rỗng', 'HỮU HẠN'], ['ℕ', 'VÔ HẠN'], ['ℝ', 'VÔ HẠN']], 'Tập hữu hạn đếm được số phần tử.', $D);
        $this->sortQ($L, 'Cho A = {1; 2; 3}. Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['2 ∈ A', 'ĐÚNG'], ['{2} ⊂ A', 'ĐÚNG'], ['4 ∈ A', 'SAI'], ['∅ ⊂ A', 'ĐÚNG']], 'Phân biệt ∈ (phần tử) và ⊂ (tập con).', $D);
        $this->fill($L, 'Cho A = {2; 4; 6}, B = {4; 6; 8}. Khi đó A ∪ B = {2; 4; 6; ___}.', [[0, '8']], 'Hợp gồm tất cả phần tử không trùng lặp.', $D);
        $this->fill($L, 'Tập hợp có một phần tử duy nhất gọi là tập ___ (điền từ).', [[0, 'đơn']], 'Tập đơn (singleton) chỉ có một phần tử.', $D);
        $this->fill($L, 'Tập hợp các số thực x thỏa mãn 2 ≤ x < 5 viết dưới dạng [2; ___).', [[0, '5']], 'Nửa khoảng [2; 5).', $D);
        $this->fill($L, 'Ký hiệu ∈ đọc là "___" (điền từ).', [[0, 'thuộc']], 'a ∈ A đọc là "a thuộc A".', $D);
        $this->fill($L, 'Cho A = {1; 2}, số tập con của A là ___.', [[0, '4']], 'Tập n phần tử có 2ⁿ tập con: ∅, {1}, {2}, {1;2}.', $D);
    }

    // 57. toan-thpt-lop-10-lop-10-3 — Hàm số bậc hai và đồ thị parabol (lớp 10, trung bình)
    private function seedTo27(): void {
        $L = 'toan-thpt-lop-10-lop-10-3'; $D = 'trung_binh';
        $this->quiz($L, 'Tọa độ đỉnh của parabol y = x² + 2x + 1 là?', ['(−1; 0)', '(1; 4)', '(0; 1)', '(−1; 2)'], 0, 'y = (x+1)² nên đỉnh (−1; 0).', $D);
        $this->quiz($L, 'Hàm số y = x² − 2x có giá trị nhỏ nhất bằng?', ['−1', '1', '0', '−2'], 0, 'Đỉnh tại x = 1, y = 1 − 2 = −1.', $D);
        $this->quiz($L, 'Trục đối xứng của parabol y = x² − 4x là đường thẳng?', ['x = 2', 'x = −2', 'x = 4', 'x = 0'], 0, 'x = −b/(2a) = 4/2 = 2.', $D);
        $this->quiz($L, 'Parabol y = −3x² + 6x có bề lõm hướng?', ['Xuống dưới', 'Lên trên', 'Sang trái', 'Sang phải'], 0, 'a = −3 < 0 nên bề lõm hướng xuống.', $D);
        $this->quiz($L, 'Giao điểm của parabol y = x² + 1 với trục tung là?', ['(0; 1)', '(1; 0)', '(0; 0)', '(−1; 0)'], 0, 'x = 0 thì y = 1.', $D);
        $this->matching($L, 'Nối mỗi hàm số với tọa độ đỉnh.', [['y = x²', '(0; 0)'], ['y = x² − 4', '(0; −4)'], ['y = (x − 1)²', '(1; 0)'], ['y = x² + 2x + 1', '(−1; 0)']], 'Đỉnh parabol y = ax² + bx + c tại x = −b/2a.', $D);
        $this->matching($L, 'Nối mỗi hàm số với hướng bề lõm.', [['y = 2x²', 'Lên trên'], ['y = −x²', 'Xuống dưới'], ['y = x² + x', 'Lên trên'], ['y = −2x² + 3', 'Xuống dưới']], 'a > 0 lõm lên; a < 0 lõm xuống.', $D);
        $this->matching($L, 'Nối mỗi hàm số với trục đối xứng.', [['y = x²', 'x = 0'], ['y = x² − 6x', 'x = 3'], ['y = 2x² + 4x', 'x = −1'], ['y = −x² + 2x', 'x = 1']], 'Trục đối xứng x = −b/(2a).', $D);
        $this->matching($L, 'Nối mỗi hàm số với giá trị nhỏ nhất/lớn nhất.', [['y = x² + 1', 'Nhỏ nhất 1'], ['y = −x² + 4', 'Lớn nhất 4'], ['y = 2x² − 8', 'Nhỏ nhất −8'], ['y = −3x²', 'Lớn nhất 0']], 'Giá trị tại đỉnh là cực trị.', $D);
        $this->matching($L, 'Nối mỗi parabol với số giao điểm với trục hoành.', [['y = x² + 1', '0 giao điểm'], ['y = x² − 1', '2 giao điểm'], ['y = x²', '1 giao điểm'], ['y = −x² + 4', '2 giao điểm']], 'Số giao điểm phụ thuộc dấu của Δ.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "PARABOL MỞ LÊN" hoặc "MỞ XUỐNG".', [['y = 3x²', 'PARABOL MỞ LÊN'], ['y = x² − 5', 'PARABOL MỞ LÊN'], ['y = −2x²', 'PARABOL MỞ XUỐNG'], ['y = −x² + x', 'PARABOL MỞ XUỐNG']], 'Dấu của a quyết định hướng bề lõm.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "ĐI QUA GỐC O" hoặc "KHÔNG".', [['y = x²', 'ĐI QUA GỐC O'], ['y = 2x² + 3x', 'ĐI QUA GỐC O'], ['y = x² + 1', 'KHÔNG'], ['y = −x² + 2', 'KHÔNG']], 'Qua O khi c = 0.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "CÓ GIÁ TRỊ NHỎ NHẤT" hoặc "LỚN NHẤT".', [['y = x²', 'CÓ GIÁ TRỊ NHỎ NHẤT'], ['y = 5x² + 2', 'CÓ GIÁ TRỊ NHỎ NHẤT'], ['y = −x²', 'CÓ GIÁ TRỊ LỚN NHẤT'], ['y = −4x² + 1', 'CÓ GIÁ TRỊ LỚN NHẤT']], 'a > 0 có min; a < 0 có max.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "ĐỈNH TRÊN TRỤC TUNG" hoặc "KHÁC".', [['y = 3x² + 2', 'ĐỈNH TRÊN TRỤC TUNG'], ['y = −x² − 1', 'ĐỈNH TRÊN TRỤC TUNG'], ['y = x² + 2x', 'KHÁC'], ['y = 2x² − 8x', 'KHÁC']], 'Đỉnh trên trục tung khi b = 0.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Đỉnh parabol là điểm thấp nhất khi a > 0', 'ĐÚNG'], ['Trục đối xứng đi qua đỉnh', 'ĐÚNG'], ['Parabol luôn cắt trục hoành', 'SAI'], ['y = x² đối xứng qua trục tung', 'ĐÚNG']], 'Parabol y = x² + 1 không cắt trục hoành.', $D);
        $this->fill($L, 'Đỉnh của parabol y = x² + 4x + 3 có hoành độ bằng ___.', [[0, '−2']], 'x = −b/2a = −4/2 = −2.', $D);
        $this->fill($L, 'Hàm số y = 2x² − 8x + 5 đạt giá trị nhỏ nhất bằng ___.', [[0, '−3']], 'Đỉnh x = 2, y = 8 − 16 + 5 = −3.', $D);
        $this->fill($L, 'Parabol y = ax² + bx + c có bề lõm hướng xuống khi a ___ 0.', [[0, '<']], 'a < 0 thì parabol mở xuống dưới.', $D);
        $this->fill($L, 'Giao điểm của parabol y = x² + 3 với trục tung là (0; ___).', [[0, '3']], 'x = 0 cho y = 3.', $D);
        $this->fill($L, 'Trục đối xứng của parabol y = −x² + 6x là đường thẳng x = ___.', [[0, '3']], 'x = −6/(2×−1) = 3.', $D);
    }

    // 58. toan-thpt-lop-10-lop-10-4 — Dấu của tam thức bậc hai và bất phương trình bậc hai (lớp 10, khó)
    private function seedTo28(): void {
        $L = 'toan-thpt-lop-10-lop-10-4'; $D = 'kho';
        $this->quiz($L, 'Tam thức f(x) = x² − 5x + 6 có các nghiệm là?', ['x = 2 và x = 3', 'x = 1 và x = 6', 'x = −2 và x = −3', 'Vô nghiệm'], 0, 'x² − 5x + 6 = (x − 2)(x − 3).', $D);
        $this->quiz($L, 'Với a < 0 và Δ < 0, tam thức f(x) = ax² + bx + c?', ['Luôn âm với mọi x', 'Luôn dương với mọi x', 'Bằng 0', 'Đổi dấu'], 0, 'a < 0, Δ < 0 thì f(x) < 0 mọi x.', $D);
        $this->quiz($L, 'Bất phương trình x² − 9 < 0 có tập nghiệm là?', ['(−3; 3)', '(−∞; −3) ∪ (3; +∞)', '{−3; 3}', '∅'], 0, 'x² < 9 ⇔ −3 < x < 3.', $D);
        $this->quiz($L, 'Tam thức f(x) = x² − 4x + 4 mang dấu dương khi nào?', ['x ≠ 2', 'Mọi x', 'x > 2', 'x < 2'], 0, 'f(x) = (x−2)² ≥ 0, dương khi x ≠ 2.', $D);
        $this->quiz($L, 'Biệt thức Δ của tam thức x² + 2x − 3 bằng?', ['16', '4', '−8', '10'], 0, 'Δ = 4 + 12 = 16.', $D);
        $this->matching($L, 'Nối mỗi tam thức với nghiệm của nó.', [['x² − 5x + 6', 'x = 2; x = 3'], ['x² − 4', 'x = −2; x = 2'], ['x² + 2x + 1', 'x = −1 (kép)'], ['x² + 1', 'Vô nghiệm']], 'Giải phương trình bậc hai tìm nghiệm.', $D);
        $this->matching($L, 'Nối mỗi Δ với số nghiệm.', [['Δ > 0', 'Hai nghiệm phân biệt'], ['Δ = 0', 'Nghiệm kép'], ['Δ < 0', 'Vô nghiệm'], ['Δ = 25', 'Hai nghiệm phân biệt']], 'Dấu của Δ quyết định số nghiệm.', $D);
        $this->matching($L, 'Nối mỗi bất phương trình với tập nghiệm.', [['x² − 4 < 0', '(−2; 2)'], ['x² − 4 > 0', '(−∞; −2) ∪ (2; +∞)'], ['(x − 1)² ≤ 0', '{1}'], ['x² + 1 > 0', 'ℝ']], 'Xét dấu tam thức để giải bất phương trình.', $D);
        $this->matching($L, 'Nối mỗi trường hợp (a, Δ) với dấu của tam thức.', [['a > 0, Δ < 0', 'Luôn dương'], ['a < 0, Δ < 0', 'Luôn âm'], ['a > 0, Δ > 0', 'Đổi dấu qua nghiệm'], ['a > 0, Δ = 0', 'Không âm']], 'Định lý về dấu tam thức bậc hai.', $D);
        $this->matching($L, 'Nối mỗi tam thức với dạng phân tích.', [['x² − 9', '(x − 3)(x + 3)'], ['x² − 6x + 9', '(x − 3)²'], ['2x² − 8', '2(x − 2)(x + 2)'], ['x² + 5x + 6', '(x + 2)(x + 3)']], 'Phân tích thành nhân tử.', $D);
        $this->sortQ($L, 'Kéo mỗi tam thức vào nhóm theo số nghiệm.', [['x² − 1', 'HAI NGHIỆM'], ['x² − 5x + 6', 'HAI NGHIỆM'], ['x² + 2x + 1', 'NGHIỆM KÉP'], ['x² + 4', 'VÔ NGHIỆM']], 'Tính Δ để biết số nghiệm.', $D);
        $this->sortQ($L, 'Kéo mỗi tam thức vào nhóm "LUÔN DƯƠNG" hoặc "LUÔN ÂM".', [['x² + 1', 'LUÔN DƯƠNG'], ['2x² + 3', 'LUÔN DƯƠNG'], ['−x² − 2', 'LUÔN ÂM'], ['−3x² − 1', 'LUÔN ÂM']], 'a và Δ cùng quyết định dấu.', $D);
        $this->sortQ($L, 'Kéo mỗi tam thức vào nhóm "NGHIỆM NGUYÊN" hoặc "KHÔNG".', [['x² − 3x + 2', 'NGHIỆM NGUYÊN'], ['x² − 7x + 10', 'NGHIỆM NGUYÊN'], ['x² − 2', 'KHÔNG'], ['x² + x + 1', 'KHÔNG']], 'x²−3x+2 = (x−1)(x−2).', $D);
        $this->sortQ($L, 'Kéo mỗi tam thức vào nhóm có "a > 0" hoặc "a < 0".', [['3x² − 2x', 'a > 0'], ['x² + 5', 'a > 0'], ['−2x² + x', 'a < 0'], ['−x² − 7', 'a < 0']], 'Hệ số a là hệ số của x².', $D);
        $this->sortQ($L, 'Kéo mỗi bất phương trình vào nhóm "NGHIỆM LÀ KHOẢNG" hoặc "KHÁC".', [['x² − 4 < 0', 'NGHIỆM LÀ KHOẢNG'], ['x² − 1 > 0', 'KHÁC'], ['(x − 2)² < 0', 'KHÁC'], ['x² + 1 < 0', 'KHÁC']], '(x−2)² < 0 vô nghiệm; x²+1 < 0 vô nghiệm.', $D);
        $this->fill($L, 'Nghiệm của x² − 8x + 15 = 0 là x = 3 và x = ___.', [[0, '5']], '(x − 3)(x − 5) = 0.', $D);
        $this->fill($L, 'Biệt thức Δ của tam thức 3x² − 6x + 2 bằng ___.', [[0, '12']], 'Δ = 36 − 24 = 12.', $D);
        $this->fill($L, 'Tập nghiệm của (x − 2)(x − 6) < 0 là khoảng (2; ___).', [[0, '6']], 'Trong trái, ngoài cùng: nghiệm giữa hai nghiệm.', $D);
        $this->fill($L, 'Khi Δ < 0 và a > 0, tam thức luôn ___ (điền "dương" hoặc "âm").', [[0, 'dương']], 'Cùng dấu với a mọi x.', $D);
        $this->fill($L, 'Tam thức x² − 2x + 1 = (x − ___)².', [[0, '1']], 'Bình phương của hiệu: (x − 1)².', $D);
    }

    // 59. toan-thpt-lop-11-lop-11-1 — Góc lượng giác và giá trị lượng giác của góc đặc biệt (lớp 11, trung bình)
    private function seedTo29(): void {
        $L = 'toan-thpt-lop-11-lop-11-1'; $D = 'trung_binh';
        $this->quiz($L, 'Giá trị của cos 30° bằng?', ['√3/2', '1/2', '√2/2', '1'], 0, 'cos 30° = √3/2.', $D);
        $this->quiz($L, 'Giá trị của sin 60° bằng?', ['1/2', '√3/2', '√2/2', '0'], 1, 'sin 60° = √3/2.', $D);
        $this->quiz($L, 'Giá trị của tan 30° bằng?', ['√3/3', '√3', '1', '0'], 0, 'tan 30° = 1/√3 = √3/3.', $D);
        $this->quiz($L, 'Góc 90° đổi sang radian bằng?', ['π/2', 'π', 'π/4', '2π'], 0, '180° = π rad nên 90° = π/2.', $D);
        $this->quiz($L, 'Đẳng thức nào đúng với mọi góc α?', ['tan α · cot α = 1', 'sin α + cos α = 1', 'tan α = sin α − cos α', 'cot α = cos α + sin α'], 0, 'tan α · cot α = 1 (khi xác định).', $D);
        $this->matching($L, 'Nối mỗi góc với giá trị sin.', [['30°', '1/2'], ['45°', '√2/2'], ['60°', '√3/2'], ['90°', '1']], 'Bảng giá trị lượng giác góc đặc biệt.', $D);
        $this->matching($L, 'Nối mỗi góc với giá trị cos.', [['60°', '1/2'], ['45°', '√2/2'], ['30°', '√3/2'], ['0°', '1']], 'cos 60° = sin 30° = 1/2.', $D);
        $this->matching($L, 'Nối mỗi công thức với tên gọi.', [['sin²α + cos²α = 1', 'Công thức cơ bản'], ['tan α = sin α/cos α', 'Định nghĩa tan'], ['1 + tan²α = 1/cos²α', 'Hệ quả'], ['cot α = cos α/sin α', 'Định nghĩa cot']], 'Các công thức lượng giác cơ bản.', $D);
        $this->matching($L, 'Nối mỗi góc với giá trị tan.', [['45°', '1'], ['30°', '√3/3'], ['60°', '√3'], ['0°', '0']], 'tan = sin/cos.', $D);
        $this->matching($L, 'Nối mỗi góc (radian) với độ.', [['π/6', '30°'], ['π/4', '45°'], ['π/3', '60°'], ['π/2', '90°']], 'Đổi radian sang độ.', $D);
        $this->sortQ($L, 'Kéo mỗi giá trị sin vào nhóm "DƯƠNG" hoặc "ÂM" (góc phần tư II).', [['sin 120°', 'DƯƠNG'], ['sin 150°', 'DƯƠNG'], ['cos 120°', 'ÂM'], ['cos 150°', 'ÂM']], 'Góc phần tư II: sin dương, cos âm.', $D);
        $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm "BẰNG 0", "BẰNG 1" hoặc "BẰNG −1".', [['sin 0°', 'BẰNG 0'], ['cos 0°', 'BẰNG 1'], ['cos 180°', 'BẰNG −1'], ['sin 180°', 'BẰNG 0']], 'Giá trị tại các góc đặc biệt.', $D);
        $this->sortQ($L, 'Kéo mỗi góc vào nhóm "PHẦN TƯ I" hoặc "PHẦN TƯ II".', [['30°', 'PHẦN TƯ I'], ['80°', 'PHẦN TƯ I'], ['100°', 'PHẦN TƯ II'], ['170°', 'PHẦN TƯ II']], 'Phần tư I: 0°–90°; phần tư II: 90°–180°.', $D);
        $this->sortQ($L, 'Kéo mỗi đẳng thức vào nhóm "ĐÚNG" hoặc "SAI".', [['sin²α + cos²α = 1', 'ĐÚNG'], ['tan α · cot α = 1', 'ĐÚNG'], ['sin 2α = 2 sin α', 'SAI'], ['cos 90° = 1', 'SAI']], 'sin 2α = 2 sin α cos α; cos 90° = 0.', $D);
        $this->sortQ($L, 'Kéo mỗi góc vào nhóm "TAN XÁC ĐỊNH" hoặc "KHÔNG".', [['30°', 'TAN XÁC ĐỊNH'], ['45°', 'TAN XÁC ĐỊNH'], ['90°', 'KHÔNG'], ['270°', 'KHÔNG']], 'tan không xác định khi cos = 0.', $D);
        $this->fill($L, 'sin 30° + cos 60° = ___ (điền số).', [[0, '1']], '1/2 + 1/2 = 1.', $D);
        $this->fill($L, 'Góc 90° tương ứng với π/___ radian.', [[0, '2']], '90° = π/2 rad.', $D);
        $this->fill($L, 'cot α có nghĩa khi sin α ___ 0 (điền "bằng" hoặc "khác").', [[0, 'khác']], 'cot α = cos α/sin α nên cần sin α khác 0.', $D);
        $this->fill($L, 'cos 0° = ___ (điền số).', [[0, '1']], 'cos 0° = 1.', $D);
        $this->fill($L, 'Theo công thức, 1 + cot²α = 1/___²α.', [[0, 'sin']], '1 + cot²α = 1/sin²α.', $D);
    }

    // 60. toan-thpt-lop-11-lop-11-2 — Phương trình lượng giác cơ bản (lớp 11, khó)
    private function seedTo30(): void {
        $L = 'toan-thpt-lop-11-lop-11-2'; $D = 'kho';
        $this->quiz($L, 'Nghiệm của phương trình cos x = 0 là?', ['x = π/2 + kπ', 'x = kπ', 'x = k2π', 'Vô nghiệm'], 0, 'cos x = 0 tại x = π/2 + kπ.', $D);
        $this->quiz($L, 'Nghiệm của phương trình sin x = 1 là?', ['x = π/2 + k2π', 'x = kπ', 'x = π + k2π', 'Vô nghiệm'], 0, 'sin x = 1 tại x = π/2 + k2π.', $D);
        $this->quiz($L, 'Phương trình cos x = 2 có nghiệm không?', ['Không', 'Có vô số', 'Có 1 nghiệm', 'Có 2 nghiệm'], 0, '|cos x| ≤ 1 nên cos x = 2 vô nghiệm.', $D);
        $this->quiz($L, 'Phương trình tan x = 0 có họ nghiệm?', ['x = kπ', 'x = π/2 + kπ', 'x = k2π', 'Vô nghiệm'], 0, 'tan x = 0 khi sin x = 0: x = kπ.', $D);
        $this->quiz($L, 'Số nghiệm của sin x = 1/2 trên [0; 2π] là?', ['2 nghiệm', '1 nghiệm', '4 nghiệm', 'Vô số'], 0, 'x = π/6 và x = 5π/6.', $D);
        $this->matching($L, 'Nối mỗi phương trình với họ nghiệm.', [['sin x = 0', 'x = kπ'], ['cos x = 0', 'x = π/2 + kπ'], ['tan x = 0', 'x = kπ'], ['cot x = 0', 'x = π/2 + kπ']], 'Phương trình lượng giác đặc biệt.', $D);
        $this->matching($L, 'Nối mỗi phương trình với điều kiện xác định.', [['tan x', 'x ≠ π/2 + kπ'], ['cot x', 'x ≠ kπ'], ['1/sin x', 'x ≠ kπ'], ['1/cos x', 'x ≠ π/2 + kπ']], 'Mẫu số phải khác 0.', $D);
        $this->matching($L, 'Nối mỗi phương trình với số nghiệm trên [0; 2π].', [['sin x = 0', '3 nghiệm'], ['cos x = 1', '2 nghiệm'], ['tan x = 1 (xác định)', '2 nghiệm'], ['sin x = 2', '0 nghiệm']], 'Đếm nghiệm trên đoạn.', $D);
        $this->matching($L, 'Nối mỗi giá trị đặc biệt với kết quả.', [['sin π/6', '1/2'], ['cos π/3', '1/2'], ['tan π/4', '1'], ['sin π/2', '1']], 'Giá trị lượng giác góc đặc biệt.', $D);
        $this->matching($L, 'Nối mỗi phương trình với họ nghiệm đúng.', [['sin x = −1', 'x = −π/2 + k2π'], ['cos x = −1', 'x = π + k2π'], ['sin x = 1', 'x = π/2 + k2π'], ['cos x = 0', 'x = π/2 + kπ']], 'Họ nghiệm đặc biệt cần nhớ.', $D);
        $this->sortQ($L, 'Kéo mỗi phương trình vào nhóm "CÓ NGHIỆM" hoặc "VÔ NGHIỆM".', [['sin x = 1/2', 'CÓ NGHIỆM'], ['cos x = −1', 'CÓ NGHIỆM'], ['sin x = 2', 'VÔ NGHIỆM'], ['cos x = −2', 'VÔ NGHIỆM']], '|sin x|, |cos x| ≤ 1.', $D);
        $this->sortQ($L, 'Kéo mỗi họ nghiệm vào nhóm "CHỨA kπ" hoặc "CHỨA k2π".', [['x = kπ', 'CHỨA kπ'], ['x = π/2 + kπ', 'CHỨA kπ'], ['x = π/2 + k2π', 'CHỨA k2π'], ['x = k2π', 'CHỨA k2π']], 'Chu kỳ sin, cos là 2π; tan, cot là π.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['sin x = 0 ⇔ x = kπ', 'ĐÚNG'], ['cos x = 1 ⇔ x = k2π', 'ĐÚNG'], ['tan x xác định mọi x', 'SAI'], ['Phương trình sin x = 2 có nghiệm', 'SAI']], 'tan không xác định tại π/2 + kπ.', $D);
        $this->sortQ($L, 'Kéo mỗi phương trình vào nhóm "NGHIỆM ĐẶC BIỆT" hoặc "DẠNG TỔNG QUÁT".', [['sin x = 0', 'NGHIỆM ĐẶC BIỆT'], ['cos x = 1', 'NGHIỆM ĐẶC BIỆT'], ['sin x = 1/3', 'DẠNG TỔNG QUÁT'], ['cos x = −1/2', 'DẠNG TỔNG QUÁT']], 'Giá trị đặc biệt 0, ±1 có họ nghiệm gọn.', $D);
        $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm "TRONG [−1; 1]" hoặc "NGOÀI".', [['1/2', 'TRONG [−1; 1]'], ['−1', 'TRONG [−1; 1]'], ['2', 'NGOÀI'], ['−3/2', 'NGOÀI']], 'sin x = a, cos x = a có nghiệm khi |a| ≤ 1.', $D);
        $this->fill($L, 'Phương trình sin x = −1 có họ nghiệm x = −π/2 + k___ (điền ký hiệu).', [[0, '2π']], 'Chu kỳ của sin là 2π.', $D);
        $this->fill($L, 'Phương trình cos x = 0 có họ nghiệm x = π/2 + k___ (điền ký hiệu).', [[0, 'π']], 'Hai điểm đối nhau trên đường tròn, chu kỳ π.', $D);
        $this->fill($L, 'Điều kiện xác định của cot x là x ≠ k___ (điền ký hiệu).', [[0, 'π']], 'cot x = cos x/sin x, cần sin x ≠ 0.', $D);
        $this->fill($L, 'Phương trình cos x = 1/2 có một nghiệm là x = π/___ (điền số).', [[0, '3']], 'cos π/3 = 1/2.', $D);
        $this->fill($L, 'Phương trình sin x = a có nghiệm khi |a| ___ 1 (điền dấu).', [[0, '≤']], '|sin x| ≤ 1 mọi x.', $D);
    }

    // 61. toan-thpt-lop-11-lop-11-3 — Cấp số cộng: số hạng tổng quát và tổng n số hạng đầu (lớp 11, trung bình)
    private function seedTo31(): void {
        $L = 'toan-thpt-lop-11-lop-11-3'; $D = 'trung_binh';
        $this->quiz($L, 'Cho cấp số cộng có u₁ = 5, d = 3. Số hạng u₄ bằng?', ['14', '11', '17', '12'], 0, 'u₄ = u₁ + 3d = 5 + 9 = 14.', $D);
        $this->quiz($L, 'Dãy số nào sau đây là cấp số cộng?', ['2; 5; 8; 11', '1; 2; 4; 8', '3; 3; 4; 4', '5; 10; 15; 21'], 0, 'Hiệu hai số liên tiếp đều bằng 3.', $D);
        $this->quiz($L, 'Tổng 5 số hạng đầu của cấp số cộng có u₁ = 2, d = 2 bằng?', ['30', '20', '25', '40'], 0, 'S₅ = 5(2×2 + 4×2)/2 = 5×12/2 = 30.', $D);
        $this->quiz($L, 'Cấp số cộng có u₁ = 10, u₂ = 7. Công sai d bằng?', ['−3', '3', '17', '−17'], 0, 'd = u₂ − u₁ = 7 − 10 = −3.', $D);
        $this->quiz($L, 'Cho cấp số cộng 1; 4; 7; 10; ... Số hạng thứ 10 là?', ['28', '25', '31', '22'], 0, 'u₁₀ = 1 + 9×3 = 28.', $D);
        $this->matching($L, 'Nối mỗi cấp số cộng với công sai.', [['1; 4; 7; 10', 'd = 3'], ['10; 7; 4; 1', 'd = −3'], ['5; 5; 5; 5', 'd = 0'], ['2; 6; 10; 14', 'd = 4']], 'd = u₂ − u₁.', $D);
        $this->matching($L, 'Nối mỗi bộ (u₁, d, n) với số hạng uₙ.', [['u₁=2, d=3, n=4', 'u₄ = 11'], ['u₁=5, d=−2, n=3', 'u₃ = 1'], ['u₁=1, d=5, n=5', 'u₅ = 21'], ['u₁=10, d=0, n=7', 'u₇ = 10']], 'uₙ = u₁ + (n−1)d.', $D);
        $this->matching($L, 'Nối mỗi công thức với ý nghĩa.', [['uₙ = u₁ + (n−1)d', 'Số hạng tổng quát'], ['Sₙ = n(u₁+uₙ)/2', 'Tổng n số hạng đầu'], ['d = u₂ − u₁', 'Công sai'], ['uₙ₊₁ − uₙ = d', 'Định nghĩa']], 'Công thức cấp số cộng.', $D);
        $this->matching($L, 'Nối mỗi tổng với giá trị.', [['1+2+...+10', '55'], ['2+4+6+8', '20'], ['5+5+5 (3 số)', '15'], ['1+3+5+7', '16']], 'Tổng cấp số cộng.', $D);
        $this->matching($L, 'Nối mỗi dãy với công sai.', [['3; 8; 13; 18', 'd = 5'], ['20; 15; 10; 5', 'd = −5'], ['7; 7; 7', 'd = 0'], ['1; 6; 11', 'd = 5']], 'Hiệu số liên tiếp không đổi.', $D);
        $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm "LÀ CẤP SỐ CỘNG" hoặc "KHÔNG".', [['4; 7; 10; 13', 'LÀ CẤP SỐ CỘNG'], ['9; 6; 3; 0', 'LÀ CẤP SỐ CỘNG'], ['1; 3; 6; 10', 'KHÔNG'], ['2; 4; 8; 16', 'KHÔNG']], 'Cấp số cộng có hiệu không đổi.', $D);
        $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm theo dấu của công sai.', [['1; 3; 5', 'd DƯƠNG'], ['10; 8; 6', 'd ÂM'], ['4; 4; 4', 'd BẰNG 0'], ['2; 5; 8', 'd DƯƠNG']], 'd > 0 dãy tăng; d < 0 dãy giảm.', $D);
        $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm "DÃY TĂNG", "DÃY GIẢM" hoặc "KHÔNG ĐỔI".', [['2; 5; 8', 'DÃY TĂNG'], ['9; 6; 3', 'DÃY GIẢM'], ['7; 7; 7', 'DÃY KHÔNG ĐỔI'], ['1; 4; 7', 'DÃY TĂNG']], 'Dấu của d quyết định tính tăng giảm.', $D);
        $this->sortQ($L, 'Xét cấp số cộng 3; 7; 11; 15; ... Kéo mỗi số vào nhóm "THUỘC" hoặc "KHÔNG".', [['19', 'THUỘC'], ['23', 'THUỘC'], ['20', 'KHÔNG'], ['14', 'KHÔNG']], 'uₙ = 3 + (n−1)×4: 19, 23 thuộc dãy.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Cấp số cộng có d không đổi', 'ĐÚNG'], ['u₅ = u₁ + 4d', 'ĐÚNG'], ['Dãy hằng không phải cấp số cộng', 'SAI'], ['Sₙ tính được khi biết u₁, uₙ, n', 'ĐÚNG']], 'Dãy hằng là cấp số cộng với d = 0.', $D);
        $this->fill($L, 'Cấp số cộng có u₁ = 6, d = 4. Số hạng u₃ = ___.', [[0, '14']], 'u₃ = 6 + 2×4 = 14.', $D);
        $this->fill($L, 'Công thức số hạng tổng quát: uₙ = u₁ + (n − ___)d.', [[0, '1']], 'uₙ = u₁ + (n−1)d.', $D);
        $this->fill($L, 'Dãy 10; 7; 4; 1; ... có công sai d = ___.', [[0, '−3']], 'd = 7 − 10 = −3.', $D);
        $this->fill($L, 'Tổng Sₙ = n[2u₁ + (n − 1)d]/___.', [[0, '2']], 'Chia cho 2.', $D);
        $this->fill($L, 'Nếu u₁ = 2, d = 5 thì u₆ = ___.', [[0, '27']], 'u₆ = 2 + 5×5 = 27.', $D);
    }

    // 62. toan-thpt-lop-11-lop-11-4 — Cấp số nhân: số hạng tổng quát và tổng n số hạng đầu (lớp 11, khó)
    private function seedTo32(): void {
        $L = 'toan-thpt-lop-11-lop-11-4'; $D = 'kho';
        $this->quiz($L, 'Cho cấp số nhân có u₁ = 3, q = 2. Số hạng u₄ bằng?', ['24', '12', '18', '9'], 0, 'u₄ = 3 × 2³ = 24.', $D);
        $this->quiz($L, 'Dãy số nào sau đây là cấp số nhân?', ['2; 6; 18; 54', '2; 4; 6; 8', '1; 3; 5; 7', '5; 5; 6; 6'], 0, 'Tỉ số liên tiếp đều bằng 3.', $D);
        $this->quiz($L, 'Tổng 3 số hạng đầu của cấp số nhân u₁ = 4, q = 3 bằng?', ['52', '36', '40', '48'], 0, '4 + 12 + 36 = 52.', $D);
        $this->quiz($L, 'Cấp số nhân có u₁ = 8, u₂ = 4. Công bội q bằng?', ['1/2', '2', '−4', '4'], 0, 'q = u₂/u₁ = 4/8 = 1/2.', $D);
        $this->quiz($L, 'Cho cấp số nhân 5; 10; 20; ... Số hạng thứ 5 là?', ['80', '40', '160', '60'], 0, 'u₅ = 5 × 2⁴ = 80.', $D);
        $this->matching($L, 'Nối mỗi cấp số nhân với công bội.', [['2; 6; 18', 'q = 3'], ['10; 5; 2,5', 'q = 0,5'], ['3; −6; 12', 'q = −2'], ['7; 7; 7', 'q = 1']], 'q = u₂/u₁.', $D);
        $this->matching($L, 'Nối mỗi bộ (u₁, q, n) với số hạng uₙ.', [['u₁=2, q=3, n=3', 'u₃ = 18'], ['u₁=5, q=2, n=4', 'u₄ = 40'], ['u₁=10, q=0,5, n=3', 'u₃ = 2,5'], ['u₁=1, q=−2, n=4', 'u₄ = −8']], 'uₙ = u₁ × qⁿ⁻¹.', $D);
        $this->matching($L, 'Nối mỗi công thức với ý nghĩa.', [['uₙ = u₁qⁿ⁻¹', 'Số hạng tổng quát'], ['Sₙ = u₁(qⁿ−1)/(q−1)', 'Tổng n số hạng đầu'], ['q = u₂/u₁', 'Công bội'], ['uₙ² = uₙ₋₁uₙ₊₁', 'Tính chất']], 'Công thức cấp số nhân.', $D);
        $this->matching($L, 'Nối mỗi tổng với giá trị.', [['1+2+4', '7'], ['3+9+27', '39'], ['5+10 (2 số)', '15'], ['2+6+18+54', '80']], 'Cộng các số hạng.', $D);
        $this->matching($L, 'Nối mỗi dãy với công bội.', [['4; 12; 36', 'q = 3'], ['16; 8; 4', 'q = 1/2'], ['2; −4; 8', 'q = −2'], ['9; 9; 9', 'q = 1']], 'Tỉ số hai số liên tiếp.', $D);
        $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm "LÀ CẤP SỐ NHÂN" hoặc "KHÔNG".', [['3; 9; 27', 'LÀ CẤP SỐ NHÂN'], ['5; 10; 20', 'LÀ CẤP SỐ NHÂN'], ['2; 4; 7', 'KHÔNG'], ['1; 2; 4; 9', 'KHÔNG']], 'Tỉ số liên tiếp phải không đổi.', $D);
        $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm "CÔNG BỘI DƯƠNG" hoặc "ÂM".', [['2; 6; 18', 'CÔNG BỘI DƯƠNG'], ['4; 2; 1', 'CÔNG BỘI DƯƠNG'], ['3; −6; 12', 'CÔNG BỘI ÂM'], ['5; −10; 20', 'CÔNG BỘI ÂM']], 'q âm thì dấu các số hạng xen kẽ.', $D);
        $this->sortQ($L, 'Kéo mỗi dãy số vào nhóm "CÓ q = 2" hoặc "KHÁC".', [['1; 2; 4', 'CÓ q = 2'], ['3; 6; 12', 'CÓ q = 2'], ['2; 6; 18', 'KHÁC'], ['5; 10; 15', 'KHÁC']], 'q = 2 khi số sau gấp đôi số trước.', $D);
        $this->sortQ($L, 'Xét cấp số nhân 2; 6; 18; 54; ... Kéo mỗi số vào nhóm "THUỘC" hoặc "KHÔNG".', [['162', 'THUỘC'], ['486', 'THUỘC'], ['100', 'KHÔNG'], ['36', 'KHÔNG']], 'uₙ = 2 × 3ⁿ⁻¹: 162, 486 thuộc dãy.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Cấp số nhân có q không đổi', 'ĐÚNG'], ['u₃ = u₁q²', 'ĐÚNG'], ['q = 0 được phép', 'SAI'], ['Dãy 1; 2; 4 là cấp số nhân', 'ĐÚNG']], 'Công bội q phải khác 0.', $D);
        $this->fill($L, 'Cấp số nhân có u₁ = 4, q = 3. Số hạng u₃ = ___.', [[0, '36']], 'u₃ = 4 × 3² = 36.', $D);
        $this->fill($L, 'Dãy 8; 4; 2; 1; ... có công bội q = ___.', [[0, '1/2']], 'q = 4/8 = 1/2.', $D);
        $this->fill($L, 'Công thức số hạng tổng quát: uₙ = u₁q^(n − ___) (điền số).', [[0, '1']], 'uₙ = u₁qⁿ⁻¹.', $D);
        $this->fill($L, 'Tổng S₂ của cấp số nhân u₁ = 6, q = 4 là ___.', [[0, '30']], '6 + 24 = 30.', $D);
        $this->fill($L, 'Nếu ba số a, b, c lập thành cấp số nhân thì b² = a × ___.', [[0, 'c']], 'Tính chất: bình phương số giữa bằng tích hai số kề.', $D);
    }

    // 63. toan-thpt-lop-12-lop-12-1 — Đạo hàm: định nghĩa và các công thức cơ bản (lớp 12, trung bình)
    private function seedTo33(): void {
        $L = 'toan-thpt-lop-12-lop-12-1'; $D = 'trung_binh';
        $this->quiz($L, 'Đạo hàm của hàm số y = x⁴ bằng?', ['4x³', 'x³', '4x⁴', '3x³'], 0, '(xⁿ)′ = n·xⁿ⁻¹.', $D);
        $this->quiz($L, 'Đạo hàm của hàm số y = cos x bằng?', ['−sin x', 'sin x', 'cos x', '−cos x'], 0, '(cos x)′ = −sin x.', $D);
        $this->quiz($L, 'Đạo hàm của hàm số y = eˣ bằng?', ['eˣ', 'x·eˣ⁻¹', '1', '0'], 0, '(eˣ)′ = eˣ.', $D);
        $this->quiz($L, 'Đạo hàm của hàm số y = ln x (x > 0) bằng?', ['1/x', 'x', 'ln x', '0'], 0, '(ln x)′ = 1/x.', $D);
        $this->quiz($L, 'Đạo hàm của hàm số y = 2x⁵ tại x = 1 bằng?', ['10', '5', '2', '1'], 0, 'y′ = 10x⁴, tại x = 1 bằng 10.', $D);
        $this->matching($L, 'Nối mỗi hàm số với đạo hàm của nó.', [['y = x⁵', 'y′ = 5x⁴'], ['y = x²', 'y′ = 2x'], ['y = 7', 'y′ = 0'], ['y = 3x', 'y′ = 3']], 'Công thức đạo hàm cơ bản.', $D);
        $this->matching($L, 'Nối mỗi hàm số với đạo hàm của nó.', [['y = sin x', 'y′ = cos x'], ['y = cos x', 'y′ = −sin x'], ['y = eˣ', 'y′ = eˣ'], ['y = ln x', 'y′ = 1/x']], 'Đạo hàm lượng giác, mũ, logarit.', $D);
        $this->matching($L, 'Nối mỗi hàm số với đạo hàm tại điểm.', [['y = x² tại x = 3', 'y′ = 6'], ['y = x³ tại x = 2', 'y′ = 12'], ['y = 4x tại x = 5', 'y′ = 4'], ['y = 10 tại x = 1', 'y′ = 0']], 'Tính đạo hàm rồi thay x.', $D);
        $this->matching($L, 'Nối mỗi ký hiệu với ý nghĩa.', [['y′', 'Đạo hàm của y'], ['f′(x)', 'Đạo hàm của f tại x'], ['dy/dx', 'Đạo hàm y theo x'], ['y″', 'Đạo hàm cấp hai']], 'Ký hiệu đạo hàm.', $D);
        $this->matching($L, 'Nối mỗi hàm số với đạo hàm của nó.', [['y = √x', 'y′ = 1/(2√x)'], ['y = 1/x²', 'y′ = −2/x³'], ['y = x√x', 'y′ = (3/2)√x'], ['y = 5/x', 'y′ = −5/x²']], 'Viết về dạng lũy thừa rồi đạo hàm.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "ĐẠO HÀM BẰNG 0" hoặc "KHÁC 0".', [['y = 8', 'ĐẠO HÀM BẰNG 0'], ['y = −3', 'ĐẠO HÀM BẰNG 0'], ['y = x', 'KHÁC 0'], ['y = 2x + 1', 'KHÁC 0']], 'Đạo hàm hàm hằng bằng 0.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "ĐẠO HÀM LÀ ĐA THỨC" hoặc "KHÔNG".', [['y = x³', 'ĐẠO HÀM LÀ ĐA THỨC'], ['y = 5x² + 2x', 'ĐẠO HÀM LÀ ĐA THỨC'], ['y = sin x', 'KHÔNG'], ['y = 1/x', 'KHÔNG']], 'Đạo hàm đa thức vẫn là đa thức.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "CÓ ĐẠO HÀM TẠI x = 1" hoặc "KHÔNG".', [['y = x²', 'CÓ ĐẠO HÀM TẠI x = 1'], ['y = ln x', 'CÓ ĐẠO HÀM TẠI x = 1'], ['y = 1/x', 'CÓ ĐẠO HÀM TẠI x = 1'], ['y = √x tại x = 0', 'KHÔNG']], '√x không có đạo hàm tại x = 0.', $D);
        $this->sortQ($L, 'Kéo mỗi đạo hàm vào nhóm "LÀ HẰNG SỐ" hoặc "CHỨA x".', [['(5)′ = 0', 'LÀ HẰNG SỐ'], ['(7x)′ = 7', 'LÀ HẰNG SỐ'], ['(x²)′ = 2x', 'CHỨA x'], ['(x³)′ = 3x²', 'CHỨA x']], 'Đạo hàm hàm bậc nhất là hằng số.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['(xⁿ)′ = n·xⁿ⁻¹', 'ĐÚNG'], ['(sin x)′ = cos x', 'ĐÚNG'], ['(eˣ)′ = x·eˣ⁻¹', 'SAI'], ['(ln x)′ = x', 'SAI']], '(eˣ)′ = eˣ; (ln x)′ = 1/x.', $D);
        $this->fill($L, 'Đạo hàm của y = 5x⁴ là y′ = 20x^___.', [[0, '3']], '(x⁴)′ = 4x³, nhân 5 được 20x³.', $D);
        $this->fill($L, 'Đạo hàm của y = cos x là y′ = −___ x.', [[0, 'sin']], '(cos x)′ = −sin x.', $D);
        $this->fill($L, 'Nếu y = x⁹ thì y′ = 9x^___.', [[0, '8']], 'Hạ số mũ xuống, trừ 1 ở số mũ.', $D);
        $this->fill($L, 'Đạo hàm của y = eˣ + 1 là y′ = ___.', [[0, 'eˣ']], '(eˣ)′ = eˣ; đạo hàm hằng số bằng 0.', $D);
        $this->fill($L, 'Đạo hàm của y = 6/x là y′ = −6/x^___.', [[0, '2']], '(6/x)′ = −6/x².', $D);
    }

    // 64. toan-thpt-lop-12-lop-12-2 — Quy tắc tính đạo hàm: tổng, tích, thương và hàm hợp (lớp 12, khó)
    private function seedTo34(): void {
        $L = 'toan-thpt-lop-12-lop-12-2'; $D = 'kho';
        $this->quiz($L, 'Đạo hàm của y = x³ − 2x² bằng?', ['3x² − 4x', '3x² − 2x', 'x² − 4x', '3x − 4'], 0, 'Đạo hàm từng hạng tử: 3x² − 4x.', $D);
        $this->quiz($L, 'Đạo hàm của y = x² · cos x bằng?', ['2x·cos x − x²·sin x', '2x·cos x', '−x²·sin x', '2x + cos x'], 0, 'Quy tắc tích: (uv)′ = u′v + uv′.', $D);
        $this->quiz($L, 'Đạo hàm của y = sin(2x) bằng?', ['2cos(2x)', 'cos(2x)', '−2cos(2x)', '2sin(2x)'], 0, 'Hàm hợp: (sin u)′ = u′·cos u.', $D);
        $this->quiz($L, 'Đạo hàm của y = (3x − 1)³ bằng?', ['9(3x − 1)²', '3(3x − 1)²', '(3x − 1)²', '27(3x − 1)²'], 0, '[(3x−1)³]′ = 3(3x−1)² × 3 = 9(3x−1)².', $D);
        $this->quiz($L, 'Đạo hàm của y = x/(x + 1) tại x = 1 bằng?', ['1/4', '1/2', '1', '0'], 0, 'y′ = 1/(x+1)², tại x = 1 bằng 1/4.', $D);
        $this->matching($L, 'Nối mỗi hàm số với đạo hàm (quy tắc tổng – hiệu).', [['y = x³ + x', 'y′ = 3x² + 1'], ['y = 2x² − 5x', 'y′ = 4x − 5'], ['y = x⁴ − x²', 'y′ = 4x³ − 2x'], ['y = 7 − 3x', 'y′ = −3']], 'Đạo hàm của tổng bằng tổng các đạo hàm.', $D);
        $this->matching($L, 'Nối mỗi hàm số với đạo hàm (quy tắc tích).', [['y = x·sin x', 'y′ = sin x + x·cos x'], ['y = x²·eˣ', 'y′ = eˣ(2x + x²)'], ['y = 2x·ln x', 'y′ = 2ln x + 2'], ['y = x·cos x', 'y′ = cos x − x·sin x']], 'Áp dụng (uv)′ = u′v + uv′.', $D);
        $this->matching($L, 'Nối mỗi hàm hợp với đạo hàm của nó.', [['y = (2x + 1)³', 'y′ = 6(2x + 1)²'], ['y = sin(3x)', 'y′ = 3cos(3x)'], ['y = e^{2x}', 'y′ = 2e^{2x}'], ['y = √(x² + 1)', 'y′ = x/√(x² + 1)']], 'Nhân thêm đạo hàm của hàm trong.', $D);
        $this->matching($L, 'Nối mỗi quy tắc với công thức.', [['Đạo hàm tổng', '(u+v)′ = u′ + v′'], ['Đạo hàm tích', '(uv)′ = u′v + uv′'], ['Đạo hàm thương', '(u/v)′ = (u′v − uv′)/v²'], ['Hàm hợp', '[f(u)]′ = f′(u)·u′']], 'Bốn quy tắc đạo hàm quan trọng.', $D);
        $this->matching($L, 'Nối mỗi hàm số với đạo hàm tại điểm.', [['y = x² + 1 tại x = 2', 'y′ = 4'], ['y = x³ tại x = −1', 'y′ = 3'], ['y = 1/x tại x = 2', 'y′ = −1/4'], ['y = √x tại x = 4', 'y′ = 1/4']], 'Tính y′ rồi thay giá trị x.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm theo "QUY TẮC" cần dùng.', [['y = x² + 3x', 'TỔNG – HIỆU'], ['y = x·eˣ', 'TÍCH'], ['y = sin x / x', 'THƯƠNG'], ['y = (x² + 1)³', 'HÀM HỢP']], 'Nhận dạng để chọn quy tắc đúng.', $D);
        $this->sortQ($L, 'Kéo mỗi đẳng thức đạo hàm vào nhóm "ĐÚNG" hoặc "SAI".', [['(x²·x³)′ = 5x⁴', 'ĐÚNG'], ['(sin 2x)′ = 2cos 2x', 'ĐÚNG'], ['(x·x)′ = 1', 'SAI'], ['[(x+1)²]′ = x + 1', 'SAI']], '(x·x)′ = (x²)′ = 2x; [(x+1)²]′ = 2(x+1).', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "HÀM HỢP" hoặc "KHÔNG".', [['y = sin(2x)', 'HÀM HỢP'], ['y = (x³ + 1)²', 'HÀM HỢP'], ['y = x² + 2x', 'KHÔNG'], ['y = eˣ + x', 'KHÔNG']], 'Hàm hợp là hàm của hàm.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "ĐẠO HÀM CHỨA eˣ" hoặc "KHÔNG".', [['y = x·eˣ', 'ĐẠO HÀM CHỨA eˣ'], ['y = e^{3x}', 'ĐẠO HÀM CHỨA eˣ'], ['y = x² + 1', 'KHÔNG'], ['y = sin x', 'KHÔNG']], '(eˣ)′ = eˣ nên đạo hàm vẫn chứa eˣ.', $D);
        $this->sortQ($L, 'Kéo mỗi bước tính [(3x+1)²]′ vào nhóm "ĐÚNG THỨ TỰ".', [['Viết dạng hàm hợp', 'BƯỚC 1'], ['Đạo hàm ngoài: 2(3x+1)', 'BƯỚC 2'], ['Nhân đạo hàm trong: × 3', 'BƯỚC 3'], ['Kết quả 6(3x+1)', 'BƯỚC 4']], 'Hàm hợp: đạo hàm ngoài nhân đạo hàm trong.', $D);
        $this->fill($L, 'Quy tắc đạo hàm của hiệu: (u − v)′ = u′ − ___.', [[0, 'v′']], 'Đạo hàm của hiệu bằng hiệu các đạo hàm.', $D);
        $this->fill($L, 'Đạo hàm của y = (2x − 1)² là y′ = 4(2x − ___).', [[0, '1']], '[(2x−1)²]′ = 2(2x−1)×2 = 4(2x−1).', $D);
        $this->fill($L, 'Nếu y = x³·ln x thì y′ = x²(3ln x + ___).', [[0, '1']], '(uv)′ = 3x²ln x + x³×(1/x) = x²(3ln x + 1).', $D);
        $this->fill($L, 'Đạo hàm của y = cos(5x) là y′ = −5___(5x).', [[0, 'sin']], '[cos u]′ = −u′·sin u.', $D);
        $this->fill($L, 'Quy tắc hàm hợp: [f(u(x))]′ = f′(u) × ___.', [[0, 'u′']], 'Nhân thêm đạo hàm của hàm trong.', $D);
    }

    // 65. toan-thpt-lop-12-lop-12-3 — Tính đơn điệu và cực trị của hàm số (lớp 12, khó)
    private function seedTo35(): void {
        $L = 'toan-thpt-lop-12-lop-12-3'; $D = 'kho';
        $this->quiz($L, 'Cho y′ = 3x − 6. Hàm số đồng biến trên khoảng nào?', ['(2; +∞)', '(−∞; 2)', 'ℝ', '(−∞; 0)'], 0, 'y′ > 0 ⇔ 3x − 6 > 0 ⇔ x > 2.', $D);
        $this->quiz($L, 'Hàm số y = x³ − 3x² đạt cực tiểu tại điểm nào?', ['x = 2', 'x = 0', 'x = 1', 'x = 3'], 0, 'y′ = 3x² − 6x = 3x(x − 2); đổi dấu − sang + tại x = 2.', $D);
        $this->quiz($L, 'Hàm số y = −x³ + 3x đồng biến trên khoảng nào?', ['(−1; 1)', '(−∞; −1)', '(1; +∞)', 'ℝ'], 0, 'y′ = −3x² + 3 > 0 ⇔ x² < 1 ⇔ −1 < x < 1.', $D);
        $this->quiz($L, 'Giá trị cực đại của hàm số y = −x² + 2x + 3 bằng?', ['4', '3', '5', '1'], 0, 'Đỉnh x = 1, y = −1 + 2 + 3 = 4.', $D);
        $this->quiz($L, 'Hàm số y = x³ đồng biến hay nghịch biến trên ℝ?', ['Đồng biến', 'Nghịch biến', 'Không đơn điệu', 'Hằng'], 0, 'y′ = 3x² ≥ 0 mọi x nên đồng biến trên ℝ.', $D);
        $this->matching($L, 'Nối mỗi hàm số với khoảng đồng biến.', [['y = x²', '(0; +∞)'], ['y = −x²', '(−∞; 0)'], ['y = x³', 'ℝ'], ['y = 2x + 1', 'ℝ']], 'Xét dấu y′ để tìm khoảng đồng biến.', $D);
        $this->matching($L, 'Nối mỗi hàm số với số điểm cực trị.', [['y = x³', '0 điểm'], ['y = x²', '1 điểm'], ['y = x³ − 3x', '2 điểm'], ['y = 2x + 5', '0 điểm']], 'Điểm cực trị là nơi y′ đổi dấu.', $D);
        $this->matching($L, 'Nối mỗi dấu của y′ với kết luận.', [['y′ > 0', 'Đồng biến'], ['y′ < 0', 'Nghịch biến'], ['y′ = 0', 'Điểm tới hạn'], ['y′ đổi dấu + sang −', 'Cực đại']], 'Dấu đạo hàm quyết định tính đơn điệu.', $D);
        $this->matching($L, 'Nối mỗi hàm số với giá trị cực tiểu.', [['y = x² − 4x + 7', '3'], ['y = x² + 2x + 5', '4'], ['y = 2x² − 8x + 9', '1'], ['y = x² − 6x + 11', '2']], 'Cực tiểu tại đỉnh của parabol mở lên.', $D);
        $this->matching($L, 'Nối mỗi điểm tới hạn với loại của nó.', [['y′ đổi + sang −', 'Cực đại'], ['y′ đổi − sang +', 'Cực tiểu'], ['y′ không đổi dấu', 'Không cực trị'], ['y′ = 0 tại x = 0 của y = x³', 'Không cực trị']], 'Phải đổi dấu mới là cực trị.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "ĐỒNG BIẾN TRÊN ℝ" hoặc "KHÔNG".', [['y = x³', 'ĐỒNG BIẾN TRÊN ℝ'], ['y = 3x + 2', 'ĐỒNG BIẾN TRÊN ℝ'], ['y = −x³', 'KHÔNG'], ['y = x²', 'KHÔNG']], 'y′ ≥ 0 mọi x và chỉ bằng 0 tại hữu hạn điểm.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm theo loại cực trị.', [['y = x²', 'CỰC TIỂU'], ['y = −x²', 'CỰC ĐẠI'], ['y = x³ − 3x', 'CẢ HAI'], ['y = x³', 'KHÔNG CÓ']], 'Parabol mở lên có cực tiểu; mở xuống có cực đại.', $D);
        $this->sortQ($L, 'Kéo mỗi điểm tới hạn vào nhóm "CỰC ĐẠI" hoặc "CỰC TIỂU".', [['y′: + → −', 'CỰC ĐẠI'], ['Đỉnh parabol mở xuống', 'CỰC ĐẠI'], ['y′: − → +', 'CỰC TIỂU'], ['Đỉnh parabol mở lên', 'CỰC TIỂU']], 'Đổi dấu + sang − là cực đại.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm có "y′(0) DƯƠNG" hoặc "ÂM".', [['y = x³ + x', 'y′(0) DƯƠNG'], ['y = 2x + x²', 'y′(0) DƯƠNG'], ['y = −x³ + x²', 'y′(0) ÂM'], ['y = x² − 3x', 'y′(0) ÂM']], 'y′ = 3x²+1 tại 0 bằng 1 > 0.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['y′ = 0 là điều kiện cần của cực trị', 'ĐÚNG'], ['Cực trị luôn tại điểm y′ = 0', 'SAI'], ['Hàm bậc ba có tối đa 2 cực trị', 'ĐÚNG'], ['y′ > 0 thì đồng biến', 'ĐÚNG']], 'Cực trị có thể tại điểm y′ không xác định.', $D);
        $this->fill($L, 'Hàm số nghịch biến trên khoảng (a; b) khi y′ ___ 0 trên khoảng đó.', [[0, '<']], 'y′ < 0 thì hàm số nghịch biến.', $D);
        $this->fill($L, 'Điểm mà y′ đổi dấu từ dương sang âm là điểm cực ___.', [[0, 'đại']], 'Qua cực đại, hàm chuyển từ tăng sang giảm.', $D);
        $this->fill($L, 'Hàm số y = x³ − 6x² đạt cực tiểu tại x = ___.', [[0, '4']], 'y′ = 3x² − 12x = 3x(x − 4); cực tiểu tại x = 4.', $D);
        $this->fill($L, 'Giá trị cực đại của hàm số y = −x² + 4x − 3 là ___.', [[0, '1']], 'Đỉnh x = 2, y = −4 + 8 − 3 = 1.', $D);
        $this->fill($L, 'Số điểm cực trị của hàm số y = x⁴ − 2x² là ___.', [[0, '3']], 'y′ = 4x³ − 4x = 4x(x−1)(x+1): 3 điểm tới hạn đều đổi dấu.', $D);
    }

    // 66. toan-thpt-lop-12-lop-12-4 — Giá trị lớn nhất – giá trị nhỏ nhất của hàm số (lớp 12, khó)
    private function seedTo36(): void {
        $L = 'toan-thpt-lop-12-lop-12-4'; $D = 'kho';
        $this->quiz($L, 'GTLN của y = −x² + 2x + 8 trên ℝ bằng?', ['9', '8', '10', '7'], 0, 'Đỉnh x = 1, y = −1 + 2 + 8 = 9.', $D);
        $this->quiz($L, 'GTNN của y = x² + 4x + 7 trên ℝ bằng?', ['3', '7', '4', '11'], 0, 'Đỉnh x = −2, y = 4 − 8 + 7 = 3.', $D);
        $this->quiz($L, 'GTLN của y = cos x trên ℝ bằng?', ['1', '0', '−1', '2'], 0, 'cos x ≤ 1 mọi x.', $D);
        $this->quiz($L, 'GTNN của y = |x − 3| trên ℝ bằng?', ['0', '3', '−3', '1'], 0, '|x − 3| ≥ 0, bằng 0 tại x = 3.', $D);
        $this->quiz($L, 'GTLN của y = −2x + 5 trên đoạn [0; 2] bằng?', ['5', '1', '3', '9'], 0, 'Hàm nghịch biến nên max tại x = 0: y = 5.', $D);
        $this->matching($L, 'Nối mỗi hàm số (trên ℝ) với GTLN của nó.', [['y = −x² + 4', '4'], ['y = 5 − 2x²', '5'], ['y = −(x − 1)² + 2', '2'], ['y = sin x', '1']], 'GTLN tại đỉnh hoặc biên.', $D);
        $this->matching($L, 'Nối mỗi hàm số (trên ℝ) với GTNN của nó.', [['y = x² + 1', '1'], ['y = 2x² − 3', '−3'], ['y = (x + 2)²', '0'], ['y = |x| + 4', '4']], 'GTNN tại điểm thấp nhất.', $D);
        $this->matching($L, 'Nối mỗi ký hiệu với ý nghĩa.', [['max', 'Giá trị lớn nhất'], ['min', 'Giá trị nhỏ nhất'], ['GTLN', 'Giá trị lớn nhất'], ['GTNN', 'Giá trị nhỏ nhất']], 'Ký hiệu GTLN, GTNN.', $D);
        $this->matching($L, 'Nối mỗi hàm số trên đoạn với điểm đạt GTLN.', [['y = x² trên [0; 2]', 'x = 2'], ['y = −x² trên [−1; 1]', 'x = 0'], ['y = 2x + 1 trên [0; 3]', 'x = 3'], ['y = −x + 4 trên [1; 2]', 'x = 1']], 'So sánh giá trị tại điểm tới hạn và đầu mút.', $D);
        $this->matching($L, 'Nối mỗi bài toán thực tế với hàm số.', [['Diện tích lớn nhất', 'y = x(10 − x)'], ['Tổng nhỏ nhất', 'y = x + 1/x'], ['Doanh thu lớn nhất', 'y = −2x² + 100x'], ['Chi phí nhỏ nhất', 'y = x² − 20x + 500']], 'GTLN, GTNN giải bài toán tối ưu.', $D);
        $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm "GTLN TẠI ĐIỂM TRONG" hoặc "TẠI ĐẦU MÚT".', [['Parabol mở xuống trên ℝ', 'GTLN TẠI ĐIỂM TRONG'], ['Hàm đồng biến trên [a; b]', 'TẠI ĐẦU MÚT'], ['y = x² trên [1; 3]', 'TẠI ĐẦU MÚT'], ['y = −x² + 4x trên [0; 4]', 'GTLN TẠI ĐIỂM TRONG']], 'Đỉnh trong đoạn thì GTLN tại đỉnh.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "CÓ GTLN DƯƠNG" hoặc "KHÁC".', [['y = −x² + 9', 'CÓ GTLN DƯƠNG'], ['y = 4 − x²', 'CÓ GTLN DƯƠNG'], ['y = −x² − 1', 'KHÁC'], ['y = x²', 'KHÁC']], 'GTLN của −x²−1 là −1 < 0.', $D);
        $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm "CÓ CẢ GTLN VÀ GTNN" hoặc "KHÔNG".', [['Hàm liên tục trên [a; b]', 'CÓ CẢ GTLN VÀ GTNN'], ['y = x² trên [0; 1]', 'CÓ CẢ GTLN VÀ GTNN'], ['y = 1/x trên (0; 1)', 'KHÔNG'], ['y = x trên (0; 1)', 'KHÔNG']], 'Khoảng mở có thể không đạt biên.', $D);
        $this->sortQ($L, 'Kéo mỗi hàm số vào nhóm "GTNN BẰNG 0" hoặc "KHÁC 0".', [['y = x²', 'GTNN BẰNG 0'], ['y = (x − 5)²', 'GTNN BẰNG 0'], ['y = x² + 2', 'KHÁC 0'], ['y = |x − 1| + 3', 'KHÁC 0']], 'Bình phương, trị tuyệt đối ≥ 0.', $D);
        $this->sortQ($L, 'Kéo mỗi khẳng định vào nhóm "ĐÚNG" hoặc "SAI".', [['Hàm liên tục trên đoạn kín có GTLN, GTNN', 'ĐÚNG'], ['GTLN luôn tại điểm tới hạn', 'SAI'], ['Cần so sánh cả hai đầu mút', 'ĐÚNG'], ['Parabol mở lên có GTNN', 'ĐÚNG']], 'GTLN có thể tại đầu mút.', $D);
        $this->fill($L, 'GTNN của hàm số y = x² + 2x + 5 trên ℝ bằng ___.', [[0, '4']], 'Đỉnh x = −1, y = 1 − 2 + 5 = 4.', $D);
        $this->fill($L, 'Trên đoạn [a; b], để tìm GTNN ta so sánh giá trị tại điểm tới hạn và hai đầu ___.', [[0, 'mút']], 'Quy tắc tìm GTLN, GTNN trên đoạn.', $D);
        $this->fill($L, 'GTLN của hàm số y = 10 − (x − 3)² trên ℝ bằng ___.', [[0, '10']], '(x−3)² ≥ 0 nên y ≤ 10, bằng 10 tại x = 3.', $D);
        $this->fill($L, 'Với x > 0, theo Cô-si, x + 4/x ≥ ___.', [[0, '4']], 'x + 4/x ≥ 2√(x·4/x) = 4.', $D);
        $this->fill($L, 'Hàm số y = −3x² + 12x − 7 đạt GTLN tại x = ___.', [[0, '2']], 'Đỉnh x = −b/2a = −12/−6 = 2.', $D);
    }
}
