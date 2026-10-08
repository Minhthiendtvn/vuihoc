@extends('admin.layout')

@section('title', 'Chủ đề')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">🗂️ Chủ đề</h1>
    <a href="{{ route('admin.topics.create') }}" class="vh-btn vh-btn-primary">＋ Thêm chủ đề</a>
</div>

<div class="vh-table-wrap">
    <table class="vh-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Chủ đề</th>
                <th>Môn học</th>
                <th>Khối lớp</th>
                <th>Kỹ năng</th>
                <th>Thứ tự</th>
                <th>Hiển thị</th>
                <th style="width:150px">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($topics as $topic)
                <tr>
                    <td>{{ $topic->id }}</td>
                    <td>
                        <strong>{{ $topic->icon }} {{ $topic->name }}</strong><br>
                        <small style="color:var(--ink-soft)">/{{ $topic->slug }}</small>
                        @if ($topic->is_demo)
                            <span class="vh-pill vh-pill-amber">Mẫu</span>
                        @endif
                    </td>
                    <td>{{ $topic->subject->icon }} {{ $topic->subject->name }}</td>
                    <td>{{ $topic->grade_min }}–{{ $topic->grade_max }}</td>
                    <td>{{ $topic->skills_count }}</td>
                    <td>{{ $topic->sort_order }}</td>
                    <td>
                        @if ($topic->is_published)
                            <span class="vh-pill vh-pill-green">Đang hiện</span>
                        @else
                            <span class="vh-pill vh-pill-gray">Đang ẩn</span>
                        @endif
                    </td>
                    <td>
                        <div class="vh-actions">
                            <a href="{{ route('admin.topics.edit', $topic) }}" class="vh-btn vh-btn-ghost vh-btn-small">✏️ Sửa</a>
                            <form method="POST" action="{{ route('admin.topics.destroy', $topic) }}" class="vh-inline-form"
                                  onsubmit="return confirm('Xóa chủ đề “{{ $topic->name }}” cùng toàn bộ kỹ năng, bài học và câu hỏi bên trong?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vh-btn vh-btn-danger vh-btn-small">🗑 Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="vh-empty">Chưa có chủ đề nào. <a href="{{ route('admin.topics.create') }}">Thêm chủ đề đầu tiên →</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px">{{ $topics->links() }}</div>
@endsection
