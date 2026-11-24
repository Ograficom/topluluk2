@extends('layouts.app')

@section('title', 'Topluluk görevleri ve liderlik tablosu')
@section('description', 'OgraFi topluluk görevlerini tamamla, puan kazan ve liderlik tablosunda yüksel.')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/tasks.css') }}?v=20261124-tasks-2">
@endpush

@section('content')
<article class="tasks-page">
    <header class="tasks-page__hero card card-body">
        <div>

            <h1 class="h3">Görevler ve liderlik</h1>
            <p class="color-gray">Görevleri tamamla, puan kazan ve topluluğun en aktif üyeleri arasına katıl.</p>
        </div>
        @auth
            <div class="tasks-page__score">
                <span>Toplam puanın</span>
                <strong>{{ number_format($myScore) }}</strong>
                <small>{{ $myBadge['name'] ?? 'Yeni Üye' }}</small>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-md">Giriş yap</a>
        @endauth
    </header>

    <div class="tasks-page__grid">
        <section class="card card-body tasks-panel">
            <header class="tasks-panel__header">
                <div>
                    <h2 class="h5">Aktif görevler</h2>
                    <p class="small color-gray">Tamamladığın görevlerin ödülleri otomatik hesabına eklenir.</p>
                </div>
                <span class="tasks-count">{{ $tasks->count() }}</span>
            </header>

            @forelse ($tasks as $item)
                @php
                    $task = $item['task'];
                    $badge = filled($task['reward_badge'] ?? null)
                        ? collect(\App\Models\ReputationSetting::current()->reward_badges ?? [])->firstWhere('name', $task['reward_badge'])
                        : null;
                @endphp
                <div class="task-card {{ $item['completed'] ? 'is-completed' : '' }}">
                    <div class="task-card__top">
                        <div class="task-card__icon">
                            @if ($badge && filled($badge['image'] ?? null))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($badge['image']) }}" alt="">
                            @else
                                <x-repost-icon name="{{ ($task['activity'] ?? 'comments') === 'posts' ? 'file-text' : 'message-circle' }}" size="22" />
                            @endif
                        </div>
                        <div class="task-card__main">
                            <h3>{{ $task['title'] }}</h3>
                            @if (filled($task['description'] ?? null))
                                <p>{{ $task['description'] }}</p>
                            @else
                                <p>{{ match ($task['activity'] ?? 'comments') { 'posts' => 'Yazı yayınla', 'event' => 'Yönetici onaylı etkinlik', default => 'Yorum yap' } }} · {{ $item['target'] }} adet</p>
                            @endif
                        </div>
                        @if ($item['completed'])
                            <span class="task-status task-status--done">Tamamlandı</span>
                        @else
                            <span class="task-reward">+{{ number_format((int) ($task['reward_points'] ?? 0)) }} puan</span>
                        @endif
                    </div>
                    <div class="task-progress" role="progressbar" aria-valuenow="{{ $item['progress'] }}" aria-valuemin="0" aria-valuemax="{{ $item['target'] }}">
                        <span style="width: {{ $item['percent'] }}%"></span>
                    </div>
                    @if (filled($task['ends_at'] ?? null))
                        <p class="small color-gray">Bitiş {{ \Illuminate\Support\Carbon::parse($task['ends_at'])->format('d.m.Y H:i') }}</p>
                    @endif
                    <div class="task-card__footer">
                        <span>{{ $item['progress'] }} / {{ $item['target'] }} {{ match ($task['activity'] ?? 'comments') { 'posts' => 'yazı', 'event' => 'onay', default => 'yorum' } }}</span>
                        @if ($badge)
                            <span class="task-card__badge">Rozet: {{ $badge['name'] }}</span>
                        @endif
                    </div>
                    @guest
                        <small class="task-login-hint"><a href="{{ route('login') }}">Giriş yap</a> ve görev ilerlemeni takip et.</small>
                    @endguest
                </div>
            @empty
                <div class="tasks-empty">
                    <x-repost-icon name="clipboard-list" size="30" />
                    <strong>Henüz aktif görev yok</strong>
                    <span>Yeni görevler burada yayınlandığında görebileceksin.</span>
                </div>
            @endforelse
        </section>

        <aside class="card card-body leaderboard-panel">
            <header class="tasks-panel__header">
                <div>
                    <h2 class="h5">Liderlik tablosu</h2>
                    <p class="small color-gray">Seçilen dönemde kazanılan net puana göre</p>
                </div>
                <x-repost-icon name="trophy" size="22" />
            </header>
            <nav class="tabs-nav" aria-label="Sıralama dönemi">
                @foreach (['all' => 'Tüm zamanlar', 'month' => 'Bu ay', 'week' => 'Bu hafta'] as $value => $label)
                    <a href="{{ route('tasks.index', ['period' => $value]) }}" class="tabs-nav__button {{ $period === $value ? 'is-active' : '' }}" @if ($period === $value) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            <ol class="leaderboard-list">
                @forelse ($leaderboard as $member)
                    <li class="leaderboard-item">
                        <span class="leaderboard-rank">{{ $loop->iteration }}</span>
                        <a href="{{ route('profiles.show', $member->username ?: $member->id) }}" class="leaderboard-user">
                            <img src="{{ $member->avatarUrl() }}" alt="" loading="lazy">
                            <span>
                                <strong>{{ $member->displayName() }}</strong>
                                <small>{{ '@'.$member->publicUsername() }}</small>
                            </span>
                        </a>
                        <strong class="leaderboard-points">{{ number_format((int) ($member->reputation_score ?? 0)) }}</strong>
                    </li>
                @empty
                    <li class="color-gray small">Henüz sıralama için yeterli veri yok.</li>
                @endforelse
            </ol>
            <a href="{{ route('members.index') }}" class="link-accent">Tüm üyeleri görüntüle</a>
        </aside>
    </div>

    @auth
        @if ($myCompletions->isNotEmpty())
            <section class="card card-body task-history">
                <h2 class="h5">Tamamladığım görevler</h2>
                <div class="task-history__list">
                    @foreach ($myCompletions as $completion)
                        <div class="task-history__item">
                            <span class="task-history__check"><x-repost-icon name="check" size="16" /></span>
                            <span><strong>{{ $completion->task_name }}</strong><small>{{ $completion->completed_at?->format('d.m.Y H:i') }}</small></span>
                            <strong>+{{ number_format($completion->reward_points) }} puan</strong>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endauth
</article>
@endsection
