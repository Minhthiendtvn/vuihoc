<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Skill;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    use ResolvesSlug;

    public function index(Request $request)
    {
        $lessons = Lesson::with(['skill.topic.subject'])
            ->withCount('questions')
            ->when($request->filled('grade'), fn ($q) => $q->where('grade', (int) $request->input('grade')))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.lessons.index', compact('lessons'));
    }

    public function create()
    {
        $skills = Skill::with('topic.subject')->orderBy('name')->get();

        return view('admin.lessons.create', compact('skills'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Lesson::class, $validated['slug'] ?? null, $validated['title']);
        $validated['is_demo'] = false;
        $this->defaultGrade($validated);

        $lesson = Lesson::create($validated);

        return redirect()->route('admin.lessons.preview', $lesson)
            ->with('success', 'Đã thêm bài học “'.$validated['title'].'”. Hãy thêm câu hỏi cho bài học.');
    }

    public function edit(Lesson $lesson)
    {
        $skills = Skill::with('topic.subject')->orderBy('name')->get();

        return view('admin.lessons.edit', compact('lesson', 'skills'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Lesson::class, $validated['slug'] ?? null, $validated['title'], $lesson->id);
        $this->defaultGrade($validated);

        $lesson->update($validated);

        return redirect()->route('admin.lessons.index')
            ->with('success', 'Đã cập nhật bài học “'.$lesson->title.'”.');
    }

    public function destroy(Lesson $lesson)
    {
        $title = $lesson->title;
        $lesson->delete();

        return redirect()->route('admin.lessons.index')
            ->with('success', 'Đã xóa bài học “'.$title.'” cùng toàn bộ câu hỏi bên trong.');
    }

    /**
     * Xem trước bài học: chỉ đọc, liệt kê câu hỏi KÈM ĐÁP ÁN để admin kiểm tra.
     */
    public function preview(Lesson $lesson)
    {
        $lesson->load([
            'skill.topic.subject',
            'questions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'questions.options' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'questions.pairs' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'questions.sortItems' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'questions.fillAnswers' => fn ($q) => $q->orderBy('blank_index')->orderBy('sort_order')->orderBy('id'),
        ]);

        return view('admin.lessons.preview', compact('lesson'));
    }

    /**
     * Nếu không chọn khối lớp, mặc định theo grade_min của chủ đề
     * chứa kỹ năng đã chọn.
     */
    private function defaultGrade(array &$validated): void
    {
        if (! empty($validated['grade'])) {
            return;
        }

        $skill = Skill::with('topic')->find($validated['skill_id']);
        $validated['grade'] = $skill?->topic?->grade_min;
    }

    private function rules(): array
    {
        return [
            'skill_id'         => 'required|exists:skills,id',
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255',
            'objective'        => 'nullable|string',
            'summary'          => 'nullable|string|max:10000',
            'difficulty'       => 'required|in:de,trung_binh,kho',
            'duration_minutes' => 'nullable|integer|min:1|max:180',
            'grade'            => 'nullable|integer|min:6|max:12',
            'instructions'     => 'nullable|string',
            'sort_order'       => 'nullable|integer|min:0|max:9999',
            'status'           => 'required|in:draft,published',
        ];
    }

    private function messages(): array
    {
        return [
            'skill_id.required' => 'Vui lòng chọn kỹ năng.',
            'skill_id.exists'   => 'Kỹ năng đã chọn không tồn tại.',
            'title.required'    => 'Vui lòng nhập tiêu đề bài học.',
            'title.max'         => 'Tiêu đề không quá 255 ký tự.',
            'difficulty.required' => 'Vui lòng chọn độ khó.',
            'difficulty.in'     => 'Độ khó không hợp lệ.',
            'duration_minutes.integer' => 'Thời lượng phải là số nguyên (phút).',
            'duration_minutes.min' => 'Thời lượng tối thiểu 1 phút.',
            'duration_minutes.max' => 'Thời lượng tối đa 180 phút.',
            'grade.integer'    => 'Khối lớp phải là số nguyên.',
            'grade.min'        => 'Khối lớp phải từ 6 đến 12.',
            'grade.max'        => 'Khối lớp phải từ 6 đến 12.',
            'status.required'   => 'Vui lòng chọn trạng thái.',
            'status.in'         => 'Trạng thái không hợp lệ.',
            'sort_order.integer' => 'Thứ tự hiển thị phải là số nguyên.',
        ];
    }
}
