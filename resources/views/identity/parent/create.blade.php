@extends('layouts.app')

@section('title', 'Thêm hồ sơ con')

@section('content')
<style>
    .vh-emoji-pick { display: flex; flex-wrap: wrap; gap: 8px; }
    .vh-emoji-pick label {
        font-size: 1.9rem; cursor: pointer; padding: 8px 10px;
        border: 2px solid #cbd5e1; border-radius: 12px; line-height: 1;
    }
    .vh-emoji-pick input { display: none; }
    .vh-emoji-pick label:has(input:checked) {
        border-color: var(--brand); background: #eef2ff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .18);
    }
</style>

<div class="vh-card" style="max-width: 560px; margin: 0 auto;">
    <h1 class="vh-title">👶 Thêm hồ sơ con</h1>
    <p class="vh-subtitle">Tạo hồ sơ học tập cho con của bạn. Mục tiêu ngày và cỡ chữ
        để mặc định, bạn có thể đổi sau trong phần sửa hồ sơ.</p>

    <form method="POST" action="{{ route('identity.children.store') }}">
        @csrf

        <div class="vh-field">
            <label for="display_name">Tên hiển thị của con</label>
            <input id="display_name" type="text" name="display_name"
                   value="{{ old('display_name') }}" required maxlength="50" autofocus
                   placeholder="Ví dụ: Bé An">
        </div>

        <div class="vh-field">
            <label>Chọn avatar cho con</label>
            <div class="vh-emoji-pick">
                @foreach ($emojis as $emoji)
                    <label title="{{ $emoji }}">
                        <input type="radio" name="avatar_emoji" value="{{ $emoji }}"
                               {{ old('avatar_emoji', '🦊') === $emoji ? 'checked' : '' }}>
                        <span>{{ $emoji }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vh-field">
            <label for="grade">Lớp của con</label>
            <select id="grade" name="grade" required>
                <option value="">— Chọn lớp —</option>
                @for ($g = 6; $g <= 12; $g++)
                    <option value="{{ $g }}" {{ (int) old('grade') === $g ? 'selected' : '' }}>
                        Lớp {{ $g }}
                    </option>
                @endfor
            </select>
        </div>

        <button type="submit" class="vh-btn vh-btn-primary" style="width: 100%;">Thêm hồ sơ</button>
    </form>

    <p style="margin-top: 16px; text-align: center;">
        <a href="{{ route('identity.children.index') }}">← Quay lại danh sách</a>
    </p>
</div>
@endsection
