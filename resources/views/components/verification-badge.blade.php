@props([
    'user' => null,
    'size' => 'md',
    'label' => 'Dogrulanmis',
])

@php
    use Illuminate\Support\Str;

    $isVerified = (bool) ($user?->is_verified ?? false);
    $profileType = Str::lower(trim((string) ($user?->profile_type ?? 'person')));
    $badgeType = Str::lower(trim((string) ($user?->verification_badge ?? '')));
    $badgeSvg = (string) ($user?->verification_badge_svg ?? '');

    if ($profileType === 'organization' && $isVerified) {
        $badgeType = 'gray-check';
        $badgeSvg = '';
    }

    $hasCustom = trim($badgeSvg) !== '';
    $shouldRender = $isVerified || ($badgeType !== '' && $badgeType !== 'none') || $hasCustom;

    $sizeMap = [
        'xs' => 'h-3.5 w-3.5',
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-6 w-6',
    ];
    $iconSizeClass = $sizeMap[$size] ?? $sizeMap['md'];

    $fillColor = match ($badgeType) {
        'gold-check' => '#d4a017',
        'gray-check' => '#7b7b7b',
        default => '#0073ff',
    };

    $customSvgInline = null;
    $customSvgUrl = null;
    if ($badgeType === 'custom' && $hasCustom) {
        if (Str::contains(Str::lower($badgeSvg), '<svg')) {
            $customSvgInline = $badgeSvg;
        } else {
            $rawPath = trim($badgeSvg);
            if (Str::startsWith($rawPath, ['http://', 'https://', '//', '/storage/', 'storage/'])) {
                $customSvgUrl = Str::startsWith($rawPath, 'storage/')
                    ? url('/storage/' . Str::after($rawPath, 'storage/'))
                    : $rawPath;
            } else {
                $customSvgUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($rawPath);
            }
        }
    }
@endphp

@if($shouldRender)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center']) }} role="img" aria-label="{{ $label }}">
        @if($customSvgInline)
            <span class="{{ $iconSizeClass }}">{!! $customSvgInline !!}</span>
        @elseif($customSvgUrl)
            <img src="{{ $customSvgUrl }}" alt="{{ $label }}" class="{{ $iconSizeClass }} object-contain" loading="lazy" decoding="async">
        @else
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="{{ $iconSizeClass }}" aria-hidden="true">
                <circle cx="12" cy="12" r="10" fill="{{ $fillColor }}"/>
                <path d="M9.8 12.7L11.2 14.1L14.9 10.4" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        @endif
    </span>
@endif
