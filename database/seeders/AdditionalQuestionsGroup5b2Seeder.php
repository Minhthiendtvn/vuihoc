<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Bổ sung câu hỏi cho 15 bài Âm nhạc HIỆN CÓ (nhóm G5, phần b2 - nửa sau).
 *
 * Mỗi bài: 5 câu MỚI cho mỗi kiểu chơi (quiz/matching/sort/fill).
 * Nội dung tiếng Việt tự viết 100%, bám topic + khối lớp + độ khó của bài,
 * không trùng prompt đã có.
 * Idempotent: chạy lại không thêm câu mới (giới hạn 8 câu/kiểu/bài + kiểm tra prompt trùng).
 */
class AdditionalQuestionsGroup5b2Seeder extends Seeder
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
        $this->seedNhacCuDanTocLop82();
        $this->seedNgheThuatTruyenThongLop91();
        $this->seedNhacCuTayVaDanNhacLop92();
        $this->seedHopAmBaLop101();
        $this->seedVongHopAmLop102();
        $this->seedKhoaNhacNhipLop103();
        $this->seedDauHoaNhipPhucLop104();
        $this->seedNhacCachMangLop111();
        $this->seedNhacTreDanGianLop112();
        $this->seedNhacSiLop113();
        $this->seedTheHeNhacSiLop114();
        $this->seedJazzBluesLop121();
        $this->seedRockPopHipHopLop122();
        $this->seedGiaoHuongConcertoLop123();
        $this->seedOperaNhacKichLop124();
        $this->seedBoSungMatching();
    }

    /**
     * Bù 6 câu matching bị guard prompt-trùng bỏ qua ở lần seed đầu
     * (prompt mới hoàn toàn, không trùng prompt cũ).
     */
    private function seedBoSungMatching(): void
    {
        $this->matching('am-nhac-thpt-10-lop-10-4', 'Nối mỗi dấu hóa kép với hiệu quả của nó.', [['Dấu thăng kép', 'Nâng lên 2 nửa cung'], ['Dấu giáng kép', 'Hạ xuống 2 nửa cung'], ['Dấu thăng đơn', 'Nâng lên 1 nửa cung'], ['Dấu bình', 'Trả về cao độ tự nhiên']], 'Dấu hóa kép tác động gấp đôi dấu hóa đơn: thăng kép nâng một cung, giáng kép hạ một cung.', 'kho');
        $this->matching('am-nhac-thpt-11-lop-11-3', 'Nối mỗi sản phẩm âm nhạc với đặc điểm của nó.', [['Đĩa đơn (single)', '1–2 ca khúc'], ['Album', 'Tuyển tập nhiều ca khúc'], ['MV', 'Video ca nhạc'], ['EP', 'Tuyển tập ngắn 4–6 ca khúc']], 'Nghệ sĩ phát hành âm nhạc dưới nhiều định dạng khác nhau.', 'trung_binh');
        $this->matching('am-nhac-thpt-11-lop-11-3', 'Nối mỗi nguồn thu nhập với người nhận trong ngành nhạc.', [['Tiền bản quyền', 'Nhạc sĩ sáng tác'], ['Cát-xê biểu diễn', 'Ca sĩ'], ['Tiền bán vé', 'Nhà tổ chức'], ['Hợp đồng quảng cáo', 'Nghệ sĩ nổi tiếng']], 'Mỗi vai trò trong ngành công nghiệp âm nhạc có nguồn thu nhập tương ứng.', 'trung_binh');
        $this->matching('am-nhac-thpt-11-lop-11-4', 'Nối mỗi thế hệ với công nghệ làm nhạc của thời đó.', [['Tiền chiến', 'Nhạc cụ acoustic, thu âm đơn giản'], ['Kháng chiến', 'Dàn nhạc, hợp xướng'], ['Sau 1975', 'Phòng thu băng từ'], ['Đương đại', 'Máy tính, phần mềm số']], 'Công nghệ ghi âm thay đổi qua từng thời kỳ, in dấu lên âm nhạc của mỗi thế hệ.', 'kho');
        $this->matching('am-nhac-thpt-12-lop-12-2', 'Nối mỗi thập niên với dòng nhạc bùng nổ của nó.', [['Thập niên 1950', 'Rock and roll'], ['Thập niên 1980', 'Pop, new wave'], ['Thập niên 1990', 'Hip-hop, grunge'], ['Thập niên 2010', 'EDM']], 'Mỗi thập niên có những dòng nhạc định hình văn hóa đại chúng của thời đó.', 'trung_binh');
        $this->matching('am-nhac-thpt-12-lop-12-4', 'Nối mỗi loại giọng hát opera với âm vực của nó.', [['Soprano', 'Giọng nữ cao'], ['Alto', 'Giọng nữ trầm'], ['Tenor', 'Giọng nam cao'], ['Bass', 'Giọng nam trầm']], 'Opera phân giọng hát thành 4 loại chính từ cao xuống trầm.', 'kho');
    }

    private function seedNhacCuDanTocLop82(): void
    {
        $s = 'an-nhac-cu-dan-toc-lop-8-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Đàn tranh thuộc nhóm nhạc cụ nào của Việt Nam?', ['Nhạc cụ hơi', 'Nhạc cụ dây', 'Nhạc cụ gõ', 'Nhạc cụ phím'], 1, 'Đàn tranh có 16 đến 19 dây, gảy bằng móng nên thuộc nhóm nhạc cụ dây.', $d);
        $this->quiz($s, 'Khèn là nhạc cụ đặc trưng của đồng bào dân tộc nào?', ['Kinh', 'Tày', 'Mông', 'Chăm'], 2, 'Khèn là nhạc cụ không thể thiếu trong đời sống và lễ hội của đồng bào Mông ở vùng Tây Bắc.', $d);
        $this->quiz($s, 'Sáo và tiêu khác nhau chủ yếu ở điểm nào?', ['Chất liệu làm nhạc cụ', 'Cách đặt nhạc cụ khi thổi', 'Số lỗ trên thân', 'Âm lượng phát ra'], 1, 'Sáo thổi ngang còn tiêu thổi dọc — tư thế thổi là điểm phân biệt rõ nhất giữa hai nhạc cụ.', $d);
        $this->quiz($s, 'Đàn bầu chỉ có một dây, vậy người chơi tạo ra các cao độ khác nhau bằng cách nào?', ['Thay dây liên tục', 'Điều chỉnh cần đàn và dùng kỹ thuật tay', 'Thổi mạnh hay thổi nhẹ', 'Gõ vào mặt đàn'], 1, 'Người chơi đàn bầu uốn cần đàn và chạm dây bằng nhiều kỹ thuật để tạo ra các cao độ khác nhau.', $d);
        $this->quiz($s, 'Trong dàn nhạc dân tộc, nhạc cụ nào thường giữ vai trò đánh dấu phách, nhịp?', ['Đàn tranh', 'Sáo', 'Trống chầu', 'Đàn nhị'], 2, 'Trống chầu giữ nhịp và đánh dấu các câu nhạc quan trọng trong dàn nhạc dân tộc.', $d);

        $this->matching($s, 'Nối mỗi nhạc cụ với số dây đặc trưng của nó.', [['Đàn tranh', '16-19 dây'], ['Đàn bầu', '1 dây'], ['Đàn nguyệt', '2 dây'], ['Đàn tỳ bà', '4 dây']], 'Số dây là một trong những dấu hiệu dễ nhận biết nhất của các nhạc cụ dây dân tộc.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với cách chơi đúng của nó.', [['Đàn nhị', 'Kéo bằng vĩ'], ['Đàn tam thập lục', 'Gõ bằng dùi tre'], ['Đàn bầu', 'Búng và rung dây'], ['Sáo', 'Thổi ngang']], 'Cách chơi quyết định âm sắc đặc trưng của từng nhạc cụ dân tộc.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ hơi với đặc điểm nhận biết.', [['Sáo', 'Thân trúc thổi ngang'], ['Tiêu', 'Thân trúc thổi dọc'], ['Khèn', 'Nhiều ống sậy của người Mông'], ['Tù và', 'Làm từ sừng trâu']], 'Mỗi nhạc cụ hơi có hình dáng và cách thổi riêng để nhận biết.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ gõ với bộ phận tạo ra âm thanh.', [['Trống chầu', 'Mặt trống căng da'], ['Cồng', 'Mặt kim loại có núm'], ['Chiêng', 'Mặt kim loại phẳng'], ['Phách', 'Hai thanh tre gõ vào nhau']], 'Chất liệu và hình dáng quyết định âm thanh riêng của từng nhạc cụ gõ.', $d);
        $this->matching($s, 'Nối mỗi tên gọi với nhạc cụ tương ứng.', [['Đàn gáo', 'Đàn hai dây, bầu làm bằng gáo dừa'], ['Đàn tính', 'Đàn 3 dây của người Tày'], ['Đàn đá', 'Nhạc cụ bằng đá, gõ vang'], ['Đàn T\'rưng', 'Nhạc cụ bằng tre của Tây Nguyên']], 'Tên gọi của nhiều nhạc cụ dân tộc gắn với chất liệu hoặc hình dáng của chúng.', $d);

        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm NHẠC CỤ DÂY hoặc NHẠC CỤ GÕ.', [['Đàn tranh', 'NHẠC CỤ DÂY'], ['Đàn nhị', 'NHẠC CỤ DÂY'], ['Trống cơm', 'NHẠC CỤ GÕ'], ['Thanh la', 'NHẠC CỤ GÕ'], ['Đàn bầu', 'NHẠC CỤ DÂY'], ['Mõ', 'NHẠC CỤ GÕ']], 'Nhạc cụ dây phát âm nhờ dây rung, nhạc cụ gõ phát âm nhờ gõ vào mặt hoặc thân.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Khèn thuộc nhóm nhạc cụ hơi', 'ĐÚNG'], ['Sáo Việt Nam thổi ngang', 'ĐÚNG'], ['Trống chầu thuộc nhóm nhạc cụ dây', 'SAI'], ['Phách được làm bằng kim loại', 'SAI']], 'Ghi nhớ nhóm nhạc cụ giúp phân loại đúng mọi nhạc cụ dân tộc.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm THỔI BẰNG HƠI hoặc GÕ BẰNG TAY/DÙI.', [['Khèn', 'THỔI BẰNG HƠI'], ['Tù và', 'THỔI BẰNG HƠI'], ['Trống cơm', 'GÕ BẰNG TAY/DÙI'], ['Thanh la', 'GÕ BẰNG TAY/DÙI'], ['Tiêu', 'THỔI BẰNG HƠI'], ['Mõ', 'GÕ BẰNG TAY/DÙI']], 'Cách tạo ra âm thanh là tiêu chí quan trọng nhất để phân nhóm nhạc cụ.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm CÓ DÂY hoặc KHÔNG CÓ DÂY.', [['Đàn gáo', 'CÓ DÂY'], ['Đàn tính', 'CÓ DÂY'], ['Đàn đá', 'KHÔNG CÓ DÂY'], ['Đàn T\'rưng', 'KHÔNG CÓ DÂY']], 'Đàn gáo, đàn tính có dây để gảy; đàn đá, đàn T\'rưng phát âm nhờ gõ.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm TÂY NGUYÊN hoặc MIỀN NÚI PHÍA BẮC.', [['Cồng chiêng', 'TÂY NGUYÊN'], ['Đàn T\'rưng', 'TÂY NGUYÊN'], ['Khèn', 'MIỀN NÚI PHÍA BẮC'], ['Đàn tính', 'MIỀN NÚI PHÍA BẮC']], 'Mỗi vùng miền có những nhạc cụ đặc trưng gắn với đời sống văn hóa của mình.', $d);

        $this->fill($s, 'Đàn ___ chỉ có duy nhất một dây nhưng vẫn chơi được nhiều giai điệu.', [[0, 'bầu']], 'Đàn bầu là nhạc cụ độc đáo của Việt Nam với chỉ một dây duy nhất.', $d);
        $this->fill($s, 'Khèn là nhạc cụ hơi đặc trưng của đồng bào dân tộc ___.', [[0, 'Mông']], 'Khèn gắn liền với lễ hội và đời sống của đồng bào Mông vùng Tây Bắc.', $d);
        $this->fill($s, 'Tù và được làm từ ___ trâu, phát ra âm thanh vang xa.', [[0, 'sừng']], 'Tù và làm từ sừng trâu, thường dùng để báo hiệu trong các lễ hội.', $d);
        $this->fill($s, 'Đàn ___ có 16 đến 19 dây, gảy bằng móng đeo ở tay.', [[0, 'tranh']], 'Đàn tranh là nhạc cụ dây gảy quen thuộc trong dàn nhạc dân tộc.', $d);
        $this->fill($s, '___ la là nhạc cụ gõ bằng kim loại, âm thanh trong và vang.', [[0, 'Thanh']], 'Thanh la thường xuất hiện trong dàn nhạc chèo, tuồng và các lễ hội.', $d);
    }

    private function seedNgheThuatTruyenThongLop91(): void
    {
        $s = 'an-nhac-cu-dan-toc-lop-9-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Nhân vật hề trong nghệ thuật chèo có vai trò gì?', ['Chỉ hát, không nói', 'Gây cười và châm biếm thói hư tật xấu', 'Chỉ múa, không hát', 'Đánh trống đệm cho diễn viên'], 1, 'Hề chèo vừa gây cười vừa châm biếm, phản ánh đời sống xã hội một cách dí dỏm, sâu sắc.', $d);
        $this->quiz($s, 'Trong ca trù, người hát chính được gọi là gì?', ['Đào nương', 'Kép đàn', 'Quan viên', 'Hề chèo'], 0, 'Đào nương là người hát chính trong ca trù, vừa hát vừa gõ phách giữ nhịp.', $d);
        $this->quiz($s, 'Đặc điểm nổi bật của lối hát quan họ là gì?', ['Hát đơn ca một mình', 'Hát đối đáp giữa liền anh và liền chị', 'Hát không có lời', 'Hát trên sân khấu lớn'], 1, 'Quan họ là lối hát đối đáp giao duyên giữa hai bên nam (liền anh) và nữ (liền chị).', $d);
        $this->quiz($s, 'Trang phục truyền thống của liền chị quan họ gồm những gì?', ['Áo dài, khăn xếp', 'Áo tứ thân, nón quai thao', 'Áo bà ba, khăn rằn', 'Váy xòe, áo yếm'], 1, 'Liền chị quan họ mặc áo tứ thân, đội nón quai thao — trang phục đặc trưng của vùng Kinh Bắc.', $d);
        $this->quiz($s, 'Nhạc cụ đệm chính trong ca trù là gì?', ['Đàn tranh', 'Đàn đáy', 'Sáo', 'Trống cơm'], 1, 'Đàn đáy là nhạc cụ đệm chính cho giọng hát đào nương trong ca trù.', $d);

        $this->matching($s, 'Nối mỗi nhân vật chèo với đặc điểm của họ.', [['Hề', 'Gây cười, châm biếm'], ['Thị Mầu', 'Cô gái lẳng lơ, táo bạo'], ['Trương Viên', 'Chàng thư sinh nghèo'], ['Thị Kính', 'Người phụ nữ chịu thương, chịu khó']], 'Mỗi nhân vật chèo đại diện cho một tính cách, một tầng lớp trong xã hội xưa.', $d);
        $this->matching($s, 'Nối mỗi loại hình với nhạc cụ đệm tiêu biểu.', [['Ca trù', 'Đàn đáy, phách'], ['Chèo', 'Trống chèo, sáo'], ['Quan họ', 'Hát chay, không nhạc cụ'], ['Đờn ca tài tử', 'Đàn kìm, đàn cò']], 'Mỗi loại hình có bộ nhạc cụ đệm riêng tạo nên màu sắc âm thanh đặc trưng.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa đúng của nó.', [['Liền anh', 'Nam giới hát quan họ'], ['Liền chị', 'Nữ giới hát quan họ'], ['Đào nương', 'Người hát chính trong ca trù'], ['Kép đàn', 'Người chơi đàn đáy trong ca trù']], 'Nắm vững thuật ngữ giúp hiểu đúng vai trò của từng người trong loại hình nghệ thuật.', $d);
        $this->matching($s, 'Nối mỗi dịp lễ hội với loại hình nghệ thuật thường diễn.', [['Hội Lim', 'Hát quan họ'], ['Lễ hội làng quê Bắc Bộ', 'Diễn chèo'], ['Đình làng xưa', 'Hát ca trù'], ['Đám cưới Nam Bộ', 'Đờn ca tài tử']], 'Nghệ thuật truyền thống gắn bó mật thiết với lễ hội và sinh hoạt cộng đồng.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với loại hình tương ứng.', [['Hát đối đáp giao duyên', 'Quan họ'], ['Sân khấu có hề, có trống', 'Chèo'], ['Một người hát, một người đàn', 'Ca trù'], ['Ngồi hát với đàn kìm, đàn cò', 'Đờn ca tài tử']], 'Mỗi loại hình có hình thức thể hiện riêng không thể lẫn với loại hình khác.', $d);

        $this->sortQ($s, 'Xếp mỗi loại hình vào nhóm MIỀN BẮC hoặc MIỀN NAM.', [['Quan họ', 'MIỀN BẮC'], ['Chèo', 'MIỀN BẮC'], ['Ca trù', 'MIỀN BẮC'], ['Đờn ca tài tử', 'MIỀN NAM']], 'Quan họ, chèo, ca trù là nghệ thuật miền Bắc; đờn ca tài tử là nghệ thuật miền Nam.', $d);
        $this->sortQ($s, 'Xếp mỗi yếu tố vào nhóm THUỘC VỀ CHÈO hoặc THUỘC VỀ CA TRÙ.', [['Nhân vật hề', 'THUỘC VỀ CHÈO'], ['Trống chèo', 'THUỘC VỀ CHÈO'], ['Đào nương', 'THUỘC VỀ CA TRÙ'], ['Đàn đáy', 'THUỘC VỀ CA TRÙ'], ['Sân khấu diễn', 'THUỘC VỀ CHÈO'], ['Phách', 'THUỘC VỀ CA TRÙ']], 'Chèo là nghệ thuật sân khấu, ca trù là nghệ thuật hát thơ thính phòng — mỗi loại có yếu tố riêng.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Quan họ hát đối đáp giữa liền anh và liền chị', 'ĐÚNG'], ['Ca trù có đào nương vừa hát vừa gõ phách', 'ĐÚNG'], ['Chèo là loại hình hát không cần sân khấu', 'SAI'], ['Đờn ca tài tử là nghệ thuật của miền Bắc', 'SAI']], 'Phân biệt đúng đặc điểm giúp không nhầm lẫn các loại hình nghệ thuật truyền thống.', $d);
        $this->sortQ($s, 'Xếp mỗi loại hình vào nhóm CÓ SÂN KHẤU DIỄN hoặc HÁT GIAO LƯU.', [['Chèo', 'CÓ SÂN KHẤU DIỄN'], ['Quan họ', 'HÁT GIAO LƯU'], ['Đờn ca tài tử', 'HÁT GIAO LƯU'], ['Ca trù', 'HÁT GIAO LƯU']], 'Chèo diễn trên sân khấu với nhân vật, còn quan họ, ca trù, đờn ca tài tử chủ yếu hát giao lưu.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm ĐỆM CHO CA TRÙ hoặc ĐỆM CHO CHÈO.', [['Đàn đáy', 'ĐỆM CHO CA TRÙ'], ['Phách', 'ĐỆM CHO CA TRÙ'], ['Trống chèo', 'ĐỆM CHO CHÈO'], ['Sáo', 'ĐỆM CHO CHÈO']], 'Đàn đáy và phách đệm cho ca trù; trống chèo và sáo đệm cho sân khấu chèo.', $d);

        $this->fill($s, 'Trong ca trù, đào nương vừa hát vừa gõ ___ để giữ nhịp.', [[0, 'phách']], 'Vừa hát vừa gõ phách là nét độc đáo của đào nương trong ca trù.', $d);
        $this->fill($s, 'Hội ___ là lễ hội lớn nhất của vùng đất quan họ Kinh Bắc.', [[0, 'Lim']], 'Hội Lim ở Bắc Ninh là nơi liền anh, liền chị khắp nơi về hát quan họ.', $d);
        $this->fill($s, 'Nhân vật ___ trong chèo chuyên gây cười và châm biếm thói hư tật xấu.', [[0, 'hề']], 'Hề là nhân vật không thể thiếu, mang tiếng cười trí tuệ trong chèo.', $d);
        $this->fill($s, 'Người chơi đàn đáy đệm cho đào nương trong ca trù được gọi là ___ đàn.', [[0, 'kép']], 'Kép đàn phối hợp ăn ý với đào nương để tạo nên buổi hát ca trù trọn vẹn.', $d);
        $this->fill($s, 'Liền anh, liền chị là cách gọi người hát trong làn điệu ___ họ.', [[0, 'quan']], 'Quan họ là làn điệu dân ca đối đáp nổi tiếng của vùng Kinh Bắc.', $d);
    }

    private function seedNhacCuTayVaDanNhacLop92(): void
    {
        $s = 'an-nhac-cu-dan-toc-lop-9-2';
        $d = 'kho';

        $this->quiz($s, 'Bộ dây (strings) trong dàn nhạc giao hưởng KHÔNG bao gồm nhạc cụ nào?', ['Violin', 'Viola', 'Cello', 'Flute'], 3, 'Flute thuộc bộ hơi gỗ, không phải bộ dây nên không có mặt trong nhóm strings.', $d);
        $this->quiz($s, 'Nhạc cụ nào có âm vực trầm nhất trong bộ dây?', ['Violin', 'Viola', 'Cello', 'Contrabass'], 3, 'Contrabass (đại hồ cầm) có âm vực trầm nhất, làm nền tảng âm trầm cho dàn nhạc.', $d);
        $this->quiz($s, 'Kèn oboe thuộc bộ nào và có đặc điểm gì nổi bật?', ['Bộ đồng, âm vang dũng mãnh', 'Bộ hơi gỗ, âm thanh trong như tiếng chim', 'Bộ dây, âm mượt mà', 'Bộ gõ, âm vang xa'], 1, 'Oboe thuộc bộ hơi gỗ, âm trong trẻo đặc trưng, thường được dùng để cả dàn nhạc lên dây.', $d);
        $this->quiz($s, 'So với đàn tranh Việt Nam, đàn harp phương Tây khác ở điểm nào?', ['Harp không có dây', 'Harp có khung lớn, chơi bằng cả hai tay gảy dây', 'Harp dùng hơi để thổi', 'Harp là nhạc cụ gõ'], 1, 'Harp có khung tam giác lớn, người chơi gảy dây bằng cả hai tay; đàn tranh đặt nằm ngang, gảy bằng móng.', $d);
        $this->quiz($s, 'Trong dàn nhạc giao hưởng, ai quyết định tốc độ và cách diễn cảm của bản nhạc?', ['Nhạc công violin đầu đàn', 'Nhạc trưởng', 'Người chơi trống định âm', 'Khán giả'], 1, 'Nhạc trưởng chỉ huy toàn dàn nhạc bằng đũa chỉ huy, quyết định tốc độ và diễn cảm.', $d);

        $this->matching($s, 'Nối mỗi nhạc cụ với bộ của nó trong dàn nhạc.', [['Oboe', 'Bộ hơi gỗ'], ['Trombone', 'Bộ đồng'], ['Timpani', 'Bộ gõ'], ['Harp', 'Bộ dây (gảy)']], 'Dàn nhạc giao hưởng chia thành 4 bộ: dây, hơi gỗ, đồng và gõ.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ Việt Nam với nhạc cụ phương Tây có vai trò tương đương.', [['Đàn tranh', 'Harp'], ['Sáo', 'Flute'], ['Trống chầu', 'Timpani'], ['Đàn nhị', 'Violin']], 'So sánh giúp thấy điểm tương đồng: đàn tranh – harp (dây gảy), sáo – flute (hơi), trống chầu – timpani (giữ nhịp), đàn nhị – violin (dây kéo).', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ dàn nhạc với ý nghĩa của nó.', [['Nhạc trưởng', 'Người chỉ huy dàn nhạc'], ['Concertmaster', 'Nhạc công violin đầu đàn'], ['Solo', 'Đoạn độc tấu một nhạc cụ'], ['Tutti', 'Cả dàn nhạc cùng chơi']], 'Thuật ngữ dàn nhạc phần lớn có gốc tiếng Ý, dùng chung trên toàn thế giới.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với cách tạo ra âm thanh.', [['Trumpet', 'Môi rung vào miệng kèn'], ['Clarinet', 'Lưỡi gà rung nhờ hơi thổi'], ['Piano', 'Búa gõ vào dây'], ['Xylophone', 'Dùi gõ vào các thanh gỗ']], 'Cách tạo âm khác nhau tạo nên âm sắc riêng của từng nhạc cụ.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm âm thanh với bộ nhạc cụ.', [['Âm vang, dũng mãnh', 'Bộ đồng'], ['Âm trong, mềm mại', 'Bộ hơi gỗ'], ['Âm mượt mà, biểu cảm', 'Bộ dây'], ['Âm dồn dập, mạnh mẽ', 'Bộ gõ']], 'Mỗi bộ nhạc cụ đảm nhận một màu sắc âm thanh riêng trong dàn nhạc.', $d);

        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm BỘ HƠI GỖ hoặc BỘ ĐỒNG.', [['Oboe', 'BỘ HƠI GỖ'], ['Clarinet', 'BỘ HƠI GỖ'], ['Trumpet', 'BỘ ĐỒNG'], ['Trombone', 'BỘ ĐỒNG'], ['Bassoon', 'BỘ HƠI GỖ'], ['Kèn cor', 'BỘ ĐỒNG']], 'Bộ hơi gỗ dùng lưỡi gà hoặc hơi xiên qua lỗ, bộ đồng dùng môi rung vào miệng kèn.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm DÂY KÉO hoặc DÂY GẢY.', [['Violin', 'DÂY KÉO'], ['Cello', 'DÂY KÉO'], ['Harp', 'DÂY GẢY'], ['Guitar', 'DÂY GẢY']], 'Dây kéo dùng vĩ cọ vào dây, dây gảy dùng tay hoặc móng tác động trực tiếp.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Oboe thuộc bộ hơi gỗ', 'ĐÚNG'], ['Contrabass có âm vực trầm nhất bộ dây', 'ĐÚNG'], ['Trumpet thuộc bộ dây', 'SAI'], ['Piano tạo âm thanh bằng hơi thổi', 'SAI']], 'Nắm vững bộ nhạc cụ giúp gọi tên đúng mọi nhạc cụ trong dàn nhạc giao hưởng.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm ÂM TRẦM hoặc ÂM CAO.', [['Contrabass', 'ÂM TRẦM'], ['Tuba', 'ÂM TRẦM'], ['Piccolo', 'ÂM CAO'], ['Violin', 'ÂM CAO']], 'Âm vực từ trầm tới cao được phân bố đều cho các nhạc cụ trong dàn nhạc.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm PHƯƠNG ĐÔNG hoặc PHƯƠNG TÂY.', [['Đàn bầu', 'PHƯƠNG ĐÔNG'], ['Đàn tranh', 'PHƯƠNG ĐÔNG'], ['Violin', 'PHƯƠNG TÂY'], ['Oboe', 'PHƯƠNG TÂY']], 'Mỗi nền văn hóa phát triển hệ nhạc cụ riêng, nhưng nhiều nhạc cụ có vai trò tương đương nhau.', $d);

        $this->fill($s, 'Nhạc cụ có âm vực trầm nhất trong bộ dây là ___.', [[0, 'contrabass']], 'Contrabass (đại hồ cầm) đảm nhận âm trầm, làm nền tảng cho cả dàn nhạc.', $d);
        $this->fill($s, 'Kèn ___ thuộc bộ hơi gỗ, thường được dùng để cả dàn nhạc lên dây.', [[0, 'oboe']], 'Âm oboe chuẩn và trong nên được chọn làm mốc lên dây cho dàn nhạc.', $d);
        $this->fill($s, 'Đoạn cả dàn nhạc cùng chơi vang lên được gọi là ___.', [[0, 'tutti']], 'Tutti trong tiếng Ý nghĩa là "tất cả", chỉ đoạn toàn dàn nhạc cùng chơi.', $d);
        $this->fill($s, 'Sáo piccolo có âm vực ___ hơn sáo flute thông thường.', [[0, 'cao']], 'Piccolo nhỏ bằng nửa flute, phát ra âm cao hơn một quãng 8.', $d);
        $this->fill($s, 'Nhạc công violin đầu đàn trong dàn nhạc được gọi là ___.', [[0, 'concertmaster']], 'Concertmaster là thủ lĩnh bộ dây, hỗ trợ nhạc trưởng điều hành dàn nhạc.', $d);
    }

    private function seedHopAmBaLop101(): void
    {
        $s = 'am-nhac-thpt-10-lop-10-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Hợp âm G (Sol trưởng) gồm các nốt nào?', ['Sol – Si – Rê', 'Sol – La – Đô', 'Sol – Đô – Mi', 'Sol – Fa – La'], 0, 'Hợp âm Sol trưởng gồm Sol (nốt gốc), Si (quãng 3 trưởng) và Rê (quãng 5 đúng).', $d);
        $this->quiz($s, 'Quãng 3 trưởng và quãng 3 thứ khác nhau ở điểm nào?', ['Số nốt trong quãng', 'Khoảng cách nửa cung: 4 nửa cung và 3 nửa cung', 'Tên gọi của các nốt', 'Vị trí trên khuông nhạc'], 1, 'Quãng 3 trưởng rộng 4 nửa cung, quãng 3 thứ rộng 3 nửa cung — đây là điểm tạo nên màu sắc trưởng/thứ.', $d);
        $this->quiz($s, 'Trong hợp âm ba, nốt ở giữa (giữa nốt gốc và nốt quãng 5) được gọi là gì?', ['Nốt chủ âm', 'Nốt bậc 3 (mediant)', 'Nốt át âm', 'Nốt dẫn âm'], 1, 'Nốt bậc 3 (mediant) là nốt giữa, quyết định màu sắc trưởng hay thứ của hợp âm.', $d);
        $this->quiz($s, 'Hợp âm F (Fa trưởng) gồm các nốt nào?', ['Fa – Sol – Đô', 'Fa – La – Đô', 'Fa – La – Rê', 'Mi – Sol – Đô'], 1, 'Hợp âm Fa trưởng gồm Fa (nốt gốc), La (quãng 3 trưởng) và Đô (quãng 5 đúng).', $d);
        $this->quiz($s, 'Vì sao hợp âm thứ nghe trầm buồn hơn hợp âm trưởng?', ['Vì có ít nốt hơn', 'Vì quãng 3 thứ hẹp hơn tạo cảm giác trầm lắng', 'Vì luôn chơi chậm hơn', 'Vì dùng các nốt thấp hơn'], 1, 'Quãng 3 thứ (3 nửa cung) hẹp hơn quãng 3 trưởng (4 nửa cung), tạo cảm giác trầm lắng, buồn hơn.', $d);

        $this->matching($s, 'Nối mỗi hợp âm với cấu tạo nốt đúng của nó.', [['G', 'Sol – Si – Rê'], ['Em', 'Mi – Sol – Si'], ['D', 'Rê – Fa# – La'], ['Am', 'La – Đô – Mi']], 'Mỗi hợp âm ba có một bộ 3 nốt cố định, chỉ cần nhớ nốt gốc và màu trưởng/thứ.', $d);
        $this->matching($s, 'Nối mỗi bậc của hợp âm ba với tên gọi đúng.', [['Nốt gốc', 'Nốt thấp nhất, đặt tên cho hợp âm'], ['Nốt bậc 3', 'Quyết định màu trưởng hay thứ'], ['Nốt bậc 5', 'Nốt cao nhất của hợp âm ba'], ['Quãng 3 trưởng', 'Rộng 4 nửa cung']], 'Hiểu vai trò từng nốt giúp gọi tên và phân tích hợp âm nhanh hơn.', $d);
        $this->matching($s, 'Nối mỗi ký hiệu với ý nghĩa của nó.', [['C', 'Đô trưởng'], ['Cm', 'Đô thứ'], ['G7', 'Sol 7 (thêm nốt bậc 7)'], ['Fm', 'Fa thứ']], 'Ký hiệu quốc tế gọn nhẹ: chữ cái là nốt gốc, chữ m là thứ, số 7 là thêm nốt bậc 7.', $d);
        $this->matching($s, 'Nối mỗi quãng với số nửa cung của nó.', [['Quãng 3 trưởng', '4 nửa cung'], ['Quãng 3 thứ', '3 nửa cung'], ['Quãng 5 đúng', '7 nửa cung'], ['Quãng 8 đúng', '12 nửa cung']], 'Đếm nửa cung là cách chính xác nhất để xác định mọi quãng.', $d);
        $this->matching($s, 'Nối mỗi màu sắc cảm xúc với loại hợp âm thường tạo ra nó.', [['Vui tươi, sáng sủa', 'Hợp âm trưởng'], ['Trầm lắng, buồn', 'Hợp âm thứ'], ['Căng thẳng, muốn giải quyết', 'Hợp âm 7'], ['Ổn định, kết thúc', 'Hợp âm chủ']], 'Nhạc sĩ chọn hợp âm theo cảm xúc muốn truyền tải trong từng đoạn nhạc.', $d);

        $this->sortQ($s, 'Xếp mỗi hợp âm vào nhóm TRƯỞNG hoặc THỨ.', [['C', 'TRƯỞNG'], ['G', 'TRƯỞNG'], ['Am', 'THỨ'], ['Em', 'THỨ'], ['F', 'TRƯỞNG'], ['Dm', 'THỨ']], 'Ký hiệu không có chữ m là trưởng, có chữ m là thứ.', $d);
        $this->sortQ($s, 'Xếp mỗi cấu tạo nốt vào nhóm HỢP ÂM TRƯỞNG hoặc HỢP ÂM THỨ.', [['Đô – Mi – Sol', 'HỢP ÂM TRƯỞNG'], ['Sol – Si – Rê', 'HỢP ÂM TRƯỞNG'], ['La – Đô – Mi', 'HỢP ÂM THỨ'], ['Rê – Fa – La', 'HỢP ÂM THỨ']], 'Nhìn nốt bậc 3: cách nốt gốc 4 nửa cung là trưởng, 3 nửa cung là thứ.', $d);
        $this->sortQ($s, 'Xếp mỗi cặp nốt vào nhóm QUÃNG 3 hoặc QUÃNG 5.', [['Đô – Mi', 'QUÃNG 3'], ['Sol – Si', 'QUÃNG 3'], ['Đô – Sol', 'QUÃNG 5'], ['La – Mi', 'QUÃNG 5']], 'Đếm bậc nốt: Đô-Rê-Mi là 3 bậc, Đô-Rê-Mi-Fa-Sol là 5 bậc.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Hợp âm ba gồm 3 nốt xếp chồng theo quãng 3', 'ĐÚNG'], ['Nốt bậc 3 quyết định màu trưởng hay thứ', 'ĐÚNG'], ['Hợp âm Cm gồm Đô – Mi – Sol', 'SAI'], ['Quãng 3 thứ rộng 4 nửa cung', 'SAI']], 'Hợp âm Cm gồm Đô – Mi♭ – Sol; quãng 3 thứ chỉ rộng 3 nửa cung.', $d);
        $this->sortQ($s, 'Xếp mỗi ký hiệu vào nhóm CÓ CHỮ m hoặc KHÔNG CÓ CHỮ m.', [['Am', 'CÓ CHỮ m'], ['Em', 'CÓ CHỮ m'], ['C', 'KHÔNG CÓ CHỮ m'], ['G', 'KHÔNG CÓ CHỮ m']], 'Chữ m (minor) là dấu hiệu nhận biết nhanh hợp âm thứ.', $d);

        $this->fill($s, 'Hợp âm Sol trưởng gồm các nốt Sol, Si và ___.', [[0, 'Rê']], 'Sol trưởng = Sol (gốc) + Si (quãng 3 trưởng) + Rê (quãng 5 đúng).', $d);
        $this->fill($s, 'Quãng 3 trưởng rộng ___ nửa cung.', [[0, '4']], 'Quãng 3 trưởng gồm 4 nửa cung, rộng hơn quãng 3 thứ đúng một nửa cung.', $d);
        $this->fill($s, 'Trong hợp âm ba, nốt ở giữa quyết định màu trưởng/thứ gọi là nốt bậc ___.', [[0, '3']], 'Nốt bậc 3 (mediant) tạo nên sự khác biệt giữa hợp âm trưởng và hợp âm thứ.', $d);
        $this->fill($s, 'Hợp âm La thứ gồm các nốt La, Đô và ___.', [[0, 'Mi']], 'La thứ = La (gốc) + Đô (quãng 3 thứ) + Mi (quãng 5 đúng).', $d);
        $this->fill($s, 'Ký hiệu ___ chỉ hợp âm Rê trưởng.', [[0, 'D']], 'Trong ký hiệu quốc tế, chữ D tương ứng với nốt Rê; không có chữ m nên là hợp âm trưởng.', $d);
    }

    private function seedVongHopAmLop102(): void
    {
        $s = 'am-nhac-thpt-10-lop-10-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Vòng hợp âm Am – F – C – G thường được dùng trong giọng nào?', ['Giọng Đô trưởng', 'Giọng La thứ (song song với Đô trưởng)', 'Giọng Sol trưởng', 'Giọng Fa trưởng'], 1, 'Am là chủ âm của giọng La thứ — giọng thứ song song với Đô trưởng, vòng này rất phổ biến trong nhạc trẻ.', $d);
        $this->quiz($s, 'Trong giọng Sol trưởng, hợp âm bậc I là gì?', ['C', 'G', 'D', 'Em'], 1, 'Bậc I là hợp âm chủ, xây dựng trên nốt chủ âm Sol nên là hợp âm G.', $d);
        $this->quiz($s, 'Hợp âm song song (relative) của Đô trưởng là gì?', ['Sol trưởng', 'La thứ', 'Fa trưởng', 'Mi thứ'], 1, 'La thứ là giọng thứ song song của Đô trưởng, dùng chung bộ dấu hóa không thăng giáng.', $d);
        $this->quiz($s, 'Khi đệm hát, vì sao người ta thường kết thúc bài ở hợp âm bậc I?', ['Vì dễ bấm nhất', 'Vì bậc I tạo cảm giác ổn định, kết thúc trọn vẹn', 'Vì hợp âm bậc I vang to nhất', 'Vì thói quen, không có lý do'], 1, 'Hợp âm chủ (bậc I) mang cảm giác ổn định, là "ngôi nhà" để giai điệu trở về kết thúc.', $d);
        $this->quiz($s, 'Ký hiệu "m7" trong Am7 có nghĩa là gì?', ['Hợp âm La trưởng', 'Hợp âm La thứ thêm nốt bậc 7', 'Hợp âm La giảm', 'Chơi lặp lại 7 lần'], 1, 'Chữ m là thứ (minor), số 7 nghĩa là thêm nốt bậc 7 vào hợp âm ba La thứ.', $d);

        $this->matching($s, 'Nối mỗi giọng với hợp âm bậc I của nó.', [['Đô trưởng', 'C'], ['Sol trưởng', 'G'], ['Fa trưởng', 'F'], ['La thứ', 'Am']], 'Hợp âm bậc I luôn xây dựng trên nốt chủ âm của giọng.', $d);
        $this->matching($s, 'Nối mỗi bậc với vai trò của nó trong giọng.', [['Bậc I (chủ)', 'Ổn định, kết thúc'], ['Bậc V (át)', 'Căng thẳng, muốn về chủ'], ['Bậc IV (hạ át)', 'Mở rộng, chuyển tiếp'], ['Bậc vi', 'Màu thứ, sâu lắng']], 'Mỗi bậc có một "tính cách" riêng tạo nên mạch cảm xúc của bài hát.', $d);
        $this->matching($s, 'Nối mỗi vòng hợp âm với giọng nó thường dùng.', [['C – G – Am – F', 'Đô trưởng'], ['G – D – Em – C', 'Sol trưởng'], ['Am – F – C – G', 'La thứ'], ['F – Bb – C – Dm', 'Fa trưởng']], 'Mỗi giọng có những vòng hợp âm "ruột" được dùng đi dùng lại trong nhiều bài hát.', $d);
        $this->matching($s, 'Nối mỗi ký hiệu với tên gọi đầy đủ.', [['Em7', 'Mi thứ 7'], ['Cmaj7', 'Đô trưởng 7'], ['D7', 'Rê 7'], ['Bm', 'Si thứ']], 'Đọc ký hiệu giúp gọi đúng tên hợp âm khi nhìn vào bản nhạc hoặc hợp âm bài hát.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.', [['Giọng song song', 'Trưởng và thứ chung bộ dấu hóa'], ['Hợp âm chủ', 'Hợp âm bậc I của giọng'], ['Chuyển giọng', 'Đổi giọng giữa chừng bài hát'], ['Đệm hát', 'Chơi hợp âm theo giai điệu bài hát']], 'Nắm khái niệm giúp hiểu cấu trúc hòa âm của một bài hát.', $d);

        $this->sortQ($s, 'Xếp mỗi hợp âm vào nhóm BẬC I–IV–V hoặc BẬC KHÁC trong giọng Đô trưởng.', [['C', 'BẬC I–IV–V'], ['F', 'BẬC I–IV–V'], ['G', 'BẬC I–IV–V'], ['Am', 'BẬC KHÁC'], ['Dm', 'BẬC KHÁC'], ['Em', 'BẬC KHÁC']], 'Ba bậc I–IV–V là trụ cột hòa âm, đủ để đệm hầu hết các bài hát đơn giản.', $d);
        $this->sortQ($s, 'Xếp mỗi ký hiệu vào nhóm HỢP ÂM 7 hoặc HỢP ÂM BA THƯỜNG.', [['G7', 'HỢP ÂM 7'], ['Am7', 'HỢP ÂM 7'], ['C', 'HỢP ÂM BA THƯỜNG'], ['F', 'HỢP ÂM BA THƯỜNG']], 'Hợp âm 7 có thêm nốt bậc 7 nên màu sắc phong phú, căng hơn hợp âm ba thường.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Vòng C – G – Am – F dùng các bậc I – V – vi – IV', 'ĐÚNG'], ['La thứ là giọng song song của Đô trưởng', 'ĐÚNG'], ['Bài hát thường kết thúc ở hợp âm bậc I', 'ĐÚNG'], ['Chữ m trong ký hiệu Am nghĩa là trưởng', 'SAI']], 'Chữ m là minor (thứ), không phải trưởng — đây là điểm dễ nhầm nhất khi đọc ký hiệu.', $d);
        $this->sortQ($s, 'Xếp mỗi cặp giọng – hợp âm chủ vào nhóm GIỌNG TRƯỞNG hoặc GIỌNG THỨ.', [['Đô trưởng – C', 'GIỌNG TRƯỞNG'], ['Sol trưởng – G', 'GIỌNG TRƯỞNG'], ['La thứ – Am', 'GIỌNG THỨ'], ['Mi thứ – Em', 'GIỌNG THỨ']], 'Hợp âm chủ mang tên nốt chủ âm và cùng màu trưởng/thứ với giọng.', $d);
        $this->sortQ($s, 'Xếp mỗi hợp âm vào nhóm CÓ TRONG VÒNG C–G–Am–F hoặc KHÔNG CÓ.', [['C', 'CÓ TRONG VÒNG C–G–Am–F'], ['G', 'CÓ TRONG VÒNG C–G–Am–F'], ['Am', 'CÓ TRONG VÒNG C–G–Am–F'], ['F', 'CÓ TRONG VÒNG C–G–Am–F'], ['D', 'KHÔNG CÓ'], ['Em', 'KHÔNG CÓ']], 'Vòng I – V – vi – IV là vòng hợp âm phổ biến nhất trong nhạc nhẹ hiện nay.', $d);

        $this->fill($s, 'Giọng thứ song song với Đô trưởng là ___ thứ.', [[0, 'La']], 'La thứ dùng chung bộ dấu hóa với Đô trưởng (không có dấu thăng giáng).', $d);
        $this->fill($s, 'Trong giọng Sol trưởng, hợp âm bậc V là ___.', [[0, 'D']], 'Bậc V của Sol trưởng xây dựng trên nốt Rê nên là hợp âm D.', $d);
        $this->fill($s, 'Ký hiệu quốc tế của nốt Sol là chữ ___.', [[0, 'G']], 'Bảy chữ cái A–B–C–D–E–F–G tương ứng với La–Si–Đô–Rê–Mi–Fa–Sol.', $d);
        $this->fill($s, 'Hợp âm ___ là hợp âm chủ của giọng Fa trưởng.', [[0, 'F']], 'Hợp âm chủ (bậc I) luôn xây dựng trên nốt chủ âm Fa.', $d);
        $this->fill($s, 'Vòng I – vi – IV – V trong giọng Đô trưởng là C – Am – ___ – G.', [[0, 'F']], 'Bậc IV của Đô trưởng là Fa, nên vòng này là C – Am – F – G.', $d);
    }

    private function seedKhoaNhacNhipLop103(): void
    {
        $s = 'am-nhac-thpt-10-lop-10-3';
        $d = 'trung_binh';

        $this->quiz($s, 'Khóa Fa thường dùng để ghi nốt cho đối tượng nào?', ['Giọng nữ cao', 'Tay trái đàn piano và các nhạc cụ âm trầm', 'Sáo', 'Violin'], 1, 'Khóa Fa ghi các nốt âm trầm, dùng cho tay trái piano, cello, contrabass và giọng nam trầm.', $d);
        $this->quiz($s, 'Trong nhịp 2/4, mỗi ô nhịp có bao nhiêu phách?', ['4 phách', '3 phách', '2 phách', '6 phách'], 2, 'Số trên của số chỉ nhịp cho biết số phách trong mỗi ô nhịp — 2/4 có 2 phách.', $d);
        $this->quiz($s, 'Nốt đen trong nhịp 4/4 dài bao nhiêu phách?', ['4 phách', '2 phách', '1 phách', 'Nửa phách'], 2, 'Trong nhịp 4/4, nốt đen dài đúng 1 phách — là đơn vị phách chuẩn.', $d);
        $this->quiz($s, 'Dấu lặng (dấu nghỉ) có tác dụng gì trong bản nhạc?', ['Tăng âm lượng', 'Chỉ chỗ ngừng, không phát ra âm thanh', 'Chơi nhanh hơn', 'Lặp lại đoạn nhạc'], 1, 'Dấu lặng quy định khoảng ngừng có độ dài tương ứng, tạo nhịp thở cho bản nhạc.', $d);
        $this->quiz($s, 'Vạch nhịp đôi (hai vạch đứng) ở cuối bản nhạc có ý nghĩa gì?', ['Bắt đầu đoạn nhạc mới', 'Kết thúc bản nhạc hoặc đoạn nhạc', 'Chơi to dần lên', 'Đổi sang nhịp khác'], 1, 'Vạch nhịp đôi ở cuối báo hiệu bản nhạc hoặc đoạn nhạc đã kết thúc.', $d);

        $this->matching($s, 'Nối mỗi nốt nhạc với trường độ của nó trong nhịp 4/4.', [['Nốt tròn', '4 phách'], ['Nốt trắng', '2 phách'], ['Nốt đen', '1 phách'], ['Nốt móc đơn', 'Nửa phách']], 'Nốt tròn dài gấp đôi nốt trắng, gấp 4 lần nốt đen — cứ thế chia đôi dần.', $d);
        $this->matching($s, 'Nối mỗi số chỉ nhịp với số phách trong mỗi ô nhịp.', [['2/4', '2 phách'], ['3/4', '3 phách'], ['4/4', '4 phách'], ['6/8', '6 phách (nhịp kép)']], 'Số trên cho biết số phách, số dưới cho biết loại nốt làm đơn vị phách.', $d);
        $this->matching($s, 'Nối mỗi loại vạch nhịp với ý nghĩa của nó.', [['Vạch nhịp đơn', 'Ngăn cách các ô nhịp'], ['Vạch nhịp đôi', 'Kết thúc đoạn hoặc bản nhạc'], ['Dấu lặp lại', 'Chơi lặp lại đoạn nhạc'], ['Khóa nhạc đầu khuông', 'Xác định cao độ các nốt']], 'Các ký hiệu trên khuông nhạc mỗi loại một nhiệm vụ, đọc đúng mới chơi đúng.', $d);
        $this->matching($s, 'Nối mỗi dấu lặng với nốt có trường độ tương ứng.', [['Dấu lặng tròn', 'Nốt tròn'], ['Dấu lặng trắng', 'Nốt trắng'], ['Dấu lặng đen', 'Nốt đen'], ['Dấu lặng móc đơn', 'Nốt móc đơn']], 'Mỗi dấu lặng tương ứng với một loại nốt về độ dài của khoảng ngừng.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.', [['Ô nhịp', 'Khoảng giữa hai vạch nhịp'], ['Phách', 'Đơn vị nhịp cơ bản'], ['Trường độ', 'Độ dài ngắn của âm thanh'], ['Cao độ', 'Độ cao thấp của âm thanh']], 'Ô nhịp, phách, trường độ, cao độ là 4 khái niệm nền tảng để đọc bản nhạc.', $d);

        $this->sortQ($s, 'Xếp mỗi số chỉ nhịp vào nhóm NHỊP CHẴN PHÁCH hoặc NHỊP LẺ PHÁCH.', [['2/4', 'NHỊP CHẴN PHÁCH'], ['4/4', 'NHỊP CHẴN PHÁCH'], ['6/8', 'NHỊP CHẴN PHÁCH'], ['3/4', 'NHỊP LẺ PHÁCH'], ['3/8', 'NHỊP LẺ PHÁCH']], 'Nhịp chẵn phách cho cảm giác vững chãi, nhịp lẻ phách (như 3/4) cho cảm giác uyển chuyển.', $d);
        $this->sortQ($s, 'Xếp mỗi nốt vào nhóm DÀI HƠN 1 PHÁCH hoặc NGẮN HƠN 1 PHÁCH (nhịp 4/4).', [['Nốt tròn', 'DÀI HƠN 1 PHÁCH'], ['Nốt trắng', 'DÀI HƠN 1 PHÁCH'], ['Nốt móc đơn', 'NGẮN HƠN 1 PHÁCH'], ['Nốt móc kép', 'NGẮN HƠN 1 PHÁCH']], 'So với nốt đen (1 phách): nốt tròn, trắng dài hơn; nốt móc đơn, móc kép ngắn hơn.', $d);
        $this->sortQ($s, 'Xếp mỗi đối tượng vào nhóm DÙNG KHÓA SOL hoặc DÙNG KHÓA FA.', [['Violin', 'DÙNG KHÓA SOL'], ['Sáo', 'DÙNG KHÓA SOL'], ['Cello', 'DÙNG KHÓA FA'], ['Tay trái piano', 'DÙNG KHÓA FA']], 'Nhạc cụ âm cao dùng khóa Sol, nhạc cụ âm trầm dùng khóa Fa.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Nốt đen trong nhịp 4/4 dài 1 phách', 'ĐÚNG'], ['Khóa Fa dùng để ghi các nốt âm trầm', 'ĐÚNG'], ['Nhịp 3/4 có 4 phách trong mỗi ô nhịp', 'SAI'], ['Vạch nhịp đôi ở cuối báo hiệu bắt đầu bản nhạc', 'SAI']], 'Nhịp 3/4 có 3 phách mỗi ô nhịp; vạch nhịp đôi ở cuối báo hiệu kết thúc.', $d);
        $this->sortQ($s, 'Xếp mỗi ký hiệu vào nhóm CHỈ CAO ĐỘ hoặc CHỈ TRƯỜNG ĐỘ.', [['Khóa Sol', 'CHỈ CAO ĐỘ'], ['Dấu thăng', 'CHỈ CAO ĐỘ'], ['Nốt đen', 'CHỈ TRƯỜNG ĐỘ'], ['Dấu lặng trắng', 'CHỈ TRƯỜNG ĐỘ']], 'Khóa nhạc và dấu hóa quyết định cao độ; hình nốt và dấu lặng quyết định trường độ.', $d);

        $this->fill($s, 'Trong nhịp 4/4, nốt ___ là đơn vị của 1 phách.', [[0, 'đen']], 'Nốt đen dài đúng 1 phách trong nhịp 4/4.', $d);
        $this->fill($s, 'Số ___ trong số chỉ nhịp cho biết có bao nhiêu phách trong mỗi ô nhịp.', [[0, 'trên']], 'Số trên = số phách, số dưới = loại nốt làm đơn vị phách.', $d);
        $this->fill($s, 'Khóa ___ dùng để ghi các nốt âm trầm cho tay trái piano.', [[0, 'Fa']], 'Khóa Fa ôm lấy nốt Fa, chuyên ghi âm vực trầm.', $d);
        $this->fill($s, 'Khoảng giữa hai vạch nhịp được gọi là ô ___.', [[0, 'nhịp']], 'Ô nhịp là đơn vị cấu trúc cơ bản của bản nhạc.', $d);
        $this->fill($s, 'Nốt móc đơn dài bằng ___ nốt đen.', [[0, 'nửa']], 'Hai nốt móc đơn ghép lại dài bằng một nốt đen.', $d);
    }

    private function seedDauHoaNhipPhucLop104(): void
    {
        $s = 'am-nhac-thpt-10-lop-10-4';
        $d = 'kho';

        $this->quiz($s, 'Dấu thăng kép có tác dụng gì?', ['Nâng nốt lên một cung (2 nửa cung)', 'Nâng nốt lên nửa cung', 'Hạ nốt xuống nửa cung', 'Hủy mọi dấu hóa trước đó'], 0, 'Dấu thăng kép nâng nốt lên 2 nửa cung, tức là một cung nguyên.', $d);
        $this->quiz($s, 'Bộ khóa có 3 dấu thăng (Fa#, Đô#, Sol#) là của giọng nào?', ['Giọng La trưởng', 'Giọng Mi trưởng', 'Giọng Rê trưởng', 'Giọng Sol trưởng'], 0, 'Giọng La trưởng có 3 dấu thăng trong bộ khóa: Fa#, Đô# và Sol#.', $d);
        $this->quiz($s, 'Dấu hóa bất thường (ghi ngay trước nốt nhạc) có hiệu lực trong phạm vi nào?', ['Cả bản nhạc', 'Chỉ trong ô nhịp chứa nó', 'Cả đoạn nhạc', 'Chỉ một nốt duy nhất'], 1, 'Dấu hóa bất thường chỉ có hiệu lực trong ô nhịp chứa nó, sang ô nhịp mới thì hết hiệu lực.', $d);
        $this->quiz($s, 'Nhịp 5/4 thuộc loại nhịp nào?', ['Nhịp đơn', 'Nhịp kép', 'Nhịp hỗn hợp', 'Nhịp tự do'], 2, 'Nhịp 5/4 gồm 5 phách chia thành nhóm 3+2 hoặc 2+3, là nhịp hỗn hợp.', $d);
        $this->quiz($s, 'Khi gặp nốt có dấu chấm dôi (chấm sau nốt), trường độ của nốt thay đổi thế nào?', ['Ngắn đi một nửa', 'Dài thêm một nửa giá trị của nó', 'Dài gấp đôi', 'Không thay đổi'], 1, 'Dấu chấm dôi làm nốt dài thêm một nửa giá trị ban đầu, ví dụ nốt đen chấm dôi dài 1,5 phách.', $d);

        $this->matching($s, 'Nối mỗi dấu hóa với tác dụng của nó.', [['Dấu thăng (#)', 'Nâng lên nửa cung'], ['Dấu giáng (b)', 'Hạ xuống nửa cung'], ['Dấu thăng kép', 'Nâng lên một cung'], ['Dấu bình', 'Hủy thăng hoặc giáng']], 'Bốn dấu hóa cơ bản điều chỉnh cao độ nốt nhạc lên xuống theo nửa cung.', $d);
        $this->matching($s, 'Nối mỗi bộ khóa với giọng tương ứng.', [['1 dấu thăng', 'Sol trưởng'], ['2 dấu thăng', 'Rê trưởng'], ['3 dấu thăng', 'La trưởng'], ['1 dấu giáng', 'Fa trưởng']], 'Số lượng và vị trí dấu hóa trong bộ khóa xác định giọng của bản nhạc.', $d);
        $this->matching($s, 'Nối mỗi loại nhịp với ví dụ của nó.', [['Nhịp đơn', '2/4, 3/4'], ['Nhịp kép', '6/8, 9/8'], ['Nhịp hỗn hợp', '5/4, 7/8'], ['Nhịp C', '4/4']], 'Nhịp C (common time) là cách viết tắt quen thuộc của nhịp 4/4.', $d);
        $this->matching($s, 'Nối mỗi kỹ thuật đọc nhanh với mô tả đúng.', [['Đọc theo cụm nốt', 'Nhìn một nhóm nốt cùng lúc'], ['Nhìn trước', 'Mắt đi trước tay 1-2 ô nhịp'], ['Nhận diện mẫu hình', 'Nhớ các mẫu thang, hợp âm quen thuộc'], ['Giữ nhịp ổn định', 'Không dừng lại khi vấp']], 'Đọc nhanh là kỹ năng tổng hợp của mắt, trí nhớ mẫu hình và sự ổn định nhịp.', $d);
        $this->matching($s, 'Nối mỗi khoảng cách nốt với số cung của nó.', [['Mi – Fa', 'Nửa cung'], ['Si – Đô', 'Nửa cung'], ['Đô – Rê', 'Một cung'], ['Fa – Sol', 'Một cung']], 'Trong thang nhạc tự nhiên chỉ có Mi–Fa và Si–Đô là nửa cung, còn lại đều một cung.', $d);

        $this->sortQ($s, 'Xếp mỗi dấu hóa vào nhóm NÂNG CAO ĐỘ hoặc HẠ CAO ĐỘ.', [['Dấu thăng', 'NÂNG CAO ĐỘ'], ['Dấu thăng kép', 'NÂNG CAO ĐỘ'], ['Dấu giáng', 'HẠ CAO ĐỘ'], ['Dấu giáng kép', 'HẠ CAO ĐỘ']], 'Thăng (kể cả thăng kép) nâng cao độ, giáng (kể cả giáng kép) hạ cao độ.', $d);
        $this->sortQ($s, 'Xếp mỗi cặp nốt vào nhóm NỬA CUNG hoặc MỘT CUNG.', [['Mi – Fa', 'NỬA CUNG'], ['Si – Đô', 'NỬA CUNG'], ['Đô – Rê', 'MỘT CUNG'], ['La – Si', 'MỘT CUNG']], 'Ghi nhớ hai cặp nửa cung tự nhiên Mi–Fa và Si–Đô là chìa khóa đọc dấu hóa.', $d);
        $this->sortQ($s, 'Xếp mỗi số chỉ nhịp vào nhóm NHỊP ĐƠN hoặc NHỊP HỖN HỢP.', [['2/4', 'NHỊP ĐƠN'], ['3/4', 'NHỊP ĐƠN'], ['5/4', 'NHỊP HỖN HỢP'], ['7/8', 'NHỊP HỖN HỢP']], 'Nhịp hỗn hợp có số phách không chia đều thành nhóm 2 hoặc 3 như nhịp đơn, nhịp kép.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Dấu hóa bất thường chỉ có hiệu lực trong ô nhịp đó', 'ĐÚNG'], ['Dấu thăng kép nâng nốt lên một cung', 'ĐÚNG'], ['Bộ khóa 2 dấu thăng là của giọng Sol trưởng', 'SAI'], ['Nhịp 5/4 là nhịp đơn', 'SAI']], 'Bộ khóa 2 dấu thăng là của giọng Rê trưởng; nhịp 5/4 là nhịp hỗn hợp.', $d);
        $this->sortQ($s, 'Xếp mỗi giọng vào nhóm 1 DẤU THĂNG hoặc 2 DẤU THĂNG.', [['Sol trưởng', '1 DẤU THĂNG'], ['Mi thứ', '1 DẤU THĂNG'], ['Rê trưởng', '2 DẤU THĂNG'], ['Si thứ', '2 DẤU THĂNG']], 'Giọng thứ song song dùng chung bộ khóa với giọng trưởng của nó.', $d);

        $this->fill($s, 'Dấu ___ kép nâng nốt nhạc lên một cung.', [[0, 'thăng']], 'Thăng kép gồm 2 dấu thăng chồng nhau, nâng nốt lên 2 nửa cung.', $d);
        $this->fill($s, 'Dấu hóa ghi ngay trước nốt nhạc chỉ có hiệu lực trong ___ nhịp đó.', [[0, 'ô']], 'Sang ô nhịp mới, dấu hóa bất thường hết hiệu lực.', $d);
        $this->fill($s, 'Giọng La trưởng có ___ dấu thăng trong bộ khóa.', [[0, '3']], 'Bộ khóa La trưởng gồm Fa#, Đô# và Sol#.', $d);
        $this->fill($s, 'Nhịp 5/4 là nhịp ___ hợp.', [[0, 'hỗn']], 'Nhịp 5/4 chia phách thành nhóm 3+2 hoặc 2+3 nên là nhịp hỗn hợp.', $d);
        $this->fill($s, 'Nốt đen có dấu chấm dôi dài ___ phách.', [[0, '1,5']], 'Chấm dôi cộng thêm một nửa giá trị: 1 + 0,5 = 1,5 phách.', $d);
    }

    private function seedNhacCachMangLop111(): void
    {
        $s = 'am-nhac-thpt-11-lop-11-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Nhạc tiền chiến còn được gọi bằng tên nào khác?', ['Nhạc vàng', 'Nhạc lãng mạn', 'Nhạc rock', 'Nhạc điện tử'], 1, 'Nhạc tiền chiến (trước 1945) còn gọi là nhạc lãng mạn, với giai điệu trữ tình và lời ca trau chuốt.', $d);
        $this->quiz($s, 'Nội dung chủ yếu của nhạc cách mạng thời kháng chiến là gì?', ['Tình yêu đôi lứa', 'Ca ngợi quê hương, cổ vũ chiến đấu', 'Cuộc sống thành thị', 'Phong cảnh thiên nhiên'], 1, 'Nhạc cách mạng ca ngợi quê hương, đất nước và cổ vũ tinh thần chiến đấu của quân và dân.', $d);
        $this->quiz($s, 'Đặc điểm giai điệu của nhạc cách mạng thường như thế nào?', ['Nhẹ nhàng, ru ngủ', 'Hùng tráng, mạnh mẽ, dễ hát tập thể', 'Phức tạp, khó hát', 'Chậm rãi, buồn bã'], 1, 'Giai điệu nhạc cách mạng hùng tráng, tiết tấu rõ ràng để mọi người cùng hát vang.', $d);
        $this->quiz($s, 'Nhạc tiền chiến thường được sáng tác cho đối tượng nào thưởng thức?', ['Quân đội ngoài mặt trận', 'Tầng lớp thị dân, trí thức thành thị', 'Nông dân vùng quê', 'Trẻ em'], 1, 'Nhạc tiền chiến nở rộ ở đô thị, phục vụ tầng lớp thị dân và trí thức yêu cái đẹp lãng mạn.', $d);
        $this->quiz($s, 'Vì sao nhạc cách mạng có sức lan tỏa mạnh mẽ trong kháng chiến?', ['Vì được phát trên radio nhiều', 'Vì giai điệu dễ nhớ, lời ca khơi dậy lòng yêu nước', 'Vì chỉ hát trong nhà hát lớn', 'Vì dùng nhạc cụ hiện đại'], 1, 'Giai điệu dễ nhớ, dễ hát cùng lời ca khơi dậy lòng yêu nước giúp nhạc cách mạng lan tỏa khắp mặt trận và hậu phương.', $d);

        $this->matching($s, 'Nối mỗi dòng nhạc với bối cảnh ra đời của nó.', [['Nhạc tiền chiến', 'Đô thị trước năm 1945'], ['Nhạc cách mạng', 'Kháng chiến chống Pháp, chống Mỹ'], ['Nhạc đỏ', 'Tên gọi khác của nhạc cách mạng'], ['Nhạc lãng mạn', 'Tên gọi khác của nhạc tiền chiến']], 'Bối cảnh lịch sử quyết định nội dung và phong cách của mỗi dòng nhạc.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với dòng nhạc tương ứng.', [['Giai điệu hùng tráng, dễ hát tập thể', 'Nhạc cách mạng'], ['Lời ca trau chuốt, trữ tình', 'Nhạc tiền chiến'], ['Ca ngợi quê hương, cổ vũ chiến đấu', 'Nhạc cách mạng'], ['Tâm sự tình yêu, thân phận', 'Nhạc tiền chiến']], 'Nghe giai điệu và lời ca có thể nhận ra ngay dòng nhạc thuộc về đâu.', $d);
        $this->matching($s, 'Nối mỗi vai trò với người đảm nhận.', [['Sáng tác ca khúc', 'Nhạc sĩ'], ['Thể hiện ca khúc', 'Ca sĩ'], ['Viết lời cho ca khúc', 'Người viết lời'], ['Phối khí cho ca khúc', 'Nhạc sĩ phối khí']], 'Một ca khúc hoàn chỉnh là công sức của nhiều vai trò khác nhau.', $d);
        $this->matching($s, 'Nối mỗi hình thức biểu diễn với mô tả của nó.', [['Hành khúc', 'Nhạc bước đều, hùng mạnh'], ['Hợp xướng', 'Nhiều người hát nhiều bè'], ['Đơn ca', 'Một người hát'], ['Tốp ca', 'Nhóm ít người cùng hát']], 'Mỗi hình thức biểu diễn tạo ra không khí và hiệu quả âm thanh khác nhau.', $d);
        $this->matching($s, 'Nối mỗi mục đích với dòng nhạc phù hợp.', [['Cổ vũ chiến đấu', 'Nhạc cách mạng'], ['Bày tỏ tâm sự lãng mạn', 'Nhạc tiền chiến'], ['Hát trong lễ kỷ niệm', 'Nhạc cách mạng'], ['Hát trong phòng trà', 'Nhạc tiền chiến']], 'Hoàn cảnh sử dụng phản ánh rõ tính chất của từng dòng nhạc.', $d);

        $this->sortQ($s, 'Xếp mỗi đặc điểm vào nhóm NHẠC CÁCH MẠNG hoặc NHẠC TIỀN CHIẾN.', [['Hùng tráng, dễ hát tập thể', 'NHẠC CÁCH MẠNG'], ['Ca ngợi quê hương, cổ vũ chiến đấu', 'NHẠC CÁCH MẠNG'], ['Trữ tình, lời ca trau chuốt', 'NHẠC TIỀN CHIẾN'], ['Tâm sự tình yêu đôi lứa', 'NHẠC TIỀN CHIẾN']], 'Một bên hướng tới tập thể, một bên hướng tới tâm sự cá nhân.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Nhạc cách mạng ra đời trong các cuộc kháng chiến', 'ĐÚNG'], ['Nhạc tiền chiến còn gọi là nhạc lãng mạn', 'ĐÚNG'], ['Nhạc tiền chiến ra đời sau năm 1975', 'SAI'], ['Nhạc cách mạng chủ yếu viết về tình yêu đôi lứa', 'SAI']], 'Nhạc tiền chiến ra đời trước 1945; nhạc cách mạng chủ yếu ca ngợi quê hương, cổ vũ chiến đấu.', $d);
        $this->sortQ($s, 'Xếp mỗi hình thức biểu diễn vào nhóm MỘT NGƯỜI hoặc NHIỀU NGƯỜI.', [['Đơn ca', 'MỘT NGƯỜI'], ['Độc tấu', 'MỘT NGƯỜI'], ['Hợp xướng', 'NHIỀU NGƯỜI'], ['Tốp ca', 'NHIỀU NGƯỜI']], 'Đơn ca, độc tấu tôn vinh cá nhân; hợp xướng, tốp ca tạo sức mạnh tập thể.', $d);
        $this->sortQ($s, 'Xếp mỗi nội dung vào nhóm CA NGỢI TẬP THỂ hoặc TÂM SỰ CÁ NHÂN.', [['Quê hương đất nước', 'CA NGỢI TẬP THỂ'], ['Người chiến sĩ chiến đấu', 'CA NGỢI TẬP THỂ'], ['Nỗi nhớ người yêu', 'TÂM SỰ CÁ NHÂN'], ['Tâm trạng cô đơn', 'TÂM SỰ CÁ NHÂN']], 'Nội dung ca ngợi tập thể thuộc nhạc cách mạng, tâm sự cá nhân thuộc nhạc tiền chiến.', $d);
        $this->sortQ($s, 'Xếp mỗi thời kỳ vào nhóm TRƯỚC 1945 hoặc 1945–1975.', [['Nhạc tiền chiến nở rộ', 'TRƯỚC 1945'], ['Nhạc lãng mạn đô thị', 'TRƯỚC 1945'], ['Nhạc kháng chiến chống Pháp', '1945–1975'], ['Nhạc kháng chiến chống Mỹ', '1945–1975']], 'Mốc 1945 chia hai dòng nhạc: lãng mạn đô thị trước đó, cách mạng kháng chiến sau đó.', $d);

        $this->fill($s, 'Nhạc tiền chiến còn được gọi là nhạc ___ mạn.', [[0, 'lãng']], 'Chất lãng mạn là nét đặc trưng của dòng nhạc đô thị trước năm 1945.', $d);
        $this->fill($s, 'Giai điệu nhạc cách mạng thường hùng tráng để mọi người dễ hát ___ thể.', [[0, 'tập']], 'Tính tập thể là sức mạnh lan tỏa của nhạc cách mạng.', $d);
        $this->fill($s, 'Người viết nhạc cho ca khúc được gọi là nhạc ___.', [[0, 'sĩ']], 'Nhạc sĩ là người sáng tác phần nhạc của ca khúc.', $d);
        $this->fill($s, 'Hình thức nhiều người hát nhiều bè khác nhau gọi là hợp ___.', [[0, 'xướng']], 'Hợp xướng thường dùng trong các bài hát tập thể và lễ kỷ niệm.', $d);
        $this->fill($s, 'Nhạc cách mạng còn được gọi với cái tên thân thuộc là nhạc ___.', [[0, 'đỏ']], '"Nhạc đỏ" là tên gọi dân gian của dòng nhạc cách mạng Việt Nam.', $d);
    }

    private function seedNhacTreDanGianLop112(): void
    {
        $s = 'am-nhac-thpt-11-lop-11-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Bolero có nguồn gốc từ đâu trước khi phổ biến ở Việt Nam?', ['Mỹ Latinh', 'Châu Âu', 'Nhật Bản', 'Trung Đông'], 0, 'Bolero có gốc từ Mỹ Latinh, du nhập vào Việt Nam và được Việt hóa thành dòng nhạc trữ tình đặc sắc.', $d);
        $this->quiz($s, 'Nhạc trẻ Việt Nam hiện nay thường được sản xuất bằng công cụ nào?', ['Chỉ nhạc cụ dân tộc', 'Phần mềm làm nhạc trên máy tính', 'Chỉ ghi âm trực tiếp một lần', 'Không dùng công cụ nào'], 1, 'Nhạc trẻ hiện đại chủ yếu được sản xuất bằng phần mềm làm nhạc kết hợp thu âm nhạc cụ thật.', $d);
        $this->quiz($s, 'Dòng nhạc dân gian đương đại khác dân ca truyền thống ở điểm nào?', ['Không dùng chất liệu dân ca', 'Phối chất liệu dân ca với hòa âm, nhạc cụ hiện đại', 'Chỉ hát không có nhạc đệm', 'Không có lời ca'], 1, 'Dân gian đương đại giữ hồn dân ca nhưng khoác lên hòa âm và cách phối khí hiện đại.', $d);
        $this->quiz($s, 'Yếu tố nào quan trọng nhất tạo nên bản "hit" trong nhạc trẻ?', ['Giai điệu dễ nhớ, bắt tai', 'Bài hát thật dài', 'Dùng thật nhiều nhạc cụ', 'Hát thật to'], 0, 'Giai điệu bắt tai, dễ nhớ là yếu tố then chốt khiến bài hát lan tỏa nhanh.', $d);
        $this->quiz($s, 'Trong ê-kíp làm nhạc trẻ, người chịu trách nhiệm phối khí, tạo bản nhạc nền gọi là gì?', ['Ca sĩ', 'Producer (nhà sản xuất âm nhạc)', 'Quản lý', 'Khán giả'], 1, 'Producer là người định hình âm thanh, phối khí và sản xuất bản thu hoàn chỉnh.', $d);

        $this->matching($s, 'Nối mỗi dòng nhạc với nhịp điệu đặc trưng của nó.', [['Bolero', 'Chậm, đều đặn'], ['Nhạc pop', 'Đa dạng, bắt tai'], ['Dân gian đương đại', 'Kết hợp dân ca và hiện đại'], ['Ballad', 'Chậm, giàu cảm xúc']], 'Nhịp điệu là dấu hiệu dễ nhận biết nhất của mỗi dòng nhạc.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ nhạc trẻ với ý nghĩa của nó.', [['Producer', 'Người sản xuất, phối khí bài hát'], ['Beat', 'Nền nhạc, nhịp điệu của bài'], ['Hook', 'Đoạn giai điệu bắt tai nhất'], ['Cover', 'Hát lại bài của người khác']], 'Thuật ngữ nhạc trẻ phần lớn du nhập từ tiếng Anh cùng với công nghệ làm nhạc.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với vai trò trong ban nhạc trẻ.', [['Guitar điện', 'Giai điệu, hợp âm chính'], ['Trống', 'Giữ nhịp, tạo năng lượng'], ['Bass', 'Âm trầm, nối nhịp và hòa âm'], ['Keyboard', 'Đệm hòa âm, tạo hiệu ứng']], 'Mỗi nhạc cụ đảm nhận một vai trò riêng tạo nên âm thanh đầy đặn của ban nhạc.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với dòng nhạc tương ứng.', [['Lời ca giản dị, gần gũi đời thường', 'Nhạc trẻ'], ['Giai điệu mượt mà, da diết', 'Nhạc trữ tình'], ['Dùng đàn bầu, sáo phối hiện đại', 'Dân gian đương đại'], ['Tiết tấu sôi động', 'Nhạc trẻ']], 'Mỗi dòng nhạc có chất liệu và cách thể hiện riêng không thể lẫn.', $d);
        $this->matching($s, 'Nối mỗi kênh với vai trò lan tỏa nhạc trẻ.', [['Mạng xã hội', 'Lan tỏa nhanh qua video ngắn'], ['Nền tảng nhạc số', 'Nghe trực tuyến mọi lúc'], ['Liveshow', 'Gặp gỡ khán giả trực tiếp'], ['Radio', 'Phát sóng tới đại chúng']], 'Nhạc trẻ lan tỏa mạnh nhờ kết hợp nhiều kênh truyền thông hiện đại.', $d);

        $this->sortQ($s, 'Xếp mỗi đặc điểm vào nhóm NHẠC TRỮ TÌNH hoặc NHẠC TRẺ.', [['Giai điệu chậm, da diết', 'NHẠC TRỮ TÌNH'], ['Lời ca sướt mướt về tình yêu', 'NHẠC TRỮ TÌNH'], ['Tiết tấu sôi động, hiện đại', 'NHẠC TRẺ'], ['Giai điệu bắt tai, dễ nhớ', 'NHẠC TRẺ']], 'Trữ tình chậm rãi, sâu lắng; nhạc trẻ sôi động, gần gũi với giới trẻ.', $d);
        $this->sortQ($s, 'Xếp mỗi yếu tố vào nhóm TRUYỀN THỐNG hoặc HIỆN ĐẠI.', [['Đàn bầu', 'TRUYỀN THỐNG'], ['Làn điệu dân ca', 'TRUYỀN THỐNG'], ['Phần mềm làm nhạc', 'HIỆN ĐẠI'], ['Guitar điện', 'HIỆN ĐẠI']], 'Dân gian đương đại chính là cuộc gặp gỡ giữa hai nhóm yếu tố này.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Bolero có gốc từ Mỹ Latinh', 'ĐÚNG'], ['Dân gian đương đại phối dân ca với hòa âm hiện đại', 'ĐÚNG'], ['Nhạc trẻ không bao giờ dùng nhạc cụ dân tộc', 'SAI'], ['Hook là đoạn khó nghe nhất của bài hát', 'SAI']], 'Nhiều bài nhạc trẻ dùng đàn bầu, sáo rất thành công; hook là đoạn bắt tai nhất.', $d);
        $this->sortQ($s, 'Xếp mỗi công việc vào nhóm TRƯỚC hoặc SAU khi bài hát phát hành.', [['Sáng tác giai điệu', 'TRƯỚC'], ['Phối khí, thu âm', 'TRƯỚC'], ['Quảng bá trên mạng xã hội', 'SAU'], ['Biểu diễn trong liveshow', 'SAU']], 'Một bài hát trải qua chuỗi công đoạn từ sáng tác tới quảng bá sau phát hành.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm THƯỜNG DÙNG TRONG BOLERO hoặc THƯỜNG DÙNG TRONG NHẠC TRẺ.', [['Guitar thùng', 'THƯỜNG DÙNG TRONG BOLERO'], ['Trống jazz', 'THƯỜNG DÙNG TRONG BOLERO'], ['Guitar điện', 'THƯỜNG DÙNG TRONG NHẠC TRẺ'], ['Synthesizer', 'THƯỜNG DÙNG TRONG NHẠC TRẺ']], 'Bolero chuộng âm thanh mộc mạc, nhạc trẻ chuộng âm thanh điện tử hiện đại.', $d);

        $this->fill($s, 'Bolero có nguồn gốc từ vùng ___ Latinh.', [[0, 'Mỹ']], 'Bolero gốc Mỹ Latinh, du nhập vào Việt Nam từ giữa thế kỷ 20.', $d);
        $this->fill($s, 'Người chịu trách nhiệm phối khí và sản xuất bài hát được gọi là ___.', [[0, 'producer']], 'Producer định hình âm thanh cuối cùng của bài hát.', $d);
        $this->fill($s, 'Đoạn giai điệu bắt tai nhất, dễ nhớ nhất của bài hát được gọi là ___.', [[0, 'hook']], 'Hook là "cái móc" níu tai người nghe, quyết định độ lan tỏa của bài hát.', $d);
        $this->fill($s, 'Hát lại bài hát của người khác theo phong cách của mình được gọi là ___.', [[0, 'cover']], 'Cover là hình thức phổ biến để ca sĩ trẻ thể hiện cá tính riêng.', $d);
        $this->fill($s, 'Nền nhạc và nhịp điệu điện tử của bài hát hiện đại thường được gọi là ___.', [[0, 'beat']], 'Beat là khung nhịp điệu mà ca sĩ hát theo.', $d);
    }

    private function seedNhacSiLop113(): void
    {
        $s = 'am-nhac-thpt-11-lop-11-3';
        $d = 'trung_binh';

        $this->quiz($s, 'Nhạc sĩ phối khí (arranger) làm công việc gì?', ['Viết lời bài hát', 'Sắp xếp các bè nhạc cụ cho ca khúc', 'Hát chính trong ban nhạc', 'Quay video ca nhạc'], 1, 'Người phối khí quyết định mỗi nhạc cụ chơi gì, tạo nên màu sắc âm thanh của bản thu.', $d);
        $this->quiz($s, 'Khi một ca khúc bị dùng trong phim mà không xin phép, nhạc sĩ bị xâm phạm quyền gì?', ['Quyền đi lại', 'Quyền tác giả (tác quyền)', 'Quyền bầu cử', 'Quyền sở hữu nhà cửa'], 1, 'Tác quyền bảo vệ quyền lợi của người sáng tác khi tác phẩm bị sử dụng trái phép.', $d);
        $this->quiz($s, 'Demo trong quá trình sáng tác nhạc là gì?', ['Bản thu thử để nghe và chỉnh sửa', 'Buổi biểu diễn chính thức', 'Hợp đồng phát hành', 'Giải thưởng âm nhạc'], 0, 'Demo là bản thu nháp giúp nhạc sĩ nghe lại và chỉnh sửa trước khi thu chính thức.', $d);
        $this->quiz($s, 'Nhạc sĩ thường bắt đầu sáng tác một ca khúc từ yếu tố nào?', ['Tiền bản quyền', 'Giai điệu, lời ca hoặc một cảm xúc', 'Trang phục biểu diễn', 'Số lượng khán giả'], 1, 'Cảm hứng sáng tác thường đến từ giai điệu vang trong đầu, câu chữ hay một cảm xúc mạnh.', $d);
        $this->quiz($s, 'Ca sĩ hát nhạc của người khác mà không ghi tên tác giả là thiếu tôn trọng ai?', ['Khán giả', 'Nhạc sĩ sáng tác', 'Chủ phòng trà', 'Người quay phim'], 1, 'Ghi tên tác giả là sự tôn trọng tối thiểu dành cho người đã sáng tác ra ca khúc.', $d);

        $this->matching($s, 'Nối mỗi vai trò với công việc của họ.', [['Nhạc sĩ sáng tác', 'Viết giai điệu và hòa âm'], ['Người viết lời', 'Viết ca từ'], ['Nhạc sĩ phối khí', 'Sắp xếp các bè nhạc cụ'], ['Producer', 'Giám sát toàn bộ bản thu']], 'Một ca khúc hoàn chỉnh là công sức phối hợp của nhiều vai trò khác nhau.', $d);
        $this->matching($s, 'Nối mỗi công đoạn sáng tác với mô tả của nó.', [['Lên ý tưởng', 'Tìm cảm hứng và chủ đề'], ['Viết demo', 'Thu bản nháp để nghe thử'], ['Phối khí', 'Sắp xếp các nhạc cụ'], ['Thu âm chính thức', 'Thu bản hoàn chỉnh phát hành']], 'Ca khúc đi từ ý tưởng tới bản phát hành qua nhiều công đoạn nối tiếp.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.', [['Tác quyền', 'Quyền lợi của người sáng tác'], ['Demo', 'Bản thu nháp'], ['Hit', 'Bài hát được yêu thích rộng rãi'], ['Album', 'Tuyển tập nhiều ca khúc']], 'Nắm khái niệm giúp hiểu quy trình làm nhạc chuyên nghiệp.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với vai trò của nó trong thành công của bài hát.', [['Giai điệu hay', 'Níu tai người nghe'], ['Lời ca ý nghĩa', 'Chạm tới cảm xúc'], ['Phối khí tốt', 'Tạo màu sắc âm thanh'], ['Ca sĩ thể hiện', 'Truyền tải hồn bài hát']], 'Bài hát thành công là sự cộng hưởng của giai điệu, lời ca, phối khí và giọng hát.', $d);
        $this->matching($s, 'Nối mỗi hành động với thái độ nó thể hiện.', [['Ghi tên tác giả khi hát', 'Tôn trọng nhạc sĩ'], ['Xin phép khi dùng nhạc', 'Tôn trọng tác quyền'], ['Trả nhuận bút đầy đủ', 'Tôn trọng công sức'], ['Tự ý sửa lời bài hát', 'Thiếu tôn trọng']], 'Tôn trọng tác quyền là tôn trọng người đã sáng tạo ra âm nhạc.', $d);

        $this->sortQ($s, 'Xếp mỗi công việc vào nhóm của NHẠC SĨ hoặc CA SĨ.', [['Viết giai điệu', 'NHẠC SĨ'], ['Viết hòa âm', 'NHẠC SĨ'], ['Thể hiện trên sân khấu', 'CA SĨ'], ['Truyền cảm xúc qua giọng hát', 'CA SĨ']], 'Nhạc sĩ tạo ra tác phẩm, ca sĩ thổi hồn vào tác phẩm bằng giọng hát.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Nhạc sĩ là người sáng tác ra ca khúc', 'ĐÚNG'], ['Ca sĩ và nhạc sĩ có thể là một người', 'ĐÚNG'], ['Dùng nhạc không xin phép là tôn trọng tác quyền', 'SAI'], ['Phối khí là công việc của ca sĩ', 'SAI']], 'Phối khí là việc của nhạc sĩ phối khí; dùng nhạc phải xin phép mới là tôn trọng tác quyền.', $d);
        $this->sortQ($s, 'Xếp mỗi công đoạn vào nhóm SÁNG TÁC hoặc PHÁT HÀNH.', [['Viết giai điệu', 'SÁNG TÁC'], ['Thu demo', 'SÁNG TÁC'], ['Quảng bá MV', 'PHÁT HÀNH'], ['Biểu diễn liveshow', 'PHÁT HÀNH']], 'Sáng tác tạo ra tác phẩm, phát hành đưa tác phẩm tới khán giả.', $d);
        $this->sortQ($s, 'Xếp mỗi yếu tố vào nhóm THUỘC VỀ TÁC GIẢ hoặc THUỘC VỀ NGƯỜI HÁT.', [['Giai điệu', 'THUỘC VỀ TÁC GIẢ'], ['Lời ca', 'THUỘC VỀ TÁC GIẢ'], ['Giọng hát', 'THUỘC VỀ NGƯỜI HÁT'], ['Phong cách trình diễn', 'THUỘC VỀ NGƯỜI HÁT']], 'Tác giả sở hữu phần sáng tạo, người hát sở hữu phần thể hiện.', $d);
        $this->sortQ($s, 'Xếp mỗi hành vi vào nhóm TÔN TRỌNG hoặc XÂM PHẠM tác quyền.', [['Xin phép trước khi dùng nhạc', 'TÔN TRỌNG'], ['Ghi tên tác giả đầy đủ', 'TÔN TRỌNG'], ['Tự ý dùng nhạc không xin phép', 'XÂM PHẠM'], ['Nhận bài hát của người khác là của mình', 'XÂM PHẠM']], 'Tôn trọng tác quyền là đạo đức nghề nghiệp cơ bản trong âm nhạc.', $d);

        $this->fill($s, 'Bản thu nháp để nghe thử và chỉnh sửa trước khi thu chính thức gọi là ___.', [[0, 'demo']], 'Demo giúp nhạc sĩ hoàn thiện ca khúc trước khi ra bản chính thức.', $d);
        $this->fill($s, 'Người sắp xếp các bè nhạc cụ cho ca khúc gọi là nhạc sĩ phối ___.', [[0, 'khí']], 'Phối khí quyết định màu sắc âm thanh của bản thu.', $d);
        $this->fill($s, '___ là quyền lợi hợp pháp của người sáng tác khi tác phẩm bị sử dụng.', [[0, 'Tác quyền']], 'Tác quyền bảo vệ công sức sáng tạo của nhạc sĩ.', $d);
        $this->fill($s, 'Tuyển tập nhiều ca khúc phát hành cùng nhau gọi là ___.', [[0, 'album']], 'Album thường gồm nhiều ca khúc có chủ đề liên kết với nhau.', $d);
        $this->fill($s, 'Khi hát nhạc của người khác, cần ghi tên ___ giả để tôn trọng họ.', [[0, 'tác']], 'Ghi tên tác giả là phép lịch sự tối thiểu trong âm nhạc.', $d);
    }

    private function seedTheHeNhacSiLop114(): void
    {
        $s = 'am-nhac-thpt-11-lop-11-4';
        $d = 'kho';

        $this->quiz($s, 'Điểm chung của thế hệ nhạc sĩ tiền chiến (trước 1945) là gì?', ['Chỉ sáng tác nhạc cách mạng', 'Đặt nền móng cho tân nhạc Việt Nam với kỹ thuật phương Tây', 'Chỉ viết nhạc cho phim', 'Không biết nhạc lý'], 1, 'Thế hệ tiền chiến tiếp thu kỹ thuật âm nhạc phương Tây, đặt nền móng cho tân nhạc Việt Nam.', $d);
        $this->quiz($s, 'Thế hệ nhạc sĩ thời kháng chiến chống Pháp, chống Mỹ có đóng góp nổi bật nào?', ['Phát triển nhạc điện tử', 'Sáng tác kho tàng nhạc cách mạng và hành khúc', 'Chỉ sáng tác nhạc thiếu nhi', 'Du nhập nhạc rock vào Việt Nam'], 1, 'Thế hệ kháng chiến để lại kho tàng nhạc cách mạng, hành khúc cổ vũ tinh thần chiến đấu.', $d);
        $this->quiz($s, 'Sau năm 1975, đời sống âm nhạc Việt Nam có chuyển biến gì?', ['Âm nhạc ngừng phát triển', 'Đất nước thống nhất, nhiều dòng nhạc phát triển và giao lưu rộng', 'Chỉ còn nhạc cách mạng', 'Mọi loại nhạc trẻ đều bị cấm'], 1, 'Sau 1975, đất nước thống nhất tạo điều kiện cho nhiều dòng nhạc phát triển và giao lưu.', $d);
        $this->quiz($s, 'Thế hệ nhạc sĩ đương đại (sau Đổi mới) có đặc điểm nổi bật nào?', ['Chỉ sáng tác theo phong cách cũ', 'Tiếp cận công nghệ, hội nhập quốc tế, phong cách đa dạng', 'Không dùng nhạc cụ điện tử', 'Chỉ hát nhạc nước ngoài'], 1, 'Thế hệ đương đại làm nhạc bằng công nghệ số, phong cách đa dạng và hội nhập quốc tế.', $d);
        $this->quiz($s, 'Vì sao cần bảo tồn và phát huy âm nhạc của các thế hệ đi trước?', ['Để kiếm tiền bản quyền', 'Vì đó là di sản văn hóa và nguồn cảm hứng cho thế hệ sau', 'Vì pháp luật bắt buộc', 'Để cấm nhạc mới ra đời'], 1, 'Âm nhạc các thế hệ trước là di sản văn hóa dân tộc và nguồn cảm hứng vô tận cho sáng tạo mới.', $d);

        $this->matching($s, 'Nối mỗi thế hệ nhạc sĩ với đóng góp tiêu biểu của họ.', [['Tiền chiến', 'Đặt nền móng tân nhạc'], ['Kháng chiến', 'Kho tàng nhạc cách mạng'], ['Sau 1975', 'Đa dạng dòng nhạc, thống nhất'], ['Đương đại', 'Công nghệ số, hội nhập']], 'Mỗi thế hệ để lại một dấu ấn riêng trong dòng chảy âm nhạc Việt Nam.', $d);
        $this->matching($s, 'Nối mỗi thời kỳ với đặc điểm âm nhạc của nó.', [['Trước 1945', 'Nhạc lãng mạn đô thị'], ['1945–1954', 'Nhạc kháng chiến chống Pháp'], ['1954–1975', 'Nhạc cách mạng hai miền'], ['Sau 1986', 'Đổi mới, nhạc trẻ nở rộ']], 'Dòng chảy âm nhạc luôn song hành với dòng chảy lịch sử dân tộc.', $d);
        $this->matching($s, 'Nối mỗi khái niệm với ý nghĩa của nó.', [['Tân nhạc', 'Nhạc mới theo kỹ thuật phương Tây'], ['Nhạc đỏ', 'Nhạc cách mạng'], ['Nhạc vàng', 'Nhạc trữ tình miền Nam trước 1975'], ['Nhạc xanh', 'Nhạc trẻ hiện đại']], 'Tên gọi các dòng nhạc phản ánh thời đại và không gian ra đời của chúng.', $d);
        $this->matching($s, 'Nối mỗi hành động với ý nghĩa của nó trong sự phát triển âm nhạc.', [['Sáng tác mới', 'Làm giàu kho tàng âm nhạc'], ['Bảo tồn di sản', 'Giữ gìn giá trị cũ'], ['Đào tạo thế hệ trẻ', 'Nối tiếp truyền thống'], ['Giao lưu quốc tế', 'Quảng bá âm nhạc Việt']], 'Phát triển âm nhạc cần cả sáng tạo mới và gìn giữ giá trị cũ.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với thế hệ chịu ảnh hưởng mạnh nhất từ nó.', [['Kỹ thuật hòa âm phương Tây', 'Tiền chiến'], ['Hoàn cảnh chiến tranh', 'Kháng chiến'], ['Công nghệ thu âm số', 'Đương đại'], ['Thống nhất đất nước', 'Sau 1975']], 'Hoàn cảnh thời đại in dấu đậm nét lên sáng tạo của mỗi thế hệ nhạc sĩ.', $d);

        $this->sortQ($s, 'Xếp mỗi dòng nhạc vào nhóm TRƯỚC 1975 hoặc SAU 1975.', [['Nhạc tiền chiến', 'TRƯỚC 1975'], ['Nhạc kháng chiến', 'TRƯỚC 1975'], ['Nhạc trẻ hiện đại', 'SAU 1975'], ['Dân gian đương đại', 'SAU 1975']], 'Mốc 1975 là ranh giới quan trọng trong lịch sử âm nhạc Việt Nam hiện đại.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Thế hệ tiền chiến đặt nền móng cho tân nhạc Việt Nam', 'ĐÚNG'], ['Thế hệ đương đại hội nhập quốc tế', 'ĐÚNG'], ['Thế hệ kháng chiến chỉ sáng tác nhạc thiếu nhi', 'SAI'], ['Sau 1975 âm nhạc Việt Nam ngừng phát triển', 'SAI']], 'Thế hệ kháng chiến sáng tác nhiều thể loại; sau 1975 âm nhạc càng phát triển đa dạng.', $d);
        $this->sortQ($s, 'Xếp mỗi hoạt động vào nhóm BẢO TỒN hoặc SÁNG TẠO.', [['Sưu tầm ca khúc cũ', 'BẢO TỒN'], ['Phục dựng bản nhạc xưa', 'BẢO TỒN'], ['Sáng tác ca khúc mới', 'SÁNG TẠO'], ['Phối lại nhạc cũ theo phong cách mới', 'SÁNG TẠO']], 'Bảo tồn giữ gìn quá khứ, sáng tạo mở ra tương lai — cả hai đều cần thiết.', $d);
        $this->sortQ($s, 'Xếp mỗi đặc điểm vào nhóm THẾ HỆ KHÁNG CHIẾN hoặc THẾ HỆ ĐƯƠNG ĐẠI.', [['Giai điệu hùng tráng cổ vũ chiến đấu', 'THẾ HỆ KHÁNG CHIẾN'], ['Sáng tác trong hoàn cảnh chiến tranh', 'THẾ HỆ KHÁNG CHIẾN'], ['Dùng công nghệ số để sản xuất', 'THẾ HỆ ĐƯƠNG ĐẠI'], ['Phong cách đa dạng, hội nhập', 'THẾ HỆ ĐƯƠNG ĐẠI']], 'Hoàn cảnh thời đại tạo nên dấu ấn khác biệt của mỗi thế hệ.', $d);
        $this->sortQ($s, 'Xếp mỗi tên gọi dòng nhạc vào nhóm MIỀN BẮC hoặc MIỀN NAM trước 1975.', [['Nhạc đỏ', 'MIỀN BẮC'], ['Nhạc cách mạng', 'MIỀN BẮC'], ['Nhạc vàng', 'MIỀN NAM'], ['Nhạc trữ tình Sài Gòn xưa', 'MIỀN NAM']], 'Trước 1975, hai miền có hai dòng nhạc chủ đạo: nhạc đỏ ở miền Bắc, nhạc vàng ở miền Nam.', $d);

        $this->fill($s, 'Dòng nhạc mới theo kỹ thuật phương Tây đầu thế kỷ 20 được gọi là ___ nhạc.', [[0, 'tân']], 'Tân nhạc ra đời từ thế hệ nhạc sĩ tiền chiến tiếp thu âm nhạc phương Tây.', $d);
        $this->fill($s, 'Thế hệ kháng chiến để lại kho tàng hành ___ cổ vũ tinh thần chiến đấu.', [[0, 'khúc']], 'Hành khúc với nhịp bước đều, giai điệu hùng tráng là vũ khí tinh thần.', $d);
        $this->fill($s, 'Sau năm ___, đất nước thống nhất mở ra thời kỳ mới cho âm nhạc Việt Nam.', [[0, '1975']], 'Mốc 1975 đánh dấu bước ngoặt của đời sống âm nhạc Việt Nam.', $d);
        $this->fill($s, 'Thế hệ đương đại sản xuất âm nhạc chủ yếu trên máy ___.', [[0, 'tính']], 'Máy tính với phần mềm làm nhạc là công cụ chủ yếu của nhạc sĩ đương đại.', $d);
        $this->fill($s, 'Giữ gìn các giá trị âm nhạc cũ cho thế hệ sau được gọi là bảo ___.', [[0, 'tồn']], 'Bảo tồn di sản âm nhạc là trách nhiệm của mỗi thế hệ.', $d);
    }

    private function seedJazzBluesLop121(): void
    {
        $s = 'am-nhac-thpt-12-lop-12-1';
        $d = 'trung_binh';

        $this->quiz($s, 'Nhạc cổ điển châu Âu phát triển rực rỡ nhất vào thời kỳ nào?', ['Thời kỳ Baroque – Cổ điển – Lãng mạn (thế kỷ 17–19)', 'Thời tiền sử', 'Thế kỷ 21', 'Thời Trung cổ sơ khai'], 0, 'Ba thời kỳ Baroque, Cổ điển, Lãng mạn (thế kỷ 17–19) là đỉnh cao của nhạc cổ điển châu Âu.', $d);
        $this->quiz($s, 'Ứng tấu (improvisation) trong jazz có nghĩa là gì?', ['Chơi đúng từng nốt trong bản nhạc', 'Sáng tạo giai điệu ngay khi biểu diễn', 'Hát không có lời', 'Chơi thật nhanh'], 1, 'Ứng tấu là linh hồn của jazz — nghệ sĩ sáng tạo giai điệu trực tiếp trên sân khấu.', $d);
        $this->quiz($s, 'Blues có nguồn gốc từ cộng đồng nào ở Mỹ?', ['Người nhập cư châu Âu', 'Cộng đồng người Mỹ gốc Phi', 'Người bản địa châu Mỹ', 'Người nhập cư châu Á'], 1, 'Blues ra đời từ những bài hát lao động và tâm sự của cộng đồng người Mỹ gốc Phi.', $d);
        $this->quiz($s, 'Nhạc cụ nào được xem là biểu tượng của dàn nhạc jazz truyền thống?', ['Đàn tranh', 'Kèn trumpet và saxophone', 'Trống cơm', 'Sáo'], 1, 'Kèn trumpet và saxophone với khả năng ứng tấu tuyệt vời là biểu tượng của jazz.', $d);
        $this->quiz($s, 'Điểm khác biệt lớn nhất giữa nhạc cổ điển và jazz là gì?', ['Nhạc cổ điển dùng nhạc cụ điện', 'Jazz đề cao ứng tấu, nhạc cổ điển tuân thủ bản nhạc', 'Nhạc cổ điển không có giai điệu', 'Jazz không có nhịp điệu'], 1, 'Nhạc cổ điển tôn trọng tuyệt đối bản nhạc, jazz tôn vinh sự sáng tạo ứng tấu của nghệ sĩ.', $d);

        $this->matching($s, 'Nối mỗi thể loại với quê hương của nó.', [['Nhạc cổ điển', 'Châu Âu'], ['Jazz', 'Nước Mỹ'], ['Blues', 'Miền Nam nước Mỹ'], ['Nhạc bác học', 'Tên gọi khác của nhạc cổ điển']], 'Mỗi thể loại mang đậm dấu ấn của vùng đất nơi nó ra đời.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa của nó.', [['Ứng tấu', 'Sáng tạo giai điệu khi biểu diễn'], ['Swing', 'Tiết tấu nhún nhảy của jazz'], ['Blues 12 ô nhịp', 'Cấu trúc chuẩn của blues'], ['Giao hưởng', 'Tác phẩm lớn cho dàn nhạc']], 'Thuật ngữ là chìa khóa để nghe và hiểu đúng mỗi thể loại.', $d);
        $this->matching($s, 'Nối mỗi nhạc cụ với thể loại nó gắn bó nhất.', [['Violin', 'Nhạc cổ điển'], ['Saxophone', 'Jazz'], ['Guitar điện', 'Blues và rock'], ['Piano', 'Cả ba thể loại']], 'Piano đa năng xuất hiện trong cả nhạc cổ điển, jazz và blues.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với thể loại tương ứng.', [['Tuân thủ nghiêm bản nhạc', 'Nhạc cổ điển'], ['Ứng tấu tự do', 'Jazz'], ['12 ô nhịp, tâm sự buồn', 'Blues'], ['Dàn nhạc giao hưởng lớn', 'Nhạc cổ điển']], 'Mỗi thể loại có một "tính cách" âm nhạc riêng không thể lẫn.', $d);
        $this->matching($s, 'Nối mỗi hình thức biểu diễn với mô tả của nó.', [['Độc tấu piano', 'Một người chơi piano'], ['Tứ tấu jazz', 'Bốn nhạc công jazz'], ['Dàn nhạc giao hưởng', 'Hàng chục nhạc công cổ điển'], ['Ban nhạc blues', 'Nhóm nhạc công chơi blues']], 'Quy mô biểu diễn phản ánh tính chất của từng thể loại.', $d);

        $this->sortQ($s, 'Xếp mỗi đặc điểm vào nhóm NHẠC CỔ ĐIỂN hoặc JAZZ.', [['Tuân thủ bản nhạc nghiêm ngặt', 'NHẠC CỔ ĐIỂN'], ['Dàn nhạc giao hưởng', 'NHẠC CỔ ĐIỂN'], ['Ứng tấu tự do', 'JAZZ'], ['Tiết tấu swing', 'JAZZ']], 'Cổ điển kỷ luật với bản nhạc, jazz tự do với ứng tấu.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm GẮN VỚI JAZZ hoặc GẮN VỚI NHẠC CỔ ĐIỂN.', [['Saxophone', 'GẮN VỚI JAZZ'], ['Trumpet', 'GẮN VỚI JAZZ'], ['Violin', 'GẮN VỚI NHẠC CỔ ĐIỂN'], ['Cello', 'GẮN VỚI NHẠC CỔ ĐIỂN']], 'Kèn đồng, saxophone là hơi thở của jazz; bộ dây là nền tảng của nhạc cổ điển.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Jazz đề cao ứng tấu của nghệ sĩ', 'ĐÚNG'], ['Blues có cấu trúc 12 ô nhịp điển hình', 'ĐÚNG'], ['Nhạc cổ điển ra đời ở châu Mỹ', 'SAI'], ['Blues có nguồn gốc từ châu Âu', 'SAI']], 'Nhạc cổ điển ra đời ở châu Âu; blues có nguồn gốc từ cộng đồng người Mỹ gốc Phi.', $d);
        $this->sortQ($s, 'Xếp mỗi thể loại vào nhóm CHÂU ÂU hoặc CHÂU MỸ.', [['Nhạc cổ điển', 'CHÂU ÂU'], ['Opera', 'CHÂU ÂU'], ['Jazz', 'CHÂU MỸ'], ['Blues', 'CHÂU MỸ']], 'Nhạc cổ điển và opera từ châu Âu; jazz và blues từ châu Mỹ.', $d);
        $this->sortQ($s, 'Xếp mỗi yếu tố vào nhóm ĐẶC TRƯNG CỦA JAZZ hoặc ĐẶC TRƯNG CỦA BLUES.', [['Tiết tấu swing', 'ĐẶC TRƯNG CỦA JAZZ'], ['Ứng tấu', 'ĐẶC TRƯNG CỦA JAZZ'], ['Cấu trúc 12 ô nhịp', 'ĐẶC TRƯNG CỦA BLUES'], ['Tâm sự buồn', 'ĐẶC TRƯNG CỦA BLUES']], 'Jazz phóng khoáng với swing và ứng tấu; blues trầm lắng với cấu trúc 12 ô nhịp.', $d);

        $this->fill($s, 'Linh hồn của jazz là nghệ thuật ứng ___ của nghệ sĩ.', [[0, 'tấu']], 'Ứng tấu — sáng tạo giai điệu ngay trên sân khấu — là nét đặc sắc nhất của jazz.', $d);
        $this->fill($s, 'Tiết tấu nhún nhảy đặc trưng của jazz được gọi là ___.', [[0, 'swing']], 'Swing tạo cảm giác nhún nhảy, lôi cuốn đặc trưng của jazz.', $d);
        $this->fill($s, 'Blues ra đời từ cộng đồng người Mỹ gốc ___.', [[0, 'Phi']], 'Blues bắt nguồn từ những bài hát lao động của người Mỹ gốc Phi.', $d);
        $this->fill($s, 'Bài blues thường được viết trên nền hòa âm ___ ô nhịp.', [[0, '12']], 'Vòng hòa âm 12 ô nhịp là khung chuẩn của hầu hết các bài blues.', $d);
        $this->fill($s, 'Nhạc cổ điển châu Âu đạt đỉnh cao từ thế kỷ 17 đến thế kỷ ___.', [[0, '19']], 'Ba thời kỳ Baroque, Cổ điển và Lãng mạn kéo dài từ thế kỷ 17 đến thế kỷ 19.', $d);
    }

    private function seedRockPopHipHopLop122(): void
    {
        $s = 'am-nhac-thpt-12-lop-12-2';
        $d = 'trung_binh';

        $this->quiz($s, 'Rap trong hip-hop khác hát thông thường ở điểm nào?', ['Rap là nói có nhịp điệu, gieo vần trên nền beat', 'Rap không có lời', 'Rap chỉ dùng nhạc cụ dân tộc', 'Rap hát thật chậm'], 0, 'Rap là nói có nhịp điệu, gieo vần trên nền beat — hình thức thể hiện cốt lõi của hip-hop.', $d);
        $this->quiz($s, 'DJ trong nhạc điện tử làm công việc gì?', ['Chỉ ngồi nghe nhạc', 'Phối, trộn các bản nhạc và tạo hiệu ứng trực tiếp', 'Hát chính trên sân khấu', 'Bán vé cho khán giả'], 1, 'DJ trộn (mix) các bản nhạc, tạo hiệu ứng và dẫn dắt năng lượng của sàn nhảy.', $d);
        $this->quiz($s, 'Vì sao nhạc pop dễ tiếp cận đông đảo khán giả?', ['Vì phát miễn phí', 'Vì giai điệu đơn giản, dễ nhớ, chủ đề gần gũi', 'Vì chỉ phát trên truyền hình', 'Vì bài hát rất dài'], 1, 'Pop là viết tắt của popular — giai điệu đơn giản, bắt tai, chủ đề gần gũi nên ai cũng nghe được.', $d);
        $this->quiz($s, 'Ban nhạc rock điển hình gồm những nhạc cụ nào?', ['Sáo, đàn tranh, trống cơm', 'Guitar điện, bass, trống và giọng hát', 'Kèn trumpet, saxophone', 'Piano, violin, cello'], 1, 'Bộ tứ guitar điện – bass – trống – giọng hát là đội hình kinh điển của rock.', $d);
        $this->quiz($s, 'Festival âm nhạc điện tử thu hút khán giả chủ yếu nhờ yếu tố nào?', ['Âm thanh, ánh sáng và không khí sôi động', 'Ghế ngồi êm ái', 'Đồ ăn ngon', 'Vé vào cửa rẻ'], 0, 'EDM festival là trải nghiệm tổng hợp của âm thanh lớn, ánh sáng rực rỡ và đám đông cuồng nhiệt.', $d);

        $this->matching($s, 'Nối mỗi thể loại với đặc điểm của nó.', [['Rock', 'Guitar điện mạnh mẽ'], ['Pop', 'Giai điệu bắt tai, đại chúng'], ['Hip-hop', 'Rap trên nền beat'], ['EDM', 'Nhạc điện tử sôi động']], 'Mỗi thể loại hiện đại có một "chất" riêng không thể lẫn.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa của nó.', [['Rap', 'Nói có nhịp điệu, gieo vần'], ['DJ', 'Người trộn nhạc trực tiếp'], ['Beat', 'Nền nhịp điệu của bài'], ['Remix', 'Phối lại bản nhạc cũ']], 'Thuật ngữ nhạc hiện đại phần lớn có gốc tiếng Anh.', $d);
        $this->matching($s, 'Nối mỗi vai trò với công việc của họ.', [['Rapper', 'Rap và viết lời'], ['DJ', 'Trộn nhạc, tạo hiệu ứng'], ['Producer', 'Sản xuất bản thu'], ['Vocalist', 'Hát chính']], 'Mỗi vai trò góp một mảnh ghép vào thành công của sản phẩm âm nhạc.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với thể loại nó đặc trưng.', [['Guitar điện distortion', 'Rock'], ['Vòng hợp âm đơn giản', 'Pop'], ['Breakdance', 'Hip-hop'], ['Drop bùng nổ', 'EDM']], 'Nghe một yếu tố đặc trưng có thể đoán ra ngay thể loại của bài hát.', $d);
        $this->matching($s, 'Nối mỗi hình thức với mô tả của nó.', [['Liveshow', 'Biểu diễn trực tiếp'], ['MV', 'Video ca nhạc'], ['Festival', 'Lễ hội âm nhạc lớn'], ['Livestream', 'Phát trực tiếp trên mạng']], 'Nghệ sĩ hiện đại tiếp cận khán giả qua nhiều hình thức khác nhau.', $d);

        $this->sortQ($s, 'Xếp mỗi đặc điểm vào nhóm ROCK hoặc POP.', [['Guitar điện mạnh mẽ', 'ROCK'], ['Tiết tấu dồn dập', 'ROCK'], ['Giai điệu nhẹ nhàng, bắt tai', 'POP'], ['Hướng tới đại chúng', 'POP']], 'Rock mạnh mẽ, nổi loạn; pop nhẹ nhàng, gần gũi đại chúng.', $d);
        $this->sortQ($s, 'Xếp mỗi yếu tố vào nhóm HIP-HOP hoặc EDM.', [['Rap', 'HIP-HOP'], ['Breakdance', 'HIP-HOP'], ['DJ trộn nhạc', 'EDM'], ['Drop bùng nổ', 'EDM']], 'Hip-hop là văn hóa đường phố với rap và breakdance; EDM là âm nhạc của DJ và sàn nhảy.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Rock dùng guitar điện làm nhạc cụ chủ đạo', 'ĐÚNG'], ['EDM được tạo chủ yếu bằng máy tính', 'ĐÚNG'], ['Hip-hop ra đời từ nhạc cổ điển', 'SAI'], ['Nhạc pop kén người nghe, khó tiếp cận', 'SAI']], 'Hip-hop ra đời từ văn hóa đường phố Mỹ; pop chính là dòng nhạc đại chúng dễ nghe nhất.', $d);
        $this->sortQ($s, 'Xếp mỗi nhạc cụ vào nhóm ĐIỆN TỬ hoặc TRUYỀN THỐNG.', [['Synthesizer', 'ĐIỆN TỬ'], ['Máy trống điện tử', 'ĐIỆN TỬ'], ['Guitar thùng', 'TRUYỀN THỐNG'], ['Trống jazz', 'TRUYỀN THỐNG']], 'Nhạc cụ điện tử tạo âm thanh bằng mạch điện và phần mềm.', $d);
        $this->sortQ($s, 'Xếp mỗi thể loại vào nhóm THẾ KỶ 20 SỚM hoặc THẾ KỶ 20 MUỘN.', [['Jazz', 'THẾ KỶ 20 SỚM'], ['Blues', 'THẾ KỶ 20 SỚM'], ['Hip-hop', 'THẾ KỶ 20 MUỘN'], ['EDM', 'THẾ KỶ 20 MUỘN']], 'Jazz, blues ra đời đầu thế kỷ 20; hip-hop, EDM bùng nổ vào cuối thế kỷ 20.', $d);

        $this->fill($s, 'Hình thức nói có nhịp điệu, gieo vần trên nền beat được gọi là ___.', [[0, 'rap']], 'Rap là hình thức thể hiện cốt lõi của văn hóa hip-hop.', $d);
        $this->fill($s, 'Người trộn nhạc trực tiếp trong các buổi tiệc nhạc điện tử được gọi là ___.', [[0, 'DJ']], 'DJ dẫn dắt năng lượng sàn nhảy bằng cách trộn các bản nhạc.', $d);
        $this->fill($s, 'Pop là viết tắt của từ ___ có nghĩa là phổ biến, đại chúng.', [[0, 'popular']], 'Nhạc pop hướng tới đông đảo khán giả với giai điệu dễ nghe.', $d);
        $this->fill($s, 'Phối lại một bản nhạc cũ theo phong cách mới được gọi là ___.', [[0, 'remix']], 'Remix giúp bản nhạc cũ khoác diện mạo mới, rất phổ biến trong EDM.', $d);
        $this->fill($s, 'Đoạn bùng nổ mạnh nhất trong một bản EDM thường được gọi là ___.', [[0, 'drop']], 'Drop là khoảnh khắc cả sàn nhảy cùng bùng nổ theo nhạc.', $d);
    }

    private function seedGiaoHuongConcertoLop123(): void
    {
        $s = 'am-nhac-thpt-12-lop-12-3';
        $d = 'kho';

        $this->quiz($s, 'Trong concerto, đoạn độc tấu không có dàn nhạc đệm để khoe kỹ thuật gọi là gì?', ['Cadenza', 'Coda', 'Intro', 'Outro'], 0, 'Cadenza là đoạn độc tấu khoe kỹ thuật, thường xuất hiện gần cuối chương 1 của concerto.', $d);
        $this->quiz($s, 'Chương 1 của bản giao hưởng cổ điển thường viết ở hình thức nào?', ['Rondo', 'Sonata (hình thức xô-nát)', 'Biến tấu tự do', 'Không theo hình thức nào'], 1, 'Chương 1 giao hưởng cổ điển thường dùng hình thức sonata với 3 phần: trình bày – phát triển – tái hiện.', $d);
        $this->quiz($s, 'Dàn nhạc giao hưởng đầy đủ có khoảng bao nhiêu nhạc công?', ['5–10 người', '20–30 người', '70–100 người', '500 người'], 2, 'Dàn nhạc giao hưởng hiện đại có 70–100 nhạc công chia thành 4 bộ: dây, hơi gỗ, đồng và gõ.', $d);
        $this->quiz($s, 'Vì sao chương 2 của giao hưởng thường có tốc độ chậm?', ['Để nhạc công nghỉ tay', 'Để tạo sự tương phản, lắng đọng cảm xúc sau chương 1', 'Vì khán giả buồn ngủ', 'Vì nhà soạn nhạc hết ý tưởng'], 1, 'Chương chậm tạo sự tương phản và chiều sâu cảm xúc, là "trái tim" trữ tình của bản giao hưởng.', $d);
        $this->quiz($s, 'Concerto grosso khác concerto độc tấu ở điểm nào?', ['Không có dàn nhạc đệm', 'Một nhóm độc tấu đối thoại với dàn nhạc thay vì một người', 'Chỉ có một chương duy nhất', 'Không cần nhạc trưởng'], 1, 'Concerto grosso dùng một nhóm nhỏ độc tấu (concertino) đối thoại với toàn dàn nhạc.', $d);

        $this->matching($s, 'Nối mỗi chương giao hưởng với tính chất thường thấy của nó.', [['Chương 1', 'Nhanh, hình thức sonata'], ['Chương 2', 'Chậm, trữ tình'], ['Chương 3', 'Vũ khúc, uyển chuyển'], ['Chương 4', 'Nhanh, kết thúc rực rỡ']], 'Bốn chương giao hưởng như bốn hồi của một câu chuyện âm nhạc.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ với ý nghĩa của nó.', [['Cadenza', 'Đoạn độc tấu khoe kỹ thuật'], ['Coda', 'Đoạn kết'], ['Sonata', 'Hình thức 3 phần kinh điển'], ['Tutti', 'Cả dàn nhạc cùng chơi']], 'Thuật ngữ có gốc tiếng Ý được dùng thống nhất trong nhạc cổ điển toàn thế giới.', $d);
        $this->matching($s, 'Nối mỗi bộ với vai trò của nó trong dàn nhạc.', [['Bộ dây', 'Nền tảng, đông nhạc công nhất'], ['Bộ hơi gỗ', 'Màu sắc, giai điệu'], ['Bộ đồng', 'Sức mạnh, cao trào'], ['Bộ gõ', 'Nhịp điệu, điểm nhấn']], 'Bốn bộ nhạc cụ phối hợp tạo nên âm thanh đầy đặn của dàn nhạc giao hưởng.', $d);
        $this->matching($s, 'Nối mỗi loại concerto với đặc điểm của nó.', [['Concerto độc tấu', 'Một nhạc cụ đối thoại với dàn nhạc'], ['Concerto grosso', 'Nhóm nhỏ đối thoại với dàn nhạc'], ['Concerto cho piano', 'Loại concerto phổ biến nhất'], ['Concerto cho violin', 'Đòi hỏi kỹ thuật rất cao']], 'Concerto là cuộc đối thoại âm nhạc giữa cá nhân và tập thể.', $d);
        $this->matching($s, 'Nối mỗi giai đoạn của hình thức sonata với mô tả.', [['Trình bày', 'Giới thiệu các chủ đề'], ['Phát triển', 'Biến hóa các chủ đề'], ['Tái hiện', 'Chủ đề trở lại'], ['Coda', 'Kết thúc']], 'Hình thức sonata như một câu chuyện có mở đầu, diễn biến, hồi kết.', $d);

        $this->sortQ($s, 'Xếp mỗi đặc điểm vào nhóm GIAO HƯỞNG hoặc CONCERTO.', [['Dành cho toàn dàn nhạc', 'GIAO HƯỞNG'], ['Thường có 4 chương', 'GIAO HƯỞNG'], ['Có nhạc cụ độc tấu chính', 'CONCERTO'], ['Có đoạn cadenza', 'CONCERTO']], 'Giao hưởng tôn vinh tập thể dàn nhạc, concerto tôn vinh nghệ sĩ độc tấu.', $d);
        $this->sortQ($s, 'Xếp mỗi chương vào nhóm THƯỜNG NHANH hoặc THƯỜNG CHẬM.', [['Chương 1 giao hưởng', 'THƯỜNG NHANH'], ['Chương 4 giao hưởng', 'THƯỜNG NHANH'], ['Chương 2 giao hưởng', 'THƯỜNG CHẬM'], ['Chương 2 concerto', 'THƯỜNG CHẬM']], 'Nhanh – chậm – uyển chuyển – nhanh là mạch tốc độ kinh điển.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Cadenza là đoạn độc tấu khoe kỹ thuật', 'ĐÚNG'], ['Chương 1 giao hưởng thường dùng hình thức sonata', 'ĐÚNG'], ['Concerto chỉ có một chương duy nhất', 'SAI'], ['Dàn nhạc giao hưởng chỉ có 10 nhạc công', 'SAI']], 'Concerto thường có 3 chương; dàn nhạc giao hưởng có tới 70–100 nhạc công.', $d);
        $this->sortQ($s, 'Xếp mỗi đối tượng vào nhóm ĐỘC TẤU hoặc TẬP THỂ.', [['Piano trong concerto', 'ĐỘC TẤU'], ['Violin trong concerto', 'ĐỘC TẤU'], ['Bộ dây giao hưởng', 'TẬP THỂ'], ['Dàn hợp xướng', 'TẬP THỂ']], 'Độc tấu tỏa sáng cá nhân, tập thể tạo sức mạnh đồng lòng.', $d);
        $this->sortQ($s, 'Xếp mỗi thuật ngữ vào nhóm PHẦN CỦA TÁC PHẨM hoặc TOÀN BỘ TÁC PHẨM.', [['Chương', 'PHẦN CỦA TÁC PHẨM'], ['Cadenza', 'PHẦN CỦA TÁC PHẨM'], ['Giao hưởng', 'TOÀN BỘ TÁC PHẨM'], ['Concerto', 'TOÀN BỘ TÁC PHẨM']], 'Giao hưởng và concerto là tác phẩm hoàn chỉnh gồm nhiều chương, đoạn.', $d);

        $this->fill($s, 'Đoạn độc tấu khoe kỹ thuật trong concerto được gọi là ___.', [[0, 'cadenza']], 'Cadenza thường xuất hiện gần cuối chương 1, là lúc nghệ sĩ tỏa sáng.', $d);
        $this->fill($s, 'Chương 1 của giao hưởng cổ điển thường viết theo hình thức ___.', [[0, 'sonata']], 'Hình thức sonata gồm 3 phần: trình bày – phát triển – tái hiện.', $d);
        $this->fill($s, 'Dàn nhạc giao hưởng đầy đủ có khoảng 70 đến ___ nhạc công.', [[0, '100']], 'Dàn nhạc lớn chia thành 4 bộ: dây, hơi gỗ, đồng và gõ.', $d);
        $this->fill($s, 'Đoạn kết của một chương hoặc một tác phẩm được gọi là ___.', [[0, 'coda']], 'Coda trong tiếng Ý nghĩa là "cái đuôi", khép lại tác phẩm một cách trọn vẹn.', $d);
        $this->fill($s, 'Trong concerto grosso, nhóm nhỏ độc tấu đối thoại với dàn nhạc gọi là ___.', [[0, 'concertino']], 'Concertino là nhóm 2–4 nhạc công độc tấu trong concerto grosso.', $d);
    }

    private function seedOperaNhacKichLop124(): void
    {
        $s = 'am-nhac-thpt-12-lop-12-4';
        $d = 'kho';

        $this->quiz($s, 'Trong opera, đoạn một nhân vật bộc lộ tâm sự bằng giọng hát cao vút gọi là gì?', ['Aria', 'Đoạn hội thoại thường', 'Hợp xướng', 'Khúc mở màn'], 0, 'Aria là đoạn đơn ca trữ tình, nơi nhân vật opera bộc lộ cảm xúc sâu kín nhất.', $d);
        $this->quiz($s, 'Nhạc thính phòng (chamber music) có tên gọi như vậy vì sao?', ['Vì chỉ chơi trong phòng kín', 'Vì viết cho ít nhạc công, diễn trong không gian nhỏ thân mật', 'Vì không có khán giả', 'Vì nhạc cụ rất nhỏ'], 1, 'Chamber nghĩa là phòng — nhạc thính phòng viết cho ít người, biểu diễn trong không gian thân mật.', $d);
        $this->quiz($s, 'Điểm khác biệt cốt lõi giữa opera và nhạc kịch (musical) là gì?', ['Opera không có nhạc đệm', 'Opera hát toàn bộ, musical xen kẽ hát và thoại', 'Musical không cần sân khấu', 'Opera chỉ có một diễn viên'], 1, 'Opera hát xuyên suốt từ đầu tới cuối, musical xen kẽ các đoạn hát với lời thoại thường.', $d);
        $this->quiz($s, 'Tam tấu (trio) trong nhạc thính phòng gồm mấy nhạc công?', ['2', '3', '4', '5'], 1, 'Trio gồm 3 nhạc công — mỗi người một bè độc lập, đối thoại âm nhạc với nhau.', $d);
        $this->quiz($s, 'Vì sao nhạc thính phòng đòi hỏi sự ăn ý rất cao giữa các nhạc công?', ['Vì không có nhạc trưởng, mỗi người vừa chơi vừa lắng nghe nhau', 'Vì nhạc cụ quá to', 'Vì khán giả rất khó tính', 'Vì bản nhạc quá ngắn'], 0, 'Không có nhạc trưởng, các nhạc công thính phòng phải lắng nghe và phối hợp trực tiếp với nhau.', $d);

        $this->matching($s, 'Nối mỗi hình thức với đặc điểm của nó.', [['Opera', 'Hát toàn bộ lời thoại'], ['Musical', 'Xen kẽ hát và thoại'], ['Nhạc thính phòng', 'Ít nhạc công, không nhạc trưởng'], ['Operetta', 'Opera nhẹ nhàng, vui tươi']], 'Mỗi hình thức sân khấu âm nhạc có một cách kể chuyện riêng.', $d);
        $this->matching($s, 'Nối mỗi tên gọi với số nhạc công tương ứng.', [['Song tấu (duo)', '2'], ['Tam tấu (trio)', '3'], ['Tứ tấu (quartet)', '4'], ['Ngũ tấu (quintet)', '5']], 'Tên gọi Latin chỉ rõ số nhạc công: duo, trio, quartet, quintet.', $d);
        $this->matching($s, 'Nối mỗi thuật ngữ opera với ý nghĩa của nó.', [['Aria', 'Đoạn đơn ca bộc lộ tâm sự'], ['Hợp xướng opera', 'Đám đông cùng hát'], ['Mở màn (overture)', 'Khúc nhạc dạo đầu'], ['Sân khấu opera', 'Kết hợp hát, diễn, múa, mỹ thuật']], 'Opera là nghệ thuật tổng hợp đỉnh cao của âm nhạc và sân khấu.', $d);
        $this->matching($s, 'Nối mỗi yếu tố với vai trò của nó trong một vở opera.', [['Ca sĩ opera', 'Hát và diễn xuất'], ['Dàn nhạc', 'Đệm và dẫn dắt cảm xúc'], ['Đạo diễn', 'Dàn dựng sân khấu'], ['Họa sĩ sân khấu', 'Thiết kế cảnh trí']], 'Một vở opera là công sức của hàng trăm con người sau cánh gà.', $d);
        $this->matching($s, 'Nối mỗi đặc điểm với hình thức nghệ thuật tương ứng.', [['Quy mô lớn, tráng lệ', 'Opera'], ['Gần gũi, tính giải trí cao', 'Musical'], ['Thân mật, tinh tế', 'Nhạc thính phòng'], ['Kết hợp nhiều loại hình nghệ thuật', 'Opera và musical']], 'Từ tráng lệ tới thân mật, mỗi hình thức phục vụ một nhu cầu thưởng thức khác nhau.', $d);

        $this->sortQ($s, 'Xếp mỗi đặc điểm vào nhóm OPERA hoặc NHẠC THÍNH PHÒNG.', [['Hát toàn bộ, sân khấu lớn', 'OPERA'], ['Dàn nhạc giao hưởng đệm', 'OPERA'], ['Ít nhạc công, không nhạc trưởng', 'NHẠC THÍNH PHÒNG'], ['Không gian biểu diễn nhỏ', 'NHẠC THÍNH PHÒNG']], 'Opera hoành tráng trên sân khấu lớn, thính phòng tinh tế trong không gian nhỏ.', $d);
        $this->sortQ($s, 'Xếp mỗi tên gọi vào nhóm 2–3 NGƯỜI hoặc 4–5 NGƯỜI.', [['Song tấu', '2–3 NGƯỜI'], ['Tam tấu', '2–3 NGƯỜI'], ['Tứ tấu', '4–5 NGƯỜI'], ['Ngũ tấu', '4–5 NGƯỜI']], 'Số nhạc công càng ít, mỗi bè nhạc càng độc lập và đòi hỏi kỹ thuật cao.', $d);
        $this->sortQ($s, 'Xếp mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.', [['Aria là đoạn đơn ca bộc lộ tâm sự', 'ĐÚNG'], ['Nhạc thính phòng không có nhạc trưởng', 'ĐÚNG'], ['Musical hát toàn bộ, không có thoại', 'SAI'], ['Tam tấu gồm 4 nhạc công', 'SAI']], 'Musical xen kẽ hát và thoại; tam tấu gồm 3 nhạc công.', $d);
        $this->sortQ($s, 'Xếp mỗi hình thức vào nhóm NGHỆ THUẬT TỔNG HỢP hoặc THUẦN ÂM NHẠC.', [['Opera', 'NGHỆ THUẬT TỔNG HỢP'], ['Musical', 'NGHỆ THUẬT TỔNG HỢP'], ['Tứ tấu dây', 'THUẦN ÂM NHẠC'], ['Tam tấu piano', 'THUẦN ÂM NHẠC']], 'Opera và musical kết hợp hát, diễn, múa, mỹ thuật; thính phòng thuần túy âm nhạc.', $d);
        $this->sortQ($s, 'Xếp mỗi yếu tố vào nhóm THUỘC VỀ SÂN KHẤU hoặc THUỘC VỀ ÂM NHẠC.', [['Diễn xuất', 'THUỘC VỀ SÂN KHẤU'], ['Phục trang', 'THUỘC VỀ SÂN KHẤU'], ['Giai điệu', 'THUỘC VỀ ÂM NHẠC'], ['Hòa âm', 'THUỘC VỀ ÂM NHẠC']], 'Nghệ thuật tổng hợp là sự hòa quyện của yếu tố sân khấu và yếu tố âm nhạc.', $d);

        $this->fill($s, 'Đoạn đơn ca bộc lộ tâm sự của nhân vật trong opera được gọi là ___.', [[0, 'aria']], 'Aria là khoảnh khắc cảm xúc thăng hoa nhất của nhân vật opera.', $d);
        $this->fill($s, 'Tam tấu trong nhạc thính phòng gồm ___ nhạc công.', [[0, '3']], 'Mỗi nhạc công trong tam tấu đảm nhận một bè độc lập.', $d);
        $this->fill($s, '"Chamber" trong nhạc thính phòng có nghĩa là ___.', [[0, 'phòng']], 'Nhạc thính phòng ra đời để biểu diễn trong các căn phòng của giới quý tộc xưa.', $d);
        $this->fill($s, 'Khúc nhạc dạo đầu mở màn một vở opera được gọi là ___.', [[0, 'overture']], 'Overture giới thiệu các chủ đề âm nhạc chính của vở opera.', $d);
        $this->fill($s, 'Opera nhẹ nhàng, vui tươi xen kẽ đối thoại được gọi là ___.', [[0, 'operetta']], 'Operetta là "em gái" vui nhộn của opera trang trọng.', $d);
    }
}
