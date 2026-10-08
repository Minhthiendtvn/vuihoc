@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="vh-card" style="max-width: 480px; margin: 0 auto;">
    <h1 class="vh-title">📧 Quên mật khẩu</h1>
    <p class="vh-subtitle">Nhập email đã đăng ký — hệ thống sẽ tạo liên kết đặt lại mật khẩu
        (hiệu lực 60 phút).</p>

    <div class="vh-alert vh-alert-info">
        Chế độ demo: liên kết đặt lại được ghi vào file log của máy chủ
        (<code>storage/logs/password-resets.log</code>), không gửi email thật.
    </div>

    <form method="POST" action="{{ route('auth.password.send') }}">
        @csrf

        <div class="vh-field">
            <label for="email">Địa chỉ email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="vidu@email.com">
        </div>

        <button type="submit" class="vh-btn vh-btn-primary" style="width: 100%;">Gửi liên kết đặt lại</button>
    </form>

    <p style="margin-top: 16px; text-align: center;">
        <a href="{{ route('auth.login') }}">← Quay lại đăng nhập</a>
    </p>
</div>
@endsection
