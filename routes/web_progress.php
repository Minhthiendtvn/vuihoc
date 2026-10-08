<?php

// routes/web_progress.php — SỞ HỮU: agent Progress (phase 2)
// Phạm vi: trang tiến độ /tien-do (XP, cấp độ, chuỗi ngày, huy hiệu, biểu đồ),
// khu vực phụ huynh /phu-huynh (tiến độ các con, mục tiêu ngày),
// lớp học /lop* (tạo lớp, tham gia bằng mã, xem thành viên).

use App\Http\Controllers\Progress\AssignmentController;
use App\Http\Controllers\Progress\ClassroomController;
use App\Http\Controllers\Progress\ParentController;
use App\Http\Controllers\Progress\ProgressController;
use Illuminate\Support\Facades\Route;

// Link chia sẻ bài được giao — công khai, không cần đăng nhập để xem.
Route::middleware('web')->group(function () {
    Route::get('/bai-giao/{token}', [AssignmentController::class, 'showByToken'])
        ->name('assignments.shared');
});

Route::middleware(['web', 'auth'])->group(function () {
    // Tiến độ cá nhân — cần đăng nhập + hồ sơ học viên đang hoạt động
    // (kiểm tra active_profile bên trong controller).
    Route::get('/tien-do', [ProgressController::class, 'index'])->name('progress.index');

    // Khu vực phụ huynh — chỉ role parent.
    Route::middleware(['role:parent'])->group(function () {
        Route::get('/phu-huynh', [ParentController::class, 'index'])->name('parent.index');

        // Giao bài cho từng con.
        Route::get('/phu-huynh/con/{profile}/giao-bai', [AssignmentController::class, 'createForProfile'])
            ->name('assignments.create.profile');
        Route::post('/phu-huynh/con/{profile}/giao-bai', [AssignmentController::class, 'storeForProfile'])
            ->name('assignments.store.profile');
    });

    // Học viên tham gia lớp bằng mã — mọi user đã đăng nhập có hồ sơ đều dùng được.
    Route::post('/lop/tham-gia', [ClassroomController::class, 'join'])->name('classroom.join');

    // Lớp học — giáo viên và phụ huynh.
    Route::middleware(['role:teacher,parent'])->group(function () {
        Route::get('/lop', [ClassroomController::class, 'index'])->name('classroom.index');
        Route::post('/lop', [ClassroomController::class, 'store'])->name('classroom.store');
        Route::get('/lop/{classroom}', [ClassroomController::class, 'show'])->name('classroom.show');

        // Giao bài cho cả lớp.
        Route::get('/lop/{classroom}/giao-bai', [AssignmentController::class, 'createForClassroom'])
            ->name('assignments.create.classroom');
        Route::post('/lop/{classroom}/giao-bai', [AssignmentController::class, 'storeForClassroom'])
            ->name('assignments.store.classroom');

        // Xoá bài đã giao — chỉ người tạo (kiểm tra trong controller).
        Route::delete('/giao-bai/{assignment}', [AssignmentController::class, 'destroy'])
            ->name('assignments.destroy');
    });
});
