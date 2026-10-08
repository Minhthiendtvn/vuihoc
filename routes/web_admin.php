<?php

// routes/web_admin.php — SỞ HỮU: agent Admin (phase 2)
// Phạm vi: /admin/* với middleware 'role:admin' — quản lý môn học,
// chủ đề, kỹ năng, bài học, câu hỏi (4 loại game), huy hiệu,
// người dùng, nội dung mẫu.

use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemoDataController;
use App\Http\Controllers\Admin\LearningContentImportController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// QUAN TRỌNG: các file route phase 2 được require trong `then()` của
// bootstrap/app.php nên KHÔNG tự có middleware group `web`
// (session/CSRF/auth). File này tự bọc group `web` để session hoạt động.
Route::middleware('web')->group(function () {

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {
            // Tổng quan
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('learning-imports', [LearningContentImportController::class, 'index'])->name('imports.index');
            Route::post('learning-imports/preview', [LearningContentImportController::class, 'preview'])->name('imports.preview');
            Route::post('learning-imports/confirm', [LearningContentImportController::class, 'store'])->name('imports.store');
            Route::post('learning-imports/cancel', [LearningContentImportController::class, 'cancel'])->name('imports.cancel');

            // CRUD danh mục học liệu
            Route::resource('subjects', SubjectController::class)->except(['show']);
            Route::resource('topics', TopicController::class)->except(['show']);
            Route::resource('skills', SkillController::class)->except(['show']);

            // Bài học + xem trước (kèm đáp án, chỉ đọc)
            Route::get('lessons/{lesson}/preview', [LessonController::class, 'preview'])
                ->name('lessons.preview');
            Route::resource('lessons', LessonController::class)->except(['show']);

            // Câu hỏi 4 loại game
            Route::resource('questions', QuestionController::class)->except(['show']);

            // Huy hiệu
            Route::resource('badges', BadgeController::class)->except(['show']);

            // Người dùng (xem, đổi role — không có xóa)
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

            // Dữ liệu mẫu (xem số lượng + xóa toàn bộ)
            Route::get('demo', [DemoDataController::class, 'index'])->name('demo');
            Route::delete('demo', [DemoDataController::class, 'destroy'])->name('demo.destroy');
        });

}); // end middleware('web')
