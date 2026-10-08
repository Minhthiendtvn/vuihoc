@extends('admin.layout')

@section('title', 'Dữ liệu mẫu')

@section('content')
<div class="vh-page-head">
    <h1 class="vh-title" style="margin:0">🧹 Dữ liệu mẫu</h1>
</div>

<div class="vh-card">
    <p>Dữ liệu mẫu là các bản ghi có cờ <code>is_demo = 1</code> (do seeder tạo ra để demo). Nội dung do admin nhập qua trang quản trị có <code>is_demo = 0</code> và <strong>không bị ảnh hưởng</strong> khi xóa dữ liệu mẫu.</p>

    <div class="vh-table-wrap">
        <table class="vh-table">
            <thead><tr><th>Bảng</th><th>Số bản ghi mẫu</th></tr></thead>
            <tbody>
                @foreach ($counts as $label => $count)
                    <tr>
                        <td>{{ $label }}</td>
                        <td><strong>{{ number_format($count) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px">
        <form method="POST" action="{{ route('admin.demo.destroy') }}"
              onsubmit="return confirm('Xóa TOÀN BỘ dữ liệu mẫu (các bản ghi is_demo = 1)? Dữ liệu thật do admin nhập sẽ được giữ lại. Hành động này không thể hoàn tác!')">
            @csrf
            @method('DELETE')
            <button type="submit" class="vh-btn vh-btn-danger">🗑 Xóa toàn bộ dữ liệu mẫu</button>
        </form>
        <p class="vh-hint">Xóa theo thứ tự khóa ngoại: Câu hỏi (kèm dữ liệu con) → Bài học → Kỹ năng → Chủ đề → Môn học → Huy hiệu.</p>
    </div>
</div>
@endsection
