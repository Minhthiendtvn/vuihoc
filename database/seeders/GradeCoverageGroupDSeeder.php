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
 * Phủ dữ liệu theo khối lớp cho NHÓM D (Âm nhạc, Mỹ thuật, GDTC, Trải nghiệm & Hướng nghiệp).
 * Nội dung TỰ VIẾT 100% tiếng Việt, bám chương trình từng khối lớp, độ khó tăng dần.
 *
 * Với mỗi topic, mỗi lớp trong [grade_min..grade_max]: tạo 2 bài học mới gắn vào
 * skill đầu tiên (sort_order nhỏ nhất) của topic, slug `{topic}-lop-{grade}-1|2`.
 * Mỗi bài: 4 quiz + 4 matching + 4 sort + 4 fill = 16 câu, grade + is_demo = true.
 *
 * Idempotent: bài học bỏ qua khi slug đã tồn tại; câu hỏi bỏ qua theo (lesson_id, game_type).
 */
class GradeCoverageGroupDSeeder extends Seeder
{
    private array $lessonCache = [];

    public function run(): void
    {
        foreach ($this->lessonPlan() as $p) {
            [$topic, $grade, $idx, $method, $title, $objective, $difficulty, $duration, $instructions] = $p;
            $slug = "{$topic}-lop-{$grade}-{$idx}";
            $this->ensureLesson($topic, $slug, $grade, $title, $objective, $difficulty, $duration, $instructions);
            $this->{$method}($slug, $grade, $difficulty);
        }
    }

    /**
     * [topic_slug, grade, index, seed_method, title, objective, difficulty, duration_minutes, instructions]
     */
    private function lessonPlan(): array
    {
        return [
            // ---------- ÂM NHẠC: nốt nhạc & cao độ (6-7) ----------
            ['an-not-nhac-cao-do', 6, 1, 'seedAnCaoDo61',
                'Nốt nhạc cơ bản lớp 6: bảy nốt nhạc Đồ – Si (1)',
                'Kể được tên 7 nốt nhạc, nhận biết hình nốt tròn – trắng – đen – móc đơn.',
                'de', 12,
                'Có 7 nốt nhạc: Đồ (C), Rê (D), Mi (E), Pha (F), Son (G), La (A), Si (B). Hình nốt cho biết trường độ: nốt tròn dài nhất, rồi đến nốt trắng, nốt đen, nốt móc đơn ngắn hơn.'],
            ['an-not-nhac-cao-do', 6, 2, 'seedAnCaoDo62',
                'Khuông nhạc và khóa Son lớp 6: đọc tên nốt trên dòng kẻ (2)',
                'Biết khuông nhạc 5 dòng 4 khe, khóa Son; đọc được tên nốt trên dòng kẻ và trong khe.',
                'de', 12,
                'Khuông nhạc có 5 dòng kẻ và 4 khe. Khóa Son đặt ở đầu khuông nhạc cho biết vị trí nốt Son nằm trên dòng kẻ thứ hai. Nốt nhạc được viết trên dòng kẻ hoặc trong khe nhạc.'],
            ['an-not-nhac-cao-do', 7, 1, 'seedAnCaoDo71',
                'Cao độ và quãng lớp 7: quãng 2, 3, 4, 5, 8 (1)',
                'Hiểu quãng là khoảng cách cao độ; xác định được quãng 2, 3, 4, 5, 8 từ nốt Đồ.',
                'de', 13,
                'Quãng là khoảng cách cao độ giữa hai nốt nhạc. Từ nốt Đồ: Đồ–Rê là quãng 2, Đồ–Mi là quãng 3, Đồ–Pha là quãng 4, Đồ–Son là quãng 5, Đồ–Đồ (cao hơn) là quãng 8 còn gọi là bát độ.'],
            ['an-not-nhac-cao-do', 7, 2, 'seedAnCaoDo72',
                'Dấu hóa và âm giai lớp 7: thăng, giáng, bình và âm giai Đô trưởng (2)',
                'Biết tác dụng dấu thăng – giáng – bình; đọc được âm giai Đô trưởng 8 nốt.',
                'trung_binh', 13,
                'Dấu thăng (#) nâng nốt lên nửa cung, dấu giáng (b) hạ nốt xuống nửa cung, dấu bình hủy tác dụng thăng giáng. Âm giai Đô trưởng gồm 8 nốt: Đồ Rê Mi Pha Son La Si (Đồ cao). Trong đó Mi–Pha và Si–Đồ cách nhau nửa cung.'],

            // ---------- ÂM NHẠC: nhịp & dấu lặng (7-8) ----------
            ['an-nhip-dau-lang', 7, 1, 'seedAnNhip71',
                'Nhịp và dấu lặng lớp 7: nhịp 2/4, 3/4 (1)',
                'Hiểu số chỉ nhịp; phân biệt nhịp 2/4 – 3/4; nhận biết dấu lặng tròn – trắng – đen.',
                'de', 12,
                'Số chỉ nhịp gồm hai số: số trên cho biết mỗi ô nhịp có mấy phách, số dưới cho biết giá trị mỗi phách (4 = nốt đen). Nhịp 2/4: mỗi ô 2 phách; nhịp 3/4: mỗi ô 3 phách. Dấu lặng là ký hiệu nghỉ: lặng tròn nghỉ 4 phách, lặng trắng 2 phách, lặng đen 1 phách.'],
            ['an-nhip-dau-lang', 7, 2, 'seedAnNhip72',
                'Nhịp 4/4 và dấu chấm dôi lớp 7: đọc bản nhạc đơn giản (2)',
                'Biết nhịp 4/4 (ký hiệu C); tính được giá trị nốt có dấu chấm dôi.',
                'trung_binh', 13,
                'Nhịp 4/4 (ký hiệu C): mỗi ô nhịp 4 phách đen, phách 1 mạnh nhất, phách 3 mạnh vừa. Dấu chấm dôi đặt sau nốt hoặc dấu lặng làm tăng thêm một nửa giá trị: nốt đen chấm dôi dài 1,5 phách, nốt trắng chấm dôi dài 3 phách.'],
            ['an-nhip-dau-lang', 8, 1, 'seedAnNhip81',
                'Nhịp đơn, nhịp kép lớp 8: nhịp 6/8 và phân loại (1)',
                'Phân biệt nhịp đơn – nhịp kép; biết nhịp 6/8 có 2 phách, mỗi phách bằng 3 nốt móc đơn.',
                'trung_binh', 13,
                'Nhịp đơn: mỗi phách chia thành 2 phần bằng nhau (2/4, 3/4, 4/4). Nhịp kép: mỗi phách chia thành 3 phần bằng nhau (6/8, 9/8, 12/8). Nhịp 6/8 có 2 phách, mỗi phách bằng một nốt đen chấm dôi, tức 3 nốt móc đơn.'],
            ['an-nhip-dau-lang', 8, 2, 'seedAnNhip82',
                'Trường độ và đếm phách lớp 8: thực hành tổng hợp (2)',
                'Tính nhanh giá trị các hình nốt (kể cả móc kép); biết đếm phách và dấu diễn cảm cơ bản.',
                'trung_binh', 14,
                'Khi đơn vị phách là nốt đen: nốt tròn = 4, nốt trắng = 2, nốt đen = 1, nốt móc đơn = 1/2, nốt móc kép = 1/4 phách. Đếm phách đều ("1 và 2 và...") giúp giữ đúng nhịp. Dấu legato: hát liền; dấu staccato: hát nảy ngắn gọn.'],

            // ---------- ÂM NHẠC: nhạc cụ dân tộc (8-9) ----------
            ['an-nhac-cu-dan-toc', 8, 1, 'seedAnNhacCu81',
                'Nhạc cụ dân tộc lớp 8: nhóm dây – gảy, dây – kéo (1)',
                'Kể được đàn bầu, đàn tranh, đàn nguyệt, đàn nhị; phân biệt dây gảy và dây kéo.',
                'trung_binh', 13,
                'Nhạc cụ dây gảy: đàn bầu (1 dây, độc đáo nhất), đàn tranh (16 dây), đàn nguyệt (2 dây, mặt tròn), đàn tỳ bà (4 dây). Nhạc cụ dây kéo: đàn nhị (đàn cò) dùng vĩ kéo, âm thanh gần giọng người.'],
            ['an-nhac-cu-dan-toc', 8, 2, 'seedAnNhacCu82',
                'Nhạc cụ dân tộc lớp 8: nhóm hơi và nhóm gõ (2)',
                'Kể được sáo, kèn bầu, trống cơm, phách, cồng chiêng; biết cồng chiêng Tây Nguyên là di sản UNESCO.',
                'trung_binh', 13,
                'Nhạc cụ hơi: sáo trúc, tiêu, kèn bầu, tù và — dùng hơi thổi. Nhạc cụ gõ: trống cơm, trống chầu, phách, sanh tiền, đàn đá — dùng dùi hoặc tay gõ. Không gian văn hóa cồng chiêng Tây Nguyên là di sản văn hóa phi vật thể của nhân loại.'],
            ['an-nhac-cu-dan-toc', 9, 1, 'seedAnNhacCu91',
                'Nghệ thuật truyền thống lớp 9: chèo, quan họ, ca trù (1)',
                'Biết quan họ Bắc Ninh, chèo, ca trù, đờn ca tài tử; nêu được di sản UNESCO của Việt Nam.',
                'trung_binh', 13,
                'Quan họ Bắc Ninh: hát giao duyên, di sản UNESCO 2009. Chèo: sân khấu dân gian đồng bằng Bắc Bộ. Ca trù (hát ả đào): hát nói, hát thờ. Đờn ca tài tử Nam Bộ: di sản UNESCO 2013. Xẩm là hát của người khiếm thị.'],
            ['an-nhac-cu-dan-toc', 9, 2, 'seedAnNhacCu92',
                'Nhạc cụ phương Tây và dàn nhạc lớp 9: so sánh Đông – Tây (2)',
                'Kể được violin, piano, trumpet, flute; phân biệt dàn nhạc giao hưởng và dàn nhạc dân tộc.',
                'kho', 14,
                'Nhạc cụ phương Tây: violin (dây kéo), piano (phím – búa gõ dây), trumpet (đồng thổi), flute (sáo ngang). Dàn nhạc giao hưởng khoảng 100 nhạc công do nhạc trưởng chỉ huy. Dàn nhạc dân tộc Việt Nam gồm đàn tranh, đàn bầu, sáo, đàn nhị, trống...'],

            // ---------- MỸ THUẬT: màu sắc (6-7) ----------
            ['mt-mau-sac', 6, 1, 'seedMtMau61',
                'Màu sắc lớp 6: ba màu cơ bản và pha màu (1)',
                'Kể được 3 màu cơ bản; pha được màu thứ cấp cam – lục – tím.',
                'de', 12,
                'Ba màu cơ bản: đỏ, vàng, lam — không pha được từ màu khác. Pha hai màu cơ bản được màu thứ cấp: đỏ + vàng = cam, vàng + lam = lục (xanh lá), lam + đỏ = tím.'],
            ['mt-mau-sac', 6, 2, 'seedMtMau62',
                'Màu nóng – màu lạnh lớp 6: cảm xúc của màu sắc (2)',
                'Phân biệt gam màu nóng – lạnh; biết màu sắc gợi cảm xúc khác nhau.',
                'de', 12,
                'Màu nóng: đỏ, cam, vàng — gợi cảm giác ấm áp, sôi động. Màu lạnh: lam, lục, tím — gợi cảm giác mát mẻ, yên tĩnh. Người vẽ chọn gam màu phù hợp với nội dung tranh: biển dùng gam lạnh, lửa trại dùng gam nóng.'],
            ['mt-mau-sac', 7, 1, 'seedMtMau71',
                'Sắc độ và hòa sắc lớp 7: đậm – nhạt, màu tương phản (1)',
                'Hiểu sắc độ; xác định được cặp màu tương phản trên vòng tròn màu.',
                'de', 13,
                'Sắc độ là độ đậm nhạt của màu: thêm trắng thì nhạt dần, thêm đen thì đậm dần. Hai màu đối diện nhau trên vòng tròn màu gọi là màu tương phản: đỏ–lục, vàng–tím, cam–lam. Đặt cạnh nhau, màu tương phản làm nhau nổi bật.'],
            ['mt-mau-sac', 7, 2, 'seedMtMau72',
                'Màu chủ đạo trong tranh lớp 7: bố cục màu (2)',
                'Biết màu chủ đạo, điểm nhấn màu; biết màu tự nhiên trong tranh Đông Hồ.',
                'trung_binh', 13,
                'Màu chủ đạo chiếm diện tích lớn, quyết định cảm xúc chung của tranh. Điểm nhấn màu là mảng màu tương phản nhỏ thu hút mắt nhìn. Tranh dân gian Đông Hồ dùng màu tự nhiên: đen từ than, đỏ từ gạch non, vàng từ hoa hòe, trên giấy điệp làm từ vỏ sò.'],

            // ---------- MỸ THUẬT: đường nét & bố cục (7-8) ----------
            ['mt-duong-net-bo-cuc', 7, 1, 'seedMtNet71',
                'Đường nét lớp 7: các loại nét và cảm xúc (1)',
                'Phân biệt nét thẳng – cong – gấp khúc – đứt; biết nét trong vẽ kỹ thuật.',
                'de', 12,
                'Nét thẳng gợi vững chãi, nét cong gợi mềm mại uyển chuyển, nét gấp khúc gợi mạnh mẽ gấp gáp, nét đứt gợi nhẹ nhàng. Trong vẽ kỹ thuật: nét liền đậm vẽ đường bao thấy được, nét đứt vẽ đường bị khuất, nét chấm gạch vẽ trục đối xứng.'],
            ['mt-duong-net-bo-cuc', 7, 2, 'seedMtNet72',
                'Bố cục lớp 7: đối xứng, cân bằng và điểm nhấn (2)',
                'Hiểu bố cục, bố cục đối xứng, điểm nhấn; biết đặt đường chân trời ở 1/3 tranh.',
                'trung_binh', 13,
                'Bố cục là cách sắp xếp các hình mảng trong tranh. Bố cục đối xứng: hai bên giống nhau qua một trục, tạo cảm giác trang nghiêm. Điểm nhấn là vị trí thu hút mắt nhìn nhất, thường đặt ở khoảng 1/3 tranh. Các mảng nặng – nhẹ cần phân bố cân bằng.'],
            ['mt-duong-net-bo-cuc', 8, 1, 'seedMtNet81',
                'Hình khối và không gian lớp 8: vẽ theo mẫu (1)',
                'Nhận biết khối cầu – trụ – hộp – nón; hiểu phối cảnh gần to xa nhỏ và sáng – tối.',
                'trung_binh', 13,
                'Mọi vật thể đều gồm các khối cơ bản: khối cầu (quả cam), khối trụ (lon sữa), khối hộp (hộp quà), khối nón (nón lá). Phối cảnh: vật gần vẽ to, vật xa vẽ nhỏ. Ánh sáng tạo ba phần: phần sáng hướng về nguồn sáng, phần tối quay lưng lại, bóng đổ in trên mặt đất phía đối diện nguồn sáng.'],
            ['mt-duong-net-bo-cuc', 8, 2, 'seedMtNet82',
                'Trang trí và họa tiết lớp 8: hoa văn dân tộc (2)',
                'Biết họa tiết trang trí lặp lại có quy luật; nhận biết hoa văn trống đồng Đông Sơn.',
                'trung_binh', 13,
                'Họa tiết trang trí được lặp lại có quy luật tạo nhịp điệu: họa tiết hình học, hoa lá cách điệu, con vật. Trống đồng Đông Sơn có hoa văn chim Lạc và ngôi sao nhiều cánh ở trung tâm mặt trống — biểu tượng văn hóa Đông Sơn.'],

            // ---------- MỸ THUẬT: tranh dân gian (8-9) ----------
            ['mt-tranh-dan-gian', 8, 1, 'seedMtTranh81',
                'Tranh Đông Hồ lớp 8: làng nghề và đặc điểm (1)',
                'Biết làng Đông Hồ (Bắc Ninh), giấy điệp, màu tự nhiên; kể được tranh Đám cưới chuột.',
                'trung_binh', 13,
                'Làng Đông Hồ (Thuận Thành, Bắc Ninh) làm tranh khắc ván. Giấy điệp quét từ vỏ sò điệp giã nhỏ, óng ánh. Màu tự nhiên: đen từ than, đỏ từ gạch non, vàng từ hoa hòe. Chủ đề: chúc tụng (Vinh hoa Phú quý), sinh hoạt (Đám cưới chuột), lịch sử.'],
            ['mt-tranh-dan-gian', 8, 2, 'seedMtTranh82',
                'Tranh Hàng Trống và Kim Hoàng lớp 8: nét riêng (2)',
                'Phân biệt Hàng Trống (in ván + vẽ tay), Kim Hoàng (giấy hồng điều), Đông Hồ.',
                'trung_binh', 13,
                'Tranh Hàng Trống (phố Hàng Trống, Hà Nội): in ván nét đen rồi tô màu bằng tay, nổi tiếng về tranh thờ (Ngũ hổ) và tranh Tố nữ. Tranh Kim Hoàng (Hoài Đức, Hà Nội): in trên giấy hồng điều đỏ, chủ yếu là tranh Tết.'],
            ['mt-tranh-dan-gian', 9, 1, 'seedMtTranh91',
                'Tranh dân gian các vùng miền lớp 9: Làng Sình, tranh kính (1)',
                'Biết tranh Làng Sình (Huế, tranh thờ), tranh kính Nam Bộ; phân biệt vùng miền.',
                'trung_binh', 14,
                'Tranh Làng Sình (Huế): in ván rồi tô màu, chủ yếu dùng thờ cúng (Lý ngư vọng nguyệt). Tranh kính Nam Bộ: vẽ trực tiếp trên tờ kính, chủ đề Bát tiên, Tứ quý. Mỗi vùng miền có dòng tranh riêng: Bắc – Đông Hồ, Hàng Trống; Trung – Làng Sình; Nam – tranh kính.'],
            ['mt-tranh-dan-gian', 9, 2, 'seedMtTranh92',
                'Bảo tồn tranh dân gian lớp 9: nghệ nhân và phát huy (2)',
                'Nêu được nguy cơ mai một; đề xuất cách bảo tồn và ứng dụng hiện đại.',
                'kho', 14,
                'Nhiều làng nghề tranh đứng trước nguy cơ mai một vì nghệ nhân cao tuổi, ít người kế nghiệp. Bảo tồn bằng: truyền nghề cho thế hệ trẻ, đưa vào trường học, phát triển du lịch làng nghề, số hóa lưu trữ, ứng dụng họa tiết dân gian vào thời trang và thiết kế hiện đại.'],

            // ---------- GDTC: vận động an toàn (6-7) ----------
            ['tt-van-dong-an-toan', 6, 1, 'seedTtAnToan61',
                'Khởi động và an toàn lớp 6: nguyên tắc vận động (1)',
                'Biết khởi động 5-10 phút; mang giày thể thao; kiểm tra sân bãi trước khi chơi.',
                'de', 10,
                'Trước khi vận động phải khởi động 5-10 phút: xoay các khớp, chạy nhẹ tại chỗ để làm nóng cơ, tránh chấn thương. Mang giày thể thao vừa chân, trang phục thoải mái. Kiểm tra sân bãi khô ráo, dụng cụ chắc chắn; uống đủ nước.'],
            ['tt-van-dong-an-toan', 6, 2, 'seedTtAnToan62',
                'Tư thế vận động đúng lớp 6: chạy, nhảy, thở (2)',
                'Thực hiện đúng tư thế chạy, cách tiếp đất khi nhảy, cách thở khi vận động.',
                'de', 11,
                'Chạy đúng: người hơi nghiêng về trước, mắt nhìn thẳng, hai tay đánh nhịp theo bước chân. Nhảy xuống: tiếp đất bằng cả bàn chân, gối khụy để giảm chấn động. Khi vận động mạnh: hít vào bằng mũi, thở ra bằng miệng, thở đều không nín thở.'],
            ['tt-van-dong-an-toan', 7, 1, 'seedTtAnToan71',
                'Chấn thương thể thao lớp 7: phòng tránh và sơ cứu (1)',
                'Nhận biết chuột rút, bong gân, trật khớp; biết sơ cứu RICE.',
                'de', 12,
                'Chấn thương thường gặp: chuột rút (cơ co cứng đột ngột), bong gân, trật khớp, trầy xước. Sơ cứu theo RICE: Rest (nghỉ ngơi), Ice (chườm lạnh trong 48 giờ đầu), Compression (băng ép), Elevation (kê cao). Tuyệt đối không tự nắn khớp bị trật — cố định và đưa đến cơ sở y tế.'],
            ['tt-van-dong-an-toan', 7, 2, 'seedTtAnToan72',
                'Tố chất thể lực lớp 7: sức nhanh, mạnh, bền, dẻo (2)',
                'Phân biệt sức nhanh – mạnh – bền – dẻo; chọn bài tập phù hợp từng tố chất.',
                'trung_binh', 12,
                'Các tố chất thể lực: sức nhanh (chạy 60m), sức mạnh (chống đẩy, bật xa), sức bền (chạy 800-1000m), sự dẻo dai (ép dẻo), sự khéo léo (đá cầu). Tập luyện phải tăng dần cường độ, đúng kỹ thuật, đều đặn — tập quá sức dễ chấn thương.'],

            // ---------- GDTC: luật thể thao (7-8) ----------
            ['tt-luat-the-thao', 7, 1, 'seedTtLuat71',
                'Luật bóng đá cơ bản lớp 7: sân, việt vị, phạt đền (1)',
                'Biết mỗi đội 11 người, luật việt vị, phạt đền 11m, thẻ vàng – thẻ đỏ.',
                'de', 12,
                'Mỗi đội 11 cầu thủ. Việt vị: cầu thủ tấn công đứng gần khung thành đối phương hơn bóng và cầu thủ thứ hai cuối cùng khi đồng đội chuyền bóng. Phạm lỗi trong vòng cấm bị phạt đền: đá từ chấm 11m. Thẻ vàng cảnh cáo, thẻ đỏ truất quyền thi đấu.'],
            ['tt-luat-the-thao', 7, 2, 'seedTtLuat72',
                'Luật bóng rổ cơ bản lớp 7: dẫn bóng, bước chạy (2)',
                'Biết cách dẫn bóng, lỗi chạy bước, cách tính điểm 1 – 2 – 3 điểm.',
                'trung_binh', 12,
                'Dẫn bóng bằng một tay (mỗi lần chạm một tay), không được cầm bóng chạy quá số bước quy định (lỗi chạy bước), không dẫn bóng lại sau khi đã dừng (lỗi hai lần dẫn). Ném trong vòng 2 điểm, ném phạt 1 điểm, ném ngoài vạch 3 điểm được 3 điểm. Rổ cao 3,05m.'],
            ['tt-luat-the-thao', 8, 1, 'seedTtLuat81',
                'Luật bóng chuyền lớp 8: đội hình và lỗi (1)',
                'Biết mỗi đội 6 người, tối đa 3 lần chạm bóng; nhận biết lỗi chạm lưới.',
                'trung_binh', 13,
                'Mỗi đội 6 người trên sân. Mỗi đội được chạm bóng tối đa 3 lần trước khi đưa sang sân đối phương. Lỗi thường gặp: chạm lưới, bóng ra ngoài, dẫm vạch khi phát bóng. Thắng 3 trong 5 set; set thường đến 25 điểm (cách 2 điểm), set 5 đến 15 điểm. Libero là cầu thủ chuyên phòng thủ mặc áo khác màu.'],
            ['tt-luat-the-thao', 8, 2, 'seedTtLuat82',
                'Luật cầu lông lớp 8: tính điểm và giao cầu (2)',
                'Biết tính điểm rally đến 21, giao cầu chéo sân, thắng 2/3 set.',
                'trung_binh', 13,
                'Tính điểm rally: mỗi pha bóng đều có điểm. Thắng 1 set khi đạt 21 điểm và hơn đối phương 2 điểm (tối đa 30 điểm). Giao cầu phải phát sang ô chéo sân, chân không dẫm vạch. Thắng trận khi thắng 2 trong 3 set. Sân đơn hẹp hơn sân đôi; lưới cao 1,55m ở biên.'],

            // ---------- GDTC: sức khỏe & vệ sinh (8-9) ----------
            ['tt-suc-khoe-ve-sinh', 8, 1, 'seedTtSk81',
                'Dinh dưỡng cho người vận động lớp 8: nhóm chất (1)',
                'Kể được 4 nhóm chất; biết ăn uống – bổ sung nước đúng khi tập luyện.',
                'trung_binh', 13,
                'Bốn nhóm chất: bột đường (cơm, bánh mì — năng lượng chính), đạm (thịt, trứng, sữa — xây dựng cơ bắp), béo (dầu, mỡ — dự trữ năng lượng), vitamin – khoáng chất – chất xơ (rau, trái cây). Ăn nhẹ trước tập 1-2 giờ, uống nước từng ngụm, không nhịn khát.'],
            ['tt-suc-khoe-ve-sinh', 8, 2, 'seedTtSk82',
                'Vệ sinh trong tập luyện lớp 8: cá nhân và môi trường (2)',
                'Biết vệ sinh cá nhân sau tập; giữ sân bãi, dụng cụ sạch sẽ; không dùng chung đồ cá nhân.',
                'trung_binh', 13,
                'Sau khi tập: tắm rửa, thay quần áo khô để tránh cảm lạnh và viêm da. Không dùng chung khăn mặt, bình nước để tránh lây bệnh ngoài da. Giữ sân bãi sạch: bỏ rác đúng nơi, không khạc nhổ bừa bãi; vệ sinh dụng cụ tập chung và báo ngay khi dụng cụ hỏng.'],
            ['tt-suc-khoe-ve-sinh', 9, 1, 'seedTtSk91',
                'Tim mạch và hô hấp khi vận động lớp 9: nhịp tim, nhịp thở (1)',
                'Ước tính được nhịp tim tối đa (220 – tuổi); biết thở đúng khi vận động.',
                'trung_binh', 14,
                'Nhịp tim tối đa ước tính ≈ 220 – tuổi (bạn 15 tuổi ≈ 205 nhịp/phút). Khi vận động, tim đập nhanh để bơm máu, phổi thở nhanh để lấy oxy. Vùng tập hiệu quả khoảng 60-80% nhịp tim tối đa. Thở đúng: hít sâu bằng mũi, thở nhịp nhàng — không nín thở khi chạy bền.'],
            ['tt-suc-khoe-ve-sinh', 9, 2, 'seedTtSk92',
                'Chất kích thích và thể thao lớp 9: doping, rượu bia, thuốc lá (2)',
                'Biết doping bị cấm; nêu tác hại của rượu bia, thuốc lá với thể lực.',
                'kho', 14,
                'Doping là chất kích thích bị cấm trong thể thao — vận động viên dương tính bị cấm thi đấu, tước huy chương và hại sức khỏe lâu dài. Rượu bia làm giảm phản xạ, hại gan; thuốc lá hại phổi, giảm sức bền. Người chơi thể thao phải nói không với mọi chất kích thích, thi đấu trung thực.'],

            // ---------- TNHN: kỹ năng sống (6-7) ----------
            ['tnhn-ky-nang-song', 6, 1, 'seedTnhnKn61',
                'Quản lý thời gian lớp 6: thời gian biểu học tập (1)',
                'Biết lập thời gian biểu; ưu tiên việc quan trọng; học 25-30 phút nghỉ 5 phút.',
                'de', 10,
                'Lập thời gian biểu: giờ học, giờ chơi, giờ ngủ rõ ràng. Việc quan trọng và gấp làm trước. Học tập trung 25-30 phút rồi nghỉ 5 phút để não hồi phục. Ngủ đủ 8-10 tiếng mỗi đêm, không thức khuya — thiếu ngủ làm giảm trí nhớ.'],
            ['tnhn-ky-nang-song', 6, 2, 'seedTnhnKn62',
                'Giao tiếp và làm việc nhóm lớp 6: lắng nghe, chia sẻ (2)',
                'Biết lắng nghe không ngắt lời; phân công khi làm việc nhóm; nói năng lịch sự.',
                'de', 11,
                'Lắng nghe tích cực: nhìn người nói, không ngắt lời, gật đầu đồng cảm. Nói rõ ràng, dùng lời lẽ lịch sự, biết cảm ơn và xin lỗi. Làm việc nhóm: phân công rõ ràng (nhóm trưởng, thư ký, thành viên), tôn trọng ý kiến bạn, giúp đỡ nhau, cùng chịu trách nhiệm.'],
            ['tnhn-ky-nang-song', 7, 1, 'seedTnhnKn71',
                'Quản lý cảm xúc lớp 7: nhận diện và kiểm soát (1)',
                'Nhận diện cảm xúc; biết cách bình tĩnh khi giận: hít thở sâu, đếm 1-10.',
                'de', 12,
                'Cảm xúc có tích cực (vui, yêu thương) và tiêu cực (giận, buồn, sợ) — cảm xúc nào cũng cần được nhận diện, không nên giấu kín. Khi giận: dừng lại, hít thở sâu, đếm từ 1 đến 10, chỉ nói chuyện khi đã bình tĩnh. Tuyệt đối không làm tổn thương người khác hay đập phá.'],
            ['tnhn-ky-nang-song', 7, 2, 'seedTnhnKn72',
                'Giải quyết vấn đề lớp 7: 5 bước xử lý tình huống (2)',
                'Thực hiện 5 bước: xác định – tìm nguyên nhân – liệt kê giải pháp – chọn – đánh giá.',
                'trung_binh', 12,
                '5 bước giải quyết vấn đề: 1. Xác định vấn đề là gì; 2. Tìm hiểu nguyên nhân; 3. Liệt kê các giải pháp có thể; 4. Chọn giải pháp tốt nhất; 5. Thực hiện rồi đánh giá kết quả. Gặp vấn đề vượt khả năng (bị bắt nạt, thấy cháy...) phải nhờ người lớn giúp đỡ.'],

            // ---------- TNHN: an toàn & môi trường (7-8) ----------
            ['tnhn-an-toan-moi-truong', 7, 1, 'seedTnhnAt71',
                'An toàn giao thông lớp 7: quy tắc đường bộ (1)',
                'Biết đội mũ bảo hiểm, đi bên phải, qua đường ở vạch kẻ; nhận biết biển cấm – nguy hiểm.',
                'de', 12,
                'Đội mũ bảo hiểm khi đi xe máy, xe đạp điện. Đi bên phải đường, không dàn hàng ngang, không lạng lách. Qua đường ở vạch kẻ dành cho người đi bộ, nhìn trước nhìn sau. Biển tròn viền đỏ là biển cấm; biển tam giác viền đỏ là biển nguy hiểm; đèn đỏ phải dừng lại.'],
            ['tnhn-an-toan-moi-truong', 7, 2, 'seedTnhnAt72',
                'Phòng cháy chữa cháy lớp 7: thoát hiểm (2)',
                'Nhớ số 114; biết thoát hiểm cúi thấp, đi thang bộ; cách dùng bình chữa cháy.',
                'trung_binh', 12,
                'Số điện thoại cứu hỏa là 114. Khi cháy: hô hoán báo động, gọi 114, thoát hiểm bằng thang bộ — cúi thấp người tránh khói, dùng khăn ướt che mũi, tuyệt đối không dùng thang máy, không quay lại lấy đồ. Quần áo bén lửa: nằm lăn để dập. Dùng bình chữa cháy: rút chốt, hướng vòi vào gốc lửa, bóp cò.'],
            ['tnhn-an-toan-moi-truong', 8, 1, 'seedTnhnAt81',
                'Bảo vệ môi trường lớp 8: rác thải và tái chế (1)',
                'Phân loại rác hữu cơ – tái chế – nguy hại; hiểu 3R; biết tác hại túi nilon.',
                'trung_binh', 13,
                'Phân loại rác: hữu cơ (vỏ rau củ — ủ làm phân), tái chế (chai nhựa, giấy), nguy hại (pin cũ — thu gom riêng). 3R: Reduce (giảm thiểu), Reuse (tái sử dụng), Recycle (tái chế). Túi nilon mất hàng trăm năm mới phân hủy — hãy mang túi vải đi chợ, dùng bình nước cá nhân.'],
            ['tnhn-an-toan-moi-truong', 8, 2, 'seedTnhnAt82',
                'An toàn trên mạng lớp 8: bảo mật thông tin (2)',
                'Không chia sẻ thông tin cá nhân; đặt mật khẩu mạnh; cảnh giác lừa đảo, bắt nạt mạng.',
                'trung_binh', 13,
                'Không chia sẻ công khai địa chỉ nhà, số điện thoại, trường lớp, ảnh thẻ với người lạ. Mật khẩu mạnh: dài, gồm chữ hoa, chữ thường, số và ký tự đặc biệt; bật xác thực 2 lớp. Tin nhắn "trúng thưởng, bấm link" là lừa đảo — không bấm. Bị bắt nạt trên mạng: chặn, báo cáo và báo ngay cho người lớn.'],

            // ---------- TNHN: định hướng nghề nghiệp (8-9) ----------
            ['tnhn-dinh-huong-nghe-nghiep', 8, 1, 'seedTnhnNghe81',
                'Tìm hiểu nghề nghiệp lớp 8: các nhóm nghề cơ bản (1)',
                'Kể được các nhóm nghề y tế, giáo dục, kỹ thuật, công nghệ, nghệ thuật; biết công việc đặc trưng.',
                'trung_binh', 13,
                'Các nhóm nghề cơ bản: y tế (bác sĩ khám chữa bệnh), giáo dục (giáo viên dạy học), kỹ thuật (kỹ sư thiết kế), công nghệ thông tin (lập trình viên viết phần mềm), nông nghiệp (nông dân trồng trọt), nghệ thuật (họa sĩ, ca sĩ), kinh doanh, pháp luật (luật sư). Mọi nghề chân chính đều đáng tôn trọng.'],
            ['tnhn-dinh-huong-nghe-nghiep', 8, 2, 'seedTnhnNghe82',
                'Khám phá bản thân lớp 8: sở thích và năng lực (2)',
                'Phân biệt sở thích – điểm mạnh – điểm yếu; biết chọn nghề theo năng lực, không theo phong trào.',
                'trung_binh', 13,
                'Hiểu bản thân qua 4 yếu tố: sở thích (điều mình yêu thích), điểm mạnh (điều mình làm tốt), điểm yếu (điều cần cải thiện), giá trị (điều mình coi trọng). Chọn nghề phù hợp năng lực và đam mê — không chạy theo phong trào hay vì bạn bè chọn. Phát huy điểm mạnh, rèn luyện điểm yếu từng ngày.'],
            ['tnhn-dinh-huong-nghe-nghiep', 9, 1, 'seedTnhnNghe91',
                'Chọn trường, chọn ngành lớp 9: hướng đi sau THCS (1)',
                'Biết các hướng đi sau lớp 9: THPT, trung cấp nghề; căn cứ chọn theo năng lực, gia đình.',
                'trung_binh', 14,
                'Sau tốt nghiệp THCS có nhiều hướng đi: thi vào THPT (học văn hóa 3 năm), học trung cấp nghề (học nghề cụ thể như điện, may, nấu ăn), hoặc vừa học văn hóa vừa học nghề. Sau THPT: đại học, cao đẳng, trường nghề hoặc đi làm. Chọn theo năng lực, sở thích, điều kiện gia đình và nhu cầu xã hội — học nghề cũng là hướng đi tốt.'],
            ['tnhn-dinh-huong-nghe-nghiep', 9, 2, 'seedTnhnNghe92',
                'Nghề nghiệp tương lai lớp 9: kỹ năng số và học suốt đời (2)',
                'Biết AI thay đổi nghề nghiệp; nêu kỹ năng tương lai: công nghệ số, ngoại ngữ, sáng tạo.',
                'kho', 14,
                'Trí tuệ nhân tạo (AI) và tự động hóa đang thay đổi thị trường lao động: nghề lặp đi lặp lại đơn giản dễ bị thay thế; nghề mới ra đời (kỹ sư AI, phân tích dữ liệu). Nghề cần sáng tạo, cảm xúc, chăm sóc con người khó bị thay thế. Hành trang tương lai: kỹ năng số, ngoại ngữ, tư duy sáng tạo và tinh thần học suốt đời.'],
        ];
    }

    // ---------------- helpers ----------------

    private function ensureLesson(string $topicSlug, string $slug, int $grade, string $title, string $objective, string $difficulty, int $duration, string $instructions): void
    {
        if (Lesson::where('slug', $slug)->exists()) {
            return;
        }
        $topic = Topic::where('slug', $topicSlug)->firstOrFail();
        $skill = $topic->skills()->orderBy('sort_order')->firstOrFail();
        Lesson::create([
            'skill_id'         => $skill->id,
            'slug'             => $slug,
            'title'            => $title,
            'objective'        => $objective,
            'difficulty'       => $difficulty,
            'duration_minutes' => $duration,
            'instructions'     => $instructions,
            'sort_order'       => (int) ($skill->lessons()->max('sort_order') ?? 0) + 1,
            'status'           => 'published',
            'grade'            => $grade,
            'is_demo'          => true,
        ]);
    }

    private function lesson(string $slug): Lesson
    {
        if (! isset($this->lessonCache[$slug])) {
            $this->lessonCache[$slug] = Lesson::where('slug', $slug)->firstOrFail();
        }
        return $this->lessonCache[$slug];
    }

    private function seeded(string $lessonSlug, string $type): bool
    {
        return Question::where('lesson_id', $this->lesson($lessonSlug)->id)
            ->where('game_type', $type)->exists();
    }

    private function newQuestion(string $lessonSlug, string $gameType, string $prompt, string $explanation, string $difficulty, int $grade, int $points = 10): Question
    {
        $lesson = $this->lesson($lessonSlug);
        return Question::create([
            'lesson_id'   => $lesson->id,
            'game_type'   => $gameType,
            'prompt'      => $prompt,
            'explanation' => $explanation,
            'difficulty'  => $difficulty,
            'points'      => $points,
            'sort_order'  => (int) (Question::where('lesson_id', $lesson->id)->max('sort_order') ?? 0) + 1,
            'grade'       => $grade,
            'is_demo'     => true,
        ]);
    }

