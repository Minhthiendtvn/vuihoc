<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\PlaySession;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            ['icon' => '📚', 'label' => 'Môn học',   'value' => Subject::count(),  'link' => route('admin.subjects.index')],
            ['icon' => '🗂️', 'label' => 'Chủ đề',    'value' => Topic::count(),    'link' => route('admin.topics.index')],
            ['icon' => '📖', 'label' => 'Bài học',   'value' => Lesson::count(),   'link' => route('admin.lessons.index')],
            ['icon' => '❓', 'label' => 'Câu hỏi',   'value' => Question::count(), 'link' => route('admin.questions.index')],
            ['icon' => '👥', 'label' => 'Người dùng', 'value' => User::count(),     'link' => route('admin.users.index')],
            ['icon' => '🎮', 'label' => 'Lượt chơi',  'value' => PlaySession::count(), 'link' => null],
        ];

        $recentPlays = PlaySession::with(['profile', 'lesson'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPlays'));
    }
}
