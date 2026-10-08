<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['name' => 'Chơi lần đầu', 'slug' => 'first-play', 'description' => 'Hoàn thành lượt chơi đầu tiên. Chúc mừng bạn đã bắt đầu hành trình học tập!', 'icon' => '🎮', 'criteria' => 'first_play', 'threshold' => 1],
            ['name' => 'Chuỗi 3 ngày', 'slug' => 'streak-3', 'description' => 'Học liên tục 3 ngày. Thói quen tốt đang hình thành!', 'icon' => '🔥', 'criteria' => 'streak_3', 'threshold' => 3],
            ['name' => 'Chuỗi 7 ngày', 'slug' => 'streak-7', 'description' => 'Học liên tục 7 ngày. Bạn thật kiên trì!', 'icon' => '⚡', 'criteria' => 'streak_7', 'threshold' => 7],
            ['name' => 'Chuỗi 30 ngày', 'slug' => 'streak-30', 'description' => 'Học liên tục 30 ngày. Bạn là tấm gương chăm học!', 'icon' => '👑', 'criteria' => 'streak_30', 'threshold' => 30],
            ['name' => 'Ngôi sao 1000 XP', 'slug' => 'xp-1000', 'description' => 'Tích luỹ 1000 điểm kinh nghiệm.', 'icon' => '⭐', 'criteria' => 'xp_1000', 'threshold' => 1000],
            ['name' => 'Viên kim cương 5000 XP', 'slug' => 'xp-5000', 'description' => 'Tích luỹ 5000 điểm kinh nghiệm. Thật xuất sắc!', 'icon' => '💎', 'criteria' => 'xp_5000', 'threshold' => 5000],
            ['name' => 'Hoàn hảo 5 lượt', 'slug' => 'perfect-5', 'description' => 'Đạt 100% điểm trong 5 lượt chơi.', 'icon' => '🌟', 'criteria' => 'perfect_5', 'threshold' => 5],
            ['name' => 'Nhà thám hiểm', 'slug' => 'explorer-3', 'description' => 'Chơi game ở 3 môn học khác nhau.', 'icon' => '🗺️', 'criteria' => 'explorer_3', 'threshold' => 3],
            ['name' => 'Học giả', 'slug' => 'scholar-10', 'description' => 'Hoàn thành 10 bài học. Kiến thức của bạn ngày càng rộng!', 'icon' => '📚', 'criteria' => 'scholar_10', 'threshold' => 10],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(
                ['slug' => $b['slug']],
                $b + ['is_demo' => true]
            );
        }
    }
}
