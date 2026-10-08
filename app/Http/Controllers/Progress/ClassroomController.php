<?php

namespace App\Http\Controllers\Progress;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\ClassMember;
use App\Models\Classroom;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    /** Danh sách lớp học của mình (role teacher,parent). */
    public function index(): View
    {
        $classrooms = auth()->user()->classrooms()
            ->withCount('members')
            ->orderByDesc('created_at')
            ->get();

        return view('classroom.index', [
            'classrooms' => $classrooms,
        ]);
    }

    /** Tạo lớp học mới: tự sinh mã 6 ký tự hoa/số, duy nhất. */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $classroom = Classroom::create([
            'owner_id'    => $request->user()->id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'code'        => self::generateUniqueCode(),
        ]);

        return redirect()
            ->route('classroom.show', $classroom)
            ->with('success', "Đã tạo lớp “{$classroom->name}”. Mã tham gia: {$classroom->code}");
    }

    /** Xem chi tiết lớp: thành viên + tiến độ từng thành viên (admin xem được mọi lớp). */
    public function show(Classroom $classroom): View|RedirectResponse
    {
        if ($classroom->owner_id !== auth()->id() && ! is_admin()) {
            abort(403);
        }

        $classroom->load(['members.profile']);

        $weekAgo = Carbon::now('Asia/Ho_Chi_Minh')->subDays(7);

        $members = $classroom->members->map(function (ClassMember $member) use ($weekAgo) {
            $profile = $member->profile;

            return [
                'member'   => $member,
                'profile'  => $profile,
                'weekPlays' => $profile
                    ? $profile->playSessions()
                        ->where('status', 'finished')
                        ->where('finished_at', '>=', $weekAgo)
                        ->count()
                    : 0,
            ];
        });

        return view('classroom.show', [
            'classroom'   => $classroom,
            'members'     => $members,
            'assignments' => Assignment::with(['lesson.skill.topic.subject', 'creator'])
                ->where('classroom_id', $classroom->id)
                ->orderByDesc('created_at')
                ->get(),
            'gameTypes'   => config('vuihoc.game_types', []),
        ]);
    }

    /**
     * Học viên tham gia lớp bằng mã: POST /lop/tham-gia {code}.
     * Cần active_profile. Kiểm tra mã tồn tại, chưa tham gia → tạo class_members.
     */
    public function join(Request $request): RedirectResponse
    {
        $profile = active_profile();

        if (! $profile) {
            return redirect()->route('progress.index')
                ->with('error', 'Vui lòng chọn hồ sơ học viên trước khi tham gia lớp.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:16',
        ]);

        $code = Str::upper(trim($validated['code']));
        $classroom = Classroom::where('code', $code)->first();

        if (! $classroom) {
            return redirect()->route('progress.index')
                ->with('error', "Không tìm thấy lớp nào với mã “{$code}”. Hãy kiểm tra lại mã tham gia.");
        }

        $exists = ClassMember::where('classroom_id', $classroom->id)
            ->where('profile_id', $profile->id)
            ->exists();

        if ($exists) {
            return redirect()->route('progress.index')
                ->with('info', "“{$profile->display_name}” đã là thành viên của lớp “{$classroom->name}”.");
        }

        ClassMember::create([
            'classroom_id' => $classroom->id,
            'profile_id'   => $profile->id,
            'joined_at'    => now(),
        ]);

        return redirect()->route('progress.index')
            ->with('success', "“{$profile->display_name}” đã tham gia lớp “{$classroom->name}” thành công! 🎉");
    }

    /** Sinh mã lớp 6 ký tự hoa/số, đảm bảo duy nhất trong bảng classrooms. */
    private static function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Classroom::where('code', $code)->exists());

        return $code;
    }
}
