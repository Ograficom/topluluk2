<section class="repost-category-hero" data-ografi-layout-section="category_header">
    @if($category->cover_image_url)
        <div class="repost-category-hero__cover" style="background-image:url('{{ $category->cover_image_url }}')"></div>
    @endif
    <div class="repost-category-hero__identity">
        <span class="repost-category-hero__avatar">@if($category->profile_image_url)<img src="{{ $category->profile_image_url }}" alt="" width="64" height="64">@else{{ mb_substr($category->name, 0, 1) }}@endif</span>
        <div><h1>{{ $category->name }}</h1><p>{{ $category->description }}</p><span class="meta">{{ number_format((int)$category->followers_count) }} üye · {{ number_format((int)$category->posts_count) }} gönderi</span></div>
    </div>
</section>
<nav class="repost-profile-tabs" data-ografi-layout-section="category_tabs" aria-label="Topluluk sekmeleri">
    <a class="is-active" href="{{ route('blog.category', $category->slug) }}">Gönderiler</a>
    @auth
        <form method="POST" action="{{ route('blog.category.join', $category->slug) }}">@csrf<button class="btn btn-secondary btn-sm btn-rounded" type="submit">{{ $category->is_joined_by_viewer ? 'Katıldın' : 'Topluluğa katıl' }}</button></form>
    @endauth
</nav>
<div data-ografi-layout-section="category_content" class="repost-category-description">
    @if($category->description)<p>{{ $category->description }}</p>@endif
</div>
