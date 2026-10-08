@extends('layouts.app')

@section('title', 'Trò chơi')

@section('content')
@include('library._styles')

<style>
.games-hero {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 55%, #a855f7 100%);
    border-radius: 24px; color: #fff; padding: 44px 32px; text-align: center;
    position: relative; overflow: hidden;
    box-shadow: 0 12px 32px rgba(79, 70, 229, 0.3); margin-bottom: 32px;
}
.games-hero::before, .games-hero::after {
    position: absolute; font-size: 3rem; opacity: 0.25;
}
.games-hero::before { content: "🎮"; top: 14px; left: 24px; transform: rotate(-12deg); }
.games-hero::after { content: "🏆"; bottom: 10px; right: 28px; }
.games-hero h1 { font-size: 2.2rem; margin: 0 0 8px; font-weight: 900; }
.games-hero p { font-size: 1.1rem; opacity: 0.95; margin: 0; }
.games-grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
.games-card {
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--game-color, var(--brand)) 14%, #ffffff),
        #ffffff 70%);
    border-radius: var(--radius); box-shadow: var(--shadow);
    padding: 28px 24px; text-align: center; display: flex; flex-direction: column; gap: 8px;
    border-top: 6px solid var(--game-color, var(--brand));
    transition: transform 0.12s ease, box-shadow 0.15s ease;
}
.games-card:hover { transform: translateY(-4px); box-shadow: 0 10px 26px rgba(79,70,229,0.2); }
.games-icon {
    width: 72px; height: 72px; border-radius: 50%; margin: 0 auto;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.2rem;
    background: color-mix(in srgb, var(--game-color, var(--brand)) 20%, #ffffff);
    box-shadow: inset 0 0 0 3px color-mix(in srgb, var(--game-color, var(--brand)) 40%, #ffffff);
}
.games-name { font-size: 1.3rem; font-weight: 900; margin: 0; color: var(--game-color, var(--brand)); }
.games-tagline { color: var(--ink-soft); font-size: 0.95rem; margin: 0; flex: 1; }
.games-count { font-size: 1.05rem; font-weight: 800; color: var(--game-color, var(--brand)); margin: 4px 0 8px; }
.games-count.zero { color: var(--ink-soft); font-weight: 600; }
.games-note { text-align: center; color: var(--ink-soft); margin-top: 24px; font-size: 0.95rem; }
</style>

<div class="lib-breadcrumb">
    <a href="{{ route('library.index') }}">🏠 Trang chủ</a> › Trò chơi
</div>

<div class="games-hero">
    <h1>🎮 Trò chơi</h1>
    <p>Chọn kiểu chơi bạn thích — Học mà chơi, chơi mà học! 🚀</p>
</div>

<div class="games-grid">
    @php
        $gameColors = ['quiz' => '#4f46e5', 'matching' => '#ec4899', 'sort' => '#f59e0b', 'fill' => '#10b981'];
    @endphp
    @foreach ($games as $game)
        <div class="games-card" style="--game-color: {{ $gameColors[$game['type']] ?? '#4f46e5' }}">
            <div class="games-icon">{{ $game['icon'] }}</div>
            <h2 class="games-name">{{ $game['name'] }}</h2>
            <p class="games-tagline">{{ $game['tagline'] }}</p>
            @if ($game['playable_count'] > 0)
                <p class="games-count">📚 {{ $game['playable_count'] }} bài chơi được</p>
                <a href="{{ route('games.show', $game['type']) }}" class="vh-btn vh-btn-primary">Xem các bài ➜</a>
            @else
                <p class="games-count zero">Chưa có bài nào chơi được</p>
                <button class="vh-btn vh-btn-ghost" disabled>Chưa có bài</button>
            @endif
        </div>
    @endforeach
</div>

<p class="games-note">
    @if ($grade)
        📌 Đang hiện bài phù hợp với khối lớp {{ $grade }} của hồ sơ bạn.
    @else
        📌 Bạn đang xem với tư cách khách — hiện bài của mọi khối lớp.
        <a href="{{ route('auth.login') }}" style="color:var(--brand);font-weight:700">Đăng nhập</a> để lọc theo khối lớp của mình nhé!
    @endif
</p>
@endsection
