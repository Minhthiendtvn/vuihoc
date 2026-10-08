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
 * Môn Tin học — nội dung TỰ VIẾT 100% TIẾNG VIỆT, bám chương trình phổ thông.
 * 3 chủ đề (lớp 6-7 / 7-8 / 8-9), 6 kỹ năng, 6 bài học, mỗi bài 12 câu
 * (3 quiz + 3 matching + 3 sort + 3 fill) = 72 câu.
 * Idempotent: updateOrCreate theo slug; câu hỏi kiểm tra exists theo
 * (lesson_id, game_type) trước khi seed.
 */
class SubjectTinHocSeeder extends Seeder
{
    private array $lessonBySlug = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(
            ['slug' => 'tin-hoc'],
            [
                'name' => 'Tin học', 'icon' => '💻', 'color' => '#6366f1',
                'description' => 'Học tin học qua game: phần cứng, phần mềm và an toàn thông tin.',
                'sort_order' => 9, 'is_published' => true, 'is_demo' => true,
            ]
        );

        foreach ($this->tree() as $t) {
            $topic = Topic::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'subject_id' => $subject->id,
                    'name' => $t['name'], 'description' => $t['description'], 'icon' => $t['icon'],
                    'sort_order' => $t['sort_order'],
                    'grade_min' => $t['grade_min'], 'grade_max' => $t['grade_max'],
                    'is_published' => true, 'is_demo' => true,
                ]
            );
            foreach ($t['skills'] as $sk) {
                $skill = Skill::updateOrCreate(
                    ['slug' => $sk['slug']],
                    [
                        'topic_id' => $topic->id,
                        'name' => $sk['name'], 'description' => $sk['description'],
                        'sort_order' => $sk['sort_order'], 'is_demo' => true,
                    ]
                );
                $order = 1;
                foreach ($sk['lessons'] as $l) {
                    $lesson = Lesson::updateOrCreate(
                        ['slug' => $l['slug']],
                        [
                            'skill_id' => $skill->id,
                            'title' => $l['title'], 'objective' => $l['objective'],
                            'difficulty' => $l['difficulty'], 'duration_minutes' => $l['duration_minutes'],
                            'instructions' => $l['instructions'],
                            'sort_order' => $order++, 'status' => 'published', 'is_demo' => true,
                        ]
                    );
                    $this->lessonBySlug[$l['slug']] = $lesson;
                }
            }
        }

        foreach ($this->questions() as $lessonSlug => $byType) {
            $this->seedLessonQuestions($lessonSlug, $byType);
        }
    }

    private function tree(): array
    {
        return [
            [
                'name' => 'Phần cứng máy tính', 'slug' => 'th-phan-cung-may-tinh',
                'description' => 'Nhận biết các bộ phận của máy tính và chức năng của từng bộ phận.',
                'icon' => '🖥️', 'sort_order' => 1, 'grade_min' => 6, 'grade_max' => 7,
                'skills' => [
                    [
                        'name' => 'Nhận biết phần cứng', 'slug' => 'kn-th-phan-cung-1',
                        'description' => 'Kể tên được các bộ phận chính của máy tính.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Các bộ phận của máy tính', 'slug' => 'th-cac-bo-phan-may-tinh',
                                'objective' => 'Kể tên và nhận biết được các bộ phận chính của máy tính: CPU, RAM, màn hình, bàn phím, chuột.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Quan sát tên gọi và chức năng của từng bộ phận. Phân biệt thiết bị vào, thiết bị ra và bộ phận xử lý.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Chức năng phần cứng', 'slug' => 'kn-th-phan-cung-2',
                        'description' => 'Nêu được chức năng của từng bộ phận phần cứng.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Chức năng của từng bộ phận', 'slug' => 'th-chuc-nang-phan-cung',
                                'objective' => 'Nêu được chức năng của CPU, RAM, ổ cứng và các thiết bị vào/ra.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                                'instructions' => 'Ghi nhớ: CPU xử lý, RAM nhớ tạm, ổ cứng lưu lâu dài. Thiết bị vào đưa dữ liệu vào, thiết bị ra đưa kết quả ra.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Phần mềm, tệp và thư mục', 'slug' => 'th-phan-mem-tep-thu-muc',
                'description' => 'Phân biệt hệ điều hành và phần mềm ứng dụng; quản lý tệp và thư mục.',
                'icon' => '💾', 'sort_order' => 2, 'grade_min' => 7, 'grade_max' => 8,
                'skills' => [
                    [
                        'name' => 'Phần mềm hệ thống và ứng dụng', 'slug' => 'kn-th-phan-mem-1',
                        'description' => 'Phân biệt hệ điều hành và phần mềm ứng dụng.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Hệ điều hành và phần mềm ứng dụng', 'slug' => 'th-he-dieu-hanh-ung-dung',
                                'objective' => 'Phân biệt được hệ điều hành với phần mềm ứng dụng và kể tên ví dụ.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Hệ điều hành quản lý toàn bộ máy tính (ví dụ Windows). Phần mềm ứng dụng phục vụ từng công việc cụ thể (Word, Chrome).',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Tệp và thư mục', 'slug' => 'kn-th-tep-thu-muc-1',
                        'description' => 'Nhận biết loại tệp qua phần mở rộng và biết quản lý thư mục.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Tệp và thư mục', 'slug' => 'th-tep-va-thu-muc',
                                'objective' => 'Nhận biết loại tệp qua phần mở rộng và biết cách sắp xếp tệp trong thư mục.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                                'instructions' => 'Phần mở rộng (đuôi tệp) cho biết loại tệp: .docx văn bản, .jpg ảnh, .mp3 nhạc. Dùng thư mục để phân loại tệp gọn gàng.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Sử dụng máy tính an toàn', 'slug' => 'th-su-dung-an-toan',
                'description' => 'Bảo vệ thông tin cá nhân và sử dụng phím tắt khi soạn thảo văn bản.',
                'icon' => '🔒', 'sort_order' => 3, 'grade_min' => 8, 'grade_max' => 9,
                'skills' => [
                    [
                        'name' => 'An toàn thông tin', 'slug' => 'kn-th-an-toan-1',
                        'description' => 'Biết đặt mật khẩu mạnh và bảo vệ thông tin cá nhân.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Mật khẩu mạnh và bảo mật cá nhân', 'slug' => 'th-mat-khau-bao-mat',
                                'objective' => 'Biết cách đặt mật khẩu mạnh và các quy tắc bảo vệ thông tin cá nhân trên mạng.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Mật khẩu mạnh: dài, có chữ hoa, chữ thường, số và ký tự đặc biệt. Không chia sẻ mật khẩu và thông tin riêng tư cho người lạ.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Soạn thảo văn bản', 'slug' => 'kn-th-van-ban-1',
                        'description' => 'Gõ phím đúng tư thế và dùng phím tắt cơ bản.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Gõ phím và phím tắt văn bản', 'slug' => 'th-go-phim-tat',
                                'objective' => 'Biết hàng phím cơ sở và sử dụng được các phím tắt Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+S.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Đặt ngón tay lên hàng phím cơ sở (A S D F J K L). Nhớ các phím tắt: Ctrl+C sao chép, Ctrl+V dán, Ctrl+X cắt, Ctrl+S lưu.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ---------------- câu hỏi ----------------
    // Mỗi bài: 'quiz' => [[prompt, [4 options], correctIndex, explanation, difficulty], ...]
    //           'matching' => [[prompt, [[trái, phải], ...], explanation, difficulty], ...]
    //           'sort' => [[prompt, [[nội dung, nhóm], ...], explanation, difficulty], ...]
    //           'fill' => [[prompt (chứa ___), [[0, đáp án], ...], explanation, difficulty], ...]

    private function questions(): array
    {
        return [
            // ============ Bài 1: Các bộ phận của máy tính ============
            'th-cac-bo-phan-may-tinh' => [
                'quiz' => [
                    ['Bộ phận nào được ví như "bộ não" của máy tính?',
                        ['CPU', 'RAM', 'Ổ cứng', 'Màn hình'], 0,
                        'CPU (bộ xử lý trung tâm) thực hiện mọi phép tính và điều khiển hoạt động của máy tính, nên được ví như bộ não.', 'de'],
                    ['Thiết bị nào sau đây là thiết bị vào (đưa dữ liệu vào máy tính)?',
                        ['Màn hình', 'Loa', 'Bàn phím', 'Máy in'], 2,
                        'Bàn phím và chuột là thiết bị vào. Màn hình, loa, máy in là thiết bị ra (đưa kết quả ra ngoài).', 'de'],
                    ['RAM có chức năng gì?',
                        ['Lưu dữ liệu tạm thời khi máy đang chạy', 'Lưu dữ liệu vĩnh viễn', 'Hiển thị hình ảnh', 'Phát ra âm thanh'], 0,
                        'RAM là bộ nhớ truy cập ngẫu nhiên, chỉ lưu dữ liệu tạm thời; tắt máy thì dữ liệu trong RAM bị mất.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi bộ phận với chức năng của nó.',
                        [['CPU', 'Xử lý mọi tính toán'], ['Màn hình', 'Hiển thị hình ảnh'], ['Bàn phím', 'Nhập chữ và số'], ['Loa', 'Phát ra âm thanh']],
                        'CPU xử lý; màn hình hiển thị; bàn phím nhập liệu; loa phát âm thanh.', 'de'],
                    ['Nối mỗi thiết bị với loại của nó.',
                        [['Chuột', 'Thiết bị vào'], ['Máy in', 'Thiết bị ra'], ['Ổ cứng', 'Thiết bị lưu trữ'], ['Webcam', 'Thiết bị vào']],
                        'Chuột và webcam đưa dữ liệu vào; máy in đưa kết quả ra; ổ cứng lưu trữ dữ liệu.', 'de'],
                    ['Nối tên tiếng Anh với nghĩa tiếng Việt.',
                        [['CPU', 'Bộ xử lý trung tâm'], ['RAM', 'Bộ nhớ truy cập ngẫu nhiên'], ['Monitor', 'Màn hình'], ['Printer', 'Máy in']],
                        'CPU là bộ xử lý trung tâm, RAM là bộ nhớ truy cập ngẫu nhiên, monitor là màn hình, printer là máy in.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi thiết bị vào nhóm "Thiết bị vào" hoặc "Thiết bị ra".',
                        [['Bàn phím', 'Thiết bị vào'], ['Chuột', 'Thiết bị vào'], ['Màn hình', 'Thiết bị ra'], ['Loa', 'Thiết bị ra']],
                        'Thiết bị vào đưa dữ liệu vào máy (bàn phím, chuột); thiết bị ra đưa kết quả ra (màn hình, loa).', 'de'],
                    ['Kéo mỗi bộ phận vào nhóm "Bên trong" hoặc "Bên ngoài" máy tính.',
                        [['CPU', 'Bên trong'], ['RAM', 'Bên trong'], ['Màn hình', 'Bên ngoài'], ['Máy in', 'Bên ngoài']],
                        'CPU và RAM nằm bên trong thùng máy; màn hình và máy in là thiết bị bên ngoài.', 'de'],
                    ['Kéo mỗi thiết bị vào nhóm "Lưu tạm thời" hoặc "Lưu lâu dài".',
                        [['RAM', 'Lưu tạm thời'], ['Ổ cứng', 'Lưu lâu dài'], ['USB', 'Lưu lâu dài'], ['Thẻ nhớ', 'Lưu lâu dài']],
                        'RAM chỉ lưu tạm khi máy chạy; ổ cứng, USB, thẻ nhớ giữ dữ liệu lâu dài kể cả khi tắt máy.', 'de'],
                ],
                'fill' => [
                    ['Bộ phận được ví như "bộ não" của máy tính là ___.', [[0, 'cpu']],
                        'CPU thực hiện mọi phép tính và điều khiển máy tính nên được ví như bộ não.', 'de'],
                    ['Bàn phím và chuột là các thiết bị ___ của máy tính (vào/ra).', [[0, 'vào']],
                        'Bàn phím và chuột đưa dữ liệu từ người dùng vào máy tính nên là thiết bị vào.', 'de'],
                    ['Khi tắt máy, dữ liệu lưu trong ___ sẽ bị mất.', [[0, 'ram']],
                        'RAM chỉ lưu dữ liệu tạm thời; tắt máy là dữ liệu trong RAM mất hết.', 'de'],
                ],
            ],
            // ============ Bài 2: Chức năng của từng bộ phận ============
            'th-chuc-nang-phan-cung' => [
                'quiz' => [
                    ['Muốn lưu bài văn để tuần sau mở lại, em nên lưu vào đâu?',
                        ['Ổ cứng', 'RAM', 'CPU', 'Loa'], 0,
                        'Ổ cứng lưu dữ liệu lâu dài, không mất khi tắt máy. RAM chỉ nhớ tạm.', 'de'],
                    ['Thiết bị nào vừa là thiết bị vào vừa là thiết bị ra?',
                        ['Bàn phím', 'Chuột', 'Màn hình cảm ứng', 'Loa'], 2,
                        'Màn hình cảm ứng vừa hiển thị hình ảnh (ra) vừa nhận thao tác chạm của ngón tay (vào).', 'trung_binh'],
                    ['Tốc độ xử lý của máy tính phụ thuộc chủ yếu vào bộ phận nào?',
                        ['CPU', 'Loa', 'Máy in', 'Chuột'], 0,
                        'CPU càng mạnh thì máy tính tính toán và xử lý công việc càng nhanh.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi bộ phận với nhiệm vụ của nó.',
                        [['Ổ cứng', 'Lưu trữ dữ liệu lâu dài'], ['RAM', 'Lưu dữ liệu tạm khi máy chạy'], ['CPU', 'Tính toán và điều khiển'], ['Card đồ hoạ', 'Xử lý hình ảnh']],
                        'Ổ cứng lưu lâu dài; RAM nhớ tạm; CPU tính toán; card đồ hoạ xử lý hình ảnh.', 'trung_binh'],
                    ['Nối mỗi việc cần làm với thiết bị phù hợp.',
                        [['Gõ chữ', 'Bàn phím'], ['Di chuyển con trỏ', 'Chuột'], ['Nghe nhạc', 'Loa'], ['In bài tập', 'Máy in']],
                        'Gõ chữ dùng bàn phím, di chuyển con trỏ dùng chuột, nghe nhạc dùng loa, in bài dùng máy in.', 'de'],
                    ['Nối mỗi đơn vị lưu trữ với giá trị của nó.',
                        [['1 byte', '8 bit'], ['1 KB', '1024 byte'], ['1 MB', '1024 KB'], ['1 GB', '1024 MB']],
                        '1 byte = 8 bit; mỗi đơn vị lớn hơn gấp 1024 lần đơn vị liền trước nó.', 'trung_binh'],
                ],
                'sort' => [
                    ['Kéo mỗi thiết bị vào nhóm "Có lưu trữ dữ liệu" hoặc "Không lưu trữ".',
                        [['Ổ cứng', 'Có lưu trữ dữ liệu'], ['USB', 'Có lưu trữ dữ liệu'], ['Thẻ nhớ', 'Có lưu trữ dữ liệu'], ['Màn hình', 'Không lưu trữ']],
                        'Ổ cứng, USB, thẻ nhớ đều lưu được dữ liệu; màn hình chỉ hiển thị, không lưu trữ.', 'de'],
                    ['Kéo mỗi thiết bị vào nhóm đúng về việc giữ dữ liệu khi tắt máy.',
                        [['RAM', 'Mất dữ liệu khi tắt máy'], ['Ổ cứng', 'Giữ dữ liệu khi tắt máy'], ['USB', 'Giữ dữ liệu khi tắt máy'], ['Đĩa CD', 'Giữ dữ liệu khi tắt máy']],
                        'RAM cần điện mới giữ được dữ liệu; ổ cứng, USB, đĩa CD giữ dữ liệu kể cả khi tắt máy.', 'trung_binh'],
                    ['Kéo mỗi bộ phận vào nhóm "Xử lý" hoặc "Hiển thị".',
                        [['CPU', 'Xử lý'], ['RAM', 'Xử lý'], ['Màn hình', 'Hiển thị'], ['Máy chiếu', 'Hiển thị']],
                        'CPU và RAM tham gia xử lý dữ liệu; màn hình và máy chiếu dùng để hiển thị.', 'de'],
                ],
                'fill' => [
                    ['1 GB bằng ___ MB.', [[0, '1024']],
                        'Trong tin học, 1 GB = 1024 MB.', 'trung_binh'],
                    ['Thiết bị vừa hiển thị hình ảnh vừa nhận thao tác chạm tay là màn hình ___.', [[0, 'cảm ứng']],
                        'Màn hình cảm ứng vừa là thiết bị ra (hiển thị) vừa là thiết bị vào (nhận chạm).', 'de'],
                    ['Muốn máy tính chạy nhanh, em nên chọn CPU có tốc độ ___ (cao/thấp).', [[0, 'cao']],
                        'CPU tốc độ cao xử lý được nhiều phép tính hơn trong cùng một thời gian.', 'de'],
                ],
            ],
            // ============ Bài 3: Hệ điều hành và phần mềm ứng dụng ============
            'th-he-dieu-hanh-ung-dung' => [
                'quiz' => [
                    ['Đâu là hệ điều hành?',
                        ['Windows', 'Word', 'Chrome', 'Zalo'], 0,
                        'Windows là hệ điều hành, quản lý phần cứng và phần mềm của máy tính. Word, Chrome là phần mềm ứng dụng.', 'de'],
                    ['Phần mềm nào dùng để duyệt web, đọc tin tức trên mạng?',
                        ['Google Chrome', 'Notepad', 'Paint', 'Calculator'], 0,
                        'Google Chrome là trình duyệt web. Notepad soạn văn bản thô, Paint vẽ hình, Calculator tính toán.', 'de'],
                    ['Nếu máy tính không được cài hệ điều hành thì điều gì xảy ra?',
                        ['Máy chạy nhanh hơn', 'Không thể sử dụng được', 'Chỉ xem phim được', 'Tự cài hệ điều hành'], 1,
                        'Hệ điều hành là cầu nối giữa người dùng và phần cứng; thiếu nó máy tính không thể hoạt động bình thường.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi phần mềm với loại của nó.',
                        [['Windows', 'Hệ điều hành'], ['Microsoft Word', 'Ứng dụng văn phòng'], ['Chrome', 'Ứng dụng duyệt web'], ['Zalo', 'Ứng dụng nhắn tin']],
                        'Windows là hệ điều hành; Word, Chrome, Zalo là các phần mềm ứng dụng phục vụ từng nhu cầu.', 'de'],
                    ['Nối mỗi phần mềm với công dụng của nó.',
                        [['Word', 'Soạn thảo văn bản'], ['Excel', 'Tính toán bảng biểu'], ['PowerPoint', 'Trình chiếu'], ['Paint', 'Vẽ hình đơn giản']],
                        'Word soạn văn bản, Excel tính toán bảng biểu, PowerPoint làm bài trình chiếu, Paint vẽ hình đơn giản.', 'de'],
                    ['Nối mỗi hệ điều hành với thiết bị nó thường chạy trên.',
                        [['Windows', 'Máy tính để bàn'], ['Android', 'Điện thoại Android'], ['iOS', 'Điện thoại iPhone'], ['macOS', 'Máy tính Mac']],
                        'Windows chạy trên máy tính thường, Android và iOS chạy trên điện thoại, macOS chạy trên máy Mac.', 'trung_binh'],
                ],
                'sort' => [
                    ['Kéo mỗi phần mềm vào nhóm "Hệ điều hành" hoặc "Phần mềm ứng dụng".',
                        [['Windows', 'Hệ điều hành'], ['Android', 'Hệ điều hành'], ['Word', 'Phần mềm ứng dụng'], ['Chrome', 'Phần mềm ứng dụng']],
                        'Windows, Android là hệ điều hành; Word, Chrome là phần mềm ứng dụng chạy trên hệ điều hành.', 'de'],
                    ['Kéo mỗi phần mềm vào nhóm "Dùng để học tập" hoặc "Dùng để giải trí".',
                        [['Word', 'Dùng để học tập'], ['Phần mềm học toán', 'Dùng để học tập'], ['Trò chơi điện tử', 'Dùng để giải trí'], ['Ứng dụng nghe nhạc', 'Dùng để giải trí']],
                        'Phần mềm học tập phục vụ việc học; trò chơi và ứng dụng nghe nhạc phục vụ giải trí.', 'de'],
                    ['Kéo mỗi phần mềm vào nhóm "Cài sẵn theo máy" hoặc "Cài thêm".',
                        [['Hệ điều hành', 'Cài sẵn theo máy'], ['Word', 'Cài thêm'], ['Zalo', 'Cài thêm'], ['Phần mềm diệt virus', 'Cài thêm']],
                        'Hệ điều hành thường được cài sẵn; các ứng dụng như Word, Zalo do người dùng cài thêm.', 'de'],
                ],
                'fill' => [
                    ['___ là phần mềm quản lý toàn bộ hoạt động của máy tính.', [[0, 'hệ điều hành']],
                        'Hệ điều hành quản lý phần cứng, phần mềm và là cầu nối với người dùng.', 'de'],
                    ['Để soạn thảo văn bản, em mở phần mềm ___.', [[0, 'word']],
                        'Microsoft Word (gọi tắt là Word) là phần mềm soạn thảo văn bản phổ biến.', 'de'],
                    ['Muốn lên mạng đọc tin tức, em dùng trình duyệt web như ___.', [[0, 'chrome']],
                        'Chrome là trình duyệt web phổ biến; ngoài ra còn có Cốc Cốc, Edge, Firefox.', 'de'],
                ],
            ],
            // ============ Bài 4: Tệp và thư mục ============
            'th-tep-va-thu-muc' => [
                'quiz' => [
                    ['Tệp văn bản Word thường có phần mở rộng nào?',
                        ['.docx', '.mp3', '.jpg', '.mp4'], 0,
                        '.docx là tệp văn bản Word; .mp3 là nhạc, .jpg là ảnh, .mp4 là video.', 'de'],
                    ['Muốn sắp xếp gọn gàng nhiều tệp cùng chủ đề, em nên làm gì?',
                        ['Tạo thư mục', 'Xoá hết các tệp', 'Đổi tên máy tính', 'Tắt máy'], 0,
                        'Thư mục (folder) dùng để chứa và phân loại các tệp cùng chủ đề cho gọn gàng.', 'de'],
                    ['Phần mở rộng (đuôi) của tệp cho biết điều gì?',
                        ['Loại định dạng của tệp', 'Kích thước chính xác', 'Người tạo ra tệp', 'Ngày tạo tệp'], 0,
                        'Đuôi tệp cho biết tệp thuộc loại nào: văn bản, hình ảnh, âm thanh hay video.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi đuôi tệp với loại tệp tương ứng.',
                        [['.docx', 'Văn bản'], ['.jpg', 'Hình ảnh'], ['.mp3', 'Âm thanh'], ['.mp4', 'Video']],
                        '.docx là văn bản, .jpg là ảnh, .mp3 là nhạc, .mp4 là video.', 'de'],
                    ['Nối mỗi thao tác với kết quả của nó.',
                        [['Đổi tên tệp', 'Tệp có tên mới'], ['Xoá tệp', 'Tệp chuyển vào Thùng rác'], ['Sao chép tệp', 'Có thêm một bản giống hệt'], ['Di chuyển tệp', 'Tệp sang vị trí mới']],
                        'Đổi tên thì tên mới; xoá thì vào Thùng rác; sao chép tạo bản giống hệt; di chuyển đổi vị trí.', 'trung_binh'],
                    ['Nối mỗi biểu tượng với ý nghĩa của nó.',
                        [['Thư mục màu vàng', 'Nơi chứa các tệp'], ['Tệp có chữ W', 'Văn bản Word'], ['Tệp có hình nốt nhạc', 'Tệp âm thanh'], ['Thùng rác', 'Nơi chứa tệp đã xoá']],
                        'Thư mục chứa tệp; chữ W là Word; nốt nhạc là âm thanh; Thùng rác chứa tệp đã xoá.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi tệp vào nhóm "Văn bản" hoặc "Hình ảnh".',
                        [['baivan.docx', 'Văn bản'], ['anh.png', 'Hình ảnh'], ['toan.pdf', 'Văn bản'], ['meo.jpg', 'Hình ảnh']],
                        '.docx và .pdf là tài liệu văn bản; .png và .jpg là tệp hình ảnh.', 'de'],
                    ['Kéo mỗi việc làm vào nhóm "An toàn" hoặc "Nguy hiểm".',
                        [['Lưu bản sao tệp quan trọng', 'An toàn'], ['Đổi tên tệp của mình', 'An toàn'], ['Xoá tệp trong thư mục hệ thống', 'Nguy hiểm'], ['Mở tệp lạ từ email không rõ nguồn', 'Nguy hiểm']],
                        'Sao lưu và đổi tên tệp của mình thì an toàn; xoá tệp hệ thống hoặc mở tệp lạ rất nguy hiểm.', 'trung_binh'],
                    ['Kéo mỗi tên tệp vào nhóm "Hợp lệ" hoặc "Không hợp lệ".',
                        [['baitap1.docx', 'Hợp lệ'], ['vanban.txt', 'Hợp lệ'], ['baocao:2024.docx', 'Không hợp lệ'], ['anh*dep.jpg', 'Không hợp lệ']],
                        'Tên tệp không được chứa các ký tự đặc biệt như : * ? " < > | / \\.', 'trung_binh'],
                ],
                'fill' => [
                    ['Tệp bài hát thường có đuôi ___.', [[0, 'mp3']],
                        '.mp3 là định dạng tệp âm thanh phổ biến.', 'de'],
                    ['Muốn chứa nhiều tệp cùng chủ đề, em tạo một ___.', [[0, 'thư mục']],
                        'Thư mục dùng để phân loại và chứa các tệp cùng chủ đề.', 'de'],
                    ['Khi xoá một tệp, tệp đó sẽ được chuyển vào ___.', [[0, 'thùng rác']],
                        'Tệp bị xoá sẽ nằm trong Thùng rác và có thể khôi phục lại nếu cần.', 'de'],
                ],
            ],
            // ============ Bài 5: Mật khẩu mạnh và bảo mật cá nhân ============
            'th-mat-khau-bao-mat' => [
                'quiz' => [
                    ['Mật khẩu nào sau đây là mạnh nhất?',
                        ['123456', 'matkhau', 'An@2024!Hoc', 'ngaysinh'], 2,
                        'Mật khẩu mạnh phải dài và gồm chữ hoa, chữ thường, số cùng ký tự đặc biệt như An@2024!Hoc.', 'de'],
                    ['Khi nhận được tin nhắn lạ yêu cầu cung cấp mật khẩu, em nên làm gì?',
                        ['Cung cấp ngay', 'Không cung cấp và báo cho người lớn', 'Chia sẻ cho bạn cùng biết', 'Tắt máy và bỏ qua'], 1,
                        'Không ai được phép hỏi mật khẩu của em; đó có thể là lừa đảo. Hãy báo cho người lớn biết.', 'de'],
                    ['Thông tin nào KHÔNG nên chia sẻ công khai trên mạng?',
                        ['Tên bài hát yêu thích', 'Địa chỉ nhà', 'Màu sắc yêu thích', 'Môn thể thao yêu thích'], 1,
                        'Địa chỉ nhà, số điện thoại là thông tin riêng tư; kẻ xấu có thể lợi dụng nếu em chia sẻ công khai.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi hành vi với đánh giá an toàn.',
                        [['Dùng mật khẩu "123456"', 'Không an toàn'], ['Chia sẻ mật khẩu cho bạn', 'Không an toàn'], ['Đăng xuất sau khi dùng máy chung', 'An toàn'], ['Dùng một mật khẩu cho mọi tài khoản', 'Không an toàn']],
                        'Mật khẩu dễ đoán, chia sẻ mật khẩu hay dùng chung một mật khẩu đều không an toàn.', 'trung_binh'],
                    ['Nối mỗi tình huống với cách xử lý đúng.',
                        [['Người lạ xin mật khẩu', 'Từ chối và báo người lớn'], ['Quên đăng xuất ở máy trường', 'Nhờ người lớn đổi mật khẩu ngay'], ['Thấy link lạ hứa tặng quà', 'Không bấm vào'], ['Bạn rủ đăng ảnh thẻ học sinh', 'Từ chối']],
                        'Luôn từ chối yêu cầu đáng ngờ, không bấm link lạ và báo người lớn khi gặp sự cố.', 'trung_binh'],
                    ['Nối mỗi yếu tố với vai trò của nó trong mật khẩu.',
                        [['Chữ hoa (A, B, C...)', 'Giúp mật khẩu mạnh'], ['Số (1, 2, 3...)', 'Giúp mật khẩu mạnh'], ['Ký tự đặc biệt (@, #, !...)', 'Giúp mật khẩu mạnh'], ['Ngày sinh của bản thân', 'Không nên dùng']],
                        'Chữ hoa, số và ký tự đặc biệt làm mật khẩu mạnh hơn; ngày sinh dễ bị đoán nên không dùng.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi việc làm vào nhóm "Nên làm" hoặc "Không nên làm".',
                        [['Đặt mật khẩu dài trên 8 ký tự', 'Nên làm'], ['Đăng xuất khỏi máy tính chung', 'Nên làm'], ['Cho bạn mượn tài khoản', 'Không nên làm'], ['Ghi mật khẩu ra giấy dán ở bàn', 'Không nên làm']],
                        'Mật khẩu dài và đăng xuất sau khi dùng chung là nên làm; chia sẻ tài khoản hay để lộ mật khẩu thì không.', 'de'],
                    ['Kéo mỗi thông tin vào nhóm "Riêng tư" hoặc "Có thể chia sẻ".',
                        [['Số điện thoại', 'Riêng tư'], ['Địa chỉ nhà', 'Riêng tư'], ['Sở thích đọc sách', 'Có thể chia sẻ'], ['Món ăn yêu thích', 'Có thể chia sẻ']],
                        'Số điện thoại, địa chỉ nhà là riêng tư; sở thích, món ăn yêu thích có thể chia sẻ.', 'de'],
                    ['Kéo mỗi mật khẩu vào nhóm "Mạnh" hoặc "Yếu".',
                        [['Hs@2026!vn', 'Mạnh'], ['Tin$hoc9A', 'Mạnh'], ['12345678', 'Yếu'], ['abcdef', 'Yếu']],
                        'Mật khẩu mạnh dài và có đủ chữ hoa, chữ thường, số, ký tự đặc biệt; mật khẩu toàn số hoặc toàn chữ thường thì yếu.', 'trung_binh'],
                ],
                'fill' => [
                    ['Mật khẩu mạnh nên dài ít nhất ___ ký tự.', [[0, '8']],
                        'Mật khẩu từ 8 ký tự trở lên sẽ khó bị đoán hơn.', 'de'],
                    ['Khi dùng chung máy tính ở trường, xong việc em phải ___ khỏi tài khoản.', [[0, 'đăng xuất']],
                        'Đăng xuất để người khác không thể dùng tài khoản của em.', 'de'],
                    ['Không bao giờ tiết lộ ___ của mình cho người lạ trên mạng.', [[0, 'mật khẩu']],
                        'Mật khẩu là chìa khoá tài khoản; chỉ mình em được biết.', 'de'],
                ],
            ],
            // ============ Bài 6: Gõ phím và phím tắt văn bản ============
            'th-go-phim-tat' => [
                'quiz' => [
                    ['Tổ hợp phím nào dùng để sao chép (copy)?',
                        ['Ctrl + C', 'Ctrl + V', 'Ctrl + S', 'Ctrl + X'], 0,
                        'Ctrl+C sao chép, Ctrl+V dán, Ctrl+X cắt, Ctrl+S lưu.', 'de'],
                    ['Muốn lưu nhanh văn bản đang soạn, em nhấn tổ hợp phím nào?',
                        ['Ctrl + S', 'Ctrl + P', 'Ctrl + N', 'Ctrl + O'], 0,
                        'Ctrl+S (Save) lưu tài liệu nhanh mà không cần dùng chuột.', 'de'],
                    ['Khi gõ tiếng Việt đúng kỹ thuật, các ngón tay đặt ở hàng phím nào lúc bắt đầu?',
                        ['Hàng phím số', 'Hàng phím cơ sở', 'Hàng phím chức năng', 'Bàn phím số bên phải'], 1,
                        'Hàng phím cơ sở gồm A S D F J K L là nơi đặt các ngón tay khi bắt đầu gõ.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi phím tắt với chức năng của nó.',
                        [['Ctrl+C', 'Sao chép'], ['Ctrl+V', 'Dán'], ['Ctrl+X', 'Cắt'], ['Ctrl+S', 'Lưu']],
                        'Ctrl+C sao chép, Ctrl+V dán, Ctrl+X cắt, Ctrl+S lưu tài liệu.', 'de'],
                    ['Nối mỗi phím tắt với chức năng của nó.',
                        [['Ctrl+Z', 'Hoàn tác'], ['Ctrl+B', 'In đậm'], ['Ctrl+I', 'In nghiêng'], ['Ctrl+U', 'Gạch chân']],
                        'Ctrl+Z hoàn tác thao tác vừa làm; Ctrl+B/I/U định dạng chữ đậm, nghiêng, gạch chân.', 'trung_binh'],
                    ['Nối mỗi ngón tay với phím nó phụ trách ở hàng cơ sở.',
                        [['Ngón trỏ tay trái', 'Phím F'], ['Ngón trỏ tay phải', 'Phím J'], ['Ngón giữa tay trái', 'Phím D'], ['Ngón giữa tay phải', 'Phím K']],
                        'Hai ngón trỏ đặt ở F và J (có gờ nổi để nhận biết), các ngón còn lại đặt ở D, S, A và K, L.', 'trung_binh'],
                ],
                'sort' => [
                    ['Kéo mỗi phím tắt vào nhóm "Chỉnh sửa" hoặc "Định dạng".',
                        [['Ctrl+C', 'Chỉnh sửa'], ['Ctrl+V', 'Chỉnh sửa'], ['Ctrl+B', 'Định dạng'], ['Ctrl+I', 'Định dạng']],
                        'Ctrl+C, Ctrl+V dùng khi chỉnh sửa nội dung; Ctrl+B, Ctrl+I dùng để định dạng chữ.', 'de'],
                    ['Kéo mỗi phím vào nhóm "Hàng phím số" hoặc "Hàng phím chữ".',
                        [['Phím 1', 'Hàng phím số'], ['Phím 5', 'Hàng phím số'], ['Phím A', 'Hàng phím chữ'], ['Phím M', 'Hàng phím chữ']],
                        'Hàng trên cùng là hàng phím số; các hàng dưới là hàng phím chữ.', 'de'],
                    ['Kéo mỗi thói quen vào nhóm "Nên làm" hoặc "Không nên" khi gõ phím.',
                        [['Nhìn vào màn hình khi gõ', 'Nên làm'], ['Đặt ngón tay đúng hàng cơ sở', 'Nên làm'], ['Gõ bằng một ngón tay', 'Không nên'], ['Cúi sát màn hình', 'Không nên']],
                        'Gõ đúng kỹ thuật: mắt nhìn màn hình, ngón tay đặt đúng hàng cơ sở, ngồi thẳng lưng.', 'de'],
                ],
                'fill' => [
                    ['Để dán nội dung đã sao chép, em nhấn ___ + V.', [[0, 'ctrl']],
                        'Ctrl+V là phím tắt dán nội dung đã sao chép hoặc cắt.', 'de'],
                    ['Phím tắt ___ giúp lưu nhanh tài liệu đang soạn.', [[0, 'ctrl+s']],
                        'Nhấn Ctrl+S thường xuyên để không bị mất bài khi máy gặp sự cố.', 'de'],
                    ['Hàng phím ___ là nơi đặt các ngón tay khi bắt đầu gõ.', [[0, 'cơ sở']],
                        'Hàng phím cơ sở (A S D F J K L) là vị trí chuẩn của các ngón tay.', 'de'],
                ],
            ],
        ];
    }

    private function seedLessonQuestions(string $lessonSlug, array $byType): void
    {
        $lesson = $this->lessonBySlug[$lessonSlug] ?? Lesson::where('slug', $lessonSlug)->firstOrFail();
        $order = 1;
        foreach (['quiz', 'matching', 'sort', 'fill'] as $type) {
            $items = $byType[$type] ?? [];
            if (empty($items)) {
                continue;
            }
            // Đã có câu hỏi loại này cho bài học → bỏ qua cả loại (idempotent).
            if (Question::where('lesson_id', $lesson->id)->where('game_type', $type)->exists()) {
                continue;
            }
            foreach ($items as $q) {
                [$prompt, $data, $explanation] = [$q[0], $q[1], $q[3]];
                $difficulty = $q[4] ?? 'de';
                $extra = $q[2] ?? null;
                $question = Question::create([
                    'lesson_id' => $lesson->id, 'game_type' => $type,
                    'prompt' => $prompt, 'explanation' => $explanation,
                    'difficulty' => $difficulty, 'points' => 10,
                    'sort_order' => $order++, 'is_demo' => true,
                ]);
                $this->seedDetails($question, $type, $data, $extra);
            }
        }
    }

    private function seedDetails(Question $question, string $type, array $data, $extra): void
    {
        switch ($type) {
            case 'quiz':
                foreach ($data as $i => $text) {
                    QuestionOption::create([
                        'question_id' => $question->id, 'option_text' => $text,
                        'is_correct' => $i === $extra, 'sort_order' => $i + 1,
                    ]);
                }
                break;
            case 'matching':
                foreach ($data as $i => [$left, $right]) {
                    MatchingPair::create([
                        'question_id' => $question->id,
                        'left_text' => $left, 'right_text' => $right, 'sort_order' => $i + 1,
                    ]);
                }
                break;
            case 'sort':
                foreach ($data as $i => [$text, $category]) {
                    SortItem::create([
                        'question_id' => $question->id,
                        'item_text' => $text, 'category' => $category, 'sort_order' => $i + 1,
                    ]);
                }
                break;
            case 'fill':
                foreach ($data as $i => [$blankIndex, $text]) {
                    FillAnswer::create([
                        'question_id' => $question->id,
                        'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1,
                    ]);
                }
                break;
        }
    }
}
