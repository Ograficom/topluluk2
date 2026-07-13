<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OllamaRssEditor
{
    public function edit(string $title, string $summary): array
    {
        $response = Http::timeout(config('rss_import.ollama_timeout'))
            ->post(config('rss_import.ollama_url').'/api/generate', [
                'model' => config('rss_import.ollama_model'),
                'stream' => false,
                'format' => 'json',
                'prompt' => implode("\n", [
                    'Aşağıdaki RSS içeriğini Türkçe, tarafsız ve özgün bir haber metni olarak düzenle.',
                    'Bilgi ekleme, iddia uydurma, kaynak URL üretme ve reklam dili kullanma.',
                    'Yalnızca JSON döndür: {"title":"en fazla 160 karakter","summary":"en fazla 250 karakter","content":"2-5 kısa paragraf"}',
                    'Başlık: '.$title,
                    'Metin: '.$summary,
                ]),
                'options' => ['temperature' => 0.2],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Ollama request failed with HTTP '.$response->status());
        }

        $edited = json_decode($response->json('response', ''), true);
        if (! is_array($edited) || empty($edited['title']) || empty($edited['content'])) {
            throw new RuntimeException('Ollama returned an invalid response.');
        }

        return [
            'title' => mb_substr(strip_tags($edited['title']), 0, 160),
            'summary' => mb_substr(strip_tags($edited['summary'] ?? ''), 0, 250),
            'content' => trim(strip_tags($edited['content'])),
        ];
    }
}
