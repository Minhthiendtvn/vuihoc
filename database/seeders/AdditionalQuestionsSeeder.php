<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Thêm câu hỏi cho các bài học HIỆN CÓ: nâng mỗi bài lên 8 câu cho
 * mỗi kiểu chơi (quiz/matching/sort/fill).
 *
 * Gồm 12 seeder nhóm môn + 3 seeder vá (top-up). Tất cả đều idempotent
 * theo logic "top-up đến 8" (bỏ qua khi đủ 8 câu/kiểu, bỏ qua prompt
 * trùng) — chạy lại nhiều lần không tạo trùng lặp.
 */
class AdditionalQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdditionalQuestionsGroup1Seeder::class,   // Toán + Tin học
            AdditionalQuestionsGroup2Seeder::class,   // Tiếng Việt + Ngữ văn
            AdditionalQuestionsGroup3aSeeder::class,  // Tiếng Anh
            AdditionalQuestionsGroup3bSeeder::class,  // Công nghệ
            AdditionalQuestionsGroup4Seeder::class,   // Khoa học + Lịch sử + Địa lý
            AdditionalQuestionsGroup5aSeeder::class,  // GDCD
            AdditionalQuestionsGroup5b1Seeder::class, // Âm nhạc (1/2)
            AdditionalQuestionsGroup5b2Seeder::class, // Âm nhạc (2/2)
            AdditionalQuestionsGroup6Seeder::class,   // Giáo dục thể chất
            AdditionalQuestionsGroup6bSeeder::class,  // Mỹ thuật
            AdditionalQuestionsGroup6c1Seeder::class, // Trải nghiệm & HN (1/2)
            AdditionalQuestionsGroup6c2Seeder::class, // Trải nghiệm & HN (2/2)
            TopUpQuestionsT1Seeder::class,            // Vá: Khoa học + Địa lý
            TopUpQuestionsT2Seeder::class,            // Vá: Lịch sử + Toán
            TopUpQuestionsT3Seeder::class,            // Vá: Anh + Việt + TNHN + Tin + GDTC
        ]);
    }
}
