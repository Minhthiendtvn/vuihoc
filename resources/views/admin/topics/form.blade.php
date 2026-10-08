{{-- Dùng chung cho create/edit. Biến: $topic (null khi thêm), $subjects, $action, $method --}}
<form method="POST" action="{{ $action }}" class="vh-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="vh-form-grid">
        <div class="vh-field">
            <label for="subject_id">Môn học *</label>
            <select id="subject_id" name="subject_id" required>
                <option value="">— Chọn môn học —</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}"
                        @selected(old('subject_id', $topic->subject_id ?? '') == $subject->id)>
                        {{ $subject->icon }} {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="vh-field">
            <label for="name">Tên chủ đề *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $topic->name ?? '') }}" required maxlength="255"
                   placeholder="Ví dụ: Số tự nhiên">
        </div>
        <div class="vh-field">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $topic->slug ?? '') }}" maxlength="255"
                   placeholder="Để trống để tự sinh từ tên">
        </div>
        <div class="vh-field">
            <label for="icon">Biểu tượng (emoji)</label>
            <input type="text" id="icon" name="icon" value="{{ old('icon', $topic->icon ?? '') }}" maxlength="16"
                   placeholder="Ví dụ: 🔢">
        </div>
        <div class="vh-field">
            <label for="grade_min">Khối lớp bắt đầu *</label>
            <select id="grade_min" name="grade_min">
                @for ($g = 6; $g <= 12; $g++)
                    <option value="{{ $g }}" @selected(old('grade_min', $topic->grade_min ?? 6) == $g)>Lớp {{ $g }}</option>
                @endfor
            </select>
        </div>
        <div class="vh-field">
            <label for="grade_max">Khối lớp kết thúc *</label>
            <select id="grade_max" name="grade_max">
                @for ($g = 6; $g <= 12; $g++)
                    <option value="{{ $g }}" @selected(old('grade_max', $topic->grade_max ?? 12) == $g)>Lớp {{ $g }}</option>
                @endfor
            </select>
        </div>
        <div class="vh-field vh-full">
            <label for="description">Mô tả</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Giới thiệu ngắn về chủ đề">{{ old('description', $topic->description ?? '') }}</textarea>
        </div>
        <div class="vh-field">
            <label for="sort_order">Thứ tự hiển thị</label>
            <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
                   value="{{ old('sort_order', $topic->sort_order ?? 0) }}">
        </div>
        <div class="vh-field">
            <label>Hiển thị ngoài website</label>
            <label style="font-weight:400">
                <input type="checkbox" name="is_published" value="1"
                       @checked(old('is_published', $topic->is_published ?? true))>
                Cho phép học viên nhìn thấy chủ đề này
            </label>
        </div>
    </div>

    <div class="vh-actions" style="margin-top:8px">
        <button type="submit" class="vh-btn vh-btn-primary">💾 Lưu</button>
        <a href="{{ route('admin.topics.index') }}" class="vh-btn vh-btn-ghost">Hủy</a>
    </div>
</form>
