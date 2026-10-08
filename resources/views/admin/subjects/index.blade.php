@extends('admin.layout')

@section('title', 'Môn học')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">📚 Môn học</h1>
    <a href="{{ route('admin.subjects.create') }}" class="vh-btn vh-btn-primary">＋ Thêm môn học</a>
</div>

<div class="vh-table-wrap">
    <table class="vh-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Môn học</th>
                <th>Màu</th>
                <th>Chủ đề</th>
                <th>Thứ tự</th>
                <th>Hiển thị</th>
                <th style="width:150px">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subjects as $subject)
                <tr>
                    <td>{{ $subject->id }}</td>
                    <td>
                        <strong>{{ $subject->icon }} {{ $subject->name }}</strong><br>
                        <small style="color:var(--ink-soft)">/{{ $subject->slug }}</small>
                        @if ($subject->is_demo)
                            <span class="vh-pill vh-pill-amber">Mẫu</span>
                        @endif
                    </td>
                    <td>
                        <span style="display:inline-block;width:22px;height:22px;border-radius:6px;background:{{ $subject->color }};border:1px solid #cbd5e1"></span>
                        <small>{{ $subject->color }}</small>
                    </td>
                    <td>{{ $subject->topics_count }}</td>
                    <td>{{ $subject->sort_order }}</td>
                    <td>
                        @if ($subject->is_published)
                            <span class="vh-pill vh-pill-green">Đang hiện</span>
                        @else
                            <span class="vh-pill vh-pill-gray">Đang ẩn</span>
                        @endif
                    </td>
                    <td>
                        <div class="vh-actions">
                            <a href="{{ route('admin.subjects.edit', $subject) }}" class="vh-btn vh-btn-ghost vh-btn-small">✏️ Sửa</a>
                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="vh-inline-form"
                                  onsubmit="return confirm('Xóa môn học “{{ $subject->name }}” cùng toàn bộ chủ đề, kỹ năng, bài học và câu hỏi bên trong?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vh-btn vh-btn-danger vh-btn-small">🗑 Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="vh-empty">Chưa có môn học nào. <a href="{{ route('admin.subjects.create') }}">Thêm môn học đầu tiên →</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px">{{ $subjects->links() }}</div>
@endsection
