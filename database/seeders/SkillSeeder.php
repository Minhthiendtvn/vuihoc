<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * topic_slug => [skills...]. Mỗi chủ đề 1-2 kỹ năng (mẫu dùng 1 kỹ năng
     * bao quát, bài học chia nhỏ bên trong).
     */
    public static function tree(): array
    {
        return [
            'toan-so-tu-nhien'      => [['name' => 'Cộng, trừ, nhân, chia số tự nhiên', 'slug' => 'kn-toan-so-tu-nhien-1']],
            'toan-phan-so'           => [['name' => 'Cộng, trừ, nhân, chia phân số', 'slug' => 'kn-toan-phan-so-1']],
            'toan-bieu-thuc-dai-so'  => [['name' => 'Thu gọn và tính giá trị biểu thức', 'slug' => 'kn-toan-dai-so-1']],
            'toan-hinh-hoc-phang'    => [['name' => 'Nhận biết góc và tam giác', 'slug' => 'kn-toan-hinh-hoc-1']],
            'tv-tu-va-cau'           => [['name' => 'Nhận diện từ loại và câu', 'slug' => 'kn-tv-tu-cau-1']],
            'tv-chinh-ta'            => [['name' => 'Viết đúng chính tả', 'slug' => 'kn-tv-chinh-ta-1']],
            'tv-van-mieu-ta'         => [['name' => 'Viết và cảm thụ văn miêu tả', 'slug' => 'kn-tv-mieu-ta-1']],
            'en-tu-vung-lop-6'       => [['name' => 'Family and school vocabulary', 'slug' => 'kn-en-vocab-6-1']],
            'en-ngu-phap-co-ban'     => [['name' => 'Basic grammar', 'slug' => 'kn-en-grammar-1']],
            'en-tu-vung-lop-7'       => [['name' => 'Health and travel vocabulary', 'slug' => 'kn-en-vocab-7-1']],
            'kh-co-the-nguoi'        => [['name' => 'Hệ xương và hệ tiêu hoá', 'slug' => 'kn-kh-co-the-1']],
            'kh-chat-quanh-ta'       => [['name' => 'Trạng thái chất và nước', 'slug' => 'kn-kh-chat-1']],
            'kh-nang-luong'          => [['name' => 'Nguồn năng lượng và điện', 'slug' => 'kn-kh-nang-luong-1']],
            'ls-dung-nuoc'           => [['name' => 'Nước Văn Lang và anh hùng dân tộc', 'slug' => 'kn-ls-dung-nuoc-1']],
            'ls-dinh-tien-le'        => [['name' => 'Nhà Đinh và nhà Tiền Lê', 'slug' => 'kn-ls-dinh-tien-le-1']],
            'ls-chong-nguyen-mong'   => [['name' => 'Kháng chiến chống quân Nguyên – Mông', 'slug' => 'kn-ls-chong-nguyen-mong-1']],
        ];
    }

    public function run(): void
    {
        foreach (self::tree() as $topicSlug => $skills) {
            $topic = Topic::where('slug', $topicSlug)->firstOrFail();
            $order = 1;
            foreach ($skills as $s) {
                Skill::updateOrCreate(
                    ['slug' => $s['slug']],
                    [
                        'topic_id' => $topic->id,
                        'name' => $s['name'],
                        'description' => $s['name'],
                        'sort_order' => $order++,
                        'is_demo' => true,
                    ]
                );
            }
        }
    }
}
