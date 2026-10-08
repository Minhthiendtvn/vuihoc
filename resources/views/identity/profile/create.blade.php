@extends('layouts.app')

@section('title', 'Tạo hồ sơ học tập')

@section('content')
<div class="vh-card" style="max-width: 560px; margin: 0 auto;">
    <h1 class="vh-title">🌟 Tạo hồ sơ học tập</h1>
    <p class="vh-subtitle">Hồ sơ giúp VuiHoc theo dõi XP, cấp độ và chuỗi ngày học của bạn.</p>

    <form method="POST" action="{{ route('profile.store') }}">
        @csrf
        @include('identity.profile._form', ['profile' => null])
        <button type="submit" class="vh-btn vh-btn-primary vh-btn-big" style="width: 100%;">
            Bắt đầu học! 🚀
        </button>
    </form>
</div>
@endsection
