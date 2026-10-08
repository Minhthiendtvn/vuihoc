<?php

namespace Database\Seeders;

use App\Models\FillAnswer;
use App\Models\Lesson;
use App\Models\MatchingPair;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Skill;
use App\Models\SortItem;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;

/**
 * Dữ liệu THPT (lớp 10, 11, 12) cho 3 môn: KHOA HỌC, LỊCH SỬ, ĐỊA LÝ.
 *
 * 9 topics mới (mỗi môn 3 topics, mỗi topic đúng 1 khối: grade_min = grade_max),
 * mỗi topic 2 skills, mỗi skill 2 bài học (slug {topic-slug}-lop-{grade}-{n},
 * skill 1 -> n = 1,2 ; skill 2 -> n = 3,4). Mỗi bài: đúng 4 quiz + 4 matching
 * + 4 sort + 4 fill (= 16 câu, >= 576 câu tổng). Nội dung TỰ VIẾT 100% tiếng
 * Việt, bám kiến thức THPT Việt Nam.
 *
 * Lưu ý: cột difficulty của lessons/questions là enum ('de','trung_binh','kho')
 * nên 'medium' -> 'trung_binh', 'hard' -> 'kho'.
 *
 * Idempotent: topic/skill firstOrCreate theo slug; lesson bỏ qua khi slug tồn
 * tại; câu hỏi bỏ qua khi (lesson_id, game_type) đã được seed (logic seeded()).
 */
