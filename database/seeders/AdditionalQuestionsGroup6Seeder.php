<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Bổ sung câu hỏi cho các bài học HIỆN CÓ nhóm G6
 * (Mỹ thuật + Giáo dục thể chất + Trải nghiệm & Hướng nghiệp) — 90 bài.
 *
 * Mỗi bài: 5 câu MỚI cho mỗi kiểu chơi (quiz/matching/sort/fill).
 * Nội dung tiếng Việt tự viết 100%, bám topic + khối lớp + độ khó của bài,
 * không trùng prompt đã có. Idempotent: chạy lại không thêm câu mới
 * (giới hạn 8 câu/kiểu/bài + kiểm tra prompt trùng).
 */
class AdditionalQuestionsGroup6Seeder extends Seeder
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
        $this->seedTtKhoiDongTruocKhiTap();
        $this->seedTtAnToanKhiVanDong();
        $this->seedTtLuatBongDaCoBan();
        $this->seedTtLuatBongRoCoBan();
        $this->seedTtRenLuyenSucKhoe();
        $this->seedTtVeSinhCaNhanKhiTap();
        $this->seedTtVanDongAnToanLop61();
        $this->seedTtVanDongAnToanLop62();
        $this->seedTtVanDongAnToanLop71();
        $this->seedTtVanDongAnToanLop72();
        $this->seedTtLuatTheThaoLop71();
        $this->seedTtLuatTheThaoLop72();
        $this->seedTtLuatTheThaoLop81();
        $this->seedTtLuatTheThaoLop82();
        $this->seedTtSucKhoeVeSinhLop81();
        $this->seedTtSucKhoeVeSinhLop82();
        $this->seedTtSucKhoeVeSinhLop91();
        $this->seedTtSucKhoeVeSinhLop92();
        $this->seedGdtcThpt10Lop101();
        $this->seedGdtcThpt10Lop102();
        $this->seedGdtcThpt10Lop103();
        $this->seedGdtcThpt10Lop104();
        $this->seedGdtcThpt11Lop111();
        $this->seedGdtcThpt11Lop112();
        $this->seedGdtcThpt11Lop113();
        $this->seedGdtcThpt11Lop114();
        $this->seedGdtcThpt12Lop121();
        $this->seedGdtcThpt12Lop122();
        $this->seedGdtcThpt12Lop123();
        $this->seedGdtcThpt12Lop124();
    }

    // ============ GIÁO DỤC THỂ CHẤT ============

    private function seedTtKhoiDongTruocKhiTap(): void
    {
        $s = 'tt-khoi-dong-truoc-khi-tap'; $d = 'de';
        $this->quiz($s, 'Khi khởi động, các động tác nên được thực hiện với cường độ thế nào?', ['Nhẹ nhàng rồi tăng dần', 'Mạnh ngay từ đầu', 'Nhanh hết sức có thể', 'Thật chậm và đột ngột'], 0, 'Khởi động cần nhẹ nhàng rồi tăng dần cường độ để cơ thể thích nghi, tránh chấn thương.', $d);
        $this->quiz($s, 'Trước khi chạy, bộ phận nào của cơ thể cần được khởi động?', ['Cổ, vai, tay, chân và các khớp', 'Chỉ cần cổ tay', 'Chỉ cần cổ chân', 'Không cần khởi động bộ phận nào'], 0, 'Chạy dùng nhiều nhóm cơ và khớp nên cần khởi động toàn thân.', $d);
        $this->quiz($s, 'Nếu bỏ qua bước khởi động, nguy cơ nào dễ xảy ra nhất?', ['Chuột rút, căng cơ, bong gân', 'Khát nước nhiều hơn', 'Ngủ ngon hơn', 'Tăng chiều cao nhanh'], 0, 'Cơ và khớp chưa được làm nóng rất dễ bị tổn thương khi vận động mạnh đột ngột.', $d);
        $this->quiz($s, 'Sau khi khởi động đúng cách, cơ thể có biểu hiện nào?', ['Người ấm lên, tim đập nhanh hơn một chút', 'Tay chân tê cứng', 'Chóng mặt, buồn nôn', 'Mệt lả không muốn tập'], 0, 'Khởi động đúng làm thân nhiệt tăng nhẹ, tim đập nhanh hơn và tinh thần tỉnh táo.', $d);
        $this->quiz($s, 'Động tác xoay khớp cổ tay, cổ chân khi khởi động có tác dụng gì?', ['Làm khớp linh hoạt, tránh trẹo khớp', 'Làm xương dài ra nhanh', 'Giúp khớp kêu cho vui tai', 'Không có tác dụng gì'], 0, 'Xoay khớp nhẹ nhàng giúp khớp vận động trơn tru và linh hoạt hơn.', $d);
        $this->matching($s, 'Nối mỗi động tác khởi động với nhóm cơ được làm nóng.', [['Xoay cổ', 'Cơ cổ'], ['Vung tay', 'Cơ vai, cánh tay'], ['Gập bụng nhẹ', 'Cơ bụng'], ['Nhún gối', 'Cơ đùi, khớp gối']], 'Mỗi động tác khởi động tác động lên một nhóm cơ, khớp nhất định.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn khởi động với nội dung phù hợp.', [['Chạy nhẹ tại chỗ', 'Làm nóng toàn thân'], ['Xoay các khớp', 'Tăng độ linh hoạt khớp'], ['Ép dẻo nhẹ', 'Kéo giãn cơ'], ['Chạy tăng tốc ngắn', 'Chuẩn bị cho vận động mạnh']], 'Khởi động gồm các giai đoạn từ nhẹ đến mạnh dần.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với ý nghĩa của nó sau khi khởi động.', [['Người ấm dần lên', 'Cơ thể đã sẵn sàng'], ['Thở hơi nhanh', 'Tim mạch đang hoạt động tốt'], ['Ra mồ hôi nhẹ', 'Cường độ khởi động vừa đủ'], ['Đau nhói ở cơ', 'Cần dừng lại kiểm tra']], 'Quan sát dấu hiệu cơ thể giúp biết khởi động đã đủ hay cần điều chỉnh.', $d);
        $this->matching($s, 'Nối mỗi dụng cụ với cách dùng khi khởi động.', [['Dây nhảy', 'Khởi động tim mạch'], ['Thảm tập', 'Ép dẻo, giãn cơ'], ['Bóng nhỏ', 'Khởi động tay, phối hợp'], ['Cột mốc', 'Bài tập di chuyển']], 'Dụng cụ hỗ trợ giúp phần khởi động đa dạng và hiệu quả hơn.', $d);
        $this->matching($s, 'Nối mỗi môn thể thao với động tác khởi động chuyên môn gợi ý.', [['Bóng đá', 'Chạy bước nhỏ, đá chân'], ['Bóng rổ', 'Nhảy nhẹ, xoay cổ tay'], ['Bơi lội', 'Xoay vai, vung tay'], ['Cầu lông', 'Xoay cổ tay, bước ngang']], 'Ngoài khởi động chung, mỗi môn còn có động tác khởi động riêng cho nhóm cơ chính.', $d);
        $this->sortQ($s, 'Xếp các động tác vào nhóm: Nhẹ nhàng (phù hợp khởi động) / Quá mạnh (không phù hợp).', [['Xoay khớp cổ tay', 'Nhẹ nhàng'], ['Chạy nâng cao đùi hết sức', 'Quá mạnh'], ['Gập người chạm mũi chân', 'Nhẹ nhàng'], ['Bật nhảy xa hết lực', 'Quá mạnh'], ['Đi bộ nhanh', 'Nhẹ nhàng'], ['Chạy nước rút 100m', 'Quá mạnh']], 'Khởi động chỉ dùng động tác nhẹ nhàng, tăng dần; động tác hết sức để dành cho phần chính.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Nên làm trước buổi tập / Không cần thiết.', [['Khởi động 5-10 phút', 'Nên làm'], ['Uống một ít nước', 'Nên làm'], ['Ăn no ngay trước khi tập', 'Không cần thiết'], ['Kiểm tra sân bãi, giày dép', 'Nên làm'], ['Ngồi chơi điện thoại', 'Không cần thiết']], 'Chuẩn bị tốt trước buổi tập gồm khởi động, nước uống và kiểm tra đồ dùng, sân bãi.', $d);
        $this->sortQ($s, 'Xếp các động tác vào nhóm theo phần cơ thể được tác động.', [['Xoay vai', 'Phần trên'], ['Xoay hông', 'Phần dưới'], ['Vung tay', 'Phần trên'], ['Nhún gối', 'Phần dưới'], ['Gập cổ sang hai bên', 'Phần trên'], ['Xoay cổ chân', 'Phần dưới']], 'Khởi động toàn diện cần tác động cả phần trên và phần dưới cơ thể.', $d);
        $this->sortQ($s, 'Xếp các biểu hiện vào nhóm: Khởi động đúng cách / Khởi động sai cách.', [['Người ấm dần, dễ chịu', 'Đúng cách'], ['Đau nhói ở khớp', 'Sai cách'], ['Thở đều, tinh thần tỉnh táo', 'Đúng cách'], ['Mệt lả trước khi vào bài tập', 'Sai cách']], 'Khởi động đúng giúp cơ thể ấm lên và tỉnh táo; đau hoặc mệt lả là dấu hiệu sai cách.', $d);
        $this->sortQ($s, 'Xếp các động tác vào nhóm: Làm nóng cơ thể / Kéo giãn cơ.', [['Chạy nhẹ tại chỗ', 'Làm nóng'], ['Ép dẻo đùi sau', 'Kéo giãn'], ['Nhảy dây chậm', 'Làm nóng'], ['Gập người giữ 10 giây', 'Kéo giãn'], ['Bước ngang nhanh', 'Làm nóng']], 'Khởi động gồm làm nóng cơ thể bằng vận động nhẹ và kéo giãn các nhóm cơ.', $d);
        $this->fill($s, 'Khởi động giúp cơ thể nóng dần lên và phòng tránh chấn ___.', [[0, 'thương']], 'Khởi động làm nóng cơ, khớp nên giảm nguy cơ chấn thương khi vận động mạnh.', $d);
        $this->fill($s, 'Khi khởi động, nên bắt đầu từ phần ___ cơ thể rồi mới xuống phần dưới.', [[0, 'trên']], 'Khởi động từ trên xuống dưới giúp tác động đầy đủ các khớp một cách có hệ thống.', $d);
        $this->fill($s, 'Động tác ___ dẻo giúp cơ bắp mềm mại và linh hoạt hơn.', [[0, 'ép']], 'Ép dẻo kéo giãn cơ, tăng biên độ vận động của khớp.', $d);
        $this->fill($s, 'Sau khi khởi động đúng, cơ thể thấy ___ lên và tinh thần tỉnh táo.', [[0, 'ấm']], 'Thân nhiệt tăng nhẹ, người ấm lên cho thấy cơ thể đã sẵn sàng vận động.', $d);
        $this->fill($s, 'Không nên khởi động quá ___ vì sẽ làm mất sức trước khi tập chính.', [[0, 'mạnh']], 'Khởi động quá mạnh gây mệt sớm, ảnh hưởng đến phần tập chính.', $d);
    }

    private function seedTtAnToanKhiVanDong(): void
    {
        $s = 'tt-an-toan-khi-van-dong'; $d = 'de';
        $this->quiz($s, 'Vì sao không nên đeo trang sức khi tập thể thao?', ['Dễ vướng víu, gây trầy xước hoặc chấn thương', 'Làm đẹp hơn khi tập', 'Giúp tập khỏe hơn', 'Không ảnh hưởng gì'], 0, 'Trang sức có thể vướng vào dụng cụ, bạn tập hoặc gây trầy xước khi va chạm.', $d);
        $this->quiz($s, 'Khi trời nắng gắt, tập thể thao ngoài trời cần lưu ý gì?', ['Đội mũ, uống đủ nước, tránh giờ nắng đỉnh điểm', 'Tập càng lâu càng tốt', 'Không cần uống nước', 'Mặc áo khoác dày'], 0, 'Nắng gắt dễ gây say nắng, mất nước nên cần che chắn và bổ sung nước.', $d);
        $this->quiz($s, 'Sân tập bị ướt sau mưa, em nên làm gì?', ['Báo thầy cô, chờ sân khô hoặc chuyển chỗ khác', 'Cứ tập bình thường cho nhanh', 'Chạy thật nhanh cho vui', 'Tập các động tác nhảy cao'], 0, 'Sân ướt rất trơn, dễ trượt ngã gây chấn thương nên phải xử lý trước khi tập.', $d);
        $this->quiz($s, 'Khi bạn tập bị ngã và chảy máu nhẹ, em nên làm gì?', ['Báo thầy cô và giúp bạn cầm máu, sát trùng', 'Để bạn tự lo, tiếp tục chơi', 'Đổ nước đá lên vết thương', 'Bôi đất lên vết thương'], 0, 'Vết thương chảy máu cần được cầm máu, sát trùng và báo người lớn xử lý.', $d);
        $this->quiz($s, 'Vì sao khi tập thể thao không nên đùa giỡn, xô đẩy bạn bè?', ['Dễ gây ngã, va chạm dẫn đến chấn thương', 'Đùa giỡn giúp vui hơn', 'Xô đẩy là cách khởi động tốt', 'Không sao cả'], 0, 'Đùa giỡn, xô đẩy khi vận động rất dễ gây ngã và chấn thương cho cả hai bên.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu nguy hiểm với hành động cần làm.', [['Chóng mặt, buồn nôn', 'Dừng tập, ngồi nghỉ'], ['Đau nhói ở khớp', 'Báo thầy cô ngay'], ['Khó thở', 'Ngồi nghỉ, thở sâu'], ['Chảy máu', 'Cầm máu, sát trùng']], 'Khi cơ thể có dấu hiệu bất thường phải dừng tập và báo người lớn ngay.', $d);
        $this->matching($s, 'Nối mỗi môn thể thao với đồ bảo hộ phù hợp.', [['Đạp xe', 'Mũ bảo hiểm'], ['Bóng đá', 'Giày đinh, bảo vệ ống chân'], ['Bơi lội', 'Kính bơi, phao (nếu chưa biết bơi)'], ['Cầu lông', 'Giày thể thao đế mềm']], 'Mỗi môn thể thao có đồ bảo hộ riêng giúp giảm nguy cơ chấn thương.', $d);
        $this->matching($s, 'Nối mỗi thời điểm với việc nên làm để giữ sức khỏe.', [['Trước khi tập', 'Khởi động, uống ít nước'], ['Trong khi tập', 'Uống nước từng ngụm nhỏ'], ['Sau khi tập', 'Thả lỏng, lau mồ hôi'], ['Buổi tối', 'Ngủ đủ giấc']], 'Chăm sóc cơ thể đúng ở từng thời điểm giúp tập luyện an toàn và hiệu quả.', $d);
        $this->matching($s, 'Nối mỗi vật dụng với cách dùng an toàn.', [['Bình nước cá nhân', 'Uống từng ngụm nhỏ'], ['Khăn mặt riêng', 'Lau mồ hôi, không dùng chung'], ['Giày thể thao', 'Buộc dây chắc chắn'], ['Túi đựng đồ', 'Cất gọn, tránh vướng lối đi']], 'Dùng đồ cá nhân đúng cách vừa vệ sinh vừa tránh tai nạn.', $d);
        $this->matching($s, 'Nối mỗi nơi tập với lưu ý an toàn.', [['Sân cỏ ướt', 'Dễ trơn, cần kiểm tra trước'], ['Sân bê tông', 'Nên đi giày đế mềm'], ['Bể bơi', 'Không chạy nhảy quanh thành bể'], ['Phòng tập', 'Sắp xếp dụng cụ gọn gàng']], 'Mỗi địa điểm tập có nguy cơ riêng, cần quan sát và phòng tránh.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: An toàn / Nguy hiểm khi chơi ở sân trường.', [['Xếp hàng chờ đến lượt', 'An toàn'], ['Chen lấn, xô đẩy', 'Nguy hiểm'], ['Khởi động trước khi chơi', 'An toàn'], ['Đá bóng vào khu vực đông người', 'Nguy hiểm'], ['Nhặt rác trên sân', 'An toàn']], 'Giữ trật tự, khởi động và quan sát xung quanh giúp sân chơi an toàn cho mọi người.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Nên làm / Không nên làm khi trời mưa to.', [['Tìm chỗ trú an toàn', 'Nên làm'], ['Đứng dưới gốc cây cao', 'Không nên làm'], ['Tiếp tục đá bóng ngoài sân', 'Không nên làm'], ['Báo thầy cô', 'Nên làm']], 'Mưa to kèm sấm sét rất nguy hiểm, phải tìm chỗ trú và báo người lớn.', $d);
        $this->sortQ($s, 'Xếp các loại giày vào nhóm: Phù hợp / Không phù hợp khi tập thể thao.', [['Giày thể thao vừa chân', 'Phù hợp'], ['Dép lê', 'Không phù hợp'], ['Giày cao gót', 'Không phù hợp'], ['Giày đinh đá bóng', 'Phù hợp']], 'Giày thể thao vừa chân, đế bám tốt giúp bảo vệ chân và tránh trượt ngã.', $d);
        $this->sortQ($s, 'Xếp các thói quen vào nhóm: Tốt / Xấu cho sức khỏe người tập luyện.', [['Ngủ đủ 8-9 tiếng', 'Tốt'], ['Thức khuya chơi game', 'Xấu'], ['Ăn đủ bữa', 'Tốt'], ['Bỏ bữa sáng', 'Xấu'], ['Uống đủ nước', 'Tốt']], 'Ngủ đủ, ăn đủ bữa và uống đủ nước là nền tảng sức khỏe cho người tập luyện.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Cần dừng tập ngay / Có thể tiếp tục.', [['Bị bong gân cổ chân', 'Dừng ngay'], ['Hơi khát nước', 'Tiếp tục'], ['Chóng mặt, hoa mắt', 'Dừng ngay'], ['Ra mồ hôi nhiều', 'Tiếp tục'], ['Đau ngực, khó thở', 'Dừng ngay']], 'Đau, chóng mặt, khó thở là tín hiệu nguy hiểm, phải dừng tập và báo người lớn.', $d);
        $this->fill($s, 'Khi tập thể thao dưới trời nắng, cần đội ___ và uống đủ nước.', [[0, 'mũ']], 'Mũ che nắng kết hợp uống nước giúp phòng say nắng, say nóng.', $d);
        $this->fill($s, 'Không nên tập thể thao khi đang bị ___ hoặc sốt.', [[0, 'ốm']], 'Cơ thể đang ốm cần nghỉ ngơi, vận động mạnh lúc này rất nguy hiểm.', $d);
        $this->fill($s, 'Dây giày phải được buộc ___ chắn trước khi vận động.', [[0, 'chắc']], 'Dây giày lỏng dễ tuột gây vấp ngã khi chạy nhảy.', $d);
        $this->fill($s, 'Khi chơi thể thao, nên để đồ đạc cá nhân ___ gàng ở một góc sân.', [[0, 'gọn']], 'Đồ đạc bừa bãi trên sân dễ gây vấp ngã cho người đang vận động.', $d);
        $this->fill($s, 'Tuyệt đối không ___ đẩy, đùa giỡn khi bạn đang thực hiện động tác khó.', [[0, 'xô']], 'Xô đẩy lúc bạn đang vận động có thể gây ngã và chấn thương nghiêm trọng.', $d);
    }

    private function seedTtLuatBongDaCoBan(): void
    {
        $s = 'tt-luat-bong-da-co-ban'; $d = 'trung_binh';
        $this->quiz($s, 'Cầu thủ nào được phép dùng tay chơi bóng trong trận đấu?', ['Thủ môn, trong vòng cấm của đội mình', 'Mọi cầu thủ', 'Tiền đạo', 'Hậu vệ'], 0, 'Chỉ thủ môn được dùng tay và chỉ trong vòng cấm địa của đội mình.', $d);
        $this->quiz($s, 'Khi bóng đi hết đường biên dọc, trận đấu được tiếp tục bằng cách nào?', ['Ném biên', 'Đá phạt góc', 'Phát bóng', 'Thả bóng'], 0, 'Bóng ra ngoài ở biên dọc thì đội không chạm bóng cuối cùng được hưởng quả ném biên.', $d);
        $this->quiz($s, 'Thẻ vàng trong bóng đá có ý nghĩa gì?', ['Cảnh cáo cầu thủ phạm lỗi', 'Truất quyền thi đấu ngay', 'Thưởng cho cầu thủ', 'Kết thúc trận đấu'], 0, 'Thẻ vàng là hình thức cảnh cáo; hai thẻ vàng thành một thẻ đỏ.', $d);
        $this->quiz($s, 'Bàn thắng được công nhận khi nào?', ['Bóng đi qua hoàn toàn vạch vôi khung thành', 'Bóng chạm cột dọc', 'Bóng bay qua xà ngang', 'Thủ môn chạm được vào bóng'], 0, 'Bàn thắng hợp lệ khi toàn bộ quả bóng vượt qua vạch vôi giữa hai cột dọc và dưới xà ngang.', $d);
        $this->quiz($s, 'Quả đá phạt góc được thực hiện khi nào?', ['Đội phòng ngự đưa bóng hết đường biên ngang sân mình', 'Bóng ra biên dọc', 'Có cầu thủ việt vị', 'Trọng tài thổi còi kết thúc hiệp'], 0, 'Đội tấn công được hưởng phạt góc khi đối phương đưa bóng hết đường biên ngang của sân mình.', $d);
        $this->matching($s, 'Nối mỗi tình huống với cách đá phạt tương ứng.', [['Phạm lỗi ngoài vòng cấm', 'Đá phạt trực tiếp'], ['Phạm lỗi trong vòng cấm', 'Phạt đền'], ['Bóng ra biên dọc', 'Ném biên'], ['Bóng ra biên ngang do đội thủ chạm cuối', 'Phạt góc']], 'Mỗi tình huống phạm lỗi hoặc bóng ra ngoài có cách tiếp tục trận đấu riêng.', $d);
        $this->matching($s, 'Nối mỗi vị trí với khu vực hoạt động chính.', [['Thủ môn', 'Trước khung thành'], ['Hậu vệ', 'Phần sân nhà'], ['Tiền vệ', 'Giữa sân'], ['Tiền đạo', 'Gần khung thành đối phương']], 'Mỗi vị trí đảm nhận một khu vực và nhiệm vụ riêng trên sân.', $d);
        $this->matching($s, 'Nối mỗi tín hiệu của trọng tài với ý nghĩa.', [['Thổi còi dài', 'Bắt đầu hoặc kết thúc hiệp đấu'], ['Giơ thẻ vàng', 'Cảnh cáo'], ['Giơ thẻ đỏ', 'Truất quyền thi đấu'], ['Chỉ tay về phía khung thành', 'Công nhận bàn thắng']], 'Trọng tài dùng còi và cử chỉ để điều khiển trận đấu.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với mô tả đúng.', [['Việt vị', 'Đứng sau hậu vệ cuối khi nhận bóng tấn công'], ['Phạt đền', 'Đá phạt từ chấm 11m'], ['Ném biên', 'Ném bóng bằng hai tay qua đầu'], ['Hiệp phụ', 'Thời gian đá thêm khi hòa ở vòng loại trực tiếp']], 'Nắm vững thuật ngữ giúp hiểu và theo dõi trận đấu tốt hơn.', $d);
        $this->matching($s, 'Nối mỗi số áo thường gặp với vị trí tương ứng.', [['Số 1', 'Thủ môn'], ['Số 4, 5', 'Trung vệ'], ['Số 10', 'Tiền vệ tấn công'], ['Số 9', 'Tiền đạo cắm']], 'Số áo thường gắn với vị trí truyền thống trên sân, dù hiện nay có thể linh hoạt hơn.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Được phép / Bị cấm trong bóng đá.', [['Tranh bóng bằng chân hợp lệ', 'Được phép'], ['Dùng tay chơi bóng (trừ thủ môn trong vòng cấm)', 'Bị cấm'], ['Xô đẩy đối phương từ phía sau', 'Bị cấm'], ['Chuyền bóng cho đồng đội', 'Được phép'], ['Cố tình đá vào chân đối phương', 'Bị cấm']], 'Bóng đá cấm dùng tay (trừ thủ môn) và các hành vi thô bạo, nguy hiểm.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Đội tấn công được hưởng / Đội phòng ngự được hưởng.', [['Đối phương đưa bóng hết biên ngang sân mình', 'Tấn công'], ['Mình đưa bóng hết biên ngang sân đối phương', 'Phòng ngự'], ['Đối phương phạm lỗi trong vòng cấm của họ', 'Tấn công'], ['Bóng ra biên dọc do đối phương chạm cuối', 'Tấn công']], 'Đội không phạm lỗi hoặc không chạm bóng cuối cùng sẽ được hưởng quyền tiếp tục.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Việt vị / Không việt vị.', [['Nhận bóng khi đứng sau hậu vệ cuối cùng của đối phương', 'Việt vị'], ['Nhận bóng từ quả ném biên của đồng đội', 'Không việt vị'], ['Đứng ngang hàng với hậu vệ cuối khi đồng đội chuyền', 'Không việt vị'], ['Đứng trong vòng cấm đối phương khi chỉ còn thủ môn phía trước', 'Việt vị']], 'Việt vị xảy ra khi cầu thủ tấn công đứng sau hàng thủ đối phương, trừ một số tình huống đặc biệt như ném biên.', $d);
        $this->sortQ($s, 'Xếp các quả đá vào nhóm: Đá phạt trực tiếp / Đá phạt gián tiếp.', [['Đá thẳng vào khung thành ghi bàn được công nhận', 'Trực tiếp'], ['Phải chạm thêm một cầu thủ khác mới được tính bàn thắng', 'Gián tiếp'], ['Phạm lỗi kéo áo đối phương', 'Trực tiếp'], ['Thủ môn bắt bóng từ đường chuyền về của đồng đội', 'Gián tiếp']], 'Đá phạt trực tiếp có thể ghi bàn ngay, đá phạt gián tiếp cần chạm thêm người khác.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về luật bóng đá.', [['Mỗi đội có 11 cầu thủ trên sân', 'Đúng'], ['Thủ môn được dùng tay ở mọi vị trí', 'Sai'], ['Nhận 2 thẻ vàng sẽ bị truất quyền thi đấu', 'Đúng'], ['Bóng chạm tay cầu thủ tấn công ghi bàn vẫn được công nhận', 'Sai']], 'Nắm luật cơ bản giúp chơi đúng và tránh bị phạt oan.', $d);
        $this->fill($s, 'Khi bóng đi hết đường biên dọc, đội không chạm bóng cuối cùng được hưởng quả ném ___.', [[0, 'biên']], 'Ném biên là cách tiếp tục trận đấu khi bóng ra ngoài ở biên dọc.', $d);
        $this->fill($s, 'Cầu thủ nhận thẻ ___ sẽ phải rời sân ngay lập tức.', [[0, 'đỏ']], 'Thẻ đỏ truất quyền thi đấu, đội bóng phải chơi thiếu người.', $d);
        $this->fill($s, 'Quả phạt góc được thực hiện từ ___ sân gần vị trí bóng ra ngoài nhất.', [[0, 'góc']], 'Phạt góc đá từ góc sân, là cơ hội ghi bàn tốt cho đội tấn công.', $d);
        $this->fill($s, 'Thời gian nghỉ giữa hai hiệp đấu bóng đá là ___ phút.', [[0, '15']], 'Giữa hai hiệp, cầu thủ được nghỉ tối đa 15 phút.', $d);
        $this->fill($s, 'Bàn thắng chỉ được công nhận khi bóng đi ___ hoàn toàn vạch vôi khung thành.', [[0, 'qua']], 'Luật quy định toàn bộ quả bóng phải vượt qua vạch vôi thì mới tính bàn thắng.', $d);
    }

    private function seedTtLuatBongRoCoBan(): void
    {
        $s = 'tt-luat-bong-ro-co-ban'; $d = 'trung_binh';
        $this->quiz($s, 'Trong bóng rổ, cầu thủ được phép làm gì khi đang giữ bóng?', ['Dẫn bóng khi di chuyển', 'Ôm bóng chạy thoải mái', 'Giữ bóng đứng yên mãi', 'Chuyền bóng bằng chân'], 0, 'Muốn di chuyển khi giữ bóng, cầu thủ phải dẫn bóng liên tục bằng một tay.', $d);
        $this->quiz($s, 'Lỗi hai lần dẫn bóng trong bóng rổ là gì?', ['Dừng dẫn bóng rồi lại dẫn tiếp', 'Dẫn bóng quá nhanh', 'Dẫn bóng bằng hai tay luân phiên', 'Chuyền bóng cho đồng đội'], 0, 'Sau khi đã ôm bóng dừng dẫn, cầu thủ không được dẫn bóng lại lần nữa.', $d);
        $this->quiz($s, 'Một trận bóng rổ theo luật FIBA gồm mấy hiệp?', ['4 hiệp, mỗi hiệp 10 phút', '2 hiệp, mỗi hiệp 45 phút', '3 hiệp, mỗi hiệp 15 phút', '1 hiệp 40 phút'], 0, 'Bóng rổ FIBA thi đấu 4 hiệp, mỗi hiệp 10 phút.', $d);
        $this->quiz($s, 'Khi cầu thủ bị phạm lỗi trong lúc ném rổ không thành công, hình phạt là gì?', ['Được ném phạt', 'Được ném lại từ đầu', 'Đối phương bị thẻ đỏ', 'Không có hình phạt'], 0, 'Phạm lỗi với người đang ném rổ thì người bị phạm lỗi được ném phạt.', $d);
        $this->quiz($s, 'Trong bóng rổ, bắt bóng bật bảng có vai trò gì?', ['Giành lại quyền kiểm soát bóng sau cú ném hỏng', 'Tính thêm 3 điểm', 'Kết thúc hiệp đấu', 'Phạm lỗi kỹ thuật'], 0, 'Bắt bóng bật bảng giúp đội giành lại bóng để tổ chức tấn công hoặc phòng thủ.', $d);
        $this->matching($s, 'Nối mỗi khu vực ném với số điểm.', [['Trong vòng cung 3 điểm', '2 điểm'], ['Ngoài vòng cung 3 điểm', '3 điểm'], ['Vạch ném phạt', '1 điểm'], ['Ném rổ sau tiếng còi hết giờ', 'Không tính']], 'Điểm số phụ thuộc vào vị trí ném và loại cú ném.', $d);
        $this->matching($s, 'Nối mỗi lỗi với tên gọi.', [['Ôm bóng chạy quá 2 bước', 'Chạy bước'], ['Dẫn bóng lại sau khi đã dừng', 'Hai lần dẫn bóng'], ['Giữ bóng quá 5 giây không chuyền', 'Lỗi 5 giây'], ['Chạm bóng khi bóng đang rơi xuống rổ', 'Chặn bóng']], 'Bóng rổ có nhiều lỗi kỹ thuật, cầu thủ cần nắm để tránh mất bóng.', $d);
        $this->matching($s, 'Nối mỗi vị trí với nhiệm vụ.', [['Hậu vệ dẫn bóng', 'Tổ chức tấn công'], ['Tiền vệ ghi điểm', 'Ghi điểm chủ lực'], ['Trung phong', 'Tranh bóng dưới rổ'], ['Dự bị', 'Sẵn sàng thay người']], 'Mỗi vị trí trong bóng rổ có vai trò riêng trong tấn công và phòng thủ.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa.', [['Dẫn bóng', 'Đập bóng xuống sân khi di chuyển'], ['Chuyền bóng', 'Đưa bóng cho đồng đội'], ['Ném rổ', 'Tung bóng vào rổ ghi điểm'], ['Phòng thủ', 'Ngăn đối phương ghi điểm']], 'Bốn kỹ năng cơ bản của bóng rổ là dẫn, chuyền, ném và phòng thủ.', $d);
        $this->matching($s, 'Nối mỗi tình huống với số quả ném phạt.', [['Bị phạm lỗi khi ném 2 điểm không vào', '2 quả ném phạt'], ['Bị phạm lỗi khi ném 3 điểm không vào', '3 quả ném phạt'], ['Bị phạm lỗi khi ném vào rổ', '1 quả ném phạt cộng điểm'], ['Lỗi kỹ thuật của đối phương', '1 quả ném phạt']], 'Số quả ném phạt phụ thuộc vào tình huống phạm lỗi.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Hợp lệ / Phạm luật trong bóng rổ.', [['Dẫn bóng bằng một tay khi di chuyển', 'Hợp lệ'], ['Ôm bóng chạy 3 bước', 'Phạm luật'], ['Chuyền bóng cho đồng đội', 'Hợp lệ'], ['Đá bóng bằng chân', 'Phạm luật'], ['Ném bóng vào rổ đối phương', 'Hợp lệ']], 'Bóng rổ chủ yếu dùng tay; dùng chân chơi bóng hoặc ôm bóng chạy là phạm luật.', $d);
        $this->sortQ($s, 'Xếp các cú ném vào nhóm theo số điểm.', [['Ném phạt thành công', '1 điểm'], ['Ném trong vòng cung', '2 điểm'], ['Ném ngoài vạch 3 điểm', '3 điểm'], ['Úp rổ', '2 điểm']], 'Ném phạt 1 điểm, ném thường 2 điểm, ném xa ngoài vạch 3 điểm được 3 điểm.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được tiếp tục / Mất bóng.', [['Dẫn bóng đúng luật qua sân đối phương', 'Được tiếp tục'], ['Phạm lỗi chạy bước', 'Mất bóng'], ['Bóng ra ngoài do đối phương chạm cuối', 'Được tiếp tục'], ['Giữ bóng quá 24 giây không ném', 'Mất bóng']], 'Phạm lỗi kỹ thuật hoặc hết thời gian tấn công sẽ khiến đội mất quyền kiểm soát bóng.', $d);
        $this->sortQ($s, 'Xếp các kỹ năng vào nhóm: Tấn công / Phòng thủ.', [['Ném rổ', 'Tấn công'], ['Chắn bóng', 'Phòng thủ'], ['Dẫn bóng qua người', 'Tấn công'], ['Bắt bóng bật bảng', 'Phòng thủ'], ['Chuyền nhanh phản công', 'Tấn công']], 'Bóng rổ gồm hai mặt tấn công ghi điểm và phòng thủ ngăn đối phương.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về bóng rổ.', [['Mỗi đội có 5 cầu thủ trên sân', 'Đúng'], ['Được ôm bóng chạy thoải mái', 'Sai'], ['Ném phạt thành công được 1 điểm', 'Đúng'], ['Chiều cao rổ bóng rổ là 3,05m', 'Đúng']], 'Nắm vững luật cơ bản giúp chơi bóng rổ đúng và hiệu quả.', $d);
        $this->fill($s, 'Khi dẫn bóng, cầu thủ chỉ được dùng ___ tay đập bóng xuống sân.', [[0, 'một']], 'Dẫn bóng bằng một tay; dùng hai tay cùng lúc rồi tiếp tục là lỗi hai lần dẫn bóng.', $d);
        $this->fill($s, 'Đội tấn công phải dứt điểm trong vòng ___ giây.', [[0, '24']], 'Luật 24 giây buộc đội tấn công phải ném rổ, giúp trận đấu diễn ra nhanh.', $d);
        $this->fill($s, 'Cầu thủ không được đứng trong khu vực dưới rổ đối phương quá ___ giây.', [[0, '3']], 'Luật 3 giây ngăn cầu thủ tấn công đứng lì dưới rổ chờ bóng.', $d);
        $this->fill($s, 'Vành rổ bóng rổ được treo ở độ cao 3,___m so với mặt sân.', [[0, '05']], 'Chiều cao chuẩn của rổ bóng rổ là 3,05m.', $d);
        $this->fill($s, 'Sau mỗi hiệp đấu, hai đội ___ sân cho nhau.', [[0, 'đổi']], 'Hai đội đổi sân sau mỗi hiệp để đảm bảo công bằng.', $d);
    }

    private function seedTtRenLuyenSucKhoe(): void
    {
        $s = 'tt-ren-luyen-suc-khoe'; $d = 'de';
        $this->quiz($s, 'Ngoài vận động, yếu tố nào cũng rất quan trọng để cơ thể khỏe mạnh?', ['Ăn uống đủ chất và ngủ đủ giấc', 'Chơi game nhiều', 'Thức khuya', 'Uống nước ngọt'], 0, 'Sức khỏe là tổng hòa của vận động, dinh dưỡng và giấc ngủ.', $d);
        $this->quiz($s, 'Vì sao học sinh không nên ngồi một chỗ quá lâu?', ['Dễ mỏi cơ, đau lưng và tăng cân', 'Ngồi lâu giúp cao nhanh', 'Ngồi lâu tốt cho mắt', 'Không ảnh hưởng gì'], 0, 'Ngồi lâu gây mỏi cơ xương, ảnh hưởng tuần hoàn và thị lực.', $d);
        $this->quiz($s, 'Loại đồ uống nào tốt nhất khi khát sau giờ học?', ['Nước lọc', 'Nước ngọt có ga', 'Trà sữa', 'Nước tăng lực'], 0, 'Nước lọc bù nước tốt nhất, không đường và không chất kích thích.', $d);
        $this->quiz($s, 'Tập thể dục buổi sáng mang lại lợi ích gì?', ['Tinh thần tỉnh táo, sẵn sàng cho ngày học', 'Buồn ngủ cả ngày', 'Mất thời gian', 'Không có lợi ích'], 0, 'Vận động nhẹ buổi sáng giúp máu lưu thông, tinh thần sảng khoái.', $d);
        $this->quiz($s, 'Khi nào nên đi khám sức khỏe định kỳ?', ['Ít nhất 1 lần mỗi năm', 'Chỉ khi ốm nặng', 'Không bao giờ cần', 'Mỗi 10 năm một lần'], 0, 'Khám định kỳ giúp phát hiện sớm vấn đề sức khỏe để xử lý kịp thời.', $d);
        $this->matching($s, 'Nối mỗi nhóm thực phẩm với lợi ích.', [['Rau xanh, trái cây', 'Bổ sung vitamin, chất xơ'], ['Thịt, trứng, sữa', 'Cung cấp đạm xây dựng cơ thể'], ['Cơm, bánh mì', 'Cung cấp năng lượng'], ['Nước', 'Điều hòa thân nhiệt']], 'Ăn đa dạng các nhóm thực phẩm giúp cơ thể phát triển toàn diện.', $d);
        $this->matching($s, 'Nối mỗi thói quen xấu với hậu quả.', [['Thức khuya', 'Mệt mỏi, giảm trí nhớ'], ['Bỏ bữa sáng', 'Thiếu năng lượng học tập'], ['Uống nhiều nước ngọt', 'Tăng cân, hại răng'], ['Ngồi lì một chỗ', 'Đau lưng, mỏi cổ']], 'Thói quen xấu tích lũy lâu ngày sẽ làm sức khỏe suy giảm.', $d);
        $this->matching($s, 'Nối mỗi hoạt động với thời lượng gợi ý mỗi ngày.', [['Vận động thể chất', 'Ít nhất 60 phút'], ['Ngủ', '8-9 tiếng'], ['Học tập', 'Tập trung, có nghỉ giải lao'], ['Giải trí', 'Vừa phải, không quá 2 tiếng']], 'Phân bổ thời gian hợp lý cho vận động, ngủ, học và chơi giúp cân bằng cuộc sống.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với ý nghĩa sức khỏe.', [['Ăn ngon, ngủ tốt', 'Cơ thể khỏe mạnh'], ['Mệt mỏi kéo dài', 'Cần nghỉ ngơi, xem lại sinh hoạt'], ['Hay ốm vặt', 'Sức đề kháng kém'], ['Tinh thần vui vẻ', 'Tâm lý ổn định']], 'Lắng nghe dấu hiệu cơ thể giúp điều chỉnh sinh hoạt kịp thời.', $d);
        $this->matching($s, 'Nối mỗi môn vận động với lợi ích nổi bật.', [['Chạy bộ', 'Tăng sức bền tim mạch'], ['Bơi lội', 'Phát triển toàn thân'], ['Nhảy dây', 'Rèn sự nhanh nhẹn'], ['Đá bóng', 'Rèn tinh thần đồng đội']], 'Mỗi môn thể thao có lợi ích riêng, nên chọn môn phù hợp sở thích.', $d);
        $this->sortQ($s, 'Xếp các món ăn vặt vào nhóm: Tốt cho sức khỏe / Nên hạn chế.', [['Trái cây tươi', 'Tốt'], ['Bim bim, snack', 'Hạn chế'], ['Sữa chua', 'Tốt'], ['Kẹo ngọt', 'Hạn chế'], ['Các loại hạt', 'Tốt']], 'Trái cây, sữa chua, các loại hạt giàu dinh dưỡng; đồ ngọt, snack nhiều đường muối nên hạn chế.', $d);
        $this->sortQ($s, 'Xếp các hoạt động cuối tuần vào nhóm: Vận động tích cực / Thụ động.', [['Đạp xe cùng gia đình', 'Tích cực'], ['Nằm xem điện thoại cả ngày', 'Thụ động'], ['Chơi cầu lông', 'Tích cực'], ['Ngủ nướng đến trưa', 'Thụ động'], ['Đi bộ công viên', 'Tích cực']], 'Cuối tuần nên dành thời gian vận động ngoài trời thay vì chỉ nằm một chỗ.', $d);
        $this->sortQ($s, 'Xếp các thói quen buổi tối vào nhóm: Giúp ngủ ngon / Gây khó ngủ.', [['Đi ngủ đúng giờ', 'Ngủ ngon'], ['Uống cà phê buổi tối', 'Khó ngủ'], ['Đọc sách nhẹ nhàng', 'Ngủ ngon'], ['Chơi game đến khuya', 'Khó ngủ']], 'Thói quen tốt buổi tối giúp dễ ngủ và ngủ sâu, sáng dậy tỉnh táo.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Tăng sức đề kháng / Làm giảm sức đề kháng.', [['Tập thể dục đều đặn', 'Tăng'], ['Ăn nhiều rau củ', 'Tăng'], ['Thức khuya thường xuyên', 'Giảm'], ['Hút thuốc lá', 'Giảm'], ['Giữ vệ sinh cá nhân', 'Tăng']], 'Sinh hoạt lành mạnh giúp hệ miễn dịch khỏe, chống lại bệnh tật.', $d);
        $this->sortQ($s, 'Xếp các loại nước uống vào nhóm: Nên uống thường xuyên / Nên hạn chế.', [['Nước lọc', 'Thường xuyên'], ['Nước ép trái cây tươi', 'Thường xuyên'], ['Nước ngọt có ga', 'Hạn chế'], ['Trà sữa', 'Hạn chế'], ['Sữa tươi', 'Thường xuyên']], 'Nước lọc, sữa và nước ép tươi tốt cho cơ thể; đồ uống nhiều đường nên hạn chế.', $d);
        $this->fill($s, 'Mỗi ngày nên uống đủ khoảng 1,5 đến 2 ___ nước.', [[0, 'lít']], 'Uống đủ nước giúp cơ thể hoạt động tốt và đào thải độc tố.', $d);
        $this->fill($s, 'Không nên dùng điện thoại liên tục quá 2 ___ mà không cho mắt nghỉ ngơi.', [[0, 'tiếng']], 'Cho mắt nghỉ sau mỗi 1-2 giờ dùng thiết bị điện tử giúp bảo vệ thị lực.', $d);
        $this->fill($s, 'Rửa tay bằng xà phòng giúp phòng tránh nhiều bệnh ___ nhiễm.', [[0, 'lây']], 'Rửa tay sạch là cách đơn giản mà hiệu quả để phòng bệnh lây nhiễm.', $d);
        $this->fill($s, 'Tập thể dục đều đặn giúp xương khớp chắc ___ và cơ thể dẻo dai.', [[0, 'khỏe']], 'Vận động thường xuyên là cách tốt nhất để xương khớp chắc khỏe.', $d);
        $this->fill($s, 'Ăn chậm, nhai ___ giúp tiêu hóa tốt hơn.', [[0, 'kỹ']], 'Nhai kỹ giúp thức ăn dễ tiêu hóa và tạo cảm giác no, tránh ăn quá nhiều.', $d);
    }

    private function seedTtVeSinhCaNhanKhiTap(): void
    {
        $s = 'tt-ve-sinh-ca-nhan-khi-tap'; $d = 'de';
        $this->quiz($s, 'Vì sao nên tắm sau khi tập thể thao?', ['Loại bỏ mồ hôi, bụi bẩn và vi khuẩn trên da', 'Tắm cho mát thôi, không cần thiết', 'Để khoe cơ bắp', 'Tốn nước vô ích'], 0, 'Mồ hôi và bụi bẩn bám trên da sau khi tập là môi trường cho vi khuẩn gây mùi và bệnh da.', $d);
        $this->quiz($s, 'Quần áo tập bị ướt mồ hôi nên xử lý thế nào?', ['Thay ra ngay, giặt sạch và phơi khô', 'Mặc tiếp đến hôm sau', 'Vắt khô rồi mặc lại', 'Để nguyên trong túi kín'], 0, 'Quần áo ẩm ướt dễ sinh vi khuẩn, gây mùi hôi và bệnh ngoài da.', $d);
        $this->quiz($s, 'Bình nước cá nhân nên được vệ sinh như thế nào?', ['Rửa sạch hằng ngày', 'Không bao giờ rửa', 'Rửa mỗi năm một lần', 'Chỉ tráng qua khi nhớ'], 0, 'Bình nước dùng hằng ngày cần rửa sạch để tránh vi khuẩn tích tụ gây bệnh đường ruột.', $d);
        $this->quiz($s, 'Móng tay dài khi chơi thể thao có thể gây gì?', ['Cào xước bản thân và bạn chơi', 'Không ảnh hưởng gì', 'Giúp cầm bóng chắc hơn', 'Làm đẹp tay'], 0, 'Móng tay dài dễ gây trầy xước khi va chạm, nên cắt ngắn gọn khi chơi thể thao.', $d);
        $this->quiz($s, 'Sau khi tập, nên lau khô người trước khi vào phòng điều hòa vì sao?', ['Tránh bị cảm lạnh do thay đổi nhiệt độ đột ngột', 'Để tiết kiệm điện', 'Không cần thiết', 'Để da khô nhanh'], 0, 'Người ướt mồ hôi gặp lạnh đột ngột rất dễ bị cảm lạnh, viêm họng.', $d);
        $this->matching($s, 'Nối mỗi vật dụng cá nhân với lý do không nên dùng chung.', [['Khăn mặt', 'Lây bệnh ngoài da'], ['Bình nước', 'Lây bệnh đường hô hấp'], ['Lược', 'Lây chấy, nấm da đầu'], ['Giày', 'Lây nấm chân']], 'Đồ dùng cá nhân tiếp xúc trực tiếp cơ thể nên mỗi người dùng riêng để tránh lây bệnh.', $d);
        $this->matching($s, 'Nối mỗi thời điểm với việc vệ sinh cần làm.', [['Trước khi tập', 'Buộc tóc gọn, cắt móng tay'], ['Trong khi tập', 'Lau mồ hôi bằng khăn riêng'], ['Sau khi tập', 'Tắm rửa, thay đồ khô'], ['Hằng tuần', 'Giặt khăn, vệ sinh giày']], 'Vệ sinh đúng từng thời điểm giúp cơ thể sạch sẽ và phòng bệnh.', $d);
        $this->matching($s, 'Nối mỗi thói quen với đánh giá.', [['Tắm ngay sau khi tập', 'Tốt'], ['Mặc lại đồ ướt mồ hôi', 'Xấu'], ['Rửa tay trước khi ăn', 'Tốt'], ['Dùng chung khăn với bạn', 'Xấu']], 'Thói quen vệ sinh tốt bảo vệ sức khỏe bản thân và người xung quanh.', $d);
        $this->matching($s, 'Nối mỗi bộ phận với cách chăm sóc khi tập luyện.', [['Da', 'Tắm sạch sau khi ra mồ hôi'], ['Tóc', 'Buộc gọn khi tập'], ['Chân', 'Đi giày vừa, thay tất khô'], ['Tay', 'Rửa sạch trước khi ăn']], 'Mỗi bộ phận cơ thể cần được chăm sóc phù hợp khi vận động.', $d);
        $this->matching($s, 'Nối mỗi loại rác sau buổi tập với cách xử lý.', [['Chai nhựa', 'Bỏ vào thùng tái chế'], ['Khăn giấy đã dùng', 'Bỏ vào thùng rác chung'], ['Vỏ trái cây', 'Bỏ vào thùng rác hữu cơ'], ['Băng gạc y tế', 'Gói kín bỏ thùng rác']], 'Dọn rác đúng nơi sau buổi tập giữ vệ sinh chung cho sân bãi.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Vệ sinh cá nhân / Vệ sinh chung.', [['Tắm sau khi tập', 'Cá nhân'], ['Nhặt rác trên sân', 'Chung'], ['Giặt quần áo tập', 'Cá nhân'], ['Lau dọn phòng thay đồ', 'Chung'], ['Rửa bình nước', 'Cá nhân']], 'Vệ sinh cá nhân giữ cơ thể sạch sẽ, vệ sinh chung giữ môi trường tập luyện sạch đẹp.', $d);
        $this->sortQ($s, 'Xếp các vật dụng vào nhóm: Nên mang theo khi tập / Không cần thiết.', [['Bình nước cá nhân', 'Nên mang'], ['Khăn mặt riêng', 'Nên mang'], ['Đồ trang sức', 'Không cần'], ['Giày thể thao', 'Nên mang'], ['Gối ôm', 'Không cần']], 'Mang đủ đồ dùng cá nhân cần thiết giúp buổi tập thoải mái và vệ sinh.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Đúng / Sai về vệ sinh khi tập.', [['Uống chung bình nước với bạn', 'Sai'], ['Lau mồ hôi bằng khăn riêng', 'Đúng'], ['Nhổ nước bọt bừa bãi trên sân', 'Sai'], ['Bỏ rác đúng nơi quy định', 'Đúng'], ['Mặc đồ tập sạch mỗi buổi', 'Đúng']], 'Giữ vệ sinh khi tập vừa bảo vệ sức khỏe vừa tôn trọng người xung quanh.', $d);
        $this->sortQ($s, 'Xếp các thời điểm vào nhóm: Cần tắm rửa ngay / Có thể để sau.', [['Sau khi tập ra nhiều mồ hôi', 'Ngay'], ['Sau khi đi bộ nhẹ 10 phút', 'Để sau'], ['Sau khi chơi bóng dưới trời nắng', 'Ngay'], ['Sau khi ngồi học trong lớp', 'Để sau']], 'Khi ra nhiều mồ hôi cần tắm rửa sớm để da sạch sẽ, tránh vi khuẩn.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Phòng bệnh ngoài da / Chưa đủ để phòng bệnh.', [['Tắm sau khi tập', 'Phòng bệnh'], ['Thay đồ khô ngay', 'Phòng bệnh'], ['Mặc đồ ẩm cả ngày', 'Chưa đủ'], ['Dùng chung khăn mặt', 'Chưa đủ'], ['Giặt khăn tập thường xuyên', 'Phòng bệnh']], 'Da sạch, khô ráo và đồ dùng riêng là chìa khóa phòng bệnh ngoài da.', $d);
        $this->fill($s, 'Sau khi tập, nên ___ khô mồ hôi trước khi thay quần áo.', [[0, 'lau']], 'Lau khô mồ hôi giúp cơ thể sạch sẽ, tránh cảm lạnh khi thay đồ.', $d);
        $this->fill($s, 'Không nên đi chân ___ trên sàn phòng thay đồ công cộng.', [[0, 'đất']], 'Sàn phòng thay đồ công cộng dễ có nấm, nên đi dép để bảo vệ chân.', $d);
        $this->fill($s, 'Tóc dài nên được buộc ___ gàng khi tập thể thao.', [[0, 'gọn']], 'Tóc buộc gọn tránh vướng víu, che tầm nhìn và gây mất an toàn.', $d);
        $this->fill($s, 'Tất chân ướt mồ hôi cần được thay bằng tất ___ ngay sau buổi tập.', [[0, 'khô']], 'Tất khô giúp chân thoáng, phòng nấm chân và mùi hôi.', $d);
        $this->fill($s, 'Giày thể thao ướt cần được phơi nơi thoáng ___ để nhanh khô.', [[0, 'gió']], 'Gió và nắng nhẹ giúp giày khô nhanh, tránh ẩm mốc.', $d);
    }

    private function seedTtVanDongAnToanLop61(): void
    {
        $s = 'tt-van-dong-an-toan-lop-6-1'; $d = 'de';
        $this->quiz($s, 'Trước khi chơi bóng ở sân trường, em nên kiểm tra điều gì?', ['Sân có vật sắc nhọn, vũng nước hay không', 'Xem có bạn nữ nào chơi không', 'Sân có đẹp để chụp ảnh không', 'Không cần kiểm tra gì'], 0, 'Kiểm tra sân giúp phát hiện vật nguy hiểm, tránh chấn thương khi chơi.', $d);
        $this->quiz($s, 'Khi chơi thể thao theo nhóm, điều quan trọng nhất là gì?', ['Tuân thủ luật chơi và tôn trọng bạn bè', 'Phải thắng bằng mọi giá', 'Chỉ chơi với bạn thân', 'Chơi càng mạnh càng tốt'], 0, 'Chơi đúng luật và tôn trọng nhau giúp mọi người đều vui và an toàn.', $d);
        $this->quiz($s, 'Nếu bạn chơi bị đau và ngồi xuống ôm chân, em nên làm gì?', ['Dừng chơi, hỏi thăm và báo thầy cô', 'Cười bạn yếu đuối', 'Kéo bạn đứng dậy chơi tiếp', 'Bỏ mặc bạn'], 0, 'Khi bạn bị đau phải dừng ngay, quan tâm và nhờ người lớn giúp đỡ.', $d);
        $this->quiz($s, 'Vì sao khi vận động không nên vừa chạy vừa nhìn điện thoại?', ['Dễ vấp ngã, va vào người khác', 'Nhìn điện thoại giúp chạy nhanh hơn', 'Không sao cả', 'Giúp giải trí khi chạy'], 0, 'Mất tập trung khi vận động rất dễ gây tai nạn cho mình và người xung quanh.', $d);
        $this->quiz($s, 'Sau khi vận động mạnh, nên làm gì để cơ thể hồi phục?', ['Đi bộ chậm, thả lỏng vài phút', 'Ngồi bệt xuống ngay lập tức', 'Uống thật nhiều nước đá', 'Nằm lăn ra sân'], 0, 'Thả lỏng nhẹ nhàng giúp nhịp tim giảm dần, cơ thể hồi phục tốt hơn.', $d);
        $this->matching($s, 'Nối mỗi nguyên tắc với ý nghĩa.', [['Khởi động trước', 'Chuẩn bị cơ thể'], ['Chơi đúng luật', 'An toàn cho mọi người'], ['Thả lỏng sau', 'Hồi phục cơ thể'], ['Uống đủ nước', 'Bù nước đã mất']], 'Bốn nguyên tắc vàng giúp buổi vận động an toàn và hiệu quả.', $d);
        $this->matching($s, 'Nối mỗi dụng cụ thể thao với lưu ý khi dùng.', [['Bóng đá', 'Không đá vào người khác'], ['Dây nhảy', 'Chừa khoảng trống xung quanh'], ['Vợt cầu lông', 'Không vung vợt gần mặt bạn'], ['Thảm tập', 'Trải phẳng, tránh trơn trượt']], 'Dùng dụng cụ đúng cách tránh gây thương tích cho mình và bạn bè.', $d);
        $this->matching($s, 'Nối mỗi biểu hiện của bạn chơi với cách ứng xử.', [['Bạn bị ngã', 'Đỡ bạn dậy, hỏi thăm'], ['Bạn chơi sai luật', 'Nhắc nhở nhẹ nhàng'], ['Bạn thắng mình', 'Chúc mừng bạn'], ['Bạn mệt xin nghỉ', 'Đồng ý, để bạn nghỉ']], 'Ứng xử đẹp khi chơi thể thao thể hiện tinh thần thể thao và tình bạn.', $d);
        $this->matching($s, 'Nối mỗi thời tiết với cách vận động phù hợp.', [['Trời nắng nhẹ', 'Chơi bình thường, uống đủ nước'], ['Trời nắng gắt', 'Chơi trong bóng râm, rút ngắn thời gian'], ['Trời mưa', 'Nghỉ, chơi trong nhà'], ['Trời lạnh', 'Khởi động kỹ hơn']], 'Điều chỉnh vận động theo thời tiết để bảo vệ sức khỏe.', $d);
        $this->matching($s, 'Nối mỗi việc chuẩn bị với thứ tự thực hiện.', [['Đầu tiên', 'Mặc đồ thể thao, đi giày'], ['Tiếp theo', 'Khởi động 5-10 phút'], ['Sau đó', 'Vào bài tập chính'], ['Cuối cùng', 'Thả lỏng, nghỉ ngơi']], 'Buổi vận động có trình tự: chuẩn bị, khởi động, tập chính, thả lỏng.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Chuẩn bị tốt / Chuẩn bị chưa tốt.', [['Mang giày thể thao vừa chân', 'Tốt'], ['Mặc quần jean bó đi đá bóng', 'Chưa tốt'], ['Mang theo bình nước', 'Tốt'], ['Ăn no căng trước giờ tập', 'Chưa tốt'], ['Buộc tóc gọn gàng', 'Tốt']], 'Chuẩn bị tốt gồm trang phục phù hợp, giày vừa chân và nước uống.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Tinh thần thể thao đẹp / Chưa đẹp.', [['Bắt tay đối thủ sau trận đấu', 'Đẹp'], ['Cãi nhau với trọng tài', 'Chưa đẹp'], ['Cổ vũ cho cả hai đội', 'Đẹp'], ['Cố tình chơi xấu để thắng', 'Chưa đẹp']], 'Tinh thần thể thao là chơi hết mình nhưng công bằng và tôn trọng mọi người.', $d);
        $this->sortQ($s, 'Xếp các động tác vào nhóm: Khởi động / Bài tập chính.', [['Xoay khớp cổ tay', 'Khởi động'], ['Chạy nước rút 60m', 'Chính'], ['Chạy nhẹ tại chỗ', 'Khởi động'], ['Nhảy xa có đà', 'Chính'], ['Ép dẻo nhẹ', 'Khởi động']], 'Khởi động là động tác nhẹ chuẩn bị, bài tập chính là nội dung vận động cường độ cao.', $d);
        $this->sortQ($s, 'Xếp các nơi vào nhóm: Chơi được / Không nên chơi thể thao.', [['Sân trường bằng phẳng', 'Chơi được'], ['Lòng đường nhiều xe', 'Không nên'], ['Sân cỏ công viên', 'Chơi được'], ['Mái nhà', 'Không nên'], ['Sân thể thao của trường', 'Chơi được']], 'Chỉ chơi thể thao ở nơi an toàn, tránh đường giao thông và nơi cao nguy hiểm.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Trước buổi tập / Sau buổi tập.', [['Khởi động', 'Trước'], ['Thả lỏng cơ bắp', 'Sau'], ['Chuẩn bị trang phục', 'Trước'], ['Tắm rửa sạch sẽ', 'Sau'], ['Ăn nhẹ bổ sung năng lượng', 'Sau']], 'Trước tập cần chuẩn bị và khởi động, sau tập cần thả lỏng, ăn uống và vệ sinh.', $d);
        $this->fill($s, 'Khi vận động, nên mặc quần áo ___ mát, thấm mồ hôi.', [[0, 'thoáng']], 'Quần áo thoáng mát giúp cơ thể dễ chịu và thoát mồ hôi tốt.', $d);
        $this->fill($s, 'Chơi thể thao phải tuân thủ ___ chơi đã thống nhất.', [[0, 'luật']], 'Tuân thủ luật chơi giúp trận đấu công bằng và an toàn.', $d);
        $this->fill($s, 'Nếu thấy chóng mặt khi đang tập, phải ___ lại và ngồi nghỉ ngay.', [[0, 'dừng']], 'Chóng mặt là tín hiệu cơ thể cần nghỉ, cố tập tiếp rất nguy hiểm.', $d);
        $this->fill($s, 'Không được chơi bóng ở ___ đường vì rất nguy hiểm.', [[0, 'lòng']], 'Lòng đường có xe cộ qua lại, tuyệt đối không phải nơi chơi thể thao.', $d);
        $this->fill($s, 'Sau khi tập xong, nên đi bộ ___ nhàng vài phút để thả lỏng.', [[0, 'nhẹ']], 'Đi bộ nhẹ sau tập giúp nhịp tim giảm từ từ, cơ thể đỡ mỏi.', $d);
    }

    private function seedTtVanDongAnToanLop62(): void
    {
        $s = 'tt-van-dong-an-toan-lop-6-2'; $d = 'de';
        $this->quiz($s, 'Khi chạy đường dài, nên đặt chân như thế nào?', ['Tiếp đất bằng cả bàn chân, nhẹ nhàng', 'Chỉ chạm gót chân thật mạnh', 'Nhảy từng bước thật cao', 'Chạy bằng mũi chân nhón gót'], 0, 'Tiếp đất nhẹ nhàng bằng cả bàn chân giúp giảm chấn động lên khớp gối.', $d);
        $this->quiz($s, 'Khi nhảy cao, tay nên làm gì để giữ thăng bằng?', ['Vung tự nhiên theo nhịp nhảy', 'Giơ thẳng lên trời suốt', 'Bỏ tay vào túi quần', 'Khoanh tay trước ngực'], 0, 'Tay vung tự nhiên giúp giữ thăng bằng và tăng lực cho cú nhảy.', $d);
        $this->quiz($s, 'Vì sao khi chạy không nên thở bằng miệng há to?', ['Dễ bị khô họng, đau họng và mệt nhanh', 'Thở miệng giúp chạy nhanh hơn', 'Không ảnh hưởng gì', 'Thở miệng tốt cho phổi'], 0, 'Thở miệng há to làm khô họng, dễ đau rát; nên hít bằng mũi, thở ra bằng miệng vừa phải.', $d);
        $this->quiz($s, 'Tư thế đứng nghỉ đúng sau khi chạy là gì?', ['Đứng thẳng, hai tay chống hông, thở đều', 'Gập người, hai tay chống gối thở hổn hển', 'Nằm lăn ra đất', 'Ngồi xổm bó gối'], 0, 'Đứng thẳng chống hông giúp lồng ngực mở rộng, thở dễ dàng hơn.', $d);
        $this->quiz($s, 'Khi chạy tiếp sức, người nhận gậy nên làm gì?', ['Chạy đà trước và đưa tay ra sau đón gậy', 'Đứng yên chờ gậy', 'Quay đầu nhìn người trao gậy', 'Chạy ngược lại'], 0, 'Người nhận gậy chạy đà và đưa tay ra sau giúp việc trao gậy nhanh, không mất đà.', $d);
        $this->matching($s, 'Nối mỗi kỹ thuật chạy với mô tả.', [['Xuất phát', 'Tư thế sẵn sàng khi có hiệu lệnh'], ['Chạy giữa quãng', 'Giữ nhịp đều, thở đúng'], ['Về đích', 'Tăng tốc, ưỡn ngực chạm đích'], ['Chạy đà', 'Tăng dần tốc độ']], 'Chạy cự ly ngắn gồm các giai đoạn: xuất phát, chạy giữa quãng và về đích.', $d);
        $this->matching($s, 'Nối mỗi kỹ thuật nhảy với yêu cầu.', [['Giậm nhảy', 'Dùng một chân bật mạnh'], ['Trên không', 'Co chân, vung tay giữ thăng bằng'], ['Tiếp đất', 'Chạm đất bằng hai chân, gối hơi khuỵu'], ['Đo đà', 'Chạy đà đúng số bước đã đo']], 'Nhảy xa gồm giậm nhảy, bay trên không và tiếp đất an toàn.', $d);
        $this->matching($s, 'Nối mỗi cách thở với thời điểm.', [['Hít vào bằng mũi', 'Khi chạy nhẹ, đều'], ['Thở ra bằng miệng', 'Nhịp thở ra khi vận động'], ['Thở sâu, chậm', 'Khi thả lỏng sau tập'], ['Nín thở', 'Không nên khi vận động']], 'Thở đúng nhịp giúp cung cấp đủ oxy và đỡ mệt khi vận động.', $d);
        $this->matching($s, 'Nối mỗi tư thế sai với hậu quả.', [['Chạy gù lưng', 'Đau lưng, thở khó'], ['Tiếp đất bằng gót mạnh', 'Đau khớp gối'], ['Ngoái đầu nhìn sau khi chạy', 'Mất thăng bằng, dễ ngã'], ['Thở hổn hển, há miệng to', 'Mệt nhanh, khô họng']], 'Tư thế sai không chỉ kém hiệu quả mà còn dễ gây chấn thương.', $d);
        $this->matching($s, 'Nối mỗi bài tập với tố chất được rèn.', [['Chạy 60m', 'Sức nhanh'], ['Nhảy dây', 'Sự khéo léo'], ['Chống đẩy', 'Sức mạnh'], ['Chạy bền', 'Sức bền']], 'Mỗi bài tập rèn một tố chất thể lực khác nhau.', $d);
        $this->sortQ($s, 'Xếp các kỹ thuật vào nhóm: Đúng / Sai khi chạy.', [['Người hơi nghiêng về trước', 'Đúng'], ['Tay đánh chéo qua người', 'Sai'], ['Mắt nhìn thẳng về trước', 'Đúng'], ['Bước chân quá dài, nảy người cao', 'Sai'], ['Thở đều theo nhịp bước', 'Đúng']], 'Chạy đúng: người hơi nghiêng trước, mắt nhìn thẳng, tay đánh dọc thân, thở đều.', $d);
        $this->sortQ($s, 'Xếp các động tác vào nhóm: Rèn sức mạnh / Rèn sự dẻo dai.', [['Chống đẩy', 'Sức mạnh'], ['Ép dẻo', 'Dẻo dai'], ['Gập bụng', 'Sức mạnh'], ['Xoạc chân', 'Dẻo dai'], ['Kéo xà', 'Sức mạnh']], 'Bài tập dùng sức nặng cơ thể rèn sức mạnh, bài tập kéo giãn rèn dẻo dai.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Nên / Không nên khi nhảy cao.', [['Khởi động kỹ chân', 'Nên'], ['Giậm nhảy bằng một chân', 'Nên'], ['Tiếp đất bằng một chân duỗi thẳng', 'Không nên'], ['Nhảy khi sân trơn ướt', 'Không nên'], ['Đo đà trước khi nhảy', 'Nên']], 'Nhảy an toàn cần khởi động kỹ, giậm nhảy đúng và tiếp đất bằng hai chân.', $d);
        $this->sortQ($s, 'Xếp các cách thở vào nhóm: Đúng / Sai khi vận động.', [['Hít vào bằng mũi', 'Đúng'], ['Nín thở khi chạy bền', 'Sai'], ['Thở ra bằng miệng nhẹ nhàng', 'Đúng'], ['Thở gấp, há miệng to', 'Sai']], 'Khi vận động nên hít bằng mũi, thở ra bằng miệng, giữ nhịp thở đều.', $d);
        $this->sortQ($s, 'Xếp các bộ phận vào nhóm: Cần khởi động kỹ khi chạy / Ít dùng khi chạy.', [['Khớp gối', 'Cần kỹ'], ['Cổ chân', 'Cần kỹ'], ['Cơ đùi', 'Cần kỹ'], ['Khớp cổ tay', 'Ít dùng'], ['Ngón tay', 'Ít dùng']], 'Chạy dùng nhiều chân nên khớp gối, cổ chân, cơ đùi cần khởi động kỹ nhất.', $d);
        $this->fill($s, 'Khi chạy, mắt nên nhìn ___ về phía trước, không cúi đầu.', [[0, 'thẳng']], 'Nhìn thẳng giúp giữ thăng bằng và quan sát đường chạy.', $d);
        $this->fill($s, 'Bàn chân tiếp đất nên đặt ___ nhàng để giảm chấn động.', [[0, 'nhẹ']], 'Tiếp đất nhẹ giúp bảo vệ khớp gối và cổ chân.', $d);
        $this->fill($s, 'Khi nhảy xa, phải giậm nhảy bằng ___ chân đã thuận.', [[0, 'một']], 'Nhảy xa giậm bằng một chân, tiếp đất bằng hai chân.', $d);
        $this->fill($s, 'Nhịp thở khi chạy nên đều theo từng bước ___.', [[0, 'chân']], 'Thở theo nhịp bước chân giúp duy trì oxy đều và đỡ mệt.', $d);
        $this->fill($s, 'Sau khi về đích, không nên dừng ___ ngột mà đi bộ chậm lại.', [[0, 'đột']], 'Dừng đột ngột sau khi chạy nhanh dễ gây chóng mặt, cần giảm tốc từ từ.', $d);
    }

    private function seedTtVanDongAnToanLop71(): void
    {
        $s = 'tt-van-dong-an-toan-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Khi bị chuột rút ở bắp chân, nên xử lý thế nào?', ['Dừng vận động, kéo giãn nhẹ nhàng cơ bị co rút', 'Cố chạy tiếp cho qua', 'Đứng yên không làm gì', 'Nhờ bạn đấm mạnh vào chỗ đau'], 0, 'Kéo giãn nhẹ và xoa bóp giúp cơ bị chuột rút thả lỏng dần.', $d);
        $this->quiz($s, 'Vì sao khi bị bong gân không nên chườm nóng ngay?', ['Chườm nóng làm sưng to hơn trong 48 giờ đầu', 'Chườm nóng tốn tiền', 'Chườm nóng không có tác dụng', 'Chườm nóng gây lạnh'], 0, '48 giờ đầu sau bong gân phải chườm lạnh để giảm sưng; chườm nóng chỉ dùng sau đó.', $d);
        $this->quiz($s, 'Dấu hiệu nào cho thấy chấn thương nặng cần đi bệnh viện?', ['Sưng to nhanh, đau dữ dội, không cử động được', 'Hơi mỏi cơ', 'Ra ít mồ hôi', 'Hơi khát nước'], 0, 'Sưng to, đau dữ dội, biến dạng hoặc mất cử động là dấu hiệu chấn thương nặng.', $d);
        $this->quiz($s, 'Để phòng tránh chuột rút khi tập, nên làm gì?', ['Khởi động kỹ và uống đủ nước', 'Tập ngay khi vừa ăn no', 'Tập dưới trời nắng gắt', 'Không cần làm gì'], 0, 'Khởi động kỹ và đủ nước giúp cơ hoạt động tốt, giảm nguy cơ chuột rút.', $d);
        $this->quiz($s, 'Khi sơ cứu bạn bị trầy xước da, việc đầu tiên là gì?', ['Rửa sạch vết thương bằng nước sạch', 'Bôi ngay thuốc đỏ', 'Dán băng keo lên luôn', 'Thổi vào vết thương'], 0, 'Rửa sạch vết thương bằng nước sạch giúp loại bỏ bụi bẩn trước khi sát trùng.', $d);
        $this->matching($s, 'Nối mỗi bước sơ cứu bong gân với nội dung.', [['Nghỉ ngơi', 'Dừng vận động ngay'], ['Chườm lạnh', 'Giảm sưng đau 48 giờ đầu'], ['Băng ép', 'Cố định, hạn chế sưng'], ['Kê cao', 'Giúp máu lưu thông, giảm sưng']], 'Bốn bước RICE giúp xử lý bong gân nhẹ hiệu quả.', $d);
        $this->matching($s, 'Nối mỗi chấn thương với dấu hiệu nhận biết.', [['Chuột rút', 'Cơ co cứng đột ngột, đau'], ['Bong gân', 'Sưng, đau ở khớp'], ['Trầy xước', 'Da bị xước, rớm máu'], ['Trật khớp', 'Khớp biến dạng, đau dữ dội']], 'Nhận biết đúng chấn thương giúp sơ cứu đúng cách.', $d);
        $this->matching($s, 'Nối mỗi vật dụng y tế với công dụng.', [['Băng gạc', 'Băng vết thương'], ['Nước muối sinh lý', 'Rửa vết thương'], ['Túi chườm lạnh', 'Giảm sưng'], ['Băng thun', 'Cố định khớp']], 'Túi y tế thể thao nên có đủ vật dụng sơ cứu cơ bản.', $d);
        $this->matching($s, 'Nối mỗi nguyên nhân với cách phòng tránh.', [['Không khởi động', 'Khởi động 5-10 phút'], ['Sân trơn', 'Kiểm tra sân trước khi tập'], ['Thiếu nước', 'Uống đủ nước'], ['Tập quá sức', 'Tập vừa sức, tăng dần']], 'Hầu hết chấn thương nhẹ đều phòng tránh được bằng chuẩn bị tốt.', $d);
        $this->matching($s, 'Nối mỗi việc làm khi bị thương với đánh giá.', [['Báo thầy cô ngay', 'Đúng'], ['Tự ý nắn khớp', 'Sai'], ['Chườm lạnh khi bong gân', 'Đúng'], ['Cố tập tiếp khi đau dữ dội', 'Sai']], 'Khi bị thương phải báo người lớn, không tự ý xử lý sai cách.', $d);
        $this->sortQ($s, 'Xếp các cách xử lý vào nhóm: Đúng / Sai khi bị bong gân nhẹ.', [['Chườm lạnh trong 48 giờ đầu', 'Đúng'], ['Xoa dầu nóng ngay lập tức', 'Sai'], ['Nghỉ ngơi, hạn chế vận động', 'Đúng'], ['Cố chạy tiếp cho đỡ đau', 'Sai'], ['Kê cao chân bị thương', 'Đúng']], 'Bong gân nhẹ: nghỉ, chườm lạnh, băng ép, kê cao; tuyệt đối không chườm nóng sớm.', $d);
        $this->sortQ($s, 'Xếp các chấn thương vào nhóm: Sơ cứu tại chỗ được / Cần đến cơ sở y tế.', [['Trầy xước nhẹ', 'Tại chỗ'], ['Gãy xương nghi ngờ', 'Y tế'], ['Chuột rút', 'Tại chỗ'], ['Trật khớp', 'Y tế'], ['Bong gân nhẹ', 'Tại chỗ']], 'Chấn thương nhẹ sơ cứu tại chỗ; gãy xương, trật khớp phải đến cơ sở y tế.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Phòng tránh chấn thương / Gây nguy cơ chấn thương.', [['Khởi động kỹ', 'Phòng tránh'], ['Đi giày vừa chân', 'Phòng tránh'], ['Chơi xô đẩy', 'Nguy cơ'], ['Tập quá sức', 'Nguy cơ'], ['Kiểm tra dụng cụ', 'Phòng tránh']], 'Chuẩn bị tốt và chơi đúng cách giúp phòng tránh hầu hết chấn thương.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về sơ cứu.', [['Rửa vết thương bằng nước sạch trước khi băng', 'Đúng'], ['Tự nắn lại khớp bị trật', 'Sai'], ['Chườm lạnh giúp giảm sưng', 'Đúng'], ['Bỏ qua chấn thương nhẹ, tập tiếp bình thường', 'Sai']], 'Sơ cứu đúng: làm sạch vết thương, chườm lạnh khi sưng, không tự nắn khớp.', $d);
        $this->sortQ($s, 'Xếp các dấu hiệu vào nhóm: Cơ thể bình thường khi tập / Cần dừng tập.', [['Ra mồ hôi, thở hơi nhanh', 'Bình thường'], ['Đau nhói ở khớp', 'Dừng tập'], ['Hơi mỏi cơ', 'Bình thường'], ['Chóng mặt, buồn nôn', 'Dừng tập'], ['Tim đập nhanh hơn', 'Bình thường']], 'Mỏi nhẹ, ra mồ hôi là bình thường; đau nhói, chóng mặt phải dừng ngay.', $d);
        $this->fill($s, 'Khi bị chuột rút, nên ___ giãn nhẹ nhàng vùng cơ bị co cứng.', [[0, 'kéo']], 'Kéo giãn nhẹ giúp cơ đang co cứng thả lỏng dần.', $d);
        $this->fill($s, 'Vết thương trầy xước cần được rửa bằng nước ___ trước khi sát trùng.', [[0, 'sạch']], 'Rửa bằng nước sạch loại bỏ bụi bẩn, giảm nguy cơ nhiễm trùng.', $d);
        $this->fill($s, 'Sau khi sơ cứu, nếu chỗ đau sưng to hơn thì phải đến cơ sở y ___ ngay.', [[0, 'tế']], 'Sưng to dần sau sơ cứu có thể là chấn thương nặng, cần bác sĩ kiểm tra.', $d);
        $this->fill($s, 'Không nên tự ý bẻ, nắn khớp bị ___ vì có thể làm tổn thương nặng thêm.', [[0, 'trật']], 'Khớp bị trật phải được cố định và đưa đến cơ sở y tế, tuyệt đối không tự nắn.', $d);
        $this->fill($s, 'Túi y tế khi đi tập nên có băng gạc, nước muối sinh lý và túi chườm ___.', [[0, 'lạnh']], 'Túi chườm lạnh rất cần thiết để xử lý bong gân, va đập sưng đau.', $d);
    }

    private function seedTtVanDongAnToanLop72(): void
    {
        $s = 'tt-van-dong-an-toan-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Bài tập nhảy dây chủ yếu rèn luyện tố chất nào?', ['Sự nhanh nhẹn, khéo léo và sức bền', 'Sức mạnh cơ bắp tay', 'Chiều cao', 'Trí nhớ'], 0, 'Nhảy dây đòi hỏi phối hợp nhanh tay chân, rèn sự nhanh nhẹn và sức bền.', $d);
        $this->quiz($s, 'Muốn tăng sức mạnh cơ chân, nên tập bài nào?', ['Bật nhảy, chạy lên dốc', 'Ngồi đọc sách', 'Nằm nghỉ', 'Đứng yên'], 0, 'Bật nhảy và chạy dốc tác động mạnh lên cơ chân, giúp chân khỏe hơn.', $d);
        $this->quiz($s, 'Tố chất dẻo dai có vai trò gì?', ['Giúp khớp vận động linh hoạt, giảm chấn thương', 'Giúp chạy nhanh hơn', 'Giúp nhớ bài tốt hơn', 'Không có vai trò gì'], 0, 'Cơ thể dẻo dai giúp thực hiện động tác rộng, giảm nguy cơ căng cơ, rách cơ.', $d);
        $this->quiz($s, 'Nguyên tắc nào quan trọng khi rèn luyện thể lực?', ['Tăng dần cường độ, đều đặn mỗi ngày', 'Tập thật nặng một lần rồi nghỉ cả tháng', 'Tập khi nào nhớ thì tập', 'Càng đau càng tốt'], 0, 'Rèn thể lực cần đều đặn và tăng dần để cơ thể thích nghi an toàn.', $d);
        $this->quiz($s, 'Đo sức bền của học sinh thường dùng bài kiểm tra nào?', ['Chạy bền 800m nữ, 1000m nam', 'Chạy 30m', 'Nhảy xa', 'Ném bóng'], 0, 'Chạy bền cự ly trung bình là bài kiểm tra sức bền phổ biến trong trường học.', $d);
        $this->matching($s, 'Nối mỗi tố chất với định nghĩa.', [['Sức nhanh', 'Thực hiện động tác trong thời gian ngắn nhất'], ['Sức mạnh', 'Khả năng thắng lực cản của cơ bắp'], ['Sức bền', 'Duy trì vận động lâu không mệt'], ['Dẻo dai', 'Khớp vận động với biên độ lớn']], 'Bốn tố chất thể lực cơ bản là nhanh, mạnh, bền, dẻo.', $d);
        $this->matching($s, 'Nối mỗi bài tập với tố chất chính được rèn.', [['Chạy 100m', 'Sức nhanh'], ['Kéo xà đơn', 'Sức mạnh'], ['Chạy 1500m', 'Sức bền'], ['Xoạc ngang', 'Dẻo dai']], 'Mỗi bài tập rèn nổi bật một tố chất, cần phối hợp nhiều bài để phát triển toàn diện.', $d);
        $this->matching($s, 'Nối mỗi môn thể thao với tố chất nổi bật nhất.', [['Chạy nước rút', 'Sức nhanh'], ['Cử tạ', 'Sức mạnh'], ['Marathon', 'Sức bền'], ['Thể dục dụng cụ', 'Dẻo dai']], 'Mỗi môn thể thao đòi hỏi nổi bật một tố chất khác nhau.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với ý nghĩa khi tập luyện.', [['Thở đều, còn nói được', 'Cường độ vừa phải'], ['Thở hổn hển, không nói được', 'Cường độ quá cao'], ['Cơ hơi mỏi', 'Tập luyện có hiệu quả'], ['Đau nhói', 'Cần dừng lại']], 'Lắng nghe cơ thể giúp điều chỉnh cường độ tập phù hợp.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn tập với nhiệm vụ.', [['Khởi động', 'Chuẩn bị cơ thể'], ['Phần chính', 'Rèn luyện tố chất'], ['Thả lỏng', 'Hồi phục'], ['Nghỉ ngơi', 'Cơ bắp phát triển']], 'Buổi tập khoa học gồm khởi động, tập chính, thả lỏng và nghỉ ngơi hợp lý.', $d);
        $this->sortQ($s, 'Xếp các bài tập vào nhóm: Rèn sức nhanh / Rèn sức bền.', [['Chạy 60m', 'Nhanh'], ['Chạy 1500m', 'Bền'], ['Chạy tiếp sức 4x100m', 'Nhanh'], ['Đi bộ nhanh 30 phút', 'Bền'], ['Chạy nâng cao đùi 20m', 'Nhanh']], 'Cự ly ngắn, tốc độ cao rèn sức nhanh; cự ly dài, duy trì lâu rèn sức bền.', $d);
        $this->sortQ($s, 'Xếp các bài tập vào nhóm: Dùng dụng cụ / Không cần dụng cụ.', [['Nhảy dây', 'Dụng cụ'], ['Chống đẩy', 'Không cần'], ['Kéo xà đơn', 'Dụng cụ'], ['Gập bụng', 'Không cần'], ['Ném bóng', 'Dụng cụ']], 'Nhiều bài tập thể lực không cần dụng cụ, có thể tập mọi lúc mọi nơi.', $d);
        $this->sortQ($s, 'Xếp các cách tập vào nhóm: Khoa học / Không khoa học.', [['Tập đều đặn mỗi ngày', 'Khoa học'], ['Tăng dần cường độ', 'Khoa học'], ['Tập quá sức liên tục', 'Không khoa học'], ['Bỏ khởi động', 'Không khoa học'], ['Nghỉ ngơi đủ', 'Khoa học']], 'Tập khoa học là đều đặn, tăng dần, có khởi động và nghỉ ngơi hợp lý.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về tố chất thể lực.', [['Chạy bền rèn sức bền', 'Đúng'], ['Chỉ cần tập sức mạnh là đủ', 'Sai'], ['Ép dẻo giúp cơ thể linh hoạt', 'Đúng'], ['Tố chất thể lực không thể cải thiện', 'Sai']], 'Thể lực gồm nhiều tố chất và đều có thể cải thiện qua rèn luyện đúng cách.', $d);
        $this->sortQ($s, 'Xếp các hoạt động hằng ngày vào nhóm: Rèn thể lực / Ít vận động.', [['Đi bộ đến trường', 'Rèn thể lực'], ['Leo cầu thang', 'Rèn thể lực'], ['Đi thang máy', 'Ít vận động'], ['Phụ giúp việc nhà', 'Rèn thể lực'], ['Nằm xem ti vi cả buổi', 'Ít vận động']], 'Tận dụng hoạt động hằng ngày như đi bộ, leo cầu thang cũng giúp rèn thể lực.', $d);
        $this->fill($s, 'Bài tập bật cóc giúp phát triển sức ___ của đôi chân.', [[0, 'mạnh']], 'Bật cóc là bài tập rèn sức mạnh cơ chân hiệu quả.', $d);
        $this->fill($s, 'Chạy ziczac qua các cọc tiêu rèn sự nhanh nhẹn và ___ léo.', [[0, 'khéo']], 'Chạy ziczac đòi hỏi đổi hướng nhanh, rèn sự khéo léo.', $d);
        $this->fill($s, 'Để có sức bền tốt, cần tập luyện ___ đặn trong thời gian dài.', [[0, 'đều']], 'Sức bền được xây dựng qua tập luyện đều đặn, kiên trì.', $d);
        $this->fill($s, 'Trước khi đo thành tích chạy, cần khởi động thật ___ để đạt kết quả tốt.', [[0, 'kỹ']], 'Khởi động kỹ giúp cơ thể sẵn sàng, tránh chấn thương và đạt thành tích tốt.', $d);
        $this->fill($s, 'Sau mỗi buổi tập nặng, cơ thể cần được nghỉ ___ để hồi phục.', [[0, 'ngơi']], 'Nghỉ ngơi là lúc cơ bắp phục hồi và phát triển mạnh hơn.', $d);
    }

    // ============ TRẢI NGHIỆM & HƯỚNG NGHIỆP ============

    private function seedTtLuatTheThaoLop71(): void
    {
        $s = 'tt-luat-the-thao-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Sân bóng đá 11 người có chiều dài tiêu chuẩn khoảng bao nhiêu?', ['105m', '400m', '50m', '200m'], 0, 'Sân bóng đá tiêu chuẩn dài khoảng 90-120m, thường dùng 105m.', $d);
        $this->quiz($s, 'Chấm phạt đền cách khung thành bao xa?', ['11m', '5m', '16m', '9m'], 0, 'Điểm đá phạt đền cách khung thành đúng 11m.', $d);
        $this->quiz($s, 'Cầu thủ bị phạt việt vị khi nào?', ['Nhận bóng khi đứng sau hậu vệ cuối cùng của đối phương', 'Đứng trong vòng cấm đội nhà', 'Đá bóng ra ngoài biên', 'Đứng gần cột cờ góc'], 0, 'Việt vị xảy ra khi cầu thủ tấn công đứng gần khung thành đối phương hơn bóng và hậu vệ cuối khi đồng đội chuyền bóng.', $d);
        $this->quiz($s, 'Tình huống nào KHÔNG bị phạt việt vị?', ['Nhận bóng trực tiếp từ quả ném biên', 'Nhận bóng khi đứng sau hậu vệ cuối lúc đồng đội chuyền lên', 'Đứng sau thủ môn khi đồng đội sút bóng', 'Nhận bóng khi chỉ còn thủ môn phía trước'], 0, 'Nhận bóng trực tiếp từ ném biên, phạt góc hoặc phát bóng thì không bị phạt việt vị.', $d);
        $this->quiz($s, 'Khi đá phạt đền, thủ môn phải đứng ở đâu trước khi bóng được đá?', ['Trên vạch vôi khung thành', 'Ngoài vòng cấm', 'Giữa sân', 'Sau khung thành'], 0, 'Khi đá phạt đền, thủ môn phải đứng trên vạch vôi khung thành cho đến khi bóng được đá.', $d);
        $this->matching($s, 'Nối mỗi khu vực trên sân bóng đá với đặc điểm.', [['Vòng tròn giữa sân', 'Nơi bắt đầu trận đấu'], ['Vòng cấm địa', 'Khu vực 16,5m trước khung thành'], ['Chấm phạt đền', 'Cách khung thành 11m'], ['Cột cờ góc', 'Đánh dấu 4 góc sân']], 'Mỗi khu vực trên sân có kích thước và chức năng riêng.', $d);
        $this->matching($s, 'Nối mỗi tình huống với cách tiếp tục trận đấu.', [['Phạm lỗi trong vòng cấm', 'Phạt đền'], ['Bóng ra biên dọc', 'Ném biên'], ['Phạm lỗi ngoài vòng cấm', 'Đá phạt trực tiếp'], ['Cầu thủ việt vị', 'Đá phạt gián tiếp cho đối phương']], 'Mỗi tình huống có cách đưa bóng vào cuộc riêng theo luật.', $d);
        $this->matching($s, 'Nối mỗi vạch trên sân với vị trí.', [['Vạch giữa sân', 'Chia sân thành hai nửa'], ['Vạch vôi khung thành', 'Giới hạn khung thành'], ['Vạch 16,5m', 'Giới hạn vòng cấm địa'], ['Vạch 5,5m', 'Giới hạn vùng cầu môn']], 'Các vạch kẻ xác định ranh giới các khu vực trên sân.', $d);
        $this->matching($s, 'Nối mỗi người với nhiệm vụ khi đá phạt đền.', [['Người đá phạt', 'Đá bóng từ chấm 11m'], ['Thủ môn', 'Cản phá trên vạch vôi'], ['Các cầu thủ khác', 'Đứng ngoài vòng cấm'], ['Trọng tài', 'Thổi còi cho phép đá']], 'Khi đá phạt đền, mỗi người có vị trí và nhiệm vụ riêng.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa.', [['Việt vị', 'Lỗi vị trí khi tấn công'], ['Phạt đền', 'Cơ hội ghi bàn từ chấm 11m'], ['Ném biên', 'Đưa bóng vào cuộc từ biên dọc'], ['Phát bóng', 'Đưa bóng vào cuộc từ vùng cầu môn']], 'Nắm vững thuật ngữ giúp hiểu luật bóng đá tốt hơn.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Bị phạt việt vị / Không bị phạt việt vị.', [['Đứng sau hậu vệ cuối khi đồng đội chuyền lên', 'Việt vị'], ['Nhận bóng trực tiếp từ quả ném biên', 'Không việt vị'], ['Đứng ngang hàng hậu vệ cuối khi bóng được chuyền', 'Không việt vị'], ['Chạy lên nhận bóng khi chỉ còn thủ môn phía trước', 'Việt vị'], ['Nhận bóng từ quả phạt góc', 'Không việt vị'], ['Đứng ở phần sân nhà khi đồng đội chuyền', 'Không việt vị']], 'Việt vị chỉ xảy ra ở phần sân đối phương và không áp dụng với ném biên, phạt góc, phát bóng.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được đá phạt đền / Không được đá phạt đền.', [['Bị phạm lỗi trong vòng cấm đối phương', 'Được'], ['Bị phạm lỗi ở giữa sân', 'Không'], ['Đối phương dùng tay chơi bóng trong vòng cấm', 'Được'], ['Bóng ra biên dọc', 'Không']], 'Phạt đền chỉ được hưởng khi bị phạm lỗi trong vòng cấm địa của đối phương.', $d);
        $this->sortQ($s, 'Xếp các khu vực vào nhóm: Trong vòng cấm / Ngoài vòng cấm.', [['Chấm phạt đền', 'Trong'], ['Vạch 5,5m', 'Trong'], ['Vòng tròn giữa sân', 'Ngoài'], ['Cung phạt đền', 'Ngoài']], 'Vòng cấm địa là khu vực 16,5m trước khung thành; cung phạt đền nằm ngoài vòng cấm.', $d);
        $this->sortQ($s, 'Xếp các kích thước vào nhóm: Đúng chuẩn sân 11 người / Không đúng chuẩn.', [['Sân dài khoảng 105m', 'Đúng'], ['Sân rộng khoảng 68m', 'Đúng'], ['Khung thành rộng 7,32m', 'Đúng'], ['Chấm phạt đền cách khung thành 5m', 'Không đúng']], 'Sân chuẩn: dài khoảng 105m, rộng khoảng 68m, khung thành 7,32m x 2,44m, chấm phạt đền cách 11m.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về luật bóng đá.', [['Trận đấu bắt đầu từ vòng tròn giữa sân', 'Đúng'], ['Thủ môn được dùng tay ở mọi vị trí', 'Sai'], ['Phạt đền được đá từ chấm 11m', 'Đúng'], ['Nhận bóng từ ném biên vẫn bị phạt việt vị', 'Sai']], 'Nắm luật cơ bản về sân bãi, việt vị và phạt đền giúp chơi đúng luật.', $d);
        $this->fill($s, 'Sân bóng đá 11 người tiêu chuẩn có chiều dài khoảng 105m và chiều rộng khoảng ___m.', [[0, '68']], 'Chiều rộng sân tiêu chuẩn khoảng 68m.', $d);
        $this->fill($s, 'Chấm phạt đền cách khung thành ___m.', [[0, '11']], 'Điểm đá phạt đền cách khung thành đúng 11m.', $d);
        $this->fill($s, 'Cầu thủ đứng sau hậu vệ cuối cùng của đối phương khi đồng đội chuyền bóng sẽ bị phạt lỗi ___ vị.', [[0, 'việt']], 'Đứng ở vị trí việt vị và tham gia pha bóng sẽ bị phạt.', $d);
        $this->fill($s, 'Khi đá phạt đền, các cầu thủ khác phải đứng ngoài vòng ___ địa.', [[0, 'cấm']], 'Khi đá phạt đền, mọi cầu thủ khác phải đứng ngoài vòng cấm địa.', $d);
        $this->fill($s, 'Khung thành bóng đá rộng 7,32m và cao 2,___m.', [[0, '44']], 'Kích thước chuẩn của khung thành là 7,32m x 2,44m.', $d);
    }

    private function seedTtLuatTheThaoLop72(): void
    {
        $s = 'tt-luat-the-thao-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Lỗi "bước chạy" (traveling) trong bóng rổ xảy ra khi nào?', ['Cầm bóng di chuyển quá số bước cho phép mà không dẫn bóng', 'Dẫn bóng quá nhanh', 'Ném rổ không vào', 'Đứng yên quá lâu'], 0, 'Ôm bóng chạy quá 2 bước mà không dẫn bóng là lỗi bước chạy.', $d);
        $this->quiz($s, 'Sau khi bắt bóng và dừng lại, cầu thủ được phép làm gì với chân trụ?', ['Xoay người quanh chân trụ', 'Nhấc chân trụ lên rồi đặt xuống thoải mái', 'Nhảy lên rồi đáp xuống vẫn giữ bóng', 'Chạy tiếp 3 bước'], 0, 'Cầu thủ được xoay người quanh chân trụ nhưng không được nhấc chân trụ lên trước khi chuyền hoặc ném.', $d);
        $this->quiz($s, 'Dẫn bóng bằng hai tay cùng lúc rồi tiếp tục dẫn bị phạt lỗi gì?', ['Hai lần dẫn bóng', 'Bước chạy', 'Lỗi 3 giây', 'Không phạm lỗi'], 0, 'Đã dừng dẫn bóng (kể cả khi dùng hai tay ôm bóng) thì không được dẫn lại.', $d);
        $this->quiz($s, 'Lỗi "bế bóng" (carrying) khi dẫn bóng nghĩa là gì?', ['Để bóng nghỉ trong lòng bàn tay quá lâu khi dẫn', 'Ôm bóng chạy', 'Ném bóng quá mạnh', 'Chuyền bóng bằng một tay'], 0, 'Dẫn bóng phải đập bóng liên tục, không được để bóng nghỉ lâu trong tay.', $d);
        $this->quiz($s, 'Cầu thủ đang chạy nhận bóng được phép đi thêm mấy bước trước khi phải dẫn, chuyền hoặc ném?', ['2 bước', '5 bước', 'Không giới hạn', '4 bước'], 0, 'Luật cho phép đi tối đa 2 bước sau khi bắt bóng.', $d);
        $this->matching($s, 'Nối mỗi lỗi với mô tả.', [['Bước chạy', 'Cầm bóng đi quá 2 bước không dẫn'], ['Hai lần dẫn bóng', 'Dừng dẫn rồi dẫn lại'], ['Bế bóng', 'Bóng nghỉ quá lâu trong tay khi dẫn'], ['Lỗi 3 giây', 'Đứng quá lâu dưới rổ đối phương']], 'Bóng rổ có nhiều lỗi kỹ thuật liên quan đến di chuyển và dẫn bóng.', $d);
        $this->matching($s, 'Nối mỗi động tác với đánh giá.', [['Dẫn bóng bằng một tay', 'Hợp lệ'], ['Ôm bóng chạy 3 bước', 'Phạm lỗi'], ['Xoay người quanh chân trụ', 'Hợp lệ'], ['Nhấc chân trụ trước khi dẫn bóng', 'Phạm lỗi']], 'Giữ đúng chân trụ và dẫn bóng liên tục là yêu cầu cơ bản.', $d);
        $this->matching($s, 'Nối mỗi tình huống dẫn bóng với kết quả.', [['Dẫn bóng đúng luật qua người', 'Được tiếp tục tấn công'], ['Ôm bóng chạy quá bước', 'Mất bóng'], ['Dẫn bóng ra ngoài sân', 'Mất quyền kiểm soát'], ['Dừng dẫn bóng', 'Phải chuyền hoặc ném']], 'Phạm lỗi dẫn bóng khiến đội mất quyền kiểm soát bóng.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa.', [['Chân trụ', 'Chân giữ nguyên khi xoay người'], ['Bước chạy', 'Lỗi di chuyển quá số bước'], ['Dẫn bóng', 'Đập bóng xuống sân liên tục'], ['Bế bóng', 'Lỗi để bóng nghỉ trong tay']], 'Hiểu thuật ngữ giúp nắm vững luật dẫn bóng và di chuyển.', $d);
        $this->matching($s, 'Nối mỗi tình huống chân trụ với quy định.', [['Nhấc chân trụ lên', 'Chỉ được khi đã chuyền hoặc ném bóng'], ['Chân trụ chạm đất trở lại', 'Phạm lỗi bước chạy'], ['Nhảy bằng hai chân rồi đáp xuống', 'Được chuyền hoặc ném trước khi đáp'], ['Xoay quanh chân trụ', 'Hợp lệ']], 'Chân trụ là điểm tựa, vi phạm quy định về chân trụ là lỗi bước chạy.', $d);
        $this->sortQ($s, 'Xếp các hành động vào nhóm: Hợp lệ / Phạm lỗi bước chạy.', [['Bắt bóng rồi đi 2 bước và ném rổ', 'Hợp lệ'], ['Ôm bóng chạy 4 bước', 'Phạm lỗi'], ['Dừng lại, xoay quanh chân trụ', 'Hợp lệ'], ['Nhấc chân trụ rồi đặt xuống khi chưa chuyền', 'Phạm lỗi']], 'Được đi tối đa 2 bước sau khi bắt bóng; nhấc chân trụ sớm là phạm lỗi.', $d);
        $this->sortQ($s, 'Xếp các cách dẫn bóng vào nhóm: Đúng luật / Phạm luật.', [['Đập bóng bằng một tay', 'Đúng'], ['Dẫn lại sau khi đã ôm bóng dừng', 'Phạm luật'], ['Vừa chạy vừa đập bóng liên tục', 'Đúng'], ['Để bóng nghỉ lâu trong lòng bàn tay', 'Phạm luật'], ['Đổi tay dẫn bóng khi qua người', 'Đúng']], 'Dẫn bóng đúng luật: một tay, liên tục, không bế bóng, không dẫn lại sau khi dừng.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được tiếp tục dẫn / Phải dừng dẫn.', [['Vừa bắt đầu cầm bóng', 'Được tiếp tục'], ['Đã ôm bóng dừng lại', 'Phải dừng'], ['Bóng bật ra sau khi chạm tay đối phương', 'Được tiếp tục'], ['Đã dùng hai tay ôm bóng', 'Phải dừng']], 'Một khi đã dừng dẫn bóng, cầu thủ phải chuyền hoặc ném, không được dẫn lại.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về luật dẫn bóng.', [['Được dẫn bóng bằng một tay hoặc đổi tay', 'Đúng'], ['Được dẫn bóng lại sau khi đã dừng hẳn', 'Sai'], ['Không được nhấc chân trụ trước khi dẫn bóng', 'Đúng'], ['Bế bóng là lỗi khi dẫn bóng', 'Đúng']], 'Luật dẫn bóng bảo vệ tính liên tục và công bằng của trận đấu.', $d);
        $this->sortQ($s, 'Xếp số bước sau khi bắt bóng vào nhóm: Hợp lệ / Phạm lỗi.', [['Đi 1 bước rồi ném', 'Hợp lệ'], ['Đi 2 bước rồi lên rổ', 'Hợp lệ'], ['Đi 3 bước không dẫn bóng', 'Phạm lỗi'], ['Đứng yên xoay quanh chân trụ', 'Hợp lệ']], 'Sau khi bắt bóng chỉ được đi tối đa 2 bước nếu không dẫn bóng.', $d);
        $this->fill($s, 'Cầu thủ ôm bóng chạy quá 2 bước mà không dẫn bóng sẽ bị phạt lỗi bước ___.', [[0, 'chạy']], 'Lỗi bước chạy xảy ra khi di chuyển quá số bước cho phép mà không dẫn bóng.', $d);
        $this->fill($s, 'Sau khi dừng dẫn bóng, cầu thủ không được dẫn bóng lại lần nữa, đó là lỗi hai lần dẫn ___.', [[0, 'bóng']], 'Đã ôm bóng dừng dẫn thì phải chuyền hoặc ném, không được dẫn lại.', $d);
        $this->fill($s, 'Khi xoay người sau khi dừng bóng, một chân phải giữ nguyên làm chân ___.', [[0, 'trụ']], 'Chân trụ không được nhấc lên trước khi chuyền hoặc ném bóng.', $d);
        $this->fill($s, 'Để bóng nghỉ quá lâu trong lòng bàn tay khi dẫn bóng là lỗi ___ bóng.', [[0, 'bế']], 'Dẫn bóng phải đập bóng liên tục xuống sân.', $d);
        $this->fill($s, 'Chân trụ chỉ được nhấc lên khi cầu thủ đã ___ bóng hoặc ném bóng đi.', [[0, 'chuyền']], 'Nhấc chân trụ trước khi chuyền hoặc ném là lỗi bước chạy.', $d);
    }

    private function seedTtLuatTheThaoLop81(): void
    {
        $s = 'tt-luat-the-thao-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Mỗi đội bóng chuyền trên sân có bao nhiêu cầu thủ?', ['6 người', '5 người', '11 người', '7 người'], 0, 'Bóng chuyền thi đấu với 6 cầu thủ mỗi đội trên sân.', $d);
        $this->quiz($s, 'Khi giành lại quyền phát bóng, các cầu thủ phải làm gì?', ['Xoay vòng vị trí theo chiều kim đồng hồ', 'Đứng yên vị trí cũ', 'Đổi hết sang sân đối phương', 'Ngồi nghỉ'], 0, 'Giành lại quyền phát bóng thì cả đội xoay vòng vị trí theo chiều kim đồng hồ.', $d);
        $this->quiz($s, 'Một đội được chạm bóng tối đa mấy lần trước khi đưa bóng qua lưới?', ['3 lần', '5 lần', '1 lần', 'Không giới hạn'], 0, 'Mỗi đội chỉ được chạm bóng tối đa 3 lần trước khi đưa bóng qua lưới.', $d);
        $this->quiz($s, 'Lỗi "dính bóng" trong bóng chuyền là gì?', ['Giữ bóng quá lâu trên tay khi chạm bóng', 'Đánh bóng quá mạnh', 'Chạm lưới khi phát bóng', 'Đứng sai vị trí'], 0, 'Dính bóng là lỗi giữ bóng, không đánh bóng đi ngay khi chạm bóng.', $d);
        $this->quiz($s, 'Cầu thủ hàng sau có được nhảy chắn bóng trên lưới không?', ['Không, chỉ cầu thủ hàng trước được chắn', 'Được thoải mái', 'Được nếu nhảy thấp', 'Được khi đội đang thua'], 0, 'Chỉ cầu thủ hàng trước được tham gia chắn bóng trên lưới.', $d);
        $this->matching($s, 'Nối mỗi vị trí với nhiệm vụ.', [['Chuyền hai', 'Tổ chức tấn công'], ['Chủ công', 'Đập bóng ghi điểm'], ['Libero', 'Chuyên phòng thủ, mặc áo khác màu'], ['Phụ công', 'Chắn bóng, đánh nhanh']], 'Mỗi vị trí trong đội hình bóng chuyền có vai trò riêng.', $d);
        $this->matching($s, 'Nối mỗi lỗi với mô tả.', [['Chạm lưới', 'Chạm vào lưới khi đánh bóng'], ['Dính bóng', 'Giữ bóng quá lâu'], ['Quá 3 lần chạm', 'Chạm bóng lần thứ 4 mới qua lưới'], ['Sang sân đối phương', 'Chân qua vạch giữa sân']], 'Các lỗi phổ biến khiến đội mất điểm trong bóng chuyền.', $d);
        $this->matching($s, 'Nối mỗi tình huống với kết quả.', [['Phát bóng ra ngoài', 'Mất điểm, đối phương phát bóng'], ['Bóng chạm lưới rồi qua', 'Được tiếp tục'], ['Đập bóng chạm tay chắn ra ngoài', 'Được điểm'], ['Phát bóng giẫm vạch', 'Phạm lỗi phát bóng']], 'Hiểu luật giúp xử lý đúng các tình huống trên sân.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa.', [['Luân chuyển', 'Xoay vòng vị trí khi giành quyền phát bóng'], ['Chắn bóng', 'Nhảy chặn cú đập của đối phương'], ['Phát bóng', 'Đưa bóng vào cuộc'], ['Ăn điểm trực tiếp', 'Ghi điểm từ quả phát bóng']], 'Các khái niệm cơ bản trong thi đấu bóng chuyền.', $d);
        $this->matching($s, 'Nối mỗi hàng với thành phần.', [['Hàng trước', '3 người gần lưới'], ['Hàng sau', '3 người gần vạch cuối sân'], ['Libero', 'Chỉ chơi ở hàng sau'], ['Đội trưởng', 'Đại diện đội làm việc với trọng tài']], 'Đội hình 6 người chia thành hàng trước và hàng sau.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Hợp lệ / Phạm lỗi.', [['Chạm bóng 3 lần rồi đưa qua lưới', 'Hợp lệ'], ['Giữ bóng lâu trên tay', 'Phạm lỗi'], ['Nhảy chắn bóng ở hàng trước', 'Hợp lệ'], ['Chạm lưới khi đập bóng', 'Phạm lỗi'], ['Phát bóng từ sau vạch cuối sân', 'Hợp lệ']], 'Chạm bóng tối đa 3 lần, không dính bóng, không chạm lưới là yêu cầu cơ bản.', $d);
        $this->sortQ($s, 'Xếp các vị trí vào nhóm: Hàng trước / Hàng sau.', [['Vị trí số 2', 'Trước'], ['Vị trí số 4', 'Trước'], ['Vị trí số 6', 'Sau'], ['Vị trí số 1', 'Sau'], ['Vị trí số 3', 'Trước']], 'Vị trí 2, 3, 4 thuộc hàng trước; vị trí 1, 5, 6 thuộc hàng sau.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được điểm / Mất điểm.', [['Đập bóng rơi vào sân đối phương', 'Được'], ['Phát bóng ra ngoài sân', 'Mất'], ['Đối phương chạm lưới khi chắn', 'Được'], ['Đội mình chạm bóng 4 lần', 'Mất']], 'Bóng chuyền tính điểm mỗi pha bóng, bên phạm lỗi mất điểm.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về bóng chuyền.', [['Mỗi đội có 6 người trên sân', 'Đúng'], ['Được chạm bóng 4 lần trước khi đưa qua lưới', 'Sai'], ['Libero mặc áo khác màu', 'Đúng'], ['Cầu thủ hàng sau được nhảy chắn bóng', 'Sai']], 'Nắm vững đội hình và các lỗi cơ bản giúp thi đấu đúng luật.', $d);
        $this->sortQ($s, 'Xếp các chuỗi chạm bóng vào nhóm: Đúng luật / Sai luật.', [['Đỡ - chuyền - đập', 'Đúng'], ['Đập ngay từ lần chạm đầu tiên', 'Đúng'], ['Chạm 4 lần mới đưa qua lưới', 'Sai'], ['Một người chạm 2 lần liên tiếp', 'Sai']], 'Tối đa 3 lần chạm và một người không được chạm 2 lần liên tiếp.', $d);
        $this->fill($s, 'Mỗi đội bóng chuyền có ___ cầu thủ trên sân.', [[0, '6']], 'Bóng chuyền thi đấu 6 đấu 6 trên sân.', $d);
        $this->fill($s, 'Một đội chỉ được chạm bóng tối đa ___ lần trước khi đưa bóng qua lưới.', [[0, '3']], 'Quá 3 lần chạm bóng là phạm lỗi.', $d);
        $this->fill($s, 'Cầu thủ mặc áo khác màu, chuyên phòng thủ hàng sau gọi là ___.', [[0, 'libero']], 'Libero là cầu thủ chuyên phòng thủ, chỉ chơi ở hàng sau.', $d);
        $this->fill($s, 'Khi giành lại quyền phát bóng, cả đội phải xoay vòng vị trí theo chiều kim ___ hồ.', [[0, 'đồng']], 'Luân chuyển vị trí theo chiều kim đồng hồ khi giành quyền phát bóng.', $d);
        $this->fill($s, 'Chạm tay vào lưới khi đang đánh bóng là lỗi chạm ___.', [[0, 'lưới']], 'Chạm lưới trong lúc bóng còn trong cuộc là phạm lỗi.', $d);
    }

    private function seedTtLuatTheThaoLop82(): void
    {
        $s = 'tt-luat-the-thao-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Một ván cầu lông thắng khi đạt bao nhiêu điểm?', ['21 điểm', '15 điểm', '25 điểm', '11 điểm'], 0, 'Ván cầu lông kết thúc khi một bên đạt 21 điểm.', $d);
        $this->quiz($s, 'Khi tỉ số hòa 20-20, phải thắng cách biệt mấy điểm mới thắng ván?', ['2 điểm', '1 điểm', '3 điểm', 'Không cần cách biệt'], 0, 'Ở tỉ số 20-20, bên nào dẫn trước 2 điểm thì thắng ván.', $d);
        $this->quiz($s, 'Quả giao cầu hợp lệ phải như thế nào?', ['Đánh cầu từ dưới thắt lưng, bay chéo sang ô đối diện', 'Đánh mạnh từ trên cao', 'Đánh thẳng sang ô bên cạnh', 'Đánh cầu chạm lưới'], 0, 'Giao cầu phải đánh từ dưới thắt lưng và bay chéo sang ô giao cầu đối diện.', $d);
        $this->quiz($s, 'Điểm số tối đa của một ván cầu lông là bao nhiêu?', ['30 điểm', '40 điểm', '21 điểm', '25 điểm'], 0, 'Ván đấu kết thúc ở 21 điểm, nhưng nếu hòa 29-29 thì ai ghi điểm 30 trước sẽ thắng.', $d);
        $this->quiz($s, 'Trận cầu lông thắng khi thắng mấy ván?', ['2 ván', '1 ván', '3 ván', '5 ván'], 0, 'Trận đấu gồm tối đa 3 ván, ai thắng 2 ván trước thì thắng trận.', $d);
        $this->matching($s, 'Nối mỗi tỉ số với kết quả ván đấu.', [['21-15', 'Thắng ván'], ['20-20', 'Phải đánh tiếp'], ['29-29', 'Ai ghi điểm 30 thắng'], ['21-20', 'Chưa thắng, cần cách biệt 2']], 'Thắng ván cần 21 điểm và cách biệt tối thiểu 2 điểm.', $d);
        $this->matching($s, 'Nối mỗi tình huống giao cầu với đánh giá.', [['Đánh cầu từ dưới thắt lưng', 'Hợp lệ'], ['Giẫm vạch khi giao cầu', 'Phạm lỗi'], ['Đánh cầu bay chéo ô', 'Hợp lệ'], ['Đánh cầu không qua lưới', 'Phạm lỗi']], 'Giao cầu phải đúng kỹ thuật: chân chạm đất, vợt dưới thắt lưng, cầu qua lưới vào ô chéo.', $d);
        $this->matching($s, 'Nối mỗi điểm số với ô giao cầu.', [['Điểm chẵn', 'Giao từ ô bên phải'], ['Điểm lẻ', 'Giao từ ô bên trái'], ['Bắt đầu ván (0-0)', 'Giao từ ô bên phải'], ['Sau khi thắng điểm', 'Đổi ô theo điểm số']], 'Vị trí giao cầu phụ thuộc vào điểm số chẵn hay lẻ.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa.', [['Giao cầu', 'Phát cầu bắt đầu pha bóng'], ['Đập cầu', 'Đánh mạnh từ trên cao'], ['Bỏ nhỏ', 'Đánh nhẹ sát lưới'], ['Phòng thủ', 'Đỡ các cú đánh của đối phương']], 'Các kỹ thuật cơ bản trong cầu lông.', $d);
        $this->matching($s, 'Nối mỗi lỗi với hậu quả.', [['Cầu rơi ngoài sân', 'Mất điểm'], ['Chạm lưới khi đánh', 'Mất điểm'], ['Giao cầu sai ô', 'Mất điểm'], ['Cầu chạm người', 'Mất điểm']], 'Mọi lỗi trong pha bóng đều khiến bên phạm lỗi mất điểm.', $d);
        $this->sortQ($s, 'Xếp các tỉ số vào nhóm: Thắng ván / Chưa thắng ván.', [['21-18', 'Thắng'], ['21-20', 'Chưa'], ['30-29', 'Thắng'], ['20-19', 'Chưa']], 'Thắng ván cần 21 điểm với cách biệt 2 điểm, tối đa 30 điểm.', $d);
        $this->sortQ($s, 'Xếp các quả giao cầu vào nhóm: Hợp lệ / Phạm lỗi.', [['Đánh từ dưới thắt lưng, bay chéo ô', 'Hợp lệ'], ['Giẫm lên vạch giới hạn', 'Phạm lỗi'], ['Cầu bay qua lưới vào đúng ô', 'Hợp lệ'], ['Chân không chạm đất khi giao', 'Phạm lỗi']], 'Giao cầu hợp lệ: chân chạm đất, không giẫm vạch, vợt dưới thắt lưng.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được điểm / Mất điểm.', [['Cầu rơi vào sân đối phương', 'Được'], ['Đánh cầu ra ngoài', 'Mất'], ['Đối phương đánh cầu chạm lưới rơi sân họ', 'Được'], ['Cầu chạm người mình rơi xuống', 'Mất']], 'Bên nào khiến cầu rơi vào sân đối phương hoặc đối phương phạm lỗi thì được điểm.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về cầu lông.', [['Ván thắng ở 21 điểm', 'Đúng'], ['Thắng cách biệt 1 điểm là đủ', 'Sai'], ['Điểm 30 là điểm tối đa', 'Đúng'], ['Trận đấu thắng khi thắng 2 ván', 'Đúng']], 'Luật tính điểm cầu lông: 21 điểm, cách biệt 2, tối đa 30, thắng 2/3 ván.', $d);
        $this->sortQ($s, 'Xếp các kỹ thuật vào nhóm: Tấn công / Phòng thủ.', [['Đập cầu', 'Tấn công'], ['Bỏ nhỏ', 'Tấn công'], ['Đỡ cầu cao sâu', 'Phòng thủ'], ['Phông cầu cuối sân', 'Phòng thủ']], 'Cầu lông gồm các kỹ thuật tấn công ghi điểm và phòng thủ hóa giải.', $d);
        $this->fill($s, 'Một ván cầu lông kết thúc khi một bên đạt ___ điểm.', [[0, '21']], 'Ván đấu kết thúc ở 21 điểm.', $d);
        $this->fill($s, 'Khi tỉ số hòa 20-20, bên nào dẫn trước ___ điểm thì thắng ván.', [[0, '2']], 'Cần cách biệt tối thiểu 2 điểm để thắng ván.', $d);
        $this->fill($s, 'Điểm số tối đa trong một ván cầu lông là ___ điểm.', [[0, '30']], 'Nếu hòa 29-29, ai ghi điểm 30 trước sẽ thắng ván.', $d);
        $this->fill($s, 'Quả giao cầu phải được đánh từ dưới thắt ___ của người giao.', [[0, 'lưng']], 'Vợt phải tiếp xúc cầu dưới thắt lưng khi giao cầu.', $d);
        $this->fill($s, 'Trận cầu lông gồm 3 ván, ai thắng ___ ván trước sẽ thắng trận.', [[0, '2']], 'Thắng 2 trên 3 ván là thắng trận.', $d);
    }

    private function seedTtSucKhoeVeSinhLop81(): void
    {
        $s = 'tt-suc-khoe-ve-sinh-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Chất nào cung cấp năng lượng chính cho cơ bắp khi vận động?', ['Chất bột đường (carbohydrate)', 'Vitamin C', 'Chất xơ', 'Nước'], 0, 'Chất bột đường là nguồn năng lượng chính cho cơ bắp khi vận động.', $d);
        $this->quiz($s, 'Chất đạm (protein) có vai trò gì với người tập luyện?', ['Xây dựng và phục hồi cơ bắp', 'Cung cấp nước', 'Làm xương dài nhanh', 'Không có vai trò'], 0, 'Chất đạm giúp xây dựng, phục hồi và phát triển cơ bắp sau khi tập.', $d);
        $this->quiz($s, 'Trước buổi tập 1-2 giờ nên ăn gì?', ['Bữa ăn nhẹ giàu tinh bột, dễ tiêu', 'Ăn thật no đồ chiên rán', 'Nhịn ăn hoàn toàn', 'Uống nước ngọt'], 0, 'Trước khi tập nên ăn nhẹ, dễ tiêu để có năng lượng mà không bị nặng bụng.', $d);
        $this->quiz($s, 'Sau khi tập, nên bổ sung gì trong 30-60 phút?', ['Đạm và tinh bột để phục hồi cơ', 'Chỉ uống nước ngọt', 'Không ăn gì', 'Ăn kẹo ngọt'], 0, 'Sau tập 30-60 phút là thời điểm vàng bổ sung đạm và tinh bột giúp cơ phục hồi.', $d);
        $this->quiz($s, 'Vitamin D giúp ích gì cho người vận động?', ['Giúp xương chắc khỏe', 'Tăng chiều cao ngay lập tức', 'Giảm cân nhanh', 'Tăng sức nhanh'], 0, 'Vitamin D giúp cơ thể hấp thu canxi, cho xương chắc khỏe.', $d);
        $this->matching($s, 'Nối mỗi chất dinh dưỡng với vai trò.', [['Chất bột đường', 'Năng lượng chính'], ['Chất đạm', 'Xây dựng cơ bắp'], ['Chất béo tốt', 'Dự trữ năng lượng'], ['Canxi', 'Xương chắc khỏe']], 'Mỗi nhóm chất dinh dưỡng có vai trò riêng với người vận động.', $d);
        $this->matching($s, 'Nối mỗi thực phẩm với nhóm chất chính.', [['Cơm, bánh mì', 'Tinh bột'], ['Thịt, trứng', 'Đạm'], ['Rau, trái cây', 'Vitamin, chất xơ'], ['Sữa', 'Canxi, đạm']], 'Ăn đa dạng thực phẩm giúp đủ các nhóm chất dinh dưỡng.', $d);
        $this->matching($s, 'Nối mỗi thời điểm với cách ăn phù hợp.', [['Trước tập 1-2 giờ', 'Ăn nhẹ, dễ tiêu'], ['Trong khi tập', 'Uống nước từng ngụm'], ['Sau tập 1 giờ', 'Ăn đủ đạm và tinh bột'], ['Buổi tối', 'Ăn vừa phải, không quá no']], 'Ăn uống đúng thời điểm giúp tập luyện hiệu quả và phục hồi tốt.', $d);
        $this->matching($s, 'Nối mỗi thói quen ăn uống với đánh giá.', [['Ăn đủ 3 bữa', 'Tốt'], ['Bỏ bữa sáng', 'Xấu'], ['Ăn nhiều rau xanh', 'Tốt'], ['Uống nước ngọt thay nước lọc', 'Xấu']], 'Thói quen ăn uống tốt là nền tảng sức khỏe cho người tập luyện.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với nguyên nhân dinh dưỡng.', [['Mệt nhanh khi tập', 'Thiếu năng lượng'], ['Chuột rút thường xuyên', 'Thiếu nước, khoáng'], ['Lâu phục hồi sau tập', 'Thiếu đạm'], ['Hay ốm vặt', 'Thiếu vitamin']], 'Dấu hiệu cơ thể phản ánh tình trạng dinh dưỡng.', $d);
        $this->sortQ($s, 'Xếp các thực phẩm vào nhóm: Nên ăn trước khi tập / Không nên ăn trước khi tập.', [['Chuối', 'Nên'], ['Bánh mì', 'Nên'], ['Đồ chiên nhiều dầu mỡ', 'Không nên'], ['Nước ngọt có ga', 'Không nên'], ['Cháo loãng', 'Nên']], 'Trước khi tập nên ăn nhẹ, dễ tiêu; tránh đồ nhiều dầu mỡ và nước ngọt có ga.', $d);
        $this->sortQ($s, 'Xếp các chất vào nhóm: Cung cấp năng lượng / Không cung cấp năng lượng.', [['Tinh bột', 'Có'], ['Chất béo', 'Có'], ['Vitamin', 'Không'], ['Nước', 'Không'], ['Chất đạm', 'Có']], 'Tinh bột, chất béo và đạm sinh năng lượng; vitamin, khoáng chất và nước thì không.', $d);
        $this->sortQ($s, 'Xếp các thói quen vào nhóm: Tốt / Xấu cho người tập luyện.', [['Uống đủ nước', 'Tốt'], ['Ăn đủ bữa', 'Tốt'], ['Nhịn ăn để giảm cân nhanh', 'Xấu'], ['Ăn khuya quá no', 'Xấu']], 'Ăn đủ bữa và uống đủ nước giúp tập luyện hiệu quả.', $d);
        $this->sortQ($s, 'Xếp các loại đồ uống vào nhóm: Phù hợp khi tập / Không phù hợp.', [['Nước lọc', 'Phù hợp'], ['Nước điện giải', 'Phù hợp'], ['Nước ngọt có ga', 'Không'], ['Rượu bia', 'Không']], 'Nước lọc và nước điện giải phù hợp khi tập; tránh nước ngọt có ga và rượu bia.', $d);
        $this->sortQ($s, 'Xếp các bữa ăn vào nhóm: Đúng thời điểm / Sai thời điểm.', [['Ăn nhẹ trước tập 1 giờ', 'Đúng'], ['Ăn no căng ngay trước giờ tập', 'Sai'], ['Ăn phục hồi sau tập 1 giờ', 'Đúng'], ['Nhịn đói cả ngày rồi tập nặng', 'Sai']], 'Ăn nhẹ trước tập và ăn phục hồi sau tập là đúng thời điểm.', $d);
        $this->fill($s, 'Chất bột đường là nguồn năng ___ chính cho cơ bắp khi vận động.', [[0, 'lượng']], 'Chất bột đường cung cấp năng lượng chính cho cơ bắp.', $d);
        $this->fill($s, 'Chất đạm giúp xây dựng và phục ___ cơ bắp sau khi tập.', [[0, 'hồi']], 'Bổ sung đạm sau tập giúp cơ bắp phục hồi và phát triển.', $d);
        $this->fill($s, 'Người tập luyện nên uống đủ nước trước, trong và ___ khi tập.', [[0, 'sau']], 'Bù nước đầy đủ ở mọi giai đoạn của buổi tập.', $d);
        $this->fill($s, 'Không nên ăn quá no ngay trước giờ tập vì dễ bị đau ___ và khó chịu.', [[0, 'bụng']], 'Ăn no ngay trước khi tập gây nặng bụng, khó vận động.', $d);
        $this->fill($s, 'Rau xanh và trái cây cung cấp vitamin và chất ___ tốt cho tiêu hóa.', [[0, 'xơ']], 'Chất xơ giúp tiêu hóa tốt và phòng táo bón.', $d);
    }

    private function seedTtSucKhoeVeSinhLop82(): void
    {
        $s = 'tt-suc-khoe-ve-sinh-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Dụng cụ tập dùng chung (thảm, bóng) cần được xử lý thế nào?', ['Lau chùi, vệ sinh thường xuyên', 'Không cần vệ sinh', 'Để bẩn cũng được', 'Chỉ vệ sinh khi hỏng'], 0, 'Dụng cụ dùng chung cần lau chùi thường xuyên để tránh lây bệnh ngoài da.', $d);
        $this->quiz($s, 'Vì sao không nên dùng chung bình nước khi tập?', ['Dễ lây bệnh qua đường hô hấp, tiêu hóa', 'Uống chung vui hơn', 'Không có lý do', 'Tiết kiệm nước'], 0, 'Dùng chung bình nước dễ lây các bệnh qua đường hô hấp và tiêu hóa.', $d);
        $this->quiz($s, 'Sàn phòng tập bị ướt mồ hôi cần làm gì?', ['Lau khô ngay để tránh trơn trượt', 'Để tự khô', 'Đổ thêm nước', 'Không quan tâm'], 0, 'Sàn ướt rất trơn, phải lau khô ngay để tránh té ngã.', $d);
        $this->quiz($s, 'Khăn tập nên được giặt với tần suất nào?', ['Sau mỗi buổi tập', 'Mỗi tháng một lần', 'Khi có mùi mới giặt', 'Không cần giặt'], 0, 'Khăn tập thấm mồ hôi cần giặt sau mỗi buổi để tránh vi khuẩn và mùi hôi.', $d);
        $this->quiz($s, 'Khi tập ở bể bơi công cộng, cần lưu ý vệ sinh gì?', ['Tắm tráng trước khi xuống bể', 'Nhảy xuống ngay', 'Ăn uống trong bể', 'Khạc nhổ trong bể'], 0, 'Tắm tráng trước khi xuống bể giữ nước bể sạch cho mọi người.', $d);
        $this->matching($s, 'Nối mỗi khu vực với cách giữ vệ sinh.', [['Sân tập', 'Nhặt rác sau buổi tập'], ['Phòng thay đồ', 'Đi dép, giữ khô ráo'], ['Dụng cụ chung', 'Lau chùi sau khi dùng'], ['Thùng rác', 'Bỏ rác đúng loại']], 'Giữ vệ sinh chung là trách nhiệm của mọi người trong buổi tập.', $d);
        $this->matching($s, 'Nối mỗi vật dụng với tần suất vệ sinh.', [['Khăn mặt', 'Giặt sau mỗi buổi tập'], ['Bình nước', 'Rửa hằng ngày'], ['Giày tập', 'Phơi khô, vệ sinh hằng tuần'], ['Thảm tập chung', 'Lau sau mỗi lần dùng']], 'Mỗi vật dụng có tần suất vệ sinh phù hợp để luôn sạch sẽ.', $d);
        $this->matching($s, 'Nối mỗi thói quen với đánh giá.', [['Rửa tay trước khi ăn sau buổi tập', 'Tốt'], ['Dùng chung khăn với bạn', 'Xấu'], ['Lau mồ hôi bằng khăn riêng', 'Tốt'], ['Vứt rác bừa bãi trên sân', 'Xấu']], 'Thói quen vệ sinh tốt bảo vệ sức khỏe bản thân và mọi người.', $d);
        $this->matching($s, 'Nối mỗi nơi với nguy cơ vệ sinh.', [['Sàn phòng thay đồ ẩm ướt', 'Nấm chân'], ['Bình nước bẩn', 'Bệnh đường ruột'], ['Khăn ẩm để lâu', 'Vi khuẩn, mùi hôi'], ['Dụng cụ chung không lau', 'Lây bệnh ngoài da']], 'Môi trường ẩm ướt, bẩn là nơi vi khuẩn dễ sinh sôi.', $d);
        $this->matching($s, 'Nối mỗi việc làm với thời điểm.', [['Tắm tráng', 'Trước khi xuống bể bơi'], ['Lau khô người', 'Sau khi tập xong'], ['Giặt khăn', 'Sau buổi tập'], ['Lau dụng cụ', 'Sau khi sử dụng']], 'Vệ sinh đúng thời điểm giúp buổi tập sạch sẽ, an toàn.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Giữ vệ sinh chung / Gây mất vệ sinh.', [['Nhặt rác trên sân sau buổi tập', 'Giữ'], ['Nhổ nước bọt bừa bãi', 'Mất'], ['Lau dụng cụ sau khi dùng', 'Giữ'], ['Vứt vỏ chai lung tung', 'Mất']], 'Giữ vệ sinh chung thể hiện ý thức và tôn trọng mọi người.', $d);
        $this->sortQ($s, 'Xếp các vật dụng vào nhóm: Dùng riêng / Có thể dùng chung nếu vệ sinh.', [['Khăn mặt', 'Riêng'], ['Bình nước', 'Riêng'], ['Bóng tập', 'Chung được'], ['Thảm tập', 'Chung được']], 'Đồ tiếp xúc trực tiếp cơ thể nên dùng riêng; dụng cụ tập có thể dùng chung nếu lau chùi.', $d);
        $this->sortQ($s, 'Xếp các thói quen vào nhóm: Nên làm / Không nên làm ở bể bơi.', [['Tắm tráng trước khi xuống bể', 'Nên'], ['Đi vệ sinh đúng nơi quy định', 'Nên'], ['Khạc nhổ trong bể', 'Không nên'], ['Ăn uống trong bể bơi', 'Không nên']], 'Bể bơi công cộng cần mọi người giữ vệ sinh để nước luôn sạch.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Đúng / Sai về vệ sinh dụng cụ.', [['Lau tay cầm dụng cụ sau khi dùng', 'Đúng'], ['Dùng thảm chung mà không lau', 'Sai'], ['Phơi khô dụng cụ sau khi lau', 'Đúng'], ['Để dụng cụ ướt trong túi kín', 'Sai']], 'Dụng cụ sạch, khô ráo giúp phòng bệnh và dùng bền hơn.', $d);
        $this->sortQ($s, 'Xếp các biểu hiện vào nhóm: Dụng cụ sạch / Dụng cụ cần vệ sinh.', [['Khô ráo, không mùi', 'Sạch'], ['Ẩm ướt, có mùi hôi', 'Cần vệ sinh'], ['Bề mặt láng, không vết bẩn', 'Sạch'], ['Dính mồ hôi, bụi bẩn', 'Cần vệ sinh']], 'Quan sát tình trạng dụng cụ để vệ sinh kịp thời.', $d);
        $this->fill($s, 'Dụng cụ tập dùng chung cần được lau ___ sau mỗi lần sử dụng.', [[0, 'chùi']], 'Lau chùi dụng cụ chung sau mỗi lần dùng để tránh lây bệnh.', $d);
        $this->fill($s, 'Không nên dùng chung khăn mặt và bình ___ với người khác.', [[0, 'nước']], 'Khăn mặt và bình nước là đồ dùng cá nhân, không dùng chung.', $d);
        $this->fill($s, 'Khăn tập bị ướt mồ hôi cần được giặt sạch và phơi ___.', [[0, 'khô']], 'Khăn khô ráo, sạch sẽ tránh vi khuẩn và mùi hôi.', $d);
        $this->fill($s, 'Sàn phòng tập ướt dễ gây trơn trượt nên cần lau khô ___.', [[0, 'ngay']], 'Lau khô sàn ướt ngay để tránh té ngã.', $d);
        $this->fill($s, 'Trước khi xuống bể bơi công cộng, cần tắm ___ sạch sẽ.', [[0, 'tráng']], 'Tắm tráng giúp giữ vệ sinh chung cho bể bơi.', $d);
    }

    private function seedTtSucKhoeVeSinhLop91(): void
    {
        $s = 'tt-suc-khoe-ve-sinh-lop-9-1'; $d = 'trung_binh';
        $this->quiz($s, 'Khi vận động, tim đập nhanh hơn để làm gì?', ['Bơm máu mang oxy đến cơ bắp nhiều hơn', 'Làm cơ thể mệt nhanh', 'Không có tác dụng', 'Gây hại cho tim'], 0, 'Tim đập nhanh hơn để bơm máu giàu oxy đến cơ bắp đang hoạt động.', $d);
        $this->quiz($s, 'Nhịp tim khi nghỉ ngơi của người khỏe mạnh khoảng bao nhiêu?', ['60-100 lần/phút', '200 lần/phút', '20 lần/phút', '150 lần/phút'], 0, 'Nhịp tim lúc nghỉ của người khỏe mạnh thường 60-100 lần/phút.', $d);
        $this->quiz($s, 'Vì sao khi chạy nhanh ta thở gấp hơn?', ['Cơ thể cần nhiều oxy hơn', 'Phổi bị hỏng', 'Thở gấp giúp chạy nhanh hơn', 'Không có lý do'], 0, 'Vận động mạnh cần nhiều oxy nên nhịp thở tăng lên.', $d);
        $this->quiz($s, 'Cách thở đúng khi chạy bền là gì?', ['Hít bằng mũi, thở ra bằng miệng theo nhịp', 'Nín thở càng lâu càng tốt', 'Thở gấp há miệng to', 'Thở thật nhanh liên tục'], 0, 'Hít bằng mũi, thở ra bằng miệng theo nhịp bước giúp đủ oxy và đỡ mệt.', $d);
        $this->quiz($s, 'Sau khi ngừng vận động, nhịp tim sẽ thế nào?', ['Giảm dần về mức bình thường', 'Tăng mãi không giảm', 'Ngừng đập', 'Không thay đổi'], 0, 'Sau khi dừng vận động, nhịp tim giảm dần về mức lúc nghỉ.', $d);
        $this->matching($s, 'Nối mỗi cơ quan với vai trò khi vận động.', [['Tim', 'Bơm máu đi nuôi cơ thể'], ['Phổi', 'Trao đổi oxy và khí cacbonic'], ['Cơ bắp', 'Co giãn tạo vận động'], ['Máu', 'Vận chuyển oxy, dinh dưỡng']], 'Tim, phổi, máu và cơ bắp phối hợp khi vận động.', $d);
        $this->matching($s, 'Nối mỗi cường độ với biểu hiện.', [['Nhẹ', 'Thở đều, nói chuyện được'], ['Vừa', 'Thở nhanh hơn, hơi mệt'], ['Nặng', 'Thở gấp, khó nói'], ['Quá sức', 'Chóng mặt, buồn nôn']], 'Biểu hiện cơ thể phản ánh cường độ vận động.', $d);
        $this->matching($s, 'Nối mỗi việc làm với tác dụng lên tim mạch.', [['Tập thể dục đều đặn', 'Tim khỏe hơn'], ['Hút thuốc lá', 'Hại tim, phổi'], ['Ngủ đủ giấc', 'Tim được nghỉ ngơi'], ['Ăn nhiều mỡ', 'Tăng nguy cơ bệnh tim']], 'Lối sống ảnh hưởng trực tiếp đến sức khỏe tim mạch.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với cách xử lý.', [['Tim đập nhanh khi tập', 'Bình thường'], ['Đau ngực khi vận động', 'Dừng ngay, báo người lớn'], ['Hơi thở gấp', 'Giảm cường độ'], ['Khó thở dữ dội', 'Dừng tập, cần giúp đỡ']], 'Đau ngực, khó thở dữ dội là tín hiệu nguy hiểm phải dừng ngay.', $d);
        $this->matching($s, 'Nối mỗi bài tập với hệ cơ quan được rèn chính.', [['Chạy bền', 'Tim mạch, hô hấp'], ['Bơi lội', 'Tim mạch, toàn thân'], ['Hít thở sâu', 'Hô hấp'], ['Đi bộ nhanh', 'Tim mạch']], 'Các bài tập sức bền rèn hệ tim mạch và hô hấp.', $d);
        $this->sortQ($s, 'Xếp các hoạt động vào nhóm: Tốt cho tim mạch / Hại tim mạch.', [['Chạy bộ đều đặn', 'Tốt'], ['Hút thuốc lá', 'Hại'], ['Bơi lội', 'Tốt'], ['Thức khuya thường xuyên', 'Hại'], ['Đạp xe', 'Tốt']], 'Vận động đều đặn tốt cho tim; thuốc lá và thức khuya hại tim mạch.', $d);
        $this->sortQ($s, 'Xếp các cách thở vào nhóm: Đúng / Sai khi chạy bền.', [['Hít mũi, thở miệng theo nhịp', 'Đúng'], ['Nín thở khi chạy', 'Sai'], ['Thở đều, không gấp', 'Đúng'], ['Há miệng thở hổn hển', 'Sai']], 'Thở đúng nhịp giúp cung cấp đủ oxy khi chạy bền.', $d);
        $this->sortQ($s, 'Xếp các biểu hiện vào nhóm: Bình thường khi tập / Cần dừng tập.', [['Tim đập nhanh hơn', 'Bình thường'], ['Thở hơi gấp', 'Bình thường'], ['Đau tức ngực', 'Dừng'], ['Chóng mặt, hoa mắt', 'Dừng'], ['Ra mồ hôi', 'Bình thường']], 'Tim đập nhanh, ra mồ hôi là bình thường; đau ngực, chóng mặt phải dừng ngay.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Giúp tim khỏe / Làm tim yếu đi.', [['Tập thể dục 30 phút mỗi ngày', 'Khỏe'], ['Ăn nhiều rau xanh', 'Khỏe'], ['Uống rượu bia', 'Yếu'], ['Ngồi lì cả ngày', 'Yếu']], 'Vận động và ăn uống lành mạnh giúp tim khỏe mạnh.', $d);
        $this->sortQ($s, 'Xếp các giai đoạn vào nhóm: Nhịp tim tăng / Nhịp tim giảm.', [['Bắt đầu chạy nhanh', 'Tăng'], ['Đi bộ thả lỏng sau tập', 'Giảm'], ['Lên dốc khi đạp xe', 'Tăng'], ['Ngồi nghỉ sau buổi tập', 'Giảm']], 'Nhịp tim tăng khi gắng sức và giảm dần khi nghỉ ngơi.', $d);
        $this->fill($s, 'Khi vận động, tim đập nhanh hơn để đưa nhiều oxy đến cơ ___.', [[0, 'bắp']], 'Máu mang oxy đến cơ bắp đang hoạt động mạnh.', $d);
        $this->fill($s, 'Phổi có nhiệm vụ lấy khí oxy và thải khí ___ ra ngoài.', [[0, 'cacbonic']], 'Phổi trao đổi khí: nhận oxy, thải khí cacbonic.', $d);
        $this->fill($s, 'Khi chạy bền nên hít vào bằng mũi và thở ra bằng ___.', [[0, 'miệng']], 'Hít mũi, thở miệng theo nhịp là cách thở đúng khi chạy bền.', $d);
        $this->fill($s, 'Tập thể dục đều đặn giúp tim mạch khỏe ___ hơn.', [[0, 'mạnh']], 'Vận động đều đặn giúp tim khỏe mạnh.', $d);
        $this->fill($s, 'Nếu thấy đau ___ khi đang vận động, phải dừng lại ngay.', [[0, 'ngực']], 'Đau ngực khi vận động là tín hiệu nguy hiểm.', $d);
    }

    private function seedTtSucKhoeVeSinhLop92(): void
    {
        $s = 'tt-suc-khoe-ve-sinh-lop-9-2'; $d = 'kho';
        $this->quiz($s, 'Vì sao vận động viên bị cấm sử dụng doping?', ['Gian lận, gây hại sức khỏe nghiêm trọng', 'Doping giúp khỏe hơn nên nên dùng', 'Không có lý do', 'Doping rẻ tiền'], 0, 'Doping là gian lận và gây hại nghiêm trọng cho sức khỏe.', $d);
        $this->quiz($s, 'Hút thuốc lá ảnh hưởng thế nào đến người chơi thể thao?', ['Giảm sức bền, hại phổi và tim', 'Giúp phổi khỏe hơn', 'Không ảnh hưởng', 'Tăng sức mạnh'], 0, 'Thuốc lá làm hại phổi, tim và giảm sức bền rõ rệt.', $d);
        $this->quiz($s, 'Uống rượu bia trước khi thi đấu gây hậu quả gì?', ['Giảm phản xạ, mất thăng bằng, nguy hiểm', 'Tăng sự tỉnh táo', 'Giúp thi đấu tốt hơn', 'Không ảnh hưởng'], 0, 'Rượu bia làm giảm phản xạ, mất thăng bằng, rất nguy hiểm khi thi đấu.', $d);
        $this->quiz($s, 'Lạm dụng nước tăng lực chứa chất kích thích có thể gây gì?', ['Tim đập nhanh bất thường, mất ngủ', 'Không có hại', 'Tăng chiều cao', 'Tốt cho sức khỏe'], 0, 'Chất kích thích trong nước tăng lực dùng nhiều gây tim đập nhanh, mất ngủ, hại sức khỏe.', $d);
        $this->quiz($s, 'Hành vi đúng khi bị rủ thử chất kích thích là gì?', ['Từ chối dứt khoát và báo người lớn', 'Thử một lần cho biết', 'Giữ bí mật giúp bạn', 'Thử ít thì không sao'], 0, 'Phải từ chối dứt khoát và báo thầy cô, cha mẹ để được giúp đỡ.', $d);
        $this->matching($s, 'Nối mỗi chất với tác hại.', [['Thuốc lá', 'Hại phổi, giảm sức bền'], ['Rượu bia', 'Giảm phản xạ, hại gan'], ['Doping', 'Gian lận, hại sức khỏe'], ['Ma túy', 'Hủy hoại sức khỏe, phạm pháp']], 'Mọi chất kích thích đều gây hại cho sức khỏe và thể thao.', $d);
        $this->matching($s, 'Nối mỗi hành vi với đánh giá.', [['Từ chối chất kích thích', 'Đúng'], ['Thử cho biết', 'Sai'], ['Báo người lớn khi bị rủ rê', 'Đúng'], ['Che giấu cho bạn dùng', 'Sai']], 'Từ chối và báo người lớn là cách xử lý đúng khi bị rủ rê.', $d);
        $this->matching($s, 'Nối mỗi biểu hiện với nguyên nhân có thể.', [['Thở gấp khi vận động nhẹ', 'Hút thuốc lá'], ['Phản xạ chậm', 'Uống rượu bia'], ['Tim đập loạn', 'Lạm dụng chất kích thích'], ['Mệt mỏi kéo dài', 'Thiếu ngủ, sinh hoạt xấu']], 'Chất kích thích để lại dấu hiệu rõ trên cơ thể.', $d);
        $this->matching($s, 'Nối mỗi quy định với ý nghĩa.', [['Cấm doping', 'Thi đấu công bằng'], ['Kiểm tra doping', 'Phát hiện gian lận'], ['Cấm quảng cáo thuốc lá', 'Bảo vệ giới trẻ'], ['Xử phạt vận động viên vi phạm', 'Răn đe, giữ sạch thể thao']], 'Các quy định nhằm giữ thể thao công bằng và trong sạch.', $d);
        $this->matching($s, 'Nối mỗi lời rủ rê với cách đáp lại.', [['Thử một hơi cho biết', 'Không, mình không thử'], ['Uống một ly không sao', 'Mình không uống rượu bia'], ['Ai cũng dùng mà', 'Mình không làm theo'], ['Giữ bí mật giúp tớ', 'Mình sẽ báo thầy cô để giúp bạn']], 'Cần có câu trả lời dứt khoát trước mọi lời rủ rê.', $d);
        $this->sortQ($s, 'Xếp các chất vào nhóm: Chất kích thích bị cấm / Không phải chất kích thích.', [['Thuốc lá', 'Cấm'], ['Ma túy', 'Cấm'], ['Nước lọc', 'Không'], ['Doping', 'Cấm'], ['Sữa tươi', 'Không']], 'Thuốc lá, ma túy, doping đều là chất kích thích bị cấm.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Đúng / Sai khi bị rủ dùng chất kích thích.', [['Từ chối dứt khoát', 'Đúng'], ['Thử một lần', 'Sai'], ['Báo thầy cô, cha mẹ', 'Đúng'], ['Rủ thêm bạn khác thử', 'Sai']], 'Từ chối và báo người lớn là hành vi đúng đắn.', $d);
        $this->sortQ($s, 'Xếp các tác hại vào nhóm: Ngắn hạn / Dài hạn của chất kích thích.', [['Say, mất kiểm soát', 'Ngắn hạn'], ['Nghiện, khó bỏ', 'Dài hạn'], ['Phản xạ chậm', 'Ngắn hạn'], ['Hỏng gan, phổi', 'Dài hạn']], 'Chất kích thích gây hại cả trước mắt và lâu dài.', $d);
        $this->sortQ($s, 'Xếp các quan niệm vào nhóm: Đúng / Sai.', [['Doping là gian lận trong thể thao', 'Đúng'], ['Thử một lần không sao', 'Sai'], ['Thuốc lá làm giảm sức bền', 'Đúng'], ['Rượu bia giúp thi đấu tốt hơn', 'Sai']], 'Không có chuyện thử một lần không sao với chất kích thích.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Bảo vệ bản thân / Đẩy mình vào nguy hiểm.', [['Tránh xa nơi có chất kích thích', 'Bảo vệ'], ['Kết bạn với người lành mạnh', 'Bảo vệ'], ['Tò mò thử chất lạ', 'Nguy hiểm'], ['Nghe lời rủ rê của kẻ xấu', 'Nguy hiểm']], 'Chủ động tránh xa và chọn bạn lành mạnh là cách tự bảo vệ.', $d);
        $this->fill($s, 'Doping là chất kích thích bị ___ trong thi đấu thể thao.', [[0, 'cấm']], 'Doping bị cấm tuyệt đối trong thi đấu thể thao.', $d);
        $this->fill($s, 'Hút thuốc lá làm hại phổi và giảm sức ___ của người tập luyện.', [[0, 'bền']], 'Thuốc lá làm giảm sức bền rõ rệt.', $d);
        $this->fill($s, 'Khi bị rủ thử chất kích thích, cần từ chối dứt khoát và báo người ___.', [[0, 'lớn']], 'Báo người lớn để được bảo vệ và giúp đỡ kịp thời.', $d);
        $this->fill($s, 'Uống rượu bia làm giảm phản xạ và khả năng giữ thăng ___.', [[0, 'bằng']], 'Rượu bia gây mất thăng bằng, rất nguy hiểm khi vận động.', $d);
        $this->fill($s, 'Thể thao chân chính đề cao sự trung thực và nói không với gian ___.', [[0, 'lận']], 'Gian lận bằng doping đi ngược tinh thần thể thao.', $d);
    }

    private function seedGdtcThpt10Lop101(): void
    {
        $s = 'gdtc-thpt-10-lop-10-1'; $d = 'trung_binh';
        $this->quiz($s, 'Kích thước sân bóng đá 11 người tiêu chuẩn quốc tế là bao nhiêu?', ['105m x 68m', '90m x 45m', '120m x 90m', '100m x 50m'], 0, 'Sân tiêu chuẩn quốc tế thường dùng kích thước 105m x 68m.', $d);
        $this->quiz($s, 'Vòng cấm địa (vùng 16,5m) có kích thước thế nào?', ['Rộng 40,3m, sâu 16,5m', 'Rộng 18,3m, sâu 5,5m', 'Rộng 68m, sâu 20m', 'Rộng 30m, sâu 10m'], 0, 'Vòng cấm địa rộng 40,32m và sâu 16,5m tính từ vạch cuối sân.', $d);
        $this->quiz($s, 'Khung thành bóng đá có kích thước chuẩn là?', ['Rộng 7,32m, cao 2,44m', 'Rộng 5m, cao 2m', 'Rộng 7m, cao 3m', 'Rộng 6m, cao 2,5m'], 0, 'Khung thành chuẩn rộng 7,32m, cao 2,44m.', $d);
        $this->quiz($s, 'Vòng tròn giữa sân có bán kính bao nhiêu?', ['9,15m', '11m', '5,5m', '16,5m'], 0, 'Vòng tròn giữa sân có bán kính 9,15m.', $d);
        $this->quiz($s, 'Cột cờ góc cao tối thiểu bao nhiêu?', ['1,5m', '3m', '1m', '2m'], 0, 'Cột cờ góc cao tối thiểu 1,5m.', $d);
        $this->matching($s, 'Nối mỗi khu vực với kích thước.', [['Chấm phạt đền', 'Cách khung thành 11m'], ['Vùng cầu môn (5,5m)', 'Sâu 5,5m'], ['Cung phạt đền', 'Bán kính 9,15m'], ['Vòng tròn giữa sân', 'Bán kính 9,15m']], 'Các khu vực trên sân có kích thước quy định chặt chẽ.', $d);
        $this->matching($s, 'Nối mỗi vạch với ý nghĩa.', [['Vạch giữa sân', 'Chia đôi sân'], ['Vạch cuối sân', 'Giới hạn chiều dài'], ['Vạch biên dọc', 'Giới hạn chiều rộng'], ['Vạch vôi khung thành', 'Xác định bàn thắng']], 'Hệ thống vạch kẻ xác định ranh giới sân và các khu vực.', $d);
        $this->matching($s, 'Nối mỗi vị trí đặt bóng với tình huống.', [['Chấm 11m', 'Phạt đền'], ['Góc sân', 'Phạt góc'], ['Vòng tròn giữa sân', 'Giao bóng'], ['Điểm phạm lỗi', 'Đá phạt trực tiếp']], 'Mỗi quả đá phạt có vị trí đặt bóng riêng.', $d);
        $this->matching($s, 'Nối mỗi loại sân với đặc điểm.', [['Sân cỏ tự nhiên', 'Mặt sân truyền thống'], ['Sân cỏ nhân tạo', 'Ít tốn công chăm sóc'], ['Sân futsal', 'Sân nhỏ, 5 người mỗi đội'], ['Sân đất nện', 'Phổ biến ở trường học']], 'Mặt sân ảnh hưởng đến lối chơi và an toàn của cầu thủ.', $d);
        $this->matching($s, 'Nối mỗi thiết bị với vị trí.', [['Khung thành', 'Giữa vạch cuối sân'], ['Cột cờ góc', '4 góc sân'], ['Khu vực kỹ thuật', 'Ngoài đường biên dọc'], ['Bảng tỉ số', 'Ngoài sân, dễ quan sát']], 'Thiết bị sân bãi được bố trí đúng vị trí quy định.', $d);
        $this->sortQ($s, 'Xếp các kích thước vào nhóm: Đúng chuẩn / Không đúng chuẩn.', [['Sân 105m x 68m', 'Đúng'], ['Khung thành 7,32m x 2,44m', 'Đúng'], ['Chấm phạt đền cách 5m', 'Không đúng'], ['Vòng tròn giữa sân bán kính 9,15m', 'Đúng'], ['Cột cờ góc cao 50cm', 'Không đúng']], 'Kích thước chuẩn: sân 105x68m, khung thành 7,32x2,44m, chấm 11m, vòng tròn 9,15m.', $d);
        $this->sortQ($s, 'Xếp các khu vực vào nhóm: Gần khung thành / Xa khung thành.', [['Vùng cầu môn 5,5m', 'Gần'], ['Chấm phạt đền', 'Gần'], ['Vòng tròn giữa sân', 'Xa'], ['Góc sân', 'Xa']], 'Vùng cầu môn và chấm phạt đền nằm sát khung thành.', $d);
        $this->sortQ($s, 'Xếp các loại mặt sân vào nhóm: Thường dùng thi đấu chuyên nghiệp / Thường dùng phong trào.', [['Cỏ tự nhiên', 'Chuyên nghiệp'], ['Cỏ nhân tạo đạt chuẩn', 'Chuyên nghiệp'], ['Sân đất', 'Phong trào'], ['Sân bê tông', 'Phong trào']], 'Thi đấu chuyên nghiệp dùng cỏ tự nhiên hoặc cỏ nhân tạo đạt chuẩn.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về sân bóng đá.', [['Sân phải hình chữ nhật', 'Đúng'], ['Khung thành đặt ở giữa sân', 'Sai'], ['Có 4 cột cờ góc', 'Đúng'], ['Vạch vôi rộng 20cm', 'Sai']], 'Sân hình chữ nhật, khung thành ở giữa vạch cuối sân, có 4 cột cờ góc.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Diễn ra trong vòng cấm / Ngoài vòng cấm.', [['Thủ môn được dùng tay', 'Trong'], ['Đá phạt đền', 'Trong'], ['Giao bóng giữa sân', 'Ngoài'], ['Ném biên', 'Ngoài']], 'Trong vòng cấm, thủ môn được dùng tay và có quả phạt đền.', $d);
        $this->fill($s, 'Sân bóng đá 11 người tiêu chuẩn dài 105m, rộng ___m.', [[0, '68']], 'Sân tiêu chuẩn quốc tế 105m x 68m.', $d);
        $this->fill($s, 'Khung thành rộng 7,32m và cao 2,___m.', [[0, '44']], 'Khung thành chuẩn 7,32m x 2,44m.', $d);
        $this->fill($s, 'Chấm phạt đền cách khung thành ___m.', [[0, '11']], 'Điểm đá phạt đền cách khung thành 11m.', $d);
        $this->fill($s, 'Vòng tròn giữa sân có bán kính 9,___m.', [[0, '15']], 'Vòng tròn giữa sân bán kính 9,15m.', $d);
        $this->fill($s, 'Sân bóng đá phải có hình chữ ___.', [[0, 'nhật']], 'Sân bóng đá có hình chữ nhật.', $d);
    }

    private function seedGdtcThpt10Lop102(): void
    {
        $s = 'gdtc-thpt-10-lop-10-2'; $d = 'kho';
        $this->quiz($s, 'Điều kiện để bị phạt việt vị KHÔNG bao gồm yếu tố nào?', ['Cầu thủ đứng ở phần sân nhà', 'Tham gia tích cực vào pha bóng', 'Đứng gần khung thành đối phương hơn bóng', 'Đứng gần khung thành hơn hậu vệ cuối thứ hai'], 0, 'Đứng ở phần sân nhà thì không thể bị phạt việt vị.', $d);
        $this->quiz($s, 'Cầu thủ ở vị trí việt vị nhưng không tham gia pha bóng thì sao?', ['Không bị phạt', 'Vẫn bị phạt', 'Bị thẻ vàng', 'Bị truất quyền thi đấu'], 0, 'Chỉ bị phạt việt vị khi cầu thủ ở vị trí việt vị tham gia tích cực vào pha bóng.', $d);
        $this->quiz($s, 'Quả đá phạt gián tiếp được thực hiện khi nào?', ['Thủ môn bắt bóng từ đường chuyền về của đồng đội', 'Phạm lỗi kéo áo', 'Phạm lỗi đốn ngã đối phương', 'Dùng tay chơi bóng'], 0, 'Thủ môn bắt bóng từ đường chuyền về của đồng đội bị phạt đá phạt gián tiếp.', $d);
        $this->quiz($s, 'Khi đá phạt, hàng rào đối phương phải đứng cách bóng bao xa?', ['9,15m', '5m', '11m', '16,5m'], 0, 'Hàng rào phải đứng cách bóng tối thiểu 9,15m.', $d);
        $this->quiz($s, 'Bàn thắng từ quả đá phạt gián tiếp được công nhận khi?', ['Bóng chạm một cầu thủ khác trước khi vào lưới', 'Bóng bay thẳng vào lưới', 'Thủ môn không chạm được bóng', 'Trọng tài không thổi còi'], 0, 'Đá phạt gián tiếp phải chạm thêm một cầu thủ khác thì bàn thắng mới được công nhận.', $d);
        $this->matching($s, 'Nối mỗi tình huống việt vị với kết luận.', [['Nhận bóng sau hàng thủ đối phương', 'Việt vị'], ['Đứng ở phần sân nhà', 'Không việt vị'], ['Nhận bóng từ ném biên', 'Không việt vị'], ['Hậu vệ dâng cao đánh lừa đối phương', 'Bẫy việt vị']], 'Việt vị phụ thuộc vào vị trí, sự tham gia pha bóng và tình huống đặc biệt.', $d);
        $this->matching($s, 'Nối mỗi quả phạt với đặc điểm.', [['Phạt trực tiếp', 'Được sút thẳng vào lưới'], ['Phạt gián tiếp', 'Phải chạm thêm người mới tính bàn'], ['Phạt đền', 'Đá từ chấm 11m'], ['Phạt góc', 'Đá từ góc sân']], 'Mỗi loại quả phạt có quy định ghi bàn khác nhau.', $d);
        $this->matching($s, 'Nối mỗi lỗi với hình phạt.', [['Việt vị', 'Phạt gián tiếp cho đối phương'], ['Phạm lỗi trong vòng cấm', 'Phạt đền'], ['Nhận 2 thẻ vàng', 'Thẻ đỏ rời sân'], ['Lỗi phản tinh thần thể thao', 'Thẻ vàng']], 'Mức phạt tương xứng với tính chất lỗi.', $d);
        $this->matching($s, 'Nối mỗi tín hiệu trọng tài với ý nghĩa khi đá phạt.', [['Giơ tay thẳng lên trời', 'Phạt gián tiếp'], ['Thổi còi', 'Cho phép thực hiện'], ['Chỉ tay về chấm 11m', 'Phạt đền'], ['Chỉ tay về góc sân', 'Phạt góc']], 'Trọng tài dùng cử chỉ để báo loại quả phạt.', $d);
        $this->matching($s, 'Nối mỗi vị trí cầu thủ với nhiệm vụ khi đội được đá phạt.', [['Người đá phạt', 'Thực hiện cú đá'], ['Đồng đội', 'Chờ bóng hoặc che chắn'], ['Thủ môn đội nhà', 'Đứng ở phần sân nhà'], ['Đối phương', 'Lập hàng rào cách 9,15m']], 'Khi đá phạt, mỗi bên có sự bố trí riêng.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Việt vị / Không việt vị.', [['Nhận bóng khi đứng sau hậu vệ cuối ở phần sân đối phương', 'Việt vị'], ['Đứng ở phần sân nhà khi đồng đội chuyền', 'Không'], ['Nhận bóng trực tiếp từ quả phát bóng của thủ môn', 'Không'], ['Đứng sau thủ môn khi đồng đội sút bóng', 'Việt vị'], ['Đứng ngang hàng hậu vệ cuối khi bóng được chuyền', 'Không']], 'Không việt vị khi ở sân nhà, ngang hàng hậu vệ cuối, hoặc nhận bóng từ ném biên, phạt góc, phát bóng.', $d);
        $this->sortQ($s, 'Xếp các quả đá vào nhóm: Trực tiếp / Gián tiếp.', [['Sút thẳng vào lưới được công nhận', 'Trực tiếp'], ['Cần chạm thêm người mới được tính', 'Gián tiếp'], ['Lỗi thủ môn bắt bóng chuyền về', 'Gián tiếp'], ['Lỗi kéo áo đối phương', 'Trực tiếp']], 'Trực tiếp được ghi bàn ngay; gián tiếp cần chạm thêm cầu thủ khác.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Bị thẻ vàng / Bị thẻ đỏ.', [['Phạm lỗi phản tinh thần thể thao', 'Vàng'], ['Nhận thẻ vàng thứ hai', 'Đỏ'], ['Cố tình dùng tay cản bàn thắng', 'Đỏ'], ['Câu giờ', 'Vàng'], ['Phạm lỗi nghiêm trọng', 'Đỏ']], 'Thẻ vàng cảnh cáo, thẻ đỏ truất quyền thi đấu.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về luật nâng cao.', [['Đứng ở phần sân nhà không thể việt vị', 'Đúng'], ['Mọi quả đá phạt đều được sút thẳng vào lưới', 'Sai'], ['Hàng rào cách bóng 9,15m', 'Đúng'], ['Nhận bóng từ ném biên vẫn bị phạt việt vị', 'Sai']], 'Luật việt vị và đá phạt có nhiều điểm tinh tế cần nắm vững.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được đá phạt đền / Không được đá phạt đền.', [['Bị đốn ngã trong vòng cấm', 'Được'], ['Bị phạm lỗi ở giữa sân', 'Không'], ['Đối phương chơi bóng bằng tay trong vòng cấm', 'Được'], ['Tự ngã không va chạm', 'Không']], 'Phạt đền chỉ khi bị phạm lỗi trong vòng cấm địa đối phương.', $d);
        $this->fill($s, 'Cầu thủ đứng ở phần sân ___ thì không thể bị phạt việt vị.', [[0, 'nhà']], 'Việt vị chỉ có thể xảy ra ở phần sân đối phương.', $d);
        $this->fill($s, 'Quả phạt gián tiếp chỉ được tính bàn thắng khi bóng đã chạm thêm một cầu thủ ___.', [[0, 'khác']], 'Đá phạt gián tiếp cần chạm thêm người mới được công nhận bàn thắng.', $d);
        $this->fill($s, 'Khi đá phạt, hàng rào đối phương phải đứng cách bóng 9,___m.', [[0, '15']], 'Khoảng cách tối thiểu của hàng rào là 9,15m.', $d);
        $this->fill($s, 'Thủ môn bắt bóng từ đường chuyền về của đồng đội sẽ bị phạt quả đá phạt gián ___.', [[0, 'tiếp']], 'Đây là một trong các lỗi dẫn đến đá phạt gián tiếp.', $d);
        $this->fill($s, 'Cầu thủ nhận đủ hai thẻ vàng sẽ phải nhận thẻ ___ và rời sân.', [[0, 'đỏ']], 'Hai thẻ vàng tương đương một thẻ đỏ.', $d);
    }

    private function seedGdtcThpt10Lop103(): void
    {
        $s = 'gdtc-thpt-10-lop-10-3'; $d = 'trung_binh';
        $this->quiz($s, 'Thủ môn có nhiệm vụ chính là gì?', ['Bảo vệ khung thành', 'Ghi nhiều bàn thắng', 'Chuyền bóng lên trên', 'Đá phạt góc'], 0, 'Nhiệm vụ chính của thủ môn là bảo vệ khung thành, cản phá các cú sút.', $d);
        $this->quiz($s, 'Tiền đạo cắm (số 9) thường hoạt động ở đâu?', ['Gần khung thành đối phương', 'Trước khung thành đội nhà', 'Dọc biên sân', 'Chỉ ở giữa sân'], 0, 'Tiền đạo cắm hoạt động gần khung thành đối phương để ghi bàn.', $d);
        $this->quiz($s, 'Hậu vệ cánh có nhiệm vụ gì?', ['Phòng ngự biên và hỗ trợ tấn công', 'Chỉ đứng trong vòng cấm', 'Không được qua giữa sân', 'Chỉ phát bóng'], 0, 'Hậu vệ cánh vừa phòng ngự hành lang biên vừa dâng cao hỗ trợ tấn công.', $d);
        $this->quiz($s, 'Tiền vệ trung tâm đóng vai trò gì?', ['Kết nối phòng ngự và tấn công', 'Chỉ phòng ngự', 'Chỉ ghi bàn', 'Đứng yên một chỗ'], 0, 'Tiền vệ trung tâm là cầu nối giữa các tuyến, điều tiết lối chơi.', $d);
        $this->quiz($s, 'Đội trưởng trên sân có quyền gì?', ['Đại diện đội làm việc với trọng tài', 'Thay trọng tài thổi còi', 'Tự ý đổi luật', 'Ra lệnh cho đối phương'], 0, 'Đội trưởng đại diện đội trao đổi với trọng tài và động viên đồng đội.', $d);
        $this->matching($s, 'Nối mỗi vị trí với khu vực hoạt động.', [['Thủ môn', 'Trước khung thành'], ['Trung vệ', 'Giữa hàng phòng ngự'], ['Tiền vệ', 'Khu vực giữa sân'], ['Tiền đạo', 'Gần khung thành đối phương']], 'Mỗi tuyến đảm nhận một khu vực riêng trên sân.', $d);
        $this->matching($s, 'Nối mỗi số áo với vị trí truyền thống.', [['Số 1', 'Thủ môn'], ['Số 4', 'Trung vệ'], ['Số 7', 'Tiền vệ cánh'], ['Số 9', 'Tiền đạo cắm']], 'Số áo truyền thống thường gắn với vị trí trên sân.', $d);
        $this->matching($s, 'Nối mỗi vị trí với nhiệm vụ chính.', [['Hậu vệ', 'Ngăn chặn tấn công đối phương'], ['Tiền vệ', 'Kiểm soát bóng, tổ chức lối chơi'], ['Tiền đạo', 'Ghi bàn'], ['Thủ môn', 'Cản phá cú sút']], 'Bốn tuyến có nhiệm vụ rõ ràng: thủ môn, hậu vệ, tiền vệ, tiền đạo.', $d);
        $this->matching($s, 'Nối mỗi vị trí với kỹ năng cần thiết.', [['Thủ môn', 'Phản xạ, bắt bóng'], ['Hậu vệ', 'Tranh chấp, kèm người'], ['Tiền vệ', 'Chuyền bóng, quan sát'], ['Tiền đạo', 'Dứt điểm, chọn vị trí']], 'Mỗi vị trí đòi hỏi bộ kỹ năng đặc thù.', $d);
        $this->matching($s, 'Nối mỗi vị trí với ví dụ nhiệm vụ cụ thể.', [['Hậu vệ cánh', 'Tạt bóng từ biên'], ['Tiền vệ phòng ngự', 'Đánh chặn trước hàng thủ'], ['Tiền đạo cánh', 'Đột phá biên'], ['Trung phong', 'Không chiến trong vòng cấm']], 'Trong mỗi tuyến còn có vị trí chuyên biệt với nhiệm vụ cụ thể.', $d);
        $this->sortQ($s, 'Xếp các vị trí vào nhóm: Phòng ngự / Tấn công.', [['Thủ môn', 'Phòng ngự'], ['Trung vệ', 'Phòng ngự'], ['Tiền đạo cắm', 'Tấn công'], ['Tiền vệ cánh', 'Tấn công'], ['Hậu vệ cánh', 'Phòng ngự']], 'Thủ môn và hậu vệ thuộc tuyến phòng ngự; tiền đạo thuộc tuyến tấn công.', $d);
        $this->sortQ($s, 'Xếp các nhiệm vụ vào nhóm: Của thủ môn / Của cầu thủ khác.', [['Dùng tay trong vòng cấm', 'Thủ môn'], ['Phát bóng lên', 'Thủ môn'], ['Đá phạt góc', 'Khác'], ['Kèm tiền đạo đối phương', 'Khác']], 'Chỉ thủ môn được dùng tay trong vòng cấm và thực hiện phát bóng.', $d);
        $this->sortQ($s, 'Xếp các số áo vào nhóm: Thường là hậu vệ / Thường là tiền đạo.', [['Số 2, 3', 'Hậu vệ'], ['Số 4, 5', 'Hậu vệ'], ['Số 9', 'Tiền đạo'], ['Số 10', 'Tiền đạo']], 'Số áo nhỏ thường là hậu vệ, số 9, 10, 11 thường là tiền đạo.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về vị trí.', [['Thủ môn được dùng tay trong vòng cấm', 'Đúng'], ['Tiền đạo có nhiệm vụ chính là phòng ngự', 'Sai'], ['Tiền vệ kết nối các tuyến', 'Đúng'], ['Hậu vệ không được tham gia tấn công', 'Sai']], 'Mỗi vị trí có nhiệm vụ chính nhưng đều có thể hỗ trợ các tuyến khác.', $d);
        $this->sortQ($s, 'Xếp các vị trí vào nhóm: Chơi gần khung thành đội nhà / Chơi gần khung thành đối phương.', [['Thủ môn', 'Nhà'], ['Trung vệ', 'Nhà'], ['Tiền đạo cắm', 'Đối phương'], ['Tiền vệ tấn công', 'Đối phương']], 'Tuyến phòng ngự chơi gần sân nhà, tuyến tấn công chơi gần sân đối phương.', $d);
        $this->fill($s, 'Người bảo vệ khung thành được gọi là thủ ___.', [[0, 'môn']], 'Thủ môn là vị trí đặc biệt được dùng tay trong vòng cấm.', $d);
        $this->fill($s, 'Tiền đạo cắm thường mang áo số ___ theo truyền thống.', [[0, '9']], 'Số 9 truyền thống dành cho tiền đạo cắm.', $d);
        $this->fill($s, 'Tiền vệ là cầu nối giữa hàng phòng ngự và hàng tấn ___.', [[0, 'công']], 'Tiền vệ kết nối phòng ngự và tấn công.', $d);
        $this->fill($s, 'Hậu vệ cánh hoạt động chủ yếu ở hai bên ___ sân.', [[0, 'biên']], 'Hậu vệ cánh trấn giữ hành lang biên.', $d);
        $this->fill($s, 'Đội trưởng là người đại diện đội làm việc với trọng ___.', [[0, 'tài']], 'Đội trưởng trao đổi với trọng tài thay mặt cả đội.', $d);
    }

    private function seedGdtcThpt10Lop104(): void
    {
        $s = 'gdtc-thpt-10-lop-10-4'; $d = 'kho';
        $this->quiz($s, 'Sơ đồ 4-4-2 có nghĩa là gì?', ['4 hậu vệ, 4 tiền vệ, 2 tiền đạo', '4 thủ môn, 4 hậu vệ, 2 tiền đạo', '4 tiền đạo, 4 tiền vệ, 2 hậu vệ', '4 đội bóng thi đấu'], 0, 'Sơ đồ 4-4-2 gồm 4 hậu vệ, 4 tiền vệ và 2 tiền đạo.', $d);
        $this->quiz($s, 'Sơ đồ nào thiên về phòng ngự phản công?', ['4-5-1', '3-4-3', '4-3-3', '2-5-3'], 0, 'Sơ đồ 4-5-1 chắc chắn ở giữa sân, phù hợp phòng ngự phản công.', $d);
        $this->quiz($s, 'Sơ đồ 4-3-3 mạnh ở điểm nào?', ['Tấn công biên với 3 tiền đạo', 'Phòng ngự số đông', 'Chỉ đá giữa sân', 'Không cần thủ môn'], 0, 'Sơ đồ 4-3-3 với 3 tiền đạo mạnh trong tấn công, đặc biệt ở biên.', $d);
        $this->quiz($s, 'Khi đội đang dẫn bàn và muốn bảo vệ tỉ số, huấn luyện viên thường làm gì?', ['Tăng cầu thủ phòng ngự, đá chắc chắn', 'Rút hết hậu vệ ra', 'Cho thủ môn lên đá tiền đạo', 'Thay toàn bộ đội hình'], 0, 'Khi cần bảo vệ tỉ số, đội thường tăng cường phòng ngự và đá chắc chắn.', $d);
        $this->quiz($s, 'Chiến thuật "pressing" nghĩa là gì?', ['Gây áp lực ngay bên phần sân đối phương', 'Đứng yên chờ bóng', 'Chỉ phòng ngự trong vòng cấm', 'Đá bóng lên trời'], 0, 'Pressing là chiến thuật áp sát, gây áp lực ngay bên phần sân đối phương.', $d);
        $this->matching($s, 'Nối mỗi sơ đồ với đặc điểm.', [['4-4-2', 'Cân bằng công thủ'], ['4-3-3', 'Tấn công mạnh'], ['5-3-2', 'Phòng ngự số đông'], ['4-5-1', 'Chắc chắn giữa sân']], 'Mỗi sơ đồ có triết lý công thủ khác nhau.', $d);
        $this->matching($s, 'Nối mỗi chiến thuật với mô tả.', [['Phản công', 'Phòng ngự rồi tấn công nhanh'], ['Pressing', 'Áp sát gây áp lực'], ['Kiểm soát bóng', 'Giữ bóng, triển khai chậm'], ['Đá dài', 'Chuyền dài vượt tuyến']], 'Chiến thuật là cách đội triển khai lối chơi.', $d);
        $this->matching($s, 'Nối mỗi tình huống với điều chỉnh chiến thuật.', [['Đang dẫn bàn', 'Đá chắc, bảo vệ tỉ số'], ['Đang bị dẫn', 'Tăng tiền đạo, dồn ép'], ['Đối phương mạnh hơn', 'Phòng ngự phản công'], ['Cuối trận cần gỡ hòa', 'Dồn toàn lực tấn công']], 'Huấn luyện viên điều chỉnh chiến thuật theo diễn biến trận đấu.', $d);
        $this->matching($s, 'Nối mỗi vai trò với sơ đồ phù hợp.', [['Tiền đạo mục tiêu đơn độc', '4-5-1'], ['Hai tiền đạo', '4-4-2'], ['Ba tiền đạo', '4-3-3'], ['Hàng thủ 5 người', '5-3-2']], 'Sơ đồ được chọn phù hợp với con người và ý đồ chiến thuật.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa.', [['Đội hình', 'Cách bố trí cầu thủ trên sân'], ['Chiến thuật', 'Kế hoạch thi đấu'], ['Thay người', 'Điều chỉnh nhân sự, chiến thuật'], ['Sơ đồ', 'Sắp xếp vị trí các tuyến']], 'Đội hình, sơ đồ và chiến thuật tạo nên lối chơi của đội bóng.', $d);
        $this->sortQ($s, 'Xếp các sơ đồ vào nhóm: Thiên về tấn công / Thiên về phòng ngự.', [['4-3-3', 'Tấn công'], ['3-4-3', 'Tấn công'], ['5-3-2', 'Phòng ngự'], ['4-5-1', 'Phòng ngự']], 'Sơ đồ nhiều tiền đạo thiên về tấn công, nhiều hậu vệ thiên về phòng ngự.', $d);
        $this->sortQ($s, 'Xếp các chiến thuật vào nhóm: Tấn công / Phòng ngự.', [['Pressing tầm cao', 'Tấn công'], ['Phản công nhanh', 'Tấn công'], ['Phòng ngự số đông', 'Phòng ngự'], ['Lùi sâu đội hình', 'Phòng ngự'], ['Dồn ép đối phương', 'Tấn công']], 'Chiến thuật được phân loại theo ý đồ tấn công hay phòng ngự.', $d);
        $this->sortQ($s, 'Xếp các quyết định vào nhóm: Hợp lý / Không hợp lý khi đang dẫn 1-0 ở phút 85.', [['Thay thêm hậu vệ', 'Hợp lý'], ['Rút hậu vệ thêm tiền đạo', 'Không hợp lý'], ['Giữ bóng chắc chắn', 'Hợp lý'], ['Dâng cao toàn đội', 'Không hợp lý']], 'Khi cần bảo vệ tỉ số, ưu tiên chắc chắn thay vì mạo hiểm.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về chiến thuật.', [['Sơ đồ 4-4-2 có 4 hậu vệ, 4 tiền vệ, 2 tiền đạo', 'Đúng'], ['Pressing là đứng yên chờ đối phương', 'Sai'], ['Phản công cần tốc độ', 'Đúng'], ['Mọi trận đấu chỉ dùng một sơ đồ duy nhất', 'Sai']], 'Chiến thuật linh hoạt, thay đổi theo diễn biến trận đấu.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: Thuộc chiến thuật / Không thuộc chiến thuật.', [['Cách bố trí đội hình', 'Thuộc'], ['Lối chơi pressing', 'Thuộc'], ['Màu áo đấu', 'Không thuộc'], ['Kế hoạch thay người', 'Thuộc'], ['Thời tiết hôm nay', 'Không thuộc']], 'Chiến thuật gồm đội hình, lối chơi và kế hoạch điều chỉnh.', $d);
        $this->fill($s, 'Sơ đồ 4-4-2 gồm 4 hậu vệ, 4 tiền vệ và 2 tiền ___.', [[0, 'đạo']], 'Sơ đồ 4-4-2 là sơ đồ cân bằng công thủ kinh điển.', $d);
        $this->fill($s, 'Chiến thuật gây áp lực ngay bên phần sân đối phương gọi là ___.', [[0, 'pressing']], 'Pressing đòi hỏi thể lực và sự phối hợp đồng đội cao.', $d);
        $this->fill($s, 'Khi bị dẫn bàn, đội thường tăng cường tấn công để tìm bàn gỡ ___.', [[0, 'hòa']], 'Bàn gỡ hòa giúp đội lấy lại thế trận.', $d);
        $this->fill($s, 'Phòng ngự rồi tổ chức tấn công nhanh được gọi là phản ___.', [[0, 'công']], 'Phản công là vũ khí lợi hại trước đối thủ mạnh hơn.', $d);
        $this->fill($s, 'Huấn luyện viên là người xây dựng chiến thuật và chỉ đạo ___ đấu.', [[0, 'thi']], 'Huấn luyện viên quyết định sơ đồ, chiến thuật và thay người.', $d);
    }

    private function seedGdtcThpt11Lop111(): void
    {
        $s = 'gdtc-thpt-11-lop-11-1'; $d = 'trung_binh';
        $this->quiz($s, 'Kích thước sân bóng chuyền là bao nhiêu?', ['18m x 9m', '28m x 15m', '40m x 20m', '24m x 12m'], 0, 'Sân bóng chuyền tiêu chuẩn dài 18m, rộng 9m.', $d);
        $this->quiz($s, 'Chiều cao lưới bóng chuyền nam là bao nhiêu?', ['2,43m', '2,24m', '3,05m', '1,8m'], 0, 'Lưới bóng chuyền nam cao 2,43m, nữ cao 2,24m.', $d);
        $this->quiz($s, 'Bóng chuyền tính điểm theo thể thức nào?', ['Ghi điểm mỗi pha bóng', 'Chỉ ghi điểm khi phát bóng', 'Tính điểm theo hiệp', 'Ai phát bóng mới được điểm'], 0, 'Bóng chuyền dùng thể thức ghi điểm mỗi pha bóng (rally scoring).', $d);
        $this->quiz($s, 'Một ván bóng chuyền (trừ ván 5) thắng khi đạt bao nhiêu điểm?', ['25 điểm, cách biệt 2', '21 điểm', '15 điểm', '30 điểm'], 0, 'Ván đấu thường thắng ở 25 điểm với cách biệt tối thiểu 2 điểm.', $d);
        $this->quiz($s, 'Trận bóng chuyền thắng khi thắng mấy ván?', ['3 ván trong 5 ván', '2 ván trong 3 ván', '1 ván duy nhất', '4 ván'], 0, 'Trận đấu gồm tối đa 5 ván, ai thắng 3 ván trước thì thắng trận.', $d);
        $this->matching($s, 'Nối mỗi đối tượng với kích thước.', [['Sân bóng chuyền', '18m x 9m'], ['Lưới nam', 'Cao 2,43m'], ['Lưới nữ', 'Cao 2,24m'], ['Vạch tấn công', 'Cách lưới 3m']], 'Kích thước sân và lưới bóng chuyền được quy định chặt chẽ.', $d);
        $this->matching($s, 'Nối mỗi tỉ số với kết quả.', [['25-20', 'Thắng ván'], ['24-24', 'Đánh tiếp đến cách biệt 2'], ['25-24', 'Chưa thắng'], ['15-13 ở ván 5', 'Thắng trận']], 'Thắng ván cần cách biệt 2 điểm, không giới hạn điểm tối đa.', $d);
        $this->matching($s, 'Nối mỗi khu vực sân với mô tả.', [['Khu vực phát bóng', 'Sau vạch cuối sân'], ['Khu vực tấn công', 'Giữa lưới và vạch 3m'], ['Khu vực tự do', 'Bao quanh sân thi đấu'], ['Cột lưới', 'Dựng ngoài sân']], 'Sân bóng chuyền gồm sân thi đấu và khu vực tự do xung quanh.', $d);
        $this->matching($s, 'Nối mỗi tình huống với điểm số.', [['Bóng rơi vào sân đối phương', 'Được 1 điểm'], ['Phát bóng ra ngoài', 'Đối phương được 1 điểm'], ['Đối phương chạm lưới', 'Được 1 điểm'], ['Bóng chạm ăng-ten ra ngoài', 'Mất 1 điểm']], 'Mỗi pha bóng kết thúc đều có 1 điểm cho một bên.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa.', [['Rally scoring', 'Ghi điểm mỗi pha bóng'], ['Ván quyết thắng', 'Ván 5 đánh đến 15 điểm'], ['Ăng-ten', 'Cột giới hạn trên lưới'], ['Đổi sân', 'Hai đội đổi bên sau mỗi ván']], 'Thuật ngữ giúp hiểu cách tổ chức thi đấu bóng chuyền.', $d);
        $this->sortQ($s, 'Xếp các kích thước vào nhóm: Đúng / Sai.', [['Sân 18m x 9m', 'Đúng'], ['Lưới nam 2,43m', 'Đúng'], ['Lưới nữ 2,24m', 'Đúng'], ['Sân 28m x 15m', 'Sai']], 'Sân 18x9m, lưới nam 2,43m, lưới nữ 2,24m là chuẩn.', $d);
        $this->sortQ($s, 'Xếp các tỉ số ván 1-4 vào nhóm: Thắng ván / Chưa thắng.', [['25-23', 'Thắng'], ['25-24', 'Chưa'], ['26-24', 'Thắng'], ['24-22', 'Chưa']], 'Cần 25 điểm và cách biệt 2 điểm mới thắng ván.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được điểm / Mất điểm.', [['Đập bóng vào sân đối phương', 'Được'], ['Phát bóng chạm lưới ra ngoài', 'Mất'], ['Chắn bóng rơi vào sân đối phương', 'Được'], ['Đánh bóng ra ngoài sân', 'Mất']], 'Bên nào khiến bóng rơi vào sân đối phương hoặc đối phương phạm lỗi thì được điểm.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về sân và tính điểm.', [['Ván 5 đánh đến 15 điểm', 'Đúng'], ['Thắng 2 ván là thắng trận', 'Sai'], ['Mỗi pha bóng đều có điểm', 'Đúng'], ['Lưới nam nữ cao bằng nhau', 'Sai']], 'Trận thắng khi thắng 3/5 ván; ván 5 đánh đến 15 điểm.', $d);
        $this->sortQ($s, 'Xếp các vị trí vào nhóm: Trong sân / Ngoài sân.', [['Vạch 3m', 'Trong'], ['Khu vực phát bóng', 'Ngoài'], ['Cột ăng-ten', 'Ngoài'], ['Ô giao cầu', 'Trong']], 'Khu vực phát bóng nằm ngoài sân, sau vạch cuối sân.', $d);
        $this->fill($s, 'Sân bóng chuyền dài 18m và rộng ___m.', [[0, '9']], 'Sân bóng chuyền tiêu chuẩn 18m x 9m.', $d);
        $this->fill($s, 'Chiều cao lưới bóng chuyền nam là 2,___m.', [[0, '43']], 'Lưới nam cao 2,43m.', $d);
        $this->fill($s, 'Một ván đấu (trừ ván 5) kết thúc ở ___ điểm với cách biệt 2 điểm.', [[0, '25']], 'Ván thường thắng ở 25 điểm.', $d);
        $this->fill($s, 'Ván quyết thắng (ván 5) chỉ đánh đến ___ điểm.', [[0, '15']], 'Ván 5 đánh đến 15 điểm với cách biệt 2.', $d);
        $this->fill($s, 'Trận đấu bóng chuyền gồm 5 ván, đội thắng ___ ván trước sẽ thắng trận.', [[0, '3']], 'Thắng 3 trên 5 ván là thắng trận.', $d);
    }

    private function seedGdtcThpt11Lop112(): void
    {
        $s = 'gdtc-thpt-11-lop-11-2'; $d = 'trung_binh';
        $this->quiz($s, 'Khi nào đội bóng chuyền phải xoay vòng vị trí?', ['Khi giành lại quyền phát bóng từ đối phương', 'Sau mỗi điểm ghi được', 'Khi hết ván đấu', 'Không bao giờ phải xoay'], 0, 'Giành lại quyền phát bóng thì cả đội xoay vòng vị trí.', $d);
        $this->quiz($s, 'Thứ tự xoay vòng vị trí theo chiều nào?', ['Chiều kim đồng hồ', 'Ngược chiều kim đồng hồ', 'Tùy ý', 'Không xoay'], 0, 'Các cầu thủ xoay vòng vị trí theo chiều kim đồng hồ.', $d);
        $this->quiz($s, 'Lỗi vị trí (sai luân chuyển) xảy ra khi nào?', ['Cầu thủ đứng sai thứ tự khi đối phương phát bóng', 'Cầu thủ nhảy quá cao', 'Cầu thủ đập bóng mạnh', 'Cầu thủ chạy nhanh'], 0, 'Đứng sai thứ tự vị trí tại thời điểm đối phương phát bóng là lỗi vị trí.', $d);
        $this->quiz($s, 'Cầu thủ hàng sau có được hoàn thành cú đập bóng trên lưới không?', ['Không được', 'Được thoải mái', 'Được nếu nhảy từ hàng sau', 'Được khi không ai chắn'], 0, 'Cầu thủ hàng sau không được hoàn thành cú tấn công trên lưới từ khu vực trước.', $d);
        $this->quiz($s, 'Libero không được thực hiện hành động nào?', ['Chắn bóng và đập bóng hoàn thành trên lưới', 'Phát bóng', 'Đỡ bóng', 'Cứu bóng'], 0, 'Libero chuyên phòng thủ, không được chắn bóng và đập bóng ghi điểm trên lưới.', $d);
        $this->matching($s, 'Nối mỗi thời điểm với việc phải làm.', [['Giành quyền phát bóng', 'Xoay vòng vị trí'], ['Trước khi đối phương phát bóng', 'Đứng đúng thứ tự'], ['Hết mỗi ván', 'Đổi sân'], ['Bắt đầu ván quyết thắng', 'Bốc thăm chọn sân']], 'Luân chuyển và đổi sân đảm bảo công bằng cho hai đội.', $d);
        $this->matching($s, 'Nối mỗi lỗi với mô tả.', [['Sai vị trí', 'Đứng sai thứ tự khi phát bóng'], ['Chạm lưới', 'Chạm lưới khi đánh bóng'], ['Dính bóng', 'Giữ bóng quá lâu'], ['Hàng sau tấn công', 'Hoàn thành cú đập trên lưới từ khu trước']], 'Các lỗi về vị trí và kỹ thuật khiến đội mất điểm.', $d);
        $this->matching($s, 'Nối mỗi vị trí số với hàng.', [['Vị trí 2, 3, 4', 'Hàng trước'], ['Vị trí 1, 5, 6', 'Hàng sau'], ['Vị trí 1', 'Người phát bóng'], ['Vị trí 3', 'Giữa hàng trước']], 'Sáu vị trí được đánh số, chia thành hàng trước và hàng sau.', $d);
        $this->matching($s, 'Nối mỗi hành động của libero với đánh giá.', [['Đỡ bóng hàng sau', 'Được phép'], ['Chắn bóng trên lưới', 'Không được'], ['Phát bóng một vòng', 'Được phép'], ['Đập bóng hoàn thành trên lưới', 'Không được']], 'Libero chỉ chơi phòng thủ ở hàng sau.', $d);
        $this->matching($s, 'Nối mỗi tình huống với hình phạt.', [['Sai vị trí', 'Mất điểm'], ['Chạm bóng 4 lần', 'Mất điểm'], ['Phát bóng giẫm vạch', 'Mất điểm'], ['Bóng qua lưới hợp lệ', 'Được tiếp tục']], 'Mọi lỗi trong bóng chuyền đều khiến đội phạm lỗi mất điểm.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Hợp lệ / Phạm lỗi.', [['Xoay vòng khi giành quyền phát bóng', 'Hợp lệ'], ['Đứng sai thứ tự khi đối phương phát bóng', 'Phạm lỗi'], ['Hàng trước nhảy chắn bóng', 'Hợp lệ'], ['Hàng sau đập bóng hoàn thành trên lưới', 'Phạm lỗi']], 'Luân chuyển đúng và tôn trọng giới hạn hàng trước, hàng sau.', $d);
        $this->sortQ($s, 'Xếp các vị trí vào nhóm: Hàng trước / Hàng sau.', [['Vị trí 4', 'Trước'], ['Vị trí 2', 'Trước'], ['Vị trí 6', 'Sau'], ['Vị trí 1', 'Sau'], ['Vị trí 3', 'Trước']], 'Vị trí 2, 3, 4 là hàng trước; 1, 5, 6 là hàng sau.', $d);
        $this->sortQ($s, 'Xếp các việc của libero vào nhóm: Được làm / Không được làm.', [['Đỡ bóng', 'Được'], ['Chắn bóng', 'Không'], ['Phát bóng một vòng', 'Được'], ['Đập bóng ghi điểm trên lưới', 'Không']], 'Libero chuyên phòng thủ, không tham gia tấn công trên lưới.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về luân chuyển.', [['Xoay vòng theo chiều kim đồng hồ', 'Đúng'], ['Hàng sau được chắn bóng', 'Sai'], ['Sai vị trí bị mất điểm', 'Đúng'], ['Libero được đập bóng trên lưới', 'Sai']], 'Luân chuyển đúng chiều và đúng giới hạn vị trí là bắt buộc.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Phải xoay vòng / Không phải xoay.', [['Giành lại quyền phát bóng', 'Phải'], ['Ghi điểm khi đang phát bóng', 'Không'], ['Đối phương phát bóng hỏng', 'Phải'], ['Hết ván đấu', 'Không']], 'Chỉ xoay vòng khi giành lại quyền phát bóng từ đối phương.', $d);
        $this->fill($s, 'Khi giành lại quyền phát bóng, các cầu thủ phải xoay vòng theo chiều kim ___ hồ.', [[0, 'đồng']], 'Luân chuyển vị trí theo chiều kim đồng hồ.', $d);
        $this->fill($s, 'Đứng sai thứ tự vị trí khi đối phương phát bóng là lỗi sai vị ___.', [[0, 'trí']], 'Lỗi sai vị trí khiến đội mất điểm.', $d);
        $this->fill($s, 'Cầu thủ hàng sau không được hoàn thành cú đập bóng trên ___.', [[0, 'lưới']], 'Hàng sau chỉ được tấn công từ sau vạch 3m.', $d);
        $this->fill($s, 'Libero là cầu thủ chuyên phòng thủ và không được ___ bóng trên lưới.', [[0, 'chắn']], 'Libero không được chắn bóng.', $d);
        $this->fill($s, 'Mỗi đội bóng chuyền có 6 vị trí được đánh số từ 1 đến ___.', [[0, '6']], 'Sáu vị trí chia thành hàng trước và hàng sau.', $d);
    }

    private function seedGdtcThpt11Lop113(): void
    {
        $s = 'gdtc-thpt-11-lop-11-3'; $d = 'trung_binh';
        $this->quiz($s, 'Kích thước sân cầu lông đánh đơn là bao nhiêu?', ['13,4m x 5,18m', '13,4m x 6,1m', '18m x 9m', '12m x 6m'], 0, 'Sân cầu lông đánh đơn dài 13,4m, rộng 5,18m.', $d);
        $this->quiz($s, 'Kích thước sân cầu lông đánh đôi là bao nhiêu?', ['13,4m x 6,1m', '13,4m x 5,18m', '15m x 7m', '13,4m x 7m'], 0, 'Sân đánh đôi rộng hơn sân đánh đơn, dùng thêm hai biên dọc ngoài.', $d);
        $this->quiz($s, 'Chiều cao lưới cầu lông ở giữa sân là bao nhiêu?', ['1,524m', '1,55m', '2,43m', '1,8m'], 0, 'Lưới cao 1,55m ở cột và 1,524m ở giữa sân.', $d);
        $this->quiz($s, 'Khi giao cầu, chân người giao phải thế nào?', ['Chạm đất, không giẫm vạch', 'Được nhảy lên', 'Được giẫm vạch', 'Không cần chạm đất'], 0, 'Khi giao cầu, chân phải chạm đất và không được giẫm lên vạch giới hạn.', $d);
        $this->quiz($s, 'Giao cầu trong đánh đôi, cầu phải rơi vào ô nào?', ['Ô chéo sân trong giới hạn sân đôi', 'Ô bên cạnh', 'Bất kỳ đâu', 'Ngoài sân'], 0, 'Giao cầu phải bay chéo sang ô giao cầu đối diện, trong giới hạn sân đôi.', $d);
        $this->matching($s, 'Nối mỗi nội dung với kích thước sân.', [['Đánh đơn', '13,4m x 5,18m'], ['Đánh đôi', '13,4m x 6,1m'], ['Chiều cao lưới ở cột', '1,55m'], ['Chiều cao lưới ở giữa', '1,524m']], 'Sân đơn hẹp hơn sân đôi; lưới cao 1,55m ở cột.', $d);
        $this->matching($s, 'Nối mỗi điểm số với ô giao cầu.', [['0 điểm', 'Ô phải'], ['1 điểm', 'Ô trái'], ['2 điểm', 'Ô phải'], ['3 điểm', 'Ô trái']], 'Điểm chẵn giao từ ô phải, điểm lẻ giao từ ô trái.', $d);
        $this->matching($s, 'Nối mỗi lỗi giao cầu với mô tả.', [['Giẫm vạch', 'Chân chạm vạch giới hạn'], ['Đánh cầu quá cao', 'Vợt cao hơn thắt lưng'], ['Giao sai ô', 'Không đúng ô chéo'], ['Chân không chạm đất', 'Nhảy lên khi giao']], 'Giao cầu sai kỹ thuật bị mất điểm.', $d);
        $this->matching($s, 'Nối mỗi đường giới hạn với ý nghĩa.', [['Vạch cuối sân', 'Giới hạn chiều dài'], ['Vạch biên dọc đơn', 'Giới hạn chiều rộng đánh đơn'], ['Vạch biên dọc đôi', 'Giới hạn chiều rộng đánh đôi'], ['Vạch giao cầu ngắn', 'Cầu phải bay qua vạch này']], 'Hệ thống vạch kẻ xác định giới hạn sân đơn, sân đôi.', $d);
        $this->matching($s, 'Nối mỗi tình huống với kết quả.', [['Giao cầu đúng luật vào ô', 'Được tiếp tục'], ['Giao cầu ra ngoài', 'Mất điểm'], ['Cầu chạm lưới rồi rơi vào ô', 'Được tiếp tục'], ['Giẫm vạch khi giao', 'Mất điểm']], 'Giao cầu đúng luật thì pha bóng tiếp tục.', $d);
        $this->sortQ($s, 'Xếp các kích thước vào nhóm: Đúng / Sai.', [['Sân đơn 13,4m x 5,18m', 'Đúng'], ['Sân đôi 13,4m x 6,1m', 'Đúng'], ['Lưới giữa cao 1,524m', 'Đúng'], ['Sân đơn rộng 6,1m', 'Sai']], 'Sân đơn rộng 5,18m, sân đôi rộng 6,1m.', $d);
        $this->sortQ($s, 'Xếp các quả giao cầu vào nhóm: Hợp lệ / Phạm lỗi.', [['Chân chạm đất, cầu từ dưới thắt lưng', 'Hợp lệ'], ['Giẫm lên vạch', 'Phạm lỗi'], ['Cầu bay chéo vào đúng ô', 'Hợp lệ'], ['Vợt cao quá thắt lưng khi chạm cầu', 'Phạm lỗi']], 'Giao cầu hợp lệ: chân chạm đất, vợt dưới thắt lưng, cầu vào ô chéo.', $d);
        $this->sortQ($s, 'Xếp các điểm số vào nhóm: Giao từ ô phải / Giao từ ô trái.', [['0', 'Phải'], ['2', 'Phải'], ['1', 'Trái'], ['5', 'Trái']], 'Điểm chẵn giao ô phải, điểm lẻ giao ô trái.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về sân và giao cầu.', [['Điểm chẵn giao từ ô phải', 'Đúng'], ['Sân đôi hẹp hơn sân đơn', 'Sai'], ['Giao cầu phải đánh từ dưới thắt lưng', 'Đúng'], ['Được giẫm vạch khi giao cầu', 'Sai']], 'Sân đôi rộng hơn sân đơn; giao cầu có nhiều quy định chặt chẽ.', $d);
        $this->sortQ($s, 'Xếp các bộ phận vào nhóm: Trên sân / Ngoài sân.', [['Cột lưới', 'Ngoài'], ['Vạch biên', 'Trên'], ['Ghế trọng tài', 'Ngoài'], ['Ô giao cầu', 'Trên']], 'Cột lưới và ghế trọng tài đặt ngoài sân thi đấu.', $d);
        $this->fill($s, 'Sân cầu lông dài 13,4m; sân đánh đơn rộng 5,___m.', [[0, '18']], 'Sân đánh đơn rộng 5,18m.', $d);
        $this->fill($s, 'Chiều cao lưới cầu lông ở giữa sân là 1,___m.', [[0, '524']], 'Lưới cao 1,524m ở giữa sân.', $d);
        $this->fill($s, 'Khi giao cầu, vợt phải đánh cầu từ dưới thắt ___ người giao.', [[0, 'lưng']], 'Điểm tiếp xúc cầu phải dưới thắt lưng.', $d);
        $this->fill($s, 'Điểm số chẵn thì giao cầu từ ô bên ___.', [[0, 'phải']], 'Điểm chẵn giao từ ô bên phải.', $d);
        $this->fill($s, 'Sân đánh đôi rộng hơn sân đánh đơn vì dùng thêm hai đường biên ___.', [[0, 'dọc']], 'Sân đôi dùng biên dọc ngoài, sân đơn dùng biên dọc trong.', $d);
    }

    private function seedGdtcThpt11Lop114(): void
    {
        $s = 'gdtc-thpt-11-lop-11-4'; $d = 'kho';
        $this->quiz($s, 'Lỗi "chạm lưới" trong cầu lông xảy ra khi nào?', ['Người hoặc vợt chạm lưới khi cầu còn trong cuộc', 'Cầu chạm lưới khi bay qua', 'Lưới bị gió rung', 'Khán giả chạm lưới'], 0, 'Người hoặc vợt chạm lưới trong lúc cầu còn trong cuộc là phạm lỗi.', $d);
        $this->quiz($s, 'Đánh cầu khi cầu chưa qua sang phần sân mình bị phạt gì?', ['Mất điểm vì đánh cầu bên sân đối phương', 'Được điểm', 'Được đánh lại', 'Không sao'], 0, 'Không được thò vợt qua lưới đánh cầu khi cầu chưa qua sang sân mình.', $d);
        $this->quiz($s, 'Cầu thủ có được thò vợt qua lưới để đánh cầu không?', ['Không, trừ khi cầu đã qua lưới', 'Được thoải mái', 'Được nếu nhảy cao', 'Được khi trọng tài cho phép'], 0, 'Chỉ được đưa vợt qua lưới khi cầu đã bay qua sang phần sân mình.', $d);
        $this->quiz($s, 'Lỗi giao cầu "trì hoãn" nghĩa là gì?', ['Cố tình chậm trễ, làm mất thời gian', 'Giao cầu quá nhanh', 'Giao cầu quá mạnh', 'Giao cầu quá nhẹ'], 0, 'Cố tình trì hoãn trận đấu sẽ bị trọng tài cảnh cáo.', $d);
        $this->quiz($s, 'Khi cả hai bên cùng phạm lỗi một lúc, trọng tài xử lý thế nào?', ['Cho đánh lại pha bóng đó', 'Bên giao cầu được điểm', 'Bên đỡ được điểm', 'Hủy trận đấu'], 0, 'Khi không xác định được bên phạm lỗi, trọng tài cho đánh lại pha bóng.', $d);
        $this->matching($s, 'Nối mỗi lỗi với mô tả.', [['Chạm lưới', 'Người/vợt chạm lưới khi cầu trong cuộc'], ['Đánh cầu bên sân đối phương', 'Vợt qua lưới đánh cầu chưa qua'], ['Chạm người', 'Cầu chạm vào người'], ['Hai lần chạm', 'Vợt chạm cầu 2 lần liên tiếp']], 'Các lỗi đặc biệt trong pha đánh cầu.', $d);
        $this->matching($s, 'Nối mỗi hành vi với hình phạt.', [['Cố tình trì hoãn', 'Thẻ vàng cảnh cáo'], ['Xúc phạm trọng tài', 'Thẻ đỏ truất quyền'], ['Tái phạm nhiều lần', 'Xử thua trận'], ['Chơi đẹp', 'Được khen ngợi']], 'Hành vi phi thể thao bị xử phạt theo mức độ.', $d);
        $this->matching($s, 'Nối mỗi tình huống với kết quả.', [['Cầu chạm trần nhà thi đấu', 'Mất điểm'], ['Cầu mắc trên lưới', 'Đánh lại'], ['Vợt chạm cầu 2 lần', 'Mất điểm'], ['Cầu rơi đúng vạch', 'Được tính trong sân']], 'Cầu chạm vạch được tính trong sân; cầu mắc lưới thì đánh lại.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa.', [['Lỗi giao cầu', 'Vi phạm quy định khi phát cầu'], ['Lỗi đánh cầu', 'Vi phạm trong pha đánh'], ['Đánh lại', 'Thực hiện lại pha bóng'], ['Cảnh cáo', 'Nhắc nhở lần đầu']], 'Phân biệt các loại lỗi và cách xử lý.', $d);
        $this->matching($s, 'Nối mỗi vật dụng với quy định.', [['Vợt', 'Không được thò qua lưới đánh cầu'], ['Giày', 'Không để lại dấu đen trên sân'], ['Quần áo', 'Đúng quy định thi đấu'], ['Khăn', 'Lau mồ hôi khi nghỉ giữa ván']], 'Trang phục, dụng cụ thi đấu cũng có quy định riêng.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Phạm lỗi / Hợp lệ.', [['Vợt chạm lưới khi cầu đang trong cuộc', 'Phạm lỗi'], ['Thò vợt qua lưới đánh cầu chưa qua', 'Phạm lỗi'], ['Đánh cầu sau khi cầu đã qua lưới', 'Hợp lệ'], ['Cầu chạm vạch biên', 'Hợp lệ'], ['Vợt chạm cầu hai lần', 'Phạm lỗi']], 'Chạm lưới, thò vợt qua lưới sớm và chạm cầu 2 lần đều phạm lỗi.', $d);
        $this->sortQ($s, 'Xếp các tình huống vào nhóm: Được đánh lại / Mất điểm.', [['Cầu mắc kẹt trên đỉnh lưới', 'Đánh lại'], ['Trọng tài không xác định được bên lỗi', 'Đánh lại'], ['Đánh cầu ra ngoài sân', 'Mất điểm'], ['Chạm lưới khi cầu trong cuộc', 'Mất điểm']], 'Tình huống không phân định được thì đánh lại pha bóng.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Bị cảnh cáo / Bị truất quyền.', [['Cố tình trì hoãn trận đấu', 'Cảnh cáo'], ['Xúc phạm trọng tài', 'Truất quyền'], ['Ném vợt thể hiện thái độ', 'Cảnh cáo'], ['Hành hung đối thủ', 'Truất quyền']], 'Hành vi phi thể thao bị phạt từ cảnh cáo đến truất quyền.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về lỗi đặc biệt.', [['Được thò vợt qua lưới nếu cầu đã qua', 'Đúng'], ['Cầu chạm vạch được tính ngoài sân', 'Sai'], ['Chạm lưới khi cầu trong cuộc là lỗi', 'Đúng'], ['Được đánh cầu 2 lần liên tiếp', 'Sai']], 'Cầu chạm vạch tính trong sân; chạm lưới và chạm cầu 2 lần là lỗi.', $d);
        $this->sortQ($s, 'Xếp các lỗi vào nhóm: Lỗi khi giao cầu / Lỗi khi đánh cầu.', [['Giẫm vạch giao cầu', 'Giao cầu'], ['Đánh cầu quá cao khi giao', 'Giao cầu'], ['Chạm lưới khi đập cầu', 'Đánh cầu'], ['Vợt chạm cầu 2 lần', 'Đánh cầu']], 'Lỗi được phân loại theo giai đoạn giao cầu hay pha đánh.', $d);
        $this->fill($s, 'Người hoặc vợt chạm lưới khi cầu còn trong cuộc là lỗi chạm ___.', [[0, 'lưới']], 'Chạm lưới trong lúc cầu còn trong cuộc là phạm lỗi.', $d);
        $this->fill($s, 'Không được thò vợt qua lưới để đánh cầu khi cầu chưa ___ sang sân mình.', [[0, 'qua']], 'Chỉ được đưa vợt qua lưới khi cầu đã qua sang sân mình.', $d);
        $this->fill($s, 'Cầu rơi trúng vạch biên được tính là cầu trong ___.', [[0, 'sân']], 'Cầu chạm vạch được tính là trong sân.', $d);
        $this->fill($s, 'Cố tình làm chậm trận đấu sẽ bị trọng tài ___ cáo.', [[0, 'cảnh']], 'Trì hoãn trận đấu bị phạt cảnh cáo.', $d);
        $this->fill($s, 'Vợt chạm cầu hai lần liên tiếp trong một cú đánh là ___.', [[0, 'lỗi']], 'Chạm cầu 2 lần liên tiếp là phạm lỗi.', $d);
    }

    private function seedGdtcThpt12Lop121(): void
    {
        $s = 'gdtc-thpt-12-lop-12-1'; $d = 'trung_binh';
        $this->quiz($s, 'Nhóm chất nào cung cấp năng lượng chủ yếu cho cơ thể?', ['Chất bột đường và chất béo', 'Vitamin và khoáng chất', 'Nước và chất xơ', 'Chỉ chất đạm'], 0, 'Chất bột đường và chất béo là nguồn năng lượng chính của cơ thể.', $d);
        $this->quiz($s, 'Chất đạm có nhiều trong thực phẩm nào?', ['Thịt, cá, trứng, sữa, đậu', 'Rau muống, rau cải', 'Kẹo, bánh ngọt', 'Nước ngọt'], 0, 'Thịt, cá, trứng, sữa và các loại đậu giàu chất đạm.', $d);
        $this->quiz($s, 'Vitamin và khoáng chất có vai trò gì?', ['Điều hòa hoạt động cơ thể, tăng đề kháng', 'Cung cấp năng lượng chính', 'Xây dựng cơ bắp', 'Không có vai trò'], 0, 'Vitamin và khoáng chất điều hòa hoạt động cơ thể, tăng sức đề kháng.', $d);
        $this->quiz($s, 'Chất xơ có nhiều trong thực phẩm nào và có tác dụng gì?', ['Rau củ quả, giúp tiêu hóa tốt', 'Thịt mỡ, tăng cân', 'Đồ ngọt, cung cấp năng lượng', 'Muối, giữ nước'], 0, 'Chất xơ trong rau củ quả giúp tiêu hóa tốt.', $d);
        $this->quiz($s, 'Thiếu canxi lâu ngày dễ dẫn đến gì?', ['Xương yếu, loãng xương', 'Tăng chiều cao', 'Mắt sáng hơn', 'Da đẹp hơn'], 0, 'Thiếu canxi lâu ngày khiến xương yếu, dễ loãng xương.', $d);
        $this->matching($s, 'Nối mỗi nhóm chất với vai trò.', [['Chất bột đường', 'Cung cấp năng lượng'], ['Chất đạm', 'Xây dựng cơ thể'], ['Chất béo', 'Dự trữ năng lượng'], ['Vitamin, khoáng chất', 'Điều hòa, bảo vệ cơ thể']], 'Bốn nhóm chất dinh dưỡng chính có vai trò bổ sung cho nhau.', $d);
        $this->matching($s, 'Nối mỗi thực phẩm với nhóm chất chính.', [['Cơm, khoai', 'Bột đường'], ['Thịt bò', 'Chất đạm'], ['Dầu ăn', 'Chất béo'], ['Cam, chanh', 'Vitamin C']], 'Mỗi thực phẩm giàu một nhóm chất nhất định.', $d);
        $this->matching($s, 'Nối mỗi chất với nguồn thực phẩm.', [['Canxi', 'Sữa, tôm cua'], ['Sắt', 'Thịt đỏ, rau dền'], ['Vitamin A', 'Cà rốt, gấc'], ['Chất xơ', 'Rau xanh, trái cây']], 'Biết nguồn thực phẩm giúp bổ sung đúng chất cơ thể cần.', $d);
        $this->matching($s, 'Nối mỗi tình trạng thiếu chất với biểu hiện.', [['Thiếu đạm', 'Chậm lớn, cơ yếu'], ['Thiếu sắt', 'Thiếu máu, mệt mỏi'], ['Thiếu vitamin D', 'Xương yếu'], ['Thiếu nước', 'Mệt mỏi, khô da']], 'Thiếu chất dinh dưỡng gây biểu hiện rõ trên cơ thể.', $d);
        $this->matching($s, 'Nối mỗi bữa ăn với nguyên tắc.', [['Bữa sáng', 'Không được bỏ'], ['Bữa trưa', 'Ăn đủ no'], ['Bữa tối', 'Ăn nhẹ, trước khi ngủ 2 giờ'], ['Bữa phụ', 'Ăn vừa phải khi cần']], 'Ăn đúng bữa, đúng lượng giúp cơ thể khỏe mạnh.', $d);
        $this->sortQ($s, 'Xếp các thực phẩm vào nhóm: Giàu đạm / Giàu bột đường.', [['Thịt gà', 'Đạm'], ['Trứng', 'Đạm'], ['Cơm', 'Bột đường'], ['Bánh mì', 'Bột đường'], ['Đậu nành', 'Đạm']], 'Thịt, trứng, đậu giàu đạm; cơm, bánh mì giàu bột đường.', $d);
        $this->sortQ($s, 'Xếp các chất vào nhóm: Sinh năng lượng / Không sinh năng lượng.', [['Bột đường', 'Có'], ['Chất béo', 'Có'], ['Vitamin', 'Không'], ['Chất đạm', 'Có'], ['Khoáng chất', 'Không']], 'Bột đường, béo, đạm sinh năng lượng; vitamin, khoáng chất thì không.', $d);
        $this->sortQ($s, 'Xếp các thói quen vào nhóm: Cân đối dinh dưỡng / Mất cân đối.', [['Ăn đủ 4 nhóm chất', 'Cân đối'], ['Chỉ ăn thịt, bỏ rau', 'Mất'], ['Uống sữa mỗi ngày', 'Cân đối'], ['Ăn quá nhiều đồ ngọt', 'Mất']], 'Ăn đủ 4 nhóm chất mới đảm bảo dinh dưỡng cân đối.', $d);
        $this->sortQ($s, 'Xếp các thực phẩm vào nhóm: Nên ăn nhiều / Nên hạn chế.', [['Rau xanh', 'Nhiều'], ['Trái cây', 'Nhiều'], ['Đồ chiên rán', 'Hạn chế'], ['Nước ngọt', 'Hạn chế'], ['Cá', 'Nhiều']], 'Rau, trái cây, cá nên ăn nhiều; đồ chiên rán, nước ngọt nên hạn chế.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về dinh dưỡng.', [['Cần ăn đủ 4 nhóm chất mỗi ngày', 'Đúng'], ['Vitamin cung cấp năng lượng chính', 'Sai'], ['Chất xơ tốt cho tiêu hóa', 'Đúng'], ['Bỏ bữa sáng không ảnh hưởng sức khỏe', 'Sai']], 'Dinh dưỡng cân đối là nền tảng sức khỏe.', $d);
        $this->fill($s, 'Bốn nhóm chất dinh dưỡng chính là bột đường, chất đạm, chất béo và vitamin - ___ chất.', [[0, 'khoáng']], 'Bốn nhóm chất: bột đường, đạm, béo, vitamin và khoáng chất.', $d);
        $this->fill($s, 'Chất đạm giúp xây dựng và phục hồi cơ ___.', [[0, 'bắp']], 'Chất đạm rất quan trọng với cơ bắp.', $d);
        $this->fill($s, 'Rau xanh và trái cây giàu vitamin, khoáng chất và chất ___.', [[0, 'xơ']], 'Chất xơ tốt cho hệ tiêu hóa.', $d);
        $this->fill($s, 'Thiếu ___ lâu ngày dễ gây thiếu máu, mệt mỏi.', [[0, 'sắt']], 'Sắt cần thiết để tạo máu.', $d);
        $this->fill($s, 'Uống đủ nước mỗi ngày giúp cơ thể chuyển hóa và đào ___ tốt.', [[0, 'thải']], 'Nước giúp chuyển hóa và đào thải độc tố.', $d);
    }

    private function seedGdtcThpt12Lop122(): void
    {
        $s = 'gdtc-thpt-12-lop-12-2'; $d = 'trung_binh';
        $this->quiz($s, 'Nên ăn bữa chính trước buổi tập bao lâu?', ['2-3 giờ', '5 phút', 'Ngay trước khi tập', '10 giờ'], 0, 'Bữa chính nên ăn trước buổi tập 2-3 giờ để tiêu hóa kịp.', $d);
        $this->quiz($s, 'Trước khi tập 30-60 phút có thể ăn gì?', ['Chuối, bánh mì nhỏ', 'Cơm no căng', 'Lẩu cay', 'Đồ chiên rán'], 0, 'Trước tập 30-60 phút chỉ nên ăn nhẹ, dễ tiêu như chuối, bánh mì.', $d);
        $this->quiz($s, 'Trong khi tập kéo dài trên 1 giờ nên bổ sung gì?', ['Nước và điện giải', 'Cơm thịt', 'Bánh kem', 'Không cần gì'], 0, 'Tập dài trên 1 giờ cần bổ sung nước và điện giải.', $d);
        $this->quiz($s, 'Sau khi tập, thời điểm vàng bổ sung dinh dưỡng là khi nào?', ['Trong 30-60 phút sau tập', 'Sau 5 giờ', 'Ngày hôm sau', 'Không cần bổ sung'], 0, '30-60 phút sau tập là thời điểm vàng để bổ sung đạm và tinh bột phục hồi cơ.', $d);
        $this->quiz($s, 'Vì sao không nên uống nước đá lạnh quá nhiều khi đang tập?', ['Dễ viêm họng, ảnh hưởng tiêu hóa', 'Nước đá tốt hơn nước thường', 'Không có lý do', 'Nước đá giúp tập khỏe hơn'], 0, 'Uống nhiều nước đá lạnh khi đang tập dễ gây viêm họng và ảnh hưởng tiêu hóa.', $d);
        $this->matching($s, 'Nối mỗi thời điểm với cách ăn uống.', [['Trước tập 2-3 giờ', 'Bữa chính đủ chất'], ['Trước tập 30 phút', 'Ăn nhẹ dễ tiêu'], ['Trong khi tập', 'Uống nước từng ngụm'], ['Sau tập 1 giờ', 'Bữa phục hồi giàu đạm']], 'Ăn uống đúng thời điểm quanh buổi tập rất quan trọng.', $d);
        $this->matching($s, 'Nối mỗi thực phẩm với thời điểm phù hợp.', [['Chuối', 'Trước khi tập'], ['Cơm, thịt', 'Sau khi tập'], ['Nước điện giải', 'Trong khi tập dài'], ['Sữa', 'Sau khi tập']], 'Mỗi thực phẩm phù hợp với một thời điểm khác nhau.', $d);
        $this->matching($s, 'Nối mỗi sai lầm với hậu quả.', [['Ăn no ngay trước tập', 'Đau bụng, khó chịu'], ['Nhịn ăn khi tập nặng', 'Kiệt sức'], ['Uống ít nước', 'Mất nước, chuột rút'], ['Ăn quá nhiều đồ ngọt', 'Tăng cân, mệt nhanh']], 'Sai lầm trong ăn uống ảnh hưởng trực tiếp đến buổi tập.', $d);
        $this->matching($s, 'Nối mỗi loại đồ uống với đánh giá khi tập.', [['Nước lọc', 'Tốt nhất'], ['Nước điện giải', 'Tốt khi tập dài'], ['Nước ngọt có ga', 'Không nên'], ['Rượu bia', 'Tuyệt đối không']], 'Nước lọc là lựa chọn tốt nhất khi tập luyện.', $d);
        $this->matching($s, 'Nối mỗi nhu cầu với cách đáp ứng.', [['Năng lượng trước tập', 'Tinh bột dễ tiêu'], ['Phục hồi cơ sau tập', 'Chất đạm'], ['Bù nước', 'Uống đủ nước'], ['Bù muối khoáng', 'Nước điện giải, trái cây']], 'Đáp ứng đúng nhu cầu dinh dưỡng ở từng giai đoạn.', $d);
        $this->sortQ($s, 'Xếp các món ăn vào nhóm: Phù hợp trước khi tập / Không phù hợp trước khi tập.', [['Chuối', 'Phù hợp'], ['Bánh mì', 'Phù hợp'], ['Cơm no căng', 'Không'], ['Đồ cay nóng', 'Không'], ['Cháo loãng', 'Phù hợp']], 'Trước khi tập ăn nhẹ, dễ tiêu; tránh no căng và đồ cay nóng.', $d);
        $this->sortQ($s, 'Xếp các thời điểm ăn vào nhóm: Đúng / Sai.', [['Ăn chính trước tập 2-3 giờ', 'Đúng'], ['Ăn no 10 phút trước khi tập', 'Sai'], ['Ăn phục hồi sau tập 1 giờ', 'Đúng'], ['Nhịn đói tập nặng', 'Sai']], 'Ăn đúng thời điểm giúp có năng lượng và phục hồi tốt.', $d);
        $this->sortQ($s, 'Xếp các đồ uống vào nhóm: Nên dùng khi tập / Không nên dùng.', [['Nước lọc', 'Nên'], ['Nước điện giải', 'Nên'], ['Nước ngọt có ga', 'Không nên'], ['Cà phê đặc', 'Không nên']], 'Khi tập nên uống nước lọc hoặc nước điện giải.', $d);
        $this->sortQ($s, 'Xếp các thói quen vào nhóm: Tốt / Xấu.', [['Uống nước từng ngụm nhỏ', 'Tốt'], ['Ăn nhẹ trước tập', 'Tốt'], ['Uống ừng ực thật nhiều một lúc', 'Xấu'], ['Ăn no căng trước giờ tập', 'Xấu']], 'Uống từng ngụm nhỏ và ăn nhẹ trước tập là thói quen tốt.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về ăn uống khi tập.', [['Sau tập nên bổ sung đạm để phục hồi cơ', 'Đúng'], ['Càng nhịn ăn tập càng khỏe', 'Sai'], ['Nước rất quan trọng khi tập luyện', 'Đúng'], ['Ăn no ngay trước tập giúp có sức', 'Sai']], 'Ăn uống khoa học quanh buổi tập giúp tập hiệu quả và an toàn.', $d);
        $this->fill($s, 'Nên ăn bữa chính trước buổi tập khoảng 2 đến 3 ___.', [[0, 'giờ']], 'Bữa chính cách buổi tập 2-3 giờ.', $d);
        $this->fill($s, 'Trước khi tập 30 phút chỉ nên ăn nhẹ như chuối hoặc bánh ___.', [[0, 'mì']], 'Ăn nhẹ, dễ tiêu trước khi tập.', $d);
        $this->fill($s, 'Trong khi tập cần uống nước từng ngụm ___.', [[0, 'nhỏ']], 'Uống từng ngụm nhỏ, không uống ừng ực.', $d);
        $this->fill($s, 'Sau khi tập 30-60 phút là thời điểm vàng để bổ sung chất ___.', [[0, 'đạm']], 'Bổ sung đạm giúp cơ bắp phục hồi.', $d);
        $this->fill($s, 'Ăn quá no ngay trước giờ tập dễ gây đau bụng và khó ___.', [[0, 'chịu']], 'Ăn no ngay trước khi tập gây khó chịu, ảnh hưởng vận động.', $d);
    }

    private function seedGdtcThpt12Lop123(): void
    {
        $s = 'gdtc-thpt-12-lop-12-3'; $d = 'trung_binh';
        $this->quiz($s, 'Nguyên tắc "tăng dần" trong tập luyện nghĩa là gì?', ['Tăng dần cường độ, khối lượng theo thời gian', 'Tập thật nặng ngay từ đầu', 'Tập càng nhiều càng tốt', 'Không cần tăng gì'], 0, 'Tăng dần giúp cơ thể thích nghi an toàn, tránh quá tải.', $d);
        $this->quiz($s, 'Vì sao cần tập luyện thường xuyên, đều đặn?', ['Cơ thể thích nghi và tiến bộ bền vững', 'Tập một lần là đủ', 'Nghỉ dài mới tốt', 'Tập thất thường hiệu quả hơn'], 0, 'Tập đều đặn giúp cơ thể tiến bộ bền vững.', $d);
        $this->quiz($s, 'Nguyên tắc "vừa sức" có nghĩa là gì?', ['Chọn cường độ phù hợp khả năng bản thân', 'Tập quá sức chịu đựng', 'Tập theo người khác bất chấp', 'Càng đau càng tốt'], 0, 'Tập vừa sức tránh chấn thương và quá tải.', $d);
        $this->quiz($s, 'Vì sao mỗi buổi tập cần có phần khởi động và thả lỏng?', ['Chuẩn bị và hồi phục cơ thể an toàn', 'Tốn thời gian', 'Không cần thiết', 'Chỉ để cho đẹp'], 0, 'Khởi động chuẩn bị cơ thể, thả lỏng giúp hồi phục an toàn.', $d);
        $this->quiz($s, 'Nghỉ ngơi có vai trò gì trong tập luyện?', ['Giúp cơ bắp phục hồi và phát triển', 'Làm mất hết kết quả tập', 'Không có vai trò', 'Chỉ dành cho người lười'], 0, 'Cơ bắp phục hồi và phát triển trong lúc nghỉ ngơi.', $d);
        $this->matching($s, 'Nối mỗi nguyên tắc với nội dung.', [['Tăng dần', 'Nâng cường độ từ từ'], ['Đều đặn', 'Tập thường xuyên'], ['Vừa sức', 'Phù hợp khả năng'], ['Toàn diện', 'Rèn nhiều tố chất']], 'Bốn nguyên tắc vàng của tập luyện khoa học.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn buổi tập với nhiệm vụ.', [['Khởi động', 'Chuẩn bị cơ thể'], ['Phần chính', 'Rèn luyện trọng tâm'], ['Thả lỏng', 'Hồi phục'], ['Nghỉ ngơi', 'Phát triển cơ bắp']], 'Buổi tập khoa học gồm khởi động, tập chính, thả lỏng và nghỉ ngơi.', $d);
        $this->matching($s, 'Nối mỗi sai lầm với hậu quả.', [['Tập quá sức', 'Chấn thương, quá tải'], ['Bỏ khởi động', 'Dễ căng cơ'], ['Tập thất thường', 'Không tiến bộ'], ['Thiếu ngủ', 'Hồi phục kém']], 'Sai lầm trong tập luyện gây hậu quả cho sức khỏe.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với ý nghĩa.', [['Tiến bộ dần', 'Tập đúng phương pháp'], ['Mệt mỏi kéo dài', 'Cần giảm tải, nghỉ ngơi'], ['Đau nhói', 'Có thể chấn thương'], ['Hứng thú tập luyện', 'Tâm lý tốt']], 'Lắng nghe cơ thể để điều chỉnh kế hoạch tập.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với vai trò.', [['Dinh dưỡng', 'Nhiên liệu cho cơ thể'], ['Giấc ngủ', 'Phục hồi'], ['Nước uống', 'Điều hòa thân nhiệt'], ['Tinh thần', 'Quyết định sự kiên trì']], 'Tập luyện hiệu quả cần kết hợp nhiều yếu tố.', $d);
        $this->sortQ($s, 'Xếp các cách tập vào nhóm: Khoa học / Không khoa học.', [['Tăng dần cường độ', 'Khoa học'], ['Tập đều mỗi tuần', 'Khoa học'], ['Tập quá sức liên tục', 'Không'], ['Bỏ qua khởi động', 'Không'], ['Ngủ đủ giấc', 'Khoa học']], 'Tập khoa học: tăng dần, đều đặn, vừa sức, có khởi động và nghỉ ngơi.', $d);
        $this->sortQ($s, 'Xếp các nguyên tắc vào nhóm: Đúng / Sai.', [['Tập vừa sức mình', 'Đúng'], ['Càng đau càng hiệu quả', 'Sai'], ['Cần khởi động trước mỗi buổi', 'Đúng'], ['Nghỉ ngơi là không cần thiết', 'Sai']], 'Đau không đồng nghĩa hiệu quả; nghỉ ngơi là một phần của tập luyện.', $d);
        $this->sortQ($s, 'Xếp các giai đoạn vào nhóm: Đầu buổi / Cuối buổi.', [['Khởi động', 'Đầu'], ['Thả lỏng', 'Cuối'], ['Chạy nhẹ làm nóng', 'Đầu'], ['Giãn cơ hồi phục', 'Cuối']], 'Đầu buổi khởi động, cuối buổi thả lỏng.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Giúp tiến bộ / Cản trở tiến bộ.', [['Ghi chép quá trình tập', 'Giúp'], ['Đặt mục tiêu rõ ràng', 'Giúp'], ['Tập bữa đực bữa cái', 'Cản trở'], ['So sánh quá sức với người khác', 'Cản trở']], 'Mục tiêu rõ ràng và kiên trì giúp tiến bộ bền vững.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về nghỉ ngơi.', [['Cơ bắp phát triển trong lúc nghỉ', 'Đúng'], ['Tập 7 ngày/tuần cường độ cao là tốt nhất', 'Sai'], ['Ngủ đủ giúp hồi phục nhanh', 'Đúng'], ['Đau cơ nhẹ sau tập là bình thường', 'Đúng']], 'Nghỉ ngơi hợp lý là một phần không thể thiếu của tập luyện.', $d);
        $this->fill($s, 'Nguyên tắc quan trọng là tăng dần cường độ, không tập quá ___ ngay từ đầu.', [[0, 'sức']], 'Tập quá sức ngay từ đầu dễ gây chấn thương.', $d);
        $this->fill($s, 'Mỗi buổi tập cần bắt đầu bằng phần khởi ___.', [[0, 'động']], 'Khởi động là phần mở đầu bắt buộc của buổi tập.', $d);
        $this->fill($s, 'Sau phần tập chính, cần thả ___ để cơ thể hồi phục.', [[0, 'lỏng']], 'Thả lỏng giúp cơ thể hồi phục sau vận động.', $d);
        $this->fill($s, 'Tập luyện phải đều đặn, không nên bữa đực bữa ___.', [[0, 'cái']], 'Tập thất thường không mang lại tiến bộ.', $d);
        $this->fill($s, 'Nghỉ ngơi và ngủ đủ giấc giúp cơ bắp phục ___ tốt hơn.', [[0, 'hồi']], 'Cơ bắp phục hồi và phát triển trong lúc nghỉ ngơi.', $d);
    }

    private function seedGdtcThpt12Lop124(): void
    {
        $s = 'gdtc-thpt-12-lop-12-4'; $d = 'kho';
        $this->quiz($s, 'Biện pháp nào quan trọng nhất để phòng tránh chấn thương?', ['Khởi động kỹ và tập đúng kỹ thuật', 'Tập càng nặng càng tốt', 'Không cần chuẩn bị gì', 'Tập một mình nơi vắng'], 0, 'Khởi động kỹ và đúng kỹ thuật là biện pháp phòng chấn thương hiệu quả nhất.', $d);
        $this->quiz($s, 'Khi phát hiện dụng cụ tập bị hỏng, cần làm gì?', ['Báo ngay và không sử dụng', 'Vẫn dùng bình thường', 'Tự sửa qua loa rồi dùng', 'Giấu đi'], 0, 'Dụng cụ hỏng phải báo ngay và tuyệt đối không sử dụng.', $d);
        $this->quiz($s, 'Vì sao phải kiểm tra sân bãi trước khi tập?', ['Phát hiện vật nguy hiểm, chỗ trơn trượt', 'Mất thời gian', 'Không cần thiết', 'Chỉ để cho đẹp'], 0, 'Kiểm tra sân giúp phát hiện vật sắc nhọn, vũng nước gây nguy hiểm.', $d);
        $this->quiz($s, 'Mang đồ bảo hộ khi chơi thể thao đối kháng giúp gì?', ['Giảm mức độ chấn thương khi va chạm', 'Làm đẹp', 'Không có tác dụng', 'Gây vướng víu nên bỏ'], 0, 'Đồ bảo hộ giảm mức độ chấn thương khi va chạm.', $d);
        $this->quiz($s, 'Khi cơ thể có dấu hiệu quá tải (đau kéo dài, mệt mỏi), nên làm gì?', ['Giảm cường độ, nghỉ ngơi và theo dõi', 'Cố tập tiếp', 'Tăng cường độ', 'Uống thuốc giảm đau rồi tập'], 0, 'Dấu hiệu quá tải cần được nghỉ ngơi, theo dõi, không cố tập tiếp.', $d);
        $this->matching($s, 'Nối mỗi biện pháp với tác dụng.', [['Khởi động kỹ', 'Giảm căng cơ, bong gân'], ['Đồ bảo hộ', 'Giảm chấn thương va chạm'], ['Kỹ thuật đúng', 'Tránh sai tư thế'], ['Kiểm tra sân bãi', 'Tránh trượt ngã']], 'Phòng tránh chấn thương cần nhiều biện pháp phối hợp.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu quá tải với cách xử lý.', [['Đau cơ kéo dài', 'Nghỉ ngơi, giảm tải'], ['Mệt mỏi triền miên', 'Xem lại chế độ tập'], ['Mất ngủ, chán ăn', 'Cần nghỉ dài hơn'], ['Đau nhói đột ngột', 'Dừng ngay, kiểm tra']], 'Nhận biết dấu hiệu quá tải để xử lý kịp thời.', $d);
        $this->matching($s, 'Nối mỗi môn với đồ bảo hộ.', [['Bóng đá', 'Bảo vệ ống chân'], ['Đạp xe', 'Mũ bảo hiểm'], ['Trượt patin', 'Bảo vệ gối, khuỷu tay'], ['Võ thuật', 'Găng tay, bảo hộ răng']], 'Mỗi môn thể thao có đồ bảo hộ phù hợp.', $d);
        $this->matching($s, 'Nối mỗi nguyên nhân chấn thương với cách phòng.', [['Không khởi động', 'Khởi động 10-15 phút'], ['Sân trơn', 'Kiểm tra, làm khô sân'], ['Tập quá sức', 'Tăng dần, vừa sức'], ['Dụng cụ hỏng', 'Kiểm tra trước khi dùng']], 'Mỗi nguyên nhân chấn thương đều có cách phòng tránh.', $d);
        $this->matching($s, 'Nối mỗi việc làm với thời điểm.', [['Kiểm tra sân', 'Trước buổi tập'], ['Khởi động', 'Đầu buổi tập'], ['Thả lỏng', 'Cuối buổi tập'], ['Đánh giá cơ thể', 'Sau mỗi tuần tập']], 'Phòng tránh chấn thương là việc làm xuyên suốt quá trình tập.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: Phòng tránh chấn thương / Gây nguy cơ.', [['Khởi động kỹ', 'Phòng'], ['Mang đồ bảo hộ', 'Phòng'], ['Chơi xô đẩy', 'Nguy cơ'], ['Tập quá sức', 'Nguy cơ'], ['Kiểm tra dụng cụ', 'Phòng']], 'Chuẩn bị tốt giúp phòng tránh, chủ quan gây nguy cơ chấn thương.', $d);
        $this->sortQ($s, 'Xếp các dấu hiệu vào nhóm: Nghỉ ngơi được / Cần đi khám.', [['Mỏi cơ nhẹ sau tập', 'Nghỉ được'], ['Đau dữ dội, sưng to', 'Đi khám'], ['Hơi căng cơ', 'Nghỉ được'], ['Nghi ngờ gãy xương', 'Đi khám'], ['Mệt sau buổi tập nặng', 'Nghỉ được']], 'Mỏi nhẹ nghỉ ngơi là khỏi; đau dữ dội, sưng to, nghi gãy xương phải đi khám.', $d);
        $this->sortQ($s, 'Xếp các hành vi vào nhóm: Đúng / Sai khi dụng cụ hỏng.', [['Báo thầy cô ngay', 'Đúng'], ['Vẫn cố dùng', 'Sai'], ['Đánh dấu để người khác tránh', 'Đúng'], ['Giấu đi cho xong', 'Sai']], 'Dụng cụ hỏng phải báo ngay, không cố dùng.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm: Đúng / Sai về phòng chấn thương.', [['Khởi động giúp phòng chấn thương', 'Đúng'], ['Đồ bảo hộ là không cần thiết', 'Sai'], ['Đau kéo dài cần được theo dõi', 'Đúng'], ['Tập quá sức không gây hại', 'Sai']], 'Phòng chấn thương: khởi động, đồ bảo hộ, vừa sức, theo dõi dấu hiệu.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: Chủ quan (từ bản thân) / Khách quan (từ môi trường).', [['Không khởi động', 'Chủ quan'], ['Tập quá sức', 'Chủ quan'], ['Sân trơn ướt', 'Khách quan'], ['Dụng cụ hỏng', 'Khách quan'], ['Thiếu ngủ', 'Chủ quan']], 'Chấn thương đến từ cả yếu tố chủ quan và khách quan.', $d);
        $this->fill($s, 'Khởi động kỹ trước mỗi buổi tập giúp phòng tránh chấn ___.', [[0, 'thương']], 'Khởi động kỹ là biện pháp phòng chấn thương quan trọng nhất.', $d);
        $this->fill($s, 'Dụng cụ tập bị hỏng phải được báo ngay và ___ sử dụng.', [[0, 'ngừng']], 'Tuyệt đối không dùng dụng cụ đã hỏng.', $d);
        $this->fill($s, 'Khi chơi thể thao đối kháng nên mang đồ bảo ___ phù hợp.', [[0, 'hộ']], 'Đồ bảo hộ giảm chấn thương khi va chạm.', $d);
        $this->fill($s, 'Sân bãi trơn trượt, có vật sắc nhọn phải được xử lý trước khi ___.', [[0, 'tập']], 'Kiểm tra và xử lý sân bãi trước mỗi buổi tập.', $d);
        $this->fill($s, 'Đau nhói đột ngột khi vận động là tín hiệu phải dừng ___ ngay.', [[0, 'lại']], 'Đau nhói đột ngột là tín hiệu nguy hiểm, phải dừng ngay.', $d);
    }
}
