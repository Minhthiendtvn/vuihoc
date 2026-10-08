<?php

namespace Database\Seeders;

use App\Models\FillAnswer;
use App\Models\Lesson;
use App\Models\MatchingPair;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Skill;
use App\Models\SortItem;
use App\Models\Topic;
use Illuminate\Database\Seeder;

/**
 * Phủ dữ liệu theo khối lớp cho NHÓM C: GDCD, Tin học, Công nghệ.
 * Nội dung TỰ VIẾT 100% tiếng Việt, bám chương trình đúng khối lớp 6-9.
 *
 * Với mỗi topic, với mỗi lớp trong [grade_min..grade_max]: tạo 2 bài học mới
 * gắn vào skill đầu tiên (sort_order nhỏ nhất) của topic.
 * Slug bài: {topic-slug}-lop-{grade}-1 / {topic-slug}-lop-{grade}-2.
 * Mỗi bài: >=4 quiz + >=4 matching + >=4 sort + >=4 fill (16 câu),
 * question nào cũng set grade + is_demo=true.
 *
 * Idempotent: bài học bỏ qua khi slug đã tồn tại; câu hỏi bỏ qua theo
 * (lesson_id, game_type) đã có — chạy lại không tạo trùng.
 */
class GradeCoverageGroupCSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $skillCache = [];

    public function run(): void
    {
        foreach ($this->plan() as $topicSlug => $grades) {
            foreach ($grades as $grade => $lessons) {
                $n = 1;
                foreach ($lessons as $meta) {
                    $this->ensureLesson($topicSlug, (int) $grade, $n++, $meta);
                }
            }
        }

        // GDCD
        $this->seedGdQuyenTreEmL61();
        $this->seedGdQuyenTreEmL62();
        $this->seedGdQuyenTreEmL71();
        $this->seedGdQuyenTreEmL72();
        $this->seedGdKyNangSongL71();
        $this->seedGdKyNangSongL72();
        $this->seedGdKyNangSongL81();
        $this->seedGdKyNangSongL82();
        $this->seedGdAnToanMangL81();
        $this->seedGdAnToanMangL82();
        $this->seedGdAnToanMangL91();
        $this->seedGdAnToanMangL92();
        // Tin học
        $this->seedThPhanCungL61();
        $this->seedThPhanCungL62();
        $this->seedThPhanCungL71();
        $this->seedThPhanCungL72();
        $this->seedThPhanMemL71();
        $this->seedThPhanMemL72();
        $this->seedThPhanMemL81();
        $this->seedThPhanMemL82();
        $this->seedThSuDungAnToanL81();
        $this->seedThSuDungAnToanL82();
        $this->seedThSuDungAnToanL91();
        $this->seedThSuDungAnToanL92();
        // Công nghệ
        $this->seedCnAnToanDienL61();
        $this->seedCnAnToanDienL62();
        $this->seedCnAnToanDienL71();
        $this->seedCnAnToanDienL72();
        $this->seedCnVatLieuL71();
        $this->seedCnVatLieuL72();
        $this->seedCnVatLieuL81();
        $this->seedCnVatLieuL82();
        $this->seedCnTrongTrotL81();
        $this->seedCnTrongTrotL82();
        $this->seedCnTrongTrotL91();
        $this->seedCnTrongTrotL92();
    }

    // ---------------- plan bài học ----------------
    // topic slug => grade => 2 bài (n=1, n=2)

    private function plan(): array
    {
        return [
            'gd-quyen-tre-em' => [
                6 => [
                    [
                        'title' => 'Quyền trẻ em lớp 6: 4 nhóm quyền cơ bản (1)',
                        'objective' => 'Nêu được 4 nhóm quyền của trẻ em: sống còn, phát triển, bảo vệ, tham gia.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Trẻ em có 4 nhóm quyền: sống còn (được nuôi dưỡng, chăm sóc), phát triển (được học tập, vui chơi), bảo vệ (không bị bạo lực, xâm hại), tham gia (được bày tỏ ý kiến). Mẹo nhớ: "Sống – Phát triển – Bảo vệ – Tham gia".',
                    ],
                    [
                        'title' => 'Quyền trẻ em lớp 6: bổn phận của trẻ em (2)',
                        'objective' => 'Kể được các bổn phận của trẻ em: hiếu thảo, chăm học, yêu Tổ quốc, tôn trọng mọi người.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Bên cạnh quyền, trẻ em có bổn phận: kính trọng, hiếu thảo với ông bà cha mẹ; chăm chỉ học tập; yêu quê hương, đất nước; tôn trọng, giúp đỡ mọi người; giữ gìn vệ sinh, bảo vệ môi trường.',
                    ],
                ],
                7 => [
                    [
                        'title' => 'Quyền trẻ em lớp 7: phòng chống bạo lực trẻ em (1)',
                        'objective' => 'Nhận biết các hành vi bạo lực với trẻ em và biết cách tự bảo vệ, tìm sự giúp đỡ.',
                        'difficulty' => 'de', 'duration' => 12,
                        'instructions' => 'Bạo lực trẻ em gồm: đánh đập, chửi mắng xúc phạm; dụ dỗ xâm hại; bỏ mặc không chăm sóc. Khi gặp nguy hiểm: hét to, chạy đến nơi đông người, kể với người tin cậy, gọi tổng đài 111.',
                    ],
                    [
                        'title' => 'Quyền trẻ em lớp 7: bảo vệ trẻ em trên thực tế (2)',
                        'objective' => 'Hiểu trách nhiệm của gia đình, nhà trường, xã hội trong bảo vệ trẻ em; biết quyền được tham gia ý kiến.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Gia đình phải nuôi dưỡng, không được bạo hành; nhà trường bảo đảm môi trường học tập an toàn; xã hội lên án hành vi xâm hại trẻ em. Trẻ có quyền tham gia: bày tỏ ý kiến, tham gia hoạt động Đội, góp ý xây dựng trường lớp.',
                    ],
                ],
            ],
            'gd-ky-nang-song' => [
                7 => [
                    [
                        'title' => 'Kỹ năng sống lớp 7: quản lý cảm xúc (1)',
                        'objective' => 'Nhận biết cảm xúc của bản thân và biết cách kiềm chế cơn giận.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Cảm xúc có vui, buồn, giận, sợ... Khi tức giận: hít thở sâu, đếm từ 1 đến 10, rời khỏi chỗ gây bực, nói chuyện khi đã bình tĩnh. Không đập phá, không nói lời làm tổn thương người khác.',
                    ],
                    [
                        'title' => 'Kỹ năng sống lớp 7: giao tiếp ứng xử (2)',
                        'objective' => 'Biết chào hỏi lễ phép, lắng nghe người khác, nói lời cảm ơn và xin lỗi đúng lúc.',
                        'difficulty' => 'trung_binh', 'duration' => 10,
                        'instructions' => 'Giao tiếp tốt: chào hỏi lễ phép, nhìn vào mắt người nói, lắng nghe không ngắt lời, nói "cảm ơn" khi được giúp, nói "xin lỗi" khi làm sai, không nói tục chửi bậy.',
                    ],
                ],
                8 => [
                    [
                        'title' => 'Kỹ năng sống lớp 8: quản lý thời gian (1)',
                        'objective' => 'Biết lập thời gian biểu, phân biệt việc quan trọng – khẩn cấp, tránh lãng phí thời gian.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Quản lý thời gian: liệt kê việc cần làm, xếp theo mức quan trọng, làm việc quan trọng trước, đặt giờ cố định cho học tập – nghỉ ngơi, hạn chế lướt điện thoại vô bổ.',
                    ],
                    [
                        'title' => 'Kỹ năng sống lớp 8: giải quyết mâu thuẫn (2)',
                        'objective' => 'Biết kiềm chế khi mâu thuẫn, lắng nghe đối phương, tìm giải pháp đôi bên cùng có lợi.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Khi mâu thuẫn: bình tĩnh, lắng nghe bạn nói hết, nói cảm xúc của mình ("mình buồn vì..."), không đổ lỗi, cùng tìm cách giải quyết, nhờ thầy cô hòa giải nếu không tự giải quyết được.',
                    ],
                ],
            ],
            'gd-an-toan-mang' => [
                8 => [
                    [
                        'title' => 'An toàn mạng lớp 8: bảo vệ thông tin cá nhân (1)',
                        'objective' => 'Biết những thông tin cá nhân không được chia sẻ công khai; biết đặt mật khẩu mạnh.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Thông tin cá nhân như họ tên đầy đủ, địa chỉ nhà, số điện thoại, trường lớp, ảnh riêng tư — không đăng công khai. Mật khẩu mạnh: dài từ 8 ký tự, có chữ hoa, chữ thường, số, ký tự đặc biệt; không dùng ngày sinh, tên mình.',
                    ],
                    [
                        'title' => 'An toàn mạng lớp 8: nhận biết tin giả (2)',
                        'objective' => 'Nhận biết dấu hiệu tin giả và biết kiểm chứng thông tin trước khi chia sẻ.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Dấu hiệu tin giả: tiêu đề giật gân, không rõ nguồn, chính tả sai nhiều, ảnh cắt ghép, kêu gọi chia sẻ gấp. Kiểm chứng: xem nguồn uy tín (báo chính thống), đối chiếu nhiều nguồn, hỏi thầy cô. Không chia sẻ tin chưa kiểm chứng.',
                    ],
                ],
                9 => [
                    [
                        'title' => 'An toàn mạng lớp 9: ứng phó lừa đảo mạng (1)',
                        'objective' => 'Nhận biết các chiêu lừa đảo trực tuyến; biết cách ứng phó và báo cáo.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Chiêu lừa đảo: giả danh công an hoặc ngân hàng, trúng thưởng bấm link, việc nhẹ lương cao, xin mã OTP. Nguyên tắc: không bấm link lạ, không cung cấp OTP hay mật khẩu, không chuyển tiền cho người lạ, chặn và báo cáo.',
                    ],
                    [
                        'title' => 'Môi trường lớp 9: trách nhiệm bảo vệ môi trường (2)',
                        'objective' => 'Hiểu trách nhiệm bảo vệ môi trường của công dân; biết các hành vi bị pháp luật cấm.',
                        'difficulty' => 'kho', 'duration' => 15,
                        'instructions' => 'Bảo vệ môi trường: tiết kiệm điện nước, phân loại rác, trồng cây xanh, hạn chế túi nilon. Pháp luật cấm: xả rác bừa bãi, chặt phá rừng, xả thải chưa xử lý. Biến đổi khí hậu gây hạn hán, lũ lụt, nước biển dâng — mỗi người đều có trách nhiệm.',
                    ],
                ],
            ],
            'th-phan-cung-may-tinh' => [
                6 => [
                    [
                        'title' => 'Phần cứng máy tính lớp 6: các bộ phận chính (1)',
                        'objective' => 'Kể tên các bộ phận chính của máy tính: thân máy, màn hình, bàn phím, chuột, loa.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Máy tính để bàn gồm: thân máy (chứa CPU, bộ nhớ), màn hình (hiển thị), bàn phím (gõ chữ), chuột (điều khiển), loa (phát âm thanh). Laptop gộp tất cả trong một khối gọn nhẹ.',
                    ],
                    [
                        'title' => 'Phần cứng máy tính lớp 6: thiết bị vào – ra (2)',
                        'objective' => 'Phân biệt thiết bị vào (input) và thiết bị ra (output) của máy tính.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Thiết bị vào đưa thông tin VÀO máy: bàn phím, chuột, micro, máy quét, webcam. Thiết bị ra đưa thông tin RA ngoài: màn hình, loa, máy in, máy chiếu. Mẹo nhớ: "Vào là nhập, Ra là xuất".',
                    ],
                ],
                7 => [
                    [
                        'title' => 'Phần cứng máy tính lớp 7: bộ xử lý và bộ nhớ (1)',
                        'objective' => 'Biết CPU là bộ não, RAM là bộ nhớ tạm, ổ cứng và SSD lưu trữ lâu dài.',
                        'difficulty' => 'de', 'duration' => 12,
                        'instructions' => 'CPU (bộ xử lý trung tâm) là "bộ não", thực hiện mọi tính toán. RAM là bộ nhớ tạm, mất dữ liệu khi tắt máy. Ổ cứng (HDD) và SSD lưu dữ liệu lâu dài; SSD nhanh hơn HDD.',
                    ],
                    [
                        'title' => 'Phần cứng máy tính lớp 7: bảo quản thiết bị (2)',
                        'objective' => 'Biết cách giữ gìn, vệ sinh và sử dụng thiết bị đúng cách, an toàn điện.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Bảo quản máy tính: để nơi khô ráo thoáng mát, tránh nước và bụi, không ăn uống gần máy, tắt máy đúng cách (Start → Shut down), dùng ổn áp khi điện chập chờn, không tự tháo linh kiện.',
                    ],
                ],
            ],
            'th-phan-mem-tep-thu-muc' => [
                7 => [
                    [
                        'title' => 'Phần mềm lớp 7: hệ thống và ứng dụng (1)',
                        'objective' => 'Phân biệt phần mềm hệ thống (hệ điều hành) và phần mềm ứng dụng.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Phần mềm hệ thống: hệ điều hành (Windows, Android) điều khiển phần cứng. Phần mềm ứng dụng phục vụ nhu cầu người dùng (Word, trình duyệt, game). Không có hệ điều hành, máy tính không hoạt động được.',
                    ],
                    [
                        'title' => 'Tệp và thư mục lớp 7: phần mở rộng (2)',
                        'objective' => 'Nhận biết loại tệp qua phần mở rộng: .docx, .xlsx, .pptx, .pdf, .jpg, .mp3, .mp4.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Tên tệp gồm tên và phần mở rộng sau dấu chấm. .docx là văn bản Word, .xlsx là bảng tính Excel, .pptx là trình chiếu, .pdf là tài liệu, .jpg/.png là ảnh, .mp3 là nhạc, .mp4 là video.',
                    ],
                ],
                8 => [
                    [
                        'title' => 'Quản lý tệp lớp 8: thư mục (1)',
                        'objective' => 'Biết tạo, đổi tên, sao chép, di chuyển, xóa tệp và thư mục; sắp xếp khoa học.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Thư mục (folder) chứa tệp và thư mục con. Thao tác: tạo mới (New → Folder), đổi tên (Rename), sao chép (Copy – Paste), di chuyển (Cut – Paste), xóa (Delete → thùng rác). Đặt tên rõ ràng, phân loại theo môn học.',
                    ],
                    [
                        'title' => 'Phần mềm lớp 8: bản quyền phần mềm (2)',
                        'objective' => 'Hiểu bản quyền phần mềm; phân biệt phần mềm có phí, miễn phí và mã nguồn mở.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Phần mềm có bản quyền phải mua giấy phép, không sao chép lậu. Phần mềm miễn phí dùng không mất tiền. Mã nguồn mở được xem và sửa mã nguồn (ví dụ LibreOffice). Dùng phần mềm lậu là vi phạm pháp luật và dễ dính virus.',
                    ],
                ],
            ],
            'th-su-dung-an-toan' => [
                8 => [
                    [
                        'title' => 'Sử dụng an toàn lớp 8: mật khẩu mạnh (1)',
                        'objective' => 'Biết tạo và quản lý mật khẩu mạnh; biết bật xác thực hai bước.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Mật khẩu mạnh: dài từ 8 ký tự, gồm chữ hoa, chữ thường, số, ký tự đặc biệt; không dùng thông tin cá nhân. Mỗi tài khoản một mật khẩu khác nhau. Bật xác thực hai bước (2FA). Không chia sẻ mật khẩu; đăng xuất khi dùng máy chung.',
                    ],
                    [
                        'title' => 'Sử dụng an toàn lớp 8: virus máy tính (2)',
                        'objective' => 'Biết virus lây lan qua đâu, cách phòng tránh và dùng phần mềm diệt virus.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Virus lây qua USB lạ, tệp đính kèm email lạ, phần mềm crack, link lạ. Dấu hiệu: máy chậm bất thường, file lạ xuất hiện, quảng cáo bật liên tục. Phòng tránh: không cắm USB lạ, không mở tệp lạ, cài phần mềm diệt virus, cập nhật thường xuyên, sao lưu dữ liệu.',
                    ],
                ],
                9 => [
                    [
                        'title' => 'Sử dụng an toàn lớp 9: quyền riêng tư trực tuyến (1)',
                        'objective' => 'Hiểu dấu chân số; biết kiểm soát quyền riêng tư và ứng xử có trách nhiệm trên mạng.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Dấu chân số: mọi thứ đăng lên mạng đều lưu lại lâu dài. Hãy kiểm tra cài đặt riêng tư, suy nghĩ trước khi đăng, xin phép trước khi đăng ảnh người khác, không bình luận ác ý, không lan truyền tin chưa kiểm chứng.',
                    ],
                    [
                        'title' => 'Sử dụng an toàn lớp 9: ứng phó sự cố mạng (2)',
                        'objective' => 'Biết quy trình ứng phó khi bị hack, bị lừa, bị bắt nạt mạng; biết các kênh hỗ trợ.',
                        'difficulty' => 'kho', 'duration' => 15,
                        'instructions' => 'Bị hack: đổi mật khẩu ngay, đăng xuất mọi thiết bị, bật 2FA, báo cáo nền tảng. Bị lừa tiền: giữ bằng chứng, báo công an, báo ngân hàng. Bị bắt nạt mạng: không đáp trả, lưu bằng chứng, chặn và báo cáo, kể người lớn, gọi 111 nếu nghiêm trọng.',
                    ],
                ],
            ],
            'cn-an-toan-dien' => [
                6 => [
                    [
                        'title' => 'An toàn điện lớp 6: nhận biết nguy hiểm (1)',
                        'objective' => 'Nhận biết các tình huống nguy hiểm về điện trong gia đình.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Tình huống nguy hiểm: dây điện hở, ổ cắm vỡ, tay ướt chạm đồ điện, dùng vật kim loại chọc ổ cắm, thả diều gần đường dây điện, trèo cột điện. Điện giật có thể gây bỏng, ngừng tim, tử vong.',
                    ],
                    [
                        'title' => 'An toàn điện lớp 6: quy tắc dùng đồ điện (2)',
                        'objective' => 'Biết các quy tắc an toàn khi sử dụng đồ điện trong gia đình.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Quy tắc: cắm và rút phích cầm vào phần nhựa, không giật dây; không dùng đồ điện khi tay ướt; rút phích khi không dùng; không để đồ điện gần nước; không tự sửa đồ điện hỏng; nhờ người lớn khi cần.',
                    ],
                ],
                7 => [
                    [
                        'title' => 'An toàn điện lớp 7: xử lý sự cố điện (1)',
                        'objective' => 'Biết cách xử lý khi gặp sự cố điện: ngắt nguồn, gọi người lớn, gọi cứu hộ.',
                        'difficulty' => 'de', 'duration' => 12,
                        'instructions' => 'Khi chập điện: ngắt cầu dao ngay. Người bị điện giật: KHÔNG chạm tay trực tiếp khi chưa ngắt điện — dùng vật cách điện (gậy gỗ khô) gạt dây ra. Gọi 114 khi cháy, 115 khi cấp cứu. Không dội nước vào đám cháy điện.',
                    ],
                    [
                        'title' => 'An toàn điện lớp 7: tiết kiệm điện (2)',
                        'objective' => 'Biết các biện pháp tiết kiệm điện trong gia đình và ý nghĩa của việc tiết kiệm điện.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Tiết kiệm điện: tắt đèn và quạt khi ra khỏi phòng, dùng bóng đèn LED, rút sạc khi đầy pin, để điều hòa 26–27°C, tận dụng ánh sáng tự nhiên. Ý nghĩa: giảm tiền điện, giảm tải lưới điện, bảo vệ môi trường.',
                    ],
                ],
            ],
            'cn-vat-lieu-dung-cu' => [
                7 => [
                    [
                        'title' => 'Vật liệu lớp 7: gỗ, kim loại, nhựa (1)',
                        'objective' => 'Nêu được tính chất cơ bản và ứng dụng của gỗ, kim loại, nhựa.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Gỗ nhẹ, dễ gia công, làm bàn ghế. Kim loại (sắt, nhôm, đồng) cứng, dẫn điện, dẫn nhiệt; làm dụng cụ, dây điện, xoong nồi. Nhựa nhẹ, không dẫn điện, chống nước; làm chai lọ, đồ dùng hằng ngày.',
                    ],
                    [
                        'title' => 'Dụng cụ lớp 7: đo và đánh dấu (2)',
                        'objective' => 'Biết công dụng của thước, êke, compa; đo và đánh dấu đúng kỹ thuật.',
                        'difficulty' => 'de', 'duration' => 10,
                        'instructions' => 'Thước thẳng đo độ dài, êke vẽ góc vuông, compa vẽ đường tròn. Khi đo: đặt vạch 0 trùng đầu vật, mắt nhìn thẳng. Đánh dấu bằng bút chì, vạch dấu rõ ràng trước khi cắt.',
                    ],
                ],
                8 => [
                    [
                        'title' => 'Dụng cụ lớp 8: cơ khí cầm tay (1)',
                        'objective' => 'Biết công dụng và cách dùng an toàn của búa, tua vít, kìm, cờ lê, cưa.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Búa đóng đinh; tua vít vặn ốc (đầu dẹt, đầu 4 cạnh); kìm kẹp và cắt dây; cờ lê, mỏ lết vặn bu-lông; cưa cắt gỗ. An toàn: cầm chắc cán, kiểm tra dụng cụ trước khi dùng, không dùng dụng cụ hỏng, đeo kính bảo hộ khi cần.',
                    ],
                    [
                        'title' => 'Vật liệu lớp 8: dùng tiết kiệm và tái chế (2)',
                        'objective' => 'Biết sử dụng vật liệu tiết kiệm, tái chế; biết phân loại rác thải.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Tiết kiệm vật liệu: đo đạc kỹ trước khi cắt, tận dụng vật liệu thừa. Tái chế: giấy, nhựa, kim loại, thủy tinh có thể tái chế. Phân loại rác: hữu cơ, tái chế, nguy hại (pin, bóng đèn). Pin cũ không vứt bừa bãi.',
                    ],
                ],
            ],
            'cn-trong-trot' => [
                8 => [
                    [
                        'title' => 'Trồng trọt lớp 8: các bước trồng cây (1)',
                        'objective' => 'Nêu được các bước cơ bản khi trồng cây: chọn giống, làm đất, gieo trồng, chăm sóc.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Bước 1: chọn giống tốt, phù hợp. Bước 2: làm đất tơi xốp, bón lót. Bước 3: gieo hạt hoặc trồng cây con đúng thời vụ, đúng khoảng cách. Bước 4: chăm sóc — tưới nước, bón phân, phòng trừ sâu bệnh.',
                    ],
                    [
                        'title' => 'Trồng trọt lớp 8: tưới nước và bón phân (2)',
                        'objective' => 'Biết tưới nước đúng cách; phân biệt phân hữu cơ và phân hóa học.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Tưới nước: đủ ẩm, không để úng; tưới sáng sớm hoặc chiều mát, tránh giữa trưa nắng gắt. Phân hữu cơ (phân chuồng, phân xanh) an toàn, cải tạo đất. Phân hóa học (đạm, lân, kali) tác dụng nhanh nhưng phải đúng liều; lạm dụng làm hại đất.',
                    ],
                ],
                9 => [
                    [
                        'title' => 'Trồng trọt lớp 9: phòng trừ sâu bệnh (1)',
                        'objective' => 'Nhận biết sâu bệnh hại cây; biết biện pháp phòng trừ tổng hợp an toàn.',
                        'difficulty' => 'trung_binh', 'duration' => 12,
                        'instructions' => 'Sâu bệnh thường gặp: sâu ăn lá, rệp, bệnh đốm lá, thối rễ. Phòng trừ tổng hợp: vệ sinh đồng ruộng, bắt sâu bằng tay, dùng thiên địch (bọ rùa, ong), bẫy đèn, ưu tiên thuốc sinh học, hạn chế thuốc hóa học.',
                    ],
                    [
                        'title' => 'Trồng trọt lớp 9: thu hoạch, bảo quản và công nghệ mới (2)',
                        'objective' => 'Biết thu hoạch đúng thời điểm, bảo quản nông sản; làm quen với thủy canh, tưới nhỏ giọt.',
                        'difficulty' => 'kho', 'duration' => 15,
                        'instructions' => 'Thu hoạch đúng độ chín, nhẹ tay, vào buổi sáng mát. Bảo quản nơi khô ráo thoáng mát; thóc phải phơi hoặc sấy khô. Công nghệ mới: thủy canh (trồng cây trong nước dinh dưỡng, không cần đất), nhà kính, tưới nhỏ giọt tiết kiệm nước.',
                    ],
                ],
            ],
        ];
    }

    // ---------------- helpers ----------------

    private function firstSkill(string $topicSlug): Skill
    {
        if (! isset($this->skillCache[$topicSlug])) {
            $topic = Topic::where('slug', $topicSlug)->firstOrFail();
            $this->skillCache[$topicSlug] = Skill::where('topic_id', $topic->id)
                ->orderBy('sort_order')->firstOrFail();
        }
        return $this->skillCache[$topicSlug];
    }

    private function ensureLesson(string $topicSlug, int $grade, int $n, array $meta): Lesson
    {
        $slug = "{$topicSlug}-lop-{$grade}-{$n}";
        $existing = Lesson::where('slug', $slug)->first();
        if ($existing) {
            if ($existing->grade === null) {
                $existing->update(['grade' => $grade]);
            }
            $this->lessonBySlug[$slug] = $existing->fresh();
            return $this->lessonBySlug[$slug];
        }
        $skill = $this->firstSkill($topicSlug);
        $order = (int) Lesson::where('skill_id', $skill->id)->max('sort_order') + 1;
        $lesson = Lesson::create([
            'skill_id' => $skill->id,
            'slug' => $slug,
            'title' => $meta['title'],
            'objective' => $meta['objective'],
            'difficulty' => $meta['difficulty'],
            'duration_minutes' => $meta['duration'],
            'instructions' => $meta['instructions'],
            'sort_order' => $order,
            'status' => 'published',
            'grade' => $grade,
            'is_demo' => true,
        ]);
        $this->lessonBySlug[$slug] = $lesson;
        return $lesson;
    }

    private function lesson(string $slug): Lesson
    {
        if (! isset($this->lessonBySlug[$slug])) {
            $this->lessonBySlug[$slug] = Lesson::where('slug', $slug)->firstOrFail();
        }
        return $this->lessonBySlug[$slug];
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
            'grade'       => $lesson->grade,
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

    // ================= GDCD — QUYỀN TRẺ EM =================

    private function seedGdQuyenTreEmL61(): void
    {
        $L = 'gd-quyen-tre-em-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trẻ em có bao nhiêu nhóm quyền cơ bản?',
                ['4 nhóm', '3 nhóm', '5 nhóm', '2 nhóm'], 0,
                'Trẻ em có 4 nhóm quyền: sống còn, phát triển, bảo vệ và tham gia.');
            $this->quiz($L, 'Quyền được đi học thuộc nhóm quyền nào?',
                ['Phát triển', 'Sống còn', 'Bảo vệ', 'Tham gia'], 0,
                'Được đi học giúp trẻ phát triển trí tuệ, thuộc nhóm quyền phát triển.');
            $this->quiz($L, 'Quyền được bày tỏ ý kiến thuộc nhóm quyền nào?',
                ['Tham gia', 'Sống còn', 'Bảo vệ', 'Phát triển'], 0,
                'Trẻ em có quyền nói lên suy nghĩ, ý kiến của mình — thuộc nhóm quyền tham gia.');
            $this->quiz($L, 'Việc nào thể hiện quyền được bảo vệ của trẻ em?',
                ['Không ai được đánh đập trẻ em', 'Trẻ phải làm việc nặng nhọc', 'Trẻ không được đi học', 'Cấm trẻ vui chơi'], 0,
                'Trẻ em được bảo vệ khỏi mọi hình thức bạo lực và xâm hại.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nhóm quyền với ví dụ đúng của nó.',
                [['Sống còn', 'Được nuôi dưỡng, chăm sóc'],
                 ['Phát triển', 'Được đi học, vui chơi'],
                 ['Bảo vệ', 'Không bị đánh đập, xâm hại'],
                 ['Tham gia', 'Được bày tỏ ý kiến']],
                'Bốn nhóm quyền: sống còn, phát triển, bảo vệ, tham gia.');
            $this->matching($L, 'Nối mỗi quyền của trẻ với người có trách nhiệm đảm bảo.',
                [['Được đi học', 'Gia đình và nhà trường'],
                 ['Được chữa bệnh', 'Gia đình và bệnh viện'],
                 ['Được vui chơi', 'Gia đình và địa phương'],
                 ['Được an toàn', 'Mọi người xung quanh']],
                'Gia đình, nhà trường và xã hội cùng có trách nhiệm đảm bảo quyền trẻ em.');
            $this->matching($L, 'Nối mỗi khái niệm với ý nghĩa đúng của nó.',
                [['Trẻ em', 'Người dưới 16 tuổi'],
                 ['Quyền trẻ em', 'Những điều trẻ được hưởng, được pháp luật bảo vệ'],
                 ['Bổn phận', 'Những việc trẻ em phải làm'],
                 ['Công ước', 'Thỏa thuận quốc tế về quyền trẻ em']],
                'Người dưới 16 tuổi được coi là trẻ em và được hưởng các quyền theo pháp luật.');
            $this->matching($L, 'Nối mỗi tình huống với nhóm quyền tương ứng.',
                [['Bé An được tiêm vắc-xin', 'Sống còn'],
                 ['Bé Bình được đến trường', 'Phát triển'],
                 ['Bé Chi được nói ý kiến trong lớp', 'Tham gia'],
                 ['Bé Dũng được bảo vệ khỏi bắt nạt', 'Bảo vệ']],
                'Tiêm vắc-xin là sống còn, đi học là phát triển, nói ý kiến là tham gia, chống bắt nạt là bảo vệ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm QUYỀN SỐNG CÒN hoặc QUYỀN PHÁT TRIỂN.',
                [['Được ăn no mặc ấm', 'Sống còn'], ['Được khám chữa bệnh', 'Sống còn'],
                 ['Được đi học', 'Phát triển'], ['Được vui chơi', 'Phát triển']],
                'Ăn, mặc, chữa bệnh là sống còn; học tập, vui chơi là phát triển.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm QUYỀN hoặc BỔN PHẬN của trẻ em.',
                [['Được đi học', 'Quyền'], ['Được vui chơi', 'Quyền'],
                 ['Kính trọng cha mẹ', 'Bổn phận'], ['Chăm chỉ học tập', 'Bổn phận']],
                'Được hưởng là quyền, phải làm là bổn phận.');
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm ĐƯỢC PHÉP hoặc KHÔNG ĐƯỢC PHÉP đối với trẻ em.',
                [['Cho trẻ đi học', 'Được phép'], ['Cho trẻ vui chơi', 'Được phép'],
                 ['Đánh đập trẻ', 'Không được phép'], ['Bắt trẻ lao động nặng', 'Không được phép']],
                'Mọi hành vi bạo lực, bóc lột trẻ em đều bị pháp luật nghiêm cấm.');
            $this->sortQ($L, 'Kéo mỗi việc vào đúng NHÓM QUYỀN của nó.',
                [['Được ăn uống đầy đủ', 'Sống còn'], ['Được khám chữa bệnh', 'Sống còn'],
                 ['Được học tập', 'Phát triển'], ['Được vui chơi giải trí', 'Phát triển'],
                 ['Được nói lên ý kiến', 'Tham gia'], ['Được bầu ban cán sự lớp', 'Tham gia']],
                'Sống còn: ăn, chữa bệnh. Phát triển: học, chơi. Tham gia: nói ý kiến, bầu cử.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trẻ em có ___ nhóm quyền cơ bản.', [[0, '4']],
                'Bốn nhóm quyền: sống còn, phát triển, bảo vệ, tham gia.');
            $this->fill($L, 'Quyền được đi học thuộc nhóm quyền phát ___.', [[0, 'triển']],
                'Học tập giúp trẻ phát triển trí tuệ và nhân cách.');
            $this->fill($L, 'Người dưới ___ tuổi được coi là trẻ em.', [[0, '16']],
                'Theo pháp luật Việt Nam, trẻ em là người dưới 16 tuổi.');
            $this->fill($L, 'Trẻ em có quyền bày ___ ý kiến của mình.', [[0, 'tỏ']],
                'Bày tỏ ý kiến là một quyền thuộc nhóm quyền tham gia.');
        }
    }

    private function seedGdQuyenTreEmL62(): void
    {
        $L = 'gd-quyen-tre-em-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bổn phận quan trọng nhất của học sinh là gì?',
                ['Chăm chỉ học tập', 'Chơi game thật giỏi', 'Ngủ thật nhiều', 'Xem tivi cả ngày'], 0,
                'Học tập là bổn phận hàng đầu của học sinh.');
            $this->quiz($L, 'Việc nào thể hiện lòng hiếu thảo với cha mẹ?',
                ['Giúp mẹ làm việc nhà', 'Cãi lại bố mẹ', 'Bỏ học đi chơi', 'Nói dối cha mẹ'], 0,
                'Giúp đỡ cha mẹ việc nhà là biểu hiện của lòng hiếu thảo.');
            $this->quiz($L, 'Trẻ em có bổn phận gì đối với Tổ quốc?',
                ['Yêu quê hương, đất nước', 'Chỉ lo cho bản thân', 'Không cần biết lịch sử', 'Chê bai quê hương'], 0,
                'Yêu quê hương, đất nước là bổn phận thiêng liêng của mỗi trẻ em Việt Nam.');
            $this->quiz($L, 'Gặp người lớn tuổi, em nên làm gì?',
                ['Chào hỏi lễ phép', 'Làm ngơ đi qua', 'Trêu chọc', 'Chen lấn xô đẩy'], 0,
                'Kính trọng người lớn tuổi là nét đẹp văn hóa của người Việt Nam.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bổn phận với việc làm thể hiện nó.',
                [['Hiếu thảo', 'Giúp đỡ cha mẹ việc nhà'],
                 ['Chăm học', 'Làm bài tập đầy đủ'],
                 ['Yêu nước', 'Tìm hiểu lịch sử dân tộc'],
                 ['Tôn trọng', 'Chào hỏi lễ phép']],
                'Hiếu thảo, chăm học, yêu nước, tôn trọng là những bổn phận của trẻ em.');
            $this->matching($L, 'Nối mỗi nơi với bổn phận của trẻ em ở đó.',
                [['Gia đình', 'Vâng lời ông bà cha mẹ'],
                 ['Trường học', 'Chấp hành nội quy'],
                 ['Nơi công cộng', 'Giữ trật tự vệ sinh'],
                 ['Quê hương', 'Bảo vệ môi trường']],
                'Ở mỗi nơi, trẻ em đều có những bổn phận phù hợp cần thực hiện.');
            $this->matching($L, 'Nối mỗi hành vi với đánh giá đúng về nó.',
                [['Nhặt rác bỏ vào thùng', 'Tốt'],
                 ['Giúp bạn học bài', 'Tốt'],
                 ['Vẽ bậy lên tường', 'Xấu'],
                 ['Nói tục chửi bậy', 'Xấu']],
                'Hành vi tốt cần phát huy, hành vi xấu cần tránh xa.');
            $this->matching($L, 'Nối mỗi người với cách ứng xử đúng của trẻ em.',
                [['Thầy cô', 'Kính trọng'],
                 ['Bạn bè', 'Đoàn kết giúp đỡ'],
                 ['Người già', 'Nhường nhịn'],
                 ['Em nhỏ', 'Yêu thương']],
                'Với mỗi người, trẻ em cần có cách ứng xử lễ phép, phù hợp.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm BỔN PHẬN TRONG GIA ĐÌNH hoặc Ở TRƯỜNG.',
                [['Giúp mẹ nấu cơm', 'Trong gia đình'], ['Vâng lời cha mẹ', 'Trong gia đình'],
                 ['Làm bài tập đầy đủ', 'Ở trường'], ['Giữ trật tự trong lớp', 'Ở trường']],
                'Trong gia đình giúp đỡ cha mẹ, ở trường chăm học và giữ kỷ luật.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN LÀM hoặc KHÔNG NÊN LÀM.',
                [['Chào hỏi người lớn', 'Nên làm'], ['Giúp đỡ bạn bè', 'Nên làm'],
                 ['Xả rác bừa bãi', 'Không nên làm'], ['Nói dối', 'Không nên làm']],
                'Việc tốt nên làm mỗi ngày, việc xấu tuyệt đối tránh xa.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm QUYỀN hoặc BỔN PHẬN.',
                [['Được chăm sóc sức khỏe', 'Quyền'], ['Được vui chơi', 'Quyền'],
                 ['Giữ gìn vệ sinh chung', 'Bổn phận'], ['Bảo vệ cây xanh', 'Bổn phận']],
                'Được chăm sóc, vui chơi là quyền; giữ vệ sinh, bảo vệ cây xanh là bổn phận.');
            $this->sortQ($L, 'Kéo mỗi bổn phận vào đúng ĐỐI TƯỢNG của nó.',
                [['Kính trọng cha mẹ', 'Với gia đình'], ['Phụ giúp việc nhà', 'Với gia đình'],
                 ['Chăm chỉ học tập', 'Với bản thân'], ['Rèn luyện sức khỏe', 'Với bản thân'],
                 ['Giữ gìn trường lớp sạch đẹp', 'Với trường học'], ['Giúp đỡ người khó khăn', 'Với xã hội']],
                'Trẻ em có bổn phận với gia đình, bản thân, trường học và xã hội.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Học sinh có bổn phận ___ chỉ học tập.', [[0, 'chăm']],
                'Chăm chỉ học tập là bổn phận hàng đầu của học sinh.');
            $this->fill($L, 'Gặp thầy cô, em phải chào hỏi lễ ___.', [[0, 'phép']],
                'Lễ phép với thầy cô là nét đẹp của học sinh.');
            $this->fill($L, 'Trẻ em phải yêu quê ___, đất nước.', [[0, 'hương']],
                'Yêu quê hương, đất nước là bổn phận thiêng liêng.');
            $this->fill($L, 'Không được ___ rác bừa bãi nơi công cộng.', [[0, 'xả']],
                'Giữ gìn vệ sinh nơi công cộng là bổn phận của mỗi người.');
        }
    }

    private function seedGdQuyenTreEmL71(): void
    {
        $L = 'gd-quyen-tre-em-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hành vi nào là bạo lực đối với trẻ em?',
                ['Đánh đập trẻ', 'Nhắc nhở nhẹ nhàng', 'Khuyên bảo', 'Dạy dỗ ân cần'], 0,
                'Đánh đập là bạo lực thể chất, bị pháp luật nghiêm cấm.');
            $this->quiz($L, 'Khi bị người lạ dụ dỗ đi theo, em nên làm gì?',
                ['Hét to và chạy đến nơi đông người', 'Đi theo người lạ', 'Im lặng nghe theo', 'Cho người lạ địa chỉ nhà'], 0,
                'Hét to và chạy đến nơi đông người là cách tự bảo vệ tốt nhất.');
            $this->quiz($L, 'Tổng đài quốc gia bảo vệ trẻ em là số nào?',
                ['111', '113', '114', '115'], 0,
                'Gọi 111 khi trẻ em bị bạo lực, xâm hại hoặc cần giúp đỡ khẩn cấp.');
            $this->quiz($L, 'Khi gặp nguy hiểm, em nên kể với ai đầu tiên?',
                ['Cha mẹ hoặc thầy cô tin cậy', 'Bạn mới quen trên mạng', 'Người lạ tốt bụng', 'Giữ bí mật một mình'], 0,
                'Hãy kể ngay với cha mẹ hoặc thầy cô mà em tin cậy để được giúp đỡ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hành vi với loại của nó.',
                [['Đánh đập', 'Bạo lực thể chất'],
                 ['Chửi mắng, sỉ nhục', 'Bạo lực tinh thần'],
                 ['Dụ dỗ đi theo người lạ', 'Nguy cơ xâm hại'],
                 ['Bỏ mặc không cho ăn', 'Bỏ bê, sao nhãng']],
                'Bạo lực có nhiều dạng: thể chất, tinh thần, xâm hại và bỏ bê.');
            $this->matching($L, 'Nối mỗi tình huống với cách xử lý đúng.',
                [['Bị bạn đánh', 'Báo thầy cô'],
                 ['Người lạ cho quà rủ đi', 'Từ chối và bỏ chạy'],
                 ['Thấy bạn bị bắt nạt', 'Can ngăn hoặc báo người lớn'],
                 ['Bị đe dọa trên mạng', 'Chặn và kể với cha mẹ']],
                'Gặp nguy hiểm: tránh xa, báo người lớn, gọi 111 khi cần.');
            $this->matching($L, 'Nối mỗi số điện thoại với công dụng của nó.',
                [['111', 'Bảo vệ trẻ em'],
                 ['113', 'Công an'],
                 ['114', 'Cứu hỏa'],
                 ['115', 'Cấp cứu y tế']],
                'Nhớ các số khẩn cấp: 111, 113, 114, 115.');
            $this->matching($L, 'Nối mỗi nơi với mức độ an toàn khi có người lạ.',
                [['Sân trường có thầy cô', 'An toàn'],
                 ['Nhà bạn thân đã quen', 'An toàn'],
                 ['Ngõ vắng với người lạ', 'Nguy hiểm'],
                 ['Xe của người không quen', 'Nguy hiểm']],
                'Luôn ở nơi đông người, quen thuộc; tránh nơi vắng vẻ với người lạ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Đi học cùng bạn', 'An toàn'], ['Ở nhà với bố mẹ', 'An toàn'],
                 ['Nhận quà của người lạ', 'Nguy hiểm'], ['Đi theo người lạ', 'Nguy hiểm']],
                'Đi cùng người quen thì an toàn; tin người lạ thì nguy hiểm.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN khi gặp nguy hiểm.',
                [['Hét to kêu cứu', 'Nên'], ['Kể với người tin cậy', 'Nên'],
                 ['Im lặng chịu đựng', 'Không nên'], ['Giấu kín mọi chuyện', 'Không nên']],
                'Gặp nguy hiểm phải kêu cứu và kể với người tin cậy, không im lặng.');
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm BẠO LỰC hoặc KHÔNG PHẢI BẠO LỰC.',
                [['Đánh đập', 'Bạo lực'], ['Sỉ nhục', 'Bạo lực'],
                 ['Nhắc nhở nhẹ nhàng', 'Không phải bạo lực'], ['Khen ngợi', 'Không phải bạo lực']],
                'Đánh đập, sỉ nhục là bạo lực; nhắc nhở, khen ngợi thì không.');
            $this->sortQ($L, 'Kéo mỗi người vào nhóm NGƯỜI TIN CẬY hoặc NGƯỜI LẠ cần cảnh giác.',
                [['Cha mẹ', 'Người tin cậy'], ['Thầy cô giáo', 'Người tin cậy'],
                 ['Bác hàng xóm thân thiết', 'Người tin cậy'],
                 ['Người lạ rủ đi chơi', 'Người lạ'], ['Bạn mới quen trên mạng', 'Người lạ'],
                 ['Người lạ xin địa chỉ nhà', 'Người lạ']],
                'Chỉ tin cậy người thân quen; luôn cảnh giác với người lạ.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tổng đài quốc gia bảo vệ trẻ em là số ___.', [[0, '111']],
                'Gọi 111 khi trẻ em bị bạo lực, xâm hại hoặc cần giúp đỡ.');
            $this->fill($L, 'Khi gặp nguy hiểm, em hãy ___ to để kêu cứu.', [[0, 'hét']],
                'Hét to thu hút sự chú ý của mọi người xung quanh.');
            $this->fill($L, 'Không được đi theo người ___.', [[0, 'lạ']],
                'Người lạ có thể có ý đồ xấu, phải luôn cảnh giác.');
            $this->fill($L, 'Bị bắt nạt, em nên kể với người ___ cậy.', [[0, 'tin']],
                'Người tin cậy như cha mẹ, thầy cô sẽ giúp em.');
        }
    }

    private function seedGdQuyenTreEmL72(): void
    {
        $L = 'gd-quyen-tre-em-lop-7-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ai chịu trách nhiệm đầu tiên trong việc bảo vệ trẻ em?',
                ['Gia đình', 'Nhà trường', 'Xã hội', 'Bản thân trẻ'], 0,
                'Gia đình là nơi đầu tiên có trách nhiệm nuôi dưỡng và bảo vệ trẻ em.', $d);
            $this->quiz($L, 'Nhà trường KHÔNG được làm gì đối với học sinh?',
                ['Đánh đập, xúc phạm học sinh', 'Khen thưởng học sinh', 'Dạy dỗ học sinh', 'Nhắc nhở học sinh'], 0,
                'Mọi hình thức đánh đập, xúc phạm học sinh đều bị nghiêm cấm.', $d);
            $this->quiz($L, 'Quyền tham gia của trẻ em thể hiện qua việc nào?',
                ['Được bày tỏ ý kiến', 'Bị ép làm theo ý người lớn', 'Không được nói', 'Bị cấm tham gia hoạt động'], 0,
                'Trẻ em có quyền bày tỏ ý kiến về những việc liên quan đến mình.', $d);
            $this->quiz($L, 'Thấy bạn bị bạo hành, em nên làm gì?',
                ['Báo thầy cô hoặc người lớn', 'Đứng xem cho vui', 'Hùa theo', 'Bỏ đi mặc kệ'], 0,
                'Báo người lớn là cách giúp bạn an toàn và đúng đắn nhất.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chủ thể với trách nhiệm bảo vệ trẻ em của họ.',
                [['Gia đình', 'Nuôi dưỡng, không bạo hành'],
                 ['Nhà trường', 'Môi trường học tập an toàn'],
                 ['Xã hội', 'Lên án, tố giác hành vi xâm hại'],
                 ['Bản thân trẻ', 'Biết tự bảo vệ mình']],
                'Bảo vệ trẻ em là trách nhiệm chung của gia đình, nhà trường và xã hội.', $d);
            $this->matching($L, 'Nối mỗi quyền với biểu hiện đúng của nó.',
                [['Được học tập', 'Đến trường đầy đủ'],
                 ['Được vui chơi', 'Tham gia hoạt động Đội'],
                 ['Được bày tỏ ý kiến', 'Phát biểu trong lớp'],
                 ['Được bảo vệ', 'Không ai được xâm hại']],
                'Mỗi quyền của trẻ em đều có biểu hiện cụ thể trong đời sống.', $d);
            $this->matching($L, 'Nối mỗi hành vi của người lớn với đánh giá đúng.',
                [['Đánh con khi con mắc lỗi', 'Sai'],
                 ['Lắng nghe con nói', 'Đúng'],
                 ['Bắt con nghỉ học đi làm', 'Sai'],
                 ['Cho con đi khám bệnh', 'Đúng']],
                'Người lớn phải bảo vệ, không được xâm hại quyền trẻ em.', $d);
            $this->matching($L, 'Nối mỗi tổ chức với vai trò của nó đối với trẻ em.',
                [['Đội Thiếu niên', 'Hoạt động vui chơi, rèn luyện'],
                 ['Nhà trường', 'Dạy dỗ, giáo dục'],
                 ['Gia đình', 'Nuôi dưỡng, chăm sóc'],
                 ['Tổng đài 111', 'Hỗ trợ khẩn cấp']],
                'Nhiều tổ chức cùng chung tay bảo vệ và chăm sóc trẻ em.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI đối với trẻ em.',
                [['Cho trẻ đi học', 'Đúng'], ['Lắng nghe ý kiến trẻ', 'Đúng'],
                 ['Bắt trẻ lao động nặng', 'Sai'], ['Đánh đập trẻ', 'Sai']],
                'Cho đi học, lắng nghe là đúng; bóc lột, bạo hành là sai.', $d);
            $this->sortQ($L, 'Kéo mỗi trách nhiệm vào đúng CHỦ THỂ của nó.',
                [['Nuôi dưỡng con', 'Gia đình'], ['Dạy học an toàn', 'Nhà trường'],
                 ['Tố giác hành vi xâm hại', 'Xã hội'], ['Tự bảo vệ mình', 'Bản thân trẻ']],
                'Mỗi chủ thể có trách nhiệm riêng trong bảo vệ trẻ em.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm QUYỀN THAM GIA hoặc KHÔNG.',
                [['Phát biểu ý kiến', 'Tham gia'], ['Bầu ban cán sự', 'Tham gia'],
                 ['Bị cấm nói', 'Không tham gia'], ['Bị ép im lặng', 'Không tham gia']],
                'Được nói, được bầu là tham gia; bị cấm, bị ép thì không.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm QUYỀN, BỔN PHẬN hoặc ĐƯỢC BẢO VỆ.',
                [['Đi học đầy đủ', 'Quyền'], ['Vui chơi giải trí', 'Quyền'],
                 ['Chăm chỉ học tập', 'Bổn phận'], ['Hiếu thảo với cha mẹ', 'Bổn phận'],
                 ['Không bị bạo hành', 'Được bảo vệ'], ['Có nơi ở an toàn', 'Được bảo vệ']],
                'Quyền được hưởng, bổn phận phải làm, và luôn được bảo vệ.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Gia đình có trách nhiệm ___ dưỡng và bảo vệ trẻ em.', [[0, 'nuôi']],
                'Nuôi dưỡng là trách nhiệm đầu tiên của gia đình.', $d);
            $this->fill($L, 'Nhà trường phải bảo đảm môi trường học tập ___ toàn.', [[0, 'an']],
                'Trường học phải là nơi an toàn cho mọi học sinh.', $d);
            $this->fill($L, 'Trẻ em có quyền ___ tỏ ý kiến của mình.', [[0, 'bày']],
                'Bày tỏ ý kiến là quyền tham gia của trẻ em.', $d);
            $this->fill($L, 'Hành vi xâm hại trẻ em bị pháp luật ___ lý nghiêm.', [[0, 'xử']],
                'Pháp luật xử lý nghiêm mọi hành vi xâm hại trẻ em.', $d);
        }
    }

    // ================= GDCD — KỸ NĂNG SỐNG =================

    private function seedGdKyNangSongL71(): void
    {
        $L = 'gd-ky-nang-song-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi tức giận, cách xử lý đúng là gì?',
                ['Hít thở sâu và bình tĩnh lại', 'Đập phá đồ đạc', 'Chửi bới ầm ĩ', 'Đánh bạn cho hả giận'], 0,
                'Hít thở sâu giúp ta bình tĩnh lại trước khi hành động.');
            $this->quiz($L, 'Cảm xúc nào là cảm xúc tích cực?',
                ['Vui vẻ', 'Giận dữ', 'Ghen tị', 'Sợ hãi'], 0,
                'Vui vẻ là cảm xúc tích cực, giúp ta khỏe mạnh và học tốt.');
            $this->quiz($L, 'Trước khi nói trong lúc đang giận, em nên làm gì?',
                ['Đếm từ 1 đến 10 cho bình tĩnh', 'Nói ngay cho hả', 'Hét thật to', 'Bỏ đi không bao giờ gặp'], 0,
                'Đếm đến 10 giúp cơn giận lắng xuống, tránh nói lời làm tổn thương.');
            $this->quiz($L, 'Việc nào giúp tâm trạng buồn bã tốt hơn?',
                ['Tâm sự với người tin cậy', 'Giữ kín trong lòng mãi', 'Trả thù người khác', 'Khóc một mình suốt ngày'], 0,
                'Chia sẻ với người tin cậy giúp ta nhẹ lòng và tìm được cách giải quyết.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cảm xúc với biểu hiện của nó.',
                [['Vui', 'Cười tươi'],
                 ['Buồn', 'Khóc, im lặng'],
                 ['Giận', 'Đỏ mặt, to tiếng'],
                 ['Sợ', 'Run, muốn trốn tránh']],
                'Nhận biết cảm xúc qua biểu hiện giúp ta hiểu mình và hiểu người.');
            $this->matching($L, 'Nối mỗi tình huống với cách xử lý phù hợp.',
                [['Bị điểm kém', 'Cố gắng hơn lần sau'],
                 ['Bị bạn trêu', 'Bình tĩnh nói chuyện'],
                 ['Đang tức giận', 'Hít thở sâu'],
                 ['Buồn bã', 'Tâm sự với mẹ']],
                'Mỗi tình huống đều có cách xử lý bình tĩnh và đúng đắn.');
            $this->matching($L, 'Nối mỗi hành vi với đánh giá nên hay không nên.',
                [['Hít thở sâu khi giận', 'Nên'],
                 ['Xin lỗi khi làm sai', 'Nên'],
                 ['Đập phá đồ đạc', 'Không nên'],
                 ['Nói lời tổn thương', 'Không nên']],
                'Kiềm chế cơn giận, biết xin lỗi là biểu hiện của người có kỹ năng sống.');
            $this->matching($L, 'Nối mỗi người với việc có nên tâm sự cùng họ không.',
                [['Cha mẹ', 'Nên tâm sự'],
                 ['Thầy cô tin cậy', 'Nên tâm sự'],
                 ['Bạn thân', 'Nên tâm sự'],
                 ['Người lạ trên mạng', 'Không nên']],
                'Chỉ tâm sự chuyện buồn với người thân quen, đáng tin cậy.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cảm xúc vào nhóm TÍCH CỰC hoặc TIÊU CỰC.',
                [['Vui vẻ', 'Tích cực'], ['Tự hào', 'Tích cực'],
                 ['Giận dữ', 'Tiêu cực'], ['Ghen tị', 'Tiêu cực']],
                'Cảm xúc tích cực giúp ta khỏe mạnh; tiêu cực cần được kiểm soát.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN khi tức giận.',
                [['Hít thở sâu', 'Nên'], ['Đếm đến 10', 'Nên'],
                 ['Đập phá đồ đạc', 'Không nên'], ['Chửi bới', 'Không nên']],
                'Khi giận: nên bình tĩnh lại, không nên đập phá hay chửi bới.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm GIÚP BÌNH TĨNH hoặc LÀM TỆ HƠN.',
                [['Nghe nhạc nhẹ', 'Giúp bình tĩnh'], ['Đi dạo', 'Giúp bình tĩnh'],
                 ['Cãi nhau to tiếng', 'Làm tệ hơn'], ['Trả đũa', 'Làm tệ hơn']],
                'Nghe nhạc, đi dạo giúp bình tĩnh; cãi nhau, trả đũa chỉ làm tệ hơn.');
            $this->sortQ($L, 'Kéo mỗi biểu hiện vào đúng CẢM XÚC của nó.',
                [['Cười tươi', 'Vui'], ['Nhảy cẫng lên', 'Vui'],
                 ['Khóc', 'Buồn'], ['Thở dài', 'Buồn'],
                 ['Đỏ mặt', 'Giận'], ['Nắm chặt tay', 'Giận']],
                'Mỗi cảm xúc có biểu hiện riêng trên khuôn mặt và cơ thể.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Khi tức giận, hãy hít thở ___ để bình tĩnh lại.', [[0, 'sâu']],
                'Hít thở sâu giúp cơ thể thư giãn, cơn giận lắng xuống.');
            $this->fill($L, 'Trước khi nói lúc đang giận, hãy đếm từ 1 đến ___.', [[0, '10']],
                'Đếm đến 10 cho ta thời gian bình tĩnh, tránh nói lời hối hận.');
            $this->fill($L, 'Không nên ___ phá đồ đạc khi tức giận.', [[0, 'đập']],
                'Đập phá đồ đạc không giải quyết được gì mà còn gây thiệt hại.');
            $this->fill($L, 'Buồn bã, em có thể ___ sự với cha mẹ.', [[0, 'tâm']],
                'Tâm sự giúp ta nhẹ lòng và nhận được lời khuyên tốt.');
        }
    }

    private function seedGdKyNangSongL72(): void
    {
        $L = 'gd-ky-nang-song-lop-7-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi được bạn giúp đỡ, em nên nói gì?',
                ['Cảm ơn bạn', 'Không nói gì', 'Đó là việc của bạn mà', 'Lần sau giúp tiếp nhé'], 0,
                'Lời cảm ơn thể hiện sự trân trọng và lịch sự.', $d);
            $this->quiz($L, 'Khi làm sai, em nên làm gì?',
                ['Nhận lỗi và xin lỗi', 'Đổ lỗi cho bạn', 'Chối cãi quanh co', 'Im lặng bỏ đi'], 0,
                'Dám nhận lỗi và xin lỗi là biểu hiện của người dũng cảm.', $d);
            $this->quiz($L, 'Lắng nghe người khác nói cần làm gì?',
                ['Không ngắt lời', 'Vừa nghe vừa chơi điện thoại', 'Nhìn đi chỗ khác', 'Cười cợt'], 0,
                'Lắng nghe chăm chú, không ngắt lời là tôn trọng người nói.', $d);
            $this->quiz($L, 'Gặp thầy cô ở sân trường, em nên làm gì?',
                ['Chào lễ phép', 'Làm ngơ đi qua', 'Trêu đùa', 'Chạy trốn'], 0,
                'Chào hỏi lễ phép thể hiện sự kính trọng thầy cô.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tình huống với câu nói phù hợp.',
                [['Được giúp đỡ', 'Cảm ơn bạn'],
                 ['Làm đổ nước vào bạn', 'Xin lỗi bạn'],
                 ['Gặp thầy cô', 'Em chào thầy, cô ạ'],
                 ['Muốn hỏi điều gì', 'Bạn cho mình hỏi một chút']],
                'Mỗi tình huống có câu nói lịch sự phù hợp.', $d);
            $this->matching($L, 'Nối mỗi hành vi với đánh giá lịch sự hay khiếm nhã.',
                [['Chào hỏi', 'Lịch sự'],
                 ['Lắng nghe', 'Lịch sự'],
                 ['Nói tục', 'Khiếm nhã'],
                 ['Ngắt lời người khác', 'Khiếm nhã']],
                'Người lịch sự được mọi người yêu mến.', $d);
            $this->matching($L, 'Nối mỗi đối tượng với cách xưng hô đúng.',
                [['Thầy cô', 'Em, thưa thầy cô'],
                 ['Bạn bè', 'Mình, bạn'],
                 ['Người lớn tuổi', 'Cháu, bác'],
                 ['Em nhỏ', 'Anh, chị']],
                'Xưng hô đúng thể hiện sự lễ phép và tôn trọng.', $d);
            $this->matching($L, 'Nối mỗi việc với đánh giá nên hay không nên khi trò chuyện.',
                [['Nhìn vào người nói', 'Nên'],
                 ['Gật đầu đồng tình', 'Nên'],
                 ['Nói to át lời người khác', 'Không nên'],
                 ['Bĩu môi chê bai', 'Không nên']],
                'Trò chuyện văn minh: nhìn người nói, không át lời, không chê bai.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm LỊCH SỰ hoặc KHIẾM NHÃ.',
                [['Chào hỏi', 'Lịch sự'], ['Cảm ơn', 'Lịch sự'],
                 ['Nói tục', 'Khiếm nhã'], ['Chen lấn', 'Khiếm nhã']],
                'Lịch sự được yêu mến, khiếm nhã bị xa lánh.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN khi trò chuyện.',
                [['Lắng nghe chăm chú', 'Nên'], ['Tôn trọng ý kiến bạn', 'Nên'],
                 ['Ngắt lời', 'Không nên'], ['Cười nhạo', 'Không nên']],
                'Trò chuyện tốt: lắng nghe, tôn trọng; tránh ngắt lời, cười nhạo.', $d);
            $this->sortQ($L, 'Kéo mỗi câu nói vào đúng HOÀN CẢNH của nó.',
                [['Cảm ơn', 'Khi được giúp đỡ'], ['Xin lỗi', 'Khi làm sai'],
                 ['Em chào thầy ạ', 'Khi gặp người lớn'], ['Tạm biệt', 'Khi ra về']],
                'Nói đúng câu, đúng lúc là người giao tiếp khéo léo.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm LỜI NÓI ĐẸP, LỜI NÓI XẤU hoặc THÁI ĐỘ TỐT.',
                [['Chào hỏi lễ phép', 'Lời nói đẹp'], ['Cảm ơn chân thành', 'Lời nói đẹp'],
                 ['Nói tục chửi bậy', 'Lời nói xấu'], ['Đặt điều nói xấu', 'Lời nói xấu'],
                 ['Lắng nghe chăm chú', 'Thái độ tốt'], ['Giúp đỡ tận tình', 'Thái độ tốt']],
                'Lời nói đẹp và thái độ tốt giúp ta có nhiều bạn bè.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Được giúp đỡ, em nhớ nói lời ___ ơn.', [[0, 'cảm']],
                'Lời cảm ơn tuy nhỏ nhưng thể hiện sự trân trọng.', $d);
            $this->fill($L, 'Làm sai, em phải biết nhận lỗi và ___ lỗi.', [[0, 'xin']],
                'Biết xin lỗi là biểu hiện của lòng dũng cảm.', $d);
            $this->fill($L, 'Khi trò chuyện, không nên ___ lời người khác.', [[0, 'ngắt']],
                'Ngắt lời là thiếu tôn trọng người đang nói.', $d);
            $this->fill($L, 'Gặp người lớn, em chào hỏi ___ phép.', [[0, 'lễ']],
                'Lễ phép là nét đẹp văn hóa của học sinh.', $d);
        }
    }

    private function seedGdKyNangSongL81(): void
    {
        $L = 'gd-ky-nang-song-lop-8-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Việc đầu tiên khi quản lý thời gian là gì?',
                ['Liệt kê các việc cần làm', 'Chơi trước đã', 'Để mai tính', 'Làm việc dễ trước'], 0,
                'Liệt kê việc cần làm giúp ta nhìn rõ và sắp xếp hợp lý.', $d);
            $this->quiz($L, 'Ta nên ưu tiên làm việc nào trước?',
                ['Quan trọng và gấp', 'Dễ làm', 'Vui vẻ', 'Để được lâu'], 0,
                'Việc quan trọng và gấp cần được ưu tiên làm trước.', $d);
            $this->quiz($L, '"Kẻ cắp thời gian" lớn nhất của học sinh hiện nay là gì?',
                ['Lướt điện thoại vô bổ', 'Đọc sách', 'Tập thể dục', 'Giúp mẹ việc nhà'], 0,
                'Lướt điện thoại vô bổ ngốn rất nhiều thời gian mà ta không hay.', $d);
            $this->quiz($L, 'Một thời gian biểu tốt cần đảm bảo điều gì?',
                ['Cân bằng học – nghỉ – chơi', 'Học suốt ngày đêm', 'Chơi suốt ngày', 'Không cần kế hoạch'], 0,
                'Thời gian biểu tốt cân bằng giữa học tập, nghỉ ngơi và vui chơi.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi việc với mức ưu tiên của nó.',
                [['Làm bài tập mai nộp', 'Gấp và quan trọng'],
                 ['Ôn thi cuối kỳ', 'Quan trọng'],
                 ['Xem phim giải trí', 'Ít quan trọng'],
                 ['Lướt mạng vô bổ', 'Nên hạn chế']],
                'Xếp việc theo mức quan trọng giúp ta không bỏ sót việc cần thiết.', $d);
            $this->matching($L, 'Nối mỗi thói quen với đánh giá tốt hay xấu.',
                [['Dậy đúng giờ', 'Tốt'],
                 ['Học đúng giờ đã định', 'Tốt'],
                 ['Thức khuya lướt điện thoại', 'Xấu'],
                 ['Để bài tập đến phút chót', 'Xấu']],
                'Thói quen tốt tiết kiệm thời gian, thói quen xấu lãng phí thời gian.', $d);
            $this->matching($L, 'Nối mỗi khung giờ với việc phù hợp.',
                [['Sáng sớm', 'Học bài'],
                 ['Buổi tối', 'Làm bài tập'],
                 ['Giờ ra chơi', 'Nghỉ ngơi'],
                 ['Cuối tuần', 'Vui chơi cùng gia đình']],
                'Giờ nào việc nấy giúp một ngày trôi qua hiệu quả.', $d);
            $this->matching($L, 'Nối mỗi nguyên tắc với ý nghĩa của nó.',
                [['Việc hôm nay chớ để ngày mai', 'Không trì hoãn'],
                 ['Giờ nào việc nấy', 'Tập trung khi làm'],
                 ['Ưu tiên việc quan trọng', 'Việc quan trọng tạo kết quả lớn'],
                 ['Nghỉ ngơi hợp lý', 'Tái tạo năng lượng']],
                'Những nguyên tắc đơn giản giúp quản lý thời gian hiệu quả.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm QUAN TRỌNG hoặc ÍT QUAN TRỌNG.',
                [['Ôn thi', 'Quan trọng'], ['Làm bài tập', 'Quan trọng'],
                 ['Cày game', 'Ít quan trọng'], ['Lướt mạng vô bổ', 'Ít quan trọng']],
                'Ưu tiên việc quan trọng, hạn chế việc ít quan trọng.', $d);
            $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm TỐT hoặc XẤU.',
                [['Lập thời gian biểu', 'Tốt'], ['Đúng giờ', 'Tốt'],
                 ['Trì hoãn', 'Xấu'], ['Thức khuya vô bổ', 'Xấu']],
                'Thói quen tốt giúp ta làm chủ thời gian.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Làm việc khó trước', 'Nên'], ['Nghỉ ngơi hợp lý', 'Nên'],
                 ['Để dồn việc', 'Không nên'], ['Học thâu đêm', 'Không nên']],
                'Làm việc khó trước, nghỉ ngơi hợp lý; tránh dồn việc và thức thâu đêm.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm VIỆC HỌC, VIỆC NHÀ hoặc VUI CHƠI.',
                [['Làm bài tập về nhà', 'Việc học'], ['Ôn bài cũ', 'Việc học'],
                 ['Phụ giúp cha mẹ', 'Việc nhà'], ['Quét nhà', 'Việc nhà'],
                 ['Đá bóng với bạn', 'Vui chơi'], ['Xem phim cuối tuần', 'Vui chơi']],
                'Một ngày cân bằng gồm việc học, việc nhà và vui chơi.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hãy ___ kê các việc cần làm trong ngày.', [[0, 'liệt']],
                'Liệt kê giúp ta không quên việc quan trọng.', $d);
            $this->fill($L, 'Nên ưu tiên làm việc quan trọng và ___ trước.', [[0, 'gấp']],
                'Việc vừa quan trọng vừa gấp phải làm trước tiên.', $d);
            $this->fill($L, '"Việc hôm nay chớ để ngày ___."', [[0, 'mai']],
                'Không trì hoãn là nguyên tắc vàng quản lý thời gian.', $d);
            $this->fill($L, 'Hạn chế lướt điện thoại ___ bổ.', [[0, 'vô']],
                'Lướt điện thoại vô bổ là kẻ cắp thời gian lớn nhất.', $d);
        }
    }

    private function seedGdKyNangSongL82(): void
    {
        $L = 'gd-ky-nang-song-lop-8-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi mâu thuẫn với bạn, điều đầu tiên nên làm là gì?',
                ['Bình tĩnh lại', 'Cãi to hơn bạn', 'Đánh nhau', 'Nghỉ chơi luôn'], 0,
                'Bình tĩnh là bước đầu tiên để giải quyết mọi mâu thuẫn.', $d);
            $this->quiz($L, 'Câu nói nào giúp hòa giải mâu thuẫn?',
                ['"Mình buồn vì..."', '"Tại bạn hết"', '"Bạn tệ lắm"', '"Tôi không thèm nói"'], 0,
                'Nói cảm xúc của mình thay vì đổ lỗi giúp đối phương dễ lắng nghe.', $d);
            $this->quiz($L, 'Không tự hòa giải được thì nên làm gì?',
                ['Nhờ thầy cô giúp đỡ', 'Rủ thêm người đánh nhau', 'Đăng lên mạng bêu xấu', 'Im lặng ôm hận'], 0,
                'Nhờ thầy cô hòa giải là cách khôn ngoan khi mâu thuẫn vượt tầm.', $d);
            $this->quiz($L, 'Lắng nghe khi mâu thuẫn giúp ích gì?',
                ['Hiểu ý của bạn', 'Bị thua thiệt', 'Mất thời gian', 'Bạn lấn tới'], 0,
                'Lắng nghe giúp ta hiểu bạn nghĩ gì, từ đó dễ tìm tiếng nói chung.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bước hòa giải với nội dung của nó.',
                [['Bình tĩnh', 'Hít thở sâu'],
                 ['Lắng nghe', 'Để bạn nói hết'],
                 ['Bày tỏ', 'Nói cảm xúc của mình'],
                 ['Giải pháp', 'Cùng tìm cách giải quyết']],
                'Bốn bước hòa giải: bình tĩnh, lắng nghe, bày tỏ, tìm giải pháp.', $d);
            $this->matching($L, 'Nối mỗi câu nói với tác dụng của nó.',
                [['"Mình hiểu ý bạn"', 'Xoa dịu'],
                 ['"Cùng tìm cách nhé"', 'Hợp tác'],
                 ['"Tại bạn hết"', 'Đổ lỗi'],
                 ['"Đồ tồi"', 'Xúc phạm']],
                'Lời nói xoa dịu và hợp tác giúp hòa giải; đổ lỗi và xúc phạm làm tệ hơn.', $d);
            $this->matching($L, 'Nối mỗi tình huống với cách xử lý phù hợp.',
                [['Tranh nhau đồ chơi', 'Chia nhau cùng chơi'],
                 ['Bạn nói xấu mình', 'Bình tĩnh hỏi rõ'],
                 ['Hiểu lầm nhau', 'Giải thích rõ ràng'],
                 ['Bị bạn đánh', 'Báo thầy cô']],
                'Mỗi mâu thuẫn có cách xử lý phù hợp, từ nhẹ đến cần người lớn.', $d);
            $this->matching($L, 'Nối mỗi người với trường hợp cần nhờ họ hòa giải.',
                [['Tự hai bạn', 'Mâu thuẫn nhỏ'],
                 ['Lớp trưởng', 'Cần người trung gian'],
                 ['Thầy cô', 'Mâu thuẫn lớn'],
                 ['Cha mẹ', 'Việc nghiêm trọng']],
                'Mâu thuẫn nhỏ tự giải quyết, lớn hơn thì nhờ người có uy tín.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm GIÚP HÒA GIẢI hoặc LÀM CĂNG THÊM.',
                [['Lắng nghe', 'Giúp hòa giải'], ['Bình tĩnh', 'Giúp hòa giải'],
                 ['Chửi bới', 'Làm căng thêm'], ['Đánh nhau', 'Làm căng thêm']],
                'Bình tĩnh và lắng nghe giúp hòa giải; chửi bới, đánh nhau làm tệ hơn.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN khi mâu thuẫn.',
                [['Nói cảm xúc của mình', 'Nên'], ['Tìm giải pháp chung', 'Nên'],
                 ['Đổ lỗi cho bạn', 'Không nên'], ['Đăng mạng bêu xấu', 'Không nên']],
                'Nên bày tỏ và tìm giải pháp; không nên đổ lỗi hay bêu xấu.', $d);
            $this->sortQ($L, 'Kéo mỗi cách nói vào nhóm TỐT hoặc XẤU.',
                [['"Mình buồn vì..."', 'Tốt'], ['"Cùng giải quyết nhé"', 'Tốt'],
                 ['"Tại bạn hết"', 'Xấu'], ['"Bạn thật tệ"', 'Xấu']],
                'Nói về cảm xúc của mình là tốt; đổ lỗi, chê bai là xấu.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào đúng BƯỚC hòa giải của nó.',
                [['Hít thở sâu', 'Bước 1: Bình tĩnh'], ['Đếm đến 10', 'Bước 1: Bình tĩnh'],
                 ['Nghe bạn nói hết', 'Bước 2: Lắng nghe'], ['Không ngắt lời', 'Bước 2: Lắng nghe'],
                 ['Đề xuất cách giải quyết', 'Bước 3: Cùng giải quyết'], ['Nhờ thầy cô nếu cần', 'Bước 3: Cùng giải quyết']],
                'Ba bước: bình tĩnh, lắng nghe, cùng tìm cách giải quyết.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Khi mâu thuẫn, trước hết hãy ___ tĩnh lại.', [[0, 'bình']],
                'Bình tĩnh là bước đầu tiên của mọi cuộc hòa giải.', $d);
            $this->fill($L, 'Hãy lắng nghe để ___ ý kiến của bạn.', [[0, 'hiểu']],
                'Hiểu ý bạn giúp ta tìm được tiếng nói chung.', $d);
            $this->fill($L, 'Nói "___ buồn vì..." thay vì đổ lỗi cho bạn.', [[0, 'mình']],
                'Nói cảm xúc của mình giúp đối phương dễ lắng nghe hơn.', $d);
            $this->fill($L, 'Không tự giải quyết được thì nhờ ___ cô giúp.', [[0, 'thầy']],
                'Thầy cô là người hòa giải công bằng và đáng tin cậy.', $d);
        }
    }

    // ================= GDCD — AN TOÀN MẠNG & MÔI TRƯỜNG =================

    private function seedGdAnToanMangL81(): void
    {
        $L = 'gd-an-toan-mang-lop-8-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thông tin nào KHÔNG nên đăng công khai lên mạng?',
                ['Địa chỉ nhà', 'Sở thích đọc sách', 'Món ăn yêu thích', 'Ước mơ của mình'], 0,
                'Địa chỉ nhà là thông tin nhạy cảm, kẻ xấu có thể lợi dụng.', $d);
            $this->quiz($L, 'Mật khẩu nào mạnh nhất?',
                ['Hs#2026$Ab', '123456', 'ngaysinhcuaminh', 'tenminh123'], 0,
                'Mật khẩu mạnh dài, có đủ chữ hoa, chữ thường, số và ký tự đặc biệt.', $d);
            $this->quiz($L, 'Người lạ xin ảnh riêng tư của em, em nên làm gì?',
                ['Từ chối và kể với cha mẹ', 'Gửi ngay cho họ', 'Gửi một ít', 'Đòi quà rồi mới gửi'], 0,
                'Tuyệt đối không gửi ảnh riêng tư cho người lạ; hãy kể với cha mẹ.', $d);
            $this->quiz($L, 'Tài khoản mạng xã hội nên để ở chế độ nào?',
                ['Riêng tư', 'Công khai hoàn toàn', 'Ai cũng xem được', 'Chia sẻ mật khẩu cho bạn'], 0,
                'Chế độ riêng tư giúp kiểm soát ai được xem thông tin của mình.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thông tin với việc có nên chia sẻ công khai không.',
                [['Địa chỉ nhà', 'Không chia sẻ'],
                 ['Số điện thoại', 'Không chia sẻ'],
                 ['Sở thích', 'Được chia sẻ'],
                 ['Ảnh hoạt động của lớp', 'Được chia sẻ']],
                'Thông tin nhạy cảm không chia sẻ; sở thích, hoạt động chung thì được.', $d);
            $this->matching($L, 'Nối mỗi mật khẩu với đánh giá mạnh hay yếu.',
                [['123456', 'Yếu'],
                 ['Tên và ngày sinh', 'Yếu'],
                 ['Dùng chung mọi tài khoản', 'Yếu'],
                 ['8 ký tự đủ 4 loại', 'Mạnh']],
                'Mật khẩu yếu dễ bị đoán; mật khẩu mạnh khó bị bẻ khóa.', $d);
            $this->matching($L, 'Nối mỗi tình huống với cách xử lý đúng.',
                [['Người lạ kết bạn', 'Không chấp nhận'],
                 ['Bị xin thông tin cá nhân', 'Từ chối'],
                 ['Nghi lộ mật khẩu', 'Đổi ngay'],
                 ['Bị đe dọa', 'Kể với người lớn']],
                'Cảnh giác với người lạ, bảo vệ thông tin, nhờ người lớn khi bị đe dọa.', $d);
            $this->matching($L, 'Nối mỗi việc với đánh giá về quyền riêng tư.',
                [['Để chế độ bạn bè', 'Bảo vệ'],
                 ['Kiểm tra ảnh được gắn thẻ', 'Nên làm'],
                 ['Đăng ảnh nhạy cảm', 'Nguy hiểm'],
                 ['Chia sẻ vị trí trực tiếp', 'Không nên']],
                'Chủ động cài đặt riêng tư và kiểm soát những gì mình chia sẻ.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm NÊN hoặc KHÔNG NÊN chia sẻ công khai.',
                [['Sở thích', 'Nên chia sẻ'], ['Ảnh hoạt động lớp', 'Nên chia sẻ'],
                 ['Địa chỉ nhà', 'Không nên'], ['Số điện thoại', 'Không nên']],
                'Chia sẻ sở thích thì được; địa chỉ, số điện thoại thì không.', $d);
            $this->sortQ($L, 'Kéo mỗi mật khẩu vào nhóm MẠNH hoặc YẾU.',
                [['Dài đủ 4 loại ký tự', 'Mạnh'], ['Cụm từ khó đoán', 'Mạnh'],
                 ['123456', 'Yếu'], ['Ngày sinh', 'Yếu']],
                'Mật khẩu mạnh dài và phức tạp; yếu thì dễ đoán.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Chế độ riêng tư', 'An toàn'], ['Kể cha mẹ khi bị đe dọa', 'An toàn'],
                 ['Kết bạn với người lạ', 'Nguy hiểm'], ['Gửi ảnh riêng cho người lạ', 'Nguy hiểm']],
                'Riêng tư và kể người lớn thì an toàn; tin người lạ thì nguy hiểm.', $d);
            $this->sortQ($L, 'Kéo mỗi thông tin vào nhóm NHẠY CẢM hoặc ÍT NHẠY CẢM.',
                [['Họ tên đầy đủ', 'Nhạy cảm'], ['Địa chỉ nhà', 'Nhạy cảm'], ['Số điện thoại', 'Nhạy cảm'],
                 ['Món ăn yêu thích', 'Ít nhạy cảm'], ['Đội bóng yêu thích', 'Ít nhạy cảm'], ['Sách đang đọc', 'Ít nhạy cảm']],
                'Thông tin định danh là nhạy cảm; sở thích thì ít nhạy cảm hơn.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Không đăng ___ chỉ nhà lên mạng xã hội.', [[0, 'địa']],
                'Địa chỉ nhà là thông tin nhạy cảm, phải giữ kín.', $d);
            $this->fill($L, 'Mật khẩu mạnh nên dài từ ___ ký tự trở lên.', [[0, '8']],
                'Mật khẩu càng dài càng khó bị bẻ khóa.', $d);
            $this->fill($L, 'Không dùng ngày ___ làm mật khẩu.', [[0, 'sinh']],
                'Ngày sinh dễ đoán, không an toàn khi làm mật khẩu.', $d);
            $this->fill($L, 'Bị đe dọa trên mạng, hãy kể với ___ mẹ.', [[0, 'cha']],
                'Cha mẹ sẽ giúp em xử lý khi bị đe dọa trên mạng.', $d);
        }
    }

    private function seedGdAnToanMangL82(): void
    {
        $L = 'gd-an-toan-mang-lop-8-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dấu hiệu nào cho thấy đây có thể là tin giả?',
                ['Tiêu đề giật gân', 'Nguồn rõ ràng', 'Báo chính thống đăng', 'Nhiều nguồn giống nhau'], 0,
                'Tin giả thường có tiêu đề giật gân để câu lượt xem.', $d);
            $this->quiz($L, 'Trước khi chia sẻ một tin giật gân, em nên làm gì?',
                ['Kiểm chứng nguồn tin', 'Chia sẻ ngay', 'Thêm bình luận kích động', 'Gửi cho nhiều người'], 0,
                'Luôn kiểm chứng nguồn trước khi chia sẻ bất kỳ tin nào.', $d);
            $this->quiz($L, 'Nguồn tin nào đáng tin cậy?',
                ['Báo chí chính thống', 'Tin nhắn nặc danh', 'Trang lá cải', 'Lời đồn thổi'], 0,
                'Báo chí chính thống có quy trình kiểm chứng thông tin.', $d);
            $this->quiz($L, 'Chia sẻ tin giả có thể gây hậu quả gì?',
                ['Gây hoang mang và có thể vi phạm pháp luật', 'Giúp ích cho mọi người', 'Hoàn toàn vô hại', 'Được mọi người khen ngợi'], 0,
                'Tin giả gây hoang mang dư luận; người lan truyền có thể bị xử phạt.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dấu hiệu với đánh giá tin giả hay tin thật.',
                [['Giật gân, không rõ nguồn', 'Tin giả'],
                 ['Chính tả sai nhiều', 'Tin giả'],
                 ['Báo chính thống đăng', 'Tin thật'],
                 ['Nhiều nguồn uy tín giống nhau', 'Tin thật']],
                'Tin giả: giật gân, vô nguồn, sai chính tả; tin thật: nguồn uy tín.', $d);
            $this->matching($L, 'Nối mỗi việc với đánh giá nên hay không nên.',
                [['Kiểm chứng trước khi chia sẻ', 'Nên'],
                 ['Hỏi thầy cô khi nghi ngờ', 'Nên'],
                 ['Chia sẻ ngay tin sốc', 'Không nên'],
                 ['Tin mọi thứ trên mạng', 'Không nên']],
                'Người dùng mạng thông minh luôn kiểm chứng trước khi tin.', $d);
            $this->matching($L, 'Nối mỗi nguồn với mức độ đáng tin.',
                [['Báo chí chính thống', 'Đáng tin'],
                 ['Website của trường', 'Đáng tin'],
                 ['Trang nặc danh', 'Không đáng tin'],
                 ['Tin nhắn chuyển tiếp', 'Không đáng tin']],
                'Ưu tiên nguồn chính thống; cảnh giác với nguồn nặc danh.', $d);
            $this->matching($L, 'Nối mỗi việc với hậu quả của tin giả.',
                [['Hoang mang dư luận', 'Có thể xảy ra'],
                 ['Mất tiền oan', 'Có thể xảy ra'],
                 ['Bị xử phạt', 'Có thể xảy ra'],
                 ['Mọi người vui vẻ', 'Không xảy ra']],
                'Tin giả gây hoang mang, thiệt hại tiền bạc và bị xử phạt.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nguồn tin vào nhóm ĐÁNG TIN hoặc ĐÁNG NGỜ.',
                [['Báo chính thống', 'Đáng tin'], ['Website trường', 'Đáng tin'],
                 ['Tin nhắn nặc danh', 'Đáng ngờ'], ['Tiêu đề giật gân', 'Đáng ngờ']],
                'Nguồn chính thống đáng tin; nặc danh, giật gân thì đáng ngờ.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Kiểm chứng nguồn', 'Nên'], ['Đọc nhiều nguồn', 'Nên'],
                 ['Chia sẻ vội vàng', 'Không nên'], ['Tin ngay lập tức', 'Không nên']],
                'Nên kiểm chứng đa nguồn; không nên vội tin, vội chia sẻ.', $d);
            $this->sortQ($L, 'Kéo mỗi dấu hiệu vào nhóm TIN GIẢ hoặc TIN THẬT.',
                [['Không rõ tác giả', 'Tin giả'], ['Ảnh cắt ghép', 'Tin giả'],
                 ['Có nguồn uy tín', 'Tin thật'], ['Ngày tháng rõ ràng', 'Tin thật']],
                'Vô danh, ảnh ghép là tin giả; nguồn uy tín là tin thật.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm BƯỚC KIỂM CHỨNG hoặc KHÔNG NÊN LÀM.',
                [['Xem nguồn đăng tin', 'Bước kiểm chứng'], ['So sánh nhiều nguồn', 'Bước kiểm chứng'], ['Hỏi thầy cô', 'Bước kiểm chứng'],
                 ['Chia sẻ ngay lập tức', 'Không nên làm'], ['Bình luận kích động', 'Không nên làm'], ['Tin theo cảm tính', 'Không nên làm']],
                'Kiểm chứng: xem nguồn, so sánh, hỏi người có hiểu biết.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tin giả thường có tiêu đề ___ gân.', [[0, 'giật']],
                'Tiêu đề giật gân nhằm câu lượt xem, lượt chia sẻ.', $d);
            $this->fill($L, 'Hãy kiểm ___ thông tin trước khi chia sẻ.', [[0, 'chứng']],
                'Kiểm chứng là thói quen của người dùng mạng thông minh.', $d);
            $this->fill($L, 'Nguồn tin đáng tin là báo chí ___ thống.', [[0, 'chính']],
                'Báo chí chính thống có trách nhiệm kiểm chứng thông tin.', $d);
            $this->fill($L, 'Không ___ sẻ tin chưa kiểm chứng.', [[0, 'chia']],
                'Chia sẻ tin giả có thể gây hoang mang và bị xử phạt.', $d);
        }
    }

    private function seedGdAnToanMangL91(): void
    {
        $L = 'gd-an-toan-mang-lop-9-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Chúc mừng bạn trúng thưởng, bấm link nhận quà" là gì?',
                ['Lừa đảo', 'Thật 100%', 'May mắn', 'Quảng cáo thật'], 0,
                'Trúng thưởng qua tin nhắn lạ, yêu cầu bấm link hầu hết là lừa đảo.', $d);
            $this->quiz($L, 'Có người hỏi mã OTP của em, em nên làm gì?',
                ['Tuyệt đối không cho', 'Cho ngay', 'Cho một nửa', 'Hỏi xin quà đã'], 0,
                'Mã OTP là chìa khóa tài khoản, không bao giờ cung cấp cho ai.', $d);
            $this->quiz($L, '"Việc nhẹ lương cao, chỉ cần chuyển khoản đặt cọc trước" là dấu hiệu gì?',
                ['Lừa đảo', 'Cơ hội tốt', 'Việc làm thật', 'Sự giúp đỡ'], 0,
                'Việc làm thật không bao giờ yêu cầu nộp tiền trước.', $d);
            $this->quiz($L, 'Khi nghi ngờ bị lừa đảo, em nên làm gì?',
                ['Chặn, báo cáo và kể với người lớn', 'Chuyển tiền thử xem sao', 'Bấm link xem thử', 'Im lặng cho qua'], 0,
                'Chặn, báo cáo tài khoản lừa đảo và kể ngay với người lớn.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chiêu trò với dấu hiệu của nó.',
                [['Trúng thưởng', 'Bấm link nhận quà'],
                 ['Giả danh công an', 'Đe dọa chuyển tiền'],
                 ['Việc nhẹ lương cao', 'Nộp phí trước'],
                 ['Giả người thân', 'Vay tiền gấp qua mạng']],
                'Nhận diện chiêu trò là bước đầu tự bảo vệ.', $d);
            $this->matching($L, 'Nối mỗi yêu cầu với việc có nên cung cấp không.',
                [['Mã OTP', 'Không bao giờ'],
                 ['Mật khẩu tài khoản', 'Không bao giờ'],
                 ['Địa chỉ nhà', 'Không'],
                 ['Sở thích', 'Được']],
                'OTP và mật khẩu tuyệt đối không cung cấp cho bất kỳ ai.', $d);
            $this->matching($L, 'Nối mỗi tình huống với cách xử lý đúng.',
                [['Nhận link lạ', 'Không bấm'],
                 ['Bị dọa dẫm', 'Kể với người lớn'],
                 ['Đã chuyển tiền cho kẻ lừa', 'Báo công an'],
                 ['Thấy quảng cáo lừa đảo', 'Báo cáo']],
                'Không bấm link lạ; bị hại thì giữ bằng chứng và báo cơ quan chức năng.', $d);
            $this->matching($L, 'Nối mỗi kênh với trường hợp cần dùng.',
                [['Nút báo cáo', 'Tin giả, lừa đảo'],
                 ['Tổng đài 111', 'Trẻ em bị hại'],
                 ['Công an 113', 'Bị lừa mất tiền'],
                 ['Cha mẹ', 'Mọi điều nghi ngờ']],
                'Mỗi kênh hỗ trợ có chức năng riêng, cần gọi đúng nơi.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tin nhắn vào nhóm LỪA ĐẢO hoặc BÌNH THƯỜNG.',
                [['Trúng thưởng bấm link', 'Lừa đảo'], ['Xin mã OTP', 'Lừa đảo'],
                 ['Thông báo từ ngân hàng chính thức', 'Bình thường'], ['Bạn bè rủ học nhóm', 'Bình thường']],
                'Xin OTP, trúng thưởng bấm link là lừa đảo.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Không bấm link lạ', 'Nên'], ['Không cho ai mã OTP', 'Nên'], ['Kể với cha mẹ', 'Nên'],
                 ['Chuyển tiền cho người lạ', 'Không nên']],
                'Không bấm link lạ, không cho OTP, kể với cha mẹ.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm AN TOÀN hoặc RỦI RO.',
                [['Xác minh qua kênh chính thức', 'An toàn'], ['Chặn kẻ lừa đảo', 'An toàn'],
                 ['Tin lời người lạ', 'Rủi ro'], ['Cung cấp thông tin cá nhân', 'Rủi ro']],
                'Xác minh qua kênh chính thức luôn an toàn hơn tin người lạ.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NGUYÊN TẮC PHÒNG TRÁNH hoặc SAI LẦM.',
                [['Không bấm link lạ', 'Nguyên tắc'], ['Không cho mã OTP', 'Nguyên tắc'], ['Không chuyển tiền cho người lạ', 'Nguyên tắc'],
                 ['Bấm thử xem sao', 'Sai lầm'], ['Cho OTP vì thấy "gấp"', 'Sai lầm'], ['Chuyển khoản đặt cọc', 'Sai lầm']],
                'Ba nguyên tắc vàng: không link lạ, không OTP, không chuyển tiền.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tuyệt đối không cung cấp mã ___ cho người khác.', [[0, 'OTP']],
                'Mã OTP là lớp bảo vệ cuối cùng của tài khoản.', $d);
            $this->fill($L, '"Trúng thưởng, bấm ___ nhận quà" thường là lừa đảo.', [[0, 'link']],
                'Link lạ có thể đánh cắp thông tin hoặc cài mã độc.', $d);
            $this->fill($L, '"Việc nhẹ lương cao" yêu cầu nộp phí trước là dấu hiệu ___ đảo.', [[0, 'lừa']],
                'Việc làm thật không bao giờ thu phí người xin việc.', $d);
            $this->fill($L, 'Nghi bị lừa, hãy chặn và ___ cáo tài khoản đó.', [[0, 'báo']],
                'Báo cáo giúp ngăn kẻ lừa đảo hại thêm người khác.', $d);
        }
    }

    private function seedGdAnToanMangL92(): void
    {
        $L = 'gd-an-toan-mang-lop-9-2';
        $d = 'kho';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hành vi nào bị pháp luật về môi trường nghiêm cấm?',
                ['Xả thải chưa xử lý ra sông', 'Trồng cây xanh', 'Phân loại rác', 'Tiết kiệm nước'], 0,
                'Xả thải chưa qua xử lý gây ô nhiễm nghiêm trọng và bị xử phạt.', $d);
            $this->quiz($L, 'Biến đổi khí hậu gây ra hiện tượng nào?',
                ['Lũ lụt và hạn hán', 'Thời tiết ổn định', 'Mưa thuận gió hòa', 'Không ảnh hưởng gì'], 0,
                'Biến đổi khí hậu làm thời tiết cực đoan: lũ lụt, hạn hán, nước biển dâng.', $d);
            $this->quiz($L, 'Để giảm rác thải nhựa, em nên làm gì?',
                ['Mang túi vải đi chợ', 'Dùng nhiều túi nilon', 'Vứt chai nhựa bừa bãi', 'Đốt rác nhựa'], 0,
                'Túi vải dùng nhiều lần giúp giảm đáng kể rác thải nhựa.', $d);
            $this->quiz($L, 'Tiết kiệm điện là trách nhiệm của ai?',
                ['Của mọi người', 'Chỉ của nhà nước', 'Không cần thiết', 'Chỉ khi mất điện'], 0,
                'Mỗi người tiết kiệm một chút, cả xã hội tiết kiệm rất nhiều.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hành vi với đánh giá tốt hay xấu cho môi trường.',
                [['Phân loại rác', 'Tốt'],
                 ['Trồng cây xanh', 'Tốt'],
                 ['Xả rác bừa bãi', 'Xấu'],
                 ['Chặt phá rừng', 'Xấu']],
                'Hành vi tốt bảo vệ môi trường, hành vi xấu phá hoại môi trường.', $d);
            $this->matching($L, 'Nối mỗi việc làm với thứ nó giúp tiết kiệm.',
                [['Tắt điện khi ra khỏi phòng', 'Tiết kiệm điện'],
                 ['Khóa vòi nước khi đánh răng', 'Tiết kiệm nước'],
                 ['Dùng túi vải đi chợ', 'Giảm túi nilon'],
                 ['Đi xe đạp quãng gần', 'Giảm khí thải']],
                'Những việc nhỏ hằng ngày góp phần bảo vệ môi trường.', $d);
            $this->matching($L, 'Nối mỗi hiện tượng với nguyên nhân của nó.',
                [['Nước biển dâng', 'Biến đổi khí hậu'],
                 ['Lũ lụt', 'Mưa lớn, rừng bị chặt phá'],
                 ['Hạn hán', 'Thiếu mưa kéo dài'],
                 ['Ô nhiễm không khí', 'Khói bụi, khí thải']],
                'Hiểu nguyên nhân để có hành động bảo vệ đúng.', $d);
            $this->matching($L, 'Nối mỗi đối tượng với trách nhiệm bảo vệ môi trường của họ.',
                [['Học sinh', 'Giữ trường lớp sạch đẹp'],
                 ['Gia đình', 'Phân loại rác tại nhà'],
                 ['Doanh nghiệp', 'Xử lý chất thải đúng quy định'],
                 ['Nhà nước', 'Ban hành và thực thi luật']],
                'Bảo vệ môi trường là trách nhiệm chung của toàn xã hội.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm BẢO VỆ hoặc PHÁ HOẠI môi trường.',
                [['Trồng cây', 'Bảo vệ'], ['Tiết kiệm nước', 'Bảo vệ'],
                 ['Xả rác bừa bãi', 'Phá hoại'], ['Chặt phá rừng', 'Phá hoại']],
                'Trồng cây, tiết kiệm là bảo vệ; xả rác, chặt rừng là phá hoại.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Phân loại rác', 'Nên'], ['Dùng túi vải', 'Nên'],
                 ['Vứt pin bừa bãi', 'Không nên'], ['Đốt rác thải nhựa', 'Không nên']],
                'Nên phân loại rác, dùng túi vải; không vứt pin, không đốt nhựa.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm TIẾT KIỆM ĐIỆN hoặc TIẾT KIỆM NƯỚC.',
                [['Tắt đèn khi ra ngoài', 'Tiết kiệm điện'], ['Rút sạc khi đầy pin', 'Tiết kiệm điện'],
                 ['Khóa vòi khi đánh răng', 'Tiết kiệm nước'], ['Tưới cây bằng nước vo gạo', 'Tiết kiệm nước']],
                'Tắt thiết bị điện tiết kiệm điện; tái sử dụng nước tiết kiệm nước.', $d);
            $this->sortQ($L, 'Kéo mỗi đồ vật vào nhóm THAY THẾ NILON hoặc NÊN HẠN CHẾ.',
                [['Túi vải', 'Thay thế nilon'], ['Hộp cơm cá nhân', 'Thay thế nilon'], ['Bình nước cá nhân', 'Thay thế nilon'],
                 ['Túi nilon dùng một lần', 'Nên hạn chế'], ['Ống hút nhựa', 'Nên hạn chế'], ['Chai nhựa dùng một lần', 'Nên hạn chế']],
                'Dùng đồ tái sử dụng thay đồ nhựa dùng một lần.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hãy ___ loại rác trước khi bỏ.', [[0, 'phân']],
                'Phân loại rác giúp tái chế hiệu quả và giảm ô nhiễm.', $d);
            $this->fill($L, 'Trồng cây xanh giúp bảo vệ ___ trường.', [[0, 'môi']],
                'Cây xanh làm sạch không khí và chống xói mòn.', $d);
            $this->fill($L, 'Hành vi xả thải bẩn ra sông sẽ bị pháp ___ xử lý nghiêm.', [[0, 'luật']],
                'Pháp luật xử lý nghiêm hành vi gây ô nhiễm môi trường.', $d);
            $this->fill($L, 'Tắt điện khi ra khỏi phòng để ___ kiệm điện.', [[0, 'tiết']],
                'Tiết kiệm điện vừa giảm tiền vừa bảo vệ môi trường.', $d);
        }
    }

    // ================= TIN HỌC — PHẦN CỨNG MÁY TÍNH =================

    private function seedThPhanCungL61(): void
    {
        $L = 'th-phan-cung-may-tinh-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bộ phận nào hiển thị hình ảnh của máy tính?',
                ['Màn hình', 'Bàn phím', 'Chuột', 'Loa'], 0,
                'Màn hình hiển thị mọi hình ảnh, chữ viết của máy tính.');
            $this->quiz($L, 'Muốn gõ chữ, ta dùng bộ phận nào?',
                ['Bàn phím', 'Chuột', 'Loa', 'Màn hình'], 0,
                'Bàn phím có các phím chữ, số dùng để nhập văn bản.');
            $this->quiz($L, 'Chuột máy tính dùng để làm gì?',
                ['Điều khiển con trỏ', 'Gõ chữ', 'Phát nhạc', 'Hiển thị ảnh'], 0,
                'Di chuyển chuột để điều khiển con trỏ, nhấp chuột để chọn.');
            $this->quiz($L, 'Laptop khác máy tính để bàn ở điểm nào?',
                ['Gọn nhẹ, gộp trong một khối', 'To hơn nhiều', 'Không có màn hình', 'Không dùng được'], 0,
                'Laptop gộp màn hình, bàn phím, thân máy trong một khối gọn nhẹ, dễ mang theo.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bộ phận với chức năng của nó.',
                [['Màn hình', 'Hiển thị hình ảnh'],
                 ['Bàn phím', 'Nhập chữ và số'],
                 ['Chuột', 'Điều khiển con trỏ'],
                 ['Loa', 'Phát âm thanh']],
                'Mỗi bộ phận có một chức năng riêng trong máy tính.');
            $this->matching($L, 'Nối tên tiếng Anh với tên tiếng Việt của bộ phận.',
                [['Monitor', 'Màn hình'],
                 ['Keyboard', 'Bàn phím'],
                 ['Mouse', 'Chuột'],
                 ['Speaker', 'Loa']],
                'Học tên tiếng Anh giúp em đọc hiểu tài liệu máy tính.');
            $this->matching($L, 'Nối mỗi bộ phận với việc có sẵn trong laptop không.',
                [['Màn hình', 'Có sẵn'],
                 ['Bàn phím', 'Có sẵn'],
                 ['Chuột rời', 'Có thể không có'],
                 ['Thân máy rời', 'Không có']],
                'Laptop gộp sẵn màn hình và bàn phím, không có thân máy rời.');
            $this->matching($L, 'Nối mỗi việc với bộ phận dùng để thực hiện.',
                [['Gõ văn bản', 'Bàn phím'],
                 ['Nhấp chọn', 'Chuột'],
                 ['Xem phim', 'Màn hình'],
                 ['Nghe nhạc', 'Loa']],
                'Chọn đúng bộ phận cho từng việc khi dùng máy tính.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm THIẾT BỊ NHẬP hoặc THIẾT BỊ XUẤT.',
                [['Bàn phím', 'Nhập'], ['Chuột', 'Nhập'],
                 ['Màn hình', 'Xuất'], ['Loa', 'Xuất']],
                'Bàn phím, chuột đưa vào; màn hình, loa đưa ra.');
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm CÓ DÂY hoặc KHÔNG DÂY.',
                [['Chuột có dây', 'Có dây'], ['Bàn phím có dây', 'Có dây'],
                 ['Chuột không dây', 'Không dây'], ['Tai nghe bluetooth', 'Không dây']],
                'Thiết bị không dây kết nối qua bluetooth, gọn gàng hơn.');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm MÁY BÀN hoặc LAPTOP.',
                [['Màn hình rời', 'Máy bàn'], ['Bàn phím rời', 'Máy bàn'],
                 ['Gập được', 'Laptop'], ['Mang đi dễ dàng', 'Laptop']],
                'Máy bàn các bộ phận rời nhau; laptop gộp chung, dễ mang đi.');
            $this->sortQ($L, 'Kéo mỗi linh kiện vào nhóm TRONG THÂN MÁY hoặc NGOÀI THÂN MÁY.',
                [['CPU', 'Trong thân máy'], ['RAM', 'Trong thân máy'], ['Ổ cứng', 'Trong thân máy'],
                 ['Màn hình', 'Ngoài thân máy'], ['Bàn phím', 'Ngoài thân máy'], ['Chuột', 'Ngoài thân máy']],
                'CPU, RAM, ổ cứng nằm trong thân máy; màn hình, bàn phím, chuột ở ngoài.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bộ phận hiển thị hình ảnh gọi là ___ hình.', [[0, 'màn']],
                'Màn hình là nơi hiển thị mọi thứ của máy tính.');
            $this->fill($L, 'Dùng ___ phím để gõ chữ và số.', [[0, 'bàn']],
                'Bàn phím là thiết bị nhập chữ quan trọng nhất.');
            $this->fill($L, '___ dùng để điều khiển con trỏ trên màn hình.', [[0, 'Chuột']],
                'Nhấp chuột để chọn, kéo chuột để di chuyển đồ vật.');
            $this->fill($L, 'Laptop gộp mọi bộ phận trong ___ khối gọn nhẹ.', [[0, 'một']],
                'Nhờ gọn nhẹ mà laptop dễ mang theo bên mình.');
        }
    }

    private function seedThPhanCungL62(): void
    {
        $L = 'th-phan-cung-may-tinh-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thiết bị nào là thiết bị vào (input)?',
                ['Bàn phím', 'Màn hình', 'Loa', 'Máy in'], 0,
                'Bàn phím đưa thông tin vào máy tính nên là thiết bị vào.');
            $this->quiz($L, 'Thiết bị nào là thiết bị ra (output)?',
                ['Máy in', 'Chuột', 'Micro', 'Bàn phím'], 0,
                'Máy in đưa kết quả ra giấy nên là thiết bị ra.');
            $this->quiz($L, 'Webcam dùng để làm gì?',
                ['Thu hình ảnh vào máy', 'In ảnh ra giấy', 'Phát nhạc', 'Hiển thị phim'], 0,
                'Webcam thu hình ảnh đưa vào máy, dùng khi gọi video.');
            $this->quiz($L, 'Micro là thiết bị vào hay thiết bị ra?',
                ['Thiết bị vào', 'Thiết bị ra', 'Vừa vào vừa ra', 'Không phải thiết bị'], 0,
                'Micro thu âm thanh đưa vào máy nên là thiết bị vào.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thiết bị với loại vào hoặc ra.',
                [['Bàn phím', 'Vào'],
                 ['Chuột', 'Vào'],
                 ['Màn hình', 'Ra'],
                 ['Loa', 'Ra']],
                'Vào: bàn phím, chuột. Ra: màn hình, loa.');
            $this->matching($L, 'Nối mỗi thiết bị với công dụng của nó.',
                [['Máy quét', 'Đưa ảnh vào máy'],
                 ['Máy in', 'In ra giấy'],
                 ['Micro', 'Thu âm vào máy'],
                 ['Webcam', 'Thu hình vào máy']],
                'Máy quét, micro, webcam đưa vào; máy in đưa ra.');
            $this->matching($L, 'Nối mỗi việc với thiết bị dùng để làm.',
                [['Gõ chữ', 'Bàn phím'],
                 ['In bài tập', 'Máy in'],
                 ['Nghe nhạc', 'Loa'],
                 ['Gọi video', 'Webcam']],
                'Chọn đúng thiết bị cho từng công việc.');
            $this->matching($L, 'Nối mỗi thiết bị với nhóm của nó.',
                [['Máy quét', 'Thiết bị vào'],
                 ['Máy chiếu', 'Thiết bị ra'],
                 ['Màn hình cảm ứng', 'Vừa vào vừa ra'],
                 ['USB', 'Thiết bị lưu trữ']],
                'Màn hình cảm ứng vừa hiển thị vừa nhận chạm; USB để lưu trữ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm VÀO hoặc RA.',
                [['Bàn phím', 'Vào'], ['Chuột', 'Vào'],
                 ['Màn hình', 'Ra'], ['Máy in', 'Ra']],
                'Nhớ mẹo: "Vào là nhập, Ra là xuất".');
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm VÀO hoặc RA (tiếp).',
                [['Micro', 'Vào'], ['Webcam', 'Vào'],
                 ['Loa', 'Ra'], ['Máy chiếu', 'Ra']],
                'Micro, webcam thu vào; loa, máy chiếu phát ra.');
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm NHẬP DỮ LIỆU hoặc XUẤT KẾT QUẢ.',
                [['Bàn phím', 'Nhập dữ liệu'], ['Máy quét', 'Nhập dữ liệu'],
                 ['Máy in', 'Xuất kết quả'], ['Màn hình', 'Xuất kết quả']],
                'Nhập: bàn phím, máy quét. Xuất: máy in, màn hình.');
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm THIẾT BỊ VÀO hoặc THIẾT BỊ RA.',
                [['Bàn phím', 'Thiết bị vào'], ['Chuột', 'Thiết bị vào'], ['Micro', 'Thiết bị vào'],
                 ['Màn hình', 'Thiết bị ra'], ['Loa', 'Thiết bị ra'], ['Máy in', 'Thiết bị ra']],
                'Ba thiết bị vào, ba thiết bị ra thường gặp nhất.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bàn phím là thiết bị ___.', [[0, 'vào']],
                'Bàn phím đưa thông tin vào máy tính.');
            $this->fill($L, 'Màn hình là thiết bị ___.', [[0, 'ra']],
                'Màn hình đưa thông tin ra cho ta nhìn thấy.');
            $this->fill($L, 'Máy in đưa thông tin ___ khỏi máy tính.', [[0, 'ra']],
                'Máy in là thiết bị ra: in kết quả ra giấy.');
            $this->fill($L, 'Micro thu âm thanh ___ máy tính.', [[0, 'vào']],
                'Micro là thiết bị vào: thu âm thanh vào máy.');
        }
    }

    private function seedThPhanCungL71(): void
    {
        $L = 'th-phan-cung-may-tinh-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Bộ não" của máy tính là linh kiện nào?',
                ['CPU', 'Màn hình', 'Chuột', 'Loa'], 0,
                'CPU thực hiện mọi tính toán, điều khiển nên được ví như bộ não.');
            $this->quiz($L, 'Tắt máy tính thì dữ liệu ở đâu bị mất?',
                ['RAM', 'Ổ cứng', 'SSD', 'USB'], 0,
                'RAM là bộ nhớ tạm, tắt máy là mất dữ liệu trong RAM.');
            $this->quiz($L, 'Loại ổ lưu trữ nào chạy nhanh hơn?',
                ['SSD', 'HDD', 'Đĩa mềm', 'Đĩa CD'], 0,
                'SSD không có đĩa quay nên đọc ghi nhanh và êm hơn HDD.');
            $this->quiz($L, 'Muốn máy chạy nhiều chương trình cùng lúc mượt mà cần gì?',
                ['RAM dung lượng lớn', 'Màn hình thật to', 'Loa thật hay', 'Chuột thật đẹp'], 0,
                'RAM càng lớn, máy càng chạy được nhiều chương trình cùng lúc.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi linh kiện với vai trò của nó.',
                [['CPU', 'Xử lý, tính toán'],
                 ['RAM', 'Nhớ tạm khi đang chạy'],
                 ['SSD', 'Lưu trữ nhanh'],
                 ['HDD', 'Lưu trữ nhiều, giá rẻ']],
                'CPU xử lý, RAM nhớ tạm, SSD/HDD lưu lâu dài.');
            $this->matching($L, 'Nối tên đầy đủ với tên viết tắt.',
                [['Central Processing Unit', 'CPU'],
                 ['Random Access Memory', 'RAM'],
                 ['Solid State Drive', 'SSD'],
                 ['Hard Disk Drive', 'HDD']],
                'Học tên tiếng Anh của các linh kiện máy tính.');
            $this->matching($L, 'Nối mỗi đặc điểm với linh kiện tương ứng.',
                [['Bộ não máy tính', 'CPU'],
                 ['Mất dữ liệu khi tắt máy', 'RAM'],
                 ['Nhanh, chạy êm', 'SSD'],
                 ['Rẻ, dung lượng lớn', 'HDD']],
                'Mỗi linh kiện có đặc điểm riêng không lẫn vào đâu.');
            $this->matching($L, 'Nối mỗi đơn vị với thứ nó dùng để đo.',
                [['GHz', 'Tốc độ xử lý'],
                 ['GB', 'Dung lượng nhớ'],
                 ['Inch', 'Kích thước màn hình'],
                 ['Dpi', 'Độ phân giải']],
                'GHz đo tốc độ CPU, GB đo dung lượng, inch đo màn hình.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi linh kiện vào nhóm XỬ LÝ, NHỚ TẠM hoặc LƯU LÂU DÀI.',
                [['CPU', 'Xử lý'],
                 ['RAM', 'Nhớ tạm'],
                 ['SSD', 'Lưu lâu dài'], ['HDD', 'Lưu lâu dài']],
                'CPU xử lý, RAM nhớ tạm, SSD và HDD lưu lâu dài.');
            $this->sortQ($L, 'Kéo mỗi thiết bị lưu trữ vào nhóm NHANH hoặc CHẬM HƠN.',
                [['SSD', 'Nhanh'], ['RAM', 'Nhanh'],
                 ['HDD', 'Chậm hơn'], ['Đĩa CD', 'Chậm hơn']],
                'SSD và RAM rất nhanh; HDD và đĩa CD chậm hơn.');
            $this->sortQ($L, 'Kéo mỗi nơi lưu vào nhóm MẤT KHI TẮT MÁY hoặc CÒN KHI TẮT MÁY.',
                [['RAM', 'Mất khi tắt máy'], ['Dữ liệu CPU đang tính', 'Mất khi tắt máy'],
                 ['SSD', 'Còn khi tắt máy'], ['USB', 'Còn khi tắt máy']],
                'Nhớ lưu bài vào ổ cứng, SSD hoặc USB trước khi tắt máy.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm VIỆC CỦA CPU, CỦA RAM hoặc CỦA Ổ CỨNG.',
                [['Tính toán', 'Việc của CPU'], ['Điều khiển chương trình', 'Việc của CPU'],
                 ['Nhớ tạm chương trình đang chạy', 'Việc của RAM'], ['Chạy mượt khi RAM lớn', 'Việc của RAM'],
                 ['Lưu file văn bản', 'Việc của ổ cứng'], ['Lưu ảnh, nhạc', 'Việc của ổ cứng']],
                'CPU tính toán, RAM nhớ tạm, ổ cứng lưu trữ lâu dài.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'CPU được ví như "bộ ___" của máy tính.', [[0, 'não']],
                'CPU điều khiển và tính toán mọi thứ như bộ não.');
            $this->fill($L, 'RAM là bộ nhớ ___, tắt máy sẽ mất dữ liệu.', [[0, 'tạm']],
                'Vì là bộ nhớ tạm nên phải lưu bài trước khi tắt máy.');
            $this->fill($L, '___ chạy nhanh hơn ổ cứng HDD truyền thống.', [[0, 'SSD']],
                'SSD không có bộ phận quay nên nhanh và bền hơn.');
            $this->fill($L, 'Tốc độ CPU thường được đo bằng đơn vị ___.', [[0, 'GHz']],
                'GHz càng cao, CPU xử lý càng nhanh.');
        }
    }

    private function seedThPhanCungL72(): void
    {
        $L = 'th-phan-cung-may-tinh-lop-7-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tắt máy tính đúng cách là làm thế nào?',
                ['Chọn Start rồi Shut down', 'Rút phích điện ngay', 'Nhấn giữ nút nguồn', 'Gập máy lại'], 0,
                'Tắt đúng cách giúp máy lưu dữ liệu và bền hơn.', $d);
            $this->quiz($L, 'Không nên làm gì gần máy tính?',
                ['Ăn uống', 'Gõ phím', 'Nhìn màn hình', 'Nghe nhạc'], 0,
                'Đồ ăn, nước uống đổ vào máy gây chập và hỏng bàn phím.', $d);
            $this->quiz($L, 'Máy tính nên đặt ở nơi như thế nào?',
                ['Khô ráo, thoáng mát', 'Ẩm ướt', 'Ngoài nắng gắt', 'Gần bồn nước'], 0,
                'Nơi khô ráo thoáng mát giúp máy tản nhiệt tốt, bền hơn.', $d);
            $this->quiz($L, 'Khi điện chập chờn, nên dùng gì để bảo vệ máy?',
                ['Ổn áp', 'Dây điện thật dài', 'Nhiều ổ cắm', 'Bỏ qua không sao'], 0,
                'Ổn áp giữ điện áp ổn định, bảo vệ máy khỏi cháy linh kiện.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi việc làm với đánh giá đúng hay sai.',
                [['Tắt máy đúng cách', 'Đúng'],
                 ['Lau màn hình bằng khăn mềm', 'Đúng'],
                 ['Rút điện đột ngột', 'Sai'],
                 ['Ăn mì gần bàn phím', 'Sai']],
                'Tắt đúng cách, lau khăn mềm là đúng; rút đột ngột, ăn gần máy là sai.', $d);
            $this->matching($L, 'Nối mỗi sự cố với cách xử lý phù hợp.',
                [['Máy nóng', 'Để nơi thoáng mát'],
                 ['Dính nước vào máy', 'Tắt máy, gọi người lớn'],
                 ['Màn hình bám bụi', 'Lau bằng khăn mềm'],
                 ['Điện chập chờn', 'Dùng ổn áp']],
                'Mỗi sự cố có cách xử lý riêng, không tự ý tháo máy.', $d);
            $this->matching($L, 'Nối mỗi bộ phận với cách giữ gìn nó.',
                [['Màn hình', 'Tránh va đập'],
                 ['Bàn phím', 'Tránh nước đổ vào'],
                 ['Chuột', 'Dùng nhẹ tay'],
                 ['Dây điện', 'Không gấp gãy']],
                'Giữ gìn từng bộ phận giúp máy bền và đẹp lâu.', $d);
            $this->matching($L, 'Nối mỗi thói quen với đánh giá tốt hay xấu.',
                [['Tắt máy khi không dùng', 'Tốt'],
                 ['Cập nhật phần mềm', 'Tốt'],
                 ['Để máy chạy cả đêm', 'Xấu'],
                 ['Tự tháo linh kiện', 'Xấu']],
                'Thói quen tốt giúp máy bền; thói quen xấu làm máy nhanh hỏng.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Tắt máy đúng cách', 'Nên'], ['Để máy nơi khô ráo', 'Nên'],
                 ['Ăn uống gần máy', 'Không nên'], ['Rút điện đột ngột', 'Không nên']],
                'Nên tắt đúng cách, để nơi khô ráo; không ăn gần máy, không rút đột ngột.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm TỐT hoặc XẤU cho máy tính.',
                [['Lau bụi định kỳ', 'Tốt'], ['Dùng ổn áp', 'Tốt'],
                 ['Để nước gần máy', 'Xấu'], ['Va đập mạnh', 'Xấu']],
                'Lau bụi, dùng ổn áp thì tốt; nước và va đập thì xấu.', $d);
            $this->sortQ($L, 'Kéo mỗi sự cố vào nhóm cách xử lý đúng.',
                [['Máy chạy chậm', 'Khởi động lại'], ['Máy bị treo', 'Khởi động lại'],
                 ['Dính nước vào máy', 'Tắt ngay, gọi người lớn'], ['Có mùi khét', 'Tắt ngay, gọi người lớn']],
                'Chậm, treo thì khởi động lại; dính nước, mùi khét thì tắt ngay gọi người lớn.', $d);
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm DỤNG CỤ VỆ SINH, NGUY HIỂM hoặc NÊN LÀM.',
                [['Khăn mềm khô', 'Dụng cụ vệ sinh'], ['Chổi quét bụi', 'Dụng cụ vệ sinh'],
                 ['Nước đổ vào máy', 'Nguy hiểm'], ['Va đập mạnh', 'Nguy hiểm'],
                 ['Để nơi khô thoáng', 'Nên làm'], ['Tắt máy khi không dùng', 'Nên làm']],
                'Vệ sinh bằng khăn mềm; tránh nước và va đập; để nơi khô thoáng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tắt máy đúng cách: Start rồi chọn Shut ___.', [[0, 'down']],
                'Shut down là lệnh tắt máy đúng cách trong Windows.', $d);
            $this->fill($L, 'Không ___ uống gần máy tính.', [[0, 'ăn']],
                'Đồ ăn thức uống dễ đổ vào máy gây hỏng hóc.', $d);
            $this->fill($L, 'Để máy nơi khô ráo, ___ mát.', [[0, 'thoáng']],
                'Nơi thoáng mát giúp máy tản nhiệt tốt.', $d);
            $this->fill($L, 'Khi điện chập chờn nên dùng ___ áp.', [[0, 'ổn']],
                'Ổn áp bảo vệ máy khỏi điện áp không ổn định.', $d);
        }
    }

    // ================= TIN HỌC — PHẦN MỀM, TỆP, THƯ MỤC =================

    private function seedThPhanMemL71(): void
    {
        $L = 'th-phan-mem-tep-thu-muc-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đâu là ví dụ về hệ điều hành?',
                ['Windows', 'Word', 'Chrome', 'Zalo'], 0,
                'Windows là hệ điều hành của máy tính.');
            $this->quiz($L, 'Phần mềm ứng dụng là phần mềm thế nào?',
                ['Phục vụ nhu cầu người dùng', 'Điều khiển phần cứng', 'Khởi động máy tính', 'Quản lý tệp tin'], 0,
                'Phần mềm ứng dụng phục vụ nhu cầu cụ thể như soạn thảo, giải trí.');
            $this->quiz($L, 'Máy tính cần gì để có thể hoạt động được?',
                ['Hệ điều hành', 'Thật nhiều game', 'Thật nhiều ảnh', 'Thật nhiều nhạc'], 0,
                'Không có hệ điều hành, máy tính không thể hoạt động.');
            $this->quiz($L, 'Word thuộc loại phần mềm nào?',
                ['Ứng dụng', 'Hệ thống', 'Điều khiển', 'Virus'], 0,
                'Word là phần mềm ứng dụng dùng để soạn thảo văn bản.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phần mềm với loại của nó.',
                [['Windows', 'Hệ thống'],
                 ['Android', 'Hệ thống'],
                 ['Word', 'Ứng dụng'],
                 ['Chrome', 'Ứng dụng']],
                'Windows, Android là hệ thống; Word, Chrome là ứng dụng.');
            $this->matching($L, 'Nối mỗi loại phần mềm với chức năng của nó.',
                [['Hệ điều hành', 'Điều khiển phần cứng'],
                 ['Soạn thảo', 'Gõ văn bản'],
                 ['Trình duyệt', 'Xem trang web'],
                 ['Diệt virus', 'Bảo vệ máy tính']],
                'Mỗi loại phần mềm có chức năng riêng.');
            $this->matching($L, 'Nối mỗi phần mềm với công dụng của nó.',
                [['Word', 'Soạn văn bản'],
                 ['Excel', 'Tính toán bảng biểu'],
                 ['PowerPoint', 'Làm trình chiếu'],
                 ['Paint', 'Vẽ hình']],
                'Word soạn văn bản, Excel tính toán, PowerPoint trình chiếu, Paint vẽ.');
            $this->matching($L, 'Nối mỗi thiết bị với hệ điều hành của nó.',
                [['Máy tính cá nhân', 'Windows'],
                 ['Điện thoại Samsung', 'Android'],
                 ['iPhone', 'iOS'],
                 ['Laptop Apple', 'macOS']],
                'Mỗi thiết bị dùng hệ điều hành phù hợp với nó.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm HỆ THỐNG hoặc ỨNG DỤNG.',
                [['Windows', 'Hệ thống'], ['Android', 'Hệ thống'],
                 ['Word', 'Ứng dụng'], ['Game', 'Ứng dụng']],
                'Windows, Android là hệ thống; Word, game là ứng dụng.');
            $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm công dụng của nó.',
                [['Word', 'Soạn văn bản'], ['PowerPoint', 'Trình chiếu'],
                 ['Excel', 'Bảng tính'], ['Chrome', 'Duyệt web']],
                'Chọn đúng phần mềm cho từng công việc.');
            $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm HỆ ĐIỀU HÀNH hoặc KHÔNG PHẢI.',
                [['Windows', 'Hệ điều hành'], ['Android', 'Hệ điều hành'],
                 ['Word', 'Không phải'], ['Zalo', 'Không phải']],
                'Windows và Android là hệ điều hành; Word, Zalo thì không.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm VIỆC CỦA HỆ ĐIỀU HÀNH hoặc CỦA ỨNG DỤNG.',
                [['Khởi động máy', 'Hệ điều hành'], ['Quản lý tệp tin', 'Hệ điều hành'], ['Điều khiển chuột', 'Hệ điều hành'],
                 ['Soạn văn bản', 'Ứng dụng'], ['Chơi game', 'Ứng dụng'], ['Chỉnh sửa ảnh', 'Ứng dụng']],
                'Hệ điều hành lo việc chung của máy; ứng dụng phục vụ nhu cầu cụ thể.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phần mềm điều khiển toàn bộ máy tính gọi là hệ điều ___.', [[0, 'hành']],
                'Hệ điều hành là phần mềm quan trọng nhất của máy tính.');
            $this->fill($L, 'Word là phần mềm ___ dụng.', [[0, 'ứng']],
                'Phần mềm ứng dụng phục vụ nhu cầu cụ thể của người dùng.');
            $this->fill($L, 'Không có hệ điều hành, máy tính không ___ động được.', [[0, 'hoạt']],
                'Hệ điều hành là cầu nối giữa người dùng và phần cứng.');
            $this->fill($L, '___ dùng để soạn thảo văn bản.', [[0, 'Word']],
                'Word là phần mềm soạn thảo văn bản phổ biến nhất.');
        }
    }

    private function seedThPhanMemL72(): void
    {
        $L = 'th-phan-mem-tep-thu-muc-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tệp văn bản Word thường có phần mở rộng nào?',
                ['.docx', '.mp3', '.jpg', '.exe'], 0,
                '.docx là phần mở rộng của tệp văn bản Word.');
            $this->quiz($L, 'Phần mở rộng nào là của tệp ảnh?',
                ['.jpg', '.docx', '.mp3', '.xlsx'], 0,
                '.jpg là một trong những định dạng ảnh phổ biến nhất.');
            $this->quiz($L, 'Muốn xem video, ta mở tệp có phần mở rộng nào?',
                ['.mp4', '.docx', '.xlsx', '.txt'], 0,
                '.mp4 là định dạng video phổ biến.');
            $this->quiz($L, 'Phần mở rộng của tệp nằm ở vị trí nào trong tên tệp?',
                ['Sau dấu chấm', 'Trước tên tệp', 'Ở giữa tên', 'Không có vị trí cố định'], 0,
                'Phần mở rộng nằm sau dấu chấm, cho biết loại tệp.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phần mở rộng với loại tệp của nó.',
                [['.docx', 'Văn bản'],
                 ['.xlsx', 'Bảng tính'],
                 ['.pptx', 'Trình chiếu'],
                 ['.pdf', 'Tài liệu']],
                'Mỗi phần mở rộng tương ứng với một loại tệp.');
            $this->matching($L, 'Nối mỗi phần mở rộng với nội dung của nó.',
                [['.jpg', 'Ảnh'],
                 ['.mp3', 'Nhạc'],
                 ['.mp4', 'Video'],
                 ['.txt', 'Văn bản thuần']],
                '.jpg là ảnh, .mp3 là nhạc, .mp4 là video, .txt là văn bản thuần.');
            $this->matching($L, 'Nối mỗi tệp với phần mềm dùng để mở nó.',
                [['Bài văn.docx', 'Word'],
                 ['Bảng điểm.xlsx', 'Excel'],
                 ['Ảnh.jpg', 'Trình xem ảnh'],
                 ['Nhạc.mp3', 'Trình nghe nhạc']],
                'Mỗi loại tệp cần phần mềm phù hợp để mở.');
            $this->matching($L, 'Nối mỗi phần mở rộng với phần mềm tạo ra nó.',
                [['.docx', 'Word'],
                 ['.xlsx', 'Excel'],
                 ['.pptx', 'PowerPoint'],
                 ['.pdf', 'Trình đọc PDF']],
                'Word tạo .docx, Excel tạo .xlsx, PowerPoint tạo .pptx.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi phần mở rộng vào nhóm VĂN BẢN hoặc ĐA PHƯƠNG TIỆN.',
                [['.docx', 'Văn bản'], ['.pdf', 'Văn bản'],
                 ['.mp3', 'Đa phương tiện'], ['.mp4', 'Đa phương tiện']],
                '.docx, .pdf là văn bản; .mp3, .mp4 là đa phương tiện.');
            $this->sortQ($L, 'Kéo mỗi phần mở rộng vào nhóm ẢNH hoặc KHÔNG PHẢI ẢNH.',
                [['.jpg', 'Ảnh'], ['.png', 'Ảnh'],
                 ['.mp3', 'Không phải ảnh'], ['.docx', 'Không phải ảnh']],
                '.jpg, .png là ảnh; .mp3, .docx thì không.');
            $this->sortQ($L, 'Kéo mỗi phần mở rộng vào nhóm TÀI LIỆU hoặc GIẢI TRÍ.',
                [['.docx', 'Tài liệu'], ['.pdf', 'Tài liệu'],
                 ['.mp3', 'Giải trí'], ['.mp4', 'Giải trí']],
                '.docx, .pdf để học tập; .mp3, .mp4 để giải trí.');
            $this->sortQ($L, 'Kéo mỗi phần mở rộng vào nhóm VĂN BẢN, HÌNH ẢNH hoặc ÂM THANH/VIDEO.',
                [['.docx', 'Văn bản'], ['.txt', 'Văn bản'],
                 ['.jpg', 'Hình ảnh'], ['.png', 'Hình ảnh'],
                 ['.mp3', 'Âm thanh/Video'], ['.mp4', 'Âm thanh/Video']],
                'Nhìn phần mở rộng là biết ngay loại tệp.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tệp Word thường có phần mở rộng là ___.', [[0, '.docx']],
                '.docx là định dạng văn bản của Word.');
            $this->fill($L, 'Ảnh thường có đuôi .jpg hoặc ___.', [[0, '.png']],
                '.jpg và .png là hai định dạng ảnh phổ biến.');
            $this->fill($L, 'Nhạc số thường được lưu dưới dạng ___.', [[0, '.mp3']],
                '.mp3 là định dạng nhạc số phổ biến nhất.');
            $this->fill($L, 'Phần mở rộng nằm sau dấu ___ trong tên tệp.', [[0, 'chấm']],
                'Ví dụ: trong "baitap.docx", ".docx" là phần mở rộng.');
        }
    }

    private function seedThPhanMemL81(): void
    {
        $L = 'th-phan-mem-tep-thu-muc-lop-8-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Muốn tạo thư mục mới, ta chọn lệnh nào?',
                ['New → Folder', 'Delete', 'Rename', 'Copy'], 0,
                'Nhấp chuột phải, chọn New rồi Folder để tạo thư mục mới.', $d);
            $this->quiz($L, 'Tệp đã xóa sẽ đi đâu?',
                ['Thùng rác (Recycle Bin)', 'Mất hẳn ngay', 'Thư mục mới', 'Màn hình nền'], 0,
                'Tệp xóa thường vào thùng rác, có thể khôi phục lại.', $d);
            $this->quiz($L, 'Để sao chép tệp, ta dùng cặp lệnh nào?',
                ['Copy rồi Paste', 'Cut rồi Paste', 'Delete', 'Rename'], 0,
                'Copy sao chép, Paste dán — tệp gốc vẫn còn.', $d);
            $this->quiz($L, 'Nên đặt tên thư mục như thế nào?',
                ['Rõ ràng theo nội dung', 'Tên ngẫu nhiên', 'Toàn ký tự số', 'Để trống'], 0,
                'Tên rõ ràng giúp tìm tệp nhanh chóng sau này.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thao tác với phím tắt của nó.',
                [['Sao chép', 'Ctrl + C'],
                 ['Dán', 'Ctrl + V'],
                 ['Cắt', 'Ctrl + X'],
                 ['Xóa', 'Delete']],
                'Nhớ phím tắt giúp thao tác nhanh hơn rất nhiều.', $d);
            $this->matching($L, 'Nối mỗi lệnh với ý nghĩa của nó.',
                [['Copy', 'Sao chép'],
                 ['Cut', 'Cắt để di chuyển'],
                 ['Rename', 'Đổi tên'],
                 ['Delete', 'Xóa']],
                'Copy giữ bản gốc, Cut di chuyển đi, Rename đổi tên, Delete xóa.', $d);
            $this->matching($L, 'Nối mỗi nhu cầu với cách làm đúng.',
                [['Lưu bài các môn riêng', 'Tạo thư mục từng môn'],
                 ['Đặt tên sai', 'Dùng Rename'],
                 ['Chuyển sang thư mục khác', 'Cut rồi Paste'],
                 ['Xóa nhầm tệp', 'Khôi phục từ thùng rác']],
                'Sắp xếp khoa học giúp quản lý tệp dễ dàng.', $d);
            $this->matching($L, 'Nối mỗi biểu tượng với ý nghĩa của nó.',
                [['Thư mục màu vàng', 'Folder chứa tệp'],
                 ['Thùng rác', 'Chứa tệp đã xóa'],
                 ['Biểu tượng Word', 'Tệp văn bản'],
                 ['Ổ USB', 'Thiết bị lưu trữ']],
                'Nhìn biểu tượng là biết loại tệp và thiết bị.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cặp phím tắt vào đúng THAO TÁC của nó.',
                [['Ctrl + C', 'Sao chép'], ['Ctrl + V', 'Dán'],
                 ['Ctrl + X', 'Cắt'], ['Delete', 'Xóa']],
                'C là Copy, V là dán, X là cắt, Delete là xóa.', $d);
            $this->sortQ($L, 'Kéo mỗi cách sắp xếp vào nhóm HỢP LÝ hoặc CHƯA HỢP LÝ.',
                [['Thư mục "Toán"', 'Hợp lý'], ['Thư mục "Văn"', 'Hợp lý'],
                 ['Dồn hết ra màn hình', 'Chưa hợp lý'], ['Tên "aaaa"', 'Chưa hợp lý']],
                'Phân loại theo môn, đặt tên rõ ràng là hợp lý.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm GIỮ LẠI hoặc CÓ THỂ XÓA.',
                [['Bài tập đang làm', 'Giữ lại'], ['Ảnh kỷ niệm', 'Giữ lại'],
                 ['Tệp trùng lặp', 'Có thể xóa'], ['Tệp rác tạm', 'Có thể xóa']],
                'Giữ tệp quan trọng, xóa tệp trùng lặp và tệp rác.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN ĐẶT TÊN hoặc KHÔNG NÊN.',
                [['Tên rõ ràng', 'Nên'], ['Phân loại theo môn', 'Nên'], ['Sao lưu tệp quan trọng', 'Nên'],
                 ['Dồn hết một chỗ', 'Không nên'], ['Tên toàn ký tự lạ', 'Không nên'], ['Xóa bừa tệp hệ thống', 'Không nên']],
                'Đặt tên rõ ràng, phân loại, sao lưu; không dồn đống, không xóa bừa.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thư mục mới được tạo bằng lệnh New rồi ___.', [[0, 'Folder']],
                'Folder nghĩa là thư mục trong tiếng Anh.', $d);
            $this->fill($L, 'Sao chép dùng phím tắt Ctrl + ___.', [[0, 'C']],
                'C là chữ đầu của Copy (sao chép).', $d);
            $this->fill($L, 'Dán dùng phím tắt Ctrl + ___.', [[0, 'V']],
                'Sau khi Copy hoặc Cut, nhấn Ctrl + V để dán.', $d);
            $this->fill($L, 'Tệp bị xóa sẽ được đưa vào ___ rác.', [[0, 'thùng']],
                'Thùng rác (Recycle Bin) cho phép khôi phục tệp đã xóa nhầm.', $d);
        }
    }

    private function seedThPhanMemL82(): void
    {
        $L = 'th-phan-mem-tep-thu-muc-lop-8-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dùng phần mềm crack (bẻ khóa) là hành vi gì?',
                ['Vi phạm bản quyền', 'Bình thường', 'Được khuyến khích', 'Rất an toàn'], 0,
                'Phần mềm crack là vi phạm bản quyền và dễ chứa virus.', $d);
            $this->quiz($L, 'Phần mềm mã nguồn mở cho phép người dùng làm gì?',
                ['Xem và sửa mã nguồn', 'Giấu mã nguồn', 'Thu phí thật cao', 'Cấm chia sẻ'], 0,
                'Mã nguồn mở: được xem, sửa và chia sẻ mã nguồn tự do.', $d);
            $this->quiz($L, 'Đâu là ví dụ về phần mềm miễn phí, hợp pháp?',
                ['LibreOffice', 'Phần mềm crack', 'Phần mềm lậu', 'Bản bẻ khóa'], 0,
                'LibreOffice là bộ văn phòng miễn phí và mã nguồn mở.', $d);
            $this->quiz($L, 'Mua phần mềm có bản quyền, người dùng được gì?',
                ['Hỗ trợ và cập nhật', 'Virus miễn phí', 'Không ai hỗ trợ', 'Dùng lậu thoải mái'], 0,
                'Bản quyền mang lại cập nhật, hỗ trợ kỹ thuật và sự an tâm.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại phần mềm với đặc điểm của nó.',
                [['Có bản quyền', 'Phải mua giấy phép'],
                 ['Miễn phí', 'Dùng không mất tiền'],
                 ['Mã nguồn mở', 'Được xem, sửa mã nguồn'],
                 ['Dùng lậu', 'Vi phạm pháp luật']],
                'Hiểu các loại giấy phép để dùng phần mềm đúng luật.', $d);
            $this->matching($L, 'Nối mỗi phần mềm với loại của nó.',
                [['Windows bản quyền', 'Có phí'],
                 ['LibreOffice', 'Miễn phí, mã nguồn mở'],
                 ['Chrome', 'Miễn phí'],
                 ['Game crack', 'Lậu']],
                'Chọn phần mềm hợp pháp, tránh phần mềm lậu.', $d);
            $this->matching($L, 'Nối mỗi hành vi với đánh giá đúng hay sai.',
                [['Mua bản quyền', 'Đúng'],
                 ['Dùng phần mềm mã nguồn mở', 'Đúng'],
                 ['Crack để dùng chùa', 'Sai'],
                 ['Bán phần mềm lậu', 'Sai']],
                'Tôn trọng bản quyền là tôn trọng công sức người làm ra.', $d);
            $this->matching($L, 'Nối mỗi lợi ích với việc dùng phần mềm bản quyền.',
                [['Được cập nhật', 'Có'],
                 ['Được hỗ trợ kỹ thuật', 'Có'],
                 ['An toàn hơn', 'Có'],
                 ['Chứa virus', 'Không']],
                'Phần mềm bản quyền an toàn và được hỗ trợ đầy đủ.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm HỢP PHÁP hoặc VI PHẠM.',
                [['Mua bản quyền', 'Hợp pháp'], ['Dùng phần mềm mở', 'Hợp pháp'],
                 ['Dùng crack', 'Vi phạm'], ['Sao chép lậu', 'Vi phạm']],
                'Mua bản quyền, dùng phần mềm mở là hợp pháp.', $d);
            $this->sortQ($L, 'Kéo mỗi phần mềm vào nhóm MẤT PHÍ hoặc MIỄN PHÍ.',
                [['Windows bản quyền', 'Mất phí'], ['Office 365', 'Mất phí'],
                 ['LibreOffice', 'Miễn phí'], ['Chrome', 'Miễn phí']],
                'Có phần mềm mất phí, có phần mềm miễn phí hợp pháp.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Dùng phần mềm miễn phí', 'Nên'], ['Mua bản quyền', 'Nên'],
                 ['Tải bản crack', 'Không nên'], ['Chia sẻ key lậu', 'Không nên']],
                'Nên dùng phần mềm hợp pháp; không tải crack, không chia sẻ key lậu.', $d);
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào đúng LOẠI PHẦN MỀM của nó.',
                [['Trả tiền mua', 'Thương mại'], ['Cần giấy phép', 'Thương mại'],
                 ['Dùng không mất tiền', 'Miễn phí'], ['LibreOffice', 'Miễn phí'],
                 ['Được xem mã nguồn', 'Mã nguồn mở'], ['Được sửa và chia sẻ', 'Mã nguồn mở']],
                'Thương mại trả phí, miễn phí không mất tiền, mã nguồn mở được sửa.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dùng phần mềm crack là vi phạm ___ quyền.', [[0, 'bản']],
                'Bản quyền bảo vệ công sức của người làm phần mềm.', $d);
            $this->fill($L, 'Phần mềm ___ phí được dùng mà không mất tiền.', [[0, 'miễn']],
                'Nhiều phần mềm miễn phí chất lượng tốt như LibreOffice.', $d);
            $this->fill($L, 'Mã nguồn ___ cho phép xem và sửa mã nguồn.', [[0, 'mở']],
                'Mã nguồn mở khuyến khích cộng đồng cùng cải tiến.', $d);
            $this->fill($L, 'Nên mua phần mềm bản quyền để được hỗ trợ và ___ nhật.', [[0, 'cập']],
                'Cập nhật giúp phần mềm an toàn và có tính năng mới.', $d);
        }
    }

    // ================= TIN HỌC — SỬ DỤNG MÁY TÍNH AN TOÀN =================

    private function seedThSuDungAnToanL81(): void
    {
        $L = 'th-su-dung-an-toan-lop-8-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Mật khẩu mạnh cần có đặc điểm gì?',
                ['Đủ 4 loại ký tự', 'Chỉ chữ thường', 'Là ngày sinh', 'Là tên mình'], 0,
                'Mật khẩu mạnh gồm chữ hoa, chữ thường, số và ký tự đặc biệt.', $d);
            $this->quiz($L, 'Có nên dùng một mật khẩu cho mọi tài khoản không?',
                ['Không', 'Có', 'Tùy hứng', 'Nên'], 0,
                'Mỗi tài khoản một mật khẩu riêng để lộ một nơi không mất tất cả.', $d);
            $this->quiz($L, 'Xác thực hai bước (2FA) giúp gì cho tài khoản?',
                ['Bảo vệ thêm một lớp', 'Đăng nhập nhanh hơn', 'Khỏi cần nhớ mật khẩu', 'Chia sẻ dễ hơn'], 0,
                '2FA yêu cầu thêm mã xác nhận nên kẻ xấu khó đăng nhập dù biết mật khẩu.', $d);
            $this->quiz($L, 'Dùng xong máy tính chung ở trường, em phải làm gì?',
                ['Đăng xuất tài khoản', 'Để nguyên đó', 'Cho bạn dùng tiếp', 'Chỉ tắt màn hình'], 0,
                'Đăng xuất để người khác không vào được tài khoản của mình.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi mật khẩu với đánh giá mạnh hay yếu.',
                [['P@ssw0rd!', 'Mạnh'],
                 ['Tr@ns0ng2026', 'Mạnh'],
                 ['12345678', 'Yếu'],
                 ['qwerty', 'Yếu']],
                'Mật khẩu phức tạp, khó đoán là mạnh.', $d);
            $this->matching($L, 'Nối mỗi thói quen với đánh giá tốt hay xấu.',
                [['Mỗi tài khoản một mật khẩu', 'Tốt'],
                 ['Bật xác thực hai bước', 'Tốt'],
                 ['Ghi mật khẩu dán lên máy', 'Xấu'],
                 ['Cho bạn mượn tài khoản', 'Xấu']],
                'Thói quen tốt bảo vệ tài khoản, thói quen xấu gây nguy hiểm.', $d);
            $this->matching($L, 'Nối mỗi tình huống với cách xử lý đúng.',
                [['Nghi lộ mật khẩu', 'Đổi ngay'],
                 ['Quên mật khẩu', 'Dùng chức năng "Quên mật khẩu"'],
                 ['Dùng máy tính chung', 'Đăng xuất khi xong'],
                 ['Email lạ xin mật khẩu', 'Không cho']],
                'Mỗi tình huống có cách xử lý an toàn riêng.', $d);
            $this->matching($L, 'Nối mỗi yếu tố với vai trò trong mật khẩu mạnh.',
                [['Độ dài từ 8 ký tự', 'Cần có'],
                 ['Ký tự đặc biệt', 'Cần có'],
                 ['Thông tin cá nhân', 'Tránh dùng'],
                 ['Dùng chung nhiều nơi', 'Tránh']],
                'Mật khẩu mạnh: dài, phức tạp, không phải thông tin cá nhân.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi mật khẩu vào nhóm MẠNH hoặc YẾU.',
                [['aB3$kL9!', 'Mạnh'], ['Xy7#pQ2@', 'Mạnh'],
                 ['123456', 'Yếu'], ['matkhau', 'Yếu']],
                'Phức tạp khó đoán là mạnh; đơn giản dễ đoán là yếu.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Bật xác thực hai bước', 'Nên'], ['Đăng xuất máy dùng chung', 'Nên'],
                 ['Chia sẻ mật khẩu', 'Không nên'], ['Dùng ngày sinh làm mật khẩu', 'Không nên']],
                'Nên bật 2FA, đăng xuất máy chung; không chia sẻ mật khẩu.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Đổi mật khẩu định kỳ', 'An toàn'], ['Cảnh giác email lạ', 'An toàn'],
                 ['Cho bạn mượn nick', 'Nguy hiểm'], ['Bấm link xin mật khẩu', 'Nguy hiểm']],
                'Cảnh giác và đổi mật khẩu thì an toàn; cho mượn, bấm link lạ thì nguy hiểm.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm MẬT KHẨU MẠNH, MẬT KHẨU YẾU hoặc BẢO VỆ THÊM.',
                [['Dài 12 ký tự', 'Mật khẩu mạnh'], ['Đủ 4 loại ký tự', 'Mật khẩu mạnh'],
                 ['123456', 'Mật khẩu yếu'], ['Tên của mình', 'Mật khẩu yếu'],
                 ['Bật xác thực hai bước', 'Bảo vệ thêm'], ['Đăng xuất máy dùng chung', 'Bảo vệ thêm']],
                'Mật khẩu mạnh cộng thêm các biện pháp bảo vệ thì tài khoản rất an toàn.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mật khẩu mạnh nên dài ít nhất ___ ký tự.', [[0, '8']],
                'Mật khẩu càng dài càng khó bị bẻ khóa.', $d);
            $this->fill($L, 'Không dùng ngày sinh hay ___ mình làm mật khẩu.', [[0, 'tên']],
                'Thông tin cá nhân rất dễ bị đoán ra.', $d);
            $this->fill($L, 'Mỗi tài khoản nên dùng mật khẩu ___ nhau.', [[0, 'khác']],
                'Mật khẩu khác nhau để lộ một tài khoản không mất tất cả.', $d);
            $this->fill($L, 'Nên bật xác thực ___ bước cho tài khoản quan trọng.', [[0, '2']],
                'Xác thực hai bước là lớp bảo vệ thứ hai cho tài khoản.', $d);
        }
    }

    private function seedThSuDungAnToanL82(): void
    {
        $L = 'th-su-dung-an-toan-lop-8-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Virus máy tính có thể lây qua đâu?',
                ['USB lạ', 'Màn hình sạch', 'Bàn phím mới', 'Loa ngoài'], 0,
                'USB lạ là con đường lây virus phổ biến nhất.', $d);
            $this->quiz($L, 'Dấu hiệu nào cho thấy máy tính có thể nhiễm virus?',
                ['Chạy chậm bất thường', 'Chạy nhanh hơn', 'Máy mát hơn', 'Pin dùng lâu hơn'], 0,
                'Máy đột nhiên chậm bất thường là dấu hiệu nghi nhiễm virus.', $d);
            $this->quiz($L, 'Nhận email lạ có tệp đính kèm, em nên làm gì?',
                ['Không mở, xóa ngay', 'Mở ngay xem thử', 'Tải về máy', 'Chuyển tiếp cho bạn'], 0,
                'Tệp đính kèm trong email lạ rất có thể chứa virus.', $d);
            $this->quiz($L, 'Phần mềm diệt virus cần được làm gì thường xuyên?',
                ['Cập nhật', 'Tắt đi', 'Xóa bỏ', 'Không cần làm gì'], 0,
                'Cập nhật thường xuyên để nhận diện được virus mới.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nguồn lây với ví dụ của nó.',
                [['USB lạ', 'Cắm USB không rõ nguồn gốc'],
                 ['Email lạ', 'Tệp đính kèm đáng ngờ'],
                 ['Web lậu', 'Link tải không rõ ràng'],
                 ['Phần mềm crack', 'Bản bẻ khóa chứa mã độc']],
                'Virus lây qua USB lạ, email lạ, web lậu và phần mềm crack.', $d);
            $this->matching($L, 'Nối mỗi dấu hiệu với việc có phải nhiễm virus không.',
                [['Máy chạy chậm', 'Có thể'],
                 ['File lạ tự xuất hiện', 'Có thể'],
                 ['Quảng cáo bật liên tục', 'Có thể'],
                 ['Máy chạy mát mẻ', 'Không']],
                'Chậm bất thường, file lạ, quảng cáo liên tục là dấu hiệu nhiễm virus.', $d);
            $this->matching($L, 'Nối mỗi việc làm với đánh giá phòng tránh virus.',
                [['Quét USB trước khi mở', 'Nên'],
                 ['Cập nhật phần mềm diệt virus', 'Nên'],
                 ['Sao lưu dữ liệu quan trọng', 'Nên'],
                 ['Mở tệp đính kèm lạ', 'Không nên']],
                'Phòng tránh: quét USB, cập nhật diệt virus, sao lưu dữ liệu.', $d);
            $this->matching($L, 'Nối mỗi loại mã độc với đặc điểm của nó.',
                [['Virus', 'Lây lan qua tệp tin'],
                 ['Worm', 'Tự lan qua mạng'],
                 ['Trojan', 'Giả dạng phần mềm tốt'],
                 ['Ransomware', 'Mã hóa dữ liệu đòi tiền']],
                'Mỗi loại mã độc có cách lây lan và gây hại riêng.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm NGUY CƠ hoặc AN TOÀN.',
                [['USB lạ', 'Nguy cơ'], ['Link lạ', 'Nguy cơ'],
                 ['Website chính thức', 'An toàn'], ['Phần mềm bản quyền', 'An toàn']],
                'USB lạ, link lạ là nguy cơ; nguồn chính thức, bản quyền thì an toàn.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Quét virus cho USB', 'Nên'], ['Cập nhật phần mềm', 'Nên'],
                 ['Mở tệp đính kèm lạ', 'Không nên'], ['Tắt phần mềm diệt virus', 'Không nên']],
                'Nên quét USB và cập nhật; không mở tệp lạ, không tắt diệt virus.', $d);
            $this->sortQ($L, 'Kéo mỗi biểu hiện vào nhóm DẤU HIỆU NHIỄM hoặc BÌNH THƯỜNG.',
                [['Máy chậm đột ngột', 'Dấu hiệu nhiễm'], ['File lạ xuất hiện', 'Dấu hiệu nhiễm'],
                 ['Chạy mượt mà', 'Bình thường'], ['Khởi động nhanh', 'Bình thường']],
                'Chậm đột ngột, file lạ là dấu hiệu nhiễm virus.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm PHÒNG TRÁNH hoặc NGUY HIỂM.',
                [['Không cắm USB lạ', 'Phòng tránh'], ['Không mở tệp lạ', 'Phòng tránh'], ['Cài phần mềm diệt virus', 'Phòng tránh'],
                 ['Mở link trúng thưởng', 'Nguy hiểm'], ['Tải phần mềm crack', 'Nguy hiểm'], ['Tắt phần mềm diệt virus', 'Nguy hiểm']],
                'Phòng tránh bằng thói quen tốt; tránh xa nguồn nguy hiểm.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Virus có thể lây qua ___ lạ cắm vào máy.', [[0, 'USB']],
                'Luôn quét virus cho USB lạ trước khi mở.', $d);
            $this->fill($L, 'Không mở tệp đính kèm trong email ___.', [[0, 'lạ']],
                'Email lạ thường chứa virus trong tệp đính kèm.', $d);
            $this->fill($L, 'Nên cài phần mềm ___ virus cho máy tính.', [[0, 'diệt']],
                'Phần mềm diệt virus là lá chắn bảo vệ máy tính.', $d);
            $this->fill($L, 'Hãy ___ lưu dữ liệu quan trọng thường xuyên.', [[0, 'sao']],
                'Sao lưu giúp không mất dữ liệu khi máy nhiễm virus.', $d);
        }
    }

    private function seedThSuDungAnToanL91(): void
    {
        $L = 'th-su-dung-an-toan-lop-9-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, '"Dấu chân số" là gì?',
                ['Những gì ta để lại trên mạng', 'Dấu chân khi đi bộ', 'Mật khẩu của ta', 'Virus máy tính'], 0,
                'Mọi thứ đăng lên mạng đều lưu lại lâu dài thành dấu chân số.', $d);
            $this->quiz($L, 'Trước khi đăng ảnh có mặt bạn bè, em cần làm gì?',
                ['Xin phép bạn trước', 'Đăng luôn cho nhanh', 'Gắn thẻ tất cả', 'Chỉnh ảnh xấu đi'], 0,
                'Tôn trọng bạn bè: xin phép trước khi đăng ảnh có mặt họ.', $d);
            $this->quiz($L, 'Bình luận ác ý trên mạng có thể dẫn đến gì?',
                ['Vi phạm pháp luật', 'Hoàn toàn vô hại', 'Được mọi người khen', 'Giúp bạn nổi tiếng'], 0,
                'Xúc phạm người khác trên mạng có thể bị xử lý theo pháp luật.', $d);
            $this->quiz($L, 'Nên kiểm tra cài đặt riêng tư của tài khoản khi nào?',
                ['Thường xuyên', 'Không bao giờ', 'Một lần duy nhất', 'Khi mất tài khoản'], 0,
                'Kiểm tra thường xuyên để đảm bảo thông tin được bảo vệ.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hành vi với hậu quả của nó.',
                [['Đăng ảnh nhạy cảm', 'Rất khó xóa bỏ'],
                 ['Bình luận ác ý', 'Có thể bị xử lý'],
                 ['Chia sẻ tin giả', 'Gây hoang mang'],
                 ['Lộ thông tin cá nhân', 'Bị kẻ xấu lợi dụng']],
                'Mỗi hành vi thiếu suy nghĩ trên mạng đều có hậu quả.', $d);
            $this->matching($L, 'Nối mỗi việc với đánh giá nên hay không nên.',
                [['Suy nghĩ trước khi đăng', 'Nên'],
                 ['Kiểm tra cài đặt riêng tư', 'Nên'],
                 ['Đăng ảnh bạn chưa xin phép', 'Không nên'],
                 ['Chửi bới trên mạng', 'Không nên']],
                'Người văn minh suy nghĩ kỹ trước khi đăng bất cứ thứ gì.', $d);
            $this->matching($L, 'Nối mỗi quyền với phạm vi của nó trên mạng.',
                [['Quyền riêng tư', 'Được pháp luật bảo vệ'],
                 ['Quyền bày tỏ', 'Trong khuôn khổ pháp luật'],
                 ['Bị bắt nạt mạng', 'Được bảo vệ, giúp đỡ'],
                 ['Ẩn danh để hại người', 'Không được phép']],
                'Tự do trên mạng luôn đi kèm trách nhiệm và giới hạn pháp luật.', $d);
            $this->matching($L, 'Nối mỗi tình huống với cách xử lý đúng.',
                [['Bị bình luận ác ý', 'Chặn và báo cáo'],
                 ['Ảnh bị đăng lén', 'Yêu cầu gỡ bỏ'],
                 ['Thấy tin giả', 'Không chia sẻ'],
                 ['Bạn bị bắt nạt mạng', 'Động viên và báo cáo giúp']],
                'Ứng xử đúng giúp ta và bạn bè an toàn trên mạng.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm NÊN hoặc KHÔNG NÊN đăng công khai.',
                [['Ảnh hoạt động lớp', 'Nên'], ['Thành tích học tập', 'Nên'],
                 ['Địa chỉ nhà', 'Không nên'], ['Ảnh riêng tư', 'Không nên']],
                'Chia sẻ điều tích cực; giữ kín thông tin riêng tư.', $d);
            $this->sortQ($L, 'Kéo mỗi hành vi vào nhóm VĂN MINH hoặc THIẾU VĂN MINH.',
                [['Bình luận lịch sự', 'Văn minh'], ['Tôn trọng ý kiến khác', 'Văn minh'],
                 ['Chửi bới', 'Thiếu văn minh'], ['Lan truyền tin giả', 'Thiếu văn minh']],
                'Văn minh mạng: lịch sự, tôn trọng, không lan truyền tin giả.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm BẢO VỆ hoặc LÀM LỘ riêng tư.',
                [['Để chế độ bạn bè', 'Bảo vệ'], ['Kiểm tra ảnh được gắn thẻ', 'Bảo vệ'],
                 ['Công khai mọi thứ', 'Làm lộ'], ['Chia sẻ vị trí trực tiếp', 'Làm lộ']],
                'Chủ động cài đặt riêng tư để bảo vệ thông tin cá nhân.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NGUYÊN TẮC VÀNG hoặc SAI LẦM.',
                [['Suy nghĩ trước khi đăng', 'Nguyên tắc'], ['Xin phép khi đăng ảnh người khác', 'Nguyên tắc'], ['Kiểm tra cài đặt riêng tư', 'Nguyên tắc'],
                 ['Đăng mọi thứ lên mạng', 'Sai lầm'], ['Bình luận ác ý', 'Sai lầm'], ['Chia sẻ tin chưa kiểm chứng', 'Sai lầm']],
                'Ba nguyên tắc vàng khi dùng mạng xã hội.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Mọi thứ đăng lên mạng tạo thành "dấu chân ___".', [[0, 'số']],
                'Dấu chân số theo ta rất lâu, thậm chí suốt đời.', $d);
            $this->fill($L, 'Đăng ảnh có mặt bạn bè cần ___ phép trước.', [[0, 'xin']],
                'Xin phép thể hiện sự tôn trọng bạn bè.', $d);
            $this->fill($L, 'Không ___ luận ác ý trên mạng xã hội.', [[0, 'bình']],
                'Bình luận ác ý làm tổn thương người khác và vi phạm pháp luật.', $d);
            $this->fill($L, 'Hãy kiểm tra cài đặt ___ tư thường xuyên.', [[0, 'riêng']],
                'Cài đặt riêng tư giúp kiểm soát ai xem được thông tin của mình.', $d);
        }
    }

    private function seedThSuDungAnToanL92(): void
    {
        $L = 'th-su-dung-an-toan-lop-9-2';
        $d = 'kho';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nghi ngờ tài khoản bị hack, việc đầu tiên cần làm là gì?',
                ['Đổi mật khẩu ngay', 'Đăng thêm ảnh', 'Chia sẻ cho bạn bè', 'Bỏ tài khoản luôn'], 0,
                'Đổi mật khẩu ngay để kẻ xấu không tiếp tục dùng tài khoản.', $d);
            $this->quiz($L, 'Bị lừa chuyển tiền qua mạng, em cần làm gì?',
                ['Giữ bằng chứng và báo công an', 'Chuyển thêm tiền', 'Im lặng cho qua', 'Xóa hết tin nhắn'], 0,
                'Giữ bằng chứng giao dịch và báo công an để được hỗ trợ.', $d);
            $this->quiz($L, 'Bị bắt nạt trên mạng, em KHÔNG nên làm gì?',
                ['Đáp trả chửi lại', 'Chặn kẻ bắt nạt', 'Lưu bằng chứng', 'Kể với cha mẹ'], 0,
                'Đáp trả chỉ làm mọi chuyện tệ hơn; hãy chặn, lưu bằng chứng và báo cáo.', $d);
            $this->quiz($L, 'Kênh hỗ trợ nào dành cho trẻ em bị hại trên mạng?',
                ['Tổng đài 111', 'Tổng đài taxi', 'Bạn mới quen trên mạng', 'Im lặng chịu đựng'], 0,
                'Tổng đài 111 hỗ trợ trẻ em bị bạo lực, xâm hại cả trên mạng.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự cố với bước xử lý đầu tiên.',
                [['Bị hack tài khoản', 'Đổi mật khẩu ngay'],
                 ['Bị lừa tiền', 'Giữ bằng chứng'],
                 ['Bị bắt nạt mạng', 'Chặn và báo cáo'],
                 ['Lộ thông tin cá nhân', 'Thu hồi và cảnh báo']],
                'Mỗi sự cố có bước xử lý đầu tiên khác nhau.', $d);
            $this->matching($L, 'Nối mỗi thứ với việc có cần giữ làm bằng chứng không.',
                [['Tin nhắn đe dọa', 'Chụp màn hình giữ lại'],
                 ['Giao dịch bị lừa', 'Lưu sao kê'],
                 ['Bài đăng bôi xấu', 'Lưu đường link'],
                 ['Tin nhắn rác quảng cáo', 'Xóa được']],
                'Bằng chứng giúp cơ quan chức năng xử lý kẻ xấu.', $d);
            $this->matching($L, 'Nối mỗi kênh với trường hợp cần gọi.',
                [['111', 'Trẻ em bị hại'],
                 ['113', 'Bị lừa đảo, đe dọa'],
                 ['Ngân hàng', 'Khóa thẻ gấp'],
                 ['Nền tảng mạng', 'Báo cáo tài khoản xấu']],
                'Gọi đúng kênh để được hỗ trợ nhanh nhất.', $d);
            $this->matching($L, 'Nối mỗi hành động với đánh giá đúng hay sai.',
                [['Đáp trả kẻ bắt nạt', 'Sai'],
                 ['Lưu bằng chứng', 'Đúng'],
                 ['Xóa hết tin nhắn đe dọa', 'Sai'],
                 ['Kể với người lớn', 'Đúng']],
                'Không đáp trả, giữ bằng chứng, kể người lớn là đúng.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI khi bị hack tài khoản.',
                [['Đổi mật khẩu ngay', 'Đúng'], ['Đăng xuất mọi thiết bị', 'Đúng'],
                 ['Chia sẻ mật khẩu mới', 'Sai'], ['Bỏ mặc không làm gì', 'Sai']],
                'Bị hack: đổi mật khẩu, đăng xuất mọi nơi; không chia sẻ, không bỏ mặc.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN khi bị bắt nạt mạng.',
                [['Chặn kẻ bắt nạt', 'Nên'], ['Lưu bằng chứng', 'Nên'], ['Kể với người lớn', 'Nên'],
                 ['Đáp trả chửi lại', 'Không nên']],
                'Nên chặn, lưu bằng chứng, kể người lớn; không đáp trả.', $d);
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm CẦN GIỮ hoặc XÓA ĐƯỢC.',
                [['Tin nhắn đe dọa', 'Cần giữ'], ['Sao kê chuyển tiền', 'Cần giữ'], ['Ảnh chụp màn hình', 'Cần giữ'],
                 ['Tin nhắn rác quảng cáo', 'Xóa được']],
                'Giữ bằng chứng bị hại; tin rác thì xóa được.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm KHI BỊ HACK hoặc KHI BỊ LỪA/BẮT NẠT.',
                [['Đổi mật khẩu', 'Khi bị hack'], ['Bật xác thực hai bước', 'Khi bị hack'], ['Đăng xuất mọi thiết bị', 'Khi bị hack'],
                 ['Giữ bằng chứng', 'Khi bị lừa/bắt nạt'], ['Báo công an', 'Khi bị lừa/bắt nạt'], ['Chặn và báo cáo', 'Khi bị lừa/bắt nạt']],
                'Bị hack thì khóa tài khoản; bị lừa, bị bắt nạt thì giữ bằng chứng và báo cáo.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bị hack, hãy ___ mật khẩu ngay lập tức.', [[0, 'đổi']],
                'Đổi mật khẩu nhanh giúp giành lại quyền kiểm soát tài khoản.', $d);
            $this->fill($L, 'Bị lừa tiền, cần giữ ___ chứng và báo công an.', [[0, 'bằng']],
                'Bằng chứng giúp công an điều tra kẻ lừa đảo.', $d);
            $this->fill($L, 'Bị bắt nạt mạng, đừng ___ trả mà hãy chặn và báo cáo.', [[0, 'đáp']],
                'Đáp trả chỉ khiến mâu thuẫn leo thang.', $d);
            $this->fill($L, 'Trẻ em bị hại trên mạng có thể gọi tổng đài ___.', [[0, '111']],
                'Tổng đài 111 luôn sẵn sàng hỗ trợ trẻ em.', $d);
        }
    }

    // ================= CÔNG NGHỆ — AN TOÀN ĐIỆN =================

    private function seedCnAnToanDienL61(): void
    {
        $L = 'cn-an-toan-dien-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tay ướt có được cắm điện không?',
                ['Không', 'Được', 'Tùy lúc', 'Được nếu cẩn thận'], 0,
                'Nước dẫn điện nên tay ướt chạm đồ điện rất nguy hiểm.');
            $this->quiz($L, 'Thấy dây điện bị hở trong nhà, em nên làm gì?',
                ['Báo người lớn ngay', 'Chạm thử xem sao', 'Dùng băng dính quấn', 'Bỏ qua'], 0,
                'Dây điện hở rất nguy hiểm, phải báo người lớn xử lý.');
            $this->quiz($L, 'Không được thả diều ở đâu?',
                ['Gần đường dây điện', 'Sân vận động rộng', 'Công viên', 'Bãi đất trống'], 0,
                'Diều vướng vào dây điện có thể gây điện giật và chập cháy.');
            $this->quiz($L, 'Vật liệu nào dẫn điện, nguy hiểm khi chọc vào ổ cắm?',
                ['Kim loại', 'Nhựa', 'Gỗ khô', 'Cao su'], 0,
                'Kim loại dẫn điện nên tuyệt đối không chọc vào ổ cắm.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tình huống với đánh giá nguy hiểm hay an toàn.',
                [['Tay ướt cắm điện', 'Nguy hiểm'],
                 ['Dây điện bị hở', 'Nguy hiểm'],
                 ['Ổ cắm còn nguyên vẹn', 'An toàn'],
                 ['Dùng đồ điện tay khô ráo', 'An toàn']],
                'Tay ướt và dây hở là nguy hiểm; đồ nguyên vẹn, tay khô là an toàn.');
            $this->matching($L, 'Nối mỗi vật liệu với khả năng dẫn điện.',
                [['Kim loại', 'Dẫn điện'],
                 ['Nước', 'Dẫn điện'],
                 ['Nhựa', 'Không dẫn điện'],
                 ['Gỗ khô', 'Không dẫn điện']],
                'Kim loại và nước dẫn điện; nhựa và gỗ khô thì không.');
            $this->matching($L, 'Nối mỗi hành vi với đánh giá đúng hay sai.',
                [['Thả diều gần dây điện', 'Sai'],
                 ['Trèo lên cột điện', 'Sai'],
                 ['Báo người lớn khi thấy dây hở', 'Đúng'],
                 ['Dùng điện khi tay khô', 'Đúng']],
                'Tránh xa dây điện, cột điện; thấy sự cố báo người lớn.');
            $this->matching($L, 'Nối mỗi nơi với việc có điện nguy hiểm không.',
                [['Ổ cắm điện', 'Có'],
                 ['Dây điện bị đứt', 'Có'],
                 ['Cột điện', 'Có'],
                 ['Đồ chơi bằng nhựa', 'Không']],
                'Ổ cắm, dây đứt, cột điện đều nguy hiểm; đồ chơi nhựa thì không.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tình huống vào nhóm NGUY HIỂM hoặc AN TOÀN.',
                [['Dây điện hở', 'Nguy hiểm'], ['Tay ướt chạm ổ cắm', 'Nguy hiểm'],
                 ['Ổ cắm có nắp che', 'An toàn'], ['Dùng điện tay khô ráo', 'An toàn']],
                'Dây hở, tay ướt là nguy hiểm; ổ có nắp, tay khô là an toàn.');
            $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm DẪN ĐIỆN hoặc CÁCH ĐIỆN.',
                [['Sắt', 'Dẫn điện'], ['Đồng', 'Dẫn điện'],
                 ['Nhựa', 'Cách điện'], ['Cao su', 'Cách điện']],
                'Sắt, đồng dẫn điện; nhựa, cao su cách điện.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Báo người lớn khi thấy dây hở', 'Nên'], ['Thả diều xa dây điện', 'Nên'],
                 ['Chạm vào dây điện hở', 'Không nên'], ['Chọc que vào ổ cắm', 'Không nên']],
                'Nên báo người lớn, thả diều xa dây điện; không chạm dây hở.');
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm NGUY HIỂM hoặc AN TOÀN.',
                [['Dây điện bị hở', 'Nguy hiểm'], ['Ổ cắm bị vỡ', 'Nguy hiểm'], ['Tay ướt chạm điện', 'Nguy hiểm'],
                 ['Ổ cắm nguyên vẹn', 'An toàn'], ['Dây điện bọc kín', 'An toàn'], ['Tay khô ráo', 'An toàn']],
                'Nhận biết nguy hiểm để tránh xa, giữ an toàn cho mình.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tay ___ không được chạm vào đồ điện.', [[0, 'ướt']],
                'Nước dẫn điện nên tay ướt rất nguy hiểm.');
            $this->fill($L, 'Thấy dây điện hở, hãy báo ___ lớn ngay.', [[0, 'người']],
                'Người lớn sẽ xử lý dây điện hở một cách an toàn.');
            $this->fill($L, 'Không thả diều gần đường ___ điện.', [[0, 'dây']],
                'Diều vướng dây điện gây nguy hiểm tính mạng.');
            $this->fill($L, 'Kim loại là vật liệu ___ điện.', [[0, 'dẫn']],
                'Vì dẫn điện nên không dùng vật kim loại chọc vào ổ cắm.');
        }
    }

    private function seedCnAnToanDienL62(): void
    {
        $L = 'cn-an-toan-dien-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Rút phích điện phải làm thế nào?',
                ['Cầm phần nhựa rút ra', 'Giật mạnh dây điện', 'Dùng răng cắn', 'Nhờ bạn giật hộ'], 0,
                'Cầm phần nhựa (cách điện) rút thẳng ra, không giật dây.');
            $this->quiz($L, 'Đồ điện trong nhà bị hỏng, em nên làm gì?',
                ['Báo người lớn', 'Tự mở ra sửa', 'Vẫn dùng tiếp', 'Giấu đi'], 0,
                'Trẻ em không tự sửa đồ điện; hãy báo người lớn.');
            $this->quiz($L, 'Dùng xong đồ điện nên làm gì?',
                ['Tắt và rút phích', 'Để nguyên', 'Cắm thêm đồ khác', 'Phủ vải lên'], 0,
                'Tắt và rút phích khi không dùng vừa an toàn vừa tiết kiệm điện.');
            $this->quiz($L, 'Ấm điện nên đặt ở đâu?',
                ['Xa tầm với của trẻ nhỏ', 'Cạnh bồn rửa', 'Trên giường', 'Nơi ẩm ướt'], 0,
                'Ấm điện nóng và dùng điện nên để xa trẻ nhỏ, xa nước.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đồ điện với quy tắc dùng nó.',
                [['Bàn là', 'Rút điện sau khi dùng'],
                 ['Quạt máy', 'Không thò tay vào'],
                 ['Ấm điện', 'Để xa nguồn nước'],
                 ['Tivi', 'Tắt khi không xem']],
                'Mỗi đồ điện có quy tắc an toàn riêng cần nhớ.');
            $this->matching($L, 'Nối mỗi hành động với đánh giá đúng hay sai.',
                [['Cầm phần nhựa rút phích', 'Đúng'],
                 ['Nhờ người lớn sửa đồ hỏng', 'Đúng'],
                 ['Giật mạnh dây điện', 'Sai'],
                 ['Tay ướt bật công tắc', 'Sai']],
                'Cầm nhựa rút phích, nhờ người lớn là đúng.');
            $this->matching($L, 'Nối mỗi đồ điện với nơi nên đặt nó.',
                [['Ấm điện', 'Xa bồn rửa'],
                 ['Máy sấy tóc', 'Nơi khô ráo'],
                 ['Quạt máy', 'Nơi vững chắc'],
                 ['Dây điện', 'Gọn gàng, xa lối đi']],
                'Đặt đồ điện đúng nơi giúp an toàn cho cả nhà.');
            $this->matching($L, 'Nối mỗi việc với đánh giá khi không dùng đồ điện.',
                [['Tắt công tắc', 'Nên'],
                 ['Rút phích cắm', 'Nên'],
                 ['Để nguyên', 'Không nên'],
                 ['Cắm qua đêm', 'Không nên']],
                'Không dùng thì tắt và rút phích là an toàn nhất.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI.',
                [['Cầm nhựa rút phích', 'Đúng'], ['Tay khô dùng điện', 'Đúng'],
                 ['Giật dây điện', 'Sai'], ['Tự sửa đồ hỏng', 'Sai']],
                'Cầm nhựa, tay khô là đúng; giật dây, tự sửa là sai.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Rút điện khi không dùng', 'Nên'], ['Báo người lớn khi đồ hỏng', 'Nên'],
                 ['Để đồ điện gần nước', 'Không nên'], ['Dùng đồ điện đã hỏng', 'Không nên']],
                'Nên rút điện, báo người lớn; không để gần nước, không dùng đồ hỏng.');
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Phích cắm nguyên vẹn', 'An toàn'], ['Ổ cắm có nắp che', 'An toàn'],
                 ['Dây điện bị sờn', 'Nguy hiểm'], ['Ổ cắm bị vỡ', 'Nguy hiểm']],
                'Đồ nguyên vẹn thì an toàn; sờn, vỡ thì nguy hiểm.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI khi dùng đồ điện.',
                [['Cầm phần nhựa rút phích', 'Đúng'], ['Tay khô ráo', 'Đúng'], ['Rút điện khi dùng xong', 'Đúng'],
                 ['Giật mạnh dây điện', 'Sai'], ['Tay ướt bật công tắc', 'Sai'], ['Tự ý sửa đồ hỏng', 'Sai']],
                'Ba đúng, ba sai — nhớ kỹ để dùng điện an toàn mỗi ngày.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Rút phích điện phải cầm vào phần ___.', [[0, 'nhựa']],
                'Phần nhựa cách điện nên cầm vào đó rất an toàn.');
            $this->fill($L, 'Không dùng đồ điện khi tay ___.', [[0, 'ướt']],
                'Tay ướt dẫn điện gây nguy hiểm.');
            $this->fill($L, 'Đồ điện hỏng phải báo ___ lớn.', [[0, 'người']],
                'Chỉ người lớn mới được sửa chữa đồ điện.');
            $this->fill($L, 'Dùng xong nhớ ___ điện hoặc rút phích.', [[0, 'tắt']],
                'Tắt điện khi không dùng vừa an toàn vừa tiết kiệm.');
        }
    }

    private function seedCnAnToanDienL71(): void
    {
        $L = 'cn-an-toan-dien-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thấy chập điện trong nhà, việc đầu tiên cần làm là gì?',
                ['Ngắt cầu dao ngay', 'Dội nước vào', 'Chạm thử xem sao', 'Bỏ chạy thật xa'], 0,
                'Ngắt cầu dao để cắt nguồn điện là việc đầu tiên và quan trọng nhất.');
            $this->quiz($L, 'Thấy người bị điện giật, em KHÔNG được làm gì?',
                ['Chạm tay trực tiếp vào người đó', 'Gọi cấp cứu', 'Ngắt điện', 'Gọi người lớn'], 0,
                'Chạm trực tiếp sẽ khiến em cũng bị điện giật theo.');
            $this->quiz($L, 'Để cứu người bị điện giật, nên dùng vật gì gạt dây điện ra?',
                ['Gậy gỗ khô', 'Tay không', 'Thanh sắt', 'Dội nước'], 0,
                'Gậy gỗ khô cách điện nên an toàn khi gạt dây điện.');
            $this->quiz($L, 'Xảy ra cháy do điện, gọi số điện thoại nào?',
                ['114', '113', '115', '111'], 0,
                '114 là số cứu hỏa, gọi ngay khi có cháy.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự cố với cách xử lý đúng.',
                [['Chập điện', 'Ngắt cầu dao'],
                 ['Cháy do điện', 'Gọi 114, không dội nước'],
                 ['Người bị điện giật', 'Ngắt điện rồi mới cứu'],
                 ['Dây điện đứt rơi xuống', 'Tránh xa, báo người lớn']],
                'Ngắt điện trước, cứu người sau; cháy điện không dội nước.');
            $this->matching($L, 'Nối mỗi số điện thoại với trường hợp cần gọi.',
                [['114', 'Cháy'],
                 ['115', 'Cấp cứu người bị nạn'],
                 ['113', 'Công an'],
                 ['111', 'Bảo vệ trẻ em']],
                'Nhớ các số khẩn cấp để gọi đúng lúc.');
            $this->matching($L, 'Nối mỗi vật với việc có dùng được để cứu người bị giật không.',
                [['Gậy gỗ khô', 'Được'],
                 ['Ghế nhựa', 'Được'],
                 ['Tay không', 'Không'],
                 ['Thanh kim loại', 'Không']],
                'Chỉ dùng vật cách điện như gỗ khô, nhựa; không dùng tay hay kim loại.');
            $this->matching($L, 'Nối mỗi việc với đánh giá đúng hay sai khi có sự cố điện.',
                [['Ngắt điện trước khi cứu', 'Đúng'],
                 ['Gọi người lớn giúp', 'Đúng'],
                 ['Dội nước vào đám cháy điện', 'Sai'],
                 ['Chạm vào người đang bị giật', 'Sai']],
                'Ngắt điện, gọi người lớn là đúng; dội nước, chạm trực tiếp là sai.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI khi xử lý sự cố điện.',
                [['Ngắt cầu dao khi chập điện', 'Đúng'], ['Dùng gậy gỗ cứu người', 'Đúng'],
                 ['Dội nước vào đám cháy điện', 'Sai'], ['Chạm tay vào người bị giật', 'Sai']],
                'Ngắt điện, dùng gậy gỗ là đúng; dội nước, chạm tay là sai.');
            $this->sortQ($L, 'Kéo mỗi số điện thoại vào đúng TRƯỜNG HỢP của nó.',
                [['114', 'Cháy'], ['115', 'Người bị thương'],
                 ['111', 'Trẻ em gặp nguy hiểm'], ['113', 'Mất an ninh trật tự']],
                'Cháy gọi 114, cấp cứu gọi 115, trẻ em gọi 111.');
            $this->sortQ($L, 'Kéo mỗi vật vào nhóm ĐƯỢC hoặc KHÔNG ĐƯỢC dùng để gạt dây điện.',
                [['Gậy gỗ khô', 'Được'], ['Ghế nhựa', 'Được'],
                 ['Tay không', 'Không được'], ['Thanh kim loại', 'Không được']],
                'Vật cách điện thì được; tay không và kim loại thì không.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm BƯỚC 1, BƯỚC 2 hoặc TUYỆT ĐỐI KHÔNG.',
                [['Ngắt cầu dao', 'Bước 1'], ['Gọi người lớn', 'Bước 1'],
                 ['Dùng vật cách điện gạt dây', 'Bước 2: Cứu người'], ['Gọi 115', 'Bước 2: Cứu người'],
                 ['Dội nước vào cháy điện', 'Tuyệt đối không'], ['Chạm tay trực tiếp', 'Tuyệt đối không']],
                'Bước 1 ngắt điện gọi người lớn; bước 2 cứu người; tuyệt đối không dội nước, chạm tay.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Chập điện, hãy ___ cầu dao ngay.', [[0, 'ngắt']],
                'Ngắt cầu dao cắt nguồn điện, ngăn cháy lan.');
            $this->fill($L, 'Cứu người bị điện giật bằng gậy ___ khô.', [[0, 'gỗ']],
                'Gỗ khô cách điện nên an toàn khi cứu người.');
            $this->fill($L, 'Cháy do điện gọi số ___.', [[0, '114']],
                '114 là số điện thoại cứu hỏa.');
            $this->fill($L, 'Tuyệt đối không ___ nước vào đám cháy điện.', [[0, 'dội']],
                'Nước dẫn điện nên dội vào cháy điện cực kỳ nguy hiểm.');
        }
    }

    private function seedCnAnToanDienL72(): void
    {
        $L = 'cn-an-toan-dien-lop-7-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Loại bóng đèn nào tiết kiệm điện nhất?',
                ['Bóng LED', 'Bóng sợi đốt', 'Bóng huỳnh quang cũ', 'Đèn dầu'], 0,
                'Bóng LED sáng hơn mà tốn ít điện hơn nhiều so với sợi đốt.', $d);
            $this->quiz($L, 'Ra khỏi phòng, em nên làm gì?',
                ['Tắt đèn và quạt', 'Để nguyên', 'Mở thêm đèn', 'Nhờ người khác tắt'], 0,
                'Tắt thiết bị khi ra khỏi phòng là thói quen tiết kiệm điện.', $d);
            $this->quiz($L, 'Điều hòa nên để ở nhiệt độ bao nhiêu là hợp lý?',
                ['26–27°C', '16°C', '18°C', 'Càng lạnh càng tốt'], 0,
                '26–27°C vừa mát vừa tiết kiệm điện, tốt cho sức khỏe.', $d);
            $this->quiz($L, 'Tiết kiệm điện mang lại lợi ích gì?',
                ['Giảm tiền điện và bảo vệ môi trường', 'Nhà tối om', 'Rất bất tiện', 'Không có lợi gì'], 0,
                'Tiết kiệm điện giảm chi phí, giảm tải lưới điện và bảo vệ môi trường.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi việc làm với việc có tiết kiệm điện không.',
                [['Tắt đèn khi ra ngoài', 'Có'],
                 ['Dùng bóng LED', 'Có'],
                 ['Rút sạc khi đầy pin', 'Có'],
                 ['Để tivi ở chế độ chờ', 'Không']],
                'Tắt đèn, dùng LED, rút sạc là tiết kiệm; để chờ là lãng phí.', $d);
            $this->matching($L, 'Nối mỗi thiết bị với mức tiêu thụ điện của nó.',
                [['Điều hòa', 'Nhiều'],
                 ['Bàn là', 'Nhiều'],
                 ['Tủ lạnh', 'Trung bình'],
                 ['Đèn LED', 'Ít']],
                'Điều hòa, bàn là tốn nhiều điện; đèn LED tốn ít.', $d);
            $this->matching($L, 'Nối mỗi thói quen với đánh giá tốt hay xấu.',
                [['Tận dụng ánh sáng tự nhiên', 'Tốt'],
                 ['Giặt đủ tải máy giặt', 'Tốt'],
                 ['Bật điều hòa cả ngày', 'Xấu'],
                 ['Mở tủ lạnh thật lâu', 'Xấu']],
                'Tận dụng tự nhiên, dùng đủ tải là tốt.', $d);
            $this->matching($L, 'Nối mỗi thời điểm với cách dùng điện hợp lý.',
                [['Giờ cao điểm buổi tối', 'Hạn chế dùng'],
                 ['Ban ngày', 'Tận dụng ánh nắng'],
                 ['Đêm khuya', 'Tắt bớt thiết bị'],
                 ['Sáng sớm', 'Dùng bình thường']],
                'Hạn chế dùng điện giờ cao điểm để giảm tải lưới điện.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm TIẾT KIỆM hoặc LÃNG PHÍ điện.',
                [['Tắt đèn khi ra ngoài', 'Tiết kiệm'], ['Dùng bóng LED', 'Tiết kiệm'],
                 ['Bật điều hòa 16°C', 'Lãng phí'], ['Để tivi ở chế độ chờ', 'Lãng phí']],
                'Tắt đèn, dùng LED là tiết kiệm; điều hòa quá lạnh, để chờ là lãng phí.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Rút sạc khi đầy pin', 'Nên'], ['Tận dụng ánh nắng', 'Nên'],
                 ['Mở tủ lạnh thật lâu', 'Không nên'], ['Bật đèn giữa ban ngày', 'Không nên']],
                'Nên rút sạc, tận dụng nắng; không mở tủ lạnh lâu, không bật đèn ban ngày.', $d);
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm ÍT TỐN hoặc TỐN NHIỀU điện.',
                [['Đèn LED', 'Ít tốn'], ['Quạt máy', 'Ít tốn'],
                 ['Điều hòa', 'Tốn nhiều'], ['Bàn là', 'Tốn nhiều']],
                'LED, quạt ít tốn; điều hòa, bàn là tốn nhiều điện.', $d);
            $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm THÓI QUEN TỐT hoặc THÓI QUEN XẤU.',
                [['Tắt đèn khi ra ngoài', 'Thói quen tốt'], ['Rút sạc khi đầy pin', 'Thói quen tốt'], ['Tận dụng ánh sáng tự nhiên', 'Thói quen tốt'],
                 ['Bật điều hòa 16 độ', 'Thói quen xấu'], ['Để tivi ở chế độ chờ', 'Thói quen xấu'], ['Mở tủ lạnh thật lâu', 'Thói quen xấu']],
                'Ba thói quen tốt tiết kiệm điện mỗi ngày.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bóng đèn ___ tiết kiệm điện hơn bóng sợi đốt.', [[0, 'LED']],
                'LED cho ánh sáng tốt mà tiêu thụ ít điện năng.', $d);
            $this->fill($L, 'Ra khỏi phòng nhớ ___ đèn và quạt.', [[0, 'tắt']],
                'Tắt thiết bị không dùng là cách tiết kiệm đơn giản nhất.', $d);
            $this->fill($L, 'Điều hòa nên để 26–27 độ ___.', [[0, 'C']],
                '26–27 độ C vừa đủ mát vừa tiết kiệm điện.', $d);
            $this->fill($L, 'Rút ___ khi điện thoại đã đầy pin.', [[0, 'sạc']],
                'Sạc đầy mà không rút vẫn tốn điện và hại pin.', $d);
        }
    }

    // ================= CÔNG NGHỆ — VẬT LIỆU & DỤNG CỤ =================

    private function seedCnVatLieuL71(): void
    {
        $L = 'cn-vat-lieu-dung-cu-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Vật liệu nào dẫn điện tốt?',
                ['Kim loại', 'Gỗ', 'Nhựa', 'Vải'], 0,
                'Kim loại như đồng, sắt dẫn điện rất tốt.');
            $this->quiz($L, 'Bàn ghế học sinh thường được làm bằng vật liệu nào?',
                ['Gỗ', 'Sắt', 'Nhựa dẻo', 'Giấy'], 0,
                'Gỗ nhẹ, dễ gia công nên thường dùng làm bàn ghế.');
            $this->quiz($L, 'Chai nước uống thường được làm bằng gì?',
                ['Nhựa', 'Gỗ', 'Sắt', 'Đất sét'], 0,
                'Nhựa nhẹ, chống nước nên dùng làm chai lọ.');
            $this->quiz($L, 'Nhựa có những tính chất nào?',
                ['Nhẹ, không dẫn điện, chống nước', 'Rất nặng', 'Dẫn điện tốt', 'Thấm nước'], 0,
                'Nhựa nhẹ, cách điện và chống nước nên ứng dụng rộng rãi.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi vật liệu với tính chất của nó.',
                [['Gỗ', 'Nhẹ, dễ gia công'],
                 ['Sắt', 'Cứng, chắc'],
                 ['Nhôm', 'Nhẹ, không gỉ'],
                 ['Nhựa', 'Chống nước, cách điện']],
                'Mỗi vật liệu có tính chất riêng phù hợp với từng ứng dụng.');
            $this->matching($L, 'Nối mỗi vật liệu với ứng dụng của nó.',
                [['Gỗ', 'Bàn ghế'],
                 ['Sắt', 'Khung nhà'],
                 ['Đồng', 'Dây điện'],
                 ['Nhựa', 'Chai lọ']],
                'Chọn vật liệu phù hợp với công dụng của sản phẩm.');
            $this->matching($L, 'Nối mỗi vật liệu với khả năng dẫn điện.',
                [['Đồng', 'Dẫn điện tốt'],
                 ['Sắt', 'Dẫn điện'],
                 ['Gỗ khô', 'Không dẫn điện'],
                 ['Nhựa', 'Không dẫn điện']],
                'Đồng, sắt dẫn điện; gỗ khô, nhựa cách điện.');
            $this->matching($L, 'Nối mỗi đồ vật với vật liệu làm ra nó.',
                [['Bàn học', 'Gỗ'],
                 ['Xoong nồi', 'Nhôm'],
                 ['Chai nước', 'Nhựa'],
                 ['Đinh', 'Sắt']],
                'Nhìn đồ vật có thể đoán vật liệu làm ra nó.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm DẪN ĐIỆN hoặc KHÔNG DẪN ĐIỆN.',
                [['Đồng', 'Dẫn điện'], ['Sắt', 'Dẫn điện'],
                 ['Nhựa', 'Không dẫn điện'], ['Gỗ khô', 'Không dẫn điện']],
                'Đồng, sắt dẫn điện; nhựa, gỗ khô cách điện.');
            $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm TỰ NHIÊN hoặc NHÂN TẠO.',
                [['Gỗ', 'Tự nhiên'], ['Tre', 'Tự nhiên'],
                 ['Nhựa', 'Nhân tạo'], ['Nilon', 'Nhân tạo']],
                'Gỗ, tre có sẵn trong tự nhiên; nhựa, nilon do con người tạo ra.');
            $this->sortQ($L, 'Kéo mỗi vật liệu vào nhóm CỨNG hoặc MỀM DẺO.',
                [['Sắt', 'Cứng'], ['Gỗ', 'Cứng'],
                 ['Nhựa dẻo', 'Mềm dẻo'], ['Cao su', 'Mềm dẻo']],
                'Sắt, gỗ cứng chắc; nhựa dẻo, cao su mềm dẻo.');
            $this->sortQ($L, 'Kéo mỗi đồ vật vào nhóm ĐỒ GỖ, ĐỒ KIM LOẠI hoặc ĐỒ NHỰA.',
                [['Bàn ghế', 'Đồ gỗ'], ['Tủ quần áo', 'Đồ gỗ'],
                 ['Xoong nồi', 'Đồ kim loại'], ['Đinh ốc', 'Đồ kim loại'],
                 ['Chai lọ', 'Đồ nhựa'], ['Rổ rá', 'Đồ nhựa']],
                'Phân loại đồ vật theo vật liệu làm ra chúng.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dây điện thường làm bằng ___ vì dẫn điện tốt.', [[0, 'đồng']],
                'Đồng dẫn điện tốt và dẻo nên dùng làm dây điện.');
            $this->fill($L, 'Bàn ghế học sinh thường làm bằng ___.', [[0, 'gỗ']],
                'Gỗ nhẹ và dễ gia công thành bàn ghế.');
            $this->fill($L, 'Chai nước làm bằng nhựa vì nhẹ và chống ___.', [[0, 'nước']],
                'Nhựa không thấm nước nên đựng được chất lỏng.');
            $this->fill($L, 'Nhôm nhẹ và không bị ___ như sắt.', [[0, 'gỉ']],
                'Nhôm có lớp oxit bảo vệ nên không gỉ như sắt.');
        }
    }

    private function seedCnVatLieuL72(): void
    {
        $L = 'cn-vat-lieu-dung-cu-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dụng cụ nào dùng để vẽ đường tròn?',
                ['Compa', 'Thước thẳng', 'Êke', 'Bút chì'], 0,
                'Compa có một chân nhọn cố định và một chân chì để vẽ tròn.');
            $this->quiz($L, 'Êke dùng để làm gì?',
                ['Vẽ góc vuông', 'Vẽ đường tròn', 'Đo độ dài', 'Cắt vật liệu'], 0,
                'Êke hình tam giác vuông dùng để vẽ và kiểm tra góc vuông.');
            $this->quiz($L, 'Khi đo độ dài, đặt thước như thế nào là đúng?',
                ['Vạch 0 trùng đầu vật', 'Đặt tùy ý', 'Vạch 10 trùng đầu vật', 'Không cần đặt thẳng'], 0,
                'Vạch 0 trùng đầu vật thì số đo mới chính xác.');
            $this->quiz($L, 'Nên dùng gì để đánh dấu trước khi cắt?',
                ['Bút chì', 'Bút xóa', 'Sơn', 'Dao'], 0,
                'Bút chì vạch dấu rõ mà dễ tẩy xóa khi cần.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dụng cụ với công dụng của nó.',
                [['Thước thẳng', 'Đo độ dài'],
                 ['Êke', 'Vẽ góc vuông'],
                 ['Compa', 'Vẽ đường tròn'],
                 ['Bút chì', 'Đánh dấu']],
                'Mỗi dụng cụ có công dụng riêng trong đo và vẽ.');
            $this->matching($L, 'Nối mỗi đơn vị với thứ nó dùng để đo.',
                [['cm', 'Độ dài'],
                 ['Độ', 'Góc'],
                 ['mm', 'Chi tiết nhỏ'],
                 ['m', 'Vật dài lớn']],
                'Chọn đơn vị phù hợp với kích thước cần đo.');
            $this->matching($L, 'Nối mỗi thao tác với đánh giá đúng hay sai.',
                [['Vạch 0 trùng đầu vật', 'Đúng'],
                 ['Đánh dấu bằng bút chì', 'Đúng'],
                 ['Mắt nhìn xiên khi đọc', 'Sai'],
                 ['Đo ướm chừng', 'Sai']],
                'Đo đúng kỹ thuật cho kết quả chính xác.');
            $this->matching($L, 'Nối mỗi dụng cụ với môn học hay dùng nó.',
                [['Thước kẻ', 'Môn Toán'],
                 ['Compa', 'Môn Toán'],
                 ['Êke', 'Môn Toán, Kỹ thuật'],
                 ['Thước dây', 'May mặc, Công nghệ']],
                'Dụng cụ đo xuất hiện trong nhiều môn học.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm ĐO ĐỘ DÀI hoặc VẼ HÌNH.',
                [['Thước thẳng', 'Đo độ dài'], ['Thước dây', 'Đo độ dài'],
                 ['Compa', 'Vẽ hình'], ['Êke', 'Vẽ hình']],
                'Thước để đo, compa và êke để vẽ.');
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI khi đo.',
                [['Vạch 0 trùng đầu vật', 'Đúng'], ['Mắt nhìn thẳng', 'Đúng'],
                 ['Nhìn xiên khi đọc số', 'Sai'], ['Thước bị cong', 'Sai']],
                'Đo đúng: vạch 0 trùng đầu, mắt nhìn thẳng, thước thẳng.');
            $this->sortQ($L, 'Kéo mỗi đơn vị vào nhóm NHỎ hoặc LỚN.',
                [['mm', 'Nhỏ'], ['cm', 'Nhỏ'],
                 ['m', 'Lớn'], ['km', 'Lớn']],
                'mm, cm đo vật nhỏ; m, km đo vật lớn.');
            $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm ĐO, VẼ hoặc ĐÁNH DẤU.',
                [['Thước thẳng', 'Đo độ dài'], ['Thước dây', 'Đo độ dài'],
                 ['Compa', 'Vẽ hình'], ['Êke', 'Vẽ hình'],
                 ['Bút chì', 'Đánh dấu'], ['Phấn', 'Đánh dấu']],
                'Đo bằng thước, vẽ bằng compa êke, đánh dấu bằng chì phấn.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '___ dùng để vẽ đường tròn.', [[0, 'Compa']],
                'Compa là dụng cụ chuyên để vẽ đường tròn.');
            $this->fill($L, 'Êke dùng để vẽ góc ___.', [[0, 'vuông']],
                'Êke có hình tam giác vuông.');
            $this->fill($L, 'Khi đo, đặt vạch ___ trùng với đầu vật.', [[0, '0']],
                'Bắt đầu từ vạch 0 thì số đo mới chính xác.');
            $this->fill($L, 'Nên đánh dấu bằng bút ___ trước khi cắt.', [[0, 'chì']],
                'Vạch chì rõ ràng và dễ sửa khi đánh dấu sai.');
        }
    }

    private function seedCnVatLieuL81(): void
    {
        $L = 'cn-vat-lieu-dung-cu-lop-8-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Muốn vặn ốc vít, ta dùng dụng cụ nào?',
                ['Tua vít', 'Búa', 'Cưa', 'Kìm'], 0,
                'Tua vít chuyên dùng để vặn ốc vít.', $d);
            $this->quiz($L, 'Búa dùng để làm gì?',
                ['Đóng đinh', 'Vặn ốc', 'Cắt gỗ', 'Kẹp vật'], 0,
                'Búa dùng để đóng đinh và gõ.', $d);
            $this->quiz($L, 'Cưa tay thường dùng để cắt vật liệu nào?',
                ['Gỗ', 'Sắt dày', 'Kính', 'Đá'], 0,
                'Cưa tay có răng cưa thích hợp để cắt gỗ.', $d);
            $this->quiz($L, 'Khi dùng dụng cụ cơ khí, cần chú ý gì?',
                ['Cầm chắc chắn, đeo bảo hộ khi cần', 'Dùng bừa bãi', 'Dùng dụng cụ đã hỏng', 'Đùa giỡn khi dùng'], 0,
                'Cầm chắc, dùng đúng cách và đeo bảo hộ để an toàn.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dụng cụ với công dụng của nó.',
                [['Búa', 'Đóng đinh'],
                 ['Tua vít', 'Vặn ốc vít'],
                 ['Kìm', 'Kẹp và cắt'],
                 ['Cờ lê', 'Vặn bu-lông']],
                'Mỗi dụng cụ cơ khí có công dụng riêng.', $d);
            $this->matching($L, 'Nối mỗi dụng cụ với loại ốc hoặc việc phù hợp.',
                [['Tua vít đầu dẹt', 'Ốc đầu rãnh thẳng'],
                 ['Tua vít 4 cạnh', 'Ốc bake'],
                 ['Kìm nhọn', 'Kẹp chi tiết nhỏ'],
                 ['Mỏ lết', 'Vặn nhiều cỡ ốc']],
                'Chọn đúng dụng cụ cho từng loại ốc.', $d);
            $this->matching($L, 'Nối mỗi công việc với dụng cụ dùng để làm.',
                [['Đóng đinh', 'Búa'],
                 ['Vặn ốc', 'Tua vít'],
                 ['Cắt dây điện', 'Kìm cắt'],
                 ['Cưa gỗ', 'Cưa tay']],
                'Đúng việc, đúng dụng cụ thì hiệu quả và an toàn.', $d);
            $this->matching($L, 'Nối mỗi nguy hiểm với cách phòng tránh.',
                [['Búa tuột cán', 'Kiểm tra trước khi dùng'],
                 ['Đứt tay', 'Đeo găng tay'],
                 ['Mạt văng vào mắt', 'Đeo kính bảo hộ'],
                 ['Dụng cụ hỏng', 'Không dùng']],
                'Kiểm tra dụng cụ và đeo bảo hộ để tránh tai nạn.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm VẶN, ĐÓNG hoặc CẮT.',
                [['Tua vít', 'Vặn'], ['Cờ lê', 'Vặn'],
                 ['Búa', 'Đóng'],
                 ['Cưa', 'Cắt']],
                'Tua vít, cờ lê để vặn; búa để đóng; cưa để cắt.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Kiểm tra dụng cụ trước', 'An toàn'], ['Đeo kính bảo hộ', 'An toàn'],
                 ['Dùng búa đã hỏng', 'Nguy hiểm'], ['Đùa giỡn khi dùng', 'Nguy hiểm']],
                'Kiểm tra và bảo hộ thì an toàn; dùng đồ hỏng, đùa giỡn thì nguy hiểm.', $d);
            $this->sortQ($L, 'Kéo mỗi dụng cụ vào nhóm CẦM TAY hoặc DÙNG MÁY.',
                [['Búa', 'Cầm tay'], ['Tua vít', 'Cầm tay'],
                 ['Máy khoan', 'Dùng máy'], ['Máy cưa', 'Dùng máy']],
                'Dụng cụ cầm tay dùng sức người; máy dùng điện.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào đúng NHÓM DỤNG CỤ của nó.',
                [['Đóng đinh', 'Dùng búa'], ['Vặn ốc vít', 'Dùng tua vít'],
                 ['Kẹp giữ vật', 'Dùng kìm'], ['Vặn bu-lông', 'Dùng cờ lê'],
                 ['Cắt gỗ', 'Dùng cưa'], ['Kiểm tra cán búa', 'Trước khi dùng']],
                'Nhớ công dụng từng dụng cụ và kiểm tra trước khi dùng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, '___ vít dùng để vặn ốc.', [[0, 'Tua']],
                'Tua vít là dụng cụ chuyên vặn ốc vít.', $d);
            $this->fill($L, 'Búa dùng để ___ đinh.', [[0, 'đóng']],
                'Đóng đinh là công dụng chính của búa.', $d);
            $this->fill($L, 'Kìm có thể kẹp và ___ dây.', [[0, 'cắt']],
                'Kìm vừa kẹp giữ vừa cắt được dây.', $d);
            $this->fill($L, 'Trước khi dùng, phải kiểm tra dụng cụ còn ___ không.', [[0, 'tốt']],
                'Dụng cụ hỏng dễ gây tai nạn, phải kiểm tra trước.', $d);
        }
    }

    private function seedCnVatLieuL82(): void
    {
        $L = 'cn-vat-lieu-dung-cu-lop-8-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trước khi cắt vật liệu, cần làm gì?',
                ['Đo đạc kỹ', 'Cắt bừa', 'Cắt thật nhiều', 'Không cần làm gì'], 0,
                'Đo đạc kỹ trước khi cắt để tránh lãng phí vật liệu.', $d);
            $this->quiz($L, 'Pin cũ thuộc loại rác nào?',
                ['Rác nguy hại', 'Rác hữu cơ', 'Rác tái chế', 'Rác thường'], 0,
                'Pin chứa hóa chất độc hại nên là rác nguy hại.', $d);
            $this->quiz($L, 'Những vật liệu nào có thể tái chế?',
                ['Giấy, nhựa, kim loại', 'Thức ăn thừa', 'Đất cát', 'Nước thải'], 0,
                'Giấy, nhựa, kim loại, thủy tinh đều có thể tái chế.', $d);
            $this->quiz($L, 'Vứt pin bừa bãi gây hậu quả gì?',
                ['Ô nhiễm môi trường', 'Không sao cả', 'Tốt cho đất', 'Sạch sẽ hơn'], 0,
                'Pin chứa chì, thủy ngân gây ô nhiễm đất và nước.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại rác với ví dụ của nó.',
                [['Rác hữu cơ', 'Vỏ trái cây'],
                 ['Rác tái chế', 'Chai nhựa'],
                 ['Rác nguy hại', 'Pin cũ'],
                 ['Rác tái chế', 'Giấy vụn']],
                'Phân loại rác giúp xử lý và tái chế hiệu quả.', $d);
            $this->matching($L, 'Nối mỗi việc làm với việc có tiết kiệm vật liệu không.',
                [['Đo kỹ trước khi cắt', 'Có'],
                 ['Tận dụng vật liệu thừa', 'Có'],
                 ['Cắt bừa bãi', 'Không'],
                 ['Vứt vật liệu còn dùng được', 'Không']],
                'Đo kỹ và tận dụng giúp tiết kiệm vật liệu.', $d);
            $this->matching($L, 'Nối mỗi vật liệu cũ với sản phẩm tái chế từ nó.',
                [['Giấy cũ', 'Giấy mới'],
                 ['Chai nhựa', 'Hạt nhựa'],
                 ['Sắt vụn', 'Thép mới'],
                 ['Thủy tinh vỡ', 'Chai mới']],
                'Tái chế biến rác thành tài nguyên mới.', $d);
            $this->matching($L, 'Nối mỗi hành vi với đánh giá đúng hay sai.',
                [['Phân loại rác', 'Đúng'],
                 ['Tận dụng giấy một mặt', 'Đúng'],
                 ['Vứt pin bừa bãi', 'Sai'],
                 ['Đốt túi nilon', 'Sai']],
                'Phân loại và tận dụng là đúng; vứt pin, đốt nilon là sai.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm RÁC TÁI CHẾ, HỮU CƠ hoặc NGUY HẠI.',
                [['Giấy vụn', 'Tái chế'], ['Chai nhựa', 'Tái chế'],
                 ['Vỏ chuối', 'Hữu cơ'],
                 ['Pin cũ', 'Nguy hại']],
                'Giấy, nhựa tái chế; vỏ trái cây hữu cơ; pin là nguy hại.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm TIẾT KIỆM hoặc LÃNG PHÍ vật liệu.',
                [['Đo kỹ trước khi cắt', 'Tiết kiệm'], ['Tận dụng vật thừa', 'Tiết kiệm'],
                 ['Cắt bừa bãi', 'Lãng phí'], ['Vứt vật liệu còn dùng được', 'Lãng phí']],
                'Đo kỹ, tận dụng là tiết kiệm; cắt bừa, vứt bừa là lãng phí.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Phân loại rác', 'Nên'], ['Tái sử dụng đồ cũ', 'Nên'],
                 ['Vứt pin bừa bãi', 'Không nên'], ['Đốt rác nhựa', 'Không nên']],
                'Nên phân loại và tái sử dụng; không vứt pin, không đốt nhựa.', $d);
            $this->sortQ($L, 'Kéo mỗi thứ vào nhóm RÁC HỮU CƠ, RÁC TÁI CHẾ hoặc RÁC NGUY HẠI.',
                [['Vỏ rau củ', 'Hữu cơ'], ['Cơm thừa', 'Hữu cơ'],
                 ['Chai nhựa', 'Tái chế'], ['Giấy vụn', 'Tái chế'],
                 ['Pin cũ', 'Nguy hại'], ['Bóng đèn vỡ', 'Nguy hại']],
                'Ba loại rác chính cần phân loại đúng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hãy ___ loại rác trước khi bỏ.', [[0, 'phân']],
                'Phân loại rác là bước đầu của tái chế.', $d);
            $this->fill($L, 'Pin cũ là rác ___ hại, không vứt bừa bãi.', [[0, 'nguy']],
                'Rác nguy hại cần thu gom và xử lý riêng.', $d);
            $this->fill($L, 'Giấy, nhựa, kim loại có thể ___ chế.', [[0, 'tái']],
                'Tái chế giúp tiết kiệm tài nguyên thiên nhiên.', $d);
            $this->fill($L, 'Đo đạc kỹ trước khi cắt để tiết kiệm vật ___.', [[0, 'liệu']],
                'Vật liệu tiết kiệm được nhờ đo đạc cẩn thận.', $d);
        }
    }

    // ================= CÔNG NGHỆ — TRỒNG TRỌT =================

    private function seedCnTrongTrotL81(): void
    {
        $L = 'cn-trong-trot-lop-8-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bước đầu tiên khi trồng cây là gì?',
                ['Chọn giống tốt', 'Tưới nước', 'Bón phân', 'Thu hoạch'], 0,
                'Giống tốt là nền tảng cho vụ mùa bội thu.', $d);
            $this->quiz($L, 'Đất trồng cây cần có đặc điểm gì?',
                ['Tơi xốp', 'Cứng chắc', 'Ngập nước', 'Đầy đá sỏi'], 0,
                'Đất tơi xốp giúp rễ cây phát triển và thoát nước tốt.', $d);
            $this->quiz($L, 'Gieo trồng cần đảm bảo yêu cầu nào?',
                ['Đúng thời vụ', 'Vào ban đêm', 'Lúc mưa to', 'Tùy hứng'], 0,
                'Đúng thời vụ giúp cây sinh trưởng tốt, ít sâu bệnh.', $d);
            $this->quiz($L, 'Bón lót là bón phân vào lúc nào?',
                ['Trước khi trồng', 'Sau khi thu hoạch', 'Khi cây đã chết', 'Không cần bón'], 0,
                'Bón lót cung cấp dinh dưỡng cho đất trước khi gieo trồng.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bước trồng cây với nội dung của nó.',
                [['Chọn giống', 'Hạt tốt, phù hợp'],
                 ['Làm đất', 'Tơi xốp, bón lót'],
                 ['Gieo trồng', 'Đúng thời vụ'],
                 ['Chăm sóc', 'Tưới, bón, trừ sâu']],
                'Bốn bước: chọn giống, làm đất, gieo trồng, chăm sóc.', $d);
            $this->matching($L, 'Nối mỗi công việc với bước tương ứng.',
                [['Chọn hạt giống', 'Chọn giống'],
                 ['Cày xới đất', 'Làm đất'],
                 ['Gieo hạt', 'Gieo trồng'],
                 ['Tưới nước', 'Chăm sóc']],
                'Mỗi công việc thuộc một bước trong quy trình.', $d);
            $this->matching($L, 'Nối mỗi loại cây với thời vụ trồng của nó.',
                [['Lúa', 'Trồng theo vụ'],
                 ['Rau ăn lá', 'Trồng quanh năm'],
                 ['Cây ăn quả', 'Đầu mùa mưa'],
                 ['Hoa tết', 'Gieo trước tết']],
                'Mỗi loại cây có thời vụ trồng thích hợp riêng.');
            $this->matching($L, 'Nối mỗi dụng cụ với công dụng của nó.',
                [['Cuốc', 'Xới đất'],
                 ['Bình tưới', 'Tưới nước'],
                 ['Kéo tỉa', 'Tỉa cành'],
                 ['Găng tay', 'Bảo hộ tay']],
                'Dụng cụ phù hợp giúp công việc hiệu quả và an toàn.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm TRƯỚC hoặc SAU khi gieo trồng.',
                [['Chọn giống', 'Trước'], ['Làm đất', 'Trước'],
                 ['Tưới nước', 'Sau'], ['Bón thúc', 'Sau']],
                'Chọn giống, làm đất trước; tưới nước, bón thúc sau.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm BƯỚC 1–2 hoặc BƯỚC 3–4.',
                [['Chọn giống', 'Bước 1–2'], ['Làm đất', 'Bước 1–2'],
                 ['Gieo trồng', 'Bước 3–4'], ['Chăm sóc', 'Bước 3–4']],
                'Chuẩn bị trước (1–2), gieo trồng và chăm sóc sau (3–4).', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI kỹ thuật.',
                [['Đất tơi xốp', 'Đúng'], ['Gieo đúng thời vụ', 'Đúng'],
                 ['Gieo quá dày', 'Sai'], ['Không bón lót', 'Sai']],
                'Đất tơi xốp, đúng thời vụ là đúng; gieo dày, bỏ bón lót là sai.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm CHUẨN BỊ, GIEO TRỒNG hoặc CHĂM SÓC.',
                [['Chọn hạt giống tốt', 'Chuẩn bị'], ['Làm đất tơi xốp', 'Chuẩn bị'], ['Bón lót', 'Chuẩn bị'],
                 ['Gieo đúng khoảng cách', 'Gieo trồng'],
                 ['Tưới nước', 'Chăm sóc'], ['Bón thúc', 'Chăm sóc']],
                'Chuẩn bị kỹ, gieo đúng, chăm sóc đều thì cây tốt.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bước đầu tiên là ___ giống tốt.', [[0, 'chọn']],
                'Chọn giống tốt quyết định phần lớn thành công.', $d);
            $this->fill($L, 'Đất trồng cần tơi ___ để rễ phát triển.', [[0, 'xốp']],
                'Đất tơi xốp giúp rễ thở và hút dinh dưỡng.', $d);
            $this->fill($L, 'Gieo trồng phải đúng thời ___.', [[0, 'vụ']],
                'Đúng thời vụ cây mới sinh trưởng tốt.', $d);
            $this->fill($L, '___ lót là bón phân trước khi trồng.', [[0, 'Bón']],
                'Bón lót làm đất giàu dinh dưỡng trước khi gieo.', $d);
        }
    }

    private function seedCnTrongTrotL82(): void
    {
        $L = 'cn-trong-trot-lop-8-2';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nên tưới cây vào lúc nào trong ngày?',
                ['Sáng sớm hoặc chiều mát', 'Giữa trưa nắng gắt', 'Nửa đêm', 'Lúc nào cũng được'], 0,
                'Tưới sáng sớm hoặc chiều mát giúp cây hấp thụ tốt, ít bay hơi.', $d);
            $this->quiz($L, 'Tưới quá nhiều nước gây hại gì cho cây?',
                ['Úng rễ', 'Cây tốt hơn', 'Ra nhiều hoa', 'Không sao cả'], 0,
                'Úng nước làm rễ thối, cây héo và chết.', $d);
            $this->quiz($L, 'Đâu là ví dụ về phân hữu cơ?',
                ['Phân chuồng, phân xanh', 'Phân đạm', 'Phân lân', 'Phân kali'], 0,
                'Phân hữu cơ có nguồn gốc tự nhiên như phân chuồng, phân xanh.', $d);
            $this->quiz($L, 'Lạm dụng phân hóa học gây hậu quả gì?',
                ['Làm hại đất', 'Cây tốt mãi', 'Không sao cả', 'Đất tốt hơn'], 0,
                'Bón quá nhiều phân hóa học làm đất chai cứng, ô nhiễm.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi loại phân với ví dụ của nó.',
                [['Phân hữu cơ', 'Phân chuồng'],
                 ['Phân hữu cơ', 'Phân xanh'],
                 ['Phân hóa học', 'Đạm (N)'],
                 ['Phân hóa học', 'Lân (P)']],
                'Hữu cơ: phân chuồng, phân xanh. Hóa học: đạm, lân, kali.', $d);
            $this->matching($L, 'Nối mỗi chất dinh dưỡng với vai trò của nó.',
                [['Đạm (N)', 'Phát triển thân lá'],
                 ['Lân (P)', 'Phát triển rễ'],
                 ['Kali (K)', 'Ra hoa, đậu quả'],
                 ['Phân hữu cơ', 'Cải tạo đất']],
                'Đạm cho lá, lân cho rễ, kali cho hoa quả.', $d);
            $this->matching($L, 'Nối mỗi việc với đánh giá đúng hay sai.',
                [['Tưới sáng sớm', 'Đúng'],
                 ['Bón đúng liều lượng', 'Đúng'],
                 ['Tưới giữa trưa nắng', 'Sai'],
                 ['Bón thật nhiều phân', 'Sai']],
                'Tưới đúng lúc, bón đúng liều là đúng kỹ thuật.', $d);
            $this->matching($L, 'Nối mỗi dấu hiệu với tình trạng của cây.',
                [['Lá héo rũ', 'Thiếu nước'],
                 ['Rễ thối', 'Thừa nước'],
                 ['Lá vàng úa', 'Thiếu đạm'],
                 ['Xanh tốt', 'Đủ dinh dưỡng']],
                'Nhìn lá và rễ có thể đoán cây thiếu hay thừa gì.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi loại phân vào nhóm HỮU CƠ hoặc HÓA HỌC.',
                [['Phân chuồng', 'Hữu cơ'], ['Phân xanh', 'Hữu cơ'],
                 ['Đạm', 'Hóa học'], ['Kali', 'Hóa học']],
                'Phân chuồng, phân xanh là hữu cơ; đạm, kali là hóa học.', $d);
            $this->sortQ($L, 'Kéo mỗi thời điểm vào nhóm NÊN hoặc KHÔNG NÊN tưới.',
                [['Sáng sớm', 'Nên'], ['Chiều mát', 'Nên'],
                 ['Trưa nắng gắt', 'Không nên'], ['Tưới đến úng nước', 'Không nên']],
                'Nên tưới sáng sớm, chiều mát; không tưới trưa nắng, không để úng.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI khi bón phân.',
                [['Bón đúng liều', 'Đúng'], ['Bón xa gốc cây', 'Đúng'],
                 ['Bón quá nhiều', 'Sai'], ['Bón lên lá non', 'Sai']],
                'Bón đúng liều, xa gốc là đúng; bón nhiều, bón lên lá non là sai.', $d);
            $this->sortQ($L, 'Kéo mỗi loại phân vào nhóm PHÂN HỮU CƠ hoặc PHÂN HÓA HỌC.',
                [['Phân chuồng ủ hoai', 'Hữu cơ'], ['Phân xanh', 'Hữu cơ'], ['Rơm rạ ủ', 'Hữu cơ'],
                 ['Đạm', 'Hóa học'], ['Lân', 'Hóa học'], ['Kali', 'Hóa học']],
                'Ba loại hữu cơ, ba loại hóa học thường dùng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nên tưới cây vào sáng sớm hoặc chiều ___.', [[0, 'mát']],
                'Chiều mát ít nắng gắt, cây hấp thụ nước tốt.', $d);
            $this->fill($L, 'Tưới quá nhiều làm cây bị ___ rễ.', [[0, 'úng']],
                'Úng rễ làm cây héo úa và có thể chết.', $d);
            $this->fill($L, 'Phân chuồng thuộc loại phân ___ cơ.', [[0, 'hữu']],
                'Phân hữu cơ an toàn và cải tạo đất tốt.', $d);
            $this->fill($L, 'Đạm (N) giúp cây phát triển phần ___.', [[0, 'lá']],
                'Thiếu đạm lá cây vàng úa, còi cọc.', $d);
        }
    }

    private function seedCnTrongTrotL91(): void
    {
        $L = 'cn-trong-trot-lop-9-1';
        $d = 'trung_binh';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bọ rùa có lợi cho cây trồng vì sao?',
                ['Ăn rệp hại cây', 'Ăn lá cây', 'Phá hoa', 'Đẻ trứng hại cây'], 0,
                'Bọ rùa là thiên địch, chuyên ăn rệp gây hại.', $d);
            $this->quiz($L, 'Biện pháp trừ sâu nào an toàn nhất?',
                ['Bắt sâu bằng tay', 'Phun thật nhiều thuốc', 'Bỏ mặc', 'Đốt cả ruộng'], 0,
                'Bắt sâu bằng tay không độc hại, bảo vệ môi trường.', $d);
            $this->quiz($L, 'Dấu hiệu nào cho thấy cây bị sâu ăn lá?',
                ['Lá bị thủng lỗ', 'Lá xanh tốt', 'Ra nhiều hoa', 'Quả to'], 0,
                'Sâu ăn lá để lại những lỗ thủng trên phiến lá.', $d);
            $this->quiz($L, 'Thuốc bảo vệ thực vật hóa học phải dùng thế nào?',
                ['Đúng liều, đúng lúc, đúng cách', 'Càng nhiều càng tốt', 'Tùy hứng', 'Trộn bừa các loại'], 0,
                'Dùng đúng liều, đúng lúc, đúng cách để hiệu quả và an toàn.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sâu bệnh với dấu hiệu của nó.',
                [['Sâu ăn lá', 'Lá bị thủng lỗ'],
                 ['Rệp', 'Lá xoăn, có nhựa dính'],
                 ['Bệnh đốm lá', 'Đốm nâu trên lá'],
                 ['Thối rễ', 'Cây héo dù đủ nước']],
                'Nhận biết dấu hiệu để phòng trừ kịp thời.', $d);
            $this->matching($L, 'Nối mỗi biện pháp với ví dụ của nó.',
                [['Thủ công', 'Bắt sâu bằng tay'],
                 ['Sinh học', 'Thả ong, bọ rùa'],
                 ['Vật lý', 'Bẫy đèn'],
                 ['Hóa học', 'Phun thuốc đúng cách']],
                'Ưu tiên biện pháp an toàn trước khi dùng thuốc hóa học.', $d);
            $this->matching($L, 'Nối mỗi thiên địch với con mồi của nó.',
                [['Bọ rùa', 'Rệp'],
                 ['Ong ký sinh', 'Sâu hại'],
                 ['Chim', 'Sâu bọ'],
                 ['Ếch', 'Côn trùng']],
                'Thiên địch là bạn của nhà nông.', $d);
            $this->matching($L, 'Nối mỗi việc với đánh giá nên hay không nên.',
                [['Vệ sinh đồng ruộng', 'Nên'],
                 ['Dùng thuốc sinh học', 'Nên'],
                 ['Phun thuốc bừa bãi', 'Không nên'],
                 ['Vứt bao thuốc bừa bãi', 'Không nên']],
                'Phòng trừ tổng hợp: vệ sinh, sinh học trước; hạn chế hóa học.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm AN TOÀN hoặc ĐỘC HẠI.',
                [['Bắt sâu bằng tay', 'An toàn'], ['Dùng thiên địch', 'An toàn'],
                 ['Phun nhiều thuốc hóa học', 'Độc hại'], ['Vứt bao thuốc bừa bãi', 'Độc hại']],
                'Biện pháp tự nhiên an toàn; lạm dụng hóa chất thì độc hại.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm PHÒNG NGỪA hoặc TRỪ DIỆT.',
                [['Vệ sinh ruộng', 'Phòng ngừa'], ['Chọn giống khỏe', 'Phòng ngừa'],
                 ['Bắt sâu', 'Trừ diệt'], ['Phun thuốc', 'Trừ diệt']],
                'Phòng ngừa trước, trừ diệt khi sâu bệnh đã xuất hiện.', $d);
            $this->sortQ($L, 'Kéo mỗi con vật vào nhóm CÓ LỢI hoặc CÓ HẠI cho cây.',
                [['Bọ rùa', 'Có lợi'], ['Ong', 'Có lợi'],
                 ['Sâu ăn lá', 'Có hại'], ['Rệp', 'Có hại']],
                'Bọ rùa, ong có lợi; sâu ăn lá, rệp có hại.', $d);
            $this->sortQ($L, 'Kéo mỗi dấu hiệu, biện pháp vào đúng NHÓM của nó.',
                [['Lá bị thủng lỗ', 'Dấu hiệu sâu'], ['Lá xoăn có rệp', 'Dấu hiệu sâu'],
                 ['Cây héo dù đủ nước', 'Dấu hiệu bệnh rễ'], ['Đốm nâu trên lá', 'Dấu hiệu bệnh lá'],
                 ['Bắt sâu bằng tay', 'Biện pháp an toàn'], ['Thả bọ rùa', 'Biện pháp an toàn']],
                'Nhận diện đúng dấu hiệu để chọn biện pháp phù hợp.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bọ rùa là thiên địch, chuyên ăn ___.', [[0, 'rệp']],
                'Một con bọ rùa ăn rất nhiều rệp mỗi ngày.', $d);
            $this->fill($L, 'Sâu ăn lá làm lá cây bị ___ lỗ.', [[0, 'thủng']],
                'Lá thủng lỗ là dấu hiệu đặc trưng của sâu ăn lá.', $d);
            $this->fill($L, 'Nên ưu tiên biện pháp ___ học như thả ong, bọ rùa.', [[0, 'sinh']],
                'Biện pháp sinh học an toàn cho người và môi trường.', $d);
            $this->fill($L, 'Phun thuốc phải đúng liều, đúng lúc, đúng ___.', [[0, 'cách']],
                'Ba "đúng" khi dùng thuốc bảo vệ thực vật.', $d);
        }
    }

    private function seedCnTrongTrotL92(): void
    {
        $L = 'cn-trong-trot-lop-9-2';
        $d = 'kho';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nên thu hoạch nông sản vào lúc nào trong ngày?',
                ['Sáng sớm mát mẻ', 'Trưa nắng gắt', 'Chiều tối muộn', 'Nửa đêm'], 0,
                'Sáng sớm mát mẻ giúp nông sản tươi lâu, ít dập nát.', $d);
            $this->quiz($L, 'Thóc sau khi thu hoạch cần làm gì trước khi cất?',
                ['Phơi hoặc sấy khô', 'Để ướt', 'Đóng bao khi ướt', 'Để ngoài mưa'], 0,
                'Thóc ướt dễ mốc, mọc mầm nên phải phơi khô.', $d);
            $this->quiz($L, 'Thủy canh là phương pháp trồng cây như thế nào?',
                ['Trong nước dinh dưỡng, không cần đất', 'Trên đất khô cằn', 'Không cần nước', 'Trong bóng tối'], 0,
                'Thủy canh trồng cây trong dung dịch dinh dưỡng thay cho đất.', $d);
            $this->quiz($L, 'Tưới nhỏ giọt có ưu điểm gì?',
                ['Tiết kiệm nước', 'Tốn nhiều nước', 'Làm úng cây', 'Không có tác dụng'], 0,
                'Tưới nhỏ giọt đưa nước trực tiếp đến rễ, tiết kiệm đáng kể.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nông sản với cách bảo quản đúng.',
                [['Thóc', 'Phơi khô'],
                 ['Rau xanh', 'Nơi thoáng mát'],
                 ['Trái cây', 'Tránh dập nát'],
                 ['Hạt giống', 'Nơi khô ráo']],
                'Mỗi nông sản có cách bảo quản phù hợp riêng.', $d);
            $this->matching($L, 'Nối mỗi công nghệ với đặc điểm của nó.',
                [['Thủy canh', 'Trồng trong nước dinh dưỡng'],
                 ['Nhà kính', 'Kiểm soát nhiệt độ, sâu bệnh'],
                 ['Tưới nhỏ giọt', 'Tiết kiệm nước'],
                 ['Đèn LED trồng cây', 'Trồng được trong nhà']],
                'Công nghệ mới giúp trồng trọt hiệu quả và bền vững.', $d);
            $this->matching($L, 'Nối mỗi thời điểm với việc thu hoạch tương ứng.',
                [['Lúa chín vàng', 'Gặt'],
                 ['Rau còn non', 'Hái'],
                 ['Quả đã chín', 'Hái nhẹ tay'],
                 ['Hoa vừa nở', 'Cắt sáng sớm']],
                'Thu hoạch đúng độ chín cho chất lượng tốt nhất.', $d);
            $this->matching($L, 'Nối mỗi việc với đánh giá đúng hay sai.',
                [['Hái nhẹ tay', 'Đúng'],
                 ['Phơi thóc thật khô', 'Đúng'],
                 ['Quăng quật nông sản', 'Sai'],
                 ['Để nông sản nơi ẩm ướt', 'Sai']],
                'Nhẹ tay khi thu hoạch, bảo quản khô ráo là đúng.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm ĐÚNG hoặc SAI khi thu hoạch.',
                [['Thu đúng độ chín', 'Đúng'], ['Hái nhẹ tay', 'Đúng'],
                 ['Thu khi còn non', 'Sai'], ['Quăng quật', 'Sai']],
                'Đúng độ chín, nhẹ tay là đúng; thu non, quăng quật là sai.', $d);
            $this->sortQ($L, 'Kéo mỗi cách vào nhóm BẢO QUẢN TỐT hoặc KÉM.',
                [['Nơi khô ráo', 'Tốt'], ['Phơi khô', 'Tốt'],
                 ['Nơi ẩm ướt', 'Kém'], ['Đóng bao khi ướt', 'Kém']],
                'Khô ráo thì tốt; ẩm ướt thì nông sản dễ mốc hỏng.', $d);
            $this->sortQ($L, 'Kéo mỗi phương pháp vào nhóm TRUYỀN THỐNG hoặc HIỆN ĐẠI.',
                [['Trồng trên đất', 'Truyền thống'], ['Tưới tràn', 'Truyền thống'],
                 ['Thủy canh', 'Hiện đại'], ['Tưới nhỏ giọt', 'Hiện đại']],
                'Truyền thống đơn giản; hiện đại tiết kiệm và hiệu quả hơn.', $d);
            $this->sortQ($L, 'Kéo mỗi việc vào nhóm BẢO QUẢN hoặc CÔNG NGHỆ MỚI.',
                [['Phơi khô thóc', 'Bảo quản'], ['Nơi khô ráo', 'Bảo quản'], ['Đóng gói kín', 'Bảo quản'],
                 ['Thủy canh', 'Công nghệ mới'], ['Nhà kính', 'Công nghệ mới'], ['Tưới nhỏ giọt', 'Công nghệ mới']],
                'Bảo quản tốt giữ chất lượng; công nghệ mới nâng hiệu quả.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thu hoạch khi nông sản đạt độ ___ muồi.', [[0, 'chín']],
                'Đúng độ chín cho năng suất và chất lượng cao nhất.', $d);
            $this->fill($L, 'Thóc ướt phải ___ khô trước khi cất giữ.', [[0, 'phơi']],
                'Phơi khô chống mốc và mọc mầm.', $d);
            $this->fill($L, '___ canh là trồng cây không cần đất.', [[0, 'Thủy']],
                'Thủy canh dùng dung dịch dinh dưỡng thay đất.', $d);
            $this->fill($L, 'Tưới nhỏ ___ giúp tiết kiệm nước.', [[0, 'giọt']],
                'Mỗi giọt nước được đưa đúng đến rễ cây.', $d);
        }
    }
}
