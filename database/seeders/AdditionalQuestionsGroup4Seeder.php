<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdditionalQuestionsGroup4Seeder extends Seeder
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
        // Địa lý
        $this->seedDlCacChauLuc(); $this->seedDlDaiDuong(); $this->seedDlViTriVietNam(); $this->seedDlThuDoChauA();
        $this->seedDlThuDoTheGioi(); $this->seedDlDiaHinhSongNgoi(); $this->seedDlKhiHau(); $this->seedDlChauLucDaiDuongLop61();
        $this->seedDlChauLucDaiDuongLop62(); $this->seedDlChauLucDaiDuongLop71(); $this->seedDlChauLucDaiDuongLop72();
        $this->seedDlThuDoCacNuocLop71(); $this->seedDlThuDoCacNuocLop72(); $this->seedDlThuDoCacNuocLop81();
        $this->seedDlThuDoCacNuocLop82(); $this->seedDlDiaHinhVietNamLop81(); $this->seedDlDiaHinhVietNamLop82();
        $this->seedDlDiaHinhVietNamLop91(); $this->seedDlDiaHinhVietNamLop92(); $this->seedDiaLyThpt10Lop101();
        $this->seedDiaLyThpt10Lop102(); $this->seedDiaLyThpt10Lop103(); $this->seedDiaLyThpt10Lop104();
        $this->seedDiaLyThpt11Lop111(); $this->seedDiaLyThpt11Lop112(); $this->seedDiaLyThpt11Lop113();
        $this->seedDiaLyThpt11Lop114(); $this->seedDiaLyThpt12Lop121(); $this->seedDiaLyThpt12Lop122();
        $this->seedDiaLyThpt12Lop123(); $this->seedDiaLyThpt12Lop124();
        // Khoa học
        $this->seedKhHeXuong(); $this->seedKhHeTieuHoa(); $this->seedKhTrangThaiChat(); $this->seedKhNuoc();
        $this->seedKhNguonNangLuong(); $this->seedKhDien(); $this->seedKhCoTheNguoiLop61(); $this->seedKhCoTheNguoiLop62();
        $this->seedKhCoTheNguoiLop71(); $this->seedKhCoTheNguoiLop72(); $this->seedKhChatQuanhTaLop71();
        $this->seedKhChatQuanhTaLop72(); $this->seedKhChatQuanhTaLop81(); $this->seedKhChatQuanhTaLop82();
        $this->seedKhNangLuongLop81(); $this->seedKhNangLuongLop82(); $this->seedKhNangLuongLop91();
        $this->seedKhNangLuongLop92(); $this->seedKhoaHocThpt10Lop101(); $this->seedKhoaHocThpt10Lop102();
        $this->seedKhoaHocThpt10Lop103(); $this->seedKhoaHocThpt10Lop104(); $this->seedKhoaHocThpt11Lop111();
        $this->seedKhoaHocThpt11Lop112(); $this->seedKhoaHocThpt11Lop113(); $this->seedKhoaHocThpt11Lop114();
        $this->seedKhoaHocThpt12Lop121(); $this->seedKhoaHocThpt12Lop122(); $this->seedKhoaHocThpt12Lop123();
        $this->seedKhoaHocThpt12Lop124();
        // Lịch sử
        $this->seedLsNuocVanLang(); $this->seedLsAnhHungDanToc(); $this->seedLsDinhBoLinh(); $this->seedLsLeHoan();
        $this->seedLsDongBoDau(); $this->seedLsTranHungDao(); $this->seedLsDungNuocLop61(); $this->seedLsDungNuocLop62();
        $this->seedLsDungNuocLop71(); $this->seedLsDungNuocLop72(); $this->seedLsDinhTienLeLop71();
        $this->seedLsDinhTienLeLop72(); $this->seedLsDinhTienLeLop81(); $this->seedLsDinhTienLeLop82();
        $this->seedLsChongNguyenMongLop81(); $this->seedLsChongNguyenMongLop82(); $this->seedLsChongNguyenMongLop91();
        $this->seedLsChongNguyenMongLop92(); $this->seedLichSuThpt10Lop101(); $this->seedLichSuThpt10Lop102();
        $this->seedLichSuThpt10Lop103(); $this->seedLichSuThpt10Lop104(); $this->seedLichSuThpt11Lop111();
        $this->seedLichSuThpt11Lop112(); $this->seedLichSuThpt11Lop113(); $this->seedLichSuThpt11Lop114();
        $this->seedLichSuThpt12Lop121(); $this->seedLichSuThpt12Lop122(); $this->seedLichSuThpt12Lop123();
        $this->seedLichSuThpt12Lop124();
    }

    private function seedDlCacChauLuc(): void
    {
        $s = 'dl-cac-chau-luc'; $d = 'de';
        $this->quiz($s, 'Châu lục nào có diện tích nhỏ nhất thế giới?', ['Châu Á', 'Châu Phi', 'Châu Đại Dương', 'Châu Âu'], 2, 'Châu Đại Dương là châu lục có diện tích nhỏ nhất thế giới.', $d);
        $this->quiz($s, 'Châu lục nào KHÔNG có dân cư sinh sống thường xuyên?', ['Châu Âu', 'Châu Nam Cực', 'Châu Phi', 'Châu Đại Dương'], 1, 'Châu Nam Cực quá lạnh nên không có dân cư sinh sống thường xuyên, chỉ có các trạm nghiên cứu khoa học.', $d);
        $this->quiz($s, 'Trên thế giới có tất cả bao nhiêu châu lục?', ['5', '6', '7', '8'], 2, 'Thế giới có 7 châu lục: Á, Âu, Phi, Bắc Mỹ, Nam Mỹ, Đại Dương và Nam Cực.', $d);
        $this->quiz($s, 'Hoang mạc nóng lớn nhất thế giới Xa-ha-ra nằm ở châu lục nào?', ['Châu Á', 'Châu Phi', 'Châu Đại Dương', 'Bắc Mỹ'], 1, 'Hoang mạc Xa-ha-ra nằm ở phía bắc châu Phi, là hoang mạc nóng lớn nhất thế giới.', $d);
        $this->quiz($s, 'Châu Âu và châu Á cùng nằm trên một lục địa có tên là gì?', ['Lục địa Phi – Âu', 'Lục địa Á – Âu', 'Lục địa Mỹ', 'Lục địa Nam Cực'], 1, 'Châu Âu và châu Á cùng nằm trên lục địa Á – Âu, lục địa lớn nhất thế giới.', $d);
        $this->matching($s, 'Nối mỗi châu lục với một quốc gia tiêu biểu của nó.', [['Châu Á', 'Việt Nam'], ['Châu Phi', 'Ai Cập'], ['Châu Âu', 'Pháp'], ['Châu Mỹ', 'Bra-xin']], 'Mỗi châu lục đều có những quốc gia tiêu biểu: Việt Nam ở châu Á, Ai Cập ở châu Phi, Pháp ở châu Âu, Bra-xin ở châu Mỹ.', $d);
        $this->matching($s, 'Nối mỗi châu lục với đặc điểm diện tích của nó.', [['Châu Á', 'Diện tích lớn nhất'], ['Châu Đại Dương', 'Diện tích nhỏ nhất'], ['Châu Phi', 'Có hoang mạc Xa-ha-ra'], ['Châu Nam Cực', 'Lạnh nhất, phủ băng tuyết']], 'Châu Á lớn nhất, châu Đại Dương nhỏ nhất, châu Phi có hoang mạc Xa-ha-ra, châu Nam Cực lạnh nhất.', $d);
        $this->matching($s, 'Nối mỗi châu lục với địa danh nổi tiếng của nó.', [['Châu Á', 'Đỉnh Ê-vơ-rét'], ['Châu Phi', 'Sông Nin'], ['Châu Mỹ', 'Rừng A-ma-dôn'], ['Châu Âu', 'Dãy An-pơ']], 'Mỗi châu lục có địa danh nổi tiếng riêng: Ê-vơ-rét ở châu Á, sông Nin ở châu Phi, rừng A-ma-dôn ở châu Mỹ, dãy An-pơ ở châu Âu.', $d);
        $this->matching($s, 'Nối mỗi châu lục với đặc điểm dân cư của nó.', [['Châu Á', 'Đông dân nhất'], ['Châu Đại Dương', 'Ít dân nhất'], ['Châu Nam Cực', 'Không có dân cư thường trú'], ['Châu Phi', 'Tỉ lệ sinh cao']], 'Châu Á đông dân nhất, châu Đại Dương ít dân nhất, châu Nam Cực không có dân cư thường trú.', $d);
        $this->matching($s, 'Nối mỗi châu lục với lục địa mà nó nằm trên.', [['Châu Á', 'Lục địa Á – Âu'], ['Châu Âu', 'Lục địa Á – Âu'], ['Châu Phi', 'Lục địa Phi'], ['Châu Mỹ', 'Lục địa Mỹ']], 'Châu Á và châu Âu cùng nằm trên lục địa Á – Âu; châu Phi nằm trên lục địa Phi; châu Mỹ nằm trên lục địa Mỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi châu lục vào nhóm có DIỆN TÍCH TRÊN 20 TRIỆU km² hoặc DƯỚI 20 TRIỆU km².', [['Châu Á', 'TRÊN 20 TRIỆU km²'], ['Châu Phi', 'TRÊN 20 TRIỆU km²'], ['Bắc Mỹ', 'TRÊN 20 TRIỆU km²'], ['Nam Mỹ', 'DƯỚI 20 TRIỆU km²'], ['Châu Âu', 'DƯỚI 20 TRIỆU km²'], ['Châu Đại Dương', 'DƯỚI 20 TRIỆU km²']], 'Châu Á, châu Phi và Bắc Mỹ có diện tích trên 20 triệu km²; các châu còn lại nhỏ hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi quốc gia vào nhóm THUỘC CHÂU PHI hoặc KHÔNG THUỘC CHÂU PHI.', [['Ai Cập', 'THUỘC CHÂU PHI'], ['Ni-giê-ri-a', 'THUỘC CHÂU PHI'], ['Nhật Bản', 'KHÔNG THUỘC CHÂU PHI'], ['Bra-xin', 'KHÔNG THUỘC CHÂU PHI']], 'Ai Cập và Ni-giê-ri-a thuộc châu Phi; Nhật Bản thuộc châu Á, Bra-xin thuộc châu Mỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi châu lục vào nhóm CÓ ĐƯỜNG XÍCH ĐẠO ĐI QUA hoặc KHÔNG.', [['Châu Á', 'CÓ ĐƯỜNG XÍCH ĐẠO ĐI QUA'], ['Châu Phi', 'CÓ ĐƯỜNG XÍCH ĐẠO ĐI QUA'], ['Nam Mỹ', 'CÓ ĐƯỜNG XÍCH ĐẠO ĐI QUA'], ['Châu Âu', 'KHÔNG CÓ XÍCH ĐẠO ĐI QUA'], ['Bắc Mỹ', 'KHÔNG CÓ XÍCH ĐẠO ĐI QUA'], ['Châu Đại Dương', 'KHÔNG CÓ XÍCH ĐẠO ĐI QUA']], 'Đường xích đạo đi qua châu Á, châu Phi và Nam Mỹ; không đi qua châu Âu, Bắc Mỹ và châu Đại Dương.', $d);
        $this->sortQ($s, 'Kéo mỗi châu lục vào nhóm CHỦ YẾU Ở BÁN CẦU ĐÔNG hoặc BÁN CẦU TÂY.', [['Châu Á', 'BÁN CẦU ĐÔNG'], ['Châu Phi', 'BÁN CẦU ĐÔNG'], ['Châu Âu', 'BÁN CẦU ĐÔNG'], ['Châu Đại Dương', 'BÁN CẦU ĐÔNG'], ['Bắc Mỹ', 'BÁN CẦU TÂY'], ['Nam Mỹ', 'BÁN CẦU TÂY']], 'Châu Á, Phi, Âu và Đại Dương chủ yếu ở bán cầu Đông; châu Mỹ chủ yếu ở bán cầu Tây.', $d);
        $this->sortQ($s, 'Kéo mỗi nơi vào nhóm CÓ NGƯỜI SINH SỐNG THƯỜNG XUYÊN hoặc KHÔNG.', [['Việt Nam', 'CÓ NGƯỜI SINH SỐNG'], ['Ai Cập', 'CÓ NGƯỜI SINH SỐNG'], ['Pháp', 'CÓ NGƯỜI SINH SỐNG'], ['Châu Nam Cực', 'KHÔNG CÓ NGƯỜI SINH SỐNG']], 'Hầu hết các châu lục đều có người sinh sống, trừ châu Nam Cực quá lạnh giá.', $d);
        $this->fill($s, 'Trên thế giới có tất cả ___ châu lục.', [[0, '7']], 'Thế giới có 7 châu lục: Á, Âu, Phi, Bắc Mỹ, Nam Mỹ, Đại Dương và Nam Cực.', $d);
        $this->fill($s, 'Châu lục nhỏ nhất thế giới là châu Đại ___.', [[0, 'Dương']], 'Châu Đại Dương là châu lục có diện tích nhỏ nhất thế giới.', $d);
        $this->fill($s, 'Châu ___ là châu lục duy nhất không có dân cư sinh sống thường xuyên.', [[0, 'Nam Cực']], 'Châu Nam Cực quanh năm băng giá nên không có dân cư sinh sống thường xuyên.', $d);
        $this->fill($s, 'Hoang mạc nóng lớn nhất thế giới Xa-ha-ra nằm ở châu ___.', [[0, 'Phi']], 'Hoang mạc Xa-ha-ra nằm ở phía bắc châu Phi.', $d);
        $this->fill($s, 'Châu Âu và châu Á cùng nằm trên lục địa Á – ___.', [[0, 'Âu']], 'Lục địa Á – Âu là lục địa lớn nhất thế giới, gồm châu Á và châu Âu.', $d);
    }

    private function seedDlDaiDuong(): void
    {
        $s = 'dl-dai-duong'; $d = 'de';
        $this->quiz($s, 'Rãnh biển sâu nhất thế giới Ma-ri-an nằm ở đại dương nào?', ['Đại Tây Dương', 'Ấn Độ Dương', 'Thái Bình Dương', 'Bắc Băng Dương'], 2, 'Rãnh Ma-ri-an sâu hơn 11 000 m nằm ở phía tây Thái Bình Dương.', $d);
        $this->quiz($s, 'Đại dương nào nằm giữa châu Mỹ và châu Âu – châu Phi?', ['Thái Bình Dương', 'Ấn Độ Dương', 'Bắc Băng Dương', 'Đại Tây Dương'], 3, 'Đại Tây Dương nằm giữa châu Mỹ ở phía tây và châu Âu – châu Phi ở phía đông.', $d);
        $this->quiz($s, 'Đại dương nào bao quanh châu Nam Cực?', ['Ấn Độ Dương', 'Đại Tây Dương', 'Nam Đại Dương', 'Bắc Băng Dương'], 2, 'Nam Đại Dương là vùng biển bao quanh châu Nam Cực.', $d);
        $this->quiz($s, 'Đại dương nào lớn thứ hai thế giới?', ['Ấn Độ Dương', 'Đại Tây Dương', 'Nam Đại Dương', 'Bắc Băng Dương'], 1, 'Đại Tây Dương là đại dương lớn thứ hai thế giới, sau Thái Bình Dương.', $d);
        $this->quiz($s, 'Đại dương nào nằm hoàn toàn ở vùng cực Bắc?', ['Thái Bình Dương', 'Đại Tây Dương', 'Ấn Độ Dương', 'Bắc Băng Dương'], 3, 'Bắc Băng Dương nằm quanh Bắc Cực, là đại dương nhỏ nhất thế giới.', $d);
        $this->matching($s, 'Nối mỗi đại dương với thứ tự diện tích của nó.', [['Thái Bình Dương', 'Lớn nhất'], ['Đại Tây Dương', 'Lớn thứ hai'], ['Ấn Độ Dương', 'Lớn thứ ba'], ['Bắc Băng Dương', 'Nhỏ nhất']], 'Thứ tự diện tích: Thái Bình Dương lớn nhất, rồi đến Đại Tây Dương, Ấn Độ Dương, nhỏ nhất là Bắc Băng Dương.', $d);
        $this->matching($s, 'Nối mỗi đại dương với vị trí của nó.', [['Thái Bình Dương', 'Phía đông châu Á'], ['Đại Tây Dương', 'Giữa châu Mỹ và châu Âu – Phi'], ['Ấn Độ Dương', 'Phía nam châu Á'], ['Bắc Băng Dương', 'Quanh Bắc Cực']], 'Thái Bình Dương ở phía đông châu Á, Đại Tây Dương giữa châu Mỹ và châu Âu – Phi, Ấn Độ Dương ở phía nam châu Á.', $d);
        $this->matching($s, 'Nối tên tiếng Anh với tên tiếng Việt của mỗi đại dương.', [['Pacific Ocean', 'Thái Bình Dương'], ['Atlantic Ocean', 'Đại Tây Dương'], ['Indian Ocean', 'Ấn Độ Dương'], ['Arctic Ocean', 'Bắc Băng Dương']], 'Tên tiếng Anh của các đại dương: Pacific – Thái Bình Dương, Atlantic – Đại Tây Dương, Indian – Ấn Độ Dương, Arctic – Bắc Băng Dương.', $d);
        $this->matching($s, 'Nối mỗi đại dương với đặc điểm riêng của nó.', [['Thái Bình Dương', 'Có rãnh Ma-ri-an sâu nhất'], ['Đại Tây Dương', 'Nối châu Âu với châu Mỹ'], ['Ấn Độ Dương', 'Chịu ảnh hưởng mạnh của gió mùa'], ['Nam Đại Dương', 'Bao quanh châu Nam Cực']], 'Mỗi đại dương có đặc điểm riêng: rãnh Ma-ri-an ở Thái Bình Dương, Nam Đại Dương bao quanh Nam Cực.', $d);
        $this->matching($s, 'Nối mỗi đại dương với một quốc gia giáp nó.', [['Thái Bình Dương', 'Việt Nam'], ['Đại Tây Dương', 'Bra-xin'], ['Ấn Độ Dương', 'Ấn Độ'], ['Bắc Băng Dương', 'Nga']], 'Việt Nam giáp Thái Bình Dương, Bra-xin giáp Đại Tây Dương, Ấn Độ giáp Ấn Độ Dương, Nga giáp Bắc Băng Dương.', $d);
        $this->sortQ($s, 'Kéo mỗi đại dương vào nhóm GIÁP CHÂU Á hoặc KHÔNG GIÁP CHÂU Á.', [['Thái Bình Dương', 'GIÁP CHÂU Á'], ['Ấn Độ Dương', 'GIÁP CHÂU Á'], ['Bắc Băng Dương', 'GIÁP CHÂU Á'], ['Đại Tây Dương', 'KHÔNG GIÁP CHÂU Á'], ['Nam Đại Dương', 'KHÔNG GIÁP CHÂU Á']], 'Thái Bình Dương, Ấn Độ Dương và Bắc Băng Dương đều giáp châu Á.', $d);
        $this->sortQ($s, 'Kéo mỗi vùng biển vào nhóm THUỘC THÁI BÌNH DƯƠNG hoặc KHÔNG.', [['Biển Đông', 'THUỘC THÁI BÌNH DƯƠNG'], ['Biển Nhật Bản', 'THUỘC THÁI BÌNH DƯƠNG'], ['Biển San Hô', 'THUỘC THÁI BÌNH DƯƠNG'], ['Địa Trung Hải', 'KHÔNG THUỘC'], ['Biển Ca-ri-bê', 'KHÔNG THUỘC']], 'Biển Đông, biển Nhật Bản và biển San Hô thuộc Thái Bình Dương; Địa Trung Hải và biển Ca-ri-bê thuộc Đại Tây Dương.', $d);
        $this->sortQ($s, 'Kéo mỗi đại dương vào nhóm DIỆN TÍCH TRÊN 70 TRIỆU km² hoặc DƯỚI 70 TRIỆU km².', [['Thái Bình Dương', 'TRÊN 70 TRIỆU km²'], ['Đại Tây Dương', 'TRÊN 70 TRIỆU km²'], ['Ấn Độ Dương', 'TRÊN 70 TRIỆU km²'], ['Nam Đại Dương', 'DƯỚI 70 TRIỆU km²'], ['Bắc Băng Dương', 'DƯỚI 70 TRIỆU km²']], 'Ba đại dương lớn (Thái Bình, Đại Tây, Ấn Độ) đều trên 70 triệu km²; Nam Đại Dương và Bắc Băng Dương nhỏ hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi đảo vào nhóm Ở THÁI BÌNH DƯƠNG hoặc Ở ĐẠI DƯƠNG KHÁC.', [['Phú Quốc', 'Ở THÁI BÌNH DƯƠNG'], ['Hoàng Sa', 'Ở THÁI BÌNH DƯƠNG'], ['Trường Sa', 'Ở THÁI BÌNH DƯƠNG'], ['Ma-đa-gát-xca', 'Ở ĐẠI DƯƠNG KHÁC'], ['Ai-xơ-len', 'Ở ĐẠI DƯƠNG KHÁC']], 'Các đảo của Việt Nam đều ở Thái Bình Dương; Ma-đa-gát-xca ở Ấn Độ Dương, Ai-xơ-len ở Đại Tây Dương.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Thái Bình Dương là đại dương lớn nhất', 'ĐÚNG'], ['Biển Đông thuộc Thái Bình Dương', 'ĐÚNG'], ['Bắc Băng Dương nằm quanh Nam Cực', 'SAI'], ['Đại Tây Dương nằm giữa châu Á và châu Mỹ', 'SAI']], 'Thái Bình Dương lớn nhất và Biển Đông thuộc nó là đúng; Bắc Băng Dương ở quanh Bắc Cực, Đại Tây Dương nằm giữa châu Mỹ và châu Âu – Phi.', $d);
        $this->fill($s, 'Rãnh biển sâu nhất thế giới Ma-ri-an nằm ở ___ Bình Dương.', [[0, 'Thái']], 'Rãnh Ma-ri-an nằm ở phía tây Thái Bình Dương, sâu hơn 11 000 m.', $d);
        $this->fill($s, 'Đại dương lớn thứ hai thế giới là Đại Tây ___.', [[0, 'Dương']], 'Đại Tây Dương lớn thứ hai thế giới, sau Thái Bình Dương.', $d);
        $this->fill($s, 'Đại dương nằm giữa châu Phi và châu Mỹ là Đại Tây ___.', [[0, 'Dương']], 'Đại Tây Dương nằm giữa châu Mỹ ở phía tây và châu Âu – châu Phi ở phía đông.', $d);
        $this->fill($s, 'Bắc Băng Dương là đại dương ___ nhất thế giới.', [[0, 'nhỏ']], 'Bắc Băng Dương nằm quanh Bắc Cực, là đại dương nhỏ nhất thế giới.', $d);
        $this->fill($s, 'Ấn Độ Dương nằm ở phía ___ của châu Á.', [[0, 'nam']], 'Ấn Độ Dương nằm ở phía nam châu Á, chịu ảnh hưởng mạnh của gió mùa.', $d);
    }

    private function seedDlViTriVietNam(): void
    {
        $s = 'dl-vi-tri-viet-nam'; $d = 'trung_binh';
        $this->quiz($s, 'Việt Nam nằm ở khu vực nào của châu Á?', ['Đông Bắc Á', 'Đông Nam Á', 'Nam Á', 'Tây Á'], 1, 'Việt Nam nằm ở phía đông bán đảo Đông Dương, thuộc khu vực Đông Nam Á.', $d);
        $this->quiz($s, 'Việt Nam có chung biên giới trên đất liền với bao nhiêu nước?', ['2', '3', '4', '5'], 1, 'Việt Nam có chung biên giới đất liền với 3 nước: Trung Quốc, Lào và Cam-pu-chia.', $d);
        $this->quiz($s, 'Nước nào có đường biên giới trên đất liền dài nhất với Việt Nam?', ['Trung Quốc', 'Lào', 'Cam-pu-chia', 'Thái Lan'], 1, 'Lào là nước có đường biên giới trên đất liền dài nhất với Việt Nam.', $d);
        $this->quiz($s, 'Điểm cực Bắc của Việt Nam thuộc tỉnh nào?', ['Lào Cai', 'Hà Giang', 'Cao Bằng', 'Điện Biên'], 1, 'Điểm cực Bắc của nước ta ở Lũng Cú, tỉnh Hà Giang.', $d);
        $this->quiz($s, 'Quần đảo Trường Sa do tỉnh nào quản lí hành chính?', ['Khánh Hòa', 'Đà Nẵng', 'Bình Định', 'Phú Yên'], 0, 'Quần đảo Trường Sa do tỉnh Khánh Hòa quản lí hành chính.', $d);
        $this->matching($s, 'Nối mỗi nước láng giềng với vị trí tương đối so với Việt Nam.', [['Trung Quốc', 'Phía bắc'], ['Lào', 'Phía tây'], ['Cam-pu-chia', 'Phía tây nam']], 'Trung Quốc ở phía bắc, Lào ở phía tây, Cam-pu-chia ở phía tây nam của Việt Nam.', $d);
        $this->matching($s, 'Nối mỗi điểm cực của Việt Nam với tỉnh của nó.', [['Cực Bắc', 'Hà Giang'], ['Cực Tây', 'Điện Biên'], ['Cực Nam', 'Cà Mau'], ['Cực Đông', 'Khánh Hòa']], 'Bốn điểm cực: Bắc – Hà Giang, Tây – Điện Biên, Nam – Cà Mau, Đông – Khánh Hòa.', $d);
        $this->matching($s, 'Nối mỗi thành phố với đặc điểm của nó.', [['Hà Nội', 'Thủ đô'], ['TP. Hồ Chí Minh', 'Đông dân nhất'], ['Hải Phòng', 'Cảng biển lớn miền Bắc'], ['Đà Nẵng', 'Thành phố biển miền Trung']], 'Hà Nội là thủ đô, TP. Hồ Chí Minh đông dân nhất, Hải Phòng là cảng lớn miền Bắc, Đà Nẵng là thành phố biển miền Trung.', $d);
        $this->matching($s, 'Nối mỗi vùng với thành phố trung tâm của nó.', [['Đồng bằng sông Hồng', 'Hà Nội'], ['Duyên hải miền Trung', 'Đà Nẵng'], ['Tây Nguyên', 'Buôn Ma Thuột'], ['Nam Bộ', 'TP. Hồ Chí Minh']], 'Mỗi vùng có trung tâm riêng: Hà Nội, Đà Nẵng, Buôn Ma Thuột và TP. Hồ Chí Minh.', $d);
        $this->matching($s, 'Nối mỗi đảo, quần đảo với vùng biển của nó.', [['Hoàng Sa', 'Biển miền Trung'], ['Trường Sa', 'Biển Đông Nam Bộ'], ['Phú Quốc', 'Vịnh Thái Lan'], ['Côn Đảo', 'Biển Đông Nam Bộ']], 'Hoàng Sa ở biển miền Trung, Trường Sa và Côn Đảo ở biển Đông Nam Bộ, Phú Quốc ở vịnh Thái Lan.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉnh vào nhóm GIÁP BIỂN hoặc KHÔNG GIÁP BIỂN.', [['Quảng Ninh', 'GIÁP BIỂN'], ['Khánh Hòa', 'GIÁP BIỂN'], ['Điện Biên', 'KHÔNG GIÁP BIỂN'], ['Hà Giang', 'KHÔNG GIÁP BIỂN']], 'Quảng Ninh, Khánh Hòa giáp biển; Điện Biên, Hà Giang là tỉnh miền núi không giáp biển.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm Ở MIỀN TRUNG hoặc KHÔNG.', [['Huế', 'Ở MIỀN TRUNG'], ['Đà Nẵng', 'Ở MIỀN TRUNG'], ['Hà Nội', 'KHÔNG Ở MIỀN TRUNG'], ['Cần Thơ', 'KHÔNG Ở MIỀN TRUNG']], 'Huế và Đà Nẵng ở miền Trung; Hà Nội ở miền Bắc, Cần Thơ ở miền Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉnh vào nhóm CÓ BIÊN GIỚI ĐẤT LIỀN hoặc KHÔNG.', [['Lạng Sơn', 'CÓ BIÊN GIỚI ĐẤT LIỀN'], ['Quảng Trị', 'CÓ BIÊN GIỚI ĐẤT LIỀN'], ['Kon Tum', 'CÓ BIÊN GIỚI ĐẤT LIỀN'], ['Ninh Bình', 'KHÔNG CÓ'], ['Hải Dương', 'KHÔNG CÓ']], 'Lạng Sơn, Quảng Trị, Kon Tum có biên giới đất liền; Ninh Bình, Hải Dương không có.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phố vào nhóm THÀNH PHỐ TRỰC THUỘC TRUNG ƯƠNG hoặc KHÔNG.', [['Hà Nội', 'TRỰC THUỘC TRUNG ƯƠNG'], ['Hải Phòng', 'TRỰC THUỘC TRUNG ƯƠNG'], ['Nam Định', 'KHÔNG PHẢI'], ['Việt Trì', 'KHÔNG PHẢI']], 'Hà Nội và Hải Phòng là thành phố trực thuộc trung ương; Nam Định và Việt Trì là thành phố thuộc tỉnh.', $d);
        $this->sortQ($s, 'Kéo mỗi đảo, quần đảo vào nhóm XA BỜ hoặc GẦN BỜ.', [['Hoàng Sa', 'XA BỜ'], ['Trường Sa', 'XA BỜ'], ['Phú Quốc', 'GẦN BỜ'], ['Cát Bà', 'GẦN BỜ']], 'Hoàng Sa và Trường Sa là quần đảo xa bờ; Phú Quốc và Cát Bà là đảo gần bờ.', $d);
        $this->fill($s, 'Việt Nam có chung đường biên giới trên đất liền với ___ nước.', [[0, '3']], 'Việt Nam giáp 3 nước trên đất liền: Trung Quốc, Lào và Cam-pu-chia.', $d);
        $this->fill($s, 'Nước có đường biên giới dài nhất với Việt Nam là ___.', [[0, 'Lào']], 'Lào có đường biên giới trên đất liền dài nhất với Việt Nam.', $d);
        $this->fill($s, 'Điểm cực Bắc của nước ta thuộc tỉnh Hà ___.', [[0, 'Giang']], 'Điểm cực Bắc ở Lũng Cú, tỉnh Hà Giang.', $d);
        $this->fill($s, 'Quần đảo Trường Sa do tỉnh Khánh ___ quản lí hành chính.', [[0, 'Hòa']], 'Quần đảo Trường Sa do tỉnh Khánh Hòa quản lí hành chính.', $d);
        $this->fill($s, 'Thành phố đông dân nhất Việt Nam là Thành phố Hồ Chí ___.', [[0, 'Minh']], 'TP. Hồ Chí Minh là thành phố đông dân nhất Việt Nam.', $d);
    }

    private function seedDlThuDoChauA(): void
    {
        $s = 'dl-thu-do-chau-a'; $d = 'trung_binh';
        $this->quiz($s, 'Thủ đô của Ấn Độ là thành phố nào?', ['Mumbai', 'New Delhi', 'Kolkata', 'Chennai'], 1, 'New Delhi là thủ đô của Ấn Độ; Mumbai chỉ là thành phố lớn nhất.', $d);
        $this->quiz($s, 'Thủ đô của Mông Cổ là thành phố nào?', ['Ulan Bator', 'Almaty', 'Bishkek', 'Dushanbe'], 0, 'Ulan Bator là thủ đô của Mông Cổ, một trong những thủ đô lạnh nhất thế giới.', $d);
        $this->quiz($s, 'Thủ đô của Phi-líp-pin là thành phố nào?', ['Cebu', 'Davao', 'Ma-ni-la', 'Quezon'], 2, 'Ma-ni-la là thủ đô của Phi-líp-pin.', $d);
        $this->quiz($s, 'Thủ đô của Thổ Nhĩ Kỳ là thành phố nào?', ['Istanbul', 'Izmir', 'Ankara', 'Antalya'], 2, 'Ankara là thủ đô của Thổ Nhĩ Kỳ; Istanbul chỉ là thành phố lớn nhất.', $d);
        $this->quiz($s, 'Thủ đô của Triều Tiên là thành phố nào?', ['Bình Nhưỡng', 'Khai Thành', 'Seoul', 'Busan'], 0, 'Bình Nhưỡng là thủ đô của Triều Tiên; Seoul là thủ đô của Hàn Quốc.', $d);
        $this->matching($s, 'Nối mỗi nước Tây Á với thủ đô của nó.', [['Thổ Nhĩ Kỳ', 'Ankara'], ['Ả Rập Xê Út', 'Ri-át'], ['I-ran', 'Tê-hê-ran'], ['I-rắc', 'Bát-đa']], 'Thủ đô các nước Tây Á: Thổ Nhĩ Kỳ – Ankara, Ả Rập Xê Út – Ri-át, I-ran – Tê-hê-ran, I-rắc – Bát-đa.', $d);
        $this->matching($s, 'Nối mỗi nước Nam Á với thủ đô của nó.', [['Ấn Độ', 'New Delhi'], ['Pa-ki-xtan', 'I-xla-ma-bát'], ['Băng-la-đét', 'Đắc-ca'], ['Nê-pan', 'Kat-man-đu']], 'Thủ đô các nước Nam Á: Ấn Độ – New Delhi, Pa-ki-xtan – I-xla-ma-bát, Băng-la-đét – Đắc-ca, Nê-pan – Kat-man-đu.', $d);
        $this->matching($s, 'Nối mỗi thành phố lớn (KHÔNG phải thủ đô) với quốc gia của nó.', [['Thượng Hải', 'Trung Quốc'], ['Mumbai', 'Ấn Độ'], ['Istanbul', 'Thổ Nhĩ Kỳ'], ['Karachi', 'Pa-ki-xtan']], 'Thượng Hải, Mumbai, Istanbul và Karachi đều là thành phố lớn nhưng không phải thủ đô.', $d);
        $this->matching($s, 'Nối mỗi nước Trung Á với thủ đô của nó.', [['Ca-dắc-xtan', 'A-xta-na'], ['U-dơ-bê-ki-xtan', 'Tát-ken'], ['Tát-gi-ki-xtan', 'Đu-san-bê'], ['Tuốc-mê-ni-xtan', 'Át-ga-bát']], 'Thủ đô các nước Trung Á: Ca-dắc-xtan – A-xta-na, U-dơ-bê-ki-xtan – Tát-ken, Tát-gi-ki-xtan – Đu-san-bê, Tuốc-mê-ni-xtan – Át-ga-bát.', $d);
        $this->matching($s, 'Nối mỗi nước Đông Nam Á còn lại với thủ đô của nó.', [['Mi-an-ma', 'Nây-pi-đô'], ['Bru-nây', 'Ban-đa Xê-ri Bê-ga-oan'], ['Đông Ti-mo', 'Đi-li'], ['Xin-ga-po', 'Xin-ga-po']], 'Thủ đô Mi-an-ma là Nây-pi-đô, Bru-nây là Ban-đa Xê-ri Bê-ga-oan, Đông Ti-mo là Đi-li.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc KHÔNG PHẢI THỦ ĐÔ.', [['Hà Nội', 'THỦ ĐÔ'], ['Bắc Kinh', 'THỦ ĐÔ'], ['Thượng Hải', 'KHÔNG PHẢI THỦ ĐÔ'], ['Osaka', 'KHÔNG PHẢI THỦ ĐÔ']], 'Hà Nội và Bắc Kinh là thủ đô; Thượng Hải và Osaka chỉ là thành phố lớn.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm ĐÔNG NAM Á hoặc ĐÔNG Á.', [['Viêng Chăn', 'ĐÔNG NAM Á'], ['Phnom Penh', 'ĐÔNG NAM Á'], ['Bình Nhưỡng', 'ĐÔNG Á'], ['Ulan Bator', 'ĐÔNG Á']], 'Viêng Chăn và Phnom Penh ở Đông Nam Á; Bình Nhưỡng và Ulan Bator ở Đông Á.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm NƯỚC LÁNG GIỀNG CỦA VIỆT NAM hoặc KHÔNG.', [['Viêng Chăn', 'NƯỚC LÁNG GIỀNG'], ['Bắc Kinh', 'NƯỚC LÁNG GIỀNG'], ['Ri-át', 'KHÔNG PHẢI LÁNG GIỀNG'], ['A-xta-na', 'KHÔNG PHẢI LÁNG GIỀNG']], 'Lào và Trung Quốc là láng giềng của Việt Nam; Ả Rập Xê Út và Ca-dắc-xtan thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm VEN BIỂN hoặc TRONG ĐẤT LIỀN.', [['Tokyo', 'VEN BIỂN'], ['Ma-ni-la', 'VEN BIỂN'], ['Bắc Kinh', 'TRONG ĐẤT LIỀN'], ['New Delhi', 'TRONG ĐẤT LIỀN'], ['Ka-bun', 'TRONG ĐẤT LIỀN']], 'Tokyo và Ma-ni-la là thủ đô ven biển; Bắc Kinh, New Delhi và Ka-bun nằm trong đất liền.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nước – thủ đô vào nhóm ĐÚNG hoặc SAI.', [['Trung Quốc – Bắc Kinh', 'ĐÚNG'], ['Hàn Quốc – Seoul', 'ĐÚNG'], ['Nhật Bản – Osaka', 'SAI'], ['Thổ Nhĩ Kỳ – Istanbul', 'SAI']], 'Thủ đô Nhật Bản là Tokyo, thủ đô Thổ Nhĩ Kỳ là Ankara; còn Bắc Kinh và Seoul là đúng.', $d);
        $this->fill($s, 'Thủ đô của Ấn Độ là New ___.', [[0, 'Delhi']], 'New Delhi là thủ đô của Ấn Độ.', $d);
        $this->fill($s, 'Thủ đô của Mông Cổ là Ulan ___.', [[0, 'Bator']], 'Ulan Bator là thủ đô của Mông Cổ.', $d);
        $this->fill($s, 'Thủ đô của Phi-líp-pin là Ma-ni-___.', [[0, 'la']], 'Ma-ni-la là thủ đô của Phi-líp-pin.', $d);
        $this->fill($s, 'Thủ đô của Ả Rập Xê Út là ___.', [[0, 'Ri-át']], 'Ri-át là thủ đô của Ả Rập Xê Út.', $d);
        $this->fill($s, 'Istanbul không phải thủ đô của Thổ Nhĩ Kỳ; thủ đô của nước này là ___.', [[0, 'Ankara']], 'Ankara mới là thủ đô của Thổ Nhĩ Kỳ; Istanbul chỉ là thành phố lớn nhất.', $d);
    }

    private function seedDlThuDoTheGioi(): void
    {
        $s = 'dl-thu-do-the-gioi'; $d = 'trung_binh';
        $this->quiz($s, 'Thủ đô của Ý, nơi có đấu trường La Mã, là thành phố nào?', ['Milan', 'Roma', 'Naples', 'Venice'], 1, 'Roma là thủ đô của Ý, nổi tiếng với đấu trường La Mã cổ đại.', $d);
        $this->quiz($s, 'Thủ đô của Tây Ban Nha là thành phố nào?', ['Barcelona', 'Sevilla', 'Madrid', 'Valencia'], 2, 'Madrid là thủ đô của Tây Ban Nha; Barcelona chỉ là thành phố lớn thứ hai.', $d);
        $this->quiz($s, 'Thủ đô của Bra-xin là thành phố nào?', ['Rio de Janeiro', 'São Paulo', 'Bra-xi-li-a', 'Salvador'], 2, 'Bra-xi-li-a là thủ đô được xây dựng mới của Bra-xin; Rio de Janeiro chỉ là thành phố du lịch nổi tiếng.', $d);
        $this->quiz($s, 'Thủ đô của Ai Cập là thành phố nào?', ['Alexandria', 'Giza', 'Cai-rô', 'Luxor'], 2, 'Cai-rô là thủ đô của Ai Cập, thành phố lớn nhất châu Phi.', $d);
        $this->quiz($s, 'Thủ đô của Ca-na-đa là thành phố nào?', ['Toronto', 'Vancouver', 'Montreal', 'Ốt-ta-oa'], 3, 'Ốt-ta-oa là thủ đô của Ca-na-đa; Toronto chỉ là thành phố lớn nhất.', $d);
        $this->matching($s, 'Nối mỗi nước châu Âu với thủ đô của nó.', [['Ý', 'Roma'], ['Tây Ban Nha', 'Madrid'], ['Bồ Đào Nha', 'Lít-bon'], ['Hy Lạp', 'A-ten']], 'Thủ đô Nam Âu: Ý – Roma, Tây Ban Nha – Madrid, Bồ Đào Nha – Lít-bon, Hy Lạp – A-ten.', $d);
        $this->matching($s, 'Nối mỗi nước châu Mỹ với thủ đô của nó.', [['Bra-xin', 'Bra-xi-li-a'], ['Ác-hen-ti-na', 'Bu-ê-nốt Ai-rét'], ['Mê-hi-cô', 'Mê-hi-cô Xi-ti'], ['Cu-ba', 'La Ha-ba-na']], 'Thủ đô châu Mỹ: Bra-xin – Bra-xi-li-a, Ác-hen-ti-na – Bu-ê-nốt Ai-rét, Mê-hi-cô – Mê-hi-cô Xi-ti, Cu-ba – La Ha-ba-na.', $d);
        $this->matching($s, 'Nối mỗi nước châu Phi với thủ đô của nó.', [['Ai Cập', 'Cai-rô'], ['Nam Phi', 'Prê-tô-ri-a'], ['Ni-giê-ri-a', 'A-bu-gia'], ['Ê-ti-ô-pi-a', 'A-đít A-ba-ba']], 'Thủ đô châu Phi: Ai Cập – Cai-rô, Nam Phi – Prê-tô-ri-a, Ni-giê-ri-a – A-bu-gia, Ê-ti-ô-pi-a – A-đít A-ba-ba.', $d);
        $this->matching($s, 'Nối mỗi "bẫy" (thành phố lớn KHÔNG phải thủ đô) với quốc gia của nó.', [['Niu Óoc', 'Hoa Kỳ'], ['Xít-ni', 'Úc'], ['Rio de Janeiro', 'Bra-xin'], ['Tô-rôn-tô', 'Ca-na-đa']], 'Niu Óoc, Xít-ni, Rio de Janeiro và Tô-rôn-tô đều là thành phố lớn nhưng không phải thủ đô.', $d);
        $this->matching($s, 'Nối mỗi nước châu Đại Dương với thủ đô của nó.', [['Niu Di-lân', 'Oa-linh-tơn'], ['Pa-pua Niu Ghi-nê', 'Po Mo-rét-bi'], ['Phi-gi', 'Xu-va'], ['Xa-moa', 'A-pi-a']], 'Thủ đô châu Đại Dương: Niu Di-lân – Oa-linh-tơn, Pa-pua Niu Ghi-nê – Po Mo-rét-bi, Phi-gi – Xu-va, Xa-moa – A-pi-a.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm CHÂU ÂU hoặc CHÂU MỸ.', [['Roma', 'CHÂU ÂU'], ['Madrid', 'CHÂU ÂU'], ['Ốt-ta-oa', 'CHÂU MỸ'], ['Bra-xi-li-a', 'CHÂU MỸ']], 'Roma và Madrid ở châu Âu; Ốt-ta-oa và Bra-xi-li-a ở châu Mỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc THÀNH PHỐ LỚN (không phải thủ đô).', [['Cai-rô', 'THỦ ĐÔ'], ['A-ten', 'THỦ ĐÔ'], ['Niu Óoc', 'THÀNH PHỐ LỚN'], ['Xít-ni', 'THÀNH PHỐ LỚN']], 'Cai-rô và A-ten là thủ đô; Niu Óoc và Xít-ni chỉ là thành phố lớn.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm CHÂU PHI hoặc CHÂU Á.', [['Cai-rô', 'CHÂU PHI'], ['A-bu-gia', 'CHÂU PHI'], ['Bắc Kinh', 'CHÂU Á'], ['New Delhi', 'CHÂU Á']], 'Cai-rô và A-bu-gia ở châu Phi; Bắc Kinh và New Delhi ở châu Á.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm NẰM TRÊN ĐẢO hoặc TRÊN LỤC ĐỊA.', [['Oa-linh-tơn', 'TRÊN ĐẢO'], ['Ma-ni-la', 'TRÊN ĐẢO'], ['Pa-ri', 'TRÊN LỤC ĐỊA'], ['Ma-đrít', 'TRÊN LỤC ĐỊA'], ['Mát-xcơ-va', 'TRÊN LỤC ĐỊA']], 'Oa-linh-tơn và Ma-ni-la nằm trên đảo; Pa-ri, Ma-đrít và Mát-xcơ-va nằm trên lục địa.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nước – thủ đô vào nhóm ĐÚNG hoặc SAI.', [['Úc – Can-bê-ra', 'ĐÚNG'], ['Ca-na-đa – Ốt-ta-oa', 'ĐÚNG'], ['Hoa Kỳ – Niu Óoc', 'SAI'], ['Bra-xin – Rio de Janeiro', 'SAI']], 'Thủ đô Hoa Kỳ là Oa-sinh-tơn, thủ đô Bra-xin là Bra-xi-li-a; còn Can-bê-ra và Ốt-ta-oa là đúng.', $d);
        $this->fill($s, 'Thủ đô của Ý, nơi có đấu trường La Mã, là ___.', [[0, 'Roma']], 'Roma là thủ đô của Ý, nổi tiếng với đấu trường La Mã.', $d);
        $this->fill($s, 'Thủ đô của Tây Ban Nha là ___.', [[0, 'Madrid']], 'Madrid là thủ đô của Tây Ban Nha.', $d);
        $this->fill($s, 'Thủ đô của Bra-xin là Bra-xi-li-___.', [[0, 'a']], 'Bra-xi-li-a là thủ đô được xây dựng mới của Bra-xin.', $d);
        $this->fill($s, 'Thủ đô của Ai Cập là ___.', [[0, 'Cai-rô']], 'Cai-rô là thủ đô của Ai Cập.', $d);
        $this->fill($s, 'Thủ đô của Ca-na-đa là Ốt-ta-___.', [[0, 'oa']], 'Ốt-ta-oa là thủ đô của Ca-na-đa.', $d);
    }

    private function seedDlDiaHinhSongNgoi(): void
    {
        $s = 'dl-dia-hinh-song-ngoi'; $d = 'trung_binh';
        $this->quiz($s, 'Dãy núi nào chạy dọc phía tây nước ta, là biên giới tự nhiên với Lào?', ['Hoàng Liên Sơn', 'Trường Sơn', 'Bạch Mã', 'Hoành Sơn'], 1, 'Dãy Trường Sơn chạy dọc phía tây nước ta, là biên giới tự nhiên với Lào.', $d);
        $this->quiz($s, 'Sông Hồng bắt nguồn từ đâu?', ['Vân Nam, Trung Quốc', 'Lào', 'Điện Biên', 'Sơn La'], 0, 'Sông Hồng bắt nguồn từ tỉnh Vân Nam (Trung Quốc), chảy vào Việt Nam ở Lào Cai.', $d);
        $this->quiz($s, 'Đồng bằng sông Hồng còn có tên gọi là gì?', ['Đồng bằng Nam Bộ', 'Đồng bằng Bắc Bộ', 'Đồng bằng duyên hải', 'Đồng bằng Thanh Hóa'], 1, 'Đồng bằng sông Hồng còn gọi là đồng bằng Bắc Bộ.', $d);
        $this->quiz($s, 'Vùng nào có đất đỏ ba-dan màu mỡ, rất thích hợp trồng cà phê?', ['Đồng bằng sông Hồng', 'Duyên hải miền Trung', 'Tây Nguyên', 'Đông Bắc Bộ'], 2, 'Tây Nguyên có đất đỏ ba-dan màu mỡ, thích hợp trồng cà phê, cao su.', $d);
        $this->quiz($s, 'Sông Đà là phụ lưu lớn nhất của sông nào?', ['Sông Mê Kông', 'Sông Hồng', 'Sông Mã', 'Sông Đồng Nai'], 1, 'Sông Đà là phụ lưu lớn nhất của sông Hồng, trên đó có các nhà máy thủy điện lớn.', $d);
        $this->matching($s, 'Nối mỗi cao nguyên với đặc điểm nổi bật của nó.', [['Tây Nguyên', 'Đất đỏ ba-dan trồng cà phê'], ['Mộc Châu', 'Chè và sữa'], ['Đà Lạt', 'Rau, hoa xứ lạnh'], ['Đồng Văn', 'Cao nguyên đá']], 'Tây Nguyên đất đỏ ba-dan, Mộc Châu nổi tiếng chè sữa, Đà Lạt rau hoa, Đồng Văn là cao nguyên đá.', $d);
        $this->matching($s, 'Nối mỗi dãy núi với vị trí của nó.', [['Hoàng Liên Sơn', 'Tây Bắc'], ['Trường Sơn Bắc', 'Bắc Trung Bộ'], ['Trường Sơn Nam', 'Tây Nguyên'], ['Hoành Sơn', 'Ranh giới Hà Tĩnh – Quảng Bình']], 'Hoàng Liên Sơn ở Tây Bắc, Trường Sơn Bắc ở Bắc Trung Bộ, Trường Sơn Nam ở Tây Nguyên.', $d);
        $this->matching($s, 'Nối mỗi đồng bằng với đặc điểm của nó.', [['Đồng bằng sông Cửu Long', 'Vựa lúa lớn nhất'], ['Đồng bằng sông Hồng', 'Đông dân, thâm canh cao'], ['Duyên hải miền Trung', 'Nhỏ hẹp, đất cát'], ['Thanh – Nghệ – Tĩnh', 'Lớn nhất miền Trung']], 'ĐBSCL là vựa lúa lớn nhất, ĐBSH đông dân thâm canh cao, duyên hải miền Trung nhỏ hẹp.', $d);
        $this->matching($s, 'Nối mỗi hồ với đặc điểm của nó.', [['Ba Bể', 'Hồ nước ngọt tự nhiên lớn nhất'], ['Hoàn Kiếm', 'Hồ giữa lòng Hà Nội'], ['Trị An', 'Hồ thủy điện ở Đông Nam Bộ'], ['Thác Bà', 'Hồ thủy điện ở miền Bắc']], 'Hồ Ba Bể là hồ nước ngọt tự nhiên lớn nhất; Trị An và Thác Bà là hồ thủy điện.', $d);
        $this->matching($s, 'Nối mỗi con sông với tỉnh, thành phố nó chảy qua.', [['Sông Hương', 'Thừa Thiên Huế'], ['Sông Hàn', 'Đà Nẵng'], ['Sông Sài Gòn', 'TP. Hồ Chí Minh'], ['Sông Lam', 'Nghệ An']], 'Sông Hương chảy qua Huế, sông Hàn qua Đà Nẵng, sông Sài Gòn qua TP. Hồ Chí Minh, sông Lam qua Nghệ An.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm MIỀN NÚI hoặc ĐỒNG BẰNG.', [['Sa Pa', 'MIỀN NÚI'], ['Đà Lạt', 'MIỀN NÚI'], ['Cần Thơ', 'ĐỒNG BẰNG'], ['Hải Phòng', 'ĐỒNG BẰNG']], 'Sa Pa và Đà Lạt ở miền núi; Cần Thơ và Hải Phòng ở đồng bằng.', $d);
        $this->sortQ($s, 'Kéo mỗi con sông vào nhóm Ở MIỀN BẮC hoặc Ở MIỀN NAM.', [['Sông Lô', 'MIỀN BẮC'], ['Sông Đà', 'MIỀN BẮC'], ['Sông Đồng Nai', 'MIỀN NAM'], ['Sông Vàm Cỏ', 'MIỀN NAM']], 'Sông Lô và sông Đà ở miền Bắc; sông Đồng Nai và sông Vàm Cỏ ở miền Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm ĐỊA HÌNH CAO hoặc ĐỊA HÌNH THẤP.', [['Cao nguyên Lâm Đồng', 'ĐỊA HÌNH CAO'], ['Núi Bà Đen', 'ĐỊA HÌNH CAO'], ['Đồng bằng sông Hồng', 'ĐỊA HÌNH THẤP'], ['Bãi biển Mỹ Khê', 'ĐỊA HÌNH THẤP']], 'Cao nguyên Lâm Đồng và núi Bà Đen là địa hình cao; đồng bằng và bãi biển là địa hình thấp.', $d);
        $this->sortQ($s, 'Kéo mỗi vùng vào nhóm ĐỒNG BẰNG CHÂU THỔ hoặc KHÔNG PHẢI.', [['Đồng bằng sông Hồng', 'ĐỒNG BẰNG CHÂU THỔ'], ['Đồng bằng sông Cửu Long', 'ĐỒNG BẰNG CHÂU THỔ'], ['Tây Nguyên', 'KHÔNG PHẢI'], ['Duyên hải miền Trung', 'KHÔNG PHẢI']], 'ĐBSH và ĐBSCL là đồng bằng châu thổ do sông bồi đắp; Tây Nguyên và duyên hải miền Trung thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi con sông vào nhóm BẮT NGUỒN TỪ NƯỚC NGOÀI hoặc TRONG NƯỚC.', [['Sông Hồng', 'TỪ NƯỚC NGOÀI'], ['Sông Mê Kông', 'TỪ NƯỚC NGOÀI'], ['Sông Thu Bồn', 'TRONG NƯỚC'], ['Sông Hàn', 'TRONG NƯỚC']], 'Sông Hồng và Mê Kông bắt nguồn từ nước ngoài; sông Thu Bồn và sông Hàn bắt nguồn trong nước.', $d);
        $this->fill($s, 'Dãy núi chạy dọc biên giới Việt – Lào ở phía tây là dãy Trường ___.', [[0, 'Sơn']], 'Dãy Trường Sơn chạy dọc phía tây, là biên giới tự nhiên với Lào.', $d);
        $this->fill($s, 'Sông Đà là phụ lưu lớn nhất của sông ___.', [[0, 'Hồng']], 'Sông Đà là phụ lưu lớn nhất của sông Hồng.', $d);
        $this->fill($s, 'Vùng đất đỏ ba-dan màu mỡ, thích hợp trồng cà phê là Tây ___.', [[0, 'Nguyên']], 'Tây Nguyên có đất đỏ ba-dan, thích hợp trồng cà phê.', $d);
        $this->fill($s, 'Hồ nước ngọt tự nhiên lớn nhất Việt Nam là hồ Ba ___.', [[0, 'Bể']], 'Hồ Ba Bể (Bắc Kạn) là hồ nước ngọt tự nhiên lớn nhất Việt Nam.', $d);
        $this->fill($s, 'Sông Hương chảy qua thành phố ___ ở miền Trung.', [[0, 'Huế']], 'Sông Hương chảy qua thành phố Huế, tỉnh Thừa Thiên Huế.', $d);
    }

    private function seedDlKhiHau(): void
    {
        $s = 'dl-khi-hau'; $d = 'trung_binh';
        $this->quiz($s, 'Miền Bắc Việt Nam một năm có mấy mùa rõ rệt?', ['2', '3', '4', 'Không phân mùa'], 2, 'Miền Bắc có 4 mùa rõ rệt: xuân, hạ, thu, đông.', $d);
        $this->quiz($s, 'Mùa bão ở Việt Nam thường tập trung vào những tháng nào?', ['Tháng 1 – 3', 'Tháng 4 – 6', 'Tháng 7 – 11', 'Tháng 12'], 2, 'Mùa bão ở Việt Nam thường từ tháng 7 đến tháng 11 hằng năm.', $d);
        $this->quiz($s, 'Nơi nào được coi là mưa nhiều nhất Việt Nam?', ['Phan Thiết', 'Bạch Mã', 'Ninh Thuận', 'Cà Mau'], 1, 'Vùng núi Bạch Mã (Thừa Thiên Huế) là nơi mưa nhiều nhất nước ta.', $d);
        $this->quiz($s, 'Gió Lào (gió phơn Tây Nam) gây ra hiện tượng gì ở miền Trung?', ['Mưa lớn', 'Khô nóng', 'Rét đậm', 'Sương mù'], 1, 'Gió Lào vượt dãy Trường Sơn gây khô nóng gay gắt cho miền Trung vào mùa hè.', $d);
        $this->quiz($s, 'Vì sao miền Trung hay bị lũ lụt vào mùa mưa?', ['Địa hình hẹp, mưa lớn tập trung', 'Không có sông', 'Quá lạnh', 'Thiếu rừng hoàn toàn'], 0, 'Miền Trung địa hình hẹp, mưa bão tập trung nên hay bị lũ lụt.', $d);
        $this->matching($s, 'Nối mỗi loại thiên tai với mùa nó thường xảy ra.', [['Bão', 'Cuối hè đầu thu'], ['Lũ lụt miền Trung', 'Thu đông'], ['Hạn hán Nam Bộ', 'Mùa khô'], ['Rét đậm miền Bắc', 'Mùa đông']], 'Bão cuối hè đầu thu, lũ miền Trung thu đông, hạn Nam Bộ mùa khô, rét đậm miền Bắc mùa đông.', $d);
        $this->matching($s, 'Nối mỗi địa danh với đặc điểm khí hậu của nó.', [['Sa Pa', 'Lạnh, đôi khi có tuyết'], ['Đà Lạt', 'Mát mẻ quanh năm'], ['Phan Thiết', 'Khô, ít mưa'], ['Huế', 'Mưa nhiều']], 'Sa Pa lạnh có tuyết, Đà Lạt mát quanh năm, Phan Thiết khô ít mưa, Huế mưa nhiều.', $d);
        $this->matching($s, 'Nối mỗi loại gió với hướng thổi của nó ở Việt Nam.', [['Gió mùa đông bắc', 'Từ phương Bắc xuống'], ['Gió mùa tây nam', 'Từ Ấn Độ Dương vào'], ['Gió Tín phong', 'Từ phía đông vào'], ['Gió Lào', 'Từ phía tây vượt Trường Sơn']], 'Gió mùa đông bắc từ phương Bắc, gió mùa tây nam từ Ấn Độ Dương, gió Lào từ phía tây.', $d);
        $this->matching($s, 'Nối mỗi hiện tượng thời tiết với nguyên nhân của nó.', [['Sương muối', 'Rét đậm'], ['Lũ quét', 'Mưa lớn ở miền núi'], ['Hạn hán', 'Thiếu mưa kéo dài'], ['Ngập lụt đô thị', 'Mưa lớn, thoát nước kém']], 'Sương muối do rét đậm, lũ quét do mưa lớn miền núi, hạn hán do thiếu mưa kéo dài.', $d);
        $this->matching($s, 'Nối mỗi yếu tố khí hậu với ảnh hưởng của nó tới nông nghiệp.', [['Mưa nhiều', 'Gây ngập úng'], ['Nắng nóng kéo dài', 'Gây hạn hán'], ['Bão', 'Làm thiệt hại mùa màng'], ['Rét đậm', 'Gia súc chết rét']], 'Mưa nhiều gây ngập úng, nắng nóng gây hạn hán, bão thiệt hại mùa màng, rét đậm làm gia súc chết rét.', $d);
        $this->sortQ($s, 'Kéo mỗi tháng vào nhóm MÙA MƯA hoặc MÙA KHÔ (ở miền Nam).', [['Tháng 8', 'MÙA MƯA'], ['Tháng 9', 'MÙA MƯA'], ['Tháng 2', 'MÙA KHÔ'], ['Tháng 3', 'MÙA KHÔ']], 'Miền Nam mưa nhiều từ tháng 5 đến tháng 10, khô từ tháng 11 đến tháng 4 năm sau.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm THUỘC KHÍ HẬU NHIỆT ĐỚI hoặc KHÔNG.', [['Nóng ẩm quanh năm', 'NHIỆT ĐỚI'], ['Mưa nhiều', 'NHIỆT ĐỚI'], ['Tuyết rơi dày mùa đông', 'KHÔNG PHẢI'], ['Mùa đông lạnh kéo dài', 'KHÔNG PHẢI']], 'Khí hậu nhiệt đới nóng ẩm, mưa nhiều; tuyết rơi và đông lạnh kéo dài không phải đặc điểm nhiệt đới.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm THUẬN LỢI hoặc KHÓ KHĂN cho sản xuất nông nghiệp.', [['Trồng cây quanh năm', 'THUẬN LỢI'], ['Nhiều vụ trong năm', 'THUẬN LỢI'], ['Bão lũ', 'KHÓ KHĂN'], ['Sâu bệnh phát triển', 'KHÓ KHĂN']], 'Khí hậu nhiệt đới giúp trồng quanh năm, nhiều vụ; nhưng bão lũ và sâu bệnh gây khó khăn.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm CỦA MIỀN BẮC hoặc CỦA MIỀN NAM.', [['Rét đậm mùa đông', 'MIỀN BẮC'], ['Có 4 mùa rõ rệt', 'MIỀN BẮC'], ['Nắng nóng quanh năm', 'MIỀN NAM'], ['Chỉ có 2 mùa', 'MIỀN NAM']], 'Miền Bắc có 4 mùa, mùa đông rét đậm; miền Nam nắng quanh năm, chỉ có 2 mùa.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm DO GIÓ MÙA ĐÔNG BẮC hoặc GIÓ MÙA TÂY NAM.', [['Rét, hanh khô', 'GIÓ MÙA ĐÔNG BẮC'], ['Trời lạnh miền Bắc', 'GIÓ MÙA ĐÔNG BẮC'], ['Mưa nhiều ở Nam Bộ', 'GIÓ MÙA TÂY NAM'], ['Nóng ẩm', 'GIÓ MÙA TÂY NAM']], 'Gió mùa đông bắc gây rét, hanh khô; gió mùa tây nam gây mưa nhiều, nóng ẩm.', $d);
        $this->fill($s, 'Miền Bắc có bốn mùa: xuân, hạ, thu, ___.', [[0, 'đông']], 'Miền Bắc có 4 mùa rõ rệt: xuân, hạ, thu, đông.', $d);
        $this->fill($s, 'Gió phơn Tây Nam còn gọi là gió ___, gây khô nóng ở miền Trung.', [[0, 'Lào']], 'Gió Lào (gió phơn Tây Nam) gây khô nóng gay gắt ở miền Trung.', $d);
        $this->fill($s, 'Nơi mưa nhiều nhất nước ta là vùng núi Bạch ___.', [[0, 'Mã']], 'Vùng núi Bạch Mã là nơi mưa nhiều nhất Việt Nam.', $d);
        $this->fill($s, 'Mùa bão ở Việt Nam thường từ tháng 7 đến tháng ___.', [[0, '11']], 'Mùa bão ở Việt Nam thường kéo dài từ tháng 7 đến tháng 11.', $d);
        $this->fill($s, 'Sa Pa đôi khi có ___ rơi vào mùa đông.', [[0, 'tuyết']], 'Sa Pa ở vùng núi cao nên mùa đông đôi khi có tuyết rơi.', $d);
    }

    private function seedDlChauLucDaiDuongLop61(): void
    {
        $s = 'dl-chau-luc-dai-duong-lop-6-1'; $d = 'de';
        $this->quiz($s, 'Sông nào dài nhất châu Á?', ['Sông Mê Kông', 'Sông Trường Giang', 'Sông Hằng', 'Sông Hoàng Hà'], 1, 'Sông Trường Giang (Dương Tử) dài khoảng 6 300 km, là sông dài nhất châu Á.', $d);
        $this->quiz($s, 'Hồ nước ngọt sâu nhất thế giới nằm ở châu Á có tên là gì?', ['Hồ Bai-can', 'Hồ Ca-xpi', 'Hồ Tōn-lê Sáp', 'Hồ Ba Bể'], 0, 'Hồ Bai-can ở Nga là hồ nước ngọt sâu nhất thế giới, sâu hơn 1 600 m.', $d);
        $this->quiz($s, 'Châu Á tiếp giáp với bao nhiêu đại dương?', ['1', '2', '3', '4'], 2, 'Châu Á tiếp giáp với 3 đại dương: Thái Bình Dương, Ấn Độ Dương và Bắc Băng Dương.', $d);
        $this->quiz($s, 'Đỉnh Ê-vơ-rét cao khoảng bao nhiêu mét?', ['7 849 m', '8 849 m', '9 849 m', '6 849 m'], 1, 'Đỉnh Ê-vơ-rét cao khoảng 8 849 m, là đỉnh núi cao nhất thế giới.', $d);
        $this->quiz($s, 'Quốc gia nào có diện tích lớn nhất châu Á?', ['Trung Quốc', 'Ấn Độ', 'Nga', 'Ca-dắc-xtan'], 2, 'Nga là quốc gia có diện tích lớn nhất châu Á và cũng lớn nhất thế giới.', $d);
        $this->matching($s, 'Nối mỗi con sông châu Á với đặc điểm của nó.', [['Sông Trường Giang', 'Dài nhất châu Á'], ['Sông Mê Kông', 'Chảy qua nhiều nước Đông Nam Á'], ['Sông Hằng', 'Dòng sông thiêng của Ấn Độ'], ['Sông Hoàng Hà', 'Cái nôi văn minh Trung Hoa']], 'Trường Giang dài nhất châu Á, Mê Kông chảy qua nhiều nước, Hằng là sông thiêng Ấn Độ.', $d);
        $this->matching($s, 'Nối mỗi địa danh châu Á với quốc gia của nó.', [['Vạn Lý Trường Thành', 'Trung Quốc'], ['Ăng-co Vát', 'Cam-pu-chia'], ['Núi Phú Sĩ', 'Nhật Bản'], ['Vịnh Hạ Long', 'Việt Nam']], 'Vạn Lý Trường Thành ở Trung Quốc, Ăng-co Vát ở Cam-pu-chia, núi Phú Sĩ ở Nhật Bản.', $d);
        $this->matching($s, 'Nối mỗi quốc gia với khu vực của nó ở châu Á.', [['Nhật Bản', 'Đông Á'], ['Ấn Độ', 'Nam Á'], ['Thái Lan', 'Đông Nam Á'], ['Ả Rập Xê Út', 'Tây Á']], 'Nhật Bản ở Đông Á, Ấn Độ ở Nam Á, Thái Lan ở Đông Nam Á, Ả Rập Xê Út ở Tây Á.', $d);
        $this->matching($s, 'Nối mỗi châu lục với đặc điểm dân cư – tự nhiên của nó.', [['Châu Á', 'Đông dân nhất'], ['Châu Âu', 'Nhiều nước phát triển'], ['Châu Phi', 'Có hoang mạc Xa-ha-ra'], ['Châu Mỹ', 'Có rừng A-ma-dôn']], 'Châu Á đông dân nhất, châu Âu nhiều nước phát triển, châu Phi có Xa-ha-ra, châu Mỹ có rừng A-ma-dôn.', $d);
        $this->matching($s, 'Nối mỗi bán đảo châu Á với khu vực của nó.', [['Bán đảo Đông Dương', 'Đông Nam Á'], ['Bán đảo Ả Rập', 'Tây Á'], ['Bán đảo Triều Tiên', 'Đông Á'], ['Bán đảo Ấn Độ', 'Nam Á']], 'Bán đảo Đông Dương ở Đông Nam Á, Ả Rập ở Tây Á, Triều Tiên ở Đông Á, Ấn Độ ở Nam Á.', $d);
        $this->sortQ($s, 'Kéo mỗi quốc gia vào nhóm THUỘC CHÂU Á hoặc KHÔNG THUỘC CHÂU Á.', [['Hàn Quốc', 'THUỘC CHÂU Á'], ['Ấn Độ', 'THUỘC CHÂU Á'], ['Ai Cập', 'KHÔNG THUỘC CHÂU Á'], ['Pháp', 'KHÔNG THUỘC CHÂU Á']], 'Hàn Quốc và Ấn Độ thuộc châu Á; Ai Cập thuộc châu Phi, Pháp thuộc châu Âu.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm Ở CHÂU Á hoặc KHÔNG Ở CHÂU Á.', [['Vạn Lý Trường Thành', 'Ở CHÂU Á'], ['Núi Phú Sĩ', 'Ở CHÂU Á'], ['Tháp Eiffel', 'KHÔNG Ở CHÂU Á'], ['Kim tự tháp Giza', 'KHÔNG Ở CHÂU Á']], 'Vạn Lý Trường Thành và núi Phú Sĩ ở châu Á; tháp Eiffel ở châu Âu, kim tự tháp Giza ở châu Phi.', $d);
        $this->sortQ($s, 'Kéo mỗi khu vực vào nhóm ĐÔNG DÂN hoặc THƯA DÂN ở châu Á.', [['Đồng bằng sông Hằng', 'ĐÔNG DÂN'], ['Đồng bằng Hoa Bắc', 'ĐÔNG DÂN'], ['Xi-bia', 'THƯA DÂN'], ['Hoang mạc Gô-bi', 'THƯA DÂN']], 'Đồng bằng sông Hằng và Hoa Bắc đông dân; Xi-bia và hoang mạc Gô-bi thưa dân.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm ĐỊA HÌNH hoặc KHÍ HẬU của châu Á.', [['Núi cao', 'ĐỊA HÌNH'], ['Đồng bằng rộng', 'ĐỊA HÌNH'], ['Gió mùa', 'KHÍ HẬU'], ['Khô hạn', 'KHÍ HẬU']], 'Núi cao, đồng bằng là địa hình; gió mùa, khô hạn là khí hậu.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Châu Á là châu lục rộng nhất', 'ĐÚNG'], ['Nhật Bản thuộc châu Á', 'ĐÚNG'], ['Sông Nin ở châu Á', 'SAI'], ['Châu Á không giáp đại dương nào', 'SAI']], 'Châu Á rộng nhất và Nhật Bản thuộc châu Á là đúng; sông Nin ở châu Phi, châu Á giáp 3 đại dương.', $d);
        $this->fill($s, 'Sông dài nhất châu Á là sông Trường ___.', [[0, 'Giang']], 'Sông Trường Giang dài khoảng 6 300 km, là sông dài nhất châu Á.', $d);
        $this->fill($s, 'Hồ nước ngọt sâu nhất thế giới Bai-can nằm ở nước ___.', [[0, 'Nga']], 'Hồ Bai-can ở Nga sâu hơn 1 600 m, sâu nhất thế giới.', $d);
        $this->fill($s, 'Châu Á tiếp giáp với ___ đại dương.', [[0, '3']], 'Châu Á giáp 3 đại dương: Thái Bình Dương, Ấn Độ Dương và Bắc Băng Dương.', $d);
        $this->fill($s, 'Đỉnh Ê-vơ-rét cao khoảng 8 849 ___.', [[0, 'mét']], 'Đỉnh Ê-vơ-rét cao khoảng 8 849 m.', $d);
        $this->fill($s, 'Quốc gia có diện tích lớn nhất châu Á là ___.', [[0, 'Nga']], 'Nga là quốc gia rộng nhất châu Á và thế giới.', $d);
    }

    private function seedDlChauLucDaiDuongLop62(): void
    {
        $s = 'dl-chau-luc-dai-duong-lop-6-2'; $d = 'de';
        $this->quiz($s, 'Sông Nin, một trong những sông dài nhất thế giới, nằm ở châu lục nào?', ['Châu Á', 'Châu Phi', 'Châu Âu', 'Châu Mỹ'], 1, 'Sông Nin chảy qua đông bắc châu Phi, là một trong những sông dài nhất thế giới.', $d);
        $this->quiz($s, 'Kênh đào nào nối Thái Bình Dương với Đại Tây Dương?', ['Kênh Xuy-ê', 'Kênh Pa-na-ma', 'Kênh Co-rin-tơ', 'Kênh Kiel'], 1, 'Kênh đào Pa-na-ma nối Thái Bình Dương với Đại Tây Dương.', $d);
        $this->quiz($s, 'Châu Mỹ gồm mấy châu lục nhỏ?', ['1', '2', '3', '4'], 1, 'Châu Mỹ gồm 2 châu lục: Bắc Mỹ và Nam Mỹ.', $d);
        $this->quiz($s, 'Nước nào có diện tích lớn nhất châu Phi?', ['Ai Cập', 'An-giê-ri', 'Ni-giê-ri-a', 'Xu-đăng'], 1, 'An-giê-ri là nước có diện tích lớn nhất châu Phi.', $d);
        $this->quiz($s, 'Quốc gia nào có diện tích lớn nhất châu Đại Dương?', ['Niu Di-lân', 'Úc', 'Phi-gi', 'Pa-pua Niu Ghi-nê'], 1, 'Úc (Ô-xtrây-li-a) là quốc gia lớn nhất châu Đại Dương.', $d);
        $this->matching($s, 'Nối mỗi châu lục với một đặc điểm khác của nó.', [['Bắc Mỹ', 'Có Hoa Kỳ'], ['Nam Mỹ', 'Có rừng A-ma-dôn'], ['Châu Âu', 'Nhiều nước phát triển'], ['Châu Đại Dương', 'Nhỏ nhất thế giới']], 'Bắc Mỹ có Hoa Kỳ, Nam Mỹ có rừng A-ma-dôn, châu Âu nhiều nước phát triển.', $d);
        $this->matching($s, 'Nối mỗi địa danh với châu lục của nó.', [['Sông Nin', 'Châu Phi'], ['Dãy An-đét', 'Nam Mỹ'], ['Hồ Bai-can', 'Châu Á'], ['Dãy An-pơ', 'Châu Âu']], 'Sông Nin ở châu Phi, dãy An-đét ở Nam Mỹ, hồ Bai-can ở châu Á, dãy An-pơ ở châu Âu.', $d);
        $this->matching($s, 'Nối mỗi châu lục với đại dương lớn giáp nó.', [['Châu Á', 'Thái Bình Dương'], ['Châu Phi', 'Đại Tây Dương'], ['Châu Âu', 'Đại Tây Dương'], ['Úc', 'Ấn Độ Dương']], 'Châu Á giáp Thái Bình Dương, châu Phi và châu Âu giáp Đại Tây Dương, Úc giáp Ấn Độ Dương.', $d);
        $this->matching($s, 'Nối mỗi châu lục với ngôn ngữ phổ biến ở đó.', [['Nam Mỹ', 'Tiếng Tây Ban Nha'], ['Bắc Mỹ', 'Tiếng Anh'], ['Châu Phi', 'Tiếng Pháp, tiếng Anh'], ['Châu Đại Dương', 'Tiếng Anh']], 'Nam Mỹ phổ biến tiếng Tây Ban Nha; Bắc Mỹ và châu Đại Dương phổ biến tiếng Anh.', $d);
        $this->matching($s, 'Nối mỗi hoang mạc với châu lục của nó.', [['Xa-ha-ra', 'Châu Phi'], ['Gô-bi', 'Châu Á'], ['A-ta-ca-ma', 'Nam Mỹ'], ['Hoang mạc Úc', 'Châu Đại Dương']], 'Xa-ha-ra ở châu Phi, Gô-bi ở châu Á, A-ta-ca-ma ở Nam Mỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi châu lục vào nhóm BÁN CẦU BẮC hoặc BÁN CẦU NAM (chủ yếu).', [['Châu Âu', 'BÁN CẦU BẮC'], ['Bắc Mỹ', 'BÁN CẦU BẮC'], ['Châu Đại Dương', 'BÁN CẦU NAM'], ['Nam Cực', 'BÁN CẦU NAM']], 'Châu Âu và Bắc Mỹ chủ yếu ở bán cầu Bắc; châu Đại Dương và Nam Cực ở bán cầu Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm Ở CHÂU PHI hoặc KHÔNG PHẢI CHÂU PHI.', [['Sông Nin', 'Ở CHÂU PHI'], ['Kim tự tháp Giza', 'Ở CHÂU PHI'], ['Vạn Lý Trường Thành', 'KHÔNG PHẢI'], ['Tháp Eiffel', 'KHÔNG PHẢI']], 'Sông Nin và kim tự tháp Giza ở châu Phi; Vạn Lý Trường Thành ở châu Á, tháp Eiffel ở châu Âu.', $d);
        $this->sortQ($s, 'Kéo mỗi châu lục vào nhóm ĐÔNG DÂN hoặc THƯA VẮNG.', [['Châu Á', 'ĐÔNG DÂN'], ['Châu Âu', 'ĐÔNG DÂN'], ['Châu Nam Cực', 'THƯA VẮNG'], ['Châu Đại Dương', 'THƯA VẮNG']], 'Châu Á và châu Âu đông dân; châu Nam Cực và châu Đại Dương thưa vắng.', $d);
        $this->sortQ($s, 'Kéo mỗi đại dương vào nhóm LỚN NHẤT/NHỎ NHẤT hoặc CÒN LẠI.', [['Thái Bình Dương', 'LỚN NHẤT/NHỎ NHẤT'], ['Bắc Băng Dương', 'LỚN NHẤT/NHỎ NHẤT'], ['Đại Tây Dương', 'CÒN LẠI'], ['Ấn Độ Dương', 'CÒN LẠI']], 'Thái Bình Dương lớn nhất, Bắc Băng Dương nhỏ nhất; hai đại dương còn lại ở giữa.', $d);
        $this->sortQ($s, 'Kéo mỗi châu lục vào nhóm CÓ HOANG MẠC LỚN hoặc KHÔNG.', [['Châu Phi', 'CÓ HOANG MẠC LỚN'], ['Châu Á', 'CÓ HOANG MẠC LỚN'], ['Châu Âu', 'KHÔNG CÓ'], ['Nam Cực', 'KHÔNG CÓ']], 'Châu Phi có Xa-ha-ra, châu Á có Gô-bi; châu Âu và Nam Cực không có hoang mạc nóng lớn.', $d);
        $this->fill($s, 'Sông Nin, một trong những sông dài nhất thế giới, nằm ở châu ___.', [[0, 'Phi']], 'Sông Nin chảy qua đông bắc châu Phi.', $d);
        $this->fill($s, 'Kênh đào ___ nối Thái Bình Dương với Đại Tây Dương.', [[0, 'Pa-na-ma']], 'Kênh đào Pa-na-ma nối hai đại dương lớn.', $d);
        $this->fill($s, 'Châu Mỹ gồm hai châu lục nhỏ là Bắc Mỹ và Nam ___.', [[0, 'Mỹ']], 'Châu Mỹ gồm Bắc Mỹ và Nam Mỹ.', $d);
        $this->fill($s, 'Quốc gia lớn nhất châu Đại Dương là ___.', [[0, 'Úc']], 'Úc là quốc gia lớn nhất châu Đại Dương.', $d);
        $this->fill($s, 'Nước có diện tích lớn nhất châu Phi là An-giê-___.', [[0, 'ri']], 'An-giê-ri là nước rộng nhất châu Phi.', $d);
    }

    private function seedDlChauLucDaiDuongLop71(): void
    {
        $s = 'dl-chau-luc-dai-duong-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Con sông nào dài nhất châu Âu?', ['Sông Đa-nuýp', 'Sông Vôn-ga', 'Sông Ranh', 'Sông Xen'], 1, 'Sông Vôn-ga dài khoảng 3 690 km, là sông dài nhất châu Âu.', $d);
        $this->quiz($s, 'Châu Âu nằm ở phía nào của lục địa Á – Âu?', ['Phía đông', 'Phía tây', 'Phía nam', 'Phía bắc'], 1, 'Châu Âu nằm ở phía tây của lục địa Á – Âu.', $d);
        $this->quiz($s, 'Đồng tiền chung của nhiều nước châu Âu có tên là gì?', ['Đô-la', 'Bảng Anh', 'Ơ-rô', 'Phờ-răng'], 2, 'Đồng ơ-rô (euro) là đồng tiền chung của nhiều nước Liên minh châu Âu.', $d);
        $this->quiz($s, 'Nước nào có diện tích lớn nhất châu Âu?', ['Pháp', 'Đức', 'Nga', 'U-crai-na'], 2, 'Nga là nước có diện tích lớn nhất châu Âu (phần lãnh thổ thuộc châu Âu).', $d);
        $this->quiz($s, 'Dãy núi nào là ranh giới tự nhiên giữa châu Âu và châu Á?', ['Dãy An-pơ', 'Dãy Pi-rê-nê', 'Dãy U-ran', 'Dãy Các-pát'], 2, 'Dãy U-ran là ranh giới tự nhiên giữa châu Âu và châu Á.', $d);
        $this->matching($s, 'Nối mỗi địa danh châu Âu với đặc điểm của nó.', [['Sông Vôn-ga', 'Dài nhất châu Âu'], ['Dãy U-ran', 'Ranh giới Âu – Á'], ['Biển Địa Trung Hải', 'Ở phía nam châu Âu'], ['Bán đảo I-bê-ri', 'Có Tây Ban Nha, Bồ Đào Nha']], 'Sông Vôn-ga dài nhất châu Âu, dãy U-ran là ranh giới Âu – Á.', $d);
        $this->matching($s, 'Nối mỗi nước châu Âu với thủ đô của nó.', [['Đức', 'Béc-lin'], ['Ba Lan', 'Vác-xa-va'], ['Hà Lan', 'Am-xtéc-đam'], ['Thụy Điển', 'Xtốc-khôm']], 'Thủ đô: Đức – Béc-lin, Ba Lan – Vác-xa-va, Hà Lan – Am-xtéc-đam, Thụy Điển – Xtốc-khôm.', $d);
        $this->matching($s, 'Nối mỗi ngành kinh tế với đặc điểm ở châu Âu.', [['Công nghiệp', 'Phát triển ở trình độ cao'], ['Du lịch', 'Nguồn thu lớn'], ['Nông nghiệp', 'Hiện đại, năng suất cao'], ['Dịch vụ', 'Chiếm tỉ trọng lớn']], 'Châu Âu có công nghiệp phát triển cao, du lịch là nguồn thu lớn.', $d);
        $this->matching($s, 'Nối mỗi kiểu khí hậu với khu vực ở châu Âu.', [['Ôn đới hải dương', 'Tây Âu'], ['Ôn đới lục địa', 'Đông Âu'], ['Địa Trung Hải', 'Nam Âu'], ['Cận cực', 'Bắc Âu']], 'Tây Âu ôn đới hải dương, Đông Âu ôn đới lục địa, Nam Âu khí hậu Địa Trung Hải.', $d);
        $this->matching($s, 'Nối mỗi nước với công trình nổi tiếng của nó.', [['Pháp', 'Tháp Eiffel'], ['Ý', 'Đấu trường La Mã'], ['Hy Lạp', 'Đền Pác-tê-nông'], ['Anh', 'Đồng hồ Big Ben']], 'Pháp – tháp Eiffel, Ý – đấu trường La Mã, Hy Lạp – đền Pác-tê-nông, Anh – Big Ben.', $d);
        $this->sortQ($s, 'Kéo mỗi quốc gia vào nhóm THUỘC CHÂU ÂU hoặc KHÔNG THUỘC.', [['Đức', 'THUỘC CHÂU ÂU'], ['Ba Lan', 'THUỘC CHÂU ÂU'], ['Ai Cập', 'KHÔNG THUỘC'], ['Nhật Bản', 'KHÔNG THUỘC']], 'Đức và Ba Lan thuộc châu Âu; Ai Cập thuộc châu Phi, Nhật Bản thuộc châu Á.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm Ở CHÂU ÂU hoặc KHÔNG Ở CHÂU ÂU.', [['Tháp Eiffel', 'Ở CHÂU ÂU'], ['Đấu trường La Mã', 'Ở CHÂU ÂU'], ['Vạn Lý Trường Thành', 'KHÔNG Ở CHÂU ÂU'], ['Tượng Nữ thần Tự do', 'KHÔNG Ở CHÂU ÂU']], 'Tháp Eiffel và đấu trường La Mã ở châu Âu; hai địa danh còn lại thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm TỰ NHIÊN hoặc KINH TẾ – XÃ HỘI của châu Âu.', [['Đồng bằng', 'TỰ NHIÊN'], ['Khí hậu ôn đới', 'TỰ NHIÊN'], ['Liên minh châu Âu', 'KINH TẾ – XÃ HỘI'], ['Công nghiệp phát triển', 'KINH TẾ – XÃ HỘI']], 'Đồng bằng, khí hậu ôn đới là tự nhiên; EU và công nghiệp phát triển là kinh tế – xã hội.', $d);
        $this->sortQ($s, 'Kéo mỗi nước vào nhóm TÂY ÂU hoặc ĐÔNG ÂU.', [['Pháp', 'TÂY ÂU'], ['Đức', 'TÂY ÂU'], ['Ba Lan', 'ĐÔNG ÂU'], ['U-crai-na', 'ĐÔNG ÂU']], 'Pháp và Đức ở Tây Âu; Ba Lan và U-crai-na ở Đông Âu.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Châu Âu ở phía tây lục địa Á – Âu', 'ĐÚNG'], ['Nhiều nước châu Âu dùng đồng ơ-rô', 'ĐÚNG'], ['Sông Nin là sông dài nhất châu Âu', 'SAI'], ['Dãy An-pơ ở Bắc Âu', 'SAI']], 'Châu Âu ở phía tây lục địa và nhiều nước dùng ơ-rô là đúng; sông Nin ở châu Phi, An-pơ ở Nam Âu.', $d);
        $this->fill($s, 'Sông dài nhất châu Âu là sông ___.', [[0, 'Vôn-ga']], 'Sông Vôn-ga dài khoảng 3 690 km, dài nhất châu Âu.', $d);
        $this->fill($s, 'Dãy núi ___ là ranh giới tự nhiên giữa châu Âu và châu Á.', [[0, 'U-ran']], 'Dãy U-ran ngăn cách châu Âu với châu Á.', $d);
        $this->fill($s, 'Đồng tiền chung của nhiều nước châu Âu là đồng ___.', [[0, 'ơ-rô']], 'Đồng ơ-rô là tiền chung của nhiều nước EU.', $d);
        $this->fill($s, 'Châu Âu nằm ở phía ___ của lục địa Á – Âu.', [[0, 'tây']], 'Châu Âu nằm ở phía tây lục địa Á – Âu.', $d);
        $this->fill($s, 'Nước có diện tích lớn nhất châu Âu là ___.', [[0, 'Nga']], 'Nga là nước rộng nhất châu Âu.', $d);
    }

    private function seedDlChauLucDaiDuongLop72(): void
    {
        $s = 'dl-chau-luc-dai-duong-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Thác nước cao nhất thế giới nằm ở nước nào thuộc Nam Mỹ?', ['Bra-xin', 'Vê-nê-du-ê-la', 'Pê-ru', 'Cô-lôm-bi-a'], 1, 'Thác An-hen ở Vê-nê-du-ê-la cao 979 m, là thác cao nhất thế giới.', $d);
        $this->quiz($s, 'Hồ nào lớn nhất châu Phi?', ['Hồ Tan-ga-ni-ca', 'Hồ Vic-to-ri-a', 'Hồ Ma-la-uy', 'Hồ Chát'], 1, 'Hồ Vic-to-ri-a là hồ lớn nhất châu Phi.', $d);
        $this->quiz($s, 'Sa mạc A-ta-ca-ma, nơi khô hạn nhất thế giới, nằm ở nước nào?', ['Pê-ru', 'Bô-li-vi-a', 'Chi-lê', 'Ác-hen-ti-na'], 2, 'Sa mạc A-ta-ca-ma nằm ở Chi-lê, là nơi khô hạn nhất thế giới.', $d);
        $this->quiz($s, 'Nước nào đông dân nhất châu Phi?', ['Ai Cập', 'Ni-giê-ri-a', 'Ê-ti-ô-pi-a', 'Nam Phi'], 1, 'Ni-giê-ri-a là nước đông dân nhất châu Phi.', $d);
        $this->quiz($s, 'Dãy núi nào chạy dọc phía tây Bắc Mỹ?', ['Dãy An-đét', 'Dãy Rốc-ki', 'Dãy A-pa-lát', 'Dãy An-pơ'], 1, 'Dãy Rốc-ki chạy dọc phía tây Bắc Mỹ.', $d);
        $this->matching($s, 'Nối mỗi địa danh châu Phi với đặc điểm của nó.', [['Hồ Vic-to-ri-a', 'Hồ lớn nhất châu Phi'], ['Sông Công-gô', 'Sông sâu nhất thế giới'], ['Đảo Ma-đa-gát-xca', 'Đảo lớn ở Ấn Độ Dương'], ['Mũi Hảo Vọng', 'Điểm cực nam châu Phi']], 'Hồ Vic-to-ri-a lớn nhất châu Phi, sông Công-gô sâu nhất thế giới.', $d);
        $this->matching($s, 'Nối mỗi địa danh châu Mỹ với đặc điểm của nó.', [['Thác An-hen', 'Thác cao nhất thế giới'], ['Sa mạc A-ta-ca-ma', 'Nơi khô hạn nhất'], ['Eo đất Pa-na-ma', 'Nối hai châu Mỹ'], ['Ngũ Đại Hồ', 'Hồ nước ngọt lớn ở Bắc Mỹ']], 'Thác An-hen cao nhất thế giới, A-ta-ca-ma khô hạn nhất, Ngũ Đại Hồ ở Bắc Mỹ.', $d);
        $this->matching($s, 'Nối mỗi nước với khu vực của nó ở châu Mỹ.', [['Mê-hi-cô', 'Bắc Mỹ'], ['Cu-ba', 'Trung Mỹ'], ['Pê-ru', 'Nam Mỹ'], ['Ja-mai-ca', 'Trung Mỹ']], 'Mê-hi-cô ở Bắc Mỹ, Cu-ba và Ja-mai-ca ở Trung Mỹ, Pê-ru ở Nam Mỹ.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm dân cư với châu lục tương ứng.', [['Nhiều dân nhập cư', 'Châu Mỹ'], ['Tỉ lệ gia tăng dân số cao', 'Châu Phi'], ['Dân số già', 'Châu Âu'], ['Mật độ dân số thấp', 'Châu Đại Dương']], 'Châu Mỹ nhiều dân nhập cư, châu Phi gia tăng dân số nhanh, châu Âu dân số già.', $d);
        $this->matching($s, 'Nối mỗi nước với thủ đô của nó.', [['Nam Phi', 'Prê-tô-ri-a'], ['Kê-ni-a', 'Nai-rô-bi'], ['Pê-ru', 'Li-ma'], ['Vê-nê-du-ê-la', 'Ca-ra-cát']], 'Nam Phi – Prê-tô-ri-a, Kê-ni-a – Nai-rô-bi, Pê-ru – Li-ma, Vê-nê-du-ê-la – Ca-ra-cát.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm Ở CHÂU PHI hoặc Ở CHÂU MỸ.', [['Kim tự tháp Giza', 'Ở CHÂU PHI'], ['Hồ Vic-to-ri-a', 'Ở CHÂU PHI'], ['Tượng Nữ thần Tự do', 'Ở CHÂU MỸ'], ['Thác An-hen', 'Ở CHÂU MỸ']], 'Kim tự tháp Giza và hồ Vic-to-ri-a ở châu Phi; hai địa danh còn lại ở châu Mỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi nước vào nhóm BẮC MỸ hoặc NAM MỸ.', [['Mê-hi-cô', 'BẮC MỸ'], ['Ca-na-đa', 'BẮC MỸ'], ['Pê-ru', 'NAM MỸ'], ['Chi-lê', 'NAM MỸ']], 'Mê-hi-cô và Ca-na-đa ở Bắc Mỹ; Pê-ru và Chi-lê ở Nam Mỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi dãy núi, hồ vào nhóm BẮC MỸ hoặc NAM MỸ.', [['Dãy Rốc-ki', 'BẮC MỸ'], ['Ngũ Đại Hồ', 'BẮC MỸ'], ['Dãy An-đét', 'NAM MỸ'], ['Rừng A-ma-dôn', 'NAM MỸ']], 'Dãy Rốc-ki và Ngũ Đại Hồ ở Bắc Mỹ; dãy An-đét và rừng A-ma-dôn ở Nam Mỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi con sông vào nhóm Ở CHÂU PHI hoặc Ở CHÂU MỸ.', [['Sông Nin', 'Ở CHÂU PHI'], ['Sông Công-gô', 'Ở CHÂU PHI'], ['Sông A-ma-dôn', 'Ở CHÂU MỸ'], ['Sông Mi-xi-xi-pi', 'Ở CHÂU MỸ']], 'Sông Nin và Công-gô ở châu Phi; sông A-ma-dôn và Mi-xi-xi-pi ở châu Mỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Kênh Pa-na-ma nối hai đại dương', 'ĐÚNG'], ['Dãy An-đét ở Nam Mỹ', 'ĐÚNG'], ['Rừng A-ma-dôn ở châu Phi', 'SAI'], ['Xa-ha-ra ở Nam Mỹ', 'SAI']], 'Kênh Pa-na-ma nối hai đại dương và An-đét ở Nam Mỹ là đúng; rừng A-ma-dôn ở Nam Mỹ, Xa-ha-ra ở châu Phi.', $d);
        $this->fill($s, 'Thác nước cao nhất thế giới An-hen nằm ở châu Nam ___.', [[0, 'Mỹ']], 'Thác An-hen ở Vê-nê-du-ê-la, thuộc Nam Mỹ.', $d);
        $this->fill($s, 'Hồ lớn nhất châu Phi là hồ Vic-to-___.', [[0, 'ri-a']], 'Hồ Vic-to-ri-a là hồ lớn nhất châu Phi.', $d);
        $this->fill($s, 'Nơi khô hạn nhất thế giới là sa mạc A-ta-ca-ma ở nước ___.', [[0, 'Chi-lê']], 'Sa mạc A-ta-ca-ma ở Chi-lê khô hạn nhất thế giới.', $d);
        $this->fill($s, 'Nước đông dân nhất châu Phi là Ni-giê-___.', [[0, 'ri-a']], 'Ni-giê-ri-a đông dân nhất châu Phi.', $d);
        $this->fill($s, 'Dãy núi chạy dọc phía tây Bắc Mỹ là dãy ___.', [[0, 'Rốc-ki']], 'Dãy Rốc-ki chạy dọc phía tây Bắc Mỹ.', $d);
    }

    private function seedDlThuDoCacNuocLop71(): void
    {
        $s = 'dl-thu-do-cac-nuoc-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Thủ đô của Việt Nam là thành phố nào?', ['TP. Hồ Chí Minh', 'Đà Nẵng', 'Hà Nội', 'Hải Phòng'], 2, 'Hà Nội là thủ đô của Việt Nam từ năm 1010 (Thăng Long).', $d);
        $this->quiz($s, 'Thủ đô của Mi-an-ma là thành phố nào?', ['Yangon', 'Mandalay', 'Nây-pi-đô', 'Mawlamyine'], 2, 'Nây-pi-đô là thủ đô mới của Mi-an-ma từ năm 2005.', $d);
        $this->quiz($s, 'Thủ đô của Bru-nây là thành phố nào?', ['Kuala Belait', 'Tutong', 'Ban-đa Xê-ri Bê-ga-oan', 'Seria'], 2, 'Ban-đa Xê-ri Bê-ga-oan là thủ đô của Bru-nây.', $d);
        $this->quiz($s, 'Thủ đô của Đông Ti-mo là thành phố nào?', ['Baucau', 'Pante Macassar', 'Suai', 'Đi-li'], 3, 'Đi-li là thủ đô của Đông Ti-mo.', $d);
        $this->quiz($s, 'Thủ đô của Xin-ga-po là gì?', ['Jurong', 'Xin-ga-po', 'Tampines', 'Woodlands'], 1, 'Xin-ga-po là thành phố – quốc gia, thủ đô cũng mang tên Xin-ga-po.', $d);
        $this->matching($s, 'Nối mỗi nước với thành phố lớn (KHÔNG phải thủ đô) của nó.', [['Việt Nam', 'TP. Hồ Chí Minh'], ['Phi-líp-pin', 'Quê-dôn'], ['Mi-an-ma', 'Yangon'], ['Indonesia', 'Xu-ra-ba-ya']], 'TP. Hồ Chí Minh, Quê-dôn, Yangon và Xu-ra-ba-ya đều lớn nhưng không phải thủ đô.', $d);
        $this->matching($s, 'Nối mỗi nước Đông Nam Á với đặc điểm của nó.', [['Việt Nam', 'Hình chữ S'], ['Thái Lan', 'Xứ sở chùa Vàng'], ['Indonesia', 'Quốc đảo lớn nhất khu vực'], ['Phi-líp-pin', 'Hơn 7 000 đảo']], 'Việt Nam hình chữ S, Thái Lan xứ chùa Vàng, Indonesia quốc đảo lớn nhất, Phi-líp-pin hơn 7 000 đảo.', $d);
        $this->matching($s, 'Nối mỗi nước với ngôn ngữ chính của nó.', [['Việt Nam', 'Tiếng Việt'], ['Thái Lan', 'Tiếng Thái'], ['Indonesia', 'Tiếng Indonesia'], ['Phi-líp-pin', 'Tiếng Phi-líp-pin']], 'Mỗi nước Đông Nam Á có ngôn ngữ chính riêng.', $d);
        $this->matching($s, 'Nối mỗi thủ đô với vị trí của nó.', [['Hà Nội', 'Miền Bắc Việt Nam'], ['Băng Cốc', 'Đồng bằng sông Chao Phraya'], ['Gia-các-ta', 'Đảo Gia-va'], ['Ma-ni-la', 'Đảo Lu-dôn']], 'Hà Nội ở miền Bắc, Băng Cốc ở đồng bằng Chao Phraya, Gia-các-ta ở đảo Gia-va.', $d);
        $this->matching($s, 'Nối mỗi nước với món ăn đặc trưng của nó.', [['Việt Nam', 'Phở'], ['Thái Lan', 'Tom Yum'], ['Indonesia', 'Nasi Goreng'], ['Ma-lai-xi-a', 'Nasi Lemak']], 'Phở của Việt Nam, Tom Yum của Thái Lan, Nasi Goreng của Indonesia.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc KHÔNG PHẢI THỦ ĐÔ.', [['Hà Nội', 'THỦ ĐÔ'], ['Nây-pi-đô', 'THỦ ĐÔ'], ['TP. Hồ Chí Minh', 'KHÔNG PHẢI THỦ ĐÔ'], ['Chiang Mai', 'KHÔNG PHẢI THỦ ĐÔ']], 'Hà Nội và Nây-pi-đô là thủ đô; TP. Hồ Chí Minh và Chiang Mai thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm ĐÔNG NAM Á LỤC ĐỊA hoặc HẢI ĐẢO.', [['Nây-pi-đô', 'LỤC ĐỊA'], ['Viêng Chăn', 'LỤC ĐỊA'], ['Đi-li', 'HẢI ĐẢO'], ['Ban-đa Xê-ri Bê-ga-oan', 'HẢI ĐẢO']], 'Nây-pi-đô và Viêng Chăn ở Đông Nam Á lục địa; Đi-li và Ban-đa Xê-ri Bê-ga-oan ở hải đảo.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm CỦA NƯỚC LÁNG GIỀNG VIỆT NAM hoặc KHÔNG.', [['Viêng Chăn', 'LÁNG GIỀNG'], ['Phnom Penh', 'LÁNG GIỀNG'], ['Băng Cốc', 'KHÔNG PHẢI'], ['Gia-các-ta', 'KHÔNG PHẢI']], 'Lào và Cam-pu-chia là láng giềng của Việt Nam; Thái Lan và Indonesia thì không giáp Việt Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nước – thủ đô vào nhóm ĐÚNG hoặc SAI.', [['Việt Nam – Hà Nội', 'ĐÚNG'], ['Xin-ga-po – Xin-ga-po', 'ĐÚNG'], ['Thái Lan – Chiang Mai', 'SAI'], ['Indonesia – Bali', 'SAI']], 'Thủ đô Thái Lan là Băng Cốc, thủ đô Indonesia là Gia-các-ta.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm VEN BIỂN hoặc TRONG ĐẤT LIỀN.', [['Ma-ni-la', 'VEN BIỂN'], ['Băng Cốc', 'VEN BIỂN'], ['Đi-li', 'VEN BIỂN'], ['Hà Nội', 'TRONG ĐẤT LIỀN'], ['Viêng Chăn', 'TRONG ĐẤT LIỀN'], ['Nây-pi-đô', 'TRONG ĐẤT LIỀN']], 'Ma-ni-la, Băng Cốc, Đi-li ven biển; Hà Nội, Viêng Chăn, Nây-pi-đô trong đất liền.', $d);
        $this->fill($s, 'Thủ đô của Việt Nam là ___.', [[0, 'Hà Nội']], 'Hà Nội là thủ đô của Việt Nam.', $d);
        $this->fill($s, 'Thủ đô của Mi-an-ma là Nây-pi-___.', [[0, 'đô']], 'Nây-pi-đô là thủ đô mới của Mi-an-ma.', $d);
        $this->fill($s, 'Thủ đô của Bru-nây là Ban-đa Xê-ri Bê-ga-___.', [[0, 'oan']], 'Ban-đa Xê-ri Bê-ga-oan là thủ đô của Bru-nây.', $d);
        $this->fill($s, 'Thủ đô của Đông Ti-mo là ___.', [[0, 'Đi-li']], 'Đi-li là thủ đô của Đông Ti-mo.', $d);
        $this->fill($s, 'Xin-ga-po vừa là tên nước vừa là tên ___.', [[0, 'thủ đô']], 'Xin-ga-po là thành phố – quốc gia.', $d);
    }

    private function seedDlThuDoCacNuocLop72(): void
    {
        $s = 'dl-thu-do-cac-nuoc-lop-7-2'; $d = 'de';
        $this->quiz($s, 'Thủ đô của Ca-dắc-xtan là thành phố nào?', ['Almaty', 'A-xta-na', 'Shymkent', 'Karaganda'], 1, 'A-xta-na là thủ đô của Ca-dắc-xtan.', $d);
        $this->quiz($s, 'Thủ đô của I-ran là thành phố nào?', ['Isfahan', 'Shiraz', 'Tê-hê-ran', 'Mashhad'], 2, 'Tê-hê-ran là thủ đô của I-ran.', $d);
        $this->quiz($s, 'Thủ đô của Ả Rập Xê Út là thành phố nào?', ['Giê-đa', 'Ri-át', 'Méc-ca', 'Đam-mam'], 1, 'Ri-át là thủ đô của Ả Rập Xê Út.', $d);
        $this->quiz($s, 'Thủ đô của I-xra-en là thành phố nào?', ['Tel Aviv', 'Haifa', 'Giê-ru-sa-lem', 'Eilat'], 2, 'Giê-ru-sa-lem là thủ đô của I-xra-en.', $d);
        $this->quiz($s, 'Thủ đô của Áp-ga-ni-xtan là thành phố nào?', ['Kandahar', 'Herat', 'Ka-bun', 'Mazar'], 2, 'Ka-bun là thủ đô của Áp-ga-ni-xtan.', $d);
        $this->matching($s, 'Nối mỗi nước Trung Á với thủ đô của nó.', [['Ca-dắc-xtan', 'A-xta-na'], ['U-dơ-bê-ki-xtan', 'Tát-ken'], ['Cư-rơ-gư-xtan', 'Bít-kếch'], ['Mông Cổ', 'Ulan Bator']], 'Ca-dắc-xtan – A-xta-na, U-dơ-bê-ki-xtan – Tát-ken, Cư-rơ-gư-xtan – Bít-kếch, Mông Cổ – Ulan Bator.', $d);
        $this->matching($s, 'Nối mỗi nước Tây Á với thủ đô của nó.', [['I-ran', 'Tê-hê-ran'], ['I-rắc', 'Bát-đa'], ['Xi-ri', 'Đa-mát'], ['Gioóc-đa-ni', 'Am-man']], 'I-ran – Tê-hê-ran, I-rắc – Bát-đa, Xi-ri – Đa-mát, Gioóc-đa-ni – Am-man.', $d);
        $this->matching($s, 'Nối mỗi nước Nam Á với thủ đô của nó.', [['Pa-ki-xtan', 'I-xla-ma-bát'], ['Băng-la-đét', 'Đắc-ca'], ['Nê-pan', 'Kat-man-đu'], ['Bu-tan', 'Thim-phu']], 'Pa-ki-xtan – I-xla-ma-bát, Băng-la-đét – Đắc-ca, Nê-pan – Kat-man-đu, Bu-tan – Thim-phu.', $d);
        $this->matching($s, 'Nối mỗi nước với biệt danh của nó.', [['Nhật Bản', 'Đất nước mặt trời mọc'], ['Trung Quốc', 'Đông dân nhất châu Á'], ['Mông Cổ', 'Xứ thảo nguyên'], ['Hàn Quốc', 'Xứ sở kim chi']], 'Nhật Bản – mặt trời mọc, Mông Cổ – thảo nguyên, Hàn Quốc – kim chi.', $d);
        $this->matching($s, 'Nối mỗi nước với danh lam thắng cảnh nổi tiếng của nó.', [['Trung Quốc', 'Vạn Lý Trường Thành'], ['Nhật Bản', 'Núi Phú Sĩ'], ['Ấn Độ', 'Đền Taj Mahal'], ['Cam-pu-chia', 'Ăng-co Vát']], 'Trung Quốc – Vạn Lý Trường Thành, Nhật Bản – núi Phú Sĩ, Ấn Độ – Taj Mahal.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc THÀNH PHỐ LỚN (không phải thủ đô).', [['Tê-hê-ran', 'THỦ ĐÔ'], ['Ri-át', 'THỦ ĐÔ'], ['Dubai', 'THÀNH PHỐ LỚN'], ['Mumbai', 'THÀNH PHỐ LỚN']], 'Tê-hê-ran và Ri-át là thủ đô; Dubai và Mumbai chỉ là thành phố lớn.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm ĐÔNG Á hoặc NAM Á – TÂY Á.', [['Bình Nhưỡng', 'ĐÔNG Á'], ['Ulan Bator', 'ĐÔNG Á'], ['Ka-bun', 'NAM Á – TÂY Á'], ['Tê-hê-ran', 'NAM Á – TÂY Á']], 'Bình Nhưỡng và Ulan Bator ở Đông Á; Ka-bun và Tê-hê-ran ở Nam Á – Tây Á.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nước – thủ đô vào nhóm ĐÚNG hoặc SAI.', [['I-ran – Tê-hê-ran', 'ĐÚNG'], ['Hàn Quốc – Seoul', 'ĐÚNG'], ['Ả Rập Xê Út – Dubai', 'SAI'], ['Pa-ki-xtan – Karachi', 'SAI']], 'Thủ đô Ả Rập Xê Út là Ri-át, thủ đô Pa-ki-xtan là I-xla-ma-bát.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm TỪNG ĐỔI TÊN/ĐỔI VỊ TRÍ hoặc ỔN ĐỊNH LÂU DÀI.', [['Nây-pi-đô', 'ĐỔI VỊ TRÍ'], ['A-xta-na', 'ĐỔI TÊN'], ['I-xla-ma-bát', 'XÂY MỚI'], ['Bắc Kinh', 'ỔN ĐỊNH'], ['Viêng Chăn', 'ỔN ĐỊNH'], ['Kat-man-đu', 'ỔN ĐỊNH']], 'Nây-pi-đô, A-xta-na, I-xla-ma-bát là thủ đô mới/đổi tên; Bắc Kinh, Viêng Chăn, Kat-man-đu ổn định lâu dài.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm VỪA LÀ TRUNG TÂM KINH TẾ LỚN hoặc KHÔNG PHẢI.', [['Tokyo', 'TRUNG TÂM KINH TẾ LỚN'], ['Seoul', 'TRUNG TÂM KINH TẾ LỚN'], ['Nây-pi-đô', 'KHÔNG PHẢI'], ['I-xla-ma-bát', 'KHÔNG PHẢI']], 'Tokyo và Seoul vừa là thủ đô vừa là trung tâm kinh tế lớn; Nây-pi-đô và I-xla-ma-bát thì không.', $d);
        $this->fill($s, 'Thủ đô của Ca-dắc-xtan là A-xta-___.', [[0, 'na']], 'A-xta-na là thủ đô của Ca-dắc-xtan.', $d);
        $this->fill($s, 'Thủ đô của I-ran là Tê-hê-___.', [[0, 'ran']], 'Tê-hê-ran là thủ đô của I-ran.', $d);
        $this->fill($s, 'Thủ đô của Ả Rập Xê Út là ___.', [[0, 'Ri-át']], 'Ri-át là thủ đô của Ả Rập Xê Út.', $d);
        $this->fill($s, 'Thủ đô của I-xra-en là Giê-ru-sa-___.', [[0, 'lem']], 'Giê-ru-sa-lem là thủ đô của I-xra-en.', $d);
        $this->fill($s, 'Thủ đô của Áp-ga-ni-xtan là Ka-___.', [[0, 'bun']], 'Ka-bun là thủ đô của Áp-ga-ni-xtan.', $d);
    }

    private function seedDlThuDoCacNuocLop81(): void
    {
        $s = 'dl-thu-do-cac-nuoc-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Thủ đô của Anh, nơi có đồng hồ Big Ben, là thành phố nào?', ['Manchester', 'Luân Đôn', 'Liverpool', 'Birmingham'], 1, 'Luân Đôn là thủ đô của Anh, nổi tiếng với đồng hồ Big Ben.', $d);
        $this->quiz($s, 'Thủ đô của Tây Ban Nha là thành phố nào?', ['Barcelona', 'Sevilla', 'Valencia', 'Madrid'], 3, 'Madrid là thủ đô của Tây Ban Nha; Barcelona chỉ là thành phố lớn thứ hai.', $d);
        $this->quiz($s, 'Thủ đô của Hy Lạp là thành phố nào?', ['Thessaloniki', 'Patras', 'A-ten', 'Heraklion'], 2, 'A-ten là thủ đô của Hy Lạp, cái nôi của nền văn minh phương Tây.', $d);
        $this->quiz($s, 'Thủ đô của Thụy Sĩ là thành phố nào?', ['Zurich', 'Geneva', 'Bern', 'Basel'], 2, 'Bern mới là thủ đô của Thụy Sĩ; Zurich và Geneva chỉ là thành phố lớn.', $d);
        $this->quiz($s, 'Thủ đô của U-crai-na là thành phố nào?', ['Kharkiv', 'Odessa', 'Lviv', 'Ki-ép'], 3, 'Ki-ép là thủ đô của U-crai-na.', $d);
        $this->matching($s, 'Nối mỗi nước Tây Âu với thủ đô của nó.', [['Anh', 'Luân Đôn'], ['Ai-len', 'Đu-blin'], ['Bỉ', 'Brúc-xen'], ['Lúc-xăm-bua', 'Lúc-xăm-bua']], 'Anh – Luân Đôn, Ai-len – Đu-blin, Bỉ – Brúc-xen, Lúc-xăm-bua – Lúc-xăm-bua.', $d);
        $this->matching($s, 'Nối mỗi nước Bắc – Đông Âu với thủ đô của nó.', [['Thụy Điển', 'Xtốc-khôm'], ['Na Uy', 'Ô-xlô'], ['Phần Lan', 'Hen-xin-ki'], ['Đan Mạch', 'Cô-pen-ha-gen']], 'Thụy Điển – Xtốc-khôm, Na Uy – Ô-xlô, Phần Lan – Hen-xin-ki, Đan Mạch – Cô-pen-ha-gen.', $d);
        $this->matching($s, 'Nối mỗi nước Nam Âu với thủ đô của nó.', [['Bồ Đào Nha', 'Lít-bon'], ['Crô-a-ti-a', 'Da-grép'], ['Xéc-bi-a', 'Bên-grát'], ['Ru-ma-ni', 'Bu-ca-rét']], 'Bồ Đào Nha – Lít-bon, Crô-a-ti-a – Da-grép, Xéc-bi-a – Bên-grát, Ru-ma-ni – Bu-ca-rét.', $d);
        $this->matching($s, 'Nối mỗi thủ đô với công trình nổi tiếng của nó.', [['Luân Đôn', 'Đồng hồ Big Ben'], ['Madrid', 'Cung điện Hoàng gia'], ['A-ten', 'Đền Pác-tê-nông'], ['Béc-lin', 'Cổng Brandenburg']], 'Luân Đôn – Big Ben, A-ten – đền Pác-tê-nông, Béc-lin – Cổng Brandenburg.', $d);
        $this->matching($s, 'Nối mỗi nước với biệt danh của nó.', [['Thụy Sĩ', 'Xứ sở đồng hồ'], ['Hà Lan', 'Xứ sở hoa tulip'], ['Phần Lan', 'Xứ sở nghìn hồ'], ['Ai-xơ-len', 'Xứ sở băng và lửa']], 'Thụy Sĩ – đồng hồ, Hà Lan – tulip, Phần Lan – nghìn hồ, Ai-xơ-len – băng lửa.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ CHÂU ÂU hoặc KHÔNG PHẢI.', [['Luân Đôn', 'THỦ ĐÔ CHÂU ÂU'], ['Madrid', 'THỦ ĐÔ CHÂU ÂU'], ['Niu Óoc', 'KHÔNG PHẢI'], ['Tokyo', 'KHÔNG PHẢI']], 'Luân Đôn và Madrid là thủ đô châu Âu; Niu Óoc ở châu Mỹ, Tokyo ở châu Á.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm TÂY ÂU hoặc ĐÔNG – BẮC ÂU.', [['Luân Đôn', 'TÂY ÂU'], ['Madrid', 'TÂY ÂU'], ['Xtốc-khôm', 'ĐÔNG – BẮC ÂU'], ['Ki-ép', 'ĐÔNG – BẮC ÂU']], 'Luân Đôn và Madrid ở Tây Âu; Xtốc-khôm và Ki-ép ở Đông – Bắc Âu.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nước – thủ đô vào nhóm ĐÚNG hoặc SAI.', [['Anh – Luân Đôn', 'ĐÚNG'], ['U-crai-na – Ki-ép', 'ĐÚNG'], ['Thụy Sĩ – Zurich', 'SAI'], ['Tây Ban Nha – Barcelona', 'SAI']], 'Thủ đô Thụy Sĩ là Bern, thủ đô Tây Ban Nha là Madrid.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm CÓ SÔNG LỚN CHẢY QUA hoặc KHÔNG RÕ.', [['Luân Đôn', 'CÓ SÔNG LỚN'], ['Pa-ri', 'CÓ SÔNG LỚN'], ['Bu-đa-pét', 'CÓ SÔNG LỚN'], ['Ma-đrít', 'KHÔNG RÕ'], ['A-ten', 'KHÔNG RÕ']], 'Luân Đôn (sông Thames), Pa-ri (sông Xen), Bu-đa-pét (sông Đa-nuýp) có sông lớn chảy qua.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm CỦA NƯỚC ĐÔNG DÂN (trên 40 triệu) hoặc ÍT DÂN.', [['Luân Đôn', 'NƯỚC ĐÔNG DÂN'], ['Ma-đrít', 'NƯỚC ĐÔNG DÂN'], ['Béc-lin', 'NƯỚC ĐÔNG DÂN'], ['Đu-blin', 'NƯỚC ÍT DÂN'], ['Bern', 'NƯỚC ÍT DÂN']], 'Anh, Tây Ban Nha, Đức đông dân; Ai-len và Thụy Sĩ ít dân hơn.', $d);
        $this->fill($s, 'Thủ đô của Anh, nơi có đồng hồ Big Ben, là Luân ___.', [[0, 'Đôn']], 'Luân Đôn là thủ đô của Anh.', $d);
        $this->fill($s, 'Thủ đô của Tây Ban Nha là ___.', [[0, 'Madrid']], 'Madrid là thủ đô của Tây Ban Nha.', $d);
        $this->fill($s, 'Thủ đô của Hy Lạp là ___.', [[0, 'A-ten']], 'A-ten là thủ đô của Hy Lạp.', $d);
        $this->fill($s, 'Thủ đô của Thụy Sĩ là ___, không phải Zurich.', [[0, 'Bern']], 'Bern mới là thủ đô của Thụy Sĩ.', $d);
        $this->fill($s, 'Thủ đô của U-crai-na là ___.', [[0, 'Ki-ép']], 'Ki-ép là thủ đô của U-crai-na.', $d);
    }

    private function seedDlThuDoCacNuocLop82(): void
    {
        $s = 'dl-thu-do-cac-nuoc-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Thủ đô của Mê-hi-cô là thành phố nào?', ['Guadalajara', 'Mê-hi-cô Xi-ti', 'Monterrey', 'Cancun'], 1, 'Mê-hi-cô Xi-ti là thủ đô của Mê-hi-cô.', $d);
        $this->quiz($s, 'Thủ đô của Ác-hen-ti-na là thành phố nào?', ['Cordoba', 'Rosario', 'Bu-ê-nốt Ai-rét', 'Mendoza'], 2, 'Bu-ê-nốt Ai-rét là thủ đô của Ác-hen-ti-na.', $d);
        $this->quiz($s, 'Thủ đô của Cu-ba là thành phố nào?', ['Santiago', 'La Ha-ba-na', 'Camaguey', 'Holguin'], 1, 'La Ha-ba-na là thủ đô của Cu-ba.', $d);
        $this->quiz($s, 'Thủ đô của Niu Di-lân là thành phố nào?', ['Ock-lân', 'Oa-linh-tơn', 'Christchurch', 'Hamilton'], 1, 'Oa-linh-tơn là thủ đô của Niu Di-lân; Ock-lân chỉ là thành phố lớn nhất.', $d);
        $this->quiz($s, 'Thủ đô của Ê-ti-ô-pi-a là thành phố nào?', ['Dire Dawa', 'Gondar', 'Mekelle', 'A-đít A-ba-ba'], 3, 'A-đít A-ba-ba là thủ đô của Ê-ti-ô-pi-a.', $d);
        $this->matching($s, 'Nối mỗi nước châu Mỹ với thủ đô của nó.', [['Mê-hi-cô', 'Mê-hi-cô Xi-ti'], ['Pê-ru', 'Li-ma'], ['Chi-lê', 'Xan-ti-a-gô'], ['Cô-lôm-bi-a', 'Bô-gô-ta']], 'Mê-hi-cô – Mê-hi-cô Xi-ti, Pê-ru – Li-ma, Chi-lê – Xan-ti-a-gô, Cô-lôm-bi-a – Bô-gô-ta.', $d);
        $this->matching($s, 'Nối mỗi nước châu Phi với thủ đô của nó.', [['Ma-rốc', 'Ra-bát'], ['An-giê-ri', 'An-giê'], ['Kê-ni-a', 'Nai-rô-bi'], ['Ga-na', 'Ác-cra']], 'Ma-rốc – Ra-bát, An-giê-ri – An-giê, Kê-ni-a – Nai-rô-bi, Ga-na – Ác-cra.', $d);
        $this->matching($s, 'Nối mỗi quốc đảo châu Đại Dương với thủ đô của nó.', [['Va-nu-a-tu', 'Po Vi-la'], ['Xô-lô-môn', 'Hô-ni-a-ra'], ['Tông-ga', 'Nu-ku-a-lô-pha'], ['Ki-ri-ba-ti', 'Ta-ra-oa']], 'Va-nu-a-tu – Po Vi-la, Xô-lô-môn – Hô-ni-a-ra, Tông-ga – Nu-ku-a-lô-pha.', $d);
        $this->matching($s, 'Nối mỗi nước với châu lục của nó.', [['Mê-hi-cô', 'Bắc Mỹ'], ['Ai Cập', 'Châu Phi'], ['Niu Di-lân', 'Châu Đại Dương'], ['Pê-ru', 'Nam Mỹ']], 'Mê-hi-cô ở Bắc Mỹ, Ai Cập ở châu Phi, Niu Di-lân ở châu Đại Dương, Pê-ru ở Nam Mỹ.', $d);
        $this->matching($s, 'Nối mỗi thủ đô với đặc điểm của nó.', [['Oa-sinh-tơn', 'Có Nhà Trắng'], ['Can-bê-ra', 'Được quy hoạch xây mới'], ['Bra-xi-li-a', 'Xây dựng từ thập niên 1960'], ['Cai-rô', 'Thành phố lớn nhất châu Phi']], 'Oa-sinh-tơn có Nhà Trắng, Can-bê-ra được quy hoạch xây mới, Cai-rô lớn nhất châu Phi.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc THÀNH PHỐ LỚN (không phải thủ đô).', [['Li-ma', 'THỦ ĐÔ'], ['Nai-rô-bi', 'THỦ ĐÔ'], ['Los Angeles', 'THÀNH PHỐ LỚN'], ['Johannesburg', 'THÀNH PHỐ LỚN']], 'Li-ma và Nai-rô-bi là thủ đô; Los Angeles và Johannesburg chỉ là thành phố lớn.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm Ở CHÂU MỸ hoặc Ở CHÂU PHI – ĐẠI DƯƠNG.', [['Mê-hi-cô Xi-ti', 'Ở CHÂU MỸ'], ['Li-ma', 'Ở CHÂU MỸ'], ['A-đít A-ba-ba', 'Ở CHÂU PHI – ĐẠI DƯƠNG'], ['Oa-linh-tơn', 'Ở CHÂU PHI – ĐẠI DƯƠNG']], 'Mê-hi-cô Xi-ti và Li-ma ở châu Mỹ; A-đít A-ba-ba ở châu Phi, Oa-linh-tơn ở châu Đại Dương.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp nước – thủ đô vào nhóm ĐÚNG hoặc SAI.', [['Mê-hi-cô – Mê-hi-cô Xi-ti', 'ĐÚNG'], ['Kê-ni-a – Nai-rô-bi', 'ĐÚNG'], ['Ác-hen-ti-na – Rio de Janeiro', 'SAI'], ['Niu Di-lân – Ock-lân', 'SAI']], 'Thủ đô Ác-hen-ti-na là Bu-ê-nốt Ai-rét, thủ đô Niu Di-lân là Oa-linh-tơn.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm ĐƯỢC XÂY MỚI LÀM THỦ ĐÔ hoặc THỦ ĐÔ LỊCH SỬ.', [['Can-bê-ra', 'XÂY MỚI'], ['Bra-xi-li-a', 'XÂY MỚI'], ['A-bu-gia', 'XÂY MỚI'], ['Cai-rô', 'LỊCH SỬ'], ['Li-ma', 'LỊCH SỬ'], ['La Ha-ba-na', 'LỊCH SỬ']], 'Can-bê-ra, Bra-xi-li-a, A-bu-gia được xây mới làm thủ đô; các thủ đô còn lại có lịch sử lâu đời.', $d);
        $this->sortQ($s, 'Kéo mỗi thủ đô vào nhóm VEN BIỂN hoặc TRONG ĐẤT LIỀN.', [['La Ha-ba-na', 'VEN BIỂN'], ['Oa-linh-tơn', 'VEN BIỂN'], ['Ác-cra', 'VEN BIỂN'], ['Mê-hi-cô Xi-ti', 'TRONG ĐẤT LIỀN'], ['A-đít A-ba-ba', 'TRONG ĐẤT LIỀN'], ['Nai-rô-bi', 'TRONG ĐẤT LIỀN']], 'La Ha-ba-na, Oa-linh-tơn, Ác-cra ven biển; ba thủ đô còn lại trong đất liền.', $d);
        $this->fill($s, 'Thủ đô của Mê-hi-cô là Mê-hi-cô ___.', [[0, 'Xi-ti']], 'Mê-hi-cô Xi-ti là thủ đô của Mê-hi-cô.', $d);
        $this->fill($s, 'Thủ đô của Ác-hen-ti-na là Bu-ê-nốt Ai-___.', [[0, 'rét']], 'Bu-ê-nốt Ai-rét là thủ đô của Ác-hen-ti-na.', $d);
        $this->fill($s, 'Thủ đô của Cu-ba là La Ha-ba-___.', [[0, 'na']], 'La Ha-ba-na là thủ đô của Cu-ba.', $d);
        $this->fill($s, 'Thủ đô của Niu Di-lân là Oa-linh-___.', [[0, 'tơn']], 'Oa-linh-tơn là thủ đô của Niu Di-lân.', $d);
        $this->fill($s, 'Thủ đô của Ê-ti-ô-pi-a là A-đít A-ba-___.', [[0, 'ba']], 'A-đít A-ba-ba là thủ đô của Ê-ti-ô-pi-a.', $d);
    }

    private function seedDlDiaHinhVietNamLop81(): void
    {
        $s = 'dl-dia-hinh-viet-nam-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Dãy Hoàng Liên Sơn nằm ở vùng núi nào của nước ta?', ['Đông Bắc', 'Tây Bắc', 'Trường Sơn Bắc', 'Trường Sơn Nam'], 1, 'Dãy Hoàng Liên Sơn nằm ở vùng núi Tây Bắc.', $d);
        $this->quiz($s, 'Địa hình cánh cung là nét đặc trưng của vùng núi nào?', ['Tây Bắc', 'Đông Bắc', 'Tây Nguyên', 'Trường Sơn Nam'], 1, 'Vùng núi Đông Bắc có địa hình cánh cung chụm lại ở Tam Đảo.', $d);
        $this->quiz($s, 'Đỉnh núi cao thứ hai Việt Nam (sau Fansipan) là đỉnh nào?', ['Pu-xi-lung', 'Ngọc Linh', 'Bạch Mã', 'Lang Biang'], 0, 'Pu-xi-lung (Lai Châu) cao 3 083 m, là đỉnh cao thứ hai Việt Nam.', $d);
        $this->quiz($s, 'Cây công nghiệp nào được trồng nhiều ở vùng trung du Bắc Bộ?', ['Cà phê', 'Chè', 'Cao su', 'Hồ tiêu'], 1, 'Chè là cây công nghiệp trồng nhiều ở trung du Bắc Bộ (Thái Nguyên, Phú Thọ).', $d);
        $this->quiz($s, '"Nóc nhà Nam Bộ" là ngọn núi nào?', ['Núi Bà Đen', 'Núi Chứa Chan', 'Núi Cấm', 'Núi Sam'], 0, 'Núi Bà Đen (Tây Ninh) cao 986 m, được gọi là "nóc nhà Nam Bộ".', $d);
        $this->matching($s, 'Nối mỗi vùng núi với đặc điểm địa hình của nó.', [['Tây Bắc', 'Núi cao, hiểm trở'], ['Đông Bắc', 'Địa hình cánh cung'], ['Trường Sơn Bắc', 'Hẹp ngang, nhiều đèo'], ['Trường Sơn Nam', 'Cao nguyên xếp tầng']], 'Tây Bắc núi cao hiểm trở, Đông Bắc địa hình cánh cung, Trường Sơn Nam nhiều cao nguyên.', $d);
        $this->matching($s, 'Nối mỗi đỉnh núi với tỉnh của nó.', [['Pu-xi-lung', 'Lai Châu'], ['Ngọc Linh', 'Kon Tum'], ['Lang Biang', 'Lâm Đồng'], ['Bạch Mã', 'Thừa Thiên Huế']], 'Pu-xi-lung ở Lai Châu, Ngọc Linh ở Kon Tum, Lang Biang ở Lâm Đồng.', $d);
        $this->matching($s, 'Nối mỗi loại đất với vùng phân bố chủ yếu của nó.', [['Đất feralit đỏ vàng', 'Miền núi'], ['Đất phù sa', 'Đồng bằng'], ['Đất xám', 'Trung du'], ['Đất mặn, đất phèn', 'Ven biển']], 'Đất feralit ở miền núi, đất phù sa ở đồng bằng, đất xám ở trung du.', $d);
        $this->matching($s, 'Nối mỗi cây trồng với vùng thích hợp của nó.', [['Chè', 'Trung du Bắc Bộ'], ['Cà phê', 'Tây Nguyên'], ['Lúa nước', 'Đồng bằng'], ['Cao su', 'Đông Nam Bộ']], 'Chè ở trung du, cà phê ở Tây Nguyên, lúa ở đồng bằng, cao su ở Đông Nam Bộ.', $d);
        $this->matching($s, 'Nối mỗi tỉnh với vùng địa hình của nó.', [['Lai Châu', 'Tây Bắc'], ['Cao Bằng', 'Đông Bắc'], ['Phú Thọ', 'Trung du'], ['Đắk Lắk', 'Tây Nguyên']], 'Lai Châu ở Tây Bắc, Cao Bằng ở Đông Bắc, Phú Thọ ở trung du, Đắk Lắk ở Tây Nguyên.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉnh vào nhóm Ở MIỀN NÚI hoặc Ở ĐỒNG BẰNG.', [['Điện Biên', 'Ở MIỀN NÚI'], ['Kon Tum', 'Ở MIỀN NÚI'], ['Thái Bình', 'Ở ĐỒNG BẰNG'], ['Long An', 'Ở ĐỒNG BẰNG']], 'Điện Biên và Kon Tum ở miền núi; Thái Bình và Long An ở đồng bằng.', $d);
        $this->sortQ($s, 'Kéo mỗi dãy núi vào nhóm TÂY BẮC hoặc TRƯỜNG SƠN.', [['Hoàng Liên Sơn', 'TÂY BẮC'], ['Pu-xi-lung', 'TÂY BẮC'], ['Bạch Mã', 'TRƯỜNG SƠN'], ['Ngọc Linh', 'TRƯỜNG SƠN']], 'Hoàng Liên Sơn và Pu-xi-lung ở Tây Bắc; Bạch Mã và Ngọc Linh thuộc Trường Sơn.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm VÙNG NÚI TÂY BẮC hoặc ĐÔNG BẮC.', [['Núi cao nhất nước', 'TÂY BẮC'], ['Hướng tây bắc – đông nam', 'TÂY BẮC'], ['Địa hình cánh cung', 'ĐÔNG BẮC'], ['Núi thấp, đồi thoải', 'ĐÔNG BẮC']], 'Tây Bắc núi cao nhất, hướng tây bắc – đông nam; Đông Bắc địa hình cánh cung, núi thấp.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm PHÙ HỢP VỚI MIỀN NÚI hoặc ĐỒNG BẰNG.', [['Trồng rừng', 'MIỀN NÚI'], ['Chăn nuôi gia súc lớn', 'MIỀN NÚI'], ['Trồng lúa nước', 'ĐỒNG BẰNG'], ['Nuôi trồng thủy sản', 'ĐỒNG BẰNG']], 'Miền núi phù hợp trồng rừng, chăn nuôi; đồng bằng phù hợp lúa nước, thủy sản.', $d);
        $this->sortQ($s, 'Kéo mỗi vùng vào nhóm ĐẤT ĐỎ BA-DAN hoặc ĐẤT PHÙ SA.', [['Tây Nguyên', 'ĐẤT ĐỎ BA-DAN'], ['Đông Nam Bộ', 'ĐẤT ĐỎ BA-DAN'], ['Đồng bằng sông Hồng', 'ĐẤT PHÙ SA'], ['Đồng bằng sông Cửu Long', 'ĐẤT PHÙ SA']], 'Tây Nguyên và Đông Nam Bộ có đất đỏ ba-dan; hai đồng bằng có đất phù sa.', $d);
        $this->fill($s, 'Dãy Hoàng Liên Sơn nằm ở vùng núi Tây ___.', [[0, 'Bắc']], 'Dãy Hoàng Liên Sơn nằm ở vùng núi Tây Bắc.', $d);
        $this->fill($s, 'Địa hình cánh cung là nét đặc trưng của vùng núi Đông ___.', [[0, 'Bắc']], 'Vùng núi Đông Bắc có địa hình cánh cung.', $d);
        $this->fill($s, 'Đỉnh núi cao thứ hai Việt Nam là Pu-xi-___.', [[0, 'lung']], 'Pu-xi-lung cao 3 083 m, cao thứ hai sau Fansipan.', $d);
        $this->fill($s, 'Cây công nghiệp trồng nhiều ở trung du Bắc Bộ là cây ___.', [[0, 'chè']], 'Chè trồng nhiều ở trung du Bắc Bộ.', $d);
        $this->fill($s, '"Nóc nhà Nam Bộ" là núi Bà ___, ở tỉnh Tây Ninh.', [[0, 'Đen']], 'Núi Bà Đen được gọi là "nóc nhà Nam Bộ".', $d);
    }

    private function seedDlDiaHinhVietNamLop82(): void
    {
        $s = 'dl-dia-hinh-viet-nam-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Vịnh nào ở Quảng Ninh được UNESCO công nhận di sản thiên nhiên thế giới?', ['Vịnh Lăng Cô', 'Vịnh Hạ Long', 'Vịnh Vân Phong', 'Vịnh Nha Trang'], 1, 'Vịnh Hạ Long (Quảng Ninh) là di sản thiên nhiên thế giới.', $d);
        $this->quiz($s, 'Đảo nào lớn nhất Việt Nam?', ['Cát Bà', 'Phú Quốc', 'Lý Sơn', 'Côn Đảo'], 1, 'Phú Quốc (Kiên Giang) là đảo lớn nhất Việt Nam.', $d);
        $this->quiz($s, 'Vùng biển Việt Nam thuộc đại dương nào?', ['Đại Tây Dương', 'Ấn Độ Dương', 'Thái Bình Dương', 'Bắc Băng Dương'], 2, 'Vùng biển Việt Nam là một phần của Thái Bình Dương (qua Biển Đông).', $d);
        $this->quiz($s, 'Bãi biển nào nổi tiếng nhất ở Đà Nẵng?', ['Mỹ Khê', 'Nha Trang', 'Mũi Né', 'Đồ Sơn'], 0, 'Bãi biển Mỹ Khê là bãi biển nổi tiếng nhất Đà Nẵng.', $d);
        $this->quiz($s, 'Cảng biển nào lớn nhất ở miền Trung?', ['Cảng Quy Nhơn', 'Cảng Đà Nẵng', 'Cảng Nha Trang', 'Cảng Dung Quất'], 1, 'Cảng Đà Nẵng là cảng biển lớn nhất miền Trung.', $d);
        $this->matching($s, 'Nối mỗi đồng bằng với đặc điểm khác của nó.', [['Đồng bằng sông Cửu Long', 'Rộng nhất'], ['Đồng bằng sông Hồng', 'Thâm canh cao'], ['Duyên hải miền Trung', 'Hẹp ngang'], ['Tây Nam Bộ', 'Nhiều kênh rạch']], 'ĐBSCL rộng nhất, ĐBSH thâm canh cao, duyên hải miền Trung hẹp ngang.', $d);
        $this->matching($s, 'Nối mỗi đảo với đặc điểm của nó.', [['Phú Quốc', 'Đảo lớn nhất'], ['Cát Bà', 'Vườn quốc gia biển'], ['Lý Sơn', 'Đảo núi lửa'], ['Côn Đảo', 'Di tích lịch sử']], 'Phú Quốc lớn nhất, Lý Sơn là đảo núi lửa, Côn Đảo gắn với di tích lịch sử.', $d);
        $this->matching($s, 'Nối mỗi vũng, vịnh với đặc điểm của nó.', [['Vịnh Hạ Long', 'Di sản thế giới'], ['Vịnh Lăng Cô', 'Vịnh đẹp thế giới'], ['Vũng Tàu', 'Du lịch biển'], ['Vịnh Vân Phong', 'Vịnh kín gió']], 'Vịnh Hạ Long là di sản thế giới, vịnh Lăng Cô là vịnh đẹp thế giới.', $d);
        $this->matching($s, 'Nối mỗi nguồn lợi biển với ví dụ của nó.', [['Hải sản', 'Tôm, cá'], ['Du lịch', 'Nghỉ dưỡng biển'], ['Giao thông', 'Cảng biển'], ['Khoáng sản', 'Dầu khí, muối']], 'Biển cho hải sản, du lịch, giao thông cảng biển và khoáng sản như dầu khí.', $d);
        $this->matching($s, 'Nối mỗi tỉnh với điểm du lịch biển của nó.', [['Quảng Ninh', 'Vịnh Hạ Long'], ['Khánh Hòa', 'Vịnh Nha Trang'], ['Bình Thuận', 'Mũi Né'], ['Kiên Giang', 'Đảo Phú Quốc']], 'Quảng Ninh – Hạ Long, Khánh Hòa – Nha Trang, Bình Thuận – Mũi Né, Kiên Giang – Phú Quốc.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm ĐỒNG BẰNG SÔNG HỒNG hoặc SÔNG CỬU LONG.', [['Sông Hồng', 'ĐỒNG BẰNG SÔNG HỒNG'], ['Đê điều', 'ĐỒNG BẰNG SÔNG HỒNG'], ['Sông Cửu Long', 'ĐỒNG BẰNG SÔNG CỬU LONG'], ['Kênh rạch', 'ĐỒNG BẰNG SÔNG CỬU LONG']], 'ĐBSH có sông Hồng và hệ thống đê điều; ĐBSCL có sông Cửu Long và kênh rạch chằng chịt.', $d);
        $this->sortQ($s, 'Kéo mỗi đảo vào nhóm ĐẢO GẦN BỜ hoặc QUẦN ĐẢO XA BỜ.', [['Cát Bà', 'ĐẢO GẦN BỜ'], ['Lý Sơn', 'ĐẢO GẦN BỜ'], ['Hoàng Sa', 'QUẦN ĐẢO XA BỜ'], ['Trường Sa', 'QUẦN ĐẢO XA BỜ']], 'Cát Bà và Lý Sơn là đảo gần bờ; Hoàng Sa và Trường Sa là quần đảo xa bờ.', $d);
        $this->sortQ($s, 'Kéo mỗi lợi ích của biển vào nhóm KINH TẾ hoặc QUỐC PHÒNG.', [['Đánh bắt hải sản', 'KINH TẾ'], ['Du lịch biển', 'KINH TẾ'], ['Căn cứ hải quân', 'QUỐC PHÒNG'], ['Bảo vệ chủ quyền biển đảo', 'QUỐC PHÒNG']], 'Biển mang lại lợi ích kinh tế và có vai trò quốc phòng quan trọng.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉnh vào nhóm Ở ĐỒNG BẰNG hoặc VEN BIỂN – HẢI ĐẢO.', [['Nam Định', 'Ở ĐỒNG BẰNG'], ['An Giang', 'Ở ĐỒNG BẰNG'], ['Bình Thuận', 'VEN BIỂN – HẢI ĐẢO'], ['Quảng Ngãi', 'VEN BIỂN – HẢI ĐẢO']], 'Nam Định và An Giang ở đồng bằng; Bình Thuận và Quảng Ngãi ven biển.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Phú Quốc là đảo lớn nhất Việt Nam', 'ĐÚNG'], ['Bờ biển Việt Nam dài hơn 3 260 km', 'ĐÚNG'], ['Vịnh Hạ Long ở miền Trung', 'SAI'], ['Hoàng Sa ở phía nam Tổ quốc', 'SAI']], 'Phú Quốc lớn nhất và bờ biển dài hơn 3 260 km là đúng; Hạ Long ở miền Bắc, Hoàng Sa ở biển miền Trung.', $d);
        $this->fill($s, 'Vịnh được UNESCO công nhận di sản thiên nhiên thế giới ở Quảng Ninh là vịnh Hạ ___.', [[0, 'Long']], 'Vịnh Hạ Long là di sản thiên nhiên thế giới.', $d);
        $this->fill($s, 'Đảo lớn nhất Việt Nam là đảo Phú ___.', [[0, 'Quốc']], 'Phú Quốc là đảo lớn nhất Việt Nam.', $d);
        $this->fill($s, 'Bãi biển nổi tiếng của Đà Nẵng là bãi biển Mỹ ___.', [[0, 'Khê']], 'Bãi biển Mỹ Khê nổi tiếng ở Đà Nẵng.', $d);
        $this->fill($s, 'Vùng biển Việt Nam thuộc ___ Bình Dương.', [[0, 'Thái']], 'Vùng biển Việt Nam thuộc Thái Bình Dương.', $d);
        $this->fill($s, 'Cảng biển lớn nhất miền Trung là cảng Đà ___.', [[0, 'Nẵng']], 'Cảng Đà Nẵng lớn nhất miền Trung.', $d);
    }

    private function seedDlDiaHinhVietNamLop91(): void
    {
        $s = 'dl-dia-hinh-viet-nam-lop-9-1'; $d = 'trung_binh';
        $this->quiz($s, 'Mỏ sắt lớn nhất Việt Nam nằm ở đâu?', ['Thái Nguyên', 'Thạch Khê – Hà Tĩnh', 'Lào Cai', 'Cao Bằng'], 1, 'Mỏ sắt Thạch Khê (Hà Tĩnh) là mỏ sắt lớn nhất Việt Nam.', $d);
        $this->quiz($s, 'A-pa-tít, nguyên liệu sản xuất phân bón, tập trung ở đâu?', ['Lào Cai', 'Quảng Ninh', 'Thái Nguyên', 'Phú Thọ'], 0, 'A-pa-tít tập trung ở Lào Cai, dùng sản xuất phân lân.', $d);
        $this->quiz($s, 'Sa khoáng ti-tan phân bố nhiều ở vùng nào?', ['Tây Nguyên', 'Ven biển miền Trung', 'Đồng bằng sông Hồng', 'Miền núi phía Bắc'], 1, 'Ti-tan phân bố nhiều ở ven biển miền Trung (Bình Thuận, Ninh Thuận).', $d);
        $this->quiz($s, 'Mỏ vàng nổi tiếng ở Quảng Nam có tên là gì?', ['Bồng Miêu', 'Thạch Khê', 'Cẩm Phả', 'Nà Rụa'], 0, 'Mỏ vàng Bồng Miêu (Quảng Nam) là mỏ vàng nổi tiếng của Việt Nam.', $d);
        $this->quiz($s, 'Đá vôi là nguyên liệu chính để sản xuất gì?', ['Thép', 'Xi măng', 'Phân bón', 'Giấy'], 1, 'Đá vôi là nguyên liệu chính để sản xuất xi măng.', $d);
        $this->matching($s, 'Nối mỗi khoáng sản với nơi phân bố chính của nó.', [['Sắt', 'Thạch Khê – Hà Tĩnh'], ['A-pa-tít', 'Lào Cai'], ['Ti-tan', 'Ven biển miền Trung'], ['Vàng', 'Bồng Miêu – Quảng Nam']], 'Sắt ở Thạch Khê, a-pa-tít ở Lào Cai, ti-tan ở ven biển miền Trung, vàng ở Bồng Miêu.', $d);
        $this->matching($s, 'Nối mỗi khoáng sản với công dụng chính của nó.', [['Than đá', 'Nhiên liệu, phát điện'], ['Sắt', 'Luyện thép'], ['A-pa-tít', 'Sản xuất phân bón'], ['Đá vôi', 'Sản xuất xi măng']], 'Than đá làm nhiên liệu, sắt luyện thép, a-pa-tít làm phân bón, đá vôi làm xi măng.', $d);
        $this->matching($s, 'Nối mỗi loại tài nguyên với tính chất của nó.', [['Rừng', 'Tái tạo được'], ['Đất', 'Tái tạo được'], ['Dầu khí', 'Không tái tạo'], ['Than đá', 'Không tái tạo']], 'Rừng và đất tái tạo được; dầu khí và than đá không tái tạo được.', $d);
        $this->matching($s, 'Nối mỗi mỏ với khoáng sản của nó.', [['Thạch Khê', 'Sắt'], ['Cẩm Phả', 'Than'], ['Lào Cai', 'A-pa-tít'], ['Bồng Miêu', 'Vàng']], 'Thạch Khê – sắt, Cẩm Phả – than, Lào Cai – a-pa-tít, Bồng Miêu – vàng.', $d);
        $this->matching($s, 'Nối mỗi ngành công nghiệp với khoáng sản nó sử dụng.', [['Luyện kim', 'Sắt, than'], ['Phân bón', 'A-pa-tít'], ['Xi măng', 'Đá vôi'], ['Nhiệt điện', 'Than, dầu khí']], 'Luyện kim dùng sắt than, phân bón dùng a-pa-tít, xi măng dùng đá vôi.', $d);
        $this->sortQ($s, 'Kéo mỗi khoáng sản vào nhóm NĂNG LƯỢNG hoặc KIM LOẠI.', [['Dầu khí', 'NĂNG LƯỢNG'], ['Than đá', 'NĂNG LƯỢNG'], ['Sắt', 'KIM LOẠI'], ['Vàng', 'KIM LOẠI']], 'Dầu khí và than đá là khoáng sản năng lượng; sắt và vàng là kim loại.', $d);
        $this->sortQ($s, 'Kéo mỗi tài nguyên vào nhóm TÁI TẠO ĐƯỢC hoặc KHÔNG TÁI TẠO.', [['Rừng', 'TÁI TẠO ĐƯỢC'], ['Nước', 'TÁI TẠO ĐƯỢC'], ['Than đá', 'KHÔNG TÁI TẠO'], ['Dầu mỏ', 'KHÔNG TÁI TẠO']], 'Rừng và nước tái tạo được; than đá và dầu mỏ không tái tạo.', $d);
        $this->sortQ($s, 'Kéo mỗi khoáng sản vào nhóm Ở MIỀN BẮC hoặc Ở MIỀN NAM – TÂY NGUYÊN.', [['Sắt Thạch Khê', 'Ở MIỀN BẮC'], ['A-pa-tít Lào Cai', 'Ở MIỀN BẮC'], ['Bô-xít Tây Nguyên', 'Ở MIỀN NAM – TÂY NGUYÊN'], ['Dầu khí thềm lục địa Nam', 'Ở MIỀN NAM – TÂY NGUYÊN']], 'Sắt và a-pa-tít ở miền Bắc; bô-xít và dầu khí ở Tây Nguyên, thềm lục địa phía Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm KHAI THÁC HỢP LÍ hoặc LÃNG PHÍ.', [['Dùng tiết kiệm', 'KHAI THÁC HỢP LÍ'], ['Tái chế', 'KHAI THÁC HỢP LÍ'], ['Khai thác bừa bãi', 'LÃNG PHÍ'], ['Bỏ phí tài nguyên', 'LÃNG PHÍ']], 'Dùng tiết kiệm và tái chế là hợp lí; khai thác bừa bãi là lãng phí.', $d);
        $this->sortQ($s, 'Kéo mỗi khoáng sản vào nhóm KIM LOẠI hoặc PHI KIM LOẠI.', [['Sắt', 'KIM LOẠI'], ['Vàng', 'KIM LOẠI'], ['Bô-xít', 'KIM LOẠI'], ['Than đá', 'PHI KIM LOẠI'], ['Đá vôi', 'PHI KIM LOẠI'], ['A-pa-tít', 'PHI KIM LOẠI']], 'Sắt, vàng, bô-xít là kim loại; than đá, đá vôi, a-pa-tít là phi kim loại.', $d);
        $this->fill($s, 'Mỏ sắt lớn nhất Việt Nam là Thạch Khê, ở tỉnh Hà ___.', [[0, 'Tĩnh']], 'Mỏ sắt Thạch Khê ở Hà Tĩnh.', $d);
        $this->fill($s, 'A-pa-tít, nguyên liệu sản xuất phân bón, tập trung ở ___ Cai.', [[0, 'Lào']], 'A-pa-tít tập trung ở Lào Cai.', $d);
        $this->fill($s, 'Sa khoáng ti-tan phân bố nhiều ở ven biển miền ___.', [[0, 'Trung']], 'Ti-tan phân bố nhiều ở ven biển miền Trung.', $d);
        $this->fill($s, 'Mỏ vàng nổi tiếng ở Quảng Nam là Bồng ___.', [[0, 'Miêu']], 'Mỏ vàng Bồng Miêu ở Quảng Nam.', $d);
        $this->fill($s, 'Đá vôi là nguyên liệu chính để sản xuất xi ___.', [[0, 'măng']], 'Đá vôi dùng sản xuất xi măng.', $d);
    }

    private function seedDlDiaHinhVietNamLop92(): void
    {
        $s = 'dl-dia-hinh-viet-nam-lop-9-2'; $d = 'kho';
        $this->quiz($s, 'Rừng có vai trò gì trong việc chống biến đổi khí hậu?', ['Thải khí CO2', 'Hấp thụ khí CO2', 'Làm tăng nhiệt độ', 'Gây mưa axit'], 1, 'Rừng hấp thụ khí CO2, giúp chống biến đổi khí hậu.', $d);
        $this->quiz($s, 'Nguồn năng lượng nào sau đây gây ô nhiễm môi trường khi sử dụng?', ['Mặt trời', 'Gió', 'Than đá', 'Thủy triều'], 2, 'Than đá khi đốt thải nhiều khí gây ô nhiễm; mặt trời, gió là năng lượng sạch.', $d);
        $this->quiz($s, 'Hiện tượng nào là biểu hiện rõ của biến đổi khí hậu?', ['Nước biển dâng', 'Động đất', 'Núi lửa phun', 'Sóng thần'], 0, 'Nước biển dâng do băng tan là biểu hiện rõ của biến đổi khí hậu.', $d);
        $this->quiz($s, 'Tầng ô-dôn bị thủng gây ra hậu quả gì?', ['Mưa nhiều hơn', 'Tia cực tím chiếu mạnh xuống Trái Đất', 'Trái Đất lạnh đi', 'Gió mạnh hơn'], 1, 'Tầng ô-dôn thủng làm tia cực tím chiếu mạnh xuống, gây hại cho sinh vật.', $d);
        $this->quiz($s, 'Ngày Môi trường thế giới là ngày nào?', ['5/6', '22/4', '1/6', '20/3'], 0, 'Ngày 5/6 hằng năm là Ngày Môi trường thế giới.', $d);
        $this->matching($s, 'Nối mỗi vấn đề môi trường với nguyên nhân chính của nó.', [['Ô nhiễm không khí', 'Khí thải xe cộ'], ['Ô nhiễm nguồn nước', 'Nước thải chưa xử lí'], ['Rác thải nhựa', 'Túi ni-lông'], ['Xói mòn đất', 'Chặt phá rừng']], 'Khí thải gây ô nhiễm không khí, nước thải gây ô nhiễm nguồn nước, chặt rừng gây xói mòn.', $d);
        $this->matching($s, 'Nối mỗi biện pháp với mục đích của nó.', [['Trồng rừng', 'Chống xói mòn'], ['Xử lí nước thải', 'Bảo vệ nguồn nước'], ['Dùng túi vải', 'Giảm rác nhựa'], ['Tiết kiệm điện', 'Giảm khí thải']], 'Trồng rừng chống xói mòn, xử lí nước thải bảo vệ nguồn nước, túi vải giảm rác nhựa.', $d);
        $this->matching($s, 'Nối mỗi biểu hiện với hậu quả của biến đổi khí hậu.', [['Băng tan', 'Nước biển dâng'], ['Nắng nóng kéo dài', 'Hạn hán'], ['Mưa cực đoan', 'Lũ lụt'], ['Bão mạnh hơn', 'Thiệt hại lớn']], 'Băng tan gây nước biển dâng, nắng nóng gây hạn hán, mưa cực đoan gây lũ lụt.', $d);
        $this->matching($s, 'Nối mỗi hành động với nhóm của nó.', [['Đi xe đạp', 'Giao thông xanh'], ['Phân loại rác', 'Tái chế'], ['Tắt điện khi ra khỏi phòng', 'Tiết kiệm năng lượng'], ['Trồng cây xanh', 'Tăng mảng xanh']], 'Đi xe đạp là giao thông xanh, phân loại rác giúp tái chế, tắt điện tiết kiệm năng lượng.', $d);
        $this->matching($s, 'Nối mỗi chất gây ô nhiễm với nguồn phát sinh của nó.', [['Khí CO2', 'Đốt than đá'], ['Rác nhựa', 'Túi ni-lông'], ['Thuốc trừ sâu', 'Sản xuất nông nghiệp'], ['Nước thải', 'Khu công nghiệp']], 'Đốt than thải CO2, túi ni-lông thành rác nhựa, nông nghiệp dùng thuốc trừ sâu.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm BẢO VỆ MÔI TRƯỜNG hoặc GÂY Ô NHIỄM.', [['Trồng cây', 'BẢO VỆ MÔI TRƯỜNG'], ['Phân loại rác', 'BẢO VỆ MÔI TRƯỜNG'], ['Xả rác bừa bãi', 'GÂY Ô NHIỄM'], ['Đốt rác ngoài trời', 'GÂY Ô NHIỄM']], 'Trồng cây và phân loại rác bảo vệ môi trường; xả rác và đốt rác gây ô nhiễm.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm DO BIẾN ĐỔI KHÍ HẬU hoặc KHÔNG PHẢI.', [['Nước biển dâng', 'DO BIẾN ĐỔI KHÍ HẬU'], ['Băng tan', 'DO BIẾN ĐỔI KHÍ HẬU'], ['Động đất', 'KHÔNG PHẢI'], ['Núi lửa phun', 'KHÔNG PHẢI']], 'Nước biển dâng và băng tan do biến đổi khí hậu; động đất, núi lửa do nội lực.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn năng lượng vào nhóm SẠCH hoặc GÂY Ô NHIỄM.', [['Mặt trời', 'SẠCH'], ['Gió', 'SẠCH'], ['Than đá', 'GÂY Ô NHIỄM'], ['Dầu mỏ', 'GÂY Ô NHIỄM']], 'Mặt trời và gió là năng lượng sạch; than đá và dầu mỏ gây ô nhiễm.', $d);
        $this->sortQ($s, 'Kéo mỗi vùng vào nhóm CHỊU ẢNH HƯỞNG NẶNG hoặc ÍT của nước biển dâng.', [['Đồng bằng sông Cửu Long', 'ẢNH HƯỞNG NẶNG'], ['Cà Mau', 'ẢNH HƯỞNG NẶNG'], ['Tây Nguyên', 'ẢNH HƯỞNG ÍT'], ['Hà Giang', 'ẢNH HƯỞNG ÍT']], 'ĐBSCL và Cà Mau thấp, chịu ảnh hưởng nặng; Tây Nguyên và Hà Giang ở cao, ít ảnh hưởng.', $d);
        $this->sortQ($s, 'Kéo mỗi loại rác vào nhóm TÁI CHẾ ĐƯỢC hoặc KHÓ TÁI CHẾ.', [['Chai nhựa', 'TÁI CHẾ ĐƯỢC'], ['Giấy', 'TÁI CHẾ ĐƯỢC'], ['Pin cũ', 'KHÓ TÁI CHẾ'], ['Túi ni-lông bẩn', 'KHÓ TÁI CHẾ']], 'Chai nhựa và giấy tái chế được; pin cũ và túi ni-lông bẩn khó tái chế.', $d);
        $this->fill($s, 'Rừng giúp hấp thụ khí ___, chống biến đổi khí hậu.', [[0, 'CO2']], 'Rừng hấp thụ khí CO2, giảm hiệu ứng nhà kính.', $d);
        $this->fill($s, 'Ngày Môi trường thế giới là ngày 5 tháng ___.', [[0, '6']], 'Ngày 5/6 là Ngày Môi trường thế giới.', $d);
        $this->fill($s, 'Tầng ô-dôn bị thủng làm tia ___ chiếu mạnh xuống Trái Đất.', [[0, 'cực tím']], 'Tia cực tím gây hại cho da và sinh vật.', $d);
        $this->fill($s, 'Đi xe đạp thay xe máy giúp giảm khí ___.', [[0, 'thải']], 'Xe đạp không thải khí, bảo vệ môi trường.', $d);
        $this->fill($s, 'Phân loại rác giúp việc tái ___ dễ dàng hơn.', [[0, 'chế']], 'Phân loại rác tại nguồn giúp tái chế hiệu quả.', $d);
    }

    private function seedDiaLyThpt10Lop101(): void
    {
        $s = 'dia-ly-thpt-10-lop-10-1'; $d = 'trung_binh';
        $this->quiz($s, 'Hành tinh nào lớn nhất trong Hệ Mặt Trời?', ['Sao Thổ', 'Sao Mộc', 'Sao Hỏa', 'Trái Đất'], 1, 'Sao Mộc là hành tinh lớn nhất Hệ Mặt Trời.', $d);
        $this->quiz($s, 'Hành tinh nào có vành đai đẹp nhất Hệ Mặt Trời?', ['Sao Mộc', 'Sao Hỏa', 'Sao Thổ', 'Sao Kim'], 2, 'Sao Thổ có hệ thống vành đai đẹp nhất Hệ Mặt Trời.', $d);
        $this->quiz($s, 'Mặt Trời là loại thiên thể nào?', ['Hành tinh', 'Vệ tinh', 'Ngôi sao', 'Tiểu hành tinh'], 2, 'Mặt Trời là một ngôi sao ở trung tâm Hệ Mặt Trời.', $d);
        $this->quiz($s, 'Ánh sáng từ Mặt Trời đến Trái Đất mất khoảng bao lâu?', ['8 giây', '8 phút', '8 giờ', '8 ngày'], 1, 'Ánh sáng Mặt Trời mất khoảng 8 phút để đến Trái Đất.', $d);
        $this->quiz($s, 'Hành tinh nào được mệnh danh là "hành tinh đỏ"?', ['Sao Kim', 'Sao Thủy', 'Sao Hỏa', 'Sao Mộc'], 2, 'Sao Hỏa có màu đỏ nên được gọi là "hành tinh đỏ".', $d);
        $this->matching($s, 'Nối mỗi hành tinh với biệt danh của nó.', [['Sao Hỏa', 'Hành tinh đỏ'], ['Sao Thổ', 'Chúa tể của các vành đai'], ['Sao Kim', 'Ngôi sao mai'], ['Trái Đất', 'Hành tinh xanh']], 'Sao Hỏa – hành tinh đỏ, Sao Thổ – vành đai, Sao Kim – sao mai, Trái Đất – hành tinh xanh.', $d);
        $this->matching($s, 'Nối mỗi hành tinh với màu sắc đặc trưng của nó.', [['Sao Hỏa', 'Đỏ'], ['Sao Kim', 'Vàng sáng'], ['Sao Hải Vương', 'Xanh lam'], ['Trái Đất', 'Xanh dương']], 'Sao Hỏa đỏ, Sao Kim vàng sáng, Sao Hải Vương xanh lam, Trái Đất xanh dương.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với định nghĩa của nó.', [['Hệ Mặt Trời', 'Mặt Trời và các thiên thể quay quanh nó'], ['Quỹ đạo', 'Đường chuyển động của hành tinh'], ['Vệ tinh', 'Thiên thể quay quanh hành tinh'], ['Tiểu hành tinh', 'Thiên thể nhỏ giữa Sao Hỏa và Sao Mộc']], 'Hệ Mặt Trời gồm Mặt Trời và các thiên thể quay quanh nó.', $d);
        $this->matching($s, 'Nối mỗi hành tinh với đặc điểm nổi bật khác của nó.', [['Sao Mộc', 'Lớn nhất'], ['Sao Thổ', 'Vành đai đẹp nhất'], ['Sao Thiên Vương', 'Tự quay nghiêng'], ['Sao Hải Vương', 'Xa Mặt Trời nhất']], 'Sao Mộc lớn nhất, Sao Thổ vành đai đẹp nhất, Sao Hải Vương xa nhất.', $d);
        $this->matching($s, 'Nối mỗi mùa ở Bắc bán cầu với các tháng của nó.', [['Mùa xuân', 'Tháng 3 – 5'], ['Mùa hạ', 'Tháng 6 – 8'], ['Mùa thu', 'Tháng 9 – 11'], ['Mùa đông', 'Tháng 12 – 2']], 'Bắc bán cầu: xuân (3–5), hạ (6–8), thu (9–11), đông (12–2).', $d);
        $this->sortQ($s, 'Kéo mỗi hành tinh vào nhóm HÀNH TINH ĐẤT ĐÁ hoặc KHÍ KHỔNG LỒ.', [['Sao Thủy', 'ĐẤT ĐÁ'], ['Sao Kim', 'ĐẤT ĐÁ'], ['Trái Đất', 'ĐẤT ĐÁ'], ['Sao Hỏa', 'ĐẤT ĐÁ'], ['Sao Mộc', 'KHÍ KHỔNG LỒ'], ['Sao Thổ', 'KHÍ KHỔNG LỒ']], 'Bốn hành tinh gần Mặt Trời là đất đá; Sao Mộc, Sao Thổ là khí khổng lồ.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Mặt Trời là một ngôi sao', 'ĐÚNG'], ['Ánh sáng Mặt Trời đến Trái Đất mất 8 phút', 'ĐÚNG'], ['Sao Hỏa gần Mặt Trời hơn Trái Đất', 'SAI'], ['Mặt Trăng tự phát sáng', 'SAI']], 'Mặt Trời là sao; Sao Hỏa xa Mặt Trời hơn Trái Đất; Mặt Trăng phản chiếu ánh sáng Mặt Trời.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm DO TỰ QUAY hoặc DO QUAY QUANH MẶT TRỜI.', [['Ngày đêm luân phiên', 'DO TỰ QUAY'], ['Giờ khác nhau trên Trái Đất', 'DO TỰ QUAY'], ['Bốn mùa trong năm', 'DO QUAY QUANH MẶT TRỜI'], ['Ngày đêm dài ngắn theo mùa', 'DO QUAY QUANH MẶT TRỜI']], 'Tự quay gây ngày đêm; quay quanh Mặt Trời gây ra bốn mùa.', $d);
        $this->sortQ($s, 'Kéo mỗi mốc thời gian vào nhóm MỘT NGÀY, MỘT NĂM hoặc NGÀY ĐẶC BIỆT.', [['24 giờ', 'MỘT NGÀY'], ['365 ngày', 'MỘT NĂM'], ['Ngày 21/3', 'NGÀY ĐẶC BIỆT'], ['Ngày 22/12', 'NGÀY ĐẶC BIỆT']], '24 giờ là một ngày, 365 ngày là một năm; 21/3 và 22/12 là ngày đặc biệt.', $d);
        $this->sortQ($s, 'Kéo mỗi hành tinh vào nhóm GẦN MẶT TRỜI HƠN hoặc XA HƠN Trái Đất.', [['Sao Thủy', 'GẦN HƠN'], ['Sao Kim', 'GẦN HƠN'], ['Sao Hỏa', 'XA HƠN'], ['Sao Mộc', 'XA HƠN']], 'Sao Thủy và Sao Kim gần Mặt Trời hơn Trái Đất; Sao Hỏa và Sao Mộc xa hơn.', $d);
        $this->fill($s, 'Hành tinh lớn nhất Hệ Mặt Trời là Sao ___.', [[0, 'Mộc']], 'Sao Mộc là hành tinh lớn nhất Hệ Mặt Trời.', $d);
        $this->fill($s, 'Mặt Trời là một ngôi ___ ở trung tâm Hệ Mặt Trời.', [[0, 'sao']], 'Mặt Trời là một ngôi sao.', $d);
        $this->fill($s, 'Ánh sáng từ Mặt Trời đến Trái Đất mất khoảng 8 ___.', [[0, 'phút']], 'Ánh sáng mất khoảng 8 phút để đi từ Mặt Trời đến Trái Đất.', $d);
        $this->fill($s, '"Hành tinh đỏ" là tên gọi của Sao ___.', [[0, 'Hỏa']], 'Sao Hỏa được gọi là "hành tinh đỏ".', $d);
        $this->fill($s, 'Vệ tinh tự nhiên duy nhất của Trái Đất là Mặt ___.', [[0, 'Trăng']], 'Mặt Trăng là vệ tinh tự nhiên duy nhất của Trái Đất.', $d);
    }

    private function seedDiaLyThpt10Lop102(): void
    {
        $s = 'dia-ly-thpt-10-lop-10-2'; $d = 'kho';
        $this->quiz($s, 'Lớp nào của Trái Đất có nhiệt độ cao nhất?', ['Vỏ Trái Đất', 'Manti', 'Nhân (lõi)', 'Thạch quyển'], 2, 'Nhân Trái Đất có nhiệt độ cao nhất, lên tới khoảng 5 000°C.', $d);
        $this->quiz($s, 'Núi lửa phun trào là kết quả của quá trình nào?', ['Nội lực', 'Ngoại lực', 'Phong hóa', 'Xâm thực'], 0, 'Núi lửa phun trào là kết quả tác động của nội lực.', $d);
        $this->quiz($s, 'Thung lũng sông và đồng bằng châu thổ do quá trình nào tạo ra?', ['Nội lực', 'Ngoại lực', 'Động đất', 'Núi lửa'], 1, 'Thung lũng sông và đồng bằng châu thổ do ngoại lực (nước chảy) tạo ra.', $d);
        $this->quiz($s, 'Độ dày trung bình của vỏ Trái Đất ở lục địa khoảng bao nhiêu?', ['5 km', '15 km', '35 km', '100 km'], 2, 'Vỏ Trái Đất ở lục địa dày khoảng 35 km, ở đại dương mỏng hơn.', $d);
        $this->quiz($s, 'Việt Nam nằm trên mảng kiến tạo nào?', ['Mảng Thái Bình Dương', 'Mảng Âu – Á', 'Mảng Ấn – Úc', 'Mảng Phi'], 1, 'Việt Nam nằm trên mảng kiến tạo Âu – Á.', $d);
        $this->matching($s, 'Nối mỗi lớp của Trái Đất với đặc điểm khác của nó.', [['Vỏ Trái Đất', 'Mỏng nhất'], ['Manti', 'Dày nhất'], ['Nhân ngoài', 'Ở thể lỏng'], ['Nhân trong', 'Thể rắn, nóng nhất']], 'Vỏ mỏng nhất, manti dày nhất, nhân ngoài lỏng, nhân trong rắn và nóng nhất.', $d);
        $this->matching($s, 'Nối mỗi dạng địa hình với tác nhân tạo ra nó.', [['Thung lũng chữ V', 'Sông'], ['Cồn cát', 'Gió'], ['Hang động đá vôi', 'Nước ngầm'], ['Bãi biển', 'Sóng biển']], 'Sông tạo thung lũng, gió tạo cồn cát, nước ngầm tạo hang động đá vôi.', $d);
        $this->matching($s, 'Nối mỗi hiện tượng với nơi nó thường xảy ra.', [['Động đất', 'Ranh giới mảng'], ['Núi lửa', 'Vành đai lửa Thái Bình Dương'], ['Sóng thần', 'Vùng biển có động đất'], ['Núi trẻ', 'Nơi hai mảng xô vào nhau']], 'Động đất ở ranh giới mảng, núi lửa ở vành đai lửa Thái Bình Dương.', $d);
        $this->matching($s, 'Nối mỗi dạng địa hình với quá trình tạo ra nó.', [['Núi lửa', 'Nội lực'], ['Đồng bằng châu thổ', 'Ngoại lực'], ['Dãy núi uốn nếp', 'Nội lực'], ['Cao nguyên đá vôi', 'Ngoại lực']], 'Núi lửa và núi uốn nếp do nội lực; châu thổ và đá vôi do ngoại lực.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với định nghĩa của nó.', [['Nội lực', 'Lực từ bên trong Trái Đất'], ['Ngoại lực', 'Lực từ bên ngoài'], ['Thạch quyển', 'Lớp vỏ cứng ngoài cùng'], ['Phong hóa', 'Phá hủy đá tại chỗ']], 'Nội lực từ trong, ngoại lực từ ngoài; thạch quyển là vỏ cứng ngoài cùng.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm NỘI LỰC hoặc NGOẠI LỰC.', [['Uốn nếp', 'NỘI LỰC'], ['Đứt gãy', 'NỘI LỰC'], ['Xâm thực', 'NGOẠI LỰC'], ['Bồi tụ', 'NGOẠI LỰC']], 'Uốn nếp, đứt gãy do nội lực; xâm thực, bồi tụ do ngoại lực.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Núi lửa do nội lực gây ra', 'ĐÚNG'], ['Phong hóa là tác động của ngoại lực', 'ĐÚNG'], ['Trái Đất có 3 lớp: vỏ, manti, nhân', 'ĐÚNG'], ['Vỏ Trái Đất dày nhất ở đại dương', 'SAI']], 'Vỏ Trái Đất dày nhất ở lục địa, mỏng ở đại dương.', $d);
        $this->sortQ($s, 'Kéo mỗi lớp/bộ phận vào nhóm THỂ RẮN hoặc THỂ LỎNG/DẺO.', [['Vỏ Trái Đất', 'THỂ RẮN'], ['Nhân trong', 'THỂ RẮN'], ['Nhân ngoài', 'THỂ LỎNG'], ['Quyển mềm', 'THỂ DẺO']], 'Vỏ và nhân trong thể rắn; nhân ngoài lỏng; quyển mềm dẻo.', $d);
        $this->sortQ($s, 'Kéo mỗi dạng địa hình Việt Nam vào nhóm DO NỘI LỰC hoặc NGOẠI LỰC là chính.', [['Fansipan', 'DO NỘI LỰC'], ['Dãy Trường Sơn', 'DO NỘI LỰC'], ['Đồng bằng sông Hồng', 'DO NGOẠI LỰC'], ['Vịnh Hạ Long', 'DO NGOẠI LỰC']], 'Fansipan và Trường Sơn do nội lực nâng lên; đồng bằng và Hạ Long do ngoại lực tạo hình.', $d);
        $this->sortQ($s, 'Kéo mỗi quá trình vào nhóm DIỄN RA NHANH hoặc CHẬM.', [['Động đất', 'NHANH'], ['Núi lửa phun', 'NHANH'], ['Phong hóa', 'CHẬM'], ['Bồi tụ', 'CHẬM']], 'Động đất, núi lửa diễn ra nhanh; phong hóa, bồi tụ diễn ra chậm.', $d);
        $this->fill($s, 'Lớp có nhiệt độ cao nhất trong lòng Trái Đất là ___.', [[0, 'nhân']], 'Nhân Trái Đất nóng nhất, khoảng 5 000°C.', $d);
        $this->fill($s, 'Núi lửa phun trào là kết quả tác động của ___ lực.', [[0, 'nội']], 'Núi lửa do nội lực gây ra.', $d);
        $this->fill($s, 'Đồng bằng châu thổ được tạo ra chủ yếu do ___ lực.', [[0, 'ngoại']], 'Châu thổ do ngoại lực (sông bồi tụ) tạo ra.', $d);
        $this->fill($s, 'Việt Nam nằm trên mảng kiến tạo Âu – ___.', [[0, 'Á']], 'Việt Nam nằm trên mảng Âu – Á.', $d);
        $this->fill($s, 'Vành đai núi lửa quanh Thái Bình Dương gọi là vành đai ___.', [[0, 'lửa']], 'Vành đai lửa Thái Bình Dương có nhiều núi lửa, động đất.', $d);
    }

    private function seedDiaLyThpt10Lop103(): void
    {
        $s = 'dia-ly-thpt-10-lop-10-3'; $d = 'trung_binh';
        $this->quiz($s, 'Tầng nào của khí quyển có tầng ô-dôn?', ['Tầng đối lưu', 'Tầng bình lưu', 'Tầng giữa', 'Tầng nhiệt'], 1, 'Tầng ô-dôn nằm ở tầng bình lưu, bảo vệ Trái Đất khỏi tia cực tím.', $d);
        $this->quiz($s, 'Khí nào chiếm tỉ lệ lớn nhất trong khí quyển?', ['Ô-xy', 'Ni-tơ', 'Các-bô-níc', 'Hy-đrô'], 1, 'Ni-tơ chiếm khoảng 78% thể tích khí quyển.', $d);
        $this->quiz($s, 'Nguyên nhân chính hình thành gió mùa là gì?', ['Chênh lệch nhiệt độ giữa lục địa và đại dương', 'Trái Đất tự quay', 'Dòng biển nóng', 'Địa hình núi cao'], 0, 'Gió mùa hình thành do chênh lệch nhiệt độ giữa lục địa và đại dương theo mùa.', $d);
        $this->quiz($s, 'Vùng áp thấp xích đạo có đặc điểm thời tiết nào?', ['Khô hạn', 'Nóng ẩm, mưa nhiều', 'Lạnh khô', 'Ít mưa'], 1, 'Áp thấp xích đạo nóng ẩm, gây mưa nhiều.', $d);
        $this->quiz($s, 'Frông là gì?', ['Một loại gió', 'Mặt ngăn cách hai khối khí khác nhau', 'Một loại mây', 'Vùng áp cao'], 1, 'Frông là mặt ngăn cách giữa hai khối khí có tính chất khác nhau.', $d);
        $this->matching($s, 'Nối mỗi tầng khí quyển với đặc điểm của nó.', [['Tầng đối lưu', 'Có hiện tượng thời tiết'], ['Tầng bình lưu', 'Có tầng ô-dôn'], ['Tầng cao', 'Không khí rất loãng']], 'Đối lưu có thời tiết, bình lưu có ô-dôn, tầng cao không khí loãng.', $d);
        $this->matching($s, 'Nối mỗi loại gió với nguyên nhân hình thành của nó.', [['Gió mậu dịch', 'Chênh lệch khí áp'], ['Gió mùa', 'Chênh lệch nhiệt lục địa – đại dương'], ['Gió Tây ôn đới', 'Hoàn lưu khí quyển'], ['Gió Lào', 'Địa hình núi cao']], 'Gió mậu dịch do chênh lệch áp, gió mùa do chênh lệch nhiệt, gió Lào do địa hình.', $d);
        $this->matching($s, 'Nối mỗi khối khí với tính chất của nó.', [['Khối khí nóng', 'Nhiệt độ cao'], ['Khối khí lạnh', 'Nhiệt độ thấp'], ['Khối khí ẩm', 'Nhiều hơi nước'], ['Khối khí khô', 'Ít hơi nước']], 'Khối khí nóng/lạnh khác nhiệt độ; khối khí ẩm/khô khác lượng hơi nước.', $d);
        $this->matching($s, 'Nối mỗi đai khí áp với thời tiết đặc trưng của nó.', [['Áp thấp xích đạo', 'Mưa nhiều'], ['Áp cao cận chí tuyến', 'Khô hạn'], ['Áp thấp ôn đới', 'Mưa'], ['Áp cao cực', 'Lạnh khô']], 'Áp thấp xích đạo mưa nhiều, áp cao cận chí tuyến khô hạn, áp cao cực lạnh khô.', $d);
        $this->matching($s, 'Nối mỗi hiện tượng khí tượng với nguyên nhân của nó.', [['Mưa', 'Hơi nước ngưng tụ'], ['Sương mù', 'Hơi nước gần mặt đất'], ['Bão', 'Vùng áp thấp mạnh'], ['Lốc xoáy', 'Không khí đối lưu mạnh']], 'Mưa do hơi nước ngưng tụ, bão là vùng áp thấp mạnh.', $d);
        $this->sortQ($s, 'Kéo mỗi loại gió vào nhóm GIÓ THƯỜNG XUYÊN hoặc GIÓ THEO MÙA – ĐỊA PHƯƠNG.', [['Gió Tây ôn đới', 'GIÓ THƯỜNG XUYÊN'], ['Gió mậu dịch', 'GIÓ THƯỜNG XUYÊN'], ['Gió mùa', 'GIÓ THEO MÙA – ĐỊA PHƯƠNG'], ['Gió Lào', 'GIÓ THEO MÙA – ĐỊA PHƯƠNG']], 'Gió Tây ôn đới và mậu dịch thổi thường xuyên; gió mùa và gió Lào theo mùa, địa phương.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Tầng ô-dôn ở tầng bình lưu', 'ĐÚNG'], ['Ni-tơ chiếm 78% khí quyển', 'ĐÚNG'], ['Frông là mặt ngăn cách hai khối khí', 'ĐÚNG'], ['Gió thổi từ áp thấp đến áp cao', 'SAI']], 'Gió thổi từ áp cao đến áp thấp; ba phát biểu còn lại đúng.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân tố vào nhóm LÀM TĂNG hoặc LÀM GIẢM nhiệt độ không khí.', [['Gần xích đạo', 'LÀM TĂNG'], ['Mùa hè', 'LÀM TĂNG'], ['Xa xích đạo', 'LÀM GIẢM'], ['Mùa đông', 'LÀM GIẢM']], 'Gần xích đạo và mùa hè làm tăng nhiệt độ; xa xích đạo và mùa đông làm giảm.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm Ở TẦNG ĐỐI LƯU hoặc TẦNG BÌNH LƯU.', [['Mây', 'TẦNG ĐỐI LƯU'], ['Mưa', 'TẦNG ĐỐI LƯU'], ['Tầng ô-dôn', 'TẦNG BÌNH LƯU'], ['Máy bay dân dụng', 'TẦNG BÌNH LƯU']], 'Mây mưa ở tầng đối lưu; ô-dôn và máy bay dân dụng ở tầng bình lưu.', $d);
        $this->sortQ($s, 'Kéo mỗi khí vào nhóm CHIẾM TỈ LỆ LỚN hoặc NHỎ trong khí quyển.', [['Ni-tơ', 'TỈ LỆ LỚN'], ['Ô-xy', 'TỈ LỆ LỚN'], ['Các-bô-níc', 'TỈ LỆ NHỎ'], ['Ô-dôn', 'TỈ LỆ NHỎ']], 'Ni-tơ (78%) và ô-xy (21%) chiếm tỉ lệ lớn; các-bô-níc và ô-dôn rất nhỏ.', $d);
        $this->fill($s, 'Tầng ô-dôn nằm ở tầng bình ___.', [[0, 'lưu']], 'Tầng ô-dôn nằm ở tầng bình lưu.', $d);
        $this->fill($s, 'Khí chiếm tỉ lệ lớn nhất trong khí quyển là khí ___.', [[0, 'ni-tơ']], 'Ni-tơ chiếm khoảng 78% khí quyển.', $d);
        $this->fill($s, 'Frông là mặt ngăn cách hai khối ___ khác nhau.', [[0, 'khí']], 'Frông ngăn cách hai khối khí khác tính chất.', $d);
        $this->fill($s, 'Vùng áp thấp xích đạo có thời tiết nóng ẩm, mưa ___.', [[0, 'nhiều']], 'Áp thấp xích đạo nóng ẩm, mưa nhiều.', $d);
        $this->fill($s, 'Gió mùa hình thành do chênh lệch nhiệt độ giữa lục địa và đại ___.', [[0, 'dương']], 'Chênh lệch nhiệt lục địa – đại dương sinh ra gió mùa.', $d);
    }

    private function seedDiaLyThpt10Lop104(): void
    {
        $s = 'dia-ly-thpt-10-lop-10-4'; $d = 'kho';
        $this->quiz($s, 'Quy luật phi địa đới là sự thay đổi của thiên nhiên theo yếu tố nào?', ['Vĩ độ', 'Độ cao và địa hình', 'Kinh độ', 'Thời gian'], 1, 'Phi địa đới là thay đổi thiên nhiên theo độ cao, địa hình, không theo vĩ độ.', $d);
        $this->quiz($s, 'Rừng lá kim (tai-ga) phân bố chủ yếu ở đới nào?', ['Đới nóng', 'Đới ôn hòa lạnh', 'Đới lạnh', 'Hoang mạc'], 1, 'Rừng tai-ga phân bố ở đới ôn hòa lạnh (phía bắc).', $d);
        $this->quiz($s, 'Xavan là thảm thực vật đặc trưng của đới nào?', ['Đới lạnh', 'Đới ôn hòa', 'Đới nóng (cận xích đạo)', 'Đới cực'], 2, 'Xavan là thảm thực vật của đới nóng, vùng cận xích đạo.', $d);
        $this->quiz($s, 'Động vật nào đặc trưng của đới lạnh?', ['Voi', 'Gấu Bắc Cực', 'Hổ', 'Lạc đà'], 1, 'Gấu Bắc Cực và tuần lộc là động vật đặc trưng đới lạnh.', $d);
        $this->quiz($s, 'Ở vùng núi cao Việt Nam, thảm thực vật thay đổi theo quy luật nào?', ['Địa đới', 'Phi địa đới', 'Đai cao', 'Không đổi'], 1, 'Lên cao thảm thực vật thay đổi theo quy luật phi địa đới (đai cao).', $d);
        $this->matching($s, 'Nối mỗi đới thiên nhiên với động vật đặc trưng của nó.', [['Đới nóng', 'Voi, hổ'], ['Đới ôn hòa', 'Hươu, nai'], ['Đới lạnh', 'Gấu Bắc Cực, tuần lộc'], ['Hoang mạc', 'Lạc đà']], 'Đới nóng – voi hổ, ôn hòa – hươu nai, lạnh – gấu Bắc Cực, hoang mạc – lạc đà.', $d);
        $this->matching($s, 'Nối mỗi kiểu rừng với đặc điểm của nó.', [['Rừng mưa nhiệt đới', 'Nhiều tầng, xanh quanh năm'], ['Rừng lá kim', 'Lá hình kim'], ['Rừng lá rộng', 'Rụng lá mùa đông'], ['Rừng ngập mặn', 'Ven biển nhiệt đới']], 'Rừng mưa nhiệt đới nhiều tầng; rừng lá kim lá hình kim; rừng ngập mặn ven biển.', $d);
        $this->matching($s, 'Nối mỗi nhân tố với quy luật mà nó tạo ra.', [['Vĩ độ', 'Địa đới'], ['Độ cao', 'Phi địa đới'], ['Dòng biển', 'Phi địa đới'], ['Địa hình', 'Phi địa đới']], 'Vĩ độ tạo địa đới; độ cao, dòng biển, địa hình tạo phi địa đới.', $d);
        $this->matching($s, 'Nối mỗi cây trồng với đới khí hậu thích hợp của nó.', [['Lúa nước', 'Đới nóng'], ['Lúa mì', 'Đới ôn hòa'], ['Cao su', 'Đới nóng'], ['Táo', 'Đới ôn hòa']], 'Lúa nước, cao su ở đới nóng; lúa mì, táo ở đới ôn hòa.', $d);
        $this->matching($s, 'Nối mỗi đới với lượng mưa đặc trưng của nó.', [['Đới nóng ẩm', 'Mưa nhiều'], ['Hoang mạc', 'Mưa rất ít'], ['Đới ôn hòa', 'Mưa trung bình'], ['Đới lạnh', 'Mưa ít']], 'Đới nóng ẩm mưa nhiều, hoang mạc mưa rất ít, đới lạnh mưa ít.', $d);
        $this->sortQ($s, 'Kéo mỗi thảm thực vật vào nhóm ĐỚI NÓNG, ĐỚI ÔN HÒA hoặc ĐỚI LẠNH.', [['Xavan', 'ĐỚI NÓNG'], ['Rừng mưa nhiệt đới', 'ĐỚI NÓNG'], ['Rừng lá kim', 'ĐỚI ÔN HÒA'], ['Thảo nguyên', 'ĐỚI ÔN HÒA'], ['Đài nguyên', 'ĐỚI LẠNH'], ['Băng tuyết', 'ĐỚI LẠNH']], 'Xavan, rừng mưa ở đới nóng; tai-ga, thảo nguyên ở ôn hòa; đài nguyên ở đới lạnh.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Xavan ở đới nóng', 'ĐÚNG'], ['Địa đới thay đổi theo vĩ độ', 'ĐÚNG'], ['Rừng tai-ga ở đới nóng', 'SAI'], ['Càng lên cao càng nóng', 'SAI']], 'Càng lên cao nhiệt độ càng giảm; rừng tai-ga ở đới ôn hòa lạnh.', $d);
        $this->sortQ($s, 'Kéo mỗi sự thay đổi vào nhóm ĐỊA ĐỚI hoặc PHI ĐỊA ĐỚI.', [['Thay đổi theo vĩ độ', 'ĐỊA ĐỚI'], ['Thay đổi theo độ cao', 'PHI ĐỊA ĐỚI'], ['Do dòng biển', 'PHI ĐỊA ĐỚI'], ['Do địa hình', 'PHI ĐỊA ĐỚI']], 'Theo vĩ độ là địa đới; theo độ cao, dòng biển, địa hình là phi địa đới.', $d);
        $this->sortQ($s, 'Kéo mỗi đới/kiểu thảm thực vật vào nhóm CÓ RỪNG hoặc KHÔNG CÓ RỪNG.', [['Rừng mưa nhiệt đới', 'CÓ RỪNG'], ['Rừng tai-ga', 'CÓ RỪNG'], ['Hoang mạc', 'KHÔNG CÓ RỪNG'], ['Đài nguyên', 'KHÔNG CÓ RỪNG']], 'Rừng mưa nhiệt đới và tai-ga có rừng; hoang mạc và đài nguyên không có rừng.', $d);
        $this->sortQ($s, 'Kéo mỗi kiểu khí hậu – thảm thực vật vào nhóm ẨM hoặc KHÔ.', [['Rừng mưa nhiệt đới', 'ẨM'], ['Rừng lá rộng', 'ẨM'], ['Hoang mạc', 'KHÔ'], ['Thảo nguyên khô', 'KHÔ']], 'Rừng mưa và rừng lá rộng ở nơi ẩm; hoang mạc và thảo nguyên khô ở nơi khô.', $d);
        $this->fill($s, 'Sự thay đổi thiên nhiên theo độ cao gọi là quy luật phi địa ___.', [[0, 'đới']], 'Phi địa đới là thay đổi theo độ cao, không theo vĩ độ.', $d);
        $this->fill($s, 'Rừng lá kim còn gọi là rừng ___.', [[0, 'tai-ga']], 'Rừng lá kim ở đới ôn hòa lạnh gọi là rừng tai-ga.', $d);
        $this->fill($s, 'Thảm thực vật đặc trưng của vùng cận xích đạo là ___.', [[0, 'xavan']], 'Xavan là thảm thực vật vùng cận xích đạo.', $d);
        $this->fill($s, 'Động vật đặc trưng của đới lạnh là gấu Bắc ___.', [[0, 'Cực']], 'Gấu Bắc Cực sống ở đới lạnh.', $d);
        $this->fill($s, 'Ở vùng núi cao Việt Nam, thảm thực vật thay đổi theo ___ cao.', [[0, 'độ']], 'Thảm thực vật thay đổi theo độ cao (đai cao).', $d);
    }

    private function seedDiaLyThpt11Lop111(): void
    {
        $s = 'dia-ly-thpt-11-lop-11-1'; $d = 'trung_binh';
        $this->quiz($s, 'Dân số thế giới đạt mốc 7 tỉ người vào năm nào?', ['1987', '1999', '2011', '2022'], 2, 'Dân số thế giới đạt 7 tỉ người năm 2011, 8 tỉ người năm 2022.', $d);
        $this->quiz($s, 'Tỉ suất tử thô phản ánh điều gì?', ['Số trẻ sinh ra trên 1000 dân', 'Số người chết trên 1000 dân', 'Số người nhập cư', 'Tuổi thọ trung bình'], 1, 'Tỉ suất tử thô là số người chết trên 1000 dân trong một năm.', $d);
        $this->quiz($s, 'Nước nào có mật độ dân số cao nhất thế giới?', ['Xin-ga-po', 'Mô-na-cô', 'Băng-la-đét', 'Hà Lan'], 1, 'Mô-na-cô là nước có mật độ dân số cao nhất thế giới.', $d);
        $this->quiz($s, 'Châu lục nào có tỉ lệ gia tăng dân số nhanh nhất hiện nay?', ['Châu Á', 'Châu Âu', 'Châu Phi', 'Châu Mỹ'], 2, 'Châu Phi có tỉ lệ gia tăng dân số nhanh nhất hiện nay.', $d);
        $this->quiz($s, '"Bùng nổ dân số" xảy ra khi nào?', ['Sinh cao, tử cao', 'Sinh cao, tử thấp', 'Sinh thấp, tử thấp', 'Sinh thấp, tử cao'], 1, 'Bùng nổ dân số xảy ra khi tỉ suất sinh cao mà tỉ suất tử thấp.', $d);
        $this->matching($s, 'Nối mỗi mốc dân số thế giới với năm đạt được.', [['5 tỉ người', '1987'], ['6 tỉ người', '1999'], ['7 tỉ người', '2011'], ['8 tỉ người', '2022']], 'Các mốc: 5 tỉ (1987), 6 tỉ (1999), 7 tỉ (2011), 8 tỉ (2022).', $d);
        $this->matching($s, 'Nối mỗi nước với đặc điểm mật độ dân số của nó.', [['Mô-na-cô', 'Cao nhất thế giới'], ['Xin-ga-po', 'Cao nhất châu Á'], ['Mông Cổ', 'Thấp nhất thế giới'], ['Úc', 'Thấp']], 'Mô-na-cô mật độ cao nhất, Mông Cổ thấp nhất thế giới.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ dân số với ý nghĩa của nó.', [['Mật độ dân số', 'Số dân trên một đơn vị diện tích'], ['Tuổi thọ trung bình', 'Số năm sống trung bình'], ['Cơ cấu dân số', 'Tỉ lệ các nhóm dân số'], ['Phân bố dân cư', 'Sự sắp xếp dân cư trên lãnh thổ']], 'Mật độ là số dân/diện tích; tuổi thọ là số năm sống trung bình.', $d);
        $this->matching($s, 'Nối mỗi nước với thứ hạng dân số thế giới của nó.', [['Ấn Độ', 'Thứ 1'], ['Trung Quốc', 'Thứ 2'], ['Hoa Kỳ', 'Thứ 3'], ['Indonesia', 'Thứ 4']], 'Ấn Độ đông dân nhất, rồi đến Trung Quốc, Hoa Kỳ, Indonesia.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn với đặc điểm gia tăng dân số của nó.', [['Trước cách mạng công nghiệp', 'Tăng chậm'], ['Sau cách mạng công nghiệp', 'Tăng nhanh'], ['Nước phát triển hiện nay', 'Tăng chậm'], ['Châu Phi hiện nay', 'Tăng nhanh']], 'Sau cách mạng công nghiệp dân số tăng nhanh; nước phát triển hiện nay tăng chậm.', $d);
        $this->sortQ($s, 'Kéo mỗi quốc gia vào nhóm TRÊN hoặc DƯỚI 200 triệu dân.', [['Bra-xin', 'TRÊN 200 TRIỆU'], ['Pa-ki-xtan', 'TRÊN 200 TRIỆU'], ['Việt Nam', 'DƯỚI 200 TRIỆU'], ['Nhật Bản', 'DƯỚI 200 TRIỆU']], 'Bra-xin và Pa-ki-xtan trên 200 triệu dân; Việt Nam và Nhật Bản dưới 200 triệu.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Dân số thế giới đạt 8 tỉ năm 2022', 'ĐÚNG'], ['Châu Phi gia tăng dân số nhanh', 'ĐÚNG'], ['Trung Quốc đông dân nhất thế giới', 'SAI'], ['Mô-na-cô có mật độ dân số thấp', 'SAI']], 'Ấn Độ hiện đông dân nhất; Mô-na-cô mật độ cao nhất thế giới.', $d);
        $this->sortQ($s, 'Kéo mỗi nơi vào nhóm MẬT ĐỘ DÂN SỐ CAO hoặc THẤP.', [['Mô-na-cô', 'CAO'], ['Xin-ga-po', 'CAO'], ['Mông Cổ', 'THẤP'], ['Úc', 'THẤP']], 'Mô-na-cô và Xin-ga-po mật độ cao; Mông Cổ và Úc mật độ thấp.', $d);
        $this->sortQ($s, 'Kéo mỗi nước vào nhóm GIA TĂNG DÂN SỐ NHANH hoặc CHẬM.', [['Ni-giê-ri-a', 'NHANH'], ['Pa-ki-xtan', 'NHANH'], ['Nhật Bản', 'CHẬM'], ['Đức', 'CHẬM']], 'Ni-giê-ri-a và Pa-ki-xtan tăng nhanh; Nhật Bản và Đức tăng chậm.', $d);
        $this->sortQ($s, 'Kéo mỗi nước vào nhóm PHÁT TRIỂN hoặc ĐANG PHÁT TRIỂN (theo gia tăng dân số).', [['Nhật Bản', 'PHÁT TRIỂN'], ['Đức', 'PHÁT TRIỂN'], ['Ấn Độ', 'ĐANG PHÁT TRIỂN'], ['Ni-giê-ri-a', 'ĐANG PHÁT TRIỂN']], 'Nước phát triển gia tăng dân số chậm; nước đang phát triển tăng nhanh.', $d);
        $this->fill($s, 'Dân số thế giới đạt mốc 7 tỉ người vào năm ___.', [[0, '2011']], 'Năm 2011 dân số thế giới đạt 7 tỉ người.', $d);
        $this->fill($s, 'Nước có mật độ dân số cao nhất thế giới là ___.', [[0, 'Mô-na-cô']], 'Mô-na-cô mật độ dân số cao nhất thế giới.', $d);
        $this->fill($s, 'Châu lục có tỉ lệ gia tăng dân số nhanh nhất là châu ___.', [[0, 'Phi']], 'Châu Phi gia tăng dân số nhanh nhất.', $d);
        $this->fill($s, 'Nước đông dân thứ hai thế giới là Trung ___.', [[0, 'Quốc']], 'Trung Quốc đông dân thứ hai sau Ấn Độ.', $d);
        $this->fill($s, '"Bùng nổ dân số" xảy ra khi tỉ suất sinh cao và tỉ suất tử ___.', [[0, 'thấp']], 'Sinh cao, tử thấp gây bùng nổ dân số.', $d);
    }

    private function seedDiaLyThpt11Lop112(): void
    {
        $s = 'dia-ly-thpt-11-lop-11-2'; $d = 'kho';
        $this->quiz($s, 'Cơ cấu dân số theo lao động thường chia thành mấy nhóm?', ['2', '3', '4', '5'], 1, 'Cơ cấu theo lao động chia 3 nhóm: trong độ tuổi, dưới và trên độ tuổi lao động.', $d);
        $this->quiz($s, 'Đô thị hóa là gì?', ['Giảm dân thành thị', 'Tăng tỉ lệ dân thành thị, mở rộng đô thị', 'Di cư ra nước ngoài', 'Giảm dân số'], 1, 'Đô thị hóa là quá trình tăng tỉ lệ dân thành thị và mở rộng đô thị.', $d);
        $this->quiz($s, 'Thành phố nào đông dân nhất thế giới hiện nay?', ['Delhi', 'Thượng Hải', 'Tokyo', 'São Paulo'], 2, 'Tokyo là thành phố đông dân nhất thế giới hiện nay.', $d);
        $this->quiz($s, 'Di cư quốc tế mang lại lợi ích gì cho nước tiếp nhận?', ['Giảm dân số', 'Bổ sung lực lượng lao động', 'Tăng thất nghiệp', 'Giảm sản xuất'], 1, 'Di cư giúp nước tiếp nhận bổ sung lực lượng lao động.', $d);
        $this->quiz($s, '"Chảy máu chất xám" là hiện tượng gì?', ['Lao động giỏi di cư ra nước ngoài', 'Người già di cư về quê', 'Trẻ em bỏ học', 'Dân số giảm'], 0, 'Chảy máu chất xám là hiện tượng lao động trình độ cao di cư ra nước ngoài.', $d);
        $this->matching($s, 'Nối mỗi nhóm tuổi với vai trò lao động của nó.', [['0 – 14 tuổi', 'Ngoài độ tuổi lao động'], ['15 – 64 tuổi', 'Trong độ tuổi lao động'], ['Trên 65 tuổi', 'Ngoài độ tuổi lao động']], 'Nhóm 15 – 64 tuổi là lực lượng lao động chính.', $d);
        $this->matching($s, 'Nối mỗi siêu đô thị với châu lục của nó.', [['Tokyo', 'Châu Á'], ['Delhi', 'Châu Á'], ['São Paulo', 'Châu Mỹ'], ['Cai-rô', 'Châu Phi']], 'Tokyo, Delhi ở châu Á; São Paulo ở châu Mỹ; Cai-rô ở châu Phi.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa của nó.', [['Đô thị hóa', 'Tăng tỉ lệ dân thành thị'], ['Di cư', 'Chuyển nơi cư trú'], ['Chảy máu chất xám', 'Mất lao động trình độ cao'], ['Siêu đô thị', 'Trên 10 triệu dân']], 'Đô thị hóa tăng dân thành thị; di cư là chuyển nơi cư trú.', $d);
        $this->matching($s, 'Nối mỗi nguyên nhân với loại di cư tương ứng.', [['Tìm việc làm', 'Di cư kinh tế'], ['Chiến tranh', 'Di cư tị nạn'], ['Học tập', 'Di cư học tập'], ['Đoàn tụ gia đình', 'Di cư gia đình']], 'Tìm việc – di cư kinh tế, chiến tranh – tị nạn, học tập – di cư học tập.', $d);
        $this->matching($s, 'Nối mỗi nước với đặc điểm cơ cấu dân số của nó.', [['Nhật Bản', 'Dân số già'], ['Ni-giê-ri-a', 'Dân số trẻ'], ['Việt Nam', 'Cơ cấu dân số vàng'], ['Đức', 'Dân số già']], 'Nhật Bản, Đức dân số già; Ni-giê-ri-a dân số trẻ; Việt Nam cơ cấu vàng.', $d);
        $this->sortQ($s, 'Kéo mỗi quốc gia vào nhóm DÂN SỐ TRẺ hoặc DÂN SỐ GIÀ.', [['Ni-giê-ri-a', 'DÂN SỐ TRẺ'], ['Ấn Độ', 'DÂN SỐ TRẺ'], ['Nhật Bản', 'DÂN SỐ GIÀ'], ['I-ta-li-a', 'DÂN SỐ GIÀ']], 'Ni-giê-ri-a, Ấn Độ dân số trẻ; Nhật Bản, I-ta-li-a dân số già.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Tokyo là siêu đô thị', 'ĐÚNG'], ['Chảy máu chất xám là mất lao động giỏi', 'ĐÚNG'], ['Di cư chỉ diễn ra trong nước', 'SAI'], ['Đô thị hóa làm giảm dân thành thị', 'SAI']], 'Di cư có cả trong nước và quốc tế; đô thị hóa làm tăng dân thành thị.', $d);
        $this->sortQ($s, 'Kéo mỗi đô thị vào nhóm SIÊU ĐÔ THỊ hoặc CHƯA ĐẠT.', [['Tokyo', 'SIÊU ĐÔ THỊ'], ['Delhi', 'SIÊU ĐÔ THỊ'], ['Hà Nội', 'CHƯA ĐẠT'], ['Berlin', 'CHƯA ĐẠT']], 'Tokyo và Delhi trên 10 triệu dân là siêu đô thị.', $d);
        $this->sortQ($s, 'Kéo mỗi tác động của đô thị hóa vào nhóm TÍCH CỰC hoặc TIÊU CỰC.', [['Tạo nhiều việc làm', 'TÍCH CỰC'], ['Hiện đại hóa', 'TÍCH CỰC'], ['Ô nhiễm môi trường', 'TIÊU CỰC'], ['Kẹt xe', 'TIÊU CỰC']], 'Đô thị hóa tạo việc làm, hiện đại hóa nhưng gây ô nhiễm, kẹt xe.', $d);
        $this->sortQ($s, 'Kéo mỗi luồng di cư vào nhóm TRONG NƯỚC hoặc QUỐC TẾ.', [['Nông thôn ra thành phố', 'TRONG NƯỚC'], ['Miền núi xuống đồng bằng', 'TRONG NƯỚC'], ['Sang Hàn Quốc lao động', 'QUỐC TẾ'], ['Sang Nhật Bản du học', 'QUỐC TẾ']], 'Di cư trong nước và quốc tế đều phổ biến hiện nay.', $d);
        $this->fill($s, 'Thành phố đông dân nhất thế giới hiện nay là ___.', [[0, 'Tokyo']], 'Tokyo đông dân nhất thế giới.', $d);
        $this->fill($s, '"Chảy máu chất xám" là hiện tượng lao động trình độ ___ di cư ra nước ngoài.', [[0, 'cao']], 'Chảy máu chất xám là mất lao động trình độ cao.', $d);
        $this->fill($s, 'Đô thị hóa làm tăng tỉ lệ dân ___.', [[0, 'thành thị']], 'Đô thị hóa tăng tỉ lệ dân thành thị.', $d);
        $this->fill($s, 'Nước có "cơ cấu dân số vàng" ở Đông Nam Á là Việt ___.', [[0, 'Nam']], 'Việt Nam đang trong thời kì cơ cấu dân số vàng.', $d);
        $this->fill($s, 'Di cư từ nông thôn ra thành thị để tìm việc làm gọi là di cư kinh ___.', [[0, 'tế']], 'Di cư vì việc làm là di cư kinh tế.', $d);
    }

    private function seedDiaLyThpt11Lop113(): void
    {
        $s = 'dia-ly-thpt-11-lop-11-3'; $d = 'trung_binh';
        $this->quiz($s, 'Nước nào xuất khẩu gạo lớn nhất thế giới hiện nay?', ['Thái Lan', 'Việt Nam', 'Ấn Độ', 'Pa-ki-xtan'], 2, 'Ấn Độ là nước xuất khẩu gạo lớn nhất thế giới hiện nay.', $d);
        $this->quiz($s, 'Bra-xin là nước sản xuất lớn nhất thế giới về cây công nghiệp nào?', ['Cà phê', 'Mía đường', 'Cao su', 'Chè'], 1, 'Bra-xin là nước sản xuất mía đường lớn nhất thế giới.', $d);
        $this->quiz($s, 'Cách mạng công nghiệp lần thứ nhất gắn liền với phát minh nào?', ['Máy tính', 'Máy hơi nước', 'Điện', 'Internet'], 1, 'Máy hơi nước là phát minh tiêu biểu của cách mạng công nghiệp lần thứ nhất.', $d);
        $this->quiz($s, 'Ngành công nghiệp mũi nhọn của Hàn Quốc là gì?', ['Dệt may', 'Điện tử', 'Đóng tàu', 'Thép'], 1, 'Điện tử là ngành công nghiệp mũi nhọn của Hàn Quốc.', $d);
        $this->quiz($s, 'Nước nào được mệnh danh là "vựa bánh mì" của châu Âu?', ['Pháp', 'U-crai-na', 'Đức', 'Ba Lan'], 1, 'U-crai-na được mệnh danh là "vựa bánh mì" của châu Âu.', $d);
        $this->matching($s, 'Nối mỗi nước với nông sản xuất khẩu nổi bật của nó.', [['Việt Nam', 'Gạo'], ['Bra-xin', 'Đường mía'], ['Hoa Kỳ', 'Đậu tương'], ['Hà Lan', 'Hoa']], 'Việt Nam – gạo, Bra-xin – đường mía, Hoa Kỳ – đậu tương, Hà Lan – hoa.', $d);
        $this->matching($s, 'Nối mỗi cuộc cách mạng công nghiệp với phát minh tiêu biểu của nó.', [['Lần thứ nhất', 'Máy hơi nước'], ['Lần thứ hai', 'Điện'], ['Lần thứ ba', 'Máy tính'], ['Lần thứ tư', 'Trí tuệ nhân tạo']], 'Các phát minh: máy hơi nước, điện, máy tính, trí tuệ nhân tạo.', $d);
        $this->matching($s, 'Nối mỗi tổ chức quốc tế với lĩnh vực hoạt động của nó.', [['FAO', 'Lương thực – nông nghiệp'], ['OPEC', 'Dầu mỏ'], ['WTO', 'Thương mại'], ['UNESCO', 'Giáo dục – văn hóa']], 'FAO – nông nghiệp, OPEC – dầu mỏ, WTO – thương mại, UNESCO – giáo dục văn hóa.', $d);
        $this->matching($s, 'Nối mỗi nước với ngành công nghiệp mũi nhọn của nó.', [['Nhật Bản', 'Ô tô'], ['Hàn Quốc', 'Điện tử'], ['Đức', 'Cơ khí'], ['Trung Quốc', 'Hàng tiêu dùng']], 'Nhật Bản – ô tô, Hàn Quốc – điện tử, Đức – cơ khí, Trung Quốc – hàng tiêu dùng.', $d);
        $this->matching($s, 'Nối mỗi loại hình nông nghiệp với đặc điểm của nó.', [['Quảng canh', 'Diện tích lớn, năng suất thấp'], ['Thâm canh', 'Đầu tư lớn, năng suất cao'], ['Hữu cơ', 'Không dùng hóa chất'], ['Trang trại', 'Quy mô lớn']], 'Quảng canh diện tích lớn, thâm canh năng suất cao, hữu cơ không hóa chất.', $d);
        $this->sortQ($s, 'Kéo mỗi ngành vào nhóm CÔNG NGHIỆP NẶNG hoặc CÔNG NGHIỆP NHẸ.', [['Luyện kim', 'CÔNG NGHIỆP NẶNG'], ['Khai khoáng', 'CÔNG NGHIỆP NẶNG'], ['Dệt may', 'CÔNG NGHIỆP NHẸ'], ['Chế biến thực phẩm', 'CÔNG NGHIỆP NHẸ']], 'Luyện kim, khai khoáng là công nghiệp nặng; dệt may, thực phẩm là công nghiệp nhẹ.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Ấn Độ xuất khẩu gạo lớn nhất', 'ĐÚNG'], ['Hàn Quốc mạnh về điện tử', 'ĐÚNG'], ['Máy hơi nước thuộc cách mạng lần 2', 'SAI'], ['FAO là tổ chức dầu mỏ', 'SAI']], 'Máy hơi nước thuộc lần 1; FAO là tổ chức lương thực – nông nghiệp.', $d);
        $this->sortQ($s, 'Kéo mỗi cây trồng vào nhóm CÂY LƯƠNG THỰC hoặc CÂY CÔNG NGHIỆP.', [['Lúa', 'CÂY LƯƠNG THỰC'], ['Ngô', 'CÂY LƯƠNG THỰC'], ['Cà phê', 'CÂY CÔNG NGHIỆP'], ['Cao su', 'CÂY CÔNG NGHIỆP']], 'Lúa, ngô là lương thực; cà phê, cao su là cây công nghiệp.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn năng lượng vào nhóm TÁI TẠO hoặc KHÔNG TÁI TẠO.', [['Mặt trời', 'TÁI TẠO'], ['Sinh khối', 'TÁI TẠO'], ['Than đá', 'KHÔNG TÁI TẠO'], ['Khí đốt', 'KHÔNG TÁI TẠO']], 'Mặt trời, sinh khối tái tạo được; than đá, khí đốt không tái tạo.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm NÔNG NGHIỆP hoặc CÔNG NGHIỆP.', [['Trồng lúa', 'NÔNG NGHIỆP'], ['Chăn nuôi', 'NÔNG NGHIỆP'], ['Luyện thép', 'CÔNG NGHIỆP'], ['Lắp ráp ô tô', 'CÔNG NGHIỆP']], 'Trồng lúa, chăn nuôi là nông nghiệp; luyện thép, lắp ráp ô tô là công nghiệp.', $d);
        $this->fill($s, 'Nước xuất khẩu gạo lớn nhất thế giới hiện nay là Ấn ___.', [[0, 'Độ']], 'Ấn Độ xuất khẩu gạo lớn nhất thế giới.', $d);
        $this->fill($s, 'Cách mạng công nghiệp lần thứ nhất gắn với phát minh máy hơi ___.', [[0, 'nước']], 'Máy hơi nước mở đầu cách mạng công nghiệp lần thứ nhất.', $d);
        $this->fill($s, 'Bra-xin là nước sản xuất ___ mía lớn nhất thế giới.', [[0, 'đường']], 'Bra-xin sản xuất đường mía lớn nhất thế giới.', $d);
        $this->fill($s, 'Ngành công nghiệp mũi nhọn của Hàn Quốc là điện ___.', [[0, 'tử']], 'Điện tử là mũi nhọn của Hàn Quốc.', $d);
        $this->fill($s, 'U-crai-na được mệnh danh là "vựa bánh mì" của châu ___.', [[0, 'Âu']], 'U-crai-na là vựa bánh mì của châu Âu.', $d);
    }

    private function seedDiaLyThpt11Lop114(): void
    {
        $s = 'dia-ly-thpt-11-lop-11-4'; $d = 'kho';
        $this->quiz($s, 'Ngành nào được gọi là "ngành công nghiệp không khói"?', ['Luyện kim', 'Du lịch', 'Khai khoáng', 'Hóa chất'], 1, 'Du lịch được gọi là "ngành công nghiệp không khói".', $d);
        $this->quiz($s, 'Trung tâm tài chính lớn nhất Đông Nam Á đặt tại đâu?', ['Băng Cốc', 'Xin-ga-po', 'Gia-các-ta', 'Kuala Lumpur'], 1, 'Xin-ga-po là trung tâm tài chính lớn nhất Đông Nam Á.', $d);
        $this->quiz($s, 'Toàn cầu hóa có tác động tiêu cực nào sau đây?', ['Mở rộng thị trường', 'Gia tăng khoảng cách giàu nghèo', 'Tiếp cận công nghệ', 'Tăng giao lưu văn hóa'], 1, 'Toàn cầu hóa có thể làm gia tăng khoảng cách giàu nghèo.', $d);
        $this->quiz($s, 'Tổ chức nào sau đây là tổ chức kinh tế khu vực?', ['UNESCO', 'ASEAN', 'WHO', 'UNICEF'], 1, 'ASEAN là tổ chức kinh tế – chính trị khu vực Đông Nam Á.', $d);
        $this->quiz($s, 'Làn sóng toàn cầu hóa hiện nay gắn chặt với lĩnh vực nào?', ['Nông nghiệp', 'Công nghệ thông tin', 'Khai khoáng', 'Dệt may'], 1, 'Toàn cầu hóa hiện nay gắn chặt với công nghệ thông tin.', $d);
        $this->matching($s, 'Nối mỗi loại dịch vụ với ví dụ của nó.', [['Vận tải', 'Hàng không'], ['Viễn thông', 'Internet'], ['Tài chính', 'Ngân hàng'], ['Du lịch', 'Khách sạn']], 'Vận tải – hàng không, viễn thông – internet, tài chính – ngân hàng, du lịch – khách sạn.', $d);
        $this->matching($s, 'Nối mỗi tổ chức quốc tế với năm thành lập của nó.', [['Liên hợp quốc', '1945'], ['ASEAN', '1967'], ['WTO', '1995'], ['EU', '1993']], 'LHQ (1945), ASEAN (1967), WTO (1995), EU (1993).', $d);
        $this->matching($s, 'Nối mỗi biểu hiện toàn cầu hóa với ví dụ của nó.', [['Thương mại', 'Xuất nhập khẩu tăng'], ['Đầu tư', 'Vốn FDI'], ['Lao động', 'Di cư quốc tế'], ['Văn hóa', 'Giao lưu văn hóa']], 'Toàn cầu hóa biểu hiện qua thương mại, đầu tư FDI, di cư lao động.', $d);
        $this->matching($s, 'Nối mỗi trung tâm tài chính với khu vực của nó.', [['Niu Óoc', 'Bắc Mỹ'], ['Luân Đôn', 'Châu Âu'], ['Xin-ga-po', 'Đông Nam Á'], ['Tokyo', 'Đông Á']], 'Niu Óoc ở Bắc Mỹ, Luân Đôn ở châu Âu, Xin-ga-po ở Đông Nam Á.', $d);
        $this->matching($s, 'Nối mỗi loại hình du lịch với ví dụ ở Việt Nam.', [['Du lịch biển', 'Nha Trang'], ['Du lịch văn hóa', 'Hội An'], ['Du lịch sinh thái', 'Cúc Phương'], ['Du lịch mạo hiểm', 'Leo núi Fansipan']], 'Nha Trang – biển, Hội An – văn hóa, Cúc Phương – sinh thái.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm DỊCH VỤ hoặc SẢN XUẤT VẬT CHẤT.', [['Ngân hàng', 'DỊCH VỤ'], ['Giáo dục', 'DỊCH VỤ'], ['Trồng lúa', 'SẢN XUẤT VẬT CHẤT'], ['Luyện thép', 'SẢN XUẤT VẬT CHẤT']], 'Ngân hàng, giáo dục là dịch vụ; trồng lúa, luyện thép là sản xuất vật chất.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Du lịch là ngành công nghiệp không khói', 'ĐÚNG'], ['ASEAN thành lập năm 1967', 'ĐÚNG'], ['Toàn cầu hóa chỉ có mặt tích cực', 'SAI'], ['WTO là tổ chức quân sự', 'SAI']], 'Toàn cầu hóa có cả thách thức; WTO là tổ chức thương mại.', $d);
        $this->sortQ($s, 'Kéo mỗi tác động của toàn cầu hóa vào nhóm TÍCH CỰC hoặc THÁCH THỨC.', [['Mở rộng thị trường', 'TÍCH CỰC'], ['Tiếp cận công nghệ', 'TÍCH CỰC'], ['Cạnh tranh gay gắt', 'THÁCH THỨC'], ['Phụ thuộc kinh tế', 'THÁCH THỨC']], 'Toàn cầu hóa mở rộng thị trường nhưng gây cạnh tranh gay gắt.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ chức vào nhóm KINH TẾ hoặc PHI KINH TẾ.', [['WTO', 'KINH TẾ'], ['IMF', 'KINH TẾ'], ['UNESCO', 'PHI KINH TẾ'], ['WHO', 'PHI KINH TẾ']], 'WTO và IMF là tổ chức kinh tế; UNESCO và WHO phi kinh tế.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm TRONG NƯỚC hoặc QUỐC TẾ.', [['Chợ truyền thống', 'TRONG NƯỚC'], ['Xe ôm công nghệ', 'TRONG NƯỚC'], ['Xuất khẩu gạo', 'QUỐC TẾ'], ['Thu hút vốn FDI', 'QUỐC TẾ']], 'Chợ truyền thống trong nước; xuất khẩu và FDI mang tính quốc tế.', $d);
        $this->fill($s, 'Ngành được gọi là "ngành công nghiệp không khói" là ngành du ___.', [[0, 'lịch']], 'Du lịch là ngành công nghiệp không khói.', $d);
        $this->fill($s, 'Tổ chức ASEAN được thành lập năm ___.', [[0, '1967']], 'ASEAN thành lập năm 1967.', $d);
        $this->fill($s, 'Trung tâm tài chính lớn nhất Đông Nam Á là ___.', [[0, 'Xin-ga-po']], 'Xin-ga-po là trung tâm tài chính lớn nhất Đông Nam Á.', $d);
        $this->fill($s, 'Toàn cầu hóa làm gia tăng khoảng cách giàu ___.', [[0, 'nghèo']], 'Toàn cầu hóa có thể làm giàu nghèo phân hóa.', $d);
        $this->fill($s, 'Vốn đầu tư trực tiếp từ nước ngoài viết tắt là ___.', [[0, 'FDI']], 'FDI là vốn đầu tư trực tiếp nước ngoài.', $d);
    }

    private function seedDiaLyThpt12Lop121(): void
    {
        $s = 'dia-ly-thpt-12-lop-12-1'; $d = 'trung_binh';
        $this->quiz($s, 'Việt Nam nằm ở múi giờ thứ mấy (so với GMT)?', ['GMT+6', 'GMT+7', 'GMT+8', 'GMT+9'], 1, 'Việt Nam nằm ở múi giờ GMT+7.', $d);
        $this->quiz($s, 'Điểm cực Tây của Việt Nam thuộc tỉnh nào?', ['Lai Châu', 'Sơn La', 'Điện Biên', 'Lào Cai'], 2, 'Điểm cực Tây ở A Pa Chải, tỉnh Điện Biên.', $d);
        $this->quiz($s, 'Diện tích tự nhiên của Việt Nam xếp thứ mấy ở Đông Nam Á?', ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5'], 2, 'Việt Nam xếp thứ 4 Đông Nam Á về diện tích (sau Indonesia, Mi-an-ma, Thái Lan).', $d);
        $this->quiz($s, 'Đường biên giới trên đất liền của Việt Nam dài khoảng bao nhiêu?', ['3 260 km', '4 639 km', '5 000 km', '2 000 km'], 1, 'Biên giới đất liền của Việt Nam dài khoảng 4 639 km.', $d);
        $this->quiz($s, 'Việt Nam có bao nhiêu tỉnh, thành phố giáp biển?', ['20', '24', '28', '32'], 2, 'Việt Nam có 28 tỉnh, thành phố giáp biển.', $d);
        $this->matching($s, 'Nối mỗi số liệu với nội dung tương ứng của Việt Nam.', [['331 212 km²', 'Diện tích tự nhiên'], ['3 260 km', 'Đường bờ biển'], ['4 639 km', 'Biên giới đất liền'], ['1 triệu km²', 'Vùng biển']], 'Diện tích 331 212 km², bờ biển 3 260 km, biên giới đất liền 4 639 km.', $d);
        $this->matching($s, 'Nối mỗi vùng núi với đặc điểm của nó.', [['Tây Bắc', 'Cao nhất'], ['Đông Bắc', 'Cánh cung'], ['Bắc Trường Sơn', 'Hẹp ngang'], ['Nam Trường Sơn', 'Cao nguyên']], 'Tây Bắc cao nhất, Đông Bắc cánh cung, Trường Sơn Nam nhiều cao nguyên.', $d);
        $this->matching($s, 'Nối mỗi đồng bằng với diện tích của nó.', [['Đồng bằng sông Cửu Long', 'Khoảng 40 000 km²'], ['Đồng bằng sông Hồng', 'Khoảng 15 000 km²'], ['Duyên hải miền Trung', 'Hẹp, nhỏ']], 'ĐBSCL khoảng 40 000 km², ĐBSH khoảng 15 000 km².', $d);
        $this->matching($s, 'Nối mỗi hướng núi chính với vùng thể hiện rõ nhất.', [['Tây bắc – đông nam', 'Tây Bắc'], ['Vòng cung', 'Đông Bắc'], ['Tây – đông', 'Trường Sơn Bắc']], 'Tây Bắc hướng tây bắc – đông nam, Đông Bắc hướng vòng cung.', $d);
        $this->matching($s, 'Nối mỗi tỉnh với vị trí cực của nó.', [['Hà Giang', 'Cực Bắc'], ['Điện Biên', 'Cực Tây'], ['Cà Mau', 'Cực Nam'], ['Khánh Hòa', 'Cực Đông']], 'Bốn cực: Hà Giang, Điện Biên, Cà Mau, Khánh Hòa.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉnh vào nhóm Ở ĐỒI NÚI hoặc Ở ĐỒNG BẰNG.', [['Lai Châu', 'Ở ĐỒI NÚI'], ['Đắk Lắk', 'Ở ĐỒI NÚI'], ['Thái Bình', 'Ở ĐỒNG BẰNG'], ['Đồng Tháp', 'Ở ĐỒNG BẰNG']], 'Lai Châu và Đắk Lắk ở đồi núi; Thái Bình và Đồng Tháp ở đồng bằng.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Việt Nam ở múi giờ GMT+7', 'ĐÚNG'], ['Diện tích Việt Nam khoảng 331 nghìn km²', 'ĐÚNG'], ['Biên giới đất liền dài hơn bờ biển', 'ĐÚNG'], ['Việt Nam có 20 tỉnh giáp biển', 'SAI']], 'Biên giới đất liền (4 639 km) dài hơn bờ biển (3 260 km); Việt Nam có 28 tỉnh giáp biển.', $d);
        $this->sortQ($s, 'Kéo mỗi đỉnh núi vào nhóm CAO TRÊN hoặc DƯỚI 3.000 m.', [['Fansipan', 'TRÊN 3.000 m'], ['Pu-xi-lung', 'TRÊN 3.000 m'], ['Bạch Mã', 'DƯỚI 3.000 m'], ['Bà Đen', 'DƯỚI 3.000 m']], 'Fansipan và Pu-xi-lung trên 3 000 m; Bạch Mã và Bà Đen dưới 3 000 m.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉnh/thành phố vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.', [['Lào Cai', 'MIỀN BẮC'], ['Hải Phòng', 'MIỀN BẮC'], ['Nghệ An', 'MIỀN TRUNG'], ['Đà Nẵng', 'MIỀN TRUNG'], ['Cần Thơ', 'MIỀN NAM'], ['TP. Hồ Chí Minh', 'MIỀN NAM']], 'Lào Cai, Hải Phòng miền Bắc; Nghệ An, Đà Nẵng miền Trung; Cần Thơ, TP.HCM miền Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi tỉnh vào nhóm GIÁP BIỂN hoặc KHÔNG GIÁP BIỂN.', [['Thanh Hóa', 'GIÁP BIỂN'], ['Phú Yên', 'GIÁP BIỂN'], ['Sơn La', 'KHÔNG GIÁP BIỂN'], ['Gia Lai', 'KHÔNG GIÁP BIỂN']], 'Thanh Hóa và Phú Yên giáp biển; Sơn La và Gia Lai không giáp biển.', $d);
        $this->fill($s, 'Việt Nam nằm ở múi giờ GMT+___.', [[0, '7']], 'Việt Nam ở múi giờ GMT+7.', $d);
        $this->fill($s, 'Điểm cực Tây của Việt Nam thuộc tỉnh Điện ___.', [[0, 'Biên']], 'Cực Tây ở A Pa Chải, Điện Biên.', $d);
        $this->fill($s, 'Đường biên giới trên đất liền của Việt Nam dài khoảng 4 639 ___.', [[0, 'km']], 'Biên giới đất liền dài khoảng 4 639 km.', $d);
        $this->fill($s, 'Việt Nam có ___ tỉnh, thành phố giáp biển.', [[0, '28']], 'Việt Nam có 28 tỉnh, thành phố giáp biển.', $d);
        $this->fill($s, 'Diện tích vùng biển Việt Nam khoảng 1 triệu ___ vuông.', [[0, 'km']], 'Vùng biển Việt Nam khoảng 1 triệu km².', $d);
    }

    private function seedDiaLyThpt12Lop122(): void
    {
        $s = 'dia-ly-thpt-12-lop-12-2'; $d = 'kho';
        $this->quiz($s, 'Sông nào có diện tích lưu vực lớn nhất Việt Nam?', ['Sông Hồng', 'Sông Mê Kông', 'Sông Đồng Nai', 'Sông Mã'], 1, 'Sông Mê Kông có diện tích lưu vực lớn nhất Việt Nam.', $d);
        $this->quiz($s, 'Đất phù sa thích hợp nhất để trồng cây gì?', ['Cà phê', 'Lúa nước', 'Cao su', 'Chè'], 1, 'Đất phù sa màu mỡ thích hợp trồng lúa nước.', $d);
        $this->quiz($s, 'Mùa khô ở Tây Nguyên và Nam Bộ kéo dài từ tháng nào?', ['Tháng 5 đến tháng 10', 'Tháng 11 đến tháng 4 năm sau', 'Cả năm', 'Tháng 1 đến tháng 3'], 1, 'Mùa khô ở Tây Nguyên và Nam Bộ từ tháng 11 đến tháng 4 năm sau.', $d);
        $this->quiz($s, 'Loại đất nào thích hợp trồng cây công nghiệp lâu năm như cà phê?', ['Đất phù sa', 'Đất đỏ ba-dan', 'Đất cát', 'Đất phèn'], 1, 'Đất đỏ ba-dan thích hợp trồng cà phê, cao su.', $d);
        $this->quiz($s, 'Sông Hồng có hai phụ lưu lớn là sông Đà và sông nào?', ['Sông Lô', 'Sông Cầu', 'Sông Đuống', 'Sông Luộc'], 0, 'Sông Đà và sông Lô là hai phụ lưu lớn của sông Hồng.', $d);
        $this->matching($s, 'Nối mỗi con sông với đặc điểm khác của nó.', [['Sông Mê Kông', 'Dài nhất Đông Nam Á'], ['Sông Hồng', 'Lớn nhất miền Bắc'], ['Sông Đồng Nai', 'Lớn nhất Đông Nam Bộ'], ['Sông Cửu Long', 'Đổ ra biển bằng 9 cửa']], 'Mê Kông dài nhất Đông Nam Á, sông Hồng lớn nhất miền Bắc.', $d);
        $this->matching($s, 'Nối mỗi loại đất với cây trồng phù hợp của nó.', [['Đất phù sa', 'Lúa nước'], ['Đất đỏ ba-dan', 'Cà phê'], ['Đất feralit', 'Cây công nghiệp'], ['Đất cát', 'Điều, dừa']], 'Phù sa – lúa, ba-dan – cà phê, feralit – cây công nghiệp, cát – điều dừa.', $d);
        $this->matching($s, 'Nối mỗi mùa với đặc điểm ở miền Trung.', [['Mùa mưa', 'Thu đông'], ['Mùa khô', 'Mùa hè'], ['Mùa bão', 'Cuối hè đầu thu'], ['Mùa lũ', 'Mùa thu']], 'Miền Trung mưa vào thu đông, lũ vào mùa thu.', $d);
        $this->matching($s, 'Nối mỗi miền với kiểu khí hậu của nó.', [['Miền Bắc', 'Cận nhiệt đới gió mùa'], ['Miền Trung', 'Nhiệt đới gió mùa'], ['Miền Nam', 'Nhiệt đới gió mùa'], ['Tây Nguyên', 'Cận xích đạo']], 'Miền Bắc cận nhiệt đới; miền Trung, Nam nhiệt đới gió mùa.', $d);
        $this->matching($s, 'Nối mỗi con sông với tỉnh nó chảy qua.', [['Sông Đà', 'Hòa Bình'], ['Sông Lô', 'Tuyên Quang'], ['Sông Thu Bồn', 'Quảng Nam'], ['Sông Vàm Cỏ', 'Long An']], 'Sông Đà qua Hòa Bình, sông Lô qua Tuyên Quang, Thu Bồn qua Quảng Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi con sông vào nhóm Ở MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.', [['Sông Cầu', 'MIỀN BẮC'], ['Sông Đuống', 'MIỀN BẮC'], ['Sông Gianh', 'MIỀN TRUNG'], ['Sông Hàn', 'MIỀN TRUNG'], ['Sông Tiền', 'MIỀN NAM'], ['Sông Hậu', 'MIỀN NAM']], 'Sông Cầu, Đuống miền Bắc; Gianh, Hàn miền Trung; Tiền, Hậu miền Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Sông Mê Kông dài nhất Đông Nam Á', 'ĐÚNG'], ['Mùa khô Tây Nguyên từ tháng 11', 'ĐÚNG'], ['Đất feralit ở đồng bằng', 'SAI'], ['Miền Bắc không có mùa đông', 'SAI']], 'Đất feralit ở đồi núi; miền Bắc có mùa đông lạnh.', $d);
        $this->sortQ($s, 'Kéo mỗi loại đất vào nhóm ĐẤT ĐỒNG BẰNG hoặc ĐẤT ĐỒI NÚI.', [['Đất phù sa', 'ĐẤT ĐỒNG BẰNG'], ['Đất phèn', 'ĐẤT ĐỒNG BẰNG'], ['Đất feralit', 'ĐẤT ĐỒI NÚI'], ['Đất đỏ ba-dan', 'ĐẤT ĐỒI NÚI']], 'Phù sa, phèn ở đồng bằng; feralit, ba-dan ở đồi núi.', $d);
        $this->sortQ($s, 'Kéo mỗi tháng vào nhóm MÙA MƯA hoặc MÙA KHÔ (Tây Nguyên – Nam Bộ).', [['Tháng 7', 'MÙA MƯA'], ['Tháng 8', 'MÙA MƯA'], ['Tháng 1', 'MÙA KHÔ'], ['Tháng 2', 'MÙA KHÔ']], 'Tây Nguyên – Nam Bộ mưa từ tháng 5 đến tháng 10, khô từ tháng 11 đến tháng 4.', $d);
        $this->sortQ($s, 'Kéo mỗi cây trồng vào nhóm CÂY LƯƠNG THỰC hoặc CÂY CÔNG NGHIỆP.', [['Lúa', 'CÂY LƯƠNG THỰC'], ['Ngô', 'CÂY LƯƠNG THỰC'], ['Cà phê', 'CÂY CÔNG NGHIỆP'], ['Chè', 'CÂY CÔNG NGHIỆP']], 'Lúa, ngô là lương thực; cà phê, chè là công nghiệp.', $d);
        $this->fill($s, 'Sông có diện tích lưu vực lớn nhất Việt Nam là sông ___.', [[0, 'Mê Kông']], 'Sông Mê Kông có lưu vực lớn nhất Việt Nam.', $d);
        $this->fill($s, 'Đất phù sa thích hợp nhất để trồng ___ nước.', [[0, 'lúa']], 'Đất phù sa trồng lúa nước tốt nhất.', $d);
        $this->fill($s, 'Mùa khô ở Tây Nguyên kéo dài từ tháng 11 đến tháng ___ năm sau.', [[0, '4']], 'Mùa khô Tây Nguyên từ tháng 11 đến tháng 4.', $d);
        $this->fill($s, 'Đất đỏ ba-dan thích hợp trồng cây công nghiệp lâu năm như cà ___.', [[0, 'phê']], 'Cà phê ưa đất đỏ ba-dan.', $d);
        $this->fill($s, 'Sông Hồng có hai phụ lưu lớn là sông Đà và sông ___.', [[0, 'Lô']], 'Sông Đà và sông Lô là phụ lưu lớn của sông Hồng.', $d);
    }

    private function seedDiaLyThpt12Lop123(): void
    {
        $s = 'dia-ly-thpt-12-lop-12-3'; $d = 'trung_binh';
        $this->quiz($s, 'Việt Nam là nước xuất khẩu cà phê lớn thứ mấy thế giới?', ['Thứ nhất', 'Thứ hai', 'Thứ ba', 'Thứ tư'], 1, 'Việt Nam là nước xuất khẩu cà phê lớn thứ hai thế giới, sau Bra-xin.', $d);
        $this->quiz($s, 'Vùng nào trồng chè nhiều nhất Việt Nam?', ['Tây Nguyên', 'Trung du miền núi phía Bắc', 'Đông Nam Bộ', 'Duyên hải miền Trung'], 1, 'Trung du miền núi phía Bắc (Thái Nguyên) trồng chè nhiều nhất.', $d);
        $this->quiz($s, 'Vật nuôi nào có số lượng lớn nhất ở Việt Nam?', ['Trâu', 'Bò', 'Lợn', 'Dê'], 2, 'Lợn là vật nuôi có số lượng lớn nhất ở Việt Nam.', $d);
        $this->quiz($s, 'Mô hình "lúa – tôm" phổ biến ở vùng nào?', ['Đồng bằng sông Hồng', 'Đồng bằng sông Cửu Long', 'Duyên hải miền Trung', 'Tây Nguyên'], 1, 'Mô hình lúa – tôm phổ biến ở Đồng bằng sông Cửu Long.', $d);
        $this->quiz($s, 'Việt Nam đứng đầu thế giới về xuất khẩu mặt hàng nào?', ['Gạo', 'Hạt điều', 'Cà phê', 'Chè'], 1, 'Việt Nam đứng đầu thế giới về xuất khẩu hạt điều.', $d);
        $this->matching($s, 'Nối mỗi cây công nghiệp với vùng trồng chính của nó.', [['Chè', 'Thái Nguyên'], ['Cao su', 'Đông Nam Bộ'], ['Hồ tiêu', 'Tây Nguyên'], ['Điều', 'Bình Phước']], 'Chè ở Thái Nguyên, cao su ở Đông Nam Bộ, hồ tiêu ở Tây Nguyên, điều ở Bình Phước.', $d);
        $this->matching($s, 'Nối mỗi mặt hàng nông sản với thứ hạng xuất khẩu của Việt Nam.', [['Gạo', 'Top 3 thế giới'], ['Cà phê', 'Thứ 2 thế giới'], ['Hạt điều', 'Thứ 1 thế giới'], ['Hồ tiêu', 'Thứ 1 thế giới']], 'Gạo top 3, cà phê thứ 2, hạt điều và hồ tiêu thứ 1 thế giới.', $d);
        $this->matching($s, 'Nối mỗi vật nuôi với vùng chăn nuôi chính của nó.', [['Trâu', 'Miền núi phía Bắc'], ['Bò sữa', 'Mộc Châu'], ['Lợn', 'Đồng bằng'], ['Tôm', 'Đồng bằng sông Cửu Long']], 'Trâu ở miền núi, bò sữa ở Mộc Châu, lợn ở đồng bằng, tôm ở ĐBSCL.', $d);
        $this->matching($s, 'Nối mỗi mô hình sản xuất với vùng áp dụng của nó.', [['Lúa – tôm', 'Đồng bằng sông Cửu Long'], ['Chè – rừng', 'Trung du'], ['Cà phê – hồ tiêu', 'Tây Nguyên'], ['Vườn – ao – chuồng', 'Nhiều vùng']], 'Lúa – tôm ở ĐBSCL, chè – rừng ở trung du, cà phê – hồ tiêu ở Tây Nguyên.', $d);
        $this->matching($s, 'Nối mỗi vụ lúa với thời gian gieo trồng của nó.', [['Vụ đông xuân', 'Cuối năm – đầu năm sau'], ['Vụ hè thu', 'Giữa năm'], ['Vụ mùa', 'Cuối năm']], 'Đông xuân cuối năm – đầu năm, hè thu giữa năm, vụ mùa cuối năm.', $d);
        $this->sortQ($s, 'Kéo mỗi cây trồng vào nhóm CÂY LƯƠNG THỰC hoặc CÂY CÔNG NGHIỆP.', [['Lúa', 'CÂY LƯƠNG THỰC'], ['Ngô', 'CÂY LƯƠNG THỰC'], ['Khoai', 'CÂY LƯƠNG THỰC'], ['Cà phê', 'CÂY CÔNG NGHIỆP'], ['Hồ tiêu', 'CÂY CÔNG NGHIỆP'], ['Điều', 'CÂY CÔNG NGHIỆP']], 'Lúa, ngô, khoai là lương thực; cà phê, hồ tiêu, điều là công nghiệp.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Việt Nam xuất khẩu cà phê thứ 2 thế giới', 'ĐÚNG'], ['Hạt điều Việt Nam thứ 1 thế giới', 'ĐÚNG'], ['Chè trồng nhiều ở Tây Nguyên', 'SAI'], ['Lúa – tôm ở đồng bằng sông Hồng', 'SAI']], 'Chè trồng nhiều ở trung du phía Bắc; lúa – tôm ở ĐBSCL.', $d);
        $this->sortQ($s, 'Kéo mỗi sản phẩm vào nhóm LƯƠNG THỰC, CÔNG NGHIỆP hoặc THỦY SẢN.', [['Gạo', 'LƯƠNG THỰC'], ['Sắn', 'LƯƠNG THỰC'], ['Cà phê', 'CÔNG NGHIỆP'], ['Cao su', 'CÔNG NGHIỆP'], ['Tôm', 'THỦY SẢN'], ['Cá tra', 'THỦY SẢN']], 'Gạo, sắn là lương thực; cà phê, cao su là công nghiệp; tôm, cá tra là thủy sản.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm THUẬN LỢI hoặc KHÓ KHĂN cho nông nghiệp.', [['Đất phù sa màu mỡ', 'THUẬN LỢI'], ['Lao động dồi dào', 'THUẬN LỢI'], ['Thiên tai', 'KHÓ KHĂN'], ['Sâu bệnh', 'KHÓ KHĂN']], 'Đất tốt, lao động dồi dào là thuận lợi; thiên tai, sâu bệnh là khó khăn.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm TRỒNG TRỌT hoặc CHĂN NUÔI.', [['Trồng lúa', 'TRỒNG TRỌT'], ['Trồng cà phê', 'TRỒNG TRỌT'], ['Nuôi trâu', 'CHĂN NUÔI'], ['Nuôi lợn', 'CHĂN NUÔI']], 'Trồng lúa, cà phê là trồng trọt; nuôi trâu, lợn là chăn nuôi.', $d);
        $this->fill($s, 'Việt Nam là nước xuất khẩu cà phê lớn thứ ___ thế giới.', [[0, '2']], 'Việt Nam xuất khẩu cà phê thứ 2 thế giới.', $d);
        $this->fill($s, 'Vùng trồng chè nhiều nhất Việt Nam là trung du miền núi phía ___.', [[0, 'Bắc']], 'Trung du miền núi phía Bắc trồng chè nhiều nhất.', $d);
        $this->fill($s, 'Mô hình "lúa – tôm" phổ biến ở đồng bằng sông Cửu ___.', [[0, 'Long']], 'Lúa – tôm phổ biến ở ĐBSCL.', $d);
        $this->fill($s, 'Việt Nam đứng đầu thế giới về xuất khẩu hạt ___.', [[0, 'điều']], 'Hạt điều Việt Nam đứng đầu thế giới.', $d);
        $this->fill($s, 'Vật nuôi có số lượng lớn nhất ở Việt Nam là ___.', [[0, 'lợn']], 'Lợn là vật nuôi đông nhất Việt Nam.', $d);
    }

    private function seedDiaLyThpt12Lop124(): void
    {
        $s = 'dia-ly-thpt-12-lop-12-4'; $d = 'kho';
        $this->quiz($s, 'Nhà máy thủy điện nào lớn nhất Việt Nam?', ['Hòa Bình', 'Sơn La', 'Trị An', 'Thác Bà'], 1, 'Thủy điện Sơn La là nhà máy thủy điện lớn nhất Việt Nam.', $d);
        $this->quiz($s, 'Trung tâm công nghiệp lớn thứ hai của Việt Nam (sau TP. Hồ Chí Minh) là:', ['Hải Phòng', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ'], 1, 'Hà Nội là trung tâm công nghiệp lớn thứ hai sau TP. Hồ Chí Minh.', $d);
        $this->quiz($s, 'Tuyến đường sắt nào dài nhất Việt Nam?', ['Hà Nội – Lào Cai', 'Bắc – Nam (Thống Nhất)', 'Hà Nội – Hải Phòng', 'Sài Gòn – Nha Trang'], 1, 'Đường sắt Bắc – Nam (Thống Nhất) dài 1 726 km, dài nhất Việt Nam.', $d);
        $this->quiz($s, 'Sân bay quốc tế nào lớn nhất Việt Nam?', ['Nội Bài', 'Tân Sơn Nhất', 'Đà Nẵng', 'Cam Ranh'], 1, 'Sân bay Tân Sơn Nhất (TP. Hồ Chí Minh) lớn nhất Việt Nam.', $d);
        $this->quiz($s, 'Loại hình giao thông nào vận chuyển hàng hóa nhiều nhất ở Việt Nam?', ['Đường bộ', 'Đường sắt', 'Đường biển', 'Đường hàng không'], 2, 'Đường biển vận chuyển khối lượng hàng hóa lớn nhất.', $d);
        $this->matching($s, 'Nối mỗi ngành công nghiệp với trung tâm chính của nó.', [['Dầu khí', 'Vũng Tàu'], ['Điện tử', 'Bắc Ninh'], ['Dệt may', 'TP. Hồ Chí Minh'], ['Xi măng', 'Hải Phòng']], 'Dầu khí ở Vũng Tàu, điện tử ở Bắc Ninh, dệt may ở TP.HCM.', $d);
        $this->matching($s, 'Nối mỗi điểm du lịch với loại hình của nó.', [['Hạ Long', 'Biển đảo'], ['Hội An', 'Văn hóa'], ['Sa Pa', 'Núi'], ['Phong Nha', 'Hang động']], 'Hạ Long – biển đảo, Hội An – văn hóa, Sa Pa – núi, Phong Nha – hang động.', $d);
        $this->matching($s, 'Nối mỗi tuyến giao thông với loại hình của nó.', [['Bắc – Nam', 'Đường sắt'], ['Cát Lái', 'Đường biển'], ['Nội Bài – Lào Cai', 'Đường bộ cao tốc'], ['Tân Sơn Nhất', 'Đường hàng không']], 'Bắc – Nam là đường sắt, Cát Lái là cảng biển, Tân Sơn Nhất là hàng không.', $d);
        $this->matching($s, 'Nối mỗi khu công nghiệp với tỉnh của nó.', [['Thăng Long', 'Hà Nội'], ['VSIP', 'Bình Dương'], ['Dung Quất', 'Quảng Ngãi'], ['Phú Mỹ', 'Bà Rịa – Vũng Tàu']], 'Thăng Long ở Hà Nội, VSIP ở Bình Dương, Dung Quất ở Quảng Ngãi.', $d);
        $this->matching($s, 'Nối mỗi nhà máy điện với loại hình của nó.', [['Sơn La', 'Thủy điện'], ['Phú Mỹ', 'Nhiệt điện'], ['Bạc Liêu', 'Điện gió'], ['Ninh Thuận', 'Điện mặt trời']], 'Sơn La – thủy điện, Phú Mỹ – nhiệt điện, Bạc Liêu – điện gió.', $d);
        $this->sortQ($s, 'Kéo mỗi ngành vào nhóm CÔNG NGHIỆP NẶNG hoặc CÔNG NGHIỆP NHẸ.', [['Luyện kim', 'CÔNG NGHIỆP NẶNG'], ['Hóa chất', 'CÔNG NGHIỆP NẶNG'], ['Dệt may', 'CÔNG NGHIỆP NHẸ'], ['Da giày', 'CÔNG NGHIỆP NHẸ']], 'Luyện kim, hóa chất là công nghiệp nặng; dệt may, da giày là nhẹ.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Sơn La là thủy điện lớn nhất', 'ĐÚNG'], ['Cát Lái là cảng biển lớn nhất', 'ĐÚNG'], ['Dung Quất ở Quảng Nam', 'SAI'], ['Tân Sơn Nhất ở Hà Nội', 'SAI']], 'Dung Quất ở Quảng Ngãi; Tân Sơn Nhất ở TP. Hồ Chí Minh.', $d);
        $this->sortQ($s, 'Kéo mỗi điểm du lịch vào nhóm Ở MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.', [['Hạ Long', 'MIỀN BẮC'], ['Sa Pa', 'MIỀN BẮC'], ['Hội An', 'MIỀN TRUNG'], ['Nha Trang', 'MIỀN TRUNG'], ['Phú Quốc', 'MIỀN NAM'], ['Cần Thơ', 'MIỀN NAM']], 'Hạ Long, Sa Pa miền Bắc; Hội An, Nha Trang miền Trung; Phú Quốc, Cần Thơ miền Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm CÔNG NGHIỆP hoặc DỊCH VỤ.', [['Luyện thép', 'CÔNG NGHIỆP'], ['Đóng tàu', 'CÔNG NGHIỆP'], ['Ngân hàng', 'DỊCH VỤ'], ['Du lịch', 'DỊCH VỤ']], 'Luyện thép, đóng tàu là công nghiệp; ngân hàng, du lịch là dịch vụ.', $d);
        $this->sortQ($s, 'Kéo mỗi tuyến vào nhóm ĐƯỜNG BỘ, ĐƯỜNG SẮT, ĐƯỜNG BIỂN hoặc HÀNG KHÔNG.', [['Xe khách Bắc – Nam', 'ĐƯỜNG BỘ'], ['Tàu Thống Nhất', 'ĐƯỜNG SẮT'], ['Cảng Cát Lái', 'ĐƯỜNG BIỂN'], ['Sân bay Nội Bài', 'HÀNG KHÔNG']], 'Xe khách – đường bộ, tàu Thống Nhất – đường sắt, Cát Lái – đường biển, Nội Bài – hàng không.', $d);
        $this->fill($s, 'Nhà máy thủy điện lớn nhất Việt Nam là thủy điện Sơn ___.', [[0, 'La']], 'Thủy điện Sơn La lớn nhất Việt Nam.', $d);
        $this->fill($s, 'Tuyến đường sắt dài nhất Việt Nam là tuyến Bắc – ___.', [[0, 'Nam']], 'Đường sắt Bắc – Nam dài 1 726 km.', $d);
        $this->fill($s, 'Sân bay quốc tế lớn nhất Việt Nam là ___ Sơn Nhất.', [[0, 'Tân']], 'Sân bay Tân Sơn Nhất lớn nhất Việt Nam.', $d);
        $this->fill($s, 'Loại hình vận chuyển hàng hóa nhiều nhất ở Việt Nam là đường ___.', [[0, 'biển']], 'Đường biển vận chuyển hàng hóa nhiều nhất.', $d);
        $this->fill($s, 'Khu kinh tế lọc dầu đầu tiên của Việt Nam là Dung ___.', [[0, 'Quất']], 'Dung Quất (Quảng Ngãi) có nhà máy lọc dầu đầu tiên.', $d);
    }

    private function seedKhHeXuong(): void
    {
        $s = 'kh-he-xuong'; $d = 'de';
        $this->quiz($s, 'Người trưởng thành có khoảng bao nhiêu chiếc xương?', ['106', '206', '306', '406'], 1, 'Người trưởng thành có khoảng 206 chiếc xương.', $d);
        $this->quiz($s, 'Xương nào tạo thành lồng ngực bảo vệ tim và phổi?', ['Xương sọ', 'Xương sườn', 'Xương đùi', 'Xương sống'], 1, 'Xương sườn tạo thành lồng ngực bảo vệ tim và phổi.', $d);
        $this->quiz($s, 'Khớp có vai trò gì?', ['Bảo vệ não', 'Giúp các xương cử động', 'Sản sinh máu', 'Tiêu hóa thức ăn'], 1, 'Khớp là nơi hai xương nối nhau, giúp xương cử động được.', $d);
        $this->quiz($s, 'Chất nào giúp xương chắc khỏe?', ['Đường', 'Canxi', 'Muối', 'Dầu mỡ'], 1, 'Canxi là chất giúp xương chắc khỏe.', $d);
        $this->quiz($s, 'Xương nhỏ nhất trong cơ thể người nằm ở đâu?', ['Ngón tay', 'Tai', 'Mũi', 'Mắt'], 1, 'Xương nhỏ nhất cơ thể nằm ở tai trong.', $d);
        $this->matching($s, 'Nối mỗi xương với vai trò của nó.', [['Xương sườn', 'Bảo vệ tim, phổi'], ['Xương sống', 'Nâng đỡ cơ thể'], ['Xương đùi', 'Chịu lực khi đi đứng'], ['Xương bàn tay', 'Cầm nắm đồ vật']], 'Xương sườn bảo vệ tim phổi, xương sống nâng đỡ, xương đùi chịu lực.', $d);
        $this->matching($s, 'Nối mỗi bộ phận với mô tả đúng.', [['Khớp', 'Nơi hai xương nối nhau'], ['Cơ', 'Giúp xương cử động'], ['Tủy xương', 'Sản sinh tế bào máu'], ['Sụn', 'Giảm ma sát ở khớp']], 'Khớp nối hai xương, cơ giúp cử động, tủy xương sinh tế bào máu.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với tác dụng của nó với xương.', [['Canxi', 'Giúp xương chắc khỏe'], ['Vitamin D', 'Giúp hấp thụ canxi'], ['Tập thể dục', 'Xương khỏe mạnh'], ['Ánh nắng', 'Giúp tổng hợp vitamin D']], 'Canxi chắc xương, vitamin D giúp hấp thụ canxi, tập thể dục tốt cho xương.', $d);
        $this->matching($s, 'Nối mỗi xương với vị trí của nó.', [['Xương sọ', 'Đầu'], ['Xương sườn', 'Ngực'], ['Xương đùi', 'Chân'], ['Xương cánh tay', 'Tay']], 'Xương sọ ở đầu, xương sườn ở ngực, xương đùi ở chân.', $d);
        $this->matching($s, 'Nối mỗi thói quen với tác dụng của nó.', [['Uống sữa', 'Bổ sung canxi'], ['Tắm nắng', 'Có vitamin D'], ['Ngồi đúng tư thế', 'Không cong vẹo cột sống'], ['Mang vác nặng', 'Hại cột sống']], 'Uống sữa bổ sung canxi, tắm nắng có vitamin D, mang vác nặng hại cột sống.', $d);
        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm THUỘC HỆ XƯƠNG hoặc KHÔNG THUỘC HỆ XƯƠNG.', [['Xương đùi', 'THUỘC HỆ XƯƠNG'], ['Khớp', 'THUỘC HỆ XƯƠNG'], ['Tim', 'KHÔNG THUỘC'], ['Phổi', 'KHÔNG THUỘC']], 'Xương đùi và khớp thuộc hệ xương; tim và phổi thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi thói quen vào nhóm TỐT CHO XƯƠNG hoặc HẠI CHO XƯƠNG.', [['Uống sữa', 'TỐT CHO XƯƠNG'], ['Tập thể dục', 'TỐT CHO XƯƠNG'], ['Ngồi sai tư thế', 'HẠI CHO XƯƠNG'], ['Mang vác nặng', 'HẠI CHO XƯƠNG']], 'Uống sữa, tập thể dục tốt cho xương; ngồi sai tư thế, mang vác nặng có hại.', $d);
        $this->sortQ($s, 'Kéo mỗi xương vào nhóm BẢO VỆ CƠ QUAN hoặc GIÚP VẬN ĐỘNG.', [['Xương sọ', 'BẢO VỆ CƠ QUAN'], ['Xương sườn', 'BẢO VỆ CƠ QUAN'], ['Xương tay', 'GIÚP VẬN ĐỘNG'], ['Xương chân', 'GIÚP VẬN ĐỘNG']], 'Xương sọ, xương sườn bảo vệ cơ quan; xương tay, chân giúp vận động.', $d);
        $this->sortQ($s, 'Kéo mỗi thực phẩm vào nhóm GIÀU CANXI hoặc ÍT CANXI.', [['Sữa', 'GIÀU CANXI'], ['Tôm', 'GIÀU CANXI'], ['Kẹo', 'ÍT CANXI'], ['Nước ngọt', 'ÍT CANXI']], 'Sữa và tôm giàu canxi; kẹo và nước ngọt ít canxi.', $d);
        $this->sortQ($s, 'Kéo mỗi xương vào nhóm XƯƠNG DÀI hoặc XƯƠNG NGẮN/DẸT.', [['Xương đùi', 'XƯƠNG DÀI'], ['Xương cánh tay', 'XƯƠNG DÀI'], ['Xương sọ', 'XƯƠNG DẸT'], ['Xương sườn', 'XƯƠNG DẸT']], 'Xương đùi, xương cánh tay là xương dài; xương sọ, xương sườn là xương dẹt.', $d);
        $this->fill($s, 'Người trưởng thành có khoảng 206 chiếc ___.', [[0, 'xương']], 'Người trưởng thành có khoảng 206 chiếc xương.', $d);
        $this->fill($s, 'Nơi hai xương nối với nhau, giúp cử động gọi là ___.', [[0, 'khớp']], 'Khớp giúp các xương cử động được với nhau.', $d);
        $this->fill($s, 'Xương sườn tạo thành lồng ngực bảo vệ tim và ___.', [[0, 'phổi']], 'Lồng ngực bảo vệ tim và phổi.', $d);
        $this->fill($s, 'Chất giúp xương chắc khỏe là ___.', [[0, 'canxi']], 'Canxi giúp xương chắc khỏe.', $d);
        $this->fill($s, 'Xương nhỏ nhất trong cơ thể nằm ở ___.', [[0, 'tai']], 'Xương nhỏ nhất cơ thể nằm ở tai trong.', $d);
    }

    private function seedKhHeTieuHoa(): void
    {
        $s = 'kh-he-tieu-hoa'; $d = 'trung_binh';
        $this->quiz($s, 'Quá trình tiêu hóa thức ăn bắt đầu từ đâu?', ['Dạ dày', 'Miệng', 'Ruột non', 'Thực quản'], 1, 'Tiêu hóa bắt đầu từ miệng, nơi thức ăn được nhai nhỏ.', $d);
        $this->quiz($s, 'Dịch vị do dạ dày tiết ra có tác dụng gì?', ['Hấp thụ chất dinh dưỡng', 'Tiêu hóa thức ăn', 'Vận chuyển máu', 'Lọc chất độc'], 1, 'Dịch vị giúp tiêu hóa thức ăn trong dạ dày.', $d);
        $this->quiz($s, 'Ruột non có vai trò quan trọng nào?', ['Nhào trộn thức ăn', 'Hấp thụ chất dinh dưỡng', 'Chứa thức ăn', 'Đẩy phân ra ngoài'], 1, 'Ruột non là nơi hấp thụ chất dinh dưỡng chủ yếu.', $d);
        $this->quiz($s, 'Gan tiết ra chất gì giúp tiêu hóa mỡ?', ['Dịch vị', 'Mật', 'Nước bọt', 'Dịch tụy'], 1, 'Gan tiết ra mật giúp tiêu hóa mỡ.', $d);
        $this->quiz($s, 'Phần thức ăn không tiêu hóa được sẽ đi đâu?', ['Được hấp thụ hết', 'Xuống ruột già rồi thải ra ngoài', 'Quay lại dạ dày', 'Vào máu'], 1, 'Phần không tiêu hóa được xuống ruột già rồi thải ra ngoài.', $d);
        $this->matching($s, 'Nối mỗi cơ quan với vai trò của nó trong tiêu hoá.', [['Miệng', 'Nhai nhỏ thức ăn'], ['Dạ dày', 'Nhào trộn thức ăn'], ['Ruột non', 'Hấp thụ chất dinh dưỡng'], ['Ruột già', 'Hấp thụ nước, tạo phân']], 'Miệng nhai, dạ dày nhào trộn, ruột non hấp thụ, ruột già tạo phân.', $d);
        $this->matching($s, 'Nối mỗi loại răng với số lượng của nó (người trưởng thành).', [['Răng cửa', '8 chiếc'], ['Răng nanh', '4 chiếc'], ['Răng tiền hàm', '8 chiếc'], ['Răng hàm', '12 chiếc']], 'Người trưởng thành có 32 răng: 8 cửa, 4 nanh, 8 tiền hàm, 12 hàm.', $d);
        $this->matching($s, 'Nối mỗi thói quen với kết quả của nó.', [['Rửa tay trước khi ăn', 'Không đau bụng'], ['Ăn chín uống sôi', 'Phòng bệnh đường ruột'], ['Nhai kĩ', 'Dễ tiêu hóa'], ['Ăn vội vàng', 'Dễ bị nghẹn']], 'Rửa tay, ăn chín, nhai kĩ giúp tiêu hóa tốt; ăn vội dễ nghẹn.', $d);
        $this->matching($s, 'Nối mỗi vitamin với tác dụng của nó.', [['Vitamin A', 'Giúp sáng mắt'], ['Vitamin C', 'Tăng sức đề kháng'], ['Vitamin D', 'Giúp chắc xương'], ['Vitamin B', 'Giúp ngon miệng']], 'Vitamin A sáng mắt, C tăng đề kháng, D chắc xương.', $d);
        $this->matching($s, 'Nối mỗi bệnh đường ruột với nguyên nhân của nó.', [['Tiêu chảy', 'Ăn uống mất vệ sinh'], ['Táo bón', 'Ăn ít rau'], ['Đau dạ dày', 'Ăn không đúng giờ'], ['Nhiễm giun sán', 'Ăn rau sống bẩn']], 'Ăn bẩn gây tiêu chảy, ít rau gây táo bón, ăn rau sống bẩn nhiễm giun.', $d);
        $this->sortQ($s, 'Kéo mỗi cơ quan vào nhóm TRƯỚC DẠ DÀY hoặc SAU DẠ DÀY (theo đường đi của thức ăn).', [['Miệng', 'TRƯỚC DẠ DÀY'], ['Thực quản', 'TRƯỚC DẠ DÀY'], ['Ruột non', 'SAU DẠ DÀY'], ['Ruột già', 'SAU DẠ DÀY']], 'Miệng và thực quản trước dạ dày; ruột non và ruột già sau dạ dày.', $d);
        $this->sortQ($s, 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.', [['Tiêu hóa bắt đầu từ miệng', 'ĐÚNG'], ['Ruột non hấp thụ chất dinh dưỡng', 'ĐÚNG'], ['Gan tiết ra mật', 'ĐÚNG'], ['Dạ dày hấp thụ thức ăn', 'SAI']], 'Dạ dày nhào trộn chứ không hấp thụ; ruột non mới hấp thụ.', $d);
        $this->sortQ($s, 'Kéo mỗi cơ quan vào nhóm CƠ QUAN TIÊU HOÁ hoặc KHÔNG PHẢI.', [['Gan', 'CƠ QUAN TIÊU HOÁ'], ['Tụy', 'CƠ QUAN TIÊU HOÁ'], ['Tim', 'KHÔNG PHẢI'], ['Phổi', 'KHÔNG PHẢI']], 'Gan và tụy là tuyến tiêu hóa; tim và phổi thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm TỐT CHO TIÊU HOÁ hoặc HẠI CHO TIÊU HOÁ.', [['Ăn sữa chua', 'TỐT CHO TIÊU HOÁ'], ['Vận động nhẹ', 'TỐT CHO TIÊU HOÁ'], ['Uống nước ngọt có gas', 'HẠI CHO TIÊU HOÁ'], ['Bỏ bữa sáng', 'HẠI CHO TIÊU HOÁ']], 'Sữa chua và vận động tốt cho tiêu hóa; nước ngọt có gas và bỏ bữa có hại.', $d);
        $this->sortQ($s, 'Kéo mỗi món ăn vào nhóm GIÀU ĐẠM hoặc GIÀU BỘT ĐƯỜNG.', [['Thịt', 'GIÀU ĐẠM'], ['Trứng', 'GIÀU ĐẠM'], ['Cơm', 'GIÀU BỘT ĐƯỜNG'], ['Khoai', 'GIÀU BỘT ĐƯỜNG']], 'Thịt, trứng giàu đạm; cơm, khoai giàu bột đường.', $d);
        $this->fill($s, 'Quá trình tiêu hóa thức ăn bắt đầu từ ___.', [[0, 'miệng']], 'Tiêu hóa bắt đầu từ miệng.', $d);
        $this->fill($s, 'Dịch giúp tiêu hóa mỡ do gan tiết ra gọi là ___.', [[0, 'mật']], 'Mật do gan tiết ra giúp tiêu hóa mỡ.', $d);
        $this->fill($s, 'Ruột non có các lông ruột giúp hấp thụ chất dinh ___.', [[0, 'dưỡng']], 'Lông ruột giúp hấp thụ chất dinh dưỡng.', $d);
        $this->fill($s, 'Thức ăn thừa được thải ra ngoài qua hậu ___.', [[0, 'môn']], 'Phân thải ra ngoài qua hậu môn.', $d);
        $this->fill($s, 'Tuyến tụy tiết dịch tụy đổ vào tá ___.', [[0, 'tràng']], 'Dịch tụy đổ vào tá tràng (đầu ruột non).', $d);
    }

    private function seedKhTrangThaiChat(): void
    {
        $s = 'kh-trang-thai-chat'; $d = 'de';
        $this->quiz($s, 'Chất rắn có đặc điểm nào?', ['Thay đổi theo vật chứa', 'Giữ nguyên hình dạng', 'Lan tỏa khắp nơi', 'Không có khối lượng'], 1, 'Chất rắn giữ nguyên hình dạng của mình.', $d);
        $this->quiz($s, 'Chất lỏng có đặc điểm nào?', ['Giữ nguyên hình dạng', 'Thay đổi hình dạng theo vật chứa', 'Bay khắp nơi', 'Cứng và giòn'], 1, 'Chất lỏng thay đổi hình dạng theo vật chứa nó.', $d);
        $this->quiz($s, 'Hiện tượng nước từ thể lỏng chuyển thành thể rắn gọi là gì?', ['Nóng chảy', 'Đông đặc', 'Bay hơi', 'Ngưng tụ'], 1, 'Nước lỏng thành đá gọi là sự đông đặc.', $d);
        $this->quiz($s, 'Sương mù hình thành do hiện tượng nào?', ['Bay hơi', 'Ngưng tụ', 'Đông đặc', 'Nóng chảy'], 1, 'Sương mù do hơi nước ngưng tụ thành giọt nhỏ.', $d);
        $this->quiz($s, 'Băng khô khi gặp nóng sẽ thế nào?', ['Tan thành nước', 'Bay hơi trực tiếp thành khí', 'Cháy', 'Nổ'], 1, 'Băng khô thăng hoa: từ rắn bay hơi trực tiếp thành khí.', $d);
        $this->matching($s, 'Nối mỗi vật với trạng thái của nó ở nhiệt độ thường.', [['Sắt', 'Rắn'], ['Dầu ăn', 'Lỏng'], ['Không khí', 'Khí'], ['Thủy ngân', 'Lỏng']], 'Sắt rắn, dầu ăn lỏng, không khí là khí, thủy ngân lỏng.', $d);
        $this->matching($s, 'Nối mỗi quá trình với sự chuyển thể tương ứng.', [['Nóng chảy', 'Rắn thành lỏng'], ['Đông đặc', 'Lỏng thành rắn'], ['Bay hơi', 'Lỏng thành khí'], ['Ngưng tụ', 'Khí thành lỏng']], 'Nóng chảy: rắn→lỏng; đông đặc: lỏng→rắn; bay hơi: lỏng→khí; ngưng tụ: khí→lỏng.', $d);
        $this->matching($s, 'Nối mỗi trạng thái với đặc điểm của nó.', [['Rắn', 'Giữ nguyên hình dạng'], ['Lỏng', 'Thay đổi theo vật chứa'], ['Khí', 'Lan tỏa khắp nơi']], 'Rắn giữ hình dạng, lỏng theo vật chứa, khí lan tỏa.', $d);
        $this->matching($s, 'Nối mỗi hiện tượng trong đời sống với sự chuyển thể tương ứng.', [['Sương đọng trên lá', 'Ngưng tụ'], ['Nước sôi bốc hơi', 'Bay hơi'], ['Tuyết tan', 'Nóng chảy'], ['Băng khô bay hơi', 'Thăng hoa']], 'Sương đọng là ngưng tụ, nước sôi là bay hơi, tuyết tan là nóng chảy.', $d);
        $this->matching($s, 'Nối mỗi vật với trạng thái của nó.', [['Đường', 'Rắn'], ['Mật ong', 'Lỏng'], ['Khí ô-xy', 'Khí'], ['Cồn', 'Lỏng']], 'Đường rắn, mật ong lỏng, ô-xy là khí, cồn lỏng.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm TRẠNG THÁI RẮN hoặc TRẠNG THÁI LỎNG.', [['Viên đá', 'RẮN'], ['Thanh sắt', 'RẮN'], ['Nước', 'LỎNG'], ['Dầu ăn', 'LỎNG']], 'Đá và sắt rắn; nước và dầu ăn lỏng.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm XẢY RA KHI ĐUN NÓNG hoặc KHI LÀM LẠNH.', [['Nước đá tan', 'KHI ĐUN NÓNG'], ['Nước sôi', 'KHI ĐUN NÓNG'], ['Hơi nước thành giọt', 'KHI LÀM LẠNH'], ['Nước đóng băng', 'KHI LÀM LẠNH']], 'Đun nóng: đá tan, nước sôi; làm lạnh: ngưng tụ, đóng băng.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm GIỮ NGUYÊN HÌNH DẠNG hoặc THAY ĐỔI THEO VẬT CHỨA.', [['Viên gạch', 'GIỮ NGUYÊN HÌNH DẠNG'], ['Thanh gỗ', 'GIỮ NGUYÊN HÌNH DẠNG'], ['Nước', 'THAY ĐỔI THEO VẬT CHỨA'], ['Không khí', 'THAY ĐỔI THEO VẬT CHỨA']], 'Gạch, gỗ giữ hình dạng; nước, không khí thay đổi theo vật chứa.', $d);
        $this->sortQ($s, 'Kéo mỗi sự chuyển thể vào nhóm CẦN NHẬN NHIỆT hoặc TỎA NHIỆT.', [['Nóng chảy', 'CẦN NHẬN NHIỆT'], ['Bay hơi', 'CẦN NHẬN NHIỆT'], ['Đông đặc', 'TỎA NHIỆT'], ['Ngưng tụ', 'TỎA NHIỆT']], 'Nóng chảy, bay hơi cần nhận nhiệt; đông đặc, ngưng tụ tỏa nhiệt.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm THỂ RẮN, THỂ LỎNG hoặc THỂ KHÍ.', [['Muối', 'THỂ RẮN'], ['Đường', 'THỂ RẮN'], ['Sữa', 'THỂ LỎNG'], ['Nước mắm', 'THỂ LỎNG'], ['Hơi thở', 'THỂ KHÍ'], ['Bóng bay', 'THỂ KHÍ']], 'Muối, đường rắn; sữa, nước mắm lỏng; hơi thở, bóng bay chứa khí.', $d);
        $this->fill($s, 'Chất rắn giữ nguyên hình ___ của mình.', [[0, 'dạng']], 'Chất rắn giữ nguyên hình dạng.', $d);
        $this->fill($s, 'Chất lỏng thay đổi hình dạng theo vật ___.', [[0, 'chứa']], 'Chất lỏng theo hình dạng vật chứa.', $d);
        $this->fill($s, 'Nước từ thể lỏng chuyển thành hơi gọi là sự bay ___.', [[0, 'hơi']], 'Lỏng thành hơi gọi là bay hơi.', $d);
        $this->fill($s, 'Khi trời lạnh, hơi nước thành giọt gọi là sự ngưng ___.', [[0, 'tụ']], 'Khí thành lỏng gọi là ngưng tụ.', $d);
        $this->fill($s, 'Băng khô nóng lên bay hơi trực tiếp gọi là sự thăng ___.', [[0, 'hoa']], 'Rắn thành khí trực tiếp gọi là thăng hoa.', $d);
    }

    private function seedKhNuoc(): void
    {
        $s = 'kh-nuoc'; $d = 'de';
        $this->quiz($s, 'Nước sôi ở nhiệt độ bao nhiêu?', ['0°C', '50°C', '100°C', '200°C'], 2, 'Nước sôi ở 100°C trong điều kiện thường.', $d);
        $this->quiz($s, 'Nước đá tan thành nước lỏng ở nhiệt độ bao nhiêu?', ['0°C', '10°C', '50°C', '100°C'], 0, 'Nước đá tan ở 0°C.', $d);
        $this->quiz($s, 'Nước chiếm khoảng bao nhiêu phần bề mặt Trái Đất?', ['Một nửa', 'Ba phần tư', 'Một phần tư', 'Toàn bộ'], 1, 'Nước chiếm khoảng 3/4 bề mặt Trái Đất.', $d);
        $this->quiz($s, 'Nước tinh khiết có màu, mùi, vị như thế nào?', ['Xanh, thơm, ngọt', 'Không màu, không mùi, không vị', 'Trắng, hắc, đắng', 'Vàng, tanh, mặn'], 1, 'Nước tinh khiết không màu, không mùi, không vị.', $d);
        $this->quiz($s, 'Vì sao phải đun sôi nước trước khi uống?', ['Cho ngon', 'Để diệt vi khuẩn', 'Cho ấm', 'Cho thơm'], 1, 'Đun sôi để diệt vi khuẩn gây bệnh trong nước.', $d);
        $this->matching($s, 'Nối mỗi dạng nước với nơi có nó.', [['Nước mưa', 'Rơi từ mây'], ['Nước ngầm', 'Trong lòng đất'], ['Nước biển', 'Vị mặn'], ['Nước sông', 'Vị ngọt']], 'Nước mưa từ mây, nước ngầm trong đất, nước biển mặn, nước sông ngọt.', $d);
        $this->matching($s, 'Nối mỗi nguồn nước với cách dùng phù hợp.', [['Nước máy', 'Ăn uống'], ['Nước mưa', 'Tưới cây'], ['Nước giếng', 'Sinh hoạt'], ['Nước đóng chai', 'Uống trực tiếp']], 'Nước máy ăn uống, nước mưa tưới cây, nước đóng chai uống trực tiếp.', $d);
        $this->matching($s, 'Nối mỗi việc làm với ý nghĩa của nó.', [['Đậy nắp khi đun', 'Nhanh sôi, tiết kiệm'], ['Tái sử dụng nước', 'Tiết kiệm nước'], ['Xả rác xuống sông', 'Gây ô nhiễm'], ['Trồng cây ven sông', 'Chống xói mòn']], 'Đậy nắp nhanh sôi, tái sử dụng tiết kiệm, xả rác gây ô nhiễm.', $d);
        $this->matching($s, 'Nối mỗi đối tượng với nhu cầu nước của nó.', [['Con người', 'Uống, sinh hoạt'], ['Cây lúa', 'Tưới tiêu'], ['Cá', 'Môi trường sống'], ['Vệ sinh', 'Rửa ráy']], 'Người cần nước uống, lúa cần tưới, cá cần nước để sống.', $d);
        $this->matching($s, 'Nối mỗi tính chất với mô tả của nước.', [['Không màu', 'Trong suốt'], ['Không mùi', 'Không có mùi lạ'], ['Không vị', 'Vị nhạt'], ['Hòa tan', 'Tan nhiều chất']], 'Nước trong suốt, không mùi, vị nhạt, hòa tan nhiều chất.', $d);
        $this->sortQ($s, 'Kéo mỗi mô tả vào nhóm TÍNH CHẤT CỦA NƯỚC hoặc KHÔNG PHẢI.', [['Không màu', 'TÍNH CHẤT CỦA NƯỚC'], ['Sôi ở 100°C', 'TÍNH CHẤT CỦA NƯỚC'], ['Có màu đỏ', 'KHÔNG PHẢI'], ['Cháy được', 'KHÔNG PHẢI']], 'Nước không màu, sôi ở 100°C; nước không có màu đỏ và không cháy.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm TIẾT KIỆM NƯỚC hoặc LÃNG PHÍ NƯỚC.', [['Dùng vòi tiết kiệm', 'TIẾT KIỆM NƯỚC'], ['Hứng nước mưa tưới cây', 'TIẾT KIỆM NƯỚC'], ['Mở vòi khi đánh răng', 'LÃNG PHÍ NƯỚC'], ['Rửa xe bằng vòi xối', 'LÃNG PHÍ NƯỚC']], 'Vòi tiết kiệm và hứng nước mưa là tiết kiệm; mở vòi khi đánh răng là lãng phí.', $d);
        $this->sortQ($s, 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.', [['Nước chiếm 3/4 bề mặt Trái Đất', 'ĐÚNG'], ['Nước sôi ở 100°C', 'ĐÚNG'], ['Nước biển uống được trực tiếp', 'SAI'], ['Nước tinh khiết có màu xanh', 'SAI']], 'Nước biển mặn không uống trực tiếp được; nước tinh khiết không màu.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn nước vào nhóm NƯỚC SẠCH hoặc NƯỚC Ô NHIỄM.', [['Nước máy', 'NƯỚC SẠCH'], ['Nước đóng chai', 'NƯỚC SẠCH'], ['Nước ao tù', 'NƯỚC Ô NHIỄM'], ['Nước thải', 'NƯỚC Ô NHIỄM']], 'Nước máy và đóng chai sạch; nước ao tù và nước thải ô nhiễm.', $d);
        $this->sortQ($s, 'Kéo mỗi loại nước vào nhóm UỐNG ĐƯỢC hoặc KHÔNG NÊN UỐNG TRỰC TIẾP.', [['Nước đun sôi', 'UỐNG ĐƯỢC'], ['Nước đóng chai', 'UỐNG ĐƯỢC'], ['Nước ao', 'KHÔNG NÊN UỐNG'], ['Nước mưa bẩn', 'KHÔNG NÊN UỐNG']], 'Nước đun sôi và đóng chai uống được; nước ao, nước mưa bẩn thì không.', $d);
        $this->fill($s, 'Nước chiếm khoảng 3/4 bề mặt ___ Đất.', [[0, 'Trái']], 'Nước chiếm 3/4 bề mặt Trái Đất.', $d);
        $this->fill($s, 'Nước đá tan thành nước ở 0 độ ___.', [[0, 'C']], 'Nước đá tan ở 0°C.', $d);
        $this->fill($s, 'Nước tinh khiết không màu, không mùi, không ___.', [[0, 'vị']], 'Nước tinh khiết không màu, không mùi, không vị.', $d);
        $this->fill($s, 'Đun sôi nước để diệt vi ___.', [[0, 'khuẩn']], 'Đun sôi diệt vi khuẩn trong nước.', $d);
        $this->fill($s, 'Nước biển có vị ___ nên không uống trực tiếp được.', [[0, 'mặn']], 'Nước biển mặn, không uống trực tiếp được.', $d);
    }

    private function seedKhNguonNangLuong(): void
    {
        $s = 'kh-nguon-nang-luong'; $d = 'trung_binh';
        $this->quiz($s, 'Năng lượng gió thường được dùng để làm gì?', ['Đun nấu', 'Phát điện', 'Sưởi ấm', 'Chạy xe máy'], 1, 'Tuabin gió biến năng lượng gió thành điện năng.', $d);
        $this->quiz($s, 'Nguồn năng lượng nào sau đây là năng lượng hóa thạch?', ['Mặt trời', 'Than đá', 'Gió', 'Nước'], 1, 'Than đá là năng lượng hóa thạch, hình thành hàng triệu năm.', $d);
        $this->quiz($s, 'Nhà máy thủy điện sử dụng năng lượng của gì?', ['Gió', 'Nước chảy', 'Mặt trời', 'Than đá'], 1, 'Thủy điện dùng năng lượng của dòng nước chảy.', $d);
        $this->quiz($s, 'Năng lượng hạt nhân có ưu điểm gì?', ['Rẻ tiền', 'Tạo năng lượng rất lớn, ít khí thải', 'Dễ làm', 'Không nguy hiểm'], 1, 'Năng lượng hạt nhân tạo ra năng lượng rất lớn và ít khí thải.', $d);
        $this->quiz($s, 'Vì sao chúng ta cần tiết kiệm năng lượng?', ['Vì thích', 'Vì tài nguyên có hạn và bảo vệ môi trường', 'Vì lười', 'Vì sợ tối'], 1, 'Tài nguyên năng lượng có hạn nên cần tiết kiệm và bảo vệ môi trường.', $d);
        $this->matching($s, 'Nối mỗi nguồn năng lượng với loại của nó.', [['Mặt trời', 'Tái tạo'], ['Than đá', 'Hóa thạch'], ['Gió', 'Tái tạo'], ['Khí đốt', 'Hóa thạch']], 'Mặt trời, gió tái tạo được; than đá, khí đốt là hóa thạch.', $d);
        $this->matching($s, 'Nối mỗi nguồn năng lượng với ứng dụng của nó.', [['Mặt trời', 'Pin mặt trời'], ['Gió', 'Tuabin gió'], ['Nước', 'Thủy điện'], ['Củi', 'Đun nấu']], 'Mặt trời – pin mặt trời, gió – tuabin, nước – thủy điện, củi – đun nấu.', $d);
        $this->matching($s, 'Nối mỗi việc làm với lợi ích của nó.', [['Tắt đèn khi ra ngoài', 'Tiết kiệm điện'], ['Dùng bình nước nóng mặt trời', 'Tiết kiệm điện'], ['Đi bộ quãng gần', 'Giảm xăng dầu'], ['Tái chế giấy', 'Tiết kiệm tài nguyên']], 'Tắt đèn, bình mặt trời, đi bộ, tái chế đều tiết kiệm năng lượng.', $d);
        $this->matching($s, 'Nối mỗi thiết bị với năng lượng nó sử dụng.', [['Bếp gas', 'Khí đốt'], ['Bếp củi', 'Sinh khối'], ['Đèn pin mặt trời', 'Mặt trời'], ['Quạt điện', 'Điện năng']], 'Bếp gas dùng khí đốt, bếp củi dùng sinh khối, đèn pin mặt trời dùng mặt trời.', $d);
        $this->matching($s, 'Nối mỗi nguồn năng lượng với đặc điểm của nó.', [['Mặt trời', 'Vô tận'], ['Than đá', 'Gây ô nhiễm'], ['Gió', 'Sạch'], ['Hạt nhân', 'Năng lượng rất lớn']], 'Mặt trời vô tận, than đá gây ô nhiễm, gió sạch, hạt nhân năng lượng lớn.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn vào nhóm NĂNG LƯỢNG TÁI TẠO hoặc NĂNG LƯỢNG HOÁ THẠCH.', [['Mặt trời', 'TÁI TẠO'], ['Sinh khối', 'TÁI TẠO'], ['Than đá', 'HOÁ THẠCH'], ['Dầu mỏ', 'HOÁ THẠCH']], 'Mặt trời, sinh khối tái tạo; than đá, dầu mỏ là hóa thạch.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn vào nhóm SẠCH hoặc GÂY Ô NHIỄM.', [['Gió', 'SẠCH'], ['Mặt trời', 'SẠCH'], ['Than đá', 'GÂY Ô NHIỄM'], ['Xăng dầu', 'GÂY Ô NHIỄM']], 'Gió và mặt trời sạch; than đá và xăng dầu gây ô nhiễm.', $d);
        $this->sortQ($s, 'Kéo mỗi thiết bị vào nhóm DÙNG ĐIỆN hoặc KHÔNG DÙNG ĐIỆN.', [['Tivi', 'DÙNG ĐIỆN'], ['Tủ lạnh', 'DÙNG ĐIỆN'], ['Bếp củi', 'KHÔNG DÙNG ĐIỆN'], ['Xe đạp', 'KHÔNG DÙNG ĐIỆN']], 'Tivi, tủ lạnh dùng điện; bếp củi, xe đạp không dùng điện.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm TIẾT KIỆM hoặc LÃNG PHÍ NĂNG LƯỢNG.', [['Tắt điện khi ra ngoài', 'TIẾT KIỆM'], ['Đi xe đạp', 'TIẾT KIỆM'], ['Bật điều hòa cả ngày', 'LÃNG PHÍ'], ['Để đèn sáng không cần', 'LÃNG PHÍ']], 'Tắt điện, đi xe đạp tiết kiệm; bật điều hòa cả ngày, để đèn sáng là lãng phí.', $d);
        $this->sortQ($s, 'Kéo mỗi ứng dụng vào nhóm DÙNG NĂNG LƯỢNG MẶT TRỜI hoặc KHÁC.', [['Pin mặt trời', 'NĂNG LƯỢNG MẶT TRỜI'], ['Bình nước nóng mặt trời', 'NĂNG LƯỢNG MẶT TRỜI'], ['Thủy điện', 'KHÁC'], ['Nhiệt điện than', 'KHÁC']], 'Pin và bình nước nóng dùng năng lượng mặt trời; thủy điện, nhiệt điện thì khác.', $d);
        $this->fill($s, 'Tuabin gió biến năng lượng gió thành điện ___.', [[0, 'năng']], 'Tuabin gió tạo ra điện năng.', $d);
        $this->fill($s, 'Thủy điện sử dụng năng lượng của nước ___.', [[0, 'chảy']], 'Thủy điện dùng năng lượng nước chảy.', $d);
        $this->fill($s, 'Than đá, dầu mỏ là năng lượng hóa ___.', [[0, 'thạch']], 'Than đá, dầu mỏ là năng lượng hóa thạch.', $d);
        $this->fill($s, 'Năng lượng hạt nhân tạo ra lượng điện rất ___.', [[0, 'lớn']], 'Năng lượng hạt nhân rất lớn.', $d);
        $this->fill($s, 'Tiết kiệm năng lượng giúp bảo vệ môi ___.', [[0, 'trường']], 'Tiết kiệm năng lượng bảo vệ môi trường.', $d);
    }

    private function seedKhDien(): void
    {
        $s = 'kh-dien'; $d = 'trung_binh';
        $this->quiz($s, 'Dòng điện là gì?', ['Dòng nước chảy', 'Dòng chuyển dời có hướng của các điện tích', 'Luồng gió', 'Ánh sáng'], 1, 'Dòng điện là dòng chuyển dời có hướng của các điện tích.', $d);
        $this->quiz($s, 'Nguồn điện có vai trò gì trong mạch điện?', ['Phát sáng', 'Cung cấp năng lượng', 'Dẫn điện', 'Ngắt mạch'], 1, 'Nguồn điện (pin, ắc quy) cung cấp năng lượng cho mạch.', $d);
        $this->quiz($s, 'Đơn vị đo cường độ dòng điện là gì?', ['Vôn', 'Ampe', 'Oát', 'Ôm'], 1, 'Ampe là đơn vị đo cường độ dòng điện.', $d);
        $this->quiz($s, 'Kim loại nào dẫn điện tốt nhất?', ['Sắt', 'Đồng', 'Bạc', 'Nhôm'], 2, 'Bạc dẫn điện tốt nhất trong các kim loại.', $d);
        $this->quiz($s, 'Vì sao chim đậu trên dây điện không bị điện giật?', ['Vì chim không dẫn điện', 'Vì không tạo thành mạch kín qua người chim', 'Vì điện yếu', 'Vì chim bay nhanh'], 1, 'Chim chỉ đậu một dây nên không tạo mạch kín, không bị giật.', $d);
        $this->matching($s, 'Nối mỗi vật liệu với tính dẫn điện của nó.', [['Đồng', 'Dẫn điện tốt'], ['Nhựa', 'Cách điện'], ['Sắt', 'Dẫn điện'], ['Gỗ khô', 'Cách điện']], 'Đồng, sắt dẫn điện; nhựa, gỗ khô cách điện.', $d);
        $this->matching($s, 'Nối mỗi bộ phận với chức năng của nó trong mạch điện.', [['Pin', 'Nguồn điện'], ['Dây dẫn', 'Dẫn điện'], ['Bóng đèn', 'Phát sáng'], ['Công tắc', 'Đóng ngắt mạch']], 'Pin là nguồn, dây dẫn điện, bóng đèn phát sáng, công tắc đóng ngắt.', $d);
        $this->matching($s, 'Nối mỗi dụng cụ với đại lượng nó đo.', [['Ampe kế', 'Cường độ dòng điện'], ['Vôn kế', 'Hiệu điện thế'], ['Oát kế', 'Công suất']], 'Ampe kế đo cường độ, vôn kế đo hiệu điện thế, oát kế đo công suất.', $d);
        $this->matching($s, 'Nối mỗi hiện tượng với nguyên nhân của nó.', [['Bóng đèn sáng', 'Có dòng điện chạy qua'], ['Cầu chì đứt', 'Dòng điện quá tải'], ['Bị điện giật', 'Chạm vào vật có điện'], ['Chập điện', 'Hai dây chạm nhau']], 'Có dòng điện bóng đèn sáng; quá tải cầu chì đứt.', $d);
        $this->matching($s, 'Nối mỗi cách dùng điện với mức độ an toàn của nó.', [['Tay khô chạm công tắc', 'An toàn hơn'], ['Đi dép trong nhà', 'Cách điện tốt'], ['Tay ướt chạm ổ điện', 'Nguy hiểm'], ['Sửa điện khi đang có điện', 'Nguy hiểm']], 'Tay ướt chạm ổ điện và sửa điện khi có điện rất nguy hiểm.', $d);
        $this->sortQ($s, 'Kéo mỗi vật liệu vào nhóm VẬT DẪN ĐIỆN hoặc VẬT CÁCH ĐIỆN.', [['Nhôm', 'VẬT DẪN ĐIỆN'], ['Nước muối', 'VẬT DẪN ĐIỆN'], ['Thủy tinh', 'VẬT CÁCH ĐIỆN'], ['Cao su', 'VẬT CÁCH ĐIỆN']], 'Nhôm và nước muối dẫn điện; thủy tinh và cao su cách điện.', $d);
        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm THUỘC MẠCH ĐIỆN hoặc KHÔNG THUỘC.', [['Ắc quy', 'THUỘC MẠCH ĐIỆN'], ['Cầu chì', 'THUỘC MẠCH ĐIỆN'], ['Cái bàn', 'KHÔNG THUỘC'], ['Cái ghế', 'KHÔNG THUỘC']], 'Ắc quy và cầu chì thuộc mạch điện; bàn ghế thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.', [['Đồng dẫn điện tốt', 'ĐÚNG'], ['Công tắc để đóng ngắt mạch', 'ĐÚNG'], ['Nhựa dẫn điện', 'SAI'], ['Nước cất dẫn điện tốt', 'SAI']], 'Nhựa cách điện; nước cất hầu như không dẫn điện.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm AN TOÀN ĐIỆN hoặc NGUY HIỂM.', [['Rút phích khi không dùng', 'AN TOÀN ĐIỆN'], ['Dùng ổ cắm an toàn', 'AN TOÀN ĐIỆN'], ['Thả diều gần đường điện', 'NGUY HIỂM'], ['Sờ tay ướt vào ổ điện', 'NGUY HIỂM']], 'Rút phích, ổ cắm an toàn là an toàn; thả diều gần điện, tay ướt sờ ổ điện nguy hiểm.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm CÓ DÒNG ĐIỆN hoặc KHÔNG CÓ.', [['Mạch kín có pin', 'CÓ DÒNG ĐIỆN'], ['Công tắc đang đóng', 'CÓ DÒNG ĐIỆN'], ['Mạch hở', 'KHÔNG CÓ'], ['Pin đã hết', 'KHÔNG CÓ']], 'Mạch kín có pin thì có dòng điện; mạch hở hoặc hết pin thì không.', $d);
        $this->fill($s, 'Đơn vị đo cường độ dòng điện là ___.', [[0, 'ampe']], 'Ampe là đơn vị đo cường độ dòng điện.', $d);
        $this->fill($s, 'Kim loại dẫn điện tốt nhất là ___.', [[0, 'bạc']], 'Bạc dẫn điện tốt nhất.', $d);
        $this->fill($s, 'Nguồn điện có vai trò cung cấp năng ___ cho mạch.', [[0, 'lượng']], 'Nguồn điện cung cấp năng lượng.', $d);
        $this->fill($s, 'Cầu chì có tác dụng bảo vệ mạch khi bị quá ___.', [[0, 'tải']], 'Quá tải cầu chì sẽ đứt để bảo vệ mạch.', $d);
        $this->fill($s, 'Không thả diều gần đường dây ___.', [[0, 'điện']], 'Thả diều gần đường dây điện rất nguy hiểm.', $d);
    }

    private function seedKhCoTheNguoiLop61(): void
    {
        $s = 'kh-co-the-nguoi-lop-6-1'; $d = 'de';
        $this->quiz($s, 'Xương sống (cột sống) có vai trò gì?', ['Bảo vệ não', 'Nâng đỡ cơ thể', 'Giúp nhai', 'Bơm máu'], 1, 'Cột sống nâng đỡ toàn bộ cơ thể.', $d);
        $this->quiz($s, 'Cơ có vai trò gì đối với xương?', ['Bảo vệ xương', 'Giúp xương cử động', 'Nuôi xương', 'Làm xương dài'], 1, 'Cơ bám vào xương, co duỗi giúp xương cử động.', $d);
        $this->quiz($s, 'Tủy xương có chức năng gì?', ['Tiêu hóa', 'Sản sinh tế bào máu', 'Lọc máu', 'Dự trữ mỡ'], 1, 'Tủy xương sản sinh các tế bào máu.', $d);
        $this->quiz($s, 'Vì sao trẻ em nên tắm nắng buổi sáng?', ['Cho vui', 'Để tổng hợp vitamin D giúp hấp thụ canxi', 'Để da đen', 'Để ấm'], 1, 'Tắm nắng giúp tổng hợp vitamin D, giúp hấp thụ canxi cho xương.', $d);
        $this->quiz($s, 'Ngồi sai tư thế trong thời gian dài dễ gây ra bệnh gì?', ['Cận thị', 'Cong vẹo cột sống', 'Sâu răng', 'Viêm họng'], 1, 'Ngồi sai tư thế lâu ngày dễ bị cong vẹo cột sống.', $d);
        $this->matching($s, 'Nối mỗi bộ phận với chức năng của nó.', [['Xương sọ', 'Bảo vệ não'], ['Cột sống', 'Nâng đỡ cơ thể'], ['Lồng ngực', 'Bảo vệ tim, phổi'], ['Xương chậu', 'Nâng đỡ phần trên cơ thể']], 'Xương sọ bảo vệ não, cột sống nâng đỡ, lồng ngực bảo vệ tim phổi.', $d);
        $this->matching($s, 'Nối mỗi khớp với vị trí của nó.', [['Khớp gối', 'Chân'], ['Khớp khuỷu', 'Tay'], ['Khớp vai', 'Vai'], ['Khớp cổ', 'Cổ']], 'Khớp gối ở chân, khớp khuỷu ở tay, khớp vai ở vai.', $d);
        $this->matching($s, 'Nối mỗi việc làm với kết quả của nó.', [['Tập thể dục', 'Cơ thể khỏe mạnh'], ['Uống sữa', 'Xương chắc khỏe'], ['Ngồi thẳng lưng', 'Dáng đẹp'], ['Thức khuya', 'Cơ thể mệt mỏi']], 'Tập thể dục khỏe mạnh, uống sữa chắc xương, thức khuya mệt mỏi.', $d);
        $this->matching($s, 'Nối mỗi xương với loại xương của nó.', [['Xương đùi', 'Xương dài'], ['Xương sọ', 'Xương dẹt'], ['Đốt sống', 'Xương ngắn'], ['Xương sườn', 'Xương dẹt']], 'Xương đùi dài, xương sọ dẹt, đốt sống ngắn.', $d);
        $this->matching($s, 'Nối mỗi chất dinh dưỡng với nguồn thực phẩm của nó.', [['Canxi', 'Sữa'], ['Vitamin D', 'Ánh nắng'], ['Chất đạm', 'Thịt'], ['Chất sắt', 'Rau xanh']], 'Canxi trong sữa, vitamin D từ ánh nắng, đạm trong thịt.', $d);
        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm THUỘC HỆ XƯƠNG hoặc KHÔNG THUỘC HỆ XƯƠNG.', [['Cột sống', 'THUỘC HỆ XƯƠNG'], ['Sụn', 'THUỘC HỆ XƯƠNG'], ['Dạ dày', 'KHÔNG THUỘC'], ['Gan', 'KHÔNG THUỘC']], 'Cột sống và sụn thuộc hệ xương; dạ dày và gan thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi thói quen vào nhóm TỐT CHO XƯƠNG hoặc HẠI CHO XƯƠNG.', [['Bơi lội', 'TỐT CHO XƯƠNG'], ['Ăn tôm', 'TỐT CHO XƯƠNG'], ['Hút thuốc lá', 'HẠI CHO XƯƠNG'], ['Uống rượu bia', 'HẠI CHO XƯƠNG']], 'Bơi lội và ăn tôm tốt cho xương; thuốc lá, rượu bia có hại.', $d);
        $this->sortQ($s, 'Kéo mỗi xương vào nhóm BẢO VỆ CƠ QUAN hoặc GIÚP VẬN ĐỘNG.', [['Xương sườn', 'BẢO VỆ CƠ QUAN'], ['Đốt sống', 'BẢO VỆ CƠ QUAN'], ['Xương đùi', 'GIÚP VẬN ĐỘNG'], ['Xương cánh tay', 'GIÚP VẬN ĐỘNG']], 'Xương sườn, đốt sống bảo vệ cơ quan; xương đùi, cánh tay giúp vận động.', $d);
        $this->sortQ($s, 'Kéo mỗi thực phẩm vào nhóm GIÀU CANXI hoặc ÍT CANXI.', [['Cua', 'GIÀU CANXI'], ['Sữa chua', 'GIÀU CANXI'], ['Bánh kẹo', 'ÍT CANXI'], ['Mì tôm', 'ÍT CANXI']], 'Cua và sữa chua giàu canxi; bánh kẹo và mì tôm ít canxi.', $d);
        $this->sortQ($s, 'Kéo mỗi xương vào nhóm Ở ĐẦU, Ở THÂN hoặc Ở CHI.', [['Xương sọ', 'Ở ĐẦU'], ['Xương sống', 'Ở THÂN'], ['Xương sườn', 'Ở THÂN'], ['Xương tay', 'Ở CHI'], ['Xương chân', 'Ở CHI']], 'Xương sọ ở đầu; xương sống, sườn ở thân; xương tay, chân ở chi.', $d);
        $this->fill($s, 'Cột sống có vai trò nâng đỡ cơ ___.', [[0, 'thể']], 'Cột sống nâng đỡ cơ thể.', $d);
        $this->fill($s, 'Cơ bám vào xương giúp xương cử ___.', [[0, 'động']], 'Cơ giúp xương cử động.', $d);
        $this->fill($s, 'Tủy xương có chức năng sản sinh tế bào ___.', [[0, 'máu']], 'Tủy xương sản sinh tế bào máu.', $d);
        $this->fill($s, 'Tắm nắng giúp cơ thể tổng hợp vitamin ___.', [[0, 'D']], 'Vitamin D giúp hấp thụ canxi.', $d);
        $this->fill($s, 'Ngồi sai tư thế lâu ngày dễ bị cong vẹo cột ___.', [[0, 'sống']], 'Ngồi sai tư thế gây cong vẹo cột sống.', $d);
    }

    private function seedKhCoTheNguoiLop62(): void
    {
        $s = 'kh-co-the-nguoi-lop-6-2'; $d = 'de';
        $this->quiz($s, 'Nước bọt có tác dụng gì?', ['Làm mềm thức ăn, tiêu hóa tinh bột', 'Diệt hết vi khuẩn', 'Làm thức ăn ngon', 'Hấp thụ chất dinh dưỡng'], 0, 'Nước bọt làm mềm thức ăn và bắt đầu tiêu hóa tinh bột.', $d);
        $this->quiz($s, 'Ruột già có vai trò gì?', ['Hấp thụ nước, tạo phân', 'Nhai thức ăn', 'Tiêu hóa đạm', 'Bơm máu'], 0, 'Ruột già hấp thụ nước và tạo thành phân.', $d);
        $this->quiz($s, 'Thức ăn ở trong dạ dày khoảng bao lâu?', ['Vài phút', '3 – 4 giờ', '1 ngày', '1 tuần'], 1, 'Thức ăn được nhào trộn trong dạ dày khoảng 3 – 4 giờ.', $d);
        $this->quiz($s, 'Bệnh nào sau đây do ăn uống mất vệ sinh gây ra?', ['Cận thị', 'Tiêu chảy', 'Viêm họng', 'Sâu răng'], 1, 'Ăn uống mất vệ sinh dễ gây tiêu chảy.', $d);
        $this->quiz($s, 'Vì sao nên ăn chậm, nhai kĩ?', ['Cho ngon miệng', 'Giúp thức ăn nhỏ, dễ tiêu hóa', 'Cho no lâu', 'Cho lịch sự'], 1, 'Nhai kĩ làm thức ăn nhỏ ra, giúp dạ dày tiêu hóa dễ dàng.', $d);
        $this->matching($s, 'Nối mỗi cơ quan với vai trò của nó trong tiêu hoá.', [['Tuyến nước bọt', 'Tiết nước bọt'], ['Thực quản', 'Đẩy thức ăn xuống'], ['Tụy', 'Tiết dịch tụy'], ['Gan', 'Tiết mật']], 'Tuyến nước bọt, thực quản, tụy và gan đều tham gia tiêu hóa.', $d);
        $this->matching($s, 'Nối mỗi loại răng với số lượng của nó.', [['Răng cửa', '8 chiếc'], ['Răng nanh', '4 chiếc'], ['Răng tiền hàm', '8 chiếc'], ['Răng hàm', '12 chiếc']], 'Người trưởng thành có 32 chiếc răng.', $d);
        $this->matching($s, 'Nối mỗi thói quen với kết quả của nó.', [['Rửa tay trước khi ăn', 'Không đau bụng'], ['Ăn chín uống sôi', 'Phòng bệnh đường ruột'], ['Nhai kĩ', 'Dễ tiêu hóa'], ['Ăn vội vàng', 'Dễ bị nghẹn']], 'Rửa tay, ăn chín, nhai kĩ tốt cho tiêu hóa.', $d);
        $this->matching($s, 'Nối mỗi vitamin với tác dụng của nó.', [['Vitamin A', 'Giúp sáng mắt'], ['Vitamin C', 'Tăng sức đề kháng'], ['Vitamin D', 'Giúp chắc xương'], ['Vitamin B', 'Giúp ngon miệng']], 'Vitamin A sáng mắt, C tăng đề kháng, D chắc xương.', $d);
        $this->matching($s, 'Nối mỗi bệnh với nguyên nhân của nó.', [['Tiêu chảy', 'Ăn uống mất vệ sinh'], ['Táo bón', 'Ăn ít rau xanh'], ['Đau dạ dày', 'Ăn không đúng giờ'], ['Nhiễm giun sán', 'Ăn rau sống bẩn']], 'Ăn bẩn gây tiêu chảy, ít rau gây táo bón.', $d);
        $this->sortQ($s, 'Kéo mỗi cơ quan vào nhóm THUỘC HỆ TIÊU HOÁ hoặc KHÔNG THUỘC HỆ TIÊU HOÁ.', [['Thực quản', 'THUỘC HỆ TIÊU HOÁ'], ['Hậu môn', 'THUỘC HỆ TIÊU HOÁ'], ['Tim', 'KHÔNG THUỘC'], ['Thận', 'KHÔNG THUỘC']], 'Thực quản và hậu môn thuộc hệ tiêu hóa; tim và thận thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm TỐT CHO TIÊU HOÁ hoặc HẠI CHO TIÊU HOÁ.', [['Ăn sữa chua', 'TỐT CHO TIÊU HOÁ'], ['Vận động nhẹ', 'TỐT CHO TIÊU HOÁ'], ['Uống nước ngọt có gas', 'HẠI CHO TIÊU HOÁ'], ['Thức khuya', 'HẠI CHO TIÊU HOÁ']], 'Sữa chua và vận động tốt; nước ngọt có gas và thức khuya có hại.', $d);
        $this->sortQ($s, 'Sắp xếp đường đi của thức ăn: kéo mỗi cơ quan vào nhóm TRƯỚC DẠ DÀY hoặc SAU DẠ DÀY.', [['Tuyến nước bọt', 'TRƯỚC DẠ DÀY'], ['Thực quản', 'TRƯỚC DẠ DÀY'], ['Tá tràng', 'SAU DẠ DÀY'], ['Ruột thừa', 'SAU DẠ DÀY']], 'Tuyến nước bọt và thực quản trước dạ dày; tá tràng và ruột thừa sau dạ dày.', $d);
        $this->sortQ($s, 'Kéo mỗi món ăn vào nhóm GIÀU ĐẠM hoặc GIÀU BỘT ĐƯỜNG.', [['Cá', 'GIÀU ĐẠM'], ['Đậu phụ', 'GIÀU ĐẠM'], ['Bánh mì', 'GIÀU BỘT ĐƯỜNG'], ['Mía', 'GIÀU BỘT ĐƯỜNG']], 'Cá, đậu phụ giàu đạm; bánh mì, mía giàu bột đường.', $d);
        $this->sortQ($s, 'Kéo mỗi thực phẩm vào nhóm NÊN ĂN NHIỀU hoặc NÊN HẠN CHẾ.', [['Rau xanh', 'NÊN ĂN NHIỀU'], ['Trái cây', 'NÊN ĂN NHIỀU'], ['Đồ chiên rán', 'NÊN HẠN CHẾ'], ['Kẹo ngọt', 'NÊN HẠN CHẾ']], 'Nên ăn nhiều rau xanh, trái cây; hạn chế đồ chiên rán, kẹo ngọt.', $d);
        $this->fill($s, 'Nước bọt giúp làm mềm thức ăn và tiêu hóa tinh ___.', [[0, 'bột']], 'Nước bọt tiêu hóa tinh bột.', $d);
        $this->fill($s, 'Ruột già hấp thụ nước và tạo thành ___.', [[0, 'phân']], 'Ruột già tạo thành phân.', $d);
        $this->fill($s, 'Thức ăn ở trong dạ dày khoảng 3 – 4 ___.', [[0, 'giờ']], 'Thức ăn ở dạ dày 3 – 4 giờ.', $d);
        $this->fill($s, 'Ăn uống mất vệ sinh dễ bị bệnh tiêu ___.', [[0, 'chảy']], 'Ăn bẩn dễ bị tiêu chảy.', $d);
        $this->fill($s, 'Nên ăn chậm, nhai ___ để dễ tiêu hóa.', [[0, 'kĩ']], 'Nhai kĩ giúp dễ tiêu hóa.', $d);
    }

    private function seedKhCoTheNguoiLop71(): void
    {
        $s = 'kh-co-the-nguoi-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Máu gồm những thành phần nào?', ['Chỉ có hồng cầu', 'Huyết tương, hồng cầu, bạch cầu, tiểu cầu', 'Chỉ có nước', 'Chỉ có bạch cầu'], 1, 'Máu gồm huyết tương, hồng cầu, bạch cầu và tiểu cầu.', $d);
        $this->quiz($s, 'Hồng cầu có chức năng gì?', ['Diệt vi khuẩn', 'Vận chuyển khí ô-xy', 'Đông máu', 'Tiêu hóa'], 1, 'Hồng cầu vận chuyển khí ô-xy đi nuôi cơ thể.', $d);
        $this->quiz($s, 'Bạch cầu có chức năng gì?', ['Mang ô-xy', 'Bảo vệ cơ thể, diệt vi khuẩn', 'Đông máu', 'Vận chuyển chất'], 1, 'Bạch cầu bảo vệ cơ thể, tiêu diệt vi khuẩn.', $d);
        $this->quiz($s, 'Khi hít vào, lồng ngực sẽ như thế nào?', ['Xẹp xuống', 'Nâng lên, mở rộng', 'Không đổi', 'Co lại'], 1, 'Hít vào lồng ngực nâng lên, mở rộng để chứa không khí.', $d);
        $this->quiz($s, 'Vì sao không nên thở bằng miệng?', ['Mệt', 'Không khí không được làm ấm, lọc bụi', 'Khó thở', 'Đau họng'], 1, 'Thở bằng mũi giúp không khí được làm ấm và lọc bụi.', $d);
        $this->matching($s, 'Nối mỗi thành phần của máu với chức năng của nó.', [['Huyết tương', 'Vận chuyển các chất'], ['Hồng cầu', 'Mang khí ô-xy'], ['Bạch cầu', 'Diệt vi khuẩn'], ['Tiểu cầu', 'Giúp đông máu']], 'Huyết tương vận chuyển, hồng cầu mang ô-xy, bạch cầu diệt khuẩn, tiểu cầu đông máu.', $d);
        $this->matching($s, 'Nối mỗi bộ phận hô hấp với đặc điểm của nó.', [['Mũi', 'Lọc bụi, làm ấm không khí'], ['Khí quản', 'Dẫn khí vào phổi'], ['Phế quản', 'Chia nhỏ trong phổi'], ['Phổi', 'Nơi trao đổi khí']], 'Mũi lọc bụi, khí quản dẫn khí, phổi trao đổi khí.', $d);
        $this->matching($s, 'Nối mỗi việc làm với tác hại của nó với tim, phổi.', [['Hút thuốc lá', 'Hại phổi'], ['Uống rượu bia', 'Hại gan, tim'], ['Thức khuya', 'Tim mệt mỏi'], ['Ít vận động', 'Tim yếu đi']], 'Hút thuốc hại phổi, rượu bia hại gan tim, ít vận động tim yếu.', $d);
        $this->matching($s, 'Nối mỗi hiện tượng với lời giải thích của nó.', [['Tim đập nhanh khi chạy', 'Cơ thể cần nhiều ô-xy'], ['Thở gấp', 'Cơ thể thiếu ô-xy'], ['Mặt đỏ khi xúc động', 'Máu dồn lên mặt'], ['Ngáp', 'Não thiếu ô-xy']], 'Chạy cần nhiều ô-xy nên tim đập nhanh; ngáp do não thiếu ô-xy.', $d);
        $this->matching($s, 'Nối mỗi bệnh với cơ quan bị ảnh hưởng của nó.', [['Hen suyễn', 'Phổi'], ['Cao huyết áp', 'Tim mạch'], ['Viêm phế quản', 'Phế quản'], ['Thiếu máu', 'Máu']], 'Hen suyễn ở phổi, cao huyết áp ở tim mạch, thiếu máu do thiếu hồng cầu.', $d);
        $this->sortQ($s, 'Kéo mỗi cơ quan vào nhóm HỆ TUẦN HOÀN hoặc HỆ HÔ HẤP.', [['Tĩnh mạch', 'HỆ TUẦN HOÀN'], ['Mao mạch', 'HỆ TUẦN HOÀN'], ['Khí quản', 'HỆ HÔ HẤP'], ['Phế nang', 'HỆ HÔ HẤP']], 'Tĩnh mạch, mao mạch thuộc tuần hoàn; khí quản, phế nang thuộc hô hấp.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm TỐT CHO TIM PHỔI hoặc HẠI CHO TIM PHỔI.', [['Chạy bộ', 'TỐT CHO TIM PHỔI'], ['Ăn nhạt', 'TỐT CHO TIM PHỔI'], ['Hút thuốc lá', 'HẠI CHO TIM PHỔI'], ['Ăn mặn', 'HẠI CHO TIM PHỔI']], 'Chạy bộ, ăn nhạt tốt; hút thuốc, ăn mặn hại tim phổi.', $d);
        $this->sortQ($s, 'Kéo mỗi chất khí vào nhóm CƠ THỂ LẤY VÀO hoặc CƠ THỂ THẢI RA khi hô hấp.', [['Khí ô-xy', 'CƠ THỂ LẤY VÀO'], ['Khí các-bô-níc', 'CƠ THỂ THẢI RA'], ['Hơi nước', 'CƠ THỂ THẢI RA']], 'Hít vào lấy ô-xy; thở ra thải các-bô-níc và hơi nước.', $d);
        $this->sortQ($s, 'Kéo mỗi mạch máu vào nhóm DẪN MÁU ĐI KHỎI TIM hoặc DẪN MÁU VỀ TIM.', [['Động mạch chủ', 'ĐI KHỎI TIM'], ['Động mạch phổi', 'ĐI KHỎI TIM'], ['Tĩnh mạch chủ', 'VỀ TIM'], ['Tĩnh mạch phổi', 'VỀ TIM']], 'Động mạch dẫn máu đi khỏi tim; tĩnh mạch dẫn máu về tim.', $d);
        $this->sortQ($s, 'Kéo mỗi trạng thái vào nhóm TIM ĐẬP NHANH hoặc TIM ĐẬP CHẬM.', [['Khi chạy bộ', 'TIM ĐẬP NHANH'], ['Khi sợ hãi', 'TIM ĐẬP NHANH'], ['Khi ngủ', 'TIM ĐẬP CHẬM'], ['Khi nghỉ ngơi', 'TIM ĐẬP CHẬM']], 'Chạy bộ, sợ hãi tim đập nhanh; ngủ, nghỉ ngơi tim đập chậm.', $d);
        $this->fill($s, 'Hồng cầu có chức năng vận chuyển khí ___.', [[0, 'ô-xy']], 'Hồng cầu mang ô-xy đi nuôi cơ thể.', $d);
        $this->fill($s, 'Bạch cầu giúp bảo vệ cơ thể, tiêu diệt vi ___.', [[0, 'khuẩn']], 'Bạch cầu diệt vi khuẩn.', $d);
        $this->fill($s, 'Tiểu cầu giúp máu đông khi bị ___.', [[0, 'thương']], 'Tiểu cầu giúp đông máu khi bị thương.', $d);
        $this->fill($s, 'Khi hít vào, lồng ngực nâng lên và mở ___.', [[0, 'rộng']], 'Hít vào lồng ngực mở rộng.', $d);
        $this->fill($s, 'Không nên thở bằng ___ vì không khí không được lọc sạch.', [[0, 'miệng']], 'Nên thở bằng mũi để lọc bụi.', $d);
    }

    private function seedKhCoTheNguoiLop72(): void
    {
        $s = 'kh-co-the-nguoi-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Nước tiểu được tạo ra ở đâu?', ['Bàng quang', 'Thận', 'Gan', 'Dạ dày'], 1, 'Thận lọc máu để tạo thành nước tiểu.', $d);
        $this->quiz($s, 'Nước tiểu sau khi tạo ra được chứa ở đâu?', ['Thận', 'Bàng quang', 'Gan', 'Ruột'], 1, 'Nước tiểu được chứa trong bàng quang trước khi thải ra.', $d);
        $this->quiz($s, 'Phổi bài tiết chất thải nào ra ngoài?', ['Nước tiểu', 'Mồ hôi', 'Khí các-bô-níc', 'Phân'], 2, 'Phổi thải khí các-bô-níc ra ngoài khi thở.', $d);
        $this->quiz($s, 'Vì sao không nên nhịn tiểu quá lâu?', ['Mất thời gian', 'Hại thận và bàng quang', 'Khát nước', 'Đói bụng'], 1, 'Nhịn tiểu lâu gây hại cho thận và bàng quang.', $d);
        $this->quiz($s, 'Ngoài bài tiết, da còn có vai trò gì?', ['Bơm máu', 'Bảo vệ cơ thể, cảm nhận', 'Tiêu hóa', 'Hô hấp'], 1, 'Da bảo vệ cơ thể khỏi vi khuẩn và giúp cảm nhận nóng lạnh.', $d);
        $this->matching($s, 'Nối mỗi cơ quan với sản phẩm bài tiết của nó.', [['Thận', 'Nước tiểu'], ['Da', 'Mồ hôi'], ['Phổi', 'Khí các-bô-níc'], ['Ruột già', 'Phân']], 'Thận – nước tiểu, da – mồ hôi, phổi – CO2, ruột già – phân.', $d);
        $this->matching($s, 'Nối mỗi việc vệ sinh với lợi ích của nó.', [['Đánh răng 2 lần/ngày', 'Răng chắc khỏe'], ['Tắm rửa thường xuyên', 'Da sạch sẽ'], ['Cắt móng tay', 'Không chứa vi khuẩn'], ['Gội đầu', 'Tóc sạch']], 'Đánh răng, tắm rửa, cắt móng tay giúp cơ thể sạch sẽ, khỏe mạnh.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với nguyên nhân có thể của nó.', [['Nước tiểu vàng đậm', 'Uống thiếu nước'], ['Người mệt mỏi', 'Thiếu ngủ'], ['Da khô', 'Thiếu nước'], ['Hôi miệng', 'Vệ sinh răng miệng kém']], 'Nước tiểu vàng đậm và da khô do thiếu nước.', $d);
        $this->matching($s, 'Nối mỗi cơ quan với hệ cơ quan của nó.', [['Thận', 'Hệ bài tiết'], ['Tim', 'Hệ tuần hoàn'], ['Phổi', 'Hệ hô hấp'], ['Dạ dày', 'Hệ tiêu hóa']], 'Thận – bài tiết, tim – tuần hoàn, phổi – hô hấp, dạ dày – tiêu hóa.', $d);
        $this->matching($s, 'Nối mỗi bệnh với cách phòng tránh của nó.', [['Sỏi thận', 'Uống đủ nước'], ['Viêm da', 'Giữ vệ sinh da'], ['Cảm cúm', 'Rửa tay thường xuyên'], ['Sâu răng', 'Đánh răng đều đặn']], 'Uống đủ nước phòng sỏi thận, giữ vệ sinh phòng viêm da.', $d);
        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm THAM GIA BÀI TIẾT hoặc KHÔNG THAM GIA BÀI TIẾT.', [['Bàng quang', 'THAM GIA BÀI TIẾT'], ['Tuyến mồ hôi', 'THAM GIA BÀI TIẾT'], ['Tim', 'KHÔNG THAM GIA'], ['Dạ dày', 'KHÔNG THAM GIA']], 'Bàng quang và tuyến mồ hôi tham gia bài tiết; tim và dạ dày thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi thói quen vào nhóm TỐT CHO THẬN hoặc HẠI CHO THẬN.', [['Uống đủ nước', 'TỐT CHO THẬN'], ['Ăn nhạt', 'TỐT CHO THẬN'], ['Ăn mặn', 'HẠI CHO THẬN'], ['Nhịn tiểu lâu', 'HẠI CHO THẬN']], 'Uống đủ nước, ăn nhạt tốt cho thận; ăn mặn, nhịn tiểu có hại.', $d);
        $this->sortQ($s, 'Kéo mỗi chất thải vào nhóm THẢI QUA THẬN hoặc THẢI QUA ĐƯỜNG KHÁC.', [['Urê', 'THẢI QUA THẬN'], ['Nước tiểu', 'THẢI QUA THẬN'], ['Khí CO2', 'THẢI QUA PHỔI'], ['Mồ hôi', 'THẢI QUA DA']], 'Urê và nước tiểu qua thận; CO2 qua phổi; mồ hôi qua da.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm VỆ SINH CÁ NHÂN TỐT hoặc CHƯA TỐT.', [['Rửa tay bằng xà phòng', 'VỆ SINH TỐT'], ['Đánh răng mỗi ngày', 'VỆ SINH TỐT'], ['Ngoáy mũi', 'CHƯA TỐT'], ['Khạc nhổ bừa bãi', 'CHƯA TỐT']], 'Rửa tay, đánh răng là vệ sinh tốt; ngoáy mũi, khạc nhổ bừa bãi thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi loại đồ uống vào nhóm NÊN UỐNG NHIỀU hoặc NÊN HẠN CHẾ.', [['Nước lọc', 'NÊN UỐNG NHIỀU'], ['Nước canh', 'NÊN UỐNG NHIỀU'], ['Nước ngọt có gas', 'NÊN HẠN CHẾ'], ['Rượu bia', 'NÊN HẠN CHẾ']], 'Nên uống nhiều nước lọc; hạn chế nước ngọt có gas và rượu bia.', $d);
        $this->fill($s, 'Nước tiểu được tạo ra ở thận rồi chứa trong bàng ___.', [[0, 'quang']], 'Bàng quang chứa nước tiểu.', $d);
        $this->fill($s, 'Phổi bài tiết khí ___ ra ngoài khi thở.', [[0, 'các-bô-níc']], 'Phổi thải khí các-bô-níc.', $d);
        $this->fill($s, 'Không nên nhịn ___ lâu vì hại thận.', [[0, 'tiểu']], 'Nhịn tiểu lâu hại thận và bàng quang.', $d);
        $this->fill($s, 'Da còn có vai trò bảo vệ cơ thể khỏi vi ___.', [[0, 'khuẩn']], 'Da ngăn vi khuẩn xâm nhập.', $d);
        $this->fill($s, 'Mỗi ngày nên uống khoảng 1,5 – 2 ___ nước.', [[0, 'lít']], 'Uống 1,5 – 2 lít nước mỗi ngày tốt cho thận.', $d);
    }

    private function seedKhChatQuanhTaLop71(): void
    {
        $s = 'kh-chat-quanh-ta-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Khi đun nóng, các hạt cấu tạo nên chất chuyển động như thế nào?', ['Chậm lại', 'Nhanh hơn', 'Đứng yên', 'Biến mất'], 1, 'Đun nóng làm các hạt chuyển động nhanh hơn.', $d);
        $this->quiz($s, 'Ở nhiệt độ 100°C, nước tồn tại ở trạng thái nào?', ['Rắn', 'Lỏng', 'Khí (hơi)', 'Vừa lỏng vừa rắn'], 2, 'Ở 100°C nước sôi, chuyển thành hơi nước (thể khí).', $d);
        $this->quiz($s, 'Sắt nóng chảy ở nhiệt độ khoảng bao nhiêu?', ['100°C', '500°C', '1 500°C', '3 000°C'], 2, 'Sắt nóng chảy ở khoảng 1 500°C.', $d);
        $this->quiz($s, '"Nước đá khô" thực chất là gì?', ['Nước đá lạnh', 'Khí CO2 ở thể rắn', 'Muối khô', 'Đường khô'], 1, 'Nước đá khô là khí CO2 ở thể rắn, thăng hoa thành khí.', $d);
        $this->quiz($s, 'Vì sao quần áo ướt phơi ngoài nắng mau khô?', ['Vì nắng đẹp', 'Vì nước bay hơi nhanh khi nóng', 'Vì gió thổi', 'Vì vải tốt'], 1, 'Nắng nóng làm nước trong quần áo bay hơi nhanh.', $d);
        $this->matching($s, 'Nối mỗi sự chuyển thể với ví dụ khác của nó.', [['Đông đặc', 'Nước thành đá'], ['Thăng hoa', 'Băng khô bay hơi'], ['Hóa lỏng', 'Hơi nước thành mưa'], ['Nóng chảy', 'Sáp nến tan']], 'Nước thành đá là đông đặc, băng khô bay hơi là thăng hoa.', $d);
        $this->matching($s, 'Nối mỗi vật với trạng thái của nó ở nhiệt độ thường.', [['Nhôm', 'Rắn'], ['Rượu', 'Lỏng'], ['Khí hê-li', 'Khí'], ['Chì', 'Rắn']], 'Nhôm và chì rắn, rượu lỏng, hê-li là khí.', $d);
        $this->matching($s, 'Nối mỗi chất với trạng thái của nó ở 0°C.', [['Nước', 'Lỏng'], ['Dầu ăn', 'Lỏng'], ['Không khí', 'Khí'], ['Nước đá', 'Rắn']], 'Ở 0°C nước và dầu ăn lỏng, không khí là khí, nước đá rắn.', $d);
        $this->matching($s, 'Nối mỗi ví dụ trong đời sống với quá trình của nó.', [['Nến chảy', 'Nóng chảy'], ['Mực khô', 'Bay hơi'], ['Kính mờ khi lạnh', 'Ngưng tụ'], ['Tủ lạnh đóng tuyết', 'Đông đặc']], 'Nến chảy là nóng chảy, mực khô là bay hơi, kính mờ là ngưng tụ.', $d);
        $this->matching($s, 'Nối mỗi nhiệt độ với sự chuyển thể của nước tại đó.', [['0°C', 'Nước đá tan'], ['100°C', 'Nước sôi'], ['Dưới 0°C', 'Nước đóng băng']], '0°C đá tan, 100°C nước sôi, dưới 0°C nước đóng băng.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm TRẠNG THÁI RẮN hoặc TRẠNG THÁI LỎNG.', [['Vàng', 'RẮN'], ['Đồng', 'RẮN'], ['Mật ong', 'LỎNG'], ['Sữa', 'LỎNG']], 'Vàng và đồng rắn; mật ong và sữa lỏng.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm XẢY RA KHI ĐUN NÓNG hoặc KHI LÀM LẠNH.', [['Sáp nến tan', 'KHI ĐUN NÓNG'], ['Quần áo khô', 'KHI ĐUN NÓNG'], ['Sương đọng', 'KHI LÀM LẠNH'], ['Tuyết rơi', 'KHI LÀM LẠNH']], 'Đun nóng: nến tan, quần áo khô; làm lạnh: sương đọng, tuyết rơi.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm GIỮ NGUYÊN HÌNH DẠNG hoặc THAY ĐỔI THEO VẬT CHỨA.', [['Quyển sách', 'GIỮ NGUYÊN HÌNH DẠNG'], ['Cục tẩy', 'GIỮ NGUYÊN HÌNH DẠNG'], ['Si-rô', 'THAY ĐỔI THEO VẬT CHỨA'], ['Dầu gội', 'THAY ĐỔI THEO VẬT CHỨA']], 'Sách và tẩy giữ hình dạng; si-rô và dầu gội theo vật chứa.', $d);
        $this->sortQ($s, 'Kéo mỗi hiện tượng vào nhóm CHUYỂN THỂ THUẬN (cần nhiệt) hoặc NGHỊCH (toả nhiệt).', [['Bay hơi', 'THUẬN (cần nhiệt)'], ['Thăng hoa', 'THUẬN (cần nhiệt)'], ['Ngưng tụ', 'NGHỊCH (tỏa nhiệt)'], ['Đông đặc', 'NGHỊCH (tỏa nhiệt)']], 'Bay hơi, thăng hoa cần nhiệt; ngưng tụ, đông đặc tỏa nhiệt.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm CÓ THỂ CHẢY ĐƯỢC hoặc KHÔNG.', [['Nước', 'CÓ THỂ CHẢY'], ['Thủy ngân', 'CÓ THỂ CHẢY'], ['Viên đá', 'KHÔNG CHẢY'], ['Thanh gỗ', 'KHÔNG CHẢY']], 'Nước và thủy ngân chảy được; đá và gỗ thì không.', $d);
        $this->fill($s, 'Khi đun nóng, các hạt chất chuyển động ___ hơn.', [[0, 'nhanh']], 'Đun nóng làm hạt chuyển động nhanh hơn.', $d);
        $this->fill($s, 'Ở 100°C, nước tồn tại ở thể ___.', [[0, 'khí']], 'Ở 100°C nước thành hơi (thể khí).', $d);
        $this->fill($s, 'Quần áo ướt mau khô ngoài nắng vì nước bay ___ nhanh.', [[0, 'hơi']], 'Nắng nóng làm nước bay hơi nhanh.', $d);
        $this->fill($s, 'Sắt nóng chảy ở khoảng 1 500 độ ___.', [[0, 'C']], 'Sắt nóng chảy ở khoảng 1 500°C.', $d);
        $this->fill($s, 'Nước đá khô là khí CO2 ở thể ___.', [[0, 'rắn']], 'Nước đá khô là CO2 rắn.', $d);
    }

    private function seedKhChatQuanhTaLop72(): void
    {
        $s = 'kh-chat-quanh-ta-lop-7-2'; $d = 'de';
        $this->quiz($s, 'Nước mưa được hình thành như thế nào?', ['Từ lòng đất', 'Hơi nước ngưng tụ thành mây rồi rơi xuống', 'Từ biển bơm lên', 'Từ nhà máy'], 1, 'Hơi nước bay lên, ngưng tụ thành mây rồi rơi xuống thành mưa.', $d);
        $this->quiz($s, 'Vì sao không nên uống nước lã (chưa đun sôi)?', ['Vì nhạt', 'Vì có thể chứa vi khuẩn gây bệnh', 'Vì lạnh', 'Vì đắt'], 1, 'Nước lã có thể chứa vi khuẩn gây bệnh.', $d);
        $this->quiz($s, 'Nước có vai trò gì với cây trồng?', ['Làm cây đẹp', 'Giúp quang hợp, vận chuyển chất', 'Làm đất cứng', 'Xua sâu'], 1, 'Nước giúp cây quang hợp và vận chuyển chất dinh dưỡng.', $d);
        $this->quiz($s, 'Lũ lụt thường do hiện tượng nào gây ra?', ['Nắng nóng', 'Mưa lớn kéo dài', 'Gió nhẹ', 'Sương mù'], 1, 'Mưa lớn kéo dài gây lũ lụt.', $d);
        $this->quiz($s, 'Nước ngầm được tạo ra như thế nào?', ['Bơm từ biển', 'Nước mưa thấm xuống lòng đất', 'Nhà máy sản xuất', 'Từ mây rơi thẳng'], 1, 'Nước mưa thấm qua đất tạo thành nước ngầm.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn với mô tả trong vòng tuần hoàn của nước.', [['Bay hơi', 'Nước thành hơi bay lên'], ['Ngưng tụ', 'Hơi thành mây'], ['Mưa', 'Nước rơi xuống đất'], ['Thấm', 'Nước vào lòng đất']], 'Bay hơi – ngưng tụ – mưa – thấm là vòng tuần hoàn của nước.', $d);
        $this->matching($s, 'Nối mỗi nguồn nước với nơi có nó.', [['Sông', 'Chảy trên mặt đất'], ['Giếng', 'Đào sâu trong đất'], ['Mưa', 'Rơi từ trời'], ['Biển', 'Nước mặn']], 'Sông chảy mặt đất, giếng đào sâu, mưa từ trời, biển mặn.', $d);
        $this->matching($s, 'Nối mỗi việc làm với hậu quả của nó với nguồn nước.', [['Vứt pin vào nguồn nước', 'Gây nhiễm độc'], ['Xây đập', 'Giữ nước'], ['Phá rừng đầu nguồn', 'Gây lũ lụt'], ['Trồng rừng', 'Giữ nguồn nước']], 'Vứt pin gây nhiễm độc, phá rừng gây lũ, trồng rừng giữ nước.', $d);
        $this->matching($s, 'Nối mỗi nhu cầu với lượng nước cần dùng.', [['Uống', 'Ít nhất'], ['Tắm giặt', 'Nhiều'], ['Tưới ruộng', 'Rất nhiều'], ['Sản xuất công nghiệp', 'Rất nhiều']], 'Uống cần ít nhất; tưới ruộng và công nghiệp cần rất nhiều nước.', $d);
        $this->matching($s, 'Nối mỗi cách làm sạch nước với tác dụng của nó.', [['Lắng', 'Loại cặn bẩn'], ['Lọc', 'Loại chất bẩn'], ['Đun sôi', 'Diệt vi khuẩn'], ['Khử trùng', 'Diệt vi khuẩn']], 'Lắng loại cặn, lọc loại bẩn, đun sôi và khử trùng diệt khuẩn.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm TIẾT KIỆM NƯỚC hoặc LÃNG PHÍ NƯỚC.', [['Tưới cây bằng nước vo gạo', 'TIẾT KIỆM NƯỚC'], ['Sửa vòi nước rò rỉ', 'TIẾT KIỆM NƯỚC'], ['Đánh răng mở vòi', 'LÃNG PHÍ NƯỚC'], ['Tắm bồn đầy', 'LÃNG PHÍ NƯỚC']], 'Nước vo gạo tưới cây và sửa vòi rò rỉ là tiết kiệm.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn nước vào nhóm NƯỚC SẠCH hoặc NƯỚC Ô NHIỄM.', [['Nước mưa hứng sạch', 'NƯỚC SẠCH'], ['Nước máy', 'NƯỚC SẠCH'], ['Nước cống', 'NƯỚC Ô NHIỄM'], ['Nước ao có rác', 'NƯỚC Ô NHIỄM']], 'Nước mưa sạch và nước máy sạch; nước cống và ao rác ô nhiễm.', $d);
        $this->sortQ($s, 'Sắp xếp vòng tuần hoàn của nước: kéo mỗi giai đoạn vào nhóm TRƯỚC KHI MƯA hoặc SAU KHI MƯA.', [['Hơi nước bay lên', 'TRƯỚC KHI MƯA'], ['Mây đen kéo đến', 'TRƯỚC KHI MƯA'], ['Nước chảy thành sông', 'SAU KHI MƯA'], ['Cây cối tươi tốt', 'SAU KHI MƯA']], 'Trước mưa: bay hơi, mây đen; sau mưa: nước chảy thành sông.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm BẢO VỆ NGUỒN NƯỚC hoặc GÂY Ô NHIỄM NGUỒN NƯỚC.', [['Không xả rác xuống sông', 'BẢO VỆ NGUỒN NƯỚC'], ['Xử lí nước thải', 'BẢO VỆ NGUỒN NƯỚC'], ['Đổ dầu xuống sông', 'GÂY Ô NHIỄM'], ['Vứt rác bừa bãi', 'GÂY Ô NHIỄM']], 'Không xả rác và xử lí nước thải bảo vệ nguồn nước.', $d);
        $this->sortQ($s, 'Kéo mỗi việc dùng nước vào nhóm TRONG NHÀ hoặc NGOÀI TRỜI.', [['Nấu ăn', 'TRONG NHÀ'], ['Tắm rửa', 'TRONG NHÀ'], ['Tưới cây', 'NGOÀI TRỜI'], ['Rửa xe', 'NGOÀI TRỜI']], 'Nấu ăn, tắm rửa trong nhà; tưới cây, rửa xe ngoài trời.', $d);
        $this->fill($s, 'Nước mưa hình thành do hơi nước ngưng ___.', [[0, 'tụ']], 'Hơi nước ngưng tụ thành mây gây mưa.', $d);
        $this->fill($s, 'Không nên uống nước lã vì có nhiều vi ___.', [[0, 'khuẩn']], 'Nước lã có vi khuẩn gây bệnh.', $d);
        $this->fill($s, 'Nước mưa thấm xuống đất tạo thành nước ___.', [[0, 'ngầm']], 'Nước thấm xuống tạo nước ngầm.', $d);
        $this->fill($s, 'Mưa lớn kéo dài gây ra lũ ___.', [[0, 'lụt']], 'Mưa lớn kéo dài gây lũ lụt.', $d);
        $this->fill($s, 'Nước giúp cây quang ___ để tạo chất hữu cơ.', [[0, 'hợp']], 'Cây quang hợp nhờ nước và ánh sáng.', $d);
    }

    private function seedKhChatQuanhTaLop81(): void
    {
        $s = 'kh-chat-quanh-ta-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Không khí là chất nguyên chất hay hỗn hợp?', ['Chất nguyên chất', 'Hỗn hợp', 'Đơn chất', 'Hợp chất'], 1, 'Không khí là hỗn hợp của nhiều khí: ni-tơ, ô-xy...', $d);
        $this->quiz($s, 'Nước cất là chất nguyên chất hay hỗn hợp?', ['Hỗn hợp', 'Chất nguyên chất', 'Dung dịch', 'Hỗn hợp không đồng nhất'], 1, 'Nước cất chỉ chứa nước nên là chất nguyên chất.', $d);
        $this->quiz($s, 'Trong dung dịch nước đường, đường đóng vai trò gì?', ['Dung môi', 'Chất tan', 'Chất xúc tác', 'Chất bảo quản'], 1, 'Đường là chất tan, nước là dung môi.', $d);
        $this->quiz($s, 'Muốn tách muối ra khỏi nước biển, ta dùng phương pháp nào?', ['Lọc', 'Cô cạn (bay hơi)', 'Chiết', 'Ly tâm'], 1, 'Cô cạn nước biển để thu muối.', $d);
        $this->quiz($s, 'Dầu ăn và nước có tạo thành dung dịch không?', ['Có', 'Không, vì dầu không tan trong nước', 'Có một phần', 'Tùy nhiệt độ'], 1, 'Dầu không tan trong nước nên không tạo dung dịch.', $d);
        $this->matching($s, 'Nối mỗi hỗn hợp với ví dụ của nó.', [['Hỗn hợp đồng nhất', 'Nước muối'], ['Hỗn hợp không đồng nhất', 'Cát và nước'], ['Hỗn hợp đồng nhất', 'Không khí'], ['Hỗn hợp không đồng nhất', 'Dầu và nước']], 'Nước muối, không khí đồng nhất; cát-nước, dầu-nước không đồng nhất.', $d);
        $this->matching($s, 'Nối mỗi dung dịch với chất tan của nó.', [['Nước đường', 'Đường'], ['Nước muối', 'Muối ăn'], ['Cồn y tế', 'Cồn'], ['Nước chanh đường', 'Đường']], 'Chất tan: đường trong nước đường, muối trong nước muối.', $d);
        $this->matching($s, 'Nối mỗi hỗn hợp với phương pháp tách phù hợp.', [['Cát và nước', 'Lọc'], ['Muối và nước', 'Cô cạn'], ['Dầu và nước', 'Chiết'], ['Rượu và nước', 'Chưng cất']], 'Cát-nước lọc, muối-nước cô cạn, dầu-nước chiết, rượu-nước chưng cất.', $d);
        $this->matching($s, 'Nối mỗi chất với phân loại của nó.', [['Khí ô-xy', 'Đơn chất'], ['Nước', 'Hợp chất'], ['Sắt', 'Đơn chất'], ['Muối ăn', 'Hợp chất']], 'Ô-xy, sắt là đơn chất; nước, muối ăn là hợp chất.', $d);
        $this->matching($s, 'Nối mỗi dụng cụ thí nghiệm với công dụng của nó.', [['Phễu', 'Để lọc'], ['Cốc đong', 'Để đong'], ['Đũa thủy tinh', 'Để khuấy'], ['Đèn cồn', 'Để đun']], 'Phễu lọc, cốc đong, đũa khuấy, đèn cồn đun.', $d);
        $this->sortQ($s, 'Kéo mỗi chất vào nhóm CHẤT NGUYÊN CHẤT hoặc HỖN HỢP.', [['Vàng nguyên chất', 'CHẤT NGUYÊN CHẤT'], ['Nước cất', 'CHẤT NGUYÊN CHẤT'], ['Sữa', 'HỖN HỢP'], ['Không khí', 'HỖN HỢP']], 'Vàng nguyên chất, nước cất là chất nguyên chất; sữa, không khí là hỗn hợp.', $d);
        $this->sortQ($s, 'Kéo mỗi hỗn hợp vào nhóm ĐỒNG NHẤT hoặc KHÔNG ĐỒNG NHẤT.', [['Nước đường', 'ĐỒNG NHẤT'], ['Giấm', 'ĐỒNG NHẤT'], ['Phở', 'KHÔNG ĐỒNG NHẤT'], ['Chè thập cẩm', 'KHÔNG ĐỒNG NHẤT']], 'Nước đường, giấm đồng nhất; phở, chè thập cẩm không đồng nhất.', $d);
        $this->sortQ($s, 'Kéo mỗi phương pháp vào nhóm TÁCH CHẤT RẮN KHÔNG TAN hoặc TÁCH CHẤT TAN.', [['Lọc', 'TÁCH CHẤT RẮN KHÔNG TAN'], ['Cô cạn', 'TÁCH CHẤT TAN'], ['Chưng cất', 'TÁCH CHẤT TAN']], 'Lọc tách chất rắn không tan; cô cạn, chưng cất tách chất tan.', $d);
        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm DUNG MÔI LÀ NƯỚC hoặc DUNG MÔI KHÔNG PHẢI NƯỚC.', [['Nước muối', 'DUNG MÔI LÀ NƯỚC'], ['Nước đường', 'DUNG MÔI LÀ NƯỚC'], ['Cồn i-ốt', 'DUNG MÔI KHÔNG PHẢI NƯỚC'], ['Xăng pha nhớt', 'DUNG MÔI KHÔNG PHẢI NƯỚC']], 'Nước muối, nước đường dung môi là nước; cồn i-ốt, xăng pha nhớt thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi chất vào nhóm TAN ĐƯỢC TRONG NƯỚC hoặc KHÔNG TAN.', [['Muối', 'TAN ĐƯỢC'], ['Đường', 'TAN ĐƯỢC'], ['Cát', 'KHÔNG TAN'], ['Dầu ăn', 'KHÔNG TAN']], 'Muối, đường tan trong nước; cát, dầu ăn không tan.', $d);
        $this->fill($s, 'Không khí là một hỗn ___.', [[0, 'hợp']], 'Không khí là hỗn hợp nhiều khí.', $d);
        $this->fill($s, 'Nước cất là chất nguyên ___.', [[0, 'chất']], 'Nước cất là chất nguyên chất.', $d);
        $this->fill($s, 'Trong dung dịch đường, đường là chất ___.', [[0, 'tan']], 'Đường là chất tan.', $d);
        $this->fill($s, 'Muốn tách muối khỏi nước biển, ta dùng phương pháp cô ___.', [[0, 'cạn']], 'Cô cạn để tách muối.', $d);
        $this->fill($s, 'Dầu ăn không tan trong nước nên không tạo thành dung ___.', [[0, 'dịch']], 'Dầu-nước không tạo dung dịch.', $d);
    }

    private function seedKhChatQuanhTaLop82(): void
    {
        $s = 'kh-chat-quanh-ta-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Khối lượng của nguyên tử tập trung chủ yếu ở đâu?', ['Lớp vỏ electron', 'Hạt nhân', 'Chia đều', 'Ngoài nguyên tử'], 1, 'Khối lượng nguyên tử tập trung ở hạt nhân.', $d);
        $this->quiz($s, 'Số electron ở lớp ngoài cùng quyết định điều gì?', ['Màu sắc', 'Tính chất hóa học', 'Khối lượng', 'Nhiệt độ nóng chảy'], 1, 'Electron lớp ngoài cùng quyết định tính chất hóa học.', $d);
        $this->quiz($s, 'Nguyên tử ô-xy có bao nhiêu electron?', ['6', '8', '10', '16'], 1, 'Ô-xy (Z = 8) có 8 electron.', $d);
        $this->quiz($s, 'Phân tử khí ô-xy gồm mấy nguyên tử ô-xy?', ['1', '2', '3', '4'], 1, 'Phân tử ô-xy gồm 2 nguyên tử, kí hiệu O2.', $d);
        $this->quiz($s, 'Kim loại dẫn điện được là nhờ hạt nào?', ['Proton', 'Electron tự do', 'Nơtron', 'Hạt nhân'], 1, 'Electron tự do giúp kim loại dẫn điện.', $d);
        $this->matching($s, 'Nối mỗi hạt với khối lượng tương đối của nó.', [['Proton', 'Khoảng 1 đvC'], ['Nơtron', 'Khoảng 1 đvC'], ['Electron', 'Rất nhỏ']], 'Proton và nơtron khoảng 1 đvC; electron rất nhỏ.', $d);
        $this->matching($s, 'Nối mỗi chất với công thức hóa học của nó.', [['Khí ô-xy', 'O2'], ['Khí hi-đrô', 'H2'], ['Muối ăn', 'NaCl'], ['Đường', 'C12H22O11']], 'Ô-xy O2, hi-đrô H2, muối ăn NaCl.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với định nghĩa của nó.', [['Nguyên tử', 'Hạt nhỏ nhất của chất'], ['Phân tử', 'Nhiều nguyên tử liên kết'], ['Ion', 'Nguyên tử mang điện'], ['Hạt nhân', 'Trung tâm nguyên tử']], 'Nguyên tử là hạt nhỏ nhất; phân tử do nhiều nguyên tử tạo thành.', $d);
        $this->matching($s, 'Nối mỗi vật với nguyên tố chính cấu tạo nên nó.', [['Dây đồng', 'Đồng'], ['Than chì', 'Cacbon'], ['Nước', 'Hi-đrô và ô-xy'], ['Muối ăn', 'Natri và clo']], 'Dây đồng từ đồng, than chì từ cacbon, nước từ H và O.', $d);
        $this->matching($s, 'Nối mỗi nguyên tố với kí hiệu hóa học của nó.', [['Hi-đrô', 'H'], ['Ô-xy', 'O'], ['Cacbon', 'C'], ['Sắt', 'Fe']], 'Hi-đrô H, ô-xy O, cacbon C, sắt Fe.', $d);
        $this->sortQ($s, 'Kéo mỗi hạt vào nhóm NẰM TRONG HẠT NHÂN hoặc CHUYỂN ĐỘNG QUANH HẠT NHÂN.', [['Proton', 'TRONG HẠT NHÂN'], ['Nơtron', 'TRONG HẠT NHÂN'], ['Electron', 'QUANH HẠT NHÂN']], 'Proton, nơtron trong hạt nhân; electron chuyển động quanh hạt nhân.', $d);
        $this->sortQ($s, 'Kéo mỗi hạt/đối tượng vào nhóm MANG ĐIỆN hoặc KHÔNG MANG ĐIỆN.', [['Proton', 'MANG ĐIỆN'], ['Electron', 'MANG ĐIỆN'], ['Ion', 'MANG ĐIỆN'], ['Nơtron', 'KHÔNG MANG ĐIỆN']], 'Proton, electron, ion mang điện; nơtron không mang điện.', $d);
        $this->sortQ($s, 'Kéo mỗi chất vào nhóm ĐƠN CHẤT hoặc HỢP CHẤT.', [['Khí ô-xy (O2)', 'ĐƠN CHẤT'], ['Sắt (Fe)', 'ĐƠN CHẤT'], ['Nước (H2O)', 'HỢP CHẤT'], ['Khí CO2', 'HỢP CHẤT']], 'O2, Fe là đơn chất; H2O, CO2 là hợp chất.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Hạt nhân gồm proton và nơtron', 'ĐÚNG'], ['Nguyên tử trung hòa về điện', 'ĐÚNG'], ['Electron nằm trong hạt nhân', 'SAI'], ['Proton mang điện âm', 'SAI']], 'Electron quanh hạt nhân; proton mang điện dương.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm KIM LOẠI hoặc PHI KIM.', [['Sắt', 'KIM LOẠI'], ['Đồng', 'KIM LOẠI'], ['Ô-xy', 'PHI KIM'], ['Lưu huỳnh', 'PHI KIM']], 'Sắt, đồng là kim loại; ô-xy, lưu huỳnh là phi kim.', $d);
        $this->fill($s, 'Khối lượng nguyên tử tập trung chủ yếu ở hạt ___.', [[0, 'nhân']], 'Hạt nhân chứa gần hết khối lượng nguyên tử.', $d);
        $this->fill($s, 'Nguyên tử ô-xy có 8 ___.', [[0, 'electron']], 'Ô-xy có 8 electron.', $d);
        $this->fill($s, 'Phân tử khí ô-xy gồm 2 nguyên tử ô-xy, kí hiệu là ___.', [[0, 'O2']], 'Phân tử ô-xy là O2.', $d);
        $this->fill($s, 'Kim loại dẫn điện được là nhờ các electron tự ___.', [[0, 'do']], 'Electron tự do giúp dẫn điện.', $d);
        $this->fill($s, 'Số electron lớp ngoài cùng quyết định tính chất hóa ___.', [[0, 'học']], 'Tính chất hóa học do electron lớp ngoài cùng quyết định.', $d);
    }

    private function seedKhNangLuongLop81(): void
    {
        $s = 'kh-nang-luong-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Ánh sáng mặt trời là dạng năng lượng nào?', ['Hóa năng', 'Quang năng', 'Động năng', 'Thế năng'], 1, 'Ánh sáng mặt trời là quang năng.', $d);
        $this->quiz($s, 'Thức ăn chứa dạng năng lượng nào?', ['Quang năng', 'Điện năng', 'Hóa năng', 'Nhiệt năng'], 2, 'Thức ăn chứa hóa năng cung cấp cho cơ thể.', $d);
        $this->quiz($s, 'Viên pin chứa dạng năng lượng nào?', ['Quang năng', 'Hóa năng', 'Động năng', 'Thế năng'], 1, 'Pin chứa hóa năng, khi dùng biến thành điện năng.', $d);
        $this->quiz($s, 'Năng lượng gió thực chất là dạng năng lượng nào của không khí?', ['Thế năng', 'Động năng', 'Hóa năng', 'Quang năng'], 1, 'Gió là không khí chuyển động nên có động năng.', $d);
        $this->quiz($s, 'Khi bật đèn pin, năng lượng chuyển hóa như thế nào?', ['Quang năng thành điện năng', 'Hóa năng thành điện năng thành quang năng', 'Động năng thành nhiệt năng', 'Thế năng thành động năng'], 1, 'Pin: hóa năng → điện năng → quang năng ở bóng đèn.', $d);
        $this->matching($s, 'Nối mỗi vật với dạng năng lượng chủ yếu của nó.', [['Mặt Trời', 'Quang năng'], ['Thức ăn', 'Hóa năng'], ['Lò xo bị nén', 'Thế năng đàn hồi'], ['Quạt đang quay', 'Động năng']], 'Mặt Trời – quang năng, thức ăn – hóa năng, lò xo nén – thế năng đàn hồi.', $d);
        $this->matching($s, 'Nối mỗi thiết bị với sự chuyển hoá năng lượng của nó.', [['Bóng đèn', 'Điện năng thành quang năng'], ['Quạt điện', 'Điện năng thành động năng'], ['Bếp điện', 'Điện năng thành nhiệt năng'], ['Pin mặt trời', 'Quang năng thành điện năng']], 'Bóng đèn: điện→sáng; quạt: điện→động năng; bếp: điện→nhiệt.', $d);
        $this->matching($s, 'Nối mỗi nguồn năng lượng với dạng năng lượng của nó.', [['Than đá', 'Hóa năng'], ['Gió', 'Động năng'], ['Nước trên đập cao', 'Thế năng'], ['Mặt trời', 'Quang năng']], 'Than – hóa năng, gió – động năng, nước trên cao – thế năng.', $d);
        $this->matching($s, 'Nối mỗi dạng năng lượng với ví dụ của nó.', [['Nhiệt năng', 'Nước đang sôi'], ['Năng lượng âm thanh', 'Tiếng nhạc'], ['Quang năng', 'Ánh nắng'], ['Điện năng', 'Viên pin']], 'Nước sôi – nhiệt năng, tiếng nhạc – âm thanh, ánh nắng – quang năng.', $d);
        $this->matching($s, 'Nối mỗi hoạt động với năng lượng nó tiêu thụ.', [['Chạy bộ', 'Hóa năng từ thức ăn'], ['Xem tivi', 'Điện năng'], ['Nấu cơm', 'Nhiệt năng'], ['Đạp xe', 'Động năng']], 'Chạy bộ dùng hóa năng, xem tivi dùng điện năng.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn vào nhóm NĂNG LƯỢNG TÁI TẠO hoặc NĂNG LƯỢNG HOÁ THẠCH.', [['Gió', 'TÁI TẠO'], ['Sinh khối', 'TÁI TẠO'], ['Khí đốt', 'HOÁ THẠCH'], ['Than bùn', 'HOÁ THẠCH']], 'Gió, sinh khối tái tạo; khí đốt, than bùn là hóa thạch.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm CÓ ĐỘNG NĂNG hoặc KHÔNG CÓ ĐỘNG NĂNG.', [['Xe đang chạy', 'CÓ ĐỘNG NĂNG'], ['Người đang bơi', 'CÓ ĐỘNG NĂNG'], ['Quyển sách trên bàn', 'KHÔNG CÓ'], ['Ngôi nhà đứng yên', 'KHÔNG CÓ']], 'Vật chuyển động có động năng; vật đứng yên thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm CÓ THẾ NĂNG HẤP DẪN hoặc KHÔNG CÓ.', [['Con diều đang bay', 'CÓ THẾ NĂNG'], ['Nước trên đập cao', 'CÓ THẾ NĂNG'], ['Quả bóng dưới đất', 'KHÔNG CÓ'], ['Con cá dưới nước', 'KHÔNG CÓ']], 'Vật ở trên cao có thế năng hấp dẫn.', $d);
        $this->sortQ($s, 'Kéo mỗi thiết bị vào nhóm BIẾN ĐIỆN NĂNG THÀNH NHIỆT NĂNG hoặc THÀNH DẠNG KHÁC.', [['Bàn là', 'THÀNH NHIỆT NĂNG'], ['Ấm điện', 'THÀNH NHIỆT NĂNG'], ['Quạt điện', 'THÀNH DẠNG KHÁC'], ['Đèn LED', 'THÀNH DẠNG KHÁC']], 'Bàn là, ấm điện biến điện thành nhiệt; quạt, đèn LED thành dạng khác.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn vào nhóm NĂNG LƯỢNG SẠCH hoặc KHÔNG SẠCH.', [['Mặt trời', 'SẠCH'], ['Thủy triều', 'SẠCH'], ['Than đá', 'KHÔNG SẠCH'], ['Xăng dầu', 'KHÔNG SẠCH']], 'Mặt trời, thủy triều sạch; than đá, xăng dầu không sạch.', $d);
        $this->fill($s, 'Thức ăn chứa hóa ___ cung cấp năng lượng cho cơ thể.', [[0, 'năng']], 'Thức ăn chứa hóa năng.', $d);
        $this->fill($s, 'Viên pin biến hóa năng thành điện ___.', [[0, 'năng']], 'Pin biến hóa năng thành điện năng.', $d);
        $this->fill($s, 'Năng lượng gió là động năng của không ___.', [[0, 'khí']], 'Gió là động năng của không khí.', $d);
        $this->fill($s, 'Lò xo bị nén có thế năng đàn ___.', [[0, 'hồi']], 'Lò xo nén có thế năng đàn hồi.', $d);
        $this->fill($s, 'Khi bật đèn pin: hóa năng thành điện năng thành quang ___.', [[0, 'năng']], 'Đèn pin: hóa năng → điện năng → quang năng.', $d);
    }

    private function seedKhNangLuongLop82(): void
    {
        $s = 'kh-nang-luong-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Trong trường hợp nào lực KHÔNG sinh công cơ học?', ['Kéo xe đi', 'Đẩy tường nhưng tường không dịch chuyển', 'Nâng vật lên', 'Kéo vật đi'], 1, 'Vật không dịch chuyển thì lực không sinh công.', $d);
        $this->quiz($s, 'Một người đẩy bức tường, tường không dịch chuyển. Công thực hiện bằng bao nhiêu?', ['100 J', '50 J', '0 J', 'Không tính được'], 2, 'Không dịch chuyển nên công bằng 0.', $d);
        $this->quiz($s, 'Ngoài oát (W), đơn vị công suất còn có tên gọi nào?', ['Jun', 'Mã lực', 'Niu-tơn', 'Mét'], 1, 'Mã lực cũng là đơn vị công suất.', $d);
        $this->quiz($s, 'Hai người cùng kéo một vật như nhau, ai "khỏe" hơn?', ['Người kéo nhanh hơn', 'Người to hơn', 'Người kéo chậm hơn', 'Như nhau'], 0, 'Người thực hiện công nhanh hơn (công suất lớn hơn) thì khỏe hơn.', $d);
        $this->quiz($s, 'Công suất 100 W có nghĩa là gì?', ['Thực hiện 100 J trong 1 giây', 'Thực hiện 100 J trong 1 giờ', 'Dùng lực 100 N', 'Đi được 100 m'], 0, '100 W nghĩa là mỗi giây thực hiện công 100 J.', $d);
        $this->matching($s, 'Nối mỗi đại lượng với kí hiệu của nó.', [['Công', 'A'], ['Lực', 'F'], ['Quãng đường', 's'], ['Công suất', 'P']], 'Công A, lực F, quãng đường s, công suất P.', $d);
        $this->matching($s, 'Nối mỗi công thức với tên gọi của nó.', [['A = F × s', 'Công cơ học'], ['P = A / t', 'Công suất'], ['v = s / t', 'Vận tốc'], ['d = m / V', 'Khối lượng riêng']], 'A = F·s là công, P = A/t là công suất.', $d);
        $this->matching($s, 'Nối mỗi ví dụ với công sinh ra của nó.', [['Kéo vật 10 N đi 2 m', '20 J'], ['Đẩy tường không dịch chuyển', '0 J'], ['Nâng vật 50 N lên 1 m', '50 J']], 'Công = lực × quãng đường: 10×2 = 20 J, 50×1 = 50 J.', $d);
        $this->matching($s, 'Nối mỗi thiết bị với công suất đặc trưng của nó.', [['Bóng đèn LED', 'Khoảng 10 W'], ['Quạt điện', 'Khoảng 50 W'], ['Điều hòa', 'Khoảng 1000 W'], ['Tủ lạnh', 'Khoảng 150 W']], 'LED ~10 W, quạt ~50 W, điều hòa ~1000 W.', $d);
        $this->matching($s, 'Nối mỗi hoạt động với công suất tương đối của nó.', [['Đi bộ', 'Nhỏ'], ['Chạy xe máy', 'Trung bình'], ['Máy bay cất cánh', 'Rất lớn'], ['Đọc sách', 'Rất nhỏ']], 'Máy bay công suất rất lớn, đọc sách rất nhỏ.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm CÓ SINH CÔNG CƠ HỌC hoặc KHÔNG SINH CÔNG.', [['Xách cặp lên cầu thang', 'CÓ SINH CÔNG'], ['Kéo xe đi', 'CÓ SINH CÔNG'], ['Giữ vật đứng yên', 'KHÔNG SINH CÔNG'], ['Đẩy tường không dịch', 'KHÔNG SINH CÔNG']], 'Có lực và dịch chuyển thì sinh công; giữ yên hoặc đẩy không dịch thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi đơn vị vào nhóm ĐÚNG hoặc SAI với đại lượng công, công suất.', [['Jun – công', 'ĐÚNG'], ['Oát – công suất', 'ĐÚNG'], ['Niu-tơn – công suất', 'SAI'], ['Mét – công', 'SAI']], 'Jun đo công, oát đo công suất; niu-tơn đo lực, mét đo độ dài.', $d);
        $this->sortQ($s, 'Kéo mỗi máy vào nhóm CÔNG SUẤT LỚN hoặc CÔNG SUẤT NHỎ.', [['Máy bay', 'CÔNG SUẤT LỚN'], ['Tàu hỏa', 'CÔNG SUẤT LỚN'], ['Đồng hồ treo tường', 'CÔNG SUẤT NHỎ'], ['Đèn ngủ', 'CÔNG SUẤT NHỎ']], 'Máy bay, tàu hỏa công suất lớn; đồng hồ, đèn ngủ công suất nhỏ.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm LÀM TĂNG CÔNG hoặc KHÔNG LÀM TĂNG.', [['Tăng lực kéo', 'LÀM TĂNG CÔNG'], ['Kéo đi xa hơn', 'LÀM TĂNG CÔNG'], ['Nghỉ lâu hơn', 'KHÔNG LÀM TĂNG'], ['Đi chậm hơn', 'KHÔNG LÀM TĂNG']], 'Công = lực × quãng đường nên tăng lực hoặc đi xa hơn làm tăng công.', $d);
        $this->sortQ($s, 'Kéo mỗi lực vào nhóm CÙNG CHIỀU hoặc NGƯỢC CHIỀU chuyển động.', [['Lực kéo xe tiến lên', 'CÙNG CHIỀU'], ['Lực đẩy vật đi', 'CÙNG CHIỀU'], ['Lực ma sát', 'NGƯỢC CHIỀU'], ['Sức cản không khí', 'NGƯỢC CHIỀU']], 'Lực kéo cùng chiều chuyển động; ma sát và sức cản ngược chiều.', $d);
        $this->fill($s, 'Người đẩy tường mà tường không dịch chuyển thì công bằng ___.', [[0, '0']], 'Không dịch chuyển nên công bằng 0.', $d);
        $this->fill($s, 'Ngoài oát, đơn vị công suất còn có mã ___.', [[0, 'lực']], 'Mã lực là đơn vị công suất.', $d);
        $this->fill($s, 'Công suất 100 W nghĩa là mỗi giây thực hiện 100 ___.', [[0, 'J']], '100 W = 100 J mỗi giây.', $d);
        $this->fill($s, 'Lực vuông góc với hướng di chuyển thì không sinh ___.', [[0, 'công']], 'Lực vuông góc hướng đi không sinh công.', $d);
        $this->fill($s, 'Người nào thực hiện công nhanh hơn thì công suất ___ hơn.', [[0, 'lớn']], 'Công suất lớn nghĩa là làm việc nhanh.', $d);
    }

    private function seedKhNangLuongLop91(): void
    {
        $s = 'kh-nang-luong-lop-9-1'; $d = 'trung_binh';
        $this->quiz($s, 'Hiệu điện thế là gì?', ['Dòng điện chạy', 'Sự chênh lệch điện thế giữa hai điểm', 'Điện trở của dây', 'Công suất điện'], 1, 'Hiệu điện thế là sự chênh lệch điện thế giữa hai điểm.', $d);
        $this->quiz($s, 'Điện trở có tác dụng gì trong mạch điện?', ['Tăng dòng điện', 'Cản trở dòng điện', 'Phát sáng', 'Tích điện'], 1, 'Điện trở cản trở dòng điện chạy qua.', $d);
        $this->quiz($s, 'Định luật Ôm được phát biểu bằng công thức nào?', ['I = U × R', 'I = U / R', 'I = U + R', 'I = U - R'], 1, 'Định luật Ôm: I = U / R.', $d);
        $this->quiz($s, 'Khi các bóng đèn mắc nối tiếp, cường độ dòng điện như thế nào?', ['Như nhau ở mọi điểm', 'Khác nhau mỗi đèn', 'Bằng không', 'Tăng dần'], 0, 'Mắc nối tiếp thì cường độ dòng điện như nhau ở mọi điểm.', $d);
        $this->quiz($s, 'Vì sao dây dẫn điện thường làm bằng đồng?', ['Vì đẹp', 'Vì dẫn điện tốt và giá rẻ', 'Vì nhẹ', 'Vì cứng'], 1, 'Đồng dẫn điện tốt và giá thành rẻ nên dùng làm dây dẫn.', $d);
        $this->matching($s, 'Nối mỗi đại lượng điện với kí hiệu của nó.', [['Cường độ dòng điện', 'I'], ['Hiệu điện thế', 'U'], ['Điện trở', 'R'], ['Công suất điện', 'P']], 'I – cường độ, U – hiệu điện thế, R – điện trở, P – công suất.', $d);
        $this->matching($s, 'Nối mỗi linh kiện với chức năng của nó.', [['Điện trở', 'Cản trở dòng điện'], ['Bóng đèn', 'Phát sáng'], ['Cầu chì', 'Bảo vệ mạch điện'], ['Biến trở', 'Thay đổi điện trở']], 'Điện trở cản dòng, cầu chì bảo vệ mạch, biến trở thay đổi điện trở.', $d);
        $this->matching($s, 'Nối mỗi nguồn điện với hiệu điện thế đặc trưng của nó.', [['Pin tiểu', '1,5 V'], ['Ắc quy xe máy', '12 V'], ['Điện lưới Việt Nam', '220 V'], ['Pin điện thoại', '3,7 V']], 'Pin tiểu 1,5 V, ắc quy 12 V, điện lưới 220 V.', $d);
        $this->matching($s, 'Nối mỗi cách mắc với đặc điểm của nó.', [['Mắc nối tiếp', 'Cường độ như nhau'], ['Mắc song song', 'Hiệu điện thế như nhau'], ['Mắc nối tiếp', 'Một hỏng thì cả tắt'], ['Mắc song song', 'Một hỏng vẫn sáng']], 'Nối tiếp cùng cường độ; song song cùng hiệu điện thế.', $d);
        $this->matching($s, 'Nối mỗi vật liệu với điện trở suất tương đối của nó.', [['Bạc', 'Nhỏ nhất'], ['Đồng', 'Nhỏ'], ['Sắt', 'Trung bình'], ['Cao su', 'Rất lớn']], 'Bạc dẫn tốt nhất, cao su gần như không dẫn.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm VẬT DẪN ĐIỆN hoặc VẬT CÁCH ĐIỆN.', [['Bạc', 'VẬT DẪN ĐIỆN'], ['Than chì', 'VẬT DẪN ĐIỆN'], ['Sứ', 'VẬT CÁCH ĐIỆN'], ['Nhựa', 'VẬT CÁCH ĐIỆN']], 'Bạc và than chì dẫn điện; sứ và nhựa cách điện.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm MẠCH KÍN (có dòng điện) hoặc MẠCH HỞ.', [['Công tắc đang đóng', 'MẠCH KÍN'], ['Dây nối tốt', 'MẠCH KÍN'], ['Công tắc đang mở', 'MẠCH HỞ'], ['Dây bị đứt', 'MẠCH HỞ']], 'Công tắc đóng, dây tốt là mạch kín; công tắc mở, dây đứt là mạch hở.', $d);
        $this->sortQ($s, 'Kéo mỗi đại lượng vào nhóm ĐO BẰNG AMPE KẾ hoặc ĐO BẰNG VÔN KẾ.', [['Cường độ qua bóng đèn', 'AMPE KẾ'], ['Dòng điện trong dây', 'AMPE KẾ'], ['Hiệu điện thế hai đầu pin', 'VÔN KẾ'], ['Điện áp ổ cắm', 'VÔN KẾ']], 'Ampe kế đo cường độ, vôn kế đo hiệu điện thế.', $d);
        $this->sortQ($s, 'Kéo mỗi nguồn vào nhóm ĐIỆN ÁP AN TOÀN hoặc ĐIỆN ÁP NGUY HIỂM.', [['Pin 12 V', 'AN TOÀN'], ['Nguồn 24 V', 'AN TOÀN'], ['Điện lưới 220 V', 'NGUY HIỂM'], ['Điện 380 V', 'NGUY HIỂM']], 'Dưới 40 V an toàn; 220 V và 380 V nguy hiểm.', $d);
        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm MẮC NỐI TIẾP hoặc MẮC SONG SONG.', [['Đèn trang trí cũ', 'NỐI TIẾP'], ['Ắc quy nối tiếp', 'NỐI TIẾP'], ['Ổ cắm trong nhà', 'SONG SONG'], ['Đèn các phòng', 'SONG SONG']], 'Đèn trang trí cũ mắc nối tiếp; ổ cắm và đèn các phòng mắc song song.', $d);
        $this->fill($s, 'Định luật Ôm: I = U / ___.', [[0, 'R']], 'I = U / R là định luật Ôm.', $d);
        $this->fill($s, 'Mắc nối tiếp thì cường độ dòng điện ở mọi điểm ___ nhau.', [[0, 'như']], 'Nối tiếp: cường độ như nhau mọi điểm.', $d);
        $this->fill($s, 'Điện trở có tác dụng cản trở dòng ___.', [[0, 'điện']], 'Điện trở cản trở dòng điện.', $d);
        $this->fill($s, 'Dây dẫn thường làm bằng đồng vì dẫn điện tốt và giá ___.', [[0, 'rẻ']], 'Đồng dẫn tốt, giá rẻ.', $d);
        $this->fill($s, 'Hiệu điện thế của điện lưới Việt Nam là 220 ___.', [[0, 'V']], 'Điện lưới Việt Nam 220 V.', $d);
    }

    private function seedKhNangLuongLop92(): void
    {
        $s = 'kh-nang-luong-lop-9-2'; $d = 'kho';
        $this->quiz($s, 'Công tơ điện trong gia đình dùng để đo đại lượng nào?', ['Công suất', 'Điện năng tiêu thụ', 'Cường độ', 'Hiệu điện thế'], 1, 'Công tơ điện đo điện năng tiêu thụ (số điện).', $d);
        $this->quiz($s, 'Vì sao dây dẫn điện nóng lên khi có dòng điện chạy qua?', ['Do tác dụng nhiệt của dòng điện', 'Do ma sát', 'Do nắng', 'Do chập điện'], 0, 'Dòng điện có tác dụng nhiệt làm dây dẫn nóng lên.', $d);
        $this->quiz($s, 'Khi phát hiện người bị điện giật, việc đầu tiên cần làm là gì?', ['Kéo người ra ngay', 'Ngắt nguồn điện', 'Dội nước', 'Gọi to'], 1, 'Phải ngắt nguồn điện trước khi cứu người bị giật.', $d);
        $this->quiz($s, 'Vì sao không được dùng dây đồng thay cho dây cầu chì?', ['Vì đắt', 'Vì dây đồng không tự đứt khi quá tải', 'Vì xấu', 'Vì khó nối'], 1, 'Dây đồng không đứt khi quá tải nên không bảo vệ được mạch.', $d);
        $this->quiz($s, 'Thiết bị nào sau đây biến điện năng thành cơ năng?', ['Bàn là', 'Quạt điện', 'Bóng đèn', 'Nồi cơm'], 1, 'Quạt điện biến điện năng thành cơ năng (cánh quạt quay).', $d);
        $this->matching($s, 'Nối mỗi thiết bị với điện năng nó tiêu thụ trong 1 giờ.', [['Bóng đèn 100 W', '0,1 số điện'], ['Bàn là 1000 W', '1 số điện'], ['Tủ lạnh 150 W', '0,15 số điện']], '100 W dùng 1 giờ hết 0,1 số điện; 1000 W hết 1 số điện.', $d);
        $this->matching($s, 'Nối mỗi sự cố điện với cách xử lí an toàn của nó.', [['Chập điện', 'Ngắt cầu dao ngay'], ['Người bị điện giật', 'Ngắt điện rồi mới cứu'], ['Dây điện đứt rơi xuống', 'Tránh xa và báo'], ['Mưa bão lớn', 'Rút thiết bị điện']], 'Chập điện ngắt cầu dao; người bị giật phải ngắt điện trước.', $d);
        $this->matching($s, 'Nối mỗi thói quen với kết quả của nó.', [['Dùng đèn LED', 'Ít tốn điện'], ['Tắt thiết bị không dùng', 'Tiết kiệm điện'], ['Để tủ lạnh mở lâu', 'Tốn điện'], ['Để điều hòa 16°C', 'Tốn điện']], 'Đèn LED và tắt thiết bị tiết kiệm điện.', $d);
        $this->matching($s, 'Nối mỗi đại lượng điện với đơn vị của nó.', [['Điện năng', 'kWh'], ['Công suất', 'W'], ['Cường độ dòng điện', 'A'], ['Hiệu điện thế', 'V']], 'Điện năng kWh, công suất W, cường độ A, hiệu điện thế V.', $d);
        $this->matching($s, 'Nối mỗi tác dụng của dòng điện với ví dụ của nó.', [['Tác dụng nhiệt', 'Bàn là'], ['Tác dụng phát sáng', 'Bóng đèn'], ['Tác dụng từ', 'Nam châm điện'], ['Tác dụng hóa học', 'Mạ điện']], 'Bàn là – nhiệt, bóng đèn – sáng, nam châm điện – từ, mạ điện – hóa.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm AN TOÀN ĐIỆN hoặc NGUY HIỂM.', [['Dùng bút thử điện', 'AN TOÀN ĐIỆN'], ['Đứng trên ghế khô khi sửa', 'AN TOÀN ĐIỆN'], ['Chạm tay ướt vào công tắc', 'NGUY HIỂM'], ['Leo lên cột điện', 'NGUY HIỂM']], 'Bút thử điện an toàn; tay ướt chạm công tắc, leo cột điện nguy hiểm.', $d);
        $this->sortQ($s, 'Kéo mỗi thiết bị vào nhóm TỐN NHIỀU ĐIỆN hoặc ÍT TỐN ĐIỆN.', [['Điều hòa', 'TỐN NHIỀU ĐIỆN'], ['Bình nóng lạnh', 'TỐN NHIỀU ĐIỆN'], ['Đèn LED', 'ÍT TỐN ĐIỆN'], ['Quạt điện', 'ÍT TỐN ĐIỆN']], 'Điều hòa, bình nóng lạnh tốn nhiều điện; đèn LED, quạt ít tốn.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm TIẾT KIỆM ĐIỆN hoặc LÃNG PHÍ ĐIỆN.', [['Giặt đầy lồng máy', 'TIẾT KIỆM ĐIỆN'], ['Phơi quần áo ngoài nắng', 'TIẾT KIỆM ĐIỆN'], ['Giặt ít đồ nhiều lần', 'LÃNG PHÍ ĐIỆN'], ['Sấy quần áo thường xuyên', 'LÃNG PHÍ ĐIỆN']], 'Giặt đầy lồng, phơi nắng tiết kiệm; giặt ít đồ nhiều lần lãng phí.', $d);
        $this->sortQ($s, 'Kéo mỗi số liệu vào nhóm ĐIỆN ÁP ĐỊNH MỨC hay CÔNG SUẤT ĐỊNH MỨC trên nhãn thiết bị.', [['220 V', 'ĐIỆN ÁP ĐỊNH MỨC'], ['110 V', 'ĐIỆN ÁP ĐỊNH MỨC'], ['100 W', 'CÔNG SUẤT ĐỊNH MỨC'], ['1000 W', 'CÔNG SUẤT ĐỊNH MỨC']], 'V là điện áp định mức; W là công suất định mức.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm NÊN LÀM hoặc KHÔNG NÊN khi trời có sấm sét.', [['Ở trong nhà', 'NÊN LÀM'], ['Rút ăng-ten tivi', 'NÊN LÀM'], ['Đứng dưới cây to', 'KHÔNG NÊN'], ['Cầm ô kim loại', 'KHÔNG NÊN']], 'Sấm sét nên ở trong nhà; không đứng dưới cây to.', $d);
        $this->fill($s, 'Công tơ điện đo lượng điện ___ mà gia đình tiêu thụ.', [[0, 'năng']], 'Công tơ đo điện năng tiêu thụ.', $d);
        $this->fill($s, 'Khi có người bị điện giật, việc đầu tiên là ngắt nguồn ___.', [[0, 'điện']], 'Phải ngắt nguồn điện trước khi cứu.', $d);
        $this->fill($s, 'Không dùng dây đồng thay cầu chì vì nó không tự ___ khi quá tải.', [[0, 'đứt']], 'Cầu chì phải tự đứt khi quá tải.', $d);
        $this->fill($s, 'Quạt điện biến điện năng thành cơ ___.', [[0, 'năng']], 'Quạt biến điện năng thành cơ năng.', $d);
        $this->fill($s, 'Dây dẫn nóng lên khi có dòng điện là do tác dụng ___.', [[0, 'nhiệt']], 'Dòng điện có tác dụng nhiệt.', $d);
    }

    private function seedKhoaHocThpt10Lop101(): void
    {
        $s = 'khoa-hoc-thpt-10-lop-10-1'; $d = 'trung_binh';
        $this->quiz($s, 'Một xe đi quãng đường 90 km trong 1,5 giờ. Vận tốc của xe là bao nhiêu?', ['45 km/h', '60 km/h', '75 km/h', '135 km/h'], 1, 'v = s/t = 90/1,5 = 60 km/h.', $d);
        $this->quiz($s, '72 km/h đổi ra m/s bằng bao nhiêu?', ['7,2 m/s', '20 m/s', '36 m/s', '72 m/s'], 1, '72 km/h = 72/3,6 = 20 m/s.', $d);
        $this->quiz($s, 'Trong chuyển động thẳng đều, quãng đường tỉ lệ thuận với đại lượng nào?', ['Gia tốc', 'Thời gian', 'Khối lượng', 'Lực'], 1, 's = v·t nên quãng đường tỉ lệ thuận với thời gian.', $d);
        $this->quiz($s, 'Đơn vị của vận tốc trong hệ SI là gì?', ['km/h', 'm/s', 'm/s²', 'km/s'], 1, 'Trong hệ SI, vận tốc đo bằng m/s.', $d);
        $this->quiz($s, 'Một người chạy với vận tốc 10 m/s trong 30 giây. Quãng đường đi được là:', ['300 m', '30 m', '3 m', '40 m'], 0, 's = v·t = 10 × 30 = 300 m.', $d);
        $this->matching($s, 'Nối mỗi đại lượng với đơn vị SI của nó.', [['Quãng đường', 'm'], ['Thời gian', 's'], ['Vận tốc', 'm/s'], ['Gia tốc', 'm/s²']], 'SI: quãng đường m, thời gian s, vận tốc m/s, gia tốc m/s².', $d);
        $this->matching($s, 'Nối mỗi vận tốc với quãng đường xe đi được trong 2 giờ.', [['36 km/h', '72 km'], ['50 km/h', '100 km'], ['60 km/h', '120 km'], ['20 m/s', '144 km']], 'Quãng đường 2 giờ: 36→72 km, 50→100 km, 60→120 km.', $d);
        $this->matching($s, 'Nối mỗi công thức với ý nghĩa của nó.', [['s = v × t', 'Tính quãng đường'], ['v = s / t', 'Tính vận tốc'], ['t = s / v', 'Tính thời gian']], 's = v·t tính quãng đường, v = s/t tính vận tốc, t = s/v tính thời gian.', $d);
        $this->matching($s, 'Nối mỗi chuyển động với ví dụ của nó.', [['Thẳng đều', 'Xe chạy ổn định'], ['Tròn đều', 'Kim đồng hồ'], ['Rơi tự do', 'Vật rơi thẳng'], ['Ném ngang', 'Bóng lăn khỏi bàn']], 'Xe chạy ổn định là thẳng đều, kim đồng hồ là tròn đều.', $d);
        $this->matching($s, 'Nối mỗi dạng đồ thị với chuyển động tương ứng.', [['Đường thẳng qua gốc (s – t)', 'Thẳng đều'], ['Đường thẳng song song trục t (v – t)', 'Thẳng đều'], ['Đường cong (s – t)', 'Biến đổi']], 'Đồ thị s–t là đường thẳng qua gốc khi chuyển động thẳng đều.', $d);
        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm CHUYỂN ĐỘNG ĐỀU hoặc BIẾN ĐỔI.', [['Tàu chạy ổn định', 'CHUYỂN ĐỘNG ĐỀU'], ['Ánh sáng trong chân không', 'CHUYỂN ĐỘNG ĐỀU'], ['Xe đang phanh', 'BIẾN ĐỔI'], ['Vật rơi tự do', 'BIẾN ĐỔI']], 'Tàu ổn định và ánh sáng là đều; xe phanh và vật rơi là biến đổi.', $d);
        $this->sortQ($s, 'Kéo mỗi đại lượng vào nhóm ĐẠI LƯỢNG VÔ HƯỚNG hoặc VÉC-TƠ.', [['Quãng đường', 'VÔ HƯỚNG'], ['Thời gian', 'VÔ HƯỚNG'], ['Vận tốc', 'VÉC-TƠ'], ['Gia tốc', 'VÉC-TƠ']], 'Quãng đường, thời gian vô hướng; vận tốc, gia tốc là véc-tơ.', $d);
        $this->sortQ($s, 'Kéo mỗi giá trị vận tốc vào nhóm LỚN HƠN hoặc NHỎ HƠN 15 m/s.', [['72 km/h', 'LỚN HƠN 15 m/s'], ['54 km/h', 'BẰNG 15 m/s'], ['36 km/h', 'NHỎ HƠN 15 m/s'], ['10 m/s', 'NHỎ HƠN 15 m/s']], '54 km/h = 15 m/s; 72 km/h = 20 m/s; 36 km/h = 10 m/s.', $d);
        $this->sortQ($s, 'Kéo mỗi phương trình vào nhóm ĐÚNG với chuyển động thẳng đều.', [['s = v × t', 'ĐÚNG'], ['v không đổi', 'ĐÚNG'], ['v tăng dần', 'SAI'], ['a khác 0', 'SAI']], 'Thẳng đều: s = v·t, v không đổi, a = 0.', $d);
        $this->sortQ($s, 'Kéo mỗi vận tốc vào nhóm NHANH HƠN hoặc CHẬM HƠN 60 km/h.', [['80 km/h', 'NHANH HƠN'], ['25 m/s', 'NHANH HƠN'], ['40 km/h', 'CHẬM HƠN'], ['10 m/s', 'CHẬM HƠN']], '25 m/s = 90 km/h nhanh hơn 60; 10 m/s = 36 km/h chậm hơn.', $d);
        $this->fill($s, '72 km/h đổi ra m/s bằng ___ m/s.', [[0, '20']], '72/3,6 = 20 m/s.', $d);
        $this->fill($s, 'Xe đi 90 km trong 1,5 giờ thì vận tốc là ___ km/h.', [[0, '60']], 'v = 90/1,5 = 60 km/h.', $d);
        $this->fill($s, 'Trong hệ SI, đơn vị của vận tốc là ___.', [[0, 'm/s']], 'SI: vận tốc đo bằng m/s.', $d);
        $this->fill($s, 'Người chạy 10 m/s trong 30 giây đi được ___ m.', [[0, '300']], 's = 10 × 30 = 300 m.', $d);
        $this->fill($s, 'Trong chuyển động thẳng đều, quãng đường tỉ lệ thuận với thời ___.', [[0, 'gian']], 's = v·t nên tỉ lệ thuận với thời gian.', $d);
    }

    private function seedKhoaHocThpt10Lop102(): void
    {
        $s = 'khoa-hoc-thpt-10-lop-10-2'; $d = 'kho';
        $this->quiz($s, 'Đơn vị của gia tốc trong hệ SI là gì?', ['m/s', 'm/s²', 'km/h', 'N'], 1, 'Gia tốc đo bằng m/s².', $d);
        $this->quiz($s, 'Xe đang chạy 20 m/s, phanh dừng hẳn sau 10 s. Gia tốc của xe là:', ['2 m/s²', '-2 m/s²', '200 m/s²', '0'], 1, 'a = (0 - 20)/10 = -2 m/s².', $d);
        $this->quiz($s, 'Trong chuyển động thẳng chậm dần đều, gia tốc và vận tốc có quan hệ thế nào?', ['Cùng chiều', 'Ngược chiều', 'Vuông góc', 'Không liên quan'], 1, 'Chậm dần đều: gia tốc ngược chiều vận tốc.', $d);
        $this->quiz($s, 'Vật rơi tự do (g = 10 m/s²) sau 2 giây có vận tốc bao nhiêu?', ['5 m/s', '10 m/s', '20 m/s', '2 m/s'], 2, 'v = g·t = 10 × 2 = 20 m/s.', $d);
        $this->quiz($s, 'Đồ thị vận tốc – thời gian của chuyển động thẳng biến đổi đều có dạng gì?', ['Đường thẳng song song trục t', 'Đường thẳng xiên', 'Đường cong', 'Đường tròn'], 1, 'Đồ thị v–t là đường thẳng xiên góc.', $d);
        $this->matching($s, 'Nối mỗi công thức với tên gọi của nó.', [['a = Δv / Δt', 'Gia tốc'], ['v = v0 + a × t', 'Vận tốc'], ['s = v0 × t + a × t² / 2', 'Quãng đường']], 'a = Δv/Δt là gia tốc, v = v0 + at là vận tốc.', $d);
        $this->matching($s, 'Nối mỗi chuyển động với dấu của gia tốc (chọn chiều dương là chiều chuyển động).', [['Nhanh dần đều', 'a cùng chiều v'], ['Chậm dần đều', 'a ngược chiều v'], ['Thẳng đều', 'a = 0']], 'Nhanh dần đều a cùng chiều v; chậm dần đều a ngược chiều v.', $d);
        $this->matching($s, 'Nối mỗi giá trị km/h với giá trị m/s tương ứng.', [['18 km/h', '5 m/s'], ['36 km/h', '10 m/s'], ['54 km/h', '15 m/s'], ['90 km/h', '25 m/s']], 'Chia cho 3,6: 18→5, 36→10, 54→15, 90→25 m/s.', $d);
        $this->matching($s, 'Nối mỗi ví dụ với loại chuyển động của nó.', [['Xe đang phanh', 'Chậm dần đều'], ['Vật rơi tự do', 'Nhanh dần đều'], ['Xe bắt đầu tăng tốc', 'Nhanh dần đều'], ['Tàu vào ga', 'Chậm dần đều']], 'Xe phanh, tàu vào ga là chậm dần đều; vật rơi, tăng tốc là nhanh dần đều.', $d);
        $this->matching($s, 'Nối mỗi đại lượng với đặc điểm của nó.', [['Gia tốc', 'Đại lượng véc-tơ'], ['Quãng đường', 'Đại lượng vô hướng'], ['Vận tốc', 'Đại lượng véc-tơ'], ['Thời gian', 'Đại lượng vô hướng']], 'Gia tốc, vận tốc là véc-tơ; quãng đường, thời gian vô hướng.', $d);
        $this->sortQ($s, 'Kéo mỗi chuyển động vào nhóm NHANH DẦN ĐỀU hoặc CHẬM DẦN ĐỀU.', [['Xe bắt đầu xuất phát', 'NHANH DẦN ĐỀU'], ['Vật rơi tự do', 'NHANH DẦN ĐỀU'], ['Xe đang phanh', 'CHẬM DẦN ĐỀU'], ['Tàu vào ga', 'CHẬM DẦN ĐỀU']], 'Xuất phát và rơi tự do là nhanh dần đều; phanh và vào ga là chậm dần đều.', $d);
        $this->sortQ($s, 'Kéo mỗi đại lượng vào nhóm CÓ THỂ ÂM hoặc LUÔN KHÔNG ÂM.', [['Gia tốc', 'CÓ THỂ ÂM'], ['Vận tốc', 'CÓ THỂ ÂM'], ['Quãng đường', 'LUÔN KHÔNG ÂM'], ['Thời gian', 'LUÔN KHÔNG ÂM']], 'Gia tốc, vận tốc có thể âm; quãng đường, thời gian không âm.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Rơi tự do là nhanh dần đều', 'ĐÚNG'], ['Chậm dần đều thì a ngược chiều v', 'ĐÚNG'], ['Thẳng đều có gia tốc bằng 0', 'ĐÚNG'], ['Gia tốc luôn dương', 'SAI']], 'Gia tốc có thể âm khi chọn chiều dương thích hợp.', $d);
        $this->sortQ($s, 'Kéo mỗi giá trị gia tốc vào nhóm DƯƠNG hoặc ÂM (chọn chiều dương là chiều chuyển động).', [['a = 2 m/s²', 'DƯƠNG'], ['a = 5 m/s²', 'DƯƠNG'], ['a = -3 m/s²', 'ÂM'], ['a = -1 m/s²', 'ÂM']], 'Nhanh dần đều a dương; chậm dần đều a âm.', $d);
        $this->sortQ($s, 'Kéo mỗi chuyển động vào nhóm CÓ GIA TỐC hoặc KHÔNG CÓ GIA TỐC.', [['Biến đổi đều', 'CÓ GIA TỐC'], ['Rơi tự do', 'CÓ GIA TỐC'], ['Thẳng đều', 'KHÔNG CÓ'], ['Đứng yên', 'KHÔNG CÓ']], 'Biến đổi đều và rơi tự do có gia tốc; thẳng đều và đứng yên không có.', $d);
        $this->fill($s, 'Đơn vị của gia tốc trong hệ SI là ___.', [[0, 'm/s²']], 'Gia tốc đo bằng m/s².', $d);
        $this->fill($s, 'Xe đang chạy 20 m/s phanh dừng sau 10 s thì gia tốc là ___ m/s².', [[0, '-2']], 'a = (0-20)/10 = -2 m/s².', $d);
        $this->fill($s, 'Vật rơi tự do sau 3 s (g = 10 m/s²) có vận tốc ___ m/s.', [[0, '30']], 'v = 10 × 3 = 30 m/s.', $d);
        $this->fill($s, 'Đồ thị vận tốc – thời gian của chuyển động biến đổi đều là đường thẳng ___.', [[0, 'xiên']], 'Đồ thị v–t là đường thẳng xiên.', $d);
        $this->fill($s, 'Trong chuyển động chậm dần đều, tích a × v ___ 0.', [[0, '<']], 'Chậm dần đều: a ngược chiều v nên a·v < 0.', $d);
    }

    private function seedKhoaHocThpt10Lop103(): void
    {
        $s = 'khoa-hoc-thpt-10-lop-10-3'; $d = 'trung_binh';
        $this->quiz($s, 'Theo định luật II Newton, gia tốc của vật tỉ lệ như thế nào với lực tác dụng?', ['Tỉ lệ thuận', 'Tỉ lệ nghịch', 'Không liên quan', 'Bằng nhau'], 0, 'a = F/m nên gia tốc tỉ lệ thuận với lực, tỉ lệ nghịch với khối lượng.', $d);
        $this->quiz($s, 'Một xe khối lượng 1000 kg tăng tốc 2 m/s². Lực tác dụng là bao nhiêu?', ['500 N', '1000 N', '2000 N', '200 N'], 2, 'F = m·a = 1000 × 2 = 2000 N.', $d);
        $this->quiz($s, 'Vì sao hành khách bị ngã về phía trước khi xe phanh gấp?', ['Do lực đẩy', 'Do quán tính', 'Do ma sát', 'Do trọng lực'], 1, 'Do quán tính, cơ thể giữ nguyên chuyển động nên chúi về phía trước.', $d);
        $this->quiz($s, 'Lực và phản lực có đặc điểm nào sau đây?', ['Cùng đặt vào một vật', 'Cùng độ lớn, ngược chiều, đặt vào hai vật khác nhau', 'Cùng chiều', 'Triệt tiêu nhau'], 1, 'Lực và phản lực cùng độ lớn, ngược chiều, đặt vào hai vật khác nhau.', $d);
        $this->quiz($s, 'Khối lượng và trọng lượng khác nhau ở điểm nào?', ['Như nhau', 'Khối lượng không đổi, trọng lượng phụ thuộc vào g', 'Trọng lượng không đổi', 'Cả hai đều thay đổi'], 1, 'Khối lượng không đổi; trọng lượng P = mg phụ thuộc gia tốc trọng trường.', $d);
        $this->matching($s, 'Nối mỗi định luật Newton với công thức của nó.', [['Định luật I', 'Không có công thức riêng'], ['Định luật II', 'F = m × a'], ['Định luật III', 'F12 = -F21']], 'Định luật II: F = ma; định luật III: lực – phản lực.', $d);
        $this->matching($s, 'Nối mỗi hiện tượng với định luật Newton giải thích nó.', [['Xe phanh, người chúi tới', 'Định luật I'], ['Đá bóng bay đi', 'Định luật II'], ['Súng giật lùi khi bắn', 'Định luật III'], ['Tên lửa bay lên', 'Định luật III']], 'Quán tính – ĐL I, F = ma – ĐL II, giật lùi – ĐL III.', $d);
        $this->matching($s, 'Nối mỗi đại lượng với đơn vị SI của nó.', [['Lực', 'N'], ['Khối lượng', 'kg'], ['Gia tốc', 'm/s²']], 'Lực N, khối lượng kg, gia tốc m/s².', $d);
        $this->matching($s, 'Nối mỗi ví dụ với loại lực tác dụng.', [['Táo rơi xuống', 'Trọng lực'], ['Lò xo bị kéo', 'Lực đàn hồi'], ['Xe trượt trên đường', 'Lực ma sát'], ['Nam châm hút sắt', 'Lực từ']], 'Táo rơi – trọng lực, lò xo – đàn hồi, xe trượt – ma sát.', $d);
        $this->matching($s, 'Nối mỗi sự thay đổi với kết quả của gia tốc (F không đổi).', [['F tăng 2 lần, m không đổi', 'a tăng 2 lần'], ['m tăng 2 lần, F không đổi', 'a giảm 2 lần']], 'a = F/m: F tăng thì a tăng, m tăng thì a giảm.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp lực vào nhóm CẶP LỰC – PHẢN LỰC hoặc KHÔNG PHẢI.', [['Tay đẩy tường – tường đẩy tay', 'LỰC – PHẢN LỰC'], ['Chân đạp đất – đất đẩy chân', 'LỰC – PHẢN LỰC'], ['Trọng lực – lực ma sát', 'KHÔNG PHẢI'], ['Lực kéo – trọng lượng', 'KHÔNG PHẢI']], 'Lực – phản lực tác dụng vào hai vật khác nhau.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Lực và phản lực cùng độ lớn', 'ĐÚNG'], ['Quán tính là tính giữ nguyên trạng thái', 'ĐÚNG'], ['Công thức là F = m / a', 'SAI'], ['Khối lượng thay đổi theo nơi đặt', 'SAI']], 'F = m·a; khối lượng không đổi mọi nơi.', $d);
        $this->sortQ($s, 'Kéo mỗi tình huống vào nhóm THỂ HIỆN QUÁN TÍNH hoặc KHÔNG.', [['Giật nhanh khăn trải bàn', 'QUÁN TÍNH'], ['Xe phanh, người chúi tới', 'QUÁN TÍNH'], ['Xe chạy đều', 'KHÔNG RÕ'], ['Vật bị đẩy thì chuyển động', 'KHÔNG RÕ']], 'Giật khăn và phanh gấp thể hiện quán tính.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp (vật 2 kg) vào nhóm LỰC NHỎ (≤ 6 N) hoặc LỰC LỚN (> 6 N).', [['Lực 4 N', 'LỰC NHỎ'], ['Lực 6 N', 'LỰC NHỎ'], ['Lực 8 N', 'LỰC LỚN'], ['Lực 10 N', 'LỰC LỚN']], 'So sánh với 6 N để phân loại.', $d);
        $this->sortQ($s, 'Kéo mỗi đại lượng vào nhóm KHỐI LƯỢNG hoặc TRỌNG LƯỢNG.', [['5 kg', 'KHỐI LƯỢNG'], ['2 kg', 'KHỐI LƯỢNG'], ['50 N (g = 10)', 'TRỌNG LƯỢNG'], ['20 N (g = 10)', 'TRỌNG LƯỢNG']], 'kg là khối lượng, N là trọng lượng.', $d);
        $this->fill($s, 'Xe khối lượng 1000 kg tăng tốc 2 m/s² cần lực ___ N.', [[0, '2000']], 'F = 1000 × 2 = 2000 N.', $d);
        $this->fill($s, 'Hành khách ngã về phía trước khi xe phanh gấp là do quán ___.', [[0, 'tính']], 'Quán tính giữ nguyên chuyển động.', $d);
        $this->fill($s, 'Lực và phản lực đặt vào hai ___ khác nhau.', [[0, 'vật']], 'Lực – phản lực tác dụng lên hai vật khác nhau.', $d);
        $this->fill($s, 'Khối lượng của vật không đổi dù ở Trái Đất hay Mặt ___.', [[0, 'Trăng']], 'Khối lượng không đổi mọi nơi.', $d);
        $this->fill($s, 'Định luật II Newton: a = F / ___.', [[0, 'm']], 'a = F/m.', $d);
    }

    private function seedKhoaHocThpt10Lop104(): void
    {
        $s = 'khoa-hoc-thpt-10-lop-10-4'; $d = 'kho';
        $this->quiz($s, 'Trọng lực tác dụng lên vật có phương và chiều như thế nào?', ['Ngang, sang phải', 'Thẳng đứng, hướng xuống', 'Thẳng đứng, hướng lên', 'Xiên'], 1, 'Trọng lực có phương thẳng đứng, chiều hướng xuống.', $d);
        $this->quiz($s, 'Lực ma sát trượt xuất hiện khi nào?', ['Vật đứng yên', 'Vật trượt trên bề mặt', 'Vật rơi tự do', 'Vật bay'], 1, 'Ma sát trượt xuất hiện khi vật trượt trên bề mặt.', $d);
        $this->quiz($s, 'Công thức tính lực đàn hồi của lò xo là:', ['F = m × a', 'F = k × Δl', 'F = μ × N', 'P = m × g'], 1, 'Lực đàn hồi: F = k·Δl.', $d);
        $this->quiz($s, 'Lò xo có k = 100 N/m bị dãn 0,1 m. Lực đàn hồi là bao nhiêu?', ['10 N', '100 N', '1000 N', '1 N'], 0, 'F = 100 × 0,1 = 10 N.', $d);
        $this->quiz($s, 'Vì sao đi trên mặt băng dễ bị ngã?', ['Vì lạnh', 'Vì lực ma sát rất nhỏ', 'Vì trơn mắt', 'Vì nặng'], 1, 'Mặt băng ma sát rất nhỏ nên dễ trượt ngã.', $d);
        $this->matching($s, 'Nối mỗi loại lực với công thức tính của nó.', [['Trọng lực', 'P = m × g'], ['Ma sát trượt', 'F = μ × N'], ['Lực đàn hồi', 'F = k × Δl']], 'P = mg, Fms = μN, Fđh = kΔl.', $d);
        $this->matching($s, 'Nối mỗi việc làm với tác dụng của nó lên ma sát.', [['Tra dầu mỡ', 'Giảm ma sát'], ['Rắc cát lên mặt băng', 'Tăng ma sát'], ['Mài nhẵn bề mặt', 'Giảm ma sát'], ['Đi giày đế gai', 'Tăng ma sát']], 'Tra dầu giảm ma sát; rắc cát, đế gai tăng ma sát.', $d);
        $this->matching($s, 'Nối mỗi vật với lực giữ nó cân bằng.', [['Quyển sách trên bàn', 'Phản lực của bàn'], ['Bóng đèn treo', 'Lực căng của dây'], ['Xe đứng yên trên dốc', 'Lực ma sát nghỉ']], 'Sách – phản lực bàn, đèn – lực căng dây, xe – ma sát nghỉ.', $d);
        $this->matching($s, 'Nối mỗi độ dãn của lò xo (k = 200 N/m) với lực đàn hồi.', [['0,05 m', '10 N'], ['0,1 m', '20 N'], ['0,15 m', '30 N']], 'F = 200 × Δl: 0,05→10 N, 0,1→20 N, 0,15→30 N.', $d);
        $this->matching($s, 'Nối mỗi lực với phương, chiều của nó.', [['Trọng lực', 'Thẳng đứng, hướng xuống'], ['Phản lực của mặt bàn', 'Thẳng đứng, hướng lên'], ['Lực căng dây', 'Dọc theo dây']], 'Trọng lực xuống, phản lực lên, căng dây dọc dây.', $d);
        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm MA SÁT CÓ ÍCH hoặc MA SÁT CÓ HẠI.', [['Ma sát giữa giày và đất', 'CÓ ÍCH'], ['Ma sát phanh xe', 'CÓ ÍCH'], ['Ma sát trong máy móc', 'CÓ HẠI'], ['Ma sát làm mòn lốp', 'CÓ HẠI']], 'Ma sát giúp đi lại, phanh xe; nhưng làm mòn máy móc, lốp xe.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Trọng lực hướng thẳng đứng xuống', 'ĐÚNG'], ['Công thức lực đàn hồi là F = kΔl', 'ĐÚNG'], ['Ma sát trượt cùng chiều chuyển động', 'SAI'], ['Đi trên băng dễ vì ma sát lớn', 'SAI']], 'Ma sát trượt ngược chiều chuyển động; băng ma sát nhỏ.', $d);
        $this->sortQ($s, 'Kéo mỗi vật vào nhóm TRỌNG LƯỢNG LỚN HƠN hoặc NHỎ HƠN 50 N (g = 10 m/s²).', [['Vật 6 kg', 'LỚN HƠN 50 N'], ['Vật 4 kg', 'NHỎ HƠN 50 N']], '6 kg → 60 N lớn hơn 50 N; 4 kg → 40 N nhỏ hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi lực vào nhóm LỰC TIẾP XÚC hoặc LỰC TRƯỜNG.', [['Lực ma sát', 'LỰC TIẾP XÚC'], ['Lực đàn hồi', 'LỰC TIẾP XÚC'], ['Trọng lực', 'LỰC TRƯỜNG'], ['Lực hấp dẫn', 'LỰC TRƯỜNG']], 'Ma sát, đàn hồi cần tiếp xúc; trọng lực, hấp dẫn là lực trường.', $d);
        $this->sortQ($s, 'Kéo mỗi lực vào nhóm PHỤ THUỘC hoặc KHÔNG PHỤ THUỘC vào khối lượng vật.', [['Trọng lực', 'PHỤ THUỘC'], ['Ma sát trượt', 'PHỤ THUỘC'], ['Lực đàn hồi lò xo', 'KHÔNG PHỤ THUỘC']], 'Trọng lực và ma sát phụ thuộc khối lượng; đàn hồi phụ thuộc độ dãn.', $d);
        $this->fill($s, 'Trọng lực có phương thẳng đứng, chiều hướng ___.', [[0, 'xuống']], 'Trọng lực hướng xuống.', $d);
        $this->fill($s, 'Lò xo k = 100 N/m dãn 0,1 m thì lực đàn hồi là ___ N.', [[0, '10']], 'F = 100 × 0,1 = 10 N.', $d);
        $this->fill($s, 'Đi trên băng dễ ngã vì lực ma sát rất ___.', [[0, 'nhỏ']], 'Băng ma sát nhỏ nên dễ trượt.', $d);
        $this->fill($s, 'Công thức tính lực đàn hồi: F = k × ___.', [[0, 'Δl']], 'F = k·Δl.', $d);
        $this->fill($s, 'Lực ma sát nghỉ giữ cho vật không bị ___.', [[0, 'trượt']], 'Ma sát nghỉ chống trượt.', $d);
    }

    private function seedKhoaHocThpt11Lop111(): void
    {
        $s = 'khoa-hoc-thpt-11-lop-11-1'; $d = 'trung_binh';
        $this->quiz($s, 'Nguyên tử hi-đrô có bao nhiêu proton?', ['0', '1', '2', '3'], 1, 'Hi-đrô (Z = 1) có 1 proton.', $d);
        $this->quiz($s, 'Hạt nhân nguyên tử hê-li gồm những hạt nào?', ['2 proton', '2 proton, 2 nơtron', '2 nơtron', '2 electron'], 1, 'Hạt nhân He gồm 2 proton và 2 nơtron.', $d);
        $this->quiz($s, 'Điện tích hạt nhân của nguyên tố có Z = 11 là bao nhiêu?', ['11+', '+11', '-11', '0'], 1, 'Điện tích hạt nhân là +11 (đơn vị điện tích nguyên tố).', $d);
        $this->quiz($s, 'Mô hình nguyên tử hiện đại được gọi là mô hình gì?', ['Hành tinh nguyên tử', 'Đám mây electron', 'Bánh pudding', 'Hạt rắn'], 1, 'Mô hình hiện đại là mô hình đám mây electron (cơ học lượng tử).', $d);
        $this->quiz($s, 'Nhà bác học nào đã phát hiện ra electron?', ['Rơ-dơ-pho', 'Tôm-xơn', 'Chát-uých', 'Bo'], 1, 'Tôm-xơn (Thomson) phát hiện ra electron năm 1897.', $d);
        $this->matching($s, 'Nối mỗi hạt với kí hiệu của nó.', [['Proton', 'p'], ['Nơtron', 'n'], ['Electron', 'e']], 'Proton p, nơtron n, electron e.', $d);
        $this->matching($s, 'Nối mỗi hạt với khối lượng tương đối của nó.', [['Proton', '1 đvC'], ['Nơtron', '1 đvC'], ['Electron', '1/1840 đvC']], 'Proton, nơtron ~1 đvC; electron nhẹ hơn 1840 lần.', $d);
        $this->matching($s, 'Nối mỗi nguyên tố với số hiệu nguyên tử Z của nó.', [['Hi-đrô', 'Z = 1'], ['Hê-li', 'Z = 2'], ['Li-ti', 'Z = 3'], ['Be-ri-li', 'Z = 4']], 'H 1, He 2, Li 3, Be 4.', $d);
        $this->matching($s, 'Nối mỗi phát hiện với nhà khoa học của nó.', [['Electron', 'Tôm-xơn'], ['Hạt nhân nguyên tử', 'Rơ-dơ-pho'], ['Proton', 'Rơ-dơ-pho'], ['Nơtron', 'Chát-uých']], 'Tôm-xơn – electron, Rơ-dơ-pho – hạt nhân, Chát-uých – nơtron.', $d);
        $this->matching($s, 'Nối mỗi nguyên tố với số electron của nó (nguyên tử trung hòa).', [['H (Z = 1)', '1 electron'], ['He (Z = 2)', '2 electron'], ['O (Z = 8)', '8 electron'], ['Na (Z = 11)', '11 electron']], 'Số electron = Z trong nguyên tử trung hòa.', $d);
        $this->sortQ($s, 'Kéo mỗi hạt/đối tượng vào nhóm MANG ĐIỆN hoặc KHÔNG MANG ĐIỆN.', [['Hạt alpha', 'MANG ĐIỆN'], ['Ion dương', 'MANG ĐIỆN'], ['Nguyên tử trung hòa', 'KHÔNG MANG ĐIỆN'], ['Nơtron', 'KHÔNG MANG ĐIỆN']], 'Hạt alpha, ion mang điện; nguyên tử trung hòa, nơtron không mang điện.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm KIM LOẠI hoặc PHI KIM.', [['Natri', 'KIM LOẠI'], ['Sắt', 'KIM LOẠI'], ['Ô-xy', 'PHI KIM'], ['Clo', 'PHI KIM']], 'Natri, sắt là kim loại; ô-xy, clo là phi kim.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Nguyên tử H có 1 proton', 'ĐÚNG'], ['Số hiệu Z bằng số proton', 'ĐÚNG'], ['Nơtron mang điện âm', 'SAI'], ['Electron nằm trong hạt nhân', 'SAI']], 'Nơtron không mang điện; electron chuyển động quanh hạt nhân.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm có Z CHẴN hoặc Z LẺ.', [['Hê-li (Z = 2)', 'Z CHẴN'], ['Ô-xy (Z = 8)', 'Z CHẴN'], ['Hi-đrô (Z = 1)', 'Z LẺ'], ['Natri (Z = 11)', 'Z LẺ']], 'He, O có Z chẵn; H, Na có Z lẻ.', $d);
        $this->sortQ($s, 'Kéo mỗi hạt vào nhóm NHẸ HƠN hoặc NẶNG HƠN proton.', [['Electron', 'NHẸ HƠN'], ['Nơtron', 'NẶNG HƠN (một chút)']], 'Electron nhẹ hơn proton 1840 lần; nơtron nặng hơn một chút.', $d);
        $this->fill($s, 'Nguyên tử hi-đrô có ___ proton.', [[0, '1']], 'Hi-đrô có 1 proton.', $d);
        $this->fill($s, 'Hạt nhân hê-li gồm 2 proton và 2 ___.', [[0, 'nơtron']], 'Hạt nhân He: 2 proton, 2 nơtron.', $d);
        $this->fill($s, 'Người phát hiện ra electron là nhà bác học ___.', [[0, 'Tôm-xơn']], 'Tôm-xơn phát hiện electron năm 1897.', $d);
        $this->fill($s, 'Điện tích hạt nhân của nguyên tố có Z = 11 là ___.', [[0, '+11']], 'Điện tích hạt nhân = +Z.', $d);
        $this->fill($s, 'Mô hình nguyên tử hiện đại gọi là mô hình đám ___ electron.', [[0, 'mây']], 'Mô hình đám mây electron.', $d);
    }

    private function seedKhoaHocThpt11Lop112(): void
    {
        $s = 'khoa-hoc-thpt-11-lop-11-2'; $d = 'kho';
        $this->quiz($s, 'Nguyên tử khối trung bình của clo khoảng bao nhiêu?', ['35', '35,5', '36', '37'], 1, 'Clo có nguyên tử khối trung bình 35,5.', $d);
        $this->quiz($s, 'Hai đồng vị ¹²C và ¹⁴C khác nhau ở điểm nào?', ['Số proton', 'Số nơtron', 'Số electron', 'Điện tích'], 1, 'Đồng vị cùng Z, khác số nơtron.', $d);
        $this->quiz($s, 'Lớp electron M (n = 3) chứa tối đa bao nhiêu electron?', ['8', '18', '32', '2'], 1, 'Lớp M chứa tối đa 18 electron (2n²).', $d);
        $this->quiz($s, 'Nguyên tử natri (Z = 11) có sự phân bố electron theo lớp là:', ['2, 8, 1', '2, 8, 2', '2, 7, 2', '2, 8, 8'], 0, 'Na (Z = 11): 2, 8, 1.', $d);
        $this->quiz($s, 'Quy tắc bát tử (octet) phát biểu như thế nào?', ['Lớp ngoài cùng bền khi có 2 electron', 'Lớp ngoài cùng bền vững khi có 8 electron', 'Mọi lớp đều có 8 electron', 'Hạt nhân có 8 proton'], 1, 'Lớp ngoài cùng có 8 electron thì bền vững.', $d);
        $this->matching($s, 'Nối mỗi đồng vị với kí hiệu của nó.', [['Cacbon-12', '¹²C'], ['Cacbon-14', '¹⁴C'], ['Hi-đrô-1', '¹H'], ['Hi-đrô-2 (đơ-teri)', '²H']], 'Cacbon-12 là ¹²C, cacbon-14 là ¹⁴C.', $d);
        $this->matching($s, 'Nối mỗi lớp electron với tên gọi của nó.', [['n = 1', 'Lớp K'], ['n = 2', 'Lớp L'], ['n = 3', 'Lớp M'], ['n = 4', 'Lớp N']], 'n=1 K, n=2 L, n=3 M, n=4 N.', $d);
        $this->matching($s, 'Nối mỗi cặp nguyên tử với quan hệ giữa chúng.', [['¹²C và ¹⁴C', 'Đồng vị của nhau'], ['¹⁶O và ¹⁸O', 'Đồng vị của nhau'], ['¹²C và ¹⁴N', 'Không phải đồng vị']], 'Cùng nguyên tố, khác số khối là đồng vị.', $d);
        $this->matching($s, 'Nối mỗi nguyên tố với số electron lớp ngoài cùng của nó.', [['Natri', '1'], ['Ma-giê', '2'], ['Nhôm', '3'], ['Clo', '7']], 'Na 1, Mg 2, Al 3, Cl 7 electron lớp ngoài cùng.', $d);
        $this->matching($s, 'Nối mỗi đồng vị với ứng dụng của nó.', [['Cacbon-14', 'Xác định tuổi cổ vật'], ['Đơ-teri (²H)', 'Nghiên cứu khoa học'], ['Cô-ban-60', 'Y tế (xạ trị)']], '¹⁴C xác định tuổi cổ vật, ⁶⁰Co dùng trong y tế.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tử vào nhóm ĐỒNG VỊ CỦA NHAU hoặc KHÔNG.', [['³⁵Cl', 'ĐỒNG VỊ'], ['³⁷Cl', 'ĐỒNG VỊ'], ['¹²C', 'KHÔNG PHẢI']], '³⁵Cl và ³⁷Cl là đồng vị của clo.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm theo số electron lớp ngoài cùng.', [['Natri', '1 ELECTRON'], ['Kali', '1 ELECTRON'], ['Clo', '7 ELECTRON'], ['Flo', '7 ELECTRON']], 'Na, K có 1 e lớp ngoài; Cl, F có 7 e.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Đồng vị cùng Z, khác số nơtron', 'ĐÚNG'], ['Lớp M chứa tối đa 18 electron', 'ĐÚNG'], ['¹²C có 6 nơtron', 'ĐÚNG'], ['Số khối A = Z - N', 'SAI']], 'A = Z + N; ¹²C có 6 proton và 6 nơtron.', $d);
        $this->sortQ($s, 'Kéo mỗi hạt nhân vào nhóm có SỐ KHỐI CHẴN hoặc LẺ.', [['¹²C', 'CHẴN'], ['¹⁶O', 'CHẴN'], ['¹H', 'LẺ'], ['³⁵Cl', 'LẺ']], '¹²C, ¹⁶O số khối chẵn; ¹H, ³⁵Cl số khối lẻ.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tử vào nhóm LỚP NGOÀI CÙNG BỀN hoặc KÉM BỀN.', [['Hê-li (2e)', 'BỀN'], ['Ne-on (8e)', 'BỀN'], ['Natri (1e)', 'KÉM BỀN'], ['Clo (7e)', 'KÉM BỀN']], 'Lớp ngoài cùng 2 hoặc 8 electron thì bền.', $d);
        $this->fill($s, 'Nguyên tử khối trung bình của clo là 35,___.', [[0, '5']], 'Clo có nguyên tử khối 35,5.', $d);
        $this->fill($s, 'Đồng vị ¹²C và ¹⁴C khác nhau về số ___.', [[0, 'nơtron']], 'Đồng vị khác nhau số nơtron.', $d);
        $this->fill($s, 'Lớp M (n = 3) chứa tối đa ___ electron.', [[0, '18']], 'Lớp M tối đa 18 electron.', $d);
        $this->fill($s, 'Nguyên tử natri (Z = 11) có ___ electron ở lớp ngoài cùng.', [[0, '1']], 'Na: 2, 8, 1.', $d);
        $this->fill($s, 'Quy tắc bát tử: lớp ngoài cùng bền vững khi có 8 ___.', [[0, 'electron']], '8 electron lớp ngoài cùng thì bền.', $d);
    }

    private function seedKhoaHocThpt11Lop113(): void
    {
        $s = 'khoa-hoc-thpt-11-lop-11-3'; $d = 'trung_binh';
        $this->quiz($s, 'Bảng tuần hoàn các nguyên tố hiện nay có bao nhiêu nguyên tố?', ['100', '112', '118', '120'], 2, 'Bảng tuần hoàn hiện có 118 nguyên tố.', $d);
        $this->quiz($s, 'Nhóm IA trong bảng tuần hoàn gồm các nguyên tố nào?', ['Halogen', 'Kim loại kiềm', 'Khí hiếm', 'Kim loại kiềm thổ'], 1, 'Nhóm IA là các kim loại kiềm (trừ hi-đrô).', $d);
        $this->quiz($s, 'Nhóm VIIA trong bảng tuần hoàn gồm các nguyên tố nào?', ['Kim loại kiềm', 'Halogen', 'Khí hiếm', 'Á kim'], 1, 'Nhóm VIIA là các halogen: F, Cl, Br, I.', $d);
        $this->quiz($s, 'Chu kì 1 của bảng tuần hoàn có bao nhiêu nguyên tố?', ['2', '8', '18', '32'], 0, 'Chu kì 1 chỉ có 2 nguyên tố: H và He.', $d);
        $this->quiz($s, 'Nhóm IIA gồm các nguyên tố được gọi là gì?', ['Kim loại kiềm', 'Kim loại kiềm thổ', 'Halogen', 'Khí hiếm'], 1, 'Nhóm IIA là các kim loại kiềm thổ: Be, Mg, Ca...', $d);
        $this->matching($s, 'Nối mỗi nhóm với một nguyên tố ví dụ của nó.', [['Nhóm IA', 'Natri'], ['Nhóm IIA', 'Canxi'], ['Nhóm VIIA', 'Clo'], ['Nhóm VIIIA', 'Hê-li']], 'IA – Na, IIA – Ca, VIIA – Cl, VIIIA – He.', $d);
        $this->matching($s, 'Nối mỗi chu kì với số nguyên tố trong chu kì đó.', [['Chu kì 1', '2 nguyên tố'], ['Chu kì 2', '8 nguyên tố'], ['Chu kì 3', '8 nguyên tố'], ['Chu kì 4', '18 nguyên tố']], 'Chu kì 1 có 2, chu kì 2 và 3 có 8, chu kì 4 có 18 nguyên tố.', $d);
        $this->matching($s, 'Nối mỗi nguyên tố với ô của nó trong bảng tuần hoàn.', [['Hi-đrô', 'Ô số 1'], ['Hê-li', 'Ô số 2'], ['Li-ti', 'Ô số 3'], ['Ô-xy', 'Ô số 8']], 'Số ô = số hiệu nguyên tử Z.', $d);
        $this->matching($s, 'Nối mỗi nhà khoa học với công trình của ông.', [['Men-đê-lê-ép', 'Bảng tuần hoàn'], ['Rơ-dơ-pho', 'Mô hình hành tinh nguyên tử'], ['Tôm-xơn', 'Phát hiện electron']], 'Men-đê-lê-ép – bảng tuần hoàn, Rơ-dơ-pho – mô hình hành tinh.', $d);
        $this->matching($s, 'Nối mỗi tính chất với nhóm nguyên tố thể hiện rõ nhất.', [['Dẫn điện tốt', 'Kim loại kiềm'], ['Hầu như không phản ứng', 'Khí hiếm'], ['Tạo thành muối', 'Halogen']], 'Kim loại dẫn điện, khí hiếm trơ, halogen tạo muối.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm KIM LOẠI KIỀM, HALOGEN hoặc KHÍ HIẾM.', [['Kali', 'KIM LOẠI KIỀM'], ['Brom', 'HALOGEN'], ['Flo', 'HALOGEN'], ['Ne-on', 'KHÍ HIẾM'], ['Argon', 'KHÍ HIẾM']], 'Kali kiềm, brom và flo halogen, ne-on và argon khí hiếm.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm CHU KÌ 2 hoặc CHU KÌ 3.', [['Li-ti', 'CHU KÌ 2'], ['Ni-tơ', 'CHU KÌ 2'], ['Natri', 'CHU KÌ 3'], ['Lưu huỳnh', 'CHU KÌ 3']], 'Li, N ở chu kì 2; Na, S ở chu kì 3.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Bảng tuần hoàn có 118 nguyên tố', 'ĐÚNG'], ['Chu kì 1 có 2 nguyên tố', 'ĐÚNG'], ['Hê-li thuộc nhóm VIIIA', 'ĐÚNG'], ['Nhóm IA là các halogen', 'SAI']], 'Nhóm IA là kim loại kiềm; halogen là nhóm VIIA.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm NHÓM A hoặc NHÓM B.', [['Natri', 'NHÓM A'], ['Clo', 'NHÓM A'], ['Sắt', 'NHÓM B'], ['Đồng', 'NHÓM B']], 'Na, Cl thuộc nhóm A; Fe, Cu thuộc nhóm B.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm KIM LOẠI, PHI KIM hoặc KHÍ HIẾM.', [['Natri', 'KIM LOẠI'], ['Sắt', 'KIM LOẠI'], ['Ô-xy', 'PHI KIM'], ['Lưu huỳnh', 'PHI KIM'], ['Hê-li', 'KHÍ HIẾM'], ['Ne-on', 'KHÍ HIẾM']], 'Na, Fe kim loại; O, S phi kim; He, Ne khí hiếm.', $d);
        $this->fill($s, 'Bảng tuần hoàn hiện nay có ___ nguyên tố.', [[0, '118']], 'Bảng tuần hoàn có 118 nguyên tố.', $d);
        $this->fill($s, 'Nhóm VIIA gồm các nguyên tố ___.', [[0, 'halogen']], 'Nhóm VIIA là halogen.', $d);
        $this->fill($s, 'Chu kì 1 chỉ có 2 nguyên tố là hi-đrô và ___.', [[0, 'hê-li']], 'Chu kì 1: H và He.', $d);
        $this->fill($s, 'Nhóm IIA gồm các kim loại kiềm ___.', [[0, 'thổ']], 'Nhóm IIA là kim loại kiềm thổ.', $d);
        $this->fill($s, 'Nguyên tố ở ô số 11 trong bảng tuần hoàn là ___.', [[0, 'natri']], 'Ô 11 là natri (Na).', $d);
    }

    private function seedKhoaHocThpt11Lop114(): void
    {
        $s = 'khoa-hoc-thpt-11-lop-11-4'; $d = 'kho';
        $this->quiz($s, 'Trong một chu kì, đi từ trái sang phải, độ âm điện biến đổi thế nào?', ['Giảm dần', 'Tăng dần', 'Không đổi', 'Tăng rồi giảm'], 1, 'Độ âm điện tăng dần từ trái sang phải trong chu kì.', $d);
        $this->quiz($s, 'Nguyên tố nào có tính phi kim mạnh nhất trong bảng tuần hoàn?', ['Ô-xy', 'Clo', 'Flo', 'Lưu huỳnh'], 2, 'Flo có tính phi kim mạnh nhất, độ âm điện lớn nhất.', $d);
        $this->quiz($s, 'Trong nhóm IA, kim loại nào hoạt động hóa học mạnh nhất?', ['Li-ti', 'Natri', 'Kali', 'Xê-si'], 3, 'Đi từ trên xuống dưới, tính kim loại tăng; xê-si hoạt động mạnh nhất (trong các kim loại bền).', $d);
        $this->quiz($s, 'So với nguyên tử, bán kính của ion dương (cation) như thế nào?', ['Lớn hơn', 'Nhỏ hơn', 'Bằng nhau', 'Gấp đôi'], 1, 'Cation mất electron nên bán kính nhỏ hơn nguyên tử.', $d);
        $this->quiz($s, 'Trong một chu kì, tính axit của oxit cao nhất biến đổi thế nào từ trái sang phải?', ['Giảm dần', 'Tăng dần', 'Không đổi', 'Mất hẳn'], 1, 'Tính axit của oxit cao nhất tăng dần từ trái sang phải.', $d);
        $this->matching($s, 'Nối mỗi tính chất với xu hướng biến đổi của nó trong chu kì (trái sang phải).', [['Bán kính nguyên tử', 'Giảm dần'], ['Độ âm điện', 'Tăng dần'], ['Tính kim loại', 'Giảm dần'], ['Tính phi kim', 'Tăng dần']], 'Trong chu kì: bán kính giảm, độ âm điện tăng, kim loại giảm, phi kim tăng.', $d);
        $this->matching($s, 'Nối mỗi tính chất với xu hướng biến đổi của nó trong nhóm A (trên xuống dưới).', [['Bán kính nguyên tử', 'Tăng dần'], ['Tính kim loại', 'Tăng dần'], ['Độ âm điện', 'Giảm dần']], 'Trong nhóm A: bán kính tăng, kim loại tăng, độ âm điện giảm.', $d);
        $this->matching($s, 'Nối mỗi nguyên tố với đặc điểm nổi bật của nó.', [['Flo', 'Phi kim mạnh nhất'], ['Xê-si', 'Kim loại hoạt động mạnh'], ['Hê-li', 'Khí trơ'], ['Sắt', 'Kim loại chuyển tiếp']], 'Flo phi kim mạnh nhất, xê-si hoạt động mạnh, hê-li trơ.', $d);
        $this->matching($s, 'Nối mỗi cặp nguyên tố với nguyên tố có độ âm điện lớn hơn.', [['Flo – Clo', 'Flo'], ['Ô-xy – Lưu huỳnh', 'Ô-xy'], ['Natri – Clo', 'Clo']], 'Flo > Clo, ô-xy > lưu huỳnh, clo > natri về độ âm điện.', $d);
        $this->matching($s, 'Nối mỗi ion với điện tích của nó.', [['Na+', 'Điện tích +1'], ['Ca2+', 'Điện tích +2'], ['Cl-', 'Điện tích -1'], ['O2-', 'Điện tích -2']], 'Na+ (+1), Ca2+ (+2), Cl- (-1), O2- (-2).', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm KIM LOẠI hoặc PHI KIM.', [['Kali', 'KIM LOẠI'], ['Can-xi', 'KIM LOẠI'], ['Phốt-pho', 'PHI KIM'], ['Lưu huỳnh', 'PHI KIM']], 'Kali, can-xi là kim loại; phốt-pho, lưu huỳnh là phi kim.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Độ âm điện tăng từ trái sang phải', 'ĐÚNG'], ['Bán kính tăng từ trên xuống dưới', 'ĐÚNG'], ['Flo là phi kim mạnh nhất', 'ĐÚNG'], ['Kim loại kiềm ở nhóm VIIA', 'SAI']], 'Kim loại kiềm ở nhóm IA; halogen ở nhóm VIIA.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố chu kì 3 vào nhóm BÁN KÍNH LỚN hoặc NHỎ.', [['Natri', 'BÁN KÍNH LỚN'], ['Ma-giê', 'BÁN KÍNH LỚN'], ['Clo', 'BÁN KÍNH NHỎ'], ['Argon', 'BÁN KÍNH NHỎ']], 'Trong chu kì 3, bán kính giảm từ Na đến Ar.', $d);
        $this->sortQ($s, 'Kéo mỗi ion vào nhóm CATION hoặc ANION.', [['Na+', 'CATION'], ['Ca2+', 'CATION'], ['Cl-', 'ANION'], ['O2-', 'ANION']], 'Ion dương là cation, ion âm là anion.', $d);
        $this->sortQ($s, 'Kéo mỗi nguyên tố vào nhóm TÍNH KIM LOẠI MẠNH hoặc YẾU.', [['Kali', 'MẠNH'], ['Natri', 'MẠNH'], ['Clo', 'YẾU'], ['Flo', 'YẾU']], 'Kali, natri kim loại mạnh; clo, flo phi kim mạnh.', $d);
        $this->fill($s, 'Trong một chu kì, độ âm điện tăng dần từ trái sang ___.', [[0, 'phải']], 'Độ âm điện tăng từ trái sang phải.', $d);
        $this->fill($s, 'Nguyên tố có tính phi kim mạnh nhất là ___.', [[0, 'flo']], 'Flo phi kim mạnh nhất.', $d);
        $this->fill($s, 'Trong nhóm IA, xê-si là kim loại hoạt động hóa học rất ___.', [[0, 'mạnh']], 'Xê-si hoạt động hóa học rất mạnh.', $d);
        $this->fill($s, 'Bán kính của ion Na+ ___ hơn bán kính nguyên tử Na.', [[0, 'nhỏ']], 'Cation nhỏ hơn nguyên tử.', $d);
        $this->fill($s, 'Tính axit của oxit cao nhất tăng dần từ trái sang phải trong một chu ___.', [[0, 'kì']], 'Trong chu kì, tính axit oxit cao nhất tăng dần.', $d);
    }

    private function seedKhoaHocThpt12Lop121(): void
    {
        $s = 'khoa-hoc-thpt-12-lop-12-1'; $d = 'trung_binh';
        $this->quiz($s, 'Đường trong phân tử ADN là loại đường nào?', ['Ribôzơ', 'Đêôxiribôzơ', 'Glu-côzơ', 'Fruc-tôzơ'], 1, 'Đường trong ADN là đêôxiribôzơ.', $d);
        $this->quiz($s, 'Nhóm phốt phát trong phân tử ADN có vai trò gì?', ['Mang thông tin', 'Tạo bộ khung của mạch', 'Liên kết hiđro', 'Dự trữ năng lượng'], 1, 'Đường và phốt phát tạo bộ khung của mạch ADN.', $d);
        $this->quiz($s, 'Chiều dài của một vòng xoắn ADN là bao nhiêu?', ['3,4 nm', '34 nm', '0,34 nm', '340 nm'], 0, 'Mỗi vòng xoắn ADN dài 3,4 nm.', $d);
        $this->quiz($s, 'Mỗi vòng xoắn của phân tử ADN có bao nhiêu cặp nuclêôtit?', ['5', '10', '20', '34'], 1, 'Mỗi vòng xoắn có 10 cặp nuclêôtit.', $d);
        $this->quiz($s, 'ADN trong tế bào nhân thực nằm chủ yếu ở đâu?', ['Tế bào chất', 'Nhân tế bào', 'Màng tế bào', 'Ti thể'], 1, 'ADN nằm chủ yếu trong nhân tế bào.', $d);
        $this->matching($s, 'Nối mỗi bazơ nitơ với nhóm của nó.', [['Ađênin', 'Bazơ purin'], ['Timin', 'Bazơ pirimiđin'], ['Guanin', 'Bazơ purin'], ['Xitôzin', 'Bazơ pirimiđin']], 'A, G là purin; T, X là pirimiđin.', $d);
        $this->matching($s, 'Nối mỗi thành phần với vị trí của nó trong ADN.', [['Đường đêôxiribôzơ', 'Bộ khung'], ['Nhóm phốt phát', 'Bộ khung'], ['Bazơ nitơ', 'Bên trong']], 'Đường và phốt phát tạo khung; bazơ ở bên trong.', $d);
        $this->matching($s, 'Nối mỗi mạch đơn với mạch bổ sung của nó.', [['A – T – G', 'T – A – X'], ['X – G – A', 'G – X – T']], 'Theo nguyên tắc bổ sung: A–T, G–X.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với phân tử tương ứng.', [['Xoắn kép', 'ADN'], ['Một mạch đơn', 'ARN'], ['Có bazơ U', 'ARN'], ['Có bazơ T', 'ADN']], 'ADN xoắn kép có T; ARN một mạch có U.', $d);
        $this->matching($s, 'Nối mỗi nhà khoa học với đóng góp của ông.', [['Watson – Crick', 'Mô hình ADN'], ['Menđen', 'Quy luật di truyền'], ['Mooc-gan', 'Di truyền liên kết']], 'Watson – Crick: mô hình ADN; Menđen: quy luật di truyền.', $d);
        $this->sortQ($s, 'Kéo mỗi cặp bazơ vào nhóm LIÊN KẾT BẰNG 2 hoặc 3 LIÊN KẾT HIĐRO.', [['Ađênin – Timin', '2 LIÊN KẾT'], ['Timin – Ađênin', '2 LIÊN KẾT'], ['Guanin – Xitôzin', '3 LIÊN KẾT'], ['Xitôzin – Guanin', '3 LIÊN KẾT']], 'A–T liên kết bằng 2, G–X bằng 3 liên kết hiđro.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['ADN có 4 loại nuclêôtit', 'ĐÚNG'], ['ADN nằm trong nhân tế bào', 'ĐÚNG'], ['Ađênin liên kết với guanin', 'SAI'], ['Đường trong ADN là ribôzơ', 'SAI']], 'A liên kết với T; đường trong ADN là đêôxiribôzơ.', $d);
        $this->sortQ($s, 'Kéo mỗi thành phần vào nhóm THUỘC ADN, THUỘC ARN hoặc CẢ HAI.', [['Timin (T)', 'THUỘC ADN'], ['Uraxin (U)', 'THUỘC ARN'], ['Ađênin (A)', 'CẢ HAI'], ['Guanin (G)', 'CẢ HAI']], 'T chỉ có ở ADN, U chỉ có ở ARN; A, G có ở cả hai.', $d);
        $this->sortQ($s, 'Kéo mỗi mạch vào nhóm là MẠCH BỔ SUNG ĐÚNG của A – T – G – X – A.', [['T – A – X – G – T', 'ĐÚNG'], ['A – T – G – X – A', 'SAI'], ['T – A – X – X – T', 'SAI']], 'Mạch bổ sung của A–T–G–X–A là T–A–X–G–T.', $d);
        $this->sortQ($s, 'Kéo mỗi bazơ vào nhóm BAZƠ PURIN hoặc PIRI MIĐIN.', [['Ađênin', 'PURIN'], ['Guanin', 'PURIN'], ['Timin', 'PIRI MIĐIN'], ['Xitôzin', 'PIRI MIĐIN']], 'A, G là purin; T, X là pirimiđin.', $d);
        $this->fill($s, 'Đường trong phân tử ADN là đường đêôxi ___.', [[0, 'ribôzơ']], 'Đường đêôxiribôzơ trong ADN.', $d);
        $this->fill($s, 'Mỗi vòng xoắn của ADN dài 3,4 ___.', [[0, 'nm']], 'Vòng xoắn ADN dài 3,4 nm.', $d);
        $this->fill($s, 'Mỗi vòng xoắn ADN có 10 cặp nuclê ___.', [[0, 'ôtit']], 'Mỗi vòng xoắn có 10 cặp nuclêôtit.', $d);
        $this->fill($s, 'ADN trong tế bào nhân thực nằm chủ yếu ở ___ tế bào.', [[0, 'nhân']], 'ADN nằm trong nhân tế bào.', $d);
        $this->fill($s, 'Ađênin và guanin thuộc nhóm bazơ ___.', [[0, 'purin']], 'A và G là bazơ purin.', $d);
    }

    private function seedKhoaHocThpt12Lop122(): void
    {
        $s = 'khoa-hoc-thpt-12-lop-12-2'; $d = 'kho';
        $this->quiz($s, 'Có bao nhiêu bộ ba mã hóa axit amin?', ['64', '61', '20', '3'], 1, '64 bộ ba trừ 3 bộ ba kết thúc còn 61 bộ ba mã hóa axit amin.', $d);
        $this->quiz($s, 'Quá trình tổng hợp ARN từ khuôn ADN được gọi là gì?', ['Dịch mã', 'Phiên mã', 'Nhân đôi', 'Tái bản'], 1, 'Phiên mã là quá trình tổng hợp ARN từ ADN.', $d);
        $this->quiz($s, 'Quá trình tổng hợp prôtêin từ ARN được gọi là gì?', ['Phiên mã', 'Dịch mã', 'Nhân đôi', 'Phân giải'], 1, 'Dịch mã là quá trình tổng hợp prôtêin (chuỗi pôlipeptit).', $d);
        $this->quiz($s, 'Bộ ba nào mã hóa axit amin mê-ti-ô-nin (mở đầu)?', ['UUU', 'AUG', 'UAA', 'GGG'], 1, 'AUG vừa là mã mở đầu vừa mã hóa mê-ti-ô-nin.', $d);
        $this->quiz($s, 'Mã di truyền có tính phổ biến nghĩa là gì?', ['Mỗi loài một mã', 'Hầu hết sinh vật dùng chung mã', 'Mã thay đổi', 'Mã chỉ có ở người'], 1, 'Tính phổ biến: các loài sinh vật dùng chung mã di truyền.', $d);
        $this->matching($s, 'Nối mỗi bộ ba với axit amin hoặc vai trò của nó.', [['AUG', 'Mê-ti-ô-nin (mở đầu)'], ['UUU', 'Phê-nyl-a-la-nin'], ['UAA', 'Kết thúc']], 'AUG mở đầu, UUU mã hóa phê-nyl-a-la-nin, UAA kết thúc.', $d);
        $this->matching($s, 'Nối mỗi quá trình với nơi diễn ra của nó (tế bào nhân thực).', [['Nhân đôi ADN', 'Nhân'], ['Phiên mã', 'Nhân'], ['Dịch mã', 'Tế bào chất']], 'Nhân đôi và phiên mã ở nhân; dịch mã ở tế bào chất.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa của nó.', [['Côđon', 'Bộ ba trên mARN'], ['Anticôđon', 'Bộ ba trên tARN'], ['Pôlixôm', 'Nhiều ribôxôm cùng dịch mã']], 'Côđon trên mARN, anticôđon trên tARN.', $d);
        $this->matching($s, 'Nối mỗi loại ARN với chức năng của nó.', [['mARN', 'Khuôn cho dịch mã'], ['tARN', 'Vận chuyển axit amin'], ['rARN', 'Cấu tạo ribôxôm']], 'mARN làm khuôn, tARN vận chuyển, rARN cấu tạo ribôxôm.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với tính chất của mã di truyền.', [['Mã bộ ba', 'Tính đặc hiệu'], ['Nhiều bộ ba cùng một axit amin', 'Tính thoái hóa'], ['Các loài dùng chung', 'Tính phổ biến']], 'Mã di truyền đặc hiệu, thoái hóa và phổ biến.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm CỦA GEN hoặc CỦA NHIỄM SẮC THỂ.', [['Đoạn ADN mang thông tin', 'CỦA GEN'], ['Cấu tạo từ ADN và prôtêin', 'CỦA NHIỄM SẮC THỂ'], ['Chứa nhiều gen', 'CỦA NHIỄM SẮC THỂ']], 'Gen là đoạn ADN; NST chứa nhiều gen.', $d);
        $this->sortQ($s, 'Kéo mỗi bộ ba vào nhóm MÃ MỞ ĐẦU, MÃ KẾT THÚC hoặc MÃ HÓA AXIT AMIN.', [['AUG', 'MÃ MỞ ĐẦU'], ['UAA', 'MÃ KẾT THÚC'], ['UAG', 'MÃ KẾT THÚC'], ['UGA', 'MÃ KẾT THÚC'], ['UUU', 'MÃ HÓA AXIT AMIN']], 'AUG mở đầu; UAA, UAG, UGA kết thúc.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Có 64 bộ ba mã di truyền', 'ĐÚNG'], ['AUG là mã mở đầu', 'ĐÚNG'], ['Mã di truyền được đọc chồng gối lên nhau', 'SAI'], ['Mỗi bộ ba mã hóa một axit amin', 'ĐÚNG']], 'Mã di truyền đọc liên tục, không chồng gối.', $d);
        $this->sortQ($s, 'Kéo mỗi quá trình vào nhóm DIỄN RA Ở NHÂN hoặc Ở TẾ BÀO CHẤT (tế bào nhân thực).', [['Nhân đôi ADN', 'Ở NHÂN'], ['Phiên mã', 'Ở NHÂN'], ['Dịch mã', 'Ở TẾ BÀO CHẤT']], 'Nhân đôi và phiên mã ở nhân; dịch mã ở tế bào chất.', $d);
        $this->sortQ($s, 'Kéo mỗi bộ ba vào nhóm MÃ HÓA AXIT AMIN hoặc KHÔNG MÃ HÓA.', [['UUU', 'MÃ HÓA'], ['AUG', 'MÃ HÓA'], ['UAA', 'KHÔNG MÃ HÓA'], ['UGA', 'KHÔNG MÃ HÓA']], 'UAA và UGA là bộ ba kết thúc, không mã hóa axit amin.', $d);
        $this->fill($s, 'Có 61 bộ ba mã hóa axit ___.', [[0, 'amin']], '61 bộ ba mã hóa axit amin.', $d);
        $this->fill($s, 'Quá trình tổng hợp ARN từ ADN gọi là phiên ___.', [[0, 'mã']], 'Phiên mã tổng hợp ARN.', $d);
        $this->fill($s, 'Quá trình tổng hợp prôtêin gọi là dịch ___.', [[0, 'mã']], 'Dịch mã tổng hợp prôtêin.', $d);
        $this->fill($s, 'Mã di truyền có tính phổ biến nghĩa là hầu hết sinh vật dùng ___.', [[0, 'chung']], 'Các loài dùng chung mã di truyền.', $d);
        $this->fill($s, 'Bộ ba đối mã trên tARN gọi là anti ___.', [[0, 'côđon']], 'Anticôđon trên tARN.', $d);
    }

    private function seedKhoaHocThpt12Lop123(): void
    {
        $s = 'khoa-hoc-thpt-12-lop-12-3'; $d = 'trung_binh';
        $this->quiz($s, 'Cơ thể có kiểu gen AA được gọi là gì?', ['Dị hợp', 'Đồng hợp trội', 'Đồng hợp lặn', 'Thể ba'], 1, 'AA là đồng hợp trội.', $d);
        $this->quiz($s, 'Phép lai AA × aa cho đời con có kiểu gen như thế nào?', ['100% AA', '100% Aa', '100% aa', '50% Aa, 50% aa'], 1, 'AA × aa cho 100% Aa.', $d);
        $this->quiz($s, 'Tính trạng lặn chỉ biểu hiện khi cơ thể có kiểu gen nào?', ['AA', 'Aa', 'aa', 'AA và Aa'], 2, 'Tính trạng lặn chỉ biểu hiện ở thể đồng hợp lặn aa.', $d);
        $this->quiz($s, 'Ai là người đặt nền móng cho di truyền học?', ['Đác-uyn', 'Menđen', 'Mooc-gan', 'Watson'], 1, 'Menđen đặt nền móng di truyền học qua thí nghiệm đậu Hà Lan.', $d);
        $this->quiz($s, 'Phép lai phân tích được dùng để làm gì?', ['Tạo giống mới', 'Xác định kiểu gen của cá thể', 'Tăng năng suất', 'Gây đột biến'], 1, 'Lai phân tích xác định kiểu gen của cá thể mang tính trạng trội.', $d);
        $this->matching($s, 'Nối mỗi phép lai với tỉ lệ kiểu gen ở đời con.', [['AA × AA', '100% AA'], ['Aa × Aa', '1 AA : 2 Aa : 1 aa'], ['Aa × aa', '1 Aa : 1 aa'], ['aa × aa', '100% aa']], 'Aa × Aa cho 1:2:1; Aa × aa cho 1:1.', $d);
        $this->matching($s, 'Nối mỗi kiểu gen với loại giao tử nó tạo ra.', [['AA', 'Giao tử A'], ['aa', 'Giao tử a'], ['Aa', 'Giao tử A và a']], 'AA tạo A, aa tạo a, Aa tạo A và a.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ di truyền với định nghĩa của nó.', [['Alen', 'Trạng thái khác nhau của gen'], ['Lôcut', 'Vị trí của gen trên NST'], ['Kiểu hình', 'Biểu hiện ra bên ngoài']], 'Alen là trạng thái của gen; lôcut là vị trí gen.', $d);
        $this->matching($s, 'Nối mỗi tính trạng đậu Hà Lan với tính trội – lặn của nó.', [['Hạt vàng', 'Trội'], ['Hạt xanh', 'Lặn'], ['Thân cao', 'Trội'], ['Thân thấp', 'Lặn']], 'Menđen: hạt vàng trội, hạt xanh lặn; thân cao trội, thân thấp lặn.', $d);
        $this->matching($s, 'Nối mỗi phép lai với kết quả F1 của nó.', [['AA × aa', '100% trội'], ['aa × aa', '100% lặn'], ['AA × AA', '100% trội']], 'AA × aa cho F1 toàn trội.', $d);
        $this->sortQ($s, 'Kéo mỗi kiểu gen vào nhóm ĐỒNG HỢP hoặc DỊ HỢP.', [['AA', 'ĐỒNG HỢP'], ['aa', 'ĐỒNG HỢP'], ['Aa', 'DỊ HỢP'], ['Bb', 'DỊ HỢP']], 'AA, aa đồng hợp; Aa, Bb dị hợp.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Aa × Aa cho tỉ lệ 3 trội : 1 lặn', 'ĐÚNG'], ['Menđen thí nghiệm trên đậu Hà Lan', 'ĐÚNG'], ['aa là thể dị hợp', 'SAI'], ['Tính trạng trội luôn biểu hiện khi có alen trội', 'ĐÚNG']], 'aa là đồng hợp lặn.', $d);
        $this->sortQ($s, 'Kéo mỗi phép lai vào nhóm cho tỉ lệ 3:1, 1:1 hoặc KHÁC.', [['Aa × Aa', '3:1'], ['Aa × aa', '1:1'], ['AA × aa', 'KHÁC']], 'Aa×Aa cho 3:1, Aa×aa cho 1:1, AA×aa cho toàn trội.', $d);
        $this->sortQ($s, 'Kéo mỗi tính trạng của đậu Hà Lan vào nhóm TRỘI hoặc LẶN (theo Menđen).', [['Hoa đỏ', 'TRỘI'], ['Hạt vàng', 'TRỘI'], ['Hoa trắng', 'LẶN'], ['Hạt xanh', 'LẶN']], 'Hoa đỏ, hạt vàng trội; hoa trắng, hạt xanh lặn.', $d);
        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm KIỂU GEN hoặc KIỂU HÌNH.', [['Aa', 'KIỂU GEN'], ['AA', 'KIỂU GEN'], ['Thân cao', 'KIỂU HÌNH'], ['Hạt vàng', 'KIỂU HÌNH']], 'Aa, AA là kiểu gen; thân cao, hạt vàng là kiểu hình.', $d);
        $this->fill($s, 'Kiểu gen AA được gọi là đồng hợp ___.', [[0, 'trội']], 'AA là đồng hợp trội.', $d);
        $this->fill($s, 'Phép lai AA × aa cho đời con 100% ___.', [[0, 'Aa']], 'AA × aa cho toàn Aa.', $d);
        $this->fill($s, 'Tính trạng lặn chỉ biểu hiện ở thể đồng hợp ___.', [[0, 'lặn']], 'Lặn chỉ biểu hiện ở aa.', $d);
        $this->fill($s, 'Phép lai phân tích giúp xác định ___ gen của cá thể.', [[0, 'kiểu']], 'Lai phân tích xác định kiểu gen.', $d);
        $this->fill($s, 'Người đặt nền móng cho di truyền học là ___.', [[0, 'Menđen']], 'Menđen là cha đẻ di truyền học.', $d);
    }

    private function seedKhoaHocThpt12Lop124(): void
    {
        $s = 'khoa-hoc-thpt-12-lop-12-4'; $d = 'kho';
        $this->quiz($s, 'Cơ thể có kiểu gen AABB giảm phân tạo ra bao nhiêu loại giao tử?', ['1', '2', '4', '8'], 0, 'AABB chỉ tạo 1 loại giao tử AB.', $d);
        $this->quiz($s, 'Phép lai AaBb × aabb (lai phân tích) cho tỉ lệ kiểu hình ở đời con là:', ['9:3:3:1', '1:1:1:1', '3:1', '1:1'], 1, 'AaBb × aabb cho 1:1:1:1.', $d);
        $this->quiz($s, 'Biến dị tổ hợp là gì?', ['Đột biến gen', 'Sự tổ hợp lại các tính trạng của bố mẹ ở đời con', 'Thường biến', 'Đột biến NST'], 1, 'Biến dị tổ hợp là tổ hợp lại tính trạng của P ở đời con.', $d);
        $this->quiz($s, 'Điều kiện nghiệm đúng của quy luật phân li độc lập là:', ['Các gen trên cùng một NST', 'Các gen trên các cặp NST tương đồng khác nhau', 'Các gen liên kết', 'Gen trong tế bào chất'], 1, 'Các gen phải nằm trên các cặp NST khác nhau.', $d);
        $this->quiz($s, 'Tỉ lệ kiểu hình 9:3:3:1 gồm bao nhiêu tổ hợp giao tử?', ['9', '12', '16', '4'], 2, '9:3:3:1 = 16 tổ hợp (4 × 4).', $d);
        $this->matching($s, 'Nối mỗi phép lai với số loại kiểu gen ở đời con.', [['AaBb × AaBb', '9 loại'], ['AaBb × aabb', '4 loại'], ['AABB × aabb', '1 loại']], 'AaBb×AaBb cho 9 kiểu gen; lai phân tích cho 4.', $d);
        $this->matching($s, 'Nối mỗi tỉ lệ kiểu gen với phép lai tương ứng.', [['1:2:1', 'Aa × Aa'], ['1:1', 'Aa × aa'], ['9:3:3:1 (kiểu hình)', 'AaBb × AaBb']], '1:2:1 là Aa×Aa, 1:1 là Aa×aa.', $d);
        $this->matching($s, 'Nối mỗi cơ thể với số loại giao tử nó tạo ra.', [['AABB', '1 loại'], ['AaBB', '2 loại'], ['AaBb', '4 loại']], 'Số loại giao tử = 2^(số cặp dị hợp).', $d);
        $this->matching($s, 'Nối mỗi quy luật với phạm vi của nó.', [['Phân li', 'Một cặp tính trạng'], ['Phân li độc lập', 'Hai cặp tính trạng'], ['Liên kết gen', 'Các gen cùng NST']], 'Phân li: một cặp; phân li độc lập: hai cặp; liên kết: cùng NST.', $d);
        $this->matching($s, 'Nối mỗi phép lai với loại của nó.', [['Aa × Aa', 'Lai một cặp tính trạng'], ['AaBb × AaBb', 'Lai hai cặp tính trạng'], ['AaBb × aabb', 'Lai phân tích']], 'AaBb × aabb là lai phân tích hai cặp tính trạng.', $d);
        $this->sortQ($s, 'Kéo mỗi kiểu gen vào nhóm tạo ra 1, 2 hoặc 4 LOẠI GIAO TỬ.', [['AABB', '1 LOẠI'], ['AaBB', '2 LOẠI'], ['AABb', '2 LOẠI'], ['AaBb', '4 LOẠI']], 'Số giao tử = 2^(số cặp dị hợp).', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['AaBb tạo 4 loại giao tử', 'ĐÚNG'], ['Tỉ lệ 9:3:3:1 có 16 tổ hợp', 'ĐÚNG'], ['Biến dị tổ hợp làm tăng đa dạng', 'ĐÚNG'], ['Phân li độc lập cần các gen cùng NST', 'SAI']], 'Phân li độc lập cần gen trên các NST khác nhau.', $d);
        $this->sortQ($s, 'Kéo mỗi phép lai hai cặp tính trạng vào nhóm theo SỐ KIỂU HÌNH ở đời con.', [['AABB × aabb', '1 KIỂU HÌNH'], ['AaBb × aabb', '4 KIỂU HÌNH'], ['AaBb × AaBb', '4 KIỂU HÌNH']], 'AABB×aabb cho 1 kiểu hình; hai phép còn lại cho 4.', $d);
        $this->sortQ($s, 'Kéo mỗi ví dụ vào nhóm BIẾN DỊ TỔ HỢP hoặc KHÔNG PHẢI.', [['Con khác bố mẹ về tính trạng', 'BIẾN DỊ TỔ HỢP'], ['Xuất hiện kiểu hình mới', 'BIẾN DỊ TỔ HỢP'], ['Con giống hệt mẹ (vô tính)', 'KHÔNG PHẢI']], 'Biến dị tổ hợp tạo kiểu hình mới khác bố mẹ.', $d);
        $this->sortQ($s, 'Kéo mỗi trường hợp vào nhóm GEN TRÊN CÙNG NST hoặc KHÁC NST.', [['Di truyền liên kết', 'CÙNG NST'], ['Phân li độc lập', 'KHÁC NST']], 'Liên kết: cùng NST; phân li độc lập: khác NST.', $d);
        $this->fill($s, 'Cơ thể có kiểu gen AABB giảm phân tạo ___ loại giao tử.', [[0, '1']], 'AABB chỉ tạo giao tử AB.', $d);
        $this->fill($s, 'Tỉ lệ 9:3:3:1 gồm ___ tổ hợp giao tử.', [[0, '16']], '9+3+3+1 = 16 tổ hợp.', $d);
        $this->fill($s, 'Biến dị tổ hợp làm tăng tính đa ___ của sinh giới.', [[0, 'dạng']], 'Biến dị tổ hợp tăng đa dạng.', $d);
        $this->fill($s, 'Các gen phân li độc lập khi nằm trên các cặp NST ___ nhau.', [[0, 'khác']], 'Gen trên các NST khác nhau phân li độc lập.', $d);
        $this->fill($s, 'Phép lai AaBb × aabb được gọi là lai phân ___.', [[0, 'tích']], 'Đó là lai phân tích.', $d);
    }

    private function seedLsNuocVanLang(): void
    {
        $s = 'ls-nuoc-van-lang'; $d = 'de';
        $this->quiz($s, 'Nước Văn Lang ra đời vào khoảng thời gian nào?', ['Thế kỉ VII TCN', 'Thế kỉ III TCN', 'Thế kỉ I', 'Thế kỉ X'], 0, 'Nước Văn Lang ra đời khoảng thế kỉ VII TCN.', $d);
        $this->quiz($s, 'Nước Văn Lang được chia thành bao nhiêu bộ?', ['10', '12', '15', '20'], 2, 'Nước Văn Lang chia thành 15 bộ.', $d);
        $this->quiz($s, 'Kinh đô của nước Văn Lang nay thuộc tỉnh nào?', ['Hà Nội', 'Phú Thọ', 'Vĩnh Phúc', 'Hòa Bình'], 1, 'Kinh đô Phong Châu nay thuộc tỉnh Phú Thọ.', $d);
        $this->quiz($s, 'Người Lạc Việt sống chủ yếu bằng nghề gì?', ['Buôn bán', 'Trồng lúa nước', 'Chăn nuôi', 'Đánh cá biển'], 1, 'Người Lạc Việt trồng lúa nước là chính.', $d);
        $this->quiz($s, 'Trống đồng Đông Sơn là di sản văn hóa của thời kì nào?', ['Nhà Lý', 'Thời Hùng Vương', 'Nhà Nguyễn', 'Thời Pháp thuộc'], 1, 'Trống đồng Đông Sơn là di sản thời Hùng Vương.', $d);
        $this->matching($s, 'Nối mỗi tên gọi với ý nghĩa lịch sử của nó.', [['Văn Lang', 'Tên nước đầu tiên'], ['Hùng Vương', 'Vua nước Văn Lang'], ['Lạc Việt', 'Tên cộng đồng cư dân']], 'Văn Lang là nước đầu tiên, Hùng Vương là vua, Lạc Việt là cư dân.', $d);
        $this->matching($s, 'Nối mỗi di tích với địa phương hiện nay của nó.', [['Đền Hùng', 'Phú Thọ'], ['Trống đồng Đông Sơn', 'Thanh Hóa'], ['Thành Cổ Loa', 'Hà Nội']], 'Đền Hùng ở Phú Thọ, trống đồng Đông Sơn ở Thanh Hóa.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với công lao của ông.', [['Hùng Vương', 'Dựng nước Văn Lang'], ['Lạc Long Quân', 'Nhân vật truyền thuyết'], ['Âu Cơ', 'Nhân vật truyền thuyết']], 'Hùng Vương dựng nước; Lạc Long Quân, Âu Cơ trong truyền thuyết.', $d);
        $this->matching($s, 'Nối mỗi nghề nghiệp với mô tả của nó thời Văn Lang.', [['Trồng lúa', 'Nghề nông nghiệp chính'], ['Đúc đồng', 'Nghề thủ công'], ['Đánh cá', 'Nghề ven sông biển']], 'Trồng lúa là chính, đúc đồng phát triển, đánh cá ven sông.', $d);
        $this->matching($s, 'Nối mỗi phong tục với mô tả của nó.', [['Ăn trầu', 'Phong tục lâu đời'], ['Xăm mình', 'Phong tục cổ'], ['Nhuộm răng đen', 'Phong tục cổ']], 'Ăn trầu, xăm mình, nhuộm răng là phong tục thời Hùng Vương.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm THUỘC NƯỚC VĂN LANG hoặc KHÔNG THUỘC.', [['Hùng Vương', 'THUỘC VĂN LANG'], ['15 bộ', 'THUỘC VĂN LANG'], ['An Dương Vương', 'KHÔNG THUỘC'], ['Nhà Đinh', 'KHÔNG THUỘC']], 'Hùng Vương và 15 bộ thuộc Văn Lang; An Dương Vương, nhà Đinh thì không.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm THỜI HÙNG VƯƠNG hoặc THỜI SAU.', [['Lạc Long Quân', 'THỜI HÙNG VƯƠNG'], ['Âu Cơ', 'THỜI HÙNG VƯƠNG'], ['Lý Thường Kiệt', 'THỜI SAU'], ['Nguyễn Trãi', 'THỜI SAU']], 'Lạc Long Quân, Âu Cơ thời Hùng Vương; Lý Thường Kiệt, Nguyễn Trãi thời sau.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC CÔNG NGUYÊN hoặc SAU CÔNG NGUYÊN.', [['Nước Văn Lang ra đời', 'TRƯỚC CÔNG NGUYÊN'], ['Trống đồng Đông Sơn', 'TRƯỚC CÔNG NGUYÊN'], ['Nhà Lý thành lập', 'SAU CÔNG NGUYÊN'], ['Nhà Trần thành lập', 'SAU CÔNG NGUYÊN']], 'Văn Lang và trống đồng trước Công nguyên; nhà Lý, Trần sau Công nguyên.', $d);
        $this->sortQ($s, 'Kéo mỗi nghề nghiệp vào nhóm CÓ Ở THỜI VĂN LANG hoặc CHƯA CÓ.', [['Trồng lúa nước', 'CÓ Ở THỜI VĂN LANG'], ['Đúc đồng', 'CÓ Ở THỜI VĂN LANG'], ['Chữ viết', 'CHƯA CÓ'], ['Xe máy', 'CHƯA CÓ']], 'Trồng lúa, đúc đồng có từ thời Văn Lang; chữ viết, xe máy chưa có.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm VUA hoặc NGƯỜI DÂN.', [['Hùng Vương', 'VUA'], ['Nông dân Lạc Việt', 'NGƯỜI DÂN']], 'Hùng Vương là vua; nông dân Lạc Việt là người dân.', $d);
        $this->fill($s, 'Nước Văn Lang ra đời vào khoảng thế kỉ VII trước Công ___.', [[0, 'nguyên']], 'Văn Lang ra đời khoảng thế kỉ VII TCN.', $d);
        $this->fill($s, 'Nước Văn Lang chia thành 15 ___.', [[0, 'bộ']], 'Văn Lang có 15 bộ.', $d);
        $this->fill($s, 'Kinh đô Văn Lang ở Phong Châu, nay thuộc tỉnh Phú ___.', [[0, 'Thọ']], 'Phong Châu nay thuộc Phú Thọ.', $d);
        $this->fill($s, 'Người Lạc Việt sống chủ yếu bằng nghề trồng lúa ___.', [[0, 'nước']], 'Người Lạc Việt trồng lúa nước.', $d);
        $this->fill($s, 'Trống đồng Đông Sơn là di sản của thời Hùng ___.', [[0, 'Vương']], 'Trống đồng thời Hùng Vương.', $d);
    }

    private function seedLsAnhHungDanToc(): void
    {
        $s = 'ls-anh-hung-dan-toc'; $d = 'de';
        $this->quiz($s, 'Bà Triệu phất cờ khởi nghĩa vào năm nào?', ['40', '248', '542', '938'], 1, 'Bà Triệu khởi nghĩa năm 248 chống quân Ngô.', $d);
        $this->quiz($s, 'Lý Bí lập ra nhà nước có tên là gì?', ['Văn Lang', 'Vạn Xuân', 'Đại Cồ Việt', 'Đại Việt'], 1, 'Lý Bí lập nước Vạn Xuân năm 544.', $d);
        $this->quiz($s, 'Ngô Quyền đánh thắng quân Nam Hán vào năm nào?', ['938', '981', '1077', '1288'], 0, 'Ngô Quyền thắng trận Bạch Đằng năm 938.', $d);
        $this->quiz($s, 'Hai Bà Trưng quê ở đâu?', ['Mê Linh', 'Thanh Hóa', 'Nghệ An', 'Phú Thọ'], 0, 'Hai Bà Trưng quê ở Mê Linh (Hà Nội).', $d);
        $this->quiz($s, 'Đinh Bộ Lĩnh đã dẹp loạn bao nhiêu sứ quân?', ['10', '12', '15', '8'], 1, 'Đinh Bộ Lĩnh dẹp loạn 12 sứ quân.', $d);
        $this->matching($s, 'Nối mỗi anh hùng với quê hương của người đó.', [['Hai Bà Trưng', 'Mê Linh'], ['Bà Triệu', 'Triệu Sơn – Thanh Hóa'], ['Lý Bí', 'Thái Bình'], ['Ngô Quyền', 'Đường Lâm – Hà Nội']], 'Hai Bà Mê Linh, Bà Triệu Thanh Hóa, Lý Bí Thái Bình, Ngô Quyền Đường Lâm.', $d);
        $this->matching($s, 'Nối mỗi cuộc khởi nghĩa với năm diễn ra của nó.', [['Hai Bà Trưng', 'Năm 40'], ['Bà Triệu', 'Năm 248'], ['Lý Bí', 'Năm 542'], ['Ngô Quyền', 'Năm 938']], 'Hai Bà 40, Bà Triệu 248, Lý Bí 542, Ngô Quyền 938.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với danh hiệu của người đó.', [['Ngô Quyền', 'Người mở nền độc lập'], ['Bà Triệu', 'Bậc liệt nữ'], ['Lý Bí', 'Lý Nam Đế']], 'Ngô Quyền mở nền độc lập, Bà Triệu là liệt nữ, Lý Bí là Lý Nam Đế.', $d);
        $this->matching($s, 'Nối mỗi câu nói nổi tiếng với nhân vật của nó.', [['"Tôi muốn cưỡi cơn gió mạnh..."', 'Bà Triệu'], ['"Đánh cho để dài tóc..."', 'Bà Trưng Trắc']], 'Bà Triệu muốn cưỡi gió mạnh; Trưng Trắc đánh cho để dài tóc.', $d);
        $this->matching($s, 'Nối mỗi trận đánh với địa điểm của nó.', [['Bạch Đằng 938', 'Sông Bạch Đằng'], ['Khởi nghĩa Hai Bà', 'Mê Linh'], ['Khởi nghĩa Bà Triệu', 'Thanh Hóa']], 'Bạch Đằng 938 trên sông Bạch Đằng, Hai Bà ở Mê Linh.', $d);
        $this->sortQ($s, 'Kéo mỗi anh hùng vào cuộc khởi nghĩa mà người đó lãnh đạo.', [['Bà Triệu', 'KHỞI NGHĨA NĂM 248'], ['Lý Bí', 'KHỞI NGHĨA NĂM 542'], ['Mai Thúc Loan', 'KHỞI NGHĨA NĂM 722']], 'Bà Triệu 248, Lý Bí 542, Mai Thúc Loan 722.', $d);
        $this->sortQ($s, 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.', [['Bà Triệu khởi nghĩa năm 248', 'ĐÚNG'], ['Lý Bí lập nước Vạn Xuân', 'ĐÚNG'], ['Hai Bà Trưng quê ở Mê Linh', 'ĐÚNG'], ['Ngô Quyền đánh thắng quân Tống', 'SAI']], 'Ngô Quyền đánh thắng quân Nam Hán, không phải quân Tống.', $d);
        $this->sortQ($s, 'Kéo mỗi tên gọi vào nhóm ANH HÙNG hoặc ĐỊA DANH.', [['Lý Bí', 'ANH HÙNG'], ['Mai Thúc Loan', 'ANH HÙNG'], ['Sông Bạch Đằng', 'ĐỊA DANH'], ['Núi Tản Viên', 'ĐỊA DANH']], 'Lý Bí, Mai Thúc Loan là anh hùng; Bạch Đằng, Tản Viên là địa danh.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU năm 500.', [['Khởi nghĩa Hai Bà (40)', 'TRƯỚC NĂM 500'], ['Khởi nghĩa Bà Triệu (248)', 'TRƯỚC NĂM 500'], ['Khởi nghĩa Lý Bí (542)', 'SAU NĂM 500'], ['Chiến thắng Bạch Đằng (938)', 'SAU NĂM 500']], 'Hai Bà và Bà Triệu trước năm 500; Lý Bí và Ngô Quyền sau năm 500.', $d);
        $this->sortQ($s, 'Kéo mỗi cuộc khởi nghĩa vào nhóm THÀNH CÔNG hoặc CHƯA THÀNH CÔNG.', [['Hai Bà Trưng', 'THÀNH CÔNG (bước đầu)'], ['Ngô Quyền 938', 'THÀNH CÔNG'], ['Bà Triệu', 'CHƯA THÀNH CÔNG']], 'Hai Bà và Ngô Quyền thành công; Bà Triệu chưa thành công.', $d);
        $this->fill($s, 'Bà Triệu khởi nghĩa năm ___.', [[0, '248']], 'Bà Triệu khởi nghĩa năm 248.', $d);
        $this->fill($s, 'Lý Bí lập ra nước Vạn ___.', [[0, 'Xuân']], 'Lý Bí lập nước Vạn Xuân.', $d);
        $this->fill($s, 'Ngô Quyền đánh thắng quân Nam Hán năm ___.', [[0, '938']], 'Chiến thắng Bạch Đằng năm 938.', $d);
        $this->fill($s, 'Hai Bà Trưng quê ở ___ Linh (Hà Nội).', [[0, 'Mê']], 'Hai Bà quê ở Mê Linh.', $d);
        $this->fill($s, 'Đinh Bộ Lĩnh dẹp loạn 12 sứ ___.', [[0, 'quân']], 'Đinh Bộ Lĩnh dẹp 12 sứ quân.', $d);
    }

    private function seedLsDinhBoLinh(): void
    {
        $s = 'ls-dinh-bo-linh'; $d = 'de';
        $this->quiz($s, 'Đinh Bộ Lĩnh quê ở đâu?', ['Thăng Long', 'Hoa Lư – Ninh Bình', 'Thanh Hóa', 'Nghệ An'], 1, 'Đinh Bộ Lĩnh quê ở Hoa Lư, Ninh Bình.', $d);
        $this->quiz($s, 'Đinh Bộ Lĩnh lên ngôi hoàng đế vào năm nào?', ['938', '968', '980', '1009'], 1, 'Năm 968, Đinh Bộ Lĩnh lên ngôi hoàng đế.', $d);
        $this->quiz($s, 'Đinh Bộ Lĩnh lấy hiệu là gì?', ['Đinh Tiên Hoàng', 'Đinh Phế Đế', 'Lê Đại Hành', 'Lý Thái Tổ'], 0, 'Đinh Bộ Lĩnh lấy hiệu Đinh Tiên Hoàng.', $d);
        $this->quiz($s, 'Người kế vị Đinh Tiên Hoàng là ai?', ['Đinh Liễn', 'Đinh Phế Đế (Đinh Toàn)', 'Lê Hoàn', 'Ngô Xương Ngập'], 1, 'Đinh Toàn (Đinh Phế Đế) kế vị khi còn nhỏ tuổi.', $d);
        $this->quiz($s, 'Triều đại nhà Đinh tồn tại trong bao nhiêu năm?', ['10', '12', '20', '30'], 1, 'Nhà Đinh tồn tại 12 năm (968 – 980).', $d);
        $this->matching($s, 'Nối mỗi danh hiệu với nhân vật của nó.', [['Đinh Tiên Hoàng', 'Đinh Bộ Lĩnh'], ['Vạn Thắng Vương', 'Đinh Bộ Lĩnh'], ['Thập đạo tướng quân', 'Lê Hoàn']], 'Đinh Tiên Hoàng và Vạn Thắng Vương là Đinh Bộ Lĩnh.', $d);
        $this->matching($s, 'Nối mỗi sự kiện với năm diễn ra của nó.', [['Dẹp xong loạn 12 sứ quân', 'Năm 968'], ['Lên ngôi hoàng đế', 'Năm 968'], ['Nhà Đinh kết thúc', 'Năm 980']], 'Năm 968 dẹp loạn và lên ngôi; năm 980 nhà Đinh kết thúc.', $d);
        $this->matching($s, 'Nối mỗi việc làm với ý nghĩa của nó.', [['Đặt tên nước Đại Cồ Việt', 'Khẳng định độc lập'], ['Đóng đô ở Hoa Lư', 'Dựa vào địa thế hiểm'], ['Dẹp loạn 12 sứ quân', 'Thống nhất đất nước']], 'Đặt tên nước khẳng định độc lập, Hoa Lư hiểm yếu, dẹp loạn thống nhất.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với vai trò của người đó.', [['Đinh Bộ Lĩnh', 'Vua đầu tiên nhà Đinh'], ['Đinh Liễn', 'Con trai Đinh Bộ Lĩnh'], ['Lê Hoàn', 'Tướng rồi lên ngôi vua']], 'Đinh Bộ Lĩnh vua đầu, Đinh Liễn con trai, Lê Hoàn tướng rồi vua.', $d);
        $this->matching($s, 'Nối mỗi địa danh với sự kiện gắn với nó.', [['Hoa Lư', 'Kinh đô nhà Đinh'], ['Sông Bạch Đằng', 'Chiến thắng 938'], ['Đại La', 'Nơi Ngô Quyền đóng đô']], 'Hoa Lư kinh đô Đinh, Bạch Đằng chiến thắng 938.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm THỜI LOẠN 12 SỨ QUÂN hoặc SAU KHI THỐNG NHẤT.', [['Các sứ quân cát cứ', 'THỜI LOẠN'], ['Đinh Bộ Lĩnh lên ngôi', 'SAU THỐNG NHẤT'], ['Đặt tên nước Đại Cồ Việt', 'SAU THỐNG NHẤT']], 'Loạn 12 sứ quân trước 968; lên ngôi, đặt tên nước sau 968.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm VUA NHÀ ĐINH hoặc KHÔNG PHẢI.', [['Đinh Tiên Hoàng', 'VUA NHÀ ĐINH'], ['Đinh Phế Đế', 'VUA NHÀ ĐINH'], ['Lê Đại Hành', 'KHÔNG PHẢI'], ['Lý Thái Tổ', 'KHÔNG PHẢI']], 'Đinh Tiên Hoàng và Đinh Phế Đế là vua nhà Đinh.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm KINH ĐÔ THỜI ĐINH hoặc KHÔNG PHẢI.', [['Hoa Lư', 'KINH ĐÔ THỜI ĐINH'], ['Thăng Long', 'KHÔNG PHẢI'], ['Phú Xuân', 'KHÔNG PHẢI']], 'Hoa Lư là kinh đô nhà Đinh.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm CÔNG LAO CỦA ĐINH BỘ LĨNH hoặc KHÔNG PHẢI.', [['Dẹp loạn 12 sứ quân', 'CÔNG LAO'], ['Thống nhất đất nước', 'CÔNG LAO'], ['Đánh thắng quân Mông Cổ', 'KHÔNG PHẢI'], ['Mở khoa thi đầu tiên', 'KHÔNG PHẢI']], 'Dẹp loạn và thống nhất là công lao của Đinh Bộ Lĩnh.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU năm 968.', [['Loạn 12 sứ quân', 'TRƯỚC NĂM 968'], ['Ngô Quyền mất (944)', 'TRƯỚC NĂM 968'], ['Nhà Tiền Lê thành lập (980)', 'SAU NĂM 968'], ['Kháng chiến chống Tống (981)', 'SAU NĂM 968']], 'Loạn 12 sứ quân trước 968; Tiền Lê và chống Tống sau 968.', $d);
        $this->fill($s, 'Đinh Bộ Lĩnh quê ở Hoa Lư, tỉnh Ninh ___.', [[0, 'Bình']], 'Đinh Bộ Lĩnh quê ở Ninh Bình.', $d);
        $this->fill($s, 'Đinh Bộ Lĩnh lên ngôi hoàng đế năm ___.', [[0, '968']], 'Năm 968 lên ngôi.', $d);
        $this->fill($s, 'Đinh Bộ Lĩnh lấy hiệu là Đinh Tiên ___.', [[0, 'Hoàng']], 'Hiệu Đinh Tiên Hoàng.', $d);
        $this->fill($s, 'Triều Đinh tồn tại 12 năm, từ 968 đến ___.', [[0, '980']], 'Nhà Đinh 968 – 980.', $d);
        $this->fill($s, 'Người kế vị Đinh Tiên Hoàng là Đinh Phế ___.', [[0, 'Đế']], 'Đinh Phế Đế kế vị.', $d);
    }

    private function seedLsLeHoan(): void
    {
        $s = 'ls-le-hoan'; $d = 'de';
        $this->quiz($s, 'Trước khi lên ngôi, Lê Hoàn giữ chức vụ gì?', ['Tể tướng', 'Thập đạo tướng quân', 'Thái sư', 'Quan văn'], 1, 'Lê Hoàn là Thập đạo tướng quân trước khi lên ngôi.', $d);
        $this->quiz($s, 'Lê Hoàn lên ngôi vua vào năm nào?', ['968', '980', '981', '1009'], 1, 'Năm 980, Lê Hoàn lên ngôi, lập nhà Tiền Lê.', $d);
        $this->quiz($s, 'Năm 981, Lê Hoàn đã đánh thắng quân xâm lược nào?', ['Quân Tống', 'Quân Mông Cổ', 'Quân Minh', 'Quân Thanh'], 0, 'Năm 981, Lê Hoàn đánh thắng quân Tống.', $d);
        $this->quiz($s, 'Hoàng hậu Dương Vân Nga trước đây là vợ của ai?', ['Ngô Quyền', 'Đinh Tiên Hoàng', 'Lê Lợi', 'Lý Thái Tổ'], 1, 'Dương Vân Nga là vợ Đinh Tiên Hoàng, sau lấy Lê Hoàn.', $d);
        $this->quiz($s, 'Lê Hoàn mất vào năm nào?', ['980', '981', '1005', '1009'], 2, 'Lê Hoàn mất năm 1005.', $d);
        $this->matching($s, 'Nối mỗi vị vua với niên hiệu của ông.', [['Đinh Tiên Hoàng', 'Thái Bình'], ['Lê Đại Hành', 'Thiên Phúc'], ['Lý Thái Tổ', 'Thuận Thiên']], 'Đinh Tiên Hoàng – Thái Bình, Lê Đại Hành – Thiên Phúc.', $d);
        $this->matching($s, 'Nối mỗi sự kiện với năm diễn ra của nó.', [['Lê Hoàn lên ngôi', 'Năm 980'], ['Đánh thắng quân Tống', 'Năm 981'], ['Lê Hoàn mất', 'Năm 1005']], '980 lên ngôi, 981 thắng Tống, 1005 mất.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với vai trò của người đó.', [['Dương Vân Nga', 'Hoàng hậu'], ['Lê Long Đĩnh', 'Vua cuối nhà Tiền Lê'], ['Phạm Cự Lượng', 'Tướng ủng hộ Lê Hoàn']], 'Dương Vân Nga hoàng hậu, Lê Long Đĩnh vua cuối Tiền Lê.', $d);
        $this->matching($s, 'Nối mỗi trận đánh năm 981 với địa điểm của nó.', [['Bạch Đằng 981', 'Sông Bạch Đằng'], ['Chi Lăng 981', 'Lạng Sơn']], 'Năm 981 thắng ở Bạch Đằng và Chi Lăng.', $d);
        $this->matching($s, 'Nối mỗi triều đại với thời gian tồn tại của nó.', [['Nhà Đinh', '968 – 980'], ['Nhà Tiền Lê', '980 – 1009'], ['Nhà Lý', '1009 – 1225']], 'Đinh 968–980, Tiền Lê 980–1009, Lý 1009–1225.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào triều đại đúng.', [['Đinh Liễn', 'NHÀ ĐINH'], ['Lê Long Đĩnh', 'NHÀ TIỀN LÊ'], ['Lý Công Uẩn', 'NHÀ LÝ']], 'Đinh Liễn nhà Đinh, Lê Long Đĩnh Tiền Lê, Lý Công Uẩn nhà Lý.', $d);
        $this->sortQ($s, 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.', [['Lê Hoàn thắng quân Tống năm 981', 'ĐÚNG'], ['Lê Hoàn từng là Thập đạo tướng quân', 'ĐÚNG'], ['Dương Vân Nga là hoàng hậu', 'ĐÚNG'], ['Nhà Tiền Lê đóng đô ở Thăng Long', 'SAI']], 'Tiền Lê đóng đô ở Hoa Lư.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm VUA hoặc TƯỚNG.', [['Lê Đại Hành', 'VUA'], ['Lê Long Đĩnh', 'VUA'], ['Phạm Cự Lượng', 'TƯỚNG'], ['Đinh Điền', 'TƯỚNG']], 'Lê Đại Hành, Lê Long Đĩnh là vua; Phạm Cự Lượng, Đinh Điền là tướng.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU năm 980.', [['Đinh Tiên Hoàng bị sát hại (979)', 'TRƯỚC NĂM 980'], ['Lê Hoàn làm Thập đạo tướng quân', 'TRƯỚC NĂM 980'], ['Đánh thắng quân Tống (981)', 'SAU NĂM 980'], ['Lê Hoàn mất (1005)', 'SAU NĂM 980']], 'Năm 980 là mốc Lê Hoàn lên ngôi.', $d);
        $this->sortQ($s, 'Kéo mỗi trận đánh vào nhóm CHỐNG QUÂN TỐNG hoặc KHÁC.', [['Bạch Đằng 981', 'CHỐNG TỐNG'], ['Chi Lăng 981', 'CHỐNG TỐNG'], ['Bạch Đằng 938', 'KHÁC'], ['Như Nguyệt 1077', 'KHÁC']], 'Bạch Đằng và Chi Lăng 981 chống Tống.', $d);
        $this->fill($s, 'Trước khi lên ngôi, Lê Hoàn giữ chức Thập đạo tướng ___.', [[0, 'quân']], 'Lê Hoàn là Thập đạo tướng quân.', $d);
        $this->fill($s, 'Lê Hoàn lên ngôi năm ___.', [[0, '980']], 'Năm 980 lên ngôi.', $d);
        $this->fill($s, 'Hoàng hậu Dương Vân Nga trước là vợ của Đinh Tiên ___.', [[0, 'Hoàng']], 'Dương Vân Nga vợ Đinh Tiên Hoàng.', $d);
        $this->fill($s, 'Năm 981, Lê Hoàn đánh thắng quân Tống ở Bạch Đằng và Chi ___.', [[0, 'Lăng']], 'Thắng ở Bạch Đằng và Chi Lăng.', $d);
        $this->fill($s, 'Lê Hoàn mất năm 1005, con trai là Lê Long ___ lên ngôi.', [[0, 'Đĩnh']], 'Lê Long Đĩnh kế vị.', $d);
    }

    private function seedLsDongBoDau(): void
    {
        $s = 'ls-dong-bo-dau'; $d = 'trung_binh';
        $this->quiz($s, 'Lần đầu tiên quân Mông Cổ xâm lược Đại Việt vào năm nào?', ['1258', '1285', '1288', '1278'], 0, 'Năm 1258, quân Mông Cổ lần đầu xâm lược.', $d);
        $this->quiz($s, 'Ai là người chỉ huy cuộc kháng chiến lần thứ nhất (1258)?', ['Trần Hưng Đạo', 'Trần Thủ Độ', 'Trần Quang Khải', 'Trần Thái Tông'], 1, 'Thái sư Trần Thủ Độ chỉ huy kháng chiến lần 1.', $d);
        $this->quiz($s, 'Lần thứ ba (1287 – 1288), quân Nguyên do ai chỉ huy xâm lược?', ['Hốt Tất Liệt', 'Thoát Hoan', 'Ô Mã Nhi', 'Toa Đô'], 1, 'Thoát Hoan chỉ huy quân Nguyên lần 3.', $d);
        $this->quiz($s, 'Trận Bạch Đằng năm 1288 do ai chỉ huy quân ta?', ['Trần Quang Khải', 'Trần Hưng Đạo', 'Trần Nhật Duật', 'Trần Khánh Dư'], 1, 'Trần Hưng Đạo tổng chỉ huy trận Bạch Đằng 1288.', $d);
        $this->quiz($s, 'Hội nghị Diên Hồng thể hiện quyết tâm đánh giặc diễn ra năm nào?', ['1284', '1285', '1288', '1258'], 0, 'Hội nghị Diên Hồng họp năm 1284.', $d);
        $this->matching($s, 'Nối mỗi năm với cuộc kháng chiến tương ứng.', [['1258', 'Lần thứ nhất'], ['1285', 'Lần thứ hai'], ['1287 – 1288', 'Lần thứ ba']], '1258 lần 1, 1285 lần 2, 1287–1288 lần 3.', $d);
        $this->matching($s, 'Nối mỗi danh tướng với chiến công của ông.', [['Trần Hưng Đạo', 'Bạch Đằng 1288'], ['Trần Quang Khải', 'Chương Dương'], ['Trần Nhật Duật', 'Hàm Tử']], 'Trần Hưng Đạo – Bạch Đằng, Trần Quang Khải – Chương Dương.', $d);
        $this->matching($s, 'Nối mỗi địa danh với sự kiện gắn với nó.', [['Bạch Đằng', 'Trận thủy chiến 1288'], ['Chương Dương', 'Trận đánh 1285'], ['Vạn Kiếp', 'Căn cứ của nhà Trần']], 'Bạch Đằng 1288, Chương Dương 1285, Vạn Kiếp căn cứ.', $d);
        $this->matching($s, 'Nối mỗi câu nói với nhân vật của nó.', [['"Đầu tôi chưa rơi xuống đất, xin bệ hạ đừng lo"', 'Trần Thủ Độ'], ['"Đánh!"', 'Trần Hưng Đạo']], 'Trần Thủ Độ quyết tâm đánh giặc; Trần Hưng Đạo hô "Đánh!".', $d);
        $this->matching($s, 'Nối mỗi hội nghị với nội dung của nó.', [['Hội nghị Diên Hồng', 'Hỏi ý kiến toàn dân'], ['Hội nghị Bình Than', 'Bàn kế đánh giặc']], 'Diên Hồng hỏi ý dân, Bình Than bàn kế đánh giặc.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm LẦN 1 (1258) hoặc LẦN 2 – 3.', [['Quân Mông Cổ xâm lược', 'LẦN 1'], ['Trần Thủ Độ chỉ huy', 'LẦN 1'], ['Trận Bạch Đằng 1288', 'LẦN 2 – 3'], ['Thoát Hoan xâm lược', 'LẦN 2 – 3']], '1258 là lần 1; 1285 và 1287–1288 là lần 2, 3.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm TƯỚNG NHÀ TRẦN hoặc TƯỚNG GIẶC.', [['Trần Hưng Đạo', 'TƯỚNG NHÀ TRẦN'], ['Trần Quang Khải', 'TƯỚNG NHÀ TRẦN'], ['Thoát Hoan', 'TƯỚNG GIẶC'], ['Ô Mã Nhi', 'TƯỚNG GIẶC']], 'Trần Hưng Đạo, Trần Quang Khải tướng ta; Thoát Hoan, Ô Mã Nhi tướng giặc.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm KẾ SÁCH CỦA NHÀ TRẦN hoặc KHÔNG PHẢI.', [['Vườn không nhà trống', 'KẾ SÁCH NHÀ TRẦN'], ['Đóng cọc ở Bạch Đằng', 'KẾ SÁCH NHÀ TRẦN'], ['Đánh trực diện', 'KHÔNG PHẢI'], ['Cầu hòa giặc', 'KHÔNG PHẢI']], 'Vườn không nhà trống và đóng cọc là kế sách nhà Trần.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm NGUYÊN NHÂN THẮNG LỢI hoặc KHÔNG PHẢI.', [['Đoàn kết toàn dân', 'NGUYÊN NHÂN THẮNG LỢI'], ['Tài chỉ huy', 'NGUYÊN NHÂN THẮNG LỢI'], ['Quân giặc đông', 'KHÔNG PHẢI'], ['Vũ khí giặc tốt', 'KHÔNG PHẢI']], 'Đoàn kết và tài chỉ huy là nguyên nhân thắng lợi.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU năm 1285.', [['Lần 1 (1258)', 'TRƯỚC NĂM 1285'], ['Diên Hồng (1284)', 'TRƯỚC NĂM 1285'], ['Lần 3 (1287 – 1288)', 'SAU NĂM 1285'], ['Bạch Đằng 1288', 'SAU NĂM 1285']], 'Năm 1285 là mốc lần kháng chiến thứ hai.', $d);
        $this->fill($s, 'Lần đầu quân Mông Cổ xâm lược Đại Việt năm ___.', [[0, '1258']], 'Năm 1258 xâm lược lần 1.', $d);
        $this->fill($s, 'Người chỉ huy kháng chiến lần thứ nhất là Trần Thủ ___.', [[0, 'Độ']], 'Trần Thủ Độ chỉ huy lần 1.', $d);
        $this->fill($s, 'Lần thứ ba, quân Nguyên do Thoát ___ chỉ huy.', [[0, 'Hoan']], 'Thoát Hoan chỉ huy lần 3.', $d);
        $this->fill($s, 'Hội nghị Diên Hồng diễn ra năm ___.', [[0, '1284']], 'Diên Hồng họp năm 1284.', $d);
        $this->fill($s, 'Trận quyết chiến năm 1288 diễn ra trên sông Bạch ___.', [[0, 'Đằng']], 'Bạch Đằng 1288.', $d);
    }

    private function seedLsTranHungDao(): void
    {
        $s = 'ls-tran-hung-dao'; $d = 'trung_binh';
        $this->quiz($s, 'Trần Hưng Đạo là con của ai?', ['Trần Thái Tông', 'Trần Liễu', 'Trần Thủ Độ', 'Trần Thánh Tông'], 1, 'Trần Hưng Đạo là con của Trần Liễu.', $d);
        $this->quiz($s, 'Trần Hưng Đạo được phong tước hiệu gì?', ['Hưng Đạo Vương', 'Quốc Công', 'Thái Sư', 'Đại Vương'], 0, 'Trần Hưng Đạo được phong Hưng Đạo Vương.', $d);
        $this->quiz($s, '"Hịch tướng sĩ" được Trần Hưng Đạo viết vào năm nào?', ['1284', '1285', '1288', '1300'], 0, 'Hịch tướng sĩ viết khoảng năm 1284.', $d);
        $this->quiz($s, '"Binh thư yếu lược" là tác phẩm viết về lĩnh vực gì?', ['Văn học', 'Quân sự', 'Y học', 'Nông nghiệp'], 1, 'Binh thư yếu lược là tác phẩm quân sự.', $d);
        $this->quiz($s, 'Trần Hưng Đạo mất vào năm nào?', ['1288', '1300', '1307', '1228'], 1, 'Trần Hưng Đạo mất năm 1300.', $d);
        $this->matching($s, 'Nối mỗi tác phẩm với nội dung của nó.', [['Hịch tướng sĩ', 'Kêu gọi tướng sĩ đánh giặc'], ['Binh thư yếu lược', 'Binh pháp quân sự'], ['Vạn Kiếp tông bí truyền thư', 'Binh pháp']], 'Hịch tướng sĩ kêu gọi đánh giặc; hai tác phẩm còn lại về binh pháp.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với quan hệ trong gia đình.', [['Trần Liễu', 'Cha của Trần Hưng Đạo'], ['Trần Quốc Tuấn', 'Tên thật'], ['Trần Hưng Đạo', 'Tước hiệu']], 'Trần Liễu là cha; Trần Quốc Tuấn là tên thật.', $d);
        $this->matching($s, 'Nối mỗi danh hiệu với ý nghĩa của nó.', [['Đức Thánh Trần', 'Nhân dân tôn kính'], ['Hưng Đạo Vương', 'Tước được phong']], 'Nhân dân tôn thờ ông là Đức Thánh Trần.', $d);
        $this->matching($s, 'Nối mỗi trận đánh với vai trò của Trần Hưng Đạo.', [['Bạch Đằng 1288', 'Tổng chỉ huy'], ['Chương Dương 1285', 'Phối hợp chỉ huy']], 'Ông tổng chỉ huy Bạch Đằng 1288.', $d);
        $this->matching($s, 'Nối mỗi câu văn trong "Hịch tướng sĩ" với ý nghĩa của nó.', [['"Ta thường tới bữa quên ăn, nửa đêm vỗ gối..."', 'Lòng lo nước, thương dân'], ['"Nay các ngươi ngồi nhìn chủ nhục mà không biết lo..."', 'Phê phán tướng sĩ thờ ơ']], 'Hai câu văn nổi tiếng trong Hịch tướng sĩ.', $d);
        $this->sortQ($s, 'Kéo mỗi tác phẩm vào nhóm CỦA TRẦN HƯNG ĐẠO hoặc KHÔNG PHẢI.', [['Hịch tướng sĩ', 'CỦA TRẦN HƯNG ĐẠO'], ['Vạn Kiếp tông bí truyền thư', 'CỦA TRẦN HƯNG ĐẠO'], ['Bình Ngô đại cáo', 'KHÔNG PHẢI'], ['Truyện Kiều', 'KHÔNG PHẢI']], 'Hịch tướng sĩ và Vạn Kiếp tông bí truyền thư của Trần Hưng Đạo.', $d);
        $this->sortQ($s, 'Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.', [['Trần Hưng Đạo tên thật là Trần Quốc Tuấn', 'ĐÚNG'], ['Ông mất năm 1300', 'ĐÚNG'], ['Hịch tướng sĩ viết năm 1284', 'ĐÚNG'], ['Ông là vua nhà Trần', 'SAI']], 'Ông là tướng, không phải vua.', $d);
        $this->sortQ($s, 'Kéo mỗi trận đánh vào cuộc kháng chiến đúng.', [['Bạch Đằng 1288', 'LẦN 3'], ['Chương Dương 1285', 'LẦN 2']], 'Bạch Đằng 1288 thuộc lần 3; Chương Dương 1285 thuộc lần 2.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm TƯỚNG NHÀ TRẦN hoặc KHÁC.', [['Trần Quang Khải', 'TƯỚNG NHÀ TRẦN'], ['Trần Nhật Duật', 'TƯỚNG NHÀ TRẦN'], ['Thoát Hoan', 'KHÁC'], ['Lý Thường Kiệt', 'KHÁC']], 'Trần Quang Khải, Trần Nhật Duật là tướng nhà Trần.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU năm 1288.', [['Hịch tướng sĩ (1284)', 'TRƯỚC NĂM 1288'], ['Trận Bạch Đằng', 'NĂM 1288'], ['Trần Hưng Đạo mất (1300)', 'SAU NĂM 1288']], 'Hịch tướng sĩ 1284, mất 1300.', $d);
        $this->fill($s, 'Cha của Trần Hưng Đạo là Trần ___.', [[0, 'Liễu']], 'Trần Liễu là cha của ông.', $d);
        $this->fill($s, 'Trần Hưng Đạo được phong tước Hưng Đạo ___.', [[0, 'Vương']], 'Tước Hưng Đạo Vương.', $d);
        $this->fill($s, '"Hịch tướng sĩ" được viết năm ___.', [[0, '1284']], 'Hịch tướng sĩ viết năm 1284.', $d);
        $this->fill($s, '"Binh thư yếu lược" là tác phẩm về quân ___.', [[0, 'sự']], 'Tác phẩm quân sự.', $d);
        $this->fill($s, 'Trần Hưng Đạo mất năm ___.', [[0, '1300']], 'Ông mất năm 1300.', $d);
    }

    private function seedLsDungNuocLop61(): void
    {
        $s = 'ls-dung-nuoc-lop-6-1'; $d = 'de';
        $this->quiz($s, 'Ai được coi là vị vua đầu tiên của nước Văn Lang?', ['An Dương Vương', 'Hùng Vương', 'Thục Phán', 'Lạc Long Quân'], 1, 'Hùng Vương là vị vua đầu tiên của nước Văn Lang.', $d);
        $this->quiz($s, 'Đền thờ các vua Hùng đặt ở tỉnh nào?', ['Vĩnh Phúc', 'Phú Thọ', 'Hòa Bình', 'Hà Nội'], 1, 'Đền Hùng ở tỉnh Phú Thọ.', $d);
        $this->quiz($s, 'Ngày Giỗ Tổ Hùng Vương là ngày nào?', ['Mùng 1 tháng 1', 'Mùng 10 tháng 3 âm lịch', 'Rằm tháng 7', 'Mùng 5 tháng 5'], 1, 'Giỗ Tổ Hùng Vương vào mùng 10 tháng 3 âm lịch.', $d);
        $this->quiz($s, 'Nghề đúc đồng thời Văn Lang nổi tiếng với sản phẩm nào?', ['Trống đồng', 'Chiêng', 'Cồng', 'Chuông'], 0, 'Trống đồng Đông Sơn là sản phẩm đúc đồng nổi tiếng.', $d);
        $this->quiz($s, '"Con Rồng cháu Tiên" gắn với truyền thuyết nào?', ['Sơn Tinh – Thủy Tinh', 'Lạc Long Quân – Âu Cơ', 'Thánh Gióng', 'Mị Châu – Trọng Thủy'], 1, 'Truyền thuyết Lạc Long Quân – Âu Cơ sinh ra nòi giống Việt.', $d);
        $this->matching($s, 'Nối mỗi di sản với mô tả của nó.', [['Trống đồng', 'Sản phẩm đúc đồng'], ['Đền Hùng', 'Nơi thờ các vua Hùng'], ['Lưỡi cày đồng', 'Công cụ nông nghiệp']], 'Trống đồng đúc đồng, đền Hùng thờ vua, lưỡi cày làm nông.', $d);
        $this->matching($s, 'Nối mỗi chức danh với quyền hạn của nó.', [['Hùng Vương', 'Vua cả nước'], ['Lạc hầu', 'Giúp việc cho vua'], ['Lạc tướng', 'Đứng đầu các bộ']], 'Hùng Vương là vua, lạc hầu giúp vua, lạc tướng đứng đầu bộ.', $d);
        $this->matching($s, 'Nối mỗi lễ hội với ý nghĩa của nó.', [['Giỗ Tổ Hùng Vương', 'Nhớ công ơn dựng nước'], ['Hội đền Hùng', 'Tưởng nhớ các vua Hùng']], 'Giỗ Tổ và hội đền Hùng tưởng nhớ công ơn các vua Hùng.', $d);
        $this->matching($s, 'Nối mỗi địa danh với tỉnh hiện nay của nó.', [['Phong Châu', 'Phú Thọ'], ['Cổ Loa', 'Hà Nội'], ['Đông Sơn', 'Thanh Hóa']], 'Phong Châu ở Phú Thọ, Cổ Loa ở Hà Nội, Đông Sơn ở Thanh Hóa.', $d);
        $this->matching($s, 'Nối mỗi truyền thuyết với nhân vật chính của nó.', [['Trăm trứng nở trăm con', 'Âu Cơ'], ['Sơn Tinh – Thủy Tinh', 'Vua Hùng thứ XVIII']], 'Âu Cơ sinh trăm trứng; Sơn Tinh – Thủy Tinh thời Hùng Vương XVIII.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm THUỘC NƯỚC VĂN LANG hoặc KHÔNG THUỘC.', [['Trống đồng', 'THUỘC VĂN LANG'], ['Giỗ Tổ Hùng Vương', 'THUỘC VĂN LANG'], ['Chữ Nôm', 'KHÔNG THUỘC'], ['Áo dài', 'KHÔNG THUỘC']], 'Trống đồng và Giỗ Tổ thuộc thời Văn Lang.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm THỜI HÙNG VƯƠNG hoặc THỜI SAU.', [['Lạc Long Quân', 'THỜI HÙNG VƯƠNG'], ['Âu Cơ', 'THỜI HÙNG VƯƠNG'], ['Nguyễn Huệ', 'THỜI SAU'], ['Hồ Chí Minh', 'THỜI SAU']], 'Lạc Long Quân, Âu Cơ thời Hùng Vương.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC CÔNG NGUYÊN hoặc SAU CÔNG NGUYÊN.', [['Truyền thuyết Sơn Tinh – Thủy Tinh', 'TRƯỚC CÔNG NGUYÊN'], ['Nước Văn Lang', 'TRƯỚC CÔNG NGUYÊN'], ['Khởi nghĩa Hai Bà Trưng', 'SAU CÔNG NGUYÊN']], 'Văn Lang trước Công nguyên; Hai Bà Trưng sau Công nguyên.', $d);
        $this->sortQ($s, 'Kéo mỗi đồ vật vào nhóm CÓ Ở THỜI VĂN LANG hoặc CHƯA CÓ.', [['Nhà sàn', 'CÓ Ở THỜI VĂN LANG'], ['Thuyền độc mộc', 'CÓ Ở THỜI VĂN LANG'], ['Điện thoại', 'CHƯA CÓ'], ['Internet', 'CHƯA CÓ']], 'Nhà sàn và thuyền độc mộc có từ thời Văn Lang.', $d);
        $this->sortQ($s, 'Kéo mỗi câu chuyện, nhân vật vào nhóm TRUYỀN THUYẾT hoặc LỊCH SỬ.', [['Lạc Long Quân – Âu Cơ', 'TRUYỀN THUYẾT'], ['Sơn Tinh – Thủy Tinh', 'TRUYỀN THUYẾT'], ['Đinh Bộ Lĩnh', 'LỊCH SỬ'], ['Hai Bà Trưng', 'LỊCH SỬ']], 'Lạc Long Quân, Sơn Tinh là truyền thuyết; Đinh Bộ Lĩnh, Hai Bà là lịch sử.', $d);
        $this->fill($s, 'Vị vua đầu tiên của nước Văn Lang là Hùng ___.', [[0, 'Vương']], 'Hùng Vương là vua đầu tiên.', $d);
        $this->fill($s, 'Đền thờ các vua Hùng ở tỉnh Phú ___.', [[0, 'Thọ']], 'Đền Hùng ở Phú Thọ.', $d);
        $this->fill($s, 'Ngày Giỗ Tổ Hùng Vương là mùng 10 tháng 3 âm ___.', [[0, 'lịch']], 'Mùng 10 tháng 3 âm lịch.', $d);
        $this->fill($s, 'Sản phẩm đúc đồng nổi tiếng thời Văn Lang là trống ___.', [[0, 'đồng']], 'Trống đồng Đông Sơn.', $d);
        $this->fill($s, '"Con Rồng cháu Tiên" gắn với truyền thuyết Lạc Long Quân và Âu ___.', [[0, 'Cơ']], 'Lạc Long Quân – Âu Cơ.', $d);
    }

    private function seedLsDungNuocLop62(): void
    {
        $s = 'ls-dung-nuoc-lop-6-2'; $d = 'de';
        $this->quiz($s, 'An Dương Vương tên thật là gì?', ['Hùng Vương', 'Thục Phán', 'Triệu Đà', 'Cao Lỗ'], 1, 'An Dương Vương tên thật là Thục Phán.', $d);
        $this->quiz($s, 'Thành Cổ Loa có bao nhiêu vòng thành?', ['1', '2', '3', '4'], 2, 'Thành Cổ Loa có 3 vòng thành hình ốc.', $d);
        $this->quiz($s, 'Ai là người chế tạo ra nỏ thần cho quân Âu Lạc?', ['An Dương Vương', 'Cao Lỗ', 'Triệu Đà', 'Mị Châu'], 1, 'Cao Lỗ chế tạo nỏ thần (nỏ liên châu).', $d);
        $this->quiz($s, 'Nước Âu Lạc tồn tại trong khoảng bao nhiêu năm?', ['10', '29', '50', '100'], 1, 'Âu Lạc tồn tại từ 208 TCN đến 179 TCN, khoảng 29 năm.', $d);
        $this->quiz($s, 'Triệu Đà đánh chiếm Âu Lạc vào năm nào?', ['208 TCN', '179 TCN', '111 TCN', '40'], 1, 'Năm 179 TCN, Triệu Đà chiếm Âu Lạc.', $d);
        $this->matching($s, 'Nối mỗi thành tựu của Âu Lạc với mô tả của nó.', [['Thành Cổ Loa', 'Hình xoáy ốc'], ['Nỏ thần', 'Vũ khí lợi hại'], ['Trống đồng', 'Di sản văn hóa']], 'Cổ Loa hình ốc, nỏ thần lợi hại, trống đồng văn hóa.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với việc làm của người đó.', [['An Dương Vương', 'Xây thành Cổ Loa'], ['Cao Lỗ', 'Chế tạo nỏ thần'], ['Mị Châu', 'Người con gái lầm lỡ'], ['Trọng Thủy', 'Con trai Triệu Đà']], 'An Dương Vương xây thành, Cao Lỗ chế nỏ.', $d);
        $this->matching($s, 'Nối mỗi lĩnh vực với thành tựu của Âu Lạc.', [['Quân sự', 'Nỏ thần'], ['Xây dựng', 'Thành Cổ Loa'], ['Nông nghiệp', 'Trồng lúa nước']], 'Quân sự có nỏ thần, xây dựng có Cổ Loa.', $d);
        $this->matching($s, 'Nối mỗi nhà nước với kinh đô của nó.', [['Văn Lang', 'Phong Châu'], ['Âu Lạc', 'Cổ Loa'], ['Vạn Xuân', 'Long Biên']], 'Văn Lang – Phong Châu, Âu Lạc – Cổ Loa, Vạn Xuân – Long Biên.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện của nước Âu Lạc.', [['208 TCN', 'Âu Lạc ra đời'], ['179 TCN', 'Âu Lạc bị Triệu Đà chiếm']], '208 TCN ra đời, 179 TCN bị chiếm.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm THUỘC NƯỚC ÂU LẠC hoặc THUỘC NƯỚC VĂN LANG.', [['Nỏ thần', 'THUỘC ÂU LẠC'], ['Thành Cổ Loa', 'THUỘC ÂU LẠC'], ['Trống đồng', 'THUỘC VĂN LANG'], ['15 bộ', 'THUỘC VĂN LANG']], 'Nỏ thần, Cổ Loa thuộc Âu Lạc; trống đồng, 15 bộ thuộc Văn Lang.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU khi nước Âu Lạc ra đời (208 TCN).', [['Nước Văn Lang', 'TRƯỚC 208 TCN'], ['Các vua Hùng', 'TRƯỚC 208 TCN'], ['Triệu Đà xâm lược', 'SAU 208 TCN'], ['Bắc thuộc', 'SAU 208 TCN']], 'Văn Lang trước 208 TCN; Triệu Đà và Bắc thuộc sau 208 TCN.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật, vũ khí vào nhóm CÓ TRONG TRUYỀN THUYẾT NỎ THẦN hoặc KHÔNG.', [['Cao Lỗ', 'CÓ TRONG TRUYỀN THUYẾT'], ['Mị Châu', 'CÓ TRONG TRUYỀN THUYẾT'], ['Hai Bà Trưng', 'KHÔNG CÓ'], ['Lý Bí', 'KHÔNG CÓ']], 'Cao Lỗ, Mị Châu trong truyền thuyết nỏ thần.', $d);
        $this->sortQ($s, 'Kéo mỗi công trình vào nhóm DO AN DƯƠNG VƯƠNG XÂY hoặc KHÔNG PHẢI.', [['Thành Cổ Loa', 'DO AN DƯƠNG VƯƠNG XÂY'], ['Hoàng thành Thăng Long', 'KHÔNG PHẢI'], ['Kinh đô Huế', 'KHÔNG PHẢI']], 'Thành Cổ Loa do An Dương Vương xây.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm VUA hoặc TƯỚNG, THẦN DÂN.', [['An Dương Vương', 'VUA'], ['Triệu Đà', 'VUA'], ['Cao Lỗ', 'TƯỚNG']], 'An Dương Vương, Triệu Đà là vua; Cao Lỗ là tướng.', $d);
        $this->fill($s, 'An Dương Vương tên thật là Thục ___.', [[0, 'Phán']], 'Thục Phán là tên thật.', $d);
        $this->fill($s, 'Thành Cổ Loa có 3 vòng ___.', [[0, 'thành']], 'Cổ Loa có 3 vòng thành.', $d);
        $this->fill($s, 'Người chế tạo nỏ thần là ___ Lỗ.', [[0, 'Cao']], 'Cao Lỗ chế nỏ thần.', $d);
        $this->fill($s, 'Triệu Đà đánh chiếm Âu Lạc năm 179 trước Công ___.', [[0, 'nguyên']], 'Năm 179 TCN.', $d);
        $this->fill($s, 'Nước Âu Lạc tồn tại khoảng 29 ___.', [[0, 'năm']], 'Âu Lạc tồn tại khoảng 29 năm.', $d);
    }

    private function seedLsDungNuocLop71(): void
    {
        $s = 'ls-dung-nuoc-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Chồng của Trưng Trắc là ai?', ['Thi Sách', 'Tô Định', 'Mã Viện', 'Đỗ Năng Tế'], 0, 'Thi Sách là chồng của Trưng Trắc, bị Tô Định giết hại.', $d);
        $this->quiz($s, 'Thái thú Tô Định cai trị Giao Chỉ như thế nào?', ['Nhân từ', 'Tàn bạo', 'Công bằng', 'Khoan dung'], 1, 'Tô Định cai trị tàn bạo, bóc lột nhân dân.', $d);
        $this->quiz($s, 'Nghĩa quân Hai Bà Trưng đã đánh đuổi quân Hán khỏi đâu?', ['Chỉ Mê Linh', 'Khắp Giao Chỉ', 'Chỉ Luy Lâu', 'Nửa nước'], 1, 'Nghĩa quân làm chủ khắp Giao Chỉ.', $d);
        $this->quiz($s, 'Cuộc khởi nghĩa Hai Bà Trưng kéo dài trong bao lâu?', ['1 năm', '3 năm', '10 năm', '20 năm'], 1, 'Khởi nghĩa kéo dài 3 năm (40 – 43).', $d);
        $this->quiz($s, 'Mã Viện là tướng của triều đại phong kiến nào?', ['Nhà Ngô', 'Nhà Hán', 'Nhà Đường', 'Nhà Tống'], 1, 'Mã Viện là tướng nhà Đông Hán.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với phe của người đó.', [['Trưng Trắc', 'Nghĩa quân'], ['Trưng Nhị', 'Nghĩa quân'], ['Tô Định', 'Quân Hán'], ['Mã Viện', 'Quân Hán']], 'Hai Bà phe nghĩa quân; Tô Định, Mã Viện phe quân Hán.', $d);
        $this->matching($s, 'Nối mỗi chính sách của nhà Hán với hậu quả của nó.', [['Sưu cao thuế nặng', 'Dân khổ cực'], ['Bắt phu phen', 'Dân lầm than'], ['Đồng hóa văn hóa', 'Nguy cơ mất nước']], 'Sưu thuế nặng, phu phen, đồng hóa làm dân khổ.', $d);
        $this->matching($s, 'Nối mỗi cuộc khởi nghĩa với năm bùng nổ của nó.', [['Hai Bà Trưng', 'Năm 40'], ['Bà Triệu', 'Năm 248'], ['Lý Bí', 'Năm 542']], 'Hai Bà 40, Bà Triệu 248, Lý Bí 542.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện của khởi nghĩa Hai Bà Trưng.', [['Năm 40', 'Khởi nghĩa bùng nổ'], ['Năm 42', 'Mã Viện đem quân sang'], ['Năm 43', 'Khởi nghĩa thất bại']], '40 khởi nghĩa, 42 Mã Viện sang, 43 thất bại.', $d);
        $this->matching($s, 'Nối mỗi địa danh với sự kiện gắn với Hai Bà Trưng.', [['Mê Linh', 'Nơi dựng cờ khởi nghĩa'], ['Hát Môn', 'Nơi Hai Bà hi sinh']], 'Mê Linh dựng cờ, Hát Môn nơi hi sinh.', $d);
        $this->sortQ($s, 'Sắp xếp diễn biến: kéo mỗi sự kiện vào nhóm TRƯỚC KHỞI NGHĨA hoặc TRONG/SAU KHỞI NGHĨA.', [['Tô Định cai trị tàn bạo', 'TRƯỚC KHỞI NGHĨA'], ['Thi Sách bị giết', 'TRƯỚC KHỞI NGHĨA'], ['Mã Viện đàn áp', 'TRONG/SAU KHỞI NGHĨA'], ['Hai Bà hi sinh', 'TRONG/SAU KHỞI NGHĨA']], 'Tô Định cai trị và Thi Sách bị giết trước khởi nghĩa.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm NGHĨA QUÂN hoặc QUÂN XÂM LƯỢC.', [['Trưng Nhị', 'NGHĨA QUÂN'], ['Đô Dương', 'NGHĨA QUÂN'], ['Tô Định', 'QUÂN XÂM LƯỢC'], ['Mã Viện', 'QUÂN XÂM LƯỢC']], 'Trưng Nhị, Đô Dương nghĩa quân; Tô Định, Mã Viện quân Hán.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm THỂ HIỆN LÒNG YÊU NƯỚC hoặc KHÔNG.', [['Phất cờ khởi nghĩa', 'YÊU NƯỚC'], ['Hi sinh vì nước', 'YÊU NƯỚC'], ['Đầu hàng giặc', 'KHÔNG YÊU NƯỚC'], ['Bỏ trốn', 'KHÔNG YÊU NƯỚC']], 'Phất cờ và hi sinh thể hiện lòng yêu nước.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm GẮN VỚI HAI BÀ TRƯNG hoặc KHÔNG.', [['Đền Hát Môn', 'GẮN VỚI HAI BÀ'], ['Mê Linh', 'GẮN VỚI HAI BÀ'], ['Đền Hùng', 'KHÔNG GẮN'], ['Thành Cổ Loa', 'KHÔNG GẮN']], 'Đền Hát Môn và Mê Linh gắn với Hai Bà Trưng.', $d);
        $this->sortQ($s, 'Kéo mỗi năm vào nhóm NĂM 40 hoặc NĂM 43.', [['Khởi nghĩa bùng nổ', 'NĂM 40'], ['Hai Bà hi sinh', 'NĂM 43']], 'Năm 40 khởi nghĩa, năm 43 Hai Bà hi sinh.', $d);
        $this->fill($s, 'Chồng của Trưng Trắc là Thi ___.', [[0, 'Sách']], 'Thi Sách là chồng Trưng Trắc.', $d);
        $this->fill($s, 'Thái thú nhà Hán cai trị Giao Chỉ tên là Tô ___.', [[0, 'Định']], 'Tô Định cai trị tàn bạo.', $d);
        $this->fill($s, 'Cuộc khởi nghĩa Hai Bà Trưng kéo dài 3 năm, từ 40 đến ___.', [[0, '43']], 'Khởi nghĩa 40 – 43.', $d);
        $this->fill($s, 'Nhà Hán cử Mã ___ sang đàn áp.', [[0, 'Viện']], 'Mã Viện đàn áp khởi nghĩa.', $d);
        $this->fill($s, 'Hai Bà hi sinh ở cửa sông Hát ___.', [[0, 'Môn']], 'Hai Bà hi sinh ở Hát Môn.', $d);
    }

    private function seedLsDungNuocLop72(): void
    {
        $s = 'ls-dung-nuoc-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Trước khi khởi nghĩa, Ngô Quyền từng giữ chức vụ gì?', ['Tể tướng', 'Thứ sử Giao Châu', 'Thái sư', 'Tướng quân'], 1, 'Ngô Quyền từng là Thứ sử Giao Châu.', $d);
        $this->quiz($s, 'Tướng giặc Nam Hán tử trận trên sông Bạch Đằng là ai?', ['Lưu Nghiễm', 'Lưu Hoằng Thao', 'Hoằng Tháo', 'Lý Khắc Dụng'], 1, 'Lưu Hoằng Thao tử trận ở Bạch Đằng 938.', $d);
        $this->quiz($s, 'Ngô Quyền xưng vương vào năm nào?', ['938', '939', '940', '944'], 1, 'Năm 939, Ngô Quyền xưng vương.', $d);
        $this->quiz($s, 'Ngô Quyền đóng đô ở đâu?', ['Hoa Lư', 'Cổ Loa', 'Thăng Long', 'Đại La'], 1, 'Ngô Quyền đóng đô ở Cổ Loa.', $d);
        $this->quiz($s, 'Kiều Công Tiễn đã làm gì để cầu cứu quân Nam Hán?', ['Dâng đất', 'Giết Dương Đình Nghệ', 'Nộp thuế', 'Cưới công chúa'], 1, 'Kiều Công Tiễn giết Dương Đình Nghệ rồi cầu cứu Nam Hán.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với phe của người đó.', [['Ngô Quyền', 'Phe ta'], ['Lưu Hoằng Thao', 'Phe giặc'], ['Dương Đình Nghệ', 'Phe ta'], ['Kiều Công Tiễn', 'Kẻ phản bội']], 'Ngô Quyền, Dương Đình Nghệ phe ta; Lưu Hoằng Thao phe giặc.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện của buổi đầu độc lập.', [['931', 'Dương Đình Nghệ giành quyền'], ['938', 'Chiến thắng Bạch Đằng'], ['939', 'Ngô Quyền xưng vương']], '931 Dương Đình Nghệ, 938 Bạch Đằng, 939 xưng vương.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với tác dụng của nó trong chiến thắng.', [['Cọc gỗ bịt sắt', 'Đâm thủng thuyền giặc'], ['Thủy triều', 'Nhấn chìm thuyền giặc'], ['Lòng yêu nước', 'Sức mạnh tinh thần']], 'Cọc gỗ đâm thủng thuyền, thủy triều nhấn chìm.', $d);
        $this->matching($s, 'Nối mỗi trận Bạch Đằng với người chỉ huy quân ta.', [['Bạch Đằng 938', 'Ngô Quyền'], ['Bạch Đằng 981', 'Lê Hoàn'], ['Bạch Đằng 1288', 'Trần Hưng Đạo']], '938 Ngô Quyền, 981 Lê Hoàn, 1288 Trần Hưng Đạo.', $d);
        $this->matching($s, 'Nối mỗi địa danh với sự kiện của nó.', [['Cổ Loa', 'Kinh đô của Ngô Quyền'], ['Sông Bạch Đằng', 'Chiến thắng 938']], 'Cổ Loa kinh đô Ngô Quyền; Bạch Đằng chiến thắng 938.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC TRẬN BẠCH ĐẰNG 938 hoặc SAU TRẬN BẠCH ĐẰNG 938.', [['Kiều Công Tiễn giết Dương Đình Nghệ', 'TRƯỚC 938'], ['Nam Hán đem quân sang', 'TRƯỚC 938'], ['Ngô Quyền xưng vương', 'SAU 938'], ['Đóng đô ở Cổ Loa', 'SAU 938']], 'Trước 938: Kiều Công Tiễn phản bội; sau 938: xưng vương, đóng đô.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm PHE TA hoặc PHE GIẶC.', [['Dương Đình Nghệ', 'PHE TA'], ['Ngô Xương Ngập', 'PHE TA'], ['Lưu Hoằng Thao', 'PHE GIẶC'], ['Lưu Nghiễm', 'PHE GIẶC']], 'Dương Đình Nghệ, Ngô Xương Ngập phe ta.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm KẾ SÁCH CỦA NGÔ QUYỀN hoặc KHÔNG PHẢI.', [['Đóng cọc gỗ', 'KẾ SÁCH NGÔ QUYỀN'], ['Lợi dụng thủy triều', 'KẾ SÁCH NGÔ QUYỀN'], ['Đánh trực diện', 'KHÔNG PHẢI'], ['Rút lui', 'KHÔNG PHẢI']], 'Đóng cọc và lợi dụng thủy triều là kế sách của Ngô Quyền.', $d);
        $this->sortQ($s, 'Kéo mỗi ý nghĩa vào nhóm Ý NGHĨA CỦA CHIẾN THẮNG BẠCH ĐẰNG hoặc KHÔNG PHẢI.', [['Chấm dứt Bắc thuộc', 'Ý NGHĨA'], ['Mở nền độc lập', 'Ý NGHĨA'], ['Thống nhất Trung Quốc', 'KHÔNG PHẢI'], ['Mở rộng lãnh thổ', 'KHÔNG PHẢI']], 'Bạch Đằng 938 chấm dứt Bắc thuộc, mở nền độc lập.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm VUA TA hoặc TƯỚNG GIẶC.', [['Ngô Quyền', 'VUA TA'], ['Lưu Hoằng Thao', 'TƯỚNG GIẶC']], 'Ngô Quyền vua ta; Lưu Hoằng Thao tướng giặc.', $d);
        $this->fill($s, 'Trước khi khởi nghĩa, Ngô Quyền giữ chức Thứ sử Giao ___.', [[0, 'Châu']], 'Ngô Quyền là Thứ sử Giao Châu.', $d);
        $this->fill($s, 'Tướng giặc Nam Hán tử trận ở Bạch Đằng là Lưu Hoằng ___.', [[0, 'Thao']], 'Lưu Hoằng Thao tử trận.', $d);
        $this->fill($s, 'Năm 939, Ngô Quyền xưng ___.', [[0, 'vương']], 'Năm 939 xưng vương.', $d);
        $this->fill($s, 'Ngô Quyền đóng đô ở Cổ ___.', [[0, 'Loa']], 'Đóng đô ở Cổ Loa.', $d);
        $this->fill($s, 'Kiều Công Tiễn đã giết Dương Đình ___ để cầu cứu Nam Hán.', [[0, 'Nghệ']], 'Kiều Công Tiễn giết Dương Đình Nghệ.', $d);
    }

    private function seedLsDinhTienLeLop71(): void
    {
        $s = 'ls-dinh-tien-le-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Cha của Đinh Bộ Lĩnh là ai?', ['Đinh Công Trứ', 'Đinh Liễn', 'Đinh Điền', 'Đinh Toàn'], 0, 'Cha Đinh Bộ Lĩnh là Đinh Công Trứ.', $d);
        $this->quiz($s, 'Năm 968, Đinh Bộ Lĩnh đặt niên hiệu là gì?', ['Thái Bình', 'Thiên Phúc', 'Thuận Thiên', 'Long Thụy'], 0, 'Niên hiệu Thái Bình (968).', $d);
        $this->quiz($s, 'Đinh Tiên Hoàng bị sát hại vào năm nào?', ['968', '979', '980', '981'], 1, 'Năm 979, Đinh Tiên Hoàng bị sát hại.', $d);
        $this->quiz($s, 'Thời trẻ, Đinh Bộ Lĩnh thường chơi trò gì với bạn bè?', ['Đánh cờ', 'Tập trận', 'Đá bóng', 'Thả diều'], 1, 'Đinh Bộ Lĩnh chơi trò tập trận, lấy bông lau làm cờ.', $d);
        $this->quiz($s, 'Đinh Bộ Lĩnh thu phục các sứ quân bằng cách nào?', ['Chỉ đánh', 'Vừa đánh vừa thuyết phục', 'Mua chuộc', 'Cầu hòa'], 1, 'Đinh Bộ Lĩnh vừa đánh vừa thu phục các sứ quân.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện thời Đinh.', [['944', 'Ngô Quyền mất'], ['968', 'Đinh Bộ Lĩnh lên ngôi'], ['979', 'Đinh Tiên Hoàng bị sát hại']], '944 Ngô Quyền mất, 968 lên ngôi, 979 bị sát hại.', $d);
        $this->matching($s, 'Nối mỗi tước hiệu với nhân vật của nó.', [['Vạn Thắng Vương', 'Đinh Bộ Lĩnh'], ['Thập đạo tướng quân', 'Lê Hoàn']], 'Vạn Thắng Vương là Đinh Bộ Lĩnh.', $d);
        $this->matching($s, 'Nối mỗi việc làm với ý nghĩa của nó.', [['Đặt quốc hiệu Đại Cồ Việt', 'Khẳng định chủ quyền'], ['Đặt niên hiệu Thái Bình', 'Khẳng định độc lập']], 'Quốc hiệu và niên hiệu khẳng định độc lập, chủ quyền.', $d);
        $this->matching($s, 'Nối mỗi triều đại với kinh đô của nó.', [['Nhà Đinh', 'Hoa Lư'], ['Nhà Tiền Lê', 'Hoa Lư'], ['Nhà Lý', 'Thăng Long']], 'Đinh và Tiền Lê đóng đô Hoa Lư; Lý dời về Thăng Long.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với thời kì của người đó.', [['Ngô Xương Ngập', 'Thời loạn 12 sứ quân'], ['Đinh Liễn', 'Nhà Đinh']], 'Ngô Xương Ngập thời loạn sứ quân; Đinh Liễn nhà Đinh.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC NĂM 968 hoặc SAU NĂM 968.', [['Ngô Quyền mất', 'TRƯỚC 968'], ['Loạn 12 sứ quân', 'TRƯỚC 968'], ['Niên hiệu Thái Bình', 'SAU 968'], ['Đinh Tiên Hoàng bị hại', 'SAU 968']], 'Năm 968 là mốc Đinh Bộ Lĩnh lên ngôi.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm THỜI LOẠN 12 SỨ QUÂN hoặc SAU KHI THỐNG NHẤT.', [['Các sứ quân cát cứ', 'THỜI LOẠN'], ['Đinh Bộ Lĩnh dẹp loạn', 'THỜI LOẠN'], ['Đinh Tiên Hoàng', 'SAU THỐNG NHẤT']], 'Dẹp loạn thuộc thời loạn; làm vua sau thống nhất.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm KINH ĐÔ THỜI ĐINH hoặc KHÔNG PHẢI.', [['Hoa Lư', 'KINH ĐÔ THỜI ĐINH'], ['Cổ Loa', 'KHÔNG PHẢI'], ['Thăng Long', 'KHÔNG PHẢI']], 'Hoa Lư là kinh đô nhà Đinh.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm CÔNG LAO CỦA ĐINH BỘ LĨNH hoặc KHÔNG PHẢI.', [['Thống nhất đất nước', 'CÔNG LAO'], ['Đặt tên nước', 'CÔNG LAO'], ['Đánh thắng Nguyên – Mông', 'KHÔNG PHẢI'], ['Dời đô về Thăng Long', 'KHÔNG PHẢI']], 'Thống nhất và đặt tên nước là công lao của Đinh Bộ Lĩnh.', $d);
        $this->sortQ($s, 'Kéo mỗi vua nhà Đinh vào nhóm VUA ĐẦU hoặc VUA CUỐI.', [['Đinh Tiên Hoàng', 'VUA ĐẦU'], ['Đinh Phế Đế', 'VUA CUỐI']], 'Đinh Tiên Hoàng vua đầu, Đinh Phế Đế vua cuối.', $d);
        $this->fill($s, 'Cha của Đinh Bộ Lĩnh là Đinh Công ___.', [[0, 'Trứ']], 'Đinh Công Trứ là cha.', $d);
        $this->fill($s, 'Năm 968, Đinh Bộ Lĩnh đặt niên hiệu là Thái ___.', [[0, 'Bình']], 'Niên hiệu Thái Bình.', $d);
        $this->fill($s, 'Đinh Tiên Hoàng bị sát hại năm ___.', [[0, '979']], 'Năm 979 bị sát hại.', $d);
        $this->fill($s, 'Thời trẻ, Đinh Bộ Lĩnh thường chơi trò tập ___ với bạn bè.', [[0, 'trận']], 'Chơi trò tập trận.', $d);
        $this->fill($s, 'Đinh Bộ Lĩnh vừa đánh vừa thu ___ các sứ quân.', [[0, 'phục']], 'Vừa đánh vừa thu phục.', $d);
    }

    private function seedLsDinhTienLeLop72(): void
    {
        $s = 'ls-dinh-tien-le-lop-7-2'; $d = 'de';
        $this->quiz($s, 'Lê Hoàn quê ở đâu?', ['Ninh Bình', 'Thanh Hóa', 'Nghệ An', 'Hà Nội'], 1, 'Lê Hoàn quê ở Thanh Hóa (Ái Châu).', $d);
        $this->quiz($s, 'Ai là người có công tôn Lê Hoàn lên ngôi vua?', ['Phạm Cự Lượng', 'Đinh Điền', 'Nguyễn Bặc', 'Phạm Hạp'], 0, 'Phạm Cự Lượng tôn Lê Hoàn lên ngôi.', $d);
        $this->quiz($s, 'Niên hiệu của vua Lê Đại Hành là gì?', ['Thái Bình', 'Thiên Phúc', 'Thuận Thiên', 'Long Thụy'], 1, 'Niên hiệu Thiên Phúc.', $d);
        $this->quiz($s, 'Nhà Tiền Lê tồn tại trong bao nhiêu năm?', ['12', '29', '50', '100'], 1, 'Nhà Tiền Lê tồn tại 29 năm (980 – 1009).', $d);
        $this->quiz($s, 'Vị vua cuối cùng của nhà Tiền Lê là ai?', ['Lê Đại Hành', 'Lê Long Đĩnh', 'Lê Long Việt', 'Lê Hoàn'], 1, 'Lê Long Đĩnh là vua cuối nhà Tiền Lê.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện thời Tiền Lê.', [['979', 'Đinh Tiên Hoàng mất'], ['980', 'Lê Hoàn lên ngôi'], ['1005', 'Lê Hoàn mất'], ['1009', 'Tiền Lê kết thúc']], '979 Đinh mất, 980 Lê Hoàn lên ngôi, 1009 Tiền Lê kết thúc.', $d);
        $this->matching($s, 'Nối mỗi mặt trận năm 981 với quân giặc ở đó.', [['Bạch Đằng', 'Thủy quân Tống'], ['Chi Lăng', 'Bộ binh Tống']], 'Bạch Đằng đánh thủy quân, Chi Lăng đánh bộ binh Tống.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với việc làm của người đó.', [['Phạm Cự Lượng', 'Tôn Lê Hoàn lên ngôi'], ['Dương Vân Nga', 'Trao áo long bào cho Lê Hoàn']], 'Phạm Cự Lượng tôn vua; Dương Vân Nga trao áo long bào.', $d);
        $this->matching($s, 'Nối mỗi triều đại với vị vua đầu tiên của nó.', [['Nhà Đinh', 'Đinh Tiên Hoàng'], ['Nhà Tiền Lê', 'Lê Đại Hành'], ['Nhà Lý', 'Lý Thái Tổ']], 'Đinh – Đinh Tiên Hoàng, Tiền Lê – Lê Đại Hành, Lý – Lý Thái Tổ.', $d);
        $this->matching($s, 'Nối mỗi niên hiệu với vị vua của nó.', [['Thái Bình', 'Đinh Tiên Hoàng'], ['Thiên Phúc', 'Lê Đại Hành']], 'Thái Bình – Đinh Tiên Hoàng, Thiên Phúc – Lê Đại Hành.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC KHI LÊ HOÀN LÊN NGÔI (980) hoặc SAU.', [['Đinh Tiên Hoàng mất', 'TRƯỚC 980'], ['Phạm Cự Lượng tôn vua', 'TRƯỚC 980'], ['Đánh thắng quân Tống', 'SAU 980'], ['Lê Hoàn mất', 'SAU 980']], 'Năm 980 là mốc Lê Hoàn lên ngôi.', $d);
        $this->sortQ($s, 'Kéo mỗi trận đánh vào nhóm CHỐNG QUÂN TỐNG hoặc KHÔNG PHẢI.', [['Bạch Đằng 981', 'CHỐNG TỐNG'], ['Chi Lăng 981', 'CHỐNG TỐNG'], ['Bạch Đằng 938', 'KHÔNG PHẢI'], ['Bạch Đằng 1288', 'KHÔNG PHẢI']], 'Bạch Đằng và Chi Lăng 981 chống quân Tống.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm CHỦ TRƯƠNG CỦA LÊ HOÀN hoặc KHÔNG PHẢI.', [['Đánh giặc giữ nước', 'CHỦ TRƯƠNG'], ['Đóng đô ở Hoa Lư', 'CHỦ TRƯƠNG'], ['Dời đô về Thăng Long', 'KHÔNG PHẢI'], ['Cầu hòa với Tống', 'KHÔNG PHẢI']], 'Lê Hoàn đánh giặc, đóng đô Hoa Lư.', $d);
        $this->sortQ($s, 'Kéo mỗi danh hiệu vào nhóm CỦA LÊ HOÀN hoặc CỦA NGƯỜI KHÁC.', [['Lê Đại Hành', 'CỦA LÊ HOÀN'], ['Đinh Tiên Hoàng', 'CỦA NGƯỜI KHÁC'], ['Lý Thái Tổ', 'CỦA NGƯỜI KHÁC']], 'Lê Đại Hành là hiệu của Lê Hoàn.', $d);
        $this->sortQ($s, 'Kéo mỗi vị vua vào nhóm VUA NHÀ TIỀN LÊ hoặc VUA TRIỀU KHÁC.', [['Lê Đại Hành', 'TIỀN LÊ'], ['Lê Long Đĩnh', 'TIỀN LÊ'], ['Đinh Tiên Hoàng', 'TRIỀU KHÁC'], ['Lý Thái Tổ', 'TRIỀU KHÁC']], 'Lê Đại Hành, Lê Long Đĩnh là vua Tiền Lê.', $d);
        $this->fill($s, 'Lê Hoàn quê ở Thanh ___ (Ái Châu).', [[0, 'Hóa']], 'Lê Hoàn quê Thanh Hóa.', $d);
        $this->fill($s, 'Người có công tôn Lê Hoàn lên ngôi là Phạm Cự ___.', [[0, 'Lượng']], 'Phạm Cự Lượng tôn vua.', $d);
        $this->fill($s, 'Niên hiệu của Lê Đại Hành là Thiên ___.', [[0, 'Phúc']], 'Niên hiệu Thiên Phúc.', $d);
        $this->fill($s, 'Nhà Tiền Lê tồn tại 29 năm, từ 980 đến ___.', [[0, '1009']], 'Tiền Lê 980 – 1009.', $d);
        $this->fill($s, 'Vị vua cuối cùng của nhà Tiền Lê là Lê Long ___.', [[0, 'Đĩnh']], 'Lê Long Đĩnh vua cuối.', $d);
    }

    private function seedLsDinhTienLeLop81(): void
    {
        $s = 'ls-dinh-tien-le-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Nhà nước thời Đinh – Tiền Lê là nhà nước như thế nào?', ['Phong kiến tập quyền', 'Phong kiến phân quyền', 'Chiếm hữu nô lệ', 'Tư bản'], 0, 'Nhà nước Đinh – Tiền Lê là phong kiến tập quyền.', $d);
        $this->quiz($s, 'Trong nhà nước Đinh – Tiền Lê, ai nắm mọi quyền hành?', ['Tể tướng', 'Vua', 'Thái sư', 'Tướng quân'], 1, 'Vua nắm mọi quyền hành.', $d);
        $this->quiz($s, 'Quân đội thời nhà Đinh được tổ chức thành bao nhiêu đạo?', ['5', '10', '12', '15'], 1, 'Quân đội nhà Đinh có 10 đạo (thập đạo quân).', $d);
        $this->quiz($s, '"Thập đạo quân" là quân đội của triều đại nào?', ['Nhà Ngô', 'Nhà Đinh', 'Nhà Lý', 'Nhà Trần'], 1, 'Thập đạo quân là quân đội nhà Đinh.', $d);
        $this->quiz($s, 'Thời Đinh – Tiền Lê, cả nước được chia thành bao nhiêu đạo?', ['5', '10', '12', '15'], 1, 'Cả nước chia thành 10 đạo.', $d);
        $this->matching($s, 'Nối mỗi chức quan với nhiệm vụ của nó.', [['Thái sư', 'Đứng đầu các quan'], ['Tướng quân', 'Chỉ huy quân đội'], ['Quan văn', 'Trị dân, giúp việc']], 'Thái sư đứng đầu, tướng quân chỉ huy, quan văn trị dân.', $d);
        $this->matching($s, 'Nối mỗi bộ phận quân đội với nơi đóng quân của nó.', [['Cấm quân', 'Bảo vệ kinh thành'], ['Quân địa phương', 'Bảo vệ địa phương']], 'Cấm quân bảo vệ kinh thành; quân địa phương ở các đạo.', $d);
        $this->matching($s, 'Nối mỗi việc làm của nhà nước với mục đích của nó.', [['Ban hành luật pháp', 'Giữ kỉ cương'], ['Xây dựng quân đội', 'Bảo vệ đất nước']], 'Luật pháp giữ kỉ cương; quân đội bảo vệ đất nước.', $d);
        $this->matching($s, 'Nối mỗi triều đại với chính sách tổ chức của nó.', [['Nhà Đinh', 'Xây dựng tập quyền'], ['Nhà Tiền Lê', 'Tiếp tục tập quyền']], 'Đinh xây dựng, Tiền Lê tiếp tục nhà nước tập quyền.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với định nghĩa của nó.', [['Tập quyền', 'Quyền lực tập trung vào vua'], ['Phân quyền', 'Quyền lực chia cho địa phương']], 'Tập quyền: vua nắm mọi quyền.', $d);
        $this->sortQ($s, 'Kéo mỗi chức danh vào nhóm QUAN VĂN hoặc QUAN VÕ.', [['Thái sư', 'QUAN VĂN'], ['Quan trị dân', 'QUAN VĂN'], ['Tướng quân', 'QUAN VÕ'], ['Thập đạo tướng quân', 'QUAN VÕ']], 'Thái sư quan văn; tướng quân quan võ.', $d);
        $this->sortQ($s, 'Kéo mỗi bộ phận vào nhóm Ở KINH ĐÔ hoặc Ở ĐỊA PHƯƠNG.', [['Cấm quân', 'Ở KINH ĐÔ'], ['Thái sư', 'Ở KINH ĐÔ'], ['Quân địa phương', 'Ở ĐỊA PHƯƠNG'], ['Quan trấn', 'Ở ĐỊA PHƯƠNG']], 'Cấm quân, thái sư ở kinh đô; quân địa phương, quan trấn ở địa phương.', $d);
        $this->sortQ($s, 'Kéo mỗi biện pháp vào nhóm VỀ CHÍNH TRỊ hoặc VỀ KINH TẾ.', [['Ban hành luật', 'CHÍNH TRỊ'], ['Tổ chức quan lại', 'CHÍNH TRỊ'], ['Khuyến khích nông nghiệp', 'KINH TẾ'], ['Mở mang thủ công', 'KINH TẾ']], 'Luật pháp, quan lại là chính trị; khuyến nông, thủ công là kinh tế.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm NHÀ NƯỚC TẬP QUYỀN hoặc PHÂN TÁN.', [['Vua nắm mọi quyền', 'TẬP QUYỀN'], ['Trung ương mạnh', 'TẬP QUYỀN'], ['Sứ quân cát cứ', 'PHÂN TÁN'], ['Địa phương tự trị', 'PHÂN TÁN']], 'Vua nắm quyền, trung ương mạnh là tập quyền.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ chức vào nhóm NHÀ NƯỚC PHONG KIẾN hoặc KHÔNG PHẢI.', [['Nhà Đinh', 'PHONG KIẾN'], ['Nhà Tiền Lê', 'PHONG KIẾN'], ['Bộ lạc', 'KHÔNG PHẢI'], ['Thị tộc', 'KHÔNG PHẢI']], 'Đinh và Tiền Lê là nhà nước phong kiến.', $d);
        $this->fill($s, 'Nhà nước Đinh – Tiền Lê là nhà nước phong kiến tập ___.', [[0, 'quyền']], 'Nhà nước phong kiến tập quyền.', $d);
        $this->fill($s, 'Vua là người nắm mọi quyền ___ trong nước.', [[0, 'hành']], 'Vua nắm mọi quyền hành.', $d);
        $this->fill($s, 'Quân đội nhà Đinh có 10 ___ quân.', [[0, 'đạo']], 'Thập đạo quân.', $d);
        $this->fill($s, 'Lực lượng bảo vệ vua và kinh thành gọi là cấm ___.', [[0, 'quân']], 'Cấm quân bảo vệ kinh thành.', $d);
        $this->fill($s, 'Thời Đinh – Tiền Lê, cả nước chia thành 10 ___.', [[0, 'đạo']], 'Cả nước 10 đạo.', $d);
    }

    private function seedLsDinhTienLeLop82(): void
    {
        $s = 'ls-dinh-tien-le-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Nhà Đinh – Tiền Lê đặc biệt khuyến khích phát triển nghề gì?', ['Buôn bán', 'Nông nghiệp', 'Đánh cá', 'Khai mỏ'], 1, 'Nông nghiệp là nghề được khuyến khích nhất.', $d);
        $this->quiz($s, 'Tiền đồng Thái Bình do vị vua nào cho đúc?', ['Ngô Quyền', 'Đinh Tiên Hoàng', 'Lê Đại Hành', 'Lý Thái Tổ'], 1, 'Đinh Tiên Hoàng cho đúc tiền Thái Bình.', $d);
        $this->quiz($s, 'Lễ Tịch điền (vua cày ruộng) do vị vua nào khởi xướng?', ['Đinh Tiên Hoàng', 'Lê Đại Hành', 'Lý Thái Tổ', 'Trần Thái Tông'], 1, 'Lê Đại Hành khởi xướng lễ Tịch điền.', $d);
        $this->quiz($s, 'Chùa chiền thời Đinh – Tiền Lê chủ yếu thờ ai?', ['Thần', 'Phật', 'Thánh', 'Tổ tiên'], 1, 'Phật giáo thịnh hành, chùa thờ Phật.', $d);
        $this->quiz($s, 'Kinh đô Hoa Lư có đặc điểm địa thế như thế nào?', ['Đồng bằng', 'Núi non hiểm trở', 'Ven biển', 'Trên đảo'], 1, 'Hoa Lư có núi non hiểm trở, dễ phòng thủ.', $d);
        $this->matching($s, 'Nối mỗi chính sách với vị vua thực hiện nó.', [['Khuyến khích nông nghiệp', 'Lê Đại Hành'], ['Cho đúc tiền Thái Bình', 'Đinh Tiên Hoàng'], ['Mở lễ Tịch điền', 'Lê Đại Hành']], 'Lê Đại Hành khuyến nông, Tịch điền; Đinh Tiên Hoàng đúc tiền.', $d);
        $this->matching($s, 'Nối mỗi sản phẩm với nghề làm ra nó.', [['Gốm sứ', 'Nghề gốm'], ['Vải vóc', 'Nghề dệt'], ['Đồ đồng', 'Nghề đúc đồng']], 'Gốm, vải, đồ đồng là sản phẩm thủ công.', $d);
        $this->matching($s, 'Nối mỗi lễ hội với thời gian của nó.', [['Lễ Tịch điền', 'Đầu xuân'], ['Hội chùa', 'Ngày lễ Phật']], 'Tịch điền đầu xuân; hội chùa ngày lễ Phật.', $d);
        $this->matching($s, 'Nối mỗi tầng lớp với địa vị của nó.', [['Nông dân', 'Đông đảo nhất'], ['Thợ thủ công', 'Làm nghề thủ công'], ['Nhà sư', 'Được trọng vọng']], 'Nông dân đông nhất; nhà sư được trọng vọng.', $d);
        $this->matching($s, 'Nối mỗi di tích với triều đại của nó.', [['Chùa Nhất Trụ', 'Nhà Đinh'], ['Cố đô Hoa Lư', 'Đinh – Tiền Lê']], 'Chùa Nhất Trụ nhà Đinh; Hoa Lư cố đô Đinh – Tiền Lê.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm NÔNG NGHIỆP hoặc THỦ CÔNG NGHIỆP.', [['Cày cấy', 'NÔNG NGHIỆP'], ['Chăn nuôi', 'NÔNG NGHIỆP'], ['Dệt vải', 'THỦ CÔNG NGHIỆP'], ['Đúc đồng', 'THỦ CÔNG NGHIỆP']], 'Cày cấy, chăn nuôi là nông nghiệp; dệt, đúc đồng là thủ công.', $d);
        $this->sortQ($s, 'Kéo mỗi chính sách vào nhóm KHUYẾN KHÍCH SẢN XUẤT hoặc KHÔNG PHẢI.', [['Lễ Tịch điền', 'KHUYẾN KHÍCH'], ['Đúc tiền đồng', 'KHUYẾN KHÍCH'], ['Bỏ ruộng hoang', 'KHÔNG PHẢI'], ['Tăng thuế nặng', 'KHÔNG PHẢI']], 'Tịch điền và đúc tiền khuyến khích sản xuất.', $d);
        $this->sortQ($s, 'Kéo mỗi di tích vào nhóm THỜI ĐINH – TIỀN LÊ hoặc THỜI KHÁC.', [['Chùa Nhất Trụ', 'ĐINH – TIỀN LÊ'], ['Cố đô Hoa Lư', 'ĐINH – TIỀN LÊ'], ['Chùa Một Cột', 'THỜI KHÁC'], ['Cố đô Huế', 'THỜI KHÁC']], 'Chùa Nhất Trụ và Hoa Lư thời Đinh – Tiền Lê.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm ĐỜI SỐNG VẬT CHẤT hoặc ĐỜI SỐNG TINH THẦN.', [['Ăn mặc', 'VẬT CHẤT'], ['Nhà ở', 'VẬT CHẤT'], ['Tín ngưỡng', 'TINH THẦN'], ['Lễ hội', 'TINH THẦN']], 'Ăn mặc, nhà ở vật chất; tín ngưỡng, lễ hội tinh thần.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm TRỒNG TRỌT hoặc CHĂN NUÔI.', [['Trồng lúa', 'TRỒNG TRỌT'], ['Trồng khoai', 'TRỒNG TRỌT'], ['Nuôi trâu', 'CHĂN NUÔI'], ['Nuôi lợn', 'CHĂN NUÔI']], 'Trồng lúa, khoai là trồng trọt; nuôi trâu, lợn là chăn nuôi.', $d);
        $this->fill($s, 'Tiền đồng Thái Bình do vua Đinh Tiên ___ cho đúc.', [[0, 'Hoàng']], 'Đinh Tiên Hoàng đúc tiền Thái Bình.', $d);
        $this->fill($s, 'Lễ Tịch điền do vua Lê Đại ___ khởi xướng.', [[0, 'Hành']], 'Lê Đại Hành mở lễ Tịch điền.', $d);
        $this->fill($s, 'Chùa chiền thời Đinh – Tiền Lê chủ yếu thờ ___.', [[0, 'Phật']], 'Phật giáo thịnh hành.', $d);
        $this->fill($s, 'Kinh đô Hoa Lư có địa thế núi non hiểm ___.', [[0, 'trở']], 'Hoa Lư hiểm trở.', $d);
        $this->fill($s, 'Nhà Đinh – Tiền Lê rất coi trọng nghề ___.', [[0, 'nông']], 'Coi trọng nông nghiệp.', $d);
    }

    private function seedLsChongNguyenMongLop81(): void
    {
        $s = 'ls-chong-nguyen-mong-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Thượng hoàng nào cùng vua Trần chỉ đạo kháng chiến lần 2 và lần 3?', ['Trần Thái Tông', 'Trần Thánh Tông', 'Trần Nhân Tông', 'Trần Anh Tông'], 1, 'Thượng hoàng Trần Thánh Tông cùng vua Trần Nhân Tông chỉ đạo.', $d);
        $this->quiz($s, 'Năm 1283, Trần Quốc Tuấn được cử giữ chức vụ gì?', ['Tể tướng', 'Quốc công Tiết chế', 'Thái sư', 'Tướng quân'], 1, 'Trần Quốc Tuấn làm Quốc công Tiết chế thống lĩnh quân đội.', $d);
        $this->quiz($s, 'Quân Nguyên lần thứ hai xâm lược theo mấy hướng?', ['1', '2', '3', '4'], 1, 'Lần 2 giặc tiến theo 2 hướng: đường bộ và đường thủy.', $d);
        $this->quiz($s, 'Trận Hàm Tử – Chương Dương diễn ra vào năm nào?', ['1258', '1285', '1288', '1287'], 1, 'Hàm Tử – Chương Dương năm 1285.', $d);
        $this->quiz($s, 'Sau thắng lợi, nhà Trần đã đối xử với tù binh Nguyên như thế nào?', ['Giết hết', 'Tha cho về nước', 'Bắt làm nô lệ', 'Giam cầm'], 1, 'Nhà Trần tha tù binh về nước, thể hiện lòng nhân đạo.', $d);
        $this->matching($s, 'Nối mỗi lần kháng chiến với tướng giặc chỉ huy.', [['Lần 1 (1258)', 'Ngột Lương Hợp Thai'], ['Lần 2 (1285)', 'Thoát Hoan'], ['Lần 3 (1287 – 1288)', 'Thoát Hoan']], 'Lần 1 Ngột Lương Hợp Thai; lần 2, 3 Thoát Hoan.', $d);
        $this->matching($s, 'Nối mỗi danh tướng với trận đánh của ông.', [['Trần Nhật Duật', 'Hàm Tử'], ['Trần Quang Khải', 'Chương Dương'], ['Trần Khánh Dư', 'Vân Đồn']], 'Trần Nhật Duật – Hàm Tử, Trần Quang Khải – Chương Dương, Trần Khánh Dư – Vân Đồn.', $d);
        $this->matching($s, 'Nối mỗi trận đánh với năm diễn ra của nó.', [['Hàm Tử', '1285'], ['Chương Dương', '1285'], ['Bạch Đằng', '1288'], ['Vân Đồn', '1287']], 'Hàm Tử, Chương Dương 1285; Vân Đồn 1287; Bạch Đằng 1288.', $d);
        $this->matching($s, 'Nối mỗi chủ trương với người đề xướng nó.', [['Vườn không nhà trống', 'Trần Hưng Đạo'], ['Đánh lâu dài', 'Nhà Trần']], 'Trần Hưng Đạo đề xướng vườn không nhà trống.', $d);
        $this->matching($s, 'Nối mỗi hội nghị với năm diễn ra của nó.', [['Bình Than', '1282'], ['Diên Hồng', '1284']], 'Bình Than 1282, Diên Hồng 1284.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm LẦN 1 (1258) hoặc LẦN 2 – 3.', [['Ngột Lương Hợp Thai xâm lược', 'LẦN 1'], ['Trần Thủ Độ chỉ huy', 'LẦN 1'], ['Ô Mã Nhi xâm lược', 'LẦN 2 – 3'], ['Trận Vân Đồn', 'LẦN 2 – 3']], 'Lần 1: Ngột Lương Hợp Thai; lần 2–3: Ô Mã Nhi, Vân Đồn.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm KẾ SÁCH CỦA NHÀ TRẦN hoặc KHÔNG PHẢI.', [['Tiêu thổ kháng chiến', 'KẾ SÁCH NHÀ TRẦN'], ['Đánh du kích', 'KẾ SÁCH NHÀ TRẦN'], ['Đối đầu trực diện', 'KHÔNG PHẢI'], ['Đầu hàng giặc', 'KHÔNG PHẢI']], 'Tiêu thổ và du kích là kế sách nhà Trần.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm TƯỚNG NHÀ TRẦN hoặc TƯỚNG GIẶC.', [['Trần Nhật Duật', 'TƯỚNG NHÀ TRẦN'], ['Trần Khánh Dư', 'TƯỚNG NHÀ TRẦN'], ['Ngột Lương Hợp Thai', 'TƯỚNG GIẶC'], ['Toa Đô', 'TƯỚNG GIẶC']], 'Trần Nhật Duật, Trần Khánh Dư tướng ta.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm NGUYÊN NHÂN THẮNG LỢI hoặc KHÔNG PHẢI.', [['Lòng yêu nước', 'NGUYÊN NHÂN'], ['Nghệ thuật quân sự', 'NGUYÊN NHÂN'], ['Giặc rất mạnh', 'KHÔNG PHẢI'], ['Giặc rất đông', 'KHÔNG PHẢI']], 'Lòng yêu nước và nghệ thuật quân sự là nguyên nhân thắng lợi.', $d);
        $this->sortQ($s, 'Kéo mỗi trận đánh vào nhóm THỦY CHIẾN hoặc BỘ CHIẾN.', [['Bạch Đằng', 'THỦY CHIẾN'], ['Vân Đồn', 'THỦY CHIẾN'], ['Chương Dương', 'BỘ CHIẾN'], ['Hàm Tử', 'BỘ CHIẾN']], 'Bạch Đằng, Vân Đồn thủy chiến; Chương Dương, Hàm Tử bộ chiến.', $d);
        $this->fill($s, 'Thượng hoàng cùng vua chỉ đạo kháng chiến lần 2, 3 là Trần Thánh ___.', [[0, 'Tông']], 'Trần Thánh Tông là thượng hoàng.', $d);
        $this->fill($s, 'Năm 1283, Trần Quốc Tuấn được cử làm Quốc công Tiết ___.', [[0, 'chế']], 'Quốc công Tiết chế.', $d);
        $this->fill($s, 'Trận Hàm Tử – Chương Dương diễn ra năm ___.', [[0, '1285']], 'Năm 1285.', $d);
        $this->fill($s, 'Tướng giặc chỉ huy lần thứ nhất là Ngột Lương Hợp ___.', [[0, 'Thai']], 'Ngột Lương Hợp Thai.', $d);
        $this->fill($s, 'Trận Vân Đồn năm 1287 do Trần Khánh ___ chỉ huy.', [[0, 'Dư']], 'Trần Khánh Dư chỉ huy Vân Đồn.', $d);
    }

    private function seedLsChongNguyenMongLop82(): void
    {
        $s = 'ls-chong-nguyen-mong-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Trận Bạch Đằng năm 1288 diễn ra vào tháng mấy?', ['Tháng 1', 'Tháng 4', 'Tháng 8', 'Tháng 12'], 1, 'Trận Bạch Đằng 1288 diễn ra vào tháng 4.', $d);
        $this->quiz($s, 'Đạo quân nào của giặc bị tiêu diệt hoàn toàn ở Bạch Đằng 1288?', ['Đạo bộ binh của Thoát Hoan', 'Đạo thủy binh của Ô Mã Nhi', 'Đạo quân của Toa Đô', 'Quân Chiêm Thành'], 1, 'Đạo thủy binh của Ô Mã Nhi bị tiêu diệt ở Bạch Đằng.', $d);
        $this->quiz($s, 'Thoát Hoan đã thoát chết bằng cách nào?', ['Đầu hàng', 'Chui vào ống đồng chạy về', 'Trốn trong rừng', 'Vượt biển'], 1, 'Thoát Hoan chui vào ống đồng để chạy về nước.', $d);
        $this->quiz($s, 'Sau chiến thắng Bạch Đằng 1288, quân Nguyên còn xâm lược Đại Việt nữa không?', ['Còn nhiều lần', 'Không còn nữa', 'Còn 1 lần', 'Không rõ'], 1, 'Sau 1288, quân Nguyên không dám xâm lược nữa.', $d);
        $this->quiz($s, 'Cọc gỗ ở sông Bạch Đằng được đóng xuống vào thời điểm nào?', ['Khi giặc đến', 'Trước khi giặc đến, lúc nước triều xuống', 'Sau trận đánh', 'Khi nước lên'], 1, 'Cọc được đóng trước, khi thủy triều xuống mới nhô lên.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với tác dụng của nó trong trận Bạch Đằng.', [['Cọc gỗ bịt sắt', 'Đâm thủng thuyền giặc'], ['Thủy triều rút', 'Làm thuyền mắc cọc'], ['Thuyền nhẹ của ta', 'Dụ giặc vào bãi cọc']], 'Cọc đâm thủng thuyền, triều rút mắc cọc, thuyền nhẹ dụ giặc.', $d);
        $this->matching($s, 'Nối mỗi tướng giặc với kết cục của hắn trong trận 1288.', [['Ô Mã Nhi', 'Bị bắt sống'], ['Phàn Tiếp', 'Bị giết'], ['Thoát Hoan', 'Chạy thoát']], 'Ô Mã Nhi bị bắt, Phàn Tiếp bị giết, Thoát Hoan chạy thoát.', $d);
        $this->matching($s, 'Nối mỗi trận Bạch Đằng với năm diễn ra của nó.', [['Bạch Đằng 938', 'Ngô Quyền'], ['Bạch Đằng 981', 'Lê Hoàn'], ['Bạch Đằng 1288', 'Trần Hưng Đạo']], '938 Ngô Quyền, 981 Lê Hoàn, 1288 Trần Hưng Đạo.', $d);
        $this->matching($s, 'Nối mỗi nơi với việc làm ở đó trước trận đánh.', [['Sông Bạch Đằng', 'Đóng cọc gỗ'], ['Vạn Kiếp', 'Tập kết quân ta']], 'Bạch Đằng đóng cọc, Vạn Kiếp tập kết quân.', $d);
        $this->matching($s, 'Nối mỗi đạo quân Nguyên với đường tiến quân của nó.', [['Ô Mã Nhi', 'Đường thủy'], ['Thoát Hoan', 'Đường bộ']], 'Ô Mã Nhi đường thủy, Thoát Hoan đường bộ.', $d);
        $this->sortQ($s, 'Kéo mỗi bước vào nhóm TRƯỚC KHI ĐÁNH hoặc TRONG KHI ĐÁNH (trận Bạch Đằng 1288).', [['Đóng cọc gỗ', 'TRƯỚC KHI ĐÁNH'], ['Dụ giặc vào', 'TRƯỚC KHI ĐÁNH'], ['Thuyền giặc mắc cọc', 'TRONG KHI ĐÁNH'], ['Quân ta tấn công', 'TRONG KHI ĐÁNH']], 'Trước: đóng cọc, dụ giặc; trong: mắc cọc, tấn công.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm TƯỚNG TA hoặc TƯỚNG GIẶC (năm 1288).', [['Trần Hưng Đạo', 'TƯỚNG TA'], ['Các vương hầu', 'TƯỚNG TA'], ['Ô Mã Nhi', 'TƯỚNG GIẶC'], ['Phàn Tiếp', 'TƯỚNG GIẶC']], 'Trần Hưng Đạo tướng ta; Ô Mã Nhi, Phàn Tiếp tướng giặc.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm KẾ SÁCH CỦA TRẦN HƯNG ĐẠO hoặc KHÔNG PHẢI.', [['Học kế của Ngô Quyền', 'KẾ SÁCH'], ['Đóng cọc gỗ', 'KẾ SÁCH'], ['Dàn trận đối đầu', 'KHÔNG PHẢI'], ['Xin hàng giặc', 'KHÔNG PHẢI']], 'Học Ngô Quyền, đóng cọc là kế sách của Trần Hưng Đạo.', $d);
        $this->sortQ($s, 'Kéo mỗi chi tiết vào nhóm CỦA TRẬN BẠCH ĐẰNG 1288 hoặc KHÔNG PHẢI.', [['Tháng 4 năm 1288', 'CỦA 1288'], ['Ô Mã Nhi bị bắt', 'CỦA 1288'], ['Năm 938', 'KHÔNG PHẢI'], ['Năm 981', 'KHÔNG PHẢI']], 'Tháng 4/1288 và Ô Mã Nhi bị bắt thuộc trận 1288.', $d);
        $this->sortQ($s, 'Kéo mỗi tướng giặc vào nhóm ĐẠO THỦY hoặc ĐẠO BỘ (quân Nguyên 1288).', [['Ô Mã Nhi', 'ĐẠO THỦY'], ['Phàn Tiếp', 'ĐẠO THỦY'], ['Thoát Hoan', 'ĐẠO BỘ']], 'Ô Mã Nhi, Phàn Tiếp đạo thủy; Thoát Hoan đạo bộ.', $d);
        $this->fill($s, 'Trận Bạch Đằng 1288 diễn ra vào tháng ___.', [[0, '4']], 'Tháng 4 năm 1288.', $d);
        $this->fill($s, 'Đạo thủy binh của giặc do Ô Mã ___ chỉ huy.', [[0, 'Nhi']], 'Ô Mã Nhi chỉ huy đạo thủy.', $d);
        $this->fill($s, 'Thoát Hoan thoát chết phải chui vào ống ___ chạy về.', [[0, 'đồng']], 'Chui ống đồng chạy về.', $d);
        $this->fill($s, 'Cọc gỗ được đóng xuống lòng sông trước khi ___ đến.', [[0, 'giặc']], 'Đóng cọc trước khi giặc đến.', $d);
        $this->fill($s, 'Sau chiến thắng Bạch Đằng 1288, quân Nguyên không dám xâm lược Đại Việt ___.', [[0, 'nữa']], 'Giặc không dám xâm lược nữa.', $d);
    }

    private function seedLsChongNguyenMongLop91(): void
    {
        $s = 'ls-chong-nguyen-mong-lop-9-1'; $d = 'trung_binh';
        $this->quiz($s, 'Trần Hưng Đạo là con trai của ai?', ['Trần Thái Tông', 'Trần Liễu', 'Trần Thủ Độ', 'Trần Quang Khải'], 1, 'Trần Hưng Đạo (Trần Quốc Tuấn) là con của An Sinh Vương Trần Liễu.', $d);
        $this->quiz($s, 'Trần Hưng Đạo được phong tước hiệu gì?', ['Hưng Đạo Vương', 'Chiêu Minh Vương', 'Thái úy', 'Quốc công'], 0, 'Trần Quốc Tuấn được phong Hưng Đạo Vương.', $d);
        $this->quiz($s, 'Trần Hưng Đạo qua đời vào năm nào?', ['1288', '1300', '1314', '1228'], 1, 'Trần Hưng Đạo mất năm 1300.', $d);
        $this->quiz($s, 'Trần Hưng Đạo có quan hệ như thế nào với vua Trần Nhân Tông?', ['Cha con', 'Ông cháu', 'Chú cháu', 'Anh em'], 2, 'Trần Hưng Đạo là chú họ của vua Trần Nhân Tông.', $d);
        $this->quiz($s, 'Địa danh Vạn Kiếp gắn với nhân vật lịch sử nào?', ['Trần Quang Khải', 'Trần Hưng Đạo', 'Trần Khánh Dư', 'Phạm Ngũ Lão'], 1, 'Vạn Kiếp là căn cứ, phủ đệ của Trần Hưng Đạo.', $d);
        $this->matching($s, 'Nối mỗi địa danh với sự kiện gắn với Trần Hưng Đạo.', [['Vạn Kiếp', 'Căn cứ của Trần Hưng Đạo'], ['Sông Bạch Đằng', 'Nơi đánh thắng quân Nguyên']], 'Vạn Kiếp căn cứ; Bạch Đằng nơi thắng trận.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện trong cuộc đời Trần Hưng Đạo.', [['1228', 'Trần Quốc Tuấn ra đời'], ['1283', 'Được cử làm Tiết chế'], ['1300', 'Trần Hưng Đạo qua đời']], '1228 sinh, 1283 Tiết chế, 1300 mất.', $d);
        $this->matching($s, 'Nối mỗi câu nói với nhân vật đã nói ra nó.', [['"Đầu tôi chưa rơi xuống đất, xin bệ hạ đừng lo"', 'Trần Hưng Đạo'], ['"Đánh!"', 'Các bô lão ở Diên Hồng']], 'Trần Hưng Đạo trấn an vua; bô lão hô đánh.', $d);
        $this->matching($s, 'Nối mỗi tác phẩm của Trần Hưng Đạo với thể loại của nó.', [['Hịch tướng sĩ', 'Áng thiên cổ hùng văn'], ['Binh thư yếu lược', 'Sách quân sự']], 'Hịch tướng sĩ là áng hùng văn; Binh thư yếu lược là binh thư.', $d);
        $this->matching($s, 'Nối mỗi người với quan hệ của họ với Trần Hưng Đạo.', [['Trần Liễu', 'Cha'], ['Trần Nhân Tông', 'Cháu (vua)']], 'Trần Liễu là cha; Trần Nhân Tông là cháu.', $d);
        $this->sortQ($s, 'Kéo mỗi câu văn vào nhóm CÂU TRONG HỊCH TƯỚNG SĨ hoặc KHÔNG PHẢI.', [['Ta thường tới bữa quên ăn, nửa đêm vỗ gối', 'TRONG HỊCH'], ['Nam quốc sơn hà Nam đế cư', 'KHÔNG PHẢI'], ['Nay các ngươi ngồi nhìn chủ nhục mà không biết lo', 'TRONG HỊCH']], 'Hai câu đầu trong Hịch tướng sĩ; Nam quốc sơn hà là bài thơ khác.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU khi Trần Hưng Đạo mất (1300).', [['Kháng chiến chống Nguyên', 'TRƯỚC 1300'], ['Trần Hưng Đạo làm Tiết chế', 'TRƯỚC 1300'], ['Khởi nghĩa Lam Sơn', 'SAU 1300']], 'Kháng chiến chống Nguyên trước 1300; Lam Sơn sau 1300.', $d);
        $this->sortQ($s, 'Kéo mỗi khẩu hiệu, hành động vào nhóm THỂ HIỆN TINH THẦN ĐÁNH GIẶC hoặc KHÔNG.', [['Sát Thát', 'ĐÁNH GIẶC'], ['Hô "Đánh!"', 'ĐÁNH GIẶC'], ['Xin hàng giặc', 'KHÔNG ĐÁNH'], ['Bỏ chạy', 'KHÔNG ĐÁNH']], 'Sát Thát và hô đánh thể hiện quyết tâm.', $d);
        $this->sortQ($s, 'Kéo mỗi câu nói vào nhóm CỦA TRẦN HƯNG ĐẠO hoặc CỦA NGƯỜI KHÁC.', [['Đầu tôi chưa rơi xuống đất, xin bệ hạ đừng lo', 'CỦA TRẦN HƯNG ĐẠO'], ['Ta thà làm ma nước Nam chứ không thèm làm vương đất Bắc', 'CỦA NGƯỜI KHÁC']], 'Câu đầu của Trần Hưng Đạo; câu sau của Trần Bình Trọng.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm THỜI TRẦN hoặc THỜI KHÁC.', [['Hịch tướng sĩ', 'THỜI TRẦN'], ['Bạch Đằng 1288', 'THỜI TRẦN'], ['Bình Ngô đại cáo', 'THỜI KHÁC'], ['Ngọc Hồi – Đống Đa', 'THỜI KHÁC']], 'Hịch tướng sĩ và Bạch Đằng 1288 thời Trần.', $d);
        $this->fill($s, 'Trần Hưng Đạo là con của Trần ___.', [[0, 'Liễu']], 'Cha là Trần Liễu.', $d);
        $this->fill($s, 'Trần Hưng Đạo được phong tước Hưng Đạo ___.', [[0, 'Vương']], 'Hưng Đạo Vương.', $d);
        $this->fill($s, 'Căn cứ và phủ đệ của Trần Hưng Đạo ở Vạn ___.', [[0, 'Kiếp']], 'Vạn Kiếp.', $d);
        $this->fill($s, 'Trần Hưng Đạo mất năm ___.', [[0, '1300']], 'Mất năm 1300.', $d);
        $this->fill($s, 'Ở hội nghị Diên Hồng, các bô lão đồng thanh hô ___.', [[0, 'Đánh']], 'Các bô lão hô "Đánh!".', $d);
    }

    private function seedLsChongNguyenMongLop92(): void
    {
        $s = 'ls-chong-nguyen-mong-lop-9-2'; $d = 'kho';
        $this->quiz($s, 'Thắng lợi chống Nguyên – Mông đã đập tan tham vọng gì của nhà Nguyên?', ['Mở rộng sang châu Âu', 'Xâm lược Đại Việt, mở rộng xuống Đông Nam Á', 'Chinh phục Nhật Bản', 'Đánh chiếm Ấn Độ'], 1, 'Thắng lợi đập tan tham vọng xâm lược Đại Việt của nhà Nguyên.', $d);
        $this->quiz($s, 'Nhà Trần đã củng cố khối đại đoàn kết dân tộc bằng việc làm nào?', ['Tăng thuế', 'Mở hội nghị Diên Hồng, Bình Than', 'Cấm buôn bán', 'Xây thành'], 1, 'Hội nghị Bình Than, Diên Hồng huy động toàn dân.', $d);
        $this->quiz($s, 'Sau thất bại ở Đại Việt, nhà Nguyên buộc phải làm gì?', ['Tiếp tục xâm lược', 'Từ bỏ ý định xâm lược Đại Việt', 'Đánh Chiêm Thành', 'Cầu hòa nhà Tống'], 1, 'Nhà Nguyên từ bỏ ý định xâm lược Đại Việt.', $d);
        $this->quiz($s, 'Vị thượng hoàng nào có công lớn trong cả ba lần kháng chiến?', ['Trần Thái Tông', 'Trần Thánh Tông', 'Trần Nhân Tông', 'Trần Anh Tông'], 1, 'Thượng hoàng Trần Thánh Tông cùng vua chỉ đạo kháng chiến.', $d);
        $this->quiz($s, 'Bài học "lấy dân làm gốc" được nhà Trần thể hiện qua việc nào?', ['Bóc lột dân', 'Dựa vào dân, toàn dân đánh giặc', 'Cấm dân ra trận', 'Thu thuế nặng'], 1, 'Nhà Trần dựa vào dân, phát huy toàn dân kháng chiến.', $d);
        $this->matching($s, 'Nối mỗi thắng lợi với ý nghĩa quốc tế của nó.', [['Ba lần kháng chiến thắng lợi', 'Bảo vệ nền độc lập dân tộc'], ['Bạch Đằng 1288', 'Ngăn bước tiến của quân Nguyên']], 'Thắng lợi bảo vệ độc lập, ngăn bước tiến quân Nguyên.', $d);
        $this->matching($s, 'Nối mỗi bài học với biểu hiện của nó.', [['Đoàn kết toàn dân', 'Vua tôi đồng lòng, toàn dân đánh giặc'], ['Nghệ thuật quân sự', 'Vườn không nhà trống, đánh lâu dài']], 'Đoàn kết toàn dân; nghệ thuật vườn không nhà trống.', $d);
        $this->matching($s, 'Nối mỗi chủ trương với nội dung của nó.', [['Khoan thư sức dân', 'Chăm lo đời sống nhân dân'], ['Sát Thát', 'Quyết tâm đánh giặc']], 'Khoan thư sức dân chăm lo dân; Sát Thát quyết đánh.', $d);
        $this->matching($s, 'Nối mỗi nhân tố với vai trò của nó trong thắng lợi.', [['Lòng yêu nước', 'Sức mạnh tinh thần'], ['Vua tôi đồng lòng', 'Sức mạnh tổ chức']], 'Lòng yêu nước là sức mạnh tinh thần.', $d);
        $this->matching($s, 'Nối mỗi chính sách của nhà Trần với mục đích của nó.', [['Khoan thư sức dân', 'Làm kế sâu rễ bền gốc'], ['Xây dựng quân đội', 'Sẵn sàng đánh giặc']], 'Khoan thư sức dân để sâu rễ bền gốc.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm NGUYÊN NHÂN THẮNG LỢI hoặc HẬU QUẢ CỦA THẮNG LỢI.', [['Lòng yêu nước', 'NGUYÊN NHÂN'], ['Vua tôi đồng lòng', 'NGUYÊN NHÂN'], ['Giữ vững độc lập', 'HẬU QUẢ'], ['Nâng cao vị thế', 'HẬU QUẢ']], 'Nguyên nhân: yêu nước, đồng lòng; hậu quả: độc lập, vị thế.', $d);
        $this->sortQ($s, 'Kéo mỗi ý nghĩa vào nhóm VỀ ĐỐI NỘI hoặc ĐỐI NGOẠI.', [['Củng cố đoàn kết dân tộc', 'ĐỐI NỘI'], ['Phát triển kinh tế', 'ĐỐI NỘI'], ['Ngăn Nguyên mở rộng', 'ĐỐI NGOẠI'], ['Nâng cao vị thế', 'ĐỐI NGOẠI']], 'Đối nội: đoàn kết, kinh tế; đối ngoại: ngăn giặc, vị thế.', $d);
        $this->sortQ($s, 'Kéo mỗi bài học vào nhóm BÀI HỌC CHO HÔM NAY hoặc KHÔNG PHẢI.', [['Đoàn kết dân tộc', 'BÀI HỌC'], ['Quốc phòng toàn dân', 'BÀI HỌC'], ['Ỷ lại vào người khác', 'KHÔNG PHẢI'], ['Chia rẽ nội bộ', 'KHÔNG PHẢI']], 'Đoàn kết và quốc phòng toàn dân là bài học.', $d);
        $this->sortQ($s, 'Kéo mỗi ý nghĩa vào nhóm Ý NGHĨA LỊCH SỬ hoặc Ý NGHĨA HIỆN THỰC.', [['Bảo vệ độc lập dân tộc', 'LỊCH SỬ'], ['Đập tan tham vọng Nguyên', 'LỊCH SỬ'], ['Bài học dựng nước', 'HIỆN THỰC'], ['Bài học giữ nước', 'HIỆN THỰC']], 'Lịch sử: bảo vệ độc lập; hiện thực: bài học dựng, giữ nước.', $d);
        $this->sortQ($s, 'Kéo mỗi chiến công vào nhóm CỦA NHÀ TRẦN hoặc CỦA TRIỀU ĐẠI KHÁC.', [['Ba lần chống Nguyên – Mông', 'NHÀ TRẦN'], ['Chống quân Tống', 'TRIỀU KHÁC'], ['Chống quân Thanh', 'TRIỀU KHÁC']], 'Ba lần chống Nguyên – Mông là của nhà Trần.', $d);
        $this->fill($s, 'Thắng lợi chống Nguyên – Mông đã đập tan tham vọng xâm lược của nhà ___.', [[0, 'Nguyên']], 'Đập tan tham vọng nhà Nguyên.', $d);
        $this->fill($s, 'Bài học lớn nhất là phát huy sức mạnh đại đoàn kết toàn ___.', [[0, 'dân']], 'Đoàn kết toàn dân.', $d);
        $this->fill($s, 'Nhà Trần thực hiện chính sách khoan thư sức ___ để làm kế sâu rễ bền gốc.', [[0, 'dân']], 'Khoan thư sức dân.', $d);
        $this->fill($s, 'Thắng lợi ba lần kháng chiến giữ vững nền độc ___ dân tộc.', [[0, 'lập']], 'Giữ vững độc lập.', $d);
        $this->fill($s, 'Nghệ thuật quân sự nhà Trần là lấy ít địch ___, lấy yếu chống mạnh.', [[0, 'nhiều']], 'Lấy ít địch nhiều.', $d);
    }

    private function seedLichSuThpt10Lop101(): void
    {
        $s = 'lich-su-thpt-10-lop-10-1'; $d = 'trung_binh';
        $this->quiz($s, 'Dương Đình Nghệ giành quyền tự chủ vào năm nào?', ['905', '931', '938', '939'], 1, 'Năm 931, Dương Đình Nghệ giành quyền tự chủ.', $d);
        $this->quiz($s, 'Ngô Quyền qua đời vào năm nào?', ['939', '944', '950', '965'], 1, 'Ngô Quyền mất năm 944.', $d);
        $this->quiz($s, 'Đinh Tiên Hoàng đặt niên hiệu là gì?', ['Thái Bình', 'Thiên Phúc', 'Thuận Thiên', 'Long Thụy'], 0, 'Niên hiệu Thái Bình (968).', $d);
        $this->quiz($s, 'Năm 979, sau khi Đinh Tiên Hoàng mất, ai lên ngôi vua?', ['Đinh Liễn', 'Đinh Toàn (Đinh Phế Đế)', 'Lê Hoàn', 'Ngô Xương Văn'], 1, 'Đinh Toàn lên ngôi, hiệu Đinh Phế Đế.', $d);
        $this->quiz($s, 'Ngô Xương Văn mất năm 965 đã mở đầu cho thời kì nào?', ['Bắc thuộc', 'Loạn 12 sứ quân', 'Nhà Đinh', 'Nhà Lý'], 1, 'Năm 965 mở đầu loạn 12 sứ quân.', $d);
        $this->matching($s, 'Nối mỗi vị vua với niên hiệu của ông.', [['Đinh Tiên Hoàng', 'Thái Bình'], ['Lê Đại Hành', 'Thiên Phúc']], 'Thái Bình – Đinh Tiên Hoàng, Thiên Phúc – Lê Đại Hành.', $d);
        $this->matching($s, 'Nối mỗi sự kiện với ý nghĩa lịch sử của nó.', [['Bạch Đằng 938', 'Chấm dứt Bắc thuộc'], ['Đặt quốc hiệu Đại Cồ Việt', 'Khẳng định nền độc lập']], 'Bạch Đằng chấm dứt Bắc thuộc; quốc hiệu khẳng định độc lập.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với tước hiệu của ông.', [['Đinh Bộ Lĩnh', 'Vạn Thắng Vương'], ['Lê Hoàn', 'Thập đạo tướng quân']], 'Đinh Bộ Lĩnh – Vạn Thắng Vương; Lê Hoàn – Thập đạo tướng quân.', $d);
        $this->matching($s, 'Nối mỗi năm với triều đại bắt đầu từ năm đó.', [['939', 'Nhà Ngô'], ['968', 'Nhà Đinh'], ['980', 'Nhà Tiền Lê']], '939 nhà Ngô, 968 nhà Đinh, 980 nhà Tiền Lê.', $d);
        $this->matching($s, 'Nối mỗi địa danh với vai trò của nó trong thế kỉ X.', [['Cổ Loa', 'Kinh đô nhà Ngô'], ['Hoa Lư', 'Kinh đô Đinh – Tiền Lê']], 'Cổ Loa kinh đô Ngô; Hoa Lư kinh đô Đinh – Tiền Lê.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC NĂM 1000 hoặc SAU NĂM 1000.', [['Bạch Đằng 938', 'TRƯỚC 1000'], ['Đinh Bộ Lĩnh lên ngôi', 'TRƯỚC 1000'], ['Lý dời đô về Thăng Long', 'SAU 1000'], ['Lý Thường Kiệt đánh Tống', 'SAU 1000']], 'Trước 1000: Bạch Đằng, Đinh; sau 1000: Lý dời đô.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm THỜI NGÔ – ĐINH – TIỀN LÊ hoặc THỜI LÝ.', [['Kinh đô Hoa Lư', 'NGÔ – ĐINH – TIỀN LÊ'], ['Thập đạo quân', 'NGÔ – ĐINH – TIỀN LÊ'], ['Kinh đô Thăng Long', 'THỜI LÝ'], ['Quốc Tử Giám', 'THỜI LÝ']], 'Hoa Lư, thập đạo quân thời Ngô – Đinh – Tiền Lê.', $d);
        $this->sortQ($s, 'Kéo mỗi địa danh vào nhóm KINH ĐÔ THẾ KỈ X hoặc KHÔNG PHẢI.', [['Cổ Loa', 'KINH ĐÔ'], ['Hoa Lư', 'KINH ĐÔ'], ['Đại La', 'KHÔNG PHẢI'], ['Thăng Long', 'KHÔNG PHẢI']], 'Cổ Loa và Hoa Lư là kinh đô thế kỉ X.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm VUA hoặc QUAN LẠI, TƯỚNG LĨNH.', [['Ngô Quyền', 'VUA'], ['Đinh Tiên Hoàng', 'VUA'], ['Phạm Cự Lượng', 'QUAN, TƯỚNG'], ['Cao Lỗ', 'QUAN, TƯỚNG']], 'Ngô Quyền, Đinh Tiên Hoàng là vua.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm CHỐNG NGOẠI XÂM hoặc LOẠN TRONG NƯỚC.', [['Bạch Đằng 938', 'CHỐNG NGOẠI XÂM'], ['Chống Tống 981', 'CHỐNG NGOẠI XÂM'], ['Loạn 12 sứ quân', 'LOẠN TRONG NƯỚC']], 'Bạch Đằng và chống Tống là chống ngoại xâm.', $d);
        $this->fill($s, 'Dương Đình Nghệ giành quyền tự chủ năm ___.', [[0, '931']], 'Năm 931.', $d);
        $this->fill($s, 'Ngô Quyền mất năm ___.', [[0, '944']], 'Mất năm 944.', $d);
        $this->fill($s, 'Đinh Tiên Hoàng đặt niên hiệu là Thái ___.', [[0, 'Bình']], 'Niên hiệu Thái Bình.', $d);
        $this->fill($s, 'Năm 979, Đinh ___ lên ngôi, hiệu là Đinh Phế Đế.', [[0, 'Toàn']], 'Đinh Toàn lên ngôi.', $d);
        $this->fill($s, 'Ngô Xương Văn mất năm 965, mở đầu thời loạn 12 sứ ___.', [[0, 'quân']], 'Loạn 12 sứ quân.', $d);
    }

    private function seedLichSuThpt10Lop102(): void
    {
        $s = 'lich-su-thpt-10-lop-10-2'; $d = 'kho';
        $this->quiz($s, 'Nhà Lý tồn tại trong bao nhiêu năm?', ['100', '175', '216', '300'], 2, 'Nhà Lý tồn tại 216 năm (1009 – 1225).', $d);
        $this->quiz($s, 'Vị vua đầu tiên của nhà Trần là ai?', ['Trần Thái Tông (Trần Cảnh)', 'Trần Thánh Tông', 'Trần Nhân Tông', 'Trần Thủ Độ'], 0, 'Trần Cảnh lên ngôi, hiệu Trần Thái Tông (1226).', $d);
        $this->quiz($s, 'Nhà Hồ tồn tại trong bao nhiêu năm?', ['7', '20', '50', '100'], 0, 'Nhà Hồ tồn tại 7 năm (1400 – 1407).', $d);
        $this->quiz($s, 'Lê Lợi lên ngôi hoàng đế vào năm nào?', ['1418', '1427', '1428', '1430'], 2, 'Năm 1428, Lê Lợi lên ngôi.', $d);
        $this->quiz($s, 'Nhà Lê sơ tồn tại trong khoảng bao nhiêu năm?', ['50', '100', '200', '300'], 1, 'Nhà Lê sơ tồn tại khoảng 100 năm (1428 – 1527).', $d);
        $this->matching($s, 'Nối mỗi triều đại với vị vua cuối cùng của nó.', [['Nhà Lý', 'Lý Huệ Tông'], ['Nhà Trần', 'Trần Thiếu Đế'], ['Nhà Hồ', 'Hồ Hán Thương']], 'Lý – Lý Huệ Tông, Trần – Trần Thiếu Đế, Hồ – Hồ Hán Thương.', $d);
        $this->matching($s, 'Nối mỗi cuộc khởi nghĩa với người lãnh đạo của nó.', [['Khởi nghĩa Lam Sơn', 'Lê Lợi'], ['Khởi nghĩa Tây Sơn', 'Nguyễn Huệ']], 'Lam Sơn – Lê Lợi, Tây Sơn – Nguyễn Huệ.', $d);
        $this->matching($s, 'Nối mỗi chính sách với triều đại thực hiện nó.', [['Cải cách toàn diện', 'Nhà Hồ'], ['Đề cao khoa cử', 'Nhà Lý'], ['Ban hành luật Hồng Đức', 'Nhà Lê']], 'Hồ cải cách, Lý khoa cử, Lê luật Hồng Đức.', $d);
        $this->matching($s, 'Nối mỗi kinh đô với triều đại đóng đô ở đó.', [['Thăng Long', 'Lý – Trần – Lê'], ['Tây Đô', 'Nhà Hồ']], 'Thăng Long của Lý, Trần, Lê; Tây Đô của Hồ.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện của nó.', [['1400', 'Nhà Hồ thành lập'], ['1428', 'Lê Lợi lên ngôi']], '1400 nhà Hồ, 1428 Lê Lợi lên ngôi.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC NĂM 1400 hoặc SAU NĂM 1400.', [['Nhà Trần suy vong', 'TRƯỚC 1400'], ['Hồ Quý Ly phế truất nhà Trần', 'TRƯỚC 1400'], ['Nhà Hồ thành lập', 'SAU 1400'], ['Khởi nghĩa Lam Sơn', 'SAU 1400']], 'Năm 1400 là mốc nhà Hồ thành lập.', $d);
        $this->sortQ($s, 'Kéo mỗi triều đại vào nhóm TỒN TẠI TRÊN 100 NĂM hoặc DƯỚI 100 NĂM.', [['Nhà Lý (216 năm)', 'TRÊN 100 NĂM'], ['Nhà Trần (175 năm)', 'TRÊN 100 NĂM'], ['Nhà Hồ (7 năm)', 'DƯỚI 100 NĂM']], 'Lý 216, Trần 175 trên 100 năm; Hồ 7 năm.', $d);
        $this->sortQ($s, 'Kéo mỗi triều đại vào nhóm ĐÓNG ĐÔ Ở THĂNG LONG hoặc KHÔNG.', [['Nhà Lý', 'THĂNG LONG'], ['Nhà Trần', 'THĂNG LONG'], ['Nhà Lê', 'THĂNG LONG'], ['Nhà Hồ', 'KHÔNG']], 'Lý, Trần, Lê đóng đô Thăng Long; Hồ ở Tây Đô.', $d);
        $this->sortQ($s, 'Kéo mỗi việc làm vào nhóm CẢI CÁCH hoặc CHIẾN TRANH GIỮ NƯỚC.', [['Cải cách của Hồ Quý Ly', 'CẢI CÁCH'], ['Luật Hồng Đức', 'CẢI CÁCH'], ['Kháng chiến chống Nguyên', 'GIỮ NƯỚC'], ['Khởi nghĩa Lam Sơn', 'GIỮ NƯỚC']], 'Cải cách Hồ Quý Ly, luật Hồng Đức là cải cách.', $d);
        $this->sortQ($s, 'Kéo mỗi vị vua vào nhóm VUA KHAI SÁNG hoặc VUA CUỐI TRIỀU.', [['Lý Thái Tổ', 'KHAI SÁNG'], ['Trần Thái Tông', 'KHAI SÁNG'], ['Lê Lợi', 'KHAI SÁNG'], ['Lý Huệ Tông', 'VUA CUỐI'], ['Hồ Hán Thương', 'VUA CUỐI']], 'Lý Thái Tổ, Trần Thái Tông, Lê Lợi là vua khai sáng.', $d);
        $this->fill($s, 'Nhà Lý tồn tại 216 năm, từ 1009 đến ___.', [[0, '1225']], 'Nhà Lý 1009 – 1225.', $d);
        $this->fill($s, 'Vị vua đầu tiên của nhà Trần là Trần Thái ___ (Trần Cảnh).', [[0, 'Tông']], 'Trần Thái Tông.', $d);
        $this->fill($s, 'Nhà Hồ chỉ tồn tại 7 năm, từ 1400 đến ___.', [[0, '1407']], 'Nhà Hồ 1400 – 1407.', $d);
        $this->fill($s, 'Năm 1428, Lê Lợi lên ngôi, đặt quốc hiệu là Đại ___.', [[0, 'Việt']], 'Quốc hiệu Đại Việt.', $d);
        $this->fill($s, 'Kinh đô của nhà Hồ là Tây ___ ở Thanh Hóa.', [[0, 'Đô']], 'Tây Đô.', $d);
    }

    private function seedLichSuThpt10Lop103(): void
    {
        $s = 'lich-su-thpt-10-lop-10-3'; $d = 'trung_binh';
        $this->quiz($s, 'Văn Miếu – nơi thờ Khổng Tử được xây dựng vào năm nào?', ['1070', '1075', '1076', '1086'], 0, 'Văn Miếu được xây dựng năm 1070.', $d);
        $this->quiz($s, 'Ai được coi là vị Trạng nguyên đầu tiên của nước ta?', ['Chu Văn An', 'Lê Văn Thịnh', 'Mạc Đĩnh Chi', 'Nguyễn Trãi'], 1, 'Lê Văn Thịnh đỗ Trạng nguyên khoa thi 1075.', $d);
        $this->quiz($s, '"Tam khôi" trong khoa cử Nho học gồm những danh hiệu nào?', ['Trạng nguyên, Bảng nhãn, Thám hoa', 'Tiến sĩ, Cử nhân, Tú tài', 'Thám hoa, Hoàng giáp, Đồng tiến sĩ', 'Trạng nguyên, Tiến sĩ, Phó bảng'], 0, 'Tam khôi: Trạng nguyên, Bảng nhãn, Thám hoa.', $d);
        $this->quiz($s, 'Nền khoa cử Nho học Việt Nam chấm dứt vào năm nào?', ['1918', '1919', '1920', '1945'], 1, 'Khoa thi cuối cùng năm 1919.', $d);
        $this->quiz($s, 'Quốc Tử Giám thời Nguyễn được đặt tại đâu?', ['Hà Nội', 'Huế', 'Thăng Long', 'Sài Gòn'], 1, 'Quốc Tử Giám thời Nguyễn đặt ở Huế.', $d);
        $this->matching($s, 'Nối mỗi danh hiệu khoa cử với thứ hạng của nó.', [['Trạng nguyên', 'Đỗ đầu'], ['Bảng nhãn', 'Đỗ thứ hai'], ['Thám hoa', 'Đỗ thứ ba']], 'Tam khôi: trạng nguyên đầu, bảng nhãn hai, thám hoa ba.', $d);
        $this->matching($s, 'Nối mỗi cơ sở giáo dục với năm thành lập của nó.', [['Văn Miếu', '1070'], ['Quốc Tử Giám', '1076']], 'Văn Miếu 1070, Quốc Tử Giám 1076.', $d);
        $this->matching($s, 'Nối mỗi triều đại với chính sách giáo dục của nó.', [['Nhà Lý', 'Mở khoa thi Nho học'], ['Nhà Lê', 'Đề cao khoa cử']], 'Lý mở khoa thi; Lê đề cao khoa cử.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với danh hiệu của ông.', [['Chu Văn An', 'Vạn thế sư biểu'], ['Lê Văn Thịnh', 'Trạng nguyên đầu tiên']], 'Chu Văn An vạn thế sư biểu; Lê Văn Thịnh trạng nguyên đầu.', $d);
        $this->matching($s, 'Nối mỗi kì thi với cấp tổ chức của nó.', [['Thi Hương', 'Cấp tỉnh'], ['Thi Hội', 'Cấp trung ương'], ['Thi Đình', 'Vua trực tiếp chấm']], 'Hương cấp tỉnh, Hội trung ương, Đình vua chấm.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện giáo dục vào nhóm THỜI LÝ hoặc THỜI NGUYỄN.', [['Xây Văn Miếu', 'THỜI LÝ'], ['Khoa thi 1075', 'THỜI LÝ'], ['Quốc Tử Giám ở Huế', 'THỜI NGUYỄN'], ['Khoa thi 1919', 'THỜI NGUYỄN']], 'Văn Miếu, khoa thi 1075 thời Lý.', $d);
        $this->sortQ($s, 'Kéo mỗi danh hiệu vào nhóm DANH HIỆU TIẾN SĨ hoặc KHÔNG PHẢI.', [['Trạng nguyên', 'TIẾN SĨ'], ['Bảng nhãn', 'TIẾN SĨ'], ['Cử nhân', 'KHÔNG PHẢI'], ['Tú tài', 'KHÔNG PHẢI']], 'Trạng nguyên, bảng nhãn là tiến sĩ.', $d);
        $this->sortQ($s, 'Kéo mỗi kì thi vào nhóm CẤP THẤP hoặc CẤP CAO.', [['Thi Hương', 'CẤP THẤP'], ['Thi Hội', 'CẤP CAO'], ['Thi Đình', 'CẤP CAO']], 'Thi Hương cấp thấp; Hội, Đình cấp cao.', $d);
        $this->sortQ($s, 'Kéo mỗi môn học vào nhóm CÓ TRONG KHOA CỬ NHO HỌC hoặc KHÔNG.', [['Tứ thư, Ngũ kinh', 'CÓ TRONG'], ['Văn sách', 'CÓ TRONG'], ['Toán cao cấp', 'KHÔNG CÓ'], ['Ngoại ngữ hiện đại', 'KHÔNG CÓ']], 'Tứ thư ngũ kinh, văn sách trong khoa cử Nho học.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC NĂM 1500 hoặc SAU NĂM 1500.', [['Khoa thi 1075', 'TRƯỚC 1500'], ['Xây Văn Miếu', 'TRƯỚC 1500'], ['Khoa thi 1919', 'SAU 1500']], 'Khoa thi 1075 trước 1500; 1919 sau 1500.', $d);
        $this->fill($s, 'Văn Miếu được xây dựng năm ___.', [[0, '1070']], 'Năm 1070.', $d);
        $this->fill($s, 'Trạng nguyên đầu tiên của nước ta là Lê Văn ___.', [[0, 'Thịnh']], 'Lê Văn Thịnh.', $d);
        $this->fill($s, 'Ba danh hiệu cao nhất là Trạng nguyên, Bảng nhãn và Thám ___.', [[0, 'hoa']], 'Thám hoa.', $d);
        $this->fill($s, 'Khoa thi Nho học cuối cùng diễn ra năm ___.', [[0, '1919']], 'Năm 1919.', $d);
        $this->fill($s, 'Kì thi do vua trực tiếp chấm gọi là thi ___.', [[0, 'Đình']], 'Thi Đình.', $d);
    }

    private function seedLichSuThpt10Lop104(): void
    {
        $s = 'lich-su-thpt-10-lop-10-4'; $d = 'kho';
        $this->quiz($s, 'Chùa Một Cột được xây dựng vào năm nào?', ['1010', '1049', '1070', '1100'], 1, 'Chùa Một Cột xây năm 1049.', $d);
        $this->quiz($s, 'Vị vua nào cho xây dựng chùa Một Cột?', ['Lý Thái Tổ', 'Lý Thái Tông', 'Lý Thánh Tông', 'Lý Nhân Tông'], 1, 'Lý Thái Tông cho xây chùa Một Cột.', $d);
        $this->quiz($s, 'Các tháp Chăm chủ yếu thờ vị thần nào?', ['Thần Vishnu', 'Thần Shiva', 'Phật Thích Ca', 'Thần Brahma'], 1, 'Tháp Chăm thờ thần Shiva.', $d);
        $this->quiz($s, 'Nhã nhạc cung đình Huế được UNESCO công nhận di sản vào năm nào?', ['1993', '1999', '2003', '2010'], 2, 'Nhã nhạc được công nhận năm 2003.', $d);
        $this->quiz($s, 'Thánh địa Mỹ Sơn được UNESCO công nhận di sản thế giới năm nào?', ['1993', '1999', '2003', '2010'], 1, 'Mỹ Sơn được công nhận năm 1999.', $d);
        $this->matching($s, 'Nối mỗi công trình tôn giáo với tôn giáo của nó.', [['Chùa Một Cột', 'Phật giáo'], ['Thánh địa Mỹ Sơn', 'Ấn Độ giáo'], ['Tháp Chăm', 'Ấn Độ giáo']], 'Chùa Một Cột Phật giáo; Mỹ Sơn, tháp Chăm Ấn Độ giáo.', $d);
        $this->matching($s, 'Nối mỗi di sản Việt Nam với năm UNESCO công nhận.', [['Cố đô Huế', '1993'], ['Thánh địa Mỹ Sơn', '1999'], ['Nhã nhạc cung đình', '2003'], ['Hoàng thành Thăng Long', '2010']], 'Huế 1993, Mỹ Sơn 1999, Nhã nhạc 2003, Thăng Long 2010.', $d);
        $this->matching($s, 'Nối mỗi công trình với địa phương của nó.', [['Chùa Một Cột', 'Hà Nội'], ['Cố đô Huế', 'Thừa Thiên Huế'], ['Thánh địa Mỹ Sơn', 'Quảng Nam']], 'Một Cột – Hà Nội, Huế – Thừa Thiên Huế, Mỹ Sơn – Quảng Nam.', $d);
        $this->matching($s, 'Nối mỗi loại hình nghệ thuật với môi trường của nó.', [['Nhã nhạc', 'Cung đình'], ['Chèo', 'Dân gian'], ['Múa rối nước', 'Dân gian']], 'Nhã nhạc cung đình; chèo, rối nước dân gian.', $d);
        $this->matching($s, 'Nối mỗi vật liệu với công trình dùng nó.', [['Gỗ', 'Chùa Một Cột'], ['Gạch nung', 'Tháp Chăm']], 'Chùa Một Cột bằng gỗ; tháp Chăm bằng gạch.', $d);
        $this->sortQ($s, 'Kéo mỗi công trình vào nhóm PHẬT GIÁO hoặc ẤN ĐỘ GIÁO.', [['Chùa Một Cột', 'PHẬT GIÁO'], ['Chùa Dâu', 'PHẬT GIÁO'], ['Thánh địa Mỹ Sơn', 'ẤN ĐỘ GIÁO'], ['Tháp Chăm', 'ẤN ĐỘ GIÁO']], 'Chùa Phật giáo; tháp Chăm, Mỹ Sơn Ấn Độ giáo.', $d);
        $this->sortQ($s, 'Kéo mỗi công trình vào nhóm THỜI LÝ hoặc THỜI NGUYỄN.', [['Chùa Một Cột', 'THỜI LÝ'], ['Hoàng thành Thăng Long', 'THỜI LÝ'], ['Cố đô Huế', 'THỜI NGUYỄN'], ['Lăng tẩm Huế', 'THỜI NGUYỄN']], 'Chùa Một Cột thời Lý; cố đô Huế thời Nguyễn.', $d);
        $this->sortQ($s, 'Kéo mỗi di sản vào nhóm DI SẢN VĂN HÓA hoặc DI SẢN THIÊN NHIÊN.', [['Cố đô Huế', 'VĂN HÓA'], ['Thánh địa Mỹ Sơn', 'VĂN HÓA'], ['Vịnh Hạ Long', 'THIÊN NHIÊN'], ['Phong Nha – Kẻ Bàng', 'THIÊN NHIÊN']], 'Huế, Mỹ Sơn văn hóa; Hạ Long, Phong Nha thiên nhiên.', $d);
        $this->sortQ($s, 'Kéo mỗi công trình vào nhóm KIẾN TRÚC TÔN GIÁO hoặc KIẾN TRÚC CUNG ĐÌNH.', [['Chùa Một Cột', 'TÔN GIÁO'], ['Tháp Chăm', 'TÔN GIÁO'], ['Hoàng thành Thăng Long', 'CUNG ĐÌNH'], ['Kinh thành Huế', 'CUNG ĐÌNH']], 'Chùa, tháp tôn giáo; hoàng thành, kinh thành cung đình.', $d);
        $this->sortQ($s, 'Kéo mỗi công trình vào nhóm Ở MIỀN BẮC hoặc Ở MIỀN TRUNG.', [['Chùa Một Cột', 'MIỀN BẮC'], ['Hoàng thành Thăng Long', 'MIỀN BẮC'], ['Cố đô Huế', 'MIỀN TRUNG'], ['Thánh địa Mỹ Sơn', 'MIỀN TRUNG']], 'Một Cột, Thăng Long miền Bắc; Huế, Mỹ Sơn miền Trung.', $d);
        $this->fill($s, 'Chùa Một Cột được xây dựng năm ___.', [[0, '1049']], 'Năm 1049.', $d);
        $this->fill($s, 'Vua cho xây chùa Một Cột là Lý Thái ___.', [[0, 'Tông']], 'Lý Thái Tông.', $d);
        $this->fill($s, 'Các tháp Chăm chủ yếu thờ thần ___.', [[0, 'Shiva']], 'Thờ thần Shiva.', $d);
        $this->fill($s, 'Nhã nhạc cung đình Huế được UNESCO công nhận năm ___.', [[0, '2003']], 'Năm 2003.', $d);
        $this->fill($s, 'Thánh địa Mỹ Sơn được UNESCO công nhận năm ___.', [[0, '1999']], 'Năm 1999.', $d);
    }

    private function seedLichSuThpt11Lop111(): void
    {
        $s = 'lich-su-thpt-11-lop-11-1'; $d = 'trung_binh';
        $this->quiz($s, 'Vua Hàm Nghi lên ngôi vào năm nào?', ['1883', '1884', '1885', '1886'], 1, 'Hàm Nghi lên ngôi năm 1884.', $d);
        $this->quiz($s, 'Tôn Thất Thuyết đã tấn công quân Pháp tại đâu vào tháng 7/1885?', ['Hà Nội', 'Kinh thành Huế', 'Sài Gòn', 'Đà Nẵng'], 1, 'Tấn công Pháp ở kinh thành Huế (đồn Mang Cá).', $d);
        $this->quiz($s, 'Khởi nghĩa Ba Đình (1886–1887) do ai lãnh đạo?', ['Đinh Công Tráng', 'Phan Đình Phùng', 'Nguyễn Thiện Thuật', 'Tống Duy Tân'], 0, 'Đinh Công Tráng lãnh đạo khởi nghĩa Ba Đình.', $d);
        $this->quiz($s, 'Khởi nghĩa Bãi Sậy (1883–1892) do ai lãnh đạo?', ['Nguyễn Thiện Thuật', 'Đinh Công Tráng', 'Phan Đình Phùng', 'Hoàng Hoa Thám'], 0, 'Nguyễn Thiện Thuật lãnh đạo khởi nghĩa Bãi Sậy.', $d);
        $this->quiz($s, 'Phong trào Cần Vương mang tính chất nào?', ['Nông dân', 'Phong kiến yêu nước chống Pháp', 'Tư sản', 'Vô sản'], 1, 'Cần Vương là phong trào yêu nước chống Pháp theo hệ tư tưởng phong kiến.', $d);
        $this->matching($s, 'Nối mỗi cuộc khởi nghĩa trong phong trào Cần Vương với địa bàn của nó.', [['Ba Đình', 'Thanh Hóa'], ['Bãi Sậy', 'Hưng Yên'], ['Hương Khê', 'Hà Tĩnh']], 'Ba Đình – Thanh Hóa, Bãi Sậy – Hưng Yên, Hương Khê – Hà Tĩnh.', $d);
        $this->matching($s, 'Nối mỗi cuộc khởi nghĩa với người lãnh đạo của nó.', [['Ba Đình', 'Đinh Công Tráng'], ['Bãi Sậy', 'Nguyễn Thiện Thuật'], ['Hương Khê', 'Phan Đình Phùng']], 'Ba Đình – Đinh Công Tráng, Bãi Sậy – Nguyễn Thiện Thuật, Hương Khê – Phan Đình Phùng.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện của phong trào Cần Vương.', [['1884', 'Hàm Nghi lên ngôi'], ['1885', 'Chiếu Cần Vương ban bố'], ['1896', 'Phan Đình Phùng hi sinh']], '1884 lên ngôi, 1885 Chiếu Cần Vương, 1896 kết thúc.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với việc làm của ông.', [['Tôn Thất Thuyết', 'Phò vua Hàm Nghi xuất bôn'], ['Phan Đình Phùng', 'Lãnh đạo khởi nghĩa Hương Khê']], 'Tôn Thất Thuyết phò vua; Phan Đình Phùng lãnh đạo Hương Khê.', $d);
        $this->matching($s, 'Nối mỗi căn cứ với địa danh của nó.', [['Hương Khê', 'Ngàn Trươi, Hà Tĩnh'], ['Ba Đình', 'Nga Sơn, Thanh Hóa']], 'Hương Khê ở Ngàn Trươi; Ba Đình ở Nga Sơn.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm DO VĂN THÂN, SĨ PHU hoặc DO NÔNG DÂN lãnh đạo.', [['Cần Vương', 'VĂN THÂN, SĨ PHU'], ['Ba Đình', 'VĂN THÂN, SĨ PHU'], ['Yên Thế', 'NÔNG DÂN']], 'Cần Vương do văn thân, sĩ phu lãnh đạo; Yên Thế nông dân.', $d);
        $this->sortQ($s, 'Kéo mỗi khởi nghĩa vào nhóm TRONG hoặc NGOÀI phong trào Cần Vương.', [['Ba Đình', 'TRONG CẦN VƯƠNG'], ['Bãi Sậy', 'TRONG CẦN VƯƠNG'], ['Hương Khê', 'TRONG CẦN VƯƠNG'], ['Yên Thế', 'NGOÀI CẦN VƯƠNG'], ['Đông Du', 'NGOÀI CẦN VƯƠNG']], 'Ba Đình, Bãi Sậy, Hương Khê thuộc Cần Vương.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm CÓ VUA THAM GIA hoặc KHÔNG CÓ VUA.', [['Cần Vương', 'CÓ VUA'], ['Yên Thế', 'KHÔNG CÓ VUA'], ['Đông Du', 'KHÔNG CÓ VUA']], 'Cần Vương có vua Hàm Nghi tham gia.', $d);
        $this->sortQ($s, 'Kéo mỗi khởi nghĩa vào nhóm TRƯỚC NĂM 1890 hoặc TỪ NĂM 1890 TRỞ ĐI.', [['Ba Đình (1886–1887)', 'TRƯỚC 1890'], ['Hương Khê (1885–1896)', 'TỪ 1890 TRỞ ĐI']], 'Ba Đình trước 1890; Hương Khê kéo dài qua 1890.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm CHỐNG THỰC DÂN PHÁP hoặc CHỐNG PHONG KIẾN.', [['Cần Vương', 'CHỐNG PHÁP'], ['Yên Thế', 'CHỐNG PHÁP'], ['Khởi nghĩa nông dân', 'CHỐNG PHONG KIẾN']], 'Cần Vương và Yên Thế chống Pháp.', $d);
        $this->fill($s, 'Vua Hàm Nghi lên ngôi năm ___.', [[0, '1884']], 'Lên ngôi năm 1884.', $d);
        $this->fill($s, 'Tôn Thất Thuyết tấn công Pháp ở kinh thành ___.', [[0, 'Huế']], 'Tấn công ở kinh thành Huế.', $d);
        $this->fill($s, 'Khởi nghĩa Ba Đình do Đinh Công ___ lãnh đạo.', [[0, 'Tráng']], 'Đinh Công Tráng.', $d);
        $this->fill($s, 'Khởi nghĩa Bãi Sậy do Nguyễn Thiện ___ lãnh đạo.', [[0, 'Thuật']], 'Nguyễn Thiện Thuật.', $d);
        $this->fill($s, 'Phong trào Cần Vương mang tính chất yêu nước chống ___.', [[0, 'Pháp']], 'Chống thực dân Pháp.', $d);
    }

    private function seedLichSuThpt11Lop112(): void
    {
        $s = 'lich-su-thpt-11-lop-11-2'; $d = 'kho';
        $this->quiz($s, 'Khởi nghĩa Yên Thế (1884–1913) kéo dài trong bao nhiêu năm?', ['10', '20', '29', '40'], 2, 'Yên Thế kéo dài 29 năm (1884 – 1913).', $d);
        $this->quiz($s, 'Lãnh tụ Hoàng Hoa Thám (Đề Thám) bị thực dân Pháp ám sát năm nào?', ['1910', '1913', '1915', '1920'], 1, 'Đề Thám bị ám sát năm 1913.', $d);
        $this->quiz($s, 'Phan Bội Châu chủ trương cứu nước bằng con đường nào?', ['Cải cách ôn hòa', 'Bạo động vũ trang', 'Khai dân trí', 'Bất bạo động'], 1, 'Phan Bội Châu chủ trương bạo động vũ trang.', $d);
        $this->quiz($s, 'Tổ chức Việt Nam Quang phục hội do ai thành lập năm 1912?', ['Phan Châu Trinh', 'Phan Bội Châu', 'Nguyễn Ái Quốc', 'Lương Văn Can'], 1, 'Phan Bội Châu thành lập Việt Nam Quang phục hội (1912).', $d);
        $this->quiz($s, 'Ai là người sáng lập trường Đông Kinh Nghĩa Thục (1907)?', ['Phan Bội Châu', 'Lương Văn Can', 'Phan Châu Trinh', 'Nguyễn Quyền'], 1, 'Lương Văn Can sáng lập Đông Kinh Nghĩa Thục.', $d);
        $this->matching($s, 'Nối mỗi tổ chức với năm thành lập của nó.', [['Đông Kinh Nghĩa Thục', '1907'], ['Việt Nam Quang phục hội', '1912'], ['Hội Duy Tân', '1904']], 'Đông Kinh Nghĩa Thục 1907, Quang phục hội 1912, Duy Tân 1904.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với chủ trương cứu nước của ông.', [['Phan Bội Châu', 'Bạo động vũ trang'], ['Phan Châu Trinh', 'Cải cách, duy tân'], ['Hoàng Hoa Thám', 'Vũ trang chống Pháp']], 'Phan Bội Châu bạo động; Phan Châu Trinh cải cách.', $d);
        $this->matching($s, 'Nối mỗi phong trào với nơi diễn ra chủ yếu của nó.', [['Yên Thế', 'Bắc Giang'], ['Đông Du', 'Nhật Bản'], ['Đông Kinh Nghĩa Thục', 'Hà Nội']], 'Yên Thế – Bắc Giang, Đông Du – Nhật Bản, Nghĩa Thục – Hà Nội.', $d);
        $this->matching($s, 'Nối mỗi phong trào với kết quả của nó.', [['Đông Du', 'Bị Pháp – Nhật cấu kết đàn áp'], ['Yên Thế', 'Bị đàn áp năm 1913']], 'Đông Du bị đàn áp; Yên Thế thất bại 1913.', $d);
        $this->matching($s, 'Nối mỗi khẩu hiệu với phong trào của nó.', [['Khai dân trí, chấn dân khí', 'Duy Tân'], ['Đánh đuổi thực dân Pháp', 'Yên Thế']], 'Khai dân trí của Duy Tân; đánh Pháp của Yên Thế.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm DO SĨ PHU hoặc DO NÔNG DÂN lãnh đạo.', [['Đông Du', 'SĨ PHU'], ['Duy Tân', 'SĨ PHU'], ['Đông Kinh Nghĩa Thục', 'SĨ PHU'], ['Yên Thế', 'NÔNG DÂN']], 'Đông Du, Duy Tân sĩ phu; Yên Thế nông dân.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm HOẠT ĐỘNG TRONG NƯỚC hoặc NGOÀI NƯỚC.', [['Yên Thế', 'TRONG NƯỚC'], ['Đông Kinh Nghĩa Thục', 'TRONG NƯỚC'], ['Đông Du', 'NGOÀI NƯỚC']], 'Đông Du hoạt động ở Nhật Bản.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm BỊ PHÁP ĐÀN ÁP hoặc BỊ BUỘC ĐÓNG CỬA.', [['Yên Thế', 'BỊ ĐÀN ÁP'], ['Đông Du', 'BỊ ĐÀN ÁP'], ['Đông Kinh Nghĩa Thục', 'BỊ ĐÓNG CỬA']], 'Yên Thế, Đông Du bị đàn áp; Nghĩa Thục bị đóng cửa.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm CUỐI THẾ KỈ XIX hoặc ĐẦU THẾ KỈ XX.', [['Yên Thế (bắt đầu 1884)', 'CUỐI XIX'], ['Đông Du (1905)', 'ĐẦU XX'], ['Duy Tân (1906)', 'ĐẦU XX']], 'Yên Thế cuối XIX; Đông Du, Duy Tân đầu XX.', $d);
        $this->sortQ($s, 'Kéo mỗi nhân vật vào nhóm CHỦ TRƯƠNG BẠO ĐỘNG hoặc CHỦ TRƯƠNG ÔN HÒA.', [['Phan Bội Châu', 'BẠO ĐỘNG'], ['Hoàng Hoa Thám', 'BẠO ĐỘNG'], ['Phan Châu Trinh', 'ÔN HÒA']], 'Phan Bội Châu bạo động; Phan Châu Trinh ôn hòa.', $d);
        $this->fill($s, 'Khởi nghĩa Yên Thế kéo dài 29 năm, từ 1884 đến ___.', [[0, '1913']], 'Yên Thế 1884 – 1913.', $d);
        $this->fill($s, 'Đề Thám (Hoàng Hoa Thám) bị thực dân Pháp ám sát năm ___.', [[0, '1913']], 'Bị ám sát năm 1913.', $d);
        $this->fill($s, 'Phan Bội Châu chủ trương dùng bạo động vũ trang để đánh đuổi ___.', [[0, 'Pháp']], 'Đánh đuổi thực dân Pháp.', $d);
        $this->fill($s, 'Tổ chức Việt Nam Quang phục hội được thành lập năm ___.', [[0, '1912']], 'Thành lập năm 1912.', $d);
        $this->fill($s, 'Người sáng lập Đông Kinh Nghĩa Thục là Lương Văn ___.', [[0, 'Can']], 'Lương Văn Can.', $d);
    }

    private function seedLichSuThpt11Lop113(): void
    {
        $s = 'lich-su-thpt-11-lop-11-3'; $d = 'trung_binh';
        $this->quiz($s, 'Nguyễn Tất Thành sinh năm nào?', ['1888', '1890', '1892', '1894'], 1, 'Nguyễn Tất Thành sinh năm 1890 tại Nghệ An.', $d);
        $this->quiz($s, 'Năm 1919, Nguyễn Ái Quốc đã gửi "Yêu sách của nhân dân An Nam" tới hội nghị nào?', ['Hội nghị Genève', 'Hội nghị Véc-xây', 'Hội nghị Paris', 'Hội nghị Potsdam'], 1, 'Gửi Yêu sách tới Hội nghị Véc-xây (1919).', $d);
        $this->quiz($s, 'Tờ báo "Người cùng khổ" (Le Paria) do ai sáng lập năm 1922?', ['Phan Bội Châu', 'Nguyễn Ái Quốc', 'Phan Châu Trinh', 'Nguyễn An Ninh'], 1, 'Nguyễn Ái Quốc sáng lập báo Le Paria năm 1922.', $d);
        $this->quiz($s, 'Hội Việt Nam Cách mạng Thanh niên được thành lập vào năm nào?', ['1923', '1925', '1927', '1929'], 1, 'Hội Việt Nam Cách mạng Thanh niên thành lập năm 1925.', $d);
        $this->quiz($s, 'Nguyễn Ái Quốc đã tán thành và truyền bá luận cương về vấn đề dân tộc và thuộc địa của ai?', ['Mác', 'Lênin', 'Xtalin', 'Mao Trạch Đông'], 1, 'Nguyễn Ái Quốc tán thành luận cương của Lênin.', $d);
        $this->matching($s, 'Nối mỗi tên gọi của Bác với giai đoạn sử dụng.', [['Nguyễn Tất Thành', 'Trước 1919'], ['Nguyễn Ái Quốc', '1919 – 1942'], ['Hồ Chí Minh', 'Từ 1942 trở đi']], 'Nguyễn Tất Thành, Nguyễn Ái Quốc rồi Hồ Chí Minh.', $d);
        $this->matching($s, 'Nối mỗi tác phẩm của Nguyễn Ái Quốc với năm ra đời.', [['Đường Kách mệnh', '1927'], ['Bản án chế độ thực dân Pháp', '1925']], 'Bản án 1925, Đường Kách mệnh 1927.', $d);
        $this->matching($s, 'Nối mỗi tổ chức với vai trò của Nguyễn Ái Quốc trong đó.', [['Hội Việt Nam Cách mạng Thanh niên', 'Sáng lập'], ['Đảng Cộng sản Pháp', 'Tham gia sáng lập']], 'Sáng lập Hội Thanh niên; tham gia sáng lập Đảng Cộng sản Pháp.', $d);
        $this->matching($s, 'Nối mỗi địa điểm với sự kiện diễn ra ở đó.', [['Bến Nhà Rồng', 'Ra đi tìm đường cứu nước 1911'], ['Hương Cảng (Trung Quốc)', 'Hội nghị hợp nhất 1930']], 'Bến Nhà Rồng ra đi 1911; Hương Cảng hợp nhất 1930.', $d);
        $this->matching($s, 'Nối mỗi năm với sự kiện trong hành trình của Nguyễn Ái Quốc.', [['1920', 'Gia nhập Quốc tế Cộng sản'], ['1925', 'Thành lập Hội Thanh niên']], '1920 vào Quốc tế Cộng sản; 1925 lập Hội Thanh niên.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU khi Nguyễn Tất Thành ra đi tìm đường cứu nước (1911).', [['Phong trào Đông Du', 'TRƯỚC 1911'], ['Phong trào Duy Tân', 'TRƯỚC 1911'], ['Tham gia Đảng Cộng sản Pháp', 'SAU 1911'], ['Hợp nhất các tổ chức cộng sản', 'SAU 1911']], 'Đông Du, Duy Tân trước 1911.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm Ở PHÁP hoặc Ở TRUNG QUỐC.', [['Tham gia Đảng Cộng sản Pháp', 'Ở PHÁP'], ['Ra báo Người cùng khổ', 'Ở PHÁP'], ['Lập Hội Thanh niên', 'Ở TRUNG QUỐC'], ['Viết Đường Kách mệnh', 'Ở TRUNG QUỐC']], 'Ở Pháp: Đảng Cộng sản Pháp, báo Le Paria; ở Trung Quốc: Hội Thanh niên.', $d);
        $this->sortQ($s, 'Kéo mỗi tác phẩm vào nhóm CỦA NGUYỄN ÁI QUỐC hoặc KHÔNG PHẢI.', [['Đường Kách mệnh', 'CỦA NGUYỄN ÁI QUỐC'], ['Bản án chế độ thực dân Pháp', 'CỦA NGUYỄN ÁI QUỐC'], ['Bình Ngô đại cáo', 'KHÔNG PHẢI'], ['Hịch tướng sĩ', 'KHÔNG PHẢI']], 'Đường Kách mệnh và Bản án là của Nguyễn Ái Quốc.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ chức vào nhóm DO NGUYỄN ÁI QUỐC SÁNG LẬP/THAM GIA hoặc KHÔNG.', [['Hội Việt Nam Cách mạng Thanh niên', 'CÓ THAM GIA'], ['Đảng Cộng sản Pháp', 'CÓ THAM GIA'], ['Việt Nam Quốc dân Đảng', 'KHÔNG']], 'Hội Thanh niên và Đảng Cộng sản Pháp có Nguyễn Ái Quốc.', $d);
        $this->sortQ($s, 'Kéo mỗi tên gọi vào nhóm BÚT DANH, TÊN CỦA BÁC HỒ hoặc KHÔNG PHẢI.', [['Nguyễn Tất Thành', 'CỦA BÁC HỒ'], ['Nguyễn Ái Quốc', 'CỦA BÁC HỒ'], ['Hồ Chí Minh', 'CỦA BÁC HỒ'], ['Phan Bội Châu', 'KHÔNG PHẢI'], ['Phan Châu Trinh', 'KHÔNG PHẢI']], 'Ba tên đều là của Bác Hồ.', $d);
        $this->fill($s, 'Nguyễn Tất Thành sinh năm ___ tại Nghệ An.', [[0, '1890']], 'Sinh năm 1890.', $d);
        $this->fill($s, 'Năm 1919, Nguyễn Ái Quốc gửi Yêu sách tới Hội nghị Véc-___.', [[0, 'xây']], 'Hội nghị Véc-xây.', $d);
        $this->fill($s, 'Báo Le Paria (Người cùng khổ) do Nguyễn Ái Quốc sáng lập năm ___.', [[0, '1922']], 'Sáng lập năm 1922.', $d);
        $this->fill($s, 'Hội Việt Nam Cách mạng Thanh niên thành lập năm ___.', [[0, '1925']], 'Thành lập năm 1925.', $d);
        $this->fill($s, 'Tác phẩm Bản án chế độ thực dân Pháp xuất bản năm ___.', [[0, '1925']], 'Xuất bản năm 1925.', $d);
    }

    private function seedLichSuThpt11Lop114(): void
    {
        $s = 'lich-su-thpt-11-lop-11-4'; $d = 'kho';
        $this->quiz($s, 'Cuộc khởi nghĩa Yên Bái (2/1930) do tổ chức nào lãnh đạo?', ['Đảng Cộng sản', 'Việt Nam Quốc dân Đảng', 'Hội Thanh niên', 'Đông Du'], 1, 'Việt Nam Quốc dân Đảng lãnh đạo khởi nghĩa Yên Bái.', $d);
        $this->quiz($s, 'Ai là người lãnh đạo cuộc khởi nghĩa Yên Bái?', ['Nguyễn Thái Học', 'Phó Đức Chính', 'Nguyễn Khắc Nhu', 'Đội Cấn'], 0, 'Nguyễn Thái Học lãnh đạo khởi nghĩa Yên Bái.', $d);
        $this->quiz($s, 'Cao trào cách mạng 1936–1939 còn được gọi là gì?', ['Xô viết Nghệ Tĩnh', 'Phong trào Mặt trận Dân chủ', 'Tổng khởi nghĩa', 'Đồng Khởi'], 1, 'Cao trào 1936–1939 là phong trào Mặt trận Dân chủ.', $d);
        $this->quiz($s, 'Hội nghị Trung ương 8 (5/1941) đã quyết định vấn đề quan trọng nào?', ['Đánh Pháp', 'Đặt nhiệm vụ giải phóng dân tộc lên hàng đầu', 'Cải cách ruộng đất', 'Tổng tuyển cử'], 1, 'Hội nghị đặt giải phóng dân tộc lên hàng đầu, lập Việt Minh.', $d);
        $this->quiz($s, 'Cuộc khởi nghĩa Nam Kì bùng nổ vào năm nào?', ['1930', '1940', '1941', '1945'], 1, 'Khởi nghĩa Nam Kì năm 1940.', $d);
        $this->matching($s, 'Nối mỗi cuộc khởi nghĩa, binh biến với năm diễn ra.', [['Bắc Sơn', '1940'], ['Nam Kì', '1940'], ['Binh biến Đô Lương', '1941']], 'Bắc Sơn, Nam Kì 1940; Đô Lương 1941.', $d);
        $this->matching($s, 'Nối mỗi tổ chức với năm thành lập của nó.', [['Việt Minh', '1941'], ['Đội Việt Nam Tuyên truyền Giải phóng quân', '1944'], ['Việt Nam Quốc dân Đảng', '1927']], 'Việt Minh 1941, Giải phóng quân 1944, Quốc dân Đảng 1927.', $d);
        $this->matching($s, 'Nối mỗi cuộc khởi nghĩa với địa bàn của nó.', [['Yên Bái', 'Yên Bái'], ['Nam Kì', 'Nam Bộ'], ['Bắc Sơn', 'Lạng Sơn']], 'Yên Bái – Yên Bái, Nam Kì – Nam Bộ, Bắc Sơn – Lạng Sơn.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với vai trò của ông.', [['Nguyễn Thái Học', 'Lãnh đạo khởi nghĩa Yên Bái'], ['Võ Nguyên Giáp', 'Chỉ huy Đội Giải phóng quân']], 'Nguyễn Thái Học – Yên Bái; Võ Nguyên Giáp – Giải phóng quân.', $d);
        $this->matching($s, 'Nối mỗi cao trào cách mạng với thời gian của nó.', [['Xô viết Nghệ Tĩnh', '1930 – 1931'], ['Mặt trận Dân chủ', '1936 – 1939'], ['Giải phóng dân tộc', '1939 – 1945']], 'Xô viết 1930–31, Dân chủ 1936–39, Giải phóng 1939–45.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm TRƯỚC hoặc SAU khi Đảng Cộng sản ra đời (1930).', [['Yên Thế', 'TRƯỚC 1930'], ['Đông Du', 'TRƯỚC 1930'], ['Xô viết Nghệ Tĩnh', 'SAU 1930'], ['Việt Minh', 'SAU 1930']], 'Yên Thế, Đông Du trước 1930.', $d);
        $this->sortQ($s, 'Kéo mỗi phong trào vào nhóm DO ĐẢNG CỘNG SẢN LÃNH ĐẠO hoặc KHÔNG.', [['Xô viết Nghệ Tĩnh', 'DO ĐẢNG'], ['Nam Kì', 'DO ĐẢNG'], ['Yên Bái', 'KHÔNG DO ĐẢNG'], ['Yên Thế', 'KHÔNG DO ĐẢNG']], 'Xô viết, Nam Kì do Đảng lãnh đạo.', $d);
        $this->sortQ($s, 'Kéo mỗi cuộc khởi nghĩa vào nhóm NĂM 1940 hoặc NĂM KHÁC.', [['Bắc Sơn', 'NĂM 1940'], ['Nam Kì', 'NĂM 1940'], ['Yên Bái', 'NĂM KHÁC'], ['Đô Lương', 'NĂM KHÁC']], 'Bắc Sơn, Nam Kì năm 1940.', $d);
        $this->sortQ($s, 'Kéo mỗi cuộc khởi nghĩa vào nhóm Ở MIỀN BẮC hoặc Ở MIỀN NAM.', [['Yên Bái', 'MIỀN BẮC'], ['Bắc Sơn', 'MIỀN BẮC'], ['Nam Kì', 'MIỀN NAM']], 'Yên Bái, Bắc Sơn miền Bắc; Nam Kì miền Nam.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ chức vào nhóm LỰC LƯỢNG VŨ TRANG hoặc MẶT TRẬN DÂN TỘC.', [['Đội Giải phóng quân', 'VŨ TRANG'], ['Việt Minh', 'MẶT TRẬN']], 'Giải phóng quân vũ trang; Việt Minh mặt trận.', $d);
        $this->fill($s, 'Khởi nghĩa Yên Bái (1930) do Việt Nam Quốc dân Đảng lãnh đạo, người đứng đầu là Nguyễn Thái ___.', [[0, 'Học']], 'Nguyễn Thái Học.', $d);
        $this->fill($s, 'Cao trào 1936–1939 còn gọi là phong trào Mặt trận Dân ___.', [[0, 'chủ']], 'Mặt trận Dân chủ.', $d);
        $this->fill($s, 'Hội nghị Trung ương 8 tháng 5/1941 quyết định thành lập Mặt trận Việt ___.', [[0, 'Minh']], 'Việt Minh.', $d);
        $this->fill($s, 'Khởi nghĩa Nam Kì bùng nổ năm ___.', [[0, '1940']], 'Năm 1940.', $d);
        $this->fill($s, 'Cuộc binh biến Đô Lương do Đội Cung lãnh đạo năm ___.', [[0, '1941']], 'Năm 1941.', $d);
    }

    private function seedLichSuThpt12Lop121(): void
    {
        $s = 'lich-su-thpt-12-lop-12-1'; $d = 'trung_binh';
        $this->quiz($s, 'Tổng khởi nghĩa tháng Tám năm 1945 giành chính quyền trong cả nước diễn ra trong khoảng thời gian nào?', ['2/9 – 10/9', '19/8 – 28/8', '1/8 – 15/8', '15/8 – 2/9'], 1, 'Tổng khởi nghĩa từ 19/8 đến 28/8/1945.', $d);
        $this->quiz($s, 'Chiến dịch Biên giới Thu – Đông diễn ra vào năm nào?', ['1947', '1950', '1953', '1954'], 1, 'Chiến dịch Biên giới năm 1950.', $d);
        $this->quiz($s, 'Chiến dịch Điện Biên Phủ mở màn vào ngày nào?', ['13/3/1954', '7/5/1954', '19/12/1946', '2/9/1945'], 0, 'Điện Biên Phủ mở màn ngày 13/3/1954.', $d);
        $this->quiz($s, 'Hiệp định Genève về chấm dứt chiến tranh ở Đông Dương được kí ngày nào?', ['7/5/1954', '21/7/1954', '27/1/1973', '30/4/1975'], 1, 'Hiệp định Genève kí ngày 21/7/1954.', $d);
        $this->quiz($s, 'Tướng Pháp trực tiếp chỉ huy tập đoàn cứ điểm Điện Biên Phủ là ai?', ['Navarre', 'De Castries', 'Cogny', 'Leclerc'], 1, 'De Castries chỉ huy ở Điện Biên Phủ.', $d);
        $this->matching($s, 'Nối mỗi chiến dịch trong kháng chiến chống Pháp với năm mở màn.', [['Việt Bắc', '1947'], ['Biên giới', '1950'], ['Điện Biên Phủ', '1954']], 'Việt Bắc 1947, Biên giới 1950, Điện Biên Phủ 1954.', $d);
        $this->matching($s, 'Nối mỗi sự kiện với ngày diễn ra của nó.', [['Tổng khởi nghĩa ở Hà Nội', '19/8/1945'], ['Tuyên ngôn Độc lập', '2/9/1945'], ['Toàn quốc kháng chiến', '19/12/1946']], '19/8 khởi nghĩa, 2/9 Tuyên ngôn, 19/12 kháng chiến.', $d);
        $this->matching($s, 'Nối mỗi nhân vật với vai trò của ông.', [['Võ Nguyên Giáp', 'Chỉ huy chiến dịch Điện Biên Phủ'], ['Hồ Chí Minh', 'Lãnh đạo cách mạng Việt Nam']], 'Võ Nguyên Giáp chỉ huy; Hồ Chí Minh lãnh đạo.', $d);
        $this->matching($s, 'Nối mỗi văn kiện với nội dung của nó.', [['Hiệp định Sơ bộ 6/3/1946', 'Hòa hoãn với Pháp'], ['Hiệp định Genève', 'Chấm dứt chiến tranh Đông Dương']], 'Sơ bộ hòa hoãn; Genève chấm dứt chiến tranh.', $d);
        $this->matching($s, 'Nối mỗi địa danh với sự kiện diễn ra ở đó.', [['Điện Biên Phủ', 'Chiến thắng 7/5/1954'], ['Quảng trường Ba Đình', 'Đọc Tuyên ngôn Độc lập']], 'Điện Biên Phủ chiến thắng; Ba Đình Tuyên ngôn.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU ngày 2/9/1945.', [['Tổng khởi nghĩa', 'TRƯỚC 2/9'], ['Toàn quốc kháng chiến', 'SAU 2/9'], ['Chiến dịch Điện Biên Phủ', 'SAU 2/9']], 'Tổng khởi nghĩa trước 2/9; kháng chiến, Điện Biên Phủ sau.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm THUỘC CÁCH MẠNG THÁNG TÁM hoặc KHÁNG CHIẾN CHỐNG PHÁP.', [['Tổng khởi nghĩa', 'CÁCH MẠNG THÁNG TÁM'], ['Tuyên ngôn Độc lập', 'CÁCH MẠNG THÁNG TÁM'], ['Chiến dịch Việt Bắc', 'CHỐNG PHÁP'], ['Điện Biên Phủ', 'CHỐNG PHÁP']], 'Tổng khởi nghĩa, Tuyên ngôn thuộc CMT8.', $d);
        $this->sortQ($s, 'Kéo mỗi chiến dịch vào nhóm CHIẾN DỊCH LỚN hoặc KHÔNG PHẢI.', [['Việt Bắc', 'CHIẾN DỊCH LỚN'], ['Biên giới', 'CHIẾN DỊCH LỚN'], ['Điện Biên Phủ', 'CHIẾN DỊCH LỚN'], ['Đồng Khởi', 'KHÔNG PHẢI']], 'Việt Bắc, Biên giới, Điện Biên Phủ là chiến dịch lớn.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm NĂM 1945 hoặc NĂM 1954.', [['Cách mạng tháng Tám', 'NĂM 1945'], ['Tuyên ngôn Độc lập', 'NĂM 1945'], ['Điện Biên Phủ', 'NĂM 1954'], ['Hiệp định Genève', 'NĂM 1954']], 'CMT8 năm 1945; Điện Biên Phủ, Genève năm 1954.', $d);
        $this->sortQ($s, 'Kéo mỗi lực lượng, nhân vật vào nhóm CỦA TA hoặc CỦA PHÁP (năm 1954).', [['Võ Nguyên Giáp', 'CỦA TA'], ['Quân đội nhân dân', 'CỦA TA'], ['De Castries', 'CỦA PHÁP'], ['Quân viễn chinh Pháp', 'CỦA PHÁP']], 'Võ Nguyên Giáp của ta; De Castries của Pháp.', $d);
        $this->fill($s, 'Tổng khởi nghĩa tháng Tám 1945 diễn ra từ 19/8 đến 28/8, giành chính quyền trong cả ___.', [[0, 'nước']], 'Giành chính quyền cả nước.', $d);
        $this->fill($s, 'Chiến dịch Biên giới diễn ra năm ___.', [[0, '1950']], 'Năm 1950.', $d);
        $this->fill($s, 'Chiến dịch Điện Biên Phủ mở màn ngày 13/3/___ .', [[0, '1954']], 'Mở màn 13/3/1954.', $d);
        $this->fill($s, 'Hiệp định Genève được kí ngày 21/7/___ .', [[0, '1954']], 'Kí 21/7/1954.', $d);
        $this->fill($s, 'Tướng Pháp chỉ huy ở Điện Biên Phủ là De ___.', [[0, 'Castries']], 'De Castries.', $d);
    }

    private function seedLichSuThpt12Lop122(): void
    {
        $s = 'lich-su-thpt-12-lop-12-2'; $d = 'kho';
        $this->quiz($s, 'Chiến lược "Chiến tranh đặc biệt" của Mĩ ở miền Nam được thực hiện trong thời gian nào?', ['1954–1960', '1961–1965', '1965–1968', '1969–1973'], 1, 'Chiến tranh đặc biệt 1961 – 1965.', $d);
        $this->quiz($s, 'Chiến lược "Chiến tranh cục bộ" của Mĩ được thực hiện trong thời gian nào?', ['1961–1965', '1965–1968', '1969–1973', '1973–1975'], 1, 'Chiến tranh cục bộ 1965 – 1968.', $d);
        $this->quiz($s, 'Chiến lược "Việt Nam hóa chiến tranh" của Mĩ được thực hiện trong thời gian nào?', ['1965–1968', '1969–1973', '1961–1965', '1973–1975'], 1, 'Việt Nam hóa chiến tranh 1969 – 1973.', $d);
        $this->quiz($s, 'Cuộc Tổng tiến công và nổi dậy Tết Mậu Thân diễn ra năm nào?', ['1965', '1968', '1972', '1975'], 1, 'Tết Mậu Thân năm 1968.', $d);
        $this->quiz($s, 'Chiến dịch Tây Nguyên (3/1975) mở màn bằng trận đánh nào?', ['Buôn Ma Thuột', 'Huế', 'Đà Nẵng', 'Xuân Lộc'], 0, 'Mở màn bằng trận Buôn Ma Thuột (10/3/1975).', $d);
        $this->matching($s, 'Nối mỗi chiến lược chiến tranh của Mĩ với thời gian thực hiện.', [['Chiến tranh đặc biệt', '1961 – 1965'], ['Chiến tranh cục bộ', '1965 – 1968'], ['Việt Nam hóa chiến tranh', '1969 – 1973']], 'Đặc biệt 61–65, cục bộ 65–68, Việt Nam hóa 69–73.', $d);
        $this->matching($s, 'Nối mỗi thắng lợi với ý nghĩa của nó.', [['Đồng Khởi 1960', 'Chuyển sang thế tiến công'], ['Mậu Thân 1968', 'Buộc Mĩ xuống thang chiến tranh'], ['Điện Biên Phủ trên không', 'Buộc Mĩ kí Hiệp định Paris']], 'Đồng Khởi tiến công; Mậu Thân buộc Mĩ xuống thang.', $d);
        $this->matching($s, 'Nối mỗi sự kiện với năm diễn ra của nó.', [['Tết Mậu Thân', '1968'], ['Hiệp định Paris', '1973'], ['Đại thắng mùa Xuân', '1975']], 'Mậu Thân 1968, Paris 1973, mùa Xuân 1975.', $d);
        $this->matching($s, 'Nối mỗi chiến dịch năm 1975 với thời gian của nó.', [['Tây Nguyên', '3/1975'], ['Huế – Đà Nẵng', '3/1975'], ['Hồ Chí Minh', '4/1975']], 'Tây Nguyên, Huế – Đà Nẵng tháng 3; Hồ Chí Minh tháng 4.', $d);
        $this->matching($s, 'Nối mỗi địa danh với chiến thắng gắn với nó.', [['Buôn Ma Thuột', 'Mở màn chiến dịch 1975'], ['Sài Gòn', 'Kết thúc chiến tranh']], 'Buôn Ma Thuột mở màn; Sài Gòn kết thúc.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU Tết Mậu Thân 1968.', [['Đồng Khởi', 'TRƯỚC 1968'], ['Chiến tranh đặc biệt', 'TRƯỚC 1968'], ['Hiệp định Paris', 'SAU 1968'], ['Đại thắng mùa Xuân', 'SAU 1968']], 'Đồng Khởi trước 1968; Paris, mùa Xuân sau 1968.', $d);
        $this->sortQ($s, 'Kéo mỗi chiến lược, phong trào vào nhóm CỦA MĨ hoặc CỦA TA.', [['Chiến tranh cục bộ', 'CỦA MĨ'], ['Việt Nam hóa chiến tranh', 'CỦA MĨ'], ['Đồng Khởi', 'CỦA TA'], ['Mậu Thân', 'CỦA TA']], 'Cục bộ, Việt Nam hóa của Mĩ; Đồng Khởi, Mậu Thân của ta.', $d);
        $this->sortQ($s, 'Kéo mỗi chiến thắng vào nhóm TRÊN BỘ hoặc TRÊN KHÔNG.', [['Mậu Thân 1968', 'TRÊN BỘ'], ['Chiến dịch Tây Nguyên', 'TRÊN BỘ'], ['Điện Biên Phủ trên không', 'TRÊN KHÔNG']], 'Điện Biên Phủ trên không là chiến thắng trên không.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU Hiệp định Paris (1973).', [['Mậu Thân', 'TRƯỚC 1973'], ['Điện Biên Phủ trên không', 'TRƯỚC 1973'], ['Đại thắng mùa Xuân', 'SAU 1973']], 'Mậu Thân trước 1973; mùa Xuân sau 1973.', $d);
        $this->sortQ($s, 'Kéo mỗi chiến dịch, sự kiện vào nhóm NĂM 1975 hoặc NĂM KHÁC.', [['Tây Nguyên', 'NĂM 1975'], ['Huế – Đà Nẵng', 'NĂM 1975'], ['Hồ Chí Minh', 'NĂM 1975'], ['Mậu Thân', 'NĂM KHÁC'], ['Paris', 'NĂM KHÁC']], 'Ba chiến dịch đều năm 1975.', $d);
        $this->fill($s, 'Chiến lược Chiến tranh đặc biệt của Mĩ được thực hiện trong các năm 1961–___.', [[0, '1965']], '1961 – 1965.', $d);
        $this->fill($s, 'Cuộc Tổng tiến công Tết Mậu Thân diễn ra năm ___.', [[0, '1968']], 'Năm 1968.', $d);
        $this->fill($s, 'Chiến dịch Tây Nguyên mở màn bằng trận Buôn Ma Thuột ngày 10/3/___ .', [[0, '1975']], 'Ngày 10/3/1975.', $d);
        $this->fill($s, 'Chiến dịch Huế – Đà Nẵng toàn thắng tháng 3/___ .', [[0, '1975']], 'Tháng 3/1975.', $d);
        $this->fill($s, 'Dinh Độc Lập bị quân ta chiếm ngày 30/4/1975, Tổng thống ngụy đầu hàng là Dương Văn ___.', [[0, 'Minh']], 'Dương Văn Minh đầu hàng.', $d);
    }

    private function seedLichSuThpt12Lop123(): void
    {
        $s = 'lich-su-thpt-12-lop-12-3'; $d = 'trung_binh';
        $this->quiz($s, 'Đại hội VI của Đảng, đề ra đường lối Đổi mới, họp vào tháng 12 năm nào?', ['1985', '1986', '1987', '1988'], 1, 'Đại hội VI họp tháng 12/1986.', $d);
        $this->quiz($s, 'Ai là Tổng Bí thư đã đề xướng công cuộc Đổi mới?', ['Trường Chinh', 'Nguyễn Văn Linh', 'Đỗ Mười', 'Lê Duẩn'], 1, 'Nguyễn Văn Linh đề xướng Đổi mới.', $d);
        $this->quiz($s, 'Chính sách "khoán hộ" trong nông nghiệp (Khoán 100) bắt đầu từ năm nào?', ['1976', '1981', '1986', '1989'], 1, 'Khoán 100 bắt đầu năm 1981.', $d);
        $this->quiz($s, 'Trước Đổi mới, tình trạng lạm phát ở Việt Nam được mô tả như thế nào?', ['Ổn định', 'Phi mã', 'Thấp', 'Không có'], 1, 'Lạm phát phi mã trước Đổi mới.', $d);
        $this->quiz($s, 'Sau Đổi mới, Việt Nam từ nước phải nhập khẩu đã trở thành nước xuất khẩu mặt hàng nào?', ['Dầu mỏ', 'Gạo', 'Cà phê', 'Thép'], 1, 'Việt Nam trở thành nước xuất khẩu gạo.', $d);
        $this->matching($s, 'Nối mỗi sự kiện của công cuộc Đổi mới với năm diễn ra.', [['Đại hội VI', '1986'], ['Khoán 100', '1981'], ['Xóa bỏ bao cấp', '1989']], 'Khoán 100 năm 1981, Đại hội VI 1986, xóa bao cấp 1989.', $d);
        $this->matching($s, 'Nối mỗi chính sách với nội dung của nó.', [['Khoán hộ', 'Giao quyền sử dụng đất cho hộ nông dân'], ['Mở cửa', 'Hội nhập kinh tế quốc tế']], 'Khoán hộ giao đất; mở cửa hội nhập.', $d);
        $this->matching($s, 'Nối mỗi thành tựu với lĩnh vực của nó.', [['Xuất khẩu gạo', 'Nông nghiệp'], ['Thu hút đầu tư nước ngoài', 'Đầu tư']], 'Gạo nông nghiệp; FDI đầu tư.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với nội dung của nó.', [['Bao cấp', 'Nhà nước bao hết mọi thứ'], ['Kinh tế thị trường', 'Vận hành theo quy luật thị trường']], 'Bao cấp nhà nước bao hết; thị trường theo quy luật.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn với đặc điểm của nó.', [['Trước 1986', 'Khủng hoảng kinh tế – xã hội'], ['Sau 1986', 'Đổi mới toàn diện']], 'Trước 1986 khủng hoảng; sau 1986 đổi mới.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm KINH TẾ KẾ HOẠCH HÓA hoặc KINH TẾ THỊ TRƯỜNG.', [['Bao cấp', 'KẾ HOẠCH HÓA'], ['Tem phiếu', 'KẾ HOẠCH HÓA'], ['Khoán hộ', 'THỊ TRƯỜNG'], ['Mở cửa', 'THỊ TRƯỜNG']], 'Bao cấp, tem phiếu kế hoạch hóa; khoán hộ, mở cửa thị trường.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm TRƯỚC hoặc SAU Đại hội VI (1986).', [['Khủng hoảng', 'TRƯỚC 1986'], ['Lạm phát phi mã', 'TRƯỚC 1986'], ['Tăng trưởng kinh tế', 'SAU 1986'], ['Xuất khẩu gạo', 'SAU 1986']], 'Trước 1986 khủng hoảng; sau 1986 tăng trưởng.', $d);
        $this->sortQ($s, 'Kéo mỗi kết quả vào nhóm THÀNH TỰU hoặc KHÓ KHĂN sau Đổi mới.', [['Xuất khẩu gạo', 'THÀNH TỰU'], ['Kiềm chế lạm phát', 'THÀNH TỰU'], ['Tham nhũng', 'KHÓ KHĂN'], ['Phân hóa giàu nghèo', 'KHÓ KHĂN']], 'Gạo, lạm phát là thành tựu; tham nhũng là khó khăn.', $d);
        $this->sortQ($s, 'Kéo mỗi chính sách vào nhóm NÔNG NGHIỆP hoặc CÔNG NGHIỆP, DỊCH VỤ.', [['Khoán hộ', 'NÔNG NGHIỆP'], ['Giao đất cho nông dân', 'NÔNG NGHIỆP'], ['Mở khu công nghiệp', 'CÔNG NGHIỆP'], ['Thu hút FDI', 'CÔNG NGHIỆP']], 'Khoán hộ nông nghiệp; khu công nghiệp, FDI công nghiệp.', $d);
        $this->sortQ($s, 'Kéo mỗi thành tựu vào nhóm TRONG NƯỚC hoặc QUỐC TẾ.', [['Xóa đói giảm nghèo', 'TRONG NƯỚC'], ['Gia nhập ASEAN', 'QUỐC TẾ'], ['Bình thường hóa với Mĩ', 'QUỐC TẾ']], 'Xóa đói trong nước; ASEAN, Mĩ quốc tế.', $d);
        $this->fill($s, 'Đại hội VI của Đảng, đề ra đường lối Đổi mới, họp tháng 12/___ .', [[0, '1986']], 'Tháng 12/1986.', $d);
        $this->fill($s, 'Tổng Bí thư đề xướng công cuộc Đổi mới là Nguyễn Văn ___.', [[0, 'Linh']], 'Nguyễn Văn Linh.', $d);
        $this->fill($s, 'Chính sách khoán hộ trong nông nghiệp (Khoán 100) bắt đầu năm ___.', [[0, '1981']], 'Năm 1981.', $d);
        $this->fill($s, 'Trước Đổi mới, lạm phát ở Việt Nam ở mức phi ___.', [[0, 'mã']], 'Lạm phát phi mã.', $d);
        $this->fill($s, 'Sau Đổi mới, Việt Nam từ nước nhập khẩu trở thành nước xuất khẩu ___.', [[0, 'gạo']], 'Xuất khẩu gạo.', $d);
    }

    private function seedLichSuThpt12Lop124(): void
    {
        $s = 'lich-su-thpt-12-lop-12-4'; $d = 'trung_binh';
        $this->quiz($s, 'Việt Nam và Mĩ bình thường hóa quan hệ ngoại giao vào năm nào?', ['1990', '1995', '2000', '2007'], 1, 'Bình thường hóa với Mĩ năm 1995.', $d);
        $this->quiz($s, 'Việt Nam gia nhập Liên hợp quốc vào năm nào?', ['1976', '1977', '1995', '2007'], 1, 'Gia nhập Liên hợp quốc năm 1977.', $d);
        $this->quiz($s, 'Hiệp định Thương mại tự do Việt Nam – EU (EVFTA) chính thức có hiệu lực năm nào?', ['2018', '2019', '2020', '2021'], 2, 'EVFTA có hiệu lực năm 2020.', $d);
        $this->quiz($s, 'Việt Nam tham gia Hiệp định Đối tác Toàn diện và Tiến bộ xuyên Thái Bình Dương (CPTPP) năm nào?', ['2016', '2018', '2020', '2022'], 1, 'Tham gia CPTPP năm 2018.', $d);
        $this->quiz($s, 'Việt Nam đã đăng cai Hội nghị cấp cao APEC vào các năm nào?', ['1998 và 2006', '2006 và 2017', '2017 và 2020', '2006 và 2020'], 1, 'Việt Nam đăng cai APEC 2006 và 2017.', $d);
        $this->matching($s, 'Nối mỗi sự kiện đối ngoại với năm diễn ra của nó.', [['Bình thường hóa với Mĩ', '1995'], ['Gia nhập Liên hợp quốc', '1977'], ['EVFTA có hiệu lực', '2020']], 'Mĩ 1995, LHQ 1977, EVFTA 2020.', $d);
        $this->matching($s, 'Nối mỗi tổ chức quốc tế với trụ sở chính của nó.', [['ASEAN', 'Jakarta'], ['Liên hợp quốc', 'New York'], ['WTO', 'Geneva']], 'ASEAN – Jakarta, LHQ – New York, WTO – Geneva.', $d);
        $this->matching($s, 'Nối mỗi hiệp định thương mại với năm kí kết.', [['EVFTA', '2019'], ['CPTPP', '2018']], 'EVFTA kí 2019, CPTPP 2018.', $d);
        $this->matching($s, 'Nối mỗi vai trò quốc tế của Việt Nam với thời gian.', [['Chủ tịch ASEAN', '2020'], ['Ủy viên không thường trực HĐBA', '2020 – 2021']], 'Chủ tịch ASEAN 2020; HĐBA 2020–2021.', $d);
        $this->matching($s, 'Nối mỗi đối tác với hiệp định thương mại của Việt Nam.', [['Liên minh châu Âu', 'EVFTA'], ['11 nước vành đai Thái Bình Dương', 'CPTPP']], 'EU – EVFTA; 11 nước – CPTPP.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm TRƯỚC NĂM 2000 hoặc TỪ NĂM 2000 TRỞ ĐI.', [['Gia nhập ASEAN (1995)', 'TRƯỚC 2000'], ['Gia nhập APEC (1998)', 'TRƯỚC 2000'], ['Gia nhập WTO (2007)', 'TỪ 2000'], ['EVFTA (2020)', 'TỪ 2000']], 'ASEAN, APEC trước 2000; WTO, EVFTA từ 2000.', $d);
        $this->sortQ($s, 'Kéo mỗi tổ chức vào nhóm TỔ CHỨC KHU VỰC hoặc TỔ CHỨC TOÀN CẦU.', [['ASEAN', 'KHU VỰC'], ['APEC', 'KHU VỰC'], ['Liên hợp quốc', 'TOÀN CẦU'], ['WTO', 'TOÀN CẦU']], 'ASEAN, APEC khu vực; LHQ, WTO toàn cầu.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm ĐỐI NGOẠI ĐA PHƯƠNG hoặc SONG PHƯƠNG.', [['Gia nhập ASEAN', 'ĐA PHƯƠNG'], ['Gia nhập WTO', 'ĐA PHƯƠNG'], ['Bình thường hóa với Mĩ', 'SONG PHƯƠNG'], ['Kí EVFTA', 'SONG PHƯƠNG']], 'ASEAN, WTO đa phương; Mĩ, EVFTA song phương.', $d);
        $this->sortQ($s, 'Kéo mỗi sự kiện vào nhóm THẬP NIÊN 1990 hoặc THẬP NIÊN 2010.', [['Gia nhập ASEAN', '1990'], ['Bình thường hóa với Mĩ', '1990'], ['Kí EVFTA', '2010'], ['Chủ tịch ASEAN', '2010']], 'ASEAN, Mĩ thập niên 1990; EVFTA thập niên 2010.', $d);
        $this->sortQ($s, 'Kéo mỗi tên gọi vào nhóm HIỆP ĐỊNH THƯƠNG MẠI hoặc TỔ CHỨC QUỐC TẾ.', [['EVFTA', 'HIỆP ĐỊNH'], ['CPTPP', 'HIỆP ĐỊNH'], ['ASEAN', 'TỔ CHỨC'], ['WTO', 'TỔ CHỨC']], 'EVFTA, CPTPP hiệp định; ASEAN, WTO tổ chức.', $d);
        $this->fill($s, 'Việt Nam bình thường hóa quan hệ với Mĩ năm ___.', [[0, '1995']], 'Năm 1995.', $d);
        $this->fill($s, 'Việt Nam gia nhập Liên hợp quốc năm ___.', [[0, '1977']], 'Năm 1977.', $d);
        $this->fill($s, 'Hiệp định EVFTA có hiệu lực năm ___.', [[0, '2020']], 'Có hiệu lực 2020.', $d);
        $this->fill($s, 'Việt Nam tham gia Hiệp định CPTPP năm ___.', [[0, '2018']], 'Tham gia năm 2018.', $d);
        $this->fill($s, 'Trụ sở ASEAN đặt tại ___, Indonesia.', [[0, 'Jakarta']], 'Trụ sở ở Jakarta.', $d);
    }
}
