<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Phủ dữ liệu theo khối lớp cho toàn bộ 43 topics (đợt 2026-10-07, theo yêu cầu
 * của Thiện "càng nhiều data càng tốt").
 *
 * Với mỗi topic × mỗi khối trong [grade_min..grade_max]: 2 bài học mới gắn đúng
 * grade đó, mỗi bài ≥4 câu cho mỗi kiểu chơi (quiz/matching/sort/fill).
 * Các seeder con đều idempotent (bỏ qua theo slug lesson) nên chạy lại an toàn.
 * Gọi SAU AdditionalSubjectsSeeder trong DatabaseSeeder.
 */
class GradeCoverageSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GradeCoverageGroupASeeder::class, // Toán, Tiếng Việt, Tiếng Anh
            GradeCoverageGroupBSeeder::class, // Khoa học, Lịch sử, Ngữ văn, Địa lý
            GradeCoverageGroupCSeeder::class, // GDCD, Tin học, Công nghệ
            GradeCoverageGroupDSeeder::class, // Âm nhạc, Mỹ thuật, GDTC, Trải nghiệm & HN
        ]);
    }
}
