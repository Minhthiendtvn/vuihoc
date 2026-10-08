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
 * Môn Âm nhạc — nội dung TỰ VIẾT 100% TIẾNG VIỆT, bám chương trình phổ thông.
 * 3 chủ đề (lớp 6-7 / 7-8 / 8-9), 6 kỹ năng, 6 bài học, mỗi bài 12 câu
 * (3 quiz + 3 matching + 3 sort + 3 fill) = 72 câu.
 * Idempotent: updateOrCreate theo slug; câu hỏi kiểm tra exists theo
 * (lesson_id, game_type) trước khi seed.
 */
class SubjectAmNhacSeeder extends Seeder
{
    private array $lessonBySlug = [];

    public function run(): void
    {
        $subject = Subject::updateOrCreate(
            ['slug' => 'am-nhac'],
            [
                'name' => 'Âm nhạc', 'icon' => '🎵', 'color' => '#a855f7',
                'description' => 'Học nhạc lý qua game: nốt nhạc, cao độ và nhạc cụ dân tộc.',
                'sort_order' => 11, 'is_published' => true, 'is_demo' => true,
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
                'name' => 'Nốt nhạc và cao độ', 'slug' => 'an-not-nhac-cao-do',
                'description' => 'Bảy nốt nhạc cơ bản, cao độ và trường độ của nốt nhạc.',
                'icon' => '🎼', 'sort_order' => 1, 'grade_min' => 6, 'grade_max' => 7,
                'skills' => [
                    [
                        'name' => 'Bảy nốt nhạc', 'slug' => 'kn-an-not-nhac-1',
                        'description' => 'Đọc tên và nhớ thứ tự bảy nốt nhạc cơ bản.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Bảy nốt nhạc', 'slug' => 'an-bay-not-nhac',
                                'objective' => 'Đọc đúng tên và thứ tự bảy nốt nhạc: Đồ, Rê, Mi, Fa, Sol, La, Si.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Học thuộc thứ tự 7 nốt nhạc: Đồ – Rê – Mi – Fa – Sol – La – Si. Đồ thấp nhất, Si cao nhất.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Cao độ và trường độ', 'slug' => 'kn-an-cao-do-1',
                        'description' => 'Phân biệt cao độ, trường độ và các hình nốt nhạc.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Cao độ và trường độ', 'slug' => 'an-cao-do-truong-do',
                                'objective' => 'Phân biệt cao độ với trường độ; nhận biết nốt tròn, trắng, đen, móc đơn.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Cao độ: âm cao hay thấp (do vị trí nốt trên khuông nhạc). Trường độ: âm dài hay ngắn (do hình dạng nốt).',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Nhịp và dấu lặng', 'slug' => 'an-nhip-dau-lang',
                'description' => 'Nhịp 2/4, nhịp 3/4 và các loại dấu lặng trong bản nhạc.',
                'icon' => '🥁', 'sort_order' => 2, 'grade_min' => 7, 'grade_max' => 8,
                'skills' => [
                    [
                        'name' => 'Nhịp 2/4 và 3/4', 'slug' => 'kn-an-nhip-1',
                        'description' => 'Hiểu ý nghĩa số chỉ nhịp và phân biệt nhịp 2/4 với 3/4.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Nhịp 2/4 và nhịp 3/4', 'slug' => 'an-nhip-2-4-3-4',
                                'objective' => 'Hiểu ý nghĩa của số chỉ nhịp; phân biệt nhịp 2/4 và nhịp 3/4.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 12,
                                'instructions' => 'Số trên chỉ số phách trong một ô nhịp, số dưới chỉ nốt làm đơn vị phách. Nhịp 2/4 có 2 phách, nhịp 3/4 có 3 phách; phách đầu luôn mạnh.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Dấu lặng', 'slug' => 'kn-an-dau-lang-1',
                        'description' => 'Nhận biết các loại dấu lặng và ý nghĩa của chúng.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Các loại dấu lặng', 'slug' => 'an-cac-dau-lang',
                                'objective' => 'Nêu được công dụng của dấu lặng và phân biệt lặng tròn, lặng trắng, lặng đen.',
                                'difficulty' => 'trung_binh', 'duration_minutes' => 10,
                                'instructions' => 'Dấu lặng chỉ chỗ nghỉ, không phát ra âm thanh. Mỗi loại nốt đều có dấu lặng tương ứng với độ dài nghỉ bằng nhau.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Nhạc cụ dân tộc', 'slug' => 'an-nhac-cu-dan-toc',
                'description' => 'Nhạc cụ dân tộc Việt Nam và cách hát đúng giai điệu.',
                'icon' => '🎻', 'sort_order' => 3, 'grade_min' => 8, 'grade_max' => 9,
                'skills' => [
                    [
                        'name' => 'Nhạc cụ dân tộc Việt Nam', 'slug' => 'kn-an-nhac-cu-1',
                        'description' => 'Nhận biết đàn bầu, đàn tranh, sáo, trống cơm.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Đàn bầu, đàn tranh, sáo, trống cơm', 'slug' => 'an-dan-bau-tranh-sao-trong',
                                'objective' => 'Nhận biết được đàn bầu, đàn tranh, sáo, trống cơm và cách chơi của chúng.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Đàn bầu một dây, đàn tranh nhiều dây, sáo thổi bằng hơi, trống cơm vỗ bằng tay. Đây đều là nhạc cụ dân tộc Việt Nam.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Bài hát thiếu nhi', 'slug' => 'kn-an-bai-hat-1',
                        'description' => 'Biết cách hát đúng giai điệu bài hát thiếu nhi.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Hát đúng giai điệu', 'slug' => 'an-hat-bai-thieu-nhi',
                                'objective' => 'Biết các yếu tố để hát đúng giai điệu: nghe mẫu, lấy hơi, giữ nhịp.',
                                'difficulty' => 'de', 'duration_minutes' => 10,
                                'instructions' => 'Muốn hát hay: nghe kỹ giai điệu mẫu, lấy hơi đúng chỗ, giữ đúng nhịp và phát âm rõ lời.',
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
            // ============ Bài 1: Bảy nốt nhạc ============
            'an-bay-not-nhac' => [
                'quiz' => [
                    ['Có tất cả bao nhiêu nốt nhạc cơ bản?',
                        ['5', '6', '7', '8'], 2,
                        'Có 7 nốt nhạc cơ bản: Đồ, Rê, Mi, Fa, Sol, La, Si.', 'de'],
                    ['Nốt nào đứng ngay sau nốt Mi?',
                        ['Rê', 'Fa', 'Sol', 'La'], 1,
                        'Thứ tự 7 nốt nhạc là: Đồ – Rê – Mi – Fa – Sol – La – Si.', 'de'],
                    ['Nốt nhạc nào có cao độ thấp nhất trong 7 nốt cơ bản?',
                        ['Si', 'La', 'Đồ', 'Sol'], 2,
                        'Đồ là nốt thấp nhất, Si là nốt cao nhất trong thang 7 nốt cơ bản.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi nốt nhạc với vị trí của nó trong thang nhạc.',
                        [['Đồ', 'Nốt thứ 1'], ['Rê', 'Nốt thứ 2'], ['Mi', 'Nốt thứ 3'], ['Sol', 'Nốt thứ 5']],
                        'Thứ tự: Đồ (1) – Rê (2) – Mi (3) – Fa (4) – Sol (5) – La (6) – Si (7).', 'de'],
                    ['Nối mỗi nốt nhạc với ký hiệu chữ cái quốc tế.',
                        [['Đồ', 'C'], ['Rê', 'D'], ['Mi', 'E'], ['Fa', 'F']],
                        'Ký hiệu quốc tế: Đồ=C, Rê=D, Mi=E, Fa=F, Sol=G, La=A, Si=B.', 'trung_binh'],
                    ['Nối mỗi nốt nhạc với cách gọi khác của nó.',
                        [['Đồ', 'Còn gọi là Do'], ['Sol', 'Còn gọi là Son'], ['Si', 'Còn gọi là Ti']],
                        'Theo cách gọi quốc tế: Đồ còn gọi là Do, Sol còn gọi là Son, Si còn gọi là Ti.', 'trung_binh'],
                ],
                'sort' => [
                    ['Kéo mỗi nốt nhạc vào nhóm "Nốt thấp" hoặc "Nốt cao".',
                        [['Đồ', 'Nốt thấp'], ['Rê', 'Nốt thấp'], ['La', 'Nốt cao'], ['Si', 'Nốt cao']],
                        'Trong thang 7 nốt, Đồ và Rê thuộc nhóm thấp; La và Si thuộc nhóm cao.', 'de'],
                    ['Kéo mỗi nốt nhạc vào nhóm "Nửa đầu" hoặc "Nửa sau" của thang nhạc.',
                        [['Đồ', 'Nửa đầu'], ['Mi', 'Nửa đầu'], ['La', 'Nửa sau'], ['Si', 'Nửa sau']],
                        'Nửa đầu thang nhạc: Đồ, Rê, Mi; nửa sau: Fa, Sol, La, Si.', 'de'],
                    ['Kéo mỗi tên nốt nhạc vào nhóm theo số chữ cái.',
                        [['Đồ', '2 chữ cái'], ['Rê', '2 chữ cái'], ['Fa', '2 chữ cái'], ['Sol', '3 chữ cái']],
                        'Đồ, Rê, Mi, Fa, La, Si có 2 chữ cái; Sol có 3 chữ cái.', 'de'],
                ],
                'fill' => [
                    ['Thang nhạc cơ bản gồm ___ nốt nhạc.', [[0, '7']],
                        'Thang nhạc cơ bản có 7 nốt: Đồ, Rê, Mi, Fa, Sol, La, Si.', 'de'],
                    ['Sau nốt Rê là nốt ___.', [[0, 'mi']],
                        'Thứ tự: Đồ – Rê – Mi – Fa – Sol – La – Si.', 'de'],
                    ['Nốt ___ là nốt có cao độ cao nhất trong 7 nốt cơ bản.', [[0, 'si']],
                        'Si đứng cuối thang 7 nốt nên có cao độ cao nhất.', 'de'],
                ],
            ],
            // ============ Bài 2: Cao độ và trường độ ============
            'an-cao-do-truong-do' => [
                'quiz' => [
                    ['"Cao độ" của nốt nhạc cho biết điều gì?',
                        ['Nốt nhạc cao hay thấp', 'Nốt nhạc dài hay ngắn', 'Nốt nhạc to hay nhỏ', 'Nốt nhạc nhanh hay chậm'], 0,
                        'Cao độ cho biết âm thanh cao hay thấp; trường độ cho biết âm dài hay ngắn.', 'de'],
                    ['"Trường độ" của nốt nhạc cho biết điều gì?',
                        ['Nốt ngân dài hay ngắn', 'Nốt cao hay thấp', 'Nốt to hay nhỏ', 'Nốt vui hay buồn'], 0,
                        'Trường độ là độ dài ngắn của âm thanh: nốt tròn ngân dài nhất, nốt móc ngắn hơn.', 'de'],
                    ['Nốt nhạc nào ngân dài nhất?',
                        ['Nốt tròn', 'Nốt trắng', 'Nốt đen', 'Nốt móc đơn'], 0,
                        'Nốt tròn có trường độ dài nhất, bằng 2 nốt trắng và bằng 4 nốt đen.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi nốt nhạc với hình dạng của nó.',
                        [['Nốt tròn', 'Hình bầu dục rỗng'], ['Nốt trắng', 'Bầu dục rỗng có đuôi'], ['Nốt đen', 'Bầu dục đặc có đuôi'], ['Nốt móc đơn', 'Nốt đen có thêm móc']],
                        'Nốt tròn rỗng không đuôi; nốt trắng rỗng có đuôi; nốt đen đặc có đuôi; nốt móc đơn có thêm móc.', 'trung_binh'],
                    ['Nối mỗi nốt nhạc với độ dài tương đối của nó.',
                        [['Nốt tròn', 'Dài nhất'], ['Nốt trắng', 'Bằng nửa nốt tròn'], ['Nốt đen', 'Bằng nửa nốt trắng'], ['Nốt móc đơn', 'Bằng nửa nốt đen']],
                        'Mỗi nốt đứng sau dài bằng một nửa nốt đứng trước: tròn → trắng → đen → móc đơn.', 'trung_binh'],
                    ['Nối mỗi thuật ngữ với ý nghĩa của nó.',
                        [['Cao độ', 'Âm cao hay thấp'], ['Trường độ', 'Âm dài hay ngắn'], ['Cường độ', 'Âm to hay nhỏ'], ['Âm sắc', 'Màu sắc riêng của âm thanh']],
                        'Cao độ: cao thấp; trường độ: dài ngắn; cường độ: to nhỏ; âm sắc: màu sắc riêng của mỗi nhạc cụ, giọng hát.', 'trung_binh'],
                ],
                'sort' => [
                    ['Kéo mỗi nốt nhạc vào nhóm "Ngân dài" hoặc "Ngân ngắn".',
                        [['Nốt tròn', 'Ngân dài'], ['Nốt trắng', 'Ngân dài'], ['Nốt đen', 'Ngân ngắn'], ['Nốt móc đơn', 'Ngân ngắn']],
                        'Nốt tròn và nốt trắng ngân dài; nốt đen và nốt móc đơn ngân ngắn.', 'de'],
                    ['Kéo mỗi nốt nhạc vào nhóm "Rỗng ruột" hoặc "Đặc ruột".',
                        [['Nốt tròn', 'Rỗng ruột'], ['Nốt trắng', 'Rỗng ruột'], ['Nốt đen', 'Đặc ruột'], ['Nốt móc đơn', 'Đặc ruột']],
                        'Nốt tròn và nốt trắng rỗng ruột; nốt đen và nốt móc đơn đặc ruột.', 'de'],
                    ['Kéo mỗi yếu tố vào nhóm "Liên quan cao độ" hoặc "Liên quan trường độ".',
                        [['Vị trí nốt trên khuông nhạc', 'Liên quan cao độ'], ['Nốt nằm ở dòng kẻ cao', 'Liên quan cao độ'], ['Hình dạng nốt nhạc', 'Liên quan trường độ'], ['Nốt tròn hay nốt đen', 'Liên quan trường độ']],
                        'Vị trí nốt trên khuông nhạc quyết định cao độ; hình dạng nốt quyết định trường độ.', 'trung_binh'],
                ],
                'fill' => [
                    ['Nốt nhạc nằm càng cao trên khuông nhạc thì cao độ càng ___.', [[0, 'cao']],
                        'Vị trí nốt trên khuông nhạc quyết định cao độ: càng lên cao âm càng cao.', 'de'],
                    ['Nốt ___ có trường độ dài nhất.', [[0, 'tròn']],
                        'Nốt tròn ngân dài nhất, bằng 2 nốt trắng, 4 nốt đen.', 'de'],
                    ['Một nốt trắng dài bằng ___ nốt đen.', [[0, '2']],
                        'Nốt trắng dài gấp đôi nốt đen, tức bằng 2 nốt đen.', 'trung_binh'],
                ],
            ],
            // ============ Bài 3: Nhịp 2/4 và nhịp 3/4 ============
            'an-nhip-2-4-3-4' => [
                'quiz' => [
                    ['Nhịp 2/4 có nghĩa là gì?',
                        ['Mỗi ô nhịp có 2 phách', 'Mỗi bài có 2 ô nhịp', 'Mỗi phách dài 2 giây', 'Tốc độ nhanh gấp 2 lần'], 0,
                        'Số trên (2) chỉ số phách trong một ô nhịp; số dưới (4) chỉ nốt đen là đơn vị phách.', 'trung_binh'],
                    ['Nhịp 3/4 thường gợi cảm giác như thế nào?',
                        ['Nhẹ nhàng, uyển chuyển như điệu valse', 'Hùng mạnh như hành khúc', 'Vội vàng, gấp gáp', 'Buồn bã, chậm chạp'], 0,
                        'Nhịp 3/4 có 3 phách, thường dùng trong các điệu valse nhẹ nhàng, uyển chuyển.', 'trung_binh'],
                    ['Trong nhịp 2/4, phách nào là phách mạnh?',
                        ['Phách 1', 'Phách 2', 'Cả hai phách', 'Không có phách mạnh'], 0,
                        'Trong nhịp 2/4, phách 1 mạnh, phách 2 nhẹ. Trong nhịp 3/4 cũng vậy: phách 1 mạnh nhất.', 'de'],
                ],
                'matching' => [
                    ['Nối mỗi thành phần của số chỉ nhịp với ý nghĩa của nó.',
                        [['Số 2 trong 2/4', '2 phách mỗi ô nhịp'], ['Số 3 trong 3/4', '3 phách mỗi ô nhịp'], ['Số 4 ở mẫu số', 'Nốt đen làm đơn vị phách'], ['Vạch nhịp', 'Ngăn cách các ô nhịp']],
                        'Số trên chỉ số phách, số dưới chỉ đơn vị phách; vạch nhịp ngăn cách các ô nhịp với nhau.', 'trung_binh'],
                    ['Nối mỗi nhịp với đặc điểm phách của nó.',
                        [['Nhịp 2/4', 'Phách 1 mạnh'], ['Nhịp 3/4', 'Phách 1 mạnh'], ['Phách 2 của nhịp 2/4', 'Phách nhẹ'], ['Phách 2, 3 của nhịp 3/4', 'Phách nhẹ']],
                        'Trong mọi loại nhịp, phách đầu tiên luôn là phách mạnh, các phách còn lại nhẹ hơn.', 'trung_binh'],
                    ['Nối mỗi loại nhạc với nhịp thường dùng.',
                        [['Hành khúc thiếu nhi', 'Nhịp 2/4'], ['Điệu valse', 'Nhịp 3/4'], ['Bài hát bước đều', 'Nhịp 2/4']],
                        'Hành khúc và bài hát bước đều thường dùng nhịp 2/4; điệu valse uyển chuyển dùng nhịp 3/4.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi ví dụ vào nhóm "Nhịp 2/4" hoặc "Nhịp 3/4".',
                        [['Hành khúc', 'Nhịp 2/4'], ['Bài hát bước đều', 'Nhịp 2/4'], ['Điệu valse', 'Nhịp 3/4'], ['Điệu nhảy xoay tròn', 'Nhịp 3/4']],
                        'Hành khúc, bước đều dùng nhịp 2/4; valse và điệu nhảy xoay tròn dùng nhịp 3/4.', 'de'],
                    ['Kéo mỗi phách vào nhóm "Phách mạnh" hoặc "Phách nhẹ".',
                        [['Phách 1 của nhịp 2/4', 'Phách mạnh'], ['Phách 1 của nhịp 3/4', 'Phách mạnh'], ['Phách 2 của nhịp 2/4', 'Phách nhẹ'], ['Phách 3 của nhịp 3/4', 'Phách nhẹ']],
                        'Phách đầu tiên của mọi loại nhịp luôn là phách mạnh; các phách sau là phách nhẹ.', 'trung_binh'],
                    ['Kéo mỗi mô tả vào nhóm "Có 2 phách" hoặc "Có 3 phách".',
                        [['Một ô nhịp 2/4', 'Có 2 phách'], ['Hai tiếng vỗ tay mạnh – nhẹ', 'Có 2 phách'], ['Một ô nhịp 3/4', 'Có 3 phách'], ['Ba tiếng vỗ tay mạnh – nhẹ – nhẹ', 'Có 3 phách']],
                        'Nhịp 2/4: mạnh – nhẹ; nhịp 3/4: mạnh – nhẹ – nhẹ.', 'de'],
                ],
                'fill' => [
                    ['Nhịp 2/4 mỗi ô nhịp có ___ phách.', [[0, '2']],
                        'Số trên của số chỉ nhịp 2/4 cho biết mỗi ô nhịp có 2 phách.', 'de'],
                    ['Trong mọi loại nhịp, phách ___ luôn là phách mạnh.', [[0, 'đầu tiên']],
                        'Phách đầu tiên của ô nhịp luôn được nhấn mạnh.', 'de'],
                    ['Số 4 ở mẫu số của 2/4 cho biết ___ đen là đơn vị phách.', [[0, 'nốt']],
                        'Số dưới 4 nghĩa là nốt đen được lấy làm đơn vị tính phách.', 'trung_binh'],
                ],
            ],
            // ============ Bài 4: Các loại dấu lặng ============
            'an-cac-dau-lang' => [
                'quiz' => [
                    ['Dấu lặng dùng để làm gì trong bản nhạc?',
                        ['Chỉ chỗ nghỉ, ngưng phát âm', 'Làm nốt nhạc cao hơn', 'Làm nốt nhạc dài hơn', 'Kết thúc bài hát'], 0,
                        'Dấu lặng chỉ khoảng nghỉ, nơi người hát hoặc người chơi nhạc tạm ngưng phát ra âm thanh.', 'de'],
                    ['Dấu lặng nào tương ứng với nốt tròn?',
                        ['Dấu lặng tròn', 'Dấu lặng đen', 'Dấu lặng móc đơn', 'Không có'], 0,
                        'Mỗi loại nốt đều có dấu lặng tương ứng: lặng tròn nghỉ bằng một nốt tròn, lặng đen nghỉ bằng một nốt đen.', 'trung_binh'],
                    ['Một ô nhịp 2/4 gồm 1 nốt đen và 1 dấu lặng đen có tổng cộng mấy phách?',
                        ['1 phách', '2 phách', '3 phách', '4 phách'], 1,
                        'Nốt đen 1 phách + lặng đen 1 phách = 2 phách, vừa đủ một ô nhịp 2/4.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi dấu lặng với độ dài nghỉ của nó.',
                        [['Lặng tròn', 'Nghỉ 4 phách'], ['Lặng trắng', 'Nghỉ 2 phách'], ['Lặng đen', 'Nghỉ 1 phách'], ['Lặng móc đơn', 'Nghỉ nửa phách']],
                        'Độ dài nghỉ của dấu lặng bằng trường độ của nốt tương ứng: tròn 4, trắng 2, đen 1, móc đơn nửa phách.', 'trung_binh'],
                    ['Nối mỗi dấu lặng với nốt nhạc tương ứng.',
                        [['Lặng tròn', 'Nốt tròn'], ['Lặng trắng', 'Nốt trắng'], ['Lặng đen', 'Nốt đen']],
                        'Mỗi dấu lặng tương ứng với một loại nốt nhạc có cùng độ dài.', 'de'],
                    ['Nối mỗi tình huống với cách xử lý đúng khi gặp dấu lặng.',
                        [['Gặp dấu lặng khi hát', 'Ngưng hát đúng số phách'], ['Hết dấu lặng', 'Hát tiếp đúng nhịp'], ['Dấu lặng ở đầu bài', 'Đếm nhịp rồi mới vào'], ['Bỏ qua dấu lặng', 'Làm sai nhịp bài hát']],
                        'Gặp dấu lặng phải ngưng đúng số phách rồi hát tiếp; bỏ qua dấu lặng sẽ làm sai nhịp.', 'trung_binh'],
                ],
                'sort' => [
                    ['Kéo mỗi dấu lặng vào nhóm "Nghỉ dài" hoặc "Nghỉ ngắn".',
                        [['Lặng tròn', 'Nghỉ dài'], ['Lặng trắng', 'Nghỉ dài'], ['Lặng đen', 'Nghỉ ngắn'], ['Lặng móc đơn', 'Nghỉ ngắn']],
                        'Lặng tròn (4 phách) và lặng trắng (2 phách) nghỉ dài; lặng đen (1 phách) và lặng móc đơn nghỉ ngắn.', 'de'],
                    ['Kéo mỗi yếu tố vào nhóm "Phát ra âm thanh" hoặc "Không phát ra âm thanh".',
                        [['Nốt nhạc', 'Phát ra âm thanh'], ['Tiếng vỗ tay', 'Phát ra âm thanh'], ['Dấu lặng', 'Không phát ra âm thanh'], ['Khoảng nghỉ', 'Không phát ra âm thanh']],
                        'Nốt nhạc và tiếng vỗ tay phát ra âm thanh; dấu lặng và khoảng nghỉ thì không.', 'de'],
                    ['Kéo mỗi mô tả vào nhóm "Tương ứng nốt tròn" hoặc "Tương ứng nốt đen".',
                        [['Lặng tròn', 'Tương ứng nốt tròn'], ['Nghỉ 4 phách', 'Tương ứng nốt tròn'], ['Lặng đen', 'Tương ứng nốt đen'], ['Nghỉ 1 phách', 'Tương ứng nốt đen']],
                        'Lặng tròn nghỉ 4 phách như nốt tròn; lặng đen nghỉ 1 phách như nốt đen.', 'trung_binh'],
                ],
                'fill' => [
                    ['Dấu lặng là ký hiệu chỉ chỗ ___ trong bản nhạc.', [[0, 'nghỉ']],
                        'Khi gặp dấu lặng, người hát hoặc người chơi nhạc tạm ngưng phát ra âm thanh.', 'de'],
                    ['Dấu lặng đen có độ dài nghỉ bằng một nốt ___.', [[0, 'đen']],
                        'Mỗi dấu lặng có độ dài nghỉ bằng nốt nhạc cùng tên với nó.', 'de'],
                    ['Khi gặp dấu lặng, người hát phải tạm ___ phát ra âm thanh.', [[0, 'ngưng']],
                        'Dấu lặng là lệnh nghỉ: ngưng hát đúng số phách rồi hát tiếp.', 'de'],
                ],
            ],
            // ============ Bài 5: Đàn bầu, đàn tranh, sáo, trống cơm ============
            'an-dan-bau-tranh-sao-trong' => [
                'quiz' => [
                    ['Đàn bầu có mấy dây?',
                        ['1 dây', '2 dây', '6 dây', '16 dây'], 0,
                        'Đàn bầu chỉ có một dây duy nhất; người chơi một tay gảy, một tay rung cần đàn để tạo âm thanh đặc trưng.', 'de'],
                    ['Nhạc cụ nào sau đây thuộc bộ hơi?',
                        ['Sáo', 'Đàn tranh', 'Trống cơm', 'Đàn bầu'], 0,
                        'Sáo là nhạc cụ bộ hơi (thổi); đàn tranh, đàn bầu thuộc bộ dây; trống cơm thuộc bộ gõ.', 'de'],
                    ['Trống cơm được bịt da ở mấy mặt?',
                        ['1 mặt', '2 mặt', '3 mặt', '4 mặt'], 1,
                        'Trống cơm có hai mặt bịt da, thân thắt eo ở giữa; khi vỗ phát ra tiếng trầm bổng vui tai.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi nhạc cụ với số dây của nó.',
                        [['Đàn bầu', '1 dây'], ['Đàn nguyệt', '2 dây'], ['Đàn tỳ bà', '4 dây'], ['Đàn tranh', '16 dây']],
                        'Đàn bầu 1 dây, đàn nguyệt 2 dây, đàn tỳ bà 4 dây, đàn tranh truyền thống 16 dây.', 'trung_binh'],
                    ['Nối mỗi nhạc cụ với cách chơi của nó.',
                        [['Đàn bầu', 'Gảy bằng móng'], ['Sáo', 'Thổi bằng hơi'], ['Trống cơm', 'Vỗ bằng tay'], ['Đàn tranh', 'Gảy bằng móng gảy']],
                        'Đàn bầu và đàn tranh gảy dây, sáo thổi bằng hơi, trống cơm vỗ bằng tay.', 'de'],
                    ['Nối mỗi nhạc cụ với bộ nhạc cụ của nó.',
                        [['Đàn bầu', 'Bộ dây'], ['Đàn tranh', 'Bộ dây'], ['Sáo trúc', 'Bộ hơi'], ['Trống cơm', 'Bộ gõ']],
                        'Đàn bầu, đàn tranh thuộc bộ dây; sáo thuộc bộ hơi; trống cơm thuộc bộ gõ.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi nhạc cụ vào nhóm "Bộ dây" hoặc "Bộ gõ".',
                        [['Đàn bầu', 'Bộ dây'], ['Đàn tranh', 'Bộ dây'], ['Trống cơm', 'Bộ gõ'], ['Phách', 'Bộ gõ']],
                        'Đàn bầu, đàn tranh có dây thuộc bộ dây; trống cơm và phách gõ phát ra tiếng thuộc bộ gõ.', 'de'],
                    ['Kéo mỗi nhạc cụ vào nhóm "Bộ hơi" hoặc "Bộ dây".',
                        [['Sáo', 'Bộ hơi'], ['Kèn', 'Bộ hơi'], ['Đàn bầu', 'Bộ dây'], ['Đàn nguyệt', 'Bộ dây']],
                        'Sáo, kèn thổi bằng hơi thuộc bộ hơi; đàn bầu, đàn nguyệt có dây thuộc bộ dây.', 'de'],
                    ['Kéo mỗi nhạc cụ vào nhóm "Một dây" hoặc "Nhiều dây".',
                        [['Đàn bầu', 'Một dây'], ['Đàn tranh', 'Nhiều dây'], ['Đàn nhị', 'Nhiều dây'], ['Đàn tỳ bà', 'Nhiều dây']],
                        'Đàn bầu đặc biệt chỉ có một dây; đàn tranh, đàn nhị, đàn tỳ bà đều có nhiều dây.', 'trung_binh'],
                ],
                'fill' => [
                    ['Đàn bầu chỉ có duy nhất ___ dây đàn.', [[0, 'một']],
                        'Đàn bầu là nhạc cụ độc đáo của Việt Nam với chỉ một dây duy nhất.', 'de'],
                    ['Sáo là nhạc cụ thuộc bộ ___.', [[0, 'hơi']],
                        'Sáo phát ra âm thanh nhờ hơi thổi nên thuộc bộ hơi.', 'de'],
                    ['Trống cơm được chơi bằng cách ___ tay lên mặt trống.', [[0, 'vỗ']],
                        'Người chơi vỗ tay lên hai mặt da của trống cơm để tạo ra âm thanh trầm bổng.', 'de'],
                ],
            ],
            // ============ Bài 6: Hát đúng giai điệu ============
            'an-hat-bai-thieu-nhi' => [
                'quiz' => [
                    ['Muốn hát đúng giai điệu, việc đầu tiên cần làm là gì?',
                        ['Nghe kỹ giai điệu mẫu', 'Hát thật to', 'Hát thật nhanh', 'Nhắm mắt hát'], 0,
                        'Nghe kỹ giai điệu mẫu giúp em nhớ đúng cao độ và nhịp điệu trước khi cất tiếng hát.', 'de'],
                    ['Khi hát, em cần chú ý điều gì về hơi thở?',
                        ['Lấy hơi đúng chỗ, không ngắt giữa câu', 'Nín thở suốt bài', 'Thở gấp liên tục', 'Lấy hơi giữa chừng câu hát'], 0,
                        'Lấy hơi ở chỗ dấu lặng hoặc cuối câu nhạc; ngắt hơi giữa chừng làm sai giai điệu.', 'trung_binh'],
                    ['Hát "lệch tông" nghĩa là gì?',
                        ['Hát sai cao độ so với nhạc đệm', 'Hát sai lời bài hát', 'Hát quá to', 'Hát quá nhanh'], 0,
                        'Lệch tông là hát cao hơn hoặc thấp hơn cao độ chuẩn của bài hát và nhạc đệm.', 'trung_binh'],
                ],
                'matching' => [
                    ['Nối mỗi yếu tố với tác dụng của nó khi hát.',
                        [['Nghe giai điệu mẫu', 'Nhớ đúng nhạc'], ['Lấy hơi đúng chỗ', 'Hát liền mạch'], ['Phát âm rõ lời', 'Người nghe hiểu'], ['Giữ đúng nhịp', 'Không sai tempo']],
                        'Nghe mẫu để nhớ nhạc, lấy hơi đúng chỗ để liền mạch, phát âm rõ để người nghe hiểu, giữ nhịp để không sai tempo.', 'de'],
                    ['Nối mỗi bài hát thiếu nhi với nội dung của nó.',
                        [['"Cháu yêu bà"', 'Tình cảm với bà'], ['"Lớp chúng mình"', 'Tình bạn lớp học'], ['"Đi học"', 'Niềm vui đến trường']],
                        '"Cháu yêu bà" về tình cảm bà cháu, "Lớp chúng mình" về tình bạn, "Đi học" về niềm vui đến trường.', 'de'],
                    ['Nối mỗi cách hát với đánh giá của nó.',
                        [['Hát đúng cao độ', 'Đúng'], ['Thuộc lời bài hát', 'Đúng'], ['Hát sai nhịp', 'Sai'], ['Hát ồn ào át tiếng bạn', 'Sai']],
                        'Hát đúng cao độ và thuộc lời là đúng; hát sai nhịp hay át tiếng bạn khi hát tập thể là sai.', 'de'],
                ],
                'sort' => [
                    ['Kéo mỗi việc làm vào nhóm "Giúp hát đúng" hoặc "Làm hát sai".',
                        [['Nghe kỹ nhạc mẫu', 'Giúp hát đúng'], ['Lấy hơi đúng chỗ', 'Giúp hát đúng'], ['Hát vội vàng', 'Làm hát sai'], ['Không thuộc lời', 'Làm hát sai']],
                        'Nghe kỹ mẫu và lấy hơi đúng chỗ giúp hát đúng; hát vội và không thuộc lời làm hát sai.', 'de'],
                    ['Kéo mỗi âm thanh vào nhóm "Bài hát thiếu nhi" hoặc "Không phải bài hát".',
                        [['"Cháu yêu bà"', 'Bài hát thiếu nhi'], ['"Lớp chúng mình"', 'Bài hát thiếu nhi'], ['Bản tin thời sự', 'Không phải bài hát'], ['Tiếng còi xe', 'Không phải bài hát']],
                        '"Cháu yêu bà" và "Lớp chúng mình" là bài hát thiếu nhi; bản tin và tiếng còi xe không phải.', 'de'],
                    ['Kéo mỗi việc làm vào nhóm "Nên làm" hoặc "Không nên" khi hát tập thể.',
                        [['Hát cùng nhịp với cả lớp', 'Nên làm'], ['Nghe bạn hát để vào đúng', 'Nên làm'], ['Hát át tiếng bạn', 'Không nên'], ['Đùa giỡn khi đang hát', 'Không nên']],
                        'Hát tập thể cần cùng nhịp, lắng nghe nhau; không hát át tiếng bạn và không đùa giỡn.', 'de'],
                ],
                'fill' => [
                    ['Trước khi hát, em cần nghe kỹ ___ mẫu.', [[0, 'giai điệu']],
                        'Nghe kỹ giai điệu mẫu giúp nhớ đúng cao độ và nhịp điệu của bài hát.', 'de'],
                    ['Khi hát tập thể, cả lớp phải hát cùng một ___.', [[0, 'nhịp']],
                        'Cùng một nhịp thì tiếng hát của cả lớp mới đều và hay.', 'de'],
                    ['Hát sai cao độ so với nhạc đệm gọi là hát lệch ___.', [[0, 'tông']],
                        'Lệch tông là hát không đúng cao độ chuẩn của bài hát.', 'trung_binh'],
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
