{{-- Dùng chung cho create/edit. Biến: $lesson (null khi thêm), $skills, $action, $method --}}
<form method="POST" action="{{ $action }}" class="vh-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="vh-form-grid">
        <div class="vh-field">
            <label for="skill_id">Kỹ năng *</label>
            <select id="skill_id" name="skill_id" required>
                <option value="">— Chọn kỹ năng —</option>
                @foreach ($skills as $skill)
                    <option value="{{ $skill->id }}"
                        @selected(old('skill_id', $lesson->skill_id ?? '') == $skill->id)>
                        {{ $skill->name }} ({{ $skill->topic->name ?? '' }} · {{ $skill->topic->subject->name ?? '' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="vh-field">
            <label for="title">Tiêu đề bài học *</label>
            <input type="text" id="title" name="title" value="{{ old('title', $lesson->title ?? '') }}" required maxlength="255"
                   placeholder="Ví dụ: Ôn tập phép cộng trong phạm vi 100">
        </div>
        <div class="vh-field">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $lesson->slug ?? '') }}" maxlength="255"
                   placeholder="Để trống để tự sinh từ tiêu đề">
        </div>
        <div class="vh-field">
            <label for="difficulty">Độ khó *</label>
            <select id="difficulty" name="difficulty">
                @foreach (config('vuihoc.difficulties') as $key => $label)
                    <option value="{{ $key }}" @selected(old('difficulty', $lesson->difficulty ?? 'trung_binh') == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="vh-field">
            <label for="grade">Khối lớp</label>
            <select id="grade" name="grade">
                <option value="">— Theo khối của chủ đề —</option>
                @for ($g = 6; $g <= 12; $g++)
                    <option value="{{ $g }}" @selected(old('grade', $lesson->grade ?? '') == $g)>Lớp {{ $g }}</option>
                @endfor
            </select>
        </div>
        <div class="vh-field">
            <label for="duration_minutes">Thời lượng (phút)</label>
            <input type="number" id="duration_minutes" name="duration_minutes" min="1" max="180"
                   value="{{ old('duration_minutes', $lesson->duration_minutes ?? 10) }}">
        </div>
        <div class="vh-field">
            <label for="status">Trạng thái *</label>
            <select id="status" name="status">
                <option value="draft" @selected(old('status', $lesson->status ?? 'draft') == 'draft')>Nháp (học viên chưa thấy)</option>
                <option value="published" @selected(old('status', $lesson->status ?? 'draft') == 'published')>Đã xuất bản</option>
            </select>
        </div>
        <div class="vh-field">
            <label for="sort_order">Thứ tự hiển thị</label>
            <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
                   value="{{ old('sort_order', $lesson->sort_order ?? 0) }}">
        </div>
        <div class="vh-field vh-full">
            <label for="objective">Mục tiêu bài học</label>
            <textarea id="objective" name="objective" rows="2"
                      placeholder="Học xong bài này, học viên làm được gì?">{{ old('objective', $lesson->objective ?? '') }}</textarea>
        </div>
        <div class="vh-field vh-full">
            <label for="summary">Tóm tắt bài học</label>
            <textarea id="summary" name="summary" rows="4"
                      placeholder="Mỗi dòng một ý. Dòng bắt đầu bằng '- ' sẽ hiển thị thành gạch đầu dòng ngoài trang bài học.">{{ old('summary', $lesson->summary ?? '') }}</textarea>
            <p class="vh-hint">Mỗi dòng một ý. Dòng bắt đầu bằng "- " sẽ hiển thị thành gạch đầu dòng ngoài trang bài học.</p>
        </div>
        <div class="vh-field vh-full">
            <label for="instructions">Hướng dẫn làm bài</label>
            <textarea id="instructions" name="instructions" rows="3"
                      placeholder="Ví dụ: Đọc kỹ đề bài, chọn đáp án đúng nhất. Mỗi câu có 30 giây suy nghĩ.">{{ old('instructions', $lesson->instructions ?? '') }}</textarea>
        </div>
    </div>

    <div class="vh-actions" style="margin-top:8px">
        <button type="submit" class="vh-btn vh-btn-primary">💾 Lưu</button>
        <a href="{{ route('admin.lessons.index') }}" class="vh-btn vh-btn-ghost">Hủy</a>
    </div>
</form>
