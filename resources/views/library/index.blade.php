@extends('layouts.app')

@section('title', 'VuiHoc — Học mà chơi, chơi mà giỏi')

@section('content')
@include('library._styles')

{{-- HERO --}}
<section class="lib-hero" x-data="{ showGuide: false }">
    <h1>🎓 VuiHoc</h1>
    <p class="lib-slogan">Biến mỗi bài học thành một trò chơi — học vui mỗi ngày, giỏi lên từng ngày.</p>
    <div class="lib-hero-actions">
        <a href="{{ route('library.library') }}" class="vh-btn vh-btn-primary vh-btn-big">🚀 Bắt đầu học ngay</a>
        <button type="button" class="vh-btn-outline vh-btn-big" @click="showGuide = true">📖 Hướng dẫn</button>
    </div>

    {{-- Modal hướng dẫn 3 bước --}}
    <div class="lib-modal-backdrop" x-show="showGuide" x-cloak @click.self="showGuide = false">
        <div class="lib-modal" style="text-align: left; color: var(--ink);">
            <h2 style="margin-top:0">📖 Học với VuiHoc chỉ với 3 bước</h2>
            <div class="lib-step">
                <div class="lib-step-num">1</div>
                <div>
                    <h3>Chọn môn học</h3>
                    <p>Toán, Văn, Anh văn, Khoa học… đủ các môn từ lớp 6 đến lớp 12, mỗi môn một màu riêng cho dễ nhận biết.</p>
                </div>
            </div>
            <div class="lib-step">
                <div class="lib-step-num">2</div>
                <div>
                    <h3>Chọn chủ đề</h3>
                    <p>Mỗi môn chia thành nhiều chủ đề nhỏ, mỗi chủ đề gồm các kỹ năng và bài học theo đúng khối lớp của bạn.</p>
                </div>
            </div>
            <div class="lib-step">
                <div class="lib-step-num">3</div>
                <div>
                    <h3>Chơi thử thách</h3>
                    <p>Mỗi bài học có 4 kiểu chơi: trắc nghiệm, ghép cặp, kéo-thả sắp xếp và điền từ. Trả lời đúng để nhận XP, giữ chuỗi ngày học và mở huy hiệu!</p>
                </div>
            </div>
            <div style="text-align:center">
                <a href="{{ route('library.library') }}" class="vh-btn vh-btn-primary">Bắt đầu ngay 🚀</a>
                <button type="button" class="vh-btn vh-btn-ghost" @click="showGuide = false" style="margin-left:8px">Đóng</button>
            </div>
        </div>
    </div>
</section>

{{-- MÔN HỌC NỔI BẬT --}}
<section class="lib-section">
    <h2 class="lib-section-title">📚 Môn học nổi bật</h2>
    <p class="lib-section-sub">Chọn một môn để khám phá các chủ đề bên trong.</p>
    @if ($subjects->isEmpty())
        <div class="vh-card"><p class="vh-subtitle">Các môn học đang được chuẩn bị. Hãy quay lại sau nhé!</p></div>
    @else
        <div class="vh-grid">
            @foreach ($subjects as $subject)
                <a href="{{ route('library.subject', $subject->slug) }}" class="lib-subject-card"
                   style="--subject-color: {{ $subject->color ?: '#4f46e5' }}">
                    <div class="lib-subject-body">
                        <div class="lib-subject-icon">{{ $subject->icon ?: '📖' }}</div>
                        <div class="lib-subject-name">{{ $subject->name }}</div>
                        <p class="lib-subject-desc">{{ \Illuminate\Support\Str::limit($subject->description, 80) }}</p>
                        <div class="lib-subject-meta">{{ $subject->published_topics_count }} chủ đề →</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- CHỦ ĐỀ MỚI --}}
