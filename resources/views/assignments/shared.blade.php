@extends('layouts.app')

@section('title', 'Bài được giao: ' . $assignment->lesson?->title)

@section('content')
<style>
    .asg-share { max-width: 640px; margin: 24px auto; text-align: center; }
    .asg-share-card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); padding: 32px 28px; }
    .asg-share-ico { font-size: 3rem; }
    .asg-share-meta { color: var(--ink-soft); margin: 10px 0; line-height: 1.8; }
    .asg-share-note { background: #fef9c3; border-radius: 12px; padding: 12px 16px; margin: 16px 0; text-align: left; }
    .asg-share-note strong { display: block; margin-bottom: 4px; }
</style>

<div class="asg-share">
    <div class="asg-share-card">
        <div class="asg-share-ico">📝</div>
        <h1 class="vh-title" style="font-size: 1.5rem;">{{ $assignment->lesson?->title ?? 'Bài học' }}</h1>
        <div class="asg-share-meta">
            📚 {{ $assignment->lesson?->skill?->topic?->subject?->name ?? '' }}
            · 🎮 {{ $gameTypes[$assignment->game_type] ?? $assignment->game_type }}<br>
            👤 Người giao: <strong>{{ $assignment->creator?->name ?? '—' }}</strong>
            · 🎯 Dành cho: <strong>{{ $assignment->targetName() }}</strong><br>
            @if ($assignment->deadline)
                ⏰ Hạn: <strong>{{ $assignment->deadline->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</strong>
                @if ($assignment->isOverdue()) <span style="color: var(--danger, #dc2626); font-weight: 700;">(đã quá hạn)</span> @endif
            @else
                ⏰ Không giới hạn thời gian
            @endif
            @if ($assignment->status === 'done')
                <br>✅ <strong style="color: var(--brand-2, #16a34a);">Bài này đã được hoàn thành.</strong>
            @endif
        </div>

        @if ($assignment->note)
            <div class="asg-share-note">
                <strong>💬 Lời nhắn:</strong>
                {{ $assignment->note }}
            </div>
        @endif

        @if ($profile && ! $isTarget)
            <p class="vh-subtitle">ℹ️ Bài này được giao cho <strong>{{ $assignment->targetName() }}</strong>, không phải hồ sơ hiện tại của bạn. Bạn vẫn có thể chơi thử.</p>
        @endif

        @auth
            <form method="POST" action="{{ route('gameplay.start', ['lesson' => $assignment->lesson?->slug ?? $assignment->lesson_id, 'game_type' => $assignment->game_type]) }}">
                @csrf
                <button type="submit" class="vh-btn vh-btn-primary" style="font-size: 1.1rem; padding: 14px 36px;">▶ Chơi ngay</button>
            </form>
        @else
            <a href="{{ route('auth.login') }}" class="vh-btn vh-btn-primary" style="font-size: 1.1rem; padding: 14px 36px;">🔑 Đăng nhập để chơi</a>
            <p class="vh-subtitle" style="margin-top: 10px;">Cần đăng nhập để bắt đầu làm bài được giao.</p>
        @endauth
    </div>
</div>
@endsection
