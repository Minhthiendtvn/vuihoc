<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Toán', 'slug' => 'toan', 'icon' => '🔢', 'color' => '#3b82f6',
                'description' => 'Học toán qua game: số học, đại số, hình học từ lớp 6 đến lớp 12.',
                'sort_order' => 1, 'is_published' => true, 'is_demo' => true,
            ],
            [
                'name' => 'Tiếng Việt', 'slug' => 'tieng-viet', 'icon' => '📖', 'color' => '#ef4444',
                'description' => 'Chơi mà học tiếng Việt: từ loại, chính tả, văn miêu tả và biện pháp tu từ.',
                'sort_order' => 2, 'is_published' => true, 'is_demo' => true,
            ],
            [
                'name' => 'Tiếng Anh', 'slug' => 'tieng-anh', 'icon' => '🔤', 'color' => '#8b5cf6',
                'description' => 'Luyện từ vựng và ngữ pháp tiếng Anh theo chủ đề, phù hợp lớp 6 trở lên.',
                'sort_order' => 3, 'is_published' => true, 'is_demo' => true,
            ],
            [
                'name' => 'Khoa học', 'slug' => 'khoa-hoc', 'icon' => '🔬', 'color' => '#10b981',
                'description' => 'Khám phá cơ thể người, chất quanh ta và năng lượng qua trò chơi.',
                'sort_order' => 4, 'is_published' => true, 'is_demo' => true,
            ],
            [
                'name' => 'Lịch sử', 'slug' => 'lich-su', 'icon' => '🏛️', 'color' => '#f59e0b',
                'description' => 'Dòng lịch sử dân tộc: từ thời dựng nước đến các cuộc kháng chiến.',
                'sort_order' => 5, 'is_published' => true, 'is_demo' => true,
            ],
        ];

        foreach ($subjects as $s) {
            Subject::updateOrCreate(['slug' => $s['slug']], $s);
        }
    }
}
