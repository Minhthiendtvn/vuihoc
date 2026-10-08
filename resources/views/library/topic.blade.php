@extends('layouts.app')

@section('title', $topic->name)

@section('content')
@include('library._styles')

<div class="lib-breadcrumb">
    <a href="{{ route('library.index') }}">🏠 Trang chủ</a> ›
    <a href="{{ route('library.library') }}">Thư viện</a> ›
    <a href="{{ route('library.subject', $topic->subject->slug) }}">{{ $topic->subject->icon }} {{ $topic->subject->name }}</a> ›
    {{ $topic->name }}
</div>

<div class="vh-card" style="border-top: 6px solid {{ $topic->subject->color ?: '#4f46e5' }}">
    <div style="display:flex;gap:12px;align-items:flex-start;justify-content:space-between">
        <div>
            <h1 class="vh-title" style="margin:0">{{ $topic->icon }} {{ $topic->name }}</h1>
            <p class="vh-subtitle" style="margin:8px 0 0">{{ $topic->description }}</p>
            <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
                <span class="lib-badge">{{ $topic->subject->name }}</span>
                @if ($topic->grade_min && $topic->grade_max)
                    <span class="lib-badge lib-badge-type">Lớp {{ $topic->grade_min }}–{{ $topic->grade_max }}</span>
                @endif
            </div>
        </div>
        @include('library._favorite_button', [
            'targetType' => 'topic',
            'targetId' => $topic->id,
            'isFav' => in_array($topic->id, $favTopicIds, true),
        ])
    </div>
</div>

@forelse ($topic->skills as $skill)
    <section class="lib-section">
        <h2 class="lib-section-title">🎯 {{ $skill->name }}</h2>
        @if ($skill->description)
            <p class="lib-section-sub">{{ $skill->description }}</p>
        @endif

        @if ($skill->lessons->isEmpty())
            <div class="vh-card"><p class="vh-subtitle">Kỹ năng này chưa có bài học nào. Hãy quay lại sau nhé!</p></div>
        @else
            <div class="vh-grid">
                @foreach ($skill->lessons as $lesson)
                    <div class="lib-card" style="--subject-color: {{ $topic->subject->color ?: '#4f46e5' }}">
                        <h3 class="lib-card-title">
                            <a href="{{ route('library.lesson', $lesson->slug) }}">{{ $lesson->title }}</a>
                        </h3>
                        <p class="lib-card-desc">{{ \Illuminate\Support\Str::limit($lesson->objective, 90) }}</p>
                        <div class="lib-card-meta">
                            @if ($lesson->grade)
                                <span class="lib-badge vh-badge-grade">🎓 Lớp {{ $lesson->grade }}</span>
                            @endif
                            <span class="lib-badge lib-badge-diff-{{ $lesson->difficulty }}">{{ config('vuihoc.difficulties.' . $lesson->difficulty, $lesson->difficulty) }}</span>
                            <span class="lib-badge lib-badge-type">⏱ {{ $lesson->duration_minutes }} phút</span>
                        </div>
                        <div class="lib-card-meta">
                            @foreach ($gameTypes as $type)
                                <span class="lib-badge lib-badge-type">{{ config('vuihoc.game_types.' . $type, $type) }}: {{ (int) ($lesson->{"{$type}_count"} ?? 0) }}</span>
                            @endforeach
                        </div>
                        <div class="lib-card-actions" style="justify-content:space-between;align-items:center">
                            @include('library._play_dropdown', ['lesson' => $lesson])
                            @include('library._favorite_button', [
                                'targetType' => 'lesson',
                                'targetId' => $lesson->id,
                                'isFav' => in_array($lesson->id, $favLessonIds, true),
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@empty
    <div class="vh-card"><p class="vh-subtitle">Chủ đề này chưa có kỹ năng nào. Nội dung mới sắp ra mắt!</p></div>
@endforelse
@endsection
