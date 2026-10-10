<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeService
{
    public function ask(string $question): string
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.anthropic.model'),
                'max_tokens' => 1000,

                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $question,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Claude API error: '.$response->status()
            );
        }

        $content = $response->json('content', []);

        $texts = collect($content)
            ->filter(fn ($block) =>
                ($block['type'] ?? null) === 'text'
                && isset($block['text'])
            )
            ->pluck('text')
            ->filter()
            ->values();

        $answer = $texts->implode("\n");

        if ($answer === '') {
            throw new RuntimeException(
                'Claude API returned no text content.'
            );
        }

        return $answer;
    }
}
