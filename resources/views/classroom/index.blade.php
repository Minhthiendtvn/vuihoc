@extends('layouts.app')

@section('title', 'Lớp học của tôi')

@section('content')
<h1 class="vh-title">🏫 Lớp học của tôi</h1>
<p class="vh-subtitle">Tạo lớp cho học sinh, chia sẻ mã 6 ký tự để các em tham gia.</p>

<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">➕ Tạo lớp mới</h2>
    <form method="POST" action="{{ route('classroom.store') }}">
        @csrf
        <div class="vh-field">
            <label for="name">Tên lớp</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                   placeholder="Ví dụ: Lớp Toán 6A">
        </div>
        <div class="vh-field">
            <label for="description">Mô tả (không bắt buộc)</label>
            <textarea id="description" name="description" rows="3" maxlength="2000"
                      placeholder="Thông tin thêm về lớp...">{{ old('description') }}</textarea>
        </div>
        <button type="submit" class="vh-btn vh-btn-primary">Tạo lớp</button>
    </form>
</div>

<div class="vh-card">
    <h2 class="vh-title" style="font-size: 1.25rem;">🔑 Tham gia lớp bằng mã</h2>
    <p class="vh-subtitle">Nhập mã 6 ký tự do giáo viên cung cấp để cho hồ sơ học viên đang hoạt động tham gia lớp.</p>
    <form method="POST" action="{{ route('classroom.join') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
        @csrf
        <input type="text" name="code" required maxlength="16" placeholder="ABC123"
               style="flex: 1; min-width: 160px; padding: 12px 14px; font-size: 1rem; border: 2px solid #cbd5e1; border-radius: 12px; text-transform: uppercase;">
        <button type="submit" class="vh-btn vh-btn-primary">Tham gia</button>
    </form>
</div>

<h2 class="vh-title" style="font-size: 1.4rem;">📚 Các lớp của tôi ({{ $classrooms->count() }})</h2>
@if ($classrooms->isEmpty())
    <div class="vh-card"><p class="vh-subtitle">Chưa có lớp nào. Hãy tạo lớp đầu tiên ở trên nhé!</p></div>
@else
    <div class="vh-grid">
        @foreach ($classrooms as $c)
            <div class="vh-card">
                <h3 style="margin: 0 0 6px;">{{ $c->name }}</h3>
                <p class="vh-subtitle" style="margin-bottom: 8px;">{{ $c->description ?: 'Chưa có mô tả.' }}</p>
                <p style="margin: 0 0 12px;">Mã tham gia: <strong style="font-size: 1.2rem; letter-spacing: 2px; color: var(--brand);">{{ $c->code }}</strong></p>
                <p class="vh-subtitle" style="margin-bottom: 12px;">👥 {{ $c->members_count }} thành viên</p>
                <a href="{{ route('classroom.show', $c) }}" class="vh-btn vh-btn-ghost">Xem chi tiết</a>
            </div>
        @endforeach
    </div>
@endif
@endsection
