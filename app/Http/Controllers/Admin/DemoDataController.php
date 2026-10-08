<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Support\Facades\DB;

class DemoDataController extends Controller
{
    public function index()
    {
        $counts = [
            'Môn học'   => Subject::where('is_demo', true)->count(),
            'Chủ đề'    => Topic::where('is_demo', true)->count(),
            'Kỹ năng'   => Skill::where('is_demo', true)->count(),
            'Bài học'   => Lesson::where('is_demo', true)->count(),
            'Câu hỏi'   => Question::where('is_demo', true)->count(),
            'Huy hiệu'  => Badge::where('is_demo', true)->count(),
        ];

        return view('admin.demo.index', compact('counts'));
    }

    public function destroy()
    {
        DB::transaction(function () {
            // Xóa theo thứ tự khóa ngoại: câu hỏi (cascade xóa dữ liệu con)
            // → bài học → kỹ năng → chủ đề → môn học → huy hiệu (cascade xóa profile_badges).
            Question::where('is_demo', true)->delete();
            Lesson::where('is_demo', true)->delete();
            Skill::where('is_demo', true)->delete();
            Topic::where('is_demo', true)->delete();
            Subject::where('is_demo', true)->delete();
            Badge::where('is_demo', true)->delete();
        });

        return redirect()->route('admin.demo')
            ->with('success', 'Đã xóa toàn bộ dữ liệu mẫu.');
    }
}
