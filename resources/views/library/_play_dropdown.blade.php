{{-- Dropdown "Chơi ngay": chọn 1 trong 4 kiểu chơi → POST /bai-hoc/{lesson}/choi/{game_type} --}}
@props(['lesson'])
@php
    $gameTypes = config('vuihoc.game_types', []);
    $playUrl = fn (string $type) => \App\Http\Controllers\Library\LibraryController::playUrl($lesson, $type);
@endphp
@guest
    <a href="/dang-nhap" class="vh-btn vh-btn-primary">▶ Chơi ngay</a>
@else
    <div class="lib-play-dropdown" x-data="{ open: false }">
        <button type="button" class="vh-btn vh-btn-primary" @click="open = !open">▶ Chơi ngay ▾</button>
        <div class="lib-play-menu" x-show="open" @click.outside="open = false" x-cloak>
            @foreach ($gameTypes as $type => $label)
                @php $count = (int) ($lesson->{"{$type}_count"} ?? 0); @endphp
                <form method="POST" action="{{ $playUrl($type) }}">
                    @csrf
                    <button type="submit" @disabled($count === 0) title="{{ $count === 0 ? 'Bài này chưa có câu hỏi kiểu ' . $label : "Bắt đầu chơi kiểu $label" }}">🎯 {{ $label }} <span class="lib-badge-type lib-badge">{{ $count }} câu</span></button>
                </form>
            @endforeach
        </div>
    </div>
@endguest
