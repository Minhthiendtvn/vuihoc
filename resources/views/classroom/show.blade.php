@extends('layouts.app')

@section('title', 'Lớp ' . $classroom->name)

@section('content')
<style>
    .cl-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
    .cl-table th, .cl-table td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    .cl-table th { color: var(--ink-soft); font-size: 0.85rem; text-transform: uppercase; }
    @media (max-width: 640px) { .cl-table { font-size: 0.85rem; } }
</style>

<p><a href="{{ route('classroom.index') }}" style="color: var(--brand); font-weight: 700; text-decoration: none;">← Về danh sách lớp</a></p>

<h1 class="vh-title">🏫 {{ $classroom->name }}</h1>
<p class="vh-subtitle">
    Mã tham gia: <strong style="font-size: 1.1rem; letter-spacing: 2px; color: var(--brand);">{{ $classroom->code }}</strong>
    · {{ $members->count() }} thành viên
</p>
@if ($classroom->description)
    <p>{{ $classroom->description }}</p>
@endif

<div style="margin-bottom: 20px;">
    <a href="{{ route('assignments.create.classroom', $classroom) }}" class="vh-btn vh-btn-primary">📝 Giao bài cho cả lớp</a>
</div>

<div class="vh-card" style="margin-bottom: 20px;">
    <h2 class="vh-title" style="font-size: 1.25rem;">📝 Bài đã giao cho lớp</h2>
    @include('assignments._list', [
        'assignments' => $assignments,
        'gameTypes'   => $gameTypes,
        'canDelete'   => true,
    ])
</div>

<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">👥 Thành viên & tiến độ</h2>
    @if ($members->isEmpty())
        <p class="vh-subtitle">Chưa có thành viên nào. Chia sẻ mã <strong>{{ $classroom->code }}</strong> cho học viên tham gia.</p>
    @else
        <div style="overflow-x: auto;">
            <table class="cl-table">
                <thead>
                    <tr>
                        <th>Học viên</th>
                        <th>Tổng XP</th>
                        <th>Cấp độ</th>
                        <th>Chuỗi ngày</th>
                        <th>Lượt chơi 7 ngày</th>
                        <th>Ngày tham gia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $row)
                        @php $p = $row['profile']; @endphp
                        <tr>
                            <td style="font-weight: 700;">{{ $p?->avatar_emoji }} {{ $p?->display_name ?? '(hồ sơ đã xóa)' }}</td>
                            <td style="font-weight: 700; color: var(--brand);">{{ number_format($p?->total_xp ?? 0) }}</td>
                            <td>Cấp {{ $p?->level ?? 1 }}</td>
                            <td>🔥 {{ $p?->current_streak ?? 0 }}</td>
                            <td>{{ $row['weekPlays'] }}</td>
                            <td>{{ $row['member']->joined_at?->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
