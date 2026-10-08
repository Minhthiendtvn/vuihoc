@extends('layouts.app')

@section('title', 'Khu vực phụ huynh')

@section('content')
<style>
    .pa-children { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
    .pa-child { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 999px; background: var(--card); box-shadow: var(--shadow); text-decoration: none; color: var(--ink); font-weight: 700; }
    .pa-child.active { background: linear-gradient(135deg, var(--brand), #7c3aed); color: #fff; }
    .pa-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; margin-bottom: 20px; }
    .pa-stat { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); padding: 18px; text-align: center; }
    .pa-stat .pa-num { font-size: 1.6rem; font-weight: 800; color: var(--brand); }
    .pa-stat .pa-lbl { color: var(--ink-soft); font-size: 0.9rem; margin-top: 4px; }
    .pa-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
    .pa-table th, .pa-table td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    .pa-table th { color: var(--ink-soft); font-size: 0.85rem; text-transform: uppercase; }
    .pa-strength { display: flex; align-items: center; gap: 12px; background: #fef9c3; border-radius: var(--radius); padding: 14px 18px; font-weight: 700; }
    @media (max-width: 640px) { .pa-table { font-size: 0.85rem; } }
</style>

<h1 class="vh-title">👨‍👩‍👧 Khu vực phụ huynh</h1>
<p class="vh-subtitle">Theo dõi tiến độ học tập của các con.</p>

@if ($children->isEmpty())
    <div class="vh-card">
        <p class="vh-subtitle">Chưa có hồ sơ học viên nào. Hãy tạo hồ sơ cho con trong mục quản lý hồ sơ để bắt đầu theo dõi nhé.</p>
    </div>
@else
    <div class="pa-children">
        @foreach ($children as $child)
            <a href="{{ route('parent.index', ['profile' => $child->id]) }}"
               class="pa-child {{ $selected && $selected->id === $child->id ? 'active' : '' }}">
                {{ $child->avatar_emoji }} {{ $child->display_name }}
            </a>
        @endforeach
    </div>

    @if ($summary)
        <h2 class="vh-title" style="font-size: 1.4rem;">{{ $summary['profile']->avatar_emoji }} {{ $summary['profile']->display_name }}</h2>
        <div style="margin-bottom: 20px;">
            <a href="{{ route('assignments.create.profile', $summary['profile']) }}" class="vh-btn vh-btn-primary">📝 Giao bài cho con</a>
        </div>

        <div class="pa-stats">
            <div class="pa-stat">
                <div class="pa-num">{{ number_format($summary['profile']->total_xp) }}</div>
                <div class="pa-lbl">Tổng XP</div>
            </div>
            <div class="pa-stat">
                <div class="pa-num">Cấp {{ $summary['profile']->level }}</div>
                <div class="pa-lbl">Cấp độ</div>
            </div>
            <div class="pa-stat">
                <div class="pa-num">🔥 {{ $summary['profile']->current_streak }}</div>
                <div class="pa-lbl">Chuỗi ngày học</div>
            </div>
            <div class="pa-stat">
                <div class="pa-num">{{ $summary['avgAccuracy'] }}%</div>
                <div class="pa-lbl">Độ chính xác TB</div>
            </div>
            <div class="pa-stat">
                <div class="pa-num">{{ $summary['plays'] }}</div>
                <div class="pa-lbl">Lượt chơi</div>
            </div>
            <div class="pa-stat">
                <div class="pa-num">{{ $summary['profile']->daily_goal }}</div>
                <div class="pa-lbl">Mục tiêu/ngày</div>
            </div>
        </div>

        @if ($summary['strongest'])
            <div class="pa-strength" style="margin-bottom: 20px;">
                <span style="font-size: 1.8rem;">💪</span>
                <span>Điểm mạnh: <strong>{{ $summary['strongest']['name'] }}</strong>
                    — chính xác {{ $summary['strongest']['avgAccuracy'] }}% sau {{ $summary['strongest']['plays'] }} lượt chơi.</span>
            </div>
        @endif

        @if ($summary['weakest']->isNotEmpty())
            <div class="vh-card" style="margin-bottom: 20px;">
                <h2 class="vh-title" style="font-size: 1.25rem;">📉 Cần luyện thêm</h2>
                <p class="vh-subtitle" style="margin-top: 0;">Những kỹ năng con đang làm chưa tốt — giao bài luyện ngay để cải thiện nhé.</p>
                @foreach ($summary['weakest'] as $w)
                    <div class="pa-weak">
                        <div>
                            <div style="font-weight: 700;">🎯 {{ $w['name'] }}</div>
                            <div class="vh-subtitle" style="margin: 2px 0 0;">Chính xác {{ $w['avgAccuracy'] }}% sau {{ $w['plays'] }} lượt chơi</div>
                        </div>
                        <a href="{{ route('assignments.create.profile', ['profile' => $summary['profile']->id, 'skill' => $w['skill_id']]) }}"
                           class="vh-btn vh-btn-ghost">📝 Giao bài luyện</a>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="vh-card" style="margin-bottom: 20px;">
            <h2 class="vh-title" style="font-size: 1.25rem;">📝 Bài đã giao</h2>
            @include('assignments._list', [
                'assignments' => $summary['assignments'],
                'gameTypes'   => $summary['gameTypes'],
                'canDelete'   => true,
            ])
        </div>

        <div class="vh-card">
            <h2 class="vh-title" style="font-size: 1.25rem;">🕘 10 lượt chơi gần đây</h2>
            @if ($summary['history']->isEmpty())
                <p class="vh-subtitle">Con chưa chơi lượt nào.</p>
            @else
                <div style="overflow-x: auto;">
                    <table class="pa-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Bài học</th>
                                <th>Kiểu chơi</th>
                                <th>Điểm</th>
                                <th>Chính xác</th>
                                <th>XP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summary['history'] as $s)
                                <tr>
                                    <td>{{ $s->finished_at?->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                                    <td>{{ $s->lesson?->title ?? '—' }}</td>
                                    <td>{{ $summary['gameTypes'][$s->game_type] ?? $s->game_type }}</td>
                                    <td>{{ $s->score }}/{{ $s->max_score }}</td>
                                    <td>{{ (float) $s->accuracy }}%</td>
                                    <td style="font-weight: 700; color: var(--brand);">+{{ $s->xp_earned }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
@endif
@endsection
