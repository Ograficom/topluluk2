@props(['size' => 14])

<span
    class="company-verify"
    role="img"
    aria-label="Doğrulanmış şirket"
    data-tooltip="Doğrulanmış şirket"
    style="display:inline-flex!important;align-items:center!important;justify-content:center!important;vertical-align:middle!important;line-height:0!important;flex:0 0 ${'{'}$size{'}'}px!important;width:${'{'}$size{'}'}px!important;height:${'{'}$size{'}'}px!important;min-width:${'{'}$size{'}'}px!important;min-height:${'{'}$size{'}'}px!important;max-width:${'{'}$size{'}'}px!important;max-height:${'{'}$size{'}'}px!important;overflow:hidden!important;"
>
    <img
        src="{{ asset('assets/img/icons/company-verify.svg') }}"
        alt=""
        width="{{ $size }}"
        height="{{ $size }}"
        style="display:block!important;width:${'{'}$size{'}'}px!important;height:${'{'}$size{'}'}px!important;min-width:${'{'}$size{'}'}px!important;min-height:${'{'}$size{'}'}px!important;max-width:${'{'}$size{'}'}px!important;max-height:${'{'}$size{'}'}px!important;object-fit:contain!important;margin:0!important;padding:0!important;"
        loading="lazy"
    >
</span>
