<!doctype html>
<html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OgraFi paylaşımları</title></head>
<body><main><h1>OgraFi paylaşımları</h1>
@foreach($posts as $post)
    <article><h2><a href="{{ route('blog.post', $post->slug) }}">{{ $post->title }}</a></h2><p>{{ $post->excerpt }}</p><p>{{ $post->author?->name }} · {{ optional($post->published_at)->toAtomString() }}</p></article>
@endforeach
</main></body></html>
