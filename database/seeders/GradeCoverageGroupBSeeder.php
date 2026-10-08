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
 * Phủ dữ liệu theo khối lớp cho NHÓM B: Khoa học, Lịch sử, Ngữ văn, Địa lý.
 *
 * Mỗi topic (độ tuổi grade_min..grade_max) x mỗi lớp: 2 bài học mới,
 * mỗi bài >= 16 câu (4 quiz + 4 matching + 4 sort + 4 fill).
 * Slug bài: {topic-slug}-lop-{grade}-1 / {topic-slug}-lop-{grade}-2.
 * Bài gắn vào skill đầu tiên (sort_order nhỏ nhất) của topic.
 *
 * Nội dung TỰ VIẾT 100% tiếng Việt, bám chương trình đúng khối lớp,
 * độ khó tăng dần theo lớp. Idempotent: bài học bỏ qua khi slug đã tồn tại;
 * câu hỏi bỏ qua theo (lesson_id, game_type). Mỗi lesson và question đều
 * có cột grade (khối lớp) theo yêu cầu.
 */
class GradeCoverageGroupBSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];

    /**
     * Kế hoạch bài học: topic slug => danh sách bài.
     * Bài số (1) kiến thức nền của khối, bài (2) nâng cao / mở rộng.
     */
    private array $plan = [
        // ---------------- KHOA HỌC ----------------
        'kh-co-the-nguoi' => [
            ['slug' => 'kh-co-the-nguoi-lop-6-1', 'grade' => 6,
             'title' => 'Cơ thể người lớp 6: hệ xương (1)',
             'objective' => 'Kể được các bộ phận chính của hệ xương, nêu chức năng nâng đỡ – bảo vệ và cách giữ xương chắc khoẻ.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Cơ thể người có hơn 200 chiếc xương. Xương có chức năng nâng đỡ cơ thể và bảo vệ các cơ quan bên trong. Các khớp giúp xương cử động được. Muốn xương chắc khoẻ cần ăn đủ canxi, tắm nắng và vận động thường xuyên.'],
            ['slug' => 'kh-co-the-nguoi-lop-6-2', 'grade' => 6,
             'title' => 'Cơ thể người lớp 6: hệ tiêu hoá (2)',
             'objective' => 'Kể được đường đi của thức ăn, nêu vai trò của răng – dạ dày – ruột và thói quen ăn uống tốt.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Thức ăn đi qua miệng, thực quản, dạ dày rồi xuống ruột. Răng nhai nhỏ thức ăn, dạ dày co bóp và tiết dịch tiêu hoá, ruột non hấp thụ chất dinh dưỡng. Cần ăn chậm, nhai kĩ, ăn chín uống sôi.'],
            ['slug' => 'kh-co-the-nguoi-lop-7-1', 'grade' => 7,
             'title' => 'Cơ thể người lớp 7: hệ tuần hoàn và hô hấp (1)',
             'objective' => 'Nêu được vai trò của tim – máu – mạch máu, đường đi của không khí và ý nghĩa của hô hấp.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'Tim bơm máu đi khắp cơ thể qua mạch máu, máu mang ô-xy và chất dinh dưỡng nuôi tế bào. Không khí đi từ mũi qua khí quản vào phổi; hô hấp giúp lấy ô-xy, thải khí các-bô-níc.'],
            ['slug' => 'kh-co-the-nguoi-lop-7-2', 'grade' => 7,
             'title' => 'Cơ thể người lớp 7: hệ bài tiết và vệ sinh cơ thể (2)',
             'objective' => 'Kể được các cơ quan bài tiết, nêu vai trò của thận và thói quen giữ vệ sinh cơ thể.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Cơ thể bài tiết chất thải qua thận (nước tiểu), da (mồ hôi) và phổi (hơi thở). Thận lọc máu tạo nước tiểu. Cần uống đủ nước, ăn nhạt, giữ vệ sinh da và răng miệng hằng ngày.'],
        ],
        'kh-chat-quanh-ta' => [
            ['slug' => 'kh-chat-quanh-ta-lop-7-1', 'grade' => 7,
             'title' => 'Chất quanh ta lớp 7: các trạng thái của chất (1)',
             'objective' => 'Phân biệt ba trạng thái rắn – lỏng – khí, nêu sự chuyển thể khi nóng lên hoặc lạnh đi.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Chất tồn tại ở ba trạng thái: rắn, lỏng, khí. Khi đun nóng, chất rắn nóng chảy thành lỏng, chất lỏng bay hơi thành khí; khi làm lạnh, quá trình diễn ra ngược lại (ngưng tụ, đông đặc).'],
            ['slug' => 'kh-chat-quanh-ta-lop-7-2', 'grade' => 7,
             'title' => 'Chất quanh ta lớp 7: nước trong đời sống (2)',
             'objective' => 'Nêu vai trò của nước, vòng tuần hoàn của nước trong tự nhiên và cách tiết kiệm nước.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'Nước chiếm phần lớn cơ thể người và bề mặt Trái Đất. Nước bay hơi lên trời, ngưng tụ thành mây rồi rơi xuống thành mưa – đó là vòng tuần hoàn của nước. Cần tiết kiệm và giữ sạch nguồn nước.'],
            ['slug' => 'kh-chat-quanh-ta-lop-8-1', 'grade' => 8,
             'title' => 'Chất quanh ta lớp 8: hỗn hợp và dung dịch (1)',
             'objective' => 'Phân biệt chất nguyên chất với hỗn hợp, hỗn hợp đồng nhất với không đồng nhất; nêu các cách tách chất.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Hỗn hợp gồm hai hay nhiều chất trộn lẫn. Hỗn hợp đồng nhất (dung dịch) như nước đường; không đồng nhất như nước lẫn dầu. Tách chất bằng lọc, cô cạn, chưng cất tùy tính chất.'],
            ['slug' => 'kh-chat-quanh-ta-lop-8-2', 'grade' => 8,
             'title' => 'Chất quanh ta lớp 8: nguyên tử và phân tử (2)',
             'objective' => 'Mô tả cấu tạo nguyên tử (hạt nhân – electron), nêu khái niệm phân tử và ví dụ.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Mọi chất đều cấu tạo từ những hạt vô cùng nhỏ. Nguyên tử gồm hạt nhân (proton, nơtron) ở giữa và electron chuyển động xung quanh. Nhiều nguyên tử liên kết tạo thành phân tử, ví dụ phân tử nước H2O.'],
        ],
        'kh-nang-luong' => [
            ['slug' => 'kh-nang-luong-lop-8-1', 'grade' => 8,
             'title' => 'Năng lượng lớp 8: các dạng năng lượng (1)',
             'objective' => 'Kể được các dạng năng lượng, nêu sự chuyển hoá năng lượng và định luật bảo toàn năng lượng.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Năng lượng có nhiều dạng: động năng (do chuyển động), thế năng (do vị trí), nhiệt năng, hoá năng, điện năng. Năng lượng không tự sinh ra hay mất đi, chỉ chuyển từ dạng này sang dạng khác.'],
            ['slug' => 'kh-nang-luong-lop-8-2', 'grade' => 8,
             'title' => 'Năng lượng lớp 8: công và công suất (2)',
             'objective' => 'Tính được công cơ học và công suất trong trường hợp đơn giản, biết đơn vị jun và oát.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Khi lực tác dụng làm vật chuyển dời, lực đã sinh công: A = F × s, đơn vị là jun (J). Công thực hiện trong một giây gọi là công suất: P = A / t, đơn vị là oát (W). Máy nào công suất lớn thì làm việc khoẻ hơn.'],
            ['slug' => 'kh-nang-luong-lop-9-1', 'grade' => 9,
             'title' => 'Năng lượng lớp 9: dòng điện và mạch điện (1)',
             'objective' => 'Nêu khái niệm dòng điện, cường độ dòng điện, hiệu điện thế và điều kiện có dòng điện trong mạch.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Dòng điện là dòng chuyển dời có hướng của các điện tích. Cường độ dòng điện đo bằng ampe kế, đơn vị ampe (A); hiệu điện thế đo bằng vôn kế, đơn vị vôn (V). Muốn có dòng điện, mạch phải kín và có nguồn điện.'],
            ['slug' => 'kh-nang-luong-lop-9-2', 'grade' => 9,
             'title' => 'Năng lượng lớp 9: điện năng và an toàn điện (2)',
             'objective' => 'Tính công suất điện và điện năng tiêu thụ, nêu các quy tắc an toàn khi dùng điện.',
             'difficulty' => 'kho', 'duration_minutes' => 12,
             'instructions' => 'Công suất điện P = U × I, đơn vị oát (W). Điện năng tiêu thụ tính bằng ki-lô-oát giờ (kWh), tức "số điện". Dùng điện phải an toàn: không chạm tay ướt vào ổ điện, ngắt điện trước khi sửa chữa, dùng dây dẫn đúng tiêu chuẩn.'],
        ],
        // ---------------- LỊCH SỬ ----------------
        'ls-dung-nuoc' => [
            ['slug' => 'ls-dung-nuoc-lop-6-1', 'grade' => 6,
             'title' => 'Lịch sử lớp 6: nước Văn Lang (1)',
             'objective' => 'Nêu được thời gian, người đứng đầu, kinh đô và tổ chức của nước Văn Lang – nhà nước đầu tiên của ta.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Khoảng thế kỉ VII TCN, các bộ lạc hợp nhất thành nước Văn Lang do Hùng Vương đứng đầu, kinh đô ở Phong Châu (Phú Thọ ngày nay). Cả nước chia thành 15 bộ. Đây là nhà nước đầu tiên trong lịch sử Việt Nam.'],
            ['slug' => 'ls-dung-nuoc-lop-6-2', 'grade' => 6,
             'title' => 'Lịch sử lớp 6: An Dương Vương và nước Âu Lạc (2)',
             'objective' => 'Kể được sự ra đời của nước Âu Lạc, thành Cổ Loa và truyền thuyết nỏ thần.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Năm 208 TCN, Thục Phán – An Dương Vương hợp nhất Âu Việt và Lạc Việt thành nước Âu Lạc, đóng đô ở Cổ Loa (Hà Nội). Ông cho xây thành Cổ Loa hình xoáy ốc và chế tạo nỏ thần bắn một lúc nhiều mũi tên.'],
            ['slug' => 'ls-dung-nuoc-lop-7-1', 'grade' => 7,
             'title' => 'Lịch sử lớp 7: khởi nghĩa Hai Bà Trưng (1)',
             'objective' => 'Trình bày nguyên nhân, diễn biến chính và kết quả của cuộc khởi nghĩa Hai Bà Trưng năm 40.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'Năm 40, Trưng Trắc và Trưng Nhị phất cờ khởi nghĩa ở Mê Linh chống ách đô hộ nhà Hán. Nghĩa quân nhanh chóng làm chủ cả nước, Trưng Trắc lên làm vua. Năm 43, nhà Hán cử Mã Viện sang đàn áp, cuộc khởi nghĩa thất bại nhưng nêu cao tinh thần yêu nước.'],
            ['slug' => 'ls-dung-nuoc-lop-7-2', 'grade' => 7,
             'title' => 'Lịch sử lớp 7: Ngô Quyền và chiến thắng Bạch Đằng (2)',
             'objective' => 'Trình bày trận Bạch Đằng năm 938 và ý nghĩa mở đầu thời kì độc lập của dân tộc.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Năm 938, Ngô Quyền đóng cọc gỗ đầu bịt sắt xuống lòng sông Bạch Đằng, dụ quân Nam Hán vào trận địa lúc thuỷ triều lên rồi đánh úp khi nước rút. Quân giặc tan vỡ, chấm dứt hơn một nghìn năm Bắc thuộc, mở đầu thời kì độc lập tự chủ.'],
        ],
        'ls-dinh-tien-le' => [
            ['slug' => 'ls-dinh-tien-le-lop-7-1', 'grade' => 7,
             'title' => 'Lịch sử lớp 7: Đinh Bộ Lĩnh dẹp loạn 12 sứ quân (1)',
             'objective' => 'Trình bày quá trình Đinh Bộ Lĩnh thống nhất đất nước và sự ra đời của nước Đại Cồ Việt.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Sau khi Ngô Quyền mất, đất nước rơi vào loạn 12 sứ quân. Đinh Bộ Lĩnh dẹp loạn, thống nhất đất nước, năm 968 lên ngôi hoàng đế, đặt tên nước là Đại Cồ Việt, đóng đô ở Hoa Lư (Ninh Bình), lấy niên hiệu Thái Bình.'],
            ['slug' => 'ls-dinh-tien-le-lop-7-2', 'grade' => 7,
             'title' => 'Lịch sử lớp 7: Lê Hoàn và kháng chiến chống Tống (2)',
             'objective' => 'Trình bày sự ra đời của nhà Tiền Lê và chiến thắng chống quân Tống năm 981.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'Năm 980, trước nguy cơ quân Tống xâm lược, Lê Hoàn – Thập đạo tướng quân được tôn lên làm vua, lập nhà Tiền Lê. Năm 981, ông chỉ huy quân dân đánh tan quân Tống trên sông Bạch Đằng và ở Chi Lăng, bảo vệ vững chắc nền độc lập.'],
            ['slug' => 'ls-dinh-tien-le-lop-8-1', 'grade' => 8,
             'title' => 'Lịch sử lớp 8: tổ chức nhà nước Đinh – Tiền Lê (1)',
             'objective' => 'Nêu được bộ máy nhà nước thời Đinh – Tiền Lê: vua, quan văn – võ, quân đội và luật pháp.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Nhà nước Đinh – Tiền Lê do vua đứng đầu, giúp việc có thái sư, đại tổng quản; chia quan văn, quan võ. Quân đội gồm cấm quân bảo vệ kinh thành và quân các lộ. Nhà Đinh ban hành hình luật để giữ kỉ cương – bước đầu xây dựng nhà nước phong kiến độc lập.'],
            ['slug' => 'ls-dinh-tien-le-lop-8-2', 'grade' => 8,
             'title' => 'Lịch sử lớp 8: kinh tế và văn hoá thời Đinh – Tiền Lê (2)',
             'objective' => 'Nêu nét chính về nông nghiệp, thủ công nghiệp, tiền tệ và đời sống văn hoá thời Đinh – Tiền Lê.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Thời Đinh – Tiền Lê, nông nghiệp là gốc: nhà nước khuyến khích khai hoang, đắp đê. Thủ công nghiệp phát triển, đúc tiền đồng Thái Bình – đồng tiền đầu tiên của nước ta. Phật giáo thịnh hành, nhiều chùa tháp được xây dựng.'],
        ],
        'ls-chong-nguyen-mong' => [
            ['slug' => 'ls-chong-nguyen-mong-lop-8-1', 'grade' => 8,
             'title' => 'Lịch sử lớp 8: ba lần kháng chiến chống quân Nguyên – Mông (1)',
             'objective' => 'Nêu được thời gian, đường lối kháng chiến và kết quả của ba lần đánh quân Nguyên – Mông.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Quân Mông – Nguyên ba lần xâm lược Đại Việt (1258, 1285, 1287–1288) đều bị đánh bại. Nhà Trần thực hiện "vườn không nhà trống", rút lui bảo toàn lực lượng rồi phản công. Hội nghị Diên Hồng thể hiện quyết tâm "đánh" của toàn dân.'],
            ['slug' => 'ls-chong-nguyen-mong-lop-8-2', 'grade' => 8,
             'title' => 'Lịch sử lớp 8: trận Bạch Đằng năm 1288 (2)',
             'objective' => 'Trình bày trận Bạch Đằng 1288: kế sách của Trần Hưng Đạo, diễn biến và kết quả.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Năm 1288, Trần Hưng Đạo học kế của Ngô Quyền: đóng cọc gỗ xuống sông Bạch Đằng, nhử quân Nguyên vào lúc nước lên rồi đánh úp khi nước rút. Ô Mã Nhi bị bắt sống, Thoát Hoan chui ống đồng chạy trốn. Chiến thắng kết thúc cuộc kháng chiến.'],
            ['slug' => 'ls-chong-nguyen-mong-lop-9-1', 'grade' => 9,
             'title' => 'Lịch sử lớp 9: Trần Hưng Đạo và Hịch tướng sĩ (1)',
             'objective' => 'Nêu công lao của Trần Hưng Đạo, nội dung và ý nghĩa của Hịch tướng sĩ.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Trần Hưng Đạo (Trần Quốc Tuấn) là tổng chỉ huy kháng chiến chống Nguyên – Mông. Khoảng năm 1284, ông viết Hịch tướng sĩ kêu gọi tướng sĩ đoàn kết đánh giặc, thể hiện lòng yêu nước nồng nàn. Ông còn soạn Binh thư yếu lược, Vạn Kiếp tông bí truyền thư.'],
            ['slug' => 'ls-chong-nguyen-mong-lop-9-2', 'grade' => 9,
             'title' => 'Lịch sử lớp 9: ý nghĩa lịch sử của kháng chiến chống Nguyên – Mông (2)',
             'objective' => 'Đánh giá ý nghĩa lịch sử và rút ra bài học từ thắng lợi của ba lần kháng chiến.',
             'difficulty' => 'kho', 'duration_minutes' => 12,
             'instructions' => 'Thắng lợi đập tan tham vọng xâm lược của đế quốc Nguyên – Mông, bảo vệ vững chắc nền độc lập và toàn vẹn lãnh thổ. Bài học: đoàn kết toàn dân, đường lối kháng chiến đúng đắn, nghệ thuật quân sự "lấy yếu chống mạnh, lấy ít địch nhiều" của nhà Trần.'],
        ],
        // ---------------- NGỮ VĂN ----------------
        'nv-tu-loai' => [
            ['slug' => 'nv-tu-loai-lop-6-1', 'grade' => 6,
             'title' => 'Ngữ văn lớp 6: danh từ (1)',
             'objective' => 'Nhận biết danh từ, phân biệt danh từ chung – danh từ riêng và cấu tạo cụm danh từ.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Danh từ là từ chỉ người, vật, hiện tượng, khái niệm. Danh từ chung chỉ loại sự vật (học sinh, con sông); danh từ riêng chỉ tên riêng (Hà Nội, sông Hồng) và viết hoa. Cụm danh từ gồm danh từ trung tâm và các thành tố phụ (ví dụ: "những bông hoa đẹp").'],
            ['slug' => 'nv-tu-loai-lop-6-2', 'grade' => 6,
             'title' => 'Ngữ văn lớp 6: động từ và tính từ (2)',
             'objective' => 'Nhận biết động từ, tính từ; biết cấu tạo cụm động từ và cụm tính từ.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Động từ chỉ hành động, trạng thái của sự vật (chạy, đọc, ngủ). Tính từ chỉ đặc điểm, tính chất (đẹp, cao, nhanh). Cụm động từ và cụm tính từ mở rộng ý nghĩa cho từ trung tâm, ví dụ: "đang chạy rất nhanh", "rất đẹp và hiền".'],
            ['slug' => 'nv-tu-loai-lop-7-1', 'grade' => 7,
             'title' => 'Ngữ văn lớp 7: nhận diện từ loại trong câu (1)',
             'objective' => 'Xác định đúng từ loại của từ trong ngữ cảnh câu cụ thể, tránh nhầm lẫn.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'Một từ có thể thuộc từ loại khác nhau tùy ngữ cảnh: "đá" trong "quả bóng đá" là danh từ, trong "anh ấy đá bóng" là động từ. Muốn xác định đúng phải đặt từ vào câu và xét nghĩa, chức năng của nó.'],
            ['slug' => 'nv-tu-loai-lop-7-2', 'grade' => 7,
             'title' => 'Ngữ văn lớp 7: lượng từ và quan hệ từ (2)',
             'objective' => 'Nhận biết lượng từ (những, các, mỗi) và quan hệ từ (và, với, nhưng, của).',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Lượng từ chỉ lượng ít nhiều của sự vật: những, các (số nhiều), mỗi, một (số ít). Quan hệ từ nối các từ ngữ trong câu, biểu thị quan hệ: và, với, cùng (nối), nhưng, mà (tương phản), của, ở, từ (quan hệ sở hữu, nơi chốn).'],
        ],
        'nv-bien-phap-tu-tu' => [
            ['slug' => 'nv-bien-phap-tu-tu-lop-7-1', 'grade' => 7,
             'title' => 'Ngữ văn lớp 7: so sánh (1)',
             'objective' => 'Nhận biết phép so sánh, chỉ ra các vế và từ so sánh, nêu tác dụng.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'So sánh là đối chiếu sự vật này với sự vật khác có nét tương đồng để làm nổi bật hình ảnh. Mô hình: A như B (ví dụ: "Trẻ em như búp trên cành"). Từ so sánh thường gặp: như, tựa, bằng, là. So sánh có hai kiểu: ngang bằng và không ngang bằng.'],
            ['slug' => 'nv-bien-phap-tu-tu-lop-7-2', 'grade' => 7,
             'title' => 'Ngữ văn lớp 7: nhân hoá (2)',
             'objective' => 'Nhận biết phép nhân hoá, nêu các cách nhân hoá và tác dụng.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'Nhân hoá là gán đặc điểm của con người (hành động, tình cảm, cách xưng hô) cho sự vật để chúng trở nên sinh động, gần gũi. Cách nhân hoá: dùng từ chỉ người gọi vật, dùng động từ/tính từ của người cho vật, trò chuyện với vật như với người.'],
            ['slug' => 'nv-bien-phap-tu-tu-lop-8-1', 'grade' => 8,
             'title' => 'Ngữ văn lớp 8: ẩn dụ (1)',
             'objective' => 'Nhận biết ẩn dụ, phân biệt bốn kiểu ẩn dụ và nêu tác dụng.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Ẩn dụ là gọi tên sự vật này bằng tên sự vật khác có nét tương đồng, nhưng lược bỏ từ so sánh. Bốn kiểu: ẩn dụ hình thức (về hình dạng), cách thức (về cách tiến hành), phẩm chất (về tính chất), chuyển đổi cảm giác (từ giác quan này sang giác quan khác).'],
            ['slug' => 'nv-bien-phap-tu-tu-lop-8-2', 'grade' => 8,
             'title' => 'Ngữ văn lớp 8: hoán dụ (2)',
             'objective' => 'Nhận biết hoán dụ, phân biệt hoán dụ với ẩn dụ.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Hoán dụ là gọi tên sự vật bằng tên sự vật khác có quan hệ gần gũi: lấy bộ phận chỉ toàn thể ("bàn tay" chỉ người lao động), vật chứa chỉ vật bị chứa ("nhà" chỉ gia đình), dấu hiệu chỉ sự vật ("áo nâu" chỉ người nông dân). Ẩn dụ dựa trên tương đồng, hoán dụ dựa trên gần gũi.'],
        ],
        'nv-van-mieu-ta' => [
            ['slug' => 'nv-van-mieu-ta-lop-8-1', 'grade' => 8,
             'title' => 'Ngữ văn lớp 8: bài văn miêu tả (1)',
             'objective' => 'Nêu bố cục bài văn miêu tả, cách quan sát và trình tự miêu tả.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Bài văn miêu tả gồm ba phần: mở bài (giới thiệu đối tượng), thân bài (miêu tả chi tiết), kết bài (cảm nghĩ). Muốn tả hay phải quan sát kĩ bằng nhiều giác quan và sắp xếp theo trình tự hợp lí: từ bao quát đến chi tiết hoặc theo trình tự không gian, thời gian.'],
            ['slug' => 'nv-van-mieu-ta-lop-8-2', 'grade' => 8,
             'title' => 'Ngữ văn lớp 8: dấu câu trong văn bản (2)',
             'objective' => 'Dùng đúng dấu phẩy, dấu chấm, dấu hai chấm, dấu chấm hỏi – chấm than và dấu ngoặc kép.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Dấu phẩy ngăn cách các bộ phận trong câu; dấu chấm kết thúc câu kể; dấu hai chấm báo hiệu lời giải thích hoặc lời dẫn; dấu chấm hỏi dùng cuối câu hỏi, dấu chấm than cuối câu cảm thán; dấu ngoặc kép đánh dấu lời nói trực tiếp hoặc từ ngữ được trích dẫn.'],
            ['slug' => 'nv-van-mieu-ta-lop-9-1', 'grade' => 9,
             'title' => 'Ngữ văn lớp 9: văn tả người (1)',
             'objective' => 'Nêu yêu cầu của bài văn tả người: tả chân dung, tả hoạt động, kết hợp kể và biểu cảm.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Tả người cần làm nổi bật chân dung (hình dáng, khuôn mặt, trang phục) và hoạt động, việc làm tiêu biểu. Chọn chi tiết tiêu biểu, kết hợp kể chuyện và bộc lộ tình cảm, tránh liệt kê chung chung.'],
            ['slug' => 'nv-van-mieu-ta-lop-9-2', 'grade' => 9,
             'title' => 'Ngữ văn lớp 9: văn tả cảnh, tả vật (2)',
             'objective' => 'Nêu yêu cầu của bài văn tả cảnh, tả con vật, đồ vật: trình tự, chi tiết và cảm xúc.',
             'difficulty' => 'kho', 'duration_minutes' => 12,
             'instructions' => 'Tả cảnh cần bao quát toàn cảnh rồi đi vào chi tiết theo trình tự không gian hoặc thời gian, lồng cảm xúc của người viết. Tả con vật, đồ vật cần nêu hình dáng, đặc điểm nổi bật và ích lợi; dùng so sánh, nhân hoá để bài văn sinh động.'],
        ],
        // ---------------- ĐỊA LÝ ----------------
        'dl-chau-luc-dai-duong' => [
            ['slug' => 'dl-chau-luc-dai-duong-lop-6-1', 'grade' => 6,
             'title' => 'Địa lý lớp 6: châu Á (1)',
             'objective' => 'Nêu vị trí, diện tích, đặc điểm địa hình và dân cư của châu Á.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Châu Á là châu lục lớn nhất thế giới, nằm chủ yếu ở bán cầu Bắc. Địa hình đa dạng: có dãy Hi-ma-lay-a với đỉnh Ê-vơ-rét cao nhất thế giới, nhiều đồng bằng và hoang mạc rộng lớn. Châu Á cũng đông dân nhất, tập trung ở Đông Á và Nam Á.'],
            ['slug' => 'dl-chau-luc-dai-duong-lop-6-2', 'grade' => 6,
             'title' => 'Địa lý lớp 6: các châu lục khác (2)',
             'objective' => 'Kể tên và nêu nét nổi bật của châu Âu, châu Phi, châu Mỹ, châu Đại Dương, châu Nam Cực.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Ngoài châu Á còn 6 châu lục: châu Âu (kinh tế phát triển), châu Phi (hoang mạc Xa-ha-ra, sông Nin), Bắc Mỹ và Nam Mỹ, châu Đại Dương (nhỏ nhất) và châu Nam Cực (lạnh nhất, quanh năm băng tuyết).'],
            ['slug' => 'dl-chau-luc-dai-duong-lop-7-1', 'grade' => 7,
             'title' => 'Địa lý lớp 7: châu Âu (1)',
             'objective' => 'Nêu đặc điểm địa hình, khí hậu và kinh tế của châu Âu.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'Châu Âu có địa hình chủ yếu là đồng bằng và núi thấp, khí hậu ôn hoà nhờ dòng biển nóng. Đây là châu lục có nền kinh tế phát triển cao, công nghiệp và dịch vụ hiện đại, nhiều nước thuộc Liên minh châu Âu (EU).'],
            ['slug' => 'dl-chau-luc-dai-duong-lop-7-2', 'grade' => 7,
             'title' => 'Địa lý lớp 7: châu Phi và châu Mỹ (2)',
             'objective' => 'Nêu nét nổi bật về tự nhiên và dân cư của châu Phi và châu Mỹ.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Châu Phi có hoang mạc Xa-ha-ra lớn nhất thế giới, sông Nin dài nhất thế giới, khí hậu nóng. Châu Mỹ gồm Bắc Mỹ (kinh tế phát triển) và Nam Mỹ (rừng A-ma-dôn rộng lớn, dãy An-đét dài nhất thế giới).'],
        ],
        'dl-thu-do-cac-nuoc' => [
            ['slug' => 'dl-thu-do-cac-nuoc-lop-7-1', 'grade' => 7,
             'title' => 'Địa lý lớp 7: thủ đô Đông Nam Á (1)',
             'objective' => 'Nhớ thủ đô của các nước Đông Nam Á, đặc biệt các nước láng giềng.',
             'difficulty' => 'de', 'duration_minutes' => 10,
             'instructions' => 'Đông Nam Á có 11 nước. Thủ đô các nước tiêu biểu: Việt Nam – Hà Nội, Lào – Viêng Chăn, Campuchia – Phnom Penh, Thái Lan – Bangkok, Myanmar – Naypyidaw, Indonesia – Jakarta, Malaysia – Kuala Lumpur, Singapore – Singapore, Philippines – Manila, Brunei – Bandar Seri Begawan, Đông Timor – Dili.'],
            ['slug' => 'dl-thu-do-cac-nuoc-lop-7-2', 'grade' => 7,
             'title' => 'Địa lý lớp 7: thủ đô châu Á (2)',
             'objective' => 'Nhớ thủ đô các nước Đông Á, Nam Á và Tây Á tiêu biểu.',
             'difficulty' => 'de', 'duration_minutes' => 12,
             'instructions' => 'Đông Á: Nhật Bản – Tokyo, Trung Quốc – Bắc Kinh, Hàn Quốc – Seoul, Mông Cổ – Ulan Bator. Nam Á: Ấn Độ – New Delhi. Tây Á: Thổ Nhĩ Kỳ – Ankara, Ả Rập Xê Út – Riyadh. Học theo nhóm vùng sẽ dễ nhớ hơn.'],
            ['slug' => 'dl-thu-do-cac-nuoc-lop-8-1', 'grade' => 8,
             'title' => 'Địa lý lớp 8: thủ đô châu Âu (1)',
             'objective' => 'Nhớ thủ đô các nước châu Âu tiêu biểu và tránh nhầm lẫn thường gặp.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Châu Âu: Pháp – Paris, Đức – Berlin, Ý – Rome, Anh – London, Nga – Mát-xcơ-va, Tây Ban Nha – Madrid, Hà Lan – Amsterdam, Thuỵ Sĩ – Bern. Chú ý: thủ đô Thổ Nhĩ Kỳ là Ankara (không phải Istanbul).'],
            ['slug' => 'dl-thu-do-cac-nuoc-lop-8-2', 'grade' => 8,
             'title' => 'Địa lý lớp 8: thủ đô châu Mỹ, châu Phi, châu Đại Dương (2)',
             'objective' => 'Nhớ thủ đô các nước tiêu biểu ở châu Mỹ, châu Phi, châu Đại Dương; tránh nhầm thành phố lớn với thủ đô.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Châu Mỹ: Mỹ – Washington D.C., Canada – Ottawa, Bra-xin – Brasilia, Argentina – Buenos Aires. Châu Phi: Ai Cập – Cairo, Nam Phi – Pretoria. Châu Đại Dương: Úc – Canberra, New Zealand – Wellington. Nhiều thành phố nổi tiếng (New York, Sydney) không phải thủ đô.'],
        ],
        'dl-dia-hinh-viet-nam' => [
            ['slug' => 'dl-dia-hinh-viet-nam-lop-8-1', 'grade' => 8,
             'title' => 'Địa lý lớp 8: vùng núi và trung du Việt Nam (1)',
             'objective' => 'Nêu bốn vùng núi chính, dãy Hoàng Liên Sơn, đỉnh Fansipan và vùng trung du.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Địa hình Việt Nam 3/4 là đồi núi, gồm bốn vùng: Tây Bắc (dãy Hoàng Liên Sơn, đỉnh Fansipan 3 143 m), Đông Bắc, Trường Sơn Bắc và Trường Sơn Nam. Vùng trung du Bắc Bộ nằm giữa miền núi và đồng bằng, đất feralit thích hợp trồng cây công nghiệp.'],
            ['slug' => 'dl-dia-hinh-viet-nam-lop-8-2', 'grade' => 8,
             'title' => 'Địa lý lớp 8: đồng bằng và ven biển Việt Nam (2)',
             'objective' => 'Nêu hai đồng bằng châu thổ lớn, đường bờ biển, các đảo và quần đảo của Việt Nam.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Việt Nam có hai đồng bằng châu thổ lớn: đồng bằng sông Hồng và đồng bằng sông Cửu Long – vựa lúa của cả nước. Đường bờ biển dài hơn 3 260 km với nhiều vũng vịnh, đảo lớn (Phú Quốc, Cát Bà) và hai quần đảo Hoàng Sa, Trường Sa.'],
            ['slug' => 'dl-dia-hinh-viet-nam-lop-9-1', 'grade' => 9,
             'title' => 'Địa lý lớp 9: tài nguyên khoáng sản Việt Nam (1)',
             'objective' => 'Kể tên các loại khoáng sản chính, nơi phân bố và ý nghĩa kinh tế.',
             'difficulty' => 'trung_binh', 'duration_minutes' => 12,
             'instructions' => 'Việt Nam giàu khoáng sản: than đá tập trung ở Quảng Ninh, dầu khí ở thềm lục địa phía Nam, bô-xít ở Tây Nguyên, sắt ở Thái Nguyên, a-pa-tít ở Lào Cai. Khoáng sản là nguyên liệu quan trọng cho công nghiệp nhưng cần khai thác hợp lí, tiết kiệm.'],
            ['slug' => 'dl-dia-hinh-viet-nam-lop-9-2', 'grade' => 9,
             'title' => 'Địa lý lớp 9: bảo vệ tài nguyên và môi trường (2)',
             'objective' => 'Nêu thực trạng suy giảm tài nguyên, các biện pháp bảo vệ và ứng phó biến đổi khí hậu.',
             'difficulty' => 'kho', 'duration_minutes' => 12,
             'instructions' => 'Tài nguyên đang suy giảm do khai thác quá mức: rừng bị thu hẹp, đất bạc màu, ô nhiễm nguồn nước. Cần trồng rừng, xử lí chất thải, tiết kiệm năng lượng. Việt Nam là một trong những nước chịu ảnh hưởng nặng của biến đổi khí hậu: nước biển dâng đe doạ đồng bằng sông Cửu Long.'],
        ],
    ];

    public function run(): void
    {
        // 1) Tạo bài học (idempotent theo slug), gắn vào skill đầu tiên của topic.
        foreach ($this->plan as $topicSlug => $defs) {
            $topic = Topic::where('slug', $topicSlug)->firstOrFail();
            $skill = Skill::where('topic_id', $topic->id)->orderBy('sort_order')->firstOrFail();
            $order = (int) (Lesson::where('skill_id', $skill->id)->max('sort_order') ?? 0);
            foreach ($defs as $def) {
                if (Lesson::where('slug', $def['slug'])->exists()) {
                    continue;
                }
                Lesson::create([
                    'skill_id'         => $skill->id,
                    'title'            => $def['title'],
                    'slug'             => $def['slug'],
                    'objective'        => $def['objective'],
                    'difficulty'       => $def['difficulty'],
                    'duration_minutes' => $def['duration_minutes'],
                    'instructions'     => $def['instructions'],
                    'sort_order'       => ++$order,
                    'status'           => 'published',
                    'grade'            => $def['grade'],
                    'is_demo'          => true,
                ]);
            }
        }

        // 2) Câu hỏi cho từng bài.
        $this->seedKhCoTheNguoi();
        $this->seedKhChatQuanhTa();
        $this->seedKhNangLuong();
        $this->seedLsDungNuoc();
        $this->seedLsDinhTienLe();
        $this->seedLsChongNguyenMong();
        $this->seedNvTuLoai();
        $this->seedNvBienPhapTuTu();
        $this->seedNvVanMieuTa();
        $this->seedDlChauLucDaiDuong();
        $this->seedDlThuDoCacNuoc();
        $this->seedDlDiaHinhVietNam();
    }

    // ---------------- helpers ----------------

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

    // ================= KHOA HỌC: CƠ THỂ NGƯỜI =================

    private function seedKhCoTheNguoi(): void
    {
        $this->seedKhCoTheNguoi61();
        $this->seedKhCoTheNguoi62();
        $this->seedKhCoTheNguoi71();
        $this->seedKhCoTheNguoi72();
    }

    private function seedKhCoTheNguoi61(): void
    {
        $L = 'kh-co-the-nguoi-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cơ thể người trưởng thành có khoảng bao nhiêu chiếc xương?',
                ['Hơn 200 chiếc', 'Hơn 100 chiếc', 'Hơn 500 chiếc', 'Hơn 50 chiếc'], 0,
                'Cơ thể người trưởng thành có hơn 200 chiếc xương tạo thành bộ xương.');
            $this->quiz($L, 'Chức năng chính của xương là gì?',
                ['Nâng đỡ cơ thể và bảo vệ các cơ quan', 'Tiêu hoá thức ăn', 'Bơm máu đi khắp cơ thể', 'Hô hấp lấy ô-xy'], 0,
                'Xương nâng đỡ cơ thể, tạo khung cho cơ bám và bảo vệ các cơ quan bên trong như não, tim, phổi.');
            $this->quiz($L, 'Bộ phận nào giúp các xương cử động được với nhau?',
                ['Khớp xương', 'Cơ bắp', 'Gân', 'Da'], 0,
                'Khớp là chỗ nối giữa các xương, giúp xương cử động linh hoạt.');
            $this->quiz($L, 'Để xương chắc khoẻ, chúng ta cần bổ sung chất nào?',
                ['Canxi', 'Đường', 'Muối', 'Dầu mỡ'], 0,
                'Canxi là thành phần chính của xương; sữa, tôm cua nhỏ và tắm nắng giúp xương chắc khoẻ.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi bộ phận với chức năng của nó.',
                [['Xương sọ', 'Bảo vệ não'],
                 ['Xương sườn', 'Bảo vệ tim và phổi'],
                 ['Xương chân', 'Nâng đỡ và giúp đi lại'],
                 ['Cột sống', 'Nâng đỡ cơ thể, bảo vệ tuỷ sống']],
                'Xương sọ bảo vệ não, xương sườn bảo vệ tim phổi, xương chân giúp đi lại, cột sống nâng đỡ cơ thể.');
            $this->matching($L, 'Nối mỗi loại khớp với ví dụ.',
                [['Khớp vai', 'Xoay được nhiều hướng'],
                 ['Khớp gối', 'Gập – duỗi'],
                 ['Khớp cổ tay', 'Xoay, gập linh hoạt'],
                 ['Khớp hộp sọ', 'Bất động, bảo vệ não']],
                'Khớp vai xoay nhiều hướng, khớp gối gập duỗi, khớp hộp sọ bất động để bảo vệ não.');
            $this->matching($L, 'Nối mỗi thói quen với tác dụng của nó.',
                [['Uống sữa', 'Bổ sung canxi cho xương'],
                 ['Tắm nắng buổi sáng', 'Giúp cơ thể hấp thụ canxi tốt hơn'],
                 ['Chơi thể thao', 'Xương và cơ phát triển khoẻ'],
                 ['Ngồi sai tư thế', 'Dễ cong vẹo cột sống']],
                'Uống sữa bổ sung canxi, tắm nắng giúp hấp thụ canxi, vận động giúp xương chắc, ngồi sai tư thế hại cột sống.');
            $this->matching($L, 'Nối mỗi bộ phận với nhóm của nó.',
                [['Xương cánh tay', 'Xương chi'],
                 ['Xương sườn', 'Xương thân mình'],
                 ['Xương sọ', 'Xương đầu'],
                 ['Xương chậu', 'Xương thân mình']],
                'Bộ xương gồm xương đầu, xương thân mình và xương chi (tay, chân).');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm THUỘC HỆ XƯƠNG hoặc KHÔNG THUỘC HỆ XƯƠNG.',
                [['Xương đùi', 'Thuộc hệ xương'], ['Khớp gối', 'Thuộc hệ xương'],
                 ['Tim', 'Không thuộc hệ xương'], ['Dạ dày', 'Không thuộc hệ xương']],
                'Xương đùi và khớp gối thuộc hệ xương; tim thuộc hệ tuần hoàn, dạ dày thuộc hệ tiêu hoá.');
            $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm TỐT CHO XƯƠNG hoặc HẠI CHO XƯƠNG.',
                [['Uống sữa mỗi ngày', 'Tốt cho xương'], ['Chơi thể thao', 'Tốt cho xương'],
                 ['Ngồi gù lưng', 'Hại cho xương'], ['Mang cặp quá nặng một bên vai', 'Hại cho xương']],
                'Uống sữa và vận động tốt cho xương; ngồi gù lưng, mang cặp lệch vai hại cột sống.');
            $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm BẢO VỆ CƠ QUAN BÊN TRONG hoặc GIÚP VẬN ĐỘNG.',
                [['Xương sọ', 'Bảo vệ cơ quan bên trong'], ['Xương sườn', 'Bảo vệ cơ quan bên trong'],
                 ['Xương cánh tay', 'Giúp vận động'], ['Xương cẳng chân', 'Giúp vận động']],
                'Xương sọ, xương sườn bảo vệ não, tim, phổi; xương tay chân giúp vận động.');
            $this->sortQ($L, 'Kéo mỗi thực phẩm vào nhóm GIÀU CANXI hoặc ÍT CANXI.',
                [['Sữa', 'Giàu canxi'], ['Tôm, cua nhỏ ăn cả vỏ', 'Giàu canxi'],
                 ['Kẹo ngọt', 'Ít canxi'], ['Nước ngọt có ga', 'Ít canxi']],
                'Sữa và tôm cua nhỏ giàu canxi, tốt cho xương; kẹo và nước ngọt ít canxi.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cơ thể người có hơn 200 chiếc ___.', [[0, 'xương']],
                'Hơn 200 chiếc xương liên kết với nhau tạo thành bộ xương.');
            $this->fill($L, 'Chỗ nối giữa các xương giúp cử động được gọi là ___.', [[0, 'khớp']],
                'Nhờ các khớp mà tay chân ta cử động linh hoạt.');
            $this->fill($L, 'Xương sọ có chức năng bảo vệ ___.', [[0, 'não']],
                'Hộp sọ cứng chắc bảo vệ bộ não bên trong.');
            $this->fill($L, 'Chất dinh dưỡng giúp xương chắc khoẻ là ___.', [[0, 'canxi']],
                'Sữa và tôm cua là nguồn canxi tốt cho xương.');
        }
    }

    private function seedKhCoTheNguoi62(): void
    {
        $L = 'kh-co-the-nguoi-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thức ăn sau khi qua miệng sẽ đi tới đâu tiếp theo?',
                ['Thực quản', 'Dạ dày', 'Ruột non', 'Ruột già'], 0,
                'Thức ăn từ miệng qua thực quản rồi mới xuống dạ dày.');
            $this->quiz($L, 'Cơ quan nào co bóp và tiết dịch để tiêu hoá thức ăn?',
                ['Dạ dày', 'Gan', 'Tim', 'Phổi'], 0,
                'Dạ dày co bóp nhào trộn thức ăn và tiết dịch vị để tiêu hoá.');
            $this->quiz($L, 'Chất dinh dưỡng được hấp thụ chủ yếu ở đâu?',
                ['Ruột non', 'Dạ dày', 'Miệng', 'Thực quản'], 0,
                'Ruột non là nơi hấp thụ chất dinh dưỡng vào máu nuôi cơ thể.');
            $this->quiz($L, 'Thói quen ăn uống nào sau đây là tốt?',
                ['Ăn chậm, nhai kĩ', 'Vừa ăn vừa chạy nhảy', 'Ăn thật nhanh cho xong', 'Bỏ bữa sáng thường xuyên'], 0,
                'Ăn chậm nhai kĩ giúp thức ăn được nghiền nhỏ, dễ tiêu hoá.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cơ quan với vai trò của nó trong tiêu hoá.',
                [['Răng', 'Nhai nhỏ thức ăn'],
                 ['Dạ dày', 'Co bóp, tiêu hoá thức ăn'],
                 ['Ruột non', 'Hấp thụ chất dinh dưỡng'],
                 ['Ruột già', 'Hấp thụ nước, tạo phân']],
                'Răng nhai nhỏ, dạ dày tiêu hoá, ruột non hấp thụ dinh dưỡng, ruột già tạo phân.');
            $this->matching($L, 'Nối mỗi loại răng với chức năng của nó.',
                [['Răng cửa', 'Cắn, xé thức ăn'],
                 ['Răng nanh', 'Xé thức ăn'],
                 ['Răng hàm', 'Nghiền nhỏ thức ăn'],
                 ['Răng sữa', 'Răng tạm thời ở trẻ nhỏ']],
                'Răng cửa cắn, răng nanh xé, răng hàm nghiền; trẻ nhỏ có răng sữa.');
            $this->matching($L, 'Nối mỗi thói quen với kết quả của nó.',
                [['Ăn chín uống sôi', 'Phòng bệnh đường ruột'],
                 ['Rửa tay trước khi ăn', 'Tránh đưa vi khuẩn vào miệng'],
                 ['Đánh răng ngày hai lần', 'Răng chắc khoẻ, hơi thở thơm'],
                 ['Ăn quà vặt bẩn', 'Dễ bị đau bụng, tiêu chảy']],
                'Ăn chín uống sôi, rửa tay, đánh răng giúp cơ thể khoẻ; quà vặt bẩn gây bệnh.');
            $this->matching($L, 'Nối mỗi chất với vai trò đối với cơ thể.',
                [['Chất đạm', 'Xây dựng cơ bắp'],
                 ['Chất bột đường', 'Cung cấp năng lượng'],
                 ['Vitamin', 'Tăng sức đề kháng'],
                 ['Chất xơ', 'Giúp tiêu hoá tốt']],
                'Đạm xây cơ, bột đường cho năng lượng, vitamin tăng đề kháng, chất xơ tốt cho tiêu hoá.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cơ quan vào nhóm THUỘC HỆ TIÊU HOÁ hoặc KHÔNG THUỘC HỆ TIÊU HOÁ.',
                [['Dạ dày', 'Thuộc hệ tiêu hoá'], ['Ruột non', 'Thuộc hệ tiêu hoá'],
                 ['Tim', 'Không thuộc hệ tiêu hoá'], ['Phổi', 'Không thuộc hệ tiêu hoá']],
                'Dạ dày, ruột non thuộc hệ tiêu hoá; tim thuộc hệ tuần hoàn, phổi thuộc hệ hô hấp.');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TỐT CHO TIÊU HOÁ hoặc HẠI CHO TIÊU HOÁ.',
                [['Ăn đúng bữa', 'Tốt cho tiêu hoá'], ['Nhai kĩ trước khi nuốt', 'Tốt cho tiêu hoá'],
                 ['Ăn đồ ôi thiu', 'Hại cho tiêu hoá'], ['Uống nước lã chưa đun sôi', 'Hại cho tiêu hoá']],
                'Ăn đúng bữa, nhai kĩ tốt cho tiêu hoá; đồ ôi thiu, nước lã gây đau bụng.');
            $this->sortQ($L, 'Sắp xếp đường đi của thức ăn: kéo mỗi cơ quan vào nhóm TRƯỚC DẠ DÀY hoặc SAU DẠ DÀY.',
                [['Miệng', 'Trước dạ dày'], ['Thực quản', 'Trước dạ dày'],
                 ['Ruột non', 'Sau dạ dày'], ['Ruột già', 'Sau dạ dày']],
                'Đường đi của thức ăn: miệng → thực quản → dạ dày → ruột non → ruột già.');
            $this->sortQ($L, 'Kéo mỗi món ăn vào nhóm GIÀU ĐẠM hoặc GIÀU BỘT ĐƯỜNG.',
                [['Thịt, cá, trứng', 'Giàu đạm'], ['Đậu phụ, sữa', 'Giàu đạm'],
                 ['Cơm, bánh mì', 'Giàu bột đường'], ['Khoai, sắn', 'Giàu bột đường']],
                'Thịt cá trứng đậu giàu đạm; cơm, bánh mì, khoai sắn giàu bột đường.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thức ăn từ miệng đi qua thực quản rồi xuống ___.', [[0, 'dạ dày']],
                'Dạ dày là nơi co bóp và tiêu hoá thức ăn.');
            $this->fill($L, 'Chất dinh dưỡng được hấp thụ chủ yếu ở ruột ___.', [[0, 'non']],
                'Ruột non có nhiều lông ruột giúp hấp thụ chất dinh dưỡng.');
            $this->fill($L, 'Để phòng bệnh đường ruột, cần ăn ___ uống sôi.', [[0, 'chín']],
                'Ăn chín uống sôi diệt vi khuẩn gây bệnh.');
            $this->fill($L, 'Bộ phận nhai nhỏ thức ăn trong miệng là ___.', [[0, 'răng']],
                'Răng nghiền nhỏ thức ăn giúp dạ dày tiêu hoá dễ dàng.');
        }
    }

    private function seedKhCoTheNguoi71(): void
    {
        $L = 'kh-co-the-nguoi-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cơ quan nào bơm máu đi khắp cơ thể?',
                ['Tim', 'Phổi', 'Gan', 'Thận'], 0,
                'Tim co bóp liên tục để bơm máu đi nuôi khắp cơ thể.', 'de');
            $this->quiz($L, 'Máu mang chất gì đến nuôi các tế bào?',
                ['Ô-xy và chất dinh dưỡng', 'Khí các-bô-níc', 'Chất thải', 'Nước tiểu'], 0,
                'Máu vận chuyển ô-xy và chất dinh dưỡng đến tế bào, đồng thời mang chất thải đi.', 'de');
            $this->quiz($L, 'Không khí khi hít vào đi qua các bộ phận nào theo đúng thứ tự?',
                ['Mũi → khí quản → phổi', 'Miệng → thực quản → dạ dày', 'Mũi → thực quản → phổi', 'Khí quản → mũi → phổi'], 0,
                'Đường đi của không khí: mũi (lọc bụi) → khí quản → phế quản → phổi.', 'de');
            $this->quiz($L, 'Khi thở ra, cơ thể thải ra khí nào là chủ yếu?',
                ['Khí các-bô-níc', 'Ô-xy', 'Ni-tơ', 'Hi-đrô'], 0,
                'Hô hấp lấy ô-xy vào và thải khí các-bô-níc ra ngoài.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thành phần với vai trò trong hệ tuần hoàn.',
                [['Tim', 'Bơm máu'],
                 ['Động mạch', 'Dẫn máu từ tim đi'],
                 ['Tĩnh mạch', 'Dẫn máu về tim'],
                 ['Mao mạch', 'Trao đổi chất với tế bào']],
                'Tim bơm máu; động mạch dẫn máu đi, tĩnh mạch dẫn máu về, mao mạch trao đổi chất.', 'de');
            $this->matching($L, 'Nối mỗi bộ phận hô hấp với vai trò của nó.',
                [['Mũi', 'Lọc bụi, làm ấm không khí'],
                 ['Khí quản', 'Dẫn không khí vào phổi'],
                 ['Phổi', 'Trao đổi khí'],
                 ['Cơ hoành', 'Giúp hít vào, thở ra']],
                'Mũi lọc bụi, khí quản dẫn khí, phổi trao đổi khí, cơ hoành giúp thở.', 'de');
            $this->matching($L, 'Nối mỗi thói quen với ảnh hưởng tới tim, phổi.',
                [['Tập thể dục đều đặn', 'Tim khoẻ, phổi tốt'],
                 ['Hút thuốc lá', 'Hại phổi, dễ ung thư'],
                 ['Hít thở không khí trong lành', 'Phổi sạch khoẻ'],
                 ['Thức khuya thường xuyên', 'Cơ thể mệt mỏi, tim làm việc quá sức']],
                'Vận động tốt cho tim phổi; thuốc lá hại phổi; thức khuya làm cơ thể suy yếu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hiện tượng với nguyên nhân.',
                [['Tim đập nhanh khi chạy', 'Cơ thể cần nhiều ô-xy hơn'],
                 ['Thở gấp khi leo cầu thang', 'Phổi phải làm việc nhiều hơn'],
                 ['Mặt đỏ khi vận động', 'Máu lưu thông mạnh'],
                 ['Ho khi hít phải bụi', 'Cơ thể tống bụi ra ngoài']],
                'Khi vận động, tim phổi làm việc mạnh hơn để cung cấp ô-xy cho cơ thể.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi cơ quan vào nhóm HỆ TUẦN HOÀN hoặc HỆ HÔ HẤP.',
                [['Tim', 'Hệ tuần hoàn'], ['Mạch máu', 'Hệ tuần hoàn'],
                 ['Phổi', 'Hệ hô hấp'], ['Khí quản', 'Hệ hô hấp']],
                'Tim, mạch máu thuộc hệ tuần hoàn; phổi, khí quản thuộc hệ hô hấp.', 'de');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm TỐT CHO TIM PHỔI hoặc HẠI CHO TIM PHỔI.',
                [['Chạy bộ mỗi sáng', 'Tốt cho tim phổi'], ['Ăn nhiều rau xanh', 'Tốt cho tim phổi'],
                 ['Hút thuốc lá', 'Hại cho tim phổi'], ['Ngồi lì một chỗ cả ngày', 'Hại cho tim phổi']],
                'Vận động và ăn rau tốt cho tim phổi; thuốc lá và lười vận động gây hại.', 'de');
            $this->sortQ($L, 'Kéo mỗi chất khí vào nhóm CƠ THỂ LẤY VÀO hoặc CƠ THỂ THẢI RA khi hô hấp.',
                [['Ô-xy', 'Cơ thể lấy vào'], ['Không khí sạch', 'Cơ thể lấy vào'],
                 ['Khí các-bô-níc', 'Cơ thể thải ra'], ['Hơi nước', 'Cơ thể thải ra']],
                'Hít vào lấy ô-xy, thở ra thải khí các-bô-níc và hơi nước.', 'de');
            $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm DẪN MÁU ĐI KHỎI TIM hoặc DẪN MÁU VỀ TIM.',
                [['Động mạch', 'Dẫn máu đi khỏi tim'], ['Động mạch chủ', 'Dẫn máu đi khỏi tim'],
                 ['Tĩnh mạch', 'Dẫn máu về tim'], ['Tĩnh mạch chủ', 'Dẫn máu về tim']],
                'Động mạch dẫn máu từ tim đi nuôi cơ thể, tĩnh mạch dẫn máu trở về tim.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cơ quan bơm máu đi khắp cơ thể là ___.', [[0, 'tim']],
                'Tim hoạt động không ngừng nghỉ suốt đời người.', 'de');
            $this->fill($L, 'Mạch máu dẫn máu từ tim đi nuôi cơ thể gọi là động ___.', [[0, 'mạch']],
                'Động mạch dẫn máu giàu ô-xy từ tim đi khắp cơ thể.', 'de');
            $this->fill($L, 'Khi hít vào, cơ thể lấy khí ___ vào phổi.', [[0, 'ô-xy']],
                'Ô-xy theo máu đi nuôi các tế bào.', 'de');
            $this->fill($L, 'Khi thở ra, cơ thể thải ra khí ___.', [[0, 'các-bô-níc']],
                'Khí các-bô-níc là sản phẩm của quá trình hô hấp tế bào.', 'de');
        }
    }

    private function seedKhCoTheNguoi72(): void
    {
        $L = 'kh-co-the-nguoi-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cơ quan nào lọc máu để tạo thành nước tiểu?',
                ['Thận', 'Gan', 'Tim', 'Dạ dày'], 0,
                'Thận lọc máu, loại bỏ chất thải tạo thành nước tiểu.', 'trung_binh');
            $this->quiz($L, 'Ngoài thận, cơ thể còn bài tiết qua những bộ phận nào?',
                ['Da và phổi', 'Tim và gan', 'Xương và cơ', 'Mắt và tai'], 0,
                'Da bài tiết mồ hôi, phổi thải khí các-bô-níc và hơi nước.', 'trung_binh');
            $this->quiz($L, 'Mồ hôi có vai trò gì đối với cơ thể?',
                ['Thải chất thải và làm mát cơ thể', 'Nuôi dưỡng tế bào', 'Bảo vệ xương', 'Tiêu hoá thức ăn'], 0,
                'Toát mồ hôi giúp thải một phần chất thải và làm mát cơ thể khi nóng.', 'trung_binh');
            $this->quiz($L, 'Để thận khoẻ mạnh, chúng ta nên làm gì?',
                ['Uống đủ nước, ăn nhạt', 'Nhịn tiểu thường xuyên', 'Ăn thật mặn', 'Uống ít nước'], 0,
                'Uống đủ nước giúp thận lọc tốt; ăn mặn và nhịn tiểu hại thận.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi cơ quan bài tiết với chất thải của nó.',
                [['Thận', 'Nước tiểu'],
                 ['Da', 'Mồ hôi'],
                 ['Phổi', 'Khí các-bô-níc và hơi nước'],
                 ['Ruột già', 'Phân']],
                'Thận thải nước tiểu, da thải mồ hôi, phổi thải khí, ruột già thải phân.', 'trung_binh');
            $this->matching($L, 'Nối mỗi thói quen vệ sinh với lợi ích của nó.',
                [['Tắm rửa hằng ngày', 'Da sạch, phòng bệnh ngoài da'],
                 ['Đánh răng 2 lần mỗi ngày', 'Răng chắc, hơi thở thơm'],
                 ['Rửa tay bằng xà phòng', 'Diệt vi khuẩn gây bệnh'],
                 ['Cắt móng tay thường xuyên', 'Tránh vi khuẩn trú ngụ']],
                'Giữ vệ sinh thân thể giúp phòng nhiều bệnh tật.', 'de');
            $this->matching($L, 'Nối mỗi dấu hiệu với lời khuyên phù hợp.',
                [['Nước tiểu sẫm màu', 'Cần uống thêm nước'],
                 ['Da khô nẻ', 'Uống đủ nước, giữ ẩm da'],
                 ['Mệt mỏi vì nóng', 'Nghỉ nơi thoáng mát, bù nước'],
                 ['Táo bón', 'Ăn nhiều rau, uống đủ nước']],
                'Cơ thể báo hiệu khi thiếu nước: nước tiểu sẫm, da khô, mệt mỏi.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cơ quan với hệ cơ quan của nó.',
                [['Thận', 'Hệ bài tiết'],
                 ['Da', 'Hệ bài tiết (qua mồ hôi)'],
                 ['Tim', 'Hệ tuần hoàn'],
                 ['Dạ dày', 'Hệ tiêu hoá']],
                'Thận và da tham gia bài tiết; tim thuộc tuần hoàn; dạ dày thuộc tiêu hoá.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm THAM GIA BÀI TIẾT hoặc KHÔNG THAM GIA BÀI TIẾT.',
                [['Thận', 'Tham gia bài tiết'], ['Da', 'Tham gia bài tiết'],
                 ['Xương', 'Không tham gia bài tiết'], ['Cơ bắp', 'Không tham gia bài tiết']],
                'Thận, da, phổi tham gia bài tiết; xương và cơ không trực tiếp bài tiết.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm TỐT CHO THẬN hoặc HẠI CHO THẬN.',
                [['Uống đủ 1,5 – 2 lít nước mỗi ngày', 'Tốt cho thận'], ['Ăn nhạt, ít muối', 'Tốt cho thận'],
                 ['Nhịn tiểu thường xuyên', 'Hại cho thận'], ['Ăn quá mặn', 'Hại cho thận']],
                'Uống đủ nước và ăn nhạt tốt cho thận; nhịn tiểu, ăn mặn hại thận.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi chất thải vào nhóm THẢI QUA THẬN hoặc THẢI QUA ĐƯỜNG KHÁC.',
                [['Nước tiểu', 'Thải qua thận'], ['Chất độc trong máu được lọc', 'Thải qua thận'],
                 ['Mồ hôi', 'Thải qua đường khác'], ['Khí các-bô-níc', 'Thải qua đường khác']],
                'Thận lọc máu tạo nước tiểu; mồ hôi qua da, khí các-bô-níc qua phổi.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm VỆ SINH CÁ NHÂN TỐT hoặc CHƯA TỐT.',
                [['Rửa tay trước khi ăn', 'Vệ sinh cá nhân tốt'], ['Tắm hằng ngày', 'Vệ sinh cá nhân tốt'],
                 ['Dùng chung khăn mặt với người lạ', 'Chưa tốt'], ['Khạc nhổ bừa bãi', 'Chưa tốt']],
                'Rửa tay, tắm hằng ngày là vệ sinh tốt; dùng chung đồ cá nhân, khạc nhổ bừa bãi dễ lây bệnh.', 'de');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Cơ quan lọc máu tạo nước tiểu là ___.', [[0, 'thận']],
                'Mỗi người có hai quả thận nằm ở vùng thắt lưng.', 'trung_binh');
            $this->fill($L, 'Da bài tiết chất thải dưới dạng ___.', [[0, 'mồ hôi']],
                'Mồ hôi còn giúp làm mát cơ thể khi trời nóng.', 'trung_binh');
            $this->fill($L, 'Để thận khoẻ, mỗi ngày nên uống đủ ___ và ăn nhạt.', [[0, 'nước']],
                'Thiếu nước khiến thận phải làm việc vất vả hơn.', 'de');
            $this->fill($L, 'Rửa tay bằng ___ giúp diệt vi khuẩn gây bệnh.', [[0, 'xà phòng']],
                'Rửa tay đúng cách là biện pháp phòng bệnh đơn giản mà hiệu quả.', 'de');
        }
    }

    // ================= KHOA HỌC: CHẤT QUANH TA =================

    private function seedKhChatQuanhTa(): void
    {
        $this->seedKhChatQuanhTa71();
        $this->seedKhChatQuanhTa72();
        $this->seedKhChatQuanhTa81();
        $this->seedKhChatQuanhTa82();
    }

    private function seedKhChatQuanhTa71(): void
    {
        $L = 'kh-chat-quanh-ta-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Chất tồn tại ở mấy trạng thái cơ bản?',
                ['3 trạng thái: rắn, lỏng, khí', '2 trạng thái: rắn và lỏng', '4 trạng thái', '1 trạng thái duy nhất'], 0,
                'Chất tồn tại ở ba trạng thái cơ bản: rắn, lỏng và khí.');
            $this->quiz($L, 'Khi đun nóng, nước đá (rắn) sẽ chuyển thành gì?',
                ['Nước lỏng', 'Hơi nước ngay lập tức', 'Không thay đổi', 'Biến mất'], 0,
                'Nước đá nóng chảy thành nước lỏng – đó là sự nóng chảy.');
            $this->quiz($L, 'Hiện tượng nước bay hơi thành hơi nước xảy ra khi nào?',
                ['Khi đun nóng hoặc để lâu ngoài nắng', 'Chỉ khi đun sôi', 'Khi cho vào tủ lạnh', 'Không bao giờ xảy ra'], 0,
                'Nước bay hơi khi được đun nóng; ngay cả ở nhiệt độ thường, nước để lâu cũng bay hơi dần.');
            $this->quiz($L, 'Sương đọng trên lá cây vào buổi sáng là do hiện tượng nào?',
                ['Ngưng tụ', 'Bay hơi', 'Nóng chảy', 'Đông đặc'], 0,
                'Hơi nước trong không khí gặp lạnh ngưng tụ thành giọt sương trên lá.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự chuyển thể với ví dụ của nó.',
                [['Nóng chảy', 'Nước đá tan thành nước'],
                 ['Đông đặc', 'Nước đóng thành đá trong tủ lạnh'],
                 ['Bay hơi', 'Nước trong nồi cạn dần khi đun'],
                 ['Ngưng tụ', 'Hơi nước đọng thành giọt trên nắp nồi']],
                'Nóng chảy: rắn→lỏng; đông đặc: lỏng→rắn; bay hơi: lỏng→khí; ngưng tụ: khí→lỏng.');
            $this->matching($L, 'Nối mỗi vật với trạng thái của nó ở nhiệt độ thường.',
                [['Cục đá', 'Rắn'],
                 ['Nước uống', 'Lỏng'],
                 ['Không khí', 'Khí'],
                 ['Dầu ăn', 'Lỏng']],
                'Đá là rắn, nước và dầu ăn là lỏng, không khí là khí.');
            $this->matching($L, 'Nối mỗi trạng thái với đặc điểm của nó.',
                [['Rắn', 'Có hình dạng xác định'],
                 ['Lỏng', 'Có thể chảy, lấy hình dạng vật chứa'],
                 ['Khí', 'Lan toả, chiếm đầy không gian'],
                 ['Hơi nước', 'Không nhìn thấy bằng mắt thường']],
                'Rắn giữ hình dạng; lỏng chảy được; khí lan toả khắp nơi.');
            $this->matching($L, 'Nối mỗi hiện tượng trong đời sống với sự chuyển thể tương ứng.',
                [['Quần áo phơi khô dần', 'Bay hơi'],
                 ['Sương mù buổi sáng', 'Ngưng tụ'],
                 ['Kem tan chảy', 'Nóng chảy'],
                 ['Nước đá trong tủ lạnh', 'Đông đặc']],
                'Phơi quần áo là bay hơi, sương mù là ngưng tụ, kem tan là nóng chảy.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi vật vào nhóm TRẠNG THÁI RẮN hoặc TRẠNG THÁI LỎNG.',
                [['Viên phấn', 'Trạng thái rắn'], ['Thìa inox', 'Trạng thái rắn'],
                 ['Sữa', 'Trạng thái lỏng'], ['Mật ong', 'Trạng thái lỏng']],
                'Phấn, thìa inox là rắn; sữa, mật ong là lỏng.');
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm KHI ĐUN NÓNG hoặc KHI LÀM LẠNH.',
                [['Nước đá tan', 'Khi đun nóng'], ['Nước bay hơi', 'Khi đun nóng'],
                 ['Nước đóng băng', 'Khi làm lạnh'], ['Hơi nước thành giọt', 'Khi làm lạnh']],
                'Đun nóng: nóng chảy, bay hơi; làm lạnh: đông đặc, ngưng tụ.');
            $this->sortQ($L, 'Kéo mỗi vật vào nhóm GIỮ NGUYÊN HÌNH DẠNG hoặc THAY ĐỔI THEO VẬT CHỨA.',
                [['Quyển sách', 'Giữ nguyên hình dạng'], ['Viên gạch', 'Giữ nguyên hình dạng'],
                 ['Nước', 'Thay đổi theo vật chứa'], ['Dầu ăn', 'Thay đổi theo vật chứa']],
                'Chất rắn giữ hình dạng; chất lỏng lấy hình dạng của vật chứa.');
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm CHUYỂN THỂ THUẬN (cần nhiệt) hoặc NGHỊCH (toả nhiệt).',
                [['Nước đá tan', 'Cần nhận nhiệt'], ['Nước bay hơi', 'Cần nhận nhiệt'],
                 ['Hơi nước ngưng tụ', 'Toả nhiệt ra'], ['Nước đông đặc', 'Toả nhiệt ra']],
                'Nóng chảy, bay hơi cần nhận nhiệt; ngưng tụ, đông đặc toả nhiệt.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ba trạng thái của chất là rắn, lỏng và ___.', [[0, 'khí']],
                'Ví dụ: nước đá (rắn), nước (lỏng), hơi nước (khí).');
            $this->fill($L, 'Nước đá tan thành nước lỏng là sự nóng ___.', [[0, 'chảy']],
                'Nóng chảy là chuyển từ rắn sang lỏng khi đun nóng.');
            $this->fill($L, 'Hơi nước gặp lạnh tạo thành giọt nước là sự ngưng ___.', [[0, 'tụ']],
                'Sương sớm và giọt nước trên nắp nồi đều do ngưng tụ.');
            $this->fill($L, 'Nước trong tủ lạnh đóng thành đá là sự đông ___.', [[0, 'đặc']],
                'Đông đặc là chuyển từ lỏng sang rắn khi làm lạnh.');
        }
    }

    private function seedKhChatQuanhTa72(): void
    {
        $L = 'kh-chat-quanh-ta-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nước chiếm khoảng bao nhiêu phần cơ thể người?',
                ['Khoảng 70%', 'Khoảng 10%', 'Khoảng 30%', 'Khoảng 100%'], 0,
                'Nước chiếm khoảng 70% khối lượng cơ thể người.', 'de');
            $this->quiz($L, 'Vòng tuần hoàn của nước trong tự nhiên bắt đầu từ đâu?',
                ['Nước bay hơi từ sông, biển lên trời', 'Mưa rơi xuống đất', 'Nước chảy ra biển', 'Cây hút nước'], 0,
                'Nước bay hơi → ngưng tụ thành mây → mưa rơi xuống → chảy ra sông biển → lại bay hơi.', 'de');
            $this->quiz($L, 'Việc làm nào sau đây giúp tiết kiệm nước?',
                ['Khoá vòi nước khi đánh răng', 'Xả nước liên tục khi rửa rau', 'Tắm vòi sen thật lâu', 'Rửa xe bằng vòi xịt mạnh'], 0,
                'Khoá vòi khi không dùng là cách tiết kiệm nước đơn giản nhất.', 'de');
            $this->quiz($L, 'Nước bị ô nhiễm gây ra hậu quả gì?',
                ['Gây bệnh cho người và sinh vật', 'Làm nước ngọt hơn', 'Giúp cây lớn nhanh', 'Không gây hại gì'], 0,
                'Nước ô nhiễm gây bệnh đường ruột, bệnh ngoài da và giết chết sinh vật dưới nước.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi giai đoạn với mô tả trong vòng tuần hoàn của nước.',
                [['Bay hơi', 'Nước từ sông biển bốc lên trời'],
                 ['Ngưng tụ', 'Hơi nước tạo thành mây'],
                 ['Mưa', 'Nước từ mây rơi xuống đất'],
                 ['Dòng chảy', 'Nước theo sông suối ra biển']],
                'Vòng tuần hoàn: bay hơi → ngưng tụ → mưa → dòng chảy.', 'de');
            $this->matching($L, 'Nối mỗi nguồn nước với đặc điểm của nó.',
                [['Nước mưa', 'Sạch tự nhiên, có thể hứng dùng'],
                 ['Nước giếng', 'Nước ngầm, cần lọc trước khi uống'],
                 ['Nước máy', 'Đã qua xử lí, an toàn'],
                 ['Nước thải công nghiệp', 'Ô nhiễm, phải xử lí']],
                'Nước máy đã xử lí nên an toàn; nước thải phải xử lí trước khi thải ra.', 'de');
            $this->matching($L, 'Nối mỗi hành động với kết quả của nó.',
                [['Vứt rác xuống sông', 'Ô nhiễm nguồn nước'],
                 ['Trồng cây ven sông', 'Giữ đất, lọc nước tự nhiên'],
                 ['Xả thải chưa xử lí', 'Giết chết cá tôm'],
                 ['Dùng nước tiết kiệm', 'Bảo vệ nguồn nước']],
                'Vứt rác và xả thải gây ô nhiễm; trồng cây và tiết kiệm giúp bảo vệ nguồn nước.', 'de');
            $this->matching($L, 'Nối mỗi vai trò với đối tượng cần nước.',
                [['Uống, nấu ăn', 'Con người'],
                 ['Tưới tiêu', 'Cây trồng'],
                 ['Sinh sống', 'Cá, tôm dưới nước'],
                 ['Sản xuất', 'Nhà máy']],
                'Mọi sinh vật và hoạt động sản xuất đều cần nước sạch.', 'de');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm TIẾT KIỆM NƯỚC hoặc LÃNG PHÍ NƯỚC.',
                [['Khoá vòi khi đánh răng', 'Tiết kiệm nước'], ['Tái sử dụng nước vo gạo tưới cây', 'Tiết kiệm nước'],
                 ['Xả vòi nước liên tục', 'Lãng phí nước'], ['Rửa xe bằng vòi chảy mạnh', 'Lãng phí nước']],
                'Khoá vòi, tái sử dụng nước giúp tiết kiệm; xả vòi liên tục gây lãng phí.', 'de');
            $this->sortQ($L, 'Kéo mỗi nguồn nước vào nhóm NƯỚC SẠCH hoặc NƯỚC Ô NHIỄM.',
                [['Nước máy', 'Nước sạch'], ['Nước mưa hứng sạch', 'Nước sạch'],
                 ['Nước ao tù đọng', 'Nước ô nhiễm'], ['Nước thải nhà máy', 'Nước ô nhiễm']],
                'Nước máy và nước mưa sạch an toàn; ao tù, nước thải công nghiệp bị ô nhiễm.', 'de');
            $this->sortQ($L, 'Sắp xếp vòng tuần hoàn của nước: kéo mỗi giai đoạn vào nhóm TRƯỚC KHI MƯA hoặc SAU KHI MƯA.',
                [['Bay hơi', 'Trước khi mưa'], ['Ngưng tụ thành mây', 'Trước khi mưa'],
                 ['Nước chảy ra sông', 'Sau khi mưa'], ['Nước thấm xuống đất', 'Sau khi mưa']],
                'Trước mưa: bay hơi, ngưng tụ; sau mưa: chảy ra sông, thấm xuống đất.', 'de');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BẢO VỆ NGUỒN NƯỚC hoặc GÂY Ô NHIỄM NGUỒN NƯỚC.',
                [['Không vứt rác xuống sông', 'Bảo vệ nguồn nước'], ['Trồng cây xanh', 'Bảo vệ nguồn nước'],
                 ['Đổ dầu thải xuống cống', 'Gây ô nhiễm nguồn nước'], ['Xả rác bừa bãi', 'Gây ô nhiễm nguồn nước']],
                'Không vứt rác, trồng cây bảo vệ nguồn nước; đổ dầu thải, xả rác gây ô nhiễm.', 'de');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nước chiếm khoảng 70% khối lượng cơ thể ___.', [[0, 'người']],
                'Vì vậy mỗi ngày cần uống đủ nước.', 'de');
            $this->fill($L, 'Nước bay hơi lên trời, ngưng tụ thành ___ rồi rơi xuống thành mưa.', [[0, 'mây']],
                'Đó là vòng tuần hoàn của nước trong tự nhiên.', 'de');
            $this->fill($L, 'Khi đánh răng, nên ___ vòi nước để tiết kiệm.', [[0, 'khoá']],
                'Khoá vòi khi không dùng giúp tiết kiệm rất nhiều nước.', 'de');
            $this->fill($L, 'Vứt rác xuống sông gây ô ___ nguồn nước.', [[0, 'nhiễm']],
                'Nước ô nhiễm gây bệnh cho người và sinh vật.', 'de');
        }
    }

    private function seedKhChatQuanhTa81(): void
    {
        $L = 'kh-chat-quanh-ta-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hỗn hợp là gì?',
                ['Hai hay nhiều chất trộn lẫn với nhau', 'Một chất tinh khiết duy nhất', 'Một nguyên tố hoá học', 'Một hợp chất mới'], 0,
                'Hỗn hợp gồm hai hay nhiều chất trộn lẫn, mỗi chất vẫn giữ tính chất riêng.', 'trung_binh');
            $this->quiz($L, 'Ví dụ nào sau đây là hỗn hợp đồng nhất (dung dịch)?',
                ['Nước đường', 'Nước lẫn dầu ăn', 'Cát lẫn sỏi', 'Nước phù sa'], 0,
                'Nước đường tan đều, không phân biệt được các thành phần – đó là dung dịch.', 'trung_binh');
            $this->quiz($L, 'Trong dung dịch nước muối, nước đóng vai trò gì?',
                ['Dung môi', 'Chất tan', 'Chất rắn', 'Chất khí'], 0,
                'Dung môi là chất dùng để hoà tan chất khác; ở đây nước là dung môi, muối là chất tan.', 'trung_binh');
            $this->quiz($L, 'Muốn tách cát ra khỏi nước, ta dùng phương pháp nào?',
                ['Lọc', 'Cô cạn', 'Chưng cất', 'Bay hơi'], 0,
                'Cát không tan trong nước nên dùng giấy lọc để tách cát ra khỏi nước.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hỗn hợp với loại của nó.',
                [['Nước đường', 'Hỗn hợp đồng nhất'],
                 ['Không khí', 'Hỗn hợp đồng nhất'],
                 ['Nước lẫn dầu', 'Hỗn hợp không đồng nhất'],
                 ['Bột mì lẫn cát', 'Hỗn hợp không đồng nhất']],
                'Nước đường, không khí đồng nhất; nước lẫn dầu, bột mì lẫn cát không đồng nhất.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dung dịch với cặp dung môi – chất tan.',
                [['Nước muối', 'Nước – muối ăn'],
                 ['Nước đường', 'Nước – đường'],
                 ['Giấm ăn', 'Nước – axit axetic'],
                 ['Nước chanh đường', 'Nước – đường và chanh']],
                'Trong các dung dịch trên, nước đều là dung môi.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hỗn hợp với phương pháp tách phù hợp.',
                [['Cát lẫn nước', 'Lọc'],
                 ['Muối lẫn nước', 'Cô cạn (đun cho nước bay hơi)'],
                 ['Dầu lẫn nước', 'Chiết (để lắng rồi tách lớp)'],
                 ['Rượu lẫn nước', 'Chưng cất']],
                'Lọc tách chất rắn không tan; cô cạn tách muối; chiết tách hai chất lỏng không hoà tan.', 'trung_binh');
            $this->matching($L, 'Nối mỗi chất với phân loại của nó.',
                [['Nước cất', 'Chất nguyên chất'],
                 ['Khí ô-xy trong bình', 'Chất nguyên chất'],
                 ['Nước biển', 'Hỗn hợp'],
                 ['Sữa tươi', 'Hỗn hợp']],
                'Nước cất, ô-xy tinh khiết là chất nguyên chất; nước biển, sữa là hỗn hợp.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chất vào nhóm CHẤT NGUYÊN CHẤT hoặc HỖN HỢP.',
                [['Đường tinh luyện', 'Chất nguyên chất'], ['Nước cất', 'Chất nguyên chất'],
                 ['Không khí', 'Hỗn hợp'], ['Nước chanh', 'Hỗn hợp']],
                'Đường tinh luyện, nước cất là chất nguyên chất; không khí, nước chanh là hỗn hợp.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hỗn hợp vào nhóm ĐỒNG NHẤT hoặc KHÔNG ĐỒNG NHẤT.',
                [['Nước muối', 'Đồng nhất'], ['Không khí', 'Đồng nhất'],
                 ['Cát lẫn nước', 'Không đồng nhất'], ['Dầu lẫn nước', 'Không đồng nhất']],
                'Nước muối, không khí đồng nhất; cát lẫn nước, dầu lẫn nước không đồng nhất.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi phương pháp vào nhóm TÁCH CHẤT RẮN KHÔNG TAN hoặc TÁCH CHẤT TAN.',
                [['Lọc qua giấy lọc', 'Tách chất rắn không tan'], ['Để lắng rồi gạn', 'Tách chất rắn không tan'],
                 ['Cô cạn', 'Tách chất tan'], ['Chưng cất', 'Tách chất tan']],
                'Lọc, gạn tách chất rắn không tan; cô cạn, chưng cất tách chất tan khỏi dung môi.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi ví dụ vào nhóm DUNG MÔI LÀ NƯỚC hoặc DUNG MÔI KHÔNG PHẢI NƯỚC.',
                [['Nước đường', 'Dung môi là nước'], ['Nước muối sinh lí', 'Dung môi là nước'],
                 ['Sơn pha với xăng', 'Dung môi không phải nước'], ['Mực bút lông dầu', 'Dung môi không phải nước']],
                'Nước là dung môi phổ biến nhất; xăng, dầu cũng có thể làm dung môi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hỗn hợp gồm hai hay nhiều ___ trộn lẫn với nhau.', [[0, 'chất']],
                'Mỗi chất trong hỗn hợp vẫn giữ tính chất riêng.', 'trung_binh');
            $this->fill($L, 'Hỗn hợp đồng nhất còn gọi là dung ___.', [[0, 'dịch']],
                'Ví dụ: nước đường, nước muối là dung dịch.', 'trung_binh');
            $this->fill($L, 'Trong dung dịch nước muối, muối là chất ___.', [[0, 'tan']],
                'Chất tan là chất bị hoà tan trong dung môi.', 'trung_binh');
            $this->fill($L, 'Muốn tách cát khỏi nước, ta dùng phương pháp ___.', [[0, 'lọc']],
                'Giấy lọc giữ lại cát, cho nước chảy qua.', 'trung_binh');
        }
    }

    private function seedKhChatQuanhTa82(): void
    {
        $L = 'kh-chat-quanh-ta-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nguyên tử gồm những thành phần nào?',
                ['Hạt nhân ở giữa và electron xung quanh', 'Chỉ có proton', 'Chỉ có electron', 'Phân tử và nguyên tố'], 0,
                'Nguyên tử gồm hạt nhân (proton, nơtron) ở giữa và electron chuyển động xung quanh.', 'trung_binh');
            $this->quiz($L, 'Hạt nào mang điện tích dương trong nguyên tử?',
                ['Proton', 'Electron', 'Nơtron', 'Phân tử'], 0,
                'Proton mang điện dương, electron mang điện âm, nơtron không mang điện.', 'trung_binh');
            $this->quiz($L, 'Phân tử nước gồm những nguyên tử nào?',
                ['2 nguyên tử hiđrô và 1 nguyên tử ôxy', '1 nguyên tử hiđrô và 2 nguyên tử ôxy', '2 nguyên tử ôxy', '3 nguyên tử hiđrô'], 0,
                'Mỗi phân tử nước (H2O) gồm 2 nguyên tử hiđrô liên kết với 1 nguyên tử ôxy.', 'trung_binh');
            $this->quiz($L, 'Kim cương và than chì đều cấu tạo từ nguyên tử nào?',
                ['Cacbon', 'Ôxy', 'Sắt', 'Nhôm'], 0,
                'Kim cương và than chì đều do nguyên tử cacbon tạo nên, chỉ khác cách sắp xếp.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi hạt với điện tích của nó.',
                [['Proton', 'Điện tích dương'],
                 ['Electron', 'Điện tích âm'],
                 ['Nơtron', 'Không mang điện'],
                 ['Hạt nhân', 'Mang điện dương (do proton)']],
                'Proton dương, electron âm, nơtron trung hoà; hạt nhân mang điện dương.', 'trung_binh');
            $this->matching($L, 'Nối mỗi chất với công thức phân tử của nó.',
                [['Nước', 'H2O'],
                 ['Khí ôxy', 'O2'],
                 ['Khí cacbonic', 'CO2'],
                 ['Muối ăn', 'NaCl']],
                'Nước H2O, ôxy O2, khí cacbonic CO2, muối ăn NaCl.', 'trung_binh');
            $this->matching($L, 'Nối mỗi khái niệm với mô tả đúng.',
                [['Nguyên tử', 'Hạt vô cùng nhỏ cấu tạo nên chất'],
                 ['Phân tử', 'Nhóm nguyên tử liên kết với nhau'],
                 ['Nguyên tố', 'Loại nguyên tử cùng loại'],
                 ['Hợp chất', 'Chất tạo từ hai nguyên tố trở lên']],
                'Nguyên tử là hạt cơ bản; phân tử là nhóm nguyên tử liên kết.', 'trung_binh');
            $this->matching($L, 'Nối mỗi vật liệu với nguyên tử chủ yếu cấu tạo nên nó.',
                [['Dây đồng', 'Nguyên tử đồng'],
                 ['Đinh sắt', 'Nguyên tử sắt'],
                 ['Than củi', 'Nguyên tử cacbon'],
                 ['Nước uống', 'Phân tử nước']],
                'Kim loại cấu tạo từ nguyên tử kim loại; nước cấu tạo từ phân tử H2O.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hạt vào nhóm NẰM TRONG HẠT NHÂN hoặc CHUYỂN ĐỘNG QUANH HẠT NHÂN.',
                [['Proton', 'Nằm trong hạt nhân'], ['Nơtron', 'Nằm trong hạt nhân'],
                 ['Electron', 'Chuyển động quanh hạt nhân'], ['Đám mây electron', 'Chuyển động quanh hạt nhân']],
                'Hạt nhân chứa proton và nơtron; electron chuyển động xung quanh.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hạt vào nhóm MANG ĐIỆN hoặc KHÔNG MANG ĐIỆN.',
                [['Proton', 'Mang điện'], ['Electron', 'Mang điện'],
                 ['Nơtron', 'Không mang điện'], ['Nguyên tử trung hoà', 'Không mang điện']],
                'Proton và electron mang điện trái dấu; nơtron không mang điện.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi chất vào nhóm ĐƠN CHẤT hoặc HỢP CHẤT.',
                [['Khí ôxy (O2)', 'Đơn chất'], ['Sắt (Fe)', 'Đơn chất'],
                 ['Nước (H2O)', 'Hợp chất'], ['Khí cacbonic (CO2)', 'Hợp chất']],
                'Đơn chất tạo từ một nguyên tố; hợp chất tạo từ hai nguyên tố trở lên.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi mô hình vào nhóm ĐÚNG VỚI CẤU TẠO NGUYÊN TỬ hoặc SAI.',
                [['Hạt nhân ở giữa, electron xung quanh', 'Đúng với cấu tạo nguyên tử'],
                 ['Electron ở giữa, hạt nhân xung quanh', 'Sai'],
                 ['Proton và nơtron nằm trong hạt nhân', 'Đúng với cấu tạo nguyên tử'],
                 ['Nguyên tử là khối đặc không có cấu tạo', 'Sai']],
                'Nguyên tử có cấu tạo rỗng: hạt nhân ở giữa, electron chuyển động xung quanh.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hạt nhân nguyên tử gồm proton và ___.', [[0, 'nơtron']],
                'Proton mang điện dương, nơtron không mang điện.', 'trung_binh');
            $this->fill($L, 'Hạt mang điện tích âm trong nguyên tử là ___.', [[0, 'electron']],
                'Electron chuyển động xung quanh hạt nhân.', 'trung_binh');
            $this->fill($L, 'Công thức hoá học của nước là H2___.', [[0, 'O']],
                'Mỗi phân tử nước gồm 2 H và 1 O.', 'trung_binh');
            $this->fill($L, 'Nhiều nguyên tử liên kết với nhau tạo thành phân ___.', [[0, 'tử']],
                'Ví dụ: hai nguyên tử ôxy liên kết tạo phân tử O2.', 'trung_binh');
        }
    }

    // ================= KHOA HỌC: NĂNG LƯỢNG =================

    private function seedKhNangLuong(): void
    {
        $this->seedKhNangLuong81();
        $this->seedKhNangLuong82();
        $this->seedKhNangLuong91();
        $this->seedKhNangLuong92();
    }

    private function seedKhNangLuong81(): void
    {
        $L = 'kh-nang-luong-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Vật đang chuyển động có dạng năng lượng nào?',
                ['Động năng', 'Thế năng', 'Quang năng', 'Hoá năng'], 0,
                'Vật chuyển động có động năng; vật càng nặng, chạy càng nhanh thì động năng càng lớn.', 'trung_binh');
            $this->quiz($L, 'Quả bóng đặt trên cao có dạng năng lượng nào?',
                ['Thế năng hấp dẫn', 'Động năng', 'Nhiệt năng', 'Điện năng'], 0,
                'Vật ở trên cao có thế năng hấp dẫn; càng cao thì thế năng càng lớn.', 'trung_binh');
            $this->quiz($L, 'Khi quả bóng rơi từ trên cao xuống, năng lượng chuyển hoá như thế nào?',
                ['Thế năng chuyển thành động năng', 'Động năng chuyển thành thế năng', 'Năng lượng biến mất', 'Không có chuyển hoá'], 0,
                'Khi rơi, độ cao giảm (thế năng giảm) và tốc độ tăng (động năng tăng).', 'trung_binh');
            $this->quiz($L, 'Định luật bảo toàn năng lượng phát biểu như thế nào?',
                ['Năng lượng không tự sinh ra hay mất đi, chỉ chuyển hoá từ dạng này sang dạng khác',
                 'Năng lượng có thể tự sinh ra từ hư không',
                 'Năng lượng sẽ mất dần theo thời gian',
                 'Chỉ có động năng được bảo toàn'], 0,
                'Năng lượng không tự nhiên sinh ra hay mất đi, chỉ chuyển từ dạng này sang dạng khác.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi vật với dạng năng lượng chủ yếu của nó.',
                [['Xe đang chạy', 'Động năng'],
                 ['Nước trên đập cao', 'Thế năng'],
                 ['Than đang cháy', 'Nhiệt năng và hoá năng'],
                 ['Pin điện thoại', 'Hoá năng']],
                'Xe chạy có động năng; nước trên cao có thế năng; pin chứa hoá năng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi thiết bị với sự chuyển hoá năng lượng của nó.',
                [['Bóng đèn', 'Điện năng thành quang năng'],
                 ['Quạt điện', 'Điện năng thành động năng'],
                 ['Bếp điện', 'Điện năng thành nhiệt năng'],
                 ['Pin mặt trời', 'Quang năng thành điện năng']],
                'Thiết bị điện chuyển điện năng thành dạng năng lượng có ích khác.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nguồn năng lượng với phân loại của nó.',
                [['Than đá', 'Năng lượng hoá thạch'],
                 ['Gió', 'Năng lượng tái tạo'],
                 ['Mặt trời', 'Năng lượng tái tạo'],
                 ['Dầu mỏ', 'Năng lượng hoá thạch']],
                'Than đá, dầu mỏ là hoá thạch (sẽ cạn kiệt); gió, mặt trời tái tạo được.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dạng năng lượng với ví dụ.',
                [['Động năng', 'Vận động viên đang chạy'],
                 ['Thế năng đàn hồi', 'Dây cung đang giương'],
                 ['Nhiệt năng', 'Nước sôi trong ấm'],
                 ['Hoá năng', 'Thức ăn ta ăn hằng ngày']],
                'Chạy có động năng; dây cung giương có thế năng đàn hồi; thức ăn chứa hoá năng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi nguồn vào nhóm NĂNG LƯỢNG TÁI TẠO hoặc NĂNG LƯỢNG HOÁ THẠCH.',
                [['Năng lượng mặt trời', 'Năng lượng tái tạo'], ['Năng lượng gió', 'Năng lượng tái tạo'],
                 ['Than đá', 'Năng lượng hoá thạch'], ['Dầu mỏ', 'Năng lượng hoá thạch']],
                'Mặt trời, gió tái tạo được; than đá, dầu mỏ là hoá thạch, dùng sẽ hết.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm CÓ ĐỘNG NĂNG hoặc KHÔNG CÓ ĐỘNG NĂNG.',
                [['Ô tô đang chạy', 'Có động năng'], ['Quả bóng đang lăn', 'Có động năng'],
                 ['Quyển sách trên bàn', 'Không có động năng'], ['Xe đang đỗ', 'Không có động năng']],
                'Chỉ vật đang chuyển động mới có động năng.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm CÓ THẾ NĂNG HẤP DẪN hoặc KHÔNG CÓ.',
                [['Diều đang bay cao', 'Có thế năng hấp dẫn'], ['Nước trên đập thuỷ điện', 'Có thế năng hấp dẫn'],
                 ['Hòn đá dưới đất', 'Không có thế năng hấp dẫn'], ['Cá đang bơi', 'Không có thế năng hấp dẫn đáng kể']],
                'Vật ở trên cao so với mặt đất có thế năng hấp dẫn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm BIẾN ĐIỆN NĂNG THÀNH NHIỆT NĂNG hoặc THÀNH DẠNG KHÁC.',
                [['Bàn là', 'Thành nhiệt năng'], ['Nồi cơm điện', 'Thành nhiệt năng'],
                 ['Quạt máy', 'Thành dạng khác (động năng)'], ['Đèn học', 'Thành dạng khác (quang năng)']],
                'Bàn là, nồi cơm điện biến điện năng thành nhiệt; quạt thành động năng, đèn thành quang năng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Năng lượng của vật đang chuyển động gọi là động ___.', [[0, 'năng']],
                'Xe chạy càng nhanh thì động năng càng lớn.', 'trung_binh');
            $this->fill($L, 'Nước trên đập cao có thế năng hấp ___.', [[0, 'dẫn']],
                'Thuỷ điện biến thế năng của nước thành điện năng.', 'trung_binh');
            $this->fill($L, 'Năng lượng không tự sinh ra hay mất đi, chỉ ___ hoá từ dạng này sang dạng khác.', [[0, 'chuyển']],
                'Đó là định luật bảo toàn năng lượng.', 'trung_binh');
            $this->fill($L, 'Than đá và dầu mỏ thuộc nhóm năng lượng hoá ___.', [[0, 'thạch']],
                'Năng lượng hoá thạch hình thành qua hàng triệu năm, dùng sẽ cạn kiệt.', 'trung_binh');
        }
    }

    private function seedKhNangLuong82(): void
    {
        $L = 'kh-nang-luong-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Công cơ học được tính bằng công thức nào?',
                ['A = F × s', 'A = F / s', 'A = F + s', 'A = m × v'], 0,
                'Công A bằng lực F nhân với quãng đường s vật đi được theo hướng của lực.', 'trung_binh');
            $this->quiz($L, 'Đơn vị của công cơ học là gì?',
                ['Jun (J)', 'Oát (W)', 'Niutơn (N)', 'Mét (m)'], 0,
                'Đơn vị công là jun (J); 1 J là công của lực 1 N làm vật đi 1 m.', 'trung_binh');
            $this->quiz($L, 'Công suất cho biết điều gì?',
                ['Mức độ nhanh chậm khi thực hiện công', 'Độ lớn của lực', 'Quãng đường vật đi', 'Khối lượng của vật'], 0,
                'Công suất P = A / t cho biết trong một giây thực hiện được bao nhiêu công.', 'trung_binh');
            $this->quiz($L, 'Một người kéo vật với lực 100 N đi được 5 m. Công thực hiện là bao nhiêu?',
                ['500 J', '20 J', '105 J', '95 J'], 0,
                'A = F × s = 100 × 5 = 500 J.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đại lượng với đơn vị của nó.',
                [['Công', 'Jun (J)'],
                 ['Công suất', 'Oát (W)'],
                 ['Lực', 'Niutơn (N)'],
                 ['Quãng đường', 'Mét (m)']],
                'Công: jun; công suất: oát; lực: niutơn; quãng đường: mét.', 'trung_binh');
            $this->matching($L, 'Nối mỗi công thức với ý nghĩa của nó.',
                [['A = F × s', 'Tính công cơ học'],
                 ['P = A / t', 'Tính công suất'],
                 ['1 kW = 1000 W', 'Đổi đơn vị công suất'],
                 ['1 J = 1 N × 1 m', 'Định nghĩa đơn vị jun']],
                'Nhớ công thức và đơn vị để tính đúng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi trường hợp với nhận xét về công.',
                [['Đẩy xe mà xe không nhúc nhích', 'Không sinh công (s = 0)'],
                 ['Xách cặp đứng yên', 'Không sinh công theo nghĩa vật lí'],
                 ['Kéo xe đi được một đoạn', 'Có sinh công'],
                 ['Nâng tạ lên cao', 'Có sinh công']],
                'Có công khi lực làm vật chuyển dời theo hướng của lực.', 'trung_binh');
            $this->matching($L, 'Nối mỗi máy với công suất đặc trưng.',
                [['Bóng đèn học', 'Vài chục oát'],
                 ['Quạt máy', 'Vài chục đến trăm oát'],
                 ['Máy điều hoà', 'Khoảng một nghìn oát'],
                 ['Xe máy', 'Vài nghìn oát (mã lực)']],
                'Công suất càng lớn, máy càng khoẻ, làm việc càng nhanh.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm CÓ SINH CÔNG CƠ HỌC hoặc KHÔNG SINH CÔNG.',
                [['Đẩy tủ lạnh trượt đi', 'Có sinh công cơ học'], ['Kéo xe bò đi', 'Có sinh công cơ học'],
                 ['Ôm cột điện đứng yên', 'Không sinh công'], ['Giữ tạ đứng im', 'Không sinh công']],
                'Sinh công khi lực làm vật chuyển dời; giữ yên thì không sinh công cơ học.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đại lượng vào nhóm ĐƠN VỊ ĐÚNG hoặc ĐƠN VỊ SAI.',
                [['Công – jun', 'Đơn vị đúng'], ['Công suất – oát', 'Đơn vị đúng'],
                 ['Công – oát', 'Đơn vị sai'], ['Công suất – jun', 'Đơn vị sai']],
                'Công đo bằng jun (J), công suất đo bằng oát (W).', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi máy vào nhóm CÔNG SUẤT LỚN hoặc CÔNG SUẤT NHỎ.',
                [['Máy cày', 'Công suất lớn'], ['Ô tô tải', 'Công suất lớn'],
                 ['Quạt bàn', 'Công suất nhỏ'], ['Đèn ngủ', 'Công suất nhỏ']],
                'Máy cày, ô tô tải công suất lớn; quạt bàn, đèn ngủ công suất nhỏ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm LÀM TĂNG CÔNG hoặc KHÔNG ẢNH HƯỞNG.',
                [['Tăng lực kéo', 'Làm tăng công'], ['Tăng quãng đường', 'Làm tăng công'],
                 ['Tăng thời gian nghỉ', 'Không ảnh hưởng'], ['Đổi màu sơn của xe', 'Không ảnh hưởng']],
                'A = F × s nên tăng lực hoặc tăng quãng đường đều làm tăng công.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Công cơ học tính bằng công thức A = F × ___.', [[0, 's']],
                's là quãng đường vật đi được theo hướng của lực.', 'trung_binh');
            $this->fill($L, 'Đơn vị của công là ___ (J).', [[0, 'jun']],
                '1 jun là công của lực 1 niutơn làm vật đi 1 mét.', 'trung_binh');
            $this->fill($L, 'Công suất tính bằng công thức P = A / ___.', [[0, 't']],
                't là thời gian thực hiện công.', 'trung_binh');
            $this->fill($L, 'Đơn vị của công suất là ___ (W).', [[0, 'oát']],
                '1 oát là công suất thực hiện 1 jun công trong 1 giây.', 'trung_binh');
        }
    }

    private function seedKhNangLuong91(): void
    {
        $L = 'kh-nang-luong-lop-9-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dòng điện là gì?',
                ['Dòng chuyển dời có hướng của các điện tích', 'Dòng nước chảy', 'Luồng không khí', 'Ánh sáng mặt trời'], 0,
                'Dòng điện là dòng chuyển dời có hướng của các hạt mang điện (electron).', 'trung_binh');
            $this->quiz($L, 'Cường độ dòng điện được đo bằng dụng cụ nào?',
                ['Ampe kế', 'Vôn kế', 'Nhiệt kế', 'Lực kế'], 0,
                'Ampe kế đo cường độ dòng điện, đơn vị là ampe (A).', 'trung_binh');
            $this->quiz($L, 'Hiệu điện thế được đo bằng dụng cụ nào?',
                ['Vôn kế', 'Ampe kế', 'Oát kế', 'Công tơ điện'], 0,
                'Vôn kế đo hiệu điện thế, đơn vị là vôn (V).', 'trung_binh');
            $this->quiz($L, 'Điều kiện để có dòng điện chạy trong mạch là gì?',
                ['Mạch kín và có nguồn điện', 'Mạch hở', 'Không có nguồn điện', 'Dây dẫn bị đứt'], 0,
                'Muốn có dòng điện, mạch điện phải kín và phải có nguồn điện (pin, ắc quy).', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đại lượng với dụng cụ đo và đơn vị.',
                [['Cường độ dòng điện', 'Ampe kế – ampe (A)'],
                 ['Hiệu điện thế', 'Vôn kế – vôn (V)'],
                 ['Điện trở', 'Ôm kế – ôm (Ω)'],
                 ['Công suất điện', 'Oát kế – oát (W)']],
                'Cường độ: ampe kế (A); hiệu điện thế: vôn kế (V).', 'trung_binh');
            $this->matching($L, 'Nối mỗi bộ phận với vai trò trong mạch điện.',
                [['Pin', 'Nguồn điện'],
                 ['Dây dẫn', 'Dẫn điện'],
                 ['Bóng đèn', 'Thiết bị tiêu thụ điện'],
                 ['Công tắc', 'Đóng – ngắt mạch']],
                'Pin cấp điện, dây dẫn truyền điện, bóng đèn dùng điện, công tắc điều khiển.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nguồn điện với hiệu điện thế đặc trưng.',
                [['Pin tiểu', '1,5 V'],
                 ['Ắc quy xe máy', '12 V'],
                 ['Điện lưới gia đình', '220 V'],
                 ['Pin điện thoại', 'Khoảng 3,7 V']],
                'Pin tiểu 1,5 V; ắc quy 12 V; điện lưới 220 V – nguy hiểm, không được chạm trực tiếp.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cách mắc với đặc điểm của nó.',
                [['Mắc nối tiếp', 'Dòng điện qua các thiết bị như nhau'],
                 ['Mắc song song', 'Hiệu điện thế trên các nhánh như nhau'],
                 ['Đèn trang trí nối tiếp', 'Một bóng cháy, cả dây tắt'],
                 ['Ổ điện trong nhà', 'Mắc song song']],
                'Nối tiếp chung dòng điện; song song chung hiệu điện thế.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi vật vào nhóm VẬT DẪN ĐIỆN hoặc VẬT CÁCH ĐIỆN.',
                [['Dây đồng', 'Vật dẫn điện'], ['Thanh sắt', 'Vật dẫn điện'],
                 ['Nhựa', 'Vật cách điện'], ['Gỗ khô', 'Vật cách điện']],
                'Kim loại dẫn điện tốt; nhựa, gỗ khô, cao su cách điện.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm MẠCH KÍN (có dòng điện) hoặc MẠCH HỞ.',
                [['Công tắc đang đóng', 'Mạch kín (có dòng điện)'], ['Pin nối với bóng đèn sáng', 'Mạch kín (có dòng điện)'],
                 ['Công tắc đang mở', 'Mạch hở'], ['Dây dẫn bị đứt', 'Mạch hở']],
                'Mạch kín và có nguồn thì có dòng điện; công tắc mở hoặc dây đứt là mạch hở.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đại lượng vào nhóm ĐO BẰNG AMPE KẾ hoặc ĐO BẰNG VÔN KẾ.',
                [['Cường độ dòng điện', 'Đo bằng ampe kế'], ['Dòng điện qua bóng đèn', 'Đo bằng ampe kế'],
                 ['Hiệu điện thế hai đầu pin', 'Đo bằng vôn kế'], ['Hiệu điện thế của ổ điện', 'Đo bằng vôn kế']],
                'Ampe kế đo cường độ dòng điện; vôn kế đo hiệu điện thế.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nguồn vào nhóm ĐIỆN ÁP AN TOÀN hoặc ĐIỆN ÁP NGUY HIỂM.',
                [['Pin tiểu 1,5 V', 'Điện áp an toàn'], ['Ắc quy 12 V', 'Điện áp an toàn'],
                 ['Điện lưới 220 V', 'Điện áp nguy hiểm'], ['Đường dây cao thế', 'Điện áp nguy hiểm']],
                'Dưới 40 V thường an toàn; điện lưới 220 V và cao thế rất nguy hiểm.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dòng điện là dòng chuyển dời có ___ của các điện tích.', [[0, 'hướng']],
                'Các electron chuyển động có hướng tạo thành dòng điện.', 'trung_binh');
            $this->fill($L, 'Cường độ dòng điện đo bằng ___ kế, đơn vị là ampe.', [[0, 'ampe']],
                'Kí hiệu đơn vị ampe là A.', 'trung_binh');
            $this->fill($L, 'Hiệu điện thế đo bằng ___ kế, đơn vị là vôn.', [[0, 'vôn']],
                'Kí hiệu đơn vị vôn là V.', 'trung_binh');
            $this->fill($L, 'Muốn có dòng điện, mạch phải ___ và có nguồn điện.', [[0, 'kín']],
                'Mạch hở (công tắc mở, dây đứt) thì không có dòng điện.', 'trung_binh');
        }
    }

    private function seedKhNangLuong92(): void
    {
        $L = 'kh-nang-luong-lop-9-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Công suất điện được tính bằng công thức nào?',
                ['P = U × I', 'P = U / I', 'P = U + I', 'P = I / U'], 0,
                'Công suất điện P bằng hiệu điện thế U nhân với cường độ dòng điện I.', 'kho');
            $this->quiz($L, 'Một bóng đèn 220 V – 100 W có nghĩa là gì?',
                ['Dùng đúng 220 V thì công suất là 100 W', 'Lúc nào cũng sáng 100 W', 'Tiêu thụ 100 J mỗi giờ', 'Chịu được tối đa 100 V'], 0,
                'Số 220 V là hiệu điện thế định mức, 100 W là công suất định mức khi dùng đúng 220 V.', 'kho');
            $this->quiz($L, '"Số điện" mà công tơ điện đo chính là đơn vị nào?',
                ['Ki-lô-oát giờ (kWh)', 'Oát (W)', 'Jun (J)', 'Ampe (A)'], 0,
                '1 số điện = 1 kWh là điện năng của thiết bị 1000 W dùng trong 1 giờ.', 'kho');
            $this->quiz($L, 'Việc làm nào sau đây KHÔNG an toàn khi dùng điện?',
                ['Chạm tay ướt vào ổ điện', 'Ngắt điện trước khi sửa chữa', 'Dùng dây dẫn đúng tiêu chuẩn', 'Tắt thiết bị khi không dùng'], 0,
                'Tay ướt dẫn điện tốt nên tuyệt đối không chạm vào ổ điện, công tắc khi tay ướt.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi thiết bị với công suất định mức thường gặp.',
                [['Bóng đèn LED', '5 – 20 W'],
                 ['Quạt máy', '40 – 75 W'],
                 ['Nồi cơm điện', '500 – 1000 W'],
                 ['Máy điều hoà', '1000 – 2000 W']],
                'Thiết bị càng "nặng điện" thì công suất càng lớn.', 'kho');
            $this->matching($L, 'Nối mỗi tình huống với cách xử lí an toàn.',
                [['Tay ướt', 'Lau khô tay trước khi chạm công tắc'],
                 ['Sửa điện trong nhà', 'Ngắt cầu dao trước'],
                 ['Dây điện bị hở', 'Bọc băng keo cách điện hoặc thay dây mới'],
                 ['Mưa bão', 'Tránh xa cột điện, dây điện đứt']],
                'An toàn điện: tay khô, ngắt điện khi sửa, tránh dây đứt lúc mưa bão.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hành động với mục đích tiết kiệm điện.',
                [['Tắt đèn khi ra khỏi phòng', 'Tránh lãng phí điện'],
                 ['Dùng bóng đèn LED', 'Ít tốn điện hơn bóng sợi đốt'],
                 ['Tận dụng ánh sáng tự nhiên', 'Giảm thời gian bật đèn'],
                 ['Để điều hoà 16°C cả ngày', 'Tốn điện, không nên']],
                'Tắt thiết bị không dùng, dùng LED, tận dụng ánh sáng tự nhiên giúp tiết kiệm điện.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đại lượng điện với đơn vị của nó.',
                [['Công suất điện', 'Oát (W)'],
                 ['Điện năng tiêu thụ', 'Ki-lô-oát giờ (kWh)'],
                 ['Cường độ dòng điện', 'Ampe (A)'],
                 ['Hiệu điện thế', 'Vôn (V)']],
                'Công suất: W; điện năng: kWh; cường độ: A; hiệu điện thế: V.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm AN TOÀN ĐIỆN hoặc NGUY HIỂM.',
                [['Ngắt cầu dao trước khi sửa điện', 'An toàn điện'], ['Đi giày khô khi chạm thiết bị điện', 'An toàn điện'],
                 ['Dùng tay ướt cắm phích điện', 'Nguy hiểm'], ['Trèo lên cột điện', 'Nguy hiểm']],
                'Tay khô, ngắt điện khi sửa là an toàn; tay ướt, trèo cột điện rất nguy hiểm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thiết bị vào nhóm TỐN NHIỀU ĐIỆN hoặc ÍT TỐN ĐIỆN.',
                [['Máy điều hoà', 'Tốn nhiều điện'], ['Bình nóng lạnh', 'Tốn nhiều điện'],
                 ['Bóng đèn LED', 'Ít tốn điện'], ['Quạt bàn', 'Ít tốn điện']],
                'Điều hoà, bình nóng lạnh công suất lớn nên tốn nhiều điện.', 'kho');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm TIẾT KIỆM ĐIỆN hoặc LÃNG PHÍ ĐIỆN.',
                [['Tắt tivi khi không xem', 'Tiết kiệm điện'], ['Giặt quần áo bằng tay với ít đồ', 'Tiết kiệm điện'],
                 ['Bật đèn cả ngày dù trời sáng', 'Lãng phí điện'], ['Mở tủ lạnh liên tục', 'Lãng phí điện']],
                'Tắt thiết bị không dùng giúp tiết kiệm; bật đèn ban ngày, mở tủ lạnh lâu gây lãng phí.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi số liệu vào nhóm ĐIỆN ÁP ĐỊNH MỨC hay CÔNG SUẤT ĐỊNH MỨC trên nhãn thiết bị.',
                [['220 V', 'Điện áp định mức'], ['110 V', 'Điện áp định mức'],
                 ['100 W', 'Công suất định mức'], ['1500 W', 'Công suất định mức']],
                'Trên nhãn: V là điện áp định mức, W là công suất định mức.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Công suất điện tính bằng công thức P = U × ___.', [[0, 'I']],
                'I là cường độ dòng điện chạy qua thiết bị.', 'kho');
            $this->fill($L, 'Một "số điện" chính là một ki-lô-oát ___.', [[0, 'giờ']],
                'Kí hiệu là kWh.', 'kho');
            $this->fill($L, 'Tuyệt đối không chạm tay ___ vào ổ điện.', [[0, 'ướt']],
                'Nước dẫn điện nên tay ướt rất nguy hiểm khi chạm vào điện.', 'trung_binh');
            $this->fill($L, 'Trước khi sửa chữa điện trong nhà phải ___ cầu dao.', [[0, 'ngắt']],
                'Ngắt điện đảm bảo không còn dòng điện chạy qua khi sửa chữa.', 'trung_binh');
        }
    }

    // ================= LỊCH SỬ: DỰNG NƯỚC =================

    private function seedLsDungNuoc(): void
    {
        $this->seedLsDungNuoc61();
        $this->seedLsDungNuoc62();
        $this->seedLsDungNuoc71();
        $this->seedLsDungNuoc72();
    }

    private function seedLsDungNuoc61(): void
    {
        $L = 'ls-dung-nuoc-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Nước Văn Lang ra đời vào khoảng thời gian nào?',
                ['Thế kỉ VII TCN', 'Thế kỉ X', 'Thế kỉ XV', 'Thế kỉ XIX'], 0,
                'Khoảng thế kỉ VII TCN, các bộ lạc hợp nhất thành nước Văn Lang.');
            $this->quiz($L, 'Ai là người đứng đầu nước Văn Lang?',
                ['Hùng Vương', 'An Dương Vương', 'Ngô Quyền', 'Đinh Bộ Lĩnh'], 0,
                'Hùng Vương là người đứng đầu nước Văn Lang, được nhân dân tôn thờ.');
            $this->quiz($L, 'Kinh đô của nước Văn Lang đặt ở đâu?',
                ['Phong Châu (Phú Thọ)', 'Hoa Lư (Ninh Bình)', 'Cổ Loa (Hà Nội)', 'Thăng Long'], 0,
                'Kinh đô Văn Lang đặt ở Phong Châu, thuộc tỉnh Phú Thọ ngày nay.');
            $this->quiz($L, 'Nước Văn Lang được chia thành bao nhiêu bộ?',
                ['15 bộ', '10 bộ', '12 bộ', '20 bộ'], 0,
                'Cả nước Văn Lang chia thành 15 bộ, đứng đầu mỗi bộ là Lạc tướng.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chi tiết với nội dung đúng về nước Văn Lang.',
                [['Người đứng đầu', 'Hùng Vương'],
                 ['Kinh đô', 'Phong Châu'],
                 ['Đơn vị hành chính', '15 bộ'],
                 ['Thời gian ra đời', 'Khoảng thế kỉ VII TCN']],
                'Văn Lang: Hùng Vương đứng đầu, kinh đô Phong Châu, chia 15 bộ.');
            $this->matching($L, 'Nối mỗi chức danh với vai trò của nó.',
                [['Hùng Vương', 'Người đứng đầu cả nước'],
                 ['Lạc tướng', 'Đứng đầu mỗi bộ'],
                 ['Bồ chính', 'Đứng đầu các chiềng, chạ'],
                 ['Lạc dân', 'Người dân thường']],
                'Hùng Vương trị vì cả nước, Lạc tướng cai quản từng bộ.');
            $this->matching($L, 'Nối mỗi phong tục với mô tả của nó.',
                [['Ăn trầu', 'Phong tục của người Việt cổ'],
                 ['Xăm mình', 'Tục của trai tráng thời Hùng Vương'],
                 ['Làm bánh chưng', 'Gắn với truyền thuyết Lang Liêu'],
                 ['Nhuộm răng đen', 'Tục làm đẹp thời xưa']],
                'Người Việt cổ có tục ăn trầu, xăm mình, làm bánh chưng bánh giầy.');
            $this->matching($L, 'Nối mỗi di tích với địa phương hiện nay.',
                [['Đền Hùng', 'Phú Thọ'],
                 ['Phong Châu', 'Phú Thọ'],
                 ['Cổ Loa', 'Hà Nội'],
                 ['Hoa Lư', 'Ninh Bình']],
                'Đền Hùng và Phong Châu ở Phú Thọ; Cổ Loa ở Hà Nội; Hoa Lư ở Ninh Bình.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm THUỘC NƯỚC VĂN LANG hoặc KHÔNG THUỘC.',
                [['Hùng Vương', 'Thuộc nước Văn Lang'], ['Kinh đô Phong Châu', 'Thuộc nước Văn Lang'],
                 ['An Dương Vương', 'Không thuộc'], ['Ngô Quyền', 'Không thuộc']],
                'Văn Lang gắn với Hùng Vương và Phong Châu; An Dương Vương thuộc Âu Lạc.');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm THỜI HÙNG VƯƠNG hoặc THỜI SAU.',
                [['Hùng Vương', 'Thời Hùng Vương'], ['Lang Liêu', 'Thời Hùng Vương'],
                 ['Hai Bà Trưng', 'Thời sau'], ['Trần Hưng Đạo', 'Thời sau']],
                'Hùng Vương, Lang Liêu thuộc thời Văn Lang; Hai Bà Trưng, Trần Hưng Đạo thuộc thời sau.');
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC CÔNG NGUYÊN hoặc SAU CÔNG NGUYÊN.',
                [['Nước Văn Lang ra đời', 'Trước Công nguyên'], ['An Dương Vương lập Âu Lạc', 'Trước Công nguyên'],
                 ['Khởi nghĩa Hai Bà Trưng', 'Sau Công nguyên'], ['Ngô Quyền thắng Bạch Đằng', 'Sau Công nguyên']],
                'Văn Lang (thế kỉ VII TCN) và Âu Lạc (208 TCN) đều trước Công nguyên.');
            $this->sortQ($L, 'Kéo mỗi nghề nghiệp vào nhóm CÓ Ở THỜI VĂN LANG hoặc CHƯA CÓ.',
                [['Trồng lúa nước', 'Có ở thời Văn Lang'], ['Đúc đồng', 'Có ở thời Văn Lang'],
                 ['Lái máy bay', 'Chưa có'], ['Dùng điện thoại', 'Chưa có']],
                'Thời Văn Lang đã trồng lúa nước và đúc đồng rất phát triển.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Nhà nước đầu tiên của nước ta là nước Văn ___.', [[0, 'Lang']],
                'Nước Văn Lang ra đời khoảng thế kỉ VII TCN.');
            $this->fill($L, 'Người đứng đầu nước Văn Lang là Hùng ___.', [[0, 'Vương']],
                'Nhân dân ta đời đời nhớ ơn các Vua Hùng.');
            $this->fill($L, 'Kinh đô nước Văn Lang đặt ở Phong ___.', [[0, 'Châu']],
                'Phong Châu nay thuộc tỉnh Phú Thọ.');
            $this->fill($L, 'Cả nước Văn Lang chia thành 15 ___.', [[0, 'bộ']],
                'Đứng đầu mỗi bộ là Lạc tướng.');
        }
    }

    private function seedLsDungNuoc62(): void
    {
        $L = 'ls-dung-nuoc-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ai là người lập ra nước Âu Lạc?',
                ['An Dương Vương (Thục Phán)', 'Hùng Vương', 'Ngô Quyền', 'Lê Hoàn'], 0,
                'Năm 208 TCN, Thục Phán – An Dương Vương lập nước Âu Lạc.');
            $this->quiz($L, 'Kinh đô của nước Âu Lạc đặt ở đâu?',
                ['Cổ Loa', 'Phong Châu', 'Hoa Lư', 'Thăng Long'], 0,
                'An Dương Vương đóng đô ở Cổ Loa, nay thuộc Hà Nội.');
            $this->quiz($L, 'Thành Cổ Loa có hình dạng đặc biệt như thế nào?',
                ['Hình xoáy ốc', 'Hình vuông', 'Hình tròn', 'Hình chữ nhật'], 0,
                'Thành Cổ Loa xây theo hình xoáy ốc, gồm nhiều vòng thành.');
            $this->quiz($L, 'Vũ khí lợi hại của quân Âu Lạc gắn với truyền thuyết nào?',
                ['Nỏ thần', 'Gươm thần', 'Giáo dài', 'Cung tên thường'], 0,
                'Truyền thuyết kể An Dương Vương được thần Kim Quy cho móng rùa làm lẫy nỏ thần.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chi tiết với nội dung đúng về nước Âu Lạc.',
                [['Người sáng lập', 'An Dương Vương'],
                 ['Kinh đô', 'Cổ Loa'],
                 ['Năm thành lập', '208 TCN'],
                 ['Vũ khí nổi tiếng', 'Nỏ thần']],
                'Âu Lạc (208 TCN): An Dương Vương, kinh đô Cổ Loa, nỏ thần.');
            $this->matching($L, 'Nối mỗi nhân vật truyền thuyết với vai trò của họ.',
                [['Thục Phán', 'Vua nước Âu Lạc'],
                 ['Thần Kim Quy', 'Giúp An Dương Vương xây thành, chế nỏ'],
                 ['Mị Châu', 'Con gái An Dương Vương'],
                 ['Trọng Thuỷ', 'Con trai Triệu Đà']],
                'Truyền thuyết Mị Châu – Trọng Thuỷ gắn với sự mất nước Âu Lạc.');
            $this->matching($L, 'Nối mỗi thành tựu với lĩnh vực của nó.',
                [['Thành Cổ Loa', 'Quân sự – kiến trúc'],
                 ['Nỏ thần', 'Vũ khí'],
                 ['Trống đồng', 'Văn hoá'],
                 ['Lúa nước', 'Nông nghiệp']],
                'Âu Lạc kế thừa và phát triển văn minh Văn Lang: thành luỹ, vũ khí, trống đồng.');
            $this->matching($L, 'Nối mỗi nước với người đứng đầu.',
                [['Văn Lang', 'Hùng Vương'],
                 ['Âu Lạc', 'An Dương Vương'],
                 ['Nam Việt (Triệu Đà)', 'Triệu Đà'],
                 ['Đại Cồ Việt', 'Đinh Tiên Hoàng']],
                'Văn Lang – Hùng Vương; Âu Lạc – An Dương Vương; Đại Cồ Việt – Đinh Tiên Hoàng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm THUỘC NƯỚC ÂU LẠC hoặc THUỘC NƯỚC VĂN LANG.',
                [['An Dương Vương', 'Thuộc nước Âu Lạc'], ['Thành Cổ Loa', 'Thuộc nước Âu Lạc'],
                 ['Hùng Vương', 'Thuộc nước Văn Lang'], ['Kinh đô Phong Châu', 'Thuộc nước Văn Lang']],
                'Âu Lạc: An Dương Vương, Cổ Loa; Văn Lang: Hùng Vương, Phong Châu.');
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC hoặc SAU khi nước Âu Lạc ra đời (208 TCN).',
                [['Nước Văn Lang tồn tại', 'Trước'], ['Hùng Vương dựng nước', 'Trước'],
                 ['Triệu Đà xâm lược', 'Sau'], ['Hai Bà Trưng khởi nghĩa', 'Sau']],
                'Văn Lang có trước; Triệu Đà xâm lược và Hai Bà Trưng khởi nghĩa diễn ra sau 208 TCN.');
            $this->sortQ($L, 'Kéo mỗi vũ khí vào nhóm CÓ TRONG TRUYỀN THUYẾT NỎ THẦN hoặc KHÔNG.',
                [['Nỏ bắn một lúc nhiều mũi tên', 'Có trong truyền thuyết nỏ thần'], ['Lẫy nỏ bằng móng rùa thần', 'Có trong truyền thuyết nỏ thần'],
                 ['Súng đại bác', 'Không'], ['Máy bay chiến đấu', 'Không']],
                'Nỏ thần bắn nhiều mũi tên một lúc, lẫy nỏ làm bằng móng thần Kim Quy.');
            $this->sortQ($L, 'Kéo mỗi công trình vào nhóm DO AN DƯƠNG VƯƠNG XÂY hoặc KHÔNG PHẢI.',
                [['Thành Cổ Loa', 'Do An Dương Vương xây'], ['Cung điện ở Cổ Loa', 'Do An Dương Vương xây'],
                 ['Hoàng thành Thăng Long', 'Không phải'], ['Cố đô Hoa Lư', 'Không phải']],
                'An Dương Vương xây thành Cổ Loa; Hoa Lư là của nhà Đinh.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Người lập nước Âu Lạc là An Dương ___.', [[0, 'Vương']],
                'An Dương Vương tên thật là Thục Phán.');
            $this->fill($L, 'Kinh đô nước Âu Lạc đặt ở Cổ ___.', [[0, 'Loa']],
                'Thành Cổ Loa có hình xoáy ốc độc đáo.');
            $this->fill($L, 'Nước Âu Lạc ra đời năm 208 ___.', [[0, 'TCN']],
                'Trước Công nguyên, An Dương Vương hợp nhất Âu Việt và Lạc Việt.');
            $this->fill($L, 'Vũ khí lợi hại của Âu Lạc là nỏ ___.', [[0, 'thần']],
                'Tương truyền lẫy nỏ thần làm bằng móng rùa của thần Kim Quy.');
        }
    }

    private function seedLsDungNuoc71(): void
    {
        $L = 'ls-dung-nuoc-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Cuộc khởi nghĩa Hai Bà Trưng bùng nổ vào năm nào?',
                ['Năm 40', 'Năm 43', 'Năm 938', 'Năm 208 TCN'], 0,
                'Mùa xuân năm 40, Hai Bà Trưng phất cờ khởi nghĩa ở Mê Linh.', 'de');
            $this->quiz($L, 'Hai Bà Trưng khởi nghĩa ở đâu?',
                ['Mê Linh', 'Cổ Loa', 'Hoa Lư', 'Bạch Đằng'], 0,
                'Hai bà dựng cờ khởi nghĩa tại Mê Linh (nay thuộc Hà Nội).', 'de');
            $this->quiz($L, 'Nguyên nhân trực tiếp khiến Hai Bà Trưng nổi dậy là gì?',
                ['Chính sách cai trị tàn bạo của nhà Hán', 'Tranh giành ngôi vua', 'Muốn mở rộng bờ cõi', 'Bị ngoại bang xúi giục'], 0,
                'Nhà Hán cai trị tàn bạo, bắt dân nộp thuế nặng, nên Hai Bà phất cờ khởi nghĩa.', 'de');
            $this->quiz($L, 'Sau thắng lợi, Trưng Trắc đã làm gì?',
                ['Lên ngôi vua, đóng đô ở Mê Linh', 'Rút về núi ẩn náu', 'Sang Trung Quốc cầu hoà', 'Nhường ngôi cho em'], 0,
                'Trưng Trắc lên ngôi vua (Trưng Vương), đóng đô ở Mê Linh.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi mốc thời gian với sự kiện.',
                [['Năm 40', 'Hai Bà Trưng khởi nghĩa'],
                 ['Năm 42', 'Mã Viện đem quân sang đàn áp'],
                 ['Năm 43', 'Khởi nghĩa thất bại, Hai Bà hi sinh'],
                 ['Sau khởi nghĩa', 'Nhân dân lập đền thờ Hai Bà']],
                'Khởi nghĩa năm 40, thất bại năm 43 trước quân Mã Viện.', 'de');
            $this->matching($L, 'Nối mỗi nhân vật với vai trò của họ.',
                [['Trưng Trắc', 'Thủ lĩnh khởi nghĩa, lên ngôi vua'],
                 ['Trưng Nhị', 'Cùng chị lãnh đạo khởi nghĩa'],
                 ['Thi Sách', 'Chồng Trưng Trắc, bị Tô Định giết'],
                 ['Tô Định', 'Quan thái thú nhà Hán tàn bạo']],
                'Thi Sách bị Tô Định giết hại là một nguyên nhân khiến Hai Bà nổi dậy.', 'de');
            $this->matching($L, 'Nối mỗi chính sách của nhà Hán với nỗi khổ của nhân dân.',
                [['Thuế nặng', 'Dân nghèo kiệt quệ'],
                 ['Bắt lao dịch', 'Dân phải đi phu, bỏ ruộng vườn'],
                 ['Đồng hoá', 'Ép dân ta theo phong tục Hán'],
                 ['Đàn áp', 'Ai chống đối bị giết hại']],
                'Chính sách tàn bạo của nhà Hán khiến nhân dân ta căm phẫn.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cuộc khởi nghĩa với người lãnh đạo.',
                [['Năm 40', 'Hai Bà Trưng'],
                 ['Năm 248', 'Bà Triệu'],
                 ['Năm 938', 'Ngô Quyền'],
                 ['Năm 968', 'Đinh Bộ Lĩnh']],
                'Hai Bà Trưng (40), Bà Triệu (248), Ngô Quyền (938) đều là anh hùng dân tộc.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Sắp xếp diễn biến: kéo mỗi sự kiện vào nhóm TRƯỚC KHỞI NGHĨA hoặc TRONG/SAU KHỞI NGHĨA.',
                [['Tô Định cai trị tàn bạo', 'Trước khởi nghĩa'], ['Thi Sách bị giết hại', 'Trước khởi nghĩa'],
                 ['Hai Bà lên ngôi vua', 'Trong/sau khởi nghĩa'], ['Mã Viện sang đàn áp', 'Trong/sau khởi nghĩa']],
                'Trước: ách đô hộ tàn bạo; sau: Hai Bà lên ngôi rồi Mã Viện đàn áp.', 'de');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm NGHĨA QUÂN hoặc QUÂN XÂM LƯỢC.',
                [['Trưng Trắc', 'Nghĩa quân'], ['Trưng Nhị', 'Nghĩa quân'],
                 ['Tô Định', 'Quân xâm lược'], ['Mã Viện', 'Quân xâm lược']],
                'Trưng Trắc, Trưng Nhị lãnh đạo nghĩa quân; Tô Định, Mã Viện là tướng nhà Hán.', 'de');
            $this->sortQ($L, 'Kéo mỗi câu nói/hành động vào nhóm THỂ HIỆN LÒNG YÊU NƯỚC hoặc KHÔNG.',
                [['"Một xin rửa sạch nước thù..."', 'Thể hiện lòng yêu nước'], ['Phất cờ khởi nghĩa chống quân Hán', 'Thể hiện lòng yêu nước'],
                 ['Đầu hàng giặc để giữ mạng', 'Không'], ['Bỏ trốn khi giặc đến', 'Không']],
                'Lời thề và hành động khởi nghĩa thể hiện lòng yêu nước của Hai Bà.', 'de');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm GẮN VỚI HAI BÀ TRƯNG hoặc KHÔNG.',
                [['Mê Linh', 'Gắn với Hai Bà Trưng'], ['Hát Môn', 'Gắn với Hai Bà Trưng'],
                 ['Bạch Đằng', 'Không'], ['Hoa Lư', 'Không']],
                'Mê Linh là nơi dựng cờ, Hát Môn là nơi Hai Bà hoá (theo truyền thuyết).', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Hai Bà Trưng phất cờ khởi nghĩa vào năm ___.', [[0, '40']],
                'Mùa xuân năm 40, tại Mê Linh.', 'de');
            $this->fill($L, 'Nơi Hai Bà dựng cờ khởi nghĩa là Mê ___.', [[0, 'Linh']],
                'Mê Linh nay thuộc thành phố Hà Nội.', 'de');
            $this->fill($L, 'Sau thắng lợi, Trưng Trắc lên ngôi ___.', [[0, 'vua']],
                'Trưng Vương đóng đô ở Mê Linh.', 'de');
            $this->fill($L, 'Nhà Hán cử Mã ___ sang đàn áp cuộc khởi nghĩa.', [[0, 'Viện']],
                'Năm 43, khởi nghĩa thất bại nhưng tinh thần yêu nước còn mãi.', 'trung_binh');
        }
    }

    private function seedLsDungNuoc72(): void
    {
        $L = 'ls-dung-nuoc-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trận Bạch Đằng năm 938 do ai chỉ huy quân ta?',
                ['Ngô Quyền', 'Đinh Bộ Lĩnh', 'Lê Hoàn', 'Trần Hưng Đạo'], 0,
                'Ngô Quyền là người chỉ huy trận Bạch Đằng năm 938.', 'de');
            $this->quiz($L, 'Kế sách đánh giặc của Ngô Quyền trên sông Bạch Đằng là gì?',
                ['Đóng cọc gỗ đầu bịt sắt, dụ giặc vào lúc thuỷ triều lên', 'Đánh trực diện trên bộ', 'Rút lui vào rừng', 'Cầu hoà với giặc'], 0,
                'Ngô Quyền cho đóng cọc gỗ xuống lòng sông, nhử giặc vào khi nước lên rồi đánh úp khi nước rút.', 'trung_binh');
            $this->quiz($L, 'Quân ta đã đánh tan quân xâm lược nào trong trận Bạch Đằng 938?',
                ['Quân Nam Hán', 'Quân Nguyên – Mông', 'Quân Tống', 'Quân Minh'], 0,
                'Trận Bạch Đằng 938 đánh tan quân Nam Hán do Lưu Hoằng Tháo chỉ huy.', 'de');
            $this->quiz($L, 'Ý nghĩa lớn nhất của chiến thắng Bạch Đằng năm 938 là gì?',
                ['Chấm dứt hơn 1000 năm Bắc thuộc, mở đầu thời kì độc lập', 'Mở rộng bờ cõi xuống phía Nam', 'Thống nhất 12 sứ quân', 'Đánh thắng quân Nguyên'], 0,
                'Chiến thắng Bạch Đằng chấm dứt thời kì Bắc thuộc, mở ra kỉ nguyên độc lập tự chủ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nhân vật với vai trò trong trận Bạch Đằng 938.',
                [['Ngô Quyền', 'Chỉ huy quân ta'],
                 ['Lưu Hoằng Tháo', 'Tướng giặc Nam Hán tử trận'],
                 ['Dương Đình Nghệ', 'Người nuôi dưỡng Ngô Quyền'],
                 ['Kiều Công Tiễn', 'Kẻ giết Dương Đình Nghệ, cầu cứu Nam Hán']],
                'Ngô Quyền trả thù cho Dương Đình Nghệ và đánh tan quân Nam Hán.', 'trung_binh');
            $this->matching($L, 'Nối mỗi mốc thời gian với sự kiện.',
                [['Năm 931', 'Dương Đình Nghệ giành quyền tự chủ'],
                 ['Năm 937', 'Kiều Công Tiễn giết Dương Đình Nghệ'],
                 ['Năm 938', 'Chiến thắng Bạch Đằng'],
                 ['Năm 939', 'Ngô Quyền xưng vương, đóng đô ở Cổ Loa']],
                'Từ 931 đến 939: giành tự chủ → Bạch Đằng → Ngô Quyền xưng vương.', 'trung_binh');
            $this->matching($L, 'Nối mỗi yếu tố với vai trò trong chiến thắng.',
                [['Cọc gỗ đầu bịt sắt', 'Vũ khí bí mật dưới lòng sông'],
                 ['Thuỷ triều', 'Yếu tố tự nhiên được lợi dụng'],
                 ['Lòng yêu nước', 'Sức mạnh tinh thần của quân dân'],
                 ['Tài chỉ huy', 'Ngô Quyền bày mưu, đoán đúng ý giặc']],
                'Thắng lợi nhờ vũ khí, địa hình, lòng dân và tài chỉ huy.', 'trung_binh');
            $this->matching($L, 'Nối mỗi trận Bạch Đằng với thời gian.',
                [['Bạch Đằng 938', 'Ngô Quyền đánh Nam Hán'],
                 ['Bạch Đằng 981', 'Lê Hoàn đánh Tống'],
                 ['Bạch Đằng 1288', 'Trần Hưng Đạo đánh Nguyên'],
                 ['Cả ba trận', 'Đều dùng kế cọc gỗ và thuỷ triều']],
                'Sông Bạch Đằng ghi dấu ba chiến thắng lẫy lừng của dân tộc.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC TRẬN BẠCH ĐẰNG 938 hoặc SAU TRẬN BẠCH ĐẰNG 938.',
                [['Kiều Công Tiễn cầu cứu Nam Hán', 'Trước trận Bạch Đằng 938'], ['Ngô Quyền đóng cọc gỗ', 'Trước trận Bạch Đằng 938'],
                 ['Ngô Quyền xưng vương', 'Sau trận Bạch Đằng 938'], ['Đóng đô ở Cổ Loa', 'Sau trận Bạch Đằng 938']],
                'Trước: chuẩn bị trận địa; sau: Ngô Quyền xưng vương, đóng đô Cổ Loa.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm PHE TA hoặc PHE GIẶC.',
                [['Ngô Quyền', 'Phe ta'], ['Dương Đình Nghệ', 'Phe ta'],
                 ['Lưu Hoằng Tháo', 'Phe giặc'], ['Kiều Công Tiễn', 'Phe giặc (phản bội)']],
                'Ngô Quyền, Dương Đình Nghệ là phe ta; Lưu Hoằng Tháo là giặc Nam Hán.', 'de');
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm KẾ SÁCH CỦA NGÔ QUYỀN hoặc KHÔNG PHẢI.',
                [['Đóng cọc gỗ đầu bịt sắt', 'Kế sách của Ngô Quyền'], ['Dụ giặc vào lúc nước lên', 'Kế sách của Ngô Quyền'],
                 ['Đánh úp khi nước rút', 'Kế sách của Ngô Quyền'], ['Cầu hoà nộp cống', 'Không phải']],
                'Kế sách: cọc ngầm + dụ giặc lúc nước lên + đánh úp lúc nước rút.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi ý nghĩa vào nhóm Ý NGHĨA CỦA CHIẾN THẮNG BẠCH ĐẰNG hoặc KHÔNG PHẢI.',
                [['Chấm dứt Bắc thuộc', 'Ý nghĩa của chiến thắng Bạch Đằng'], ['Mở đầu thời độc lập', 'Ý nghĩa của chiến thắng Bạch Đằng'],
                 ['Thống nhất 12 sứ quân', 'Không phải'], ['Đánh thắng quân Minh', 'Không phải']],
                'Bạch Đằng 938 chấm dứt Bắc thuộc, mở đầu kỉ nguyên độc lập.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Người chỉ huy trận Bạch Đằng năm 938 là Ngô ___.', [[0, 'Quyền']],
                'Ngô Quyền là con rể và tướng tài của Dương Đình Nghệ.', 'de');
            $this->fill($L, 'Vũ khí bí mật dưới lòng sông Bạch Đằng là cọc gỗ đầu bịt ___.', [[0, 'sắt']],
                'Cọc gỗ đâm thủng thuyền giặc khi nước rút.', 'trung_binh');
            $this->fill($L, 'Trận Bạch Đằng 938 đánh tan quân Nam ___.', [[0, 'Hán']],
                'Tướng giặc Lưu Hoằng Tháo tử trận.', 'de');
            $this->fill($L, 'Chiến thắng Bạch Đằng chấm dứt hơn 1000 năm Bắc ___.', [[0, 'thuộc']],
                'Mở đầu thời kì độc lập tự chủ của dân tộc.', 'trung_binh');
        }
    }

    // ================= LỊCH SỬ: ĐINH – TIỀN LÊ =================

    private function seedLsDinhTienLe(): void
    {
        $this->seedLsDinhTienLe71();
        $this->seedLsDinhTienLe72();
        $this->seedLsDinhTienLe81();
        $this->seedLsDinhTienLe82();
    }

    private function seedLsDinhTienLe71(): void
    {
        $L = 'ls-dinh-tien-le-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Sau khi Ngô Quyền mất, đất nước rơi vào tình trạng gì?',
                ['Loạn 12 sứ quân', 'Thái bình thịnh trị', 'Bị quân Tống đô hộ', 'Chia thành hai nước'], 0,
                'Các thế lực cát cứ nổi lên, sử gọi là loạn 12 sứ quân.', 'de');
            $this->quiz($L, 'Ai là người dẹp loạn 12 sứ quân, thống nhất đất nước?',
                ['Đinh Bộ Lĩnh', 'Lê Hoàn', 'Ngô Xương Văn', 'Dương Tam Kha'], 0,
                'Đinh Bộ Lĩnh dẹp loạn 12 sứ quân, thống nhất đất nước năm 968.', 'de');
            $this->quiz($L, 'Năm 968, Đinh Bộ Lĩnh lên ngôi, đặt tên nước là gì?',
                ['Đại Cồ Việt', 'Đại Việt', 'Âu Lạc', 'Văn Lang'], 0,
                'Đinh Bộ Lĩnh đặt tên nước là Đại Cồ Việt, khẳng định nước ta sánh ngang phương Bắc.', 'de');
            $this->quiz($L, 'Kinh đô của nước Đại Cồ Việt đặt ở đâu?',
                ['Hoa Lư', 'Cổ Loa', 'Thăng Long', 'Phong Châu'], 0,
                'Đinh Tiên Hoàng đóng đô ở Hoa Lư (Ninh Bình) – vùng núi hiểm trở, dễ phòng thủ.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự kiện với năm diễn ra.',
                [['Ngô Quyền mất', 'Năm 944'],
                 ['Loạn 12 sứ quân', '944 – 968'],
                 ['Đinh Bộ Lĩnh lên ngôi', 'Năm 968'],
                 ['Lê Hoàn lên ngôi', 'Năm 980']],
                '944 Ngô Quyền mất → loạn 12 sứ quân → 968 Đinh Bộ Lĩnh thống nhất.', 'de');
            $this->matching($L, 'Nối mỗi danh hiệu với nhân vật.',
                [['Đinh Tiên Hoàng', 'Đinh Bộ Lĩnh'],
                 ['Vạn Thắng Vương', 'Đinh Bộ Lĩnh lúc trẻ'],
                 ['Ngô Vương', 'Ngô Quyền'],
                 ['Lê Đại Hành', 'Lê Hoàn']],
                'Đinh Bộ Lĩnh: Đinh Tiên Hoàng; Ngô Quyền: Ngô Vương; Lê Hoàn: Lê Đại Hành.', 'de');
            $this->matching($L, 'Nối mỗi chi tiết với nội dung về nước Đại Cồ Việt.',
                [['Tên nước', 'Đại Cồ Việt'],
                 ['Niên hiệu', 'Thái Bình'],
                 ['Kinh đô', 'Hoa Lư'],
                 ['Ý nghĩa tên nước', 'Nước Việt lớn, sánh ngang phương Bắc']],
                'Đại Cồ Việt (968): kinh đô Hoa Lư, niên hiệu Thái Bình.', 'trung_binh');
            $this->matching($L, 'Nối mỗi triều đại với người sáng lập.',
                [['Nhà Ngô', 'Ngô Quyền'],
                 ['Nhà Đinh', 'Đinh Bộ Lĩnh'],
                 ['Nhà Tiền Lê', 'Lê Hoàn'],
                 ['Nhà Lý', 'Lý Công Uẩn']],
                'Ngô – Đinh – Tiền Lê – Lý là các triều đại đầu thời độc lập.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC NĂM 968 hoặc SAU NĂM 968.',
                [['Loạn 12 sứ quân', 'Trước năm 968'], ['Ngô Quyền mất (944)', 'Trước năm 968'],
                 ['Lê Hoàn lên ngôi (980)', 'Sau năm 968'], ['Kháng chiến chống Tống (981)', 'Sau năm 968']],
                '968 là mốc Đinh Bộ Lĩnh lên ngôi, lập nước Đại Cồ Việt.', 'de');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm THỜI LOẠN 12 SỨ QUÂN hoặc SAU KHI THỐNG NHẤT.',
                [['Đinh Bộ Lĩnh lúc trẻ', 'Thời loạn 12 sứ quân'], ['Các sứ quân cát cứ', 'Thời loạn 12 sứ quân'],
                 ['Đinh Tiên Hoàng', 'Sau khi thống nhất'], ['Lê Hoàn làm vua', 'Sau khi thống nhất']],
                'Đinh Bộ Lĩnh dẹp các sứ quân rồi lên ngôi hoàng đế.', 'de');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm KINH ĐÔ THỜI ĐINH hoặc KHÔNG PHẢI.',
                [['Hoa Lư', 'Kinh đô thời Đinh'], ['Tràng An (Ninh Bình)', 'Kinh đô thời Đinh'],
                 ['Thăng Long', 'Không phải'], ['Phú Xuân', 'Không phải']],
                'Hoa Lư (Ninh Bình) là kinh đô nước Đại Cồ Việt thời Đinh – Tiền Lê.', 'de');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm CÔNG LAO CỦA ĐINH BỘ LĨNH hoặc KHÔNG PHẢI.',
                [['Dẹp loạn 12 sứ quân', 'Công lao của Đinh Bộ Lĩnh'], ['Lập nước Đại Cồ Việt', 'Công lao của Đinh Bộ Lĩnh'],
                 ['Đánh thắng quân Nguyên', 'Không phải'], ['Dời đô ra Thăng Long', 'Không phải']],
                'Đinh Bộ Lĩnh dẹp loạn, lập nước Đại Cồ Việt; dời đô là Lý Công Uẩn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Sau khi Ngô Quyền mất, đất nước rơi vào loạn 12 sứ ___.', [[0, 'quân']],
                'Các sứ quân cát cứ mỗi người một vùng.', 'de');
            $this->fill($L, 'Người dẹp loạn 12 sứ quân là Đinh Bộ ___.', [[0, 'Lĩnh']],
                'Ông lên ngôi năm 968, hiệu là Đinh Tiên Hoàng.', 'de');
            $this->fill($L, 'Tên nước do Đinh Bộ Lĩnh đặt là Đại Cồ ___.', [[0, 'Việt']],
                'Tên nước khẳng định ý chí độc lập, tự cường.', 'de');
            $this->fill($L, 'Kinh đô nước Đại Cồ Việt đặt ở Hoa ___.', [[0, 'Lư']],
                'Hoa Lư nay thuộc tỉnh Ninh Bình.', 'de');
        }
    }

    private function seedLsDinhTienLe72(): void
    {
        $L = 'ls-dinh-tien-le-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trước khi lên ngôi, Lê Hoàn giữ chức vụ gì?',
                ['Thập đạo tướng quân', 'Thái sư', 'Lạc tướng', 'Bồ chính'], 0,
                'Lê Hoàn là Thập đạo tướng quân, chỉ huy quân đội nhà Đinh.', 'de');
            $this->quiz($L, 'Lê Hoàn lên ngôi vào năm nào, trong hoàn cảnh nào?',
                ['Năm 980, trước nguy cơ quân Tống xâm lược', 'Năm 968, sau khi dẹp loạn', 'Năm 938, sau Bạch Đằng', 'Năm 1010, khi dời đô'], 0,
                'Năm 980, trước hoạ ngoại xâm, Lê Hoàn được tôn lên làm vua.', 'de');
            $this->quiz($L, 'Năm 981, Lê Hoàn đã đánh tan quân xâm lược nào?',
                ['Quân Tống', 'Quân Nguyên', 'Quân Minh', 'Quân Nam Hán'], 0,
                'Năm 981, Lê Hoàn chỉ huy đánh tan quân Tống ở Bạch Đằng và Chi Lăng.', 'de');
            $this->quiz($L, 'Nhà Tiền Lê đóng đô ở đâu?',
                ['Hoa Lư', 'Thăng Long', 'Cổ Loa', 'Phú Xuân'], 0,
                'Nhà Tiền Lê tiếp tục đóng đô ở Hoa Lư như nhà Đinh.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi sự kiện với năm diễn ra.',
                [['Lê Hoàn lên ngôi', 'Năm 980'],
                 ['Chiến thắng Bạch Đằng – Chi Lăng', 'Năm 981'],
                 ['Nhà Đinh thành lập', 'Năm 968'],
                 ['Nhà Lý thành lập', 'Năm 1009']],
                '980 Lê Hoàn lên ngôi; 981 thắng Tống; 1009 nhà Lý thành lập.', 'de');
            $this->matching($L, 'Nối mỗi mặt trận với kết quả năm 981.',
                [['Mặt trận thuỷ (Bạch Đằng)', 'Đánh tan thuỷ quân Tống'],
                 ['Mặt trận bộ (Chi Lăng)', 'Chặn đứng bộ binh Tống'],
                 ['Ngoại giao', 'Giảng hoà, giữ quan hệ hoà hiếu'],
                 ['Kết quả chung', 'Bảo vệ vững chắc độc lập']],
                'Thắng lợi cả thuỷ lẫn bộ, sau đó giảng hoà với nhà Tống.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nhân vật với vai trò năm 980 – 981.',
                [['Lê Hoàn', 'Vua, tổng chỉ huy kháng chiến'],
                 ['Phạm Cự Lạng', 'Tướng ủng hộ Lê Hoàn lên ngôi'],
                 ['Hầu Nhân Bảo', 'Tướng Tống tử trận'],
                 ['Đinh Phế Đế', 'Vua nhỏ tuổi nhường ngôi']],
                'Lê Hoàn lên ngôi khi vua Đinh còn nhỏ tuổi, trước hoạ ngoại xâm.', 'trung_binh');
            $this->matching($L, 'Nối mỗi triều đại với kinh đô của nó.',
                [['Nhà Đinh', 'Hoa Lư'],
                 ['Nhà Tiền Lê', 'Hoa Lư'],
                 ['Nhà Lý (sau dời đô)', 'Thăng Long'],
                 ['Nhà Ngô', 'Cổ Loa']],
                'Đinh và Tiền Lê đều đóng đô ở Hoa Lư.', 'de');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm TRƯỚC KHI LÊ HOÀN LÊN NGÔI (980) hoặc SAU.',
                [['Đinh Tiên Hoàng bị ám hại', 'Trước khi Lê Hoàn lên ngôi (980)'], ['Quân Tống chuẩn bị xâm lược', 'Trước khi Lê Hoàn lên ngôi (980)'],
                 ['Chiến thắng chống Tống (981)', 'Sau'], ['Lê Hoàn mất (1005)', 'Sau']],
                '980 là mốc Lê Hoàn lên ngôi lập nhà Tiền Lê.', 'de');
            $this->sortQ($L, 'Kéo mỗi trận đánh vào nhóm CHỐNG QUÂN TỐNG hoặc KHÔNG PHẢI.',
                [['Bạch Đằng 981', 'Chống quân Tống'], ['Chi Lăng 981', 'Chống quân Tống'],
                 ['Bạch Đằng 938', 'Không phải'], ['Bạch Đằng 1288', 'Không phải']],
                'Bạch Đằng và Chi Lăng 981 đánh quân Tống; 938 đánh Nam Hán; 1288 đánh Nguyên.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm CHỦ TRƯƠNG CỦA LÊ HOÀN hoặc KHÔNG PHẢI.',
                [['Trực tiếp chỉ huy kháng chiến', 'Chủ trương của Lê Hoàn'], ['Giảng hoà với nhà Tống sau thắng lợi', 'Chủ trương của Lê Hoàn'],
                 ['Đầu hàng quân Tống', 'Không phải'], ['Bỏ kinh đô chạy giặc', 'Không phải']],
                'Lê Hoàn vừa đánh thắng vừa khéo léo giảng hoà, giữ yên bờ cõi.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi danh hiệu vào nhóm CỦA LÊ HOÀN hoặc CỦA NGƯỜI KHÁC.',
                [['Lê Đại Hành', 'Của Lê Hoàn'], ['Thập đạo tướng quân', 'Của Lê Hoàn'],
                 ['Đinh Tiên Hoàng', 'Của người khác'], ['Ngô Vương', 'Của người khác']],
                'Lê Hoàn: Thập đạo tướng quân, Lê Đại Hành; Đinh Tiên Hoàng là Đinh Bộ Lĩnh.', 'de');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Trước khi lên ngôi, Lê Hoàn là Thập đạo tướng ___.', [[0, 'quân']],
                'Ông chỉ huy toàn bộ quân đội nhà Đinh.', 'de');
            $this->fill($L, 'Lê Hoàn lên ngôi năm ___, lập nhà Tiền Lê.', [[0, '980']],
                'Trước nguy cơ quân Tống xâm lược.', 'de');
            $this->fill($L, 'Năm 981, quân dân ta đánh tan quân ___ xâm lược.', [[0, 'Tống']],
                'Thắng lợi trên cả mặt trận thuỷ và bộ.', 'de');
            $this->fill($L, 'Hai mặt trận thắng lợi năm 981 là Bạch Đằng và Chi ___.', [[0, 'Lăng']],
                'Chi Lăng nay thuộc tỉnh Lạng Sơn.', 'trung_binh');
        }
    }

    private function seedLsDinhTienLe81(): void
    {
        $L = 'ls-dinh-tien-le-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Người đứng đầu nhà nước thời Đinh – Tiền Lê là ai?',
                ['Vua (hoàng đế)', 'Thái sư', 'Lạc tướng', 'Bồ chính'], 0,
                'Vua là người đứng đầu, nắm mọi quyền hành trong nhà nước phong kiến.', 'trung_binh');
            $this->quiz($L, 'Giúp việc cho vua thời Đinh – Tiền Lê có các chức quan nào?',
                ['Thái sư, đại tổng quản; chia quan văn, quan võ', 'Chỉ có quan văn', 'Chỉ có quan võ', 'Không có quan lại'], 0,
                'Bộ máy gồm thái sư, đại tổng quản, chia thành quan văn và quan võ.', 'trung_binh');
            $this->quiz($L, 'Quân đội thời Đinh – Tiền Lê gồm những bộ phận nào?',
                ['Cấm quân và quân các lộ', 'Chỉ có thuỷ quân', 'Chỉ có tượng binh', 'Không có quân đội thường trực'], 0,
                'Quân đội gồm cấm quân bảo vệ kinh thành và quân đóng ở các lộ, phủ.', 'trung_binh');
            $this->quiz($L, 'Nhà Đinh đã làm gì để giữ kỉ cương đất nước?',
                ['Ban hành hình luật', 'Bãi bỏ mọi luật lệ', 'Giao hết quyền cho sứ quân', 'Không quan tâm'], 0,
                'Nhà Đinh ban hành hình luật – bộ luật thành văn đầu tiên để trừng trị kẻ có tội.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chức quan với nhiệm vụ của nó.',
                [['Vua', 'Đứng đầu, quyết định mọi việc'],
                 ['Thái sư', 'Quan đầu triều, giúp vua việc nước'],
                 ['Đại tổng quản', 'Trông coi việc võ'],
                 ['Tăng thống', 'Trông coi việc Phật giáo']],
                'Bộ máy nhà Đinh – Tiền Lê còn đơn giản nhưng đã có phân công rõ ràng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi bộ phận quân đội với nhiệm vụ.',
                [['Cấm quân', 'Bảo vệ kinh thành và vua'],
                 ['Quân các lộ', 'Giữ gìn an ninh các địa phương'],
                 ['Thuỷ quân', 'Chiến đấu trên sông biển'],
                 ['Dân binh', 'Lực lượng dự bị từ nhân dân']],
                'Quân đội được tổ chức thành nhiều bộ phận với nhiệm vụ riêng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi biện pháp với mục đích của nhà nước.',
                [['Đúc tiền đồng', 'Khẳng định chủ quyền, thuận tiện trao đổi'],
                 ['Ban hành luật', 'Giữ kỉ cương, trừng trị tội phạm'],
                 ['Xây dựng quân đội', 'Bảo vệ đất nước'],
                 ['Đặt quan cai trị các vùng', 'Củng cố quyền lực trung ương']],
                'Các biện pháp đều nhằm xây dựng nhà nước phong kiến độc lập vững mạnh.', 'trung_binh');
            $this->matching($L, 'Nối mỗi triều đại với đặc điểm tổ chức.',
                [['Nhà Đinh', 'Vua + thái sư, quan văn võ; cấm quân'],
                 ['Nhà Tiền Lê', 'Kế thừa nhà Đinh, củng cố thêm'],
                 ['Nhà Lý', 'Hoàn chỉnh hơn, có khoa cử'],
                 ['Thời Văn Lang', 'Còn đơn sơ: vua, lạc tướng, bồ chính']],
                'Tổ chức nhà nước ngày càng hoàn chỉnh từ Văn Lang đến Lý.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chức danh vào nhóm QUAN VĂN hoặc QUAN VÕ.',
                [['Thái sư', 'Quan văn'], ['Quan trông coi thuế ruộng', 'Quan văn'],
                 ['Đại tổng quản', 'Quan võ'], ['Tướng cấm quân', 'Quan võ']],
                'Thời Đinh – Tiền Lê đã phân biệt quan văn và quan võ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi bộ phận vào nhóm Ở KINH ĐÔ hoặc Ở ĐỊA PHƯƠNG.',
                [['Cấm quân', 'Ở kinh đô'], ['Thái sư, đại tổng quản', 'Ở kinh đô'],
                 ['Quân các lộ', 'Ở địa phương'], ['Quan cai quản phủ, châu', 'Ở địa phương']],
                'Kinh đô có cấm quân và quan đầu triều; địa phương có quân các lộ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi biện pháp vào nhóm VỀ CHÍNH TRỊ hoặc VỀ KINH TẾ.',
                [['Ban hành hình luật', 'Về chính trị'], ['Xây dựng quân đội', 'Về chính trị'],
                 ['Đúc tiền đồng Thái Bình', 'Về kinh tế'], ['Khuyến khích khai hoang', 'Về kinh tế']],
                'Chính trị: luật pháp, quân đội; kinh tế: tiền tệ, khai hoang.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm NHÀ NƯỚC PHONG KIẾN TẬP QUYỀN hoặc PHÂN TÁN.',
                [['Vua nắm mọi quyền hành', 'Phong kiến tập quyền'], ['Có quân đội trung ương', 'Phong kiến tập quyền'],
                 ['Sứ quân cát cứ', 'Phân tán'], ['Mỗi vùng một luật riêng', 'Phân tán']],
                'Đinh – Tiền Lê xây dựng nhà nước phong kiến tập quyền, xoá cát cứ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Người đứng đầu nhà nước thời Đinh – Tiền Lê là ___.', [[0, 'vua']],
                'Vua nắm mọi quyền hành: lập pháp, hành pháp.', 'trung_binh');
            $this->fill($L, 'Quan đầu triều giúp vua việc nước gọi là thái ___.', [[0, 'sư']],
                'Thái sư là chức quan cao nhất dưới vua.', 'trung_binh');
            $this->fill($L, 'Lực lượng bảo vệ kinh thành gọi là cấm ___.', [[0, 'quân']],
                'Cấm quân là đội quân tinh nhuệ nhất.', 'trung_binh');
            $this->fill($L, 'Nhà Đinh ban hành hình ___ để giữ kỉ cương.', [[0, 'luật']],
                'Đây là bộ luật thành văn đầu tiên của nước ta.', 'trung_binh');
        }
    }

    private function seedLsDinhTienLe82(): void
    {
        $L = 'ls-dinh-tien-le-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ngành kinh tế chính thời Đinh – Tiền Lê là gì?',
                ['Nông nghiệp trồng lúa nước', 'Buôn bán với châu Âu', 'Khai thác dầu mỏ', 'Du lịch'], 0,
                'Nông nghiệp trồng lúa nước là ngành kinh tế chính, nhà nước khuyến khích khai hoang.', 'trung_binh');
            $this->quiz($L, 'Đồng tiền đầu tiên của nước ta có tên là gì?',
                ['Tiền đồng Thái Bình', 'Tiền giấy', 'Tiền kẽm', 'Tiền vàng'], 0,
                'Nhà Đinh cho đúc tiền đồng Thái Bình – đồng tiền đầu tiên của Việt Nam.', 'trung_binh');
            $this->quiz($L, 'Nghề thủ công nào phát triển mạnh thời Đinh – Tiền Lê?',
                ['Đúc đồng, dệt, gốm', 'Chế tạo ô tô', 'Đóng tàu vũ trụ', 'Luyện thép hiện đại'], 0,
                'Thủ công nghiệp phát triển: đúc đồng, rèn sắt, dệt vải, làm gốm.', 'trung_binh');
            $this->quiz($L, 'Tôn giáo nào thịnh hành thời Đinh – Tiền Lê?',
                ['Phật giáo', 'Thiên Chúa giáo', 'Hồi giáo', 'Nho giáo độc tôn'], 0,
                'Phật giáo rất thịnh hành; nhiều chùa tháp được xây dựng, có chức Tăng thống.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chính sách kinh tế với nội dung của nó.',
                [['Khuyến khích khai hoang', 'Mở rộng ruộng đất'],
                 ['Đắp đê', 'Chống lũ, bảo vệ mùa màng'],
                 ['Đúc tiền đồng', 'Thuận tiện buôn bán'],
                 ['Lập xưởng thủ công', 'Phát triển nghề thủ công']],
                'Nhà nước chăm lo nông nghiệp và thủ công nghiệp.', 'trung_binh');
            $this->matching($L, 'Nối mỗi sản phẩm với nghề làm ra nó.',
                [['Tiền đồng Thái Bình', 'Nghề đúc đồng'],
                 ['Vải lụa', 'Nghề dệt'],
                 ['Đồ gốm', 'Nghề gốm'],
                 ['Công cụ sắt', 'Nghề rèn']],
                'Thủ công nghiệp thời Đinh – Tiền Lê khá đa dạng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi lễ hội/phong tục với ý nghĩa của nó.',
                [['Lễ tịch điền', 'Vua khuyến khích nông nghiệp'],
                 ['Xây chùa tháp', 'Phật giáo thịnh hành'],
                 ['Ăn trầu', 'Phong tục lâu đời'],
                 ['Đua thuyền', 'Lễ hội sông nước']],
                'Lễ tịch điền đầu xuân do vua chủ trì để khuyến nông.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tầng lớp với công việc chính.',
                [['Nông dân', 'Trồng lúa, nộp thuế'],
                 ['Thợ thủ công', 'Làm đồ gốm, dệt vải, đúc đồng'],
                 ['Thương nhân', 'Buôn bán'],
                 ['Quan lại', 'Giúp vua cai trị']],
                'Xã hội phân thành nông dân, thợ thủ công, thương nhân, quan lại.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm NÔNG NGHIỆP hoặc THỦ CÔNG NGHIỆP.',
                [['Trồng lúa nước', 'Nông nghiệp'], ['Đắp đê chống lũ', 'Nông nghiệp'],
                 ['Đúc tiền đồng', 'Thủ công nghiệp'], ['Dệt vải', 'Thủ công nghiệp']],
                'Nông nghiệp là gốc; thủ công nghiệp gồm đúc, rèn, dệt, gốm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi chính sách vào nhóm KHUYẾN KHÍCH SẢN XUẤT hoặc KHÔNG PHẢI.',
                [['Vua cày tịch điền', 'Khuyến khích sản xuất'], ['Khuyến khích khai hoang', 'Khuyến khích sản xuất'],
                 ['Tăng thuế nặng', 'Không phải'], ['Bỏ mặc đê điều', 'Không phải']],
                'Vua đích thân cày ruộng tịch điền để khuyến khích nông nghiệp.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi di tích vào nhóm THỜI ĐINH – TIỀN LÊ hoặc THỜI KHÁC.',
                [['Cố đô Hoa Lư', 'Thời Đinh – Tiền Lê'], ['Chùa Nhất Trụ (Hoa Lư)', 'Thời Đinh – Tiền Lê'],
                 ['Hoàng thành Thăng Long', 'Thời khác'], ['Chùa Một Cột', 'Thời khác (nhà Lý)']],
                'Hoa Lư và chùa Nhất Trụ thuộc thời Đinh – Tiền Lê.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm ĐỜI SỐNG VẬT CHẤT hoặc ĐỜI SỐNG TINH THẦN.',
                [['Cơm gạo, nhà ở', 'Đời sống vật chất'], ['Tiền đồng mua bán', 'Đời sống vật chất'],
                 ['Lễ hội, chùa chiền', 'Đời sống tinh thần'], ['Phong tục ăn trầu', 'Đời sống tinh thần']],
                'Đời sống vật chất: ăn mặc ở; tinh thần: lễ hội, tín ngưỡng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ngành kinh tế chính thời Đinh – Tiền Lê là nông ___.', [[0, 'nghiệp']],
                'Trồng lúa nước là nguồn sống chính của nhân dân.', 'trung_binh');
            $this->fill($L, 'Đồng tiền đầu tiên của nước ta là tiền đồng Thái ___.', [[0, 'Bình']],
                'Thái Bình là niên hiệu của Đinh Tiên Hoàng.', 'trung_binh');
            $this->fill($L, 'Tôn giáo thịnh hành thời Đinh – Tiền Lê là Phật ___.', [[0, 'giáo']],
                'Nhiều chùa tháp được xây dựng thời kì này.', 'trung_binh');
            $this->fill($L, 'Lễ vua đích thân cày ruộng đầu xuân gọi là lễ tịch ___.', [[0, 'điền']],
                'Lễ tịch điền nhằm khuyến khích nông nghiệp.', 'trung_binh');
        }
    }

    // ================= LỊCH SỬ: CHỐNG NGUYÊN – MÔNG =================

    private function seedLsChongNguyenMong(): void
    {
        $this->seedLsChongNguyenMong81();
        $this->seedLsChongNguyenMong82();
        $this->seedLsChongNguyenMong91();
        $this->seedLsChongNguyenMong92();
    }

    private function seedLsChongNguyenMong81(): void
    {
        $L = 'ls-chong-nguyen-mong-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Quân Mông – Nguyên đã mấy lần xâm lược Đại Việt?',
                ['3 lần', '1 lần', '2 lần', '5 lần'], 0,
                'Quân Mông – Nguyên xâm lược Đại Việt 3 lần: 1258, 1285, 1287–1288.', 'trung_binh');
            $this->quiz($L, 'Lần đầu quân Mông Cổ xâm lược (1258), ai là người chỉ huy kháng chiến?',
                ['Trần Thủ Độ', 'Trần Hưng Đạo', 'Trần Quang Khải', 'Trần Nhật Duật'], 0,
                'Năm 1258, Trần Thủ Độ chỉ huy đánh tan quân Mông Cổ ở Đông Bộ Đầu.', 'trung_binh');
            $this->quiz($L, 'Kế sách "vườn không nhà trống" có nghĩa là gì?',
                ['Rút lui, mang hết lương thực, để giặc không cướp được gì', 'Trồng thêm vườn tược', 'Xây nhà trống để ở', 'Đầu hàng giặc'], 0,
                'Quân dân ta rút lui, mang theo lương thực, giặc tiến sâu thì thiếu ăn, ốm đau.', 'trung_binh');
            $this->quiz($L, 'Hội nghị Diên Hồng thể hiện điều gì?',
                ['Quyết tâm đánh giặc của toàn dân', 'Ý định cầu hoà', 'Sự chia rẽ trong triều đình', 'Kế hoạch đầu hàng'], 0,
                'Các bô lão cả nước về dự hội nghị Diên Hồng, đồng thanh hô "đánh".', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lần kháng chiến với năm và kết quả.',
                [['Lần 1 (1258)', 'Thắng ở Đông Bộ Đầu'],
                 ['Lần 2 (1285)', 'Thắng ở Tây Kết, Hàm Tử'],
                 ['Lần 3 (1287–1288)', 'Thắng ở Bạch Đằng'],
                 ['Cả ba lần', 'Quân Nguyên – Mông đều thất bại']],
                'Ba lần kháng chiến đều thắng lợi hoàn toàn.', 'trung_binh');
            $this->matching($L, 'Nối mỗi danh tướng với chiến công của ông.',
                [['Trần Thủ Độ', 'Chỉ huy thắng lợi lần 1 (1258)'],
                 ['Trần Hưng Đạo', 'Tổng chỉ huy hai lần sau'],
                 ['Trần Quang Khải', 'Thắng ở Chương Dương'],
                 ['Trần Nhật Duật', 'Thắng ở Hàm Tử']],
                'Các tướng nhà Trần đều lập chiến công hiển hách.', 'trung_binh');
            $this->matching($L, 'Nối mỗi trận đánh với địa điểm.',
                [['Đông Bộ Đầu', 'Thăng Long (lần 1)'],
                 ['Chương Dương', 'Thăng Long (lần 2)'],
                 ['Tây Kết', 'Khoái Châu, Hưng Yên (lần 2)'],
                 ['Bạch Đằng', 'Quảng Ninh (lần 3)']],
                'Các trận đánh lớn diễn ra khắp từ Thăng Long đến Bạch Đằng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu nói với nhân vật.',
                [['"Đầu thần chưa rơi xuống đất, xin bệ hạ đừng lo"', 'Trần Thủ Độ'],
                 ['"Đánh"', 'Các bô lão ở Diên Hồng'],
                 ['"Ta thà làm ma nước Nam chứ không thèm làm vương đất Bắc"', 'Trần Bình Trọng'],
                 ['"Sát Thát"', 'Nghĩa quân nhà Trần']],
                'Những câu nói thể hiện khí phách anh hùng của quân dân nhà Trần.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi sự kiện vào nhóm LẦN 1 (1258) hoặc LẦN 2 – 3.',
                [['Trần Thủ Độ chỉ huy', 'Lần 1 (1258)'], ['Thắng ở Đông Bộ Đầu', 'Lần 1 (1258)'],
                 ['Trần Hưng Đạo tổng chỉ huy', 'Lần 2 – 3'], ['Hội nghị Diên Hồng', 'Lần 2 – 3']],
                'Lần 1 do Trần Thủ Độ chỉ huy; lần 2–3 do Trần Hưng Đạo tổng chỉ huy.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hành động vào nhóm KẾ SÁCH CỦA NHÀ TRẦN hoặc KHÔNG PHẢI.',
                [['Vườn không nhà trống', 'Kế sách của nhà Trần'], ['Rút lui bảo toàn lực lượng rồi phản công', 'Kế sách của nhà Trần'],
                 ['Đánh trực diện ngay từ đầu', 'Không phải'], ['Đầu hàng để giữ mạng', 'Không phải']],
                'Nhà Trần tránh chỗ mạnh, đánh chỗ yếu: rút lui rồi phản công.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm TƯỚNG NHÀ TRẦN hoặc TƯỚNG GIẶC.',
                [['Trần Hưng Đạo', 'Tướng nhà Trần'], ['Trần Quang Khải', 'Tướng nhà Trần'],
                 ['Thoát Hoan', 'Tướng giặc'], ['Ô Mã Nhi', 'Tướng giặc']],
                'Trần Hưng Đạo, Trần Quang Khải là tướng ta; Thoát Hoan, Ô Mã Nhi là tướng Nguyên.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm NGUYÊN NHÂN THẮNG LỢI hoặc KHÔNG PHẢI.',
                [['Đoàn kết toàn dân', 'Nguyên nhân thắng lợi'], ['Đường lối kháng chiến đúng đắn', 'Nguyên nhân thắng lợi'],
                 ['Giặc mạnh hơn ta', 'Không phải'], ['Nhờ may mắn', 'Không phải']],
                'Thắng lợi nhờ đoàn kết toàn dân và đường lối đúng đắn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Quân Mông – Nguyên xâm lược Đại Việt tổng cộng ___ lần.', [[0, '3']],
                'Các năm 1258, 1285 và 1287–1288.', 'trung_binh');
            $this->fill($L, 'Người chỉ huy thắng lợi lần thứ nhất (1258) là Trần Thủ ___.', [[0, 'Độ']],
                'Ông là thái sư đầu triều nhà Trần.', 'trung_binh');
            $this->fill($L, 'Kế sách rút lui, không để giặc cướp được lương thực gọi là "vườn không nhà ___".', [[0, 'trống']],
                'Kế sách làm giặc thiếu ăn, ốm đau, chán nản.', 'trung_binh');
            $this->fill($L, 'Hội nghị ___ Hồng thể hiện quyết tâm đánh giặc của toàn dân.', [[0, 'Diên']],
                'Các bô lão đồng thanh hô "đánh".', 'trung_binh');
        }
    }

    private function seedLsChongNguyenMong82(): void
    {
        $L = 'ls-chong-nguyen-mong-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trận Bạch Đằng năm 1288 do ai chỉ huy quân ta?',
                ['Trần Hưng Đạo', 'Trần Thủ Độ', 'Lê Hoàn', 'Ngô Quyền'], 0,
                'Trần Hưng Đạo (Trần Quốc Tuấn) là tổng chỉ huy trận Bạch Đằng 1288.', 'trung_binh');
            $this->quiz($L, 'Trần Hưng Đạo đã học kế sách của ai khi đánh trận Bạch Đằng 1288?',
                ['Ngô Quyền (năm 938)', 'Đinh Bộ Lĩnh', 'Lê Lợi', 'Quang Trung'], 0,
                'Trần Hưng Đạo học kế đóng cọc gỗ của Ngô Quyền năm 938.', 'trung_binh');
            $this->quiz($L, 'Tướng giặc nào bị quân ta bắt sống trong trận Bạch Đằng 1288?',
                ['Ô Mã Nhi', 'Thoát Hoan', 'Toa Đô', 'Lý Hằng'], 0,
                'Ô Mã Nhi bị bắt sống; Thoát Hoan phải chui vào ống đồng để chạy trốn.', 'trung_binh');
            $this->quiz($L, 'Trận Bạch Đằng 1288 có ý nghĩa gì?',
                ['Kết thúc thắng lợi cuộc kháng chiến lần 3', 'Mở đầu kháng chiến', 'Đánh thắng quân Tống', 'Thống nhất đất nước'], 0,
                'Bạch Đằng 1288 tiêu diệt đạo thuỷ binh giặc, kết thúc cuộc kháng chiến lần 3.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi chi tiết với vai trò trong trận Bạch Đằng 1288.',
                [['Cọc gỗ đầu bịt sắt', 'Đâm thủng thuyền giặc khi nước rút'],
                 ['Thuỷ triều', 'Được tính toán để dụ giặc'],
                 ['Thuyền nhẹ của ta', 'Nhử giặc vào trận địa'],
                 ['Quân mai phục', 'Đánh úp khi giặc mắc cạn']],
                'Kế sách: cọc ngầm + thuỷ triều + nhử giặc + đánh úp.', 'trung_binh');
            $this->matching($L, 'Nối mỗi tướng giặc với số phận của hắn.',
                [['Ô Mã Nhi', 'Bị bắt sống'],
                 ['Thoát Hoan', 'Chui ống đồng chạy trốn'],
                 ['Phàn Tiếp', 'Tử trận'],
                 ['Toa Đô', 'Tử trận ở Tây Kết (lần 2)']],
                'Tướng giặc kẻ chết, người bị bắt, kẻ chạy trốn nhục nhã.', 'trung_binh');
            $this->matching($L, 'Nối mỗi trận Bạch Đằng với người chỉ huy.',
                [['Năm 938', 'Ngô Quyền'],
                 ['Năm 981', 'Lê Hoàn'],
                 ['Năm 1288', 'Trần Hưng Đạo'],
                 ['Điểm chung', 'Đều dùng kế cọc gỗ']],
                'Ba trận Bạch Đằng đều dùng kế cọc ngầm và lợi dụng thuỷ triều.', 'trung_binh');
            $this->matching($L, 'Nối mỗi địa danh với sự kiện năm 1288.',
                [['Sông Bạch Đằng', 'Trận thuỷ chiến quyết định'],
                 ['Vạn Kiếp', 'Căn cứ của Trần Hưng Đạo'],
                 ['Thăng Long', 'Kinh đô được giải phóng'],
                 ['Chi Lăng', 'Nơi chặn đường rút của giặc (các lần trước)']],
                'Vạn Kiếp là đại bản doanh; Bạch Đằng là trận quyết chiến.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi bước vào nhóm TRƯỚC KHI ĐÁNH hoặc TRONG KHI ĐÁNH (trận Bạch Đằng 1288).',
                [['Đóng cọc gỗ xuống sông', 'Trước khi đánh'], ['Nghiên cứu thuỷ triều', 'Trước khi đánh'],
                 ['Nhử giặc vào trận địa', 'Trong khi đánh'], ['Đánh úp khi nước rút', 'Trong khi đánh']],
                'Trước: chuẩn bị cọc, nghiên cứu thuỷ triều; trong: nhử giặc, đánh úp.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nhân vật vào nhóm TƯỚNG TA hoặc TƯỚNG GIẶC (1288).',
                [['Trần Hưng Đạo', 'Tướng ta'], ['Trần Khánh Dư', 'Tướng ta'],
                 ['Ô Mã Nhi', 'Tướng giặc'], ['Phàn Tiếp', 'Tướng giặc']],
                'Trần Hưng Đạo, Trần Khánh Dư là tướng ta; Ô Mã Nhi, Phàn Tiếp là tướng Nguyên.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm KẾ SÁCH CỦA TRẦN HƯNG ĐẠO hoặc KHÔNG PHẢI.',
                [['Đóng cọc gỗ đầu bịt sắt', 'Kế sách của Trần Hưng Đạo'], ['Lợi dụng thuỷ triều lên xuống', 'Kế sách của Trần Hưng Đạo'],
                 ['Đánh úp khi thuyền giặc mắc cạn', 'Kế sách của Trần Hưng Đạo'], ['Cầu hoà nộp cống cho giặc', 'Không phải']],
                'Kế sách: cọc ngầm, thuỷ triều, đánh úp khi giặc mắc cạn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi kết quả vào nhóm CỦA TRẬN BẠCH ĐẰNG 1288 hoặc KHÔNG PHẢI.',
                [['Tiêu diệt thuỷ binh giặc', 'Của trận Bạch Đằng 1288'], ['Ô Mã Nhi bị bắt', 'Của trận Bạch Đằng 1288'],
                 ['Đánh thắng quân Minh', 'Không phải'], ['Thống nhất 12 sứ quân', 'Không phải']],
                'Bạch Đằng 1288: diệt thuỷ binh Nguyên, bắt Ô Mã Nhi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tổng chỉ huy trận Bạch Đằng 1288 là Trần Hưng ___.', [[0, 'Đạo']],
                'Tên thật của ông là Trần Quốc Tuấn.', 'trung_binh');
            $this->fill($L, 'Trần Hưng Đạo học kế đóng cọc của Ngô Quyền năm ___.', [[0, '938']],
                '350 năm trước, Ngô Quyền đã thắng Nam Hán trên sông này.', 'trung_binh');
            $this->fill($L, 'Tướng giặc bị bắt sống ở Bạch Đằng 1288 là Ô Mã ___.', [[0, 'Nhi']],
                'Thoát Hoan phải chui vào ống đồng mới thoát chết.', 'trung_binh');
            $this->fill($L, 'Trận Bạch Đằng 1288 tiêu diệt đạo ___ binh của giặc.', [[0, 'thuỷ']],
                'Mất thuỷ binh, quân Nguyên không còn đường tiếp tế.', 'trung_binh');
        }
    }

    private function seedLsChongNguyenMong91(): void
    {
        $L = 'ls-chong-nguyen-mong-lop-9-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Tên thật của Trần Hưng Đạo là gì?',
                ['Trần Quốc Tuấn', 'Trần Quang Khải', 'Trần Nhật Duật', 'Trần Thủ Độ'], 0,
                'Trần Hưng Đạo tên thật là Trần Quốc Tuấn, được phong Hưng Đạo Vương.', 'trung_binh');
            $this->quiz($L, '"Hịch tướng sĩ" được Trần Hưng Đạo viết vào khoảng thời gian nào?',
                ['Trước cuộc kháng chiến lần 2 (khoảng 1284)', 'Sau khi thắng lợi hoàn toàn', 'Thời nhà Lý', 'Thời nhà Lê'], 0,
                'Khoảng năm 1284, trước khi giặc sang lần 2, ông viết Hịch tướng sĩ.', 'trung_binh');
            $this->quiz($L, 'Mục đích chính của "Hịch tướng sĩ" là gì?',
                ['Kêu gọi tướng sĩ đoàn kết, quyết tâm đánh giặc', 'Kể tội vua quan', 'Cầu hoà với giặc', 'Mô tả cảnh đẹp đất nước'], 0,
                'Bài hịch khích lệ lòng yêu nước, kêu gọi tướng sĩ đồng lòng đánh giặc.', 'trung_binh');
            $this->quiz($L, 'Ngoài "Hịch tướng sĩ", Trần Hưng Đạo còn để lại tác phẩm quân sự nào?',
                ['Binh thư yếu lược', 'Đại Việt sử kí', 'Phú sông Bạch Đằng', 'Chiếu dời đô'], 0,
                'Ông còn soạn Binh thư yếu lược và Vạn Kiếp tông bí truyền thư về nghệ thuật quân sự.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi tác phẩm với nội dung của nó.',
                [['Hịch tướng sĩ', 'Kêu gọi tướng sĩ đánh giặc'],
                 ['Binh thư yếu lược', 'Sách về nghệ thuật quân sự'],
                 ['Vạn Kiếp tông bí truyền thư', 'Bí quyết dùng binh'],
                 ['Cả ba', 'Di sản của Trần Hưng Đạo']],
                'Hịch tướng sĩ là áng văn bất hủ; hai tác phẩm kia về quân sự.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu trong Hịch tướng sĩ với ý nghĩa.',
                [['Kể tội giặc', 'Khơi dậy lòng căm thù giặc'],
                 ['Nêu gương trung thần', 'Khích lệ noi theo'],
                 ['Phê phán thói hưởng lạc', 'Chấn chỉnh tướng sĩ'],
                 ['Kêu gọi đoàn kết', 'Tạo sức mạnh toàn quân']],
                'Bài hịch kết hợp lí lẽ và tình cảm để động viên tướng sĩ.', 'kho');
            $this->matching($L, 'Nối mỗi phẩm chất với biểu hiện của Trần Hưng Đạo.',
                [['Yêu nước', 'Đặt việc nước lên trên thù nhà'],
                 ['Trí tuệ', 'Bày mưu thắng giặc ba lần'],
                 ['Khoan dung', 'Tha cho kẻ từng chống đối mình'],
                 ['Khiêm tốn', 'Không nhận công lao về mình']],
                'Trần Hưng Đạo gác thù nhà (với Trần Quang Khải) vì việc nước.', 'kho');
            $this->matching($L, 'Nối mỗi nhân vật với đánh giá của sử sách.',
                [['Trần Hưng Đạo', 'Anh hùng dân tộc, thánh nhân'],
                 ['Trần Quang Khải', 'Tướng tài, hoà giải vì nước'],
                 ['Trần Bình Trọng', 'Khí phách "làm ma nước Nam"'],
                 ['Trần Thủ Độ', 'Công thần khai quốc nhà Trần']],
                'Các nhân vật nhà Trần đều được sử sách ca ngợi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi tác phẩm vào nhóm CỦA TRẦN HƯNG ĐẠO hoặc KHÔNG PHẢI.',
                [['Hịch tướng sĩ', 'Của Trần Hưng Đạo'], ['Binh thư yếu lược', 'Của Trần Hưng Đạo'],
                 ['Đại Việt sử kí toàn thư', 'Không phải'], ['Truyện Kiều', 'Không phải']],
                'Trần Hưng Đạo: Hịch tướng sĩ, Binh thư yếu lược.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nội dung vào nhóm CÓ TRONG HỊCH TƯỚNG SĨ hoặc KHÔNG.',
                [['Kể tội giặc Nguyên', 'Có trong Hịch tướng sĩ'], ['Kêu gọi tướng sĩ đoàn kết', 'Có trong Hịch tướng sĩ'],
                 ['Dạy cách trồng lúa', 'Không'], ['Bàn chuyện buôn bán', 'Không']],
                'Hịch tướng sĩ: kể tội giặc, nêu gương, kêu gọi đoàn kết đánh giặc.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi phẩm chất vào nhóm CỦA TRẦN HƯNG ĐẠO hoặc KHÔNG PHẢI.',
                [['Yêu nước, thương dân', 'Của Trần Hưng Đạo'], ['Trí dũng song toàn', 'Của Trần Hưng Đạo'],
                 ['Hèn nhát, sợ giặc', 'Không phải'], ['Tham lam, vơ vét', 'Không phải']],
                'Trần Hưng Đạo là bậc anh hùng yêu nước, trí dũng song toàn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm THỂ HIỆN LÒNG YÊU NƯỚC hoặc KHÔNG.',
                [['Gác thù nhà vì việc nước', 'Thể hiện lòng yêu nước'], ['Viết hịch động viên tướng sĩ', 'Thể hiện lòng yêu nước'],
                 ['Đầu hàng giặc để an thân', 'Không'], ['Tranh giành quyền lực', 'Không']],
                'Gác thù riêng, viết hịch vì nước là lòng yêu nước cao cả.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tên thật của Trần Hưng Đạo là Trần Quốc ___.', [[0, 'Tuấn']],
                'Ông được phong Hưng Đạo Vương.', 'trung_binh');
            $this->fill($L, 'Áng văn bất hủ của Trần Hưng Đạo là "Hịch tướng ___".', [[0, 'sĩ']],
                'Bài hịch viết khoảng năm 1284.', 'trung_binh');
            $this->fill($L, 'Tác phẩm quân sự của Trần Hưng Đạo là "Binh thư yếu ___".', [[0, 'lược']],
                'Cùng với Vạn Kiếp tông bí truyền thư.', 'kho');
            $this->fill($L, 'Trần Hưng Đạo được nhân dân tôn thờ là Đức ___ Trần.', [[0, 'Thánh']],
                'Đền thờ ông có ở nhiều nơi, tiêu biểu là đền Kiếp Bạc.', 'trung_binh');
        }
    }

    private function seedLsChongNguyenMong92(): void
    {
        $L = 'ls-chong-nguyen-mong-lop-9-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ý nghĩa lớn nhất của thắng lợi ba lần kháng chiến chống Nguyên – Mông là gì?',
                ['Đập tan tham vọng xâm lược, bảo vệ vững chắc độc lập dân tộc', 'Mở rộng lãnh thổ', 'Thu được nhiều chiến lợi phẩm', 'Được phong vương'], 0,
                'Thắng lợi đập tan ý đồ xâm lược của đế quốc Nguyên – Mông, giữ vững độc lập.', 'kho');
            $this->quiz($L, 'Bài học lớn nhất rút ra từ thắng lợi chống Nguyên – Mông là gì?',
                ['Đoàn kết toàn dân và đường lối kháng chiến đúng đắn', 'Nhờ vũ khí hiện đại', 'Nhờ giặc tự rút lui', 'Nhờ viện trợ nước ngoài'], 0,
                'Sức mạnh đoàn kết toàn dân từ vua quan đến bô lão là bài học lớn nhất.', 'kho');
            $this->quiz($L, 'Nghệ thuật quân sự của nhà Trần được đúc kết bằng câu nào?',
                ['Lấy yếu chống mạnh, lấy ít địch nhiều', 'Đánh nhanh thắng nhanh', 'Phòng thủ bị động', 'Cầu viện nước ngoài'], 0,
                'Nhà Trần phát huy lối đánh "lấy yếu chống mạnh, lấy ít địch nhiều".', 'kho');
            $this->quiz($L, 'Thắng lợi của Đại Việt có ý nghĩa gì với các nước trong khu vực?',
                ['Góp phần chặn đứng làn sóng xâm lược của Mông – Nguyên xuống Đông Nam Á', 'Không ảnh hưởng gì', 'Khiến các nước sợ hãi Đại Việt', 'Buộc các nước phải cống nạp'], 0,
                'Đại Việt là một trong số ít nước đánh bại Mông – Nguyên, góp phần bảo vệ cả khu vực.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nguyên nhân với vai trò trong thắng lợi.',
                [['Vua tôi đồng lòng', 'Lãnh đạo sáng suốt, quyết tâm'],
                 ['Toàn dân đoàn kết', 'Sức mạnh tổng hợp'],
                 ['Đường lối đúng', 'Tránh chỗ mạnh, đánh chỗ yếu'],
                 ['Địa hình hiểm trở', 'Lợi thế sân nhà']],
                'Thắng lợi là tổng hoà nhiều nguyên nhân, trong đó đoàn kết là quyết định.', 'kho');
            $this->matching($L, 'Nối mỗi bài học với ý nghĩa cho hôm nay.',
                [['Đoàn kết toàn dân', 'Sức mạnh vô địch trong mọi thời đại'],
                 ['Tự lực tự cường', 'Không trông chờ vào bên ngoài'],
                 ['Kết hợp đánh và đàm', 'Vừa kiên quyết vừa khôn khéo'],
                 ['Xây dựng quốc phòng', 'Giữ nước từ khi chưa có giặc']],
                'Bài học lịch sử vẫn nguyên giá trị trong sự nghiệp bảo vệ Tổ quốc hôm nay.', 'kho');
            $this->matching($L, 'Nối mỗi cuộc kháng chiến trong lịch sử với kẻ thù.',
                [['Chống Nguyên – Mông', 'Đế quốc Mông – Nguyên'],
                 ['Chống Tống (1077)', 'Nhà Tống'],
                 ['Chống Minh', 'Nhà Minh'],
                 ['Chống Pháp – Mỹ', 'Thực dân, đế quốc hiện đại']],
                'Dân tộc ta có truyền thống đấu tranh chống ngoại xâm hào hùng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi danh nhân với công lao.',
                [['Trần Hưng Đạo', 'Tổng chỉ huy ba lần kháng chiến'],
                 ['Trần Thủ Độ', 'Người mở đầu thắng lợi 1258'],
                 ['Lý Thường Kiệt', 'Thắng Tống ở Như Nguyệt 1077'],
                 ['Lê Lợi', 'Đánh đuổi quân Minh, lập nhà Lê']],
                'Các anh hùng dân tộc qua các thời kì đều được nhân dân tôn thờ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi yếu tố vào nhóm NGUYÊN NHÂN CHỦ QUAN hoặc KHÁCH QUAN của thắng lợi.',
                [['Đoàn kết toàn dân', 'Nguyên nhân chủ quan'], ['Đường lối kháng chiến đúng', 'Nguyên nhân chủ quan'],
                 ['Giặc xa hậu phương, thiếu lương thực', 'Nguyên nhân khách quan'], ['Khí hậu khắc nghiệt với giặc', 'Nguyên nhân khách quan']],
                'Chủ quan: đoàn kết, đường lối; khách quan: giặc xa nhà, khí hậu.', 'kho');
            $this->sortQ($L, 'Kéo mỗi bài học vào nhóm VỀ ĐOÀN KẾT hoặc VỀ NGHỆ THUẬT QUÂN SỰ.',
                [['Vua tôi đồng lòng, toàn dân đoàn kết', 'Về đoàn kết'], ['Diên Hồng đồng thanh hô đánh', 'Về đoàn kết'],
                 ['Vườn không nhà trống', 'Về nghệ thuật quân sự'], ['Lấy yếu chống mạnh', 'Về nghệ thuật quân sự']],
                'Đoàn kết là sức mạnh; vườn không nhà trống là nghệ thuật.', 'kho');
            $this->sortQ($L, 'Kéo mỗi nhận định vào nhóm ĐÚNG hoặc SAI về ý nghĩa thắng lợi.',
                [['Bảo vệ vững chắc nền độc lập', 'Đúng'], ['Đập tan tham vọng của Nguyên – Mông', 'Đúng'],
                 ['Mở rộng lãnh thổ sang Trung Quốc', 'Sai'], ['Trở thành nước chư hầu của Nguyên', 'Sai']],
                'Thắng lợi giữ vững độc lập, đập tan tham vọng xâm lược của giặc.', 'kho');
            $this->sortQ($L, 'Kéo mỗi giá trị vào nhóm CÒN NGUYÊN GIÁ TRỊ HÔM NAY hoặc CHỈ CÓ Ý NGHĨA XƯA.',
                [['Tinh thần đoàn kết toàn dân', 'Còn nguyên giá trị hôm nay'], ['Lòng yêu nước', 'Còn nguyên giá trị hôm nay'],
                 ['Vũ khí cọc gỗ', 'Chỉ có ý nghĩa xưa'], ['Chiến thuật cụ thể từng trận', 'Chỉ có ý nghĩa xưa']],
                'Tinh thần yêu nước, đoàn kết còn mãi; vũ khí, chiến thuật cụ thể thuộc về quá khứ.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bài học lớn nhất là sức mạnh đoàn kết ___ dân.', [[0, 'toàn']],
                'Từ vua quan đến bô lão, nông dân đều một lòng đánh giặc.', 'kho');
            $this->fill($L, 'Nghệ thuật quân sự nhà Trần: lấy yếu chống ___, lấy ít địch nhiều.', [[0, 'mạnh']],
                'Tránh chỗ mạnh, đánh chỗ yếu của giặc.', 'kho');
            $this->fill($L, 'Thắng lợi đập tan tham vọng xâm lược của đế quốc Nguyên – ___.', [[0, 'Mông']],
                'Bảo vệ vững chắc nền độc lập dân tộc.', 'kho');
            $this->fill($L, 'Truyền thống yêu nước, đoàn kết là bài học còn nguyên giá trị cho sự nghiệp bảo vệ ___ quốc hôm nay.', [[0, 'Tổ']],
                'Bài học lịch sử soi sáng hiện tại và tương lai.', 'kho');
        }
    }

    // ================= NGỮ VĂN: TỪ LOẠI =================

    private function seedNvTuLoai(): void
    {
        $this->seedNvTuLoai61();
        $this->seedNvTuLoai62();
        $this->seedNvTuLoai71();
        $this->seedNvTuLoai72();
    }

    private function seedNvTuLoai61(): void
    {
        $L = 'nv-tu-loai-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Danh từ là gì?',
                ['Từ chỉ người, vật, hiện tượng, khái niệm', 'Từ chỉ hành động', 'Từ chỉ đặc điểm', 'Từ nối các từ ngữ'], 0,
                'Danh từ chỉ người, vật, hiện tượng, khái niệm: học sinh, bàn ghế, tình bạn.');
            $this->quiz($L, 'Từ nào sau đây là danh từ riêng?',
                ['Hà Nội', 'học sinh', 'con sông', 'ngôi nhà'], 0,
                'Danh từ riêng chỉ tên riêng của người, địa danh... và viết hoa: Hà Nội.');
            $this->quiz($L, 'Từ nào sau đây là danh từ chung?',
                ['quyển sách', 'Nguyễn Du', 'sông Hồng', 'Việt Nam'], 0,
                'Danh từ chung chỉ loại sự vật: quyển sách; các từ còn lại là danh từ riêng.');
            $this->quiz($L, 'Cụm từ nào sau đây là cụm danh từ?',
                ['những bông hoa đẹp', 'đang nở rộ', 'rất đẹp', 'chạy nhanh'], 0,
                'Cụm danh từ gồm danh từ trung tâm (bông hoa) và thành tố phụ (những, đẹp).');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với từ loại của nó.',
                [['bàn ghế', 'Danh từ'],
                 ['chạy', 'Động từ'],
                 ['đẹp', 'Tính từ'],
                 ['và', 'Quan hệ từ']],
                'Bàn ghế là danh từ, chạy là động từ, đẹp là tính từ, và là quan hệ từ.');
            $this->matching($L, 'Nối mỗi danh từ với phân loại của nó.',
                [['Hà Nội', 'Danh từ riêng'],
                 ['sông Hồng', 'Danh từ riêng'],
                 ['học sinh', 'Danh từ chung'],
                 ['tình bạn', 'Danh từ chung (trừu tượng)']],
                'Hà Nội, sông Hồng viết hoa; học sinh, tình bạn là danh từ chung.');
            $this->matching($L, 'Nối mỗi cụm danh từ với từ trung tâm của nó.',
                [['những bông hoa đẹp', 'bông hoa'],
                 ['các bạn học sinh', 'bạn học sinh'],
                 ['một ngôi nhà nhỏ', 'ngôi nhà'],
                 ['con sông quê hương', 'sông']],
                'Từ trung tâm là danh từ chính, các từ còn lại bổ sung ý nghĩa.');
            $this->matching($L, 'Nối mỗi từ với nhóm danh từ của nó.',
                [['cô giáo', 'Danh từ chỉ người'],
                 ['quyển vở', 'Danh từ chỉ vật'],
                 ['cơn mưa', 'Danh từ chỉ hiện tượng'],
                 ['lòng yêu nước', 'Danh từ chỉ khái niệm']],
                'Danh từ chỉ người, vật, hiện tượng tự nhiên và khái niệm trừu tượng.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ hoặc KHÔNG PHẢI DANH TỪ.',
                [['trường học', 'Danh từ'], ['bạn bè', 'Danh từ'],
                 ['chạy nhảy', 'Không phải danh từ'], ['xinh đẹp', 'Không phải danh từ']],
                'Trường học, bạn bè là danh từ; chạy nhảy là động từ, xinh đẹp là tính từ.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm DANH TỪ RIÊNG hoặc DANH TỪ CHUNG.',
                [['Hồ Gươm', 'Danh từ riêng'], ['Bác Hồ', 'Danh từ riêng'],
                 ['hồ nước', 'Danh từ chung'], ['bác sĩ', 'Danh từ chung']],
                'Hồ Gươm, Bác Hồ viết hoa; hồ nước, bác sĩ là danh từ chung.');
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm CỤM DANH TỪ hoặc KHÔNG PHẢI.',
                [['những cánh đồng lúa', 'Cụm danh từ'], ['một buổi sáng đẹp trời', 'Cụm danh từ'],
                 ['đang bay lượn', 'Không phải'], ['rất nhanh nhẹn', 'Không phải']],
                'Cụm danh từ lấy danh từ làm trung tâm.');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm VIẾT HOA hoặc KHÔNG VIẾT HOA.',
                [['Việt Nam', 'Viết hoa'], ['Nguyễn Du', 'Viết hoa'],
                 ['quê hương', 'Không viết hoa'], ['dòng sông', 'Không viết hoa']],
                'Danh từ riêng (tên người, địa danh) phải viết hoa chữ cái đầu.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ chỉ người, vật, hiện tượng, khái niệm gọi là danh ___.', [[0, 'từ']],
                'Ví dụ: học sinh, bàn ghế, cơn mưa, tình bạn.');
            $this->fill($L, 'Danh từ riêng chỉ tên riêng và phải viết ___.', [[0, 'hoa']],
                'Ví dụ: Hà Nội, sông Hồng, Nguyễn Du.');
            $this->fill($L, 'Trong cụm "những bông hoa đẹp", từ trung tâm là "bông ___".', [[0, 'hoa']],
                'Các từ "những", "đẹp" bổ sung ý nghĩa cho "bông hoa".');
            $this->fill($L, '"Tình bạn" là danh từ chỉ khái ___.', [[0, 'niệm']],
                'Khái niệm trừu tượng vẫn là danh từ.');
        }
    }

    private function seedNvTuLoai62(): void
    {
        $L = 'nv-tu-loai-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Động từ là gì?',
                ['Từ chỉ hành động, trạng thái của sự vật', 'Từ chỉ người, vật', 'Từ chỉ đặc điểm', 'Từ nối câu'], 0,
                'Động từ chỉ hành động, trạng thái: chạy, đọc, ngủ, suy nghĩ.');
            $this->quiz($L, 'Tính từ là gì?',
                ['Từ chỉ đặc điểm, tính chất của sự vật', 'Từ chỉ hành động', 'Từ chỉ người', 'Từ chỉ số lượng'], 0,
                'Tính từ chỉ đặc điểm, tính chất: đẹp, cao, nhanh, hiền lành.');
            $this->quiz($L, 'Từ nào sau đây là động từ?',
                ['đọc sách', 'quyển sách', 'rất hay', 'bàn ghế'], 0,
                'Đọc sách chỉ hành động; quyển sách, bàn ghế là danh từ; rất hay có tính từ.');
            $this->quiz($L, 'Cụm từ nào sau đây là cụm tính từ?',
                ['rất đẹp và hiền', 'đang chạy nhanh', 'những bông hoa', 'cô giáo dạy'], 0,
                'Cụm tính từ lấy tính từ làm trung tâm: rất đẹp và hiền.');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ với từ loại của nó.',
                [['đọc', 'Động từ'],
                 ['xanh', 'Tính từ'],
                 ['quyển vở', 'Danh từ'],
                 ['một cách chăm chỉ', 'Cụm từ bổ sung']],
                'Đọc là động từ, xanh là tính từ, quyển vở là danh từ.');
            $this->matching($L, 'Nối mỗi cụm từ với loại cụm của nó.',
                [['đang học bài', 'Cụm động từ'],
                 ['rất chăm chỉ', 'Cụm tính từ'],
                 ['những quyển sách hay', 'Cụm danh từ'],
                 ['chạy rất nhanh', 'Cụm động từ']],
                'Cụm động từ: đang học bài; cụm tính từ: rất chăm chỉ.');
            $this->matching($L, 'Nối mỗi động từ với nhóm nghĩa của nó.',
                [['chạy, nhảy', 'Động từ chỉ hành động'],
                 ['ngủ, thức', 'Động từ chỉ trạng thái'],
                 ['yêu, ghét', 'Động từ chỉ tình cảm'],
                 ['nghĩ, nhớ', 'Động từ chỉ hoạt động trí óc']],
                'Động từ có nhiều nhóm nghĩa: hành động, trạng thái, tình cảm, trí óc.');
            $this->matching($L, 'Nối mỗi tính từ với nhóm nghĩa của nó.',
                [['đẹp, xấu', 'Tính từ chỉ hình dáng'],
                 ['hiền, dữ', 'Tính từ chỉ tính cách'],
                 ['nóng, lạnh', 'Tính từ chỉ cảm giác'],
                 ['nhanh, chậm', 'Tính từ chỉ mức độ']],
                'Tính từ miêu tả hình dáng, tính cách, cảm giác, mức độ.');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm ĐỘNG TỪ hoặc TÍNH TỪ.',
                [['chạy', 'Động từ'], ['đọc', 'Động từ'],
                 ['đẹp', 'Tính từ'], ['cao', 'Tính từ']],
                'Chạy, đọc chỉ hành động; đẹp, cao chỉ đặc điểm.');
            $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm CỤM ĐỘNG TỪ hoặc CỤM TÍNH TỪ.',
                [['đang đá bóng', 'Cụm động từ'], ['sẽ đi học', 'Cụm động từ'],
                 ['rất xinh xắn', 'Cụm tính từ'], ['hơi buồn', 'Cụm tính từ']],
                'Cụm động từ có từ chỉ ý tiếp diễn (đang, sẽ); cụm tính từ có từ chỉ mức độ (rất, hơi).');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm ĐỘNG TỪ CHỈ HÀNH ĐỘNG hoặc CHỈ TRẠNG THÁI.',
                [['đá bóng', 'Chỉ hành động'], ['viết chữ', 'Chỉ hành động'],
                 ['ngủ', 'Chỉ trạng thái'], ['thức', 'Chỉ trạng thái']],
                'Đá bóng, viết chữ là hành động; ngủ, thức là trạng thái.');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ ĐỘNG TỪ hoặc KHÔNG CÓ ĐỘNG TỪ.',
                [['Em đang đọc truyện.', 'Có động từ'], ['Chim bay trên trời.', 'Có động từ'],
                 ['Bông hoa đẹp.', 'Không có động từ'], ['Trời xanh.', 'Không có động từ']],
                'Câu có động từ: đang đọc, bay; câu chỉ có tính từ làm vị ngữ thì không.');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ chỉ hành động, trạng thái gọi là động ___.', [[0, 'từ']],
                'Ví dụ: chạy, ngủ, suy nghĩ.');
            $this->fill($L, 'Từ chỉ đặc điểm, tính chất gọi là tính ___.', [[0, 'từ']],
                'Ví dụ: đẹp, cao, hiền lành.');
            $this->fill($L, 'Trong cụm "đang học bài", từ trung tâm là từ "___".', [[0, 'học']],
                '"Đang" chỉ ý tiếp diễn, "học" là động từ trung tâm.');
            $this->fill($L, 'Trong cụm "rất chăm chỉ", từ chỉ mức độ là từ "___".', [[0, 'rất']],
                '"Chăm chỉ" là tính từ trung tâm.');
        }
    }

    private function seedNvTuLoai71(): void
    {
        $L = 'nv-tu-loai-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Trong câu "Quả bóng đá lăn tròn", từ "đá" thuộc từ loại nào?',
                ['Danh từ', 'Động từ', 'Tính từ', 'Quan hệ từ'], 0,
                'Ở đây "đá" chỉ chất liệu làm bóng, là danh từ.', 'de');
            $this->quiz($L, 'Trong câu "Anh ấy đá bóng rất giỏi", từ "đá" thuộc từ loại nào?',
                ['Động từ', 'Danh từ', 'Tính từ', 'Phó từ'], 0,
                'Ở đây "đá" chỉ hành động, là động từ.', 'de');
            $this->quiz($L, 'Muốn xác định đúng từ loại của một từ, cần làm gì?',
                ['Đặt từ vào ngữ cảnh câu cụ thể', 'Đoán theo cảm tính', 'Tra từ điển là đủ', 'Nhìn hình thức của từ'], 0,
                'Một từ có thể thuộc từ loại khác nhau tùy ngữ cảnh, phải xét trong câu.', 'de');
            $this->quiz($L, 'Trong câu "Cô ấy hát hay", từ "hay" thuộc từ loại nào?',
                ['Tính từ', 'Động từ', 'Danh từ', 'Quan hệ từ'], 0,
                '"Hay" chỉ đặc điểm của tiếng hát, là tính từ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi từ trong câu với từ loại đúng của nó.',
                [['Em (trong "Em đi học")', 'Danh từ (đại từ)'],
                 ['đi (trong "Em đi học")', 'Động từ'],
                 ['học (trong "Em đi học")', 'Động từ'],
                 ['chăm chỉ (trong "Em học chăm chỉ")', 'Tính từ']],
                'Phải xét từ trong câu cụ thể mới xác định đúng từ loại.', 'de');
            $this->matching($L, 'Nối mỗi từ đa nghĩa với từ loại trong ngữ cảnh cho sẵn.',
                [['đẹp (Cô ấy đẹp)', 'Tính từ'],
                 ['đẹp (Vẻ đẹp)', 'Danh từ'],
                 ['ăn (Ăn cơm)', 'Động từ'],
                 ['ăn (Món ăn)', 'Danh từ']],
                'Cùng một hình thức từ nhưng ngữ cảnh khác thì từ loại khác.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với từ loại của từ gạch chân.',
                [['"Trời mưa to." (mưa)', 'Động từ'],
                 ['"Cơn mưa rào." (mưa)', 'Danh từ'],
                 ['"Hoa nở đẹp." (đẹp)', 'Tính từ'],
                 ['"Vẻ đẹp quê hương." (đẹp)', 'Danh từ']],
                '"Mưa" vừa là động từ vừa là danh từ tùy câu; "đẹp" cũng vậy.', 'trung_binh');
            $this->matching($L, 'Nối mỗi lỗi sai với cách sửa.',
                [['Nhầm "chạy" là tính từ', 'Xét nghĩa: chạy chỉ hành động → động từ'],
                 ['Nhầm "xanh" là động từ', 'Xét nghĩa: xanh chỉ màu sắc → tính từ'],
                 ['Nhầm "bàn" là động từ', 'Xét nghĩa: bàn chỉ đồ vật → danh từ'],
                 ['Đoán mò không xét câu', 'Luôn đặt từ vào ngữ cảnh câu']],
                'Xác định từ loại phải dựa vào nghĩa và chức năng trong câu.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Xét từ "sáng" trong câu: kéo mỗi câu vào nhóm "SÁNG" LÀ DANH TỪ hoặc LÀ TÍNH TỪ.',
                [['Buổi sáng đẹp trời. (sáng)', '"Sáng" là danh từ'], ['Một sáng mùa thu. (sáng)', '"Sáng" là danh từ'],
                 ['Trời sáng rồi. (sáng)', '"Sáng" là tính từ'], ['Nụ cười sáng ngời. (sáng)', '"Sáng" là tính từ']],
                '"Buổi sáng" chỉ thời gian (danh từ); "trời sáng" chỉ trạng thái (tính từ).', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ LOẠI ĐÚNG hoặc SAI trong câu "Bạn ấy học giỏi".',
                [['Bạn ấy – danh từ', 'Từ loại đúng'], ['học – động từ', 'Từ loại đúng'],
                 ['giỏi – tính từ', 'Từ loại đúng'], ['học – tính từ', 'Sai']],
                'Bạn ấy (danh từ) – học (động từ) – giỏi (tính từ).', 'de');
            $this->sortQ($L, 'Kéo mỗi cách làm vào nhóm ĐÚNG hoặc SAI khi xác định từ loại.',
                [['Xét từ trong câu cụ thể', 'Đúng'], ['Xét nghĩa và chức năng của từ', 'Đúng'],
                 ['Đoán mò theo cảm tính', 'Sai'], ['Áp đặt một từ loại cho mọi ngữ cảnh', 'Sai']],
                'Phải xét từ trong ngữ cảnh câu, dựa vào nghĩa và chức năng.', 'de');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm CÓ THỂ LÀM NHIỀU TỪ LOẠI hoặc CHỈ MỘT TỪ LOẠI.',
                [['đá', 'Có thể làm nhiều từ loại'], ['ăn', 'Có thể làm nhiều từ loại'],
                 ['và', 'Chỉ một từ loại'], ['những', 'Chỉ một từ loại']],
                'Đá, ăn có thể là danh từ hoặc động từ; và, những chỉ là một từ loại.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Muốn xác định từ loại phải đặt từ vào ngữ ___ cụ thể.', [[0, 'cảnh']],
                'Cùng một từ, ngữ cảnh khác nhau có thể thuộc từ loại khác nhau.', 'de');
            $this->fill($L, 'Trong "quả bóng đá", từ "đá" là danh ___.', [[0, 'từ']],
                'Đá chỉ chất liệu làm bóng.', 'de');
            $this->fill($L, 'Trong "anh ấy đá bóng", từ "đá" là động ___.', [[0, 'từ']],
                'Đá chỉ hành động.', 'de');
            $this->fill($L, 'Xác định từ loại dựa vào nghĩa và chức ___ của từ trong câu.', [[0, 'năng']],
                'Không đoán mò theo cảm tính.', 'trung_binh');
        }
    }

    private function seedNvTuLoai72(): void
    {
        $L = 'nv-tu-loai-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Từ nào sau đây là lượng từ?',
                ['những', 'đẹp', 'chạy', 'và'], 0,
                'Lượng từ chỉ lượng ít nhiều: những, các, mỗi, một, vài.', 'de');
            $this->quiz($L, 'Từ nào sau đây là quan hệ từ?',
                ['và', 'những', 'đẹp', 'chạy'], 0,
                'Quan hệ từ nối các từ ngữ: và, với, cùng, nhưng, của.', 'de');
            $this->quiz($L, 'Trong cụm "những bông hoa", từ "những" là gì?',
                ['Lượng từ', 'Danh từ', 'Động từ', 'Tính từ'], 0,
                '"Những" chỉ số nhiều, là lượng từ đứng trước danh từ.', 'trung_binh');
            $this->quiz($L, 'Trong câu "Tôi đi học cùng bạn", từ "cùng" là gì?',
                ['Quan hệ từ', 'Lượng từ', 'Động từ', 'Tính từ'], 0,
                '"Cùng" nối "tôi" với "bạn", biểu thị quan hệ cùng nhau – là quan hệ từ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi lượng từ với ý nghĩa của nó.',
                [['những, các', 'Chỉ số nhiều'],
                 ['mỗi, một', 'Chỉ số ít, từng cái'],
                 ['vài', 'Chỉ số lượng ít không xác định'],
                 ['mọi', 'Chỉ toàn thể']],
                'Lượng từ đứng trước danh từ để chỉ lượng.', 'de');
            $this->matching($L, 'Nối mỗi quan hệ từ với quan hệ nó biểu thị.',
                [['và, với', 'Quan hệ nối liền'],
                 ['nhưng, mà', 'Quan hệ tương phản'],
                 ['của', 'Quan hệ sở hữu'],
                 ['ở, từ, đến', 'Quan hệ nơi chốn, thời gian']],
                'Quan hệ từ biểu thị quan hệ giữa các từ ngữ trong câu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi từ trong câu với từ loại của nó.',
                [['những (những học sinh)', 'Lượng từ'],
                 ['và (bút và vở)', 'Quan hệ từ'],
                 ['của (sách của em)', 'Quan hệ từ'],
                 ['mỗi (mỗi người)', 'Lượng từ']],
                'Những, mỗi là lượng từ; và, của là quan hệ từ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với từ loại của từ in đậm.',
                [['<b>Các</b> bạn đến rồi.', 'Lượng từ'],
                 ['Tôi <b>và</b> bạn đi học.', 'Quan hệ từ'],
                 ['Quyển sách <b>của</b> tôi.', 'Quan hệ từ'],
                 ['<b>Mỗi</b> người một việc.', 'Lượng từ']],
                'Nhận diện lượng từ và quan hệ từ trong câu cụ thể.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm LƯỢNG TỪ hoặc QUAN HỆ TỪ.',
                [['những', 'Lượng từ'], ['mỗi', 'Lượng từ'],
                 ['và', 'Quan hệ từ'], ['nhưng', 'Quan hệ từ']],
                'Những, mỗi là lượng từ; và, nhưng là quan hệ từ.', 'de');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm LƯỢNG TỪ CHỈ SỐ NHIỀU hoặc SỐ ÍT.',
                [['những', 'Chỉ số nhiều'], ['các', 'Chỉ số nhiều'],
                 ['mỗi', 'Chỉ số ít'], ['một', 'Chỉ số ít']],
                'Những, các chỉ số nhiều; mỗi, một chỉ từng cái riêng lẻ.', 'de');
            $this->sortQ($L, 'Kéo mỗi quan hệ từ vào nhóm NỐI LIỀN hoặc TƯƠNG PHẢN.',
                [['và', 'Nối liền'], ['với', 'Nối liền'],
                 ['nhưng', 'Tương phản'], ['mà', 'Tương phản']],
                'Và, với nối liền; nhưng, mà biểu thị tương phản.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ LOẠI ĐÚNG hoặc SAI trong câu "Sách của em rất hay".',
                [['của – quan hệ từ', 'Từ loại đúng'], ['rất – từ chỉ mức độ', 'Từ loại đúng'],
                 ['hay – tính từ', 'Từ loại đúng'], ['của – lượng từ', 'Sai']],
                'Của là quan hệ từ (sở hữu); rất chỉ mức độ; hay là tính từ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Từ chỉ lượng ít nhiều của sự vật gọi là lượng ___.', [[0, 'từ']],
                'Ví dụ: những, các, mỗi, một.', 'de');
            $this->fill($L, 'Từ nối các từ ngữ, biểu thị quan hệ gọi là quan hệ ___.', [[0, 'từ']],
                'Ví dụ: và, với, nhưng, của.', 'de');
            $this->fill($L, 'Trong "những bông hoa", từ "những" là lượng ___.', [[0, 'từ']],
                'Những chỉ số nhiều.', 'trung_binh');
            $this->fill($L, 'Trong "bút và vở", từ "và" là quan hệ ___.', [[0, 'từ']],
                'Và nối hai danh từ bút – vở.', 'trung_binh');
        }
    }

    // ================= NGỮ VĂN: BIỆN PHÁP TU TỪ =================

    private function seedNvBienPhapTuTu(): void
    {
        $this->seedNvBienPhapTuTu71();
        $this->seedNvBienPhapTuTu72();
        $this->seedNvBienPhapTuTu81();
        $this->seedNvBienPhapTuTu82();
    }

    private function seedNvBienPhapTuTu71(): void
    {
        $L = 'nv-bien-phap-tu-tu-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phép so sánh là gì?',
                ['Đối chiếu sự vật này với sự vật khác có nét tương đồng', 'Gán đặc điểm người cho vật', 'Gọi vật này bằng tên vật khác', 'Lặp lại từ ngữ'], 0,
                'So sánh đối chiếu hai sự vật có nét tương đồng để làm nổi bật hình ảnh.', 'de');
            $this->quiz($L, 'Trong câu "Trẻ em như búp trên cành", từ so sánh là từ nào?',
                ['như', 'trẻ em', 'búp', 'trên cành'], 0,
                'Từ so sánh thường gặp: như, tựa, bằng, là.', 'de');
            $this->quiz($L, 'Câu nào sau đây sử dụng phép so sánh?',
                ['Mặt trời như quả cầu lửa khổng lồ', 'Mặt trời mọc ở đằng đông', 'Hôm nay trời nắng', 'Em đi học sớm'], 0,
                'Câu 1 so sánh mặt trời với quả cầu lửa.', 'de');
            $this->quiz($L, 'Tác dụng chính của phép so sánh là gì?',
                ['Làm hình ảnh cụ thể, sinh động, gợi cảm', 'Làm câu văn khó hiểu', 'Kéo dài câu văn', 'Thay thế từ ngữ'], 0,
                'So sánh giúp hình ảnh trở nên cụ thể, sinh động và gợi cảm hơn.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu với vế so sánh của nó.',
                [['Trẻ em như búp trên cành', 'Trẻ em – búp trên cành'],
                 ['Cô ấy đẹp như hoa', 'Cô ấy – hoa'],
                 ['Nhanh như chớp', '(Ai đó) – chớp'],
                 ['Trắng như tuyết', '(Vật gì đó) – tuyết']],
                'Mô hình so sánh: A như B.', 'de');
            $this->matching($L, 'Nối mỗi từ với vai trò trong phép so sánh.',
                [['như, tựa', 'Từ so sánh'],
                 ['hơn, kém', 'So sánh không ngang bằng'],
                 ['bằng', 'So sánh ngang bằng'],
                 ['là', 'Có thể là từ so sánh']],
                'Từ so sánh: như, tựa, bằng, là; hơn/kém chỉ mức độ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với kiểu so sánh của nó.',
                [['Cao như núi', 'So sánh ngang bằng'],
                 ['Đẹp hơn hoa', 'So sánh không ngang bằng (hơn)'],
                 ['Chậm như sên', 'So sánh ngang bằng'],
                 ['Kém xa anh ấy', 'So sánh không ngang bằng (kém)']],
                'So sánh ngang bằng: như, tựa; không ngang bằng: hơn, kém.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hình ảnh so sánh với ý nghĩa gợi ra.',
                [['Trẻ em như búp trên cành', 'Non nớt, đáng yêu'],
                 ['Lòng mẹ như biển cả', 'Bao la, sâu nặng'],
                 ['Nhanh như chớp', 'Rất nhanh'],
                 ['Cứng như đá', 'Rất cứng rắn']],
                'So sánh làm nổi bật đặc điểm của sự vật được so sánh.', 'de');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ PHÉP SO SÁNH hoặc KHÔNG.',
                [['Trăng như chiếc thuyền nan', 'Có phép so sánh'], ['Mẹ hiền như cô tiên', 'Có phép so sánh'],
                 ['Trăng lên cao.', 'Không'], ['Em yêu mẹ.', 'Không']],
                'Câu có từ so sánh (như) và hai vế đối chiếu là câu so sánh.', 'de');
            $this->sortQ($L, 'Kéo mỗi từ vào nhóm TỪ SO SÁNH hoặc KHÔNG PHẢI.',
                [['như', 'Từ so sánh'], ['tựa', 'Từ so sánh'],
                 ['và', 'Không phải'], ['những', 'Không phải']],
                'Từ so sánh: như, tựa, bằng, là.', 'de');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm SO SÁNH NGANG BẰNG hoặc KHÔNG NGANG BẰNG.',
                [['Đẹp như tranh', 'So sánh ngang bằng'], ['Trắng như bông', 'So sánh ngang bằng'],
                 ['Cao hơn núi', 'Không ngang bằng'], ['Nhanh hơn gió', 'Không ngang bằng']],
                'Như, tựa: ngang bằng; hơn, kém: không ngang bằng.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm SO SÁNH ĐÚNG hoặc KHÔNG PHẢI SO SÁNH.',
                [['Mắt sáng như sao', 'So sánh đúng'], ['Tóc đen như gỗ mun', 'So sánh đúng'],
                 ['Em ăn cơm.', 'Không phải so sánh'], ['Trời hôm nay đẹp.', 'Không phải so sánh']],
                'So sánh cần hai sự vật đối chiếu qua từ so sánh.', 'de');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phép tu từ đối chiếu hai sự vật có nét tương đồng gọi là so ___.', [[0, 'sánh']],
                'Ví dụ: Trẻ em như búp trên cành.', 'de');
            $this->fill($L, 'Trong "Trẻ em như búp trên cành", từ so sánh là từ "___".', [[0, 'như']],
                'Các từ so sánh: như, tựa, bằng, là.', 'de');
            $this->fill($L, 'Mô hình của phép so sánh là: A ___ B.', [[0, 'như']],
                'A là sự vật được so sánh, B là sự vật dùng để so sánh.', 'de');
            $this->fill($L, 'So sánh làm hình ảnh cụ thể, sinh động và gợi ___.', [[0, 'cảm']],
                'Đó là tác dụng chính của phép so sánh.', 'de');
        }
    }

    private function seedNvBienPhapTuTu72(): void
    {
        $L = 'nv-bien-phap-tu-tu-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Phép nhân hoá là gì?',
                ['Gán đặc điểm của con người cho sự vật', 'So sánh hai sự vật', 'Gọi vật này bằng tên vật khác', 'Đảo trật tự từ'], 0,
                'Nhân hoá gán hành động, tình cảm, cách xưng hô của người cho vật.', 'de');
            $this->quiz($L, 'Câu nào sau đây sử dụng phép nhân hoá?',
                ['Chị gió mơn man mái tóc em', 'Gió thổi mạnh hôm nay', 'Trời hôm nay có gió', 'Em thích gió mát'], 0,
                'Gọi gió là "chị", gán hành động "mơn man" của người cho gió.', 'de');
            $this->quiz($L, 'Cách nhân hoá nào sau đây là đúng?',
                ['Dùng từ chỉ người để gọi vật', 'Dùng từ chỉ vật để gọi người', 'So sánh vật với vật', 'Liệt kê sự vật'], 0,
                'Nhân hoá: dùng từ ngữ vốn chỉ người để gọi, tả vật.', 'de');
            $this->quiz($L, 'Tác dụng của phép nhân hoá là gì?',
                ['Làm sự vật sinh động, gần gũi như con người', 'Làm câu văn khô khan', 'Che giấu ý nghĩa', 'Rút ngắn câu văn'], 0,
                'Nhân hoá khiến sự vật trở nên có hồn, gần gũi với con người.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu nhân hoá với cách nhân hoá được dùng.',
                [['Chị gió mơn man', 'Dùng từ xưng hô của người'],
                 ['Bác mặt trời cười', 'Gán hành động của người'],
                 ['Hoa thì thầm với bướm', 'Gán lời nói của người'],
                 ['Cây bàng buồn rụng lá', 'Gán tình cảm của người']],
                'Nhân hoá bằng xưng hô, hành động, lời nói, tình cảm của con người.', 'de');
            $this->matching($L, 'Nối mỗi sự vật được nhân hoá với đặc điểm người được gán.',
                [['Ông mặt trời', 'Xưng hô "ông", hành động như người'],
                 ['Chị mây', 'Xưng hô "chị", bay lượn'],
                 ['Bác nông dân (cây lúa gọi)', 'Trò chuyện như người'],
                 ['Em bé (gió đùa)', 'Chơi đùa như trẻ nhỏ']],
                'Sự vật được gọi bằng từ xưng hô thân mật của con người.', 'de');
            $this->matching($L, 'Nối mỗi câu với biện pháp tu từ của nó.',
                [['Mặt trời như quả cầu lửa', 'So sánh'],
                 ['Ông mặt trời thức dậy', 'Nhân hoá'],
                 ['Trăng tròn như chiếc đĩa', 'So sánh'],
                 ['Chị trăng tâm sự cùng em', 'Nhân hoá']],
                'Phân biệt so sánh (như) và nhân hoá (gán đặc điểm người).', 'trung_binh');
            $this->matching($L, 'Nối mỗi chi tiết với tác dụng của nhân hoá.',
                [['Gió "mơn man"', 'Gợi cảm giác dịu dàng'],
                 ['Mưa "khóc"', 'Gợi nỗi buồn'],
                 ['Nắng "cười"', 'Gợi niềm vui'],
                 ['Sóng "vỗ về"', 'Gợi sự âu yếm']],
                'Nhân hoá truyền tình cảm của con người vào sự vật.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ NHÂN HOÁ hoặc KHÔNG.',
                [['Bác mặt trời mỉm cười', 'Có nhân hoá'], ['Chị ong chăm chỉ làm việc', 'Có nhân hoá'],
                 ['Mặt trời mọc lúc 6 giờ.', 'Không'], ['Ong bay đi hút mật.', 'Không']],
                'Nhân hoá: gọi vật bằng từ chỉ người, gán hành động người.', 'de');
            $this->sortQ($L, 'Kéo mỗi cách làm vào nhóm LÀ NHÂN HOÁ hoặc KHÔNG PHẢI.',
                [['Gọi gió là "chị gió"', 'Là nhân hoá'], ['Cho hoa biết "cười"', 'Là nhân hoá'],
                 ['So sánh hoa với nắng', 'Không phải'], ['Tả màu sắc của hoa', 'Không phải']],
                'Nhân hoá khác so sánh và tả thực.', 'de');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm NHÂN HOÁ BẰNG XƯNG HÔ hoặc BẰNG HÀNH ĐỘNG.',
                [['Ông trăng tròn vành vạnh', 'Nhân hoá bằng xưng hô'], ['Chị gió bay qua', 'Nhân hoá bằng xưng hô'],
                 ['Mây đùa giỡn trên trời', 'Nhân hoá bằng hành động'], ['Sóng vỗ về bờ cát', 'Nhân hoá bằng hành động']],
                'Xưng hô: ông, chị, bác; hành động: đùa giỡn, vỗ về.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm NHÂN HOÁ hoặc SO SÁNH.',
                [['Nắng vàng ươm như mật ong', 'So sánh'], ['Nắng nhảy nhót trên sân', 'Nhân hoá'],
                 ['Mây trắng như bông', 'So sánh'], ['Mây thủ thỉ trò chuyện', 'Nhân hoá']],
                'So sánh dùng "như"; nhân hoá gán hành động người.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phép tu từ gán đặc điểm con người cho sự vật gọi là nhân ___.', [[0, 'hoá']],
                'Ví dụ: Chị gió mơn man mái tóc em.', 'de');
            $this->fill($L, 'Trong "ông mặt trời", từ "ông" là cách xưng hô của con ___.', [[0, 'người']],
                'Dùng từ chỉ người để gọi vật là nhân hoá.', 'de');
            $this->fill($L, 'Nhân hoá làm sự vật trở nên sinh động và gần ___.', [[0, 'gũi']],
                'Sự vật như có hồn, có tình cảm.', 'de');
            $this->fill($L, '"Hoa thì thầm với bướm" là nhân hoá bằng cách gán ___ nói cho hoa.', [[0, 'lời']],
                'Thì thầm là hành động nói của con người.', 'de');
        }
    }

    private function seedNvBienPhapTuTu81(): void
    {
        $L = 'nv-bien-phap-tu-tu-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Ẩn dụ là gì?',
                ['Gọi tên sự vật này bằng tên sự vật khác có nét tương đồng, lược bỏ từ so sánh', 'So sánh có dùng từ "như"', 'Gán đặc điểm người cho vật', 'Lặp lại từ ngữ'], 0,
                'Ẩn dụ giống so sánh nhưng không dùng từ so sánh: "Thuyền về có nhớ bến chăng".', 'trung_binh');
            $this->quiz($L, 'Câu nào sau đây sử dụng ẩn dụ?',
                ['Thuyền về có nhớ bến chăng', 'Thuyền như chiếc lá', 'Thuyền chạy nhanh', 'Thuyền chở nhiều hàng'], 0,
                '"Thuyền – bến" ẩn dụ cho người đi – người ở lại, không dùng từ "như".', 'trung_binh');
            $this->quiz($L, '"Người cha mái tóc bạc / Đốt lửa cho anh nằm" – "người cha" ẩn dụ cho ai?',
                ['Bác Hồ', 'Người cha ruột', 'Người lính già', 'Người dân'], 0,
                'Trong bài "Đêm nay Bác không ngủ", "người cha" ẩn dụ chỉ Bác Hồ.', 'trung_binh');
            $this->quiz($L, 'Điểm khác nhau cơ bản giữa ẩn dụ và so sánh là gì?',
                ['Ẩn dụ lược bỏ từ so sánh', 'Ẩn dụ dài hơn so sánh', 'Ẩn dụ không có hình ảnh', 'Ẩn dụ dễ hiểu hơn'], 0,
                'So sánh: A như B; ẩn dụ: gọi thẳng A bằng tên B.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu ẩn dụ với kiểu ẩn dụ của nó.',
                [['Ăn quả nhớ kẻ trồng cây ("ăn quả")', 'Ẩn dụ cách thức'],
                 ['Thuyền về có nhớ bến chăng', 'Ẩn dụ hình thức'],
                 ['Người cha mái tóc bạc', 'Ẩn dụ phẩm chất'],
                 ['Giọng nói ngọt ngào', 'Ẩn dụ chuyển đổi cảm giác']],
                'Bốn kiểu ẩn dụ: hình thức, cách thức, phẩm chất, chuyển đổi cảm giác.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hình ảnh ẩn dụ với ý nghĩa của nó.',
                [['Thuyền – bến', 'Người đi – người ở'],
                 ['Người cha', 'Bác Hồ kính yêu'],
                 ['Mặt trời (trong "Mặt trời trong lăng")', 'Bác Hồ'],
                 ['Trăng', 'Vẻ đẹp, người bạn tri âm']],
                'Ẩn dụ giàu sức gợi, cần đặt trong văn cảnh để hiểu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với biện pháp tu từ của nó.',
                [['Ngày ngày mặt trời đi qua trên lăng', 'Ẩn dụ'],
                 ['Mặt trời như quả cầu lửa', 'So sánh'],
                 ['Bác mặt trời mỉm cười', 'Nhân hoá'],
                 ['Một cây làm chẳng nên non', 'Ẩn dụ (cây – người)']],
                'Phân biệt ẩn dụ với so sánh và nhân hoá.', 'trung_binh');
            $this->matching($L, 'Nối mỗi kiểu ẩn dụ với ví dụ.',
                [['Ẩn dụ hình thức', 'Thuyền – bến (hình ảnh cụ thể)'],
                 ['Ẩn dụ phẩm chất', 'Người cha (phẩm chất cao quý)'],
                 ['Ẩn dụ cách thức', 'Ăn quả nhớ kẻ trồng cây'],
                 ['Ẩn dụ chuyển đổi cảm giác', 'Tiếng suối trong như tiếng hát xa']],
                'Bốn kiểu ẩn dụ dựa trên nét tương đồng khác nhau.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ẨN DỤ hoặc SO SÁNH.',
                [['Thuyền về có nhớ bến chăng', 'Ẩn dụ'], ['Uống nước nhớ nguồn', 'Ẩn dụ'],
                 ['Trẻ em như búp trên cành', 'So sánh'], ['Đẹp như hoa', 'So sánh']],
                'Ẩn dụ không dùng từ so sánh; so sánh có từ "như".', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm ẨN DỤ hoặc NHÂN HOÁ.',
                [['Ngày ngày mặt trời đi qua trên lăng', 'Ẩn dụ'], ['Người cha mái tóc bạc', 'Ẩn dụ'],
                 ['Ông mặt trời thức dậy', 'Nhân hoá'], ['Chị gió mơn man', 'Nhân hoá']],
                'Ẩn dụ gọi vật này bằng tên vật khác; nhân hoá gán đặc điểm người.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hình ảnh vào nhóm ẨN DỤ HÌNH THỨC hoặc ẨN DỤ PHẨM CHẤT.',
                [['Thuyền – bến', 'Ẩn dụ hình thức'], ['Mây – tóc', 'Ẩn dụ hình thức'],
                 ['Người cha (chỉ Bác Hồ)', 'Ẩn dụ phẩm chất'], ['Ngọn đuốc (chỉ niềm tin)', 'Ẩn dụ phẩm chất']],
                'Hình thức: tương đồng hình dáng; phẩm chất: tương đồng tính chất.', 'kho');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ ẨN DỤ hoặc KHÔNG.',
                [['Về thăm quê Bác, làng Sen', 'Không (nói trực tiếp)'], ['Ăn quả nhớ kẻ trồng cây', 'Có ẩn dụ'],
                 ['Trời hôm nay nắng đẹp.', 'Không'], ['Một cây làm chẳng nên non', 'Có ẩn dụ']],
                'Ẩn dụ gửi gắm ý nghĩa sâu xa qua hình ảnh cụ thể.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phép tu từ gọi vật này bằng tên vật khác, lược bỏ từ so sánh gọi là ẩn ___.', [[0, 'dụ']],
                'Ví dụ: Thuyền về có nhớ bến chăng.', 'trung_binh');
            $this->fill($L, 'Ẩn dụ khác so sánh ở chỗ không dùng từ so ___.', [[0, 'sánh']],
                'So sánh: A như B; ẩn dụ: gọi A bằng tên B.', 'trung_binh');
            $this->fill($L, '"Người cha mái tóc bạc" là ẩn dụ chỉ Bác ___.', [[0, 'Hồ']],
                'Ẩn dụ phẩm chất: tình yêu thương như người cha.', 'trung_binh');
            $this->fill($L, 'Có bốn kiểu ẩn dụ: hình thức, cách thức, phẩm chất và chuyển đổi cảm ___.', [[0, 'giác']],
                'Ví dụ chuyển đổi cảm giác: "giọng nói ngọt ngào".', 'kho');
        }
    }

    private function seedNvBienPhapTuTu82(): void
    {
        $L = 'nv-bien-phap-tu-tu-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hoán dụ là gì?',
                ['Gọi tên sự vật bằng tên sự vật khác có quan hệ gần gũi', 'So sánh hai sự vật tương đồng', 'Gán đặc điểm người cho vật', 'Nói quá sự thật'], 0,
                'Hoán dụ dựa trên quan hệ gần gũi: "bàn tay" chỉ người lao động.', 'trung_binh');
            $this->quiz($L, 'Trong câu "Bàn tay ta làm nên tất cả", "bàn tay" hoán dụ cho gì?',
                ['Người lao động', 'Cái bàn', 'Đôi tay cụ thể', 'Công cụ'], 0,
                'Lấy bộ phận (bàn tay) chỉ toàn thể (người lao động).', 'trung_binh');
            $this->quiz($L, 'Câu nào sau đây sử dụng hoán dụ?',
                ['Áo nâu liền với áo xanh nông thôn', 'Áo nâu đẹp quá', 'Tôi mua áo nâu', 'Áo nâu bị rách'], 0,
                '"Áo nâu" chỉ người nông dân, "áo xanh" chỉ bộ đội – lấy dấu hiệu chỉ sự vật.', 'trung_binh');
            $this->quiz($L, 'Điểm khác nhau giữa hoán dụ và ẩn dụ là gì?',
                ['Hoán dụ dựa trên quan hệ gần gũi, ẩn dụ dựa trên nét tương đồng', 'Hoán dụ dài hơn ẩn dụ', 'Hoán dụ không có hình ảnh', 'Hai phép giống hệt nhau'], 0,
                'Ẩn dụ: tương đồng (thuyền – người đi); hoán dụ: gần gũi (bàn tay – người).', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi câu hoán dụ với kiểu hoán dụ của nó.',
                [['Bàn tay ta làm nên tất cả', 'Lấy bộ phận chỉ toàn thể'],
                 ['Nhà vẫn còn nghèo', 'Lấy vật chứa chỉ vật bị chứa'],
                 ['Áo nâu liền với áo xanh', 'Lấy dấu hiệu chỉ sự vật'],
                 ['Một cây làm chẳng nên non', 'Lấy cái cụ thể chỉ cái trừu tượng']],
                'Bốn kiểu hoán dụ thường gặp trong chương trình.', 'trung_binh');
            $this->matching($L, 'Nối mỗi hình ảnh hoán dụ với ý nghĩa của nó.',
                [['Bàn tay', 'Người lao động'],
                 ['Áo nâu', 'Người nông dân'],
                 ['Áo xanh', 'Bộ đội'],
                 ['Mái nhà', 'Gia đình']],
                'Hoán dụ quen thuộc trong ca dao, thơ ca.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với biện pháp tu từ của nó.',
                [['Bàn tay ta làm nên tất cả', 'Hoán dụ'],
                 ['Thuyền về có nhớ bến chăng', 'Ẩn dụ'],
                 ['Trẻ em như búp trên cành', 'So sánh'],
                 ['Chị gió mơn man', 'Nhân hoá']],
                'Bốn biện pháp tu từ: so sánh, nhân hoá, ẩn dụ, hoán dụ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cặp với cơ sở của phép tu từ.',
                [['Thuyền – người đi', 'Tương đồng → ẩn dụ'],
                 ['Bàn tay – người lao động', 'Gần gũi → hoán dụ'],
                 ['Trẻ em – búp', 'Tương đồng → so sánh'],
                 ['Gió – chị', 'Gán người → nhân hoá']],
                'Tương đồng sinh ẩn dụ/so sánh; gần gũi sinh hoán dụ.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm HOÁN DỤ hoặc ẨN DỤ.',
                [['Bàn tay ta làm nên tất cả', 'Hoán dụ'], ['Áo nâu liền với áo xanh', 'Hoán dụ'],
                 ['Thuyền về có nhớ bến chăng', 'Ẩn dụ'], ['Người cha mái tóc bạc', 'Ẩn dụ']],
                'Hoán dụ: gần gũi; ẩn dụ: tương đồng.', 'kho');
            $this->sortQ($L, 'Kéo mỗi hình ảnh vào nhóm LẤY BỘ PHẬN CHỈ TOÀN THỂ hoặc LẤY DẤU HIỆU CHỈ SỰ VẬT.',
                [['Bàn tay (chỉ người lao động)', 'Lấy bộ phận chỉ toàn thể'], ['Đầu (chỉ người, "đầu người")', 'Lấy bộ phận chỉ toàn thể'],
                 ['Áo nâu (chỉ nông dân)', 'Lấy dấu hiệu chỉ sự vật'], ['Mũ cối (chỉ bộ đội)', 'Lấy dấu hiệu chỉ sự vật']],
                'Bộ phận → toàn thể; dấu hiệu (trang phục) → con người.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ HOÁN DỤ hoặc KHÔNG.',
                [['Nhớ ai như nhớ thuốc lào', 'Không (so sánh)'], ['Sen tàn, cúc lại nở hoa', 'Không (tả thực)'],
                 ['Vì lợi ích mười năm trồng cây', 'Có hoán dụ ("trồng cây" chỉ giáo dục)'], ['Bàn tay ta làm nên tất cả', 'Có hoán dụ']],
                '"Trồng cây" hoán dụ chỉ sự nghiệp giáo dục con người.', 'kho');
            $this->sortQ($L, 'Kéo mỗi quan hệ vào nhóm SINH HOÁN DỤ hoặc SINH ẨN DỤ.',
                [['Bộ phận – toàn thể', 'Sinh hoán dụ'], ['Vật chứa – vật bị chứa', 'Sinh hoán dụ'],
                 ['Tương đồng hình thức', 'Sinh ẩn dụ'], ['Tương đồng phẩm chất', 'Sinh ẩn dụ']],
                'Gần gũi → hoán dụ; tương đồng → ẩn dụ.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Phép tu từ gọi vật này bằng tên vật khác có quan hệ gần gũi gọi là hoán ___.', [[0, 'dụ']],
                'Ví dụ: Bàn tay ta làm nên tất cả.', 'trung_binh');
            $this->fill($L, '"Bàn tay" chỉ người lao động là lấy bộ phận chỉ toàn ___.', [[0, 'thể']],
                'Một kiểu hoán dụ thường gặp.', 'trung_binh');
            $this->fill($L, '"Áo nâu" chỉ người nông dân là lấy dấu hiệu chỉ sự ___.', [[0, 'vật']],
                'Áo nâu là trang phục đặc trưng của nông dân xưa.', 'trung_binh');
            $this->fill($L, 'Hoán dụ dựa trên quan hệ gần gũi, ẩn dụ dựa trên nét tương ___.', [[0, 'đồng']],
                'Đây là điểm khác nhau cơ bản của hai phép.', 'kho');
        }
    }

    // ================= NGỮ VĂN: VĂN MIÊU TẢ =================

    private function seedNvVanMieuTa(): void
    {
        $this->seedNvVanMieuTa81();
        $this->seedNvVanMieuTa82();
        $this->seedNvVanMieuTa91();
        $this->seedNvVanMieuTa92();
    }

    private function seedNvVanMieuTa81(): void
    {
        $L = 'nv-van-mieu-ta-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Bài văn miêu tả gồm mấy phần?',
                ['3 phần: mở bài, thân bài, kết bài', '2 phần: mở bài, kết bài', '4 phần', '1 phần duy nhất'], 0,
                'Bố cục 3 phần: mở bài giới thiệu, thân bài miêu tả chi tiết, kết bài nêu cảm nghĩ.', 'trung_binh');
            $this->quiz($L, 'Muốn tả hay, trước hết phải làm gì?',
                ['Quan sát kĩ bằng nhiều giác quan', 'Đoán mò', 'Chép văn mẫu', 'Viết thật dài'], 0,
                'Quan sát kĩ bằng mắt, tai, mũi... là cơ sở để miêu tả chân thực, sinh động.', 'trung_binh');
            $this->quiz($L, 'Trình tự nào sau đây hợp lí khi tả cảnh?',
                ['Từ bao quát đến chi tiết', 'Từ chi tiết đến bao quát rồi lại chi tiết', 'Ngẫu nhiên, không cần trình tự', 'Chỉ tả một chi tiết'], 0,
                'Thường tả từ bao quát toàn cảnh rồi đi vào từng chi tiết cụ thể.', 'trung_binh');
            $this->quiz($L, 'Phần kết bài của văn miêu tả thường làm gì?',
                ['Nêu cảm nghĩ, tình cảm với đối tượng', 'Giới thiệu đối tượng', 'Liệt kê chi tiết', 'Kể một câu chuyện khác'], 0,
                'Kết bài bộc lộ tình cảm, ấn tượng sâu sắc về đối tượng được tả.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi phần của bài văn với nhiệm vụ của nó.',
                [['Mở bài', 'Giới thiệu đối tượng miêu tả'],
                 ['Thân bài', 'Miêu tả chi tiết theo trình tự'],
                 ['Kết bài', 'Nêu cảm nghĩ, tình cảm'],
                 ['Cả bài', 'Dùng từ ngữ gợi hình, gợi cảm']],
                'Mở – thân – kết là bố cục chuẩn của văn miêu tả.', 'trung_binh');
            $this->matching($L, 'Nối mỗi giác quan với điều quan sát được khi tả cảnh.',
                [['Mắt', 'Màu sắc, hình dáng'],
                 ['Tai', 'Âm thanh'],
                 ['Mũi', 'Mùi hương'],
                 ['Da', 'Cảm giác nóng, lạnh, mịn, ráp']],
                'Quan sát bằng nhiều giác quan giúp bài văn sinh động.', 'trung_binh');
            $this->matching($L, 'Nối mỗi trình tự với cách tả phù hợp.',
                [['Không gian', 'Từ xa đến gần, từ ngoài vào trong'],
                 ['Thời gian', 'Từ sáng đến tối, từ xuân sang đông'],
                 ['Bao quát – chi tiết', 'Toàn cảnh rồi từng bộ phận'],
                 ['Cảm xúc', 'Theo dòng cảm nghĩ của người viết']],
                'Chọn trình tự hợp lí giúp bài văn mạch lạc.', 'trung_binh');
            $this->matching($L, 'Nối mỗi biện pháp tu từ với tác dụng trong văn miêu tả.',
                [['So sánh', 'Làm hình ảnh cụ thể, sinh động'],
                 ['Nhân hoá', 'Làm sự vật có hồn'],
                 ['Từ láy', 'Gợi hình, gợi cảm'],
                 ['Điệp ngữ', 'Nhấn mạnh, tạo nhịp điệu']],
                'Dùng biện pháp tu từ giúp bài văn miêu tả hay hơn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm MỞ BÀI hoặc THÂN BÀI (văn tả cảnh).',
                [['Quê hương em có dòng sông rất đẹp.', 'Mở bài'], ['Mỗi lần về quê, em đều ra bờ sông chơi.', 'Mở bài'],
                 ['Nước sông trong vắt, soi bóng hàng tre.', 'Thân bài'], ['Buổi chiều, mặt sông lấp lánh nắng vàng.', 'Thân bài']],
                'Mở bài giới thiệu; thân bài miêu tả chi tiết.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm QUAN SÁT BẰNG MẮT hoặc BẰNG TAI.',
                [['Hoa phượng đỏ rực', 'Quan sát bằng mắt'], ['Mặt hồ phẳng lặng', 'Quan sát bằng mắt'],
                 ['Tiếng chim hót líu lo', 'Quan sát bằng tai'], ['Tiếng suối róc rách', 'Quan sát bằng tai']],
                'Mắt thấy hình ảnh, tai nghe âm thanh.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cách viết vào nhóm NÊN LÀM hoặc NÊN TRÁNH khi tả.',
                [['Quan sát kĩ trước khi viết', 'Nên làm'], ['Dùng từ ngữ gợi hình', 'Nên làm'],
                 ['Liệt kê chung chung', 'Nên tránh'], ['Chép nguyên văn mẫu', 'Nên tránh']],
                'Nên quan sát kĩ, dùng từ gợi hình; tránh liệt kê, chép mẫu.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi trình tự vào nhóm TẢ CẢNH hoặc TẢ NGƯỜI phù hợp.',
                [['Từ xa đến gần', 'Tả cảnh'], ['Theo thời gian trong ngày', 'Tả cảnh'],
                 ['Từ chân dung đến hoạt động', 'Tả người'], ['Từ ngoại hình đến tính cách', 'Tả người']],
                'Tả cảnh theo không gian, thời gian; tả người từ ngoại hình đến hoạt động.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Bài văn miêu tả gồm ba phần: mở bài, thân bài và ___ bài.', [[0, 'kết']],
                'Kết bài nêu cảm nghĩ về đối tượng.', 'trung_binh');
            $this->fill($L, 'Muốn tả hay phải quan sát ___ bằng nhiều giác quan.', [[0, 'kĩ']],
                'Quan sát kĩ giúp miêu tả chân thực, sinh động.', 'trung_binh');
            $this->fill($L, 'Trình tự tả cảnh thường từ bao quát đến chi ___.', [[0, 'tiết']],
                'Hoặc theo trình tự không gian, thời gian.', 'trung_binh');
            $this->fill($L, 'Dùng so sánh, nhân hoá giúp bài văn miêu tả thêm sinh ___.', [[0, 'động']],
                'Biện pháp tu từ làm hình ảnh gợi cảm hơn.', 'trung_binh');
        }
    }

    private function seedNvVanMieuTa82(): void
    {
        $L = 'nv-van-mieu-ta-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Dấu phẩy có tác dụng gì?',
                ['Ngăn cách các bộ phận trong câu', 'Kết thúc câu', 'Biểu thị câu hỏi', 'Trích dẫn lời nói'], 0,
                'Dấu phẩy ngăn cách các thành phần câu, các vế câu.', 'trung_binh');
            $this->quiz($L, 'Dấu hai chấm thường dùng để làm gì?',
                ['Báo hiệu phần giải thích, liệt kê hoặc lời dẫn', 'Kết thúc đoạn văn', 'Ngăn cách từ ngữ', 'Biểu thị cảm xúc'], 0,
                'Dấu hai chấm báo hiệu lời giải thích, liệt kê hoặc dẫn lời nói.', 'trung_binh');
            $this->quiz($L, 'Câu nào dùng dấu câu đúng?',
                ['Hà Nội, Hải Phòng, Đà Nẵng là thành phố lớn.', 'Hà Nội Hải Phòng Đà Nẵng là thành phố lớn?', 'Hà Nội! Hải Phòng! Đà Nẵng!', 'Hà Nội: Hải Phòng: Đà Nẵng.'], 0,
                'Liệt kê các thành phần cùng chức năng phải ngăn cách bằng dấu phẩy.', 'trung_binh');
            $this->quiz($L, 'Dấu ngoặc kép dùng để làm gì?',
                ['Đánh dấu lời nói trực tiếp hoặc từ ngữ trích dẫn', 'Kết thúc câu kể', 'Ngăn cách vế câu', 'Biểu thị sự ngạc nhiên'], 0,
                'Dấu ngoặc kép đánh dấu lời nói trực tiếp, từ ngữ được trích dẫn.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi dấu câu với công dụng của nó.',
                [['Dấu chấm (.)', 'Kết thúc câu kể'],
                 ['Dấu phẩy (,)', 'Ngăn cách các bộ phận'],
                 ['Dấu chấm hỏi (?)', 'Kết thúc câu hỏi'],
                 ['Dấu chấm than (!)', 'Kết thúc câu cảm thán, cầu khiến']],
                'Mỗi dấu câu có công dụng riêng, dùng sai gây hiểu lầm.', 'trung_binh');
            $this->matching($L, 'Nối mỗi dấu câu với ví dụ sử dụng đúng.',
                [['Dấu hai chấm', 'Cô giáo nói: "Các em trật tự."'],
                 ['Dấu ngoặc kép', '"Học, học nữa, học mãi."'],
                 ['Dấu phẩy', 'Mùa xuân, cây cối đâm chồi.'],
                 ['Dấu chấm than', 'Đẹp quá!']],
                'Hai chấm dẫn lời nói; ngoặc kép đánh dấu trích dẫn.', 'trung_binh');
            $this->matching($L, 'Nối mỗi lỗi dùng dấu câu với cách sửa.',
                [['Thiếu dấu phẩy khi liệt kê', 'Thêm dấu phẩy giữa các thành phần'],
                 ['Dùng dấu chấm giữa chừng câu', 'Nối lại thành một câu hoàn chỉnh'],
                 ['Quên dấu hỏi cuối câu hỏi', 'Thêm dấu chấm hỏi'],
                 ['Dùng bừa dấu chấm than', 'Chỉ dùng khi cảm thán thật sự']],
                'Dùng đúng dấu câu giúp câu văn rõ nghĩa.', 'trung_binh');
            $this->matching($L, 'Nối mỗi câu với dấu câu còn thiếu.',
                [['Mẹ mua rau___ thịt___ cá.', 'Dấu phẩy'],
                 ['Bạn có khoẻ không___', 'Dấu chấm hỏi'],
                 ['Ôi___ đẹp quá___', 'Dấu phẩy và dấu chấm than'],
                 ['Em bé nói___ "Con yêu mẹ."', 'Dấu hai chấm']],
                'Điền dấu câu phù hợp với từng loại câu.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi dấu vào nhóm DÙNG CUỐI CÂU hoặc DÙNG GIỮA CÂU.',
                [['Dấu chấm', 'Dùng cuối câu'], ['Dấu chấm hỏi', 'Dùng cuối câu'],
                 ['Dấu phẩy', 'Dùng giữa câu'], ['Dấu hai chấm', 'Dùng giữa câu']],
                'Chấm, hỏi, than đặt cuối câu; phẩy, hai chấm đặt giữa câu.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm DÙNG DẤU CÂU ĐÚNG hoặc SAI.',
                [['Em thích đọc sách, xem phim.', 'Dùng dấu câu đúng'], ['"Cố lên!" – bạn ấy động viên.', 'Dùng dấu câu đúng'],
                 ['Em thích đọc sách xem phim', 'Sai'], ['Bạn có khỏe không.', 'Sai']],
                'Liệt kê cần dấu phẩy; câu hỏi phải kết thúc bằng dấu hỏi.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi trường hợp vào nhóm NÊN DÙNG DẤU HAI CHẤM hoặc KHÔNG.',
                [['Dẫn lời nói trực tiếp', 'Nên dùng dấu hai chấm'], ['Liệt kê các ví dụ', 'Nên dùng dấu hai chấm'],
                 ['Kết thúc câu kể', 'Không'], ['Ngăn cách chủ ngữ – vị ngữ', 'Không']],
                'Hai chấm báo hiệu lời dẫn, giải thích, liệt kê.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU KỂ hoặc CÂU CẢM.',
                [['Hôm nay trời đẹp.', 'Câu kể'], ['Em đi học đúng giờ.', 'Câu kể'],
                 ['Đẹp quá!', 'Câu cảm'], ['Vui quá đi!', 'Câu cảm']],
                'Câu kể kết thúc bằng dấu chấm; câu cảm bằng dấu chấm than.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dấu ___ dùng để ngăn cách các bộ phận trong câu.', [[0, 'phẩy']],
                'Ví dụ: Mùa xuân, cây cối đâm chồi nảy lộc.', 'trung_binh');
            $this->fill($L, 'Dấu hai chấm báo hiệu phần giải thích hoặc lời ___.', [[0, 'dẫn']],
                'Ví dụ: Bạn ấy nói: "Mình sẽ cố gắng."', 'trung_binh');
            $this->fill($L, 'Cuối câu hỏi phải đặt dấu chấm ___.', [[0, 'hỏi']],
                'Ví dụ: Bạn có khoẻ không?', 'trung_binh');
            $this->fill($L, 'Dấu ___ kép đánh dấu lời nói trực tiếp.', [[0, 'ngoặc']],
                'Ví dụ: "Học, học nữa, học mãi."', 'trung_binh');
        }
    }

    private function seedNvVanMieuTa91(): void
    {
        $L = 'nv-van-mieu-ta-lop-9-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi tả người, cần làm nổi bật những gì?',
                ['Chân dung và hoạt động, việc làm tiêu biểu', 'Chỉ tả quần áo', 'Chỉ kể tên tuổi', 'Tả thật dài là được'], 0,
                'Tả người cần chân dung (hình dáng, khuôn mặt) và hoạt động tiêu biểu.', 'trung_binh');
            $this->quiz($L, 'Chi tiết nào sau đây là chi tiết tiêu biểu khi tả mẹ?',
                ['Đôi bàn tay chai sần vì tần tảo', 'Mẹ cao 1m60', 'Mẹ thích màu xanh', 'Mẹ sinh năm 1980'], 0,
                'Chi tiết tiêu biểu gợi được tính cách, cuộc đời nhân vật.', 'trung_binh');
            $this->quiz($L, 'Trong văn tả người, ngoài tả còn cần kết hợp với gì?',
                ['Kể chuyện và bộc lộ tình cảm', 'Chỉ tả, không kể', 'Chỉ nêu số liệu', 'Chép lại tiểu sử'], 0,
                'Kết hợp kể việc làm và bộc lộ tình cảm giúp nhân vật sống động.', 'kho');
            $this->quiz($L, 'Lỗi nào cần tránh khi tả người?',
                ['Liệt kê chung chung, không có chi tiết tiêu biểu', 'Dùng biện pháp tu từ', 'Bộc lộ tình cảm', 'Tả theo trình tự'], 0,
                'Liệt kê chung chung khiến bài văn nhạt nhoà, thiếu ấn tượng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nội dung với phần tương ứng trong bài văn tả người.',
                [['Giới thiệu người được tả', 'Mở bài'],
                 ['Tả chân dung, hoạt động', 'Thân bài'],
                 ['Nêu tình cảm, suy nghĩ', 'Kết bài'],
                 ['Chọn chi tiết tiêu biểu', 'Xuyên suốt bài']],
                'Bố cục 3 phần, chi tiết tiêu biểu xuyên suốt.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đối tượng với cách tả phù hợp.',
                [['Tả chân dung', 'Hình dáng, khuôn mặt, trang phục'],
                 ['Tả hoạt động', 'Việc làm, cử chỉ hằng ngày'],
                 ['Tả tính cách', 'Qua việc làm, lời nói'],
                 ['Tả trong kỉ niệm', 'Gắn với câu chuyện đáng nhớ']],
                'Tả người toàn diện: ngoại hình, hoạt động, tính cách.', 'trung_binh');
            $this->matching($L, 'Nối mỗi chi tiết với tác dụng của nó.',
                [['Đôi mắt hiền từ', 'Gợi tính cách nhân hậu'],
                 ['Bàn tay chai sần', 'Gợi sự tần tảo'],
                 ['Nụ cười tươi', 'Gợi sự vui vẻ, lạc quan'],
                 ['Dáng đi nhanh nhẹn', 'Gợi sự năng động']],
                'Chi tiết ngoại hình gợi ra tính cách bên trong.', 'kho');
            $this->matching($L, 'Nối mỗi câu văn với nhận xét.',
                [['Mẹ có đôi bàn tay chai sần.', 'Chi tiết tiêu biểu, gợi cảm'],
                 ['Mẹ là người phụ nữ.', 'Chung chung, nhạt nhoà'],
                 ['Bà cười hiền, mắt nheo lại.', 'Cụ thể, sinh động'],
                 ['Ông ấy rất tốt.', 'Chung chung, cần chi tiết']],
                'Chi tiết cụ thể, gợi cảm làm bài văn hay hơn lời chung chung.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm TIÊU BIỂU hoặc CHUNG CHUNG.',
                [['Đôi bàn tay gầy guộc, chai sần', 'Tiêu biểu'], ['Ánh mắt ấm áp mỗi khi nhìn con', 'Tiêu biểu'],
                 ['Là một con người', 'Chung chung'], ['Sống ở thành phố', 'Chung chung']],
                'Chi tiết tiêu biểu cụ thể, gợi hình, gợi cảm.', 'kho');
            $this->sortQ($L, 'Kéo mỗi nội dung vào nhóm NÊN CÓ hoặc NÊN TRÁNH trong bài tả người.',
                [['Chi tiết tiêu biểu', 'Nên có'], ['Tình cảm chân thành', 'Nên có'],
                 ['Liệt kê dài dòng', 'Nên tránh'], ['Bịa đặt sự việc', 'Nên tránh']],
                'Nên có chi tiết tiêu biểu và tình cảm chân thành.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cách viết vào nhóm TẢ CHÂN DUNG hoặc TẢ HOẠT ĐỘNG.',
                [['Khuôn mặt phúc hậu, tóc bạc', 'Tả chân dung'], ['Dáng người gầy, cao', 'Tả chân dung'],
                 ['Ngày ngày ra đồng từ sớm', 'Tả hoạt động'], ['Tối nào cũng kể chuyện cho cháu', 'Tả hoạt động']],
                'Chân dung: ngoại hình; hoạt động: việc làm.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đoạn văn vào nhóm MỞ BÀI hoặc KẾT BÀI (tả mẹ).',
                [['Mẹ là người em yêu quý nhất.', 'Mở bài'], ['Trong gia đình, mẹ là người gần gũi với em nhất.', 'Mở bài'],
                 ['Em mong mẹ luôn khoẻ mạnh.', 'Kết bài'], ['Em tự hứa sẽ chăm ngoan để mẹ vui.', 'Kết bài']],
                'Mở bài giới thiệu; kết bài bộc lộ tình cảm, ước mong.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tả người cần làm nổi bật chân dung và ___ động tiêu biểu.', [[0, 'hoạt']],
                'Hoạt động thể hiện tính cách con người.', 'trung_binh');
            $this->fill($L, 'Chi tiết gợi được tính cách, số phận nhân vật gọi là chi tiết tiêu ___.', [[0, 'biểu']],
                'Ví dụ: đôi bàn tay chai sần của mẹ.', 'kho');
            $this->fill($L, 'Văn tả người cần kết hợp tả, kể và bộc lộ tình ___.', [[0, 'cảm']],
                'Tình cảm chân thành làm bài văn sâu sắc.', 'kho');
            $this->fill($L, 'Tránh liệt kê chung ___, thiếu chi tiết cụ thể.', [[0, 'chung']],
                'Chung chung khiến bài văn nhạt nhoà.', 'trung_binh');
        }
    }

    private function seedNvVanMieuTa92(): void
    {
        $L = 'nv-van-mieu-ta-lop-9-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Khi tả cảnh, trình tự nào sau đây là hợp lí?',
                ['Từ bao quát đến chi tiết, hoặc theo không gian, thời gian', 'Tả lộn xộn, không cần trình tự', 'Chỉ tả một chi tiết duy nhất', 'Tả từ chi tiết nhỏ nhất trước'], 0,
                'Tả cảnh cần trình tự hợp lí: bao quát → chi tiết, hoặc theo không gian/thời gian.', 'kho');
            $this->quiz($L, 'Khi tả con vật, cần chú ý những gì?',
                ['Hình dáng, đặc điểm nổi bật và ích lợi', 'Chỉ tả màu sắc', 'Chỉ tả nơi ở', 'Không cần quan sát'], 0,
                'Tả con vật: hình dáng, màu sắc, đặc điểm nổi bật, ích lợi hoặc kỉ niệm.', 'trung_binh');
            $this->quiz($L, 'Khi tả đồ vật, nên tả theo trình tự nào?',
                ['Từ bao quát đến từng bộ phận, nêu công dụng', 'Chỉ tả giá tiền', 'Chỉ tả nơi mua', 'Tả ngẫu nhiên'], 0,
                'Tả đồ vật: hình dáng chung → từng bộ phận → công dụng, kỉ niệm.', 'trung_binh');
            $this->quiz($L, 'Yếu tố nào làm bài văn tả cảnh thêm sâu sắc?',
                ['Lồng cảm xúc, suy nghĩ của người viết', 'Chỉ liệt kê sự vật', 'Viết thật dài', 'Dùng nhiều số liệu'], 0,
                'Cảm xúc của người viết làm cảnh vật có hồn, bài văn sâu sắc.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đối tượng với trọng tâm khi tả.',
                [['Tả cảnh buổi sáng', 'Không gian, ánh sáng, âm thanh'],
                 ['Tả con vật', 'Hình dáng, đặc điểm, ích lợi'],
                 ['Tả đồ vật', 'Hình dáng, bộ phận, công dụng'],
                 ['Tả cây cối', 'Hình dáng, sự thay đổi theo mùa']],
                'Mỗi đối tượng có trọng tâm miêu tả riêng.', 'trung_binh');
            $this->matching($L, 'Nối mỗi trình tự với đối tượng phù hợp.',
                [['Từ xa đến gần', 'Tả cảnh'],
                 ['Từ bao quát đến bộ phận', 'Tả đồ vật, con vật'],
                 ['Theo thời gian', 'Tả cảnh trong ngày, cây theo mùa'],
                 ['Theo cảm xúc', 'Bài văn trữ tình']],
                'Chọn trình tự phù hợp với đối tượng miêu tả.', 'kho');
            $this->matching($L, 'Nối mỗi biện pháp với ví dụ trong văn tả cảnh.',
                [['So sánh', 'Mặt hồ như tấm gương khổng lồ'],
                 ['Nhân hoá', 'Hàng tre thì thầm trong gió'],
                 ['Từ láy', 'Lấp lánh, róc rách, xào xạc'],
                 ['Điệp ngữ', 'Đẹp quá! Đẹp đến nao lòng!']],
                'Biện pháp tu từ làm cảnh vật sinh động.', 'kho');
            $this->matching($L, 'Nối mỗi câu văn với nhận xét.',
                [['Nắng vàng ươm trải khắp cánh đồng.', 'Gợi hình, gợi cảm'],
                 ['Cánh đồng rộng.', 'Đơn điệu, chung chung'],
                 ['Gió mơn man, lúa rì rào như sóng.', 'Sinh động, có nhạc điệu'],
                 ['Có cánh đồng.', 'Quá sơ sài']],
                'Câu văn hay dùng từ ngữ gợi hình, gợi cảm.', 'kho');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi chi tiết vào nhóm TẢ CẢNH hoặc TẢ ĐỒ VẬT.',
                [['Mặt trời nhô lên, nắng vàng rực rỡ', 'Tả cảnh'], ['Tiếng chim hót líu lo', 'Tả cảnh'],
                 ['Chiếc cặp màu xanh, có nhiều ngăn', 'Tả đồ vật'], ['Cây bút máy nắp vàng óng', 'Tả đồ vật']],
                'Tả cảnh: thiên nhiên; tả đồ vật: hình dáng, bộ phận.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cách viết vào nhóm NÊN LÀM hoặc NÊN TRÁNH khi tả cảnh.',
                [['Lồng cảm xúc vào cảnh', 'Nên làm'], ['Quan sát bằng nhiều giác quan', 'Nên làm'],
                 ['Chỉ liệt kê tên sự vật', 'Nên tránh'], ['Tả một cách khô khan', 'Nên tránh']],
                'Tả cảnh hay cần cảm xúc và quan sát tinh tế.', 'kho');
            $this->sortQ($L, 'Kéo mỗi nội dung vào nhóm TẢ CON VẬT hoặc TẢ CÂY CỐI.',
                [['Bộ lông vàng óng, đôi tai vểnh', 'Tả con vật'], ['Chú chó trung thành, nhanh nhẹn', 'Tả con vật'],
                 ['Cây phượng già, hoa đỏ rực', 'Tả cây cối'], ['Tán lá xum xuê che mát sân trường', 'Tả cây cối']],
                'Tả con vật: hình dáng, tính nết; tả cây: hình dáng, sự thay đổi.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ CẢM XÚC hoặc KHÔ KHAN.',
                [['Quê hương trong em đẹp đến nao lòng.', 'Có cảm xúc'], ['Em yêu biết mấy dòng sông quê!', 'Có cảm xúc'],
                 ['Sông dài 10 km.', 'Khô khan'], ['Cây cao 5 mét.', 'Khô khan']],
                'Cảm xúc làm bài văn có hồn; số liệu khô khan thuộc văn thuyết minh.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Tả cảnh thường theo trình tự từ bao quát đến chi ___.', [[0, 'tiết']],
                'Hoặc theo không gian, thời gian.', 'kho');
            $this->fill($L, 'Tả đồ vật cần nêu hình dáng, các bộ phận và công ___.', [[0, 'dụng']],
                'Kèm theo kỉ niệm với đồ vật nếu có.', 'trung_binh');
            $this->fill($L, 'Lồng ___ xúc của người viết làm bài văn thêm sâu sắc.', [[0, 'cảm']],
                'Cảnh vật qua cảm xúc trở nên có hồn.', 'kho');
            $this->fill($L, 'Từ láy như "lấp lánh", "róc rách" có tác dụng gợi hình, gợi ___.', [[0, 'cảm']],
                'Từ láy rất đắc dụng trong văn miêu tả.', 'kho');
        }
    }

    // ================= ĐỊA LÝ: CHÂU LỤC – ĐẠI DƯƠNG =================

    private function seedDlChauLucDaiDuong(): void
    {
        $this->seedDlChauLucDaiDuong61();
        $this->seedDlChauLucDaiDuong62();
        $this->seedDlChauLucDaiDuong71();
        $this->seedDlChauLucDaiDuong72();
    }

    private function seedDlChauLucDaiDuong61(): void
    {
        $L = 'dl-chau-luc-dai-duong-lop-6-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Châu Á nằm chủ yếu ở bán cầu nào?',
                ['Bán cầu Bắc', 'Bán cầu Nam', 'Cả hai bán cầu bằng nhau', 'Không thuộc bán cầu nào'], 0,
                'Châu Á nằm chủ yếu ở bán cầu Bắc và bán cầu Đông.', 'de');
            $this->quiz($L, 'Dãy núi nào có đỉnh Ê-vơ-rét cao nhất thế giới?',
                ['Hi-ma-lay-a', 'An-đét', 'An-pơ', 'Trường Sơn'], 0,
                'Dãy Hi-ma-lay-a ở châu Á có đỉnh Ê-vơ-rét cao 8 848 m.', 'de');
            $this->quiz($L, 'Châu Á đông dân nhất tập trung ở khu vực nào?',
                ['Đông Á và Nam Á', 'Bắc Á', 'Tây Á', 'Trung Á'], 0,
                'Dân cư châu Á tập trung đông ở Đông Á (Trung Quốc) và Nam Á (Ấn Độ).', 'de');
            $this->quiz($L, 'Hoang mạc lớn ở châu Á là hoang mạc nào?',
                ['Gô-bi', 'Xa-ha-ra', 'Ca-la-ha-ri', 'A-ta-ca-ma'], 0,
                'Hoang mạc Gô-bi ở Trung Á; Xa-ha-ra ở châu Phi.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi địa danh châu Á với đặc điểm của nó.',
                [['Dãy Hi-ma-lay-a', 'Có đỉnh Ê-vơ-rét cao nhất thế giới'],
                 ['Hoang mạc Gô-bi', 'Hoang mạc lớn ở Trung Á'],
                 ['Đồng bằng Hoa Bắc', 'Đồng bằng đông dân ở Trung Quốc'],
                 ['Bán đảo Ả Rập', 'Bán đảo lớn ở Tây Á']],
                'Địa hình châu Á đa dạng: núi cao, hoang mạc, đồng bằng, bán đảo.', 'de');
            $this->matching($L, 'Nối mỗi con sông châu Á với đặc điểm của nó.',
                [['Sông Trường Giang', 'Sông dài nhất châu Á'],
                 ['Sông Mê Kông', 'Chảy qua nhiều nước Đông Nam Á'],
                 ['Sông Hằng', 'Sông thiêng của Ấn Độ'],
                 ['Sông Hoàng Hà', 'Sông lớn của Trung Quốc']],
                'Châu Á có nhiều sông lớn: Trường Giang, Mê Kông, Hằng, Hoàng Hà.', 'de');
            $this->matching($L, 'Nối mỗi quốc gia với khu vực của nó ở châu Á.',
                [['Việt Nam', 'Đông Nam Á'],
                 ['Nhật Bản', 'Đông Á'],
                 ['Ấn Độ', 'Nam Á'],
                 ['Ả Rập Xê Út', 'Tây Á']],
                'Châu Á chia thành các tiểu vùng: Đông Á, Nam Á, Tây Á, Trung Á, Đông Nam Á.', 'de');
            $this->matching($L, 'Nối mỗi đặc điểm với châu lục tương ứng.',
                [['Diện tích lớn nhất', 'châu Á'],
                 ['Có hoang mạc Xa-ha-ra', 'châu Phi'],
                 ['Kinh tế phát triển cao', 'châu Âu'],
                 ['Lạnh nhất, nhiều băng tuyết', 'châu Nam Cực']],
                'Mỗi châu lục có nét nổi bật riêng.', 'de');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi quốc gia vào nhóm THUỘC CHÂU Á hoặc KHÔNG THUỘC CHÂU Á.',
                [['Việt Nam', 'Thuộc châu Á'], ['Ấn Độ', 'Thuộc châu Á'],
                 ['Pháp', 'Không thuộc châu Á'], ['Bra-xin', 'Không thuộc châu Á']],
                'Việt Nam, Ấn Độ ở châu Á; Pháp ở châu Âu; Bra-xin ở châu Mỹ.', 'de');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm Ở CHÂU Á hoặc KHÔNG Ở CHÂU Á.',
                [['Dãy Hi-ma-lay-a', 'Ở châu Á'], ['Hoang mạc Gô-bi', 'Ở châu Á'],
                 ['Dãy An-pơ', 'Không ở châu Á'], ['Hoang mạc Xa-ha-ra', 'Không ở châu Á']],
                'Hi-ma-lay-a, Gô-bi ở châu Á; An-pơ ở châu Âu; Xa-ha-ra ở châu Phi.', 'de');
            $this->sortQ($L, 'Kéo mỗi khu vực vào nhóm ĐÔNG DÂN hoặc THƯA DÂN ở châu Á.',
                [['Đồng bằng Hoa Bắc', 'Đông dân'], ['Đồng bằng sông Hằng', 'Đông dân'],
                 ['Hoang mạc Gô-bi', 'Thưa dân'], ['Vùng Xi-bê-ri', 'Thưa dân']],
                'Đồng bằng đông dân; hoang mạc, vùng lạnh giá thưa dân.', 'de');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm ĐỊA HÌNH hoặc KHÍ HẬU của châu Á.',
                [['Nhiều núi cao, sơn nguyên', 'Địa hình'], ['Có hoang mạc rộng lớn', 'Địa hình'],
                 ['Gió mùa thịnh hành', 'Khí hậu'], ['Phân hoá đa dạng', 'Khí hậu']],
                'Địa hình: núi cao, hoang mạc; khí hậu: gió mùa, phân hoá đa dạng.', 'de');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Châu lục có diện tích lớn nhất thế giới là châu ___.', [[0, 'Á']],
                'Châu Á chiếm khoảng 1/3 diện tích đất liền.', 'de');
            $this->fill($L, 'Đỉnh núi cao nhất thế giới Ê-vơ-rét thuộc dãy Hi-ma-lay-___.', [[0, 'a']],
                'Dãy Hi-ma-lay-a nằm ở Nam Á.', 'de');
            $this->fill($L, 'Hoang mạc lớn ở Trung Á là hoang mạc Gô-___.', [[0, 'bi']],
                'Gô-bi là hoang mạc lớn thứ hai châu Á.', 'de');
            $this->fill($L, 'Việt Nam thuộc tiểu vùng Đông Nam ___.', [[0, 'Á']],
                'Đông Nam Á gồm 11 quốc gia.', 'de');
        }
    }

    private function seedDlChauLucDaiDuong62(): void
    {
        $L = 'dl-chau-luc-dai-duong-lop-6-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Châu lục nào nhỏ nhất thế giới?',
                ['châu Đại Dương', 'châu Âu', 'châu Nam Cực', 'châu Phi'], 0,
                'Châu Đại Dương là châu lục nhỏ nhất, gồm Úc và các đảo Thái Bình Dương.', 'de');
            $this->quiz($L, 'Châu lục nào lạnh nhất, quanh năm bao phủ băng tuyết?',
                ['châu Nam Cực', 'châu Âu', 'châu Á', 'châu Mỹ'], 0,
                'Châu Nam Cực nằm quanh cực Nam, lạnh nhất Trái Đất.', 'de');
            $this->quiz($L, 'Hoang mạc lớn nhất thế giới Xa-ha-ra nằm ở châu lục nào?',
                ['châu Phi', 'châu Á', 'châu Mỹ', 'châu Đại Dương'], 0,
                'Xa-ha-ra ở Bắc Phi là hoang mạc nóng lớn nhất thế giới.', 'de');
            $this->quiz($L, 'Dãy núi dài nhất thế giới An-đét nằm ở châu lục nào?',
                ['châu Nam Mỹ', 'châu Bắc Mỹ', 'châu Âu', 'châu Á'], 0,
                'Dãy An-đét chạy dọc bờ tây Nam Mỹ, dài nhất thế giới.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi châu lục với đặc điểm nổi bật của nó.',
                [['châu Âu', 'Kinh tế phát triển, nhiều nước công nghiệp'],
                 ['châu Phi', 'Hoang mạc Xa-ha-ra, sông Nin'],
                 ['châu Bắc Mỹ', 'Có Hoa Kì, Canada'],
                 ['châu Nam Mỹ', 'Rừng A-ma-dôn, dãy An-đét']],
                'Mỗi châu lục có nét đặc trưng riêng về tự nhiên và kinh tế.', 'de');
            $this->matching($L, 'Nối mỗi địa danh với châu lục của nó.',
                [['Sông Nin', 'châu Phi'],
                 ['Rừng A-ma-dôn', 'châu Nam Mỹ'],
                 ['Dãy An-pơ', 'châu Âu'],
                 ['Lục địa Úc', 'châu Đại Dương']],
                'Sông Nin ở châu Phi; A-ma-dôn ở Nam Mỹ; An-pơ ở châu Âu; Úc ở Đại Dương.', 'de');
            $this->matching($L, 'Nối mỗi đại dương với vị trí tương đối của nó.',
                [['Thái Bình Dương', 'Giữa châu Á – châu Mỹ'],
                 ['Đại Tây Dương', 'Giữa châu Mỹ – châu Âu, châu Phi'],
                 ['Ấn Độ Dương', 'Phía nam châu Á'],
                 ['Bắc Băng Dương', 'Quanh Bắc Cực']],
                'Bốn đại dương bao quanh các châu lục.', 'de');
            $this->matching($L, 'Nối mỗi châu lục với số lượng quốc gia tương đối.',
                [['châu Phi', 'Nhiều nước nhất (hơn 50 nước)'],
                 ['châu Á', 'Rất nhiều nước, đông dân nhất'],
                 ['châu Đại Dương', 'Ít nước nhất'],
                 ['châu Nam Cực', 'Không có quốc gia thường trú']],
                'Châu Phi có nhiều quốc gia nhất; Nam Cực không có dân cư thường trú.', 'de');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi châu lục vào nhóm BÁN CẦU BẮC hoặc BÁN CẦU NAM (chủ yếu).',
                [['châu Âu', 'Bán cầu Bắc'], ['châu Bắc Mỹ', 'Bán cầu Bắc'],
                 ['châu Nam Cực', 'Bán cầu Nam'], ['châu Đại Dương', 'Bán cầu Nam']],
                'Châu Âu, Bắc Mỹ chủ yếu ở bán cầu Bắc; Nam Cực, Đại Dương ở bán cầu Nam.', 'de');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm CHÂU PHI hoặc KHÔNG PHẢI CHÂU PHI.',
                [['Hoang mạc Xa-ha-ra', 'châu Phi'], ['Sông Nin', 'châu Phi'],
                 ['Dãy Hi-ma-lay-a', 'Không phải châu Phi'], ['Rừng A-ma-dôn', 'Không phải châu Phi']],
                'Xa-ha-ra, sông Nin ở châu Phi; Hi-ma-lay-a ở châu Á; A-ma-dôn ở Nam Mỹ.', 'de');
            $this->sortQ($L, 'Kéo mỗi châu lục vào nhóm CÓ DÂN CƯ ĐÔNG hoặc THƯA VẮNG.',
                [['châu Á', 'Dân cư đông'], ['châu Âu', 'Dân cư đông'],
                 ['châu Nam Cực', 'Thưa vắng'], ['châu Đại Dương', 'Thưa vắng']],
                'Châu Á đông dân nhất; Nam Cực hầu như không có dân cư thường trú.', 'de');
            $this->sortQ($L, 'Kéo mỗi đại dương vào nhóm LỚN NHẤT/NHỎ NHẤT hoặc CÒN LẠI.',
                [['Thái Bình Dương', 'Lớn nhất'], ['Bắc Băng Dương', 'Nhỏ nhất'],
                 ['Đại Tây Dương', 'Còn lại'], ['Ấn Độ Dương', 'Còn lại']],
                'Thái Bình Dương lớn nhất; Bắc Băng Dương nhỏ nhất.', 'de');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Châu lục nhỏ nhất thế giới là châu Đại ___.', [[0, 'Dương']],
                'Gồm lục địa Úc và các đảo Thái Bình Dương.', 'de');
            $this->fill($L, 'Châu lục lạnh nhất là châu Nam ___.', [[0, 'Cực']],
                'Quanh năm bao phủ bởi băng tuyết.', 'de');
            $this->fill($L, 'Hoang mạc nóng lớn nhất thế giới Xa-ha-ra nằm ở châu ___.', [[0, 'Phi']],
                'Xa-ha-ra chiếm phần lớn Bắc Phi.', 'de');
            $this->fill($L, 'Dãy núi dài nhất thế giới An-đét nằm ở châu Nam ___.', [[0, 'Mỹ']],
                'Dãy An-đét chạy dọc bờ tây Nam Mỹ.', 'de');
        }
    }

    private function seedDlChauLucDaiDuong71(): void
    {
        $L = 'dl-chau-luc-dai-duong-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Địa hình châu Âu chủ yếu là gì?',
                ['Đồng bằng và núi thấp', 'Núi cao hiểm trở', 'Hoang mạc rộng lớn', 'Cao nguyên đá'], 0,
                'Châu Âu chủ yếu là đồng bằng và núi thấp, thuận lợi cho sinh sống.', 'de');
            $this->quiz($L, 'Khí hậu châu Âu có nét nổi bật nào?',
                ['Ôn hoà nhờ dòng biển nóng', 'Nóng bức quanh năm', 'Lạnh giá quanh năm', 'Khô hạn kéo dài'], 0,
                'Dòng biển nóng Bắc Đại Tây Dương làm khí hậu Tây Âu ôn hoà, ấm áp.', 'de');
            $this->quiz($L, 'Dãy núi cao, trẻ ở Nam Âu là dãy nào?',
                ['An-pơ', 'Hi-ma-lay-a', 'An-đét', 'U-ran'], 0,
                'Dãy An-pơ ở Nam Âu là dãy núi trẻ, cao, có nhiều đỉnh tuyết phủ.', 'de');
            $this->quiz($L, 'Liên minh châu Âu (EU) là tổ chức như thế nào?',
                ['Liên kết kinh tế – chính trị của nhiều nước châu Âu', 'Một quốc gia duy nhất', 'Tổ chức quân sự', 'Câu lạc bộ thể thao'], 0,
                'EU là liên minh kinh tế – chính trị gồm nhiều nước châu Âu.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi địa danh châu Âu với đặc điểm của nó.',
                [['Dãy An-pơ', 'Dãy núi trẻ, cao ở Nam Âu'],
                 ['Đồng bằng Đông Âu', 'Đồng bằng rộng lớn'],
                 ['Sông Đa-nuýp', 'Sông dài, chảy qua nhiều nước'],
                 ['Bán đảo I-bê-rich', 'Nơi có Tây Ban Nha, Bồ Đào Nha']],
                'Địa hình châu Âu: núi trẻ ở nam, đồng bằng rộng ở đông.', 'de');
            $this->matching($L, 'Nối mỗi nước với thủ đô của nó.',
                [['Pháp', 'Paris'],
                 ['Đức', 'Berlin'],
                 ['Ý', 'Rome'],
                 ['Tây Ban Nha', 'Madrid']],
                'Paris, Berlin, Rome, Madrid là thủ đô các nước Tây – Nam Âu.', 'de');
            $this->matching($L, 'Nối mỗi ngành kinh tế với đặc điểm ở châu Âu.',
                [['Công nghiệp', 'Phát triển cao, hiện đại'],
                 ['Dịch vụ', 'Chiếm tỉ trọng lớn'],
                 ['Nông nghiệp', 'Thâm canh, năng suất cao'],
                 ['Du lịch', 'Rất phát triển nhờ cảnh quan, di tích']],
                'Châu Âu có nền kinh tế phát triển cao, dịch vụ chiếm ưu thế.', 'trung_binh');
            $this->matching($L, 'Nối mỗi kiểu khí hậu với khu vực ở châu Âu.',
                [['Ôn đới hải dương', 'Tây Âu'],
                 ['Ôn đới lục địa', 'Đông Âu'],
                 ['Địa Trung Hải', 'Nam Âu'],
                 ['Hàn đới', 'Bắc Âu']],
                'Khí hậu châu Âu phân hoá từ tây sang đông, từ nam lên bắc.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi quốc gia vào nhóm THUỘC CHÂU ÂU hoặc KHÔNG.',
                [['Pháp', 'Thuộc châu Âu'], ['Đức', 'Thuộc châu Âu'],
                 ['Nhật Bản', 'Không'], ['Ai Cập', 'Không']],
                'Pháp, Đức ở châu Âu; Nhật Bản ở châu Á; Ai Cập ở châu Phi.', 'de');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm Ở CHÂU ÂU hoặc KHÔNG.',
                [['Dãy An-pơ', 'Ở châu Âu'], ['Sông Đa-nuýp', 'Ở châu Âu'],
                 ['Sông Nin', 'Không'], ['Dãy An-đét', 'Không']],
                'An-pơ, Đa-nuýp ở châu Âu; Nin ở châu Phi; An-đét ở Nam Mỹ.', 'de');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm TỰ NHIÊN hoặc KINH TẾ – XÃ HỘI của châu Âu.',
                [['Nhiều đồng bằng, núi thấp', 'Tự nhiên'], ['Khí hậu ôn hoà', 'Tự nhiên'],
                 ['Công nghiệp hiện đại', 'Kinh tế – xã hội'], ['Dân số già', 'Kinh tế – xã hội']],
                'Tự nhiên: đồng bằng, khí hậu ôn hoà; kinh tế – xã hội: công nghiệp, dân số già.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nước vào nhóm TÂY ÂU hoặc ĐÔNG ÂU.',
                [['Pháp', 'Tây Âu'], ['Đức', 'Tây Âu'],
                 ['Ba Lan', 'Đông Âu'], ['U-crai-na', 'Đông Âu']],
                'Pháp, Đức ở Tây Âu; Ba Lan, U-crai-na ở Đông Âu.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Dãy núi trẻ, cao ở Nam Âu là dãy An-___.', [[0, 'pơ']],
                'An-pơ có nhiều đỉnh tuyết phủ quanh năm.', 'de');
            $this->fill($L, 'Khí hậu Tây Âu ôn hoà nhờ dòng biển ___.', [[0, 'nóng']],
                'Dòng biển nóng Bắc Đại Tây Dương.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Pháp là ___.', [[0, 'Paris']],
                'Paris nổi tiếng với tháp Eiffel.', 'de');
            $this->fill($L, 'Liên minh châu Âu viết tắt là ___.', [[0, 'EU']],
                'EU là liên minh kinh tế – chính trị của các nước châu Âu.', 'trung_binh');
        }
    }

    private function seedDlChauLucDaiDuong72(): void
    {
        $L = 'dl-chau-luc-dai-duong-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Con sông dài nhất thế giới là sông nào, ở châu lục nào?',
                ['Sông Nin – châu Phi', 'Sông A-ma-dôn – châu Mỹ', 'Sông Trường Giang – châu Á', 'Sông Đa-nuýp – châu Âu'], 0,
                'Sông Nin ở châu Phi dài khoảng 6 650 km, là sông dài nhất thế giới.', 'trung_binh');
            $this->quiz($L, 'Rừng mưa nhiệt đới lớn nhất thế giới nằm ở đâu?',
                ['Lưu vực sông A-ma-dôn, Nam Mỹ', 'Trung Phi', 'Đông Nam Á', 'Trung Mỹ'], 0,
                'Rừng A-ma-dôn ở Nam Mỹ được mệnh danh là "lá phổi xanh" của Trái Đất.', 'trung_binh');
            $this->quiz($L, 'Kênh đào Pa-na-ma nối hai đại dương nào?',
                ['Thái Bình Dương và Đại Tây Dương', 'Ấn Độ Dương và Thái Bình Dương', 'Bắc Băng Dương và Đại Tây Dương', 'Ấn Độ Dương và Đại Tây Dương'], 0,
                'Kênh Pa-na-ma nối Thái Bình Dương với Đại Tây Dương, rút ngắn đường biển.', 'trung_binh');
            $this->quiz($L, 'Nước nào có nền kinh tế phát triển nhất châu Mỹ?',
                ['Hoa Kì', 'Bra-xin', 'Mê-hi-cô', 'Argentina'], 0,
                'Hoa Kì là nền kinh tế lớn nhất châu Mỹ và hàng đầu thế giới.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi địa danh châu Phi với đặc điểm của nó.',
                [['Hoang mạc Xa-ha-ra', 'Hoang mạc nóng lớn nhất thế giới'],
                 ['Sông Nin', 'Sông dài nhất thế giới'],
                 ['Bồn địa Công-gô', 'Rừng rậm nhiệt đới lớn thứ hai'],
                 ['Núi Ki-li-man-gia-rô', 'Núi cao nhất châu Phi']],
                'Châu Phi: Xa-ha-ra, sông Nin, rừng Công-gô, núi Ki-li-man-gia-rô.', 'trung_binh');
            $this->matching($L, 'Nối mỗi địa danh châu Mỹ với đặc điểm của nó.',
                [['Dãy An-đét', 'Dãy núi dài nhất thế giới'],
                 ['Rừng A-ma-dôn', 'Rừng mưa lớn nhất thế giới'],
                 ['Đồng bằng La Pla-ta', 'Đồng bằng màu mỡ ở Nam Mỹ'],
                 ['Hồ Ngũ Đại', 'Cụm hồ lớn ở Bắc Mỹ']],
                'Châu Mỹ: An-đét, A-ma-dôn, La Pla-ta, Ngũ Đại Hồ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước với khu vực ở châu Mỹ.',
                [['Hoa Kì', 'Bắc Mỹ'],
                 ['Bra-xin', 'Nam Mỹ'],
                 ['Mê-hi-cô', 'Bắc Mỹ (Trung Mỹ)'],
                 ['Argentina', 'Nam Mỹ']],
                'Bắc Mỹ: Hoa Kì, Canada, Mê-hi-cô; Nam Mỹ: Bra-xin, Argentina...', 'de');
            $this->matching($L, 'Nối mỗi đặc điểm dân cư với châu lục.',
                [['Da đen, nhiều bộ tộc', 'châu Phi'],
                 ['Đa chủng tộc do nhập cư', 'châu Mỹ'],
                 ['Dân số già', 'châu Âu'],
                 ['Đông dân nhất', 'châu Á']],
                'Mỗi châu lục có đặc điểm dân cư riêng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm CHÂU PHI hoặc CHÂU MỸ.',
                [['Hoang mạc Xa-ha-ra', 'châu Phi'], ['Sông Nin', 'châu Phi'],
                 ['Dãy An-đét', 'châu Mỹ'], ['Rừng A-ma-dôn', 'châu Mỹ']],
                'Xa-ha-ra, Nin ở châu Phi; An-đét, A-ma-dôn ở châu Mỹ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi nước vào nhóm BẮC MỸ hoặc NAM MỸ.',
                [['Hoa Kì', 'Bắc Mỹ'], ['Canada', 'Bắc Mỹ'],
                 ['Bra-xin', 'Nam Mỹ'], ['Argentina', 'Nam Mỹ']],
                'Bắc Mỹ: Hoa Kì, Canada; Nam Mỹ: Bra-xin, Argentina.', 'de');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm BẮC MỸ hoặc NAM MỸ.',
                [['Kinh tế phát triển cao', 'Bắc Mỹ'], ['Công nghiệp hiện đại', 'Bắc Mỹ'],
                 ['Rừng A-ma-dôn rộng lớn', 'Nam Mỹ'], ['Nhiều nước đang phát triển', 'Nam Mỹ']],
                'Bắc Mỹ phát triển cao; Nam Mỹ có rừng A-ma-dôn, nhiều nước đang phát triển.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi con sông vào nhóm CHÂU PHI hoặc CHÂU MỸ.',
                [['Sông Nin', 'châu Phi'], ['Sông Ni-giê', 'châu Phi'],
                 ['Sông A-ma-dôn', 'châu Mỹ'], ['Sông Mi-xi-xi-pi', 'châu Mỹ']],
                'Nin, Ni-giê ở châu Phi; A-ma-dôn, Mi-xi-xi-pi ở châu Mỹ.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Sông dài nhất thế giới là sông ___, ở châu Phi.', [[0, 'Nin']],
                'Sông Nin dài khoảng 6 650 km.', 'trung_binh');
            $this->fill($L, 'Rừng mưa lớn nhất thế giới là rừng A-ma-___, ở Nam Mỹ.', [[0, 'dôn']],
                'Được mệnh danh là "lá phổi xanh" của Trái Đất.', 'trung_binh');
            $this->fill($L, 'Dãy núi dài nhất thế giới là dãy An-___.', [[0, 'đét']],
                'Dãy An-đét chạy dọc bờ tây Nam Mỹ.', 'trung_binh');
            $this->fill($L, 'Kênh đào Pa-na-ma nối Thái Bình Dương với Đại Tây ___.', [[0, 'Dương']],
                'Kênh đào rút ngắn đường biển giữa hai đại dương.', 'trung_binh');
        }
    }

    // ================= ĐỊA LÝ: THỦ ĐÔ CÁC NƯỚC =================

    private function seedDlThuDoCacNuoc(): void
    {
        $this->seedDlThuDoCacNuoc71();
        $this->seedDlThuDoCacNuoc72();
        $this->seedDlThuDoCacNuoc81();
        $this->seedDlThuDoCacNuoc82();
    }

    private function seedDlThuDoCacNuoc71(): void
    {
        $L = 'dl-thu-do-cac-nuoc-lop-7-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thủ đô của Lào là thành phố nào?',
                ['Viêng Chăn', 'Phnom Penh', 'Bangkok', 'Hà Nội'], 0,
                'Viêng Chăn là thủ đô của Lào, nước láng giềng phía tây Việt Nam.', 'de');
            $this->quiz($L, 'Thủ đô của Campuchia là thành phố nào?',
                ['Phnom Penh', 'Viêng Chăn', 'Bangkok', 'Jakarta'], 0,
                'Phnom Penh là thủ đô của Campuchia.', 'de');
            $this->quiz($L, 'Thủ đô của Thái Lan là thành phố nào?',
                ['Bangkok', 'Chiang Mai', 'Phuket', 'Pattaya'], 0,
                'Bangkok là thủ đô và thành phố lớn nhất Thái Lan.', 'de');
            $this->quiz($L, 'Thủ đô của Indonesia là thành phố nào?',
                ['Jakarta', 'Bali', 'Surabaya', 'Medan'], 0,
                'Jakarta trên đảo Java là thủ đô của Indonesia.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nước Đông Nam Á với thủ đô của nó.',
                [['Việt Nam', 'Hà Nội'],
                 ['Lào', 'Viêng Chăn'],
                 ['Campuchia', 'Phnom Penh'],
                 ['Thái Lan', 'Bangkok']],
                'Hà Nội, Viêng Chăn, Phnom Penh, Bangkok là thủ đô 4 nước Đông Dương mở rộng.', 'de');
            $this->matching($L, 'Nối mỗi nước Đông Nam Á hải đảo với thủ đô của nó.',
                [['Indonesia', 'Jakarta'],
                 ['Malaysia', 'Kuala Lumpur'],
                 ['Philippines', 'Manila'],
                 ['Singapore', 'Singapore']],
                'Jakarta, Kuala Lumpur, Manila là thủ đô các nước hải đảo.', 'de');
            $this->matching($L, 'Nối mỗi nước với thủ đô của nó.',
                [['Myanmar', 'Naypyidaw'],
                 ['Brunei', 'Bandar Seri Begawan'],
                 ['Đông Timor', 'Dili'],
                 ['Việt Nam', 'Hà Nội']],
                'Naypyidaw là thủ đô mới của Myanmar (trước là Yangon).', 'de');
            $this->matching($L, 'Nối mỗi thủ đô với quốc gia của nó.',
                [['Hà Nội', 'Việt Nam'],
                 ['Bangkok', 'Thái Lan'],
                 ['Jakarta', 'Indonesia'],
                 ['Manila', 'Philippines']],
                'Luyện nhớ theo cặp nước – thủ đô.', 'de');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc KHÔNG PHẢI THỦ ĐÔ.',
                [['Hà Nội', 'Thủ đô'], ['Bangkok', 'Thủ đô'],
                 ['TP. Hồ Chí Minh', 'Không phải thủ đô'], ['Chiang Mai', 'Không phải thủ đô']],
                'Hà Nội, Bangkok là thủ đô; TP. Hồ Chí Minh, Chiang Mai chỉ là thành phố lớn.', 'de');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm ĐÔNG NAM Á LỤC ĐỊA hoặc HẢI ĐẢO.',
                [['Hà Nội', 'Đông Nam Á lục địa'], ['Bangkok', 'Đông Nam Á lục địa'],
                 ['Jakarta', 'Hải đảo'], ['Manila', 'Hải đảo']],
                'Lục địa: Việt Nam, Lào, Campuchia, Thái Lan, Myanmar; hải đảo: Indonesia, Philippines...', 'de');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm NƯỚC LÁNG GIỀNG CỦA VIỆT NAM hoặc KHÔNG.',
                [['Viêng Chăn (Lào)', 'Nước láng giềng'], ['Phnom Penh (Campuchia)', 'Nước láng giềng'],
                 ['Tokyo (Nhật Bản)', 'Không'], ['Seoul (Hàn Quốc)', 'Không']],
                'Lào và Campuchia là hai nước láng giềng của Việt Nam.', 'de');
            $this->sortQ($L, 'Kéo mỗi cặp vào nhóm ĐÚNG hoặc SAI (nước – thủ đô).',
                [['Lào – Viêng Chăn', 'Đúng'], ['Thái Lan – Bangkok', 'Đúng'],
                 ['Campuchia – Bangkok', 'Sai'], ['Việt Nam – TP. Hồ Chí Minh', 'Sai']],
                'Campuchia – Phnom Penh; Việt Nam – Hà Nội mới đúng.', 'de');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thủ đô của Lào là Viêng ___.', [[0, 'Chăn']],
                'Viêng Chăn nằm bên sông Mê Kông.', 'de');
            $this->fill($L, 'Thủ đô của Campuchia là Phnom ___.', [[0, 'Penh']],
                'Phnom Penh nằm bên sông Mê Kông.', 'de');
            $this->fill($L, 'Thủ đô của Thái Lan là ___.', [[0, 'Bangkok']],
                'Bangkok là thành phố đông dân, sầm uất.', 'de');
            $this->fill($L, 'Thủ đô của Indonesia là ___.', [[0, 'Jakarta']],
                'Jakarta nằm trên đảo Java.', 'de');
        }
    }

    private function seedDlThuDoCacNuoc72(): void
    {
        $L = 'dl-thu-do-cac-nuoc-lop-7-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thủ đô của Nhật Bản là thành phố nào?',
                ['Tokyo', 'Osaka', 'Kyoto', 'Nagoya'], 0,
                'Tokyo là thủ đô của Nhật Bản.', 'de');
            $this->quiz($L, 'Thủ đô của Trung Quốc là thành phố nào?',
                ['Bắc Kinh', 'Thượng Hải', 'Quảng Châu', 'Thâm Quyến'], 0,
                'Bắc Kinh là thủ đô của Trung Quốc; Thượng Hải chỉ là thành phố lớn nhất.', 'de');
            $this->quiz($L, 'Thủ đô của Hàn Quốc là thành phố nào?',
                ['Seoul', 'Busan', 'Incheon', 'Daegu'], 0,
                'Seoul là thủ đô của Hàn Quốc.', 'de');
            $this->quiz($L, 'Thủ đô của Ấn Độ là thành phố nào?',
                ['New Delhi', 'Mumbai', 'Kolkata', 'Chennai'], 0,
                'New Delhi là thủ đô của Ấn Độ; Mumbai là thành phố lớn nhất.', 'de');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nước Đông Á với thủ đô của nó.',
                [['Nhật Bản', 'Tokyo'],
                 ['Trung Quốc', 'Bắc Kinh'],
                 ['Hàn Quốc', 'Seoul'],
                 ['Mông Cổ', 'Ulan Bator']],
                'Tokyo, Bắc Kinh, Seoul, Ulan Bator là thủ đô Đông Á.', 'de');
            $this->matching($L, 'Nối mỗi nước Nam Á – Tây Á với thủ đô của nó.',
                [['Ấn Độ', 'New Delhi'],
                 ['Thổ Nhĩ Kỳ', 'Ankara'],
                 ['Ả Rập Xê Út', 'Riyadh'],
                 ['I-ran', 'Tehran']],
                'New Delhi, Ankara, Riyadh, Tehran là thủ đô Nam Á – Tây Á.', 'de');
            $this->matching($L, 'Nối mỗi thành phố lớn với nhận xét đúng.',
                [['Thượng Hải', 'Thành phố lớn nhất Trung Quốc, không phải thủ đô'],
                 ['Mumbai', 'Thành phố lớn nhất Ấn Độ, không phải thủ đô'],
                 ['Osaka', 'Thành phố lớn của Nhật, không phải thủ đô'],
                 ['Istanbul', 'Thành phố lớn nhất Thổ Nhĩ Kỳ, không phải thủ đô']],
                'Thành phố lớn nhất chưa chắc là thủ đô – bẫy thường gặp.', 'trung_binh');
            $this->matching($L, 'Nối mỗi thủ đô với quốc gia của nó.',
                [['Tokyo', 'Nhật Bản'],
                 ['Bắc Kinh', 'Trung Quốc'],
                 ['Seoul', 'Hàn Quốc'],
                 ['New Delhi', 'Ấn Độ']],
                'Luyện nhớ theo cặp nước – thủ đô châu Á.', 'de');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc THÀNH PHỐ LỚN (không phải thủ đô).',
                [['Tokyo', 'Thủ đô'], ['Bắc Kinh', 'Thủ đô'],
                 ['Thượng Hải', 'Không phải thủ đô'], ['Osaka', 'Không phải thủ đô']],
                'Tokyo, Bắc Kinh là thủ đô; Thượng Hải, Osaka chỉ là thành phố lớn.', 'de');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm ĐÔNG Á hoặc NAM Á – TÂY Á.',
                [['Tokyo', 'Đông Á'], ['Seoul', 'Đông Á'],
                 ['New Delhi', 'Nam Á – Tây Á'], ['Ankara', 'Nam Á – Tây Á']],
                'Tokyo, Seoul ở Đông Á; New Delhi ở Nam Á; Ankara ở Tây Á.', 'de');
            $this->sortQ($L, 'Kéo mỗi cặp vào nhóm ĐÚNG hoặc SAI (nước – thủ đô).',
                [['Nhật Bản – Tokyo', 'Đúng'], ['Hàn Quốc – Seoul', 'Đúng'],
                 ['Trung Quốc – Thượng Hải', 'Sai'], ['Ấn Độ – Mumbai', 'Sai']],
                'Trung Quốc – Bắc Kinh; Ấn Độ – New Delhi mới đúng.', 'de');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm ĐÃ TỪNG ĐỔI TÊN/ĐỔI VỊ TRÍ hoặc ỔN ĐỊNH LÂU DÀI.',
                [['Bắc Kinh', 'Ổn định lâu dài'], ['Tokyo', 'Ổn định lâu dài'],
                 ['Naypyidaw (Myanmar)', 'Đã từng đổi tên/đổi vị trí'], ['Astana (Kazakhstan)', 'Đã từng đổi tên/đổi vị trí']],
                'Một số nước châu Á đã dời đô hoặc đổi tên thủ đô.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thủ đô của Nhật Bản là ___.', [[0, 'Tokyo']],
                'Tokyo là một trong những đô thị đông dân nhất thế giới.', 'de');
            $this->fill($L, 'Thủ đô của Trung Quốc là Bắc ___.', [[0, 'Kinh']],
                'Bắc Kinh có Tử Cấm Thành nổi tiếng.', 'de');
            $this->fill($L, 'Thủ đô của Hàn Quốc là ___.', [[0, 'Seoul']],
                'Seoul nằm bên sông Hàn.', 'de');
            $this->fill($L, 'Thủ đô của Ấn Độ là New ___.', [[0, 'Delhi']],
                'New Delhi là trung tâm chính trị của Ấn Độ.', 'de');
        }
    }

    private function seedDlThuDoCacNuoc81(): void
    {
        $L = 'dl-thu-do-cac-nuoc-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thủ đô của Pháp là thành phố nào?',
                ['Paris', 'London', 'Berlin', 'Madrid'], 0,
                'Paris là thủ đô của Pháp, nổi tiếng với tháp Eiffel.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của Đức là thành phố nào?',
                ['Berlin', 'Munich', 'Hamburg', 'Frankfurt'], 0,
                'Berlin là thủ đô của Đức.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của Ý là thành phố nào?',
                ['Rome', 'Milan', 'Venice', 'Naples'], 0,
                'Rome là thủ đô của Ý, thành phố có lịch sử hàng nghìn năm.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của Nga là thành phố nào?',
                ['Mát-xcơ-va', 'Saint Petersburg', 'Kiev', 'Minsk'], 0,
                'Mát-xcơ-va (Moscow) là thủ đô của Liên bang Nga.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nước Tây Âu với thủ đô của nó.',
                [['Pháp', 'Paris'],
                 ['Đức', 'Berlin'],
                 ['Anh', 'London'],
                 ['Tây Ban Nha', 'Madrid']],
                'Paris, Berlin, London, Madrid là thủ đô Tây Âu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước Bắc – Đông Âu với thủ đô của nó.',
                [['Nga', 'Mát-xcơ-va'],
                 ['Ba Lan', 'Warsaw'],
                 ['U-crai-na', 'Kiev'],
                 ['Thuỵ Điển', 'Stockholm']],
                'Mát-xcơ-va, Warsaw, Kiev, Stockholm là thủ đô Bắc – Đông Âu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước Nam Âu với thủ đô của nó.',
                [['Ý', 'Rome'],
                 ['Bồ Đào Nha', 'Lisbon'],
                 ['Hy Lạp', 'Athens'],
                 ['Hà Lan', 'Amsterdam']],
                'Rome, Lisbon, Athens là thủ đô Nam Âu; Amsterdam ở Tây Âu.', 'trung_binh');
            $this->matching($L, 'Nối mỗi thủ đô với công trình nổi tiếng của nó.',
                [['Paris', 'Tháp Eiffel'],
                 ['London', 'Đồng hồ Big Ben'],
                 ['Rome', 'Đấu trường Cô-lô-xê'],
                 ['Athens', 'Đền Pác-tê-nông']],
                'Mỗi thủ đô châu Âu gắn với công trình biểu tượng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ CHÂU ÂU hoặc KHÔNG PHẢI.',
                [['Paris', 'Thủ đô châu Âu'], ['Berlin', 'Thủ đô châu Âu'],
                 ['New York', 'Không phải'], ['Sydney', 'Không phải']],
                'Paris, Berlin là thủ đô châu Âu.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm TÂY ÂU hoặc ĐÔNG – BẮC ÂU.',
                [['Paris', 'Tây Âu'], ['Madrid', 'Tây Âu'],
                 ['Mát-xcơ-va', 'Đông – Bắc Âu'], ['Warsaw', 'Đông – Bắc Âu']],
                'Paris, Madrid ở Tây – Nam Âu; Mát-xcơ-va, Warsaw ở Đông Âu.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cặp vào nhóm ĐÚNG hoặc SAI (nước – thủ đô).',
                [['Pháp – Paris', 'Đúng'], ['Đức – Berlin', 'Đúng'],
                 ['Ý – Milan', 'Sai'], ['Anh – Manchester', 'Sai']],
                'Ý – Rome; Anh – London mới đúng.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm CÓ SÔNG CHẢY QUA hoặc KHÔNG RÕ.',
                [['Paris (sông Seine)', 'Có sông chảy qua'], ['London (sông Thames)', 'Có sông chảy qua'],
                 ['Rome (sông Tiber)', 'Có sông chảy qua'], ['Madrid', 'Không rõ']],
                'Nhiều thủ đô châu Âu hình thành bên các dòng sông.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thủ đô của Pháp là ___.', [[0, 'Paris']],
                'Paris có tháp Eiffel nổi tiếng thế giới.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Đức là ___.', [[0, 'Berlin']],
                'Berlin từng bị chia cắt bởi bức tường Berlin.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Ý là ___.', [[0, 'Rome']],
                'Rome có đấu trường Cô-lô-xê cổ đại.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Nga là Mát-xcơ-___.', [[0, 'va']],
                'Quảng trường Đỏ nằm ở trung tâm Mát-xcơ-va.', 'trung_binh');
        }
    }

    private function seedDlThuDoCacNuoc82(): void
    {
        $L = 'dl-thu-do-cac-nuoc-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Thủ đô của Hoa Kì là thành phố nào?',
                ['Washington D.C.', 'New York', 'Los Angeles', 'Chicago'], 0,
                'Washington D.C. là thủ đô Hoa Kì; New York chỉ là thành phố lớn nhất.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của Ca-na-đa là thành phố nào?',
                ['Ottawa', 'Toronto', 'Vancouver', 'Montreal'], 0,
                'Ottawa là thủ đô Ca-na-đa; Toronto là thành phố lớn nhất.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của Ô-xtrây-li-a (Úc) là thành phố nào?',
                ['Canberra', 'Sydney', 'Melbourne', 'Perth'], 0,
                'Canberra là thủ đô Úc; Sydney, Melbourne chỉ là thành phố lớn.', 'trung_binh');
            $this->quiz($L, 'Thủ đô của Ai Cập là thành phố nào?',
                ['Cairo', 'Alexandria', 'Giza', 'Luxor'], 0,
                'Cairo là thủ đô Ai Cập, thành phố lớn nhất châu Phi.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi nước châu Mỹ với thủ đô của nó.',
                [['Hoa Kì', 'Washington D.C.'],
                 ['Ca-na-đa', 'Ottawa'],
                 ['Bra-xin', 'Brasilia'],
                 ['Argentina', 'Buenos Aires']],
                'Washington D.C., Ottawa, Brasilia là thủ đô châu Mỹ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước châu Phi với thủ đô của nó.',
                [['Ai Cập', 'Cairo'],
                 ['Nam Phi', 'Pretoria'],
                 ['Ni-giê-ri-a', 'Abuja'],
                 ['Kê-ni-a', 'Nairobi']],
                'Cairo, Pretoria, Abuja là thủ đô châu Phi.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nước châu Đại Dương với thủ đô của nó.',
                [['Úc', 'Canberra'],
                 ['New Zealand', 'Wellington'],
                 ['Phi-gi', 'Suva'],
                 ['Pa-pua New Ghi-nê', 'Port Moresby']],
                'Canberra, Wellington là thủ đô châu Đại Dương.', 'trung_binh');
            $this->matching($L, 'Nối mỗi "bẫy" với đáp án đúng.',
                [['Thủ đô Hoa Kì', 'Washington D.C. (không phải New York)'],
                 ['Thủ đô Úc', 'Canberra (không phải Sydney)'],
                 ['Thủ đô Ca-na-đa', 'Ottawa (không phải Toronto)'],
                 ['Thủ đô Bra-xin', 'Brasilia (không phải Rio de Janeiro)']],
                'Cẩn thận với các thành phố nổi tiếng nhưng không phải thủ đô.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi thành phố vào nhóm THỦ ĐÔ hoặc THÀNH PHỐ LỚN (không phải thủ đô).',
                [['Washington D.C.', 'Thủ đô'], ['Canberra', 'Thủ đô'],
                 ['New York', 'Không phải thủ đô'], ['Sydney', 'Không phải thủ đô']],
                'Washington D.C., Canberra là thủ đô; New York, Sydney không phải.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm CHÂU MỸ hoặc CHÂU PHI – ĐẠI DƯƠNG.',
                [['Washington D.C.', 'châu Mỹ'], ['Brasilia', 'châu Mỹ'],
                 ['Cairo', 'châu Phi – Đại Dương'], ['Canberra', 'châu Phi – Đại Dương']],
                'Washington D.C., Brasilia ở châu Mỹ; Cairo ở châu Phi; Canberra ở Đại Dương.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi cặp vào nhóm ĐÚNG hoặc SAI (nước – thủ đô).',
                [['Hoa Kì – Washington D.C.', 'Đúng'], ['Úc – Canberra', 'Đúng'],
                 ['Ca-na-đa – Toronto', 'Sai'], ['Ai Cập – Alexandria', 'Sai']],
                'Ca-na-đa – Ottawa; Ai Cập – Cairo mới đúng.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi thủ đô vào nhóm ĐƯỢC XÂY MỚI LÀM THỦ ĐÔ hoặc THỦ ĐÔ LỊCH SỬ.',
                [['Washington D.C.', 'Được xây mới làm thủ đô'], ['Canberra', 'Được xây mới làm thủ đô'],
                 ['Brasilia', 'Được xây mới làm thủ đô'], ['Cairo', 'Thủ đô lịch sử']],
                'Washington D.C., Canberra, Brasilia là thủ đô được quy hoạch xây mới.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Thủ đô của Hoa Kì là Washington ___.', [[0, 'D.C.']],
                'D.C. là viết tắt của District of Columbia.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Ca-na-đa là ___.', [[0, 'Ottawa']],
                'Không phải Toronto dù Toronto lớn hơn.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Úc là ___.', [[0, 'Canberra']],
                'Không phải Sydney dù Sydney nổi tiếng hơn.', 'trung_binh');
            $this->fill($L, 'Thủ đô của Ai Cập là ___.', [[0, 'Cairo']],
                'Cairo nằm bên sông Nin.', 'trung_binh');
        }
    }

    // ================= ĐỊA LÝ: ĐỊA HÌNH VIỆT NAM =================

    private function seedDlDiaHinhVietNam(): void
    {
        $this->seedDlDiaHinhVietNam81();
        $this->seedDlDiaHinhVietNam82();
        $this->seedDlDiaHinhVietNam91();
        $this->seedDlDiaHinhVietNam92();
    }

    private function seedDlDiaHinhVietNam81(): void
    {
        $L = 'dl-dia-hinh-viet-nam-lop-8-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Địa hình Việt Nam có đặc điểm nổi bật nào?',
                ['3/4 diện tích là đồi núi', 'Hoàn toàn là đồng bằng', 'Hoàn toàn là núi cao', 'Không có đồi núi'], 0,
                '3/4 diện tích Việt Nam là đồi núi, 1/4 là đồng bằng.', 'trung_binh');
            $this->quiz($L, 'Dãy núi nào có đỉnh Fansipan cao 3 143 m?',
                ['Hoàng Liên Sơn', 'Trường Sơn', 'Tam Đảo', 'Bạch Mã'], 0,
                'Dãy Hoàng Liên Sơn ở Tây Bắc có đỉnh Fansipan – nóc nhà Đông Dương.', 'trung_binh');
            $this->quiz($L, 'Vùng núi Tây Bắc và Đông Bắc khác nhau ở điểm nào?',
                ['Tây Bắc núi cao, hiểm trở; Đông Bắc núi thấp hơn, nhiều cánh cung', 'Hoàn toàn giống nhau', 'Tây Bắc là đồng bằng', 'Đông Bắc không có núi'], 0,
                'Tây Bắc có núi cao nhất nước; Đông Bắc có địa hình cánh cung đặc trưng.', 'trung_binh');
            $this->quiz($L, 'Vùng trung du Bắc Bộ có đất gì thích hợp trồng cây công nghiệp?',
                ['Đất feralit', 'Đất phù sa', 'Đất cát', 'Đất phèn'], 0,
                'Trung du Bắc Bộ có đất feralit trên đá badan và đá vôi, thích hợp trồng chè, cây ăn quả.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi vùng núi với đặc điểm của nó.',
                [['Tây Bắc', 'Núi cao nhất, có Fansipan'],
                 ['Đông Bắc', 'Địa hình cánh cung'],
                 ['Trường Sơn Bắc', 'Núi thấp, hẹp ngang'],
                 ['Trường Sơn Nam', 'Cao nguyên xếp tầng (Tây Nguyên)']],
                'Bốn vùng núi: Tây Bắc, Đông Bắc, Trường Sơn Bắc, Trường Sơn Nam.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đỉnh núi, cao nguyên với vùng của nó.',
                [['Fansipan', 'Hoàng Liên Sơn – Tây Bắc'],
                 ['Ngọc Linh', 'Trường Sơn Nam'],
                 ['Tây Nguyên', 'Cao nguyên đất đỏ badan'],
                 ['Đồng Văn', 'Cao nguyên đá – Đông Bắc']],
                'Fansipan ở Tây Bắc; Tây Nguyên là vùng cao nguyên đất đỏ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi loại đất với vùng phân bố.',
                [['Đất feralit', 'Đồi núi, trung du'],
                 ['Đất phù sa', 'Đồng bằng'],
                 ['Đất badan', 'Tây Nguyên'],
                 ['Đất cát biển', 'Ven biển miền Trung']],
                'Đất feralit ở đồi núi; phù sa ở đồng bằng; badan ở Tây Nguyên.', 'trung_binh');
            $this->matching($L, 'Nối mỗi cây trồng với vùng thích hợp.',
                [['Chè', 'Trung du Bắc Bộ'],
                 ['Cà phê', 'Tây Nguyên'],
                 ['Lúa nước', 'Đồng bằng'],
                 ['Cao su', 'Đông Nam Bộ, Tây Nguyên']],
                'Chè ở trung du; cà phê ở Tây Nguyên; lúa ở đồng bằng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi vùng vào nhóm MIỀN NÚI hoặc ĐỒNG BẰNG.',
                [['Tây Bắc', 'Miền núi'], ['Tây Nguyên', 'Miền núi'],
                 ['Đồng bằng sông Hồng', 'Đồng bằng'], ['Đồng bằng sông Cửu Long', 'Đồng bằng']],
                'Tây Bắc, Tây Nguyên là miền núi; hai đồng bằng châu thổ là đồng bằng.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi dãy núi vào nhóm TÂY BẮC hoặc TRƯỜNG SƠN.',
                [['Hoàng Liên Sơn', 'Tây Bắc'], ['Pu Đen Đinh', 'Tây Bắc'],
                 ['Bạch Mã', 'Trường Sơn'], ['Ngọc Linh', 'Trường Sơn']],
                'Hoàng Liên Sơn ở Tây Bắc; Bạch Mã, Ngọc Linh thuộc Trường Sơn.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đặc điểm vào nhóm VÙNG NÚI TÂY BẮC hoặc ĐÔNG BẮC.',
                [['Núi cao, hiểm trở nhất nước', 'Vùng núi Tây Bắc'], ['Có đỉnh Fansipan', 'Vùng núi Tây Bắc'],
                 ['Địa hình cánh cung', 'Đông Bắc'], ['Cao nguyên đá Đồng Văn', 'Đông Bắc']],
                'Tây Bắc: núi cao hiểm trở; Đông Bắc: cánh cung, cao nguyên đá.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm PHÙ HỢP VỚI MIỀN NÚI hoặc ĐỒNG BẰNG.',
                [['Trồng chè, cà phê', 'Phù hợp với miền núi'], ['Chăn nuôi đại gia súc', 'Phù hợp với miền núi'],
                 ['Trồng lúa nước', 'Phù hợp với đồng bằng'], ['Nuôi tôm nước lợ', 'Phù hợp với đồng bằng']],
                'Miền núi: cây công nghiệp, chăn nuôi; đồng bằng: lúa nước, thuỷ sản.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Ba phần tư diện tích Việt Nam là đồi ___.', [[0, 'núi']],
                'Chỉ 1/4 là đồng bằng.', 'trung_binh');
            $this->fill($L, 'Đỉnh núi cao nhất Việt Nam là ___, cao 3 143 m.', [[0, 'Fansipan']],
                'Fansipan thuộc dãy Hoàng Liên Sơn, mệnh danh nóc nhà Đông Dương.', 'trung_binh');
            $this->fill($L, 'Vùng núi Đông Bắc có địa hình cánh ___.', [[0, 'cung']],
                'Các cánh cung: Sông Gâm, Ngân Sơn, Bắc Sơn, Đông Triều.', 'trung_binh');
            $this->fill($L, 'Tây Nguyên là vùng cao nguyên đất đỏ ___.', [[0, 'badan']],
                'Đất badan rất thích hợp trồng cà phê.', 'trung_binh');
        }
    }

    private function seedDlDiaHinhVietNam82(): void
    {
        $L = 'dl-dia-hinh-viet-nam-lop-8-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Đồng bằng châu thổ lớn nhất Việt Nam là đồng bằng nào?',
                ['Đồng bằng sông Cửu Long', 'Đồng bằng sông Hồng', 'Đồng bằng ven biển miền Trung', 'Đồng bằng Đông Nam Bộ'], 0,
                'Đồng bằng sông Cửu Long rộng khoảng 40 000 km², lớn nhất nước.', 'trung_binh');
            $this->quiz($L, 'Đường bờ biển Việt Nam dài khoảng bao nhiêu?',
                ['Hơn 3 260 km', 'Hơn 1 000 km', 'Hơn 5 000 km', 'Hơn 500 km'], 0,
                'Bờ biển Việt Nam dài hơn 3 260 km, từ Quảng Ninh đến Kiên Giang.', 'trung_binh');
            $this->quiz($L, 'Hai quần đảo xa bờ của Việt Nam là quần đảo nào?',
                ['Hoàng Sa và Trường Sa', 'Cát Bà và Phú Quốc', 'Côn Đảo và Phú Quý', 'Lý Sơn và Cù Lao Chàm'], 0,
                'Hoàng Sa (Đà Nẵng) và Trường Sa (Khánh Hoà) là hai quần đảo thiêng liêng.', 'trung_binh');
            $this->quiz($L, 'Vùng biển Việt Nam có nguồn lợi lớn nào?',
                ['Thuỷ sản và dầu khí', 'Vàng bạc', 'Kim cương', 'Than đá'], 0,
                'Biển Việt Nam giàu thuỷ sản, dầu khí và tiềm năng du lịch.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi đồng bằng với đặc điểm của nó.',
                [['Đồng bằng sông Hồng', 'Được bồi đắp bởi sông Hồng, sông Thái Bình'],
                 ['Đồng bằng sông Cửu Long', 'Rộng lớn nhất, nhiều kênh rạch'],
                 ['Đồng bằng ven biển miền Trung', 'Hẹp ngang, bị chia cắt'],
                 ['Cả hai đồng bằng lớn', 'Là vựa lúa của cả nước']],
                'Hai đồng bằng châu thổ là vựa lúa lớn nhất nước.', 'trung_binh');
            $this->matching($L, 'Nối mỗi đảo, quần đảo với đặc điểm của nó.',
                [['Phú Quốc', 'Đảo lớn nhất Việt Nam'],
                 ['Cát Bà', 'Đảo lớn ở vịnh Bắc Bộ'],
                 ['Hoàng Sa', 'Quần đảo thuộc Đà Nẵng'],
                 ['Trường Sa', 'Quần đảo thuộc Khánh Hoà']],
                'Phú Quốc lớn nhất; Hoàng Sa, Trường Sa là quần đảo xa bờ.', 'trung_binh');
            $this->matching($L, 'Nối mỗi vũng, vịnh với đặc điểm của nó.',
                [['Vịnh Hạ Long', 'Di sản thiên nhiên thế giới'],
                 ['Vịnh Bắc Bộ', 'Vịnh lớn ở phía Bắc'],
                 ['Vũng Tàu', 'Cửa ngõ biển Đông Nam Bộ'],
                 ['Đà Nẵng', 'Vũng biển miền Trung']],
                'Vịnh Hạ Long là di sản thiên nhiên thế giới.', 'trung_binh');
            $this->matching($L, 'Nối mỗi nguồn lợi biển với ví dụ.',
                [['Thuỷ sản', 'Cá, tôm, mực'],
                 ['Khoáng sản', 'Dầu khí, titan'],
                 ['Du lịch', 'Vịnh Hạ Long, Phú Quốc'],
                 ['Giao thông', 'Cảng Hải Phòng, Đà Nẵng, Sài Gòn']],
                'Biển mang lại thuỷ sản, dầu khí, du lịch và giao thông.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi vùng vào nhóm ĐỒNG BẰNG hoặc VEN BIỂN – HẢI ĐẢO.',
                [['Đồng bằng sông Hồng', 'Đồng bằng'], ['Đồng bằng sông Cửu Long', 'Đồng bằng'],
                 ['Đảo Phú Quốc', 'Ven biển – hải đảo'], ['Quần đảo Trường Sa', 'Ven biển – hải đảo']],
                'Đồng bằng ở trong đất liền; đảo, quần đảo ở ven biển, ngoài khơi.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi địa danh vào nhóm ĐỒNG BẰNG SÔNG HỒNG hoặc SÔNG CỬU LONG.',
                [['Hà Nội', 'Đồng bằng sông Hồng'], ['Hải Phòng', 'Đồng bằng sông Hồng'],
                 ['Cần Thơ', 'Sông Cửu Long'], ['Cà Mau', 'Sông Cửu Long']],
                'Hà Nội, Hải Phòng ở đồng bằng sông Hồng; Cần Thơ, Cà Mau ở đồng bằng sông Cửu Long.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi đảo vào nhóm ĐẢO GẦN BỜ hoặc QUẦN ĐẢO XA BỜ.',
                [['Phú Quốc', 'Đảo gần bờ'], ['Cát Bà', 'Đảo gần bờ'],
                 ['Hoàng Sa', 'Quần đảo xa bờ'], ['Trường Sa', 'Quần đảo xa bờ']],
                'Phú Quốc, Cát Bà gần bờ; Hoàng Sa, Trường Sa xa bờ.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi lợi ích vào nhóm KINH TẾ hoặc QUỐC PHÒNG.',
                [['Đánh bắt thuỷ sản', 'Kinh tế'], ['Khai thác dầu khí', 'Kinh tế'],
                 ['Bảo vệ chủ quyền biển đảo', 'Quốc phòng'], ['Căn cứ hải quân', 'Quốc phòng']],
                'Biển vừa mang lợi ích kinh tế vừa có ý nghĩa quốc phòng.', 'trung_binh');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Đồng bằng châu thổ lớn nhất nước ta là đồng bằng sông Cửu ___.', [[0, 'Long']],
                'Rộng khoảng 40 000 km², là vựa lúa lớn nhất.', 'trung_binh');
            $this->fill($L, 'Bờ biển Việt Nam dài hơn 3 260 ___.', [[0, 'km']],
                'Từ Quảng Ninh đến Kiên Giang.', 'trung_binh');
            $this->fill($L, 'Hai quần đảo xa bờ là Hoàng Sa và Trường ___.', [[0, 'Sa']],
                'Là bộ phận thiêng liêng của Tổ quốc.', 'trung_binh');
            $this->fill($L, 'Vịnh Hạ Long là di sản thiên nhiên thế ___.', [[0, 'giới']],
                'Được UNESCO công nhận.', 'trung_binh');
        }
    }

    private function seedDlDiaHinhVietNam91(): void
    {
        $L = 'dl-dia-hinh-viet-nam-lop-9-1';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Than đá ở Việt Nam tập trung chủ yếu ở đâu?',
                ['Quảng Ninh', 'Tây Nguyên', 'Đồng bằng sông Cửu Long', 'Duyên hải miền Trung'], 0,
                'Than đá tập trung ở Quảng Ninh với trữ lượng lớn.', 'trung_binh');
            $this->quiz($L, 'Dầu khí của Việt Nam phân bố chủ yếu ở đâu?',
                ['Thềm lục địa phía Nam', 'Đồng bằng sông Hồng', 'Tây Bắc', 'Đông Bắc'], 0,
                'Dầu khí ở thềm lục địa phía Nam, các mỏ Bạch Hổ, Đại Hùng...', 'trung_binh');
            $this->quiz($L, 'Bô-xít – nguyên liệu sản xuất nhôm – tập trung ở đâu?',
                ['Tây Nguyên', 'Quảng Ninh', 'Thái Nguyên', 'Lào Cai'], 0,
                'Tây Nguyên có trữ lượng bô-xít rất lớn.', 'trung_binh');
            $this->quiz($L, 'Vì sao phải khai thác khoáng sản hợp lí, tiết kiệm?',
                ['Khoáng sản hình thành qua hàng triệu năm, sẽ cạn kiệt', 'Khoáng sản là vô tận', 'Khai thác nhiều càng tốt', 'Không cần quan tâm'], 0,
                'Khoáng sản là tài nguyên không tái tạo, cần khai thác hợp lí.', 'kho');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi khoáng sản với nơi phân bố chính.',
                [['Than đá', 'Quảng Ninh'],
                 ['Dầu khí', 'Thềm lục địa phía Nam'],
                 ['Bô-xít', 'Tây Nguyên'],
                 ['Sắt', 'Thái Nguyên']],
                'Than ở Quảng Ninh; dầu khí ở thềm lục địa; bô-xít ở Tây Nguyên; sắt ở Thái Nguyên.', 'trung_binh');
            $this->matching($L, 'Nối mỗi khoáng sản với công dụng của nó.',
                [['Than đá', 'Nhiên liệu, sản xuất điện'],
                 ['Dầu khí', 'Nhiên liệu, hoá dầu'],
                 ['Bô-xít', 'Sản xuất nhôm'],
                 ['A-pa-tít', 'Sản xuất phân bón']],
                'Khoáng sản là nguyên liệu quan trọng cho công nghiệp.', 'trung_binh');
            $this->matching($L, 'Nối mỗi loại tài nguyên với tính chất của nó.',
                [['Than đá, dầu khí', 'Không tái tạo, sẽ cạn kiệt'],
                 ['Rừng trồng', 'Có thể tái tạo'],
                 ['Năng lượng mặt trời', 'Vô tận, sạch'],
                 ['Đất đai', 'Hạn chế, cần bảo vệ']],
                'Phân loại tài nguyên để sử dụng hợp lí.', 'kho');
            $this->matching($L, 'Nối mỗi mỏ với khoáng sản của nó.',
                [['Mỏ than Quảng Ninh', 'Than đá'],
                 ['Mỏ Bạch Hổ', 'Dầu khí'],
                 ['Mỏ sắt Trại Cau', 'Sắt'],
                 ['Mỏ a-pa-tít Lào Cai', 'A-pa-tít']],
                'Các mỏ khoáng sản lớn của Việt Nam.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi khoáng sản vào nhóm NĂNG LƯỢNG hoặc KIM LOẠI.',
                [['Than đá', 'Năng lượng'], ['Dầu khí', 'Năng lượng'],
                 ['Sắt', 'Kim loại'], ['Bô-xít', 'Kim loại']],
                'Than, dầu là khoáng sản năng lượng; sắt, bô-xít là kim loại.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi tài nguyên vào nhóm TÁI TẠO ĐƯỢC hoặc KHÔNG TÁI TẠO.',
                [['Rừng trồng', 'Tái tạo được'], ['Năng lượng gió', 'Tái tạo được'],
                 ['Than đá', 'Không tái tạo'], ['Dầu mỏ', 'Không tái tạo']],
                'Rừng, gió tái tạo được; than đá, dầu mỏ sẽ cạn kiệt.', 'kho');
            $this->sortQ($L, 'Kéo mỗi khoáng sản vào nhóm Ở MIỀN BẮC hoặc Ở MIỀN NAM – TÂY NGUYÊN.',
                [['Than Quảng Ninh', 'Ở miền Bắc'], ['Sắt Thái Nguyên', 'Ở miền Bắc'],
                 ['Dầu khí thềm lục địa Nam', 'Ở miền Nam – Tây Nguyên'], ['Bô-xít Tây Nguyên', 'Ở miền Nam – Tây Nguyên']],
                'Than, sắt ở miền Bắc; dầu khí, bô-xít ở phía Nam, Tây Nguyên.', 'trung_binh');
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm KHAI THÁC HỢP LÍ hoặc LÃNG PHÍ.',
                [['Khai thác có kế hoạch, tiết kiệm', 'Khai thác hợp lí'], ['Tái chế kim loại', 'Khai thác hợp lí'],
                 ['Khai thác bừa bãi', 'Lãng phí'], ['Để thất thoát, ô nhiễm', 'Lãng phí']],
                'Khai thác hợp lí, tiết kiệm và tái chế là cần thiết.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Than đá tập trung chủ yếu ở tỉnh Quảng ___.', [[0, 'Ninh']],
                'Vùng than Quảng Ninh có trữ lượng lớn nhất nước.', 'trung_binh');
            $this->fill($L, 'Dầu khí phân bố ở thềm lục địa phía ___.', [[0, 'Nam']],
                'Các mỏ: Bạch Hổ, Đại Hùng, Lan Tây...', 'trung_binh');
            $this->fill($L, 'Bô-xít là nguyên liệu để sản xuất ___.', [[0, 'nhôm']],
                'Tây Nguyên có trữ lượng bô-xít rất lớn.', 'trung_binh');
            $this->fill($L, 'Khoáng sản là tài nguyên không tái tạo nên cần khai thác hợp lí, tiết ___.', [[0, 'kiệm']],
                'Tránh lãng phí và gây ô nhiễm môi trường.', 'kho');
        }
    }

    private function seedDlDiaHinhVietNam92(): void
    {
        $L = 'dl-dia-hinh-viet-nam-lop-9-2';
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, 'Hiện nay rừng Việt Nam đang trong tình trạng nào?',
                ['Bị thu hẹp do khai thác quá mức', 'Phủ kín khắp cả nước', 'Không còn vai trò gì', 'Tự phục hồi hoàn toàn'], 0,
                'Rừng bị thu hẹp do khai thác quá mức, đốt nương làm rẫy.', 'kho');
            $this->quiz($L, 'Biện pháp nào sau đây giúp bảo vệ rừng?',
                ['Trồng rừng, giao đất giao rừng cho dân', 'Chặt hết rừng làm nương', 'Đốt rừng lấy đất', 'Khai thác gỗ bừa bãi'], 0,
                'Trồng rừng, bảo vệ rừng đầu nguồn là biện pháp quan trọng.', 'kho');
            $this->quiz($L, 'Biến đổi khí hậu gây ra hậu quả nào cho Việt Nam?',
                ['Nước biển dâng, bão lũ nhiều hơn', 'Khí hậu mát mẻ hơn', 'Không ảnh hưởng gì', 'Mưa thuận gió hoà mãi'], 0,
                'Nước biển dâng đe doạ đồng bằng sông Cửu Long; bão lũ diễn biến phức tạp.', 'kho');
            $this->quiz($L, 'Mỗi học sinh có thể làm gì để bảo vệ môi trường?',
                ['Tiết kiệm điện nước, không xả rác, trồng cây xanh', 'Xả rác bừa bãi', 'Chặt cây trong trường', 'Lãng phí giấy'], 0,
                'Tiết kiệm tài nguyên, giữ vệ sinh, trồng cây là việc ai cũng làm được.', 'trung_binh');
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, 'Nối mỗi vấn đề môi trường với nguyên nhân của nó.',
                [['Rừng bị thu hẹp', 'Khai thác quá mức, đốt nương'],
                 ['Ô nhiễm nguồn nước', 'Xả thải chưa qua xử lí'],
                 ['Đất bạc màu', 'Canh tác không hợp lí'],
                 ['Không khí ô nhiễm', 'Khói bụi công nghiệp, giao thông']],
                'Mỗi vấn đề môi trường đều có nguyên nhân từ con người.', 'kho');
            $this->matching($L, 'Nối mỗi biện pháp với mục đích của nó.',
                [['Trồng rừng', 'Phủ xanh đất trống, chống xói mòn'],
                 ['Xử lí nước thải', 'Bảo vệ nguồn nước'],
                 ['Tiết kiệm năng lượng', 'Giảm khai thác, giảm khí thải'],
                 ['Phân loại rác', 'Tái chế, giảm ô nhiễm']],
                'Bảo vệ môi trường cần nhiều biện pháp đồng bộ.', 'kho');
            $this->matching($L, 'Nối mỗi biểu hiện với hậu quả của biến đổi khí hậu.',
                [['Nước biển dâng', 'Ngập đồng bằng ven biển'],
                 ['Bão mạnh hơn', 'Thiệt hại về người và tài sản'],
                 ['Hạn hán kéo dài', 'Thiếu nước sản xuất'],
                 ['Xâm nhập mặn', 'Đất nhiễm mặn, khó trồng trọt']],
                'Biến đổi khí hậu đe doạ trực tiếp đồng bằng sông Cửu Long.', 'kho');
            $this->matching($L, 'Nối mỗi hành động với nhóm của nó.',
                [['Tắt đèn khi ra khỏi phòng', 'Tiết kiệm năng lượng'],
                 ['Mang túi vải đi chợ', 'Giảm rác thải nhựa'],
                 ['Trồng cây xanh', 'Tăng mảng xanh'],
                 ['Đổ dầu thải xuống cống', 'Gây ô nhiễm (cần tránh)']],
                'Hành động nhỏ mỗi ngày góp phần bảo vệ môi trường.', 'trung_binh');
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, 'Kéo mỗi việc làm vào nhóm BẢO VỆ MÔI TRƯỜNG hoặc GÂY Ô NHIỄM.',
                [['Trồng cây xanh', 'Bảo vệ môi trường'], ['Phân loại rác thải', 'Bảo vệ môi trường'],
                 ['Xả rác xuống sông', 'Gây ô nhiễm'], ['Đốt rừng làm nương', 'Gây ô nhiễm']],
                'Trồng cây, phân loại rác bảo vệ môi trường; xả rác, đốt rừng gây hại.', 'kho');
            $this->sortQ($L, 'Kéo mỗi hiện tượng vào nhóm DO BIẾN ĐỔI KHÍ HẬU hoặc KHÔNG PHẢI.',
                [['Nước biển dâng', 'Do biến đổi khí hậu'], ['Bão lũ bất thường', 'Do biến đổi khí hậu'],
                 ['Ngày đêm luân phiên', 'Không phải'], ['Thuỷ triều lên xuống', 'Không phải']],
                'Biến đổi khí hậu gây nước biển dâng, thời tiết cực đoan.', 'kho');
            $this->sortQ($L, 'Kéo mỗi nguồn năng lượng vào nhóm SẠCH hoặc GÂY Ô NHIỄM.',
                [['Năng lượng mặt trời', 'Sạch'], ['Năng lượng gió', 'Sạch'],
                 ['Than đá', 'Gây ô nhiễm'], ['Dầu mỏ', 'Gây ô nhiễm']],
                'Năng lượng tái tạo sạch; than đá, dầu mỏ gây ô nhiễm.', 'kho');
            $this->sortQ($L, 'Kéo mỗi đối tượng vào nhóm CHỊU ẢNH HƯỞNG NẶNG hoặc ÍT của nước biển dâng.',
                [['Đồng bằng sông Cửu Long', 'Chịu ảnh hưởng nặng'], ['Vùng ven biển miền Trung', 'Chịu ảnh hưởng nặng'],
                 ['Vùng núi Tây Bắc', 'Ít'], ['Cao nguyên Tây Nguyên', 'Ít']],
                'Vùng thấp ven biển chịu ảnh hưởng nặng của nước biển dâng.', 'kho');
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, 'Rừng bị thu hẹp do khai thác quá ___ và đốt nương làm rẫy.', [[0, 'mức']],
                'Cần trồng rừng và bảo vệ rừng đầu nguồn.', 'kho');
            $this->fill($L, 'Biến đổi khí hậu làm nước biển ___, đe doạ vùng ven biển.', [[0, 'dâng']],
                'Đồng bằng sông Cửu Long chịu ảnh hưởng nặng nề.', 'kho');
            $this->fill($L, 'Năng lượng mặt trời và năng lượng gió là năng lượng ___.', [[0, 'sạch']],
                'Không gây ô nhiễm, có thể tái tạo.', 'kho');
            $this->fill($L, 'Mỗi người cần tiết kiệm điện, nước và không xả ___ bừa bãi.', [[0, 'rác']],
                'Hành động nhỏ góp phần bảo vệ môi trường.', 'trung_binh');
        }
    }
}
