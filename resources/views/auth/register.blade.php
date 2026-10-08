@extends('layouts.app')

@section('title', 'Đăng ký')

@section('content')
<div class="vh-card" style="max-width: 480px; margin: 0 auto;">
    <h1 class="vh-title">📝 Đăng ký tài khoản</h1>
    <p class="vh-subtitle">Miễn phí — bắt đầu hành trình học mà chơi ngay!</p>

    <form method="POST" action="{{ route('auth.register.post') }}">
        @csrf

        <div class="vh-field">
            <label for="name">Họ và tên</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   placeholder="Nguyễn Văn An">
        </div>

        <div class="vh-field">
            <label for="email">Địa chỉ email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   placeholder="vidu@email.com">
        </div>

        <div class="vh-field">
            <label for="password">Mật khẩu (ít nhất 8 ký tự)</label>
            <input id="password" type="password" name="password" required placeholder="••••••••">
        </div>

        <div class="vh-field">
            <label for="password_confirmation">Nhập lại mật khẩu</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   placeholder="••••••••">
        </div>

        <div class="vh-field">
            <label style="display: flex; align-items: flex-start; gap: 8px; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="is_parent" value="1" style="width: auto; margin-top: 4px;"
                       {{ old('is_parent') ? 'checked' : '' }}>
                <span>Tôi là <strong>phụ huynh</strong> — muốn tạo và quản lý hồ sơ học tập cho con
                      (không tick: tài khoản học viên tự học)</span>
            </label>
        </div>

        <button type="submit" class="vh-btn vh-btn-primary" style="width: 100%;">Tạo tài khoản</button>
    </form>

    <p style="margin-top: 16px; text-align: center;">
        Đã có tài khoản? <a href="{{ route('auth.login') }}">Đăng nhập</a>
    </p>
</div>
@endsection
