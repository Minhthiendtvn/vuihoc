<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tóm tắt bài học nhóm môn B: Khoa học, Lịch sử, Ngữ văn.
 *
 * - Dữ liệu: [lesson_id => "summary"], mỗi summary gồm 5-8 ý,
 *   mỗi ý một dòng bắt đầu bằng "- ".
 * - Chỉ ghi khi summary đang NULL hoặc rỗng (idempotent,
 *   chạy lại không ghi đè nội dung đã có).
 */
class LessonSummaryGroupBSeeder extends Seeder
{
    public function run(): void
    {
        $summaries = [
            // ================= KHOA HỌC =================
            // L6 - Hệ xương của người
            21 => "- Hệ xương nâng đỡ cơ thể, tạo khung và bảo vệ các cơ quan bên trong như não, tim, phổi.
- Xương sọ tạo thành hộp sọ, bao bọc và bảo vệ não.
- Xương sườn bảo vệ tim và phổi.
- Cột sống nâng đỡ toàn bộ cơ thể.
- Xương đùi là xương dài và chắc nhất trong cơ thể người.",
            // L6 - Hệ tiêu hoá
            22 => "- Đường đi của thức ăn: miệng → thực quản → dạ dày → ruột non → ruột già.
- Thực quản là ống nối miệng với dạ dày, đưa thức ăn xuống dạ dày.
- Miệng là nơi đầu tiên tiếp nhận, nhai và nghiền nhỏ thức ăn.
- Tim thuộc hệ tuần hoàn, phổi thuộc hệ hô hấp — đó không phải là cơ quan tiêu hoá.
- Các cơ quan tiêu hoá phối hợp nhịp nhàng để biến thức ăn thành chất dinh dưỡng nuôi cơ thể.",
            // L6 - Cơ thể người lớp 6: hệ xương (1)
            167 => "- Cơ thể người trưởng thành có hơn 200 chiếc xương tạo thành bộ xương.
- Xương nâng đỡ cơ thể, tạo khung cho cơ bám và bảo vệ các cơ quan bên trong như não, tim, phổi.
- Khớp là chỗ nối giữa các xương, giúp xương cử động linh hoạt.
- Canxi là thành phần chính cấu tạo nên xương.
- Sữa, tôm cua nhỏ và tắm nắng giúp xương chắc khoẻ.",
            // L6 - Cơ thể người lớp 6: hệ tiêu hoá (2)
            168 => "- Thức ăn từ miệng qua thực quản rồi mới xuống dạ dày.
- Dạ dày co bóp nhào trộn thức ăn và tiết dịch vị để tiêu hoá.
- Ruột non là nơi hấp thụ chất dinh dưỡng vào máu nuôi cơ thể.
- Sau ruột non, thức ăn đi tới ruột già, nơi hấp thụ nước và tạo thành phân.
- Ăn chậm nhai kĩ giúp thức ăn được nghiền nhỏ, dễ tiêu hoá.",
            // L7 - Các trạng thái của chất
            23 => "- Chất tồn tại ở ba trạng thái cơ bản: rắn, lỏng và khí.
- Nước đá có hình dạng cố định nên ở trạng thái rắn.
- Chất lỏng có hình dạng theo vật chứa nó.
- Chất khí không có hình dạng cố định, luôn lan toả chiếm đầy bình chứa.
- Khi đun sôi, nước chuyển từ thể lỏng thành hơi nước ở thể khí.",
            // L7 - Nước quanh ta
            24 => "- Nước tinh khiết không màu, không mùi, không vị.
- Trong điều kiện thường, nước sôi ở 100°C và đóng băng ở 0°C.
- Con người chỉ sống được vài ngày nếu thiếu nước.
- Nước cần cho mọi hoạt động sống và sinh hoạt hằng ngày.
- Nguồn nước sạch có hạn nên cần tiết kiệm mỗi ngày, ví dụ khoá vòi khi không dùng.",
            // L7 - Cơ thể người lớp 7: hệ tuần hoàn và hô hấp (1)
            169 => "- Tim co bóp liên tục để bơm máu đi nuôi khắp cơ thể.
- Máu vận chuyển ô-xy và chất dinh dưỡng đến tế bào, đồng thời mang chất thải đi.
- Đường đi của không khí khi hít vào: mũi → khí quản → phế quản → phổi.
- Mũi có tác dụng lọc bụi trong không khí trước khi vào phổi.
- Phổi là nơi trao đổi khí: lấy ô-xy vào máu và thải khí các-bô-níc ra ngoài.",
            // L7 - Cơ thể người lớp 7: hệ bài tiết và vệ sinh cơ thể (2)
            170 => "- Thận lọc máu, loại bỏ chất thải tạo thành nước tiểu.
- Ngoài thận, da bài tiết mồ hôi, phổi thải khí các-bô-níc và hơi nước.
- Toát mồ hôi giúp thải một phần chất thải và làm mát cơ thể khi nóng.
- Uống đủ nước giúp thận lọc tốt.
- Ăn mặn và nhịn tiểu làm hại thận; cần giữ vệ sinh cơ thể hằng ngày.",
            // L7 - Chất quanh ta lớp 7: các trạng thái của chất (1)
            171 => "- Chất tồn tại ở ba trạng thái cơ bản: rắn, lỏng và khí.
- Đun nóng, nước đá (rắn) nóng chảy thành nước lỏng — đó là sự nóng chảy.
- Nước bay hơi thành hơi nước khi được đun nóng; ngay ở nhiệt độ thường, nước để lâu cũng bay hơi dần.
- Hơi nước trong không khí gặp lạnh ngưng tụ thành giọt — như sương đọng trên lá vào buổi sáng.
- Sự chuyển thể của chất diễn ra khi nhiệt độ thay đổi: nóng chảy, bay hơi, ngưng tụ.",
            // L7 - Chất quanh ta lớp 7: nước trong đời sống (2)
            172 => "- Nước chiếm khoảng 70% khối lượng cơ thể người.
- Vòng tuần hoàn của nước trong tự nhiên: bay hơi → ngưng tụ thành mây → mưa rơi xuống → chảy ra sông biển → lại bay hơi.
- Nước cần cho sinh hoạt và sản xuất; nguồn nước sạch có hạn.
- Khoá vòi khi không dùng là cách tiết kiệm nước đơn giản nhất.
- Nước ô nhiễm gây bệnh đường ruột, bệnh ngoài da và giết chết sinh vật dưới nước.",
            // L8 - Nguồn năng lượng
            25 => "- Mặt trời, gió, nước là nguồn năng lượng tái tạo vì không bao giờ cạn kiệt.
- Than đá, dầu mỏ, khí đốt hình thành qua hàng triệu năm nên dùng hết là cạn kiệt — đó là năng lượng không tái tạo.
- Tấm pin mặt trời biến ánh sáng mặt trời thành điện năng.
- Ưu tiên dùng năng lượng tái tạo giúp tiết kiệm tài nguyên và giảm ô nhiễm môi trường.
- Năng lượng mặt trời, gió và nước sạch, không gây ô nhiễm nên được khuyến khích sử dụng.",
            // L8 - Điện và mạch điện
            26 => "- Kim loại như đồng, sắt dẫn điện; nhựa và gỗ khô cách điện.
- Mạch điện đơn giản gồm: nguồn điện, dây dẫn, bóng đèn và công tắc.
- Công tắc dùng để đóng, ngắt dòng điện trong mạch.
- Nhờ vật cách điện bọc ngoài, dây điện và dụng cụ điện an toàn khi cầm nắm.
- Tuyệt đối không chạm vào ổ điện; dùng điện phải luôn cẩn thận vì rất nguy hiểm.",
            // L8 - Chất quanh ta lớp 8: hỗn hợp và dung dịch (1)
            173 => "- Hỗn hợp gồm hai hay nhiều chất trộn lẫn, mỗi chất vẫn giữ tính chất riêng.
- Dung dịch là hỗn hợp đồng nhất: các thành phần tan đều, không phân biệt được — như nước đường.
- Dung môi là chất dùng để hoà tan chất khác; trong nước muối, nước là dung môi, muối là chất tan.
- Cát không tan trong nước nên có thể tách cát ra khỏi nước bằng cách lọc qua giấy lọc.
- Muốn tách một chất ra khỏi hỗn hợp, cần chọn phương pháp phù hợp với tính chất của các chất.",
            // L8 - Chất quanh ta lớp 8: nguyên tử và phân tử (2)
            174 => "- Nguyên tử gồm hạt nhân ở giữa (proton, nơtron) và các electron chuyển động xung quanh.
- Proton mang điện dương, electron mang điện âm, nơtron không mang điện.
- Mỗi phân tử nước (H2O) gồm 2 nguyên tử hiđrô liên kết với 1 nguyên tử ôxy.
- Kim cương và than chì đều do nguyên tử cacbon tạo nên, chỉ khác cách sắp xếp các nguyên tử.
- Mọi chất đều được cấu tạo từ các nguyên tử và phân tử vô cùng nhỏ bé.",
            // L8 - Năng lượng lớp 8: các dạng năng lượng (1)
            175 => "- Vật chuyển động có động năng; vật càng nặng, chạy càng nhanh thì động năng càng lớn.
- Vật ở trên cao có thế năng hấp dẫn; càng cao thì thế năng càng lớn.
- Khi quả bóng rơi từ trên cao xuống, độ cao giảm (thế năng giảm) và tốc độ tăng (động năng tăng).
- Năng lượng không tự nhiên sinh ra hay mất đi, chỉ chuyển từ dạng này sang dạng khác — đó là định luật bảo toàn năng lượng.
- Trong đời sống, các thiết bị biến đổi năng lượng từ dạng này sang dạng khác để phục vụ con người.",
            // L8 - Năng lượng lớp 8: công và công suất (2)
            176 => "- Công cơ học A bằng lực F nhân với quãng đường s mà vật đi được theo hướng của lực: A = F × s.
- Đơn vị công là jun (J); 1 J là công của lực 1 N làm vật đi được 1 m.
- Ví dụ: kéo vật với lực 100 N đi được 5 m thì công thực hiện là 500 J.
- Công suất P = A / t cho biết trong một giây thực hiện được bao nhiêu công.
- Máy có công suất lớn làm được nhiều công trong cùng một thời gian.",
            // L9 - Năng lượng lớp 9: dòng điện và mạch điện (1)
            177 => "- Dòng điện là dòng chuyển dời có hướng của các hạt mang điện (electron).
- Muốn có dòng điện, mạch điện phải kín và phải có nguồn điện như pin, ắc quy.
- Cường độ dòng điện được đo bằng ampe kế, đơn vị là ampe (A).
- Hiệu điện thế được đo bằng vôn kế, đơn vị là vôn (V).
- Cường độ dòng điện càng lớn thì tác dụng của dòng điện càng mạnh, nhưng phải dùng đúng mức an toàn.",
            // L9 - Năng lượng lớp 9: điện năng và an toàn điện (2)
            178 => "- Công suất điện P bằng hiệu điện thế U nhân với cường độ dòng điện I.
- Trên bóng đèn ghi “220 V – 100 W”: 220 V là hiệu điện thế định mức, 100 W là công suất định mức khi dùng đúng 220 V.
- “Số điện” công tơ đo chính là kilôoát giờ (kWh): 1 số điện là điện năng của thiết bị 1000 W dùng trong 1 giờ.
- Tay ướt dẫn điện tốt nên tuyệt đối không chạm vào ổ điện, công tắc khi tay ướt.
- Sử dụng điện an toàn: không dùng dây điện hở, ngắt nguồn khi sửa chữa thiết bị điện.",
            // L10 Vật lý - Chuyển động thẳng đều: vận tốc và quãng đường (1)
            311 => "- Vận tốc của chuyển động thẳng đều bằng quãng đường chia cho thời gian: v = s / t.
- Quãng đường đi được: s = v × t; ví dụ xe chạy 54 km/h trong 2 giờ đi được 108 km.
- Ví dụ: đi bộ 6 km hết 1,5 giờ thì vận tốc trung bình là 4 km/h.
- Trong chuyển động thẳng đều, vận tốc không đổi theo thời gian.
- Đồ thị quãng đường – thời gian của chuyển động thẳng đều là đường thẳng xiên qua gốc tọa độ.",
            // L10 Vật lý - Chuyển động thẳng biến đổi đều: gia tốc (2)
            312 => "- Gia tốc bằng độ biến thiên vận tốc chia cho thời gian biến thiên: a = Δv / Δt.
- Ví dụ: ô tô tăng tốc từ 10 m/s lên 20 m/s trong 5 s thì gia tốc là 2 m/s².
- Chuyển động thẳng nhanh dần đều: gia tốc cùng chiều với vận tốc làm tốc độ tăng dần.
- Chuyển động thẳng chậm dần đều: gia tốc ngược chiều với vận tốc làm tốc độ giảm dần.
- Với gia tốc không đổi, quãng đường được tính bằng công thức của chuyển động thẳng biến đổi đều.",
            // L10 Vật lý - Ba định luật Newton (3)
            313 => "- Định luật I Newton (định luật quán tính): vật giữ nguyên trạng thái đứng yên hoặc chuyển động thẳng đều khi không chịu lực hoặc hợp lực bằng 0.
- Định luật II Newton: gia tốc của vật tỉ lệ thuận với lực tác dụng và tỉ lệ nghịch với khối lượng: a = F / m.
- Ví dụ: vật khối lượng 2 kg chịu lực 10 N thì gia tốc của vật là 5 m/s².
- Định luật III Newton: lực và phản lực đặt lên hai vật khác nhau nên không triệt tiêu nhau.
- Ba định luật Newton là cơ sở của cơ học cổ điển, giải thích chuyển động của các vật.",
            // L10 Vật lý - Trọng lực, lực ma sát và lực đàn hồi (4)
            314 => "- Trọng lượng P bằng khối lượng m nhân với gia tốc rơi tự do g: P = m × g.
- Ví dụ: vật khối lượng 5 kg (lấy g = 10 m/s²) có trọng lượng là 50 N.
- Lực ma sát nghỉ xuất hiện khi có lực có xu hướng làm vật trượt; nó cân bằng với lực đó.
- Lực ma sát có lợi khi giúp ta đi lại, cầm nắm; có hại khi làm mòn chi tiết máy.
- Lực đàn hồi của lò xo chống lại biến dạng: F = k × Δl.",
            // L11 Hóa học - Hạt cơ bản cấu tạo nên nguyên tử (1)
            315 => "- Nguyên tử gồm hạt nhân (proton, nơtron) và các electron chuyển động xung quanh.
- Proton nằm trong hạt nhân, mỗi proton mang điện tích +1.
- Số hiệu nguyên tử Z đặc trưng cho nguyên tố hoá học và bằng số proton trong hạt nhân.
- Nguyên tử trung hoà về điện vì điện tích dương của proton cân bằng điện tích âm của electron.
- Electron có khối lượng rất nhỏ, chỉ bằng khoảng 1/1836 khối lượng proton.",
            // L11 Hóa học - Số khối, đồng vị và cấu hình electron (2)
            316 => "- Số khối A của hạt nhân bằng tổng số proton (Z) và số nơtron (N): A = Z + N.
- Ví dụ: nguyên tử X có Z = 8, N = 8 thì A = 16, đó là đồng vị oxi-16.
- Hai nguyên tử là đồng vị của nhau khi cùng Z (cùng nguyên tố) nhưng khác số nơtron.
- Số electron tối đa của lớp thứ n là 2n²; lớp L (n = 2) chứa tối đa 8 electron.
- Các electron được sắp xếp thành từng lớp, từng phân lớp theo nguyên tắc nhất định.",
            // L11 Hóa học - Nguyên tắc sắp xếp và cấu tạo bảng tuần hoàn (3)
            317 => "- Các nguyên tố trong bảng tuần hoàn được sắp xếp theo số hiệu nguyên tử Z tăng dần.
- Bảng tuần hoàn hiện nay có 7 chu kì, tương ứng với 7 lớp electron.
- Số thứ tự chu kì bằng số lớp electron của nguyên tử nguyên tố đó.
- Nguyên tố ở chu kì 3 có 3 lớp electron.
- Nhóm VIIIA gồm các khí hiếm: lớp ngoài cùng bền vững (8 electron) nên rất trơ về mặt hoá học.",
            // L11 Hóa học - Xu hướng biến đổi tính chất các nguyên tố (4)
            318 => "- Trong một chu kì, đi từ trái sang phải, điện tích hạt nhân tăng nên hút electron mạnh hơn, bán kính nguyên tử giảm.
- Trong một nhóm A, đi từ trên xuống dưới, bán kính tăng, electron lớp ngoài dễ mất đi hơn nên tính kim loại tăng.
- Flo là nguyên tố có độ âm điện lớn nhất trong bảng tuần hoàn.
- Trong chu kì 3, nguyên tố đầu chu kì có tính kim loại mạnh nhất; tính kim loại giảm dần sang phải.
- Hiểu xu hướng biến đổi giúp dự đoán tính chất của nguyên tố chưa nghiên cứu kĩ.",
            // L12 Sinh học - Cấu trúc phân tử ADN (1)
            319 => "- ADN được cấu tạo từ các đơn phân là nucleotit; mỗi nucleotit gồm đường deoxyribose, axit photphoric và bazơ nitơ.
- Năm 1953, Watson và Crick công bố mô hình xoắn kép của phân tử ADN.
- Theo nguyên tắc bổ sung: A liên kết với T bằng 2 liên kết hiđro, G liên kết với X.
- Đoạn mạch có trình tự A–T–G–X thì mạch bổ sung là T–A–X–G.
- Cấu trúc xoắn kép và nguyên tắc bổ sung là cơ sở của sự nhân đôi và truyền đạt thông tin di truyền.",
            // L12 Sinh học - Gen và mã di truyền (2)
            320 => "- Gen là một đoạn của phân tử ADN mang thông tin mã hoá cho một sản phẩm nhất định (chuỗi pôlipeptit hay ARN).
- Mã di truyền được đọc theo từng bộ ba nucleotit (codon), mỗi codon mã hoá cho một axit amin.
- AUG vừa là bộ ba mở đầu dịch mã vừa mã hoá axit amin Metionin.
- Mã di truyền có tính thoái hoá: nhiều codon khác nhau có thể cùng mã hoá cho một axit amin (ví dụ Lơxin).
- Tính thoái hoá giúp giảm tác hại khi ADN bị đột biến.",
            // L12 Sinh học - Lai một cặp tính trạng và quy luật phân li (3)
            321 => "- Tính trạng trội là tính trạng được biểu hiện, lấn át sự biểu hiện của alen lặn ở cơ thể dị hợp.
- Cơ thể có kiểu gen Aa mang hai alen khác nhau của cùng một gen nên gọi là thể dị hợp.
- Phép lai Aa × Aa cho tỉ lệ kiểu hình ở F2 là 3 trội : 1 lặn — tỉ lệ đặc trưng của lai một cặp tính trạng.
- Quy luật phân li của Mendel: mỗi giao tử chỉ chứa một alen của cặp alen.
- Thí nghiệm của Mendel trên đậu Hà Lan đặt nền móng cho di truyền học.",
            // L12 Sinh học - Lai hai cặp tính trạng và phân li độc lập (4)
            322 => "- Phép lai AaBb × AaBb cho tỉ lệ kiểu hình ở F2 là 9 : 3 : 3 : 1, đặc trưng của phân li độc lập.
- Tỉ lệ 9:3:3:1 chính là tích của hai tỉ lệ 3:1, vì mỗi cặp tính trạng phân li 3:1.
- Cơ thể có kiểu gen AaBb giảm phân tạo 4 loại giao tử (2², với n là số cặp gen dị hợp).
- Điều kiện để các gen phân li độc lập: các cặp NST tương đồng phân li độc lập trong giảm phân.
- Quy luật phân li độc lập giải thích sự đa dạng di truyền của sinh vật.",
            // ================= LỊCH SỬ =================
            // L6 - Nước Văn Lang
            27 => "- Nước Văn Lang là nhà nước đầu tiên của người Việt, do các vua Hùng dựng nên.
- Người đứng đầu nước Văn Lang gọi là Vua Hùng (Hùng Vương).
- Kinh đô Văn Lang đặt ở Phong Châu, thuộc tỉnh Phú Thọ ngày nay.
- Vua Hùng đặt các Lạc tướng giúp việc cai trị các bộ.
- Ngày Giỗ Tổ Hùng Vương (10/3 âm lịch) tưởng nhớ công lao dựng nước của các vua Hùng.",
            // L6 - Các anh hùng dân tộc
            28 => "- Hai Bà Trưng phất cờ khởi nghĩa năm 40 sau Công nguyên.
- Bà Triệu khởi nghĩa năm 248; Lý Bí khởi nghĩa năm 542.
- Lý Bí xưng đế năm 544, lập nước Vạn Xuân.
- Ngô Quyền đại phá quân Nam Hán trên sông Bạch Đằng năm 938.
- Hát Môn là nơi Hai Bà Trưng tuẫn tiết; Bạch Đằng gắn với chiến công của Ngô Quyền.",
            // L6 - Lịch sử lớp 6: nước Văn Lang (1)
            179 => "- Khoảng thế kỉ VII TCN, các bộ lạc hợp nhất thành nước Văn Lang.
- Hùng Vương là người đứng đầu nước Văn Lang, được nhân dân tôn thờ.
- Kinh đô Văn Lang đặt ở Phong Châu, thuộc tỉnh Phú Thọ ngày nay.
- Cả nước chia thành 15 bộ, đứng đầu mỗi bộ là Lạc tướng.
- Đời sống người Văn Lang gắn với nghề trồng lúa nước và trống đồng Đông Sơn.",
            // L6 - Lịch sử lớp 6: An Dương Vương và nước Âu Lạc (2)
            180 => "- Năm 208 TCN, Thục Phán (An Dương Vương) lập nước Âu Lạc, thay thế nước Văn Lang.
- An Dương Vương đóng đô ở Cổ Loa, nay thuộc Hà Nội.
- Thành Cổ Loa xây theo hình xoáy ốc, gồm nhiều vòng thành vững chắc.
- Truyền thuyết kể An Dương Vương được thần Kim Quy cho móng rùa làm lẫy nỏ thần.
- Nước Âu Lạc có quân đội mạnh, song sau này bị Triệu Đà thôn tính.",
            // L7 - Đinh Bộ Lĩnh dẹp loạn 12 sứ quân
            29 => "- Sau khi Ngô Quyền mất, các thế lực cát cứ nổi lên, sử gọi là loạn 12 sứ quân.
- Đinh Bộ Lĩnh dẹp loạn 12 sứ quân, thống nhất đất nước năm 968.
- Năm 968, Đinh Bộ Lĩnh lên ngôi, đặt tên nước là Đại Cồ Việt.
- Nhà Đinh đóng đô ở Hoa Lư (Ninh Bình ngày nay) — vùng núi hiểm trở, dễ phòng thủ.
- Việc đặt quốc hiệu Đại Cồ Việt khẳng định nước ta sánh ngang với phương Bắc.",
            // L7 - Lê Hoàn và nhà Tiền Lê
            30 => "- Lê Đại Hành chính là hiệu của vua Lê Hoàn nhà Tiền Lê.
- Nhà Đinh chỉ tồn tại 12 năm (968–980); nhà Tiền Lê tiếp tục đóng đô ở Hoa Lư.
- Phạm Cự Lượng và Đinh Điền là các tướng tài thời Đinh – Tiền Lê.
- Năm 981, Lê Hoàn chỉ huy đánh tan quân Tống xâm lược, bảo vệ vững chắc nền độc lập non trẻ.
- Thắng lợi năm 981 cho thấy ý chí quyết tâm bảo vệ độc lập của dân tộc.",
            // L7 - Lịch sử lớp 7: khởi nghĩa Hai Bà Trưng (1)
            181 => "- Mùa xuân năm 40, Hai Bà Trưng phất cờ khởi nghĩa ở Mê Linh (nay thuộc Hà Nội).
- Nguyên nhân: nhà Hán cai trị tàn bạo, bắt dân nộp thuế nặng.
- Nghĩa quân nhanh chóng làm chủ Mê Linh, Cổ Loa, Luy Lâu.
- Sau thắng lợi, Trưng Trắc lên ngôi vua (Trưng Vương), đóng đô ở Mê Linh.
- Đây là cuộc khởi nghĩa đầu tiên của nhân dân ta chống ách đô hộ phương Bắc.",
            // L7 - Lịch sử lớp 7: Ngô Quyền và chiến thắng Bạch Đằng (2)
            182 => "- Năm 938, Ngô Quyền chỉ huy quân ta đánh tan quân Nam Hán trên sông Bạch Đằng.
- Kế sách: đóng cọc gỗ xuống lòng sông, nhử giặc vào trận địa khi nước lên rồi đánh úp khi nước rút.
- Quân Nam Hán do Lưu Hoằng Tháo chỉ huy bị tiêu diệt hoàn toàn.
- Chiến thắng Bạch Đằng chấm dứt thời kì Bắc thuộc hơn 1000 năm.
- Thắng lợi mở ra kỉ nguyên độc lập, tự chủ lâu dài của dân tộc.",
            // L7 - Lịch sử lớp 7: Đinh Bộ Lĩnh dẹp loạn 12 sứ quân (1)
            183 => "- Sau khi Ngô Quyền mất, đất nước rơi vào tình trạng cát cứ của các sứ quân.
- Đinh Bộ Lĩnh dẹp loạn 12 sứ quân, thống nhất đất nước năm 968.
- Đinh Bộ Lĩnh lên ngôi hoàng đế (Đinh Tiên Hoàng), đặt tên nước là Đại Cồ Việt.
- Kinh đô đặt ở Hoa Lư (Ninh Bình) — vùng núi hiểm trở, thuận lợi phòng thủ.
- Nhà Đinh là triều đại phong kiến đầu tiên, mở đầu thời kì độc lập tự chủ.",
            // L7 - Lịch sử lớp 7: Lê Hoàn và kháng chiến chống Tống (2)
            184 => "- Trước khi lên ngôi, Lê Hoàn là Thập đạo tướng quân, chỉ huy quân đội nhà Đinh.
- Năm 980, trước hoạ ngoại xâm, Lê Hoàn được tôn lên làm vua, lập nhà Tiền Lê.
- Năm 981, Lê Hoàn chỉ huy đánh tan quân Tống ở Bạch Đằng và Chi Lăng.
- Nhà Tiền Lê tiếp tục đóng đô ở Hoa Lư như nhà Đinh.
- Thắng lợi năm 981 bảo vệ vững chắc nền độc lập còn non trẻ của đất nước.",
            // L8 - Ba lần kháng chiến chống Nguyên – Mông
            31 => "- Quân dân Đại Việt đã ba lần đánh bại quân Nguyên – Mông: 1258, 1285 và 1287–1288.
- Cuộc kháng chiến lần thứ hai diễn ra năm 1285.
- Trần Hưng Đạo (Trần Quốc Tuấn) là tổng chỉ huy cuộc kháng chiến lần 2 và lần 3.
- Quân dân nhà Trần đoàn kết một lòng, từ vua quan đến nhân dân cùng đánh giặc.
- Ba lần thắng lợi giữ vững nền độc lập của Đại Việt trước đế quốc hùng mạnh nhất thời bấy giờ.",
            // L8 - Trần Hưng Đạo
            32 => "- Trần Hưng Đạo tên thật là Trần Quốc Tuấn, được phong Hưng Đạo Đại Vương.
- Ông là vị tướng tổng chỉ huy kháng chiến, không phải vua, và là bậc anh hùng dân tộc.
- Chương Dương, Tây Kết là những trận đánh tiêu biểu của cuộc kháng chiến lần 2; Vân Đồn, Bạch Đằng thuộc lần 3.
- Ông để lại các tác phẩm quân sự và bài “Hịch tướng sĩ” khích lệ tướng sĩ.
- Cần phân biệt: “Nam quốc sơn hà” gắn với Lý Thường Kiệt; “Đại Việt sử ký toàn thư” do Ngô Sĩ Liên biên soạn.",
            // L8 - Lịch sử lớp 8: tổ chức nhà nước Đinh – Tiền Lê (1)
            185 => "- Vua là người đứng đầu nhà nước, nắm mọi quyền hành.
- Giúp việc cho vua có các chức quan: thái sư, đại tổng quản, chia thành quan văn và quan võ.
- Quân đội gồm cấm quân bảo vệ kinh thành và quân đóng ở các lộ, phủ.
- Nhà Đinh ban hành hình luật — bộ luật thành văn đầu tiên để trừng trị kẻ có tội, giữ kỉ cương đất nước.
- Bộ máy nhà nước tuy còn đơn giản nhưng đã mang tính chất nhà nước phong kiến tập quyền.",
            // L8 - Lịch sử lớp 8: kinh tế và văn hoá thời Đinh – Tiền Lê (2)
            186 => "- Nông nghiệp trồng lúa nước là ngành kinh tế chính; nhà nước khuyến khích khai hoang mở rộng ruộng đồng.
- Nhà Đinh cho đúc tiền đồng Thái Bình — đồng tiền đầu tiên của Việt Nam.
- Thủ công nghiệp phát triển: đúc đồng, rèn sắt, dệt vải, làm gốm.
- Phật giáo rất thịnh hành; nhiều chùa tháp được xây dựng, có chức Tăng thống đứng đầu tăng giới.
- Kinh tế, văn hoá bước đầu phát triển tạo nền tảng cho các triều đại sau.",
            // L8 - Lịch sử lớp 8: ba lần kháng chiến chống quân Nguyên – Mông (1)
            187 => "- Quân Mông – Nguyên xâm lược Đại Việt 3 lần: 1258, 1285 và 1287–1288.
- Năm 1258, Trần Thủ Độ chỉ huy đánh tan quân Mông Cổ ở Đông Bộ Đầu.
- Kế sách “vườn không nhà trống”: quân dân ta rút lui, mang theo lương thực khiến giặc thiếu ăn, ốm đau.
- Hội nghị Diên Hồng: các bô lão cả nước đồng thanh hô “đánh”, thể hiện ý chí quyết chiến.
- Thắng lợi của ba lần kháng chiến bắt nguồn từ sức mạnh đoàn kết toàn dân.",
            // L8 - Lịch sử lớp 8: trận Bạch Đằng năm 1288 (2)
            188 => "- Trận Bạch Đằng năm 1288 do Trần Hưng Đạo (Trần Quốc Tuấn) tổng chỉ huy.
- Trần Hưng Đạo học kế đóng cọc gỗ dưới lòng sông của Ngô Quyền năm 938.
- Tướng giặc Ô Mã Nhi bị bắt sống; Thoát Hoan phải chui vào ống đồng để chạy trốn.
- Trận Bạch Đằng tiêu diệt đạo thuỷ binh giặc, kết thúc cuộc kháng chiến lần 3.
- Đây là một trong những chiến thắng thuỷ chiến vĩ đại nhất lịch sử dân tộc.",
            // L9 - Lịch sử lớp 9: Trần Hưng Đạo và Hịch tướng sĩ (1)
            189 => "- Trần Hưng Đạo tên thật là Trần Quốc Tuấn, được phong Hưng Đạo Vương.
- Khoảng năm 1284, trước khi giặc sang lần 2, ông viết “Hịch tướng sĩ”.
- Bài hịch khích lệ lòng yêu nước, kêu gọi tướng sĩ đồng lòng đánh giặc.
- Ngoài “Hịch tướng sĩ”, ông còn soạn “Binh thư yếu lược” và “Vạn Kiếp tông bí truyền thư” về nghệ thuật quân sự.
- Trần Hưng Đạo được nhân dân tôn thờ là Đức Thánh Trần.",
            // L9 - Lịch sử lớp 9: ý nghĩa lịch sử của kháng chiến chống Nguyên – Mông (2)
            190 => "- Thắng lợi đập tan ý đồ xâm lược của đế quốc Nguyên – Mông, giữ vững độc lập dân tộc.
- Bài học lớn nhất: sức mạnh đoàn kết toàn dân, từ vua quan đến các bô lão và nhân dân.
- Nghệ thuật quân sự nhà Trần: “lấy yếu chống mạnh, lấy ít địch nhiều”.
- Đại Việt là một trong số ít nước đánh bại Mông – Nguyên, góp phần bảo vệ cả khu vực.
- Thắng lợi hun đúc lòng tự hào và ý chí quật cường của dân tộc.",
            // L10 - Buổi đầu độc lập: Ngô, Đinh, Tiền Lê (1)
            323 => "- Chiến thắng Bạch Đằng năm 938 gắn với tên tuổi Ngô Quyền: dùng cọc gỗ đánh tan quân Nam Hán, mở ra thời kì độc lập.
- Năm 968, Đinh Bộ Lĩnh lên ngôi hoàng đế, đặt quốc hiệu Đại Cồ Việt — quốc hiệu chính thức đầu tiên của nhà nước phong kiến Việt Nam.
- Kinh đô nước Đại Cồ Việt thời Đinh – Tiền Lê đặt tại Hoa Lư (nay thuộc Ninh Bình).
- Năm 981, Lê Hoàn (Lê Đại Hành) lãnh đạo kháng chiến đánh tan quân Tống, bảo vệ nền độc lập non trẻ.
- Giai đoạn Ngô – Đinh – Tiền Lê đặt nền móng cho nhà nước phong kiến độc lập, tự chủ.",
            // L10 - Các triều đại Lý, Trần, Hồ, Lê và những chiến công (2)
            324 => "- Năm 1009, Lý Công Uẩn lên ngôi, lập ra nhà Lý, mở ra thời kì phát triển rực rỡ.
- Nhà Trần lãnh đạo nhân dân ba lần đánh thắng Mông – Nguyên (1258, 1285, 1287–1288).
- Lê Lợi cùng Nguyễn Trãi lãnh đạo khởi nghĩa Lam Sơn (1418–1427), giành lại độc lập từ tay nhà Minh.
- Năm 1789, Quang Trung hành quân thần tốc, đại phá 29 vạn quân Thanh ở Ngọc Hồi – Đống Đa.
- Mỗi triều đại đều ghi dấu những chiến công hiển hách bảo vệ Tổ quốc.",
            // L10 - Giáo dục và khoa cử thời phong kiến (3)
            325 => "- Năm 1076, vua Lý Nhân Tông cho mở Quốc Tử Giám — trường đại học đầu tiên của Việt Nam; Văn Miếu được dựng năm 1070.
- Khoa thi Nho học đầu tiên: khoa thi Minh kinh bác học năm 1075 thời vua Lý Nhân Tông.
- Chu Văn An là nhà giáo lỗi lạc thời Trần, được tôn vinh là “vạn thế sư biểu”.
- Nền khoa cử Nho học kéo dài hơn 800 năm, khép lại với khoa thi Hội cuối cùng năm 1919.
- Giáo dục, khoa cử tuyển chọn nhân tài, góp phần xây dựng đất nước.",
            // L10 - Kiến trúc và nghệ thuật phong kiến (4)
            326 => "- Chùa Một Cột (Diên Hựu) được xây năm 1049 thời vua Lý Thái Tông.
- Cố đô Huế (Phú Xuân) là kinh đô của nhà Nguyễn từ năm 1802 đến 1945.
- Tháp Chăm là di sản kiến trúc của vương quốc Champa; Thánh địa Mỹ Sơn là trung tâm tôn giáo của vương quốc này.
- Hoàng thành Thăng Long được UNESCO công nhận di sản thế giới nhân kỉ niệm 1000 năm Thăng Long – Hà Nội.
- Kiến trúc, nghệ thuật phong kiến thể hiện tài năng và bản sắc văn hoá dân tộc.",
            // L11 - Phong trào Cần Vương (1885–1896) (1)
            327 => "- Tháng 7/1885, sau vụ Kinh thành Huế, vua Hàm Nghi ra Chiếu Cần Vương kêu gọi văn thân, sĩ phu đứng lên cứu nước.
- Tôn Thất Thuyết phò vua Hàm Nghi xuất bôn, là người lãnh đạo phong trào.
- Khởi nghĩa Hương Khê do Phan Đình Phùng lãnh đạo là cuộc khởi nghĩa tiêu biểu nhất của phong trào.
- Phong trào kết thúc năm 1896 với sự hi sinh của Phan Đình Phùng.
- Phong trào Cần Vương thể hiện tinh thần yêu nước nhưng thất bại vì thiếu đường lối đúng đắn.",
            // L11 - Khởi nghĩa nông dân và phong trào Duy tân đầu thế kỉ XX (2)
            328 => "- Khởi nghĩa Yên Thế (1884–1913) do Hoàng Hoa Thám lãnh đạo nông dân chống Pháp gần 30 năm.
- Phong trào Đông Du (1905) do Phan Bội Châu khởi xướng: đưa thanh niên sang Nhật du học để học cách đánh Pháp.
- Đông Kinh Nghĩa Thục mở trường dạy chữ quốc ngữ miễn phí tại Hà Nội, bị Pháp đóng cửa năm 1908.
- Phan Châu Trinh chủ trương cải cách, nâng cao dân trí trước khi giành độc lập.
- Các phong trào đầu thế kỉ XX mang màu sắc dân chủ tư sản nhưng đều thất bại.",
            // L11 - Nguyễn Ái Quốc tìm đường cứu nước và thành lập Đảng (3)
            329 => "- Ngày 5/6/1911, Nguyễn Tất Thành rời bến Nhà Rồng (Sài Gòn) trên tàu Đô đốc La-tu-sơ Tơ-rê-vin ra đi tìm đường cứu nước.
- Tại Đại hội Tua (12/1920), Nguyễn Ái Quốc bỏ phiếu tán thành Quốc tế III, tham gia sáng lập Đảng Cộng sản Pháp.
- Người tìm thấy con đường cứu nước đúng đắn: cách mạng vô sản theo chủ nghĩa Mác – Lênin.
- Hội nghị hợp nhất tại Hương Cảng do Nguyễn Ái Quốc chủ trì đã hợp nhất ba tổ chức cộng sản thành Đảng Cộng sản Việt Nam.
- Sự ra đời của Đảng là bước ngoặt vĩ đại của cách mạng Việt Nam.",
            // L11 - Phong trào cách mạng 1930–1945 (4)
            330 => "- Phong trào Xô viết Nghệ Tĩnh lập nên chính quyền Xô viết đầu tiên của nhân dân.
- Hội nghị Trung ương 8 (5/1941) do Nguyễn Ái Quốc chủ trì quyết định thành lập Mặt trận Việt Minh.
- Đội Việt Nam Tuyên truyền Giải phóng quân được thành lập, là tiền thân của Quân đội nhân dân Việt Nam.
- Đội gồm 34 chiến sĩ với vũ khí thô sơ tại Cao Bằng, do Võ Nguyên Giáp chỉ huy.
- Phong trào 1930–1945 chuẩn bị lực lượng cho thắng lợi của Cách mạng tháng Tám.",
            // L12 - Cách mạng tháng Tám và kháng chiến chống Pháp (1945–1954) (1)
            331 => "- Ngày 19/8/1945, Hà Nội khởi nghĩa giành chính quyền, mở đầu thắng lợi của Cách mạng tháng Tám.
- Đại tướng Võ Nguyên Giáp là Tổng tư lệnh, trực tiếp chỉ huy chiến dịch Điện Biên Phủ.
- Chiến dịch Điện Biên Phủ kết thúc thắng lợi ngày 7/5/1954, “nên vành hoa đỏ nên thiên sử vàng”.
- Hiệp định Genève kí ngày 21/7/1954, công nhận độc lập, chủ quyền của Việt Nam.
- Thắng lợi kháng chiến chống Pháp chấm dứt ách thống trị thực dân trên đất nước ta.",
            // L12 - Kháng chiến chống Mĩ cứu nước (1954–1975) (2)
            332 => "- Ngày 17/1/1960, Bến Tre nổi dậy, mở đầu phong trào Đồng Khởi.
- “Điện Biên Phủ trên không”: 12 ngày đêm cuối năm 1972, Hà Nội bắn rơi nhiều pháo đài bay B-52 của Mĩ.
- Hiệp định Paris buộc Mĩ phải rút quân, công nhận độc lập, chủ quyền của Việt Nam.
- 11 giờ 30 phút ngày 30/4/1975, cờ giải phóng tung bay trên Dinh Độc Lập, kết thúc chiến dịch Hồ Chí Minh lịch sử.
- Thắng lợi 30/4/1975 giải phóng hoàn toàn miền Nam, thống nhất đất nước.",
            // L12 - Công cuộc Đổi mới từ năm 1986 (3)
            333 => "- Đại hội VI (tháng 12/1986) đề ra đường lối đổi mới toàn diện đất nước.
- Trước Đổi mới, nền kinh tế – xã hội rơi vào khủng hoảng của mô hình bao cấp, kế hoạch hoá tập trung.
- Nội dung trọng tâm của công cuộc Đổi mới là đổi mới kinh tế.
- Việt Nam phát triển nền kinh tế nhiều thành phần vận hành theo cơ chế thị trường.
- Đổi mới đưa đất nước thoát khỏi khủng hoảng, đời sống nhân dân ngày càng cải thiện.",
            // L12 - Việt Nam hội nhập quốc tế (4)
            334 => "- Ngày 28/7/1995, Việt Nam trở thành thành viên thứ 7 của ASEAN.
- Năm 2007, Việt Nam trở thành thành viên thứ 150 của Tổ chức Thương mại Thế giới (WTO).
- Việt Nam gia nhập Diễn đàn Hợp tác Kinh tế châu Á – Thái Bình Dương (APEC), hội nhập sâu rộng vào kinh tế khu vực.
- Việt Nam hai lần đảm nhiệm Ủy viên không thường trực Hội đồng Bảo an Liên hợp quốc: 2008–2009 và 2020–2021.
- Hội nhập quốc tế nâng cao vị thế, uy tín của Việt Nam trên trường quốc tế.",
            // ================= NGỮ VĂN =================
            // L6 - Danh từ và động từ
            45 => "- Danh từ chỉ người, vật, nơi chốn: trong câu “Mẹ nấu cơm”, “mẹ” và “cơm” là danh từ.
- Động từ chỉ hành động, hoạt động: “nấu”, “bay”, “chạy nhảy”, “bơi lội” là động từ.
- “Học sinh” chỉ người nên là danh từ chỉ người.
- Muốn xác định đúng từ loại, cần xét nghĩa của từ trong câu cụ thể.
- Phân biệt được danh từ, động từ giúp đặt câu và viết văn đúng.",
            // L6 - Tính từ và câu văn
            46 => "- Tính từ chỉ đặc điểm, tính chất: “cao lớn”, “chăm chỉ” là tính từ.
- “Hiền lành” là tính từ chỉ tính cách con người.
- Trong câu “Trời trong xanh”, chủ ngữ “trời” trả lời câu hỏi “cái gì?”, vị ngữ “trong xanh” trả lời “thế nào?”.
- “Chạy nhanh”, “đọc truyện” chỉ hành động nên là cụm động từ.
- Dùng tính từ phù hợp giúp câu văn sinh động, gợi cảm hơn.",
            // L6 - Phân biệt từ loại trong câu
            47 => "- “Hót” chỉ hoạt động của chim nên là động từ.
- “Chạy” chỉ hành động là động từ; “nhanh” chỉ đặc điểm là tính từ.
- Trong câu “Cô giáo giảng bài rất hay”, “hay” chỉ đặc điểm của bài giảng nên là tính từ.
- Danh từ, động từ, tính từ là ba từ loại cơ bản nhất cần nắm vững.
- Một từ có thể thuộc từ loại khác nhau, phải xét trong ngữ cảnh của câu.",
            // L6 - Ngữ văn lớp 6: danh từ (1)
            191 => "- Danh từ chỉ người, vật, hiện tượng, khái niệm: học sinh, bàn ghế, tình bạn.
- Danh từ riêng chỉ tên riêng của người, địa danh... và được viết hoa: Hà Nội.
- Danh từ chung chỉ loại sự vật: quyển sách.
- Cụm danh từ gồm danh từ trung tâm (bông hoa) và các thành tố phụ (những, đẹp).
- Nắm vững danh từ giúp gọi tên sự vật chính xác trong giao tiếp và viết văn.",
            // L6 - Ngữ văn lớp 6: động từ và tính từ (2)
            192 => "- Động từ chỉ hành động, trạng thái: chạy, đọc, ngủ, suy nghĩ.
- Tính từ chỉ đặc điểm, tính chất: đẹp, cao, nhanh, hiền lành.
- “Đọc sách” chỉ hành động nên “đọc” là động từ; “quyển sách”, “bàn ghế” là danh từ.
- Cụm tính từ lấy tính từ làm trung tâm: “rất đẹp và hiền”.
- Động từ và tính từ thường làm vị ngữ, giúp câu văn đầy đủ ý nghĩa.",
            // L7 - So sánh và nhân hoá
            48 => "- “Mặt trời như một quả cầu lửa khổng lồ” dùng biện pháp so sánh vì có từ “như”.
- “Chị gió thì thầm ngoài cửa sổ”: gió được gán hành động của con người (thì thầm) → nhân hoá.
- So sánh thường xuất hiện cùng các từ ngữ: như, tựa, giống như...
- Nhân hoá: gán hành động, tình cảm của người cho vật (“mỉm cười”, “gọi”).
- So sánh và nhân hoá làm câu văn sinh động, gợi hình, gợi cảm hơn.",
            // L7 - Ẩn dụ và hoán dụ
            49 => "- “Thuyền”, “bến” gọi người ra đi, người ở lại bằng tên sự vật khác → ẩn dụ.
- “Bàn tay ta làm nên tất cả”: lấy bộ phận (bàn tay) chỉ toàn thể (con người lao động) → hoán dụ.
- So sánh dùng từ “như, tựa, giống”; ẩn dụ gọi thẳng tên sự vật khác mà không dùng từ so sánh.
- “Quả” chỉ thành quả là ẩn dụ; “cả lớp” lấy vật chứa chỉ học sinh là hoán dụ.
- Ẩn dụ và hoán dụ làm lời văn hàm súc, giàu hình ảnh.",
            // L7 - Ngữ văn lớp 7: nhận diện từ loại trong câu (1)
            193 => "- Một từ có thể thuộc từ loại khác nhau tùy ngữ cảnh, phải xét trong câu cụ thể.
- Trong “quả bóng đá lăn tròn”, “đá” chỉ chất liệu làm bóng nên là danh từ.
- Trong “anh ấy đá bóng rất giỏi”, “đá” chỉ hành động nên là động từ.
- Trong “cô ấy hát hay”, “hay” chỉ đặc điểm của tiếng hát nên là tính từ.
- Không thể xác định đúng từ loại nếu tách từ ra khỏi câu.",
            // L7 - Ngữ văn lớp 7: lượng từ và quan hệ từ (2)
            194 => "- Lượng từ chỉ lượng ít nhiều: những, các, mỗi, một, vài.
- Quan hệ từ nối các từ ngữ với nhau: và, với, cùng, nhưng, của.
- Trong “những bông hoa”, “những” chỉ số nhiều, là lượng từ đứng trước danh từ.
- Trong “tôi đi học cùng bạn”, “cùng” nối “tôi” với “bạn”, biểu thị quan hệ cùng nhau.
- Dùng đúng lượng từ, quan hệ từ giúp câu văn rõ ràng, mạch lạc.",
            // L7 - Ngữ văn lớp 7: so sánh (1)
            195 => "- So sánh là đối chiếu hai sự vật có nét tương đồng để làm nổi bật hình ảnh.
- Từ so sánh thường gặp: như, tựa, bằng, là.
- Trong “trẻ em như búp trên cành”, “như” là từ so sánh.
- Tác dụng: so sánh giúp hình ảnh trở nên cụ thể, sinh động và gợi cảm hơn.
- So sánh gồm hai vế: vế được so sánh và vế dùng để so sánh.",
            // L7 - Ngữ văn lớp 7: nhân hoá (2)
            196 => "- Nhân hoá là gán hành động, tình cảm, cách xưng hô của người cho vật.
- Cách nhân hoá: dùng từ ngữ vốn chỉ người để gọi, tả vật — như gọi gió là “chị”, gán hành động “mơn man”.
- Nhân hoá khiến sự vật trở nên có hồn, gần gũi với con người.
- Nhân hoá thường đi cùng với so sánh để tăng sức gợi hình.
- Nhận diện nhân hoá giúp cảm thụ cái hay của câu văn, câu thơ.",
            // L8 - Bố cục bài văn miêu tả
            50 => "- Bài văn miêu tả gồm ba phần: mở bài, thân bài, kết bài.
- Mở bài giới thiệu đối tượng được miêu tả.
- Thân bài tả chi tiết các đặc điểm của đối tượng theo trình tự hợp lí.
- Kết bài nêu cảm nghĩ, ấn tượng về đối tượng được tả.
- Nên dùng từ ngữ gợi hình, gợi cảm (xanh mướt, rì rào) để bài văn sinh động.",
            // L8 - Dấu câu trong văn bản
            52 => "- Câu kể (câu trần thuật) kết thúc bằng dấu chấm.
- Câu hỏi kết thúc bằng dấu hỏi (?).
- Dấu hai chấm (:) báo hiệu phần giải thích, liệt kê hoặc lời nói trực tiếp.
- Dấu phẩy dùng để ngăn cách các thành phần bên trong câu.
- Dùng đúng dấu câu giúp người đọc hiểu đúng ý của câu văn.",
            // L8 - Ngữ văn lớp 8: ẩn dụ (1)
            197 => "- Ẩn dụ giống so sánh nhưng không dùng từ so sánh.
- “Thuyền – bến” ẩn dụ cho người đi – người ở lại, không dùng từ “như”.
- Trong “Đêm nay Bác không ngủ”, “người cha” ẩn dụ chỉ Bác Hồ.
- Phân biệt: so sánh nói “A như B”; ẩn dụ gọi thẳng A bằng tên của B.
- Ẩn dụ làm lời thơ hàm súc, giàu sức gợi.",
            // L8 - Ngữ văn lớp 8: hoán dụ (2)
            198 => "- Hoán dụ dựa trên quan hệ gần gũi giữa các sự vật.
- “Bàn tay” lấy bộ phận chỉ toàn thể (người lao động) là hoán dụ.
- “Áo nâu” chỉ người nông dân, “áo xanh” chỉ bộ đội: lấy dấu hiệu chỉ sự vật.
- Phân biệt: ẩn dụ dựa trên nét tương đồng (thuyền – người đi); hoán dụ dựa trên quan hệ gần gũi (bàn tay – người).
- Hoán dụ làm câu văn ngắn gọn mà giàu hình ảnh.",
            // L8 - Ngữ văn lớp 8: bài văn miêu tả (1)
            199 => "- Bố cục 3 phần: mở bài giới thiệu, thân bài miêu tả chi tiết, kết bài nêu cảm nghĩ.
- Muốn tả hay, trước hết phải quan sát kĩ bằng mắt, tai, mũi... để miêu tả chân thực, sinh động.
- Khi tả cảnh, trình tự hợp lí là từ bao quát toàn cảnh rồi đi vào từng chi tiết cụ thể.
- Kết bài bộc lộ tình cảm, ấn tượng sâu sắc về đối tượng được tả.
- Chọn chi tiết tiêu biểu, tránh liệt kê dàn trải.",
            // L8 - Ngữ văn lớp 8: dấu câu trong văn bản (2)
            200 => "- Dấu phẩy ngăn cách các thành phần câu và các vế câu.
- Dấu hai chấm báo hiệu phần giải thích, liệt kê hoặc dẫn lời nói trực tiếp.
- Dấu ngoặc kép đánh dấu lời nói trực tiếp và từ ngữ được trích dẫn.
- Khi liệt kê các thành phần cùng chức năng, phải ngăn cách bằng dấu phẩy.
- Đặt sai dấu câu có thể làm thay đổi nghĩa của câu.",
            // L9 - Ngữ văn lớp 9: văn tả người (1)
            201 => "- Tả người cần làm nổi bật chân dung (hình dáng, khuôn mặt) và hoạt động tiêu biểu.
- Chi tiết tiêu biểu gợi được tính cách, cuộc đời của nhân vật.
- Ngoài tả, cần kết hợp kể việc làm và bộc lộ tình cảm để nhân vật sống động.
- Tránh liệt kê chung chung khiến bài văn nhạt nhoà, thiếu ấn tượng.
- Nên sắp xếp chi tiết theo trình tự hợp lí: từ ngoại hình đến tính cách, việc làm.",
            // L9 - Ngữ văn lớp 9: văn tả cảnh, tả vật (2)
            202 => "- Tả cảnh cần trình tự hợp lí: bao quát → chi tiết, hoặc theo không gian, thời gian.
- Tả con vật: chú ý hình dáng, màu sắc, đặc điểm nổi bật, ích lợi hoặc kỉ niệm.
- Tả đồ vật: từ hình dáng chung đến từng bộ phận, rồi công dụng và kỉ niệm gắn bó.
- Cảm xúc của người viết làm cảnh vật có hồn, bài văn thêm sâu sắc.
- Kết hợp quan sát tinh tế với tưởng tượng, so sánh để bài văn sinh động.",
            // L10 - Đọc hiểu truyện ngắn: cốt truyện và nhân vật (1)
            347 => "- Cốt truyện là hệ thống sự kiện có quan hệ nhân quả, thường phát triển qua: mở đầu – thắt nút – cao trào – mở nút – kết thúc.
- Tình huống truyện là hoàn cảnh đặc biệt làm nảy sinh sự việc; qua cách nhân vật ứng xử, tính cách và chủ đề được bộc lộ.
- Người kể chuyện ngôi thứ nhất xưng “tôi”, kể theo điểm nhìn hạn chế của mình.
- Nhân vật chính giữ vai trò trung tâm, mọi sự kiện xoay quanh nhân vật này.
- Đọc hiểu truyện cần bám sát văn bản, không suy diễn xa rời chi tiết.",
            // L10 - Đọc hiểu truyện ngắn: chi tiết, chủ đề và thông điệp (2)
            348 => "- Chi tiết nghệ thuật là chi tiết được chọn lọc kĩ, giàu sức gợi, góp phần khắc họa nhân vật và thể hiện chủ đề.
- Chủ đề là vấn đề trung tâm mà tác phẩm tập trung phản ánh và thể hiện.
- Thông điệp là điều tác giả gửi gắm, chỉ hiện ra khi khái quát toàn bộ diễn biến, số phận nhân vật và kết thúc.
- Tư tưởng là cách nhìn, cách đánh giá của tác giả về cuộc sống, thể hiện qua toàn bộ tác phẩm.
- Đọc hiểu sâu là đi từ chi tiết đến chủ đề, thông điệp của tác phẩm.",
            // L10 - Đọc hiểu thơ: thể thơ, nhịp điệu và vần (1)
            349 => "- Thơ lục bát: câu lục (6 chữ) xen câu bát (8 chữ), gieo vần ở tiếng thứ 6 của câu lục và câu bát.
- Nhịp là cách ngắt câu thơ thành từng đoạn tạo tiết tấu (ví dụ nhịp 2/2, 4/4 trong câu 8 chữ).
- Vần là sự lặp lại âm thanh ở cuối (hoặc giữa) câu thơ, tạo nhạc tính và sự liên kết.
- Thơ tự do phá bỏ khuôn mẫu gò bó của thơ cách luật, nhịp điệu theo cảm xúc tự nhiên.
- Đọc thơ cần chú ý thể thơ, nhịp, vần để cảm nhận nhạc điệu của bài.",
            // L10 - Đọc hiểu thơ: hình ảnh thơ và cảm xúc trữ tình (2)
            350 => "- Hình ảnh thơ không phải ảnh chụp mà là hình ảnh hiện lên trong tâm trí người đọc từ ngôn ngữ thơ.
- Chủ thể trữ tình (thường xưng “tôi”, “ta”, “anh”, “em”) là người trực tiếp bộc lộ cảm xúc.
- Cảm xúc trữ tình thấm đẫm trong hình ảnh, từ ngữ và giọng điệu của toàn bài.
- Từ láy, từ gợi hình, gợi thanh làm câu thơ sinh động như có hình, có tiếng.
- Đọc thơ là lắng nghe cảm xúc của chủ thể trữ tình qua từng hình ảnh.",
            // L11 - Nghị luận về tư tưởng đạo lí: khái niệm và thao tác lập luận (1)
            351 => "- Nghị luận về tư tưởng đạo lí bàn về các vấn đề thuộc lĩnh vực tư tưởng, đạo đức, lối sống.
- Thao tác giải thích: làm rõ nghĩa của khái niệm, giúp người đọc hiểu đúng vấn đề.
- Thao tác chứng minh: dùng dẫn chứng (số liệu, tấm gương, sự việc) và lí lẽ để làm sáng tỏ luận điểm.
- Luận điểm là ý chính của bài; mỗi luận điểm cần được triển khai bằng luận cứ.
- Bài nghị luận cần lập luận chặt chẽ, dẫn chứng thuyết phục.",
            // L11 - Nghị luận về tư tưởng đạo lí: phân tích đề và sử dụng dẫn chứng (2)
            352 => "- Phân tích đề giúp xác định đúng vấn đề và góc độ bàn luận, tránh lạc đề.
- Dẫn chứng phải chọn lọc: tiêu biểu, xác thực, gắn chặt với luận điểm đang chứng minh.
- Bố cục ba phần là chuẩn mực: mở bài nêu vấn đề, thân bài triển khai, kết bài khẳng định.
- Câu chuyển đoạn tạo sự liên kết giữa các đoạn, giúp mạch lập luận trôi chảy.
- Dẫn chứng càng gần gũi thực tế càng có sức thuyết phục.",
            // L11 - Nghị luận về hiện tượng đời sống: nhận diện và đánh giá (1)
            353 => "- Hiện tượng đời sống là những sự việc, hiện tượng có thật, nổi bật trong đời sống hằng ngày.
- Bước đầu tiên: nhận diện đúng hiện tượng — đó là hiện tượng gì, biểu hiện ra sao — rồi mới đánh giá.
- Đánh giá cần khách quan, toàn diện để bài văn thuyết phục và sâu sắc.
- Vì bàn về đời sống thực nên dẫn chứng phải lấy từ thực tế có thật, đáng tin cậy.
- Bài văn hay là bài vừa nhận diện đúng, vừa đánh giá sâu.",
            // L11 - Nghị luận về hiện tượng đời sống: nguyên nhân và giải pháp (2)
            354 => "- Phân tích nguyên nhân cần thấy cả yếu tố chủ quan (ý thức con người) và khách quan (môi trường, quản lí).
- Giải pháp phải cụ thể, làm được trong thực tế, hướng tới từng đối tượng liên quan.
- Với hiện tượng tích cực, bài văn nên ngợi ca ý nghĩa và lan toả trong cộng đồng.
- Liên hệ bản thân thể hiện sự thấm thía vấn đề và trách nhiệm của người viết.
- Kết bài khẳng định lại quan điểm và kêu gọi hành động.",
            // L12 - Phân tích tác phẩm văn xuôi: nhân vật và tình huống truyện (1)
            355 => "- Tính cách nhân vật bộc lộ qua bốn phương diện: ngoại hình, hành động, lời nói và tâm lí.
- Phân tích tình huống truyện giúp thấy “cái thế” mà nhân vật bị đặt vào — nơi tính cách và tư tưởng bộc lộ rõ nhất.
- Diễn biến tâm lí nhân vật bộc lộ rõ nhất khi đối mặt với xung đột, biến cố hoặc lựa chọn khó khăn.
- Lời nói là “cửa sổ” tâm hồn: qua đối thoại và độc thoại, tính cách nhân vật hiện lên chân thực.
- Phân tích nhân vật phải gắn với tình huống và gắn với chủ đề tác phẩm.",
            // L12 - Phân tích tác phẩm văn xuôi: giá trị nội dung và nghệ thuật (2)
            356 => "- Giá trị hiện thực: tác phẩm như “tấm gương” phản ánh đời sống, xã hội một thời.
- Giá trị nhân đạo là tình cảm nhân văn: yêu thương, đồng cảm, đấu tranh cho con người.
- Giá trị nghệ thuật nằm ở cách xây dựng tình huống, kết cấu, ngôn ngữ, giọng điệu...
- Nội dung và nghệ thuật thống nhất: tư tưởng sâu sắc cần hình thức nghệ thuật đặc sắc để thể hiện.
- Đánh giá tác phẩm phải toàn diện cả nội dung và nghệ thuật.",
            // L12 - Phân tích thơ: tứ thơ, hình tượng và cảm xúc (1)
            357 => "- Tứ thơ là “xương sống” ý tưởng, gắn kết các hình ảnh, cảm xúc trong bài.
- Hình tượng thơ là hình ảnh nghệ thuật được tạo bằng ngôn từ, nhạc điệu và biện pháp tu từ.
- Khi phân tích một khổ thơ: đi từ từ ngữ, hình ảnh đặc sắc đến biện pháp tu từ và cảm xúc chủ đạo.
- Mạch cảm xúc trong bài thơ thường vận động: từ nhớ đến thương, từ buồn đến hi vọng...
- Bám sát tứ thơ giúp bài phân tích có trọng tâm, không lan man.",
            // L12 - Phân tích thơ: giá trị tư tưởng và nghệ thuật (2)
            358 => "- Giá trị tư tưởng của bài thơ là chiều sâu nhận thức và tình cảm nhân văn mà bài thơ gửi gắm.
- Đặc sắc nghệ thuật của thơ là sự kết hợp của nhạc điệu, ngôn từ, hình ảnh và giọng điệu.
- Kết bài khái quát lại giá trị tư tưởng, nghệ thuật và để lại cảm nhận chân thành.
- Nội dung và nghệ thuật thống nhất: phân tích tách rời sẽ phiến diện.
- Một bài phân tích thơ hay vừa sâu sắc về tư tưởng, vừa tinh tế về nghệ thuật.",
        ];

        foreach ($summaries as $id => $summary) {
            DB::table('lessons')
                ->where('id', $id)
                ->where(function ($query) {
                    $query->whereNull('summary')->orWhere('summary', '');
                })
                ->update(['summary' => $summary]);
        }
    }
}
