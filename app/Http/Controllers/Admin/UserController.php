<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('learnerProfile')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $user->load(['learnerProfile', 'childProfiles', 'classrooms']);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể đổi vai trò của chính mình (để tránh tự khóa khỏi trang quản trị).');
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,teacher,parent,learner',
        ], [
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in'       => 'Vai trò không hợp lệ.',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã đổi vai trò của “'.$user->name.'” thành '.$this->roleLabel($validated['role']).'.');
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'admin'   => 'Quản trị viên',
            'teacher' => 'Giáo viên',
            'parent'  => 'Phụ huynh',
            'learner' => 'Học viên',
            default   => $role,
        };
    }
}
