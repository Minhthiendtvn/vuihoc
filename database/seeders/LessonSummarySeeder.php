<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Tóm tắt bài học: gọi các seeder nhóm môn (A/B/C/D).
 * Mỗi seeder con chỉ điền cho bài nào summary đang NULL/rỗng (idempotent).
 */
class LessonSummarySeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LessonSummaryGroupASeeder::class,
            LessonSummaryGroupBSeeder::class,
            LessonSummaryGroupCSeeder::class,
            LessonSummaryGroupDSeeder::class,
        ]);
    }
}
