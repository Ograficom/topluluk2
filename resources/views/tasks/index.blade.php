@extends('layouts.app')

@section('title', 'Topluluk görevleri ve liderlik tablosu')
@section('description', 'OgraFi topluluk görevlerini tamamla, puan kazan ve liderlik tablosunda yüksel.')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/tasks.css') }}?v=20261125-participations">
@endpush

@section('content')
<article class="tasks-page">
    <div class="tasks-page__grid">
        <section class="card card-body tasks-panel">
            <header class="tasks-panel__header">
                <div>
                    <h1 class="h3">Aktif görevler</h1>
                    <p class="small color-gray">Tamamladığın görevlerin ödülleri otomatik hesabına eklenir.</p>
                </div>
                <span class="tasks-count">{{ $tasks->count() }}</span>
            </header>

            @if (session('task_status'))
                <p class="task-participation-message" role="status">{{ session('task_status') }}</p>
            @endif
            @if ($errors->has('external_username'))
                <p class="task-participation-message" role="alert">{{ $errors->first('external_username') }}</p>
            @endif
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
                                <p>{{ \App\Services\CommunityTaskService::ACTIVITIES[$task['activity'] ?? 'comments'] ?? 'Görev' }} · {{ $item['target'] }} adet</p>
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
                        <span>{{ $item['progress'] }} / {{ $item['target'] }} {{ match ($task['activity'] ?? 'comments') { 'posts' => 'yazı', 'comments' => 'yorum', 'follow_members' => 'üye', 'login_streak' => 'gün', default => 'tamamlanma' } }}</span>
                        @if ($badge)
                            <span class="task-card__badge">
                                @if (filled($badge['image'] ?? null))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($badge['image']) }}" alt="" width="32" height="32" loading="lazy">
                                @else
                                    <x-repost-icon :name="$badge['icon'] ?? 'trophy'" size="24" />
                                @endif
                                <span>Kazanılacak rozet <strong>{{ $badge['name'] }}</strong></span>
                            </span>
                        @endif
                    </div>
                    @if (filled($task['action_url'] ?? null) && in_array(parse_url($task['action_url'], PHP_URL_SCHEME), ['http', 'https'], true))
                        <a class="btn btn-primary btn-sm" href="{{ $task['action_url'] }}" target="_blank" rel="noopener noreferrer">Görev bağlantısını aç</a>
                    @endif
                    @if (in_array($task['activity'] ?? '', ['event', 'social_follow'], true))
                        @auth
                            @if ($item['completed'])
                                <p class="small color-gray">Görevin onaylandı ve ödülün hesabına eklendi.</p>
                            @elseif ($item['participation'])
                                <p class="task-participation-message" role="status">
                                    {{ match ($item['participation']->status) { 'approved' => 'Başvurun onaylandı.', 'rejected' => 'Başvurun reddedildi.', default => 'Başvurun yönetici onayı bekliyor.' } }}
                                </p>
                            @elseif (filled($task['key'] ?? null)
                                && (! filled($task['starts_at'] ?? null) || now()->gte($task['starts_at']))
                                && (! filled($task['ends_at'] ?? null) || now()->lte($task['ends_at'])))
                                <form action="{{ route('tasks.submit', ['taskKey' => $task['key']]) }}" method="POST" class="task-participation-form">
                                    @csrf
                                    @if (($task['activity'] ?? '') === 'social_follow')
                                        <label for="task-social-{{ $loop->index }}">Sosyal medya kullanıcı adın</label>
                                        <input id="task-social-{{ $loop->index }}" name="external_username" maxlength="100" placeholder="@kullaniciadi" required>
                                    @endif
                                    <button type="submit" class="btn btn-primary btn-sm">Görevi tamamladım</button>
                                </form>
                                <p class="small color-gray">Yönetici kontrolünden sonra ödülün verilir.</p>
                            @else
                                <p class="small color-gray">Bu görev şu anda başvuru almıyor.</p>
                            @endif
                        @else
                            <p class="small color-gray">Görevi tamamladıktan sonra giriş yaparak başvuru gönder.</p>
                        @endauth
                    @endif
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
