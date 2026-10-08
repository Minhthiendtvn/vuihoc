@extends('layouts.app')

@section('title', 'Kết quả: ' . ($lesson?->title ?? 'Bài chơi'))

@section('content')
<div class="vh-play">
    <div class="vh-play-kicker">{{ $gameName }} · Kỹ năng đã luyện: <strong>{{ $skillName }}</strong></div>
    <h1 class="vh-play-title">🎯 Kết quả bài chơi</h1>

    @if ($cheated)
        <div class="vh-alert vh-alert-error">
            ⚠️ Bài làm hoàn thành dưới 5 giây nên được tính là <strong>gian lận</strong> và chấm 0 điểm.
        </div>
    @endif

    {{-- Tổng quan điểm số --}}
    <section class="vh-card vh-result-summary">
        <div class="vh-result-score">
            <div class="vh-result-big">{{ $session->score }}<span>/{{ $session->max_score }}</span></div>
            <div class="vh-result-label">điểm</div>
        </div>
        <div class="vh-result-stats">
            <div class="vh-stat"><span class="vh-stat-v">{{ number_format((float) $session->accuracy, 1) }}%</span><span class="vh-stat-k">độ chính xác</span></div>
            <div class="vh-stat"><span class="vh-stat-v">{{ intdiv((int) $session->duration_seconds, 60) }}:{{ str_pad((int) $session->duration_seconds % 60, 2, '0', STR_PAD_LEFT) }}</span><span class="vh-stat-k">thời gian làm</span></div>
            <div class="vh-stat"><span class="vh-stat-v">+{{ $session->xp_earned }} XP</span><span class="vh-stat-k">kinh nghiệm</span></div>
        </div>
    </section>

    {{-- Gamification: cấp độ / huy hiệu / chuỗi ngày --}}
    @if ($gamification)
        <section class="vh-card vh-gami">
            <h2 class="vh-section-title">🏆 Thành tích</h2>
            <div class="vh-gami-row">
                @if ($gamification['level_up'])
                    <div class="vh-gami-item vh-gami-levelup">🎉 Lên cấp {{ $gamification['new_level'] }}!</div>
                @endif
                @if (! empty($gamification['streak']))
                    <div class="vh-gami-item">🔥 Chuỗi {{ $gamification['streak'] }} ngày học</div>
                @endif
                <div class="vh-gami-item">⭐ Nhận {{ $gamification['xp'] }} XP</div>
            </div>
            @if (! empty($gamification['new_badges']))
                <div class="vh-badges">
                    @foreach ($gamification['new_badges'] as $badge)
                        <div class="vh-badge">
                            <span class="vh-badge-icon">{{ $badge['icon'] }}</span>
                            <div>
                                <div class="vh-badge-name">{{ $badge['name'] }}</div>
                                <div class="vh-badge-desc">{{ $badge['description'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <div class="vh-alert vh-alert-info">ℹ️ XP đang được hệ thống tính toán, quay lại sau nhé!</div>
    @endif

    {{-- Xem lại từng câu --}}
    <h2 class="vh-section-title">📝 Xem lại từng câu</h2>
    @foreach ($review as $qi => $r)
        <section class="vh-card vh-question {{ ($r['correct'] ?? false) ? 'is-correct' : 'is-wrong' }}">
            <div class="vh-q-head">
                <span class="vh-q-num">Câu {{ $qi + 1 }}</span>
                <span class="vh-q-result">{{ ($r['correct'] ?? false) ? '✅ Đúng' : '❌ Sai' }}</span>
                <span class="vh-q-points">{{ $r['score'] ?? 0 }}/{{ $r['max_score'] ?? $r['points'] ?? 0 }} điểm</span>
            </div>
            <p class="vh-q-prompt">{{ $r['prompt'] ?? '' }}</p>

            @if (! empty($r['items']))
                <ul class="vh-review-items">
                    @foreach ($r['items'] as $item)
                        <li class="{{ ($item['is_correct'] ?? false) ? 'is-correct' : 'is-wrong' }}">
                            <div class="vh-review-label">{{ ($item['is_correct'] ?? false) ? '✅' : '❌' }} {{ $item['label'] ?? '' }}</div>
                            <div class="vh-review-answers">
                                <span>Bạn: <strong>{{ $item['user'] ?? '—' }}</strong></span>
                                @if (! ($item['is_correct'] ?? false))
                                    <span>Đáp án đúng: <strong class="vh-correct-text">{{ $item['correct'] ?? '—' }}</strong></span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="vh-review-answers">
                    <div>Bạn chọn: <strong>{{ $r['user_answer'] ?? '—' }}</strong></div>
                    @if (! ($r['correct'] ?? false))
                        <div>Đáp án đúng: <strong class="vh-correct-text">{{ $r['correct_answer'] ?? '—' }}</strong></div>
                    @endif
                </div>
            @endif

            @if (! empty($r['explanation']))
                <div class="vh-explanation">💡 {{ $r['explanation'] }}</div>
            @endif
        </section>
    @endforeach

    {{-- Bài tiếp theo + chơi lại --}}
    <section class="vh-card vh-next">
        <h2 class="vh-section-title">🚀 Chơi tiếp</h2>
        <div class="vh-next-row">
            @if ($nextLesson)
                <div>
                    <div class="vh-next-label">Bài tiếp theo cùng kỹ năng:</div>
                    <div class="vh-next-title">{{ $nextLesson->title }}</div>
                </div>
                <form method="POST" action="{{ route('gameplay.start', ['lesson' => $nextLesson->slug ?? $nextLesson->id, 'game_type' => $session->game_type]) }}">
                    @csrf
                    <button class="vh-btn vh-btn-primary" type="submit">Chơi bài tiếp ▶</button>
                </form>
            @else
                <div class="vh-next-label">Bạn đã chơi hết các bài của kỹ năng này. Tuyệt vời! 🎉</div>
            @endif
            <form method="POST" action="{{ route('gameplay.start', ['lesson' => $lesson->slug ?? $lesson->id, 'game_type' => $session->game_type]) }}">
                @csrf
                <button class="vh-btn vh-btn-ghost" type="submit">🔁 Chơi lại bài này</button>
            </form>
        </div>
        <div class="vh-next-links">
            <a href="/thu-vien">📚 Về thư viện</a>
            <a href="/">🏠 Trang chủ</a>
        </div>
    </section>
</div>
@endsection
