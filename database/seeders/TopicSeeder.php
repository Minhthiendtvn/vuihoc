<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Trả về cây chủ đề mẫu: subject_slug => [topics...].
     * Dùng lại trong SkillSeeder và LessonSeeder để giữ đồng bộ slug.
     */
    public static function tree(): array
    {
        return [
            'toan' => [
                ['name' => 'Số tự nhiên', 'slug' => 'toan-so-tu-nhien', 'grade_min' => 6, 'grade_max' => 7, 'icon' => '1️⃣', 'description' => 'Cộng, trừ, nhân, chia số tự nhiên.'],
                ['name' => 'Phân số', 'slug' => 'toan-phan-so', 'grade_min' => 6, 'grade_max' => 7, 'icon' => '½', 'description' => 'Cộng, trừ, nhân, chia phân số.'],
                ['name' => 'Biểu thức đại số', 'slug' => 'toan-bieu-thuc-dai-so', 'grade_min' => 7, 'grade_max' => 8, 'icon' => '🧮', 'description' => 'Đơn thức, đa thức và giá trị biểu thức.'],
                ['name' => 'Hình học phẳng', 'slug' => 'toan-hinh-hoc-phang', 'grade_min' => 7, 'grade_max' => 8, 'icon' => '📐', 'description' => 'Góc, đường thẳng và tam giác.'],
            ],
            'tieng-viet' => [
                ['name' => 'Từ và câu', 'slug' => 'tv-tu-va-cau', 'grade_min' => 6, 'grade_max' => 7, 'icon' => '🔤', 'description' => 'Từ loại và cấu tạo câu.'],
                ['name' => 'Chính tả', 'slug' => 'tv-chinh-ta', 'grade_min' => 6, 'grade_max' => 7, 'icon' => '✏️', 'description' => 'Dấu hỏi, dấu ngã và các âm đầu dễ nhầm.'],
                ['name' => 'Luyện văn miêu tả', 'slug' => 'tv-van-mieu-ta', 'grade_min' => 7, 'grade_max' => 8, 'icon' => '📝', 'description' => 'Bài văn miêu tả và biện pháp tu từ.'],
            ],
            'tieng-anh' => [
                ['name' => 'Từ vựng lớp 6', 'slug' => 'en-tu-vung-lop-6', 'grade_min' => 6, 'grade_max' => 7, 'icon' => '👨‍👩‍👧', 'description' => 'Vocabulary: family and school.'],
                ['name' => 'Ngữ pháp cơ bản', 'slug' => 'en-ngu-phap-co-ban', 'grade_min' => 6, 'grade_max' => 7, 'icon' => '⏰', 'description' => 'Grammar: present simple and prepositions.'],
                ['name' => 'Từ vựng lớp 7', 'slug' => 'en-tu-vung-lop-7', 'grade_min' => 7, 'grade_max' => 8, 'icon' => '🌍', 'description' => 'Vocabulary: health and travel.'],
            ],
            'khoa-hoc' => [
                ['name' => 'Cơ thể người', 'slug' => 'kh-co-the-nguoi', 'grade_min' => 6, 'grade_max' => 7, 'icon' => '🧍', 'description' => 'Hệ xương và hệ tiêu hoá.'],
                ['name' => 'Chất quanh ta', 'slug' => 'kh-chat-quanh-ta', 'grade_min' => 7, 'grade_max' => 8, 'icon' => '🧪', 'description' => 'Trạng thái của chất và nước.'],
                ['name' => 'Năng lượng', 'slug' => 'kh-nang-luong', 'grade_min' => 8, 'grade_max' => 9, 'icon' => '⚡', 'description' => 'Nguồn năng lượng và điện.'],
            ],
            'lich-su' => [
                ['name' => 'Việt Nam thời dựng nước', 'slug' => 'ls-dung-nuoc', 'grade_min' => 6, 'grade_max' => 7, 'icon' => '🏞️', 'description' => 'Nước Văn Lang và các anh hùng dân tộc.'],
                ['name' => 'Nhà Đinh – nhà Tiền Lê', 'slug' => 'ls-dinh-tien-le', 'grade_min' => 7, 'grade_max' => 8, 'icon' => '👑', 'description' => 'Đinh Bộ Lĩnh và Lê Hoàn.'],
                ['name' => 'Kháng chiến chống Nguyên – Mông', 'slug' => 'ls-chong-nguyen-mong', 'grade_min' => 8, 'grade_max' => 9, 'icon' => '⚔️', 'description' => 'Các trận đánh và danh tướng Trần Hưng Đạo.'],
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::tree() as $subjectSlug => $topics) {
            $subject = Subject::where('slug', $subjectSlug)->firstOrFail();
            $order = 1;
            foreach ($topics as $t) {
                Topic::updateOrCreate(
                    ['slug' => $t['slug']],
                    [
                        'subject_id' => $subject->id,
                        'name' => $t['name'],
                        'description' => $t['description'],
                        'icon' => $t['icon'],
                        'sort_order' => $order++,
                        'grade_min' => $t['grade_min'],
                        'grade_max' => $t['grade_max'],
                        'is_published' => true,
                        'is_demo' => true,
                    ]
                );
            }
        }
    }
}
