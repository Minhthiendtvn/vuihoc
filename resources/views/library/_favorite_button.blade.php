{{-- Nút yêu thích ♡/♥ — $targetType: lesson|topic, $targetId, $isFav --}}
@props(['targetType', 'targetId', 'isFav' => false])
@guest
    <a href="/dang-nhap" class="lib-fav-btn" title="Đăng nhập để lưu vào yêu thích">♡</a>
@else
    <form method="POST" action="{{ route('library.favorites.toggle') }}" style="display:inline">
        @csrf
        <input type="hidden" name="target_type" value="{{ $targetType }}">
        <input type="hidden" name="target_id" value="{{ $targetId }}">
        <button type="submit" class="lib-fav-btn {{ $isFav ? 'is-fav' : '' }}"
                title="{{ $isFav ? 'Bỏ khỏi yêu thích' : 'Thêm vào yêu thích' }}">{{ $isFav ? '♥' : '♡' }}</button>
    </form>
@endguest
