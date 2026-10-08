{{-- Danh sách bài được giao. Props: $assignments, $gameTypes, $canDelete = true --}}
@props(['assignments', 'gameTypes' => [], 'canDelete' => true])

@if ($assignments->isEmpty())
    <p class="vh-subtitle">Chưa có bài nào được giao.</p>
@else
    <div class="asg-list">
        @foreach ($assignments as $a)
            @php
                $label = $a->statusLabel();
                $pillClass = $a->status === 'done' ? 'asg-pill-done' : ($a->isOverdue() ? 'asg-pill-overdue' : 'asg-pill-pending');
            @endphp
            <div class="asg-item {{ $a->isOverdue() ? 'asg-overdue' : '' }}">
                <div class="asg-item-main">
                    <div class="asg-item-title">
                        📖 {{ $a->lesson?->title ?? 'Bài học đã bị xoá' }}
                        <span class="asg-pill {{ $pillClass }}">{{ $label === 'Đã làm' ? '✅' : ($label === 'Quá hạn' ? '⏰' : '📌') }} {{ $label }}</span>
                    </div>
                    <div class="asg-item-meta">
                        🎮 {{ $gameTypes[$a->game_type] ?? $a->game_type }}
                        @if ($a->isForClass()) · 🏫 {{ $a->classroom?->name }} @endif
                        @if ($a->deadline)
                            · ⏰ Hạn: {{ $a->deadline->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                        @endif
                        · 👤 {{ $a->creator?->name ?? '—' }}
                    </div>
                    @if ($a->note)
                        <div class="asg-item-note">💬 {{ $a->note }}</div>
                    @endif
                </div>
                <div class="asg-item-actions">
                    <button type="button" class="vh-btn vh-btn-ghost asg-copy"
                            data-url="{{ route('assignments.shared', $a->share_token) }}"
                            title="Sao chép link chia sẻ">🔗 Copy link</button>
                    @if ($canDelete)
                        <form method="POST" action="{{ route('assignments.destroy', $a) }}"
                              onsubmit="return confirm('Xoá bài được giao này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="vh-btn vh-btn-ghost" title="Xoá bài giao">🗑️</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <script>
    document.querySelectorAll('.asg-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-url');
            function done() {
                var old = btn.textContent;
                btn.textContent = '✅ Đã copy!';
                setTimeout(function () { btn.textContent = old; }, 1500);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(function () { fallback(); });
            } else { fallback(); }
            function fallback() {
                var ta = document.createElement('textarea');
                ta.value = url; document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(ta);
            }
        });
    });
    </script>
@endif
