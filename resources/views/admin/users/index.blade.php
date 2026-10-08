@extends('admin.layout')

@section('title', 'Người dùng')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">👥 Người dùng</h1>
</div>

@php
    $roleLabels = ['admin' => 'Quản trị viên', 'teacher' => 'Giáo viên', 'parent' => 'Phụ huynh', 'learner' => 'Học viên'];
    $roleClass = ['admin' => 'vh-pill-red', 'teacher' => 'vh-pill-blue', 'parent' => 'vh-pill-amber', 'learner' => 'vh-pill-green'];
@endphp

<div class="vh-table-wrap">
    <table class="vh-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Hồ sơ học viên</th>
                <th>Ngày tạo</th>
                <th style="width:110px">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        @if ($user->id === auth()->id())
                            <span class="vh-pill vh-pill-gray">Bạn</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td><span class="vh-pill {{ $roleClass[$user->role] ?? 'vh-pill-gray' }}">{{ $roleLabels[$user->role] ?? $user->role }}</span></td>
                    <td>{{ $user->learnerProfile?->display_name ?? '—' }}</td>
                    <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="vh-btn vh-btn-ghost vh-btn-small">✏️ Sửa</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="vh-empty">Chưa có người dùng nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px">{{ $users->links() }}</div>
@endsection
