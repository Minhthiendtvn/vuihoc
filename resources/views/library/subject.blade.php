@extends('layouts.app')

@section('title', $subject->name)

@section('content')
@include('library._styles')

<div class="lib-breadcrumb">
    <a href="{{ route('library.index') }}">🏠 Trang chủ</a> ›
    <a href="{{ route('library.library') }}">Thư viện</a> ›
    {{ $subject->name }}
</div>

<div class="vh-card" style="border-top: 6px solid {{ $subject->color ?: '#4f46e5' }}">
    <div style="display:flex;gap:16px;align-items:center">
        <div style="font-size:3rem">{{ $subject->icon ?: '📖' }}</div>
        <div>
            <h1 class="vh-title" style="margin:0">{{ $subject->name }}</h1>
            <p class="vh-subtitle" style="margin:4px 0 0">{{ $subject->description }}</p>
        </div>
    </div>
</div>

<section class="lib-section">
    <h2 class="lib-section-title">🗂️ Các chủ đề
        @if ($grade)
            <span style="font-size:1rem;color:var(--ink-soft)">(dành cho lớp {{ $grade }} của bạn)</span>
        @endif
    </h2>
    @if ($topics->isEmpty())
        <div class="vh-card">
            <p class="vh-subtitle">
                @if ($grade)
                    Chưa có chủ đề nào của môn {{ $subject->name }} dành cho lớp {{ $grade }}.
                    <a href="{{ route('library.library', ['mon' => $subject->slug]) }}">Xem tất cả chủ đề của môn này →</a>
                @else
                    Các chủ đề của môn {{ $subject->name }} đang được chuẩn bị. Hãy quay lại sau nhé!
                @endif
            </p>
        </div>
    @else
        <div class="vh-grid">
            @foreach ($topics as $topic)
                <div class="lib-card" style="--subject-color: {{ $subject->color ?: '#4f46e5' }}">
                    <h3 class="lib-card-title">
                        <a href="{{ route('library.topic', $topic->slug) }}">{{ $topic->icon }} {{ $topic->name }}</a>
                    </h3>
                    <p class="lib-card-desc">{{ \Illuminate\Support\Str::limit($topic->description, 100) }}</p>
                    <div class="lib-card-meta">
                        <span class="lib-badge">{{ $topic->skills_count }} kỹ năng</span>
                        @if ($topic->grade_min && $topic->grade_max)
                            <span class="lib-badge lib-badge-type">Lớp {{ $topic->grade_min }}–{{ $topic->grade_max }}</span>
                        @endif
                    </div>
                    <div class="lib-card-actions">
                        <a href="{{ route('library.topic', $topic->slug) }}" class="vh-btn vh-btn-ghost" style="padding:8px 18px">Khám phá →</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
