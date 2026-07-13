@props([
    'count' => 0,
    'icon' => null,
    'label' => null,
    'class' => '',
    'triggerClass' => null,
    // Options should come from reaction_types (id + emoji/icon/url)
    'gifs' => [],
    'isAdd' => false,
])

@php
    $addSvg = '<iconify-icon icon="lucide:smile-plus" class="text-[18px]"></iconify-icon>';
    $uid = 'rx_' . substr(md5(($label ?? '') . ($count ?? '') . uniqid('', true)), 0, 8);
    $rawIcon = $isAdd ? $addSvg : ($icon ?? null);
    $isAuth = auth()->check();

    $startsWithUrl = function ($val) {
        if (!is_string($val)) return false;
        return str_starts_with($val, 'http://')
            || str_starts_with($val, 'https://')
            || str_starts_with($val, '/storage')
            || str_starts_with($val, '/uploads')
            || str_starts_with($val, '/');
    };

    $renderIcon = function ($value, $labelText = null, $size = 'h-9 w-9 rounded-full') use ($startsWithUrl) {
        if (!isset($value)) {
            return e('?');
        }

        $isString = is_string($value);
        $raw = $isString ? trim($value) : $value;
        $hasHtml = $isString && preg_match('/<\s*(img|svg|iconify-icon)/i', $raw);
        $resolved = $raw;
        if (
            $isString
            && !$startsWithUrl($resolved)
            && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $resolved)
        ) {
            $resolved = str_starts_with($resolved, 'storage/')
                ? url('/' . ltrim($resolved, '/'))
                : asset('storage/' . ltrim($resolved, '/'));
        }

        $isImgExt = $isString && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $resolved);
        $hasUrl = $startsWithUrl($resolved);

        if ($hasHtml) {
            return $raw;
        }

        if ($isImgExt || $hasUrl) {
            return '<img src="'.e($resolved).'" alt="'.e($labelText ?? 'reaction').'" class="'.$size.' object-cover">';
        }

        return e($raw ?: '?');
    };

$optionsSource = collect($gifs ?? []);

    if ($optionsSource->isEmpty()) {
        $optionsSource = \App\Models\ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url'])
            ->map(fn ($type) => [
                'id' => $type->id,
                'short_code' => $type->short_code,
                'emoji' => $type->emoji,
                'gif_url' => $type->gif_url,
                'label' => $type->label,
            ]);
    }

    $options = $optionsSource
        ->take(12)
        ->map(function ($item) use ($renderIcon) {
            $id = null;
            $label = null;
            $iconValue = null;
            $shortCode = null;

            if (is_array($item)) {
                $id = $item['id'] ?? null;
                $shortCode = $item['short_code'] ?? null;
                $label = $item['label'] ?? $item['title'] ?? null;
                $iconValue = $item['icon'] ?? $item['emoji'] ?? $item['gif_url'] ?? $item['short_code'] ?? null;
            } elseif (is_object($item)) {
                $id = $item->id ?? null;
                $shortCode = $item->short_code ?? null;
                $label = $item->label ?? $item->title ?? $item->name ?? null;
                $iconValue = $item->icon ?? $item->emoji ?? $item->gif_url ?? $item->short_code ?? null;
            } else {
                $iconValue = $item;
            }

            if (!$iconValue && $label) {
                $iconValue = mb_substr($label, 0, 1);
            }

            return [
                'id' => $id,
                'short_code' => $shortCode ?? null,
                'icon' => $renderIcon($iconValue, $label),
                'label' => $label,
            ];
        })
        ->filter(fn($opt) => !empty($opt['icon']))
        ->values();

    $fallbackIcon = $rawIcon ?: ($options->first()['icon'] ?? $addSvg);
    $triggerIcon = $renderIcon($fallbackIcon, $label, 'h-5 w-5 rounded-full');
    if ($isAdd) {
        $triggerIcon = isset($icon)
            ? $renderIcon($icon, $label, 'h-5 w-5 rounded-full')
            : $addSvg;
    }
    $defaultTriggerClass = $isAdd
        ? 'rx-add-trigger'
        : 'rx-summary-trigger';
    $triggerClass = $triggerClass ?: $defaultTriggerClass;
@endphp

