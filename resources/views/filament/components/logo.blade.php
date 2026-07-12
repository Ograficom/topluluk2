@php
    $logo = settings()->group('general')->get('site_logo');
    $logo = !empty($logo) ? Storage::disk(getCurrentDisk())->url($logo) : asset('/images/logo.svg');
@endphp
<div>
    <img src="{{ $logo }}" class="h-7" alt="logo" />
</div>
