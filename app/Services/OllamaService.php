<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OllamaService
{
    protected string $driver;
    protected string $baseUrl;
    protected ?string $apiKey;
    protected string $model;
    protected string $visionModel;
    protected int $timeout;

    public function __construct()
    {
        $this->driver = config('ollama.driver', 'cloud');
        $this->baseUrl = rtrim((string) config('ollama.url', 'https://ollama.com'), '/');
        $this->apiKey = config('ollama.api_key');
        $this->model = config('ollama.model', 'gpt-oss:20b');
        $this->visionModel = config('ollama.vision_model', 'gemma4:31b');
        $this->timeout = (int) config('ollama.timeout', 120);
    }

    protected function client()
    {
        $client = Http::withoutVerifying()
            ->timeout($this->timeout)
            ->retry(2, 500, throw: false)
            ->acceptJson()
            ->asJson();

        if ($this->driver === 'cloud') {
            if (empty($this->apiKey)) {
                throw new RuntimeException('Ollama API key eksik. .env içine OLLAMA_API_KEY ekleyin.');
            }

            $client = $client->withToken($this->apiKey);
        }

        return $client;
    }

    protected function endpoint(string $path): string
    {
        $base = str_ends_with($this->baseUrl, '/api') ? substr($this->baseUrl, 0, -4) : $this->baseUrl;

        return $base . '/api/' . ltrim($path, '/');
    }

    public function generate(string $prompt, ?string $system = null): string
    {
        $payload = [
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.7,
            ],
        ];

        if (! empty($system)) {
            $payload['system'] = $system;
        }

        $response = $this->client()->post($this->endpoint('generate'), $payload);

        if ($response->failed()) {
            throw new RuntimeException('Ollama generate hatası: ' . $response->body());
        }

        $answer = $response->json('response');

        if (! is_string($answer) || trim($answer) === '') {
            throw new RuntimeException('Ollama boş cevap döndürdü.');
        }

        return trim($answer);
    }

    public function chat(array $messages, ?string $model = null): string
    {
        $response = $this->client()->post($this->endpoint('chat'), [
            'model' => $model ?: $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => 0.7,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Ollama chat hatası: ' . $response->body());
        }

        $answer = data_get($response->json(), 'message.content');

        if (! is_string($answer) || trim($answer) === '') {
            throw new RuntimeException('Ollama boş cevap döndürdü.');
        }

        return trim($answer);
    }

    /**
     * Chat request constrained to a JSON response, optionally with one or more
     * base64-encoded images attached to the user message (requires a vision-capable model).
     *
     * NOTE: Ollama Cloud's JSON-Schema `format` mode is unreliable on the large hosted
     * "thinking" models (verified against gpt-oss:20b and gemma4:31b - both silently ignore
     * a schema object and return free-form prose). The schema is instead appended as plain
     * text to the prompt and `format: "json"` (string mode) is used, mirroring the proven
     * approach in RssArticleRewriteService.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $schema  JSON Schema describing the required response shape.
     * @param  array<int, string>  $images  Base64-encoded image payloads (no data: prefix).
     * @return array<string, mixed>
     */
    public function chatStructured(
        array $messages,
        array $schema,
        array $images = [],
        ?string $model = null,
        float $temperature = 0.1,
    ): array {
        if ($messages !== []) {
            $lastIndex = array_key_last($messages);
            $messages[$lastIndex]['content'] = rtrim((string) $messages[$lastIndex]['content'])
                . "\n\nSadece gecerli JSON dondur, JSON disinda hicbir metin ekleme. Beklenen JSON semasi:\n"
                . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($images !== []) {
                $messages[$lastIndex]['images'] = $images;
            }
        }

        $useModel = $model ?: ($images !== [] ? $this->visionModel : $this->model);

        $response = $this->client()->post($this->endpoint('chat'), [
            'model' => $useModel,
            'messages' => $messages,
            'stream' => false,
            'format' => 'json',
            'options' => [
                'temperature' => $temperature,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Ollama chat hatası: ' . $response->body());
        }

        $answer = data_get($response->json(), 'message.content');

        if (! is_string($answer) || trim($answer) === '') {
            throw new RuntimeException('Ollama boş cevap döndürdü.');
        }

        $decoded = json_decode($this->stripJsonCodeFence($answer), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Ollama gecerli JSON dondurmedi: ' . mb_substr($answer, 0, 500));
        }

        return $decoded;
    }

    public function visionModel(): string
    {
        return $this->visionModel;
    }

    /**
     * Some models wrap JSON responses in a markdown code fence (```json ... ```)
     * despite being told not to. Strip it so json_decode() sees raw JSON.
     */
    private function stripJsonCodeFence(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $value, $matches) === 1) {
            return trim($matches[1]);
        }

        return $value;
    }
}