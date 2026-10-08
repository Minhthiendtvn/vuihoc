<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tóm tắt bài học nhóm môn C (Địa lý, GDCD, Tin học, Công nghệ).
 * Idempotent: chỉ cập nhật khi cột summary đang NULL hoặc rỗng, chạy lại không ghi đè.
 */
class LessonSummaryGroupCSeeder extends Seeder
{
    public function run(): void
    {
        $summaries = [
            // ================= TIN HOC =================
            39 => "- CPU là bộ não của máy tính, thực hiện mọi phép tính và điều khiển hoạt động của máy.\n"
                . "- Bàn phím, chuột là thiết bị vào (đưa dữ liệu vào máy); màn hình, loa, máy in là thiết bị ra (đưa kết quả ra).\n"
                . "- RAM là bộ nhớ tạm thời: tắt máy thì dữ liệu trong RAM bị mất.\n"
                . "- Ổ cứng (SSD) lưu dữ liệu lâu dài, không mất khi tắt máy.\n"
                . "- Mỗi bộ phận có chức năng riêng và phối hợp với nhau để máy tính hoạt động.",

            40 => "- Muốn giữ tài liệu dùng lâu dài phải lưu vào ổ cứng hoặc SSD, không để trong RAM.\n"
                . "- Màn hình cảm ứng vừa là thiết bị vào (nhận thao tác chạm) vừa là thiết bị ra (hiển thị hình ảnh).\n"
                . "- Tốc độ xử lý của máy tính phụ thuộc chủ yếu vào CPU: CPU càng mạnh, máy chạy càng nhanh.\n"
                . "- RAM càng lớn thì máy tính chạy được nhiều chương trình cùng lúc càng mượt mà.\n"
                . "- Nhận biết và phân loại được các thiết bị vào, thiết bị ra của máy tính.",

            143 => "- Màn hình hiển thị mọi hình ảnh, chữ viết của máy tính.\n"
                . "- Bàn phím dùng để gõ chữ, nhập văn bản và số liệu vào máy.\n"
                . "- Chuột điều khiển con trỏ trên màn hình, nhấp chuột để chọn và mở.\n"
                . "- Laptop gộp màn hình, bàn phím và thân máy thành một khối gọn nhẹ, dễ mang theo.\n"
                . "- Máy tính để bàn gồm các bộ phận rời, thuận tiện nâng cấp từng phần.",

            144 => "- Thiết bị vào đưa dữ liệu vào máy tính: bàn phím, chuột, micro, webcam.\n"
                . "- Thiết bị ra đưa kết quả ra ngoài: màn hình, máy in, loa.\n"
                . "- Webcam thu hình ảnh vào máy, dùng khi gọi video, học trực tuyến.\n"
                . "- Micro thu âm thanh vào máy nên là thiết bị vào.\n"
                . "- Phân biệt thiết bị vào và thiết bị ra giúp sử dụng máy tính đúng cách.",

            41 => "- Hệ điều hành (như Windows) quản lý phần cứng và phần mềm, là cầu nối giữa người dùng với máy tính.\n"
                . "- Máy tính thiếu hệ điều hành sẽ không thể hoạt động bình thường.\n"
                . "- Phần mềm ứng dụng phục vụ nhu cầu cụ thể: Word soạn văn bản, Chrome duyệt web.\n"
                . "- Ví dụ: Windows là hệ điều hành, còn Word và Chrome là phần mềm ứng dụng.\n"
                . "- Phân biệt được phần mềm hệ thống (hệ điều hành) và phần mềm ứng dụng.",

            42 => "- Phần mở rộng (đuôi tệp) cho biết tệp thuộc loại nào: .docx là văn bản Word, .mp3 là nhạc, .jpg là ảnh, .mp4 là video.\n"
                . "- Đuôi tệp nằm sau dấu chấm trong tên tệp.\n"
                . "- Thư mục (folder) dùng để chứa và phân loại các tệp cùng chủ đề cho gọn gàng.\n"
                . "- Đặt tên tệp, thư mục rõ ràng giúp tìm kiếm nhanh chóng sau này.\n"
                . "- Không nên đổi bừa đuôi tệp vì tệp có thể không mở được.",

            145 => "- CPU là bộ não của máy tính, thực hiện mọi phép tính và điều khiển.\n"
                . "- RAM là bộ nhớ tạm: tắt máy thì dữ liệu trong RAM mất hết.\n"
                . "- SSD đọc ghi nhanh và êm hơn HDD vì không có đĩa quay.\n"
                . "- RAM càng lớn, máy càng chạy được nhiều chương trình cùng lúc mượt mà.\n"
                . "- Tốc độ máy tính phụ thuộc chủ yếu vào CPU và dung lượng RAM.",

            146 => "- Tắt máy tính đúng cách giúp lưu dữ liệu và làm máy bền hơn.\n"
                . "- Không để đồ ăn, nước uống gần máy tính vì đổ vào gây chập, hỏng bàn phím.\n"
                . "- Đặt máy ở nơi khô ráo, thoáng mát để tản nhiệt tốt.\n"
                . "- Khi điện chập chờn nên dùng ổn áp để bảo vệ linh kiện khỏi cháy hỏng.\n"
                . "- Bảo quản thiết bị đúng cách giúp máy tính bền và hoạt động ổn định.",

            147 => "- Hệ điều hành (như Windows) là phần mềm hệ thống, điều hành toàn bộ máy tính.\n"
                . "- Phần mềm ứng dụng phục vụ nhu cầu cụ thể của người dùng như soạn thảo, giải trí, học tập.\n"
                . "- Không có hệ điều hành, máy tính không thể hoạt động.\n"
                . "- Ví dụ: Windows là hệ điều hành, Word là phần mềm ứng dụng soạn thảo văn bản.\n"
                . "- Mỗi phần mềm được tạo ra cho một mục đích sử dụng nhất định.",

            148 => "- .docx là tệp văn bản Word, .jpg là tệp ảnh, .mp4 là tệp video.\n"
                . "- Phần mở rộng nằm sau dấu chấm trong tên tệp, cho biết loại tệp.\n"
                . "- Biết đuôi tệp giúp chọn đúng phần mềm để mở.\n"
                . "- Thư mục dùng để sắp xếp, phân loại các tệp cho gọn gàng.\n"
                . "- Đặt tên tệp rõ ràng giúp tìm kiếm nhanh sau này.",

            43 => "- Mật khẩu mạnh phải dài và gồm chữ hoa, chữ thường, số cùng ký tự đặc biệt.\n"
                . "- Tuyệt đối không cung cấp mật khẩu cho người lạ; tin nhắn yêu cầu mật khẩu là lừa đảo.\n"
                . "- Địa chỉ nhà, số điện thoại là thông tin riêng tư, không chia sẻ công khai trên mạng.\n"
                . "- Khi gặp yêu cầu khả nghi, hãy báo ngay cho người lớn biết.\n"
                . "- Giữ kín thông tin cá nhân là nguyên tắc đầu tiên khi dùng máy tính.",

            44 => "- Ctrl+C sao chép, Ctrl+V dán, Ctrl+X cắt, Ctrl+S lưu nhanh văn bản.\n"
                . "- Khi gõ tiếng Việt đúng kỹ thuật, các ngón tay đặt ở hàng phím cơ sở A S D F J K L.\n"
                . "- Dùng phím tắt giúp thao tác nhanh hơn dùng chuột.\n"
                . "- Luyện gõ 10 ngón giúp soạn thảo văn bản nhanh và chính xác.\n"
                . "- Nhớ các phím tắt thông dụng để làm việc hiệu quả hơn.",

            149 => "- Nhấp chuột phải, chọn New rồi Folder để tạo thư mục mới.\n"
                . "- Tệp đã xóa thường vào thùng rác (Recycle Bin), có thể khôi phục lại khi cần.\n"
                . "- Copy sao chép tệp (tệp gốc vẫn còn), Paste để dán tệp vào vị trí mới.\n"
                . "- Đặt tên thư mục rõ ràng giúp tìm tệp nhanh chóng sau này.\n"
                . "- Sắp xếp tệp theo thư mục giúp máy tính gọn gàng, dễ quản lý.",

            150 => "- Dùng phần mềm crack (bẻ khóa) là vi phạm bản quyền và dễ bị nhiễm virus.\n"
                . "- Phần mềm mã nguồn mở cho phép xem, sửa và chia sẻ mã nguồn tự do.\n"
                . "- LibreOffice là bộ phần mềm văn phòng miễn phí, hợp pháp.\n"
                . "- Mua phần mềm có bản quyền được cập nhật, hỗ trợ kỹ thuật và yên tâm sử dụng.\n"
                . "- Tôn trọng bản quyền là trách nhiệm của người dùng máy tính.",

            151 => "- Mật khẩu mạnh gồm chữ hoa, chữ thường, số và ký tự đặc biệt.\n"
                . "- Mỗi tài khoản nên dùng một mật khẩu riêng để lộ một nơi không mất tất cả.\n"
                . "- Xác thực hai bước (2FA) yêu cầu thêm mã xác nhận nên bảo vệ tốt hơn dù lộ mật khẩu.\n"
                . "- Dùng xong máy tính chung ở trường phải đăng xuất tài khoản của mình.\n"
                . "- Đổi mật khẩu định kỳ giúp tài khoản an toàn hơn.",

            152 => "- USB lạ là con đường lây virus máy tính phổ biến nhất.\n"
                . "- Dấu hiệu nghi nhiễm virus: máy tính đột nhiên chậm bất thường.\n"
                . "- Không mở tệp đính kèm trong email lạ vì có thể chứa virus.\n"
                . "- Phần mềm diệt virus cần cập nhật thường xuyên để nhận diện được virus mới.\n"
                . "- Cẩn thận với thiết bị lạ và email lạ giúp phòng tránh virus hiệu quả.",

            153 => "- Dấu chân số: mọi thứ đăng lên mạng đều lưu lại lâu dài, khó xóa hết.\n"
                . "- Trước khi đăng ảnh có mặt bạn bè phải xin phép họ.\n"
                . "- Bình luận ác ý, xúc phạm người khác trên mạng có thể bị xử lý theo pháp luật.\n"
                . "- Kiểm tra cài đặt riêng tư của tài khoản thường xuyên để bảo vệ thông tin.\n"
                . "- Nghĩ kỹ trước khi đăng bất cứ điều gì lên mạng.",

            154 => "- Nghi ngờ tài khoản bị hack: đổi mật khẩu ngay lập tức.\n"
                . "- Bị lừa chuyển tiền qua mạng: giữ lại bằng chứng giao dịch và báo công an.\n"
                . "- Bị bắt nạt trên mạng: không đáp trả, hãy chặn, lưu bằng chứng và báo cáo.\n"
                . "- Trẻ em bị hại trên mạng được hỗ trợ qua tổng đài 111.\n"
                . "- Khi gặp sự cố trên mạng, kể ngay với người lớn để được giúp đỡ.",

            287 => "- Muốn tạo mục lục tự động trong Word, trước tiên phải áp dụng Heading Styles cho các tiêu đề.\n"
                . "- Sau khi sửa nội dung, dùng Update Table để cập nhật mục lục mà không cần tạo lại.\n"
                . "- Phím tắt định dạng: Ctrl+B in đậm, Ctrl+I in nghiêng, Ctrl+U gạch chân.\n"
                . "- Kiểu căn lề: Justify căn đều hai bên, Left căn trái, Center căn giữa, Right căn phải.\n"
                . "- Định dạng văn bản đúng chuẩn giúp tài liệu chuyên nghiệp, dễ đọc.",

            288 => "- Insert > Table để chèn bảng, chọn số hàng và số cột theo ý muốn.\n"
                . "- Merge Cells gộp các ô đã chọn thành một ô; Split Cells tách một ô thành nhiều ô.\n"
                . "- Chọn kiểu bao quanh Square hoặc Tight để chữ ôm quanh hình, không che mất chữ.\n"
                . "- References > Insert Caption đánh số tự động Bảng 1, Hình 1 cho bảng biểu và hình ảnh.\n"
                . "- Trình bày bảng biểu, hình ảnh khoa học giúp văn bản rõ ràng hơn.",

            289 => "- Mọi công thức trong Excel đều bắt đầu bằng dấu bằng (=).\n"
                . "- SUM(vùng) tính tổng, AVERAGE tính trung bình, MAX tìm giá trị lớn nhất, MIN tìm giá trị nhỏ nhất.\n"
                . "- Địa chỉ ô = tên cột + số hàng, ví dụ C5.\n"
                . "- Ví dụ =SUM(B2:B10) tính tổng các ô từ B2 đến B10.\n"
                . "- Dùng hàm giúp tính toán nhanh và chính xác hơn tính tay.",

            290 => "- Cú pháp hàm IF: =IF(điều kiện, giá trị khi đúng, giá trị khi sai).\n"
                . "- Ví dụ =IF(B2>=5,\"Đạt\",\"Chưa đạt\") trả về Đạt khi B2 = 7.\n"
                . "- COUNTIF(vùng, điều kiện) đếm các ô trong vùng thỏa mãn điều kiện cho trước.\n"
                . "- Data > Filter tạo nút lọc ở tiêu đề cột để hiển thị dữ liệu theo tiêu chí.\n"
                . "- Hàm điều kiện và lọc dữ liệu giúp xử lý bảng tính thông minh hơn.",

            291 => "- Mạng máy tính là các máy tính kết nối với nhau để chia sẻ tài nguyên và trao đổi thông tin.\n"
                . "- LAN là mạng cục bộ phạm vi hẹp; MAN phạm vi đô thị; WAN phạm vi diện rộng.\n"
                . "- Internet là mạng WAN lớn nhất, kết nối hàng tỉ thiết bị trên toàn cầu.\n"
                . "- Nhờ mạng máy tính, nhiều máy có thể dùng chung máy in, dữ liệu và kết nối Internet.\n"
                . "- Phân loại mạng dựa vào phạm vi địa lý kết nối.",

            292 => "- Mô hình khách – chủ: server lưu trữ và cung cấp dịch vụ, client là máy sử dụng dịch vụ.\n"
                . "- Switch hoặc hub là thiết bị trung tâm nối nhiều máy tính trong mạng LAN.\n"
                . "- Router định tuyến gói tin giữa các mạng, thường tích hợp phát Wi-Fi trong gia đình.\n"
                . "- Mạng ngang hàng (peer-to-peer) không phân biệt server và client, phù hợp mạng nhỏ.\n"
                . "- Mỗi thiết bị mạng có vai trò riêng trong việc kết nối và truyền dữ liệu.",

            293 => "- Mỗi thiết bị tham gia mạng có một địa chỉ IP duy nhất, ví dụ 192.168.1.1.\n"
                . "- Hệ thống DNS dịch tên miền (như google.com) thành địa chỉ IP để máy tính hiểu.\n"
                . "- HTTPS mã hóa dữ liệu truyền đi, an toàn hơn HTTP khi đăng nhập, thanh toán.\n"
                . "- Email, tìm kiếm, lưu trữ đám mây là các dịch vụ trên Internet; soạn thảo offline thì không.\n"
                . "- Internet mang lại nhiều dịch vụ tiện ích cho học tập và đời sống.",

            294 => "- Phishing là hình thức lừa người dùng nhập thông tin vào trang web giả mạo.\n"
                . "- Ransomware mã hóa tệp tin, nạn nhân phải trả tiền mới lấy lại được dữ liệu.\n"
                . "- Mật khẩu mạnh: dài, gồm chữ hoa, chữ thường, số và ký tự đặc biệt, không đoán được.\n"
                . "- Email lạ có tệp đính kèm tuyệt đối không mở vì có thể chứa mã độc.\n"
                . "- Cảnh giác với đường link và tệp lạ là cách phòng chống mã độc hiệu quả.",

            295 => "- Thuật toán mô tả cách giải bài toán bằng các bước rõ ràng, hữu hạn.\n"
                . "- Thuật toán phải dừng sau hữu hạn bước; tính vô hạn là sai.\n"
                . "- Trong sơ đồ khối: hình thoi là điều kiện rẽ nhánh, hình chữ nhật là tính toán, ô van là bắt đầu/kết thúc, mũi tên chỉ hướng đi.\n"
                . "- Sơ đồ khối mô tả thuật toán một cách trực quan bằng hình vẽ.\n"
                . "- Có thể mô tả thuật toán bằng liệt kê bước hoặc bằng sơ đồ khối.",

            296 => "- Cấu trúc tuần tự thực hiện các bước theo đúng thứ tự từ trên xuống.\n"
                . "- Rẽ nhánh đầy đủ (if-else): điều kiện đúng thì làm A, sai thì làm B.\n"
                . "- Cấu trúc lặp (for, while) thực hiện lại khối lệnh khi điều kiện còn đúng.\n"
                . "- Bài toán tính tổng các số từ 1 đến 100 phù hợp với cấu trúc lặp.\n"
                . "- Ba cấu trúc cơ bản kết hợp với nhau giải được mọi bài toán.",

            297 => "- Lệnh print() dùng để in ra màn hình, ví dụ print(\"Xin chào\").\n"
                . "- Python gán biến bằng dấu =, không cần khai báo kiểu trước.\n"
                . "- Các kiểu dữ liệu cơ bản: int (số nguyên), float (số thập phân), str (chuỗi ký tự).\n"
                . "- input() trả về chuỗi (str); muốn tính toán phải đổi kiểu, ví dụ int(input(\"Nhập tuổi: \")).\n"
                . "- Biến giúp lưu trữ dữ liệu để chương trình xử lý.",

            298 => "- range(3) tạo dãy 0, 1, 2; range(a, b) chạy từ a đến b-1.\n"
                . "- Vòng lặp while tiếp tục chạy chừng nào điều kiện còn đúng.\n"
                . "- Python dùng thụt đầu dòng để xác định khối lệnh sau if, for, while.\n"
                . "- Thụt đầu dòng không đúng làm chương trình báo lỗi hoặc chạy sai.\n"
                . "- Kết hợp if với vòng lặp giải được nhiều bài toán thực tế.",

            // ================= CONG NGHE =================
            58 => "- Nước dẫn điện tốt nên tay ướt chạm vào ổ điện hoặc thiết bị điện rất dễ bị điện giật.\n"
                . "- Trước khi sửa chữa thiết bị điện phải ngắt cầu dao hoặc rút phích điện.\n"
                . "- Thấy dây điện hở vỏ tuyệt đối không chạm vào, tránh xa và báo người lớn.\n"
                . "- Trẻ em không tự ý sửa đồ điện; luôn nhờ người lớn xử lý.\n"
                . "- Tuân thủ quy tắc an toàn điện bảo vệ tính mạng của chính mình.",

            59 => "- Thấy người bị điện giật: tuyệt đối không chạm tay vào nạn nhân, phải ngắt điện trước.\n"
                . "- Dùng vật cách điện như gậy gỗ khô để gạt dây điện ra khỏi người bị nạn.\n"
                . "- Ngửi thấy mùi khét từ ổ điện phải ngắt điện và báo người lớn ngay.\n"
                . "- Vật kim loại hoặc ướt dẫn điện nên không dùng để cứu người bị điện giật.\n"
                . "- Bình tĩnh, ngắt điện trước rồi mới cứu là nguyên tắc quan trọng nhất.",

            155 => "- Tay ướt không được cắm điện vì nước dẫn điện rất tốt.\n"
                . "- Thấy dây điện hở trong nhà phải báo người lớn xử lý, không tự chạm vào.\n"
                . "- Không thả diều gần đường dây điện vì diều vướng vào gây điện giật, chập cháy.\n"
                . "- Kim loại dẫn điện nên tuyệt đối không chọc vào ổ cắm.\n"
                . "- Nhận biết dấu hiệu nguy hiểm về điện để tránh xa.",

            156 => "- Rút phích điện phải cầm phần nhựa cách điện rút thẳng ra, không giật dây.\n"
                . "- Đồ điện trong nhà bị hỏng, trẻ em không tự sửa mà báo người lớn.\n"
                . "- Dùng xong đồ điện nên tắt và rút phích, vừa an toàn vừa tiết kiệm điện.\n"
                . "- Ấm điện nóng và dùng điện nên để xa trẻ nhỏ, xa nước.\n"
                . "- Dùng đồ điện đúng cách mỗi ngày để an toàn cho cả gia đình.",

            60 => "- Sắt (kim loại) dễ bị gỉ sét khi tiếp xúc với nước và không khí ẩm.\n"
                . "- Tay cầm kìm, tua vít thường bọc nhựa vì nhựa cách điện, cách nhiệt, cầm an toàn.\n"
                . "- Nhựa nhẹ, không thấm nước nên dùng làm chai lọ, hộp đựng, áo mưa.\n"
                . "- Mỗi vật liệu có tính chất riêng, cần chọn vật liệu phù hợp với mục đích sử dụng.\n"
                . "- Tính chất vật liệu quyết định công dụng của đồ vật.",

            62 => "- Búa dùng để đóng đinh, gõ; kìm để kẹp, cắt; tua vít để vặn ốc; cưa để cắt gỗ.\n"
                . "- Tua vít có đầu vừa với rãnh ốc nên dùng để vặn chặt hoặc tháo ốc vít.\n"
                . "- Dụng cụ sắc nhọn như cưa dễ gây đứt tay, phải có người lớn hướng dẫn khi dùng.\n"
                . "- Dùng dụng cụ phải cầm chắc, giữ tay xa lưỡi sắc để an toàn.\n"
                . "- Chọn đúng dụng cụ giúp công việc nhanh và an toàn hơn.",

            157 => "- Thấy chập điện trong nhà, việc đầu tiên là ngắt cầu dao để cắt nguồn điện.\n"
                . "- Thấy người bị điện giật tuyệt đối không chạm trực tiếp vào nạn nhân.\n"
                . "- Dùng gậy gỗ khô (vật cách điện) để gạt dây điện ra khỏi người bị nạn.\n"
                . "- Xảy ra cháy do điện gọi ngay số 114 (cứu hỏa).\n"
                . "- Ngắt điện là nguyên tắc an toàn đầu tiên và quan trọng nhất.",

            158 => "- Bóng đèn LED tiết kiệm điện nhất: sáng hơn mà tốn ít điện hơn bóng sợi đốt.\n"
                . "- Ra khỏi phòng phải tắt đèn, quạt và các thiết bị điện.\n"
                . "- Điều hòa để ở 26–27 độ C vừa mát vừa tiết kiệm điện, tốt cho sức khỏe.\n"
                . "- Tiết kiệm điện giúp giảm chi phí, giảm tải lưới điện và bảo vệ môi trường.\n"
                . "- Tiết kiệm điện là thói quen tốt mỗi học sinh nên rèn luyện.",

            159 => "- Kim loại như đồng, sắt dẫn điện rất tốt.\n"
                . "- Gỗ nhẹ, dễ gia công nên thường dùng làm bàn ghế học sinh.\n"
                . "- Nhựa nhẹ, không thấm nước nên dùng làm chai nước, đồ dùng hằng ngày.\n"
                . "- Tính chất nổi bật của nhựa: nhẹ, cách điện, chống nước.\n"
                . "- Nhận biết tính chất vật liệu giúp chọn đồ dùng phù hợp.",

            160 => "- Compa có chân nhọn cố định và chân chì, dùng để vẽ đường tròn.\n"
                . "- Êke hình tam giác vuông dùng để vẽ và kiểm tra góc vuông.\n"
                . "- Khi đo độ dài, đặt vạch 0 của thước trùng với đầu vật thì số đo mới chính xác.\n"
                . "- Dùng bút chì đánh dấu trước khi cắt vì vạch rõ mà dễ tẩy xóa.\n"
                . "- Đo và đánh dấu chính xác giúp sản phẩm làm ra đúng kích thước.",

            64 => "- Bước đầu tiên khi trồng cây là chuẩn bị đất: xới tơi xốp để rễ dễ phát triển và thoát nước tốt.\n"
                . "- Đất tơi xốp giúp rễ đâm sâu, hút nước và chất dinh dưỡng dễ dàng.\n"
                . "- Sau khi gieo hạt cần tưới nhẹ ngay để hạt có độ ẩm nảy mầm, không làm trôi hạt.\n"
                . "- Trồng cây theo đúng trình tự: chuẩn bị đất, gieo trồng rồi chăm sóc.\n"
                . "- Làm đúng kỹ thuật từng bước giúp cây sinh trưởng tốt.",

            66 => "- Nên tưới nước cho cây vào sáng sớm hoặc chiều mát để cây hút nước tốt, tránh sốc nhiệt giữa trưa.\n"
                . "- Bón quá nhiều phân hóa học làm cháy rễ, cây héo và chết; phải bón đúng liều lượng hướng dẫn.\n"
                . "- Lá héo, rũ xuống là dấu hiệu cây thiếu nước, cần tưới ngay.\n"
                . "- Phân hữu cơ có nguồn gốc tự nhiên như phân chuồng, phân xanh.\n"
                . "- Chăm sóc đúng cách giúp cây khỏe, cho năng suất cao.",

            161 => "- Tua vít chuyên dùng để vặn ốc vít; búa dùng để đóng đinh và gõ.\n"
                . "- Cưa tay có răng cưa thích hợp để cắt gỗ.\n"
                . "- Khi dùng dụng cụ cơ khí phải cầm chắc, dùng đúng cách và đeo đồ bảo hộ.\n"
                . "- Chọn dụng cụ phù hợp với công việc để làm việc hiệu quả.\n"
                . "- An toàn là yêu cầu đầu tiên khi dùng mọi dụng cụ cơ khí.",

            162 => "- Đo đạc kỹ trước khi cắt để tránh lãng phí vật liệu.\n"
                . "- Pin cũ chứa chì, thủy ngân nên là rác nguy hại, vứt bừa bãi gây ô nhiễm đất và nước.\n"
                . "- Giấy, nhựa, kim loại, thủy tinh đều có thể tái chế.\n"
                . "- Phân loại rác và tái chế giúp tiết kiệm tài nguyên, bảo vệ môi trường.\n"
                . "- Dùng vật liệu tiết kiệm là trách nhiệm của mỗi người.",

            163 => "- Giống tốt là nền tảng cho vụ mùa bội thu, cần chọn giống khỏe và gieo đúng thời vụ.\n"
                . "- Đất trồng cây cần tơi xốp để rễ phát triển và thoát nước tốt.\n"
                . "- Gieo trồng đúng thời vụ giúp cây sinh trưởng tốt, ít sâu bệnh.\n"
                . "- Bón lót là bón phân vào đất trước khi gieo trồng để cung cấp dinh dưỡng.\n"
                . "- Chuẩn bị tốt từng bước quyết định năng suất cây trồng.",

            164 => "- Tưới cây vào sáng sớm hoặc chiều mát giúp cây hấp thụ tốt, ít bay hơi.\n"
                . "- Tưới quá nhiều nước gây úng, làm rễ thối, cây héo và chết.\n"
                . "- Phân hữu cơ có nguồn gốc tự nhiên như phân chuồng, phân xanh.\n"
                . "- Lạm dụng phân hóa học làm đất chai cứng và gây ô nhiễm môi trường.\n"
                . "- Tưới nước và bón phân vừa đủ giúp cây phát triển khỏe mạnh.",

            165 => "- Bọ rùa là thiên địch có ích, chuyên ăn rệp gây hại cho cây trồng.\n"
                . "- Bắt sâu bằng tay là biện pháp an toàn nhất, không độc hại, bảo vệ môi trường.\n"
                . "- Sâu ăn lá để lại những lỗ thủng trên phiến lá.\n"
                . "- Thuốc bảo vệ thực vật hóa học phải dùng đúng liều, đúng lúc, đúng cách.\n"
                . "- Ưu tiên biện pháp sinh học và thủ công trước khi dùng thuốc hóa học.",

            166 => "- Nên thu hoạch nông sản vào sáng sớm mát mẻ để tươi lâu, ít dập nát.\n"
                . "- Thóc ướt dễ mốc, mọc mầm nên phải phơi khô trước khi cất trữ.\n"
                . "- Thủy canh là phương pháp trồng cây trong dung dịch dinh dưỡng thay cho đất.\n"
                . "- Tưới nhỏ giọt đưa nước trực tiếp đến rễ, tiết kiệm nước đáng kể.\n"
                . "- Ứng dụng công nghệ mới giúp trồng trọt hiệu quả và bền vững hơn.",

            299 => "- Cường độ dòng điện I đo bằng ampe (A), điện áp đo bằng vôn (V), điện trở đo bằng ôm.\n"
                . "- Vôn kế đo điện áp được mắc song song với đoạn mạch; ampe kế đo dòng điện mắc nối tiếp.\n"
                . "- Định luật Ôm: I = U/R, dòng điện tỉ lệ thuận với điện áp, tỉ lệ nghịch với điện trở.\n"
                . "- Số liệu 220V – 100W là thông số định mức: dùng đúng 220V thì công suất là 100W.\n"
                . "- Nắm vững các đại lượng điện là cơ sở để học mạch điện.",

            300 => "- Mạch mắc nối tiếp chỉ có một đường đi nên dòng điện qua mọi thiết bị bằng nhau.\n"
                . "- Điện trở tương đương mạch nối tiếp: R = R1 + R2, ví dụ 4 ôm + 6 ôm = 10 ôm.\n"
                . "- Các nhánh song song cùng nối vào hai điểm chung nên điện áp trên các nhánh bằng nhau.\n"
                . "- Thiết bị điện trong gia đình mắc song song: mỗi thiết bị đủ 220V, tắt một cái các cái khác vẫn chạy.\n"
                . "- Phân biệt mắc nối tiếp và song song để hiểu cách đấu điện trong nhà.",

            301 => "- Tay ướt dẫn điện tốt, chạm vào thiết bị điện rất dễ bị giật.\n"
                . "- Chim đậu trên dây điện cao thế không bị giật vì không có hiệu điện thế đáng kể qua cơ thể.\n"
                . "- Aptomat tự động ngắt điện khi quá tải, ngắn mạch; loại chống rò còn bảo vệ khi rò điện.\n"
                . "- Khi sửa chữa điện trong nhà, việc đầu tiên phải làm là ngắt điện.\n"
                . "- Hiểu nguyên nhân tai nạn điện để phòng tránh hiệu quả.",

            302 => "- Khi chưa ngắt được điện, tuyệt đối không chạm tay trần vào nạn nhân bị điện giật.\n"
                . "- Số điện thoại cấp cứu y tế là 115; công an là 113; cứu hỏa là 114.\n"
                . "- Nạn nhân ngừng thở cần hô hấp nhân tạo và ép tim càng sớm càng tốt.\n"
                . "- Ép tim ngoài lồng ngực 100–120 lần mỗi phút, ấn sâu khoảng 5cm ở người lớn.\n"
                . "- Sơ cứu đúng và kịp thời có thể cứu sống nạn nhân.",

            303 => "- Bản vẽ kỹ thuật là ngôn ngữ chung giúp người thiết kế và người chế tạo hiểu đúng ý nhau.\n"
                . "- Nét liền đậm vẽ cạnh thấy (đường bao thấy); nét đứt vẽ cạnh khuất.\n"
                . "- Nét chấm gạch mảnh vẽ đường tâm và trục đối xứng.\n"
                . "- Khổ giấy A4 là 210x297mm; A3 là 297x420mm; mỗi khổ sau gấp đôi khổ trước.\n"
                . "- Đọc đúng các loại đường nét là bước đầu để hiểu bản vẽ.",

            304 => "- Phép chiếu vuông góc: tia chiếu vuông góc với mặt phẳng chiếu tạo ra hình chiếu.\n"
                . "- Hình chiếu đứng nhìn vật thể từ phía trước; hình chiếu bằng nhìn từ trên; hình chiếu cạnh nhìn từ bên trái.\n"
                . "- Quy tắc bố trí: hình chiếu bằng nằm dưới hình chiếu đứng, hình chiếu cạnh nằm bên phải hình chiếu đứng.\n"
                . "- Hình chiếu trục đo giúp hình dung vật thể trong không gian dễ dàng hơn.\n"
                . "- Ba hình chiếu phối hợp cho biết đầy đủ hình dạng vật thể.",

            305 => "- Khi đọc bản vẽ chi tiết, đọc khung tên trước để biết tổng quan về chi tiết.\n"
                . "- Kí hiệu đường kính là Ø (ví dụ Ø20), kí hiệu bán kính là R.\n"
                . "- Kích thước định hình cho biết độ lớn của chi tiết; kích thước định vị cho biết vị trí tương đối.\n"
                . "- Con số kích thước ghi phía trên đường kích thước, đơn vị mặc định là mm.\n"
                . "- Đọc bản vẽ đúng trình tự giúp hiểu chính xác yêu cầu chế tạo.",

            306 => "- Bản vẽ lắp thể hiện cách lắp các chi tiết thành sản phẩm hoàn chỉnh.\n"
                . "- Đối chiếu số thứ tự trên hình vẽ với bảng kê để biết từng chi tiết.\n"
                . "- Bản vẽ nhà gồm: mặt bằng cho biết bố trí không gian, mặt đứng cho biết hình dáng bên ngoài, mặt cắt cho biết cấu tạo bên trong.\n"
                . "- Kí hiệu mũi tên chỉ hướng Bắc giúp bố trí phòng ốc đón nắng, tránh nắng hợp lý.\n"
                . "- Đọc được bản vẽ lắp và bản vẽ nhà phục vụ đời sống thực tế.",

            307 => "- Cách mạng công nghiệp lần 1 gắn với máy hơi nước (cuối thế kỉ 18); lần 2 với điện; lần 3 với máy tính; lần 4 với số hóa và AI.\n"
                . "- Cách mạng công nghiệp 4.0 đặc trưng bởi số hóa, kết nối vạn vật (IoT), trí tuệ nhân tạo và robot thông minh.\n"
                . "- Công nghệ giúp sản xuất hiệu quả hơn, tiết kiệm tài nguyên và sức lao động.\n"
                . "- IoT là các thiết bị như tủ lạnh, camera, cảm biến kết nối mạng và hoạt động thông minh.\n"
                . "- Mỗi cuộc cách mạng công nghiệp đều thay đổi mạnh mẽ sản xuất và đời sống.",

            308 => "- Hệ thống tự động gồm: cảm biến thu thập thông tin, bộ điều khiển xử lý, cơ cấu chấp hành thực hiện.\n"
                . "- Bộ điều khiển đóng vai trò bộ não trong hệ thống tự động.\n"
                . "- Robot công nghiệp làm việc chính xác, liên tục trong môi trường nguy hiểm.\n"
                . "- Robot cần bảo trì và đầu tư ban đầu cao nhưng mang lại hiệu quả lâu dài.\n"
                . "- Tự động hóa giúp nâng cao năng suất và giảm lao động nặng nhọc.",

            309 => "- Ô nhiễm không khí chủ yếu do khí thải chứa CO, SO2 và bụi mịn.\n"
                . "- Nước thải chưa qua xử lý mang hóa chất, chất hữu cơ gây ô nhiễm nguồn nước.\n"
                . "- Bụi mịn PM2.5 nhỏ hơn 2,5 micromet, rất nguy hiểm cho sức khỏe.\n"
                . "- Hóa chất nông nghiệp ngấm vào đất, nước và tồn dư trong thực phẩm gây ô nhiễm.\n"
                . "- Nhận biết nguyên nhân ô nhiễm là bước đầu để bảo vệ môi trường.",

            310 => "- Phát triển bền vững là sự hài hòa giữa kinh tế, xã hội và môi trường.\n"
                . "- Phân loại và tái chế rác giúp giảm rác thải, tiết kiệm tài nguyên.\n"
                . "- Năng lượng tái tạo: mặt trời, gió, thủy triều; năng lượng hóa thạch: than, dầu, khí.\n"
                . "- Học sinh góp phần bảo vệ môi trường bằng những hành động nhỏ hằng ngày.\n"
                . "- Bảo vệ môi trường hôm nay là giữ cuộc sống tốt đẹp cho mai sau.",

            // ================= DIA LY =================
            70 => "- Châu Á là châu lục có diện tích lớn nhất thế giới, chiếm khoảng 1/3 diện tích đất liền.\n"
                . "- Châu Á cũng đông dân nhất, chiếm hơn một nửa dân số thế giới.\n"
                . "- Việt Nam nằm ở khu vực Đông Nam Á, thuộc châu Á.\n"
                . "- Mỗi châu lục có đặc điểm nổi bật: châu Phi có Xa-ha-ra, Nam Cực lạnh nhất, Đại Dương nhỏ nhất.\n"
                . "- Nhận biết vị trí và đặc điểm cơ bản của các châu lục trên bản đồ.",

            71 => "- Thái Bình Dương là đại dương lớn nhất, chiếm khoảng 1/3 diện tích bề mặt Trái Đất.\n"
                . "- Bắc Băng Dương nằm quanh Bắc Cực, là đại dương nhỏ nhất và lạnh nhất.\n"
                . "- Biển Đông là biển ven bờ ở phía tây của Thái Bình Dương.\n"
                . "- Mỗi đại dương có diện tích và đặc điểm khí hậu khác nhau.\n"
                . "- Xác định được vị trí các đại dương trên bản đồ thế giới.",

            72 => "- Hà Nội là thủ đô của nước Cộng hòa xã hội chủ nghĩa Việt Nam.\n"
                . "- Lãnh thổ Việt Nam có hình chữ S kéo dài từ bắc xuống nam.\n"
                . "- Phía bắc giáp Trung Quốc, phía tây giáp Lào và Campuchia, phía đông và phía nam giáp Biển Đông.\n"
                . "- Việt Nam giáp biển ở phía đông và phía nam với vùng biển rộng lớn.\n"
                . "- Nắm vị trí địa lí là cơ sở để hiểu đặc điểm tự nhiên, kinh tế đất nước.",

            203 => "- Châu Á nằm chủ yếu ở bán cầu Bắc và bán cầu Đông.\n"
                . "- Dãy Hi-ma-lay-a có đỉnh Ê-vơ-rét cao 8.848m, cao nhất thế giới.\n"
                . "- Dân cư châu Á tập trung đông ở Đông Á (Trung Quốc) và Nam Á (Ấn Độ).\n"
                . "- Hoang mạc Gô-bi ở Trung Á là hoang mạc lớn của châu Á; Xa-ha-ra ở châu Phi.\n"
                . "- Châu Á có địa hình đa dạng và dân cư đông đúc nhất thế giới.",

            204 => "- Châu Đại Dương là châu lục nhỏ nhất, gồm nước Úc và các đảo Thái Bình Dương.\n"
                . "- Châu Nam Cực nằm quanh cực Nam, lạnh nhất Trái Đất, quanh năm bao phủ băng tuyết.\n"
                . "- Hoang mạc Xa-ha-ra ở Bắc Phi là hoang mạc nóng lớn nhất thế giới.\n"
                . "- Dãy An-đét chạy dọc bờ tây Nam Mỹ là dãy núi dài nhất thế giới.\n"
                . "- Mỗi châu lục có những kỷ lục địa lí riêng đáng nhớ.",

            73 => "- Tokyo là thủ đô của Nhật Bản; Bắc Kinh là thủ đô của Trung Quốc.\n"
                . "- Seoul là thủ đô của Hàn Quốc.\n"
                . "- Thủ đô các nước Đông Nam Á: Hà Nội (Việt Nam), Bangkok (Thái Lan), Viêng Chăn (Lào), Phnom Penh (Campuchia).\n"
                . "- Thủ đô là trung tâm chính trị, hành chính của một quốc gia.\n"
                . "- Ghi nhớ thủ đô giúp hiểu biết thêm về các nước láng giềng.",

            74 => "- Paris là thủ đô của Pháp, nổi tiếng với tháp Eiffel.\n"
                . "- Washington D.C. là thủ đô của Mỹ; New York chỉ là thành phố lớn nhất.\n"
                . "- London là thủ đô của Anh.\n"
                . "- Thủ đô các nước châu Âu: Paris (Pháp), Berlin (Đức), Rome (Ý), Mát-xcơ-va (Nga).\n"
                . "- Phân biệt thủ đô với thành phố lớn nhất của một nước.",

            205 => "- Địa hình châu Âu chủ yếu là đồng bằng và núi thấp, thuận lợi cho sinh sống.\n"
                . "- Dòng biển nóng Bắc Đại Tây Dương làm khí hậu Tây Âu ôn hòa, ấm áp.\n"
                . "- Dãy An-pơ ở Nam Âu là dãy núi trẻ, cao, có nhiều đỉnh tuyết phủ.\n"
                . "- EU là liên minh kinh tế, chính trị gồm nhiều nước châu Âu.\n"
                . "- Thiên nhiên và khí hậu thuận lợi giúp châu Âu phát triển sớm.",

            206 => "- Sông Nin ở châu Phi dài khoảng 6.650km, là sông dài nhất thế giới.\n"
                . "- Rừng A-ma-dôn ở Nam Mỹ được mệnh danh là lá phổi xanh của Trái Đất.\n"
                . "- Kênh đào Pa-na-ma nối Thái Bình Dương với Đại Tây Dương, rút ngắn đường biển.\n"
                . "- Hoa Kì là nền kinh tế lớn nhất châu Mỹ và hàng đầu thế giới.\n"
                . "- Châu Phi và châu Mỹ có nhiều kỷ lục tự nhiên thế giới.",

            207 => "- Viêng Chăn là thủ đô của Lào, nước láng giềng phía tây Việt Nam.\n"
                . "- Phnom Penh là thủ đô của Campuchia.\n"
                . "- Bangkok là thủ đô và thành phố lớn nhất Thái Lan.\n"
                . "- Jakarta trên đảo Java là thủ đô của Indonesia.\n"
                . "- Ghi nhớ thủ đô các nước Đông Nam Á, khu vực của Việt Nam.",

            208 => "- Tokyo là thủ đô của Nhật Bản; Bắc Kinh là thủ đô của Trung Quốc.\n"
                . "- Seoul là thủ đô của Hàn Quốc; New Delhi là thủ đô của Ấn Độ.\n"
                . "- Thượng Hải là thành phố lớn nhất Trung Quốc nhưng không phải thủ đô; Mumbai cũng vậy với Ấn Độ.\n"
                . "- Thủ đô là nơi đặt cơ quan đầu não của quốc gia.\n"
                . "- Phân biệt thủ đô với thành phố lớn nhất của một nước.",

            75 => "- Dãy Hoàng Liên Sơn ở Tây Bắc có đỉnh Fansipan cao 3.143m, được gọi là nóc nhà Đông Dương.\n"
                . "- Đồng bằng sông Cửu Long là đồng bằng châu thổ lớn nhất nước ta.\n"
                . "- Sông Hồng bồi đắp đồng bằng Bắc Bộ; sông Mê Công bồi đắp đồng bằng Nam Bộ.\n"
                . "- Địa hình Việt Nam đa dạng: vùng núi, trung du, đồng bằng và ven biển.\n"
                . "- Sông ngòi và địa hình gắn bó chặt chẽ, tạo nên đồng bằng màu mỡ.",

            76 => "- Việt Nam có khí hậu nhiệt đới gió mùa: nóng ẩm, mưa nhiều.\n"
                . "- Miền Nam có hai mùa rõ rệt: mùa mưa và mùa khô.\n"
                . "- Gió mùa đông bắc mang không khí lạnh khiến miền Bắc có mùa đông lạnh.\n"
                . "- Khí hậu phân hóa theo miền: miền Bắc có đông lạnh, miền Trung mưa lệch mùa, miền Nam hai mùa.\n"
                . "- Khí hậu ảnh hưởng lớn đến sản xuất nông nghiệp và đời sống.",

            209 => "- Paris là thủ đô của Pháp, nổi tiếng với tháp Eiffel.\n"
                . "- Berlin là thủ đô của Đức.\n"
                . "- Rome là thủ đô của Ý, thành phố có lịch sử hàng nghìn năm.\n"
                . "- Mát-xcơ-va (Moscow) là thủ đô của Liên bang Nga.\n"
                . "- Các thủ đô châu Âu thường là trung tâm văn hóa, lịch sử lâu đời.",

            210 => "- Washington D.C. là thủ đô Hoa Kì; Ottawa là thủ đô Ca-na-đa.\n"
                . "- Canberra là thủ đô Ô-xtrây-li-a (Úc); Sydney, Melbourne chỉ là thành phố lớn.\n"
                . "- Cairo là thủ đô Ai Cập, thành phố lớn nhất châu Phi.\n"
                . "- Nhiều nước có thủ đô không phải là thành phố lớn nhất.\n"
                . "- Ghi nhớ thủ đô các châu lục để mở rộng hiểu biết thế giới.",

            211 => "- 3/4 diện tích Việt Nam là đồi núi, 1/4 là đồng bằng.\n"
                . "- Dãy Hoàng Liên Sơn ở Tây Bắc có đỉnh Fansipan, nóc nhà Đông Dương.\n"
                . "- Vùng núi Tây Bắc có núi cao nhất nước; vùng Đông Bắc có địa hình cánh cung đặc trưng.\n"
                . "- Trung du Bắc Bộ có đất feralit thích hợp trồng chè và cây ăn quả.\n"
                . "- Đồi núi là đặc trưng nổi bật của địa hình Việt Nam.",

            212 => "- Đồng bằng sông Cửu Long rộng khoảng 40.000km2, là đồng bằng châu thổ lớn nhất nước.\n"
                . "- Đường bờ biển Việt Nam dài hơn 3.260km, từ Quảng Ninh đến Kiên Giang.\n"
                . "- Hoàng Sa (Đà Nẵng) và Trường Sa (Khánh Hòa) là hai quần đảo xa bờ thiêng liêng của Tổ quốc.\n"
                . "- Vùng biển Việt Nam giàu thủy sản, dầu khí và tiềm năng du lịch.\n"
                . "- Đồng bằng và biển là hai thế mạnh lớn của đất nước.",

            213 => "- Than đá tập trung chủ yếu ở Quảng Ninh với trữ lượng lớn.\n"
                . "- Dầu khí phân bố ở thềm lục địa phía Nam với các mỏ Bạch Hổ, Đại Hùng.\n"
                . "- Tây Nguyên có trữ lượng bô-xít rất lớn, là nguyên liệu sản xuất nhôm.\n"
                . "- Khoáng sản là tài nguyên không tái tạo nên phải khai thác hợp lý, tiết kiệm.\n"
                . "- Việt Nam giàu tài nguyên khoáng sản nhưng cần sử dụng bền vững.",

            214 => "- Rừng Việt Nam bị thu hẹp do khai thác quá mức và đốt nương làm rẫy.\n"
                . "- Trồng rừng và bảo vệ rừng đầu nguồn là biện pháp bảo vệ rừng quan trọng.\n"
                . "- Biến đổi khí hậu gây nước biển dâng, đe dọa đồng bằng sông Cửu Long; bão lũ diễn biến phức tạp.\n"
                . "- Mỗi học sinh có thể góp phần bảo vệ môi trường bằng cách tiết kiệm tài nguyên và trồng cây.\n"
                . "- Bảo vệ tài nguyên, môi trường là trách nhiệm của mọi người.",

            335 => "- Thứ tự các hành tinh từ Mặt Trời: Thủy, Kim, Trái Đất, Hỏa, Mộc, Thổ, Thiên Vương, Hải Vương.\n"
                . "- Trái Đất tự quay quanh trục một vòng hết 24 giờ, tạo ra hiện tượng ngày và đêm.\n"
                . "- Trái Đất chuyển động quanh Mặt Trời một vòng hết 365,25 ngày, tạo ra các mùa trong năm.\n"
                . "- Nửa Trái Đất được Mặt Trời chiếu sáng là ban ngày, nửa khuất là ban đêm.\n"
                . "- Hai chuyển động của Trái Đất giải thích ngày đêm và các mùa.",

            336 => "- Vỏ lục địa dày 30–70km, dày hơn nhiều so với vỏ đại dương chỉ 5–10km.\n"
                . "- Thạch quyển là lớp đá cứng ngoài cùng của Trái Đất, dày khoảng 100km.\n"
                . "- Nội lực là lực phát sinh từ năng lượng bên trong Trái Đất.\n"
                . "- Động đất, núi lửa và hiện tượng uốn nếp là kết quả của nội lực.\n"
                . "- Vận động của thạch quyển không ngừng làm thay đổi bề mặt Trái Đất.",

            337 => "- Các hiện tượng thời tiết như mưa, bão xảy ra ở tầng đối lưu (0–16km).\n"
                . "- Cứ lên cao 100m, nhiệt độ không khí giảm khoảng 0,6 độ C.\n"
                . "- Gió là sự chuyển động của không khí từ nơi áp cao về nơi áp thấp.\n"
                . "- Gió Tín phong (mậu dịch) thổi thường xuyên từ áp cao cận chí tuyến về áp thấp xích đạo.\n"
                . "- Nhiệt độ, khí áp và gió là ba yếu tố cơ bản của khí hậu.",

            338 => "- Sinh quyển gồm toàn bộ sinh vật và môi trường sống của chúng trên Trái Đất.\n"
                . "- Thảm thực vật đặc trưng của đới nóng là rừng nhiệt đới xanh quanh năm, nhiều tầng tán.\n"
                . "- Hoang mạc Xa-ha-ra ở Bắc Phi là hoang mạc nhiệt đới lớn nhất thế giới.\n"
                . "- Quy luật địa đới: thiên nhiên thay đổi theo vĩ độ do lượng bức xạ Mặt Trời thay đổi.\n"
                . "- Khí hậu quyết định sự phân bố sinh vật trên Trái Đất.",

            339 => "- Tháng 11/2022, dân số thế giới chính thức cán mốc 8 tỉ người.\n"
                . "- Châu Á đông dân nhất, chiếm khoảng 60% dân số thế giới.\n"
                . "- Tỉ suất gia tăng dân số tự nhiên phản ánh sự chênh lệch giữa sinh và tử.\n"
                . "- Năm 2023, Ấn Độ đã vượt Trung Quốc trở thành nước đông dân nhất thế giới.\n"
                . "- Quy mô và gia tăng dân số ảnh hưởng lớn đến phát triển kinh tế, xã hội.",

            340 => "- Tháp dân số thể hiện cơ cấu tuổi: hình dạng tháp cho biết dân số trẻ, già hay ổn định.\n"
                . "- Nhật Bản là quốc gia già hóa dân số nhanh nhất thế giới, thiếu lao động trẻ.\n"
                . "- Đô thị hóa hút lao động từ nông thôn vào thành phố.\n"
                . "- Siêu đô thị là đô thị có dân số rất lớn, ví dụ Tokyo, Delhi, Thượng Hải.\n"
                . "- Cơ cấu dân số và đô thị hóa đặt ra nhiều thách thức cho các quốc gia.",

            341 => "- Châu Á gió mùa chiếm khoảng 90% sản lượng lúa gạo thế giới.\n"
                . "- Trung Quốc dẫn đầu thế giới về sản lượng lúa mì; Nga dẫn đầu về xuất khẩu lúa mì.\n"
                . "- Cách mạng công nghiệp lần thứ tư gắn với IoT, AI, dữ liệu lớn và robot.\n"
                . "- Công nghiệp nặng được coi là quả đấm thép của nền kinh tế, cung cấp tư liệu sản xuất.\n"
                . "- Nông nghiệp và công nghiệp là hai ngành kinh tế vật chất cơ bản.",

            342 => "- Ở các nước phát triển, ngành dịch vụ chiếm trên 70% GDP.\n"
                . "- Toàn cầu hóa biểu hiện rõ nét nhất trong tự do hóa thương mại và đầu tư.\n"
                . "- WTO (Tổ chức Thương mại Thế giới) thành lập năm 1995, điều tiết thương mại toàn cầu.\n"
                . "- Công ty xuyên quốc gia là chủ thể thúc đẩy toàn cầu hóa kinh tế.\n"
                . "- Dịch vụ và toàn cầu hóa đang định hình nền kinh tế thế giới hiện đại.",

            343 => "- Việt Nam rộng khoảng 331.212km2, đứng thứ 65 thế giới về diện tích.\n"
                . "- Đường bờ biển Việt Nam dài khoảng 3.260km, từ Móng Cái đến Hà Tiên.\n"
                . "- Đỉnh Phan-xi-păng cao 3.143m, được mệnh danh là nóc nhà Đông Dương.\n"
                . "- Địa hình Việt Nam: đồi núi chiếm 3/4, đồng bằng chỉ 1/4 diện tích.\n"
                . "- Vị trí và địa hình tạo nên đặc điểm tự nhiên riêng của Việt Nam.",

            344 => "- Khí hậu Việt Nam nóng ẩm quanh năm, mưa theo mùa, chịu ảnh hưởng gió mùa châu Á.\n"
                . "- Sông Mê Công dài 4.350km; đoạn chảy qua Việt Nam gọi là sông Tiền, sông Hậu.\n"
                . "- Đất feralit chiếm khoảng 65% diện tích, phân bố chủ yếu ở vùng đồi núi.\n"
                . "- Mùa mưa ở miền Bắc trùng với mùa gió tây nam nóng ẩm.\n"
                . "- Khí hậu, sông ngòi và đất đai là điều kiện quan trọng cho nông nghiệp.",

            345 => "- Đồng bằng sông Cửu Long là vựa lúa lớn nhất, chiếm hơn 50% sản lượng lúa cả nước.\n"
                . "- Việt Nam là nước xuất khẩu cà phê lớn thứ hai thế giới, chỉ sau Bra-xin.\n"
                . "- Đất bazan Tây Nguyên rất thích hợp trồng cà phê và cây công nghiệp lâu năm.\n"
                . "- Đồng bằng sông Cửu Long cũng là vùng nuôi trồng thủy sản lớn nhất cả nước với cá tra, tôm sú.\n"
                . "- Nông nghiệp là thế mạnh truyền thống và hiện đại của Việt Nam.",

            346 => "- Dầu khí là ngành công nghiệp trọng điểm, đóng góp lớn vào ngân sách và xuất khẩu.\n"
                . "- TP.HCM và vùng Đông Nam Bộ là trung tâm công nghiệp lớn nhất cả nước.\n"
                . "- Du lịch biển là thế mạnh nổi bật với đường bờ biển dài và nhiều bãi biển đẹp.\n"
                . "- Cụm cảng TP.HCM là cảng biển lớn nhất, cửa ngõ xuất nhập khẩu của cả nước.\n"
                . "- Công nghiệp và dịch vụ đang chuyển dịch cơ cấu kinh tế Việt Nam.",

            // ================= GDCD =================
            83 => "- Trẻ em có quyền được giáo dục, tức là được đi học.\n"
                . "- Trẻ em có quyền được chăm sóc sức khỏe: khám chữa bệnh, tiêm phòng đầy đủ.\n"
                . "- Khi bị xâm hại hoặc gặp nguy hiểm, báo ngay cho người lớn tin cậy hoặc gọi tổng đài 111.\n"
                . "- Các quyền cơ bản của trẻ em: được sống, được giáo dục, được vui chơi giải trí.\n"
                . "- Biết quyền của mình để tự bảo vệ bản thân tốt hơn.",

            84 => "- Trẻ em có bổn phận kính trọng, hiếu thảo với ông bà, cha mẹ.\n"
                . "- Học sinh phải lễ phép, tôn trọng và vâng lời thầy cô giáo.\n"
                . "- Trẻ em cần yêu quê hương đất nước, chăm học để sau này xây dựng đất nước.\n"
                . "- Đối với bạn bè, trẻ em cần đoàn kết, giúp đỡ lẫn nhau.\n"
                . "- Quyền đi đôi với bổn phận: hưởng quyền phải làm tròn bổn phận.",

            85 => "- Đi học đúng giờ là biểu hiện cơ bản của tôn trọng kỷ luật.\n"
                . "- Xếp hàng ngay ngắn, không chen lấn là giữ kỷ luật nơi công cộng.\n"
                . "- Kỷ luật giúp tập thể hoạt động nền nếp, đạt hiệu quả cao.\n"
                . "- Tôn trọng kỷ luật thể hiện ở việc chấp hành thời gian, nội quy trường lớp và nơi công cộng.\n"
                . "- Người có kỷ luật được mọi người tin tưởng, quý mến.",

            131 => "- Trẻ em có 4 nhóm quyền cơ bản: sống còn, phát triển, bảo vệ và tham gia.\n"
                . "- Quyền được đi học thuộc nhóm quyền phát triển.\n"
                . "- Quyền được bày tỏ ý kiến thuộc nhóm quyền tham gia.\n"
                . "- Quyền được bảo vệ: trẻ em được bảo vệ khỏi mọi hình thức bạo lực, xâm hại.\n"
                . "- Nhớ 4 nhóm quyền để biết mình được bảo vệ như thế nào.",

            132 => "- Học tập là bổn phận hàng đầu của học sinh.\n"
                . "- Giúp đỡ cha mẹ việc nhà là biểu hiện của lòng hiếu thảo.\n"
                . "- Yêu quê hương, đất nước là bổn phận thiêng liêng của mỗi trẻ em Việt Nam.\n"
                . "- Kính trọng người lớn tuổi là nét đẹp văn hóa của người Việt Nam.\n"
                . "- Làm tròn bổn phận là cách thể hiện lòng biết ơn với gia đình và đất nước.",

            86 => "- Tắt thiết bị điện khi không dùng là cách tiết kiệm điện đơn giản.\n"
                . "- Bỏ ống heo là cách tiết kiệm tiền đơn giản và hiệu quả.\n"
                . "- Tài nguyên thiên nhiên có hạn nên phải sử dụng hợp lý, tránh lãng phí.\n"
                . "- Tiết kiệm thể hiện trong việc dùng điện, nước và thời gian hằng ngày.\n"
                . "- Tiết kiệm là đức tính tốt cần rèn luyện từ nhỏ.",

            87 => "- Tình bạn đẹp xây dựng trên sự chân thành và giúp đỡ lẫn nhau.\n"
                . "- Khi bạn mắc lỗi, góp ý chân thành để bạn sửa lỗi, không chê cười hay bỏ mặc.\n"
                . "- Nói xấu sau lưng làm mất lòng tin, phá hoại tình bạn.\n"
                . "- Chân thành, giúp đỡ, bao dung là nền tảng của tình bạn đẹp.\n"
                . "- Giữ gìn tình bạn đẹp làm cuộc sống học trò thêm ý nghĩa.",

            133 => "- Đánh đập trẻ em là bạo lực thể chất, bị pháp luật nghiêm cấm.\n"
                . "- Khi bị người lạ dụ dỗ đi theo, hãy hét to và chạy đến nơi đông người.\n"
                . "- Tổng đài quốc gia bảo vệ trẻ em là 111.\n"
                . "- Khi gặp nguy hiểm, kể ngay với cha mẹ hoặc thầy cô tin cậy để được giúp đỡ.\n"
                . "- Biết tự bảo vệ mình là kỹ năng quan trọng của mỗi trẻ em.",

            134 => "- Gia đình là nơi đầu tiên có trách nhiệm nuôi dưỡng và bảo vệ trẻ em.\n"
                . "- Mọi hình thức đánh đập, xúc phạm học sinh đều bị pháp luật nghiêm cấm.\n"
                . "- Quyền tham gia của trẻ em thể hiện qua việc được bày tỏ ý kiến về những việc liên quan đến mình.\n"
                . "- Thấy bạn bị bạo hành, báo người lớn là cách giúp bạn an toàn và đúng đắn nhất.\n"
                . "- Bảo vệ trẻ em là trách nhiệm của gia đình, nhà trường và xã hội.",

            135 => "- Khi tức giận, hít thở sâu giúp bình tĩnh lại trước khi hành động.\n"
                . "- Đang giận mà sắp nói thì đếm đến 10 để tránh nói lời làm tổn thương người khác.\n"
                . "- Vui vẻ là cảm xúc tích cực, giúp khỏe mạnh và học tốt.\n"
                . "- Khi buồn bã, chia sẻ với người tin cậy giúp nhẹ lòng và tìm được cách giải quyết.\n"
                . "- Quản lý cảm xúc tốt giúp các mối quan hệ hài hòa hơn.",

            136 => "- Được giúp đỡ thì nói lời cảm ơn để thể hiện sự trân trọng và lịch sự.\n"
                . "- Làm sai thì dám nhận lỗi và xin lỗi là biểu hiện của người dũng cảm.\n"
                . "- Lắng nghe chăm chú, không ngắt lời là tôn trọng người đang nói.\n"
                . "- Gặp thầy cô phải chào hỏi lễ phép, thể hiện sự kính trọng.\n"
                . "- Giao tiếp lịch sự giúp ta được mọi người yêu quý.",

            88 => "- Địa chỉ nhà, số điện thoại là thông tin cá nhân, không chia sẻ công khai trên mạng.\n"
                . "- Tuyệt đối không gặp riêng người lạ quen qua mạng; báo ngay cho cha mẹ.\n"
                . "- Bị bắt nạt trên mạng: lưu bằng chứng, chặn kẻ bắt nạt và báo cha mẹ, thầy cô.\n"
                . "- Mật khẩu giữ kín; cảnh giác với người lạ xin thông tin, ảnh riêng tư.\n"
                . "- An toàn trên mạng bắt đầu từ việc bảo vệ thông tin cá nhân.",

            89 => "- Bỏ rác đúng nơi quy định giữ đường phố, trường học sạch đẹp.\n"
                . "- Dùng bình nước cá nhân, làn đi chợ giúp giảm rác thải nhựa.\n"
                . "- Cây xanh làm sạch không khí, cho bóng mát và chống xói mòn đất.\n"
                . "- Những việc nhỏ mỗi ngày của mỗi người góp phần bảo vệ môi trường lớn.\n"
                . "- Bảo vệ môi trường là bảo vệ cuộc sống của chính chúng ta.",

            137 => "- Việc đầu tiên khi quản lý thời gian là liệt kê các việc cần làm.\n"
                . "- Ưu tiên làm trước những việc quan trọng và gấp.\n"
                . "- Kẻ cắp thời gian lớn nhất của học sinh hiện nay là lướt điện thoại vô bổ.\n"
                . "- Thời gian biểu tốt cân bằng giữa học tập, nghỉ ngơi và vui chơi.\n"
                . "- Quản lý thời gian tốt giúp học tập hiệu quả mà vẫn vui chơi.",

            138 => "- Khi mâu thuẫn với bạn, điều đầu tiên là giữ bình tĩnh.\n"
                . "- Nói về cảm xúc của mình thay vì đổ lỗi giúp đối phương dễ lắng nghe.\n"
                . "- Không tự hòa giải được thì nhờ thầy cô giúp đỡ.\n"
                . "- Lắng nghe giúp hiểu bạn nghĩ gì, từ đó dễ tìm tiếng nói chung.\n"
                . "- Giải quyết mâu thuẫn trong hòa bình giữ được tình bạn.",

            139 => "- Địa chỉ nhà là thông tin nhạy cảm, không đăng công khai lên mạng.\n"
                . "- Mật khẩu mạnh phải dài và có đủ chữ hoa, chữ thường, số, ký tự đặc biệt.\n"
                . "- Tuyệt đối không gửi ảnh riêng tư cho người lạ; kể ngay với cha mẹ.\n"
                . "- Để tài khoản mạng xã hội ở chế độ riêng tư để kiểm soát người xem thông tin.\n"
                . "- Kiểm tra cài đặt riêng tư thường xuyên để bảo vệ tài khoản.",

            140 => "- Tin giả thường có tiêu đề giật gân để câu lượt xem.\n"
                . "- Trước khi chia sẻ tin giật gân phải kiểm chứng nguồn tin.\n"
                . "- Báo chí chính thống có quy trình kiểm chứng nên đáng tin cậy hơn.\n"
                . "- Lan truyền tin giả gây hoang mang dư luận và có thể bị xử phạt theo pháp luật.\n"
                . "- Là người dùng mạng thông minh: đọc kỹ, kiểm chứng rồi mới chia sẻ.",

            141 => "- Tin nhắn trúng thưởng, bấm link nhận quà hầu hết là lừa đảo.\n"
                . "- Mã OTP là chìa khóa tài khoản, không bao giờ cung cấp cho bất kỳ ai.\n"
                . "- Việc nhẹ lương cao nhưng yêu cầu chuyển khoản đặt cọc trước là dấu hiệu lừa đảo.\n"
                . "- Nghi ngờ bị lừa đảo: chặn, báo cáo tài khoản đó và kể ngay với người lớn.\n"
                . "- Cảnh giác với món lợi bất ngờ trên mạng để không sập bẫy.",

            142 => "- Xả thải chưa qua xử lý ra môi trường là hành vi bị pháp luật nghiêm cấm và xử phạt.\n"
                . "- Biến đổi khí hậu gây thời tiết cực đoan: lũ lụt, hạn hán, nước biển dâng.\n"
                . "- Dùng túi vải nhiều lần giúp giảm đáng kể rác thải nhựa.\n"
                . "- Tiết kiệm điện, tiết kiệm tài nguyên là trách nhiệm của mỗi người.\n"
                . "- Bảo vệ môi trường vừa là đạo đức vừa là nghĩa vụ pháp luật.",

            275 => "- Pháp luật là hệ thống quy tắc xử sự chung do Nhà nước ban hành, có tính bắt buộc.\n"
                . "- Ba đặc trưng cơ bản: tính quy phạm phổ biến, tính xác định chặt chẽ về hình thức, được bảo đảm thực hiện bằng quyền lực Nhà nước.\n"
                . "- Pháp luật được bảo đảm thực hiện bằng biện pháp cưỡng chế như xử phạt hành chính, truy tố, xét xử.\n"
                . "- Ở Việt Nam, văn bản quy phạm pháp luật (Hiến pháp, luật, nghị định) là nguồn chính của pháp luật.\n"
                . "- Hiểu pháp luật để sống và làm việc theo pháp luật.",

            276 => "- Bản chất giai cấp: pháp luật là ý chí của giai cấp cầm quyền được nâng lên thành luật.\n"
                . "- Bản chất xã hội: quy phạm pháp luật bắt nguồn từ thực tiễn đời sống, điều chỉnh các quan hệ xã hội.\n"
                . "- Pháp luật là phương tiện để Nhà nước quản lý mọi mặt đời sống xã hội một cách thống nhất, hiệu quả.\n"
                . "- Pháp luật ghi nhận, bảo vệ quyền cơ bản của công dân và quy định nghĩa vụ công dân phải thực hiện.\n"
                . "- Pháp luật vừa quản lý xã hội vừa bảo vệ quyền con người.",

            277 => "- Vi phạm pháp luật phải đủ 4 dấu hiệu: trái pháp luật, có lỗi, do người có năng lực trách nhiệm pháp lý thực hiện, xâm hại quan hệ xã hội được pháp luật bảo vệ.\n"
                . "- Vượt đèn đỏ là vi phạm hành chính (xâm phạm quy tắc quản lý nhà nước).\n"
                . "- Vi phạm hợp đồng thuê nhà là vi phạm dân sự (xâm phạm quan hệ tài sản).\n"
                . "- Giáo viên bỏ tiết là vi phạm kỷ luật (vi phạm nội quy, quy chế cơ quan).\n"
                . "- Phân loại vi phạm giúp xác định hình thức xử lý phù hợp.",

            278 => "- Trách nhiệm pháp lý là nghĩa vụ gánh chịu hậu quả bất lợi theo quy định của pháp luật.\n"
                . "- Trộm cắp tài sản là tội phạm nên phải chịu trách nhiệm hình sự.\n"
                . "- Vi phạm hợp đồng mua bán chủ yếu phải bồi thường thiệt hại (trách nhiệm dân sự).\n"
                . "- Từ đủ 16 tuổi phải chịu trách nhiệm hình sự về mọi tội phạm; từ đủ 14 đến dưới 16 tuổi chỉ chịu về tội rất nghiêm trọng, đặc biệt nghiêm trọng.\n"
                . "- Ai vi phạm pháp luật đều phải chịu trách nhiệm tương ứng.",

            279 => "- Sản xuất của cải vật chất tạo ra tư liệu sinh hoạt, là cơ sở cho mọi hoạt động khác của xã hội.\n"
                . "- Ba yếu tố cơ bản của quá trình sản xuất: sức lao động, đối tượng lao động, tư liệu lao động.\n"
                . "- Công cụ lao động là yếu tố quan trọng nhất trong tư liệu lao động, thể hiện trình độ phát triển sản xuất.\n"
                . "- Đối tượng lao động là những gì con người tác động vào trong sản xuất như đất đai, nguyên liệu.\n"
                . "- Hiểu quá trình sản xuất để thấy vai trò của lao động con người.",

            280 => "- Tiêu dùng là khâu cuối của quá trình tái sản xuất: sử dụng sản phẩm để thỏa mãn nhu cầu.\n"
                . "- Văn hóa tiêu dùng là ứng xử văn minh: hợp lý, tiết kiệm, bảo vệ môi trường, tôn trọng người khác.\n"
                . "- Người tiêu dùng có quyền được an toàn, được thông tin đầy đủ, trung thực.\n"
                . "- Người tiêu dùng có nghĩa vụ tìm hiểu thông tin, sử dụng đúng hướng dẫn và bảo vệ môi trường.\n"
                . "- Tiêu dùng văn minh vừa tốt cho mình vừa tốt cho xã hội.",

            281 => "- Thị trường theo nghĩa rộng là tổng hòa các quan hệ mua bán, trao đổi hàng hóa, dịch vụ.\n"
                . "- Chức năng thừa nhận: hàng hóa chỉ thực sự có giá trị khi được thị trường thừa nhận (có người mua).\n"
                . "- Quy luật cung cầu: cung vượt cầu thì giá giảm; cầu vượt cung thì giá tăng.\n"
                . "- Chức năng thông tin: thông tin về cung, cầu, giá cả giúp người sản xuất điều chỉnh kịp thời.\n"
                . "- Thị trường là cầu nối giữa sản xuất và tiêu dùng.",

            282 => "- Cạnh tranh là động lực phát triển của nền kinh tế thị trường.\n"
                . "- Cạnh tranh lành mạnh thúc đẩy tiến bộ kỹ thuật, mang lại lợi ích cho người tiêu dùng.\n"
                . "- Quảng cáo sai sự thật là hành vi cạnh tranh không lành mạnh, bị pháp luật cấm.\n"
                . "- Pháp luật bảo đảm cạnh tranh lành mạnh, chống hàng giả và gian lận thương mại.\n"
                . "- Cạnh tranh lành mạnh tốt cho cả doanh nghiệp và người tiêu dùng.",

            283 => "- Bình đẳng trước pháp luật: mọi công dân đều bình đẳng về quyền và nghĩa vụ, không phân biệt.\n"
                . "- Bình đẳng trong lao động: mọi công dân bình đẳng trong thực hiện quyền lao động, không phân biệt giới tính.\n"
                . "- Bình đẳng trong kinh doanh: tự do kinh doanh trong khuôn khổ pháp luật; ngành nghề cấm thì không được kinh doanh.\n"
                . "- Bình đẳng trong hôn nhân và gia đình: vợ chồng bình đẳng, con cái không phân biệt trai gái.\n"
                . "- Bình đẳng là quyền cơ bản và nguyên tắc nền tảng của pháp luật.",

            284 => "- Công dân đủ 18 tuổi trở lên có quyền bầu cử.\n"
                . "- Công dân từ đủ 21 tuổi trở lên có quyền ứng cử vào Quốc hội, Hội đồng nhân dân.\n"
                . "- Khiếu nại là đề nghị xem xét lại quyết định xâm phạm quyền lợi của mình; tố cáo là báo hành vi vi phạm pháp luật gây thiệt hại chung.\n"
                . "- Pháp luật nghiêm cấm lợi dụng khiếu nại, tố cáo để vu khống, xúc phạm người khác.\n"
                . "- Thực hiện quyền dân chủ phải đúng pháp luật và có trách nhiệm.",

            285 => "- Tuân thủ pháp luật là nghĩa vụ của công dân và biểu hiện của nếp sống văn minh.\n"
                . "- Thuế là nguồn thu chủ yếu của ngân sách nhà nước, dùng để xây dựng đất nước.\n"
                . "- Thuế giá trị gia tăng đánh vào giá trị tăng thêm của hàng hóa, dịch vụ; người tiêu dùng cuối cùng chịu thuế này.\n"
                . "- Trốn thuế là vi phạm pháp luật, tùy mức độ bị phạt tiền hoặc phạt tù.\n"
                . "- Nộp thuế đầy đủ là nghĩa vụ và cũng là đóng góp cho đất nước.",

            286 => "- Hiến pháp khẳng định bảo vệ Tổ quốc là nghĩa vụ thiêng liêng và quyền cao quý của công dân.\n"
                . "- Luật Nghĩa vụ quân sự quy định độ tuổi gọi nhập ngũ thực hiện nghĩa vụ quân sự.\n"
                . "- Thời hạn phục vụ tại ngũ trong thời bình của hạ sĩ quan, binh sĩ là 24 tháng.\n"
                . "- Tố giác hành vi xâm phạm an ninh quốc gia là góp phần bảo vệ Tổ quốc.\n"
                . "- Mỗi công dân đều có trách nhiệm với sự bình yên của đất nước.",
        ];

        $updated = 0;
        foreach ($summaries as $lessonId => $summary) {
            $updated += DB::table('lessons')
                ->where('id', $lessonId)
                ->where(function ($q) {
                    $q->whereNull('summary')->orWhere('summary', '');
                })
                ->update(['summary' => $summary]);
        }

        $this->command->info("LessonSummaryGroupCSeeder: {$updated} lesson summaries written.");
    }
}
