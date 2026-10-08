<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Đăng ký / đăng nhập / đăng xuất (tự viết, không dùng Breeze).
 * Validation và thông báo 100% tiếng Việt.
 */
class AuthController extends Controller
{
    /** Form đăng nhập. */
    public function showLogin()
    {
        return view('auth.login');
    }

    /** Xử lý đăng nhập. */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Vui lòng nhập địa chỉ email.',
            'email.email'       => 'Địa chỉ email chưa đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Học viên chưa có hồ sơ → bắt buộc tạo hồ sơ trước khi dùng.
            if ($user->role === 'learner' && ! $user->learnerProfile) {
                return redirect()
                    ->route('profile.create')
                    ->with('info', 'Chào mừng bạn đến với VuiHoc! Hãy tạo hồ sơ học tập của bạn trước nhé.');
            }

            return redirect()->intended(route('home'));
        }

        return back()
            ->withErrors(['email' => 'Email hoặc mật khẩu chưa đúng. Vui lòng thử lại.'])
            ->onlyInput('email');
    }

    /** Form đăng ký. */
    public function showRegister()
    {
        return view('auth.register');
    }

    /** Xử lý đăng ký. Checkbox "Tôi là phụ huynh" → role parent, mặc định learner. */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Vui lòng nhập họ tên.',
            'name.max'           => 'Họ tên quá dài (tối đa 255 ký tự).',
            'email.required'     => 'Vui lòng nhập địa chỉ email.',
            'email.email'        => 'Địa chỉ email chưa đúng định dạng.',
            'email.unique'       => 'Email này đã được đăng ký. Mời bạn đăng nhập.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại chưa khớp.',
        ]);

        $role = $request->boolean('is_parent') ? 'parent' : 'learner';

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // cast 'hashed' trong model User
            'role'     => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($role === 'learner') {
            return redirect()
                ->route('profile.create')
                ->with('info', 'Đăng ký thành công! Hãy tạo hồ sơ học tập của bạn nào.');
        }

        return redirect()
            ->route('identity.children.index')
            ->with('info', 'Đăng ký thành công! Hãy thêm hồ sơ cho con của bạn để bắt đầu nhé.');
    }

    /** Đăng xuất (hỗ trợ cả GET theo link trong layout và POST). */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Bạn đã đăng xuất. Hẹn gặp lại!');
    }
}
