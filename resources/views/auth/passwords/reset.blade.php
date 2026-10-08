@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="vh-card" style="max-width: 480px; margin: 0 auto;">
    <h1 class="vh-title">🔄 Đặt lại mật khẩu</h1>
    <p class="vh-subtitle">Nhập mật khẩu mới cho tài khoản của bạn (ít nhất 8 ký tự).</p>

    <form method="POST" action="{{ route('auth.password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="vh-field">
            <label for="email">Địa chỉ email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required
                   placeholder="vidu@email.com">
        </div>

        <div class="vh-field">
            <label for="password">Mật khẩu mới</label>
            <input id="password" type="password" name="password" required autofocus placeholder="••••••••">
        </div>

        <div class="vh-field">
            <label for="password_confirmation">Nhập lại mật khẩu mới</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   placeholder="••••••••">
        </div>

        <button type="submit" class="vh-btn vh-btn-primary" style="width: 100%;">Đặt lại mật khẩu</button>
    </form>
</div>
@endsection