    private function quiz(string $lesson, int $grade, string $prompt, array $options, int $correct, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'quiz', $prompt, $explanation, $difficulty, $grade);
        foreach ($options as $i => $text) {
            QuestionOption::create([
                'question_id' => $q->id, 'option_text' => $text,
                'is_correct' => $i === $correct, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function matching(string $lesson, int $grade, string $prompt, array $pairs, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'matching', $prompt, $explanation, $difficulty, $grade);
        foreach ($pairs as $i => [$left, $right]) {
            MatchingPair::create([
                'question_id' => $q->id, 'left_text' => $left, 'right_text' => $right, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function sortQ(string $lesson, int $grade, string $prompt, array $items, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'sort', $prompt, $explanation, $difficulty, $grade);
        foreach ($items as $i => [$text, $category]) {
            SortItem::create([
                'question_id' => $q->id, 'item_text' => $text, 'category' => $category, 'sort_order' => $i + 1,
            ]);
        }
    }

    private function fill(string $lesson, int $grade, string $prompt, array $answers, string $explanation, string $difficulty = 'de'): void
    {
        $q = $this->newQuestion($lesson, 'fill', $prompt, $explanation, $difficulty, $grade);
        foreach ($answers as $i => [$blankIndex, $text]) {
            FillAnswer::create([
                'question_id' => $q->id, 'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1,
            ]);
        }
    }

    // ================= ÂM NHẠC: nốt nhạc & cao độ =================

    private function seedAnCaoDo61(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Có tất cả bao nhiêu nốt nhạc cơ bản?',
                ['7 nốt', '5 nốt', '6 nốt', '8 nốt'], 0,
                'Hệ thống âm nhạc dùng 7 nốt nhạc cơ bản: Đồ, Rê, Mi, Pha, Son, La, Si.', $d);
            $this->quiz($L, $g, 'Nốt nhạc đầu tiên trong 7 nốt nhạc là nốt nào?',
                ['Nốt Đồ', 'Nốt Rê', 'Nốt Mi', 'Nốt Son'], 0,
                'Thứ tự 7 nốt nhạc bắt đầu từ nốt Đồ: Đồ – Rê – Mi – Pha – Son – La – Si.', $d);
            $this->quiz($L, $g, 'Nốt "Son" tương ứng với chữ cái nào trong ký hiệu quốc tế?',
                ['Chữ G', 'Chữ S', 'Chữ N', 'Chữ L'], 0,
                'Ký hiệu quốc tế: Đồ = C, Rê = D, Mi = E, Pha = F, Son = G, La = A, Si = B.', $d);
            $this->quiz($L, $g, 'Hình nốt nào có trường độ dài nhất?',
                ['Nốt tròn', 'Nốt trắng', 'Nốt đen', 'Nốt móc đơn'], 0,
                'Nốt tròn có trường độ dài nhất, bằng 2 nốt trắng, 4 nốt đen, 8 nốt móc đơn.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi nốt nhạc với ký hiệu quốc tế của nó.',
                [['Nốt Đồ', 'Chữ C'], ['Nốt Rê', 'Chữ D'], ['Nốt Mi', 'Chữ E'], ['Nốt Son', 'Chữ G']],
                'Đồ = C, Rê = D, Mi = E, Pha = F, Son = G, La = A, Si = B.', $d);
            $this->matching($L, $g, 'Nối mỗi hình vẽ với tên hình nốt.',
                [['Hình bầu dục rỗng, không cán', 'Nốt tròn'],
                 ['Hình bầu dục rỗng, có cán', 'Nốt trắng'],
                 ['Hình bầu dục đặc, có cán', 'Nốt đen'],
                 ['Nốt đen có thêm đuôi cong', 'Nốt móc đơn']],
                'Nốt tròn rỗng không cán; nốt trắng rỗng có cán; nốt đen đặc có cán; nốt móc đơn có thêm đuôi.', $d);
            $this->matching($L, $g, 'Nối mỗi nốt nhạc với vị trí thứ tự của nó.',
                [['Nốt Đồ', 'Nốt thứ nhất'], ['Nốt Mi', 'Nốt thứ ba'],
                 ['Nốt Son', 'Nốt thứ năm'], ['Nốt Si', 'Nốt thứ bảy']],
                'Thứ tự: 1-Đồ, 2-Rê, 3-Mi, 4-Pha, 5-Son, 6-La, 7-Si.', $d);
            $this->matching($L, $g, 'Nối mỗi tên gọi với ý nghĩa của nó.',
                [['Đô', 'Cách gọi khác của nốt Đồ'],
                 ['Sol', 'Cách gọi khác của nốt Son'],
                 ['Nốt nhạc', 'Ký hiệu ghi lại âm thanh'],
                 ['Hình nốt', 'Cho biết trường độ của nốt']],
                'Đô = Đồ, Sol = Son là cách gọi theo tiếng Pháp – Ý.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi nốt vào nhóm NỐT TRẦM (thấp) hoặc NỐT BỔNG (cao).',
                [['Nốt Đồ', 'Nốt trầm'], ['Nốt Rê', 'Nốt trầm'],
                 ['Nốt La', 'Nốt bổng'], ['Nốt Si', 'Nốt bổng']],
                'Trong 7 nốt, Đồ – Rê – Mi trầm hơn; Son – La – Si bổng hơn.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hình nốt vào nhóm HÌNH RỖNG hoặc HÌNH ĐẶC.',
                [['Nốt tròn', 'Hình rỗng'], ['Nốt trắng', 'Hình rỗng'],
                 ['Nốt đen', 'Hình đặc'], ['Nốt móc đơn', 'Hình đặc']],
                'Nốt tròn và nốt trắng có đầu nốt rỗng; nốt đen và nốt móc đơn có đầu nốt đặc.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hình nốt vào nhóm TRƯỜNG ĐỘ DÀI hoặc TRƯỜNG ĐỘ NGẮN.',
                [['Nốt tròn', 'Trường độ dài'], ['Nốt trắng', 'Trường độ dài'],
                 ['Nốt đen', 'Trường độ ngắn'], ['Nốt móc đơn', 'Trường độ ngắn']],
                'Nốt tròn dài nhất (4 phách), rồi đến nốt trắng (2 phách); nốt đen (1 phách) và móc đơn (nửa phách) ngắn hơn.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi ký hiệu vào nhóm NỐT NHẠC hoặc KHÔNG PHẢI NỐT NHẠC.',
                [['Nốt Đồ', 'Nốt nhạc'], ['Nốt Mi', 'Nốt nhạc'],
                 ['Dấu lặng đen', 'Không phải nốt nhạc'], ['Khóa Son', 'Không phải nốt nhạc']],
                'Nốt nhạc ghi lại âm thanh; dấu lặng ghi sự im lặng; khóa nhạc cho biết vị trí nốt.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Có tất cả ___ nốt nhạc cơ bản.', [[0, '7']],
                '7 nốt nhạc: Đồ, Rê, Mi, Pha, Son, La, Si.', $d);
            $this->fill($L, $g, 'Nốt nhạc thứ năm trong bảy nốt là nốt ___.', [[0, 'Son']],
                'Thứ tự: 1-Đồ, 2-Rê, 3-Mi, 4-Pha, 5-Son, 6-La, 7-Si.', $d);
            $this->fill($L, $g, 'Ký hiệu quốc tế của nốt La là chữ ___.', [[0, 'A']],
                'La = A trong ký hiệu quốc tế (Đồ=C, Rê=D, Mi=E, Pha=F, Son=G, La=A, Si=B).', $d);
            $this->fill($L, $g, 'Hình nốt ___ có trường độ dài nhất.', [[0, 'tròn']],
                'Nốt tròn dài 4 phách — dài nhất trong các hình nốt cơ bản.', $d);
        }
    }

    private function seedAnCaoDo62(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Khuông nhạc có bao nhiêu dòng kẻ?',
                ['5 dòng', '4 dòng', '6 dòng', '3 dòng'], 0,
                'Khuông nhạc gồm 5 dòng kẻ ngang song song.', $d);
            $this->quiz($L, $g, 'Năm dòng kẻ của khuông nhạc tạo thành mấy khe nhạc?',
                ['4 khe', '5 khe', '3 khe', '6 khe'], 0,
                'Giữa 5 dòng kẻ là 4 khe nhạc — nốt nhạc viết trên dòng hoặc trong khe.', $d);
            $this->quiz($L, $g, 'Khóa Son cho biết vị trí của nốt nhạc nào?',
                ['Nốt Son', 'Nốt Đồ', 'Nốt La', 'Nốt Mi'], 0,
                'Khóa Son (khóa G) cho biết nốt Son nằm trên dòng kẻ thứ hai của khuông nhạc.', $d);
            $this->quiz($L, $g, 'Khóa nhạc thường được viết ở vị trí nào của khuông nhạc?',
                ['Đầu khuông nhạc', 'Cuối khuông nhạc', 'Giữa khuông nhạc', 'Ngoài khuông nhạc'], 0,
                'Khóa nhạc viết ở đầu mỗi khuông nhạc để xác định tên các nốt.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi vị trí trên khuông nhạc (khóa Son) với nốt nhạc tương ứng.',
                [['Dòng kẻ thứ nhất', 'Nốt Mi'], ['Khe thứ nhất', 'Nốt Pha'],
                 ['Dòng kẻ thứ hai', 'Nốt Son'], ['Khe thứ hai', 'Nốt La']],
                'Khóa Son: dòng 1-Mi, khe 1-Pha, dòng 2-Son, khe 2-La, dòng 3-Si, khe 3-Đồ, dòng 4-Rê, khe 4-Mi, dòng 5-Pha.', $d);
            $this->matching($L, $g, 'Nối mỗi bộ phận với tên gọi của nó.',
                [['Năm đường kẻ ngang', 'Khuông nhạc'],
                 ['Dấu viết ở đầu khuông', 'Khóa nhạc'],
                 ['Chấm tròn của nốt', 'Đầu nốt nhạc'],
                 ['Đường thẳng đứng của nốt', 'Cán nốt nhạc']],
                'Khuông nhạc + khóa nhạc + đầu nốt + cán nốt là các bộ phận cơ bản của bản nhạc.', $d);
            $this->matching($L, $g, 'Nối mỗi nốt với vị trí dòng kẻ của nó (khóa Son).',
                [['Nốt Si', 'Dòng kẻ thứ ba'], ['Nốt Rê', 'Dòng kẻ thứ tư'],
                 ['Nốt Pha', 'Dòng kẻ thứ năm'], ['Nốt Mi', 'Dòng kẻ thứ nhất']],
                'Các dòng kẻ từ dưới lên: 1-Mi, 2-Son, 3-Si, 4-Rê, 5-Pha.', $d);
            $this->matching($L, $g, 'Nối mỗi khe nhạc với nốt nhạc trong khe đó (khóa Son).',
                [['Khe thứ nhất', 'Nốt Pha'], ['Khe thứ hai', 'Nốt La'],
                 ['Khe thứ ba', 'Nốt Đồ'], ['Khe thứ tư', 'Nốt Mi']],
                'Các khe từ dưới lên: 1-Pha, 2-La, 3-Đồ, 4-Mi.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi nốt vào nhóm NỐT TRÊN DÒNG KẺ hoặc NỐT TRONG KHE.',
                [['Nốt Mi (dòng 1)', 'Nốt trên dòng kẻ'], ['Nốt Son (dòng 2)', 'Nốt trên dòng kẻ'],
                 ['Nốt Pha (khe 1)', 'Nốt trong khe'], ['Nốt La (khe 2)', 'Nốt trong khe']],
                'Nốt Mi, Son, Si, Rê, Pha nằm trên các dòng kẻ; nốt Pha, La, Đồ, Mi nằm trong các khe.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Khuông nhạc có 5 dòng kẻ', 'Đúng'],
                 ['Khóa nhạc viết ở đầu khuông', 'Đúng'],
                 ['Khuông nhạc có 6 dòng kẻ', 'Sai'],
                 ['Nốt Son nằm trên dòng kẻ thứ hai', 'Đúng']],
                'Khuông nhạc 5 dòng 4 khe; khóa nhạc ở đầu khuông; khóa Son xác định nốt Son ở dòng 2.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nốt vào nhóm DÒNG KẺ LẺ (1, 3, 5) hoặc DÒNG KẺ CHẴN (2, 4).',
                [['Nốt Mi (dòng 1)', 'Dòng kẻ lẻ'], ['Nốt Si (dòng 3)', 'Dòng kẻ lẻ'],
                 ['Nốt Son (dòng 2)', 'Dòng kẻ chẵn'], ['Nốt Rê (dòng 4)', 'Dòng kẻ chẵn']],
                'Dòng lẻ: 1-Mi, 3-Si, 5-Pha. Dòng chẵn: 2-Son, 4-Rê.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi vật vào nhóm THUỘC KHUÔNG NHẠC hoặc KHÔNG THUỘC.',
                [['Dòng kẻ', 'Thuộc khuông nhạc'], ['Khe nhạc', 'Thuộc khuông nhạc'],
                 ['Phím đàn', 'Không thuộc khuông nhạc'], ['Dây đàn', 'Không thuộc khuông nhạc']],
                'Khuông nhạc gồm dòng kẻ, khe nhạc và khóa nhạc; phím đàn, dây đàn là bộ phận của nhạc cụ.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Khuông nhạc có ___ dòng kẻ.', [[0, '5']],
                'Khuông nhạc gồm 5 dòng kẻ ngang song song.', $d);
            $this->fill($L, $g, 'Giữa 5 dòng kẻ tạo thành ___ khe nhạc.', [[0, '4']],
                '4 khe nhạc nằm giữa 5 dòng kẻ.', $d);
            $this->fill($L, $g, 'Khóa ___ thường dùng cho giọng hát và nhiều loại nhạc cụ.', [[0, 'Son']],
                'Khóa Son cho biết nốt Son nằm trên dòng kẻ thứ hai.', $d);
            $this->fill($L, $g, 'Nốt Son nằm trên dòng kẻ thứ ___.', [[0, 'hai']],
                'Khóa Son ôm lấy dòng kẻ thứ hai — đó là vị trí nốt Son.', $d);
        }
    }

    private function seedAnCaoDo71(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Quãng trong âm nhạc là gì?',
                ['Khoảng cách cao độ giữa hai nốt nhạc', 'Độ dài của một bản nhạc',
                 'Tốc độ của bản nhạc', 'Âm lượng của nốt nhạc'], 0,
                'Quãng đo khoảng cách cao độ giữa hai nốt nhạc, tính bằng số bậc.', $d);
            $this->quiz($L, $g, 'Hai nốt Đồ và Mi tạo thành quãng mấy?',
                ['Quãng 3', 'Quãng 2', 'Quãng 4', 'Quãng 5'], 0,
                'Đồ – Rê – Mi: đếm 3 bậc nên Đồ–Mi là quãng 3.', $d);
            $this->quiz($L, $g, 'Quãng 8 còn được gọi là gì?',
                ['Bát độ', 'Thất độ', 'Lục độ', 'Ngũ độ'], 0,
                'Quãng 8 là hai nốt cùng tên cách nhau một bát độ (octave), ví dụ Đồ – Đồ cao.', $d);
            $this->quiz($L, $g, 'Hai nốt Đồ và Son tạo thành quãng mấy?',
                ['Quãng 5', 'Quãng 4', 'Quãng 6', 'Quãng 8'], 0,
                'Đồ – Rê – Mi – Pha – Son: đếm 5 bậc nên Đồ–Son là quãng 5.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi cặp nốt (từ nốt Đồ) với tên quãng.',
                [['Đồ – Rê', 'Quãng 2'], ['Đồ – Mi', 'Quãng 3'],
                 ['Đồ – Pha', 'Quãng 4'], ['Đồ – Son', 'Quãng 5']],
                'Đếm số bậc từ nốt thấp: Đồ(1)–Rê(2)–Mi(3)–Pha(4)–Son(5).', $d);
            $this->matching($L, $g, 'Nối mỗi quãng với đặc điểm của nó.',
                [['Quãng 2', 'Hai nốt kề nhau'], ['Quãng 3', 'Cách nhau một nốt'],
                 ['Quãng 5', 'Từ Đồ tới Son'], ['Quãng 8', 'Cùng tên, cao hơn một bát độ']],
                'Quãng càng lớn thì hai nốt càng cách xa nhau về cao độ.', $d);
            $this->matching($L, $g, 'Nối mỗi cặp nốt với tên quãng đúng.',
                [['Rê – Son', 'Quãng 4'], ['Mi – Si', 'Quãng 5'],
                 ['Pha – La', 'Quãng 3'], ['Đồ – Đồ (cao)', 'Quãng 8']],
                'Rê–Mi–Pha–Son là quãng 4; Mi–Pha–Son–La–Si là quãng 5; Pha–Son–La là quãng 3.', $d);
            $this->matching($L, $g, 'Nối mỗi hiện tượng với tên gọi.',
                [['Nốt cao hơn', 'Âm thanh bổng hơn'],
                 ['Nốt thấp hơn', 'Âm thanh trầm hơn'],
                 ['Hai nốt cùng cao độ', 'Đồng âm'],
                 ['Hai nốt cùng tên cách nhau 8 bậc', 'Bát độ']],
                'Cao độ quyết định âm bổng hay trầm; quãng 8 là hai nốt cùng tên ở hai bát độ.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi quãng vào nhóm QUÃNG HẸP hoặc QUÃNG RỘNG.',
                [['Quãng 2', 'Quãng hẹp'], ['Quãng 3', 'Quãng hẹp'],
                 ['Quãng 5', 'Quãng rộng'], ['Quãng 8', 'Quãng rộng']],
                'Quãng 2, 3 hẹp (nốt gần nhau); quãng 5, 8 rộng (nốt xa nhau).', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Quãng 3 gồm hai nốt Đồ – Mi', 'Đúng'],
                 ['Quãng 5 gồm hai nốt Đồ – La', 'Sai'],
                 ['Quãng 8 là hai nốt cùng tên cách nhau một bát độ', 'Đúng'],
                 ['Quãng 2 là hai nốt cách nhau một nốt', 'Sai']],
                'Quãng 5 từ Đồ là Đồ–Son; quãng 2 là hai nốt kề nhau.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nốt trong cặp vào nhóm NỐT THẤP HƠN hoặc NỐT CAO HƠN.',
                [['Nốt Đồ (trong cặp Đồ–Son)', 'Nốt thấp hơn'],
                 ['Nốt Mi (trong cặp Mi–La)', 'Nốt thấp hơn'],
                 ['Nốt Son (trong cặp Đồ–Son)', 'Nốt cao hơn'],
                 ['Nốt La (trong cặp Mi–La)', 'Nốt cao hơn']],
                'Trong một quãng, nốt đứng trước (thấp hơn) trầm hơn nốt đứng sau.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi quãng vào nhóm QUÃNG ĐÃ HỌC hoặc QUÃNG CHƯA HỌC.',
                [['Quãng 4', 'Quãng đã học'], ['Quãng 8', 'Quãng đã học'],
                 ['Quãng 6', 'Quãng chưa học'], ['Quãng 7', 'Quãng chưa học']],
                'Bài này học các quãng 2, 3, 4, 5, 8; quãng 6, 7 sẽ học ở bài sau.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Khoảng cách cao độ giữa hai nốt nhạc gọi là ___.', [[0, 'quãng']],
                'Quãng cho biết hai nốt cách nhau bao xa về cao độ.', $d);
            $this->fill($L, $g, 'Hai nốt Đồ và Pha tạo thành quãng ___.', [[0, '4']],
                'Đồ – Rê – Mi – Pha: đếm 4 bậc nên là quãng 4.', $d);
            $this->fill($L, $g, 'Quãng 8 còn gọi là ___ độ.', [[0, 'bát']],
                'Bát độ: hai nốt cùng tên, nốt sau cao gấp đôi tần số nốt trước.', $d);
            $this->fill($L, $g, 'Trong quãng Đồ – Son, nốt ___ có cao độ cao hơn.', [[0, 'Son']],
                'Nốt đứng sau trong quãng luôn cao hơn nốt đứng trước.', $d);
        }
    }

    private function seedAnCaoDo72(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Dấu thăng (#) có tác dụng gì với nốt nhạc?',
                ['Nâng nốt lên nửa cung', 'Hạ nốt xuống nửa cung',
                 'Hạ nốt xuống một cung', 'Không thay đổi nốt'], 0,
                'Dấu thăng đặt trước nốt nào thì nâng nốt đó lên nửa cung.', $d);
            $this->quiz($L, $g, 'Dấu giáng (b) có tác dụng gì với nốt nhạc?',
                ['Hạ nốt xuống nửa cung', 'Nâng nốt lên nửa cung',
                 'Nâng nốt lên một cung', 'Xóa nốt nhạc'], 0,
                'Dấu giáng đặt trước nốt nào thì hạ nốt đó xuống nửa cung.', $d);
            $this->quiz($L, $g, 'Âm giai Đô trưởng gồm mấy nốt và bắt đầu bằng nốt nào?',
                ['8 nốt, bắt đầu bằng nốt Đồ', '7 nốt, bắt đầu bằng nốt Rê',
                 '8 nốt, bắt đầu bằng nốt Son', '7 nốt, bắt đầu bằng nốt Đồ'], 0,
                'Âm giai Đô trưởng: Đồ Rê Mi Pha Son La Si (Đồ cao) — 8 nốt.', $d);
            $this->quiz($L, $g, 'Dấu bình dùng để làm gì?',
                ['Hủy tác dụng của dấu thăng hoặc giáng trước đó', 'Nâng nốt lên một cung',
                 'Kéo dài trường độ nốt', 'Kết thúc bản nhạc'], 0,
                'Dấu bình trả nốt nhạc về cao độ tự nhiên, hủy dấu hóa trước đó.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi dấu hóa với tác dụng của nó.',
                [['Dấu thăng (#)', 'Nâng nốt lên nửa cung'],
                 ['Dấu giáng (b)', 'Hạ nốt xuống nửa cung'],
                 ['Dấu bình', 'Trả nốt về cao độ tự nhiên'],
                 ['Không có dấu hóa', 'Nốt giữ cao độ gốc']],
                'Thăng nâng, giáng hạ, bình hủy — ba dấu hóa cơ bản.', $d);
            $this->matching($L, $g, 'Nối mỗi vị trí với nốt tương ứng trong âm giai Đô trưởng.',
                [['Nốt thứ nhất', 'Nốt Đồ'], ['Nốt thứ ba', 'Nốt Mi'],
                 ['Nốt thứ năm', 'Nốt Son'], ['Nốt thứ tám', 'Nốt Đồ (cao)']],
                'Âm giai Đô trưởng: 1-Đồ, 2-Rê, 3-Mi, 4-Pha, 5-Son, 6-La, 7-Si, 8-Đồ cao.', $d);
            $this->matching($L, $g, 'Nối mỗi nốt có dấu hóa với ý nghĩa.',
                [['Pha thăng', 'Nốt Pha nâng lên nửa cung'],
                 ['Si giáng', 'Nốt Si hạ xuống nửa cung'],
                 ['Đồ bình', 'Nốt Đồ ở cao độ tự nhiên'],
                 ['Son thăng', 'Nốt Son nâng lên nửa cung']],
                'Đọc tên nốt có dấu hóa: tên nốt + tên dấu hóa.', $d);
            $this->matching($L, $g, 'Nối mỗi khái niệm với ý nghĩa.',
                [['Nửa cung', 'Khoảng cách nhỏ nhất, như Mi – Pha'],
                 ['Một cung', 'Bằng hai nửa cung, như Đồ – Rê'],
                 ['Âm giai', 'Chuỗi nốt xếp theo thứ tự cao độ'],
                 ['Dấu hóa', 'Ký hiệu thay đổi cao độ của nốt']],
                'Trong âm giai Đô trưởng, Mi–Pha và Si–Đồ cách nhau nửa cung, các cặp còn lại cách nhau một cung.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi dấu hóa vào nhóm DẤU NÂNG hoặc DẤU HẠ cao độ.',
                [['Dấu thăng (#)', 'Dấu nâng cao độ'], ['Thăng kép', 'Dấu nâng cao độ'],
                 ['Dấu giáng (b)', 'Dấu hạ cao độ'], ['Giáng kép', 'Dấu hạ cao độ']],
                'Thăng (kể cả thăng kép) nâng cao độ; giáng (kể cả giáng kép) hạ cao độ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cặp nốt trong âm giai Đô trưởng vào nhóm NỬA CUNG hoặc MỘT CUNG.',
                [['Mi – Pha', 'Nửa cung'], ['Si – Đồ', 'Nửa cung'],
                 ['Đồ – Rê', 'Một cung'], ['Pha – Son', 'Một cung']],
                'Âm giai trưởng có 2 vị trí nửa cung: Mi–Pha và Si–Đồ; còn lại là một cung.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Dấu thăng nâng nốt lên nửa cung', 'Đúng'],
                 ['Dấu giáng nâng nốt lên nửa cung', 'Sai'],
                 ['Âm giai Đô trưởng có 8 nốt', 'Đúng'],
                 ['Dấu bình làm nốt cao thêm', 'Sai']],
                'Giáng thì hạ chứ không nâng; dấu bình chỉ hủy dấu hóa, không làm cao thêm.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nốt vào nhóm TRONG ÂM GIAI ĐÔ TRƯỞNG hoặc NGOÀI ÂM GIAI.',
                [['Nốt Mi', 'Trong âm giai Đô trưởng'], ['Nốt La', 'Trong âm giai Đô trưởng'],
                 ['Nốt Pha thăng', 'Ngoài âm giai Đô trưởng'], ['Nốt Rê thăng', 'Ngoài âm giai Đô trưởng']],
                'Âm giai Đô trưởng không có dấu hóa: Đồ Rê Mi Pha Son La Si (Đồ).', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Dấu ___ nâng nốt nhạc lên nửa cung.', [[0, 'thăng']],
                'Dấu thăng (#) đặt trước nốt nào thì nâng nốt đó lên nửa cung.', $d);
            $this->fill($L, $g, 'Dấu giáng hạ nốt nhạc xuống ___ cung.', [[0, 'nửa']],
                'Dấu giáng (b) hạ nốt xuống nửa cung — bằng một nửa của một cung.', $d);
            $this->fill($L, $g, 'Âm giai Đô trưởng có 8 nốt, bắt đầu bằng nốt ___ và kết thúc bằng nốt ___ ở cao hơn một bát độ.', [[0, 'Đồ'], [1, 'Đồ']],
                'Âm giai Đô trưởng: Đồ Rê Mi Pha Son La Si (Đồ cao).', $d);
            $this->fill($L, $g, 'Khoảng cách giữa nốt Mi và nốt Pha là ___ cung.', [[0, 'nửa']],
                'Mi–Pha và Si–Đồ là hai vị trí nửa cung trong âm giai Đô trưởng.', $d);
        }
    }

    // ================= ÂM NHẠC: nhịp & dấu lặng =================

    private function seedAnNhip71(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Trong số chỉ nhịp 2/4, số 2 (số trên) cho biết điều gì?',
                ['Mỗi ô nhịp có 2 phách', 'Mỗi phách dài 2 giây',
                 'Bản nhạc có 2 ô nhịp', 'Mỗi ô nhịp dài 2 phút'], 0,
                'Số trên của số chỉ nhịp cho biết mỗi ô nhịp có bao nhiêu phách.', $d);
            $this->quiz($L, $g, 'Nhịp 3/4 thì mỗi ô nhịp có mấy phách?',
                ['3 phách', '2 phách', '4 phách', '6 phách'], 0,
                'Nhịp 3/4: mỗi ô nhịp có 3 phách đen.', $d);
            $this->quiz($L, $g, 'Dấu lặng đen tương đương với thời gian nghỉ bao lâu?',
                ['Nghỉ 1 phách', 'Nghỉ 2 phách', 'Nghỉ 3 phách', 'Nghỉ 4 phách'], 0,
                'Dấu lặng đen tương ứng với nốt đen: nghỉ 1 phách.', $d);
            $this->quiz($L, $g, 'Trong nhịp 2/4, phách nào là phách mạnh?',
                ['Phách 1', 'Phách 2', 'Cả hai phách', 'Không có phách mạnh'], 0,
                'Phách đầu tiên của mỗi ô nhịp luôn là phách mạnh, được nhấn mạnh hơn.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi số chỉ nhịp với ý nghĩa của nó.',
                [['Nhịp 2/4', 'Mỗi ô nhịp 2 phách'], ['Nhịp 3/4', 'Mỗi ô nhịp 3 phách'],
                 ['Nhịp 4/4', 'Mỗi ô nhịp 4 phách'], ['Số chỉ nhịp', 'Viết ở đầu bản nhạc']],
                'Số trên = số phách mỗi ô; số dưới = giá trị mỗi phách (4 là nốt đen).', $d);
            $this->matching($L, $g, 'Nối mỗi dấu lặng với thời gian nghỉ tương ứng.',
                [['Dấu lặng tròn', 'Nghỉ 4 phách'], ['Dấu lặng trắng', 'Nghỉ 2 phách'],
                 ['Dấu lặng đen', 'Nghỉ 1 phách'], ['Dấu lặng móc đơn', 'Nghỉ nửa phách']],
                'Dấu lặng có giá trị nghỉ tương ứng với hình nốt cùng tên.', $d);
            $this->matching($L, $g, 'Nối mỗi khái niệm với ý nghĩa.',
                [['Phách mạnh', 'Phách 1, được nhấn mạnh'],
                 ['Phách yếu', 'Các phách còn lại trong ô'],
                 ['Ô nhịp', 'Phần nhạc giữa hai vạch nhịp'],
                 ['Vạch nhịp', 'Đường kẻ đứng chia các ô nhịp']],
                'Ô nhịp được chia bởi vạch nhịp; phách 1 là phách mạnh.', $d);
            $this->matching($L, $g, 'Nối mỗi cách đếm với loại nhịp tương ứng.',
                [['Đếm "1 – 2"', 'Nhịp 2/4'], ['Đếm "1 – 2 – 3"', 'Nhịp 3/4'],
                 ['Nghỉ 1 phách', 'Dấu lặng đen'], ['Nghỉ 4 phách', 'Dấu lặng tròn']],
                'Đếm đúng số phách giúp giữ nhịp chính xác khi hát và chơi nhạc.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi đặc điểm vào nhóm NHỊP 2/4 hoặc NHỊP 3/4.',
                [['Đếm "1 – 2"', 'Nhịp 2/4'], ['Thường dùng cho hành khúc', 'Nhịp 2/4'],
                 ['Đếm "1 – 2 – 3"', 'Nhịp 3/4'], ['Thường dùng cho điệu valse', 'Nhịp 3/4']],
                'Nhịp 2/4 khỏe khoắn hợp hành khúc; nhịp 3/4 uyển chuyển hợp điệu valse.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phách vào nhóm PHÁCH MẠNH hoặc PHÁCH YẾU.',
                [['Phách 1 của ô nhịp', 'Phách mạnh'],
                 ['Phách đầu mỗi ô nhịp', 'Phách mạnh'],
                 ['Phách 2 trong nhịp 3/4', 'Phách yếu'],
                 ['Phách 3 trong nhịp 3/4', 'Phách yếu']],
                'Phách 1 luôn mạnh nhất; các phách còn lại là phách yếu.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nhịp 3/4 có 3 phách trong mỗi ô nhịp', 'Đúng'],
                 ['Số chỉ nhịp viết ở đầu bản nhạc', 'Đúng'],
                 ['Dấu lặng đen nghỉ 2 phách', 'Sai'],
                 ['Nhịp 2/4 có 3 phách', 'Sai']],
                'Dấu lặng đen nghỉ 1 phách; nhịp 2/4 có 2 phách mỗi ô.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi ký hiệu vào nhóm NỐT NHẠC (có âm thanh) hoặc DẤU LẶNG (im lặng).',
                [['Nốt đen', 'Nốt nhạc'], ['Nốt trắng', 'Nốt nhạc'],
                 ['Dấu lặng đen', 'Dấu lặng'], ['Dấu lặng tròn', 'Dấu lặng']],
                'Nốt nhạc phát ra âm thanh; dấu lặng là khoảng im lặng có độ dài xác định.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Số chỉ nhịp 3/4 cho biết mỗi ô nhịp có ___ phách.', [[0, '3']],
                'Số trên của 3/4 là 3 — mỗi ô nhịp 3 phách đen.', $d);
            $this->fill($L, $g, 'Dấu lặng ___ có thời gian nghỉ tương đương với nốt đen.', [[0, 'đen']],
                'Dấu lặng đen nghỉ 1 phách, bằng trường độ nốt đen.', $d);
            $this->fill($L, $g, 'Trong mỗi ô nhịp, phách ___ thường được nhấn mạnh nhất.', [[0, '1']],
                'Phách 1 là phách mạnh nhất của ô nhịp.', $d);
            $this->fill($L, $g, 'Nhịp 2/4 khỏe khoắn thường dùng cho các bài ___ khúc.', [[0, 'hành']],
                'Hành khúc thường viết ở nhịp 2/4 với tiết tấu mạnh mẽ.', $d);
        }
    }

    private function seedAnNhip72(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Nhịp 4/4 thì mỗi ô nhịp có mấy phách đen?',
                ['4 phách', '2 phách', '3 phách', '6 phách'], 0,
                'Nhịp 4/4: mỗi ô nhịp có 4 phách đen.', $d);
            $this->quiz($L, $g, 'Nốt đen có dấu chấm dôi thì dài bao nhiêu phách?',
                ['1,5 phách', '1 phách', '2 phách', '3 phách'], 0,
                'Dấu chấm dôi tăng thêm một nửa giá trị: nốt đen (1) + một nửa (0,5) = 1,5 phách.', $d);
            $this->quiz($L, $g, 'Trong nhịp 4/4, ngoài phách 1 thì phách nào còn được nhấn (mạnh vừa)?',
                ['Phách 3', 'Phách 2', 'Phách 4', 'Không có phách nào'], 0,
                'Nhịp 4/4: phách 1 mạnh nhất, phách 3 mạnh vừa, phách 2 và 4 yếu.', $d);
            $this->quiz($L, $g, 'Ký hiệu chữ C ở đầu bản nhạc thay cho số chỉ nhịp nào?',
                ['Nhịp 4/4', 'Nhịp 2/4', 'Nhịp 3/4', 'Nhịp 6/8'], 0,
                'Chữ C là ký hiệu thay thế cho nhịp 4/4 (nhịp common).', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi nốt có dấu chấm dôi với trường độ của nó.',
                [['Nốt trắng chấm dôi', '3 phách'], ['Nốt đen chấm dôi', '1,5 phách'],
                 ['Nốt tròn', '4 phách'], ['Nốt móc đơn', '0,5 phách']],
                'Chấm dôi tăng thêm 1/2: trắng (2+1)=3; đen (1+0,5)=1,5.', $d);
            $this->matching($L, $g, 'Nối mỗi phách trong nhịp 4/4 với tính chất của nó.',
                [['Phách 1', 'Mạnh nhất'], ['Phách 3', 'Mạnh vừa'],
                 ['Phách 2 và 4', 'Phách yếu'], ['Dấu chấm dôi', 'Tăng thêm 1/2 giá trị']],
                'Nhịp 4/4: mạnh – yếu – mạnh vừa – yếu.', $d);
            $this->matching($L, $g, 'Nối mỗi ký hiệu với ý nghĩa.',
                [['Ký hiệu C', 'Nhịp 4/4'],
                 ['Ký hiệu C có gạch dọc', 'Nhịp 2/2'],
                 ['Số chỉ nhịp', 'Gồm tử số và mẫu số'],
                 ['Vạch nhịp kép', 'Kết thúc bản nhạc']],
                'C là nhịp 4/4; C gạch dọc là nhịp 2/2 (nhanh gấp đôi).', $d);
            $this->matching($L, $g, 'Nối mỗi giá trị với số nốt móc đơn tương đương.',
                [['1 nốt đen chấm dôi', '3 nốt móc đơn'], ['1 nốt trắng chấm dôi', '6 nốt móc đơn'],
                 ['1 nốt trắng', '4 nốt móc đơn'], ['1 nốt tròn', '8 nốt móc đơn']],
                'Nốt móc đơn = 0,5 phách: đen chấm dôi 1,5 = 3 móc đơn; trắng chấm dôi 3 = 6 móc đơn.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi hình nốt vào nhóm DÀI HƠN 1 PHÁCH hoặc NGẮN HƠN 1 PHÁCH.',
                [['Nốt tròn', 'Dài hơn 1 phách'], ['Nốt trắng', 'Dài hơn 1 phách'],
                 ['Nốt móc đơn', 'Ngắn hơn 1 phách'], ['Nốt móc kép', 'Ngắn hơn 1 phách']],
                'Tròn = 4, trắng = 2 phách (dài hơn 1); móc đơn = 1/2, móc kép = 1/4 (ngắn hơn 1).', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phách của nhịp 4/4 vào nhóm PHÁCH MẠNH hoặc PHÁCH YẾU.',
                [['Phách 1', 'Phách mạnh'], ['Phách 3', 'Phách mạnh'],
                 ['Phách 2', 'Phách yếu'], ['Phách 4', 'Phách yếu']],
                'Nhịp 4/4: phách 1 mạnh nhất, phách 3 mạnh vừa — đều là phách mạnh.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Dấu chấm dôi tăng giá trị nốt thêm một nửa', 'Đúng'],
                 ['Ký hiệu C nghĩa là nhịp 4/4', 'Đúng'],
                 ['Nhịp 4/4 có 3 phách', 'Sai'],
                 ['Nốt đen chấm dôi dài 2 phách', 'Sai']],
                'Nhịp 4/4 có 4 phách; nốt đen chấm dôi dài 1,5 phách.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi ví dụ vào nhóm CÓ DẤU CHẤM DÔI hoặc KHÔNG CÓ.',
                [['Nốt trắng chấm dôi = 3 phách', 'Có dấu chấm dôi'],
                 ['Dấu lặng đen chấm dôi = 1,5 phách', 'Có dấu chấm dôi'],
                 ['Nốt đen = 1 phách', 'Không có dấu chấm dôi'],
                 ['Nốt tròn = 4 phách', 'Không có dấu chấm dôi']],
                'Dấu chấm dôi áp dụng cho cả nốt nhạc và dấu lặng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Nhịp 4/4 còn được ký hiệu bằng chữ ___.', [[0, 'C']],
                'Chữ C thay cho số chỉ nhịp 4/4.', $d);
            $this->fill($L, $g, 'Nốt trắng có dấu chấm dôi dài ___ phách.', [[0, '3']],
                'Nốt trắng (2 phách) + một nửa (1 phách) = 3 phách.', $d);
            $this->fill($L, $g, 'Dấu chấm dôi làm tăng giá trị của nốt thêm ___ giá trị của nốt đó.', [[0, 'một nửa']],
                'Dấu chấm dôi = cộng thêm 1/2 giá trị nốt đứng trước nó.', $d);
            $this->fill($L, $g, 'Một ô nhịp 4/4 chứa vừa đủ ___ nốt đen.', [[0, '4']],
                '4 nốt đen x 1 phách = 4 phách = 1 ô nhịp 4/4.', $d);
        }
    }

    private function seedAnNhip81(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Nhịp 6/8 thuộc loại nhịp nào?',
                ['Nhịp kép', 'Nhịp đơn', 'Nhịp hỗn hợp', 'Không phải số chỉ nhịp'], 0,
                'Nhịp 6/8 là nhịp kép vì mỗi phách chia thành 3 phần bằng nhau.', $d);
            $this->quiz($L, $g, 'Nhịp 6/8 có mấy phách trong mỗi ô nhịp?',
                ['2 phách', '3 phách', '4 phách', '6 phách'], 0,
                'Nhịp 6/8 có 2 phách; mỗi phách bằng một nốt đen chấm dôi.', $d);
            $this->quiz($L, $g, 'Mỗi phách trong nhịp 6/8 tương đương với mấy nốt móc đơn?',
                ['3 nốt', '2 nốt', '4 nốt', '6 nốt'], 0,
                'Mỗi phách nhịp kép = 1 nốt đen chấm dôi = 3 nốt móc đơn.', $d);
            $this->quiz($L, $g, 'Số chỉ nhịp nào sau đây là nhịp đơn?',
                ['2/4', '6/8', '9/8', '12/8'], 0,
                'Nhịp đơn: mỗi phách chia 2 phần (2/4, 3/4, 4/4). Nhịp kép: mỗi phách chia 3 phần (6/8, 9/8, 12/8).', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi loại nhịp với đặc điểm.',
                [['Nhịp đơn', 'Mỗi phách chia thành 2 phần'],
                 ['Nhịp kép', 'Mỗi phách chia thành 3 phần'],
                 ['Nhịp 6/8', 'Nhịp kép 2 phách'],
                 ['Nhịp 9/8', 'Nhịp kép 3 phách']],
                'Đơn chia 2, kép chia 3 — đó là điểm khác biệt cốt lõi.', $d);
            $this->matching($L, $g, 'Nối mỗi số chỉ nhịp với loại của nó.',
                [['2/4', 'Nhịp đơn'], ['3/4', 'Nhịp đơn'],
                 ['6/8', 'Nhịp kép'], ['12/8', 'Nhịp kép']],
                'Mẫu số 4 thường là nhịp đơn; mẫu số 8 thường là nhịp kép.', $d);
            $this->matching($L, $g, 'Nối mỗi giá trị với ý nghĩa trong nhịp 6/8.',
                [['Một phách của 6/8', '1 nốt đen chấm dôi'],
                 ['Một ô nhịp 6/8', '6 nốt móc đơn'],
                 ['Một phách của 2/4', '1 nốt đen'],
                 ['Một ô nhịp 2/4', '2 nốt đen']],
                'Ô nhịp 6/8 = 2 phách x 3 móc đơn = 6 nốt móc đơn.', $d);
            $this->matching($L, $g, 'Nối mỗi thành phần của số chỉ nhịp 6/8 với ý nghĩa.',
                [['Tử số 6', 'Tổng 6 nốt móc đơn mỗi ô'],
                 ['Mẫu số 8', 'Đơn vị là nốt móc đơn'],
                 ['Số phách', '2 phách'],
                 ['Giá trị mỗi phách', 'Nốt đen chấm dôi']],
                'Hiểu cấu tạo số chỉ nhịp giúp đếm phách chính xác.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi số chỉ nhịp vào nhóm NHỊP ĐƠN hoặc NHỊP KÉP.',
                [['2/4', 'Nhịp đơn'], ['4/4', 'Nhịp đơn'],
                 ['6/8', 'Nhịp kép'], ['9/8', 'Nhịp kép']],
                'Nhịp đơn mẫu số thường là 4; nhịp kép mẫu số thường là 8.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nhịp 6/8 có 2 phách', 'Đúng'],
                 ['Nhịp 3/4 là nhịp đơn', 'Đúng'],
                 ['Nhịp kép mỗi phách chia làm 2 phần', 'Sai'],
                 ['Mỗi phách 6/8 bằng 2 nốt móc đơn', 'Sai']],
                'Nhịp kép mỗi phách chia 3 phần; mỗi phách 6/8 bằng 3 nốt móc đơn.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi số chỉ nhịp vào nhóm có 2 PHÁCH hoặc 3 PHÁCH.',
                [['Nhịp 2/4', '2 phách'], ['Nhịp 6/8', '2 phách'],
                 ['Nhịp 3/4', '3 phách'], ['Nhịp 9/8', '3 phách']],
                '2/4 và 6/8 đều có 2 phách nhưng khác nhau: đơn chia 2, kép chia 3.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi loại nhịp vào nhóm cảm giác khi nghe.',
                [['Nhịp 6/8', 'Nhịp kép – dập dềnh'], ['Nhịp 9/8', 'Nhịp kép – dập dềnh'],
                 ['Nhịp 2/4', 'Nhịp đơn – dứt khoát'], ['Nhịp 4/4', 'Nhịp đơn – dứt khoát']],
                'Nhịp kép (chia 3) gợi cảm giác đu đưa, dập dềnh như sóng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Nhịp 6/8 là nhịp ___, mỗi phách chia thành 3 phần bằng nhau.', [[0, 'kép']],
                'Nhịp kép: mỗi phách chia 3 phần bằng nhau.', $d);
            $this->fill($L, $g, 'Một ô nhịp 6/8 gồm ___ phách.', [[0, '2']],
                'Nhịp 6/8 có 2 phách, mỗi phách là một nốt đen chấm dôi.', $d);
            $this->fill($L, $g, 'Mỗi phách của nhịp 6/8 tương đương ___ nốt móc đơn.', [[0, '3']],
                '1 nốt đen chấm dôi = 3 nốt móc đơn.', $d);
            $this->fill($L, $g, 'Nhịp 2/4 là nhịp ___, mỗi phách chia thành 2 phần bằng nhau.', [[0, 'đơn']],
                'Nhịp đơn: mỗi phách chia 2 phần bằng nhau.', $d);
        }
    }

    private function seedAnNhip82(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Nốt móc kép bằng bao nhiêu phách (khi đơn vị phách là nốt đen)?',
                ['1/4 phách', '1 phách', '1/2 phách', '2 phách'], 0,
                'Nốt móc kép = một nửa nốt móc đơn = 1/4 phách.', $d);
            $this->quiz($L, $g, 'Một ô nhịp 2/4 chứa tối đa bao nhiêu nốt móc đơn?',
                ['4 nốt', '2 nốt', '8 nốt', '16 nốt'], 0,
                'Ô nhịp 2/4 = 2 phách; mỗi phách = 2 móc đơn → tối đa 4 nốt móc đơn.', $d);
            $this->quiz($L, $g, 'Dấu lặng móc đơn có thời gian nghỉ là bao lâu?',
                ['1/2 phách', '1 phách', '2 phách', '1/4 phách'], 0,
                'Dấu lặng móc đơn nghỉ nửa phách, bằng trường độ nốt móc đơn.', $d);
            $this->quiz($L, $g, 'Khi đếm "1 và 2 và" trong nhịp 2/4, chữ "và" rơi vào vị trí nào?',
                ['Giữa hai phách', 'Trên phách mạnh', 'Cuối ô nhịp', 'Đầu ô nhịp'], 0,
                'Chữ "và" đếm nửa phách sau mỗi phách — giúp chia nhỏ nhịp chính xác.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi hình nốt với trường độ (đơn vị nốt đen).',
                [['Nốt tròn', '4 phách'], ['Nốt trắng', '2 phách'],
                 ['Nốt đen', '1 phách'], ['Nốt móc kép', '1/4 phách']],
                'Tròn 4 – trắng 2 – đen 1 – móc đơn 1/2 – móc kép 1/4.', $d);
            $this->matching($L, $g, 'Nối mỗi nhóm nốt với giá trị tương đương.',
                [['2 nốt móc đơn', '1 nốt đen'], ['4 nốt móc đơn', '1 nốt trắng'],
                 ['8 nốt móc đơn', '1 nốt tròn'], ['2 nốt móc kép', '1 nốt móc đơn']],
                'Quy đổi nhanh giúp kiểm tra ô nhịp có đủ hay thừa phách.', $d);
            $this->matching($L, $g, 'Nối mỗi cách đếm với loại nhịp.',
                [['Đếm "1 – 2 – 3 – 4"', 'Nhịp 4/4'], ['Đếm "1 – 2"', 'Nhịp 2/4'],
                 ['Đếm "1 – 2 – 3"', 'Nhịp 3/4'], ['Đếm "1 – 2" (mỗi phách 3 móc đơn)', 'Nhịp 6/8']],
                'Mỗi loại nhịp có cách đếm phách riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi dấu diễn cảm với ý nghĩa.',
                [['Dấu legato (nối)', 'Hát liền mạch các nốt'],
                 ['Dấu staccato (chấm)', 'Hát nảy, ngắn gọn từng nốt'],
                 ['Dấu lặng', 'Nghỉ, không phát ra âm thanh'],
                 ['Dấu chấm dôi', 'Kéo dài thêm một nửa giá trị']],
                'Dấu diễn cảm cho biết cách thể hiện, không chỉ cao độ trường độ.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi hình nốt vào nhóm DÀI HƠN NỐT ĐEN hoặc NGẮN HƠN NỐT ĐEN.',
                [['Nốt tròn', 'Dài hơn nốt đen'], ['Nốt trắng', 'Dài hơn nốt đen'],
                 ['Nốt móc đơn', 'Ngắn hơn nốt đen'], ['Nốt móc kép', 'Ngắn hơn nốt đen']],
                'Tròn (4), trắng (2) dài hơn đen (1); móc đơn (1/2), móc kép (1/4) ngắn hơn.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['4 nốt móc đơn = 1 nốt trắng', 'Đúng'],
                 ['Dấu lặng tròn nghỉ 4 phách', 'Đúng'],
                 ['Nốt móc kép dài hơn nốt móc đơn', 'Sai'],
                 ['1 ô nhịp 3/4 chứa được 6 nốt móc đơn', 'Đúng']],
                'Móc kép (1/4) ngắn hơn móc đơn (1/2); ô 3/4 = 3 phách = 6 móc đơn.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi ký hiệu vào nhóm CÓ ÂM THANH hoặc IM LẶNG.',
                [['Nốt đen', 'Có âm thanh'], ['Nốt móc đơn', 'Có âm thanh'],
                 ['Dấu lặng đen', 'Im lặng'], ['Dấu lặng trắng', 'Im lặng']],
                'Nốt nhạc phát âm thanh; dấu lặng là quãng nghỉ im lặng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nhóm nốt vào nhóm VỪA ĐỦ 1 Ô NHỊP 4/4 hoặc VƯỢT QUÁ.',
                [['4 nốt đen', 'Vừa đủ 1 ô nhịp'], ['2 nốt trắng', 'Vừa đủ 1 ô nhịp'],
                 ['5 nốt đen', 'Vượt quá 1 ô nhịp'], ['1 nốt tròn + 1 nốt đen', 'Vượt quá 1 ô nhịp']],
                'Ô nhịp 4/4 = 4 phách: 5 nốt đen = 5 phách là thừa.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Nốt ___ kép có giá trị bằng 1/4 phách (khi đơn vị phách là nốt đen).', [[0, 'móc']],
                'Nốt móc kép = 1/2 nốt móc đơn = 1/4 phách.', $d);
            $this->fill($L, $g, 'Một ô nhịp 3/4 chứa được tối đa ___ nốt móc đơn.', [[0, '6']],
                'Ô 3/4 = 3 phách = 6 nốt móc đơn.', $d);
            $this->fill($L, $g, 'Cách hát nảy, ngắn gọn từng nốt được ký hiệu bằng dấu ___.', [[0, 'staccato']],
                'Staccato (dấu chấm trên/ dưới nốt): hát nảy gọn.', $d);
            $this->fill($L, $g, 'Đếm phách đều giúp người chơi nhạc giữ đúng ___.', [[0, 'nhịp']],
                'Giữ nhịp ổn định là kỹ năng cơ bản của mọi nhạc công.', $d);
        }
    }

    // ================= ÂM NHẠC: nhạc cụ dân tộc & nghệ thuật truyền thống =================

    private function seedAnNhacCu81(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Đàn bầu có mấy dây?',
                ['1 dây', '2 dây', '4 dây', '16 dây'], 0,
                'Đàn bầu chỉ có duy nhất 1 dây — nhạc cụ độc đáo nhất của Việt Nam.', $d);
            $this->quiz($L, $g, 'Đàn tranh có mấy dây?',
                ['16 dây', '4 dây', '12 dây', '21 dây'], 0,
                'Đàn tranh (đàn thập lục) có 16 dây, gảy bằng móng.', $d);
            $this->quiz($L, $g, 'Nhạc cụ nào sau đây dùng vĩ để kéo?',
                ['Đàn nhị', 'Đàn bầu', 'Đàn tranh', 'Đàn nguyệt'], 0,
                'Đàn nhị (đàn cò) có 2 dây, dùng vĩ kéo — âm thanh gần với giọng người.', $d);
            $this->quiz($L, $g, 'Đàn nguyệt còn có tên gọi khác là gì?',
                ['Đàn kìm', 'Đàn sến', 'Đàn gáo', 'Đàn tính'], 0,
                'Đàn nguyệt (mặt tròn như mặt trăng) còn gọi là đàn kìm, có 2 dây.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi nhạc cụ với đặc điểm cấu tạo.',
                [['Đàn bầu', '1 dây'], ['Đàn tranh', '16 dây'],
                 ['Đàn nguyệt', '2 dây, mặt tròn'], ['Đàn tỳ bà', '4 dây, mặt hình quả lê']],
                'Số dây và hình dáng mặt đàn là dấu hiệu nhận biết các nhạc cụ dây gảy.', $d);
            $this->matching($L, $g, 'Nối mỗi nhạc cụ với cách chơi.',
                [['Đàn nhị', 'Dùng vĩ kéo'], ['Đàn bầu', 'Dùng que gảy'],
                 ['Đàn tranh', 'Đeo móng gảy'], ['Đàn tỳ bà', 'Dùng phím gảy']],
                'Dây gảy dùng que/móng/phím; dây kéo dùng vĩ.', $d);
            $this->matching($L, $g, 'Nối mỗi nhạc cụ với màu sắc âm thanh.',
                [['Đàn bầu', 'Réo rắt, độc đáo'], ['Đàn tranh', 'Trong trẻo, thánh thót'],
                 ['Đàn nhị', 'Gần với giọng người'], ['Đàn nguyệt', 'Thường đệm cho chèo, hát văn']],
                'Mỗi nhạc cụ có âm sắc riêng, phù hợp với từng loại hình nghệ thuật.', $d);
            $this->matching($L, $g, 'Nối mỗi nhạc cụ với đặc điểm riêng.',
                [['Đàn gáo', 'Mặt đàn làm bằng gáo dừa'],
                 ['Đàn tính', 'Nhạc cụ của người Tày'],
                 ['Đàn t\'rưng', 'Nhạc cụ của người Ê Đê'],
                 ['Đàn đá', 'Nhạc cụ gõ cổ xưa']],
                'Các dân tộc Việt Nam đều có nhạc cụ đặc trưng riêng.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm DÂY GẢY hoặc DÂY KÉO.',
                [['Đàn bầu', 'Dây gảy'], ['Đàn tranh', 'Dây gảy'],
                 ['Đàn nhị', 'Dây kéo'], ['Đàn gáo', 'Dây kéo']],
                'Dây gảy: bầu, tranh, nguyệt, tỳ bà. Dây kéo: nhị, gáo.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Đàn bầu chỉ có 1 dây', 'Đúng'],
                 ['Đàn nhị dùng vĩ để kéo', 'Đúng'],
                 ['Đàn tranh có 4 dây', 'Sai'],
                 ['Đàn nguyệt có 16 dây', 'Sai']],
                'Đàn tranh 16 dây; đàn nguyệt 2 dây.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm ÍT DÂY hoặc NHIỀU DÂY.',
                [['Đàn bầu (1 dây)', 'Ít dây'], ['Đàn nhị (2 dây)', 'Ít dây'],
                 ['Đàn tranh (16 dây)', 'Nhiều dây'], ['Đàn thập lục (16 dây)', 'Nhiều dây']],
                'Đàn thập lục là tên gọi khác của đàn tranh — đều 16 dây.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm NHẠC CỤ DÂN TỘC hoặc NHẠC CỤ PHƯƠNG TÂY.',
                [['Đàn bầu', 'Nhạc cụ dân tộc'], ['Đàn tranh', 'Nhạc cụ dân tộc'],
                 ['Violin', 'Nhạc cụ phương Tây'], ['Piano', 'Nhạc cụ phương Tây']],
                'Đàn bầu, đàn tranh là nhạc cụ truyền thống Việt Nam.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Đàn ___ là nhạc cụ độc đáo chỉ có 1 dây của Việt Nam.', [[0, 'bầu']],
                'Đàn bầu — 1 dây, âm thanh réo rắt đặc biệt.', $d);
            $this->fill($L, $g, 'Đàn tranh có ___ dây.', [[0, '16']],
                'Đàn tranh còn gọi là đàn thập lục (thập lục = mười sáu).', $d);
            $this->fill($L, $g, 'Đàn nhị phát ra âm thanh nhờ chiếc ___ kéo trên dây đàn.', [[0, 'vĩ']],
                'Vĩ là thanh tre có cước dùng để kéo dây đàn nhị.', $d);
            $this->fill($L, $g, 'Mặt đàn nguyệt có hình ___.', [[0, 'tròn']],
                'Nguyệt nghĩa là mặt trăng — mặt đàn tròn như trăng rằm.', $d);
        }
    }

    private function seedAnNhacCu82(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Sáo của Việt Nam thường được làm bằng chất liệu gì?',
                ['Trúc', 'Gỗ lim', 'Đồng', 'Nhựa'], 0,
                'Sáo Việt Nam thường làm bằng trúc — nhẹ, âm thanh trong.', $d);
            $this->quiz($L, $g, 'Cồng chiêng là di sản văn hóa của vùng nào?',
                ['Tây Nguyên', 'Đồng bằng sông Hồng', 'Miền Trung', 'Nam Bộ'], 0,
                'Không gian văn hóa cồng chiêng Tây Nguyên là di sản văn hóa phi vật thể của nhân loại.', $d);
            $this->quiz($L, $g, 'Trống chầu được dùng trong loại hình nghệ thuật nào?',
                ['Ca trù', 'Chèo', 'Quan họ', 'Hát văn'], 0,
                'Trong ca trù, trống chầu do người thưởng thức cầm — đánh khen chê ca nương.', $d);
            $this->quiz($L, $g, 'Nhạc cụ nào sau đây thuộc nhóm nhạc cụ gõ?',
                ['Phách', 'Sáo', 'Kèn bầu', 'Tiêu'], 0,
                'Phách (hai thanh gỗ/tre gõ vào nhau) dùng giữ nhịp — thuộc nhóm gõ.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi nhạc cụ với cách phát ra âm thanh.',
                [['Sáo', 'Thổi bằng hơi'], ['Kèn bầu', 'Thổi, âm vang'],
                 ['Trống cơm', 'Gõ bằng tay hoặc dùi'], ['Phách', 'Gõ để giữ nhịp']],
                'Hơi: thổi; gõ: dùng tay hoặc dùi tác động.', $d);
            $this->matching($L, $g, 'Nối mỗi nhạc cụ với vùng miền gắn liền với nó.',
                [['Cồng chiêng', 'Tây Nguyên'], ['Trống cơm', 'Đồng bằng Bắc Bộ'],
                 ['Đàn đá', 'Nam Trung Bộ'], ['Kèn Saranai', 'Người Chăm, Nam Trung Bộ']],
                'Mỗi vùng miền có nhạc cụ đặc trưng riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi nhạc cụ với loại hình nghệ thuật nó phục vụ.',
                [['Trống chầu', 'Ca trù'], ['Trống cơm', 'Chèo'],
                 ['Sanh tiền', 'Hát văn, chầu văn'], ['Thanh la', 'Dàn nhạc dân tộc']],
                'Nhạc cụ gắn chặt với từng loại hình nghệ thuật truyền thống.', $d);
            $this->matching($L, $g, 'Nối mỗi nhóm nhạc cụ với cách phân loại.',
                [['Nhạc cụ hơi', 'Dùng hơi thổi'], ['Nhạc cụ gõ', 'Dùng dùi hoặc tay gõ'],
                 ['Nhạc cụ dây', 'Dây rung phát ra âm'], ['Cồng chiêng', 'Tự thân vang khi gõ']],
                'Phân loại nhạc cụ theo cách phát ra âm thanh.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm NHẠC CỤ HƠI hoặc NHẠC CỤ GÕ.',
                [['Sáo', 'Nhạc cụ hơi'], ['Kèn bầu', 'Nhạc cụ hơi'],
                 ['Trống cơm', 'Nhạc cụ gõ'], ['Phách', 'Nhạc cụ gõ']],
                'Sáo, kèn bầu dùng hơi thổi; trống cơm, phách dùng gõ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Cồng chiêng là di sản văn hóa phi vật thể của nhân loại', 'Đúng'],
                 ['Trống cơm dùng trong hát chèo', 'Đúng'],
                 ['Sáo làm bằng kim loại', 'Sai'],
                 ['Phách là nhạc cụ hơi', 'Sai']],
                'Sáo làm bằng trúc; phách là nhạc cụ gõ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm theo vùng miền.',
                [['Cồng chiêng', 'Tây Nguyên'], ['Trống cơm', 'Đồng bằng Bắc Bộ'],
                 ['Đàn đá', 'Nam Trung Bộ'], ['Kèn Saranai', 'Nam Trung Bộ']],
                'Cồng chiêng – Tây Nguyên; trống cơm – Bắc Bộ; đàn đá, kèn Saranai – Nam Trung Bộ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm làm bằng TRE/GỖ hoặc KIM LOẠI.',
                [['Sáo', 'Tre, gỗ'], ['Phách', 'Tre, gỗ'],
                 ['Cồng', 'Kim loại'], ['Chiêng', 'Kim loại']],
                'Sáo, phách làm từ tre gỗ; cồng chiêng đúc bằng đồng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Cồng chiêng là di sản văn hóa của đồng bào ___ Nguyên.', [[0, 'Tây']],
                'Không gian văn hóa cồng chiêng Tây Nguyên — di sản UNESCO.', $d);
            $this->fill($L, $g, 'Trống ___ giữ vai trò "cầm chầu" trong nghệ thuật ca trù.', [[0, 'chầu']],
                'Trống chầu: người nghe cầm dùi đánh để khen – chê câu hát.', $d);
            $this->fill($L, $g, 'Sáo Việt Nam thường được làm bằng ___.', [[0, 'trúc']],
                'Trúc nhẹ, thẳng, cho âm sáo trong trẻo.', $d);
            $this->fill($L, $g, 'Phách là nhạc cụ ___ dùng để giữ nhịp cho dàn nhạc.', [[0, 'gõ']],
                'Phách gõ nhịp — nhạc cụ gõ đơn giản nhất.', $d);
        }
    }

    private function seedAnNhacCu91(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Quan họ là làn điệu dân ca của tỉnh nào?',
                ['Bắc Ninh', 'Thừa Thiên Huế', 'Nghệ An', 'Cần Thơ'], 0,
                'Dân ca quan họ có nguồn gốc từ tỉnh Bắc Ninh.', $d);
            $this->quiz($L, $g, 'Dân ca quan họ được UNESCO công nhận là di sản vào năm nào?',
                ['2009', '2013', '2015', '2003'], 0,
                'Quan họ Bắc Ninh được UNESCO công nhận năm 2009.', $d);
            $this->quiz($L, $g, 'Ca trù còn được gọi với tên nào?',
                ['Hát ả đào', 'Hát chèo', 'Hát xẩm', 'Hát văn'], 0,
                'Ca trù còn gọi là hát ả đào, hát nói, hát thờ.', $d);
            $this->quiz($L, $g, 'Đờn ca tài tử là nghệ thuật truyền thống của vùng nào?',
                ['Nam Bộ', 'Bắc Bộ', 'Tây Nguyên', 'Miền Trung'], 0,
                'Đờn ca tài tử Nam Bộ được UNESCO công nhận năm 2013.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi loại hình nghệ thuật với đặc điểm của nó.',
                [['Quan họ', 'Bắc Ninh – hát giao duyên đối đáp'],
                 ['Chèo', 'Sân khấu dân gian đồng bằng Bắc Bộ'],
                 ['Ca trù', 'Hát ả đào, hát nói'],
                 ['Xẩm', 'Hát của người khiếm thị']],
                'Mỗi loại hình có không gian diễn xướng riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi di sản với năm UNESCO công nhận.',
                [['Quan họ', 'Di sản UNESCO 2009'],
                 ['Ca trù', 'Di sản UNESCO 2009'],
                 ['Đờn ca tài tử', 'Di sản UNESCO 2013'],
                 ['Cồng chiêng Tây Nguyên', 'Di sản UNESCO 2008']],
                'Việt Nam có nhiều di sản văn hóa phi vật thể được UNESCO vinh danh.', $d);
            $this->matching($L, $g, 'Nối mỗi vai diễn với giới tính người diễn.',
                [['Liền anh', 'Nam hát quan họ'], ['Liền chị', 'Nữ hát quan họ'],
                 ['Đào', 'Diễn viên nữ trong chèo'], ['Kép', 'Diễn viên nam trong chèo']],
                'Quan họ hát đối đáp nam – nữ; chèo có đào – kép.', $d);
            $this->matching($L, $g, 'Nối mỗi làn điệu với quê hương của nó.',
                [['Nhã nhạc', 'Cung đình Huế'], ['Hát xoan', 'Phú Thọ'],
                 ['Ví giặm', 'Nghệ An – Hà Tĩnh'], ['Hát then', 'Người Tày – Nùng']],
                'Mỗi vùng miền có làn điệu dân ca đặc trưng.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi loại hình vào nhóm theo miền.',
                [['Quan họ', 'Miền Bắc'], ['Chèo', 'Miền Bắc'],
                 ['Đờn ca tài tử', 'Miền Nam'], ['Hò Huế', 'Miền Trung']],
                'Quan họ, chèo – Bắc Bộ; hò Huế – Trung Bộ; đờn ca tài tử – Nam Bộ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Quan họ có nguồn gốc từ Bắc Ninh', 'Đúng'],
                 ['Ca trù còn gọi là hát ả đào', 'Đúng'],
                 ['Chèo là sân khấu của Nam Bộ', 'Sai'],
                 ['Xẩm là hát của người khiếm thị', 'Đúng']],
                'Chèo là sân khấu dân gian đồng bằng Bắc Bộ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi loại hình vào nhóm HÁT DÂN CA hoặc SÂN KHẤU.',
                [['Quan họ', 'Hát dân ca'], ['Ca trù', 'Hát dân ca'],
                 ['Chèo', 'Sân khấu'], ['Tuồng', 'Sân khấu']],
                'Quan họ, ca trù là hát; chèo, tuồng là sân khấu có diễn xuất.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi loại hình vào nhóm HÁT CÓ NHẠC CỤ ĐỆM hoặc HÁT CHAY.',
                [['Ca trù', 'Có nhạc cụ đệm'], ['Chèo', 'Có nhạc cụ đệm'],
                 ['Quan họ', 'Hát đối đáp ít nhạc cụ'], ['Hát ru', 'Hát chay không nhạc cụ']],
                'Ca trù có phách, trống chầu đệm; hát ru thường hát chay.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Quan họ là làn điệu dân ca của tỉnh Bắc ___.', [[0, 'Ninh']],
                'Dân ca quan họ Bắc Ninh — di sản UNESCO 2009.', $d);
            $this->fill($L, $g, 'Ca trù còn được gọi là hát ___ đào.', [[0, 'ả']],
                'Hát ả đào: ca nương (ả đào) hát, kép đàn đáy đệm.', $d);
            $this->fill($L, $g, 'Chèo là loại hình sân khấu dân gian của đồng bằng ___ Bộ.', [[0, 'Bắc']],
                'Chèo ra đời và phát triển ở đồng bằng Bắc Bộ.', $d);
            $this->fill($L, $g, 'Đờn ca tài tử là nghệ thuật truyền thống của vùng ___ Bộ.', [[0, 'Nam']],
                'Đờn ca tài tử Nam Bộ — di sản UNESCO 2013.', $d);
        }
    }

    private function seedAnNhacCu92(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Violin thuộc nhóm nhạc cụ nào?',
                ['Dây kéo', 'Dây gảy', 'Hơi', 'Gõ'], 0,
                'Violin có 4 dây, dùng vĩ kéo — đứng đầu bộ dây dàn nhạc giao hưởng.', $d);
            $this->quiz($L, $g, 'Người chỉ huy dàn nhạc giao hưởng được gọi là gì?',
                ['Nhạc trưởng', 'Ca sĩ chính', 'Nhạc công', 'Khán giả'], 0,
                'Nhạc trưởng dùng đũa chỉ huy điều khiển toàn bộ dàn nhạc.', $d);
            $this->quiz($L, $g, 'Đàn piano tạo ra âm thanh bằng cách nào?',
                ['Búa gõ vào dây đàn', 'Thổi hơi vào ống', 'Gảy dây bằng tay', 'Kéo vĩ trên dây'], 0,
                'Nhấn phím piano làm búa gõ vào dây — piano thuộc nhóm phím dây.', $d);
            $this->quiz($L, $g, 'Nhạc cụ nào sau đây thuộc bộ đồng (brass)?',
                ['Trumpet', 'Violin', 'Flute', 'Clarinet'], 0,
                'Trumpet (kèn trom-pet) làm bằng đồng, dùng hơi thổi — thuộc bộ đồng.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi nhạc cụ phương Tây với nhóm của nó.',
                [['Violin', 'Dây kéo'], ['Piano', 'Phím – búa gõ dây'],
                 ['Trumpet', 'Đồng thổi'], ['Flute', 'Hơi – sáo ngang']],
                'Bốn nhóm chính: dây, phím, đồng, hơi.', $d);
            $this->matching($L, $g, 'Nối mỗi khái niệm Đông – Tây tương ứng.',
                [['Nhạc trưởng', 'Chỉ huy dàn nhạc giao hưởng'],
                 ['Đàn bầu', 'Nhạc cụ dây gảy Việt Nam'],
                 ['Violin', 'Nhạc cụ dây kéo phương Tây'],
                 ['Sáo trúc', 'Nhạc cụ hơi Việt Nam']],
                'Đông – Tây đều có dây, hơi, gõ nhưng cấu tạo và âm sắc khác nhau.', $d);
            $this->matching($L, $g, 'Nối mỗi dàn nhạc với đặc điểm.',
                [['Dàn nhạc giao hưởng', 'Khoảng 100 nhạc công'],
                 ['Dàn nhạc dân tộc', 'Đàn tranh, đàn bầu, sáo, nhị...'],
                 ['Ban nhạc rock', 'Guitar điện, trống jazz'],
                 ['Dàn hợp xướng', 'Hát nhiều bè không nhạc cụ hoặc ít nhạc cụ']],
                'Mỗi dàn nhạc có biên chế và âm sắc đặc trưng.', $d);
            $this->matching($L, $g, 'Nối mỗi nhạc cụ bộ dây với âm vực.',
                [['Violin', 'Dây kéo âm cao'], ['Viola', 'Dây kéo âm trung'],
                 ['Cello', 'Dây kéo âm trầm'], ['Contrabass', 'Dây kéo trầm nhất']],
                'Bộ dây giao hưởng xếp từ cao đến trầm: violin – viola – cello – contrabass.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm PHƯƠNG TÂY hoặc VIỆT NAM.',
                [['Violin', 'Phương Tây'], ['Piano', 'Phương Tây'],
                 ['Đàn bầu', 'Việt Nam'], ['Đàn tranh', 'Việt Nam']],
                'Violin, piano phương Tây; đàn bầu, đàn tranh Việt Nam.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm BỘ DÂY hoặc BỘ HƠI.',
                [['Violin', 'Bộ dây'], ['Cello', 'Bộ dây'],
                 ['Trumpet', 'Bộ hơi'], ['Flute', 'Bộ hơi']],
                'Dây: violin, cello. Hơi: trumpet (đồng), flute (gỗ).', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Piano vừa có phím vừa có dây', 'Đúng'],
                 ['Nhạc trưởng chỉ huy dàn nhạc', 'Đúng'],
                 ['Trumpet thuộc bộ dây', 'Sai'],
                 ['Flute là nhạc cụ gõ', 'Sai']],
                'Trumpet thuộc bộ đồng; flute thuộc bộ hơi.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nhạc cụ vào nhóm BỘ DÂY, BỘ HƠI hoặc BỘ GÕ.',
                [['Violin', 'Bộ dây'], ['Trumpet', 'Bộ hơi'],
                 ['Trống định âm', 'Bộ gõ'], ['Clarinet', 'Bộ hơi']],
                'Dàn nhạc giao hưởng có 4 bộ: dây, hơi gỗ, hơi đồng, gõ.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Người chỉ huy dàn nhạc giao hưởng được gọi là nhạc ___.', [[0, 'trưởng']],
                'Nhạc trưởng điều khiển nhịp độ, cường độ của cả dàn nhạc.', $d);
            $this->fill($L, $g, 'Đàn piano phát ra âm thanh nhờ búa gõ vào ___.', [[0, 'dây']],
                'Mỗi phím piano nối với một búa gõ vào dây tương ứng.', $d);
            $this->fill($L, $g, 'Kèn trumpet thuộc bộ nhạc cụ bằng ___.', [[0, 'đồng']],
                'Bộ đồng (brass): trumpet, trombone, tuba... đều làm bằng đồng thau.', $d);
            $this->fill($L, $g, 'Đàn ___ là nhạc cụ dây kéo nổi tiếng nhất của phương Tây.', [[0, 'violin']],
                'Violin giữ vai trò chủ đạo trong dàn nhạc giao hưởng.', $d);
        }
    }

    // ================= MỸ THUẬT: màu sắc =================

    private function seedMtMau61(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Ba màu cơ bản là những màu nào?',
                ['Đỏ, vàng, lam', 'Đỏ, cam, tím', 'Vàng, lục, lam', 'Cam, tím, lục'], 0,
                'Ba màu cơ bản: đỏ, vàng, lam — không pha được từ màu khác.', $d);
            $this->quiz($L, $g, 'Pha màu đỏ với màu vàng ta được màu gì?',
                ['Màu cam', 'Màu tím', 'Màu lục', 'Màu hồng'], 0,
                'Đỏ + vàng = cam (màu thứ cấp).', $d);
            $this->quiz($L, $g, 'Pha màu vàng với màu lam ta được màu gì?',
                ['Màu lục (xanh lá)', 'Màu cam', 'Màu tím', 'Màu nâu'], 0,
                'Vàng + lam = lục, tức màu xanh lá cây.', $d);
            $this->quiz($L, $g, 'Màu nào sau đây KHÔNG phải màu cơ bản?',
                ['Màu tím', 'Màu đỏ', 'Màu vàng', 'Màu lam'], 0,
                'Màu tím là màu thứ cấp, pha từ lam + đỏ.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi cách pha với màu thu được.',
                [['Đỏ + Vàng', 'Màu cam'], ['Vàng + Lam', 'Màu lục'],
                 ['Lam + Đỏ', 'Màu tím'], ['Đỏ + Trắng', 'Màu hồng']],
                'Ba màu thứ cấp: cam, lục, tím; đỏ + trắng = hồng.', $d);
            $this->matching($L, $g, 'Nối mỗi màu với loại của nó.',
                [['Màu đỏ', 'Màu cơ bản'], ['Màu vàng', 'Màu cơ bản'],
                 ['Màu cam', 'Màu thứ cấp'], ['Màu tím', 'Màu thứ cấp']],
                'Cơ bản: đỏ, vàng, lam. Thứ cấp: cam, lục, tím.', $d);
            $this->matching($L, $g, 'Nối mỗi khái niệm với ý nghĩa.',
                [['Pha màu', 'Trộn hai màu với nhau'],
                 ['Màu cơ bản', 'Không pha được từ màu khác'],
                 ['Màu thứ cấp', 'Pha từ hai màu cơ bản'],
                 ['Vòng tròn màu', 'Bảng sắp xếp màu sắc']],
                'Hiểu phân loại màu giúp pha màu chính xác.', $d);
            $this->matching($L, $g, 'Nối mỗi tên màu với cách gọi quen thuộc.',
                [['Màu lục', 'Xanh lá cây'], ['Màu lam', 'Xanh dương'],
                 ['Màu cam', 'Vàng đỏ'], ['Màu chàm', 'Xanh đậm']],
                'Lục = xanh lá, lam = xanh dương, chàm = xanh đậm.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi màu vào nhóm MÀU CƠ BẢN hoặc MÀU THỨ CẤP.',
                [['Màu đỏ', 'Màu cơ bản'], ['Màu vàng', 'Màu cơ bản'],
                 ['Màu cam', 'Màu thứ cấp'], ['Màu tím', 'Màu thứ cấp']],
                'Cơ bản: đỏ, vàng, lam. Thứ cấp: cam, lục, tím.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Đỏ + vàng = cam', 'Đúng'], ['Có 3 màu cơ bản', 'Đúng'],
                 ['Vàng + lam = tím', 'Sai'], ['Tím là màu cơ bản', 'Sai']],
                'Vàng + lam = lục; tím là màu thứ cấp.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi màu vào nhóm MÀU CƠ BẢN hoặc MÀU THỨ CẤP.',
                [['Màu lam', 'Màu cơ bản'], ['Màu đỏ', 'Màu cơ bản'],
                 ['Màu lục', 'Màu thứ cấp'], ['Màu cam', 'Màu thứ cấp']],
                'Lam là cơ bản; lục, cam là thứ cấp.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi vật vào nhóm có màu CƠ BẢN hoặc màu THỨ CẤP.',
                [['Quả cà chua chín', 'Màu cơ bản'], ['Bông hoa hướng dương', 'Màu cơ bản'],
                 ['Chiếc lá xanh', 'Màu thứ cấp'], ['Quả cà tím', 'Màu thứ cấp']],
                'Cà chua đỏ, hướng dương vàng (cơ bản); lá xanh lục, cà tím (thứ cấp).', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Ba màu cơ bản là đỏ, vàng và ___.', [[0, 'lam']],
                'Đỏ, vàng, lam là 3 màu cơ bản.', $d);
            $this->fill($L, $g, 'Pha màu đỏ với màu vàng ta được màu ___.', [[0, 'cam']],
                'Đỏ + vàng = cam.', $d);
            $this->fill($L, $g, 'Màu tím được pha từ màu lam và màu ___.', [[0, 'đỏ']],
                'Lam + đỏ = tím.', $d);
            $this->fill($L, $g, 'Màu ___ là màu thứ cấp được pha từ vàng và lam.', [[0, 'lục']],
                'Vàng + lam = lục (xanh lá cây).', $d);
        }
    }

    private function seedMtMau62(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Nhóm màu nào sau đây là màu nóng?',
                ['Đỏ, cam, vàng', 'Lam, lục, tím', 'Đen, trắng, xám', 'Hồng, nâu, be'], 0,
                'Màu nóng: đỏ, cam, vàng — gợi cảm giác ấm áp, sôi động.', $d);
            $this->quiz($L, $g, 'Màu nào sau đây gợi cảm giác mát mẻ nhất?',
                ['Màu lam', 'Màu đỏ', 'Màu cam', 'Màu vàng'], 0,
                'Màu lam (xanh dương) gợi cảm giác mát mẻ như nước, bầu trời.', $d);
            $this->quiz($L, $g, 'Màu đỏ thường gợi cảm xúc gì?',
                ['Sôi động, nhiệt huyết', 'Buồn bã, ảm đạm', 'Lạnh lẽo, xa cách', 'Sợ hãi, lo lắng'], 0,
                'Màu đỏ gợi sự sôi động, nhiệt huyết, mạnh mẽ.', $d);
            $this->quiz($L, $g, 'Vẽ tranh phong cảnh biển nên dùng gam màu nào là hợp lý nhất?',
                ['Gam màu lạnh', 'Gam màu nóng', 'Chỉ dùng màu đen', 'Chỉ dùng màu trắng'], 0,
                'Biển, nước hợp với gam lạnh (lam, lục) tạo cảm giác mát mẻ.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi màu với cảm giác nó gợi ra.',
                [['Màu đỏ', 'Nóng – sôi động'], ['Màu cam', 'Nóng – ấm áp'],
                 ['Màu lam', 'Lạnh – mát mẻ'], ['Màu tím', 'Lạnh – trầm lắng']],
                'Màu nóng gợi ấm áp sôi động; màu lạnh gợi mát mẻ trầm lắng.', $d);
            $this->matching($L, $g, 'Nối mỗi hình ảnh với gam màu phù hợp.',
                [['Mặt trời', 'Màu nóng'], ['Ngọn lửa', 'Màu nóng'],
                 ['Nước biển', 'Màu lạnh'], ['Bầu trời đêm', 'Màu lạnh']],
                'Mặt trời, lửa — nóng; biển, đêm — lạnh.', $d);
            $this->matching($L, $g, 'Nối mỗi màu với cảm xúc thường gặp.',
                [['Màu vàng', 'Vui tươi'], ['Màu lục', 'Tươi mát'],
                 ['Màu hồng', 'Dịu dàng'], ['Màu nâu', 'Gần gũi, mộc mạc']],
                'Màu sắc ảnh hưởng đến cảm xúc người xem.', $d);
            $this->matching($L, $g, 'Nối mỗi mục đích với gam màu nên dùng.',
                [['Quảng cáo nước giải khát mát', 'Dùng màu lạnh'],
                 ['Quảng cáo lẩu cay', 'Dùng màu nóng'],
                 ['Tranh mùa hè rực rỡ', 'Gam màu nóng'],
                 ['Tranh mùa đông', 'Gam màu lạnh']],
                'Chọn gam màu phù hợp giúp truyền tải đúng thông điệp.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi màu vào nhóm MÀU NÓNG hoặc MÀU LẠNH.',
                [['Màu đỏ', 'Màu nóng'], ['Màu cam', 'Màu nóng'],
                 ['Màu lam', 'Màu lạnh'], ['Màu lục', 'Màu lạnh']],
                'Nóng: đỏ, cam, vàng. Lạnh: lam, lục, tím.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Màu nóng gợi cảm giác ấm áp', 'Đúng'],
                 ['Màu lạnh gợi cảm giác mát mẻ', 'Đúng'],
                 ['Màu lam là màu nóng', 'Sai'],
                 ['Màu vàng là màu lạnh', 'Sai']],
                'Lam là lạnh, vàng là nóng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi màu vào nhóm gợi CẢM XÚC MẠNH hoặc CẢM XÚC NHẸ NHÀNG.',
                [['Màu đỏ', 'Cảm xúc mạnh'], ['Màu cam đậm', 'Cảm xúc mạnh'],
                 ['Màu lam nhạt', 'Cảm xúc nhẹ nhàng'], ['Màu lục nhạt', 'Cảm xúc nhẹ nhàng']],
                'Màu nóng đậm gây ấn tượng mạnh; màu lạnh nhạt tạo cảm giác nhẹ nhàng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hình ảnh vào nhóm MÀU NÓNG hoặc MÀU LẠNH.',
                [['Ngọn lửa', 'Màu nóng'], ['Mặt trời', 'Màu nóng'],
                 ['Dòng sông', 'Màu lạnh'], ['Tảng băng', 'Màu lạnh']],
                'Lửa, mặt trời — nóng; sông, băng — lạnh.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Ba màu nóng là đỏ, cam và ___.', [[0, 'vàng']],
                'Đỏ, cam, vàng là 3 màu nóng.', $d);
            $this->fill($L, $g, 'Màu ___ gợi cảm giác mát mẻ, yên tĩnh.', [[0, 'lam']],
                'Màu lam như màu nước biển, bầu trời.', $d);
            $this->fill($L, $g, 'Ngọn lửa thường được vẽ bằng gam màu ___.', [[0, 'nóng']],
                'Lửa: đỏ, cam, vàng — gam màu nóng.', $d);
            $this->fill($L, $g, 'Nhóm màu lạnh gồm lam, lục và ___.', [[0, 'tím']],
                'Lam, lục, tím là 3 màu lạnh.', $d);
        }
    }

    private function seedMtMau71(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Thêm màu trắng vào màu đỏ ta được màu gì?',
                ['Màu đỏ nhạt (hồng)', 'Màu đỏ đậm', 'Màu cam', 'Màu tím'], 0,
                'Thêm trắng làm màu nhạt dần: đỏ + trắng = hồng.', $d);
            $this->quiz($L, $g, 'Cặp màu tương phản của màu đỏ là màu nào?',
                ['Màu lục', 'Màu cam', 'Màu vàng', 'Màu tím'], 0,
                'Đỏ – lục là cặp màu đối diện nhau trên vòng tròn màu.', $d);
            $this->quiz($L, $g, 'Cặp màu tương phản của màu vàng là màu nào?',
                ['Màu tím', 'Màu cam', 'Màu lục', 'Màu lam'], 0,
                'Vàng – tím đối diện nhau trên vòng tròn màu.', $d);
            $this->quiz($L, $g, 'Sắc độ của màu sắc là gì?',
                ['Độ đậm nhạt của màu', 'Độ sáng của đèn',
                 'Kích thước của mảng màu', 'Hình dáng của nét vẽ'], 0,
                'Sắc độ chỉ độ đậm nhạt: thêm đen thì đậm, thêm trắng thì nhạt.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi cặp màu với quan hệ của chúng.',
                [['Đỏ – Lục', 'Cặp tương phản'], ['Vàng – Tím', 'Cặp tương phản'],
                 ['Cam – Lam', 'Cặp tương phản'], ['Đỏ – Cam', 'Cặp gần nhau']],
                'Tương phản: đỏ–lục, vàng–tím, cam–lam. Gần nhau: đỏ–cam.', $d);
            $this->matching($L, $g, 'Nối mỗi cách pha với kết quả sắc độ.',
                [['Thêm màu trắng', 'Màu nhạt dần'], ['Thêm màu đen', 'Màu đậm dần'],
                 ['Thêm màu xám', 'Màu trầm xuống'], ['Không pha thêm', 'Giữ màu nguyên']],
                'Trắng nhạt, đen đậm, xám trầm.', $d);
            $this->matching($L, $g, 'Nối mỗi cách dùng màu với hiệu quả.',
                [['Màu tương phản đặt cạnh nhau', 'Nổi bật, rực rỡ'],
                 ['Màu gần nhau đặt cạnh nhau', 'Hài hòa, êm dịu'],
                 ['Sắc độ đậm', 'Gây chú ý mạnh'],
                 ['Sắc độ nhạt', 'Nhẹ nhàng, thoáng đãng']],
                'Tương phản gây nổi bật; tương đồng tạo hài hòa.', $d);
            $this->matching($L, $g, 'Nối mỗi khái niệm với ý nghĩa.',
                [['Vòng tròn màu', 'Sắp xếp màu theo quang phổ'],
                 ['Màu đối diện nhau', 'Màu tương phản'],
                 ['Màu kề nhau', 'Màu tương đồng'],
                 ['Hòa sắc', 'Cách phối màu hài hòa']],
                'Vòng tròn màu là công cụ phối màu cơ bản của họa sĩ.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi cặp màu vào nhóm TƯƠNG PHẢN hoặc TƯƠNG ĐỒNG.',
                [['Đỏ – Lục', 'Tương phản'], ['Vàng – Tím', 'Tương phản'],
                 ['Đỏ – Cam', 'Tương đồng'], ['Lam – Lục', 'Tương đồng']],
                'Đối diện nhau là tương phản; kề nhau là tương đồng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi màu đã pha vào nhóm SẮC ĐỘ ĐẬM hoặc SẮC ĐỘ NHẠT.',
                [['Đỏ + đen', 'Sắc độ đậm'], ['Lam + đen', 'Sắc độ đậm'],
                 ['Vàng + trắng', 'Sắc độ nhạt'], ['Hồng phấn', 'Sắc độ nhạt']],
                'Thêm đen → đậm; thêm trắng → nhạt.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Thêm trắng làm màu nhạt đi', 'Đúng'],
                 ['Đỏ và lục là cặp tương phản', 'Đúng'],
                 ['Vàng và cam là cặp tương phản', 'Sai'],
                 ['Thêm đen làm màu nhạt đi', 'Sai']],
                'Vàng – cam kề nhau là tương đồng; thêm đen làm đậm.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cách đặt màu vào nhóm NỔI BẬT hoặc HÀI HÒA.',
                [['Đỏ đặt cạnh lục', 'Nổi bật, tương phản'],
                 ['Vàng đặt cạnh tím', 'Nổi bật, tương phản'],
                 ['Lam đặt cạnh lục', 'Hài hòa, tương đồng'],
                 ['Đỏ đặt cạnh cam', 'Hài hòa, tương đồng']],
                'Tương phản gây nổi bật; tương đồng tạo hài hòa.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Thêm màu ___ vào màu sắc sẽ làm màu nhạt dần.', [[0, 'trắng']],
                'Trắng làm nhạt, đen làm đậm.', $d);
            $this->fill($L, $g, 'Cặp màu tương phản của màu cam là màu ___.', [[0, 'lam']],
                'Cam – lam đối diện nhau trên vòng tròn màu.', $d);
            $this->fill($L, $g, 'Độ đậm nhạt của màu sắc gọi là ___ độ.', [[0, 'sắc']],
                'Sắc độ: từ nhạt (thêm trắng) đến đậm (thêm đen).', $d);
            $this->fill($L, $g, 'Hai màu đối diện nhau trên vòng tròn màu gọi là màu ___ phản.', [[0, 'tương']],
                'Màu tương phản đặt cạnh nhau sẽ làm nhau nổi bật.', $d);
        }
    }

    private function seedMtMau72(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Màu chủ đạo trong tranh là gì?',
                ['Màu chiếm diện tích lớn nhất', 'Màu đắt tiền nhất',
                 'Màu vẽ đầu tiên', 'Màu sáng nhất'], 0,
                'Màu chủ đạo chiếm diện tích lớn, quyết định cảm xúc chung của tranh.', $d);
            $this->quiz($L, $g, 'Điểm nhấn màu trong tranh có tác dụng gì?',
                ['Thu hút ánh nhìn của người xem', 'Làm tranh tối đi',
                 'Che khuyết điểm', 'Tăng giá tranh'], 0,
                'Điểm nhấn là mảng màu tương phản nhỏ, dẫn mắt người xem.', $d);
            $this->quiz($L, $g, 'Tranh Đông Hồ thường dùng màu lấy từ đâu?',
                ['Từ thiên nhiên', 'Từ hóa chất công nghiệp', 'Từ màu nước ngoại', 'Từ phẩm nhuộm vải'], 0,
                'Tranh Đông Hồ dùng màu tự nhiên: than, gạch non, hoa hòe...', $d);
            $this->quiz($L, $g, 'Giấy điệp trong tranh Đông Hồ được làm từ gì?',
                ['Vỏ sò điệp giã nhỏ', 'Vỏ cây dó', 'Rơm rạ', 'Bột giấy công nghiệp'], 0,
                'Vỏ sò điệp giã nhỏ quét lên giấy dó tạo độ óng ánh cho giấy điệp.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi thành phần màu với vai trò trong tranh.',
                [['Màu chủ đạo', 'Quyết định cảm xúc chung'],
                 ['Điểm nhấn màu', 'Thu hút ánh nhìn'],
                 ['Màu nền', 'Tạo không gian'],
                 ['Màu phụ', 'Hỗ trợ màu chính']],
                'Bốn vai trò phối hợp tạo nên bố cục màu hoàn chỉnh.', $d);
            $this->matching($L, $g, 'Nối mỗi nguyên liệu với màu nó tạo ra trong tranh Đông Hồ.',
                [['Than củi', 'Màu đen'], ['Gạch non', 'Màu đỏ'],
                 ['Hoa hòe', 'Màu vàng'], ['Vỏ sò điệp', 'Giấy điệp óng ánh']],
                'Màu tự nhiên bền, trầm ấm — nét riêng của tranh Đông Hồ.', $d);
            $this->matching($L, $g, 'Nối mỗi nội dung tranh với màu chủ đạo phù hợp.',
                [['Tranh mùa xuân', 'Màu chủ đạo tươi sáng'],
                 ['Tranh mùa thu', 'Màu chủ đạo vàng cam'],
                 ['Tranh đêm trăng', 'Màu chủ đạo tối'],
                 ['Tranh biển', 'Màu chủ đạo lam']],
                'Màu chủ đạo phải phù hợp với nội dung và cảm xúc tranh.', $d);
            $this->matching($L, $g, 'Nối mỗi kiểu bố cục màu với đặc điểm.',
                [['Bố cục màu cân đối', 'Màu phân bố đều'],
                 ['Bố cục màu lệch', 'Điểm nhấn đặt một phía'],
                 ['Bố cục tương phản', 'Màu đối lập mạnh'],
                 ['Bố cục đơn sắc', 'Một màu với nhiều sắc độ']],
                'Các kiểu bố cục màu tạo hiệu quả thị giác khác nhau.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi mảng màu vào nhóm MÀU CHỦ ĐẠO hoặc ĐIỂM NHẤN.',
                [['Màu chiếm diện tích lớn', 'Màu chủ đạo'],
                 ['Màu xanh da trời phủ rộng', 'Màu chủ đạo'],
                 ['Mảng đỏ nhỏ nhưng nổi bật', 'Điểm nhấn'],
                 ['Chấm vàng nhỏ giữa tranh', 'Điểm nhấn']],
                'Chủ đạo: diện tích lớn. Điểm nhấn: nhỏ nhưng tương phản, nổi bật.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Màu chủ đạo quyết định cảm xúc chung của tranh', 'Đúng'],
                 ['Tranh Đông Hồ dùng màu tự nhiên', 'Đúng'],
                 ['Điểm nhấn màu nên chiếm diện tích lớn', 'Sai'],
                 ['Màu nền không quan trọng trong tranh', 'Sai']],
                'Điểm nhấn phải nhỏ mới "nhấn"; màu nền tạo không gian rất quan trọng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nguyên liệu vào nhóm MÀU TỰ NHIÊN hoặc MÀU CÔNG NGHIỆP.',
                [['Than củi', 'Màu tự nhiên'], ['Gạch non', 'Màu tự nhiên'],
                 ['Màu nước hộp', 'Màu công nghiệp'], ['Màu acrylic', 'Màu công nghiệp']],
                'Tranh dân gian dùng màu tự nhiên; màu công nghiệp tiện lợi hơn.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi bức tranh vào nhóm GAM NÓNG hoặc GAM LẠNH chủ đạo.',
                [['Tranh hoàng hôn', 'Gam nóng chủ đạo'], ['Tranh lửa trại', 'Gam nóng chủ đạo'],
                 ['Tranh đêm trăng', 'Gam lạnh chủ đạo'], ['Tranh suối mát', 'Gam lạnh chủ đạo']],
                'Hoàng hôn, lửa trại — nóng; đêm trăng, suối mát — lạnh.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Màu chiếm diện tích lớn nhất trong tranh gọi là màu ___ đạo.', [[0, 'chủ']],
                'Màu chủ đạo quyết định cảm xúc chung của bức tranh.', $d);
            $this->fill($L, $g, 'Mảng màu tương phản nhỏ thu hút mắt nhìn gọi là điểm ___ màu.', [[0, 'nhấn']],
                'Điểm nhấn dẫn mắt người xem đến chi tiết quan trọng.', $d);
            $this->fill($L, $g, 'Giấy điệp trong tranh Đông Hồ được làm từ vỏ ___ điệp.', [[0, 'sò']],
                'Vỏ sò điệp giã nhỏ tạo độ óng ánh đặc trưng.', $d);
            $this->fill($L, $g, 'Màu đỏ trong tranh Đông Hồ thường được làm từ ___ non.', [[0, 'gạch']],
                'Gạch non giã nhỏ cho màu đỏ trầm ấm.', $d);
        }
    }

    // ================= MỸ THUẬT: đường nét & bố cục =================

    private function seedMtNet71(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Nét cong thường gợi cảm giác gì?',
                ['Mềm mại, uyển chuyển', 'Cứng nhắc, khô khan', 'Gấp gáp, vội vã', 'Nặng nề, chậm chạp'], 0,
                'Nét cong như dòng sông, cầu vồng gợi sự mềm mại uyển chuyển.', $d);
            $this->quiz($L, $g, 'Nét gấp khúc (zigzag) thường gợi cảm giác gì?',
                ['Mạnh mẽ, gấp gáp', 'Dịu dàng, êm ái', 'Yên tĩnh, thanh bình', 'Buồn bã, ảm đạm'], 0,
                'Nét gấp khúc như tia chớp gợi sự mạnh mẽ, gấp gáp.', $d);
            $this->quiz($L, $g, 'Trong vẽ kỹ thuật, nét nào dùng để vẽ đường bao thấy được?',
                ['Nét liền đậm', 'Nét đứt', 'Nét chấm gạch', 'Nét lượn sóng'], 0,
                'Nét liền đậm vẽ đường bao thấy được — nét quan trọng nhất của bản vẽ.', $d);
            $this->quiz($L, $g, 'Trong vẽ kỹ thuật, nét đứt thường dùng để vẽ gì?',
                ['Đường bị khuất, không nhìn thấy', 'Đường viền ngoài',
                 'Trục đối xứng', 'Khung tên bản vẽ'], 0,
                'Nét đứt vẽ các cạnh bị khuất sau vật thể.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi loại nét với cảm giác nó gợi ra.',
                [['Nét thẳng', 'Vững chãi, chắc chắn'], ['Nét cong', 'Mềm mại, uyển chuyển'],
                 ['Nét gấp khúc', 'Mạnh mẽ, sôi động'], ['Nét đứt đoạn', 'Nhẹ nhàng, thoáng đãng']],
                'Mỗi loại nét mang một cảm xúc riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi loại nét với hiệu quả diễn tả.',
                [['Nét đậm', 'Gây chú ý, vật ở gần'], ['Nét nhạt', 'Vật ở xa, mờ'],
                 ['Nét to', 'Mạnh mẽ'], ['Nét mảnh', 'Tinh tế']],
                'Đậm nhạt, to nhỏ của nét tạo chiều sâu cho tranh.', $d);
            $this->matching($L, $g, 'Nối mỗi hình ảnh với loại nét phù hợp để vẽ.',
                [['Đường chân trời', 'Nét ngang'], ['Thân cây đứng', 'Nét dọc'],
                 ['Dòng sông uốn lượn', 'Nét cong'], ['Tia chớp', 'Nét gấp khúc']],
                'Chọn nét phù hợp giúp diễn tả đúng đặc điểm sự vật.', $d);
            $this->matching($L, $g, 'Nối mỗi loại nét kỹ thuật với công dụng.',
                [['Nét liền đậm', 'Vẽ đường bao thấy được'], ['Nét đứt', 'Vẽ đường bị khuất'],
                 ['Nét chấm gạch', 'Vẽ trục đối xứng'], ['Nét lượn sóng', 'Vẽ đường dạo, trang trí']],
                'Bản vẽ kỹ thuật dùng các loại nét chuẩn để mọi người cùng hiểu.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi vật vào nhóm vẽ bằng NÉT THẲNG hoặc NÉT CONG.',
                [['Cột nhà', 'Nét thẳng'], ['Thân cây thẳng', 'Nét thẳng'],
                 ['Cầu vồng', 'Nét cong'], ['Con sóng', 'Nét cong']],
                'Vật cứng, thẳng dùng nét thẳng; vật mềm, uốn lượn dùng nét cong.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nét cong gợi cảm giác mềm mại', 'Đúng'],
                 ['Nét đậm thường vẽ vật ở gần', 'Đúng'],
                 ['Nét gấp khúc gợi cảm giác dịu dàng', 'Sai'],
                 ['Nét đứt dùng vẽ đường bao thấy được', 'Sai']],
                'Nét gấp khúc gợi mạnh mẽ; đường bao thấy được vẽ bằng nét liền đậm.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi loại nét vào nhóm gợi cảm giác ÊM DỊU hoặc MẠNH MẼ.',
                [['Nét cong', 'Êm dịu'], ['Nét lượn sóng', 'Êm dịu'],
                 ['Nét gấp khúc', 'Mạnh mẽ'], ['Nét chéo dứt khoát', 'Mạnh mẽ']],
                'Nét mềm gợi êm dịu; nét gãy, chéo gợi mạnh mẽ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi loại nét kỹ thuật vào nhóm vẽ ĐƯỜNG NHÌN THẤY hoặc ĐƯỜNG BỊ KHUẤT.',
                [['Nét liền đậm', 'Đường nhìn thấy'], ['Nét liền mảnh (đường bao)', 'Đường nhìn thấy'],
                 ['Nét đứt', 'Đường bị khuất'], ['Nét đứt (cạnh khuất)', 'Đường bị khuất']],
                'Thấy được vẽ nét liền; bị khuất vẽ nét đứt.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Đường nét ___ thường gợi cảm giác mềm mại, uyển chuyển.', [[0, 'cong']],
                'Nét cong như sóng nước, cầu vồng.', $d);
            $this->fill($L, $g, 'Nét ___ khúc gợi cảm giác mạnh mẽ, gấp gáp.', [[0, 'gấp']],
                'Nét gấp khúc như tia chớp, đường zigzag.', $d);
            $this->fill($L, $g, 'Trong vẽ kỹ thuật, đường bị khuất được vẽ bằng nét ___.', [[0, 'đứt']],
                'Nét đứt là quy ước quốc tế cho đường khuất.', $d);
            $this->fill($L, $g, 'Nét ___ đậm dùng để vẽ đường bao thấy được của vật thể.', [[0, 'liền']],
                'Nét liền đậm là nét chính của bản vẽ kỹ thuật.', $d);
        }
    }

    private function seedMtNet72(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Bố cục đối xứng là gì?',
                ['Hai bên giống nhau qua một trục', 'Xếp ngẫu nhiên không quy tắc',
                 'Chỉ vẽ một bên tranh', 'Vẽ nhiều màu sắc'], 0,
                'Đối xứng: hai nửa tranh giống nhau qua trục giữa.', $d);
            $this->quiz($L, $g, 'Điểm nhấn trong tranh có tác dụng gì?',
                ['Thu hút ánh nhìn đầu tiên của người xem', 'Làm tranh tối đi',
                 'Tăng kích thước tranh', 'Giảm số màu dùng'], 0,
                'Điểm nhấn dẫn mắt người xem đến chi tiết quan trọng nhất.', $d);
            $this->quiz($L, $g, 'Bố cục nào tạo cảm giác trang nghiêm, ổn định nhất?',
                ['Bố cục đối xứng', 'Bố cục tự do', 'Bố cục lệch', 'Bố cục ngẫu nhiên'], 0,
                'Đối xứng tạo cảm giác trang nghiêm — thường dùng cho tranh thờ, logo.', $d);
            $this->quiz($L, $g, 'Để bức tranh cân bằng, các mảng màu – hình nên được sắp xếp thế nào?',
                ['Nặng nhẹ phân bố hợp lý', 'Dồn hết về một góc',
                 'Chỉ dùng một màu', 'Vẽ càng nhiều càng tốt'], 0,
                'Cân bằng: mảng đậm một bên cần có mảng đối trọng bên kia.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi khái niệm với ý nghĩa.',
                [['Bố cục đối xứng', 'Hai bên giống nhau qua trục'],
                 ['Bố cục tự do', 'Sắp xếp linh hoạt'],
                 ['Điểm nhấn', 'Thu hút mắt nhìn'],
                 ['Cân bằng', 'Nặng nhẹ phân bố hợp lý']],
                'Bốn khái niệm cơ bản của bố cục tranh.', $d);
            $this->matching($L, $g, 'Nối mỗi loại tranh với bố cục thường dùng.',
                [['Tranh thờ', 'Bố cục đối xứng'], ['Logo', 'Thường đối xứng'],
                 ['Tranh phong cảnh', 'Bố cục tự do'], ['Tranh trừu tượng', 'Bố cục tự do']],
                'Tranh thờ, logo cần trang nghiêm → đối xứng; phong cảnh cần tự nhiên → tự do.', $d);
            $this->matching($L, $g, 'Nối mỗi cách đặt điểm nhấn với hiệu quả.',
                [['Điểm nhấn ở giữa', 'Cảm giác ổn định'],
                 ['Điểm nhấn lệch 1/3', 'Cảm giác chuyển động, đẹp'],
                 ['Không có điểm nhấn', 'Tranh nhạt nhòa'],
                 ['Mảng lớn một bên không đối trọng', 'Mất cân bằng']],
                'Vị trí điểm nhấn quyết định nhịp điệu của tranh.', $d);
            $this->matching($L, $g, 'Nối mỗi vị trí với vai trò trong bố cục.',
                [['Vị trí 1/3 tranh', 'Điểm nhấn đẹp'], ['Chính giữa', 'Vị trí trang trọng'],
                 ['Góc tranh', 'Vị trí phụ'], ['Đường chân trời ở 1/3', 'Bố cục đẹp']],
                'Quy tắc 1/3 là bí quyết bố cục của nhiều họa sĩ.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi hình vào nhóm BỐ CỤC ĐỐI XỨNG hoặc BỐ CỤC TỰ DO.',
                [['Mặt người vẽ chính diện', 'Đối xứng'], ['Con bướm', 'Đối xứng'],
                 ['Dòng sông uốn lượn', 'Tự do'], ['Đám mây', 'Tự do']],
                'Mặt người, bướm đối xứng; sông, mây tự do.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Bố cục đối xứng tạo cảm giác trang nghiêm', 'Đúng'],
                 ['Điểm nhấn thu hút ánh nhìn', 'Đúng'],
                 ['Mọi bức tranh đều phải đối xứng', 'Sai'],
                 ['Bố cục không quan trọng trong vẽ tranh', 'Sai']],
                'Bố cục tự do cũng đẹp; bố cục luôn quan trọng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi vị trí vào nhóm VỊ TRÍ MẠNH hoặc VỊ TRÍ YẾU.',
                [['Chính giữa tranh', 'Vị trí mạnh'], ['Điểm 1/3 tranh', 'Vị trí mạnh'],
                 ['Sát mép tranh', 'Vị trí yếu'], ['Góc khuất', 'Vị trí yếu']],
                'Giữa và 1/3 là vị trí mạnh; sát mép, góc khuất là vị trí yếu.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cách sắp xếp vào nhóm CÂN BẰNG hoặc MẤT CÂN BẰNG.',
                [['Hai bên nặng đều nhau', 'Cân bằng'],
                 ['Mảng đậm có mảng nhạt đối trọng', 'Cân bằng'],
                 ['Mọi chi tiết dồn về một góc', 'Mất cân bằng'],
                 ['Một bên nặng, một bên trống trơn', 'Mất cân bằng']],
                'Cân bằng không nhất thiết đối xứng — chỉ cần nặng nhẹ hợp lý.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Cách sắp xếp các hình mảng trong tranh gọi là ___ cục.', [[0, 'bố']],
                'Bố cục quyết định nhịp điệu và cảm xúc của tranh.', $d);
            $this->fill($L, $g, 'Bố cục ___ xứng có hai bên giống nhau qua một trục.', [[0, 'đối']],
                'Đối xứng tạo cảm giác trang nghiêm, ổn định.', $d);
            $this->fill($L, $g, 'Vị trí thu hút mắt nhìn nhất trong tranh gọi là điểm ___.', [[0, 'nhấn']],
                'Điểm nhấn là "trái tim" của bức tranh.', $d);
            $this->fill($L, $g, 'Đặt đường chân trời ở vị trí ___ tranh thường tạo bố cục đẹp.', [[0, '1/3']],
                'Quy tắc 1/3: tránh đặt đường chân trời chính giữa.', $d);
        }
    }

    private function seedMtNet81(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Quả cam gần với khối cơ bản nào nhất?',
                ['Khối cầu', 'Khối hộp', 'Khối trụ', 'Khối nón'], 0,
                'Quả cam tròn — gần với khối cầu nhất.', $d);
            $this->quiz($L, $g, 'Lon sữa bò gần với khối cơ bản nào nhất?',
                ['Khối trụ', 'Khối cầu', 'Khối nón', 'Khối chóp'], 0,
                'Lon sữa hình trụ tròn — gần với khối trụ.', $d);
            $this->quiz($L, $g, 'Trong phối cảnh, vật ở càng xa thì được vẽ thế nào?',
                ['Nhỏ lại', 'To ra', 'Không đổi', 'Biến mất'], 0,
                'Phối cảnh: gần to — xa nhỏ, đó là quy luật thị giác.', $d);
            $this->quiz($L, $g, 'Bóng đổ của vật nằm ở phía nào so với nguồn sáng?',
                ['Phía đối diện với nguồn sáng', 'Cùng phía với nguồn sáng',
                 'Phía trên vật', 'Không có bóng đổ'], 0,
                'Ánh sáng chiếu tới đâu, bóng đổ in phía đối diện.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi vật với khối cơ bản gần nhất.',
                [['Quả bóng', 'Khối cầu'], ['Hộp sữa', 'Khối hộp'],
                 ['Lon nước', 'Khối trụ'], ['Nón lá', 'Khối nón']],
                'Mọi vật thể đều cấu tạo từ các khối cơ bản.', $d);
            $this->matching($L, $g, 'Nối mỗi khái niệm phối cảnh với ý nghĩa.',
                [['Đường chân trời', 'Đường phân chia trời và đất'],
                 ['Điểm tụ', 'Nơi các đường song song gặp nhau'],
                 ['Vật gần', 'Vẽ to'], ['Vật xa', 'Vẽ nhỏ']],
                'Phối cảnh giúp tranh có chiều sâu không gian.', $d);
            $this->matching($L, $g, 'Nối mỗi phần của vật với đặc điểm ánh sáng.',
                [['Phần sáng', 'Hướng về nguồn sáng'],
                 ['Phần tối', 'Quay lưng về nguồn sáng'],
                 ['Bóng đổ', 'In trên mặt đất'],
                 ['Phản quang', 'Sáng nhẹ ở mép phần tối']],
                'Sáng – tối – bóng đổ – phản quang tạo khối cho vật.', $d);
            $this->matching($L, $g, 'Nối mỗi cách vẽ với ý nghĩa.',
                [['Vẽ theo mẫu', 'Quan sát vật thật để vẽ'],
                 ['Ký họa', 'Vẽ nhanh ghi lại hình ảnh'],
                 ['Phác thảo', 'Vẽ nháp bố cục'],
                 ['Tô bóng', 'Tạo khối bằng sáng tối']],
                'Vẽ theo mẫu rèn khả năng quan sát — kỹ năng quan trọng nhất.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi vật vào nhóm KHỐI TRÒN hoặc KHỐI VUÔNG.',
                [['Quả cam', 'Khối tròn'], ['Quả bóng', 'Khối tròn'],
                 ['Hộp quà', 'Khối vuông'], ['Quyển sách', 'Khối vuông']],
                'Tròn: cầu, trụ. Vuông: hộp.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Vật càng xa càng vẽ nhỏ', 'Đúng'],
                 ['Mọi vật đều gồm các khối cơ bản', 'Đúng'],
                 ['Bóng đổ nằm cùng phía với nguồn sáng', 'Sai'],
                 ['Vẽ theo mẫu không cần quan sát kỹ', 'Sai']],
                'Bóng đổ đối diện nguồn sáng; vẽ mẫu phải quan sát kỹ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phần vào nhóm CÓ ÁNH SÁNG hoặc KHÔNG CÓ ÁNH SÁNG.',
                [['Phần sáng', 'Có ánh sáng trực tiếp'],
                 ['Phản quang', 'Có ánh sáng gián tiếp'],
                 ['Phần tối', 'Không có ánh sáng'],
                 ['Bóng đổ', 'Không có ánh sáng']],
                'Phản quang là ánh sáng hắt từ môi trường vào phần tối.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi vật trong tranh phong cảnh vào nhóm VẼ TO (gần) hoặc VẼ NHỎ (xa).',
                [['Người đứng gần', 'Vẽ to'], ['Cây trước mặt', 'Vẽ to'],
                 ['Ngôi nhà xa', 'Vẽ nhỏ'], ['Núi xa tít', 'Vẽ nhỏ']],
                'Gần to — xa nhỏ là quy luật phối cảnh cơ bản.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Quả địa cầu gần với khối ___.', [[0, 'cầu']],
                'Quả địa cầu hình cầu.', $d);
            $this->fill($L, $g, 'Trong phối cảnh, vật ở càng xa thì vẽ càng ___.', [[0, 'nhỏ']],
                'Gần to — xa nhỏ.', $d);
            $this->fill($L, $g, 'Bóng đổ nằm ở phía ___ diện với nguồn sáng.', [[0, 'đối']],
                'Bóng luôn ở phía đối diện nguồn sáng.', $d);
            $this->fill($L, $g, 'Phần của vật quay lưng về phía nguồn sáng gọi là phần ___.', [[0, 'tối']],
                'Phần tối + bóng đổ + phản quang tạo khối.', $d);
        }
    }

    private function seedMtNet82(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Ở trung tâm mặt trống đồng Đông Sơn có hình gì?',
                ['Ngôi sao nhiều cánh (mặt trời)', 'Con rồng', 'Chữ viết', 'Hình người'], 0,
                'Trung tâm mặt trống đồng là ngôi sao nhiều cánh tượng trưng cho mặt trời.', $d);
            $this->quiz($L, $g, 'Họa tiết trang trí thường được sắp xếp theo quy tắc nào?',
                ['Lặp lại có quy luật', 'Ngẫu nhiên tùy hứng',
                 'Chỉ vẽ một lần', 'Càng rối càng đẹp'], 0,
                'Họa tiết lặp lại có quy luật tạo nhịp điệu cho trang trí.', $d);
            $this->quiz($L, $g, 'Chim Lạc là họa tiết xuất hiện trên hiện vật nào?',
                ['Trống đồng Đông Sơn', 'Gốm sứ Bát Tràng', 'Tranh Đông Hồ', 'Tượng Phật'], 0,
                'Chim Lạc bay quanh ngôi sao là họa tiết đặc trưng của trống đồng Đông Sơn.', $d);
            $this->quiz($L, $g, 'Hoa văn thổ cẩm rực rỡ thường thấy trong trang phục của ai?',
                ['Các dân tộc thiểu số', 'Người nước ngoài', 'Diễn viên xiếc', 'Cầu thủ bóng đá'], 0,
                'Người H\'Mông, Thái, Dao... dệt thổ cẩm với hoa văn rực rỡ.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi hiện vật với họa tiết đặc trưng.',
                [['Trống đồng', 'Chim Lạc, mặt trời'], ['Thổ cẩm', 'Hoa văn dệt'],
                 ['Gốm sứ', 'Hoa văn vẽ tay'], ['Khăn piêu', 'Hoa văn thêu']],
                'Mỗi chất liệu có kỹ thuật tạo họa tiết riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi loại họa tiết với nguồn gốc.',
                [['Họa tiết hình học', 'Đường thẳng, hình tròn'],
                 ['Họa tiết hoa lá', 'Cách điệu từ thiên nhiên'],
                 ['Họa tiết con vật', 'Chim, rồng, cá'],
                 ['Họa tiết chữ', 'Chữ Phúc, Lộc, Thọ']],
                'Họa tiết được cách điệu từ thiên nhiên và đời sống.', $d);
            $this->matching($L, $g, 'Nối mỗi cách sắp xếp với tên gọi.',
                [['Lặp lại đều đặn', 'Nhịp điệu'], ['Hai bên giống nhau', 'Đối xứng'],
                 ['Xen kẽ nhau', 'Đăng đối'], ['Phóng khoáng', 'Tự do']],
                'Nhịp điệu, đối xứng, đăng đối là quy tắc trang trí cơ bản.', $d);
            $this->matching($L, $g, 'Nối mỗi hiện vật với hình dáng của nó.',
                [['Mặt trống đồng', 'Hình tròn'], ['Đĩa gốm', 'Hình tròn'],
                 ['Thân trống đồng', 'Hình trụ'], ['Khăn thổ cẩm', 'Hình chữ nhật']],
                'Hình dáng hiện vật quyết định cách bố trí họa tiết.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi họa tiết vào nhóm TỪ THIÊN NHIÊN hoặc HÌNH HỌC.',
                [['Hoa sen cách điệu', 'Từ thiên nhiên'], ['Chim Lạc', 'Từ thiên nhiên'],
                 ['Đường zigzag', 'Hình học'], ['Hình tròn đồng tâm', 'Hình học']],
                'Hoa lá, con vật cách điệu từ thiên nhiên; đường nét là hình học.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Họa tiết trang trí lặp lại có quy luật', 'Đúng'],
                 ['Trống đồng thuộc văn hóa Đông Sơn', 'Đúng'],
                 ['Chim Lạc là họa tiết của phương Tây', 'Sai'],
                 ['Trang trí không cần quy luật nào', 'Sai']],
                'Chim Lạc là biểu tượng Việt Nam; trang trí cần quy luật.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi họa tiết vào nhóm trên HIỆN VẬT tương ứng.',
                [['Chim Lạc', 'Trống đồng'], ['Mặt trời', 'Trống đồng'],
                 ['Hoa văn dệt', 'Thổ cẩm'], ['Hoa văn vẽ', 'Gốm sứ']],
                'Trống đồng: chim Lạc, mặt trời. Thổ cẩm: hoa văn dệt. Gốm: hoa văn vẽ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cách vẽ vào nhóm CÁCH ĐIỆU hoặc TẢ THỰC.',
                [['Hoa sen thành họa tiết', 'Cách điệu'], ['Chim Lạc trên trống đồng', 'Cách điệu'],
                 ['Ảnh chụp bông hoa', 'Tả thực'], ['Vẽ chân dung giống hệt', 'Tả thực']],
                'Cách điệu: đơn giản hóa, trang trí hóa; tả thực: giống thật.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Ở trung tâm mặt trống đồng là hình ngôi ___ nhiều cánh.', [[0, 'sao']],
                'Ngôi sao tượng trưng cho mặt trời.', $d);
            $this->fill($L, $g, 'Loài chim huyền thoại trên trống đồng gọi là chim ___.', [[0, 'Lạc']],
                'Chim Lạc — biểu tượng của văn hóa Đông Sơn.', $d);
            $this->fill($L, $g, 'Trống đồng là biểu tượng của nền văn hóa Đông ___.', [[0, 'Sơn']],
                'Văn hóa Đông Sơn cách đây hơn 2000 năm.', $d);
            $this->fill($L, $g, 'Họa tiết trang trí thường được ___ lại theo quy luật.', [[0, 'lặp']],
                'Lặp lại tạo nhịp điệu cho trang trí.', $d);
        }
    }

    // ================= MỸ THUẬT: tranh dân gian =================

    private function seedMtTranh81(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Làng tranh Đông Hồ thuộc tỉnh nào?',
                ['Bắc Ninh', 'Hà Nội', 'Thừa Thiên Huế', 'Nam Định'], 0,
                'Làng Đông Hồ, huyện Thuận Thành, tỉnh Bắc Ninh.', $d);
            $this->quiz($L, $g, 'Giấy điệp trong tranh Đông Hồ được làm từ gì?',
                ['Vỏ sò điệp giã nhỏ', 'Vỏ cây dó', 'Rơm rạ', 'Bột giấy'], 0,
                'Vỏ sò điệp giã nhỏ, trộn hồ quét lên giấy dó tạo độ óng ánh.', $d);
            $this->quiz($L, $g, 'Bức tranh "Đám cưới chuột" thuộc chủ đề nào?',
                ['Sinh hoạt, châm biếm', 'Chúc tụng', 'Lịch sử', 'Tôn giáo'], 0,
                '"Đám cưới chuột" châm biếm thói hư tật xấu qua sinh hoạt của loài chuột.', $d);
            $this->quiz($L, $g, 'Màu vàng trong tranh Đông Hồ được lấy từ đâu?',
                ['Hoa hòe', 'Nghệ', 'Lá chàm', 'Đất son'], 0,
                'Hoa hòe cho màu vàng; than cho màu đen; gạch non cho màu đỏ.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi nguyên liệu với sản phẩm nó tạo ra.',
                [['Vỏ sò điệp', 'Giấy điệp'], ['Than củi', 'Màu đen'],
                 ['Gạch non', 'Màu đỏ'], ['Hoa hòe', 'Màu vàng']],
                'Nguyên liệu tự nhiên tạo nên màu sắc trầm ấm của tranh Đông Hồ.', $d);
            $this->matching($L, $g, 'Nối mỗi bức tranh với chủ đề của nó.',
                [['Đám cưới chuột', 'Sinh hoạt, châm biếm'], ['Vinh hoa Phú quý', 'Chúc tụng'],
                 ['Bà Trưng', 'Lịch sử'], ['Đàn gà mẹ con', 'Chúc tụng đông con']],
                'Tranh Đông Hồ có 3 chủ đề chính: chúc tụng, sinh hoạt, lịch sử.', $d);
            $this->matching($L, $g, 'Nối mỗi công đoạn với nội dung.',
                [['Khắc ván', 'Khắc nét đen lên gỗ'], ['Quét điệp', 'Làm giấy điệp'],
                 ['In màu', 'In từng ván màu'], ['Phơi khô', 'Bảo quản tranh']],
                'Quy trình làm tranh Đông Hồ hoàn toàn thủ công.', $d);
            $this->matching($L, $g, 'Nối mỗi yếu tố với thông tin đúng.',
                [['Làng Đông Hồ', 'Thuận Thành, Bắc Ninh'],
                 ['Nghệ nhân', 'Người làm tranh'],
                 ['Ván khắc', 'Khắc trên gỗ thị'],
                 ['Tranh Tết', 'Treo trong dịp Tết']],
                'Tranh Đông Hồ còn gọi là tranh Tết vì treo nhiều vào dịp Tết.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi nguyên liệu vào nhóm TỰ NHIÊN hoặc CÔNG NGHIỆP.',
                [['Vỏ sò điệp', 'Tự nhiên'], ['Than củi', 'Tự nhiên'],
                 ['Màu acrylic', 'Công nghiệp'], ['Giấy in offset', 'Công nghiệp']],
                'Tranh Đông Hồ dùng nguyên liệu tự nhiên.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Tranh Đông Hồ in từ ván khắc gỗ', 'Đúng'],
                 ['Giấy điệp làm từ vỏ sò', 'Đúng'],
                 ['Tranh Đông Hồ dùng màu hóa học', 'Sai'],
                 ['Làng Đông Hồ ở Huế', 'Sai']],
                'Tranh Đông Hồ dùng màu tự nhiên; làng ở Bắc Ninh.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi bức tranh vào nhóm theo CHỦ ĐỀ.',
                [['Đám cưới chuột', 'Sinh hoạt'], ['Vinh hoa', 'Chúc tụng'],
                 ['Bà Trưng cưỡi voi', 'Lịch sử'], ['Phú quý', 'Chúc tụng']],
                'Sinh hoạt, chúc tụng, lịch sử — 3 chủ đề chính.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm CÔNG ĐOẠN LÀM TRANH hoặc CÁCH DÙNG TRANH.',
                [['Khắc ván gỗ', 'Làm tranh'], ['In màu từng ván', 'Làm tranh'],
                 ['Treo tranh ngày Tết', 'Dùng tranh'], ['Dán tranh lên tường', 'Dùng tranh']],
                'Làm tranh thủ công nhiều công đoạn; tranh dùng treo Tết, trang trí.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Làng tranh Đông Hồ thuộc huyện Thuận Thành, tỉnh Bắc ___.', [[0, 'Ninh']],
                'Đông Hồ — làng tranh nổi tiếng của Bắc Ninh.', $d);
            $this->fill($L, $g, 'Giấy ___ được quét từ vỏ sò điệp giã nhỏ.', [[0, 'điệp']],
                'Giấy điệp óng ánh là nét riêng của tranh Đông Hồ.', $d);
            $this->fill($L, $g, 'Bức tranh "Đám cưới ___" rất nổi tiếng của làng Đông Hồ.', [[0, 'chuột']],
                '"Đám cưới chuột" — 12 con chuột rước dâu vui nhộn.', $d);
            $this->fill($L, $g, 'Màu đen trong tranh Đông Hồ được làm từ ___.', [[0, 'than']],
                'Than củi giã nhỏ cho màu đen.', $d);
        }
    }

    private function seedMtTranh82(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Tranh Hàng Trống khác tranh Đông Hồ ở điểm nào?',
                ['In ván rồi tô màu bằng tay', 'Chỉ vẽ không in',
                 'In bằng máy hiện đại', 'Vẽ trên lụa'], 0,
                'Hàng Trống: in ván lấy nét đen rồi nghệ nhân tô màu bằng tay.', $d);
            $this->quiz($L, $g, 'Tranh Kim Hoàng thường được in trên giấy màu gì?',
                ['Hồng điều (đỏ)', 'Trắng', 'Vàng', 'Xanh'], 0,
                'Giấy hồng điều đỏ là nét riêng của tranh Kim Hoàng.', $d);
            $this->quiz($L, $g, 'Dòng tranh nào nổi tiếng nhất về tranh thờ?',
                ['Hàng Trống', 'Đông Hồ', 'Kim Hoàng', 'Làng Sình'], 0,
                'Tranh thờ Hàng Trống (như Ngũ hổ) nổi tiếng khắp Bắc Bộ.', $d);
            $this->quiz($L, $g, 'Bộ tranh "Tố nữ" là tác phẩm tiêu biểu của dòng tranh nào?',
                ['Hàng Trống', 'Đông Hồ', 'Kim Hoàng', 'Tranh kính'], 0,
                '"Tố nữ" — 4 cô gái đẹp, tác phẩm tiêu biểu của Hàng Trống.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi dòng tranh với kỹ thuật đặc trưng.',
                [['Tranh Hàng Trống', 'In ván + tô màu bằng tay'],
                 ['Tranh Đông Hồ', 'In ván hoàn toàn'],
                 ['Tranh Kim Hoàng', 'In trên giấy hồng điều'],
                 ['Tranh Làng Sình', 'In ván, tô màu (Huế)']],
                'Mỗi dòng tranh có kỹ thuật và chất liệu riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi tác phẩm với dòng tranh của nó.',
                [['Tố nữ', 'Hàng Trống'], ['Ngũ hổ', 'Hàng Trống'],
                 ['Đám cưới chuột', 'Đông Hồ'], ['Vinh hoa', 'Đông Hồ']],
                'Tố nữ, Ngũ hổ — Hàng Trống. Đám cưới chuột, Vinh hoa — Đông Hồ.', $d);
            $this->matching($L, $g, 'Nối mỗi loại tranh với dòng tranh nổi tiếng về nó.',
                [['Tranh thờ', 'Hàng Trống'], ['Tranh Tết', 'Cả ba dòng'],
                 ['Tranh chúc tụng', 'Đông Hồ'], ['Tranh giấy đỏ', 'Kim Hoàng']],
                'Mỗi dòng tranh có thế mạnh riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi làng nghề với địa phương.',
                [['Phố Hàng Trống', 'Hà Nội'], ['Làng Kim Hoàng', 'Hoài Đức, Hà Nội'],
                 ['Làng Đông Hồ', 'Bắc Ninh'], ['Làng Sình', 'Huế']],
                'Ba dòng tranh Bắc Bộ và một dòng tranh miền Trung.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi tác phẩm vào nhóm theo DÒNG TRANH.',
                [['Tố nữ', 'Hàng Trống'], ['Ngũ hổ', 'Hàng Trống'],
                 ['Đám cưới chuột', 'Đông Hồ'], ['Vinh hoa', 'Đông Hồ']],
                'Nhận biết tác phẩm giúp phân biệt các dòng tranh.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Tranh Hàng Trống tô màu bằng tay', 'Đúng'],
                 ['Tranh Kim Hoàng in trên giấy đỏ', 'Đúng'],
                 ['Tranh Đông Hồ ở Hà Nội', 'Sai'],
                 ['Tranh Hàng Trống chỉ in ván, không vẽ tay', 'Sai']],
                'Đông Hồ ở Bắc Ninh; Hàng Trống vừa in ván vừa vẽ tay.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi đặc điểm vào nhóm DÒNG TRANH tương ứng.',
                [['Tố nữ', 'Tranh Hàng Trống'], ['Vẽ tay tô màu', 'Tranh Hàng Trống'],
                 ['In trên giấy hồng điều', 'Tranh Kim Hoàng'], ['Giấy điệp vỏ sò', 'Tranh Đông Hồ']],
                'Ba dòng tranh, ba nét riêng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi làng nghề vào nhóm theo ĐỊA PHƯƠNG.',
                [['Hàng Trống', 'Hà Nội'], ['Kim Hoàng', 'Hà Nội'],
                 ['Đông Hồ', 'Bắc Ninh'], ['Làng Sình', 'Huế']],
                'Hà Nội có 2 dòng tranh; Bắc Ninh và Huế mỗi nơi 1 dòng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Tranh Hàng Trống sau khi in ván còn được ___ màu bằng tay.', [[0, 'tô']],
                'Tô màu bằng tay tạo nên sự mềm mại cho tranh Hàng Trống.', $d);
            $this->fill($L, $g, 'Tranh Kim Hoàng thường in trên giấy màu hồng ___.', [[0, 'điều']],
                'Giấy hồng điều — nét riêng của Kim Hoàng.', $d);
            $this->fill($L, $g, 'Dòng tranh nổi tiếng với tranh ___ là Hàng Trống.', [[0, 'thờ']],
                'Tranh thờ Hàng Trống như Ngũ hổ treo ở nhiều đình chùa.', $d);
            $this->fill($L, $g, 'Bộ tranh "___ nữ" là tác phẩm tiêu biểu của Hàng Trống.', [[0, 'Tố']],
                '"Tố nữ" vẽ 4 thiếu nữ với 4 loại đàn.', $d);
        }
    }

    private function seedMtTranh91(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Tranh Làng Sình có nguồn gốc ở đâu?',
                ['Huế', 'Hà Nội', 'Bắc Ninh', 'Sài Gòn'], 0,
                'Làng Sình (Phú Vang, Thừa Thiên Huế) nổi tiếng về tranh thờ.', $d);
            $this->quiz($L, $g, 'Tranh Làng Sình chủ yếu được dùng để làm gì?',
                ['Thờ cúng', 'Trang trí Tết', 'Quảng cáo', 'Minh họa sách'], 0,
                'Tranh Làng Sình chủ yếu là tranh thờ cúng trong gia đình.', $d);
            $this->quiz($L, $g, 'Tranh kính Nam Bộ được vẽ trên chất liệu gì?',
                ['Tờ kính', 'Giấy điệp', 'Vải lụa', 'Gỗ'], 0,
                'Tranh kính vẽ trực tiếp lên mặt sau của tờ kính.', $d);
            $this->quiz($L, $g, 'Chủ đề nào phổ biến trong tranh kính Nam Bộ?',
                ['Bát tiên', 'Đám cưới chuột', 'Ngũ hổ', 'Tố nữ'], 0,
                'Tranh kính Nam Bộ hay vẽ Bát tiên, Tứ quý.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi dòng tranh với vùng miền và đặc điểm.',
                [['Tranh Làng Sình', 'Huế – tranh thờ'],
                 ['Tranh kính', 'Nam Bộ – vẽ trên kính'],
                 ['Tranh Đông Hồ', 'Bắc Ninh – giấy điệp'],
                 ['Tranh Hàng Trống', 'Hà Nội – vẽ tay']],
                'Bốn dòng tranh tiêu biểu của ba miền.', $d);
            $this->matching($L, $g, 'Nối mỗi chất liệu với dòng tranh dùng nó.',
                [['Giấy hồng điều', 'Kim Hoàng'], ['Giấy điệp', 'Đông Hồ'],
                 ['Tờ kính', 'Tranh kính Nam Bộ'], ['Giấy dó', 'Tranh Làng Sình']],
                'Chất liệu tạo nên nét riêng của mỗi dòng tranh.', $d);
            $this->matching($L, $g, 'Nối mỗi tác phẩm với dòng tranh của nó.',
                [['Bát tiên', 'Tranh kính Nam Bộ'], ['Tứ quý', 'Nhiều dòng tranh'],
                 ['Ngũ hổ', 'Hàng Trống'], ['Lý ngư vọng nguyệt', 'Làng Sình']],
                '"Lý ngư vọng nguyệt" (cá chép trông trăng) là tranh thờ Làng Sình.', $d);
            $this->matching($L, $g, 'Nối mỗi loại tranh với mục đích sử dụng.',
                [['Tranh thờ', 'Dùng cúng bái'], ['Tranh Tết', 'Treo ngày Tết'],
                 ['Tranh chúc tụng', 'Chúc phúc'], ['Tranh lịch sử', 'Nhớ công ơn']],
                'Tranh dân gian gắn với đời sống tâm linh và lễ hội.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi dòng tranh vào nhóm theo MIỀN.',
                [['Làng Sình', 'Miền Trung'], ['Tranh kính', 'Miền Nam'],
                 ['Đông Hồ', 'Miền Bắc'], ['Hàng Trống', 'Miền Bắc']],
                'Trung: Làng Sình. Nam: tranh kính. Bắc: Đông Hồ, Hàng Trống.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Tranh Làng Sình ở Huế', 'Đúng'],
                 ['Tranh Làng Sình dùng để thờ cúng', 'Đúng'],
                 ['Tranh kính vẽ trên giấy', 'Sai'],
                 ['Tranh kính là của miền Bắc', 'Sai']],
                'Tranh kính vẽ trên kính, là của Nam Bộ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi chất liệu vào nhóm GIẤY hoặc KHÔNG PHẢI GIẤY.',
                [['Giấy điệp', 'Chất liệu giấy'], ['Giấy hồng điều', 'Chất liệu giấy'],
                 ['Tờ kính', 'Không phải giấy'], ['Ván gỗ', 'Không phải giấy']],
                'Tranh kính vẽ trên kính; tranh sơn mài vẽ trên gỗ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi loại tranh vào nhóm MỤC ĐÍCH TÂM LINH hoặc TRANG TRÍ.',
                [['Tranh thờ Làng Sình', 'Tâm linh'], ['Tranh Ngũ hổ trấn trạch', 'Tâm linh'],
                 ['Tranh Tố nữ treo nhà', 'Trang trí'], ['Tranh Tết', 'Trang trí']],
                'Tranh thờ, trấn trạch mang ý nghĩa tâm linh.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Tranh Làng Sình có nguồn gốc từ thành phố ___.', [[0, 'Huế']],
                'Làng Sình, Phú Vang, Thừa Thiên Huế.', $d);
            $this->fill($L, $g, 'Tranh Làng Sình chủ yếu dùng vào việc ___ cúng.', [[0, 'thờ']],
                'Tranh thờ cúng tổ tiên, thần linh.', $d);
            $this->fill($L, $g, 'Tranh ___ Nam Bộ được vẽ trực tiếp trên tờ kính.', [[0, 'kính']],
                'Vẽ mặt sau tờ kính rồi lật lại xem.', $d);
            $this->fill($L, $g, 'Bức "Lý ngư vọng nguyệt" vẽ con cá ___ trông trăng.', [[0, 'chép']],
                'Cá chép trông trăng — tranh thờ Làng Sình.', $d);
        }
    }

    private function seedMtTranh92(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Thách thức lớn nhất của tranh dân gian hiện nay là gì?',
                ['Mai một, thiếu người kế nghiệp', 'Thừa nghệ nhân',
                 'Không ai mua', 'Hết nguyên liệu'], 0,
                'Nghệ nhân cao tuổi, giới trẻ ít theo nghề — tranh dân gian đứng trước nguy cơ mai một.', $d);
            $this->quiz($L, $g, 'Nghệ nhân là gì?',
                ['Người thợ giỏi, giữ nghề truyền thống', 'Người mua tranh',
                 'Người bán tranh', 'Người xem tranh'], 0,
                'Nghệ nhân là người tinh thông và truyền dạy nghề truyền thống.', $d);
            $this->quiz($L, $g, 'Cách nào sau đây giúp bảo tồn tranh dân gian hiệu quả nhất?',
                ['Truyền nghề cho thế hệ trẻ', 'Cất tranh vào kho',
                 'Ngừng làm tranh', 'Chỉ bán cho nước ngoài'], 0,
                'Truyền nghề cho thế hệ trẻ là cách bảo tồn bền vững nhất.', $d);
            $this->quiz($L, $g, 'Ví dụ nào là ứng dụng tranh dân gian vào cuộc sống hiện đại?',
                ['Họa tiết Đông Hồ trên áo dài, bao bì', 'Đốt tranh cũ',
                 'Vẽ tranh bằng máy in', 'Nhập tranh ngoại'], 0,
                'Họa tiết dân gian lên áo dài, bao bì, logo — vừa đẹp vừa quảng bá văn hóa.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi khái niệm với ý nghĩa.',
                [['Nghệ nhân', 'Người giữ nghề truyền thống'],
                 ['Làng nghề', 'Nơi sản xuất thủ công truyền thống'],
                 ['Truyền nghề', 'Dạy nghề cho thế hệ sau'],
                 ['Bảo tồn', 'Giữ gìn không để mai một']],
                'Bảo tồn bắt đầu từ con người: nghệ nhân và làng nghề.', $d);
            $this->matching($L, $g, 'Nối mỗi giải pháp với lợi ích của nó.',
                [['Đưa vào trường học', 'Giáo dục thế hệ trẻ'],
                 ['Du lịch làng nghề', 'Quảng bá và tạo thu nhập'],
                 ['Ứng dụng thiết kế', 'Đưa vào áo dài, bao bì'],
                 ['Số hóa', 'Lưu trữ lâu dài']],
                'Bảo tồn cần nhiều giải pháp đồng bộ.', $d);
            $this->matching($L, $g, 'Nối mỗi sự kiện với ý nghĩa.',
                [['Nghệ nhân Đông Hồ', 'Người giữ lửa nghề'],
                 ['Lễ hội làng nghề', 'Tôn vinh nghề truyền thống'],
                 ['Bảo tàng', 'Trưng bày, lưu giữ'],
                 ['Sách giáo khoa mỹ thuật', 'Giáo dục thẩm mỹ']],
                'Xã hội có nhiều cách tôn vinh tranh dân gian.', $d);
            $this->matching($L, $g, 'Nối mỗi ứng dụng với lĩnh vực.',
                [['Tranh Đông Hồ trên tem', 'Bưu chính'],
                 ['Họa tiết trống đồng trên logo', 'Thương hiệu'],
                 ['Tranh dân gian trong sách', 'Giáo dục'],
                 ['Lễ hội tranh', 'Du lịch văn hóa']],
                'Tranh dân gian sống mãi khi gắn với đời sống hiện đại.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi việc làm vào nhóm BẢO TỒN hoặc KHÔNG BẢO TỒN.',
                [['Truyền nghề cho trẻ', 'Bảo tồn'], ['Ghi chép, số hóa', 'Bảo tồn'],
                 ['Bỏ nghề đi làm khác', 'Không bảo tồn'], ['Phá ván khắc cổ', 'Không bảo tồn']],
                'Mỗi hành động đều ảnh hưởng đến sự sống còn của nghề.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nhiều làng tranh đang mai một', 'Đúng'],
                 ['Nghệ nhân là người giữ nghề truyền thống', 'Đúng'],
                 ['Tranh dân gian không thể ứng dụng hiện đại', 'Sai'],
                 ['Du lịch giúp quảng bá làng nghề', 'Đúng']],
                'Tranh dân gian ứng dụng tốt vào thời trang, thiết kế.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi giải pháp vào nhóm GIẢI PHÁP CON NGƯỜI hoặc GIẢI PHÁP CÔNG NGHỆ.',
                [['Truyền nghề', 'Con người'], ['Đưa vào trường học', 'Con người'],
                 ['Số hóa tranh', 'Công nghệ'], ['Bảo tàng số', 'Công nghệ']],
                'Bảo tồn cần cả con người và công nghệ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi yếu tố vào nhóm NGUY CƠ hoặc CƠ HỘI.',
                [['Nghệ nhân cao tuổi', 'Nguy cơ'], ['Ít người học nghề', 'Nguy cơ'],
                 ['Du lịch phát triển', 'Cơ hội'], ['Thiết kế ứng dụng', 'Cơ hội']],
                'Nhận diện nguy cơ và cơ hội để bảo tồn đúng hướng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Người thợ giỏi giữ nghề truyền thống được gọi là nghệ ___.', [[0, 'nhân']],
                'Nghệ nhân — báu vật sống của làng nghề.', $d);
            $this->fill($L, $g, 'Nhiều làng nghề tranh đang đứng trước nguy cơ mai ___.', [[0, 'một']],
                'Mai một: dần biến mất theo thời gian.', $d);
            $this->fill($L, $g, 'Đưa nghề làm tranh vào ___ học giúp thế hệ trẻ tiếp cận.', [[0, 'trường']],
                'Giáo dục trong nhà trường nuôi dưỡng tình yêu nghề.', $d);
            $this->fill($L, $g, '___ hóa tranh dân gian giúp lưu trữ lâu dài trên mạng.', [[0, 'Số']],
                'Số hóa: chụp ảnh, dựng bảo tàng số.', $d);
        }
    }

    // ================= GDTC: vận động an toàn =================

    private function seedTtAnToan61(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Trước khi chơi thể thao, việc đầu tiên cần làm là gì?',
                ['Khởi động', 'Uống nước ngọt', 'Ngồi nghỉ', 'Ăn no'], 0,
                'Khởi động làm nóng cơ, giúp tránh chấn thương khi vận động.', $d);
            $this->quiz($L, $g, 'Nên khởi động trong khoảng thời gian bao lâu?',
                ['5 – 10 phút', '1 phút', '30 phút', '1 giờ'], 0,
                'Khởi động 5-10 phút là đủ: xoay khớp, chạy nhẹ, ép dẻo.', $d);
            $this->quiz($L, $g, 'Khi chơi thể thao nên mang loại giày nào?',
                ['Giày thể thao', 'Dép lê', 'Giày cao gót', 'Đi chân đất'], 0,
                'Giày thể thao vừa chân bảo vệ bàn chân và khớp.', $d);
            $this->quiz($L, $g, 'Khởi động kỹ có tác dụng gì?',
                ['Làm nóng cơ, tránh chấn thương', 'Làm mệt trước khi chơi',
                 'Tốn thời gian', 'Không có tác dụng'], 0,
                'Cơ nóng lên sẽ dẻo dai hơn, giảm nguy cơ rách cơ, bong gân.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi động tác với giai đoạn của buổi tập.',
                [['Xoay khớp cổ tay', 'Khởi động'], ['Chạy nhẹ tại chỗ', 'Khởi động'],
                 ['Ép dẻo', 'Khởi động'], ['Đi bộ thả lỏng', 'Thả lỏng sau tập']],
                'Khởi động trước tập, thả lỏng sau tập.', $d);
            $this->matching($L, $g, 'Nối mỗi việc chuẩn bị với lợi ích của nó.',
                [['Mang giày thể thao', 'Bảo vệ chân'],
                 ['Mặc trang phục thoải mái', 'Dễ vận động'],
                 ['Chọn sân bằng phẳng', 'An toàn'],
                 ['Kiểm tra dụng cụ', 'Tránh hỏng hóc gây chấn thương']],
                'Chuẩn bị tốt giúp buổi tập an toàn và hiệu quả.', $d);
            $this->matching($L, $g, 'Nối mỗi thói quen với đánh giá.',
                [['Uống nước trước khi tập', 'Bù nước'], ['Thả lỏng sau khi tập', 'Hồi phục'],
                 ['Không ăn no trước khi tập', 'Tránh đau bụng'], ['Tắm ngay khi đang đổ mồ hôi', 'Không nên']],
                'Tắm khi đang đổ mồ hôi, gặp gió lạnh dễ bị cảm.', $d);
            $this->matching($L, $g, 'Nối mỗi tình huống sân bãi với nguy cơ.',
                [['Sân ướt', 'Dễ trượt ngã'], ['Dụng cụ hỏng', 'Dễ chấn thương'],
                 ['Sân có đá dăm', 'Dễ trầy xước'], ['Chơi gần đường đông xe', 'Nguy hiểm']],
                'Luôn kiểm tra sân bãi trước khi chơi.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Khởi động trước khi chơi', 'Nên'], ['Mang giày thể thao', 'Nên'],
                 ['Chơi khi sân ướt', 'Không nên'], ['Ăn no rồi chạy ngay', 'Không nên']],
                'An toàn là nguyên tắc đầu tiên của thể thao.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Phải khởi động trước khi vận động', 'Đúng'],
                 ['Nên uống đủ nước khi tập', 'Đúng'],
                 ['Có thể chơi thể thao bằng dép lê', 'Sai'],
                 ['Sân ướt vẫn an toàn', 'Sai']],
                'Dép lê dễ tuột gây chấn thương; sân ướt dễ trượt ngã.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm TRƯỚC – TRONG – SAU buổi tập.',
                [['Khởi động', 'Trước khi tập'], ['Kiểm tra sân bãi', 'Trước khi tập'],
                 ['Uống nước từng ngụm', 'Trong khi tập'], ['Thả lỏng', 'Sau khi tập']],
                'Mỗi giai đoạn có việc cần làm riêng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi điều kiện vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Sân khô ráo', 'An toàn'], ['Giày vừa chân', 'An toàn'],
                 ['Sân trơn ướt', 'Nguy hiểm'], ['Dụng cụ bị gãy', 'Nguy hiểm']],
                'Điều kiện an toàn giúp phòng tránh chấn thương.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Trước khi vận động phải ___ động 5-10 phút.', [[0, 'khởi']],
                'Khởi động: xoay khớp, chạy nhẹ, ép dẻo.', $d);
            $this->fill($L, $g, 'Khi chơi thể thao nên mang giày thể ___.', [[0, 'thao']],
                'Giày thể thao bảo vệ chân và khớp.', $d);
            $this->fill($L, $g, 'Không nên ăn ___ trước khi vận động mạnh.', [[0, 'no']],
                'Ăn no rồi vận động mạnh dễ đau bụng, buồn nôn.', $d);
            $this->fill($L, $g, 'Sân bị trơn ___ rất dễ gây trượt ngã.', [[0, 'ướt']],
                'Kiểm tra sân khô ráo trước khi chơi.', $d);
        }
    }

    private function seedTtAnToan62(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Khi chạy, hai tay nên làm gì?',
                ['Đánh nhịp theo bước chạy', 'Đút túi quần', 'Giơ cao quá đầu', 'Buông thõng'], 0,
                'Tay đánh nhịp ngược với chân giúp giữ thăng bằng và tăng tốc.', $d);
            $this->quiz($L, $g, 'Khi nhảy từ trên cao xuống, tiếp đất thế nào là đúng?',
                ['Gối khụy, cả bàn chân chạm đất', 'Chân thẳng, gót chạm đất',
                 'Tiếp đất bằng mũi chân', 'Nhảy xuống nền cứng'], 0,
                'Gối khụy hấp thụ lực, tránh chấn thương khớp gối.', $d);
            $this->quiz($L, $g, 'Khi vận động mạnh nên thở thế nào?',
                ['Hít bằng mũi, thở bằng miệng', 'Nín thở',
                 'Thở bằng mũi cả hai chiều', 'Thở gấp gáp'], 0,
                'Hít mũi lọc không khí, thở miệng đẩy nhanh khí cặn.', $d);
            $this->quiz($L, $g, 'Tư thế chạy đúng là người như thế nào?',
                ['Hơi nghiêng về phía trước', 'Ngửa ra phía sau',
                 'Cúi gằm mặt xuống', 'Nghiêng sang một bên'], 0,
                'Người hơi nghiêng trước, mắt nhìn thẳng, lưng thẳng.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi động tác với yêu cầu kỹ thuật.',
                [['Tay đánh nhịp', 'Chạy đúng'], ['Gối khụy khi tiếp đất', 'Giảm chấn thương'],
                 ['Hít bằng mũi', 'Thở đúng'], ['Người hơi nghiêng trước', 'Tư thế chạy']],
                'Kỹ thuật đúng giúp chạy nhanh và an toàn.', $d);
            $this->matching($L, $g, 'Nối mỗi cách làm với đánh giá ĐÚNG – SAI.',
                [['Tiếp đất bằng gót cứng', 'Sai – dễ chấn thương'],
                 ['Tiếp đất cả bàn chân', 'Đúng'],
                 ['Nín thở khi chạy', 'Sai'],
                 ['Thở đều khi chạy', 'Đúng']],
                'Tiếp đất sai và nín thở đều gây hại.', $d);
            $this->matching($L, $g, 'Nối mỗi nội dung với tố chất nó rèn luyện.',
                [['Chạy ngắn', 'Tốc độ'], ['Chạy dài', 'Sức bền'],
                 ['Nhảy xa', 'Sức bật'], ['Ném bóng', 'Sức mạnh']],
                'Mỗi nội dung rèn một tố chất khác nhau.', $d);
            $this->matching($L, $g, 'Nối mỗi tư thế với đánh giá.',
                [['Mắt nhìn thẳng', 'Tư thế đúng'], ['Lưng thẳng', 'Tư thế đúng'],
                 ['Vai thả lỏng', 'Tư thế đúng'], ['Cúi gằm mặt', 'Tư thế sai']],
                'Tư thế đúng: mắt thẳng, lưng thẳng, vai thả lỏng.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi kỹ thuật vào nhóm ĐÚNG hoặc SAI.',
                [['Tay đánh nhịp khi chạy', 'Đúng'], ['Gối khụy khi tiếp đất', 'Đúng'],
                 ['Nín thở khi chạy nhanh', 'Sai'], ['Tiếp đất bằng gót chân cứng', 'Sai']],
                'Kỹ thuật đúng bảo vệ cơ thể và tăng hiệu quả.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi động tác vào nhóm KỸ THUẬT CHẠY hoặc KỸ THUẬT NHẢY.',
                [['Bước chạy đều', 'Chạy'], ['Về đích', 'Chạy'],
                 ['Giậm nhảy', 'Nhảy'], ['Tiếp đất', 'Nhảy']],
                'Chạy: bước đều, về đích. Nhảy: giậm nhảy, tiếp đất.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Thở đều khi chạy', 'Nên'], ['Khởi động kỹ', 'Nên'],
                 ['Chạy ngay sau khi ăn no', 'Không nên'], ['Nhảy từ chỗ cao xuống nền cứng', 'Không nên']],
                'Bảo vệ cơ thể là ưu tiên hàng đầu.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cơ quan vào nhóm VẬN ĐỘNG hoặc HÔ HẤP – TUẦN HOÀN.',
                [['Chân, tay', 'Cơ quan vận động'], ['Cơ bắp', 'Cơ quan vận động'],
                 ['Phổi, tim', 'Cơ quan hô hấp – tuần hoàn'], ['Khí quản', 'Cơ quan hô hấp']],
                'Vận động cần cả cơ quan vận động và hô hấp – tuần hoàn.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Khi chạy, hai tay nên ___ nhịp theo bước chân.', [[0, 'đánh']],
                'Tay đánh nhịp ngược chiều với chân.', $d);
            $this->fill($L, $g, 'Khi tiếp đất sau cú nhảy, đầu gối nên ___ lại để giảm chấn động.', [[0, 'khụy']],
                'Gối khụy hấp thụ lực tiếp đất.', $d);
            $this->fill($L, $g, 'Khi vận động mạnh, hít vào bằng ___ và thở ra bằng miệng.', [[0, 'mũi']],
                'Mũi lọc bụi, làm ấm không khí.', $d);
            $this->fill($L, $g, 'Tư thế chạy đúng là người hơi nghiêng về phía ___.', [[0, 'trước']],
                'Nghiêng trước nhẹ giúp tận dụng trọng lực.', $d);
        }
    }

    private function seedTtAnToan71(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Chuột rút là hiện tượng gì?',
                ['Cơ co cứng đột ngột', 'Xương bị gãy',
                 'Da bị trầy xước', 'Khớp bị trật'], 0,
                'Chuột rút: cơ co cứng đột ngột, đau nhói — cần kéo giãn nhẹ.', $d);
            $this->quiz($L, $g, 'Trong sơ cứu RICE, chữ I có nghĩa là gì?',
                ['Ice – chườm lạnh', 'Injury – chấn thương',
                 'Iron – sắt', 'Ice-cream – kem'], 0,
                'RICE: Rest (nghỉ), Ice (chườm lạnh), Compression (băng ép), Elevation (kê cao).', $d);
            $this->quiz($L, $g, 'Khi bị bong gân, trong 48 giờ đầu nên làm gì?',
                ['Chườm lạnh và nghỉ ngơi', 'Xoa dầu nóng ngay',
                 'Cố chạy tiếp', 'Ngâm nước nóng'], 0,
                'Chườm lạnh giảm sưng; dầu nóng chỉ dùng sau 48 giờ.', $d);
            $this->quiz($L, $g, 'Khi bạn bị trật khớp, em nên xử lý thế nào?',
                ['Cố định và đưa đến cơ sở y tế', 'Tự nắn khớp lại',
                 'Bảo bạn cố cử động', 'Mặc kệ'], 0,
                'Tuyệt đối không tự nắn khớp trật — dễ gây tổn thương nặng hơn.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi chữ cái trong RICE với ý nghĩa.',
                [['R – Rest', 'Nghỉ ngơi'], ['I – Ice', 'Chườm lạnh'],
                 ['C – Compression', 'Băng ép'], ['E – Elevation', 'Kê cao']],
                'RICE là nguyên tắc sơ cứu chấn thương mềm cơ bản.', $d);
            $this->matching($L, $g, 'Nối mỗi chấn thương với cách sơ cứu.',
                [['Chuột rút', 'Kéo giãn cơ nhẹ nhàng'],
                 ['Trầy xước', 'Rửa sạch, sát trùng'],
                 ['Bong gân', 'Chườm lạnh, băng ép'],
                 ['Chảy máu cam', 'Hơi cúi đầu, bóp cánh mũi']],
                'Sơ cứu đúng giúp giảm đau và tránh biến chứng.', $d);
            $this->matching($L, $g, 'Nối mỗi việc làm với mục đích khi bị bong gân.',
                [['Chườm lạnh trong 48 giờ đầu', 'Giảm sưng đau'],
                 ['Băng ép nhẹ', 'Hạn chế sưng'],
                 ['Kê cao chân', 'Giảm sưng'],
                 ['Không xoa dầu nóng ngay', 'Tránh sưng to hơn']],
                'Dầu nóng làm giãn mạch — chỉ dùng sau khi hết sưng.', $d);
            $this->matching($L, $g, 'Nối mỗi việc làm với vai trò phòng tránh hay nguy cơ.',
                [['Khởi động kỹ', 'Phòng tránh'], ['Mang giày phù hợp', 'Phòng tránh'],
                 ['Chơi quá sức', 'Nguy cơ chấn thương'], ['Sân bãi không an toàn', 'Nguy cơ chấn thương']],
                'Phòng tránh tốt hơn chữa trị.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi cách xử lý khi bị bong gân vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Chườm lạnh', 'Nên'], ['Nghỉ ngơi', 'Nên'],
                 ['Xoa dầu nóng ngay', 'Không nên'], ['Tự nắn khớp bị trật', 'Không nên']],
                'Lạnh trước, nóng sau; không tự nắn khớp.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['RICE gồm nghỉ, chườm lạnh, băng ép, kê cao', 'Đúng'],
                 ['Chuột rút là cơ co cứng đột ngột', 'Đúng'],
                 ['Bị trật khớp nên tự nắn lại', 'Sai'],
                 ['Trầy xước không cần rửa sạch', 'Sai']],
                'Trật khớp phải đến cơ sở y tế; trầy xước phải rửa sạch sát trùng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi chấn thương vào nhóm NHẸ hoặc NẶNG.',
                [['Trầy xước nhẹ', 'Nhẹ'], ['Chuột rút', 'Nhẹ'],
                 ['Trật khớp', 'Nặng'], ['Nghi gãy xương', 'Nặng']],
                'Chấn thương nặng cần đưa đến cơ sở y tế ngay.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cách chườm – băng vào nhóm ĐÚNG hoặc SAI.',
                [['Chườm đá bọc trong khăn', 'Đúng'], ['Băng ép vừa phải', 'Đúng'],
                 ['Chườm đá trực tiếp lên da', 'Sai'], ['Băng quá chặt gây tê', 'Sai']],
                'Đá trực tiếp gây bỏng lạnh; băng chặt gây tắc máu.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Hiện tượng cơ co cứng đột ngột khi vận động gọi là ___ rút.', [[0, 'chuột']],
                'Chuột rút thường do khởi động chưa kỹ hoặc mất nước.', $d);
            $this->fill($L, $g, 'Trong sơ cứu RICE, chữ R nghĩa là ___ ngơi.', [[0, 'nghỉ']],
                'Rest: dừng vận động, nghỉ ngơi ngay khi chấn thương.', $d);
            $this->fill($L, $g, 'Khi bị bong gân, trong 48 giờ đầu nên chườm ___.', [[0, 'lạnh']],
                'Chườm lạnh 15-20 phút mỗi lần, đá bọc trong khăn.', $d);
            $this->fill($L, $g, 'Tuyệt đối không tự ___ khớp bị trật, phải đưa đến cơ sở y tế.', [[0, 'nắn']],
                'Tự nắn có thể làm rách dây chằng, tổn thương nặng hơn.', $d);
        }
    }

    private function seedTtAnToan72(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Chạy 100m chủ yếu rèn luyện tố chất nào?',
                ['Sức nhanh', 'Sức bền', 'Sự dẻo dai', 'Sự khéo léo'], 0,
                'Chạy cự ly ngắn rèn sức nhanh — bứt tốc tối đa.', $d);
            $this->quiz($L, $g, 'Bài tập chống đẩy chủ yếu rèn luyện tố chất nào?',
                ['Sức mạnh', 'Sức bền', 'Sức nhanh', 'Sự dẻo dai'], 0,
                'Chống đẩy rèn sức mạnh cơ tay, vai, ngực.', $d);
            $this->quiz($L, $g, 'Chạy bền 800m – 1000m chủ yếu rèn luyện tố chất nào?',
                ['Sức bền', 'Sức nhanh', 'Sức mạnh', 'Sự khéo léo'], 0,
                'Chạy dài rèn sức bền — duy trì vận động lâu.', $d);
            $this->quiz($L, $g, 'Ép dẻo, xoạc chân rèn luyện tố chất nào?',
                ['Sự dẻo dai', 'Sức mạnh', 'Sức nhanh', 'Sức bền'], 0,
                'Ép dẻo giúp khớp linh hoạt, cơ dẻo dai.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi bài tập với tố chất nó rèn luyện.',
                [['Chạy 60m', 'Sức nhanh'], ['Chống đẩy', 'Sức mạnh'],
                 ['Chạy 1000m', 'Sức bền'], ['Ép dẻo', 'Sự dẻo dai']],
                'Chọn bài tập đúng với tố chất muốn phát triển.', $d);
            $this->matching($L, $g, 'Nối mỗi tố chất với ý nghĩa.',
                [['Sức nhanh', 'Hoàn thành động tác nhanh'],
                 ['Sức mạnh', 'Vượt qua lực cản lớn'],
                 ['Sức bền', 'Duy trì vận động lâu'],
                 ['Sự khéo léo', 'Chính xác, nhịp nhàng']],
                'Năm tố chất thể lực cơ bản của con người.', $d);
            $this->matching($L, $g, 'Nối mỗi môn vận động với tố chất nổi bật.',
                [['Nhảy dây', 'Nhanh và bền'], ['Bật xa', 'Sức bật'],
                 ['Gập bụng', 'Sức mạnh cơ bụng'], ['Đá cầu', 'Khéo léo']],
                'Mỗi môn thể thao rèn nổi bật một vài tố chất.', $d);
            $this->matching($L, $g, 'Nối mỗi cách tập với kết quả.',
                [['Tập đều đặn', 'Thể lực tiến bộ'], ['Tập đúng kỹ thuật', 'Hiệu quả cao'],
                 ['Tập quá sức', 'Dễ chấn thương'], ['Bỏ tập lâu ngày', 'Thể lực giảm']],
                'Tập luyện cần đều đặn, đúng cách, vừa sức.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi bài tập vào nhóm TỐ CHẤT tương ứng.',
                [['Chạy 100m', 'Sức nhanh'], ['Nhảy xa lấy đà', 'Sức nhanh'],
                 ['Nâng tạ nhẹ', 'Sức mạnh'], ['Bơi đường dài', 'Sức bền']],
                'Chạy, nhảy rèn sức nhanh; nâng tạ rèn sức mạnh; bơi dài rèn sức bền.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Chạy ngắn rèn sức nhanh', 'Đúng'],
                 ['Tập quá sức dễ chấn thương', 'Đúng'],
                 ['Chống đẩy rèn sức bền', 'Sai'],
                 ['Ép dẻo rèn sự dẻo dai', 'Đúng']],
                'Chống đẩy rèn sức mạnh, không phải sức bền.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nội dung vào nhóm SỨC NHANH, SỨC MẠNH hoặc SỨC BỀN.',
                [['Chạy 100m', 'Sức nhanh'], ['Nhảy xa', 'Sức nhanh'],
                 ['Đẩy tạ', 'Sức mạnh'], ['Chạy marathon', 'Sức bền']],
                'Nhảy xa cần bứt tốc — thuộc sức nhanh.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cách tập vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Tăng dần cường độ', 'Nên'], ['Khởi động trước khi tập', 'Nên'],
                 ['Tập ngay với cường độ cao', 'Không nên'], ['Tập khi đang ốm', 'Không nên']],
                'Tăng dần, vừa sức, khỏe mạnh mới tập.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Chạy cự ly ngắn chủ yếu rèn luyện sức ___.', [[0, 'nhanh']],
                'Chạy 60m, 100m — bứt tốc tối đa.', $d);
            $this->fill($L, $g, 'Bài tập chống đẩy giúp phát triển sức ___.', [[0, 'mạnh']],
                'Sức mạnh cơ tay, vai, ngực.', $d);
            $this->fill($L, $g, 'Chạy bền giúp rèn luyện sức ___.', [[0, 'bền']],
                'Sức bền: duy trì vận động trong thời gian dài.', $d);
            $this->fill($L, $g, 'Ép dẻo giúp cơ thể thêm ___ dai.', [[0, 'dẻo']],
                'Dẻo dai giúp khớp linh hoạt, tránh chấn thương.', $d);
        }
    }

    // ================= GDTC: luật thể thao =================

    private function seedTtLuat71(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Mỗi đội bóng đá có bao nhiêu cầu thủ trên sân?',
                ['11 cầu thủ', '7 cầu thủ', '5 cầu thủ', '9 cầu thủ'], 0,
                'Bóng đá 11 người: mỗi đội 11 cầu thủ trên sân.', $d);
            $this->quiz($L, $g, 'Quả phạt đền được đá từ chấm cách khung thành bao xa?',
                ['11m', '9m', '16m', '5m'], 0,
                'Chấm phạt đền cách khung thành 11m.', $d);
            $this->quiz($L, $g, 'Cầu thủ nhận thẻ đỏ sẽ bị xử lý thế nào?',
                ['Truất quyền thi đấu', 'Chỉ bị cảnh cáo', 'Được đá phạt', 'Được thay người mới'], 0,
                'Thẻ đỏ: truất quyền thi đấu, đội phải chơi thiếu người.', $d);
            $this->quiz($L, $g, 'Một trận bóng đá chính thức có mấy hiệp, mỗi hiệp bao lâu?',
                ['2 hiệp, mỗi hiệp 45 phút', '2 hiệp, mỗi hiệp 30 phút',
                 '4 hiệp, mỗi hiệp 15 phút', '1 hiệp 90 phút'], 0,
                'Trận đấu có 2 hiệp, mỗi hiệp 45 phút, nghỉ giữa hiệp 15 phút.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi thuật ngữ với ý nghĩa.',
                [['Việt vị', 'Đứng sau hàng thủ đối phương khi nhận bóng'],
                 ['Phạt đền', 'Đá từ chấm 11m'],
                 ['Thẻ vàng', 'Cảnh cáo'], ['Thẻ đỏ', 'Truất quyền thi đấu']],
                'Bốn khái niệm luật cơ bản nhất của bóng đá.', $d);
            $this->matching($L, $g, 'Nối mỗi vị trí với nhiệm vụ.',
                [['Thủ môn', 'Được dùng tay trong vòng cấm'],
                 ['Tiền đạo', 'Ghi bàn'], ['Hậu vệ', 'Phòng ngự'],
                 ['Tiền vệ', 'Kiến tạo, nối tuyến']],
                'Mỗi vị trí có nhiệm vụ riêng trên sân.', $d);
            $this->matching($L, $g, 'Nối mỗi tình huống với cách xử lý.',
                [['Bóng hết biên ngang do đội thủ chạm', 'Phạt góc'],
                 ['Bóng hết biên dọc', 'Ném biên'],
                 ['Bắt đầu trận đấu', 'Giao bóng giữa sân'],
                 ['Thủ môn bắt được bóng', 'Phát bóng lên']],
                'Bóng ra ngoài biên xử lý khác nhau tùy vị trí.', $d);
            $this->matching($L, $g, 'Nối mỗi chi tiết sân với kích thước chuẩn.',
                [['Sân 11 người', 'Dài khoảng 105m, rộng 68m'],
                 ['Khung thành', 'Rộng 7,32m, cao 2,44m'],
                 ['Chấm phạt đền', 'Cách khung thành 11m'],
                 ['Vòng tròn giữa sân', 'Bán kính 9,15m']],
                'Sân bóng đá có kích thước chuẩn quốc tế.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Mỗi đội có 11 cầu thủ trên sân', 'Đúng'],
                 ['Phạt đền đá từ chấm 11m', 'Đúng'],
                 ['Thẻ đỏ chỉ là cảnh cáo nhẹ', 'Sai'],
                 ['Trận đấu có 3 hiệp', 'Sai']],
                'Thẻ đỏ là truất quyền thi đấu; trận đấu có 2 hiệp.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi vị trí vào nhóm TẤN CÔNG hoặc PHÒNG NGỰ.',
                [['Tiền đạo', 'Tấn công'], ['Tiền vệ công', 'Tấn công'],
                 ['Hậu vệ', 'Phòng ngự'], ['Thủ môn', 'Phòng ngự']],
                'Tiền đạo, tiền vệ công tấn công; hậu vệ, thủ môn phòng ngự.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hình thức phạt vào nhóm PHẠT CÁ NHÂN hoặc PHẠT ĐỘI.',
                [['Thẻ vàng', 'Phạt cá nhân'], ['Thẻ đỏ', 'Phạt cá nhân'],
                 ['Phạt đền', 'Phạt đội'], ['Phạt gián tiếp', 'Phạt đội']],
                'Thẻ phạt cầu thủ; phạt đền, phạt gián tiếp phạt cả đội.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi tình huống vào nhóm TRONG VÒNG CẤM hoặc NGOÀI VÒNG CẤM.',
                [['Thủ môn được bắt bóng', 'Trong vòng cấm'],
                 ['Phạm lỗi bị phạt đền', 'Trong vòng cấm'],
                 ['Đá phạt trực tiếp', 'Ngoài vòng cấm'],
                 ['Ném biên', 'Ngoài vòng cấm']],
                'Vòng cấm là khu vực 16m50 trước khung thành.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Mỗi đội bóng đá ra sân với ___ cầu thủ.', [[0, '11']],
                'Bóng đá 11 người mỗi đội.', $d);
            $this->fill($L, $g, 'Quả phạt đền được thực hiện từ chấm ___m.', [[0, '11']],
                'Chấm 11m trước khung thành.', $d);
            $this->fill($L, $g, 'Cầu thủ nhận thẻ ___ sẽ bị truất quyền thi đấu.', [[0, 'đỏ']],
                'Thẻ đỏ: ra sân ngay, đội chơi thiếu người.', $d);
            $this->fill($L, $g, 'Lỗi ___ vị xảy ra khi cầu thủ tấn công đứng sau hàng thủ đối phương lúc đồng đội chuyền bóng.', [[0, 'việt']],
                'Việt vị: đứng dưới (gần khung thành hơn) cầu thủ thứ hai cuối cùng của đối phương.', $d);
        }
    }

    private function seedTtLuat72(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Khi dẫn bóng rổ, mỗi lần chạm bóng được dùng mấy tay?',
                ['Một tay', 'Hai tay cùng lúc', 'Chân', 'Đầu'], 0,
                'Dẫn bóng: đập bóng bằng một tay, có thể đổi tay.', $d);
            $this->quiz($L, $g, 'Ném rổ thành công trong vòng cung 3 điểm được tính mấy điểm?',
                ['2 điểm', '1 điểm', '3 điểm', '4 điểm'], 0,
                'Ném trong vòng cung: 2 điểm; ngoài vòng cung: 3 điểm.', $d);
            $this->quiz($L, $g, 'Lỗi "chạy bước" trong bóng rổ là gì?',
                ['Cầm bóng chạy quá số bước mà không dẫn bóng', 'Chạy quá nhanh',
                 'Chạy sai hướng', 'Chạy ra ngoài sân'], 0,
                'Cầm bóng được chạy tối đa 2 bước rồi phải chuyền hoặc ném.', $d);
            $this->quiz($L, $g, 'Theo luật FIBA, cầu thủ phạm bao nhiêu lỗi cá nhân thì bị truất quyền thi đấu?',
                ['5 lỗi', '3 lỗi', '6 lỗi', '4 lỗi'], 0,
                'Đủ 5 lỗi cá nhân: truất quyền thi đấu trận đó.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi thuật ngữ với ý nghĩa.',
                [['Dẫn bóng', 'Đập bóng bằng một tay khi di chuyển'],
                 ['Chạy bước', 'Cầm bóng chạy quá số bước quy định'],
                 ['Hai lần dẫn', 'Dẫn bóng lại sau khi đã dừng'],
                 ['Ném phạt', 'Ném từ vạch phạt khi bị phạm lỗi']],
                'Ba lỗi dẫn bóng cơ bản: chạy bước, hai lần dẫn, mang bóng.', $d);
            $this->matching($L, $g, 'Nối mỗi tình huống ghi điểm với số điểm.',
                [['Ném trong vòng cung', '2 điểm'], ['Ném phạt thành công', '1 điểm'],
                 ['Ném ngoài vòng 3 điểm', '3 điểm'], ['Chiều cao rổ', '3,05m']],
                'Cách tính điểm cơ bản của bóng rổ.', $d);
            $this->matching($L, $g, 'Nối mỗi loại lỗi với hình phạt.',
                [['Đủ 5 lỗi cá nhân', 'Truất quyền thi đấu'],
                 ['Lỗi kỹ thuật', 'Đối phương được ném phạt'],
                 ['Lỗi phản tinh thần thể thao', 'Truất quyền ngay'],
                 ['Hết giờ thi đấu', 'Kết thúc hiệp/trận']],
                'Luật phạt nghiêm để giữ tinh thần thể thao.', $d);
            $this->matching($L, $g, 'Nối mỗi vị trí với nhiệm vụ.',
                [['Hậu vệ dẫn bóng', 'Tổ chức tấn công'],
                 ['Tiền phong', 'Ghi điểm từ xa'],
                 ['Trung phong', 'Tranh bóng dưới rổ'],
                 ['Huấn luyện viên', 'Chỉ đạo chiến thuật']],
                'Năm vị trí trên sân bóng rổ phối hợp với nhau.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Dẫn bóng bằng một tay', 'Đúng'],
                 ['Ném phạt thành công được 1 điểm', 'Đúng'],
                 ['Được cầm bóng chạy 5 bước', 'Sai'],
                 ['Rổ bóng rổ cao 3,05m', 'Đúng']],
                'Cầm bóng chỉ được chạy tối đa 2 bước.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cú ném vào nhóm theo SỐ ĐIỂM.',
                [['Ném trong vòng cung', '2 điểm'], ['Úp rổ', '2 điểm'],
                 ['Ném phạt thành công', '1 điểm'], ['Ném ngoài vạch 3 điểm', '3 điểm']],
                'Trong vòng 2, phạt 1, ngoài vòng 3.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hành động vào nhóm PHẠM LỖI hoặc KHÔNG PHẠM LỖI.',
                [['Chạy bước', 'Phạm lỗi'], ['Hai lần dẫn bóng', 'Phạm lỗi'],
                 ['Dẫn bóng đúng luật', 'Không phạm lỗi'], ['Ném rổ', 'Không phạm lỗi']],
                'Chạy bước và hai lần dẫn là lỗi dẫn bóng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi người vào nhóm TRÊN SÂN hoặc NGOÀI SÂN.',
                [['Hậu vệ', 'Trên sân'], ['Trung phong', 'Trên sân'],
                 ['Huấn luyện viên', 'Ngoài sân'], ['Cầu thủ dự bị', 'Ngoài sân']],
                'Mỗi đội 5 cầu thủ trên sân bóng rổ.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Lỗi cầm bóng chạy quá số bước quy định gọi là lỗi chạy ___.', [[0, 'bước']],
                'Chạy bước: cầm bóng quá 2 bước không dẫn bóng.', $d);
            $this->fill($L, $g, 'Một cú ném phạt thành công được tính ___ điểm.', [[0, '1']],
                'Ném phạt: 1 điểm.', $d);
            $this->fill($L, $g, 'Chiều cao của rổ bóng rổ là 3,___m.', [[0, '05']],
                'Rổ cao 3,05m so với mặt sân.', $d);
            $this->fill($L, $g, 'Cầu thủ phạm đủ 5 lỗi cá nhân sẽ bị ___ quyền thi đấu.', [[0, 'truất']],
                'Luật FIBA: 5 lỗi cá nhân = truất quyền thi đấu.', $d);
        }
    }

    private function seedTtLuat81(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Mỗi đội bóng chuyền có bao nhiêu người trên sân?',
                ['6 người', '5 người', '7 người', '11 người'], 0,
                'Bóng chuyền trong nhà: mỗi đội 6 người trên sân.', $d);
            $this->quiz($L, $g, 'Mỗi đội được chạm bóng tối đa mấy lần trước khi đưa sang sân đối phương?',
                ['3 lần', '2 lần', '4 lần', 'Không giới hạn'], 0,
                'Tối đa 3 lần chạm bóng mỗi đợt — thường là đỡ, chuyền 2, đập.', $d);
            $this->quiz($L, $g, 'Một set đấu bóng chuyền (set thường) đánh đến bao nhiêu điểm?',
                ['25 điểm', '21 điểm', '15 điểm', '30 điểm'], 0,
                'Set 1-4 đến 25 điểm và hơn đối phương 2 điểm.', $d);
            $this->quiz($L, $g, 'Muốn thắng một trận bóng chuyền cần thắng bao nhiêu set?',
                ['3 set', '2 set', '5 set', '4 set'], 0,
                'Thắng 3 trong tối đa 5 set thì thắng trận.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi kỹ thuật với vai trò.',
                [['Phát bóng', 'Mở đầu mỗi lượt bóng'],
                 ['Chuyền 2', 'Dọn bóng cho đồng đội đập'],
                 ['Đập bóng', 'Ghi điểm tấn công'],
                 ['Chắn bóng', 'Ngăn cú đập của đối phương']],
                'Bốn kỹ thuật cơ bản của bóng chuyền.', $d);
            $this->matching($L, $g, 'Nối mỗi tình huống với kết luận ĐÚNG – SAI luật.',
                [['Chạm lưới khi đánh bóng', 'Lỗi'],
                 ['Bóng rơi ngoài sân', 'Lỗi của đội đánh'],
                 ['Dẫm vạch khi phát bóng', 'Lỗi'],
                 ['Chạm bóng 4 lần một đợt', 'Lỗi']],
                'Các lỗi thường gặp trong bóng chuyền.', $d);
            $this->matching($L, $g, 'Nối mỗi set với điểm số quy định.',
                [['Set 1 đến set 4', '25 điểm'], ['Set 5 quyết định', '15 điểm'],
                 ['Điều kiện thắng set', 'Hơn 2 điểm'], ['Thắng trận', 'Thắng 3 set']],
                'Set 5 ngắn hơn: chỉ đến 15 điểm.', $d);
            $this->matching($L, $g, 'Nối mỗi vị trí với đặc điểm.',
                [['Chuyền 2', 'Vị trí số 3, dọn bóng'],
                 ['Chủ công', 'Vị trí số 4, đập biên'],
                 ['Đối chuyền', 'Vị trí số 2'],
                 ['Libero', 'Chuyên phòng thủ, áo khác màu']],
                'Libero chỉ phòng thủ, không được phát bóng và đập bóng trên lưới.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Mỗi đội có 6 người trên sân', 'Đúng'],
                 ['Chạm lưới khi đánh bóng là lỗi', 'Đúng'],
                 ['Được chạm bóng 4 lần một đợt', 'Sai'],
                 ['Set 5 đánh đến 25 điểm', 'Sai']],
                'Tối đa 3 lần chạm; set 5 chỉ đến 15 điểm.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi kỹ thuật vào nhóm TẤN CÔNG hoặc PHÒNG THỦ.',
                [['Đập bóng', 'Tấn công'], ['Phát bóng tấn công', 'Tấn công'],
                 ['Chắn bóng', 'Phòng thủ'], ['Cứu bóng', 'Phòng thủ']],
                'Đập, phát bóng tấn công ghi điểm; chắn, cứu bóng phòng thủ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi tình huống vào nhóm PHẠM LỖI hoặc HỢP LỆ.',
                [['Chạm lưới', 'Phạm lỗi'], ['Bóng rơi ngoài sân', 'Phạm lỗi'],
                 ['Chắn bóng hợp lệ', 'Hợp lệ'], ['Phát bóng qua lưới', 'Hợp lệ']],
                'Chạm lưới và đánh ra ngoài đều mất điểm.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi con số vào nhóm ĐIỂM SỐ hoặc SỐ SET.',
                [['25 điểm', 'Điểm số'], ['15 điểm', 'Điểm số'],
                 ['3 set', 'Số set'], ['5 set', 'Số set']],
                '25, 15 là điểm số (thắng set); 3, 5 là số set (thắng trận).', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Mỗi đội bóng chuyền thi đấu với ___ người trên sân.', [[0, '6']],
                'Bóng chuyền trong nhà: 6 đấu 6.', $d);
            $this->fill($L, $g, 'Mỗi đội được chạm bóng tối đa ___ lần trước khi đưa bóng sang sân đối phương.', [[0, '3']],
                'Quá 3 lần chạm là phạm lỗi.', $d);
            $this->fill($L, $g, 'Cầu thủ chuyên phòng thủ, mặc áo khác màu gọi là ___.', [[0, 'libero']],
                'Libero chỉ phòng thủ, không phát bóng.', $d);
            $this->fill($L, $g, 'Để thắng một set thường cần đạt ___ điểm và hơn đối phương 2 điểm.', [[0, '25']],
                'Set thường: 25 điểm, cách 2 điểm.', $d);
        }
    }

    private function seedTtLuat82(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Một set cầu lông đánh đến bao nhiêu điểm thì thắng (cách 2 điểm)?',
                ['21 điểm', '25 điểm', '15 điểm', '11 điểm'], 0,
                'Thắng set khi đạt 21 điểm và hơn đối phương 2 điểm.', $d);
            $this->quiz($L, $g, 'Muốn thắng một trận cầu lông cần thắng bao nhiêu set?',
                ['2 set', '3 set', '1 set', '5 set'], 0,
                'Thắng 2 trong 3 set thì thắng trận.', $d);
            $this->quiz($L, $g, 'Khi giao cầu, cầu phải được phát sang ô nào?',
                ['Ô chéo sân', 'Ô cùng phía', 'Bất kỳ ô nào', 'Ô của mình'], 0,
                'Giao cầu chéo sân sang ô đối diện của đối phương.', $d);
            $this->quiz($L, $g, 'Điểm số tối đa của một set cầu lông là bao nhiêu?',
                ['30 điểm', '25 điểm', '21 điểm', '35 điểm'], 0,
                'Nếu 29-29, ai ghi điểm 30 trước thắng set.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi kỹ thuật với ý nghĩa.',
                [['Giao cầu', 'Phát cầu mở đầu pha bóng'],
                 ['Đập cầu', 'Đánh mạnh để ghi điểm'],
                 ['Bỏ nhỏ', 'Đánh nhẹ sát lưới'],
                 ['Phòng thủ', 'Đỡ cú đánh của đối phương']],
                'Bốn kỹ thuật cơ bản của cầu lông.', $d);
            $this->matching($L, $g, 'Nối mỗi con số với ý nghĩa trong luật cầu lông.',
                [['21 điểm', 'Thắng 1 set'], ['Hơn 2 điểm', 'Điều kiện thắng set'],
                 ['30 điểm', 'Điểm tối đa'], ['2 set', 'Thắng trận']],
                '21 – 2 – 30 – 2: bốn con số của cầu lông.', $d);
            $this->matching($L, $g, 'Nối mỗi chi tiết với thông số.',
                [['Sân đánh đơn', 'Rộng 5,18m'], ['Sân đánh đôi', 'Rộng 6,1m'],
                 ['Chiều cao lưới ở biên', '1,55m'], ['Quả cầu', 'Làm bằng lông vũ']],
                'Sân dài 13,4m; đơn hẹp hơn đôi.', $d);
            $this->matching($L, $g, 'Nối mỗi tình huống giao cầu với đánh giá.',
                [['Giao cầu sang ô chéo', 'Đúng luật'],
                 ['Cầu rơi trong sân đối phương', 'Đúng luật'],
                 ['Chân dẫm vạch khi giao cầu', 'Phạm lỗi'],
                 ['Vợt chạm lưới khi đánh', 'Phạm lỗi']],
                'Giao cầu sai ô hoặc dẫm vạch đều mất điểm.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Set cầu lông đánh đến 21 điểm', 'Đúng'],
                 ['Giao cầu phải phát chéo sân', 'Đúng'],
                 ['Thắng trận cần thắng 3 set', 'Sai'],
                 ['Điểm tối đa một set là 25', 'Sai']],
                'Thắng 2/3 set; điểm tối đa là 30.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nội dung vào nhóm ĐÁNH ĐƠN hoặc ĐÁNH ĐÔI.',
                [['1 người mỗi bên', 'Đánh đơn'], ['Sân hẹp hơn', 'Đánh đơn'],
                 ['2 người mỗi bên', 'Đánh đôi'], ['Sân rộng hơn', 'Đánh đôi']],
                'Đơn: 1v1 sân hẹp. Đôi: 2v2 sân rộng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi tình huống vào nhóm GHI ĐIỂM hoặc MẤT ĐIỂM.',
                [['Cầu rơi trong sân đối phương', 'Ghi điểm'],
                 ['Đối phương đánh cầu ra ngoài', 'Ghi điểm'],
                 ['Mình đánh cầu ra ngoài', 'Mất điểm'],
                 ['Giao cầu không qua lưới', 'Mất điểm']],
                'Tính điểm rally: mỗi pha bóng đều có điểm.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi kỹ thuật vào nhóm TẤN CÔNG hoặc PHÒNG THỦ.',
                [['Đập cầu', 'Tấn công'], ['Bỏ nhỏ', 'Tấn công'],
                 ['Phòng thủ xa', 'Phòng thủ'], ['Đỡ cầu bổng', 'Phòng thủ']],
                'Đập, bỏ nhỏ ghi điểm; phòng thủ giữ cầu.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Mỗi set cầu lông thi đấu đến ___ điểm.', [[0, '21']],
                '21 điểm và hơn đối phương 2 điểm.', $d);
            $this->fill($L, $g, 'Cầu thủ giao cầu phải phát cầu sang ô ___ sân.', [[0, 'chéo']],
                'Giao cầu chéo sang ô đối diện.', $d);
            $this->fill($L, $g, 'Điểm số tối đa trong một set cầu lông là ___ điểm.', [[0, '30']],
                '29-29: ai được 30 trước thắng.', $d);
            $this->fill($L, $g, 'Muốn thắng trận đấu cầu lông, cần thắng ___ set.', [[0, '2']],
                'Thắng 2 trong 3 set.', $d);
        }
    }

    // ================= GDTC: sức khỏe & vệ sinh =================

    private function seedTtSk81(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Nhóm chất nào cung cấp năng lượng chính cho cơ thể vận động?',
                ['Bột đường', 'Chất đạm', 'Vitamin', 'Chất xơ'], 0,
                'Bột đường (cơm, bánh mì, khoai) là nguồn năng lượng chính.', $d);
            $this->quiz($L, $g, 'Chất đạm có vai trò gì với cơ thể?',
                ['Xây dựng và phục hồi cơ bắp', 'Cung cấp vitamin',
                 'Giúp xương dài ra', 'Tăng chiều cao ngay'], 0,
                'Đạm (thịt, trứng, sữa, đậu) xây dựng cơ bắp.', $d);
            $this->quiz($L, $g, 'Trước khi tập luyện nên ăn khi nào là hợp lý?',
                ['Ăn nhẹ trước 1 – 2 giờ', 'Ăn no ngay trước khi tập',
                 'Nhịn ăn cả ngày', 'Ăn trong lúc tập'], 0,
                'Ăn nhẹ trước 1-2 giờ để có năng lượng mà không đau bụng.', $d);
            $this->quiz($L, $g, 'Khi vận động ra nhiều mồ hôi, cần bổ sung nhiều nhất là gì?',
                ['Nước', 'Nước ngọt có gas', 'Kẹo', 'Cà phê'], 0,
                'Mồ hôi mất nước — cần uống nước từng ngụm, không nhịn khát.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi thực phẩm với nhóm chất của nó.',
                [['Cơm, bánh mì', 'Bột đường'], ['Thịt, trứng, sữa', 'Chất đạm'],
                 ['Dầu ăn, mỡ', 'Chất béo'], ['Rau, trái cây', 'Vitamin, chất xơ']],
                'Bữa ăn đủ 4 nhóm chất mới cân đối.', $d);
            $this->matching($L, $g, 'Nối mỗi nhóm chất với vai trò.',
                [['Bột đường', 'Cung cấp năng lượng'], ['Chất đạm', 'Xây dựng cơ bắp'],
                 ['Vitamin', 'Tăng sức đề kháng'], ['Nước', 'Bù lượng nước mất đi']],
                'Mỗi nhóm chất có vai trò riêng không thể thay thế.', $d);
            $this->matching($L, $g, 'Nối mỗi thói quen ăn uống với đánh giá.',
                [['Ăn nhẹ trước tập 1-2 giờ', 'Đúng'],
                 ['Uống nước từng ngụm khi tập', 'Đúng'],
                 ['Ăn no ngay trước khi chạy', 'Sai'],
                 ['Nhịn uống nước khi tập', 'Sai']],
                'Ăn no vận động dễ đau bụng; nhịn nước dễ kiệt sức.', $d);
            $this->matching($L, $g, 'Nối mỗi thực phẩm với lợi ích khi tập luyện.',
                [['Cơm, chuối', 'Nạp năng lượng'], ['Thịt, trứng', 'Xây dựng cơ bắp'],
                 ['Rau, nước lọc', 'Vitamin và bù nước'], ['Sữa', 'Bổ sung đạm, canxi']],
                'Ăn đúng giúp tập luyện hiệu quả và hồi phục nhanh.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi thực phẩm vào nhóm CHẤT tương ứng.',
                [['Cơm', 'Bột đường'], ['Bánh mì', 'Bột đường'],
                 ['Thịt bò', 'Chất đạm'], ['Rau muống', 'Vitamin, chất xơ']],
                'Cơm, bánh mì: bột đường; thịt bò: chất đạm; rau: vitamin, chất xơ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Bột đường cung cấp năng lượng chính', 'Đúng'],
                 ['Đạm giúp phát triển cơ bắp', 'Đúng'],
                 ['Không cần uống nước khi tập', 'Sai'],
                 ['Nên ăn no ngay trước khi thi đấu', 'Sai']],
                'Phải uống đủ nước; chỉ ăn nhẹ trước khi tập.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi thói quen vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Ăn đủ 4 nhóm chất', 'Nên'], ['Uống đủ nước', 'Nên'],
                 ['Uống nước ngọt có gas khi tập', 'Không nên'], ['Bỏ bữa sáng rồi đi tập', 'Không nên']],
                'Nước ngọt có gas gây đầy bụng; bỏ bữa sáng thiếu năng lượng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm TRƯỚC hoặc SAU buổi tập.',
                [['Ăn nhẹ', 'Trước khi tập'], ['Khởi động', 'Trước khi tập'],
                 ['Bổ sung nước', 'Sau khi tập'], ['Ăn bữa chính', 'Sau khi tập']],
                'Trước tập: ăn nhẹ + khởi động. Sau tập: bù nước + ăn chính.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Nhóm chất cung cấp năng lượng chính cho vận động là ___ đường.', [[0, 'bột']],
                'Bột đường: cơm, bánh mì, khoai, mì.', $d);
            $this->fill($L, $g, 'Chất ___ giúp xây dựng và phục hồi cơ bắp.', [[0, 'đạm']],
                'Đạm: thịt, cá, trứng, sữa, đậu.', $d);
            $this->fill($L, $g, 'Nên ăn nhẹ trước khi tập khoảng 1 đến ___ giờ.', [[0, '2']],
                'Ăn nhẹ trước 1-2 giờ là hợp lý.', $d);
            $this->fill($L, $g, 'Khi vận động ra nhiều mồ hôi, cần bổ sung ___ đầy đủ.', [[0, 'nước']],
                'Uống từng ngụm nhỏ, không uống ừng ực.', $d);
        }
    }

    private function seedTtSk82(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Sau khi tập thể thao ra nhiều mồ hôi, nên làm gì?',
                ['Tắm rửa, thay quần áo khô', 'Ngồi trước quạt ngay khi ướt đẫm',
                 'Mặc nguyên đồ ướt', 'Uống nước đá thật nhiều'], 0,
                'Tắm rửa thay đồ khô tránh cảm lạnh và viêm da.', $d);
            $this->quiz($L, $g, 'Vì sao không nên dùng chung khăn mặt, bình nước với người khác?',
                ['Dễ lây bệnh ngoài da, bệnh truyền nhiễm', 'Tốn tiền',
                 'Mất vệ sinh chung', 'Không có lý do'], 0,
                'Khăn, bình nước dùng chung dễ lây bệnh da liễu, cúm.', $d);
            $this->quiz($L, $g, 'Sau khi chơi xong, nên làm gì với sân tập?',
                ['Dọn rác, giữ sân sạch sẽ', 'Bỏ đi ngay', 'Xả rác bừa bãi', 'Mặc kệ'], 0,
                'Giữ sân sạch là trách nhiệm và văn minh của người chơi.', $d);
            $this->quiz($L, $g, 'Giày thể thao sau khi tập nên được làm gì?',
                ['Vệ sinh, phơi khô thường xuyên', 'Để ướt trong túi kín',
                 'Không bao giờ giặt', 'Cho người khác mượn'], 0,
                'Giày ẩm là môi trường của vi khuẩn gây mùi và bệnh chân.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi việc vệ sinh cá nhân với lợi ích.',
                [['Tắm sau khi tập', 'Sạch sẽ'], ['Thay quần áo khô', 'Tránh cảm lạnh'],
                 ['Giặt khăn thường xuyên', 'Vệ sinh'], ['Cắt móng tay chân', 'An toàn khi chơi']],
                'Vệ sinh cá nhân tốt bảo vệ sức khỏe.', $d);
            $this->matching($L, $g, 'Nối mỗi thói quen với lý do.',
                [['Không dùng chung bình nước', 'Tránh lây bệnh'],
                 ['Không dùng chung khăn', 'Tránh lây bệnh'],
                 ['Rửa tay trước khi ăn', 'Vệ sinh'],
                 ['Che miệng khi ho', 'Lịch sự, tránh lây bệnh']],
                'Đồ dùng cá nhân không nên dùng chung.', $d);
            $this->matching($L, $g, 'Nối mỗi việc giữ vệ sinh chung với ý nghĩa.',
                [['Bỏ rác đúng nơi', 'Sân sạch đẹp'],
                 ['Không khạc nhổ bừa bãi', 'Văn minh'],
                 ['Vệ sinh dụng cụ chung', 'Trách nhiệm'],
                 ['Báo ngay dụng cụ hỏng', 'An toàn cho mọi người']],
                'Vệ sinh chung là ý thức cộng đồng.', $d);
            $this->matching($L, $g, 'Nối mỗi nguyên nhân với hậu quả.',
                [['Mồ hôi + gặp gió lạnh', 'Dễ bị cảm'],
                 ['Mặc quần áo ẩm lâu', 'Dễ viêm da'],
                 ['Sân bẩn trơn', 'Dễ trượt ngã'],
                 ['Dụng cụ bẩn', 'Dễ nhiễm trùng vết thương']],
                'Mất vệ sinh gây hậu quả trực tiếp cho sức khỏe.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Tắm sau khi tập', 'Nên'], ['Dọn rác sau khi chơi', 'Nên'],
                 ['Dùng chung khăn với bạn', 'Không nên'], ['Khạc nhổ bừa bãi', 'Không nên']],
                'Vệ sinh cá nhân và ý thức chung.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nên thay quần áo khô sau khi tập', 'Đúng'],
                 ['Cần vệ sinh dụng cụ tập chung', 'Đúng'],
                 ['Dùng chung bình nước là tốt', 'Sai'],
                 ['Sân bẩn không ảnh hưởng gì', 'Sai']],
                'Sân bẩn dễ trượt ngã; đồ dùng chung cần vệ sinh.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm VỆ SINH CÁ NHÂN hoặc VỆ SINH CHUNG.',
                [['Tắm rửa', 'Cá nhân'], ['Giặt khăn', 'Cá nhân'],
                 ['Dọn sân', 'Chung'], ['Lau dụng cụ tập', 'Chung']],
                'Vừa giữ mình sạch, vừa giữ nơi công cộng sạch.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm TRƯỚC hoặc SAU buổi tập.',
                [['Kiểm tra sân bãi', 'Trước khi tập'], ['Chuẩn bị nước uống', 'Trước khi tập'],
                 ['Tắm rửa', 'Sau khi tập'], ['Dọn dẹp sân', 'Sau khi tập']],
                'Trước tập chuẩn bị; sau tập dọn dẹp.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Sau khi tập ra nhiều mồ hôi cần ___ rửa sạch sẽ.', [[0, 'tắm']],
                'Tắm rửa loại bỏ mồ hôi, vi khuẩn.', $d);
            $this->fill($L, $g, 'Không nên dùng ___ khăn mặt, bình nước với người khác để tránh lây bệnh.', [[0, 'chung']],
                'Đồ dùng cá nhân không dùng chung.', $d);
            $this->fill($L, $g, 'Giữ ___ bãi sạch sẽ là trách nhiệm của mọi người chơi.', [[0, 'sân']],
                'Sân sạch — an toàn — văn minh.', $d);
            $this->fill($L, $g, 'Quần áo ẩm ướt sau khi tập cần thay ngay để tránh bị ___ lạnh.', [[0, 'cảm']],
                'Mồ hôi + gió lạnh dễ gây cảm.', $d);
        }
    }

    private function seedTtSk91(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Công thức ước tính nhịp tim tối đa là gì?',
                ['220 – tuổi', '220 + tuổi', '100 + tuổi', '200 – tuổi'], 0,
                'Nhịp tim tối đa ≈ 220 trừ đi số tuổi.', $d);
            $this->quiz($L, $g, 'Bạn 15 tuổi thì nhịp tim tối đa ước tính khoảng bao nhiêu?',
                ['205 nhịp/phút', '220 nhịp/phút', '235 nhịp/phút', '150 nhịp/phút'], 0,
                '220 – 15 = 205 nhịp/phút.', $d);
            $this->quiz($L, $g, 'Khi vận động mạnh, nhịp tim sẽ thay đổi thế nào?',
                ['Tăng lên', 'Giảm xuống', 'Không đổi', 'Ngừng đập'], 0,
                'Tim đập nhanh để bơm máu nuôi cơ bắp đang hoạt động.', $d);
            $this->quiz($L, $g, 'Thở đúng khi chạy bền là thở thế nào?',
                ['Thở sâu, nhịp nhàng', 'Nín thở', 'Thở gấp gáp', 'Thở bằng miệng liên tục'], 0,
                'Thở sâu nhịp nhàng cung cấp đủ oxy, giúp bền bỉ.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi cơ quan với vai trò khi vận động.',
                [['Tim', 'Bơm máu'], ['Phổi', 'Trao đổi oxy'],
                 ['Máu', 'Vận chuyển oxy'], ['Cơ bắp', 'Tiêu thụ năng lượng']],
                'Tim – phổi – máu – cơ phối hợp khi vận động.', $d);
            $this->matching($L, $g, 'Nối mỗi khái niệm với ý nghĩa.',
                [['220 – tuổi', 'Nhịp tim tối đa'],
                 ['60-80% nhịp tối đa', 'Vùng tập luyện hiệu quả'],
                 ['Bắt mạch ở cổ tay', 'Cách đo nhịp tim'],
                 ['Đếm 15 giây x 4', 'Cách tính nhịp mỗi phút']],
                'Theo dõi nhịp tim giúp tập luyện an toàn.', $d);
            $this->matching($L, $g, 'Nối mỗi trạng thái với nhịp tim.',
                [['Vận động nhẹ', 'Tim đập hơi nhanh'],
                 ['Vận động mạnh', 'Tim đập rất nhanh'],
                 ['Ngồi nghỉ', 'Tim đập chậm lại'],
                 ['Hồi phục', 'Tim dần về bình thường']],
                'Nhịp tim phản ánh cường độ vận động.', $d);
            $this->matching($L, $g, 'Nối mỗi cách thở với đánh giá.',
                [['Hít sâu', 'Tốt – lấy nhiều oxy'],
                 ['Thở nhịp nhàng', 'Tốt – bền bỉ'],
                 ['Nín thở khi chạy', 'Không tốt – nhanh mệt'],
                 ['Thở quá gấp gáp', 'Không tốt – thiếu oxy']],
                'Thở đúng là kỹ năng quan trọng của chạy bền.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi hoạt động vào nhóm NHỊP TIM TĂNG hoặc NHỊP TIM GIẢM.',
                [['Bắt đầu chạy', 'Nhịp tim tăng'], ['Chạy nhanh dần', 'Nhịp tim tăng'],
                 ['Ngồi nghỉ', 'Nhịp tim giảm'], ['Nằm ngủ', 'Nhịp tim giảm']],
                'Vận động tăng nhịp tim; nghỉ ngơi giảm nhịp tim.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nhịp tim tối đa ≈ 220 – tuổi', 'Đúng'],
                 ['Thở sâu giúp lấy nhiều oxy', 'Đúng'],
                 ['Càng tập tim càng đập nhanh mãi không giảm', 'Sai'],
                 ['Nín thở giúp chạy bền hơn', 'Sai']],
                'Nghỉ ngơi tim sẽ chậm lại; nín thở gây thiếu oxy.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cơ quan vào nhóm TUẦN HOÀN hoặc HÔ HẤP.',
                [['Tim', 'Tuần hoàn'], ['Mạch máu', 'Tuần hoàn'],
                 ['Phổi', 'Hô hấp'], ['Khí quản', 'Hô hấp']],
                'Tuần hoàn: tim, mạch máu. Hô hấp: phổi, khí quản.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hoạt động vào nhóm mức độ VẬN ĐỘNG.',
                [['Đi bộ', 'Nhẹ'], ['Chạy bền', 'Vừa'],
                 ['Đá bóng', 'Vừa'], ['Chạy nước rút', 'Mạnh']],
                'Nhẹ – vừa – mạnh: ba mức cường độ vận động.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Công thức ước tính nhịp tim tối đa là 220 trừ đi ___.', [[0, 'tuổi']],
                '220 – tuổi = nhịp tim tối đa ước tính.', $d);
            $this->fill($L, $g, 'Bạn 15 tuổi có nhịp tim tối đa khoảng ___ nhịp/phút.', [[0, '205']],
                '220 – 15 = 205.', $d);
            $this->fill($L, $g, 'Khi vận động, ___ đập nhanh hơn để bơm máu đi nuôi cơ thể.', [[0, 'tim']],
                'Tim là máy bơm của hệ tuần hoàn.', $d);
            $this->fill($L, $g, 'Thở ___ và nhịp nhàng giúp cơ thể bền bỉ khi chạy dài.', [[0, 'sâu']],
                'Hít sâu lấy nhiều oxy hơn.', $d);
        }
    }

    private function seedTtSk92(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Doping là gì?',
                ['Chất kích thích bị cấm trong thể thao', 'Thuốc bổ cho vận động viên',
                 'Nước uống thể thao', 'Thực phẩm chức năng'], 0,
                'Doping là chất cấm — dùng để gian lận thành tích.', $d);
            $this->quiz($L, $g, 'Vận động viên dương tính với doping sẽ bị xử lý thế nào?',
                ['Cấm thi đấu, tước huy chương', 'Được khen thưởng',
                 'Không bị gì', 'Được thi đấu tiếp'], 0,
                'Doping bị phạt nặng: cấm thi đấu, tước danh hiệu.', $d);
            $this->quiz($L, $g, 'Thuốc lá gây hại gì cho người chơi thể thao?',
                ['Hại phổi, giảm sức bền', 'Tăng sức bền',
                 'Giúp tỉnh táo', 'Không ảnh hưởng'], 0,
                'Khói thuốc làm giảm dung tích phổi, giảm sức bền.', $d);
            $this->quiz($L, $g, 'Rượu bia ảnh hưởng gì đến người chơi thể thao?',
                ['Giảm phản xạ, hại gan', 'Tăng phản xạ',
                 'Tốt cho tim', 'Không ảnh hưởng'], 0,
                'Rượu bia làm chậm phản xạ, hại gan, giảm phong độ.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi chất với tác hại của nó.',
                [['Doping', 'Chất cấm trong thể thao'],
                 ['Rượu bia', 'Giảm phản xạ, hại gan'],
                 ['Thuốc lá', 'Hại phổi, giảm sức bền'],
                 ['Ma túy', 'Phạm pháp, hủy hoại sức khỏe']],
                'Mọi chất kích thích đều có hại.', $d);
            $this->matching($L, $g, 'Nối mỗi khái niệm kiểm tra doping với ý nghĩa.',
                [['Kiểm tra doping', 'Trước và sau thi đấu'],
                 ['Dương tính', 'Bị phạt nặng'],
                 ['Âm tính', 'Được thi đấu'],
                 ['Tước huy chương', 'Hình phạt gian lận']],
                'Kiểm tra doping giữ sự công bằng của thể thao.', $d);
            $this->matching($L, $g, 'Nối mỗi việc làm với đánh giá ĐÚNG – SAI.',
                [['Nói không với doping', 'Đúng'],
                 ['Tập luyện chăm chỉ', 'Đúng'],
                 ['Dùng chất cấm để thắng', 'Sai'],
                 ['Che giấu người dùng doping', 'Sai']],
                'Trung thực là giá trị cốt lõi của thể thao.', $d);
            $this->matching($L, $g, 'Nối mỗi thói quen với ảnh hưởng sức khỏe.',
                [['Hút thuốc lá', 'Có hại'], ['Uống rượu bia', 'Có hại'],
                 ['Tập luyện đều đặn', 'Có lợi'], ['Ăn uống lành mạnh', 'Có lợi']],
                'Lối sống lành mạnh là nền tảng của thể lực.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi thứ vào nhóm CÓ HẠI hoặc CÓ LỢI cho sức khỏe.',
                [['Doping', 'Có hại'], ['Thuốc lá', 'Có hại'],
                 ['Tập thể dục', 'Có lợi'], ['Ăn uống đủ chất', 'Có lợi']],
                'Tránh chất kích thích, chăm tập luyện.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Doping bị cấm trong thể thao', 'Đúng'],
                 ['Vận động viên phải kiểm tra doping', 'Đúng'],
                 ['Rượu bia giúp tăng thể lực', 'Sai'],
                 ['Hút thuốc không ảnh hưởng sức bền', 'Sai']],
                'Rượu bia, thuốc lá đều làm giảm thể lực.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hành vi vào nhóm BỊ PHẠT hoặc ĐƯỢC TÔN VINH.',
                [['Dương tính với doping', 'Bị phạt'], ['Tước huy chương', 'Bị phạt'],
                 ['Thi đấu trung thực', 'Được tôn vinh'], ['Nói không với chất cấm', 'Được tôn vinh']],
                'Gian lận bị phạt; trung thực được tôn vinh.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi chất/thói quen vào nhóm HỦY HOẠI hoặc BẢO VỆ sức khỏe.',
                [['Thuốc lá', 'Hủy hoại sức khỏe'], ['Rượu bia', 'Hủy hoại sức khỏe'],
                 ['Thể thao đều đặn', 'Bảo vệ sức khỏe'], ['Ngủ đủ giấc', 'Bảo vệ sức khỏe']],
                'Chọn lối sống bảo vệ sức khỏe.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Chất kích thích bị cấm sử dụng trong thể thao gọi là ___.', [[0, 'doping']],
                'Doping: chất cấm, gian lận.', $d);
            $this->fill($L, $g, 'Vận động viên dương tính với doping sẽ bị cấm thi ___ và tước huy chương.', [[0, 'đấu']],
                'Hình phạt nghiêm khắc nhất của thể thao.', $d);
            $this->fill($L, $g, 'Hút thuốc lá gây hại nghiêm trọng cho ___.', [[0, 'phổi']],
                'Phổi là cơ quan hô hấp quan trọng nhất khi vận động.', $d);
            $this->fill($L, $g, 'Người chơi thể thao cần nói ___ với rượu bia, thuốc lá và chất kích thích.', [[0, 'không']],
                'Nói không với chất kích thích — thi đấu trung thực.', $d);
        }
    }

    // ================= TNHN: kỹ năng sống =================

    private function seedTnhnKn61(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Nên học tập trung liên tục bao lâu rồi nghỉ một lần?',
                ['25 – 30 phút', '2 giờ', '5 phút', 'Cả buổi không nghỉ'], 0,
                'Học 25-30 phút nghỉ 5 phút giúp não hồi phục, nhớ lâu hơn.', $d);
            $this->quiz($L, $g, 'Việc quan trọng và gấp nên được xử lý khi nào?',
                ['Làm trước', 'Để cuối cùng', 'Nhờ người khác', 'Bỏ qua'], 0,
                'Ưu tiên việc quan trọng — đó là nguyên tắc quản lý thời gian.', $d);
            $this->quiz($L, $g, 'Học sinh mỗi đêm nên ngủ khoảng bao nhiêu tiếng?',
                ['8 – 10 tiếng', '4 tiếng', '12 tiếng', 'Không cần ngủ'], 0,
                'Thiếu ngủ làm giảm trí nhớ và khả năng tập trung.', $d);
            $this->quiz($L, $g, 'Lập thời gian biểu có lợi ích gì?',
                ['Sắp xếp công việc hợp lý, không quên việc', 'Mất thời gian',
                 'Không cần thiết', 'Chỉ để trang trí'], 0,
                'Thời gian biểu giúp cân bằng học tập, vui chơi và nghỉ ngơi.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi loại việc với cách xử lý.',
                [['Việc quan trọng', 'Làm trước'], ['Việc gấp', 'Ưu tiên ngay'],
                 ['Giờ học', 'Tập trung'], ['Giờ nghỉ', 'Thư giãn']],
                'Phân loại việc giúp dùng thời gian hiệu quả.', $d);
            $this->matching($L, $g, 'Nối mỗi thói quen với lợi ích.',
                [['25 phút học tập trung', 'Hiệu quả'], ['5 phút nghỉ giữa giờ', 'Hồi phục'],
                 ['Đi ngủ đúng giờ', 'Khỏe mạnh'], ['Dậy đúng giờ', 'Tỉnh táo']],
                'Nhịp sinh hoạt đều đặn tốt cho sức khỏe và học tập.', $d);
            $this->matching($L, $g, 'Nối mỗi công cụ với công dụng.',
                [['Sổ tay', 'Ghi việc cần làm'], ['Đồng hồ báo thức', 'Nhắc giờ giấc'],
                 ['Thời gian biểu', 'Kế hoạch trong ngày'], ['Đánh dấu hoàn thành', 'Tạo động lực']],
                'Công cụ đơn giản giúp quản lý thời gian tốt hơn.', $d);
            $this->matching($L, $g, 'Nối mỗi thói quen với đánh giá NÊN – KHÔNG NÊN.',
                [['Học đúng giờ giấc', 'Nên'], ['Ngủ đủ giấc', 'Nên'],
                 ['Thức khuya chơi game', 'Không nên'], ['Trì hoãn bài tập', 'Không nên']],
                'Thói quen tốt hôm nay tạo thành công ngày mai.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Lập thời gian biểu', 'Nên'], ['Ưu tiên việc quan trọng', 'Nên'],
                 ['Thức khuya chơi game', 'Không nên'], ['Để bài tập đến phút cuối', 'Không nên']],
                'Quản lý thời gian tốt bắt đầu từ thói quen nhỏ.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nên nghỉ 5 phút sau 25-30 phút học', 'Đúng'],
                 ['Ngủ đủ giấc giúp học tốt hơn', 'Đúng'],
                 ['Việc quan trọng nên làm sau cùng', 'Sai'],
                 ['Không cần thời gian biểu', 'Sai']],
                'Việc quan trọng làm trước; thời gian biểu rất cần thiết.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hoạt động vào nhóm theo BUỔI trong ngày.',
                [['Học ở trường', 'Buổi sáng'], ['Học bài', 'Buổi chiều'],
                 ['Vui chơi', 'Buổi chiều'], ['Ngủ', 'Buổi tối']],
                'Sáng học ở trường; chiều học bài, vui chơi; tối ngủ đủ giấc.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm QUAN TRỌNG hoặc ÍT QUAN TRỌNG.',
                [['Làm bài tập', 'Quan trọng'], ['Ôn bài kiểm tra', 'Quan trọng'],
                 ['Lướt mạng vô bổ', 'Ít quan trọng'], ['Chơi game quá giờ', 'Ít quan trọng']],
                'Ưu tiên việc quan trọng trước khi giải trí.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Nên học tập trung 25-30 phút rồi nghỉ ___ phút.', [[0, '5']],
                'Nghỉ ngắn giúp não hồi phục.', $d);
            $this->fill($L, $g, 'Việc quan trọng và gấp nên được ___ tiên làm trước.', [[0, 'ưu']],
                'Ưu tiên việc quan trọng — nguyên tắc vàng.', $d);
            $this->fill($L, $g, 'Học sinh cần ngủ đủ khoảng ___ tiếng mỗi đêm.', [[0, '8']],
                '8-10 tiếng mỗi đêm cho lứa tuổi học sinh.', $d);
            $this->fill($L, $g, 'Bản kế hoạch sắp xếp công việc trong ngày gọi là thời gian ___.', [[0, 'biểu']],
                'Thời gian biểu: học, chơi, nghỉ hợp lý.', $d);
        }
    }

    private function seedTnhnKn62(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Khi người khác đang nói, em nên làm gì?',
                ['Lắng nghe, không ngắt lời', 'Ngắt lời ngay',
                 'Bỏ đi chỗ khác', 'Nói to hơn'], 0,
                'Lắng nghe tôn trọng người nói và giúp hiểu đúng ý.', $d);
            $this->quiz($L, $g, 'Để làm việc nhóm hiệu quả, điều quan trọng nhất là gì?',
                ['Phân công rõ ràng', 'Một người làm hết',
                 'Không cần kế hoạch', 'Ai muốn làm gì thì làm'], 0,
                'Phân công rõ: ai làm gì, khi nào xong.', $d);
            $this->quiz($L, $g, 'Khi bạn có ý kiến khác với mình, em nên làm gì?',
                ['Tôn trọng và thảo luận', 'Cãi nhau to',
                 'Bỏ ngoài tai', 'Giận dỗi'], 0,
                'Tôn trọng khác biệt giúp nhóm đoàn kết và sáng tạo.', $d);
            $this->quiz($L, $g, 'Nói chuyện lịch sự là như thế nào?',
                ['Dùng lời lẽ nhẹ nhàng, biết cảm ơn xin lỗi', 'Nói to cho oai',
                 'Dùng từ ngữ thô tục', 'Nói trống không'], 0,
                'Lời nói lịch sự thể hiện văn minh.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi biểu hiện với ý nghĩa trong giao tiếp.',
                [['Nhìn người nói', 'Lắng nghe'], ['Không ngắt lời', 'Tôn trọng'],
                 ['Gật đầu', 'Đồng cảm'], ['Hỏi lại khi chưa rõ', 'Muốn hiểu đúng']],
                'Lắng nghe tích cực gồm cả lời nói và cử chỉ.', $d);
            $this->matching($L, $g, 'Nối mỗi việc làm nhóm với giá trị của nó.',
                [['Phân công rõ ràng', 'Ai cũng biết việc'], ['Đúng hẹn', 'Trách nhiệm'],
                 ['Giúp đỡ nhau', 'Đoàn kết'], ['Khen ngợi', 'Động viên']],
                'Nhóm mạnh nhờ mỗi người có trách nhiệm.', $d);
            $this->matching($L, $g, 'Nối mỗi cách nói với đánh giá NÊN – KHÔNG NÊN.',
                [['Nói to, rõ ràng', 'Nên'], ['Dùng từ lịch sự', 'Nên'],
                 ['Biết cảm ơn, xin lỗi', 'Nên'], ['Tranh cãi gay gắt', 'Không nên']],
                'Nói năng văn minh trong mọi tình huống.', $d);
            $this->matching($L, $g, 'Nối mỗi vai trò trong nhóm với nhiệm vụ.',
                [['Nhóm trưởng', 'Phân công, điều phối'],
                 ['Thư ký', 'Ghi chép'],
                 ['Thành viên', 'Làm phần việc được giao'],
                 ['Cả nhóm', 'Cùng chịu trách nhiệm']],
                'Mỗi vai trò đều quan trọng như nhau.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi cách ứng xử vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Lắng nghe khi bạn nói', 'Nên'], ['Nói lời cảm ơn', 'Nên'],
                 ['Ngắt lời người khác', 'Không nên'], ['Nói trống không', 'Không nên']],
                'Lịch sự trong giao tiếp hằng ngày.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Làm việc nhóm cần phân công rõ ràng', 'Đúng'],
                 ['Nên tôn trọng ý kiến của bạn', 'Đúng'],
                 ['Nhóm trưởng nên làm hết mọi việc', 'Sai'],
                 ['Được đổ lỗi cho bạn khi sai', 'Sai']],
                'Cả nhóm cùng làm, cùng chịu trách nhiệm.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi biểu hiện vào nhóm LẮNG NGHE TỐT hoặc CHƯA TỐT.',
                [['Nhìn người nói', 'Tốt'], ['Gật đầu đồng cảm', 'Tốt'],
                 ['Vừa nghe vừa chơi điện thoại', 'Chưa tốt'], ['Ngắt lời liên tục', 'Chưa tốt']],
                'Lắng nghe tốt cần sự tập trung.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nhiệm vụ vào nhóm VAI TRÒ trong nhóm.',
                [['Điều phối chung', 'Nhóm trưởng'], ['Theo dõi tiến độ', 'Nhóm trưởng'],
                 ['Ghi chép', 'Thư ký'], ['Làm phần việc được giao', 'Thành viên']],
                'Ba vai trò cơ bản của một nhóm học tập.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Khi người khác đang nói, không nên ___ lời họ.', [[0, 'ngắt']],
                'Ngắt lời là thiếu tôn trọng.', $d);
            $this->fill($L, $g, 'Làm việc nhóm cần phân ___ công việc rõ ràng.', [[0, 'công']],
                'Phân công: ai làm gì, khi nào xong.', $d);
            $this->fill($L, $g, 'Nói năng ___ sự, lễ phép thể hiện sự văn minh.', [[0, 'lịch']],
                'Lịch sự trong lời nói và hành động.', $d);
            $this->fill($L, $g, 'Người điều phối hoạt động chung của nhóm gọi là nhóm ___.', [[0, 'trưởng']],
                'Nhóm trưởng điều phối, không phải làm thay mọi người.', $d);
        }
    }

    private function seedTnhnKn71(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Khi đang rất tức giận, em nên làm gì trước tiên?',
                ['Hít thở sâu, bình tĩnh lại', 'Hét vào mặt người khác',
                 'Đập phá đồ đạc', 'Đánh nhau'], 0,
                'Hít thở sâu giúp cơn giận lắng xuống trước khi hành động.', $d);
            $this->quiz($L, $g, 'Cảm xúc nào sau đây là cảm xúc tích cực?',
                ['Vui vẻ', 'Giận dữ', 'Sợ hãi', 'Ghen tị'], 0,
                'Vui vẻ, yêu thương, biết ơn là cảm xúc tích cực.', $d);
            $this->quiz($L, $g, 'Vì sao cần nhận diện cảm xúc của chính mình?',
                ['Để kiểm soát hành vi tốt hơn', 'Để giấu cảm xúc',
                 'Để khoe với bạn bè', 'Không cần thiết'], 0,
                'Nhận biết mình đang giận/buồn giúp ta chọn cách ứng xử đúng.', $d);
            $this->quiz($L, $g, 'Khi bạn đang giận mình, cách ứng xử tốt nhất là gì?',
                ['Bình tĩnh lắng nghe bạn', 'Cãi lại ngay',
                 'Bỏ đi không nói gì', 'Mách lẻo'], 0,
                'Bình tĩnh lắng nghe giúp hóa giải mâu thuẫn.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi cảm xúc với biểu hiện thường thấy.',
                [['Vui', 'Cười'], ['Buồn', 'Khóc'],
                 ['Giận', 'Nóng mặt, cau có'], ['Sợ', 'Run, né tránh']],
                'Nhận diện cảm xúc qua biểu hiện bên ngoài.', $d);
            $this->matching($L, $g, 'Nối mỗi cách làm với tác dụng khi tức giận.',
                [['Hít thở sâu', 'Bình tĩnh lại'], ['Đếm từ 1 đến 10', 'Kiềm chế cơn giận'],
                 ['Đi dạo', 'Thư giãn'], ['Tâm sự với người tin cậy', 'Nhẹ lòng']],
                'Bốn cách lành mạnh để xử lý cơn giận.', $d);
            $this->matching($L, $g, 'Nối mỗi cảm xúc với loại của nó.',
                [['Vui vẻ', 'Tích cực'], ['Biết ơn', 'Tích cực'],
                 ['Giận dữ', 'Tiêu cực'], ['Ghen tị', 'Tiêu cực']],
                'Cảm xúc nào cũng bình thường — quan trọng là cách ta ứng xử.', $d);
            $this->matching($L, $g, 'Nối mỗi cách ứng xử khi giận với đánh giá.',
                [['Nói chuyện khi đã bình tĩnh', 'Nên'],
                 ['Viết nhật ký cảm xúc', 'Nên'],
                 ['Hét vào mặt bạn', 'Không nên'],
                 ['Đánh nhau', 'Không nên']],
                'Giải quyết mâu thuẫn bằng lời nói bình tĩnh.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi cảm xúc vào nhóm TÍCH CỰC hoặc TIÊU CỰC.',
                [['Vui', 'Tích cực'], ['Biết ơn', 'Tích cực'],
                 ['Giận dữ', 'Tiêu cực'], ['Buồn bã', 'Tiêu cực']],
                'Cảm xúc tiêu cực cũng cần được chia sẻ, không nên giấu kín.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Khi giận nên hít thở sâu', 'Đúng'],
                 ['Nhận diện cảm xúc giúp kiểm soát tốt', 'Đúng'],
                 ['Được phép đánh bạn khi giận', 'Sai'],
                 ['Cảm xúc tiêu cực phải giấu kín', 'Sai']],
                'Bạo lực không bao giờ đúng; nên chia sẻ cảm xúc với người tin cậy.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cách làm khi giận vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Đếm 1-10', 'Nên'], ['Tâm sự với người tin cậy', 'Nên'],
                 ['Hét vào mặt người khác', 'Không nên'], ['Đập phá đồ đạc', 'Không nên']],
                'Kiềm chế và chia sẻ thay vì bạo lực.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cách xử lý vào nhóm CÁCH BÌNH TĨNH hoặc KHÔNG PHẢI.',
                [['Hít thở sâu', 'Cách bình tĩnh'], ['Đi dạo', 'Cách bình tĩnh'],
                 ['La hét', 'Không phải cách hay'], ['Đánh nhau', 'Không phải cách hay']],
                'Bình tĩnh giúp ta không hối hận về hành động của mình.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Khi tức giận, hãy dừng lại và hít thở thật ___.', [[0, 'sâu']],
                'Hít thở sâu làm chậm nhịp tim, dịu cơn giận.', $d);
            $this->fill($L, $g, 'Đếm từ 1 đến ___ giúp kiềm chế cơn giận.', [[0, '10']],
                'Đếm 1-10 cho ta thời gian bình tĩnh lại.', $d);
            $this->fill($L, $g, 'Không nên làm ___ thương người khác khi đang giận dữ.', [[0, 'tổn']],
                'Tổn thương người khác không giải quyết được gì.', $d);
            $this->fill($L, $g, 'Chia sẻ cảm xúc với người tin cậy giúp lòng nhẹ ___.', [[0, 'nhõm']],
                'Tâm sự là cách lành mạnh để vơi bớt nỗi buồn.', $d);
        }
    }

    private function seedTnhnKn72(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Bước đầu tiên khi giải quyết một vấn đề là gì?',
                ['Xác định vấn đề là gì', 'Chọn ngay giải pháp',
                 'Đổ lỗi cho người khác', 'Bỏ cuộc'], 0,
                'Phải biết rõ vấn đề là gì mới giải quyết được.', $d);
            $this->quiz($L, $g, 'Sau khi liệt kê các giải pháp có thể, cần làm gì tiếp?',
                ['Chọn giải pháp tốt nhất', 'Làm bừa một cách',
                 'Hỏi ý kiến rồi bỏ đó', 'Chờ người khác giải quyết'], 0,
                'So sánh các giải pháp rồi chọn cách tốt nhất.', $d);
            $this->quiz($L, $g, 'Bước cuối cùng trong quy trình giải quyết vấn đề là gì?',
                ['Thực hiện và đánh giá kết quả', 'Quên vấn đề đi',
                 'Khoe với bạn bè', 'Đổ lỗi'], 0,
                'Đánh giá giúp rút kinh nghiệm cho lần sau.', $d);
            $this->quiz($L, $g, 'Khi gặp vấn đề khó vượt quá khả năng, em nên làm gì?',
                ['Bình tĩnh và nhờ người lớn giúp đỡ', 'Hoảng loạn',
                 'Giấu kín không nói với ai', 'Bỏ học'], 0,
                'Nhờ người lớn giúp không phải là yếu đuối.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi bước với nội dung (4 bước đầu).',
                [['Bước 1', 'Xác định vấn đề là gì'],
                 ['Bước 2', 'Tìm hiểu nguyên nhân'],
                 ['Bước 3', 'Liệt kê các giải pháp'],
                 ['Bước 4', 'Chọn giải pháp tốt nhất']],
                'Bước 5: thực hiện và đánh giá kết quả.', $d);
            $this->matching($L, $g, 'Nối mỗi tình huống với hướng xử lý phù hợp.',
                [['Bị điểm kém', 'Tìm nguyên nhân và kế hoạch ôn tập'],
                 ['Làm mất đồ dùng', 'Bình tĩnh tìm lại'],
                 ['Cãi nhau với bạn', 'Lắng nghe nhau'],
                 ['Quên làm bài tập', 'Lập kế hoạch nhắc nhở']],
                'Mỗi vấn đề có cách giải quyết riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi cách tìm hiểu với nhóm của nó.',
                [['Hỏi thầy cô', 'Nhờ người lớn giúp đỡ'],
                 ['Hỏi cha mẹ', 'Nhờ người lớn giúp đỡ'],
                 ['Tự suy nghĩ kỹ', 'Tự giải quyết'],
                 ['Tra cứu sách vở', 'Tìm thông tin']],
                'Kết hợp nhiều cách để hiểu rõ vấn đề.', $d);
            $this->matching($L, $g, 'Nối mỗi việc với bước hoặc thái độ tương ứng.',
                [['Thực hiện giải pháp', 'Bước 5'],
                 ['Đánh giá kết quả', 'Bước 5'],
                 ['Kiên trì theo đuổi', 'Thái độ tốt'],
                 ['Bỏ cuộc giữa chừng', 'Thái độ chưa tốt']],
                'Kiên trì đến cùng mới giải quyết triệt để.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm BƯỚC ĐẦU hoặc BƯỚC CUỐI của quy trình.',
                [['Xác định vấn đề', 'Bước đầu'], ['Tìm nguyên nhân', 'Bước đầu'],
                 ['Thực hiện giải pháp', 'Bước cuối'], ['Đánh giá kết quả', 'Bước cuối']],
                'Đầu: hiểu vấn đề. Cuối: làm và đánh giá.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Bước 1 là xác định vấn đề', 'Đúng'],
                 ['Cần đánh giá sau khi thực hiện', 'Đúng'],
                 ['Nên chọn ngay giải pháp đầu tiên nghĩ ra', 'Sai'],
                 ['Gặp khó nên bỏ cuộc ngay', 'Sai']],
                'So sánh các giải pháp trước khi chọn; kiên trì không bỏ cuộc.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi cách ứng xử vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Bình tĩnh phân tích', 'Nên'], ['Nhờ người lớn giúp khi cần', 'Nên'],
                 ['Hoảng loạn', 'Không nên'], ['Đổ lỗi cho người khác', 'Không nên']],
                'Bình tĩnh + trách nhiệm = giải quyết tốt.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi vấn đề vào nhóm TỰ GIẢI QUYẾT hoặc CẦN NGƯỜI LỚN GIÚP.',
                [['Quên mang bút', 'Tự giải quyết'], ['Làm mất đồ dùng', 'Tự giải quyết'],
                 ['Bị bạn bắt nạt', 'Cần người lớn giúp'], ['Phát hiện đám cháy', 'Cần người lớn giúp']],
                'Việc nhỏ tự làm; việc nguy hiểm phải báo người lớn.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Bước đầu tiên để giải quyết vấn đề là ___ định vấn đề là gì.', [[0, 'xác']],
                'Xác định đúng vấn đề là đã giải quyết được một nửa.', $d);
            $this->fill($L, $g, 'Sau khi liệt kê các giải pháp, cần ___ giải pháp tốt nhất.', [[0, 'chọn']],
                'So sánh ưu nhược điểm rồi chọn.', $d);
            $this->fill($L, $g, 'Bước cuối cùng là thực hiện và ___ giá kết quả.', [[0, 'đánh']],
                'Đánh giá để rút kinh nghiệm.', $d);
            $this->fill($L, $g, 'Khi gặp vấn đề vượt quá khả năng, hãy nhờ người ___ giúp đỡ.', [[0, 'lớn']],
                'Người lớn: cha mẹ, thầy cô.', $d);
        }
    }

    // ================= TNHN: an toàn & môi trường =================

    private function seedTnhnAt71(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Khi ngồi trên xe máy, xe đạp điện, em phải làm gì?',
                ['Đội mũ bảo hiểm', 'Không cần làm gì', 'Đứng lên xe', 'Thò đầu ra ngoài'], 0,
                'Mũ bảo hiểm bảo vệ đầu khi xảy ra va chạm.', $d);
            $this->quiz($L, $g, 'Qua đường ở đâu là an toàn nhất?',
                ['Vạch kẻ đường cho người đi bộ', 'Bất kỳ chỗ nào',
                 'Chỗ đông xe', 'Chỗ khuất tầm nhìn'], 0,
                'Vạch kẻ đường (vạch ngựa vằn) là nơi xe phải nhường người đi bộ.', $d);
            $this->quiz($L, $g, 'Xe đạp, xe máy đi bên nào của đường là đúng luật?',
                ['Bên phải', 'Bên trái', 'Giữa đường', 'Lề đường ngược chiều'], 0,
                'Việt Nam đi bên phải đường.', $d);
            $this->quiz($L, $g, 'Biển báo hình tròn viền đỏ là loại biển báo nào?',
                ['Biển cấm', 'Biển chỉ dẫn', 'Biển nguy hiểm', 'Biển quảng cáo'], 0,
                'Tròn viền đỏ: biển cấm. Tam giác viền đỏ: biển nguy hiểm.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi vật/quy tắc với ý nghĩa an toàn.',
                [['Mũ bảo hiểm', 'Bảo vệ đầu'], ['Vạch kẻ đường', 'Qua đường an toàn'],
                 ['Đèn đỏ', 'Dừng lại'], ['Đèn xanh', 'Được đi']],
                'Tuân thủ tín hiệu đèn là quy tắc cơ bản nhất.', $d);
            $this->matching($L, $g, 'Nối mỗi biển báo với loại của nó.',
                [['Biển tròn viền đỏ', 'Biển cấm'],
                 ['Biển tam giác viền đỏ', 'Biển nguy hiểm'],
                 ['Biển tròn nền xanh', 'Biển hiệu lệnh'],
                 ['Biển hình chữ nhật', 'Biển chỉ dẫn, thông tin']],
                'Nhận biết biển báo giúp tham gia giao thông an toàn.', $d);
            $this->matching($L, $g, 'Nối mỗi hành vi với đánh giá ĐÚNG – SAI luật.',
                [['Đi bên phải đường', 'Đúng luật'], ['Nhường người đi bộ', 'Đúng luật'],
                 ['Dàn hàng ngang', 'Sai luật'], ['Vượt đèn đỏ', 'Sai luật']],
                'Đi đúng luật bảo vệ chính mình và mọi người.', $d);
            $this->matching($L, $g, 'Nối mỗi hành vi với mức độ an toàn.',
                [['Không lạng lách', 'An toàn'], ['Không dùng điện thoại khi lái', 'An toàn'],
                 ['Phóng nhanh vượt ẩu', 'Nguy hiểm'], ['Uống rượu khi lái xe', 'Nguy hiểm']],
                'Lạng lách, rượu bia là nguyên nhân hàng đầu gây tai nạn.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi hành vi vào nhóm ĐÚNG LUẬT hoặc SAI LUẬT.',
                [['Đội mũ bảo hiểm', 'Đúng luật'], ['Đi bên phải', 'Đúng luật'],
                 ['Vượt đèn đỏ', 'Sai luật'], ['Dàn hàng ba', 'Sai luật']],
                'Đúng luật: mũ bảo hiểm, đi bên phải. Sai luật: vượt đèn đỏ, dàn hàng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Phải đội mũ bảo hiểm khi đi xe máy', 'Đúng'],
                 ['Qua đường ở vạch kẻ là an toàn nhất', 'Đúng'],
                 ['Được vượt đèn đỏ khi đường vắng', 'Sai'],
                 ['Xe đạp được đi dàn hàng ngang', 'Sai']],
                'Đèn đỏ luôn phải dừng; không dàn hàng ngang.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi biển báo vào nhóm LOẠI BIỂN.',
                [['Biển tròn viền đỏ', 'Biển cấm'], ['Biển cấm xe đạp', 'Biển cấm'],
                 ['Biển tam giác viền đỏ', 'Biển nguy hiểm'], ['Biển tròn nền xanh', 'Biển hiệu lệnh']],
                'Tròn viền đỏ: biển cấm; tam giác viền đỏ: biển nguy hiểm; tròn nền xanh: biển hiệu lệnh.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi hành vi vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Đi đúng làn đường', 'An toàn'], ['Quan sát khi qua đường', 'An toàn'],
                 ['Lạng lách đánh võng', 'Nguy hiểm'], ['Vừa lái xe vừa nghe điện thoại', 'Nguy hiểm']],
                'Mất tập trung khi lái xe rất nguy hiểm.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Khi đi xe máy, bắt buộc phải đội mũ bảo ___.', [[0, 'hiểm']],
                'Mũ bảo hiểm đạt chuẩn bảo vệ đầu.', $d);
            $this->fill($L, $g, 'Người đi bộ qua đường ở ___ kẻ dành cho người đi bộ.', [[0, 'vạch']],
                'Vạch kẻ đường: nơi an toàn nhất để qua đường.', $d);
            $this->fill($L, $g, 'Gặp đèn ___ phải dừng lại trước vạch.', [[0, 'đỏ']],
                'Đèn đỏ: dừng. Đèn xanh: đi. Đèn vàng: chậm lại.', $d);
            $this->fill($L, $g, 'Biển báo hình tròn viền đỏ là biển ___.', [[0, 'cấm']],
                'Biển cấm: không được làm điều ghi trên biển.', $d);
        }
    }

    private function seedTnhnAt72(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Số điện thoại của lực lượng cứu hỏa là số nào?',
                ['114', '115', '113', '111'], 0,
                '114: cứu hỏa. 115: cấp cứu. 113: công an.', $d);
            $this->quiz($L, $g, 'Khi có cháy trong tòa nhà, cách thoát hiểm đúng là gì?',
                ['Đi thang bộ, cúi thấp người', 'Đi thang máy cho nhanh',
                 'Trốn trong phòng kín', 'Nhảy từ cửa sổ'], 0,
                'Thang máy khi cháy rất nguy hiểm; cúi thấp tránh khói độc.', $d);
            $this->quiz($L, $g, 'Có nên dùng thang máy khi đang có cháy không?',
                ['Không, tuyệt đối không', 'Có, cho nhanh',
                 'Tùy tình huống', 'Chỉ khi cháy nhỏ'], 0,
                'Cháy có thể gây mất điện, kẹt thang máy — tuyệt đối không dùng.', $d);
            $this->quiz($L, $g, 'Khi quần áo bị bén lửa, nên làm gì?',
                ['Nằm xuống lăn để dập lửa', 'Chạy thật nhanh',
                 'Đứng yên chờ', 'Dùng nước sôi dội'], 0,
                'Nằm lăn dập lửa; chạy làm lửa bùng to hơn.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi số điện thoại với lực lượng tương ứng.',
                [['114', 'Cứu hỏa'], ['115', 'Cấp cứu y tế'],
                 ['113', 'Công an'], ['111', 'Bảo vệ trẻ em']],
                'Nhớ 4 số khẩn cấp: 111 – 113 – 114 – 115.', $d);
            $this->matching($L, $g, 'Nối mỗi bước với thứ tự dùng bình chữa cháy.',
                [['Rút chốt an toàn', 'Bước 1'], ['Hướng vòi vào gốc lửa', 'Bước 2'],
                 ['Bóp cò', 'Bước 3'], ['Quét vòi qua lại', 'Bước 4']],
                'Dùng bình chữa cháy: rút chốt – hướng vòi – bóp cò – quét.', $d);
            $this->matching($L, $g, 'Nối mỗi việc làm khi cháy với lý do.',
                [['Cúi thấp khi thoát hiểm', 'Tránh hít khói độc'],
                 ['Dùng khăn ướt che mũi', 'Lọc khói'],
                 ['Không quay lại lấy đồ', 'Bảo toàn tính mạng'],
                 ['Hô hoán báo động', 'Cứu nhiều người']],
                'Tính mạng quan trọng hơn tài sản.', $d);
            $this->matching($L, $g, 'Nối mỗi việc làm với vai trò PHÒNG CHÁY hay NGUY HIỂM.',
                [['Tắt bếp khi ra ngoài', 'Phòng cháy'],
                 ['Không đốt vàng mã bừa bãi', 'Phòng cháy'],
                 ['Để xăng gần lửa', 'Nguy hiểm'],
                 ['Dùng điện quá tải', 'Nguy hiểm']],
                'Phòng cháy tốt hơn chữa cháy.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi việc làm khi có cháy vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Gọi 114 ngay', 'Nên'], ['Thoát bằng thang bộ', 'Nên'],
                 ['Dùng thang máy', 'Không nên'], ['Quay lại lấy đồ', 'Không nên']],
                'Nhanh chóng, bình tĩnh, đúng cách.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Số cứu hỏa là 114', 'Đúng'],
                 ['Khi cháy nên cúi thấp để tránh khói', 'Đúng'],
                 ['Được dùng thang máy khi cháy', 'Sai'],
                 ['Quần áo cháy thì chạy thật nhanh', 'Sai']],
                'Chạy làm lửa to hơn; thang máy nguy hiểm khi cháy.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm PHÒNG CHÁY hoặc CHỮA CHÁY.',
                [['Tắt thiết bị điện khi ra ngoài', 'Phòng cháy'],
                 ['Không để vật dễ cháy gần bếp', 'Phòng cháy'],
                 ['Dùng bình chữa cháy', 'Chữa cháy'],
                 ['Gọi 114', 'Chữa cháy']],
                'Phòng trước, chữa sau.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm TRƯỚC hoặc TRONG khi cháy.',
                [['Học cách dùng bình chữa cháy', 'Trước khi cháy'],
                 ['Biết lối thoát hiểm', 'Trước khi cháy'],
                 ['Cúi thấp di chuyển', 'Trong khi cháy'],
                 ['Gọi 114 báo cháy', 'Trong khi cháy']],
                'Chuẩn bị trước giúp xử lý tốt khi sự cố xảy ra.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Số điện thoại cứu hỏa là ___.', [[0, '114']],
                '114 — cứu hỏa.', $d);
            $this->fill($L, $g, 'Khi cháy trong nhà cao tầng, phải thoát hiểm bằng thang ___.', [[0, 'bộ']],
                'Thang bộ an toàn; thang máy nguy hiểm khi cháy.', $d);
            $this->fill($L, $g, 'Khi di chuyển trong đám cháy, nên ___ thấp người để tránh khói độc.', [[0, 'cúi']],
                'Khói độc bay lên cao; càng thấp càng an toàn.', $d);
            $this->fill($L, $g, 'Tuyệt đối không dùng thang ___ khi có cháy.', [[0, 'máy']],
                'Mất điện sẽ kẹt trong thang máy.', $d);
        }
    }

    private function seedTnhnAt81(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, '3R trong bảo vệ môi trường là gì?',
                ['Giảm thiểu – Tái sử dụng – Tái chế', 'Rác – Ruồi – Rận',
                 'Trồng – Tưới – Thu hoạch', 'Đốt – Chôn – Xả'], 0,
                '3R: Reduce (giảm thiểu), Reuse (tái sử dụng), Recycle (tái chế).', $d);
            $this->quiz($L, $g, 'Vỏ chai nhựa thuộc loại rác nào?',
                ['Rác tái chế', 'Rác hữu cơ', 'Rác nguy hại', 'Không phải rác'], 0,
                'Chai nhựa tái chế được thành nhiều sản phẩm mới.', $d);
            $this->quiz($L, $g, 'Pin cũ, ắc quy thuộc loại rác nào?',
                ['Rác nguy hại', 'Rác hữu cơ', 'Rác tái chế', 'Rác thường'], 0,
                'Pin chứa hóa chất độc — phải thu gom riêng, không vứt bừa bãi.', $d);
            $this->quiz($L, $g, 'Túi nilon mất khoảng bao lâu để phân hủy hoàn toàn?',
                ['Hàng trăm năm', 'Vài ngày', 'Vài tuần', '1 năm'], 0,
                'Túi nilon phân hủy rất chậm — hãy hạn chế sử dụng.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi chữ R với ý nghĩa.',
                [['Reduce', 'Giảm thiểu rác'], ['Reuse', 'Tái sử dụng'],
                 ['Recycle', 'Tái chế'], ['Refuse', 'Từ chối đồ nhựa dùng một lần']],
                '4R mở rộng giúp giảm rác thải nhựa.', $d);
            $this->matching($L, $g, 'Nối mỗi loại rác với ví dụ.',
                [['Rác hữu cơ', 'Vỏ rau củ'], ['Rác tái chế', 'Chai nhựa, giấy'],
                 ['Rác nguy hại', 'Pin cũ'], ['Rác tái chế', 'Lon nhôm']],
                'Phân loại đúng giúp xử lý rác hiệu quả.', $d);
            $this->matching($L, $g, 'Nối mỗi hành động với lợi ích môi trường.',
                [['Mang túi vải đi chợ', 'Giảm túi nilon'],
                 ['Dùng bình nước cá nhân', 'Giảm chai nhựa'],
                 ['Tắt điện khi ra ngoài', 'Tiết kiệm năng lượng'],
                 ['Đi xe đạp', 'Giảm khí thải']],
                'Hành động nhỏ mỗi ngày bảo vệ môi trường lớn.', $d);
            $this->matching($L, $g, 'Nối mỗi loại rác với cách xử lý.',
                [['Vỏ rau củ', 'Ủ làm phân compost'], ['Chai nhựa', 'Tái chế'],
                 ['Pin cũ', 'Thu gom riêng'], ['Giấy báo cũ', 'Tái chế']],
                'Rác đúng nơi, đúng cách.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi loại rác vào nhóm PHÂN LOẠI đúng.',
                [['Vỏ chuối', 'Rác hữu cơ'], ['Lá cây rụng', 'Rác hữu cơ'],
                 ['Chai nhựa', 'Rác tái chế'], ['Pin cũ', 'Rác nguy hại']],
                'Hữu cơ – tái chế – nguy hại: 3 nhóm cơ bản.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nên phân loại rác tại nguồn', 'Đúng'],
                 ['3R giúp bảo vệ môi trường', 'Đúng'],
                 ['Pin cũ vứt chung rác sinh hoạt được', 'Sai'],
                 ['Túi nilon phân hủy trong vài ngày', 'Sai']],
                'Pin phải thu gom riêng; nilon phân hủy hàng trăm năm.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Mang túi vải đi chợ', 'Nên'], ['Tiết kiệm nước', 'Nên'],
                 ['Xả rác bừa bãi', 'Không nên'], ['Đốt rác thải nhựa', 'Không nên']],
                'Đốt nhựa tạo khí độc hại sức khỏe.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi loại rác vào nhóm PHÂN HỦY NHANH hoặc PHÂN HỦY RẤT LÂU.',
                [['Giấy ăn', 'Phân hủy nhanh'], ['Vỏ trái cây', 'Phân hủy nhanh'],
                 ['Túi nilon', 'Phân hủy rất lâu'], ['Chai thủy tinh', 'Phân hủy rất lâu']],
                'Rác hữu cơ, giấy phân hủy nhanh; nilon, thủy tinh rất lâu.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Ba chữ R là giảm thiểu, tái sử dụng và tái ___.', [[0, 'chế']],
                'Recycle: tái chế thành sản phẩm mới.', $d);
            $this->fill($L, $g, 'Pin cũ, ắc quy là rác ___ hại cần thu gom riêng.', [[0, 'nguy']],
                'Rác nguy hại chứa hóa chất độc.', $d);
            $this->fill($L, $g, 'Túi nilon cần hàng ___ năm mới phân hủy hết.', [[0, 'trăm']],
                'Hàng trăm năm — hãy dùng túi vải.', $d);
            $this->fill($L, $g, 'Mang ___ vải đi chợ giúp giảm rác thải nilon.', [[0, 'túi']],
                'Túi vải dùng được nhiều lần.', $d);
        }
    }

    private function seedTnhnAt82(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Thông tin nào KHÔNG nên chia sẻ công khai trên mạng?',
                ['Địa chỉ nhà', 'Món ăn yêu thích', 'Màu sắc yêu thích', 'Sở thích đọc sách'], 0,
                'Địa chỉ nhà, số điện thoại, trường lớp là thông tin nhạy cảm.', $d);
            $this->quiz($L, $g, 'Mật khẩu mạnh nên như thế nào?',
                ['Dài, gồm chữ hoa, chữ thường, số và ký tự đặc biệt', 'Là ngày sinh của mình',
                 'Là số điện thoại', 'Càng ngắn càng tốt'], 0,
                'Mật khẩu mạnh khó đoán, khó bẻ khóa.', $d);
            $this->quiz($L, $g, 'Khi nhận tin nhắn "chúc mừng bạn trúng thưởng, bấm link nhận quà", em nên làm gì?',
                ['Xóa ngay, không bấm vào link', 'Bấm link nhận quà ngay',
                 'Chuyển tiếp cho bạn bè', 'Nhập thông tin cá nhân'], 0,
                'Đó là lừa đảo — bấm link có thể mất tài khoản, tiền bạc.', $d);
            $this->quiz($L, $g, 'Khi bị bắt nạt trên mạng, em nên làm gì?',
                ['Chặn, báo cáo và báo cho người lớn', 'Im lặng chịu đựng',
                 'Chửi lại', 'Gặp mặt đối phương'], 0,
                'Bắt nạt mạng phải được ngăn chặn — đừng im lặng.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi thông tin với mức độ chia sẻ.',
                [['Địa chỉ nhà', 'Không chia sẻ'], ['Số điện thoại', 'Không chia sẻ'],
                 ['Ảnh thẻ học sinh', 'Không chia sẻ'], ['Sở thích chung', 'Có thể chia sẻ']],
                'Thông tin cá nhân chỉ chia sẻ với người tin cậy.', $d);
            $this->matching($L, $g, 'Nối mỗi thói quen bảo mật với đánh giá.',
                [['Đặt mật khẩu mạnh', 'An toàn'], ['Bật xác thực 2 lớp', 'An toàn'],
                 ['Đăng xuất khỏi máy lạ', 'An toàn'], ['Dán mật khẩu lên màn hình', 'Không an toàn']],
                'Bảo mật tốt bảo vệ tài khoản của em.', $d);
            $this->matching($L, $g, 'Nối mỗi dấu hiệu với kết luận.',
                [['Tin nhắn trúng thưởng kèm link', 'Lừa đảo'],
                 ['Người lạ nhờ chuyển tiền gấp', 'Lừa đảo'],
                 ['Link lạ không rõ nguồn', 'Không bấm vào'],
                 ['Người lạ xin ảnh riêng tư', 'Cảnh giác, báo người lớn']],
                'Cảnh giác với mọi điều "quá tốt để là thật".', $d);
            $this->matching($L, $g, 'Nối mỗi tình huống mạng với cách xử lý.',
                [['Bị chửi bới trên mạng', 'Chặn và báo cáo'],
                 ['Bị dụ dỗ gặp mặt ngoài đời', 'Báo ngay người lớn'],
                 ['Thấy nội dung xấu', 'Báo cáo nền tảng'],
                 ['Bạn bè rủ chơi game lành mạnh', 'Có thể tham gia']],
                'Nguy hiểm: chặn + báo. An toàn: tham gia lành mạnh.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN.',
                [['Đặt mật khẩu mạnh', 'Nên'], ['Bật xác thực 2 lớp', 'Nên'],
                 ['Chia sẻ địa chỉ nhà cho người lạ', 'Không nên'], ['Bấm vào link trúng thưởng', 'Không nên']],
                'Bảo mật tài khoản và thông tin cá nhân.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Không chia sẻ số điện thoại công khai', 'Đúng'],
                 ['Hẹn gặp người lạ quen qua mạng một mình là nguy hiểm', 'Đúng'],
                 ['Mật khẩu nên là ngày sinh cho dễ nhớ', 'Sai'],
                 ['Bị bắt nạt mạng nên im lặng chịu đựng', 'Sai']],
                'Ngày sinh dễ đoán; bắt nạt mạng phải báo cáo.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi tình huống vào nhóm AN TOÀN hoặc NGUY HIỂM.',
                [['Chơi game cùng bạn quen', 'An toàn'], ['Hỏi ý kiến cha mẹ trước', 'An toàn'],
                 ['Hẹn gặp người lạ một mình', 'Nguy hiểm'], ['Chia sẻ ảnh nhạy cảm', 'Nguy hiểm']],
                'Người lạ trên mạng có thể không như họ nói.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi thông tin vào nhóm NHẠY CẢM hoặc ÍT NHẠY CẢM.',
                [['Tên trường lớp', 'Nhạy cảm'], ['Địa chỉ nhà', 'Nhạy cảm'],
                 ['Món ăn yêu thích', 'Ít nhạy cảm'], ['Màu sắc yêu thích', 'Ít nhạy cảm']],
                'Thông tin nhạy cảm giúp kẻ xấu tìm ra em.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Không chia sẻ địa chỉ nhà, số điện thoại cho người ___ trên mạng.', [[0, 'lạ']],
                'Người lạ có thể lợi dụng thông tin của em.', $d);
            $this->fill($L, $g, 'Mật khẩu ___ gồm chữ hoa, chữ thường, số và ký tự đặc biệt.', [[0, 'mạnh']],
                'Mật khẩu mạnh bảo vệ tài khoản.', $d);
            $this->fill($L, $g, 'Nhận được tin nhắn trúng thưởng kèm link lạ, tuyệt đối không ___ vào.', [[0, 'bấm']],
                'Bấm link lạ có thể mất tài khoản.', $d);
            $this->fill($L, $g, 'Khi bị bắt nạt trên mạng, hãy chặn và báo cho người ___ biết.', [[0, 'lớn']],
                'Cha mẹ, thầy cô sẽ giúp em.', $d);
        }
    }

    // ================= TNHN: định hướng nghề nghiệp =================

    private function seedTnhnNghe81(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Bác sĩ thuộc nhóm nghề nào?',
                ['Y tế', 'Giáo dục', 'Xây dựng', 'Nghệ thuật'], 0,
                'Bác sĩ khám chữa bệnh — nhóm nghề y tế.', $d);
            $this->quiz($L, $g, 'Kỹ sư phần mềm (lập trình viên) thuộc nhóm nghề nào?',
                ['Công nghệ thông tin', 'Nông nghiệp', 'Y tế', 'Du lịch'], 0,
                'Lập trình viên viết phần mềm — nhóm công nghệ thông tin.', $d);
            $this->quiz($L, $g, 'Nghề nào sau đây thuộc nhóm nghề nghệ thuật?',
                ['Họa sĩ', 'Bác sĩ', 'Kỹ sư', 'Kế toán'], 0,
                'Họa sĩ, ca sĩ, diễn viên — nhóm nghệ thuật.', $d);
            $this->quiz($L, $g, 'Giáo viên làm công việc chính là gì?',
                ['Dạy học, truyền đạt kiến thức', 'Khám chữa bệnh',
                 'Xây nhà', 'Nấu ăn'], 0,
                'Giáo viên dạy học — nhóm nghề giáo dục.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi nghề với công việc đặc trưng.',
                [['Bác sĩ', 'Khám chữa bệnh'], ['Giáo viên', 'Dạy học'],
                 ['Kỹ sư', 'Thiết kế, xây dựng'], ['Nông dân', 'Trồng trọt, chăn nuôi']],
                'Bốn nhóm nghề cơ bản của xã hội.', $d);
            $this->matching($L, $g, 'Nối mỗi nghề với công việc của nó.',
                [['Lập trình viên', 'Viết phần mềm'], ['Kiến trúc sư', 'Thiết kế nhà cửa'],
                 ['Luật sư', 'Bảo vệ pháp lý'], ['Đầu bếp', 'Nấu ăn']],
                'Mỗi nghề có chuyên môn riêng.', $d);
            $this->matching($L, $g, 'Nối mỗi nhóm nghề với phẩm chất cần có.',
                [['Nghề y', 'Tận tâm với người bệnh'],
                 ['Nghề giáo', 'Kiên nhẫn với học trò'],
                 ['Nghề kỹ thuật', 'Tư duy logic'],
                 ['Nghề nghệ thuật', 'Sáng tạo']],
                'Mỗi nghề đòi hỏi phẩm chất phù hợp.', $d);
            $this->matching($L, $g, 'Nối mỗi nghề với nhiệm vụ xã hội.',
                [['Công an', 'Bảo vệ an ninh'], ['Bộ đội', 'Bảo vệ Tổ quốc'],
                 ['Nhà báo', 'Đưa tin trung thực'], ['Phi công', 'Lái máy bay an toàn']],
                'Mọi nghề đều có trách nhiệm với xã hội.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi nghề vào nhóm NGHỀ tương ứng.',
                [['Bác sĩ', 'Y tế'], ['Y tá', 'Y tế'],
                 ['Giáo viên', 'Giáo dục'], ['Kỹ sư', 'Kỹ thuật']],
                'Y tế: bác sĩ, y tá. Giáo dục: giáo viên. Kỹ thuật: kỹ sư.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Bác sĩ thuộc nhóm nghề y tế', 'Đúng'],
                 ['Mọi nghề chân chính đều đáng quý', 'Đúng'],
                 ['Lập trình viên thuộc nhóm nông nghiệp', 'Sai'],
                 ['Chỉ nghề bác sĩ là quan trọng', 'Sai']],
                'Mọi nghề đều quan trọng, không nghề nào "sang" hơn nghề nào.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nghề vào nhóm LÀM TRONG NHÀ hoặc NGOÀI TRỜI.',
                [['Giáo viên', 'Trong nhà'], ['Nhân viên văn phòng', 'Trong nhà'],
                 ['Nông dân', 'Ngoài trời'], ['Công nhân xây dựng', 'Ngoài trời']],
                'Môi trường làm việc khác nhau tùy nghề.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi nghề vào nhóm CHĂM SÓC CON NGƯỜI hoặc KỸ THUẬT.',
                [['Bác sĩ', 'Chăm sóc con người'], ['Giáo viên', 'Chăm sóc con người'],
                 ['Thợ điện', 'Kỹ thuật'], ['Thợ sửa xe', 'Kỹ thuật']],
                'Hai hướng nghề lớn: phục vụ con người và kỹ thuật.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Người khám chữa bệnh cho mọi người là bác ___.', [[0, 'sĩ']],
                'Bác sĩ — nhóm nghề y tế.', $d);
            $this->fill($L, $g, 'Người viết phần mềm máy tính gọi là lập trình ___.', [[0, 'viên']],
                'Lập trình viên — nhóm công nghệ thông tin.', $d);
            $this->fill($L, $g, 'Nghề họa sĩ, ca sĩ thuộc nhóm nghề nghệ ___.', [[0, 'thuật']],
                'Nhóm nghệ thuật cần sự sáng tạo.', $d);
            $this->fill($L, $g, 'Mọi nghề nghiệp chân chính đều đáng ___ trọng.', [[0, 'tôn']],
                'Không có nghề cao quý hơn — chỉ có người lao động chân chính.', $d);
        }
    }

    private function seedTnhnNghe82(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Chọn nghề nghiệp nên dựa vào điều gì?',
                ['Sở thích và năng lực của bản thân', 'Bạn bè chọn gì',
                 'Nghề nào đang "hot"', 'Nghề nào nhàn hạ'], 0,
                'Nghề phù hợp năng lực và đam mê mới bền vững.', $d);
            $this->quiz($L, $g, 'Điểm mạnh của một người là gì?',
                ['Điều mình làm tốt', 'Điều mình làm kém',
                 'Điều mình ghét', 'Điều mình sợ'], 0,
                'Phát huy điểm mạnh giúp ta tự tin và thành công.', $d);
            $this->quiz($L, $g, 'Có nên chọn nghề chạy theo phong trào không?',
                ['Không, nên theo năng lực bản thân', 'Có, cho bằng bạn bè',
                 'Tùy hứng', 'Không quan tâm'], 0,
                'Phong trào qua đi, chỉ năng lực và đam mê ở lại.', $d);
            $this->quiz($L, $g, 'Khi biết điểm yếu của mình, nên làm gì?',
                ['Rèn luyện để cải thiện', 'Giấu kín',
                 'Tự ti, bỏ cuộc', 'Đổ lỗi'], 0,
                'Điểm yếu nào cũng cải thiện được bằng rèn luyện.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi khái niệm với ý nghĩa.',
                [['Sở thích', 'Điều mình yêu thích'],
                 ['Điểm mạnh', 'Điều mình làm tốt'],
                 ['Điểm yếu', 'Điều cần cải thiện'],
                 ['Giá trị', 'Điều mình coi trọng']],
                'Bốn yếu tố để hiểu bản thân.', $d);
            $this->matching($L, $g, 'Nối mỗi biểu hiện với năng khiếu tương ứng.',
                [['Thích vẽ, vẽ đẹp', 'Năng khiếu mỹ thuật'],
                 ['Giỏi toán', 'Tư duy logic'],
                 ['Thích trò chuyện', 'Kỹ năng giao tiếp'],
                 ['Khéo tay', 'Phù hợp nghề thủ công']],
                'Nhận ra năng khiếu sớm giúp định hướng đúng.', $d);
            $this->matching($L, $g, 'Nối mỗi cách khám phá bản thân với đánh giá.',
                [['Viết nhật ký', 'Nên'], ['Hỏi nhận xét của bạn bè', 'Nên'],
                 ['Thử nhiều hoạt động', 'Nên'], ['So sánh mình với người khác', 'Không nên']],
                'Khám phá bản thân bằng trải nghiệm, không bằng so sánh.', $d);
            $this->matching($L, $g, 'Nối mỗi phẩm chất với đánh giá.',
                [['Kiên trì', 'Phẩm chất tốt'], ['Trung thực', 'Phẩm chất tốt'],
                 ['Lười biếng', 'Cần sửa'], ['Nóng nảy', 'Cần sửa']],
                'Phẩm chất tốt là hành trang vào nghề.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi ví dụ vào nhóm ĐIỂM MẠNH hoặc ĐIỂM YẾU.',
                [['Vẽ đẹp', 'Điểm mạnh'], ['Hát hay', 'Điểm mạnh'],
                 ['Hay quên', 'Điểm yếu'], ['Ngại giao tiếp', 'Điểm yếu']],
                'Ai cũng có điểm mạnh và điểm yếu — điều quan trọng là nhận ra chúng.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Nên chọn nghề phù hợp năng lực', 'Đúng'],
                 ['Ai cũng có điểm mạnh riêng', 'Đúng'],
                 ['Nên chạy theo nghề "hot" dù không thích', 'Sai'],
                 ['Điểm yếu không thể cải thiện', 'Sai']],
                'Điểm yếu cải thiện được bằng rèn luyện kiên trì.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi việc làm vào nhóm NÊN hoặc KHÔNG NÊN khi chọn nghề.',
                [['Tìm hiểu nhiều nghề', 'Nên'], ['Hỏi ý kiến thầy cô, cha mẹ', 'Nên'],
                 ['Chọn nghề vì bạn bè chọn', 'Không nên'], ['Tự ti về bản thân', 'Không nên']],
                'Chủ động tìm hiểu, tự tin vào bản thân.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi năng khiếu vào nhóm NGHỆ THUẬT hoặc KHOA HỌC.',
                [['Vẽ đẹp', 'Nghệ thuật'], ['Hát hay', 'Nghệ thuật'],
                 ['Giỏi toán', 'Khoa học'], ['Giỏi vật lý', 'Khoa học']],
                'Năng khiếu gợi ý hướng nghề phù hợp.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Điều mình làm tốt hơn người khác gọi là điểm ___.', [[0, 'mạnh']],
                'Phát huy điểm mạnh để tự tin.', $d);
            $this->fill($L, $g, 'Chọn nghề cần phù hợp với sở thích và ___ lực của bản thân.', [[0, 'năng']],
                'Năng lực: khả năng thực sự của mình.', $d);
            $this->fill($L, $g, 'Không nên chọn nghề chạy theo ___ trào.', [[0, 'phong']],
                'Phong trào nhất thời, nghề nghiệp lâu dài.', $d);
            $this->fill($L, $g, 'Hiểu rõ bản thân giúp chọn ___ nghiệp đúng đắn.', [[0, 'nghề']],
                'Biết mình — bước đầu của định hướng nghề.', $d);
        }
    }

    private function seedTnhnNghe91(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'Sau khi tốt nghiệp THCS, học sinh có những hướng đi nào?',
                ['Thi vào THPT hoặc học trường nghề', 'Chỉ có thi vào THPT',
                 'Nghỉ học luôn', 'Chờ 2 năm rồi thi'], 0,
                'Nhiều hướng đi: THPT, trung cấp nghề, vừa học văn hóa vừa học nghề.', $d);
            $this->quiz($L, $g, 'Trường trung cấp nghề đào tạo điều gì cho học sinh?',
                ['Nghề cụ thể, tay nghề thực hành', 'Chỉ học văn hóa',
                 'Ngoại ngữ', 'Lý thuyết suông'], 0,
                'Trung cấp nghề: học nghề cụ thể như điện, may, nấu ăn, sửa xe.', $d);
            $this->quiz($L, $g, 'Muốn vào đại học thì trước hết phải làm gì?',
                ['Tốt nghiệp THPT và thi/xét tuyển', 'Học hết lớp 9 là đủ',
                 'Có tiền là được', 'Không cần học'], 0,
                'Đại học tuyển sinh từ người tốt nghiệp THPT.', $d);
            $this->quiz($L, $g, 'Chọn hướng đi sau lớp 9 nên căn cứ vào điều gì?',
                ['Năng lực, sở thích, điều kiện gia đình', 'Bạn thân chọn gì',
                 'Trường nào gần nhà nhất', 'Nghe theo tin đồn'], 0,
                'Căn cứ: năng lực, sở thích, điều kiện gia đình, nhu cầu xã hội.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi bậc học với đặc điểm.',
                [['THPT', 'Học văn hóa 3 năm sau lớp 9'],
                 ['Trung cấp nghề', 'Học nghề sau lớp 9'],
                 ['Cao đẳng', 'Học sau THPT, 3 năm'],
                 ['Đại học', 'Học sau THPT, 4-6 năm']],
                'Mỗi bậc học có thời gian và mục tiêu khác nhau.', $d);
            $this->matching($L, $g, 'Nối mỗi con đường với cách vào.',
                [['Vào THPT', 'Thi tuyển sinh lớp 10'],
                 ['Xét tuyển', 'Dùng học bạ, điểm số'],
                 ['Học song song', 'Vừa văn hóa vừa nghề'],
                 ['Du học nghề', 'Học nghề ở nước ngoài']],
                'Nhiều con đường, nhiều cách đi.', $d);
            $this->matching($L, $g, 'Nối mỗi căn cứ với việc nó giúp quyết định.',
                [['Năng lực học tập', 'Giúp chọn trường phù hợp'],
                 ['Điều kiện gia đình', 'Giúp chọn trường phù hợp'],
                 ['Nhu cầu xã hội', 'Giúp chọn nghề'],
                 ['Sở thích', 'Giúp chọn nghề']],
                'Chọn trường theo sức mình; chọn nghề theo đam mê và nhu cầu.', $d);
            $this->matching($L, $g, 'Nối mỗi nghề với bậc học thường cần.',
                [['Thợ điện', 'Trường nghề'], ['Thợ may', 'Trường nghề'],
                 ['Bác sĩ', 'Đại học'], ['Kế toán', 'Cao đẳng hoặc đại học']],
                'Mỗi nghề cần trình độ đào tạo khác nhau.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi việc vào nhóm SAU LỚP 9 hoặc SAU LỚP 12.',
                [['Thi vào lớp 10', 'Sau lớp 9'], ['Học trung cấp nghề', 'Sau lớp 9'],
                 ['Thi đại học', 'Sau lớp 12'], ['Tốt nghiệp THPT', 'Sau lớp 12']],
                'Lớp 9: rẽ hướng. Lớp 12: vào đại học/cao đẳng/nghề.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['Học nghề cũng là hướng đi tốt', 'Đúng'],
                 ['Chọn trường nên theo năng lực', 'Đúng'],
                 ['Sau lớp 9 chỉ có một con đường là vào THPT', 'Sai'],
                 ['Đại học là con đường duy nhất thành công', 'Sai']],
                'Nhiều con đường thành công: nghề, cao đẳng, đại học.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi bậc học vào nhóm ĐÀO TẠO NGẮN hoặc ĐÀO TẠO DÀI.',
                [['THPT (3 năm)', 'Đào tạo ngắn'], ['Trung cấp (2-3 năm)', 'Đào tạo ngắn'],
                 ['Cao đẳng (3 năm)', 'Đào tạo ngắn'], ['Đại học y (6 năm)', 'Đào tạo dài']],
                'Chọn bậc học phù hợp điều kiện và mục tiêu.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi căn cứ vào nhóm CĂN CỨ QUAN TRỌNG hoặc KHÔNG PHẢI.',
                [['Học lực của mình', 'Căn cứ quan trọng'],
                 ['Sở thích của mình', 'Căn cứ quan trọng'],
                 ['Bạn bè chọn gì', 'Không phải căn cứ'],
                 ['Nghề đang "hot"', 'Không phải căn cứ chính']],
                'Quyết định của mình phải dựa trên chính mình.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'Sau lớp 9, học sinh có thể thi vào ___ hoặc học trường nghề.', [[0, 'THPT']],
                'THPT hoặc trung cấp nghề — hai hướng chính.', $d);
            $this->fill($L, $g, 'Trường trung cấp đào tạo ___ cụ thể cho học sinh.', [[0, 'nghề']],
                'Học nghề: điện, may, nấu ăn, cơ khí...', $d);
            $this->fill($L, $g, 'Muốn học đại học, trước hết phải tốt nghiệp ___.', [[0, 'THPT']],
                'Tốt nghiệp THPT là điều kiện vào đại học.', $d);
            $this->fill($L, $g, 'Học ___ cũng là hướng đi tốt, không kém gì đại học.', [[0, 'nghề']],
                'Thợ giỏi tay nghề cao được xã hội trọng dụng.', $d);
        }
    }

    private function seedTnhnNghe92(string $L, int $g, string $d): void
    {
        if (! $this->seeded($L, 'quiz')) {
            $this->quiz($L, $g, 'AI là viết tắt của cụm từ nào?',
                ['Trí tuệ nhân tạo', 'An ninh mạng', 'Máy tính bảng', 'Internet'], 0,
                'AI (Artificial Intelligence): trí tuệ nhân tạo.', $d);
            $this->quiz($L, $g, 'Nhóm nghề nào khó bị AI và máy móc thay thế nhất?',
                ['Nghề cần sáng tạo, cảm xúc', 'Nghề nhập liệu đơn giản',
                 'Nghề lắp ráp lặp lại', 'Nghề tính toán đơn giản'], 0,
                'Sáng tạo, cảm xúc, chăm sóc con người — máy khó thay thế.', $d);
            $this->quiz($L, $g, 'Kỹ năng nào rất quan trọng trong tương lai?',
                ['Công nghệ số và ngoại ngữ', 'Chỉ cần sức khỏe',
                 'Chỉ cần khéo tay', 'Không cần kỹ năng'], 0,
                'Kỹ năng số + ngoại ngữ + sáng tạo là hành trang tương lai.', $d);
            $this->quiz($L, $g, '"Học suốt đời" có nghĩa là gì?',
                ['Học hỏi liên tục, không ngừng', 'Học đến hết lớp 12',
                 'Học một nghề duy nhất', 'Không cần học nữa'], 0,
                'Thế giới thay đổi nhanh — phải học suốt đời mới thích ứng được.', $d);
        }
        if (! $this->seeded($L, 'matching')) {
            $this->matching($L, $g, 'Nối mỗi nghề với loại của nó.',
                [['Kỹ sư AI', 'Nghề mới'], ['Phân tích dữ liệu', 'Nghề mới'],
                 ['Thợ may thủ công', 'Nghề truyền thống'], ['Nghề nông truyền thống', 'Nghề truyền thống']],
                'Công nghệ sinh ra nghề mới, nhưng nghề truyền thống vẫn có giá trị.', $d);
            $this->matching($L, $g, 'Nối mỗi kỹ năng với lợi ích.',
                [['Ngoại ngữ', 'Giao tiếp toàn cầu'],
                 ['Tin học', 'Kỹ năng số'],
                 ['Sáng tạo', 'Khó bị máy thay thế'],
                 ['Học suốt đời', 'Thích ứng với thay đổi']],
                'Bốn kỹ năng vàng cho tương lai.', $d);
            $this->matching($L, $g, 'Nối mỗi thuật ngữ công nghệ với ý nghĩa.',
                [['Robot', 'Tự động hóa'], ['AI', 'Trí tuệ nhân tạo'],
                 ['Dữ liệu lớn', 'Big data'], ['Internet vạn vật', 'IoT – vạn vật kết nối']],
                'Công nghệ 4.0 đang thay đổi mọi nghề nghiệp.', $d);
            $this->matching($L, $g, 'Nối mỗi loại công việc với nguy cơ bị thay thế.',
                [['Nghề lặp đi lặp lại', 'Dễ bị thay thế'],
                 ['Nhập liệu đơn giản', 'Dễ bị thay thế'],
                 ['Nghề sáng tạo', 'Khó bị thay thế'],
                 ['Nghề chăm sóc con người', 'Khó bị thay thế']],
                'Việc càng đơn giản lặp lại càng dễ bị máy làm thay.', $d);
        }
        if (! $this->seeded($L, 'sort')) {
            $this->sortQ($L, $g, 'Kéo mỗi nghề vào nhóm NGHỀ MỚI hoặc NGHỀ TRUYỀN THỐNG.',
                [['Kỹ sư AI', 'Nghề mới'], ['Chuyên gia dữ liệu', 'Nghề mới'],
                 ['Thợ rèn thủ công', 'Nghề truyền thống'], ['Nghề nông truyền thống', 'Nghề truyền thống']],
                'Nghề mới từ công nghệ; nghề truyền thống từ cha ông.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi phát biểu vào nhóm ĐÚNG hoặc SAI.',
                [['AI đang thay đổi nhiều nghề nghiệp', 'Đúng'],
                 ['Nghề sáng tạo khó bị thay thế', 'Đúng'],
                 ['Kỹ năng số rất quan trọng', 'Đúng'],
                 ['Học xong phổ thông thì không cần học nữa', 'Sai']],
                'Phải học suốt đời mới thích ứng được.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi công việc vào nhóm DỄ hoặc KHÓ bị máy móc thay thế.',
                [['Nhập liệu đơn giản', 'Dễ bị thay thế'],
                 ['Lắp ráp lặp lại', 'Dễ bị thay thế'],
                 ['Bác sĩ phẫu thuật', 'Khó bị thay thế'],
                 ['Nghệ sĩ sáng tác', 'Khó bị thay thế']],
                'Việc đơn giản lặp lại dễ bị thay thế nhất.', $d);
            $this->sortQ($L, $g, 'Kéo mỗi kỹ năng vào nhóm KỸ NĂNG TƯƠNG LAI hoặc CHƯA ĐỦ.',
                [['Ngoại ngữ', 'Kỹ năng tương lai'], ['Tin học', 'Kỹ năng tương lai'],
                 ['Sáng tạo', 'Kỹ năng tương lai'], ['Chỉ biết một việc đơn giản', 'Chưa đủ']],
                'Tương lai cần người đa kỹ năng.', $d);
        }
        if (! $this->seeded($L, 'fill')) {
            $this->fill($L, $g, 'AI là viết tắt của trí tuệ ___ tạo.', [[0, 'nhân']],
                'Artificial Intelligence — trí tuệ nhân tạo.', $d);
            $this->fill($L, $g, 'Nghề ___ đi ___ lại đơn giản dễ bị máy móc thay thế.', [[0, 'lặp'], [1, 'lặp']],
                'Việc lặp lại đơn giản là "món ngon" của robot.', $d);
            $this->fill($L, $g, 'Ngoại ___ và tin học là hành trang không thể thiếu cho tương lai.', [[0, 'ngữ']],
                'Ngoại ngữ mở ra thế giới; tin học mở ra công nghệ.', $d);
            $this->fill($L, $g, 'Tinh thần học ___ đời giúp con người thích ứng với mọi thay đổi.', [[0, 'suốt']],
                'Học suốt đời — chìa khóa của tương lai.', $d);
        }
    }
}
