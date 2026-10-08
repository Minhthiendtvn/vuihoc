<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Dữ liệu bậc THPT cho toàn bộ 14 môn (đợt 2026-10-07, theo yêu cầu của Thiện).
 *
 * Mỗi môn thêm 3 topics mới, mỗi topic gắn đúng 1 khối (grade_min = grade_max
 * thuộc {10, 11, 12}), mỗi skill 2 bài học, mỗi bài ≥16 câu (4 quiz + 4
 * matching + 4 sort + 4 fill). Nội dung tiếng Việt tự viết, bám chương trình
 * THPT. Các seeder con đều idempotent nên chạy lại an toàn.
 * Gọi SAU GradeCoverageSeeder trong DatabaseSeeder.
 */
class HighSchoolSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HighSchoolGroupToanSeeder::class,                // Toán
            HighSchoolGroupVanAnhSeeder::class,              // Ngữ văn, Tiếng Việt, Tiếng Anh
            HighSchoolGroupKhoaHocLichSuDiaLySeeder::class,  // Khoa học, Lịch sử, Địa lý
            HighSchoolGroupGdcdTinHocCongNgheSeeder::class,  // GDCD, Tin học, Công nghệ
            HighSchoolGroupNangKhieuHuongNghiepSeeder::class, // Âm nhạc, Mỹ thuật, GDTC, Trải nghiệm & HN
        ]);
    }
}
