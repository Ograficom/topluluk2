<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FacebookUrlInspect extends Command
{
    protected $signature = 'facebook:url {post : Ografi yazı adresi veya slug} {--fields=id,engagement : Graph API alanları}';

    protected $description = 'Facebook Graph API üzerinden Ografi yazısının URL bilgilerini sorgula';

    public function handle(): int
    {
        $post = trim((string) $this->argument('post'));
        $url = filter_var($post, FILTER_VALIDATE_URL)
            ? $post
            : (preg_match('/^[\pL\pN_-]+$/u', $post) ? route('posts.show', ['post' => $post]) : '');

        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (! is_array($parts)
            || ($parts['scheme'] ?? '') !== 'https'
            || ! in_array($host, ['ografi.com', 'www.ografi.com'], true)
            || ! preg_match('~^/posts/[^/]+/?$~', $parts['path'] ?? '')
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            $this->error('Geçerli bir HTTPS Ografi yazı adresi girin.');

            return self::FAILURE;
        }

        // Use the canonical public URL, not tracking parameters or fragments.
        $url = 'https://ografi.com'.rtrim($parts['path'], '/');
        $token = trim((string) config('services.facebook.access_token'));
        $version = (string) config('services.facebook.graph_version', 'v27.0');

        if ($token === '' || ! preg_match('/^v\d+\.\d+$/', $version)) {
            $this->error('FACEBOOK_GRAPH_ACCESS_TOKEN ve FACEBOOK_GRAPH_VERSION ayarlarını kontrol edin.');

            return self::FAILURE;
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->connectTimeout(5)
                ->timeout(15)
                ->withOptions(['allow_redirects' => false])
                ->get('https://graph.facebook.com/'.$version.'/', [
                    'id' => $url,
                    'fields' => (string) $this->option('fields'),
                ]);
        } catch (ConnectionException) {
            $this->error('Facebook Graph API bağlantısı kurulamadı.');

            return self::FAILURE;
        }

        $data = $response->json();

        if (! $response->successful() || ! is_array($data) || isset($data['error'])) {
            // Do not print raw API errors or request details that could contain credentials.
            $code = $response->json('error.code');
            $this->error('Facebook Graph API sorgusu başarısız (HTTP '.$response->status()
                .(is_numeric($code) ? ', Meta kodu '.(int) $code : '').').');

            return self::FAILURE;
        }

        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));

        return self::SUCCESS;
    }
}
