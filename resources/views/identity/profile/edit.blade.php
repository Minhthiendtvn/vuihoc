@extends('layouts.app')

@section('title', 'Sửa hồ sơ')

@section('content')
<div class="vh-card" style="max-width: 560px; margin: 0 auto;">
    <h1 class="vh-title">✏️ Sửa hồ sơ</h1>
    <p class="vh-subtitle">Cập nhật thông tin hiển thị của hồ sơ
        <strong>{{ $profile->display_name }}</strong>.</p>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')
        @include('identity.profile._form', ['profile' => $profile])
        <button type="submit" class="vh-btn vh-btn-primary" style="width: 100%;">Lưu thay đổi</button>
    </form>

    <p style="margin-top: 16px; text-align: center;">
        <a href="{{ route('profile.show') }}">← Quay lại hồ sơ</a>
    </p>
</div>
@endsection
