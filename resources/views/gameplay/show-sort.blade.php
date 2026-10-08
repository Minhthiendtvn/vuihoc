@extends('layouts.app')

@section('title', 'Chơi: ' . $lesson->title)

@section('content')
<div class="vh-play">
    <div class="vh-play-head">
        <div>
            <div class="vh-play-kicker">{{ $gameName }} · Kỹ năng: {{ $skillName }}</div>
            <h1 class="vh-play-title">{{ $lesson->title }}</h1>
            <p class="vh-play-sub">Bấm chọn 1 thẻ, rồi bấm vào nhóm đúng để gán. Bấm lại thẻ đã gán để gỡ ra.</p>
        </div>
        @include('gameplay._timer', ['remainingSeconds' => $remainingSeconds])
    </div>

    <form id="vh-play-form" method="POST" action="{{ route('gameplay.submit', $session->token) }}">
        @csrf

        @foreach ($questions as $qi => $q)
            <section class="vh-card vh-question" x-data="sortGame(@json($q['items']))">
                <div class="vh-q-head">
                    <span class="vh-q-num">Câu {{ $qi + 1 }}</span>
                    <span class="vh-q-points">{{ $q['points'] }} điểm</span>
                </div>
                <p class="vh-q-prompt">{{ $q['prompt'] }}</p>

                <div class="vh-sort-items">
                    @foreach ($q['items'] as $it)
                        <button type="button" class="vh-sort-chip"
                            :class="{ 'is-selected': selected === {{ $it['id'] }}, 'is-assigned': assigned[{{ $it['id'] }}] !== undefined }"
                            @click="toggleItem({{ $it['id'] }})">
                            <span>{{ $it['text'] }}</span>
                            <em x-show="assigned[{{ $it['id'] }}] !== undefined" x-text="'→ ' + assigned[{{ $it['id'] }}]"></em>
                        </button>
                    @endforeach
                </div>

                <div class="vh-sort-cats">
                    @foreach ($q['categories'] as $cat)
                        <div class="vh-sort-cat">
                            <button type="button" class="vh-sort-cat-btn" @click="assign(@json($cat))">
                                📁 {{ $cat }}
                            </button>
                            <div class="vh-sort-cat-items">
                                <template x-for="id in itemIdsIn(@json($cat))" :key="id">
                                    <button type="button" class="vh-sort-tag" @click="unassign(id)" title="Bấm để gỡ">
                                        <span x-text="textOf[id]"></span> ✕
                                    </button>
                                </template>
                                <span class="vh-sort-empty" x-show="itemIdsIn(@json($cat)).length === 0">— chưa có thẻ nào —</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <template x-for="(cat, itemId) in assigned" :key="itemId">
                    <input type="hidden" :name="'answers[{{ $q['id'] }}][' + itemId + ']'" :value="cat">
                </template>
            </section>
        @endforeach

        <div class="vh-play-actions">
            <button class="vh-btn vh-btn-primary vh-btn-lg" type="submit">Nộp bài ✅</button>
        </div>
    </form>
</div>

<script>
function sortGame(items) {
    const textOf = {};
    items.forEach(i => { textOf[i.id] = i.text; });
    return {
        selected: null,
        assigned: {},
        textOf,
        toggleItem(id) {
            if (this.assigned[id] !== undefined) {
                delete this.assigned[id]; // bấm lại thẻ đã gán → gỡ ra
            } else {
                this.selected = (this.selected === id) ? null : id;
            }
        },
        assign(cat) {
            if (this.selected === null) return;
            this.assigned[this.selected] = cat;
            this.selected = null;
        },
        unassign(id) {
            delete this.assigned[id];
        },
        itemIdsIn(cat) {
            return Object.entries(this.assigned)
                .filter(([id, c]) => c === cat)
                .map(([id]) => Number(id));
        }
    };
}
</script>
@endsection
