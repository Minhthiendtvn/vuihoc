<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * skill_slug => [lessons...]. Mỗi bài học: đầy đủ objective, difficulty,
     * duration_minutes, instructions. Mỗi bài có 6 câu hỏi chia cho 2 kiểu chơi
     * (bài 1: quiz + matching, bài 2: sort + fill) để mỗi CHỦ ĐỀ có đủ
     * ≥3 câu cho cả 4 kiểu chơi.
     */
    public static function tree(): array
    {
        return [
            // ================= TOÁN =================
            'kn-toan-so-tu-nhien-1' => [
                [
                    'title' => 'Cộng và trừ số tự nhiên', 'slug' => 'toan-cong-tru-so-tu-nhien',
                    'objective' => 'Thực hiện đúng phép cộng, phép trừ số tự nhiên trong phạm vi 1 000 000.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Đọc kỹ đề bài, thực hiện phép cộng hoặc phép trừ rồi chọn đáp án đúng. Kiểm tra lại phép tính trước khi nộp bài.',
                ],
                [
                    'title' => 'Nhân và chia số tự nhiên', 'slug' => 'toan-nhan-chia-so-tu-nhien',
                    'objective' => 'Thực hiện đúng phép nhân, phép chia hết cho số tự nhiên.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                    'instructions' => 'Kéo thả từng ý vào nhóm đúng hoặc điền kết quả vào chỗ trống. Chú ý thứ tự thực hiện phép tính.',
                ],
            ],
            'kn-toan-phan-so-1' => [
                [
                    'title' => 'Cộng và trừ phân số', 'slug' => 'toan-cong-tru-phan-so',
                    'objective' => 'Cộng, trừ được hai phân số (cùng mẫu số và khác mẫu số).',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Nhớ quy đồng mẫu số trước khi cộng, trừ phân số khác mẫu. Rút gọn kết quả nếu được.',
                ],
                [
                    'title' => 'Nhân và chia phân số', 'slug' => 'toan-nhan-chia-phan-so',
                    'objective' => 'Nhân, chia được hai phân số, biết rút gọn kết quả.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Muốn nhân hai phân số ta nhân tử với tử, mẫu với mẫu. Muốn chia, ta nhân với phân số đảo ngược.',
                ],
            ],
            'kn-toan-dai-so-1' => [
                [
                    'title' => 'Thu gọn đơn thức', 'slug' => 'toan-thu-gon-don-thuc',
                    'objective' => 'Thu gọn được đơn thức và xác định bậc của đơn thức.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Nhân các hệ số với nhau, nhân các biến với nhau, cộng số mũ của cùng một biến.',
                ],
                [
                    'title' => 'Giá trị của biểu thức đại số', 'slug' => 'toan-gia-tri-bieu-thuc',
                    'objective' => 'Tính được giá trị của biểu thức đại số khi biết giá trị của biến.',
                    'difficulty' => 'kho', 'duration_minutes' => 15,
                    'instructions' => 'Thay giá trị của biến vào biểu thức rồi tính theo đúng thứ tự phép tính: trong ngoặc trước, nhân chia trước cộng trừ sau.',
                ],
            ],
            'kn-toan-hinh-hoc-1' => [
                [
                    'title' => 'Góc và đường thẳng', 'slug' => 'toan-goc-va-duong-thang',
                    'objective' => 'Nhận biết được góc nhọn, góc vuông, góc tù, góc bẹt và vị trí hai đường thẳng.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Quan sát hình vẽ (mô tả trong đề) và chọn đáp án đúng. Góc vuông 90 độ, góc nhọn nhỏ hơn 90 độ, góc tù lớn hơn 90 độ.',
                ],
                [
                    'title' => 'Tam giác và các góc', 'slug' => 'toan-tam-giac',
                    'objective' => 'Biết tổng ba góc trong tam giác bằng 180 độ và phân loại tam giác.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                    'instructions' => 'Vận dụng: tổng ba góc trong của một tam giác luôn bằng 180 độ. Kéo thả các ý vào nhóm đúng.',
                ],
            ],
            // ================= TIẾNG VIỆT =================
            'kn-tv-tu-cau-1' => [
                [
                    'title' => 'Các từ loại cơ bản', 'slug' => 'tv-cac-tu-loai',
                    'objective' => 'Nhận biết được danh từ, động từ, tính từ, số từ, lượng từ trong câu.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Danh từ chỉ người, vật, hiện tượng. Động từ chỉ hành động, trạng thái. Tính từ chỉ đặc điểm, tính chất.',
                ],
                [
                    'title' => 'Cấu tạo câu đơn', 'slug' => 'tv-cau-don',
                    'objective' => 'Xác định được chủ ngữ, vị ngữ và các thành phần phụ trong câu đơn.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                    'instructions' => 'Chủ ngữ thường trả lời câu hỏi "ai? cái gì?". Vị ngữ trả lời "làm gì? thế nào? là gì?".',
                ],
            ],
            'kn-tv-chinh-ta-1' => [
                [
                    'title' => 'Dấu hỏi và dấu ngã', 'slug' => 'tv-dau-hoi-dau-nga',
                    'objective' => 'Viết đúng dấu hỏi, dấu ngã trong các từ thường gặp.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Đọc kỹ từng từ và chọn dạng viết đúng. Ghi nhớ các từ mẫu để tránh nhầm lẫn.',
                ],
                [
                    'title' => 'Âm đầu ch, tr, d, gi, r', 'slug' => 'tv-am-dau',
                    'objective' => 'Phân biệt được các âm đầu ch/tr, d/gi/r trong từ ngữ thông dụng.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Đọc thầm từ rồi điền âm đầu còn thiếu. Đối chiếu với cách phát âm đúng của giáo viên.',
                ],
            ],
            'kn-tv-mieu-ta-1' => [
                [
                    'title' => 'Bài văn miêu tả', 'slug' => 'tv-bai-van-mieu-ta',
                    'objective' => 'Nắm được bố cục và cách dùng từ ngữ trong bài văn miêu tả.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Bài văn miêu tả gồm mở bài, thân bài, kết bài. Chú ý các từ ngữ gợi hình, gợi cảm.',
                ],
                [
                    'title' => 'Biện pháp tu từ', 'slug' => 'tv-bien-phap-tu-tu',
                    'objective' => 'Nhận biết được biện pháp so sánh, nhân hoá, ẩn dụ.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'So sánh dùng từ "như, tựa, giống". Nhân hoá gán đặc điểm con người cho sự vật. Ẩn dụ gọi tên sự vật này bằng tên sự vật khác.',
                ],
            ],
            // ================= TIẾNG ANH =================
            'kn-en-vocab-6-1' => [
                [
                    'title' => 'Family vocabulary', 'slug' => 'en-my-family',
                    'objective' => 'Hiểu nghĩa và ghi nhớ các từ vựng về gia đình (father, mother, brother, sister...).',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Ghép từ tiếng Anh với nghĩa tiếng Việt tương ứng. Học thuộc cách viết của từng từ.',
                ],
                [
                    'title' => 'At school vocabulary', 'slug' => 'en-at-school',
                    'objective' => 'Hiểu nghĩa và ghi nhớ các từ vựng về trường học (book, pen, classroom, teacher...).',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Phân loại các từ vào nhóm đồ dùng hoặc nhóm con người. Điền từ còn thiếu vào câu.',
                ],
            ],
            'kn-en-grammar-1' => [
                [
                    'title' => 'Present simple tense', 'slug' => 'en-present-simple',
                    'objective' => 'Chia đúng động từ ở thì hiện tại đơn với các chủ ngữ khác nhau.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Chủ ngữ he, she, it → động từ thêm s/es. Các chủ ngữ còn lại giữ nguyên động từ.',
                ],
                [
                    'title' => 'Prepositions of place', 'slug' => 'en-prepositions',
                    'objective' => 'Dùng đúng giới từ chỉ nơi chốn: in, on, under, behind, next to...',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'on = trên mặt phẳng, in = bên trong, under = phía dưới, behind = phía sau, next to = bên cạnh.',
                ],
            ],
            'kn-en-vocab-7-1' => [
                [
                    'title' => 'Health vocabulary', 'slug' => 'en-health',
                    'objective' => 'Hiểu nghĩa và ghi nhớ các từ vựng về sức khoẻ (headache, fever, healthy...).',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Ghép từ tiếng Anh với nghĩa tiếng Việt tương ứng. Phân biệt các từ chỉ bệnh và từ chỉ thể trạng.',
                ],
                [
                    'title' => 'Travel vocabulary', 'slug' => 'en-travel',
                    'objective' => 'Hiểu nghĩa và ghi nhớ các từ vựng về du lịch (ticket, suitcase, hotel, beach...).',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Điền từ còn thiếu vào câu về chuyến du lịch. Học thuộc các cụm từ thông dụng.',
                ],
            ],
            // ================= KHOA HỌC =================
            'kn-kh-co-the-1' => [
                [
                    'title' => 'Hệ xương của người', 'slug' => 'kh-he-xuong',
                    'objective' => 'Kể tên được một số xương chính và vai trò của hệ xương.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Hệ xương nâng đỡ cơ thể và bảo vệ các cơ quan bên trong. Ghi nhớ tên các xương chính.',
                ],
                [
                    'title' => 'Hệ tiêu hoá', 'slug' => 'kh-he-tieu-hoa',
                    'objective' => 'Mô tả được đường đi của thức ăn qua các cơ quan tiêu hoá.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                    'instructions' => 'Thức ăn đi theo thứ tự: miệng → thực quản → dạ dày → ruột non → ruột già.',
                ],
            ],
            'kn-kh-chat-1' => [
                [
                    'title' => 'Các trạng thái của chất', 'slug' => 'kh-trang-thai-chat',
                    'objective' => 'Nhận biết được ba trạng thái của chất: rắn, lỏng, khí.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Chất rắn có hình dạng cố định, chất lỏng chảy được, chất khí lan toả chiếm đầy không gian.',
                ],
                [
                    'title' => 'Nước quanh ta', 'slug' => 'kh-nuoc',
                    'objective' => 'Biết được tính chất của nước và vai trò của nước với sự sống.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Nước không màu, không mùi, không vị. Nhiệt độ sôi của nước là 100 độ C ở điều kiện thường.',
                ],
            ],
            'kn-kh-nang-luong-1' => [
                [
                    'title' => 'Nguồn năng lượng', 'slug' => 'kh-nguon-nang-luong',
                    'objective' => 'Phân biệt được năng lượng tái tạo và năng lượng không tái tạo.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                    'instructions' => 'Năng lượng tái tạo: mặt trời, gió, nước. Không tái tạo: than đá, dầu mỏ, khí đốt.',
                ],
                [
                    'title' => 'Điện và mạch điện', 'slug' => 'kh-dien',
                    'objective' => 'Biết các bộ phận của mạch điện đơn giản và tác dụng của dòng điện.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                    'instructions' => 'Mạch điện đơn giản gồm: nguồn điện, dây dẫn, bóng đèn và công tắc. Dòng điện có tác dụng làm nóng và phát sáng.',
                ],
            ],
            // ================= LỊCH SỬ =================
            'kn-ls-dung-nuoc-1' => [
                [
                    'title' => 'Nước Văn Lang', 'slug' => 'ls-nuoc-van-lang',
                    'objective' => 'Biết được nước Văn Lang – nhà nước đầu tiên của người Việt.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Nước Văn Lang do các vua Hùng dựng nên, kinh đô ở Phong Châu (Phú Thọ ngày nay).',
                ],
                [
                    'title' => 'Các anh hùng dân tộc', 'slug' => 'ls-anh-hung-dan-toc',
                    'objective' => 'Kể tên được các anh hùng chống ngoại xâm: Hai Bà Trưng, Bà Triệu, Ngô Quyền...',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Ghép tên anh hùng với chiến công tương ứng. Ghi nhớ thứ tự thời gian các sự kiện.',
                ],
            ],
            'kn-ls-dinh-tien-le-1' => [
                [
                    'title' => 'Đinh Bộ Lĩnh dẹp loạn 12 sứ quân', 'slug' => 'ls-dinh-bo-linh',
                    'objective' => 'Biết được công lao của Đinh Bộ Lĩnh trong việc thống nhất đất nước.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Đinh Bộ Lĩnh dẹp loạn 12 sứ quân, lập nước Đại Cồ Việt, đóng đô ở Hoa Lư.',
                ],
                [
                    'title' => 'Lê Hoàn và nhà Tiền Lê', 'slug' => 'ls-le-hoan',
                    'objective' => 'Biết được Lê Hoàn lên ngôi và chiến thắng quân Tống năm 981.',
                    'difficulty' => 'de', 'duration_minutes' => 10,
                    'instructions' => 'Lê Hoàn đánh tan quân Tống xâm lược năm 981, mở đầu triều đại nhà Tiền Lê.',
                ],
            ],
            'kn-ls-chong-nguyen-mong-1' => [
                [
                    'title' => 'Ba lần kháng chiến chống Nguyên – Mông', 'slug' => 'ls-dong-bo-dau',
                    'objective' => 'Kể được ba lần kháng chiến chống quân Nguyên – Mông thắng lợi.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Nhân dân Đại Việt đã ba lần đánh bại quân Nguyên – Mông (1258, 1285, 1287-1288).',
                ],
                [
                    'title' => 'Trần Hưng Đạo', 'slug' => 'ls-tran-hung-dao',
                    'objective' => 'Biết được vai trò của Trần Hưng Đạo trong kháng chiến chống Nguyên – Mông.',
                    'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                    'instructions' => 'Trần Hưng Đạo là tổng chỉ huy kháng chiến lần 2 và lần 3, tác giả Hịch tướng sĩ.',
                ],
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::tree() as $skillSlug => $lessons) {
            $skill = Skill::where('slug', $skillSlug)->firstOrFail();
            $order = 1;
            foreach ($lessons as $l) {
                Lesson::updateOrCreate(
                    ['slug' => $l['slug']],
                    [
                        'skill_id' => $skill->id,
                        'title' => $l['title'],
                        'objective' => $l['objective'],
                        'difficulty' => $l['difficulty'],
                        'duration_minutes' => $l['duration_minutes'],
                        'instructions' => $l['instructions'],
                        'sort_order' => $order++,
                        'status' => 'published',
                        'is_demo' => true,
                    ]
                );
            }
        }
    }
}
