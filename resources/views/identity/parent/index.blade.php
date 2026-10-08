@extends('layouts.app')

@section('title', 'Con của tôi')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
        <div>
            <h1 class="vh-title" style="margin-bottom: 4px;">👨‍👩‍👧 Con của tôi</h1>
            <p class="vh-subtitle" style="margin: 0;">Quản lý hồ sơ học tập của các con. Chọn một hồ sơ để xem tiến độ.</p>
        </div>
        <a href="{{ route('identity.children.create') }}" class="vh-btn vh-btn-primary">＋ Thêm hồ sơ con</a>
    </div>

    @if ($children->isEmpty())
        <div class="vh-card" style="text-align: center; padding: 40px 24px;">
            <div style="font-size: 3rem; margin-bottom: 12px;">🦊</div>
            <p class="vh-subtitle">Bạn chưa có hồ sơ con nào.</p>
            <a href="{{ route('identity.children.create') }}" class="vh-btn vh-btn-primary">Tạo hồ sơ đầu tiên</a>
        </div>
    @else
        <div class="vh-grid">
            @foreach ($children as $child)
                <div class="vh-card" style="margin: 0;{{ (int) $activeId === (int) $child->id ? ' outline: 3px solid var(--brand-2);' : '' }}">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <span style="font-size: 2.5rem; line-height: 1;">{{ $child->avatar_emoji }}</span>
                        <div>
                            <strong style="font-size: 1.1rem;">{{ $child->display_name }}</strong>
                            <div style="color: var(--ink-soft); font-size: .9rem;">
                                Lớp {{ $child->grade }} · ⭐ {{ $child->total_xp }} XP · 🏅 Cấp {{ $child->level }}
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        @if ((int) $activeId === (int) $child->id)
                            <span class="vh-btn vh-btn-ghost" style="cursor: default; border-color: var(--brand-2); color: var(--brand-2);">
                                ✅ Đang xem
                            </span>
                        @else
                            <form method="POST" action="{{ route('identity.children.switch', $child) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="vh-btn vh-btn-primary" style="padding: 10px 18px;">
                                    Chuyển sang xem
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('identity.children.destroy', $child) }}" style="margin: 0;"
                              onsubmit="return confirm('Xóa hồ sơ của “{{ $child->display_name }}”? Hành động này không thể hoàn tác.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="vh-btn" style="padding: 10px 18px; background: #fee2e2; color: #991b1b;">
                                🗑 Xóa
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
