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
 * Môn Công nghệ — nội dung TỰ VIẾT 100% TIẾNG VIỆT, bám chương trình phổ thông.
 * 3 chủ đề (lớp 6-7 / 7-8 / 8-9), 6 kỹ năng, 6 bài học, mỗi bài 12 câu
 * (3 quiz + 3 matching + 3 sort + 3 fill) = 72 câu.
 * Idempotent: updateOrCreate theo slug; câu hỏi kiểm tra exists theo
 * (lesson_id, game_type) trước khi seed.
 */
class SubjectCongNgheSeeder extends Seeder
{
    private array $lessonBySlug = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(
            ['slug' => 'cong-nghe'],
            [
                'name' => 'Công nghệ', 'icon' => '⚙️', 'color' => '#78716c',
                'description' => 'Khám phá công nghệ: an toàn điện, vật liệu và dụng cụ cơ khí.',
                'sort_order' => 10, 'is_published' => true, 'is_demo' => true,
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
                'name' => 'An toàn điện', 'slug' => 'cn-an-toan-dien',
                'description' => 'Các quy tắc an toàn khi sử dụng điện và cách xử lý sự cố điện.',
                'icon' => '⚡', 'sort_order' => 1, 'grade_min' => 6, 'grade_max' => 7,
                'skills' => [
                    [
                        'name' => 'Quy tắc an toàn điện', 'slug' => 'kn-cn-an-toan-dien-1',
                        'description' => 'Nêu được các quy tắc an toàn khi sử dụng điện trong gia đình.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Quy tắc an toàn khi dùng điện', 'slug' => 'cn-quy-tac-an-toan-dien',
                                'objective' => 'Nêu được các quy tắc an toàn điện: tay khô khi chạm ổ điện, ngắt điện khi sửa chữa.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Điện rất nguy hiểm nếu dùng sai cách. Ghi nhớ: tay khô mới chạm vào thiết bị điện, ngắt điện trước khi sửa chữa, báo người lớn khi thấy dây điện hở.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Xử lý sự cố điện', 'slug' => 'kn-cn-su-co-dien-1',
                        'description' => 'Biết cách xử lý đúng khi gặp sự cố về điện.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Xử lý khi gặp sự cố điện', 'slug' => 'cn-xu-ly-su-co-dien',
                                'objective' => 'Biết cách xử lý đúng khi gặp người bị điện giật, chập điện có mùi khét.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Khi có sự cố điện: ngắt nguồn điện trước, dùng vật cách điện để cứu người, gọi số khẩn cấp 114 (cháy), 115 (cấp cứu) khi cần.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Vật liệu và dụng cụ', 'slug' => 'cn-vat-lieu-dung-cu',
                'description' => 'Tính chất của gỗ, kim loại, nhựa và cách dùng dụng cụ cơ khí.',
                'icon' => '🔨', 'sort_order' => 2, 'grade_min' => 7, 'grade_max' => 8,
                'skills' => [
                    [
                        'name' => 'Vật liệu thông dụng', 'slug' => 'kn-cn-vat-lieu-1',
                        'description' => 'Nêu được tính chất và ứng dụng của gỗ, kim loại, nhựa.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Gỗ, kim loại và nhựa', 'slug' => 'cn-go-kim-loai-nhua',
                                'objective' => 'Nêu được tính chất cơ bản của gỗ, kim loại, nhựa và đồ vật làm từ chúng.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Gỗ nhẹ dễ gia công; kim loại cứng chắc nhưng sắt dễ gỉ; nhựa nhẹ, không thấm nước. Mỗi vật liệu phù hợp với những đồ vật khác nhau.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Dụng cụ cơ khí', 'slug' => 'kn-cn-dung-cu-1',
                        'description' => 'Biết công dụng và cách dùng an toàn các dụng cụ cầm tay.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Búa, kìm, tua vít và cưa', 'slug' => 'cn-bua-kim-tua-vit-cua',
                                'objective' => 'Nêu được công dụng của búa, kìm, tua vít, cưa và quy tắc dùng an toàn.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                                'instructions' => 'Búa để đóng, kìm để kẹp cắt, tua vít để vặn ốc, cưa để cắt gỗ. Dụng cụ sắc nhọn phải có người lớn hướng dẫn khi dùng.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Trồng trọt cơ bản', 'slug' => 'cn-trong-trot',
                'description' => 'Các bước trồng cây và kỹ thuật chăm sóc cây trồng.',
                'icon' => '🌱', 'sort_order' => 3, 'grade_min' => 8, 'grade_max' => 9,
                'skills' => [
                    [
                        'name' => 'Kỹ thuật trồng cây', 'slug' => 'kn-cn-trong-trot-1',
                        'description' => 'Nêu được các bước cơ bản khi trồng cây.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Các bước trồng cây', 'slug' => 'cn-cac-buoc-trong-cay',
                                'objective' => 'Nêu được trình tự các bước trồng cây: làm đất, gieo trồng, tưới nước, chăm sóc.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Trồng cây theo trình tự: làm đất tơi xốp → gieo hạt hoặc trồng cây con → tưới nước nhẹ → chăm sóc thường xuyên.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Chăm sóc cây trồng', 'slug' => 'kn-cn-cham-soc-1',
                        'description' => 'Biết tưới nước và bón phân đúng cách cho cây.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Tưới nước và bón phân', 'slug' => 'cn-tuoi-nuoc-bon-phan',
                                'objective' => 'Biết thời điểm tưới nước phù hợp và nguyên tắc bón phân đúng liều lượng.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                                'instructions' => 'Tưới nước vào sáng sớm hoặc chiều mát. Bón phân đúng loại, đúng liều; bón quá nhiều phân hoá học sẽ làm cây bị cháy rễ.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function questions(): array
    {
        return [
            // ============ Bài 1: Quy tắc an toàn khi dùng điện ============
            'cn-quy-tac-an-toan-dien' => [
                'quiz' => [
                    ['Vì sao không được chạm vào ổ điện khi tay đang ướt?',
                        ['Vì nước dẫn điện gây giật', 'Vì làm bẩn ổ điện', 'Vì tay ướt trơn khó cắm', 'Vì ổ điện sẽ bị hỏng'], 0,
                        'Nước dẫn điện rất tốt; tay ướt chạm vào ổ điện hoặc thiết bị điện dễ bị điện giật nguy hiểm.', 'de'],
                    ['Trước khi sửa chữa thiết bị điện trong nhà, việc đầu tiên phải làm là gì?',
                        ['Ngắt nguồn điện', 'Đeo găng tay vải', 'Đứng trên ghế kim loại', 'Nhờ bạn giữ dây điện'], 0,
                        'Luôn ngắt cầu dao hoặc rút phích điện trước khi sửa chữa để đảm bảo không còn điện.', 'de'],
                    ['Khi phát hiện dây điện bị hở (tróc vỏ), em phải làm gì?',
                        ['Dùng tay nối lại', 'Báo người lớn và tránh xa', 'Quấn giấy rồi dùng tiếp', 'Cắm điện thử xem sao'], 1,
                        'Dây điện hở rất nguy hiểm; tuyệt đối không tự chạm vào, hãy tránh xa và báo người lớn xử lý.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi tình huống với đánh giá đúng/sai.',
                        [['Tay khô cắm phích điện', 'Đúng'], ['Tay ướt rút phích điện', 'Sai'], ['Thả diều gần đường dây điện', 'Sai'], ['Ngắt cầu dao trước khi thay bóng đèn', 'Đúng']],
                        'Tay khô và ngắt điện khi sửa chữa là đúng; tay ướt chạm điện và chơi gần đường dây điện là sai.', 'de'],
                    ['Nối mỗi vật với khả năng dẫn điện của nó.',
                        [['Dây đồng', 'Dẫn điện'], ['Tay cầm nhựa của kìm', 'Cách điện'], ['Nước', 'Dẫn điện'], ['Găng tay cao su', 'Cách điện']],
                        'Đồng và nước dẫn điện; nhựa và cao su cách điện nên được dùng làm tay cầm, găng tay bảo hộ.', 'trung_binh'],
                    ['Nối mỗi hành vi với đánh giá an toàn.',
                        [['Cắm nhiều thiết bị vào một ổ', 'Không an toàn'], ['Rút phích bằng cách cầm vào phích', 'An toàn'], ['Kéo dây điện để rút phích', 'Không an toàn'], ['Để ổ điện xa tầm tay trẻ nhỏ', 'An toàn']],
                        'Cắm quá tải và kéo dây khi rút phích dễ gây chập, đứt dây; cầm vào phích khi rút và để ổ xa trẻ nhỏ thì an toàn.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi việc làm vào nhóm "An toàn" hoặc "Nguy hiểm".',
                        [['Dùng tay khô cắm điện', 'An toàn'], ['Ngắt điện khi sửa chữa', 'An toàn'], ['Chạm ổ điện khi tay ướt', 'Nguy hiểm'], ['Tự ý nối dây điện hở', 'Nguy hiểm']],
                        'Tay khô và ngắt điện khi sửa là an toàn; tay ướt chạm điện và tự nối dây hở rất nguy hiểm.', 'de'],
                    ['Kéo mỗi vật vào nhóm "Dẫn điện" hoặc "Cách điện".',
                        [['Đồng', 'Dẫn điện'], ['Sắt', 'Dẫn điện'], ['Nhựa', 'Cách điện'], ['Cao su', 'Cách điện']],
                        'Kim loại như đồng, sắt dẫn điện; nhựa, cao su cách điện.', 'de'],
                    ['Kéo mỗi việc làm vào nhóm "Nên làm" hoặc "Không nên làm".',
                        [['Báo người lớn khi thấy dây điện hở', 'Nên làm'], ['Đứng xa khi thấy dây điện đứt rơi xuống', 'Nên làm'], ['Thả diều gần cột điện', 'Không nên làm'], ['Trèo lên cột điện', 'Không nên làm']],
                        'Thấy dây điện hở hoặc đứt phải tránh xa và báo người lớn; tuyệt đối không đến gần cột điện, đường dây điện.', 'de'],
                ],
                'fill' => [
                    ['Tay ___ không được chạm vào ổ điện vì nước dẫn điện.', [[0, 'ướt']],
                        'Tay ướt chạm vào thiết bị điện dễ bị điện giật.', 'de'],
                    ['Trước khi sửa thiết bị điện, phải ___ nguồn điện.', [[0, 'ngắt']],
                        'Ngắt cầu dao hoặc rút phích điện là việc đầu tiên và quan trọng nhất khi sửa chữa điện.', 'de'],
                    ['Khi thấy dây điện đứt rơi xuống đất, em phải tránh ___ và báo ngay cho người lớn.', [[0, 'xa']],
                        'Dây điện đứt vẫn có thể còn điện; phải đứng xa và báo người lớn, không tự ý đến gần.', 'de'],
                ],
            ],
            // ============ Bài 2: Xử lý khi gặp sự cố điện ============
            'cn-xu-ly-su-co-dien' => [
                'quiz' => [
                    ['Khi thấy người bị điện giật, việc đầu tiên em nên làm là gì?',
                        ['Kéo người đó ra ngay bằng tay', 'Ngắt nguồn điện rồi mới chạm vào nạn nhân', 'Dội nước vào người đó', 'Chạy đi gọi bạn đến xem'], 1,
                        'Tuyệt đối không chạm tay vào người đang bị giật; phải ngắt điện hoặc dùng vật cách điện gạt dây ra rồi mới cứu.', 'trung_binh'],
                    ['Khi ngửi thấy mùi khét từ ổ điện, em nên làm gì?',
                        ['Tiếp tục dùng bình thường', 'Ngắt cầu dao và báo người lớn', 'Đổ nước vào ổ điện', 'Dùng tay kiểm tra ổ điện'], 1,
                        'Mùi khét có thể là dấu hiệu chập điện, dễ gây cháy; phải ngắt điện và báo người lớn ngay.', 'de'],
                    ['Vật nào có thể dùng để gạt dây điện ra khỏi người bị nạn?',
                        ['Cây gậy gỗ khô', 'Thanh sắt', 'Khăn ướt', 'Dây thép'], 0,
                        'Gỗ khô cách điện nên an toàn; vật kim loại hoặc ướt sẽ dẫn điện gây nguy hiểm cho người cứu.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi sự cố với cách xử lý đúng.',
                        [['Chập điện có mùi khét', 'Ngắt cầu dao, báo người lớn'], ['Người bị điện giật', 'Ngắt điện rồi mới cứu'], ['Dây điện đứt rơi xuống đường', 'Đứng xa, báo người lớn'], ['Bóng đèn bị cháy', 'Ngắt điện rồi thay bóng mới']],
                        'Sự cố điện nào cũng bắt đầu bằng việc ngắt điện và báo người lớn; không tự ý xử lý khi chưa ngắt điện.', 'trung_binh'],
                    ['Nối mỗi đồ vật với việc có dùng được để cứu người bị giật hay không.',
                        [['Gậy gỗ khô', 'Được'], ['Ghế nhựa khô', 'Được'], ['Thanh sắt', 'Không được'], ['Khăn ướt', 'Không được']],
                        'Chỉ dùng vật cách điện và khô ráo như gậy gỗ khô, ghế nhựa khô; vật kim loại và vật ướt dẫn điện.', 'trung_binh'],
                    ['Nối mỗi số điện thoại với trường hợp cần gọi.',
                        [['114', 'Báo cháy'], ['115', 'Cấp cứu y tế'], ['113', 'Báo công an'], ['Người lớn trong nhà', 'Báo ngay khi có sự cố điện']],
                        '114 là cứu hoả, 115 là cấp cứu, 113 là công an; với trẻ em, báo ngay cho người lớn trong nhà là quan trọng nhất.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi việc làm vào nhóm "Đúng" hoặc "Sai" khi cứu người bị điện giật.',
                        [['Ngắt nguồn điện trước', 'Đúng'], ['Dùng gậy gỗ khô gạt dây điện', 'Đúng'], ['Dùng tay kéo nạn nhân', 'Sai'], ['Dội nước vào nạn nhân', 'Sai']],
                        'Phải ngắt điện trước và chỉ dùng vật cách điện; dùng tay kéo hoặc dội nước đều làm người cứu bị giật theo.', 'trung_binh'],
                    ['Kéo mỗi vật vào nhóm "Cách điện" hoặc "Dẫn điện".',
                        [['Gậy gỗ khô', 'Cách điện'], ['Ghế nhựa', 'Cách điện'], ['Dây đồng', 'Dẫn điện'], ['Thanh sắt', 'Dẫn điện']],
                        'Gỗ khô và nhựa cách điện; đồng và sắt dẫn điện.', 'de'],
                    ['Kéo mỗi việc làm vào nhóm "Nên làm" hoặc "Không nên làm" khi ổ điện có mùi khét.',
                        [['Ngắt cầu dao', 'Nên làm'], ['Báo người lớn', 'Nên làm'], ['Tiếp tục dùng ổ điện đó', 'Không nên làm'], ['Đổ nước vào ổ điện', 'Không nên làm']],
                        'Mùi khét là dấu hiệu chập điện: ngắt cầu dao, báo người lớn; không dùng tiếp và tuyệt đối không đổ nước vào.', 'de'],
                ],
                'fill' => [
                    ['Khi cứu người bị điện giật, tuyệt đối không dùng ___ trần chạm vào nạn nhân.', [[0, 'tay']],
                        'Người đang bị giật vẫn còn điện; chạm tay vào sẽ làm người cứu bị giật theo.', 'trung_binh'],
                    ['Số điện thoại báo cháy là ___.', [[0, '114']],
                        '114 là số điện thoại của lực lượng phòng cháy chữa cháy.', 'de'],
                    ['Khi ngửi thấy mùi khét ở ổ điện, em phải ___ cầu dao ngay.', [[0, 'ngắt']],
                        'Ngắt cầu dao để cắt điện, ngăn chập điện gây cháy rồi báo người lớn.', 'de'],
                ],
            ],
            // ============ Bài 3: Gỗ, kim loại và nhựa ============
            'cn-go-kim-loai-nhua' => [
                'quiz' => [
                    ['Vật liệu nào dễ bị gỉ khi để ngoài trời mưa?',
                        ['Sắt', 'Nhựa', 'Gỗ đã sơn', 'Thuỷ tinh'], 0,
                        'Sắt (kim loại) dễ bị gỉ sét khi tiếp xúc với nước và không khí ẩm.', 'de'],
                    ['Vì sao tay cầm của kìm, tua vít thường được bọc nhựa?',
                        ['Vì nhựa cách điện, cách nhiệt', 'Vì nhựa rẻ tiền', 'Vì nhựa đẹp mắt', 'Vì nhựa nặng tay'], 0,
                        'Nhựa cách điện và cách nhiệt nên cầm an toàn, không bị giật hay bỏng khi sử dụng.', 'trung_binh'],
                    ['Vật liệu nào nhẹ, không thấm nước, thường dùng làm chai lọ?',
                        ['Nhựa', 'Gỗ', 'Sắt', 'Giấy'], 0,
                        'Nhựa nhẹ, bền, không thấm nước nên được dùng làm chai lọ, hộp đựng, áo mưa.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi vật liệu với tính chất nổi bật của nó.',
                        [['Gỗ', 'Nhẹ, dễ gia công'], ['Sắt', 'Cứng chắc, dễ gỉ'], ['Nhựa', 'Nhẹ, không thấm nước'], ['Inox', 'Sáng bóng, khó gỉ']],
                        'Gỗ nhẹ dễ gia công; sắt cứng nhưng dễ gỉ; nhựa nhẹ không thấm nước; inox sáng bóng khó gỉ.', 'de'],
                    ['Nối mỗi vật liệu với đồ vật thường làm từ nó.',
                        [['Gỗ', 'Bàn ghế'], ['Sắt', 'Cổng nhà'], ['Nhựa', 'Chai nước'], ['Nhôm', 'Nồi nấu ăn']],
                        'Bàn ghế thường làm bằng gỗ, cổng nhà bằng sắt, chai nước bằng nhựa, nồi nấu ăn bằng nhôm.', 'de'],
                    ['Nối mỗi tính chất với vật liệu có tính chất đó.',
                        [['Dễ bị gỉ', 'Sắt'], ['Cách điện', 'Nhựa'], ['Nổi trên mặt nước', 'Gỗ'], ['Dẫn điện tốt', 'Đồng']],
                        'Sắt dễ gỉ, nhựa cách điện, gỗ nhẹ nổi trên nước, đồng dẫn điện tốt.', 'trung_binh'],
                ],
                'sort' => [
                    ['Kéo mỗi vật liệu vào nhóm "Tự nhiên" hoặc "Nhân tạo".',
                        [['Gỗ', 'Tự nhiên'], ['Tre', 'Tự nhiên'], ['Nhựa', 'Nhân tạo'], ['Thuỷ tinh', 'Nhân tạo']],
                        'Gỗ, tre có sẵn trong tự nhiên; nhựa, thuỷ tinh do con người chế tạo.', 'de'],
                    ['Kéo mỗi vật liệu vào nhóm "Bị gỉ" hoặc "Không bị gỉ".',
                        [['Sắt', 'Bị gỉ'], ['Đinh sắt', 'Bị gỉ'], ['Inox', 'Không bị gỉ'], ['Nhựa', 'Không bị gỉ']],
                        'Sắt và đồ làm bằng sắt bị gỉ ngoài không khí ẩm; inox khó gỉ, nhựa không bị gỉ.', 'de'],
                    ['Kéo mỗi đồ vật vào nhóm "Đồ dùng học tập" hoặc "Đồ gia dụng lớn".',
                        [['Thước nhựa', 'Đồ dùng học tập'], ['Bút bi', 'Đồ dùng học tập'], ['Bàn gỗ', 'Đồ gia dụng lớn'], ['Tủ sắt', 'Đồ gia dụng lớn']],
                        'Thước, bút là đồ dùng học tập; bàn, tủ là đồ gia dụng lớn trong nhà.', 'de'],
                ],
                'fill' => [
                    ['Sắt để ngoài trời mưa lâu ngày sẽ bị ___.', [[0, 'gỉ']],
                        'Sắt tiếp xúc với nước và không khí ẩm sẽ bị gỉ sét.', 'de'],
                    ['___ nhẹ, không thấm nước nên thường dùng làm áo mưa, chai lọ.', [[0, 'nhựa']],
                        'Nhựa nhẹ, bền và không thấm nước nên được dùng làm nhiều đồ dùng hằng ngày.', 'de'],
                    ['Bàn ghế trong lớp học thường được làm bằng ___.', [[0, 'gỗ']],
                        'Gỗ nhẹ, dễ gia công và chắc chắn nên thường dùng làm bàn ghế.', 'de'],
                ],
            ],
            // ============ Bài 4: Búa, kìm, tua vít và cưa ============
            'cn-bua-kim-tua-vit-cua' => [
                'quiz' => [
                    ['Muốn đóng đinh vào gỗ, em dùng dụng cụ nào?',
                        ['Búa', 'Kìm', 'Tua vít', 'Cưa'], 0,
                        'Búa dùng để đóng đinh, gõ các vật; kìm để kẹp cắt, tua vít để vặn ốc, cưa để cắt gỗ.', 'de'],
                    ['Dụng cụ nào dùng để vặn chặt hoặc tháo ốc vít?',
                        ['Tua vít', 'Búa', 'Cưa', 'Kìm'], 0,
                        'Tua vít (tuốc-nơ-vít) có đầu vừa với rãnh ốc vít nên dùng để vặn ốc.', 'de'],
                    ['Khi dùng dụng cụ sắc nhọn như cưa, em cần chú ý điều gì?',
                        ['Cầm chắc, cắt xa tay và có người lớn hướng dẫn', 'Cưa thật nhanh cho xong', 'Một tay giữ lưỡi cưa', 'Vừa cưa vừa đùa giỡn'], 0,
                        'Dụng cụ sắc nhọn dễ gây đứt tay; phải có người lớn hướng dẫn, cầm chắc và giữ tay xa lưỡi cưa.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi dụng cụ với công dụng của nó.',
                        [['Búa', 'Đóng đinh'], ['Kìm', 'Kẹp, cắt dây'], ['Tua vít', 'Vặn ốc vít'], ['Cưa', 'Cắt gỗ']],
                        'Búa đóng đinh, kìm kẹp và cắt dây, tua vít vặn ốc, cưa cắt gỗ.', 'de'],
                    ['Nối mỗi dụng cụ với vật liệu làm tay cầm của nó.',
                        [['Búa', 'Cán gỗ'], ['Kìm', 'Tay cầm bọc nhựa'], ['Tua vít', 'Tay cầm nhựa'], ['Cưa', 'Tay cầm gỗ']],
                        'Tay cầm thường làm bằng gỗ hoặc bọc nhựa để cầm chắc tay, cách điện và êm tay.', 'trung_binh'],
                    ['Nối mỗi việc cần làm với dụng cụ phù hợp.',
                        [['Treo tranh cần đóng đinh', 'Búa'], ['Tháo ốc của đồ chơi', 'Tua vít'], ['Cắt dây thép nhỏ', 'Kìm'], ['Cắt tấm gỗ mỏng', 'Cưa']],
                        'Đóng đinh dùng búa, tháo ốc dùng tua vít, cắt dây thép dùng kìm, cắt gỗ dùng cưa.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi dụng cụ vào nhóm "Dụng cụ cầm tay" hoặc "Dụng cụ dùng điện".',
                        [['Búa', 'Dụng cụ cầm tay'], ['Tua vít', 'Dụng cụ cầm tay'], ['Máy khoan', 'Dụng cụ dùng điện'], ['Máy cắt', 'Dụng cụ dùng điện']],
                        'Búa, tua vít dùng sức tay; máy khoan, máy cắt chạy bằng điện.', 'de'],
                    ['Kéo mỗi dụng cụ vào nhóm "Để đóng, gõ" hoặc "Để cắt".',
                        [['Búa', 'Để đóng, gõ'], ['Búa cao su', 'Để đóng, gõ'], ['Cưa', 'Để cắt'], ['Kéo', 'Để cắt']],
                        'Búa dùng để đóng, gõ; cưa và kéo dùng để cắt.', 'de'],
                    ['Kéo mỗi việc làm vào nhóm "An toàn" hoặc "Nguy hiểm" khi dùng dụng cụ.',
                        [['Đeo kính bảo hộ khi cưa', 'An toàn'], ['Cất dụng cụ gọn gàng sau khi dùng', 'An toàn'], ['Cầm lưỡi cưa bằng tay', 'Nguy hiểm'], ['Đùa giỡn khi đang cầm búa', 'Nguy hiểm']],
                        'Dùng đồ bảo hộ và cất gọn dụng cụ thì an toàn; cầm vào lưỡi sắc và đùa giỡn khi dùng dụng cụ thì nguy hiểm.', 'trung_binh'],
                ],
                'fill' => [
                    ['Để vặn chặt ốc vít, em dùng ___.', [[0, 'tua vít']],
                        'Tua vít có đầu vừa với rãnh ốc nên vặn ốc dễ dàng.', 'de'],
                    ['___ dùng để đóng đinh vào gỗ.', [[0, 'búa']],
                        'Búa là dụng cụ dùng để đóng đinh và gõ các vật.', 'de'],
                    ['Sau khi dùng xong, phải cất dụng cụ ___ để tránh tai nạn.', [[0, 'gọn gàng']],
                        'Dụng cụ để bừa bãi, nhất là vật sắc nhọn, dễ gây tai nạn cho mọi người.', 'de'],
                ],
            ],
            // ============ Bài 5: Các bước trồng cây ============
            'cn-cac-buoc-trong-cay' => [
                'quiz' => [
                    ['Bước đầu tiên khi trồng cây là gì?',
                        ['Làm đất tơi xốp', 'Tưới thật nhiều nước', 'Bón thật nhiều phân', 'Hái quả'], 0,
                        'Đất phải được xới tơi xốp trước để rễ cây dễ phát triển và thoát nước tốt.', 'de'],
                    ['Vì sao phải xới đất tơi xốp trước khi gieo hạt?',
                        ['Để rễ dễ mọc và đất thoáng khí', 'Để đất đẹp mắt', 'Để chim không ăn hạt', 'Để đất khô nhanh'], 0,
                        'Đất tơi xốp giúp rễ đâm sâu, hút nước và chất dinh dưỡng dễ dàng.', 'trung_binh'],
                    ['Ngay sau khi gieo hạt, em cần làm gì?',
                        ['Tưới nước nhẹ nhàng', 'Phơi nắng gắt', 'Đào hạt lên xem', 'Bón phân đậm đặc'], 0,
                        'Hạt cần độ ẩm để nảy mầm; tưới nhẹ để không làm trôi hạt.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi bước trồng cây với thứ tự của nó.',
                        [['Làm đất', 'Bước 1'], ['Gieo hạt', 'Bước 2'], ['Tưới nước', 'Bước 3'], ['Chăm sóc', 'Bước 4']],
                        'Trình tự trồng cây: làm đất → gieo hạt → tưới nước → chăm sóc.', 'de'],
                    ['Nối mỗi loại cây với cách trồng của nó.',
                        [['Rau muống', 'Gieo hạt'], ['Khoai lang', 'Giâm cành'], ['Lúa', 'Gieo mạ rồi cấy'], ['Cây ăn quả', 'Trồng cây con']],
                        'Rau muống gieo hạt, khoai lang giâm cành, lúa gieo mạ rồi cấy, cây ăn quả trồng cây con.', 'trung_binh'],
                    ['Nối mỗi dụng cụ với công việc của nó.',
                        [['Cuốc', 'Xới đất'], ['Bình tưới', 'Tưới nước'], ['Xẻng', 'Đào hố'], ['Kéo', 'Tỉa cành']],
                        'Cuốc xới đất, bình tưới để tưới nước, xẻng đào hố, kéo tỉa cành.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi việc làm vào nhóm "Trước khi gieo" hoặc "Sau khi gieo".',
                        [['Xới đất', 'Trước khi gieo'], ['Bón lót', 'Trước khi gieo'], ['Tưới nước', 'Sau khi gieo'], ['Nhổ cỏ', 'Sau khi gieo']],
                        'Xới đất và bón lót làm trước khi gieo; tưới nước và nhổ cỏ làm sau khi gieo.', 'de'],
                    ['Kéo mỗi loại cây vào nhóm "Trồng bằng hạt" hoặc "Trồng bằng hom, cành".',
                        [['Lúa', 'Trồng bằng hạt'], ['Đậu', 'Trồng bằng hạt'], ['Khoai lang', 'Trồng bằng hom, cành'], ['Sắn', 'Trồng bằng hom, cành']],
                        'Lúa, đậu trồng bằng hạt; khoai lang, sắn trồng bằng hom, cành.', 'trung_binh'],
                    ['Kéo mỗi loại đất vào nhóm "Tốt cho cây" hoặc "Xấu cho cây".',
                        [['Đất tơi xốp', 'Tốt cho cây'], ['Đất nhiều mùn', 'Tốt cho cây'], ['Đất bị nén chặt', 'Xấu cho cây'], ['Đất ngập úng', 'Xấu cho cây']],
                        'Đất tơi xốp, nhiều mùn tốt cho cây; đất nén chặt và đất ngập úng làm cây khó sống.', 'de'],
                ],
                'fill' => [
                    ['Trước khi gieo hạt, cần xới đất cho ___ xốp.', [[0, 'tơi']],
                        'Đất tơi xốp giúp rễ cây dễ đâm sâu và hút chất dinh dưỡng.', 'de'],
                    ['Muốn hạt nảy mầm, đất phải đủ độ ___.', [[0, 'ẩm']],
                        'Hạt giống cần độ ẩm thích hợp mới nảy mầm được.', 'de'],
                    ['Rau muống thường được trồng bằng cách gieo ___.', [[0, 'hạt']],
                        'Rau muống là loại rau gieo hạt phổ biến, dễ trồng.', 'de'],
                ],
            ],
            // ============ Bài 6: Tưới nước và bón phân ============
            'cn-tuoi-nuoc-bon-phan' => [
                'quiz' => [
                    ['Nên tưới nước cho cây vào thời điểm nào trong ngày?',
                        ['Giữa trưa nắng gắt', 'Sáng sớm hoặc chiều mát', 'Nửa đêm', 'Bất cứ lúc nào'], 1,
                        'Tưới lúc sáng sớm hoặc chiều mát giúp cây hút nước tốt, tránh sốc nhiệt giữa trưa nắng.', 'de'],
                    ['Bón quá nhiều phân hoá học sẽ gây ra điều gì?',
                        ['Cây lớn siêu nhanh', 'Cây bị cháy rễ và chết', 'Đất càng tốt hơn', 'Không ảnh hưởng gì'], 1,
                        'Phân hoá học quá liều làm "cháy" rễ, cây héo và chết; phải bón đúng liều lượng hướng dẫn.', 'trung_binh'],
                    ['Dấu hiệu nào cho thấy cây đang bị thiếu nước?',
                        ['Lá héo rũ xuống', 'Lá xanh mướt', 'Cây ra nhiều hoa', 'Rễ mọc dài'], 0,
                        'Lá héo, rũ xuống là dấu hiệu cây thiếu nước, cần tưới ngay.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi loại phân với nguồn gốc của nó.',
                        [['Phân chuồng', 'Từ chăn nuôi'], ['Phân xanh', 'Từ cây cỏ ủ'], ['Phân hoá học', 'Từ nhà máy'], ['Phân vi sinh', 'Từ vi sinh vật']],
                        'Phân chuồng từ chăn nuôi, phân xanh từ cây cỏ ủ, phân hoá học từ nhà máy, phân vi sinh từ vi sinh vật.', 'trung_binh'],
                    ['Nối mỗi thời điểm với việc nên làm.',
                        [['Sáng sớm', 'Tưới nước'], ['Chiều mát', 'Tưới nước'], ['Giữa trưa nắng', 'Không tưới'], ['Mùa mưa nhiều', 'Giảm tưới']],
                        'Tưới vào sáng sớm hoặc chiều mát; không tưới giữa trưa nắng và giảm tưới vào mùa mưa.', 'de'],
                    ['Nối mỗi dấu hiệu của cây với nguyên nhân.',
                        [['Lá vàng úa', 'Thiếu chất dinh dưỡng'], ['Lá héo rũ', 'Thiếu nước'], ['Cây còi cọc', 'Đất nghèo dinh dưỡng'], ['Rễ bị thối', 'Tưới quá nhiều nước']],
                        'Lá vàng là thiếu dinh dưỡng, lá héo là thiếu nước, cây còi là đất nghèo, rễ thối là úng nước.', 'trung_binh'],
                ],
                'sort' => [
                    ['Kéo mỗi loại phân vào nhóm "Phân hữu cơ" hoặc "Phân hoá học".',
                        [['Phân chuồng', 'Phân hữu cơ'], ['Phân xanh', 'Phân hữu cơ'], ['Phân đạm (urê)', 'Phân hoá học'], ['Phân lân', 'Phân hoá học']],
                        'Phân chuồng, phân xanh là phân hữu cơ từ tự nhiên; phân đạm, phân lân là phân hoá học từ nhà máy.', 'trung_binh'],
                    ['Kéo mỗi thời điểm vào nhóm "Nên tưới" hoặc "Không nên tưới".',
                        [['Sáng sớm', 'Nên tưới'], ['Chiều mát', 'Nên tưới'], ['Giữa trưa nắng gắt', 'Không nên tưới'], ['Khi đất còn ướt sũng', 'Không nên tưới']],
                        'Nên tưới sáng sớm, chiều mát; không tưới giữa trưa nắng và khi đất còn ướt sũng.', 'de'],
                    ['Kéo mỗi việc làm vào nhóm "Tốt cho cây" hoặc "Hại cây".',
                        [['Tưới đủ nước', 'Tốt cho cây'], ['Bón phân đúng liều', 'Tốt cho cây'], ['Tưới quá nhiều gây úng', 'Hại cây'], ['Bón phân quá liều', 'Hại cây']],
                        'Tưới đủ và bón đúng liều thì tốt; tưới úng và bón quá liều thì hại cây.', 'de'],
                ],
                'fill' => [
                    ['Nên tưới cây vào ___ sớm hoặc chiều mát.', [[0, 'sáng']],
                        'Sáng sớm trời mát, cây hút nước tốt và ít bị sốc nhiệt.', 'de'],
                    ['Bón phân quá liều sẽ làm cây bị "___" rễ.', [[0, 'cháy']],
                        'Phân hoá học quá liều làm rễ cây bị "cháy", cây héo dần và chết.', 'trung_binh'],
                    ['Lá cây héo rũ là dấu hiệu cây đang thiếu ___.', [[0, 'nước']],
                        'Khi thiếu nước, lá cây héo rũ xuống; tưới nước kịp thời cây sẽ tươi lại.', 'de'],
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
