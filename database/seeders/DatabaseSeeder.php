<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Gọi seeders theo đúng thứ tự khoá ngoại:
     * môn → chủ đề → kỹ năng → bài học → câu hỏi → huy hiệu → tài khoản demo.
     */
    public function run(): void
    {
        $this->call([
            SubjectSeeder::class,
            TopicSeeder::class,
            SkillSeeder::class,
            LessonSeeder::class,
            QuestionSeeder::class,
            BadgeSeeder::class,
            DemoUserSeeder::class,
            AdditionalSubjectsSeeder::class,
            GradeCoverageSeeder::class,
            HighSchoolSeeder::class,
            AdditionalQuestionsSeeder::class,
            LessonSummarySeeder::class,
        ]);
    }
}