<section class="lib-section">
    <h2 class="lib-section-title">✨ Chủ đề mới</h2>
    <p class="lib-section-sub">Những chủ đề vừa được thêm vào thư viện.</p>
    @if ($latestTopics->isEmpty())
        <div class="vh-card"><p class="vh-subtitle">Chưa có chủ đề nào. Nội dung mới sắp ra mắt!</p></div>
    @else
        <div class="vh-grid">
            @foreach ($latestTopics as $topic)
                <div class="lib-card" style="--subject-color: {{ $topic->subject?->color ?: '#4f46e5' }}">
                    <h3 class="lib-card-title">
                        <a href="{{ route('library.topic', $topic->slug) }}">{{ $topic->icon }} {{ $topic->name }}</a>
                    </h3>
                    <p class="lib-card-desc">{{ \Illuminate\Support\Str::limit($topic->description, 90) }}</p>
                    <div class="lib-card-meta">
                        <span class="lib-badge">{{ $topic->subject?->name }}</span>
                        @if ($topic->grade_min && $topic->grade_max)
                            <span class="lib-badge lib-badge-type">Lớp {{ $topic->grade_min }}–{{ $topic->grade_max }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- ĐƯỢC YÊU THÍCH NHẤT --}}
<section class="lib-section">
    <h2 class="lib-section-title">❤️ Được yêu thích nhất</h2>
    <p class="lib-section-sub">Những bài học và chủ đề được các bạn lưu lại nhiều nhất.</p>
    @if ($mostLoved->isNotEmpty())
        <div class="vh-grid">
            @foreach ($mostLoved as $item)
                @if ($item instanceof \App\Models\Lesson)
                    <div class="lib-card" style="--subject-color: {{ $item->skill?->topic?->subject?->color ?: '#4f46e5' }}">
                        <h3 class="lib-card-title">
                            <a href="{{ route('library.lesson', $item->slug) }}">📝 {{ $item->title }}</a>
                        </h3>
                        <div class="lib-card-meta">
                            <span class="lib-badge">❤️ {{ $item->fav_count }} lượt thích</span>
                            <span class="lib-badge lib-badge-type">{{ $item->skill?->topic?->subject?->name }}</span>
                        </div>
                    </div>
                @else
                    <div class="lib-card" style="--subject-color: {{ $item->subject?->color ?: '#4f46e5' }}">
                        <h3 class="lib-card-title">
                            <a href="{{ route('library.topic', $item->slug) }}">{{ $item->icon }} {{ $item->name }}</a>
                        </h3>
                        <div class="lib-card-meta">
                            <span class="lib-badge">❤️ {{ $item->fav_count }} lượt thích</span>
                            <span class="lib-badge lib-badge-type">{{ $item->subject?->name }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @elseif ($suggestions->isNotEmpty())
        <div class="vh-card">
            <p><strong>💡 Gợi ý cho bạn:</strong> chưa có lượt yêu thích nào được ghi nhận — hãy là người đầu tiên khám phá và bấm ♥ cho bài học mình thích nhé!</p>
        </div>
        <div class="vh-grid">
            @foreach ($suggestions as $lesson)
                <div class="lib-card" style="--subject-color: {{ $lesson->skill?->topic?->subject?->color ?: '#4f46e5' }}">
                    <h3 class="lib-card-title">
                        <a href="{{ route('library.lesson', $lesson->slug) }}">📝 {{ $lesson->title }}</a>
                    </h3>
                    <div class="lib-card-meta">
                        <span class="lib-badge lib-badge-type">{{ $lesson->skill?->topic?->subject?->name }}</span>
                        <span class="lib-badge lib-badge-type">{{ $lesson->skill?->topic?->name }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="vh-card"><p class="vh-subtitle">Bảng xếp hạng yêu thích sẽ hiện ở đây khi có dữ liệu. Cùng chờ nhé!</p></div>
    @endif
</section>

{{-- LIÊN KẾT NHANH --}}
<section class="lib-section" style="text-align:center">
    <a href="{{ route('library.library') }}" class="vh-btn vh-btn-ghost">📚 Xem toàn bộ thư viện</a>
    @auth
        <a href="{{ route('library.favorites') }}" class="vh-btn vh-btn-ghost" style="margin-left:8px">❤️ Mục yêu thích của tôi</a>
    @endauth
</section>
@endsection
