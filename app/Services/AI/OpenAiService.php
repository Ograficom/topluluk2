<?php

namespace App\Services\AI;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAiService
{
    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    public function structured(
        string $prompt,
        array $schema,
        ?string $model = null,
        ?float $temperature = null,
        string $schemaName = 'ografi_response',
        ?string $developerInstruction = null,
    ): array {
        $apiKey = trim((string) config('services.openai.api_key'));

        if ($apiKey === '') {
            throw new \RuntimeException('OpenAI API key eksik. .env icine OPENAI_API_KEY ekleyin.');
        }

        $baseUrl = rtrim((string) config('services.openai.url', 'https://api.openai.com/v1'), '/');
        $selectedModel = $this->normalizeModel($model);
        $safeSchemaName = Str::limit(
            preg_replace('/[^A-Za-z0-9_-]/', '_', $schemaName) ?: 'ografi_response',
            64,
            ''
        );

        $payload = [
            'model' => $selectedModel,
            'input' => [
                [
                    'role' => 'developer',
                    'content' => [[
                        'type' => 'input_text',
                        'text' => $developerInstruction
                            ?: 'Turkce yanit ver. Yalnizca verilen gorevi yap, uydurma bilgi ekleme ve istenen JSON semasina kesinlikle uy.',
                    ]],
                ],
                [
                    'role' => 'user',
                    'content' => [[
                        'type' => 'input_text',
                        'text' => $prompt,
                    ]],
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => $safeSchemaName,
                    'strict' => true,
                    'schema' => $schema,
                ],
            ],
            'max_output_tokens' => max(512, (int) config('services.openai.max_output_tokens', 8000)),
        ];

        $reasoningEffort = trim((string) config('services.openai.reasoning_effort', 'none'));
        if ($reasoningEffort !== '') {
            $payload['reasoning'] = ['effort' => $reasoningEffort];
        }

        if ($temperature !== null) {
            $payload['temperature'] = max(0, min(2, $temperature));
        }

        $response = Http::timeout(max(10, (int) config('services.openai.timeout', 120)))
            ->acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->post($baseUrl . '/responses', $payload);

        if (! $response->successful()) {
            $message = $this->apiErrorMessage($response);
            throw new \RuntimeException("OpenAI HTTP {$response->status()}: {$message}");
        }

        $body = $response->json();
        if (! is_array($body)) {
            throw new \RuntimeException('OpenAI gecerli bir JSON yaniti dondurmedi.');
        }

        if (($body['status'] ?? null) === 'failed') {
            $error = data_get($body, 'error.message') ?: 'OpenAI istegi basarisiz oldu.';
            throw new \RuntimeException((string) $error);
        }

        $raw = $this->extractOutputText($body);
        if ($raw === '') {
            throw new \RuntimeException('OpenAI bos cevap dondurdu.');
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('OpenAI structured output gecerli JSON degil: ' . Str::limit($raw, 500, ''));
        }

        return $decoded;
    }

    public function normalizeModel(?string $model = null): string
    {
        $fallback = trim((string) config('services.openai.model', 'gpt-5.6-luna')) ?: 'gpt-5.6-luna';
        $candidate = trim((string) $model);

        if ($candidate === '') {
            return $fallback;
        }

        // Eski Ollama model adlari (ornegin gpt-oss:20b) OpenAI model kimligi degildir.
        if (str_contains($candidate, ':') || Str::startsWith($candidate, ['gpt-oss', 'llama', 'qwen', 'gemma'])) {
            return $fallback;
        }

        return $candidate;
    }

    /** @param array<string, mixed> $body */
    private function extractOutputText(array $body): string
    {
        if (is_string($body['output_text'] ?? null) && trim($body['output_text']) !== '') {
            return trim($body['output_text']);
        }

        $parts = [];

        foreach ((array) ($body['output'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ((array) ($item['content'] ?? []) as $content) {
                if (! is_array($content)) {
                    continue;
                }

                if (($content['type'] ?? null) === 'refusal') {
                    $reason = trim((string) ($content['refusal'] ?? ''));
                    throw new \RuntimeException($reason !== '' ? $reason : 'OpenAI istegi reddetti.');
                }

                if (($content['type'] ?? null) === 'output_text' && is_string($content['text'] ?? null)) {
                    $parts[] = $content['text'];
                }
            }
        }

        return trim(implode("\n", $parts));
    }

    private function apiErrorMessage(Response $response): string
    {
        $message = $response->json('error.message');
        if (is_string($message) && trim($message) !== '') {
            return trim($message);
        }

        return Str::limit(trim($response->body()), 1200, '');
    }
}
