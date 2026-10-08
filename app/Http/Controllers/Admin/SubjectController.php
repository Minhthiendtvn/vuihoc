<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    use ResolvesSlug;

    public function index()
    {
        $subjects = Subject::withCount('topics')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Subject::class, $validated['slug'] ?? null, $validated['name']);
        $validated['icon'] = $validated['icon'] ?: '📚';
        $validated['color'] = $validated['color'] ?: '#4f46e5';
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_demo'] = false;

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Đã thêm môn học “'.$validated['name'].'”.');
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Subject::class, $validated['slug'] ?? null, $validated['name'], $subject->id);
        $validated['icon'] = $validated['icon'] ?: '📚';
        $validated['color'] = $validated['color'] ?: '#4f46e5';
        $validated['is_published'] = $request->boolean('is_published');

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Đã cập nhật môn học “'.$subject->name.'”.');
    }

    public function destroy(Subject $subject)
    {
        $name = $subject->name;
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Đã xóa môn học “'.$name.'” cùng toàn bộ chủ đề, kỹ năng, bài học và câu hỏi bên trong.');
    }

    private function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:16',
            'color'       => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0|max:9999',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required'   => 'Vui lòng nhập tên môn học.',
            'name.max'        => 'Tên môn học không quá 255 ký tự.',
            'slug.max'        => 'Slug không quá 255 ký tự.',
            'icon.max'        => 'Biểu tượng chỉ nên là 1 emoji ngắn.',
            'color.regex'     => 'Màu phải là mã hex dạng #rrggbb, ví dụ #4f46e5.',
            'sort_order.integer' => 'Thứ tự hiển thị phải là số nguyên.',
            'sort_order.min'  => 'Thứ tự hiển thị không được âm.',
        ];
    }
}
