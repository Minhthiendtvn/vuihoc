@extends('layouts.app')

@section('title', 'Thư viện bài học')

@section('content')
@include('library._styles')

<div class="lib-breadcrumb">
    <a href="{{ route('library.index') }}">🏠 Trang chủ</a> › Thư viện
</div>

<h1 class="vh-title">📚 Thư viện bài học</h1>
<p class="vh-subtitle">Tìm kiếm và lọc theo môn học, khối lớp, độ khó để tìm bài phù hợp với bạn.</p>

{{-- BỘ LỌC --}}
<form method="GET" action="{{ route('library.library') }}" class="lib-filters">
    <div class="lib-filter-row">
        <div class="vh-field search">
            <label for="q">Tìm kiếm</label>
            <input type="text" id="q" name="q" value="{{ $q }}" placeholder="Nhập tên môn, chủ đề, kỹ năng hoặc bài học…">
        </div>
        <div class="vh-field">
            <label for="mon">Môn học</label>
            <select id="mon" name="mon">
                <option value="">Tất cả các môn</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->slug }}" @selected($subjectSlug === $subject->slug)>{{ $subject->icon }} {{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="vh-field">
            <label for="khoi">Khối lớp</label>
            <select id="khoi" name="khoi">
                <option value="">Mọi khối</option>
                @for ($g = 6; $g <= 12; $g++)
                    <option value="{{ $g }}" @selected($gradeFilter === $g)>Lớp {{ $g }}</option>
                @endfor
            </select>
        </div>
        <div class="vh-field">
            <label for="do_kho">Độ khó</label>
            <select id="do_kho" name="do_kho">
                <option value="">Mọi độ khó</option>
                @foreach ($difficulties as $key => $label)
                    <option value="{{ $key }}" @selected($difficulty === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="vh-field" style="flex:0">
            <button type="submit" class="vh-btn vh-btn-primary">🔍 Tìm</button>
        </div>
    </div>
    @if ($q !== '' || $subjectSlug !== '' || $gradeFilter || $difficulty !== '')
        <div style="margin-top:12px">
            <a href="{{ route('library.library') }}" style="color:var(--danger);font-weight:600">✕ Xóa bộ lọc</a>
        </div>
    @endif
</form>

{{-- KẾT QUẢ: CHỦ ĐỀ --}}
<section class="lib-section">
    <h2 class="lib-section-title">🗂️ Chủ đề <span style="font-size:1rem;color:var(--ink-soft)">({{ $topics->count() }})</span></h2>
    @if ($topics->isEmpty())
        <div class="vh-card"><p class="vh-subtitle">Không tìm thấy chủ đề nào phù hợp. Thử đổi từ khóa hoặc nới lỏng bộ lọc nhé!</p></div>
    @else
        <div class="vh-grid">
            @foreach ($topics as $topic)
                <div class="lib-card" style="--subject-color: {{ $topic->subject?->color ?: '#4f46e5' }}">
                    <h3 class="lib-card-title">
                        <a href="{{ route('library.topic', $topic->slug) }}">{{ $topic->icon }} {{ $topic->name }}</a>
                    </h3>
                    <p class="lib-card-desc">{{ \Illuminate\Support\Str::limit($topic->description, 90) }}</p>
                    <div class="lib-card-meta">
                        <span class="lib-badge">{{ $topic->subject?->name }}</span>
                        @if ($topic->grade_min && $topic->grade_max)
                            <span class="lib-badge lib-badge-type">Lớp {{ $topic->grade_min }}–{{ $topic->grade_max }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- KẾT QUẢ: BÀI HỌC --}}
<section class="lib-section">
    <h2 class="lib-section-title">📝 Bài học <span style="font-size:1rem;color:var(--ink-soft)">({{ $lessons->count() }})</span></h2>
    @if ($lessons->isEmpty())
        <div class="vh-card"><p class="vh-subtitle">Không tìm thấy bài học nào phù hợp. Thử đổi từ khóa hoặc nới lỏng bộ lọc nhé!</p></div>
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
                        <span class="lib-badge lib-badge-diff-{{ $lesson->difficulty }}">{{ $difficulties[$lesson->difficulty] ?? $lesson->difficulty }}</span>
                        <span class="lib-badge lib-badge-type">⏱ {{ $lesson->duration_minutes }} phút</span>
                        <span class="lib-badge lib-badge-type">{{ $lesson->skill?->topic?->subject?->name }}</span>
                    </div>
                    <div class="lib-card-actions">
                        @include('library._play_dropdown', ['lesson' => $lesson])
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
