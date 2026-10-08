{{-- Partial form hồ sơ: dùng chung cho tạo mới và sửa.
     Biến: $emojis (array), $fontSizes (array), $profile (model|null). --}}
<style>
    .vh-emoji-grid { display: flex; flex-wrap: wrap; gap: 8px; }
    .vh-emoji-grid label {
        font-size: 1.9rem; cursor: pointer; padding: 8px 10px;
        border: 2px solid #cbd5e1; border-radius: 12px; line-height: 1;
        transition: border-color .12s ease, transform .08s ease;
    }
    .vh-emoji-grid label:hover { transform: scale(1.06); }
    .vh-emoji-grid input { display: none; }
    .vh-emoji-grid input:checked + span { }
    .vh-emoji-grid label:has(input:checked) {
        border-color: var(--brand); background: #eef2ff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .18);
    }
</style>

@php
    $current = fn($field, $default = null) => old($field, $profile?->$field ?? $default);
@endphp

<div class="vh-field">
    <label for="display_name">Tên hiển thị</label>
    <input id="display_name" type="text" name="display_name"
           value="{{ $current('display_name') }}" required maxlength="50"
           placeholder="Ví dụ: Bé An">
</div>

<div class="vh-field">
    <label>Chọn avatar</label>
    <div class="vh-emoji-grid">
        @foreach ($emojis as $emoji)
            <label title="{{ $emoji }}">
                <input type="radio" name="avatar_emoji" value="{{ $emoji }}"
                       {{ $current('avatar_emoji', '🦊') === $emoji ? 'checked' : '' }}>
                <span>{{ $emoji }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="vh-field">
    <label for="grade">Lớp</label>
    <select id="grade" name="grade" required>
        <option value="">— Chọn lớp —</option>
        @for ($g = 6; $g <= 12; $g++)
            <option value="{{ $g }}" {{ (int) $current('grade') === $g ? 'selected' : '' }}>
                Lớp {{ $g }}
            </option>
        @endfor
    </select>
</div>

<div class="vh-field">
    <label for="daily_goal">Mục tiêu mỗi ngày (số thử thách, 1–10)</label>
    <input id="daily_goal" type="number" name="daily_goal" min="1" max="10"
           value="{{ $current('daily_goal', 3) }}" required>
</div>

<div class="vh-field">
    <label for="font_size">Cỡ chữ</label>
    <select id="font_size" name="font_size" required>
        @foreach ($fontSizes as $value => $label)
            <option value="{{ $value }}" {{ $current('font_size', 'normal') === $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    <p style="font-size: .85rem; color: var(--ink-soft); margin: 6px 0 0;">
        Cỡ chữ bạn chọn sẽ được áp dụng cho toàn bộ trang ở giai đoạn tiếp theo.
    </p>
</div>
