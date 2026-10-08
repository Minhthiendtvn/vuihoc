<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Identity\Concerns\HasAvatarOptions;
use App\Models\LearnerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Phụ huynh (role parent) quản lý các hồ sơ con.
 * Hồ sơ con: parent_id = id phụ huynh, user_id = null.
 * Chuyển hồ sơ đang xem bằng session('active_profile_id')
 * (helper active_profile() sẽ đọc session này).
 * Mọi route bọc middleware 'role:parent' trong routes/web_identity.php.
 */
class ParentController extends Controller
{
    use HasAvatarOptions;

    /** Trang "Con của tôi" — liệt kê các hồ sơ con. */
    public function index()
    {
        $children = Auth::user()->childProfiles()->orderByDesc('id')->get();

        return view('identity.parent.index', [
            'children' => $children,
            'activeId' => session('active_profile_id'),
        ]);
    }

    /** Form thêm hồ sơ con mới. */
    public function create()
    {
        return view('identity.parent.create', $this->avatarFormData());
    }

    /** Lưu hồ sơ con mới (parent_id = phụ huynh hiện tại, user_id = null). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:50'],
            'avatar_emoji' => ['required', 'string', $this->avatarEmojiRule()],
            'grade'        => ['required', 'integer', 'between:6,12'],
        ], [
            'display_name.required'  => 'Vui lòng nhập tên hiển thị của con.',
            'display_name.max'       => 'Tên hiển thị quá dài (tối đa 50 ký tự).',
            'avatar_emoji.required'  => 'Vui lòng chọn một biểu tượng avatar cho con.',
            'avatar_emoji.in'        => 'Biểu tượng avatar không hợp lệ.',
            'grade.required'         => 'Vui lòng chọn lớp của con.',
            'grade.between'          => 'Lớp phải từ 6 đến 12.',
        ]);

        $profile = LearnerProfile::create([
            'user_id'      => null,
            'parent_id'    => Auth::id(),
            'display_name' => $data['display_name'],
            'avatar_emoji' => $data['avatar_emoji'],
            'grade'        => $data['grade'],
            'daily_goal'   => 3,
            'font_size'    => 'normal',
        ]);

        // Con đầu tiên → tự đặt làm hồ sơ đang xem.
        if (! session('active_profile_id')) {
            session(['active_profile_id' => $profile->id]);
        }

        return redirect()
            ->route('identity.children.index')
            ->with('success', "Đã thêm hồ sơ cho “{$profile->display_name}”. 🎉");
    }

    /** Chuyển hồ sơ đang xem sang một hồ sơ con khác (chỉ con của mình). */
    public function switch(LearnerProfile $profile)
    {
        $this->ensureOwnChild($profile);

        session(['active_profile_id' => $profile->id]);

        return back()->with('success', "Đang xem hồ sơ của “{$profile->display_name}”.");
    }

    /** Xóa một hồ sơ con (chỉ con của mình). */
    public function destroy(LearnerProfile $profile)
    {
        $this->ensureOwnChild($profile);

        $name = $profile->display_name;
        $profile->delete();

        if ((int) session('active_profile_id') === (int) $profile->id) {
            session()->forget('active_profile_id');
        }

        return redirect()
            ->route('identity.children.index')
            ->with('success', "Đã xóa hồ sơ của “{$name}”.");
    }

    /** Chặn truy cập hồ sơ không thuộc về phụ huynh hiện tại. */
    protected function ensureOwnChild(LearnerProfile $profile): void
    {
        if ((int) $profile->parent_id !== (int) Auth::id()) {
            abort(403, 'Hồ sơ này không thuộc về bạn.');
        }
    }
}
