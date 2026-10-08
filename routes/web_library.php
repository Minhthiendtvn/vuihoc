<?php

// routes/web_library.php — SỞ HỮU: agent Library (phase 2)
// Phạm vi: trang chủ /, thư viện /thu-vien, môn học /mon-hoc/{slug},
// chủ đề /chu-de/{slug}, bài học /bai-hoc/{slug}, yêu thích /yeu-thich.
// Tên route: library.* (theo CONTRACT mục (c)).

use App\Http\Controllers\Library\GameController;
use App\Http\Controllers\Library\LibraryController;
use Illuminate\Support\Facades\Route;

// QUAN TRỌNG: các route trong file này được require qua callback `then`
// trong bootstrap/app.php nên KHÔNG tự có middleware group 'web'.
// Bọc trong group 'web' để có session, CSRF, ShareErrorsFromSession ($errors trong view).
Route::middleware('web')->group(function () {

    // Trang chủ — thay stub "VuiHoc OK" của phase 1.
    Route::get('/', [LibraryController::class, 'index'])->name('library.index');

    // Bí danh 'home' cho layout phase 1 (dùng route('home')).
    // Dùng URI riêng /trang-chu để không trùng URI với route library.index
    // (Laravel key route theo method+URI nên 2 route cùng URI sẽ ghi đè nhau).
    Route::redirect('/trang-chu', '/')->name('home');

    Route::get('/thu-vien', [LibraryController::class, 'library'])->name('library.library');
    Route::get('/mon-hoc/{slug}', [LibraryController::class, 'subject'])->name('library.subject');
    Route::get('/chu-de/{slug}', [LibraryController::class, 'topic'])->name('library.topic');
    Route::get('/bai-hoc/{slug}', [LibraryController::class, 'lesson'])->name('library.lesson');

    Route::get('/yeu-thich', [LibraryController::class, 'favorites'])->name('library.favorites');
    Route::post('/yeu-thich/toggle', [LibraryController::class, 'toggleFavorite'])->name('library.favorites.toggle');

    // Trang "Trò chơi": liệt kê 4 kiểu chơi (games.index) và các bài chơi được
    // của từng kiểu (games.show). Lọc khối lớp theo hồ sơ trong GameController.
    Route::get('/tro-choi', [GameController::class, 'index'])->name('games.index');
    Route::get('/tro-choi/{game_type}', [GameController::class, 'show'])->name('games.show');
});
