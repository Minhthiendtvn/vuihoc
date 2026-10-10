@extends('layouts.app')

@section('title', $lesson->title)

@section('content')
@include('library._styles')
@php
    $subject = $lesson->skill?->topic?->subject;
    $playUrl = fn (string $type) => \App\Http\Controllers\Library\LibraryController::playUrl($lesson, $type);
@endphp

<div class="lib-breadcrumb">
    <a href="{{ route('library.index') }}">🏠 Trang chủ</a> ›
    <a href="{{ route('library.library') }}">Thư viện</a> ›
    @if ($subject)
        <a href="{{ route('library.subject', $subject->slug) }}">{{ $subject->icon }} {{ $subject->name }}</a> ›
    @endif
    @if ($lesson->skill?->topic)
        <a href="{{ route('library.topic', $lesson->skill->topic->slug) }}">{{ $lesson->skill->topic->name }}</a> ›
    @endif
    {{ $lesson->title }}
</div>

<div class="vh-card" style="border-top: 6px solid {{ $subject?->color ?: '#4f46e5' }}">
    <div style="display:flex;gap:12px;align-items:flex-start;justify-content:space-between">
        <div>
            <h1 class="vh-title" style="margin:0">📝 {{ $lesson->title }}</h1>
            <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
                <span class="lib-badge lib-badge-diff-{{ $lesson->difficulty }}">{{ $difficulties[$lesson->difficulty] ?? $lesson->difficulty }}</span>
                <span class="lib-badge lib-badge-type">⏱ {{ $lesson->duration_minutes }} phút</span>
                @if ($lesson->skill)
                    <span class="lib-badge lib-badge-type">🎯 {{ $lesson->skill->name }}</span>
                @endif
            </div>
        </div>
        @include('library._favorite_button', ['targetType' => 'lesson', 'targetId' => $lesson->id, 'isFav' => $isFav])
    </div>

    @if ($lesson->objective)
        <h3 style="margin:24px 0 8px">🎯 Mục tiêu bài học</h3>
        <p>{{ $lesson->objective }}</p>
    @endif

    @if ($lesson->instructions)
        <h3 style="margin:24px 0 8px">📋 Hướng dẫn</h3>
        <p style="white-space:pre-line">{{ $lesson->instructions }}</p>
    @endif

    @if ($lesson->summary)
        @php
            $summaryLines = preg_split('/\r\n|\r|\n/', (string) $lesson->summary);
            $summaryBlocks = [];
            $pendingBullets = [];
            foreach ($summaryLines as $sline) {
                $st = trim($sline);
                if ($st === '') {
                    if ($pendingBullets) {
                        $summaryBlocks[] = ['bullets' => $pendingBullets];
                        $pendingBullets = [];
                    }
                    continue;
                }
                if (str_starts_with($st, '- ') || str_starts_with($st, '•')) {
                    $prefixLen = str_starts_with($st, '- ') ? 2 : 1;
                    $pendingBullets[] = trim(mb_substr($st, $prefixLen));
                } else {
                    if ($pendingBullets) {
                        $summaryBlocks[] = ['bullets' => $pendingBullets];
                        $pendingBullets = [];
                    }
                    $summaryBlocks[] = ['p' => $st];
                }
            }
            if ($pendingBullets) {
                $summaryBlocks[] = ['bullets' => $pendingBullets];
            }
        @endphp
        <div class="vh-summary" x-data="{ open: true }" style="--subject-color: {{ $subject?->color ?: '#4f46e5' }}">
            <button type="button" class="vh-summary-toggle" @click="open = !open" :aria-expanded="open ? 'true' : 'false'">
                <span>📖 Tóm tắt bài học</span>
                <span class="vh-summary-chevron" :class="{ 'open': open }">▾</span>
            </button>
            <div class="vh-summary-body" x-show="open" x-transition>
                @foreach ($summaryBlocks as $block)
                    @if (isset($block['bullets']))
                        <ul>
                            @foreach ($block['bullets'] as $bullet)
                                <li>{{ $bullet }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $block['p'] }}</p>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
<section class="lib-section">
    <div class="vh-card">
        <h2 class="lib-section-title">✨ Hỏi AI về bài học</h2>

        <p class="lib-section-sub">
            AI đã biết bạn đang học bài “{{ $lesson->title }}”.
        </p>

        <textarea
            id="ai-question"
            rows="4"
            style="width:100%;padding:12px;border-radius:12px"
            placeholder="Nhập câu hỏi của bạn..."
        ></textarea>

        <div style="margin-top:10px">
            <button
                type="button"
                class="vh-btn vh-btn-primary"
                onclick="askAI()"
            >
                ✨ Hỏi AI
            </button>
        </div>

        <div
            id="ai-loading"
            style="display:none;margin-top:15px"
        >
            ⏳ AI đang suy nghĩ...
        </div>

        <div
            id="ai-answer"
            style="margin-top:15px;white-space:pre-wrap;line-height:1.7"
        ></div>
    </div>
</section>

<script>
async function askAI() {
    const question = document.getElementById('ai-question').value.trim();
    const answer = document.getElementById('ai-answer');
    const loading = document.getElementById('ai-loading');

    if (!question) {
        alert('Bạn hãy nhập câu hỏi.');
        return;
    }

    answer.textContent = '';
    loading.style.display = 'block';

    try {
        const response = await fetch('/api/ai/tutor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                lesson_id: {{ $lesson->id }},
                question: question
            })
        });

        const data = await response.json();

        if (data.success) {
            answer.textContent = data.answer;
        } else {
            answer.textContent = 'AI hiện chưa thể trả lời.';
        }

    } catch (error) {
        answer.textContent = 'Có lỗi kết nối với AI.';
    } finally {
        loading.style.display = 'none';
    }
}
</script>
<section class="lib-section">
    <h2 class="lib-section-title">🎮 Chọn kiểu chơi</h2>
    <p class="lib-section-sub">Mỗi kiểu chơi dùng cùng nội dung bài học nhưng cách trả lời khác nhau. Hãy thử hết nhé!</p>
    <div class="lib-gametype-grid">
        @foreach ($gameTypes as $type => $label)
            @php $count = (int) ($lesson->{"{$type}_count"} ?? 0); @endphp
            <div class="lib-gametype-card {{ $count === 0 ? 'disabled' : '' }}">
                <p class="lib-gt-name">
                    @if ($type === 'quiz') 🎯
                    @elseif ($type === 'matching') 🧩
                    @elseif ($type === 'sort') 🔀
                    @else ✍️
                    @endif
                    {{ $label }}
                </p>
                <p class="lib-gt-count">{{ $count }} câu hỏi sẵn có</p>
                @guest
                    <a href="{{ route('auth.login') }}" class="vh-btn vh-btn-primary" style="padding:10px 20px">▶ Chơi</a>
                @else
                    <form method="POST" action="{{ $playUrl($type) }}">
                        @csrf
                        <button type="submit" class="vh-btn vh-btn-primary" style="padding:10px 20px" @disabled($count === 0)>▶ Chơi</button>
                    </form>
                @endguest
            </div>
        @endforeach
    </div>
</section>

@if ($nextLesson)
    <section class="lib-section">
        <div class="vh-card" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
            <div>
                <div style="color:var(--ink-soft);font-weight:600">Bài tiếp theo trong kỹ năng này:</div>
                <strong style="font-size:1.1rem">{{ $nextLesson->title }}</strong>
            </div>
            <a href="{{ route('library.lesson', $nextLesson->slug) }}" class="vh-btn vh-btn-primary">Học tiếp →</a>
        </div>
    </section>
@endif
@endsection
