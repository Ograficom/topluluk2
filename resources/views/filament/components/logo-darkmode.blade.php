@php
    $logoDark = settings()->group('general')->get('site_logo_dark');
    $logoDark = !empty($logoDark) ? Storage::disk(getCurrentDisk())->url($logoDark) : asset('/images/logo-dark.svg');
@endphp
<div>
    <img src="{{ $logoDark }}" class="h-7" alt="logo" />
</div>
