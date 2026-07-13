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
    protected int $timeout;

    public function __construct()
    {
        $this->driver = config('ollama.driver', 'cloud');
        $this->baseUrl = rtrim(config('ollama.base_url', 'https://ollama.com'), '/');
        $this->apiKey = config('ollama.api_key');
        $this->model = config('ollama.model', 'gpt-oss:20b');
        $this->timeout = (int) config('ollama.timeout', 120);
    }

    protected function client()
    {
        $client = Http::timeout($this->timeout)
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
        if ($this->driver === 'cloud') {
            return $this->baseUrl . '/api/' . ltrim($path, '/');
        }

        return $this->baseUrl . '/api/' . ltrim($path, '/');
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

    public function chat(array $messages): string
    {
        $response = $this->client()->post($this->endpoint('chat'), [
            'model' => $this->model,
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
}