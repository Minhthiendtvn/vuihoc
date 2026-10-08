<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản trị') — VuiHoc Admin</title>
    <link rel="stylesheet" href="{{ asset('css/vuihoc.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</head>
<body>
<div class="vh-admin-shell">
    <aside class="vh-admin-side">
        <a href="{{ route('admin.dashboard') }}" class="vh-admin-brand">🎓 VuiHoc <span>Admin</span></a>
        <nav class="vh-admin-nav">
            @php
                $menu = [
                    ['group' => null, 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => '📊', 'label' => 'Tổng quan'],
                    ['group' => 'Học liệu', 'route' => 'admin.subjects.index', 'active' => 'admin.subjects.*', 'icon' => '📚', 'label' => 'Môn học'],
                    ['group' => null, 'route' => 'admin.topics.index', 'active' => 'admin.topics.*', 'icon' => '🗂️', 'label' => 'Chủ đề'],
                    ['group' => null, 'route' => 'admin.skills.index', 'active' => 'admin.skills.*', 'icon' => '🎯', 'label' => 'Kỹ năng'],
                    ['group' => null, 'route' => 'admin.lessons.index', 'active' => 'admin.lessons.*', 'icon' => '📖', 'label' => 'Bài học'],
                    ['group' => null, 'route' => 'admin.questions.index', 'active' => 'admin.questions.*', 'icon' => '❓', 'label' => 'Câu hỏi'],
                    ['group' => null, 'route' => 'admin.imports.index', 'active' => 'admin.imports.*', 'icon' => '📥', 'label' => 'Cập nhật dữ liệu học tập'],
                    ['group' => 'Khác', 'route' => 'admin.badges.index', 'active' => 'admin.badges.*', 'icon' => '🏅', 'label' => 'Huy hiệu'],
                    ['group' => null, 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => '👥', 'label' => 'Người dùng'],
                    ['group' => null, 'route' => 'admin.demo', 'active' => 'admin.demo', 'icon' => '🧹', 'label' => 'Dữ liệu mẫu'],
                ];
            @endphp
            @foreach ($menu as $item)
                @if ($item['group'])
                    <div class="vh-nav-group">{{ $item['group'] }}</div>
                @endif
                <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['active']) ? 'active' : '' }}">
                    {{ $item['icon'] }} {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="vh-admin-side-foot">
            <a href="{{ route('home') }}">← Về website</a>
        </div>
    </aside>

    <main class="vh-admin-main">
        <div class="vh-admin-content">
            @include('partials.alerts')
            @yield('content')
        </div>
    </main>
</div>
@stack('scripts')
</body>
</html>
