@extends('layouts.app')

@section('title', $title ?? 'Çerez Politikası')
@section('hide_feed_header', '1')

@push('head')
    <style>
        .cookie-policy-page {
            width: 100%;
            padding: 28px 30px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #ffffff;
            color: #27272a;
            box-sizing: border-box;
        }
        .cookie-policy-title {
            margin: 0 0 24px;
            color: #111827;
            font-size: 26px;
            font-weight: 600;
            line-height: 1.25;
        }
        .cookie-policy-content {
            color: #3f3f46;
            font-size: 15px;
            font-weight: 400;
            line-height: 1.75;
            overflow-wrap: anywhere;
        }
        .cookie-policy-content :is(h1, h2, h3, h4) {
            margin: 28px 0 10px;
            color: #18181b;
            font-weight: 600;
            line-height: 1.35;
        }
        .cookie-policy-content h1 { font-size: 24px; }
        .cookie-policy-content h2 { font-size: 20px; }
        .cookie-policy-content h3 { font-size: 17px; }
        .cookie-policy-content :is(p, ul, ol, blockquote) { margin: 0 0 16px; }
        .cookie-policy-content :is(ul, ol) { padding-left: 22px; }
        .cookie-policy-content ul { list-style: disc; }
        .cookie-policy-content ol { list-style: decimal; }
        .cookie-policy-content a { color: #0e7c86; text-decoration: underline; text-underline-offset: 3px; }
        .cookie-policy-content table { width: 100%; margin: 18px 0; border-collapse: collapse; }
        .cookie-policy-content :is(th, td) { padding: 10px 12px; border: 1px solid #e5e7eb; text-align: left; }
        .cookie-policy-content th { background: #f8fafc; color: #18181b; font-weight: 600; }
        html.dark .cookie-policy-page,
        .dark .cookie-policy-page { border-color: #27272a; background: #18181b; color: #e4e4e7; }
        html.dark .cookie-policy-title,
        html.dark .cookie-policy-content :is(h1, h2, h3, h4),
        .dark .cookie-policy-title,
        .dark .cookie-policy-content :is(h1, h2, h3, h4) { color: #fafafa; }
        html.dark .cookie-policy-content,
        .dark .cookie-policy-content { color: #d4d4d8; }
        @media (max-width: 640px) {
            .cookie-policy-page {
                padding: 22px 16px;
                border-right: 0;
                border-left: 0;
                border-radius: 0;
            }
            .cookie-policy-title { margin-bottom: 18px; font-size: 22px; }
            .cookie-policy-content { font-size: 14px; line-height: 1.7; }
        }
    </style>
@endpush

@section('content')
    <article class="cookie-policy-page">
        <h1 class="cookie-policy-title">{{ $title ?? 'Çerez Politikası' }}</h1>
        <div class="cookie-policy-content">
            {!! $policy !!}
        </div>
    </article>
@endsection
