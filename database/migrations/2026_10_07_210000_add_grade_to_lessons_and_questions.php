<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột grade (khối lớp 6-12) vào lessons và questions để sau này
 * tách/lọc nội dung theo khối dễ dàng, thay vì chỉ chặn ở tầng topic.
 * Backfill: lessons.grade = grade_min của topic chứa nó;
 * questions.grade = grade của lesson chứa nó.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->tinyInteger('grade')->unsigned()->nullable()->after('status');
            $table->index('grade', 'ls_grade');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->tinyInteger('grade')->unsigned()->nullable()->after('game_type');
            $table->index('grade', 'q_grade');
        });

        DB::statement('UPDATE lessons l JOIN skills s ON s.id = l.skill_id JOIN topics t ON t.id = s.topic_id SET l.grade = t.grade_min WHERE l.grade IS NULL');
        DB::statement('UPDATE questions q JOIN lessons l ON l.id = q.lesson_id SET q.grade = l.grade WHERE q.grade IS NULL');
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('q_grade');
            $table->dropColumn('grade');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex('ls_grade');
            $table->dropColumn('grade');
        });
    }
};
