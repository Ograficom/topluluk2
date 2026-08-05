@php
    $pageTitleIdentityText = $title ?? '';
@endphp

<div class="page-title-identity">
    <a href="{{ route('home') }}" class="page-title-identity__nav" aria-label="Ana sayfaya git" title="Ana sayfa">
        <svg class="nav-home-icon" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M0 0h24v24H0z" fill="none"></path>
            <g fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M2 12.204c0-2.289 0-3.433.52-4.381c.518-.949 1.467-1.537 3.364-2.715l2-1.241C9.889 2.622 10.892 2 12 2s2.11.622 4.116 1.867l2 1.241c1.897 1.178 2.846 1.766 3.365 2.715S22 9.915 22 12.203v1.522c0 3.9 0 5.851-1.172 7.063S17.771 22 14 22h-4c-3.771 0-5.657 0-6.828-1.212S2 17.626 2 13.725z"></path>
                <path stroke-linecap="round" d="M12 15v3"></path>
            </g>
        </svg>
    </a>
    <button type="button" class="page-title-identity__nav" data-page-title-back aria-label="Geri git" title="Geri git">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M0 0h16v16H0z" fill="none"></path>
            <path fill="currentColor" fill-rule="evenodd" d="m5.293 8l3.854 3.854l.707-.707L6.707 8l3.147-3.146l-.707-.708z" clip-rule="evenodd"></path>
        </svg>
    </button>
    <span class="page-title-identity__divider" aria-hidden="true"></span>
    <h1 class="page-title-identity__text">{{ $pageTitleIdentityText }}</h1>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-page-title-back]').forEach((button) => {
                    button.addEventListener('click', () => {
                        let sameSiteReferrer = false;
                        try {
                            sameSiteReferrer = document.referrer !== '' && new URL(document.referrer).host === window.location.host;
                        } catch (error) {
                            sameSiteReferrer = false;
                        }

                        if (sameSiteReferrer && window.history.length > 1) {
                            window.history.back();
                        } else {
                            window.location.href = @json(route('home'));
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
