<section class="widget card card-body">
    <header><h2 class="widget__title h6">Etiketler</h2></header>
    <div class="widget__content tagscloud">
        @forelse ($popularTags as $tag => $count)
            <span><a href="{{ route('tags.show', ['tag' => $tag]) }}">{{ $tag }}</a></span>
        @empty
            <span class="small color-gray">Henüz etiket yok</span>
        @endforelse
    </div>
    <footer class="widget__footer"><a href="{{ route('tags.index') }}" class="link-accent">Tüm etiketler</a></footer>
</section>