class HighSchoolGroupKhoaHocLichSuDiaLySeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $gradeBySlug = [];

    /** subject_slug => topic_slug => [name, icon, grade, description, skills: [skill_slug => name]] */
    private array $plan = [
        'khoa-hoc' => [
            'khoa-hoc-thpt-10' => [
                'name' => 'Vật lý: Chuyển động và lực', 'icon' => '⚛️', 'grade' => 10,
                'description' => 'Chuyển động thẳng đều, biến đổi đều và ba định luật Newton — nền tảng cơ học Vật lý lớp 10.',
                'skills' => [
                    'khoa-hoc-thpt-10-chuyen-dong-thang' => 'Chuyển động thẳng',
                    'khoa-hoc-thpt-10-luc-newton' => 'Lực và định luật Newton',
                ],
            ],
            'khoa-hoc-thpt-11' => [
                'name' => 'Hóa học: Nguyên tử và bảng tuần hoàn', 'icon' => '🧪', 'grade' => 11,
                'description' => 'Cấu tạo nguyên tử, đồng vị, cấu hình electron và bảng tuần hoàn các nguyên tố hóa học.',
                'skills' => [
                    'khoa-hoc-thpt-11-cau-tao-nguyen-tu' => 'Cấu tạo nguyên tử',
                    'khoa-hoc-thpt-11-bang-tuan-hoan' => 'Bảng tuần hoàn các nguyên tố',
                ],
            ],
            'khoa-hoc-thpt-12' => [
                'name' => 'Sinh học: Di truyền học', 'icon' => '🧬', 'grade' => 12,
                'description' => 'Cấu trúc ADN, gen, mã di truyền và các quy luật di truyền của Mendel.',
                'skills' => [
                    'khoa-hoc-thpt-12-adn-gen' => 'ADN và gen',
                    'khoa-hoc-thpt-12-mendel' => 'Quy luật di truyền Mendel',
                ],
            ],
        ],
        'lich-su' => [
            'lich-su-thpt-10' => [
                'name' => 'Việt Nam phong kiến', 'icon' => '🏛️', 'grade' => 10,
                'description' => 'Các triều đại phong kiến Việt Nam từ Ngô, Đinh đến Nguyễn cùng văn hóa, giáo dục, kiến trúc thời phong kiến.',
                'skills' => [
                    'lich-su-thpt-10-trieu-dai' => 'Các triều đại phong kiến',
                    'lich-su-thpt-10-van-hoa' => 'Văn hóa phong kiến',
                ],
            ],
            'lich-su-thpt-11' => [
                'name' => 'Việt Nam cận đại', 'icon' => '⚔️', 'grade' => 11,
                'description' => 'Phong trào yêu nước cuối thế kỉ XIX – đầu thế kỉ XX và hành trình tìm đường cứu nước của Nguyễn Ái Quốc.',
                'skills' => [
                    'lich-su-thpt-11-can-vuong' => 'Phong trào yêu nước cuối thế kỷ 19',
                    'lich-su-thpt-11-nguyen-ai-quoc' => 'Nguyễn Ái Quốc và cách mạng',
                ],
            ],
            'lich-su-thpt-12' => [
                'name' => 'Việt Nam hiện đại (1945–nay)', 'icon' => '🇻🇳', 'grade' => 12,
                'description' => 'Cách mạng tháng Tám, hai cuộc kháng chiến chống Pháp – Mĩ, công cuộc Đổi mới và hội nhập quốc tế.',
                'skills' => [
                    'lich-su-thpt-12-khang-chien' => 'Kháng chiến chống Pháp-Mỹ',
                    'lich-su-thpt-12-doi-moi' => 'Đổi mới và hội nhập',
                ],
            ],
        ],
        'dia-ly' => [
            'dia-ly-thpt-10' => [
                'name' => 'Địa lí tự nhiên đại cương', 'icon' => '🌐', 'grade' => 10,
                'description' => 'Vũ trụ, Trái Đất, thạch quyển, khí quyển và sinh quyển — kiến thức địa lí tự nhiên đại cương.',
                'skills' => [
                    'dia-ly-thpt-10-vu-tru' => 'Vũ trụ và Trái Đất',
                    'dia-ly-thpt-10-khi-hau' => 'Khí hậu và sinh quyển',
                ],
            ],
            'dia-ly-thpt-11' => [
                'name' => 'Kinh tế – xã hội thế giới', 'icon' => '🏭', 'grade' => 11,
                'description' => 'Dân số, đô thị hóa, các ngành kinh tế thế giới và toàn cầu hóa.',
                'skills' => [
                    'dia-ly-thpt-11-dan-so' => 'Dân số và đô thị hóa',
                    'dia-ly-thpt-11-kinh-te' => 'Các ngành kinh tế thế giới',
                ],
            ],
            'dia-ly-thpt-12' => [
                'name' => 'Địa lí Việt Nam', 'icon' => '🗺️', 'grade' => 12,
                'description' => 'Tự nhiên Việt Nam (vị trí, địa hình, khí hậu, sông ngòi) và kinh tế Việt Nam (nông nghiệp, công nghiệp, dịch vụ).',
                'skills' => [
                    'dia-ly-thpt-12-tu-nhien' => 'Tự nhiên Việt Nam',
                    'dia-ly-thpt-12-kinh-te' => 'Kinh tế Việt Nam',
                ],
            ],
        ],
    ];

    public function run(): void
    {
        $this->ensureStructure();

        // ---- KHOA HỌC 10 ----
        $this->seedKhThpt101(); $this->seedKhThpt102();
        $this->seedKhThpt103(); $this->seedKhThpt104();
        // ---- KHOA HỌC 11 ----
        $this->seedKhThpt111(); $this->seedKhThpt112();
        $this->seedKhThpt113(); $this->seedKhThpt114();
        // ---- KHOA HỌC 12 ----
        $this->seedKhThpt121(); $this->seedKhThpt122();
        $this->seedKhThpt123(); $this->seedKhThpt124();
        // ---- LỊCH SỬ 10 ----
        $this->seedLsThpt101(); $this->seedLsThpt102();
        $this->seedLsThpt103(); $this->seedLsThpt104();
        // ---- LỊCH SỬ 11 ----
        $this->seedLsThpt111(); $this->seedLsThpt112();
        $this->seedLsThpt113(); $this->seedLsThpt114();
        // ---- LỊCH SỬ 12 ----
        $this->seedLsThpt121(); $this->seedLsThpt122();
        $this->seedLsThpt123(); $this->seedLsThpt124();
        // ---- ĐỊA LÝ 10 ----
        $this->seedDlThpt101(); $this->seedDlThpt102();
        $this->seedDlThpt103(); $this->seedDlThpt104();
        // ---- ĐỊA LÝ 11 ----
        $this->seedDlThpt111(); $this->seedDlThpt112();
        $this->seedDlThpt113(); $this->seedDlThpt114();
        // ---- ĐỊA LÝ 12 ----
        $this->seedDlThpt121(); $this->seedDlThpt122();
        $this->seedDlThpt123(); $this->seedDlThpt124();
    }

    // ---------------- cấu trúc: topic / skill / lesson ----------------

    private function ensureStructure(): void
    {
        foreach ($this->plan as $subjectSlug => $topics) {
            $subject = Subject::where('slug', $subjectSlug)->firstOrFail();
            $tOrder = 0;
            foreach ($topics as $topicSlug => $t) {
                $tOrder++;
                $topic = Topic::firstOrCreate(['slug' => $topicSlug], [
                    'subject_id' => $subject->id,
                    'name' => $t['name'],
                    'description' => $t['description'],
                    'icon' => $t['icon'],
                    'sort_order' => $tOrder,
                    'grade_min' => $t['grade'],
                    'grade_max' => $t['grade'],
                    'is_published' => true,
                    'is_demo' => true,
                ]);
                $sOrder = 0;
                foreach ($t['skills'] as $skillSlug => $skillName) {
                    $sOrder++;
                    Skill::firstOrCreate(['slug' => $skillSlug], [
                        'topic_id' => $topic->id,
                        'name' => $skillName,
                        'description' => 'Kỹ năng: ' . $skillName . ' (' . $t['name'] . ').',
                        'sort_order' => $sOrder,
                        'is_demo' => true,
                    ]);
                }
            }
        }

        foreach ($this->lessonPlan() as [$topicSlug, $skillSlug, $num, $meta]) {
            $skill = Skill::where('slug', $skillSlug)->firstOrFail();
            $grade = $this->plan[$this->subjectOfTopic($topicSlug)][$topicSlug]['grade'];
            $this->createLesson($skill, $topicSlug, $grade, $num, $meta);
        }
    }

    private function subjectOfTopic(string $topicSlug): string
    {
        foreach ($this->plan as $subjectSlug => $topics) {
            if (isset($topics[$topicSlug])) {
                return $subjectSlug;
            }
        }
        throw new \RuntimeException("Topic không có trong plan: {$topicSlug}");
    }

    /**
     * [topic_slug, skill_slug, n, meta]. Skill 1 -> n = 1,2 ; skill 2 -> n = 3,4.
     */
    private function lessonPlan(): array
    {
        $M = 'trung_binh'; // medium
        $H = 'kho';        // hard
        return [
            // ---- KHOA HỌC 10 ----
            ['khoa-hoc-thpt-10', 'khoa-hoc-thpt-10-chuyen-dong-thang', 1, [
                'title' => 'Chuyển động thẳng đều: vận tốc và quãng đường (1)',
                'objective' => 'Tính được vận tốc, quãng đường, thời gian trong chuyển động thẳng đều; đổi đơn vị km/h sang m/s.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Chuyển động thẳng đều có vận tốc không đổi: v = s/t; s = v·t; t = s/v. Đổi đơn vị: 1 m/s = 3,6 km/h. Đồ thị quãng đường – thời gian là đường thẳng xiên.']],
            ['khoa-hoc-thpt-10', 'khoa-hoc-thpt-10-chuyen-dong-thang', 2, [
                'title' => 'Chuyển động thẳng biến đổi đều: gia tốc (2)',
                'objective' => 'Tính gia tốc; vận dụng v = v₀ + at, s = v₀t + ½at²; phân biệt nhanh dần đều và chậm dần đều.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Gia tốc a = Δv/Δt đặc trưng cho sự thay đổi vận tốc. Nhanh dần đều: a cùng chiều v; chậm dần đều: a ngược chiều v. Rơi tự do là nhanh dần đều với g ≈ 9,8 m/s².']],
            ['khoa-hoc-thpt-10', 'khoa-hoc-thpt-10-luc-newton', 3, [
                'title' => 'Ba định luật Newton (3)',
                'objective' => 'Phát biểu được ba định luật Newton; vận dụng F = m·a; nhận biết cặp lực – phản lực.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Định luật I: quán tính — vật giữ nguyên trạng thái chuyển động khi hợp lực bằng 0. Định luật II: F = m·a. Định luật III: lực và phản lực cùng phương, ngược chiều, cùng độ lớn, đặt lên hai vật khác nhau.']],
            ['khoa-hoc-thpt-10', 'khoa-hoc-thpt-10-luc-newton', 4, [
                'title' => 'Trọng lực, lực ma sát và lực đàn hồi (4)',
                'objective' => 'Tính trọng lượng P = m·g; phân biệt ma sát nghỉ/trượt; nêu đặc điểm lực đàn hồi; biết cách tăng/giảm ma sát.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Trọng lực P = m·g, phương thẳng đứng, chiều từ trên xuống. Ma sát nghỉ giữ vật chưa trượt; ma sát trượt ngược chiều chuyển động. Lực đàn hồi ngược chiều biến dạng, tỉ lệ với độ biến dạng.']],
            // ---- KHOA HỌC 11 ----
            ['khoa-hoc-thpt-11', 'khoa-hoc-thpt-11-cau-tao-nguyen-tu', 1, [
                'title' => 'Hạt cơ bản cấu tạo nên nguyên tử (1)',
                'objective' => 'Nêu điện tích, vị trí của proton, neutron, electron; xác định số hiệu nguyên tử Z.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Nguyên tử gồm hạt nhân (proton mang điện +1, neutron không mang điện) và vỏ electron (mang điện −1). Z = số proton = số electron khi nguyên tử trung hòa. Khối lượng nguyên tử tập trung ở hạt nhân.']],
            ['khoa-hoc-thpt-11', 'khoa-hoc-thpt-11-cau-tao-nguyen-tu', 2, [
                'title' => 'Số khối, đồng vị và cấu hình electron (2)',
                'objective' => 'Tính số khối A = Z + N; nhận biết đồng vị; xác định số electron lớp ngoài cùng.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Số khối A = số proton + số neutron. Đồng vị: cùng Z, khác số neutron. Electron phân bố theo lớp K (tối đa 2), L (tối đa 8), M (tối đa 18) theo mức năng lượng tăng dần.']],
            ['khoa-hoc-thpt-11', 'khoa-hoc-thpt-11-bang-tuan-hoan', 3, [
                'title' => 'Nguyên tắc sắp xếp và cấu tạo bảng tuần hoàn (3)',
                'objective' => 'Nêu nguyên tắc sắp xếp theo Z tăng dần; xác định chu kì, nhóm; gọi tên các nhóm IA, IIA, VIIA, VIIIA.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Các nguyên tố sắp xếp theo số hiệu nguyên tử tăng dần thành 7 chu kì (số lớp electron) và 18 cột. Nhóm IA: kim loại kiềm; IIA: kiềm thổ; VIIA: halogen; VIIIA: khí hiếm.']],
            ['khoa-hoc-thpt-11', 'khoa-hoc-thpt-11-bang-tuan-hoan', 4, [
                'title' => 'Xu hướng biến đổi tính chất các nguyên tố (4)',
                'objective' => 'Dự đoán xu hướng biến đổi bán kính nguyên tử, độ âm điện, tính kim loại – phi kim trong chu kì và nhóm.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Trong chu kì (trái → phải): bán kính giảm, độ âm điện tăng, tính kim loại giảm. Trong nhóm A (trên → dưới): bán kính tăng, tính kim loại tăng. Flo có độ âm điện lớn nhất.']],
            // ---- KHOA HỌC 12 ----
            ['khoa-hoc-thpt-12', 'khoa-hoc-thpt-12-adn-gen', 1, [
                'title' => 'Cấu trúc phân tử ADN (1)',
                'objective' => 'Nêu thành phần nucleotit, nguyên tắc bổ sung A–T, G–X; suy ra mạch bổ sung; mô tả mô hình xoắn kép.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'ADN gồm hai mạch xoắn kép, đơn phân là nucleotit. Nguyên tắc bổ sung: A–T (2 liên kết hiđro), G–X (3 liên kết hiđro). Mô hình do Watson và Crick đề xuất năm 1953.']],
            ['khoa-hoc-thpt-12', 'khoa-hoc-thpt-12-adn-gen', 2, [
                'title' => 'Gen và mã di truyền (2)',
                'objective' => 'Định nghĩa gen; nêu đặc điểm mã di truyền; nhận biết bộ ba mở đầu và các bộ ba kết thúc.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Gen là đoạn ADN mang thông tin di truyền. Mã di truyền đọc theo bộ ba nucleotit: AUG mở đầu; UAA, UAG, UGA kết thúc. Mã có tính phổ biến, đặc hiệu và thoái hóa.']],
            ['khoa-hoc-thpt-12', 'khoa-hoc-thpt-12-mendel', 3, [
                'title' => 'Lai một cặp tính trạng và quy luật phân li (3)',
                'objective' => 'Phân biệt trội/lặn, đồng hợp/dị hợp; dự đoán tỉ lệ 3:1 (kiểu hình) và 1:2:1 (kiểu gen).',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Mendel thí nghiệm trên đậu Hà Lan: Aa × Aa cho F2 tỉ lệ 3 trội : 1 lặn. Dòng thuần có kiểu gen đồng hợp. Lai phân tích (với cá thể lặn) dùng để kiểm tra kiểu gen.']],
            ['khoa-hoc-thpt-12', 'khoa-hoc-thpt-12-mendel', 4, [
                'title' => 'Lai hai cặp tính trạng và phân li độc lập (4)',
                'objective' => 'Xác định số loại giao tử; dự đoán tỉ lệ 9:3:3:1; giải thích sự xuất hiện biến dị tổ hợp.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'AaBb × AaBb cho 9:3:3:1 = (3:1)×(3:1). Cơ thể dị hợp n cặp gen cho 2ⁿ loại giao tử. Các gen trên các cặp NST tương đồng khác nhau phân li độc lập, tạo biến dị tổ hợp.']],
            // ---- LỊCH SỬ 10 ----
            ['lich-su-thpt-10', 'lich-su-thpt-10-trieu-dai', 1, [
                'title' => 'Buổi đầu độc lập: Ngô, Đinh, Tiền Lê (1)',
                'objective' => 'Trình bày các sự kiện 938, 968, 981; nêu ý nghĩa giành độc lập và quốc hiệu Đại Cồ Việt.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => '938: Ngô Quyền thắng Bạch Đằng, đóng đô Cổ Loa. 968: Đinh Bộ Lĩnh dẹp loạn 12 sứ quân, quốc hiệu Đại Cồ Việt, đô Hoa Lư. 981: Lê Hoàn đánh thắng quân Tống xâm lược.']],
            ['lich-su-thpt-10', 'lich-su-thpt-10-trieu-dai', 2, [
                'title' => 'Các triều đại Lý, Trần, Hồ, Lê và những chiến công (2)',
                'objective' => 'Nêu quá trình các triều đại và các chiến thắng tiêu biểu: chống Tống, chống Mông – Nguyên, Lam Sơn, Tây Sơn.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Lý (1009): dời đô Thăng Long 1010. Trần: ba lần thắng Mông – Nguyên. Lê sơ: khởi nghĩa Lam Sơn 1418–1427. Tây Sơn: Rạch Gầm 1785, Ngọc Hồi – Đống Đa 1789. Nguyễn thành lập 1802.']],
            ['lich-su-thpt-10', 'lich-su-thpt-10-van-hoa', 3, [
                'title' => 'Giáo dục và khoa cử thời phong kiến (3)',
                'objective' => 'Trình bày sự ra đời của Văn Miếu – Quốc Tử Giám và các khoa thi; nêu vai trò của khoa cử Nho học.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => '1070 dựng Văn Miếu, 1076 mở Quốc Tử Giám, 1075 khoa thi đầu tiên, 1919 khoa thi cuối cùng. Các cấp thi Hương – Hội – Đình chọn nhân tài giúp nước.']],
            ['lich-su-thpt-10', 'lich-su-thpt-10-van-hoa', 4, [
                'title' => 'Kiến trúc và nghệ thuật phong kiến (4)',
                'objective' => 'Kể tên công trình kiến trúc tiêu biểu, loại hình nghệ thuật; nhận biết các di sản UNESCO của Việt Nam.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Chùa Một Cột (Lý), thành nhà Hồ, cố đô Huế (Nguyễn). Di sản UNESCO: cố đô Huế (1993), Hội An và Mỹ Sơn (1999), Hoàng thành Thăng Long (2010). Nghệ thuật: nhã nhạc, tuồng, chèo, quan họ, ca trù.']],
            // ---- LỊCH SỬ 11 ----
            ['lich-su-thpt-11', 'lich-su-thpt-11-can-vuong', 1, [
                'title' => 'Phong trào Cần Vương (1885–1896) (1)',
                'objective' => 'Trình bày hoàn cảnh, diễn biến, ý nghĩa phong trào Cần Vương; nêu các cuộc khởi nghĩa tiêu biểu.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Tháng 7/1885, Tôn Thất Thuyết phò vua Hàm Nghi ra Chiếu Cần Vương. Tiêu biểu: Hương Khê (Phan Đình Phùng), Ba Đình, Bãi Sậy. Phong trào kết thúc năm 1896.']],
            ['lich-su-thpt-11', 'lich-su-thpt-11-can-vuong', 2, [
                'title' => 'Khởi nghĩa nông dân và phong trào Duy tân đầu thế kỉ XX (2)',
                'objective' => 'So sánh chủ trương Phan Bội Châu – Phan Châu Trinh; nêu các phong trào Đông Du, Đông Kinh Nghĩa Thục, Yên Thế.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Yên Thế (Hoàng Hoa Thám, 1884–1913). Phan Bội Châu: bạo động, Đông Du (1905). Phan Châu Trinh: khai dân trí, chấn dân khí. Đông Kinh Nghĩa Thục (1907) dạy chữ quốc ngữ miễn phí.']],
            ['lich-su-thpt-11', 'lich-su-thpt-11-nguyen-ai-quoc', 3, [
                'title' => 'Nguyễn Ái Quốc tìm đường cứu nước và thành lập Đảng (3)',
                'objective' => 'Trình bày hành trình 1911–1930 của Nguyễn Ái Quốc và sự ra đời của Đảng Cộng sản Việt Nam.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => '5/6/1911 rời bến Nhà Rồng. 1920 tham gia sáng lập Đảng Cộng sản Pháp; 1925 lập Hội Việt Nam Cách mạng Thanh niên. 3/2/1930 hợp nhất tại Hương Cảng thành Đảng Cộng sản Việt Nam.']],
            ['lich-su-thpt-11', 'lich-su-thpt-11-nguyen-ai-quoc', 4, [
                'title' => 'Phong trào cách mạng 1930–1945 (4)',
                'objective' => 'Nêu các cao trào 1930–1931, 1936–1939, 1939–1945; sự ra đời của Mặt trận Việt Minh và Đội Việt Nam Tuyên truyền Giải phóng quân.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Xô viết Nghệ Tĩnh (1930–1931). Mặt trận Việt Minh thành lập 1941. 22/12/1944 thành lập Đội Việt Nam Tuyên truyền Giải phóng quân do Võ Nguyên Giáp chỉ huy. Tổng khởi nghĩa tháng 8/1945.']],
            // ---- LỊCH SỬ 12 ----
            ['lich-su-thpt-12', 'lich-su-thpt-12-khang-chien', 1, [
                'title' => 'Cách mạng tháng Tám và kháng chiến chống Pháp (1945–1954) (1)',
                'objective' => 'Trình bày thắng lợi 19/8/1945, 2/9/1945 và 9 năm kháng chiến chống Pháp đến Điện Biên Phủ, Hiệp định Genève.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => '19/8/1945 Hà Nội khởi nghĩa; 2/9/1945 Tuyên ngôn độc lập. 19/12/1946 toàn quốc kháng chiến. 7/5/1954 chiến thắng Điện Biên Phủ; 21/7/1954 kí Hiệp định Genève, vĩ tuyến 17 là giới tuyến tạm thời.']],
            ['lich-su-thpt-12', 'lich-su-thpt-12-khang-chien', 2, [
                'title' => 'Kháng chiến chống Mĩ cứu nước (1954–1975) (2)',
                'objective' => 'Trình bày các giai đoạn kháng chiến chống Mĩ: Đồng Khởi, Mậu Thân, Điện Biên Phủ trên không đến đại thắng 30/4/1975.',
                'difficulty' => $H, 'duration' => 20,
                'instructions' => 'Đồng Khởi (1960, Bến Tre). Tết Mậu Thân (1968). 12/1972 "Điện Biên Phủ trên không". 27/1/1973 Hiệp định Paris. 30/4/1975 giải phóng miền Nam, thống nhất đất nước.']],
            ['lich-su-thpt-12', 'lich-su-thpt-12-doi-moi', 3, [
                'title' => 'Công cuộc Đổi mới từ năm 1986 (3)',
                'objective' => 'Nêu hoàn cảnh, nội dung cơ bản và thành tựu bước đầu của đường lối Đổi mới.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Đại hội VI (12/1986) đề ra Đổi mới, trọng tâm là đổi mới kinh tế: xóa bao cấp, đa thành phần, kinh tế thị trường định hướng xã hội chủ nghĩa. Khoán 10 (1988) thúc đẩy nông nghiệp phát triển.']],
            ['lich-su-thpt-12', 'lich-su-thpt-12-doi-moi', 4, [
                'title' => 'Việt Nam hội nhập quốc tế (4)',
                'objective' => 'Nêu các mốc hội nhập: ASEAN (1995), APEC (1998), WTO (2007) và vai trò quốc tế của Việt Nam.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => '28/7/1995 gia nhập ASEAN; 1998 gia nhập APEC; 2007 trở thành thành viên thứ 150 của WTO. EVFTA (2020), Chủ tịch ASEAN 2020, Ủy viên không thường trực Hội đồng Bảo an Liên hợp quốc 2020–2021.']],
            // ---- ĐỊA LÝ 10 ----
            ['dia-ly-thpt-10', 'dia-ly-thpt-10-vu-tru', 1, [
                'title' => 'Hệ Mặt Trời và chuyển động của Trái Đất (1)',
                'objective' => 'Kể tên các hành tinh; trình bày hai chuyển động của Trái Đất và hệ quả ngày đêm, bốn mùa.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => '8 hành tinh quay quanh Mặt Trời theo thứ tự: Thủy, Kim, Trái Đất, Hỏa, Mộc, Thổ, Thiên Vương, Hải Vương. Trái Đất tự quay (24 giờ) gây ngày đêm luân phiên; quay quanh Mặt Trời (365,25 ngày) kết hợp trục nghiêng gây bốn mùa.']],
            ['dia-ly-thpt-10', 'dia-ly-thpt-10-vu-tru', 2, [
                'title' => 'Cấu tạo Trái Đất và vận động của thạch quyển (2)',
                'objective' => 'Nêu cấu tạo ba lớp của Trái Đất; phân biệt nội lực và ngoại lực cùng tác động lên địa hình.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Trái Đất gồm vỏ, bao Manti và nhân. Thạch quyển = vỏ Trái Đất + phần trên bao Manti. Nội lực (động đất, núi lửa, uốn nếp) phát sinh từ bên trong; ngoại lực (phong hóa, xói mòn, bồi tụ) có nguồn gốc từ bức xạ Mặt Trời.']],
            ['dia-ly-thpt-10', 'dia-ly-thpt-10-khi-hau', 3, [
                'title' => 'Khí quyển: nhiệt độ, khí áp và gió (3)',
                'objective' => 'Nêu cấu tạo khí quyển; giải thích sự phân bố nhiệt độ, khí áp và các loại gió chính.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Tầng đối lưu (0–16 km) chứa các hiện tượng thời tiết. Nhiệt độ giảm theo vĩ độ và độ cao. Gió thổi từ nơi khí áp cao đến nơi khí áp thấp: gió Tín phong, gió Tây ôn đới, gió mùa.']],
            ['dia-ly-thpt-10', 'dia-ly-thpt-10-khi-hau', 4, [
                'title' => 'Sinh quyển và các đới thiên nhiên (4)',
                'objective' => 'Nêu khái niệm sinh quyển; trình bày quy luật địa đới và các đới thiên nhiên chính.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Sinh quyển là nơi sinh sống của sinh vật trên Trái Đất. Quy luật địa đới: thiên nhiên thay đổi theo vĩ độ (đới nóng – ôn hòa – lạnh). Quy luật phi địa đới: thay đổi theo độ cao và địa hình.']],
            // ---- ĐỊA LÝ 11 ----
            ['dia-ly-thpt-11', 'dia-ly-thpt-11-dan-so', 1, [
                'title' => 'Dân số thế giới: quy mô và gia tăng (1)',
                'objective' => 'Nêu quy mô, sự gia tăng dân số thế giới và phân bố dân cư; tính gia tăng dân số tự nhiên.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Dân số thế giới đạt 8 tỉ (2022); châu Á đông dân nhất; Ấn Độ là nước đông dân nhất. Gia tăng tự nhiên = tỉ suất sinh − tỉ suất tử. Bùng nổ dân số diễn ra ở các nước đang phát triển.']],
            ['dia-ly-thpt-11', 'dia-ly-thpt-11-dan-so', 2, [
                'title' => 'Cơ cấu dân số, di cư và đô thị hóa (2)',
                'objective' => 'Đọc tháp dân số; phân biệt dân số trẻ – già; nêu xu hướng di cư và đô thị hóa.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Tháp dân số thể hiện cơ cấu theo độ tuổi và giới tính. Dân số già: Nhật Bản, châu Âu. Di cư chủ yếu từ nông thôn ra thành thị. Siêu đô thị có trên 10 triệu dân (Tokyo, Delhi, Thượng Hải...).']],
            ['dia-ly-thpt-11', 'dia-ly-thpt-11-kinh-te', 3, [
                'title' => 'Nông nghiệp và công nghiệp thế giới (3)',
                'objective' => 'Nêu vai trò, phân bố các cây trồng chính và đặc điểm các cuộc cách mạng công nghiệp.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Lúa gạo tập trung ở châu Á gió mùa, lúa mì ở vùng ôn đới. Bốn cuộc cách mạng công nghiệp: hơi nước – điện – máy tính – số hóa/AI. FAO phụ trách lương thực – nông nghiệp; OPEC là tổ chức các nước xuất khẩu dầu mỏ.']],
            ['dia-ly-thpt-11', 'dia-ly-thpt-11-kinh-te', 4, [
                'title' => 'Dịch vụ và toàn cầu hóa kinh tế (4)',
                'objective' => 'Nêu vai trò ngành dịch vụ; trình bày biểu hiện và tác động hai mặt của toàn cầu hóa.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Dịch vụ chiếm tỉ trọng lớn trong GDP các nước phát triển. Toàn cầu hóa biểu hiện qua tự do thương mại, công ty xuyên quốc gia, WTO (1995). Tác động: mở rộng thị trường nhưng cạnh tranh gay gắt, phụ thuộc lẫn nhau.']],
            // ---- ĐỊA LÝ 12 ----
            ['dia-ly-thpt-12', 'dia-ly-thpt-12-tu-nhien', 1, [
                'title' => 'Vị trí địa lí và địa hình Việt Nam (1)',
                'objective' => 'Nêu vị trí, phạm vi lãnh thổ và đặc điểm địa hình (3/4 đồi núi); kể các dạng địa hình chính.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Diện tích 331.212 km², bờ biển dài 3.260 km, biên giới đất liền 4.639 km, tiếp giáp 3 nước. Địa hình 3/4 là đồi núi, hướng Tây Bắc – Đông Nam; đỉnh Phan-xi-păng 3.143 m; hai đồng bằng lớn là sông Hồng và sông Cửu Long.']],
            ['dia-ly-thpt-12', 'dia-ly-thpt-12-tu-nhien', 2, [
                'title' => 'Khí hậu, sông ngòi và đất Việt Nam (2)',
                'objective' => 'Trình bày tính chất nhiệt đới gió mùa; nêu đặc điểm sông ngòi và các loại đất chính.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Khí hậu nhiệt đới gió mùa, phân hóa Bắc – Nam rõ rệt. Sông ngòi dày đặc, nhiều nước: Mê Công, sông Hồng. Hai loại đất chính: feralit ở đồi núi và phù sa ở đồng bằng.']],
            ['dia-ly-thpt-12', 'dia-ly-thpt-12-kinh-te', 3, [
                'title' => 'Nông nghiệp Việt Nam (3)',
                'objective' => 'Nêu thế mạnh lúa gạo, cây công nghiệp, thủy sản và các vùng sản xuất chính.',
                'difficulty' => $M, 'duration' => 15,
                'instructions' => 'Đồng bằng sông Cửu Long là vựa lúa lớn nhất; Tây Nguyên trồng cà phê; Đông Nam Bộ trồng cao su. Việt Nam thuộc top đầu thế giới về xuất khẩu gạo, đứng thứ 2 về cà phê, top đầu về hồ tiêu và điều.']],
            ['dia-ly-thpt-12', 'dia-ly-thpt-12-kinh-te', 4, [
                'title' => 'Công nghiệp và dịch vụ Việt Nam (4)',
                'objective' => 'Nêu các ngành công nghiệp trọng điểm, trung tâm công nghiệp và thế mạnh du lịch, giao thông.',
                'difficulty' => $H, 'duration' => 18,
                'instructions' => 'Dầu khí là ngành công nghiệp trọng điểm (lọc dầu Dung Quất). TP.HCM là trung tâm công nghiệp lớn nhất. Du lịch biển đảo là thế mạnh: Hạ Long, Hội An, Huế, Phú Quốc. Cảng Cát Lái (TP.HCM) là cảng biển lớn nhất.']],
        ];
    }

    // ---------------- helpers (tái dùng y hệt file mẫu) ----------------

    private function lesson(string $slug): Lesson
    {
        if (! isset($this->lessonBySlug[$slug])) {
            $this->lessonBySlug[$slug] = Lesson::where('slug', $slug)->firstOrFail();
        }
        return $this->lessonBySlug[$slug];
    }

    private function createLesson(Skill $skill, string $topicSlug, int $grade, int $num, array $meta): void
    {
        $slug = "{$topicSlug}-lop-{$grade}-{$num}";
        $existing = Lesson::where('slug', $slug)->first();
        if ($existing) {
            $this->lessonBySlug[$slug] = $existing;
            return; // đã tồn tại: bỏ qua (idempotent)
        }
        $maxOrder = (int) Lesson::where('skill_id', $skill->id)->max('sort_order');
        $lesson = Lesson::create([
            'skill_id' => $skill->id,
            'slug' => $slug,
            'title' => $meta['title'],
            'objective' => $meta['objective'],
            'difficulty' => $meta['difficulty'],
            'duration_minutes' => $meta['duration'],
            'instructions' => $meta['instructions'],
            'sort_order' => $maxOrder + 1,
            'status' => 'published',
            'grade' => $grade,
            'is_demo' => true,
        ]);
        $this->lessonBySlug[$slug] = $lesson;
        $this->gradeBySlug[$slug] = $grade;
    }

    private function seeded(string $lessonSlug, string $type): bool
    {
        return Question::where('lesson_id', $this->lesson($lessonSlug)->id)
            ->where('game_type', $type)->exists();
    }

    private function newQuestion(string $lessonSlug, string $gameType, string $prompt, string $explanation, string $difficulty = 'de', int $points = 10): Question
    {
        $lesson = $this->lesson($lessonSlug);
        $this->orderByLesson[$lessonSlug] = ($this->orderByLesson[$lessonSlug] ?? 0) + 1;

        return Question::create([
            'lesson_id'   => $lesson->id,
            'game_type'   => $gameType,
            'prompt'      => $prompt,
            'explanation' => $explanation,
            'difficulty'  => $difficulty,
            'points'      => $points,
            'sort_order'  => $this->orderByLesson[$lessonSlug],
            'grade'       => $this->gradeBySlug[$lessonSlug] ?? $lesson->grade,
            'is_demo'     => true,
        ]);
    }

    private function quiz(string $lesson, string $prompt, array $options, int $correct, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'quiz', $prompt, $explanation, $difficulty);
        foreach ($options as $i => $text) {
            QuestionOption::create([
                'question_id' => $q->id, 'option_text' => $text,
                'is_correct' => $i === $correct, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function matching(string $lesson, string $prompt, array $pairs, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'matching', $prompt, $explanation, $difficulty);
        foreach ($pairs as $i => [$left, $right]) {
            MatchingPair::create([
                'question_id' => $q->id, 'left_text' => $left, 'right_text' => $right, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function sortQ(string $lesson, string $prompt, array $items, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'sort', $prompt, $explanation, $difficulty);
        foreach ($items as $i => [$text, $category]) {
            SortItem::create([
                'question_id' => $q->id, 'item_text' => $text, 'category' => $category, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function fill(string $lesson, string $prompt, array $answers, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'fill', $prompt, $explanation, $difficulty);
        foreach ($answers as $i => [$blankIndex, $text]) {
            FillAnswer::create([
                'question_id' => $q->id, 'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1,
            ]);
        }
    }

    // ================= KHOA HỌC 10 =================

    private function seedKhThpt101(): void
    {
        $L = 'khoa-hoc-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Một xe chạy đều với vận tốc 54 km/h. Trong 2 giờ xe đi được quãng đường bao nhiêu?',
                ['96 km', '108 km', '100 km', '120 km'], 1,
                'Quãng đường s = v·t = 54 × 2 = 108 km.');
            $this->quiz($L, 'Công thức tính vận tốc của chuyển động thẳng đều là:',
                ['v = s·t', 'v = s/t', 'v = t/s', 'v = s + t'], 1,
                'Vận tốc bằng quãng đường chia cho thời gian đi hết quãng đường đó.');
            $this->quiz($L, 'Một người đi bộ quãng đường 6 km hết 1,5 giờ. Vận tốc trung bình của người đó là:',
                ['3 km/h', '4 km/h', '5 km/h', '9 km/h'], 1,
                'v = s/t = 6 / 1,5 = 4 km/h.');
            $this->quiz($L, 'Trong chuyển động thẳng đều, đồ thị quãng đường – thời gian có dạng:',
                ['Đường thẳng xiên', 'Đường cong parabol', 'Đường thẳng nằm ngang', 'Đường gấp khúc'], 0,
                'Vì s = v·t là hàm bậc nhất của t nên đồ thị là đường thẳng xiên qua gốc tọa độ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đại lượng với đơn vị đo tương ứng.',
                [['Quãng đường', 'Mét (m)'], ['Vận tốc', 'Mét/giây (m/s)'],
                 ['Thời gian', 'Giây (s)'], ['Gia tốc', 'Mét/giây² (m/s²)']],
                'Mỗi đại lượng vật lí có một đơn vị đo chuẩn trong hệ SI.');
            $this->matching($L, 'Nối mỗi giá trị vận tốc với quãng đường xe đi được trong 1 giờ.',
                [['36 km/h', '36 km'], ['54 km/h', '54 km'],
                 ['72 km/h', '72 km'], ['90 km/h', '90 km']],
                'Vận tốc 54 km/h nghĩa là mỗi giờ đi được 54 km.');
            $this->matching($L, 'Nối mỗi công thức với tên gọi của nó.',
                [['s = v·t', 'Quãng đường'], ['v = s/t', 'Vận tốc'],
                 ['t = s/v', 'Thời gian'], ['1 m/s = 3,6 km/h', 'Đổi đơn vị vận tốc']],
                'Ba công thức suy ra từ nhau: s = v·t; v = s/t; t = s/v.');
            $this->matching($L, 'Nối mỗi chuyển động với đặc điểm của nó.',
                [['Chuyển động thẳng đều', 'Vận tốc không đổi'],
                 ['Chuyển động thẳng biến đổi đều', 'Gia tốc không đổi'],
                 ['Vật đứng yên', 'Vận tốc bằng 0'],
                 ['Chuyển động tròn đều', 'Tốc độ không đổi, hướng thay đổi']],
                'Thẳng đều: v không đổi. Biến đổi đều: a không đổi.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm CHUYỂN ĐỘNG ĐỀU hoặc BIẾN ĐỔI.',
                [['Ô tô chạy 60 km/h trên cao tốc', 'Chuyển động đều'],
                 ['Người đi bộ bước đều', 'Chuyển động đều'],
                 ['Máy bay cất cánh tăng tốc', 'Biến đổi'],
                 ['Xe đạp phanh dừng lại', 'Biến đổi'],
                 ['Thang máy chạy ổn định giữa các tầng', 'Chuyển động đều'],
                 ['Vật rơi từ trên cao xuống', 'Biến đổi']],
                'Vận tốc không đổi là chuyển động đều; vận tốc thay đổi là chuyển động biến đổi.');
            $this->sortQ($L, 'Kéo mỗi đại lượng vào nhóm ĐẠI LƯỢNG VÔ HƯỚNG hoặc VÉC-TƠ.',
                [['Quãng đường', 'Vô hướng'], ['Thời gian', 'Vô hướng'],
                 ['Khối lượng', 'Vô hướng'], ['Vận tốc', 'Véc-tơ'],
                 ['Gia tốc', 'Véc-tơ'], ['Lực', 'Véc-tơ']],
                'Véc-tơ có cả độ lớn và hướng; vô hướng chỉ có độ lớn.');
            $this->sortQ($L, 'Kéo mỗi giá trị vận tốc vào nhóm LỚN HƠN hoặc NHỎ HƠN 15 m/s.',
                [['72 km/h (= 20 m/s)', 'Lớn hơn 15 m/s'],
                 ['90 km/h (= 25 m/s)', 'Lớn hơn 15 m/s'],
                 ['60 km/h (= 16,7 m/s)', 'Lớn hơn 15 m/s'],
                 ['36 km/h (= 10 m/s)', 'Nhỏ hơn 15 m/s'],
                 ['18 km/h (= 5 m/s)', 'Nhỏ hơn 15 m/s'],
                 ['45 km/h (= 12,5 m/s)', 'Nhỏ hơn 15 m/s']],
                'Đổi km/h sang m/s bằng cách chia cho 3,6.');
            $this->sortQ($L, 'Kéo mỗi phương trình vào nhóm ĐÚNG với chuyển động thẳng đều.',
                [['x = x₀ + v·t', 'Đúng'], ['v = hằng số', 'Đúng'],
                 ['s = v·t', 'Đúng'], ['v = v₀ + a·t', 'Không đúng'],
                 ['s = v₀t + ½at²', 'Không đúng'], ['a = hằng số khác 0', 'Không đúng']],
                'Thẳng đều: a = 0, v không đổi, s = v·t.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '54 km/h đổi ra m/s bằng ___ m/s.', [[0, '15']],
                '54 : 3,6 = 15 m/s.');
            $this->fill($L, 'Trong chuyển động thẳng đều, vận tốc của vật ___ theo thời gian.', [[0, 'không đổi']],
                'Đó chính là định nghĩa của chuyển động thẳng đều.');
            $this->fill($L, 'Xe đi 120 km trong 3 giờ thì vận tốc trung bình là ___ km/h.', [[0, '40']],
                'v = s/t = 120/3 = 40 km/h.');
            $this->fill($L, 'Quãng đường trong chuyển động thẳng đều bằng vận tốc nhân với ___.', [[0, 'thời gian']],
                'Công thức: s = v·t.');
        }
    }

    private function seedKhThpt102(): void
    {
        $L = 'khoa-hoc-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Công thức tính gia tốc là:',
                ['a = v·t', 'a = Δv/Δt', 'a = v/t²', 'a = s/t²'], 1,
                'Gia tốc bằng độ biến thiên vận tốc chia cho thời gian biến thiên.');
            $this->quiz($L, 'Một ô tô tăng tốc từ 10 m/s lên 20 m/s trong 5 s. Gia tốc của ô tô là:',
                ['1 m/s²', '2 m/s²', '3 m/s²', '4 m/s²'], 1,
                'a = (20 − 10)/5 = 2 m/s².');
            $this->quiz($L, 'Trong chuyển động thẳng nhanh dần đều, vận tốc và gia tốc:',
                ['Ngược chiều nhau', 'Cùng chiều nhau', 'Vuông góc nhau', 'Không liên quan'], 1,
                'Nhanh dần đều: gia tốc cùng chiều với vận tốc làm tốc độ tăng.');
            $this->quiz($L, 'Công thức tính quãng đường trong chuyển động thẳng biến đổi đều là:',
                ['s = v·t', 's = v₀t + ½at²', 's = a·t', 's = v₀ + a·t'], 1,
                'Đây là công thức quãng đường khi gia tốc không đổi.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi công thức với đại lượng mà nó tính.',
                [['a = Δv/Δt', 'Gia tốc'], ['v = v₀ + a·t', 'Vận tốc'],
                 ['s = v₀t + ½at²', 'Quãng đường'], ['v² − v₀² = 2as', 'Liên hệ v, a, s']],
                'Bốn công thức cơ bản của chuyển động thẳng biến đổi đều.');
            $this->matching($L, 'Nối mỗi loại chuyển động với quan hệ giữa gia tốc và vận tốc.',
                [['Nhanh dần đều', 'a cùng chiều v'], ['Chậm dần đều', 'a ngược chiều v'],
                 ['Thẳng đều', 'a = 0'], ['Rơi tự do', 'a = g hướng xuống']],
                'Dấu của a so với v quyết định nhanh dần hay chậm dần.');
            $this->matching($L, 'Nối mỗi giá trị km/h với giá trị m/s tương ứng.',
                [['36 km/h', '10 m/s'], ['72 km/h', '20 m/s'],
                 ['18 km/h', '5 m/s'], ['54 km/h', '15 m/s']],
                'Chia số km/h cho 3,6 để được m/s.');
            $this->matching($L, 'Nối mỗi ví dụ với loại chuyển động.',
                [['Ô tô tăng tốc khi đèn xanh', 'Nhanh dần đều'],
                 ['Vật rơi tự do', 'Nhanh dần đều'],
                 ['Xe phanh khi gặp đèn đỏ', 'Chậm dần đều'],
                 ['Tàu vào ga giảm tốc', 'Chậm dần đều']],
                'Tăng tốc là nhanh dần đều, giảm tốc là chậm dần đều.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chuyển động vào nhóm NHANH DẦN ĐỀU hoặc CHẬM DẦN ĐỀU.',
                [['Ô tô tăng tốc khi đèn xanh', 'Nhanh dần đều'],
                 ['Vật rơi tự do', 'Nhanh dần đều'],
                 ['Bi lăn xuống dốc', 'Nhanh dần đều'],
                 ['Xe phanh khi gặp đèn đỏ', 'Chậm dần đều'],
                 ['Tàu vào ga giảm tốc', 'Chậm dần đều'],
                 ['Bi lăn lên dốc rồi dừng', 'Chậm dần đều']],
                'Tốc độ tăng là nhanh dần đều, tốc độ giảm là chậm dần đều.');
            $this->sortQ($L, 'Kéo mỗi đại lượng vào nhóm CÓ THỂ ÂM hoặc LUÔN KHÔNG ÂM.',
                [['Gia tốc', 'Có thể âm'], ['Vận tốc (theo chiều dương)', 'Có thể âm'],
                 ['Độ dời', 'Có thể âm'], ['Quãng đường', 'Luôn không âm'],
                 ['Thời gian', 'Luôn không âm'], ['Tốc độ', 'Luôn không âm']],
                'Quãng đường, thời gian, tốc độ không bao giờ âm.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Gia tốc đặc trưng cho sự thay đổi vận tốc', 'Đúng'],
                 ['Nhanh dần đều có a·v > 0', 'Đúng'],
                 ['Rơi tự do là chuyển động thẳng đều', 'Sai'],
                 ['Gia tốc của chuyển động biến đổi đều không đổi', 'Đúng'],
                 ['Chậm dần đều thì tốc độ giảm dần', 'Đúng'],
                 ['Đồ thị v–t của biến đổi đều là parabol', 'Sai']],
                'Đồ thị v–t của chuyển động biến đổi đều là đường thẳng xiên.');
            $this->sortQ($L, 'Kéo mỗi giá trị gia tốc vào nhóm DƯƠNG hoặc ÂM (chọn chiều dương là chiều chuyển động).',
                [['2 m/s²', 'Dương'], ['5 m/s²', 'Dương'],
                 ['0,5 m/s²', 'Dương'], ['−3 m/s²', 'Âm'],
                 ['−1,5 m/s²', 'Âm'], ['−9,8 m/s²', 'Âm']],
                'Gia tốc dương làm tốc độ tăng, gia tốc âm làm tốc độ giảm.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Gia tốc cho biết mức độ ___ của vận tốc theo thời gian.', [[0, 'thay đổi']],
                'Gia tốc càng lớn, vận tốc thay đổi càng nhanh.');
            $this->fill($L, 'Vật bắt đầu chuyển động nhanh dần đều từ trạng thái nghỉ với a = 2 m/s², sau 4 s vận tốc là ___ m/s.', [[0, '8']],
                'v = v₀ + at = 0 + 2 × 4 = 8 m/s.');
            $this->fill($L, 'Trong chuyển động thẳng chậm dần đều, gia tốc ___ chiều với vận tốc.', [[0, 'ngược']],
                'Ngược chiều nên tốc độ giảm dần.');
            $this->fill($L, 'Rơi tự do là chuyển động nhanh dần đều với gia tốc g xấp xỉ ___ m/s².', [[0, '9,8']],
                'g ≈ 9,8 m/s², thường lấy tròn 10 m/s².');
        }
    }

    private function seedKhThpt103(): void
    {
        $L = 'khoa-hoc-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nội dung của định luật I Newton là:',
                ['F = m·a', 'Mọi vật có xu hướng giữ nguyên trạng thái chuyển động', 'Lực và phản lực', 'a = F/m'], 1,
                'Định luật I còn gọi là định luật quán tính.');
            $this->quiz($L, 'Công thức của định luật II Newton là:',
                ['F = m·a', 'F = m·v', 'F = m/a', 'F = a/m'], 0,
                'Gia tốc của vật tỉ lệ thuận với lực tác dụng và tỉ lệ nghịch với khối lượng.');
            $this->quiz($L, 'Một vật khối lượng 2 kg chịu lực 10 N thì gia tốc của vật là:',
                ['5 m/s²', '20 m/s²', '8 m/s²', '12 m/s²'], 0,
                'a = F/m = 10/2 = 5 m/s².');
            $this->quiz($L, 'Theo định luật III Newton, lực và phản lực:',
                ['Cùng phương, ngược chiều, cùng độ lớn', 'Cùng chiều, cùng độ lớn', 'Vuông góc với nhau', 'Triệt tiêu nhau vì cùng đặt lên một vật'], 0,
                'Lực và phản lực đặt lên hai vật khác nhau nên không triệt tiêu nhau.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi định luật Newton với nội dung của nó.',
                [['Định luật I', 'Quán tính'], ['Định luật II', 'F = m·a'],
                 ['Định luật III', 'Lực và phản lực'], ['Cả ba định luật', 'Nền tảng cơ học cổ điển']],
                'Ba định luật Newton là nền tảng của cơ học cổ điển.');
            $this->matching($L, 'Nối mỗi hiện tượng với định luật giải thích nó.',
                [['Hành khách ngã về sau khi xe tăng tốc', 'Định luật I'],
                 ['Đẩy xe hàng càng nặng càng khó', 'Định luật II'],
                 ['Tên lửa phụt khí xuống để bay lên', 'Định luật III'],
                 ['Bóng đập vào tường bật ngược lại', 'Định luật III']],
                'Quán tính, F = m·a và lực – phản lực giải thích các hiện tượng hằng ngày.');
            $this->matching($L, 'Nối mỗi đại lượng với đơn vị đo của nó.',
                [['Lực', 'Niutơn (N)'], ['Khối lượng', 'Kilôgam (kg)'],
                 ['Gia tốc', 'm/s²'], ['Vận tốc', 'm/s']],
                '1 N là lực truyền cho vật 1 kg gia tốc 1 m/s².');
            $this->matching($L, 'Nối mỗi ví dụ với loại lực tác dụng.',
                [['Quả táo rơi xuống đất', 'Trọng lực'],
                 ['Lò xo bị nén đẩy tay ra', 'Lực đàn hồi'],
                 ['Hòm gỗ khó đẩy trên sàn', 'Lực ma sát'],
                 ['Nam châm hút đinh sắt', 'Lực từ']],
                'Có nhiều loại lực khác nhau trong tự nhiên.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cặp lực vào nhóm CẶP LỰC – PHẢN LỰC hoặc KHÔNG PHẢI.',
                [['Tay đẩy tường – tường đẩy tay', 'Lực – phản lực'],
                 ['Chân đạp đất – đất đẩy chân', 'Lực – phản lực'],
                 ['Trọng lực của quyển sách – lực nâng của bàn', 'Không phải'],
                 ['Lực kéo của ngựa – lực kéo lại của xe', 'Lực – phản lực'],
                 ['Trọng lực – phản lực của sàn lên chân', 'Không phải'],
                 ['Đầu búa đập đinh – đinh đẩy đầu búa', 'Lực – phản lực']],
                'Lực – phản lực là hai lực của cùng một tương tác, đặt lên hai vật khác nhau.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Quán tính là tính chất của mọi vật', 'Đúng'],
                 ['Vật đứng yên thì hợp lực tác dụng bằng 0', 'Đúng'],
                 ['Lực và phản lực triệt tiêu nhau', 'Sai'],
                 ['Khối lượng càng lớn, quán tính càng lớn', 'Đúng'],
                 ['Không có lực tác dụng thì vật không thể chuyển động', 'Sai'],
                 ['F = m·a cho thấy a tỉ lệ thuận với F', 'Đúng']],
                'Vật đang chuyển động thẳng đều vẫn giữ nguyên vận tốc khi hợp lực bằng 0.');
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm THỂ HIỆN QUÁN TÍNH hoặc KHÔNG.',
                [['Xe phanh gấp, người chúi về trước', 'Quán tính'],
                 ['Giũ mạnh áo cho bụi bay ra', 'Quán tính'],
                 ['Búa đóng đinh lún sâu vào gỗ', 'Quán tính'],
                 ['Xe chạy đều trên đường thẳng', 'Không rõ rệt'],
                 ['Rót nước từ bình ra cốc', 'Không rõ rệt'],
                 ['Cán búa tra vào đầu búa khi gõ xuống đất', 'Quán tính']],
                'Quán tính là xu hướng giữ nguyên vận tốc của vật.');
            $this->sortQ($L, 'Kéo mỗi trường hợp (vật 2 kg) vào nhóm LỰC NHỎ (≤ 6 N) hoặc LỰC LỚN (> 6 N).',
                [['a = 3 m/s² → F = 6 N', 'Lực nhỏ'], ['a = 2,5 m/s² → F = 5 N', 'Lực nhỏ'],
                 ['a = 2 m/s² → F = 4 N', 'Lực nhỏ'], ['a = 5 m/s² → F = 10 N', 'Lực lớn'],
                 ['a = 4 m/s² → F = 8 N', 'Lực lớn'], ['a = 6 m/s² → F = 12 N', 'Lực lớn']],
                'Áp dụng F = m·a với m = 2 kg.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Định luật I Newton còn được gọi là định luật ___.', [[0, 'quán tính']],
                'Vật có xu hướng giữ nguyên trạng thái đứng yên hoặc chuyển động thẳng đều.');
            $this->fill($L, 'Vật khối lượng 5 kg chịu lực 20 N thì gia tốc a = ___ m/s².', [[0, '4']],
                'a = F/m = 20/5 = 4 m/s².');
            $this->fill($L, 'Khi tay ta đẩy tường với lực 30 N thì tường đẩy tay ta với lực ___ N.', [[0, '30']],
                'Lực và phản lực có cùng độ lớn.');
            $this->fill($L, 'Đại lượng đặc trưng cho mức quán tính của vật là ___.', [[0, 'khối lượng']],
                'Khối lượng càng lớn thì càng khó thay đổi vận tốc.');
        }
    }

    private function seedKhThpt104(): void
    {
        $L = 'khoa-hoc-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Công thức tính trọng lực tác dụng lên vật là:',
                ['P = m·g', 'P = m/g', 'P = g/m', 'P = m + g'], 0,
                'Trọng lượng bằng khối lượng nhân với gia tốc rơi tự do.');
            $this->quiz($L, 'Một vật khối lượng 5 kg (lấy g = 10 m/s²) có trọng lượng là:',
                ['5 N', '50 N', '0,5 N', '500 N'], 1,
                'P = 5 × 10 = 50 N.');
            $this->quiz($L, 'Lực ma sát nghỉ xuất hiện khi:',
                ['Vật đang trượt trên mặt sàn', 'Vật có xu hướng trượt nhưng chưa trượt', 'Vật rơi tự do', 'Vật không chịu lực nào'], 1,
                'Ma sát nghỉ cân bằng với lực có xu hướng làm vật trượt.');
            $this->quiz($L, 'Lực đàn hồi của lò xo có đặc điểm:',
                ['Luôn cùng chiều biến dạng', 'Ngược chiều biến dạng, tỉ lệ với độ biến dạng', 'Không phụ thuộc độ biến dạng', 'Chỉ xuất hiện khi lò xo bị nén'], 1,
                'Lực đàn hồi chống lại biến dạng: F = k·Δl.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại lực với đặc điểm của nó.',
                [['Trọng lực', 'P = m·g, hướng thẳng đứng xuống'],
                 ['Lực ma sát trượt', 'Ngược chiều chuyển động'],
                 ['Lực đàn hồi', 'Ngược chiều biến dạng'],
                 ['Lực ma sát nghỉ', 'Cân bằng với lực kéo']],
                'Mỗi loại lực có phương, chiều và đặc điểm riêng.');
            $this->matching($L, 'Nối mỗi cách làm với tác dụng lên ma sát.',
                [['Tra dầu vào ổ bi', 'Giảm ma sát'], ['Rắc cát lên đường băng', 'Tăng ma sát'],
                 ['Làm nhám mặt lốp xe', 'Tăng ma sát'], ['Dùng con lăn thay kéo lê', 'Giảm ma sát']],
                'Tùy mục đích mà người ta tăng hoặc giảm ma sát.');
            $this->matching($L, 'Nối mỗi tình huống với lực giữ vật cân bằng.',
                [['Quả bóng nằm yên trên sân', 'Trọng lực cân bằng với phản lực'],
                 ['Sách không trượt trên bàn nghiêng', 'Lực ma sát nghỉ'],
                 ['Người đứng yên trên sàn', 'Phản lực của sàn'],
                 ['Đèn treo đứng yên', 'Lực căng dây']],
                'Vật đứng yên khi các lực tác dụng cân bằng nhau.');
            $this->matching($L, 'Nối mỗi độ dãn của lò xo (k = 100 N/m) với lực đàn hồi.',
                [['1 cm', '1 N'], ['2 cm', '2 N'], ['5 cm', '5 N'], ['10 cm', '10 N']],
                'F = k·Δl = 100 × 0,01 = 1 N với độ dãn 1 cm.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm MA SÁT CÓ ÍCH hoặc MA SÁT CÓ HẠI.',
                [['Phanh xe để dừng lại', 'Có ích'], ['Đi bộ không bị trượt ngã', 'Có ích'],
                 ['Viết phấn lên bảng', 'Có ích'], ['Ổ bi bị mòn khi quay lâu', 'Có hại'],
                 ['Ma sát làm nóng động cơ', 'Có hại'], ['Dây curoa truyền chuyển động', 'Có ích']],
                'Ma sát vừa có lợi vừa có hại tùy trường hợp sử dụng.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Trọng lực có phương thẳng đứng, chiều từ trên xuống', 'Đúng'],
                 ['g ở mọi nơi trên Trái Đất đều bằng nhau tuyệt đối', 'Sai'],
                 ['Lực ma sát trượt tỉ lệ với áp lực lên mặt tiếp xúc', 'Đúng'],
                 ['Lò xo càng dãn, lực đàn hồi càng lớn', 'Đúng'],
                 ['Ma sát nghỉ xuất hiện khi vật đang trượt', 'Sai'],
                 ['Lực đàn hồi cùng chiều với biến dạng', 'Sai']],
                'g thay đổi chút ít theo vĩ độ và độ cao.');
            $this->sortQ($L, 'Kéo mỗi vật vào nhóm TRỌNG LƯỢNG LỚN HƠN hoặc NHỎ HƠN 50 N (g = 10 m/s²).',
                [['Bao gạo 10 kg', 'Lớn hơn 50 N'], ['Bình nước 20 kg', 'Lớn hơn 50 N'],
                 ['Xe máy 100 kg', 'Lớn hơn 50 N'], ['Quyển sách 0,5 kg', 'Nhỏ hơn 50 N'],
                 ['Cặp sách 3 kg', 'Nhỏ hơn 50 N'], ['Quả cam 0,2 kg', 'Nhỏ hơn 50 N']],
                'P = m·g; vật 5 kg nặng 50 N.');
            $this->sortQ($L, 'Kéo mỗi lực vào nhóm LỰC TIẾP XÚC hoặc LỰC TRƯỜNG.',
                [['Lực ma sát', 'Tiếp xúc'], ['Lực đàn hồi', 'Tiếp xúc'],
                 ['Lực căng dây', 'Tiếp xúc'], ['Phản lực của mặt sàn', 'Tiếp xúc'],
                 ['Trọng lực', 'Trường'], ['Lực hấp dẫn', 'Trường']],
                'Lực tiếp xúc cần chạm vào vật; lực trường tác dụng từ xa.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trọng lượng của vật 2 kg (g = 10 m/s²) là ___ N.', [[0, '20']],
                'P = 2 × 10 = 20 N.');
            $this->fill($L, 'Lực ma sát trượt luôn ngược chiều với chiều ___ của vật.', [[0, 'chuyển động']],
                'Ma sát cản trở chuyển động tương đối.');
            $this->fill($L, 'Khi lò xo bị dãn, lực đàn hồi có chiều ___ với chiều dãn.', [[0, 'ngược']],
                'Lực đàn hồi chống lại biến dạng.');
            $this->fill($L, 'Độ lớn của lực ma sát trượt tỉ lệ thuận với ___ của vật lên mặt tiếp xúc.', [[0, 'áp lực']],
                'Áp lực càng lớn, ma sát trượt càng lớn.');
        }
    }

    // ================= KHOA HỌC 11 =================

    private function seedKhThpt111(): void
    {
        $L = 'khoa-hoc-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hạt mang điện tích dương trong nguyên tử là:',
                ['Electron', 'Neutron', 'Proton', 'Photon'], 2,
                'Proton nằm trong hạt nhân, mỗi proton mang điện tích +1.');
            $this->quiz($L, 'Số hiệu nguyên tử Z của một nguyên tố bằng:',
                ['Số neutron', 'Số proton', 'Số proton cộng số neutron', 'Số electron cộng số neutron'], 1,
                'Z đặc trưng cho nguyên tố hóa học và bằng số proton trong hạt nhân.');
            $this->quiz($L, 'Nguyên tử trung hòa về điện vì:',
                ['Số proton bằng số electron', 'Không chứa hạt nào', 'Số neutron bằng số electron', 'Proton và neutron triệt tiêu nhau'], 0,
                'Điện tích dương của proton cân bằng điện tích âm của electron.');
            $this->quiz($L, 'Hạt nào sau đây có khối lượng nhỏ nhất?',
                ['Proton', 'Neutron', 'Electron', 'Hạt nhân'], 2,
                'Khối lượng electron rất nhỏ, chỉ bằng khoảng 1/1836 khối lượng proton.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hạt với điện tích của nó.',
                [['Proton', '+1'], ['Neutron', '0'],
                 ['Electron', '−1'], ['Hạt nhân nguyên tử', 'Dương']],
                'Hạt nhân mang điện dương, vỏ electron mang điện âm.');
            $this->matching($L, 'Nối mỗi hạt với vị trí của nó trong nguyên tử.',
                [['Proton', 'Hạt nhân'], ['Neutron', 'Hạt nhân'],
                 ['Electron', 'Vỏ nguyên tử'], ['Nguyên tử trung hòa', 'Tổng điện tích bằng 0']],
                'Hạt nhân ở trung tâm, electron chuyển động xung quanh.');
            $this->matching($L, 'Nối mỗi nguyên tố với số hiệu nguyên tử Z của nó.',
                [['Hiđro (H)', '1'], ['Heli (He)', '2'],
                 ['Cacbon (C)', '6'], ['Oxi (O)', '8']],
                'Z tăng dần theo thứ tự các nguyên tố trong bảng tuần hoàn.');
            $this->matching($L, 'Nối mỗi mô hình nguyên tử với nhà khoa học đề xuất.',
                [['Mô hình bánh pudding', 'Thomson'],
                 ['Mô hình hành tinh nguyên tử', 'Rutherford'],
                 ['Mẫu nguyên tử Bohr', 'Bohr'],
                 ['Mô hình hiện đại (obitan)', 'Cơ học lượng tử']],
                'Hiểu biết về nguyên tử ngày càng hoàn thiện theo thời gian.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hạt/đối tượng vào nhóm MANG ĐIỆN hoặc KHÔNG MANG ĐIỆN.',
                [['Proton', 'Mang điện'], ['Electron', 'Mang điện'],
                 ['Ion dương', 'Mang điện'], ['Hạt nhân', 'Mang điện'],
                 ['Neutron', 'Không mang điện'], ['Nguyên tử trung hòa', 'Không mang điện']],
                'Ion là nguyên tử đã mất hoặc nhận thêm electron.');
            $this->sortQ($L, 'Kéo mỗi nguyên tố vào nhóm KIM LOẠI hoặc PHI KIM.',
                [['Natri (Na)', 'Kim loại'], ['Sắt (Fe)', 'Kim loại'],
                 ['Nhôm (Al)', 'Kim loại'], ['Oxi (O)', 'Phi kim'],
                 ['Clo (Cl)', 'Phi kim'], ['Lưu huỳnh (S)', 'Phi kim']],
                'Kim loại dẫn điện, dẫn nhiệt tốt; phi kim thường không dẫn điện.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nguyên tử gồm hạt nhân và vỏ electron', 'Đúng'],
                 ['Khối lượng nguyên tử tập trung ở hạt nhân', 'Đúng'],
                 ['Electron mang điện tích dương', 'Sai'],
                 ['Neutron không mang điện', 'Đúng'],
                 ['Proton nằm ở vỏ nguyên tử', 'Sai'],
                 ['Nguyên tử trung hòa về điện', 'Đúng']],
                'Proton và neutron tạo nên hạt nhân; electron ở vỏ.');
            $this->sortQ($L, 'Kéo mỗi nguyên tố vào nhóm có Z CHẴN hoặc Z LẺ.',
                [['Heli (Z = 2)', 'Chẵn'], ['Cacbon (Z = 6)', 'Chẵn'],
                 ['Oxi (Z = 8)', 'Chẵn'], ['Hiđro (Z = 1)', 'Lẻ'],
                 ['Natri (Z = 11)', 'Lẻ'], ['Nhôm (Z = 13)', 'Lẻ']],
                'Số hiệu nguyên tử Z là số proton trong hạt nhân.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hạt nhân nguyên tử gồm proton và ___.', [[0, 'neutron']],
                'Proton và neutron gọi chung là nucleon.');
            $this->fill($L, 'Điện tích của electron là −1 (đơn vị điện tích ___).', [[0, 'nguyên tố']],
                'Điện tích nguyên tố e = 1,6×10⁻¹⁹ C.');
            $this->fill($L, 'Nguyên tố có số hiệu nguyên tử Z = 6 là nguyên tố ___.', [[0, 'cacbon']],
                'Cacbon có 6 proton trong hạt nhân.');
            $this->fill($L, 'Trong nguyên tử trung hòa, số proton luôn bằng số ___.', [[0, 'electron']],
                'Nhờ đó tổng điện tích của nguyên tử bằng 0.');
        }
    }

    private function seedKhThpt112(): void
    {
        $L = 'khoa-hoc-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Số khối A của hạt nhân được tính bằng công thức:',
                ['A = Z + N', 'A = Z − N', 'A = số electron', 'A = Z × N'], 0,
                'Số khối bằng tổng số proton (Z) và số neutron (N).');
            $this->quiz($L, 'Nguyên tử X có Z = 8, N = 8. Số khối của X là:',
                ['8', '16', '0', '24'], 1,
                'A = 8 + 8 = 16, đó là đồng vị oxi-16.');
            $this->quiz($L, 'Hai nguyên tử là đồng vị của nhau khi chúng:',
                ['Có cùng số khối', 'Có cùng số proton nhưng khác số neutron', 'Có cùng số neutron', 'Có cùng số electron nhưng khác số proton'], 1,
                'Đồng vị: cùng Z (cùng nguyên tố), khác số neutron.');
            $this->quiz($L, 'Lớp electron thứ hai (lớp L) chứa tối đa bao nhiêu electron?',
                ['2 electron', '8 electron', '18 electron', '32 electron'], 1,
                'Số electron tối đa của lớp thứ n là 2n²; lớp L (n=2) chứa tối đa 8.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đồng vị với số khối của nó.',
                [['Cacbon-12', '12'], ['Oxi-16', '16'],
                 ['Natri-23', '23'], ['Sắt-56', '56']],
                'Số khối ghi sau tên nguyên tố: cacbon-12 có A = 12.');
            $this->matching($L, 'Nối mỗi lớp electron với số electron tối đa.',
                [['Lớp K (n = 1)', '2'], ['Lớp L (n = 2)', '8'],
                 ['Lớp M (n = 3)', '18'], ['Lớp N (n = 4)', '32']],
                'Quy tắc 2n² cho số electron tối đa mỗi lớp.');
            $this->matching($L, 'Nối mỗi cặp nguyên tử với quan hệ giữa chúng.',
                [['¹²C và ¹⁴C', 'Đồng vị của nhau'], ['¹⁶O và ¹⁸O', 'Đồng vị của nhau'],
                 ['³⁵Cl và ³⁷Cl', 'Đồng vị của nhau'], ['¹²C và ¹⁴N', 'Không phải đồng vị']],
                '¹²C và ¹⁴N khác số proton nên là hai nguyên tố khác nhau.');
            $this->matching($L, 'Nối mỗi nguyên tố với sự phân bố electron theo lớp.',
                [['Natri (Z = 11)', '2, 8, 1'], ['Clo (Z = 17)', '2, 8, 7'],
                 ['Oxi (Z = 8)', '2, 6'], ['Magie (Z = 12)', '2, 8, 2']],
                'Electron điền từ lớp trong (K) ra lớp ngoài theo mức năng lượng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nguyên tử vào nhóm ĐỒNG VỊ CỦA NHAU.',
                [['¹²C', 'Nhóm cacbon'], ['¹⁴C', 'Nhóm cacbon'],
                 ['¹⁶O', 'Nhóm oxi'], ['¹⁸O', 'Nhóm oxi'],
                 ['¹⁴N', 'Nhóm khác'], ['²³Na', 'Nhóm khác']],
                'Đồng vị là các nguyên tử cùng nguyên tố, khác số neutron.');
            $this->sortQ($L, 'Kéo mỗi nguyên tố vào nhóm theo số electron lớp ngoài cùng.',
                [['Natri (2, 8, 1)', '1 electron'], ['Hiđro (1)', '1 electron'],
                 ['Magie (2, 8, 2)', '2 electron'], ['Heli (2)', '2 electron'],
                 ['Neon (2, 8)', '8 electron'], ['Argon (2, 8, 8)', '8 electron']],
                'Số electron lớp ngoài cùng quyết định tính chất hóa học.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Các đồng vị có cùng tính chất hóa học', 'Đúng'],
                 ['Số khối bằng tổng số proton và neutron', 'Đúng'],
                 ['Electron lớp K có năng lượng cao nhất', 'Sai'],
                 ['Một nguyên tố có thể có nhiều đồng vị', 'Đúng'],
                 ['Công thức tính số khối là A = Z − N', 'Sai'],
                 ['Lớp L chứa tối đa 8 electron', 'Đúng']],
                'Lớp K gần hạt nhân nhất nên có năng lượng thấp nhất.');
            $this->sortQ($L, 'Kéo mỗi hạt nhân vào nhóm có SỐ KHỐI CHẴN hoặc LẺ.',
                [['¹²C', 'Chẵn'], ['¹⁶O', 'Chẵn'], ['¹⁴N', 'Chẵn'],
                 ['⁵⁶Fe', 'Chẵn'], ['²³Na', 'Lẻ'], ['³⁵Cl', 'Lẻ']],
                'Số khối A = Z + N.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Số khối A bằng số proton cộng với số ___.', [[0, 'neutron']],
                'A = Z + N.');
            $this->fill($L, 'Cặp ³⁵Cl và ³⁷Cl là hai ___ của nguyên tố clo.', [[0, 'đồng vị']],
                'Cùng 17 proton, khác số neutron.');
            $this->fill($L, 'Lớp K (n = 1) chứa tối đa ___ electron.', [[0, '2']],
                'Theo quy tắc 2n².');
            $this->fill($L, 'Nguyên tử oxi (Z = 8) có ___ electron ở lớp ngoài cùng.', [[0, '6']],
                'Cấu hình: 2, 6.');
        }
    }

    private function seedKhThpt113(): void
    {
        $L = 'khoa-hoc-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Các nguyên tố trong bảng tuần hoàn được sắp xếp theo:',
                ['Khối lượng nguyên tử tăng dần', 'Số hiệu nguyên tử tăng dần', 'Tính kim loại giảm dần', 'Tên gọi theo bảng chữ cái'], 1,
                'Ngày nay bảng tuần hoàn sắp xếp theo số hiệu nguyên tử Z tăng dần.');
            $this->quiz($L, 'Bảng tuần hoàn hiện nay có bao nhiêu chu kì?',
                ['7', '8', '10', '18'], 0,
                '7 chu kì tương ứng với 7 lớp electron.');
            $this->quiz($L, 'Nguyên tố ở chu kì 3 có bao nhiêu lớp electron?',
                ['2', '3', '4', '5'], 1,
                'Số thứ tự chu kì bằng số lớp electron của nguyên tử.');
            $this->quiz($L, 'Nhóm VIIIA gồm các nguyên tố:',
                ['Kim loại kiềm', 'Halogen', 'Khí hiếm', 'Kim loại kiềm thổ'], 2,
                'Khí hiếm có lớp ngoài cùng bền vững (8 electron) nên rất trơ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nhóm với tên gọi của nó.',
                [['Nhóm IA', 'Kim loại kiềm'], ['Nhóm IIA', 'Kim loại kiềm thổ'],
                 ['Nhóm VIIA', 'Halogen'], ['Nhóm VIIIA', 'Khí hiếm']],
                'Tên gọi phản ánh tính chất đặc trưng của nhóm.');
            $this->matching($L, 'Nối mỗi chu kì với số nguyên tố trong chu kì đó.',
                [['Chu kì 1', '2 nguyên tố'], ['Chu kì 2', '8 nguyên tố'],
                 ['Chu kì 3', '8 nguyên tố'], ['Chu kì 4', '18 nguyên tố']],
                'Chu kì càng lớn càng chứa nhiều nguyên tố.');
            $this->matching($L, 'Nối mỗi nguyên tố với vị trí của nó trong bảng tuần hoàn.',
                [['Natri (Na)', 'Chu kì 3, nhóm IA'], ['Clo (Cl)', 'Chu kì 3, nhóm VIIA'],
                 ['Kali (K)', 'Chu kì 4, nhóm IA'], ['Canxi (Ca)', 'Chu kì 4, nhóm IIA']],
                'Vị trí cho biết số lớp electron và số electron lớp ngoài cùng.');
            $this->matching($L, 'Nối mỗi nhà khoa học với đóng góp cho bảng tuần hoàn.',
                [['Mendeleev', 'Bảng tuần hoàn đầu tiên (1869)'],
                 ['Moseley', 'Sắp xếp theo số hiệu nguyên tử'],
                 ['Dobereiner', 'Bộ ba nguyên tố'], ['Newlands', 'Quy luật bát âm']],
                'Bảng tuần hoàn là thành tựu của nhiều nhà khoa học.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nguyên tố vào nhóm KIM LOẠI KIỀM, HALOGEN hoặc KHÍ HIẾM.',
                [['Liti (Li)', 'Kim loại kiềm'], ['Natri (Na)', 'Kim loại kiềm'],
                 ['Flo (F)', 'Halogen'], ['Clo (Cl)', 'Halogen'],
                 ['Heli (He)', 'Khí hiếm'], ['Neon (Ne)', 'Khí hiếm']],
                'IA: kim loại kiềm; VIIA: halogen; VIIIA: khí hiếm.');
            $this->sortQ($L, 'Kéo mỗi nguyên tố vào nhóm CHU KÌ 2 hoặc CHU KÌ 3.',
                [['Liti', 'Chu kì 2'], ['Cacbon', 'Chu kì 2'], ['Oxi', 'Chu kì 2'],
                 ['Natri', 'Chu kì 3'], ['Nhôm', 'Chu kì 3'], ['Lưu huỳnh', 'Chu kì 3']],
                'Chu kì 2 có 2 lớp electron, chu kì 3 có 3 lớp.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Các nguyên tố cùng chu kì có cùng số lớp electron', 'Đúng'],
                 ['Các nguyên tố cùng nhóm A có số e lớp ngoài cùng bằng nhau', 'Đúng'],
                 ['Bảng tuần hoàn hiện nay có 8 chu kì', 'Sai'],
                 ['Nhóm VIIIA còn gọi là nhóm khí hiếm', 'Đúng'],
                 ['Mendeleev sắp xếp theo số hiệu nguyên tử', 'Sai'],
                 ['Chu kì 1 chỉ có 2 nguyên tố là H và He', 'Đúng']],
                'Mendeleev sắp xếp theo khối lượng nguyên tử; Moseley mới dùng Z.');
            $this->sortQ($L, 'Kéo mỗi nguyên tố vào nhóm NHÓM A hoặc NHÓM B.',
                [['Natri', 'Nhóm A'], ['Clo', 'Nhóm A'], ['Kali', 'Nhóm A'],
                 ['Sắt', 'Nhóm B'], ['Đồng', 'Nhóm B'], ['Kẽm', 'Nhóm B']],
                'Nhóm A gồm các nguyên tố s và p; nhóm B gồm các kim loại chuyển tiếp.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Các nguyên tố trong cùng một chu kì có cùng số ___ electron.', [[0, 'lớp']],
                'Số thứ tự chu kì = số lớp electron.');
            $this->fill($L, 'Nhóm IA trong bảng tuần hoàn còn gọi là nhóm kim loại ___.', [[0, 'kiềm']],
                'Gồm Li, Na, K, Rb, Cs, Fr.');
            $this->fill($L, 'Nguyên tố có tính phi kim mạnh nhất, độ âm điện lớn nhất là ___.', [[0, 'flo']],
                'Flo đứng đầu nhóm halogen, chu kì 2.');
            $this->fill($L, 'Bảng tuần hoàn các nguyên tố do nhà bác học ___ đề xuất năm 1869.', [[0, 'Mendeleev']],
                'Ông sắp xếp các nguyên tố theo khối lượng nguyên tử tăng dần.');
        }
    }

    private function seedKhThpt114(): void
    {
        $L = 'khoa-hoc-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trong một chu kì, đi từ trái sang phải, bán kính nguyên tử:',
                ['Tăng dần', 'Giảm dần', 'Không đổi', 'Tăng rồi giảm'], 1,
                'Điện tích hạt nhân tăng nên hút electron mạnh hơn, bán kính giảm.');
            $this->quiz($L, 'Trong một nhóm A, đi từ trên xuống dưới, tính kim loại:',
                ['Giảm dần', 'Tăng dần', 'Không đổi', 'Giảm rồi tăng'], 1,
                'Bán kính tăng, electron lớp ngoài dễ mất đi hơn.');
            $this->quiz($L, 'Nguyên tố nào sau đây có độ âm điện lớn nhất?',
                ['Flo', 'Clo', 'Oxi', 'Natri'], 0,
                'Flo là nguyên tố có độ âm điện lớn nhất trong bảng tuần hoàn.');
            $this->quiz($L, 'Trong chu kì 3, nguyên tố nào có tính kim loại mạnh nhất?',
                ['Na', 'Mg', 'Al', 'Si'], 0,
                'Đầu chu kì là kim loại mạnh nhất, tính kim loại giảm dần sang phải.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tính chất với xu hướng biến đổi trong chu kì (trái → phải).',
                [['Bán kính nguyên tử', 'Giảm dần'], ['Độ âm điện', 'Tăng dần'],
                 ['Tính kim loại', 'Giảm dần'], ['Tính phi kim', 'Tăng dần']],
                'Trong chu kì, tính phi kim tăng dần từ trái sang phải.');
            $this->matching($L, 'Nối mỗi tính chất với xu hướng biến đổi trong nhóm A (trên → dưới).',
                [['Bán kính nguyên tử', 'Tăng dần'], ['Độ âm điện', 'Giảm dần'],
                 ['Tính kim loại', 'Tăng dần'], ['Tính phi kim', 'Giảm dần']],
                'Trong nhóm A, tính kim loại tăng dần từ trên xuống dưới.');
            $this->matching($L, 'Nối mỗi nguyên tố với tính chất nổi bật của nó.',
                [['Flo', 'Phi kim mạnh nhất'], ['Xesi', 'Kim loại kiềm mạnh'],
                 ['Heli', 'Khí hiếm, rất trơ'], ['Sắt', 'Kim loại chuyển tiếp']],
                'Vị trí trong bảng tuần hoàn quyết định tính chất.');
            $this->matching($L, 'Nối mỗi cặp nguyên tố với nguyên tố có bán kính lớn hơn.',
                [['Na và Mg', 'Na'], ['K và Na', 'K'],
                 ['F và Cl', 'Cl'], ['Li và Cs', 'Cs']],
                'Trong chu kì: trái lớn hơn phải; trong nhóm: dưới lớn hơn trên.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nguyên tố vào nhóm KIM LOẠI hoặc PHI KIM.',
                [['Natri', 'Kim loại'], ['Kali', 'Kim loại'],
                 ['Magie', 'Kim loại'], ['Flo', 'Phi kim'],
                 ['Clo', 'Phi kim'], ['Oxi', 'Phi kim']],
                'Ranh giới kim loại – phi kim chạy chéo qua bảng tuần hoàn.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Bán kính nguyên tử tăng dần từ trên xuống dưới trong một nhóm', 'Đúng'],
                 ['Độ âm điện của flo là lớn nhất', 'Đúng'],
                 ['Tính phi kim tăng dần từ trái sang phải trong chu kì', 'Đúng'],
                 ['Natri có tính kim loại yếu hơn magie', 'Sai'],
                 ['Khí hiếm có độ âm điện rất lớn', 'Sai'],
                 ['Bán kính nguyên tử của K lớn hơn của Na', 'Đúng']],
                'Khí hiếm trơ nên không xét độ âm điện như các nguyên tố khác.');
            $this->sortQ($L, 'Kéo mỗi nguyên tố chu kì 3 vào nhóm BÁN KÍNH LỚN hoặc NHỎ.',
                [['Na', 'Lớn'], ['Mg', 'Lớn'], ['Al', 'Lớn'],
                 ['Cl', 'Nhỏ'], ['Ar', 'Nhỏ'], ['S', 'Nhỏ']],
                'Trong chu kì 3, bán kính giảm dần từ Na đến Ar.');
            $this->sortQ($L, 'Kéo mỗi ion vào nhóm CATION hoặc ANION.',
                [['Na⁺', 'Cation'], ['Ca²⁺', 'Cation'], ['K⁺', 'Cation'],
                 ['Cl⁻', 'Anion'], ['O²⁻', 'Anion'], ['S²⁻', 'Anion']],
                'Cation là ion dương (mất electron), anion là ion âm (nhận electron).');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trong một chu kì, tính kim loại giảm dần từ ___ sang phải.', [[0, 'trái']],
                'Đầu chu kì là kim loại kiềm mạnh.');
            $this->fill($L, 'Nguyên tố có độ âm điện lớn nhất trong bảng tuần hoàn là ___.', [[0, 'flo']],
                'Flo có độ âm điện 3,98 (thang Pauling).');
            $this->fill($L, 'Bán kính nguyên tử của kali ___ bán kính nguyên tử của natri.', [[0, 'lớn hơn']],
                'K ở dưới Na trong nhóm IA nên bán kính lớn hơn.');
            $this->fill($L, 'Trong nhóm halogen, tính oxi hóa giảm dần từ flo đến ___.', [[0, 'iot']],
                'Thứ tự: F > Cl > Br > I về tính oxi hóa.');
        }
    }

    // ================= KHOA HỌC 12 =================

    private function seedKhThpt121(): void
    {
        $L = 'khoa-hoc-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'ADN được cấu tạo từ các đơn phân là:',
                ['Axit amin', 'Nucleotit', 'Glucose', 'Glycerol'], 1,
                'Mỗi nucleotit gồm đường deoxyribose, axit photphoric và bazơ nitơ.');
            $this->quiz($L, 'Trong phân tử ADN, bazơ A liên kết với bazơ nào?',
                ['G', 'C', 'T', 'U'], 2,
                'Theo nguyên tắc bổ sung: A liên kết với T bằng 2 liên kết hiđro.');
            $this->quiz($L, 'Mô hình cấu trúc không gian của ADN do ai đề xuất?',
                ['Mendel', 'Watson và Crick', 'Darwin', 'Morgan'], 1,
                'Năm 1953, Watson và Crick công bố mô hình xoắn kép của ADN.');
            $this->quiz($L, 'Một đoạn mạch ADN có trình tự A–T–G–X thì mạch bổ sung là:',
                ['T–A–X–G', 'A–T–G–X', 'U–A–X–G', 'T–A–G–X'], 0,
                'A bổ sung với T, G bổ sung với X.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bazơ nitơ với bazơ bổ sung với nó.',
                [['A (Adenin)', 'T (Timin)'], ['T (Timin)', 'A (Adenin)'],
                 ['G (Guanin)', 'X (Xitozin)'], ['X (Xitozin)', 'G (Guanin)']],
                'Nguyên tắc bổ sung là cơ sở của nhân đôi ADN.');
            $this->matching($L, 'Nối mỗi thành phần với vai trò của nó trong ADN.',
                [['Đường deoxyribose', 'Tạo khung sườn của mạch'],
                 ['Axit photphoric', 'Tạo khung sườn của mạch'],
                 ['Bazơ nitơ', 'Mang thông tin di truyền'],
                 ['Liên kết hiđro', 'Nối hai mạch đơn với nhau']],
                'Thông tin di truyền nằm ở trình tự các bazơ nitơ.');
            $this->matching($L, 'Nối mỗi loại nucleotit với kí hiệu của nó.',
                [['Adenin', 'A'], ['Timin', 'T'],
                 ['Guanin', 'G'], ['Xitozin', 'X']],
                'Bốn loại nucleotit tạo nên sự đa dạng của ADN.');
            $this->matching($L, 'Nối mỗi đặc điểm với phân tử tương ứng.',
                [['Chuỗi xoắn kép', 'ADN'], ['Chuỗi đơn', 'ARN'],
                 ['Có bazơ U thay cho T', 'ARN'], ['Đường ribose', 'ARN']],
                'ARN khác ADN ở đường ribose và bazơ uraxin.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cặp bazơ vào nhóm LIÊN KẾT BẰNG 2 hoặc 3 LIÊN KẾT HIĐRO.',
                [['A–T', '2 liên kết'], ['T–A', '2 liên kết'],
                 ['A–T (đoạn khác)', '2 liên kết'], ['G–X', '3 liên kết'],
                 ['X–G', '3 liên kết'], ['G–X (đoạn khác)', '3 liên kết']],
                'A–T: 2 liên kết hiđro; G–X: 3 liên kết hiđro.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['ADN có cấu trúc xoắn kép', 'Đúng'],
                 ['Trong ADN, A luôn bằng T và G luôn bằng X', 'Đúng'],
                 ['ARN có chứa bazơ Timin', 'Sai'],
                 ['Đường trong ADN là deoxyribose', 'Đúng'],
                 ['Hai mạch ADN song song cùng chiều', 'Sai'],
                 ['Liên kết hiđro nối hai mạch đơn của ADN', 'Đúng']],
                'Hai mạch ADN song song ngược chiều nhau.');
            $this->sortQ($L, 'Kéo mỗi thành phần vào nhóm THUỘC ADN, THUỘC ARN hoặc CẢ HAI.',
                [['Timin', 'ADN'], ['Deoxyribose', 'ADN'],
                 ['Uraxin', 'ARN'], ['Ribose', 'ARN'],
                 ['Adenin', 'Cả hai'], ['Guanin', 'Cả hai']],
                'ADN và ARN đều có A, G, X; khác nhau ở T/U và loại đường.');
            $this->sortQ($L, 'Kéo mỗi đoạn mạch vào nhóm là MẠCH BỔ SUNG ĐÚNG của A–T–G–X–A.',
                [['T–A–X–G–T', 'Đúng'], ['A–T–G–X–A', 'Sai'],
                 ['T–A–X–X–T', 'Sai'], ['U–A–X–G–U', 'Sai'],
                 ['T–T–X–G–T', 'Sai'], ['A–A–G–X–A', 'Sai']],
                'Áp dụng nguyên tắc bổ sung từng vị trí: A→T, T→A, G→X, X→G, A→T.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đơn phân cấu tạo nên phân tử ADN là ___.', [[0, 'nucleotit']],
                'ADN là chuỗi polinucleotit.');
            $this->fill($L, 'Trong ADN, A liên kết với T bằng ___ liên kết hiđro.', [[0, '2']],
                'Còn G liên kết với X bằng 3 liên kết hiđro.');
            $this->fill($L, 'Mô hình xoắn kép của ADN do ___ và Crick đề xuất năm 1953.', [[0, 'Watson']],
                'Phát hiện này mở ra kỉ nguyên sinh học phân tử.');
            $this->fill($L, 'Hai mạch đơn của ADN xoắn song song và ___ chiều nhau.', [[0, 'ngược']],
                'Một mạch 3′→5′, mạch kia 5′→3′.');
        }
    }

    private function seedKhThpt122(): void
    {
        $L = 'khoa-hoc-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Gen là:',
                ['Đoạn ADN mang thông tin di truyền', 'Protein trong tế bào', 'Nhiễm sắc thể', 'Bào quan trong tế bào'], 0,
                'Gen là một đoạn của phân tử ADN mang thông tin mã hóa cho sản phẩm nhất định.');
            $this->quiz($L, 'Mã di truyền được đọc theo:',
                ['Từng nucleotit riêng lẻ', 'Bộ ba nucleotit', 'Từng gen', 'Từng nhiễm sắc thể'], 1,
                'Mỗi bộ ba nucleotit (codon) mã hóa cho một axit amin.');
            $this->quiz($L, 'Bộ ba nào sau đây là bộ ba mở đầu dịch mã?',
                ['UAA', 'AUG', 'UAG', 'UGA'], 1,
                'AUG vừa là mã mở đầu vừa mã hóa axit amin Metionin.');
            $this->quiz($L, 'Mã di truyền có tính thoái hóa nghĩa là:',
                ['Nhiều bộ ba cùng mã hóa một axit amin', 'Một bộ ba mã hóa nhiều axit amin', 'Mã di truyền dễ bị đột biến', 'Mỗi loài có một bộ mã riêng'], 0,
                'Ví dụ nhiều codon khác nhau cùng mã hóa cho Lơxin.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bộ ba với vai trò của nó.',
                [['UAA', 'Kết thúc'], ['UAG', 'Kết thúc'],
                 ['UGA', 'Kết thúc'], ['AUG', 'Mở đầu']],
                'Ba bộ ba kết thúc không mã hóa axit amin nào.');
            $this->matching($L, 'Nối mỗi quá trình với sản phẩm tạo ra.',
                [['Phiên mã', 'ARN'], ['Dịch mã', 'Chuỗi polipeptit (protein)'],
                 ['Nhân đôi ADN', 'Phân tử ADN con'], ['Tái bản', 'ADN']],
                'Dòng thông tin di truyền: ADN → ARN → protein.');
            $this->matching($L, 'Nối mỗi khái niệm di truyền với định nghĩa của nó.',
                [['Gen', 'Đoạn ADN mang thông tin di truyền'],
                 ['Alen', 'Các trạng thái khác nhau của cùng một gen'],
                 ['Kiểu gen', 'Tổ hợp các alen của cá thể'],
                 ['Kiểu hình', 'Biểu hiện ra bên ngoài của kiểu gen']],
                'Kiểu hình là kết quả tương tác giữa kiểu gen và môi trường.');
            $this->matching($L, 'Nối mỗi loại ARN với chức năng của nó.',
                [['mARN', 'Khuôn mẫu tổng hợp protein'],
                 ['tARN', 'Vận chuyển axit amin'],
                 ['rARN', 'Thành phần cấu tạo riboxom'],
                 ['Cả ba loại', 'Tham gia quá trình dịch mã']],
                'Ba loại ARN phối hợp trong quá trình tổng hợp protein.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi bộ ba vào nhóm MÃ MỞ ĐẦU, MÃ KẾT THÚC hoặc MÃ HÓA AXIT AMIN.',
                [['AUG', 'Mở đầu'], ['UAA', 'Kết thúc'],
                 ['UAG', 'Kết thúc'], ['UGA', 'Kết thúc'],
                 ['UUU', 'Axit amin'], ['GGG', 'Axit amin']],
                'Có 64 bộ ba, trong đó 61 mã hóa axit amin.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Mã di truyền là mã bộ ba', 'Đúng'],
                 ['AUG vừa mở đầu vừa mã hóa Metionin', 'Đúng'],
                 ['Mỗi bộ ba mã hóa nhiều axit amin khác nhau', 'Sai'],
                 ['Mã di truyền có tính phổ biến', 'Đúng'],
                 ['Gen nằm trên nhiễm sắc thể', 'Đúng'],
                 ['Mọi sinh vật dùng chung một bộ mã di truyền', 'Đúng']],
                'Tính phổ biến: hầu hết sinh vật dùng chung bộ mã.');
            $this->sortQ($L, 'Kéo mỗi quá trình vào nhóm DIỄN RA Ở NHÂN hoặc Ở TẾ BÀO CHẤT (tế bào nhân thực).',
                [['Nhân đôi ADN', 'Nhân'], ['Phiên mã', 'Nhân'],
                 ['Tổng hợp ARN', 'Nhân'], ['Dịch mã', 'Tế bào chất'],
                 ['Tổng hợp protein', 'Tế bào chất'], ['Hoạt động của riboxom', 'Tế bào chất']],
                'mARN sau phiên mã ra tế bào chất để dịch mã.');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm CỦA GEN hoặc CỦA NHIỄM SẮC THỂ.',
                [['Là đoạn ADN mang thông tin', 'Gen'],
                 ['Có thể tồn tại nhiều alen', 'Gen'],
                 ['Là đơn vị di truyền cơ bản', 'Gen'],
                 ['Cấu tạo từ ADN và protein', 'Nhiễm sắc thể'],
                 ['Truyền đạt vật chất di truyền', 'Nhiễm sắc thể'],
                 ['Xoắn cực đại khi phân bào', 'Nhiễm sắc thể']],
                'Gen nằm trên nhiễm sắc thể; NST là vật chất di truyền cấp tế bào.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mã di truyền được đọc theo từng ___ nucleotit.', [[0, 'bộ ba']],
                'Mỗi codon gồm 3 nucleotit liên tiếp.');
            $this->fill($L, 'Bộ ba mở đầu quá trình dịch mã là ___.', [[0, 'AUG']],
                'AUG mã hóa axit amin Metionin.');
            $this->fill($L, 'Gen là một đoạn ___ mang thông tin di truyền.', [[0, 'ADN']],
                'Gen là đơn vị cơ bản của di truyền.');
            $this->fill($L, 'Có ___ bộ ba kết thúc không mã hóa axit amin nào.', [[0, '3']],
                'Đó là UAA, UAG và UGA.');
        }
    }

    private function seedKhThpt123(): void
    {
        $L = 'khoa-hoc-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tính trạng trội là tính trạng:',
                ['Biểu hiện ở F1 khi lai hai dòng thuần chủng khác nhau', 'Bị lấp đi ở F1', 'Chỉ xuất hiện ở F2', 'Không di truyền được'], 0,
                'Alen trội lấn át sự biểu hiện của alen lặn ở cơ thể dị hợp.');
            $this->quiz($L, 'Phép lai Aa × Aa cho tỉ lệ kiểu hình ở F2 là:',
                ['1 : 1', '3 : 1', '9 : 3 : 3 : 1', '1 : 2 : 1'], 1,
                '3 trội : 1 lặn là tỉ lệ đặc trưng của lai một cặp tính trạng.');
            $this->quiz($L, 'Cơ thể có kiểu gen Aa được gọi là:',
                ['Thể đồng hợp', 'Thể dị hợp', 'Dòng thuần', 'Thể đột biến'], 1,
                'Dị hợp: mang hai alen khác nhau của cùng một gen.');
            $this->quiz($L, 'Quy luật phân li của Mendel phản ánh:',
                ['Sự phân li của các alen trong giảm phân', 'Sự tổ hợp tự do của các gen', 'Hiện tượng liên kết gen', 'Sự phát sinh đột biến'], 0,
                'Mỗi giao tử chỉ chứa một alen của cặp alen.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép lai với tỉ lệ kiểu hình ở đời con.',
                [['AA × aa', '100% trội'], ['Aa × Aa', '3 trội : 1 lặn'],
                 ['Aa × aa', '1 trội : 1 lặn'], ['aa × aa', '100% lặn']],
                'Tỉ lệ phân li đặc trưng cho từng phép lai.');
            $this->matching($L, 'Nối mỗi kiểu gen với kiểu hình tương ứng (A trội hoàn toàn so với a).',
                [['AA', 'Trội'], ['Aa', 'Trội'],
                 ['aa', 'Lặn'], ['Không có kiểu gen nào khác', '—']],
                'Trội hoàn toàn: Aa biểu hiện giống AA.');
            $this->matching($L, 'Nối mỗi thuật ngữ với ý nghĩa của nó.',
                [['Tính trạng', 'Đặc điểm của cơ thể'],
                 ['Cặp tính trạng tương phản', 'Hai trạng thái đối lập nhau'],
                 ['Dòng thuần', 'Kiểu gen đồng hợp, di truyền ổn định'],
                 ['Lai phân tích', 'Lai với cá thể mang tính trạng lặn']],
                'Thuật ngữ cơ bản của di truyền học Mendel.');
            $this->matching($L, 'Nối mỗi nhà khoa học với đóng góp của ông.',
                [['Mendel', 'Các quy luật di truyền'], ['Morgan', 'Di truyền liên kết'],
                 ['Watson và Crick', 'Mô hình ADN'], ['Darwin', 'Thuyết tiến hóa']],
                'Mendel được mệnh danh là "ông tổ di truyền học".');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi kiểu gen vào nhóm ĐỒNG HỢP hoặc DỊ HỢP.',
                [['AA', 'Đồng hợp'], ['aa', 'Đồng hợp'],
                 ['BB', 'Đồng hợp'], ['AAbb', 'Đồng hợp'],
                 ['Aa', 'Dị hợp'], ['Bb', 'Dị hợp']],
                'Đồng hợp: hai alen giống nhau; dị hợp: hai alen khác nhau.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['F1 của phép lai AA × aa đều có kiểu gen Aa', 'Đúng'],
                 ['Tỉ lệ kiểu gen F2 của Aa × Aa là 1:2:1', 'Đúng'],
                 ['Tính trạng lặn biểu hiện ở cơ thể dị hợp', 'Sai'],
                 ['Mendel làm thí nghiệm trên đậu Hà Lan', 'Đúng'],
                 ['Aa × aa cho tỉ lệ kiểu hình 3:1', 'Sai'],
                 ['Dòng thuần có kiểu gen đồng hợp', 'Đúng']],
                'Aa × aa là lai phân tích, cho tỉ lệ 1:1.');
            $this->sortQ($L, 'Kéo mỗi phép lai vào nhóm cho tỉ lệ 3:1, 1:1 hoặc KHÁC.',
                [['Aa × Aa', '3 : 1'], ['Bb × Bb', '3 : 1'],
                 ['Aa × aa', '1 : 1'], ['Bb × bb', '1 : 1'],
                 ['AA × aa', 'Khác'], ['aa × aa', 'Khác']],
                'AA × aa cho 100% trội; aa × aa cho 100% lặn.');
            $this->sortQ($L, 'Kéo mỗi tính trạng của đậu Hà Lan vào nhóm TRỘI hoặc LẶN (theo Mendel).',
                [['Hạt vàng', 'Trội'], ['Thân cao', 'Trội'], ['Hoa đỏ', 'Trội'],
                 ['Hạt xanh', 'Lặn'], ['Thân thấp', 'Lặn'], ['Hoa trắng', 'Lặn']],
                'Mendel nghiên cứu 7 cặp tính trạng tương phản ở đậu Hà Lan.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phép lai Aa × Aa cho tỉ lệ kiểu hình ___ trội : 1 lặn.', [[0, '3']],
                'Tỉ lệ phân li đặc trưng 3:1.');
            $this->fill($L, 'Cơ thể mang cặp alen giống nhau (AA hoặc aa) gọi là thể ___.', [[0, 'đồng hợp']],
                'Dòng thuần luôn có kiểu gen đồng hợp.');
            $this->fill($L, 'Mendel đã thực hiện các thí nghiệm di truyền trên cây ___.', [[0, 'đậu Hà Lan']],
                'Đậu Hà Lan tự thụ phấn nghiêm ngặt, có nhiều cặp tính trạng tương phản.');
            $this->fill($L, 'Phép lai giữa cá thể cần kiểm tra với cá thể mang tính trạng lặn gọi là lai ___.', [[0, 'phân tích']],
                'Lai phân tích giúp xác định kiểu gen của cá thể mang tính trạng trội.');
        }
    }

    private function seedKhThpt124(): void
    {
        $L = 'khoa-hoc-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phép lai AaBb × AaBb cho tỉ lệ kiểu hình ở F2 là:',
                ['3 : 1', '1 : 1', '9 : 3 : 3 : 1', '1 : 2 : 1'], 2,
                '9:3:3:1 = (3:1)×(3:1), đặc trưng của phân li độc lập.');
            $this->quiz($L, 'Điều kiện để các gen phân li độc lập với nhau là:',
                ['Các gen nằm trên các cặp NST tương đồng khác nhau', 'Các gen nằm trên cùng một NST', 'Các gen liên kết hoàn toàn', 'Có đột biến xảy ra'], 0,
                'Các cặp NST phân li độc lập trong giảm phân kéo theo các gen.');
            $this->quiz($L, 'Cơ thể có kiểu gen AaBb giảm phân tạo ra bao nhiêu loại giao tử?',
                ['2', '4', '8', '16'], 1,
                'Số loại giao tử = 2ⁿ với n là số cặp gen dị hợp; 2² = 4.');
            $this->quiz($L, 'Tỉ lệ kiểu hình 9:3:3:1 là tích của hai tỉ lệ nào?',
                ['(3:1) × (3:1)', '(1:1) × (1:1)', '(1:2:1) × (1:2:1)', '(9:1) × (3:3)'], 0,
                'Mỗi cặp tính trạng phân li 3:1, tổ hợp độc lập cho 9:3:3:1.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phép lai với số loại kiểu hình ở đời con.',
                [['AABB × aabb', '1 loại'], ['AaBb × AaBb', '4 loại'],
                 ['AaBb × aabb', '4 loại'], ['AABb × aabb', '2 loại']],
                'Số kiểu hình phụ thuộc vào số cặp gen dị hợp.');
            $this->matching($L, 'Nối mỗi tỉ lệ với phép lai tương ứng.',
                [['9 : 3 : 3 : 1', 'AaBb × AaBb'], ['3 : 1', 'Aa × Aa'],
                 ['1 : 1 : 1 : 1', 'AaBb × aabb'], ['1 : 1', 'Aa × aa']],
                'Lai phân tích hai cặp tính trạng cho tỉ lệ 1:1:1:1.');
            $this->matching($L, 'Nối mỗi loại giao tử của cơ thể AaBb với tỉ lệ của nó.',
                [['AB', '1/4'], ['Ab', '1/4'], ['aB', '1/4'], ['ab', '1/4']],
                'Bốn loại giao tử với tỉ lệ ngang nhau do phân li độc lập.');
            $this->matching($L, 'Nối mỗi quy luật với nội dung của nó.',
                [['Quy luật phân li', 'Các alen phân li trong giảm phân'],
                 ['Quy luật phân li độc lập', 'Các cặp alen phân li độc lập'],
                 ['Trội hoàn toàn', 'Alen trội lấn át alen lặn'],
                 ['Cả hai quy luật', 'Do Mendel phát hiện']],
                'Hai quy luật Mendel là nền tảng di truyền học.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi kiểu gen vào nhóm tạo ra 1, 2 hoặc 4 LOẠI GIAO TỬ.',
                [['AABB', '1 loại'], ['aabb', '1 loại'],
                 ['AaBB', '2 loại'], ['Aabb', '2 loại'],
                 ['aaBb', '2 loại'], ['AaBb', '4 loại']],
                'Số loại giao tử = 2^(số cặp gen dị hợp).');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['AaBb tạo 4 loại giao tử với tỉ lệ bằng nhau', 'Đúng'],
                 ['Phân li độc lập tạo ra biến dị tổ hợp', 'Đúng'],
                 ['9:3:3:1 là tỉ lệ kiểu gen', 'Sai'],
                 ['Các gen trên cùng NST luôn phân li độc lập', 'Sai'],
                 ['Lai phân tích AaBb × aabb cho 1:1:1:1', 'Đúng'],
                 ['Mendel phát hiện phân li độc lập nhờ lai hai cặp tính trạng', 'Đúng']],
                '9:3:3:1 là tỉ lệ kiểu hình; tỉ lệ kiểu gen phức tạp hơn.');
            $this->sortQ($L, 'Kéo mỗi phép lai hai cặp tính trạng vào nhóm theo SỐ KIỂU HÌNH ở đời con.',
                [['AaBb × AaBb', '4 kiểu hình'], ['AaBb × aabb', '4 kiểu hình'],
                 ['AABB × aabb', '1 kiểu hình'], ['aabb × aabb', '1 kiểu hình'],
                 ['AABb × aabb', '2 kiểu hình'], ['AaBb × aaBB', '2 kiểu hình']],
                'Đếm số tổ hợp kiểu hình khác nhau ở đời con.');
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm BIẾN DỊ TỔ HỢP hoặc KHÔNG PHẢI.',
                [['Con lai có kiểu hình khác bố mẹ', 'Biến dị tổ hợp'],
                 ['AaBb × AaBb xuất hiện kiểu hình aabb', 'Biến dị tổ hợp'],
                 ['Lai xa tạo giống mới', 'Biến dị tổ hợp'],
                 ['AA × AA cho toàn AA', 'Không phải'],
                 ['Nhân bản vô tính', 'Không phải'],
                 ['Đột biến gen tạo alen mới', 'Không phải']],
                'Biến dị tổ hợp do sự tổ hợp lại các alen có sẵn.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phép lai AaBb × AaBb cho tỉ lệ kiểu hình ___ : 3 : 3 : 1.', [[0, '9']],
                '9 A–B– : 3 A–bb : 3 aaB– : 1 aabb.');
            $this->fill($L, 'Cơ thể có kiểu gen AaBb giảm phân tạo ___ loại giao tử.', [[0, '4']],
                'AB, Ab, aB, ab.');
            $this->fill($L, 'Các gen phân li độc lập khi nằm trên các cặp nhiễm sắc thể ___ khác nhau.', [[0, 'tương đồng']],
                'Sự phân li độc lập của NST kéo theo gen.');
            $this->fill($L, 'Phép lai phân tích hai cặp tính trạng (AaBb × aabb) cho tỉ lệ ___ : 1 : 1 : 1.', [[0, '1']],
                'Mỗi loại kiểu hình chiếm 1/4.');
        }
    }

    // ================= LỊCH SỬ 10 =================

    private function seedLsThpt101(): void
    {
        $L = 'lich-su-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Chiến thắng Bạch Đằng năm 938 gắn với tên tuổi của ai?',
                ['Đinh Bộ Lĩnh', 'Ngô Quyền', 'Lê Hoàn', 'Lý Công Uẩn'], 1,
                'Ngô Quyền dùng cọc gỗ đánh tan quân Nam Hán, mở ra thời kì độc lập.');
            $this->quiz($L, 'Năm 968, Đinh Bộ Lĩnh lên ngôi hoàng đế, đặt quốc hiệu là:',
                ['Đại Việt', 'Đại Cồ Việt', 'Đại Ngu', 'Việt Nam'], 1,
                'Đại Cồ Việt là quốc hiệu chính thức đầu tiên của nhà nước phong kiến Việt Nam.');
            $this->quiz($L, 'Kinh đô của nước Đại Cồ Việt thời Đinh – Tiền Lê đặt tại:',
                ['Cổ Loa', 'Hoa Lư', 'Thăng Long', 'Phú Xuân'], 1,
                'Hoa Lư (nay thuộc Ninh Bình) là kinh đô của Đinh – Tiền Lê.');
            $this->quiz($L, 'Năm 981, quân Tống xâm lược Đại Cồ Việt, người lãnh đạo kháng chiến thắng lợi là:',
                ['Ngô Quyền', 'Đinh Bộ Lĩnh', 'Lê Hoàn', 'Lý Thường Kiệt'], 2,
                'Lê Hoàn (Lê Đại Hành) đánh tan quân Tống, bảo vệ nền độc lập non trẻ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nhân vật với sự kiện lịch sử gắn với ông.',
                [['Ngô Quyền', 'Chiến thắng Bạch Đằng 938'],
                 ['Đinh Bộ Lĩnh', 'Dẹp loạn 12 sứ quân'],
                 ['Lê Hoàn', 'Kháng chiến chống Tống 981'],
                 ['Lý Công Uẩn', 'Dời đô về Thăng Long 1010']],
                'Bốn nhân vật mở đầu các triều đại phong kiến độc lập.');
            $this->matching($L, 'Nối mỗi năm với sự kiện lịch sử tương ứng.',
                [['938', 'Chiến thắng Bạch Đằng'], ['968', 'Đinh Bộ Lĩnh lên ngôi'],
                 ['981', 'Chống Tống lần thứ nhất'], ['1010', 'Dời đô về Thăng Long']],
                'Bốn mốc son buổi đầu thời kì độc lập.');
            $this->matching($L, 'Nối mỗi triều đại với kinh đô của nó.',
                [['Nhà Ngô', 'Cổ Loa'], ['Nhà Đinh', 'Hoa Lư'],
                 ['Nhà Tiền Lê', 'Hoa Lư'], ['Nhà Lý', 'Thăng Long']],
                'Kinh đô chuyển từ Cổ Loa → Hoa Lư → Thăng Long.');
            $this->matching($L, 'Nối mỗi quốc hiệu với thời kì sử dụng.',
                [['Đại Cồ Việt', 'Đinh – Tiền Lê – đầu Lý'],
                 ['Đại Việt', 'Từ 1054 (Lý) về sau'],
                 ['Đại Ngu', 'Nhà Hồ (1400–1407)'],
                 ['Việt Nam', 'Từ thời Gia Long']],
                'Quốc hiệu thay đổi theo từng triều đại.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm THẾ KỈ X hoặc THẾ KỈ XI.',
                [['Bạch Đằng 938', 'Thế kỉ X'], ['Dẹp loạn 12 sứ quân 968', 'Thế kỉ X'],
                 ['Chống Tống 981', 'Thế kỉ X'], ['Dời đô Thăng Long 1010', 'Thế kỉ XI'],
                 ['Đổi quốc hiệu Đại Việt 1054', 'Thế kỉ XI'], ['Lý Thường Kiệt đánh Tống 1075', 'Thế kỉ XI']],
                'Thế kỉ X: 901–1000; thế kỉ XI: 1001–1100.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Ngô Quyền xưng vương, đóng đô ở Cổ Loa', 'Đúng'],
                 ['Đinh Bộ Lĩnh là người đầu tiên xưng đế ở Việt Nam', 'Đúng'],
                 ['Lê Hoàn đánh bại quân Tống năm 981', 'Đúng'],
                 ['Nhà Đinh đóng đô ở Thăng Long', 'Sai'],
                 ['Năm 1010 Lý Công Uẩn dời đô về Hoa Lư', 'Sai'],
                 ['Quốc hiệu Đại Cồ Việt tồn tại đến năm 1054', 'Đúng']],
                'Năm 1054, Lý Thánh Tông đổi quốc hiệu thành Đại Việt.');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm VUA MỞ NƯỚC hoặc TƯỚNG GIỮ NƯỚC.',
                [['Ngô Quyền', 'Vua mở nước'], ['Đinh Bộ Lĩnh', 'Vua mở nước'],
                 ['Lý Công Uẩn', 'Vua mở nước'], ['Lê Hoàn', 'Cả hai vai trò'],
                 ['Lý Thường Kiệt', 'Tướng giữ nước'], ['Trần Hưng Đạo', 'Tướng giữ nước']],
                'Lê Hoàn vừa là vua Tiền Lê vừa trực tiếp chỉ huy kháng Tống.');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm KINH ĐÔ hoặc CHIẾN TRƯỜNG.',
                [['Cổ Loa', 'Kinh đô'], ['Hoa Lư', 'Kinh đô'], ['Thăng Long', 'Kinh đô'],
                 ['Bạch Đằng', 'Chiến trường'], ['Sông Như Nguyệt', 'Chiến trường'],
                 ['Ải Chi Lăng', 'Chiến trường']],
                'Kinh đô là trung tâm chính trị; chiến trường nơi diễn ra chiến sự.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Năm 938, ___ đánh tan quân Nam Hán trên sông Bạch Đằng.', [[0, 'Ngô Quyền']],
                'Chiến thắng Bạch Đằng mở ra thời kì độc lập lâu dài.');
            $this->fill($L, 'Năm 968, Đinh Bộ Lĩnh lên ngôi hoàng đế, đặt quốc hiệu là ___.', [[0, 'Đại Cồ Việt']],
                'Ông dẹp loạn 12 sứ quân, thống nhất đất nước.');
            $this->fill($L, 'Kinh đô Hoa Lư thời Đinh – Tiền Lê nay thuộc tỉnh ___.', [[0, 'Ninh Bình']],
                'Hoa Lư là cố đô với núi non hiểm trở.');
            $this->fill($L, 'Năm 1010, Lý Công Uẩn dời đô từ Hoa Lư về ___.', [[0, 'Thăng Long']],
                'Thăng Long trở thành kinh đô lâu dài của Đại Việt.');
        }
    }

    private function seedLsThpt102(): void
    {
        $L = 'lich-su-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nhà Lý được thành lập vào năm nào?',
                ['1009', '1010', '1054', '1076'], 0,
                'Năm 1009, Lý Công Uẩn lên ngôi, lập ra nhà Lý.');
            $this->quiz($L, 'Ba lần kháng chiến chống quân Mông – Nguyên thắng lợi gắn với triều đại nào?',
                ['Lý', 'Trần', 'Hồ', 'Lê'], 1,
                'Nhà Trần lãnh đạo nhân dân ba lần đánh thắng Mông – Nguyên (1258, 1285, 1287–1288).');
            $this->quiz($L, 'Người lãnh đạo khởi nghĩa Lam Sơn (1418–1427) là:',
                ['Nguyễn Trãi', 'Lê Lợi', 'Nguyễn Huệ', 'Quang Trung'], 1,
                'Lê Lợi cùng Nguyễn Trãi lãnh đạo khởi nghĩa, giành lại độc lập từ tay nhà Minh.');
            $this->quiz($L, 'Chiến thắng Ngọc Hồi – Đống Đa (1789) gắn với tên tuổi của:',
                ['Nguyễn Huệ (Quang Trung)', 'Nguyễn Ánh', 'Lê Lợi', 'Trần Hưng Đạo'], 0,
                'Quang Trung hành quân thần tốc, đại phá 29 vạn quân Thanh.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi triều đại với thời gian tồn tại của nó.',
                [['Nhà Lý', '1009–1225'], ['Nhà Trần', '1226–1400'],
                 ['Nhà Hồ', '1400–1407'], ['Nhà Lê sơ', '1428–1527']],
                'Nhà Lý và Trần là hai triều đại hưng thịnh bậc nhất.');
            $this->matching($L, 'Nối mỗi chiến thắng với năm diễn ra.',
                [['Bạch Đằng chống Nguyên', '1288'], ['Chi Lăng – Xương Giang', '1427'],
                 ['Ngọc Hồi – Đống Đa', '1789'], ['Rạch Gầm – Xoài Mút', '1785']],
                'Bốn chiến thắng lừng lẫy trong lịch sử chống ngoại xâm.');
            $this->matching($L, 'Nối mỗi nhân vật với vai trò lịch sử của ông.',
                [['Trần Hưng Đạo', 'Tổng chỉ huy kháng chiến chống Nguyên'],
                 ['Nguyễn Trãi', 'Tác giả Bình Ngô đại cáo'],
                 ['Hồ Quý Ly', 'Lập nhà Hồ, quốc hiệu Đại Ngu'],
                 ['Gia Long', 'Lập nhà Nguyễn năm 1802']],
                'Mỗi nhân vật gắn với một bước ngoặt lịch sử.');
            $this->matching($L, 'Nối mỗi tác phẩm với tác giả của nó.',
                [['Bình Ngô đại cáo', 'Nguyễn Trãi'], ['Hịch tướng sĩ', 'Trần Hưng Đạo'],
                 ['Đại Việt sử kí', 'Lê Văn Hưu'], ['Chiếu dời đô', 'Lý Công Uẩn']],
                'Những áng văn bất hủ của dân tộc.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm THẾ KỈ XIII, XV hoặc XVIII.',
                [['Chống Mông Cổ lần 1 (1258)', 'XIII'], ['Bạch Đằng 1288', 'XIII'],
                 ['Hồ Quý Ly lên ngôi 1400', 'XV'], ['Lê Lợi lên ngôi 1428', 'XV'],
                 ['Rạch Gầm – Xoài Mút 1785', 'XVIII'], ['Ngọc Hồi – Đống Đa 1789', 'XVIII']],
                'Mỗi thế kỉ gắn với những sự kiện trọng đại.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nhà Trần ba lần đánh thắng quân Mông – Nguyên', 'Đúng'],
                 ['Bình Ngô đại cáo ra đời năm 1428', 'Đúng'],
                 ['Quang Trung là niên hiệu của Nguyễn Huệ', 'Đúng'],
                 ['Nhà Hồ tồn tại hơn 100 năm', 'Sai'],
                 ['Gia Long lập nhà Nguyễn năm 1802', 'Đúng'],
                 ['Rạch Gầm – Xoài Mút đánh thắng quân Xiêm', 'Đúng']],
                'Nhà Hồ chỉ tồn tại 7 năm (1400–1407).');
            $this->sortQ($L, 'Kéo mỗi triều đại vào nhóm TỒN TẠI TRÊN hoặc DƯỚI 100 NĂM.',
                [['Nhà Lý (216 năm)', 'Trên 100 năm'], ['Nhà Trần (175 năm)', 'Trên 100 năm'],
                 ['Nhà Lê sơ (100 năm)', 'Trên 100 năm'], ['Nhà Nguyễn (143 năm)', 'Trên 100 năm'],
                 ['Nhà Hồ (7 năm)', 'Dưới 100 năm'], ['Nhà Tây Sơn (24 năm)', 'Dưới 100 năm']],
                'Nhà Lý tồn tại lâu nhất trong các triều đại phong kiến.');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm VUA, TƯỚNG LĨNH hoặc QUAN VĂN.',
                [['Lý Thái Tổ', 'Vua'], ['Lê Lợi', 'Vua'], ['Quang Trung', 'Vua'],
                 ['Trần Hưng Đạo', 'Tướng lĩnh'], ['Lý Thường Kiệt', 'Tướng lĩnh'],
                 ['Nguyễn Trãi', 'Quan văn']],
                'Nguyễn Trãi là nhà chính trị, quân sự và văn hóa kiệt xuất.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Năm 1226, nhà ___ được thành lập, mở đầu thời kì hưng thịnh mới.', [[0, 'Trần']],
                'Nhà Trần tồn tại 175 năm với nhiều thành tựu rực rỡ.');
            $this->fill($L, 'Tác giả của "Bình Ngô đại cáo" là ___.', [[0, 'Nguyễn Trãi']],
                'Được coi là bản tuyên ngôn độc lập thứ hai của dân tộc.');
            $this->fill($L, 'Năm 1789, Quang Trung đại phá quân ___ ở Ngọc Hồi – Đống Đa.', [[0, 'Thanh']],
                'Chiến thắng diễn ra vào mùng 5 Tết Kỉ Dậu.');
            $this->fill($L, 'Năm 1802, ___ lên ngôi hoàng đế, lập ra nhà Nguyễn.', [[0, 'Gia Long']],
                'Kinh đô đặt tại Phú Xuân (Huế).');
        }
    }

    private function seedLsThpt103(): void
    {
        $L = 'lich-su-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Quốc Tử Giám – trường đại học đầu tiên của Việt Nam được thành lập năm:',
                ['1070', '1076', '1010', '1225'], 1,
                'Năm 1076, vua Lý Nhân Tông cho mở Quốc Tử Giám; Văn Miếu dựng năm 1070.');
            $this->quiz($L, 'Khoa thi Nho học đầu tiên trong lịch sử Việt Nam được tổ chức năm:',
                ['1075', '1076', '1010', '1225'], 0,
                'Khoa thi Minh kinh bác học năm 1075 thời vua Lý Nhân Tông.');
            $this->quiz($L, 'Người thầy được tôn vinh là "vạn thế sư biểu" của Việt Nam là:',
                ['Chu Văn An', 'Nguyễn Trãi', 'Lê Quý Đôn', 'Ngô Sĩ Liên'], 0,
                'Chu Văn An là nhà giáo lỗi lạc thời Trần, thầy của nhiều vua Trần.');
            $this->quiz($L, 'Khoa thi cuối cùng của nền khoa cử Nho học Việt Nam diễn ra năm:',
                ['1918', '1919', '1945', '1905'], 1,
                'Khoa thi Hội cuối cùng năm 1919, khép lại hơn 800 năm khoa cử.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự kiện giáo dục với năm diễn ra.',
                [['Dựng Văn Miếu', '1070'], ['Mở Quốc Tử Giám', '1076'],
                 ['Khoa thi đầu tiên', '1075'], ['Khoa thi cuối cùng', '1919']],
                'Bốn mốc son của nền giáo dục – khoa cử phong kiến.');
            $this->matching($L, 'Nối mỗi nhân vật với danh hiệu hoặc đóng góp của ông.',
                [['Chu Văn An', 'Vạn thế sư biểu'], ['Nguyễn Hiền', 'Trạng nguyên nhỏ tuổi nhất'],
                 ['Lê Quý Đôn', 'Nhà bác học'], ['Mạc Đĩnh Chi', 'Lưỡng quốc trạng nguyên']],
                'Những tấm gương hiếu học trong lịch sử dân tộc.');
            $this->matching($L, 'Nối mỗi cấp thi với học vị đạt được.',
                [['Thi Hương', 'Cử nhân, Tú tài'], ['Thi Hội', 'Tiến sĩ, Phó bảng'],
                 ['Thi Đình', 'Trạng nguyên, Bảng nhãn, Thám hoa'],
                 ['Cả ba cấp', 'Con đường khoa cử Nho học']],
                'Thi Đình do vua trực tiếp ra đề và chấm.');
            $this->matching($L, 'Nối mỗi triều đại với chính sách giáo dục tiêu biểu.',
                [['Lý', 'Mở Quốc Tử Giám'], ['Trần', 'Mở rộng thi cử'],
                 ['Lê sơ', 'Hoàn chỉnh chế độ khoa cử'], ['Nguyễn', 'Duy trì khoa cử đến 1919']],
                'Nho học được coi trọng suốt thời phong kiến.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi học vị vào nhóm thuộc cấp THI HƯƠNG, THI HỘI hoặc THI ĐÌNH.',
                [['Cử nhân', 'Thi Hương'], ['Tú tài', 'Thi Hương'],
                 ['Tiến sĩ', 'Thi Hội'], ['Phó bảng', 'Thi Hội'],
                 ['Trạng nguyên', 'Thi Đình'], ['Bảng nhãn', 'Thi Đình']],
                'Tam khôi (Trạng nguyên, Bảng nhãn, Thám hoa) là vinh dự cao nhất.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Quốc Tử Giám là trường đại học đầu tiên của Việt Nam', 'Đúng'],
                 ['Khoa thi đầu tiên năm 1075 thời Lý', 'Đúng'],
                 ['Chu Văn An là thầy dạy của các vua Trần', 'Đúng'],
                 ['Khoa cử chỉ tuyển chọn võ quan', 'Sai'],
                 ['Thi Đình do vua trực tiếp ra đề', 'Đúng'],
                 ['Khoa thi cuối cùng diễn ra năm 1919', 'Đúng']],
                'Khoa cử tuyển chọn quan văn giúp việc triều chính.');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm NHÀ GIÁO, TRẠNG NGUYÊN hoặc NHÀ BÁC HỌC.',
                [['Chu Văn An', 'Nhà giáo'], ['Nguyễn Hiền', 'Trạng nguyên'],
                 ['Mạc Đĩnh Chi', 'Trạng nguyên'], ['Nguyễn Bỉnh Khiêm', 'Trạng nguyên'],
                 ['Lê Quý Đôn', 'Nhà bác học'], ['Lương Thế Vinh', 'Nhà bác học']],
                'Lương Thế Vinh giỏi toán, được mệnh danh trạng Lường.');
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm THỜI LÝ, THỜI LÊ hoặc THỜI NGUYỄN.',
                [['Văn Miếu (1070)', 'Thời Lý'], ['Quốc Tử Giám (1076)', 'Thời Lý'],
                 ['Khoa thi 1075', 'Thời Lý'], ['Bia đá Văn Miếu', 'Thời Lê'],
                 ['Trường Quốc học Huế', 'Thời Nguyễn'], ['Khoa thi 1919', 'Thời Nguyễn']],
                'Mỗi triều đại đều chăm lo giáo dục theo cách riêng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trường đại học đầu tiên của Việt Nam là ___.', [[0, 'Quốc Tử Giám']],
                'Được thành lập năm 1076 thời Lý Nhân Tông.');
            $this->fill($L, 'Khoa thi Nho học đầu tiên được tổ chức năm ___.', [[0, '1075']],
                'Khoa thi Minh kinh bác học.');
            $this->fill($L, 'Người thầy được tôn vinh là "vạn thế sư biểu" là ___.', [[0, 'Chu Văn An']],
                'Ông là nhà giáo đức độ thời Trần.');
            $this->fill($L, 'Khoa thi cuối cùng của nền khoa cử phong kiến diễn ra năm ___.', [[0, '1919']],
                'Khép lại hơn 800 năm khoa cử Nho học.');
        }
    }

    private function seedLsThpt104(): void
    {
        $L = 'lich-su-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Chùa Một Cột được xây dựng dưới thời triều đại nào?',
                ['Lý', 'Trần', 'Lê', 'Nguyễn'], 0,
                'Chùa Một Cột (Diên Hựu) xây năm 1049 thời Lý Thái Tông.');
            $this->quiz($L, 'Cố đô Huế là kinh đô của triều đại nào?',
                ['Lê', 'Tây Sơn', 'Nguyễn', 'Trần'], 2,
                'Nhà Nguyễn đóng đô ở Phú Xuân (Huế) từ 1802 đến 1945.');
            $this->quiz($L, 'Tháp Chăm là di sản kiến trúc của vương quốc nào?',
                ['Champa', 'Phù Nam', 'Lan Xang', 'Đại Lý'], 0,
                'Thánh địa Mỹ Sơn là trung tâm tôn giáo của vương quốc Champa.');
            $this->quiz($L, 'Hoàng thành Thăng Long được UNESCO công nhận di sản thế giới năm:',
                ['2010', '2008', '2000', '1993'], 0,
                'Nhân kỉ niệm 1000 năm Thăng Long – Hà Nội.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi công trình với triều đại xây dựng nó.',
                [['Chùa Một Cột', 'Lý'], ['Tháp Báo Thiên', 'Lý'],
                 ['Thành nhà Hồ', 'Hồ'], ['Hoàng thành Huế', 'Nguyễn']],
                'Mỗi triều đại để lại dấu ấn kiến trúc riêng.');
            $this->matching($L, 'Nối mỗi di sản UNESCO với địa phương của nó.',
                [['Hoàng thành Thăng Long', 'Hà Nội'], ['Cố đô Huế', 'Thừa Thiên Huế'],
                 ['Phố cổ Hội An', 'Quảng Nam'], ['Thánh địa Mỹ Sơn', 'Quảng Nam']],
                'Việt Nam có nhiều di sản văn hóa thế giới.');
            $this->matching($L, 'Nối mỗi loại hình nghệ thuật với đặc trưng của nó.',
                [['Ca trù', 'Hát thơ với đàn đáy'], ['Quan họ', 'Hát đối đáp giao duyên'],
                 ['Chèo', 'Sân khấu dân gian'], ['Tuồng', 'Sân khấu cung đình']],
                'Nghệ thuật truyền thống phong phú, đa dạng.');
            $this->matching($L, 'Nối mỗi di sản với năm UNESCO công nhận.',
                [['Cố đô Huế', '1993'], ['Phố cổ Hội An', '1999'],
                 ['Thánh địa Mỹ Sơn', '1999'], ['Hoàng thành Thăng Long', '2010']],
                'Cố đô Huế là di sản đầu tiên của Việt Nam (1993).');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi công trình vào nhóm DI SẢN UNESCO hoặc CHƯA ĐƯỢC CÔNG NHẬN.',
                [['Cố đô Huế', 'UNESCO'], ['Hoàng thành Thăng Long', 'UNESCO'],
                 ['Phố cổ Hội An', 'UNESCO'], ['Thánh địa Mỹ Sơn', 'UNESCO'],
                 ['Chùa Một Cột', 'Chưa'], ['Thành Cổ Loa', 'Chưa']],
                'UNESCO công nhận các di sản có giá trị toàn cầu.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Chùa Một Cột được xây dựng thời Lý', 'Đúng'],
                 ['Cố đô Huế là kinh đô của nhà Nguyễn', 'Đúng'],
                 ['Tháp Chăm thuộc văn hóa Óc Eo', 'Sai'],
                 ['Ca trù là di sản văn hóa phi vật thể', 'Đúng'],
                 ['Hoàng thành Thăng Long được công nhận năm 2010', 'Đúng'],
                 ['Nhã nhạc cung đình Huế là di sản UNESCO', 'Đúng']],
                'Tháp Chăm thuộc văn hóa Champa, không phải Óc Eo.');
            $this->sortQ($L, 'Kéo mỗi loại hình vào nhóm NGHỆ THUẬT CUNG ĐÌNH hoặc DÂN GIAN.',
                [['Nhã nhạc', 'Cung đình'], ['Tuồng', 'Cung đình'],
                 ['Chèo', 'Dân gian'], ['Quan họ', 'Dân gian'],
                 ['Ca trù', 'Dân gian'], ['Múa rối nước', 'Dân gian']],
                'Nghệ thuật cung đình phục vụ vua chúa; dân gian gắn với nhân dân.');
            $this->sortQ($L, 'Kéo mỗi công trình vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.',
                [['Chùa Một Cột', 'Bắc'], ['Hoàng thành Thăng Long', 'Bắc'],
                 ['Cố đô Huế', 'Trung'], ['Thánh địa Mỹ Sơn', 'Trung'],
                 ['Nhà thờ Đức Bà', 'Nam'], ['Dinh Độc Lập', 'Nam']],
                'Di sản kiến trúc phân bố khắp ba miền đất nước.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Chùa Một Cột được xây dựng dưới thời nhà ___.', [[0, 'Lý']],
                'Chùa có kiến trúc độc đáo: một cột đá giữa hồ sen.');
            $this->fill($L, 'Cố đô Huế được UNESCO công nhận di sản thế giới năm ___.', [[0, '1993']],
                'Là di sản thế giới đầu tiên của Việt Nam.');
            $this->fill($L, 'Thánh địa Mỹ Sơn là trung tâm tôn giáo của vương quốc ___.', [[0, 'Champa']],
                'Được UNESCO công nhận năm 1999.');
            $this->fill($L, 'Loại hình âm nhạc cung đình Huế nổi tiếng là ___.', [[0, 'Nhã nhạc']],
                'Nhã nhạc được UNESCO công nhận di sản phi vật thể.');
        }
    }

    // ================= LỊCH SỬ 11 =================

    private function seedLsThpt111(): void
    {
        $L = 'lich-su-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Chiếu Cần Vương được ban bố vào năm nào?',
                ['1884', '1885', '1886', '1887'], 1,
                'Tháng 7/1885, sau vụ Kinh thành Huế, vua Hàm Nghi ra Chiếu Cần Vương.');
            $this->quiz($L, 'Người lãnh đạo phong trào Cần Vương là:',
                ['Tôn Thất Thuyết', 'Hoàng Hoa Thám', 'Phan Đình Phùng', 'Nguyễn Thiện Thuật'], 0,
                'Tôn Thất Thuyết phò vua Hàm Nghi xuất bôn, phát động phong trào.');
            $this->quiz($L, 'Phong trào Cần Vương kết thúc năm 1896 với sự hi sinh của:',
                ['Phan Đình Phùng', 'Tôn Thất Thuyết', 'Hoàng Hoa Thám', 'Đinh Công Tráng'], 0,
                'Phan Đình Phùng – lãnh tụ khởi nghĩa Hương Khê – mất năm 1896.');
            $this->quiz($L, 'Căn cứ chính của khởi nghĩa Hương Khê đặt tại:',
                ['Ba Đình (Thanh Hóa)', 'Bãi Sậy (Hưng Yên)', 'Hương Khê (Hà Tĩnh)', 'Yên Thế (Bắc Giang)'], 2,
                'Hương Khê là cuộc khởi nghĩa tiêu biểu nhất của phong trào Cần Vương.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cuộc khởi nghĩa với người lãnh đạo của nó.',
                [['Hương Khê', 'Phan Đình Phùng'], ['Bãi Sậy', 'Nguyễn Thiện Thuật'],
                 ['Ba Đình', 'Đinh Công Tráng'], ['Hùng Lĩnh', 'Tống Duy Tân']],
                'Các lãnh tụ văn thân yêu nước cuối thế kỉ XIX.');
            $this->matching($L, 'Nối mỗi sự kiện với năm diễn ra.',
                [['Chiếu Cần Vương', '1885'], ['Pháp đánh Bắc Kì lần 2', '1882'],
                 ['Hiệp ước Pa-tơ-nốt', '1884'], ['Kết thúc phong trào Cần Vương', '1896']],
                'Hiệp ước 1884 đánh dấu Pháp hoàn thành xâm lược Việt Nam.');
            $this->matching($L, 'Nối mỗi căn cứ khởi nghĩa với địa bàn của nó.',
                [['Hương Khê', 'Hà Tĩnh'], ['Ba Đình', 'Thanh Hóa'],
                 ['Bãi Sậy', 'Hưng Yên'], ['Yên Thế', 'Bắc Giang']],
                'Nghĩa quân dựa vào địa hình rừng núi hiểm trở.');
            $this->matching($L, 'Nối mỗi nhân vật với vai trò của ông trong phong trào.',
                [['Hàm Nghi', 'Vua ban Chiếu Cần Vương'],
                 ['Tôn Thất Thuyết', 'Phò vua, phát động phong trào'],
                 ['Phan Đình Phùng', 'Lãnh đạo khởi nghĩa Hương Khê'],
                 ['Cao Thắng', 'Chế tạo súng trường kiểu mới']],
                'Cao Thắng chế tạo được súng trường bắn nhanh như súng Pháp.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi khởi nghĩa vào nhóm PHONG TRÀO CẦN VƯƠNG hoặc KHỞI NGHĨA NÔNG DÂN.',
                [['Hương Khê', 'Cần Vương'], ['Ba Đình', 'Cần Vương'],
                 ['Bãi Sậy', 'Cần Vương'], ['Yên Thế', 'Nông dân'],
                 ['Trương Định', 'Nông dân'], ['Nguyễn Trung Trực', 'Nông dân']],
                'Cần Vương do văn thân lãnh đạo; Yên Thế do nông dân lãnh đạo.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Phong trào Cần Vương kéo dài 1885–1896', 'Đúng'],
                 ['Tôn Thất Thuyết phò vua Hàm Nghi xuất bôn', 'Đúng'],
                 ['Khởi nghĩa Yên Thế thuộc phong trào Cần Vương', 'Sai'],
                 ['Hương Khê là khởi nghĩa tiêu biểu nhất', 'Đúng'],
                 ['Pháp hoàn thành xâm lược Việt Nam năm 1884', 'Đúng'],
                 ['Cần Vương do giai cấp nông dân lãnh đạo', 'Sai']],
                'Cần Vương do tầng lớp văn thân, sĩ phu lãnh đạo.');
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU Chiếu Cần Vương (1885).',
                [['Pháp đánh thành Hà Nội lần 2 (1882)', 'Trước'],
                 ['Hiệp ước Pa-tơ-nốt (1884)', 'Trước'],
                 ['Pháp đánh Đà Nẵng (1858)', 'Trước'],
                 ['Khởi nghĩa Hương Khê (1885–1896)', 'Sau'],
                 ['Khởi nghĩa Yên Thế (1884–1913)', 'Sau'],
                 ['Vụ Kinh thành Huế (7/1885)', 'Sau']],
                'Vụ Kinh thành Huế tháng 7/1885 là nguyên nhân trực tiếp.');
            $this->sortQ($L, 'Kéo mỗi địa danh căn cứ vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.',
                [['Ba Đình', 'Bắc'], ['Bãi Sậy', 'Bắc'], ['Yên Thế', 'Bắc'],
                 ['Hương Khê', 'Trung'], ['Gò Công', 'Nam'], ['Hà Tiên', 'Nam']],
                'Phong trào Cần Vương lan rộng khắp ba miền.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Chiếu Cần Vương do vua ___ ban bố tháng 7/1885.', [[0, 'Hàm Nghi']],
                'Kêu gọi văn thân, sĩ phu và nhân dân đứng lên chống Pháp.');
            $this->fill($L, 'Người phò vua Hàm Nghi, phát động phong trào Cần Vương là ___.', [[0, 'Tôn Thất Thuyết']],
                'Ông tổ chức vụ tấn công Kinh thành Huế đêm 4/7/1885.');
            $this->fill($L, 'Khởi nghĩa tiêu biểu nhất trong phong trào Cần Vương là khởi nghĩa ___.', [[0, 'Hương Khê']],
                'Do Phan Đình Phùng và Cao Thắng lãnh đạo.');
            $this->fill($L, 'Phong trào Cần Vương kết thúc năm ___.', [[0, '1896']],
                'Kéo dài hơn 10 năm với tinh thần bất khuất.');
        }
    }

    private function seedLsThpt112(): void
    {
        $L = 'lich-su-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khởi nghĩa Yên Thế (1884–1913) do ai lãnh đạo?',
                ['Hoàng Hoa Thám', 'Phan Đình Phùng', 'Nguyễn Thiện Thuật', 'Trương Định'], 0,
                'Hoàng Hoa Thám lãnh đạo nông dân Yên Thế chống Pháp gần 30 năm.');
            $this->quiz($L, 'Phong trào Đông Du (1905) do ai khởi xướng?',
                ['Phan Châu Trinh', 'Phan Bội Châu', 'Nguyễn Ái Quốc', 'Lương Văn Can'], 1,
                'Phan Bội Châu đưa thanh niên sang Nhật du học để học cách đánh Pháp.');
            $this->quiz($L, 'Đông Kinh Nghĩa Thục được thành lập năm nào?',
                ['1905', '1907', '1908', '1911'], 1,
                'Trường dạy chữ quốc ngữ miễn phí tại Hà Nội, bị Pháp đóng cửa năm 1908.');
            $this->quiz($L, 'Chủ trương cứu nước của Phan Châu Trinh là:',
                ['Bạo động vũ trang', 'Khai dân trí, chấn dân khí, hậu dân sinh', 'Cầu viện Nhật Bản', 'Khôi phục chế độ quân chủ'], 1,
                'Ông chủ trương cải cách, nâng cao dân trí trước khi giành độc lập.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phong trào với người lãnh đạo của nó.',
                [['Đông Du', 'Phan Bội Châu'], ['Duy Tân', 'Phan Châu Trinh'],
                 ['Đông Kinh Nghĩa Thục', 'Lương Văn Can'], ['Yên Thế', 'Hoàng Hoa Thám']],
                'Hai xu hướng cứu nước đầu thế kỉ XX: bạo động và cải cách.');
            $this->matching($L, 'Nối mỗi phong trào với năm bắt đầu.',
                [['Duy Tân hội', '1904'], ['Đông Du', '1905'],
                 ['Đông Kinh Nghĩa Thục', '1907'], ['Yên Thế bùng nổ', '1884']],
                'Phong trào Duy tân diễn ra sôi nổi đầu thế kỉ XX.');
            $this->matching($L, 'Nối mỗi chủ trương với người đề xướng.',
                [['Bạo động, cầu viện Nhật', 'Phan Bội Châu'],
                 ['Khai dân trí, chấn dân khí', 'Phan Châu Trinh'],
                 ['Dạy chữ quốc ngữ miễn phí', 'Đông Kinh Nghĩa Thục'],
                 ['Đánh Pháp giành độc lập', 'Cả hai xu hướng']],
                'Mục tiêu chung là giành độc lập, khác nhau về phương pháp.');
            $this->matching($L, 'Nối mỗi sự kiện với kết quả của nó.',
                [['Pháp – Nhật thỏa hiệp', 'Trục xuất du học sinh Việt Nam'],
                 ['Thuế nặng 1908', 'Phong trào chống thuế ở Trung Kì'],
                 ['Đông Kinh Nghĩa Thục lan rộng', 'Bị Pháp đóng cửa'],
                 ['Yên Thế kéo dài', 'Pháp phải nhiều lần giảng hòa']],
                'Các phong trào đều bị thực dân Pháp đàn áp.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phong trào vào nhóm BẠO ĐỘNG VŨ TRANG hoặc CẢI CÁCH – DUY TÂN.',
                [['Đông Du', 'Bạo động'], ['Yên Thế', 'Bạo động'],
                 ['Khởi nghĩa Thái Nguyên', 'Bạo động'], ['Việt Nam Quang Phục hội', 'Bạo động'],
                 ['Duy Tân (Phan Châu Trinh)', 'Cải cách'], ['Đông Kinh Nghĩa Thục', 'Cải cách']],
                'Hai xu hướng cứu nước song song đầu thế kỉ XX.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Hoàng Hoa Thám lãnh đạo khởi nghĩa Yên Thế', 'Đúng'],
                 ['Phan Bội Châu chủ trương cải cách ôn hòa', 'Sai'],
                 ['Đông Kinh Nghĩa Thục dạy chữ quốc ngữ miễn phí', 'Đúng'],
                 ['Phan Châu Trinh khởi xướng phong trào Duy Tân', 'Đúng'],
                 ['Phong trào Đông Du đưa thanh niên sang Trung Quốc', 'Sai'],
                 ['Khởi nghĩa Yên Thế kéo dài gần 30 năm', 'Đúng']],
                'Đông Du đưa thanh niên sang Nhật Bản, không phải Trung Quốc.');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm HOẠT ĐỘNG Ở MIỀN BẮC hoặc MIỀN TRUNG.',
                [['Hoàng Hoa Thám', 'Bắc'], ['Lương Văn Can', 'Bắc'],
                 ['Nguyễn Thượng Hiền', 'Bắc'], ['Phan Bội Châu', 'Trung'],
                 ['Phan Châu Trinh', 'Trung'], ['Huỳnh Thúc Kháng', 'Trung']],
                'Phong trào Duy tân lan rộng khắp cả nước.');
            $this->sortQ($L, 'Kéo mỗi năm vào nhóm THẾ KỈ XIX hoặc THẾ KỈ XX.',
                [['1884', 'XIX'], ['1896', 'XIX'],
                 ['1905', 'XX'], ['1907', 'XX'], ['1908', 'XX'], ['1913', 'XX']],
                'Yên Thế bắt đầu cuối thế kỉ XIX, kết thúc đầu thế kỉ XX.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Người lãnh đạo khởi nghĩa Yên Thế là ___.', [[0, 'Hoàng Hoa Thám']],
                'Ông còn được gọi là Đề Thám.');
            $this->fill($L, 'Phong trào Đông Du đưa thanh niên Việt Nam sang ___ du học.', [[0, 'Nhật Bản']],
                'Do Phan Bội Châu tổ chức từ năm 1905.');
            $this->fill($L, 'Đông Kinh Nghĩa Thục được thành lập năm ___ tại Hà Nội.', [[0, '1907']],
                'Trường dạy chữ quốc ngữ miễn phí cho nhân dân.');
            $this->fill($L, 'Khẩu hiệu của Phan Châu Trinh: "Khai dân trí, chấn dân khí, ___ dân sinh".', [[0, 'hậu']],
                'Chủ trương cải cách thay vì bạo động.');
        }
    }

    private function seedLsThpt113(): void
    {
        $L = 'lich-su-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nguyễn Tất Thành ra đi tìm đường cứu nước vào năm nào?',
                ['1905', '1911', '1919', '1920'], 1,
                'Ngày 5/6/1911, Người rời bến Nhà Rồng (Sài Gòn) trên tàu Đô đốc La-tu-sơ Tơ-rê-vin.');
            $this->quiz($L, 'Nguyễn Ái Quốc tham gia sáng lập Đảng Cộng sản Pháp vào năm:',
                ['1919', '1920', '1925', '1930'], 1,
                'Tại Đại hội Tua (12/1920), Người bỏ phiếu tán thành Quốc tế III.');
            $this->quiz($L, 'Đảng Cộng sản Việt Nam được thành lập vào ngày:',
                ['3/2/1930', '2/9/1945', '19/8/1945', '1/5/1930'], 0,
                'Hội nghị hợp nhất tại Hương Cảng do Nguyễn Ái Quốc chủ trì.');
            $this->quiz($L, 'Hội nghị thành lập Đảng Cộng sản Việt Nam do ai chủ trì?',
                ['Trần Phú', 'Nguyễn Ái Quốc', 'Lê Hồng Phong', 'Ngô Gia Tự'], 1,
                'Người đã hợp nhất ba tổ chức cộng sản thành một đảng duy nhất.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự kiện với năm diễn ra.',
                [['Ra đi tìm đường cứu nước', '1911'],
                 ['Gửi yêu sách đến Hội nghị Véc-xai', '1919'],
                 ['Tham gia sáng lập Đảng Cộng sản Pháp', '1920'],
                 ['Thành lập Đảng Cộng sản Việt Nam', '1930']],
                'Hành trình 20 năm tìm đường cứu nước của Nguyễn Ái Quốc.');
            $this->matching($L, 'Nối mỗi tổ chức với năm thành lập.',
                [['Hội Việt Nam Cách mạng Thanh niên', '1925'],
                 ['Đông Dương Cộng sản Đảng', '1929'],
                 ['An Nam Cộng sản Đảng', '1929'],
                 ['Đảng Cộng sản Việt Nam', '1930']],
                'Ba tổ chức cộng sản năm 1929 được hợp nhất năm 1930.');
            $this->matching($L, 'Nối mỗi tác phẩm với tác giả của nó.',
                [['Bản án chế độ thực dân Pháp', 'Nguyễn Ái Quốc'],
                 ['Đường Kách mệnh', 'Nguyễn Ái Quốc'],
                 ['Nhật kí trong tù', 'Hồ Chí Minh'],
                 ['Tuyên ngôn độc lập', 'Hồ Chí Minh']],
                'Tác phẩm lí luận chuẩn bị cho cách mạng Việt Nam.');
            $this->matching($L, 'Nối mỗi địa điểm với sự kiện diễn ra ở đó.',
                [['Bến Nhà Rồng', 'Ra đi tìm đường cứu nước 1911'],
                 ['Hương Cảng', 'Hợp nhất thành lập Đảng'],
                 ['Quảng Châu', 'Hội Việt Nam Cách mạng Thanh niên'],
                 ['Pa-ri', 'Hoạt động cách mạng 1919–1923']],
                'Dấu chân cách mạng của Người khắp ba châu lục.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU khi thành lập Đảng (3/2/1930).',
                [['Ra đi tìm đường cứu nước 1911', 'Trước'],
                 ['Sáng lập Đảng Cộng sản Pháp 1920', 'Trước'],
                 ['Hội Việt Nam Cách mạng Thanh niên 1925', 'Trước'],
                 ['Xô viết Nghệ Tĩnh 1930–1931', 'Sau'],
                 ['Luận cương chính trị 1930', 'Sau'],
                 ['Mặt trận Việt Minh 1941', 'Sau']],
                'Đảng ra đời mở ra thời kì mới của cách mạng Việt Nam.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nguyễn Tất Thành ra đi năm 1911 từ bến Nhà Rồng', 'Đúng'],
                 ['Đảng Cộng sản Việt Nam thành lập ngày 3/2/1930', 'Đúng'],
                 ['Hội nghị thành lập Đảng họp ở Pa-ri', 'Sai'],
                 ['Nguyễn Ái Quốc chủ trì hội nghị hợp nhất', 'Đúng'],
                 ['Tên ban đầu là Đảng Cộng sản Đông Dương', 'Sai'],
                 ['Luận cương chính trị do Trần Phú soạn thảo', 'Đúng']],
                'Hội nghị họp tại Hương Cảng (Trung Quốc); tên ban đầu là Đảng Cộng sản Việt Nam.');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm LÃNH ĐẠO ĐẢNG hoặc CHÍ SĨ YÊU NƯỚC ĐẦU THẾ KỈ XX.',
                [['Nguyễn Ái Quốc', 'Lãnh đạo Đảng'], ['Trần Phú', 'Lãnh đạo Đảng'],
                 ['Lê Hồng Phong', 'Lãnh đạo Đảng'], ['Phan Bội Châu', 'Chí sĩ'],
                 ['Phan Châu Trinh', 'Chí sĩ'], ['Lương Văn Can', 'Chí sĩ']],
                'Thế hệ chí sĩ đi trước mở đường cho cách mạng vô sản.');
            $this->sortQ($L, 'Kéo mỗi năm vào nhóm THẬP NIÊN 1910, 1920 hoặc 1930.',
                [['1911', '1910'], ['1919', '1910'],
                 ['1920', '1920'], ['1925', '1920'], ['1929', '1920'], ['1930', '1930']],
                'Ba thập niên định hình con đường cứu nước.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ngày 5/6/1911, Nguyễn Tất Thành rời bến ___ ra đi tìm đường cứu nước.', [[0, 'Nhà Rồng']],
                'Người làm phụ bếp trên tàu sang Pháp.');
            $this->fill($L, 'Đảng Cộng sản Việt Nam được thành lập ngày ___/2/1930.', [[0, '3']],
                'Ngày 3/2 hằng năm là ngày thành lập Đảng.');
            $this->fill($L, 'Hội nghị hợp nhất các tổ chức cộng sản họp tại ___ (Trung Quốc).', [[0, 'Hương Cảng']],
                'Nay là Hồng Kông, Trung Quốc.');
            $this->fill($L, 'Tác phẩm "Đường Kách mệnh" là của ___.', [[0, 'Nguyễn Ái Quốc']],
                'Tập hợp bài giảng cho thanh niên yêu nước ở Quảng Châu.');
        }
    }

    private function seedLsThpt114(): void
    {
        $L = 'lich-su-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phong trào Xô viết Nghệ Tĩnh diễn ra trong thời gian nào?',
                ['1930–1931', '1936–1939', '1940–1941', '1944–1945'], 0,
                'Đỉnh cao là Xô viết Nghệ Tĩnh – chính quyền Xô viết đầu tiên của nhân dân.');
            $this->quiz($L, 'Mặt trận Việt Nam độc lập đồng minh (Việt Minh) thành lập năm:',
                ['1930', '1936', '1941', '1945'], 2,
                'Hội nghị Trung ương 8 (5/1941) do Nguyễn Ái Quốc chủ trì quyết định thành lập.');
            $this->quiz($L, 'Đội Việt Nam Tuyên truyền Giải phóng quân được thành lập ngày:',
                ['22/12/1944', '19/8/1945', '2/9/1945', '3/2/1930'], 0,
                'Tiền thân của Quân đội nhân dân Việt Nam.');
            $this->quiz($L, 'Người chỉ huy Đội Việt Nam Tuyên truyền Giải phóng quân là:',
                ['Võ Nguyên Giáp', 'Hồ Chí Minh', 'Trường Chinh', 'Phạm Văn Đồng'], 0,
                'Đội gồm 34 chiến sĩ với vũ khí thô sơ tại Cao Bằng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phong trào với thời gian diễn ra.',
                [['Xô viết Nghệ Tĩnh', '1930–1931'], ['Đấu tranh dân chủ', '1936–1939'],
                 ['Khởi nghĩa Bắc Sơn', '1940'], ['Khởi nghĩa Nam Kì', '1940']],
                'Ba cao trào cách mạng trước Tổng khởi nghĩa.');
            $this->matching($L, 'Nối mỗi sự kiện với năm diễn ra.',
                [['Mặt trận Việt Minh', '1941'], ['Đội Việt Nam Tuyên truyền Giải phóng quân', '1944'],
                 ['Tổng khởi nghĩa', '8/1945'], ['Tuyên ngôn độc lập', '2/9/1945']],
                'Chuỗi sự kiện dẫn đến thắng lợi tháng 8/1945.');
            $this->matching($L, 'Nối mỗi nhân vật với vai trò của ông/bà.',
                [['Võ Nguyên Giáp', 'Chỉ huy Đội Việt Nam Tuyên truyền Giải phóng quân'],
                 ['Trường Chinh', 'Tổng Bí thư của Đảng'],
                 ['Tôn Đức Thắng', 'Lãnh đạo phong trào công nhân'],
                 ['Nguyễn Thị Minh Khai', 'Lãnh đạo Xô viết Nghệ Tĩnh']],
                'Những người con ưu tú của cách mạng.');
            $this->matching($L, 'Nối mỗi cuộc khởi nghĩa năm 1940 với địa bàn của nó.',
                [['Bắc Sơn', 'Lạng Sơn'], ['Nam Kì', 'Nam Bộ'],
                 ['Đô Lương', 'Nghệ An'], ['Ba Tơ', 'Quảng Ngãi']],
                'Các cuộc khởi nghĩa từng phần năm 1940–1941.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm THẬP NIÊN 1930 hoặc THẬP NIÊN 1940.',
                [['Xô viết Nghệ Tĩnh', '1930'], ['Phong trào dân chủ 1936–1939', '1930'],
                 ['Khởi nghĩa Bắc Sơn 1940', '1940'], ['Việt Minh 1941', '1940'],
                 ['Đội Việt Nam Tuyên truyền Giải phóng quân 1944', '1940'],
                 ['Tổng khởi nghĩa 1945', '1940']],
                'Hai thập niên chuẩn bị cho thắng lợi 1945.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Xô viết Nghệ Tĩnh là chính quyền Xô viết đầu tiên', 'Đúng'],
                 ['Mặt trận Việt Minh thành lập năm 1941', 'Đúng'],
                 ['Đội Việt Nam Tuyên truyền Giải phóng quân thành lập 22/12/1944', 'Đúng'],
                 ['Khởi nghĩa Nam Kì nổ ra năm 1941', 'Sai'],
                 ['Nhật đảo chính Pháp ngày 9/3/1945', 'Đúng'],
                 ['Tổng khởi nghĩa giành chính quyền tháng 8/1945', 'Đúng']],
                'Khởi nghĩa Nam Kì nổ ra đêm 22 rạng 23/11/1940.');
            $this->sortQ($L, 'Kéo mỗi lực lượng vào nhóm CỦA VIỆT MINH hoặc CỦA ĐỊCH.',
                [['Đội Việt Nam Tuyên truyền Giải phóng quân', 'Việt Minh'],
                 ['Cứu quốc quân', 'Việt Minh'], ['Tự vệ đỏ', 'Việt Minh'],
                 ['Quân Nhật', 'Địch'], ['Quân Pháp', 'Địch'], ['Bảo an binh', 'Địch']],
                'Lực lượng vũ trang cách mạng đối đầu quân Nhật – Pháp.');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.',
                [['Pác Bó', 'Bắc'], ['Tân Trào', 'Bắc'],
                 ['Nghệ Tĩnh', 'Trung'], ['Ba Tơ', 'Trung'],
                 ['Nam Kì', 'Nam'], ['Sài Gòn', 'Nam']],
                'Phong trào cách mạng lan rộng cả ba miền.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phong trào Xô viết Nghệ Tĩnh diễn ra trong các năm ___–1931.', [[0, '1930']],
                'Đỉnh cao của cao trào cách mạng 1930–1931.');
            $this->fill($L, 'Mặt trận Việt Nam độc lập đồng minh gọi tắt là ___.', [[0, 'Việt Minh']],
                'Thành lập tháng 5/1941 tại Pác Bó.');
            $this->fill($L, 'Đội Việt Nam Tuyên truyền Giải phóng quân thành lập ngày 22/12/___ tại Cao Bằng.', [[0, '1944']],
                'Ngày 22/12 hằng năm là Ngày thành lập Quân đội nhân dân Việt Nam.');
            $this->fill($L, 'Ngày 2/9/1945, Chủ tịch Hồ Chí Minh đọc ___ tại Quảng trường Ba Đình.', [[0, 'Tuyên ngôn độc lập']],
                'Khai sinh nước Việt Nam Dân chủ Cộng hòa.');
        }
    }

    // ================= LỊCH SỬ 12 =================

    private function seedLsThpt121(): void
    {
        $L = 'lich-su-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cách mạng tháng Tám thắng lợi ở Hà Nội vào ngày nào?',
                ['19/8/1945', '23/8/1945', '25/8/1945', '2/9/1945'], 0,
                'Ngày 19/8/1945, Hà Nội khởi nghĩa giành chính quyền.');
            $this->quiz($L, 'Chiến dịch Điện Biên Phủ kết thúc thắng lợi ngày:',
                ['7/5/1954', '21/7/1954', '20/12/1946', '19/12/1946'], 0,
                '"Chín năm làm một Điện Biên, nên vành hoa đỏ nên thiên sử vàng."');
            $this->quiz($L, 'Người trực tiếp chỉ huy chiến dịch Điện Biên Phủ là:',
                ['Võ Nguyên Giáp', 'Hồ Chí Minh', 'Trường Chinh', 'Lê Duẩn'], 0,
                'Đại tướng Võ Nguyên Giáp là Tổng tư lệnh, trực tiếp chỉ huy chiến dịch.');
            $this->quiz($L, 'Hiệp định Genève về chấm dứt chiến tranh ở Đông Dương được kí năm:',
                ['1946', '1954', '1973', '1975'], 1,
                'Kí ngày 21/7/1954, công nhận độc lập, chủ quyền của Việt Nam.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự kiện với thời gian diễn ra.',
                [['Toàn quốc kháng chiến', '19/12/1946'], ['Chiến dịch Biên giới', '1950'],
                 ['Chiến dịch Điện Biên Phủ', '1954'], ['Hiệp định Genève', '7/1954']],
                'Chín năm kháng chiến chống thực dân Pháp (1946–1954).');
            $this->matching($L, 'Nối mỗi chiến dịch với năm mở màn.',
                [['Việt Bắc thu – đông', '1947'], ['Biên giới', '1950'],
                 ['Hòa Bình', '1951'], ['Điện Biên Phủ', '1954']],
                'Các chiến dịch lớn làm phá sản chiến lược của Pháp.');
            $this->matching($L, 'Nối mỗi nhân vật với vai trò của ông.',
                [['Hồ Chí Minh', 'Ra Lời kêu gọi toàn quốc kháng chiến'],
                 ['Võ Nguyên Giáp', 'Tổng tư lệnh quân đội'],
                 ['Trường Chinh', 'Tổng Bí thư của Đảng'],
                 ['Tố Hữu', 'Nhà thơ của kháng chiến']],
                'Toàn dân đoàn kết trong cuộc kháng chiến.');
            $this->matching($L, 'Nối mỗi mốc lịch sử với ý nghĩa của nó.',
                [['2/9/1945', 'Khai sinh nước Việt Nam Dân chủ Cộng hòa'],
                 ['19/12/1946', 'Toàn quốc kháng chiến'],
                 ['7/5/1954', 'Chiến thắng Điện Biên Phủ'],
                 ['21/7/1954', 'Kí Hiệp định Genève']],
                'Bốn mốc son của giai đoạn 1945–1954.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU Hiệp định Genève (1954).',
                [['Cách mạng tháng Tám', 'Trước'], ['Toàn quốc kháng chiến', 'Trước'],
                 ['Chiến dịch Điện Biên Phủ', 'Trước'], ['Phong trào Đồng Khởi', 'Sau'],
                 ['Tết Mậu Thân', 'Sau'], ['Hiệp định Paris', 'Sau']],
                'Genève khép lại kháng chiến chống Pháp, mở ra kháng chiến chống Mĩ.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Cách mạng tháng Tám thắng lợi ở Hà Nội ngày 19/8/1945', 'Đúng'],
                 ['Chiến dịch Điện Biên Phủ kết thúc ngày 7/5/1954', 'Đúng'],
                 ['Hiệp định Genève lấy vĩ tuyến 17 làm giới tuyến tạm thời', 'Đúng'],
                 ['Cuộc kháng chiến chống Pháp kéo dài 9 năm', 'Đúng'],
                 ['Pháp rút khỏi miền Bắc ngay trong năm 1954', 'Sai'],
                 ['Sau 1954, Mĩ thay chân Pháp ở miền Nam', 'Đúng']],
                'Pháp rút khỏi miền Bắc năm 1955 theo điều khoản hiệp định.');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.',
                [['Điện Biên Phủ', 'Bắc'], ['Việt Bắc', 'Bắc'],
                 ['Huế', 'Trung'], ['Đà Nẵng', 'Trung'],
                 ['Sài Gòn', 'Nam'], ['Bến Tre', 'Nam']],
                'Chiến trường kháng chiến trải dài cả nước.');
            $this->sortQ($L, 'Kéo mỗi lực lượng vào nhóm CỦA TA hoặc CỦA ĐỊCH (năm 1954).',
                [['Quân đội nhân dân Việt Nam', 'Ta'], ['Dân công hỏa tuyến', 'Ta'],
                 ['Thanh niên xung phong', 'Ta'], ['Quân viễn chinh Pháp', 'Địch'],
                 ['Quân đội Quốc gia', 'Địch'], ['Binh đoàn dù Pháp', 'Địch']],
                'Sức mạnh toàn dân làm nên Điện Biên Phủ.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ngày 19/8/1945, nhân dân Hà Nội khởi nghĩa giành ___.', [[0, 'chính quyền']],
                'Mở đầu thắng lợi của Cách mạng tháng Tám trong cả nước.');
            $this->fill($L, 'Chiến thắng Điện Biên Phủ diễn ra ngày 7/5/___ "lừng lẫy năm châu".', [[0, '1954']],
                'Kết thúc 56 ngày đêm chiến đấu anh dũng.');
            $this->fill($L, 'Hiệp định Genève lấy vĩ tuyến ___ làm giới tuyến quân sự tạm thời.', [[0, '17']],
                'Hai miền tập kết, chờ tổng tuyển cử thống nhất.');
            $this->fill($L, 'Lời kêu gọi "Toàn quốc kháng chiến" do Chủ tịch ___ phát động ngày 19/12/1946.', [[0, 'Hồ Chí Minh']],
                '"Thà hi sinh tất cả chứ nhất định không chịu mất nước".');
        }
    }

    private function seedLsThpt122(): void
    {
        $L = 'lich-su-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phong trào Đồng Khởi (1960) bùng nổ đầu tiên tại tỉnh nào?',
                ['Bến Tre', 'Quảng Ngãi', 'Tây Ninh', 'Củ Chi'], 0,
                'Ngày 17/1/1960, Bến Tre nổi dậy, mở đầu phong trào Đồng Khởi.');
            $this->quiz($L, 'Chiến dịch Hồ Chí Minh lịch sử kết thúc thắng lợi ngày:',
                ['30/4/1975', '27/1/1973', '7/5/1954', '19/8/1945'], 0,
                '11 giờ 30 phút ngày 30/4/1975, cờ giải phóng tung bay trên Dinh Độc Lập.');
            $this->quiz($L, '"Điện Biên Phủ trên không" diễn ra vào tháng nào?',
                ['12/1972', '1/1968', '4/1975', '3/1965'], 0,
                '12 ngày đêm cuối năm 1972, Hà Nội bắn rơi nhiều pháo đài bay B-52.');
            $this->quiz($L, 'Hiệp định Paris về chấm dứt chiến tranh ở Việt Nam được kí ngày:',
                ['27/1/1973', '21/7/1954', '30/4/1975', '2/9/1945'], 0,
                'Mĩ buộc phải rút quân, công nhận độc lập, chủ quyền của Việt Nam.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự kiện với thời gian diễn ra.',
                [['Phong trào Đồng Khởi', '1960'], ['Tổng tiến công Tết Mậu Thân', '1968'],
                 ['Điện Biên Phủ trên không', '12/1972'], ['Chiến dịch Hồ Chí Minh', '4/1975']],
                'Các bước ngoặt của kháng chiến chống Mĩ.');
            $this->matching($L, 'Nối mỗi chiến lược chiến tranh của Mĩ với thời gian thực hiện.',
                [['Chiến tranh đặc biệt', '1961–1965'],
                 ['Chiến tranh cục bộ', '1965–1968'],
                 ['Việt Nam hóa chiến tranh', '1969–1973'],
                 ['Chiến tranh phá hoại miền Bắc', '1965–1972']],
                'Mĩ thay đổi chiến lược nhưng đều thất bại.');
            $this->matching($L, 'Nối mỗi nhân vật với vai trò của ông/bà.',
                [['Lê Duẩn', 'Bí thư thứ nhất của Đảng'],
                 ['Võ Nguyên Giáp', 'Tổng tư lệnh quân đội'],
                 ['Nguyễn Thị Định', 'Lãnh đạo phong trào Đồng Khởi'],
                 ['Phạm Hùng', 'Bí thư Trung ương Cục miền Nam']],
                'Những người lãnh đạo kiệt xuất của kháng chiến.');
            $this->matching($L, 'Nối mỗi địa danh với sự kiện gắn liền.',
                [['Khe Sanh', 'Chiến dịch 1968'], ['Thành cổ Quảng Trị', 'Mùa hè đỏ lửa 1972'],
                 ['Hà Nội', 'Điện Biên Phủ trên không'], ['Sài Gòn', 'Đại thắng 30/4/1975']],
                'Địa danh ghi dấu những chiến công.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm THẬP NIÊN 1960 hoặc THẬP NIÊN 1970.',
                [['Đồng Khởi', '1960'], ['Mậu Thân 1968', '1960'], ['Ấp Bắc 1963', '1960'],
                 ['Hiệp định Paris', '1970'], ['Điện Biên Phủ trên không', '1970'],
                 ['Chiến dịch Hồ Chí Minh', '1970']],
                '21 năm kháng chiến chống Mĩ (1954–1975).');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Phong trào Đồng Khởi mở đầu năm 1960 ở Bến Tre', 'Đúng'],
                 ['Mĩ đổ quân trực tiếp vào miền Nam từ năm 1965', 'Đúng'],
                 ['Hiệp định Paris được kí ngày 27/1/1973', 'Đúng'],
                 ['Miền Nam giải phóng ngày 30/4/1976', 'Sai'],
                 ['Điện Biên Phủ trên không diễn ra tháng 12/1972', 'Đúng'],
                 ['Chiến tranh đặc biệt diễn ra 1961–1965', 'Đúng']],
                'Miền Nam hoàn toàn giải phóng ngày 30/4/1975.');
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU Hiệp định Paris (1/1973).',
                [['Phong trào Đồng Khởi', 'Trước'], ['Tết Mậu Thân 1968', 'Trước'],
                 ['Điện Biên Phủ trên không', 'Trước'], ['Chiến dịch Hồ Chí Minh', 'Sau'],
                 ['Giải phóng Phước Long', 'Sau'], ['Tổng tiến công và nổi dậy 1975', 'Sau']],
                'Paris buộc Mĩ rút quân, tạo thời cơ giải phóng miền Nam.');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.',
                [['Hà Nội', 'Bắc'], ['Hải Phòng', 'Bắc'],
                 ['Quảng Trị', 'Trung'], ['Huế', 'Trung'],
                 ['Củ Chi', 'Nam'], ['Bến Tre', 'Nam']],
                'Cả nước là một chiến trường thống nhất.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phong trào Đồng Khởi (1960) bắt đầu từ tỉnh ___.', [[0, 'Bến Tre']],
                'Do Tỉnh ủy Bến Tre lãnh đạo, lan ra khắp Nam Bộ.');
            $this->fill($L, 'Hiệp định Paris về Việt Nam được kí ngày 27/1/___.', [[0, '1973']],
                'Mĩ cam kết rút hết quân khỏi miền Nam Việt Nam.');
            $this->fill($L, 'Chiến dịch Hồ Chí Minh toàn thắng ngày 30/4/___ .', [[0, '1975']],
                'Kết thúc 21 năm kháng chiến chống Mĩ, thống nhất đất nước.');
            $this->fill($L, 'Tháng 12/1972, quân dân miền Bắc làm nên chiến thắng "___ trên không".', [[0, 'Điện Biên Phủ']],
                'Buộc Mĩ phải trở lại bàn đàm phán Paris.');
        }
    }

    private function seedLsThpt123(): void
    {
        $L = 'lich-su-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đường lối Đổi mới của Việt Nam được đề ra tại:',
                ['Đại hội VI (1986)', 'Đại hội V (1982)', 'Đại hội VII (1991)', 'Hội nghị Trung ương 6'], 0,
                'Đại hội VI tháng 12/1986 đề ra đường lối đổi mới toàn diện.');
            $this->quiz($L, 'Trước Đổi mới, nền kinh tế – xã hội Việt Nam rơi vào tình trạng:',
                ['Khủng hoảng trầm trọng', 'Phát triển ổn định', 'Tăng trưởng cao', 'Không có khó khăn'], 0,
                'Khủng hoảng của mô hình bao cấp, kế hoạch hóa tập trung.');
            $this->quiz($L, 'Nội dung trọng tâm của công cuộc Đổi mới là:',
                ['Đổi mới kinh tế', 'Đổi mới chính trị', 'Đổi mới văn hóa', 'Đổi mới quân sự'], 0,
                'Đổi mới toàn diện nhưng trọng tâm là đổi mới kinh tế.');
            $this->quiz($L, 'Mô hình kinh tế của Việt Nam sau Đổi mới là:',
                ['Kinh tế thị trường định hướng xã hội chủ nghĩa', 'Kinh tế kế hoạch hóa tập trung', 'Kinh tế tự nhiên', 'Kinh tế bao cấp'], 0,
                'Phát triển nền kinh tế nhiều thành phần vận hành theo cơ chế thị trường.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự kiện với năm diễn ra.',
                [['Đại hội VI', '1986'], ['Xóa bỏ bao cấp về giá', '1989'],
                 ['Luật Đầu tư nước ngoài', '1987'], ['Bình thường hóa quan hệ với Hoa Kì', '1995']],
                'Những bước đi đầu của Đổi mới.');
            $this->matching($L, 'Nối mỗi chính sách với nội dung của nó.',
                [['Khoán 10', 'Khoán hộ trong nông nghiệp'],
                 ['Xóa bao cấp', 'Giá cả vận hành theo thị trường'],
                 ['Đa thành phần', 'Nhiều thành phần kinh tế cùng phát triển'],
                 ['Mở cửa', 'Hội nhập kinh tế quốc tế']],
                'Các chính sách đột phá của Đổi mới.');
            $this->matching($L, 'Nối mỗi giai đoạn với đặc điểm của nó.',
                [['Trước 1986', 'Bao cấp, khủng hoảng'], ['1986–1990', 'Bước đầu đổi mới'],
                 ['Sau 1990', 'Đẩy mạnh công nghiệp hóa'], ['Sau 2000', 'Hội nhập sâu rộng']],
                'Đổi mới là quá trình liên tục, không ngừng.');
            $this->matching($L, 'Nối mỗi thành tựu với lĩnh vực của nó.',
                [['Xuất khẩu gạo top đầu', 'Nông nghiệp'], ['Thu hút FDI', 'Đầu tư'],
                 ['Xóa đói giảm nghèo', 'Xã hội'], ['Tăng trưởng GDP', 'Kinh tế vĩ mô']],
                'Đổi mới mang lại thành tựu toàn diện.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chính sách vào nhóm TRƯỚC ĐỔI MỚI hoặc SAU ĐỔI MỚI.',
                [['Bao cấp', 'Trước'], ['Kế hoạch hóa tập trung', 'Trước'],
                 ['Ngăn sông cấm chợ', 'Trước'], ['Khoán hộ', 'Sau'],
                 ['Kinh tế nhiều thành phần', 'Sau'], ['Giá cả thị trường', 'Sau']],
                'Đổi mới xóa bỏ cơ chế bao cấp trì trệ.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Đổi mới bắt đầu từ Đại hội VI năm 1986', 'Đúng'],
                 ['Trọng tâm của Đổi mới là kinh tế', 'Đúng'],
                 ['Trước Đổi mới Việt Nam tăng trưởng cao', 'Sai'],
                 ['Khoán 10 thúc đẩy nông nghiệp phát triển', 'Đúng'],
                 ['Đổi mới giữ nguyên mô hình bao cấp', 'Sai'],
                 ['Sau Đổi mới Việt Nam xuất khẩu gạo top đầu', 'Đúng']],
                'Từ nước thiếu lương thực, Việt Nam thành nước xuất khẩu gạo hàng đầu.');
            $this->sortQ($L, 'Kéo mỗi năm vào nhóm TRƯỚC hoặc SAU Đổi mới (1986).',
                [['1976', 'Trước'], ['1982', 'Trước'], ['1975', 'Trước'],
                 ['1987', 'Sau'], ['1989', 'Sau'], ['1995', 'Sau']],
                'Năm 1986 là mốc son của đất nước.');
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRONG NƯỚC hoặc QUỐC TẾ.',
                [['Đại hội VI', 'Trong nước'], ['Khoán 10', 'Trong nước'],
                 ['Xóa bao cấp', 'Trong nước'], ['Liên Xô tan rã 1991', 'Quốc tế'],
                 ['Hoa Kì bỏ cấm vận 1994', 'Quốc tế'], ['Khủng hoảng châu Á 1997', 'Quốc tế']],
                'Bối cảnh quốc tế tác động mạnh đến Đổi mới.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đường lối Đổi mới được đề ra tại Đại hội ___ của Đảng năm 1986.', [[0, 'VI']],
                'Đại hội VI họp tháng 12/1986 tại Hà Nội.');
            $this->fill($L, 'Chính sách "khoán hộ" trong nông nghiệp còn gọi là Khoán ___.', [[0, '10']],
                'Nghị quyết 10 (1988) giao ruộng đất cho hộ nông dân.');
            $this->fill($L, 'Mô hình kinh tế Việt Nam sau Đổi mới: kinh tế thị trường định hướng ___.', [[0, 'xã hội chủ nghĩa']],
                'Vừa phát triển kinh tế thị trường vừa giữ định hướng XHCN.');
            $this->fill($L, 'Năm 1989, Việt Nam cơ bản xóa bỏ chế độ ___.', [[0, 'bao cấp']],
                'Giá cả chuyển sang vận hành theo cơ chế thị trường.');
        }
    }

    private function seedLsThpt124(): void
    {
        $L = 'lich-su-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Việt Nam chính thức gia nhập ASEAN vào năm nào?',
                ['1995', '1997', '1998', '2007'], 0,
                'Ngày 28/7/1995, Việt Nam trở thành thành viên thứ 7 của ASEAN.');
            $this->quiz($L, 'Việt Nam trở thành thành viên của Tổ chức Thương mại Thế giới (WTO) năm:',
                ['2001', '2007', '2015', '1995'], 1,
                'Việt Nam là thành viên thứ 150 của WTO.');
            $this->quiz($L, 'Việt Nam gia nhập Diễn đàn Hợp tác Kinh tế châu Á – Thái Bình Dương (APEC) năm:',
                ['1995', '1998', '2007', '2015'], 1,
                'Hội nhập sâu rộng vào kinh tế khu vực châu Á – Thái Bình Dương.');
            $this->quiz($L, 'Việt Nam đảm nhiệm vị trí Ủy viên không thường trực Hội đồng Bảo an Liên hợp quốc nhiệm kì:',
                ['2020–2021', '2018–2019', '2022–2023', '2008–2009'], 0,
                'Lần thứ hai Việt Nam đảm nhiệm trọng trách này (lần đầu 2008–2009).');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tổ chức quốc tế với năm Việt Nam gia nhập.',
                [['ASEAN', '1995'], ['APEC', '1998'],
                 ['WTO', '2007'], ['Liên hợp quốc', '1977']],
                'Lộ trình hội nhập từng bước vững chắc.');
            $this->matching($L, 'Nối mỗi sự kiện đối ngoại với năm diễn ra.',
                [['Bình thường hóa quan hệ với Hoa Kì', '1995'],
                 ['Kí hiệp định thương mại Việt – Mĩ (BTA)', '2001'],
                 ['CPTPP có hiệu lực với Việt Nam', '2019'],
                 ['Kí EVFTA', '2020']],
                'Đối ngoại rộng mở, đa phương hóa.');
            $this->matching($L, 'Nối mỗi tổ chức với vai trò của nó.',
                [['ASEAN', 'Hợp tác Đông Nam Á'], ['WTO', 'Thương mại thế giới'],
                 ['APEC', 'Hợp tác kinh tế châu Á – Thái Bình Dương'],
                 ['Liên hợp quốc', 'Hòa bình và an ninh thế giới']],
                'Việt Nam là thành viên tích cực, có trách nhiệm.');
            $this->matching($L, 'Nối mỗi hiệp định thương mại với đối tác.',
                [['EVFTA', 'Liên minh châu Âu'], ['CPTPP', '11 nước vành đai Thái Bình Dương'],
                 ['RCEP', '15 nước châu Á – Thái Bình Dương'], ['BTA', 'Hoa Kì (2001)']],
                'Hệ thống FTA giúp hàng Việt vươn ra thế giới.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm THẬP NIÊN 1990 hoặc THẬP NIÊN 2000.',
                [['Gia nhập ASEAN 1995', '1990'], ['Gia nhập APEC 1998', '1990'],
                 ['Bình thường hóa với Hoa Kì 1995', '1990'], ['Hoa Kì bỏ cấm vận 1994', '1990'],
                 ['Gia nhập WTO 2007', '2000'], ['Kí BTA với Hoa Kì 2001', '2000']],
                'Hai thập niên hội nhập mạnh mẽ.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Việt Nam gia nhập ASEAN năm 1995', 'Đúng'],
                 ['Việt Nam vào WTO năm 2007', 'Đúng'],
                 ['Việt Nam là thành viên sáng lập ASEAN', 'Sai'],
                 ['EVFTA được kí năm 2020', 'Đúng'],
                 ['Việt Nam làm Chủ tịch ASEAN năm 2020', 'Đúng'],
                 ['Việt Nam gia nhập Liên hợp quốc năm 1995', 'Sai']],
                'Việt Nam gia nhập Liên hợp quốc từ năm 1977.');
            $this->sortQ($L, 'Kéo mỗi tổ chức vào nhóm KINH TẾ hoặc CHÍNH TRỊ – AN NINH.',
                [['WTO', 'Kinh tế'], ['APEC', 'Kinh tế'], ['CPTPP', 'Kinh tế'],
                 ['Hội đồng Bảo an Liên hợp quốc', 'Chính trị – an ninh'],
                 ['ASEAN', 'Cả hai lĩnh vực'], ['Diễn đàn ARF', 'Chính trị – an ninh']],
                'ASEAN hợp tác toàn diện nhiều lĩnh vực.');
            $this->sortQ($L, 'Kéo mỗi thành tựu vào nhóm ĐỐI NGOẠI hoặc KINH TẾ.',
                [['Chủ tịch ASEAN 2020', 'Đối ngoại'],
                 ['Ủy viên không thường trực Hội đồng Bảo an', 'Đối ngoại'],
                 ['Tham gia gìn giữ hòa bình Liên hợp quốc', 'Đối ngoại'],
                 ['Xuất khẩu tăng trưởng', 'Kinh tế'],
                 ['Thu hút FDI', 'Kinh tế'],
                 ['Kí nhiều hiệp định thương mại tự do', 'Kinh tế']],
                'Đối ngoại phục vụ phát triển kinh tế đất nước.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Việt Nam chính thức gia nhập ASEAN ngày 28/7/___.', [[0, '1995']],
                'Trở thành thành viên thứ 7 của Hiệp hội.');
            $this->fill($L, 'Việt Nam trở thành thành viên thứ 150 của WTO năm ___.', [[0, '2007']],
                'Mốc son hội nhập kinh tế quốc tế.');
            $this->fill($L, 'Hiệp định thương mại tự do giữa Việt Nam và Liên minh châu Âu viết tắt là ___.', [[0, 'EVFTA']],
                'Có hiệu lực từ năm 2020.');
            $this->fill($L, 'Năm 2020, Việt Nam đảm nhiệm vai trò Chủ tịch ___.', [[0, 'ASEAN']],
                'Đồng thời là Ủy viên không thường trực Hội đồng Bảo an.');
        }
    }

    // ================= ĐỊA LÝ 10 =================

    private function seedDlThpt101(): void
    {
        $L = 'dia-ly-thpt-10-lop-10-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hành tinh gần Mặt Trời nhất trong Hệ Mặt Trời là:',
                ['Sao Kim', 'Sao Thủy', 'Trái Đất', 'Sao Hỏa'], 1,
                'Thứ tự từ Mặt Trời: Thủy, Kim, Trái Đất, Hỏa, Mộc, Thổ, Thiên Vương, Hải Vương.');
            $this->quiz($L, 'Trái Đất tự quay quanh trục một vòng hết:',
                ['12 giờ', '24 giờ', '365 ngày', '1 tháng'], 1,
                'Một ngày đêm là 24 giờ do Trái Đất tự quay.');
            $this->quiz($L, 'Trái Đất chuyển động quanh Mặt Trời một vòng hết:',
                ['24 giờ', '30 ngày', '365,25 ngày', '366 ngày'], 2,
                'Một năm có 365,25 ngày; 4 năm có một năm nhuận 366 ngày.');
            $this->quiz($L, 'Hiện tượng ngày và đêm luân phiên trên Trái Đất là do:',
                ['Trái Đất tự quay quanh trục', 'Trái Đất quay quanh Mặt Trời', 'Mặt Trăng quay quanh Trái Đất', 'Mặt Trời tự quay'], 0,
                'Nửa được chiếu sáng là ban ngày, nửa khuất là ban đêm.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hành tinh với thứ tự của nó tính từ Mặt Trời.',
                [['Sao Thủy', '1'], ['Sao Kim', '2'],
                 ['Trái Đất', '3'], ['Sao Hỏa', '4']],
                'Bốn hành tinh đất đá ở vòng trong của Hệ Mặt Trời.');
            $this->matching($L, 'Nối mỗi chuyển động của Trái Đất với hệ quả của nó.',
                [['Tự quay quanh trục', 'Ngày đêm luân phiên'],
                 ['Quay quanh Mặt Trời', 'Bốn mùa trong năm'],
                 ['Trục nghiêng và không đổi hướng', 'Ngày đêm dài ngắn theo mùa'],
                 ['Cả hai chuyển động', 'Giờ địa phương khác nhau']],
                'Hai chuyển động tạo nên nhịp điệu tự nhiên của Trái Đất.');
            $this->matching($L, 'Nối mỗi ngày với hiện tượng thiên văn tương ứng.',
                [['21/3', 'Xuân phân'], ['22/6', 'Hạ chí'],
                 ['23/9', 'Thu phân'], ['22/12', 'Đông chí']],
                'Bốn ngày đặc biệt đánh dấu sự chuyển mùa.');
            $this->matching($L, 'Nối mỗi hành tinh với đặc điểm nổi bật của nó.',
                [['Sao Thủy', 'Gần Mặt Trời nhất'], ['Sao Kim', 'Nóng nhất Hệ Mặt Trời'],
                 ['Trái Đất', 'Hành tinh có sự sống'], ['Sao Hỏa', 'Hành tinh đỏ']],
                'Mỗi hành tinh có đặc điểm riêng biệt.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành tinh vào nhóm HÀNH TINH ĐẤT ĐÁ hoặc KHÍ KHỔNG LỒ.',
                [['Sao Thủy', 'Đất đá'], ['Sao Kim', 'Đất đá'],
                 ['Trái Đất', 'Đất đá'], ['Sao Hỏa', 'Đất đá'],
                 ['Sao Mộc', 'Khí khổng lồ'], ['Sao Thổ', 'Khí khổng lồ']],
                'Bốn hành tinh vòng trong là đất đá, vòng ngoài là khí khổng lồ.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Trái Đất tự quay từ tây sang đông', 'Đúng'],
                 ['Ngày đêm do Trái Đất quay quanh Mặt Trời', 'Sai'],
                 ['Quỹ đạo Trái Đất quanh Mặt Trời gần tròn', 'Đúng'],
                 ['Sao Kim là hành tinh gần Mặt Trời nhất', 'Sai'],
                 ['Trục Trái Đất nghiêng so với mặt phẳng quỹ đạo', 'Đúng'],
                 ['Một năm có 365,25 ngày', 'Đúng']],
                'Sao Thủy mới là hành tinh gần Mặt Trời nhất.');
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm DO TỰ QUAY hoặc DO QUAY QUANH MẶT TRỜI.',
                [['Ngày đêm luân phiên', 'Tự quay'],
                 ['Giờ địa phương khác nhau', 'Tự quay'],
                 ['Sự lệch hướng chuyển động', 'Tự quay'],
                 ['Bốn mùa trong năm', 'Quay quanh Mặt Trời'],
                 ['Ngày đêm dài ngắn theo mùa', 'Quay quanh Mặt Trời'],
                 ['Điểm cận nhật – viễn nhật', 'Quay quanh Mặt Trời']],
                'Tự quay gây ra ngày đêm; quay quanh Mặt Trời gây ra mùa.');
            $this->sortQ($L, 'Kéo mỗi mốc thời gian vào nhóm MỘT NGÀY, MỘT NĂM hoặc NGÀY ĐẶC BIỆT.',
                [['24 giờ', 'Một ngày'], ['21/3 xuân phân', 'Ngày đặc biệt'],
                 ['365,25 ngày', 'Một năm'], ['22/6 hạ chí', 'Ngày đặc biệt'],
                 ['366 ngày năm nhuận', 'Một năm'], ['23/9 thu phân', 'Ngày đặc biệt']],
                'Năm nhuận 4 năm một lần để bù 0,25 ngày dư.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hành tinh thứ ba tính từ Mặt Trời là ___.', [[0, 'Trái Đất']],
                'Hành tinh duy nhất có sự sống đã được biết.');
            $this->fill($L, 'Trái Đất tự quay quanh trục hết ___ giờ.', [[0, '24']],
                'Tạo ra hiện tượng ngày đêm luân phiên.');
            $this->fill($L, 'Hiện tượng bốn mùa là hệ quả của chuyển động ___ của Trái Đất.', [[0, 'quay quanh Mặt Trời']],
                'Kết hợp với trục Trái Đất nghiêng không đổi hướng.');
            $this->fill($L, 'Hướng tự quay của Trái Đất là từ tây sang ___.', [[0, 'đông']],
                'Vì vậy Mặt Trời mọc ở phía đông.');
        }
    }

    private function seedDlThpt102(): void
    {
        $L = 'dia-ly-thpt-10-lop-10-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Lớp vỏ Trái Đất dày nhất ở khu vực nào?',
                ['Đại dương', 'Lục địa', 'Vùng cực', 'Xích đạo'], 1,
                'Vỏ lục địa dày 30–70 km, vỏ đại dương chỉ 5–10 km.');
            $this->quiz($L, 'Thạch quyển bao gồm:',
                ['Vỏ Trái Đất và phần trên của bao Manti', 'Toàn bộ bao Manti', 'Nhân Trái Đất', 'Vỏ và nhân Trái Đất'], 0,
                'Thạch quyển là lớp đá cứng ngoài cùng, dày khoảng 100 km.');
            $this->quiz($L, 'Nội lực là lực phát sinh từ:',
                ['Bên trong Trái Đất', 'Bức xạ Mặt Trời', 'Gió', 'Nước chảy'], 0,
                'Nội lực do năng lượng bên trong Trái Đất (phân rã phóng xạ...).');
            $this->quiz($L, 'Hiện tượng nào sau đây do nội lực gây ra?',
                ['Động đất, núi lửa', 'Xói mòn đất', 'Phong hóa đá', 'Bồi tụ phù sa'], 0,
                'Động đất, núi lửa, uốn nếp là kết quả của nội lực.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lớp của Trái Đất với đặc điểm của nó.',
                [['Vỏ Trái Đất', 'Mỏng nhất, rắn chắc'],
                 ['Bao Manti', 'Dày nhất'],
                 ['Nhân ngoài', 'Ở trạng thái lỏng'],
                 ['Nhân trong', 'Rắn, nhiệt độ rất cao']],
                'Từ ngoài vào trong: vỏ – Manti – nhân ngoài – nhân trong.');
            $this->matching($L, 'Nối mỗi vận động với kết quả địa hình của nó.',
                [['Nội lực', 'Nâng lên, hạ xuống, uốn nếp'],
                 ['Ngoại lực', 'Bào mòn, bồi tụ'],
                 ['Động đất', 'Đứt gãy, nứt vỡ'],
                 ['Núi lửa', 'Phun trào dung nham']],
                'Nội lực và ngoại lực là hai lực đối kháng tạo địa hình.');
            $this->matching($L, 'Nối mỗi mảng kiến tạo với đặc điểm của nó.',
                [['Mảng Âu – Á', 'Chứa lãnh thổ Việt Nam'],
                 ['Mảng Thái Bình Dương', 'Lớn nhất'],
                 ['Mảng Ấn – Úc', 'Va chạm tạo dãy Himalaya'],
                 ['Mảng Phi', 'Chứa châu Phi']],
                'Các mảng kiến tạo chuyển động rất chậm trên quyển mềm.');
            $this->matching($L, 'Nối mỗi dạng địa hình với quá trình tạo ra nó.',
                [['Núi uốn nếp', 'Nội lực'], ['Đồng bằng bồi tụ', 'Ngoại lực'],
                 ['Hẻm vực', 'Xói mòn'], ['Cồn cát', 'Bồi tụ do gió']],
                'Địa hình là kết quả tác động tổng hợp của nội và ngoại lực.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm NỘI LỰC hoặc NGOẠI LỰC.',
                [['Động đất', 'Nội lực'], ['Núi lửa', 'Nội lực'], ['Uốn nếp', 'Nội lực'],
                 ['Phong hóa', 'Ngoại lực'], ['Xói mòn', 'Ngoại lực'], ['Bồi tụ', 'Ngoại lực']],
                'Nội lực từ bên trong; ngoại lực từ bên ngoài (Mặt Trời).');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Vỏ lục địa dày hơn vỏ đại dương', 'Đúng'],
                 ['Thạch quyển gồm vỏ và phần trên bao Manti', 'Đúng'],
                 ['Nhân Trái Đất hoàn toàn ở thể lỏng', 'Sai'],
                 ['Ngoại lực có nguồn gốc từ bức xạ Mặt Trời', 'Đúng'],
                 ['Động đất do ngoại lực gây ra', 'Sai'],
                 ['Núi lửa phun trào do nội lực', 'Đúng']],
                'Nhân trong ở trạng thái rắn do áp suất cực lớn.');
            $this->sortQ($L, 'Kéo mỗi lớp/bộ phận vào nhóm THỂ RẮN hoặc THỂ LỎNG/DẺO.',
                [['Vỏ Trái Đất', 'Rắn'], ['Nhân trong', 'Rắn'], ['Thạch quyển', 'Rắn'],
                 ['Nhân ngoài', 'Lỏng'], ['Bao Manti dưới', 'Dẻo'], ['Quyển mềm', 'Dẻo']],
                'Nhiệt độ và áp suất quyết định trạng thái vật chất.');
            $this->sortQ($L, 'Kéo mỗi dạng địa hình Việt Nam vào nhóm DO NỘI LỰC hoặc NGOẠI LỰC là chính.',
                [['Dãy Hoàng Liên Sơn', 'Nội lực'], ['Dãy Trường Sơn', 'Nội lực'],
                 ['Đồng bằng sông Hồng', 'Ngoại lực'], ['Cồn cát miền Trung', 'Ngoại lực'],
                 ['Vịnh Hạ Long', 'Ngoại lực'], ['Cao nguyên đá Đồng Văn', 'Cả hai']],
                'Hầu hết địa hình do cả hai lực tác động, một lực là chính.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Lớp vỏ Trái Đất ở lục địa dày hơn ở ___.', [[0, 'đại dương']],
                'Vỏ lục địa 30–70 km, vỏ đại dương 5–10 km.');
            $this->fill($L, 'Thạch quyển gồm vỏ Trái Đất và phần trên của ___.', [[0, 'bao Manti']],
                'Là lớp đá cứng ngoài cùng của Trái Đất.');
            $this->fill($L, 'Động đất và núi lửa là kết quả tác động của ___.', [[0, 'nội lực']],
                'Nội lực phát sinh từ bên trong Trái Đất.');
            $this->fill($L, 'Phong hóa, xói mòn, bồi tụ là tác động của ___.', [[0, 'ngoại lực']],
                'Ngoại lực có nguồn gốc từ năng lượng bức xạ Mặt Trời.');
        }
    }

    private function seedDlThpt103(): void
    {
        $L = 'dia-ly-thpt-10-lop-10-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Các hiện tượng thời tiết (mưa, bão) xảy ra ở tầng khí quyển nào?',
                ['Tầng đối lưu', 'Tầng bình lưu', 'Tầng giữa', 'Tầng nhiệt'], 0,
                'Tầng đối lưu (0–16 km) chứa 80% khối lượng khí quyển.');
            $this->quiz($L, 'Nhiệt độ không khí thay đổi theo quy luật nào?',
                ['Giảm dần theo độ cao', 'Tăng dần theo độ cao', 'Không đổi theo độ cao', 'Tăng rồi giảm'], 0,
                'Cứ lên cao 100 m, nhiệt độ giảm khoảng 0,6°C.');
            $this->quiz($L, 'Gió là sự chuyển động của không khí từ:',
                ['Nơi khí áp cao đến nơi khí áp thấp', 'Nơi khí áp thấp đến nơi khí áp cao', 'Xích đạo đến hai cực', 'Phía đông sang phía tây'], 0,
                'Chênh lệch khí áp là nguyên nhân sinh ra gió.');
            $this->quiz($L, 'Loại gió thổi thường xuyên từ áp cao cận chí tuyến về áp thấp xích đạo là:',
                ['Gió Tín phong', 'Gió Tây ôn đới', 'Gió mùa', 'Gió đất – biển'], 0,
                'Tín phong (mậu dịch) thổi quanh năm ở đới nóng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tầng khí quyển với độ cao của nó.',
                [['Tầng đối lưu', '0–16 km'], ['Tầng bình lưu', '16–50 km'],
                 ['Tầng giữa', '50–80 km'], ['Tầng nhiệt', 'Trên 80 km']],
                'Bốn tầng khí quyển từ mặt đất lên cao.');
            $this->matching($L, 'Nối mỗi loại gió với phạm vi hoạt động của nó.',
                [['Gió Tín phong', 'Chí tuyến – xích đạo'], ['Gió Tây ôn đới', 'Vùng ôn đới'],
                 ['Gió mùa', 'Khu vực châu Á'], ['Gió phơn', 'Sườn khuất gió của núi']],
                'Mỗi loại gió có phạm vi và tính chất riêng.');
            $this->matching($L, 'Nối mỗi nhân tố với ảnh hưởng của nó đến nhiệt độ.',
                [['Vĩ độ thấp', 'Nhiệt độ cao'], ['Độ cao lớn', 'Nhiệt độ thấp'],
                 ['Dòng biển nóng', 'Tăng nhiệt độ'], ['Dòng biển lạnh', 'Giảm nhiệt độ']],
                'Nhiệt độ phụ thuộc nhiều nhân tố tự nhiên.');
            $this->matching($L, 'Nối mỗi đai khí áp với vị trí của nó.',
                [['Áp thấp xích đạo', 'Vĩ độ 0°'], ['Áp cao cận chí tuyến', 'Vĩ độ 30°'],
                 ['Áp thấp ôn đới', 'Vĩ độ 60°'], ['Áp cao cực', 'Vĩ độ 90°']],
                'Bảy đai khí áp phân bố xen kẽ trên Trái Đất.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi loại gió vào nhóm GIÓ THƯỜNG XUYÊN hoặc GIÓ THEO MÙA – ĐỊA PHƯƠNG.',
                [['Gió Tín phong', 'Thường xuyên'], ['Gió Tây ôn đới', 'Thường xuyên'],
                 ['Gió Đông cực', 'Thường xuyên'], ['Gió mùa', 'Theo mùa'],
                 ['Gió đất – biển', 'Địa phương'], ['Gió phơn', 'Địa phương']],
                'Gió thường xuyên thổi quanh năm; gió mùa đổi hướng theo mùa.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Tầng đối lưu chứa khoảng 80% khối lượng khí quyển', 'Đúng'],
                 ['Nhiệt độ giảm dần khi lên cao', 'Đúng'],
                 ['Gió thổi từ nơi áp thấp đến nơi áp cao', 'Sai'],
                 ['Gió Tín phong thổi từ chí tuyến về xích đạo', 'Đúng'],
                 ['Việt Nam chịu ảnh hưởng của gió mùa', 'Đúng'],
                 ['Tầng ôzôn nằm ở tầng bình lưu', 'Đúng']],
                'Gió luôn thổi từ áp cao về áp thấp.');
            $this->sortQ($L, 'Kéo mỗi nhân tố vào nhóm LÀM TĂNG hoặc LÀM GIẢM nhiệt độ không khí.',
                [['Vĩ độ thấp', 'Tăng'], ['Dòng biển nóng', 'Tăng'], ['Mặt đệm tối màu', 'Tăng'],
                 ['Độ cao lớn', 'Giảm'], ['Dòng biển lạnh', 'Giảm'], ['Băng tuyết phủ', 'Giảm']],
                'Mặt đệm tối hấp thụ nhiệt tốt hơn mặt sáng.');
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm Ở TẦNG ĐỐI LƯU hoặc TẦNG BÌNH LƯU.',
                [['Mưa', 'Đối lưu'], ['Bão', 'Đối lưu'], ['Mây', 'Đối lưu'],
                 ['Sấm sét', 'Đối lưu'], ['Tầng ôzôn', 'Bình lưu'], ['Máy bay dân dụng', 'Bình lưu']],
                'Máy bay bay ở tầng bình lưu để tránh thời tiết xấu.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Các hiện tượng thời tiết như mưa, bão xảy ra ở tầng ___.', [[0, 'đối lưu']],
                'Tầng thấp nhất của khí quyển.');
            $this->fill($L, 'Gió thổi từ nơi có khí áp ___ đến nơi có khí áp thấp.', [[0, 'cao']],
                'Chênh lệch khí áp càng lớn, gió càng mạnh.');
            $this->fill($L, 'Về mùa đông, miền Bắc Việt Nam chịu ảnh hưởng của gió mùa ___.', [[0, 'đông bắc']],
                'Gió mùa đông bắc gây rét đậm, rét hại.');
            $this->fill($L, 'Cứ lên cao 100 m, nhiệt độ không khí giảm khoảng ___°C.', [[0, '0,6']],
                'Quy luật giảm nhiệt theo độ cao.');
        }
    }

    private function seedDlThpt104(): void
    {
        $L = 'dia-ly-thpt-10-lop-10-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Sinh quyển là:',
                ['Nơi sinh sống của sinh vật trên Trái Đất', 'Lớp đất đá', 'Lớp nước', 'Lớp không khí'], 0,
                'Sinh quyển gồm toàn bộ sinh vật và môi trường sống của chúng.');
            $this->quiz($L, 'Thảm thực vật đặc trưng của đới nóng là:',
                ['Rừng nhiệt đới', 'Rừng lá kim', 'Thảo nguyên', 'Hoang mạc lạnh'], 0,
                'Rừng nhiệt đới xanh quanh năm, nhiều tầng tán.');
            $this->quiz($L, 'Hoang mạc Xa-ha-ra thuộc đới thiên nhiên nào?',
                ['Đới nóng', 'Đới ôn hòa', 'Đới lạnh', 'Đới núi cao'], 0,
                'Hoang mạc nhiệt đới lớn nhất thế giới ở Bắc Phi.');
            $this->quiz($L, 'Quy luật địa đới là sự thay đổi của thiên nhiên theo:',
                ['Vĩ độ', 'Kinh độ', 'Độ cao', 'Thời gian'], 0,
                'Do lượng bức xạ Mặt Trời thay đổi theo vĩ độ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đới thiên nhiên với thảm thực vật đặc trưng.',
                [['Đới nóng', 'Rừng nhiệt đới'], ['Đới ôn hòa', 'Rừng lá rộng, thảo nguyên'],
                 ['Đới lạnh', 'Đài nguyên'], ['Đới núi cao', 'Thay đổi theo độ cao']],
                'Thảm thực vật phản ánh điều kiện khí hậu.');
            $this->matching($L, 'Nối mỗi hoang mạc với châu lục của nó.',
                [['Xa-ha-ra', 'Châu Phi'], ['Gô-bi', 'Châu Á'],
                 ['A-ta-ca-ma', 'Nam Mĩ'], ['Vic-to-ria', 'Châu Đại Dương']],
                'Hoang mạc phân bố ở nhiều châu lục.');
            $this->matching($L, 'Nối mỗi quy luật với nội dung của nó.',
                [['Quy luật địa đới', 'Thiên nhiên thay đổi theo vĩ độ'],
                 ['Quy luật phi địa đới', 'Thay đổi do độ cao, địa hình'],
                 ['Quy luật đai cao', 'Thay đổi theo độ cao địa hình'],
                 ['Cả hai quy luật', 'Chi phối sự phân bố sinh vật']],
                'Địa đới và phi địa đới cùng tác động.');
            $this->matching($L, 'Nối mỗi loài sinh vật với đới sống của nó.',
                [['Voi, hổ', 'Đới nóng'], ['Gấu trắng', 'Đới lạnh'],
                 ['Lạc đà', 'Hoang mạc'], ['Tuần lộc', 'Đài nguyên']],
                'Sinh vật thích nghi với môi trường sống.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thảm thực vật vào nhóm ĐỚI NÓNG, ĐỚI ÔN HÒA hoặc ĐỚI LẠNH.',
                [['Rừng nhiệt đới', 'Nóng'], ['Xavan', 'Nóng'],
                 ['Rừng lá kim', 'Ôn hòa'], ['Thảo nguyên', 'Ôn hòa'],
                 ['Đài nguyên', 'Lạnh'], ['Hoang mạc đới nóng', 'Nóng']],
                'Từ xích đạo về cực: rừng nhiệt đới → thảo nguyên → rừng lá kim → đài nguyên.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Sinh quyển gồm toàn bộ sinh vật trên Trái Đất', 'Đúng'],
                 ['Quy luật địa đới do bức xạ Mặt Trời chi phối', 'Đúng'],
                 ['Rừng nhiệt đới chỉ phân bố ở châu Phi', 'Sai'],
                 ['Đài nguyên là thảm thực vật của đới lạnh', 'Đúng'],
                 ['Càng lên cao thiên nhiên càng giống đới lạnh', 'Đúng'],
                 ['Hoang mạc chỉ phân bố ở đới nóng', 'Sai']],
                'Rừng nhiệt đới có ở cả Nam Mĩ, châu Phi và Đông Nam Á.');
            $this->sortQ($L, 'Kéo mỗi nhân tố vào nhóm ẢNH HƯỞNG ĐỊA ĐỚI hoặc PHI ĐỊA ĐỚI.',
                [['Bức xạ Mặt Trời', 'Địa đới'], ['Hoàn lưu khí quyển', 'Địa đới'],
                 ['Vĩ độ địa lí', 'Địa đới'], ['Địa hình', 'Phi địa đới'],
                 ['Dòng biển', 'Phi địa đới'], ['Độ cao', 'Phi địa đới']],
                'Địa đới theo vĩ độ; phi địa đới theo độ cao, địa hình.');
            $this->sortQ($L, 'Kéo mỗi đới/kiểu thảm thực vật vào nhóm CÓ RỪNG hoặc KHÔNG CÓ RỪNG.',
                [['Rừng nhiệt đới', 'Có rừng'], ['Rừng lá kim', 'Có rừng'],
                 ['Rừng hỗn hợp', 'Có rừng'], ['Thảo nguyên', 'Không có rừng'],
                 ['Hoang mạc', 'Không có rừng'], ['Đài nguyên', 'Không có rừng']],
                'Rừng cần lượng mưa và nhiệt độ thích hợp.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Quy luật thay đổi thiên nhiên theo vĩ độ gọi là quy luật ___.', [[0, 'địa đới']],
                'Nguyên nhân do bức xạ Mặt Trời phân bố không đều theo vĩ độ.');
            $this->fill($L, 'Hoang mạc lớn nhất thế giới là ___.', [[0, 'Xa-ha-ra']],
                'Nằm ở Bắc Phi, diện tích khoảng 9 triệu km².');
            $this->fill($L, 'Thảm thực vật đặc trưng của đới lạnh là ___.', [[0, 'đài nguyên']],
                'Gồm rêu, địa y và cây bụi thấp.');
            $this->fill($L, 'Rừng nhiệt đới lớn nhất thế giới nằm ở lưu vực sông ___.', [[0, 'Amazon']],
                'Được mệnh danh là "lá phổi xanh" của Trái Đất.');
        }
    }

    // ================= ĐỊA LÝ 11 =================

    private function seedDlThpt111(): void
    {
        $L = 'dia-ly-thpt-11-lop-11-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dân số thế giới đạt mốc 8 tỉ người vào năm nào?',
                ['2000', '2011', '2022', '2030'], 2,
                'Tháng 11/2022, dân số thế giới chính thức cán mốc 8 tỉ.');
            $this->quiz($L, 'Châu lục đông dân nhất thế giới hiện nay là:',
                ['Châu Á', 'Châu Phi', 'Châu Âu', 'Châu Mĩ'], 0,
                'Châu Á chiếm khoảng 60% dân số thế giới.');
            $this->quiz($L, 'Tỉ suất gia tăng dân số tự nhiên được tính bằng:',
                ['Tỉ suất sinh trừ tỉ suất tử', 'Tỉ suất sinh cộng tỉ suất tử', 'Nhập cư trừ xuất cư', 'Tỉ suất sinh nhân tỉ suất tử'], 0,
                'Gia tăng tự nhiên phản ánh sự chênh lệch sinh – tử.');
            $this->quiz($L, 'Quốc gia đông dân nhất thế giới hiện nay là:',
                ['Trung Quốc', 'Ấn Độ', 'Hoa Kì', 'Indonesia'], 1,
                'Năm 2023, Ấn Độ đã vượt Trung Quốc thành nước đông dân nhất.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi mốc dân số thế giới với năm đạt được.',
                [['1 tỉ người', '1804'], ['2 tỉ người', '1927'],
                 ['6 tỉ người', '1999'], ['8 tỉ người', '2022']],
                'Tốc độ gia tăng dân số ngày càng nhanh.');
            $this->matching($L, 'Nối mỗi châu lục với đặc điểm dân số của nó.',
                [['Châu Á', 'Đông dân nhất'], ['Châu Phi', 'Tăng nhanh nhất'],
                 ['Châu Âu', 'Tăng chậm, già hóa'], ['Châu Đại Dương', 'Ít dân nhất']],
                'Phân bố dân cư rất không đều giữa các châu lục.');
            $this->matching($L, 'Nối mỗi khái niệm dân số với định nghĩa của nó.',
                [['Gia tăng tự nhiên', 'Sinh trừ tử'], ['Gia tăng cơ học', 'Nhập cư trừ xuất cư'],
                 ['Mật độ dân số', 'Số người trên 1 km²'], ['Bùng nổ dân số', 'Dân số tăng quá nhanh']],
                'Các khái niệm cơ bản của địa lí dân cư.');
            $this->matching($L, 'Nối mỗi quốc gia với quy mô dân số ước tính.',
                [['Ấn Độ', 'Trên 1,4 tỉ'], ['Trung Quốc', 'Trên 1,4 tỉ'],
                 ['Hoa Kì', 'Trên 330 triệu'], ['Indonesia', 'Trên 270 triệu']],
                'Bốn nước đông dân hàng đầu thế giới.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi quốc gia vào nhóm TRÊN hoặc DƯỚI 200 triệu dân.',
                [['Ấn Độ', 'Trên 200 triệu'], ['Trung Quốc', 'Trên 200 triệu'],
                 ['Hoa Kì', 'Trên 200 triệu'], ['Indonesia', 'Trên 200 triệu'],
                 ['Việt Nam', 'Dưới 200 triệu'], ['Nhật Bản', 'Dưới 200 triệu']],
                'Việt Nam có khoảng 100 triệu dân.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Dân số thế giới đạt 8 tỉ năm 2022', 'Đúng'],
                 ['Châu Á là châu lục đông dân nhất', 'Đúng'],
                 ['Ấn Độ đã vượt Trung Quốc về dân số', 'Đúng'],
                 ['Châu Âu có tỉ suất sinh rất cao', 'Sai'],
                 ['Bùng nổ dân số diễn ra ở nước phát triển', 'Sai'],
                 ['Gia tăng tự nhiên = sinh − tử', 'Đúng']],
                'Bùng nổ dân số chủ yếu ở các nước đang phát triển.');
            $this->sortQ($L, 'Kéo mỗi khu vực vào nhóm MẬT ĐỘ DÂN SỐ CAO hoặc THẤP.',
                [['Đông Á', 'Cao'], ['Nam Á', 'Cao'], ['Tây Âu', 'Cao'],
                 ['Bắc Canada', 'Thấp'], ['Xa-ha-ra', 'Thấp'], ['Ô-xtrây-li-a', 'Thấp']],
                'Mật độ cao ở đồng bằng châu thổ, thưa ở hoang mạc, vùng lạnh.');
            $this->sortQ($L, 'Kéo mỗi nhóm nước vào nhóm GIA TĂNG DÂN SỐ NHANH hoặc CHẬM.',
                [['Châu Phi hạ Sahara', 'Nhanh'], ['Nam Á', 'Nhanh'],
                 ['Đông Nam Á', 'Nhanh'], ['Tây Âu', 'Chậm'],
                 ['Nhật Bản', 'Chậm'], ['Bắc Âu', 'Chậm']],
                'Nước phát triển tăng chậm, thậm chí âm; nước đang phát triển tăng nhanh.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dân số thế giới cán mốc 8 tỉ người vào năm ___.', [[0, '2022']],
                'Mốc 7 tỉ đạt năm 2011, chỉ 11 năm sau đã 8 tỉ.');
            $this->fill($L, 'Châu lục đông dân nhất thế giới là châu ___.', [[0, 'Á']],
                'Chiếm khoảng 60% dân số toàn cầu.');
            $this->fill($L, 'Quốc gia đông dân nhất thế giới hiện nay là ___.', [[0, 'Ấn Độ']],
                'Vượt Trung Quốc từ năm 2023.');
            $this->fill($L, 'Hiệu số giữa tỉ suất sinh và tỉ suất tử gọi là gia tăng ___.', [[0, 'tự nhiên']],
                'Đơn vị thường tính bằng phần nghìn (%o).');
        }
    }

    private function seedDlThpt112(): void
    {
        $L = 'dia-ly-thpt-11-lop-11-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tháp dân số thể hiện điều gì?',
                ['Cơ cấu dân số theo độ tuổi và giới tính', 'Mật độ dân số', 'Tỉ lệ thất nghiệp', 'Thu nhập bình quân'], 0,
                'Hình dạng tháp cho biết dân số trẻ, già hay ổn định.');
            $this->quiz($L, 'Nhật Bản là quốc gia điển hình có cơ cấu dân số:',
                ['Trẻ', 'Già', 'Ổn định', 'Vàng'], 1,
                'Nhật Bản già hóa nhanh nhất thế giới, thiếu lao động trẻ.');
            $this->quiz($L, 'Luồng di cư lớn nhất trên thế giới hiện nay là:',
                ['Từ nông thôn ra thành thị', 'Từ thành thị về nông thôn', 'Từ nước giàu sang nước nghèo', 'Từ Bắc xuống Nam'], 0,
                'Đô thị hóa hút lao động nông thôn vào thành phố.');
            $this->quiz($L, 'Siêu đô thị là đô thị có dân số:',
                ['Trên 1 triệu', 'Trên 5 triệu', 'Trên 10 triệu', 'Trên 20 triệu'], 2,
                'Ví dụ: Tokyo, Delhi, Thượng Hải, São Paulo.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dạng tháp dân số với đặc điểm của nó.',
                [['Tháp mở rộng', 'Dân số trẻ, tăng nhanh'],
                 ['Tháp thu hẹp', 'Dân số già, tăng chậm'],
                 ['Tháp ổn định', 'Tăng trưởng thấp, cân bằng'],
                 ['Cả ba dạng', 'Phản ánh cơ cấu tuổi – giới']],
                'Hình dạng tháp nói lên tương lai dân số.');
            $this->matching($L, 'Nối mỗi siêu đô thị với quốc gia của nó.',
                [['Tokyo', 'Nhật Bản'], ['Delhi', 'Ấn Độ'],
                 ['Thượng Hải', 'Trung Quốc'], ['São Paulo', 'Bra-xin']],
                'Các siêu đô thị tập trung ở châu Á.');
            $this->matching($L, 'Nối mỗi khái niệm với định nghĩa của nó.',
                [['Đô thị hóa', 'Tăng tỉ lệ dân thành thị'],
                 ['Di cư', 'Chuyển nơi cư trú'],
                 ['Siêu đô thị', 'Trên 10 triệu dân'],
                 ['Tị nạn', 'Di cư do chiến tranh, thiên tai']],
                'Di cư và đô thị hóa làm thay đổi phân bố dân cư.');
            $this->matching($L, 'Nối mỗi nguyên nhân với luồng di cư tương ứng.',
                [['Tìm việc làm', 'Nông thôn → thành thị'],
                 ['Chiến tranh', 'Tị nạn quốc tế'],
                 ['Học tập', 'Di cư có chọn lọc'],
                 ['Đoàn tụ gia đình', 'Di cư quốc tế']],
                'Kinh tế là nguyên nhân di cư chủ yếu.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi quốc gia vào nhóm DÂN SỐ TRẺ hoặc DÂN SỐ GIÀ.',
                [['Niger', 'Trẻ'], ['Uganda', 'Trẻ'], ['Ấn Độ', 'Trẻ'],
                 ['Nhật Bản', 'Già'], ['Đức', 'Già'], ['Ý', 'Già']],
                'Châu Phi dân số trẻ; châu Âu và Nhật Bản già hóa.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Đô thị hóa diễn ra nhanh ở nước đang phát triển', 'Đúng'],
                 ['Di cư làm thay đổi phân bố dân cư', 'Đúng'],
                 ['Siêu đô thị có trên 10 triệu dân', 'Đúng'],
                 ['Nhật Bản có cơ cấu dân số trẻ', 'Sai'],
                 ['Tháp dân số thể hiện cơ cấu tuổi – giới', 'Đúng'],
                 ['Di cư chỉ diễn ra trong phạm vi một nước', 'Sai']],
                'Di cư có cả di cư trong nước và quốc tế.');
            $this->sortQ($L, 'Kéo mỗi đô thị vào nhóm SIÊU ĐÔ THỊ hoặc CHƯA ĐẠT.',
                [['Tokyo', 'Siêu đô thị'], ['Delhi', 'Siêu đô thị'],
                 ['Thượng Hải', 'Siêu đô thị'], ['São Paulo', 'Siêu đô thị'],
                 ['Hà Nội', 'Chưa đạt'], ['TP. Hồ Chí Minh', 'Chưa đạt']],
                'Hà Nội và TP.HCM có khoảng 8–9 triệu dân.');
            $this->sortQ($L, 'Kéo mỗi tác động của đô thị hóa vào nhóm TÍCH CỰC hoặc TIÊU CỰC.',
                [['Thúc đẩy kinh tế', 'Tích cực'], ['Tạo nhiều việc làm', 'Tích cực'],
                 ['Dịch vụ tốt hơn', 'Tích cực'], ['Ô nhiễm môi trường', 'Tiêu cực'],
                 ['Kẹt xe', 'Tiêu cực'], ['Thiếu nhà ở', 'Tiêu cực']],
                'Đô thị hóa tự phát gây nhiều hệ lụy.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tháp dân số phản ánh cơ cấu dân số theo ___ và giới tính.', [[0, 'độ tuổi']],
                'Trục tung là độ tuổi, trục hoành là tỉ lệ nam – nữ.');
            $this->fill($L, 'Đô thị có trên 10 triệu dân được gọi là ___.', [[0, 'siêu đô thị']],
                'Trên thế giới có hơn 30 siêu đô thị.');
            $this->fill($L, 'Quốc gia có cơ cấu dân số già điển hình ở châu Á là ___.', [[0, 'Nhật Bản']],
                'Tỉ lệ người cao tuổi cao nhất thế giới.');
            $this->fill($L, 'Luồng di cư chủ yếu hiện nay là từ nông thôn ra ___.', [[0, 'thành thị']],
                'Gắn liền với quá trình công nghiệp hóa, đô thị hóa.');
        }
    }

    private function seedDlThpt113(): void
    {
        $L = 'dia-ly-thpt-11-lop-11-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Vùng trồng lúa gạo lớn nhất thế giới là:',
                ['Đông Nam Á và Nam Á', 'Châu Âu', 'Bắc Mĩ', 'Châu Đại Dương'], 0,
                'Châu Á gió mùa chiếm khoảng 90% sản lượng lúa gạo thế giới.');
            $this->quiz($L, 'Quốc gia sản xuất lúa mì lớn nhất thế giới là:',
                ['Trung Quốc', 'Hoa Kì', 'Nga', 'Ấn Độ'], 0,
                'Trung Quốc dẫn đầu về sản lượng; Nga dẫn đầu về xuất khẩu.');
            $this->quiz($L, 'Cách mạng công nghiệp lần thứ tư gắn liền với:',
                ['Máy hơi nước', 'Điện năng', 'Máy tính', 'Trí tuệ nhân tạo và số hóa'], 3,
                'Công nghiệp 4.0: IoT, AI, dữ liệu lớn, robot.');
            $this->quiz($L, 'Ngành công nghiệp nào được coi là "quả đấm thép" của nền kinh tế?',
                ['Cơ khí – luyện kim', 'Dệt may', 'Thực phẩm', 'Điện tử'], 0,
                'Cung cấp tư liệu sản xuất cho các ngành khác.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cây trồng với vùng sản xuất chính của nó.',
                [['Lúa gạo', 'Châu Á gió mùa'], ['Lúa mì', 'Vùng ôn đới'],
                 ['Ngô', 'Hoa Kì, Trung Quốc'], ['Cà phê', 'Bra-xin, Việt Nam']],
                'Mỗi cây trồng thích hợp với điều kiện khí hậu nhất định.');
            $this->matching($L, 'Nối mỗi cuộc cách mạng công nghiệp với đặc trưng của nó.',
                [['Lần 1', 'Máy hơi nước'], ['Lần 2', 'Điện năng'],
                 ['Lần 3', 'Máy tính, tự động hóa'], ['Lần 4', 'Số hóa, trí tuệ nhân tạo']],
                'Bốn cuộc cách mạng thay đổi nền sản xuất nhân loại.');
            $this->matching($L, 'Nối mỗi tổ chức quốc tế với lĩnh vực hoạt động của nó.',
                [['FAO', 'Lương thực – nông nghiệp'], ['OPEC', 'Dầu mỏ'],
                 ['WTO', 'Thương mại'], ['IEA', 'Năng lượng']],
                'Các tổ chức điều phối kinh tế toàn cầu.');
            $this->matching($L, 'Nối mỗi quốc gia với thế mạnh công nghiệp của nó.',
                [['Nhật Bản', 'Ô tô, điện tử'], ['Đức', 'Cơ khí chế tạo'],
                 ['Hoa Kì', 'Công nghệ cao'], ['Trung Quốc', 'Công xưởng thế giới']],
                'Mỗi cường quốc có thế mạnh riêng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi ngành vào nhóm CÔNG NGHIỆP NẶNG hoặc CÔNG NGHIỆP NHẸ.',
                [['Luyện kim', 'Nặng'], ['Cơ khí', 'Nặng'], ['Hóa chất', 'Nặng'],
                 ['Dệt may', 'Nhẹ'], ['Thực phẩm', 'Nhẹ'], ['Da giày', 'Nhẹ']],
                'Công nghiệp nặng sản xuất tư liệu sản xuất; nhẹ sản xuất hàng tiêu dùng.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Châu Á là vựa lúa của thế giới', 'Đúng'],
                 ['Cách mạng 4.0 gắn với số hóa và AI', 'Đúng'],
                 ['OPEC là tổ chức các nước xuất khẩu dầu mỏ', 'Đúng'],
                 ['Lúa mì trồng nhiều ở vùng nhiệt đới', 'Sai'],
                 ['FAO là tổ chức thuộc Liên hợp quốc', 'Đúng'],
                 ['Dệt may thuộc công nghiệp nặng', 'Sai']],
                'Lúa mì là cây ôn đới; dệt may là công nghiệp nhẹ.');
            $this->sortQ($L, 'Kéo mỗi cây trồng vào nhóm CÂY LƯƠNG THỰC hoặc CÂY CÔNG NGHIỆP.',
                [['Lúa gạo', 'Lương thực'], ['Lúa mì', 'Lương thực'], ['Ngô', 'Lương thực'],
                 ['Cà phê', 'Công nghiệp'], ['Cao su', 'Công nghiệp'], ['Mía', 'Công nghiệp']],
                'Cây lương thực nuôi sống con người; cây công nghiệp làm nguyên liệu.');
            $this->sortQ($L, 'Kéo mỗi nguồn năng lượng vào nhóm TÁI TẠO hoặc KHÔNG TÁI TẠO.',
                [['Mặt Trời', 'Tái tạo'], ['Gió', 'Tái tạo'], ['Thủy điện', 'Tái tạo'],
                 ['Than đá', 'Không tái tạo'], ['Dầu mỏ', 'Không tái tạo'], ['Khí đốt', 'Không tái tạo']],
                'Năng lượng tái tạo sạch và bền vững hơn.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tổ chức Lương thực và Nông nghiệp Liên hợp quốc viết tắt là ___.', [[0, 'FAO']],
                'Trụ sở tại Rô-ma, I-ta-li-a.');
            $this->fill($L, 'Tổ chức các nước xuất khẩu dầu mỏ viết tắt là ___.', [[0, 'OPEC']],
                'Chi phối giá dầu mỏ thế giới.');
            $this->fill($L, 'Cách mạng công nghiệp lần thứ tư còn được gọi là cách mạng ___.', [[0, 'số']],
                'Đặc trưng: kết nối vạn vật, dữ liệu lớn, AI.');
            $this->fill($L, 'Cây lương thực quan trọng nhất của khu vực châu Á gió mùa là cây ___.', [[0, 'lúa']],
                'Gạo là lương thực chính của hơn một nửa nhân loại.');
        }
    }

    private function seedDlThpt114(): void
    {
        $L = 'dia-ly-thpt-11-lop-11-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ngành dịch vụ chiếm tỉ trọng lớn nhất trong cơ cấu GDP ở:',
                ['Các nước phát triển', 'Các nước đang phát triển', 'Các nước kém phát triển', 'Mọi nước như nhau'], 0,
                'Ở nước phát triển, dịch vụ chiếm trên 70% GDP.');
            $this->quiz($L, 'Toàn cầu hóa biểu hiện rõ nét nhất trong lĩnh vực:',
                ['Kinh tế', 'Văn hóa', 'Chính trị', 'Quân sự'], 0,
                'Tự do hóa thương mại, đầu tư là biểu hiện rõ nhất.');
            $this->quiz($L, 'Tổ chức Thương mại Thế giới viết tắt là:',
                ['WTO', 'WHO', 'FAO', 'UNESCO'], 0,
                'WTO thành lập năm 1995, điều tiết thương mại toàn cầu.');
            $this->quiz($L, 'Công ty xuyên quốc gia có đặc điểm:',
                ['Hoạt động ở nhiều quốc gia', 'Chỉ hoạt động ở một nước', 'Không vì mục tiêu lợi nhuận', 'Do nhà nước quản lí'], 0,
                'Là chủ thể thúc đẩy toàn cầu hóa kinh tế.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi ngành dịch vụ với ví dụ của nó.',
                [['Giao thông vận tải', 'Hàng không'], ['Tài chính', 'Ngân hàng'],
                 ['Du lịch', 'Khách sạn'], ['Viễn thông', 'Internet']],
                'Dịch vụ rất đa dạng trong đời sống hiện đại.');
            $this->matching($L, 'Nối mỗi tổ chức quốc tế với năm thành lập.',
                [['WTO', '1995'], ['Ngân hàng Thế giới (WB)', '1944'],
                 ['Quỹ Tiền tệ Quốc tế (IMF)', '1944'], ['Liên hợp quốc', '1945']],
                'Hệ thống tổ chức điều hành kinh tế – chính trị thế giới.');
            $this->matching($L, 'Nối mỗi biểu hiện với lĩnh vực toàn cầu hóa tương ứng.',
                [['Tự do thương mại', 'Kinh tế'], ['Internet toàn cầu', 'Công nghệ'],
                 ['Giao lưu văn hóa', 'Văn hóa'], ['Công ty đa quốc gia', 'Kinh tế']],
                'Toàn cầu hóa diễn ra trên nhiều lĩnh vực.');
            $this->matching($L, 'Nối mỗi trung tâm tài chính với quốc gia của nó.',
                [['New York', 'Hoa Kì'], ['London', 'Anh'],
                 ['Tokyo', 'Nhật Bản'], ['Singapore', 'Singapore']],
                'Các trung tâm tài chính hàng đầu thế giới.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm DỊCH VỤ hoặc SẢN XUẤT VẬT CHẤT.',
                [['Ngân hàng', 'Dịch vụ'], ['Du lịch', 'Dịch vụ'],
                 ['Giáo dục', 'Dịch vụ'], ['Vận tải', 'Dịch vụ'],
                 ['Luyện thép', 'Sản xuất'], ['Trồng lúa', 'Sản xuất']],
                'Dịch vụ không tạo ra sản phẩm vật chất trực tiếp.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Dịch vụ chiếm trên 70% GDP ở nước phát triển', 'Đúng'],
                 ['Toàn cầu hóa chỉ có mặt tích cực', 'Sai'],
                 ['WTO được thành lập năm 1995', 'Đúng'],
                 ['Công ty xuyên quốc gia thúc đẩy toàn cầu hóa', 'Đúng'],
                 ['Du lịch thuộc ngành dịch vụ', 'Đúng'],
                 ['IMF là tổ chức y tế thế giới', 'Sai']],
                'IMF là Quỹ Tiền tệ Quốc tế; WHO mới là y tế.');
            $this->sortQ($L, 'Kéo mỗi tác động của toàn cầu hóa vào nhóm TÍCH CỰC hoặc THÁCH THỨC.',
                [['Mở rộng thị trường', 'Tích cực'], ['Chuyển giao công nghệ', 'Tích cực'],
                 ['Tăng trưởng kinh tế', 'Tích cực'], ['Cạnh tranh gay gắt', 'Thách thức'],
                 ['Phụ thuộc kinh tế lẫn nhau', 'Thách thức'], ['Nguy cơ mai một văn hóa', 'Thách thức']],
                'Toàn cầu hóa có cả cơ hội và thách thức.');
            $this->sortQ($L, 'Kéo mỗi tổ chức vào nhóm KINH TẾ hoặc PHI KINH TẾ.',
                [['WTO', 'Kinh tế'], ['WB', 'Kinh tế'], ['IMF', 'Kinh tế'],
                 ['FAO', 'Kinh tế'], ['WHO', 'Phi kinh tế'], ['UNESCO', 'Phi kinh tế']],
                'FAO phụ trách lương thực – nông nghiệp nên thuộc nhóm kinh tế.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tổ chức Thương mại Thế giới viết tắt là ___.', [[0, 'WTO']],
                'Việt Nam gia nhập WTO năm 2007.');
            $this->fill($L, 'Quá trình tăng cường liên kết kinh tế trên phạm vi toàn cầu gọi là ___.', [[0, 'toàn cầu hóa']],
                'Xu thế tất yếu của kinh tế thế giới hiện nay.');
            $this->fill($L, 'Ngành ___ chiếm tỉ trọng ngày càng lớn trong GDP các nước phát triển.', [[0, 'dịch vụ']],
                'Phản ánh trình độ phát triển kinh tế cao.');
            $this->fill($L, 'Quỹ Tiền tệ Quốc tế viết tắt là ___.', [[0, 'IMF']],
                'Hỗ trợ ổn định tài chính – tiền tệ toàn cầu.');
        }
    }

    // ================= ĐỊA LÝ 12 =================

    private function seedDlThpt121(): void
    {
        $L = 'dia-ly-thpt-12-lop-12-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Diện tích tự nhiên của Việt Nam khoảng:',
                ['331.212 km²', '330.000 km²', '329.560 km²', '345.000 km²'], 0,
                'Việt Nam rộng khoảng 331.212 km², đứng thứ 65 thế giới.');
            $this->quiz($L, 'Đường bờ biển Việt Nam dài khoảng:',
                ['3.260 km', '2.360 km', '4.639 km', '1.650 km'], 0,
                'Bờ biển dài từ Móng Cái đến Hà Tiên.');
            $this->quiz($L, 'Đỉnh núi cao nhất Việt Nam là:',
                ['Phan-xi-păng', 'Pu Si Lung', 'Ngọc Linh', 'Bạch Mã'], 0,
                'Phan-xi-păng cao 3.143 m, được mệnh danh "nóc nhà Đông Dương".');
            $this->quiz($L, 'Địa hình Việt Nam có đặc điểm nổi bật là:',
                ['3/4 diện tích là đồi núi', 'Chủ yếu là đồng bằng', 'Toàn núi cao', 'Chủ yếu là cao nguyên'], 0,
                'Đồi núi chiếm 3/4, đồng bằng chỉ 1/4 diện tích.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi số liệu với nội dung tương ứng của Việt Nam.',
                [['331.212 km²', 'Diện tích tự nhiên'], ['3.260 km', 'Chiều dài bờ biển'],
                 ['4.639 km', 'Biên giới đất liền'], ['3.143 m', 'Độ cao Phan-xi-păng']],
                'Bốn con số đặc trưng của lãnh thổ Việt Nam.');
            $this->matching($L, 'Nối mỗi dãy núi với khu vực phân bố của nó.',
                [['Hoàng Liên Sơn', 'Tây Bắc'], ['Trường Sơn Bắc', 'Bắc Trung Bộ'],
                 ['Trường Sơn Nam', 'Tây Nguyên'], ['Cánh cung Đông Triều', 'Đông Bắc']],
                'Địa hình núi chia thành bốn vùng rõ rệt.');
            $this->matching($L, 'Nối mỗi đồng bằng với con sông bồi đắp nó.',
                [['Đồng bằng sông Hồng', 'Sông Hồng'], ['Đồng bằng sông Cửu Long', 'Sông Mê Công'],
                 ['Đồng bằng duyên hải', 'Các sông nhỏ, ngắn, dốc'],
                 ['Cả hai đồng bằng lớn', 'Vựa lúa của cả nước']],
                'Phù sa sông ngòi tạo nên đồng bằng châu thổ.');
            $this->matching($L, 'Nối mỗi hướng địa hình với vùng thể hiện rõ nhất.',
                [['Tây Bắc – Đông Nam', 'Hướng chính của địa hình'],
                 ['Vòng cung', 'Vùng Đông Bắc'], ['Tây – Đông', 'Trường Sơn Nam'],
                 ['Cả ba hướng', 'Tạo sự đa dạng địa hình']],
                'Hướng núi ảnh hưởng đến hướng sông và khí hậu.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi dạng địa hình vào nhóm ĐỒI NÚI hoặc ĐỒNG BẰNG.',
                [['Hoàng Liên Sơn', 'Đồi núi'], ['Tây Nguyên', 'Đồi núi'],
                 ['Dãy Trường Sơn', 'Đồi núi'], ['Đồng bằng sông Hồng', 'Đồng bằng'],
                 ['Đồng bằng sông Cửu Long', 'Đồng bằng'], ['Dải duyên hải miền Trung', 'Đồng bằng']],
                'Đồi núi 3/4, đồng bằng 1/4 diện tích cả nước.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Việt Nam nằm ở khu vực Đông Nam Á', 'Đúng'],
                 ['Đường bờ biển Việt Nam dài 3.260 km', 'Đúng'],
                 ['Phan-xi-păng cao 3.143 m', 'Đúng'],
                 ['Địa hình Việt Nam chủ yếu là đồng bằng', 'Sai'],
                 ['Việt Nam có biên giới với 3 nước', 'Đúng'],
                 ['Hoàng Liên Sơn nằm ở Đông Bắc', 'Sai']],
                'Hoàng Liên Sơn ở Tây Bắc; ba nước láng giềng: Trung Quốc, Lào, Campuchia.');
            $this->sortQ($L, 'Kéo mỗi đỉnh núi vào nhóm CAO TRÊN hoặc DƯỚI 3.000 m.',
                [['Phan-xi-păng 3.143 m', 'Trên 3.000 m'], ['Pu Si Lung 3.083 m', 'Trên 3.000 m'],
                 ['Pu Ta Leng 3.049 m', 'Trên 3.000 m'], ['Ngọc Linh 2.598 m', 'Dưới 3.000 m'],
                 ['Bạch Mã 1.444 m', 'Dưới 3.000 m'], ['Lang Biang 2.167 m', 'Dưới 3.000 m']],
                'Các đỉnh trên 3.000 m đều ở Tây Bắc.');
            $this->sortQ($L, 'Kéo mỗi tỉnh/thành phố vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.',
                [['Lào Cai', 'Bắc'], ['Hà Nội', 'Bắc'],
                 ['Huế', 'Trung'], ['Đà Nẵng', 'Trung'],
                 ['TP. Hồ Chí Minh', 'Nam'], ['Cần Thơ', 'Nam']],
                'Ba miền với đặc điểm tự nhiên khác nhau.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đỉnh núi cao nhất Việt Nam, "nóc nhà Đông Dương", là ___.', [[0, 'Phan-xi-păng']],
                'Cao 3.143 m, thuộc dãy Hoàng Liên Sơn, tỉnh Lào Cai.');
            $this->fill($L, 'Đường bờ biển nước ta dài khoảng ___ km.', [[0, '3260']],
                'Từ Móng Cái (Quảng Ninh) đến Hà Tiên (Kiên Giang).');
            $this->fill($L, 'Việt Nam có chung đường biên giới trên đất liền với 3 nước: Trung Quốc, Lào và ___.', [[0, 'Campuchia']],
                'Đường biên giới đất liền dài 4.639 km.');
            $this->fill($L, 'Đồng bằng châu thổ lớn nhất Việt Nam là đồng bằng sông ___.', [[0, 'Cửu Long']],
                'Rộng khoảng 40.000 km², vựa lúa lớn nhất cả nước.');
        }
    }

    private function seedDlThpt122(): void
    {
        $L = 'dia-ly-thpt-12-lop-12-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khí hậu Việt Nam mang tính chất:',
                ['Nhiệt đới gió mùa', 'Ôn đới hải dương', 'Hàn đới', 'Địa Trung Hải'], 0,
                'Nóng ẩm quanh năm, mưa theo mùa, chịu ảnh hưởng gió mùa châu Á.');
            $this->quiz($L, 'Con sông dài nhất chảy qua lãnh thổ Việt Nam là:',
                ['Sông Hồng', 'Sông Mê Công', 'Sông Đồng Nai', 'Sông Cả'], 1,
                'Sông Mê Công dài 4.350 km, đoạn qua Việt Nam gọi là sông Tiền, sông Hậu.');
            $this->quiz($L, 'Loại đất chiếm diện tích lớn nhất ở Việt Nam là:',
                ['Đất feralit', 'Đất phù sa', 'Đất mùn núi cao', 'Đất xám bạc màu'], 0,
                'Đất feralit chiếm khoảng 65% diện tích, phân bố ở đồi núi.');
            $this->quiz($L, 'Mùa mưa ở miền Bắc Việt Nam kéo dài từ:',
                ['Tháng 5 đến tháng 10', 'Tháng 11 đến tháng 4', 'Quanh năm', 'Tháng 1 đến tháng 6'], 0,
                'Mùa mưa trùng với mùa gió tây nam nóng ẩm.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi con sông với đặc điểm của nó.',
                [['Sông Mê Công', 'Dài nhất Đông Nam Á'],
                 ['Sông Hồng', 'Sông lớn nhất Bắc Bộ'],
                 ['Sông Đồng Nai', 'Sông lớn nhất Đông Nam Bộ'],
                 ['Sông Cả', 'Sông lớn Bắc Trung Bộ']],
                'Sông ngòi Việt Nam dày đặc, nhiều nước.');
            $this->matching($L, 'Nối mỗi loại đất với nơi phân bố chủ yếu của nó.',
                [['Đất phù sa', 'Các đồng bằng'], ['Đất feralit', 'Vùng đồi núi'],
                 ['Đất mùn núi cao', 'Núi cao trên 2.000 m'], ['Đất cát biển', 'Ven biển miền Trung']],
                'Mỗi loại đất thích hợp với cây trồng khác nhau.');
            $this->matching($L, 'Nối mỗi mùa với loại gió thịnh hành ở miền Bắc.',
                [['Mùa đông', 'Gió mùa đông bắc'], ['Mùa hè', 'Gió mùa tây nam'],
                 ['Mùa thu', 'Thời kì chuyển tiếp'], ['Mùa xuân', 'Thời kì chuyển tiếp']],
                'Gió mùa tạo nên hai mùa khí hậu rõ rệt.');
            $this->matching($L, 'Nối mỗi đặc điểm với miền khí hậu tương ứng.',
                [['Mùa đông lạnh, mưa phùn', 'Miền Bắc'],
                 ['Nóng quanh năm', 'Miền Nam'],
                 ['Mưa lệch pha (thu – đông)', 'Duyên hải miền Trung'],
                 ['Mùa khô sâu sắc', 'Tây Nguyên, Nam Bộ']],
                'Khí hậu phân hóa Bắc – Nam rõ rệt.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi con sông vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.',
                [['Sông Hồng', 'Bắc'], ['Sông Đà', 'Bắc'],
                 ['Sông Cả', 'Trung'], ['Sông Thu Bồn', 'Trung'],
                 ['Sông Đồng Nai', 'Nam'], ['Sông Tiền', 'Nam']],
                'Mạng lưới sông ngòi khắp ba miền.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Việt Nam có khí hậu nhiệt đới gió mùa', 'Đúng'],
                 ['Sông ngòi nước ta dày đặc, nhiều nước', 'Đúng'],
                 ['Đất feralit chiếm diện tích lớn nhất', 'Đúng'],
                 ['Miền Nam có mùa đông lạnh giá', 'Sai'],
                 ['Sông Mê Công bắt nguồn từ Tây Tạng', 'Đúng'],
                 ['Mùa mưa miền Trung trùng với miền Bắc', 'Sai']],
                'Miền Trung mưa vào thu – đông, lệch pha so với cả nước.');
            $this->sortQ($L, 'Kéo mỗi loại đất vào nhóm ĐẤT ĐỒNG BẰNG hoặc ĐẤT ĐỒI NÚI.',
                [['Phù sa sông Hồng', 'Đồng bằng'], ['Phù sa sông Cửu Long', 'Đồng bằng'],
                 ['Đất cát ven biển', 'Đồng bằng'], ['Đất feralit đỏ vàng', 'Đồi núi'],
                 ['Đất feralit trên đá vôi', 'Đồi núi'], ['Đất xám', 'Đồi núi']],
                'Phù sa màu mỡ ở đồng bằng; feralit ở đồi núi.');
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm MÙA MƯA hoặc MÙA KHÔ (Tây Nguyên – Nam Bộ).',
                [['Mưa lớn tháng 8', 'Mùa mưa'], ['Lũ tháng 9', 'Mùa mưa'],
                 ['Mưa dông tháng 5', 'Mùa mưa'], ['Gió tây khô nóng', 'Mùa khô'],
                 ['Thiếu nước tháng 3', 'Mùa khô'], ['Nắng gắt tháng 2', 'Mùa khô']],
                'Nam Bộ: mùa mưa tháng 5–10, mùa khô tháng 11–4.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Khí hậu Việt Nam là khí hậu nhiệt đới ___.', [[0, 'gió mùa']],
                'Nóng ẩm, mưa nhiều, phân hóa theo mùa và theo miền.');
            $this->fill($L, 'Con sông dài nhất Đông Nam Á chảy qua Việt Nam là sông ___.', [[0, 'Mê Công']],
                'Đoạn cuối ở Việt Nam chia thành sông Tiền và sông Hậu.');
            $this->fill($L, 'Loại đất chiếm diện tích lớn nhất ở vùng đồi núi nước ta là đất ___.', [[0, 'feralit']],
                'Hình thành do quá trình phong hóa đá trong khí hậu nóng ẩm.');
            $this->fill($L, 'Mùa đông ở miền Bắc có gió mùa ___ gây rét đậm, rét hại.', [[0, 'đông bắc']],
                'Gió từ cao áp Xi-bia thổi về.');
        }
    }

    private function seedDlThpt123(): void
    {
        $L = 'dia-ly-thpt-12-lop-12-3';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Vựa lúa lớn nhất của Việt Nam là:',
                ['Đồng bằng sông Cửu Long', 'Đồng bằng sông Hồng', 'Duyên hải miền Trung', 'Tây Nguyên'], 0,
                'ĐBSCL chiếm hơn 50% sản lượng lúa cả nước.');
            $this->quiz($L, 'Việt Nam là nước xuất khẩu lớn thứ hai thế giới về mặt hàng nào?',
                ['Cà phê', 'Gạo', 'Cao su', 'Hồ tiêu'], 0,
                'Chỉ sau Bra-xin; Tây Nguyên là thủ phủ cà phê.');
            $this->quiz($L, 'Cây công nghiệp lâu năm quan trọng nhất ở Tây Nguyên là:',
                ['Cà phê', 'Cao su', 'Hồ tiêu', 'Điều'], 0,
                'Đất bazan Tây Nguyên rất thích hợp trồng cà phê.');
            $this->quiz($L, 'Vùng nuôi trồng thủy sản lớn nhất cả nước là:',
                ['Đồng bằng sông Cửu Long', 'Đồng bằng sông Hồng', 'Duyên hải miền Trung', 'Đông Nam Bộ'], 0,
                'Cá tra, tôm sú ĐBSCL xuất khẩu khắp thế giới.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cây trồng với vùng sản xuất chính của nó.',
                [['Lúa', 'Đồng bằng sông Cửu Long'], ['Cà phê', 'Tây Nguyên'],
                 ['Cao su', 'Đông Nam Bộ'], ['Chè', 'Trung du và miền núi Bắc Bộ']],
                'Mỗi vùng có thế mạnh cây trồng riêng.');
            $this->matching($L, 'Nối mỗi nông sản với thứ hạng xuất khẩu của Việt Nam.',
                [['Gạo', 'Top đầu thế giới'], ['Cà phê', 'Thứ 2 thế giới'],
                 ['Hồ tiêu', 'Top đầu thế giới'], ['Điều', 'Top đầu thế giới']],
                'Nông sản Việt Nam vươn ra toàn cầu.');
            $this->matching($L, 'Nối mỗi vật nuôi với vùng chăn nuôi – thủy sản của nó.',
                [['Lợn', 'Các đồng bằng'], ['Bò sữa', 'Mộc Châu, Đà Lạt'],
                 ['Tôm sú', 'Đồng bằng sông Cửu Long'], ['Cá tra', 'Đồng bằng sông Cửu Long']],
                'Chăn nuôi và thủy sản phát triển mạnh.');
            $this->matching($L, 'Nối mỗi mô hình với nội dung của nó.',
                [['Cánh đồng mẫu lớn', 'Sản xuất tập trung, quy mô lớn'],
                 ['VietGAP', 'Sản xuất nông nghiệp an toàn'],
                 ['Nông nghiệp công nghệ cao', 'Đà Lạt và vùng ven đô'],
                 ['OCOP', 'Mỗi xã một sản phẩm']],
                'Các mô hình nâng cao giá trị nông sản.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cây trồng vào nhóm CÂY LƯƠNG THỰC hoặc CÂY CÔNG NGHIỆP.',
                [['Lúa', 'Lương thực'], ['Ngô', 'Lương thực'], ['Khoai', 'Lương thực'],
                 ['Cà phê', 'Công nghiệp'], ['Cao su', 'Công nghiệp'], ['Hồ tiêu', 'Công nghiệp']],
                'Lương thực đảm bảo an ninh lương thực; cây công nghiệp xuất khẩu.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['ĐBSCL là vựa lúa lớn nhất cả nước', 'Đúng'],
                 ['Việt Nam xuất khẩu cà phê đứng thứ 2 thế giới', 'Đúng'],
                 ['Tây Nguyên trồng nhiều lúa nước', 'Sai'],
                 ['Nuôi trồng thủy sản phát triển mạnh ở ĐBSCL', 'Đúng'],
                 ['Chè trồng nhiều ở Tây Nguyên', 'Sai'],
                 ['Cao su tập trung ở Đông Nam Bộ', 'Đúng']],
                'Chè trồng nhiều ở trung du Bắc Bộ (Thái Nguyên...).');
            $this->sortQ($L, 'Kéo mỗi sản phẩm vào nhóm LƯƠNG THỰC, CÔNG NGHIỆP hoặc THỦY SẢN.',
                [['Gạo', 'Lương thực'], ['Ngô', 'Lương thực'],
                 ['Cà phê', 'Công nghiệp'], ['Cao su', 'Công nghiệp'],
                 ['Tôm', 'Thủy sản'], ['Cá tra', 'Thủy sản']],
                'Ba nhóm sản phẩm nông nghiệp chủ lực.');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm THUẬN LỢI hoặc KHÓ KHĂN cho nông nghiệp.',
                [['Đất phù sa màu mỡ', 'Thuận lợi'], ['Khí hậu nhiệt đới', 'Thuận lợi'],
                 ['Lao động dồi dào', 'Thuận lợi'], ['Thiên tai, bão lũ', 'Khó khăn'],
                 ['Sâu bệnh', 'Khó khăn'], ['Thị trường bấp bênh', 'Khó khăn']],
                'Phát huy thuận lợi, khắc phục khó khăn để phát triển.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Vựa lúa lớn nhất của Việt Nam là đồng bằng sông ___.', [[0, 'Cửu Long']],
                'Chiếm hơn một nửa sản lượng lúa cả nước.');
            $this->fill($L, 'Việt Nam đứng thứ hai thế giới về xuất khẩu ___.', [[0, 'cà phê']],
                'Sau Bra-xin, chủ yếu là cà phê vối (Robusta).');
            $this->fill($L, 'Vùng trồng cà phê lớn nhất Việt Nam là ___.', [[0, 'Tây Nguyên']],
                'Đất bazan đỏ rất phù hợp với cây cà phê.');
            $this->fill($L, 'Vùng nuôi cá tra, tôm sú xuất khẩu lớn nhất là đồng bằng sông ___.', [[0, 'Cửu Long']],
                'Thủy sản là mặt hàng xuất khẩu chủ lực.');
        }
    }

    private function seedDlThpt124(): void
    {
        $L = 'dia-ly-thpt-12-lop-12-4';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ngành công nghiệp trọng điểm mang lại nguồn thu lớn cho Việt Nam là:',
                ['Khai thác dầu khí', 'Dệt may', 'Da giày', 'Chế biến gỗ'], 0,
                'Dầu khí đóng góp lớn vào ngân sách và xuất khẩu.');
            $this->quiz($L, 'Trung tâm công nghiệp lớn nhất của Việt Nam là:',
                ['TP. Hồ Chí Minh', 'Hà Nội', 'Hải Phòng', 'Đà Nẵng'], 0,
                'TP.HCM và vùng Đông Nam Bộ là cực tăng trưởng công nghiệp.');
            $this->quiz($L, 'Loại hình du lịch là thế mạnh nổi bật nhất của Việt Nam:',
                ['Du lịch biển đảo', 'Du lịch mạo hiểm', 'Du lịch tuyết', 'Du lịch sa mạc'], 0,
                'Bờ biển dài 3.260 km với nhiều bãi biển đẹp.');
            $this->quiz($L, 'Cảng biển lớn nhất Việt Nam hiện nay là:',
                ['Cảng Cát Lái (TP.HCM)', 'Cảng Hải Phòng', 'Cảng Đà Nẵng', 'Cảng Quy Nhơn'], 0,
                'Cụm cảng TP.HCM là cửa ngõ xuất nhập khẩu lớn nhất.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi ngành công nghiệp với trung tâm của nó.',
                [['Dầu khí', 'Vũng Tàu'], ['Điện tử', 'Bắc Ninh, Thái Nguyên'],
                 ['Dệt may', 'TP.HCM, Hà Nội'], ['Luyện thép', 'Thái Nguyên, Dung Quất']],
                'Công nghiệp phân bố theo vùng nguyên liệu và thị trường.');
            $this->matching($L, 'Nối mỗi điểm du lịch với địa phương của nó.',
                [['Vịnh Hạ Long', 'Quảng Ninh'], ['Phố cổ Hội An', 'Quảng Nam'],
                 ['Cố đô Huế', 'Thừa Thiên Huế'], ['Đảo Phú Quốc', 'Kiên Giang']],
                'Tứ đại điểm đến hấp dẫn du khách quốc tế.');
            $this->matching($L, 'Nối mỗi loại hình giao thông với tuyến tiêu biểu của nó.',
                [['Đường sắt', 'Bắc – Nam (Thống Nhất)'], ['Đường bộ', 'Quốc lộ 1A'],
                 ['Hàng không', 'Nội Bài – Tân Sơn Nhất'], ['Đường biển', 'Hải Phòng – TP.HCM']],
                'Giao thông phát triển phục vụ kinh tế – du lịch.');
            $this->matching($L, 'Nối mỗi khu/cơ sở công nghiệp với vùng của nó.',
                [['Các khu công nghiệp lớn nhất', 'Đông Nam Bộ'],
                 ['Lọc dầu Dung Quất', 'Quảng Ngãi'],
                 ['Samsung Thái Nguyên', 'Trung du Bắc Bộ'],
                 ['Nhiệt điện than', 'Quảng Ninh']],
                'Công nghiệp là động lực tăng trưởng kinh tế.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi ngành vào nhóm CÔNG NGHIỆP NẶNG hoặc CÔNG NGHIỆP NHẸ.',
                [['Dầu khí', 'Nặng'], ['Luyện kim', 'Nặng'], ['Cơ khí', 'Nặng'],
                 ['Dệt may', 'Nhẹ'], ['Da giày', 'Nhẹ'], ['Chế biến thực phẩm', 'Nhẹ']],
                'Công nghiệp nặng cần vốn lớn; nhẹ thu hút nhiều lao động.');
            $this->sortQ($L, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Dầu khí là ngành công nghiệp trọng điểm', 'Đúng'],
                 ['TP.HCM là trung tâm công nghiệp lớn nhất', 'Đúng'],
                 ['Du lịch biển là thế mạnh của Việt Nam', 'Đúng'],
                 ['Việt Nam chưa có nhà máy lọc dầu', 'Sai'],
                 ['Cảng Cát Lái là cảng biển lớn nhất', 'Đúng'],
                 ['Công nghiệp phân bố đều khắp cả nước', 'Sai']],
                'Công nghiệp tập trung ở Đông Nam Bộ và Đồng bằng sông Hồng.');
            $this->sortQ($L, 'Kéo mỗi điểm du lịch vào nhóm MIỀN BẮC, MIỀN TRUNG hoặc MIỀN NAM.',
                [['Hạ Long', 'Bắc'], ['Sa Pa', 'Bắc'],
                 ['Hội An', 'Trung'], ['Huế', 'Trung'],
                 ['Phú Quốc', 'Nam'], ['Mũi Né', 'Nam']],
                'Mỗi miền có những điểm đến đặc sắc riêng.');
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm CÔNG NGHIỆP hoặc DỊCH VỤ.',
                [['Khai thác dầu', 'Công nghiệp'], ['Lọc dầu', 'Công nghiệp'],
                 ['Sản xuất ô tô', 'Công nghiệp'], ['Ngân hàng', 'Dịch vụ'],
                 ['Du lịch', 'Dịch vụ'], ['Vận tải', 'Dịch vụ']],
                'Dịch vụ ngày càng chiếm tỉ trọng cao trong GDP.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ngành công nghiệp mang lại nguồn thu ngoại tệ lớn cho Việt Nam là công nghiệp ___.', [[0, 'dầu khí']],
                'Khai thác chủ yếu ở thềm lục địa phía Nam.');
            $this->fill($L, 'Trung tâm công nghiệp lớn nhất cả nước là ___.', [[0, 'TP.HCM']],
                'Dẫn đầu về giá trị sản xuất công nghiệp.');
            $this->fill($L, 'Nhà máy lọc dầu đầu tiên của Việt Nam đặt tại ___ (Quảng Ngãi).', [[0, 'Dung Quất']],
                'Đi vào hoạt động năm 2009.');
            $this->fill($L, 'Vịnh ___ (Quảng Ninh) là di sản thiên nhiên thế giới.', [[0, 'Hạ Long']],
                'Được UNESCO công nhận năm 1994.');
        }
    }
}
