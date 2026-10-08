@extends('admin.layout')

@section('title', 'Bài học')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">📖 Bài học</h1>
    <a href="{{ route('admin.lessons.create') }}" class="vh-btn vh-btn-primary">＋ Thêm bài học</a>
</div>

<div class="vh-card" style="padding:16px">
    <form method="GET" action="{{ route('admin.lessons.index') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <label for="grade" style="font-weight:700">Lọc theo khối lớp:</label>
        <select id="grade" name="grade" style="max-width:160px">
            <option value="">— Tất cả khối —</option>
            @for ($g = 6; $g <= 12; $g++)
                <option value="{{ $g }}" @selected(request('grade') == $g)>Lớp {{ $g }}</option>
            @endfor
        </select>
        <button type="submit" class="vh-btn vh-btn-ghost vh-btn-small">🔍 Lọc</button>
        @if (request('grade'))
            <a href="{{ route('admin.lessons.index') }}" class="vh-btn vh-btn-ghost vh-btn-small">✖ Bỏ lọc</a>
        @endif
    </form>
</div>

<div class="vh-table-wrap">
    <table class="vh-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Bài học</th>
                <th>Kỹ năng</th>
                <th>Lớp</th>
                <th>Độ khó</th>
                <th>Câu hỏi</th>
                <th>Trạng thái</th>
                <th style="width:210px">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lessons as $lesson)
                <tr>
                    <td>{{ $lesson->id }}</td>
                    <td>
                        <strong>{{ $lesson->title }}</strong><br>
                        <small style="color:var(--ink-soft)">/{{ $lesson->slug }} · {{ $lesson->duration_minutes }} phút</small>
                        @if ($lesson->is_demo)
                            <span class="vh-pill vh-pill-amber">Mẫu</span>
                        @endif
                    </td>
                    <td>{{ $lesson->skill->name }}<br><small style="color:var(--ink-soft)">{{ $lesson->skill->topic->name ?? '' }}</small></td>
                    <td>{{ $lesson->grade ? 'Lớp '.$lesson->grade : '—' }}</td>
                    <td>{{ config('vuihoc.difficulties')[$lesson->difficulty] ?? $lesson->difficulty }}</td>
                    <td>{{ $lesson->questions_count }}</td>
                    <td>
                        @if ($lesson->status === 'published')
                            <span class="vh-pill vh-pill-green">Đã xuất bản</span>
                        @else
                            <span class="vh-pill vh-pill-gray">Nháp</span>
                        @endif
                    </td>
                    <td>
                        <div class="vh-actions">
                            <a href="{{ route('admin.lessons.preview', $lesson) }}" class="vh-btn vh-btn-ghost vh-btn-small">👁 Xem trước</a>
                            <a href="{{ route('admin.lessons.edit', $lesson) }}" class="vh-btn vh-btn-ghost vh-btn-small">✏️ Sửa</a>
                            <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" class="vh-inline-form"
                                  onsubmit="return confirm('Xóa bài học “{{ $lesson->title }}” cùng toàn bộ câu hỏi bên trong?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vh-btn vh-btn-danger vh-btn-small">🗑 Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="vh-empty">Chưa có bài học nào. <a href="{{ route('admin.lessons.create') }}">Thêm bài học đầu tiên →</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px">{{ $lessons->links() }}</div>
@endsection
