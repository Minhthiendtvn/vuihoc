{{-- Partial đồng hồ đếm ngược 10 phút, tự nộp bài khi hết giờ (Alpine.js). --}}
<div class="vh-play-timer" x-data="playTimer({{ $remainingSeconds }})" x-init="start()">
    <span class="vh-timer-clock">⏱</span>
    <span class="vh-timer-text" :class="{ 'vh-timer-danger': remaining <= 60 }" x-text="fmt(remaining)"></span>
</div>
<script>
function playTimer(total) {
    return {
        remaining: total,
        timer: null,
        start() {
            this.timer = setInterval(() => {
                this.remaining--;
                if (this.remaining <= 0) {
                    clearInterval(this.timer);
                    document.getElementById('vh-play-form').submit();
                }
            }, 1000);
        },
        fmt(s) {
            s = Math.max(0, s);
            return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
        }
    };
}
</script>
