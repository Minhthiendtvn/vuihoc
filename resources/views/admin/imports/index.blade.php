@extends('admin.layout')
@section('title', 'Cập nhật dữ liệu học tập')
@section('content')
<div class="vh-page-head"><h1 class="vh-title">Cập nhật dữ liệu học tập</h1></div>
<div class="vh-card">
    <p>Nhận file SQL UTF-8, tối đa 32 MB / 100.000 bản ghi nội dung. Chỉ cập nhật 9 bảng học liệu theo ID. Dùng dump cùng nguồn ID với database đang chạy.</p>
    <p>Các lệnh DROP, CREATE, TRUNCATE, DELETE và dữ liệu tài khoản, phiên đăng nhập, XP được bỏ qua. Không xóa bản ghi thiếu trong file.</p>
    <p class="vh-hint">Cột “Có sẵn” gồm cả bản ghi không thay đổi. Số lượng được kiểm tra lại khi xác nhận. Hãy backup database trước khi cập nhật nội dung.</p>
    <form method="POST" action="{{ route('admin.imports.preview') }}" enctype="multipart/form-data">
        @csrf
        <label for="sql_file">File dữ liệu .sql</label>
        <input id="sql_file" type="file" name="sql_file" accept=".sql" required>
        <button class="vh-btn vh-btn-primary" type="submit">Phân tích và xem trước</button>
    </form>
</div>
@if ($pending)
<div class="vh-card" style="margin-top:20px">
    <h2>Xem trước: {{ $pending['filename'] }}</h2>
    <p>Preview có hiệu lực 30 phút. Xác nhận sẽ cập nhật nội dung có sẵn và thêm nội dung mới.</p>
    <div class="vh-table-wrap"><table class="vh-table">
        <thead><tr><th>Bảng</th><th>Trong file</th><th>Thêm mới</th><th>Có sẵn / cập nhật</th></tr></thead>
        <tbody>@foreach ($pending['counts'] as $table => $count)
            <tr><td>{{ $table }}</td><td>{{ number_format($count['total']) }}</td><td>{{ number_format($count['new']) }}</td><td>{{ number_format($count['update']) }}</td></tr>
        @endforeach</tbody>
    </table></div>
    <form method="POST" action="{{ route('admin.imports.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $pending['token'] }}">
        <p><label><input type="checkbox" name="confirm" value="1" required> Tôi xác nhận cập nhật nội dung theo preview này.</label></p>
        <button class="vh-btn vh-btn-primary" type="submit">Xác nhận import</button>
    </form>
    <form method="POST" action="{{ route('admin.imports.cancel') }}" style="margin-top:12px">@csrf<button class="vh-btn" type="submit">Hủy preview</button></form>
</div>
@endif
<div class="vh-card" style="margin-top:20px">
    <h2>20 lần cập nhật thành công gần nhất</h2>
    <div class="vh-table-wrap"><table class="vh-table">
        <thead><tr><th>Thời gian ({{ config('app.timezone') }})</th><th>File</th><th>Admin ID</th><th>Số bản ghi</th></tr></thead>
        <tbody>@forelse ($history as $entry)
            <tr><td>{{ $entry->created_at }}</td><td>{{ $entry->filename }}</td><td>{{ $entry->admin_id }}</td><td>{{ number_format(array_sum(array_column(json_decode($entry->counts, true), 'total'))) }}</td></tr>
        @empty<tr><td colspan="4">Chưa có lượt cập nhật.</td></tr>@endforelse</tbody>
    </table></div>
</div>
@endsection
