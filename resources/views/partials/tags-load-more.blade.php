@if(isset($tags) && is_object($tags) && method_exists($tags, 'hasMorePages') && $tags->hasMorePages())
    <div class="ografi-feed-loadmore" data-tags-load-more>
        <a
            href="{{ $tags->nextPageUrl() }}"
            class="ografi-feed-loadmore__button ografi-feed-loadmore__button--icon"
            rel="next"
            data-tags-load-next
            aria-label="{{ __('site.tags_page.load_more') }}"
            title="{{ __('site.tags_page.load_more') }}"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4"/>
            </svg>
        </a>
    </div>
@endif
