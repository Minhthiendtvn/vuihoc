<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\Topic;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    use ResolvesSlug;

    public function index()
    {
        $skills = Skill::with(['topic.subject'])
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.skills.index', compact('skills'));
    }

    public function create()
    {
        $topics = Topic::with('subject')->orderBy('name')->get();

        return view('admin.skills.create', compact('topics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Skill::class, $validated['slug'] ?? null, $validated['name']);
        $validated['is_demo'] = false;

        Skill::create($validated);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Đã thêm kỹ năng “'.$validated['name'].'”.');
    }

    public function edit(Skill $skill)
    {
        $topics = Topic::with('subject')->orderBy('name')->get();

        return view('admin.skills.edit', compact('skill', 'topics'));
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Skill::class, $validated['slug'] ?? null, $validated['name'], $skill->id);

        $skill->update($validated);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Đã cập nhật kỹ năng “'.$skill->name.'”.');
    }

    public function destroy(Skill $skill)
    {
        $name = $skill->name;
        $skill->delete();

        return redirect()->route('admin.skills.index')
            ->with('success', 'Đã xóa kỹ năng “'.$name.'” cùng toàn bộ bài học và câu hỏi bên trong.');
    }

    private function rules(): array
    {
        return [
            'topic_id'    => 'required|exists:topics,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0|max:9999',
        ];
    }

    private function messages(): array
    {
        return [
            'topic_id.required' => 'Vui lòng chọn chủ đề.',
            'topic_id.exists'   => 'Chủ đề đã chọn không tồn tại.',
            'name.required'     => 'Vui lòng nhập tên kỹ năng.',
            'name.max'          => 'Tên kỹ năng không quá 255 ký tự.',
            'sort_order.integer' => 'Thứ tự hiển thị phải là số nguyên.',
        ];
    }
}
