<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateVideoSubtitles extends Command
{
    protected $signature = 'subtitles:generate {postId}';

    protected $description = 'Videolar için OpenAI Whisper tabanlı otomatik altyazı oluşturur.';

    public function handle(): int
    {
        $apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');
        if (!filled($apiKey)) {
            $this->error('OPENAI_API_KEY tanımlı değil.');
            return Command::FAILURE;
        }

        $post = Post::findOrFail($this->argument('postId'));
        $blocks = collect($post->content_json['blocks'] ?? []);
        $updated = false;

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) !== 'video') {
                continue;
            }

            $filePath = $this->resolvePublicPath($block['data']['url'] ?? '');
            if (!$filePath || !file_exists($filePath)) {
                $this->warn("Video dosyası bulunamadı: {$block['data']['url']}");
                continue;
            }

            $this->line("İşleniyor: {$filePath}");

            $subtitleUrl = $this->transcribeToVtt($filePath, $apiKey);
            if (!$subtitleUrl) {
                $this->warn('Altyazı üretilemedi.');
                continue;
            }

            $subtitles = $block['data']['subtitles'] ?? [];
            $subtitles[] = [
                'url' => $subtitleUrl,
                'lang' => 'tr',
                'label' => 'Otomatik',
            ];

            $blocks[$index]['data']['subtitles'] = $subtitles;
            $updated = true;
        }

        if ($updated) {
            $post->content_json = ['blocks' => $blocks->toArray()];
            $post->save();
            $this->info('Altyazılar başarıyla kaydedildi.');
            return Command::SUCCESS;
        }

        $this->warn('Güncellenecek video bloğu bulunamadı.');
        return Command::SUCCESS;
    }

    private function resolvePublicPath(string $url): ?string
    {
        $baseUrl = Storage::disk('public')->url('');
        if (Str::startsWith($url, $baseUrl)) {
            $relative = Str::after($url, $baseUrl);
            return Storage::disk('public')->path($relative);
        }

        $parsed = parse_url($url);
        if (!empty($parsed['path']) && Str::startsWith($parsed['path'], '/storage/')) {
            $relative = Str::after($parsed['path'], '/storage/');
            return Storage::disk('public')->path($relative);
        }

        return null;
    }

    private function transcribeToVtt(string $filePath, string $apiKey): ?string
    {
        $response = Http::withToken($apiKey)
            ->attach('file', file_get_contents($filePath), basename($filePath))
            ->asMultipart()
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'tr',
            ]);

        if (!$response->successful()) {
            $this->error('Transkripsiyon hatası: ' . $response->body());
            return null;
        }

        $text = trim($response->json('text') ?? '');
        if ($text === '') {
            $this->warn('Transkripsiyon boş çıktı verdi.');
            return null;
        }

        $vtt = $this->buildVtt($text);
        $fileName = 'editorjs/subtitles/autogen-' . uniqid() . '.vtt';
        Storage::disk('public')->put($fileName, $vtt);

        return Storage::disk('public')->url($fileName);
    }

    private function buildVtt(string $text): string
    {
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = array_chunk($words, 40);
        $duration = 4.0;
        $time = 0.0;
        $lines = ['WEBVTT', ''];

        foreach ($chunks as $chunk) {
            $start = $this->formatTime($time);
            $end = $this->formatTime($time + $duration);
            $time += $duration;

            $lines[] = "{$start} --> {$end}";
            $lines[] = implode(' ', $chunk);
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function formatTime(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d:%06.3f', $hours, $minutes, $secs);
    }
}
