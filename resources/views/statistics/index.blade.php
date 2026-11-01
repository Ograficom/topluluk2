@extends('layouts.app')

@section('title', 'İstatistikler')

@section('content')
    <div
        id="stats-data"
        hidden
        data-news-num="{{ $totalPosts }}"
        data-news-allow="{{ $publishedPosts }}"
        data-news-main="{{ $publishedPosts }}"
        data-news-moder="{{ $postsInModeration }}"
        data-news-day="{{ $postsByPeriod['day'] }}"
        data-news-week="{{ $postsByPeriod['week'] }}"
        data-news-month="{{ $postsByPeriod['month'] }}"
        data-comm-num="{{ $totalComments }}"
        data-comm-day="{{ $commentsByPeriod['day'] }}"
        data-comm-week="{{ $commentsByPeriod['week'] }}"
        data-comm-month="{{ $commentsByPeriod['month'] }}"
        data-user-num="{{ $totalUsers }}"
        data-user-banned="0"
        data-user-day="{{ $usersByPeriod['day'] }}"
        data-user-week="{{ $usersByPeriod['week'] }}"
        data-user-month="{{ $usersByPeriod['month'] }}"
        data-users-online="0"
    ></div>

    <div class="stats__counts">
        <div class="stats__count-card stats__count-card--posts card card-body">
            <div class="stats__count-head">
                <span class="stats__count-label small color-gray">Yazılar</span>
                <span class="stats__count-icon" aria-hidden="true"><x-repost-icon name="feed" size="18" /></span>
            </div>
            <span class="stats__count-num">{{ number_format($totalPosts) }}</span>
        </div>
        <div class="stats__count-card stats__count-card--comments card card-body">
            <div class="stats__count-head">
                <span class="stats__count-label small color-gray">Yorumlar</span>
                <span class="stats__count-icon" aria-hidden="true"><x-repost-icon name="comment" size="18" /></span>
            </div>
            <span class="stats__count-num">{{ number_format($totalComments) }}</span>
        </div>
        <div class="stats__count-card stats__count-card--users card card-body">
            <div class="stats__count-head">
                <span class="stats__count-label small color-gray">Üyeler</span>
                <span class="stats__count-icon" aria-hidden="true"><x-repost-icon name="user" size="18" /></span>
            </div>
            <div class="stats__count-footer">
                <span class="stats__count-num">{{ number_format($totalUsers) }}</span>
            </div>
        </div>
    </div>

    <section class="stats__chart card card-body">
        <header class="stats__chart-head">
            <div>
                <h1 class="h3">İstatistikler</h1>
                <p class="small color-gray">Yayın, üye ve yorumların döneme göre dağılımı</p>
            </div>
        </header>
        <div id="statsTimelineChart" class="stats__chart-canvas stats__chart-canvas--wide"></div>
    </section>

    <section class="stats__chart card card-body">
        <header class="stats__chart-head">
            <div>
                <h2 class="h3">Yayınlar</h2>
                <p class="small color-gray">Yayınların genel durumu</p>
            </div>
        </header>
        <div id="statsPostsChart" class="stats__chart-canvas"></div>
    </section>

    <div class="stats__chart-grid">
        <section class="stats__chart card card-body">
            <header class="stats__chart-head">
                <div>
                    <h2 class="h3">Üyeler</h2>
                    <p class="small color-gray">Kayıt sayıları dönemlere göre</p>
                </div>
            </header>
            <div id="statsUsersChart" class="stats__chart-canvas stats__chart-canvas--compact"></div>
        </section>
        <section class="stats__chart card card-body">
            <header class="stats__chart-head">
                <div>
                    <h2 class="h3">Yorumlar</h2>
                    <p class="small color-gray">Yorum etkinliği dönemlere göre</p>
                </div>
            </header>
            <div id="statsCommentsChart" class="stats__chart-canvas stats__chart-canvas--compact"></div>
        </section>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/stats.js') }}"></script>
@endpush
