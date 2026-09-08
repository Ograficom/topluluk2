<?php

namespace Tests\Unit;

use App\Services\OpenAiLegacyAdapter;
use App\Services\OllamaService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpenAiLegacyAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.openai.api_key', 'test-key');
        config()->set('services.openai.url', 'https://api.openai.com/v1');
        config()->set('services.openai.model', 'gpt-5.6-luna');
        config()->set('services.openai.reasoning_effort', 'none');
    }

    #[Test]
    public function legacy_ollama_contract_resolves_to_the_openai_adapter(): void
    {
        $service = app(OllamaService::class);

        $this->assertInstanceOf(OpenAiLegacyAdapter::class, $service);
    }

    #[Test]
    public function legacy_structured_calls_are_sent_to_openai_not_ollama(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'status' => 'completed',
                'output' => [[
                    'type' => 'message',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => '{"flag":false,"reason":null}',
                    ]],
                ]],
            ], 200),
            '*' => Http::response([], 500),
        ]);

        $result = app(OllamaService::class)->chatStructured(
            messages: [['role' => 'user', 'content' => 'Kontrol et']],
            schema: [
                'type' => 'object',
                'properties' => [
                    'flag' => ['type' => 'boolean'],
                    'reason' => ['type' => 'string'],
                ],
                'required' => ['flag'],
            ],
        );

        $this->assertFalse($result['flag']);
        $this->assertNull($result['reason']);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.openai.com/v1/responses'
                && data_get($request->data(), 'model') === 'gpt-5.6-luna';
        });
    }
}
