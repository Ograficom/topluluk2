@extends('layouts.app')

@section('title', 'Sıkça Sorulan Sorular')
@section('meta_description', 'Sıkça sorulan sorular ve cevaplar.')

@php
    $faqItems = \App\Models\Faq::query()
        ->active()
        ->ordered()
        ->get(['question', 'answer']);

    $normalizeText = function ($value): string {
        $text = trim((string) $value);

        if ($text === '') {
            return '';
        }

        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    };

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqItems->map(function ($item) use ($normalizeText) {
            $question = is_array($item) ? ($item['question'] ?? '') : ($item->question ?? '');
            $answer = is_array($item) ? ($item['answer'] ?? '') : ($item->answer ?? '');

            $question = $normalizeText($question);
            $answer = $normalizeText($answer);

            if ($question === '' || $answer === '') {
                return null;
            }

            return [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($answer),
                ],
            ];
        })->filter()->values()->all(),
    ];
@endphp

@push('head')
    <script type="application/ld+json">
        {!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <style>
        .ografi-faq-page {
            width: 100%;
        }

        .ografi-faq-list {
            width: 100%;
            padding-bottom: 0;
        }

        .ografi-faq-item {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            color: #0f172a;
            transition:
                background-color 180ms ease,
                border-color 180ms ease,
                color 180ms ease;
        }

        .ografi-faq-summary {
            color: #0f172a;
        }

        .ografi-faq-icon {
            color: #64748b;
        }

        .ografi-faq-answer {
            border-top: 1px solid rgba(226, 232, 240, 0.95);
            color: #475569;
        }

        .ografi-faq-empty {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            color: #475569;
        }

        html.dark .ografi-faq-item,
        .dark .ografi-faq-item,
        body.dark .ografi-faq-item,
        [data-theme="dark"] .ografi-faq-item,
        [data-bs-theme="dark"] .ografi-faq-item {
            background: #0f172a !important;
            border-color: rgba(51, 65, 85, 0.95) !important;
            color: #f8fafc !important;
        }

        html.dark .ografi-faq-summary,
        .dark .ografi-faq-summary,
        body.dark .ografi-faq-summary,
        [data-theme="dark"] .ografi-faq-summary,
        [data-bs-theme="dark"] .ografi-faq-summary {
            color: #f8fafc !important;
        }

        html.dark .ografi-faq-icon,
        .dark .ografi-faq-icon,
        body.dark .ografi-faq-icon,
        [data-theme="dark"] .ografi-faq-icon,
        [data-bs-theme="dark"] .ografi-faq-icon {
            color: #cbd5e1 !important;
        }

        html.dark .ografi-faq-answer,
        .dark .ografi-faq-answer,
        body.dark .ografi-faq-answer,
        [data-theme="dark"] .ografi-faq-answer,
        [data-bs-theme="dark"] .ografi-faq-answer {
            border-top-color: rgba(51, 65, 85, 0.95) !important;
            color: #cbd5e1 !important;
        }

        html.dark .ografi-faq-empty,
        .dark .ografi-faq-empty,
        body.dark .ografi-faq-empty,
        [data-theme="dark"] .ografi-faq-empty,
        [data-bs-theme="dark"] .ografi-faq-empty {
            background: #0f172a !important;
            border-color: rgba(51, 65, 85, 0.95) !important;
            color: #cbd5e1 !important;
        }

        @media (prefers-color-scheme: dark) {
            html:not(.light) .ografi-faq-item {
                background: #0f172a;
                border-color: rgba(51, 65, 85, 0.95);
                color: #f8fafc;
            }

            html:not(.light) .ografi-faq-summary {
                color: #f8fafc;
            }

            html:not(.light) .ografi-faq-icon {
                color: #cbd5e1;
            }

            html:not(.light) .ografi-faq-answer {
                border-top-color: rgba(51, 65, 85, 0.95);
                color: #cbd5e1;
            }

            html:not(.light) .ografi-faq-empty {
                background: #0f172a;
                border-color: rgba(51, 65, 85, 0.95);
                color: #cbd5e1;
            }
        }

        @media (max-width: 640px) {
            .ografi-faq-list {
                gap: 0.75rem;
                padding-bottom: calc(112px + env(safe-area-inset-bottom, 0px)) !important;
            }

            .ografi-faq-item,
            .ografi-faq-empty {
                border-radius: 1rem !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="ografi-faq-page">
        <section class="ografi-faq-list flex flex-col gap-3 sm:gap-4">
            @foreach($faqItems as $item)
                @php
                    $question = is_array($item) ? ($item['question'] ?? '') : ($item->question ?? '');
                    $answer = is_array($item) ? ($item['answer'] ?? '') : ($item->answer ?? '');

                    $question = $normalizeText($question);
                    $answer = $normalizeText($answer);
                @endphp

                @if($question !== '' && $answer !== '')
                    <article class="ografi-faq-item rounded-2xl px-4 py-4 sm:px-5 sm:py-5">
                        <details class="group">
                            <summary class="ografi-faq-summary flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-normal">
                                <span>{{ $question }}</span>

                                <span class="ografi-faq-icon shrink-0 text-lg leading-none transition-transform duration-200 group-open:rotate-45">
                                    +
                                </span>
                            </summary>

                            <div class="ografi-faq-answer mt-3 pt-3 text-sm font-normal leading-6">
                                {!! nl2br(e($answer)) !!}
                            </div>
                        </details>
                    </article>
                @endif
            @endforeach

            @if($faqItems->isEmpty())
                <div class="ografi-faq-empty rounded-xl px-4 py-3 text-sm font-normal">
                    Henüz admin panelinden aktif bir SSS eklenmedi.
                </div>
            @endif
        </section>
    </div>
@endsection
