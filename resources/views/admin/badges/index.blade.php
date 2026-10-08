@extends('admin.layout')

@section('title', 'Huy hiệu')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">🏅 Huy hiệu</h1>
    <a href="{{ route('admin.badges.create') }}" class="vh-btn vh-btn-primary">＋ Thêm huy hiệu</a>
</div>

<div class="vh-table-wrap">
    <table class="vh-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Huy hiệu</th>
                <th>Tiêu chí</th>
                <th>Ngưỡng</th>
                <th style="width:150px">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($badges as $badge)
                <tr>
                    <td>{{ $badge->id }}</td>
                    <td>
                        <strong style="font-size:1.2rem">{{ $badge->icon }}</strong> <strong>{{ $badge->name }}</strong><br>
                        <small style="color:var(--ink-soft)">/{{ $badge->slug }}</small>
                        @if ($badge->is_demo)
                            <span class="vh-pill vh-pill-amber">Mẫu</span>
                        @endif
                        @if ($badge->description)
                            <br><small style="color:var(--ink-soft)">{{ $badge->description }}</small>
                        @endif
                    </td>
                    <td>{{ $criteriaLabels[$badge->criteria] ?? $badge->criteria }}<br><small style="color:var(--ink-soft)">{{ $badge->criteria }}</small></td>
                    <td>{{ number_format($badge->threshold) }}</td>
                    <td>
                        <div class="vh-actions">
                            <a href="{{ route('admin.badges.edit', $badge) }}" class="vh-btn vh-btn-ghost vh-btn-small">✏️ Sửa</a>
                            <form method="POST" action="{{ route('admin.badges.destroy', $badge) }}" class="vh-inline-form"
                                  onsubmit="return confirm('Xóa huy hiệu “{{ $badge->name }}”?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vh-btn vh-btn-danger vh-btn-small">🗑 Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="vh-empty">Chưa có huy hiệu nào. <a href="{{ route('admin.badges.create') }}">Thêm huy hiệu đầu tiên →</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px">{{ $badges->links() }}</div>
@endsection
