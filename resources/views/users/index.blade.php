@extends('layouts.repost')

@section('title', 'Üyeler — OgraFi')
@section('meta_description', 'OgraFi topluluğunun üyelerini keşfet.')

@section('content')
    <div data-ografi-layout-container="users_index">
        <section data-ografi-layout-section="users_title" class="repost-feed-heading"><div><span class="repost-eyebrow">TOPLULUK</span><h1>Üyeler</h1><p>OgraFi'de paylaşım yapan kişileri keşfet.</p></div></section>
        <form method="GET" action="{{ route('users.index') }}" class="repost-sort-row">
            <input type="search" name="q" value="{{ $search }}" placeholder="İsim veya kullanıcı adı ara">
            <select name="sort" onchange="this.form.submit()"><option value="newest" @selected($sort === 'newest')>En yeni</option><option value="popular" @selected($sort === 'popular')>En çok takip edilen</option><option value="oldest" @selected($sort === 'oldest')>En eski</option></select>
            <button class="btn btn-secondary btn-rounded" type="submit">Ara</button>
        </form>
        <div data-ografi-layout-section="users_list" class="repost-people-grid">
            @forelse($users as $person)
                <article class="card card-body repost-person-card">
                    <a href="{{ route('users.show', $person->username) }}" class="repost-person-card__profile"><span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="58" height="58"></span><strong>{{ $person->name }}</strong><span class="meta">@{{ $person->username }}</span></a>
                    <div class="repost-category-card__meta"><span>{{ number_format((int) $person->followers_count) }} takipçi</span><span>{{ number_format((int) $person->followings_count) }} takip</span></div>
                    @auth @if(auth()->id() !== $person->id)<form method="POST" action="{{ route('users.follow', $person->username) }}">@csrf<button class="btn btn-secondary btn-rounded" type="submit">{{ $person->is_followed_by_viewer ? 'Takip ediliyor' : 'Takip et' }}</button></form>@endif @endauth
                </article>
            @empty
                <div class="repost-empty-state">Aramana uygun üye bulunamadı.</div>
            @endforelse
        </div>
        <div class="repost-pagination">{{ $users->links() }}</div>
    </div>
@endsection
