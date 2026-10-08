{{-- Dùng chung cho create/edit. Biến: $subject (null khi thêm), $action, $method --}}
<form method="POST" action="{{ $action }}" class="vh-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="vh-form-grid">
        <div class="vh-field">
            <label for="name">Tên môn học *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $subject->name ?? '') }}" required maxlength="255"
                   placeholder="Ví dụ: Toán học">
        </div>
        <div class="vh-field">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $subject->slug ?? '') }}" maxlength="255"
                   placeholder="Để trống để tự sinh từ tên">
            <p class="vh-hint">Dùng trong đường dẫn, ví dụ: toan-hoc</p>
        </div>
        <div class="vh-field">
            <label for="icon">Biểu tượng (emoji)</label>
            <input type="text" id="icon" name="icon" value="{{ old('icon', $subject->icon ?? '') }}" maxlength="16"
                   placeholder="Ví dụ: 🧮">
        </div>
        <div class="vh-field">
            <label for="color">Màu chủ đạo</label>
            <input type="color" id="color" name="color" value="{{ old('color', $subject->color ?? '#4f46e5') }}"
                   style="height:48px;padding:4px">
        </div>
        <div class="vh-field vh-full">
            <label for="description">Mô tả</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Giới thiệu ngắn về môn học">{{ old('description', $subject->description ?? '') }}</textarea>
        </div>
        <div class="vh-field">
            <label for="sort_order">Thứ tự hiển thị</label>
            <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
                   value="{{ old('sort_order', $subject->sort_order ?? 0) }}">
        </div>
        <div class="vh-field">
            <label>Hiển thị ngoài website</label>
            <label style="font-weight:400">
                <input type="checkbox" name="is_published" value="1"
                       @checked(old('is_published', $subject->is_published ?? true))>
                Cho phép học viên nhìn thấy môn học này
            </label>
        </div>
    </div>

    <div class="vh-actions" style="margin-top:8px">
        <button type="submit" class="vh-btn vh-btn-primary">💾 Lưu</button>
        <a href="{{ route('admin.subjects.index') }}" class="vh-btn vh-btn-ghost">Hủy</a>
    </div>
</form>
