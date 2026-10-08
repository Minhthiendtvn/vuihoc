@extends('layouts.app')

@section('title', 'Hồ sơ của tôi')

@section('content')
<div class="vh-card" style="max-width: 640px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <span style="font-size: 3.5rem; line-height: 1;">{{ $profile->avatar_emoji }}</span>
        <div>
            <h1 class="vh-title" style="margin-bottom: 4px;">{{ $profile->display_name }}</h1>
            <p class="vh-subtitle" style="margin: 0;">
                Lớp {{ $profile->grade }} · Mục tiêu {{ $profile->daily_goal }} thử thách/ngày ·
                Cỡ chữ: {{ $fontSizes[$profile->font_size] ?? $profile->font_size }}
            </p>
        </div>
    </div>

    <div class="vh-grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));">
        <div class="vh-card" style="margin: 0; text-align: center; padding: 16px;">
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--accent);">⭐ {{ $profile->total_xp }}</div>
            <div style="color: var(--ink-soft); font-size: .9rem;">Tổng XP</div>
        </div>
        <div class="vh-card" style="margin: 0; text-align: center; padding: 16px;">
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--brand);">🏅 Cấp {{ $profile->level }}</div>
            <div style="color: var(--ink-soft); font-size: .9rem;">Cấp độ</div>
        </div>
        <div class="vh-card" style="margin: 0; text-align: center; padding: 16px;">
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--brand-2);">🔥 {{ $profile->current_streak }}</div>
            <div style="color: var(--ink-soft); font-size: .9rem;">Chuỗi ngày học</div>
        </div>
    </div>

    <div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('profile.edit') }}" class="vh-btn vh-btn-primary">✏️ Sửa hồ sơ</a>
        <a href="{{ route('home') }}" class="vh-btn vh-btn-ghost">Về trang chủ</a>
    </div>

    <p style="margin-top: 16px; font-size: .85rem; color: var(--ink-soft);">
        💡 Ghi chú: cỡ chữ bạn chọn hiện lưu trong hồ sơ và sẽ được áp dụng cho toàn bộ
        trang ở giai đoạn tiếp theo (hiện tại giao diện dùng cỡ chữ chuẩn).
    </p>
</div>
@endsection
