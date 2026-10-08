{{-- Dùng chung cho create/edit. Biến: $skill (null khi thêm), $topics, $action, $method --}}
<form method="POST" action="{{ $action }}" class="vh-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="vh-form-grid">
        <div class="vh-field">
            <label for="topic_id">Chủ đề *</label>
            <select id="topic_id" name="topic_id" required>
                <option value="">— Chọn chủ đề —</option>
                @foreach ($topics as $topic)
                    <option value="{{ $topic->id }}"
                        @selected(old('topic_id', $skill->topic_id ?? '') == $topic->id)>
                        {{ $topic->name }} ({{ $topic->subject->name ?? '' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="vh-field">
            <label for="name">Tên kỹ năng *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $skill->name ?? '') }}" required maxlength="255"
                   placeholder="Ví dụ: Cộng trừ số tự nhiên">
        </div>
        <div class="vh-field">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $skill->slug ?? '') }}" maxlength="255"
                   placeholder="Để trống để tự sinh từ tên">
        </div>
        <div class="vh-field">
            <label for="sort_order">Thứ tự hiển thị</label>
            <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
                   value="{{ old('sort_order', $skill->sort_order ?? 0) }}">
        </div>
        <div class="vh-field vh-full">
            <label for="description">Mô tả</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Mô tả ngắn về kỹ năng cần rèn luyện">{{ old('description', $skill->description ?? '') }}</textarea>
        </div>
    </div>

    <div class="vh-actions" style="margin-top:8px">
        <button type="submit" class="vh-btn vh-btn-primary">💾 Lưu</button>
        <a href="{{ route('admin.skills.index') }}" class="vh-btn vh-btn-ghost">Hủy</a>
    </div>
</form>