<div class="relative inline-block {{ $class }}" data-rx-wrapper="{{ $uid }}" @if(!$isAuth) data-rx-guest="1" data-login-url="{{ route('login') }}" @endif>
    @if($isAuth)
        <button
            type="button"
            class="{{ $triggerClass }}"
            data-rx-trigger="{{ $uid }}"
            aria-haspopup="dialog"
            aria-expanded="false"
            aria-label="{{ $label ? $label . ' tepkisi' : 'Tepki sec' }}"
        >
            <span class="text-base leading-none">{!! $triggerIcon !!}</span>
            @if($count !== '')
                <span class="text-[13px] font-semibold">{{ number_format((int) $count) }}</span>
            @endif
            @if($label)
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
            @endif
        </button>
    @else
        @if($isAdd)
            <button
                type="button"
                class="{{ $triggerClass }}"
                data-rx-trigger="{{ $uid }}"
                aria-haspopup="dialog"
                aria-expanded="false"
                aria-label="Tepki sec"
            >
                <span class="text-base leading-none">{!! $triggerIcon !!}</span>
            </button>
        @else
            {{-- Clickable button for guests - redirects to login --}}
            <button
                type="button"
                class="{{ $triggerClass }} cursor-pointer"
                onclick="window.location.href='{{ route('login') }}'"
                aria-label="{{ $label ? $label . ' tepkisi' : 'Tepki sec' }}"
                title="Giris yapin"
            >
                <span class="text-base leading-none">{!! $triggerIcon !!}</span>
                @if($count !== '')
                    <span class="text-[13px] font-semibold">{{ number_format((int) $count) }}</span>
                @endif
            </button>
        @endif
    @endif

    {{-- Reaction dropdown panel --}}
    @if($isAuth || $isAdd)
        <div
            class="rx-panel fixed z-50 hidden"
            data-rx-panel="{{ $uid }}"
            role="dialog"
            style="max-width: calc(100vw - 24px);"
        >
            <div class="rx-panel__title">
                Reactions
            </div>
            @if($options->isNotEmpty())
                <div class="rx-panel__options">
                    @foreach($options as $option)
                        <button
                            type="button"
                            class="rx-panel__option"
                            data-rx-option="{{ $uid }}"
                            @if(!empty($option['id'])) data-rx-option-id="{{ $option['id'] }}" @endif
                            @if(!empty($option['short_code'])) data-rx-option-code="{{ $option['short_code'] }}" @endif
                            aria-label="{{ $option['label'] ?? $option['short_code'] ?? 'Tepki' }}"
                        >
                            <span class="rx-panel__option-icon">{!! $option['icon'] !!}</span>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="rx-panel__empty">
                    Tepki bulunamad&#305;.
                </div>
            @endif
        </div>
    @endif
</div>

