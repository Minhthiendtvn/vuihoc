<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng giao bài (assignments): phụ huynh giao cho từng con,
     * giáo viên/phụ huynh giao cho cả lớp.
     * - learner_profile_id: giao cho 1 hồ sơ cụ thể (nullable)
     * - classroom_id: giao cho cả lớp (nullable)
     * - status: pending/done — "quá hạn" tính động từ deadline, không lưu DB.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('learner_profile_id')->nullable()->constrained('learner_profiles')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->string('game_type', 20)->default('quiz');
            $table->text('note')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->string('share_token', 40)->unique();
            $table->enum('status', ['pending', 'done'])->default('pending');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            // Tên index tường minh, ngắn gọn (MariaDB giới hạn 64 ký tự).
            $table->index('created_by', 'asg_creator');
            $table->index('learner_profile_id', 'asg_profile');
            $table->index('classroom_id', 'asg_class');
            $table->index('lesson_id', 'asg_lesson');
            $table->index('status', 'asg_status');
            $table->index('share_token', 'asg_share');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
