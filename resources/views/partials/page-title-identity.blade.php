@php
    $pageTitleIdentityText = $title ?? '';
    $pageTitleIdentityTrailing = $trailing ?? '';
@endphp

<div class="page-title-identity">
    <div class="page-title-identity__edge-blur page-title-identity__edge-blur--top" aria-hidden="true"></div>
    <button type="button" class="page-title-identity__nav" data-page-title-back aria-label="Geri git" title="Geri git">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 19l-7-7l7-7m7 7H5"></path>
        </svg>
    </button>
    <span class="page-title-identity__divider" aria-hidden="true"></span>
    <h1 class="page-title-identity__text">{{ $pageTitleIdentityText }}</h1>
    @if ($pageTitleIdentityTrailing !== '')
        {!! $pageTitleIdentityTrailing !!}
    @endif
    <div class="page-title-identity__edge-blur page-title-identity__edge-blur--bottom" aria-hidden="true"></div>
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
