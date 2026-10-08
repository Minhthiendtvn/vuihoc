@extends('layouts.app')

@section('title', 'Giao bài cho ' . $targetName)

@section('content')
<style>
    .asg-form { max-width: 720px; margin: 0 auto; }
    .asg-field { margin-bottom: 18px; }
    .asg-field label { display: block; font-weight: 700; margin-bottom: 6px; }
    .asg-field input[type="text"], .asg-field input[type="datetime-local"], .asg-field textarea {
        width: 100%; padding: 10px 14px; border-radius: 12px; border: 2px solid #e5e7eb; font-size: 1rem; font-family: inherit;
    }
    .asg-field input:focus, .asg-field textarea:focus { outline: none; border-color: var(--brand); }
    .asg-lessons { max-height: 320px; overflow-y: auto; border: 2px solid #e5e7eb; border-radius: 12px; }
    .asg-lesson { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; }
    .asg-lesson:last-child { border-bottom: none; }
    .asg-lesson:hover { background: #f8fafc; }
    .asg-lesson.selected { background: #eef2ff; }
    .asg-lesson .asg-ltitle { font-weight: 700; }
    .asg-lesson .asg-lmeta { font-size: 0.85rem; color: var(--ink-soft); margin-top: 2px; }
    .asg-types { display: flex; gap: 10px; flex-wrap: wrap; }
    .asg-type { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 999px; border: 2px solid #e5e7eb; cursor: pointer; font-weight: 700; }
    .asg-type.selected { border-color: var(--brand); background: #eef2ff; color: var(--brand); }
    .asg-type.disabled { opacity: 0.4; cursor: not-allowed; }
    .asg-hint { font-size: 0.85rem; color: var(--ink-soft); margin-top: 4px; }
    .asg-error { color: var(--danger, #dc2626); font-size: 0.9rem; margin-top: 4px; font-weight: 600; }
</style>

<p><a href="{{ $backUrl }}" style="color: var(--brand); font-weight: 700; text-decoration: none;">← Quay lại</a></p>

<h1 class="vh-title">📝 Giao bài cho {{ $targetName }}</h1>
<p class="vh-subtitle">Chọn bài học, kiểu chơi, ghi chú và hạn hoàn thành.</p>

<div class="vh-card asg-form" x-data="asgForm()">
    <form method="POST" action="{{ $storeRoute }}">
        @csrf

        <div class="asg-field">
            <label for="asg-search">1. Chọn bài học</label>
            <input type="text" id="asg-search" x-model="q" placeholder="Gõ tên bài, môn học hoặc kỹ năng để tìm…" autocomplete="off">
            <div class="asg-hint" x-show="selectedLesson">Đã chọn: <strong x-text="selectedLesson.title"></strong>
                (<span x-text="selectedLesson.subject"></span>) — <a href="#" @click.prevent="clearLesson()" style="color: var(--brand);">chọn lại</a></div>
            <input type="hidden" name="lesson_id" :value="selectedLesson ? selectedLesson.id : ''" required>
            <div class="asg-lessons" style="margin-top: 8px;" x-show="!selectedLesson">
                <template x-for="l in filteredLessons()" :key="l.id">
                    <div class="asg-lesson" :class="{selected: selectedLesson && selectedLesson.id === l.id}" @click="selectLesson(l)">
                        <div>
                            <div class="asg-ltitle" x-text="l.title"></div>
                            <div class="asg-lmeta">
                                📚 <span x-text="l.subject"></span> · 🎯 <span x-text="l.skill"></span>
                                · <span x-text="difficultyLabel(l.difficulty)"></span>
                                · ❓ <span x-text="totalQuestions(l)"></span> câu
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="filteredLessons().length === 0" style="padding: 16px; color: var(--ink-soft); text-align: center;">
                    Không tìm thấy bài học nào. Thử từ khóa khác nhé.
                </div>
            </div>
            @error('lesson_id')<div class="asg-error">{{ $message }}</div>@enderror
        </div>

        <div class="asg-field">
            <label>2. Kiểu chơi</label>
            <div class="asg-types">
                @foreach ($gameTypes as $type => $label)
                    <label class="asg-type"
                           :class="{selected: gameType === '{{ $type }}', disabled: selectedLesson && selectedLesson.counts['{{ $type }}'] === 0}"
                           x-show="!selectedLesson || selectedLesson.counts['{{ $type }}'] > 0">
                        <input type="radio" name="game_type" value="{{ $type }}" x-model="gameType" style="accent-color: var(--brand);">
                        {{ $label }}
                        <span x-show="selectedLesson" style="font-size: 0.8rem; color: var(--ink-soft);"
                              x-text="selectedLesson ? selectedLesson.counts['{{ $type }}'] + ' câu' : ''"></span>
                    </label>
                @endforeach
            </div>
            <div class="asg-hint">Chỉ hiện các kiểu chơi mà bài học có câu hỏi.</div>
            @error('game_type')<div class="asg-error">{{ $message }}</div>@enderror
        </div>

        <div class="asg-field">
            <label for="asg-note">3. Ghi chú cho con (không bắt buộc)</label>
            <textarea id="asg-note" name="note" rows="3" maxlength="1000"
                      placeholder="VD: Cố gắng đạt trên 80% nhé! 💪">{{ old('note') }}</textarea>
            @error('note')<div class="asg-error">{{ $message }}</div>@enderror
        </div>

        <div class="asg-field">
            <label for="asg-deadline">4. Hạn hoàn thành (không bắt buộc)</label>
            <input type="datetime-local" id="asg-deadline" name="deadline" value="{{ old('deadline') }}">
            <div class="asg-hint">Để trống nếu không giới hạn thời gian. Quá hạn bài sẽ được tô đỏ để dễ nhận biết.</div>
            @error('deadline')<div class="asg-error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="vh-btn vh-btn-primary" style="width: 100%;">📝 Giao bài</button>
    </form>
</div>

<script>
function asgForm() {
    const lessons = @json($lessons);
    const difficulties = @json(config('vuihoc.difficulties', []));
    const presetSkill = {{ (int) $presetSkill }};
    return {
        q: '',
        lessons: lessons,
        selectedLesson: null,
        gameType: 'quiz',
        init() {
            if (presetSkill) {
                const first = this.lessons.find(l => l.skill_id === presetSkill);
                if (first) { this.q = first.skill; }
            }
        },
        filteredLessons() {
            const kw = this.q.trim().toLowerCase();
            let list = this.lessons;
            if (presetSkill) {
                list = list.filter(l => l.skill_id === presetSkill);
            }
            if (!kw) return list.slice(0, 50);
            return list.filter(l =>
                l.title.toLowerCase().includes(kw) ||
                l.subject.toLowerCase().includes(kw) ||
                l.skill.toLowerCase().includes(kw)
            ).slice(0, 50);
        },
        selectLesson(l) {
            this.selectedLesson = l;
            if (l.counts[this.gameType] === 0) {
                const firstAvailable = Object.keys(l.counts).find(t => l.counts[t] > 0);
                if (firstAvailable) this.gameType = firstAvailable;
            }
        },
        clearLesson() { this.selectedLesson = null; this.q = ''; },
        totalQuestions(l) { return l.counts.quiz + l.counts.matching + l.counts.sort + l.counts.fill; },
        difficultyLabel(d) { return difficulties[d] || d; },
    };
}
</script>
@endsection
