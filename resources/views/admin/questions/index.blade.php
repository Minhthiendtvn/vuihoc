@extends('admin.layout')

@section('title', 'Câu hỏi')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">❓ Câu hỏi</h1>
    <a href="{{ route('admin.questions.create') }}" class="vh-btn vh-btn-primary">＋ Thêm câu hỏi</a>
</div>

<div class="vh-card" style="padding:16px">
    <form method="GET" action="{{ route('admin.questions.index') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <label for="lesson_id" style="font-weight:700">Lọc theo bài học:</label>
        <select id="lesson_id" name="lesson_id" style="max-width:320px">
            <option value="">— Tất cả bài học —</option>
            @foreach ($lessons as $lesson)
                <option value="{{ $lesson->id }}" @selected(request('lesson_id') == $lesson->id)>
                    {{ $lesson->title }} ({{ $lesson->skill->topic->name ?? '' }})
                </option>
            @endforeach
        </select>
        <label for="grade" style="font-weight:700">Khối lớp:</label>
        <select id="grade" name="grade" style="max-width:160px">
            <option value="">— Tất cả khối —</option>
            @for ($g = 6; $g <= 12; $g++)
                <option value="{{ $g }}" @selected(request('grade') == $g)>Lớp {{ $g }}</option>
            @endfor
        </select>
        <button type="submit" class="vh-btn vh-btn-ghost vh-btn-small">🔍 Lọc</button>
        @if (request('lesson_id') || request('grade'))
            <a href="{{ route('admin.questions.index') }}" class="vh-btn vh-btn-ghost vh-btn-small">✖ Bỏ lọc</a>
        @endif
    </form>
</div>

<div class="vh-table-wrap">
    <table class="vh-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Đề bài</th>
                <th>Bài học</th>
                <th>Lớp</th>
                <th>Loại game</th>
                <th>Điểm</th>
                <th style="width:150px">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($questions as $question)
                <tr>
                    <td>{{ $question->id }}</td>
                    <td>
                        <div style="max-width:320px;white-space:pre-line">{{ \Illuminate\Support\Str::limit($question->prompt, 120) }}</div>
                        @if ($question->is_demo)
                            <span class="vh-pill vh-pill-amber">Mẫu</span>
                        @endif
                    </td>
                    <td>{{ $question->lesson->title ?? '—' }}</td>
                    <td>{{ $question->grade ? 'Lớp '.$question->grade : '—' }}</td>
                    <td><span class="vh-pill vh-pill-blue">{{ config('vuihoc.game_types')[$question->game_type] ?? $question->game_type }}</span></td>
                    <td>{{ $question->points }}</td>
                    <td>
                        <div class="vh-actions">
                            <a href="{{ route('admin.questions.edit', $question) }}" class="vh-btn vh-btn-ghost vh-btn-small">✏️ Sửa</a>
                            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" class="vh-inline-form"
                                  onsubmit="return confirm('Xóa câu hỏi này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vh-btn vh-btn-danger vh-btn-small">🗑 Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="vh-empty">Chưa có câu hỏi nào. <a href="{{ route('admin.questions.create') }}">Thêm câu hỏi đầu tiên →</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px">{{ $questions->links() }}</div>
@endsection
