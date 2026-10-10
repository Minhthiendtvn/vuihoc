@extends('layouts.app')

@section('title', 'AI Tutor')

@section('content')

<div class="vh-card">

    <h1 class="vh-title">
        🤖 AI Tutor
    </h1>

    <p>
        Trợ lý học tập AI của VuiChoi. Bạn có thể hỏi bài,
        yêu cầu giải thích hoặc nhờ AI hướng dẫn cách làm.
    </p>

    <div style="margin-top:20px">

        <textarea
            id="ai-question"
            rows="5"
            placeholder="Ví dụ: Giải thích cho em phép cộng có nhớ..."
            style="
                width:100%;
                padding:14px;
                border:1px solid #ddd;
                border-radius:12px;
                box-sizing:border-box;
                font-size:16px;
            "
        ></textarea>

        <button
            type="button"
            class="vh-btn vh-btn-primary"
            onclick="askAI()"
            style="margin-top:12px"
        >
            ✨ Hỏi AI
        </button>

    </div>

    <div
        id="ai-loading"
        style="display:none;margin-top:20px"
    >
        ⏳ AI đang suy nghĩ...
    </div>

    <div
        id="ai-answer"
        style="
            margin-top:20px;
            white-space:pre-wrap;
            line-height:1.7;
        "
    ></div>

</div>

<script>
async function askAI() {

    const question =
        document.getElementById('ai-question').value.trim();

    const answer =
        document.getElementById('ai-answer');

    const loading =
        document.getElementById('ai-loading');

    if (!question) {
        alert('Bạn hãy nhập câu hỏi.');
        return;
    }

    answer.textContent = '';
    loading.style.display = 'block';

    try {

        const response = await fetch('/api/ai/tutor', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },

            body: JSON.stringify({
                question: question
            })
        });

        const data = await response.json();

        if (data.success) {
            answer.textContent = data.answer;
        } else {
            answer.textContent =
                'AI hiện chưa thể trả lời.';
        }

    } catch (error) {

        answer.textContent =
            'Không thể kết nối với AI.';

    } finally {

        loading.style.display = 'none';
    }
}
</script>

@endsection
