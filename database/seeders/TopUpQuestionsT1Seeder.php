<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * VÁ câu hỏi còn thiếu đợt cuối — nhóm T1 (Khoa học + Địa lý), 80 combo bài×kiểu, 180 câu.
 * Mỗi combo viết đúng số câu còn thiếu (need) của đúng kiểu chơi.
 * Nội dung tiếng Việt tự viết 100%, bám topic + khối lớp + độ khó, prompt không trùng.
 * Idempotent: giới hạn 8 câu/kiểu/bài + kiểm tra prompt trùng.
 */
class TopUpQuestionsT1Seeder extends Seeder
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
        $this->seedKhHeXuong();
        $this->seedKhHeTieuHoa();
        $this->seedKhTrangThaiChat();
        $this->seedKhNuoc();
        $this->seedKhNguonNangLuong();
        $this->seedKhDien();
        $this->seedDlThuDoChauA();
        $this->seedDlThuDoTheGioi();
        $this->seedDlDiaHinhSongNgoi();
        $this->seedDlKhiHau();
        $this->seedKhCoTheNguoiLop61();
        $this->seedKhCoTheNguoiLop62();
        $this->seedKhCoTheNguoiLop71();
        $this->seedKhCoTheNguoiLop72();
        $this->seedKhChatQuanhTaLop71();
        $this->seedKhChatQuanhTaLop72();
        $this->seedKhChatQuanhTaLop81();
        $this->seedKhChatQuanhTaLop82();
        $this->seedKhNangLuongLop81();
        $this->seedKhNangLuongLop82();
        $this->seedKhNangLuongLop91();
        $this->seedKhNangLuongLop92();
        $this->seedDlChauLucDaiDuongLop61();
        $this->seedDlChauLucDaiDuongLop62();
        $this->seedDlChauLucDaiDuongLop71();
        $this->seedDlChauLucDaiDuongLop72();
        $this->seedDlThuDoCacNuocLop71();
        $this->seedDlThuDoCacNuocLop72();
        $this->seedDlThuDoCacNuocLop81();
        $this->seedDlThuDoCacNuocLop82();
        $this->seedDlDiaHinhVietNamLop81();
        $this->seedDlDiaHinhVietNamLop82();
        $this->seedDlDiaHinhVietNamLop91();
        $this->seedDlDiaHinhVietNamLop92();
        $this->seedKhoaHocThpt10Lop101();
        $this->seedKhoaHocThpt10Lop102();
        $this->seedKhoaHocThpt10Lop103();
        $this->seedKhoaHocThpt10Lop104();
        $this->seedKhoaHocThpt11Lop111();
        $this->seedKhoaHocThpt11Lop112();
        $this->seedKhoaHocThpt11Lop113();
        $this->seedKhoaHocThpt11Lop114();
        $this->seedKhoaHocThpt12Lop121();
        $this->seedKhoaHocThpt12Lop122();
        $this->seedKhoaHocThpt12Lop123();
        $this->seedKhoaHocThpt12Lop124();
        $this->seedDiaLyThpt10Lop101();
        $this->seedDiaLyThpt10Lop102();
        $this->seedDiaLyThpt10Lop103();
        $this->seedDiaLyThpt10Lop104();
        $this->seedDiaLyThpt11Lop111();
        $this->seedDiaLyThpt11Lop112();
        $this->seedDiaLyThpt11Lop113();
        $this->seedDiaLyThpt11Lop114();
        $this->seedDiaLyThpt12Lop121();
        $this->seedDiaLyThpt12Lop122();
        $this->seedDiaLyThpt12Lop123();
        $this->seedDiaLyThpt12Lop124();
    }

    private function seedKhHeXuong(): void
    {
        $s = 'kh-he-xuong';
        $d = 'de';

        $this->matching($s, 'Ghép mỗi xương dưới đây với đúng số lượng của nó trong cơ thể.', [['Xương sọ', '8 xương hộp sọ'], ['Xương sườn', '12 đôi'], ['Xương đốt sống', '33 đốt'], ['Xương tai giữa', '3 xương nhỏ mỗi tai']], 'Hộp sọ có 8 xương, mỗi bên sườn 12 chiếc, cột sống 33 đốt và mỗi tai giữa có 3 xương nhỏ nhất cơ thể.', $d);
        $this->matching($s, 'Ghép mỗi bộ phận của xương dài với tên gọi của nó.', [['Đầu xương', 'Chứa sụn giúp xương dài ra'], ['Thân xương', 'Có ống tủy chứa tủy xương'], ['Khớp xương', 'Nối hai xương, giúp cử động'], ['Tủy xương', 'Nơi sản sinh tế bào máu']], 'Xương dài gồm đầu xương, thân xương; sụn đầu xương giúp xương dài ra, tủy xương sản sinh tế bào máu.', $d);
        $this->matching($s, 'Ghép mỗi bệnh về xương với nguyên nhân gây ra nó.', [['Loãng xương', 'Thiếu canxi, tuổi cao'], ['Còi xương', 'Thiếu vitamin D, ít tắm nắng'], ['Gãy xương', 'Va chạm, ngã mạnh'], ['Viêm khớp', 'Nhiễm trùng hoặc thoái hóa sụn khớp']], 'Loãng xương do thiếu canxi, còi xương do thiếu vitamin D, gãy xương do va chạm và viêm khớp do thoái hóa hoặc nhiễm trùng.', $d);

        $this->sortQ($s, 'Xếp mỗi vật vào nhóm: Xương của người / Không phải xương.', [['Xương đùi', 'Xương của người'], ['Xương bánh chè', 'Xương của người'], ['Xương đòn', 'Xương của người'], ['Gân tay', 'Không phải xương'], ['Cơ bắp', 'Không phải xương'], ['Sụn tai', 'Không phải xương']], 'Xương đùi, xương bánh chè, xương đòn là xương thật; gân, cơ và sụn tai không phải xương.', $d);
        $this->sortQ($s, 'Xếp mỗi hoạt động vào nhóm: Rèn luyện xương / Gây hại cho xương.', [['Chạy bộ đều đặn', 'Rèn luyện xương'], ['Ngồi học đúng tư thế', 'Rèn luyện xương'], ['Bê vật quá nặng', 'Gây hại cho xương'], ['Ngồi cong lưng suốt ngày', 'Gây hại cho xương']], 'Vận động vừa sức và ngồi đúng tư thế giúp xương chắc khỏe, còn bê vật nặng và ngồi sai tư thế hại cột sống.', $d);
        $this->sortQ($s, 'Xếp mỗi xương vào nhóm: Ở tay / Ở chân.', [['Xương cẳng tay', 'Ở tay'], ['Xương vai', 'Ở tay'], ['Xương đùi', 'Ở chân'], ['Xương bánh chè', 'Ở chân']], 'Xương cẳng tay và xương vai thuộc tay, xương đùi và xương bánh chè thuộc chân.', $d);

        $this->fill($s, 'Trẻ sơ sinh có nhiều hơn 300 chiếc xương, khi lớn lên nhiều xương liền lại còn khoảng ___ chiếc.', [[0, '206']], 'Trẻ sơ sinh có trên 300 xương, nhiều xương liền lại với nhau nên người trưởng thành còn khoảng 206 xương.', $d);
        $this->fill($s, 'Xương có tính đàn hồi và rắn chắc là nhờ chất ___ và chất cốt giao trong xương.', [[0, 'canxi']], 'Muối canxi làm xương cứng chắc, chất cốt giao giúp xương có tính đàn hồi.', $d);
        $this->fill($s, 'Để phòng bệnh còi xương, trẻ em nên tắm ___ buổi sáng để cơ thể tổng hợp vitamin D.', [[0, 'nắng']], 'Tắm nắng buổi sáng giúp da tổng hợp vitamin D, phòng còi xương ở trẻ em.', $d);
    }

    private function seedKhHeTieuHoa(): void
    {
        $s = 'kh-he-tieu-hoa';
        $d = 'trung_binh';

        $this->quiz($s, 'Enzim amilaza trong nước bọt có tác dụng gì?', ['Phân giải tinh bột thành đường', 'Phân giải chất đạm', 'Hấp thụ nước', 'Tiêu hóa mỡ'], 0, 'Amilaza trong nước bọt phân giải một phần tinh bột thành đường ngay từ khoang miệng.', $d);
        $this->quiz($s, 'Mật có vai trò gì trong quá trình tiêu hóa?', ['Nhũ tương hóa mỡ thành giọt nhỏ', 'Phân giải chất đạm thành axit amin', 'Hấp thụ vitamin', 'Tiết ra axit dạ dày'], 0, 'Mật do gan tiết ra giúp nhũ tương hóa mỡ thành giọt nhỏ để dễ tiêu hóa.', $d);
        $this->quiz($s, 'Thức ăn được nghiền nhỏ chủ yếu nhờ bộ phận nào của ống tiêu hóa?', ['Răng và cơ thành dạ dày co bóp', 'Gan', 'Tuyến nước bọt', 'Ruột thừa'], 0, 'Răng nghiền thức ăn ở miệng, cơ thành dạ dày co bóp tiếp tục nghiền nhỏ thức ăn.', $d);

        $this->matching($s, 'Ghép mỗi loại thức ăn với cơ quan bắt đầu tiêu hóa nó.', [['Tinh bột', 'Khoang miệng'], ['Chất đạm', 'Dạ dày'], ['Mỡ', 'Ruột non'], ['Xenlulozơ', 'Không được tiêu hóa']], 'Tinh bột bắt đầu tiêu hóa ở miệng, đạm ở dạ dày, mỡ ở ruột non, xenlulozơ cơ thể không tiêu hóa được.', $d);
        $this->matching($s, 'Ghép mỗi bệnh đường tiêu hóa với cách phòng tránh.', [['Sâu răng', 'Đánh răng 2 lần mỗi ngày'], ['Tiêu chảy', 'Ăn chín, uống sôi'], ['Táo bón', 'Ăn nhiều rau, uống đủ nước'], ['Viêm dạ dày', 'Ăn đúng giờ, không bỏ bữa']], 'Vệ sinh răng miệng, ăn chín uống sôi, ăn nhiều chất xơ và ăn đúng giờ giúp phòng các bệnh tiêu hóa.', $d);
        $this->matching($s, 'Ghép mỗi bộ phận của răng với chức năng của nó.', [['Men răng', 'Lớp bảo vệ cứng nhất'], ['Ngà răng', 'Lớp dưới men răng'], ['Tủy răng', 'Chứa mạch máu và dây thần kinh'], ['Chân răng', 'Cắm vào xương hàm']], 'Men răng bảo vệ bên ngoài, ngà răng ở dưới men, tủy răng chứa mạch máu, dây thần kinh và chân răng cắm vào xương hàm.', $d);

        $this->sortQ($s, 'Xếp mỗi cơ quan vào nhóm: Ống tiêu hóa / Tuyến tiêu hóa.', [['Thực quản', 'Ống tiêu hóa'], ['Ruột già', 'Ống tiêu hóa'], ['Tuyến nước bọt', 'Tuyến tiêu hóa'], ['Tụy', 'Tuyến tiêu hóa']], 'Thực quản, ruột già là bộ phận của ống tiêu hóa; tuyến nước bọt và tụy là tuyến tiêu hóa.', $d);
        $this->sortQ($s, 'Xếp mỗi thực phẩm vào nhóm: Dễ tiêu hóa / Khó tiêu hóa.', [['Cháo loãng', 'Dễ tiêu hóa'], ['Rau luộc chín', 'Dễ tiêu hóa'], ['Đồ chiên rán nhiều mỡ', 'Khó tiêu hóa'], ['Thịt gân dai', 'Khó tiêu hóa']], 'Cháo loãng, rau luộc chín dễ tiêu hóa; đồ chiên nhiều mỡ và thịt gân dai khó tiêu hóa hơn.', $d);
        $this->sortQ($s, 'Xếp mỗi thói quen vào nhóm: Khi ăn / Khi không ăn.', [['Nhai kỹ thức ăn', 'Khi ăn'], ['Uống nước trong lúc ăn quá nhiều', 'Khi ăn'], ['Ăn đúng giờ', 'Khi không ăn'], ['Tập thể dục sau khi ăn no', 'Khi không ăn']], 'Nhai kỹ là thói quen khi ăn; ăn đúng giờ là thói quen sinh hoạt; nên tránh tập nặng ngay sau khi ăn.', $d);
    }


    private function seedKhTrangThaiChat(): void
    {
        $s = 'kh-trang-thai-chat';
        $d = 'de';

        $this->matching($s, 'Ghép mỗi hiện tượng quanh em với quá trình chuyển thể đúng.', [['Nước trong ly cạn dần', 'Bay hơi'], ['Kính đeo bị mờ khi ăn nóng', 'Ngưng tụ'], ['Viên đá trong nước ngọt tan ra', 'Nóng chảy'], ['Quần áo phơi khô dưới nắng', 'Bay hơi']], 'Nước cạn dần và quần áo khô là bay hơi, kính bị mờ là ngưng tụ, viên đá tan là nóng chảy.', $d);

        $this->sortQ($s, 'Xếp mỗi vật vào nhóm: Có thể tự chảy loang / Không tự chảy loang.', [['Nước', 'Có thể tự chảy loang'], ['Dầu ăn', 'Có thể tự chảy loang'], ['Cục đá', 'Không tự chảy loang'], ['Viên bi', 'Không tự chảy loang']], 'Nước và dầu ăn là chất lỏng nên tự chảy loang, cục đá và viên bi là chất rắn nên giữ nguyên hình dạng.', $d);
        $this->sortQ($s, 'Xếp mỗi hiện tượng vào nhóm: Vật nóng lên / Vật lạnh đi.', [['Nấu nước sôi', 'Vật nóng lên'], ['Nung nóng thanh sắt', 'Vật nóng lên'], ['Để nước vào tủ lạnh', 'Vật lạnh đi'], ['Phơi kem ngoài trời nắng', 'Vật nóng lên']], 'Nấu nước, nung sắt và kem ngoài nắng đều là vật nóng lên; để nước vào tủ lạnh là vật lạnh đi.', $d);
        $this->sortQ($s, 'Xếp mỗi quá trình vào nhóm: Từ lỏng sang khí / Từ khí sang lỏng.', [['Bay hơi', 'Từ lỏng sang khí'], ['Sôi', 'Từ lỏng sang khí'], ['Ngưng tụ', 'Từ khí sang lỏng'], ['Nước ao bốc hơi khi trời nắng', 'Từ lỏng sang khí']], 'Bay hơi, sôi và nước ao bốc hơi là chuyển từ lỏng sang khí; ngưng tụ là từ khí sang lỏng.', $d);

        $this->fill($s, 'Ở nhiệt độ thường, sắt là chất ___, nước là chất lỏng, không khí là chất khí.', [[0, 'rắn']], 'Ở nhiệt độ thường, sắt tồn tại ở thể rắn, nước ở thể lỏng và không khí ở thể khí.', $d);
        $this->fill($s, 'Khi đun nóng nước tới ___ độ C, nước bắt đầu sôi và chuyển thành hơi.', [[0, '100']], 'Nước sôi ở 100 độ C trong điều kiện thường và chuyển từ thể lỏng sang thể hơi.', $d);
        $this->fill($s, 'Hạt mưa trên cành cây vào mùa đông có thể đông thành đá gọi là sự đông ___.', [[0, 'đặc']], 'Nước từ thể lỏng chuyển thành thể rắn khi lạnh gọi là sự đông đặc.', $d);
    }


    private function seedKhNuoc(): void
    {
        $s = 'kh-nuoc';
        $d = 'de';

        $this->quiz($s, 'Hơi nước trong không khí khi gặp lạnh sẽ chuyển thành gì?', ['Giọt nước li ti', 'Băng khô', 'Khói bụi', 'Tuyết rơi'], 0, 'Hơi nước gặp lạnh ngưng tụ thành những giọt nước li ti tạo thành sương hoặc mây.', $d);
        $this->quiz($s, 'Nước mưa hình thành chủ yếu nhờ quá trình nào?', ['Bay hơi rồi ngưng tụ', 'Sôi', 'Đông đặc', 'Thăng hoa'], 0, 'Nước bay hơi lên cao, gặp lạnh ngưng tụ thành mây rồi rơi xuống thành mưa.', $d);
        $this->quiz($s, 'Vì sao cây cối cần nước mỗi ngày?', ['Để quang hợp và vận chuyển chất dinh dưỡng', 'Để đuổi sâu bọ', 'Để làm đất cứng lại', 'Để tạo bóng râm'], 0, 'Nước tham gia quang hợp và giúp cây vận chuyển chất dinh dưỡng khắp thân, lá, rễ.', $d);

        $this->matching($s, 'Ghép mỗi hiện tượng với quá trình của nước.', [['Sương đọng trên lá sáng sớm', 'Ngưng tụ'], ['Mưa', 'Nước rơi từ mây'], ['Nước sông ra biển', 'Chảy xuôi'], ['Mây trắng trên trời', 'Hơi nước ngưng tụ']], 'Sương và mây là hơi nước ngưng tụ, mưa là nước rơi từ mây, nước sông chảy ra biển.', $d);
        $this->matching($s, 'Ghép mỗi cách dùng nước với mục đích của nó.', [['Tưới cây', 'Giúp cây lớn lên'], ['Nấu ăn', 'Chế biến thực phẩm'], ['Giặt quần áo', 'Làm sạch'], ['Rửa tay trước khi ăn', 'Phòng bệnh']], 'Nước dùng để tưới cây, nấu ăn, giặt giũ và rửa tay phòng bệnh.', $d);
        $this->matching($s, 'Ghép mỗi phát hiện với giải thích về nước.', [['Nước đá nổi trên nước', 'Đá nhẹ hơn nước lỏng'], ['Nước có thể hòa tan muối', 'Là dung môi tốt'], ['Sương mù làm ướt áo', 'Hơi nước ngưng tụ'], ['Nước sôi có bọt khí', 'Hơi nước thoát ra']], 'Nước đá nhẹ hơn nên nổi, nước hòa tan được nhiều chất, sương mù là hơi nước ngưng tụ.', $d);

        $this->sortQ($s, 'Xếp mỗi việc vào nhóm: Giúp nước sạch / Làm nước bẩn.', [['Đậy nắp bể chứa nước', 'Giúp nước sạch'], ['Vứt rác vào ao hồ', 'Làm nước bẩn'], ['Xây nhà vệ sinh xa giếng', 'Giúp nước sạch'], ['Xả nước thải chưa xử lý ra sông', 'Làm nước bẩn']], 'Đậy nắp bể nước và giữ giếng xa nơi ô nhiễm giúp nước sạch; vứt rác và xả thải làm bẩn nước.', $d);
        $this->sortQ($s, 'Xếp mỗi chất vào nhóm: Tan trong nước / Không tan trong nước.', [['Muối ăn', 'Tan trong nước'], ['Đường', 'Tan trong nước'], ['Dầu ăn', 'Không tan trong nước'], ['Cát', 'Không tan trong nước']], 'Muối ăn và đường tan trong nước, dầu ăn và cát không tan.', $d);
        $this->sortQ($s, 'Xếp mỗi hiện tượng vào nhóm: Cần nước / Không cần nước.', [['Cây lúa trổ bông', 'Cần nước'], ['Cá sống trong ao', 'Cần nước'], ['Sách vở trên bàn', 'Không cần nước'], ['Quạt điện đang quay', 'Không cần nước']], 'Cây lúa và cá cần nước để sống; sách vở, quạt điện không cần nước.', $d);
    }


    private function seedKhNguonNangLuong(): void
    {
        $s = 'kh-nguon-nang-luong';
        $d = 'trung_binh';

        $this->matching($s, 'Ghép mỗi quá trình chuyển hóa năng lượng với ví dụ thực tế.', [['Quang năng thành hóa năng', 'Cây quang hợp'], ['Động năng thành điện năng', 'Nhà máy thủy điện'], ['Hóa năng thành nhiệt năng', 'Đốt củi nấu ăn'], ['Điện năng thành quang năng', 'Bóng đèn phát sáng']], 'Cây quang hợp biến quang năng thành hóa năng, thủy điện biến động năng thành điện năng, đốt củi biến hóa năng thành nhiệt năng.', $d);

        $this->sortQ($s, 'Xếp mỗi nguồn vào nhóm: Năng lượng sinh khối / Năng lượng hóa thạch.', [['Củi', 'Năng lượng sinh khối'], ['Rơm rạ', 'Năng lượng sinh khối'], ['Khí tự nhiên', 'Năng lượng hóa thạch'], ['Dầu mỏ', 'Năng lượng hóa thạch']], 'Củi, rơm rạ là năng lượng sinh khối; khí tự nhiên, dầu mỏ là năng lượng hóa thạch.', $d);
        $this->sortQ($s, 'Xếp mỗi thiết bị vào nhóm: Cần nhiên liệu đốt / Không cần nhiên liệu đốt.', [['Xe máy', 'Cần nhiên liệu đốt'], ['Bếp ga', 'Cần nhiên liệu đốt'], ['Pin mặt trời', 'Không cần nhiên liệu đốt'], ['Quạt bàn dùng điện', 'Không cần nhiên liệu đốt']], 'Xe máy, bếp ga đốt nhiên liệu; pin mặt trời và quạt bàn dùng điện không đốt nhiên liệu trực tiếp.', $d);
        $this->sortQ($s, 'Xếp mỗi việc làm vào nhóm: Giảm ô nhiễm không khí / Tăng ô nhiễm không khí.', [['Đi xe đạp đi học', 'Giảm ô nhiễm không khí'], ['Trồng thêm cây xanh', 'Giảm ô nhiễm không khí'], ['Đốt rơm rạ ngoài đồng', 'Tăng ô nhiễm không khí'], ['Đi xe máy cũ xả khói đen', 'Tăng ô nhiễm không khí']], 'Đi xe đạp, trồng cây giúp giảm ô nhiễm; đốt rơm rạ và xe xả khói đen làm tăng ô nhiễm không khí.', $d);

        $this->fill($s, 'Năng lượng từ mặt trời chiếu xuống được gọi là quang ___.', [[0, 'năng']], 'Quang năng là năng lượng từ ánh sáng mặt trời, được cây xanh dùng để quang hợp.', $d);
        $this->fill($s, 'Xe đạp chạy được là nhờ ___ năng của người đạp chuyển thành động năng của xe.', [[0, 'cơ']], 'Người đạp xe chuyển cơ năng của cơ thể thành động năng giúp xe lăn bánh.', $d);
        $this->fill($s, 'Khi tắt bớt đèn không cần thiết, gia đình em đang thực hành ___ kiệm điện.', [[0, 'tiết']], 'Tắt đèn và thiết bị khi không dùng là cách tiết kiệm điện đơn giản, hiệu quả.', $d);
    }


    private function seedKhDien(): void
    {
        $s = 'kh-dien';
        $d = 'trung_binh';

        $this->quiz($s, 'Để đo hiệu điện thế giữa hai đầu bóng đèn, người ta dùng dụng cụ nào?', ['Vôn kế', 'Ampe kế', 'Nhiệt kế', 'Cân'], 0, 'Vôn kế dùng để đo hiệu điện thế, ampe kế dùng để đo cường độ dòng điện.', $d);
        $this->quiz($s, 'Trong mạch điện kín, chiều dòng điện quy ước như thế nào?', ['Từ cực dương sang cực âm của nguồn điện', 'Từ cực âm sang cực dương của nguồn điện', 'Chạy vòng tròn không định hướng', 'Từ dây dẫn sang bóng đèn'], 0, 'Chiều dòng điện quy ước là chiều từ cực dương qua dây dẫn, dụng cụ điện tới cực âm của nguồn.', $d);
        $this->quiz($s, 'Cầu chì trong mạch điện gia đình có tác dụng gì?', ['Tự ngắt mạch khi dòng điện quá lớn', 'Làm tăng điện áp', 'Tiết kiệm điện năng', 'Phát sáng thay bóng đèn'], 0, 'Cầu chì tự đứt khi dòng điện quá lớn, bảo vệ mạch điện và thiết bị khỏi cháy nổ.', $d);

        $this->matching($s, 'Ghép mỗi linh kiện với kí hiệu tác dụng trong mạch điện.', [['Dây dẫn', 'Nối các bộ phận với nhau'], ['Công tắc', 'Đóng hoặc ngắt mạch điện'], ['Bóng đèn', 'Biến điện năng thành quang năng'], ['Pin', 'Cung cấp điện cho mạch']], 'Dây dẫn nối mạch, công tắc đóng ngắt, bóng đèn biến điện thành ánh sáng, pin cung cấp điện.', $d);
        $this->matching($s, 'Ghép mỗi vật liệu cách điện với ứng dụng của nó.', [['Nhựa', 'Vỏ dây điện'], ['Cao su', 'Găng tay cách điện'], ['Thủy tinh', 'Vỏ bóng đèn'], ['Gốm sứ', 'Cách điện trên cột điện']], 'Nhựa bọc dây điện, cao su làm găng tay, gốm sứ cách điện trên cột điện đều là vật liệu cách điện.', $d);
        $this->matching($s, 'Ghép mỗi trường hợp chập điện với nguyên nhân của nó.', [['Dây điện bị chuột cắn', 'Lớp cách điện hỏng'], ['Cắm quá nhiều thiết bị vào một ổ', 'Quá tải mạch điện'], ['Dây điện ướt nước', 'Nước dẫn điện'], ['Dùng dây điện quá cũ', 'Vỏ cách điện nứt vỡ']], 'Dây bị cắn, quá tải, dây ướt nước hoặc dây cũ nứt vỏ đều có thể gây chập điện.', $d);

        $this->sortQ($s, 'Xếp mỗi tình huống vào nhóm: Mạch điện kín / Mạch điện hở.', [['Công tắc đang bật', 'Mạch điện kín'], ['Bóng đèn đang sáng', 'Mạch điện kín'], ['Công tắc đang tắt', 'Mạch điện hở'], ['Dây điện bị đứt', 'Mạch điện hở']], 'Công tắc bật, bóng đèn sáng là mạch kín có dòng điện; công tắc tắt, dây đứt là mạch hở.', $d);
        $this->sortQ($s, 'Xếp mỗi vật vào nhóm: Nên chạm vào / Không nên chạm vào khi tay ướt.', [['Cốc nhựa khô', 'Nên chạm vào'], ['Tay nắm cửa gỗ', 'Nên chạm vào'], ['Ổ cắm điện', 'Không nên chạm vào khi tay ướt'], ['Công tắc điện', 'Không nên chạm vào khi tay ướt']], 'Tay ướt dễ dẫn điện nên tuyệt đối không chạm vào ổ cắm, công tắc điện.', $d);
        $this->sortQ($s, 'Xếp mỗi nguồn điện vào nhóm: An toàn cho người / Nguy hiểm.', [['Pin tiểu 1,5 V', 'An toàn cho người'], ['Ắc quy xe đạp điện', 'An toàn cho người'], ['Điện lưới 220 V', 'Nguy hiểm'], ['Dây điện cao thế', 'Nguy hiểm']], 'Pin tiểu và ắc quy nhỏ có điện áp thấp nên an toàn; điện lưới 220 V và dây cao thế rất nguy hiểm.', $d);
    }


    private function seedDlThuDoChauA(): void
    {
        $s = 'dl-thu-do-chau-a';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm ĐÔNG Á hoặc NAM Á – TÂY Á.', [['Tokyo', 'ĐÔNG Á'], ['Seoul', 'ĐÔNG Á'], ['New Delhi', 'NAM Á – TÂY Á'], ['Riyadh', 'NAM Á – TÂY Á']], 'Tokyo, Seoul thuộc Đông Á; New Delhi thuộc Nam Á, Riyadh thuộc Tây Á.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phố vào nhóm ĐÃ TỪNG DỜI ĐÔ hoặc CHƯA DỜI ĐÔ.', [['Naypyidaw', 'ĐÃ TỪNG DỜI ĐÔ'], ['Jakarta', 'ĐÃ TỪNG DỜI ĐÔ'], ['Bangkok', 'CHƯA DỜI ĐÔ'], ['Hà Nội', 'CHƯA DỜI ĐÔ']], 'Myanmar dời đô từ Yangon sang Naypyidaw, Indonesia dời đô khỏi Jakarta; Bangkok và Hà Nội giữ vai trò thủ đô lâu dài.', $d);
    }

    private function seedDlThuDoTheGioi(): void
    {
        $s = 'dl-thu-do-the-gioi';
        $d = 'trung_binh';

        $this->matching($s, 'Nối mỗi thủ đô thường bị nhầm lẫn với quốc gia đúng của nó.', [['Canberra', 'Úc'], ['Ankara', 'Thổ Nhĩ Kỳ'], ['Ottawa', 'Canada'], ['Brasilia', 'Brazil']], 'Thủ đô Úc là Canberra chứ không phải Sydney, Thổ Nhĩ Kỳ là Ankara chứ không phải Istanbul, Canada là Ottawa, Brazil là Brasilia.', $d);

        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm Ở BÁN CẦU BẮC hoặc Ở BÁN CẦU NAM.', [['Hà Nội', 'Ở BÁN CẦU BẮC'], ['Cairo', 'Ở BÁN CẦU BẮC'], ['Canberra', 'Ở BÁN CẦU NAM'], ['Brasilia', 'Ở BÁN CẦU NAM']], 'Hà Nội, Cairo ở bán cầu Bắc; Canberra, Brasilia ở bán cầu Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm TIẾNG ANH LÀ NGÔN NGỮ CHÍNH hoặc KHÔNG.', [['London', 'TIẾNG ANH LÀ NGÔN NGỮ CHÍNH'], ['Canberra', 'TIẾNG ANH LÀ NGÔN NGỮ CHÍNH'], ['Paris', 'KHÔNG'], ['Tokyo', 'KHÔNG']], 'London, Canberra dùng tiếng Anh là ngôn ngữ chính; Paris dùng tiếng Pháp, Tokyo dùng tiếng Nhật.', $d);
    }

    private function seedDlDiaHinhSongNgoi(): void
    {
        $s = 'dl-dia-hinh-song-ngoi';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm Ở MIỀN TRUNG hoặc KHÔNG Ở MIỀN TRUNG.', [['Đèo Hải Vân', 'Ở MIỀN TRUNG'], ['Sông Thu Bồn', 'Ở MIỀN TRUNG'], ['Đồng bằng sông Hồng', 'KHÔNG Ở MIỀN TRUNG'], ['Sông Cửu Long', 'KHÔNG Ở MIỀN TRUNG']], 'Đèo Hải Vân, sông Thu Bồn thuộc miền Trung; đồng bằng sông Hồng thuộc miền Bắc, sông Cửu Long thuộc miền Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi con sông vào nhóm CHẢY QUA NHIỀU NƯỚC hoặc CHỈ Ở VIỆT NAM.', [['Sông Mê Kông', 'CHẢY QUA NHIỀU NƯỚC'], ['Sông Hồng', 'CHẢY QUA NHIỀU NƯỚC'], ['Sông Hương', 'CHỈ Ở VIỆT NAM'], ['Sông Thu Bồn', 'CHỈ Ở VIỆT NAM']], 'Sông Mê Kông, sông Hồng chảy qua nhiều nước; sông Hương, sông Thu Bồn chỉ chảy trong Việt Nam.', $d);
    }

    private function seedDlKhiHau(): void
    {
        $s = 'dl-khi-hau';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi đặc điểm thời tiết vào nhóm MÙA HÈ hoặc MÙA ĐÔNG (ở miền Bắc).', [['Nóng ẩm, mưa nhiều', 'MÙA HÈ'], ['Nắng nóng gay gắt', 'MÙA HÈ'], ['Rét đậm, rét hại', 'MÙA ĐÔNG'], ['Gió mùa Đông Bắc tràn về', 'MÙA ĐÔNG']], 'Miền Bắc mùa hè nóng ẩm mưa nhiều, mùa đông rét đậm và chịu gió mùa Đông Bắc.', $d);
    }


    private function seedKhCoTheNguoiLop61(): void
    {
        $s = 'kh-co-the-nguoi-lop-6-1';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm THUỘC HỆ XƯƠNG hoặc THUỘC HỆ CƠ.', [['Xương sườn', 'THUỘC HỆ XƯƠNG'], ['Xương đùi', 'THUỘC HỆ XƯƠNG'], ['Cơ cánh tay', 'THUỘC HỆ CƠ'], ['Cơ cẳng chân', 'THUỘC HỆ CƠ']], 'Xương sườn, xương đùi thuộc hệ xương; cơ cánh tay, cơ cẳng chân thuộc hệ cơ.', $d);
        $this->sortQ($s, 'Kéo mỗi xương vào nhóm Ở THÂN hoặc Ở CHI.', [['Xương sườn', 'Ở THÂN'], ['Xương cột sống', 'Ở THÂN'], ['Xương cẳng tay', 'Ở CHI'], ['Xương cẳng chân', 'Ở CHI']], 'Xương sườn, xương cột sống ở thân; xương cẳng tay, xương cẳng chân ở chi.', $d);
    }

    private function seedKhCoTheNguoiLop62(): void
    {
        $s = 'kh-co-the-nguoi-lop-6-2';
        $d = 'de';

        $this->matching($s, 'Ghép mỗi giai đoạn tiêu hóa với nơi nó diễn ra.', [['Nhai và nuốt thức ăn', 'Khoang miệng'], ['Nghiền và trộn dịch vị', 'Dạ dày'], ['Hấp thụ chất dinh dưỡng', 'Ruột non'], ['Hấp thụ nước và tạo phân', 'Ruột già']], 'Thức ăn được nhai ở miệng, nghiền ở dạ dày, hấp thụ dinh dưỡng ở ruột non và tạo phân ở ruột già.', $d);

        $this->sortQ($s, 'Kéo mỗi cơ quan vào nhóm ỐNG TIÊU HÓA hoặc TUYẾN TIÊU HÓA.', [['Thực quản', 'ỐNG TIÊU HÓA'], ['Ruột non', 'ỐNG TIÊU HÓA'], ['Gan', 'TUYẾN TIÊU HÓA'], ['Tuyến tụy', 'TUYẾN TIÊU HÓA']], 'Thực quản, ruột non là bộ phận của ống tiêu hóa; gan và tuyến tụy là tuyến tiêu hóa.', $d);
        $this->sortQ($s, 'Kéo mỗi thực phẩm vào nhóm GIÀU VITAMIN hoặc GIÀU CHẤT BÉO.', [['Quả cam', 'GIÀU VITAMIN'], ['Rau cải xanh', 'GIÀU VITAMIN'], ['Mỡ heo', 'GIÀU CHẤT BÉO'], ['Đồ chiên rán', 'GIÀU CHẤT BÉO']], 'Cam, rau xanh giàu vitamin; mỡ heo, đồ chiên giàu chất béo nên ăn hạn chế.', $d);
        $this->sortQ($s, 'Kéo mỗi thói quen vào nhóm ĂN UỐNG HỢP VỆ SINH hoặc KHÔNG HỢP VỆ SINH.', [['Rửa tay trước khi ăn', 'ĂN UỐNG HỢP VỆ SINH'], ['Rửa sạch rau quả trước khi ăn', 'ĂN UỐNG HỢP VỆ SINH'], ['Ăn thức ăn ôi thiu', 'KHÔNG HỢP VỆ SINH'], ['Uống nước lã chưa đun sôi', 'KHÔNG HỢP VỆ SINH']], 'Rửa tay, rửa rau quả là ăn uống hợp vệ sinh; ăn đồ ôi thiu, uống nước lã gây bệnh đường ruột.', $d);
    }

    private function seedKhCoTheNguoiLop71(): void
    {
        $s = 'kh-co-the-nguoi-lop-7-1';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm THUỘC TIM hoặc THUỘC PHỔI.', [['Tâm thất', 'THUỘC TIM'], ['Tâm nhĩ', 'THUỘC TIM'], ['Phế nang', 'THUỘC PHỔI'], ['Khí quản', 'THUỘC PHỔI']], 'Tâm thất, tâm nhĩ là các ngăn của tim; phế nang, khí quản là bộ phận của phổi.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm CẦN NHIỀU ÔXI hoặc CẦN ÍT ÔXI.', [['Chạy nhanh', 'CẦN NHIỀU ÔXI'], ['Bơi lội', 'CẦN NHIỀU ÔXI'], ['Ngồi đọc sách', 'CẦN ÍT ÔXI'], ['Ngủ', 'CẦN ÍT ÔXI']], 'Chạy, bơi tiêu tốn nhiều năng lượng nên cần nhiều ôxi; đọc sách, ngủ cần ít ôxi.', $d);
    }

    private function seedKhCoTheNguoiLop72(): void
    {
        $s = 'kh-co-the-nguoi-lop-7-2';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi cơ quan vào nhóm CỦA HỆ BÀI TIẾT hoặc CỦA HỆ KHÁC.', [['Thận', 'CỦA HỆ BÀI TIẾT'], ['Bóng đái', 'CỦA HỆ BÀI TIẾT'], ['Tim', 'CỦA HỆ KHÁC'], ['Phổi', 'CỦA HỆ KHÁC']], 'Thận, bóng đái thuộc hệ bài tiết nước tiểu; tim thuộc hệ tuần hoàn, phổi thuộc hệ hô hấp.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm PHÒNG BỆNH THẬN hoặc GÂY HẠI THẬN.', [['Uống đủ nước mỗi ngày', 'PHÒNG BỆNH THẬN'], ['Ăn nhạt, ít muối', 'PHÒNG BỆNH THẬN'], ['Ăn quá mặn', 'GÂY HẠI THẬN'], ['Nhịn tiểu trong thời gian dài', 'GÂY HẠI THẬN']], 'Uống đủ nước, ăn nhạt giúp thận khỏe; ăn mặn và nhịn tiểu lâu gây hại cho thận.', $d);
        $this->sortQ($s, 'Kéo mỗi chất thải vào nhóm THẢI QUA PHỔI hoặc THẢI QUA DA.', [['Khí cacbonic', 'THẢI QUA PHỔI'], ['Hơi nước khi thở ra', 'THẢI QUA PHỔI'], ['Mồ hôi', 'THẢI QUA DA'], ['Bã nhờn', 'THẢI QUA DA']], 'Khí cacbonic và hơi nước thải qua phổi khi thở ra; mồ hôi, bã nhờn thải qua da.', $d);
    }


    private function seedKhChatQuanhTaLop71(): void
    {
        $s = 'kh-chat-quanh-ta-lop-7-1';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi vật vào nhóm THỂ RẮN hoặc THỂ KHÍ.', [['Cục phấn', 'THỂ RẮN'], ['Viên gạch', 'THỂ RẮN'], ['Khí oxi trong bình', 'THỂ KHÍ'], ['Hơi nước', 'THỂ KHÍ']], 'Cục phấn, viên gạch ở thể rắn; khí oxi, hơi nước ở thể khí.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm CẦN NHẬN NHIỆT hoặc TỎA NHIỆT.', [['Đá tan thành nước', 'CẦN NHẬN NHIỆT'], ['Nước sôi thành hơi', 'CẦN NHẬN NHIỆT'], ['Hơi nước ngưng tụ thành giọt', 'TỎA NHIỆT'], ['Nước đông thành đá', 'TỎA NHIỆT']], 'Nóng chảy, bay hơi cần nhận nhiệt; ngưng tụ, đông đặc tỏa nhiệt ra môi trường.', $d);
    }

    private function seedKhChatQuanhTaLop72(): void
    {
        $s = 'kh-chat-quanh-ta-lop-7-2';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi nguồn nước vào nhóm NƯỚC MẶT hoặc NƯỚC NGẦM.', [['Sông', 'NƯỚC MẶT'], ['Hồ', 'NƯỚC MẶT'], ['Giếng khoan', 'NƯỚC NGẦM'], ['Mạch nước ngầm', 'NƯỚC NGẦM']], 'Sông, hồ là nước mặt; giếng khoan, mạch nước ngầm là nước ngầm.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm LÀM SẠCH NƯỚC hoặc KHÔNG LÀM SẠCH NƯỚC.', [['Lắng phèn cho nước trong', 'LÀM SẠCH NƯỚC'], ['Đun sôi nước trước khi uống', 'LÀM SẠCH NƯỚC'], ['Đổ dầu thải xuống cống', 'KHÔNG LÀM SẠCH NƯỚC'], ['Vứt pin cũ xuống ao', 'KHÔNG LÀM SẠCH NƯỚC']], 'Lắng phèn, đun sôi giúp nước sạch; đổ dầu thải, vứt pin cũ làm bẩn nguồn nước.', $d);
        $this->sortQ($s, 'Kéo mỗi giai đoạn vào nhóm NƯỚC Ở TRÊN TRỜI hoặc NƯỚC Ở MẶT ĐẤT.', [['Mây', 'NƯỚC Ở TRÊN TRỜI'], ['Mưa đang rơi', 'NƯỚC Ở TRÊN TRỜI'], ['Sông', 'NƯỚC Ở MẶT ĐẤT'], ['Hồ', 'NƯỚC Ở MẶT ĐẤT']], 'Mây và mưa là nước ở trên trời, sông và hồ là nước ở mặt đất trong vòng tuần hoàn của nước.', $d);
    }

    private function seedKhChatQuanhTaLop81(): void
    {
        $s = 'kh-chat-quanh-ta-lop-8-1';
        $d = 'trung_binh';

        $this->matching($s, 'Ghép mỗi hỗn hợp với cách tách đúng trong phòng thí nghiệm.', [['Nước muối', 'Chưng cất'], ['Dầu ăn và nước', 'Chiết'], ['Cát và nước', 'Lọc'], ['Bột sắt lẫn cát', 'Dùng nam châm']], 'Nước muối tách bằng chưng cất, dầu với nước tách bằng chiết, cát với nước tách bằng lọc, bột sắt tách bằng nam châm.', $d);

        $this->sortQ($s, 'Kéo mỗi hỗn hợp vào nhóm TÁCH ĐƯỢC BẰNG LỌC hoặc KHÔNG TÁCH BẰNG LỌC.', [['Cát và nước', 'TÁCH ĐƯỢC BẰNG LỌC'], ['Bùn và nước', 'TÁCH ĐƯỢC BẰNG LỌC'], ['Bột sắt và cát', 'KHÔNG TÁCH BẰNG LỌC'], ['Dầu ăn và nước', 'KHÔNG TÁCH BẰNG LỌC']], 'Cát, bùn trong nước tách được bằng lọc; bột sắt tách bằng nam châm, dầu với nước tách bằng chiết.', $d);
        $this->sortQ($s, 'Kéo mỗi chất vào nhóm DUNG MÔI hoặc CHẤT TAN.', [['Nước trong nước muối', 'DUNG MÔI'], ['Rượu trong cồn y tế', 'DUNG MÔI'], ['Muối trong nước muối', 'CHẤT TAN'], ['Đường trong nước đường', 'CHẤT TAN']], 'Trong dung dịch, chất chiếm lượng nhiều là dung môi, chất chiếm lượng ít là chất tan.', $d);
        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm HỖN HỢP ĐỒNG NHẤT CÓ NƯỚC hoặc KHÔNG PHẢI.', [['Nước muối', 'HỖN HỢP ĐỒNG NHẤT CÓ NƯỚC'], ['Nước đường', 'HỖN HỢP ĐỒNG NHẤT CÓ NƯỚC'], ['Sữa', 'KHÔNG PHẢI'], ['Không khí', 'KHÔNG PHẢI']], 'Nước muối, nước đường là hỗn hợp đồng nhất có nước; sữa là nhũ tương, không khí không có nước.', $d);
    }

    private function seedKhChatQuanhTaLop82(): void
    {
        $s = 'kh-chat-quanh-ta-lop-8-2';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi hạt vào nhóm NHẸ NHẤT hoặc NẶNG HƠN NHIỀU.', [['Electron', 'NHẸ NHẤT'], ['Proton', 'NẶNG HƠN NHIỀU'], ['Notron', 'NẶNG HƠN NHIỀU'], ['Hạt nhân nguyên tử', 'NẶNG HƠN NHIỀU']], 'Electron nhẹ hơn proton và notron khoảng 1800 lần; hạt nhân tập trung hầu hết khối lượng nguyên tử.', $d);
    }


    private function seedKhNangLuongLop81(): void
    {
        $s = 'kh-nang-luong-lop-8-1';
        $d = 'trung_binh';

        $this->matching($s, 'Ghép mỗi hiện tượng với dạng năng lượng được giải phóng.', [['Sấm sét', 'Điện năng'], ['Núi lửa phun trào', 'Nhiệt năng'], ['Thác nước đổ xuống', 'Động năng'], ['Bom nổ', 'Hóa năng']], 'Sấm sét giải phóng điện năng, núi lửa giải phóng nhiệt năng, thác nước có động năng, bom nổ giải phóng hóa năng.', $d);

        $this->sortQ($s, 'Kéo mỗi vật vào nhóm CÓ THẾ NĂNG ĐÀN HỒI hoặc KHÔNG CÓ.', [['Dây chun bị kéo giãn', 'CÓ THẾ NĂNG ĐÀN HỒI'], ['Lò xo bị nén lại', 'CÓ THẾ NĂNG ĐÀN HỒI'], ['Hòn đá nằm yên trên đất', 'KHÔNG CÓ'], ['Quyển sách trên bàn', 'KHÔNG CÓ']], 'Vật bị biến dạng đàn hồi có thế năng đàn hồi; hòn đá, quyển sách chỉ có thế năng hấp dẫn.', $d);
        $this->sortQ($s, 'Kéo mỗi thiết bị vào nhóm DÙNG NĂNG LƯỢNG ĐIỆN hoặc DÙNG NĂNG LƯỢNG CƠ.', [['Máy giặt', 'DÙNG NĂNG LƯỢNG ĐIỆN'], ['Tủ lạnh', 'DÙNG NĂNG LƯỢNG ĐIỆN'], ['Xe đạp', 'DÙNG NĂNG LƯỢNG CƠ'], ['Cối xay quay tay', 'DÙNG NĂNG LƯỢNG CƠ']], 'Máy giặt, tủ lạnh dùng điện năng; xe đạp, cối xay quay tay dùng năng lượng cơ của con người.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn vào nhóm CÓ THỂ CẠN KIỆT hoặc KHÔNG BAO GIỜ CẠN.', [['Than đá', 'CÓ THỂ CẠN KIỆT'], ['Dầu mỏ', 'CÓ THỂ CẠN KIỆT'], ['Ánh sáng mặt trời', 'KHÔNG BAO GIỜ CẠN'], ['Gió', 'KHÔNG BAO GIỜ CẠN']], 'Than đá, dầu mỏ hình thành hàng triệu năm nên có thể cạn kiệt; mặt trời, gió là nguồn vô tận.', $d);
    }

    private function seedKhNangLuongLop82(): void
    {
        $s = 'kh-nang-luong-lop-8-2';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm CÔNG SUẤT TĂNG hoặc CÔNG SUẤT GIẢM.', [['Cùng một công, làm trong thời gian ngắn hơn', 'CÔNG SUẤT TĂNG'], ['Cùng một thời gian, thực hiện công lớn hơn', 'CÔNG SUẤT TĂNG'], ['Cùng một công, làm trong thời gian dài hơn', 'CÔNG SUẤT GIẢM'], ['Cùng một thời gian, thực hiện công nhỏ hơn', 'CÔNG SUẤT GIẢM']], 'Công suất = công / thời gian: làm nhanh hơn hoặc làm được nhiều công hơn trong cùng thời gian thì công suất tăng.', $d);
    }

    private function seedKhNangLuongLop91(): void
    {
        $s = 'kh-nang-luong-lop-9-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi cách dùng vào nhóm BÓNG ĐÈN SÁNG MẠNH HƠN hoặc SÁNG YẾU HƠN.', [['Mắc hai bóng song song', 'SÁNG MẠNH HƠN'], ['Tăng số pin trong mạch', 'SÁNG MẠNH HƠN'], ['Mắc hai bóng nối tiếp', 'SÁNG YẾU HƠN'], ['Giảm số pin trong mạch', 'SÁNG YẾU HƠN']], 'Mắc song song và tăng số pin giúp bóng sáng mạnh hơn; mắc nối tiếp và giảm pin làm bóng sáng yếu hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi vật liệu vào nhóm DẪN ĐIỆN TỐT hoặc DẪN ĐIỆN KÉM.', [['Đồng', 'DẪN ĐIỆN TỐT'], ['Nhôm', 'DẪN ĐIỆN TỐT'], ['Nước tinh khiết', 'DẪN ĐIỆN KÉM'], ['Gỗ khô', 'DẪN ĐIỆN KÉM']], 'Đồng, nhôm dẫn điện tốt nên làm dây điện; nước tinh khiết, gỗ khô dẫn điện kém.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm NÓI VỀ DÒNG ĐIỆN hoặc NÓI VỀ HIỆU ĐIỆN THẾ.', [['Đo bằng ampe kế', 'NÓI VỀ DÒNG ĐIỆN'], ['Đơn vị là ampe', 'NÓI VỀ DÒNG ĐIỆN'], ['Đo bằng vôn kế', 'NÓI VỀ HIỆU ĐIỆN THẾ'], ['Đơn vị là vôn', 'NÓI VỀ HIỆU ĐIỆN THẾ']], 'Dòng điện đo bằng ampe kế, đơn vị ampe; hiệu điện thế đo bằng vôn kế, đơn vị vôn.', $d);
    }

    private function seedKhNangLuongLop92(): void
    {
        $s = 'kh-nang-luong-lop-9-2';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi thiết bị vào nhóm CÔNG SUẤT TRÊN 1000 W hoặc DƯỚI 1000 W.', [['Bàn ủi điện', 'CÔNG SUẤT TRÊN 1000 W'], ['Bếp điện từ', 'CÔNG SUẤT TRÊN 1000 W'], ['Quạt bàn', 'CÔNG SUẤT DƯỚI 1000 W'], ['Bóng đèn LED', 'CÔNG SUẤT DƯỚI 1000 W']], 'Bàn ủi, bếp từ có công suất trên 1000 W nên tốn nhiều điện; quạt bàn, đèn LED công suất nhỏ.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm GIẢM HÓA ĐƠN TIỀN ĐIỆN hoặc TĂNG HÓA ĐƠN.', [['Dùng đèn LED thay đèn sợi đốt', 'GIẢM HÓA ĐƠN TIỀN ĐIỆN'], ['Tắt điều hòa khi ra khỏi phòng', 'GIẢM HÓA ĐƠN TIỀN ĐIỆN'], ['Để cửa tủ lạnh mở lâu', 'TĂNG HÓA ĐƠN'], ['Bật bình nóng lạnh cả ngày', 'TĂNG HÓA ĐƠN']], 'Dùng đèn LED, tắt điều hòa khi không dùng giúp giảm tiền điện; mở tủ lạnh lâu, bật bình nóng lạnh cả ngày làm tăng tiền điện.', $d);
        $this->sortQ($s, 'Kéo mỗi tình huống vào nhóm NÊN NGẮT ĐIỆN NGAY hoặc CHƯA CẦN NGẮT.', [['Ngửi thấy mùi khét từ ổ cắm', 'NÊN NGẮT ĐIỆN NGAY'], ['Nhìn thấy tia lửa điện', 'NÊN NGẮT ĐIỆN NGAY'], ['Bóng đèn bị cháy', 'CHƯA CẦN NGẮT'], ['Đui đèn lỏng gây nhấp nháy', 'CHƯA CẦN NGẮT']], 'Mùi khét, tia lửa điện là dấu hiệu nguy hiểm phải ngắt điện ngay; bóng cháy, đui lỏng chỉ cần sửa chữa.', $d);
    }


    private function seedDlChauLucDaiDuongLop61(): void
    {
        $s = 'dl-chau-luc-dai-duong-lop-6-1';
        $d = 'de';

        $this->matching($s, 'Ghép mỗi công trình nổi tiếng châu Á với quốc gia của nó.', [['Vạn Lý Trường Thành', 'Trung Quốc'], ['Đền Angkor Wat', 'Campuchia'], ['Núi Phú Sĩ', 'Nhật Bản'], ['Tháp đôi Petronas', 'Malaysia']], 'Vạn Lý Trường Thành ở Trung Quốc, Angkor Wat ở Campuchia, núi Phú Sĩ ở Nhật Bản, tháp đôi Petronas ở Malaysia.', $d);

        $this->sortQ($s, 'Kéo mỗi quốc gia vào nhóm Ở PHÍA ĐÔNG hoặc Ở PHÍA TÂY châu Á.', [['Nhật Bản', 'Ở PHÍA ĐÔNG'], ['Hàn Quốc', 'Ở PHÍA ĐÔNG'], ['Ả Rập Xê Út', 'Ở PHÍA TÂY'], ['Thổ Nhĩ Kỳ', 'Ở PHÍA TÂY']], 'Nhật Bản, Hàn Quốc ở phía đông châu Á; Ả Rập Xê Út, Thổ Nhĩ Kỳ ở phía tây châu Á.', $d);
        $this->sortQ($s, 'Kéo mỗi nước vào nhóm ĐÔNG DÂN NHẤT THẾ GIỚI hoặc KHÔNG PHẢI.', [['Ấn Độ', 'ĐÔNG DÂN NHẤT THẾ GIỚI'], ['Trung Quốc', 'KHÔNG PHẢI'], ['Indonesia', 'KHÔNG PHẢI'], ['Pakistan', 'KHÔNG PHẢI']], 'Ấn Độ hiện là nước đông dân nhất thế giới, tiếp theo là Trung Quốc.', $d);
        $this->sortQ($s, 'Kéo mỗi biển vào nhóm GIÁP CHÂU Á hoặc KHÔNG GIÁP CHÂU Á.', [['Biển Đông', 'GIÁP CHÂU Á'], ['Biển Ả Rập', 'GIÁP CHÂU Á'], ['Biển Caribe', 'KHÔNG GIÁP CHÂU Á'], ['Biển Baltic', 'KHÔNG GIÁP CHÂU Á']], 'Biển Đông, biển Ả Rập giáp châu Á; biển Caribe, biển Baltic không giáp châu Á.', $d);
    }

    private function seedDlChauLucDaiDuongLop62(): void
    {
        $s = 'dl-chau-luc-dai-duong-lop-6-2';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm Ở CHÂU MỸ hoặc KHÔNG Ở CHÂU MỸ.', [['Dãy Andes', 'Ở CHÂU MỸ'], ['Sông Amazon', 'Ở CHÂU MỸ'], ['Sa mạc Sahara', 'KHÔNG Ở CHÂU MỸ'], ['Sông Nile', 'KHÔNG Ở CHÂU MỸ']], 'Dãy Andes, sông Amazon ở châu Mỹ; sa mạc Sahara, sông Nile ở châu Phi.', $d);
    }

    private function seedDlChauLucDaiDuongLop71(): void
    {
        $s = 'dl-chau-luc-dai-duong-lop-7-1';
        $d = 'de';

        $this->matching($s, 'Ghép mỗi dòng sông châu Âu với đặc điểm của nó.', [['Sông Danube', 'Chảy qua nhiều nước, đổ ra Biển Đen'], ['Sông Rhine', 'Chảy qua Đức và Hà Lan'], ['Sông Seine', 'Chảy qua thủ đô Paris của Pháp'], ['Sông Volga', 'Dài nhất châu Âu, nằm ở Nga']], 'Danube chảy qua nhiều nước đổ ra Biển Đen, Rhine qua Đức – Hà Lan, Seine qua Paris, Volga dài nhất châu Âu.', $d);
        $this->matching($s, 'Ghép mỗi nước châu Âu với đặc trưng nổi tiếng của nó.', [['Pháp', 'Rượu vang và thời trang'], ['Thụy Sĩ', 'Đồng hồ và sô cô la'], ['Hà Lan', 'Hoa tulip và cối xay gió'], ['Ý', 'Mì Ý và pizza']], 'Pháp nổi tiếng rượu vang, Thụy Sĩ nổi tiếng đồng hồ, Hà Lan nổi tiếng hoa tulip, Ý nổi tiếng mì Ý.', $d);

        $this->sortQ($s, 'Kéo mỗi nước vào nhóm BẮC ÂU hoặc NAM ÂU.', [['Na Uy', 'BẮC ÂU'], ['Thụy Điển', 'BẮC ÂU'], ['Tây Ban Nha', 'NAM ÂU'], ['Hy Lạp', 'NAM ÂU']], 'Na Uy, Thụy Điển thuộc Bắc Âu; Tây Ban Nha, Hy Lạp thuộc Nam Âu.', $d);
    }

    private function seedDlChauLucDaiDuongLop72(): void
    {
        $s = 'dl-chau-luc-dai-duong-lop-7-2';
        $d = 'trung_binh';

        $this->matching($s, 'Ghép mỗi con vật biểu tượng với châu lục quê hương của nó.', [['Sư tử', 'Châu Phi'], ['Kangaroo', 'Châu Đại Dương'], ['Gấu trúc', 'Châu Á'], ['Đại bàng đầu trắng', 'Châu Mỹ']], 'Sư tử là biểu tượng châu Phi, kangaroo của châu Đại Dương, gấu trúc của châu Á, đại bàng đầu trắng của châu Mỹ.', $d);
    }


    private function seedDlThuDoCacNuocLop71(): void
    {
        $s = 'dl-thu-do-cac-nuoc-lop-7-1';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm NẰM Ở LỤC ĐỊA hoặc Ở HẢI ĐẢO.', [['Hà Nội', 'NẰM Ở LỤC ĐỊA'], ['Bangkok', 'NẰM Ở LỤC ĐỊA'], ['Jakarta', 'Ở HẢI ĐẢO'], ['Manila', 'Ở HẢI ĐẢO']], 'Hà Nội, Bangkok nằm trên lục địa; Jakarta, Manila là thủ đô của quốc đảo.', $d);
    }

    private function seedDlThuDoCacNuocLop72(): void
    {
        $s = 'dl-thu-do-cac-nuoc-lop-7-2';
        $d = 'de';

        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm CỦA NƯỚC ĐÔNG DÂN (trên 100 triệu) hoặc ÍT DÂN HƠN.', [['Bắc Kinh', 'CỦA NƯỚC ĐÔNG DÂN (trên 100 triệu)'], ['New Delhi', 'CỦA NƯỚC ĐÔNG DÂN (trên 100 triệu)'], ['Seoul', 'ÍT DÂN HƠN'], ['Ulaanbaatar', 'ÍT DÂN HƠN']], 'Trung Quốc, Ấn Độ đều trên 1 tỉ dân; Hàn Quốc khoảng 52 triệu, Mông Cổ khoảng 3 triệu dân.', $d);
    }

    private function seedDlThuDoCacNuocLop81(): void
    {
        $s = 'dl-thu-do-cac-nuoc-lop-8-1';
        $d = 'trung_binh';

        $this->matching($s, 'Ghép mỗi nước Tây Âu ít được nhắc tới với thủ đô của nó.', [['Bỉ', 'Brussels'], ['Áo', 'Vienna'], ['Thụy Sĩ', 'Bern'], ['Ireland', 'Dublin']], 'Thủ đô Bỉ là Brussels, Áo là Vienna, Thụy Sĩ là Bern, Ireland là Dublin.', $d);
        $this->matching($s, 'Ghép mỗi nước Đông – Bắc Âu với thủ đô của nó.', [['Ba Lan', 'Warsaw'], ['Hungary', 'Budapest'], ['Na Uy', 'Oslo'], ['Phần Lan', 'Helsinki']], 'Thủ đô Ba Lan là Warsaw, Hungary là Budapest, Na Uy là Oslo, Phần Lan là Helsinki.', $d);
        $this->matching($s, 'Ghép mỗi nước Nam Âu với thủ đô của nó.', [['Bồ Đào Nha', 'Lisbon'], ['Hy Lạp', 'Athens'], ['Croatia', 'Zagreb'], ['Romania', 'Bucharest']], 'Thủ đô Bồ Đào Nha là Lisbon, Hy Lạp là Athens, Croatia là Zagreb, Romania là Bucharest.', $d);

        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm Ở NƯỚC DÙNG ĐỒNG EURO hoặc KHÔNG DÙNG.', [['Paris', 'Ở NƯỚC DÙNG ĐỒNG EURO'], ['Berlin', 'Ở NƯỚC DÙNG ĐỒNG EURO'], ['London', 'KHÔNG DÙNG'], ['Oslo', 'KHÔNG DÙNG']], 'Pháp, Đức dùng đồng euro; Anh dùng bảng Anh, Na Uy dùng krone.', $d);
    }

    private function seedDlThuDoCacNuocLop82(): void
    {
        $s = 'dl-thu-do-cac-nuoc-lop-8-2';
        $d = 'trung_binh';

        $this->matching($s, 'Ghép mỗi thủ đô với điều đặc biệt của nó.', [['Washington D.C.', 'Đặt theo tên tổng thống đầu tiên của Mỹ'], ['Canberra', 'Thủ đô được quy hoạch và xây mới'], ['Brasilia', 'Thủ đô xây mới giữa cao nguyên'], ['Ottawa', 'Nằm bên bờ sông Ottawa']], 'Washington D.C. mang tên tổng thống đầu tiên, Canberra và Brasilia là thủ đô xây mới, Ottawa nằm bên sông Ottawa.', $d);

        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm NÓI TIẾNG ANH hoặc NÓI TIẾNG KHÁC.', [['Canberra', 'NÓI TIẾNG ANH'], ['Ottawa', 'NÓI TIẾNG ANH'], ['Buenos Aires', 'NÓI TIẾNG KHÁC'], ['Cairo', 'NÓI TIẾNG KHÁC']], 'Canberra, Ottawa dùng tiếng Anh; Buenos Aires dùng tiếng Tây Ban Nha, Cairo dùng tiếng Ả Rập.', $d);
    }


    private function seedDlDiaHinhVietNamLop81(): void
    {
        $s = 'dl-dia-hinh-viet-nam-lop-8-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi đỉnh núi vào nhóm CAO TRÊN 2000 m hoặc THẤP HƠN 2000 m.', [['Fansipan', 'CAO TRÊN 2000 m'], ['Pu Si Lung', 'CAO TRÊN 2000 m'], ['Bạch Mã', 'THẤP HƠN 2000 m'], ['Núi Bà Đen', 'THẤP HƠN 2000 m']], 'Fansipan (3143 m) và Pu Si Lung (3083 m) cao trên 2000 m; Bạch Mã và núi Bà Đen thấp hơn 2000 m.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉnh vào nhóm Ở TÂY NGUYÊN hoặc KHÔNG Ở TÂY NGUYÊN.', [['Đắk Lắk', 'Ở TÂY NGUYÊN'], ['Gia Lai', 'Ở TÂY NGUYÊN'], ['Lào Cai', 'KHÔNG Ở TÂY NGUYÊN'], ['Quảng Ninh', 'KHÔNG Ở TÂY NGUYÊN']], 'Đắk Lắk, Gia Lai thuộc Tây Nguyên; Lào Cai ở Tây Bắc, Quảng Ninh ở Đông Bắc.', $d);
    }

    private function seedDlDiaHinhVietNamLop82(): void
    {
        $s = 'dl-dia-hinh-viet-nam-lop-8-2';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi tỉnh vào nhóm CÓ BIỂN hoặc KHÔNG CÓ BIỂN.', [['Khánh Hòa', 'CÓ BIỂN'], ['Bình Thuận', 'CÓ BIỂN'], ['Đắk Lắk', 'KHÔNG CÓ BIỂN'], ['Thái Nguyên', 'KHÔNG CÓ BIỂN']], 'Khánh Hòa, Bình Thuận giáp biển; Đắk Lắk, Thái Nguyên không giáp biển.', $d);
    }

    private function seedDlDiaHinhVietNamLop91(): void
    {
        $s = 'dl-dia-hinh-viet-nam-lop-9-1';
        $d = 'trung_binh';

        $this->matching($s, 'Ghép mỗi khoáng sản với nhóm của nó.', [['Than đá', 'Khoáng sản năng lượng'], ['Sắt', 'Kim loại đen'], ['Bô xít', 'Kim loại màu'], ['Đá vôi', 'Phi kim loại']], 'Than đá là khoáng sản năng lượng, sắt là kim loại đen, bô xít là kim loại màu, đá vôi là phi kim loại.', $d);

        $this->sortQ($s, 'Kéo mỗi tài nguyên vào nhóm VÔ TẬN hoặc CÓ HẠN.', [['Năng lượng mặt trời', 'VÔ TẬN'], ['Gió', 'VÔ TẬN'], ['Dầu mỏ', 'CÓ HẠN'], ['Than đá', 'CÓ HẠN']], 'Mặt trời, gió là tài nguyên vô tận; dầu mỏ, than đá hình thành hàng triệu năm nên có hạn.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm SỬ DỤNG TIẾT KIỆM hoặc LÃNG PHÍ tài nguyên.', [['Tái chế giấy cũ', 'SỬ DỤNG TIẾT KIỆM'], ['Dùng túi vải thay túi ni lông', 'SỬ DỤNG TIẾT KIỆM'], ['Đốt than bừa bãi', 'LÃNG PHÍ'], ['Khai thác cát trái phép', 'LÃNG PHÍ']], 'Tái chế giấy, dùng túi vải là tiết kiệm tài nguyên; đốt than bừa bãi, khai thác cát trái phép là lãng phí.', $d);
        $this->sortQ($s, 'Kéo mỗi khoáng sản vào nhóm Ở QUẢNG NINH hoặc Ở NƠI KHÁC.', [['Than đá', 'Ở QUẢNG NINH'], ['Đá vôi', 'Ở QUẢNG NINH'], ['Bô xít', 'Ở NƠI KHÁC'], ['Dầu khí', 'Ở NƠI KHÁC']], 'Quảng Ninh giàu than đá và đá vôi; bô xít ở Tây Nguyên, dầu khí ở thềm lục địa phía Nam.', $d);
    }

    private function seedDlDiaHinhVietNamLop92(): void
    {
        $s = 'dl-dia-hinh-viet-nam-lop-9-2';
        $d = 'kho';

        $this->matching($s, 'Ghép mỗi loại ô nhiễm với biểu hiện của nó.', [['Ô nhiễm không khí', 'Khói bụi mù mịt'], ['Ô nhiễm nguồn nước', 'Nước đổi màu, bốc mùi'], ['Ô nhiễm tiếng ồn', 'Tiếng động lớn kéo dài'], ['Ô nhiễm đất', 'Đất chai cứng, cây khó sống']], 'Ô nhiễm không khí gây khói bụi, ô nhiễm nước làm nước đổi màu bốc mùi, ô nhiễm tiếng ồn gây tiếng động lớn, ô nhiễm đất làm đất chai cứng.', $d);
        $this->matching($s, 'Ghép mỗi giải pháp với đối tượng nó bảo vệ.', [['Trồng rừng', 'Đất và không khí'], ['Xử lý nước thải', 'Nguồn nước'], ['Phân loại rác tại nguồn', 'Môi trường sống'], ['Dùng năng lượng sạch', 'Khí hậu']], 'Trồng rừng bảo vệ đất và không khí, xử lý nước thải bảo vệ nguồn nước, phân loại rác và năng lượng sạch bảo vệ môi trường, khí hậu.', $d);

        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm GIẢM KHÍ NHÀ KÍNH hoặc TĂNG KHÍ NHÀ KÍNH.', [['Đi xe buýt thay xe máy', 'GIẢM KHÍ NHÀ KÍNH'], ['Trồng thêm cây xanh', 'GIẢM KHÍ NHÀ KÍNH'], ['Đốt than đá', 'TĂNG KHÍ NHÀ KÍNH'], ['Chặt phá rừng', 'TĂNG KHÍ NHÀ KÍNH']], 'Đi xe buýt, trồng cây giúp giảm khí nhà kính; đốt than, chặt phá rừng làm tăng khí nhà kính.', $d);
        $this->sortQ($s, 'Kéo mỗi loại chất thải vào nhóm PHÂN HỦY NHANH hoặc PHÂN HỦY CHẬM.', [['Vỏ chuối', 'PHÂN HỦY NHANH'], ['Giấy vụn', 'PHÂN HỦY NHANH'], ['Túi ni lông', 'PHÂN HỦY CHẬM'], ['Chai thủy tinh', 'PHÂN HỦY CHẬM']], 'Vỏ chuối, giấy phân hủy nhanh trong vài tuần; túi ni lông, chai thủy tinh cần hàng trăm năm mới phân hủy.', $d);
    }


    private function seedKhoaHocThpt10Lop101(): void
    {
        $s = 'khoa-hoc-thpt-10-lop-10-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi đồ thị vào nhóm ĐƯỜNG THẲNG (chuyển động đều) hoặc ĐƯỜNG CONG (biến đổi).', [['Đồ thị x – t là đường thẳng xiên', 'ĐƯỜNG THẲNG (chuyển động đều)'], ['Đồ thị v – t là đường nằm ngang', 'ĐƯỜNG THẲNG (chuyển động đều)'], ['Đồ thị v – t dốc lên', 'ĐƯỜNG CONG (biến đổi)'], ['Đồ thị x – t là đường cong', 'ĐƯỜNG CONG (biến đổi)']], 'Chuyển động thẳng đều có đồ thị x–t là đường thẳng, v–t nằm ngang; chuyển động biến đổi cho đồ thị cong.', $d);
        $this->sortQ($s, 'Kéo mỗi đơn vị vào nhóm CỦA VẬN TỐC hoặc CỦA QUÃNG ĐƯỜNG.', [['m/s', 'CỦA VẬN TỐC'], ['km/h', 'CỦA VẬN TỐC'], ['mét', 'CỦA QUÃNG ĐƯỜNG'], ['kilômét', 'CỦA QUÃNG ĐƯỜNG']], 'Vận tốc có đơn vị m/s, km/h; quãng đường có đơn vị mét, kilômét.', $d);
        $this->sortQ($s, 'Kéo mỗi tình huống vào nhóm QUÃNG ĐƯỜNG BẰNG ĐỘ DỜI hoặc KHÁC NHAU.', [['Đi thẳng một chiều không đổi hướng', 'QUÃNG ĐƯỜNG BẰNG ĐỘ DỜI'], ['Đi rồi quay lại đúng điểm xuất phát', 'KHÁC NHAU'], ['Chạy một vòng tròn về chỗ cũ', 'KHÁC NHAU'], ['Bơi thẳng từ bờ này sang bờ kia', 'QUÃNG ĐƯỜNG BẰNG ĐỘ DỜI']], 'Đi thẳng một chiều thì quãng đường bằng độ dời; quay về điểm xuất phát thì độ dời bằng 0.', $d);
    }

    private function seedKhoaHocThpt10Lop102(): void
    {
        $s = 'khoa-hoc-thpt-10-lop-10-2';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi chuyển động vào nhóm GIA TỐC KHÔNG ĐỔI hoặc GIA TỐC THAY ĐỔI.', [['Vật rơi tự do', 'GIA TỐC KHÔNG ĐỔI'], ['Xe hãm phanh đều', 'GIA TỐC KHÔNG ĐỔI'], ['Xe rú ga mạnh dần', 'GIA TỐC THAY ĐỔI'], ['Tàu lượn siêu tốc', 'GIA TỐC THAY ĐỔI']], 'Rơi tự do và hãm phanh đều có gia tốc không đổi; rú ga mạnh dần và tàu lượn có gia tốc thay đổi.', $d);
        $this->sortQ($s, 'Kéo mỗi đại lượng vào nhóm ĐẶC TRƯNG CHO SỰ BIẾN ĐỔI VẬN TỐC hoặc KHÔNG.', [['Gia tốc', 'ĐẶC TRƯNG CHO SỰ BIẾN ĐỔI VẬN TỐC'], ['Độ biến thiên vận tốc', 'ĐẶC TRƯNG CHO SỰ BIẾN ĐỔI VẬN TỐC'], ['Quãng đường', 'KHÔNG'], ['Thời gian', 'KHÔNG']], 'Gia tốc đặc trưng cho sự biến đổi nhanh hay chậm của vận tốc theo thời gian.', $d);
        $this->sortQ($s, 'Kéo mỗi công thức vào nhóm CỦA CHUYỂN ĐỘNG BIẾN ĐỔI ĐỀU hoặc KHÔNG PHẢI.', [['v = v0 + at', 'CỦA CHUYỂN ĐỘNG BIẾN ĐỔI ĐỀU'], ['s = v0t + at²/2', 'CỦA CHUYỂN ĐỘNG BIẾN ĐỔI ĐỀU'], ['v² − v0² = 2as', 'CỦA CHUYỂN ĐỘNG BIẾN ĐỔI ĐỀU'], ['s = vt', 'KHÔNG PHẢI']], 'Ba công thức đầu dùng cho chuyển động biến đổi đều; s = vt là công thức của chuyển động thẳng đều.', $d);
    }

    private function seedKhoaHocThpt10Lop103(): void
    {
        $s = 'khoa-hoc-thpt-10-lop-10-3';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm MINH HỌA ĐỊNH LUẬT I hoặc ĐỊNH LUẬT III NEWTON.', [['Hành khách ngả về sau khi xe khởi động', 'MINH HỌA ĐỊNH LUẬT I'], ['Bút tiếp tục lăn tới khi xe phanh gấp', 'MINH HỌA ĐỊNH LUẬT I'], ['Tên lửa phụt khí đẩy lên cao', 'MINH HỌA ĐỊNH LUẬT III'], ['Tay bị đau khi đấm mạnh vào tường', 'MINH HỌA ĐỊNH LUẬT III']], 'Quán tính thuộc định luật I; tên lửa bay lên và tay đau khi đấm tường là lực – phản lực của định luật III.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp (vật 1 kg) vào nhóm GIA TỐC LỚN HƠN hoặc NHỎ HƠN 5 m/s².', [['Lực tác dụng 8 N', 'GIA TỐC LỚN HƠN'], ['Lực tác dụng 10 N', 'GIA TỐC LỚN HƠN'], ['Lực tác dụng 3 N', 'GIA TỐC NHỎ HƠN'], ['Lực tác dụng 2 N', 'GIA TỐC NHỎ HƠN']], 'Theo F = ma, vật 1 kg chịu lực 8 N và 10 N có gia tốc lớn hơn 5 m/s²; lực 3 N và 2 N cho gia tốc nhỏ hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi đại lượng vào nhóm TỈ LỆ THUẬN hoặc TỈ LỆ NGHỊCH với gia tốc (F = ma).', [['Lực kéo vật', 'TỈ LỆ THUẬN'], ['Lực đẩy vật', 'TỈ LỆ THUẬN'], ['Khối lượng của xe', 'TỈ LỆ NGHỊCH'], ['Khối lượng hàng trên xe', 'TỈ LỆ NGHỊCH']], 'Theo F = ma, lực tỉ lệ thuận với gia tốc, khối lượng tỉ lệ nghịch với gia tốc.', $d);
    }

    private function seedKhoaHocThpt10Lop104(): void
    {
        $s = 'khoa-hoc-thpt-10-lop-10-4';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm MA SÁT NGHỈ, MA SÁT TRƯỢT hoặc MA SÁT LĂN.', [['Giữ quyển sách không tuột khỏi tay', 'MA SÁT NGHỈ'], ['Đẩy tủ nhưng tủ chưa nhúc nhích', 'MA SÁT NGHỈ'], ['Kéo thùng hàng trượt trên sàn', 'MA SÁT TRƯỢT'], ['Bánh xe lăn trên mặt đường', 'MA SÁT LĂN']], 'Giữ sách và đẩy tủ chưa nhúc nhích là ma sát nghỉ, kéo thùng trượt là ma sát trượt, bánh xe lăn là ma sát lăn.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm XUẤT HIỆN hoặc KHÔNG XUẤT HIỆN lực đàn hồi.', [['Lò xo bị kéo giãn', 'XUẤT HIỆN'], ['Dây chun bị kéo dài', 'XUẤT HIỆN'], ['Hòn đá đặt yên', 'KHÔNG XUẤT HIỆN'], ['Thanh sắt chưa bị biến dạng', 'KHÔNG XUẤT HIỆN']], 'Lực đàn hồi xuất hiện khi vật đàn hồi bị biến dạng như lò xo giãn, dây chun kéo dài.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm LÀM TĂNG hoặc KHÔNG LÀM TĂNG lực ma sát trượt.', [['Tăng áp lực lên mặt tiếp xúc', 'LÀM TĂNG'], ['Mặt tiếp xúc gồ ghề hơn', 'LÀM TĂNG'], ['Bôi trơn mặt tiếp xúc', 'KHÔNG LÀM TĂNG'], ['Giảm diện tích tiếp xúc', 'KHÔNG LÀM TĂNG']], 'Ma sát trượt tăng khi áp lực lớn và mặt gồ ghề; bôi trơn làm giảm, diện tích tiếp xúc không ảnh hưởng.', $d);
    }


    private function seedKhoaHocThpt11Lop111(): void
    {
        $s = 'khoa-hoc-thpt-11-lop-11-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi đối tượng vào nhóm LÀ HẠT CƠ BẢN hoặc KHÔNG PHẢI HẠT CƠ BẢN.', [['Electron', 'LÀ HẠT CƠ BẢN'], ['Quark', 'LÀ HẠT CƠ BẢN'], ['Proton', 'KHÔNG PHẢI HẠT CƠ BẢN'], ['Notron', 'KHÔNG PHẢI HẠT CƠ BẢN']], 'Electron và quark là hạt cơ bản; proton, notron được cấu tạo từ các quark nên không phải hạt cơ bản.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm CÓ Z NHỎ HƠN 10 hoặc LỚN HƠN 10.', [['Hiđro (H)', 'CÓ Z NHỎ HƠN 10'], ['Heli (He)', 'CÓ Z NHỎ HƠN 10'], ['Natri (Na)', 'CÓ Z LỚN HƠN 10'], ['Magie (Mg)', 'CÓ Z LỚN HƠN 10']], 'H có Z = 1, He có Z = 2; Na có Z = 11, Mg có Z = 12.', $d);
        $this->sortQ($s, 'Kéo mỗi hạt vào nhóm ĐIỆN TÍCH DƯƠNG, ÂM hoặc TRUNG HÒA.', [['Proton', 'ĐIỆN TÍCH DƯƠNG'], ['Hạt nhân heli', 'ĐIỆN TÍCH DƯƠNG'], ['Electron', 'ĐIỆN TÍCH ÂM'], ['Notron', 'TRUNG HÒA']], 'Proton mang điện dương, electron mang điện âm, notron trung hòa về điện.', $d);
    }

    private function seedKhoaHocThpt11Lop112(): void
    {
        $s = 'khoa-hoc-thpt-11-lop-11-2';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi cặp nguyên tử vào nhóm CÓ SỐ NOTRON BẰNG NHAU hoặc KHÁC NHAU.', [['N-14 và C-13', 'CÓ SỐ NOTRON BẰNG NHAU'], ['Na-23 và Mg-24', 'CÓ SỐ NOTRON BẰNG NHAU'], ['C-12 và C-14', 'KHÁC NHAU'], ['O-16 và O-18', 'KHÁC NHAU']], 'N-14 và C-13 đều có 7 notron, Na-23 và Mg-24 đều có 12 notron; các cặp đồng vị còn lại khác số notron.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm LỚP NGOÀI CÙNG CÓ 1 hoặc CÓ 2 ELECTRON.', [['Natri (Na)', 'CÓ 1 ELECTRON'], ['Kali (K)', 'CÓ 1 ELECTRON'], ['Magie (Mg)', 'CÓ 2 ELECTRON'], ['Canxi (Ca)', 'CÓ 2 ELECTRON']], 'Na, K thuộc nhóm IA có 1 electron lớp ngoài cùng; Mg, Ca thuộc nhóm IIA có 2 electron lớp ngoài cùng.', $d);
    }

    private function seedKhoaHocThpt11Lop113(): void
    {
        $s = 'khoa-hoc-thpt-11-lop-11-3';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm CHU KÌ 1 hoặc CHU KÌ 4.', [['Hiđro (H)', 'CHU KÌ 1'], ['Heli (He)', 'CHU KÌ 1'], ['Kali (K)', 'CHU KÌ 4'], ['Canxi (Ca)', 'CHU KÌ 4']], 'H, He có 1 lớp electron nên ở chu kì 1; K, Ca có 4 lớp electron nên ở chu kì 4.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm NHÓM IA hoặc NHÓM VIIA.', [['Liti (Li)', 'NHÓM IA'], ['Natri (Na)', 'NHÓM IA'], ['Flo (F)', 'NHÓM VIIA'], ['Clo (Cl)', 'NHÓM VIIA']], 'Li, Na là kim loại kiềm thuộc nhóm IA; F, Cl là halogen thuộc nhóm VIIA.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm LÀ KIM LOẠI KIỀM THỔ hoặc KHÔNG PHẢI.', [['Magie (Mg)', 'LÀ KIM LOẠI KIỀM THỔ'], ['Canxi (Ca)', 'LÀ KIM LOẠI KIỀM THỔ'], ['Natri (Na)', 'KHÔNG PHẢI'], ['Nhôm (Al)', 'KHÔNG PHẢI']], 'Mg, Ca thuộc nhóm IIA là kim loại kiềm thổ; Na là kim loại kiềm, Al là kim loại nhóm IIIA.', $d);
    }

    private function seedKhoaHocThpt11Lop114(): void
    {
        $s = 'khoa-hoc-thpt-11-lop-11-4';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi nguyên tố chu kì 2 vào nhóm ĐỘ ÂM ĐIỆN LỚN hoặc NHỎ.', [['Flo (F)', 'ĐỘ ÂM ĐIỆN LỚN'], ['Oxi (O)', 'ĐỘ ÂM ĐIỆN LỚN'], ['Liti (Li)', 'ĐỘ ÂM ĐIỆN NHỎ'], ['Beri (Be)', 'ĐỘ ÂM ĐIỆN NHỎ']], 'Trong một chu kì, độ âm điện tăng dần từ trái sang phải; F có độ âm điện lớn nhất.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm DỄ NHƯỜNG hoặc DỄ NHẬN ELECTRON.', [['Natri (Na)', 'DỄ NHƯỜNG ELECTRON'], ['Kali (K)', 'DỄ NHƯỜNG ELECTRON'], ['Clo (Cl)', 'DỄ NHẬN ELECTRON'], ['Oxi (O)', 'DỄ NHẬN ELECTRON']], 'Kim loại kiềm Na, K dễ nhường electron tạo cation; phi kim Cl, O dễ nhận electron tạo anion.', $d);
        $this->sortQ($s, 'Kéo mỗi ion vào nhóm CÓ CẤU HÌNH ELECTRON CỦA KHÍ HIẾM hoặc KHÔNG.', [['Na+', 'CÓ CẤU HÌNH CỦA KHÍ HIẾM'], ['Mg2+', 'CÓ CẤU HÌNH CỦA KHÍ HIẾM'], ['Cl−', 'CÓ CẤU HÌNH CỦA KHÍ HIẾM'], ['Fe2+', 'KHÔNG']], 'Na+, Mg2+ có cấu hình của Ne, Cl− có cấu hình của Ar; Fe2+ không đạt cấu hình khí hiếm.', $d);
    }


    private function seedKhoaHocThpt12Lop121(): void
    {
        $s = 'khoa-hoc-thpt-12-lop-12-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi bazơ nitơ vào nhóm BỔ SUNG VỚI A – T hoặc BỔ SUNG VỚI G – X.', [['Ađenin', 'BỔ SUNG VỚI A – T'], ['Timin', 'BỔ SUNG VỚI A – T'], ['Guanin', 'BỔ SUNG VỚI G – X'], ['Xitozin', 'BỔ SUNG VỚI G – X']], 'Theo nguyên tắc bổ sung, A liên kết với T bằng 2 liên kết hiđro, G liên kết với X bằng 3 liên kết hiđro.', $d);
        $this->sortQ($s, 'Kéo mỗi loại đường vào nhóm CỦA ADN, CỦA ARN hoặc KHÔNG PHẢI.', [['Đeoxiribozo', 'CỦA ADN'], ['Ribozo', 'CỦA ARN'], ['Glucozo', 'KHÔNG PHẢI'], ['Fructozo', 'KHÔNG PHẢI']], 'Đường đeoxiribozo có trong ADN, ribozo có trong ARN; glucozo, fructozo không phải thành phần axit nucleic.', $d);
    }

    private function seedKhoaHocThpt12Lop122(): void
    {
        $s = 'khoa-hoc-thpt-12-lop-12-2';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi bộ ba vào nhóm MÃ KẾT THÚC hoặc KHÔNG PHẢI MÃ KẾT THÚC.', [['UAA', 'MÃ KẾT THÚC'], ['UAG', 'MÃ KẾT THÚC'], ['UGA', 'MÃ KẾT THÚC'], ['AUG', 'KHÔNG PHẢI MÃ KẾT THÚC']], 'UAA, UAG, UGA là ba mã kết thúc; AUG là mã mở đầu đồng thời mã hóa metionin.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm CỦA MÃ DI TRUYỀN hoặc KHÔNG PHẢI.', [['Tính đặc hiệu', 'CỦA MÃ DI TRUYỀN'], ['Tính thoái hóa', 'CỦA MÃ DI TRUYỀN'], ['Di truyền theo dòng mẹ', 'KHÔNG PHẢI'], ['Tính trội – lặn', 'KHÔNG PHẢI']], 'Mã di truyền có tính đặc hiệu, thoái hóa và phổ biến; di truyền dòng mẹ và trội – lặn không phải đặc điểm của mã.', $d);
        $this->sortQ($s, 'Kéo mỗi quá trình vào nhóm CẦN ENZIM ARN POLIMERAZA hoặc KHÔNG CẦN.', [['Phiên mã', 'CẦN ENZIM ARN POLIMERAZA'], ['Tổng hợp mARN', 'CẦN ENZIM ARN POLIMERAZA'], ['Dịch mã', 'KHÔNG CẦN'], ['Nhân đôi ADN', 'KHÔNG CẦN']], 'Phiên mã tổng hợp mARN cần ARN polimeraza; dịch mã cần riboxom, nhân đôi ADN cần ADN polimeraza.', $d);
    }

    private function seedKhoaHocThpt12Lop123(): void
    {
        $s = 'khoa-hoc-thpt-12-lop-12-3';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi phép lai vào nhóm ĐỜI CON ĐỒNG TÍNH hoặc PHÂN TÍNH.', [['AA × AA', 'ĐỜI CON ĐỒNG TÍNH'], ['AA × aa', 'ĐỜI CON ĐỒNG TÍNH'], ['Aa × Aa', 'PHÂN TÍNH'], ['Aa × aa', 'PHÂN TÍNH']], 'AA × AA và AA × aa cho đời con đồng tính; Aa × Aa phân tính 3:1, Aa × aa phân tính 1:1.', $d);
        $this->sortQ($s, 'Kéo mỗi kiểu gen vào nhóm CHO 1 LOẠI hoặc CHO 2 LOẠI GIAO TỬ.', [['AA', 'CHO 1 LOẠI GIAO TỬ'], ['aa', 'CHO 1 LOẠI GIAO TỬ'], ['Aa', 'CHO 2 LOẠI GIAO TỬ'], ['Bb', 'CHO 2 LOẠI GIAO TỬ']], 'Cơ thể đồng hợp cho 1 loại giao tử, cơ thể dị hợp 1 cặp gen cho 2 loại giao tử.', $d);
    }

    private function seedKhoaHocThpt12Lop124(): void
    {
        $s = 'khoa-hoc-thpt-12-lop-12-4';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi phép lai hai cặp tính trạng vào nhóm ĐỜI CON CÓ 4 KIỂU HÌNH hoặc ÍT HƠN 4.', [['AaBb × AaBb', 'CÓ 4 KIỂU HÌNH'], ['AaBb × aabb', 'CÓ 4 KIỂU HÌNH'], ['AABB × aabb', 'ÍT HƠN 4'], ['AaBB × aaBb', 'ÍT HƠN 4']], 'AaBb × AaBb cho tỉ lệ 9:3:3:1, AaBb × aabb cho 1:1:1:1; các phép lai còn lại cho ít hơn 4 kiểu hình.', $d);
        $this->sortQ($s, 'Kéo mỗi kiểu gen vào nhóm DỊ HỢP 1 CẶP hoặc DỊ HỢP 2 CẶP GEN.', [['Aabb', 'DỊ HỢP 1 CẶP'], ['aaBb', 'DỊ HỢP 1 CẶP'], ['AaBB', 'DỊ HỢP 1 CẶP'], ['AaBb', 'DỊ HỢP 2 CẶP GEN']], 'Aabb, aaBb, AaBB dị hợp 1 cặp gen; AaBb dị hợp 2 cặp gen và cho 4 loại giao tử.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉ lệ kiểu hình vào nhóm CỦA LAI 1 CẶP hoặc CỦA LAI 2 CẶP TÍNH TRẠNG.', [['3 : 1', 'CỦA LAI 1 CẶP'], ['1 : 1', 'CỦA LAI 1 CẶP'], ['9 : 3 : 3 : 1', 'CỦA LAI 2 CẶP TÍNH TRẠNG'], ['1 : 1 : 1 : 1', 'CỦA LAI 2 CẶP TÍNH TRẠNG']], 'Lai 1 cặp tính trạng cho tỉ lệ 3:1 hoặc 1:1; lai 2 cặp tính trạng phân li độc lập cho 9:3:3:1 hoặc 1:1:1:1.', $d);
    }


    private function seedDiaLyThpt10Lop101(): void
    {
        $s = 'dia-ly-thpt-10-lop-10-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi hành tinh vào nhóm CÓ VÀNH ĐAI hoặc KHÔNG CÓ VÀNH ĐAI RÕ.', [['Sao Thổ', 'CÓ VÀNH ĐAI'], ['Sao Thiên Vương', 'CÓ VÀNH ĐAI'], ['Sao Hỏa', 'KHÔNG CÓ VÀNH ĐAI RÕ'], ['Sao Kim', 'KHÔNG CÓ VÀNH ĐAI RÕ']], 'Sao Thổ, sao Thiên Vương có vành đai rõ; sao Hỏa, sao Kim không có vành đai rõ.', $d);
        $this->sortQ($s, 'Kéo mỗi hành tinh vào nhóm CÓ VỆ TINH TỰ NHIÊN hoặc KHÔNG CÓ.', [['Trái Đất', 'CÓ VỆ TINH TỰ NHIÊN'], ['Sao Hỏa', 'CÓ VỆ TINH TỰ NHIÊN'], ['Sao Thủy', 'KHÔNG CÓ'], ['Sao Kim', 'KHÔNG CÓ']], 'Trái Đất có Mặt Trăng, sao Hỏa có 2 vệ tinh nhỏ; sao Thủy và sao Kim không có vệ tinh tự nhiên.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm DO TRÁI ĐẤT TỰ QUAY hoặc DO TRỤC NGHIÊNG.', [['Ngày và đêm luân phiên', 'DO TRÁI ĐẤT TỰ QUAY'], ['Sự chênh lệch giờ trên Trái Đất', 'DO TRÁI ĐẤT TỰ QUAY'], ['Bốn mùa trong năm', 'DO TRỤC NGHIÊNG'], ['Ngày đêm dài ngắn theo mùa', 'DO TRỤC NGHIÊNG']], 'Tự quay gây ra ngày đêm và chênh lệch giờ; trục nghiêng gây ra bốn mùa và ngày đêm dài ngắn theo mùa.', $d);
    }

    private function seedDiaLyThpt10Lop102(): void
    {
        $s = 'dia-ly-thpt-10-lop-10-2';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi dạng địa hình vào nhóm DO PHONG HÓA hoặc DO XÂM THỰC là chính.', [['Đất đá vụn chân núi', 'DO PHONG HÓA'], ['Đá ong', 'DO PHONG HÓA'], ['Thung lũng sông', 'DO XÂM THỰC'], ['Hang động đá vôi', 'DO XÂM THỰC']], 'Phong hóa làm đá vụn ra, tạo đá ong; xâm thực của nước tạo thung lũng sông và hang động đá vôi.', $d);
        $this->sortQ($s, 'Kéo mỗi nơi vào nhóm MẢNG TÁCH GIÃN hoặc MẢNG XÔ HÚC.', [['Sống núi giữa Đại Tây Dương', 'MẢNG TÁCH GIÃN'], ['Vành đai lửa Thái Bình Dương', 'MẢNG XÔ HÚC'], ['Dãy Himalaya', 'MẢNG XÔ HÚC'], ['Thung lũng tách giãn Đông Phi', 'MẢNG TÁCH GIÃN']], 'Sống núi giữa đại dương và thung lũng Đông Phi là nơi mảng tách giãn; vành đai lửa và Himalaya là nơi mảng xô húc.', $d);
        $this->sortQ($s, 'Kéo mỗi quá trình vào nhóm LÀM NÂNG CAO hoặc LÀM HẠ THẤP địa hình.', [['Vận động tạo núi', 'LÀM NÂNG CAO'], ['Núi lửa phun trào', 'LÀM NÂNG CAO'], ['Xói mòn', 'LÀM HẠ THẤP'], ['Phong hóa', 'LÀM HẠ THẤP']], 'Nội lực như tạo núi, núi lửa làm nâng cao địa hình; ngoại lực như xói mòn, phong hóa làm hạ thấp địa hình.', $d);
    }

    private function seedDiaLyThpt10Lop103(): void
    {
        $s = 'dia-ly-thpt-10-lop-10-3';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi đai khí áp vào nhóm ÁP CAO hoặc ÁP THẤP.', [['Đai áp cao cận chí tuyến', 'ÁP CAO'], ['Đai áp cao ở cực', 'ÁP CAO'], ['Đai áp thấp xích đạo', 'ÁP THẤP'], ['Đai áp thấp ôn đới', 'ÁP THẤP']], 'Trên Trái Đất có các đai áp cao ở cận chí tuyến và cực, các đai áp thấp ở xích đạo và ôn đới.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng khí hậu vào nhóm XẢY RA Ở VIỆT NAM hoặc KHÔNG XẢY RA.', [['Gió mùa Đông Bắc', 'XẢY RA Ở VIỆT NAM'], ['Bão nhiệt đới', 'XẢY RA Ở VIỆT NAM'], ['Gió phơn Tây Nam', 'XẢY RA Ở VIỆT NAM'], ['Tuyết rơi dày đặc', 'KHÔNG XẢY RA']], 'Việt Nam có gió mùa Đông Bắc, bão nhiệt đới và gió phơn Tây Nam; tuyết rơi dày đặc hầu như không xảy ra.', $d);
        $this->sortQ($s, 'Kéo mỗi tầng khí quyển vào nhóm CÓ hoặc KHÔNG CÓ hiện tượng thời tiết.', [['Tầng đối lưu', 'CÓ'], ['Tầng bình lưu', 'KHÔNG CÓ'], ['Tầng giữa', 'KHÔNG CÓ'], ['Tầng điện li', 'KHÔNG CÓ']], 'Mọi hiện tượng thời tiết như mây, mưa, bão đều xảy ra ở tầng đối lưu.', $d);
    }

    private function seedDiaLyThpt10Lop104(): void
    {
        $s = 'dia-ly-thpt-10-lop-10-4';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi kiểu thảm thực vật vào nhóm CÓ Ở VIỆT NAM hoặc KHÔNG CÓ.', [['Rừng mưa nhiệt đới', 'CÓ Ở VIỆT NAM'], ['Xa van', 'CÓ Ở VIỆT NAM'], ['Rừng lá kim', 'KHÔNG CÓ'], ['Đài nguyên', 'KHÔNG CÓ']], 'Việt Nam có rừng mưa nhiệt đới và xa van ở Tây Nguyên; rừng lá kim và đài nguyên không có ở Việt Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi đới thiên nhiên vào nhóm CÓ ĐẤT ĐEN hoặc KHÔNG CÓ ĐẤT ĐEN.', [['Thảo nguyên ôn đới', 'CÓ ĐẤT ĐEN'], ['Rừng lá rộng ôn đới', 'KHÔNG CÓ ĐẤT ĐEN'], ['Đài nguyên', 'KHÔNG CÓ ĐẤT ĐEN'], ['Rừng mưa nhiệt đới', 'KHÔNG CÓ ĐẤT ĐEN']], 'Đất đen màu mỡ đặc trưng của thảo nguyên ôn đới; các đới khác không có loại đất này.', $d);
    }


    private function seedDiaLyThpt11Lop111(): void
    {
        $s = 'dia-ly-thpt-11-lop-11-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi châu lục vào nhóm ĐÔNG DÂN NHẤT THẾ GIỚI hoặc KHÔNG PHẢI.', [['Châu Á', 'ĐÔNG DÂN NHẤT THẾ GIỚI'], ['Châu Phi', 'KHÔNG PHẢI'], ['Châu Âu', 'KHÔNG PHẢI'], ['Châu Đại Dương', 'KHÔNG PHẢI']], 'Châu Á chiếm khoảng 60% dân số thế giới, đông dân nhất trong các châu lục.', $d);
    }

    private function seedDiaLyThpt11Lop112(): void
    {
        $s = 'dia-ly-thpt-11-lop-11-2';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi quốc gia vào nhóm DÂN SỐ GIÀ hoặc DÂN SỐ TRẺ.', [['Nhật Bản', 'DÂN SỐ GIÀ'], ['Đức', 'DÂN SỐ GIÀ'], ['Nigeria', 'DÂN SỐ TRẺ'], ['Ấn Độ', 'DÂN SỐ TRẺ']], 'Nhật Bản, Đức có dân số già; Nigeria, Ấn Độ có dân số trẻ với tỉ lệ trẻ em cao.', $d);
        $this->sortQ($s, 'Kéo mỗi luồng di cư vào nhóm DI CƯ TỰ NGUYỆN hoặc DI CƯ BẮT BUỘC.', [['Đi xuất khẩu lao động', 'DI CƯ TỰ NGUYỆN'], ['Chuyển lên thành phố làm việc', 'DI CƯ TỰ NGUYỆN'], ['Chạy lụt', 'DI CƯ BẮT BUỘC'], ['Sơ tán vì chiến tranh', 'DI CƯ BẮT BUỘC']], 'Xuất khẩu lao động, lên thành phố làm việc là tự nguyện; chạy lụt, sơ tán chiến tranh là bắt buộc.', $d);
        $this->sortQ($s, 'Kéo mỗi siêu đô thị vào nhóm Ở CHÂU Á hoặc Ở CHÂU KHÁC.', [['Tokyo', 'Ở CHÂU Á'], ['Delhi', 'Ở CHÂU Á'], ['São Paulo', 'Ở CHÂU KHÁC'], ['New York', 'Ở CHÂU KHÁC']], 'Tokyo, Delhi là siêu đô thị châu Á; São Paulo ở châu Mỹ, New York ở Bắc Mỹ.', $d);
    }

    private function seedDiaLyThpt11Lop113(): void
    {
        $s = 'dia-ly-thpt-11-lop-11-3';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi nước vào nhóm NÔNG NGHIỆP HIỆN ĐẠI hoặc NÔNG NGHIỆP TRUYỀN THỐNG.', [['Hoa Kỳ', 'NÔNG NGHIỆP HIỆN ĐẠI'], ['Israel', 'NÔNG NGHIỆP HIỆN ĐẠI'], ['Lào', 'NÔNG NGHIỆP TRUYỀN THỐNG'], ['Campuchia', 'NÔNG NGHIỆP TRUYỀN THỐNG']], 'Hoa Kỳ, Israel có nông nghiệp hiện đại, năng suất cao; nhiều nước đang phát triển còn nông nghiệp truyền thống.', $d);
        $this->sortQ($s, 'Kéo mỗi ngành vào nhóm CÔNG NGHIỆP KHAI THÁC hoặc CÔNG NGHIỆP CHẾ BIẾN.', [['Khai thác than', 'CÔNG NGHIỆP KHAI THÁC'], ['Khai thác dầu khí', 'CÔNG NGHIỆP KHAI THÁC'], ['Dệt may', 'CÔNG NGHIỆP CHẾ BIẾN'], ['Chế biến thực phẩm', 'CÔNG NGHIỆP CHẾ BIẾN']], 'Khai thác than, dầu khí thuộc công nghiệp khai thác; dệt may, chế biến thực phẩm thuộc công nghiệp chế biến.', $d);
        $this->sortQ($s, 'Kéo mỗi sản phẩm vào nhóm CỦA NÔNG NGHIỆP NHIỆT ĐỚI hoặc ÔN ĐỚI.', [['Cà phê', 'CỦA NÔNG NGHIỆP NHIỆT ĐỚI'], ['Cao su', 'CỦA NÔNG NGHIỆP NHIỆT ĐỚI'], ['Lúa mì', 'CỦA NÔNG NGHIỆP ÔN ĐỚI'], ['Táo', 'CỦA NÔNG NGHIỆP ÔN ĐỚI']], 'Cà phê, cao su là cây nhiệt đới; lúa mì, táo là cây ôn đới.', $d);
    }

    private function seedDiaLyThpt11Lop114(): void
    {
        $s = 'dia-ly-thpt-11-lop-11-4';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm DỊCH VỤ KINH DOANH hoặc DỊCH VỤ TIÊU DÙNG.', [['Ngân hàng', 'DỊCH VỤ KINH DOANH'], ['Bảo hiểm', 'DỊCH VỤ KINH DOANH'], ['Du lịch', 'DỊCH VỤ TIÊU DÙNG'], ['Giáo dục', 'DỊCH VỤ TIÊU DÙNG']], 'Ngân hàng, bảo hiểm phục vụ kinh doanh; du lịch, giáo dục phục vụ trực tiếp người tiêu dùng.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ chức vào nhóm PHẠM VI TOÀN CẦU hoặc PHẠM VI KHU VỰC.', [['WTO', 'PHẠM VI TOÀN CẦU'], ['Liên Hợp Quốc', 'PHẠM VI TOÀN CẦU'], ['ASEAN', 'PHẠM VI KHU VỰC'], ['EU', 'PHẠM VI KHU VỰC']], 'WTO, Liên Hợp Quốc hoạt động toàn cầu; ASEAN, EU là tổ chức khu vực.', $d);
        $this->sortQ($s, 'Kéo mỗi biểu hiện vào nhóm CỦA TOÀN CẦU HÓA KINH TẾ hoặc KHÔNG PHẢI.', [['Thương mại tự do', 'CỦA TOÀN CẦU HÓA KINH TẾ'], ['Đầu tư xuyên quốc gia', 'CỦA TOÀN CẦU HÓA KINH TẾ'], ['Tự cung tự cấp', 'KHÔNG PHẢI'], ['Đóng cửa biên giới', 'KHÔNG PHẢI']], 'Thương mại tự do, đầu tư xuyên quốc gia là biểu hiện của toàn cầu hóa kinh tế.', $d);
    }


    private function seedDiaLyThpt12Lop121(): void
    {
        $s = 'dia-ly-thpt-12-lop-12-1';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm CỦA VỊ TRÍ ĐỊA LÍ hoặc CỦA ĐỊA HÌNH Việt Nam.', [['Nằm ở rìa phía đông bán đảo Đông Dương', 'CỦA VỊ TRÍ ĐỊA LÍ'], ['Nằm trong vùng nội chí tuyến', 'CỦA VỊ TRÍ ĐỊA LÍ'], ['Đồi núi chiếm 3/4 diện tích', 'CỦA ĐỊA HÌNH'], ['Địa hình thấp dần từ tây bắc xuống đông nam', 'CỦA ĐỊA HÌNH']], 'Vị trí địa lí: rìa đông bán đảo Đông Dương, trong vùng nội chí tuyến; địa hình: đồi núi chiếm 3/4, thấp dần từ tây bắc xuống đông nam.', $d);
        $this->sortQ($s, 'Kéo mỗi vùng vào nhóm Ở PHÍA ĐÔNG hoặc Ở PHÍA TÂY dãy Trường Sơn.', [['Duyên hải miền Trung', 'Ở PHÍA ĐÔNG'], ['Đồng bằng sông Cửu Long', 'Ở PHÍA ĐÔNG'], ['Đông Nam Bộ', 'Ở PHÍA ĐÔNG'], ['Tây Nguyên', 'Ở PHÍA TÂY']], 'Dãy Trường Sơn là ranh giới: phía đông là duyên hải miền Trung, Đông Nam Bộ; phía tây là Tây Nguyên.', $d);
    }

    private function seedDiaLyThpt12Lop122(): void
    {
        $s = 'dia-ly-thpt-12-lop-12-2';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi loại đất vào nhóm ĐẤT PHÙ SA hoặc ĐẤT FERALIT.', [['Đất phù sa sông Hồng', 'ĐẤT PHÙ SA'], ['Đất phù sa sông Cửu Long', 'ĐẤT PHÙ SA'], ['Đất đỏ ba-dan Tây Nguyên', 'ĐẤT FERALIT'], ['Đất xám bạc màu', 'ĐẤT FERALIT']], 'Đất phù sa ở các đồng bằng châu thổ; đất feralit gồm đất đỏ ba-dan, đất xám bạc màu ở đồi núi.', $d);
    }

    private function seedDiaLyThpt12Lop123(): void
    {
        $s = 'dia-ly-thpt-12-lop-12-3';
        $d = 'trung_binh';

        $this->sortQ($s, 'Kéo mỗi vùng vào nhóm TRỒNG LÚA NHIỀU NHẤT hoặc KHÔNG PHẢI.', [['Đồng bằng sông Cửu Long', 'TRỒNG LÚA NHIỀU NHẤT'], ['Đồng bằng sông Hồng', 'KHÔNG PHẢI'], ['Tây Nguyên', 'KHÔNG PHẢI'], ['Duyên hải miền Trung', 'KHÔNG PHẢI']], 'Đồng bằng sông Cửu Long là vựa lúa lớn nhất cả nước, chiếm hơn một nửa sản lượng lúa.', $d);
        $this->sortQ($s, 'Kéo mỗi sản phẩm vào nhóm XUẤT KHẨU CHỦ LỰC hoặc KHÔNG PHẢI.', [['Gạo', 'XUẤT KHẨU CHỦ LỰC'], ['Cà phê', 'XUẤT KHẨU CHỦ LỰC'], ['Ngô', 'KHÔNG PHẢI'], ['Khoai lang', 'KHÔNG PHẢI']], 'Gạo và cà phê là hai mặt hàng nông sản xuất khẩu chủ lực của Việt Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi vật nuôi vào nhóm CHĂN NUÔI LẤY THỊT hoặc LẤY SỮA.', [['Lợn', 'CHĂN NUÔI LẤY THỊT'], ['Gà', 'CHĂN NUÔI LẤY THỊT'], ['Bò sữa', 'CHĂN NUÔI LẤY SỮA'], ['Dê sữa', 'CHĂN NUÔI LẤY SỮA']], 'Lợn, gà chăn nuôi lấy thịt; bò sữa, dê sữa chăn nuôi lấy sữa.', $d);
    }

    private function seedDiaLyThpt12Lop124(): void
    {
        $s = 'dia-ly-thpt-12-lop-12-4';
        $d = 'kho';

        $this->sortQ($s, 'Kéo mỗi trung tâm công nghiệp vào nhóm Ở MIỀN BẮC hoặc Ở MIỀN NAM.', [['Hà Nội', 'Ở MIỀN BẮC'], ['Hải Phòng', 'Ở MIỀN BẮC'], ['TP. Hồ Chí Minh', 'Ở MIỀN NAM'], ['Cần Thơ', 'Ở MIỀN NAM']], 'Hà Nội, Hải Phòng là trung tâm công nghiệp miền Bắc; TP. Hồ Chí Minh, Cần Thơ là trung tâm công nghiệp miền Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi loại hình vào nhóm DU LỊCH BIỂN hoặc DU LỊCH VĂN HÓA.', [['Nghỉ dưỡng ở Phú Quốc', 'DU LỊCH BIỂN'], ['Tắm biển ở Nha Trang', 'DU LỊCH BIỂN'], ['Tham quan phố cổ Hội An', 'DU LỊCH VĂN HÓA'], ['Thăm cố đô Huế', 'DU LỊCH VĂN HÓA']], 'Phú Quốc, Nha Trang là du lịch biển; Hội An, Huế là du lịch văn hóa.', $d);
    }

}
