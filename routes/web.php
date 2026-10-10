<?php
use App\Http\Controllers\AiTutorPageController;
Route::get('/ai-tutor', [AiTutorPageController::class, 'index'])
    ->name('ai.tutor');
// routes/web.php — không đặt route ở đây.
// Route web được chia theo chủ sở hữu phase 2:
//   web_identity.php  (agent Identity) — auth, hồ sơ, phụ huynh
//   web_library.php   (agent Library)  — trang chủ, thư viện, môn/chủ đề/bài học
//   web_gameplay.php  (agent Gameplay) — luồng chơi game bằng play token
//   web_progress.php  (agent Progress) — tiến độ, phụ huynh, lớp học
//   web_admin.php     (agent Admin)    — /admin/* (middleware role:admin)
