@extends('layouts.app')

@section('title', 'Chơi: ' . $lesson->title)

@section('content')
<div class="vh-play">
    <div class="vh-play-head">
        <div>
            <div class="vh-play-kicker">{{ $gameName }} · Kỹ năng: {{ $skillName }}</div>
            <h1 class="vh-play-title">{{ $lesson->title }}</h1>
            <p class="vh-play-sub">Chọn 1 đáp án đúng cho mỗi câu. Bài sẽ tự nộp khi hết 10 phút.</p>
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
                <p class="vh-q-prompt">{{ $q['prompt'] }}</p>
                <div class="vh-options">
                    @foreach ($q['options'] as $opt)
                        <label class="vh-option">
                            <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt['id'] }}">
                            <span>{{ $opt['text'] }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="vh-play-actions">
            <button class="vh-btn vh-btn-primary vh-btn-lg" type="submit">Nộp bài ✅</button>
        </div>
    </form>
</div>
@endsection
