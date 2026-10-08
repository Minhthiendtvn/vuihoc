<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    use ResolvesSlug;

    /** Mã tiêu chí xét huy hiệu (theo CONTRACT) + nhãn tiếng Việt. */
    public const CRITERIA = [
        'first_play'  => 'Chơi lần đầu',
        'streak_3'    => 'Chuỗi học 3 ngày',
        'streak_7'    => 'Chuỗi học 7 ngày',
        'streak_30'   => 'Chuỗi học 30 ngày',
        'xp_1000'     => 'Tích lũy 1.000 XP',
        'xp_5000'     => 'Tích lũy 5.000 XP',
        'perfect_5'   => '5 lượt chơi đạt điểm tuyệt đối',
        'explorer_3'  => 'Khám phá 3 môn học',
        'scholar_10'  => 'Hoàn thành 10 bài học',
    ];

    public function index()
    {
        $badges = Badge::orderBy('id')->paginate(15);

        return view('admin.badges.index', [
            'badges' => $badges,
            'criteriaLabels' => self::CRITERIA,
        ]);
    }

    public function create()
    {
        return view('admin.badges.create', ['criteriaLabels' => self::CRITERIA]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Badge::class, $validated['slug'] ?? null, $validated['name']);
        $validated['icon'] = $validated['icon'] ?: '🏅';
        $validated['is_demo'] = false;

        Badge::create($validated);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Đã thêm huy hiệu “'.$validated['name'].'”.');
    }

    public function edit(Badge $badge)
    {
        return view('admin.badges.edit', [
            'badge' => $badge,
            'criteriaLabels' => self::CRITERIA,
        ]);
    }

    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $validated['slug'] = $this->resolveSlug(Badge::class, $validated['slug'] ?? null, $validated['name'], $badge->id);
        $validated['icon'] = $validated['icon'] ?: '🏅';

        $badge->update($validated);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Đã cập nhật huy hiệu “'.$badge->name.'”.');
    }

    public function destroy(Badge $badge)
    {
        $name = $badge->name;
        $badge->delete();

        return redirect()->route('admin.badges.index')
            ->with('success', 'Đã xóa huy hiệu “'.$name.'”.');
    }

    private function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:16',
            'criteria'    => 'required|in:'.implode(',', array_keys(self::CRITERIA)),
            'threshold'   => 'required|integer|min:1|max:1000000',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required'     => 'Vui lòng nhập tên huy hiệu.',
            'name.max'          => 'Tên huy hiệu không quá 255 ký tự.',
            'icon.max'          => 'Biểu tượng chỉ nên là 1 emoji ngắn.',
            'criteria.required' => 'Vui lòng chọn tiêu chí xét huy hiệu.',
            'criteria.in'       => 'Tiêu chí không hợp lệ.',
            'threshold.required' => 'Vui lòng nhập ngưỡng đạt huy hiệu.',
            'threshold.integer' => 'Ngưỡng phải là số nguyên.',
            'threshold.min'     => 'Ngưỡng tối thiểu là 1.',
            'threshold.max'     => 'Ngưỡng quá lớn.',
        ];
    }
}
