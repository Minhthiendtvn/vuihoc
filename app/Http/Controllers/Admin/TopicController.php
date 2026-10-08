<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    use ResolvesSlug;

    public function index()
    {
        $topics = Topic::with(['subject'])
            ->withCount('skills')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.topics.index', compact('topics'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.topics.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Topic::class, $validated['slug'] ?? null, $validated['name']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_demo'] = false;

        Topic::create($validated);

        return redirect()->route('admin.topics.index')
            ->with('success', 'Đã thêm chủ đề “'.$validated['name'].'”.');
    }

    public function edit(Topic $topic)
    {
        $subjects = Subject::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.topics.edit', compact('topic', 'subjects'));
    }

    public function update(Request $request, Topic $topic)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Topic::class, $validated['slug'] ?? null, $validated['name'], $topic->id);
        $validated['is_published'] = $request->boolean('is_published');

        $topic->update($validated);

        return redirect()->route('admin.topics.index')
            ->with('success', 'Đã cập nhật chủ đề “'.$topic->name.'”.');
    }

    public function destroy(Topic $topic)
    {
        $name = $topic->name;
        $topic->delete();

        return redirect()->route('admin.topics.index')
            ->with('success', 'Đã xóa chủ đề “'.$name.'” cùng toàn bộ kỹ năng, bài học và câu hỏi bên trong.');
    }

    private function rules(): array
    {
        return [
            'subject_id'  => 'required|exists:subjects,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:16',
            'grade_min'   => 'required|integer|min:6|max:12|lte:grade_max',
            'grade_max'   => 'required|integer|min:6|max:12|gte:grade_min',
            'sort_order'  => 'nullable|integer|min:0|max:9999',
        ];
    }

    private function messages(): array
    {
        return [
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'subject_id.exists'   => 'Môn học đã chọn không tồn tại.',
            'name.required'       => 'Vui lòng nhập tên chủ đề.',
            'name.max'            => 'Tên chủ đề không quá 255 ký tự.',
            'icon.max'            => 'Biểu tượng chỉ nên là 1 emoji ngắn.',
            'grade_min.required'  => 'Vui lòng chọn khối lớp bắt đầu.',
            'grade_min.min'       => 'Khối lớp từ 6 đến 12.',
            'grade_min.max'       => 'Khối lớp từ 6 đến 12.',
            'grade_min.lte'       => 'Khối lớp bắt đầu không được lớn hơn khối lớp kết thúc.',
            'grade_max.required'  => 'Vui lòng chọn khối lớp kết thúc.',
            'grade_max.min'       => 'Khối lớp từ 6 đến 12.',
            'grade_max.max'       => 'Khối lớp từ 6 đến 12.',
            'grade_max.gte'       => 'Khối lớp kết thúc không được nhỏ hơn khối lớp bắt đầu.',
            'sort_order.integer'  => 'Thứ tự hiển thị phải là số nguyên.',
        ];
    }
}
