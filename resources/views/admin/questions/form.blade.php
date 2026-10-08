{{-- Form câu hỏi dùng chung create/edit.
     Biến: $question (null khi thêm), $lessons, $initial (dữ liệu Alpine),
     $presetLesson, $action, $method --}}
<form method="POST" action="{{ $action }}" class="vh-form-card" x-data="questionForm(@json($initial))">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="vh-form-grid">
        <div class="vh-field">
            <label for="lesson_id">Bài học *</label>
            <select id="lesson_id" name="lesson_id" required>
                <option value="">— Chọn bài học —</option>
                @foreach ($lessons as $lesson)
                    <option value="{{ $lesson->id }}"
                        @selected(old('lesson_id', $question->lesson_id ?? $presetLesson) == $lesson->id)>
                        {{ $lesson->title }} ({{ $lesson->skill->topic->name ?? '' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="vh-field">
            <label for="game_type">Loại game *</label>
            <select id="game_type" name="game_type" x-model="gameType">
                @foreach (config('vuihoc.game_types') as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <p class="vh-hint">Đổi loại game sẽ hiện đúng khung nhập liệu bên dưới. Khi lưu, dữ liệu của loại cũ sẽ được thay bằng dữ liệu của loại mới.</p>
        </div>
        <div class="vh-field vh-full">
            <label for="prompt">Đề bài (prompt) *</label>
            <textarea id="prompt" name="prompt" rows="3" required
                      placeholder="Ví dụ: 7 + 5 bằng bao nhiêu?">{{ old('prompt', $question->prompt ?? '') }}</textarea>
            <p class="vh-hint" x-show="gameType === 'fill'">Với loại Điền từ, mỗi cụm “___” trong đề bài là một chỗ trống (đánh số từ 0).</p>
        </div>
        <div class="vh-field vh-full">
            <label for="explanation">Giải thích đáp án</label>
            <textarea id="explanation" name="explanation" rows="2"
                      placeholder="Giải thích vì sao đáp án đúng, hiển thị cho học viên sau khi nộp bài">{{ old('explanation', $question->explanation ?? '') }}</textarea>
        </div>
        <div class="vh-field">
            <label for="difficulty">Độ khó *</label>
            <select id="difficulty" name="difficulty">
                @foreach (config('vuihoc.difficulties') as $key => $label)
                    <option value="{{ $key }}" @selected(old('difficulty', $question->difficulty ?? 'trung_binh') == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="vh-field">
            <label for="grade">Khối lớp</label>
            <select id="grade" name="grade">
                <option value="">— Theo khối của bài học —</option>
                @for ($g = 6; $g <= 12; $g++)
                    <option value="{{ $g }}" @selected(old('grade', $question->grade ?? '') == $g)>Lớp {{ $g }}</option>
                @endfor
            </select>
        </div>
        <div class="vh-field">
            <label for="points">Điểm *</label>
            <input type="number" id="points" name="points" min="1" max="100" required
                   value="{{ old('points', $question->points ?? 10) }}">
        </div>
        <div class="vh-field">
            <label for="sort_order">Thứ tự hiển thị</label>
            <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
                   value="{{ old('sort_order', $question->sort_order ?? 0) }}">
        </div>
    </div>

    {{-- ============ Sub-form: TRẮC NGHIỆM ============ --}}
    <template x-if="gameType === 'quiz'">
        <div class="vh-subform">
            <h3>📝 Đáp án trắc nghiệm <small style="font-weight:400">(chọn đúng 1 đáp án đúng)</small></h3>
            <template x-for="(opt, i) in options" :key="'o'+i">
                <div class="vh-row">
                    <input type="text" :name="'options['+i+'][text]'" x-model="opt.text"
                           :placeholder="'Đáp án ' + (i+1)">
                    <label style="white-space:nowrap;font-weight:700;color:#166534">
                        <input type="radio" name="correct_index" :value="i" x-model.number="correctIndex"> Đúng
                    </label>
                    <button type="button" class="vh-btn vh-btn-ghost vh-btn-small" @click="removeOption(i)" title="Xóa đáp án">🗑</button>
                </div>
            </template>
            <button type="button" class="vh-btn vh-btn-ghost vh-btn-small" @click="addOption()">＋ Thêm đáp án</button>
        </div>
    </template>

    {{-- ============ Sub-form: GHÉP CẶP ============ --}}
    <template x-if="gameType === 'matching'">
        <div class="vh-subform">
            <h3>🔗 Các cặp ghép <small style="font-weight:400">(cột trái ↔ cột phải)</small></h3>
            <template x-for="(pair, i) in pairs" :key="'m'+i">
                <div class="vh-row">
                    <input type="text" :name="'pairs['+i+'][left_text]'" x-model="pair.left_text"
                           :placeholder="'Cột trái ' + (i+1)">
                    <span style="align-self:center;font-weight:800">↔</span>
                    <input type="text" :name="'pairs['+i+'][right_text]'" x-model="pair.right_text"
                           :placeholder="'Cột phải ' + (i+1)">
                    <button type="button" class="vh-btn vh-btn-ghost vh-btn-small" @click="removePair(i)" title="Xóa cặp">🗑</button>
                </div>
            </template>
            <button type="button" class="vh-btn vh-btn-ghost vh-btn-small" @click="addPair()">＋ Thêm cặp</button>
        </div>
    </template>

    {{-- ============ Sub-form: KÉO-THẢ SẮP XẾP ============ --}}
    <template x-if="gameType === 'sort'">
        <div class="vh-subform">
            <h3>🔀 Các mục cần sắp xếp <small style="font-weight:400">(mỗi mục thuộc một nhóm — cần ít nhất 2 nhóm khác nhau)</small></h3>
            <template x-for="(item, i) in sortItems" :key="'s'+i">
                <div class="vh-row">
                    <input type="text" :name="'sort_items['+i+'][item_text]'" x-model="item.item_text"
                           :placeholder="'Mục ' + (i+1)">
                    <input type="text" :name="'sort_items['+i+'][category]'" x-model="item.category"
                           placeholder="Nhóm" style="max-width:180px">
                    <button type="button" class="vh-btn vh-btn-ghost vh-btn-small" @click="removeSortItem(i)" title="Xóa mục">🗑</button>
                </div>
            </template>
            <button type="button" class="vh-btn vh-btn-ghost vh-btn-small" @click="addSortItem()">＋ Thêm mục</button>
        </div>
    </template>

    {{-- ============ Sub-form: ĐIỀN TỪ ============ --}}
    <template x-if="gameType === 'fill'">
        <div class="vh-subform">
            <h3>✏️ Đáp án các chỗ trống</h3>
            <p class="vh-hint">Mỗi “___” trong đề bài là một chỗ trống, đánh số từ <strong>0</strong>. Mỗi chỗ trống có thể có nhiều đáp án đúng — mỗi đáp án một dòng.</p>
            <template x-for="(row, i) in fillRows" :key="'f'+i">
                <div class="vh-row">
                    <input type="number" min="0" :name="'fill_answers['+i+'][blank_index]'" x-model.number="row.blank_index"
                           title="Số thứ tự chỗ trống" style="max-width:90px">
                    <textarea rows="2" :name="'fill_answers['+i+'][answer_text]'" x-model="row.answer_text"
                              placeholder="Đáp án đúng (mỗi dòng một đáp án)"></textarea>
                    <button type="button" class="vh-btn vh-btn-ghost vh-btn-small" @click="removeFillRow(i)" title="Xóa dòng">🗑</button>
                </div>
            </template>
            <button type="button" class="vh-btn vh-btn-ghost vh-btn-small" @click="addFillRow()">＋ Thêm chỗ trống</button>
        </div>
    </template>

    <div class="vh-actions" style="margin-top:16px">
        <button type="submit" class="vh-btn vh-btn-primary">💾 Lưu câu hỏi</button>
        <a href="{{ route('admin.questions.index') }}" class="vh-btn vh-btn-ghost">Hủy</a>
    </div>
</form>

@push('scripts')
<script>
function questionForm(initial) {
    const list = (key) => (Array.isArray(initial[key]) && initial[key].length ? initial[key] : null);
    return {
        gameType: initial.gameType || 'quiz',
        correctIndex: Number(initial.correctIndex ?? 0),
        options:   list('options')   || [{text:''},{text:''}],
        pairs:     list('pairs')     || [{left_text:'',right_text:''},{left_text:'',right_text:''}],
        sortItems: list('sortItems') || [{item_text:'',category:''},{item_text:'',category:''}],
        fillRows:  list('fillRows')  || [{blank_index:0,answer_text:''}],

        addOption() { this.options.push({text:''}); },
        removeOption(i) {
            if (this.options.length > 2) {
                this.options.splice(i, 1);
                if (this.correctIndex >= this.options.length) this.correctIndex = this.options.length - 1;
            }
        },
        addPair() { this.pairs.push({left_text:'',right_text:''}); },
        removePair(i) { if (this.pairs.length > 2) this.pairs.splice(i, 1); },
        addSortItem() { this.sortItems.push({item_text:'',category:''}); },
        removeSortItem(i) { if (this.sortItems.length > 2) this.sortItems.splice(i, 1); },
        addFillRow() { this.fillRows.push({blank_index: this.fillRows.length, answer_text:''}); },
        removeFillRow(i) { if (this.fillRows.length > 1) this.fillRows.splice(i, 1); },
    };
}
</script>
@endpush
