@extends('layouts.app')

@section('title', 'Chơi: ' . $lesson->title)

@section('content')
<div class="vh-play">
    <div class="vh-play-head">
        <div>
            <div class="vh-play-kicker">{{ $gameName }} · Kỹ năng: {{ $skillName }}</div>
            <h1 class="vh-play-title">{{ $lesson->title }}</h1>
            <p class="vh-play-sub">Bấm 1 ô bên trái, rồi bấm 1 ô bên phải để ghép cặp. Bấm lại ô đã ghép để bỏ ghép.</p>
        </div>
        @include('gameplay._timer', ['remainingSeconds' => $remainingSeconds])
    </div>

    <form id="vh-play-form" method="POST" action="{{ route('gameplay.submit', $session->token) }}">
        @csrf

        @foreach ($questions as $qi => $q)
            <section class="vh-card vh-question" x-data="matchGame()">
                <div class="vh-q-head">
                    <span class="vh-q-num">Câu {{ $qi + 1 }}</span>
                    <span class="vh-q-points">{{ $q['points'] }} điểm</span>
                </div>
                <p class="vh-q-prompt">{{ $q['prompt'] }}</p>

                <div class="vh-match-cols">
                    <div class="vh-match-col">
                        <div class="vh-match-col-title">📌 Cột trái</div>
                        @foreach ($q['left'] as $l)
                            <button type="button" class="vh-match-item"
                                :class="{ 'is-selected': selected === {{ $l['id'] }}, 'is-paired': pairs[{{ $l['id'] }}] !== undefined }"
                                @click="pickLeft({{ $l['id'] }})">{{ $l['text'] }}</button>
                        @endforeach
                    </div>
                    <div class="vh-match-col">
                        <div class="vh-match-col-title">🔀 Cột phải (đã xáo trộn)</div>
                        @foreach ($q['right'] as $r)
                            <button type="button" class="vh-match-item"
                                :class="{ 'is-used': used({{ $r['id'] }}) }"
                                @click="pickRight({{ $r['id'] }})">{{ $r['text'] }}</button>
                        @endforeach
                    </div>
                </div>

                <p class="vh-match-hint" x-text="hint"></p>

                <template x-for="(rightId, leftId) in pairs" :key="leftId">
                    <input type="hidden" :name="'answers[{{ $q['id'] }}][' + leftId + ']'" :value="rightId">
                </template>
            </section>
        @endforeach

        <div class="vh-play-actions">
            <button class="vh-btn vh-btn-primary vh-btn-lg" type="submit">Nộp bài ✅</button>
        </div>
    </form>
</div>

<script>
function matchGame() {
    return {
        selected: null,
        pairs: {},
        hint: 'Bấm 1 ô bên trái, rồi bấm 1 ô bên phải để ghép. Bấm lại ô phải đã ghép để bỏ ghép.',
        pickLeft(id) {
            this.selected = (this.selected === id) ? null : id;
        },
        pickRight(id) {
            // Ô phải đã ghép với ô trái nào đó → bỏ ghép cũ, chọn lại ô trái đó.
            for (const [l, r] of Object.entries(this.pairs)) {
                if (Number(r) === id) {
                    delete this.pairs[l];
                    this.selected = Number(l);
                    this.hint = 'Đã bỏ ghép cũ. Chọn ô phải mới cho ô trái đang sáng, hoặc bấm lại ô trái để hủy.';
                    return;
                }
            }
            if (this.selected === null) {
                this.hint = 'Hãy chọn 1 ô bên trái trước nhé!';
                return;
            }
            this.pairs[this.selected] = id;
            this.selected = null;
            this.hint = 'Đã ghép xong 1 cặp! Tiếp tục ghép hoặc bấm "Nộp bài".';
        },
        used(id) {
            return Object.values(this.pairs).some(r => Number(r) === id);
        }
    };
}
</script>
@endsection
