@props(['size' => 14])

<span
    class="company-verify"
    role="img"
    aria-label="Doğrulanmış şirket"
    data-tooltip="Doğrulanmış şirket"
    style="display:inline-flex;align-items:center;vertical-align:middle;line-height:1;flex:0 0 auto;"
>
    <img
        src="{{ asset('assets/img/icons/company-verify.svg') }}"
        alt=""
        width="{{ $size }}"
        height="{{ $size }}"
        style="display:block;width:{{ $size }}px;height:{{ $size }}px;max-width:none;object-fit:contain;"
        loading="lazy"
    >
</span>
