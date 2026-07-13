@php
    use Illuminate\Support\Facades\Storage;

    $page = $page ?? null;
    $sections = $page?->sections ?? [];

    $radiusMap = [
        'none' => 'rounded-none',
        'sm' => 'rounded-md',
        'md' => 'rounded-lg',
        'lg' => 'rounded-xl',
        'xl' => 'rounded-2xl',
    ];
    $shadowMap = [
        'none' => '',
        'sm' => 'shadow-sm',
        'md' => 'shadow',
        'lg' => 'shadow-lg',
    ];
    $paddingMap = [
        'sm' => 'p-3',
        'md' => 'p-4',
        'lg' => 'p-6',
        'xl' => 'p-8',
    ];
@endphp

@if(!empty($sections))
    <div class="space-y-6">
        @foreach($sections as $section)
            @php
                $type = data_get($section, 'type', 'text');
                $id = data_get($section, 'anchor');
                $bg = data_get($section, 'bg_color');
                $color = data_get($section, 'text_color');
                $radius = $radiusMap[data_get($section, 'radius', 'lg')] ?? $radiusMap['lg'];
                $shadow = $shadowMap[data_get($section, 'shadow', 'none')] ?? '';
                $padding = $paddingMap[data_get($section, 'padding', 'lg')] ?? $paddingMap['lg'];
                $full = data_get($section, 'full_width', false);
                $style = trim(($bg ? "background-color: {$bg};" : '') . ($color ? "color: {$color};" : ''));
            @endphp

            <section id="{{ $id }}" class="{{ $full ? '' : 'mx-auto max-w-7xl px-4' }}">
                <div class="community-card {{ $radius }} {{ $shadow }} {{ $padding }}" @if($style) style="{{ $style }}" @endif>
                    @switch($type)
                        @case('hero')
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="space-y-3">
                                    <h2 class="text-2xl font-semibold md:text-3xl">{{ data_get($section, 'heading') }}</h2>
                                    <p class="text-sm text-slate-600">{{ data_get($section, 'subheading') }}</p>
                                    @if(data_get($section, 'button_text'))
                                        <a href="{{ data_get($section, 'button_url', '#') }}"
                                           class="community-btn">{{ data_get($section, 'button_text') }}</a>
                                    @endif
                                </div>
                                @if(data_get($section, 'image'))
                                    @php
                                        $image = data_get($section, 'image');
                                        $imageUrl = str_starts_with($image, ['http://', 'https://', '//'])
                                            ? $image
                                            : Storage::disk('public')->url($image);
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ data_get($section, 'image_alt', '') }}"
                                         class="h-40 w-full max-w-sm rounded-xl object-cover md:h-48" />
                                @endif
                            </div>
                            @break

                        @case('text')
                            <div class="space-y-2">
                                @if(data_get($section, 'heading'))
                                    <h3 class="text-lg font-semibold">{{ data_get($section, 'heading') }}</h3>
                                @endif
                                @if(data_get($section, 'body'))
                                    <div class="text-sm text-slate-600">{!! nl2br(e(data_get($section, 'body'))) !!}</div>
                                @endif
                            </div>
                            @break

                        @case('cards')
                            @php
                                $cols = (int) data_get($section, 'columns', 3);
                                $grid = $cols === 4 ? 'md:grid-cols-4' : ($cols === 2 ? 'md:grid-cols-2' : 'md:grid-cols-3');
                            @endphp
                            @if(data_get($section, 'heading'))
                                <h3 class="mb-4 text-lg font-semibold">{{ data_get($section, 'heading') }}</h3>
                            @endif
                            <div class="grid gap-4 {{ $grid }}">
                                @foreach(data_get($section, 'items', []) as $item)
                                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                                        <div class="text-xl">{{ data_get($item, 'icon') }}</div>
                                        <div class="mt-2 font-semibold">{{ data_get($item, 'title') }}</div>
                                        <p class="text-sm text-slate-600">{{ data_get($item, 'text') }}</p>
                                    </div>
                                @endforeach
                            </div>
                            @break

                        @case('stats')
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach(data_get($section, 'items', []) as $item)
                                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
                                        <div class="text-2xl font-semibold">{{ data_get($item, 'value') }}</div>
                                        <div class="text-xs text-slate-500">{{ data_get($item, 'label') }}</div>
                                    </div>
                                @endforeach
                            </div>
                            @break

                        @case('cta')
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="text-lg font-semibold">{{ data_get($section, 'heading') }}</div>
                                    <div class="text-sm text-slate-600">{{ data_get($section, 'body') }}</div>
                                </div>
                                @if(data_get($section, 'button_text'))
                                    <a href="{{ data_get($section, 'button_url', '#') }}" class="community-btn">
                                        {{ data_get($section, 'button_text') }}
                                    </a>
                                @endif
                            </div>
                            @break

                        @case('image')
                            @php
                                $image = data_get($section, 'image');
                                $imageUrl = null;
                                if ($image) {
                                    $imageUrl = str_starts_with($image, ['http://', 'https://', '//'])
                                        ? $image
                                        : Storage::disk('public')->url($image);
                                }
                            @endphp
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ data_get($section, 'image_alt', '') }}"
                                     class="w-full rounded-xl object-cover" />
                            @endif
                            @if(data_get($section, 'caption'))
                                <div class="mt-2 text-xs text-slate-500">{{ data_get($section, 'caption') }}</div>
                            @endif
                            @break

                    @case('divider')
                        <div class="flex items-center gap-3">
                            <div class="h-px flex-1 bg-slate-200"></div>
                            <div class="text-xs font-semibold text-slate-500">{{ data_get($section, 'label') }}</div>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>
                        @break

                    @case('modal')
                        @php
                            $variant = data_get($section, 'variant', 'info');
                            $variantMap = [
                                'info' => 'bg-slate-900 text-white',
                                'success' => 'bg-emerald-600 text-white',
                                'warning' => 'bg-amber-500 text-slate-900',
                                'danger' => 'bg-rose-600 text-white',
                            ];
                            $variantClass = $variantMap[$variant] ?? $variantMap['info'];
                        @endphp
                        <div x-data="{ open: false }" class="space-y-3">
                            @if(data_get($section, 'heading'))
                                <h3 class="text-lg font-semibold">{{ data_get($section, 'heading') }}</h3>
                            @endif
                            @if(data_get($section, 'body'))
                                <div class="text-sm text-slate-600">{!! nl2br(e(data_get($section, 'body'))) !!}</div>
                            @endif
                            <button type="button" class="community-btn {{ $variantClass }}" @click="open = true">
                                {{ data_get($section, 'button_text', 'Uyari') }}
                            </button>

                            <div x-show="open" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-cloak>
                                <div class="absolute inset-0 bg-slate-900/60" @click="open = false"></div>
                                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                                    <div class="text-lg font-semibold">
                                        {{ data_get($section, 'modal_title', 'Uyari') }}
                                    </div>
                                    <div class="mt-2 text-sm text-slate-600">
                                        {!! nl2br(e(data_get($section, 'body'))) !!}
                                    </div>
                                    <div class="mt-5 flex flex-wrap justify-end gap-2">
                                        <button type="button" class="community-btn" @click="open = false">
                                            {{ data_get($section, 'cancel_text', 'Iptal') }}
                                        </button>
                                        <button type="button" class="community-btn {{ $variantClass }}" @click="open = false">
                                            {{ data_get($section, 'confirm_text', 'Tamam') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('html')
                        {!! data_get($section, 'html') !!}
                        @break

                        @default
                            <div class="text-sm text-slate-500">Bilinmeyen blok.</div>
                    @endswitch
                </div>
            </section>
        @endforeach
    </div>
@endif
