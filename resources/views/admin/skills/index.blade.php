@extends('admin.layout')

@section('title', 'Kỹ năng')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">🎯 Kỹ năng</h1>
    <a href="{{ route('admin.skills.create') }}" class="vh-btn vh-btn-primary">＋ Thêm kỹ năng</a>
</div>

<div class="vh-table-wrap">
    <table class="vh-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Kỹ năng</th>
                <th>Chủ đề</th>
                <th>Môn học</th>
                <th>Bài học</th>
                <th>Thứ tự</th>
                <th style="width:150px">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($skills as $skill)
                <tr>
                    <td>{{ $skill->id }}</td>
                    <td>
                        <strong>{{ $skill->name }}</strong><br>
                        <small style="color:var(--ink-soft)">/{{ $skill->slug }}</small>
                        @if ($skill->is_demo)
                            <span class="vh-pill vh-pill-amber">Mẫu</span>
                        @endif
                    </td>
                    <td>{{ $skill->topic->name }}</td>
                    <td>{{ $skill->topic->subject->name ?? '—' }}</td>
                    <td>{{ $skill->lessons_count }}</td>
                    <td>{{ $skill->sort_order }}</td>
                    <td>
                        <div class="vh-actions">
                            <a href="{{ route('admin.skills.edit', $skill) }}" class="vh-btn vh-btn-ghost vh-btn-small">✏️ Sửa</a>
                            <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" class="vh-inline-form"
                                  onsubmit="return confirm('Xóa kỹ năng “{{ $skill->name }}” cùng toàn bộ bài học và câu hỏi bên trong?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vh-btn vh-btn-danger vh-btn-small">🗑 Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="vh-empty">Chưa có kỹ năng nào. <a href="{{ route('admin.skills.create') }}">Thêm kỹ năng đầu tiên →</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px">{{ $skills->links() }}</div>
@endsection
