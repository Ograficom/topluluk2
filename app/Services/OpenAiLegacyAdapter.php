<?php

namespace App\Services;

use App\Services\AI\OpenAiService;

/**
 * Backwards-compatible adapter for older code paths that still type-hint
 * OllamaService. No Ollama HTTP request is made from this class.
 */
class OpenAiLegacyAdapter extends OllamaService
{
    public function __construct(private readonly OpenAiService $openAi)
    {
        // Intentionally do not call the legacy Ollama constructor.
    }

    public function generate(string $prompt, ?string $system = null): string
    {
        $messages = [];

        if (filled($system)) {
            $messages[] = ['role' => 'developer', 'content' => (string) $system];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $this->openAi->chat($messages, temperature: 0.7);
    }

    public function chat(array $messages, ?string $model = null): string
    {
        return $this->openAi->chat(
            messages: $messages,
            model: $model,
            temperature: 0.7,
        );
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $schema
     * @param  array<int, string>  $images
     * @return array<string, mixed>
     */
    public function chatStructured(
        array $messages,
        array $schema,
        array $images = [],
        ?string $model = null,
        float $temperature = 0.1,
    ): array {
        $developer = [];
        $conversation = [];

        foreach ($messages as $message) {
            $role = strtolower(trim((string) ($message['role'] ?? 'user')));
            $content = trim((string) ($message['content'] ?? ''));

            if ($content === '') {
                continue;
            }

            if (in_array($role, ['system', 'developer'], true)) {
                $developer[] = $content;
                continue;
            }

            $conversation[] = strtoupper($role) . ":\n" . $content;
        }

        return $this->openAi->structured(
            prompt: implode("\n\n", $conversation),
            schema: $schema,
            model: $model,
            temperature: $temperature,
            schemaName: 'ografi_legacy_ai',
            developerInstruction: $developer !== []
                ? implode("\n\n", $developer)
                : 'Turkce cevap ver. Uydurma bilgi ekleme ve structured output semasina tam uy.',
            images: $images,
        );
    }

    public function visionModel(): string
    {
        return (string) config('services.openai.model', 'gpt-5.6-luna');
    }
}
