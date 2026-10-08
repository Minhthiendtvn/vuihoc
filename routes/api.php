<?php

// routes/api.php — SỞ HỮU: agent API (phase 3)
// Phạm vi: /api/v1/* — API cho app mobile sau này.
// Xác thực: Sanctum bearer token. Quy ước đặt tên route: api.v1.<hành-động>.

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\PlayController;
use App\Http\Controllers\Api\ProgressController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Công khai
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/subjects', [LibraryController::class, 'subjects'])->name('subjects.index');
    Route::get('/subjects/{slug}/topics', [LibraryController::class, 'topics'])->name('subjects.topics');

    // Cần token
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/play/start', [PlayController::class, 'start'])->name('play.start');
        Route::post('/play/{token}/submit', [PlayController::class, 'submit'])->name('play.submit');
        Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
    });
});
