<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VuiHoc') — VuiHoc</title>
    <link rel="stylesheet" href="{{ asset('css/vuihoc.css') }}">
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</head>
<body>
<header class="vh-header">
    <div class="vh-container vh-header-inner">
        <a href="{{ route('home') }}" class="vh-logo">🎓 VuiHoc</a>
        <nav class="vh-nav">
            <a href="{{ route('home') }}">Trang chủ</a>
            <a href="{{ route('library.library') }}">Thư viện</a>
            <a href="{{ route('games.index') }}">🎮 Trò chơi</a>
            <a href="{{ route('progress.index') }}">Tiến độ</a>
            <a href="{{ route('parent.index') }}">Phụ huynh</a>
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
            @endauth
        </nav>
        <div class="vh-auth">
            @guest
                <a href="{{ route('auth.login') }}" class="vh-btn vh-btn-ghost">Đăng nhập</a>
                <a href="{{ route('auth.register') }}" class="vh-btn vh-btn-primary">Đăng ký</a>
            @endguest
            @auth
                <span class="vh-user">👋 {{ auth()->user()->name }}</span>
                <a href="{{ route('auth.logout') }}" class="vh-btn vh-btn-ghost">Đăng xuất</a>
            @endauth
        </div>
    </div>
</header>

<main class="vh-container vh-main">
    @include('partials.alerts')
    @yield('content')
</main>

<footer class="vh-footer">
    <div class="vh-container">
        <p>🎓 VuiHoc — Học mà chơi, chơi mà học.</p>
        <p class="vh-footer-small">Nền tảng game giáo dục đa môn cho học sinh lớp 6–12.</p>
    </div>
</footer>
</body>
</html>
