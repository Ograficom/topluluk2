@php($layoutSection = $layoutSection ?? '')
@if(in_array($layoutSection, ['discover_ads_top', 'blog_ads_top', 'post_ad_top', 'post_ad_bottom'], true))
    @if($layoutSection === 'discover_ads_top')
        <div data-ografi-layout-section="discover_ads_top" class="repost-ad-slot" aria-label="Reklam alanı">@if(isset($adHtml) && filled($adHtml)){!! $adHtml !!}@endif</div>
    @elseif($layoutSection === 'blog_ads_top')
        <div data-ografi-layout-section="blog_ads_top" class="repost-ad-slot" aria-label="Reklam alanı">@if(isset($adHtml) && filled($adHtml)){!! $adHtml !!}@endif</div>
    @elseif($layoutSection === 'post_ad_top')
        <div data-ografi-layout-section="post_ad_top" class="repost-ad-slot" aria-label="Reklam alanı">@if(isset($adHtml) && filled($adHtml)){!! $adHtml !!}@endif</div>
    @elseif($layoutSection === 'post_ad_bottom')
        <div data-ografi-layout-section="post_ad_bottom" class="repost-ad-slot" aria-label="Reklam alanı">@if(isset($adHtml) && filled($adHtml)){!! $adHtml !!}@endif</div>
    @endif
@endif
