@props(['name', 'size' => 20, 'class' => ''])

<svg {{ $attributes->merge(['class' => $class]) }} width="{{ $size }}" height="{{ $size }}" aria-hidden="true" focusable="false">
    <use href="{{ asset('assets/img/icons/sprite.svg') . '?v=20261112-toolbar-icons#' . $name }}"></use>
</svg>
