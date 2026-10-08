<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Bổ sung câu hỏi cho 15 bài Trải nghiệm & Hướng nghiệp HIỆN CÓ (nhóm G6, phần c1 - nửa đầu).
 *
 * Mỗi bài: 5 câu MỚI cho mỗi kiểu chơi (quiz/matching/sort/fill).
 * Nội dung tiếng Việt tự viết 100%, bám topic + khối lớp + độ khó của bài,
 * không trùng prompt đã có.
 * Idempotent: chạy lại không thêm câu mới (giới hạn 8 câu/kiểu/bài + kiểm tra prompt trùng).
 */
class AdditionalQuestionsGroup6c1Seeder extends Seeder
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
        $this->seedLapThoiGianBieu();
        $this->seedGiaoTiepLamViecNhom();
        $this->seedAnToanGiaoThong();
        $this->seedPhanLoaiRac();
        $this->seedCacNgheQuenThuoc();
        $this->seedUocMoNgheNghiepCuaEm();
        $this->seedKyNangSongLop61();
        $this->seedKyNangSongLop62();
        $this->seedKyNangSongLop71();
        $this->seedKyNangSongLop72();
        $this->seedAnToanMoiTruongLop71();
        $this->seedAnToanMoiTruongLop72();
        $this->seedAnToanMoiTruongLop81();
        $this->seedAnToanMoiTruongLop82();
        $this->seedDinhHuongNgheNghiepLop81();
    }

    private function seedLapThoiGianBieu(): void
    {
        $s = 'tnhn-lap-thoi-gian-bieu';
        $d = 'de';

        $this->quiz($s, 'Buổi sáng trước khi đi học, việc nào nên có trong thời gian biểu?', ['Chuẩn bị sách vở, đồ dùng học tập', 'Ngủ nướng tới giờ học', 'Chơi game một tiếng', 'Xem ti vi thật lâu'], 0, 'Chuẩn bị đồ dùng từ buổi sáng giúp em chủ động và không quên sách vở khi tới lớp.', $d);
        $this->quiz($s, 'Nên dành bao nhiêu thời gian để học bài buổi tối?', ['Học 3-4 giờ liên tục không nghỉ', 'Khoảng 1,5-2 giờ chia thành nhiều đợt ngắn', 'Không cần học, đi ngủ sớm', 'Học thâu đêm'], 1, 'Học 1,5-2 giờ chia thành đợt ngắn giúp não tiếp thu tốt hơn là học liên tục quá lâu.', $d);
        $this->quiz($s, 'Thời gian biểu hợp lý cần có đặc điểm gì?', ['Kín hết cả ngày, không có giờ nghỉ', 'Học tập, nghỉ ngơi và vui chơi cân đối', 'Chỉ toàn giờ chơi', 'Thay đổi tùy hứng mỗi ngày'], 1, 'Thời gian biểu tốt phải cân đối giữa học tập, nghỉ ngơi và vui chơi để cơ thể không quá tải.', $d);
        $this->quiz($s, 'Khi bài tập nhiều hơn dự tính, em nên làm gì?', ['Bỏ bớt bài, đi chơi', 'Điều chỉnh thời gian biểu, cắt bớt giờ giải trí', 'Thức tới 2-3 giờ sáng', 'Nhờ bạn làm hộ'], 1, 'Điều chỉnh linh hoạt thời gian biểu là kỹ năng quan trọng khi khối lượng việc thay đổi.', $d);
        $this->quiz($s, 'Thời điểm nào trong ngày trí óc minh mẫn nhất để học bài khó?', ['Nửa đêm', 'Buổi sáng sau khi thức dậy tỉnh táo', 'Lúc đang ăn cơm', 'Khi đang xem phim'], 1, 'Buổi sáng sau giấc ngủ đủ, đầu óc tỉnh táo nhất nên phù hợp để học những bài khó.', $d);

        $this->matching($s, 'Nối mỗi buổi trong ngày với hoạt động nên ưu tiên.', [['Buổi sáng trước giờ học', 'Chuẩn bị đồ dùng, ôn bài cũ'], ['Buổi trưa', 'Ăn cơm và nghỉ ngơi'], ['Buổi chiều', 'Làm bài tập, chơi thể thao'], ['Buổi tối', 'Ôn bài, chuẩn bị cho ngày mai']], 'Sắp xếp việc theo từng buổi giúp ngày học của em khoa học và không bị dồn việc.', $d);
        $this->matching($s, 'Nối mỗi công việc với thời điểm nên làm.', [['Bài tập khó', 'Làm trước khi mệt'], ['Việc nhẹ', 'Làm sau giờ học căng'], ['Dọn góc học tập', 'Cuối tuần'], ['Chuẩn bị cặp sách', 'Tối hôm trước']], 'Làm việc khó khi còn tỉnh táo, việc nhẹ để sau giúp em đỡ mệt mà vẫn xong việc.', $d);
        $this->matching($s, 'Nối mỗi thói quen với tác dụng của nó.', [['Ghi việc cần làm ra giấy', 'Không quên việc'], ['Đặt báo thức', 'Dậy đúng giờ'], ['Đánh dấu việc đã xong', 'Có thêm động lực'], ['Xem lại thời gian biểu mỗi tối', 'Ngày mai suôn sẻ']], 'Những thói quen nhỏ này giúp em quản lý thời gian tốt hơn mỗi ngày.', $d);
        $this->matching($s, 'Nối mỗi sai lầm với cách sửa.', [['Học tới khuya', 'Ngủ sớm, học từ chiều'], ['Chơi quên giờ', 'Đặt hẹn giờ báo'], ['Việc gấp để mai', 'Làm ngay việc quan trọng'], ['Ôm đồm quá nhiều việc', 'Chia nhỏ, làm từng việc một']], 'Nhận ra sai lầm và sửa kịp thời giúp thời gian biểu ngày càng hợp lý hơn.', $d);
        $this->matching($s, 'Nối mỗi khoảng thời gian nghỉ với cách nghỉ đúng.', [['Nghỉ 5-10 phút giữa giờ học', 'Đứng dậy vận động nhẹ'], ['Nghỉ trưa', 'Ngủ ngắn 20-30 phút'], ['Cuối tuần', 'Vui chơi, thăm người thân'], ['Nghỉ giữa buổi học dài', 'Uống nước, nhìn xa thư giãn mắt']], 'Nghỉ ngơi đúng cách giúp cơ thể phục hồi và học tập hiệu quả hơn.', $d);

        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Lên kế hoạch trước / Để tới đâu hay tới đó.', [['Viết danh sách việc cần làm tối nay', 'Lên kế hoạch trước'], ['Chuẩn bị cặp sách từ tối hôm trước', 'Lên kế hoạch trước'], ['Nhớ lịch thi từ đầu tuần', 'Lên kế hoạch trước'], ['Để mai tính', 'Để tới đâu hay tới đó'], ['Làm bài tập lúc nào nhớ ra', 'Để tới đâu hay tới đó'], ['Quên mang đồ dùng học tập', 'Để tới đâu hay tới đó']], 'Lên kế hoạch trước giúp em chủ động, còn để tới đâu hay tới đó dễ dẫn đến quên việc.', $d);
        $this->sortQ($s, 'Xếp các khung giờ vào nhóm: Nên học bài / Nên nghỉ ngơi.', [['8-9 giờ tối', 'Nên học bài'], ['Buổi sáng sớm tỉnh táo', 'Nên học bài'], ['5-6 giờ chiều sau giờ học', 'Nên học bài'], ['Giờ ăn cơm', 'Nên nghỉ ngơi'], ['Buổi trưa nắng nóng', 'Nên nghỉ ngơi'], ['Đêm khuya sau 11 giờ', 'Nên nghỉ ngơi']], 'Học vào lúc tỉnh táo và nghỉ ngơi đầy đủ giúp cơ thể khỏe, học mau thuộc.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Quan trọng / Ít quan trọng.', [['Ôn bài cho bài kiểm tra ngày mai', 'Quan trọng'], ['Làm bài tập về nhà', 'Quan trọng'], ['Chuẩn bị bài thuyết trình', 'Quan trọng'], ['Xem video giải trí 2 tiếng', 'Ít quan trọng'], ['Chơi game cả buổi chiều', 'Ít quan trọng'], ['Lướt mạng xã hội', 'Ít quan trọng']], 'Phân biệt việc quan trọng và ít quan trọng là bước đầu để ưu tiên đúng.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Đúng / Sai khi dùng thời gian.', [['Ưu tiên việc gấp trước', 'Đúng'], ['Làm một việc một lúc', 'Đúng'], ['Vừa học vừa nhắn tin', 'Sai'], ['Để bài tập tới sát giờ nộp', 'Sai']], 'Tập trung một việc và ưu tiên việc gấp giúp em dùng thời gian hiệu quả.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Nên làm buổi sáng / Nên làm buổi tối.', [['Ôn bài cũ', 'Nên làm buổi sáng'], ['Tập thể dục nhẹ', 'Nên làm buổi sáng'], ['Chuẩn bị quần áo, cặp sách cho ngày mai', 'Nên làm buổi tối'], ['Tổng kết việc đã làm trong ngày', 'Nên làm buổi tối']], 'Buổi sáng hợp với việc cần tỉnh táo, buổi tối hợp với việc chuẩn bị cho ngày mai.', $d);

        $this->fill($s, 'Buổi tối nên dành ___ phút để xem lại thời gian biểu ngày mai.', [[0, '5']], 'Chỉ cần 5 phút mỗi tối để kiểm tra kế hoạch, ngày mai sẽ trôi chảy hơn nhiều.', $d);
        $this->fill($s, 'Để không quên việc, em nên ___ danh sách việc cần làm ra giấy.', [[0, 'ghi']], 'Viết việc ra giấy giúp đầu óc nhẹ nhàng hơn và không bỏ sót việc quan trọng.', $d);
        $this->fill($s, 'Học tập trung 25 phút rồi nghỉ ___ phút sẽ hiệu quả hơn học liên tục.', [[0, '5']], 'Nghỉ ngắn giữa các đợt học giúp não phục hồi và ghi nhớ tốt hơn.', $d);
        $this->fill($s, 'Việc gì quan trọng thì làm ___, đừng để tới phút cuối mới vội.', [[0, 'sớm']], 'Làm sớm việc quan trọng giúp em tránh bị động và có thời gian sửa chữa.', $d);
        $this->fill($s, 'Một thời gian biểu tốt phải có cả giờ học, giờ ___ và giờ vui chơi.', [[0, 'nghỉ']], 'Cân đối học tập, nghỉ ngơi và vui chơi giúp cơ thể khỏe mạnh, tinh thần thoải mái.', $d);
    }

    private function seedGiaoTiepLamViecNhom(): void
    {
        $s = 'tnhn-giao-tiep-lam-viec-nhom';
        $d = 'de';

        $this->quiz($s, 'Khi muốn phát biểu ý kiến trong nhóm, em nên làm gì?', ['Nói to át lời bạn', 'Giơ tay xin phép rồi nói rõ ràng', 'Nói chen vào lúc bạn đang nói', 'Im lặng không nói gì'], 1, 'Giơ tay xin phép rồi trình bày rõ ràng thể hiện sự tôn trọng và giúp mọi người nghe rõ ý em.', $d);
        $this->quiz($s, 'Khi bạn trong nhóm làm sai, em nên góp ý thế nào?', ['Chê bai bạn trước cả nhóm', 'Nói riêng với bạn, nhẹ nhàng và đưa ra gợi ý', 'Mách thầy cô ngay lập tức', 'Bỏ mặc bạn'], 1, 'Góp ý riêng, nhẹ nhàng kèm gợi ý giúp bạn sửa sai mà không bị tổn thương.', $d);
        $this->quiz($s, 'Người trưởng nhóm có nhiệm vụ gì?', ['Làm hết mọi việc một mình', 'Phân công, đôn đốc và động viên các bạn', 'Ra lệnh cho mọi người', 'Chỉ ngồi xem các bạn làm'], 1, 'Trưởng nhóm là người tổ chức, phân công và động viên chứ không phải làm thay mọi người.', $d);
        $this->quiz($s, 'Khi nhóm chưa thống nhất được ý kiến, cách tốt nhất là gì?', ['Ai to tiếng hơn thì nghe người đó', 'Bỏ phiếu công bằng theo số đông', 'Giải tán nhóm', 'Để một bạn quyết hết'], 1, 'Bỏ phiếu theo số đông là cách công bằng để nhóm quyết định khi có nhiều ý kiến khác nhau.', $d);
        $this->quiz($s, 'Câu nói nào thể hiện sự tôn trọng bạn bè?', ['Ý kiến của bạn hay đấy, mình bổ sung thêm nhé', 'Ý của bạn sai bét', 'Kệ bạn, mình không quan tâm', 'Bạn im đi'], 0, 'Ghi nhận ý kiến của bạn trước khi bổ sung thể hiện sự tôn trọng và tinh thần hợp tác.', $d);

        $this->matching($s, 'Nối mỗi vai trò trong nhóm với công việc.', [['Trưởng nhóm', 'Phân công và đôn đốc chung'], ['Thư ký', 'Ghi chép ý kiến, kết quả'], ['Người trình bày', 'Nói kết quả trước lớp'], ['Thành viên', 'Hoàn thành phần việc của mình']], 'Mỗi vai trò có nhiệm vụ riêng, ai cũng quan trọng để nhóm hoạt động tốt.', $d);
        $this->matching($s, 'Nối mỗi câu nói với tác dụng.', [['Cảm ơn bạn đã giúp mình', 'Khích lệ bạn bè'], ['Mình xin lỗi vì đến muộn', 'Nhận lỗi chân thành'], ['Bạn làm tốt lắm', 'Động viên tinh thần'], ['Cho mình hỏi ý này được không', 'Lịch sự xin phép']], 'Lời nói đúng lúc có thể khích lệ, động viên và giữ hòa khí trong nhóm.', $d);
        $this->matching($s, 'Nối mỗi hành vi với đánh giá.', [['Lắng nghe hết ý bạn', 'Tôn trọng'], ['Ngắt lời bạn đang nói', 'Thiếu tôn trọng'], ['Chia sẻ tài liệu cho cả nhóm', 'Hợp tác tốt'], ['Giấu bài không cho bạn xem', 'Ích kỷ']], 'Hành vi tôn trọng và chia sẻ giúp nhóm gắn bó, ngược lại sẽ làm mất đoàn kết.', $d);
        $this->matching($s, 'Nối mỗi tình huống nhóm với cách xử lý.', [['Bạn quên làm phần việc', 'Nhắc nhẹ và cùng giúp'], ['Hai bạn cãi nhau', 'Bình tĩnh hòa giải'], ['Nhóm sắp hết giờ', 'Chia việc làm song song'], ['Có ý tưởng mới hay', 'Ghi lại để bàn thêm']], 'Xử lý tình huống khéo léo giúp nhóm vượt khó khăn mà vẫn giữ được tinh thần chung.', $d);
        $this->matching($s, 'Nối mỗi kỹ năng với biểu hiện.', [['Lắng nghe', 'Nhìn bạn, gật đầu'], ['Thuyết trình', 'Nói rõ ràng, tự tin'], ['Phản biện', 'Nêu ý khác một cách lịch sự'], ['Hợp tác', 'Giúp bạn khi bạn cần']], 'Rèn luyện các kỹ năng này giúp em làm việc nhóm ngày càng hiệu quả.', $d);

        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Nên làm / Không nên làm khi thảo luận nhóm.', [['Nghe hết ý kiến của bạn', 'Nên làm'], ['Tôn trọng ý kiến khác mình', 'Nên làm'], ['Cười nhạo ý kiến của bạn', 'Không nên làm'], ['Nói chuyện riêng khi bạn trình bày', 'Không nên làm']], 'Thảo luận tốt cần lắng nghe và tôn trọng, tránh những hành vi làm bạn tổn thương.', $d);
        $this->sortQ($s, 'Xếp các câu nói vào nhóm: Lịch sự / Chưa lịch sự.', [['Bạn cho mình mượn bút được không', 'Lịch sự'], ['Cảm ơn bạn nhiều nhé', 'Lịch sự'], ['Đồ ngốc, cái này cũng không biết', 'Chưa lịch sự'], ['Tránh ra cho tôi đi', 'Chưa lịch sự']], 'Lời nói lịch sự giúp mọi người vui vẻ, lời nói thô lỗ làm mất tình bạn.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Giúp nhóm đoàn kết / Làm nhóm rạn nứt.', [['Cùng nhau dọn dẹp sau hoạt động', 'Giúp nhóm đoàn kết'], ['Khen ngợi nỗ lực của bạn', 'Giúp nhóm đoàn kết'], ['Đổ lỗi cho bạn khi thất bại', 'Làm nhóm rạn nứt'], ['Chia bè kéo cánh', 'Làm nhóm rạn nứt']], 'Cùng chia sẻ và động viên giúp nhóm đoàn kết, đổ lỗi và chia rẽ làm nhóm tan vỡ.', $d);
        $this->sortQ($s, 'Xếp các biểu hiện vào nhóm: Lắng nghe tích cực / Chưa lắng nghe.', [['Nhìn vào mắt người nói', 'Lắng nghe tích cực'], ['Gật đầu, hỏi lại khi chưa rõ', 'Lắng nghe tích cực'], ['Nghịch điện thoại khi bạn nói', 'Chưa lắng nghe'], ['Ngáp dài, nhìn đi chỗ khác', 'Chưa lắng nghe']], 'Lắng nghe tích cực thể hiện qua ánh mắt và thái độ chú ý tới người nói.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Trách nhiệm của trưởng nhóm / Không phải.', [['Phân công công việc', 'Trách nhiệm của trưởng nhóm'], ['Nhắc nhở tiến độ', 'Trách nhiệm của trưởng nhóm'], ['Làm thay hết việc của bạn', 'Không phải'], ['Tự ý quyết định mọi thứ', 'Không phải']], 'Trưởng nhóm tổ chức và điều phối, không phải làm thay hay áp đặt mọi người.', $d);

        $this->fill($s, 'Khi thảo luận, mỗi bạn nên được ___ ý kiến của mình một cách bình đẳng.', [[0, 'trình bày']], 'Ai cũng có quyền trình bày ý kiến, đó là sự tôn trọng trong làm việc nhóm.', $d);
        $this->fill($s, 'Nói lời ___ khi làm phiền bạn là phép lịch sự tối thiểu.', [[0, 'xin lỗi']], 'Biết xin lỗi khi làm phiền người khác thể hiện sự tôn trọng và văn minh.', $d);
        $this->fill($s, 'Làm việc nhóm hiệu quả cần sự ___ tác của mọi thành viên.', [[0, 'hợp']], 'Hợp tác nghĩa là cùng góp sức, không ai đứng ngoài hay làm một mình.', $d);
        $this->fill($s, 'Khi bất đồng ý kiến, hãy ___ tĩnh lắng nghe ý kiến của bạn.', [[0, 'bình']], 'Bình tĩnh lắng nghe giúp hiểu ý bạn và tìm được tiếng nói chung.', $d);
        $this->fill($s, 'Người tổng hợp ý kiến và báo cáo kết quả của nhóm gọi là người ___ bày.', [[0, 'trình']], 'Người trình bày đại diện nhóm nói kết quả trước lớp nên cần nói rõ ràng, tự tin.', $d);
    }

    private function seedAnToanGiaoThong(): void
    {
        $s = 'tnhn-an-toan-giao-thong';
        $d = 'de';

        $this->quiz($s, 'Khi đi xe đạp, em KHÔNG được làm gì?', ['Đi đúng làn đường', 'Buông cả hai tay để khoe', 'Đội mũ bảo hiểm', 'Quan sát trước khi rẽ'], 1, 'Buông tay khi đi xe rất nguy hiểm vì không kịp xử lý khi có tình huống bất ngờ.', $d);
        $this->quiz($s, 'Muốn rẽ trái khi đang đi xe đạp, em phải làm gì?', ['Rẽ ngay không cần nhìn', 'Giơ tay xin đường và quan sát', 'Phóng nhanh qua cho kịp', 'Đi ngược chiều cho gần'], 1, 'Trước khi rẽ phải quan sát và ra tín hiệu bằng tay để các xe khác biết mà tránh.', $d);
        $this->quiz($s, 'Khi gặp xe ưu tiên (cứu thương, cứu hỏa) đang hú còi, em nên làm gì?', ['Chạy đua theo xe', 'Nhường đường ngay lập tức', 'Chặn đầu xe', 'Bấm còi theo cho vui'], 1, 'Xe ưu tiên đang làm nhiệm vụ khẩn cấp nên mọi người phải nhường đường ngay.', $d);
        $this->quiz($s, 'Đi xe đạp điện cần lưu ý gì về tốc độ?', ['Đi càng nhanh càng tốt', 'Đi đúng tốc độ quy định, không phóng nhanh', 'Lạng lách cho vui', 'Đi sát xe tải lớn'], 1, 'Đi đúng tốc độ quy định giúp em kịp xử lý khi có tình huống bất ngờ trên đường.', $d);
        $this->quiz($s, 'Khi trời mưa đường trơn, người đi xe đạp nên làm gì?', ['Đi nhanh cho khỏi ướt', 'Đi chậm, giữ khoảng cách an toàn', 'Phanh gấp đột ngột', 'Đi vào làn ô tô cho nhanh'], 1, 'Đường trơn dễ trượt ngã nên phải đi chậm và giữ khoảng cách với xe phía trước.', $d);

        $this->matching($s, 'Nối mỗi đèn tín hiệu với hành động.', [['Đèn đỏ', 'Dừng lại'], ['Đèn xanh', 'Được đi'], ['Đèn vàng', 'Đi chậm, chuẩn bị dừng'], ['Đèn đỏ nhấp nháy ở nơi giao nhau', 'Dừng và quan sát rồi đi']], 'Hiểu đúng tín hiệu đèn giúp em tham gia giao thông an toàn và đúng luật.', $d);
        $this->matching($s, 'Nối mỗi loại phương tiện với quy định.', [['Xe đạp', 'Đi bên phải, đúng làn'], ['Xe đạp điện', 'Đội mũ bảo hiểm'], ['Người ngồi sau xe máy', 'Đội mũ bảo hiểm, bám chắc'], ['Người đi bộ', 'Đi trên vỉa hè']], 'Mỗi loại phương tiện có quy định riêng để đảm bảo an toàn cho mọi người.', $d);
        $this->matching($s, 'Nối mỗi tình huống với cách xử lý đúng.', [['Muốn qua đường', 'Đi trên vạch kẻ'], ['Đường vắng không có đèn', 'Nhìn trái-phải rồi qua'], ['Xe phía sau bấm còi', 'Đi sát lề, không hoảng'], ['Trời tối', 'Mặc áo sáng màu, bật đèn xe']], 'Xử lý đúng từng tình huống giúp em tránh tai nạn khi tham gia giao thông.', $d);
        $this->matching($s, 'Nối mỗi hành vi với đánh giá.', [['Đội mũ bảo hiểm', 'An toàn'], ['Vừa đi vừa nghe nhạc to', 'Nguy hiểm'], ['Đi đúng làn đường', 'An toàn'], ['Đèo 3 người trên xe đạp điện', 'Nguy hiểm']], 'Hành vi an toàn bảo vệ em, hành vi nguy hiểm có thể gây tai nạn cho em và người khác.', $d);
        $this->matching($s, 'Nối mỗi biển báo cấm với ý nghĩa.', [['Biển cấm xe đạp', 'Xe đạp không được vào'], ['Biển cấm đi ngược chiều', 'Không được đi ngược'], ['Biển giới hạn tốc độ', 'Không đi quá số ghi trên biển'], ['Biển cấm bấm còi', 'Không bấm còi ở khu vực này']], 'Nhận biết biển báo cấm giúp em tránh vi phạm luật giao thông.', $d);

        $this->sortQ($s, 'Xếp các hành vi vào nhóm: An toàn / Nguy hiểm khi tham gia giao thông.', [['Đội mũ bảo hiểm', 'An toàn'], ['Đi đúng làn đường', 'An toàn'], ['Lạng lách đánh võng', 'Nguy hiểm'], ['Vượt đèn đỏ', 'Nguy hiểm']], 'Chọn hành vi an toàn là cách em tự bảo vệ mình trên đường.', $d);
        $this->sortQ($s, 'Xếp các vị trí vào nhóm: Dành cho người đi bộ / Dành cho xe cộ.', [['Vỉa hè', 'Dành cho người đi bộ'], ['Vạch kẻ qua đường', 'Dành cho người đi bộ'], ['Cầu vượt bộ hành', 'Dành cho người đi bộ'], ['Lòng đường', 'Dành cho xe cộ']], 'Đi đúng phần đường của mình giúp tránh va chạm với xe cộ.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Nên làm / Không nên làm khi đi xe đạp.', [['Kiểm tra phanh trước khi đi', 'Nên làm'], ['Đi sát lề đường bên phải', 'Nên làm'], ['Đèo thêm 2 bạn', 'Không nên làm'], ['Buông tay khi đang đi', 'Không nên làm']], 'Kiểm tra xe và đi đúng cách giúp chuyến đi của em an toàn hơn.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được ưu tiên / Phải nhường đường.', [['Xe cứu thương đang hú còi', 'Được ưu tiên'], ['Người đi bộ trên vạch kẻ', 'Được ưu tiên'], ['Xe mình từ đường nhánh ra đường chính', 'Phải nhường đường'], ['Gặp đèn đỏ', 'Phải nhường đường']], 'Biết khi nào được ưu tiên, khi nào phải nhường giúp giao thông trật tự, an toàn.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Đúng / Sai khi ngồi sau xe máy.', [['Đội mũ bảo hiểm cài quai', 'Đúng'], ['Ngồi ngay ngắn, bám chắc', 'Đúng'], ['Đứng lên trên yên xe', 'Sai'], ['Thò chân ra ngoài đùa nghịch', 'Sai']], 'Ngồi sau xe máy cũng phải tuân thủ quy tắc an toàn để tránh tai nạn.', $d);

        $this->fill($s, 'Khi đi xe đạp, phải đi sát ___ đường bên phải.', [[0, 'lề']], 'Đi sát lề bên phải là quy định giúp xe đạp tránh va chạm với xe lớn hơn.', $d);
        $this->fill($s, 'Gặp đèn vàng, người đi xe phải đi ___ lại và chuẩn bị dừng.', [[0, 'chậm']], 'Đèn vàng báo hiệu sắp chuyển sang đỏ nên phải đi chậm và chuẩn bị dừng lại.', $d);
        $this->fill($s, 'Không được ___ tay khi đang điều khiển xe đạp.', [[0, 'buông']], 'Buông tay khi đi xe khiến em không kịp xử lý khi gặp chướng ngại vật.', $d);
        $this->fill($s, 'Người từ ___ tuổi trở lên mới được điều khiển xe máy.', [[0, '18']], 'Pháp luật quy định đủ 18 tuổi mới được lái xe máy để đảm bảo an toàn.', $d);
        $this->fill($s, 'Khi qua đường, phải quan sát cả hai ___ trước khi bước đi.', [[0, 'bên']], 'Quan sát cả hai bên giúp phát hiện xe đang tới để qua đường an toàn.', $d);
    }

    private function seedPhanLoaiRac(): void
    {
        $s = 'tnhn-phan-loai-rac';
        $d = 'de';

        $this->quiz($s, 'Vỏ chai nhựa, lon nước ngọt thuộc loại rác nào?', ['Rác hữu cơ', 'Rác tái chế', 'Rác nguy hại', 'Rác y tế'], 1, 'Chai nhựa, lon nhôm có thể thu gom tái chế thành sản phẩm mới nên thuộc rác tái chế.', $d);
        $this->quiz($s, 'Rác hữu cơ có thể xử lý thành gì có ích?', ['Đốt bỏ hết', 'Ủ làm phân bón cho cây', 'Chôn bừa bãi', 'Thải ra sông'], 1, 'Rác hữu cơ ủ đúng cách sẽ thành phân bón tốt cho cây trồng, vừa sạch vừa tiết kiệm.', $d);
        $this->quiz($s, 'Vì sao không được vứt pin cũ vào thùng rác thường?', ['Vì pin nặng', 'Vì hóa chất trong pin gây ô nhiễm đất, nước', 'Vì pin còn dùng được', 'Vì thùng rác đã đầy'], 1, 'Pin chứa hóa chất độc hại, vứt bừa bãi sẽ ngấm vào đất và nguồn nước gây ô nhiễm.', $d);
        $this->quiz($s, 'Hành động nào giúp giảm rác thải nhựa?', ['Dùng ống hút nhựa mỗi ngày', 'Mang bình nước cá nhân', 'Mua nước chai nhựa dùng một lần', 'Xin nhiều túi nilon'], 1, 'Mang bình nước cá nhân giúp giảm đáng kể lượng chai nhựa thải ra mỗi ngày.', $d);
        $this->quiz($s, 'Giấy vụn, thùng carton cũ nên làm gì?', ['Đốt đi', 'Thu gom bán cho nơi tái chế', 'Vứt xuống sông', 'Vứt lẫn với rác hữu cơ'], 1, 'Giấy và carton thu gom tái chế được thành giấy mới, giúp tiết kiệm gỗ và bảo vệ rừng.', $d);

        $this->matching($s, 'Nối mỗi loại rác với ví dụ đúng.', [['Rác hữu cơ', 'Vỏ rau, thức ăn thừa'], ['Rác tái chế', 'Chai nhựa, lon nhôm'], ['Rác nguy hại', 'Pin cũ, bóng đèn vỡ'], ['Rác còn lại', 'Túi nilon bẩn, gốm sứ vỡ']], 'Phân loại đúng từng loại rác là bước đầu để xử lý và tái chế hiệu quả.', $d);
        $this->matching($s, 'Nối mỗi hành động với lợi ích.', [['Phân loại rác tại nhà', 'Dễ tái chế'], ['Mang túi vải đi chợ', 'Giảm túi nilon'], ['Ủ rác hữu cơ', 'Có phân bón cho cây'], ['Tái sử dụng chai lọ', 'Tiết kiệm tài nguyên']], 'Mỗi hành động nhỏ đều góp phần giảm rác thải và bảo vệ môi trường.', $d);
        $this->matching($s, 'Nối mỗi vật dụng với cách dùng thân thiện môi trường.', [['Bình nước cá nhân', 'Dùng nhiều lần'], ['Túi vải', 'Đi chợ thay túi nilon'], ['Hộp cơm riêng', 'Đựng đồ ăn thay hộp xốp'], ['Khăn tay vải', 'Thay khăn giấy dùng một lần']], 'Ưu tiên đồ dùng nhiều lần thay cho đồ dùng một lần giúp giảm rác thải đáng kể.', $d);
        $this->matching($s, 'Nối mỗi loại rác với cách xử lý đúng.', [['Pin cũ', 'Gom riêng, gửi điểm thu hồi'], ['Thức ăn thừa', 'Ủ phân hoặc cho vật nuôi'], ['Chai nhựa sạch', 'Bán ve chai tái chế'], ['Bóng đèn vỡ', 'Gói cẩn thận, bỏ thùng rác nguy hại']], 'Xử lý đúng cách từng loại rác giúp tránh ô nhiễm và tận dụng được tài nguyên.', $d);
        $this->matching($s, 'Nối mỗi thói quen với đánh giá.', [['Vứt rác đúng thùng', 'Tốt'], ['Xả rác bừa bãi', 'Xấu'], ['Nhặt rác nơi công cộng', 'Tốt'], ['Đốt rác nilon', 'Xấu']], 'Thói quen tốt với rác thải thể hiện ý thức bảo vệ môi trường của mỗi người.', $d);

        $this->sortQ($s, 'Xếp các loại rác vào nhóm: Tái chế được / Khó tái chế.', [['Chai nhựa', 'Tái chế được'], ['Lon nhôm', 'Tái chế được'], ['Giấy báo cũ', 'Tái chế được'], ['Túi nilon bẩn', 'Khó tái chế'], ['Gốm sứ vỡ', 'Khó tái chế'], ['Xốp bẩn', 'Khó tái chế']], 'Rác tái chế được nên thu gom riêng, rác khó tái chế cần hạn chế sử dụng.', $d);
        $this->sortQ($s, 'Xếp các hành động vào nhóm: Bảo vệ môi trường / Gây hại môi trường.', [['Trồng thêm cây xanh', 'Bảo vệ môi trường'], ['Tiết kiệm nước', 'Bảo vệ môi trường'], ['Xả rác xuống kênh', 'Gây hại môi trường'], ['Đốt rơm rạ ngoài đồng', 'Gây hại môi trường']], 'Hành động bảo vệ môi trường giữ cho đất, nước, không khí trong lành.', $d);
        $this->sortQ($s, 'Xếp các vật dụng vào nhóm: Nên dùng / Nên hạn chế.', [['Bình nước cá nhân', 'Nên dùng'], ['Túi vải', 'Nên dùng'], ['Ống hút nhựa dùng một lần', 'Nên hạn chế'], ['Hộp xốp đựng đồ ăn', 'Nên hạn chế']], 'Nên dùng đồ bền, dùng nhiều lần và hạn chế đồ nhựa dùng một lần.', $d);
        $this->sortQ($s, 'Xếp các loại rác vào nhóm: Rác hữu cơ / Rác vô cơ.', [['Vỏ trái cây', 'Rác hữu cơ'], ['Cơm thừa', 'Rác hữu cơ'], ['Vỏ chai thủy tinh', 'Rác vô cơ'], ['Túi nilon', 'Rác vô cơ']], 'Rác hữu cơ phân hủy được từ thực vật, động vật; rác vô cơ thì không.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Nên làm ở trường / Không nên làm.', [['Bỏ rác đúng thùng phân loại', 'Nên làm ở trường'], ['Nhắc bạn cùng giữ vệ sinh', 'Nên làm ở trường'], ['Vứt vỏ kẹo xuống sân', 'Không nên làm'], ['Vẽ bậy lên tường', 'Không nên làm']], 'Giữ gìn vệ sinh trường lớp là trách nhiệm của mỗi học sinh.', $d);

        $this->fill($s, 'Chai nhựa, lon nhôm thuộc nhóm rác ___ chế.', [[0, 'tái']], 'Rác tái chế là những loại có thể thu gom để sản xuất thành sản phẩm mới.', $d);
        $this->fill($s, 'Rác hữu cơ có thể ủ thành ___ bón cho cây trồng.', [[0, 'phân']], 'Ủ rác hữu cơ thành phân bón vừa giảm rác vừa tốt cho cây.', $d);
        $this->fill($s, 'Tuyệt đối không vứt ___ cũ bừa bãi vì chứa hóa chất độc hại.', [[0, 'pin']], 'Pin cũ phải được thu gom riêng để xử lý an toàn, không gây ô nhiễm.', $d);
        $this->fill($s, 'Mỗi người nên mang theo ___ nước cá nhân để hạn chế chai nhựa.', [[0, 'bình']], 'Bình nước cá nhân dùng nhiều lần giúp giảm rác thải nhựa mỗi ngày.', $d);
        $this->fill($s, 'Phân loại rác tại ___ giúp việc tái chế dễ dàng hơn.', [[0, 'nguồn']], 'Phân loại ngay tại nguồn (tại nhà) giúp rác tái chế không bị lẫn bẩn.', $d);
    }

    private function seedCacNgheQuenThuoc(): void
    {
        $s = 'tnhn-cac-nghe-quen-thuoc';
        $d = 'trung_binh';

        $this->quiz($s, 'Nghề nào sau đây thuộc nhóm nghề kỹ thuật - công nghệ?', ['Giáo viên', 'Kỹ sư phần mềm', 'Bác sĩ', 'Luật sư'], 1, 'Kỹ sư phần mềm làm việc với công nghệ, máy tính nên thuộc nhóm nghề kỹ thuật - công nghệ.', $d);
        $this->quiz($s, 'Để trở thành phi công, ngoài sức khỏe tốt còn cần yếu tố nào quan trọng?', ['Khả năng ngoại ngữ và xử lý tình huống nhanh', 'Giọng hát hay', 'Khéo tay vẽ tranh', 'Thích nấu ăn'], 0, 'Phi công cần ngoại ngữ để giao tiếp quốc tế và phản xạ nhanh để xử lý tình huống trên không.', $d);
        $this->quiz($s, 'Nghề nông dân hiện đại cần thêm kỹ năng gì so với trước đây?', ['Chỉ cần sức khỏe', 'Ứng dụng khoa học kỹ thuật và máy móc', 'Không cần học gì thêm', 'Chỉ cần kinh nghiệm truyền miệng'], 1, 'Nông nghiệp hiện đại dùng máy móc, kỹ thuật mới nên nông dân cũng cần học hỏi không ngừng.', $d);
        $this->quiz($s, 'Phẩm chất nào KHÔNG phù hợp với nghề kế toán?', ['Cẩn thận, tỉ mỉ', 'Trung thực', 'Cẩu thả, qua loa', 'Kiên nhẫn với con số'], 2, 'Kế toán làm việc với tiền bạc, sổ sách nên cẩu thả, qua loa là phẩm chất không thể chấp nhận.', $d);
        $this->quiz($s, 'Nghề đầu bếp chuyên nghiệp cần nhất điều gì?', ['Ăn thật nhiều', 'Kỹ năng chế biến, sáng tạo món ăn và đảm bảo vệ sinh', 'Nói thật nhiều', 'Chạy thật nhanh'], 1, 'Đầu bếp giỏi cần tay nghề chế biến, óc sáng tạo và luôn đảm bảo vệ sinh an toàn thực phẩm.', $d);

        $this->matching($s, 'Nối mỗi nghề với công việc chính.', [['Bác sĩ', 'Khám và chữa bệnh'], ['Kỹ sư xây dựng', 'Thiết kế, thi công công trình'], ['Nhà báo', 'Thu thập và đưa tin'], ['Thợ điện', 'Lắp đặt, sửa chữa hệ thống điện']], 'Mỗi nghề có công việc đặc trưng riêng, hiểu rõ giúp em định hướng tương lai.', $d);
        $this->matching($s, 'Nối mỗi nghề với phẩm chất cần có.', [['Giáo viên', 'Kiên nhẫn, yêu thương học trò'], ['Bác sĩ', 'Tận tâm, cẩn thận'], ['Công an', 'Dũng cảm, kỷ luật'], ['Nghệ sĩ', 'Sáng tạo, giàu cảm xúc']], 'Mỗi nghề đòi hỏi những phẩm chất phù hợp, rèn luyện từ bây giờ sẽ giúp em sau này.', $d);
        $this->matching($s, 'Nối mỗi nghề với nơi làm việc thường thấy.', [['Nông dân', 'Đồng ruộng, trang trại'], ['Ngư dân', 'Biển, sông'], ['Thợ may', 'Xưởng may, tiệm may'], ['Tiếp viên hàng không', 'Trên máy bay']], 'Môi trường làm việc khác nhau tạo nên đặc thù của từng nghề nghiệp.', $d);
        $this->matching($s, 'Nối mỗi nghề với dụng cụ đặc trưng.', [['Bác sĩ', 'Ống nghe'], ['Thợ mộc', 'Cưa, bào'], ['Họa sĩ', 'Cọ vẽ, màu'], ['Đầu bếp', 'Dao, chảo']], 'Dụng cụ đặc trưng phản ánh tính chất công việc của mỗi nghề.', $d);
        $this->matching($s, 'Nối mỗi nhóm nghề với ví dụ.', [['Nghề y tế', 'Bác sĩ, y tá'], ['Nghề giáo dục', 'Giáo viên, giảng viên'], ['Nghề kỹ thuật', 'Kỹ sư, thợ điện'], ['Nghề dịch vụ', 'Nhân viên bán hàng, đầu bếp']], 'Các nghề được xếp thành nhóm theo lĩnh vực hoạt động giống nhau.', $d);

        $this->sortQ($s, 'Xếp các nghề vào nhóm: Làm việc với con người / Làm việc với máy móc.', [['Giáo viên', 'Làm việc với con người'], ['Bác sĩ', 'Làm việc với con người'], ['Thợ sửa xe', 'Làm việc với máy móc'], ['Kỹ sư cơ khí', 'Làm việc với máy móc']], 'Có nghề chủ yếu tiếp xúc con người, có nghề chủ yếu làm việc với máy móc.', $d);
        $this->sortQ($s, 'Xếp các phẩm chất vào nhóm: Cần cho nghề nghiệp / Chưa phải phẩm chất nghề nghiệp.', [['Trung thực', 'Cần cho nghề nghiệp'], ['Chăm chỉ', 'Cần cho nghề nghiệp'], ['Lười biếng', 'Chưa phải phẩm chất nghề nghiệp'], ['Gian dối', 'Chưa phải phẩm chất nghề nghiệp']], 'Phẩm chất tốt là nền tảng để thành công trong bất kỳ nghề nào.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Giúp tìm hiểu nghề / Chưa giúp tìm hiểu nghề.', [['Tham quan nhà máy, bệnh viện', 'Giúp tìm hiểu nghề'], ['Phỏng vấn người đang làm nghề', 'Giúp tìm hiểu nghề'], ['Chỉ đoán mò', 'Chưa giúp tìm hiểu nghề'], ['Nghe lời đồn không kiểm chứng', 'Chưa giúp tìm hiểu nghề']], 'Tìm hiểu nghề cần thông tin thực tế, không nên đoán mò hay nghe đồn.', $d);
        $this->sortQ($s, 'Xếp các nghề vào nhóm: Trong nhà / Ngoài trời.', [['Nhân viên văn phòng', 'Trong nhà'], ['Thợ may', 'Trong nhà'], ['Nông dân', 'Ngoài trời'], ['Công nhân xây dựng', 'Ngoài trời']], 'Môi trường làm việc trong nhà hay ngoài trời ảnh hưởng đến tính chất công việc.', $d);
        $this->sortQ($s, 'Xếp các câu nói vào nhóm: Tôn trọng mọi nghề / Chưa tôn trọng.', [['Mọi nghề chân chính đều đáng quý', 'Tôn trọng mọi nghề'], ['Nghề nào cũng góp ích cho xã hội', 'Tôn trọng mọi nghề'], ['Chỉ nghề lương cao mới đáng làm', 'Chưa tôn trọng'], ['Chê bai nghề lao động chân tay', 'Chưa tôn trọng']], 'Mọi nghề nghiệp chân chính đều đáng được tôn trọng như nhau.', $d);

        $this->fill($s, 'Người thiết kế và giám sát công trình xây dựng là kỹ ___.', [[0, 'sư']], 'Kỹ sư xây dựng chịu trách nhiệm thiết kế và đảm bảo công trình an toàn, đúng kỹ thuật.', $d);
        $this->fill($s, 'Nghề giáo viên cần nhất lòng yêu ___ và sự kiên nhẫn.', [[0, 'trẻ']], 'Yêu trẻ và kiên nhẫn là phẩm chất không thể thiếu của người làm nghề giáo.', $d);
        $this->fill($s, 'Thợ ___ là người lắp đặt và sửa chữa hệ thống điện.', [[0, 'điện']], 'Thợ điện làm việc với hệ thống điện trong nhà, xưởng và công trình.', $d);
        $this->fill($s, 'Người đưa tin tức đến công chúng làm nghề nhà ___.', [[0, 'báo']], 'Nhà báo thu thập thông tin và đưa tin tức đến mọi người qua báo chí, truyền hình.', $d);
        $this->fill($s, 'Mọi nghề nghiệp ___ chính đều đáng được tôn trọng.', [[0, 'chân']], 'Dù là nghề gì, chỉ cần lao động chân chính thì đều đáng quý và đáng tôn trọng.', $d);
    }

    private function seedUocMoNgheNghiepCuaEm(): void
    {
        $s = 'tnhn-uoc-mo-nghe-nghiep-cua-em';
        $d = 'trung_binh';

        $this->quiz($s, 'Ngoài sở thích, yếu tố nào cũng quan trọng khi chọn nghề?', ['Nghề có lương cao nhất', 'Năng lực bản thân và nhu cầu xã hội', 'Bạn bè chọn gì mình chọn đó', 'Nghề nào nhàn nhất'], 1, 'Chọn nghề cần cân nhắc cả năng lực của mình và nhu cầu thực tế của xã hội.', $d);
        $this->quiz($s, 'Muốn trở thành kỹ sư trong tương lai, từ bây giờ em nên chú trọng môn nào?', ['Chỉ học môn Văn', 'Toán, Lý và Tin học', 'Chỉ học môn Thể dục', 'Không cần học môn nào'], 1, 'Nghề kỹ sư cần nền tảng Toán, Lý và Tin học vững chắc nên phải chú trọng từ bây giờ.', $d);
        $this->quiz($s, 'Khi gặp khó khăn trên con đường theo đuổi ước mơ, em nên làm gì?', ['Bỏ cuộc ngay', 'Kiên trì, tìm cách khắc phục và nhờ người giúp', 'Đổ lỗi cho hoàn cảnh', 'Chờ may mắn đến'], 1, 'Kiên trì tìm cách khắc phục và dám nhờ giúp đỡ là thái độ đúng khi gặp khó khăn.', $d);
        $this->quiz($s, 'Việc làm nào giúp em hiểu rõ hơn về nghề mình mơ ước?', ['Tìm hiểu, trò chuyện với người đang làm nghề đó', 'Chỉ tưởng tượng trong đầu', 'Nghe đồn đoán', 'Không làm gì cả'], 0, 'Trò chuyện với người đang làm nghề cho em thông tin thực tế và chính xác nhất.', $d);
        $this->quiz($s, 'Ước mơ nghề nghiệp tốt cần gắn với điều gì?', ['Chỉ lợi ích cá nhân', 'Đam mê cá nhân và đóng góp cho cộng đồng', 'Sự nổi tiếng bằng mọi giá', 'Trốn tránh mọi khó khăn'], 1, 'Ước mơ đẹp là khi đam mê của mình cũng mang lại giá trị tốt cho cộng đồng.', $d);

        $this->matching($s, 'Nối mỗi sở thích với nghề phù hợp.', [['Thích chăm sóc người khác', 'Bác sĩ, y tá'], ['Thích vẽ, sáng tạo', 'Họa sĩ, thiết kế'], ['Thích tìm hiểu máy móc', 'Kỹ sư'], ['Thích nói trước đám đông', 'MC, giáo viên']], 'Sở thích là gợi ý tốt để em bắt đầu nghĩ về nghề nghiệp phù hợp.', $d);
        $this->matching($s, 'Nối mỗi ước mơ với việc cần làm ngay từ bây giờ.', [['Muốn làm bác sĩ', 'Học tốt Sinh, Hóa'], ['Muốn làm kỹ sư', 'Học tốt Toán, Lý'], ['Muốn làm nhà văn', 'Đọc sách, luyện viết'], ['Muốn làm đầu bếp', 'Tập nấu ăn, học về dinh dưỡng']], 'Ước mơ chỉ thành hiện thực khi em hành động cụ thể ngay từ hôm nay.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn với mục tiêu.', [['THCS', 'Học đều các môn, tìm hiểu nghề'], ['THPT', 'Chọn khối thi phù hợp'], ['Đại học/Cao đẳng', 'Học chuyên sâu về nghề'], ['Đi làm', 'Tích lũy kinh nghiệm']], 'Mỗi giai đoạn có mục tiêu riêng, đi đúng từng bước sẽ tới được ước mơ.', $d);
        $this->matching($s, 'Nối mỗi khó khăn với cách vượt qua.', [['Học yếu môn cần thiết', 'Chăm chỉ bồi dưỡng thêm'], ['Thiếu tự tin', 'Rèn luyện từng bước nhỏ'], ['Gia đình chưa ủng hộ', 'Trò chuyện, chứng minh bằng hành động'], ['Chưa biết chọn nghề gì', 'Tìm hiểu nhiều nghề, hỏi thầy cô']], 'Khó khăn nào cũng có cách vượt qua nếu em bình tĩnh và chủ động tìm giải pháp.', $d);
        $this->matching($s, 'Nối mỗi phẩm chất với nghề cần nó nhất.', [['Tỉ mỉ, cẩn thận', 'Bác sĩ phẫu thuật'], ['Sáng tạo', 'Nhà thiết kế'], ['Kiên nhẫn', 'Giáo viên mầm non'], ['Dũng cảm', 'Lính cứu hỏa']], 'Hiểu phẩm chất nghề cần giúp em biết mình phải rèn luyện điều gì.', $d);

        // (bỏ prompt trùng ý với câu có sẵn 'Xếp các việc làm vào nhóm: Giúp đạt ước mơ / Cản trở ước mơ.')
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: Nên dựa vào khi chọn nghề / Không nên.', [['Sở thích và năng lực', 'Nên dựa vào khi chọn nghề'], ['Nhu cầu của xã hội', 'Nên dựa vào khi chọn nghề'], ['Chạy theo phong trào', 'Không nên'], ['Bị ép buộc', 'Không nên']], 'Chọn nghề nên dựa vào sở thích, năng lực và thực tế, không nên chạy theo phong trào.', $d);
        $this->sortQ($s, 'Xếp các câu nói vào nhóm: Tích cực / Tiêu cực.', [['Mình sẽ cố gắng từng ngày', 'Tích cực'], ['Thất bại là bài học quý', 'Tích cực'], ['Mình chẳng làm được gì đâu', 'Tiêu cực'], ['Ước mơ là viển vông', 'Tiêu cực']], 'Suy nghĩ tích cực tiếp thêm sức mạnh, suy nghĩ tiêu cực làm em chùn bước.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Nên làm ngay / Để sau cũng được.', [['Lập kế hoạch học tập', 'Nên làm ngay'], ['Tìm hiểu về nghề mơ ước', 'Nên làm ngay'], ['Mua sắm đồ hiệu', 'Để sau cũng được'], ['Chơi game giải trí quá nhiều', 'Để sau cũng được']], 'Ưu tiên việc quan trọng cho tương lai trước những thú vui nhất thời.', $d);
        $this->sortQ($s, 'Xếp các thái độ vào nhóm: Đúng / Sai khi theo đuổi ước mơ.', [['Kiên trì không bỏ cuộc', 'Đúng'], ['Dám thử thách bản thân', 'Đúng'], ['Sợ thất bại nên không dám làm', 'Sai'], ['Đổ lỗi cho người khác', 'Sai']], 'Thái độ đúng đắn là hành trang quan trọng nhất trên con đường theo đuổi ước mơ.', $d);

        $this->fill($s, 'Ước mơ cần đi đôi với ___ động kiên trì mỗi ngày.', [[0, 'hành']], 'Hành động kiên trì mỗi ngày mới biến ước mơ thành hiện thực.', $d);
        $this->fill($s, 'Chọn nghề phải phù hợp với sở thích, năng lực và ___ cầu xã hội.', [[0, 'nhu']], 'Nhu cầu xã hội cho biết nghề em chọn có cơ hội việc làm tốt hay không.', $d);
        $this->fill($s, 'Khi gặp thất bại, đừng nản lòng mà hãy coi đó là ___ học quý giá.', [[0, 'bài']], 'Mỗi thất bại đều dạy ta một bài học để lần sau làm tốt hơn.', $d);
        $this->fill($s, 'Muốn thành công, hãy bắt đầu từ những bước ___ nhỏ ngay hôm nay.', [[0, 'đi']], 'Bước đi nhỏ hôm nay là khởi đầu cho thành công lớn ngày mai.', $d);
        $this->fill($s, 'Người thành công là người không bao giờ ___ cuộc trước khó khăn.', [[0, 'bỏ']], 'Không bỏ cuộc trước khó khăn là điểm chung của những người thành công.', $d);
    }

    private function seedKyNangSongLop61(): void
    {
        $s = 'tnhn-ky-nang-song-lop-6-1';
        $d = 'de';

        $this->quiz($s, 'Khi làm bài tập về nhà, nên bắt đầu từ môn nào?', ['Môn khó nhất trước', 'Môn dễ nhất trước', 'Môn nào cũng được, làm bừa', 'Không làm, để mai chép bạn'], 0, 'Làm môn khó trước khi đầu óc còn tỉnh táo giúp hoàn thành bài tập hiệu quả hơn.', $d);
        $this->quiz($s, 'Thói quen nào giúp em không quên bài tập về nhà?', ['Ghi vào sổ liên lạc hoặc vở ghi nhớ', 'Nhớ trong đầu', 'Hỏi bạn vào sáng hôm sau', 'Đoán mò'], 0, 'Ghi bài tập ra sổ giúp em không bị sót và chủ động sắp xếp thời gian làm.', $d);
        $this->quiz($s, 'Nên sắp xếp góc học tập như thế nào?', ['Bừa bộn cho thoải mái', 'Gọn gàng, đủ ánh sáng, ít đồ gây xao nhãng', 'Để đồ chơi khắp bàn', 'Học trên giường cho êm'], 1, 'Góc học tập gọn gàng, đủ sáng giúp em tập trung và học hiệu quả hơn.', $d);
        $this->quiz($s, 'Khi bị bạn rủ đi chơi lúc đang học bài, em nên làm gì?', ['Bỏ học đi chơi ngay', 'Hẹn bạn sau khi học xong', 'Vừa học vừa chơi', 'Nghỉ học luôn'], 1, 'Hẹn bạn sau khi học xong vừa giữ được tình bạn vừa không làm dở việc học.', $d);
        $this->quiz($s, 'Mỗi sáng thức dậy, việc đầu tiên giúp ngày mới hiệu quả là gì?', ['Ôm điện thoại một tiếng', 'Xem lại việc cần làm trong ngày', 'Ngủ tiếp', 'Chơi game'], 1, 'Xem lại việc cần làm giúp em có định hướng rõ ràng cho cả ngày.', $d);

        $this->matching($s, 'Nối mỗi nhóm việc với thứ tự ưu tiên.', [['Ôn bài kiểm tra ngày mai', 'Ưu tiên 1'], ['Bài tập về nhà', 'Ưu tiên 2'], ['Dọn phòng', 'Ưu tiên 3'], ['Xem phim giải trí', 'Ưu tiên 4']], 'Xếp thứ tự ưu tiên giúp em làm việc quan trọng trước, việc ít quan trọng sau.', $d);
        $this->matching($s, 'Nối mỗi thói quen tốt với kết quả.', [['Dậy đúng giờ', 'Không vội vàng'], ['Chuẩn bị đồ từ tối', 'Không quên đồ'], ['Ghi nhớ bài tập', 'Không sót bài'], ['Ôn bài đều đặn', 'Nhớ lâu']], 'Thói quen tốt mỗi ngày tạo nên kết quả học tập tốt lâu dài.', $d);
        $this->matching($s, 'Nối mỗi đồ dùng với cách giúp quản lý thời gian.', [['Đồng hồ báo thức', 'Nhắc giờ'], ['Sổ tay', 'Ghi việc cần làm'], ['Lịch để bàn', 'Theo dõi ngày tháng'], ['Hộp bút gọn gàng', 'Tiết kiệm thời gian tìm đồ']], 'Dùng đúng đồ dùng hỗ trợ giúp em quản lý thời gian dễ dàng hơn.', $d);
        $this->matching($s, 'Nối mỗi khung giờ nghỉ với hoạt động phù hợp.', [['Nghỉ 5 phút', 'Uống nước'], ['Nghỉ 15 phút', 'Đi dạo nhẹ'], ['Buổi trưa', 'Ăn và ngủ ngắn'], ['Cuối tuần', 'Vui chơi cùng gia đình']], 'Mỗi khoảng nghỉ có cách nghỉ phù hợp để cơ thể phục hồi tốt nhất.', $d);
        $this->matching($s, 'Nối mỗi sai lầm quản lý thời gian với hậu quả.', [['Thức khuya', 'Sáng mệt mỏi'], ['Chơi quên giờ', 'Bài tập dồn đống'], ['Không ghi nhớ việc', 'Quên bài tập'], ['Ôm đồm nhiều việc', 'Không việc nào xong']], 'Nhận ra sai lầm và hậu quả giúp em tránh lặp lại chúng.', $d);

        $this->sortQ($s, 'Xếp các việc vào nhóm: Nên làm trước / Có thể làm sau.', [['Bài kiểm tra ngày mai', 'Nên làm trước'], ['Ôn bài cũ', 'Nên làm trước'], ['Bài tập nộp tuần sau', 'Có thể làm sau'], ['Dọn góc học tập', 'Có thể làm sau']], 'Việc gấp và quan trọng làm trước, việc chưa gấp có thể xếp sau.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về quản lý thời gian.', [['Nên có thời gian biểu mỗi ngày', 'Đúng'], ['Nghỉ ngơi cũng là một phần của kế hoạch', 'Đúng'], ['Học càng khuya càng tốt', 'Sai'], ['Chơi game cả ngày cuối tuần là hợp lý', 'Sai']], 'Quản lý thời gian đúng nghĩa là cân đối học tập, nghỉ ngơi và vui chơi.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Việc học / Việc nhà.', [['Làm bài tập Toán', 'Việc học'], ['Ôn từ vựng tiếng Anh', 'Việc học'], ['Quét nhà', 'Việc nhà'], ['Rửa bát', 'Việc nhà']], 'Cả việc học và việc nhà đều cần được sắp xếp hợp lý trong ngày.', $d);
        $this->sortQ($s, 'Xếp các hoạt động cuối tuần vào nhóm: Bổ ích / Lãng phí thời gian.', [['Đọc sách', 'Bổ ích'], ['Chơi thể thao', 'Bổ ích'], ['Lướt điện thoại 5 tiếng', 'Lãng phí thời gian'], ['Ngủ nướng cả ngày', 'Lãng phí thời gian']], 'Cuối tuần nên dùng vào việc bổ ích thay vì lãng phí thời gian vô nghĩa.', $d);
        $this->sortQ($s, 'Xếp các thói quen vào nhóm: Tiết kiệm thời gian / Lãng phí thời gian.', [['Chuẩn bị đồ từ tối hôm trước', 'Tiết kiệm thời gian'], ['Làm bài tập ngay sau giờ học', 'Tiết kiệm thời gian'], ['Tìm đồ đạc trong đống bừa bộn', 'Lãng phí thời gian'], ['Vừa học vừa xem ti vi', 'Lãng phí thời gian']], 'Thói quen tốt giúp tiết kiệm thời gian, thói quen xấu làm thời gian trôi đi vô ích.', $d);

        $this->fill($s, 'Mỗi tối, hãy ___ kết những việc đã làm trong ngày.', [[0, 'tổng']], 'Tổng kết mỗi tối giúp em thấy mình đã làm được gì và cần cải thiện gì.', $d);
        $this->fill($s, 'Việc khó nên làm khi đầu óc còn ___ táo nhất.', [[0, 'tỉnh']], 'Lúc tỉnh táo nhất thì khả năng tư duy tốt nhất, nên dành cho việc khó.', $d);
        $this->fill($s, 'Đừng để ___ tập dồn đến sát ngày kiểm tra mới ôn.', [[0, 'bài']], 'Ôn bài đều đặn mỗi ngày tốt hơn nhiều so với dồn đến sát ngày thi.', $d);
        $this->fill($s, 'Góc học tập gọn gàng giúp em ___ trung tốt hơn.', [[0, 'tập']], 'Không gian gọn gàng, ít xao nhãng giúp em tập trung học bài hiệu quả.', $d);
        $this->fill($s, 'Hãy nói ___ với điện thoại trong giờ học bài.', [[0, 'không']], 'Tạm rời xa điện thoại trong giờ học giúp em không bị xao nhãng.', $d);
    }

    private function seedKyNangSongLop62(): void
    {
        $s = 'tnhn-ky-nang-song-lop-6-2';
        $d = 'de';

        $this->quiz($s, 'Khi chào hỏi người lớn tuổi, em nên làm gì?', ['Chào to, rõ ràng và lễ phép', 'Lờ đi không chào', 'Chào qua loa cho xong', 'Trốn đi chỗ khác'], 0, 'Chào to, rõ ràng và lễ phép thể hiện sự tôn trọng với người lớn tuổi.', $d);
        $this->quiz($s, 'Khi mượn đồ của bạn, em cần làm gì?', ['Tự lấy không cần hỏi', 'Hỏi mượn lịch sự và trả đúng hẹn', 'Mượn rồi giữ luôn', 'Lấy lúc bạn không để ý'], 1, 'Hỏi mượn lịch sự và trả đúng hẹn thể hiện sự tôn trọng đồ đạc của bạn.', $d);
        $this->quiz($s, 'Khi vô tình làm đổ nước lên vở bạn, em nên làm gì?', ['Bỏ chạy thật nhanh', 'Xin lỗi và cùng bạn khắc phục', 'Đổ lỗi cho bạn', 'Cười nhạo bạn'], 1, 'Dám nhận lỗi và cùng khắc phục là cách ứng xử đúng khi vô tình gây ra lỗi.', $d);
        $this->quiz($s, 'Trong giờ học nhóm, bạn nói nhỏ với em chuyện riêng, em nên làm gì?', ['Nói chuyện cùng bạn', 'Nhẹ nhàng nhắc bạn tập trung', 'Mách thầy ngay', 'Bỏ nhóm đi chỗ khác'], 1, 'Nhẹ nhàng nhắc bạn tập trung vừa giữ hòa khí vừa không làm mất thời gian của nhóm.', $d);
        $this->quiz($s, 'Câu nào thể hiện sự cảm ơn chân thành?', ['Cảm ơn bạn đã giúp mình nhé', 'Ừ, biết rồi', 'Có gì đâu mà khoe', 'Thôi khỏi'], 0, 'Lời cảm ơn chân thành, cụ thể khiến người giúp mình cảm thấy được trân trọng.', $d);

        $this->matching($s, 'Nối mỗi cử chỉ với thông điệp của nó.', [['Mỉm cười khi chào', 'Thân thiện'], ['Nhìn vào mắt người nói', 'Chú ý lắng nghe'], ['Khoanh tay, quay đi', 'Thiếu thiện chí'], ['Gật đầu', 'Đồng ý, hiểu ý']], 'Cử chỉ, nét mặt cũng là một phần quan trọng của giao tiếp.', $d);
        $this->matching($s, 'Nối mỗi hành động nhóm với kết quả.', [['Chia việc công bằng', 'Ai cũng có trách nhiệm'], ['Giúp bạn yếu hơn', 'Cả nhóm cùng tiến'], ['Khen ngợi nhau', 'Vui vẻ, gắn bó'], ['Tranh cãi gay gắt', 'Mất đoàn kết']], 'Hành động tích cực mang lại kết quả tốt cho cả nhóm.', $d);
        $this->matching($s, 'Nối mỗi lời nói với cảm xúc người nghe.', [['Bạn giỏi quá', 'Vui vẻ'], ['Mình giúp bạn nhé', 'Ấm lòng'], ['Đồ dốt', 'Tổn thương'], ['Kệ bạn', 'Buồn bã']], 'Lời nói có thể làm người nghe vui hoặc buồn nên cần lựa lời mà nói.', $d);
        $this->matching($s, 'Nối mỗi thành viên với việc nên làm.', [['Bạn giỏi vẽ', 'Phụ trách trang trí'], ['Bạn nói tốt', 'Thuyết trình'], ['Bạn cẩn thận', 'Kiểm tra bài'], ['Bạn nhanh nhẹn', 'Chuẩn bị đồ dùng']], 'Phân công theo điểm mạnh của từng bạn giúp nhóm phát huy tối đa.', $d);
        $this->matching($s, 'Nối mỗi tình huống với câu nói phù hợp.', [['Muốn mượn bút', 'Cho mình mượn bút được không'], ['Làm phiền bạn', 'Xin lỗi đã làm phiền bạn'], ['Được bạn giúp', 'Cảm ơn bạn nhiều'], ['Muốn góp ý', 'Mình có ý này, bạn nghe thử nhé']], 'Dùng câu nói phù hợp từng tình huống giúp giao tiếp lịch sự, hiệu quả.', $d);

        $this->sortQ($s, 'Xếp các cách ứng xử vào nhóm: Nên / Không nên khi nói chuyện.', [['Nói năng lễ phép', 'Nên'], ['Nhìn người đối diện', 'Nên'], ['Nói tục, chửi bậy', 'Không nên'], ['Nói leo, ngắt lời', 'Không nên']], 'Ứng xử lịch sự khi nói chuyện giúp em được mọi người yêu mến.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về làm việc nhóm.', [['Mọi thành viên đều quan trọng', 'Đúng'], ['Trưởng nhóm cũng cần lắng nghe', 'Đúng'], ['Chỉ cần một bạn giỏi là đủ', 'Sai'], ['Thất bại thì đổ lỗi cho bạn', 'Sai']], 'Làm việc nhóm thành công nhờ sự đóng góp và tôn trọng lẫn nhau.', $d);
        $this->sortQ($s, 'Xếp các hành động vào nhóm: Tôn trọng bạn / Thiếu tôn trọng bạn.', [['Chờ bạn nói xong mới phát biểu', 'Tôn trọng bạn'], ['Cảm ơn khi được giúp', 'Tôn trọng bạn'], ['Cười khi bạn nói sai', 'Thiếu tôn trọng bạn'], ['Bỏ đi khi bạn đang nói', 'Thiếu tôn trọng bạn']], 'Tôn trọng bạn bè là nền tảng của tình bạn và tinh thần đồng đội.', $d);
        $this->sortQ($s, 'Xếp các nhiệm vụ vào nhóm: Của cả nhóm / Của cá nhân.', [['Thống nhất ý tưởng chung', 'Của cả nhóm'], ['Dọn dẹp sau hoạt động', 'Của cả nhóm'], ['Chuẩn bị phần việc được giao', 'Của cá nhân'], ['Tự ôn bài ở nhà', 'Của cá nhân']], 'Có việc cần cả nhóm cùng làm, có việc mỗi cá nhân tự hoàn thành.', $d);
        $this->sortQ($s, 'Xếp các câu nói vào nhóm: Khích lệ / Làm nản lòng.', [['Cố lên, bạn làm được mà', 'Khích lệ'], ['Nhóm mình cùng cố gắng nhé', 'Khích lệ'], ['Bạn kém quá, để mình làm', 'Làm nản lòng'], ['Thôi bỏ đi, không xong đâu', 'Làm nản lòng']], 'Lời khích lệ tiếp thêm sức mạnh, lời chê bai làm bạn nản lòng.', $d);

        $this->fill($s, 'Khi chào người lớn, cần chào to, rõ ràng và ___ phép.', [[0, 'lễ']], 'Chào hỏi lễ phép là nét đẹp văn hóa và thể hiện sự tôn trọng người lớn.', $d);
        $this->fill($s, 'Mượn đồ của bạn phải ___ ý trước và trả đúng hẹn.', [[0, 'xin']], 'Xin phép trước khi mượn và trả đúng hẹn thể hiện sự tôn trọng bạn bè.', $d);
        $this->fill($s, 'Khi làm sai, hãy dũng cảm nhận ___ và sửa chữa.', [[0, 'lỗi']], 'Dám nhận lỗi và sửa chữa là biểu hiện của người dũng cảm, đáng tin.', $d);
        $this->fill($s, 'Nói lời cảm ___ khi được bạn giúp đỡ.', [[0, 'ơn']], 'Lời cảm ơn chân thành làm cho tình bạn thêm gắn bó.', $d);
        $this->fill($s, 'Trong nhóm, ai cũng cần hoàn thành ___ vụ của mình.', [[0, 'nhiệm']], 'Hoàn thành nhiệm vụ của mình là trách nhiệm của mỗi thành viên trong nhóm.', $d);
    }

    private function seedKyNangSongLop71(): void
    {
        $s = 'tnhn-ky-nang-song-lop-7-1';
        $d = 'de';

        $this->quiz($s, 'Biểu hiện nào cho thấy bạn đang vui?', ['Cười tươi, nói nhiều', 'Khóc nức nở', 'Đập bàn', 'Im lặng bỏ đi'], 0, 'Khi vui, nét mặt rạng rỡ, cười tươi và thường nói nhiều hơn bình thường.', $d);
        $this->quiz($s, 'Khi buồn, cách nào giúp em cảm thấy khá hơn?', ['Nhốt mình trong phòng cả ngày', 'Tâm sự với người tin cậy hoặc viết nhật ký', 'Trút giận lên người khác', 'Bỏ ăn bỏ học'], 1, 'Tâm sự với người tin cậy hoặc viết nhật ký giúp giải tỏa nỗi buồn hiệu quả.', $d);
        $this->quiz($s, 'Vì sao không nên quyết định quan trọng lúc đang nóng giận?', ['Vì lúc đó dễ quyết định sai lầm', 'Vì không ai quan tâm', 'Vì phải đi ngủ', 'Vì đã hết giờ'], 0, 'Khi nóng giận, lý trí bị cảm xúc lấn át nên rất dễ đưa ra quyết định sai lầm.', $d);
        $this->quiz($s, 'Khi thấy bạn đang khóc, em nên làm gì?', ['Cười nhạo bạn', 'An ủi, hỏi han nhẹ nhàng', 'Bỏ mặc bạn', 'Quay video đăng mạng'], 1, 'An ủi nhẹ nhàng khi bạn buồn thể hiện sự đồng cảm và tình bạn chân thành.', $d);
        $this->quiz($s, 'Cách nào giúp em bình tĩnh trước giờ kiểm tra căng thẳng?', ['Hít thở sâu vài lần', 'Hoảng loạn', 'Không ôn bài gì', 'Nghĩ đến chuyện thất bại'], 0, 'Hít thở sâu giúp cơ thể thư giãn, đầu óc tỉnh táo hơn trước giờ kiểm tra.', $d);

        $this->matching($s, 'Nối mỗi cảm xúc với dấu hiệu trên khuôn mặt.', [['Vui', 'Cười tươi'], ['Buồn', 'Rơm rớm nước mắt'], ['Giận', 'Nhăn mặt, đỏ mặt'], ['Sợ', 'Tái mặt, run']], 'Nhận diện cảm xúc qua nét mặt giúp em hiểu mình và hiểu người khác hơn.', $d);
        $this->matching($s, 'Nối mỗi cách xử lý cơn giận với kết quả.', [['Hít thở sâu', 'Bình tĩnh lại'], ['Đếm từ 1 đến 10', 'Có thời gian suy nghĩ'], ['Đi dạo một vòng', 'Cơn giận nguôi dần'], ['Hét vào mặt người khác', 'Mọi chuyện tệ hơn']], 'Xử lý cơn giận đúng cách giúp mọi chuyện tốt hơn, sai cách làm tệ hơn.', $d);
        $this->matching($s, 'Nối mỗi cảm xúc với nhóm của nó.', [['Hạnh phúc', 'Tích cực'], ['Biết ơn', 'Tích cực'], ['Ghen tị', 'Tiêu cực'], ['Tuyệt vọng', 'Tiêu cực']], 'Cảm xúc tích cực mang lại năng lượng tốt, cảm xúc tiêu cực cần được xử lý đúng.', $d);
        $this->matching($s, 'Nối mỗi phản ứng khi bị chê với đánh giá.', [['Lắng nghe và sửa', 'Tốt'], ['Im lặng suy nghĩ', 'Tốt'], ['Nổi nóng cãi lại', 'Chưa tốt'], ['Đổ lỗi cho người khác', 'Chưa tốt']], 'Đón nhận lời chê một cách bình tĩnh giúp em tiến bộ hơn mỗi ngày.', $d);
        $this->matching($s, 'Nối mỗi tình huống với cảm xúc thường gặp.', [['Được điểm cao', 'Vui sướng'], ['Bị bạn hiểu lầm', 'Buồn bã'], ['Sắp thi mà chưa ôn', 'Lo lắng'], ['Được khen trước lớp', 'Tự hào']], 'Mỗi tình huống gợi lên cảm xúc khác nhau, đó là điều hoàn toàn bình thường.', $d);

        $this->sortQ($s, 'Xếp các cảm xúc vào nhóm: Dễ chịu / Khó chịu.', [['Vui vẻ', 'Dễ chịu'], ['Bình yên', 'Dễ chịu'], ['Tức giận', 'Khó chịu'], ['Sợ hãi', 'Khó chịu']], 'Cảm xúc nào cũng có vai trò riêng, quan trọng là biết cách đối mặt với chúng.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về cảm xúc.', [['Ai cũng có lúc buồn, giận', 'Đúng'], ['Chia sẻ giúp nhẹ lòng', 'Đúng'], ['Giận thì được đánh người', 'Sai'], ['Con trai không được khóc', 'Sai']], 'Cảm xúc là điều tự nhiên, ai cũng có quyền cảm nhận và cần học cách thể hiện đúng.', $d);
        $this->sortQ($s, 'Xếp các cách xả stress vào nhóm: Lành mạnh / Không lành mạnh.', [['Chơi thể thao', 'Lành mạnh'], ['Nghe nhạc nhẹ', 'Lành mạnh'], ['Đập phá đồ đạc', 'Không lành mạnh'], ['Chửi bới người khác', 'Không lành mạnh']], 'Xả stress lành mạnh giúp khỏe người, cách không lành mạnh gây hại cho mình và người khác.', $d);
        $this->sortQ($s, 'Xếp các cách xử lý vào nhóm: Giúp bình tĩnh / Không giúp.', [['Hít thở sâu', 'Giúp bình tĩnh'], ['Uống một cốc nước', 'Giúp bình tĩnh'], ['La hét ầm ĩ', 'Không giúp'], ['Nghĩ mãi về chuyện bực mình', 'Không giúp']], 'Những cách đơn giản như hít thở sâu, uống nước giúp ta bình tĩnh lại nhanh chóng.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Thể hiện sự đồng cảm / Thiếu đồng cảm.', [['An ủi bạn đang buồn', 'Thể hiện sự đồng cảm'], ['Lắng nghe bạn tâm sự', 'Thể hiện sự đồng cảm'], ['Cười khi bạn khóc', 'Thiếu đồng cảm'], ['Nói kệ bạn rồi bỏ đi', 'Thiếu đồng cảm']], 'Đồng cảm là đặt mình vào vị trí người khác để hiểu và chia sẻ cùng họ.', $d);

        $this->fill($s, 'Khi tức giận, hãy hít thở thật sâu và ___ từ 1 đến 10.', [[0, 'đếm']], 'Đếm từ 1 đến 10 cho ta thời gian bình tĩnh lại trước khi hành động.', $d);
        $this->fill($s, 'Đừng đưa ra quyết định quan trọng khi đang ___ giận.', [[0, 'nóng']], 'Lúc nóng giận dễ quyết định sai nên hãy đợi bình tĩnh rồi hãy quyết định.', $d);
        $this->fill($s, 'Viết ___ ký là cách tốt để giải tỏa cảm xúc.', [[0, 'nhật']], 'Viết nhật ký giúp em nhìn rõ cảm xúc của mình và nhẹ lòng hơn.', $d);
        $this->fill($s, 'Khi buồn, hãy tìm người ___ cậy để tâm sự.', [[0, 'tin']], 'Tâm sự với người tin cậy giúp nỗi buồn vơi đi một nửa.', $d);
        $this->fill($s, 'Nụ ___ mỗi ngày giúp tâm trạng vui vẻ hơn.', [[0, 'cười']], 'Nụ cười không chỉ làm mình vui mà còn lan tỏa niềm vui đến mọi người.', $d);
    }

    private function seedKyNangSongLop72(): void
    {
        $s = 'tnhn-ky-nang-song-lop-7-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Khi xác định vấn đề, cần trả lời câu hỏi nào?', ['Vấn đề thực sự là gì, do đâu mà có', 'Ai là người có lỗi', 'Làm sao đổ lỗi nhanh nhất', 'Bỏ qua cho xong chuyện'], 0, 'Xác định đúng vấn đề và nguyên nhân là nền tảng để tìm ra giải pháp hiệu quả.', $d);
        $this->quiz($s, 'Tiêu chí nào giúp chọn được giải pháp tốt nhất?', ['Khả thi, ít tác hại, nhiều lợi ích', 'Nhanh nhất dù gây hại', 'Dễ nhất cho mình', 'Theo ý người to tiếng nhất'], 0, 'Giải pháp tốt phải làm được trong thực tế, ít gây hại và mang lại nhiều lợi ích.', $d);
        $this->quiz($s, 'Sau khi thực hiện giải pháp, cần làm gì?', ['Đánh giá kết quả và rút kinh nghiệm', 'Quên luôn', 'Đổ lỗi nếu thất bại', 'Không quan tâm nữa'], 0, 'Đánh giá kết quả giúp biết giải pháp có hiệu quả không và rút kinh nghiệm cho lần sau.', $d);
        $this->quiz($s, 'Khi các giải pháp đều có mặt hạn chế, em nên làm gì?', ['Chọn giải pháp ít hạn chế nhất', 'Bỏ cuộc', 'Chọn bừa một cái', 'Để người khác quyết hộ'], 0, 'Không có giải pháp hoàn hảo, hãy chọn cái ít hạn chế nhất và tìm cách khắc phục.', $d);
        $this->quiz($s, 'Nhờ người lớn giúp đỡ khi nào là đúng?', ['Vấn đề vượt quá khả năng như bị bạo lực, xâm hại', 'Bài tập hơi khó một chút', 'Mọi chuyện nhỏ nhặt', 'Không bao giờ nhờ ai'], 0, 'Những vấn đề nghiêm trọng vượt quá khả năng như bạo lực, xâm hại nhất định phải nhờ người lớn.', $d);

        $this->matching($s, 'Nối mỗi giai đoạn với hành động cụ thể.', [['Tìm hiểu', 'Thu thập thông tin'], ['Lên ý tưởng', 'Liệt kê mọi cách có thể'], ['Quyết định', 'Cân nhắc ưu nhược điểm'], ['Hành động', 'Thực hiện từng bước']], 'Đi đúng từng giai đoạn giúp việc giải quyết vấn đề có hệ thống và hiệu quả.', $d);
        $this->matching($s, 'Nối mỗi rắc rối học tập với cách giải quyết.', [['Quên bài tập', 'Ghi nhớ vào sổ từ nay'], ['Điểm kém', 'Tìm chỗ chưa hiểu để hỏi'], ['Mất đồ dùng', 'Hỏi bạn, báo thầy cô'], ['Ngủ gật trong lớp', 'Ngủ sớm hơn']], 'Mỗi rắc rối đều có cách giải quyết riêng, quan trọng là không bỏ cuộc.', $d);
        $this->matching($s, 'Nối mỗi nguồn thông tin với độ tin cậy.', [['Sách giáo khoa', 'Đáng tin'], ['Thầy cô', 'Đáng tin'], ['Tin đồn', 'Cần kiểm chứng'], ['Quảng cáo', 'Cần kiểm chứng']], 'Khi tìm hiểu vấn đề, cần phân biệt nguồn tin đáng tin và nguồn cần kiểm chứng.', $d);
        $this->matching($s, 'Nối mỗi thái độ với kết quả khi gặp khó khăn.', [['Bình tĩnh', 'Nghĩ ra cách hay'], ['Kiên trì', 'Vượt qua được'], ['Hoảng loạn', 'Mọi việc rối thêm'], ['Bỏ cuộc', 'Không giải quyết được gì']], 'Thái độ đúng đắn quyết định một nửa thành công khi giải quyết vấn đề.', $d);
        $this->matching($s, 'Nối mỗi vấn đề với người nên nhờ giúp.', [['Bị bạn bắt nạt', 'Thầy cô, bố mẹ'], ['Bài tập khó', 'Bạn giỏi, thầy cô'], ['Ốm đau', 'Bố mẹ, bác sĩ'], ['Lạc đường', 'Người lớn đáng tin, công an']], 'Biết nhờ đúng người, đúng lúc là kỹ năng quan trọng khi gặp vấn đề khó.', $d);

        $this->sortQ($s, 'Xếp các việc vào nhóm: Nên làm trước / Nên làm sau khi gặp vấn đề.', [['Giữ bình tĩnh', 'Nên làm trước'], ['Xác định vấn đề là gì', 'Nên làm trước'], ['Đánh giá kết quả', 'Nên làm sau'], ['Rút kinh nghiệm', 'Nên làm sau']], 'Trình tự đúng là: bình tĩnh, xác định vấn đề, hành động rồi mới đánh giá.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về giải quyết vấn đề.', [['Càng nhiều ý tưởng càng tốt', 'Đúng'], ['Nên đánh giá kết quả sau khi làm', 'Đúng'], ['Cứ làm bừa cho nhanh', 'Sai'], ['Thất bại là hết cách', 'Sai']], 'Giải quyết vấn đề cần nhiều ý tưởng, làm cẩn thận và học từ thất bại.', $d);
        $this->sortQ($s, 'Xếp các cách ứng xử vào nhóm: Nên / Không nên khi có mâu thuẫn.', [['Lắng nghe ý kiến bạn', 'Nên'], ['Tìm cách cả hai cùng vui', 'Nên'], ['Dùng bạo lực', 'Không nên'], ['Im lặng chịu đựng mãi', 'Không nên']], 'Mâu thuẫn nên được giải quyết bằng đối thoại, không dùng bạo lực hay im lặng mãi.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Xử lý ngay / Cần suy nghĩ kỹ.', [['Cháy trong bếp', 'Xử lý ngay'], ['Bạn bị thương chảy máu', 'Xử lý ngay'], ['Chọn nghề tương lai', 'Cần suy nghĩ kỹ'], ['Mua điện thoại mới', 'Cần suy nghĩ kỹ']], 'Tình huống khẩn cấp cần xử lý ngay, quyết định lớn cần suy nghĩ kỹ.', $d);
        $this->sortQ($s, 'Xếp các giải pháp vào nhóm: Tốt / Chưa tốt.', [['Hỏi thầy khi chưa hiểu bài', 'Tốt'], ['Lập kế hoạch ôn thi', 'Tốt'], ['Quay cóp trong giờ kiểm tra', 'Chưa tốt'], ['Nhờ bạn làm hộ bài', 'Chưa tốt']], 'Giải pháp tốt là giải pháp trung thực và giúp mình thực sự tiến bộ.', $d);

        $this->fill($s, 'Hãy liệt kê càng nhiều ___ pháp càng tốt trước khi chọn.', [[0, 'giải']], 'Liệt kê nhiều giải pháp giúp em có thêm lựa chọn và không bỏ sót cách hay.', $d);
        $this->fill($s, 'Giải pháp tốt là giải pháp ___ thi và ít gây hại.', [[0, 'khả']], 'Giải pháp khả thi là giải pháp có thể thực hiện được trong điều kiện thực tế.', $d);
        $this->fill($s, 'Sau khi làm, cần đánh giá ___ quả để rút kinh nghiệm.', [[0, 'kết']], 'Đánh giá kết quả giúp biết mình làm tốt ở đâu, cần sửa ở đâu.', $d);
        $this->fill($s, 'Đừng ___ loạn khi gặp vấn đề, hãy bình tĩnh suy nghĩ.', [[0, 'hoảng']], 'Hoảng loạn chỉ làm mọi việc rối thêm, bình tĩnh mới nghĩ ra cách giải quyết.', $d);
        $this->fill($s, 'Thất bại không đáng sợ nếu ta biết ___ kinh nghiệm từ nó.', [[0, 'rút']], 'Rút kinh nghiệm từ thất bại giúp ta trưởng thành và làm tốt hơn lần sau.', $d);
    }

    private function seedAnToanMoiTruongLop71(): void
    {
        $s = 'tnhn-an-toan-moi-truong-lop-7-1';
        $d = 'de';

        $this->quiz($s, 'Khi đi bộ trên đường không có vỉa hè, em nên đi thế nào?', ['Đi sát lề bên phải', 'Đi giữa đường', 'Đi ngược chiều xe', 'Chạy nhảy thoải mái'], 0, 'Đi sát lề bên phải giúp người đi bộ tránh xa dòng xe chạy trên đường.', $d);
        $this->quiz($s, 'Biển báo hình tam giác viền đỏ thường báo điều gì?', ['Báo nguy hiểm cần chú ý', 'Cấm hẳn', 'Chỉ dẫn đường đi', 'Hết hiệu lực cấm'], 0, 'Biển tam giác viền đỏ là biển báo nguy hiểm, nhắc người đi đường phải chú ý.', $d);
        $this->quiz($s, 'Biển báo hình tròn nền xanh thường có ý nghĩa gì?', ['Biển hiệu lệnh phải tuân theo', 'Biển cấm', 'Biển nguy hiểm', 'Biển quảng cáo'], 0, 'Biển tròn nền xanh là biển hiệu lệnh, người tham gia giao thông bắt buộc phải làm theo.', $d);
        $this->quiz($s, 'Khi đi xe đạp qua nơi giao nhau không có đèn, em phải làm gì?', ['Phóng nhanh qua', 'Giảm tốc độ, quan sát hai bên', 'Bấm chuông liên tục rồi lao qua', 'Nhắm mắt đi qua'], 1, 'Nơi giao nhau không có đèn rất dễ xảy ra va chạm nên phải giảm tốc độ và quan sát kỹ.', $d);
        $this->quiz($s, 'Vì sao học sinh chưa đủ tuổi không được điều khiển xe máy?', ['Vì chưa đủ kỹ năng và hiểu biết về luật', 'Vì xe máy đắt tiền', 'Vì sợ bẩn quần áo', 'Vì không thích'], 0, 'Điều khiển xe máy cần kỹ năng và hiểu biết luật mà học sinh chưa đủ tuổi thường chưa có.', $d);

        $this->matching($s, 'Nối mỗi đồ bảo hộ với tác dụng.', [['Mũ bảo hiểm', 'Bảo vệ đầu'], ['Áo phản quang', 'Dễ nhìn thấy ban đêm'], ['Đèn xe', 'Chiếu sáng đường'], ['Gương chiếu hậu', 'Quan sát phía sau']], 'Đồ bảo hộ giúp người tham gia giao thông an toàn hơn, nhất là khi trời tối.', $d);
        $this->matching($s, 'Nối mỗi hình dạng biển báo với nhóm của nó.', [['Hình tròn viền đỏ', 'Biển cấm'], ['Hình tam giác viền đỏ', 'Biển nguy hiểm'], ['Hình tròn nền xanh', 'Biển hiệu lệnh'], ['Hình chữ nhật nền xanh', 'Biển chỉ dẫn']], 'Nhớ hình dạng biển báo giúp em nhanh chóng hiểu ý nghĩa khi tham gia giao thông.', $d);
        $this->matching($s, 'Nối mỗi hành vi của người đi bộ với đánh giá.', [['Đi trên vỉa hè', 'Đúng'], ['Qua đường trên vạch kẻ', 'Đúng'], ['Chạy qua đường đột ngột', 'Sai'], ['Đi dưới lòng đường', 'Sai']], 'Người đi bộ cũng phải tuân thủ luật để đảm bảo an toàn cho chính mình.', $d);
        $this->matching($s, 'Nối mỗi tình huống đi xe đạp với cách đi đúng.', [['Đường đông', 'Đi chậm, giữ khoảng cách'], ['Muốn rẽ', 'Quan sát, giơ tay báo'], ['Trời mưa', 'Đi chậm, tránh vũng nước'], ['Xuống dốc', 'Bóp phanh nhẹ, không lao nhanh']], 'Đi xe đạp đúng cách trong từng tình huống giúp tránh tai nạn đáng tiếc.', $d);
        $this->matching($s, 'Nối mỗi loại đường với người được đi.', [['Vỉa hè', 'Người đi bộ'], ['Làn xe thô sơ', 'Xe đạp'], ['Vạch qua đường', 'Người đi bộ sang đường'], ['Lòng đường chính', 'Xe cơ giới']], 'Mỗi loại đường dành cho đối tượng khác nhau để giao thông an toàn, trật tự.', $d);

        $this->sortQ($s, 'Xếp các việc vào nhóm: Người đi bộ nên làm / Không nên làm.', [['Đi trên vỉa hè', 'Nên làm'], ['Qua đường ở vạch kẻ', 'Nên làm'], ['Vừa đi vừa cắm mặt vào điện thoại', 'Không nên làm'], ['Băng qua đường cao tốc', 'Không nên làm']], 'Người đi bộ tuân thủ quy tắc sẽ an toàn hơn rất nhiều khi ra đường.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về luật đường bộ.', [['Đi bên phải là đúng luật', 'Đúng'], ['Phải nhường xe ưu tiên', 'Đúng'], ['Xe đạp được đi vào đường cao tốc', 'Sai'], ['Đèn đỏ được phép đi nếu đường vắng', 'Sai']], 'Nắm vững luật đường bộ cơ bản giúp em tham gia giao thông đúng và an toàn.', $d);
        $this->sortQ($s, 'Xếp các biển báo vào nhóm: Biển cấm / Biển chỉ dẫn.', [['Biển cấm xe đạp', 'Biển cấm'], ['Biển cấm đi ngược chiều', 'Biển cấm'], ['Biển chỉ đường một chiều', 'Biển chỉ dẫn'], ['Biển báo bệnh viện gần đây', 'Biển chỉ dẫn']], 'Phân biệt biển cấm và biển chỉ dẫn giúp em hiểu đúng ý nghĩa từng biển báo.', $d);
        $this->sortQ($s, 'Xếp các hành động khi ngồi sau xe máy vào nhóm: An toàn / Nguy hiểm.', [['Đội mũ bảo hiểm', 'An toàn'], ['Ngồi ngay ngắn', 'An toàn'], ['Đua xe với bạn', 'Nguy hiểm'], ['Bỏ tay ra khỏi người lái để nghịch', 'Nguy hiểm']], 'Ngồi sau xe máy cũng cần tuân thủ an toàn, không đùa nghịch gây nguy hiểm.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Trước khi đi xe đạp / Khi đang đi.', [['Kiểm tra phanh, lốp', 'Trước khi đi'], ['Đội mũ bảo hiểm', 'Trước khi đi'], ['Quan sát trước khi rẽ', 'Khi đang đi'], ['Giữ khoảng cách với xe trước', 'Khi đang đi']], 'Chuẩn bị kỹ trước khi đi và cẩn thận khi đang đi giúp chuyến đi an toàn.', $d);

        $this->fill($s, 'Người điều khiển xe đạp phải đi đúng phần đường, làn đường ___ định.', [[0, 'quy']], 'Đi đúng phần đường, làn đường quy định là nghĩa vụ của mọi người tham gia giao thông.', $d);
        $this->fill($s, 'Biển báo hình tam giác viền đỏ là biển báo nguy ___.', [[0, 'hiểm']], 'Thấy biển tam giác viền đỏ là phải chú ý vì phía trước có nguy hiểm.', $d);
        $this->fill($s, 'Khi trời tối, xe đạp cần có ___ phản quang để người khác dễ nhìn thấy.', [[0, 'đèn']], 'Đèn và vật phản quang giúp người khác phát hiện xe đạp từ xa trong đêm tối.', $d);
        $this->fill($s, 'Gặp xe cứu thương đang phát tín hiệu ưu tiên, phải ___ đường ngay.', [[0, 'nhường']], 'Nhường đường cho xe ưu tiên có thể cứu sống người đang gặp nguy hiểm.', $d);
        $this->fill($s, 'Tuyệt đối không được ___ xe, lạng lách trên đường.', [[0, 'đua']], 'Đua xe, lạng lách là hành vi vi phạm pháp luật và cực kỳ nguy hiểm.', $d);
    }

    private function seedAnToanMoiTruongLop72(): void
    {
        $s = 'tnhn-an-toan-moi-truong-lop-7-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Khi phát hiện cháy, việc đầu tiên cần làm là gì?', ['Hô hoán báo động cho mọi người biết', 'Chạy vào lấy đồ đạc', 'Trốn trong tủ quần áo', 'Đứng xem cho vui'], 0, 'Báo động sớm giúp mọi người kịp thoát hiểm và lực lượng chữa cháy đến kịp thời.', $d);
        $this->quiz($s, 'Khi có nhiều khói trong phòng, nên di chuyển thế nào?', ['Chạy thật nhanh', 'Cúi thấp người, dùng khăn ướt che mũi miệng', 'Đứng thẳng hít thở sâu', 'Nhảy qua cửa sổ'], 1, 'Khói độc bay lên cao nên cúi thấp và che mũi miệng bằng khăn ướt để tránh ngạt khói.', $d);
        $this->quiz($s, 'Bình chữa cháy bột (bình màu đỏ) dùng để dập đám cháy nào?', ['Cháy xăng dầu, cháy điện', 'Cháy do nước gây ra', 'Mọi đám cháy đều dùng nước', 'Không dập được đám cháy nào'], 0, 'Bình bột chữa được cháy xăng dầu, khí gas và cháy điện, nhưng không dùng cho mọi loại cháy.', $d);
        $this->quiz($s, 'Sau khi thoát ra khỏi đám cháy, em nên làm gì?', ['Quay lại lấy đồ đạc', 'Đến nơi an toàn và gọi 114', 'Đứng gần đám cháy xem', 'Trốn một mình'], 1, 'Đến nơi an toàn rồi gọi 114 báo cháy, tuyệt đối không quay lại vùng nguy hiểm.', $d);
        $this->quiz($s, 'Để phòng cháy trong gia đình, cần lưu ý gì?', ['Tắt bếp gas, thiết bị điện khi không dùng', 'Để nến cháy qua đêm', 'Sạc điện thoại trên giường cả đêm', 'Chất đồ dễ cháy gần bếp'], 0, 'Tắt bếp gas và thiết bị điện khi không dùng là cách phòng cháy đơn giản mà hiệu quả.', $d);

        $this->matching($s, 'Nối mỗi số khẩn cấp với tình huống gọi.', [['114', 'Báo cháy'], ['115', 'Cấp cứu y tế'], ['113', 'Báo công an'], ['111', 'Bảo vệ trẻ em']], 'Nhớ các số khẩn cấp giúp em cầu cứu đúng nơi khi gặp nguy hiểm.', $d);
        $this->matching($s, 'Nối mỗi nguyên nhân cháy với cách phòng tránh.', [['Chập điện', 'Không dùng dây điện cũ nát'], ['Quên tắt bếp', 'Tắt bếp khi ra khỏi nhà'], ['Đốt vàng mã', 'Đốt nơi quy định, có người trông'], ['Tàn thuốc lá', 'Dập tắt hẳn trước khi vứt']], 'Phòng cháy tốt nhất là loại bỏ nguyên nhân gây cháy ngay từ đầu.', $d);
        $this->matching($s, 'Nối mỗi việc làm đúng khi cháy với lý do.', [['Cúi thấp người', 'Tránh hít khói độc'], ['Dùng khăn ướt che mũi', 'Lọc bớt khói'], ['Chạm tay vào cửa trước khi mở', 'Kiểm tra nhiệt độ'], ['Thoát bằng thang bộ', 'Thang máy có thể mất điện']], 'Mỗi việc làm đúng khi cháy đều có lý do khoa học giúp tăng cơ hội thoát hiểm.', $d);
        $this->matching($s, 'Nối mỗi đồ vật với nguy cơ cháy.', [['Bếp gas rò rỉ', 'Rất cao'], ['Nến đang cháy', 'Cao'], ['Ổ điện quá tải', 'Cao'], ['Sách để xa bếp', 'Thấp']], 'Nhận biết đồ vật có nguy cơ cháy cao giúp em phòng tránh từ xa.', $d);
        $this->matching($s, 'Nối mỗi đám cháy nhỏ với cách dập đúng.', [['Chảo dầu bốc cháy', 'Đậy nắp chảo'], ['Rèm cửa bén lửa nhỏ', 'Dùng chăn ướt dập'], ['Thiết bị điện cháy', 'Ngắt điện rồi dập'], ['Quần áo bén lửa', 'Nằm lăn trên mặt đất']], 'Dập lửa đúng cách với từng loại đám cháy nhỏ giúp tránh cháy lan nguy hiểm.', $d);

        $this->sortQ($s, 'Xếp các hành động khi ngửi thấy mùi gas vào nhóm: Nên / Không nên.', [['Mở cửa cho thoáng', 'Nên'], ['Khóa van gas', 'Nên'], ['Bật lửa kiểm tra', 'Không nên'], ['Bật công tắc điện', 'Không nên']], 'Khi rò rỉ gas, tuyệt đối không tạo tia lửa điện mà phải thông thoáng và khóa van.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về phòng cháy.', [['Tắt thiết bị điện khi không dùng', 'Đúng'], ['Để lối thoát hiểm luôn thông thoáng', 'Đúng'], ['Sạc pin qua đêm trên giường', 'Sai'], ['Đốt nến rồi đi ngủ', 'Sai']], 'Phòng cháy là những việc làm đơn giản mỗi ngày trong gia đình.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: An toàn điện / Mất an toàn điện.', [['Rút phích cắm khi không dùng', 'An toàn điện'], ['Dùng ổ cắm đúng công suất', 'An toàn điện'], ['Cắm nhiều thiết bị vào một ổ', 'Mất an toàn điện'], ['Dùng dây điện bị tróc vỏ', 'Mất an toàn điện']], 'Dùng điện an toàn giúp tránh chập điện, nguyên nhân hàng đầu gây cháy nhà.', $d);
        $this->sortQ($s, 'Xếp các nơi trong nhà vào nhóm: Dễ cháy / Ít cháy.', [['Bếp', 'Dễ cháy'], ['Kho chứa giấy', 'Dễ cháy'], ['Phòng tắm', 'Ít cháy'], ['Sân thượng thoáng', 'Ít cháy']], 'Biết nơi dễ cháy trong nhà giúp em cẩn thận hơn khi sinh hoạt.', $d);
        $this->sortQ($s, 'Xếp các cách thoát hiểm vào nhóm: Đúng / Sai.', [['Đi thang bộ xuống', 'Đúng'], ['Cúi thấp tránh khói', 'Đúng'], ['Dùng thang máy', 'Sai'], ['Quay lại lấy điện thoại', 'Sai']], 'Thoát hiểm đúng cách là đi thang bộ, tránh khói và không quay lại lấy đồ.', $d);

        $this->fill($s, 'Khi phát hiện cháy, phải hô hoán ___ động và gọi số 114.', [[0, 'báo']], 'Hô hoán báo động giúp mọi người biết để thoát hiểm kịp thời.', $d);
        $this->fill($s, 'Trong đám cháy, khói độc bay lên cao nên phải ___ thấp người mà đi.', [[0, 'cúi']], 'Cúi thấp người giúp tránh hít phải khói độc bay ở phía trên.', $d);
        $this->fill($s, 'Dùng ___ ướt che mũi miệng để hạn chế hít khói độc.', [[0, 'khăn']], 'Khăn ướt che mũi miệng lọc bớt khói độc khi thoát hiểm.', $d);
        $this->fill($s, 'Khi quần áo bén lửa, hãy nằm xuống và ___ tròn trên mặt đất.', [[0, 'lăn']], 'Nằm lăn trên mặt đất giúp dập tắt lửa trên quần áo nhanh chóng.', $d);
        $this->fill($s, 'Sau khi thoát ra ngoài, không được quay ___ vào đám cháy.', [[0, 'lại']], 'Quay lại đám cháy để lấy đồ rất nguy hiểm, tính mạng quan trọng hơn tài sản.', $d);
    }

    private function seedAnToanMoiTruongLop81(): void
    {
        $s = 'tnhn-an-toan-moi-truong-lop-8-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Hiệu ứng nhà kính gây ra hậu quả gì?', ['Trái Đất nóng lên, băng tan', 'Trái Đất lạnh đi', 'Không ảnh hưởng gì', 'Chỉ làm mưa nhiều hơn'], 0, 'Khí nhà kính giữ nhiệt khiến Trái Đất nóng lên, băng ở hai cực tan dần.', $d);
        $this->quiz($s, 'Nguồn năng lượng nào sau đây là năng lượng sạch?', ['Than đá', 'Năng lượng mặt trời', 'Dầu mỏ', 'Khí gas'], 1, 'Năng lượng mặt trời không gây ô nhiễm nên được gọi là năng lượng sạch, tái tạo.', $d);
        $this->quiz($s, 'Vì sao nên trồng nhiều cây xanh trong thành phố?', ['Chỉ để trang trí', 'Cây hấp thụ CO2, thải oxy và giảm nóng', 'Để chim có chỗ đậu', 'Không có tác dụng gì'], 1, 'Cây xanh hấp thụ khí CO2, thải ra oxy và làm mát không khí xung quanh.', $d);
        $this->quiz($s, 'Hành động nào góp phần tiết kiệm nước?', ['Đánh răng để vòi chảy liên tục', 'Tái sử dụng nước vo gạo tưới cây', 'Tắm vòi sen một tiếng', 'Rửa xe mỗi ngày'], 1, 'Tái sử dụng nước vo gạo để tưới cây là cách tiết kiệm nước đơn giản, hiệu quả.', $d);
        $this->quiz($s, 'Ô nhiễm không khí ảnh hưởng gì đến sức khỏe?', ['Gây các bệnh về hô hấp', 'Không ảnh hưởng gì', 'Giúp cơ thể khỏe hơn', 'Chỉ ảnh hưởng cây cối'], 0, 'Không khí ô nhiễm chứa bụi mịn và khí độc gây bệnh về đường hô hấp.', $d);

        $this->matching($s, 'Nối mỗi nguyên tắc 3R với ví dụ.', [['Reduce (Giảm thiểu)', 'Hạn chế mua đồ nhựa dùng một lần'], ['Reuse (Tái sử dụng)', 'Dùng lại túi, chai'], ['Recycle (Tái chế)', 'Phân loại giấy, nhựa để tái chế'], ['Repair (Sửa chữa)', 'Sửa đồ hỏng thay vì vứt đi']], 'Thực hiện 3R giúp giảm rác thải và tiết kiệm tài nguyên thiên nhiên.', $d);
        $this->matching($s, 'Nối mỗi loại chất thải với thời gian phân hủy.', [['Giấy', 'Vài tuần'], ['Vỏ cam', 'Vài tháng'], ['Chai nhựa', 'Hàng trăm năm'], ['Thủy tinh', 'Hàng nghìn năm']], 'Rác càng lâu phân hủy càng gây hại lâu dài cho môi trường.', $d);
        $this->matching($s, 'Nối mỗi hành động tiết kiệm với tài nguyên được bảo vệ.', [['Tắt điện khi ra khỏi phòng', 'Điện năng'], ['Khóa vòi nước khi đánh răng', 'Nước sạch'], ['Đi xe đạp quãng ngắn', 'Không khí'], ['Trồng cây', 'Môi trường sống']], 'Tiết kiệm trong sinh hoạt hằng ngày chính là bảo vệ tài nguyên.', $d);
        $this->matching($s, 'Nối mỗi nguồn ô nhiễm với biện pháp giảm thiểu.', [['Khí thải xe cộ', 'Dùng phương tiện công cộng'], ['Rác thải nhựa', 'Hạn chế đồ nhựa'], ['Nước thải sinh hoạt', 'Xử lý trước khi xả'], ['Tiếng ồn', 'Trồng cây xanh cách âm']], 'Mỗi nguồn ô nhiễm đều có biện pháp giảm thiểu phù hợp.', $d);
        $this->matching($s, 'Nối mỗi hiện tượng với nguyên nhân.', [['Trái Đất nóng lên', 'Khí nhà kính'], ['Mưa axit', 'Khí thải công nghiệp'], ['Rác đầy đại dương', 'Thải rác bừa bãi'], ['Sương mù quang hóa', 'Khí thải xe cộ']], 'Hiểu nguyên nhân của các hiện tượng môi trường giúp ta biết cách khắc phục.', $d);

        $this->sortQ($s, 'Xếp các vật liệu vào nhóm: Phân hủy nhanh / Phân hủy rất lâu.', [['Vỏ chuối', 'Phân hủy nhanh'], ['Giấy báo', 'Phân hủy nhanh'], ['Túi nilon', 'Phân hủy rất lâu'], ['Chai thủy tinh', 'Phân hủy rất lâu']], 'Vật liệu phân hủy rất lâu tồn tại trong môi trường hàng trăm năm nên cần hạn chế.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về môi trường.', [['Trồng cây giúp giảm nóng', 'Đúng'], ['Tiết kiệm điện là bảo vệ môi trường', 'Đúng'], ['Rác thải nhựa tự biến mất', 'Sai'], ['Ô nhiễm không khí vô hại', 'Sai']], 'Hiểu đúng về môi trường là bước đầu để hành động đúng.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Nên / Không nên để bảo vệ môi trường.', [['Đi bộ hoặc xe đạp quãng ngắn', 'Nên'], ['Mang hộp cơm cá nhân', 'Nên'], ['Xả rác xuống sông', 'Không nên'], ['Đốt rác nilon', 'Không nên']], 'Những việc làm hằng ngày của em đều ảnh hưởng trực tiếp đến môi trường.', $d);
        $this->sortQ($s, 'Xếp các nguồn năng lượng vào nhóm: Tái tạo / Không tái tạo.', [['Mặt trời', 'Tái tạo'], ['Gió', 'Tái tạo'], ['Than đá', 'Không tái tạo'], ['Dầu mỏ', 'Không tái tạo']], 'Năng lượng tái tạo không cạn kiệt và sạch hơn năng lượng hóa thạch.', $d);
        $this->sortQ($s, 'Xếp các hành động vào nhóm: Tiết kiệm / Lãng phí tài nguyên.', [['Tắt đèn khi ra khỏi phòng', 'Tiết kiệm'], ['Dùng nước vo gạo tưới cây', 'Tiết kiệm'], ['Để vòi nước chảy khi đánh răng', 'Lãng phí'], ['Bật điều hòa mà mở cửa', 'Lãng phí']], 'Tiết kiệm tài nguyên hôm nay là để dành cho thế hệ mai sau.', $d);

        $this->fill($s, 'Hiệu ứng nhà ___ khiến Trái Đất nóng dần lên.', [[0, 'kính']], 'Hiệu ứng nhà kính do khí thải gây ra làm nhiệt độ Trái Đất tăng dần.', $d);
        $this->fill($s, 'Năng lượng mặt trời là năng lượng ___ tạo, không gây ô nhiễm.', [[0, 'tái']], 'Năng lượng tái tạo như mặt trời, gió là hướng đi bền vững cho tương lai.', $d);
        $this->fill($s, 'Mỗi người nên trồng và bảo vệ cây ___ để có không khí trong lành.', [[0, 'xanh']], 'Cây xanh là lá phổi của thành phố, giúp không khí trong lành hơn.', $d);
        $this->fill($s, 'Túi nilon cần hàng trăm ___ mới phân hủy hoàn toàn.', [[0, 'năm']], 'Túi nilon tồn tại hàng trăm năm trong môi trường nên cần hạn chế sử dụng.', $d);
        $this->fill($s, 'Tiết kiệm điện, nước cũng là cách bảo vệ ___ trường.', [[0, 'môi']], 'Mỗi hành động tiết kiệm nhỏ đều góp phần bảo vệ môi trường sống.', $d);
    }

    private function seedAnToanMoiTruongLop82(): void
    {
        $s = 'tnhn-an-toan-moi-truong-lop-8-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Dấu hiệu nào cho thấy một trang web có thể lừa đảo?', ['Địa chỉ lạ, yêu cầu nhập thông tin cá nhân, quà tặng bất thường', 'Có logo đẹp', 'Tải trang nhanh', 'Nhiều màu sắc bắt mắt'], 0, 'Web lừa đảo thường có địa chỉ lạ, đòi thông tin cá nhân và dụ dỗ bằng quà tặng bất thường.', $d);
        $this->quiz($s, 'Khi có người lạ trên mạng rủ gặp mặt riêng, em nên làm gì?', ['Đồng ý gặp ngay', 'Từ chối và báo cho bố mẹ, thầy cô', 'Rủ bạn đi cùng là được', 'Cho họ địa chỉ nhà'], 1, 'Tuyệt đối không gặp riêng người lạ quen qua mạng, phải báo ngay cho bố mẹ, thầy cô.', $d);
        $this->quiz($s, 'Chia sẻ ảnh nhạy cảm của bản thân lên mạng có nguy cơ gì?', ['Bị lợi dụng, phát tán', 'Được nhiều lượt thích', 'Không sao cả', 'Trở nên nổi tiếng'], 0, 'Ảnh nhạy cảm một khi đã đăng lên mạng có thể bị lợi dụng và phát tán không kiểm soát.', $d);
        $this->quiz($s, 'Khi thấy bạn đăng thông tin sai sự thật, em nên làm gì?', ['Chia sẻ tiếp cho vui', 'Nhắc bạn kiểm chứng, không lan truyền', 'Bình luận chửi bới', 'Mặc kệ không quan tâm'], 1, 'Thấy tin sai sự thật nên nhắc bạn kiểm chứng và tuyệt đối không lan truyền tiếp.', $d);
        $this->quiz($s, 'Sử dụng mạng xã hội lành mạnh là như thế nào?', ['Có giới hạn thời gian, chia sẻ nội dung tích cực', 'Online cả ngày lẫn đêm', 'Kết bạn với người lạ', 'Đăng mọi chuyện riêng tư'], 0, 'Dùng mạng xã hội lành mạnh là giới hạn thời gian và chỉ chia sẻ nội dung tích cực.', $d);

        $this->matching($s, 'Nối mỗi loại thông tin với cách xử lý.', [['Họ tên, trường lớp', 'Chỉ chia sẻ với người quen'], ['Ảnh gia đình', 'Cân nhắc kỹ trước khi đăng'], ['Địa chỉ nhà', 'Tuyệt đối giữ bí mật'], ['Sở thích chung', 'Có thể chia sẻ']], 'Mỗi loại thông tin có mức độ nhạy cảm khác nhau, cần chia sẻ có chọn lọc.', $d);
        $this->matching($s, 'Nối mỗi thói quen dùng mạng với đánh giá.', [['Đăng xuất sau khi dùng máy chung', 'Tốt'], ['Cập nhật phần mềm thường xuyên', 'Tốt'], ['Lưu mật khẩu trên máy lạ', 'Xấu'], ['Bấm vào link lạ', 'Xấu']], 'Thói quen tốt khi dùng mạng giúp bảo vệ tài khoản và thông tin cá nhân.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu lừa đảo với hành động đúng.', [['Tin nhắn trúng thưởng kèm link', 'Không bấm, xóa ngay'], ['Người lạ xin ảnh riêng tư', 'Từ chối, chặn ngay'], ['Web yêu cầu nhập mật khẩu lạ', 'Thoát ngay'], ['Bạn bè rủ chơi game lành mạnh', 'Có thể tham gia']], 'Nhận diện dấu hiệu lừa đảo và xử lý đúng giúp em tránh bị mất tiền, mất tài khoản.', $d);
        $this->matching($s, 'Nối mỗi rắc rối trên mạng với người nên báo.', [['Bị bắt nạt', 'Bố mẹ, thầy cô'], ['Bị lừa tiền', 'Bố mẹ, công an'], ['Thấy nội dung xấu', 'Báo cáo nền tảng'], ['Quên mật khẩu', 'Dùng chức năng quên mật khẩu']], 'Gặp rắc rối trên mạng đừng giấu một mình, hãy báo cho người có thể giúp.', $d);
        $this->matching($s, 'Nối mỗi hành vi với hậu quả.', [['Đăng tin sai sự thật', 'Vi phạm pháp luật'], ['Xúc phạm người khác', 'Bị xử lý'], ['Chia sẻ bài học hay', 'Được tôn trọng'], ['Hack tài khoản người khác', 'Vi phạm pháp luật']], 'Hành vi trên mạng cũng chịu trách nhiệm như ngoài đời thực.', $d);

        $this->sortQ($s, 'Xếp các hành động vào nhóm: An toàn / Nguy hiểm trên mạng.', [['Đặt mật khẩu mạnh', 'An toàn'], ['Chỉ kết bạn với người quen', 'An toàn'], ['Chia sẻ vị trí trực tiếp', 'Nguy hiểm'], ['Gặp người lạ quen qua mạng', 'Nguy hiểm']], 'Hành động an toàn bảo vệ em, hành động nguy hiểm có thể gây hậu quả nghiêm trọng.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về an toàn mạng.', [['Không tin ngay tin nhắn trúng thưởng', 'Đúng'], ['Mật khẩu nên đổi định kỳ', 'Đúng'], ['Ảnh riêng tư đăng lên mạng là an toàn', 'Sai'], ['Người lạ trên mạng đều tốt', 'Sai']], 'Hiểu đúng về an toàn mạng giúp em tự bảo vệ mình trên không gian số.', $d);
        $this->sortQ($s, 'Xếp các tin nhắn vào nhóm: Tin cậy / Lừa đảo.', [['Thầy cô nhắn lịch học trên nhóm lớp', 'Tin cậy'], ['Bạn bè rủ ôn bài', 'Tin cậy'], ['Trúng thưởng 10 triệu, bấm link nhận', 'Lừa đảo'], ['Người lạ xin mã OTP', 'Lừa đảo']], 'Tin nhắn lừa đảo thường đánh vào lòng tham hoặc sự nhẹ dạ của người nhận.', $d);
        $this->sortQ($s, 'Xếp các thông tin vào nhóm: Giữ bí mật / Có thể chia sẻ.', [['Mật khẩu tài khoản', 'Giữ bí mật'], ['Địa chỉ nhà', 'Giữ bí mật'], ['Món ăn yêu thích', 'Có thể chia sẻ'], ['Đội bóng yêu thích', 'Có thể chia sẻ']], 'Thông tin cá nhân quan trọng phải giữ bí mật, chỉ chia sẻ điều ít nhạy cảm.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: Ứng xử văn minh / Thiếu văn minh trên mạng.', [['Bình luận lịch sự', 'Ứng xử văn minh'], ['Tôn trọng ý kiến người khác', 'Ứng xử văn minh'], ['Chửi bới, xúc phạm', 'Thiếu văn minh'], ['Đăng tin giả câu like', 'Thiếu văn minh']], 'Văn minh trên mạng cũng quan trọng như văn minh ngoài đời thực.', $d);

        $this->fill($s, 'Không bao giờ chia sẻ ___ khẩu cho người khác, kể cả bạn thân.', [[0, 'mật']], 'Mật khẩu là chìa khóa tài khoản, tuyệt đối không chia sẻ với bất kỳ ai.', $d);
        $this->fill($s, 'Khi bị quấy rối trên mạng, hãy ___ và báo cho người lớn.', [[0, 'chặn']], 'Chặn kẻ quấy rối và báo cho người lớn là cách xử lý đúng đắn nhất.', $d);
        $this->fill($s, 'Trước khi đăng ảnh, hãy suy nghĩ xem nó có ___ hưởng xấu không.', [[0, 'ảnh']], 'Một bức ảnh đăng lên có thể ảnh hưởng lâu dài nên cần suy nghĩ kỹ trước khi đăng.', $d);
        $this->fill($s, 'Tin nhắn trúng thưởng kèm đường ___ lạ thường là lừa đảo.', [[0, 'link']], 'Đường link lạ trong tin nhắn trúng thưởng thường dẫn đến trang lừa đảo.', $d);
        $this->fill($s, 'Hãy kiểm ___ thông tin trước khi chia sẻ cho người khác.', [[0, 'chứng']], 'Kiểm chứng thông tin giúp em không lan truyền tin giả trên mạng.', $d);
    }

    private function seedDinhHuongNgheNghiepLop81(): void
    {
        $s = 'tnhn-dinh-huong-nghe-nghiep-lop-8-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Nhóm nghề nào làm việc chủ yếu với máy móc, thiết bị?', ['Nhóm nghề kỹ thuật - công nghệ', 'Nhóm nghề y tế', 'Nhóm nghề giáo dục', 'Nhóm nghề nghệ thuật'], 0, 'Nhóm nghề kỹ thuật - công nghệ làm việc chủ yếu với máy móc, thiết bị và hệ thống kỹ thuật.', $d);
        $this->quiz($s, 'Nghề kiến trúc sư thuộc nhóm nghề nào?', ['Kỹ thuật - xây dựng', 'Y tế', 'Nông nghiệp', 'Dịch vụ ăn uống'], 0, 'Kiến trúc sư thiết kế các công trình xây dựng nên thuộc nhóm nghề kỹ thuật - xây dựng.', $d);
        $this->quiz($s, 'Đặc điểm chung của nhóm nghề dịch vụ là gì?', ['Phục vụ nhu cầu trực tiếp của con người', 'Chỉ làm việc với máy móc', 'Không cần giao tiếp', 'Làm việc một mình'], 0, 'Nhóm nghề dịch vụ như bán hàng, du lịch, nhà hàng phục vụ trực tiếp nhu cầu của con người.', $d);
        $this->quiz($s, 'Muốn làm việc trong nhóm nghề công nghệ thông tin, cần giỏi gì?', ['Tư duy logic, ngoại ngữ và tin học', 'Vẽ đẹp', 'Hát hay', 'Chạy nhanh'], 0, 'Nghề công nghệ thông tin cần tư duy logic, tin học vững và ngoại ngữ để tiếp cận công nghệ mới.', $d);
        $this->quiz($s, 'Nhóm nghề nông - lâm - ngư nghiệp gắn với điều gì?', ['Thiên nhiên, đất đai, sông biển', 'Văn phòng máy lạnh', 'Sân khấu biểu diễn', 'Phòng thí nghiệm'], 0, 'Nhóm nghề nông - lâm - ngư nghiệp gắn liền với thiên nhiên như đất đai, rừng và biển.', $d);

        $this->matching($s, 'Nối mỗi nghề mới với mô tả công việc.', [['Lập trình viên', 'Viết phần mềm'], ['Chuyên viên marketing', 'Quảng bá sản phẩm'], ['Kiến trúc sư', 'Thiết kế công trình'], ['Dược sĩ', 'Tư vấn, bán thuốc']], 'Các nghề hiện đại có công việc đặc trưng gắn với sự phát triển của xã hội.', $d);
        $this->matching($s, 'Nối mỗi nghề truyền thống với sản phẩm làm ra.', [['Thợ gốm', 'Đồ gốm sứ'], ['Thợ may', 'Quần áo'], ['Thợ mộc', 'Đồ gỗ'], ['Thợ rèn', 'Dụng cụ kim loại']], 'Nghề truyền thống tạo ra sản phẩm thủ công mang đậm bản sắc văn hóa.', $d);
        $this->matching($s, 'Nối mỗi nhóm nghề với kỹ năng quan trọng.', [['Nhóm nghề y tế', 'Cẩn thận, chịu áp lực'], ['Nhóm nghề kỹ thuật', 'Tư duy logic'], ['Nhóm nghề nghệ thuật', 'Sáng tạo'], ['Nhóm nghề kinh doanh', 'Giao tiếp, đàm phán']], 'Mỗi nhóm nghề đòi hỏi bộ kỹ năng riêng, hiểu rõ giúp em chọn đúng hướng.', $d);
        $this->matching($s, 'Nối mỗi nghề với đóng góp cho xã hội.', [['Bác sĩ', 'Chăm sóc sức khỏe'], ['Giáo viên', 'Dạy dỗ thế hệ trẻ'], ['Kỹ sư', 'Xây dựng hạ tầng'], ['Nông dân', 'Cung cấp lương thực']], 'Mỗi nghề đều có đóng góp riêng làm cho xã hội phát triển.', $d);
        $this->matching($s, 'Nối mỗi ngành học với nghề tương lai.', [['Công nghệ thông tin', 'Lập trình viên'], ['Y khoa', 'Bác sĩ'], ['Sư phạm', 'Giáo viên'], ['Luật', 'Luật sư']], 'Chọn ngành học phù hợp là bước quan trọng để đi đến nghề nghiệp mơ ước.', $d);

        $this->sortQ($s, 'Xếp các nghề vào nhóm: Sản xuất / Dịch vụ.', [['Công nhân may', 'Sản xuất'], ['Thợ cơ khí', 'Sản xuất'], ['Nhân viên bán hàng', 'Dịch vụ'], ['Lễ tân khách sạn', 'Dịch vụ']], 'Nhóm sản xuất tạo ra sản phẩm, nhóm dịch vụ phục vụ nhu cầu con người.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về các nhóm nghề.', [['Mỗi nhóm nghề cần phẩm chất riêng', 'Đúng'], ['Có thể chuyển đổi giữa các nhóm nghề', 'Đúng'], ['Chỉ có một nhóm nghề đáng theo đuổi', 'Sai'], ['Nghề lao động chân tay kém giá trị', 'Sai']], 'Mọi nhóm nghề đều có giá trị và con người có thể chuyển đổi giữa các nhóm nghề.', $d);
        $this->sortQ($s, 'Xếp các nghề vào nhóm: Cần bằng cấp đại học / Có thể học nghề.', [['Bác sĩ', 'Cần bằng cấp đại học'], ['Kỹ sư', 'Cần bằng cấp đại học'], ['Thợ điện', 'Có thể học nghề'], ['Thợ làm tóc', 'Có thể học nghề']], 'Có nghề cần học đại học, có nghề chỉ cần học nghề là có thể làm tốt.', $d);
        $this->sortQ($s, 'Xếp các nghề vào nhóm: Truyền thống / Hiện đại.', [['Thợ gốm', 'Truyền thống'], ['Thợ may thủ công', 'Truyền thống'], ['Chuyên viên AI', 'Hiện đại'], ['Nhà sáng tạo nội dung số', 'Hiện đại']], 'Nghề truyền thống gìn giữ văn hóa, nghề hiện đại đáp ứng nhu cầu thời đại số.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: Thuộc về nghề / Không thuộc về nghề.', [['Công việc chính', 'Thuộc về nghề'], ['Kỹ năng cần có', 'Thuộc về nghề'], ['Món ăn yêu thích', 'Không thuộc về nghề'], ['Đội bóng hâm mộ', 'Không thuộc về nghề']], 'Tìm hiểu nghề cần tập trung vào công việc, kỹ năng và môi trường làm việc của nghề đó.', $d);

        $this->fill($s, 'Nhóm nghề làm việc với máy móc, công nghệ gọi là nhóm nghề kỹ ___.', [[0, 'thuật']], 'Nhóm nghề kỹ thuật - công nghệ bao gồm kỹ sư, thợ máy và các nghề công nghệ.', $d);
        $this->fill($s, 'Nghề dạy học thuộc nhóm nghề giáo ___.', [[0, 'dục']], 'Nhóm nghề giáo dục gồm giáo viên, giảng viên làm công việc dạy dỗ.', $d);
        $this->fill($s, 'Người thiết kế các công trình xây dựng là kiến trúc ___.', [[0, 'sư']], 'Kiến trúc sư là người vẽ nên bản thiết kế của các công trình.', $d);
        $this->fill($s, 'Mỗi nhóm nghề đều có những yêu cầu và ___ chất riêng.', [[0, 'phẩm']], 'Hiểu phẩm chất mỗi nhóm nghề cần giúp em biết mình hợp với nhóm nào.', $d);
        $this->fill($s, 'Tìm hiểu kỹ các nhóm nghề giúp em định ___ tương lai đúng đắn.', [[0, 'hướng']], 'Định hướng đúng từ sớm giúp em có lộ trình học tập rõ ràng.', $d);
    }
}
