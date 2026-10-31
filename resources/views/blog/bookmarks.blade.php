@extends('layouts.repost')

@section('title', 'Kaydedilen gönderiler — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">KÜTÜPHANEM</span><h1>Kaydedilenler</h1><p>Daha sonra okumak için ayırdığın gönderiler.</p></div></div>
    @forelse($posts as $post)
        @include('components.repost-post-card', ['post' => $post])
    @empty
        <div class="card card-body repost-empty-state"><h2>Henüz kayıtlı gönderin yok</h2><p>Beğendiğin gönderileri daha sonra bulmak için kaydedebilirsin.</p><a href="{{ route('home') }}" class="btn btn-primary btn-rounded">Akışı keşfet</a></div>
    @endforelse
    <div class="repost-pagination">{{ $posts->links() }}</div>
@endsection
