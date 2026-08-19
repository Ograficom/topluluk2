Ografi - Bugünün öne çıkan gönderileri

Merhaba {{ $recipient->name }},

Bugün yayımlanan gönderilerden sizin için en fazla 10 tanesini seçtik.

@foreach ($posts as $post)
{{ $loop->iteration }}. {{ $post->title }}
{{ $post->category?->name ?? 'Gönderi' }}
{{ Illuminate\Support\Str::limit(trim(strip_tags((string) ($post->excerpt ?: $post->content))), 180) }}
{{ route('blog.post', ['post' => $post->slug]) }}

@endforeach
E-posta ayarları: {{ $preferencesUrl }}
Abonelikten çık: {{ $unsubscribeUrl }}
