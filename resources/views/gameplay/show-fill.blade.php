@extends('layouts.app')

@section('title', 'Chơi: ' . $lesson->title)

@section('content')
<div class="vh-play">
    <div class="vh-play-head">
        <div>
            <div class="vh-play-kicker">{{ $gameName }} · Kỹ năng: {{ $skillName }}</div>
            <h1 class="vh-play-title">{{ $lesson->title }}</h1>
            <p class="vh-play-sub">Điền từ vào từng chỗ trống. Không phân biệt chữ hoa / chữ thường.</p>
        </div>
        @include('gameplay._timer', ['remainingSeconds' => $remainingSeconds])
    </div>

    <form id="vh-play-form" method="POST" action="{{ route('gameplay.submit', $session->token) }}">
        @csrf

        @foreach ($questions as $qi => $q)
            <section class="vh-card vh-question">
                <div class="vh-q-head">
                    <span class="vh-q-num">Câu {{ $qi + 1 }}</span>
                    <span class="vh-q-points">{{ $q['points'] }} điểm</span>
                </div>
                <p class="vh-q-prompt vh-fill-prompt">@foreach ($q['segments'] as $seg)@if ($seg['t'] === 'text'){{ $seg['v'] }}@else<input type="text" class="vh-blank" name="answers[{{ $q['id'] }}][{{ $seg['i'] }}]" placeholder="…" autocomplete="off">@endif @endforeach</p>
                {{-- LƯU Ý: không viết @endif@endforeach dính nhau — regex \B@ của Blade sẽ bỏ qua
                     directive đứng sau ký tự chữ, khiến compile sai (lỗi ParseError). --}}
            </section>
        @endforeach

        <div class="vh-play-actions">
            <button class="vh-btn vh-btn-primary vh-btn-lg" type="submit">Nộp bài ✅</button>
        </div>
    </form>
</div>
@endsection
