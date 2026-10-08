<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Bổ sung câu hỏi cho 15 bài học môn Trải nghiệm & Hướng nghiệp (nửa sau, G6c2).
 *
 * Mỗi bài: 5 câu MỚI cho mỗi kiểu chơi (quiz/matching/sort/fill).
 * Nội dung tiếng Việt tự viết 100%, bám topic + khối lớp + độ khó của bài,
 * không trùng prompt đã có. Idempotent: chạy lại không thêm câu mới
 * (giới hạn 8 câu/kiểu/bài + kiểm tra prompt trùng).
 */
class AdditionalQuestionsGroup6c2Seeder extends Seeder
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
        $this->seedLop82();
        $this->seedLop91();
        $this->seedLop92();
        $this->seedLop101();
        $this->seedLop102();
        $this->seedLop103();
        $this->seedLop104();
        $this->seedLop111();
        $this->seedLop112();
        $this->seedLop113();
        $this->seedLop114();
        $this->seedLop121();
        $this->seedLop122();
        $this->seedLop123();
        $this->seedLop124();
    }

    private function seedLop82(): void
    {
        $s = 'tnhn-dinh-huong-nghe-nghiep-lop-8-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Sở thích khác năng lực ở điểm nào?', [
            'Sở thích là điều bạn thích làm; năng lực là điều bạn làm giỏi',
            'Sở thích luôn tự biến thành năng lực theo thời gian',
            'Năng lực là thứ bẩm sinh, không thể rèn luyện được',
            'Sở thích quan trọng hơn năng lực khi chọn nghề',
        ], 0, 'Thích và giỏi là hai chuyện khác nhau; chọn nghề bền vững cần cả hai.', $d);
        $this->quiz($s, 'Để biết mình giỏi môn nào, cách khách quan nhất là gì?', [
            'Dựa vào kết quả học tập thực tế và nhận xét của thầy cô, bạn bè',
            'Tự đoán theo cảm tính của bản thân',
            'So sánh mình với bạn giỏi nhất lớp',
            'Chọn môn nào nhiều bạn thích học',
        ], 0, 'Kết quả thực tế và góc nhìn của người xung quanh giúp đánh giá khách quan hơn cảm tính.', $d);
        $this->quiz($s, 'Bạn Minh thích đá bóng nhưng chạy chậm và yếu sức; lời khuyên hợp lý nhất là gì?', [
            'Chấp nhận hạn chế và tìm con đường phù hợp hơn, ví dụ nghề liên quan tới thể thao',
            'Cố làm cầu thủ chuyên nghiệp bằng mọi giá',
            'Bỏ hẳn niềm đam mê thể thao của mình',
            'Đổ lỗi cho năng khiếu bẩm sinh rồi nản chí',
        ], 0, 'Hiểu điểm yếu giúp điều chỉnh hướng đi thay vì cố chấp hoặc bỏ cuộc.', $d);
        $this->quiz($s, 'Điểm yếu của bạn là hay trì hoãn; cách cải thiện hợp lý nhất là gì?', [
            'Lập kế hoạch cụ thể và chia việc lớn thành bước nhỏ',
            'Chờ đến khi có hứng mới bắt đầu làm',
            'Giấu điểm yếu, không cho ai biết',
            'Nhận thêm thật nhiều việc để ép mình',
        ], 0, 'Điểm yếu cải thiện được bằng kế hoạch cụ thể, không phải bằng ép buộc hay né tránh.', $d);
        $this->quiz($s, '"Giá trị" của một người trong chọn nghề được hiểu là gì?', [
            'Điều họ coi trọng trong công việc, như thu nhập, sự giúp đỡ người khác hay tự do',
            'Số tiền lương cao nhất họ từng nhận',
            'Chức danh oai nhất mà họ muốn có',
            'Bằng cấp cao nhất mà họ sở hữu',
        ], 0, 'Giá trị là la bàn giúp chọn nghề phù hợp với điều mình thật sự coi trọng.', $d);

        $this->matching($s, 'Nối mỗi dấu hiệu với điểm mạnh hoặc điểm yếu tương ứng.', [
            ['Làm tốt môn Toán và thích giải bài khó', 'Điểm mạnh'],
            ['Hay quên làm bài tập về nhà', 'Điểm yếu'],
            ['Giao tiếp tốt, được bạn bè tin tưởng', 'Điểm mạnh'],
            ['Sợ phát biểu trước lớp', 'Điểm yếu'],
        ], 'Nhận diện đúng điểm mạnh, điểm yếu là bước đầu để hiểu bản thân.', $d);
        $this->matching($s, 'Nối mỗi hoạt động với điều nó giúp bạn khám phá.', [
            ['Viết nhật ký mỗi tối', 'Nhận ra cảm xúc và thói quen của mình'],
            ['Thử làm lớp trưởng một tuần', 'Khả năng lãnh đạo'],
            ['Tham gia đội văn nghệ', 'Năng khiếu nghệ thuật'],
            ['Hỏi người thân nhận xét về mình', 'Góc nhìn khách quan từ bên ngoài'],
        ], 'Khám phá bản thân cần cả tự quan sát và trải nghiệm thực tế.', $d);
        $this->matching($s, 'Nối mỗi nghề với năng lực quan trọng nhất của nó.', [
            ['Giáo viên', 'Truyền đạt và kiên nhẫn'],
            ['Bác sĩ', 'Chịu áp lực và chính xác'],
            ['Kiến trúc sư', 'Sáng tạo và tư duy không gian'],
            ['Thợ điện', 'Khéo tay và cẩn thận'],
        ], 'Mỗi nghề đòi hỏi một bộ năng lực đặc trưng riêng.', $d);
        $this->matching($s, 'Nối mỗi câu nói với đánh giá về cách chọn nghề.', [
            ['Tôi muốn làm bác sĩ vì bố mẹ muốn thế', 'Chưa xuất phát từ bản thân'],
            ['Tôi thích máy tính nên tìm hiểu nghề công nghệ', 'Có định hướng rõ ràng'],
            ['Tôi chọn nghề lương cao dù chẳng thích', 'Thiếu bền vững lâu dài'],
            ['Tôi thử nhiều việc để tìm ra đam mê', 'Chủ động khám phá'],
        ], 'Lý do chọn nghề nói lên mức độ hiểu bản thân của mỗi người.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn với câu hỏi giúp hiểu bản thân.', [
            ['Nhìn lại tuổi thơ', 'Tôi thích chơi trò gì nhất?'],
            ['Đánh giá hiện tại', 'Tôi đang giỏi điều gì?'],
            ['Hướng tới tương lai', 'Tôi muốn trở thành người thế nào?'],
            ['Tổng kết', 'Ba từ nào mô tả tôi chính xác nhất?'],
        ], 'Hiểu bản thân là hành trình nối quá khứ, hiện tại và tương lai.', $d);

        $this->sortQ($s, 'Kéo mỗi câu nói vào nhóm HIỂU MÌNH hoặc CÒN MƠ HỒ.', [
            ['Tôi giỏi Toán, dở Văn — biết rõ môn mạnh và yếu của mình', 'HIỂU MÌNH'],
            ['Tôi chọn nghề vì bạn thân cũng chọn nghề đó', 'CÒN MƠ HỒ'],
            ['Tôi biết mình kiên nhẫn và thích giúp đỡ người khác', 'HIỂU MÌNH'],
            ['Tôi không biết mình thích gì nữa', 'CÒN MƠ HỒ'],
            ['Tôi từng thử nhiều vai trò và biết mình hợp làm trưởng nhóm', 'HIỂU MÌNH'],
            ['Tôi chọn nghề nào lương cao nhất, kệ có hợp hay không', 'CÒN MƠ HỒ'],
        ], 'Người hiểu mình quyết định dựa trên đặc điểm thật của bản thân.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm NÊN LÀM hoặc NÊN TRÁNH khi khám phá bản thân.', [
            ['Thử nhiều hoạt động mới', 'NÊN LÀM'],
            ['Chỉ làm việc mình đã giỏi', 'NÊN TRÁNH'],
            ['Xin nhận xét của thầy cô', 'NÊN LÀM'],
            ['So sánh mình với bạn giỏi nhất rồi tự ti', 'NÊN TRÁNH'],
            ['Ghi nhật ký điểm mạnh, điểm yếu', 'NÊN LÀM'],
            ['Đóng cửa với mọi lời góp ý', 'NÊN TRÁNH'],
        ], 'Khám phá bản thân cần sự cởi mở, không phải né tránh.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm SỞ THÍCH hoặc NĂNG LỰC.', [
            ['Thích đọc truyện tranh mỗi tối', 'SỞ THÍCH'],
            ['Giải toán nhanh và chính xác', 'NĂNG LỰC'],
            ['Thích nghe nhạc khi rảnh', 'SỞ THÍCH'],
            ['Nói tiếng Anh trôi chảy', 'NĂNG LỰC'],
            ['Thích chơi game cùng bạn bè', 'SỞ THÍCH'],
            ['Khéo tay gấp giấy origami', 'NĂNG LỰC'],
        ], 'Sở thích là điều bạn thích; năng lực là điều bạn làm giỏi.', $d);
        $this->sortQ($s, 'Kéo mỗi lựa chọn nghề vào nhóm DỰA TRÊN BẢN THÂN hoặc CHẠY THEO BÊN NGOÀI.', [
            ['Chọn vì hợp tính cách của mình', 'DỰA TRÊN BẢN THÂN'],
            ['Chọn vì bạn thân cũng chọn', 'CHẠY THEO BÊN NGOÀI'],
            ['Chọn vì đam mê và năng lực của mình', 'DỰA TRÊN BẢN THÂN'],
            ['Chọn vì lương cao dù không thích', 'CHẠY THEO BÊN NGOÀI'],
            ['Chọn sau khi trải nghiệm thực tế', 'DỰA TRÊN BẢN THÂN'],
            ['Chọn vì bị ép mà không dám trao đổi', 'CHẠY THEO BÊN NGOÀI'],
        ], 'Quyết định dựa trên bản thân thường bền vững hơn chạy theo bên ngoài.', $d);
        $this->sortQ($s, 'Kéo mỗi biểu hiện vào nhóm ĐIỂM MẠNH DÙNG ĐƯỢC CHO NGHỀ hoặc CẦN CẢI THIỆN.', [
            ['Kiên nhẫn', 'ĐIỂM MẠNH DÙNG ĐƯỢC CHO NGHỀ'],
            ['Hay trì hoãn', 'CẦN CẢI THIỆN'],
            ['Có óc quan sát tốt', 'ĐIỂM MẠNH DÙNG ĐƯỢC CHO NGHỀ'],
            ['Dễ nản khi gặp khó', 'CẦN CẢI THIỆN'],
            ['Học nhanh công nghệ mới', 'ĐIỂM MẠNH DÙNG ĐƯỢC CHO NGHỀ'],
            ['Ngại giao tiếp với người lạ', 'CẦN CẢI THIỆN'],
        ], 'Điểm mạnh là vốn liếng chọn nghề; điểm yếu là thứ cần rèn luyện.', $d);

        $this->fill($s, 'Sở thích là điều bạn thích làm, còn ___ là điều bạn làm tốt.', [[0, 'năng lực']], 'Phân biệt rõ sở thích và năng lực giúp chọn nghề thực tế hơn.', $d);
        $this->fill($s, 'Người có điểm mạnh về giao tiếp thường hợp với nghề cần tiếp ___ người.', [[0, 'xúc']], 'Điểm mạnh giao tiếp là lợi thế của các nghề dịch vụ, kinh doanh, sư phạm.', $d);
        $this->fill($s, 'Biết rõ điểm ___ giúp bạn tránh chọn nghề không hợp với mình.', [[0, 'yếu']], 'Hiểu điểm yếu không phải để tự ti mà để chọn hướng đi khôn ngoan.', $d);
        $this->fill($s, 'Thử nhiều vai trò trong nhóm giúp bạn phát hiện ___ khiếu tiềm ẩn.', [[0, 'năng']], 'Năng khiếu tiềm ẩn chỉ bộc lộ khi bạn dám thử những điều mới.', $d);
        $this->fill($s, 'Chọn nghề chỉ vì lương cao mà bỏ qua ___ thích dễ khiến bạn chán nản sau này.', [[0, 'sở']], 'Lương cao không bù đắp được sự chán nản khi làm việc mình không thích.', $d);
    }

    private function seedLop91(): void
    {
        $s = 'tnhn-dinh-huong-nghe-nghiep-lop-9-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Học hết lớp 9, nếu muốn học nghề sớm thì nên chọn loại trường nào?', [
            'Trường trung cấp nghề',
            'Trường trung học phổ thông',
            'Trường đại học',
            'Trường mầm non',
        ], 0, 'Trung cấp nghề đào tạo tay nghề cụ thể để đi làm sớm sau 1–2 năm.', $d);
        $this->quiz($s, 'Cao đẳng khác trung cấp ở điểm nào?', [
            'Đào tạo chuyên sâu hơn và có thể liên thông lên đại học',
            'Chỉ nhận học sinh giỏi',
            'Không cấp bằng tốt nghiệp',
            'Học hoàn toàn miễn phí',
        ], 0, 'Cao đẳng là bậc cao hơn trung cấp, mở đường liên thông lên đại học.', $d);
        $this->quiz($s, '"Liên thông" trong giáo dục được hiểu là gì?', [
            'Học tiếp lên bậc cao hơn, ví dụ từ trung cấp lên cao đẳng rồi đại học',
            'Chuyển trường trong cùng một năm học',
            'Học hai trường cùng một lúc',
            'Bỏ học giữa chừng rồi quay lại',
        ], 0, 'Liên thông giúp người học nghề vẫn có cơ hội lấy bằng cao hơn.', $d);
        $this->quiz($s, 'Bạn An thích nấu ăn và muốn đi làm sớm phụ giúp gia đình; hướng đi hợp lý nhất là gì?', [
            'Học trung cấp nghề ẩm thực rồi đi làm',
            'Cố thi vào lớp 10 dù không thích học văn hoá',
            'Nghỉ học đi phụ quán không cần đào tạo',
            'Chờ vài năm rồi tính tiếp',
        ], 0, 'Chọn hướng đi phù hợp sở thích và hoàn cảnh giúp An vừa học nghề vừa sớm có thu nhập.', $d);
        $this->quiz($s, 'Điều nào NÊN TRÁNH khi chọn hướng đi sau lớp 9?', [
            'Chọn theo trào lưu bạn bè mà không xem năng lực của mình',
            'Tìm hiểu kỹ các trường và ngành đào tạo',
            'Tham khảo ý kiến thầy cô và người đi trước',
            'Cân nhắc hoàn cảnh gia đình',
        ], 0, 'Chạy theo bạn bè mà bỏ qua năng lực bản thân là sai lầm phổ biến.', $d);

        $this->matching($s, 'Nối mỗi bậc học với thời gian đào tạo đặc trưng.', [
            ['Trung cấp', '1 – 2 năm'],
            ['Cao đẳng', '2 – 3 năm'],
            ['Trung học phổ thông', '3 năm'],
            ['Đại học', '4 – 6 năm'],
        ], 'Mỗi bậc học có thời gian đào tạo và mục tiêu khác nhau.', $d);
        $this->matching($s, 'Nối mỗi hướng đi sau lớp 9 với cách bắt đầu.', [
            ['Học tiếp THPT', 'Thi tuyển vào lớp 10'],
            ['Học trung cấp nghề', 'Xét tuyển học bạ THCS'],
            ['Học việc tại doanh nghiệp', 'Đăng ký học việc theo hợp đồng'],
            ['Du học', 'Đáp ứng yêu cầu của trường nước ngoài'],
        ], 'Mỗi hướng đi có cánh cửa vào riêng, cần chuẩn bị từ sớm.', $d);
        $this->matching($s, 'Nối mỗi đối tượng học sinh với lời khuyên phù hợp.', [
            ['Học giỏi, thích nghiên cứu', 'Thi vào THPT rồi hướng tới đại học'],
            ['Thích thực hành, muốn đi làm sớm', 'Học trung cấp nghề theo ngành yêu thích'],
            ['Học yếu nhưng khéo tay', 'Chọn nghề thủ công và rèn tay nghề thật giỏi'],
            ['Chưa rõ mình muốn gì', 'Dành thời gian trải nghiệm và tìm hiểu thêm'],
        ], 'Không có hướng đi nào tốt cho tất cả; quan trọng là hợp với mình.', $d);
        $this->matching($s, 'Nối mỗi loại giấy tờ với vai trò của nó.', [
            ['Bằng tốt nghiệp THCS', 'Điều kiện để học tiếp mọi hướng đi'],
            ['Học bạ THCS', 'Cơ sở xét tuyển vào trường nghề'],
            ['Chứng chỉ nghề', 'Chứng minh tay nghề với nhà tuyển dụng'],
            ['Hồ sơ đăng ký nhập học', 'Thủ tục chính thức vào trường'],
        ], 'Giấy tờ đầy đủ giúp quá trình nhập học diễn ra suôn sẻ.', $d);
        $this->matching($s, 'Nối mỗi quan niệm với đánh giá đúng đắn.', [
            ['Học nghề chỉ dành cho học sinh yếu', 'Định kiến sai lầm'],
            ['Đường nào cũng tới thành công nếu nỗ lực', 'Quan niệm đúng'],
            ['Chỉ đại học mới có tương lai', 'Định kiến sai lầm'],
            ['Học nghề sớm, đi làm sớm, học lên sau', 'Lựa chọn sáng suốt'],
        ], 'Phá bỏ định kiến giúp nhìn nhận đúng giá trị của từng hướng đi.', $d);

        $this->sortQ($s, 'Kéo mỗi nghề vào nhóm ĐÀO TẠO QUA TRƯỜNG NGHỀ hoặc CẦN BẰNG ĐẠI HỌC.', [
            ['Thợ sửa ô tô', 'ĐÀO TẠO QUA TRƯỜNG NGHỀ'],
            ['Bác sĩ', 'CẦN BẰNG ĐẠI HỌC'],
            ['Thợ làm tóc', 'ĐÀO TẠO QUA TRƯỜNG NGHỀ'],
            ['Kỹ sư xây dựng', 'CẦN BẰNG ĐẠI HỌC'],
            ['Đầu bếp', 'ĐÀO TẠO QUA TRƯỜNG NGHỀ'],
            ['Luật sư', 'CẦN BẰNG ĐẠI HỌC'],
        ], 'Nghề thủ công cần tay nghề; nghề chuyên môn sâu cần bằng đại học.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm NÊN LÀM hoặc KHÔNG NÊN trước khi chọn hướng đi.', [
            ['Tìm hiểu ngành nghề trong thực tế', 'NÊN LÀM'],
            ['Chọn bừa theo bạn bè', 'KHÔNG NÊN'],
            ['Hỏi kinh nghiệm người đi trước', 'NÊN LÀM'],
            ['Quyết định vội trong một ngày', 'KHÔNG NÊN'],
            ['Tham quan trường nghề', 'NÊN LÀM'],
            ['Nghe theo tin đồn không kiểm chứng', 'KHÔNG NÊN'],
        ], 'Chọn hướng đi là quyết định lớn, cần tìm hiểu kỹ càng.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm SỰ THẬT hoặc ĐỊNH KIẾN.', [
            ['Học nghề ra trường có thể có việc làm ngay', 'SỰ THẬT'],
            ['Học nghề chỉ dành cho học sinh kém', 'ĐỊNH KIẾN'],
            ['Học trung cấp có thể liên thông lên đại học', 'SỰ THẬT'],
            ['Không vào được THPT là thất bại', 'ĐỊNH KIẾN'],
            ['Nhiều thợ giỏi thu nhập cao hơn cử nhân', 'SỰ THẬT'],
            ['Nghề thủ công không có tương lai', 'ĐỊNH KIẾN'],
        ], 'Phân biệt sự thật và định kiến giúp chọn hướng đi tỉnh táo.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm TRONG TẦM KIỂM SOÁT hoặc NGOÀI TẦM KIỂM SOÁT.', [
            ['Năng lực của bản thân', 'TRONG TẦM KIỂM SOÁT'],
            ['Điểm chuẩn của trường', 'NGOÀI TẦM KIỂM SOÁT'],
            ['Sự nỗ lực học tập', 'TRONG TẦM KIỂM SOÁT'],
            ['Hoàn cảnh gia đình', 'NGOÀI TẦM KIỂM SOÁT'],
            ['Thái độ với nghề nghiệp', 'TRONG TẦM KIỂM SOÁT'],
            ['Chính sách tuyển sinh', 'NGOÀI TẦM KIỂM SOÁT'],
        ], 'Tập trung vào điều mình kiểm soát được thay vì lo lắng điều ngoài tầm.', $d);
        $this->sortQ($s, 'Kéo mỗi lựa chọn vào nhóm ĐI LÀM SỚM hoặc HỌC LÊN CAO.', [
            ['Học trung cấp nghề rồi đi làm', 'ĐI LÀM SỚM'],
            ['Học THPT rồi thi đại học', 'HỌC LÊN CAO'],
            ['Học việc tại xưởng sản xuất', 'ĐI LÀM SỚM'],
            ['Học cao đẳng rồi liên thông đại học', 'HỌC LÊN CAO'],
            ['Lấy chứng chỉ nghề rồi làm tự do', 'ĐI LÀM SỚM'],
            ['Học THPT rồi đi du học', 'HỌC LÊN CAO'],
        ], 'Đi làm sớm hay học lên cao đều tốt nếu phù hợp với mình.', $d);

        $this->fill($s, 'Sau lớp 9, ai muốn học nghề có thể đăng ký trường ___ cấp nghề.', [[0, 'trung']], 'Trung cấp nghề là hướng đi chính cho học sinh muốn học nghề sớm.', $d);
        $this->fill($s, 'Muốn học đại học thì bắt buộc phải tốt nghiệp trung học phổ ___.', [[0, 'thông']], 'Tốt nghiệp THPT là điều kiện bắt buộc để vào đại học.', $d);
        $this->fill($s, '___ thông là con đường học từ bậc thấp lên bậc cao hơn.', [[0, 'Liên']], 'Liên thông mở cơ hội lấy bằng cao hơn cho người học nghề.', $d);
        $this->fill($s, 'Chọn hướng đi cần dựa vào năng lực, sở thích và ___ kiện gia đình.', [[0, 'điều']], 'Hoàn cảnh gia đình là căn cứ thực tế không thể bỏ qua.', $d);
        $this->fill($s, 'Học bạ THCS được dùng để ___ tuyển vào nhiều trường nghề.', [[0, 'xét']], 'Xét học bạ là phương thức vào trường nghề phổ biến sau lớp 9.', $d);
    }

    private function seedLop92(): void
    {
        $s = 'tnhn-dinh-huong-nghe-nghiep-lop-9-2';
        $d = 'kho';

        $this->quiz($s, 'Vì sao nghề sáng tạo nội dung vẫn cần con người dù AI đã viết được?', [
            'Vì sáng tạo cần cảm xúc, trải nghiệm sống và cái nhìn văn hoá mà AI chưa có',
            'Vì AI không viết được tiếng Việt',
            'Vì dùng AI sáng tạo nội dung là phạm pháp',
            'Vì AI quá đắt nên không ai dùng',
        ], 0, 'AI là công cụ hỗ trợ; chiều sâu cảm xúc và văn hoá vẫn thuộc về con người.', $d);
        $this->quiz($s, 'Tự động hoá ảnh hưởng đến việc làm như thế nào?', [
            'Việc lặp đi lặp lại bị thay thế, việc cần tư duy được mở rộng',
            'Mọi nghề nghiệp đều biến mất hoàn toàn',
            'Không ảnh hưởng gì đến thị trường việc làm',
            'Chỉ ảnh hưởng đến nghề nông nghiệp',
        ], 0, 'Tự động hoá thay thế việc lặp lại nhưng tạo ra việc làm mới cần tư duy.', $d);
        $this->quiz($s, 'Trong thời đại AI, điều gì phân biệt người giỏi với người trung bình?', [
            'Kết hợp tư duy phản biện, kỹ năng số và thói quen học hỏi liên tục',
            'Chỉ cần dùng AI thật nhiều là đủ',
            'Chỉ cần bằng cấp cao là đủ',
            'Chỉ cần làm việc chăm chỉ như trước đây',
        ], 0, 'Lợi thế cạnh tranh nằm ở sự kết hợp giữa con người và công nghệ.', $d);
        $this->quiz($s, 'Một học sinh lớp 9 nên chuẩn bị gì cho những nghề còn chưa tồn tại?', [
            'Rèn nền tảng vững: tư duy, ngoại ngữ, tin học và thói quen học hỏi',
            'Chờ nghề mới xuất hiện rồi mới bắt đầu học',
            'Chỉ học thật giỏi một môn duy nhất',
            'Không cần chuẩn bị gì vì tương lai khó đoán',
        ], 0, 'Nền tảng vững giúp thích ứng với mọi nghề mới trong tương lai.', $d);
        $this->quiz($s, 'Điều nào KHÔNG phải biểu hiện của tinh thần học suốt đời?', [
            'Cho rằng ra trường là đã học xong, không cần học nữa',
            'Mỗi năm học thêm một kỹ năng mới',
            'Thường xuyên cập nhật công nghệ trong ngành',
            'Đọc sách chuyên môn đều đặn',
        ], 0, 'Học suốt đời nghĩa là không bao giờ coi việc học đã kết thúc.', $d);

        $this->matching($s, 'Nối mỗi công nghệ với ảnh hưởng của nó đến nghề nghiệp.', [
            ['AI tạo sinh', 'Viết, vẽ và lập trình nhanh hơn nhiều lần'],
            ['Robot công nghiệp', 'Thay thế việc lặp lại trong nhà máy'],
            ['Internet vạn vật', 'Tạo nghề quản lý dữ liệu thiết bị'],
            ['In 3D', 'Thiết kế và tạo mẫu sản phẩm nhanh, rẻ'],
        ], 'Mỗi công nghệ vừa thay thế việc cũ vừa sinh ra việc mới.', $d);
        $this->matching($s, 'Nối mỗi nghề tương lai với mô tả công việc.', [
            ['Kỹ sư dữ liệu', 'Biến dữ liệu thành quyết định kinh doanh'],
            ['Chuyên gia an ninh mạng', 'Bảo vệ hệ thống khỏi tấn công'],
            ['Người huấn luyện AI', 'Dạy AI làm đúng ý con người'],
            ['Nhà thiết kế trải nghiệm', 'Làm sản phẩm dễ dùng và đẹp'],
        ], 'Nghề tương lai xoay quanh dữ liệu, AI và trải nghiệm con người.', $d);
        $this->matching($s, 'Nối mỗi kỹ năng số với lợi ích nó mang lại.', [
            ['Tìm kiếm thông tin hiệu quả', 'Tự học mọi lúc, mọi nơi'],
            ['Bảo mật cơ bản', 'Tránh bị lừa đảo trên mạng'],
            ['Dùng thành thạo công cụ AI', 'Năng suất làm việc cao hơn'],
            ['Lập trình cơ bản', 'Hiểu cách công nghệ vận hành'],
        ], 'Kỹ năng số là hành trang không thể thiếu của mọi nghề.', $d);
        $this->matching($s, 'Nối mỗi tình huống với cách ứng xử đúng đắn.', [
            ['AI có thể làm bài tập thay bạn', 'Dùng AI để học hiểu, không để chép bài'],
            ['Thấy tin giả lan truyền trên mạng', 'Kiểm chứng trước khi chia sẻ'],
            ['Bạn rủ bỏ học vì AI làm hết mọi việc', 'Giữ vững việc học, coi AI là công cụ'],
            ['Muốn theo nghề công nghệ', 'Học tin học và ngoại ngữ ngay từ bây giờ'],
        ], 'Thái độ đúng với công nghệ quyết định bạn làm chủ hay bị thay thế.', $d);
        $this->matching($s, 'Nối mỗi nhóm công việc với mức độ rủi ro bị tự động hoá.', [
            ['Nhập liệu, sao chép giấy tờ', 'Rủi ro cao'],
            ['Chăm sóc người bệnh', 'Rủi ro thấp'],
            ['Tư vấn tâm lý', 'Rủi ro thấp'],
            ['Lái xe đường dài', 'Rủi ro trung bình đến cao'],
        ], 'Việc càng lặp lại, càng ít tương tác con người thì rủi ro càng cao.', $d);

        $this->sortQ($s, 'Kéo mỗi việc vào nhóm CON NGƯỜI LÀM TỐT HƠN hoặc AI LÀM TỐT HƠN.', [
            ['An ủi người đang buồn', 'CON NGƯỜI LÀM TỐT HƠN'],
            ['Tính toán hàng triệu con số', 'AI LÀM TỐT HƠN'],
            ['Ra quyết định đạo đức khó khăn', 'CON NGƯỜI LÀM TỐT HƠN'],
            ['Dịch văn bản thật nhanh', 'AI LÀM TỐT HƠN'],
            ['Sáng tạo ý tưởng hoàn toàn mới lạ', 'CON NGƯỜI LÀM TỐT HƠN'],
            ['Sắp xếp lịch trình tối ưu', 'AI LÀM TỐT HƠN'],
        ], 'Hiểu điểm mạnh của mỗi bên giúp kết hợp con người và AI hiệu quả.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm TƯ DUY HỌC SUỐT ĐỜI hoặc TƯ DUY CŨ.', [
            ['Mỗi năm học thêm một kỹ năng mới', 'TƯ DUY HỌC SUỐT ĐỜI'],
            ['Ra trường là ngừng học', 'TƯ DUY CŨ'],
            ['Theo dõi xu hướng của ngành mình', 'TƯ DUY HỌC SUỐT ĐỜI'],
            ['Sợ thay đổi, bám cách làm cũ', 'TƯ DUY CŨ'],
            ['Rút kinh nghiệm từ thất bại', 'TƯ DUY HỌC SUỐT ĐỜI'],
            ['Cho rằng bằng cấp là đủ cả đời', 'TƯ DUY CŨ'],
        ], 'Tư duy học suốt đời là chìa khoá thích ứng với thay đổi.', $d);
        $this->sortQ($s, 'Kéo mỗi nghề vào nhóm CẦN KỸ NĂNG SỐ CAO hoặc ÍT PHỤ THUỘC CÔNG NGHỆ SỐ.', [
            ['Lập trình viên', 'CẦN KỸ NĂNG SỐ CAO'],
            ['Thợ mộc truyền thống', 'ÍT PHỤ THUỘC CÔNG NGHỆ SỐ'],
            ['Chuyên gia marketing số', 'CẦN KỸ NĂNG SỐ CAO'],
            ['Nông dân trồng rau', 'ÍT PHỤ THUỘC CÔNG NGHỆ SỐ'],
            ['Nhà phân tích dữ liệu', 'CẦN KỸ NĂNG SỐ CAO'],
            ['Thợ cắt tóc', 'ÍT PHỤ THUỘC CÔNG NGHỆ SỐ'],
        ], 'Mức độ cần kỹ năng số khác nhau tùy từng nghề.', $d);
        $this->sortQ($s, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI về tương lai nghề nghiệp.', [
            ['AI sẽ thay thế mọi nghề trong 5 năm tới', 'SAI'],
            ['Người biết dùng AI có lợi thế cạnh tranh', 'ĐÚNG'],
            ['Học suốt đời chỉ dành cho người làm công nghệ', 'SAI'],
            ['Nghề mới sẽ xuất hiện cùng công nghệ mới', 'ĐÚNG'],
            ['Ngoại ngữ mất giá trị vì AI dịch đã tốt', 'SAI'],
            ['Tư duy phản biện ngày càng quan trọng', 'ĐÚNG'],
        ], 'Nhìn nhận đúng về AI giúp chuẩn bị tương lai tỉnh táo.', $d);
        $this->sortQ($s, 'Kéo mỗi kỹ năng vào nhóm KỸ NĂNG CỨNG hoặc KỸ NĂNG MỀM.', [
            ['Lập trình Python', 'KỸ NĂNG CỨNG'],
            ['Giao tiếp thuyết phục', 'KỸ NĂNG MỀM'],
            ['Phân tích dữ liệu', 'KỸ NĂNG CỨNG'],
            ['Làm việc nhóm', 'KỸ NĂNG MỀM'],
            ['Thiết kế đồ hoạ', 'KỸ NĂNG CỨNG'],
            ['Quản lý thời gian', 'KỸ NĂNG MỀM'],
        ], 'Tương lai cần cả kỹ năng cứng chuyên môn và kỹ năng mềm con người.', $d);

        $this->fill($s, 'Việc lặp đi lặp lại theo ___ trình cố định dễ bị máy móc thay thế nhất.', [[0, 'quy']], 'Quy trình càng cố định, máy móc càng dễ thay thế con người.', $d);
        $this->fill($s, 'Người biết ___ tác với AI sẽ có lợi thế hơn người không biết.', [[0, 'cộng']], 'Cộng tác với AI là kỹ năng cạnh tranh của tương lai.', $d);
        $this->fill($s, 'Tư duy ___ biện giúp bạn không bị tin giả đánh lừa trên mạng.', [[0, 'phản']], 'Tư duy phản biện là bộ lọc thông tin quan trọng thời đại số.', $d);
        $this->fill($s, 'Nghề phân tích ___ liệu và an ninh mạng đang thiếu nhân lực trầm trọng.', [[0, 'dữ']], 'Dữ liệu và an ninh mạng là hai lĩnh vực khát nhân lực hiện nay.', $d);
        $this->fill($s, 'Học suốt đời nghĩa là học ___ tục, không ngừng nghỉ sau khi ra trường.', [[0, 'liên']], 'Học liên tục giúp con người thích ứng với mọi thay đổi.', $d);
    }

    private function seedLop101(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-10-lop-10-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Ô "quan trọng nhưng không khẩn cấp" gồm những việc nào?', [
            'Ôn bài đều đặn, tập thể dục, xây dựng mối quan hệ',
            'Chữa cháy bài kiểm tra vào phút chót',
            'Lướt mạng xã hội hàng giờ',
            'Trả lời tin nhắn không quan trọng',
        ], 0, 'Ô 2 là nơi đầu tư cho tương lai: học đều, sức khoẻ, mối quan hệ.', $d);
        $this->quiz($s, 'Vì sao nhiều người bỏ qua ô quan trọng nhưng không khẩn cấp?', [
            'Vì không có áp lực deadline nên dễ trì hoãn',
            'Vì những việc đó không quan trọng',
            'Vì những việc đó không bao giờ cần làm',
            'Vì làm việc đó tốn quá nhiều tiền',
        ], 0, 'Thiếu deadline khiến việc quan trọng dài hạn dễ bị lùi vô thời hạn.', $d);
        $this->quiz($s, 'Việc "lướt mạng xã hội hàng giờ" thuộc ô nào của ma trận Eisenhower?', [
            'Không quan trọng và không khẩn cấp',
            'Quan trọng và khẩn cấp',
            'Quan trọng nhưng không khẩn cấp',
            'Không quan trọng nhưng khẩn cấp',
        ], 0, 'Lướt mạng vô định vừa không quan trọng vừa không khẩn cấp: ô 4, nên loại bỏ.', $d);
        $this->quiz($s, 'Giao việc cho người khác phù hợp nhất với ô nào?', [
            'Không quan trọng nhưng khẩn cấp',
            'Quan trọng và khẩn cấp',
            'Quan trọng nhưng không khẩn cấp',
            'Không quan trọng cũng không khẩn cấp',
        ], 0, 'Việc khẩn nhưng không quan trọng nên uỷ quyền để dành sức cho việc lớn.', $d);
        $this->quiz($s, 'Học sinh ôn thi: việc nào nên làm trước theo ma trận Eisenhower?', [
            'Ôn chương khó sắp thi',
            'Làm bài tập dễ để lấy tinh thần',
            'Xem phim giải trí cho đỡ căng',
            'Dọn bàn học thật kỹ rồi mới ôn',
        ], 0, 'Việc quan trọng và khẩn cấp luôn được ưu tiên làm ngay.', $d);

        $this->matching($s, 'Nối mỗi ô trong ma trận với tên gọi của nó.', [
            ['Quan trọng + khẩn cấp', 'Ô 1: Làm ngay'],
            ['Quan trọng + không khẩn cấp', 'Ô 2: Lên lịch'],
            ['Không quan trọng + khẩn cấp', 'Ô 3: Uỷ quyền'],
            ['Không quan trọng + không khẩn cấp', 'Ô 4: Loại bỏ'],
        ], 'Nhớ tên và cách xử lý của 4 ô là nền tảng dùng ma trận Eisenhower.', $d);
        $this->matching($s, 'Nối mỗi tình huống của học sinh với ô phù hợp.', [
            ['Bài kiểm tra ngày mai chưa ôn gì', 'Ô 1'],
            ['Ôn từ vựng tiếng Anh mỗi ngày', 'Ô 2'],
            ['Bạn rủ đi chơi khi sắp thi', 'Ô 3'],
            ['Cày game tới khuya', 'Ô 4'],
        ], 'Cùng một việc, đặt đúng ô sẽ biết nên làm ngay hay loại bỏ.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với tình trạng quản lý thời gian.', [
            ['Luôn trong trạng thái chữa cháy', 'Đang sống ở ô 1'],
            ['Có thời gian cho sức khoẻ và gia đình', 'Đang làm tốt ô 2'],
            ['Bận rộn cả ngày mà không hiệu quả', 'Đang kẹt ở ô 3'],
            ['Lãng phí thời gian vào việc vô ích', 'Đang sa đà ở ô 4'],
        ], 'Dấu hiệu hằng ngày cho biết bạn đang sống ở ô nào.', $d);
        $this->matching($s, 'Nối mỗi chiến lược với ô tương ứng.', [
            ['Làm ngay trong hôm nay', 'Ô 1'],
            ['Xếp lịch cụ thể trong tuần', 'Ô 2'],
            ['Nhờ người khác giúp hoặc từ chối khéo', 'Ô 3'],
            ['Xoá khỏi danh sách việc cần làm', 'Ô 4'],
        ], 'Mỗi ô có một chiến lược xử lý riêng, không lẫn lộn.', $d);
        $this->matching($s, 'Nối mỗi sai lầm sắp xếp ưu tiên với hậu quả.', [
            ['Chỉ làm việc khẩn cấp', 'Bỏ bê việc quan trọng dài hạn'],
            ['Ôm đồm mọi việc', 'Kiệt sức vì quá tải'],
            ['Không bao giờ dám từ chối', 'Mắc kẹt ở ô 3'],
            ['Trì hoãn việc quan trọng', 'Việc dồn lại thành khẩn cấp'],
        ], 'Sai lầm trong ưu tiên hôm nay thành khủng hoảng của ngày mai.', $d);

        $this->sortQ($s, 'Kéo mỗi việc vào nhóm KHẨN CẤP hoặc KHÔNG KHẨN CẤP.', [
            ['Nộp bài tập trong hôm nay', 'KHẨN CẤP'],
            ['Ôn thi cuối kỳ của tháng sau', 'KHÔNG KHẨN CẤP'],
            ['Cuộc họp bắt đầu sau 10 phút', 'KHẨN CẤP'],
            ['Đọc sách phát triển bản thân', 'KHÔNG KHẨN CẤP'],
            ['Trả lời tin nhắn cần gấp của thầy cô', 'KHẨN CẤP'],
            ['Tập thể dục đều đặn mỗi sáng', 'KHÔNG KHẨN CẤP'],
        ], 'Khẩn cấp là có deadline gần; không khẩn cấp là chưa cần ngay.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm QUAN TRỌNG hoặc KHÔNG QUAN TRỌNG.', [
            ['Ôn thi tốt nghiệp', 'QUAN TRỌNG'],
            ['Lướt tin tức giải trí', 'KHÔNG QUAN TRỌNG'],
            ['Giúp mẹ việc nhà', 'QUAN TRỌNG'],
            ['Cãi nhau với người lạ trên mạng', 'KHÔNG QUAN TRỌNG'],
            ['Chuẩn bị bài thuyết trình', 'QUAN TRỌNG'],
            ['Xem video hài liên tục hàng giờ', 'KHÔNG QUAN TRỌNG'],
        ], 'Quan trọng là việc tạo giá trị thật cho mục tiêu của bạn.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm THUỘC Ô 2 hoặc THUỘC Ô 4.', [
            ['Tập thể dục mỗi sáng', 'THUỘC Ô 2'],
            ['Lướt mạng vô định', 'THUỘC Ô 4'],
            ['Đọc sách mỗi tối', 'THUỘC Ô 2'],
            ['Chơi game thâu đêm', 'THUỘC Ô 4'],
            ['Học thêm một kỹ năng mới', 'THUỘC Ô 2'],
            ['Ngồi than vãn mà không làm gì', 'THUỘC Ô 4'],
        ], 'Ô 2 nuôi tương lai; ô 4 chỉ tiêu tốn thời gian.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm NÊN LÀM hoặc NÊN TRÁNH khi sắp xếp ưu tiên.', [
            ['Liệt kê việc theo mức quan trọng và khẩn cấp', 'NÊN LÀM'],
            ['Làm việc dễ trước để đỡ sợ việc khó', 'NÊN TRÁNH'],
            ['Dành giờ cố định mỗi tuần cho ô 2', 'NÊN LÀM'],
            ['Để việc quan trọng tới sát deadline mới làm', 'NÊN TRÁNH'],
            ['Học cách nói không với việc ô 3', 'NÊN LÀM'],
            ['Ôm luôn việc của người khác', 'NÊN TRÁNH'],
        ], 'Ưu tiên đúng là làm việc quan trọng trước, không phải việc dễ trước.', $d);
        $this->sortQ($s, 'Kéo mỗi tình huống vào nhóm XỬ LÝ ĐÚNG hoặc XỬ LÝ SAI theo Eisenhower.', [
            ['Bài thi ngày mai: ôn ngay tối nay', 'XỬ LÝ ĐÚNG'],
            ['Việc khẩn nhưng không quan trọng: tự làm hết một mình', 'XỬ LÝ SAI'],
            ['Ôn bài đều đặn mỗi ngày từ đầu kỳ', 'XỬ LÝ ĐÚNG'],
            ['Chơi game trước, bài tập để sát giờ mới làm', 'XỬ LÝ SAI'],
            ['Nhờ anh giúp việc nhà để tập trung ôn thi', 'XỬ LÝ ĐÚNG'],
            ['Nhận thêm việc khi đã quá tải', 'XỬ LÝ SAI'],
        ], 'Xử lý đúng là đặt mỗi việc vào ô phù hợp và hành động tương ứng.', $d);

        $this->fill($s, 'Ô 2 trong ma trận Eisenhower là việc quan trọng nhưng không ___ cấp.', [[0, 'khẩn']], 'Ô 2: quan trọng nhưng không khẩn cấp — nơi đầu tư cho tương lai.', $d);
        $this->fill($s, 'Việc khẩn cấp mà không quan trọng nên ___ quyền hoặc từ chối khéo.', [[0, 'ủy']], 'Uỷ quyền giải phóng thời gian cho việc thật sự quan trọng.', $d);
        $this->fill($s, 'Người quản lý thời gian tốt dành phần lớn thời gian cho ô ___.', [[0, '2']], 'Ô 2 càng được đầu tư, ô 1 (chữa cháy) càng ít xuất hiện.', $d);
        $this->fill($s, 'Khi mọi việc đều trở nên khẩn cấp, nghĩa là bạn đã bỏ bê ô ___ từ lâu.', [[0, '2']], 'Bỏ bê ô 2 khiến việc quan trọng dồn thành khẩn cấp.', $d);
        $this->fill($s, 'Ma trận này mang tên Tổng thống Mỹ Dwight D. ___.', [[0, 'Eisenhower']], 'Eisenhower nổi tiếng với khả năng sắp xếp ưu tiên công việc.', $d);
    }

    private function seedLop102(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-10-lop-10-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Trong phiên Pomodoro 25 phút, điều quan trọng nhất là gì?', [
            'Tập trung tuyệt đối, không đụng tới điện thoại hay việc khác',
            'Vừa học vừa nghe nhạc có lời',
            'Tranh thủ trả lời vài tin nhắn',
            'Học thật nhanh để xong trước 25 phút',
        ], 0, 'Pomodoro chỉ hiệu quả khi 25 phút là 25 phút tập trung hoàn toàn.', $d);
        $this->quiz($s, 'Trì hoãn vì sợ thất bại nên khắc phục bằng cách nào?', [
            'Chia việc thành bước nhỏ và cho phép mình làm chưa hoàn hảo lần đầu',
            'Chờ đến khi chắc chắn thành công mới bắt đầu',
            'Bỏ luôn việc đó để khỏi sợ',
            'Làm thật nhanh cho xong dù ẩu',
        ], 0, 'Bắt đầu nhỏ và chấp nhận chưa hoàn hảo giúp vượt qua nỗi sợ.', $d);
        $this->quiz($s, 'Chiến thuật "Ăn ếch" (Eat the Frog) nghĩa là gì?', [
            'Làm việc khó nhất, quan trọng nhất ngay đầu ngày',
            'Ăn sáng thật no để có sức học',
            'Làm việc dễ trước để lấy tinh thần',
            'Để việc khó tới cuối ngày mới làm',
        ], 0, 'Xử lý "con ếch" khó nuốt nhất trước giúp cả ngày nhẹ nhàng.', $d);
        $this->quiz($s, 'Vì sao nghỉ ngắn 5 phút lại giúp học hiệu quả hơn?', [
            'Vì não được phục hồi, tránh quá tải',
            'Vì nghỉ giúp quên bớt kiến thức cũ',
            'Vì nghỉ càng nhiều càng nhớ lâu',
            'Vì não chỉ hoạt động tốt khi lười biếng',
        ], 0, 'Nghỉ ngắn đúng cách giúp não củng cố và sẵn sàng cho phiên tiếp theo.', $d);
        $this->quiz($s, 'Dấu hiệu nào cho thấy bạn đang trì hoãn chứ không phải nghỉ ngơi?', [
            'Lướt điện thoại dù biết có việc cần làm và cảm thấy tội lỗi',
            'Ngủ trưa 20 phút sau buổi học căng thẳng',
            'Đi dạo sau khi hoàn thành kế hoạch ngày',
            'Xem phim cuối tuần để thư giãn',
        ], 0, 'Trì hoãn đi kèm cảm giác tội lỗi; nghỉ ngơi thật sự giúp phục hồi.', $d);

        $this->matching($s, 'Nối mỗi kỹ thuật quản lý công việc với cách dùng.', [
            ['Pomodoro', 'Học 25 phút tập trung, nghỉ 5 phút'],
            ['Ăn ếch', 'Việc khó nhất làm đầu ngày'],
            ['Quy tắc 2 phút', 'Việc nhỏ làm ngay lập tức'],
            ['Chia nhỏ công việc', 'Bài lớn thành các bước nhỏ'],
        ], 'Mỗi kỹ thuật giải quyết một kiểu trì hoãn khác nhau.', $d);
        $this->matching($s, 'Nối mỗi nguyên nhân trì hoãn với giải pháp phù hợp.', [
            ['Việc quá lớn, không biết bắt đầu', 'Chia thành các bước nhỏ'],
            ['Sợ làm không tốt', 'Cho phép bản thân làm thử'],
            ['Không có deadline', 'Tự đặt hạn chót cho mình'],
            ['Môi trường nhiều xao nhãng', 'Dọn chỗ học, tắt thông báo'],
        ], 'Trị trì hoãn phải trị đúng nguyên nhân mới hiệu quả.', $d);
        $this->matching($s, 'Nối mỗi hành động trong giờ nghỉ với tác dụng của nó.', [
            ['Đứng dậy vươn vai', 'Giảm mỏi người'],
            ['Uống một cốc nước', 'Giúp tỉnh táo'],
            ['Nhìn ra xa', 'Mắt đỡ mỏi'],
            ['Hít thở sâu vài lần', 'Giảm căng thẳng'],
        ], 'Nghỉ ngơi đúng cách giúp phiên học tiếp theo hiệu quả hơn.', $d);
        $this->matching($s, 'Nối mỗi sai lầm khi dùng Pomodoro với cách sửa.', [
            ['Nghỉ 5 phút thành lướt điện thoại 1 giờ', 'Hẹn giờ báo thức cho giờ nghỉ'],
            ['Học 3 giờ liền không nghỉ', 'Chia thành các phiên Pomodoro'],
            ['Vừa học vừa nhắn tin', 'Để điện thoại xa tầm tay'],
            ['Lên kế hoạch quá tham vọng', 'Giảm mục tiêu cho vừa sức mỗi ngày'],
        ], 'Pomodoro chỉ hiệu quả khi tuân thủ đúng kỷ luật của nó.', $d);
        $this->matching($s, 'Nối mỗi câu nói quen thuộc với ý nghĩa thật của nó.', [
            ['Ngày mai làm cũng được', 'Cái bẫy trì hoãn kinh điển'],
            ['Chỉ 5 phút nữa thôi', 'Khó dừng lại khi đã sa đà'],
            ['Mình không có động lực', 'Động lực đến sau khi bắt đầu'],
            ['Làm xong việc khó trước đã', 'Chiến thuật ăn ếch'],
        ], 'Nhận diện được "giọng nói trì hoãn" giúp bạn không bị nó dắt mũi.', $d);

        $this->sortQ($s, 'Kéo mỗi việc của học sinh vào nhóm NÊN DÙNG POMODORO hoặc KHÔNG CẦN.', [
            ['Ôn 3 chương Lịch sử', 'NÊN DÙNG POMODORO'],
            ['Rửa bát trong 5 phút', 'KHÔNG CẦN'],
            ['Viết bài văn 800 chữ', 'NÊN DÙNG POMODORO'],
            ['Trả lời tin nhắn của bạn', 'KHÔNG CẦN'],
            ['Học 50 từ vựng tiếng Anh', 'NÊN DÙNG POMODORO'],
            ['Xếp sách lên kệ', 'KHÔNG CẦN'],
        ], 'Pomodoro dành cho việc cần tập trung sâu, không phải việc vặt 5 phút.', $d);
        $this->sortQ($s, 'Kéo mỗi thói quen vào nhóm CHỐNG TRÌ HOÃN hoặc NUÔI TRÌ HOÃN.', [
            ['Chuẩn bị bàn học từ tối hôm trước', 'CHỐNG TRÌ HOÃN'],
            ['Để điện thoại ngay cạnh khi học', 'NUÔI TRÌ HOÃN'],
            ['Viết 3 việc quan trọng mỗi sáng', 'CHỐNG TRÌ HOÃN'],
            ['Mở máy là lướt mạng trước', 'NUÔI TRÌ HOÃN'],
            ['Hẹn giờ cho từng phiên học', 'CHỐNG TRÌ HOÃN'],
            ['Chờ có hứng mới bắt đầu học', 'NUÔI TRÌ HOÃN'],
        ], 'Thói quen hằng ngày quyết định bạn thắng hay thua trì hoãn.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về kỹ thuật Pomodoro.', [
            ['Có thể điều chỉnh 25 phút thành 50 phút nếu thấy hợp', 'ĐÚNG'],
            ['Pomodoro bắt buộc đúng 25 phút, không được thay đổi', 'SAI'],
            ['Trong phiên Pomodoro không làm việc khác xen vào', 'ĐÚNG'],
            ['Nghỉ giữa phiên để check điện thoại cũng không sao', 'SAI'],
            ['Pomodoro giúp đo được thời gian tập trung thật của mình', 'ĐÚNG'],
            ['Càng nhiều phiên càng tốt, không cần nghỉ dài', 'SAI'],
        ], 'Hiểu đúng bản chất Pomodoro giúp áp dụng linh hoạt mà vẫn hiệu quả.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm NÊN ÁP DỤNG QUY TẮC 2 PHÚT hoặc NÊN LÊN KẾ HOẠCH.', [
            ['Gửi email xin tài liệu cho thầy', 'NÊN ÁP DỤNG QUY TẮC 2 PHÚT'],
            ['Ôn thi học kỳ', 'NÊN LÊN KẾ HOẠCH'],
            ['Dọn bàn học', 'NÊN ÁP DỤNG QUY TẮC 2 PHÚT'],
            ['Viết luận văn tốt nghiệp', 'NÊN LÊN KẾ HOẠCH'],
            ['Trả lời tin nhắn của thầy cô', 'NÊN ÁP DỤNG QUY TẮC 2 PHÚT'],
            ['Học lái xe', 'NÊN LÊN KẾ HOẠCH'],
        ], 'Việc nhỏ làm ngay; việc lớn cần kế hoạch — đừng lẫn lộn.', $d);
        $this->sortQ($s, 'Kéo mỗi cảm xúc vào nhóm DẤU HIỆU TRÌ HOÃN hoặc BÌNH THƯỜNG.', [
            ['Lo lắng khi nghĩ đến bài tập lớn', 'DẤU HIỆU TRÌ HOÃN'],
            ['Hào hứng bắt tay vào việc mới', 'BÌNH THƯỜNG'],
            ['Tội lỗi vì lướt điện thoại cả buổi tối', 'DẤU HIỆU TRÌ HOÃN'],
            ['Mệt sau một ngày học tập trung', 'BÌNH THƯỜNG'],
            ['Muốn làm việc nhà để tránh bài khó', 'DẤU HIỆU TRÌ HOÃN'],
            ['Vui vì hoàn thành đúng kế hoạch', 'BÌNH THƯỜNG'],
        ], 'Nhận diện cảm xúc giúp phát hiện trì hoãn từ sớm.', $d);

        $this->fill($s, 'Kỹ thuật Pomodoro do Francesco ___ phát minh, lấy tên từ chiếc đồng hồ hình quả cà chua.', [[0, 'Cirillo']], 'Cirillo dùng đồng hồ cà chua để chia thời gian học thành từng phiên.', $d);
        $this->fill($s, 'Trong phiên Pomodoro, điện thoại nên để ở chế độ im ___.', [[0, 'lặng']], 'Im lặng điện thoại là cách đơn giản nhất để bảo vệ sự tập trung.', $d);
        $this->fill($s, 'Khi việc quá lớn gây trì hoãn, hãy ___ việc thành các bước nhỏ 10 – 15 phút.', [[0, 'chia']], 'Chia nhỏ biến việc khổng lồ thành những bước dễ bắt đầu.', $d);
        $this->fill($s, 'Động lực thường đến ___ khi bạn đã bắt đầu làm, chứ không phải trước đó.', [[0, 'sau']], 'Đừng chờ có hứng mới làm; hãy làm để tạo ra hứng.', $d);
        $this->fill($s, 'Quy tắc 2 phút giúp bạn không ___ đọng việc nhỏ trong ngày.', [[0, 'tồn']], 'Việc nhỏ làm ngay thì không bao giờ thành núi việc tồn đọng.', $d);
    }

    private function seedLop103(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-10-lop-10-3';
        $d = 'trung_binh';

        $this->quiz($s, 'Phần tóm tắt (summary) trong ghi chú Cornell nằm ở đâu?', [
            'Cuối trang, viết sau khi buổi học kết thúc',
            'Đầu trang, viết trước khi vào học',
            'Cột bên trái của trang giấy',
            'Mặt sau của trang giấy',
        ], 0, 'Tóm tắt viết sau buổi học giúp củng cố toàn bộ kiến thức vừa ghi.', $d);
        $this->quiz($s, 'Vì sao nên ôn lại ghi chú Cornell trong vòng 24 giờ?', [
            'Vì lúc đó trí nhớ còn tươi, dễ bổ sung và nhớ lâu hơn',
            'Vì sau 24 giờ vở sẽ bị mất',
            'Vì thầy cô kiểm tra vở sau 24 giờ',
            'Vì đó là quy định bắt buộc của phương pháp',
        ], 0, 'Ôn sớm tận dụng trí nhớ còn tươi để khắc sâu kiến thức.', $d);
        $this->quiz($s, 'Kỹ thuật Feynman gồm mấy bước cơ bản?', [
            '4 bước: chọn khái niệm, giải thích đơn giản, tìm chỗ vấp, đơn giản hoá',
            '2 bước: đọc và chép lại',
            '6 bước: nghe, ghi, đọc, viết, nói, thi',
            '1 bước: học thuộc lòng',
        ], 0, 'Bốn bước Feynman biến kiến thức thành thứ bạn thật sự hiểu.', $d);
        $this->quiz($s, 'Khi giải thích theo Feynman mà phải dùng nhiều thuật ngữ khó, điều đó cho thấy gì?', [
            'Bạn chưa thật sự hiểu, cần học lại chỗ đó',
            'Bạn đã hiểu rất sâu sắc',
            'Thuật ngữ khó chứng tỏ bạn giỏi',
            'Người nghe quá kém nên không hiểu',
        ], 0, 'Không diễn đạt được bằng lời đơn giản nghĩa là kiến thức còn hổng.', $d);
        $this->quiz($s, 'Cách ghi chú nào kém hiệu quả nhất?', [
            'Chép nguyên văn lời thầy mà không xử lý bằng suy nghĩ của mình',
            'Ghi ý chính bằng lời của mình',
            'Vẽ sơ đồ tóm tắt sau buổi học',
            'Ghi câu hỏi ở cột từ khoá để tự kiểm tra',
        ], 0, 'Chép máy móc không qua tư duy khiến não không ghi nhớ gì.', $d);

        $this->matching($s, 'Nối mỗi vùng của trang Cornell với vị trí của nó.', [
            ['Cột ghi chú chính', 'Bên phải, phần rộng nhất'],
            ['Cột từ khoá và câu hỏi', 'Bên trái, phần hẹp'],
            ['Phần tóm tắt', 'Dưới cùng của trang'],
            ['Tiêu đề bài học', 'Trên cùng của trang'],
        ], 'Bố cục 3 vùng rõ ràng là đặc trưng của ghi chú Cornell.', $d);
        $this->matching($s, 'Nối mỗi bước Feynman với việc cần làm.', [
            ['Chọn khái niệm', 'Viết tên chủ đề lên đầu trang giấy'],
            ['Giải thích đơn giản', 'Dạy lại như đang dạy một đứa trẻ'],
            ['Tìm chỗ vấp', 'Đánh dấu nơi mình lúng túng'],
            ['Đơn giản hoá', 'Dùng ví dụ đời thường dễ hiểu'],
        ], 'Bốn bước này biến kiến thức phức tạp thành đơn giản.', $d);
        $this->matching($s, 'Nối mỗi cách ôn bài với mức độ hiệu quả.', [
            ['Đọc lại vở nhiều lần', 'Thấp'],
            ['Tự đặt câu hỏi rồi trả lời', 'Cao'],
            ['Highlight cả trang sách', 'Thấp'],
            ['Vẽ sơ đồ tư duy từ trí nhớ', 'Cao'],
        ], 'Cách học chủ động luôn hiệu quả hơn đọc thụ động.', $d);
        $this->matching($s, 'Nối mỗi lỗi ghi chú với cách khắc phục.', [
            ['Chép nguyên văn lời thầy', 'Ghi lại bằng lời của mình'],
            ['Không bao giờ xem lại vở', 'Ôn lại trong vòng 24 giờ'],
            ['Ghi lộn xộn không cấu trúc', 'Dùng mẫu Cornell chia vùng'],
            ['Chỉ ghi chữ, không có hình', 'Thêm sơ đồ và mũi tên minh hoạ'],
        ], 'Sửa đúng lỗi giúp ghi chú trở thành công cụ học tập thật sự.', $d);
        $this->matching($s, 'Nối mỗi công cụ học tập với cách dùng phù hợp.', [
            ['Sơ đồ tư duy', 'Hệ thống hoá kiến thức một chương'],
            ['Flashcard', 'Ôn từ vựng và công thức'],
            ['Ghi chú Cornell', 'Ghi bài ngay trên lớp'],
            ['Nhật ký học tập', 'Theo dõi tiến độ mỗi ngày'],
        ], 'Mỗi công cụ có thế mạnh riêng, dùng đúng lúc mới hiệu quả.', $d);

        $this->sortQ($s, 'Kéo mỗi nội dung vào nhóm NÊN GHI hoặc KHÔNG NÊN GHI vào cột ghi chú chính.', [
            ['Ý chính thầy nhấn mạnh', 'NÊN GHI'],
            ['Câu chuyện vui ngoài lề', 'KHÔNG NÊN GHI'],
            ['Ví dụ minh hoạ bài học', 'NÊN GHI'],
            ['Lời chào đầu giờ của thầy', 'KHÔNG NÊN GHI'],
            ['Công thức quan trọng', 'NÊN GHI'],
            ['Bình luận của bạn ngồi cạnh', 'KHÔNG NÊN GHI'],
        ], 'Cột ghi chú chính chỉ dành cho nội dung thật sự của bài học.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm HỌC THEO FEYNMAN hoặc KHÔNG PHẢI.', [
            ['Giải thích bài cho em nhỏ hiểu', 'HỌC THEO FEYNMAN'],
            ['Đọc thuộc lòng định nghĩa', 'KHÔNG PHẢI'],
            ['Dùng ví dụ đời thường để minh hoạ', 'HỌC THEO FEYNMAN'],
            ['Chép lại y nguyên sách giáo khoa', 'KHÔNG PHẢI'],
            ['Tìm chỗ mình vấp khi giảng lại', 'HỌC THEO FEYNMAN'],
            ['Học vẹt công thức', 'KHÔNG PHẢI'],
        ], 'Feynman là giải thích đơn giản, không phải học thuộc máy móc.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về ghi chú Cornell.', [
            ['Cột từ khoá dùng để tự kiểm tra sau buổi học', 'ĐÚNG'],
            ['Phần tóm tắt nên viết ngay khi thầy đang giảng', 'SAI'],
            ['Nên ôn lại ghi chú trong vòng 24 giờ', 'ĐÚNG'],
            ['Cột ghi chú chính nên chép nguyên văn lời thầy', 'SAI'],
            ['Trang Cornell giúp hệ thống kiến thức rõ ràng', 'ĐÚNG'],
            ['Chỉ môn Văn mới dùng được Cornell', 'SAI'],
        ], 'Hiểu đúng cách dùng Cornell giúp phát huy hết tác dụng của nó.', $d);
        $this->sortQ($s, 'Kéo mỗi kỹ thuật vào nhóm GHI NHỚ LÂU hoặc QUÊN NHANH.', [
            ['Tự kiểm tra từ trí nhớ', 'GHI NHỚ LÂU'],
            ['Đọc lướt nhiều lần', 'QUÊN NHANH'],
            ['Dạy lại cho người khác', 'GHI NHỚ LÂU'],
            ['Highlight rồi không xem lại', 'QUÊN NHANH'],
            ['Vẽ sơ đồ từ những gì còn nhớ', 'GHI NHỚ LÂU'],
            ['Nghe giảng một cách thụ động', 'QUÊN NHANH'],
        ], 'Chủ động truy xuất kiến thức giúp nhớ lâu hơn đọc thụ động.', $d);
        $this->sortQ($s, 'Kéo mỗi nội dung vào nhóm THUỘC CỘT TỪ KHOÁ hoặc THUỘC PHẦN TÓM TẮT.', [
            ['Câu hỏi: Vì sao lá cây có màu xanh?', 'THUỘC CỘT TỪ KHOÁ'],
            ['Bài này nói về quá trình quang hợp ở thực vật', 'THUỘC PHẦN TÓM TẮT'],
            ['Từ khoá: diệp lục', 'THUỘC CỘT TỪ KHOÁ'],
            ['Ý chính: cây cần ánh sáng để tạo chất hữu cơ', 'THUỘC PHẦN TÓM TẮT'],
            ['Câu hỏi: Quang hợp xảy ra ở đâu?', 'THUỘC CỘT TỪ KHOÁ'],
            ['Kết luận: quang hợp nuôi sống cây và sinh vật', 'THUỘC PHẦN TÓM TẮT'],
        ], 'Cột từ khoá để hỏi — phần tóm tắt để chốt lại ý chính.', $d);

        $this->fill($s, 'Cột từ khoá trong ghi chú Cornell nằm ở phía ___ của trang giấy.', [[0, 'trái']], 'Cột trái hẹp dành cho từ khoá và câu hỏi tự kiểm tra.', $d);
        $this->fill($s, 'Phần tóm tắt nên viết ___ khi buổi học kết thúc để củng cố trí nhớ.', [[0, 'ngay']], 'Viết tóm tắt ngay giúp chốt kiến thức khi trí nhớ còn tươi.', $d);
        $this->fill($s, 'Feynman khuyên hãy giải thích kiến thức như đang dạy cho một đứa ___.', [[0, 'trẻ']], 'Giải thích được cho trẻ con nghĩa là bạn đã hiểu thật sự.', $d);
        $this->fill($s, 'Ghi chú tốt là ghi bằng ___ của chính mình, không chép máy móc.', [[0, 'lời']], 'Diễn đạt bằng lời mình buộc não phải xử lý kiến thức.', $d);
        $this->fill($s, 'Ôn lại ghi chú trong vòng 24 ___ giúp nhớ lâu hơn nhiều.', [[0, 'giờ']], 'Ôn trong 24 giờ đầu là thời điểm vàng chống quên lãng.', $d);
    }

    private function seedLop104(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-10-lop-10-4';
        $d = 'kho';

        $this->quiz($s, 'Vì sao học dồn (cramming) vào đêm trước thi kém hiệu quả?', [
            'Vì kiến thức chỉ vào trí nhớ ngắn hạn, nhanh quên sau thi',
            'Vì học dồn giúp nhớ lâu hơn học đều',
            'Vì não không hoạt động vào ban đêm',
            'Vì học dồn vi phạm quy định thi cử',
        ], 0, 'Học dồn không cho não thời gian chuyển kiến thức vào trí nhớ dài hạn.', $d);
        $this->quiz($s, '"Hiệu ứng kiểm tra" (testing effect) nói lên điều gì?', [
            'Tự kiểm tra giúp nhớ lâu hơn đọc lại nhiều lần',
            'Làm bài kiểm tra chỉ để lấy điểm',
            'Càng ít kiểm tra càng nhớ lâu',
            'Kiểm tra chỉ phù hợp với môn Toán',
        ], 0, 'Chủ động truy xuất kiến thức củng cố đường dẫn trí nhớ mạnh mẽ.', $d);
        $this->quiz($s, 'Khi gặp câu khó trong phòng thi, chiến lược tốt nhất là gì?', [
            'Đánh dấu lại, làm câu dễ trước rồi quay lại sau',
            'Dành hết thời gian còn lại cho câu khó đó',
            'Bỏ trống và không quay lại nữa',
            'Nhìn bài bạn bên cạnh để tham khảo',
        ], 0, 'Làm câu dễ trước đảm bảo điểm chắc, câu khó xử lý khi còn thời gian.', $d);
        $this->quiz($s, 'Ôn tập ngắt quãng nên bắt đầu từ khi nào để hiệu quả nhất?', [
            'Càng sớm càng tốt, ngay từ đầu học kỳ',
            'Một tuần trước ngày thi',
            'Đêm trước ngày thi',
            'Sáng hôm thi',
        ], 0, 'Ngắt quãng cần thời gian dài với các khoảng ôn tăng dần.', $d);
        $this->quiz($s, 'Đêm trước ngày thi quan trọng nên làm gì?', [
            'Ngủ sớm, chuẩn bị đồ dùng, chỉ ôn nhẹ nhàng',
            'Thức trắng đêm để ôn hết mọi thứ',
            'Học thêm chương mới chưa từng đọc',
            'Chơi game tới khuya để giải toả căng thẳng',
        ], 0, 'Giấc ngủ đầy đủ giúp não củng cố trí nhớ và tỉnh táo ngày thi.', $d);

        $this->matching($s, 'Nối mỗi khoảng thời gian với việc ôn tập tương ứng.', [
            ['Sau 1 ngày học', 'Ôn lại lần thứ nhất'],
            ['Sau 1 tuần', 'Ôn lại lần thứ hai'],
            ['Sau 1 tháng', 'Ôn lại lần thứ ba'],
            ['Trước thi 1 ngày', 'Ôn tổng hợp nhẹ nhàng'],
        ], 'Các khoảng ôn tăng dần là bí quyết của ôn tập ngắt quãng.', $d);
        $this->matching($s, 'Nối mỗi chiến lược trong phòng thi với cách thực hiện.', [
            ['Đọc lướt toàn bộ đề', 'Nắm cấu trúc và phân bổ thời gian'],
            ['Làm câu dễ trước', 'Lấy chắc điểm số cơ bản'],
            ['Đánh dấu câu khó', 'Quay lại khi còn thời gian'],
            ['Kiểm tra cuối giờ', 'Soát lỗi sai sót đáng tiếc'],
        ], 'Chiến lược phòng thi giúp tận dụng tối đa thời gian làm bài.', $d);
        $this->matching($s, 'Nối mỗi sai lầm ôn thi với hậu quả của nó.', [
            ['Học dồn vào đêm trước thi', 'Quên nhanh ngay sau khi thi'],
            ['Chỉ đọc mà không viết ra', 'Tưởng nhớ nhưng thật ra không nhớ'],
            ['Thức khuya triền miên', 'Mất tập trung trong ngày thi'],
            ['Không làm đề thi thử', 'Bỡ ngỡ với áp lực thời gian'],
        ], 'Nhận diện sai lầm giúp điều chỉnh cách ôn kịp thời.', $d);
        $this->matching($s, 'Nối mỗi kỹ thuật ôn tập với nguyên lý khoa học đằng sau.', [
            ['Ôn tập ngắt quãng', 'Chống lại đường cong quên lãng'],
            ['Tự kiểm tra', 'Củng cố đường dẫn trí nhớ'],
            ['Học xen kẽ các môn', 'Phân biệt tốt hơn các dạng bài'],
            ['Ngủ đủ giấc', 'Não củng cố trí nhớ khi ngủ'],
        ], 'Kỹ thuật hiệu quả đều có cơ sở khoa học về trí nhớ.', $d);
        $this->matching($s, 'Nối mỗi dấu hiệu với ý nghĩa về mức độ thuộc bài.', [
            ['Nhớ được sau 1 tuần không ôn', 'Đã thuộc vào trí nhớ dài hạn'],
            ['Chỉ nhớ khi mở vở ra xem', 'Trí nhớ còn phụ thuộc gợi ý'],
            ['Giải được bài tương tự chưa gặp', 'Đã hiểu bản chất vấn đề'],
            ['Quên ngay sau khi gấp sách', 'Chưa thật sự học vào đầu'],
        ], 'Tự đánh giá trung thực giúp biết mình đã thuộc bài đến đâu.', $d);

        $this->sortQ($s, 'Kéo mỗi cách ôn vào nhóm ÔN CHỦ ĐỘNG hoặc ÔN THỤ ĐỘNG.', [
            ['Tự làm đề rồi tự chấm', 'ÔN CHỦ ĐỘNG'],
            ['Đọc lại vở 5 lần', 'ÔN THỤ ĐỘNG'],
            ['Giảng lại bài cho bạn nghe', 'ÔN CHỦ ĐỘNG'],
            ['Nghe thầy giảng lại từ đầu', 'ÔN THỤ ĐỘNG'],
            ['Vẽ sơ đồ từ trí nhớ', 'ÔN CHỦ ĐỘNG'],
            ['Xem video bài giảng ở tốc độ 2x', 'ÔN THỤ ĐỘNG'],
        ], 'Ôn chủ động buộc não làm việc nên nhớ lâu hơn.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm ĐÚNG hoặc SAI khi làm bài trong phòng thi.', [
            ['Đọc kỹ yêu cầu đề trước khi làm', 'ĐÚNG'],
            ['Dành nửa thời gian cho một câu khó', 'SAI'],
            ['Ghi nhanh ý tưởng ra giấy nháp', 'ĐÚNG'],
            ['Nộp bài ngay khi làm xong, không kiểm tra', 'SAI'],
            ['Giữ bình tĩnh khi gặp câu lạ', 'ĐÚNG'],
            ['Nhìn bài bạn khi bí', 'SAI'],
        ], 'Kỷ luật phòng thi quyết định một phần không nhỏ kết quả.', $d);
        $this->sortQ($s, 'Kéo mỗi thói quen vào nhóm GIÚP NHỚ LÂU hoặc GÂY QUÊN NHANH.', [
            ['Ôn lại sau 1 ngày, 1 tuần, 1 tháng', 'GIÚP NHỚ LÂU'],
            ['Học dồn một đêm duy nhất', 'GÂY QUÊN NHANH'],
            ['Tự kiểm tra thường xuyên', 'GIÚP NHỚ LÂU'],
            ['Chỉ học khi sắp thi', 'GÂY QUÊN NHANH'],
            ['Ngủ đủ 7 – 8 tiếng mỗi đêm', 'GIÚP NHỚ LÂU'],
            ['Thức tới 2 giờ sáng để ôn bài', 'GÂY QUÊN NHANH'],
        ], 'Thói quen tốt nuôi trí nhớ dài hạn; thói quen xấu chỉ cho kết quả ngắn hạn.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm TUẦN CUỐI TRƯỚC THI hoặc ĐÊM TRƯỚC THI.', [
            ['Làm đề thi thử bấm giờ', 'TUẦN CUỐI TRƯỚC THI'],
            ['Đi ngủ sớm', 'ĐÊM TRƯỚC THI'],
            ['Hệ thống lại toàn bộ kiến thức', 'TUẦN CUỐI TRƯỚC THI'],
            ['Chuẩn bị bút và giấy tờ', 'ĐÊM TRƯỚC THI'],
            ['Hỏi thầy những chỗ chưa hiểu', 'TUẦN CUỐI TRƯỚC THI'],
            ['Ôn nhẹ nhàng rồi thư giãn', 'ĐÊM TRƯỚC THI'],
        ], 'Mỗi thời điểm có việc phù hợp; đừng học nặng vào đêm trước thi.', $d);
        $this->sortQ($s, 'Kéo mỗi cách làm vào nhóm CHIẾN LƯỢC ĐÚNG hoặc CHIẾN LƯỢC SAI với bài trắc nghiệm.', [
            ['Loại trừ đáp án sai trước khi chọn', 'CHIẾN LƯỢC ĐÚNG'],
            ['Để trống câu không biết', 'CHIẾN LƯỢC SAI'],
            ['Đọc hết các đáp án rồi mới chọn', 'CHIẾN LƯỢC ĐÚNG'],
            ['Chọn ngay đáp án đầu tiên thấy quen', 'CHIẾN LƯỢC SAI'],
            ['Đánh dấu câu phân vân để xem lại', 'CHIẾN LƯỢC ĐÚNG'],
            ['Đổi đáp án theo cảm tính cuối giờ', 'CHIẾN LƯỢC SAI'],
        ], 'Trắc nghiệm cũng cần chiến lược, không chỉ kiến thức.', $d);

        $this->fill($s, 'Học dồn vào đêm trước thi chỉ đưa kiến thức vào trí nhớ ___ hạn.', [[0, 'ngắn']], 'Trí nhớ ngắn hạn nhanh quên; cần ôn ngắt quãng để vào trí nhớ dài hạn.', $d);
        $this->fill($s, 'Tự kiểm tra giúp nhớ lâu hơn việc đọc lại nhiều ___.', [[0, 'lần']], 'Một lần tự kiểm tra hiệu quả hơn nhiều lần đọc thụ động.', $d);
        $this->fill($s, 'Khi bí câu khó, hãy đánh ___ lại và làm câu dễ trước.', [[0, 'dấu']], 'Đánh dấu giúp không bỏ sót câu khó khi quay lại.', $d);
        $this->fill($s, 'Giấc ngủ giúp não ___ cố trí nhớ đã học trong ngày.', [[0, 'củng']], 'Ngủ đủ giấc là một phần của chiến lược ôn thi khoa học.', $d);
        $this->fill($s, 'Ôn xen kẽ các môn giúp não phân ___ dạng bài tốt hơn.', [[0, 'biệt']], 'Học xen kẽ rèn khả năng phân biệt và vận dụng linh hoạt.', $d);
    }

    private function seedLop111(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-11-lop-11-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Nhóm Investigative (I) trong Holland phù hợp nhất với nghề nào?', [
            'Nhà khoa học, kỹ sư nghiên cứu trong phòng thí nghiệm',
            'Ca sĩ biểu diễn trên sân khấu',
            'Nhân viên bán hàng',
            'Thợ sửa xe máy',
        ], 0, 'Nhóm I thích tìm tòi, phân tích và giải quyết vấn đề khoa học.', $d);
        $this->quiz($s, 'Nhóm Social (S) có đặc điểm nổi bật nào?', [
            'Thích giúp đỡ, dạy dỗ và chăm sóc người khác',
            'Thích làm việc một mình với máy móc',
            'Thích cạnh tranh để thắng người khác',
            'Thích công việc lặp đi lặp lại',
        ], 0, 'Nhóm S hướng về con người: giáo viên, bác sĩ, nhân viên xã hội.', $d);
        $this->quiz($s, 'Nhóm Enterprising (E) phù hợp nhất với công việc nào?', [
            'Kinh doanh, quản lý và lãnh đạo đội nhóm',
            'Nghiên cứu trong phòng thí nghiệm',
            'Vẽ tranh và sáng tác nhạc',
            'Nhập liệu và lưu trữ hồ sơ',
        ], 0, 'Nhóm E thích thuyết phục, dẫn dắt và tạo ảnh hưởng.', $d);
        $this->quiz($s, 'Nhóm Conventional (C) hợp với người có tính cách nào?', [
            'Thích quy trình rõ ràng, cẩn thận và làm việc có hệ thống',
            'Thích mạo hiểm và phá vỡ mọi quy tắc',
            'Thích sáng tạo tự do không khuôn khổ',
            'Thích làm việc ngoài trời',
        ], 0, 'Nhóm C hợp với kế toán, thư ký, quản trị dữ liệu.', $d);
        $this->quiz($s, 'Mã Holland "SAE" của một người có nghĩa là gì?', [
            'Nhóm nổi trội nhất là S, tiếp theo là A rồi đến E',
            'Người đó chỉ thuộc đúng một nhóm S',
            'Người đó không thuộc nhóm nào cả',
            'Người đó phải làm 3 nghề cùng lúc',
        ], 0, 'Mã Holland xếp 2–3 nhóm nổi trội nhất theo thứ tự.', $d);

        $this->matching($s, 'Nối mỗi nhóm Holland với môi trường làm việc đặc trưng.', [
            ['R – Thực tế', 'Xưởng, công trường, phòng máy'],
            ['I – Nghiên cứu', 'Phòng thí nghiệm, viện nghiên cứu'],
            ['A – Nghệ thuật', 'Studio, sân khấu'],
            ['S – Xã hội', 'Trường học, bệnh viện'],
        ], 'Mỗi nhóm tính cách phát triển tốt trong môi trường phù hợp.', $d);
        $this->matching($s, 'Nối mỗi nhóm Holland với tính từ mô tả.', [
            ['E – Doanh nghiệp', 'Quyết đoán, giỏi thuyết phục'],
            ['C – Nghiệp vụ', 'Cẩn thận, ngăn nắp'],
            ['R – Thực tế', 'Khéo tay, thích hành động'],
            ['I – Nghiên cứu', 'Tò mò, tư duy logic'],
        ], 'Tính từ mô tả giúp nhận diện nhanh đặc trưng mỗi nhóm.', $d);
        $this->matching($s, 'Nối mỗi bạn học sinh với nhóm Holland phù hợp.', [
            ['Lan thích vẽ và sáng tác nhạc', 'A – Nghệ thuật'],
            ['Hùng mê tháo lắp máy móc', 'R – Thực tế'],
            ['Mai thích dạy em nhỏ học bài', 'S – Xã hội'],
            ['Nam mê làm thí nghiệm hoá học', 'I – Nghiên cứu'],
        ], 'Sở thích hằng ngày là manh mối nhận diện nhóm Holland.', $d);
        $this->matching($s, 'Nối mỗi nghề với nhóm Holland tương ứng.', [
            ['Kế toán', 'C – Nghiệp vụ'],
            ['Giám đốc kinh doanh', 'E – Doanh nghiệp'],
            ['Nhà báo', 'A – Nghệ thuật'],
            ['Y tá', 'S – Xã hội'],
        ], 'Xếp nghề vào nhóm giúp so sánh với tính cách của mình.', $d);
        $this->matching($s, 'Nối mỗi câu hỏi tự khám phá với nhóm Holland nó gợi ý.', [
            ['Bạn thích làm việc ngoài trời hay trong văn phòng?', 'R – Thực tế'],
            ['Bạn thích con số hay con chữ hơn?', 'C – Nghiệp vụ'],
            ['Bạn thích dẫn dắt hay hỗ trợ người khác?', 'E – Doanh nghiệp'],
            ['Bạn thích tìm tòi hay làm theo quy trình?', 'I – Nghiên cứu'],
        ], 'Tự hỏi đúng câu giúp định vị nhóm tính cách của mình.', $d);

        $this->sortQ($s, 'Kéo mỗi nghề vào đúng nhóm Holland: R, I hoặc A.', [
            ['Thợ cơ khí', 'R'],
            ['Nhà vật lý', 'I'],
            ['Hoạ sĩ', 'A'],
            ['Thợ điện', 'R'],
            ['Dược sĩ nghiên cứu', 'I'],
            ['Nhạc sĩ', 'A'],
        ], 'R làm với tay chân, I làm với trí óc nghiên cứu, A làm với sáng tạo.', $d);
        $this->sortQ($s, 'Kéo mỗi nghề vào đúng nhóm Holland: S, E hoặc C.', [
            ['Giáo viên', 'S'],
            ['Nhân viên kinh doanh', 'E'],
            ['Thủ kho', 'C'],
            ['Bác sĩ', 'S'],
            ['Quản lý dự án', 'E'],
            ['Nhân viên nhập liệu', 'C'],
        ], 'S hướng về con người, E hướng về lãnh đạo, C hướng về hệ thống.', $d);
        $this->sortQ($s, 'Kéo mỗi đặc điểm vào nhóm HƯỚNG NGOẠI hoặc HƯỚNG NỘI.', [
            ['Thích thuyết trình trước đám đông', 'HƯỚNG NGOẠI'],
            ['Thích làm việc độc lập trong phòng lab', 'HƯỚNG NỘI'],
            ['Thích tổ chức sự kiện', 'HƯỚNG NGOẠI'],
            ['Thích đọc sách nghiên cứu một mình', 'HƯỚNG NỘI'],
            ['Thích đàm phán với khách hàng', 'HƯỚNG NGOẠI'],
            ['Thích sắp xếp hồ sơ ngăn nắp', 'HƯỚNG NỘI'],
        ], 'Hướng ngoại hay hướng nội đều có nhóm nghề phù hợp riêng.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về lý thuyết Holland.', [
            ['Mã Holland gồm 2 – 3 nhóm nổi trội nhất của một người', 'ĐÚNG'],
            ['Mỗi người chỉ thuộc đúng một nhóm duy nhất', 'SAI'],
            ['Chọn nghề hợp nhóm tính cách giúp gắn bó lâu dài hơn', 'ĐÚNG'],
            ['Nhóm R hợp với công việc văn phòng giấy tờ', 'SAI'],
            ['Nhóm A chỉ dành cho người có năng khiếu bẩm sinh', 'SAI'],
            ['Có thể làm trắc nghiệm Holland để tham khảo', 'ĐÚNG'],
        ], 'Hiểu đúng Holland giúp dùng nó như công cụ tham khảo hiệu quả.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm PHÙ HỢP NHÓM S hoặc PHÙ HỢP NHÓM I.', [
            ['Tình nguyện dạy học cho trẻ em', 'PHÙ HỢP NHÓM S'],
            ['Làm thí nghiệm khoa học', 'PHÙ HỢP NHÓM I'],
            ['Chăm sóc người bệnh', 'PHÙ HỢP NHÓM S'],
            ['Giải câu đố logic khó', 'PHÙ HỢP NHÓM I'],
            ['Tư vấn tâm lý cho bạn bè', 'PHÙ HỢP NHÓM S'],
            ['Quan sát bầu trời bằng kính thiên văn', 'PHÙ HỢP NHÓM I'],
        ], 'Hoạt động yêu thích tiết lộ nhóm tính cách của bạn.', $d);

        $this->fill($s, 'Nhóm I trong Holland là nhóm ___ cứu, thích tìm tòi và phân tích.', [[0, 'nghiên']], 'Nhóm Investigative là nhóm nghiên cứu, khám phá.', $d);
        $this->fill($s, 'Nhóm E phù hợp với người thích kinh doanh và ___ đạo.', [[0, 'lãnh']], 'Enterprising là nhóm doanh nghiệp, lãnh đạo.', $d);
        $this->fill($s, 'Nhóm C thích công việc có quy trình rõ ràng và ___ tiết chính xác.', [[0, 'chi']], 'Conventional là nhóm nghiệp vụ, coi trọng chi tiết.', $d);
        $this->fill($s, 'Mã Holland viết tắt 6 nhóm là R I A S E ___.', [[0, 'C']], 'RIASEC là viết tắt của 6 nhóm tính cách Holland.', $d);
        $this->fill($s, 'Chọn nghề phù hợp nhóm tính cách giúp bạn gắn ___ lâu dài với nghề.', [[0, 'bó']], 'Phù hợp tính cách là nền tảng của sự gắn bó bền vững.', $d);
    }

    private function seedLop112(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-11-lop-11-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Lĩnh vực logistics là gì?', [
            'Vận chuyển, kho bãi và chuỗi cung ứng hàng hoá',
            'Thiết kế thời trang cao cấp',
            'Nghiên cứu vũ trụ',
            'Chăm sóc sắc đẹp',
        ], 0, 'Logistics giữ cho hàng hoá lưu thông từ nơi sản xuất đến tay người dùng.', $d);
        $this->quiz($s, 'Nghề "chuyên gia ESG" thuộc xu hướng nào?', [
            'Phát triển bền vững và bảo vệ môi trường',
            'Giải trí trực tuyến',
            'Thời trang nhanh',
            'Khai thác khoáng sản',
        ], 0, 'ESG là xu hướng doanh nghiệp phát triển gắn với môi trường và xã hội.', $d);
        $this->quiz($s, 'Lĩnh vực nông nghiệp công nghệ cao gồm nghề nào?', [
            'Kỹ sư nông nghiệp thông minh, chuyên gia drone nông nghiệp',
            'Thợ cày trâu truyền thống',
            'Người bán rau ở chợ',
            'Thợ sửa xe đạp',
        ], 0, 'Công nghệ đang biến nông nghiệp thành lĩnh vực hiện đại, cần nhân lực mới.', $d);
        $this->quiz($s, 'Vì sao lĩnh vực chăm sóc sức khoẻ luôn cần nhân lực?', [
            'Vì dân số già hoá và nhu cầu sức khoẻ ngày càng tăng',
            'Vì không ai muốn làm nghề y',
            'Vì nghề y không cần đào tạo',
            'Vì bệnh viện đang đóng cửa bớt',
        ], 0, 'Già hoá dân số khiến nhu cầu nhân lực y tế tăng đều mỗi năm.', $d);
        $this->quiz($s, 'Nghề freelancer phản ánh đặc điểm nào của thị trường lao động hiện đại?', [
            'Linh hoạt, làm việc tự do không gò bó giờ giấc',
            'Mọi người đều phải làm công ty lớn',
            'Không cần kỹ năng gì cũng làm được',
            'Thu nhập luôn ổn định tuyệt đối',
        ], 0, 'Freelancer là xu hướng làm việc linh hoạt của thời đại số.', $d);

        $this->matching($s, 'Nối mỗi lĩnh vực với nghề tiêu biểu của nó.', [
            ['Tài chính – ngân hàng', 'Chuyên viên tín dụng'],
            ['Du lịch – khách sạn', 'Hướng dẫn viên du lịch'],
            ['Xây dựng', 'Kỹ sư công trình'],
            ['Nông nghiệp', 'Kỹ sư trồng trọt'],
        ], 'Mỗi lĩnh vực chứa nhiều nghề với đặc thù riêng.', $d);
        $this->matching($s, 'Nối tiếp mỗi lĩnh vực với nghề tiêu biểu của nó.', [
            ['Luật', 'Luật sư'],
            ['Truyền thông', 'Biên tập viên'],
            ['Môi trường', 'Chuyên gia xử lý nước thải'],
            ['Thể thao', 'Huấn luyện viên'],
        ], 'Hiểu nghề tiêu biểu giúp hình dung rõ lĩnh vực.', $d);
        $this->matching($s, 'Nối mỗi nghề mới với lĩnh vực của nó.', [
            ['Streamer', 'Giải trí số'],
            ['Chuyên gia điều khiển drone', 'Công nghệ'],
            ['Huấn luyện viên AI', 'Trí tuệ nhân tạo'],
            ['Nhà sáng tạo nội dung', 'Truyền thông số'],
        ], 'Nghề mới ra đời cùng sự phát triển của công nghệ và thị trường.', $d);
        $this->matching($s, 'Nối mỗi xu hướng với nghề nghiệp nó tạo ra.', [
            ['Thương mại điện tử', 'Chuyên viên livestream bán hàng'],
            ['Kinh tế xanh', 'Kỹ sư năng lượng tái tạo'],
            ['Quan tâm sức khoẻ tinh thần', 'Nhà tâm lý trị liệu'],
            ['Đô thị hoá', 'Kỹ sư quy hoạch đô thị'],
        ], 'Xu hướng xã hội là nơi nghề mới được sinh ra.', $d);
        $this->matching($s, 'Nối mỗi kỹ năng với lĩnh vực cần nó nhất.', [
            ['Ngoại ngữ', 'Du lịch và xuất nhập khẩu'],
            ['Vẽ tay', 'Kiến trúc và thiết kế'],
            ['Kỹ năng thuyết trình', 'Kinh doanh và giáo dục'],
            ['Sơ cứu cơ bản', 'Y tế và thể thao'],
        ], 'Kỹ năng phù hợp lĩnh vực giúp bạn có lợi thế cạnh tranh.', $d);

        $this->sortQ($s, 'Kéo mỗi nghề vào nhóm DỊCH VỤ hoặc SẢN XUẤT.', [
            ['Nhân viên ngân hàng', 'DỊCH VỤ'],
            ['Công nhân may', 'SẢN XUẤT'],
            ['Tài xế công nghệ', 'DỊCH VỤ'],
            ['Thợ hàn', 'SẢN XUẤT'],
            ['Nhân viên bán hàng', 'DỊCH VỤ'],
            ['Kỹ sư cơ khí', 'SẢN XUẤT'],
        ], 'Dịch vụ phục vụ con người; sản xuất tạo ra sản phẩm.', $d);
        $this->sortQ($s, 'Kéo mỗi nghề vào nhóm LĨNH VỰC CÔNG hoặc LĨNH VỰC TƯ.', [
            ['Giáo viên trường công lập', 'LĨNH VỰC CÔNG'],
            ['Nhân viên công ty tư nhân', 'LĨNH VỰC TƯ'],
            ['Bác sĩ bệnh viện công', 'LĨNH VỰC CÔNG'],
            ['Kỹ sư công ty xây dựng', 'LĨNH VỰC TƯ'],
            ['Cán bộ phường, xã', 'LĨNH VỰC CÔNG'],
            ['Nhân viên ngân hàng thương mại', 'LĨNH VỰC TƯ'],
        ], 'Khu vực công ổn định; khu vực tư năng động — mỗi nơi một đặc điểm.', $d);
        $this->sortQ($s, 'Kéo mỗi nghề vào nhóm CẦN BẰNG CẤP CAO hoặc CẦN TAY NGHỀ GIỎI.', [
            ['Bác sĩ phẫu thuật', 'CẦN BẰNG CẤP CAO'],
            ['Thợ sửa xe máy', 'CẦN TAY NGHỀ GIỎI'],
            ['Luật sư', 'CẦN BẰNG CẤP CAO'],
            ['Thợ làm bánh', 'CẦN TAY NGHỀ GIỎI'],
            ['Giáo sư đại học', 'CẦN BẰNG CẤP CAO'],
            ['Thợ cắt tóc', 'CẦN TAY NGHỀ GIỎI'],
        ], 'Bằng cấp và tay nghề là hai con đường thành công khác nhau.', $d);
        $this->sortQ($s, 'Kéo mỗi xu hướng vào nhóm TẠO THÊM VIỆC LÀM hoặc THAY THẾ VIỆC LÀM.', [
            ['Kinh tế số', 'TẠO THÊM VIỆC LÀM'],
            ['Tự động hoá dây chuyền', 'THAY THẾ VIỆC LÀM'],
            ['Du lịch phục hồi', 'TẠO THÊM VIỆC LÀM'],
            ['AI thay thế nhập liệu', 'THAY THẾ VIỆC LÀM'],
            ['Năng lượng tái tạo', 'TẠO THÊM VIỆC LÀM'],
            ['Robot thay thu ngân', 'THAY THẾ VIỆC LÀM'],
        ], 'Xu hướng vừa thay thế việc cũ vừa tạo ra việc mới.', $d);
        $this->sortQ($s, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI về các lĩnh vực nghề nghiệp.', [
            ['Mọi lĩnh vực đều cần kỹ năng số ở mức nào đó', 'ĐÚNG'],
            ['Nghề truyền thống sẽ biến mất hoàn toàn', 'SAI'],
            ['Chọn lĩnh vực đang hot là đủ, không cần hợp bản thân', 'SAI'],
            ['Một lĩnh vực có thể chứa nhiều nghề khác nhau', 'ĐÚNG'],
            ['Lĩnh vực y tế chỉ cần mỗi bác sĩ', 'SAI'],
            ['Hiểu lĩnh vực giúp chọn nghề thực tế hơn', 'ĐÚNG'],
        ], 'Hiểu đúng về lĩnh vực giúp chọn nghề sáng suốt.', $d);

        $this->fill($s, 'Người làm nghề ___ do nhận việc qua mạng, không gò bó giờ giấc.', [[0, 'tự']], 'Nghề tự do là xu hướng làm việc linh hoạt thời đại số.', $d);
        $this->fill($s, 'Chuỗi ___ ứng gồm sản xuất, vận chuyển và phân phối hàng hoá.', [[0, 'cung']], 'Chuỗi cung ứng là huyết mạch của nền kinh tế.', $d);
        $this->fill($s, 'Kinh tế ___ đang tạo ra nghề kỹ sư điện gió, điện mặt trời.', [[0, 'xanh']], 'Kinh tế xanh mở ra nhiều nghề mới thân thiện môi trường.', $d);
        $this->fill($s, 'Dân số ___ hoá khiến nhu cầu nhân lực y tế tăng cao.', [[0, 'già']], 'Già hoá dân số là xu hướng tạo việc làm bền vững cho ngành y.', $d);
        $this->fill($s, 'Nghề streamer thuộc lĩnh vực giải trí ___.', [[0, 'số']], 'Giải trí số là lĩnh vực nghề mới của thời đại internet.', $d);
    }

    private function seedLop113(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-11-lop-11-3';
        $d = 'trung_binh';

        $this->quiz($s, 'Tính cách khác sở thích ở điểm nào?', [
            'Tính cách ổn định lâu dài, còn sở thích có thể thay đổi theo thời gian',
            'Tính cách thay đổi mỗi ngày, sở thích thì không đổi',
            'Sở thích quan trọng hơn tính cách',
            'Hai khái niệm này hoàn toàn giống nhau',
        ], 0, 'Tính cách là nền tảng ổn định; sở thích là điều bạn thích ở từng thời điểm.', $d);
        $this->quiz($s, 'Người hướng nội có hợp với nghề kinh doanh không?', [
            'Có thể, nếu rèn kỹ năng giao tiếp và chọn vai trò phù hợp',
            'Hoàn toàn không, hướng nội thì không kinh doanh được',
            'Chỉ hợp nếu đổi tính cách thành hướng ngoại',
            'Hướng nội chỉ hợp làm nghề kỹ thuật',
        ], 0, 'Tính cách không khoá chặt nghề nghiệp; kỹ năng có thể rèn luyện.', $d);
        $this->quiz($s, 'Vì sao năng lực quan trọng hơn sở thích khi chọn nghề?', [
            'Vì nghề đòi hỏi làm tốt việc được giao, không chỉ dừng ở thích',
            'Vì sở thích không mang lại niềm vui',
            'Vì năng lực giúp bạn nổi tiếng nhanh',
            'Vì sở thích không bao giờ thay đổi',
        ], 0, 'Thích mà không làm được thì khó trụ lâu với nghề.', $d);
        $this->quiz($s, 'Cách nào giúp phát hiện năng lực tiềm ẩn của bản thân?', [
            'Thử nhiều hoạt động khác nhau và quan sát kết quả',
            'Ngồi yên chờ năng lực tự bộc lộ',
            'Chỉ làm việc mình đã giỏi từ trước',
            'Hỏi thầy bói xem mình hợp nghề gì',
        ], 0, 'Năng lực tiềm ẩn chỉ lộ diện khi bạn dám thử thách mới.', $d);
        $this->quiz($s, '"Điểm mù" của bản thân được hiểu là gì?', [
            'Điều người khác thấy ở mình mà chính mình không nhận ra',
            'Chỗ không nhìn thấy khi lái xe',
            'Môn học mình bị điểm kém',
            'Nơi tối trong nhà',
        ], 0, 'Hỏi người xung quanh là cách tốt để phát hiện điểm mù.', $d);

        $this->matching($s, 'Nối mỗi khái niệm với ví dụ minh hoạ.', [
            ['Sở thích', 'Thích nghe nhạc mỗi buổi tối'],
            ['Năng lực', 'Giải toán nhanh và chính xác'],
            ['Tính cách', 'Kiên nhẫn, ít nóng giận'],
            ['Giá trị sống', 'Coi trọng sự trung thực'],
        ], 'Bốn khái niệm này hợp thành bức tranh toàn diện về con người.', $d);
        $this->matching($s, 'Nối mỗi tính cách với nghề phù hợp.', [
            ['Cẩn thận, tỉ mỉ', 'Kế toán'],
            ['Hoạt bát, thích giao tiếp', 'MC sự kiện'],
            ['Kiên nhẫn', 'Giáo viên mầm non'],
            ['Mạo hiểm, thích thử thách', 'Phi công'],
        ], 'Tính cách phù hợp giúp làm nghề nhẹ nhàng và bền vững hơn.', $d);
        $this->matching($s, 'Nối mỗi cách tự đánh giá với ưu điểm của nó.', [
            ['Viết nhật ký hằng ngày', 'Thấy rõ thói quen của mình'],
            ['Làm trắc nghiệm tính cách', 'Gợi ý dựa trên cơ sở khoa học'],
            ['Hỏi bạn bè nhận xét', 'Có góc nhìn từ bên ngoài'],
            ['Thử việc trong thực tế', 'Trải nghiệm trực tiếp nhất'],
        ], 'Kết hợp nhiều cách giúp tự đánh giá khách quan hơn.', $d);
        $this->matching($s, 'Nối mỗi biểu hiện với khái niệm tương ứng.', [
            ['Mê đá bóng từ nhỏ tới lớn', 'Sở thích'],
            ['Chạy nhanh nhất lớp', 'Năng lực'],
            ['Luôn đúng giờ trong mọi việc', 'Tính cách'],
            ['Ghét gian lận trong thi cử', 'Giá trị sống'],
        ], 'Phân loại đúng biểu hiện giúp hiểu mình chính xác hơn.', $d);
        $this->matching($s, 'Nối mỗi tình huống với lời khuyên phù hợp.', [
            ['Thích vẽ nhưng vẽ chưa đẹp', 'Rèn luyện thêm hoặc tìm nghề liên quan'],
            ['Giỏi Toán nhưng ghét số liệu khô khan', 'Cân nhắc kỹ trước khi chọn'],
            ['Hướng nội nhưng muốn làm MC', 'Rèn dần kỹ năng nói trước đám đông'],
            ['Chưa biết mình thích gì', 'Trải nghiệm nhiều hoạt động khác nhau'],
        ], 'Mỗi tình huống đều có hướng xử lý tích cực nếu hiểu rõ bản thân.', $d);

        $this->sortQ($s, 'Kéo mỗi mô tả vào nhóm TÍNH CÁCH hoặc SỞ THÍCH.', [
            ['Kiên trì theo đuổi mục tiêu', 'TÍNH CÁCH'],
            ['Thích sưu tầm tem', 'SỞ THÍCH'],
            ['Cởi mở với người lạ', 'TÍNH CÁCH'],
            ['Thích nấu ăn cuối tuần', 'SỞ THÍCH'],
            ['Cẩn thận trong mọi việc', 'TÍNH CÁCH'],
            ['Thích xem phim hành động', 'SỞ THÍCH'],
        ], 'Tính cách là cách bạn hành xử; sở thích là điều bạn thích làm.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm BẨM SINH hoặc RÈN LUYỆN ĐƯỢC.', [
            ['Chiều cao', 'BẨM SINH'],
            ['Kỹ năng giao tiếp', 'RÈN LUYỆN ĐƯỢC'],
            ['Màu mắt', 'BẨM SINH'],
            ['Tính kiên nhẫn', 'RÈN LUYỆN ĐƯỢC'],
            ['Năng khiếu âm nhạc', 'BẨM SINH'],
            ['Kỹ năng quản lý thời gian', 'RÈN LUYỆN ĐƯỢC'],
        ], 'Bẩm sinh là điểm xuất phát; rèn luyện quyết định bạn đi được bao xa.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về bản thân và nghề nghiệp.', [
            ['Sở thích có thể thay đổi theo thời gian', 'ĐÚNG'],
            ['Tính cách hoàn toàn không thể thay đổi chút nào', 'SAI'],
            ['Năng lực có thể rèn luyện nếu kiên trì', 'ĐÚNG'],
            ['Chỉ cần đam mê là đủ để thành công', 'SAI'],
            ['Hiểu mình giúp chọn nghề bền vững hơn', 'ĐÚNG'],
            ['Người hướng nội không thể làm lãnh đạo', 'SAI'],
        ], 'Hiểu đúng về bản thân giúp tránh những giới hạn tự đặt ra.', $d);
        $this->sortQ($s, 'Kéo mỗi hoạt động vào nhóm GIÚP HIỂU MÌNH hoặc CHƯA GIÚP NHIỀU.', [
            ['Viết nhật ký mỗi tối', 'GIÚP HIỂU MÌNH'],
            ['Lướt mạng xã hội giết thời gian', 'CHƯA GIÚP NHIỀU'],
            ['Tham gia câu lạc bộ', 'GIÚP HIỂU MÌNH'],
            ['So sánh mình với người khác', 'CHƯA GIÚP NHIỀU'],
            ['Làm trắc nghiệm tính cách', 'GIÚP HIỂU MÌNH'],
            ['Tránh mọi hoạt động tập thể', 'CHƯA GIÚP NHIỀU'],
        ], 'Hiểu mình đến từ quan sát và trải nghiệm, không phải so sánh.', $d);
        $this->sortQ($s, 'Kéo mỗi nghề vào nhóm HỢP NGƯỜI HƯỚNG NGOẠI hoặc HỢP NGƯỜI HƯỚNG NỘI.', [
            ['Nhân viên kinh doanh', 'HỢP NGƯỜI HƯỚNG NGOẠI'],
            ['Lập trình viên', 'HỢP NGƯỜI HƯỚNG NỘI'],
            ['Hướng dẫn viên du lịch', 'HỢP NGƯỜI HƯỚNG NGOẠI'],
            ['Nhà văn', 'HỢP NGƯỜI HƯỚNG NỘI'],
            ['MC sự kiện', 'HỢP NGƯỜI HƯỚNG NGOẠI'],
            ['Nhà nghiên cứu', 'HỢP NGƯỜI HƯỚNG NỘI'],
        ], 'Mỗi kiểu tính cách đều có những nghề phát huy thế mạnh.', $d);

        $this->fill($s, 'Tính cách ___ định hơn sở thích, ít thay đổi theo thời gian.', [[0, 'ổn']], 'Tính cách ổn định là nền tảng để chọn nghề lâu dài.', $d);
        $this->fill($s, 'Người khác có thể thấy được ___ mù của bạn mà chính bạn không nhận ra.', [[0, 'điểm']], 'Điểm mù cần góc nhìn từ bên ngoài mới phát hiện được.', $d);
        $this->fill($s, 'Năng lực tiềm ___ cần được thử thách mới bộc lộ rõ.', [[0, 'ẩn']], 'Đừng để năng lực tiềm ẩn ngủ quên vì ngại thử.', $d);
        $this->fill($s, 'Đam mê mà thiếu năng lực thì khó theo nghề ___ dài.', [[0, 'lâu']], 'Bền vững với nghề cần cả đam mê và năng lực.', $d);
        $this->fill($s, 'Tự đánh giá khách quan cần kết hợp nhìn nhận của bản thân và ___ xét của người khác.', [[0, 'nhận']], 'Nhận xét từ bên ngoài giúp bức tranh về mình đầy đủ hơn.', $d);
    }

    private function seedLop114(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-11-lop-11-4';
        $d = 'kho';

        $this->quiz($s, 'Xung đột giá trị trong công việc được hiểu là gì?', [
            'Khi công việc đòi hỏi điều trái với điều mình coi trọng',
            'Khi đồng nghiệp cãi nhau về lương',
            'Khi công ty bị thua lỗ',
            'Khi sếp và nhân viên bất đồng ý kiến',
        ], 0, 'Làm việc trái giá trị khiến bạn mệt mỏi dù lương có cao.', $d);
        $this->quiz($s, 'Vì sao mục tiêu "Tôi muốn giàu" không phải là mục tiêu SMART?', [
            'Vì không cụ thể, không đo được và không có thời hạn',
            'Vì giàu là điều xấu',
            'Vì mục tiêu phải luôn khiêm tốn',
            'Vì không ai đặt mục tiêu như vậy',
        ], 0, 'SMART yêu cầu cụ thể, đo được, khả thi, phù hợp và có thời hạn.', $d);
        $this->quiz($s, 'Chữ R (Relevant) trong SMART có nghĩa là gì?', [
            'Mục tiêu phù hợp với giá trị và mục tiêu lớn của bản thân',
            'Mục tiêu phải thật khó để thử thách',
            'Mục tiêu phải giống với bạn bè',
            'Mục tiêu phải được nhiều người biết',
        ], 0, 'Mục tiêu phù hợp (relevant) mới tạo động lực thật sự.', $d);
        $this->quiz($s, 'Khi giá trị "giúp đỡ người khác" và "thu nhập cao" có vẻ mâu thuẫn, nên làm gì?', [
            'Tìm nghề cân bằng cả hai hoặc sắp xếp ưu tiên theo từng giai đoạn',
            'Bỏ hẳn một trong hai giá trị',
            'Chọn bừa một giá trị rồi hối hận sau',
            'Từ bỏ cả hai để khỏi phải chọn',
        ], 0, 'Giá trị có thể sắp xếp ưu tiên; nhiều nghề dung hoà được cả hai.', $d);
        $this->quiz($s, 'Mục tiêu dài hạn khác ước mơ ở điểm nào?', [
            'Mục tiêu có kế hoạch và hành động cụ thể để đạt được',
            'Ước mơ luôn lớn hơn mục tiêu',
            'Mục tiêu chỉ dành cho người giàu',
            'Hai khái niệm này không khác gì nhau',
        ], 0, 'Ước mơ cộng kế hoạch hành động mới thành mục tiêu.', $d);

        $this->matching($s, 'Nối mỗi chữ trong SMART với câu hỏi kiểm tra.', [
            ['S – Cụ thể', 'Tôi muốn đạt điều gì một cách chính xác?'],
            ['M – Đo được', 'Làm sao biết mình đã đạt được?'],
            ['A – Khả thi', 'Tôi có đủ nguồn lực để làm không?'],
            ['T – Thời hạn', 'Khi nào thì phải hoàn thành?'],
        ], 'Bốn câu hỏi này biến mục tiêu mơ hồ thành SMART.', $d);
        $this->matching($s, 'Nối mỗi giá trị nghề nghiệp với nghề thể hiện rõ nó.', [
            ['Sáng tạo', 'Nhà thiết kế'],
            ['Giúp đỡ cộng đồng', 'Bác sĩ'],
            ['Tự do', 'Freelancer'],
            ['Ổn định', 'Công chức'],
        ], 'Mỗi nghề nuôi dưỡng một hệ giá trị khác nhau.', $d);
        $this->matching($s, 'Nối mỗi mục tiêu với đánh giá theo SMART.', [
            ['Đạt 8.0 IELTS trong 12 tháng', 'Mục tiêu SMART'],
            ['Học giỏi hơn', 'Chưa phải SMART'],
            ['Đọc 12 cuốn sách trong năm nay', 'Mục tiêu SMART'],
            ['Trở nên thành công', 'Chưa phải SMART'],
        ], 'So sánh giúp nhận ra mục tiêu nào còn mơ hồ.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn cuộc đời với mục tiêu phù hợp.', [
            ['Tuổi học sinh', 'Xây nền tảng kiến thức và kỹ năng'],
            ['Tuổi sinh viên', 'Rèn chuyên môn và trải nghiệm'],
            ['Mới đi làm', 'Tích luỹ kinh nghiệm thực tế'],
            ['Tuổi trung niên', 'Ổn định và cống hiến'],
        ], 'Mỗi giai đoạn có nhiệm vụ phát triển riêng.', $d);
        $this->matching($s, 'Nối mỗi sai lầm khi đặt mục tiêu với cách sửa.', [
            ['Mục tiêu quá chung chung', 'Làm cho cụ thể và đo được'],
            ['Không có thời hạn', 'Đặt deadline rõ ràng'],
            ['Mục tiêu của bố mẹ, không phải của mình', 'Đối chiếu với giá trị bản thân'],
            ['Đặt quá nhiều mục tiêu cùng lúc', 'Ưu tiên 1 – 3 mục tiêu chính'],
        ], 'Sửa đúng sai lầm giúp mục tiêu trở nên khả thi.', $d);

        $this->sortQ($s, 'Kéo mỗi mục tiêu vào nhóm NGẮN HẠN hoặc DÀI HẠN.', [
            ['Đạt 7.0 IELTS trong 6 tháng', 'NGẮN HẠN'],
            ['Trở thành bác sĩ sau 8 năm học', 'DÀI HẠN'],
            ['Hoàn thành bài tập trong tuần này', 'NGẮN HẠN'],
            ['Mở công ty riêng ở tuổi 35', 'DÀI HẠN'],
            ['Đọc xong 1 cuốn sách trong tháng', 'NGẮN HẠN'],
            ['Mua nhà ở tuổi 30', 'DÀI HẠN'],
        ], 'Mục tiêu dài hạn cần được chia thành mục tiêu ngắn hạn.', $d);
        $this->sortQ($s, 'Kéo mỗi giá trị vào nhóm HƯỚNG NỘI TÂM hoặc HƯỚNG NGOẠI CẢNH.', [
            ['Sự bình an trong tâm hồn', 'HƯỚNG NỘI TÂM'],
            ['Danh tiếng được nhiều người ngưỡng mộ', 'HƯỚNG NGOẠI CẢNH'],
            ['Cảm giác công việc có ý nghĩa', 'HƯỚNG NỘI TÂM'],
            ['Mức lương cao', 'HƯỚNG NGOẠI CẢNH'],
            ['Sự tự do sáng tạo', 'HƯỚNG NỘI TÂM'],
            ['Địa vị xã hội', 'HƯỚNG NGOẠI CẢNH'],
        ], 'Giá trị nội tâm bền vững hơn giá trị phụ thuộc bên ngoài.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về giá trị và mục tiêu.', [
            ['Giá trị nghề nghiệp của mỗi người có thể khác nhau', 'ĐÚNG'],
            ['Mục tiêu SMART đảm bảo chắc chắn 100% thành công', 'SAI'],
            ['Nên xem lại mục tiêu của mình định kỳ', 'ĐÚNG'],
            ['Giá trị vật chất luôn xấu hơn giá trị tinh thần', 'SAI'],
            ['Mục tiêu cần phù hợp với giá trị của bản thân', 'ĐÚNG'],
            ['Chỉ người lớn mới cần đặt mục tiêu', 'SAI'],
        ], 'Hiểu đúng giúp đặt mục tiêu thực tế và có động lực.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm XÂY DỰNG MỤC TIÊU hoặc THỰC HIỆN MỤC TIÊU.', [
            ['Viết mục tiêu ra giấy rõ ràng', 'XÂY DỰNG MỤC TIÊU'],
            ['Chia mục tiêu thành các bước nhỏ', 'THỰC HIỆN MỤC TIÊU'],
            ['Xác định thời hạn hoàn thành', 'XÂY DỰNG MỤC TIÊU'],
            ['Theo dõi tiến độ mỗi tuần', 'THỰC HIỆN MỤC TIÊU'],
            ['Tìm người cố vấn đồng hành', 'THỰC HIỆN MỤC TIÊU'],
            ['Làm rõ vì sao mục tiêu này quan trọng', 'XÂY DỰNG MỤC TIÊU'],
        ], 'Xây dựng đúng rồi thực hiện đều đặn mới chạm được mục tiêu.', $d);
        $this->sortQ($s, 'Kéo mỗi tình huống vào nhóm PHÙ HỢP GIÁ TRỊ hoặc TRÁI GIÁ TRỊ.', [
            ['Người coi trọng gia đình chọn việc gần nhà', 'PHÙ HỢP GIÁ TRỊ'],
            ['Người ghét gò bó làm việc giờ giấc cứng nhắc', 'TRÁI GIÁ TRỊ'],
            ['Người thích sáng tạo làm thiết kế tự do', 'PHÙ HỢP GIÁ TRỊ'],
            ['Người coi trọng trung thực phải nói dối khách hàng', 'TRÁI GIÁ TRỊ'],
            ['Người thích giúp người làm y tá', 'PHÙ HỢP GIÁ TRỊ'],
            ['Người cần ổn định làm nghề bấp bênh', 'TRÁI GIÁ TRỊ'],
        ], 'Sống và làm việc đúng giá trị mang lại sự bình an lâu dài.', $d);

        $this->fill($s, 'Chữ A trong SMART nghĩa là khả ___, mục tiêu phải trong tầm với.', [[0, 'thi']], 'Mục tiêu khả thi tạo động lực; mục tiêu viển vông gây nản chí.', $d);
        $this->fill($s, 'Mục tiêu không có thời hạn dễ bị ___ hoãn vô thời hạn.', [[0, 'trì']], 'Deadline là áp lực tích cực giúp mục tiêu thành hiện thực.', $d);
        $this->fill($s, 'Xung đột giá trị xảy ra khi công việc đòi hỏi điều ___ với điều bạn coi trọng.', [[0, 'trái']], 'Nhận diện xung đột giá trị sớm giúp tránh lựa chọn sai lầm.', $d);
        $this->fill($s, 'Ước mơ trở thành mục tiêu khi có kế hoạch và ___ động cụ thể.', [[0, 'hành']], 'Hành động cụ thể là cầu nối giữa ước mơ và hiện thực.', $d);
        $this->fill($s, 'Nên xem lại và điều chỉnh mục tiêu ___ kỳ, ví dụ mỗi 3 tháng.', [[0, 'định']], 'Xem lại định kỳ giúp mục tiêu luôn phù hợp với thực tế.', $d);
    }

    private function seedLop121(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-12-lop-12-1';
        $d = 'trung_binh';

        $this->quiz($s, '"Hiểu nghề" khi chọn ngành gồm những nội dung nào?', [
            'Công việc thực tế, thu nhập, cơ hội việc làm và yêu cầu của ngành',
            'Chỉ cần biết tên ngành nghe có hay không',
            'Chỉ cần biết điểm chuẩn của ngành',
            'Chỉ cần biết ngành có nhiều bạn đăng ký không',
        ], 0, 'Hiểu nghề sâu giúp tránh vỡ mộng sau khi vào học.', $d);
        $this->quiz($s, 'Vì sao cần tìm hiểu "điều kiện" khi chọn ngành?', [
            'Để biết ngành có phù hợp hoàn cảnh gia đình, sức khoẻ và tài chính không',
            'Để khoe với bạn bè mình hiểu biết',
            'Để chọn ngành khó nhất cho oai',
            'Điều kiện không quan trọng khi chọn ngành',
        ], 0, 'Ngành tốt mà vượt quá điều kiện gia đình sẽ thành gánh nặng.', $d);
        $this->quiz($s, 'Chọn ngành chỉ dựa vào điểm chuẩn năm trước có rủi ro gì?', [
            'Điểm chuẩn thay đổi theo số lượng thí sinh mỗi năm',
            'Điểm chuẩn các năm luôn giống hệt nhau',
            'Điểm chuẩn không liên quan đến việc trúng tuyển',
            'Không có rủi ro gì cả',
        ], 0, 'Điểm chuẩn chỉ mang tính tham khảo, không phải đảm bảo.', $d);
        $this->quiz($s, 'Khi thích hai ngành khác nhau, nên làm gì?', [
            'So sánh chi tiết, trải nghiệm thử và hỏi người trong ngành',
            'Tung đồng xu để quyết định cho nhanh',
            'Chọn ngành nào bạn thân chọn',
            'Để bố mẹ quyết định hộ',
        ], 0, 'So sánh dựa trên thông tin thật giúp quyết định sáng suốt.', $d);
        $this->quiz($s, 'Dấu hiệu nào cho thấy bạn đã chọn ngành quá vội vàng?', [
            'Không biết ngành học những gì và ra trường làm gì',
            'Đã đọc kỹ chương trình đào tạo',
            'Đã phỏng vấn sinh viên đang học ngành đó',
            'Đã so sánh nhiều ngành với nhau',
        ], 0, 'Không trả lời được "học gì, làm gì" nghĩa là chưa tìm hiểu kỹ.', $d);

        $this->matching($s, 'Nối mỗi chân kiềng chọn ngành với câu hỏi của nó.', [
            ['Hiểu mình', 'Tôi thích và giỏi điều gì?'],
            ['Hiểu nghề', 'Ngành này làm việc gì mỗi ngày?'],
            ['Hiểu điều kiện', 'Gia đình có lo được chi phí không?'],
            ['Hiểu xu hướng', 'Ngành này 10 năm tới sẽ ra sao?'],
        ], 'Bốn câu hỏi này tạo thành bộ khung chọn ngành vững chắc.', $d);
        $this->matching($s, 'Nối mỗi sai lầm chọn ngành với cách tránh.', [
            ['Chọn theo phong trào', 'Tìm hiểu kỹ bản thân mình'],
            ['Chỉ nhìn điểm chuẩn', 'Xem thêm nhu cầu nhân lực'],
            ['Nghe theo bạn bè', 'Tự quyết định có tham khảo'],
            ['Chọn ngành vì tên hay', 'Đọc chương trình đào tạo'],
        ], 'Biết sai lầm phổ biến giúp mình không lặp lại.', $d);
        $this->matching($s, 'Nối mỗi nguồn thông tin với độ tin cậy.', [
            ['Website chính thức của trường', 'Rất đáng tin'],
            ['Người đang học ngành đó', 'Đáng tham khảo'],
            ['Tin đồn trên mạng xã hội', 'Cần kiểm chứng'],
            ['Chuyên gia tư vấn tuyển sinh', 'Đáng tin nhưng cần đối chiếu'],
        ], 'Thông tin càng gần nguồn chính thức càng đáng tin.', $d);
        $this->matching($s, 'Nối mỗi ngành học với công việc thực tế sau tốt nghiệp.', [
            ['Sư phạm', 'Dạy học ở trường'],
            ['Y khoa', 'Khám chữa bệnh'],
            ['Công nghệ thông tin', 'Phát triển phần mềm'],
            ['Kế toán', 'Quản lý sổ sách tài chính'],
        ], 'Hình dung công việc thực tế giúp kiểm tra độ phù hợp.', $d);
        $this->matching($s, 'Nối mỗi câu hỏi với chân kiềng nó thuộc về.', [
            ['Tôi có chịu được áp lực không?', 'Hiểu mình'],
            ['Ngành này ra trường làm ở đâu?', 'Hiểu nghề'],
            ['Học phí ngành này bao nhiêu?', 'Hiểu điều kiện'],
            ['Tôi có đam mê với lĩnh vực này?', 'Hiểu mình'],
        ], 'Đặt câu hỏi đúng chân kiềng giúp tìm hiểu có hệ thống.', $d);

        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm TÌM HIỂU KỸ hoặc QUYẾT ĐỊNH VỘI.', [
            ['Đọc chương trình đào tạo của ngành', 'TÌM HIỂU KỸ'],
            ['Chọn vì tên ngành nghe hay', 'QUYẾT ĐỊNH VỘI'],
            ['Phỏng vấn sinh viên đang học ngành đó', 'TÌM HIỂU KỸ'],
            ['Đăng ký theo bạn thân', 'QUYẾT ĐỊNH VỘI'],
            ['Tham dự ngày hội tư vấn tuyển sinh', 'TÌM HIỂU KỸ'],
            ['Chọn ngành điểm chuẩn thấp cho dễ đậu', 'QUYẾT ĐỊNH VỘI'],
        ], 'Chọn ngành là quyết định nhiều năm, xứng đáng được tìm hiểu kỹ.', $d);
        $this->sortQ($s, 'Kéo mỗi yếu tố vào nhóm NÊN ƯU TIÊN hoặc KHÔNG NÊN ƯU TIÊN khi chọn ngành.', [
            ['Phù hợp với năng lực', 'NÊN ƯU TIÊN'],
            ['Lương cao dù không thích', 'KHÔNG NÊN ƯU TIÊN'],
            ['Đam mê với lĩnh vực', 'NÊN ƯU TIÊN'],
            ['Gần nhà cho tiện', 'KHÔNG NÊN ƯU TIÊN'],
            ['Cơ hội việc làm tốt', 'NÊN ƯU TIÊN'],
            ['Bạn bè cũng chọn ngành đó', 'KHÔNG NÊN ƯU TIÊN'],
        ], 'Ưu tiên đúng thứ tự giúp chọn ngành vừa hợp vừa bền vững.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về chọn ngành học.', [
            ['Không có ngành tốt tuyệt đối, chỉ có ngành phù hợp', 'ĐÚNG'],
            ['Ngành hot hôm nay chắc chắn hot 10 năm nữa', 'SAI'],
            ['Có thể đổi ngành nếu phát hiện không hợp', 'ĐÚNG'],
            ['Chọn ngành điểm chuẩn cao là chọn đúng', 'SAI'],
            ['Nên tham khảo nhiều người nhưng tự quyết định', 'ĐÚNG'],
            ['Học phí không quan trọng khi chọn ngành', 'SAI'],
        ], 'Tư duy đúng về chọn ngành giúp quyết định sáng suốt.', $d);
        $this->sortQ($s, 'Kéo mỗi thông tin vào nhóm CẦN TÌM HIỂU TRƯỚC hoặc CÓ THỂ TÌM SAU.', [
            ['Chương trình đào tạo', 'CẦN TÌM HIỂU TRƯỚC'],
            ['Học phí và học bổng', 'CẦN TÌM HIỂU TRƯỚC'],
            ['Câu lạc bộ sinh viên', 'CÓ THỂ TÌM SAU'],
            ['Cơ hội việc làm sau tốt nghiệp', 'CẦN TÌM HIỂU TRƯỚC'],
            ['Ký túc xá có đẹp không', 'CÓ THỂ TÌM SAU'],
            ['Yêu cầu đầu vào', 'CẦN TÌM HIỂU TRƯỚC'],
        ], 'Ưu tiên tìm hiểu điều ảnh hưởng trực tiếp đến quyết định.', $d);
        $this->sortQ($s, 'Kéo mỗi ngành vào nhóm KHỐI TỰ NHIÊN hoặc KHỐI XÃ HỘI (gợi ý tham khảo).', [
            ['Công nghệ thông tin', 'KHỐI TỰ NHIÊN'],
            ['Báo chí', 'KHỐI XÃ HỘI'],
            ['Y khoa', 'KHỐI TỰ NHIÊN'],
            ['Luật', 'KHỐI XÃ HỘI'],
            ['Kỹ thuật điện', 'KHỐI TỰ NHIÊN'],
            ['Du lịch', 'KHỐI XÃ HỘI'],
        ], 'Khối thi gợi ý hướng ôn tập, nhưng quan trọng vẫn là sự phù hợp.', $d);

        $this->fill($s, 'Ba chân kiềng chọn ngành: hiểu mình, hiểu nghề và hiểu ___ kiện.', [[0, 'điều']], 'Hiểu điều kiện giúp lựa chọn thực tế, không viển vông.', $d);
        $this->fill($s, 'Đọc ___ trình đào tạo giúp biết ngành học những môn gì.', [[0, 'chương']], 'Chương trình đào tạo là tài liệu quan trọng nhất khi tìm hiểu ngành.', $d);
        $this->fill($s, 'Điểm chuẩn chỉ mang tính tham ___, thay đổi theo từng năm.', [[0, 'khảo']], 'Đừng đặt cược tương lai chỉ vào con số điểm chuẩn năm trước.', $d);
        $this->fill($s, 'Chọn ngành vì tên nghe hay mà không tìm hiểu là quyết định ___ vàng.', [[0, 'vội']], 'Quyết định vội vàng dễ phải trả giá bằng nhiều năm học sai ngành.', $d);
        $this->fill($s, 'Ngày hội tư vấn tuyển sinh là dịp tốt để ___ đáp thắc mắc trực tiếp.', [[0, 'giải']], 'Hỏi trực tiếp giúp có thông tin chính xác và cập nhật.', $d);
    }

    private function seedLop122(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-12-lop-12-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Xét tuyển bằng kỳ thi đánh giá năng lực là gì?', [
            'Kỳ thi riêng do một số đại học tổ chức để tuyển sinh',
            'Kỳ thi tốt nghiệp THPT',
            'Bài kiểm tra IQ trên mạng',
            'Kỳ thi tuyển công chức',
        ], 0, 'Đánh giá năng lực là phương thức xét tuyển riêng của một số trường lớn.', $d);
        $this->quiz($s, 'Ưu tiên xét tuyển thẳng thường dành cho đối tượng nào?', [
            'Học sinh giỏi quốc gia, có giải thưởng hoặc chứng chỉ quốc tế',
            'Mọi học sinh đều được xét tuyển thẳng',
            'Chỉ dành cho con em cán bộ',
            'Ai nộp hồ sơ sớm thì được',
        ], 0, 'Xét tuyển thẳng ghi nhận thành tích nổi bật của thí sinh.', $d);
        $this->quiz($s, 'Khi so sánh hai trường cùng ngành, nên xem yếu tố nào?', [
            'Chương trình đào tạo, giảng viên, cơ hội việc làm và học phí',
            'Trường nào có cổng đẹp hơn',
            'Trường nào gần quán ăn ngon hơn',
            'Trường nào có đồng phục đẹp hơn',
        ], 0, 'Chất lượng đào tạo và cơ hội việc làm mới là điều cốt lõi.', $d);
        $this->quiz($s, '"Nguyện vọng 1" có ý nghĩa gì trong đăng ký xét tuyển?', [
            'Là lựa chọn ưu tiên nhất, được xét trước các nguyện vọng sau',
            'Là nguyện vọng duy nhất được đăng ký',
            'Là nguyện vọng dễ đậu nhất',
            'Là nguyện vọng bắt buộc của mọi thí sinh',
        ], 0, 'Xếp nguyện vọng theo thứ tự yêu thích thật sự để không tiếc nuối.', $d);
        $this->quiz($s, 'Nên đăng ký nguyện vọng như thế nào là hợp lý?', [
            'Đủ nhiều để có phương án dự phòng, xếp theo thứ tự yêu thích',
            'Chỉ đăng ký đúng 1 nguyện vọng cho quyết tâm',
            'Đăng ký càng nhiều càng tốt, không cần sắp xếp',
            'Nhờ người khác đăng ký hộ cho nhanh',
        ], 0, 'Nguyện vọng dự phòng là lưới an toàn cho mọi thí sinh.', $d);

        $this->matching($s, 'Nối mỗi phương thức xét tuyển với đối tượng phù hợp.', [
            ['Điểm thi tốt nghiệp THPT', 'Mọi thí sinh'],
            ['Xét học bạ', 'Học sinh có học bạ đẹp'],
            ['Thi đánh giá năng lực', 'Thí sinh dự thi riêng của trường'],
            ['Xét tuyển thẳng', 'Học sinh giỏi, có giải thưởng'],
        ], 'Chọn phương thức phát huy thế mạnh của bản thân.', $d);
        $this->matching($s, 'Nối mỗi thông tin về trường với nơi tra cứu đáng tin.', [
            ['Điểm chuẩn các năm', 'Website tuyển sinh của trường'],
            ['Học phí', 'Đề án tuyển sinh chính thức'],
            ['Chương trình đào tạo', 'Cổng thông tin đào tạo của trường'],
            ['Đánh giá của sinh viên', 'Diễn đàn và nhóm sinh viên'],
        ], 'Mỗi loại thông tin có nguồn tra cứu tin cậy riêng.', $d);
        $this->matching($s, 'Nối mỗi mốc thời gian với việc cần làm.', [
            ['Đầu năm lớp 12', 'Tìm hiểu trường và ngành'],
            ['Giữa năm học', 'Ôn thi và chuẩn bị hồ sơ'],
            ['Khi có lịch đăng ký', 'Đăng ký nguyện vọng cẩn thận'],
            ['Sau khi biết điểm', 'Điều chỉnh nguyện vọng'],
        ], 'Lộ trình rõ ràng giúp không bỏ lỡ mốc quan trọng.', $d);
        $this->matching($s, 'Nối mỗi loại trường với đặc điểm của nó.', [
            ['Đại học công lập', 'Học phí thấp, cạnh tranh cao'],
            ['Đại học tư thục', 'Học phí cao, cơ sở vật chất tốt'],
            ['Cao đẳng', 'Đào tạo thực hành, thời gian ngắn'],
            ['Trường quốc tế', 'Giảng dạy chủ yếu bằng tiếng Anh'],
        ], 'Hiểu đặc điểm từng loại trường giúp chọn đúng nhu cầu.', $d);
        $this->matching($s, 'Nối mỗi sai lầm với hậu quả khi chọn trường.', [
            ['Chỉ đăng ký 1 nguyện vọng', 'Rủi ro trượt hết mọi lựa chọn'],
            ['Không tìm hiểu học phí', 'Khó khăn tài chính khi nhập học'],
            ['Đăng ký theo cảm tính', 'Học ngành không phù hợp'],
            ['Bỏ lỡ hạn nộp hồ sơ', 'Mất cơ hội xét tuyển'],
        ], 'Sai lầm trong đăng ký có thể phải trả giá bằng cả năm chờ đợi.', $d);

        $this->sortQ($s, 'Kéo mỗi phương thức vào nhóm CHUNG CHO NHIỀU TRƯỜNG hoặc RIÊNG TỪNG TRƯỜNG.', [
            ['Điểm thi tốt nghiệp THPT', 'CHUNG CHO NHIỀU TRƯỜNG'],
            ['Kỳ thi đánh giá năng lực riêng', 'RIÊNG TỪNG TRƯỜNG'],
            ['Xét học bạ', 'CHUNG CHO NHIỀU TRƯỜNG'],
            ['Phỏng vấn tuyển sinh', 'RIÊNG TỪNG TRƯỜNG'],
            ['Xét chứng chỉ quốc tế', 'CHUNG CHO NHIỀU TRƯỜNG'],
            ['Bài kiểm tra năng khiếu vẽ', 'RIÊNG TỪNG TRƯỜNG'],
        ], 'Phương thức riêng cần tìm hiểu kỹ yêu cầu của từng trường.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm NÊN LÀM hoặc KHÔNG NÊN khi đăng ký nguyện vọng.', [
            ['Xếp theo thứ tự yêu thích thật sự', 'NÊN LÀM'],
            ['Xếp nguyện vọng dễ đậu lên đầu cho chắc', 'KHÔNG NÊN'],
            ['Có thêm nguyện vọng dự phòng', 'NÊN LÀM'],
            ['Đăng ký ngành mình không thích', 'KHÔNG NÊN'],
            ['Kiểm tra kỹ mã trường, mã ngành', 'NÊN LÀM'],
            ['Nhờ người khác đăng ký hộ không kiểm tra lại', 'KHÔNG NÊN'],
        ], 'Đăng ký cẩn thận tránh sai sót đáng tiếc.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về xét tuyển đại học.', [
            ['Mỗi trường có thể có nhiều phương thức xét tuyển', 'ĐÚNG'],
            ['Trúng tuyển nguyện vọng 1 thì không xét nguyện vọng sau', 'ĐÚNG'],
            ['Xét học bạ thì không cần thi tốt nghiệp THPT', 'SAI'],
            ['Nên tìm hiểu kỹ trước khi đăng ký', 'ĐÚNG'],
            ['Điểm chuẩn mọi năm đều giống nhau', 'SAI'],
            ['Có thể điều chỉnh nguyện vọng sau khi biết điểm', 'ĐÚNG'],
        ], 'Nắm rõ quy chế xét tuyển giúp tận dụng mọi cơ hội.', $d);
        $this->sortQ($s, 'Kéo mỗi tiêu chí vào nhóm QUAN TRỌNG hoặc ÍT QUAN TRỌNG khi chọn trường.', [
            ['Chất lượng đào tạo ngành mình chọn', 'QUAN TRỌNG'],
            ['Trường có view đẹp để chụp ảnh', 'ÍT QUAN TRỌNG'],
            ['Cơ hội việc làm sau tốt nghiệp', 'QUAN TRỌNG'],
            ['Trường gần quán trà sữa', 'ÍT QUAN TRỌNG'],
            ['Học phí phù hợp với gia đình', 'QUAN TRỌNG'],
            ['Đồng phục trường có đẹp không', 'ÍT QUAN TRỌNG'],
        ], 'Chọn trường vì chất lượng, không vì những thứ hào nhoáng.', $d);
        $this->sortQ($s, 'Kéo mỗi giấy tờ vào nhóm CẦN CHUẨN BỊ hoặc KHÔNG CẦN cho hồ sơ xét tuyển.', [
            ['Học bạ THPT', 'CẦN CHUẨN BỊ'],
            ['Giấy khai sinh', 'CẦN CHUẨN BỊ'],
            ['Chứng chỉ ngoại ngữ (nếu có)', 'CẦN CHUẨN BỊ'],
            ['Sổ hộ khẩu photo', 'CẦN CHUẨN BỊ'],
            ['Bằng lái xe máy', 'KHÔNG CẦN'],
            ['Thẻ thư viện', 'KHÔNG CẦN'],
        ], 'Chuẩn bị đủ giấy tờ giúp nộp hồ sơ suôn sẻ.', $d);

        $this->fill($s, 'Kỳ thi ___ giá năng lực do một số đại học lớn tổ chức riêng.', [[0, 'đánh']], 'Đánh giá năng lực là phương thức xét tuyển riêng bên cạnh điểm thi THPT.', $d);
        $this->fill($s, 'Thí sinh đoạt giải học sinh giỏi quốc gia được ___ tuyển thẳng.', [[0, 'xét']], 'Xét tuyển thẳng là sự ghi nhận cho thành tích xuất sắc.', $d);
        $this->fill($s, 'Trúng tuyển nguyện vọng 1 thì các nguyện vọng ___ không được xét nữa.', [[0, 'sau']], 'Vì vậy hãy xếp nguyện vọng mình thích nhất lên đầu.', $d);
        $this->fill($s, 'Mã trường, mã ngành phải ghi ___ xác trong phiếu đăng ký.', [[0, 'chính']], 'Sai mã trường, mã ngành có thể khiến hồ sơ bị loại.', $d);
        $this->fill($s, 'Nên có nguyện vọng dự ___ để phòng khi điểm không như mong đợi.', [[0, 'phòng']], 'Nguyện vọng dự phòng là lưới an toàn của mọi thí sinh.', $d);
    }

    private function seedLop123(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-12-lop-12-3';
        $d = 'trung_binh';

        $this->quiz($s, 'Thư xin việc (cover letter) khác CV ở điểm nào?', [
            'Thư bày tỏ mong muốn và lý do phù hợp; CV liệt kê thông tin cá nhân',
            'Thư dài hơn CV rất nhiều',
            'CV không cần thiết khi đã có thư xin việc',
            'Hai thứ này hoàn toàn giống nhau',
        ], 0, 'Thư xin việc thuyết phục bằng lý do; CV thuyết phục bằng dữ kiện.', $d);
        $this->quiz($s, 'Khi chưa có kinh nghiệm làm việc, nên viết gì trong CV?', [
            'Hoạt động ngoại khoá, dự án đã làm, kỹ năng và thời gian thực tập',
            'Để trống phần kinh nghiệm',
            'Bịa ra vài năm kinh nghiệm cho đẹp',
            'Chỉ ghi mỗi họ tên rồi nộp',
        ], 0, 'Chưa đi làm vẫn có nhiều thứ đáng ghi: hoạt động, dự án, kỹ năng.', $d);
        $this->quiz($s, 'Ảnh trong CV nên như thế nào?', [
            'Ảnh chân dung lịch sự, rõ mặt, nền đơn giản',
            'Ảnh selfie với filter dễ thương',
            'Ảnh chụp cùng nhóm bạn cho vui',
            'Không cần ảnh trong CV',
        ], 0, 'Ảnh lịch sự thể hiện sự chuyên nghiệp ngay từ cái nhìn đầu tiên.', $d);
        $this->quiz($s, 'Vì sao nên điều chỉnh CV cho từng vị trí ứng tuyển?', [
            'Để nhấn mạnh kinh nghiệm phù hợp với yêu cầu công việc đó',
            'Để CV mỗi nơi một kiểu cho vui',
            'Vì nhà tuyển dụng thích sự mới lạ',
            'Không cần điều chỉnh, một CV dùng cho mọi nơi',
        ], 0, 'CV "đo ni đóng giày" cho thấy bạn thật sự quan tâm vị trí đó.', $d);
        $this->quiz($s, 'Nhà tuyển dụng thường dành bao lâu cho lần lướt CV đầu tiên?', [
            'Khoảng 6 – 10 giây',
            'Khoảng 1 giờ đồng hồ',
            'Cả một buổi chiều',
            'Không bao giờ đọc CV',
        ], 0, 'Vài giây đầu quyết định CV có được đọc tiếp hay không.', $d);

        $this->matching($s, 'Nối mỗi phần của CV với ví dụ nội dung.', [
            ['Thông tin cá nhân', 'Họ tên, số điện thoại, email'],
            ['Mục tiêu nghề nghiệp', 'Mong muốn trở thành nhân viên marketing'],
            ['Kinh nghiệm', 'Thực tập sinh tại công ty ABC'],
            ['Kỹ năng', 'Tin học văn phòng, giao tiếp tốt'],
        ], 'CV chuẩn gồm các phần rõ ràng, dễ lướt nhanh.', $d);
        $this->matching($s, 'Nối mỗi lỗi thường gặp trong CV với cách sửa.', [
            ['CV dài tới 5 trang', 'Rút gọn còn 1 – 2 trang'],
            ['Lỗi chính tả', 'Đọc soát kỹ trước khi gửi'],
            ['Dùng chung một CV cho mọi nơi', 'Điều chỉnh theo từng vị trí'],
            ['Email thiếu chuyên nghiệp', 'Tạo email mới nghiêm túc'],
        ], 'Sửa những lỗi cơ bản giúp CV qua được vòng lướt đầu tiên.', $d);
        $this->matching($s, 'Nối mỗi động từ với mức độ ấn tượng khi viết CV.', [
            ['Tham gia', 'Yếu, chung chung'],
            ['Tổ chức', 'Mạnh, thể hiện vai trò'],
            ['Hỗ trợ', 'Trung bình'],
            ['Dẫn dắt', 'Mạnh, thể hiện khả năng lãnh đạo'],
        ], 'Động từ mạnh giúp kinh nghiệm của bạn nổi bật hơn.', $d);
        $this->matching($s, 'Nối mỗi giấy tờ với mục đích của nó trong hồ sơ.', [
            ['Sơ yếu lý lịch', 'Xác nhận nhân thân'],
            ['Bằng tốt nghiệp', 'Chứng minh trình độ học vấn'],
            ['Giấy khám sức khoẻ', 'Chứng minh đủ điều kiện làm việc'],
            ['Ảnh 3x4', 'Dán vào hồ sơ'],
        ], 'Mỗi giấy tờ trong hồ sơ đều có vai trò riêng.', $d);
        $this->matching($s, 'Nối mỗi tình huống khó với cách xử lý trong CV.', [
            ['Chưa có kinh nghiệm làm việc', 'Nhấn mạnh hoạt động và kỹ năng'],
            ['Điểm GPA thấp', 'Không ghi GPA, nêu điểm mạnh khác'],
            ['Từng làm trái ngành', 'Nêu kỹ năng chuyển đổi được'],
            ['Có khoảng trống thời gian', 'Giải thích trung thực, ngắn gọn'],
        ], 'Điểm yếu trong CV đều có cách trình bày khéo léo.', $d);

        $this->sortQ($s, 'Kéo mỗi thông tin vào nhóm NÊN ĐƯA hoặc KHÔNG NÊN ĐƯA vào CV.', [
            ['Kinh nghiệm thực tập', 'NÊN ĐƯA'],
            ['Sở thích ăn uống', 'KHÔNG NÊN ĐƯA'],
            ['Kỹ năng ngoại ngữ', 'NÊN ĐƯA'],
            ['Chiều cao, cân nặng', 'KHÔNG NÊN ĐƯA'],
            ['Giải thưởng học tập', 'NÊN ĐƯA'],
            ['Tình trạng hôn nhân', 'KHÔNG NÊN ĐƯA'],
        ], 'CV chỉ nên chứa thông tin liên quan đến công việc.', $d);
        $this->sortQ($s, 'Kéo mỗi cách trình bày vào nhóm CHUYÊN NGHIỆP hoặc THIẾU CHUYÊN NGHIỆP.', [
            ['Font chữ đơn giản, dễ đọc', 'CHUYÊN NGHIỆP'],
            ['Nhiều màu sắc loè loẹt', 'THIẾU CHUYÊN NGHIỆP'],
            ['Sắp xếp theo thời gian rõ ràng', 'CHUYÊN NGHIỆP'],
            ['Ảnh selfie nghiêng đầu', 'THIẾU CHUYÊN NGHIỆP'],
            ['Dùng con số đo lường kết quả', 'CHUYÊN NGHIỆP'],
            ['Viết tắt khó hiểu', 'THIẾU CHUYÊN NGHIỆP'],
        ], 'Trình bày chuyên nghiệp thể hiện sự tôn trọng nhà tuyển dụng.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về viết CV.', [
            ['CV phải trung thực tuyệt đối', 'ĐÚNG'],
            ['Nên nói dối một chút cho CV đẹp hơn', 'SAI'],
            ['Nên gửi CV dạng PDF để giữ nguyên định dạng', 'ĐÚNG'],
            ['CV càng dài càng gây ấn tượng', 'SAI'],
            ['Nên dùng email chuyên nghiệp khi ứng tuyển', 'ĐÚNG'],
            ['Không cần đọc lại CV trước khi gửi', 'SAI'],
        ], 'Trung thực và cẩn thận là hai nguyên tắc vàng của CV.', $d);
        $this->sortQ($s, 'Kéo mỗi nội dung vào nhóm THUỘC CV hoặc THUỘC THƯ XIN VIỆC.', [
            ['Danh sách kỹ năng', 'THUỘC CV'],
            ['Lý do muốn làm ở công ty này', 'THUỘC THƯ XIN VIỆC'],
            ['Quá trình học vấn', 'THUỘC CV'],
            ['Lời chào và bày tỏ mong muốn', 'THUỘC THƯ XIN VIỆC'],
            ['Kinh nghiệm làm việc', 'THUỘC CV'],
            ['Cam kết cống hiến', 'THUỘC THƯ XIN VIỆC'],
        ], 'CV liệt kê dữ kiện; thư xin việc truyền tải mong muốn.', $d);
        $this->sortQ($s, 'Kéo mỗi việc vào nhóm LÀM TRƯỚC hoặc LÀM SAU khi gửi hồ sơ.', [
            ['Soát lỗi chính tả trong CV', 'LÀM TRƯỚC'],
            ['Tìm hiểu kỹ về công ty', 'LÀM TRƯỚC'],
            ['In hồ sơ mang theo khi phỏng vấn', 'LÀM TRƯỚC'],
            ['Chuẩn bị cho vòng phỏng vấn', 'LÀM SAU'],
            ['Gửi email cảm ơn sau phỏng vấn', 'LÀM SAU'],
            ['Theo dõi kết quả ứng tuyển', 'LÀM SAU'],
        ], 'Chuẩn bị kỹ trước khi gửi; chủ động theo dõi sau khi gửi.', $d);

        $this->fill($s, 'Thư xin việc bày tỏ lý do bạn ___ hợp với vị trí ứng tuyển.', [[0, 'phù']], 'Lý do phù hợp thuyết phục hơn lời khen sáo rỗng.', $d);
        $this->fill($s, 'Nhà tuyển dụng chỉ lướt CV khoảng 6 – 10 ___ đầu tiên.', [[0, 'giây']], 'Vài giây đầu quyết định số phận của CV.', $d);
        $this->fill($s, 'Nên gửi CV dưới dạng file ___ để giữ nguyên định dạng.', [[0, 'PDF']], 'File PDF hiển thị giống nhau trên mọi thiết bị.', $d);
        $this->fill($s, 'Đặt tên email thiếu ___ túc gây ấn tượng xấu với nhà tuyển dụng.', [[0, 'nghiêm']], 'Email nghiêm túc thể hiện sự chuyên nghiệp.', $d);
        $this->fill($s, 'Khoảng trống thời gian trong CV nên giải thích trung thực và ___ gọn.', [[0, 'ngắn']], 'Giải thích ngắn gọn, trung thực tốt hơn giấu giếm.', $d);
    }

    private function seedLop124(): void
    {
        $s = 'trai-nghiem-huong-nghiep-thpt-12-lop-12-4';
        $d = 'kho';

        $this->quiz($s, 'Khi được hỏi "Điểm yếu của bạn là gì?", cách trả lời tốt nhất là gì?', [
            'Nêu điểm yếu thật nhưng đang cải thiện, kèm ví dụ cụ thể',
            'Khẳng định mình không có điểm yếu nào',
            'Kể ra thật nhiều điểm yếu để tỏ ra khiêm tốn',
            'Đổ lỗi điểm yếu cho hoàn cảnh và người khác',
        ], 0, 'Trung thực kèm hướng cải thiện cho thấy sự trưởng thành.', $d);
        $this->quiz($s, 'Câu hỏi "Vì sao chúng tôi nên chọn bạn?" muốn đánh giá điều gì?', [
            'Sự phù hợp giữa năng lực của bạn và yêu cầu công việc',
            'Khả năng nịnh nọt của ứng viên',
            'Xem ai trả lời dài nhất',
            'Đánh giá ngoại hình ứng viên',
        ], 0, 'Hãy trả lời bằng sự phù hợp cụ thể, không phải lời khen chung chung.', $d);
        $this->quiz($s, 'Khi không biết câu trả lời trong phỏng vấn, cách xử lý tốt nhất là gì?', [
            'Thừa nhận thẳng thắn và nêu cách mình sẽ tìm hiểu',
            'Bịa ra một câu trả lời cho có',
            'Im lặng cúi mặt chờ qua câu hỏi',
            'Đánh trống lảng sang chuyện khác',
        ], 0, 'Trung thực khi không biết đáng tin hơn bịa đặt.', $d);
        $this->quiz($s, 'Ngôn ngữ cơ thể nào tạo ấn tượng tốt trong phỏng vấn?', [
            'Ngồi thẳng, giao tiếp bằng mắt, cười tự nhiên',
            'Khoanh tay, tránh nhìn người đối diện',
            'Rung đùi liên tục cho đỡ căng thẳng',
            'Nhìn chằm chằm vào người phỏng vấn',
        ], 0, 'Ngôn ngữ cơ thể nói lên sự tự tin trước cả lời nói.', $d);
        $this->quiz($s, 'Khi được hỏi về mức lương mong muốn, nên làm gì?', [
            'Tìm hiểu mặt bằng chung và đưa ra khoảng lương linh hoạt',
            'Đòi mức thật cao để thử nhà tuyển dụng',
            'Nói mức thật thấp để chắc chắn được nhận',
            'Từ chối trả lời mọi câu hỏi về lương',
        ], 0, 'Hiểu mặt bằng lương giúp đàm phán tự tin và thực tế.', $d);

        $this->matching($s, 'Nối mỗi câu hỏi khó với gợi ý trả lời.', [
            ['Hãy giới thiệu về bản thân', 'Ngắn gọn 1 – 2 phút, nhấn mạnh điểm phù hợp'],
            ['Điểm yếu của bạn là gì?', 'Nêu điểm đang cải thiện'],
            ['Vì sao chọn công ty chúng tôi?', 'Thể hiện mình đã tìm hiểu kỹ'],
            ['Bạn thấy mình ở đâu sau 5 năm?', 'Mục tiêu gắn với sự phát triển của công ty'],
        ], 'Chuẩn bị trước câu trả lời giúp tự tin hơn rất nhiều.', $d);
        $this->matching($s, 'Nối mỗi biểu hiện cơ thể với ý nghĩa của nó.', [
            ['Giao tiếp bằng mắt', 'Tự tin'],
            ['Ngồi thẳng lưng', 'Chuyên nghiệp'],
            ['Bắt tay chắc chắn', 'Quyết đoán'],
            ['Cười tự nhiên', 'Thân thiện'],
        ], 'Ngôn ngữ cơ thể tích cực cộng điểm cho buổi phỏng vấn.', $d);
        $this->matching($s, 'Nối mỗi lỗi phỏng vấn với cách tránh.', [
            ['Đến muộn', 'Đi sớm 15 – 20 phút'],
            ['Không tìm hiểu về công ty', 'Đọc website công ty trước'],
            ['Nói xấu nơi làm cũ', 'Chỉ nói điều tích cực'],
            ['Trả lời lan man', 'Luyện trả lời ngắn gọn, có trọng tâm'],
        ], 'Tránh được lỗi cơ bản đã là một lợi thế lớn.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn phỏng vấn với mục tiêu của nó.', [
            ['Mở đầu', 'Tạo ấn tượng đầu tiên'],
            ['Thân bài', 'Thể hiện năng lực bản thân'],
            ['Đặt câu hỏi ngược', 'Thể hiện sự quan tâm'],
            ['Kết thúc', 'Ghi điểm và cảm ơn'],
        ], 'Mỗi giai đoạn có nhiệm vụ riêng, chuẩn bị đủ cả bốn.', $d);
        $this->matching($s, 'Nối mỗi câu trả lời mẫu với đánh giá.', [
            ['Tôi làm được mọi thứ', 'Thiếu cụ thể'],
            ['Ở dự án X, tôi đã tăng doanh số 20%', 'Thuyết phục'],
            ['Tôi không có điểm yếu', 'Thiếu trung thực'],
            ['Tôi đang cải thiện kỹ năng thuyết trình', 'Chân thành'],
        ], 'Câu trả lời cụ thể và chân thành luôn ghi điểm.', $d);

        $this->sortQ($s, 'Kéo mỗi câu trả lời vào nhóm NÊN NÓI hoặc KHÔNG NÊN NÓI khi phỏng vấn.', [
            ['Nêu thành tích cụ thể bằng con số', 'NÊN NÓI'],
            ['Nói xấu sếp cũ', 'KHÔNG NÊN NÓI'],
            ['Thể hiện đã tìm hiểu về công ty', 'NÊN NÓI'],
            ['Khoe khoang quá đà', 'KHÔNG NÊN NÓI'],
            ['Hỏi về cơ hội phát triển', 'NÊN NÓI'],
            ['Hỏi lương ngay từ câu đầu tiên', 'KHÔNG NÊN NÓI'],
        ], 'Nên nói điều thể hiện giá trị; tránh điều gây ấn tượng xấu.', $d);
        $this->sortQ($s, 'Kéo mỗi hành động vào nhóm TẠO ẤN TƯỢNG TỐT hoặc XẤU.', [
            ['Đến sớm 15 phút', 'TẠO ẤN TƯỢNG TỐT'],
            ['Bấm điện thoại khi chờ', 'TẠO ẤN TƯỢNG XẤU'],
            ['Chuẩn bị câu hỏi cho nhà tuyển dụng', 'TẠO ẤN TƯỢNG TỐT'],
            ['Ăn mặc luộm thuộm', 'TẠO ẤN TƯỢNG XẤU'],
            ['Cảm ơn sau buổi phỏng vấn', 'TẠO ẤN TƯỢNG TỐT'],
            ['Ngắt lời người đang hỏi', 'TẠO ẤN TƯỢNG XẤU'],
        ], 'Ấn tượng tốt đến từ những chi tiết nhỏ nhất.', $d);
        $this->sortQ($s, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI về kỹ năng phỏng vấn.', [
            ['Nên trung thực về kinh nghiệm của mình', 'ĐÚNG'],
            ['Im lặng khi bí là cách xử lý hay', 'SAI'],
            ['Được phép hỏi lại khi chưa rõ câu hỏi', 'ĐÚNG'],
            ['Chỉ cần giỏi chuyên môn là đủ', 'SAI'],
            ['Chuẩn bị trước giúp tự tin hơn', 'ĐÚNG'],
            ['Nên đòi lương thật cao để thử nhà tuyển dụng', 'SAI'],
        ], 'Hiểu đúng về phỏng vấn giúp chuẩn bị đúng hướng.', $d);
        $this->sortQ($s, 'Kéo mỗi việc chuẩn bị vào nhóm PHỎNG VẤN TRỰC TIẾP hoặc PHỎNG VẤN ONLINE.', [
            ['Đến địa điểm sớm 15 phút', 'PHỎNG VẤN TRỰC TIẾP'],
            ['Kiểm tra mic và camera', 'PHỎNG VẤN ONLINE'],
            ['Bắt tay chào người phỏng vấn', 'PHỎNG VẤN TRỰC TIẾP'],
            ['Chọn phông nền gọn gàng', 'PHỎNG VẤN ONLINE'],
            ['Mang hồ sơ giấy theo', 'PHỎNG VẤN TRỰC TIẾP'],
            ['Tắt thông báo trên điện thoại', 'PHỎNG VẤN ONLINE'],
        ], 'Mỗi hình thức phỏng vấn có khâu chuẩn bị riêng.', $d);
        $this->sortQ($s, 'Kéo mỗi tình huống vào nhóm XỬ LÝ KHÉO hoặc XỬ LÝ VỤNG.', [
            ['Không biết câu trả lời: thừa nhận và hứa tìm hiểu', 'XỬ LÝ KHÉO'],
            ['Bí: im lặng cúi mặt', 'XỬ LÝ VỤNG'],
            ['Được hỏi lương: đưa khoảng linh hoạt', 'XỬ LÝ KHÉO'],
            ['Chê công ty đối thủ để lấy lòng', 'XỬ LÝ VỤNG'],
            ['Kể ví dụ cụ thể khi được hỏi kinh nghiệm', 'XỬ LÝ KHÉO'],
            ['Trả lời chung chung, sáo rỗng', 'XỬ LÝ VỤNG'],
        ], 'Xử lý khéo biến tình huống khó thành điểm cộng.', $d);

        $this->fill($s, 'Nên đến điểm phỏng vấn sớm 15 – 20 ___ để ổn định tâm lý.', [[0, 'phút']], 'Đến sớm thể hiện sự tôn trọng và giúp bạn bình tĩnh.', $d);
        $this->fill($s, 'Khi bí câu hỏi, ___ nhận không biết tốt hơn bịa ra câu trả lời.', [[0, 'thừa']], 'Thừa nhận thẳng thắn đáng tin hơn bịa đặt.', $d);
        $this->fill($s, 'Giao tiếp bằng ___ thể hiện sự tự tin trong phỏng vấn.', [[0, 'mắt']], 'Ánh mắt tự tin tạo kết nối với người phỏng vấn.', $d);
        $this->fill($s, 'Câu hỏi ngược cho nhà tuyển dụng thể hiện sự quan ___ của bạn.', [[0, 'tâm']], 'Đặt câu hỏi ngược cho thấy bạn thật sự muốn gắn bó.', $d);
        $this->fill($s, 'Sau phỏng vấn, gửi thư cảm ơn trong vòng 24 ___ là điểm cộng lớn.', [[0, 'giờ']], 'Thư cảm ơn kịp thời thể hiện sự chuyên nghiệp.', $d);
    }

    // __SEED_METHODS__
}





