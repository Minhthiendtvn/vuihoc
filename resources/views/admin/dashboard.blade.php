@extends('admin.layout')

@section('title', 'Tổng quan')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">📊 Tổng quan</h1>
</div>

<div class="vh-stat-grid">
    @foreach ($stats as $stat)
        @if ($stat['link'])
            <a href="{{ $stat['link'] }}" class="vh-stat">
        @else
            <div class="vh-stat">
        @endif
                <div class="vh-stat-icon">{{ $stat['icon'] }}</div>
                <div class="vh-stat-value">{{ number_format($stat['value']) }}</div>
                <div class="vh-stat-label">{{ $stat['label'] }}</div>
        @if ($stat['link'])
            </a>
        @else
            </div>
        @endif
    @endforeach
</div>

<div class="vh-card">
    <h2 style="margin-top:0">🎮 10 lượt chơi gần nhất</h2>
    @if ($recentPlays->isEmpty())
        <p class="vh-empty">Chưa có lượt chơi nào.</p>
    @else
        <div class="vh-table-wrap">
            <table class="vh-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Học viên</th>
                        <th>Bài học</th>
                        <th>Loại game</th>
                        <th>Điểm</th>
                        <th>Độ chính xác</th>
                        <th>Trạng thái</th>
                        <th>Bắt đầu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentPlays as $play)
                        <tr>
                            <td>{{ $play->id }}</td>
                            <td>{{ $play->profile->display_name ?? '—' }}</td>
                            <td>{{ $play->lesson->title ?? '—' }}</td>
                            <td>{{ config('vuihoc.game_types')[$play->game_type] ?? $play->game_type }}</td>
                            <td>{{ $play->score ?? '—' }}{{ $play->max_score ? '/'.$play->max_score : '' }}</td>
                            <td>{{ $play->accuracy !== null ? number_format($play->accuracy, 1).'%' : '—' }}</td>
                            <td>
                                @php
                                    $statusLabels = ['started' => 'Đang chơi', 'finished' => 'Hoàn thành', 'expired' => 'Hết hạn'];
                                    $statusClass = ['started' => 'vh-pill-blue', 'finished' => 'vh-pill-green', 'expired' => 'vh-pill-gray'];
                                @endphp
                                <span class="vh-pill {{ $statusClass[$play->status] ?? 'vh-pill-gray' }}">
                                    {{ $statusLabels[$play->status] ?? $play->status }}
                                </span>
                            </td>
                            <td>{{ $play->started_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
