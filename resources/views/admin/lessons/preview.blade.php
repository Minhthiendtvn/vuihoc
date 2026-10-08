@extends('admin.layout')

@section('title', 'Xem trước bài học')

@section('content')
<div class="vh-page-head">
    <div>
        <h1 class="vh-title" style="margin:0">👁 {{ $lesson->title }}</h1>
        <p class="vh-subtitle" style="margin:6px 0 0">
            {{ $lesson->skill->topic->subject->name ?? '' }} › {{ $lesson->skill->topic->name ?? '' }} › {{ $lesson->skill->name ?? '' }}
            · {{ config('vuihoc.difficulties')[$lesson->difficulty] ?? $lesson->difficulty }}
            · {{ $lesson->duration_minutes }} phút
            @if ($lesson->status === 'published')
                <span class="vh-pill vh-pill-green">Đã xuất bản</span>
            @else
                <span class="vh-pill vh-pill-gray">Nháp</span>
            @endif
        </p>
    </div>
    <div class="vh-actions">
        <a href="{{ route('admin.questions.create', ['lesson_id' => $lesson->id]) }}" class="vh-btn vh-btn-primary">＋ Thêm câu hỏi</a>
        <a href="{{ route('admin.lessons.edit', $lesson) }}" class="vh-btn vh-btn-ghost">✏️ Sửa bài học</a>
    </div>
</div>

@if ($lesson->objective)
    <div class="vh-card">
        <strong>🎯 Mục tiêu:</strong> {{ $lesson->objective }}
        @if ($lesson->instructions)
            <br><strong>📝 Hướng dẫn:</strong> {{ $lesson->instructions }}
        @endif
    </div>
@endif

<p class="vh-hint" style="margin-bottom:12px">🔒 Trang xem trước chỉ dành cho quản trị viên — đáp án đúng được hiển thị đầy đủ để kiểm tra nội dung.</p>

@forelse ($lesson->questions as $index => $question)
    <div class="vh-preview-question">
        <div class="vh-page-head" style="margin-bottom:8px">
            <h3 style="margin:0">Câu {{ $index + 1 }} · {{ config('vuihoc.game_types')[$question->game_type] ?? $question->game_type }}</h3>
            <div class="vh-actions">
                <span class="vh-pill vh-pill-blue">{{ $question->points }} điểm</span>
                <a href="{{ route('admin.questions.edit', $question) }}" class="vh-btn vh-btn-ghost vh-btn-small">✏️ Sửa câu hỏi</a>
            </div>
        </div>

        <p style="white-space:pre-line">{{ $question->prompt }}</p>

        @if ($question->game_type === 'quiz')
            <ul class="vh-answer-list">
                @foreach ($question->options as $option)
                    <li class="{{ $option->is_correct ? 'vh-answer-correct' : '' }}">
                        {{ $option->is_correct ? '✅' : '▫️' }} {{ $option->option_text }}
                    </li>
                @endforeach
            </ul>
        @elseif ($question->game_type === 'matching')
            <div class="vh-table-wrap">
                <table class="vh-table">
                    <thead><tr><th>Cột trái</th><th>→</th><th>Cột phải</th></tr></thead>
                    <tbody>
                        @foreach ($question->pairs as $pair)
                            <tr>
                                <td>{{ $pair->left_text }}</td>
                                <td style="width:40px">→</td>
                                <td class="vh-answer-correct">{{ $pair->right_text }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($question->game_type === 'sort')
            <div class="vh-table-wrap">
                <table class="vh-table">
                    <thead><tr><th>Thứ tự đúng</th><th>Mục</th><th>Nhóm</th></tr></thead>
                    <tbody>
                        @foreach ($question->sortItems as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->item_text }}</td>
                                <td><span class="vh-pill vh-pill-blue">{{ $item->category }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($question->game_type === 'fill')
            @php $grouped = $question->fillAnswers->groupBy('blank_index'); @endphp
            @foreach ($grouped as $blankIndex => $answers)
                <div>
                    <span class="vh-blank-tag">Chỗ trống {{ $blankIndex + 1 }}</span>
                    @foreach ($answers as $answer)
                        <span class="vh-pill vh-pill-green" style="margin-right:6px">{{ $answer->answer_text }}</span>
                    @endforeach
                </div>
            @endforeach
        @endif

        @if ($question->explanation)
            <p style="margin-top:10px"><strong>💡 Giải thích:</strong> {{ $question->explanation }}</p>
        @endif
    </div>
@empty
    <div class="vh-card">
        <p class="vh-empty">Bài học chưa có câu hỏi nào.</p>
        <p style="text-align:center">
            <a href="{{ route('admin.questions.create', ['lesson_id' => $lesson->id]) }}" class="vh-btn vh-btn-primary">＋ Thêm câu hỏi đầu tiên</a>
        </p>
    </div>
@endforelse

<div style="margin-top:16px">
    <a href="{{ route('admin.lessons.index') }}" class="vh-btn vh-btn-ghost">← Về danh sách bài học</a>
</div>
@endsection
