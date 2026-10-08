@extends('layouts.app')

@section('title', 'Tiến độ của ' . $profile->display_name)

@section('content')
<style>
    .pg-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; margin-bottom: 20px; }
    .pg-stat { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); padding: 18px; text-align: center; }
    .pg-stat .pg-ico { font-size: 1.8rem; }
    .pg-stat .pg-num { font-size: 1.7rem; font-weight: 800; color: var(--brand); margin: 6px 0 2px; }
    .pg-stat .pg-lbl { color: var(--ink-soft); font-size: 0.9rem; }
    .pg-bar { height: 12px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-top: 10px; }
    .pg-bar > div { height: 100%; background: linear-gradient(90deg, var(--brand), #7c3aed); border-radius: 999px; }
    .pg-chart { display: flex; align-items: flex-end; gap: 10px; height: 170px; padding: 12px 4px 0; }
    .pg-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 6px; height: 100%; }
    .pg-barcol { width: 100%; max-width: 52px; background: linear-gradient(180deg, var(--brand-2), #16a34a); border-radius: 8px 8px 4px 4px; min-height: 4px; }
    .pg-day { font-size: 0.75rem; color: var(--ink-soft); text-align: center; line-height: 1.3; }
    .pg-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
    .pg-table th, .pg-table td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    .pg-table th { color: var(--ink-soft); font-size: 0.85rem; text-transform: uppercase; }
    .pg-table tr:last-child td { border-bottom: none; }
    .pg-badges { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .pg-badge { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); padding: 16px 12px; text-align: center; }
    .pg-badge .pg-bico { font-size: 2.2rem; }
    .pg-badge .pg-bname { font-weight: 700; margin: 6px 0 2px; }
    .pg-badge .pg-bdesc { font-size: 0.8rem; color: var(--ink-soft); }
    .pg-badge.locked { opacity: 0.38; filter: grayscale(1); }
    .pg-suggest { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .pg-suggest .pg-arrow { font-size: 1.6rem; }
    .pg-pill { display: inline-block; padding: 6px 16px; border-radius: 999px; font-weight: 700; background: #eef2ff; color: var(--brand); }
    @media (max-width: 640px) { .pg-table { font-size: 0.85rem; } }
</style>

<h1 class="vh-title">{{ $profile->avatar_emoji }} Tiến độ của {{ $profile->display_name }}</h1>
<p class="vh-subtitle">Cấp độ {{ $level }} · {{ number_format($totalXp) }} XP · Mục tiêu mỗi ngày: {{ $profile->daily_goal }} lượt chơi</p>

<div class="pg-stats">
    <div class="pg-stat">
        <div class="pg-ico">⭐</div>
        <div class="pg-num">{{ number_format($totalXp) }}</div>
        <div class="pg-lbl">Tổng XP</div>
    </div>
    <div class="pg-stat">
        <div class="pg-ico">🏅</div>
        <div class="pg-num">Cấp {{ $level }}</div>
        <div class="pg-lbl">
            @if ($nextThreshold !== null)
                Còn {{ number_format($nextThreshold - $totalXp) }} XP lên cấp {{ $nextLevel }}
            @else
                Đã đạt cấp cao nhất 🎉
            @endif
        </div>
        <div class="pg-bar"><div style="width: {{ $levelProgress }}%"></div></div>
    </div>
    <div class="pg-stat">
        <div class="pg-ico">🔥</div>
        <div class="pg-num">{{ $profile->current_streak }} ngày</div>
        <div class="pg-lbl">Chuỗi hiện tại · dài nhất: {{ $profile->longest_streak }} ngày</div>
    </div>
    <div class="pg-stat">
        <div class="pg-ico">🎯</div>
        <div class="pg-num">{{ $avgAccuracy }}%</div>
        <div class="pg-lbl">Độ chính xác trung bình</div>
    </div>
    <div class="pg-stat">
        <div class="pg-ico">🎮</div>
        <div class="pg-num">{{ $plays }}</div>
        <div class="pg-lbl">Lượt chơi đã hoàn thành</div>
    </div>
</div>

<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">📝 Bài được giao</h2>
    @php $pendingAsg = $assignments->where('status', 'pending'); @endphp
    @if ($pendingAsg->isEmpty())
        <p class="vh-subtitle">Hiện không có bài nào cần làm. Tuyệt vời! 🎉</p>
    @else
        <div class="asg-list">
            @foreach ($pendingAsg as $a)
                <div class="asg-item {{ $a->isOverdue() ? 'asg-overdue' : '' }}">
                    <div class="asg-item-main">
                        <div class="asg-item-title">
                            📖 {{ $a->lesson?->title ?? 'Bài học đã bị xoá' }}
                            @if ($a->isOverdue())
                                <span class="asg-pill asg-pill-overdue">⏰ Quá hạn</span>
                            @else
                                <span class="asg-pill asg-pill-pending">📌 Chưa làm</span>
                            @endif
                        </div>
                        <div class="asg-item-meta">
                            🎮 {{ $gameTypes[$a->game_type] ?? $a->game_type }}
                            @if ($a->isForClass()) · 🏫 {{ $a->classroom?->name }} @endif
                            @if ($a->deadline)
                                · ⏰ Hạn: {{ $a->deadline->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                            @endif
                            · 👤 {{ $a->creator?->name ?? '—' }}
                        </div>
                        @if ($a->note)
                            <div class="asg-item-note">💬 {{ $a->note }}</div>
                        @endif
                    </div>
                    <div class="asg-item-actions">
                        @if ($a->lesson)
                            <form method="POST" action="{{ route('gameplay.start', ['lesson' => $a->lesson->slug, 'game_type' => $a->game_type]) }}">
                                @csrf
                                <button type="submit" class="vh-btn vh-btn-primary">▶ Chơi ngay</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">📊 Hoạt động 7 ngày gần nhất</h2>
    <div class="pg-chart">
        @foreach ($days as $day)
            <div class="pg-col">
                <div style="font-weight: 700; color: var(--brand);">{{ $day['count'] }}</div>
                <div class="pg-barcol" style="height: {{ round($day['count'] / $maxCount * 100) }}%"></div>
                <div class="pg-day">{{ $day['label'] }}<br>{{ $day['dow'] }}</div>
            </div>
        @endforeach
    </div>
    <p class="vh-subtitle" style="margin: 12px 0 0;">Số lượt chơi hoàn thành mỗi ngày.</p>
</div>

@if ($suggestion)
<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">🧭 Gợi ý độ khó tiếp theo</h2>
    <div class="pg-suggest">
        <div>
            <div style="font-weight: 700;">{{ $suggestion['skill']->name }}</div>
            <div class="vh-subtitle" style="margin: 2px 0 0;">Bài vừa chơi: {{ $suggestion['lesson']->title }}</div>
        </div>
        <span class="pg-pill">{{ $suggestion['difficulties'][$suggestion['current']] ?? $suggestion['current'] }}</span>
        <span class="pg-arrow">➡️</span>
        <span class="pg-pill" style="background: #dcfce7; color: #166534;">{{ $suggestion['difficulties'][$suggestion['next']] ?? $suggestion['next'] }}</span>
    </div>
    <p class="vh-subtitle" style="margin: 10px 0 0;">Dựa vào độ chính xác trung bình 3 lượt chơi gần nhất của con ở kỹ năng này.</p>
</div>
@endif

<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">🏫 Tham gia lớp học</h2>
    <p class="vh-subtitle" style="margin: 0 0 10px;">Nhập mã lớp do thầy cô hoặc phụ huynh cung cấp để tham gia.</p>
    <form method="POST" action="{{ route('classroom.join') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
        @csrf
        <input type="text" name="code" placeholder="Mã lớp (vd: RFGZAH)" maxlength="16"
               style="text-transform: uppercase; padding: 10px 14px; border-radius: 12px; border: 2px solid #e5e7eb; font-size: 1rem;" required>
        <button type="submit" class="vh-btn">Tham gia</button>
    </form>
</div>

<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">🏆 Huy hiệu</h2>
    @if ($allBadges->isEmpty())
        <p class="vh-subtitle">Chưa có huy hiệu nào được tạo. Chơi game để mở khóa khi huy hiệu có mặt nhé!</p>
    @else
        <div class="pg-badges">
            @foreach ($allBadges as $badge)
                @php $earned = in_array($badge->id, $earnedIds, true); @endphp
                <div class="pg-badge {{ $earned ? '' : 'locked' }}">
                    <div class="pg-bico">{{ $badge->icon }}</div>
                    <div class="pg-bname">{{ $badge->name }}</div>
                    <div class="pg-bdesc">{{ $badge->description }}</div>
                    @if ($earned)
                        <div class="pg-bdesc" style="color: var(--brand-2); font-weight: 700;">✅ Đã đạt</div>
                    @else
                        <div class="pg-bdesc">🔒 Chưa đạt</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">🕘 Lịch sử chơi</h2>
    @if ($history->isEmpty())
        <p class="vh-subtitle">Chưa có lượt chơi nào. Hãy vào thư viện và bắt đầu chơi nhé! 🎮</p>
    @else
        <div style="overflow-x: auto;">
            <table class="pg-table">
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
                    @foreach ($history as $s)
                        <tr>
                            <td>{{ $s->finished_at?->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                            <td>{{ $s->lesson?->title ?? '—' }}</td>
                            <td>{{ $gameTypes[$s->game_type] ?? $s->game_type }}</td>
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
@endsection
