<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Services\AI\ClaudeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiTutorController extends Controller
{
    public function ask(Request $request, ClaudeService $claude): JsonResponse
    {
        $validated = $request->validate([
            'question'  => ['required', 'string', 'max:3000'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ]);

        try {
            $lesson = null;

            if (!empty($validated['lesson_id'])) {
                $lesson = Lesson::with([
                    'skill.topic.subject'
                ])->findOrFail($validated['lesson_id']);
            }

            $context = $this->buildLessonContext($lesson);

            $prompt = <<<PROMPT
Bạn là AI Tutor của nền tảng giáo dục VuiChoi dành cho học sinh Việt Nam.

NGUYÊN TẮC:
- Luôn trả lời bằng tiếng Việt rõ ràng, tự nhiên.
- Điều chỉnh cách giải thích phù hợp với lớp của học sinh.
- Ưu tiên kiến thức và nội dung bài học được cung cấp.
- Giải thích từng bước đối với bài toán hoặc vấn đề cần suy luận.
- Khuyến khích học sinh tự suy nghĩ, không chỉ đưa đáp án.
- Có thể đưa ví dụ đơn giản, gần gũi.
- Nếu học sinh hiểu sai, chỉ rõ chỗ sai và giải thích lại.
- Không bịa dữ kiện khi không chắc chắn.
- Trình bày vừa đủ, tránh câu trả lời quá dài nếu không cần thiết.

THÔNG TIN BÀI HỌC:
{$context}

CÂU HỎI CỦA HỌC SINH:
{$validated['question']}

Hãy trả lời như một gia sư thân thiện và chính xác.
PROMPT;

            $answer = $claude->ask($prompt);

            return response()->json([
                'success' => true,
                'answer' => $answer,
                'lesson' => $lesson ? [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'grade' => $lesson->grade,
                    'skill' => $lesson->skill?->name,
                    'topic' => $lesson->skill?->topic?->name,
                    'subject' => $lesson->skill?->topic?->subject?->name,
                ] : null,
            ]);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'AI hiện chưa thể trả lời. Vui lòng thử lại.',
            ], 500);
        }
    }

    private function buildLessonContext(?Lesson $lesson): string
    {
        if (!$lesson) {
            return 'Không có bài học cụ thể. Hãy trả lời dựa trên câu hỏi của học sinh.';
        }

        $skill = $lesson->skill;
        $topic = $skill?->topic;
        $subject = $topic?->subject;

        return <<<CONTEXT
Lớp: {$lesson->grade}
Môn học: {$subject?->name}
Chủ đề: {$topic?->name}
Kỹ năng: {$skill?->name}
Bài học: {$lesson->title}
Độ khó: {$lesson->difficulty}
Mục tiêu bài học:
{$lesson->objective}

Hướng dẫn:
{$lesson->instructions}

Tóm tắt bài học:
{$lesson->summary}
CONTEXT;
    }
}