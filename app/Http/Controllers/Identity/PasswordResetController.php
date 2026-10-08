<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Quên / đặt lại mật khẩu bằng token lưu ở bảng password_reset_tokens.
 * Chế độ demo: KHÔNG gửi email thật — token và link được ghi vào
 * storage/logs/password-resets.log để người kiểm thử lấy dùng.
 * Token có hiệu lực 60 phút.
 */
class PasswordResetController extends Controller
{
    /** Form nhập email để yêu cầu đặt lại mật khẩu. */
    public function showForgot()
    {
        return view('auth.passwords.email');
    }

    /** Tạo token, lưu vào DB, ghi log demo. Luôn trả cùng một thông báo. */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Địa chỉ email chưa đúng định dạng.',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            $url = route('auth.password.reset', ['token' => $token]) . '?email=' . urlencode($email);

            $line = '[' . now()->toDateTimeString() . ']'
                . ' email=' . $email
                . ' token=' . $token
                . ' url=' . $url;

            file_put_contents(
                storage_path('logs/password-resets.log'),
                $line . PHP_EOL,
                FILE_APPEND
            );
        }

        return back()->with(
            'success',
            'Nếu email này đã được đăng ký, liên kết đặt lại mật khẩu đã được ghi vào file log '
            . '(chế độ demo — hệ thống không gửi email thật).'
        );
    }

    /** Form đặt lại mật khẩu (kèm token trên URL). */
    public function showReset(Request $request, string $token)
    {
        $email = (string) $request->query('email', '');

        if (! $this->findValidToken($email, $token)) {
            return redirect()
                ->route('auth.password.email')
                ->with('error', 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn (quá 60 phút).');
        }

        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /** Đặt lại mật khẩu bằng token. */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required'     => 'Thiếu mã token đặt lại mật khẩu.',
            'email.required'     => 'Vui lòng nhập địa chỉ email.',
            'email.email'        => 'Địa chỉ email chưa đúng định dạng.',
            'password.required'  => 'Vui lòng nhập mật khẩu mới.',
            'password.min'       => 'Mật khẩu mới phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại chưa khớp.',
        ]);

        if (! $this->findValidToken($data['email'], $data['token'])) {
            return back()
                ->withErrors(['email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.'])
                ->withInput();
        }

        $user = User::where('email', $data['email'])->firstOrFail();

        $user->forceFill([
            'password'       => $data['password'], // cast 'hashed' trong model User
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()
            ->route('auth.login')
            ->with('success', 'Đặt lại mật khẩu thành công. Mời bạn đăng nhập lại.');
    }

    /**
     * Tìm token còn hiệu lực (trong 60 phút) cho email.
     * Token lưu dạng hash trong DB nên so bằng Hash::check.
     */
    protected function findValidToken(string $email, string $token): ?object
    {
        if ($email === '' || $token === '') {
            return null;
        }

        $row = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $row) {
            return null;
        }

        if (now()->diffInMinutes($row->created_at) > 60) {
            return null;
        }

        if (! Hash::check($token, $row->token)) {
            return null;
        }

        return $row;
    }
}
