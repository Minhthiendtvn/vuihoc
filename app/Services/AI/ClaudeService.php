<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ClaudeService
{
    public function ask(string $question): string
    {
        return $this->reply($question)['answer'];
    }

    public function reply(string $question): array
    {
        $messages = [['role' => 'user', 'content' => $question]];
        $answer = '';
        // Bound latency and API usage: at most one continuation, no blind retries.
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $response = Http::timeout(60)
                    ->withHeaders([
                        'x-api-key' => config('services.anthropic.key'),
                        'anthropic-version' => '2023-06-01',
                    ])
                    ->post('https://api.anthropic.com/v1/messages', [
                        'model' => config('services.anthropic.model'),
                        'max_tokens' => 4096,
                        'messages' => $messages,
                    ]);

                if (! $response->successful()) {
                    throw new RuntimeException(
                        'Claude API error: '.$response->status()
                    );
                }

                $content = $response->json('content', []);

                $texts = collect($content)
                    ->filter(fn ($block) => ($block['type'] ?? null) === 'text'
                        && isset($block['text'])
                    )
                    ->pluck('text')
                    ->filter(fn ($text) => is_string($text) && $text !== '')
                    ->values();

                $part = $texts->implode("\n");

                if (trim($part) === '') {
                    throw new RuntimeException(
                        'Claude API returned no text content.'
                    );
                }

                // Concatenate without inserting a newline in a sentence split by the limit.
                $answer .= $part;
                $stopReason = $response->json('stop_reason');
                if ($stopReason !== 'max_tokens') {
                    return ['answer' => $answer, 'incomplete' => ! in_array($stopReason, ['end_turn', 'stop_sequence', 'refusal'], true)];
                }
                $messages[] = ['role' => 'assistant', 'content' => $part];
                $messages[] = ['role' => 'user', 'content' => 'Tiếp tục đúng từ chỗ câu trả lời bị ngắt. Không lặp lại phần đã viết, không thêm lời mở đầu. Hoàn thành ngắn gọn các ý còn lại.'];
            } catch (Throwable $error) {
                if ($answer === '') {
                    throw $error;
                }
                report($error);

                // Keep the useful first part if the continuation fails.
                return ['answer' => $answer, 'incomplete' => true];
            }
        }

        return ['answer' => $answer, 'incomplete' => true];
    }
}
