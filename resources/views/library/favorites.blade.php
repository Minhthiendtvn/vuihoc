@extends('layouts.app')

@section('title', 'Mục yêu thích của tôi')

@section('content')
@include('library._styles')

<div class="lib-breadcrumb">
    <a href="{{ route('library.index') }}">🏠 Trang chủ</a> › Yêu thích
</div>

<h1 class="vh-title">❤️ Mục yêu thích của tôi</h1>
<p class="vh-subtitle">Những bài học và chủ đề bạn đã lưu lại để học sau.</p>

@if ($favLessons->isEmpty() && $favTopics->isEmpty())
    <div class="vh-card" style="text-align:center;padding:40px">
        <div style="font-size:3rem">💌</div>
        <p><strong>Danh sách yêu thích của bạn đang trống.</strong></p>
        <p class="vh-subtitle">Khi xem bài học hoặc chủ đề, hãy bấm nút ♡ để lưu lại những nội dung bạn thích nhé!</p>
        <a href="{{ route('library.library') }}" class="vh-btn vh-btn-primary">📚 Khám phá thư viện</a>
    </div>
@else
    @if ($favTopics->isNotEmpty())
        <section class="lib-section">
            <h2 class="lib-section-title">🗂️ Chủ đề đã lưu ({{ $favTopics->count() }})</h2>
            <div class="vh-grid">
                @foreach ($favTopics as $topic)
                    <div class="lib-card" style="--subject-color: {{ $topic->subject?->color ?: '#4f46e5' }}">
                        <h3 class="lib-card-title">
                            <a href="{{ route('library.topic', $topic->slug) }}">{{ $topic->icon }} {{ $topic->name }}</a>
                        </h3>
                        <p class="lib-card-desc">{{ \Illuminate\Support\Str::limit($topic->description, 90) }}</p>
                        <div class="lib-card-actions" style="justify-content:space-between;align-items:center">
                            <a href="{{ route('library.topic', $topic->slug) }}" class="vh-btn vh-btn-ghost" style="padding:8px 18px">Mở chủ đề →</a>
                            @include('library._favorite_button', ['targetType' => 'topic', 'targetId' => $topic->id, 'isFav' => true])
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($favLessons->isNotEmpty())
        <section class="lib-section">
            <h2 class="lib-section-title">📝 Bài học đã lưu ({{ $favLessons->count() }})</h2>
            <div class="vh-grid">
                @foreach ($favLessons as $lesson)
                    <div class="lib-card" style="--subject-color: {{ $lesson->skill?->topic?->subject?->color ?: '#4f46e5' }}">
                        <h3 class="lib-card-title">
                            <a href="{{ route('library.lesson', $lesson->slug) }}">{{ $lesson->title }}</a>
                        </h3>
                        <p class="lib-card-desc">{{ \Illuminate\Support\Str::limit($lesson->objective, 90) }}</p>
                        <div class="lib-card-meta">
                            <span class="lib-badge lib-badge-type">{{ $lesson->skill?->topic?->subject?->name }}</span>
                            <span class="lib-badge lib-badge-type">⏱ {{ $lesson->duration_minutes }} phút</span>
                        </div>
                        <div class="lib-card-actions" style="justify-content:space-between;align-items:center">
                            <a href="{{ route('library.lesson', $lesson->slug) }}" class="vh-btn vh-btn-primary" style="padding:8px 18px">▶ Chơi ngay</a>
                            @include('library._favorite_button', ['targetType' => 'lesson', 'targetId' => $lesson->id, 'isFav' => true])
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endif
@endsection
