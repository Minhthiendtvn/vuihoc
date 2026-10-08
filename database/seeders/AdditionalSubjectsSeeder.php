<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seed 9 môn học mở rộng (đợt 2026-10-07, theo yêu cầu của Thiện):
 * Ngữ văn, Địa lý, GDCD, Tin học, Công nghệ, Âm nhạc, Mỹ thuật,
 * Giáo dục thể chất, Trải nghiệm & Hướng nghiệp.
 *
 * Mỗi seeder con tự tạo subject của mình và idempotent (updateOrCreate
 * theo slug, câu hỏi kiểm tra exists trước khi seed) nên chạy lại an toàn.
 * Gọi SAU các seeder gốc trong DatabaseSeeder.
 */
class AdditionalSubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubjectNguVanSeeder::class,
            SubjectDiaLySeeder::class,
            SubjectGdcdSeeder::class,
            SubjectTinHocSeeder::class,
            SubjectCongNgheSeeder::class,
            SubjectAmNhacSeeder::class,
            SubjectMyThuatSeeder::class,
            SubjectGdtcSeeder::class,
            SubjectTraiNghiemHuongNghiepSeeder::class,
        ]);
    }
}
