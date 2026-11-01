@props([
    'name',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'label' => null,
])

<label class="ografi-switch">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked((bool) $checked)
        @disabled((bool) $disabled)
        aria-label="{{ $label ?: \Illuminate\Support\Str::headline($name) }}"
        {{ $attributes }}
    >
    <span class="ografi-switch__track" aria-hidden="true"><span></span></span>
</label>
