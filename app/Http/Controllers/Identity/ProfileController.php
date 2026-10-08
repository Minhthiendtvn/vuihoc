<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Identity\Concerns\HasAvatarOptions;
use App\Models\LearnerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Hồ sơ học viên của chính người dùng.
 * - Học viên (role learner): tự tạo hồ sơ sau khi đăng ký, sửa hồ sơ sau này.
 * - Phụ huynh (role parent): xem/sửa hồ sơ con đang được chọn
 *   qua session('active_profile_id') — xem chi tiết trong helper active_profile().
 */
class ProfileController extends Controller
{
    use HasAvatarOptions;

    /** Xem hồ sơ đang hoạt động. */
    public function show()
    {
        $user = Auth::user();
        $profile = active_profile();

        if ($user->role === 'learner' && ! $profile) {
            return redirect()
                ->route('profile.create')
                ->with('info', 'Bạn chưa có hồ sơ học tập. Hãy tạo một hồ sơ nhé!');
        }

        if (! $profile) {
            if ($user->role === 'parent') {
                return redirect()
                    ->route('identity.children.index')
                    ->with('info', 'Hãy chọn một hồ sơ con để xem.');
            }

            return redirect()->route('home');
        }

        return view('identity.profile.show', [
            'profile'   => $profile,
            'fontSizes' => static::fontSizeOptions(),
        ]);
    }

    /** Form tạo hồ sơ (chỉ học viên chưa có hồ sơ). */
    public function create()
    {
        $user = Auth::user();

        if ($user->role !== 'learner') {
            abort(403, 'Chỉ học viên mới tạo hồ sơ tại đây. Phụ huynh thêm hồ sơ con ở mục "Con của tôi".');
        }

        if ($user->learnerProfile) {
            return redirect()->route('profile.show');
        }

        return view('identity.profile.create', $this->avatarFormData());
    }

    /** Lưu hồ sơ học viên mới. */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'learner') {
            abort(403);
        }

        if ($user->learnerProfile) {
            return redirect()->route('profile.show');
        }

        $data = $this->validateProfile($request);

        LearnerProfile::create([
            'user_id'      => $user->id,
            'display_name' => $data['display_name'],
            'avatar_emoji' => $data['avatar_emoji'],
            'grade'        => $data['grade'],
            'daily_goal'   => $data['daily_goal'],
            'font_size'    => $data['font_size'],
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Đã tạo hồ sơ học tập! Chúc bạn học thật vui nhé. 🎉');
    }

    /** Form sửa hồ sơ đang hoạt động. */
    public function edit()
    {
        $profile = active_profile();

        if (! $profile) {
            return redirect()->route('profile.show');
        }

        return view('identity.profile.edit', array_merge(
            ['profile' => $profile],
            $this->avatarFormData()
        ));
    }

    /** Cập nhật hồ sơ đang hoạt động. */
    public function update(Request $request)
    {
        $profile = active_profile();

        if (! $profile) {
            return redirect()->route('profile.show');
        }

        $data = $this->validateProfile($request);

        $profile->update([
            'display_name' => $data['display_name'],
            'avatar_emoji' => $data['avatar_emoji'],
            'grade'        => $data['grade'],
            'daily_goal'   => $data['daily_goal'],
            'font_size'    => $data['font_size'],
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Đã cập nhật hồ sơ của bạn. 👍');
    }

    /** Validation dùng chung cho tạo/sửa hồ sơ. */
    protected function validateProfile(Request $request): array
    {
        return $request->validate([
            'display_name' => ['required', 'string', 'max:50'],
            'avatar_emoji' => ['required', 'string', $this->avatarEmojiRule()],
            'grade'        => ['required', 'integer', 'between:6,12'],
            'daily_goal'   => ['required', 'integer', 'between:1,10'],
            'font_size'    => ['required', 'in:normal,large,xlarge'],
        ], [
            'display_name.required' => 'Vui lòng nhập tên hiển thị.',
            'display_name.max'      => 'Tên hiển thị quá dài (tối đa 50 ký tự).',
            'avatar_emoji.required' => 'Vui lòng chọn một biểu tượng avatar.',
            'avatar_emoji.in'       => 'Biểu tượng avatar không hợp lệ.',
            'grade.required'        => 'Vui lòng chọn lớp.',
            'grade.between'         => 'Lớp phải từ 6 đến 12.',
            'daily_goal.required'   => 'Vui lòng nhập mục tiêu mỗi ngày.',
            'daily_goal.between'    => 'Mục tiêu mỗi ngày từ 1 đến 10 thử thách.',
            'font_size.required'    => 'Vui lòng chọn cỡ chữ.',
            'font_size.in'          => 'Cỡ chữ không hợp lệ.',
        ]);
    }
}