@once
<style>
    .rx-panel {
        width: 228px;
        border: 1px solid rgba(226, 232, 240, 0.96);
        border-radius: 16px;
        background: #ffffff;
        padding: 12px 14px 14px;
        box-shadow: 0 18px 46px rgba(15, 23, 42, 0.14);
    }

    .rx-panel__title {
        margin-bottom: 12px;
        font-size: 0.95rem;
        line-height: 1.2;
        font-weight: 500;
        color: #6b7280;
    }

    .rx-panel__options {
        display: grid;
        max-height: 240px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px 14px;
        overflow-y: auto;
    }

    .rx-panel__option {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        margin: 0 auto;
        border-radius: 999px;
        background: transparent;
        color: #111827;
        transition: transform .16s ease, background-color .16s ease;
    }

    .rx-panel__option:hover,
    .rx-panel__option:focus,
    .rx-panel__option:focus-visible {
        background: rgba(241, 245, 249, 0.95);
        transform: scale(1.06);
        outline: none;
    }

    .rx-panel__option-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        line-height: 1;
    }

    .rx-panel__option-icon img,
    .rx-panel__option-icon iconify-icon,
    .rx-panel__option-icon svg {
        display: block;
        width: 36px;
        height: 36px;
        border-radius: 999px;
        object-fit: cover;
    }

    .rx-panel__empty {
        border-radius: 14px;
        background: #f8fafc;
        padding: 16px 12px;
        text-align: center;
        font-size: 0.78rem;
        color: #6b7280;
    }

    .rx-summary-trigger {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        border-radius: 999px;
        border: 1px solid rgba(226, 232, 240, 0.96);
        background: #f1f5f9;
        padding: 6px 12px;
        color: #111827;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        transition: background-color .2s ease, color .2s ease, border-color .2s ease;
    }

    .rx-summary-trigger:hover {
        background: #e5e7eb;
        border-color: rgba(203, 213, 225, 0.96);
    }

    .rx-add-trigger {
        display: inline-flex;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid rgba(226, 232, 240, 0.96);
        background: #f1f5f9;
        color: #111827;
        transition: background-color .2s ease, color .2s ease, border-color .2s ease;
    }

    .rx-add-trigger:hover {
        background: #e5e7eb;
        color: #111827;
        border-color: rgba(203, 213, 225, 0.96);
    }

    .rx-summary-trigger .text-base,
    .rx-add-trigger .text-base {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .rx-summary-trigger .text-base img,
    .rx-summary-trigger .text-base iconify-icon,
    .rx-summary-trigger .text-base svg,
    .rx-add-trigger .text-base img,
    .rx-add-trigger .text-base iconify-icon,
    .rx-add-trigger .text-base svg {
        width: 18px;
        height: 18px;
        display: block;
        border-radius: 999px;
        object-fit: cover;
    }

    html.dark .rx-summary-trigger,
    html.dark .rx-add-trigger,
    html.dark .rx-panel {
        border-color: rgba(71, 85, 105, 0.88);
        background: rgba(15, 23, 42, 0.96);
        color: #e5e7eb;
    }

    html.dark .rx-summary-trigger:hover,
    html.dark .rx-add-trigger:hover {
        background: rgba(71, 85, 105, 0.86);
        color: #f8fafc;
    }

    html.dark .rx-panel__title,
    html.dark .rx-panel__empty {
        color: #cbd5e1;
    }

    html.dark .rx-panel__empty {
        background: rgba(30, 41, 59, 0.82);
    }

    html.dark .rx-panel__option:hover,
    html.dark .rx-panel__option:focus,
    html.dark .rx-panel__option:focus-visible {
        background: rgba(30, 41, 59, 0.92);
    }
</style>
@endonce

@once
<script>
(function () {
    const hideAll = () => {
        document.querySelectorAll('[data-rx-panel]').forEach(panel => {
            panel.classList.add('hidden');
            panel.style.top = '';
            panel.style.left = '';
        });
        document.querySelectorAll('[data-rx-trigger]').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
    };

    const positionPanel = (panel, trigger) => {
        // Remove previous position styles
        panel.style.top = '';
        panel.style.left = '';
        panel.style.right = '';
        
        // Temporarily show to measure
        const prevVisibility = panel.style.visibility;
        panel.classList.remove('hidden');
        panel.style.visibility = 'hidden';

        const panelRect = panel.getBoundingClientRect();
        const triggerRect = trigger.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Calculate available space
        const spaceBelow = viewportHeight - triggerRect.bottom - 16;
        const spaceAbove = triggerRect.top - 16;
        
        // Width of panel
        const panelWidth = panelRect.width || 224; // w-56 = 224px
        
        // Horizontal positioning
        let leftPos = triggerRect.left;
        let topPos;
        
        // Check if panel would overflow on right
        if (leftPos + panelWidth > viewportWidth - 12) {
            // Right-align instead
            panel.style.left = 'auto';
            panel.style.right = Math.max(12, viewportWidth - triggerRect.right) + 'px';
        } else {
            panel.style.left = leftPos + 'px';
        }
        
        // Vertical positioning
        if (spaceBelow >= panelRect.height || spaceBelow > spaceAbove) {
            // Open below
            topPos = triggerRect.bottom + 8;
        } else {
            // Open above
            topPos = triggerRect.top - panelRect.height - 8;
        }
        
        panel.style.top = Math.max(12, topPos) + 'px';
        
        panel.style.visibility = prevVisibility || '';
    };

    const openPanel = (uid) => {
        const panel = document.querySelector(`[data-rx-panel="${uid}"]`);
        const btn = document.querySelector(`[data-rx-trigger="${uid}"]`);
        if (!panel || !btn) return;
        
        hideAll();
        positionPanel(panel, btn);
        panel.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
    };

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-rx-trigger]');
        const closeBtn = e.target.closest('[data-rx-close]');
        const optionBtn = e.target.closest('[data-rx-option]');
        const wrapper = e.target.closest('[data-rx-wrapper]');

        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            openPanel(btn.getAttribute('data-rx-trigger'));
            return;
        }

        if (closeBtn) {
            e.preventDefault();
            hideAll();
            return;
        }

        if (optionBtn) {
            e.preventDefault();
            const wrapperEl = optionBtn.closest('[data-rx-wrapper]');
            const loginUrl = wrapperEl?.getAttribute('data-login-url');
            if (wrapperEl?.hasAttribute('data-rx-guest') && loginUrl) {
                window.location.href = loginUrl;
                return;
            }
            const target = wrapperEl || window;
            target.dispatchEvent(new CustomEvent('reaction:selected', {
                detail: {
                    uid: optionBtn.getAttribute('data-rx-option'),
                    reaction_type_id: optionBtn.getAttribute('data-rx-option-id') ? Number(optionBtn.getAttribute('data-rx-option-id')) : null,
                    short_code: optionBtn.getAttribute('data-rx-option-code') || null,
                    icon_html: optionBtn.querySelector('span')?.innerHTML || optionBtn.innerHTML || null,
                },
                bubbles: true,
            }));
            hideAll();
            return;
        }

        if (!wrapper) hideAll();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideAll();
    });

    // Reposition on scroll
    window.addEventListener('scroll', () => {
        document.querySelectorAll('[data-rx-trigger][aria-expanded="true"]').forEach(btn => {
            const uid = btn.getAttribute('data-rx-trigger');
            const panel = document.querySelector(`[data-rx-panel="${uid}"]`);
            if (panel && !panel.classList.contains('hidden')) {
                positionPanel(panel, btn);
            }
        });
    }, true);
})();
</script>
@endonce










