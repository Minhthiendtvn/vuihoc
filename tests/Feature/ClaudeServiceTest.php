<?php

namespace Tests\Feature;

use App\Services\AI\ClaudeService;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.anthropic.key' => 'test-only', 'services.anthropic.model' => 'test-model', 'logging.default' => 'null']);
    }

    private function response(string $text, string $reason = 'end_turn'): array
    {
        return ['content' => [['type' => 'text', 'text' => $text]], 'stop_reason' => $reason];
    }

    public function test_complete_response_does_not_make_an_extra_request(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->response('Hoàn tất.'))]);
        $this->assertSame(['answer' => 'Hoàn tất.', 'incomplete' => false], (new ClaudeService)->reply('Hỏi'));
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['max_tokens'] === 4096);
    }

    public function test_token_limit_continues_with_previous_answer_and_original_context(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::sequence()->push($this->response('Kết quả là ', 'max_tokens'))->push($this->response('42.'))]);
        $this->assertSame(['answer' => 'Kết quả là 42.', 'incomplete' => false], (new ClaudeService)->reply('Câu hỏi gốc'));
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => count($request['messages']) === 3 && $request['messages'][0]['content'] === 'Câu hỏi gốc' && $request['messages'][1] === ['role' => 'assistant', 'content' => 'Kết quả là ']);
    }

    public function test_two_truncated_parts_are_marked_incomplete_without_an_unbounded_loop(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::sequence()->push($this->response('Phần 1 ', 'max_tokens'))->push($this->response('phần 2', 'max_tokens'))]);
        $this->assertSame(['answer' => 'Phần 1 phần 2', 'incomplete' => true], (new ClaudeService)->reply('Hỏi'));
        Http::assertSentCount(2);
    }

    public function test_failed_continuation_preserves_partial_answer(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::sequence()->push($this->response('Đã giải được một phần', 'max_tokens'))->push([], 503)]);
        $this->assertSame(['answer' => 'Đã giải được một phần', 'incomplete' => true], (new ClaudeService)->reply('Hỏi'));
        Http::assertSentCount(2);
    }

    public function test_first_request_error_is_not_returned_as_success(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response([], 401)]);
        $this->expectException(RuntimeException::class);
        (new ClaudeService)->reply('Hỏi');
    }

    public function test_empty_response_is_rejected(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->response(''))]);
        $this->expectException(RuntimeException::class);
        (new ClaudeService)->reply('Hỏi');
    }

    public function test_numeric_zero_is_not_discarded_as_empty_text(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->response('0'))]);
        $this->assertSame('0', (new ClaudeService)->ask('Kết quả bằng bao nhiêu?'));
    }

    public function test_api_exposes_incomplete_notice_for_both_tutor_views(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::sequence()->push($this->response('Một phần', 'max_tokens'))->push($this->response(' nữa', 'max_tokens'))]);
        $this->postJson('/api/ai/tutor', ['question' => 'Giải thích'])->assertOk()->assertJsonPath('success', true)->assertJsonPath('incomplete', true)->assertJsonPath('answer', 'Một phần nữa')->assertJsonStructure(['notice']);
    }
}
