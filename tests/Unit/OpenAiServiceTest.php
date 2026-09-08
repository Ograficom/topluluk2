<?php

namespace Tests\Unit;

use App\Services\AI\OpenAiService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpenAiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.openai.api_key', 'test-key');
        config()->set('services.openai.url', 'https://api.openai.com/v1');
        config()->set('services.openai.model', 'gpt-5.6-luna');
        config()->set('services.openai.timeout', 30);
        config()->set('services.openai.max_output_tokens', 2000);
        config()->set('services.openai.reasoning_effort', 'none');
    }

    #[Test]
    public function it_sends_structured_outputs_through_the_responses_api(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->successfulResponse(), 200),
        ]);

        $schema = $this->schema();
        $result = app(OpenAiService::class)->structured(
            prompt: 'Test',
            schema: $schema,
            model: 'gpt-5.6-luna',
            temperature: 0.4,
            schemaName: 'test_response',
        );

        $this->assertSame(['ok' => true], $result);

        Http::assertSent(function (Request $request) use ($schema): bool {
            $data = $request->data();

            return $request->url() === 'https://api.openai.com/v1/responses'
                && ($data['model'] ?? null) === 'gpt-5.6-luna'
                && ($data['store'] ?? null) === false
                && data_get($data, 'reasoning.effort') === 'none'
                && ($data['temperature'] ?? null) === 0.4
                && data_get($data, 'text.format.type') === 'json_schema'
                && data_get($data, 'text.format.strict') === true
                && data_get($data, 'text.format.schema') === $schema;
        });
    }

    #[Test]
    public function it_uses_astra_compatible_parameters_when_astra_is_selected(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->successfulResponse(), 200),
        ]);

        app(OpenAiService::class)->structured(
            prompt: 'Test',
            schema: $this->schema(),
            model: 'gpt-6-astra',
            temperature: 0.8,
        );

        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return ($data['model'] ?? null) === 'gpt-6-astra'
                && data_get($data, 'reasoning.effort') === 'low'
                && ! array_key_exists('temperature', $data);
        });
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'ok' => ['type' => 'boolean'],
            ],
            'required' => ['ok'],
            'additionalProperties' => false,
        ];
    }

    /** @return array<string, mixed> */
    private function successfulResponse(): array
    {
        return [
            'status' => 'completed',
            'output' => [[
                'type' => 'message',
                'content' => [[
                    'type' => 'output_text',
                    'text' => '{"ok":true}',
                ]],
            ]],
        ];
    }
}
