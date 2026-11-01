<div class="ografi-feed-insert-assets" data-og-feed-insert-assets>
    @if($topPosts->isNotEmpty())
        <section class="card card-body og-feed-insert-card" data-og-feed-card-type="top-posts">
            <h2 class="h6">Öne çıkan gönderiler</h2>
            <ul class="widget__list">
                @foreach($topPosts as $topPost)
                    <li class="entry-card"><a class="entry-box" href="{{ url('/tr/'.$topPost->slug) }}"><span class="entry-box-content"><strong class="og-feed-insert-card__name">{{ $topPost->title }}</strong><span class="meta">{{ number_format((int)$topPost->views_count) }} görüntülenme</span></span></a></li>
                @endforeach
            </ul>
        </section>
    @endif
    @if($feedCategories->isNotEmpty())
        <section class="card card-body og-feed-insert-card" data-og-feed-card-type="categories">
            <h2 class="h6">Toplulukları keşfet</h2>
            <ul class="widget__list">
                @foreach($feedCategories as $category)
                    <li><a class="og-feed-insert-card__row" href="{{ url('/Categorys/'.$category->slug) }}"><span class="og-feed-insert-card__name">{{ $category->name }}</span><span class="og-feed-insert-card__side">Göz at</span></a></li>
                @endforeach
            </ul>
        </section>
    @endif
    @if($feedSuggestedUsers->isNotEmpty())
        <section class="card card-body og-feed-insert-card" data-og-feed-card-type="people">
            <h2 class="h6">Takip edilecek kişiler</h2>
            <ul class="widget__list">
                @foreach($feedSuggestedUsers as $person)
                    <li><a class="user-box og-feed-insert-card__person-link" href="{{ url('/u/'.$person->username) }}"><span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="40" height="40"></span><span class="user-box-content"><strong class="name og-feed-insert-card__name">{{ $person->name }}</strong><span class="meta">{{ number_format((int)$person->followers_count) }} takipçi</span></span></a></li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
