<?php

// routes/web_gameplay.php — SỞ HỮU: agent Gameplay (phase 2)
// Phạm vi: luồng chơi game bằng play token:
//   POST /bai-hoc/{lesson}/choi/{game_type} — tạo session chơi mới (gameplay.start)
//   GET  /choi/{token}          — màn hình chơi (KHÔNG trả đáp án đúng về client) (gameplay.show)
//   POST /choi/{token}/nop-bai  — nộp bài, chấm server-side (gameplay.submit)
//   GET  /choi/{token}/ket-qua — kết quả sau khi nộp (gameplay.result)
//
// Lưu ý: không dùng middleware 'auth' mặc định vì route('login') do agent Identity
// sở hữu (có thể chưa tồn tại). Controller tự kiểm tra active_profile() và
// redirect về trang đăng nhập khi chưa có hồ sơ.

use App\Http\Controllers\Gameplay\PlayController;
use Illuminate\Support\Facades\Route;

// QUAN TRỌNG: file này được require qua callback `then` trong bootstrap/app.php
// nên KHÔNG tự có middleware group 'web'. Bọc trong group 'web' để có session,
// CSRF, ShareErrorsFromSession ($errors trong view) — giống cách agent Library làm.
Route::middleware('web')->group(function () {

    Route::post('/bai-hoc/{lesson}/choi/{game_type}', [PlayController::class, 'start'])
        ->name('gameplay.start');

    Route::get('/choi/{token}', [PlayController::class, 'show'])
        ->name('gameplay.show');

    Route::post('/choi/{token}/nop-bai', [PlayController::class, 'submit'])
        ->name('gameplay.submit');

    Route::get('/choi/{token}/ket-qua', [PlayController::class, 'result'])
        ->name('gameplay.result');

});
