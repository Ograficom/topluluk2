@php
    $pageTitleIdentityText = $title ?? '';
@endphp

<div class="page-title-identity">
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
