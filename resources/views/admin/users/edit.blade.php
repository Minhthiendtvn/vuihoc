@extends('admin.layout')

@section('title', 'Sửa người dùng')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">✏️ Người dùng: {{ $user->name }}</h1>
</div>

@php
    $roleLabels = ['admin' => 'Quản trị viên', 'teacher' => 'Giáo viên', 'parent' => 'Phụ huynh', 'learner' => 'Học viên'];
@endphp

<div class="vh-form-card" style="margin-bottom:20px">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="vh-form-grid">
            <div class="vh-field">
                <label>Tên</label>
                <input type="text" value="{{ $user->name }}" disabled>
            </div>
            <div class="vh-field">
                <label>Email</label>
                <input type="text" value="{{ $user->email }}" disabled>
            </div>
            <div class="vh-field">
                <label for="role">Vai trò *</label>
                <select id="role" name="role" @disabled($user->id === auth()->id())>
                    @foreach ($roleLabels as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', $user->role) == $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @if ($user->id === auth()->id())
                    <p class="vh-hint">Không thể đổi vai trò của chính mình.</p>
                @endif
            </div>
        </div>
        @if ($user->id !== auth()->id())
            <div class="vh-actions" style="margin-top:8px">
                <button type="submit" class="vh-btn vh-btn-primary">💾 Lưu vai trò</button>
                <a href="{{ route('admin.users.index') }}" class="vh-btn vh-btn-ghost">Hủy</a>
            </div>
        @endif
    </form>
</div>

<div class="vh-card">
    <h2 style="margin-top:0">📇 Hồ sơ liên quan</h2>

    @if ($user->learnerProfile)
        @php $p = $user->learnerProfile; @endphp
        <h3>🦊 Hồ sơ học viên của chính tài khoản</h3>
        <div class="vh-table-wrap">
            <table class="vh-table">
                <thead><tr><th>Tên hiển thị</th><th>Khối</th><th>XP</th><th>Cấp</th><th>Chuỗi ngày</th><th>Mục tiêu/ngày</th></tr></thead>
                <tbody>
                    <tr>
                        <td>{{ $p->avatar_emoji }} {{ $p->display_name }}</td>
                        <td>Lớp {{ $p->grade }}</td>
                        <td>{{ number_format($p->total_xp) }}</td>
                        <td>{{ $p->level }}</td>
                        <td>{{ $p->current_streak }} ngày (dài nhất {{ $p->longest_streak }})</td>
                        <td>{{ $p->daily_goal }} lượt chơi</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if ($user->childProfiles->isNotEmpty())
        <h3>👨‍👩‍👧 Hồ sơ con ({{ $user->childProfiles->count() }})</h3>
        <div class="vh-table-wrap">
            <table class="vh-table">
                <thead><tr><th>Tên hiển thị</th><th>Khối</th><th>XP</th><th>Cấp</th><th>Chuỗi ngày</th></tr></thead>
                <tbody>
                    @foreach ($user->childProfiles as $child)
                        <tr>
                            <td>{{ $child->avatar_emoji }} {{ $child->display_name }}</td>
                            <td>Lớp {{ $child->grade }}</td>
                            <td>{{ number_format($child->total_xp) }}</td>
                            <td>{{ $child->level }}</td>
                            <td>{{ $child->current_streak }} ngày</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($user->classrooms->isNotEmpty())
        <h3>🏫 Lớp học sở hữu ({{ $user->classrooms->count() }})</h3>
        <ul class="vh-answer-list">
            @foreach ($user->classrooms as $classroom)
                <li><strong>{{ $classroom->name }}</strong> — mã: {{ $classroom->code }}</li>
            @endforeach
        </ul>
    @endif

    @if (! $user->learnerProfile && $user->childProfiles->isEmpty() && $user->classrooms->isEmpty())
        <p class="vh-empty">Tài khoản này chưa có hồ sơ học viên, hồ sơ con hay lớp học nào.</p>
    @endif
</div>

<div style="margin-top:16px">
    <a href="{{ route('admin.users.index') }}" class="vh-btn vh-btn-ghost">← Về danh sách người dùng</a>
</div>
@endsection
