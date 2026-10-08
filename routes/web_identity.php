<?php

// routes/web_identity.php — SỞ HỮU: agent Identity (phase 2)
// Phạm vi: đăng ký/đăng nhập/đăng xuất, quên mật khẩu, hồ sơ học viên
// (tạo/sửa hồ sơ, đổi tên/avatar/emoji/mục tiêu ngày/cỡ chữ),
// chuyển đổi hồ sơ con của phụ huynh (session active_profile_id),
// trang quản lý con của phụ huynh.
//
// Quy ước tên route: auth.* (xác thực), profile.* (hồ sơ học viên),
// identity.children.* (phụ huynh quản lý hồ sơ con — theo CONTRACT (c),
// tên route dạng tên-file.tên-hành-động, file này là web_identity.php;
// KHÔNG dùng parent.* vì agent Progress đã dùng parent.index cho /phu-huynh).
//
// QUAN TRỌNG: toàn bộ route bọc trong middleware group 'web'.
// File này được require trong callback then() của bootstrap/app.php nên
// KHÔNG tự có group 'web' — thiếu nó thì session/auth/CSRF/$errors đều hỏng.

use App\Http\Controllers\Identity\AuthController;
use App\Http\Controllers\Identity\ParentController;
use App\Http\Controllers\Identity\PasswordResetController;
use App\Http\Controllers\Identity\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {

    // -----------------------------------------------------------------------
    // Khách chưa đăng nhập
    // -----------------------------------------------------------------------
    Route::middleware('guest')->group(function () {
        // Đăng nhập
        Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('auth.login');
        Route::post('/dang-nhap', [AuthController::class, 'login'])->name('auth.login.post');

        // Đăng ký
        Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('auth.register');
        Route::post('/dang-ky', [AuthController::class, 'register'])->name('auth.register.post');

        // Quên mật khẩu (token lưu bảng password_reset_tokens, hết hạn 60 phút;
        // chế độ demo: token ghi vào storage/logs/password-resets.log)
        Route::get('/quen-mat-khau', [PasswordResetController::class, 'showForgot'])->name('auth.password.email');
        Route::post('/quen-mat-khau', [PasswordResetController::class, 'sendResetLink'])->name('auth.password.send');
        Route::get('/dat-lai-mat-khau/{token}', [PasswordResetController::class, 'showReset'])->name('auth.password.reset');
        Route::post('/dat-lai-mat-khau', [PasswordResetController::class, 'reset'])->name('auth.password.update');
    });

    // Alias 'login' cho middleware 'auth' mặc định của Laravel: khi guest bị
    // chặn, Laravel redirect về route('login'). Redirect 302 sang /dang-nhap
    // (agent Gameplay cũng đang chờ route('login') này tồn tại).
    Route::get('/login', fn () => redirect()->route('auth.login', 302))->name('login');

    // -----------------------------------------------------------------------
    // Đã đăng nhập
    // -----------------------------------------------------------------------
    Route::middleware('auth')->group(function () {
        // Đăng xuất — chấp nhận cả GET (link trong layout) và POST.
        Route::match(['get', 'post'], '/dang-xuat', [AuthController::class, 'logout'])->name('auth.logout');

        // Hồ sơ học viên (learner tự tạo sau khi đăng ký; sửa sau này)
        Route::get('/ho-so', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/ho-so/tao', [ProfileController::class, 'create'])->name('profile.create');
        Route::post('/ho-so', [ProfileController::class, 'store'])->name('profile.store');
        Route::get('/ho-so/sua', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/ho-so', [ProfileController::class, 'update'])->name('profile.update');

        // Phụ huynh quản lý hồ sơ con (identity.children.*)
        Route::middleware('role:parent')->group(function () {
            Route::get('/con-cua-toi', [ParentController::class, 'index'])->name('identity.children.index');
            Route::get('/con-cua-toi/them', [ParentController::class, 'create'])->name('identity.children.create');
            Route::post('/con-cua-toi', [ParentController::class, 'store'])->name('identity.children.store');
            Route::post('/con-cua-toi/chuyen/{profile}', [ParentController::class, 'switch'])->name('identity.children.switch');
            Route::delete('/con-cua-toi/{profile}', [ParentController::class, 'destroy'])->name('identity.children.destroy');
        });
    });

}); // end middleware('web')
