@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
<div class="vh-card" style="max-width: 480px; margin: 0 auto;">
    <h1 class="vh-title">🔑 Đăng nhập</h1>
    <p class="vh-subtitle">Chào mừng bạn quay lại VuiHoc!</p>

    <form method="POST" action="{{ route('auth.login.post') }}">
        @csrf

        <div class="vh-field">
            <label for="email">Địa chỉ email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="vidu@email.com">
        </div>

        <div class="vh-field">
            <label for="password">Mật khẩu</label>
            <input id="password" type="password" name="password" required placeholder="••••••••">
        </div>

        <div class="vh-field">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="remember" value="1" style="width: auto;" {{ old('remember') ? 'checked' : '' }}>
                Ghi nhớ đăng nhập
            </label>
        </div>

        <button type="submit" class="vh-btn vh-btn-primary" style="width: 100%;">Đăng nhập</button>
    </form>

    <p style="margin-top: 16px; text-align: center;">
        <a href="{{ route('auth.password.email') }}">Quên mật khẩu?</a>
        &nbsp;·&nbsp;
        <a href="{{ route('auth.register') }}">Chưa có tài khoản? Đăng ký</a>
    </p>
</div>
@endsection
