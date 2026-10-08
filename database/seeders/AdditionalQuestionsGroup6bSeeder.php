<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Bổ sung câu hỏi cho 30 bài Mỹ thuật HIỆN CÓ (nhóm G6, phần b).
 *
 * Mỗi bài: 5 câu MỚI cho mỗi kiểu chơi (quiz/matching/sort/fill).
 * Nội dung tiếng Việt tự viết 100%, bám topic + khối lớp + độ khó của bài,
 * không trùng prompt đã có, không trích tác phẩm có bản quyền.
 * Idempotent: chạy lại không thêm câu mới (giới hạn 8 câu/kiểu/bài + kiểm tra prompt trùng).
 */
class AdditionalQuestionsGroup6bSeeder extends Seeder
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

    // ===== 1. Màu nóng và màu lạnh (lớp 6, dễ) =====
    private function seedMtMauNongMauLanh(): void
    {
        $s = 'mt-mau-nong-mau-lanh'; $d = 'de';
        $this->quiz($s, 'Trong các màu sau, màu nào KHÔNG thuộc nhóm màu nóng?',
            ['Màu xanh dương', 'Màu đỏ', 'Màu cam', 'Màu vàng'], 0,
            'Màu xanh dương thuộc nhóm màu lạnh, còn đỏ, cam, vàng là ba màu nóng.', $d);
        $this->quiz($s, 'Muốn vẽ bức tranh về ngày hè oi bức, em nên ưu tiên gam màu nào?',
            ['Gam màu nóng', 'Gam màu lạnh', 'Chỉ màu đen trắng', 'Gam màu xám'], 0,
            'Gam màu nóng như đỏ, cam, vàng diễn tả tốt cái nóng của mùa hè.', $d);
        $this->quiz($s, 'Màu tím thường gợi cảm giác gì?',
            ['Sang trọng, mộng mơ', 'Nóng bỏng, sôi động', 'Mát lạnh như băng', 'Buồn tẻ, u ám'], 0,
            'Màu tím thuộc nhóm màu lạnh, thường gợi sự sang trọng và mộng mơ.', $d);
        $this->quiz($s, 'Màu xanh lá cây thuộc nhóm màu nào?',
            ['Màu lạnh', 'Màu nóng', 'Màu trung tính', 'Không thuộc nhóm nào'], 0,
            'Màu xanh lá cây thuộc nhóm màu lạnh, gợi cảm giác tươi mát của thiên nhiên.', $d);
        $this->quiz($s, 'Pha màu nóng với màu lạnh theo tỉ lệ cân bằng, bức tranh thường mang cảm giác gì?',
            ['Hài hòa, dễ chịu', 'Chỉ có sự nóng bỏng', 'Chỉ có sự lạnh lẽo', 'Rối mắt, khó chịu'], 0,
            'Kết hợp cân bằng hai nhóm màu giúp bức tranh hài hòa, không bị lệch cảm xúc.', $d);
        $this->matching($s, 'Nối mỗi con vật với gam màu thường gợi cảm giác về nó.',
            [['Cáo lửa', 'Màu nóng'], ['Cá heo', 'Màu lạnh'], ['Sư tử', 'Màu nóng'], ['Chim cánh cụt', 'Màu lạnh']],
            'Con vật sống ở vùng nóng gợi màu nóng, con vật vùng lạnh gợi màu lạnh.', $d);
        $this->matching($s, 'Nối mỗi mùa trong năm với gam màu phù hợp để vẽ.',
            [['Mùa hè', 'Màu nóng'], ['Mùa đông', 'Màu lạnh'], ['Mùa thu', 'Màu nóng'], ['Mùa xuân', 'Màu lạnh']],
            'Mùa hè, mùa thu dùng gam nóng; mùa đông, mùa xuân dùng gam lạnh sẽ hợp cảnh.', $d);
        $this->matching($s, 'Nối mỗi cảm xúc với gam màu thường dùng để thể hiện.',
            [['Vui vẻ', 'Màu nóng'], ['Buồn bã', 'Màu lạnh'], ['Giận dữ', 'Màu nóng'], ['Bình yên', 'Màu lạnh']],
            'Cảm xúc mạnh, sôi nổi hợp với màu nóng; cảm xúc trầm lắng hợp với màu lạnh.', $d);
        $this->matching($s, 'Nối mỗi món ăn, đồ uống với cảm giác màu sắc về nó.',
            [['Ớt đỏ', 'Màu nóng'], ['Kem bạc hà', 'Màu lạnh'], ['Mì cay', 'Màu nóng'], ['Nước đá chanh', 'Màu lạnh']],
            'Đồ cay nóng gợi màu nóng, đồ mát lạnh gợi màu lạnh.', $d);
        $this->matching($s, 'Nối mỗi thời điểm trong ngày với gam màu của bầu trời.',
            [['Bình minh', 'Màu nóng'], ['Hoàng hôn', 'Màu nóng'], ['Buổi trưa', 'Màu lạnh'], ['Đêm khuya', 'Màu lạnh']],
            'Bình minh và hoàng hôn rực đỏ cam; trưa và đêm bầu trời xanh thẫm.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU NÓNG ẤM / MÀU LẠNH MÁT.',
            [['Đỏ', 'MÀU NÓNG ẤM'], ['Cam', 'MÀU NÓNG ẤM'], ['Xanh dương', 'MÀU LẠNH MÁT'], ['Xanh lá', 'MÀU LẠNH MÁT'], ['Vàng', 'MÀU NÓNG ẤM'], ['Tím', 'MÀU LẠNH MÁT']],
            'Đỏ, cam, vàng là màu nóng; xanh dương, xanh lá, tím là màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các từ gợi cảm xúc vào nhóm: CẢM GIÁC ẤM ÁP / CẢM GIÁC MÁT MẺ.',
            [['Nóng bỏng', 'CẢM GIÁC ẤM ÁP'], ['Sôi động', 'CẢM GIÁC ẤM ÁP'], ['Mát lạnh', 'CẢM GIÁC MÁT MẺ'], ['Yên bình', 'CẢM GIÁC MÁT MẺ']],
            'Màu nóng gợi ấm áp, sôi động; màu lạnh gợi mát mẻ, yên bình.', $d);
        $this->sortQ($s, 'Xếp các khung cảnh vào nhóm: NÊN DÙNG GAM NÓNG / NÊN DÙNG GAM LẠNH.',
            [['Lễ hội lửa trại', 'NÊN DÙNG GAM NÓNG'], ['Núi tuyết', 'NÊN DÙNG GAM LẠNH'], ['Sa mạc nắng gắt', 'NÊN DÙNG GAM NÓNG'], ['Đại dương bao la', 'NÊN DÙNG GAM LẠNH']],
            'Chọn gam màu hợp với không khí của khung cảnh sẽ làm tranh sinh động hơn.', $d);
        $this->sortQ($s, 'Xếp các loại quả vào nhóm theo gam màu vỏ của chúng.',
            [['Ớt đỏ', 'MÀU NÓNG'], ['Quả cam', 'MÀU NÓNG'], ['Nho xanh', 'MÀU LẠNH'], ['Dưa lưới xanh', 'MÀU LẠNH']],
            'Quả có vỏ đỏ, cam, vàng thuộc gam nóng; vỏ xanh thuộc gam lạnh.', $d);
        $this->sortQ($s, 'Xếp các hoạt động vào nhóm: GỢI CẢM GIÁC NÓNG BỎNG / GỢI CẢM GIÁC MÁT LÀNH.',
            [['Chạy marathon', 'GỢI CẢM GIÁC NÓNG BỎNG'], ['Bơi lội', 'GỢI CẢM GIÁC MÁT LÀNH'], ['Đốt lửa trại', 'GỢI CẢM GIÁC NÓNG BỎNG'], ['Trượt băng', 'GỢI CẢM GIÁC MÁT LÀNH']],
            'Hoạt động gắn với lửa, vận động mạnh gợi màu nóng; gắn với nước, băng gợi màu lạnh.', $d);
        $this->fill($s, 'Màu ___ là màu lạnh, gợi cảm giác mát mẻ như bầu trời.', [[0, 'xanh dương']],
            'Màu xanh dương là màu lạnh tiêu biểu, gợi bầu trời và mặt nước.', $d);
        $this->fill($s, 'Khi vẽ cảnh tuyết rơi, họa sĩ thường dùng nhiều gam màu ___.', [[0, 'lạnh']],
            'Gam màu lạnh như xanh dương, trắng rất hợp để vẽ cảnh tuyết rơi.', $d);
        $this->fill($s, 'Màu cam được pha từ màu đỏ và màu ___, thuộc nhóm màu nóng.', [[0, 'vàng']],
            'Màu cam là màu nóng, được pha từ hai màu nóng là đỏ và vàng.', $d);
        $this->fill($s, 'Tranh về lễ hội lửa trại rực lửa nên lấy gam màu ___ làm chủ đạo.', [[0, 'nóng']],
            'Lễ hội lửa trại rực lửa hợp với gam màu nóng như đỏ, cam, vàng.', $d);
        $this->fill($s, 'Màu xanh lá cây thuộc nhóm màu ___, gợi cảm giác tươi mát.', [[0, 'lạnh']],
            'Màu xanh lá cây là màu lạnh, gợi cây cối và thiên nhiên tươi mát.', $d);
    }

    // ===== 2. Pha màu cơ bản (lớp 6, trung bình) =====
    private function seedMtPhaMauCoBan(): void
    {
        $s = 'mt-pha-mau-co-ban'; $d = 'trung_binh';
        $this->quiz($s, 'Màu lam pha với màu đỏ sẽ được màu gì?',
            ['Màu tím', 'Màu cam', 'Màu xanh lá', 'Màu nâu'], 0,
            'Màu lam pha với màu đỏ cho ra màu tím, một màu thứ cấp.', $d);
        $this->quiz($s, 'Muốn có màu xanh lá cây, ta pha hai màu nào với nhau?',
            ['Vàng và lam', 'Đỏ và vàng', 'Đỏ và lam', 'Trắng và đen'], 0,
            'Màu vàng pha với màu lam sẽ cho ra màu xanh lá cây.', $d);
        $this->quiz($s, 'Pha thêm màu đen vào màu đỏ, ta sẽ được màu đỏ như thế nào?',
            ['Đỏ đậm (đỏ sẫm)', 'Đỏ nhạt (hồng)', 'Đỏ tươi hơn', 'Không thay đổi'], 0,
            'Thêm màu đen làm màu sắc đậm và tối đi, ta được màu đỏ sẫm.', $d);
        $this->quiz($s, 'Màu nào sau đây là màu thứ cấp?',
            ['Màu cam', 'Màu đỏ', 'Màu vàng', 'Màu lam'], 0,
            'Màu cam được pha từ đỏ và vàng nên là màu thứ cấp; ba màu còn lại là màu cơ bản.', $d);
        $this->quiz($s, 'Khi pha màu, ta nên cho màu vào nhau như thế nào?',
            ['Cho từ từ màu đậm vào màu nhạt', 'Đổ ồ ạt màu nhạt vào màu đậm', 'Trộn thật nhanh hai màu', 'Cách nào cũng như nhau'], 0,
            'Cho từ từ màu đậm vào màu nhạt giúp dễ điều chỉnh, tránh màu bị đậm quá.', $d);
        $this->matching($s, 'Nối mỗi công thức pha với màu thu được.',
            [['Đỏ + lam', 'Màu tím'], ['Vàng + đỏ', 'Màu cam'], ['Lam + vàng', 'Màu xanh lá'], ['Trắng + đỏ', 'Màu hồng']],
            'Ba màu thứ cấp là cam, tím, xanh lá; đỏ pha trắng cho màu hồng.', $d);
        $this->matching($s, 'Nối mỗi mục đích với cách pha màu phù hợp.',
            [['Muốn màu nhạt', 'Pha thêm trắng'], ['Muốn màu đậm', 'Pha thêm đen'], ['Muốn màu tươi', 'Dùng màu nguyên'], ['Muốn màu trầm', 'Pha thêm nâu']],
            'Màu trắng làm nhạt màu, màu đen làm đậm màu, màu nguyên giữ độ tươi.', $d);
        $this->matching($s, 'Nối mỗi màu với nhóm của nó.',
            [['Đỏ', 'Màu cơ bản'], ['Cam', 'Màu thứ cấp'], ['Vàng', 'Màu cơ bản'], ['Tím', 'Màu thứ cấp']],
            'Màu cơ bản không pha được từ màu khác; màu thứ cấp pha từ hai màu cơ bản.', $d);
        $this->matching($s, 'Nối mỗi bước với việc cần làm khi pha màu.',
            [['Bước 1', 'Lấy màu nhạt ra bảng'], ['Bước 2', 'Thêm từ từ màu đậm'], ['Bước 3', 'Trộn đều hai màu'], ['Bước 4', 'Thử màu lên giấy']],
            'Pha màu đúng trình tự giúp màu đều và dễ điều chỉnh độ đậm nhạt.', $d);
        $this->matching($s, 'Nối mỗi màu pha với công thức tạo ra nó.',
            [['Cam', 'Đỏ + vàng'], ['Tím', 'Đỏ + lam'], ['Xanh lá', 'Vàng + lam'], ['Hồng', 'Đỏ + trắng']],
            'Ghi nhớ công thức pha giúp em chủ động tạo ra màu mình muốn.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU CƠ BẢN / MÀU PHA (THỨ CẤP).',
            [['Đỏ', 'MÀU CƠ BẢN'], ['Vàng', 'MÀU CƠ BẢN'], ['Lam', 'MÀU CƠ BẢN'], ['Cam', 'MÀU PHA (THỨ CẤP)'], ['Tím', 'MÀU PHA (THỨ CẤP)'], ['Xanh lá', 'MÀU PHA (THỨ CẤP)']],
            'Đỏ, vàng, lam là ba màu cơ bản; cam, tím, xanh lá là màu thứ cấp.', $d);
        $this->sortQ($s, 'Xếp các thao tác vào nhóm: LÀM SÁNG MÀU / LÀM TỐI MÀU.',
            [['Thêm màu trắng', 'LÀM SÁNG MÀU'], ['Thêm màu đen', 'LÀM TỐI MÀU'], ['Thêm nước (màu nước)', 'LÀM SÁNG MÀU'], ['Thêm màu nâu', 'LÀM TỐI MÀU']],
            'Màu trắng và nước làm màu sáng, nhạt đi; màu đen và nâu làm màu tối, đậm lên.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: CHUẨN BỊ / THỰC HÀNH khi pha màu.',
            [['Rửa cọ thật sạch', 'CHUẨN BỊ'], ['Lấy màu ra bảng', 'CHUẨN BỊ'], ['Trộn đều hai màu', 'THỰC HÀNH'], ['Thử màu lên giấy', 'THỰC HÀNH']],
            'Chuẩn bị cọ và màu sạch trước, rồi mới trộn và thử màu.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU NÓNG / MÀU LẠNH.',
            [['Đỏ', 'MÀU NÓNG'], ['Cam', 'MÀU NÓNG'], ['Lam', 'MÀU LẠNH'], ['Xanh lá', 'MÀU LẠNH']],
            'Trong các màu đã học, đỏ và cam là màu nóng; lam và xanh lá là màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các câu vào nhóm: ĐÚNG / SAI.',
            [['Đỏ + vàng = cam', 'ĐÚNG'], ['Lam + vàng = tím', 'SAI'], ['Trắng làm màu nhạt đi', 'ĐÚNG'], ['Đen làm màu sáng lên', 'SAI']],
            'Lam + vàng cho màu xanh lá; màu đen làm màu tối và đậm đi.', $d);
        $this->fill($s, 'Màu lam pha với màu ___ sẽ được màu xanh lá cây.', [[0, 'vàng']],
            'Màu xanh lá cây là màu thứ cấp, được pha từ vàng và lam.', $d);
        $this->fill($s, 'Ba màu cơ bản không thể pha từ màu khác là đỏ, vàng và ___.', [[0, 'lam']],
            'Đỏ, vàng, lam là ba màu cơ bản của hội họa.', $d);
        $this->fill($s, 'Pha thêm màu ___ vào bất kỳ màu nào cũng làm màu đó nhạt và sáng hơn.', [[0, 'trắng']],
            'Màu trắng có tác dụng làm nhạt và sáng mọi màu sắc.', $d);
        $this->fill($s, 'Màu cam, màu tím và màu xanh lá cây được gọi chung là màu ___ cấp.', [[0, 'thứ']],
            'Ba màu pha từ hai màu cơ bản được gọi là màu thứ cấp.', $d);
        $this->fill($s, 'Khi pha màu, nên cho màu ___ vào màu nhạt một cách từ từ để dễ điều chỉnh.', [[0, 'đậm']],
            'Cho từ từ màu đậm vào màu nhạt giúp kiểm soát độ đậm nhạt của màu pha.', $d);
    }

    // ===== 3. Đường nét và hình khối (lớp 7, trung bình) =====
    private function seedMtDuongNetHinhKhoi(): void
    {
        $s = 'mt-duong-net-hinh-khoi'; $d = 'trung_binh';
        $this->quiz($s, 'Đường lượn sóng thường gợi cảm giác gì?',
            ['Nhẹ nhàng, uyển chuyển', 'Cứng rắn, chắc chắn', 'Nguy hiểm, gấp gáp', 'Buồn bã, nặng nề'], 0,
            'Đường lượn sóng mềm mại nên gợi cảm giác nhẹ nhàng, uyển chuyển.', $d);
        $this->quiz($s, 'Đường chéo (xiên) trong tranh thường tạo cảm giác gì?',
            ['Chuyển động, năng động', 'Yên tĩnh, ổn định', 'Buồn ngủ, chậm chạp', 'Lạnh lùng, xa cách'], 0,
            'Đường chéo phá vỡ sự ổn định nên tạo cảm giác chuyển động, năng động.', $d);
        $this->quiz($s, 'Lon sữa bò có dạng gần với hình khối nào nhất?',
            ['Hình trụ', 'Hình cầu', 'Hình nón', 'Hình hộp'], 0,
            'Lon sữa bò có hai đáy tròn và thân tròn đều, gần với hình trụ nhất.', $d);
        $this->quiz($s, 'Nét vẽ nào phù hợp nhất để diễn tả mặt nước êm đềm?',
            ['Nét cong lượn sóng nhẹ', 'Nét gấp khúc', 'Nét thẳng đứng', 'Nét chấm'], 0,
            'Nét cong lượn sóng nhẹ diễn tả rất tốt mặt nước êm đềm, hiền hòa.', $d);
        $this->quiz($s, 'Khối lập phương có bao nhiêu mặt?',
            ['6 mặt', '4 mặt', '8 mặt', '12 mặt'], 0,
            'Khối lập phương có 6 mặt đều là hình vuông bằng nhau.', $d);
        $this->matching($s, 'Nối mỗi đường nét với cảm giác nó thường gợi ra.',
            [['Đường chéo', 'Năng động'], ['Đường lượn sóng', 'Êm đềm'], ['Đường thẳng ngang', 'Yên tĩnh'], ['Đường xoắn ốc', 'Huyền bí']],
            'Mỗi loại đường nét mang một cảm xúc riêng, họa sĩ chọn nét hợp với nội dung tranh.', $d);
        $this->matching($s, 'Nối mỗi vật với hình khối gần với nó nhất.',
            [['Quả bóng', 'Hình cầu'], ['Hộp quà', 'Hình hộp'], ['Cái nón', 'Hình nón'], ['Ống nước', 'Hình trụ']],
            'Quan sát vật và tìm hình khối cơ bản gần nhất giúp vẽ đúng hình dáng.', $d);
        $this->matching($s, 'Nối mỗi hình khối với số mặt của nó.',
            [['Lập phương', '6 mặt'], ['Hình cầu', '1 mặt cong'], ['Hình trụ', '3 mặt'], ['Hình nón', '2 mặt']],
            'Nắm số mặt của hình khối giúp em vẽ đúng cấu trúc của vật thể.', $d);
        $this->matching($s, 'Nối mỗi loại nét với đối tượng nên dùng nó để vẽ.',
            [['Nét cong', 'Đám mây'], ['Nét thẳng', 'Ngôi nhà'], ['Nét gấp khúc', 'Tia chớp'], ['Nét đứt', 'Đường khuất']],
            'Chọn nét vẽ phù hợp với đặc điểm của đối tượng sẽ làm tranh sinh động hơn.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với loại hình tương ứng.',
            [['Có chiều dài và chiều rộng', 'Hình phẳng'], ['Có thêm chiều sâu', 'Hình khối'], ['Chỉ thấy được một mặt', 'Hình phẳng'], ['Xoay được nhiều góc nhìn', 'Hình khối']],
            'Hình phẳng chỉ có hai chiều, hình khối có thêm chiều sâu nên nhìn được nhiều mặt.', $d);
        $this->sortQ($s, 'Xếp các đường nét vào nhóm: NÉT MỀM MẠI / NÉT CỨNG RẮN.',
            [['Đường cong', 'NÉT MỀM MẠI'], ['Đường lượn sóng', 'NÉT MỀM MẠI'], ['Đường thẳng đứng', 'NÉT CỨNG RẮN'], ['Đường gấp khúc', 'NÉT CỨNG RẮN']],
            'Đường cong, lượn sóng mềm mại; đường thẳng, gấp khúc cứng rắn, mạnh mẽ.', $d);
        $this->sortQ($s, 'Xếp các vật vào nhóm: GẦN HÌNH CẦU / GẦN HÌNH HỘP.',
            [['Quả cam', 'GẦN HÌNH CẦU'], ['Quả bóng', 'GẦN HÌNH CẦU'], ['Hộp bút', 'GẦN HÌNH HỘP'], ['Cuốn sách', 'GẦN HÌNH HỘP']],
            'Tìm hình khối gần nhất với vật giúp phác hình nhanh và đúng.', $d);
        $this->sortQ($s, 'Xếp các vật vào nhóm: NÉT THẲNG CHỦ ĐẠO / NÉT CONG CHỦ ĐẠO.',
            [['Cái bàn', 'NÉT THẲNG CHỦ ĐẠO'], ['Tòa nhà', 'NÉT THẲNG CHỦ ĐẠO'], ['Cánh buồm', 'NÉT CONG CHỦ ĐẠO'], ['Vầng trăng', 'NÉT CONG CHỦ ĐẠO']],
            'Vật có góc cạnh dùng nét thẳng, vật tròn trịa dùng nét cong là chủ đạo.', $d);
        $this->sortQ($s, 'Xếp các hình vào nhóm: CÓ GÓC NHỌN / KHÔNG CÓ GÓC NHỌN.',
            [['Hình tam giác', 'CÓ GÓC NHỌN'], ['Ngôi sao', 'CÓ GÓC NHỌN'], ['Hình tròn', 'KHÔNG CÓ GÓC NHỌN'], ['Hình bầu dục', 'KHÔNG CÓ GÓC NHỌN']],
            'Hình có đỉnh nhọn như tam giác, ngôi sao thuộc nhóm có góc nhọn.', $d);
        $this->sortQ($s, 'Xếp các nét vẽ vào nhóm: DIỄN TẢ CHUYỂN ĐỘNG / DIỄN TẢ TĨNH LẶNG.',
            [['Nét xiên', 'DIỄN TẢ CHUYỂN ĐỘNG'], ['Nét gấp khúc', 'DIỄN TẢ CHUYỂN ĐỘNG'], ['Nét ngang', 'DIỄN TẢ TĨNH LẶNG'], ['Nét cong nhẹ', 'DIỄN TẢ TĨNH LẶNG']],
            'Nét xiên, gấp khúc giàu chuyển động; nét ngang, cong nhẹ mang vẻ tĩnh lặng.', $d);
        $this->fill($s, 'Hình ___ là hình khối có 6 mặt vuông bằng nhau.', [[0, 'lập phương']],
            'Khối lập phương có 6 mặt đều là hình vuông bằng nhau.', $d);
        $this->fill($s, 'Đường ___ thường tạo cảm giác chuyển động và năng động.', [[0, 'chéo']],
            'Đường chéo (đường xiên) phá vỡ sự ổn định nên gợi chuyển động.', $d);
        $this->fill($s, 'Muốn diễn tả mặt biển êm đềm, em nên dùng nét ___ lượn nhẹ.', [[0, 'cong']],
            'Nét cong lượn nhẹ rất hợp để diễn tả mặt biển êm đềm.', $d);
        $this->fill($s, 'Hình cầu, hình trụ, hình nón đều là các hình ___.', [[0, 'khối']],
            'Các hình có chiều sâu như hình cầu, hình trụ, hình nón đều là hình khối.', $d);
        $this->fill($s, 'Cái nón lá có dạng gần với hình ___.', [[0, 'nón']],
            'Cái nón lá có đáy tròn và chóp nhọn, gần với hình nón nhất.', $d);
    }

    // ===== 4. Bố cục trong tranh (lớp 7, trung bình) =====
    private function seedMtBoCucTranh(): void
    {
        $s = 'mt-bo-cuc-tranh'; $d = 'trung_binh';
        $this->quiz($s, 'Điểm nhấn trong tranh là gì?',
            ['Chi tiết thu hút mắt nhìn đầu tiên', 'Màu nền của tranh', 'Khung viền của tranh', 'Chữ ký của họa sĩ'], 0,
            'Điểm nhấn là chi tiết được mắt người xem chú ý tới đầu tiên trong tranh.', $d);
        $this->quiz($s, 'Quy tắc một phần ba trong bố cục có nghĩa là gì?',
            ['Chia tranh thành 9 ô bằng nhau để đặt điểm nhấn', 'Chia bức tranh làm đôi', 'Vẽ tranh trong 3 giờ', 'Chỉ dùng 3 màu'], 0,
            'Chia tranh thành 9 ô bằng nhau, đặt điểm nhấn ở các giao điểm sẽ được bố cục đẹp.', $d);
        $this->quiz($s, 'Đường chân trời đặt ngay giữa tranh thường tạo cảm giác gì?',
            ['Cân bằng nhưng hơi đơn điệu', 'Hồi hộp, bất ngờ', 'Chuyển động rất mạnh', 'Bí ẩn, khó hiểu'], 0,
            'Chân trời ở giữa tạo sự cân bằng nhưng dễ gây cảm giác đơn điệu, thiếu điểm nhấn.', $d);
        $this->quiz($s, 'Trong tranh phong cảnh, tiền cảnh là phần nào?',
            ['Phần gần người xem nhất', 'Phần xa nhất của tranh', 'Vùng bầu trời', 'Khung viền tranh'], 0,
            'Tiền cảnh là phần gần người xem nhất, thường được vẽ to và rõ chi tiết.', $d);
        $this->quiz($s, 'Bố cục đối xứng thường tạo cảm giác gì?',
            ['Trang nghiêm, ổn định', 'Hỗn loạn, bất an', 'Buồn bã, u ám', 'Vui nhộn, náo nhiệt'], 0,
            'Hai bên giống nhau qua trục giữa tạo cảm giác trang nghiêm và ổn định.', $d);
        $this->matching($s, 'Nối mỗi vùng trong tranh phong cảnh với đặc điểm của nó.',
            [['Tiền cảnh', 'Gần, vẽ to rõ'], ['Trung cảnh', 'Ở giữa tranh'], ['Hậu cảnh', 'Xa, vẽ nhỏ mờ'], ['Bầu trời', 'Phía trên cao']],
            'Tranh phong cảnh chia thành tiền cảnh, trung cảnh, hậu cảnh và bầu trời.', $d);
        $this->matching($s, 'Nối mỗi cách đặt đường chân trời với hiệu quả tạo ra.',
            [['Chân trời thấp', 'Nhấn mạnh bầu trời'], ['Chân trời cao', 'Nhấn mạnh mặt đất'], ['Chân trời ở giữa', 'Cân bằng'], ['Chân trời xiên', 'Chuyển động']],
            'Vị trí đường chân trời quyết định phần nào của tranh được nhấn mạnh.', $d);
        $this->matching($s, 'Nối mỗi lỗi bố cục với cách khắc phục.',
            [['Nhân vật chính bị che khuất', 'Đặt ra vị trí thoáng'], ['Tranh bị lệch một bên', 'Thêm chi tiết cân bằng'], ['Quá nhiều chi tiết', 'Bớt chi tiết phụ'], ['Không có điểm nhấn', 'Tạo mảng màu tương phản']],
            'Nhận ra lỗi bố cục và biết cách sửa giúp bức tranh hoàn chỉnh hơn.', $d);
        $this->matching($s, 'Nối mỗi loại bố cục với cảm giác nó mang lại.',
            [['Đối xứng', 'Trang nghiêm'], ['Bất đối xứng', 'Tự nhiên'], ['Trung tâm', 'Tập trung'], ['Đường chéo', 'Năng động']],
            'Mỗi kiểu bố cục mang một cảm giác riêng, hợp với từng nội dung tranh.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với cách làm cho nó nổi bật.',
            [['Nhân vật chính', 'Vẽ to, màu nổi'], ['Điểm nhấn màu', 'Dùng màu tương phản'], ['Đường dẫn mắt', 'Sắp xếp đường nét'], ['Nền tranh', 'Giữ đơn giản']],
            'Làm nổi bật yếu tố chính bằng kích thước, màu sắc và vị trí hợp lý.', $d);
        $this->sortQ($s, 'Xếp các cách sắp xếp vào nhóm: BỐ CỤC CÂN BẰNG / BỐ CỤC LỆCH.',
            [['Hai bên tương đương nhau', 'BỐ CỤC CÂN BẰNG'], ['Một bên nặng, một bên nhẹ', 'BỐ CỤC LỆCH'], ['Nhân vật chính ở giữa', 'BỐ CỤC CÂN BẰNG'], ['Chi tiết dồn vào một góc', 'BỐ CỤC LỆCH']],
            'Bố cục cân bằng phân bố hài hòa các mảng hình, màu sắc hai bên.', $d);
        $this->sortQ($s, 'Xếp các vị trí vào nhóm: VỊ TRÍ MẠNH / VỊ TRÍ YẾU.',
            [['Giao điểm một phần ba', 'VỊ TRÍ MẠNH'], ['Chính giữa tranh', 'VỊ TRÍ MẠNH'], ['Sát mép tranh', 'VỊ TRÍ YẾU'], ['Góc khuất', 'VỊ TRÍ YẾU']],
            'Giao điểm một phần ba và vị trí trung tâm là những vị trí mạnh trong tranh.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: NÊN LÀM TRƯỚC KHI VẼ / NÊN LÀM SAU KHI VẼ.',
            [['Phác thảo bố cục', 'NÊN LÀM TRƯỚC KHI VẼ'], ['Tô màu chi tiết', 'NÊN LÀM SAU KHI VẼ'], ['Chọn đề tài', 'NÊN LÀM TRƯỚC KHI VẼ'], ['Ký tên lên tranh', 'NÊN LÀM SAU KHI VẼ']],
            'Chọn đề tài và phác bố cục trước, tô màu và ký tên sau khi hoàn thiện.', $d);
        $this->sortQ($s, 'Xếp các chi tiết vào nhóm: CHI TIẾT CHÍNH / CHI TIẾT PHỤ.',
            [['Nhân vật chính', 'CHI TIẾT CHÍNH'], ['Điểm nhấn của tranh', 'CHI TIẾT CHÍNH'], ['Bông hoa trang trí', 'CHI TIẾT PHỤ'], ['Họa tiết nền', 'CHI TIẾT PHỤ']],
            'Chi tiết chính là trọng tâm của tranh, chi tiết phụ chỉ làm nền hỗ trợ.', $d);
        $this->sortQ($s, 'Xếp các bức tranh (theo mô tả) vào nhóm: CÓ ĐIỂM NHẤN RÕ / KHÔNG CÓ ĐIỂM NHẤN.',
            [['Bông hoa đỏ giữa nền xanh', 'CÓ ĐIỂM NHẤN RÕ'], ['Chú chim vàng trên cành xanh', 'CÓ ĐIỂM NHẤN RÕ'], ['Toàn tranh một màu xám đều', 'KHÔNG CÓ ĐIỂM NHẤN'], ['Nhiều chi tiết đều nhau', 'KHÔNG CÓ ĐIỂM NHẤN']],
            'Điểm nhấn rõ khi có chi tiết tương phản nổi bật giữa nền đơn giản.', $d);
        $this->fill($s, 'Chi tiết thu hút mắt nhìn đầu tiên trong tranh gọi là điểm ___.', [[0, 'nhấn']],
            'Điểm nhấn là chi tiết được mắt người xem chú ý tới đầu tiên.', $d);
        $this->fill($s, 'Quy tắc một phần ba giúp đặt điểm nhấn vào vị trí ___ trong tranh.', [[0, 'đẹp']],
            'Đặt điểm nhấn ở giao điểm một phần ba tạo bố cục đẹp, thu hút.', $d);
        $this->fill($s, 'Trong tranh phong cảnh, phần gần người xem nhất gọi là ___ cảnh.', [[0, 'tiền']],
            'Tiền cảnh là phần gần nhất, thường vẽ to và rõ chi tiết.', $d);
        $this->fill($s, 'Đường ___ trời chia bức tranh thành phần đất và phần trời.', [[0, 'chân']],
            'Đường chân trời là ranh giới giữa mặt đất và bầu trời trong tranh.', $d);
        $this->fill($s, 'Bố cục bất đối xứng tạo cảm giác tự nhiên, ___ mái.', [[0, 'thoải']],
            'Bố cục bất đối xứng không gò bó hai bên giống nhau, tạo vẻ thoải mái.', $d);
    }

    // ===== 5. Tranh Đông Hồ nổi tiếng (lớp 8, trung bình) =====
    private function seedMtTranhDongHoNoiTieng(): void
    {
        $s = 'mt-tranh-dong-ho-noi-tieng'; $d = 'trung_binh';
        $this->quiz($s, 'Tranh Đông Hồ thường được người dân mua vào dịp nào trong năm?',
            ['Dịp Tết Nguyên đán', 'Dịp Trung thu', 'Dịp lễ hội mùa hè', 'Quanh năm như nhau'], 0,
            'Tranh Đông Hồ là món quà Tết truyền thống, nhà nhà mua về treo ngày xuân.', $d);
        $this->quiz($s, 'Bức “Đàn gà mẹ con” thể hiện mong ước gì của người dân?',
            ['Gia đình sum vầy, con đàn cháu đống', 'Mùa màng bội thu', 'Thi cử đỗ đạt', 'Buôn may bán đắt'], 0,
            'Bức “Đàn gà mẹ con” tượng trưng cho ước mong gia đình sum vầy, đông con nhiều cháu.', $d);
        $this->quiz($s, 'Màu đỏ trong tranh Đông Hồ thường được làm từ nguyên liệu nào?',
            ['Sỏi son (đá son)', 'Than củi', 'Lá cây xanh', 'Vỏ sò điệp'], 0,
            'Màu đỏ son trong tranh Đông Hồ được nghiền từ sỏi son, một loại đá tự nhiên.', $d);
        $this->quiz($s, 'Tranh Đông Hồ được in bằng kỹ thuật nào?',
            ['In bằng ván khắc gỗ', 'Vẽ tay hoàn toàn', 'In máy hiện đại', 'Khắc trên đá'], 0,
            'Tranh Đông Hồ in bằng ván khắc gỗ, mỗi màu một ván in chồng lên nhau.', $d);
        $this->quiz($s, 'Bức “Vinh hoa” vẽ con vật nào?',
            ['Con gà trống', 'Con lợn', 'Con trâu', 'Con cá chép'], 0,
            'Bức “Vinh hoa” vẽ chú gà trống oai vệ, tượng trưng cho sự vinh hiển.', $d);
        $this->matching($s, 'Nối mỗi bức tranh Đông Hồ với ý nghĩa của nó.',
            [['Đàn lợn âm dương', 'Sung túc no đủ'], ['Đám cưới chuột', 'Châm biếm xã hội'], ['Đàn gà mẹ con', 'Sum vầy'], ['Vinh hoa', 'Vinh hiển']],
            'Mỗi bức tranh Đông Hồ đều gửi gắm một ước vọng tốt đẹp của người dân.', $d);
        $this->matching($s, 'Nối mỗi màu trong tranh Đông Hồ với nguyên liệu tạo ra nó.',
            [['Màu vàng', 'Hoa hòe'], ['Màu đỏ', 'Sỏi son'], ['Màu trắng', 'Bột điệp'], ['Màu xanh', 'Lá chàm']],
            'Màu sắc tranh Đông Hồ đều lấy từ thiên nhiên nên rất bền và hài hòa.', $d);
        $this->matching($s, 'Nối mỗi công đoạn làm tranh với nội dung của nó.',
            [['Khắc ván', 'Tạo bản in'], ['Quét điệp', 'Làm giấy óng ánh'], ['In màu', 'In từng lớp màu'], ['Phơi khô', 'Hoàn thiện tranh']],
            'Làm tranh Đông Hồ trải qua nhiều công đoạn thủ công tỉ mỉ.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với giá trị của tranh Đông Hồ.',
            [['Màu tự nhiên', 'Bền màu'], ['Đề tài dân gian', 'Gần gũi'], ['In ván gỗ', 'In được nhiều'], ['Giấy điệp', 'Óng ánh']],
            'Những đặc điểm riêng làm nên giá trị độc đáo của tranh Đông Hồ.', $d);
        $this->matching($s, 'Nối mỗi chủ đề với bức tranh tiêu biểu.',
            [['Chúc tụng', 'Vinh hoa – Phú quý'], ['Sinh hoạt làng quê', 'Hứng dừa'], ['Châm biếm', 'Đám cưới chuột'], ['Lao động', 'Chăn trâu thả diều']],
            'Tranh Đông Hồ có nhiều chủ đề: chúc tụng, sinh hoạt, châm biếm, lao động.', $d);
        $this->sortQ($s, 'Xếp các bức tranh vào nhóm: TRANH ĐÔNG HỒ / TRANH KHÁC.',
            [['Đám cưới chuột', 'TRANH ĐÔNG HỒ'], ['Đàn lợn âm dương', 'TRANH ĐÔNG HỒ'], ['Tố nữ', 'TRANH KHÁC'], ['Lý ngư vọng nguyệt', 'TRANH KHÁC']],
            '“Tố nữ” là tranh Hàng Trống, “Lý ngư vọng nguyệt” là tranh Làng Sình.', $d);
        $this->sortQ($s, 'Xếp các nguyên liệu vào nhóm: NGUYÊN LIỆU TỰ NHIÊN / NHÂN TẠO.',
            [['Vỏ sò điệp', 'NGUYÊN LIỆU TỰ NHIÊN'], ['Hoa hòe', 'NGUYÊN LIỆU TỰ NHIÊN'], ['Màu công nghiệp', 'NHÂN TẠO'], ['Than lá tre', 'NGUYÊN LIỆU TỰ NHIÊN']],
            'Tranh Đông Hồ truyền thống dùng nguyên liệu tự nhiên để làm màu và giấy.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: CÔNG ĐOẠN LÀM TRANH / VIỆC KHÁC.',
            [['Khắc ván gỗ', 'CÔNG ĐOẠN LÀM TRANH'], ['Quét giấy điệp', 'CÔNG ĐOẠN LÀM TRANH'], ['In từng màu', 'CÔNG ĐOẠN LÀM TRANH'], ['Bán vé số', 'VIỆC KHÁC']],
            'Khắc ván, quét điệp, in màu là các công đoạn làm tranh Đông Hồ.', $d);
        $this->sortQ($s, 'Xếp các ý nghĩa vào nhóm: CÓ TRONG TRANH ĐÔNG HỒ / KHÔNG CÓ.',
            [['Cầu may mắn', 'CÓ TRONG TRANH ĐÔNG HỒ'], ['Châm biếm xã hội', 'CÓ TRONG TRANH ĐÔNG HỒ'], ['Quảng cáo sản phẩm', 'KHÔNG CÓ'], ['Tôn vinh lao động', 'CÓ TRONG TRANH ĐÔNG HỒ']],
            'Tranh Đông Hồ mang ước vọng may mắn, châm biếm và tôn vinh lao động.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào nhóm: ĐẶC TRƯNG ĐÔNG HỒ / KHÔNG PHẢI.',
            [['In ván khắc gỗ', 'ĐẶC TRƯNG ĐÔNG HỒ'], ['Giấy điệp óng ánh', 'ĐẶC TRƯNG ĐÔNG HỒ'], ['Màu tự nhiên', 'ĐẶC TRƯNG ĐÔNG HỒ'], ['Tô màu tay hoàn toàn', 'KHÔNG PHẢI']],
            'Tô màu tay sau khi in là đặc trưng của tranh Hàng Trống, không phải Đông Hồ.', $d);
        $this->fill($s, 'Làng Đông Hồ nay thuộc tỉnh Bắc Ninh, huyện Thuận ___.', [[0, 'Thành']],
            'Làng tranh Đông Hồ thuộc huyện Thuận Thành, tỉnh Bắc Ninh.', $d);
        $this->fill($s, 'Tranh Đông Hồ được in bằng ván ___ khắc, mỗi màu một ván.', [[0, 'gỗ']],
            'Mỗi màu trong tranh Đông Hồ được in bằng một tấm ván gỗ khắc riêng.', $d);
        $this->fill($s, 'Màu vàng trong tranh Đông Hồ được lấy từ hoa ___.', [[0, 'hòe']],
            'Hoa hòe cho màu vàng tươi, bền màu trong tranh Đông Hồ.', $d);
        $this->fill($s, 'Bức “Đàn lợn âm dương” tượng trưng cho cuộc sống ___ túc, no đủ.', [[0, 'sung']],
            'Đàn lợn mẹ con quây quần tượng trưng cho cuộc sống sung túc, no đủ.', $d);
        $this->fill($s, 'Giấy điệp óng ánh là đặc trưng riêng của tranh ___ Hồ.', [[0, 'Đông']],
            'Giấy điệp quét từ vỏ sò điệp tạo độ óng ánh riêng cho tranh Đông Hồ.', $d);
    }

    // ===== 6. Lên ý tưởng tranh dân gian (lớp 8, khó) =====
    private function seedMtYTuongTranhDanGian(): void
    {
        $s = 'mt-y-tuong-tranh-dan-gian'; $d = 'kho';
        $this->quiz($s, 'Bước đầu tiên khi lên ý tưởng vẽ tranh phong cách dân gian là gì?',
            ['Chọn đề tài gần gũi với đời sống', 'Tô màu ngay lập tức', 'Đóng khung tranh', 'Chụp ảnh mẫu vật'], 0,
            'Ý tưởng tranh dân gian bắt đầu từ việc chọn đề tài gần gũi với đời sống người dân.', $d);
        $this->quiz($s, 'Màu sắc trong tranh dân gian thường có đặc điểm gì?',
            ['Tươi sáng, rực rỡ, tương phản rõ', 'Chỉ dùng đen trắng', 'Mờ nhạt, u ám', 'Loang lổ ngẫu nhiên'], 0,
            'Tranh dân gian dùng màu tươi sáng, rực rỡ với độ tương phản rõ ràng.', $d);
        $this->quiz($s, 'Để tranh dân gian có hồn, họa sĩ cần chú ý điều gì nhất?',
            ['Nét vẽ mộc mạc, chân thật', 'Kỹ thuật siêu thực chi tiết', 'Dùng thật nhiều màu neon', 'Vẽ thật nhanh'], 0,
            'Nét vẽ mộc mạc, chân thật chính là cái hồn của tranh dân gian.', $d);
        $this->quiz($s, 'Đề tài nào KHÔNG phù hợp với tranh phong cách dân gian?',
            ['Robot chiến đấu ngoài vũ trụ', 'Lễ hội làng quê', 'Chợ phiên vùng cao', 'Trẻ em chăn trâu'], 0,
            'Tranh dân gian lấy đề tài từ đời sống quen thuộc, không hợp với đề tài viễn tưởng xa lạ.', $d);
        $this->quiz($s, 'Tranh dân gian xưa thường được sáng tác để phục vụ ai?',
            ['Người dân lao động bình thường', 'Chỉ vua chúa', 'Chỉ khách du lịch', 'Chỉ nhà sưu tầm'], 0,
            'Tranh dân gian ra đời phục vụ đời sống tinh thần của người dân lao động.', $d);
        $this->matching($s, 'Nối mỗi bước sáng tác với nội dung cần làm.',
            [['Bước 1', 'Chọn đề tài'], ['Bước 2', 'Phác thảo bố cục'], ['Bước 3', 'Vẽ nét chính'], ['Bước 4', 'Tô màu']],
            'Sáng tác tranh dân gian theo trình tự: chọn đề tài, phác bố cục, vẽ nét, tô màu.', $d);
        $this->matching($s, 'Nối mỗi đề tài với không khí muốn thể hiện.',
            [['Lễ hội', 'Vui tươi'], ['Mùa gặt', 'No ấm'], ['Chợ quê', 'Nhộn nhịp'], ['Đêm trăng', 'Lãng mạn']],
            'Mỗi đề tài gợi một không khí riêng, cần thể hiện đúng qua nét vẽ và màu sắc.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với cách thể hiện theo phong cách dân gian.',
            [['Con người', 'Nét vẽ mộc mạc'], ['Thiên nhiên', 'Cách điệu đơn giản'], ['Màu sắc', 'Tươi sáng'], ['Bố cục', 'Rõ ràng dễ hiểu']],
            'Phong cách dân gian: mộc mạc, cách điệu đơn giản, màu tươi sáng, bố cục rõ ràng.', $d);
        $this->matching($s, 'Nối mỗi lỗi thường gặp với cách khắc phục.',
            [['Đề tài xa lạ', 'Chọn đề tài quê hương'], ['Màu sắc u ám', 'Dùng màu tươi sáng'], ['Nét vẽ rối', 'Đơn giản hóa'], ['Bố cục lộn xộn', 'Phác thảo trước']],
            'Khắc phục lỗi từ khâu ý tưởng giúp tranh dân gian đúng chất và có hồn hơn.', $d);
        $this->matching($s, 'Nối mỗi nguồn cảm hứng với ví dụ cụ thể.',
            [['Lễ hội', 'Hội làng'], ['Lao động', 'Cấy lúa'], ['Tín ngưỡng', 'Thờ cúng tổ tiên'], ['Thiên nhiên', 'Mùa lúa chín']],
            'Cảm hứng sáng tác tranh dân gian đến từ lễ hội, lao động, tín ngưỡng và thiên nhiên.', $d);
        $this->sortQ($s, 'Xếp các đề tài vào nhóm: PHÙ HỢP / CHƯA PHÙ HỢP với tranh dân gian.',
            [['Hội làng', 'PHÙ HỢP'], ['Chợ quê', 'PHÙ HỢP'], ['Tàu vũ trụ', 'CHƯA PHÙ HỢP'], ['Game online', 'CHƯA PHÙ HỢP']],
            'Đề tài dân gian phải gần gũi với đời sống, văn hóa của người dân.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: LÀM TRƯỚC / LÀM SAU khi vẽ tranh.',
            [['Chọn đề tài', 'LÀM TRƯỚC'], ['Phác thảo bố cục', 'LÀM TRƯỚC'], ['Tô màu chi tiết', 'LÀM SAU'], ['Đóng khung tranh', 'LÀM SAU']],
            'Lên ý tưởng và phác thảo trước, tô màu và hoàn thiện sau.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: NÊN DÙNG NHIỀU / NÊN HẠN CHẾ trong tranh dân gian.',
            [['Đỏ tươi', 'NÊN DÙNG NHIỀU'], ['Vàng tươi', 'NÊN DÙNG NHIỀU'], ['Xám xịt', 'NÊN HẠN CHẾ'], ['Đen u ám', 'NÊN HẠN CHẾ']],
            'Tranh dân gian ưu tiên màu tươi sáng, hạn chế màu xám xịt, u ám.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào nhóm: PHONG CÁCH DÂN GIAN / PHONG CÁCH HIỆN ĐẠI.',
            [['Nét vẽ mộc mạc', 'PHONG CÁCH DÂN GIAN'], ['Màu sắc tươi sáng', 'PHONG CÁCH DÂN GIAN'], ['Trừu tượng khó hiểu', 'PHONG CÁCH HIỆN ĐẠI'], ['Ảnh ghép kỹ thuật số', 'PHONG CÁCH HIỆN ĐẠI']],
            'Dân gian mộc mạc, tươi sáng; hiện đại trừu tượng, dùng kỹ thuật số.', $d);
        $this->sortQ($s, 'Xếp các ý tưởng vào nhóm: SÁNG TẠO TỐT / CẦN XEM LẠI.',
            [['Lễ hội quê em', 'SÁNG TẠO TỐT'], ['Chợ phiên vùng cao', 'SÁNG TẠO TỐT'], ['Chép y hệt tranh có sẵn', 'CẦN XEM LẠI'], ['Vẽ không có chủ đề', 'CẦN XEM LẠI']],
            'Ý tưởng tốt phải có chủ đề rõ ràng và mang dấu ấn sáng tạo riêng.', $d);
        $this->fill($s, 'Tranh dân gian thường lấy đề tài từ cuộc sống ___ động của người dân.', [[0, 'lao']],
            'Đời sống lao động là nguồn đề tài phong phú nhất của tranh dân gian.', $d);
        $this->fill($s, 'Khi lên ý tưởng, em nên ___ thảo bố cục trước khi vẽ chi tiết.', [[0, 'phác']],
            'Phác thảo bố cục trước giúp tranh có cấu trúc rõ ràng, tránh lộn xộn.', $d);
        $this->fill($s, 'Màu sắc tranh dân gian thường ___ sáng, rực rỡ.', [[0, 'tươi']],
            'Màu tươi sáng, rực rỡ là đặc trưng của tranh dân gian.', $d);
        $this->fill($s, 'Nét vẽ dân gian mộc mạc nhưng phải ___ ràng, dễ hiểu.', [[0, 'rõ']],
            'Tranh dân gian hướng tới mọi người nên nét vẽ phải rõ ràng, dễ hiểu.', $d);
        $this->fill($s, 'Một bức tranh dân gian hay cần có ___ và cảm xúc chân thật.', [[0, 'hồn']],
            'Cái hồn từ cảm xúc chân thật làm nên sức sống của tranh dân gian.', $d);
    }

    // ===== 7. Màu sắc lớp 6: ba màu cơ bản và pha màu (1) (dễ) =====
    private function seedMtMauSacLop61(): void
    {
        $s = 'mt-mau-sac-lop-6-1'; $d = 'de';
        $this->quiz($s, 'Màu nào sau đây là màu thứ cấp?',
            ['Màu xanh lá', 'Màu đỏ', 'Màu vàng', 'Màu lam'], 0,
            'Màu xanh lá pha từ vàng và lam nên là màu thứ cấp; ba màu còn lại là màu cơ bản.', $d);
        $this->quiz($s, 'Pha màu lam với màu đỏ ta được màu gì?',
            ['Màu tím', 'Màu cam', 'Màu nâu', 'Màu hồng'], 0,
            'Màu lam pha với màu đỏ cho ra màu tím.', $d);
        $this->quiz($s, 'Màu hồng được pha từ hai màu nào?',
            ['Đỏ và trắng', 'Đỏ và vàng', 'Lam và trắng', 'Vàng và trắng'], 0,
            'Màu đỏ pha thêm màu trắng sẽ cho ra màu hồng nhạt.', $d);
        $this->quiz($s, 'Trong ba màu cơ bản, màu nào thuộc nhóm màu nóng?',
            ['Màu đỏ', 'Màu lam', 'Cả ba đều lạnh', 'Không màu nào'], 0,
            'Trong ba màu cơ bản, màu đỏ là màu nóng; vàng ấm, lam là màu lạnh.', $d);
        $this->quiz($s, 'Màu thứ cấp được tạo ra bằng cách nào?',
            ['Pha hai màu cơ bản với nhau', 'Pha màu với nước lã', 'Trộn màu với màu đen', 'Không thể tạo ra'], 0,
            'Màu thứ cấp được tạo ra bằng cách pha hai màu cơ bản với nhau.', $d);
        $this->matching($s, 'Nối mỗi màu thứ cấp với công thức pha của nó.',
            [['Cam', 'Đỏ + vàng'], ['Tím', 'Đỏ + lam'], ['Xanh lá', 'Vàng + lam'], ['Hồng', 'Đỏ + trắng']],
            'Ghi nhớ công thức pha giúp em tự tạo ra màu mình cần.', $d);
        $this->matching($s, 'Nối mỗi màu với tên gọi đúng của nó.',
            [['Đỏ', 'Màu cơ bản'], ['Cam', 'Màu thứ cấp'], ['Lam', 'Màu cơ bản'], ['Tím', 'Màu thứ cấp']],
            'Đỏ, vàng, lam là màu cơ bản; cam, tím, xanh lá là màu thứ cấp.', $d);
        $this->matching($s, 'Nối mỗi thao tác pha màu với kết quả của nó.',
            [['Thêm trắng', 'Màu nhạt đi'], ['Thêm đen', 'Màu đậm lên'], ['Thêm nước (màu nước)', 'Màu loãng ra'], ['Trộn thật đều', 'Màu đồng nhất']],
            'Mỗi thao tác khi pha màu đều cho một kết quả khác nhau.', $d);
        $this->matching($s, 'Nối mỗi vật với màu sắc đặc trưng của nó.',
            [['Mặt trời', 'Màu vàng'], ['Trái tim', 'Màu đỏ'], ['Bầu trời', 'Màu lam'], ['Quả cam chín', 'Màu cam']],
            'Quan sát màu sắc sự vật giúp em nhận biết màu cơ bản và màu thứ cấp.', $d);
        $this->matching($s, 'Nối mỗi phép pha màu với kết quả đúng.',
            [['Đỏ + vàng = ?', 'Cam'], ['Lam + vàng = ?', 'Xanh lá'], ['Đỏ + lam = ?', 'Tím'], ['Đỏ + trắng = ?', 'Hồng']],
            'Ba phép pha cơ bản cho ra cam, xanh lá, tím; đỏ pha trắng cho hồng.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU CƠ BẢN / MÀU THỨ CẤP.',
            [['Đỏ', 'MÀU CƠ BẢN'], ['Tím', 'MÀU THỨ CẤP'], ['Vàng', 'MÀU CƠ BẢN'], ['Cam', 'MÀU THỨ CẤP'], ['Lam', 'MÀU CƠ BẢN'], ['Xanh lá', 'MÀU THỨ CẤP']],
            'Màu cơ bản: đỏ, vàng, lam. Màu thứ cấp: cam, tím, xanh lá.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Cam pha từ đỏ và vàng', 'ĐÚNG'], ['Tím pha từ vàng và lam', 'SAI'], ['Xanh lá pha từ vàng và lam', 'ĐÚNG'], ['Đỏ pha được từ cam và tím', 'SAI']],
            'Tím pha từ đỏ và lam; màu cơ bản không pha được từ màu khác.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU NÓNG / MÀU LẠNH.',
            [['Đỏ', 'MÀU NÓNG'], ['Cam', 'MÀU NÓNG'], ['Lam', 'MÀU LẠNH'], ['Tím', 'MÀU LẠNH']],
            'Đỏ, cam là màu nóng; lam, tím là màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các bước vào nhóm: LÀM TRƯỚC / LÀM SAU khi pha màu.',
            [['Lấy màu ra khay', 'LÀM TRƯỚC'], ['Rửa cọ thật sạch', 'LÀM TRƯỚC'], ['Trộn đều hai màu', 'LÀM SAU'], ['Thử màu lên giấy', 'LÀM SAU']],
            'Chuẩn bị cọ sạch và lấy màu ra khay trước, rồi mới trộn và thử màu.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU SÁNG / MÀU TỐI.',
            [['Vàng', 'MÀU SÁNG'], ['Trắng', 'MÀU SÁNG'], ['Đen', 'MÀU TỐI'], ['Nâu', 'MÀU TỐI']],
            'Vàng, trắng là màu sáng; đen, nâu là màu tối.', $d);
        $this->fill($s, 'Màu cam được pha từ màu đỏ và màu ___.', [[0, 'vàng']],
            'Màu cam là màu thứ cấp, được pha từ đỏ và vàng.', $d);
        $this->fill($s, 'Màu ___ được pha từ màu lam và màu đỏ.', [[0, 'tím']],
            'Màu tím được pha từ hai màu cơ bản là lam và đỏ.', $d);
        $this->fill($s, 'Ba màu cơ bản là đỏ, ___ và lam.', [[0, 'vàng']],
            'Ba màu cơ bản trong hội họa là đỏ, vàng và lam.', $d);
        $this->fill($s, 'Pha thêm màu trắng, màu sắc sẽ ___ và nhạt đi.', [[0, 'sáng']],
            'Màu trắng làm cho mọi màu sắc sáng và nhạt đi.', $d);
        $this->fill($s, 'Màu xanh lá cây là màu thứ cấp, pha từ vàng và ___.', [[0, 'lam']],
            'Màu xanh lá cây được pha từ hai màu cơ bản vàng và lam.', $d);
    }

    // ===== 8. Màu nóng – màu lạnh lớp 6: cảm xúc của màu sắc (2) (dễ) =====
    private function seedMtMauSacLop62(): void
    {
        $s = 'mt-mau-sac-lop-6-2'; $d = 'de';
        $this->quiz($s, 'Màu vàng thường gợi cảm giác gì?',
            ['Vui tươi, ấm áp', 'Buồn bã, lạnh lẽo', 'Giận dữ, căng thẳng', 'Sợ hãi, lo âu'], 0,
            'Màu vàng như nắng mai thường gợi cảm giác vui tươi, ấm áp.', $d);
        $this->quiz($s, 'Muốn vẽ tranh về mùa đông lạnh giá, em nên dùng gam màu nào?',
            ['Gam màu lạnh', 'Gam màu nóng', 'Chỉ màu đen', 'Gam màu neon'], 0,
            'Gam màu lạnh như xanh dương, trắng rất hợp để vẽ mùa đông lạnh giá.', $d);
        $this->quiz($s, 'Màu cam gợi cho em cảm giác gì?',
            ['Năng động, thân thiện', 'Buồn ngủ, uể oải', 'Lạnh lẽo, xa cách', 'Nghiêm túc, căng thẳng'], 0,
            'Màu cam là màu nóng, gợi cảm giác năng động và thân thiện.', $d);
        $this->quiz($s, 'Màu xanh dương đậm thường được dùng để thể hiện điều gì?',
            ['Chiều sâu, sự yên tĩnh', 'Sự nóng bỏng', 'Sự giận dữ', 'Sự hỗn loạn'], 0,
            'Màu xanh dương đậm gợi chiều sâu và sự yên tĩnh, trầm lắng.', $d);
        $this->quiz($s, 'Trong tranh thiếu nhi, màu sắc thường được dùng như thế nào?',
            ['Tươi sáng, rực rỡ', 'Xám xịt, u ám', 'Chỉ đen trắng', 'Mờ nhạt, nhợt nhạt'], 0,
            'Tranh thiếu nhi thường dùng màu tươi sáng, rực rỡ hợp với tâm hồn trẻ thơ.', $d);
        $this->matching($s, 'Nối mỗi màu với cảm xúc nó thường gợi ra.',
            [['Vàng', 'Vui tươi'], ['Xám', 'Buồn bã'], ['Đỏ', 'Nhiệt huyết'], ['Xanh dương', 'Yên bình']],
            'Mỗi màu sắc đều gợi một cảm xúc riêng trong lòng người xem.', $d);
        $this->matching($s, 'Nối mỗi khung cảnh với gam màu phù hợp.',
            [['Sa mạc', 'Màu nóng'], ['Băng tuyết', 'Màu lạnh'], ['Rừng xanh', 'Màu lạnh'], ['Hoàng hôn', 'Màu nóng']],
            'Chọn gam màu hợp với khung cảnh giúp tranh truyền tải đúng cảm xúc.', $d);
        $this->matching($s, 'Nối mỗi dịp lễ với màu sắc thường dùng.',
            [['Tết Nguyên đán', 'Đỏ – vàng'], ['Giáng sinh', 'Đỏ – xanh'], ['Trung thu', 'Vàng – cam'], ['Lễ hội biển', 'Xanh dương']],
            'Mỗi dịp lễ gắn với những màu sắc mang không khí riêng.', $d);
        $this->matching($s, 'Nối mỗi con vật với cảm giác màu sắc về nó.',
            [['Sư tử', 'Nóng bỏng'], ['Cá heo', 'Mát mẻ'], ['Gà con', 'Ấm áp'], ['Ếch xanh', 'Mát mẻ']],
            'Con vật vùng nóng gợi màu nóng, con vật sống dưới nước gợi màu lạnh.', $d);
        $this->matching($s, 'Nối mỗi món ăn, đồ uống với cảm giác màu sắc.',
            [['Kem', 'Mát lạnh'], ['Lẩu cay', 'Nóng bỏng'], ['Trà đá', 'Mát lạnh'], ['Cà phê nóng', 'Ấm áp']],
            'Đồ ăn nóng gợi màu nóng, đồ ăn lạnh gợi màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU NÓNG / MÀU LẠNH.',
            [['Đỏ', 'MÀU NÓNG'], ['Vàng', 'MÀU NÓNG'], ['Xanh dương', 'MÀU LẠNH'], ['Tím', 'MÀU LẠNH']],
            'Đỏ, vàng là màu nóng; xanh dương, tím là màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các cảm xúc vào nhóm: TÍCH CỰC / TIÊU CỰC.',
            [['Vui vẻ', 'TÍCH CỰC'], ['Yêu thương', 'TÍCH CỰC'], ['Buồn bã', 'TIÊU CỰC'], ['Giận dữ', 'TIÊU CỰC']],
            'Màu sắc có thể diễn tả cả cảm xúc tích cực lẫn tiêu cực.', $d);
        $this->sortQ($s, 'Xếp các hình ảnh vào nhóm: GAM NÓNG / GAM LẠNH.',
            [['Ngọn lửa', 'GAM NÓNG'], ['Mặt trời', 'GAM NÓNG'], ['Tảng băng', 'GAM LẠNH'], ['Dòng suối', 'GAM LẠNH']],
            'Lửa, mặt trời hợp gam nóng; băng, suối hợp gam lạnh.', $d);
        $this->sortQ($s, 'Xếp các từ vào nhóm: CẢM GIÁC ẤM / CẢM GIÁC MÁT.',
            [['Ấm áp', 'CẢM GIÁC ẤM'], ['Nóng nực', 'CẢM GIÁC ẤM'], ['Mát mẻ', 'CẢM GIÁC MÁT'], ['Se lạnh', 'CẢM GIÁC MÁT']],
            'Từ ngữ gợi cảm giác ấm hợp với màu nóng, gợi mát hợp với màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các bức tranh (theo mô tả) vào nhóm: VUI TƯƠI / TRẦM LẮNG.',
            [['Vườn hoa rực rỡ', 'VUI TƯƠI'], ['Hoàng hôn vàng', 'VUI TƯƠI'], ['Đêm mưa xám', 'TRẦM LẮNG'], ['Biển đêm xanh thẫm', 'TRẦM LẮNG']],
            'Màu tươi sáng tạo vẻ vui tươi, màu trầm tối tạo vẻ trầm lắng.', $d);
        $this->fill($s, 'Màu ___ thường gợi cảm giác vui tươi, ấm áp như nắng mai.', [[0, 'vàng']],
            'Màu vàng như nắng mai gợi cảm giác vui tươi, ấm áp.', $d);
        $this->fill($s, 'Màu vàng thuộc nhóm màu ___, gợi cảm giác ấm áp.', [[0, 'nóng']],
            'Màu vàng là một trong ba màu nóng: đỏ, cam, vàng.', $d);
        $this->fill($s, 'Vẽ cảnh biển đêm yên tĩnh nên dùng gam màu ___.', [[0, 'lạnh']],
            'Gam màu lạnh như xanh dương thẫm rất hợp với cảnh biển đêm yên tĩnh.', $d);
        $this->fill($s, 'Màu đỏ và màu cam đều thuộc nhóm màu ___, gợi sự sôi động.', [[0, 'nóng']],
            'Đỏ và cam là hai màu nóng tiêu biểu, gợi sự sôi động, nhiệt huyết.', $d);
        $this->fill($s, 'Màu ___ gợi cảm giác mát mẻ như nước biển.', [[0, 'xanh dương']],
            'Màu xanh dương gợi cảm giác mát mẻ như nước biển, bầu trời.', $d);
    }

    // ===== 9. Sắc độ và hòa sắc lớp 7: đậm – nhạt, màu tương phản (1) (dễ) =====
    private function seedMtMauSacLop71(): void
    {
        $s = 'mt-mau-sac-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Thêm màu đen vào màu vàng ta được màu gì?',
            ['Vàng đậm (vàng sẫm)', 'Vàng nhạt', 'Màu cam', 'Màu trắng'], 0,
            'Thêm màu đen làm màu vàng đậm và tối đi, ta được vàng sẫm.', $d);
        $this->quiz($s, 'Cặp màu tương phản của màu lam là màu nào?',
            ['Màu cam', 'Màu đỏ', 'Màu vàng', 'Màu tím'], 0,
            'Màu cam đối diện màu lam trên vòng tròn màu nên là cặp tương phản của lam.', $d);
        $this->quiz($s, 'Hai màu tương đồng là hai màu như thế nào?',
            ['Đứng cạnh nhau trên vòng tròn màu', 'Đối diện nhau trên vòng tròn màu', 'Không liên quan gì nhau', 'Trái ngược hoàn toàn'], 0,
            'Hai màu tương đồng là hai màu đứng cạnh nhau trên vòng tròn màu.', $d);
        $this->quiz($s, 'Muốn bức tranh êm dịu, hài hòa, em nên dùng cặp màu nào?',
            ['Màu tương đồng', 'Màu tương phản', 'Màu đối lập', 'Màu ngẫu nhiên'], 0,
            'Màu tương đồng đứng cạnh nhau nên tạo cảm giác êm dịu, hài hòa.', $d);
        $this->quiz($s, 'Sắc độ đậm của màu đỏ còn được gọi là gì?',
            ['Đỏ sẫm (đỏ đậm)', 'Đỏ nhạt', 'Màu hồng', 'Đỏ tươi'], 0,
            'Sắc độ đậm của màu đỏ được gọi là đỏ sẫm hay đỏ đậm.', $d);
        $this->matching($s, 'Nối mỗi cặp màu với quan hệ của chúng.',
            [['Đỏ – xanh lá', 'Tương phản'], ['Vàng – cam', 'Tương đồng'], ['Lam – cam', 'Tương phản'], ['Xanh lá – xanh dương', 'Tương đồng']],
            'Cặp đối diện nhau là tương phản, cặp đứng cạnh nhau là tương đồng.', $d);
        $this->matching($s, 'Nối mỗi cách pha với sắc độ thu được.',
            [['Đỏ + trắng', 'Đỏ nhạt'], ['Đỏ + đen', 'Đỏ đậm'], ['Vàng + trắng', 'Vàng nhạt'], ['Lam + đen', 'Lam đậm']],
            'Thêm trắng cho sắc độ nhạt, thêm đen cho sắc độ đậm.', $d);
        $this->matching($s, 'Nối mỗi mục đích với cách dùng màu phù hợp.',
            [['Làm nổi bật', 'Dùng màu tương phản'], ['Tạo êm dịu', 'Dùng màu tương đồng'], ['Vẽ bóng tối', 'Dùng sắc độ đậm'], ['Vẽ ánh sáng', 'Dùng sắc độ nhạt']],
            'Mục đích khác nhau thì cách chọn màu và sắc độ cũng khác nhau.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.',
            [['Sắc độ', 'Độ đậm nhạt'], ['Hòa sắc', 'Cách phối màu'], ['Tương phản', 'Đối lập'], ['Tương đồng', 'Gần gũi']],
            'Sắc độ là độ đậm nhạt, hòa sắc là cách phối màu trong tranh.', $d);
        $this->matching($s, 'Nối mỗi màu với cặp màu tương phản của nó.',
            [['Vàng', 'Tím'], ['Đỏ', 'Xanh lá'], ['Lam', 'Cam'], ['Cam', 'Lam']],
            'Cặp tương phản là hai màu đối diện nhau trên vòng tròn màu.', $d);
        $this->sortQ($s, 'Xếp các cặp màu vào nhóm: TƯƠNG PHẢN / TƯƠNG ĐỒNG.',
            [['Đỏ – xanh lá', 'TƯƠNG PHẢN'], ['Vàng – tím', 'TƯƠNG PHẢN'], ['Vàng – cam', 'TƯƠNG ĐỒNG'], ['Lam – tím', 'TƯƠNG ĐỒNG']],
            'Cặp đối diện là tương phản, cặp kề nhau là tương đồng.', $d);
        $this->sortQ($s, 'Xếp các màu đã pha vào nhóm: SẮC ĐỘ ĐẬM / SẮC ĐỘ NHẠT.',
            [['Đỏ + đen', 'SẮC ĐỘ ĐẬM'], ['Xanh navy', 'SẮC ĐỘ ĐẬM'], ['Màu hồng', 'SẮC ĐỘ NHẠT'], ['Vàng nhạt', 'SẮC ĐỘ NHẠT']],
            'Pha thêm đen cho sắc độ đậm, pha thêm trắng cho sắc độ nhạt.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Trắng làm màu nhạt đi', 'ĐÚNG'], ['Màu tương phản đặt cạnh nhau sẽ nổi bật', 'ĐÚNG'], ['Đen làm màu sáng lên', 'SAI'], ['Sắc độ là độ to nhỏ của màu', 'SAI']],
            'Màu đen làm tối màu; sắc độ là độ đậm nhạt của màu sắc.', $d);
        $this->sortQ($s, 'Xếp các cách dùng màu vào nhóm: NỔI BẬT / HÀI HÒA.',
            [['Đỏ trên nền xanh lá', 'NỔI BẬT'], ['Tím trên nền vàng', 'NỔI BẬT'], ['Vàng cạnh cam', 'HÀI HÒA'], ['Lam cạnh xanh lá', 'HÀI HÒA']],
            'Màu tương phản tạo nổi bật, màu tương đồng tạo hài hòa.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU ĐẬM / MÀU NHẠT.',
            [['Nâu đất', 'MÀU ĐẬM'], ['Xanh rêu', 'MÀU ĐẬM'], ['Hồng phấn', 'MÀU NHẠT'], ['Màu be', 'MÀU NHẠT']],
            'Nâu đất, xanh rêu là màu đậm; hồng phấn, màu be là màu nhạt.', $d);
        $this->fill($s, 'Thêm màu ___ vào màu sắc sẽ làm màu đậm và tối đi.', [[0, 'đen']],
            'Màu đen có tác dụng làm mọi màu sắc đậm và tối đi.', $d);
        $this->fill($s, 'Cặp màu tương phản của màu xanh lá là màu ___.', [[0, 'đỏ']],
            'Đỏ đối diện xanh lá trên vòng tròn màu nên là cặp tương phản của nhau.', $d);
        $this->fill($s, 'Hai màu đứng cạnh nhau trên vòng tròn màu gọi là màu tương ___.', [[0, 'đồng']],
            'Hai màu kề nhau trên vòng tròn màu được gọi là màu tương đồng.', $d);
        $this->fill($s, 'Muốn tranh êm dịu, hài hòa, nên dùng các màu ___ đồng với nhau.', [[0, 'tương']],
            'Các màu tương đồng đứng cạnh nhau tạo cảm giác êm dịu, hài hòa.', $d);
        $this->fill($s, 'Độ ___ nhạt của màu sắc tạo nên chiều sâu cho bức tranh.', [[0, 'đậm']],
            'Khai thác độ đậm nhạt (sắc độ) giúp bức tranh có chiều sâu.', $d);
    }

    // ===== 10. Màu chủ đạo trong tranh lớp 7: bố cục màu (2) (trung bình) =====
    private function seedMtMauSacLop72(): void
    {
        $s = 'mt-mau-sac-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Màu chủ đạo thường chiếm diện tích như thế nào trong tranh?',
            ['Diện tích lớn nhất', 'Một chấm rất nhỏ', 'Đúng một nửa khung tranh', 'Không quan trọng'], 0,
            'Màu chủ đạo là màu chiếm diện tích lớn nhất, quyết định không khí chung của tranh.', $d);
        $this->quiz($s, 'Điểm nhấn màu nên chiếm diện tích như thế nào?',
            ['Nhỏ nhưng tương phản mạnh', 'Thật lớn', 'Bằng màu chủ đạo', 'Không cần có điểm nhấn'], 0,
            'Điểm nhấn chỉ cần diện tích nhỏ nhưng tương phản mạnh để thu hút mắt nhìn.', $d);
        $this->quiz($s, 'Tranh vẽ mùa thu thường lấy màu chủ đạo nào?',
            ['Vàng, cam, nâu', 'Xanh dương, tím', 'Đen, xám', 'Trắng, be'], 0,
            'Mùa thu với lá vàng, quả chín hợp với gam vàng, cam, nâu làm màu chủ đạo.', $d);
        $this->quiz($s, 'Muốn tạo điểm nhấn trên nền xanh lá, em nên dùng màu nào?',
            ['Màu đỏ', 'Màu xanh lá nhạt', 'Màu xanh dương', 'Màu xám'], 0,
            'Màu đỏ tương phản với xanh lá nên tạo điểm nhấn rất tốt trên nền xanh lá.', $d);
        $this->quiz($s, 'Bố cục màu tốt giúp bức tranh như thế nào?',
            ['Hài hòa, có trọng tâm rõ ràng', 'Rối mắt, khó nhìn', 'Nhạt nhòa, thiếu sức sống', 'Chỉ còn một màu'], 0,
            'Bố cục màu tốt với màu chủ đạo và điểm nhấn rõ giúp tranh hài hòa, có trọng tâm.', $d);
        $this->matching($s, 'Nối mỗi thành phần màu với vai trò của nó trong tranh.',
            [['Màu chủ đạo', 'Tạo không khí chung'], ['Điểm nhấn', 'Thu hút mắt nhìn'], ['Màu nền', 'Làm nổi chủ thể'], ['Màu phụ', 'Hỗ trợ hài hòa']],
            'Mỗi thành phần màu đảm nhận một vai trò riêng trong bố cục màu của tranh.', $d);
        $this->matching($s, 'Nối mỗi chủ đề tranh với gam màu chủ đạo phù hợp.',
            [['Mùa xuân', 'Xanh lá – hồng'], ['Mùa đông', 'Xanh dương – trắng'], ['Lễ hội', 'Đỏ – vàng'], ['Đêm trăng', 'Xanh thẫm – vàng']],
            'Chọn gam màu chủ đạo hợp với chủ đề giúp tranh truyền tải đúng không khí.', $d);
        $this->matching($s, 'Nối mỗi cách đặt điểm nhấn với hiệu quả của nó.',
            [['Màu tương phản', 'Nổi bật mạnh'], ['Màu sáng trên nền tối', 'Thu hút mắt'], ['Chi tiết nhỏ độc đáo', 'Gây tò mò'], ['Màu lặp lại', 'Tạo nhịp điệu']],
            'Điểm nhấn có thể tạo bằng màu tương phản, độ sáng hoặc chi tiết độc đáo.', $d);
        $this->matching($s, 'Nối mỗi lỗi dùng màu với cách sửa.',
            [['Quá nhiều màu nổi', 'Chọn một màu chủ đạo'], ['Không có điểm nhấn', 'Thêm mảng tương phản'], ['Màu chủ đạo mờ nhạt', 'Tăng diện tích'], ['Màu sắc lộn xộn', 'Giảm số màu']],
            'Sửa lỗi dùng màu bằng cách xác định rõ màu chủ đạo và điểm nhấn.', $d);
        $this->matching($s, 'Nối mỗi bức tranh (theo mô tả) với màu chủ đạo của nó.',
            [['Cánh đồng lúa chín', 'Vàng'], ['Biển xanh', 'Xanh dương'], ['Rừng thông', 'Xanh lá'], ['Hoàng hôn', 'Cam']],
            'Màu chủ đạo thường lấy từ màu sắc đặc trưng nhất của chủ đề tranh.', $d);
        $this->sortQ($s, 'Xếp các mảng màu vào nhóm: MÀU CHỦ ĐẠO / ĐIỂM NHẤN.',
            [['Nền trời xanh rộng', 'MÀU CHỦ ĐẠO'], ['Mảng vàng lớn', 'MÀU CHỦ ĐẠO'], ['Chấm đỏ nhỏ', 'ĐIỂM NHẤN'], ['Đốm trắng nhỏ', 'ĐIỂM NHẤN']],
            'Mảng màu lớn là màu chủ đạo, mảng nhỏ tương phản là điểm nhấn.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Màu chủ đạo chiếm diện tích lớn nhất', 'ĐÚNG'], ['Màu tương phản tạo điểm nhấn tốt', 'ĐÚNG'], ['Điểm nhấn nên thật lớn', 'SAI'], ['Tranh không cần màu chủ đạo', 'SAI']],
            'Điểm nhấn chỉ cần nhỏ nhưng tương phản; tranh cần màu chủ đạo để thống nhất.', $d);
        $this->sortQ($s, 'Xếp các gam màu vào nhóm: ẤM ÁP / MÁT MẺ.',
            [['Đỏ – cam', 'ẤM ÁP'], ['Vàng', 'ẤM ÁP'], ['Xanh dương', 'MÁT MẺ'], ['Tím', 'MÁT MẺ']],
            'Gam ấm áp từ màu nóng, gam mát mẻ từ màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các bức tranh (theo mô tả) vào nhóm: GAM NÓNG / GAM LẠNH chủ đạo.',
            [['Lễ hội lửa trại', 'GAM NÓNG'], ['Sa mạc nắng', 'GAM NÓNG'], ['Núi tuyết', 'GAM LẠNH'], ['Đại dương', 'GAM LẠNH']],
            'Khung cảnh nóng dùng gam nóng, khung cảnh lạnh dùng gam lạnh làm chủ đạo.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: LÀM TRƯỚC / LÀM SAU khi tô màu tranh.',
            [['Chọn màu chủ đạo', 'LÀM TRƯỚC'], ['Tô các mảng nền lớn', 'LÀM TRƯỚC'], ['Thêm điểm nhấn', 'LÀM SAU'], ['Hoàn thiện chi tiết', 'LÀM SAU']],
            'Chọn màu chủ đạo và tô nền lớn trước, thêm điểm nhấn và chi tiết sau.', $d);
        $this->fill($s, 'Màu chiếm diện tích ___ nhất trong tranh gọi là màu chủ đạo.', [[0, 'lớn']],
            'Màu chủ đạo là màu chiếm diện tích lớn nhất trong bức tranh.', $d);
        $this->fill($s, 'Điểm nhấn màu thường có diện tích ___ nhưng tương phản mạnh.', [[0, 'nhỏ']],
            'Điểm nhấn chỉ cần diện tích nhỏ nhưng phải tương phản mạnh với xung quanh.', $d);
        $this->fill($s, 'Muốn nổi bật trên nền xanh, điểm nhấn nên dùng màu ___ với nền.', [[0, 'tương phản']],
            'Màu tương phản với nền sẽ tạo điểm nhấn nổi bật, thu hút mắt nhìn.', $d);
        $this->fill($s, 'Tranh mùa thu thường lấy gam màu ___ làm chủ đạo.', [[0, 'nóng']],
            'Gam màu nóng như vàng, cam, nâu rất hợp làm màu chủ đạo cho tranh mùa thu.', $d);
        $this->fill($s, 'Bố cục màu ___ hòa giúp bức tranh có trọng tâm rõ ràng.', [[0, 'hài']],
            'Bố cục màu hài hòa với màu chủ đạo và điểm nhấn rõ giúp tranh có trọng tâm.', $d);
    }

    // ===== 11. Đường nét lớp 7: các loại nét và cảm xúc (1) (dễ) =====
    private function seedMtDuongNetLop71(): void
    {
        $s = 'mt-duong-net-bo-cuc-lop-7-1'; $d = 'de';
        $this->quiz($s, 'Nét thẳng ngang thường gợi cảm giác gì?',
            ['Yên tĩnh, ổn định', 'Sôi động, náo nhiệt', 'Buồn bã, u ám', 'Hồi hộp, lo sợ'], 0,
            'Nét thẳng ngang nằm yên nên gợi cảm giác yên tĩnh, ổn định.', $d);
        $this->quiz($s, 'Nét nào phù hợp nhất để vẽ mái tóc bay trong gió?',
            ['Nét cong lượn', 'Nét thẳng đứng', 'Nét gấp khúc', 'Nét chấm'], 0,
            'Nét cong lượn mềm mại rất hợp để vẽ mái tóc bay trong gió.', $d);
        $this->quiz($s, 'Trong vẽ kỹ thuật, nét liền mảnh dùng để vẽ gì?',
            ['Đường kích thước, đường gióng', 'Đường bao thấy được', 'Đường bị khuất', 'Trục đối xứng'], 0,
            'Nét liền mảnh dùng để vẽ đường kích thước và đường gióng trong bản vẽ kỹ thuật.', $d);
        $this->quiz($s, 'Nét lượn sóng thường dùng để diễn tả điều gì?',
            ['Mặt nước, sự êm đềm', 'Tia chớp', 'Tòa nhà cao tầng', 'Bánh xe'], 0,
            'Nét lượn sóng mềm mại rất hợp để diễn tả mặt nước và sự êm đềm.', $d);
        $this->quiz($s, 'Đường bao của vật thể trong vẽ kỹ thuật được vẽ bằng nét nào?',
            ['Nét liền đậm', 'Nét đứt', 'Nét chấm gạch', 'Nét lượn sóng'], 0,
            'Đường bao thấy được của vật thể được vẽ bằng nét liền đậm.', $d);
        $this->matching($s, 'Nối mỗi loại nét với cảm giác nó gợi ra.',
            [['Nét ngang', 'Yên tĩnh'], ['Nét xiên', 'Chuyển động'], ['Nét lượn sóng', 'Êm đềm'], ['Nét xoắn', 'Rối rắm']],
            'Mỗi loại nét mang một cảm xúc riêng, cần chọn nét hợp với nội dung.', $d);
        $this->matching($s, 'Nối mỗi hình ảnh với loại nét phù hợp để vẽ nó.',
            [['Mái tóc bay', 'Nét cong'], ['Tòa nhà', 'Nét thẳng'], ['Sóng biển', 'Nét lượn'], ['Tia sét', 'Nét gấp khúc']],
            'Chọn nét vẽ đúng đặc điểm của đối tượng giúp tranh sinh động hơn.', $d);
        $this->matching($s, 'Nối mỗi loại nét kỹ thuật với công dụng của nó.',
            [['Nét liền đậm', 'Đường bao thấy'], ['Nét liền mảnh', 'Đường kích thước'], ['Nét chấm gạch', 'Trục đối xứng'], ['Nét đứt', 'Đường khuất']],
            'Vẽ kỹ thuật dùng các loại nét khác nhau cho từng công dụng riêng.', $d);
        $this->matching($s, 'Nối mỗi cảm xúc với nét vẽ thể hiện nó.',
            [['Vui vẻ', 'Nét cong bay'], ['Giận dữ', 'Nét gấp khúc'], ['Buồn bã', 'Nét cong rủ xuống'], ['Bình yên', 'Nét ngang']],
            'Cảm xúc có thể diễn tả qua hướng và hình dáng của đường nét.', $d);
        $this->matching($s, 'Nối mỗi đối tượng với nét đặc trưng khi vẽ nó.',
            [['Cầu vồng', 'Nét cong'], ['Hàng rào', 'Nét thẳng'], ['Dòng sông', 'Nét lượn'], ['Ngọn núi', 'Nét xiên']],
            'Mỗi đối tượng có đường nét đặc trưng riêng khi vẽ.', $d);
        $this->sortQ($s, 'Xếp các vật vào nhóm vẽ bằng: NÉT THẲNG / NÉT CONG.',
            [['Quyển vở', 'NÉT THẲNG'], ['Cái thước', 'NÉT THẲNG'], ['Quả táo', 'NÉT CONG'], ['Vầng trăng', 'NÉT CONG']],
            'Vật có góc cạnh dùng nét thẳng, vật tròn trịa dùng nét cong.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Nét đứt vẽ đường bị khuất', 'ĐÚNG'], ['Nét liền đậm vẽ đường bao thấy được', 'ĐÚNG'], ['Nét lượn sóng gợi sự cứng rắn', 'SAI'], ['Nét gấp khúc gợi êm đềm', 'SAI']],
            'Nét lượn sóng gợi êm đềm, nét gấp khúc gợi mạnh mẽ, gấp gáp.', $d);
        $this->sortQ($s, 'Xếp các loại nét vào nhóm gợi cảm giác: ÊM DỊU / MẠNH MẼ.',
            [['Nét cong nhẹ', 'ÊM DỊU'], ['Nét lượn sóng', 'ÊM DỊU'], ['Nét gấp khúc', 'MẠNH MẼ'], ['Nét xiên dài', 'MẠNH MẼ']],
            'Nét cong, lượn êm dịu; nét gấp khúc, xiên mạnh mẽ.', $d);
        $this->sortQ($s, 'Xếp các nét kỹ thuật vào nhóm: ĐƯỜNG NHÌN THẤY / ĐƯỜNG BỊ KHUẤT.',
            [['Nét liền đậm', 'ĐƯỜNG NHÌN THẤY'], ['Nét liền mảnh', 'ĐƯỜNG NHÌN THẤY'], ['Nét chấm gạch', 'ĐƯỜNG NHÌN THẤY'], ['Nét đứt', 'ĐƯỜNG BỊ KHUẤT']],
            'Đường bị khuất được vẽ bằng nét đứt, các đường còn lại là đường nhìn thấy.', $d);
        $this->sortQ($s, 'Xếp các nét vào nhóm: NÉT CƠ BẢN / NÉT TRANG TRÍ.',
            [['Nét thẳng', 'NÉT CƠ BẢN'], ['Nét cong', 'NÉT CƠ BẢN'], ['Nét xoắn trang trí', 'NÉT TRANG TRÍ'], ['Nét chấm bi', 'NÉT TRANG TRÍ']],
            'Nét thẳng, nét cong là nét cơ bản; nét xoắn, chấm bi dùng để trang trí.', $d);
        $this->fill($s, 'Nét ___ ngang thường gợi cảm giác yên tĩnh, ổn định.', [[0, 'thẳng']],
            'Nét thẳng ngang nằm yên nên gợi cảm giác yên tĩnh, ổn định.', $d);
        $this->fill($s, 'Trong vẽ kỹ thuật, trục đối xứng được vẽ bằng nét ___ gạch.', [[0, 'chấm']],
            'Trục đối xứng trong vẽ kỹ thuật được vẽ bằng nét chấm gạch.', $d);
        $this->fill($s, 'Nét cong ___ nhẹ rất phù hợp để vẽ mái tóc bay trong gió.', [[0, 'lượn']],
            'Nét cong lượn nhẹ mềm mại, hợp để vẽ mái tóc bay trong gió.', $d);
        $this->fill($s, 'Đường bao thấy được của vật thể được vẽ bằng nét liền ___.', [[0, 'đậm']],
            'Đường bao thấy được của vật thể trong vẽ kỹ thuật dùng nét liền đậm.', $d);
        $this->fill($s, 'Nét ___ khúc thường dùng để diễn tả tia chớp.', [[0, 'gấp']],
            'Nét gấp khúc mạnh mẽ, gấp gáp rất hợp để diễn tả tia chớp.', $d);
    }

    // ===== 12. Bố cục lớp 7: đối xứng, cân bằng và điểm nhấn (2) (trung bình) =====
    private function seedMtDuongNetLop72(): void
    {
        $s = 'mt-duong-net-bo-cuc-lop-7-2'; $d = 'trung_binh';
        $this->quiz($s, 'Bố cục tự do (bất đối xứng) có đặc điểm gì?',
            ['Các mảng hình khác nhau hai bên nhưng vẫn cân bằng', 'Hai bên hoàn toàn giống nhau', 'Không theo quy tắc nào', 'Chỉ vẽ một vật duy nhất'], 0,
            'Bố cục bất đối xứng không gò hai bên giống nhau nhưng trọng lượng thị giác vẫn cân bằng.', $d);
        $this->quiz($s, 'Đặt vật chính ở giao điểm của các đường một phần ba giúp gì?',
            ['Tạo vị trí đẹp, thu hút mắt nhìn', 'Làm tranh bị lệch', 'Không có tác dụng gì', 'Chỉ dùng trong nhiếp ảnh'], 0,
            'Giao điểm một phần ba là vị trí mạnh, giúp điểm nhấn thu hút mắt nhìn.', $d);
        $this->quiz($s, 'Cân bằng trong bố cục nghĩa là gì?',
            ['Các mảng hình, màu sắc phân bố hài hòa', 'Hai bên phải giống hệt nhau', 'Mọi vật đều to bằng nhau', 'Chỉ dùng một màu duy nhất'], 0,
            'Cân bằng là sự phân bố hài hòa các mảng hình và màu sắc, không nhất thiết đối xứng.', $d);
        $this->quiz($s, 'Điểm nhấn đặt ở đâu sẽ kém hiệu quả nhất?',
            ['Sát mép tranh, góc khuất', 'Giao điểm một phần ba', 'Vị trí trung tâm', 'Nơi có màu tương phản'], 0,
            'Đặt điểm nhấn sát mép tranh hay góc khuất khiến người xem khó chú ý tới.', $d);
        $this->quiz($s, 'Bố cục đường chéo thường tạo cảm giác gì?',
            ['Chuyển động, năng động', 'Tĩnh lặng hoàn toàn', 'Buồn ngủ', 'Đơn điệu, nhàm chán'], 0,
            'Đường chéo phá vỡ sự ổn định nên tạo cảm giác chuyển động, năng động.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.',
            [['Đối xứng', 'Hai bên giống nhau'], ['Cân bằng', 'Phân bố hài hòa'], ['Điểm nhấn', 'Thu hút mắt nhìn'], ['Nhịp điệu', 'Lặp lại có quy luật']],
            'Nắm vững các khái niệm giúp em sắp xếp bố cục tranh tốt hơn.', $d);
        $this->matching($s, 'Nối mỗi loại tranh với bố cục thường dùng.',
            [['Tranh thờ', 'Đối xứng'], ['Tranh phong cảnh', 'Một phần ba'], ['Tranh chân dung', 'Trung tâm'], ['Tranh hành động', 'Đường chéo']],
            'Mỗi loại tranh hợp với một kiểu bố cục riêng.', $d);
        $this->matching($s, 'Nối mỗi vị trí với vai trò của nó trong bố cục.',
            [['Giao điểm 1/3', 'Vị trí mạnh'], ['Trung tâm', 'Tập trung'], ['Tiền cảnh', 'Gần gũi'], ['Góc khuất', 'Vị trí yếu']],
            'Đặt yếu tố chính vào vị trí mạnh giúp tranh thu hút người xem.', $d);
        $this->matching($s, 'Nối mỗi cách sắp xếp với cảm giác nó tạo ra.',
            [['Đối xứng', 'Trang nghiêm'], ['Tự do', 'Thoải mái'], ['Dồn một bên', 'Mất cân bằng'], ['Đường chéo', 'Năng động']],
            'Cách sắp xếp các mảng hình quyết định cảm giác chung của bức tranh.', $d);
        $this->matching($s, 'Nối mỗi lỗi bố cục với cách khắc phục.',
            [['Tranh bị lệch', 'Thêm chi tiết phía nhẹ'], ['Không có trọng tâm', 'Tạo điểm nhấn'], ['Chi tiết dàn đều', 'Nhóm lại thành cụm'], ['Mép tranh trống trải', 'Cân đối lại bố cục']],
            'Biết cách sửa lỗi bố cục giúp bức tranh cân đối và hoàn chỉnh hơn.', $d);
        $this->sortQ($s, 'Xếp các hình vào nhóm: BỐ CỤC ĐỐI XỨNG / BỐ CỤC TỰ DO.',
            [['Hai con rồng chầu mặt trời', 'BỐ CỤC ĐỐI XỨNG'], ['Mặt trống đồng', 'BỐ CỤC ĐỐI XỨNG'], ['Cảnh chợ quê nhộn nhịp', 'BỐ CỤC TỰ DO'], ['Đàn cá bơi lượn', 'BỐ CỤC TỰ DO']],
            'Đối xứng hai bên giống nhau qua trục, tự do sắp xếp thoải mái hơn.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Điểm nhấn thu hút mắt nhìn đầu tiên', 'ĐÚNG'], ['Giao điểm 1/3 là vị trí mạnh', 'ĐÚNG'], ['Bố cục đối xứng luôn nhàm chán', 'SAI'], ['Tranh không cần cân bằng', 'SAI']],
            'Bố cục đối xứng có thể rất đẹp; mọi bức tranh đều cần sự cân bằng.', $d);
        $this->sortQ($s, 'Xếp các vị trí vào nhóm: VỊ TRÍ MẠNH / VỊ TRÍ YẾU.',
            [['Giao điểm 1/3', 'VỊ TRÍ MẠNH'], ['Trung tâm tranh', 'VỊ TRÍ MẠNH'], ['Sát mép tranh', 'VỊ TRÍ YẾU'], ['Góc khuất', 'VỊ TRÍ YẾU']],
            'Đặt yếu tố quan trọng vào vị trí mạnh để thu hút người xem.', $d);
        $this->sortQ($s, 'Xếp các cách sắp xếp vào nhóm: CÂN BẰNG / MẤT CÂN BẰNG.',
            [['Hai bên tương đương', 'CÂN BẰNG'], ['Màu sắc phân bố đều', 'CÂN BẰNG'], ['Một bên nặng trĩu', 'MẤT CÂN BẰNG'], ['Chi tiết dồn một góc', 'MẤT CÂN BẰNG']],
            'Cân bằng là phân bố hài hòa, không để một bên quá nặng.', $d);
        $this->sortQ($s, 'Xếp các đường nét vào nhóm: TẠO CHUYỂN ĐỘNG / TẠO TĨNH LẶNG.',
            [['Đường chéo', 'TẠO CHUYỂN ĐỘNG'], ['Đường cong lượn', 'TẠO CHUYỂN ĐỘNG'], ['Đường ngang', 'TẠO TĨNH LẶNG'], ['Đường thẳng đứng', 'TẠO TĨNH LẶNG']],
            'Đường chéo, đường cong lượn tạo chuyển động; đường ngang, thẳng đứng tạo tĩnh lặng.', $d);
        $this->fill($s, 'Bố cục ___ do tạo cảm giác thoải mái, tự nhiên.', [[0, 'tự']],
            'Bố cục tự do không gò bó hai bên giống nhau, tạo cảm giác thoải mái.', $d);
        $this->fill($s, 'Vị trí giao nhau của các đường một phần ba là vị trí ___ trong tranh.', [[0, 'mạnh']],
            'Giao điểm một phần ba là vị trí mạnh, hợp để đặt điểm nhấn.', $d);
        $this->fill($s, 'Đặt đường chân trời ở vị trí một phần ba tranh thường tạo bố cục ___.', [[0, 'đẹp']],
            'Đường chân trời ở vị trí một phần ba tạo bố cục đẹp, tránh đơn điệu.', $d);
        $this->fill($s, 'Sự phân bố hài hòa các mảng hình và màu sắc gọi là sự cân ___.', [[0, 'bằng']],
            'Cân bằng là sự phân bố hài hòa các mảng hình và màu sắc trong tranh.', $d);
        $this->fill($s, 'Chi tiết được mắt nhìn tới đầu tiên gọi là ___ nhấn.', [[0, 'điểm']],
            'Điểm nhấn là chi tiết được mắt người xem chú ý tới đầu tiên.', $d);
    }

    // ===== 13. Hình khối và không gian lớp 8: vẽ theo mẫu (1) (trung bình) =====
    private function seedMtDuongNetLop81(): void
    {
        $s = 'mt-duong-net-bo-cuc-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Hình chóp có cấu tạo như thế nào?',
            ['Đáy là đa giác, các mặt bên là tam giác', 'Đáy là hình tròn', 'Không có đáy', 'Đáy là hình cầu'], 0,
            'Hình chóp có đáy là đa giác và các mặt bên đều là hình tam giác.', $d);
        $this->quiz($s, 'Khi vẽ vật thể, ánh sáng chiếu từ bên trái thì bóng đổ nằm ở đâu?',
            ['Bên phải vật thể', 'Bên trái vật thể', 'Phía trên vật thể', 'Không có bóng đổ'], 0,
            'Bóng đổ luôn nằm ở phía đối diện với nguồn sáng.', $d);
        $this->quiz($s, 'Phần sáng nhất trên vật thể thường nằm ở đâu?',
            ['Mặt hướng về nguồn sáng', 'Mặt quay lưng với nguồn sáng', 'Đáy vật thể', 'Vùng bóng đổ'], 0,
            'Mặt hướng về nguồn sáng nhận nhiều ánh sáng nhất nên sáng nhất.', $d);
        $this->quiz($s, 'Điểm tụ trong phối cảnh là gì?',
            ['Điểm các đường song song hội tụ', 'Điểm sáng nhất của tranh', 'Điểm đặt bút vẽ', 'Tâm của vật thể'], 0,
            'Trong phối cảnh, các đường thẳng song song hội tụ tại điểm tụ trên đường chân trời.', $d);
        $this->quiz($s, 'Muốn vẽ cái chai, em nên bắt đầu phác từ khối nào?',
            ['Hình trụ', 'Hình cầu', 'Hình nón', 'Hình lập phương'], 0,
            'Cái chai có thân tròn đều nên bắt đầu phác từ hình trụ là hợp lý nhất.', $d);
        $this->matching($s, 'Nối mỗi vật với khối cơ bản gần với nó nhất.',
            [['Quả dưa hấu', 'Hình cầu'], ['Hộp sữa', 'Hình hộp'], ['Cái phễu', 'Hình nón'], ['Cột nhà', 'Hình trụ']],
            'Phác vật từ khối cơ bản gần nhất giúp vẽ đúng hình dáng nhanh chóng.', $d);
        $this->matching($s, 'Nối mỗi khái niệm phối cảnh với ý nghĩa của nó.',
            [['Điểm tụ', 'Nơi các đường hội tụ'], ['Đường chân trời', 'Ngang tầm mắt'], ['Tiền cảnh', 'Gần người xem'], ['Bóng đổ', 'Vùng tối sau vật']],
            'Nắm các khái niệm phối cảnh giúp vẽ không gian đúng và có chiều sâu.', $d);
        $this->matching($s, 'Nối mỗi phần của vật với đặc điểm ánh sáng của nó.',
            [['Mặt sáng', 'Hướng về nguồn sáng'], ['Mặt tối', 'Quay lưng nguồn sáng'], ['Bóng đổ', 'Nằm trên mặt đất'], ['Điểm sáng', 'Phản chiếu mạnh']],
            'Ánh sáng tạo ra mặt sáng, mặt tối và bóng đổ trên vật thể.', $d);
        $this->matching($s, 'Nối mỗi cách vẽ với ý nghĩa của nó.',
            [['Vẽ phác khối', 'Xác định hình dáng'], ['Tô đậm nhạt', 'Tạo khối'], ['Vẽ bóng đổ', 'Tạo không gian'], ['Vẽ chi tiết', 'Hoàn thiện']],
            'Vẽ theo mẫu theo trình tự: phác khối, tô đậm nhạt, vẽ bóng đổ, chi tiết.', $d);
        $this->matching($s, 'Nối mỗi vật trong tranh phong cảnh với cách vẽ theo phối cảnh.',
            [['Cây gần', 'Vẽ to rõ'], ['Cây xa', 'Vẽ nhỏ mờ'], ['Người gần', 'Vẽ chi tiết'], ['Núi xa', 'Vẽ mờ nhạt']],
            'Vật càng xa càng vẽ nhỏ và mờ theo quy luật phối cảnh.', $d);
        $this->sortQ($s, 'Xếp các vật vào nhóm: KHỐI TRÒN / KHỐI VUÔNG.',
            [['Quả bưởi', 'KHỐI TRÒN'], ['Quả bóng', 'KHỐI TRÒN'], ['Cái tủ', 'KHỐI VUÔNG'], ['Viên gạch', 'KHỐI VUÔNG']],
            'Quả bưởi, quả bóng gần hình cầu; cái tủ, viên gạch gần hình hộp.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Vật càng xa vẽ càng nhỏ', 'ĐÚNG'], ['Mặt sáng hướng về nguồn sáng', 'ĐÚNG'], ['Bóng đổ nằm cùng phía nguồn sáng', 'SAI'], ['Không cần vẽ bóng đổ', 'SAI']],
            'Bóng đổ nằm đối diện nguồn sáng và rất cần để tạo không gian cho tranh.', $d);
        $this->sortQ($s, 'Xếp các phần của vật vào nhóm: CÓ ÁNH SÁNG / KHÔNG CÓ ÁNH SÁNG.',
            [['Mặt sáng', 'CÓ ÁNH SÁNG'], ['Điểm sáng', 'CÓ ÁNH SÁNG'], ['Mặt tối', 'KHÔNG CÓ ÁNH SÁNG'], ['Bóng đổ', 'KHÔNG CÓ ÁNH SÁNG']],
            'Mặt sáng và điểm sáng nhận ánh sáng; mặt tối và bóng đổ thiếu ánh sáng.', $d);
        $this->sortQ($s, 'Xếp các vật trong tranh phong cảnh vào nhóm: VẼ TO (gần) / VẼ NHỎ (xa).',
            [['Ngôi nhà gần', 'VẼ TO (gần)'], ['Cây cạnh đường', 'VẼ TO (gần)'], ['Dãy núi xa', 'VẼ NHỎ (xa)'], ['Đám mây xa', 'VẼ NHỎ (xa)']],
            'Vật gần vẽ to rõ, vật xa vẽ nhỏ mờ theo quy luật phối cảnh.', $d);
        $this->sortQ($s, 'Xếp các bước vẽ theo mẫu vào nhóm: LÀM TRƯỚC / LÀM SAU.',
            [['Quan sát kỹ mẫu', 'LÀM TRƯỚC'], ['Phác khối lớn', 'LÀM TRƯỚC'], ['Tô đậm nhạt', 'LÀM SAU'], ['Vẽ chi tiết nhỏ', 'LÀM SAU']],
            'Quan sát và phác khối lớn trước, tô đậm nhạt và chi tiết sau.', $d);
        $this->fill($s, 'Cái phễu có dạng gần với khối ___.', [[0, 'nón']],
            'Cái phễu có miệng tròn và thu nhọn, gần với hình nón nhất.', $d);
        $this->fill($s, 'Trong phối cảnh, các đường thẳng song song hội tụ tại ___ tụ.', [[0, 'điểm']],
            'Điểm tụ là nơi các đường thẳng song song hội tụ trên đường chân trời.', $d);
        $this->fill($s, 'Mặt của vật hướng về nguồn sáng gọi là mặt ___.', [[0, 'sáng']],
            'Mặt sáng là mặt của vật hướng về phía nguồn sáng.', $d);
        $this->fill($s, 'Vẽ vật càng xa thì càng ___ và mờ.', [[0, 'nhỏ']],
            'Theo phối cảnh, vật càng xa càng được vẽ nhỏ và mờ đi.', $d);
        $this->fill($s, 'Vùng tối nằm sau vật, trên mặt đất gọi là bóng ___.', [[0, 'đổ']],
            'Bóng đổ là vùng tối nằm sau vật, ở phía đối diện nguồn sáng.', $d);
    }

    // ===== 14. Trang trí và họa tiết lớp 8: hoa văn dân tộc (2) (trung bình) =====
    private function seedMtDuongNetLop82(): void
    {
        $s = 'mt-duong-net-bo-cuc-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Họa tiết cách điệu là gì?',
            ['Đơn giản hóa hình thật thành họa tiết trang trí', 'Vẽ y hệt ảnh chụp', 'Chỉ dùng màu đen', 'Vẽ ngẫu nhiên không quy luật'], 0,
            'Cách điệu là đơn giản hóa hình ảnh thật thành họa tiết trang trí đẹp mắt.', $d);
        $this->quiz($s, 'Hoa văn thổ cẩm của đồng bào dân tộc thường có đặc điểm gì?',
            ['Màu sắc rực rỡ, hình học đối xứng', 'Chỉ dùng một màu xám', 'Không có quy luật nào', 'Chỉ vẽ bằng bút chì'], 0,
            'Thổ cẩm dân tộc nổi bật với màu sắc rực rỡ và họa tiết hình học đối xứng.', $d);
        $this->quiz($s, 'Họa tiết trên trống đồng Đông Sơn thường diễn tả điều gì?',
            ['Đời sống, lễ hội, thiên nhiên', 'Chiến tranh hiện đại', 'Máy móc công nghiệp', 'Chữ viết'], 0,
            'Họa tiết trống đồng diễn tả đời sống, lễ hội và thiên nhiên của người xưa.', $d);
        $this->quiz($s, 'Trang trí đối xứng thường được dùng ở đâu?',
            ['Viền áo, khăn trải bàn, mặt trống', 'Tranh trừu tượng', 'Ảnh chụp', 'Bản đồ'], 0,
            'Trang trí đối xứng thường dùng cho viền áo, khăn trải bàn, mặt trống đồng.', $d);
        $this->quiz($s, 'Muốn tạo họa tiết mới từ bông hoa, em nên làm gì?',
            ['Cách điệu, đơn giản hóa hình hoa', 'Vẽ y hệt ảnh chụp bông hoa', 'Chỉ tô màu đen', 'Bỏ qua bước phác thảo'], 0,
            'Muốn tạo họa tiết từ bông hoa, em cần cách điệu và đơn giản hóa hình hoa.', $d);
        $this->matching($s, 'Nối mỗi hiện vật với họa tiết đặc trưng của nó.',
            [['Trống đồng', 'Ngôi sao nhiều cánh'], ['Gốm sứ', 'Hoa văn uốn lượn'], ['Thổ cẩm', 'Hình học đối xứng'], ['Mặt nạ', 'Họa tiết mặt người']],
            'Mỗi hiện vật văn hóa đều có họa tiết đặc trưng riêng.', $d);
        $this->matching($s, 'Nối mỗi loại họa tiết với nguồn gốc của nó.',
            [['Hoa sen', 'Thiên nhiên'], ['Hình ziczac', 'Hình học'], ['Chim lạc', 'Huyền thoại'], ['Chữ vạn', 'Tôn giáo']],
            'Họa tiết trang trí lấy cảm hứng từ thiên nhiên, hình học, huyền thoại và tôn giáo.', $d);
        $this->matching($s, 'Nối mỗi cách sắp xếp họa tiết với tên gọi của nó.',
            [['Lặp lại đều đặn', 'Nhịp điệu'], ['Hai bên giống nhau', 'Đối xứng'], ['Xoay quanh tâm', 'Hướng tâm'], ['Sắp xếp tự do', 'Phóng khoáng']],
            'Họa tiết được sắp xếp theo nhịp điệu, đối xứng, hướng tâm hoặc phóng khoáng.', $d);
        $this->matching($s, 'Nối mỗi hiện vật với hình dáng của nó.',
            [['Trống đồng', 'Hình trụ tròn'], ['Bình gốm', 'Hình bầu'], ['Khăn thổ cẩm', 'Hình chữ nhật'], ['Nón lá', 'Hình nón']],
            'Họa tiết trang trí luôn gắn với hình dáng của hiện vật.', $d);
        $this->matching($s, 'Nối mỗi dân tộc với nét trang trí tiêu biểu.',
            [['H’Mông', 'Hoa văn sặc sỡ'], ['Chăm', 'Hoa văn tháp'], ['Kinh', 'Họa tiết trống đồng'], ['Khmer', 'Hoa văn chùa']],
            'Mỗi dân tộc đều có nét hoa văn trang trí mang bản sắc riêng.', $d);
        $this->sortQ($s, 'Xếp các họa tiết vào nhóm: TỪ THIÊN NHIÊN / HÌNH HỌC.',
            [['Hoa sen', 'TỪ THIÊN NHIÊN'], ['Chim lạc', 'TỪ THIÊN NHIÊN'], ['Hình ziczac', 'HÌNH HỌC'], ['Hình thoi', 'HÌNH HỌC']],
            'Hoa sen, chim lạc từ thiên nhiên; ziczac, hình thoi là họa tiết hình học.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Cách điệu là đơn giản hóa hình thật', 'ĐÚNG'], ['Trang trí cần có quy luật', 'ĐÚNG'], ['Họa tiết phải vẽ y hệt ảnh chụp', 'SAI'], ['Thổ cẩm chỉ dùng một màu', 'SAI']],
            'Họa tiết cách điệu từ hình thật và sắp xếp có quy luật; thổ cẩm dùng nhiều màu.', $d);
        $this->sortQ($s, 'Xếp các họa tiết vào nhóm trên HIỆN VẬT tương ứng.',
            [['Ngôi sao', 'Trống đồng'], ['Hoa văn thổ cẩm', 'Khăn áo'], ['Men lam', 'Gốm sứ'], ['Chạm khắc', 'Đồ gỗ']],
            'Mỗi hiện vật mang họa tiết đặc trưng: sao trên trống đồng, men lam trên gốm.', $d);
        $this->sortQ($s, 'Xếp các cách vẽ vào nhóm: CÁCH ĐIỆU / TẢ THỰC.',
            [['Hoa sen đơn giản', 'CÁCH ĐIỆU'], ['Chim lạc', 'CÁCH ĐIỆU'], ['Chân dung chi tiết', 'TẢ THỰC'], ['Phong cảnh tỉ mỉ', 'TẢ THỰC']],
            'Cách điệu đơn giản hóa hình ảnh, tả thực vẽ chi tiết giống thật.', $d);
        $this->sortQ($s, 'Xếp các họa tiết vào nhóm: ĐỐI XỨNG / KHÔNG ĐỐI XỨNG.',
            [['Mặt trống đồng', 'ĐỐI XỨNG'], ['Dải thổ cẩm', 'ĐỐI XỨNG'], ['Cành cây tự nhiên', 'KHÔNG ĐỐI XỨNG'], ['Đàn chim bay', 'KHÔNG ĐỐI XỨNG']],
            'Mặt trống đồng và dải thổ cẩm đối xứng; cành cây, đàn chim sắp xếp tự do.', $d);
        $this->fill($s, 'Đơn giản hóa hình ảnh thật thành họa tiết trang trí gọi là ___ điệu.', [[0, 'cách']],
            'Cách điệu là đơn giản hóa hình ảnh thật thành họa tiết trang trí.', $d);
        $this->fill($s, 'Họa tiết thổ cẩm thường được sắp xếp theo quy luật ___ xứng.', [[0, 'đối']],
            'Họa tiết thổ cẩm thường sắp xếp đối xứng, tạo vẻ cân đối, đẹp mắt.', $d);
        $this->fill($s, 'Trung tâm mặt trống đồng Đông Sơn là hình ngôi sao nhiều ___.', [[0, 'cánh']],
            'Ngôi sao nhiều cánh ở trung tâm là họa tiết đặc trưng của trống đồng.', $d);
        $this->fill($s, 'Hoa văn trên trang phục dân tộc thường lấy cảm hứng từ ___ nhiên.', [[0, 'thiên']],
            'Thiên nhiên là nguồn cảm hứng chính của hoa văn trang phục dân tộc.', $d);
        $this->fill($s, 'Muốn trang trí đẹp, họa tiết cần được sắp xếp có ___ luật.', [[0, 'quy']],
            'Họa tiết trang trí cần sắp xếp có quy luật: đối xứng, nhịp điệu hoặc hướng tâm.', $d);
    }

    // ===== 15. Tranh Đông Hồ lớp 8: làng nghề và đặc điểm (1) (trung bình) =====
    private function seedMtTranhDanGianLop81(): void
    {
        $s = 'mt-tranh-dan-gian-lop-8-1'; $d = 'trung_binh';
        $this->quiz($s, 'Nghề làm tranh Đông Hồ đã có từ khoảng thời gian nào?',
            ['Hàng trăm năm trước, thời phong kiến', 'Mới có khoảng 10 năm nay', 'Thời kỳ đổi mới', 'Thế kỷ 21'], 0,
            'Nghề làm tranh Đông Hồ đã có hàng trăm năm từ thời phong kiến.', $d);
        $this->quiz($s, 'Giấy điệp được làm từ nguyên liệu chính nào?',
            ['Vỏ sò điệp giã nhỏ trộn hồ nếp', 'Bột gỗ ép', 'Nhựa tổng hợp', 'Lá cây ép khô'], 0,
            'Giấy điệp được quét từ vỏ sò điệp giã nhỏ trộn với hồ nếp.', $d);
        $this->quiz($s, 'Mỗi màu trong tranh Đông Hồ được in như thế nào?',
            ['In riêng từng ván màu', 'In một lần tất cả các màu', 'Tô tay hoàn toàn', 'In bằng máy photocopy'], 0,
            'Mỗi màu trong tranh Đông Hồ được in riêng bằng một ván khắc gỗ.', $d);
        $this->quiz($s, 'Bức “Đàn lợn âm dương” vẽ cảnh gì?',
            ['Lợn mẹ cùng đàn con quây quần', 'Chỉ một con lợn', 'Đàn gà con', 'Đàn trâu'], 0,
            'Bức “Đàn lợn âm dương” vẽ lợn mẹ cùng đàn con quây quần, tượng trưng no đủ.', $d);
        $this->quiz($s, 'Tranh Đông Hồ thường được treo ở đâu trong nhà?',
            ['Nơi trang trọng trong ngày Tết', 'Trong nhà kho', 'Ngoài sân phơi', 'Gầm cầu thang'], 0,
            'Tranh Đông Hồ được treo ở nơi trang trọng trong nhà mỗi dịp Tết đến.', $d);
        $this->matching($s, 'Nối mỗi nguyên liệu với sản phẩm nó tạo ra.',
            [['Vỏ điệp', 'Giấy điệp'], ['Gỗ thị', 'Ván khắc'], ['Hoa hòe', 'Màu vàng'], ['Than lá tre', 'Màu đen']],
            'Nguyên liệu tự nhiên tạo nên giấy, ván khắc và màu vẽ tranh Đông Hồ.', $d);
        $this->matching($s, 'Nối mỗi bức tranh với chủ đề của nó.',
            [['Vinh hoa', 'Chúc tụng'], ['Phú quý', 'Sung túc'], ['Đám cưới chuột', 'Châm biếm'], ['Thầy đồ cóc', 'Giáo dục']],
            'Tranh Đông Hồ có các chủ đề: chúc tụng, sung túc, châm biếm, giáo dục.', $d);
        $this->matching($s, 'Nối mỗi công đoạn với nội dung của nó.',
            [['Chọn giấy', 'Dùng giấy điệp'], ['Khắc ván', 'Mỗi màu một ván'], ['In tranh', 'In chồng màu'], ['Phơi khô', 'Hoàn thiện']],
            'Làm tranh Đông Hồ gồm các công đoạn: chọn giấy, khắc ván, in tranh, phơi khô.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với thông tin đúng về nó.',
            [['Làng nghề', 'Đông Hồ – Bắc Ninh'], ['Dịp dùng', 'Tết Nguyên đán'], ['Kỹ thuật', 'In ván gỗ'], ['Màu sắc', 'Từ thiên nhiên']],
            'Tranh Đông Hồ: làng nghề ở Bắc Ninh, dùng dịp Tết, in ván gỗ, màu thiên nhiên.', $d);
        $this->matching($s, 'Nối mỗi màu với nguyên liệu tạo ra nó.',
            [['Đỏ', 'Sỏi son'], ['Vàng', 'Hoa hòe'], ['Xanh', 'Lá chàm'], ['Trắng', 'Bột điệp']],
            'Màu tranh Đông Hồ đều lấy từ nguyên liệu thiên nhiên.', $d);
        $this->sortQ($s, 'Xếp các nguyên liệu vào nhóm: TỰ NHIÊN / CÔNG NGHIỆP.',
            [['Vỏ sò điệp', 'TỰ NHIÊN'], ['Hoa hòe', 'TỰ NHIÊN'], ['Sỏi son', 'TỰ NHIÊN'], ['Màu acrylic', 'CÔNG NGHIỆP']],
            'Tranh Đông Hồ truyền thống dùng nguyên liệu tự nhiên làm màu và giấy.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Tranh Đông Hồ in bằng ván gỗ', 'ĐÚNG'], ['Màu tranh lấy từ thiên nhiên', 'ĐÚNG'], ['Giấy điệp làm từ nhựa', 'SAI'], ['Tranh Đông Hồ vẽ bằng máy', 'SAI']],
            'Tranh Đông Hồ in ván gỗ thủ công, giấy điệp từ vỏ sò, màu từ thiên nhiên.', $d);
        $this->sortQ($s, 'Xếp các bức tranh vào nhóm theo CHỦ ĐỀ.',
            [['Vinh hoa', 'Chúc tụng'], ['Đàn gà mẹ con', 'Gia đình'], ['Đám cưới chuột', 'Châm biếm'], ['Chăn trâu', 'Lao động']],
            'Tranh Đông Hồ có chủ đề chúc tụng, gia đình, châm biếm và lao động.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: CÔNG ĐOẠN LÀM TRANH / CÁCH DÙNG TRANH.',
            [['Khắc ván gỗ', 'CÔNG ĐOẠN LÀM TRANH'], ['Quét giấy điệp', 'CÔNG ĐOẠN LÀM TRANH'], ['Treo tranh ngày Tết', 'CÁCH DÙNG TRANH'], ['Biếu tặng tranh', 'CÁCH DÙNG TRANH']],
            'Khắc ván, quét điệp là công đoạn làm tranh; treo Tết, biếu tặng là cách dùng tranh.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào nhóm: RIÊNG CÓ / KHÔNG RIÊNG của tranh Đông Hồ.',
            [['Giấy điệp óng ánh', 'RIÊNG CÓ'], ['In ván gỗ nhiều màu', 'RIÊNG CÓ'], ['Đề tài Đám cưới chuột', 'RIÊNG CÓ'], ['Tô màu tay sau khi in', 'KHÔNG RIÊNG']],
            'Tô màu tay sau khi in là đặc trưng của tranh Hàng Trống.', $d);
        $this->fill($s, 'Làng tranh Đông Hồ nổi tiếng với nghề in tranh bằng ván ___.', [[0, 'gỗ']],
            'Tranh Đông Hồ được in bằng ván gỗ khắc, mỗi màu một ván.', $d);
        $this->fill($s, 'Giấy điệp được quét từ vỏ sò điệp giã nhỏ trộn với ___ nếp.', [[0, 'hồ']],
            'Vỏ sò điệp giã nhỏ trộn với hồ nếp tạo nên lớp quét óng ánh của giấy điệp.', $d);
        $this->fill($s, 'Mỗi màu trong tranh Đông Hồ được in bằng một tấm ván ___ riêng.', [[0, 'khắc']],
            'Mỗi màu sắc trong tranh Đông Hồ được in bằng một tấm ván khắc riêng.', $d);
        $this->fill($s, 'Tranh Đông Hồ thường được mua về treo trong dịp ___ Nguyên đán.', [[0, 'Tết']],
            'Tranh Đông Hồ là món quà Tết truyền thống của người dân.', $d);
        $this->fill($s, 'Màu xanh trong tranh Đông Hồ được chiết từ lá ___.', [[0, 'chàm']],
            'Lá chàm cho màu xanh tự nhiên, bền màu trong tranh Đông Hồ.', $d);
    }

    // ===== 16. Tranh Hàng Trống và Kim Hoàng lớp 8: nét riêng (2) (trung bình) =====
    private function seedMtTranhDanGianLop82(): void
    {
        $s = 'mt-tranh-dan-gian-lop-8-2'; $d = 'trung_binh';
        $this->quiz($s, 'Tranh Hàng Trống có nguồn gốc từ đâu?',
            ['Phố Hàng Trống, Hà Nội', 'Làng Đông Hồ, Bắc Ninh', 'Cố đô Huế', 'Nam Định'], 0,
            'Tranh Hàng Trống có nguồn gốc từ phố Hàng Trống, Hà Nội.', $d);
        $this->quiz($s, 'Điểm khác biệt lớn nhất của tranh Hàng Trống so với tranh Đông Hồ là gì?',
            ['In nét rồi tô màu bằng tay', 'In hoàn toàn bằng máy', 'Chỉ dùng màu đen', 'Không dùng màu'], 0,
            'Tranh Hàng Trống in nét đen rồi tô màu bằng tay, còn Đông Hồ in chồng nhiều ván màu.', $d);
        $this->quiz($s, 'Tranh Kim Hoàng có nguồn gốc từ đâu?',
            ['Làng Kim Hoàng, Hoài Đức, Hà Nội', 'Bắc Ninh', 'Huế', 'Sài Gòn'], 0,
            'Tranh Kim Hoàng có nguồn gốc từ làng Kim Hoàng, huyện Hoài Đức, Hà Nội.', $d);
        $this->quiz($s, 'Bộ tranh “Tố nữ” thể hiện hình ảnh ai?',
            ['Bốn thiếu nữ với dáng vẻ khác nhau', 'Bốn vị tướng', 'Bốn con vật', 'Bốn vị thần'], 0,
            'Bộ tranh “Tố nữ” vẽ bốn thiếu nữ với dáng vẻ, trang phục khác nhau.', $d);
        $this->quiz($s, 'Tranh Hàng Trống thường được dùng vào dịp nào?',
            ['Tết và các dịp lễ', 'Chỉ trong đám cưới', 'Chỉ trong đám tang', 'Không bao giờ dùng'], 0,
            'Tranh Hàng Trống với tranh thờ và tranh chơi được dùng trong dịp Tết và các lễ.', $d);
        $this->matching($s, 'Nối mỗi dòng tranh với kỹ thuật đặc trưng của nó.',
            [['Đông Hồ', 'In ván nhiều màu'], ['Hàng Trống', 'In nét + tô tay'], ['Kim Hoàng', 'In trên giấy hồng điều'], ['Làng Sình', 'In mộc bản']],
            'Mỗi dòng tranh dân gian có kỹ thuật riêng tạo nên nét độc đáo.', $d);
        $this->matching($s, 'Nối mỗi tác phẩm với dòng tranh của nó.',
            [['Tố nữ', 'Hàng Trống'], ['Đám cưới chuột', 'Đông Hồ'], ['Lý ngư vọng nguyệt', 'Làng Sình'], ['Vinh hoa', 'Đông Hồ']],
            'Mỗi tác phẩm tiêu biểu gắn với một dòng tranh dân gian riêng.', $d);
        $this->matching($s, 'Nối mỗi loại tranh với dòng tranh nổi tiếng về nó.',
            [['Tranh thờ', 'Hàng Trống'], ['Tranh Tết', 'Đông Hồ'], ['Tranh chúc tụng', 'Đông Hồ'], ['Tranh tố nữ', 'Hàng Trống']],
            'Hàng Trống nổi tiếng với tranh thờ, Đông Hồ nổi tiếng với tranh chơi ngày Tết.', $d);
        $this->matching($s, 'Nối mỗi làng nghề với địa phương của nó.',
            [['Đông Hồ', 'Bắc Ninh'], ['Hàng Trống', 'Hà Nội'], ['Kim Hoàng', 'Hoài Đức'], ['Làng Sình', 'Huế']],
            'Các làng nghề tranh dân gian phân bố ở nhiều địa phương khác nhau.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với dòng tranh của nó.',
            [['Giấy điệp', 'Đông Hồ'], ['Tô màu tay', 'Hàng Trống'], ['Giấy hồng điều', 'Kim Hoàng'], ['Tranh thờ Phật', 'Làng Sình']],
            'Đặc điểm chất liệu và kỹ thuật giúp phân biệt các dòng tranh dân gian.', $d);
        $this->sortQ($s, 'Xếp các tác phẩm vào nhóm theo DÒNG TRANH.',
            [['Đám cưới chuột', 'Đông Hồ'], ['Vinh hoa', 'Đông Hồ'], ['Tố nữ', 'Hàng Trống'], ['Lý ngư vọng nguyệt', 'Làng Sình']],
            'Mỗi tác phẩm thuộc về một dòng tranh dân gian nhất định.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Hàng Trống tô màu tay sau khi in', 'ĐÚNG'], ['Đông Hồ dùng giấy điệp', 'ĐÚNG'], ['Kim Hoàng in trên giấy trắng', 'SAI'], ['Tố nữ là tranh Đông Hồ', 'SAI']],
            'Kim Hoàng in trên giấy hồng điều; “Tố nữ” là tranh Hàng Trống.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào nhóm DÒNG TRANH tương ứng.',
            [['In ván nhiều màu', 'Đông Hồ'], ['Tô màu tay', 'Hàng Trống'], ['Giấy hồng điều', 'Kim Hoàng'], ['Vẽ trên kính', 'Tranh kính']],
            'Mỗi dòng tranh có đặc điểm kỹ thuật và chất liệu riêng.', $d);
        $this->sortQ($s, 'Xếp các làng nghề vào nhóm theo MIỀN.',
            [['Đông Hồ', 'Miền Bắc'], ['Hàng Trống', 'Miền Bắc'], ['Kim Hoàng', 'Miền Bắc'], ['Làng Sình', 'Miền Trung']],
            'Đông Hồ, Hàng Trống, Kim Hoàng ở miền Bắc; Làng Sình ở miền Trung.', $d);
        $this->sortQ($s, 'Xếp các công đoạn vào nhóm: IN ẤN / TÔ MÀU.',
            [['Khắc ván gỗ', 'IN ẤN'], ['In nét đen', 'IN ẤN'], ['Tô màu bằng tay', 'TÔ MÀU'], ['Pha màu nước', 'TÔ MÀU']],
            'Khắc ván, in nét thuộc in ấn; tô tay, pha màu thuộc công đoạn tô màu.', $d);
        $this->fill($s, 'Tranh Hàng Trống có nguồn gốc từ phố Hàng Trống, thành phố Hà ___.', [[0, 'Nội']],
            'Tranh Hàng Trống ra đời tại phố Hàng Trống, Hà Nội.', $d);
        $this->fill($s, 'Sau khi in nét đen, tranh Hàng Trống được ___ màu bằng tay.', [[0, 'tô']],
            'Tranh Hàng Trống in nét đen rồi tô màu bằng tay rất công phu.', $d);
        $this->fill($s, 'Tranh Kim Hoàng thường in trên giấy màu hồng ___.', [[0, 'điều']],
            'Giấy hồng điều là chất liệu đặc trưng của tranh Kim Hoàng.', $d);
        $this->fill($s, 'Bộ tranh “Tố nữ” gồm bốn thiếu nữ là tác phẩm tiêu biểu của dòng tranh Hàng ___.', [[0, 'Trống']],
            'Bộ tranh “Tố nữ” là tác phẩm tiêu biểu của dòng tranh Hàng Trống.', $d);
        $this->fill($s, 'Tranh ___ Trống nổi tiếng với dòng tranh thờ và tranh chơi ngày Tết.', [[0, 'Hàng']],
            'Tranh Hàng Trống nổi tiếng với tranh thờ và tranh chơi ngày Tết.', $d);
    }

    // ===== 17. Tranh dân gian các vùng miền lớp 9: Làng Sình, tranh kính (1) (trung bình) =====
    private function seedMtTranhDanGianLop91(): void
    {
        $s = 'mt-tranh-dan-gian-lop-9-1'; $d = 'trung_binh';
        $this->quiz($s, 'Tranh Làng Sình được in bằng kỹ thuật nào?',
            ['In mộc bản (ván khắc gỗ)', 'Vẽ tay hoàn toàn', 'In offset hiện đại', 'Thêu tay'], 0,
            'Tranh Làng Sình được in bằng kỹ thuật mộc bản, tức in ván khắc gỗ.', $d);
        $this->quiz($s, 'Tranh kính Nam Bộ có đặc điểm gì khác biệt?',
            ['Vẽ trực tiếp lên mặt sau của tấm kính', 'Vẽ trên giấy dó', 'Khắc trên gỗ', 'In trên vải'], 0,
            'Tranh kính Nam Bộ được vẽ trực tiếp lên mặt sau của tấm kính, xem từ mặt trước.', $d);
        $this->quiz($s, 'Bức “Lý ngư vọng nguyệt” thuộc dòng tranh nào?',
            ['Tranh Làng Sình', 'Tranh Đông Hồ', 'Tranh Hàng Trống', 'Tranh sơn mài'], 0,
            'Bức “Lý ngư vọng nguyệt” (cá chép trông trăng) là tác phẩm của tranh Làng Sình.', $d);
        $this->quiz($s, 'Tranh Làng Sình chủ yếu phục vụ nhu cầu nào?',
            ['Thờ cúng tâm linh', 'Trang trí quán cà phê', 'Quảng cáo sản phẩm', 'Minh họa sách giáo khoa'], 0,
            'Tranh Làng Sình chủ yếu dùng vào việc thờ cúng tâm linh.', $d);
        $this->quiz($s, 'Điểm chung của các dòng tranh dân gian Việt Nam là gì?',
            ['Đề tài gần gũi, kỹ thuật thủ công', 'Chỉ dùng màu đen trắng', 'Vẽ bằng máy tính', 'Kích thước khổng lồ'], 0,
            'Các dòng tranh dân gian đều có đề tài gần gũi và làm bằng kỹ thuật thủ công.', $d);
        $this->matching($s, 'Nối mỗi dòng tranh với vùng miền và đặc điểm của nó.',
            [['Làng Sình', 'Huế – tranh thờ'], ['Tranh kính', 'Nam Bộ – vẽ trên kính'], ['Đông Hồ', 'Bắc Ninh – giấy điệp'], ['Hàng Trống', 'Hà Nội – tô tay']],
            'Mỗi vùng miền có dòng tranh dân gian với đặc điểm riêng.', $d);
        $this->matching($s, 'Nối mỗi chất liệu với dòng tranh dùng nó.',
            [['Giấy dó', 'Làng Sình'], ['Tấm kính', 'Tranh kính'], ['Giấy điệp', 'Đông Hồ'], ['Giấy hồng điều', 'Kim Hoàng']],
            'Chất liệu đặc trưng giúp nhận biết các dòng tranh dân gian.', $d);
        $this->matching($s, 'Nối mỗi tác phẩm với dòng tranh của nó.',
            [['Lý ngư vọng nguyệt', 'Làng Sình'], ['Tố nữ', 'Hàng Trống'], ['Đám cưới chuột', 'Đông Hồ'], ['Vinh hoa', 'Đông Hồ']],
            'Mỗi tác phẩm tiêu biểu gắn với một dòng tranh dân gian.', $d);
        $this->matching($s, 'Nối mỗi loại tranh với mục đích sử dụng của nó.',
            [['Tranh thờ', 'Cúng bái'], ['Tranh Tết', 'Trang trí'], ['Tranh chúc tụng', 'Biếu tặng'], ['Tranh phong cảnh', 'Thưởng ngoạn']],
            'Tranh dân gian phục vụ nhiều mục đích: thờ cúng, trang trí, biếu tặng.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với dòng tranh của nó.',
            [['Vẽ trên kính', 'Tranh kính Nam Bộ'], ['In mộc bản', 'Làng Sình'], ['Màu sắc rực rỡ', 'Tranh kính'], ['Nét vẽ mộc mạc', 'Làng Sình']],
            'Tranh kính rực rỡ vẽ trên kính, Làng Sình in mộc bản với nét mộc mạc.', $d);
        $this->sortQ($s, 'Xếp các dòng tranh vào nhóm theo MIỀN.',
            [['Đông Hồ', 'Miền Bắc'], ['Hàng Trống', 'Miền Bắc'], ['Làng Sình', 'Miền Trung'], ['Tranh kính', 'Miền Nam']],
            'Tranh dân gian có mặt ở cả ba miền Bắc, Trung, Nam.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Làng Sình ở Huế', 'ĐÚNG'], ['Lý ngư vọng nguyệt là tranh Làng Sình', 'ĐÚNG'], ['Tranh kính vẽ trên giấy', 'SAI'], ['Tranh dân gian chỉ có ở miền Bắc', 'SAI']],
            'Tranh kính vẽ trên kính; tranh dân gian có ở cả ba miền đất nước.', $d);
        $this->sortQ($s, 'Xếp các chất liệu vào nhóm: GIẤY / KHÔNG PHẢI GIẤY.',
            [['Giấy dó', 'GIẤY'], ['Giấy điệp', 'GIẤY'], ['Tấm kính', 'KHÔNG PHẢI GIẤY'], ['Ván gỗ', 'KHÔNG PHẢI GIẤY']],
            'Tranh kính dùng tấm kính, ván gỗ dùng để khắc bản in.', $d);
        $this->sortQ($s, 'Xếp các loại tranh vào nhóm: MỤC ĐÍCH TÂM LINH / TRANG TRÍ.',
            [['Tranh thờ', 'MỤC ĐÍCH TÂM LINH'], ['Tranh cúng', 'MỤC ĐÍCH TÂM LINH'], ['Tranh Tết', 'TRANG TRÍ'], ['Tranh phong cảnh', 'TRANG TRÍ']],
            'Tranh thờ, tranh cúng phục vụ tâm linh; tranh Tết, phong cảnh để trang trí.', $d);
        $this->sortQ($s, 'Xếp các bức tranh vào nhóm: TRANH LÀNG SÌNH / TRANH KHÁC.',
            [['Lý ngư vọng nguyệt', 'TRANH LÀNG SÌNH'], ['Ngũ hổ', 'TRANH LÀNG SÌNH'], ['Tố nữ', 'TRANH KHÁC'], ['Đám cưới chuột', 'TRANH KHÁC']],
            '“Lý ngư vọng nguyệt” và “Ngũ hổ” là tranh Làng Sình ở Huế.', $d);
        $this->fill($s, 'Tranh Làng Sình có nguồn gốc từ làng Sình, thành phố ___.', [[0, 'Huế']],
            'Làng Sình thuộc thành phố Huế, nổi tiếng với dòng tranh thờ in mộc bản.', $d);
        $this->fill($s, 'Tranh Làng Sình được in bằng kỹ thuật mộc ___.', [[0, 'bản']],
            'Mộc bản là kỹ thuật in bằng ván khắc gỗ của tranh Làng Sình.', $d);
        $this->fill($s, 'Tranh kính Nam Bộ được vẽ trực tiếp lên mặt ___ của tấm kính.', [[0, 'sau']],
            'Tranh kính được vẽ lên mặt sau của tấm kính và xem từ mặt trước.', $d);
        $this->fill($s, '“Lý ngư vọng nguyệt” nghĩa là cá chép trông ___.', [[0, 'trăng']],
            '“Lý ngư vọng nguyệt” là bức tranh cá chép trông trăng của Làng Sình.', $d);
        $this->fill($s, 'Mỗi vùng miền đều có dòng tranh dân gian mang ___ sắc riêng.', [[0, 'bản']],
            'Tranh dân gian mỗi vùng miền mang bản sắc văn hóa riêng.', $d);
    }

    // ===== 18. Bảo tồn tranh dân gian lớp 9: nghệ nhân và phát huy (2) (khó) =====
    private function seedMtTranhDanGianLop92(): void
    {
        $s = 'mt-tranh-dan-gian-lop-9-2'; $d = 'kho';
        $this->quiz($s, 'Vì sao nhiều làng nghề tranh dân gian bị mai một?',
            ['Thị hiếu thay đổi, thiếu người kế thừa', 'Tranh quá đắt đỏ', 'Bị cấm sản xuất', 'Hết sạch nguyên liệu'], 0,
            'Thị hiếu thay đổi và thiếu người kế thừa là nguyên nhân chính khiến làng nghề mai một.', $d);
        $this->quiz($s, 'Danh hiệu nghệ nhân ưu tú dành cho ai?',
            ['Người thợ giỏi có đóng góp xuất sắc', 'Người giàu có', 'Người nổi tiếng trên mạng', 'Người nước ngoài'], 0,
            'Nghệ nhân ưu tú là danh hiệu tôn vinh người thợ giỏi có đóng góp xuất sắc cho nghề.', $d);
        $this->quiz($s, 'Việc nào KHÔNG thuộc bảo tồn tranh dân gian?',
            ['Ngừng dạy nghề cho thế hệ trẻ', 'Số hóa tác phẩm', 'Mở lớp truyền nghề', 'Đưa vào trường học'], 0,
            'Ngừng dạy nghề cho thế hệ trẻ sẽ khiến nghề mai một, đi ngược với bảo tồn.', $d);
        $this->quiz($s, 'Ứng dụng tranh dân gian vào thiết kế hiện đại mang lại lợi ích gì?',
            ['Lan tỏa văn hóa, tạo sản phẩm mới', 'Làm mất giá trị gốc', 'Không có tác dụng gì', 'Chỉ để trang trí'], 0,
            'Ứng dụng vào thiết kế hiện đại giúp lan tỏa văn hóa và tạo ra sản phẩm mới.', $d);
        $this->quiz($s, 'Vai trò của bảo tàng trong bảo tồn tranh dân gian là gì?',
            ['Lưu giữ, trưng bày, giáo dục', 'Bán tranh giá cao', 'Cất giấu không cho xem', 'Sản xuất tranh mới'], 0,
            'Bảo tàng lưu giữ, trưng bày tranh dân gian và giáo dục công chúng về di sản.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.',
            [['Nghệ nhân', 'Thợ giỏi giữ nghề'], ['Di sản', 'Giá trị truyền đời'], ['Mai một', 'Dần biến mất'], ['Phục hưng', 'Hồi sinh trở lại']],
            'Hiểu đúng các khái niệm giúp em nhận thức rõ về bảo tồn di sản.', $d);
        $this->matching($s, 'Nối mỗi giải pháp với lợi ích của nó.',
            [['Mở lớp dạy nghề', 'Có người kế thừa'], ['Số hóa tác phẩm', 'Lưu trữ lâu dài'], ['Lễ hội làng nghề', 'Quảng bá rộng rãi'], ['Hỗ trợ nghệ nhân', 'Động viên giữ nghề']],
            'Mỗi giải pháp bảo tồn đều mang lại lợi ích thiết thực cho làng nghề.', $d);
        $this->matching($s, 'Nối mỗi nguy cơ với biểu hiện của nó.',
            [['Thiếu người học nghề', 'Nghệ nhân ngày càng già'], ['Tranh giả tràn lan', 'Mất uy tín'], ['Thị hiếu đổi thay', 'Ít người mua'], ['Nguyên liệu khan hiếm', 'Khó sản xuất']],
            'Nhận diện nguy cơ giúp tìm giải pháp bảo tồn kịp thời.', $d);
        $this->matching($s, 'Nối mỗi ứng dụng với lĩnh vực của nó.',
            [['Họa tiết Đông Hồ', 'Thời trang'], ['Tranh dân gian', 'Du lịch'], ['Mẫu tranh', 'Giáo dục'], ['Tranh số', 'Truyền thông']],
            'Tranh dân gian được ứng dụng vào thời trang, du lịch, giáo dục và truyền thông.', $d);
        $this->matching($s, 'Nối mỗi đối tượng với vai trò của họ trong bảo tồn.',
            [['Nghệ nhân', 'Truyền nghề'], ['Nhà trường', 'Giáo dục'], ['Bảo tàng', 'Lưu giữ'], ['Doanh nghiệp', 'Hỗ trợ']],
            'Bảo tồn di sản cần sự chung tay của nghệ nhân, nhà trường, bảo tàng và doanh nghiệp.', $d);
        $this->sortQ($s, 'Xếp các việc làm vào nhóm: BẢO TỒN / KHÔNG BẢO TỒN.',
            [['Mở lớp dạy nghề', 'BẢO TỒN'], ['Số hóa tranh cổ', 'BẢO TỒN'], ['Bỏ nghề đi làm việc khác', 'KHÔNG BẢO TỒN'], ['Phá hủy ván khắc cổ', 'KHÔNG BẢO TỒN']],
            'Dạy nghề và số hóa là bảo tồn; bỏ nghề và phá hủy là làm mất di sản.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Nghệ nhân là người giữ nghề', 'ĐÚNG'], ['Giới trẻ có thể góp phần bảo tồn', 'ĐÚNG'], ['Bảo tồn chỉ là việc của nhà nước', 'SAI'], ['Tranh dân gian đã lỗi thời hoàn toàn', 'SAI']],
            'Bảo tồn là trách nhiệm chung; tranh dân gian vẫn có sức sống trong đời sống hiện đại.', $d);
        $this->sortQ($s, 'Xếp các giải pháp vào nhóm: GIẢI PHÁP CON NGƯỜI / GIẢI PHÁP CÔNG NGHỆ.',
            [['Đào tạo thợ trẻ', 'GIẢI PHÁP CON NGƯỜI'], ['Tôn vinh nghệ nhân', 'GIẢI PHÁP CON NGƯỜI'], ['Số hóa 3D', 'GIẢI PHÁP CÔNG NGHỆ'], ['Website trưng bày', 'GIẢI PHÁP CÔNG NGHỆ']],
            'Bảo tồn cần cả giải pháp con người và giải pháp công nghệ.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: NGUY CƠ / CƠ HỘI.',
            [['Thiếu người kế thừa', 'NGUY CƠ'], ['Tranh giả tràn lan', 'NGUY CƠ'], ['Du lịch văn hóa', 'CƠ HỘI'], ['Thiết kế ứng dụng', 'CƠ HỘI']],
            'Thiếu người kế thừa và tranh giả là nguy cơ; du lịch và thiết kế là cơ hội.', $d);
        $this->sortQ($s, 'Xếp các hành động vào nhóm: NÊN LÀM / KHÔNG NÊN của học sinh.',
            [['Tìm hiểu tranh dân gian', 'NÊN LÀM'], ['Tham quan làng nghề', 'NÊN LÀM'], ['Xé tranh cổ', 'KHÔNG NÊN'], ['Chê bai nghề truyền thống', 'KHÔNG NÊN']],
            'Học sinh có thể góp phần bảo tồn bằng cách tìm hiểu và trân trọng di sản.', $d);
        $this->fill($s, 'Người thợ giỏi, am hiểu sâu nghề truyền thống được tôn vinh là nghệ ___.', [[0, 'nhân']],
            'Nghệ nhân là người thợ giỏi, am hiểu sâu và giữ gìn nghề truyền thống.', $d);
        $this->fill($s, 'Nhiều làng nghề đang đứng trước nguy cơ ___ một nếu không được bảo tồn.', [[0, 'mai']],
            'Không được bảo tồn, nhiều làng nghề tranh có nguy cơ mai một.', $d);
        $this->fill($s, 'Việc ___ hóa giúp tranh dân gian được lưu trữ và lan tỏa trên mạng.', [[0, 'số']],
            'Số hóa giúp tranh dân gian lưu trữ lâu dài và lan tỏa rộng rãi.', $d);
        $this->fill($s, 'Đưa nghề làm tranh vào nhà ___ giúp học sinh tiếp cận di sản.', [[0, 'trường']],
            'Đưa nghề làm tranh vào nhà trường giúp thế hệ trẻ tiếp cận di sản văn hóa.', $d);
        $this->fill($s, 'Mỗi chúng ta đều có thể góp phần ___ tồn di sản văn hóa dân tộc.', [[0, 'bảo']],
            'Bảo tồn di sản văn hóa là trách nhiệm chung của mỗi người.', $d);
    }

    // ===== 19. Mỹ thuật Trung Hoa và Nhật Bản (lớp 10, trung bình) =====
    private function seedMtThpt10Lop101(): void
    {
        $s = 'my-thuat-thpt-10-lop-10-1'; $d = 'trung_binh';
        $this->quiz($s, 'Thư pháp trong mỹ thuật Trung Hoa được coi là gì?',
            ['Nghệ thuật cao quý ngang với hội họa', 'Chữ viết thông thường', 'Nghề khắc chữ', 'Trò chơi giải trí'], 0,
            'Thư pháp được coi là nghệ thuật cao quý, ngang hàng với hội họa ở Trung Hoa.', $d);
        $this->quiz($s, 'Tranh khắc gỗ ukiyo-e thường diễn tả đề tài gì?',
            ['Đời sống thường nhật, phong cảnh, mỹ nhân', 'Chiến tranh', 'Tôn giáo', 'Chính trị'], 0,
            'Ukiyo-e diễn tả đời sống thường nhật, phong cảnh và mỹ nhân Nhật Bản.', $d);
        $this->quiz($s, 'Mực tàu trong tranh thủy mặc được làm từ nguyên liệu nào?',
            ['Than muội và keo', 'Nước biển', 'Nhựa cây', 'Đất sét'], 0,
            'Mực tàu được làm từ than muội trộn với keo, cho màu đen sâu bền.', $d);
        $this->quiz($s, 'Điểm chung của mỹ thuật Trung Hoa và Nhật Bản là gì?',
            ['Chịu ảnh hưởng Phật giáo, coi trọng thiên nhiên', 'Chỉ vẽ chân dung', 'Dùng sơn dầu', 'Không có điểm chung'], 0,
            'Cả hai nền mỹ thuật đều chịu ảnh hưởng Phật giáo và coi trọng thiên nhiên.', $d);
        $this->quiz($s, 'Tranh khắc gỗ ukiyo-e được in như thế nào?',
            ['In nhiều ván gỗ chồng màu', 'Vẽ tay từng bức một', 'In bằng máy laser', 'Khắc trên đá'], 0,
            'Ukiyo-e được in bằng nhiều ván gỗ khắc, mỗi ván một màu chồng lên nhau.', $d);
        $this->matching($s, 'Nối mỗi dòng tranh với đặc điểm của nó.',
            [['Thủy mặc', 'Mực đen – khoảng trống'], ['Ukiyo-e', 'Khắc gỗ – màu phẳng'], ['Thư pháp', 'Nét chữ nghệ thuật'], ['Tranh cuộn', 'Treo dọc']],
            'Thủy mặc dùng mực đen với khoảng trống, ukiyo-e khắc gỗ với màu phẳng.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.',
            [['Thủy mặc', 'Tranh mực nước'], ['Ukiyo-e', 'Tranh thế giới phù du'], ['Thư pháp', 'Nghệ thuật viết chữ'], ['Mộc bản', 'In ván gỗ']],
            'Hiểu khái niệm giúp phân biệt các dòng tranh phương Đông.', $d);
        $this->matching($s, 'Nối mỗi quốc gia với dòng tranh tiêu biểu của nó.',
            [['Trung Hoa', 'Thủy mặc'], ['Nhật Bản', 'Ukiyo-e'], ['Trung Hoa', 'Thư pháp'], ['Nhật Bản', 'Tranh khắc gỗ']],
            'Thủy mặc và thư pháp tiêu biểu cho Trung Hoa, ukiyo-e cho Nhật Bản.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với dòng tranh tương ứng.',
            [['Khoảng trống', 'Thủy mặc'], ['Màu phẳng', 'Ukiyo-e'], ['Sơn thủy', 'Thủy mặc'], ['Mỹ nhân', 'Ukiyo-e']],
            'Khoảng trống và sơn thủy thuộc thủy mặc; màu phẳng và mỹ nhân thuộc ukiyo-e.', $d);
        $this->matching($s, 'Nối mỗi chất liệu với dòng tranh dùng nó.',
            [['Mực tàu', 'Thủy mặc'], ['Ván gỗ', 'Ukiyo-e'], ['Giấy xuyến', 'Thư pháp'], ['Lụa', 'Thủy mặc']],
            'Mực tàu, giấy xuyến, lụa dùng cho thủy mặc; ván gỗ dùng cho ukiyo-e.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào nhóm: TRANH THỦY MẶC / UKIYO-E.',
            [['Mực đen', 'TRANH THỦY MẶC'], ['Khoảng trống', 'TRANH THỦY MẶC'], ['Khắc gỗ', 'UKIYO-E'], ['Màu phẳng rực rỡ', 'UKIYO-E']],
            'Thủy mặc: mực đen, khoảng trống; ukiyo-e: khắc gỗ, màu phẳng rực rỡ.', $d);
        $this->sortQ($s, 'Xếp các chất liệu vào nhóm: PHƯƠNG ĐÔNG / PHƯƠNG TÂY.',
            [['Mực tàu', 'PHƯƠNG ĐÔNG'], ['Giấy xuyến', 'PHƯƠNG ĐÔNG'], ['Sơn dầu', 'PHƯƠNG TÂY'], ['Vải toan', 'PHƯƠNG TÂY']],
            'Mực tàu, giấy xuyến là chất liệu phương Đông; sơn dầu, vải toan của phương Tây.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Thủy mặc dùng mực đen', 'ĐÚNG'], ['Ukiyo-e là tranh khắc gỗ Nhật Bản', 'ĐÚNG'], ['Thư pháp không phải là nghệ thuật', 'SAI'], ['Ukiyo-e vẽ bằng sơn dầu', 'SAI']],
            'Thư pháp là nghệ thuật cao quý; ukiyo-e in bằng ván gỗ khắc.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: THUỘC VỀ CHẤT LIỆU / ĐỀ TÀI.',
            [['Mực tàu', 'THUỘC VỀ CHẤT LIỆU'], ['Ván gỗ', 'THUỘC VỀ CHẤT LIỆU'], ['Sơn thủy', 'ĐỀ TÀI'], ['Mỹ nhân', 'ĐỀ TÀI']],
            'Mực tàu, ván gỗ là chất liệu; sơn thủy, mỹ nhân là đề tài.', $d);
        $this->sortQ($s, 'Xếp các đề tài vào nhóm: TRUNG HOA / NHẬT BẢN.',
            [['Sơn thủy', 'TRUNG HOA'], ['Hoa điểu', 'TRUNG HOA'], ['Mỹ nhân', 'NHẬT BẢN'], ['Núi Phú Sĩ', 'NHẬT BẢN']],
            'Sơn thủy, hoa điểu tiêu biểu Trung Hoa; mỹ nhân, núi Phú Sĩ tiêu biểu Nhật Bản.', $d);
        $this->fill($s, 'Tranh thủy mặc Trung Hoa đề cao sự hòa hợp giữa con người và ___ nhiên.', [[0, 'thiên']],
            'Thủy mặc đề cao sự hòa hợp giữa con người và thiên nhiên.', $d);
        $this->fill($s, 'Ukiyo-e nghĩa là tranh về “thế giới ___ du”.', [[0, 'phù']],
            'Ukiyo-e nghĩa là tranh về thế giới phù du, đời sống thường nhật.', $d);
        $this->fill($s, 'Nghệ thuật viết chữ đẹp trong văn hóa Trung Hoa gọi là thư ___.', [[0, 'pháp']],
            'Thư pháp là nghệ thuật viết chữ đẹp, rất được coi trọng ở Trung Hoa.', $d);
        $this->fill($s, 'Tranh thủy mặc thường được vẽ trên giấy xuyến hoặc ___.', [[0, 'lụa']],
            'Giấy xuyến và lụa là hai chất liệu chính của tranh thủy mặc.', $d);
        $this->fill($s, 'Điểm đặc sắc của thủy mặc là biết dùng khoảng ___ làm nên hồn tranh.', [[0, 'trống']],
            'Khoảng trống trong thủy mặc không phải chỗ thừa mà làm nên hồn tranh.', $d);
    }

    // ===== 20. Mỹ thuật truyền thống Việt Nam (lớp 10, trung bình) =====
    private function seedMtThpt10Lop102(): void
    {
        $s = 'my-thuat-thpt-10-lop-10-2'; $d = 'trung_binh';
        $this->quiz($s, 'Sơn mài là chất liệu hội họa đặc sắc của nước nào?',
            ['Việt Nam', 'Pháp', 'Mỹ', 'Ấn Độ'], 0,
            'Sơn mài là chất liệu hội họa đặc sắc, niềm tự hào của Việt Nam.', $d);
        $this->quiz($s, 'Tranh sơn mài được làm từ nguyên liệu chính nào?',
            ['Nhựa cây sơn', 'Sơn công nghiệp', 'Màu nước', 'Dầu ăn'], 0,
            'Tranh sơn mài làm từ nhựa cây sơn, qua nhiều công đoạn sơn và mài.', $d);
        $this->quiz($s, 'Đặc điểm nổi bật của tranh lụa Việt Nam là gì?',
            ['Mềm mại, trong trẻo, màu nhạt', 'Màu sắc chói lọi', 'Nét vẽ thô cứng', 'Kích thước khổng lồ'], 0,
            'Tranh lụa Việt Nam nổi tiếng với vẻ mềm mại, trong trẻo và màu nhạt.', $d);
        $this->quiz($s, 'Tranh Đông Hồ và Hàng Trống đều thuộc loại hình nào?',
            ['Tranh khắc gỗ dân gian', 'Tranh sơn dầu', 'Tranh lụa', 'Tranh tường'], 0,
            'Đông Hồ và Hàng Trống đều là tranh khắc gỗ dân gian của Việt Nam.', $d);
        $this->quiz($s, 'Chùa Một Cột là công trình kiến trúc tiêu biểu của thời nào?',
            ['Thời Lý', 'Thời Trần', 'Thời Lê', 'Thời Nguyễn'], 0,
            'Chùa Một Cột được xây dựng thời Lý, là biểu tượng kiến trúc Việt Nam.', $d);
        $this->matching($s, 'Nối mỗi dòng tranh với đặc điểm của nó.',
            [['Sơn mài', 'Bóng sâu – nhiều lớp'], ['Tranh lụa', 'Mềm mại – trong trẻo'], ['Đông Hồ', 'Giấy điệp – in ván'], ['Sơn dầu', 'Màu dày – bền']],
            'Mỗi dòng tranh Việt Nam có đặc điểm chất liệu và kỹ thuật riêng.', $d);
        $this->matching($s, 'Nối mỗi chất liệu với mô tả của nó.',
            [['Nhựa sơn', 'Từ cây sơn'], ['Lụa', 'Vải mịn'], ['Giấy điệp', 'Óng ánh'], ['Ván gỗ', 'Khắc in']],
            'Chất liệu quyết định vẻ đẹp đặc trưng của mỗi dòng tranh.', $d);
        $this->matching($s, 'Nối mỗi đề tài với dòng tranh thường thể hiện nó.',
            [['Lao động', 'Sơn mài'], ['Thiếu nữ', 'Tranh lụa'], ['Chúc tụng', 'Đông Hồ'], ['Sinh hoạt', 'Hàng Trống']],
            'Mỗi dòng tranh có những đề tài sở trường riêng.', $d);
        $this->matching($s, 'Nối mỗi địa danh với dòng tranh của nó.',
            [['Bắc Ninh', 'Đông Hồ'], ['Hà Nội', 'Hàng Trống'], ['Huế', 'Làng Sình'], ['Nam Bộ', 'Tranh kính']],
            'Các dòng tranh dân gian gắn với từng địa phương trên cả nước.', $d);
        $this->matching($s, 'Nối mỗi công trình với thời kỳ của nó.',
            [['Chùa Một Cột', 'Thời Lý'], ['Hoàng thành Thăng Long', 'Nhiều thời kỳ'], ['Cố đô Huế', 'Thời Nguyễn'], ['Tháp Chăm', 'Văn hóa Champa']],
            'Mỗi công trình kiến trúc là dấu ấn của một thời kỳ lịch sử.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào nhóm: TRANH ĐÔNG HỒ / HÀNG TRỐNG.',
            [['Giấy điệp', 'TRANH ĐÔNG HỒ'], ['In nhiều ván', 'TRANH ĐÔNG HỒ'], ['Tô màu tay', 'HÀNG TRỐNG'], ['Giấy thường', 'HÀNG TRỐNG']],
            'Đông Hồ dùng giấy điệp in nhiều ván; Hàng Trống in nét rồi tô tay.', $d);
        $this->sortQ($s, 'Xếp các chất liệu vào nhóm: TRUYỀN THỐNG / HIỆN ĐẠI.',
            [['Sơn mài', 'TRUYỀN THỐNG'], ['Lụa', 'TRUYỀN THỐNG'], ['Acrylic', 'HIỆN ĐẠI'], ['Sơn dầu', 'HIỆN ĐẠI']],
            'Sơn mài, lụa là chất liệu truyền thống; acrylic, sơn dầu hiện đại hơn.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Sơn mài là chất liệu của Việt Nam', 'ĐÚNG'], ['Tranh lụa có màu nhạt trong trẻo', 'ĐÚNG'], ['Đông Hồ in trên vải', 'SAI'], ['Chùa Một Cột xây thời Nguyễn', 'SAI']],
            'Đông Hồ in trên giấy điệp; chùa Một Cột xây dựng thời Lý.', $d);
        $this->sortQ($s, 'Xếp các công đoạn vào nhóm: IN ẤN / TÔ MÀU.',
            [['Khắc ván', 'IN ẤN'], ['In chồng màu', 'IN ẤN'], ['Tô tay Hàng Trống', 'TÔ MÀU'], ['Pha màu', 'TÔ MÀU']],
            'Khắc ván, in chồng màu thuộc in ấn; tô tay, pha màu thuộc tô màu.', $d);
        $this->sortQ($s, 'Xếp các loại hình vào nhóm: DÂN GIAN / BÁC HỌC.',
            [['Tranh Đông Hồ', 'DÂN GIAN'], ['Tranh Hàng Trống', 'DÂN GIAN'], ['Tranh sơn mài', 'BÁC HỌC'], ['Tranh lụa', 'BÁC HỌC']],
            'Tranh khắc gỗ dân gian phục vụ người dân; sơn mài, lụa thuộc hội họa bác học.', $d);
        $this->fill($s, 'Chất liệu sơn mài được làm từ nhựa cây ___, qua nhiều công đoạn mài.', [[0, 'sơn']],
            'Nhựa cây sơn là nguyên liệu chính tạo nên tranh sơn mài Việt Nam.', $d);
        $this->fill($s, 'Tranh lụa Việt Nam nổi tiếng với vẻ đẹp mềm mại, ___ trẻo.', [[0, 'trong']],
            'Vẻ trong trẻo, mềm mại là đặc trưng của tranh lụa Việt Nam.', $d);
        $this->fill($s, 'Hai dòng tranh khắc gỗ dân gian nổi tiếng là Đông Hồ và Hàng ___.', [[0, 'Trống']],
            'Đông Hồ và Hàng Trống là hai dòng tranh khắc gỗ dân gian nổi tiếng.', $d);
        $this->fill($s, 'Chùa Một Cột là biểu tượng kiến trúc thời ___.', [[0, 'Lý']],
            'Chùa Một Cột được xây dựng thời Lý, mang đậm dấu ấn kiến trúc thời đại.', $d);
        $this->fill($s, 'Tranh dân gian Việt Nam phản ánh đời sống ___ động của người dân.', [[0, 'lao']],
            'Tranh dân gian phản ánh chân thực đời sống lao động của người dân.', $d);
    }

    // ===== 21. Mỹ thuật Ai Cập, Hy Lạp và La Mã cổ đại (lớp 10, trung bình) =====
    private function seedMtThpt10Lop103(): void
    {
        $s = 'my-thuat-thpt-10-lop-10-3'; $d = 'trung_binh';
        $this->quiz($s, 'Kim tự tháp là công trình của nền văn minh nào?',
            ['Ai Cập cổ đại', 'Hy Lạp', 'La Mã', 'Trung Hoa'], 0,
            'Kim tự tháp là lăng mộ của các Pharaon Ai Cập cổ đại.', $d);
        $this->quiz($s, 'Tượng thần Vệ Nữ là tác phẩm tiêu biểu của nền mỹ thuật nào?',
            ['Hy Lạp cổ đại', 'Ai Cập', 'La Mã', 'Ba Tư'], 0,
            'Tượng thần Vệ Nữ tôn vinh vẻ đẹp cơ thể, tiêu biểu cho điêu khắc Hy Lạp.', $d);
        $this->quiz($s, 'Người Ai Cập cổ đại thường vẽ mắt người như thế nào?',
            ['Vẽ chính diện dù khuôn mặt nghiêng', 'Vẽ nhắm mắt', 'Không vẽ mắt', 'Vẽ mắt nhắm nghiền'], 0,
            'Theo luật frontal, người Ai Cập vẽ mắt chính diện dù khuôn mặt nghiêng.', $d);
        $this->quiz($s, 'Đấu trường Cô-lô-xê là công trình của nền văn minh nào?',
            ['La Mã', 'Hy Lạp', 'Ai Cập', 'Lưỡng Hà'], 0,
            'Đấu trường Cô-lô-xê là công trình kiến trúc vĩ đại của La Mã cổ đại.', $d);
        $this->quiz($s, 'Điểm chung của mỹ thuật ba nền văn minh cổ đại là gì?',
            ['Gắn với tôn giáo và quyền lực', 'Chỉ vẽ phong cảnh', 'Dùng màu neon', 'Không có điểm chung'], 0,
            'Mỹ thuật Ai Cập, Hy Lạp, La Mã đều gắn chặt với tôn giáo và quyền lực.', $d);
        $this->matching($s, 'Nối mỗi nền mỹ thuật với đặc điểm của nó.',
            [['Ai Cập', 'Luật frontal'], ['Hy Lạp', 'Vẻ đẹp cơ thể'], ['La Mã', 'Chân dung – kiến trúc'], ['Ai Cập', 'Lăng mộ kim tự tháp']],
            'Ai Cập có luật frontal, Hy Lạp tôn vinh cơ thể, La Mã mạnh về chân dung.', $d);
        $this->matching($s, 'Nối mỗi thành tựu với nền văn minh tương ứng.',
            [['Kim tự tháp', 'Ai Cập'], ['Tượng Vệ Nữ', 'Hy Lạp'], ['Đấu trường Cô-lô-xê', 'La Mã'], ['Đền Parthenon', 'Hy Lạp']],
            'Mỗi nền văn minh để lại những thành tựu mỹ thuật vĩ đại riêng.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.',
            [['Luật frontal', 'Vẽ chính diện dù mặt nghiêng'], ['Phù điêu', 'Chạm nổi'], ['Mái vòm', 'Kiến trúc La Mã'], ['Thức cột', 'Hệ cột Hy Lạp']],
            'Các khái niệm cơ bản giúp hiểu mỹ thuật cổ đại phương Tây.', $d);
        $this->matching($s, 'Nối mỗi loại hình với ví dụ tiêu biểu.',
            [['Kiến trúc', 'Kim tự tháp'], ['Điêu khắc', 'Tượng Vệ Nữ'], ['Hội họa', 'Bích họa'], ['Trang trí', 'Gốm vẽ']],
            'Mỹ thuật cổ đại phát triển ở cả kiến trúc, điêu khắc, hội họa và trang trí.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm văn hóa với nền văn minh của nó.',
            [['Ướp xác', 'Ai Cập'], ['Thế vận hội', 'Hy Lạp'], ['Hệ thống luật', 'La Mã'], ['Chữ tượng hình', 'Ai Cập']],
            'Đặc điểm văn hóa gắn liền với sự phát triển mỹ thuật của mỗi nền văn minh.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào đúng nền mỹ thuật: AI CẬP / HY LẠP / LA MÃ.',
            [['Kim tự tháp', 'AI CẬP'], ['Luật frontal', 'AI CẬP'], ['Tượng Vệ Nữ', 'HY LẠP'], ['Đấu trường', 'LA MÃ']],
            'Kim tự tháp, luật frontal thuộc Ai Cập; tượng Vệ Nữ thuộc Hy Lạp; đấu trường thuộc La Mã.', $d);
        $this->sortQ($s, 'Xếp các công trình vào đúng nền văn minh.',
            [['Lăng mộ hình chóp', 'Ai Cập'], ['Tượng thần Zeus', 'Hy Lạp'], ['Khải hoàn môn', 'La Mã'], ['Đền Parthenon', 'Hy Lạp']],
            'Mỗi công trình là biểu tượng của nền văn minh tạo ra nó.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Ai Cập có luật frontal', 'ĐÚNG'], ['Hy Lạp đề cao vẻ đẹp cơ thể', 'ĐÚNG'], ['La Mã không biết xây dựng', 'SAI'], ['Kim tự tháp ở Hy Lạp', 'SAI']],
            'La Mã rất giỏi xây dựng; kim tự tháp là công trình của Ai Cập.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: THUỘC VỀ TÔN GIÁO / THẾ TỤC.',
            [['Lăng mộ', 'THUỘC VỀ TÔN GIÁO'], ['Đền thờ', 'THUỘC VỀ TÔN GIÁO'], ['Chân dung', 'THẾ TỤC'], ['Tranh sinh hoạt', 'THẾ TỤC']],
            'Lăng mộ, đền thờ gắn với tôn giáo; chân dung, sinh hoạt mang tính thế tục.', $d);
        $this->sortQ($s, 'Xếp các vật liệu vào nhóm: THỜI CỔ ĐẠI / HIỆN ĐẠI.',
            [['Đá', 'THỜI CỔ ĐẠI'], ['Đồng', 'THỜI CỔ ĐẠI'], ['Bê tông cốt thép', 'HIỆN ĐẠI'], ['Kính cường lực', 'HIỆN ĐẠI']],
            'Cổ đại dùng đá, đồng; hiện đại dùng bê tông cốt thép, kính cường lực.', $d);
        $this->fill($s, 'Nơi an nghỉ hình chóp của các Pharaon được gọi là kim tự ___.', [[0, 'tháp']],
            'Kim tự tháp là lăng mộ hình chóp của các Pharaon Ai Cập.', $d);
        $this->fill($s, 'Người Hy Lạp cổ đại tôn vinh vẻ đẹp ___ thể con người trong điêu khắc.', [[0, 'cơ']],
            'Điêu khắc Hy Lạp tôn vinh vẻ đẹp cơ thể con người khỏe khoắn, cân đối.', $d);
        $this->fill($s, 'Người La Mã rất giỏi xây dựng với phát minh mái ___ và bê tông.', [[0, 'vòm']],
            'Mái vòm và bê tông là hai phát minh kiến trúc vĩ đại của La Mã.', $d);
        $this->fill($s, 'Chữ viết bằng hình vẽ của người Ai Cập gọi là chữ tượng ___.', [[0, 'hình']],
            'Chữ tượng hình là hệ chữ viết bằng hình vẽ của người Ai Cập cổ đại.', $d);
        $this->fill($s, 'Đền Parthenon là công trình tiêu biểu của kiến trúc Hy ___.', [[0, 'Lạp']],
            'Đền Parthenon ở Athens là biểu tượng của kiến trúc Hy Lạp cổ đại.', $d);
    }

    // ===== 22. Phục hưng, Ấn tượng và nghệ thuật hiện đại (lớp 10, khó) =====
    private function seedMtThpt10Lop104(): void
    {
        $s = 'my-thuat-thpt-10-lop-10-4'; $d = 'kho';
        $this->quiz($s, 'Leonardo da Vinci là danh họa của thời kỳ nào?',
            ['Phục hưng', 'Ấn tượng', 'Lập thể', 'Siêu thực'], 0,
            'Leonardo da Vinci là danh họa tiêu biểu của thời kỳ Phục hưng ở Ý.', $d);
        $this->quiz($s, 'Phối cảnh (perspective) trong hội họa Phục hưng có tác dụng gì?',
            ['Tạo chiều sâu không gian', 'Làm tranh phẳng hơn', 'Giảm bớt màu sắc', 'Vẽ nhanh hơn'], 0,
            'Luật phối cảnh giúp tạo chiều sâu không gian, làm tranh như thật.', $d);
        $this->quiz($s, 'Họa sĩ trường phái Ấn tượng quan tâm nhất điều gì?',
            ['Ánh sáng và cảm nhận khoảnh khắc', 'Vẽ chi tiết tỉ mỉ', 'Chủ đề thần thoại', 'Kích thước tranh lớn'], 0,
            'Họa sĩ Ấn tượng quan tâm ánh sáng và cảm nhận chân thực của khoảnh khắc.', $d);
        $this->quiz($s, 'Trường phái Lập thể có đặc điểm gì?',
            ['Phân mảnh hình khối, nhiều góc nhìn', 'Vẽ phong cảnh lãng mạn', 'Chỉ dùng một màu', 'Vẽ chân dung giống ảnh chụp'], 0,
            'Lập thể phân mảnh hình khối và thể hiện nhiều góc nhìn cùng lúc.', $d);
        $this->quiz($s, 'Nghệ thuật trừu tượng thế kỷ 20 hướng tới điều gì?',
            ['Biểu đạt cảm xúc qua hình và màu', 'Chép y hệt thực tế', 'Chỉ vẽ đề tài tôn giáo', 'Quay lại phong cách cổ điển'], 0,
            'Nghệ thuật trừu tượng không tả thực mà biểu đạt cảm xúc qua hình và màu.', $d);
        $this->matching($s, 'Nối mỗi thời kỳ với đặc điểm của nó.',
            [['Phục hưng', 'Phối cảnh – giải phẫu'], ['Ấn tượng', 'Ánh sáng ngoài trời'], ['Lập thể', 'Phân mảnh khối'], ['Trừu tượng', 'Không tả thực']],
            'Mỗi thời kỳ mỹ thuật có đặc điểm kỹ thuật và tinh thần riêng.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.',
            [['Phối cảnh', 'Chiều sâu không gian'], ['Ấn tượng', 'Cảm nhận khoảnh khắc'], ['Lập thể', 'Nhiều góc nhìn'], ['Dã thú', 'Màu sắc mạnh']],
            'Hiểu khái niệm giúp phân biệt các trào lưu mỹ thuật hiện đại.', $d);
        $this->matching($s, 'Nối mỗi trào lưu với thời gian tương ứng.',
            [['Phục hưng', 'Thế kỷ 14 – 16'], ['Ấn tượng', 'Cuối thế kỷ 19'], ['Lập thể', 'Đầu thế kỷ 20'], ['Siêu thực', 'Giữa thế kỷ 20']],
            'Phục hưng thế kỷ 14–16, Ấn tượng cuối thế kỷ 19, Lập thể đầu thế kỷ 20.', $d);
        $this->matching($s, 'Nối mỗi tinh thần với thời kỳ tương ứng.',
            [['Nhân văn', 'Phục hưng'], ['Khoa học', 'Phục hưng'], ['Tự do sáng tạo', 'Hiện đại'], ['Phá cách', 'Hiện đại']],
            'Phục hưng đề cao nhân văn và khoa học; hiện đại đề cao tự do và phá cách.', $d);
        $this->matching($s, 'Nối mỗi kỹ thuật với trào lưu dùng nó.',
            [['Sơn dầu nhiều lớp', 'Phục hưng'], ['Nét cọ tách màu', 'Ấn tượng'], ['Hình học phân mảnh', 'Lập thể'], ['Màu sắc tự do', 'Dã thú']],
            'Mỗi trào lưu có kỹ thuật thể hiện đặc trưng riêng.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào nhóm: PHỤC HƯNG / ẤN TƯỢNG / HIỆN ĐẠI.',
            [['Phối cảnh', 'PHỤC HƯNG'], ['Giải phẫu học', 'PHỤC HƯNG'], ['Vẽ ngoài trời', 'ẤN TƯỢNG'], ['Trừu tượng', 'HIỆN ĐẠI']],
            'Phối cảnh, giải phẫu thuộc Phục hưng; vẽ ngoài trời thuộc Ấn tượng; trừu tượng thuộc hiện đại.', $d);
        $this->sortQ($s, 'Xếp các thời kỳ vào đúng thế kỷ của nó.',
            [['Phục hưng', 'Thế kỷ 14 – 16'], ['Baroque', 'Thế kỷ 17'], ['Ấn tượng', 'Thế kỷ 19'], ['Lập thể', 'Thế kỷ 20']],
            'Phục hưng 14–16, Baroque thế kỷ 17, Ấn tượng thế kỷ 19, Lập thể thế kỷ 20.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Phục hưng coi trọng con người', 'ĐÚNG'], ['Lập thể phân mảnh hình khối', 'ĐÚNG'], ['Ấn tượng vẽ trong xưởng tối', 'SAI'], ['Trừu tượng chép y thực tế', 'SAI']],
            'Họa sĩ Ấn tượng vẽ ngoài trời; nghệ thuật trừu tượng không tả thực.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: KỸ THUẬT / TINH THẦN.',
            [['Phối cảnh', 'KỸ THUẬT'], ['Sơn dầu', 'KỸ THUẬT'], ['Nhân văn', 'TINH THẦN'], ['Tự do sáng tạo', 'TINH THẦN']],
            'Phối cảnh, sơn dầu là kỹ thuật; nhân văn, tự do sáng tạo là tinh thần.', $d);
        $this->sortQ($s, 'Xếp các họa sĩ (theo tác phẩm) vào nhóm: PHỤC HƯNG / ẤN TƯỢNG.',
            [['Vẽ nàng Mona Lisa', 'PHỤC HƯNG'], ['Vẽ bữa tiệc cuối cùng', 'PHỤC HƯNG'], ['Vẽ hoa súng', 'ẤN TƯỢNG'], ['Vẽ nhà thờ Rouen', 'ẤN TƯỢNG']],
            'Mona Lisa thuộc Phục hưng; hoa súng và nhà thờ Rouen thuộc Ấn tượng.', $d);
        $this->fill($s, 'Thời kỳ Phục hưng đề cao giá trị con người, gọi là tinh thần nhân ___.', [[0, 'văn']],
            'Tinh thần nhân văn đề cao giá trị con người là cốt lõi của Phục hưng.', $d);
        $this->fill($s, 'Kỹ thuật tạo chiều sâu không gian trong tranh Phục hưng gọi là luật phối ___.', [[0, 'cảnh']],
            'Luật phối cảnh là thành tựu kỹ thuật vĩ đại của hội họa Phục hưng.', $d);
        $this->fill($s, 'Họa sĩ Ấn tượng thích vẽ ___ trời để bắt ánh sáng tự nhiên.', [[0, 'ngoài']],
            'Họa sĩ Ấn tượng vẽ ngoài trời để bắt ánh sáng tự nhiên chân thực.', $d);
        $this->fill($s, 'Trường phái Dã thú nổi tiếng với cách dùng màu sắc ___ mẽ, rực rỡ.', [[0, 'mạnh']],
            'Trường phái Dã thú dùng màu sắc mạnh mẽ, rực rỡ đầy cảm xúc.', $d);
        $this->fill($s, 'Nghệ thuật ___ tượng không tả thực mà biểu đạt cảm xúc.', [[0, 'trừu']],
            'Nghệ thuật trừu tượng biểu đạt cảm xúc qua hình, màu thay vì tả thực.', $d);
    }

    // ===== 23. Cân bằng, tương phản và điểm nhấn (lớp 11, trung bình) =====
    private function seedMtThpt11Lop111(): void
    {
        $s = 'my-thuat-thpt-11-lop-11-1'; $d = 'trung_binh';
        $this->quiz($s, 'Tương phản về kích thước được tạo ra bằng cách nào?',
            ['Đặt vật to cạnh vật nhỏ', 'Dùng cùng một cỡ', 'Chỉ dùng vật to', 'Xóa bỏ vật nhỏ'], 0,
            'Đặt vật to cạnh vật nhỏ tạo tương phản kích thước rõ rệt.', $d);
        $this->quiz($s, 'Quy tắc một phần ba trong thiết kế có tác dụng gì?',
            ['Đặt điểm nhấn vào vị trí đẹp', 'Chia đều mọi thứ', 'Giảm bớt màu sắc', 'Tăng kích thước chữ'], 0,
            'Quy tắc một phần ba giúp đặt điểm nhấn vào vị trí đẹp, thu hút mắt nhìn.', $d);
        $this->quiz($s, 'Cân bằng bất đối xứng đạt được bằng cách nào?',
            ['Trọng lượng thị giác hai bên tương đương', 'Hai bên giống hệt nhau', 'Chỉ đặt yếu tố một bên', 'Không cần cân bằng'], 0,
            'Cân bằng bất đối xứng đạt được khi trọng lượng thị giác hai bên tương đương nhau.', $d);
        $this->quiz($s, 'Có quá nhiều điểm nhấn trong một thiết kế sẽ gây ra điều gì?',
            ['Rối mắt, mất trọng tâm', 'Đẹp hơn', 'Chuyên nghiệp hơn', 'Không ảnh hưởng gì'], 0,
            'Quá nhiều điểm nhấn khiến người xem rối mắt và mất trọng tâm.', $d);
        $this->quiz($s, 'Tương phản màu sắc mạnh nhất là cặp màu nào?',
            ['Màu bổ túc (đối diện nhau)', 'Hai màu kề nhau', 'Cùng một màu', 'Màu xám với xám'], 0,
            'Hai màu bổ túc đối diện nhau trên vòng tròn màu tạo tương phản mạnh nhất.', $d);
        $this->matching($s, 'Nối mỗi nguyên lý với mô tả của nó.',
            [['Cân bằng', 'Phân bố hài hòa'], ['Tương phản', 'Đối lập thu hút'], ['Điểm nhấn', 'Trọng tâm thiết kế'], ['Nhịp điệu', 'Lặp lại có quy luật']],
            'Cân bằng, tương phản, điểm nhấn, nhịp điệu là các nguyên lý thiết kế cơ bản.', $d);
        $this->matching($s, 'Nối mỗi cặp tương phản với ví dụ của nó.',
            [['To – nhỏ', 'Chữ tiêu đề và nội dung'], ['Đậm – nhạt', 'Nền và chữ'], ['Nóng – lạnh', 'Màu sắc'], ['Thẳng – cong', 'Đường nét']],
            'Tương phản thể hiện qua kích thước, độ đậm nhạt, màu sắc và đường nét.', $d);
        $this->matching($s, 'Nối mỗi loại cân bằng với đặc điểm của nó.',
            [['Đối xứng', 'Hai bên giống nhau'], ['Bất đối xứng', 'Trọng lượng tương đương'], ['Hướng tâm', 'Xoay quanh tâm'], ['Mất cân bằng', 'Lệch lạc']],
            'Cân bằng có nhiều dạng: đối xứng, bất đối xứng và hướng tâm.', $d);
        $this->matching($s, 'Nối mỗi vị trí với vai trò của nó trong bố cục.',
            [['Trung tâm', 'Tập trung'], ['Giao điểm 1/3', 'Vị trí đẹp'], ['Mép khung', 'Phụ trợ'], ['Góc khuất', 'Vị trí yếu']],
            'Đặt yếu tố quan trọng vào trung tâm hoặc giao điểm một phần ba.', $d);
        $this->matching($s, 'Nối mỗi lỗi thiết kế với cách sửa của nó.',
            [['Nhiều điểm nhấn', 'Giữ lại một'], ['Thiếu tương phản', 'Tăng đối lập'], ['Bố cục lệch', 'Cân lại'], ['Rối rắm', 'Đơn giản hóa']],
            'Sửa lỗi thiết kế bằng cách giữ một điểm nhấn và tăng tương phản hợp lý.', $d);
        $this->sortQ($s, 'Xếp các cặp yếu tố vào nhóm: CÓ TƯƠNG PHẢN / KHÔNG TƯƠNG PHẢN.',
            [['Đen – trắng', 'CÓ TƯƠNG PHẢN'], ['Đỏ – xanh lá', 'CÓ TƯƠNG PHẢN'], ['Xám – xám nhạt', 'KHÔNG TƯƠNG PHẢN'], ['Xanh dương – xanh lá', 'KHÔNG TƯƠNG PHẢN']],
            'Đen – trắng và đỏ – xanh lá tương phản mạnh; các cặp gần nhau ít tương phản.', $d);
        $this->sortQ($s, 'Xếp các bố cục vào nhóm: ĐỐI XỨNG / BẤT ĐỐI XỨNG.',
            [['Logo hai bên giống nhau', 'ĐỐI XỨNG'], ['Mặt đồng hồ', 'ĐỐI XỨNG'], ['Poster tự do', 'BẤT ĐỐI XỨNG'], ['Trang web hiện đại', 'BẤT ĐỐI XỨNG']],
            'Đối xứng hai bên giống nhau; bất đối xứng sắp xếp tự do nhưng vẫn cân bằng.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Tương phản thu hút sự chú ý', 'ĐÚNG'], ['Cân bằng tạo sự ổn định', 'ĐÚNG'], ['Nên có thật nhiều điểm nhấn', 'SAI'], ['Đối xứng luôn nhàm chán', 'SAI']],
            'Chỉ nên có một điểm nhấn chính; đối xứng có thể rất trang trọng, đẹp.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: TẠO ĐIỂM NHẤN TỐT / KHÔNG TỐT.',
            [['Màu tương phản', 'TẠO ĐIỂM NHẤN TỐT'], ['Kích thước lớn', 'TẠO ĐIỂM NHẤN TỐT'], ['Màu chìm vào nền', 'KHÔNG TỐT'], ['Đặt ở góc khuất', 'KHÔNG TỐT']],
            'Điểm nhấn tốt cần nổi bật về màu sắc, kích thước và vị trí.', $d);
        $this->sortQ($s, 'Xếp các thiết kế vào nhóm: CÂN BẰNG / MẤT CÂN BẰNG.',
            [['Poster phân bố đều', 'CÂN BẰNG'], ['Logo đối xứng', 'CÂN BẰNG'], ['Banner dồn một bên', 'MẤT CÂN BẰNG'], ['Tờ rơi lệch hẳn', 'MẤT CÂN BẰNG']],
            'Thiết kế cân bằng phân bố hài hòa; dồn một bên gây mất cân bằng.', $d);
        $this->fill($s, 'Đặt các yếu tố có đặc điểm đối lập cạnh nhau gọi là tạo sự tương ___.', [[0, 'phản']],
            'Tương phản là đặt các yếu tố đối lập cạnh nhau để thu hút sự chú ý.', $d);
        $this->fill($s, 'Cân bằng đối xứng tạo cảm giác trang trọng và ___ định.', [[0, 'ổn']],
            'Cân bằng đối xứng mang lại cảm giác trang trọng và ổn định.', $d);
        $this->fill($s, 'Chi tiết được chú ý đầu tiên trong thiết kế gọi là điểm ___.', [[0, 'nhấn']],
            'Điểm nhấn là chi tiết được chú ý đầu tiên trong thiết kế.', $d);
        $this->fill($s, 'Quy tắc một phần ba giúp đặt điểm nhấn vào vị trí ___ trong khung hình.', [[0, 'đẹp']],
            'Giao điểm một phần ba là vị trí đẹp để đặt điểm nhấn.', $d);
        $this->fill($s, 'Thiết kế mất cân bằng khiến người xem cảm thấy khó ___.', [[0, 'chịu']],
            'Mất cân bằng gây cảm giác khó chịu, thiếu chuyên nghiệp cho người xem.', $d);
    }

    // ===== 24. Nhịp điệu, tỷ lệ, thống nhất và khoảng trắng (lớp 11, trung bình) =====
    private function seedMtThpt11Lop112(): void
    {
        $s = 'my-thuat-thpt-11-lop-11-2'; $d = 'trung_binh';
        $this->quiz($s, 'Nhịp điệu đều đặn được tạo ra bằng cách nào?',
            ['Lặp lại yếu tố với khoảng cách bằng nhau', 'Đặt các yếu tố ngẫu nhiên', 'Chỉ dùng một yếu tố', 'Xóa bớt yếu tố'], 0,
            'Lặp lại yếu tố với khoảng cách bằng nhau tạo nhịp điệu đều đặn.', $d);
        $this->quiz($s, 'Tỷ lệ vàng thường được ứng dụng ở đâu?',
            ['Bố cục ảnh, logo, kiến trúc', 'Nấu ăn', 'Thi đấu thể thao', 'Sáng tác âm nhạc'], 0,
            'Tỷ lệ vàng được ứng dụng rộng rãi trong bố cục ảnh, logo và kiến trúc.', $d);
        $this->quiz($s, 'Thiếu tính thống nhất trong thiết kế gây ra điều gì?',
            ['Rời rạc, thiếu chuyên nghiệp', 'Đẹp hơn', 'Sáng tạo hơn', 'Không ảnh hưởng gì'], 0,
            'Thiếu thống nhất khiến thiết kế rời rạc và thiếu chuyên nghiệp.', $d);
        $this->quiz($s, 'Khoảng trắng quá ít trong thiết kế sẽ như thế nào?',
            ['Chật chội, khó đọc', 'Thoáng đãng', 'Sang trọng', 'Cân bằng'], 0,
            'Thiếu khoảng trắng khiến thiết kế chật chội và khó đọc.', $d);
        $this->quiz($s, 'Tính thống nhất trong thiết kế đạt được bằng cách nào?',
            ['Dùng chung màu sắc, font chữ, phong cách', 'Mỗi phần một kiểu khác nhau', 'Thay đổi liên tục', 'Không cần quan tâm'], 0,
            'Dùng chung màu sắc, font chữ và phong cách tạo tính thống nhất.', $d);
        $this->matching($s, 'Nối mỗi nguyên lý với mô tả của nó.',
            [['Nhịp điệu', 'Lặp lại có quy luật'], ['Tỷ lệ', 'Quan hệ kích thước'], ['Thống nhất', 'Đồng bộ phong cách'], ['Khoảng trắng', 'Vùng thở của thiết kế']],
            'Nhịp điệu, tỷ lệ, thống nhất và khoảng trắng là nguyên lý thiết kế quan trọng.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ví dụ của nó.',
            [['Nhịp điệu', 'Họa tiết lặp lại'], ['Tỷ lệ vàng', 'Khung ảnh đẹp'], ['Khoảng trắng', 'Lề trang'], ['Thống nhất', 'Bộ nhận diện']],
            'Mỗi nguyên lý thiết kế đều có ví dụ cụ thể trong thực tế.', $d);
        $this->matching($s, 'Nối mỗi con số với ý nghĩa của nó.',
            [['1 : 1,618', 'Tỷ lệ vàng'], ['1/3', 'Quy tắc bố cục'], ['2/3', 'Vùng chính'], ['50/50', 'Chia đôi']],
            'Các con số tỷ lệ giúp bố cục đạt sự cân đối, hài hòa.', $d);
        $this->matching($s, 'Nối mỗi lỗi thiết kế với cách khắc phục.',
            [['Rối rắm', 'Giảm bớt yếu tố'], ['Chật chội', 'Tăng khoảng trắng'], ['Lạc tông', 'Thống nhất màu sắc'], ['Đơn điệu', 'Thêm nhịp điệu']],
            'Khắc phục lỗi bằng cách giảm yếu tố thừa và tăng khoảng trắng.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với vai trò của nó.',
            [['Lề trang', 'Khoảng thở'], ['Tiêu đề lớn', 'Thứ bậc'], ['Màu chủ đạo', 'Thống nhất'], ['Họa tiết lặp', 'Nhịp điệu']],
            'Mỗi yếu tố trong thiết kế đảm nhận một vai trò riêng.', $d);
        $this->sortQ($s, 'Xếp các ví dụ vào nhóm: CÓ NHỊP ĐIỆU / KHÔNG CÓ NHỊP ĐIỆU.',
            [['Hàng cột đều nhau', 'CÓ NHỊP ĐIỆU'], ['Họa tiết lặp lại', 'CÓ NHỊP ĐIỆU'], ['Vân gỗ ngẫu nhiên', 'KHÔNG CÓ NHỊP ĐIỆU'], ['Đốm màu lộn xộn', 'KHÔNG CÓ NHỊP ĐIỆU']],
            'Nhịp điệu đến từ sự lặp lại có quy luật, không phải ngẫu nhiên.', $d);
        $this->sortQ($s, 'Xếp các thiết kế vào nhóm: KHOẢNG TRẮNG TỐT / QUÁ CHẬT CHỘI.',
            [['Poster thoáng đãng', 'KHOẢNG TRẮNG TỐT'], ['Trang web nhiều lề', 'KHOẢNG TRẮNG TỐT'], ['Tờ rơi chữ kín mít', 'QUÁ CHẬT CHỘI'], ['Banner chen chúc', 'QUÁ CHẬT CHỘI']],
            'Khoảng trắng tốt giúp thoáng đãng; nhồi nhét gây chật chội, khó đọc.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Khoảng trắng giúp dễ đọc', 'ĐÚNG'], ['Tỷ lệ vàng xấp xỉ 1:1,618', 'ĐÚNG'], ['Nên nhồi nhét thật nhiều chi tiết', 'SAI'], ['Thống nhất là mỗi phần một kiểu', 'SAI']],
            'Thống nhất là đồng bộ phong cách, không phải mỗi phần một kiểu.', $d);
        $this->sortQ($s, 'Xếp các cặp tỷ lệ vào nhóm: CÂN ĐỐI HÀI HÒA / LỆCH LẠC.',
            [['1 : 1,618', 'CÂN ĐỐI HÀI HÒA'], ['2 : 3', 'CÂN ĐỐI HÀI HÒA'], ['1 : 10', 'LỆCH LẠC'], ['9 : 1', 'LỆCH LẠC']],
            'Tỷ lệ vàng và các tỷ lệ gần nó tạo sự cân đối hài hòa.', $d);
        $this->sortQ($s, 'Xếp các bố cục vào nhóm: THỐNG NHẤT / RỜI RẠC.',
            [['Cùng font, cùng màu', 'THỐNG NHẤT'], ['Bộ nhận diện đồng bộ', 'THỐNG NHẤT'], ['Mỗi trang một kiểu', 'RỜI RẠC'], ['Màu sắc lộn xộn', 'RỜI RẠC']],
            'Thống nhất là đồng bộ; mỗi nơi một kiểu gây rời rạc.', $d);
        $this->fill($s, 'Sự lặp lại các yếu tố theo quy luật tạo nên ___ điệu trong thiết kế.', [[0, 'nhịp']],
            'Nhịp điệu là sự lặp lại các yếu tố theo quy luật trong thiết kế.', $d);
        $this->fill($s, 'Vùng trống xung quanh nội dung giúp thiết kế thoáng đãng gọi là khoảng ___.', [[0, 'trắng']],
            'Khoảng trắng là vùng trống giúp thiết kế thoáng đãng, dễ đọc.', $d);
        $this->fill($s, 'Dùng chung màu sắc và font chữ giúp thiết kế có tính thống ___.', [[0, 'nhất']],
            'Tính thống nhất đạt được khi dùng chung màu sắc, font chữ, phong cách.', $d);
        $this->fill($s, 'Tỷ lệ vàng giúp bố cục đạt sự cân đối ___ hòa.', [[0, 'hài']],
            'Tỷ lệ vàng mang lại sự cân đối hài hòa cho bố cục thiết kế.', $d);
        $this->fill($s, 'Thiết kế chật chội, thiếu khoảng trắng khiến người xem khó ___.', [[0, 'đọc']],
            'Thiếu khoảng trắng khiến thiết kế chật chội và khó đọc.', $d);
    }

    // ===== 25. Vòng tròn màu và các nhóm màu (lớp 11, trung bình) =====
    private function seedMtThpt11Lop113(): void
    {
        $s = 'my-thuat-thpt-11-lop-11-3'; $d = 'trung_binh';
        $this->quiz($s, 'Màu bậc 2 (màu thứ cấp) gồm những màu nào?',
            ['Cam, xanh lá, tím', 'Đỏ, vàng, lam', 'Đen, trắng, xám', 'Hồng, nâu, be'], 0,
            'Màu bậc 2 gồm cam, xanh lá và tím, pha từ hai màu bậc 1.', $d);
        $this->quiz($s, 'Màu bậc 3 được tạo ra bằng cách nào?',
            ['Pha màu bậc 1 với màu bậc 2 kề nó', 'Pha ba màu bậc 1', 'Trộn đen với trắng', 'Không thể tạo ra'], 0,
            'Màu bậc 3 được tạo bằng cách pha một màu bậc 1 với màu bậc 2 kề nó.', $d);
        $this->quiz($s, 'Trên vòng tròn màu, các màu nóng nằm ở phía nào?',
            ['Nửa có màu đỏ, cam, vàng', 'Nửa có màu lam, tím', 'Ở tâm vòng tròn', 'Ngoài vòng tròn'], 0,
            'Nửa vòng tròn màu có đỏ, cam, vàng là nhóm màu nóng.', $d);
        $this->quiz($s, 'Cặp màu bổ túc của màu vàng là màu nào?',
            ['Màu tím', 'Màu cam', 'Màu đỏ', 'Màu xanh lá'], 0,
            'Màu tím đối diện màu vàng trên vòng tròn màu nên là cặp bổ túc của vàng.', $d);
        $this->quiz($s, 'Vòng tròn màu giúp ích gì cho người thiết kế?',
            ['Chọn và phối màu hài hòa', 'Vẽ hình tròn đẹp', 'Đo kích thước', 'Chọn font chữ'], 0,
            'Vòng tròn màu là công cụ giúp chọn và phối màu hài hòa, hợp lý.', $d);
        $this->matching($s, 'Nối mỗi bậc màu với các màu thuộc bậc đó.',
            [['Bậc 1', 'Đỏ – vàng – lam'], ['Bậc 2', 'Cam – lục – tím'], ['Bậc 3', 'Vàng cam – đỏ tím'], ['Trung tính', 'Đen – trắng – xám']],
            'Bậc 1 là màu gốc, bậc 2 pha từ bậc 1, bậc 3 pha từ bậc 1 và bậc 2.', $d);
        $this->matching($s, 'Nối mỗi cặp màu với quan hệ của chúng.',
            [['Đỏ – xanh lá', 'Bổ túc'], ['Vàng – cam', 'Kề nhau'], ['Lam – tím', 'Kề nhau'], ['Cam – lam', 'Bổ túc']],
            'Cặp đối diện nhau là bổ túc, cặp đứng cạnh nhau là kề nhau.', $d);
        $this->matching($s, 'Nối mỗi nhóm màu với cảm giác nó gợi ra.',
            [['Màu nóng', 'Sôi động'], ['Màu lạnh', 'Yên tĩnh'], ['Màu trung tính', 'Trang nhã'], ['Màu pastel', 'Nhẹ nhàng']],
            'Mỗi nhóm màu trên vòng tròn gợi một cảm giác khác nhau.', $d);
        $this->matching($s, 'Nối mỗi màu bậc 2 với cách pha của nó.',
            [['Cam', 'Đỏ + vàng'], ['Xanh lá', 'Vàng + lam'], ['Tím', 'Lam + đỏ'], ['Nâu', 'Ba màu bậc 1']],
            'Màu bậc 2 được pha từ hai màu bậc 1 với nhau.', $d);
        $this->matching($s, 'Nối mỗi vị trí trên vòng tròn màu với nhóm màu.',
            [['Gần màu đỏ', 'Màu nóng'], ['Gần màu lam', 'Màu lạnh'], ['Đối diện nhau', 'Bổ túc'], ['Cạnh nhau', 'Kề nhau']],
            'Vị trí trên vòng tròn màu cho biết quan hệ giữa các màu.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: MÀU NÓNG / MÀU LẠNH.',
            [['Đỏ', 'MÀU NÓNG'], ['Cam', 'MÀU NÓNG'], ['Lam', 'MÀU LẠNH'], ['Xanh lá', 'MÀU LẠNH']],
            'Đỏ, cam là màu nóng; lam, xanh lá là màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các màu vào đúng BẬC của nó.',
            [['Đỏ', 'Bậc 1'], ['Vàng', 'Bậc 1'], ['Cam', 'Bậc 2'], ['Tím', 'Bậc 2']],
            'Đỏ, vàng, lam là bậc 1; cam, xanh lá, tím là bậc 2.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Bậc 1 gồm đỏ, vàng, lam', 'ĐÚNG'], ['Bổ túc là hai màu đối diện', 'ĐÚNG'], ['Cam là màu bậc 1', 'SAI'], ['Vòng tròn màu chỉ có 5 màu', 'SAI']],
            'Cam là màu bậc 2; vòng tròn màu cơ bản có 12 màu.', $d);
        $this->sortQ($s, 'Xếp các cặp màu vào nhóm: BỔ TÚC / KỀ NHAU.',
            [['Đỏ – xanh lá', 'BỔ TÚC'], ['Lam – cam', 'BỔ TÚC'], ['Vàng – cam', 'KỀ NHAU'], ['Tím – lam', 'KỀ NHAU']],
            'Cặp đối diện là bổ túc, cặp cạnh nhau là kề nhau.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: TRUNG TÍNH / CÓ SẮC.',
            [['Đen', 'TRUNG TÍNH'], ['Trắng', 'TRUNG TÍNH'], ['Xám', 'TRUNG TÍNH'], ['Đỏ', 'CÓ SẮC']],
            'Đen, trắng, xám là màu trung tính; đỏ là màu có sắc.', $d);
        $this->fill($s, 'Ba màu bậc 1 còn được gọi là màu ___.', [[0, 'gốc']],
            'Ba màu bậc 1 là ba màu gốc: đỏ, vàng, lam.', $d);
        $this->fill($s, 'Màu cam, xanh lá, tím thuộc nhóm màu bậc ___.', [[0, 'hai']],
            'Cam, xanh lá, tím là ba màu bậc 2 (màu thứ cấp).', $d);
        $this->fill($s, 'Hai màu nằm đối diện nhau trên vòng tròn màu gọi là cặp màu bổ ___.', [[0, 'túc']],
            'Cặp màu bổ túc là hai màu đối diện nhau trên vòng tròn màu.', $d);
        $this->fill($s, 'Pha màu bậc 1 với màu bậc 2 kề nó sẽ được màu bậc ___.', [[0, 'ba']],
            'Màu bậc 3 được pha từ một màu bậc 1 với màu bậc 2 kề nó.', $d);
        $this->fill($s, 'Vòng tròn màu cơ bản thường có ___ màu chính.', [[0, '12']],
            'Vòng tròn màu cơ bản thường gồm 12 màu chính.', $d);
    }

    // ===== 26. Phối màu và tâm lý màu sắc (lớp 11, khó) =====
    private function seedMtThpt11Lop114(): void
    {
        $s = 'my-thuat-thpt-11-lop-11-4'; $d = 'kho';
        $this->quiz($s, 'Phối màu tương đồng (analogous) là cách phối nào?',
            ['Các màu kề nhau trên vòng tròn màu', 'Hai màu đối diện nhau', 'Chỉ dùng một màu', 'Chọn màu ngẫu nhiên'], 0,
            'Phối màu tương đồng dùng các màu kề nhau trên vòng tròn màu.', $d);
        $this->quiz($s, 'Phối màu tam giác (triadic) sử dụng những màu nào?',
            ['Ba màu cách đều nhau trên vòng tròn', 'Ba màu kề nhau', 'Hai màu đối diện', 'Một màu duy nhất'], 0,
            'Phối màu tam giác dùng ba màu cách đều nhau trên vòng tròn màu.', $d);
        $this->quiz($s, 'Màu vàng trong tâm lý màu sắc thường gợi điều gì?',
            ['Lạc quan, năng lượng', 'Buồn bã', 'Sợ hãi', 'Lạnh lùng'], 0,
            'Màu vàng thường gợi sự lạc quan và năng lượng tích cực.', $d);
        $this->quiz($s, 'Màu đen trong thiết kế thường tạo cảm giác gì?',
            ['Sang trọng, quyền lực', 'Vui nhộn', 'Trẻ con', 'Lộn xộn'], 0,
            'Màu đen thường tạo cảm giác sang trọng và quyền lực.', $d);
        $this->quiz($s, 'Nguyên tắc 60-30-10 trong phối màu có nghĩa là gì?',
            ['60% màu chủ đạo, 30% màu phụ, 10% điểm nhấn', 'Chia đều cho ba màu', 'Chỉ dùng 10% màu sắc', '60% diện tích màu đen'], 0,
            'Nguyên tắc 60-30-10 phân bổ: 60% màu chủ đạo, 30% màu phụ, 10% điểm nhấn.', $d);
        $this->matching($s, 'Nối mỗi công thức phối màu với mô tả của nó.',
            [['Đơn sắc', 'Một màu nhiều sắc độ'], ['Bổ túc', 'Hai màu đối diện'], ['Tương đồng', 'Các màu kề nhau'], ['Tam giác', 'Ba màu cách đều']],
            'Bốn công thức phối màu cơ bản: đơn sắc, bổ túc, tương đồng, tam giác.', $d);
        $this->matching($s, 'Nối mỗi màu với cảm xúc nó thường gợi ra.',
            [['Đỏ', 'Nhiệt huyết'], ['Xanh lá', 'Tươi mới'], ['Tím', 'Sáng tạo'], ['Cam', 'Thân thiện']],
            'Mỗi màu sắc gợi một cảm xúc khác nhau trong tâm lý người xem.', $d);
        $this->matching($s, 'Nối mỗi màu với cảm xúc tiếp theo của nó.',
            [['Vàng', 'Lạc quan'], ['Xanh dương', 'Tin cậy'], ['Đen', 'Sang trọng'], ['Trắng', 'Tinh khiết']],
            'Hiểu tâm lý màu sắc giúp chọn màu đúng với thông điệp thiết kế.', $d);
        $this->matching($s, 'Nối mỗi lĩnh vực với màu sắc thường dùng.',
            [['Y tế', 'Xanh dương – trắng'], ['Ẩm thực', 'Đỏ – cam'], ['Môi trường', 'Xanh lá'], ['Công nghệ', 'Xanh dương – đen']],
            'Mỗi lĩnh vực có những màu sắc đặc trưng gợi đúng cảm xúc ngành nghề.', $d);
        $this->matching($s, 'Nối mỗi mục đích với cách phối màu phù hợp.',
            [['Sang trọng', 'Đơn sắc tối'], ['Trẻ trung', 'Tương đồng sáng'], ['Nổi bật', 'Bổ túc'], ['Êm dịu', 'Đơn sắc nhạt']],
            'Mục đích thiết kế quyết định công thức phối màu phù hợp.', $d);
        $this->sortQ($s, 'Xếp các cách phối màu vào đúng công thức của nó.',
            [['Đỏ với các sắc độ đỏ', 'Đơn sắc'], ['Vàng – tím', 'Bổ túc'], ['Xanh lá – xanh dương', 'Tương đồng'], ['Đỏ – vàng – lam', 'Tam giác']],
            'Nhận diện công thức phối màu qua các màu được sử dụng.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm: CẢM XÚC TÍCH CỰC / TRANG TRỌNG.',
            [['Vàng', 'CẢM XÚC TÍCH CỰC'], ['Cam', 'CẢM XÚC TÍCH CỰC'], ['Đen', 'TRANG TRỌNG'], ['Xám đậm', 'TRANG TRỌNG']],
            'Vàng, cam gợi tích cực; đen, xám đậm gợi trang trọng.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Đơn sắc là một màu nhiều sắc độ', 'ĐÚNG'], ['Màu sắc ảnh hưởng cảm xúc', 'ĐÚNG'], ['Bổ túc là hai màu kề nhau', 'SAI'], ['Nên dùng quá 5 màu chính', 'SAI']],
            'Bổ túc là hai màu đối diện; không nên dùng quá nhiều màu chính.', $d);
        $this->sortQ($s, 'Xếp các màu vào nhóm NÓNG / LẠNH theo tâm lý.',
            [['Đỏ', 'NÓNG'], ['Vàng', 'NÓNG'], ['Xanh dương', 'LẠNH'], ['Tím', 'LẠNH']],
            'Theo tâm lý, đỏ vàng là màu nóng; xanh dương, tím là màu lạnh.', $d);
        $this->sortQ($s, 'Xếp các lựa chọn màu (theo mô tả) vào nhóm: PHÙ HỢP / CHƯA PHÙ HỢP.',
            [['Hãng sữa dùng xanh dương – trắng', 'PHÙ HỢP'], ['Ngân hàng dùng xanh navy', 'PHÙ HỢP'], ['Bệnh viện dùng đỏ chói', 'CHƯA PHÙ HỢP'], ['Trường mầm non dùng xám xịt', 'CHƯA PHÙ HỢP']],
            'Chọn màu phải hợp với tính chất và cảm xúc của lĩnh vực.', $d);
        $this->fill($s, 'Phối các màu kề nhau trên vòng tròn màu gọi là phối màu tương ___.', [[0, 'đồng']],
            'Phối màu tương đồng dùng các màu kề nhau trên vòng tròn màu.', $d);
        $this->fill($s, 'Ba màu cách đều nhau trên vòng tròn màu tạo thành phối màu tam ___.', [[0, 'giác']],
            'Phối màu tam giác dùng ba màu cách đều nhau trên vòng tròn màu.', $d);
        $this->fill($s, 'Nguyên tắc 60-30-10 giúp phân bổ màu chủ đạo, màu phụ và điểm ___ hợp lý.', [[0, 'nhấn']],
            '10% dành cho điểm nhấn trong nguyên tắc phối màu 60-30-10.', $d);
        $this->fill($s, 'Màu xanh lá thường gợi cảm giác tươi mới, gắn với ___ nhiên.', [[0, 'thiên']],
            'Màu xanh lá gắn với thiên nhiên, gợi cảm giác tươi mới.', $d);
        $this->fill($s, 'Trong thiết kế, nên giới hạn số màu chính để tránh ___ mắt người xem.', [[0, 'rối']],
            'Dùng quá nhiều màu chính khiến thiết kế rối mắt, thiếu chuyên nghiệp.', $d);
    }

    // ===== 27. Logo và nhận diện thương hiệu (lớp 12, trung bình) =====
    private function seedMtThpt12Lop121(): void
    {
        $s = 'my-thuat-thpt-12-lop-12-1'; $d = 'trung_binh';
        $this->quiz($s, 'Logo dạng biểu tượng (icon) có ưu điểm gì?',
            ['Dễ nhớ, nhận diện nhanh', 'Dài dòng khó nhớ', 'Khó in ấn', 'Không có ý nghĩa'], 0,
            'Logo biểu tượng dễ nhớ và giúp nhận diện thương hiệu nhanh chóng.', $d);
        $this->quiz($s, 'Màu sắc trong logo thương hiệu có vai trò gì?',
            ['Gợi cảm xúc, tăng nhận diện', 'Chỉ để trang trí', 'Không quan trọng', 'Che khuyết điểm'], 0,
            'Màu sắc trong logo gợi cảm xúc và tăng khả năng nhận diện thương hiệu.', $d);
        $this->quiz($s, 'Font chữ trong logo nên như thế nào?',
            ['Dễ đọc, hợp tính cách thương hiệu', 'Càng cầu kỳ càng tốt', 'Thay đổi mỗi năm', 'Dùng nhiều font'], 0,
            'Font chữ trong logo cần dễ đọc và phù hợp với tính cách thương hiệu.', $d);
        $this->quiz($s, 'Yếu tố nào KHÔNG thuộc hệ nhận diện thương hiệu?',
            ['Giá bán sản phẩm', 'Logo', 'Màu sắc', 'Font chữ'], 0,
            'Hệ nhận diện gồm logo, màu sắc, font chữ; giá bán không thuộc hệ nhận diện.', $d);
        $this->quiz($s, 'Vì sao logo cần đơn giản?',
            ['Dễ nhớ, dùng được nhiều kích thước', 'Để tiết kiệm mực in', 'Để vẽ nhanh', 'Không có lý do'], 0,
            'Logo đơn giản dễ nhớ và dùng tốt ở mọi kích thước, từ danh thiếp đến biển hiệu.', $d);
        $this->matching($s, 'Nối mỗi loại logo với mô tả của nó.',
            [['Logo chữ', 'Dùng tên thương hiệu'], ['Logo biểu tượng', 'Hình ảnh đại diện'], ['Logo kết hợp', 'Chữ + biểu tượng'], ['Logo huy hiệu', 'Nằm trong khung']],
            'Có bốn loại logo chính: logo chữ, biểu tượng, kết hợp và huy hiệu.', $d);
        $this->matching($s, 'Nối mỗi yếu tố nhận diện với vai trò của nó.',
            [['Logo', 'Nhận biết'], ['Màu sắc', 'Cảm xúc'], ['Font chữ', 'Tính cách'], ['Slogan', 'Thông điệp']],
            'Mỗi yếu tố trong hệ nhận diện đảm nhận một vai trò riêng.', $d);
        $this->matching($s, 'Nối mỗi phẩm chất với ý nghĩa của nó.',
            [['Đơn giản', 'Dễ nhớ'], ['Độc đáo', 'Khác biệt'], ['Bền vững', 'Không lỗi thời'], ['Linh hoạt', 'Nhiều ứng dụng']],
            'Logo tốt cần đơn giản, độc đáo, bền vững và linh hoạt.', $d);
        $this->matching($s, 'Nối mỗi ấn phẩm với vị trí thường đặt logo.',
            [['Danh thiếp', 'Góc trên'], ['Bao bì', 'Mặt trước'], ['Biển hiệu', 'Trung tâm'], ['Website', 'Phần header']],
            'Logo được đặt ở vị trí dễ thấy trên mỗi ấn phẩm.', $d);
        $this->matching($s, 'Nối mỗi mô tả với loại logo tương ứng.',
            [['Tên viết tắt', 'Logo chữ'], ['Hình quả táo', 'Logo biểu tượng'], ['Chữ kèm hình', 'Logo kết hợp'], ['Huy hiệu tròn', 'Logo huy hiệu']],
            'Nhận diện loại logo qua hình thức thể hiện của nó.', $d);
        $this->sortQ($s, 'Xếp các ví dụ vào đúng nhóm loại logo.',
            [['Chữ viết tắt', 'Logo chữ'], ['Hình con chim', 'Logo biểu tượng'], ['Chữ + hình ảnh', 'Logo kết hợp'], ['Khung huy hiệu', 'Logo huy hiệu']],
            'Mỗi ví dụ thuộc về một loại logo nhất định.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào nhóm: LOGO TỐT / LOGO KÉM.',
            [['Đơn giản', 'LOGO TỐT'], ['Dễ nhớ', 'LOGO TỐT'], ['Rối rắm', 'LOGO KÉM'], ['Khó đọc', 'LOGO KÉM']],
            'Logo tốt đơn giản, dễ nhớ; logo kém rối rắm, khó đọc.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Logo cần đơn giản', 'ĐÚNG'], ['Màu sắc gợi cảm xúc', 'ĐÚNG'], ['Nên đổi logo mỗi tháng', 'SAI'], ['Logo không cần nhất quán', 'SAI']],
            'Logo cần ổn định, nhất quán để xây dựng nhận diện lâu dài.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: THUỘC HỆ NHẬN DIỆN / KHÔNG THUỘC.',
            [['Logo', 'THUỘC HỆ NHẬN DIỆN'], ['Màu sắc', 'THUỘC HỆ NHẬN DIỆN'], ['Font chữ', 'THUỘC HỆ NHẬN DIỆN'], ['Giá bán', 'KHÔNG THUỘC']],
            'Hệ nhận diện gồm logo, màu sắc, font chữ; giá bán không thuộc hệ nhận diện.', $d);
        $this->sortQ($s, 'Xếp các việc vào nhóm: NÊN / KHÔNG NÊN khi thiết kế logo.',
            [['Tìm hiểu thương hiệu', 'NÊN'], ['Phác thảo nhiều ý tưởng', 'NÊN'], ['Sao chép logo khác', 'KHÔNG NÊN'], ['Dùng quá nhiều màu', 'KHÔNG NÊN']],
            'Thiết kế logo cần tìm hiểu kỹ, phác thảo nhiều và tuyệt đối không sao chép.', $d);
        $this->fill($s, 'Biểu tượng hình ảnh đại diện cho thương hiệu gọi là ___.', [[0, 'logo']],
            'Logo là biểu tượng hình ảnh đại diện cho thương hiệu.', $d);
        $this->fill($s, 'Logo chỉ gồm chữ viết tên thương hiệu gọi là logo ___.', [[0, 'chữ']],
            'Logo chữ chỉ dùng chữ viết để thể hiện tên thương hiệu.', $d);
        $this->fill($s, 'Câu khẩu hiệu ngắn gọn truyền tải thông điệp gọi là ___.', [[0, 'slogan']],
            'Slogan là câu khẩu hiệu ngắn gọn truyền tải thông điệp thương hiệu.', $d);
        $this->fill($s, 'Bộ yếu tố gồm logo, màu sắc, font chữ tạo nên hệ nhận ___ thương hiệu.', [[0, 'diện']],
            'Hệ nhận diện thương hiệu gồm logo, màu sắc, font chữ đồng bộ.', $d);
        $this->fill($s, 'Logo tốt phải dễ nhớ và không bị ___ thời theo năm tháng.', [[0, 'lỗi']],
            'Logo tốt bền vững, không bị lỗi thời theo năm tháng.', $d);
    }

    // ===== 28. Poster, typography và lưới bố cục (lớp 12, trung bình) =====
    private function seedMtThpt12Lop122(): void
    {
        $s = 'my-thuat-thpt-12-lop-12-2'; $d = 'trung_binh';
        $this->quiz($s, 'Font sans-serif phù hợp với phong cách thiết kế nào?',
            ['Hiện đại, tối giản', 'Cổ điển, trang trọng', 'Viết tay', 'Trang trí cầu kỳ'], 0,
            'Font sans-serif không chân, gọn gàng, phù hợp phong cách hiện đại, tối giản.', $d);
        $this->quiz($s, 'Khoảng cách giữa các dòng chữ ảnh hưởng đến điều gì?',
            ['Độ dễ đọc của văn bản', 'Màu sắc của chữ', 'Kích thước hình ảnh', 'Tốc độ tải trang'], 0,
            'Khoảng cách dòng hợp lý giúp văn bản dễ đọc và thoáng mắt hơn.', $d);
        $this->quiz($s, 'Trong poster, thông tin nào cần nổi bật nhất?',
            ['Tiêu đề / thông điệp chính', 'Địa chỉ chi tiết', 'Chữ ký người thiết kế', 'Ngày in ấn'], 0,
            'Tiêu đề và thông điệp chính là thông tin cần nổi bật nhất trong poster.', $d);
        $this->quiz($s, 'Lưới 12 cột trong thiết kế web có tác dụng gì?',
            ['Chia bố cục linh hoạt, cân đối', 'Vẽ hình tròn', 'Chọn màu sắc', 'Tăng tốc độ tải'], 0,
            'Lưới 12 cột giúp chia bố cục linh hoạt và cân đối trong thiết kế web.', $d);
        $this->quiz($s, 'Thứ bậc thị giác (visual hierarchy) trong poster là gì?',
            ['Sắp xếp mức độ nổi bật của thông tin', 'Xếp chữ theo bảng chữ cái', 'Dùng một cỡ chữ duy nhất', 'Không dùng hình ảnh'], 0,
            'Thứ bậc thị giác là sắp xếp mức độ nổi bật của thông tin từ quan trọng đến chi tiết.', $d);
        $this->matching($s, 'Nối mỗi thành phần poster với vai trò của nó.',
            [['Tiêu đề', 'Thu hút'], ['Hình ảnh', 'Minh họa'], ['Thông tin chi tiết', 'Cung cấp'], ['Lời kêu gọi', 'Thúc đẩy hành động']],
            'Mỗi thành phần trong poster đảm nhận một vai trò riêng.', $d);
        $this->matching($s, 'Nối mỗi loại font với cảm giác của nó.',
            [['Serif', 'Trang trọng'], ['Sans-serif', 'Hiện đại'], ['Viết tay', 'Thân thiện'], ['Trang trí', 'Nghệ thuật']],
            'Mỗi loại font chữ gợi một cảm giác khác nhau cho người xem.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ typography với ý nghĩa của nó.',
            [['Kerning', 'Khoảng cách giữa các chữ'], ['Leading', 'Khoảng cách dòng'], ['Weight', 'Độ đậm của chữ'], ['Size', 'Cỡ chữ']],
            'Các thuật ngữ typography mô tả cách trình bày chữ trong thiết kế.', $d);
        $this->matching($s, 'Nối mỗi công cụ với tác dụng của nó.',
            [['Lưới bố cục', 'Căn chỉnh'], ['Thước đo', 'Đo đạc'], ['Màu sắc', 'Nhấn mạnh'], ['Khoảng trắng', 'Tạo thoáng']],
            'Lưới, thước, màu sắc và khoảng trắng là công cụ của nhà thiết kế.', $d);
        $this->matching($s, 'Nối mỗi lỗi poster với cách sửa của nó.',
            [['Chữ quá nhỏ', 'Tăng cỡ chữ'], ['Dùng quá nhiều font', 'Giảm còn 1–2 font'], ['Thiếu điểm nhấn', 'Tạo thứ bậc'], ['Màu sắc chói', 'Hài hòa lại']],
            'Sửa lỗi poster bằng cách điều chỉnh chữ, font, thứ bậc và màu sắc.', $d);
        $this->sortQ($s, 'Xếp các font chữ vào nhóm: SERIF / SANS-SERIF.',
            [['Times', 'SERIF'], ['Georgia', 'SERIF'], ['Arial', 'SANS-SERIF'], ['Helvetica', 'SANS-SERIF']],
            'Times, Georgia có chân chữ; Arial, Helvetica không có chân chữ.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: QUAN TRỌNG NHẤT / CHI TIẾT trong poster.',
            [['Tiêu đề', 'QUAN TRỌNG NHẤT'], ['Hình ảnh chính', 'QUAN TRỌNG NHẤT'], ['Địa chỉ', 'CHI TIẾT'], ['Giá vé', 'CHI TIẾT']],
            'Tiêu đề và hình ảnh chính quan trọng nhất; địa chỉ, giá vé là chi tiết.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Poster cần thứ bậc thông tin', 'ĐÚNG'], ['Lưới giúp căn chỉnh bố cục', 'ĐÚNG'], ['Nên dùng 5 font trong một poster', 'SAI'], ['Tiêu đề nên nhỏ nhất', 'SAI']],
            'Poster chỉ nên dùng 1–2 font và tiêu đề phải nổi bật nhất.', $d);
        $this->sortQ($s, 'Xếp các hành động vào nhóm: NÊN LÀM / KHÔNG NÊN khi thiết kế poster.',
            [['Tạo điểm nhấn rõ ràng', 'NÊN LÀM'], ['Dùng 1–2 font chữ', 'NÊN LÀM'], ['Nhồi nhét quá nhiều chữ', 'KHÔNG NÊN'], ['Màu sắc lộn xộn', 'KHÔNG NÊN']],
            'Poster tốt cần điểm nhấn rõ, ít font và màu sắc hài hòa.', $d);
        $this->sortQ($s, 'Xếp các văn bản vào nhóm font PHÙ HỢP: SERIF / SANS-SERIF.',
            [['Sách văn học', 'SERIF'], ['Báo in truyền thống', 'SERIF'], ['Ứng dụng điện thoại', 'SANS-SERIF'], ['Website hiện đại', 'SANS-SERIF']],
            'Văn bản truyền thống hợp serif; giao diện hiện đại hợp sans-serif.', $d);
        $this->fill($s, 'Nghệ thuật sắp xếp và trình bày chữ trong thiết kế gọi là ___.', [[0, 'typography']],
            'Typography là nghệ thuật sắp xếp và trình bày chữ trong thiết kế.', $d);
        $this->fill($s, 'Font không có chân chữ, mang phong cách hiện đại gọi là ___-serif.', [[0, 'sans']],
            'Font sans-serif không có chân chữ, phù hợp phong cách hiện đại.', $d);
        $this->fill($s, 'Hệ thống đường kẻ giúp căn chỉnh các yếu tố gọn gàng gọi là ___.', [[0, 'lưới']],
            'Lưới bố cục là hệ thống đường kẻ giúp căn chỉnh các yếu tố gọn gàng.', $d);
        $this->fill($s, 'Dòng chữ kêu gọi người xem hành động trong poster gọi là lời kêu gọi ___ động.', [[0, 'hành']],
            'Lời kêu gọi hành động thúc đẩy người xem thực hiện hành động mong muốn.', $d);
        $this->fill($s, 'Khoảng cách giữa các dòng chữ ảnh hưởng đến độ dễ ___ của văn bản.', [[0, 'đọc']],
            'Khoảng cách dòng hợp lý giúp văn bản dễ đọc hơn.', $d);
    }

    // ===== 29. Các phong cách kiến trúc tiêu biểu (lớp 12, trung bình) =====
    private function seedMtThpt12Lop123(): void
    {
        $s = 'my-thuat-thpt-12-lop-12-3'; $d = 'trung_binh';
        $this->quiz($s, 'Thức cột Doric có đặc điểm gì?',
            ['Đơn giản, chắc khỏe', 'Cầu kỳ, trang trí nhiều', 'Mảnh mai, thanh thoát', 'Không có cột'], 0,
            'Thức cột Doric đơn giản và chắc khỏe, là thức cột cổ nhất của Hy Lạp.', $d);
        $this->quiz($s, 'Nhà thờ Đức Bà Paris thuộc phong cách kiến trúc nào?',
            ['Gothic', 'Hiện đại', 'Cổ điển', 'Baroque'], 0,
            'Nhà thờ Đức Bà Paris với vòm nhọn và cửa sổ kính màu thuộc phong cách Gothic.', $d);
        $this->quiz($s, 'Kiến trúc hiện đại thường dùng vật liệu nào?',
            ['Bê tông, thép, kính', 'Rơm, tre, lá', 'Đá nguyên khối', 'Vỏ sò'], 0,
            'Kiến trúc hiện đại ưa dùng bê tông, thép và kính với hình khối đơn giản.', $d);
        $this->quiz($s, 'Mái cong, đầu đao là đặc điểm của kiến trúc nào?',
            ['Truyền thống Việt Nam', 'Gothic', 'Hiện đại', 'La Mã'], 0,
            'Mái cong vút với đầu đao là đặc điểm nổi bật của kiến trúc truyền thống Việt Nam.', $d);
        $this->quiz($s, 'Phong cách Bauhaus đề cao điều gì?',
            ['Công năng và sự đơn giản', 'Trang trí cầu kỳ', 'Mái vòm lớn', 'Tượng điêu khắc'], 0,
            'Bauhaus đề cao công năng sử dụng và sự đơn giản trong thiết kế.', $d);
        $this->matching($s, 'Nối mỗi phong cách với đặc điểm của nó.',
            [['Cổ điển', 'Cột và thức cột'], ['Gothic', 'Vòm nhọn'], ['Hiện đại', 'Công năng'], ['Bauhaus', 'Đơn giản']],
            'Mỗi phong cách kiến trúc có đặc điểm nhận diện riêng.', $d);
        $this->matching($s, 'Nối mỗi công trình với phong cách của nó.',
            [['Đền Parthenon', 'Cổ điển'], ['Nhà thờ Đức Bà', 'Gothic'], ['Biệt thự hiện đại', 'Hiện đại'], ['Chùa Một Cột', 'Truyền thống VN']],
            'Mỗi công trình tiêu biểu đại diện cho một phong cách kiến trúc.', $d);
        $this->matching($s, 'Nối mỗi vật liệu với phong cách thường dùng nó.',
            [['Đá cẩm thạch', 'Cổ điển'], ['Kính màu', 'Gothic'], ['Bê tông', 'Hiện đại'], ['Gỗ – ngói', 'Truyền thống VN']],
            'Vật liệu đặc trưng góp phần tạo nên phong cách kiến trúc.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.',
            [['Thức cột', 'Hệ cột cổ điển'], ['Vòm nhọn', 'Đặc trưng Gothic'], ['Mặt đứng', 'Mặt ngoài công trình'], ['Không gian', 'Bên trong công trình']],
            'Hiểu khái niệm giúp đọc và phân tích công trình kiến trúc.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với phong cách của nó.',
            [['Mái vòm', 'La Mã'], ['Cửa sổ kính màu', 'Gothic'], ['Mái bằng', 'Hiện đại'], ['Sân vườn', 'Truyền thống VN']],
            'Mỗi phong cách có những đặc điểm kiến trúc không thể nhầm lẫn.', $d);
        $this->sortQ($s, 'Xếp các đặc điểm vào đúng phong cách: CỔ ĐIỂN / GOTHIC / HIỆN ĐẠI / TRUYỀN THỐNG VN.',
            [['Hệ cột', 'CỔ ĐIỂN'], ['Vòm nhọn', 'GOTHIC'], ['Bê tông kính', 'HIỆN ĐẠI'], ['Mái cong', 'TRUYỀN THỐNG VN']],
            'Hệ cột thuộc cổ điển, vòm nhọn thuộc Gothic, bê tông kính thuộc hiện đại, mái cong thuộc VN.', $d);
        $this->sortQ($s, 'Xếp các công trình vào đúng phong cách của nó.',
            [['Đền Parthenon', 'CỔ ĐIỂN'], ['Nhà thờ Đức Bà', 'GOTHIC'], ['Nhà ống hiện đại', 'HIỆN ĐẠI'], ['Đình làng', 'TRUYỀN THỐNG VN']],
            'Mỗi công trình thuộc về một phong cách kiến trúc nhất định.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Gothic có vòm nhọn', 'ĐÚNG'], ['Hiện đại đề cao công năng', 'ĐÚNG'], ['Cổ điển dùng bê tông cốt thép', 'SAI'], ['Đình làng có mái bằng', 'SAI']],
            'Cổ điển dùng đá; đình làng truyền thống có mái cong, đầu đao.', $d);
        $this->sortQ($s, 'Xếp các vật liệu vào nhóm: TỰ NHIÊN TRUYỀN THỐNG / CÔNG NGHIỆP HIỆN ĐẠI.',
            [['Gỗ', 'TỰ NHIÊN TRUYỀN THỐNG'], ['Ngói', 'TỰ NHIÊN TRUYỀN THỐNG'], ['Thép', 'CÔNG NGHIỆP HIỆN ĐẠI'], ['Kính', 'CÔNG NGHIỆP HIỆN ĐẠI']],
            'Gỗ, ngói là vật liệu truyền thống; thép, kính là vật liệu công nghiệp hiện đại.', $d);
        $this->sortQ($s, 'Xếp các yếu tố vào nhóm: TRANG TRÍ / KẾT CẤU.',
            [['Hoa văn', 'TRANG TRÍ'], ['Phù điêu', 'TRANG TRÍ'], ['Cột chịu lực', 'KẾT CẤU'], ['Móng nhà', 'KẾT CẤU']],
            'Hoa văn, phù điêu để trang trí; cột, móng thuộc kết cấu chịu lực.', $d);
        $this->fill($s, 'Hệ thống cột và đầu cột trong kiến trúc cổ điển gọi là thức ___.', [[0, 'cột']],
            'Thức cột là hệ thống cột và đầu cột đặc trưng của kiến trúc cổ điển.', $d);
        $this->fill($s, 'Kiến trúc Gothic nổi bật với vòm ___ vút cao.', [[0, 'nhọn']],
            'Vòm nhọn vút cao là đặc trưng nổi bật của kiến trúc Gothic.', $d);
        $this->fill($s, 'Phong cách Bauhaus đề cao công năng và sự đơn ___.', [[0, 'giản']],
            'Bauhaus đề cao công năng sử dụng và sự đơn giản trong thiết kế.', $d);
        $this->fill($s, 'Nhà ở truyền thống Việt Nam thường có sân ___ thoáng mát.', [[0, 'vườn']],
            'Sân vườn thoáng mát là nét đẹp của nhà ở truyền thống Việt Nam.', $d);
        $this->fill($s, 'Kiến trúc hiện đại ưu tiên ___ năng sử dụng của công trình.', [[0, 'công']],
            'Kiến trúc hiện đại ưu tiên công năng sử dụng của công trình.', $d);
    }

    // ===== 30. Điêu khắc: hình thức và chất liệu (lớp 12, khó) =====
    private function seedMtThpt12Lop124(): void
    {
        $s = 'my-thuat-thpt-12-lop-12-4'; $d = 'kho';
        $this->quiz($s, 'Điêu khắc phù điêu cao có đặc điểm gì?',
            ['Nổi cao khỏi mặt nền', 'Chìm sâu vào mặt nền', 'Đứng độc lập', 'Chỉ vẽ trên giấy'], 0,
            'Phù điêu cao là hình chạm nổi cao khỏi mặt nền.', $d);
        $this->quiz($s, 'Chất liệu đồng trong điêu khắc có ưu điểm gì?',
            ['Bền, đúc được chi tiết tinh xảo', 'Rẻ tiền', 'Nhẹ như giấy', 'Dễ vỡ'], 0,
            'Đồng bền và đúc được những chi tiết tinh xảo trong điêu khắc.', $d);
        $this->quiz($s, 'Tượng chân dung nhằm mục đích gì?',
            ['Ghi lại hình ảnh con người', 'Trang trí nhà cửa', 'Làm đồ chơi', 'Xây dựng nhà'], 0,
            'Tượng chân dung nhằm ghi lại hình ảnh của con người cụ thể.', $d);
        $this->quiz($s, 'Điêu khắc sắp đặt (installation) là gì?',
            ['Tác phẩm tương tác với không gian', 'Tượng nhỏ để bàn', 'Phù điêu trên tường', 'Tượng trong bảo tàng'], 0,
            'Điêu khắc sắp đặt là tác phẩm tương tác trực tiếp với không gian trưng bày.', $d);
        $this->quiz($s, 'Quy trình đúc đồng tượng gồm các bước nào?',
            ['Tạo mẫu – làm khuôn – đúc – hoàn thiện', 'Vẽ phác thảo là xong', 'Chỉ cần nung nóng', 'Đổ nước vào khuôn'], 0,
            'Đúc đồng gồm các bước: tạo mẫu, làm khuôn, đúc và hoàn thiện.', $d);
        $this->matching($s, 'Nối mỗi hình thức điêu khắc với đặc điểm của nó.',
            [['Điêu khắc tròn', 'Xem được mọi phía'], ['Phù điêu', 'Nổi trên mặt phẳng'], ['Tượng đài', 'Quy mô lớn'], ['Tượng nhỏ', 'Để trang trí']],
            'Điêu khắc có nhiều hình thức: tượng tròn, phù điêu, tượng đài, tượng nhỏ.', $d);
        $this->matching($s, 'Nối mỗi chất liệu với đặc tính của nó.',
            [['Đá', 'Cứng – bền'], ['Gỗ', 'Ấm – dễ chạm'], ['Đồng', 'Đúc tinh xảo'], ['Thạch cao', 'Làm mẫu']],
            'Mỗi chất liệu điêu khắc có đặc tính riêng phù hợp với từng mục đích.', $d);
        $this->matching($s, 'Nối mỗi công đoạn với mô tả của nó.',
            [['Tạo mẫu', 'Định hình tác phẩm'], ['Làm khuôn', 'Tạo khuôn đúc'], ['Đúc', 'Đổ vật liệu'], ['Hoàn thiện', 'Đánh bóng']],
            'Làm tượng trải qua các công đoạn: tạo mẫu, làm khuôn, đúc, hoàn thiện.', $d);
        $this->matching($s, 'Nối mỗi vị trí đặt tượng với mục đích của nó.',
            [['Quảng trường', 'Tôn vinh'], ['Bảo tàng', 'Lưu giữ'], ['Công viên', 'Thưởng ngoạn'], ['Trường học', 'Giáo dục']],
            'Vị trí đặt tượng quyết định mục đích: tôn vinh, lưu giữ hay giáo dục.', $d);
        $this->matching($s, 'Nối mỗi loại tượng với ví dụ của nó.',
            [['Tượng đài', 'Tượng anh hùng'], ['Tượng chân dung', 'Tượng danh nhân'], ['Phù điêu', 'Chạm tường đình'], ['Tượng trang trí', 'Tượng vườn']],
            'Mỗi loại tượng có ví dụ tiêu biểu trong đời sống.', $d);
        $this->sortQ($s, 'Xếp các mô tả vào nhóm: ĐIÊU KHẮC TRÒN / PHÙ ĐIÊU.',
            [['Xem được mọi phía', 'ĐIÊU KHẮC TRÒN'], ['Đứng độc lập', 'ĐIÊU KHẮC TRÒN'], ['Nổi trên mặt phẳng', 'PHÙ ĐIÊU'], ['Gắn vào tường', 'PHÙ ĐIÊU']],
            'Điêu khắc tròn xem được mọi phía; phù điêu nổi trên mặt phẳng.', $d);
        $this->sortQ($s, 'Xếp các chất liệu vào nhóm: TRUYỀN THỐNG / HIỆN ĐẠI.',
            [['Đá', 'TRUYỀN THỐNG'], ['Gỗ', 'TRUYỀN THỐNG'], ['Nhựa composite', 'HIỆN ĐẠI'], ['Inox', 'HIỆN ĐẠI']],
            'Đá, gỗ là chất liệu truyền thống; composite, inox là chất liệu hiện đại.', $d);
        $this->sortQ($s, 'Xếp các phát biểu vào nhóm ĐÚNG / SAI.',
            [['Phù điêu nổi trên mặt phẳng', 'ĐÚNG'], ['Đồng đúc được chi tiết tinh xảo', 'ĐÚNG'], ['Điêu khắc tròn chỉ xem một mặt', 'SAI'], ['Tượng đài thường nhỏ bé', 'SAI']],
            'Điêu khắc tròn xem được mọi phía; tượng đài có quy mô lớn.', $d);
        $this->sortQ($s, 'Xếp các yêu cầu vào nhóm: TƯỢNG ĐÀI / TƯỢNG TRANG TRÍ.',
            [['Quy mô lớn', 'TƯỢNG ĐÀI'], ['Tôn vinh lịch sử', 'TƯỢNG ĐÀI'], ['Nhỏ xinh', 'TƯỢNG TRANG TRÍ'], ['Đặt trong nhà', 'TƯỢNG TRANG TRÍ']],
            'Tượng đài lớn để tôn vinh; tượng trang trí nhỏ xinh đặt trong nhà.', $d);
        $this->sortQ($s, 'Xếp các công đoạn đúc đồng vào nhóm: LÀM TRƯỚC / LÀM SAU.',
            [['Tạo mẫu đất', 'LÀM TRƯỚC'], ['Làm khuôn đúc', 'LÀM TRƯỚC'], ['Đổ đồng nóng chảy', 'LÀM SAU'], ['Đánh bóng', 'LÀM SAU']],
            'Tạo mẫu và làm khuôn trước, đổ đồng và đánh bóng sau.', $d);
        $this->fill($s, 'Tác phẩm điêu khắc có thể đi vòng quanh xem mọi phía gọi là điêu khắc ___.', [[0, 'tròn']],
            'Điêu khắc tròn là tượng độc lập, xem được từ mọi phía.', $d);
        $this->fill($s, 'Nghệ thuật chạm nổi hình trên bề mặt phẳng gọi là ___ điêu.', [[0, 'phù']],
            'Phù điêu là nghệ thuật chạm nổi hình trên bề mặt phẳng.', $d);
        $this->fill($s, 'Ngoài đá và gỗ, chất liệu ___ cũng được dùng nhiều trong điêu khắc truyền thống.', [[0, 'đồng']],
            'Đồng là chất liệu truyền thống quan trọng, đúc được chi tiết tinh xảo.', $d);
        $this->fill($s, 'Tượng lớn đặt nơi công cộng để tưởng nhớ lịch sử gọi là tượng ___.', [[0, 'đài']],
            'Tượng đài là tượng lớn nơi công cộng để tôn vinh, tưởng nhớ lịch sử.', $d);
        $this->fill($s, 'Công đoạn cuối cùng khi đúc tượng đồng là ___ thiện bề mặt.', [[0, 'hoàn']],
            'Hoàn thiện bề mặt bằng đánh bóng là công đoạn cuối khi đúc tượng đồng.', $d);
    }

    public function run(): void
    {
        $this->seedMtMauNongMauLanh();
        $this->seedMtPhaMauCoBan();
        $this->seedMtDuongNetHinhKhoi();
        $this->seedMtBoCucTranh();
        $this->seedMtTranhDongHoNoiTieng();
        $this->seedMtYTuongTranhDanGian();
        $this->seedMtMauSacLop61();
        $this->seedMtMauSacLop62();
        $this->seedMtMauSacLop71();
        $this->seedMtMauSacLop72();
        $this->seedMtDuongNetLop71();
        $this->seedMtDuongNetLop72();
        $this->seedMtDuongNetLop81();
        $this->seedMtDuongNetLop82();
        $this->seedMtTranhDanGianLop81();
        $this->seedMtTranhDanGianLop82();
        $this->seedMtTranhDanGianLop91();
        $this->seedMtTranhDanGianLop92();
        $this->seedMtThpt10Lop101();
        $this->seedMtThpt10Lop102();
        $this->seedMtThpt10Lop103();
        $this->seedMtThpt10Lop104();
        $this->seedMtThpt11Lop111();
        $this->seedMtThpt11Lop112();
        $this->seedMtThpt11Lop113();
        $this->seedMtThpt11Lop114();
        $this->seedMtThpt12Lop121();
        $this->seedMtThpt12Lop122();
        $this->seedMtThpt12Lop123();
        $this->seedMtThpt12Lop124();
        $this->seedExtraMatching();
    }

    // ===== Bổ sung matching bị trùng prompt (31 câu) =====
    private function seedExtraMatching(): void
    {
        $tb = 'trung_binh'; $k = 'kho';
        $s = 'my-thuat-thpt-10-lop-10-1';
        $this->matching($s, 'Ghép mỗi chất liệu với đặc trưng của nó trong mỹ thuật phương Đông.',
            [['Mực tàu', 'Đen sâu'], ['Giấy xuyến', 'Thấm mực'], ['Lụa', 'Mịn màng'], ['Ván gỗ', 'Khắc in']],
            'Mỗi chất liệu phương Đông có đặc trưng riêng: mực tàu đen sâu, giấy xuyến thấm mực.', $tb);
        $this->matching($s, 'Ghép mỗi đề tài với dòng tranh thường thể hiện nó.',
            [['Sơn thủy', 'Thủy mặc'], ['Mỹ nhân', 'Ukiyo-e'], ['Hoa điểu', 'Thủy mặc'], ['Phong cảnh', 'Ukiyo-e']],
            'Sơn thủy, hoa điểu thuộc thủy mặc Trung Hoa; mỹ nhân, phong cảnh thuộc ukiyo-e Nhật Bản.', $tb);
        $s = 'my-thuat-thpt-10-lop-10-2';
        $this->matching($s, 'Ghép mỗi chất liệu hội họa Việt Nam với đặc trưng của nó.',
            [['Sơn mài', 'Bóng sâu'], ['Lụa', 'Trong trẻo'], ['Giấy điệp', 'Óng ánh'], ['Sơn dầu', 'Bền màu']],
            'Sơn mài bóng sâu, lụa trong trẻo, giấy điệp óng ánh là đặc trưng hội họa Việt Nam.', $tb);
        $this->matching($s, 'Ghép mỗi vùng miền với dòng tranh dân gian tiêu biểu.',
            [['Bắc Ninh', 'Đông Hồ'], ['Hà Nội', 'Hàng Trống'], ['Huế', 'Làng Sình'], ['Nam Bộ', 'Tranh kính']],
            'Mỗi vùng miền có dòng tranh dân gian tiêu biểu riêng.', $tb);
        $s = 'my-thuat-thpt-10-lop-10-3';
        $this->matching($s, 'Ghép mỗi công trình với nền văn minh tạo ra nó.',
            [['Kim tự tháp', 'Ai Cập'], ['Đền Parthenon', 'Hy Lạp'], ['Đấu trường Cô-lô-xê', 'La Mã'], ['Tượng nhân sư', 'Ai Cập']],
            'Kim tự tháp, nhân sư thuộc Ai Cập; Parthenon thuộc Hy Lạp; Cô-lô-xê thuộc La Mã.', $tb);
        $this->matching($s, 'Ghép mỗi vị thần với nền văn minh tôn thờ.',
            [['Thần Ra', 'Ai Cập'], ['Thần Zeus', 'Hy Lạp'], ['Thần Jupiter', 'La Mã'], ['Nữ thần Isis', 'Ai Cập']],
            'Mỗi nền văn minh cổ đại có hệ thống thần linh được tôn thờ riêng.', $tb);
        $this->matching($s, 'Ghép mỗi đặc điểm tạo hình với nền mỹ thuật của nó.',
            [['Mắt vẽ chính diện', 'Ai Cập'], ['Cơ thể cân đối', 'Hy Lạp'], ['Chân dung chân thực', 'La Mã'], ['Hình khối đồ sộ', 'Ai Cập']],
            'Ai Cập vẽ theo luật frontal, Hy Lạp tôn vinh cơ thể, La Mã chân dung chân thực.', $tb);
        $s = 'my-thuat-thpt-10-lop-10-4';
        $this->matching($s, 'Ghép mỗi tác phẩm (theo mô tả) với trào lưu của nó.',
            [['Bữa tiệc cuối cùng', 'Phục hưng'], ['Hoa súng', 'Ấn tượng'], ['Tranh phân mảnh', 'Lập thể'], ['Đồng hồ chảy', 'Siêu thực']],
            'Mỗi tác phẩm tiêu biểu gắn với một trào lưu mỹ thuật nhất định.', $k);
        $this->matching($s, 'Ghép mỗi đặc điểm với trào lưu tương ứng.',
            [['Giải phẫu chính xác', 'Phục hưng'], ['Chấm màu tách', 'Ấn tượng'], ['Hình học phân mảnh', 'Lập thể'], ['Thế giới giấc mơ', 'Siêu thực']],
            'Đặc điểm kỹ thuật và tinh thần giúp nhận diện các trào lưu mỹ thuật.', $k);
        $s = 'my-thuat-thpt-11-lop-11-1';
        $this->matching($s, 'Ghép mỗi nguyên lý thiết kế với ví dụ minh họa.',
            [['Cân bằng', 'Bố cục đều'], ['Tương phản', 'Đen trên trắng'], ['Điểm nhấn', 'Tiêu đề lớn'], ['Nhịp điệu', 'Họa tiết lặp']],
            'Mỗi nguyên lý thiết kế đều có ví dụ minh họa cụ thể.', $tb);
        $this->matching($s, 'Ghép mỗi cặp yếu tố với mức độ tương phản của nó.',
            [['Đen – trắng', 'Rất mạnh'], ['Đỏ – xanh lá', 'Mạnh'], ['Xám – xám nhạt', 'Yếu'], ['Vàng – cam', 'Yếu']],
            'Cặp đối lập mạnh cho tương phản mạnh, cặp gần nhau cho tương phản yếu.', $tb);
        $s = 'my-thuat-thpt-11-lop-11-2';
        $this->matching($s, 'Ghép mỗi nguyên lý với lợi ích của nó.',
            [['Nhịp điệu', 'Sinh động'], ['Tỷ lệ vàng', 'Hài hòa'], ['Thống nhất', 'Chuyên nghiệp'], ['Khoảng trắng', 'Dễ đọc']],
            'Áp dụng đúng nguyên lý mang lại lợi ích rõ rệt cho thiết kế.', $tb);
        $this->matching($s, 'Ghép mỗi thuật ngữ với ý nghĩa của nó.',
            [['Leading', 'Giãn dòng'], ['Kerning', 'Giãn chữ'], ['Grid', 'Lưới bố cục'], ['Hierarchy', 'Thứ bậc']],
            'Các thuật ngữ thiết kế mô tả cách tổ chức chữ và bố cục.', $tb);
        $s = 'my-thuat-thpt-11-lop-11-3';
        $this->matching($s, 'Ghép mỗi màu với bậc của nó trên vòng tròn màu.',
            [['Đỏ', 'Bậc 1'], ['Cam', 'Bậc 2'], ['Vàng cam', 'Bậc 3'], ['Lam', 'Bậc 1']],
            'Đỏ, lam bậc 1; cam bậc 2; vàng cam bậc 3 trên vòng tròn màu.', $tb);
        $this->matching($s, 'Ghép mỗi cặp màu với tên quan hệ của chúng.',
            [['Đỏ – lục', 'Bổ túc'], ['Tím – vàng', 'Bổ túc'], ['Vàng – cam', 'Tương đồng'], ['Lam – chàm', 'Tương đồng']],
            'Cặp đối diện là bổ túc, cặp kề nhau là tương đồng.', $tb);
        $this->matching($s, 'Ghép mỗi nhóm màu với ví dụ của nó.',
            [['Màu nóng', 'Đỏ cam'], ['Màu lạnh', 'Lam tím'], ['Trung tính', 'Xám'], ['Pastel', 'Hồng phấn']],
            'Mỗi nhóm màu có những màu đại diện đặc trưng.', $tb);
        $s = 'my-thuat-thpt-11-lop-11-4';
        $this->matching($s, 'Ghép mỗi công thức phối màu với ví dụ của nó.',
            [['Đơn sắc', 'Xanh nhiều sắc độ'], ['Bổ túc', 'Đỏ – xanh lá'], ['Tương đồng', 'Vàng cam đỏ'], ['Tam giác', 'Đỏ vàng lam']],
            'Mỗi công thức phối màu có ví dụ cụ thể dễ nhận biết.', $k);
        $this->matching($s, 'Ghép mỗi màu với ngành nghề thường dùng nó.',
            [['Xanh dương', 'Ngân hàng'], ['Đỏ', 'Ẩm thực'], ['Xanh lá', 'Môi trường'], ['Tím', 'Sáng tạo']],
            'Màu sắc được chọn hợp với cảm xúc của từng ngành nghề.', $k);
        $s = 'my-thuat-thpt-12-lop-12-1';
        $this->matching($s, 'Ghép mỗi yếu tố với ví dụ trong hệ nhận diện.',
            [['Logo', 'Biểu tượng'], ['Màu sắc', 'Màu chủ đạo'], ['Font chữ', 'Chữ thương hiệu'], ['Slogan', 'Câu khẩu hiệu']],
            'Hệ nhận diện gồm logo, màu sắc, font chữ và slogan đồng bộ.', $tb);
        $this->matching($s, 'Ghép mỗi loại logo với đặc điểm nhận biết.',
            [['Logo chữ', 'Chỉ có chữ'], ['Biểu tượng', 'Chỉ có hình'], ['Kết hợp', 'Chữ và hình'], ['Huy hiệu', 'Nằm trong khung']],
            'Nhận biết loại logo qua hình thức thể hiện của nó.', $tb);
        $s = 'my-thuat-thpt-12-lop-12-2';
        $this->matching($s, 'Ghép mỗi thành phần với mức độ ưu tiên trong poster.',
            [['Tiêu đề', 'Cao nhất'], ['Hình ảnh', 'Cao'], ['Lời kêu gọi', 'Cao'], ['Chi tiết', 'Thấp']],
            'Tiêu đề ưu tiên cao nhất, chi tiết nhỏ ưu tiên thấp trong poster.', $tb);
        $this->matching($s, 'Ghép mỗi loại font với ví dụ của nó.',
            [['Serif', 'Times'], ['Sans-serif', 'Arial'], ['Viết tay', 'Script'], ['Trang trí', 'Display']],
            'Mỗi loại font có những ví dụ tiêu biểu quen thuộc.', $tb);
        $s = 'my-thuat-thpt-12-lop-12-3';
        $this->matching($s, 'Ghép mỗi phong cách với công trình tiêu biểu.',
            [['Cổ điển', 'Đền Parthenon'], ['Gothic', 'Nhà thờ Đức Bà'], ['Hiện đại', 'Nhà kính'], ['Truyền thống VN', 'Chùa Một Cột']],
            'Mỗi phong cách kiến trúc có công trình tiêu biểu đại diện.', $tb);
        $this->matching($s, 'Ghép mỗi chi tiết với phong cách của nó.',
            [['Thức cột', 'Cổ điển'], ['Vòm nhọn', 'Gothic'], ['Mặt kính', 'Hiện đại'], ['Đầu đao', 'Truyền thống VN']],
            'Chi tiết kiến trúc là dấu hiệu nhận biết phong cách.', $tb);
        $s = 'my-thuat-thpt-12-lop-12-4';
        $this->matching($s, 'Ghép mỗi hình thức điêu khắc với ví dụ của nó.',
            [['Tượng tròn', 'Tượng danh nhân'], ['Phù điêu', 'Chạm tường đình'], ['Tượng đài', 'Tượng quảng trường'], ['Tượng nhỏ', 'Tượng để bàn']],
            'Mỗi hình thức điêu khắc có ví dụ tiêu biểu trong đời sống.', $k);
        $this->matching($s, 'Ghép mỗi chất liệu với công dụng của nó.',
            [['Đá', 'Tượng ngoài trời'], ['Gỗ', 'Chạm khắc'], ['Đồng', 'Đúc tượng'], ['Thạch cao', 'Làm mẫu']],
            'Mỗi chất liệu điêu khắc phù hợp với một công dụng riêng.', $k);
        $s = 'mt-tranh-dong-ho-noi-tieng';
        $this->matching($s, 'Ghép mỗi bức tranh Đông Hồ với lời chúc của nó.',
            [['Vinh hoa', 'Vinh hiển'], ['Phú quý', 'Giàu sang'], ['Đàn gà mẹ con', 'Sum vầy'], ['Đàn lợn', 'No đủ']],
            'Mỗi bức tranh Đông Hồ gửi gắm một lời chúc tốt đẹp.', $tb);
        $this->sortQ($s, 'Xếp các tác phẩm vào nhóm: TRANH IN VÁN GỖ / TRANH VẼ TAY.',
            [['Đám cưới chuột', 'TRANH IN VÁN GỖ'], ['Vinh hoa', 'TRANH IN VÁN GỖ'], ['Tố nữ (tô tay)', 'TRANH VẼ TAY'], ['Tranh lụa', 'TRANH VẼ TAY']],
            'Tranh Đông Hồ in bằng ván gỗ; tranh Hàng Trống tô tay, tranh lụa vẽ tay.', $tb);
        $s = 'mt-y-tuong-tranh-dan-gian';
        $this->matching($s, 'Ghép mỗi giai đoạn sáng tác với việc chính của nó.',
            [['Lên ý tưởng', 'Chọn đề tài'], ['Phác thảo', 'Dựng bố cục'], ['Vẽ nét', 'Hoàn thiện hình'], ['Tô màu', 'Hoàn thiện tranh']],
            'Sáng tác tranh trải qua các giai đoạn: ý tưởng, phác thảo, vẽ nét, tô màu.', $k);
        $s = 'mt-tranh-dan-gian-lop-8-1';
        $this->matching($s, 'Ghép mỗi nguyên liệu tự nhiên với màu nó tạo ra.',
            [['Hoa hòe', 'Màu vàng'], ['Sỏi son', 'Màu đỏ'], ['Lá chàm', 'Màu xanh'], ['Vỏ điệp', 'Màu trắng']],
            'Nguyên liệu tự nhiên tạo nên bảng màu đặc trưng của tranh Đông Hồ.', $tb);
        $s = 'mt-tranh-dan-gian-lop-8-2';
        $this->matching($s, 'Ghép mỗi dòng tranh với nét riêng của nó.',
            [['Đông Hồ', 'Giấy điệp'], ['Hàng Trống', 'Tô màu tay'], ['Kim Hoàng', 'Giấy hồng điều'], ['Làng Sình', 'In mộc bản']],
            'Mỗi dòng tranh dân gian có nét riêng về chất liệu và kỹ thuật.', $tb);
        $s = 'mt-tranh-dan-gian-lop-9-1';
        $this->matching($s, 'Ghép mỗi miền với dòng tranh dân gian của nó.',
            [['Miền Bắc', 'Đông Hồ'], ['Miền Trung', 'Làng Sình'], ['Miền Nam', 'Tranh kính'], ['Thủ đô', 'Hàng Trống']],
            'Tranh dân gian có mặt ở cả ba miền Bắc, Trung, Nam.', $tb);
    }
}
