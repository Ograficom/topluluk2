@extends('layouts.repost')

@section('title', 'Mesaj kişileri — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">İLETİŞİM</span><h1>Kişiler</h1><p>Takip ettiğin ve seni takip eden üyeler.</p></div><a href="{{ route('messages.index') }}" class="btn btn-secondary btn-rounded">Mesajlara dön</a></div>
    <section class="card card-body repost-contacts-section">
        <h2>Takip ettiklerin</h2>
        @forelse($followingContacts as $contact)
            @php($person = $contact['user'])
            <div class="repost-contact-row">
                <a href="{{ route('users.show', $person->username) }}" class="user-box"><span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="42" height="42"></span><span class="user-box-content"><strong class="name">{{ $person->name }}</strong><span class="meta">@{{ $person->username }}</span></span></a>
                @if($contact['can_message'])<a href="{{ $contact['thread_url'] }}" class="btn btn-primary btn-sm btn-rounded">{{ $contact['thread_exists'] ? 'Konuşmayı aç' : 'Mesaj gönder' }}</a>@else<span class="meta">Mesaj gönderilemiyor</span>@endif
            </div>
        @empty
            <p class="meta">Henüz takip ettiğin kimse yok.</p>
        @endforelse
    </section>
    <section class="card card-body repost-contacts-section">
        <h2>Seni takip edenler</h2>
        @forelse($followerContacts as $contact)
            @php($person = $contact['user'])
            <div class="repost-contact-row">
                <a href="{{ route('users.show', $person->username) }}" class="user-box"><span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="42" height="42"></span><span class="user-box-content"><strong class="name">{{ $person->name }}</strong><span class="meta">@{{ $person->username }}</span></span></a>
                @if($contact['can_message'])<a href="{{ $contact['thread_url'] }}" class="btn btn-primary btn-sm btn-rounded">{{ $contact['thread_exists'] ? 'Konuşmayı aç' : 'Mesaj gönder' }}</a>@else<span class="meta">Mesaj gönderilemiyor</span>@endif
            </div>
        @empty
            <p class="meta">Henüz takipçin yok.</p>
        @endforelse
    </section>
@endsection
