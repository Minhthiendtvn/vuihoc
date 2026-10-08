@extends('layouts.app')

@section('title', 'Trò chơi: ' . $gameName)

@section('content')
@include('library._styles')

<div class="lib-breadcrumb">
    <a href="{{ route('library.index') }}">🏠 Trang chủ</a> ›
    <a href="{{ route('games.index') }}">🎮 Trò chơi</a> › {{ $gameName }}
</div>

<h1 class="vh-title">{{ $gameIcon }} {{ $gameName }}</h1>
<p class="vh-subtitle">
    {{ $gameTagline }} — {{ $lessons->count() }} bài chơi được
    @if ($grade)
        (khối lớp {{ $grade }})
    @else
        (mọi khối lớp)
    @endif.
    Chọn bài rồi bấm <strong>▶ Chơi ngay</strong> để bắt đầu! 🚀
</p>

@if ($lessons->isEmpty())
    <div class="vh-card">
        <p class="vh-subtitle">Chưa có bài học nào chơi được kiểu {{ $gameName }} cho khối lớp của bạn. Hãy thử kiểu chơi khác nhé! 🎮</p>
        <a href="{{ route('games.index') }}" class="vh-btn vh-btn-primary">← Quay lại các kiểu chơi</a>
    </div>
@else
    <div class="vh-grid">
        @foreach ($lessons as $lesson)
            <div class="lib-card" style="--subject-color: {{ $lesson->skill?->topic?->subject?->color ?: '#4f46e5' }}">
                <h3 class="lib-card-title">
                    <a href="{{ route('library.lesson', $lesson->slug) }}">{{ $lesson->title }}</a>
                </h3>
                <p class="lib-card-desc">{{ \Illuminate\Support\Str::limit($lesson->objective, 90) }}</p>
                <div class="lib-card-meta">
                    @if ($lesson->grade)
                        <span class="lib-badge vh-badge-grade">🎓 Lớp {{ $lesson->grade }}</span>
                    @endif
                    <span class="lib-badge">{{ $lesson->skill?->topic?->subject?->name }}</span>
                    <span class="lib-badge lib-badge-type">{{ $lesson->skill?->topic?->name }}</span>
                    <span class="lib-badge lib-badge-diff-{{ $lesson->difficulty }}">{{ $difficulties[$lesson->difficulty] ?? $lesson->difficulty }}</span>
                    <span class="lib-badge lib-badge-type">🧩 {{ $lesson->playable_question_count }} câu {{ mb_strtolower($gameName, 'UTF-8') }}</span>
                </div>
                <div class="lib-card-actions">
                    @guest
                        <a href="{{ route('auth.login') }}" class="vh-btn vh-btn-primary">▶ Chơi ngay</a>
                    @else
                        <form method="POST" action="{{ route('gameplay.start', ['lesson' => $lesson->slug, 'game_type' => $gameType]) }}">
                            @csrf
                            <button type="submit" class="vh-btn vh-btn-primary">▶ Chơi ngay</button>
                        </form>
                    @endguest
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
