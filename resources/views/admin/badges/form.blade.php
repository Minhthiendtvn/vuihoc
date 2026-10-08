{{-- Dùng chung cho create/edit. Biến: $badge (null khi thêm), $criteriaLabels, $action, $method --}}
<form method="POST" action="{{ $action }}" class="vh-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="vh-form-grid">
        <div class="vh-field">
            <label for="name">Tên huy hiệu *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $badge->name ?? '') }}" required maxlength="255"
                   placeholder="Ví dụ: Ngôi sao chăm chỉ">
        </div>
        <div class="vh-field">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $badge->slug ?? '') }}" maxlength="255"
                   placeholder="Để trống để tự sinh từ tên">
        </div>
        <div class="vh-field">
            <label for="icon">Biểu tượng (emoji)</label>
            <input type="text" id="icon" name="icon" value="{{ old('icon', $badge->icon ?? '🏅') }}" maxlength="16"
                   placeholder="Ví dụ: ⭐">
        </div>
        <div class="vh-field">
            <label for="criteria">Tiêu chí xét huy hiệu *</label>
            <select id="criteria" name="criteria">
                @foreach ($criteriaLabels as $code => $label)
                    <option value="{{ $code }}" @selected(old('criteria', $badge->criteria ?? 'first_play') == $code)>
                        {{ $label }} ({{ $code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="vh-field">
            <label for="threshold">Ngưỡng đạt huy hiệu *</label>
            <input type="number" id="threshold" name="threshold" min="1" max="1000000" required
                   value="{{ old('threshold', $badge->threshold ?? 1) }}">
            <p class="vh-hint">Ví dụ: tiêu chí “Chuỗi học 3 ngày” thì ngưỡng là 3.</p>
        </div>
        <div class="vh-field vh-full">
            <label for="description">Mô tả</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Mô tả cách đạt được huy hiệu này">{{ old('description', $badge->description ?? '') }}</textarea>
        </div>
    </div>

    <div class="vh-actions" style="margin-top:8px">
        <button type="submit" class="vh-btn vh-btn-primary">💾 Lưu</button>
        <a href="{{ route('admin.badges.index') }}" class="vh-btn vh-btn-ghost">Hủy</a>
    </div>
</form>
