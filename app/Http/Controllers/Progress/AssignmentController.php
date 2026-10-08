<?php

namespace App\Http\Controllers\Progress;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\ClassMember;
use App\Models\Classroom;
use App\Models\LearnerProfile;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    // ------------------------------------------------------------------
    // Form giao bài cho 1 hồ sơ con — role parent, con phải thuộc về mình.
    // GET /phu-huynh/con/{profile}/giao-bai
    // ------------------------------------------------------------------
    public function createForProfile(Request $request, LearnerProfile $profile): View
    {
        $this->ensureOwnChild($profile);

        return view('assignments.create', [
            'targetType'  => 'profile',
            'target'      => $profile,
            'targetName'  => $profile->display_name,
            'storeRoute'  => route('assignments.store.profile', $profile),
            'backUrl'     => route('parent.index', ['profile' => $profile->id]),
            'lessons'     => $this->lessonOptions(),
            'gameTypes'   => config('vuihoc.game_types', []),
            'presetSkill' => (int) $request->query('skill', 0),
        ]);
    }

    // ------------------------------------------------------------------
    // Lưu bài giao cho 1 hồ sơ con — POST /phu-huynh/con/{profile}/giao-bai
    // ------------------------------------------------------------------
    public function storeForProfile(Request $request, LearnerProfile $profile): RedirectResponse
    {
        $this->ensureOwnChild($profile);

        $validated = $this->validateAssignment($request);
        $this->ensurePublishedLesson($validated['lesson_id']);

        Assignment::create([
            'created_by'         => $request->user()->id,
            'learner_profile_id' => $profile->id,
            'classroom_id'       => null,
            'lesson_id'          => $validated['lesson_id'],
            'game_type'          => $validated['game_type'],
            'note'               => $validated['note'] ?? null,
            'deadline'           => $validated['deadline'] ?? null,
            'share_token'        => $this->uniqueShareToken(),
        ]);

        return redirect()
            ->route('parent.index', ['profile' => $profile->id])
            ->with('success', "Đã giao bài cho “{$profile->display_name}” thành công! 📝");
    }

    // ------------------------------------------------------------------
    // Form giao bài cho cả lớp — role teacher/parent, lớp phải của mình.
    // GET /lop/{classroom}/giao-bai
    // ------------------------------------------------------------------
    public function createForClassroom(Classroom $classroom): View
    {
        $this->ensureOwnClass($classroom);

        return view('assignments.create', [
            'targetType'  => 'classroom',
            'target'      => $classroom,
            'targetName'  => 'Cả lớp ' . $classroom->name,
            'storeRoute'  => route('assignments.store.classroom', $classroom),
            'backUrl'     => route('classroom.show', $classroom),
            'lessons'     => $this->lessonOptions(),
            'gameTypes'   => config('vuihoc.game_types', []),
            'presetSkill' => 0,
        ]);
    }

    // ------------------------------------------------------------------
    // Lưu bài giao cho cả lớp — POST /lop/{classroom}/giao-bai
    // ------------------------------------------------------------------
    public function storeForClassroom(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->ensureOwnClass($classroom);

        $validated = $this->validateAssignment($request);
        $this->ensurePublishedLesson($validated['lesson_id']);

        Assignment::create([
            'created_by'         => $request->user()->id,
            'learner_profile_id' => null,
            'classroom_id'       => $classroom->id,
            'lesson_id'          => $validated['lesson_id'],
            'game_type'          => $validated['game_type'],
            'note'               => $validated['note'] ?? null,
            'deadline'           => $validated['deadline'] ?? null,
            'share_token'        => $this->uniqueShareToken(),
        ]);

        return redirect()
            ->route('classroom.show', $classroom)
            ->with('success', "Đã giao bài cho cả lớp “{$classroom->name}” thành công! 📝");
    }

    // ------------------------------------------------------------------
    // Xoá bài đã giao — chỉ người tạo mới được xoá.
    // DELETE /giao-bai/{assignment}
    // ------------------------------------------------------------------
    public function destroy(Assignment $assignment): RedirectResponse
    {
        if ((int) $assignment->created_by !== (int) auth()->id() && ! is_admin()) {
            abort(403, 'Bạn không có quyền xoá bài giao này.');
        }

        $back = $assignment->isForClass()
            ? route('classroom.show', $assignment->classroom_id)
            : route('parent.index', ['profile' => $assignment->learner_profile_id]);

        $assignment->delete();

        return redirect($back)->with('success', 'Đã xoá bài được giao.');
    }

    // ------------------------------------------------------------------
    // Trang chia sẻ công khai — GET /bai-giao/{token} (không cần đăng nhập
    // để XEM; bấm "Chơi ngay" thì gameplay yêu cầu đăng nhập như thường).
    // ------------------------------------------------------------------
    public function showByToken(string $token): View
    {
        $assignment = Assignment::with(['lesson.skill.topic.subject', 'creator', 'profile', 'classroom'])
            ->where('share_token', $token)
            ->firstOrFail();

        $profile = active_profile();
        $isTarget = false;
        if ($profile) {
            $isTarget = (int) $assignment->learner_profile_id === (int) $profile->id
                || ($assignment->classroom_id && ClassMember::where('profile_id', $profile->id)
                    ->where('classroom_id', $assignment->classroom_id)->exists());
        }

        return view('assignments.shared', [
            'assignment' => $assignment,
            'gameTypes'  => config('vuihoc.game_types', []),
            'profile'    => $profile,
            'isTarget'   => $isTarget,
        ]);
    }

    // ============================ helpers =============================

    /** Phụ huynh chỉ được giao bài cho con của mình (admin được giao cho mọi hồ sơ). */
    private function ensureOwnChild(LearnerProfile $profile): void
    {
        if ((int) $profile->parent_id !== (int) auth()->id() && ! is_admin()) {
            abort(403, 'Hồ sơ này không thuộc về bạn.');
        }
    }

    /** GV/phụ huynh chỉ được giao bài trong lớp mình quản lý (admin được giao mọi lớp). */
    private function ensureOwnClass(Classroom $classroom): void
    {
        if ((int) $classroom->owner_id !== (int) auth()->id() && ! is_admin()) {
            abort(403, 'Bạn không quản lý lớp học này.');
        }
    }

    /** Danh sách bài học đã xuất bản cho form chọn (kèm môn/kỹ năng/số câu). */
    private function lessonOptions()
    {
        return Lesson::published()
            ->withQuestionTypeCounts()
            ->with(['skill.topic.subject'])
            ->orderBy('title')
            ->get()
            ->map(fn (Lesson $l) => [
                'id'         => $l->id,
                'title'      => $l->title,
                'subject'    => $l->skill?->topic?->subject?->name ?? '',
                'skill'      => $l->skill?->name ?? '',
                'skill_id'   => $l->skill_id,
                'difficulty' => $l->difficulty,
                'counts'     => [
                    'quiz'     => (int) ($l->quiz_count ?? 0),
                    'matching' => (int) ($l->matching_count ?? 0),
                    'sort'     => (int) ($l->sort_count ?? 0),
                    'fill'     => (int) ($l->fill_count ?? 0),
                ],
            ])
            ->values();
    }

    private function validateAssignment(Request $request): array
    {
        return $request->validate([
            'lesson_id' => 'required|integer|exists:lessons,id',
            'game_type' => 'required|string|in:quiz,matching,sort,fill',
            'note'      => 'nullable|string|max:1000',
            'deadline'  => 'nullable|date|after:now',
        ], [
            'lesson_id.required' => 'Vui lòng chọn bài học để giao.',
            'lesson_id.exists'   => 'Bài học không tồn tại.',
            'game_type.required' => 'Vui lòng chọn kiểu chơi.',
            'game_type.in'       => 'Kiểu chơi không hợp lệ.',
            'note.max'           => 'Ghi chú tối đa 1000 ký tự.',
            'deadline.date'      => 'Hạn hoàn thành không đúng định dạng.',
            'deadline.after'     => 'Hạn hoàn thành phải là thời gian trong tương lai.',
        ]);
    }

    /** Chặn bài học nháp/không tồn tại khi giao (kẻ gian POST tay). */
    private function ensurePublishedLesson(int $lessonId): void
    {
        $lesson = Lesson::find($lessonId);
        if (! $lesson || $lesson->status !== 'published') {
            abort(422, 'Bài học không tồn tại hoặc chưa được xuất bản.');
        }
    }

    private function uniqueShareToken(): string
    {
        do {
            $token = Str::random(40);
        } while (Assignment::where('share_token', $token)->exists());

        return $token;
    }
}
